<?php
/*******************************************************************************************************************/
/*                                              Rutas para usuarios logueados                                   */
/*******************************************************************************************************************/
$f3->route('POST /Api/auth/logout', 'apiSession->Apilogout');    //POST - logout de las APIS

/*******************************************************************************************************************/
/*                                              Rutas y acceso a las APIS                                          */
/*******************************************************************************************************************/
//Paso 1: Bienvenida
$f3->route('GET /install', 'installer->welcome');
$f3->route('GET /install/welcome', 'installer->welcome');
$f3->route('POST /install/welcome', 'installer->welcome');

//Paso 2: Credenciales MySQL
$f3->route('GET /install/credentials', 'installer->credentials');
$f3->route('POST /install/credentials', 'installer->credentials');

//Paso 3: Configuración de Base de Datos
$f3->route('GET /install/database', 'installer->database');
$f3->route('POST /install/database', 'installer->database');

//Paso 4: Resumen
$f3->route('GET /install/summary', 'installer->summary');
$f3->route('POST /install/summary', 'installer->summary');

//Paso 5: Finalización
$f3->route('GET /install/finish', 'installer->finish');
$f3->route('POST /install/finish', 'installer->finish');

