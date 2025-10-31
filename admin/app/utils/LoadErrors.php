<?php
/*******************************************************************************************************************/
/*                                                     Manejo de errores                                           */
/*******************************************************************************************************************/

//verifica la capa de desarrollo
$whitelist = ['localhost', '127.0.0.1', '::1', '172.18.0.1'];
$isDev     = in_array($_SERVER['REMOTE_ADDR'], $whitelist, true);
//En el caso de no estar en desarrollo ni ser superadministrador
if (!$isDev && $f3->get('SESSION.DataInfo.UserType') != 1) {
    // Página de error y manejo de rutas inexistentes
    $f3->route('GET /error', 'main->error404');
    $f3->set('ONERROR', function($f3) { $f3->reroute('/error'); });
}