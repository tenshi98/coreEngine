<?php
defined('MCP_ROOT') || exit;
/**
 * Limpieza de filas caducadas del AS (D-4), acotada por pasada a OAUTH_CLEANUP_BATCH filas por tabla.
 *
 * @see projects/mcp-oauth/specs/technical.md §12
 */
class OAuthCleanup
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
     * Ejecuta un DELETE acotado y devuelve las filas borradas.
     *
     * @param string       $sql    Sentencia sin LIMIT
     * @param array<mixed> $params Valores enlazados
     * @return int Filas borradas
     * @throws RuntimeException Si falla la base de datos
     */
    private function delete(string $sql, array $params = []): int
    {
        $r = $this->qb->queryExecute($sql . ' LIMIT ' . (int) McpConfig::OAUTH_CLEANUP_BATCH, $this->db, false, false, $params);
        if (($r['status'] ?? false) !== true) {
            throw new RuntimeException('OAuth: fallo de base de datos');
        }
        return (int) $r['data'];
    }

    /**
     * Borra los codigos caducados hace mas de un dia.
     *
     * @return int Filas borradas
     */
    public function purgeCodes(): int
    {
        return $this->delete('DELETE FROM tokens_oauth_codigos WHERE FechaExpiracion < DATE_SUB(NOW(), INTERVAL 1 DAY)');
    }

    /**
     * Borra los refresh caducados hace mas de un dia y los revocados hace mas de un dia.
     *
     * Los rotados (Estado 2) se conservan hasta su expiracion para detectar reusos.
     *
     * @return int Filas borradas
     */
    public function purgeRefresh(): int
    {
        return $this->delete(
            'DELETE FROM tokens_oauth_refresh WHERE FechaExpiracion < DATE_SUB(NOW(), INTERVAL 1 DAY) '
            . 'OR (Estado = 3 AND FechaCreacion < DATE_SUB(NOW(), INTERVAL 1 DAY))'
        );
    }

    /**
     * Borra los accesos OAuth (Origen 2) expirados hace mas de un dia sin refresh vigente.
     *
     * @return int Filas borradas
     */
    public function purgeAccess(): int
    {
        return $this->delete(
            'DELETE FROM tokens_listado WHERE Origen = 2 AND FechaExpiracion < DATE_SUB(NOW(), INTERVAL 1 DAY) '
            . 'AND NOT EXISTS (SELECT 1 FROM tokens_oauth_refresh r WHERE r.idTokenAcceso = tokens_listado.idToken '
            . 'AND r.Estado = 1 AND r.FechaExpiracion >= NOW())'
        );
    }

    /**
     * Borra los clientes dinamicos registrados hace mas de OAUTH_UNUSED_HOURS horas sin concesion viva.
     *
     * Nunca borra uno con filas hijas (tokens_listado.idClienteOAuth) ni con refresh vigentes (B-5, M-5).
     *
     * @return int Filas borradas
     */
    public function purgeUnusedClients(): int
    {
        return $this->delete(
            'DELETE FROM tokens_oauth_clientes WHERE FechaRegistro < DATE_SUB(NOW(), INTERVAL ? HOUR) '
            . 'AND NOT EXISTS (SELECT 1 FROM tokens_listado t WHERE t.idClienteOAuth = tokens_oauth_clientes.idCliente) '
            . 'AND NOT EXISTS (SELECT 1 FROM tokens_oauth_refresh r WHERE r.idCliente = tokens_oauth_clientes.idCliente '
            . 'AND r.Estado = 1 AND r.FechaExpiracion >= NOW())',
            [McpConfig::OAUTH_UNUSED_HOURS]
        );
    }

    /**
     * Ejecuta la limpieza, siempre o 1 de cada OAUTH_CLEANUP_ODDS veces.
     *
     * Un fallo se registra en error_log sin detalle y no se propaga.
     *
     * @param bool $siempre true para forzar la pasada
     * @return void
     */
    public function maybeRun(bool $siempre): void
    {
        if (!$siempre && random_int(1, McpConfig::OAUTH_CLEANUP_ODDS) !== 1) {
            return;
        }
        try {
            $this->purgeCodes();
            $this->purgeRefresh();
            $this->purgeAccess();
            $this->purgeUnusedClients();
        } catch (Throwable $e) {
            error_log('OAuth: limpieza fallida');
        }
    }
}
