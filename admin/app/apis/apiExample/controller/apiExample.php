<?php
/*******************************************************************************************************************/
/*                                            Se llaman otras clases                                               */
/*******************************************************************************************************************/
require_once __DIR__ . "/../../../../vendors/libs/php-jwt/src/JWT.php";  //Se llama a la libreria JWT
require_once __DIR__ . "/../../../../vendors/libs/php-jwt/src/Key.php";  //Se llama a la libreria Key

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class apiExample extends ControllerBase {

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    //Vista - Login
    public function listData($f3){

        /******************************************/
        $payload = [
            'iss' => 'http://example2.org',
            'aud' => 'http://example.com',
            'iat' => 1356999524,
            'nbf' => 1357000000
        ];

        $token   = JWT::encode($payload, ConfigToken::JWT["SECRET_KEY"], 'HS256');
        $decoded = JWT::decode($token, new Key(ConfigToken::JWT["SECRET_KEY"], 'HS256'));

        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos Consultados ===========*/
            'token'   => $token,
            'decoded' => $decoded,
        ];

        //Se instancia la vista
        $view = new View;
        echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/apiExample.php'); // Vista
    }

    /******************************************************************************/
    //Vista - Login
    public function listDataV2($f3){

        /******************************************/
        $payload = [
            'iss' => 'http://example2.org',
            'aud' => 'http://example.com',
            'iat' => 1356999524,
            'nbf' => 1357000000
        ];

        $token   = JWT::encode($payload, ConfigToken::JWT["SECRET_KEY"], 'HS256');
        $decoded = JWT::decode($token, new Key(ConfigToken::JWT["SECRET_KEY"], 'HS256'));

        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos Consultados ===========*/
            'token'   => $token,
            'decoded' => $decoded,
        ];

        //Se instancia la vista
        $view = new View;
        echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/apiExample.php'); // Vista
    }


}