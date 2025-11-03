<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class ConfigAPP{

    //Datos del Software
    const SOFTWARE = [
        'SoftwareName'    => 'coreEngine',               //Nombre del software
        'SoftwareSlogan'  => 'Software Modular',         //Slogan del software
        'CompanyName'     => 'coreEngine',               //Nombre de la compañia
        'CompanyEmail'    => 'coreEngine@coreEngine.cl', //Email de la compañia
        'CompanyCredits'  => '',                         //Creditos
        'URL'             => 'https://frucomex.cl',      //URL
    ];

    //Configuracion de la aplicacion
    const APP = [
        'N_MaxItems'              => 1000,                                      //Numero maximo de registros sin paginar
        'uploadFolder'            => __DIR__ .'/../../../admin/public/upload/', //Carpeta de subida de archivos
        'checkBruteConections'    => 5,                                         //Numero maximo de intentos de login fallidos antes de banear
        'checkBruteMaxConections' => 20,                                        //Numero maximo de intentos de login fallidos antes de enviar a la lista negra
    ];

}
