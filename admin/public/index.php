<?php
/**********************************************************************************************************************************/
/*                                                       Configuraciones                                                          */
/**********************************************************************************************************************************/
// Se cargan los archivos
require_once __DIR__ . '/../app/config/ConfigAPP.php';
require_once __DIR__ . '/../app/config/ConfigDataBase.php';
require_once __DIR__ . '/../app/config/ConfigMail.php';
require_once __DIR__ . '/../app/config/ConfigToken.php';
//verifica la capa de desarrollo
$whitelist = ['localhost', '127.0.0.1', '::1', '172.18.0.1'];
$isDev     = in_array($_SERVER['REMOTE_ADDR'], $whitelist, true);

/**********************************************************************************************************************************/
/*                                                          Seguridad                                                             */
/**********************************************************************************************************************************/
// Manejo seguro de Errores (no exponer lógica a los usuarios)
error_reporting(E_ALL);
// En el caso de no estar en desarrollo
if (!$isDev) {
    // Ocultar errores puros en producción
    ini_set('display_errors', 0);
}

// Guarda los errores en la carpeta configurada
require_once __DIR__ . '/../../vendors/application/security/ErrorHandler.php';
ErrorHandler::register();

// Limitador de solicitudes (por IP o por usuario logueado)
require_once __DIR__ . '/../../vendors/application/security/RateLimiter.php';

// Sistema de registro (logging) de auditoría para operaciones del sistema.
require_once __DIR__ . '/../../vendors/application/security/AuditLogger.php';

/**********************************************************************************************************************************/
/*                                                       Include classes                                                          */
/**********************************************************************************************************************************/
/**********************   Componentes   **********************/
// Se cargan componentes de la plataforma
$Autoload = '../../vendors/application/controller/;'; //Controladores
$Autoload.= ' ../../vendors/application/models/;';    //Modelos
$Autoload.= ' ../../vendors/application/utils/;';     //Utilidades
$Autoload.= ' ../../vendors/application/functions/;'; //Funciones
$Autoload.= ' ../app/helpers/;';                      //Helpers

/**********************     Modulos     **********************/
// Se listan las carpetas con los modulos
$arrDirectory   = array();
$arrDirectory[] = '../app/modules/';  //Modulos de la plataforma

/**********************      Rutas      **********************/
// Recorro las carpetas
foreach ($arrDirectory as $x_Directory) {
    //Se escanea la carpeta con los modulos
    $x_List = array_diff(scandir($x_Directory), ['.', '..', '.htaccess']);
    //se agregan las rutas
    foreach ($x_List as $list) {
        $Autoload .= ' ' . $x_Directory . '/' . $list . '/controller/;';
    }
}

/**********************     Sistema     **********************/
// Se carga el Framework
$f3 = require_once('../../vendors/fatfree/base.php'); //Base
$f3->set('AUTOLOAD',$Autoload);                       //Autoload

/**********************************************************************************************************************************/
/*                                                       Sesion Usuario                                                           */
/**********************************************************************************************************************************/
// Establecer la zona horaria predeterminada a usar.
date_default_timezone_set('America/Santiago');
// Se instancian otros controladores
$sessionService = new SessionService();

/*******************************************************/
// Se verifica token
$cookieToken = isset($_COOKIE['Sesion_tk'])
             ? $_COOKIE['Sesion_tk']
             : false;

/*******************************************************/
// Se verifica la sesion del servidor contra la base de datos
$UserSesion   = (!$f3->get('SESSION.TokenUser') || !$f3->get('SESSION.TokenExpires'))
                ? $sessionService->check($f3, $cookieToken)
                : $sessionService->validate($cookieToken, $f3->get('SESSION.TokenExpires'), $f3->get('SESSION.TokenUser'), $f3->get('SESSION.DataInfo'));

/**********************************************************************************************************************************/
/*                                                        Usuarios Logueados                                                      */
/**********************************************************************************************************************************/
//Solo si esta activa la sesion
if($UserSesion===true){

    // Se registran las Rutas
    require_once __DIR__ . '/../app/utils/userAdmin.php';             // Rutas de los administradores
    require_once __DIR__ . '/../app/utils/userData.php';              // Rutas de los usuarios normales
    require_once __DIR__ . '/../app/utils/sistemaFuncionalidad.php';  // Funcionalidad del sistema

    // Numero de conexiones maximas por minuto
    $userId       = $f3->get('SESSION.DataInfo.UserID');
    $rateLimited  = $userId
                  ? RateLimiter::isBlockedByUser($userId)
                  : RateLimiter::isBlockedByIp($_SERVER['REMOTE_ADDR']);
} else {
    // Numero de conexiones maximas por minuto
    $rateLimited = RateLimiter::isBlockedByIp($_SERVER['REMOTE_ADDR']);
}

// Corta la ejecucion si se supero el limite de solicitudes permitidas
if ($rateLimited) {
    http_response_code(429);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 429, 'message' => 'Demasiadas solicitudes. Intente más tarde.']);
    exit;
}

/**********************************************************************************************************************************/
/*                                                       Usuarios Visitantes                                                      */
/**********************************************************************************************************************************/
// Rutas de los usuarios no ingresados
require_once __DIR__ . '/../app/utils/userGuest.php';  // Rutas de los usuarios no loegueados
require_once __DIR__ . '/../app/utils/loadErrors.php'; // Manejo de los errores
require_once __DIR__ . '/../app/utils/installer.php';  // Instalador de la plataforma

//Ejecuta
$f3->run();
