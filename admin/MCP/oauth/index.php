<?php
ini_set('display_errors', '0'); ini_set('html_errors', '0');
/**
 * Front controller del servidor de autorizacion OAuth 2.1 del MCP (issuer = <MCP_PUBLIC_URL>/oauth).
 *
 * Los bloques se ejecutan en este orden: 1 arranque y configuracion, 2 enrutado, CORS y despacho.
 * Toda la persistencia usa McpDb::conn(); la sesion del backoffice no interviene.
 *
 * @see projects/mcp-oauth/specs/technical.md §3.2
 */
require_once dirname(__DIR__) . '/bootstrap.php';

/**********************************************************************************************************************************/
/*                                             Bloque 1: arranque y configuracion                                                 */
/**********************************************************************************************************************************/
/**
 * Respuesta de error generica del AS: 500 server_error sin detalle.
 *
 * Solo se registra la clase del fallo (o el codigo de error de F3): nunca ERROR.text completo ni la traza, que pueden
 * contener valores enlazados o secretos (H-19).
 *
 * @param Throwable|null $e Fallo capturado (null cuando lo invoca ONERROR de F3)
 * @return never
 */
function oauth_fail(?Throwable $e = null): never
{
    $f3 = Base::instance();
    error_log('OAuth error: ' . ($e !== null ? get_class($e) : (string) $f3->get('ERROR.code')));
    if (!headers_sent()) {
        OAuthHttp::error(500, 'server_error');
    }
    exit;
}

$f3 = Base::instance();
$f3->set('ONERROR', fn() => oauth_fail());

if (McpConfig::issuerUrl() === null) {
    OAuthHttp::error(503, 'temporarily_unavailable', 'OAuth not configured');
}

/**********************************************************************************************************************************/
/*                                          Bloque 2: enrutado, CORS y despacho                                                   */
/**********************************************************************************************************************************/
/**
 * Responde 204 sin cuerpo (OPTIONS) con las cabeceras comunes del AS y termina.
 *
 * @return never
 */
function oauth_no_content(): never
{
    http_response_code(204);
    header('Cache-Control: no-store');
    header('Pragma: no-cache');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: no-referrer');
    exit;
}

$oauthMetodo = (string) ($_SERVER['REQUEST_METHOD'] ?? '');
$oauthWk     = is_string($_GET['wk'] ?? null) ? $_GET['wk'] : '';
$oauthPath   = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
$oauthRuta   = rtrim((string) preg_replace('#^.*/oauth(?=/|$)#', '', $oauthPath), '/');

// [condicion, metodos admitidos, modo CORS, despacho] en el orden de specs/technical.md §3.2
$oauthRutas = [
    [$oauthWk === 'prm' || $oauthRuta === '/.well-known/oauth-protected-resource', ['GET', 'OPTIONS'], 'publico', 'prm'],
    [$oauthWk === 'as' || $oauthRuta === '/.well-known/oauth-authorization-server', ['GET', 'OPTIONS'], 'publico', 'as'],
    [$oauthRuta === '/register', ['POST', 'OPTIONS'], 'restringido', 'register'],
    [$oauthRuta === '/authorize', ['GET', 'POST'], 'ninguno', 'authorize'],
    [$oauthRuta === '/token', ['POST', 'OPTIONS'], 'restringido', 'token'],
    [$oauthRuta === '/revoke', ['POST', 'OPTIONS'], 'restringido', 'revoke'],
];

$oauthDestino = null;
foreach ($oauthRutas as [$oauthCond, $oauthMetodos, $oauthCors, $oauthAccion]) {
    if (!$oauthCond) {
        continue;
    }
    OAuthHttp::cors($oauthCors);
    if (!in_array($oauthMetodo, $oauthMetodos, true)) {
        OAuthHttp::error(405, 'invalid_request', 'Method not allowed', ['Allow' => implode(', ', $oauthMetodos)]);
    }
    if ($oauthMetodo === 'OPTIONS') {
        oauth_no_content();
    }
    $oauthDestino = $oauthAccion;
    break;
}
if ($oauthDestino === null) {
    OAuthHttp::error(404, 'invalid_request', 'Unknown endpoint');
}

try {
    switch ($oauthDestino) {
        case 'prm':
            OAuthHttp::json(200, OAuthMetadata::protectedResource());
        case 'as':
            OAuthHttp::json(200, OAuthMetadata::authorizationServer());
        case 'register':
            (new OAuthRegisterEndpoint())->handle();
        case 'authorize':
            $oauthAuthorize = new OAuthAuthorizeEndpoint();
            $oauthMetodo === 'GET' ? $oauthAuthorize->start() : $oauthAuthorize->decide();
        case 'token':
            (new OAuthTokenEndpoint())->handle();
        case 'revoke':
            (new OAuthRevokeEndpoint())->handle();
    }
} catch (Throwable $e) {
    // Fallo al construir un endpoint (p. ej. sin conexion a la BD); los endpoints ya capturan los suyos
    if ($oauthDestino === 'authorize') {
        error_log('OAuth: ' . get_class($e));
        OAuthHttp::html('error', ['titulo' => 'Error del servidor', 'mensaje' => 'No se pudo completar la solicitud. Inténtelo más tarde.', 'volverUrl' => null], 500);
    }
    oauth_fail($e);
}
