<?php
defined('MCP_ROOT') || exit;
/**
 * Verificacion del token manual pegado en /authorize y limites contra fuerza bruta.
 *
 * Solo consulta tokens_listado: el acceso y los permisos son del token. Ambos metodos son opacos a la causa
 * del rechazo (el endpoint muestra siempre "Credenciales no validas").
 *
 * @see projects/mcp-oauth/specs/technical.md §9.1
 */
class OAuthCredentials
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
     * Limita las consultas de token por IP y por token consultado (cuenta una peticion en cada clave).
     *
     * La clave por token es el SHA-256 del valor normalizado: el token nunca se escribe en el limitador.
     *
     * @param string $ipKey Clave de IP (OAuthHttp::ipKey)
     * @param string $token Token pegado por la persona
     * @return bool true si la IP (OAUTH_CRED_IP_MAX, ventana corta) o el token consultado (OAUTH_CRED_ID_MAX,
     *         ventana larga) estan bloqueados
     */
    public function throttled(string $ipKey, string $token): bool
    {
        return RateLimiter::isBlockedByIp('oauth-cred-ip:' . $ipKey, McpConfig::OAUTH_CRED_IP_MAX, ConfigAPP::APP['rateLimiterGuestWindowSeconds'])
            || RateLimiter::isBlockedByUser('oauth-cred-id:' . OAuthCrypto::hash(strtolower(trim($token))), McpConfig::OAUTH_CRED_ID_MAX, ConfigAPP::APP['rateLimiterUserWindowSeconds']);
    }

    /**
     * Verifica un token manual pegado en la pantalla de consentimiento.
     *
     * @param string $token Token pegado (se normaliza con trim y minusculas)
     * @return array{idToken: int, nombre: string}|null null ante cualquier motivo (formato, inexistente, no
     *         manual, inactivo o caducado), sin distinguirlo
     * @throws RuntimeException Si falla la base de datos
     */
    public function verifyToken(string $token): ?array
    {
        $t = strtolower(trim($token));
        if (preg_match('/^[a-f0-9]{64}$/D', $t) !== 1) {
            return null;
        }
        $r = $this->qb->queryRow([
            'data'   => 'idToken, Token, Nombre, idEstado, Origen, (FechaCaducidad IS NOT NULL AND FechaCaducidad < CURDATE()) AS Caducado',
            'table'  => 'tokens_listado',
            'where'  => 'Token = ?',
            'params' => [$t],
        ], $this->db);
        if (($r['status'] ?? false) !== true) {
            throw new RuntimeException('OAuth: fallo de base de datos');
        }
        $fila = $r['data'];
        if (!is_array($fila) || $fila === []
            || !hash_equals((string) ($fila['Token'] ?? ''), $t)
            || (int) $fila['Origen'] !== 1
            || (int) $fila['idEstado'] !== 1
            || (int) $fila['Caducado'] === 1) {
            return null;
        }
        return ['idToken' => (int) $fila['idToken'], 'nombre' => (string) $fila['Nombre']];
    }
}
