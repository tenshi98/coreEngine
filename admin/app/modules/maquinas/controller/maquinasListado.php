<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class maquinasListado extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataDate;
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
    /*******************************************************************/
    // Listar
    /*******************************************************************/
    public function listAll($f3){

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                maquinas_listado.idMaquina,
                maquinas_listado.Nombre,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor',
            'table'   => 'maquinas_listado',
            'join'    => 'LEFT JOIN core_estados ON core_estados.idEstado = maquinas_listado.idEstado',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'maquinas_listado.idEstado ASC, maquinas_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrList = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idEstado AS ID,Nombre',
            'table'   => 'core_estados',
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
        $arrEstado = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($arrList['status'] && $arrEstado['status']){

            /************************************/
            // Datos enviados a la pagina
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

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrList,$arrEstado]);
            // Despliegue de errores
            $this->showError(1, $f3, $result);
        }
    }

    /*******************************************************************/
    // Actualizar Listar
    /*******************************************************************/
    public function UpdateList($f3){

        /************************************/
        // Variables
        $WhereData_int     = 'idEstado';  // Datos búsqueda exacta
        $WhereData_string  = 'Nombre';    // Datos búsqueda relativa
        $WhereData_between = '';          // Datos búsqueda Between
        $whereInt          = '';          // Se crea cadena
        $whereParams       = [];          // Valores bindeados asociados a $whereInt
        /************************************/
        // Se validan las fechas
        $RespDataBetween = $this->searchValidateDates($WhereData_between);
        if($RespDataBetween!=''){
            Response::error($RespDataBetween, 500);
        }
        /************************************/
        // Agrego variable busqueda
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_int, 'maquinas_listado', 1);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_string, 'maquinas_listado', 2);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_between, 'maquinas_listado', 3);
        $whereInt = $r['where']; $whereParams = $r['params'];

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                maquinas_listado.idMaquina,
                maquinas_listado.Nombre,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor',
            'table'   => 'maquinas_listado',
            'join'    => 'LEFT JOIN core_estados ON core_estados.idEstado = maquinas_listado.idEstado',
            'where'   => $whereInt,
            'params'  => $whereParams,
            'group'   => '',
            'having'  => '',
            'order'   => 'maquinas_listado.idEstado ASC, maquinas_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrList = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrList['status'] === true) {

            /************************************/
            // Datos enviados a la pagina
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

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-UpdateList.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrList]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // View
    /*******************************************************************/
    public function View($f3, $params){

        /************************************/
        // Se instancia
        $arrUserData = $this->getUserData($f3);

        /************************************/
        // Se obtiene el ID
        $MaquinaID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($MaquinaID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
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
            'where'   => 'maquinas_listado.idMaquina = ?',
            'params'  => [$MaquinaID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /************************************/
        // Se verifica si se tiene el permiso para visualizar el dato
        if($arrUserData["maquinasListadoVerDocumentos"]==2){
            /************************************/
            // Se genera la query
            $query = [
                'data'    => 'idDocumentos,Nombre,NombreArchivo,FVencimiento',
                'table'   => 'maquinas_listado_documentos',
                'join'    => '',
                'where'   => 'idMaquina = ?',
                'params'  => [$MaquinaID['data']],
                'group'   => '',
                'having'  => '',
                'order'   => 'Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            // Preparo los datos
            $xParams       = ['query' => $query];
            // Ejecuto la query
            $arrDocumentos = $this->Base_GetList($xParams);
        // Si se permite junto con la creacion de tareas
        }else{
            $arrDocumentos['status'] = true;
            $arrDocumentos['data']   = [];
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                maquinas_listado_observaciones.Observacion,
                maquinas_listado_observaciones.FechaCreacion,
                usuarios_listado.Nombre AS Usuario',
            'table'   => 'maquinas_listado_observaciones',
            'join'    => 'LEFT JOIN usuarios_listado ON usuarios_listado.idUsuario = maquinas_listado_observaciones.idUsuario',
            'where'   => 'maquinas_listado_observaciones.idMaquina = ?',
            'params'  => [$MaquinaID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'maquinas_listado_observaciones.idObservaciones ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams          = ['query' => $query];
        // Ejecuto la query
        $arrObservaciones = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrDocumentos['status'] && $arrObservaciones['status']){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_Codification'     => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
                'arrDocumentos'    => $arrDocumentos['data'],
                'arrObservaciones' => $arrObservaciones['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-View.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrDocumentos,$arrObservaciones]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Resumen
    /*******************************************************************/
    public function Resumen($f3, $params){

        /************************************/
        // Se obtiene el ID
        $MaquinaID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($MaquinaID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
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
            'where'   => 'maquinas_listado.idMaquina = ?',
            'params'  => [$MaquinaID['data']],
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
            'data'    => 'idEstado AS ID,Nombre',
            'table'   => 'core_estados',
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
        $arrEstado = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idOpciones AS ID,Nombre',
            'table'   => 'core_opciones',
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
        $arrOpciones = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idTab AS ID,Nombre',
            'table'   => 'core_telemetria_tabs',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrTabs = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrEstado['status'] && $arrOpciones['status'] && $arrTabs['status']){
            /************************************/
            // Datos enviados a la pagina
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

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrEstado,$arrOpciones,$arrTabs]);
            // Despliegue de errores
            $this->showError(1, $f3, $result);
        }
    }

    /*******************************************************************/
    // Resumen Actualizar
    /*******************************************************************/
    public function ResumenUpdate($f3, $params){

        /************************************/
        // Se obtiene el ID
        $MaquinaID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($MaquinaID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
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
            'where'   => 'maquinas_listado.idMaquina = ?',
            'params'  => [$MaquinaID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
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
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Update.php');
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
    // Insertar
    /*******************************************************************/
    public function Insert(){

        /************************************/
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idEstado,Nombre,CodIdentificador,Descripcion,Sim_Num_Tel,Sim_Compania,TiempoFueraLinea,idTab,id_Geo,id_Sensores,idBackup,NregBackup,idAlertaTemprana,AlertaTemprCritica,AlertaTemprNormal',
            'required'  => 'idEstado,Nombre',
            'unique'    => 'Nombre,CodIdentificador',
            'encode'    => '',
            'table'     => 'maquinas_listado',
            'Post'      => $_POST
        ];
        // Preparo los datos
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        // Ejecuto la query
        $Response = $this->Base_insert($xParams);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if ($Response['status'] === false) {
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
        }

        /************************************/
        // Si es un ID numérico, encripta y envía con código 200 (OK)
        $DataID = $this->Codification->encryptDecrypt('encrypt', $Response['data']);
        if (!$this->isValidDecrypted($DataID, 'text')) {
            Response::error('Registro inválido', 400);
        }
        Response::success($DataID['data']);

    }

    /*******************************************************************/
    // Editar por put (solo modificar datos)
    // Editar por post (modificar y subir archivos)
    /*******************************************************************/
    public function Update(){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idMaquina,idEstado,Nombre,CodIdentificador,Descripcion,Sim_Num_Tel,Sim_Compania,TiempoFueraLinea,idTab,id_Geo,id_Sensores,idBackup,NregBackup,idAlertaTemprana,AlertaTemprCritica,AlertaTemprNormal',
            'required'  => 'idMaquina,idEstado,Nombre',
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
        // Preparo los datos
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        // Ejecuto la query
        $Response = $this->Base_update($xParams);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if ($Response['status'] === false) {
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
        }

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data']);

    }

    /*******************************************************************/
    // Borrar dato y archivos
    /*******************************************************************/
    public function Delete(){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Obtener datos
        parse_str(file_get_contents("php://input"),$dataDelete);

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Se genera la query
        $query = [
            'files'       => 'Direccion_img',
            'table'       => 'maquinas_listado',
            'where'       => 'idMaquina',
            'SubCarpeta'  => '',
            'Post'        => $dataDelete
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $Response = $this->Base_delete($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            $this->Base_transactionRollback();
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
        }

        /************************************/
        // Listado de las tablas a eliminar los datos relacionados
        $arrTableDel  = array();
        $arrTableDel[] = ['files' => 'NombreArchivo', 'table' => 'maquinas_listado_documentos'];
        $arrTableDel[] = ['files' => '',              'table' => 'maquinas_listado_observaciones'];

        /************************************/
        // Verifico si existe
        if (!empty($arrTableDel)) {
            // Recorro
            foreach ($arrTableDel as $tblDel) {
                /************************************/
                // Se genera la query
                $query = ['files' => $tblDel['files'], 'table' => $tblDel['table'], 'where' => 'idMaquina', 'SubCarpeta' => '', 'Post' => $dataDelete];
                // Preparo los datos
                $xParams    = ['query' => $query];
                // Ejecuto la query
                $respDelRel = $this->Base_delete($xParams);
                /************************************/
                // Si falla la ejecucion, se revierte de inmediato
                if ($respDelRel['status'] === false) {
                    $this->Base_transactionRollback();
                    Response::error('Error al operar con la Base de Datos', 500, $respDelRel['error']);
                }
            }
        }

        /************************************/
        // Se confirma la transacción
        $this->Base_transactionCommit();

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data']);

    }

    /*******************************************************************/
    // Borrar archivos
    /*******************************************************************/
    public function delFiles(){

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
            'table'       => 'maquinas_listado',
            'where'       => 'idMaquina',
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
        // Retorno los datos
        return $DataChecking;
    }

}
