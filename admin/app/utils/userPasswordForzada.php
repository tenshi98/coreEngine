<?php
/*******************************************************************************************************************/
/*                                   Cambio de password forzado (primer ingreso)                                   */
/*******************************************************************************************************************/
// Mientras el usuario no cambie su password inicial solo puede usar Mi Usuario y cerrar sesion.
// No se registran las rutas de administracion, de permisos ni de funcionalidad del sistema.
$f3->route('GET /principal', function($f3){ $f3->reroute('/perfil'); });  // Cualquier acceso al inicio lleva al perfil
require_once __DIR__ . '/userMiUsuario.php';
