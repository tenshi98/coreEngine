<?php
defined('MCP_ROOT') || exit;
/**
 * Auditoria del servidor MCP en tokens_mcp_log.
 *
 * @see projects/mcp-servidor/specs/technical.md §11
 */
class McpAuditLog
{
    /** @var DB\SQL Conexion MySQL_MCP (INSERT en tokens_mcp_log) */
    private $db;

    /** @var QueryBuilder Constructor de consultas */
    private QueryBuilder $qb;

    public function __construct()
    {
        $this->db = McpDb::conn();
        $this->qb = new QueryBuilder();
    }

    /**
     * Registra un mensaje del MCP en tokens_mcp_log (INSERT suelto, fuera de transaccion).
     *
     * Metodo y herramienta se reducen a 64 ASCII imprimibles (D-26, H-17). Los argumentos se
     * guardan en ASCII (\uXXXX), con toda secuencia de 64 hex redactada y truncados (H-17).
     * Un fallo solo se registra con error_log, sin datos del mensaje ni el token.
     *
     * @param int         $idToken     Token autenticado (0 si no se identifico)
     * @param string      $metodo      Metodo JSON-RPC, o "http" para rechazos previos al parseo
     * @param string|null $herramienta Nombre de la tool o URI del resource
     * @param mixed       $argumentos  Argumentos del mensaje (null = sin argumentos)
     * @param string      $resultado   "ok", codigo JSON-RPC o rechazo HTTP (p. ej. 401-inactivo)
     * @param string      $ip          REMOTE_ADDR
     * @param int|null    $idClienteOAuth Cliente OAuth del acceso (null si el token es manual o no se identifico)
     */
    public function record(int $idToken, string $metodo, ?string $herramienta, mixed $argumentos, string $resultado, string $ip, ?int $idClienteOAuth = null): void
    {
        try {
            $args = null;
            if ($argumentos !== null) {
                $json = json_encode($argumentos);
                if ($json !== false) {
                    $args = substr(self::redact($json), 0, McpConfig::MAX_LOG_ARGS_CHARS);
                }
            }
            $post = [
                'idToken'     => (string) $idToken,
                'Metodo'      => self::ascii64($metodo),
                'Herramienta' => self::ascii64($herramienta),
                'Argumentos'  => $args,
                'Resultado'   => substr(self::redact($resultado), 0, 32),
                'IP'          => substr(self::redact($ip), 0, 45),
                'Fecha'       => date('Y-m-d H:i:s'),
            ];
            // Sin cliente la clave no se inserta (queryInsert omite null) y la columna queda NULL, como hasta ahora
            if ($idClienteOAuth !== null) {
                $post['idClienteOAuth'] = (string) $idClienteOAuth;
            }
            $r = $this->qb->queryInsert([
                'data'  => 'idToken,Metodo,Herramienta,Argumentos,Resultado,IP,Fecha,idClienteOAuth',
                'table' => 'tokens_mcp_log',
                'Post'  => $post,
            ], $this->db, false, true);
            if (($r['status'] ?? false) !== true) {
                error_log('MCP: no se pudo registrar la auditoria');
            }
        } catch (Throwable $e) {
            error_log('MCP: no se pudo registrar la auditoria');
        }
    }

    /**
     * Reduce un texto a ASCII imprimible y 64 caracteres.
     *
     * @param string|null $v Texto
     * @return string|null null si la entrada es null
     */
    private static function ascii64(?string $v): ?string
    {
        if ($v === null) {
            return null;
        }
        return substr(self::redact(preg_replace('/[^\x20-\x7E]/', '?', $v)), 0, 64);
    }

    /**
     * Redacta toda secuencia de 32 o mas hex (el token mide 64; se cubren fragmentos largos).
     * Se aplica antes de truncar, para que un corte no deje un fragmento del token.
     * Es mas estricta que specs/security.md §2.8 (solo 64 hex y solo en Argumentos).
     *
     * @param string $v Texto
     * @return string Texto con las secuencias redactadas
     */
    private static function redact(string $v): string
    {
        return preg_replace('/[A-Fa-f0-9]{32,}/', '[redactado]', $v) ?? '[redactado]';
    }
}
