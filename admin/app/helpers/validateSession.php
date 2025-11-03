<?php
/*******************************************************************************************************************/
/*                                            Se llaman otras clases                                               */
/*******************************************************************************************************************/
require_once __DIR__ . "/../../../vendors/libs/php-jwt/src/JWT.php";  //Se llama a la libreria JWT
require_once __DIR__ . "/../../../vendors/libs/php-jwt/src/Key.php";  //Se llama a la libreria Key

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class validateSession {

    /******************************************************************************/
    //Checkeo Login
    public static function checkLogin($internalToken, $f3, $Headers){

        /******************************************/
        //Llamo a las otras clases
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
        $queryBuilder  = new QueryBuilder();
		$ServerClient  = new FunctionsServerClient();

        /***************************************************/
        //Verifico si utiliza el protocolo JWT
        if (isset($Headers['Authorization'])&&strpos($Headers['Authorization'], 'Bearer ') === 0) {
            //Decodifico el token
            try {
                //Decodifico el token enviado por el usuario
                $decoded = JWT::decode(substr($Headers['Authorization'], 7), new Key(ConfigToken::JWT["SECRET_KEY"], 'HS256'));
                //Devuelvo el valor contenido en el token
                $Token = $decoded->TokenUser;
                //tipo de sesion
                $TypeSession = 2;
            //En el caso de que no se pueda
            } catch (\Throwable $th) {
                return false;
            }
        /***************************************************/
        //Protocolo web, acceso normal
        }else{
            //Devuelvo el valor contenido en el token
           $Token = $internalToken;
           //tipo de sesion
           $TypeSession = 1;
        }

        /******************************/
        //Se genera la query
        $query = [
            'data'   => 'idUsuario, IP_Client, token, expiration_date',
            'table'  => 'usuarios_accesos',
            'join'   => '',
            'where'  => 'token = "'.$Token.'" AND expiration_date < "'.date('Y-m-d H:i:s').'" AND idEstado=1',
            'group'  => '',
            'having' => '',
            'order'  => 'token DESC'
        ];
        //Verifico si hay un dato
        $result = $queryBuilder->queryRow($query, $DB_conn_1);

        /******************************/
        //Si no hay resultados
        if ($result===false) {return $result;}

        /******************************/
		//Se compara la IP para evitar accesos no autorizados
        if ($result['IP_Client'] != $ServerClient->getClientIp()) { return false;}

        /***************************************************/
        /*          Consulta del usuario logueado          */
        /***************************************************/
        $query = [
            'data'   => '
                usuarios_listado.idUsuario,
                usuarios_listado.idTipoUsuario,
                usuarios_listado.idEstado,
                usuarios_listado.Nombre,
                usuarios_listado.Direccion_img,
                usuarios_listado.idMenuPosicion,
                usuarios_listado.password,
                core_tipos_usuario.Nombre AS Posicion,
                core_ubicacion_ciudad.Nombre AS UbicacionNombre,
                core_ubicacion_ciudad.Wheater AS UbicacionWheater',
            'table'  => 'usuarios_listado',
            'join'   => '
                LEFT JOIN core_tipos_usuario     ON core_tipos_usuario.idTipoUsuario   = usuarios_listado.idTipoUsuario
                LEFT JOIN core_ubicacion_ciudad  ON core_ubicacion_ciudad.idCiudad     = usuarios_listado.idCiudad',
            'where'  => 'usuarios_listado.idUsuario = "'.$result['idUsuario'].'"',
            'group'  => '',
            'having' => '',
            'order'  => 'usuarios_listado.Nombre DESC'
        ];
        //Verifico si hay un dato
        $rowData = $queryBuilder->queryRow($query, $DB_conn_1);

        /******************************/
        //Si hay resultados y el estado es correcto, se recrea la sesion en el servidor
        if($rowData!==false&&isset($rowData['idEstado'])&&$rowData['idEstado']==1){
            //Se carga la clase del usuario
            $userSession = new userSession();
            //Se cargan los datos de la sesion
            $userSession->createSession($f3, $rowData, $result, $TypeSession);
            //si no hay problemas se da como valido
            return true;
        }else{
            return false;
        }
    }

    /******************************************************************************/
    //Validacion de sesion del usuario
    public static function validateSession($Token, $f3, $Headers){
        //Instancias
		$ServerClient = new FunctionsServerClient();
        //Se verifica
        try {
            /***************************************************/
            //Verifico si utiliza el protocolo JWT
            if (isset($Headers['Authorization'])&&strpos($Headers['Authorization'], 'Bearer ') === 0) {
                //Verifico la expiracion
                if ($f3->get('SESSION.TokenExpires')!='' && date('Y-m-d H:i:s') > $f3->get('SESSION.TokenExpires')) {  return false;}
                //Decodifico el token
                try {
                    //Decodifico el token enviado por el usuario
                    $decoded = JWT::decode(substr($Headers['Authorization'], 7), new Key(ConfigToken::JWT["SECRET_KEY"], 'HS256'));
                    //se compara si el valor contenido en el token es distinto al de la sesion en el servidor
                    if ($decoded->TokenUser != $f3->get('SESSION.TokenUser')) { return false;}
                    //Se compara la IP para evitar accesos no autorizados
                    $UserData = $f3->get('SESSION.DataInfo');
                    if (isset($UserData['UserIP'])&&$UserData['UserIP'] != $ServerClient->getClientIp()) { return false;}
                    //si no hay problemas se da como valido
                    return true;
                //En el caso de que no se pueda
                } catch (\Throwable $th) {
                    return false;
                }
            /***************************************************/
            //Protocolo web, acceso normal
            }else{
                //se compara si el valor de la cockie es distinto al de la sesion en el servidor
                if ($Token != $f3->get('SESSION.TokenUser')) { return false;}
                //Se compara la IP para evitar accesos no autorizados
                $UserData = $f3->get('SESSION.DataInfo');
                if (isset($UserData['UserIP'])&&$UserData['UserIP'] != $ServerClient->getClientIp()) { return false;}
                //si no hay problemas se da como valido
                return true;
            }
        } catch (PDOException $e) {
            return false;
        }
    }

}
