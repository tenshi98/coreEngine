<?php
defined('MCP_ROOT') || exit;
/**
 * Endpoint de token del AS: POST <issuer>/token (grants authorization_code y refresh_token).
 *
 * Autentica al cliente (none, client_secret_post, client_secret_basic), canjea el codigo con PKCE S256 siempre
 * exigido o rota el refresh token, ambos dentro de una transaccion con FOR UPDATE. Los secretos no se aceptan en
 * la query string (H-19). Respuestas de error genericas: un fallo de BD o una excepcion no prevista responde
 * 500 server_error sin detalle; nunca se registran codigos, tokens, secretos ni code_verifier.
 *
 * @see projects/mcp-oauth/specs/technical.md §13.0, §13.3
 */
class OAuthTokenEndpoint
{
    /** @var DB\SQL Conexion MySQL_MCP (transaccion del canje y la rotacion) */
    private $db;

    /** @var QueryBuilder Constructor de consultas (validacion del token padre) */
    private QueryBuilder $qb;

    /** @var OAuthClientRepository Repositorio de clientes */
    private OAuthClientRepository $repo;

    /** @var OAuthCodeRepository Repositorio de codigos de autorizacion */
    private OAuthCodeRepository $codes;

    /** @var OAuthTokenService Emision, rotacion y revocacion de tokens */
    private OAuthTokenService $tokens;

    /** @var OAuthCleanup Limpieza de caducados (1 de cada OAUTH_CLEANUP_ODDS peticiones) */
    private OAuthCleanup $cleanup;

    /**
     * Crea el endpoint con los repositorios y servicios que usa (conexion unica McpDb::conn()).
     */
    public function __construct()
    {
        $this->db      = McpDb::conn();
        $this->qb      = new QueryBuilder();
        $this->repo    = new OAuthClientRepository();
        $this->codes   = new OAuthCodeRepository();
        $this->tokens  = new OAuthTokenService();
        $this->cleanup = new OAuthCleanup();
    }

    /**
     * Revierte la transaccion abierta y responde un error JSON de RFC 6749 §5.2.
     *
     * @param int    $status Codigo HTTP
     * @param string $code   Codigo de error OAuth
     * @param string $desc   Descripcion ASCII (opcional)
     * @return never
     */
    private function rollbackAndFail(int $status, string $code, string $desc = ''): never
    {
        $this->db->rollback();
        OAuthHttp::error($status, $code, $desc);
    }

    /**
     * Confirma la transaccion; si el commit falla lo trata como fallo de base de datos.
     *
     * @return void
     * @throws RuntimeException Si el commit no se puede confirmar
     */
    private function commit(): void
    {
        if ($this->db->commit() !== true) {
            throw new RuntimeException('OAuth: fallo de base de datos');
        }
    }

    /**
     * Canjea un codigo de autorizacion (authorization_code) por un access token y, si el cliente tiene ese grant, un refresh.
     *
     * Parametros invalidos -> invalid_request (el code_verifier es siempre obligatorio, PKCE S256) y resource ajeno ->
     * invalid_target. Dentro de la transaccion: consume (FOR UPDATE); un codigo reutilizado solo revoca lo emitido
     * si coinciden cliente y redirect_uri (D-18); cualquier otro fallo responde invalid_grant con rollback.
     *
     * @param array<string,string> $p Parametros del cuerpo
     * @param array<string,mixed>  $c Cliente autenticado (fila de findByClientId)
     * @return never
     * @throws RuntimeException Si falla la base de datos
     */
    public function authorizationCode(array $p, array $c): never
    {
        $code     = (string) ($p['code'] ?? '');
        $redirect = (string) ($p['redirect_uri'] ?? '');
        $verifier = (string) ($p['code_verifier'] ?? '');
        if ($code === '' || $redirect === '' || !OAuthPkce::isValidVerifier($verifier)) {
            OAuthHttp::error(400, 'invalid_request', 'code, redirect_uri and a valid code_verifier are required');
        }
        if (isset($p['resource']) && !OAuthMetadata::isMcpResource($p['resource'])) {
            OAuthHttp::error(400, 'invalid_target', 'resource is not this server');
        }

        $this->db->begin();
        $k = $this->codes->consume($code);
        if ($k === null) {
            $this->rollbackAndFail(400, 'invalid_grant');
        }
        if ($k['estado'] === 'reusado') {
            // Solo revoca lo emitido si el reuso viene del mismo cliente y con la misma redirect_uri (H-09, D-18)
            if ($k['idCliente'] === (int) $c['idCliente'] && hash_equals($k['RedirectUri'], $redirect) && $k['idTokenAcceso'] !== null) {
                $this->tokens->revokeByAccess($k['idTokenAcceso']);
                $this->commit();
                OAuthHttp::error(400, 'invalid_grant');
            }
            $this->rollbackAndFail(400, 'invalid_grant');
        }
        if ($k['estado'] !== 'ok'
            || $k['idCliente'] !== (int) $c['idCliente']
            || !hash_equals($k['RedirectUri'], $redirect)
            || !OAuthPkce::verify($verifier, $k['CodeChallenge'])
            || !$this->repo->isUsable($c)) {
            $this->rollbackAndFail(400, 'invalid_grant');
        }

        // El token padre se revalida con bloqueo: manual (Origen 1), activo y no caducado
        $r = $this->qb->queryExecute(
            'SELECT idToken FROM tokens_listado WHERE idToken = ? AND Origen = 1 AND idEstado = 1 '
            . 'AND (FechaCaducidad IS NULL OR FechaCaducidad >= CURDATE()) FOR UPDATE',
            $this->db,
            false,
            true,
            [$k['idToken']]
        );
        if (($r['status'] ?? false) !== true) {
            throw new RuntimeException('OAuth: fallo de base de datos');
        }
        if (!is_array($r['data']) || $r['data'] === []) {
            $this->rollbackAndFail(400, 'invalid_grant');
        }

        $acceso  = $this->tokens->issueAccess($c, $k['idToken']);
        $refresh = in_array('refresh_token', $c['GrantTypes'], true)
            ? $this->tokens->issueRefresh((int) $c['idCliente'], $acceso['idTokenAcceso'], OAuthCrypto::randomHex(16), null)
            : null;
        $this->codes->attachAccess($k['idCodigo'], $acceso['idTokenAcceso']);
        $this->repo->markUsed((int) $c['idCliente']);
        $this->commit();
        OAuthHttp::json(200, $this->tokens->response($acceso['access_token'], $refresh));
    }

    /**
     * Renueva un refresh token (rotacion) y entrega una pareja nueva.
     *
     * Un cliente sin el grant refresh_token recibe unauthorized_client. reusado y revocado hacen commit (la revocacion
     * de la familia debe persistir) y responden invalid_grant; expirado e invalido hacen rollback (M-3). Los scopes
     * desconocidos se ignoran (D-10).
     *
     * @param array<string,string> $p Parametros del cuerpo
     * @param array<string,mixed>  $c Cliente autenticado (fila de findByClientId)
     * @return never
     * @throws RuntimeException Si falla la base de datos
     */
    public function refreshToken(array $p, array $c): never
    {
        if (!in_array('refresh_token', $c['GrantTypes'], true)) {
            OAuthHttp::error(400, 'unauthorized_client', 'Client is not allowed to use refresh_token');
        }
        $refresh = (string) ($p['refresh_token'] ?? '');
        if ($refresh === '') {
            OAuthHttp::error(400, 'invalid_request', 'refresh_token is required');
        }
        if (isset($p['resource']) && !OAuthMetadata::isMcpResource($p['resource'])) {
            OAuthHttp::error(400, 'invalid_target', 'resource is not this server');
        }

        $this->db->begin();
        $r = $this->tokens->rotate($refresh, $c);
        if ($r['estado'] === 'ok') {
            $this->commit();
            OAuthHttp::json(200, $this->tokens->response($r['access_token'], $r['refresh_token']));
        }
        if ($r['estado'] === 'reusado' || $r['estado'] === 'revocado') {
            $this->commit();
            OAuthHttp::error(400, 'invalid_grant');
        }
        $this->rollbackAndFail(400, 'invalid_grant');
    }

    /**
     * Atiende POST /token.
     *
     * Orden: limite oauth-ip (429 con Retry-After); secretos en la query (400); cuerpo form; grant_type (invalid_request);
     * autenticacion del cliente (401 invalid_client); limpieza ocasional; despacho por grant_type
     * (unsupported_grant_type si es otro). Cualquier fallo no previsto: rollback y 500 server_error sin detalle.
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
            $grant = (string) ($p['grant_type'] ?? '');
            if ($grant === '') {
                OAuthHttp::error(400, 'invalid_request', 'grant_type is required');
            }
            $auth = OAuthClientAuth::authenticate($p, $this->repo);
            if ($auth['client'] === null) {
                OAuthHttp::error(401, 'invalid_client', '', $auth['basic'] ? ['WWW-Authenticate' => 'Basic realm="coreEngine-OAuth"'] : []);
            }
            $this->cleanup->maybeRun(false);

            if ($grant === 'authorization_code') {
                $this->authorizationCode($p, $auth['client']);
            }
            if ($grant === 'refresh_token') {
                $this->refreshToken($p, $auth['client']);
            }
            OAuthHttp::error(400, 'unsupported_grant_type', 'Unsupported grant_type');
        } catch (Throwable $e) {
            if ($this->db->trans()) {
                $this->db->rollback();
            }
            error_log('OAuth: ' . get_class($e));
            OAuthHttp::error(500, 'server_error');
        }
    }
}
