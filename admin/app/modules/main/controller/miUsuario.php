<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class miUsuario extends ControllerBase {

    /******************************************************************************/
    // Variables
    private $controllerName;
    private $FormInputs;
    private $DataDate;
    private $DataNumbers;
    private $WidgetsCommon;

    /******************************************************************************/
    //Constructor
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
    /******************************************************************************/
    //Login
    public function login($f3){

        /******************************/
        // Se llama la clase
        $authenticationService = new AuthenticationService();
        // Se autentica al usuario y se cargan los datos de sesión
        $Response = $authenticationService->authenticate($f3, $_POST);

        /******************************************/
        // Si el acceso es correcto
        if($Response['code']==200){
            // Se informa el resultado
            Response::success($Response['message']);
        }
        // Se informa el error de autenticación
        Response::error($Response['message'], $Response['code'], $Response['message']);

    }

    /******************************************************************************/
    //Recuperar contraseña
    public function forgot($f3){

        /******************************/
        // Se llama la clase
        $authenticationService = new AuthenticationService();
        // Se autentica al usuario y se actualizan los datos
        $Response = $authenticationService->recoverPassword($f3, $_POST);

        /******************************************/
        //imprimo resultados
        Response::error($Response['message'], $Response['code'], $Response['message']);

    }

    /******************************************************************************/
    //cierra sesion
    public function logout($f3){

        /******************************/
        // Se llama la clase
        $authenticationService = new AuthenticationService();
        // Se eliminan los datos de sesion (sesion y coockies)
        $Response = $authenticationService->closeSession($f3);

        /******************************************/
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
    /******************************************************************************/
    //Ver Datos
    public function view($f3){
        /******************************/
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
        // Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /******************************/
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
        // Ejecuto la query
        $xParams   = ['query' => $query];
        $arrCiudad = $this->Base_GetList($xParams);

        /******************************/
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
        // Ejecuto la query
        $xParams   = ['query' => $query];
        $arrComuna = $this->Base_GetList($xParams);

        /******************************/
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
        // Ejecuto la query
        $xParams     = ['query' => $query];
        $arrPosicion = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrCiudad['status'] && $arrComuna['status'] && $arrPosicion['status']){

            /******************************************/
            //Datos enviados a la pagina
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

            /******************************************/
            //Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/miUsuario-data.php');

        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrCiudad,$arrComuna,$arrPosicion]);
            //Muestra los errores
            $this->showError(1, $f3, $result);
        }

    }

    /******************************************************************************/
    //Ver Datos
    public function FRG_UpdateData($f3){
        /******************************/
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
        // Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status']){

            /******************************************/
            //Datos enviados a la pagina
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

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/miUsuario-data-UpdateData.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$rowData]);
            //Muestra los errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    //Ver Datos
    public function FRG_UpdateCard($f3){
        /******************************/
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
        // Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status']){
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*=========== Datos Consultados ===========*/
                'rowData' => $rowData['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/miUsuario-data-UpdateCard.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$rowData]);
            //Muestra los errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    /*                                  DATOS                                     */
    /******************************************************************************/
    /******************************************************************************/
    //Editar por put (solo modificar datos)
    //Editar por post (modificar y subir archivos)
    public function update($f3){
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            /******************************/
            // Usuario creador
            $_POST['idUsuario'] = $f3->get('SESSION.DataInfo.UserID');

            /******************************/
            //Se genera el chequeo
            $DataCheck = $this->dataCheck($_POST);

            /******************************/
            // Se genera la query
            $query = [
                'data'      => 'password,idTipoUsuario,idEstado,email,Nombre,Rut,fNacimiento,Fono,idCiudad,idComuna,Direccion,Ultimo_acceso,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin,IP_Client,Agent_Transp,idMenuPosicion',
                'required'  => 'Nombre,Rut,email',
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
                        'SufijoArchivo' => 'Perfil_',
                        'ValidarTipo'   => 'image',
                        'ValidarPeso'   => 10,
                        'Base64'        => true
                    ],
                ]
            ];
            // Ejecuto la query
            $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
            $Response = $this->Base_update($xParams);
            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response['status']){
                /******************************/
                // Se cargan las clases
                $authenticationService = new AuthenticationService();
                $sessionService        = new SessionService();
                $Client                = new FunctionsServerClient();
                //Se actualiza la sesion del usuario
                $authenticationService->updateSession($f3, $_POST['idUsuario'], $sessionService, $Client);
                // Devuelvo $Response con código 200 (OK)
                Response::success($Response['data']);
            } else {
                // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
                // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
                Response::error('Error al operar con la Base de Datos', 500, $Response['error']);
            }
        }else {
            // Request Method no esperado
            Response::error('Error en el Request Method', 500);
        }
    }

    /******************************************************************************/
    //Permite eliminar archivos
    public function delFiles($f3){
        //Verificacion metodo PUT
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            //Se parsean los datos
            parse_str(file_get_contents("php://input"),$dataPut);
            /******************************/
            // Se genera la query
            $query = [
                'files'       => 'Direccion_img',
                'table'       => 'usuarios_listado',
                'where'       => 'idUsuario',
                'SubCarpeta'  => '',
                'Post'        => $dataPut
            ];
            // Ejecuto la query
            $xParams  = ['query' => $query];
            $Response = $this->Base_delFiles($xParams);
            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response['status']){
                /******************************/
                // Se cargan las clases
                $authenticationService = new AuthenticationService();
                $sessionService        = new SessionService();
                $Client                = new FunctionsServerClient();
                //Se actualiza la sesion del usuario
                $authenticationService->updateSession($f3, $dataPut['idUsuario'], $sessionService, $Client);
                // Devuelvo $Response con código 200 (OK)
                Response::success($Response['data']);
            } else {
                // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
                // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
                Response::error('Error al operar con la Base de Datos', 500, $Response['error']);
            }
        }else {
            // Request Method no esperado
            Response::error('Error en el Request Method', 500);
        }
    }

    /******************************************************************************/
    /*                             Métodos privados                               */
    /******************************************************************************/
    /******************************************************************************/
    //Se validan los datos
    private function dataCheck($POST){
        // Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => 'oldPassword',
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
            'ValidarCoincidencias'      => 'mainPassword-oldPassword,password-rePassword',
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
        //Devuelvo
        return $DataChecking;
    }


}
