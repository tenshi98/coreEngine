<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class userSession extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $DBConn;
    private $QBuilder;
    private $Codification;
    private $Server;
    private $Client;
    private $Passwords;
    private $Operations;
    private $CommonData;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->DBConn         = $DB_conn_1;
        $this->QBuilder       = $queryBuilder;
		$this->Codification   = new FunctionsSecurityCodification();
		$this->Server         = new FunctionsServerServer();
		$this->Client         = new FunctionsServerClient();
		$this->Passwords      = new FunctionsSecurityPasswords();
		$this->Operations     = new FunctionsDataOperations();
		$this->CommonData     = new FunctionsCommonData();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                 SESIONES                                   */
    /******************************************************************************/
    /******************************************************************************/
    //Login
    public function login($f3, $POST, $TypeSession){

        /******************************************/
        //Variables
        $errors        = [];
        $Fecha         = $this->Server->fechaActual();
        $Hora          = $this->Server->horaActual();
        $DateTime      = time();
        $Email         = '';
        $Password      = '';
        $IP_Client     = $this->Client->getClientIp();
        $Agent_Transp  = $this->Client->getBrowser();

        //Valido datos ingresados por el usuario
        if (empty($POST['email'])){    $errors[] = ["message" => "Email es obligatorio"];    }else{$Email     = $POST['email'];}
        if (empty($POST['password'])){ $errors[] = ["message" => "Password es obligatorio"]; }else{$Password  = $POST['password'];}
        //Valido si dato es ingresado por una maquina
        if (!empty($POST['nombre'])){
            //Mensaje
            $errors[] = ["message" => "Nombre es obligatorio"];
            //Se guarda registro
            $this->insertBrute($Fecha, $Hora, $DateTime, $Email, $Password, $IP_Client, $Agent_Transp);
        }
        //Se verifica si se trata de hacer fuerza bruta en el ingreso
        if ($this->checkBrute($Email, $IP_Client) === true) {
            $errors[] = ["message" => "Demasiados accesos fallidos, usuario bloqueado por 2 horas"];
        }
        //Se obtiene la uicacion
        $loc = Web\Geo::instance()->location();
        //Si no esta en chile
        if (isset($loc['country_code']) && $loc['country_code']!='CL') {
            $errors[] = ["message" => "No se puede acceder desde su ubicación"];
        }

        /******************************/
        if(!empty($errors)){ return ['code' => 400, 'message' => $errors];}

        /******************************/
        //Se obtienen los datos
        $email    = $POST['email'];
        $password = $this->Codification->encryptDecrypt('encrypt',$POST['password'],ConfigToken::ENCODE_KEYS["KEY_1"]);

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
                core_tipos_usuario.Nombre AS Posicion,
                core_ubicacion_ciudad.Nombre AS UbicacionNombre,
                core_ubicacion_ciudad.Wheater AS UbicacionWheater',
            'table'  => 'usuarios_listado',
            'join'   => '
                LEFT JOIN core_tipos_usuario     ON core_tipos_usuario.idTipoUsuario   = usuarios_listado.idTipoUsuario
                LEFT JOIN core_ubicacion_ciudad  ON core_ubicacion_ciudad.idCiudad     = usuarios_listado.idCiudad',
            'where'  => 'usuarios_listado.email = "'.$email.'" AND usuarios_listado.password = "'.$password.'"',
            'group'  => '',
            'having' => '',
            'order'  => 'usuarios_listado.Nombre DESC'
        ];
        //Verifico si hay un dato
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /******************************/
        //Si no hay resultados
        if($rowData===false){
            //Se guarda registro
            $this->insertBrute($Fecha, $Hora, $DateTime, $Email, $Password, $IP_Client, $Agent_Transp);
            //Mensaje
            return ['code' => 400, 'message' => 'El email o contraseña no coinciden'];
        }

        /******************************/
        //Verifico el estado
        if(isset($rowData['idEstado'])&&$rowData['idEstado']!=1){ return ['code' => 400, 'message' => 'Usuario Inactivo'];}

        /******************************/
        //Se cargan los datos de la sesion
        $this->createSession($f3, $rowData, '', $TypeSession);
        //imprimo resultados
        return ['code' => 200, 'message' => 'Acceso Correcto'];

    }

    /******************************************************************************/
    //Recuperar contraseña
    public function forgot($f3, $POST){

        /******************************************/
        //Variables
        $errors        = [];
        $Fecha         = $this->Server->fechaActual();
        $Hora          = $this->Server->horaActual();
        $DateTime      = time();
        $Email         = '';
        $Password      = '';
        $IP_Client     = $this->Client->getClientIp();
        $Agent_Transp  = $this->Client->getBrowser();

        //Valido datos ingresados por el usuario
        if (empty($POST['email'])){    $errors[] = ["message" => "Email es obligatorio"];     }else{$Email     = $POST['email'];}
        //Valido si dato es ingresado por una maquina
        if (!empty($POST['nombre'])){
            //Mensaje
            $errors[] = ["message" => "Nombre es obligatorio"];
            //Se guarda registro
            $this->insertBrute($Fecha, $Hora, $DateTime, $Email, $Password, $IP_Client, $Agent_Transp);
        }
        //Se verifica si se trata de hacer fuerza bruta en el ingreso
        if ($this->checkBrute($Email, $IP_Client) === true) {
            $errors[] = ["message" => "Demasiados accesos fallidos, usuario bloqueado por 2 horas"];
        }
        //Se obtiene la uicacion
        $loc = Web\Geo::instance()->location();
        //Si no esta en chile
        if (isset($loc['country_code']) && $loc['country_code']!='CL') {
            $errors[] = ["message" => "No se puede acceder desde su ubicación"];
        }

        /******************************/
        if(!empty($errors)){ return ['code' => 400, 'message' => $errors];}

        //Limpio las variables
        $f3->clear('SESSION.TokenUser');    //Token del usuario
        $f3->clear('SESSION.TokenExpires'); //token valido por 1 dia
        $f3->clear('SESSION.DataInfo');     //Datos del usuario
        $f3->clear('SESSION.arrMenu');      //Menu
        $f3->clear('SESSION.arrPermisos');  //Rutas
        $f3->clear('SESSION.arrLevel');     //Niveles de permisos

        /******************************/
        //Se obtienen los datos
        $email    = $POST['email'];

        /******************************/
        //Se genera la query
        $query = [
            'data'   => 'idUsuario, idEstado, Nombre, email',
            'table'  => 'usuarios_listado',
            'join'   => '',
            'where'  => 'email = "'.$email.'"',
            'group'  => '',
            'having' => '',
            'order'  => 'Nombre DESC'
        ];
        //Verifico si hay un dato
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /******************************/
        //Si no hay resultados
        if($rowData===false){
            //Se guarda registro
            $this->insertBrute($Fecha, $Hora, $DateTime, $Email, $Password, $IP_Client, $Agent_Transp);
            //Mensaje
            return ['code' => 400, 'message' => 'El email ingresado no existe'];
        }

        /******************************/
        //Verifico el estado
        if(isset($rowData['idEstado'])&&$rowData['idEstado']!=1){ return ['code' => 400, 'message' => 'Usuario Inactivo'];}

        /******************************/
        //Se generan Variables
        $NewPasswords  = $this->Passwords->generarPassword(10,'alfanumerico');

        /******************************/
        //Se agrega respuesta
        $arrData = [
            'idUsuario' => $rowData['idUsuario'],
            'password'  => $NewPasswords,
        ];

        /******************************/
        //Se genera la query
        $query = [
            'data'      => 'password',
            'required'  => 'password',
            'unique'    => '',
            'encode'    => 'password',
            'table'     => 'usuarios_listado',
            'where'     => 'idUsuario',
            'Post'      => $arrData,
        ];
        //Ejecuto la query
        $xParams  = ['DataCheck' => '', 'query' => $query];
        $Response = $this->Base_update($xParams);

        /******************************/
        // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
        if ($Response===true) {
            /******************************/
            //Se envia el correo
            try {
                /******************************/
                //Se agrega respuesta
                $arrData = [
                    'Asunto'  => 'Cambio de contraseña',
                    'Hacia'   => $rowData['email'],
                    'Mensaje' => 'Se ha generado una nueva contraseña para el email '.$rowData['email'].', su nueva contraseña es: '.$NewPasswords,
                ];
                //Se genera la query
                $query = [
                    'data'      => 'Asunto,Hacia,Mensaje',
                    'template'  => 1,
                    'Post'      => $arrData,
                ];
                /******************************/
                $Respuesta = $this->Base_SMTPMail($f3, $query);
                //si es la respuesta esperada
                if ($Respuesta===true) {
                    //imprimo resultados
                    return ['code' => 200, 'message' => 'La nueva contraseña fue enviada a tu correo'];
                }else{
                    //imprimo resultados
                    return ['code' => 500, 'message' => $Respuesta];
                }
            }catch (Exception $e) {
                //imprimo resultados
                return ['code' => 500, 'message' => 'No se ha podido enviar el correo, contacte con el administrador'];
            }
        } else {
            // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
            // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
            return ['code' => 500, 'message' => 'No se ha podido cambiar la contraseña'];
        }

    }

    /******************************************************************************/
    //cierra sesion
    public function logout($f3){
        //Actualizo el estado del token en la BBDD
        /******************************/
        //Se agrega respuesta
        $Post             = [];
        $Post['idEstado'] = 2; //inactivo
        $Post['token']    = $f3->get('SESSION.TokenUser');

        /******************************/
        //Se genera la query
        $query = [
            'data'      => 'idEstado',
            'required'  => 'token',
            'unique'    => '',
            'table'     => 'usuarios_accesos',
            'where'     => 'token',
            'Post'      => $Post,
        ];
        //Ejecuto la query
        $xParams     = ['DataCheck' => '', 'query' => $query];
        $queryUpdate = $this->Base_update($xParams);

        /******************************/
        //Si hay resultados
        if ($queryUpdate===true) {
            /******************************/
            //Se limpian las variables
            $f3->clear('SESSION.TokenUser');    //Token del usuario
            $f3->clear('SESSION.TokenExpires'); //token valido por 1 dia
            $f3->clear('SESSION.DataInfo');     //Datos del usuario
            $f3->clear('SESSION.arrMenu');      //Menu
            $f3->clear('SESSION.arrPermisos');  //Rutas
            $f3->clear('SESSION.arrLevel');     //Niveles de permisos

            /******************************/
            return ['code' => 200, 'message' => 'Sesion cerrada correctamente'];

        //si no hay resultados
        } else {
            return ['code' => 500, 'message' => 'No se ha podido cerrar la sesion'];
        }
    }


    /******************************************************************************/
    /******************************************************************************/
    //Permite actualizar la sesion
    public function updateSession($UsuarioID, $f3, $TypeSession){

        /******************************************/
        //Limpio las variables
        $f3->clear('SESSION.DataInfo');     //Datos del usuario

        /******************************/
        //Se actualizan los datos de la sesion
        $query = [
            'data'   => '
                usuarios_listado.idUsuario,
                usuarios_listado.idTipoUsuario,
                usuarios_listado.idEstado,
                usuarios_listado.Nombre,
                usuarios_listado.Direccion_img,
                usuarios_listado.idMenuPosicion,
                core_tipos_usuario.Nombre AS Posicion,
                core_ubicacion_ciudad.Nombre AS UbicacionNombre,
                core_ubicacion_ciudad.Wheater AS UbicacionWheater',
            'table'  => 'usuarios_listado',
            'join'   => '
                LEFT JOIN core_tipos_usuario     ON core_tipos_usuario.idTipoUsuario   = usuarios_listado.idTipoUsuario
                LEFT JOIN core_ubicacion_ciudad  ON core_ubicacion_ciudad.idCiudad     = usuarios_listado.idCiudad',
            'where'  => 'usuarios_listado.idUsuario = "'.$UsuarioID.'"',
            'group'  => '',
            'having' => '',
            'order'  => 'usuarios_listado.Nombre DESC'
        ];
        //Verifico si hay un dato
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /***************************************************/
        /*         Se generan los datos del usuario        */
        /***************************************************/
        //Armo
        $rowUsuario = [
            'UserID'             => $rowData['idUsuario'],
            'UserType'           => $rowData['idTipoUsuario'],
            'UserIMG'            => $rowData['Direccion_img'],
            'UserName'           => $rowData['Nombre'],
            'UserPosition'       => $rowData['Posicion'],
            'idMenuPosicion'     => $rowData['idMenuPosicion'],
            'UbicacionNombre'    => $rowData['UbicacionNombre'],
            'UbicacionWheater'   => $rowData['UbicacionWheater'],
            'TypeSession'        => $TypeSession,
            'UserIP'             => $this->Client->getClientIp(),
        ];

        /******************************/
        //Se genera la query
        $query = [
            'data'   => '*',
            'table'  => 'core_sistemas',
            'join'   => '',
            'where'  => 'idSistema = "1"',
            'group'  => '',
            'having' => '',
            'order'  => ''
        ];
        //Verifico si hay un dato
        $xParams     = ['query' => $query];
        $rowOpciones = $this->Base_GetByID($xParams);

        //Se fucionan arreglos
        $Resultado = array_merge($rowUsuario, $rowOpciones);

        /******************************/
        //Se actualizan datos de la sesion
        $f3->set('SESSION.DataInfo', $Resultado); //Datos del usuario
    }
    /******************************************************************************/
    //Permite verificar la sesion
    private function checkBrute($Email, $IP_Client){
        /**********************************************************************/
        //Variable
        $TimeValid = time() - (2 * 60 * 60);  //Tiempo actual menos 2 horas

        /******************************/
        //Se genera la query
        $query = [
            'data'   => 'idAcceso',
            'table'  => 'usuarios_checkbrute',
            'join'   => '',
            'where'  => '(Email = "'.$Email.'" OR IP_Client = "'.$IP_Client.'") AND DateTime > "'.$TimeValid.'"',
            'group'  => '',
            'having' => '',
            'order'  => 'idAcceso DESC',
            'limit'  => 60
        ];

        /******************************/
        //Ejecuto la query
        $num_rows = $this->QBuilder->queryNRows($query, $this->DBConn);

        /**********************************************************************/
        // Si ha habido más de 5 intentos de inicio de sesión fallidos.
        if($num_rows > ConfigAPP::APP["checkBruteConections"]){
            //dar respuesta de bloqueo
            $result = true;
        //Verifico si sobrepasa el maximo de intentos fallidos
        }elseif($num_rows > ConfigAPP::APP["checkBruteMaxConections"]){
            //Le envio al servidor la tarea de enviarlo al black list
            //se envian los datos
            $Server = new FunctionsServerSecurity();
            $Server->sendIPtoBlackList($IP_Client);
            //dar respuesta de bloqueo
            $result = true;
        }else{
            //dar respuesta de bloqueo
            $result = false;
        }

        /******************************/
        //devuelvo la respuesta
        return $result;

    }
    /******************************************************************************/
    //Inserta el registro en caso de un acceso forzoso
    private function insertBrute($Fecha, $Hora, $DateTime, $Email, $Password, $IP_Client, $Agent_Transp){
        /******************************/
        //Se agrega respuesta
        $Post = [
            'Fecha'        => $Fecha,
            'Hora'         => $Hora,
            'DateTime'     => $DateTime,
            'Email'        => $Email,
            'Password'     => $Password,
            'IP_Client'    => $IP_Client,
            'Agent_Transp' => $Agent_Transp,
        ];

        /******************************/
        //Se genera la query
        $query = [
            'data'      => 'Fecha, Hora, DateTime, Email, Password, IP_Client, Agent_Transp',
            'required'  => '',
            'unique'    => '',
            'table'     => 'usuarios_checkbrute',
            'Post'      => $Post,
        ];

        /******************************/
        //Ejecuto la query
        $this->QBuilder->queryInsert($query, $this->DBConn);

    }
    /******************************************************************************/
    //Inserta el registro en caso de un acceso forzoso
    public function createSession($f3, $rowData, $result, $TypeSession){

        /******************************************/
        //Limpio las variables
        $f3->clear('SESSION.TokenUser');    //Token del usuario
        $f3->clear('SESSION.TokenExpires'); //token valido por 1 dia
        $f3->clear('SESSION.DataInfo');     //Datos del usuario
        $f3->clear('SESSION.arrMenu');      //Menu
        $f3->clear('SESSION.arrPermisos');  //Rutas
        $f3->clear('SESSION.arrLevel');     //Niveles de permisos

        /***************************************************/
        /*               Verifico procedencia              */
        /***************************************************/
        /******************************************/
        //Si se envian datos
        if($result!=''){
            /******************************/
            //Se generan Variables
            $TokenUser    = $result['token'];
            $TokenExpires = $result['expiration_date'];
        /******************************************/
        //Si no hay datos se carga el nuevo acceso
        }else{
            /******************************/
            //Se generan Variables
            $TokenUser    = $this->Passwords->generarPassword(20,'alfanumerico');
            $TokenExpires = $this->Operations->sumarDias($this->Server->fechaActual(),1).' '.$this->Server->horaActual();

            /***************************************************/
            /*         Se guarda el ingreso del usuario        */
            /***************************************************/
            //Se agrega respuesta
            $arrData = [
                'idUsuario'       => $rowData['idUsuario'],
                'Fecha'           => $this->Server->fechaActual(),
                'Hora'            => $this->Server->horaActual(),
                'DateTime'        => $this->Server->fechaActual().' '.$this->Server->horaActual(),
                'IP_Client'       => $this->Client->getClientIp(),
                'Agent_Transp'    => $this->Client->getBrowser(),
                'idSistema'       => 1,
                'token'           => $TokenUser,
                'expiration_date' => $TokenExpires,
                'idEstado'        => 1,
            ];
            /******************************/
            //Se genera la query
            $query = [
                'data'      => 'idUsuario, Fecha, Hora, DateTime, IP_Client, Agent_Transp, idSistema, token, expiration_date, idEstado',
                'required'  => '',
                'unique'    => '',
                'table'     => 'usuarios_accesos',
                'Post'      => $arrData,
            ];
            //Ejecuto la query
            $this->QBuilder->queryInsert($query, $this->DBConn);

            /***************************************************/
            /*        Se actualiza el ingreso del usuario      */
            /***************************************************/
            //Se agrega respuesta
            $arrData = [
                'idUsuario'       => $rowData['idUsuario'],
                'Ultimo_acceso'   => $this->Server->fechaActual(),
                'IP_Client'       => $this->Client->getClientIp(),
                'Agent_Transp'    => $this->Client->getBrowser(),
            ];
            /******************************/
            //Se genera la query
            $query = [
                'data'      => 'idUsuario,Ultimo_acceso,IP_Client,Agent_Transp',
                'required'  => 'idUsuario,Ultimo_acceso,IP_Client,Agent_Transp',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'usuarios_listado',
                'where'     => 'idUsuario',
                'Post'      => $arrData,
            ];
            //Ejecuto la query
            $xParams = ['DataCheck' => '', 'query' => $query];
            $this->Base_update($xParams);
        }

        /***************************************************/
        /*         Se generan los datos del usuario        */
        /***************************************************/
        //Armo los datos del usuario
        $rowUsuario = [
            'UserID'             => $rowData['idUsuario'],
            'UserType'           => $rowData['idTipoUsuario'],
            'UserIMG'            => $rowData['Direccion_img'],
            'UserName'           => $rowData['Nombre'],
            'UserPosition'       => $rowData['Posicion'],
            'idMenuPosicion'     => $rowData['idMenuPosicion'],
            'UbicacionNombre'    => $rowData['UbicacionNombre'],
            'UbicacionWheater'   => $rowData['UbicacionWheater'],
            'TypeSession'        => $TypeSession,
            'UserIP'             => $this->Client->getClientIp(),
        ];
        /******************************/
        //Se cargan los datos de la plataforma
        $query = [
            'data'   => '*',
            'table'  => 'core_sistemas',
            'join'   => '',
            'where'  => 'idSistema = "1"',
            'group'  => '',
            'having' => '',
            'order'  => ''
        ];
        //Verifico si hay un dato
        $xParams     = ['query' => $query];
        $rowOpciones = $this->Base_GetByID($xParams);

        /******************************/
        //Se fucionan arreglos
        $Resultado = array_merge($rowUsuario, $rowOpciones);

        /***************************************************/
        /*        Consulta para los permisos y menus       */
        /***************************************************/
        //Verifico el tipo de usuario
        switch ($rowData['idTipoUsuario']) {
            /******************************/
            //Super administrador
            case 1:
                /******************************/
                //Consulta para el menu
                $query = [
                    'data'    => '
                        core_permisos_categorias.Nombre AS PermisosCat,
                        core_permisos_categorias.Icon AS PermisosIcon,
                        core_iconos_colores.Nombre AS PermisosIconColor,
                        core_permisos_listado.Nombre,
                        core_permisos_listado.RutaWeb,
                        core_permisos_listado.idLevelLimit AS PermisosLevel,
                        core_permisos_listado.RutaController AS PermisosController',
                    'table'   => 'core_permisos_listado',
                    'join'    => '
                        LEFT JOIN core_permisos_categorias ON core_permisos_categorias.idPermisosCat = core_permisos_listado.idPermisosCat
                        LEFT JOIN core_iconos_colores      ON core_iconos_colores.idColor            = core_permisos_categorias.IdIconColor',
                    'where'   => 'core_permisos_listado.idEstado =1',
                    'group'   => '',
                    'having'  => '',
                    'order'   => 'core_permisos_categorias.Nombre ASC, core_permisos_listado.Nombre ASC, core_permisos_listado.RutaWeb ASC',
                    'limit'   => ConfigAPP::APP["N_MaxItems"]
                ];
                //Ejecuto la query
                $xParams = ['query' => $query];
                $arrMenu = $this->Base_GetList($xParams);
                /******************************/
                //Consulta para las rutas
                $query = [
                    'data'    => '
                        core_permisos_listado_rutas_metodo.Nombre AS Metodo,
                        core_permisos_listado_rutas.RutaWeb,
                        core_permisos_listado_rutas.RutaController',
                    'table'   => 'core_permisos_listado',
                    'join'    => '
                        LEFT JOIN core_permisos_listado_rutas        ON core_permisos_listado_rutas.idPermisos      = core_permisos_listado.idPermisos
                        LEFT JOIN core_permisos_listado_rutas_metodo ON core_permisos_listado_rutas_metodo.idMetodo = core_permisos_listado_rutas.idMetodo',
                    'where'   => 'core_permisos_listado.idEstado =1',
                    'group'   => '',
                    'having'  => '',
                    'order'   => 'core_permisos_listado_rutas_metodo.Nombre ASC, core_permisos_listado_rutas.RutaWeb ASC, core_permisos_listado_rutas.RutaController ASC',
                    'limit'   => ConfigAPP::APP["N_MaxItems"]
                ];
                //Ejecuto la query
                $xParams     = ['query' => $query];
                $arrPermisos = $this->Base_GetList($xParams);

                break;

            /******************************/
            //Normal
            default:
                /******************************/
                //Consulta para el menu
                $query = [
                    'data'    => '
                        core_permisos_categorias.Nombre AS PermisosCat,
                        core_permisos_categorias.Icon AS PermisosIcon,
                        core_iconos_colores.Nombre AS PermisosIconColor,
                        core_permisos_listado.Nombre,
                        core_permisos_listado.RutaWeb,
                        usuarios_listado_permisos.idLevelLimit AS PermisosLevel,
                        core_permisos_listado.RutaController AS PermisosController',
                    'table'   => 'usuarios_listado_permisos',
                    'join'    => '
                        LEFT JOIN core_permisos_listado    ON core_permisos_listado.idPermisos       = usuarios_listado_permisos.idPermisos
                        LEFT JOIN core_permisos_categorias ON core_permisos_categorias.idPermisosCat = core_permisos_listado.idPermisosCat
                        LEFT JOIN core_iconos_colores      ON core_iconos_colores.idColor            = core_permisos_categorias.IdIconColor',
                    'where'   => 'usuarios_listado_permisos.idUsuario = '.$rowData['idUsuario'].' AND core_permisos_listado.idEstado =1',
                    'group'   => '',
                    'having'  => '',
                    'order'   => 'core_permisos_categorias.Nombre ASC, core_permisos_listado.Nombre ASC, core_permisos_listado.RutaWeb ASC',
                    'limit'   => ConfigAPP::APP["N_MaxItems"]
                ];
                //Ejecuto la query
                $xParams = ['query' => $query];
                $arrMenu = $this->Base_GetList($xParams);
                /******************************/
                //Consulta para las rutas
                $query = [
                    'data'    => '
                        core_permisos_listado_rutas_metodo.Nombre AS Metodo,
                        core_permisos_listado_rutas.RutaWeb,
                        core_permisos_listado_rutas.RutaController',
                    'table'   => 'usuarios_listado_permisos',
                    'join'    => '
                        LEFT JOIN core_permisos_listado              ON core_permisos_listado.idPermisos            = usuarios_listado_permisos.idPermisos
                        LEFT JOIN core_permisos_listado_rutas        ON core_permisos_listado_rutas.idPermisos      = core_permisos_listado.idPermisos AND core_permisos_listado_rutas.idLevelLimit <= usuarios_listado_permisos.idLevelLimit
                        LEFT JOIN core_permisos_listado_rutas_metodo ON core_permisos_listado_rutas_metodo.idMetodo = core_permisos_listado_rutas.idMetodo',
                    'where'   => 'usuarios_listado_permisos.idUsuario = '.$rowData['idUsuario'].' AND core_permisos_listado.idEstado =1',
                    'group'   => '',
                    'having'  => '',
                    'order'   => 'core_permisos_listado_rutas_metodo.Nombre ASC, core_permisos_listado_rutas.RutaWeb ASC, core_permisos_listado_rutas.RutaController ASC',
                    'limit'   => ConfigAPP::APP["N_MaxItems"]
                ];
                //Ejecuto la query
                $xParams     = ['query' => $query];
                $arrPermisos = $this->Base_GetList($xParams);

                break;
        }

        /******************************/
        //se crea variable para los niveles de permisos
        $arrLevel = [];
        //se recorren las variables
        foreach ($arrMenu as $value) {
            //se crea la variable
            $arrLevel[$value['PermisosController']]['LevelAccess']  = $value['PermisosLevel'];
            $arrLevel[$value['PermisosController']]['RouteAccess']  = $value['RutaWeb'];
        }
        //Si es un inicio normal
        if($TypeSession==1){
            //Permisos rutas de prueba
            $arrLevel['crudNormal']['LevelAccess']   = 4;
            $arrLevel['crudResumen']['LevelAccess']  = 4;
            $arrLevel['crudInforme']['LevelAccess']  = 4;
            $arrLevel['Empty']['LevelAccess']        = 4;
            $arrLevel['crudNormal']['RouteAccess']   = 'Core/pruebas/crudNormal';
            $arrLevel['crudResumen']['RouteAccess']  = 'Core/pruebas/crudResumen';
            $arrLevel['crudInforme']['RouteAccess']  = 'Core/pruebas/crudInforme';
            $arrLevel['Empty']['RouteAccess']        = '';
        }

        /***************************************************/
        /*          Se guardan lo datos del usuario        */
        /***************************************************/
        //Se agrupan los menus
        $arrMenuNew = $this->CommonData->agruparPorClave ($arrMenu, 'PermisosCat' );
        //Seteo las variables
        $f3->set('SESSION.TokenUser', $TokenUser);       //Token del usuario
        $f3->set('SESSION.TokenExpires', $TokenExpires); //token valido por 1 dia
        $f3->set('SESSION.DataInfo', $Resultado);        //Datos del usuario
        $f3->set('SESSION.arrMenu', $arrMenuNew);        //Menu
        $f3->set('SESSION.arrPermisos', $arrPermisos);   //Rutas
        $f3->set('SESSION.arrLevel', $arrLevel);         //Niveles de permisos

    }

}
