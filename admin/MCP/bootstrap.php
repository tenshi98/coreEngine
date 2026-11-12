<?php
ini_set('display_errors', '0'); ini_set('html_errors', '0');
/**
 * Arranque del servidor MCP de coreEngine.
 *
 * Subconjunto de admin/public/index.php: configuracion, manejo de errores,
 * limitador, F3 con AUTOLOAD absoluto. Sin sesion, CSRF, rutas ni $f3->run().
 *
 * @see projects/mcp-servidor/specs/technical.md §2
 */
error_reporting(E_ALL);
set_time_limit(30); // MCP_MAX_EXECUTION_SECONDS (McpConfig aun no existe en este punto)

/**********************************************************************************************************************************/
/*                                                       Configuraciones                                                          */
/**********************************************************************************************************************************/
$mcpAdmin = dirname(__DIR__);        // .../admin
$mcpRoot  = dirname($mcpAdmin);      // .../ (raiz del proyecto: vendors/, admin/)

require_once $mcpAdmin . '/app/config/ConfigAPP.php';
require_once $mcpAdmin . '/app/config/ConfigDataBase.php';
require_once $mcpAdmin . '/app/config/ConfigToken.php';

/**********************************************************************************************************************************/
/*                                                          Seguridad                                                             */
/**********************************************************************************************************************************/
require_once $mcpRoot . '/vendors/application/security/ErrorHandler.php';
ErrorHandler::register();
require_once $mcpRoot . '/vendors/application/security/RateLimiter.php';

/**********************************************************************************************************************************/
/*                                                          Constantes                                                            */
/**********************************************************************************************************************************/
define('MCP_ROOT', __DIR__);
define('MCP_MODULES_PATH', $mcpAdmin . '/app/modules/');

/**********************************************************************************************************************************/
/*                                                       Include classes                                                          */
/**********************************************************************************************************************************/
$Autoload  = $mcpRoot . '/vendors/application/controller/;';
$Autoload .= $mcpRoot . '/vendors/application/models/;';
$Autoload .= $mcpRoot . '/vendors/application/utils/;';
$Autoload .= $mcpRoot . '/vendors/application/functions/;';
$Autoload .= $mcpAdmin . '/app/helpers/;';
$Autoload .= MCP_ROOT . '/config/;';
foreach (['Server', 'Auth', 'Discovery', 'Tools', 'Resources', 'Audit', 'Security', 'OAuth'] as $mcpDir) {
    $Autoload .= MCP_ROOT . '/src/' . $mcpDir . '/;';
}
foreach (array_diff(scandir(MCP_MODULES_PATH), ['.', '..', '.htaccess']) as $mcpModule) {
    $Autoload .= MCP_MODULES_PATH . $mcpModule . '/installer/;';
}
$Autoload .= MCP_MODULES_PATH . 'tokens/functions/;'; // InternalFunctionsTokensOAuth (reglas de redirect_uri del AS)

/**********************************************************************************************************************************/
/*                                                             Sistema                                                            */
/**********************************************************************************************************************************/
$f3 = require_once $mcpRoot . '/vendors/fatfree/base.php';
$f3->set('AUTOLOAD', $Autoload);
$f3->set('QUIET', true);
$f3->set('DEBUG', 0);
// Error generico: el detalle va solo a error_log, nunca al cliente (H-2)
$f3->set('ONERROR', function ($f3) {
    error_log('MCP error: ' . $f3->get('ERROR.text') . ' @ ' . $f3->get('ERROR.trace.0.file'));
    $body = '{"jsonrpc":"2.0","id":null,"error":{"code":-32603,"message":"Internal error"}}';
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, $body . PHP_EOL);
    } elseif (!headers_sent()) {
        http_response_code(200);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');
        echo $body;
    }
    exit;
});
unset($mcpAdmin, $mcpRoot, $mcpDir, $mcpModule);
