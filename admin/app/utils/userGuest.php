<?php
/*******************************************************************************************************************/
/*                                              Rutas para usuarios no logueados                                   */
/*******************************************************************************************************************/
/****************************** Plataforma ******************************/

//Login
$f3->route('GET  /',      'main->login');       //Vista - Login y Recuperar Contraseña
$f3->route('GET  /login', 'main->login');       //Vista - Login y Recuperar Contraseña
$f3->route('POST /login', 'miUsuario->login');  //POST - Login

//Recover password
$f3->route('POST /forgot', 'miUsuario->forgot');   //POST - Recuperar Contraseña

//Reset password (enlace recibido por correo)
$f3->route('GET  /reset-password', 'main->resetPassword');      //Vista - Definir nueva contraseña
$f3->route('POST /reset-password', 'miUsuario->resetPassword'); //POST - Definir nueva contraseña



