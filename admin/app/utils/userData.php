<?php
/*******************************************************************************************************************/
/*                                            Pantalla de inicio                                                   */
/*******************************************************************************************************************/
$f3->route('GET /principal',                                      'main->principal');                                    //pantalla principal
$f3->route('GET /principal/campanaResumen/@ano',                  'gestionCampanasWidgets->campanaResumen');             //Resumen Campañas
$f3->route('GET /principal/campanaCompSemanalResumen/@mes/@ano',  'gestionCampanasWidgets->campanaCompSemanalResumen');  //Resumen Semanal
$f3->route('GET /principal/campanaAnalisisContable/@ano',         'gestionCampanasWidgets->campanaAnalisisContable');    //Analisis Contable
$f3->route('GET /principal/campanaPartidaConfirmada',             'gestionCampanasWidgets->campanaPartidaConfirmada');   //Últimas partidas confirmadas
$f3->route('GET /principal/docMercantilesCompras',                'gestionDocumentosWidgets->pagosPendientesCompras'); //Pagos pendientes de documentos mercantiles (Compras)
$f3->route('GET /principal/docMercantilesVentas',                 'gestionDocumentosWidgets->pagosPendientesVentas');  //Pagos pendientes de documentos mercantiles (Ventas)
$f3->route('GET /principal/bodegaStock',                          'bodegasWidgets->stocksProductos');                    // Productos con bajo Stock en las bodegas
$f3->route('GET /principal/reservas',                          'reservasWidgets->reservasSolicitadas');                    // Productos con bajo Stock en las bodegas

// Rutas de Mi Usuario (compartidas con el modo de cambio de password forzado)
require_once __DIR__ . '/userMiUsuario.php';

/*******************************************************************************************************************/
/*                                             Rutas desde los permisos                                            */
/*******************************************************************************************************************/
$PermisosList = $f3->get('SESSION.arrPermisos');
// Recorro
foreach ($PermisosList as $permiso){
    //verifico si existe
    if(isset($permiso['Metodo'],$permiso['RutaWeb'],$permiso['RutaController'])&&$permiso['Metodo']!=''&&$permiso['RutaWeb']!=''&&$permiso['RutaController']!=''){
        //Se crea ruta
        $f3->route($permiso['Metodo'].' /'.$permiso['RutaWeb'], $permiso['RutaController']);
    }
}
