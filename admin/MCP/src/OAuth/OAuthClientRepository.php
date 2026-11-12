<?php
defined('MCP_ROOT') || exit;
/**
 * Repositorio de clientes OAuth (tokens_oauth_clientes) sobre la conexion MySQL_MCP.
 *
 * Toda consulta usa parametros enlazados; las fechas se calculan en SQL. El secreto del cliente
 * se guarda solo como SHA-256 y nunca se registra ni se devuelve salvo en el alta (una vez).
 *
 * @see projects/mcp-oauth/specs/technical.md §8
 */
class OAuthClientRepository
{
    /** @var DB\SQL Conexion MySQL_MCP */
    private $db;

    /** @var QueryBuilder Constructor de consultas */
    private QueryBuilder $qb;

    public function __construct()
    {
        $this->db = McpDb::conn();
        $this->qb = new QueryBuilder();
    }

    /**
     * Comprueba el resultado de QueryBuilder y devuelve sus datos.
     *
     * @param array<string,mixed> $r Respuesta de QueryBuilder
     * @return mixed Campo data de la respuesta
     * @throws RuntimeException Si status no es true (sin reenviar error, internal_error ni query)
     */
    private function check(array $r): mixed
    {
        if (($r['status'] ?? false) !== true) {
            throw new RuntimeException('OAuth: fallo de base de datos');
        }
        return $r['data'] ?? null;
    }

    /**
     * Busca un cliente por su client_id.
     *
     * @param string $clientId client_id recibido (32 hexadecimales)
     * @return array<string,mixed>|null Fila con RedirectUris decodificado (lista; si no lo es, []) y GrantTypes
     *         (Metadatos.grant_types o ['authorization_code', 'refresh_token']); null si el formato es
     *         invalido (sin consultar) o no existe
     * @throws RuntimeException Si falla la base de datos
     */
    public function findByClientId(string $clientId): ?array
    {
        if (preg_match('/^[a-f0-9]{32}$/D', $clientId) !== 1) {
            return null;
        }
        $fila = $this->check($this->qb->queryRow([
            'data'   => 'idCliente, ClientId, SecretHash, AuthMethod, Nombre, RedirectUris, Metadatos, idEstado, FechaRegistro, IPRegistro',
            'table'  => 'tokens_oauth_clientes',
            'where'  => 'ClientId = ?',
            'params' => [$clientId],
        ], $this->db));
        if (!is_array($fila) || $fila === []) {
            return null;
        }
        $uris = json_decode((string) ($fila['RedirectUris'] ?? ''), true);
        $fila['RedirectUris'] = is_array($uris) && array_is_list($uris) ? $uris : [];
        $meta = json_decode((string) ($fila['Metadatos'] ?? ''), true);
        $grants = is_array($meta) && isset($meta['grant_types']) && is_array($meta['grant_types'])
            ? array_values($meta['grant_types'])
            : ['authorization_code', 'refresh_token'];
        $fila['GrantTypes'] = $grants;
        return $fila;
    }

    /**
     * Indica si el cliente puede usarse (activo).
     *
     * @param array<string,mixed> $c Fila devuelta por findByClientId
     * @return bool true si idEstado es 1
     */
    public function isUsable(array $c): bool
    {
        return (int) ($c['idEstado'] ?? 0) === 1;
    }

    /**
     * Registra un cliente nuevo, activo, con client_id de 128 bits y secreto de 256 bits si es confidencial.
     *
     * $m son los metadatos ya validados (claves de RFC 7591): client_name, redirect_uris,
     * token_endpoint_auth_method y los opcionales grant_types, response_types, scope, client_uri, logo_uri,
     * software_id y software_version.
     *
     * @param array<string,mixed> $m  Metadatos validados
     * @param string              $ip REMOTE_ADDR del registro
     * @return array{client_id: string, client_secret: ?string, client_id_issued_at: int} El secreto en claro
     *         solo se devuelve aqui (null en clientes publicos)
     * @throws RuntimeException Si falla la base de datos
     */
    public function register(array $m, string $ip): array
    {
        $clientId = OAuthCrypto::randomHex(16);
        $metodo   = (string) ($m['token_endpoint_auth_method'] ?? 'none');
        $secreto  = $metodo !== 'none' ? OAuthCrypto::randomHex(32) : null;
        $meta     = [];
        foreach (['grant_types', 'response_types', 'scope', 'client_uri', 'logo_uri', 'software_id', 'software_version'] as $k) {
            if (isset($m[$k])) {
                $meta[$k] = $m[$k];
            }
        }
        $this->check($this->qb->queryExecute(
            'INSERT INTO tokens_oauth_clientes (ClientId, SecretHash, AuthMethod, Nombre, RedirectUris, Metadatos, idEstado, FechaRegistro, IPRegistro, IPClave, FechaUltimoUso) '
            . 'VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), ?, ?, NULL)',
            $this->db,
            false,
            false,
            [
                $clientId,
                $secreto !== null ? OAuthCrypto::hash($secreto) : null,
                $metodo,
                (string) ($m['client_name'] ?? ''),
                json_encode(array_values((array) ($m['redirect_uris'] ?? [])), JSON_UNESCAPED_SLASHES),
                $meta === [] ? null : json_encode($meta, JSON_UNESCAPED_SLASHES),
                $ip,
                OAuthHttp::ipKey($ip),
            ]
        ));
        // client_id_issued_at: epoch calculado en SQL a partir de la fecha de alta (RA-6)
        $fila = $this->check($this->qb->queryRow([
            'data'   => 'UNIX_TIMESTAMP(FechaRegistro) AS emitido',
            'table'  => 'tokens_oauth_clientes',
            'where'  => 'ClientId = ?',
            'params' => [$clientId],
        ], $this->db));
        return [
            'client_id'           => $clientId,
            'client_secret'       => $secreto,
            'client_id_issued_at' => (int) ($fila['emitido'] ?? 0),
        ];
    }

    /**
     * Autentica un cliente con su secreto.
     *
     * @param array<string,mixed> $c      Fila devuelta por findByClientId
     * @param string|null         $secret Secreto presentado (null si no hay)
     * @return bool Publico (AuthMethod none): true solo sin secreto. Confidencial: true solo con secreto cuyo
     *         SHA-256 coincide (hash_equals)
     */
    public function authenticate(array $c, ?string $secret): bool
    {
        if (($c['AuthMethod'] ?? '') === 'none') {
            return $secret === null;
        }
        return $secret !== null
            && isset($c['SecretHash']) && is_string($c['SecretHash'])
            && hash_equals($c['SecretHash'], OAuthCrypto::hash($secret));
    }

    /**
     * Cuenta los clientes que nunca obtuvieron acceso (los usados no cuentan para el tope).
     *
     * @return int Numero de clientes con FechaUltimoUso nula
     * @throws RuntimeException Si falla la base de datos
     */
    public function countUnused(): int
    {
        $fila = $this->check($this->qb->queryRow([
            'data'  => 'COUNT(*) AS n',
            'table' => 'tokens_oauth_clientes',
            'where' => 'FechaUltimoUso IS NULL',
        ], $this->db));
        return (int) ($fila['n'] ?? 0);
    }

    /**
     * Cuenta los registros de una clave de IP (IPv4 o IPv6 /64) en las ultimas 24 horas.
     *
     * @param string $ipKey Clave devuelta por OAuthHttp::ipKey
     * @return int Numero de registros
     * @throws RuntimeException Si falla la base de datos
     */
    public function countRecentByIp(string $ipKey): int
    {
        $fila = $this->check($this->qb->queryRow([
            'data'   => 'COUNT(*) AS n',
            'table'  => 'tokens_oauth_clientes',
            'where'  => 'IPClave = ? AND FechaRegistro >= DATE_SUB(NOW(), INTERVAL 1 DAY)',
            'params' => [$ipKey],
        ], $this->db));
        return (int) ($fila['n'] ?? 0);
    }

    /**
     * Marca el ultimo uso del cliente (se llama dentro de la transaccion del canje del codigo).
     *
     * @param int $idCliente Id del cliente
     * @return void
     * @throws RuntimeException Si falla la base de datos
     */
    public function markUsed(int $idCliente): void
    {
        $this->check($this->qb->queryExecute(
            'UPDATE tokens_oauth_clientes SET FechaUltimoUso = NOW() WHERE idCliente = ?',
            $this->db,
            false,
            false,
            [$idCliente]
        ));
    }
}
