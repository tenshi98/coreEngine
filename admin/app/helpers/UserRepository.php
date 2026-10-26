<?php
/**
 * Acceso a datos del usuario (tabla usuarios_listado), de los tokens de
 * recuperación de contraseña (usuarios_password_reset) y de la configuración
 * general del sistema (core_sistemas): búsqueda por id/email, actualización
 * de último acceso y contraseña, ciclo de vida de tokens de reset (crear,
 * validar, invalidar) y armado de los datos de sesión (DataInfo).
 */
class UserRepository {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
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
        /************************************/
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
            'PasswordForzarCambio' => 0,
            'PasswordUltimoCambio' => date('Y-m-d H:i:s'),
        ];
        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'password,PasswordForzarCambio,PasswordUltimoCambio',
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
     * Crea un token de recuperación de contraseña en usuarios_password_reset.
     *
     * Solo se persiste el hash sha256 del token (nunca el token en claro).
     * FechaUso no se envía: queda NULL por defecto de columna (token activo).
     *
     * @param int    $UsuarioID      Id del usuario destinatario.
     * @param string $TokenHash      Hash sha256 hex (64 chars) del token.
     * @param string $FechaCreacion  Fecha/hora de creación ('Y-m-d H:i:s').
     * @param string $FechaExpiracion Fecha/hora de expiración ('Y-m-d H:i:s').
     * @param string $IP_Client      IP de origen de la solicitud.
     * @param string $Agent_Transp   User agent de la solicitud.
     *
     * @return int|false id del token insertado (idPasswordReset), o false si el insert falló.
     */
    public function createResetToken($UsuarioID, $TokenHash, $FechaCreacion, $FechaExpiracion, $IP_Client, $Agent_Transp) {

        // Se agrega respuesta
        $arrData = [
            'idUsuario'       => $UsuarioID,
            'token_hash'      => $TokenHash,
            'FechaCreacion'   => $FechaCreacion,
            'FechaExpiracion' => $FechaExpiracion,
            'IP_Client'       => $IP_Client,
            'Agent_Transp'    => $Agent_Transp,
        ];
        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idUsuario,token_hash,FechaCreacion,FechaExpiracion,IP_Client,Agent_Transp',
            'required'  => 'idUsuario,token_hash,FechaCreacion,FechaExpiracion',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'usuarios_password_reset',
            'Post'      => $arrData,
        ];
        // Ejecuto la query
        $result = $this->queryBuilder->queryInsert($query, $this->DB_conn);
        // Retorno de datos (id insertado para poder invalidarlo si el correo falla)
        return (($result['status'] ?? false) === true) ? (int) $result['data'] : false;

    }

    /******************************************************************************/
    /**
     * Indica si el usuario tiene un token activo creado hace menos de
     * $minutos minutos (throttle anti-bombardeo de correos por destinatario).
     *
     * @param int $UsuarioID Id del usuario a consultar.
     * @param int $minutos   Ventana del throttle en minutos (por defecto 5).
     *
     * @return bool true si existe un token activo dentro de la ventana.
     */
    public function hasRecentResetToken($UsuarioID, $minutos = 5) {

        // Fecha límite calculada en PHP (portable, sin placeholder dentro de INTERVAL)
        $Limite = date('Y-m-d H:i:s', time() - ((int) $minutos * 60));

        /************************************/
        // Se genera la query
        $query = [
            'data'   => 'idPasswordReset',
            'table'  => 'usuarios_password_reset',
            'join'   => '',
            'where'  => 'idUsuario = ? AND FechaUso IS NULL AND FechaCreacion > ?',
            'params' => [$UsuarioID, $Limite],
            'group'  => '',
            'having' => '',
            'order'  => ''
        ];
        // Verifico si hay un dato
        $result = $this->queryBuilder->queryRow($query, $this->DB_conn);

        /************************************/
        // Retorno de datos
        return ($result !== false && ($result['status'] ?? false) === true && !empty($result['data']));

    }

    /******************************************************************************/
    /**
     * Invalida todos los tokens de recuperación del usuario (setea FechaUso).
     *
     * Se ejecuta antes de crear un token nuevo: garantiza como máximo un
     * token activo por usuario (uso único de los enlaces anteriores).
     *
     * @param int $UsuarioID Id del usuario cuyos tokens se invalidan.
     *
     * @return bool true si la actualización fue exitosa.
     */
    public function invalidateResetTokens($UsuarioID) {

        // Se agrega respuesta
        $arrData = [
            'idUsuario' => $UsuarioID,
            'FechaUso'  => date('Y-m-d H:i:s'),
        ];
        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'FechaUso',
            'required'  => '',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'usuarios_password_reset',
            'where'     => 'idUsuario',
            'Post'      => $arrData,
        ];
        // Ejecuto la query
        $result = $this->queryBuilder->queryUpdate($query, $this->DB_conn);
        // Retorno de datos
        return ($result['status'] ?? false) === true;

    }

    /******************************************************************************/
    /**
     * Busca un token de reset activo (sin usar y no expirado) por su hash.
     *
     * @param string $TokenHash Hash sha256 hex (64 chars) calculado sobre el token recibido.
     *
     * @return array|null Fila del token ('idPasswordReset','idUsuario','token_hash',
     *                    'FechaCreacion','FechaExpiracion') o null si no hay coincidencia activa.
     */
    public function findActiveResetToken($TokenHash) {

        /************************************/
        // Se genera la query
        $query = [
            'data'   => 'idPasswordReset,idUsuario,token_hash,FechaCreacion,FechaExpiracion',
            'table'  => 'usuarios_password_reset',
            'join'   => '',
            'where'  => 'token_hash = ? AND FechaUso IS NULL AND FechaExpiracion > NOW()',
            'params' => [$TokenHash],
            'group'  => '',
            'having' => '',
            'order'  => ''
        ];
        // Verifico si hay un dato
        $result = $this->queryBuilder->queryRow($query, $this->DB_conn);

        /************************************/
        // Si no hay resultados
        if ($result === false || ($result['status'] ?? false) !== true || empty($result['data'])) {
            // Retorno de datos
            return null;
        }

        /************************************/
        // Retorno de datos
        return $result['data'];
    }

    /******************************************************************************/
    /**
     * Marca un token de reset como usado (setea FechaUso), dejándolo
     * invalidado de forma permanente (uso único).
     *
     * @param int $idPasswordReset Id del token a marcar.
     *
     * @return bool true si la actualización fue exitosa.
     */
    public function markResetTokenUsed($idPasswordReset) {

        // Se agrega respuesta
        $arrData = [
            'idPasswordReset' => $idPasswordReset,
            'FechaUso'        => date('Y-m-d H:i:s'),
        ];
        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'FechaUso',
            'required'  => '',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'usuarios_password_reset',
            'where'     => 'idPasswordReset',
            'Post'      => $arrData,
        ];
        // Ejecuto la query
        $result = $this->queryBuilder->queryUpdate($query, $this->DB_conn);
        // Retorno de datos
        return ($result['status'] ?? false) === true;

    }

    /******************************************************************************/
    /**
     * Arma el arreglo de datos de usuario ("DataInfo") que se guarda en
     * SESSION, combinando los datos de la fila del usuario con la
     * configuración general de la plataforma (core_sistemas).
     *
     * @param array  $rowData Fila del usuario (usuarios_listado + joins).
     * @param object $Client  Helper de datos de cliente (IP, dispositivo móvil).
     *
     * @return array Datos de usuario fusionados con la configuración del sistema.
     */
    public function getDataInfo($rowData, $Client) {
        // Se instancia la libreria
        $FileManager  = new FileManager();

        // Armo los datos del usuario
        $rowUsuario = [
            'UserID'                => $rowData['idUsuario'],
            'UserType'              => $rowData['idTipoUsuario'],
            'UserIMG'               => $rowData['Direccion_img'],
            'UserName'              => $rowData['Nombre'],
            'UserPosition'          => $rowData['Posicion'],
            'idMenuPosicion'        => $rowData['idMenuPosicion'],
            'UbicacionNombre'       => $rowData['UbicacionNombre'],
            'UbicacionWheater'      => $rowData['UbicacionWheater'],
            'PasswordForzarCambio'  => (int) ($rowData['PasswordForzarCambio'] ?? 0),
            'PasswordUltimoCambio'  => $rowData['PasswordUltimoCambio'] ?? null,
            'UserIP'                => $Client->getClientIp(),
            'DeviceMobile'          => $Client->validarDispositivoMovil(),
            'MainPathUrl'           => $FileManager->getMainPathUrl(),
        ];
        /************************************/
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

        /************************************/
        // Si no hay resultados, se evita fusionar con un valor que no sea array
        $datosSistema = (isset($rowOpciones['status']) && $rowOpciones['status'] === true && is_array($rowOpciones['data']))
            ? $rowOpciones['data']
            : [];

        /************************************/
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

        /************************************/
        // Llamo a las otras clases
		$response['status'] = false;
        $response['data']   = '';

        /************************************/
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
                usuarios_listado.PasswordForzarCambio,
                usuarios_listado.PasswordUltimoCambio,
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

        /************************************/
        // Si no hay resultados
        if ($result === false || !isset($result['status']) || $result['status'] === false) {
            // Retorno de datos
            return $response;
        }

        /************************************/
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

        /************************************/
        // Llamo a las otras clases
		$response['status'] = false;
        $response['data']   = null;

        /************************************/
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

        /************************************/
        // Si no hay resultados
        if ($result === false || !isset($result['status']) || $result['status'] === false) {
            // Retorno de datos
            return $response;
        }

        /************************************/
		// Retorno de datos
        $response['status'] = true;
        $response['data']   = $result['data'];
        return $response;
    }
}
