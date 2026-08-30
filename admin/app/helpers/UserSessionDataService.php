<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Persiste y consulta los registros de acceso web de los usuarios
 * (tabla usuarios_accesos): validación de token de cookie, alta de un nuevo
 * acceso y baja de accesos anteriores.
 */
class UserSessionDataService {

    /******************************************************************************/
    // Variables
    private $DBConn;
    private $QBuilder;

    /******************************************************************************/
    /**
     * Inicializa la conexión a base de datos y el constructor de queries.
     *
     * @return void
     */
    public function __construct(){
        /*================== Instancias =================*/
        $this->DBConn    = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $this->QBuilder  = new QueryBuilder();
    }


    /******************************************************************************/
    /**
     * Verifica en base de datos si el token de cookie recibido corresponde a
     * un acceso web activo y no expirado, y que la IP de origen coincida con
     * la registrada al momento del login.
     *
     * @param string $cookieToken Token recibido en la cookie "Sesion_tk".
     *
     * @return array{status:bool, data:array|null} status=true y los datos del acceso
     *                                              (idUsuario, IP_Client, token, expiration_date)
     *                                              si es válido; status=false y data=null en caso contrario.
     */
    public function checkAccess($cookieToken){

        /******************************************/
        // Se crea el arreglo
		$response['status'] = false;
        $response['data']   = null;

        /******************************************/
        // Se cargan las clases
		$ServerClient  = new FunctionsServerClient();

        /******************************/
        // Se genera la query
        $query = [
            'data'   => 'idUsuario, IP_Client, token, expiration_date',
            'table'  => 'usuarios_accesos',
            'join'   => '',
            'where'  => 'token = ? AND expiration_date > NOW() AND idEstado = ? AND idTipoAcceso = ?',
            'params'  => [$cookieToken, 1, 1],
            'group'  => '',
            'having' => '',
            'order'  => 'token DESC'
        ];
        // Verifico si hay un dato
        $result = $this->QBuilder->queryRow($query, $this->DBConn);

        /******************************/
        // Si no hay resultados
        if ($result === false || !isset($result['status']) || $result['status'] === false) {
            // Retorno de datos
            return $response;
        }

        /******************************/
		// Se compara la IP para evitar accesos no autorizados
        if (!isset($result['data']['IP_Client']) || $result['data']['IP_Client'] != $ServerClient->getClientIp()) {
            // Retorno de datos
            return $response;
        }

        /******************************/
		// Retorno de datos
        $response['status'] = true;
        $response['data']   = $result['data'];
        return $response;

    }

    /******************************************************************************/
    /**
     * Inserta un nuevo registro de acceso web para el usuario, asociando el
     * token de sesión y su fecha de expiración.
     *
     * @param int    $UsuarioID    Id del usuario que inicia sesión.
     * @param string $TokenUser    Token de sesión generado.
     * @param string $TokenExpires Fecha/hora de expiración del token.
     * @param object $Server       Helper de datos de servidor (fecha/hora actual).
     * @param object $Client       Helper de datos de cliente (IP, user agent).
     *
     * @return void
     */
    public function registerAccess($UsuarioID, $TokenUser, $TokenExpires, $Server, $Client){

        // Se agrega respuesta
        $arrData = [
            'idUsuario'       => $UsuarioID,
            'Fecha'           => $Server->fechaActual(),
            'Hora'            => $Server->horaActual(),
            'DateTime'        => $Server->fechaActual().' '.$Server->horaActual(),
            'IP_Client'       => $Client->getClientIp(),
            'Agent_Transp'    => $Client->getBrowser(),
            'idSistema'       => 1,
            'token'           => $TokenUser,
            'expiration_date' => $TokenExpires,
            'idEstado'        => 1,
            'idTipoAcceso'    => 1, // Acceso Web
        ];
        // Se genera la query
        $query = [
            'data'      => 'idUsuario, Fecha, Hora, DateTime, IP_Client, Agent_Transp, idSistema, token, expiration_date, idEstado, idTipoAcceso',
            'required'  => '',
            'unique'    => '',
            'table'     => 'usuarios_accesos',
            'Post'      => $arrData,
        ];
        // Ejecuto la query
        $this->QBuilder->queryInsert($query, $this->DBConn);

    }

    /******************************************************************************/
    /**
     * Marca como inactivos (idEstado=2) todos los accesos web previos del
     * usuario, invalidando cualquier token de sesión anterior.
     *
     * @param int $UsuarioID Id del usuario cuyos accesos se desactivan.
     *
     * @return void
     */
    public function disabledAllAccess($UsuarioID){

        // Se agrega respuesta
        $Post                 = [];
        $Post['idUsuario']    = $UsuarioID;
        $Post['idEstado']     = 2; //inactivo
        $Post['idTipoAcceso'] = 1; // Acceso Web
        // Se genera la query
        $query = [
            'data'      => 'idEstado',
            'required'  => 'token',
            'unique'    => '',
            'table'     => 'usuarios_accesos',
            'where'     => 'idUsuario,idTipoAcceso',
            'Post'      => $Post,
        ];
        // Ejecuto la query
        $this->QBuilder->queryUpdate($query, $this->DBConn);

    }

}
