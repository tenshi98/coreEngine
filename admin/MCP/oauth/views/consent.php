<?php
defined('MCP_ROOT') || exit;
/**
 * Pantalla propia del servidor de autorizacion: pegar un token manual o denegar.
 *
 * HTML5 autonomo, sin JS y con CSS en linea. TODO dato externo (nombre declarado por el cliente, redirect_uri,
 * fecha e IP de registro) se escapa como texto con htmlspecialchars; no hay imagenes ni enlaces hacia datos del
 * cliente (H-11). Se incluye desde OAuthHttp::html('consent', ...), que ya envio la CSP y X-Frame-Options.
 *
 * Datos en $data: cliente, clientePrefijo, redirectUri, registro, ip, rid, clientId, csrf, accion, error.
 *
 * @see projects/mcp-oauth/specs/technical.md §14.1
 */
$h = static fn($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Autorizar acceso al MCP de coreEngine</title>
<style>
    body { margin: 0; padding: 16px; font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif; background: #f3f4f6; color: #1f2937; line-height: 1.5; }
    main { max-width: 520px; margin: 24px auto; padding: 24px; background: #ffffff; border: 1px solid #d1d5db; border-radius: 8px; }
    h1 { margin: 0 0 16px; font-size: 1.25rem; }
    .cliente { margin: 0 0 16px; padding: 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 0.95rem; overflow-wrap: anywhere; }
    .cliente code { word-break: break-all; }
    .aviso { margin: 0 0 16px; padding: 10px 12px; background: #fef2f2; border: 1px solid #fca5a5; border-radius: 6px; color: #991b1b; }
    form { margin: 16px 0 0; }
    label { display: block; margin: 0 0 6px; font-weight: 600; }
    input[type="password"] { box-sizing: border-box; width: 100%; padding: 10px; border: 1px solid #9ca3af; border-radius: 6px; font-size: 1rem; }
    button { margin-top: 12px; padding: 10px 16px; border: 0; border-radius: 6px; font-size: 1rem; cursor: pointer; }
    button.autorizar { background: #1d4ed8; color: #ffffff; }
    button.denegar { background: #e5e7eb; color: #111827; }
    button:focus-visible, input:focus-visible { outline: 3px solid #93c5fd; outline-offset: 2px; }
</style>
</head>
<body>
<main>
    <h1>Autorizar acceso al MCP de coreEngine</h1>

    <p class="cliente">
        <strong><?= $h($data['cliente'] ?? '') ?></strong> (nombre declarado por la aplicaci&oacute;n, no verificado)
        &middot; id <code><?= $h($data['clientePrefijo'] ?? '') ?>&hellip;</code>
        &middot; registrada el <?= $h($data['registro'] ?? '') ?> desde <?= $h($data['ip'] ?? '') ?> 
        &middot; volver&aacute; a <code><?= $h($data['redirectUri'] ?? '') ?></code>
    </p>

    <p>
        Esta aplicaci&oacute;n pide acceso de <strong>solo lectura</strong> al MCP de coreEngine.
        Tendr&aacute; los permisos del token que pegue aqu&iacute;, durante un m&aacute;ximo de 90 d&iacute;as renovables.
        Apruebe solo si usted inici&oacute; esta conexi&oacute;n.
    </p>

<?php if (isset($data['error']) && $data['error'] !== null && $data['error'] !== ''): ?>
    <p class="aviso" role="alert"><?= $h($data['error']) ?></p>
<?php endif; ?>

    <form method="post" action="<?= $h($data['accion'] ?? '') ?>" autocomplete="off">
        <input type="hidden" name="rid" value="<?= $h($data['rid'] ?? '') ?>">
        <input type="hidden" name="client_id" value="<?= $h($data['clientId'] ?? '') ?>">
        <input type="hidden" name="csrf" value="<?= $h($data['csrf'] ?? '') ?>">
        <input type="hidden" name="decision" value="token">
        <label for="token">Autorizar con un token</label>
        <input type="password" id="token" name="token" autocomplete="off" autocapitalize="off" spellcheck="false" required>
        <button type="submit" class="autorizar">Autorizar</button>
    </form>

    <form method="post" action="<?= $h($data['accion'] ?? '') ?>">
        <input type="hidden" name="rid" value="<?= $h($data['rid'] ?? '') ?>">
        <input type="hidden" name="client_id" value="<?= $h($data['clientId'] ?? '') ?>">
        <input type="hidden" name="csrf" value="<?= $h($data['csrf'] ?? '') ?>">
        <input type="hidden" name="decision" value="deny">
        <button type="submit" class="denegar">Denegar</button>
    </form>
</main>
</body>
</html>
