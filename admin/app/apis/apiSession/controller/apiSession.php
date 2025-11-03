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
class apiSession extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $userSession;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
		$this->userSession    = new userSession();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                 SESIONES                                   */
    /******************************************************************************/
    /******************************************************************************/
    //Login
    public function ApiLogin($f3){

        /******************************/
        //Se cargan los datos de la sesion
        $Response = $this->userSession->login($f3, $_POST, 2);

        /******************************************/
        //Si el acceso es correcto
        if($Response['code']==200){
            //Armo
            $payload = [
                'iss'          => ConfigAPP::SOFTWARE["URL"],
                'aud'          => ConfigAPP::SOFTWARE["URL"],
                'TokenUser'    => $f3->get('SESSION.TokenUser'),
                'TokenExpires' => $f3->get('SESSION.TokenExpires'),
            ];
            //OAuth JWT
            $Resultado = JWT::encode($payload, ConfigToken::JWT["SECRET_KEY"], 'HS256');
            //imprimo resultados
            echo Response::sendData($Response['code'], $Resultado);
        //Si da otro error
        }else{
            //imprimo resultados
            echo Response::sendData($Response['code'], $Response['message']);
        }

    }

    /******************************************************************************/
    //cierra sesion
    public function Apilogout($f3){

        /******************************/
        //Se cargan los datos de la sesion
        $Response = $this->userSession->forgot($f3, $_POST);

        /******************************************/
        //imprimo resultados
        echo Response::sendData($Response['code'], $Response['message']);

    }

}
