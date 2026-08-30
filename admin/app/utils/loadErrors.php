<?php
/*******************************************************************************************************************/
/*                                                     Manejo de errores                                           */
/*******************************************************************************************************************/

//En el caso de no estar en desarrollo ni ser superadministrador
if (!$isDev && $f3->get('SESSION.DataInfo.UserType') != 1) {
    // Página de error y manejo de rutas inexistentes
    $f3->route('GET /error', 'main->error404');
    $f3->set('ONERROR', function($f3) { $f3->reroute('/error'); });
}
