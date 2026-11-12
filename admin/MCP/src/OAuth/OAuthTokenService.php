<?php
defined('MCP_ROOT') || exit;
/**
 * Emision, rotacion y revocacion de accesos y refresh tokens OAuth.
 *
 * Los metodos que escriben o bloquean (issueAccess, issueRefresh, grantAlive, rotate, revokeByAccess) se llaman
 * dentro de la transaccion del endpoint. El access token es una fila hija de tokens_listado (Origen 2) y el
 * refresh se guarda solo como SHA-256. Las fechas se calculan en SQL.
 *
 * @see projects/mcp-oauth/specs/technical.md §11
 */
class OAuthTokenService
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
     * Ejecuta una sentencia con parametros enlazados y devuelve su resultado.
     *
     * @param string       $sql    Sentencia SQL con ?
     * @param array<mixed> $params Valores enlazados
     * @param bool         $single true para una sola fila en lecturas
     * @return mixed Filas afectadas (escritura), fila o false (lectura de una fila)
     * @throws RuntimeException Si falla la base de datos (sin reenviar error, internal_error ni query)
     */
    private function run(string $sql, array $params, bool $single = false): mixed
    {
        $r = $this->qb->queryExecute($sql, $this->db, false, $single, $params);
        if (($r['status'] ?? false) !== true) {
            throw new RuntimeException('OAuth: fallo de base de datos');
        }
        return $r['data'] ?? null;
    }

    /**
     * Inserta la fila hija de acceso (Origen 2) con un access token nuevo.
     *
     * Nombre = 'OAuth: ' + nombre del cliente (255 max.); idUsuario lleva el valor neutro fijo 0 (D-24).
     *
     * @param array<string,mixed> $c            Fila del cliente (idCliente, Nombre)
     * @param int                 $idTokenPadre Token manual verificado
     * @return array{idTokenAcceso: int, access_token: string} access_token en claro (64 hexadecimales)
     * @throws RuntimeException Si falla la base de datos
     */
    public function issueAccess(array $c, int $idTokenPadre): array
    {
        $access = OAuthCrypto::randomHex(32);
        $this->run(
            'INSERT INTO tokens_listado (Nombre, Token, FechaCreacion, idUsuario, idEstado, FechaCaducidad, Origen, idTokenPadre, idClienteOAuth, FechaExpiracion) '
            . 'VALUES (?, ?, NOW(), 0, 1, NULL, 2, ?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))',
            [mb_substr('OAuth: ' . (string) $c['Nombre'], 0, 255), $access, $idTokenPadre, (int) $c['idCliente'], McpConfig::OAUTH_ACCESS_TTL]
        );
        return ['idTokenAcceso' => (int) $this->db->lastInsertId(), 'access_token' => $access];
    }

    /**
     * Emite un refresh token de la concesion.
     *
     * FechaExpiracion = min(ahora + 30 dias, fin de familia); la familia dura 90 dias desde la primera emision.
     *
     * @param int         $idCliente     Cliente
     * @param int         $idTokenAcceso Fila hija de acceso
     * @param string      $familia       Familia (32 hexadecimales), comun a las rotaciones de una concesion
     * @param string|null $expFamilia    Fin de familia existente (rotacion) o null en la primera emision
     * @return string Refresh en claro (64 hexadecimales); en BD solo su hash
     * @throws RuntimeException Si falla la base de datos o no hay MCP_PUBLIC_URL valida
     */
    public function issueRefresh(int $idCliente, int $idTokenAcceso, string $familia, ?string $expFamilia): string
    {
        $resource = McpConfig::resourceUrl();
        if ($resource === null) {
            throw new RuntimeException('OAuth: recurso no configurado');
        }
        $r = OAuthCrypto::randomHex(32);
        $this->run(
            'INSERT INTO tokens_oauth_refresh (RefreshHash, Familia, idCliente, idTokenAcceso, Resource, Scope, Estado, FechaCreacion, FechaExpiracionFamilia, FechaExpiracion) '
            . 'VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), COALESCE(?, DATE_ADD(NOW(), INTERVAL ? SECOND)), '
            . 'LEAST(DATE_ADD(NOW(), INTERVAL ? SECOND), COALESCE(?, DATE_ADD(NOW(), INTERVAL ? SECOND))))',
            [
                OAuthCrypto::hash($r), $familia, $idCliente, $idTokenAcceso, $resource, McpConfig::OAUTH_SCOPE,
                $expFamilia, McpConfig::OAUTH_REFRESH_MAX_TTL,
                McpConfig::OAUTH_REFRESH_TTL, $expFamilia, McpConfig::OAUTH_REFRESH_MAX_TTL,
            ]
        );
        return $r;
    }

    /**
     * Indica si la concesion sigue viva y bloquea sus filas (FOR UPDATE).
     *
     * @param int $idTokenAcceso Fila hija de acceso
     * @return bool true solo si el hijo existe (Origen 2), su padre es manual (Origen 1), activo y no caducado,
     *         y su cliente esta activo
     * @throws RuntimeException Si falla la base de datos
     */
    public function grantAlive(int $idTokenAcceso): bool
    {
        $fila = $this->run(
            'SELECT t.idToken FROM tokens_listado t '
            . 'INNER JOIN tokens_listado p ON p.idToken = t.idTokenPadre '
            . 'INNER JOIN tokens_oauth_clientes c ON c.idCliente = t.idClienteOAuth '
            . 'WHERE t.idToken = ? AND t.Origen = 2 AND p.Origen = 1 AND p.idEstado = 1 '
            . 'AND (p.FechaCaducidad IS NULL OR p.FechaCaducidad >= CURDATE()) AND c.idEstado = 1 FOR UPDATE',
            [$idTokenAcceso],
            true
        );
        return is_array($fila) && $fila !== [];
    }

    /**
     * Rota un refresh token (dentro de la transaccion del endpoint; bloquea la fila con FOR UPDATE).
     *
     * Estados (M-3): invalido (formato, sin fila, de otro cliente o rotado hace menos de OAUTH_REFRESH_GRACE
     * segundos: sin cambios, el llamador hace rollback), reusado (rotado o revocado fuera de la gracia: revoca
     * la concesion, commit), expirado (refresh o familia: rollback), revocado (padre o cliente ya no validos:
     * revoca la concesion, commit) y ok (rota).
     *
     * @param string              $refresh Refresh recibido
     * @param array<string,mixed> $c       Fila del cliente autenticado (idCliente)
     * @return array{estado: string, access_token?: string, refresh_token?: string} En ok: el access token
     *         nuevo (misma fila hija, D-8) y el refresh nuevo, en claro
     * @throws RuntimeException Si falla la base de datos
     */
    public function rotate(string $refresh, array $c): array
    {
        if (preg_match('/^[a-f0-9]{64}$/D', $refresh) !== 1) {
            return ['estado' => 'invalido'];
        }
        $fila = $this->run(
            'SELECT idRefresh, Familia, idCliente, idTokenAcceso, Estado, FechaExpiracionFamilia, '
            . '(FechaExpiracion < NOW()) AS Expirado, (FechaExpiracionFamilia < NOW()) AS FamiliaExpirada, '
            . '(FechaUso IS NOT NULL AND FechaUso >= DATE_SUB(NOW(), INTERVAL ? SECOND)) AS EnGracia '
            . 'FROM tokens_oauth_refresh WHERE RefreshHash = ? FOR UPDATE',
            [McpConfig::OAUTH_REFRESH_GRACE, OAuthCrypto::hash($refresh)],
            true
        );
        if (!is_array($fila) || $fila === [] || (int) $fila['idCliente'] !== (int) $c['idCliente']) {
            return ['estado' => 'invalido'];
        }
        $estado        = (int) $fila['Estado'];
        $idTokenAcceso = (int) $fila['idTokenAcceso'];
        if ($estado === 2 && (int) $fila['EnGracia'] === 1) {
            return ['estado' => 'invalido'];
        }
        if ($estado === 2 || $estado === 3) {
            $this->revokeByAccess($idTokenAcceso);
            return ['estado' => 'reusado'];
        }
        if ((int) $fila['Expirado'] === 1 || (int) $fila['FamiliaExpirada'] === 1) {
            return ['estado' => 'expirado'];
        }
        if (!$this->grantAlive($idTokenAcceso)) {
            $this->revokeByAccess($idTokenAcceso);
            return ['estado' => 'revocado'];
        }
        $this->run('UPDATE tokens_oauth_refresh SET Estado = 2, FechaUso = NOW() WHERE idRefresh = ?', [(int) $fila['idRefresh']]);
        $access = OAuthCrypto::randomHex(32);
        $this->run(
            'UPDATE tokens_listado SET Token = ?, FechaExpiracion = DATE_ADD(NOW(), INTERVAL ? SECOND) WHERE idToken = ? AND Origen = 2',
            [$access, McpConfig::OAUTH_ACCESS_TTL, $idTokenAcceso]
        );
        $nuevo = $this->issueRefresh((int) $fila['idCliente'], $idTokenAcceso, (string) $fila['Familia'], (string) $fila['FechaExpiracionFamilia']);
        return ['estado' => 'ok', 'access_token' => $access, 'refresh_token' => $nuevo];
    }

    /**
     * Revoca una concesion: marca sus refresh como revocados (Estado 3) y borra la fila hija de acceso.
     *
     * @param int $idTokenAcceso Fila hija de acceso (Origen 2)
     * @return void
     * @throws RuntimeException Si falla la base de datos
     */
    public function revokeByAccess(int $idTokenAcceso): void
    {
        $this->run('UPDATE tokens_oauth_refresh SET Estado = 3 WHERE idTokenAcceso = ? AND Estado <> 3', [$idTokenAcceso]);
        $this->run('DELETE FROM tokens_listado WHERE idToken = ? AND Origen = 2', [$idTokenAcceso]);
    }

    /**
     * Cuerpo de la respuesta de /token.
     *
     * @param string      $access  Access token
     * @param string|null $refresh Refresh token (null si el cliente no lo tiene)
     * @return array<string,mixed> access_token, token_type, expires_in, scope y, si no es null, refresh_token
     */
    public function response(string $access, ?string $refresh): array
    {
        $r = [
            'access_token' => $access,
            'token_type'   => 'Bearer',
            'expires_in'   => McpConfig::OAUTH_ACCESS_TTL,
            'scope'        => McpConfig::OAUTH_SCOPE,
        ];
        if ($refresh !== null) {
            $r['refresh_token'] = $refresh;
        }
        return $r;
    }
}
