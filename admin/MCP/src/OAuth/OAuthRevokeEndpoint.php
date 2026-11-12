<?php
defined('MCP_ROOT') || exit;
/**
 * Endpoint de revocacion del AS (RFC 7009): POST <issuer>/revoke.
 *
 * El cliente autenticado revoca un refresh token o un access token OAuth propios; la revocacion borra la concesion
 * entera (revokeByAccess). Un token ajeno, inexistente o con formato invalido no cambia nada y responde igual
 * (200 `{}`), sin distinguir la causa. Los secretos no se aceptan en la query string (H-19); nunca se registra el token.
 *
 * @see projects/mcp-oauth/specs/technical.md §13.0, §13.4
 */
class OAuthRevokeEndpoint
{
    /** @var DB\SQL Conexion MySQL_MCP (transaccion de la revocacion) */
    private $db;

    /** @var QueryBuilder Constructor de consultas (busquedas con FOR UPDATE) */
    private QueryBuilder $qb;

    /** @var OAuthClientRepository Repositorio de clientes */
    private OAuthClientRepository $repo;

    /** @var OAuthTokenService Revocacion de concesiones */
    private OAuthTokenService $tokens;

    /**
     * Crea el endpoint con los repositorios y servicios que usa (conexion unica McpDb::conn()).
     */
    public function __construct()
    {
        $this->db     = McpDb::conn();
        $this->qb     = new QueryBuilder();
        $this->repo   = new OAuthClientRepository();
        $this->tokens = new OAuthTokenService();
    }

    /**
     * Busca con bloqueo (FOR UPDATE) la concesion a la que pertenece un token.
     *
     * @param string $sql    SELECT de una fila con dos columnas: id del acceso y id del cliente
     * @param string $valor  Valor enlazado (hash del refresh o valor del access)
     * @return array{0: int, 1: int}|null [idTokenAcceso, idCliente]; null si no hay fila
     * @throws RuntimeException Si falla la base de datos
     */
    private function lookup(string $sql, string $valor): ?array
    {
        $r = $this->qb->queryExecute($sql, $this->db, false, true, [$valor]);
        if (($r['status'] ?? false) !== true) {
            throw new RuntimeException('OAuth: fallo de base de datos');
        }
        $fila = $r['data'] ?? null;
        if (!is_array($fila) || $fila === []) {
            return null;
        }
        $v = array_values($fila);
        return [(int) $v[0], (int) $v[1]];
    }

    /**
     * Atiende POST /revoke.
     *
     * Limite oauth-ip; secretos en la query (400); cuerpo form; token obligatorio (invalid_request); autenticacion
     * del cliente (401 invalid_client). Con un token de 64 hex y en una transaccion: (1) busca por SHA-256 en los
     * refresh y (2) si no hay fila, por valor en los accesos OAuth (con token_type_hint access_token se invierte el
     * orden); si la fila es del mismo cliente, revoca la concesion. Siempre 200 con cuerpo `{}`.
     *
     * @return never
     */
    public function handle(): never
    {
        try {
            $ventana = (int) ConfigAPP::APP['rateLimiterGuestWindowSeconds'];
            if (RateLimiter::isBlockedByIp('oauth-ip:' . OAuthHttp::ipKey((string) ($_SERVER['REMOTE_ADDR'] ?? '')), McpConfig::OAUTH_IP_MAX, $ventana)) {
                OAuthHttp::error(429, 'temporarily_unavailable', 'Too many requests', ['Retry-After' => (string) $ventana]);
            }
            OAuthHttp::forbidQuerySecrets();
            $p     = OAuthHttp::formBody();
            $token = (string) ($p['token'] ?? '');
            if ($token === '') {
                OAuthHttp::error(400, 'invalid_request', 'token is required');
            }
            $auth = OAuthClientAuth::authenticate($p, $this->repo);
            if ($auth['client'] === null) {
                OAuthHttp::error(401, 'invalid_client', '', $auth['basic'] ? ['WWW-Authenticate' => 'Basic realm="coreEngine-OAuth"'] : []);
            }
            $idCliente = (int) $auth['client']['idCliente'];

            if (preg_match('/^[a-f0-9]{64}$/D', $token) === 1) {
                $busquedas = [
                    'refresh' => ['SELECT idTokenAcceso, idCliente FROM tokens_oauth_refresh WHERE RefreshHash = ? FOR UPDATE', OAuthCrypto::hash($token)],
                    'access'  => ['SELECT idToken, idClienteOAuth FROM tokens_listado WHERE Token = ? AND Origen = 2 FOR UPDATE', $token],
                ];
                if (($p['token_type_hint'] ?? '') === 'access_token') {
                    $busquedas = array_reverse($busquedas);
                }
                $this->db->begin();
                foreach ($busquedas as [$sql, $valor]) {
                    $fila = $this->lookup($sql, $valor);
                    if ($fila !== null) {
                        if ($fila[1] === $idCliente) {
                            $this->tokens->revokeByAccess($fila[0]);
                        }
                        break;
                    }
                }
                if ($this->db->commit() !== true) {
                    throw new RuntimeException('OAuth: fallo de base de datos');
                }
            }
            OAuthHttp::json(200, []);
        } catch (Throwable $e) {
            if ($this->db->trans()) {
                $this->db->rollback();
            }
            error_log('OAuth: ' . get_class($e));
            OAuthHttp::error(500, 'server_error');
        }
    }
}
