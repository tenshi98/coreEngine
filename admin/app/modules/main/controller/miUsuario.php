<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class miUsuario extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $DataDate;
    private $DataNumbers;
    private $WidgetsCommon;

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'Empty';
		$this->FormInputs     = new UIFormInputs();
		$this->DataDate       = new FunctionsDataDate();
		$this->DataNumbers    = new FunctionsDataNumbers();
		$this->WidgetsCommon  = new UIWidgetsCommon();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                 SESIONES                                   */
    /******************************************************************************/
    /*******************************************************************/
    // Login
    /*******************************************************************/
    public function login($f3){

        /************************************/
        // Se llama la clase
        $authenticationService = new AuthenticationService();
        // Se autentica al usuario y se cargan los datos de sesión
        $Response = $authenticationService->authenticate($f3, $_POST);

        /************************************/
        // Si el acceso es correcto
        if($Response['code']==200){
            // Se informa el resultado
            Response::success($Response['message']);
        }
        // Se informa el error de autenticación
        Response::error($Response['message'], $Response['code'], $Response['message']);

    }

    /*******************************************************************/
    // Recuperar contraseña
    /*******************************************************************/
    public function forgot($f3){

        /************************************/
        // Se llama la clase
        $authenticationService = new AuthenticationService();
        // Se autentica al usuario y se actualizan los datos
        $Response = $authenticationService->recoverPassword($f3, $_POST);

        /************************************/
        //imprimo resultados
        Response::error($Response['message'], $Response['code'], $Response['message']);

    }

    /*******************************************************************/
    // Cierra sesion
    /*******************************************************************/
    public function logout($f3){

        /************************************/
        // Se llama la clase
        $authenticationService = new AuthenticationService();
        // Se eliminan los datos de sesion (sesion y coockies)
        $Response = $authenticationService->closeSession($f3);

        /************************************/
        //Si es correcto
        if($Response['code']==200){
            //Se redirige al index
            $f3->reroute('/');
            //imprimo resultados
            Response::success($Response['message']);
        //Si da otro error
        }else{
            //imprimo resultados
            Response::error($Response['message'], $Response['code'], $Response['message']);
        }

    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /*******************************************************************/
    // Ver Datos
    /*******************************************************************/
    public function view($f3){

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                usuarios_listado.idUsuario,
                usuarios_listado.idTipoUsuario,
                usuarios_listado.idEstado,
                usuarios_listado.email,
                usuarios_listado.Nombre,
                usuarios_listado.Rut,
                usuarios_listado.fNacimiento,
                usuarios_listado.Fono,
                usuarios_listado.idCiudad,
                usuarios_listado.idComuna,
                usuarios_listado.Direccion,
                usuarios_listado.Direccion_img,
                usuarios_listado.Social_X,
                usuarios_listado.Social_Facebook,
                usuarios_listado.Social_Instagram,
                usuarios_listado.Social_Linkedin,
                usuarios_listado.idMenuPosicion,
                usuarios_listado.password,

                core_tipos_usuario.Nombre AS TipoUsuario,
                core_estados.Nombre AS Estado,
                core_ubicacion_ciudad.Nombre AS Ciudad,
                core_ubicacion_comunas.Nombre AS Comuna,
                core_posicion_menu.Nombre AS MenuPosicion',
            'table'   => 'usuarios_listado',
            'join'    => '
                LEFT JOIN core_tipos_usuario      ON core_tipos_usuario.idTipoUsuario   = usuarios_listado.idTipoUsuario
                LEFT JOIN core_estados            ON core_estados.idEstado              = usuarios_listado.idEstado
                LEFT JOIN core_ubicacion_ciudad   ON core_ubicacion_ciudad.idCiudad     = usuarios_listado.idCiudad
                LEFT JOIN core_ubicacion_comunas  ON core_ubicacion_comunas.idComuna    = usuarios_listado.idComuna
                LEFT JOIN core_posicion_menu      ON core_posicion_menu.idMenuPosicion  = usuarios_listado.idMenuPosicion',
            'where'   => 'usuarios_listado.idUsuario = ?',
            'params'  => [$f3->get('SESSION.DataInfo.UserID')],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idCiudad AS ID,Nombre',
            'table'   => 'core_ubicacion_ciudad',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams   = ['query' => $query];
        // Ejecuto la query
        $arrCiudad = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idComuna AS ID1, idCiudad AS ID2, Nombre',
            'table'   => 'core_ubicacion_comunas',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams   = ['query' => $query];
        // Ejecuto la query
        $arrComuna = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idMenuPosicion AS ID,Nombre',
            'table'   => 'core_posicion_menu',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams     = ['query' => $query];
        // Ejecuto la query
        $arrPosicion = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrCiudad['status'] && $arrComuna['status'] && $arrPosicion['status']){

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Perfil',
                'PageDescription' => 'Perfil',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                'Fnc_WidgetsCommon'   => $this->WidgetsCommon,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData['data'],
                'arrCiudad'       => $arrCiudad['data'],
                'arrComuna'       => $arrComuna['data'],
                'arrPosicion'     => $arrPosicion['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/miUsuario-data.php');

        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrCiudad,$arrComuna,$arrPosicion]);
            // Despliegue de errores
            $this->showError(1, $f3, $result);
        }

    }

    /*******************************************************************/
    // Ver Datos
    /*******************************************************************/
    public function FRG_UpdateData($f3){

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                usuarios_listado.email,
                usuarios_listado.Nombre,
                usuarios_listado.Rut,
                usuarios_listado.fNacimiento,
                usuarios_listado.Fono,
                usuarios_listado.Direccion,
                usuarios_listado.Direccion_img,
                usuarios_listado.Social_X,
                usuarios_listado.Social_Facebook,
                usuarios_listado.Social_Instagram,
                usuarios_listado.Social_Linkedin,

                core_tipos_usuario.Nombre AS TipoUsuario,
                core_estados.Nombre AS Estado,
                core_ubicacion_ciudad.Nombre AS Ciudad,
                core_ubicacion_comunas.Nombre AS Comuna,
                core_posicion_menu.Nombre AS MenuPosicion',
            'table'   => 'usuarios_listado',
            'join'    => '
                LEFT JOIN core_tipos_usuario      ON core_tipos_usuario.idTipoUsuario   = usuarios_listado.idTipoUsuario
                LEFT JOIN core_estados            ON core_estados.idEstado              = usuarios_listado.idEstado
                LEFT JOIN core_ubicacion_ciudad   ON core_ubicacion_ciudad.idCiudad     = usuarios_listado.idCiudad
                LEFT JOIN core_ubicacion_comunas  ON core_ubicacion_comunas.idComuna    = usuarios_listado.idComuna
                LEFT JOIN core_posicion_menu      ON core_posicion_menu.idMenuPosicion  = usuarios_listado.idMenuPosicion',
            'where'   => 'usuarios_listado.idUsuario = ?',
            'params'  => [$f3->get('SESSION.DataInfo.UserID')],
            'group'   => '',
            'having'  => '',
            'order'   => 'usuarios_listado.Nombre DESC'
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($rowData['status'] === true) {

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                'Fnc_WidgetsCommon'   => $this->WidgetsCommon,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/miUsuario-data-UpdateData.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Ver Datos
    /*******************************************************************/
    public function FRG_UpdateCard($f3){

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                usuarios_listado.Nombre,
                usuarios_listado.Direccion_img,
                usuarios_listado.Social_X,
                usuarios_listado.Social_Facebook,
                usuarios_listado.Social_Instagram,
                usuarios_listado.Social_Linkedin,
                core_tipos_usuario.Nombre AS TipoUsuario',
            'table'   => 'usuarios_listado',
            'join'    => 'LEFT JOIN core_tipos_usuario ON core_tipos_usuario.idTipoUsuario = usuarios_listado.idTipoUsuario',
            'where'   => 'usuarios_listado.idUsuario = ?',
            'params'  => [$f3->get('SESSION.DataInfo.UserID')],
            'group'   => '',
            'having'  => '',
            'order'   => 'usuarios_listado.Nombre DESC'
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($rowData['status'] === true) {
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*=========== Datos Consultados ===========*/
                'rowData' => $rowData['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/miUsuario-data-UpdateCard.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    /*                                  DATOS                                     */
    /******************************************************************************/
    /*******************************************************************/
    // Editar por put (solo modificar datos)
    // Editar por post (modificar y subir archivos)
    /*******************************************************************/
    public function update($f3){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Usuario creador
        $_POST['idUsuario'] = $f3->get('SESSION.DataInfo.UserID');

        //Verifico si esta haciendo un cambio de contraseña
        if(isset($_POST['oldPassword'], $_POST['rePassword'])&&$_POST['oldPassword']!=''&&$_POST['rePassword']!=''){
            /************************************/
            // Se genera la query
            $query = [
                'data'    => 'password',
                'table'   => 'usuarios_listado',
                'join'    => '',
                'where'   => 'idUsuario = ?',
                'params'  => [$f3->get('SESSION.DataInfo.UserID')],
                'group'   => '',
                'having'  => '',
                'order'   => 'Nombre DESC'
            ];
            // Preparo los datos
            $xParams = ['query' => $query];
            // Ejecuto la query
            $rowData = $this->Base_GetByID($xParams);

            /************************************/
            // Se verifica que la consulta haya devuelto el usuario
            if(!$rowData['status'] || empty($rowData['data']) || !isset($rowData['data']['password'])){
                Response::error('No fue posible verificar la contraseña actual', 500);
            }

            /************************************/
            // Se verifica que se ingrese la password correcta
            // Se instancia la clase
            $SecurityPasswords = new FunctionsSecurityPasswords();
            // Se verifica la contraseña
            $checkPassword = $SecurityPasswords->hashVerify($_POST['oldPassword'], $rowData['data']['password']);
            if($checkPassword!=true){
                Response::error('Error al ingresar las contraseñas', 500);
            }
            /************************************/
            // Se verifica que la nueva password y la repeticion sean iguales
            if($_POST['password']!=$_POST['rePassword']){
                Response::error('Las contraseñas no coinciden', 500);
            }

        }

        /************************************/
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idUsuario,password,idTipoUsuario,idEstado,email,Nombre,Rut,fNacimiento,Fono,idCiudad,idComuna,Direccion,Ultimo_acceso,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin,IP_Client,Agent_Transp,idMenuPosicion',
            'required'  => 'idUsuario,idTipoUsuario,idEstado,email,Nombre,idMenuPosicion',
            'unique'    => '',
            'encode'    => 'password',
            'table'     => 'usuarios_listado',
            'where'     => 'idUsuario',
            'Post'      => $_POST,
            'files'     => [
                [
                    'Identificador' => 'Direccion_img',
                    'SubCarpeta'    => '',
                    'NombreArchivo' => '',
                    'SufijoArchivo' => 'UsuarioIMG_',
                    'ValidarTipo'   => 'image',
                    'ValidarPeso'   => 10,
                    'Base64'        => true
                ],
            ]
        ];
        // Preparo los datos
        $xParams        = ['DataCheck' => $DataCheck, 'query' => $query];
        // Ejecuto la query
        $ResponseSesion = $this->Base_update($xParams);
        /************************************/
        // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
        if ($ResponseSesion['status'] === true) {
            /************************************/
            // Se cargan las clases
            $authenticationService = new AuthenticationService();
            $sessionService        = new SessionService();
            $Client                = new FunctionsServerClient();
            //Se actualiza la sesion del usuario
            $authenticationService->updateSession($f3, $_POST['idUsuario'], $sessionService, $Client);
            // Devuelvo $Response con código 200 (OK)
            Response::success($ResponseSesion['data']);
        } else {
            // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
            // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
            Response::error('Error al operar con la Base de Datos', 500, $ResponseSesion['error']);
        }

    }

    /*******************************************************************/
    // Borrar archivos
    /*******************************************************************/
    public function delFiles($f3){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Obtener datos
        parse_str(file_get_contents("php://input"),$dataPut);

        /************************************/
        // Se genera la query
        $query = [
            'files'       => 'Direccion_img',
            'table'       => 'usuarios_listado',
            'where'       => 'idUsuario',
            'SubCarpeta'  => '',
            'Post'        => $dataPut
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $Response = $this->Base_delFiles($xParams);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if ($Response['status'] === false) {
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
        }

        /************************************/
        // Se cargan las clases
        $authenticationService = new AuthenticationService();
        $sessionService        = new SessionService();
        $Client                = new FunctionsServerClient();
        //Se actualiza la sesion del usuario
        $authenticationService->updateSession($f3, $dataPut['idUsuario'], $sessionService, $Client);
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data']);

    }

    /******************************************************************************/
    /*                             Métodos privados                               */
    /******************************************************************************/
    /*******************************************************************/
    // Se validan los datos
    /*******************************************************************/
    private function dataCheck($POST){
        // Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => 'email',
            'ValidarNumero'             => '',
            'ValidarEntero'             => '',
            'ValidarRut'                => 'Rut',
            'ValidarPatente'            => '',
            'ValidarFecha'              => 'fNacimiento',
            'ValidarHora'               => '',
            'ValidarURL'                => 'Social_X,Social_Facebook,Social_Instagram,Social_Linkedin',
            'ValidarLargoMinimo'        => 'mainPassword,oldPassword,password,rePassword,email,Nombre,Direccion',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'mainPassword,oldPassword,password,rePassword,email,Nombre,Direccion',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Nombre,Direccion',
            'ValidarEspaciosVacios'     => 'email,mainPassword,oldPassword,password,rePassword,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin',
            'ValidarMayusculas'         => 'email',
            'ValidarCoincidencias'      => '',
            'ValidarDominioEmail'       => 'email',
            'ValidarPasswordSegura'     => '',
            'ValidarFechaRango'         => 'fNacimiento',
            'ValidarEdadMinima'         => '',
            'ValidarJSON'               => '',
            'ValidarUUID'               => '',
            'ValidarIP'                 => '',
            'ValidarSoloAlfanumerico'   => '',
            'ValidarSoloLetras'         => '',
            'Post'                      => $POST,
        ];
        // Retorno los datos
        return $DataChecking;
    }


}
