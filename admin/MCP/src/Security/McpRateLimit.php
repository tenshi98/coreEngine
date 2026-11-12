<?php
defined('MCP_ROOT') || exit;
/**
 * Limite de peticiones del servidor MCP.
 *
 * @see projects/mcp-servidor/specs/technical.md §11
 */
class McpRateLimit
{
    /**
     * Comprueba el limite por IP (cuenta una peticion).
     *
     * @param string $ip IP del cliente
     * @return bool true si la IP esta bloqueada
     */
    public static function checkIp(string $ip): bool
    {
        return RateLimiter::isBlockedByIp('mcp-ip:' . $ip, McpConfig::RATE_IP_MAX, ConfigAPP::APP['rateLimiterGuestWindowSeconds']);
    }

    /**
     * Comprueba el limite por token (cuenta una peticion).
     *
     * @param int $idToken Id del token autenticado
     * @return bool true si el token esta bloqueado
     */
    public static function checkToken(int $idToken): bool
    {
        return RateLimiter::isBlockedByUser('mcp-token:' . $idToken, McpConfig::limit('RATE_TOKEN_MAX'), ConfigAPP::APP['rateLimiterUserWindowSeconds']);
    }

    /**
     * Indica si una clave pasa a bloqueada por primera vez en su ventana (audita cada 429 una sola vez, H-4, D-24).
     *
     * Guarda clave => timestamp de bloqueo en cache/ratelimit-transiciones.json (con flock) y descarta
     * las marcas vencidas. Si el archivo no puede abrirse devuelve false (no se audita) y lo registra.
     *
     * @param string $clave   Clave bloqueada (p. ej. mcp-ip:<ip>)
     * @param int    $ventana Ventana del limitador en segundos
     * @return bool true solo en la primera llamada de la clave dentro de la ventana
     */
    public static function isTransition(string $clave, int $ventana): bool
    {
        $fh = @fopen(MCP_ROOT . '/cache/ratelimit-transiciones.json', 'c+');
        if ($fh === false) {
            error_log('MCP: no se puede abrir ratelimit-transiciones.json');
            return false;
        }
        try {
            if (!flock($fh, LOCK_EX)) {
                return false;
            }
            $marcas = json_decode((string) stream_get_contents($fh), true);
            $marcas = is_array($marcas) ? $marcas : [];
            $ahora  = time();
            foreach ($marcas as $k => $ts) {
                if (!is_int($ts) || $ahora - $ts >= $ventana) {
                    unset($marcas[$k]);
                }
            }
            $nueva = !isset($marcas[$clave]);
            if ($nueva) {
                $marcas[$clave] = $ahora;
            }
            ftruncate($fh, 0);
            rewind($fh);
            fwrite($fh, json_encode($marcas));
            fflush($fh);
            flock($fh, LOCK_UN);
            return $nueva;
        } finally {
            fclose($fh);
        }
    }
}
