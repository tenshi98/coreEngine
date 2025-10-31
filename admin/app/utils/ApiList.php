<?php
/*******************************************************************************************************************/
/*                                              Rutas para usuarios logueados                                   */
/*******************************************************************************************************************/
$f3->route('POST /Api/auth/logout', 'apiSession->Apilogout');    //POST - logout de las APIS

/*******************************************************************************************************************/
/*                                              Rutas y acceso a las APIS                                          */
/*******************************************************************************************************************/
/********************* VERSION 1 *********************/
$f3->route('GET /ApiList/v1/example', 'apiExample->listData');
/********************* VERSION 2 *********************/
$f3->route('GET /ApiList/v2/example', 'apiExample->listDataV2');


/*******************************************************************************************************************/
/*                                             Rutas desde los permisos                                            */
/*******************************************************************************************************************/
$PermisosList = $f3->get('SESSION.arrPermisos');
//recorro
foreach ($PermisosList AS $permiso){
    //verifico si existe
    if(isset($permiso['Metodo'],$permiso['RutaWeb'],$permiso['RutaController'])&&$permiso['Metodo']!=''&&$permiso['RutaWeb']!=''&&$permiso['RutaController']!=''){
        //Se crea ruta
        $f3->route($permiso['Metodo'].' /ApiList/app/'.$permiso['RutaWeb'], $permiso['RutaController']);
    }
}