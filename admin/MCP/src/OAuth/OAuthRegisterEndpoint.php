<?php
defined('MCP_ROOT') || exit;
/**
 * Endpoint de registro dinamico de clientes OAuth (RFC 7591): POST <issuer>/register.
 *
 * Valida los metadatos (redirect_uri en lista blanca, scopes, nombre saneado), aplica los limites de registro
 * (D-3) y devuelve 201 con todos los metadatos registrados. El cliente queda activo y sin asignacion previa.
 * Una sola sentencia INSERT: sin transaccion. Nunca se registran secretos.
 *
 * @see projects/mcp-oauth/specs/technical.md §13.0, §13.1
 */
class OAuthRegisterEndpoint
{
    /** @var OAuthClientRepository Repositorio de clientes */
    private OAuthClientRepository $repo;

    /** @var OAuthCleanup Limpieza de caducados (se ejecuta siempre antes de contar los clientes sin uso) */
    private OAuthCleanup $cleanup;

    /**
     * Crea el endpoint con los repositorios que usa (conexion unica McpDb::conn()).
     */
    public function __construct()
    {
        $this->repo    = new OAuthClientRepository();
        $this->cleanup = new OAuthCleanup();
    }

    /**
     * Comprueba que un valor es una cadena sin caracteres de control (\p{Cc}) y de longitud maxima.
     *
     * @param mixed $v   Valor recibido
     * @param int   $max Longitud maxima en caracteres
     * @return bool true si es valido
     */
    private function textoSimple(mixed $v, int $max): bool
    {
        return is_string($v)
            && $v !== ''
            && mb_check_encoding($v, 'UTF-8')
            && mb_strlen($v, 'UTF-8') <= $max
            && preg_match('/\p{Cc}/u', $v) !== 1;
    }

    /**
     * Comprueba una URL https de metadatos (client_uri, logo_uri): solo se guarda como texto (H-11).
     *
     * @param mixed $v Valor recibido
     * @return bool true si es una cadena https de hasta 2000 caracteres, con host y sin espacios ni controles
     */
    private function urlHttps(mixed $v): bool
    {
        if (!is_string($v) || $v === '' || strlen($v) > InternalFunctionsTokensOAuth::MAX_URI_CHARS
            || preg_match('/[\x00-\x20\x7F]/', $v) === 1) {
            return false;
        }
        $p = parse_url($v);
        return is_array($p) && ($p['scheme'] ?? '') === 'https' && ($p['host'] ?? '') !== '';
    }

    /**
     * Sanea el nombre declarado del cliente (D-23): sin \p{Cc}, \p{Cf} ni \p{Co}, representable en latin1
     * (lo no representable pasa a "?"), recortado y con valor por defecto si queda vacio.
     *
     * @param string $nombre Nombre UTF-8 valido
     * @return string Nombre saneado (no vacio, hasta 255 caracteres tras sanear)
     */
    private function sanearNombre(string $nombre): string
    {
        $nombre = (string) preg_replace('/[\p{Cc}\p{Cf}\p{Co}]/u', '', $nombre);
        $previo = mb_substitute_character();
        mb_substitute_character(63);
        $latin1 = mb_convert_encoding($nombre, 'ISO-8859-1', 'UTF-8');
        $nombre = mb_convert_encoding($latin1, 'UTF-8', 'ISO-8859-1');
        mb_substitute_character($previo);
        $nombre = trim($nombre);
        return $nombre === '' ? 'Cliente sin nombre' : $nombre;
    }

    /**
     * Valida los metadatos de registro (RFC 7591) y devuelve los que se registran.
     *
     * Reglas en orden: redirect_uris (1..MAX_URIS, sin duplicados, todas permitidas: invalid_redirect_uri);
     * token_endpoint_auth_method (defecto none, D-5); grant_types; response_types; software_id y
     * software_version; scope (los valores desconocidos se ignoran y se registra "mcp", D-10); client_name
     * (saneado, D-23); client_uri y logo_uri (https, solo texto). Los demas campos se ignoran.
     *
     * @param array<string,mixed> $m Metadatos recibidos (objeto JSON)
     * @return array{ok: true, data: array<string,mixed>}|array{ok: false, error: string, descripcion: string}
     *         data lleva las claves de RFC 7591 listas para OAuthClientRepository::register
     */
    public function validateMetadata(array $m): array
    {
        $fallo = static fn(string $error, string $descripcion): array => ['ok' => false, 'error' => $error, 'descripcion' => $descripcion];

        // redirect_uris
        $uris = $m['redirect_uris'] ?? null;
        if (!is_array($uris) || !array_is_list($uris) || count($uris) < 1 || count($uris) > InternalFunctionsTokensOAuth::MAX_URIS) {
            return $fallo('invalid_redirect_uri', 'redirect_uris must be a list of 1 to ' . InternalFunctionsTokensOAuth::MAX_URIS . ' URIs');
        }
        foreach ($uris as $u) {
            if (!is_string($u) || !InternalFunctionsTokensOAuth::redirectUriAllowed($u)) {
                return $fallo('invalid_redirect_uri', 'redirect_uri not allowed');
            }
        }
        if (count(array_unique($uris)) !== count($uris)) {
            return $fallo('invalid_redirect_uri', 'Duplicated redirect_uri');
        }

        // token_endpoint_auth_method (defecto none: desviacion de RFC 7591 §2, D-5)
        $metodo = $m['token_endpoint_auth_method'] ?? 'none';
        if (!is_string($metodo) || !in_array($metodo, ['none', 'client_secret_post', 'client_secret_basic'], true)) {
            return $fallo('invalid_client_metadata', 'Unsupported token_endpoint_auth_method');
        }

        // grant_types
        $grants = $m['grant_types'] ?? ['authorization_code', 'refresh_token'];
        if (!is_array($grants) || !array_is_list($grants)) {
            return $fallo('invalid_client_metadata', 'grant_types must be a list');
        }
        foreach ($grants as $g) {
            if (!is_string($g) || !in_array($g, ['authorization_code', 'refresh_token'], true)) {
                return $fallo('invalid_client_metadata', 'Unsupported grant_types');
            }
        }
        $grants = array_values(array_unique($grants));
        if (!in_array('authorization_code', $grants, true)) {
            return $fallo('invalid_client_metadata', 'grant_types must include authorization_code');
        }

        // response_types
        $tipos = $m['response_types'] ?? ['code'];
        if ($tipos !== ['code']) {
            return $fallo('invalid_client_metadata', 'response_types must be code');
        }

        // software_id, software_version
        $data = [];
        foreach (['software_id', 'software_version'] as $k) {
            if (array_key_exists($k, $m)) {
                if (!$this->textoSimple($m[$k], 255)) {
                    return $fallo('invalid_client_metadata', 'Invalid ' . $k);
                }
                $data[$k] = $m[$k];
            }
        }

        // scope: solo se valida el tipo; los valores desconocidos se ignoran y se registra "mcp" (D-10)
        if (array_key_exists('scope', $m) && !is_string($m['scope'])) {
            return $fallo('invalid_client_metadata', 'scope must be a string');
        }

        // client_name (D-23)
        $nombre = $m['client_name'] ?? '';
        if (!is_string($nombre) || !mb_check_encoding($nombre, 'UTF-8')) {
            return $fallo('invalid_client_metadata', 'client_name must be a UTF-8 string');
        }
        $nombre = $this->sanearNombre($nombre);
        if (mb_strlen($nombre, 'UTF-8') > 255) {
            return $fallo('invalid_client_metadata', 'client_name too long');
        }

        // client_uri, logo_uri: texto, nunca se descargan ni se pintan como enlace o imagen (H-11)
        foreach (['client_uri', 'logo_uri'] as $k) {
            if (array_key_exists($k, $m)) {
                if (!$this->urlHttps($m[$k])) {
                    return $fallo('invalid_client_metadata', 'Invalid ' . $k);
                }
                $data[$k] = $m[$k];
            }
        }

        return ['ok' => true, 'data' => [
            'client_name'                => $nombre,
            'redirect_uris'              => $uris,
            'token_endpoint_auth_method' => $metodo,
            'grant_types'                => $grants,
            'response_types'             => ['code'],
            'scope'                      => McpConfig::OAUTH_SCOPE,
        ] + $data];
    }

    /**
     * Responde 429 con Retry-After (ventana de invitados) y termina.
     *
     * @return never
     */
    private function tooMany(): never
    {
        OAuthHttp::error(429, 'temporarily_unavailable', 'Too many requests', [
            'Retry-After' => (string) (int) ConfigAPP::APP['rateLimiterGuestWindowSeconds'],
        ]);
    }

    /**
     * Atiende POST /register.
     *
     * Orden: limite oauth-ip; oauth-reg; registros de la IP en 24 h; limpieza; tope de clientes sin uso;
     * cuerpo JSON; validacion; alta; 201. Un fallo de base de datos responde 500 server_error sin detalle.
     *
     * @return never
     */
    public function handle(): never
    {
        try {
            $ip    = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
            $clave = OAuthHttp::ipKey($ip);
            $ventana = (int) ConfigAPP::APP['rateLimiterGuestWindowSeconds'];

            if (RateLimiter::isBlockedByIp('oauth-ip:' . $clave, McpConfig::OAUTH_IP_MAX, $ventana)
                || RateLimiter::isBlockedByIp('oauth-reg:' . $clave, McpConfig::OAUTH_REG_MAX, $ventana)
                || $this->repo->countRecentByIp($clave) >= McpConfig::OAUTH_REG_IP_DIA) {
                $this->tooMany();
            }
            $this->cleanup->maybeRun(true);
            if ($this->repo->countUnused() >= McpConfig::OAUTH_UNUSED_MAX) {
                $this->tooMany();
            }

            $v = $this->validateMetadata(OAuthHttp::jsonBody());
            if ($v['ok'] !== true) {
                OAuthHttp::error(400, $v['error'], $v['descripcion']);
            }
            $reg = $this->repo->register($v['data'], $ip);

            $resp = ['client_id' => $reg['client_id'], 'client_id_issued_at' => $reg['client_id_issued_at']] + $v['data'];
            if ($reg['client_secret'] !== null) {
                $resp['client_secret']            = $reg['client_secret'];
                $resp['client_secret_expires_at'] = 0;
            }
            OAuthHttp::json(201, $resp);
        } catch (Throwable $e) {
            error_log('OAuth: ' . get_class($e));
            OAuthHttp::error(500, 'server_error');
        }
    }
}
