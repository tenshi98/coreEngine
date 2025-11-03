<?php
/*******************************************************************************************************************/
/*                                              Rutas para usuarios no logueados                                   */
/*******************************************************************************************************************/
/****************************** Plataforma ******************************/

//Login
$f3->route('GET /', 'main->login');            //Vista - Login y Recuperar Contraseña
$f3->route('GET /login', 'main->login');       //Vista - Login y Recuperar Contraseña
$f3->route('POST /login', 'miUsuario->login'); //POST - Login

//Recover password
$f3->route('POST /forgot', 'miUsuario->forgot');   //POST - Recuperar Contraseña

/****************************** API ******************************/

//Login de la API
$f3->route('POST /Api/auth/login', 'apiSession->ApiLogin');    //POST - Login de las APIS
