<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class tercerosEntidadesListadoUsuarios extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataDate;
    private $WidgetsCommon;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'tercerosEntidadesListado';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->DataDate       = new FunctionsDataDate();
		$this->WidgetsCommon  = new UIWidgetsCommon();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //Crear nuevo
    public function New($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => 'idEntidad',
            'table'   => 'entidades_listado',
            'join'    => '',
            'where'   => 'idEntidad = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
            'data'    => 'idTipoUsuario AS ID,Nombre',
            'table'   => 'core_tipos_usuario',
            'join'    => '',
            'where'   => 'idTipoUsuario!=1',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams        = ['query' => $query];
        $arrTipoUsuario = $this->Base_GetList($xParams);

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
                'Fnc_FormInputs'       => $this->FormInputs,
                'Fnc_Codification'     => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'rowData'           => $rowData,
                'arrTipoUsuario'    => $arrTipoUsuario,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Usuarios-formNew.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }
    /******************************************************************************/
    //List
    public function UpdateList($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');
        $arrMenu  = $f3->get('SESSION.arrMenu');

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => '
                terceros_entidades_listado_usuarios.idEntidad,
                terceros_entidades_listado_usuarios.idUsuario,
                terceros_entidades_listado_usuarios.email,
                terceros_entidades_listado_usuarios.Nombre,
                terceros_entidades_listado_usuarios.Ultimo_acceso,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor',
            'table'   => 'terceros_entidades_listado_usuarios',
            'join'    => 'LEFT JOIN core_estados ON core_estados.idEstado = terceros_entidades_listado_usuarios.idEstado',
            'where'   => 'terceros_entidades_listado_usuarios.idEntidad = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'terceros_entidades_listado_usuarios.email ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams     = ['query' => $query];
        $arrUsuarios = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Variables
        $MainViewData = [
            'Count_Maquinas' => 0,
        ];
        //Se asignan datos a buscar
        $menuCounters = [
            'Externalizacion Servicios' => [
                'Clientes - Opciones Extras' => 'Count_Maquinas',
            ],
        ];
        //Se recorren los permisos y se validan
        foreach ($menuCounters as $section => $names) {
            if (!empty($arrMenu[$section])) {
                foreach ($arrMenu[$section] as $asd) {
                    if (isset($names[$asd['Nombre']])) {
                        $MainViewData[$names[$asd['Nombre']]]++;
                    }
                }
            }
        }

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($arrUsuarios)){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'     => $this->Codification,
                'Fnc_DataDate'         => $this->DataDate,
                /*=========== Datos Consultados ===========*/
                'arrUsuarios'  => $arrUsuarios,
                'MainViewData' => $MainViewData,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Usuarios-UpdateList.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //View
    public function View($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');
        $arrMenu  = $f3->get('SESSION.arrMenu');

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => '
                terceros_entidades_listado_usuarios.email,
                terceros_entidades_listado_usuarios.Nombre,
                terceros_entidades_listado_usuarios.Rut,
                terceros_entidades_listado_usuarios.Fono,
                terceros_entidades_listado_usuarios.Direccion_img,
                terceros_entidades_listado_usuarios.Ultimo_acceso,
                terceros_entidades_listado_usuarios.IP_Client,
                terceros_entidades_listado_usuarios.Agent_Transp,

                core_tipos_usuario.Nombre AS TipoUsuario,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor',
            'table'   => 'terceros_entidades_listado_usuarios',
            'join'    => '
                LEFT JOIN core_tipos_usuario      ON core_tipos_usuario.idTipoUsuario   = terceros_entidades_listado_usuarios.idTipoUsuario
                LEFT JOIN core_estados            ON core_estados.idEstado              = terceros_entidades_listado_usuarios.idEstado',
            'where'   => 'terceros_entidades_listado_usuarios.idUsuario = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        //Variables
        $MainViewData = [
            'Count_Maquinas' => 0,
            'Data_Maquinas'  => '',
            'Data_Noti'      => '',
        ];
        //Se asignan datos a buscar
        $menuCounters = [
            'Externalizacion Servicios' => [
                'Clientes - Opciones Extras' => 'Count_Maquinas',
            ],
        ];
        //Se recorren los permisos y se validan
        foreach ($menuCounters as $section => $names) {
            if (!empty($arrMenu[$section])) {
                foreach ($arrMenu[$section] as $asd) {
                    if (isset($names[$asd['Nombre']])) {
                        $MainViewData[$names[$asd['Nombre']]]++;
                    }
                }
            }
        }

        /******************************************/
        if($MainViewData['Count_Maquinas']!=0){
            //Se genera la query
            $query = [
                'data'    => 'maquinas_listado.Nombre AS Maquina',
                'table'   => 'terceros_entidades_listado_usuarios_maq',
                'join'    => '
                    LEFT JOIN terceros_entidades_listado_maquinas   ON terceros_entidades_listado_maquinas.idMaq       = terceros_entidades_listado_usuarios_maq.idMaquina
                    LEFT JOIN maquinas_listado                      ON maquinas_listado.idMaquina                      = terceros_entidades_listado_maquinas.idMaquina',
                'where'   => 'terceros_entidades_listado_usuarios_maq.idUsuario='.$this->Codification->encryptDecrypt('decrypt', $params['id']),
                'group'   => '',
                'having'  => '',
                'order'   => 'maquinas_listado.Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            //Ejecuto la query
            $xParams                       = ['query' => $query];
            $MainViewData['Data_Maquinas'] = $this->Base_GetList($xParams);
            /******************************************/
            if($UserData["maquinasListadoNotificaciones"]==2){
                //Se genera la query
                $query = [
                    'data'    => 'core_telemetria_tipo_noti.Nombre AS Notificacion',
                    'table'   => 'terceros_entidades_listado_usuarios_noti',
                    'join'    => 'LEFT JOIN core_telemetria_tipo_noti   ON core_telemetria_tipo_noti.idTipoNoti = terceros_entidades_listado_usuarios_noti.idTipoNoti',
                    'where'   => 'terceros_entidades_listado_usuarios_noti.idUsuario='.$this->Codification->encryptDecrypt('decrypt', $params['id']),
                    'group'   => '',
                    'having'  => '',
                    'order'   => 'core_telemetria_tipo_noti.Nombre ASC',
                    'limit'   => ConfigAPP::APP["N_MaxItems"]
                ];
                //Ejecuto la query
                $xParams                   = ['query' => $query];
                $MainViewData['Data_Noti'] = $this->Base_GetList($xParams);
            }
        }

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
                'Fnc_WidgetsCommon' => $this->WidgetsCommon,
                'Fnc_DataDate'      => $this->DataDate,
                /*=========== Datos Consultados ===========*/
                'rowData'       => $rowData,
                'MainViewData'  => $MainViewData,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Usuarios-View.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //Edit
    public function GetID($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => 'idUsuario,idEntidad,idTipoUsuario,idEstado,email,Nombre,Rut,Fono',
            'table'   => 'terceros_entidades_listado_usuarios',
            'join'    => '',
            'where'   => 'idUsuario = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
            'data'    => 'idTipoUsuario AS ID,Nombre',
            'table'   => 'core_tipos_usuario',
            'join'    => '',
            'where'   => 'idTipoUsuario!=1',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams        = ['query' => $query];
        $arrTipoUsuario = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idEstado AS ID,Nombre',
            'table'   => 'core_estados',
            'join'    => '',
            'where'   => 'idEstado!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrEstado = $this->Base_GetList($xParams);

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
                'Fnc_FormInputs'    => $this->FormInputs,
                'Fnc_Codification'  => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'rowData'           => $rowData,
                'arrTipoUsuario'    => $arrTipoUsuario,
                'arrEstado'         => $arrEstado,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Usuarios-formEdit.php');
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
    //Crear
    public function Insert(){

        /******************************/
        //Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /******************************/
        //Se genera la query
        $query = [
            'data'      => 'idEntidad,password,idTipoUsuario,idEstado,email,Nombre,Rut,Fono,Direccion_img,Ultimo_acceso,IP_Client,Agent_Transp',
            'required'  => 'idEntidad,password,idTipoUsuario,idEstado,email,Nombre',
            'unique'    => 'email,Rut',
            'encode'    => 'password',
            'table'     => 'terceros_entidades_listado_usuarios',
            'Post'      => $_POST
        ];
        //Ejecuto la query
        //Ejecuto la query
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        $Response = $this->Base_insert($xParams);

        /******************************/
        // Se asume que $Response contendrá un array de errores/datos, un ID numérico o algún otro valor.
        if (is_numeric($Response)) {
            // Si es un ID numérico, se envía con código 200 (OK)
            echo Response::sendData(200, $Response);
        } else {
            // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
            // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
            echo Response::sendData(500, $Response);
        }
    }

    /******************************************************************************/
    //Editar por put (solo modificar datos)
    //Editar por post (modificar y subir archivos)
    public function Update(){
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            /******************************/
            //Se genera el chequeo
            $DataCheck = $this->dataCheck($_POST);

            /******************************/
            //Se genera la query
            $query = [
                'data'      => 'idUsuario,idEntidad,password,idTipoUsuario,idEstado,email,Nombre,Rut,Fono,Direccion_img,Ultimo_acceso,IP_Client,Agent_Transp',
                'required'  => 'idEntidad,idTipoUsuario,idEstado,email,Nombre',
                'unique'    => 'email,Rut',
                'encode'    => 'password',
                'table'     => 'terceros_entidades_listado_usuarios',
                'where'     => 'idUsuario',
                'Post'      => $_POST
            ];
            //Ejecuto la query
            $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
            $Response = $this->Base_update($xParams);

            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response===true) {
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
    //Borrar dato y archivos
    public function Delete(){
        //Verificacion metodo PUT
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            //Se parsean los datos
            parse_str(file_get_contents("php://input"),$dataDelete);
            /******************************/
            //Se genera la query
            $query = [
                'files'       => '',
                'table'       => 'terceros_entidades_listado_usuarios',
                'where'       => 'idUsuario',
                'SubCarpeta'  => '',
                'Post'        => $dataDelete
            ];
            //Ejecuto la query
            $xParams  = ['query' => $query];
            $Response = $this->Base_delete($xParams);

            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response===true) {
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
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => 'email',
            'ValidarNumero'             => 'Fono',
            'ValidarEntero'             => '',
            'ValidarRut'                => 'Rut',
            'ValidarPatente'            => '',
            'ValidarFecha'              => '',
            'ValidarHora'               => '',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => 'email,Nombre',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'email,Nombre',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Nombre',
            'ValidarEspaciosVacios'     => 'email',
            'ValidarMayusculas'         => 'email',
            'ValidarCoincidencias'      => '',
            'Post'                      => $POST,
        ];
        //Devuelvo
        return $DataChecking;
    }

}
