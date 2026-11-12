<?php
ini_set('display_errors', '0'); ini_set('html_errors', '0');
/**
 * Transporte HTTP del servidor MCP de coreEngine (Streamable HTTP, solo POST).
 *
 * Los bloques se ejecutan en este orden (H-4): 1 arranque y envio, 2 metodo HTTP,
 * 3 limite por IP, 4 token en URL, 5 Origin, 6 autenticacion, 7 limite por token,
 * 8 alcance, 9 cuerpo, 10 despacho, 11 auditoria.
 *
 * @see projects/mcp-servidor/specs/technical.md §6
 */
require_once dirname(__DIR__) . '/bootstrap.php';

/**********************************************************************************************************************************/
/*                                                  Bloque 1: arranque y envio                                                    */
/**********************************************************************************************************************************/
/**
 * Envia la respuesta JSON y termina.
 *
 * CORS solo para un Origin identico a uno permitido: origen exacto, Vary: Origin,
 * nunca Allow-Credentials (H-18).
 *
 * @param int        $status  Codigo HTTP
 * @param array|null $body    Cuerpo JSON-RPC (null = sin cuerpo)
 * @param array      $headers Cabeceras adicionales [nombre => valor]
 * @return never
 */
function mcp_send(int $status, ?array $body, array $headers = []): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    $origin = $_SERVER['HTTP_ORIGIN'] ?? null;
    if (is_string($origin) && in_array($origin, McpConfig::allowedOrigins(), true)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Vary: Origin');
        header('Access-Control-Expose-Headers: MCP-Protocol-Version, WWW-Authenticate');
    }
    foreach ($headers as $name => $value) {
        header($name . ': ' . $value);
    }
    if ($body !== null) {
        echo JsonRpc::encode($body);
    }
    exit;
}

/**********************************************************************************************************************************/
/*                                                 Bloque 2: metodo HTTP y CORS                                                   */
/**********************************************************************************************************************************/
$mcpMethod = $_SERVER['REQUEST_METHOD'] ?? '';
if ($mcpMethod === 'OPTIONS') {
    $mcpOrigin = $_SERVER['HTTP_ORIGIN'] ?? null;
    if (is_string($mcpOrigin) && in_array($mcpOrigin, McpConfig::allowedOrigins(), true)) {
        mcp_send(204, null, [
            'Access-Control-Allow-Methods' => 'POST, OPTIONS',
            'Access-Control-Allow-Headers' => 'Authorization, Content-Type, Accept, MCP-Protocol-Version, Mcp-Session-Id',
            'Access-Control-Max-Age'       => '600',
        ]);
    }
    mcp_send(403, JsonRpc::error(null, -32003, 'Forbidden'));
}
if ($mcpMethod !== 'POST') {
    mcp_send(405, JsonRpc::error(null, -32600, 'Invalid Request'), ['Allow' => 'POST, OPTIONS']);
}

/**********************************************************************************************************************************/
/*                                                  Bloque 3: limite por IP                                                       */
/**********************************************************************************************************************************/
// Una llamada por peticion; los mensajes adicionales de un lote se cuentan en el bloque 10 (H-5)
$mcpIp = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
if (McpRateLimit::checkIp($mcpIp)) {
    $mcpVentanaIp = (int) ConfigAPP::APP['rateLimiterGuestWindowSeconds'];
    // Se audita solo la transicion a bloqueada, para que un flujo anonimo no inunde tokens_mcp_log (H-4)
    if (McpRateLimit::isTransition('mcp-ip:' . $mcpIp, $mcpVentanaIp)) {
        try {
            (new McpAuditLog())->record(0, 'http', null, null, '429-ip', $mcpIp);
        } catch (Throwable $e) {
            error_log('MCP: no se pudo registrar la auditoria');
        }
    }
    mcp_send(429, JsonRpc::error(null, -32004, 'Too many requests'), ['Retry-After' => (string) $mcpVentanaIp]);
}

/**********************************************************************************************************************************/
/*                                                  Bloque 4: token en la URL                                                     */
/**********************************************************************************************************************************/
// Un token en la query string acabaria en logs y referers: se rechaza sin auditar ni reflejar el valor (H-17)
$mcpQuery = (string) ($_SERVER['QUERY_STRING'] ?? '');
if (
    preg_match('/[A-Fa-f0-9]{64}/', $mcpQuery) === 1
    || preg_match('/[A-Fa-f0-9]{64}/', urldecode($mcpQuery)) === 1
    || isset($_GET['token']) || isset($_GET['access_token'])
) {
    mcp_send(400, JsonRpc::error(null, -32600, 'Invalid Request'));
}

/**********************************************************************************************************************************/
/*                                                      Bloque 5: Origin                                                          */
/**********************************************************************************************************************************/
/**
 * Registra una fila de auditoria sin romper la respuesta si falla.
 *
 * @param int         $idToken     Token autenticado (0 si no se identifico)
 * @param string      $metodo      Metodo JSON-RPC o "http"
 * @param string|null $herramienta Tool o URI
 * @param mixed       $argumentos  Argumentos del mensaje
 * @param string      $resultado   "ok", codigo JSON-RPC o rechazo HTTP
 * @param int|null    $idClienteOAuth Cliente OAuth del acceso (null si el token es manual o no se identifico)
 * @return void
 */
function mcp_audit(int $idToken, string $metodo, ?string $herramienta, mixed $argumentos, string $resultado, ?int $idClienteOAuth = null): void
{
    try {
        (new McpAuditLog())->record($idToken, $metodo, $herramienta, $argumentos, $resultado, (string) ($_SERVER['REMOTE_ADDR'] ?? ''), $idClienteOAuth);
    } catch (Throwable $e) {
        error_log('MCP: no se pudo registrar la auditoria');
    }
}

// Un Origin presente debe ser identico a uno permitido ("null" y "*" nunca); sin Origin (clientes no navegador) se continua (H-18)
if (isset($_SERVER['HTTP_ORIGIN']) && !in_array((string) $_SERVER['HTTP_ORIGIN'], McpConfig::allowedOrigins(), true)) {
    mcp_audit(0, 'http', null, null, '403-origen');
    mcp_send(403, JsonRpc::error(null, -32003, 'Forbidden'));
}

/**********************************************************************************************************************************/
/*                                                  Bloque 6: autenticacion                                                       */
/**********************************************************************************************************************************/
/**
 * Respuesta 401 unica: cuerpo y cabeceras identicos para ausente, inexistente, inactivo, caducado,
 * expirado, padre y cliente (H-15). Anade resource_metadata solo si MCP_PUBLIC_URL es valida (D-16).
 *
 * @return never
 */
function mcp_unauthorized(): never
{
    $challenge   = 'Bearer realm="coreEngine-MCP", error="invalid_token"';
    $metadataUrl = McpConfig::resourceMetadataUrl();
    if ($metadataUrl !== null) {
        $challenge .= ', resource_metadata="' . $metadataUrl . '"';
    }
    mcp_send(401, JsonRpc::error(null, -32001, 'Unauthorized'), ['WWW-Authenticate' => $challenge]);
}

/**
 * Respuesta -32603 generica para fallos de infraestructura (sin detalle, H-8).
 *
 * @return never
 */
function mcp_internal_error(): never
{
    mcp_send(200, JsonRpc::error(null, -32603, 'Internal error'));
}

try {
    $mcpAuth   = new TokenAuthenticator();
    $mcpBearer = $mcpAuth->extractBearer();
    if ($mcpBearer === null) {
        mcp_audit(0, 'http', null, null, '401-ausente');
        mcp_unauthorized();
    }
    $mcpValid = $mcpAuth->validate($mcpBearer);
} catch (McpException $e) {
    error_log('MCP: fallo al autenticar (' . $e->getCode() . ')');
    mcp_internal_error();
}
unset($mcpBearer);
$mcpIdToken = (int) $mcpValid['idToken'];
// La cuota es del token efectivo: el padre de un acceso OAuth, o el propio token manual (no se elude abriendo varias concesiones)
$mcpIdEfectivo = (int) ($mcpValid['idTokenPadre'] ?? $mcpIdToken);
$mcpIdCliente  = isset($mcpValid['idClienteOAuth']) ? (int) $mcpValid['idClienteOAuth'] : null;
if ($mcpValid['estado'] !== 'ok') {
    mcp_audit($mcpIdEfectivo, 'http', null, null, '401-' . $mcpValid['estado'], $mcpIdCliente);
    mcp_unauthorized();
}

/**********************************************************************************************************************************/
/*                                                  Bloque 7: limite por token                                                    */
/**********************************************************************************************************************************/
if (McpRateLimit::checkToken($mcpIdEfectivo)) {
    $mcpVentanaToken = (int) ConfigAPP::APP['rateLimiterUserWindowSeconds'];
    // Se audita solo la transicion a bloqueado, para no inflar tokens_mcp_log (H-4)
    if (McpRateLimit::isTransition('mcp-token:' . $mcpIdEfectivo, $mcpVentanaToken)) {
        mcp_audit($mcpIdEfectivo, 'http', null, null, '429-token', $mcpIdCliente);
    }
    mcp_send(429, JsonRpc::error(null, -32004, 'Too many requests'), ['Retry-After' => (string) $mcpVentanaToken]);
}

/**********************************************************************************************************************************/
/*                                                     Bloque 8: alcance                                                          */
/**********************************************************************************************************************************/
try {
    $scope = TokenScope::load($mcpIdToken);
} catch (McpException $e) {
    error_log('MCP: fallo al cargar el alcance (' . $e->getCode() . ')');
    mcp_internal_error();
}
if ($scope->isEmpty()) {
    mcp_audit($mcpIdEfectivo, 'http', null, null, '403-sin-alcance', $mcpIdCliente);
    mcp_send(403, JsonRpc::error(null, -32003, 'Forbidden'));
}

/**********************************************************************************************************************************/
/*                                                      Bloque 9: cuerpo                                                          */
/**********************************************************************************************************************************/
// Tamaño: primero por CONTENT_LENGTH (sin leer) y despues con lectura acotada, por si la cabecera miente o falta (H-13)
if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > McpConfig::MAX_BODY_BYTES) {
    mcp_send(413, JsonRpc::error(null, -32600, 'Invalid Request'));
}
$mcpBody = file_get_contents('php://input', false, null, 0, McpConfig::MAX_BODY_BYTES + 1);
if ($mcpBody === false || strlen($mcpBody) > McpConfig::MAX_BODY_BYTES) {
    mcp_send(413, JsonRpc::error(null, -32600, 'Invalid Request'));
}
$mcpContentType = (string) ($_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '');
if (stripos(ltrim($mcpContentType), 'application/json') !== 0) {
    mcp_send(415, JsonRpc::error(null, -32600, 'Invalid Request'));
}
if (isset($_SERVER['HTTP_MCP_PROTOCOL_VERSION']) && !in_array((string) $_SERVER['HTTP_MCP_PROTOCOL_VERSION'], McpConfig::PROTOCOL_VERSIONS, true)) {
    mcp_send(400, JsonRpc::error(null, -32600, 'Invalid Request'));
}

/**********************************************************************************************************************************/
/*                                                     Bloque 10: despacho                                                        */
/**********************************************************************************************************************************/
/**
 * Responde 429 por un mensaje adicional de un lote, auditando solo la transicion (H-4, H-5).
 *
 * @param string $clave     Clave del limitador (mcp-ip:<ip> o mcp-token:<id>)
 * @param int    $ventana   Ventana del limitador en segundos
 * @param string $resultado Resultado de auditoria (429-ip o 429-token)
 * @return never
 */
function mcp_too_many(string $clave, int $ventana, string $resultado): never
{
    global $mcpIdEfectivo, $mcpIdCliente;
    if (McpRateLimit::isTransition($clave, $ventana)) {
        mcp_audit($resultado === '429-token' ? $mcpIdEfectivo : 0, 'http', null, null, $resultado, $resultado === '429-token' ? $mcpIdCliente : null);
    }
    mcp_send(429, JsonRpc::error(null, -32004, 'Too many requests'), ['Retry-After' => (string) $ventana]);
}

try {
    $mcpParsed = JsonRpc::parse($mcpBody);
} catch (McpException $e) {
    mcp_send(400, JsonRpc::error(null, $e->getCode(), $e->getMessage()));
}
unset($mcpBody);
$mcpMessages = $mcpParsed['messages'];

// Cada mensaje de un lote cuenta contra los limites (H-5): el primero ya se conto en los bloques 3 y 7
for ($mcpI = 1; $mcpI < count($mcpMessages); $mcpI++) {
    if (McpRateLimit::checkIp($mcpIp)) {
        mcp_too_many('mcp-ip:' . $mcpIp, (int) ConfigAPP::APP['rateLimiterGuestWindowSeconds'], '429-ip');
    }
    if (McpRateLimit::checkToken($mcpIdEfectivo)) {
        mcp_too_many('mcp-token:' . $mcpIdEfectivo, (int) ConfigAPP::APP['rateLimiterUserWindowSeconds'], '429-token');
    }
}

try {
    $catalogo  = (new ModuleScanner())->getCatalog();
    $mcpServer = new McpServer(new ToolRegistry($catalogo, $scope), new ResourceRegistry($catalogo, $scope));
} catch (Throwable $e) {
    error_log('MCP: fallo al construir el servidor: ' . get_class($e));
    mcp_internal_error();
}

$mcpResponses  = [];
$mcpDispatched = [];
$mcpMaxResp    = McpConfig::limit('MAX_RESPONSE_BYTES');
foreach ($mcpMessages as $mcpMsg) {
    try {
        JsonRpc::validate($mcpMsg);
        $mcpResp = $mcpServer->handle($mcpMsg);
    } catch (McpException $e) {
        $mcpId   = (is_array($mcpMsg) && isset($mcpMsg['id']) && (is_string($mcpMsg['id']) || is_int($mcpMsg['id']))) ? $mcpMsg['id'] : null;
        $mcpResp = JsonRpc::error($mcpId, $e->getCode(), $e->getMessage());
    }
    if ($mcpResp !== null && strlen(JsonRpc::encode($mcpResp)) > $mcpMaxResp) {
        $mcpResp = JsonRpc::error($mcpResp['id'], -32602, 'Respuesta demasiado grande: reduzca limit o columns');
    }
    if ($mcpResp !== null) {
        $mcpResponses[] = $mcpResp;
    }
    $mcpDispatched[] = ['msg' => $mcpMsg, 'resp' => $mcpResp];
}

/**********************************************************************************************************************************/
/*                                                     Bloque 11: auditoria                                                       */
/**********************************************************************************************************************************/
// Una fila por mensaje despachado (lotes y notificaciones incluidos), antes de enviar la respuesta
foreach ($mcpDispatched as $mcpItem) {
    $mcpMsg    = $mcpItem['msg'];
    $mcpParams = (is_array($mcpMsg) && isset($mcpMsg['params']) && is_array($mcpMsg['params'])) ? $mcpMsg['params'] : [];
    $mcpMetodo = (is_array($mcpMsg) && isset($mcpMsg['method']) && is_string($mcpMsg['method'])) ? $mcpMsg['method'] : 'invalid';
    $mcpHerr   = null;
    if ($mcpMetodo === 'tools/call' && isset($mcpParams['name']) && is_string($mcpParams['name'])) {
        $mcpHerr = $mcpParams['name'];
    } elseif ($mcpMetodo === 'resources/read' && isset($mcpParams['uri']) && is_string($mcpParams['uri'])) {
        $mcpHerr = $mcpParams['uri'];
    }
    $mcpRes = (is_array($mcpItem['resp']) && isset($mcpItem['resp']['error']['code'])) ? (string) $mcpItem['resp']['error']['code'] : 'ok';
    mcp_audit($mcpIdEfectivo, $mcpMetodo, $mcpHerr, $mcpParams['arguments'] ?? null, $mcpRes, $mcpIdCliente);
}

// Sin respuestas (solo notificaciones) -> 202; con respuestas -> 200 (objeto o array si era un lote)
if ($mcpResponses === []) {
    mcp_send(202, null);
}
mcp_send(200, $mcpParsed['batch'] ? $mcpResponses : $mcpResponses[0]);
