<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class maquinasListado extends ControllerBase {

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
        $this->controllerName = 'maquinasListado';
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
    //Listar Todo
    public function listAll($f3){
        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => '
                maquinas_listado.idMaquina,
                maquinas_listado.Nombre,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor',
            'table'   => 'maquinas_listado',
            'join'    => 'LEFT JOIN core_estados ON core_estados.idEstado = maquinas_listado.idEstado',
            'where'   => 'maquinas_listado.idMaquina!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'maquinas_listado.idEstado ASC, maquinas_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $arrList = $this->Base_GetList($xParams);

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
        if($arrList['status'] && $arrEstado['status']){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Listado Maquinas',
                'PageDescription' => 'Listado Maquinas.',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => 'Listado de Maquinas',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_Codification'    => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList['data'],
                'arrEstado'       => $arrEstado['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$arrList,$arrEstado]);
            //Muestra los errores
            $this->showError(1, $f3, $result);
        }
    }

    /******************************************************************************/
    //List
    public function UpdateList($f3){
        /*******************************************************************/
        //Variables
        $WhereData_int     = 'idEstado';  //Datos búsqueda exacta
        $WhereData_string  = 'Nombre';    //Datos búsqueda relativa
        $WhereData_between = '';          //Datos búsqueda Between
        $whereInt          = '';          //se crea cadena
        /******************************************/
        //agrego variable busqueda
        $whereInt = $this->searchWhere($whereInt, $WhereData_int, 'maquinas_listado', 1);
        $whereInt = $this->searchWhere($whereInt, $WhereData_string, 'maquinas_listado', 2);
        $whereInt = $this->searchWhere($whereInt, $WhereData_between, 'maquinas_listado', 3);
        //Verifico si esta vacio
        $whereInt2 = $whereInt ? $whereInt . ' AND maquinas_listado.idMaquina!=0' : 'maquinas_listado.idMaquina!=0';

        /******************************/
        //Se genera la query
        $query = [
            'data'    => '
                maquinas_listado.idMaquina,
                maquinas_listado.Nombre,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor',
            'table'   => 'maquinas_listado',
            'join'    => 'LEFT JOIN core_estados ON core_estados.idEstado = maquinas_listado.idEstado',
            'where'   => $whereInt2,
            'group'   => '',
            'having'  => '',
            'order'   => 'maquinas_listado.idEstado ASC, maquinas_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $arrList = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if($arrList['status']){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'TableTitle'      => 'Listado de Maquinas',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'    => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-UpdateList.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$arrList]);
            //Muestra los errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    //View
    public function View($f3, $params){
        /******************************************/
        //Se instancia
        $arrUserData = $this->getUserData($f3);

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => '
                maquinas_listado.idMaquina,
                maquinas_listado.Nombre,
                maquinas_listado.CodIdentificador,
                maquinas_listado.Descripcion,
                maquinas_listado.Direccion_img,
                maquinas_listado.Sim_Num_Tel,
                maquinas_listado.Sim_Compania,
                maquinas_listado.TiempoFueraLinea,
                maquinas_listado.idTab,
                maquinas_listado.id_Geo,
                maquinas_listado.id_Sensores,
                maquinas_listado.idBackup,
                maquinas_listado.NregBackup,
                maquinas_listado.idAlertaTemprana,
                maquinas_listado.AlertaTemprCritica,
                maquinas_listado.AlertaTemprNormal,

                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor,
                core_telemetria_tabs.Nombre AS Tabs,
                Ops_1.Nombre AS UsoGeo,
                Ops_2.Nombre AS UsoSensores,
                Ops_3.Nombre AS UsoBackup,
                Ops_4.Nombre AS UsoAlertaTemprana',
            'table'   => 'maquinas_listado',
            'join'    => '
                LEFT JOIN core_estados           ON core_estados.idEstado        = maquinas_listado.idEstado
                LEFT JOIN core_telemetria_tabs   ON core_telemetria_tabs.idTab   = maquinas_listado.idTab
                LEFT JOIN core_opciones Ops_1    ON Ops_1.idOpciones             = maquinas_listado.id_Geo
                LEFT JOIN core_opciones Ops_2    ON Ops_2.idOpciones             = maquinas_listado.id_Sensores
                LEFT JOIN core_opciones Ops_3    ON Ops_3.idOpciones             = maquinas_listado.idBackup
                LEFT JOIN core_opciones Ops_4    ON Ops_4.idOpciones             = maquinas_listado.idAlertaTemprana',
            'where'   => 'maquinas_listado.idMaquina = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        // Se verifica si se tiene el permiso para visualizar el dato
        if($arrUserData["maquinasListadoVerDocumentos"]==2){
            //Se genera la query
            $query = [
                'data'    => 'Nombre,NombreArchivo,FVencimiento',
                'table'   => 'maquinas_listado_documentos',
                'join'    => '',
                'where'   => 'idMaquina = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
                'group'   => '',
                'having'  => '',
                'order'   => 'Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            //Ejecuto la query
            $xParams       = ['query' => $query];
            $arrDocumentos = $this->Base_GetList($xParams);
        //Si se permite junto con la creacion de tareas
        }else{
            $arrDocumentos['status'] = true;
            $arrDocumentos['data']   = [];
        }

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'FechaCreacion,Observacion',
            'table'   => 'maquinas_listado_observaciones',
            'join'    => '',
            'where'   => 'idMaquina = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'idObservaciones ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams          = ['query' => $query];
        $arrObservaciones = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if($rowData['status'] && $arrDocumentos['status'] && $arrObservaciones['status']){
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
                'arrDocumentos'    => $arrDocumentos['data'],
                'arrObservaciones' => $arrObservaciones['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-View.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrDocumentos,$arrObservaciones]);
            //Muestra los errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    //Resumen
    public function Resumen($f3, $params){
        /******************************************/
        //Se genera la query
        $query = [
            'data'    => '
                maquinas_listado.idMaquina,
                maquinas_listado.idEstado,
                maquinas_listado.Nombre,
                maquinas_listado.CodIdentificador,
                maquinas_listado.Descripcion,
                maquinas_listado.Direccion_img,
                maquinas_listado.Sim_Num_Tel,
                maquinas_listado.Sim_Compania,
                maquinas_listado.TiempoFueraLinea,
                maquinas_listado.idTab,
                maquinas_listado.id_Geo,
                maquinas_listado.id_Sensores,
                maquinas_listado.idBackup,
                maquinas_listado.NregBackup,
                maquinas_listado.idAlertaTemprana,
                maquinas_listado.AlertaTemprCritica,
                maquinas_listado.AlertaTemprNormal,

                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor,
                core_telemetria_tabs.Nombre AS Tabs,
                Ops_1.Nombre AS UsoGeo,
                Ops_2.Nombre AS UsoSensores,
                Ops_3.Nombre AS UsoBackup,
                Ops_4.Nombre AS UsoAlertaTemprana',
            'table'   => 'maquinas_listado',
            'join'    => '
                LEFT JOIN core_estados           ON core_estados.idEstado        = maquinas_listado.idEstado
                LEFT JOIN core_telemetria_tabs   ON core_telemetria_tabs.idTab   = maquinas_listado.idTab
                LEFT JOIN core_opciones Ops_1    ON Ops_1.idOpciones             = maquinas_listado.id_Geo
                LEFT JOIN core_opciones Ops_2    ON Ops_2.idOpciones             = maquinas_listado.id_Sensores
                LEFT JOIN core_opciones Ops_3    ON Ops_3.idOpciones             = maquinas_listado.idBackup
                LEFT JOIN core_opciones Ops_4    ON Ops_4.idOpciones             = maquinas_listado.idAlertaTemprana',
            'where'   => 'maquinas_listado.idMaquina = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

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
        //Se genera la query
        $query = [
            'data'    => 'idOpciones AS ID,Nombre',
            'table'   => 'core_opciones',
            'join'    => '',
            'where'   => 'idOpciones!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams     = ['query' => $query];
        $arrOpciones = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idTab AS ID,Nombre',
            'table'   => 'core_telemetria_tabs',
            'join'    => '',
            'where'   => 'idTab!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $arrTabs = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if($rowData['status'] && $arrEstado['status'] && $arrOpciones['status'] && $arrTabs['status']){
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'        => 'Resumen Maquinas',
                'PageDescription'  => 'Resumen Maquinas.',
                'PageAuthor'       => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'     => ConfigAPP::SOFTWARE['SoftwareName'],
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'       => $this->FormInputs,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_Codification'     => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData['data'],
                'arrEstado'       => $arrEstado['data'],
                'arrOpciones'     => $arrOpciones['data'],
                'arrTabs'         => $arrTabs['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrEstado,$arrOpciones,$arrTabs]);
            //Muestra los errores
            $this->showError(1, $f3, $result);
        }
    }

    /******************************************************************************/
    //Resumen-Update
    public function ResumenUpdate($f3, $params){
        /******************************************/
        //Se genera la query
        $query = [
            'data'    => '
                maquinas_listado.idMaquina,
                maquinas_listado.Nombre,
                maquinas_listado.CodIdentificador,
                maquinas_listado.Descripcion,
                maquinas_listado.Direccion_img,
                maquinas_listado.Sim_Num_Tel,
                maquinas_listado.Sim_Compania,
                maquinas_listado.TiempoFueraLinea,
                maquinas_listado.idTab,
                maquinas_listado.id_Geo,
                maquinas_listado.id_Sensores,
                maquinas_listado.idBackup,
                maquinas_listado.NregBackup,
                maquinas_listado.idAlertaTemprana,
                maquinas_listado.AlertaTemprCritica,
                maquinas_listado.AlertaTemprNormal,

                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor,
                core_telemetria_tabs.Nombre AS Tabs,
                Ops_1.Nombre AS UsoGeo,
                Ops_2.Nombre AS UsoSensores,
                Ops_3.Nombre AS UsoBackup,
                Ops_4.Nombre AS UsoAlertaTemprana',
            'table'   => 'maquinas_listado',
            'join'    => '
                LEFT JOIN core_estados           ON core_estados.idEstado        = maquinas_listado.idEstado
                LEFT JOIN core_telemetria_tabs   ON core_telemetria_tabs.idTab   = maquinas_listado.idTab
                LEFT JOIN core_opciones Ops_1    ON Ops_1.idOpciones             = maquinas_listado.id_Geo
                LEFT JOIN core_opciones Ops_2    ON Ops_2.idOpciones             = maquinas_listado.id_Sensores
                LEFT JOIN core_opciones Ops_3    ON Ops_3.idOpciones             = maquinas_listado.idBackup
                LEFT JOIN core_opciones Ops_4    ON Ops_4.idOpciones             = maquinas_listado.idAlertaTemprana',
            'where'   => 'maquinas_listado.idMaquina = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if($rowData['status']){
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Update.php');
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
    //Crear
    public function Insert(){

        /******************************/
        //Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /******************************/
        //Se genera la query
        $query = [
            'data'      => 'idEstado,Nombre,CodIdentificador,Descripcion,Sim_Num_Tel,Sim_Compania,TiempoFueraLinea,idTab,id_Geo,id_Sensores,idBackup,NregBackup,idAlertaTemprana,AlertaTemprCritica,AlertaTemprNormal',
            'required'  => 'idEstado,Nombre',
            'unique'    => 'Nombre,CodIdentificador',
            'encode'    => '',
            'table'     => 'maquinas_listado',
            'Post'      => $_POST
        ];
        //Ejecuto la query
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        $Response = $this->Base_insert($xParams);

        /******************************/
        // Se asume que $Response contendrá un array de errores/datos, un ID numérico o algún otro valor.
        if ($Response['status']){
            // Si es un ID numérico, encripta y envía con código 200 (OK)
            $Data = $this->Codification->encryptDecrypt('encrypt', $Response['data']);
            Response::success($Data);
        } else {
            // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
            // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
            Response::error('Error al operar con la Base de Datos', 500, $Response['error']);
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
                'data'      => 'idMaquina,idEstado,Nombre,CodIdentificador,Descripcion,Sim_Num_Tel,Sim_Compania,TiempoFueraLinea,idTab,id_Geo,id_Sensores,idBackup,NregBackup,idAlertaTemprana,AlertaTemprCritica,AlertaTemprNormal',
                'required'  => 'idEstado,Nombre',
                'unique'    => 'Nombre,CodIdentificador',
                'encode'    => '',
                'table'     => 'maquinas_listado',
                'where'     => 'idMaquina',
                'Post'      => $_POST,
                'files'     => [
                    [
                        'Identificador' => 'Direccion_img',
                        'SubCarpeta'    => '',
                        'NombreArchivo' => '',
                        'SufijoArchivo' => 'MaquinasIMG_',
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
            if ($Response['status']){
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
    //Borrar dato y archivos
    public function Delete(){
        //Verificacion metodo PUT
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            //Se parsean los datos
            parse_str(file_get_contents("php://input"),$dataDelete);
            /******************************/
            //Se genera la query
            $query = [
                'files'       => 'Direccion_img',
                'table'       => 'maquinas_listado',
                'where'       => 'idMaquina',
                'SubCarpeta'  => '',
                'Post'        => $dataDelete
            ];
            //Ejecuto la query
            $xParams  = ['query' => $query];
            $Response = $this->Base_delete($xParams);
            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response['status']){
                /************************************************/
                //Listado de las tablas a eliminar los datos relacionados
                $arrTableDel  = array();
                $arrTableDel[] = ['files' => 'NombreArchivo', 'table' => 'maquinas_listado_documentos'];
                $arrTableDel[] = ['files' => '',              'table' => 'maquinas_listado_observaciones'];

                /************************************************/
                //Verifico si existe
                if($arrTableDel){
                    //recorro
                    foreach ($arrTableDel as $tblDel) {
                        //Se genera la query
                        $query = ['files' => $tblDel['files'], 'table' => $tblDel['table'], 'where' => 'idMaquina', 'SubCarpeta' => '', 'Post' => $dataDelete];
                        //Ejecuto la query
                        $xParams = ['query' => $query];
                        $this->Base_delete($xParams);
                    }
                }

                /******************************/
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
    public function delFiles(){
        //Verificacion metodo PUT
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            //Se parsean los datos
            parse_str(file_get_contents("php://input"),$dataPut);
            /******************************/
            //Se genera la query
            $query = [
                'files'       => 'Direccion_img',
                'table'       => 'maquinas_listado',
                'where'       => 'idMaquina',
                'SubCarpeta'  => '',
                'Post'        => $dataPut
            ];
            //Ejecuto la query
            $xParams  = ['query' => $query];
            $Response = $this->Base_delFiles($xParams);
            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response['status']){
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
        //Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => '',
            'ValidarNumero'             => 'idEstado,Sim_Num_Tel,idTab,id_Geo,id_Sensores,idBackup,NregBackup,idAlertaTemprana',
            'ValidarEntero'             => 'idEstado,idTab,id_Geo,id_Sensores,idBackup,NregBackup,idAlertaTemprana',
            'ValidarRut'                => '',
            'ValidarPatente'            => '',
            'ValidarFecha'              => '',
            'ValidarHora'               => 'TiempoFueraLinea,AlertaTemprCritica,AlertaTemprNormal',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => 'Nombre,CodIdentificador,Descripcion,Sim_Compania',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'Nombre,CodIdentificador,Sim_Compania',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Nombre,CodIdentificador,Descripcion,Sim_Compania',
            'ValidarEspaciosVacios'     => '',
            'ValidarMayusculas'         => '',
            'ValidarCoincidencias'      => '',
            'ValidarDominioEmail'       => '',
            'ValidarPasswordSegura'     => '',
            'ValidarFechaRango'         => '',
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
