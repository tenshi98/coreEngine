<?php
defined('MCP_ROOT') || exit;
/**
 * Pagina de error propia del servidor de autorizacion (sin detalles tecnicos).
 *
 * HTML5 autonomo, sin JS y con CSS en linea; titulo y mensaje escapados como texto. El enlace de retorno solo se
 * pinta si volverUrl no es null (el endpoint lo informa unicamente con una URI efectiva ya validada) y, por
 * defensa adicional, solo con un esquema de la lista blanca de redirect_uri (https, http, cursor, vscode).
 * Se incluye desde OAuthHttp::html('error', ...), que ya envio la CSP y X-Frame-Options.
 *
 * Datos en $data: titulo, mensaje, volverUrl.
 *
 * @see projects/mcp-oauth/specs/technical.md §14.2
 */
$h = static fn($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$volver = $data['volverUrl'] ?? null;
$volver = (is_string($volver) && preg_match('/^(https?|cursor|vscode|vscode-insiders):/i', $volver) === 1) ? $volver : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= $h($data['titulo'] ?? 'Error') ?></title>
<style>
    body { margin: 0; padding: 16px; font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif; background: #f3f4f6; color: #1f2937; line-height: 1.5; }
    main { max-width: 520px; margin: 24px auto; padding: 24px; background: #ffffff; border: 1px solid #d1d5db; border-radius: 8px; }
    h1 { margin: 0 0 16px; font-size: 1.25rem; }
    a { color: #1d4ed8; }
</style>
</head>
<body>
<main>
    <h1><?= $h($data['titulo'] ?? 'Error') ?></h1>
    <p><?= $h($data['mensaje'] ?? '') ?></p>
<?php if ($volver !== null): ?>
    <p><a href="<?= $h($volver) ?>">Volver a la aplicaci&oacute;n</a></p>
<?php endif; ?>
</main>
</body>
</html>
