<?php
PHP_SAPI === 'cli' || exit;
/**
 * Script de humo del servidor MCP de coreEngine (RF-020).
 *
 * Esqueleto: configuracion por entorno, cliente HTTP seguro, aserciones y resumen.
 * Los casos de prueba se anaden entre el bloque "Casos" y el resumen final.
 * Nunca imprime cabeceras ni tokens.
 *
 * Variables de entorno: MCP_URL y los tokens/objetivos de los casos (MCP_TOKEN_OK, MCP_TOKEN_INACTIVO,
 * MCP_TOKEN_CADUCADO, MCP_TOKEN_SIN_ALCANCE, MCP_TOKEN_USUARIO_INACTIVO, MCP_TOKEN_MODULO_NO_EXPONIBLE,
 * MCP_TOKEN_RATE, MCP_TOKEN_RATE_LOTE, MCP_MODULO_FUERA, MCP_TABLA_FUERA, MCP_MODULO_NO_EXPONIBLE, MCP_MODULO_SENSIBLE,
 * MCP_COLUMNA_SENSIBLE, MCP_COLUMNA_PII, MCP_TABLA_PAGINA, MCP_COLUMNA_SIN_INDICE, MCP_TABLA_GRANDE,
 * MCP_TOKEN_UN_MODULO, MCP_MODULO_UN_MODULO, MCP_TABLA_DEL_MODULO, MCP_TABLA_USUARIO,
 * MCP_TOKEN_ENTIDADES, MCP_MODULO_ENTIDADES, MCP_TOKEN_VEHICULOS, MCP_TOKEN_TERCEROS, MCP_MODULO_TERCEROS;
 * opcional MCP_SIN_GRANT=1 para la parte degradada del caso 24). El servidor de desarrollo debe arrancar con
 * MCP_RATE_TOKEN_MAX=50 y MCP_MAX_RESPONSE_BYTES menor que el tamano de list_records de MCP_TABLA_GRANDE con limit 100
 * (McpConfig::limit solo permite bajar). Los limites por IP (120 por 60 s) obligan a esperar una ventana antes del caso 21.
 * Un caso "omitido" (parte que exige una precondicion del operador) no cuenta como OK ni como FALLO.
 *
 * Casos 28-41 (servidor de autorizacion OAuth, projects/mcp-oauth): MCP_PUBLIC_URL (la misma del servidor, sin ella se omiten
 * 28-31, 32 y 37-41), MCP_OAUTH_TOKEN (33), MCP_OAUTH_TOKEN_EXPIRADO (34), MCP_OAUTH_TOKEN_REVOCAR (35, un solo uso: queda revocado),
 * MCP_OAUTH_REFRESH_USADO (36, refresh token ya usado), MCP_OAUTH_CLIENT_ID (35 y 36: cliente dueno de esos tokens) y
 * MCP_OAUTH_CLIENT_SECRET (opcional: solo si ese cliente es confidencial con client_secret_post). Un caso OAuth sin su variable se omite.
 * Los casos 31, 32, 40 y 41 registran clientes dinamicos con /register (limite de 3 por minuto y 20 por IP en 24 h): el script espera
 * entre ventanas y, al final, borra por ClientId los que creo con McpDb::conn() si hay pdo_mysql; sin esa extension los deja a la purga de 24 h.
 *
 * @see projects/mcp-servidor/specs/functional.md §6
 */

$GLOBALS['selftest'] = ['ok' => 0, 'fallo' => 0, 'omitido' => 0];

/**
 * Valor de una variable de entorno de configuracion del script.
 *
 * @param string $nombre Nombre de la variable (p. ej. MCP_URL)
 * @return string Valor o cadena vacia si no esta definida
 */
function selftest_env(string $nombre): string
{
    $v = getenv($nombre);
    return $v === false ? '' : $v;
}

/**
 * Valida MCP_URL: sin query string ni fragmento, https salvo localhost/127.0.0.1/::1 (H-11).
 *
 * @return string|null Motivo del rechazo (sin datos sensibles) o null si es valida
 */
function selftest_url_error(): ?string
{
    $url = selftest_env('MCP_URL');
    if ($url === '') {
        return 'falta MCP_URL';
    }
    if (strpos($url, '?') !== false || strpos($url, '#') !== false) {
        return 'MCP_URL no puede llevar query string ni fragmento';
    }
    $p = parse_url($url);
    if ($p === false || !isset($p['scheme'], $p['host'])) {
        return 'MCP_URL no es una URL valida';
    }
    $esquema = strtolower($p['scheme']);
    $host    = strtolower(trim($p['host'], '[]'));
    if ($esquema === 'https') {
        return null;
    }
    if ($esquema === 'http' && in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
        return null;
    }
    return 'MCP_URL debe usar https (http solo para localhost, 127.0.0.1 o ::1)';
}

/**
 * Envia una peticion HTTP al endpoint MCP.
 *
 * No sigue redirecciones y verifica el certificado (H-11). No imprime cabeceras ni tokens.
 *
 * @param string     $metodo  Metodo HTTP (POST, GET...)
 * @param array|null $msg     Mensaje JSON-RPC (o lote) como cuerpo; null = sin cuerpo
 * @param array      $headers Cabeceras extra como lineas "Nombre: valor" (p. ej. Authorization)
 * @param string     $query   Query string opcional sin el "?" inicial (para casos negativos)
 * @return array{status: int, headers: array<string,string>, body: string, json: mixed, error: ?string}
 */
function selftest_request(string $metodo, ?array $msg, array $headers, string $query = ''): array
{
    $vacio = ['status' => 0, 'headers' => [], 'body' => '', 'json' => null, 'error' => null];
    $motivo = selftest_url_error();
    if ($motivo !== null) {
        return ['error' => $motivo] + $vacio;
    }
    $url  = selftest_env('MCP_URL') . ($query !== '' ? '?' . $query : '');
    $http = [
        'method'          => $metodo,
        'header'          => implode("\r\n", array_merge(['Content-Type: application/json', 'Accept: application/json, text/event-stream'], $headers)),
        'ignore_errors'   => true,
        'timeout'         => 30,
        'follow_location' => 0,
        'max_redirects'   => 0,
    ];
    if ($msg !== null) {
        $http['content'] = json_encode($msg);
    }
    $ctx  = stream_context_create(['http' => $http, 'ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]);
    $body = @file_get_contents($url, false, $ctx);
    if ($body === false && empty($http_response_header)) {
        return ['error' => 'sin respuesta del servidor'] + $vacio;
    }
    $status = 0;
    $cab    = [];
    foreach ($http_response_header ?? [] as $linea) {
        if (preg_match('#^HTTP/\S+\s+(\d{3})#', $linea, $m) === 1) {
            $status = (int) $m[1];
            $cab    = [];
        } elseif (strpos($linea, ':') !== false) {
            [$k, $v] = explode(':', $linea, 2);
            $cab[strtolower(trim($k))] = trim($v);
        }
    }
    $body = $body === false ? '' : $body;
    return ['status' => $status, 'headers' => $cab, 'body' => $body, 'json' => json_decode($body, true), 'error' => null];
}

/**
 * Envia un mensaje JSON-RPC por POST al endpoint MCP.
 *
 * @param array  $msg     Mensaje JSON-RPC (o lote) a enviar como cuerpo
 * @param array  $headers Cabeceras extra como lineas "Nombre: valor" (p. ej. Authorization)
 * @param string $query   Query string opcional sin el "?" inicial
 * @return array{status: int, headers: array<string,string>, body: string, json: mixed, error: ?string}
 */
function selftest_post(array $msg, array $headers, string $query = ''): array
{
    return selftest_request('POST', $msg, $headers, $query);
}

/**
 * Registra el resultado de un caso y lo imprime (sin datos sensibles).
 *
 * @param string $caso    Identificador del caso
 * @param bool   $ok      true si la observacion coincide con lo esperado
 * @param string $detalle Esperado vs. real (nunca tokens ni cabeceras)
 */
function selftest_check(string $caso, bool $ok, string $detalle): void
{
    $GLOBALS['selftest'][$ok ? 'ok' : 'fallo']++;
    echo ($ok ? 'OK    ' : 'FALLO ') . $caso . ($detalle !== '' ? ' - ' . $detalle : '') . PHP_EOL;
}

/**
 * Anota una parte de un caso que no se ejecuta por falta de una precondicion del operador (no cuenta como OK ni FALLO).
 *
 * @param string $caso   Identificador del caso
 * @param string $motivo Precondicion que falta
 */
function selftest_omit(string $caso, string $motivo): void
{
    $GLOBALS['selftest']['omitido']++;
    echo 'OMITIDO ' . $caso . ' - ' . $motivo . PHP_EOL;
}

/**
 * Imprime el resumen y sale con 0 si no hubo fallos, 1 si hubo alguno.
 */
function selftest_summary(): void
{
    $s = $GLOBALS['selftest'];
    echo $s['ok'] . ' OK, ' . $s['fallo'] . ' FALLO' . ($s['omitido'] > 0 ? ', ' . $s['omitido'] . ' omitidos' : '') . PHP_EOL;
    exit($s['fallo'] > 0 ? 1 : 0);
}

/**********************************************************************************************************************************/
/*                                                  Ayudas de los casos                                                           */
/**********************************************************************************************************************************/

/**
 * Obtiene las variables de entorno que necesita un caso; si falta alguna, el caso cuenta como FALLO.
 *
 * @param string   $caso  Identificador del caso
 * @param string[] $vars  Nombres de variable requeridos
 * @return array<string,string>|null Valores por nombre, o null si falta alguna (ya registrado como fallo)
 */
function selftest_need(string $caso, array $vars): ?array
{
    $vals = [];
    foreach ($vars as $v) {
        $vals[$v] = selftest_env($v);
        if ($vals[$v] === '') {
            selftest_check($caso, false, 'falta la variable de entorno ' . $v);
            return null;
        }
    }
    return $vals;
}

/**
 * Llamada JSON-RPC autenticada con un token.
 *
 * @param string      $token   Token Bearer (64 hex) o '' para no enviar la cabecera
 * @param string      $method  Metodo JSON-RPC
 * @param array       $params  Parametros
 * @param array       $extra   Cabeceras adicionales
 * @param string|null $version Cabecera MCP-Protocol-Version (null = no se envia)
 * @return array Respuesta de selftest_post()
 */
function selftest_rpc(string $token, string $method, array $params = [], array $extra = [], ?string $version = '2025-06-18'): array
{
    $headers = $extra;
    if ($token !== '') {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    if ($version !== null && $method !== 'initialize') {
        $headers[] = 'MCP-Protocol-Version: ' . $version;
    }
    $msg = ['jsonrpc' => '2.0', 'id' => 1, 'method' => $method];
    if ($params !== []) {
        $msg['params'] = $params;
    }
    return selftest_post($msg, $headers);
}

/**
 * Llama a una tool y devuelve la respuesta HTTP.
 *
 * @param string $token Token Bearer
 * @param string $tool  Nombre de la tool
 * @param array  $args  Argumentos
 * @return array Respuesta de selftest_post()
 */
function selftest_tool(string $token, string $tool, array $args): array
{
    return selftest_rpc($token, 'tools/call', ['name' => $tool, 'arguments' => (object) $args]);
}

/**
 * Codigo de error JSON-RPC de una respuesta (null si no hay error).
 *
 * @param array $r Respuesta de selftest_post()
 * @return int|null
 */
function selftest_code(array $r): ?int
{
    return is_array($r['json']) && isset($r['json']['error']['code']) ? (int) $r['json']['error']['code'] : null;
}

/**
 * Resume una respuesta para el detalle de un caso (solo estado y codigo; nunca cabeceras ni tokens).
 *
 * @param array $r Respuesta de selftest_post()
 * @return string
 */
function selftest_resumen(array $r): string
{
    return $r['error'] !== null ? $r['error'] : 'HTTP ' . $r['status'] . ', codigo ' . (selftest_code($r) ?? 'sin error');
}

/**
 * Nombre del modulo que contiene una tabla, segun list_modules con un token con alcance.
 *
 * @param string $token Token con alcance sobre la tabla
 * @param string $tabla Nombre de la tabla
 * @return string|null Modulo o null si no aparece
 */
function selftest_module_of(string $token, string $tabla): ?string
{
    $r = selftest_tool($token, 'list_modules', []);
    foreach ($r['json']['result']['structuredContent']['modules'] ?? [] as $m) {
        if (in_array($tabla, $m['tables'] ?? [], true)) {
            return (string) $m['module'];
        }
    }
    return null;
}

/**
 * Nombres de columna de todas las tablas de un describe_module.
 *
 * @param array $r Respuesta de describe_module
 * @return string[]
 */
function selftest_columns(array $r): array
{
    $cols = [];
    foreach ($r['json']['result']['structuredContent']['tables'] ?? [] as $t) {
        foreach ($t['columns'] ?? [] as $c) {
            $cols[] = (string) $c['name'];
        }
    }
    return $cols;
}

/**
 * Nombres de columna de una tabla concreta de un describe_module.
 *
 * @param array  $r     Respuesta de describe_module
 * @param string $tabla Nombre de la tabla
 * @return string[]|null Columnas, o null si la tabla no aparece
 */
function selftest_table_columns(array $r, string $tabla): ?array
{
    foreach ($r['json']['result']['structuredContent']['tables'] ?? [] as $t) {
        if (($t['table'] ?? '') === $tabla) {
            return array_map(static fn($c) => (string) $c['name'], $t['columns'] ?? []);
        }
    }
    return null;
}

/**
 * Nombres de las tablas de un describe_module.
 *
 * @param array $r Respuesta de describe_module
 * @return string[]
 */
function selftest_tables(array $r): array
{
    return array_map(static fn($t) => (string) ($t['table'] ?? ''), $r['json']['result']['structuredContent']['tables'] ?? []);
}

/**
 * Enum de modulos de una tool de tools/list.
 *
 * @param array  $r    Respuesta de tools/list
 * @param string $tool Nombre de la tool
 * @return string[]
 */
function selftest_enum(array $r, string $tool): array
{
    foreach ($r['json']['result']['tools'] ?? [] as $t) {
        if (($t['name'] ?? '') === $tool) {
            return $t['inputSchema']['properties']['module']['enum'] ?? [];
        }
    }
    return [];
}

/**
 * Caso comun: el token debe recibir un rechazo HTTP concreto con su codigo JSON-RPC.
 *
 * @param string $caso   Identificador del caso
 * @param string $var    Variable de entorno con el token
 * @param int    $status HTTP esperado
 * @param int    $code   Codigo JSON-RPC esperado
 */
function selftest_rechazo(string $caso, string $var, int $status, int $code): void
{
    $e = selftest_need($caso, [$var]);
    if ($e === null) {
        return;
    }
    $r = selftest_rpc($e[$var], 'tools/list');
    selftest_check($caso, $r['status'] === $status && selftest_code($r) === $code, 'esperado HTTP ' . $status . ' / ' . $code . ', real ' . selftest_resumen($r));
}

/**********************************************************************************************************************************/
/*                                       Ayudas de los casos OAuth (28-41)                                                        */
/**********************************************************************************************************************************/

/**
 * Valida una URL de las del servidor OAuth con la misma regla que MCP_URL: https salvo localhost, 127.0.0.1 o ::1 (H-11).
 *
 * @param string $url URL completa
 * @return string|null Motivo del rechazo o null si es valida
 */
function selftest_url_check(string $url): ?string
{
    $p = parse_url($url);
    if ($url === '' || $p === false || !isset($p['scheme'], $p['host']) || isset($p['fragment'])) {
        return 'URL no valida';
    }
    $esquema = strtolower($p['scheme']);
    $host    = strtolower(trim($p['host'], '[]'));
    if ($esquema === 'https' || ($esquema === 'http' && in_array($host, ['localhost', '127.0.0.1', '::1'], true))) {
        return null;
    }
    return 'la URL debe usar https (http solo para localhost, 127.0.0.1 o ::1)';
}

/**
 * Peticion HTTP a una URL arbitraria (endpoints del servidor OAuth). No sigue redirecciones, verifica el certificado y no imprime nada.
 *
 * @param string      $metodo  Metodo HTTP
 * @param string      $url     URL completa (con su query string)
 * @param string[]    $headers Cabeceras como lineas "Nombre: valor"
 * @param string|null $body    Cuerpo ya codificado (JSON o form) o null
 * @return array{status: int, headers: array<string,string>, body: string, json: mixed, error: ?string}
 */
function selftest_http(string $metodo, string $url, array $headers = [], ?string $body = null): array
{
    $vacio  = ['status' => 0, 'headers' => [], 'body' => '', 'json' => null, 'error' => null];
    $motivo = selftest_url_check($url);
    if ($motivo !== null) {
        return ['error' => $motivo] + $vacio;
    }
    $http = ['method' => $metodo, 'header' => implode("\r\n", $headers), 'ignore_errors' => true, 'timeout' => 30, 'follow_location' => 0, 'max_redirects' => 0];
    if ($body !== null) {
        $http['content'] = $body;
    }
    $ctx = stream_context_create(['http' => $http, 'ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]);
    $res = @file_get_contents($url, false, $ctx);
    if ($res === false && empty($http_response_header)) {
        return ['error' => 'sin respuesta del servidor'] + $vacio;
    }
    $status = 0;
    $cab    = [];
    foreach ($http_response_header ?? [] as $linea) {
        if (preg_match('#^HTTP/\S+\s+(\d{3})#', $linea, $m) === 1) {
            $status = (int) $m[1];
            $cab    = [];
        } elseif (strpos($linea, ':') !== false) {
            [$k, $v] = explode(':', $linea, 2);
            $cab[strtolower(trim($k))] = trim($v);
        }
    }
    $res = $res === false ? '' : $res;
    return ['status' => $status, 'headers' => $cab, 'body' => $res, 'json' => json_decode($res, true), 'error' => null];
}

/**
 * MCP_PUBLIC_URL normalizada como McpConfig::publicUrl(): sin barra final, https (http solo loopback) y sin query ni credenciales.
 *
 * @return string|null URL publica o null si falta o no es valida
 */
function selftest_public_url(): ?string
{
    $url = trim(selftest_env('MCP_PUBLIC_URL'));
    if (str_ends_with($url, '/')) {
        $url = substr($url, 0, -1);
    }
    $p = parse_url($url);
    if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false || $p === false || !isset($p['scheme'], $p['host'])
        || isset($p['query']) || isset($p['fragment']) || isset($p['user']) || isset($p['pass'])) {
        return null;
    }
    return selftest_url_check($url) === null ? $url : null;
}

/**
 * Origen (esquema, host y puerto) y ruta de una URL publica.
 *
 * @param string $url URL publica sin barra final
 * @return array{0: string, 1: string} [origen, ruta]
 */
function selftest_origen_ruta(string $url): array
{
    $p = parse_url($url);
    return [$p['scheme'] . '://' . $p['host'] . (isset($p['port']) ? ':' . $p['port'] : ''), $p['path'] ?? ''];
}

/**
 * Valida las variables de un caso OAuth: si falta alguna, el caso se omite (no cuenta como FALLO).
 *
 * @param string   $caso Identificador del caso
 * @param string[] $vars Nombres de variable requeridos
 * @return array<string,string>|null Valores por nombre, o null si falta alguna (ya anotado como omitido)
 */
function selftest_need_or_omit(string $caso, array $vars): ?array
{
    $vals = [];
    foreach ($vars as $v) {
        $vals[$v] = $v === 'MCP_PUBLIC_URL' ? (string) selftest_public_url() : selftest_env($v);
        if ($vals[$v] === '') {
            selftest_omit($caso, 'falta la variable de entorno ' . $v . ($v === 'MCP_PUBLIC_URL' ? ' (valida)' : ''));
            return null;
        }
    }
    return $vals;
}

/**
 * Espera, si hace falta, a que termine la ventana de /register (3 registros por minuto por IP, McpConfig::OAUTH_REG_MAX) y cuenta la llamada.
 */
function selftest_register_ventana(): void
{
    $v = $GLOBALS['selftest_reg'] ?? ['inicio' => 0, 'n' => 0];
    if ($v['n'] >= 3) {
        $espera = 62 - (time() - $v['inicio']);
        if ($espera > 0) {
            echo 'Esperando ' . $espera . ' s para renovar la ventana del limite de /register...' . PHP_EOL;
            sleep($espera);
        }
        $v = ['inicio' => 0, 'n' => 0];
    }
    if ($v['n'] === 0) {
        $v['inicio'] = time();
    }
    $v['n']++;
    $GLOBALS['selftest_reg'] = $v;
}

/**
 * POST <issuer>/register con metadatos JSON. Anota el client_id de todo 201 para borrarlo al final.
 *
 * @param string              $pub  MCP_PUBLIC_URL valida
 * @param array<string,mixed> $meta Metadatos RFC 7591
 * @return array Respuesta de selftest_http()
 */
function selftest_oauth_register(string $pub, array $meta): array
{
    selftest_register_ventana();
    $r = selftest_http('POST', $pub . '/oauth/register', ['Content-Type: application/json', 'Accept: application/json'], json_encode($meta));
    if ($r['status'] === 201 && is_array($r['json']) && is_string($r['json']['client_id'] ?? null)) {
        $GLOBALS['selftest_oauth_clientes'][] = $r['json']['client_id'];
    }
    return $r;
}

/**
 * Cliente dinamico compartido por los casos 31 y 32 (se registra una sola vez por ejecucion).
 *
 * @param string $pub MCP_PUBLIC_URL valida
 * @return array Respuesta de /register (201 si se pudo registrar)
 */
function selftest_oauth_cliente(string $pub): array
{
    if (!isset($GLOBALS['selftest_oauth_cliente'])) {
        $GLOBALS['selftest_oauth_cliente'] = selftest_oauth_register($pub, [
            'client_name'                => 'coreEngine selftest',
            'redirect_uris'              => ['http://127.0.0.1:5555/cb'],
            'token_endpoint_auth_method' => 'none',
            'grant_types'                => ['authorization_code', 'refresh_token'],
            'response_types'             => ['code'],
            'scope'                      => 'mcp',
            'client_uri'                 => 'https://selftest.example/',
            'software_id'                => 'mcp-selftest',
            'software_version'           => '1.0',
        ]);
    }
    return $GLOBALS['selftest_oauth_cliente'];
}

/**
 * Codigo de error OAuth de una respuesta JSON de error (RFC 6749 §5.2); null si no lo hay.
 *
 * @param array $r Respuesta de selftest_http()
 * @return string|null
 */
function selftest_oauth_error(array $r): ?string
{
    return is_array($r['json']) && isset($r['json']['error']) && is_string($r['json']['error']) ? $r['json']['error'] : null;
}

/**
 * Resume una respuesta del servidor OAuth para el detalle de un caso (estado y codigo de error; nunca cabeceras ni cuerpos).
 *
 * @param array $r Respuesta de selftest_http()
 * @return string
 */
function selftest_oauth_resumen(array $r): string
{
    return $r['error'] !== null ? $r['error'] : 'HTTP ' . $r['status'] . ', error ' . (selftest_oauth_error($r) ?? 'sin error');
}

/**
 * Borra de la BD los clientes dinamicos que creo la ejecucion (por ClientId) y lo comprueba. Sin pdo_mysql o sin conexion lo anota como omitido.
 */
function selftest_oauth_cleanup(): void
{
    $ids = $GLOBALS['selftest_oauth_clientes'] ?? [];
    if ($ids === []) {
        return;
    }
    $caso = 'limpieza de clientes OAuth del selftest';
    if (!extension_loaded('pdo_mysql')) {
        selftest_omit($caso, count($ids) . ' cliente(s) dinamico(s) sin borrar: sin pdo_mysql no hay conexion a la BD; los borra la purga de 24 h (no hay endpoint de borrado RFC 7592)');
        return;
    }
    try {
        require_once dirname(__DIR__) . '/bootstrap.php';
        $db        = McpDb::conn();
        $restantes = 0;
        foreach ($ids as $id) {
            $db->exec('DELETE FROM tokens_oauth_clientes WHERE ClientId = ?', [$id]);
            $fila       = $db->exec('SELECT COUNT(*) AS n FROM tokens_oauth_clientes WHERE ClientId = ?', [$id]);
            $restantes += (int) ($fila[0]['n'] ?? 1);
        }
        selftest_check($caso, $restantes === 0, 'esperado 0 clientes del selftest tras borrar ' . count($ids) . '; real ' . $restantes);
    } catch (Throwable $e) {
        selftest_omit($caso, count($ids) . ' cliente(s) dinamico(s) sin borrar: no se pudo usar McpDb::conn() (' . get_class($e) . '); los borra la purga de 24 h');
    }
}

/**********************************************************************************************************************************/
/*                                                            Casos                                                               */
/**********************************************************************************************************************************/

// Caso 1: initialize con token valido
$e = selftest_need('1 initialize con token valido', ['MCP_TOKEN_OK']);
if ($e !== null) {
    $r  = selftest_rpc($e['MCP_TOKEN_OK'], 'initialize', ['protocolVersion' => '2025-06-18', 'capabilities' => (object) [], 'clientInfo' => ['name' => 'mcp-selftest', 'version' => '1.0']]);
    $res = $r['json']['result'] ?? [];
    $ok  = $r['status'] === 200 && ($res['protocolVersion'] ?? '') === '2025-06-18' && isset($res['capabilities']['tools'], $res['capabilities']['resources']);
    selftest_check('1 initialize con token valido', $ok, 'esperado 200, 2025-06-18, capabilities.tools y .resources; real ' . selftest_resumen($r) . ', protocolVersion ' . (is_string($res['protocolVersion'] ?? null) ? $res['protocolVersion'] : 'ausente'));
}

// Caso 2: sin cabecera Authorization
$r2 = selftest_rpc('', 'tools/list');
selftest_check('2 sin cabecera Authorization', $r2['status'] === 401 && stripos($r2['headers']['www-authenticate'] ?? '', 'Bearer') === 0 && selftest_code($r2) === -32001, 'esperado 401, WWW-Authenticate Bearer, -32001; real ' . selftest_resumen($r2) . ', WWW-Authenticate ' . (isset($r2['headers']['www-authenticate']) ? 'presente' : 'ausente'));

// Caso 3: token inexistente (64 hex aleatorio): respuesta identica a la del caso 2
$r3 = selftest_rpc(bin2hex(random_bytes(32)), 'tools/list');
$id3 = $r3['status'] === 401 && $r3['body'] === $r2['body'] && ($r3['headers']['www-authenticate'] ?? null) === ($r2['headers']['www-authenticate'] ?? null);
selftest_check('3 token inexistente', $id3, 'esperado 401 identico al caso 2 (cuerpo y WWW-Authenticate); real ' . selftest_resumen($r3) . ', cuerpo ' . ($r3['body'] === $r2['body'] ? 'identico' : 'distinto'));

// Casos 4 y 5: token inactivo y caducado
selftest_rechazo('4 token inactivo', 'MCP_TOKEN_INACTIVO', 401, -32001);
selftest_rechazo('5 token caducado', 'MCP_TOKEN_CADUCADO', 401, -32001);

// Caso 6: token sin alcance
selftest_rechazo('6 token sin alcance', 'MCP_TOKEN_SIN_ALCANCE', 403, -32003);

// Caso 7: tools/list filtrado por alcance
$e = selftest_need('7 tools/list filtrado', ['MCP_TOKEN_OK', 'MCP_MODULO_FUERA']);
if ($e !== null) {
    $r     = selftest_rpc($e['MCP_TOKEN_OK'], 'tools/list');
    $tools = $r['json']['result']['tools'] ?? [];
    $enum  = selftest_enum($r, 'describe_module');
    selftest_check('7 tools/list filtrado', $r['status'] === 200 && count($tools) === 5 && $enum !== [] && !in_array($e['MCP_MODULO_FUERA'], $enum, true), 'esperado 5 tools y module.enum no vacio sin ' . $e['MCP_MODULO_FUERA'] . '; real ' . count($tools) . ' tools, enum ' . count($enum) . ' modulos, fuera ' . (in_array($e['MCP_MODULO_FUERA'], $enum, true) ? 'presente' : 'ausente'));
}

// Caso 8: describe_module de un modulo fuera de alcance
$e = selftest_need('8 modulo fuera de alcance', ['MCP_TOKEN_OK', 'MCP_MODULO_FUERA']);
if ($e !== null) {
    $r = selftest_tool($e['MCP_TOKEN_OK'], 'describe_module', ['module' => $e['MCP_MODULO_FUERA']]);
    selftest_check('8 modulo fuera de alcance', selftest_code($r) === -32602, 'esperado -32602; real ' . selftest_resumen($r));
}

// Caso 9: columna sensible oculta
$e = selftest_need('9 columna sensible oculta', ['MCP_TOKEN_OK', 'MCP_MODULO_SENSIBLE', 'MCP_COLUMNA_SENSIBLE']);
if ($e !== null) {
    $r    = selftest_tool($e['MCP_TOKEN_OK'], 'describe_module', ['module' => $e['MCP_MODULO_SENSIBLE']]);
    $cols = selftest_columns($r);
    selftest_check('9 columna sensible oculta', $r['status'] === 200 && selftest_code($r) === null && $cols !== [] && !in_array($e['MCP_COLUMNA_SENSIBLE'], $cols, true), 'esperado describe_module con columnas y sin ' . $e['MCP_COLUMNA_SENSIBLE'] . '; real ' . selftest_resumen($r) . ', ' . count($cols) . ' columnas');
}

// Caso 10: paginacion por cursor
$e = selftest_need('10 paginacion por cursor', ['MCP_TOKEN_OK', 'MCP_TABLA_PAGINA']);
if ($e !== null) {
    $mod = selftest_module_of($e['MCP_TOKEN_OK'], $e['MCP_TABLA_PAGINA']);
    if ($mod === null) {
        selftest_check('10 paginacion por cursor', false, 'la tabla ' . $e['MCP_TABLA_PAGINA'] . ' no aparece en list_modules del token OK');
    } else {
        $a  = selftest_tool($e['MCP_TOKEN_OK'], 'list_records', ['module' => $mod, 'table' => $e['MCP_TABLA_PAGINA'], 'limit' => 1]);
        $sa = $a['json']['result']['structuredContent'] ?? [];
        $cursor = $sa['nextCursor'] ?? null;
        $ok = count($sa['rows'] ?? []) === 1 && is_string($cursor) && $cursor !== '';
        $det = 'esperado 1 fila y nextCursor; real ' . count($sa['rows'] ?? []) . ' filas, nextCursor ' . (is_string($cursor) ? 'presente' : 'ausente');
        if ($ok) {
            $b  = selftest_tool($e['MCP_TOKEN_OK'], 'list_records', ['module' => $mod, 'table' => $e['MCP_TABLA_PAGINA'], 'limit' => 1, 'cursor' => $cursor]);
            $sb = $b['json']['result']['structuredContent'] ?? [];
            $ok = count($sb['rows'] ?? []) === 1 && $sb['rows'][0] !== $sa['rows'][0];
            $det = 'esperado una segunda fila distinta con el cursor; real ' . count($sb['rows'] ?? []) . ' filas, ' . ($ok ? 'distinta' : 'no distinta');
        }
        selftest_check('10 paginacion por cursor', $ok, $det);
    }
}

// Caso 11: GET rechazado
$r = selftest_request('GET', null, []);
selftest_check('11 GET rechazado', $r['status'] === 405 && ($r['headers']['allow'] ?? '') === 'POST, OPTIONS', 'esperado 405 con Allow: POST, OPTIONS; real ' . ($r['error'] ?? 'HTTP ' . $r['status']) . ', Allow ' . ($r['headers']['allow'] ?? 'ausente'));

// Caso 12: Origin no permitido
$e = selftest_need('12 Origin no permitido', ['MCP_TOKEN_OK']);
if ($e !== null) {
    $r = selftest_rpc($e['MCP_TOKEN_OK'], 'tools/list', [], ['Origin: https://evil.example']);
    selftest_check('12 Origin no permitido', $r['status'] === 403 && selftest_code($r) === -32003, 'esperado 403 / -32003; real ' . selftest_resumen($r));
}

// Caso 13: un usuario de registro inactivo no afecta al token (D-27)
$e = selftest_need('13 usuario de registro inactivo', ['MCP_TOKEN_USUARIO_INACTIVO']);
if ($e !== null) {
    $ri = selftest_rpc($e['MCP_TOKEN_USUARIO_INACTIVO'], 'initialize', ['protocolVersion' => '2025-06-18', 'capabilities' => (object) [], 'clientInfo' => ['name' => 'mcp-selftest', 'version' => '1.0']]);
    $rl = selftest_rpc($e['MCP_TOKEN_USUARIO_INACTIVO'], 'tools/list');
    selftest_check('13 usuario de registro inactivo', $ri['status'] === 200 && $rl['status'] === 200 && selftest_code($ri) === null && selftest_code($rl) === null, 'esperado 200 en initialize y tools/list; real initialize ' . selftest_resumen($ri) . ', tools/list ' . selftest_resumen($rl));
}

// Caso 14: un modulo asignado al token que el MCP no expone (p. ej. excluido o sin tablas consultables) no aparece (ADR-0019)
$e = selftest_need('14 modulo no exponible asignado', ['MCP_TOKEN_MODULO_NO_EXPONIBLE', 'MCP_MODULO_NO_EXPONIBLE']);
if ($e !== null) {
    $r    = selftest_rpc($e['MCP_TOKEN_MODULO_NO_EXPONIBLE'], 'tools/list');
    $enum = selftest_enum($r, 'describe_module');
    selftest_check('14 modulo no exponible asignado', $r['status'] === 200 && selftest_code($r) === null && !in_array($e['MCP_MODULO_NO_EXPONIBLE'], $enum, true), 'esperado module.enum sin ' . $e['MCP_MODULO_NO_EXPONIBLE'] . '; real ' . selftest_resumen($r) . ', modulo ' . (in_array($e['MCP_MODULO_NO_EXPONIBLE'], $enum, true) ? 'presente' : 'ausente'));
}

// Caso 15: token en la query string (se envia uno aleatorio, no el real: solo importa el formato)
$e = selftest_need('15 token en la query string', ['MCP_TOKEN_OK']);
if ($e !== null) {
    $r = selftest_post(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'ping'], ['Authorization: Bearer ' . $e['MCP_TOKEN_OK']], 'token=' . bin2hex(random_bytes(32)));
    selftest_check('15 token en la query string', $r['status'] === 400, 'esperado 400; real ' . ($r['error'] ?? 'HTTP ' . $r['status']));
}

// Caso 16: columna PII oculta
$e = selftest_need('16 columna PII oculta', ['MCP_TOKEN_OK', 'MCP_MODULO_SENSIBLE', 'MCP_COLUMNA_PII']);
if ($e !== null) {
    $r    = selftest_tool($e['MCP_TOKEN_OK'], 'describe_module', ['module' => $e['MCP_MODULO_SENSIBLE']]);
    $cols = selftest_columns($r);
    selftest_check('16 columna PII oculta', $r['status'] === 200 && selftest_code($r) === null && $cols !== [] && !in_array($e['MCP_COLUMNA_PII'], $cols, true), 'esperado describe_module con columnas y sin ' . $e['MCP_COLUMNA_PII'] . '; real ' . selftest_resumen($r) . ', ' . count($cols) . ' columnas');
}

// Caso 17: order_by sobre una columna sin indice
$e = selftest_need('17 order_by no indexado', ['MCP_TOKEN_OK', 'MCP_TABLA_PAGINA', 'MCP_COLUMNA_SIN_INDICE']);
if ($e !== null) {
    $mod = selftest_module_of($e['MCP_TOKEN_OK'], $e['MCP_TABLA_PAGINA']);
    if ($mod === null) {
        selftest_check('17 order_by no indexado', false, 'la tabla ' . $e['MCP_TABLA_PAGINA'] . ' no aparece en list_modules del token OK');
    } else {
        $r = selftest_tool($e['MCP_TOKEN_OK'], 'list_records', ['module' => $mod, 'table' => $e['MCP_TABLA_PAGINA'], 'order_by' => $e['MCP_COLUMNA_SIN_INDICE']]);
        selftest_check('17 order_by no indexado', selftest_code($r) === -32602, 'esperado -32602; real ' . selftest_resumen($r));
    }
}

// Caso 18: cursor con desplazamiento fuera de rango ({"o":10001})
$e = selftest_need('18 cursor fuera de rango', ['MCP_TOKEN_OK', 'MCP_TABLA_PAGINA']);
if ($e !== null) {
    $mod = selftest_module_of($e['MCP_TOKEN_OK'], $e['MCP_TABLA_PAGINA']);
    if ($mod === null) {
        selftest_check('18 cursor fuera de rango', false, 'la tabla ' . $e['MCP_TABLA_PAGINA'] . ' no aparece en list_modules del token OK');
    } else {
        $cursor = rtrim(strtr(base64_encode('{"o":10001}'), '+/', '-_'), '=');
        $r = selftest_tool($e['MCP_TOKEN_OK'], 'list_records', ['module' => $mod, 'table' => $e['MCP_TABLA_PAGINA'], 'cursor' => $cursor]);
        selftest_check('18 cursor fuera de rango', selftest_code($r) === -32602, 'esperado -32602; real ' . selftest_resumen($r));
    }
}

// Caso 19: tabla de un modulo que no esta asignado al token (el token si tiene otros modulos; ADR-0019)
$e = selftest_need('19 modulo no asignado', ['MCP_TOKEN_OK', 'MCP_MODULO_FUERA', 'MCP_TABLA_FUERA']);
if ($e !== null) {
    $r = selftest_tool($e['MCP_TOKEN_OK'], 'list_records', ['module' => $e['MCP_MODULO_FUERA'], 'table' => $e['MCP_TABLA_FUERA']]);
    selftest_check('19 modulo no asignado', selftest_code($r) === -32602, 'esperado -32602; real ' . selftest_resumen($r));
}

// Caso 20: limite por token (MCP_RATE_TOKEN_MAX=50 en el servidor de dev): la peticion 51 recibe 429
$e = selftest_need('20 limite por token', ['MCP_TOKEN_RATE']);
if ($e !== null) {
    $antes = 0;
    $r     = null;
    for ($n = 1; $n <= 51; $n++) {
        $r = selftest_rpc($e['MCP_TOKEN_RATE'], 'ping');
        if ($n <= 50 && $r['status'] === 429) {
            $antes = $n;
            break;
        }
    }
    $ok = $antes === 0 && $r['status'] === 429 && isset($r['headers']['retry-after']) && selftest_code($r) === -32004;
    selftest_check('20 limite por token', $ok, 'esperado 429 + Retry-After + -32004 en la peticion 51; real ' . ($antes > 0 ? '429 ya en la ' . $antes : selftest_resumen($r) . ', Retry-After ' . (isset($r['headers']['retry-after']) ? 'presente' : 'ausente')));
}

// El limite por IP (120 peticiones por 60 s) se agotaria con los casos 20 y 21 seguidos: se espera una ventana nueva
echo 'Esperando 62 s para renovar la ventana del limite por IP...' . PHP_EOL;
sleep(62);

// Caso 21: cada mensaje de un lote cuenta (49 peticiones + lote de 2 ping = 51)
$e = selftest_need('21 recuento del lote', ['MCP_TOKEN_RATE_LOTE']);
if ($e !== null) {
    $antes = 0;
    for ($n = 1; $n <= 49; $n++) {
        $r = selftest_rpc($e['MCP_TOKEN_RATE_LOTE'], 'ping');
        if ($r['status'] === 429) {
            $antes = $n;
            break;
        }
    }
    $lote = selftest_post([['jsonrpc' => '2.0', 'id' => 1, 'method' => 'ping'], ['jsonrpc' => '2.0', 'id' => 2, 'method' => 'ping']], ['Authorization: Bearer ' . $e['MCP_TOKEN_RATE_LOTE'], 'MCP-Protocol-Version: 2025-06-18']);
    selftest_check('21 recuento del lote', $antes === 0 && $lote['status'] === 429, 'esperado 429 en el lote de 2 tras 49 peticiones; real ' . ($antes > 0 ? '429 ya en la ' . $antes : 'lote HTTP ' . $lote['status']));
}

// Caso 22: respuesta mayor que el limite (MCP_MAX_RESPONSE_BYTES=200000 en el servidor de dev)
$e = selftest_need('22 respuesta mayor que el limite', ['MCP_TOKEN_OK', 'MCP_TABLA_GRANDE']);
if ($e !== null) {
    $mod = selftest_module_of($e['MCP_TOKEN_OK'], $e['MCP_TABLA_GRANDE']);
    if ($mod === null) {
        selftest_check('22 respuesta mayor que el limite', false, 'la tabla ' . $e['MCP_TABLA_GRANDE'] . ' no aparece en list_modules del token OK');
    } else {
        $r   = selftest_tool($e['MCP_TOKEN_OK'], 'list_records', ['module' => $mod, 'table' => $e['MCP_TABLA_GRANDE'], 'limit' => 100]);
        $msg = (string) ($r['json']['error']['message'] ?? '');
        selftest_check('22 respuesta mayor que el limite', selftest_code($r) === -32602 && stripos($msg, 'Respuesta demasiado grande') !== false, 'esperado -32602 "Respuesta demasiado grande"; real ' . selftest_resumen($r) . ', mensaje "' . substr($msg, 0, 60) . '"');
    }
}

// Caso 23: un token con un solo modulo ve solo ese modulo y accede a sus tablas expuestas (ADR-0019)
$e = selftest_need('23 un modulo da acceso a sus tablas', ['MCP_TOKEN_UN_MODULO', 'MCP_MODULO_UN_MODULO', 'MCP_TABLA_DEL_MODULO']);
if ($e !== null) {
    $rl    = selftest_rpc($e['MCP_TOKEN_UN_MODULO'], 'tools/list');
    $enum  = selftest_enum($rl, 'describe_module');
    $rr    = selftest_tool($e['MCP_TOKEN_UN_MODULO'], 'list_records', ['module' => $e['MCP_MODULO_UN_MODULO'], 'table' => $e['MCP_TABLA_DEL_MODULO'], 'limit' => 1]);
    $ok    = $rl['status'] === 200 && $enum === [$e['MCP_MODULO_UN_MODULO']] && $rr['status'] === 200 && selftest_code($rr) === null && !($rr['json']['result']['isError'] ?? false);
    selftest_check('23 un modulo da acceso a sus tablas', $ok, 'esperado module.enum = [' . $e['MCP_MODULO_UN_MODULO'] . '] y list_records de ' . $e['MCP_TABLA_DEL_MODULO'] . ' 200; real enum con ' . count($enum) . ' modulos (' . implode(',', $enum) . '), list_records ' . selftest_resumen($rr));
}

// Caso 24: columnas idUsuario* acompanadas de <columna>_nombre sin otros datos de usuario (D-31)
$e = selftest_need('24 idUsuario con nombre', ['MCP_TOKEN_UN_MODULO', 'MCP_MODULO_UN_MODULO', 'MCP_TABLA_USUARIO']);
if ($e !== null) {
    $tk   = $e['MCP_TOKEN_UN_MODULO'];
    $mod  = $e['MCP_MODULO_UN_MODULO'];
    $tab  = $e['MCP_TABLA_USUARIO'];
    $d    = selftest_tool($tk, 'describe_module', ['module' => $mod]);
    $cols = selftest_table_columns($d, $tab);
    $pk   = '';
    foreach ($d['json']['result']['structuredContent']['tables'] ?? [] as $t) {
        if (($t['table'] ?? '') === $tab) {
            $pk = (string) ($t['primaryKey'] ?? '');
        }
    }
    $ra   = selftest_tool($tk, 'list_records', ['module' => $mod, 'table' => $tab, 'limit' => 1]);
    $fila = $ra['json']['result']['structuredContent']['rows'][0] ?? null;
    if ($cols === null || $pk === '' || !is_array($fila)) {
        selftest_check('24 idUsuario con nombre', false, 'no se pudo leer la tabla ' . $tab . ' (describe_module ' . selftest_resumen($d) . ', list_records ' . selftest_resumen($ra) . ')');
    } else {
        $idCols = array_values(array_filter($cols, static fn($c) => in_array($c, ['idUsuario', 'idUsuarioEdicion', 'idUsuarioCreador', 'idUsuarioResponsable'], true) && $c !== $pk));
        $rg     = selftest_tool($tk, 'get_record', ['module' => $mod, 'table' => $tab, 'id' => (int) $fila[$pk]]);
        $reg    = $rg['json']['result']['structuredContent']['row'] ?? null;
        if (selftest_env('MCP_SIN_GRANT') === '1') {
            // Degradacion: la cuenta MySQL_MCP no tiene el GRANT de usuarios_listado (preparada por el operador)
            $ok = $idCols !== [] && $ra['status'] === 200 && $rg['status'] === 200 && !($ra['json']['result']['isError'] ?? false) && !($rg['json']['result']['isError'] ?? false) && selftest_code($ra) === null && selftest_code($rg) === null && is_array($reg);
            foreach ($idCols as $c) {
                $ok = $ok && !array_key_exists($c . '_nombre', $fila) && is_array($reg) && !array_key_exists($c . '_nombre', $reg);
            }
            selftest_check('24 idUsuario con nombre (degradado sin GRANT)', $ok, 'esperado 200 sin isError y sin <col>_nombre; real list_records ' . selftest_resumen($ra) . ', get_record ' . selftest_resumen($rg));
        } else {
            $extras = array_values(array_diff(array_keys($fila), $cols));
            $esper  = array_map(static fn($c) => $c . '_nombre', $idCols);
            sort($extras);
            sort($esper);
            $ok = $idCols !== [] && $extras === $esper && is_array($reg);
            foreach ($idCols as $c) {
                $ok = $ok && is_string($fila[$c . '_nombre'] ?? null) && $fila[$c . '_nombre'] !== '' && is_array($reg) && is_string($reg[$c . '_nombre'] ?? null) && $reg[$c . '_nombre'] !== '';
            }
            foreach ($fila as $k => $v) {
                if (preg_match('/^(e_?mail|password|rut|fono\d*)$/i', (string) $k) === 1 && !in_array($k, $cols, true)) {
                    $ok = false;
                }
            }
            selftest_check('24 idUsuario con nombre', $ok, 'esperado ' . implode(',', $esper) . ' no vacios en list_records y get_record y ninguna otra clave ajena a la tabla; real claves extra [' . implode(',', $extras) . '], ' . count($idCols) . ' columnas idUsuario*');
            selftest_omit('24 idUsuario con nombre (degradado sin GRANT)', 'pendiente de operador: requiere la cuenta MySQL_MCP sin GRANT sobre usuarios_listado y MCP_SIN_GRANT=1');
        }
    }
}

// Caso 25: entidades_listado muestra datos de contacto y oculta contrasena y representante legal (D-14 rev. 9)
$e = selftest_need('25 entidades_listado', ['MCP_TOKEN_ENTIDADES', 'MCP_MODULO_ENTIDADES']);
if ($e !== null) {
    $r     = selftest_tool($e['MCP_TOKEN_ENTIDADES'], 'list_records', ['module' => $e['MCP_MODULO_ENTIDADES'], 'table' => 'entidades_listado', 'limit' => 1]);
    $fila  = $r['json']['result']['structuredContent']['rows'][0] ?? null;
    $falta = [];
    $sobra = [];
    if (is_array($fila)) {
        $falta = array_values(array_filter(['Rut', 'Email', 'Fono1'], static fn($c) => !array_key_exists($c, $fila)));
        $sobra = array_values(array_filter(['password', 'PasswordForzarCambio', 'PasswordUltimoCambio', 'RepLegalNombre', 'RepLegalRut', 'RepLegalEmail', 'RepLegalFono1', 'RepLegalFono2'], static fn($c) => array_key_exists($c, $fila)));
    }
    selftest_check('25 entidades_listado', $r['status'] === 200 && selftest_code($r) === null && is_array($fila) && $falta === [] && $sobra === [], 'esperado 200, con Rut/Email/Fono1 y sin password*/RepLegal*; real ' . selftest_resumen($r) . ', faltan [' . implode(',', $falta) . '], sobran [' . implode(',', $sobra) . '], fila ' . (is_array($fila) ? 'presente' : 'ausente'));
}

// Caso 26: usuarios, permisos del core y HIDDEN_TABLES ni consultables ni visibles (D-30, D-31)
$e = selftest_need('26 tablas ocultas', ['MCP_TOKEN_TERCEROS', 'MCP_MODULO_TERCEROS']);
if ($e !== null) {
    $ocultas = ['usuarios_listado', 'core_permisos_listado', 'terceros_entidades_listado', 'terceros_entidades_listado_usuarios'];
    $malas   = [];
    foreach ($ocultas as $t) {
        $rl = selftest_tool($e['MCP_TOKEN_TERCEROS'], 'list_records', ['module' => $e['MCP_MODULO_TERCEROS'], 'table' => $t]);
        $rg = selftest_tool($e['MCP_TOKEN_TERCEROS'], 'get_record', ['module' => $e['MCP_MODULO_TERCEROS'], 'table' => $t, 'id' => 1]);
        if (selftest_code($rl) !== -32602) {
            $malas[] = $t . ' list_records ' . selftest_resumen($rl);
        }
        if (selftest_code($rg) !== -32602) {
            $malas[] = $t . ' get_record ' . selftest_resumen($rg);
        }
    }
    $rm      = selftest_tool($e['MCP_TOKEN_TERCEROS'], 'list_modules', []);
    $visibles = [];
    foreach ($rm['json']['result']['structuredContent']['modules'] ?? [] as $m) {
        $visibles = array_merge($visibles, $m['tables'] ?? []);
    }
    $rd       = selftest_tool($e['MCP_TOKEN_TERCEROS'], 'describe_module', ['module' => $e['MCP_MODULO_TERCEROS']]);
    $visibles = array_merge($visibles, selftest_tables($rd));
    $vistas   = array_values(array_intersect($ocultas, $visibles));
    selftest_check('26 tablas ocultas', $malas === [] && $vistas === [] && $rd['status'] === 200 && $rm['status'] === 200, 'esperado -32602 en list_records/get_record de las 4 y ninguna en list_modules/describe_module; real fallos [' . implode('; ', $malas) . '], visibles [' . implode(',', $vistas) . ']');
}

// Caso 27: contactos, coordenadas de vehiculos y FNacimiento visibles; Direccion_img oculta en todas las tablas (D-14 rev. 9, D-32)
$e = selftest_need('27 PII por columna', ['MCP_TOKEN_ENTIDADES', 'MCP_MODULO_ENTIDADES', 'MCP_TOKEN_VEHICULOS']);
if ($e !== null) {
    $re  = selftest_tool($e['MCP_TOKEN_ENTIDADES'], 'describe_module', ['module' => $e['MCP_MODULO_ENTIDADES']]);
    $rv  = selftest_tool($e['MCP_TOKEN_VEHICULOS'], 'describe_module', ['module' => 'vehiculos']);
    $cc  = selftest_table_columns($re, 'entidades_listado_contactos') ?? [];
    $cv  = selftest_table_columns($rv, 'vehiculos_listado') ?? [];
    $cg  = selftest_table_columns($re, 'entidades_listado_cargas') ?? [];
    $mal = [];
    foreach (['Rut', 'Email', 'Fono1', 'Direccion'] as $c) {
        in_array($c, $cc, true) || $mal[] = 'contactos sin ' . $c;
    }
    foreach (['Latitud', 'Longitud'] as $c) {
        in_array($c, $cv, true) || $mal[] = 'vehiculos_listado sin ' . $c;
    }
    in_array('FNacimiento', $cg, true) || $mal[] = 'cargas sin FNacimiento';
    foreach ([[$re, 'entidades'], [$rv, 'vehiculos']] as [$rd, $nombre]) {
        foreach ($rd['json']['result']['structuredContent']['tables'] ?? [] as $t) {
            foreach ($t['columns'] ?? [] as $c) {
                if ((string) $c['name'] === 'Direccion_img') {
                    $mal[] = 'Direccion_img en ' . $t['table'];
                }
            }
        }
    }
    foreach (['entidades_listado_contactos', 'entidades_listado'] as $t) {
        $r    = selftest_tool($e['MCP_TOKEN_ENTIDADES'], 'list_records', ['module' => $e['MCP_MODULO_ENTIDADES'], 'table' => $t, 'limit' => 1]);
        $fila = $r['json']['result']['structuredContent']['rows'][0] ?? null;
        if (selftest_code($r) !== null || $r['status'] !== 200) {
            $mal[] = $t . ' list_records ' . selftest_resumen($r);
        } elseif (is_array($fila)) {
            foreach (array_keys($fila) as $k) {
                if (preg_match('/^password/i', (string) $k) === 1 || (string) $k === 'Direccion_img' || ($t === 'entidades_listado_contactos' && preg_match('/^replegal/i', (string) $k) === 1)) {
                    $mal[] = $t . ' fila con ' . $k;
                }
            }
        }
        $filas[$t] = is_array($fila) ? 'con fila' : 'sin filas';
    }
    selftest_check('27 PII por columna', $mal === [], 'esperado contactos con Rut/Email/Fono1/Direccion, vehiculos con Latitud/Longitud, cargas con FNacimiento y ninguna Direccion_img ni password*; real incidencias [' . implode('; ', $mal) . '], filas leidas: contactos ' . ($filas['entidades_listado_contactos'] ?? '?') . ', entidades ' . ($filas['entidades_listado'] ?? '?'));
}

/**********************************************************************************************************************************/
/*                                     Casos 28-41: servidor de autorizacion OAuth                                                */
/**********************************************************************************************************************************/

// Caso 28: el 401 sin token anuncia donde estan los metadatos del recurso (RFC 9728), reutilizando la respuesta del caso 2
$e = selftest_need_or_omit('28 401 con resource_metadata', ['MCP_PUBLIC_URL']);
if ($e !== null) {
    [$origen28, $ruta28] = selftest_origen_ruta($e['MCP_PUBLIC_URL']);
    $esperado28 = $origen28 . '/.well-known/oauth-protected-resource' . $ruta28 . '/public';
    $cab28      = $r2['headers']['www-authenticate'] ?? '';
    $ok28       = $r2['status'] === 401 && stripos($cab28, 'Bearer') === 0 && str_contains($cab28, 'resource_metadata="' . $esperado28 . '"');
    selftest_check('28 401 con resource_metadata', $ok28, 'esperado 401 con WWW-Authenticate Bearer y resource_metadata="<origen>/.well-known/oauth-protected-resource<ruta>"; real ' . selftest_resumen($r2) . ', resource_metadata ' . (str_contains($cab28, 'resource_metadata=') ? ($ok28 ? 'coincide' : 'distinta') : 'ausente'));
}

// Caso 29: metadatos del recurso protegido (URL dentro del arbol del AS); el resource se reutiliza en el caso 38
$resource29 = null;
$e = selftest_need_or_omit('29 metadatos del recurso protegido', ['MCP_PUBLIC_URL']);
if ($e !== null) {
    $pub29 = $e['MCP_PUBLIC_URL'];
    $r29   = selftest_http('GET', $pub29 . '/oauth/.well-known/oauth-protected-resource', ['Accept: application/json']);
    $res29 = is_array($r29['json']) ? ($r29['json']['resource'] ?? null) : null;
    $aut29 = is_array($r29['json']) ? ($r29['json']['authorization_servers'] ?? null) : null;
    $resource29 = is_string($res29) ? $res29 : null;
    $ok29  = $r29['status'] === 200 && $res29 === $pub29 . '/public/' && $aut29 === [$pub29 . '/oauth'] && ($r29['headers']['access-control-allow-origin'] ?? '') === '*';
    selftest_check('29 metadatos del recurso protegido', $ok29, 'esperado 200, resource = <MCP_PUBLIC_URL>/public/, authorization_servers = [<MCP_PUBLIC_URL>/oauth] y CORS *; real ' . selftest_oauth_resumen($r29) . ', resource ' . (is_string($res29) ? ($res29 === $pub29 . '/public/' ? 'coincide' : 'distinto') : 'ausente') . ', authorization_servers ' . ($aut29 === [$pub29 . '/oauth'] ? 'coincide' : 'distinto') . ', CORS ' . (($r29['headers']['access-control-allow-origin'] ?? '') === '*' ? '*' : 'ausente'));
}

// Caso 30: metadatos del servidor de autorizacion (RFC 8414): solo S256 y solo authorization_code y refresh_token
$e = selftest_need_or_omit('30 metadatos del servidor de autorizacion', ['MCP_PUBLIC_URL']);
if ($e !== null) {
    $pub30 = $e['MCP_PUBLIC_URL'];
    $r30   = selftest_http('GET', $pub30 . '/oauth/.well-known/oauth-authorization-server', ['Accept: application/json']);
    $j30   = is_array($r30['json']) ? $r30['json'] : [];
    $gr30  = $j30['grant_types_supported'] ?? null;
    is_array($gr30) && sort($gr30);
    $ok30  = $r30['status'] === 200 && ($j30['issuer'] ?? null) === $pub30 . '/oauth' && ($j30['code_challenge_methods_supported'] ?? null) === ['S256']
        && $gr30 === ['authorization_code', 'refresh_token'] && ($j30['registration_endpoint'] ?? null) === $pub30 . '/oauth/register' && ($j30['token_endpoint'] ?? null) === $pub30 . '/oauth/token';
    selftest_check('30 metadatos del servidor de autorizacion', $ok30, 'esperado 200, issuer = <MCP_PUBLIC_URL>/oauth, code_challenge_methods_supported = [S256], grant_types_supported = [authorization_code, refresh_token] y endpoints register/token; real ' . selftest_oauth_resumen($r30) . ', S256 ' . (($j30['code_challenge_methods_supported'] ?? null) === ['S256'] ? 'solo' : 'distinto') . ', grants [' . (is_array($gr30) ? implode(',', array_map('strval', $gr30)) : 'ausente') . ']');
}

// Caso 31: registro dinamico, pantalla del AS y errores de cliente y de redirect_uri sin redirigir (B-4); limpia lo que crea al final
$e = selftest_need_or_omit('31 registro dinamico y pantalla del AS', ['MCP_PUBLIC_URL']);
if ($e !== null) {
    $pub31 = $e['MCP_PUBLIC_URL'];
    $reg31 = selftest_oauth_cliente($pub31);
    $j31   = is_array($reg31['json']) ? $reg31['json'] : [];
    $id31  = is_string($j31['client_id'] ?? null) ? $j31['client_id'] : '';
    $ok31  = $reg31['status'] === 201 && $id31 !== '' && is_int($j31['client_id_issued_at'] ?? null)
        && ($j31['client_name'] ?? null) === 'coreEngine selftest' && ($j31['redirect_uris'] ?? null) === ['http://127.0.0.1:5555/cb']
        && ($j31['token_endpoint_auth_method'] ?? null) === 'none' && ($j31['grant_types'] ?? null) === ['authorization_code', 'refresh_token']
        && ($j31['response_types'] ?? null) === ['code'] && ($j31['scope'] ?? null) === 'mcp' && ($j31['client_uri'] ?? null) === 'https://selftest.example/'
        && ($j31['software_id'] ?? null) === 'mcp-selftest' && !array_key_exists('client_secret', $j31);
    selftest_check('31 registro dinamico (201 con todos los metadatos)', $ok31, 'esperado 201 con client_id, client_id_issued_at y los metadatos registrados, sin client_secret (cliente publico); real ' . selftest_oauth_resumen($reg31));
    if ($id31 === '') {
        selftest_check('31 pantalla del AS', false, 'sin client_id no se puede probar /authorize (el registro fallo)');
    } else {
        $desafio31 = rtrim(strtr(base64_encode(hash('sha256', 'selftest-verifier-selftest-verifier-selftest', true)), '+/', '-_'), '=');
        $query31   = static fn(string $cid, string $uri): string => http_build_query(['response_type' => 'code', 'client_id' => $cid, 'redirect_uri' => $uri, 'code_challenge' => $desafio31, 'code_challenge_method' => 'S256', 'state' => 'selftest', 'resource' => $pub31 . '/public/'], '', '&', PHP_QUERY_RFC3986);
        $page31    = selftest_http('GET', $pub31 . '/oauth/authorize?' . $query31($id31, 'http://127.0.0.1:5555/cb'), ['Accept: text/html']);
        $html31    = str_starts_with(strtolower($page31['headers']['content-type'] ?? ''), 'text/html');
        $ok31b     = $page31['status'] === 200 && $html31 && str_contains($page31['body'], '<form') && str_contains($page31['body'], 'name="decision"') && str_contains($page31['body'], 'name="token"') && str_contains($page31['body'], 'coreEngine selftest');
        selftest_check('31 pantalla del AS (200 HTML con el formulario)', $ok31b, 'esperado 200 text/html con el formulario de token y el nombre declarado; real ' . selftest_oauth_resumen($page31) . ', ' . ($html31 ? 'text/html' : 'no es HTML') . ', formulario ' . (str_contains($page31['body'], '<form') ? 'presente' : 'ausente'));
        $sinCliente31 = selftest_http('GET', $pub31 . '/oauth/authorize?' . $query31('selftest-' . bin2hex(random_bytes(8)), 'http://127.0.0.1:5555/cb'), ['Accept: text/html']);
        selftest_check('31 authorize con client_id inexistente', $sinCliente31['status'] === 400 && !isset($sinCliente31['headers']['location']), 'esperado 400 sin cabecera Location; real ' . selftest_oauth_resumen($sinCliente31) . ', Location ' . (isset($sinCliente31['headers']['location']) ? 'presente' : 'ausente'));
        $sinUri31 = selftest_http('GET', $pub31 . '/oauth/authorize?' . $query31($id31, 'http://127.0.0.1:5555/otra'), ['Accept: text/html']);
        selftest_check('31 authorize con redirect_uri no registrada', $sinUri31['status'] === 400 && !isset($sinUri31['headers']['location']), 'esperado 400 sin cabecera Location; real ' . selftest_oauth_resumen($sinUri31) . ', Location ' . (isset($sinUri31['headers']['location']) ? 'presente' : 'ausente'));
    }
}

// Caso 32: errores de /token con un cliente registrado (el del caso 31): codigo inventado, sin grant_type y secreto en la query
$e = selftest_need_or_omit('32 errores de /token', ['MCP_PUBLIC_URL']);
if ($e !== null) {
    $pub32 = $e['MCP_PUBLIC_URL'];
    $reg32 = selftest_oauth_cliente($pub32);
    $id32  = is_array($reg32['json']) && is_string($reg32['json']['client_id'] ?? null) ? $reg32['json']['client_id'] : '';
    if ($id32 === '') {
        selftest_check('32 errores de /token', false, 'sin cliente registrado no se puede probar /token (el registro fallo: ' . selftest_oauth_resumen($reg32) . ')');
    } else {
        $form32  = ['Content-Type: application/x-www-form-urlencoded', 'Accept: application/json'];
        $a32     = selftest_http('POST', $pub32 . '/oauth/token', $form32, http_build_query(['grant_type' => 'authorization_code', 'client_id' => $id32, 'code' => bin2hex(random_bytes(32)), 'redirect_uri' => 'http://127.0.0.1:5555/cb', 'code_verifier' => str_repeat('a', 43)]));
        $b32     = selftest_http('POST', $pub32 . '/oauth/token', $form32, http_build_query(['client_id' => $id32]));
        $c32     = selftest_http('POST', $pub32 . '/oauth/token?code=x', $form32, http_build_query(['grant_type' => 'authorization_code', 'client_id' => $id32]));
        selftest_check('32 /token con codigo inventado', $a32['status'] === 400 && selftest_oauth_error($a32) === 'invalid_grant', 'esperado 400 invalid_grant; real ' . selftest_oauth_resumen($a32));
        selftest_check('32 /token sin grant_type', $b32['status'] === 400 && selftest_oauth_error($b32) === 'invalid_request', 'esperado 400 invalid_request; real ' . selftest_oauth_resumen($b32));
        selftest_check('32 /token con code en la query', $c32['status'] === 400, 'esperado 400 (secretos en la query); real ' . selftest_oauth_resumen($c32));
    }
}

// Caso 33: un access token OAuth tiene el alcance de su token padre (tools/list con MCP_OAUTH_TOKEN)
$e = selftest_need_or_omit('33 access token OAuth con alcance del padre', ['MCP_OAUTH_TOKEN']);
if ($e !== null) {
    $r33 = selftest_rpc($e['MCP_OAUTH_TOKEN'], 'tools/list');
    $n33 = count($r33['json']['result']['tools'] ?? []);
    $m33 = selftest_enum($r33, 'describe_module');
    selftest_check('33 access token OAuth con alcance del padre', $r33['status'] === 200 && selftest_code($r33) === null && $n33 === 5 && $m33 !== [], 'esperado 200, 5 tools y module.enum no vacio (alcance del padre); real ' . selftest_resumen($r33) . ', ' . $n33 . ' tools, enum ' . count($m33) . ' modulos');
}

// Caso 34: un access token OAuth expirado recibe el mismo 401 que un token ausente (caso 2)
$e = selftest_need_or_omit('34 access token OAuth expirado', ['MCP_OAUTH_TOKEN_EXPIRADO']);
if ($e !== null) {
    $r34 = selftest_rpc($e['MCP_OAUTH_TOKEN_EXPIRADO'], 'tools/list');
    $ok34 = $r34['status'] === 401 && $r34['body'] === $r2['body'] && ($r34['headers']['www-authenticate'] ?? null) === ($r2['headers']['www-authenticate'] ?? null);
    selftest_check('34 access token OAuth expirado', $ok34, 'esperado 401 identico al caso 2 (cuerpo y WWW-Authenticate); real ' . selftest_resumen($r34) . ', cuerpo ' . ($r34['body'] === $r2['body'] ? 'identico' : 'distinto'));
}

// Caso 35: /revoke responde 200 {} y el access token revocado pasa a 401 (consume MCP_OAUTH_TOKEN_REVOCAR: un solo uso)
$e = selftest_need_or_omit('35 revocacion', ['MCP_PUBLIC_URL', 'MCP_OAUTH_TOKEN_REVOCAR', 'MCP_OAUTH_CLIENT_ID']);
if ($e !== null) {
    $antes35 = selftest_rpc($e['MCP_OAUTH_TOKEN_REVOCAR'], 'tools/list');
    if ($antes35['status'] !== 200) {
        selftest_check('35 revocacion', false, 'precondicion: el token a revocar debe valer antes (esperado tools/list 200); real ' . selftest_resumen($antes35));
    } else {
        $campos35 = ['token' => $e['MCP_OAUTH_TOKEN_REVOCAR'], 'token_type_hint' => 'access_token', 'client_id' => $e['MCP_OAUTH_CLIENT_ID']];
        if (selftest_env('MCP_OAUTH_CLIENT_SECRET') !== '') {
            $campos35['client_secret'] = selftest_env('MCP_OAUTH_CLIENT_SECRET');
        }
        $rev35   = selftest_http('POST', $e['MCP_PUBLIC_URL'] . '/oauth/revoke', ['Content-Type: application/x-www-form-urlencoded', 'Accept: application/json'], http_build_query($campos35));
        $desp35  = selftest_rpc($e['MCP_OAUTH_TOKEN_REVOCAR'], 'tools/list');
        $ok35    = $rev35['status'] === 200 && $rev35['body'] === '{}' && $desp35['status'] === 401;
        selftest_check('35 revocacion', $ok35, 'esperado /revoke 200 con cuerpo {} y despues tools/list 401; real /revoke ' . selftest_oauth_resumen($rev35) . ', cuerpo ' . ($rev35['body'] === '{}' ? '{}' : 'distinto') . ', despues ' . selftest_resumen($desp35));
    }
}

// Caso 36: reusar un refresh token ya usado recibe invalid_grant (MCP_OAUTH_REFRESH_USADO)
$e = selftest_need_or_omit('36 reuso de refresh token', ['MCP_PUBLIC_URL', 'MCP_OAUTH_REFRESH_USADO', 'MCP_OAUTH_CLIENT_ID']);
if ($e !== null) {
    $campos36 = ['grant_type' => 'refresh_token', 'refresh_token' => $e['MCP_OAUTH_REFRESH_USADO'], 'client_id' => $e['MCP_OAUTH_CLIENT_ID']];
    if (selftest_env('MCP_OAUTH_CLIENT_SECRET') !== '') {
        $campos36['client_secret'] = selftest_env('MCP_OAUTH_CLIENT_SECRET');
    }
    $r36 = selftest_http('POST', $e['MCP_PUBLIC_URL'] . '/oauth/token', ['Content-Type: application/x-www-form-urlencoded', 'Accept: application/json'], http_build_query($campos36));
    selftest_check('36 reuso de refresh token', $r36['status'] === 400 && selftest_oauth_error($r36) === 'invalid_grant' && !isset($r36['json']['access_token']), 'esperado 400 invalid_grant sin access_token; real ' . selftest_oauth_resumen($r36));
}

// Caso 37: metadatos del AS tambien en la raiz del host (RFC 8414 §3.1, insercion de ruta); exige el fragmento de la raiz (docs/well-known.htaccess.example)
$e = selftest_need_or_omit('37 metadatos del AS en la raiz del host', ['MCP_PUBLIC_URL']);
if ($e !== null) {
    $pub37 = $e['MCP_PUBLIC_URL'];
    [$origen37, $ruta37] = selftest_origen_ruta($pub37);
    $r37 = selftest_http('GET', $origen37 . '/.well-known/oauth-authorization-server' . $ruta37 . '/oauth', ['Accept: application/json']);
    $iss37 = is_array($r37['json']) ? ($r37['json']['issuer'] ?? null) : null;
    selftest_check('37 metadatos del AS en la raiz del host', $r37['status'] === 200 && $iss37 === $pub37 . '/oauth', 'esperado 200 con issuer = <MCP_PUBLIC_URL>/oauth (con el fragmento de la raiz instalado); real ' . selftest_oauth_resumen($r37) . ', issuer ' . (is_string($iss37) ? ($iss37 === $pub37 . '/oauth' ? 'coincide' : 'distinto') : 'ausente'));
}

// Caso 38: metadatos del recurso en la raiz del host: mismo resource que el caso 29
$e = selftest_need_or_omit('38 metadatos del recurso en la raiz del host', ['MCP_PUBLIC_URL']);
if ($e !== null) {
    $pub38 = $e['MCP_PUBLIC_URL'];
    [$origen38, $ruta38] = selftest_origen_ruta($pub38);
    $r38   = selftest_http('GET', $origen38 . '/.well-known/oauth-protected-resource' . $ruta38 . '/public', ['Accept: application/json']);
    $res38 = is_array($r38['json']) ? ($r38['json']['resource'] ?? null) : null;
    $ok38  = $r38['status'] === 200 && $res38 === $pub38 . '/public/' && ($resource29 === null || $resource29 === $res38);
    selftest_check('38 metadatos del recurso en la raiz del host', $ok38, 'esperado 200 con resource = <MCP_PUBLIC_URL>/public/ y el mismo del caso 29 (con el fragmento de la raiz instalado); real ' . selftest_oauth_resumen($r38) . ', resource ' . (is_string($res38) ? ($res38 === $pub38 . '/public/' ? 'coincide' : 'distinto') : 'ausente') . ', caso 29 ' . ($resource29 === null ? 'sin resource' : ($resource29 === $res38 ? 'igual' : 'distinto')));
}

// Caso 39: /register rechaza redirect_uri maliciosas (userinfo y barra invertida) con invalid_redirect_uri
$e = selftest_need_or_omit('39 redirect_uri maliciosas en /register', ['MCP_PUBLIC_URL']);
if ($e !== null) {
    $malas39 = ['https://a@b/cb', 'http://evil.com\\@127.0.0.1:9999/cb'];
    foreach ($malas39 as $i39 => $uri39) {
        $r39 = selftest_oauth_register($e['MCP_PUBLIC_URL'], ['client_name' => 'coreEngine selftest', 'redirect_uris' => [$uri39]]);
        selftest_check('39 redirect_uri maliciosa #' . ($i39 + 1), $r39['status'] === 400 && selftest_oauth_error($r39) === 'invalid_redirect_uri', 'esperado 400 invalid_redirect_uri; real ' . selftest_oauth_resumen($r39));
    }
}

// Caso 40: /register sanea client_name (sin Cc/Cf/Co y representable en latin1, D-23); el cliente creado se borra al final
$e = selftest_need_or_omit('40 saneado de client_name', ['MCP_PUBLIC_URL']);
if ($e !== null) {
    $nombre40 = 'A' . mb_chr(0x202E, 'UTF-8') . 'B' . mb_chr(0x200B, 'UTF-8') . 'C' . mb_chr(0x4E2D, 'UTF-8') . 'D';
    $r40      = selftest_oauth_register($e['MCP_PUBLIC_URL'], ['client_name' => $nombre40, 'redirect_uris' => ['http://127.0.0.1:5555/cb']]);
    $dev40    = is_array($r40['json']) ? ($r40['json']['client_name'] ?? null) : null;
    $ok40     = $r40['status'] === 201 && is_string($dev40) && $dev40 !== '' && mb_check_encoding($dev40, 'UTF-8')
        && preg_match('/[\p{Cc}\p{Cf}\p{Co}]/u', $dev40) === 0 && preg_match('/[^\x{0000}-\x{00FF}]/u', $dev40) === 0
        && str_contains($dev40, 'A') && str_contains($dev40, 'B') && str_contains($dev40, 'C') && str_contains($dev40, 'D');
    selftest_check('40 saneado de client_name', $ok40, 'esperado 201 y client_name devuelto sin \p{Cc}\p{Cf}\p{Co}, representable en latin1 y con las letras A-D; real ' . selftest_oauth_resumen($r40) . ', nombre ' . (is_string($dev40) ? (preg_match('/[\p{Cc}\p{Cf}\p{Co}]/u', $dev40) === 0 ? 'sin controles' : 'con controles') . ', ' . (preg_match('/[^\x{0000}-\x{00FF}]/u', $dev40) === 0 ? 'latin1' : 'fuera de latin1') : 'ausente'));
}

// Caso 41: /register admite localhost (D-9) y rechaza localhost., x.localhost y LOCALHOST; el cliente creado se borra al final
$e = selftest_need_or_omit('41 redirect_uri localhost en /register', ['MCP_PUBLIC_URL']);
if ($e !== null) {
    $ok41r = selftest_oauth_register($e['MCP_PUBLIC_URL'], ['client_name' => 'coreEngine selftest', 'redirect_uris' => ['http://localhost:6274/cb']]);
    selftest_check('41 localhost aceptado', $ok41r['status'] === 201 && is_array($ok41r['json']) && ($ok41r['json']['redirect_uris'] ?? null) === ['http://localhost:6274/cb'], 'esperado 201 con redirect_uris identicas; real ' . selftest_oauth_resumen($ok41r));
    foreach (['http://localhost.:6274/cb', 'http://x.localhost:6274/cb', 'http://LOCALHOST:6274/cb'] as $uri41) {
        $r41 = selftest_oauth_register($e['MCP_PUBLIC_URL'], ['client_name' => 'coreEngine selftest', 'redirect_uris' => [$uri41]]);
        selftest_check('41 ' . $uri41 . ' rechazada', $r41['status'] === 400 && selftest_oauth_error($r41) === 'invalid_redirect_uri', 'esperado 400 invalid_redirect_uri; real ' . selftest_oauth_resumen($r41));
    }
}

// Limpieza: borra los clientes dinamicos creados por los casos 31, 32, 39, 40 y 41 (si hay pdo_mysql)
selftest_oauth_cleanup();

selftest_summary();
