<?php
/*******************************************************************************************************************/
/*                                              Rutas y acceso a las APIS                                          */
/*******************************************************************************************************************/
/********************* VERSION 1 *********************/
$f3->route('GET /apiList/v1/example', 'apiExample->listData');
/********************* VERSION 2 *********************/
$f3->route('GET /apiList/v2/example', 'apiExample->listDataV2');

/*******************************************************************************************************************/
/*                                             Rutas para las sesiones                                             */
/*******************************************************************************************************************/
$f3->route('POST /apiList/app/auth/logout', 'apiSession->Apilogout');    //POST - logout de las APIS
$f3->route('POST /apiList/app/auth/login',  'apiSession->ApiLogin');     //POST - Login de las APIS

/*******************************************************************************************************************/
/*                                             Rutas desde los permisos                                            */
/*******************************************************************************************************************/
$PermisosList = $f3->get('SESSION.arrPermisos');
//recorro
foreach ($PermisosList as $permiso){
    //verifico si existe
    if(isset($permiso['Metodo'],$permiso['RutaWeb'],$permiso['RutaController'])&&$permiso['Metodo']!=''&&$permiso['RutaWeb']!=''&&$permiso['RutaController']!=''){
        //Se crea ruta
        $f3->route($permiso['Metodo'].' /apiList/app/'.$permiso['RutaWeb'], $permiso['RutaController']);
    }
}
