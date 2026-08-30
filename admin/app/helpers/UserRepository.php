<?php
/**
 * Acceso a datos del usuario (tabla usuarios_listado) y a la configuración
 * general del sistema (core_sistemas): búsqueda por id/email, actualización
 * de último acceso y contraseña, y armado de los datos de sesión (DataInfo).
 */
class UserRepository {

    /******************************************************************************/
    // Variables
    private $DB_conn;
    private $queryBuilder;

    /******************************************************************************/
    /**
     * Inicializa la conexión a base de datos y el constructor de queries.
     *
     * @return void
     */
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $this->DB_conn       = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $this->queryBuilder  = new QueryBuilder();
    }

    /******************************************************************************/
    /**
     * Busca un usuario por su id, junto con su tipo, ubicación y estado.
     *
     * @param int $UsuarioID Id del usuario a buscar.
     *
     * @return array{status:bool, data:array|string}|null status=true y datos del usuario si existe.
     */
    public function findById(int $UsuarioID): ?array {
        // Retorno de datos
		return $this->searchData('usuarios_listado.idUsuario = ?', $UsuarioID);
    }

    /******************************************************************************/
    /**
     * Busca un usuario por su email, junto con su tipo, ubicación y estado.
     *
     * @param string $email Email del usuario a buscar.
     *
     * @return array{status:bool, data:array|string}|null status=true y datos del usuario si existe.
     */
    public function findByEmail(string $email): ?array {
        // Retorno de datos
        return $this->searchData('usuarios_listado.email = ?', $email);
    }

    /******************************************************************************/
    /**
     * Actualiza la fecha de último acceso, IP y user agent del usuario en
     * usuarios_listado.
     *
     * @param int    $UsuarioID Id del usuario a actualizar.
     * @param object $Server    Helper de datos de servidor (fecha actual).
     * @param object $Client    Helper de datos de cliente (IP, user agent).
     *
     * @return void
     */
    public function updateUserAccess($UsuarioID, $Server, $Client) {

        // Se agrega respuesta
        $arrData = [
            'idUsuario'       => $UsuarioID,
            'Ultimo_acceso'   => $Server->fechaActual(),
            'IP_Client'       => $Client->getClientIp(),
            'Agent_Transp'    => $Client->getBrowser(),
        ];
        // Se genera la query
        $query = [
            'data'      => 'idUsuario,Ultimo_acceso,IP_Client,Agent_Transp',
            'required'  => 'idUsuario,Ultimo_acceso,IP_Client,Agent_Transp',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'usuarios_listado',
            'where'     => 'idUsuario',
            'Post'      => $arrData,
        ];
        // Ejecuto la query
        $this->queryBuilder->queryUpdate($query, $this->DB_conn);

    }

    /******************************************************************************/
    /**
     * Actualiza (y encripta, según columna 'encode') la contraseña
     * almacenada del usuario en usuarios_listado.
     *
     * @param int    $UsuarioID    Id del usuario a actualizar.
     * @param string $NewPasswords Nueva contraseña en texto plano a persistir.
     *
     * @return bool true una vez ejecutada la actualización.
     */
    public function updateUserPassword($UsuarioID, $NewPasswords) {

        // Se agrega respuesta
        $arrData = [
            'idUsuario' => $UsuarioID,
            'password'  => $NewPasswords,
        ];
        // Se genera la query
        $query = [
            'data'      => 'password',
            'required'  => 'password',
            'unique'    => '',
            'encode'    => 'password',
            'table'     => 'usuarios_listado',
            'where'     => 'idUsuario',
            'Post'      => $arrData,
        ];
        // Ejecuto la query
        $this->queryBuilder->queryUpdate($query, $this->DB_conn);
        // Retorno de datos
        return true;

    }

    /******************************************************************************/
    /**
     * Arma el arreglo de datos de usuario ("DataInfo") que se guarda en
     * SESSION, combinando los datos de la fila del usuario con la
     * configuración general de la plataforma (core_sistemas).
     *
     * @param array  $rowData Fila del usuario (usuarios_listado + joins).
     * @param object $Client  Helper de datos de cliente (IP).
     *
     * @return array Datos de usuario fusionados con la configuración del sistema.
     */
    public function getDataInfo($rowData, $Client) {
        // Se instancia la libreria
        $FileManager  = new FileManager();

        // Armo los datos del usuario
        $rowUsuario = [
            'UserID'             => $rowData['idUsuario'],
            'UserType'           => $rowData['idTipoUsuario'],
            'UserIMG'            => $rowData['Direccion_img'],
            'UserName'           => $rowData['Nombre'],
            'UserPosition'       => $rowData['Posicion'],
            'idMenuPosicion'     => $rowData['idMenuPosicion'],
            'UbicacionNombre'    => $rowData['UbicacionNombre'],
            'UbicacionWheater'   => $rowData['UbicacionWheater'],
            'UserIP'             => $Client->getClientIp(),
            'MainPathUrl'        => $FileManager->getMainPathUrl(),
        ];
        /******************************/
        // Se cargan los datos de la plataforma
        $query = [
            'data'   => '*',
            'table'  => 'core_sistemas',
            'join'   => '',
            'where'  => 'idSistema = ?',
            'params'  => [1],
            'group'  => '',
            'having' => '',
            'order'  => ''
        ];
        // Verifico si hay un dato
        $rowOpciones = $this->queryBuilder->queryRow($query, $this->DB_conn);

        /******************************/
        // Si no hay resultados, se evita fusionar con un valor que no sea array
        $datosSistema = (isset($rowOpciones['status']) && $rowOpciones['status'] === true && is_array($rowOpciones['data']))
            ? $rowOpciones['data']
            : [];

        /******************************/
        // Retorno de datos
        return array_merge($rowUsuario, $datosSistema);
    }

    /******************************************************************************/
    /**
     * Ejecuta la búsqueda de un usuario en usuarios_listado con sus joins
     * de tipo de usuario y ubicación, según la condición WHERE recibida.
     *
     * @param string $Data Fragmento SQL para la cláusula WHERE de la consulta.
     *
     * @return array{status:bool, data:array|string} status=true y datos del usuario si existe;
     *                                                status=false y data='' en caso contrario.
     */
    private function searchData(String $Data, String $Param): ?array {

        /******************************************/
        // Llamo a las otras clases
		$response['status'] = false;
        $response['data']   = '';

        /******************************/
        // Se genera la query
        $query = [
            'data'   => '
                usuarios_listado.idUsuario,
                usuarios_listado.idTipoUsuario,
                usuarios_listado.idEstado,
                usuarios_listado.Nombre,
                usuarios_listado.email,
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
            'where'  => $Data,
            'params'  => [$Param],
            'group'  => '',
            'having' => '',
            'order'  => 'usuarios_listado.Nombre DESC'
        ];
        // Verifico si hay un dato
        $result = $this->queryBuilder->queryRow($query, $this->DB_conn);

        /******************************/
        // Si no hay resultados
        if ($result === false || !isset($result['status']) || $result['status'] === false) {
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
     * Obtiene la configuración general del sistema (core_sistemas):
     * motor de correo, logo, direcciones de contacto y redes sociales.
     *
     * @return array{status:bool, data:array|null} status=true y los datos de configuración si existen.
     */
    public function getSystemData(): ?array {

        /******************************************/
        // Llamo a las otras clases
		$response['status'] = false;
        $response['data']   = null;

        /******************************/
        // Se genera la query
        $query = [
            'data'   => 'Config_motorEmail,Sistema_IMGLogo, Sistema_Direccion, Sistema_Email, Social_X, Social_Facebook, Social_Instagram, Social_Linkedin',
            'table'  => 'core_sistemas',
            'join'   => '',
            'where'  => 'idSistema = ?',
            'params'  => [1],
            'group'  => '',
            'having' => '',
            'order'  => ''
        ];
        // Verifico si hay un dato
        $result = $this->queryBuilder->queryRow($query, $this->DB_conn);

        /******************************/
        // Si no hay resultados
        if ($result === false || !isset($result['status']) || $result['status'] === false) {
            // Retorno de datos
            return $response;
        }

        /******************************/
		// Retorno de datos
        $response['status'] = true;
        $response['data']   = $result['data'];
        return $response;
    }
}
