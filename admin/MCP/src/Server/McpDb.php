<?php
defined('MCP_ROOT') || exit;
/**
 * Conexion de BD del servidor MCP (cuenta de solo lectura, D-21).
 *
 * @see projects/mcp-servidor/specs/technical.md §4
 */
class McpDb
{
    /** @var DB\SQL|null Conexion memoizada (una por peticion) */
    private static ?DB\SQL $conn = null;

    /**
     * Devuelve la conexion unica del MCP, con el log de F3 apagado y limite de tiempo por sentencia.
     *
     * @return DB\SQL
     */
    public static function conn(): DB\SQL
    {
        if (self::$conn === null) {
            $db = Database::getSQLConnection(ConfigDataBase::MySQL_MCP);
            $db->log(false);
            $db->exec('SET SESSION max_statement_time = ' . (int) McpConfig::MAX_STATEMENT_SECONDS);
            self::$conn = $db;
        }
        return self::$conn;
    }
}
