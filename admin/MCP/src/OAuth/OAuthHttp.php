<?php
defined('MCP_ROOT') || exit;
/**
 * Utilidades HTTP del servidor de autorizacion OAuth (respuestas, cuerpos, CORS, claves de IP).
 *
 * Todas las respuestas llevan las cabeceras comunes: Cache-Control no-store, Pragma no-cache,
 * X-Content-Type-Options nosniff y Referrer-Policy no-referrer.
 *
 * @see projects/mcp-oauth/specs/technical.md §5
 */
class OAuthHttp
{
    /**
     * Envia las cabeceras comunes de toda respuesta del AS.
     *
     * @return void
     */
    private static function commonHeaders(): void
    {
        header('Cache-Control: no-store');
        header('Pragma: no-cache');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: no-referrer');
    }

    /**
     * Envia una respuesta JSON y termina.
     *
     * Un cuerpo vacio se envia como `{}`.
     *
     * @param int                  $status  Codigo HTTP
     * @param array<string,mixed>  $body    Cuerpo
     * @param array<string,string> $headers Cabeceras adicionales [nombre => valor]
     * @return never
     */
    public static function json(int $status, array $body, array $headers = []): never
    {
        http_response_code($status);
        self::commonHeaders();
        header('Content-Type: application/json; charset=utf-8');
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $body === [] ? '{}' : json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }

    /**
     * Envia un error JSON de RFC 6749 §5.2 y termina.
     *
     * La descripcion se reduce a ASCII imprimible sin `"` ni `\` (RFC 6749 §5.2); vacia, se omite.
     *
     * @param int                  $status      Codigo HTTP
     * @param string               $code        Codigo de error OAuth
     * @param string               $description Descripcion legible (opcional)
     * @param array<string,string> $headers     Cabeceras adicionales
     * @return never
     */
    public static function error(int $status, string $code, string $description = '', array $headers = []): never
    {
        $description = (string) preg_replace('/[^\x20-\x7E]|["\\\\]/', '', $description);
        self::json($status, ['error' => $code] + ($description !== '' ? ['error_description' => $description] : []), $headers);
    }

    /**
     * Redirige (302) a una URI de cliente anadiendo parametros en la query y termina.
     *
     * Quita las claves con valor null. $uri es SIEMPRE la URI efectiva resuelta desde la registrada
     * (InternalFunctionsTokensOAuth::redirectUriResolve), nunca la recibida.
     *
     * @param string                    $uri    URI efectiva
     * @param array<string,string|null> $params Parametros de la query
     * @return never
     */
    public static function redirect(string $uri, array $params): never
    {
        $params = array_filter($params, static fn($v) => $v !== null);
        $sep    = str_contains($uri, '?') ? '&' : '?';
        http_response_code(302);
        header('Location: ' . $uri . $sep . http_build_query($params, '', '&', PHP_QUERY_RFC3986));
        self::commonHeaders();
        exit;
    }

    /**
     * Analiza una cadena application/x-www-form-urlencoded (RFC 6749 §3.1: parametros no repetidos).
     *
     * @param string $raw Cuerpo o query sin procesar
     * @return array<string,string>|null null si una clave se repite
     */
    public static function parseParams(string $raw): ?array
    {
        if ($raw === '') {
            return [];
        }
        $out = [];
        foreach (explode('&', $raw) as $par) {
            $partes = explode('=', $par, 2);
            $clave  = urldecode($partes[0]);
            $valor  = urldecode($partes[1] ?? '');
            if ($clave === '') {
                continue;
            }
            if (array_key_exists($clave, $out)) {
                return null;
            }
            $out[$clave] = $valor;
        }
        return $out;
    }

    /**
     * Lee el cuerpo de la peticion con el limite OAUTH_MAX_BODY_BYTES (compartido por formBody y jsonBody).
     *
     * @param string $errorCode Codigo de error del 413
     * @return string Cuerpo sin procesar
     */
    private static function readBody(string $errorCode): string
    {
        $limite = McpConfig::OAUTH_MAX_BODY_BYTES;
        if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > $limite) {
            self::error(413, $errorCode, 'Body too large');
        }
        $raw = file_get_contents('php://input', false, null, 0, $limite + 1);
        $raw = $raw === false ? '' : $raw;
        if (strlen($raw) > $limite) {
            self::error(413, $errorCode, 'Body too large');
        }
        return $raw;
    }

    /**
     * Cabecera Content-Type de la peticion (CONTENT_TYPE o HTTP_CONTENT_TYPE).
     *
     * @return string
     */
    private static function contentType(): string
    {
        return (string) ($_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '');
    }

    /**
     * Cuerpo application/x-www-form-urlencoded de un POST del AS, ya analizado.
     *
     * Termina con 400 si el Content-Type no es el esperado o hay parametros repetidos y con 413 si supera
     * OAUTH_MAX_BODY_BYTES.
     *
     * @return array<string,string>
     */
    public static function formBody(): array
    {
        if (stripos(self::contentType(), 'application/x-www-form-urlencoded') !== 0) {
            self::error(400, 'invalid_request', 'Content-Type must be application/x-www-form-urlencoded');
        }
        $params = self::parseParams(self::readBody('invalid_request'));
        if ($params === null) {
            self::error(400, 'invalid_request', 'Repeated parameter');
        }
        return $params;
    }

    /**
     * Cuerpo JSON (objeto) de un POST del AS, ya decodificado (RFC 7591).
     *
     * Termina con 400 invalid_client_metadata si el Content-Type no es application/json o el JSON no es un
     * objeto (profundidad maxima 16) y con 413 si supera OAUTH_MAX_BODY_BYTES.
     *
     * @return array<string,mixed>
     */
    public static function jsonBody(): array
    {
        if (stripos(self::contentType(), 'application/json') !== 0) {
            self::error(400, 'invalid_client_metadata', 'Content-Type must be application/json');
        }
        $datos = json_decode(self::readBody('invalid_client_metadata'), true, 16);
        if (!is_array($datos) || ($datos !== [] && array_is_list($datos))) {
            self::error(400, 'invalid_client_metadata', 'Invalid JSON object');
        }
        return $datos;
    }

    /**
     * Pinta una vista HTML propia del AS y termina.
     *
     * CSP default-src 'none'; style-src 'unsafe-inline'; frame-ancestors 'none'; base-uri 'none' (sin form-action:
     * el POST termina en una redireccion a otro origen; sin img-src: el AS no pinta imagenes de terceros, H-11).
     *
     * @param string              $vista  'consent' o 'error' (lista blanca; otra termina en oauth_fail())
     * @param array<string,mixed> $data   Datos de la vista (quedan en ambito como $data)
     * @param int                 $status Codigo HTTP
     * @return never
     */
    public static function html(string $vista, array $data, int $status = 200): never
    {
        if (!in_array($vista, ['consent', 'error'], true)) {
            oauth_fail();
        }
        http_response_code($status);
        self::commonHeaders();
        header('Content-Type: text/html; charset=utf-8');
        header('X-Frame-Options: DENY');
        header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; frame-ancestors 'none'; base-uri 'none'");
        require MCP_ROOT . '/oauth/views/' . $vista . '.php';
        exit;
    }

    /**
     * Aplica CORS segun el modo (D-17). Nunca envia Access-Control-Allow-Credentials.
     *
     * - publico: Access-Control-Allow-Origin: *
     * - restringido: solo si HTTP_ORIGIN esta en McpConfig::allowedOrigins() (origen exacto + Vary: Origin)
     * - ninguno: nada
     * Con CORS aplicado y metodo OPTIONS anade Allow-Methods, Allow-Headers y Max-Age.
     *
     * @param string $modo 'publico', 'restringido' o 'ninguno'
     * @return void
     */
    public static function cors(string $modo): void
    {
        if ($modo === 'publico') {
            header('Access-Control-Allow-Origin: *');
        } elseif ($modo === 'restringido') {
            $origin = $_SERVER['HTTP_ORIGIN'] ?? null;
            if (!is_string($origin) || !in_array($origin, McpConfig::allowedOrigins(), true)) {
                return;
            }
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Vary: Origin');
        } else {
            return;
        }
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers: Authorization, Content-Type, MCP-Protocol-Version');
            header('Access-Control-Max-Age: 600');
        }
    }

    /**
     * Clave de IP para el limitador y el conteo de registros (H-18).
     *
     * IPv4 valida: la misma. IPv6 valida: primeros 8 bytes de inet_pton en hex + '::/64'. Otra: 'invalida'.
     *
     * @param string $ip IP del cliente (REMOTE_ADDR)
     * @return string Clave
     */
    public static function ipKey(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            return $ip;
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
            $bin = inet_pton($ip);
            if ($bin !== false) {
                return bin2hex(substr($bin, 0, 8)) . '::/64';
            }
        }
        return 'invalida';
    }

    /**
     * Rechaza con 400 las peticiones con secretos en la query (H-19): client_secret, code, code_verifier,
     * refresh_token o token como nombre de parametro. Lo usan /token y /revoke.
     *
     * @return void
     */
    public static function forbidQuerySecrets(): void
    {
        $query = (string) ($_SERVER['QUERY_STRING'] ?? '');
        if ($query === '') {
            return;
        }
        foreach (explode('&', $query) as $par) {
            $nombre = urldecode(explode('=', $par, 2)[0]);
            if (in_array($nombre, ['client_secret', 'code', 'code_verifier', 'refresh_token', 'token'], true)) {
                self::error(400, 'invalid_request', 'Credentials in query string');
            }
        }
    }
}
