<?php
/*******************************************************************************************************************/
/*                                              Rutas para usuarios logueados                                   */
/*******************************************************************************************************************/
$f3->route('POST /Api/auth/logout', 'apiSession->Apilogout');    //POST - logout de las APIS

/*******************************************************************************************************************/
/*                                              Rutas y acceso a las APIS                                          */
/*******************************************************************************************************************/
//Rutas
$f3->route('GET /install',              'installer->welcome');     //Paso 1: Bienvenida
$f3->route('POST /install/credentials', 'installer->credentials'); //Paso 2: Credenciales MySQL
$f3->route('POST /install/database',    'installer->database');    //Paso 3: Configuración de Base de Datos
$f3->route('POST /install/summary',     'installer->summary');     //Paso 4: Resumen
$f3->route('POST /install/finish',      'installer->finish');      //Paso 5: Finalización

