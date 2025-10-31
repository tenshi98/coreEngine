<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class miUsuario extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;
    private $FormInputs;
    private $DataDate;
    private $DataNumbers;
    private $WidgetsCommon;
    private $userSession;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'Empty';
		$this->FormInputs     = new UIFormInputs();
		$this->DataDate       = new FunctionsDataDate();
		$this->DataNumbers    = new FunctionsDataNumbers();
		$this->WidgetsCommon  = new UIWidgetsCommon();
		$this->userSession    = new userSession();
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
        //Se cargan los datos de la sesion
        $Response = $this->userSession->login($f3, $_POST, 1);

        /******************************************/
        //Si el acceso es correcto
        if($Response['code']==200){
            //Se genera la cookie
            setcookie('Sesion_tk_'.date("Y-m-d"),$f3->get('SESSION.TokenUser'),time() + (60*60*24));
            //imprimo resultados
            echo Response::sendData($Response['code'], $Response['message']);
        //Si da otro error
        }else{
            //imprimo resultados
            echo Response::sendData($Response['code'], $Response['message']);
        }

    }

    /******************************************************************************/
    //Recuperar contraseña
    public function forgot($f3){

        /******************************/
        //Se cargan los datos de la sesion
        $Response = $this->userSession->forgot($f3, $_POST);

        /******************************************/
        //imprimo resultados
        echo Response::sendData($Response['code'], $Response['message']);

    }

    /******************************************************************************/
    //cierra sesion
    public function logout($f3){

        /******************************/
        //Se cargan los datos de la sesion
        $Response = $this->userSession->logout($f3, $_POST);

        /******************************************/
        //Si es correcto
        if($Response['code']==200){
            //Se limpian las cookies
            setcookie('Sesion_tk_'.date("Y-m-d"),'',time()-1);
            // También es recomendable unset($_COOKIE['']) para borrar la cookie de la superglobal $_COOKIE
            unset($_COOKIE['Sesion_tk_'.date("Y-m-d")]);
            //Se redirige al index
            $f3->reroute('/');
            //imprimo resultados
            echo Response::sendData($Response['code'], $Response['message']);
        //Si da otro error
        }else{
            //imprimo resultados
            echo Response::sendData($Response['code'], $Response['message']);
        }

    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //Ver Datos
    public function view($f3){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************/
        //Se genera la query
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
            'where'   => 'usuarios_listado.idUsuario = "'.$f3->get('SESSION.DataInfo.UserID').'"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /******************************/
        //Se genera la query
        $query = [
            'data'    => 'idCiudad AS ID,Nombre',
            'table'   => 'core_ubicacion_ciudad',
            'join'    => '',
            'where'   => '',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrCiudad = $this->Base_GetList($xParams);

        /******************************/
        //Se genera la query
        $query = [
            'data'    => 'idComuna AS ID1, idCiudad AS ID2, Nombre',
            'table'   => 'core_ubicacion_comunas',
            'join'    => '',
            'where'   => '',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrComuna = $this->Base_GetList($xParams);

        /******************************/
        //Se genera la query
        $query = [
            'data'    => 'idMenuPosicion AS ID,Nombre',
            'table'   => 'core_posicion_menu',
            'join'    => '',
            'where'   => '',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams     = ['query' => $query];
        $arrPosicion = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if ($rowData!==false) {

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Perfil',
                'PageDescription' => 'Perfil',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                'Fnc_WidgetsCommon'   => $this->WidgetsCommon,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData,
                'arrCiudad'       => $arrCiudad,
                'arrComuna'       => $arrComuna,
                'arrPosicion'     => $arrPosicion,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 1, $this->returnRutaVista(__DIR__, 'app').'/miUsuario-data.php');

        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 1, $f3);
        }

    }

    /******************************************************************************/
    //Ver Datos
    public function FRG_UpdateData($f3){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************/
        //Se genera la query
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
            'where'   => 'usuarios_listado.idUsuario = "'.$f3->get('SESSION.DataInfo.UserID').'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'usuarios_listado.Nombre DESC'
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if ($rowData!==false) {

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                'Fnc_WidgetsCommon'   => $this->WidgetsCommon,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/miUsuario-data-UpdateData.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //Ver Datos
    public function FRG_UpdateCard($f3){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************/
        //Se genera la query
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
            'where'   => 'usuarios_listado.idUsuario = "'.$f3->get('SESSION.DataInfo.UserID').'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'usuarios_listado.Nombre DESC'
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if ($rowData!==false) {
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*=========== Datos Consultados ===========*/
                'rowData' => $rowData,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/miUsuario-data-UpdateCard.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
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
            //Se genera el chequeo
            $DataCheck = $this->dataCheck($_POST);

            /******************************/
            //Se genera la query
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
            //Ejecuto la query
            $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
            $Response = $this->Base_update($xParams);
            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response===true) {
                //Se actualiza la sesion del usuario
                $this->userSession->updateSession($_POST['idUsuario'], $f3, 1);
                // Devuelvo $Response con código 200 (OK)
                echo Response::sendData(200, $Response);
            } else {
                // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
                // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
                echo Response::sendData(500, $Response);
            }

        }else {
            echo Response::sendData(500, "Error en el Request Method");
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
            //Se genera la query
            $query = [
                'files'       => 'Direccion_img',
                'table'       => 'usuarios_listado',
                'where'       => 'idUsuario',
                'SubCarpeta'  => '',
                'Post'        => $dataPut
            ];
            //Ejecuto la query
            $xParams  = ['query' => $query];
            $Response = $this->Base_delFiles($xParams);
            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response===true) {
                //Se actualiza la sesion del usuario
                $this->userSession->updateSession($dataPut['idUsuario'], $f3, 1);
                // Devuelvo $Response con código 200 (OK)
                echo Response::sendData(200, $Response);
            } else {
                // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
                // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
                echo Response::sendData(500, $Response);
            }

        }else {
            echo Response::sendData(500, "Error en el Request Method");
        }
    }

    /******************************************************************************/
    //Se validan los datos
    private function dataCheck($POST){
        //Variables
        $DataChecking = [
            'emptyData'                 => 'mainPassword,oldPassword,password,rePassword',
            'encode'                    => 'oldPassword',
            'ValidarEmail'              => 'email',
            'ValidarNumero'             => '',
            'ValidarEntero'             => '',
            'ValidarRut'                => 'Rut',
            'ValidarPatente'            => '',
            'ValidarFecha'              => 'fNacimiento',
            'ValidarHora'               => '',
            'ValidarURL'                => 'Social_X,Social_Facebook,Social_Instagram,Social_Linkedin',
            'ValidarLargoMinimo'        => 'Nombre,Direccion',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'Nombre,Direccion',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Nombre,Direccion',
            'ValidarEspaciosVacios'     => 'email,password,rePassword',
            'ValidarMayusculas'         => 'email',
            'ValidarCoincidencias'      => 'mainPassword-oldPassword,password-rePassword',
            'Post'                      => $POST,
        ];
        //Devuelvo
        return $DataChecking;
    }


}