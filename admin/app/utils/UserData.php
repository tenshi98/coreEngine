<?php
/*******************************************************************************************************************/
/*                                            Pantalla de inicio                                                   */
/*******************************************************************************************************************/
$f3->route('GET /principal', 'main->principal');                                                      //pantalla principal
$f3->route('GET /principal/resumenCampana/@id', 'mainCampanas->resumenCampana');                      //Resumen Campañas
$f3->route('GET /principal/campanaAnalisisContable/@id', 'mainCampanas->campanaAnalisisContable');    //Analisis Contable
$f3->route('GET /principal/partidaConfirmada', 'mainCampanas->partidaConfirmada');                    //Últimas partidas confirmadas
$f3->route('GET /principal/docMercantiles', 'mainDocMercantiles->pagosPendientes');                   //Pagos pendientes de los documentos mercantiles
$f3->route('GET /principal/bodegaStock', 'mainBodegas->stocksProductos');                             //Productos con bajo Stock en las bodegas

/*******************************************************************************************************************/
/*                                                 Mi Usuario                                                      */
/*******************************************************************************************************************/
//Vistas
$f3->route('GET /perfil', 'miUsuario->view');                   //Ver mi perfil
//Fragments
$f3->route('GET /perfil/UpdateData', 'miUsuario->FRG_UpdateData');  //Actualizar datos del perfil
$f3->route('GET /perfil/UpdateCard', 'miUsuario->FRG_UpdateCard');  //Actualizar imagen del perfil
//Acciones
$f3->route('GET /auth/logout', 'miUsuario->logout');        //Cerrar la sesion
$f3->route('POST /perfil/update', 'miUsuario->update');     //Editar por post (modificar y subir archivos)
$f3->route('PUT /perfil/delFiles', 'miUsuario->delFiles');  //Permite eliminar el archivo de la imagen

/*******************************************************************************************************************/
/*                                             Rutas desde los permisos                                            */
/*******************************************************************************************************************/
$PermisosList = $f3->get('SESSION.arrPermisos');
//recorro
foreach ($PermisosList AS $permiso){
    //verifico si existe
    if(isset($permiso['Metodo'],$permiso['RutaWeb'],$permiso['RutaController'])&&$permiso['Metodo']!=''&&$permiso['RutaWeb']!=''&&$permiso['RutaController']!=''){
        //Se crea ruta
        $f3->route($permiso['Metodo'].' /'.$permiso['RutaWeb'], $permiso['RutaController']);
    }
}
