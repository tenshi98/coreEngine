<?php
/**********************************************************************************************************************************/
/*                                                          Seguridad                                                             */
/**********************************************************************************************************************************/
require_once('../app/utils/RateLimit.php'); //Limitador de visitas

/**********************************************************************************************************************************/
/*                                                       Include classes                                                          */
/**********************************************************************************************************************************/
/**********************   Componentes   **********************/
//Se cargan componentes de la plataforma
$Autoload = '../../vendors/application/controller/;'; //Controladores
$Autoload.= ' ../../vendors/application/models/;';    //Modelos
$Autoload.= ' ../../vendors/application/utils/;';     //Utilidades
$Autoload.= ' ../../vendors/application/functions/;'; //Funciones
$Autoload.= ' ../app/helpers/;';                      //Helpers
$Autoload.= ' ../app/config/;';                       //Configuraciones

/**********************     Modulos     **********************/
//Se listan las carpetas con los modulos
$arrDirectory   = array();
$arrDirectory[] = '../app/modules/';  //Modulos de la plataforma
$arrDirectory[] = '../app/apis/';     //Apis de la plataforma
$arrDirectory[] = '../app/crones/';   //Crones de la plataforma

/**********************      Rutas      **********************/
//recorro las carpetas
foreach ($arrDirectory as $x_Directory) {
    //Se escanea la carpeta con los modulos
    $x_List = array_diff(scandir($x_Directory), ['.', '..', '.htaccess']);
    //se agregan las rutas
    foreach ($x_List as $list) {
        $Autoload .= ' ' . $x_Directory . '/' . $list . '/controller/;';
    }
}

//Base
$f3 = require('../../vendors/fatfree/base.php'); //Base
$f3->set('AUTOLOAD',$Autoload);                  //Autoload

/**********************************************************************************************************************************/
/*                                                          Variables                                                             */
/**********************************************************************************************************************************/
// Establecer la zona horaria predeterminada a usar.
date_default_timezone_set('America/Santiago');
//Se instancian otros controladores
$validateSession = new validateSession();

/*******************************************************/
//Se verifica token
$Token = isset($_COOKIE['Sesion_tk_'.date("Y-m-d")])
        ? $_COOKIE['Sesion_tk_'.date("Y-m-d")]
        : false;

/*******************************************************/
//Se verifica si existen datos
$UserSesion   = (!$f3->get('SESSION.TokenUser') || !$f3->get('SESSION.TokenExpires'))
                ? $validateSession->checkLogin($Token, $f3, getallheaders())
                : $validateSession->validateSession($Token, $f3, getallheaders());

/**********************************************************************************************************************************/
/*                                                        Usuarios Logueados                                                      */
/**********************************************************************************************************************************/
//Solo si esta activa la sesion
if($UserSesion==true){

    require_once('../app/utils/UserAdmin.php'); //Rutas de los administradores
    require_once('../app/utils/UserData.php');  //Rutas de los usuarios normales
    require_once('../app/utils/ApiList.php');   //Rutas de los administradores

}
/**********************************************************************************************************************************/
/*                                                       Usuarios Visitantes                                                      */
/**********************************************************************************************************************************/
//Rutas de los usuarios no ingresados
require_once('../app/utils/CronList.php');   //Rutas de los crones
require_once('../app/utils/UserGuest.php');  //Rutas de los usuarios no loegueados
require_once('../app/utils/LoadErrors.php'); //Manejo de los errores

//Ejecuta
$f3->run();