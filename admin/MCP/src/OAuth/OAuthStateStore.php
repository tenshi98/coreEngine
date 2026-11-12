<?php
defined('MCP_ROOT') || exit;
/**
 * Estado del flujo /authorize en una sesion PHP PROPIA del AS (cookie coreengine_oauth).
 *
 * No usa ni lee ninguna sesion del backoffice: la sesion lleva el marcador __oauth_as y, si no lo trae, se
 * reinicia. Guarda un mapa de peticiones pendientes por rid, cada una con su CSRF.
 *
 * @see projects/mcp-oauth/specs/technical.md §9.2
 */
class OAuthStateStore
{
    /**
     * Inicia la sesion propia del AS.
     *
     * Sin sesion activa: modo estricto, nombre OAUTH_SESSION_NAME y cookie con path = ruta del issuer + '/',
     * secure si el issuer es https, httponly y SameSite Lax. Una sesion sin el marcador __oauth_as se reinicia
     * (nunca se reutilizan datos de otra sesion).
     *
     * @param bool $regenerar true para regenerar el id de sesion (lo pide el start() del endpoint, B-3)
     * @return void
     * @throws RuntimeException Si no hay issuer configurado (el endpoint ya responde 503 antes)
     */
    public static function start(bool $regenerar): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            $issuer = McpConfig::issuerUrl();
            if ($issuer === null) {
                throw new RuntimeException('OAuth: sesion sin issuer configurado');
            }
            ini_set('session.use_strict_mode', '1');
            session_name(McpConfig::OAUTH_SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => (string) parse_url($issuer, PHP_URL_PATH) . '/',
                'secure'   => parse_url($issuer, PHP_URL_SCHEME) === 'https',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
        if (!isset($_SESSION['__oauth_as'])) {
            $_SESSION = ['__oauth_as' => 1];
        }
        if ($regenerar) {
            session_regenerate_id(true);
        }
    }

    /**
     * Guarda una peticion de autorizacion pendiente.
     *
     * Conserva como maximo OAUTH_PENDING_MAX peticiones: descarta las mas antiguas.
     *
     * @param array<string,mixed> $req Peticion validada (client_id, redirect_uri efectiva, code_challenge, ...)
     * @return string rid de 128 bits (32 hexadecimales)
     */
    public static function savePending(array $req): string
    {
        $rid = OAuthCrypto::randomHex(16);
        $_SESSION['oauth_pending'][$rid] = $req + ['creado' => time(), 'fallos' => 0, 'csrf' => OAuthCrypto::randomHex(32)];
        $pendientes = $_SESSION['oauth_pending'];
        if (count($pendientes) > McpConfig::OAUTH_PENDING_MAX) {
            // Orden estable por 'creado': a igual segundo se descarta primero la insertada antes
            uasort($pendientes, static fn(array $a, array $b): int => ($a['creado'] ?? 0) <=> ($b['creado'] ?? 0));
            foreach (array_slice(array_keys($pendientes), 0, count($pendientes) - McpConfig::OAUTH_PENDING_MAX) as $viejo) {
                unset($_SESSION['oauth_pending'][$viejo]);
            }
        }
        return $rid;
    }

    /**
     * Devuelve una peticion pendiente si existe y no ha caducado.
     *
     * @param string $rid rid de la peticion
     * @return array<string,mixed>|null La peticion; null si el formato es invalido, no existe o caduco
     *         (la caducada se borra)
     */
    public static function pending(string $rid): ?array
    {
        if (preg_match('/^[a-f0-9]{32}$/D', $rid) !== 1) {
            return null;
        }
        $p = $_SESSION['oauth_pending'][$rid] ?? null;
        if (is_array($p) && time() - (int) ($p['creado'] ?? 0) <= McpConfig::OAUTH_PENDING_SECONDS) {
            return $p;
        }
        unset($_SESSION['oauth_pending'][$rid]);
        return null;
    }

    /**
     * Devuelve el token CSRF de una peticion.
     *
     * @param string $rid rid de la peticion
     * @return string CSRF (64 hexadecimales); cadena vacia si la peticion no existe
     */
    public static function csrfToken(string $rid): string
    {
        $csrf = $_SESSION['oauth_pending'][$rid]['csrf'] ?? '';
        return is_string($csrf) ? $csrf : '';
    }

    /**
     * Comprueba el CSRF de una peticion con hash_equals.
     *
     * @param string $rid rid de la peticion
     * @param string $t   Token recibido
     * @return bool true solo si la peticion existe y el token coincide
     */
    public static function checkCsrf(string $rid, string $t): bool
    {
        $csrf = self::csrfToken($rid);
        return $csrf !== '' && hash_equals($csrf, $t);
    }

    /**
     * Registra un intento fallido en la peticion.
     *
     * @param string $rid rid de la peticion
     * @return int Fallos acumulados; al llegar a OAUTH_CRED_RID_MAX la peticion se descarta. Una peticion
     *         inexistente devuelve OAUTH_CRED_RID_MAX (ya descartada)
     */
    public static function fail(string $rid): int
    {
        if (!isset($_SESSION['oauth_pending'][$rid]) || !is_array($_SESSION['oauth_pending'][$rid])) {
            return McpConfig::OAUTH_CRED_RID_MAX;
        }
        $fallos = (int) ($_SESSION['oauth_pending'][$rid]['fallos'] ?? 0) + 1;
        $_SESSION['oauth_pending'][$rid]['fallos'] = $fallos;
        if ($fallos >= McpConfig::OAUTH_CRED_RID_MAX) {
            self::clear($rid);
        }
        return $fallos;
    }

    /**
     * Descarta una peticion pendiente.
     *
     * @param string $rid rid de la peticion
     * @return void
     */
    public static function clear(string $rid): void
    {
        unset($_SESSION['oauth_pending'][$rid]);
    }
}
