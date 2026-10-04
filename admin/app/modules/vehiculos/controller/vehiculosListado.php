<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class vehiculosListado extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataDate;
    private $DataNumbers;
    private $WidgetsCommon;
    private $ServerServer;

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'vehiculosListado';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->DataDate       = new FunctionsDataDate();
		$this->DataNumbers    = new FunctionsDataNumbers();
		$this->WidgetsCommon  = new UIWidgetsCommon();
        $this->ServerServer   = new FunctionsServerServer();
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
                vehiculos_listado.idVehiculo,
                vehiculos_listado.Nombre,
                vehiculos_listado.Marca,
                vehiculos_listado.Modelo,
                core_tipos_vehiculos.Nombre AS Tipo,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor',
            'table'   => 'vehiculos_listado',
            'join'    => '
                LEFT JOIN core_tipos_vehiculos ON core_tipos_vehiculos.idTipo  = vehiculos_listado.idTipo
                LEFT JOIN core_estados         ON core_estados.idEstado        = vehiculos_listado.idEstado',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'vehiculos_listado.idEstado ASC, core_tipos_vehiculos.Nombre ASC, vehiculos_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrList = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idTipo AS ID,Nombre',
            'table'   => 'core_tipos_vehiculos',
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
        $arrTipo = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idTipoCarga AS ID,Nombre',
            'table'   => 'core_tipos_vehiculos_carga',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams      = ['query' => $query];
        // Ejecuto la query
        $arrTipoCarga = $this->Base_GetList($xParams);

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
        if($arrList['status'] && $arrTipo['status'] && $arrTipoCarga['status'] && $arrEstado['status']){

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Listado Vehículos',
                'PageDescription' => 'Listado Vehículos.',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => 'Listado Vehículos',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_Codification'    => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrList'       => $arrList['data'],
                'arrTipo'       => $arrTipo['data'],
                'arrTipoCarga'  => $arrTipoCarga['data'],
                'arrEstado'     => $arrEstado['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrList,$arrTipo,$arrTipoCarga,$arrEstado]);
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
        $WhereData_int     = 'idTipo,idTipoCarga,idEstado';  // Datos búsqueda exacta
        $WhereData_string  = 'Nombre,Marca,Modelo';          // Datos búsqueda relativa
        $WhereData_between = '';                             // Datos búsqueda Between
        $whereInt          = '';                             // Se crea cadena
        $whereParams       = [];                             // Valores bindeados asociados a $whereInt
        /************************************/
        // Se validan las fechas
        $RespDataBetween = $this->searchValidateDates($WhereData_between);
        if($RespDataBetween!=''){
            Response::error($RespDataBetween, 500);
        }
        /************************************/
        // Agrego variable busqueda
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_int, 'vehiculos_listado', 1);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_string, 'vehiculos_listado', 2);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_between, 'vehiculos_listado', 3);
        $whereInt = $r['where']; $whereParams = $r['params'];

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                vehiculos_listado.idVehiculo,
                vehiculos_listado.Nombre,
                vehiculos_listado.Marca,
                vehiculos_listado.Modelo,
                core_tipos_vehiculos.Nombre AS Tipo,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor',
            'table'   => 'vehiculos_listado',
            'join'    => '
                LEFT JOIN core_tipos_vehiculos ON core_tipos_vehiculos.idTipo  = vehiculos_listado.idTipo
                LEFT JOIN core_estados         ON core_estados.idEstado        = vehiculos_listado.idEstado',
            'where'   => $whereInt,
            'params'  => $whereParams,
            'group'   => '',
            'having'  => '',
            'order'   => 'vehiculos_listado.idEstado ASC, core_tipos_vehiculos.Nombre ASC, vehiculos_listado.Nombre ASC',
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
                'TableTitle'      => 'Listado Vehículos',
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
    // Listar
    /*******************************************************************/
    public function export($f3){

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                vehiculos_listado.Nombre,
                vehiculos_listado.Marca,
                vehiculos_listado.Modelo,
                vehiculos_listado.Num_serie,
                vehiculos_listado.AnoFab,
                vehiculos_listado.Patente,
                vehiculos_listado.CapacidadPersonas,
                vehiculos_listado.Capacidad,
                vehiculos_listado.MCubicos,
                vehiculos_listado.Direccion_img,
                vehiculos_listado.Latitud,
                vehiculos_listado.Longitud,
                vehiculos_listado.Velocidad,
                vehiculos_listado.LastUpdateFecha,
                vehiculos_listado.LastUpdateHora,
                core_tipos_vehiculos.Nombre AS Tipo,
                core_tipos_vehiculos_carga.Nombre AS TipoCarga,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor',
            'table'   => 'vehiculos_listado',
            'join'    => '
                LEFT JOIN core_tipos_vehiculos        ON core_tipos_vehiculos.idTipo              = vehiculos_listado.idTipo
                LEFT JOIN core_estados                ON core_estados.idEstado                    = vehiculos_listado.idEstado
                LEFT JOIN core_tipos_vehiculos_carga  ON core_tipos_vehiculos_carga.idTipoCarga   = vehiculos_listado.idTipoCarga',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'vehiculos_listado.idEstado ASC, vehiculos_listado.ApellidoPat ASC, vehiculos_listado.Nombre ASC, vehiculos_listado.RazonSocial ASC',
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
                'PageTitle'       => 'Exportar Vehículos',
                'PageDescription' => 'Exportar Vehículos.',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => 'Exportar Vehículos',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Exportar.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrList]);
            // Despliegue de errores
            $this->showError(1, $f3, $result);
        }
    }

    /*******************************************************************/
    // View
    /*******************************************************************/
    public function View($f3, $params){

        /************************************/
        // Se obtiene el ID
        $VehiculoID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($VehiculoID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                vehiculos_listado.Nombre,
                vehiculos_listado.Marca,
                vehiculos_listado.Modelo,
                vehiculos_listado.Num_serie,
                vehiculos_listado.AnoFab,
                vehiculos_listado.Patente,
                vehiculos_listado.CapacidadPersonas,
                vehiculos_listado.Capacidad,
                vehiculos_listado.MCubicos,
                vehiculos_listado.Direccion_img,
                vehiculos_listado.Latitud,
                vehiculos_listado.Longitud,
                vehiculos_listado.Velocidad,
                vehiculos_listado.LastUpdateFecha,
                vehiculos_listado.LastUpdateHora,
                core_tipos_vehiculos.Nombre AS Tipo,
                core_tipos_vehiculos_carga.Nombre AS TipoCarga,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor',
            'table'   => 'vehiculos_listado',
            'join'    => '
                LEFT JOIN core_tipos_vehiculos        ON core_tipos_vehiculos.idTipo              = vehiculos_listado.idTipo
                LEFT JOIN core_estados                ON core_estados.idEstado                    = vehiculos_listado.idEstado
                LEFT JOIN core_tipos_vehiculos_carga  ON core_tipos_vehiculos_carga.idTipoCarga   = vehiculos_listado.idTipoCarga',
            'where'   => 'vehiculos_listado.idVehiculo = ?',
            'params'  => [$VehiculoID['data']],
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
            'data'    => '
                vehiculos_listado_documentos.idDocumentos,
                vehiculos_listado_documentos.Nombre,
                vehiculos_listado_documentos.FechaCreacion,
                vehiculos_listado_documentos.FechaVencimiento,
                core_tipos_vehiculos_documentos.Nombre AS Tipo',
            'table'   => 'vehiculos_listado_documentos',
            'join'    => 'LEFT JOIN core_tipos_vehiculos_documentos ON core_tipos_vehiculos_documentos.idTipo = vehiculos_listado_documentos.idTipo',
            'where'   => 'vehiculos_listado_documentos.idVehiculo = ?',
            'params'  => [$VehiculoID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'core_tipos_vehiculos_documentos.Nombre ASC, vehiculos_listado_documentos.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams       = ['query' => $query];
        // Ejecuto la query
        $arrDocumentos = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                vehiculos_listado_observaciones.Observacion,
                vehiculos_listado_observaciones.FechaCreacion,
                usuarios_listado.Nombre AS Usuario',
            'table'   => 'vehiculos_listado_observaciones',
            'join'    => 'LEFT JOIN usuarios_listado ON usuarios_listado.idUsuario = vehiculos_listado_observaciones.idUsuario',
            'where'   => 'vehiculos_listado_observaciones.idVehiculo = ?',
            'params'  => [$VehiculoID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'vehiculos_listado_observaciones.idObservaciones ASC',
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
                'Fnc_DataNumbers'      => $this->DataNumbers,
                'Fnc_Codification'     => $this->Codification,
                'Fnc_WidgetsMaps'      => new UIWidgetsMaps(),
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
        $VehiculoID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($VehiculoID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                vehiculos_listado.idVehiculo,
                vehiculos_listado.Nombre,
                vehiculos_listado.Marca,
                vehiculos_listado.Modelo,
                vehiculos_listado.Num_serie,
                vehiculos_listado.AnoFab,
                vehiculos_listado.Patente,
                vehiculos_listado.CapacidadPersonas,
                vehiculos_listado.Capacidad,
                vehiculos_listado.MCubicos,
                vehiculos_listado.Direccion_img,
                vehiculos_listado.Latitud,
                vehiculos_listado.Longitud,
                vehiculos_listado.Velocidad,
                vehiculos_listado.LastUpdateFecha,
                vehiculos_listado.LastUpdateHora,
                vehiculos_listado.idTipo,
                vehiculos_listado.idEstado,
                vehiculos_listado.idTipoCarga,
                core_tipos_vehiculos.Nombre AS Tipo,
                core_tipos_vehiculos_carga.Nombre AS TipoCarga,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor',
            'table'   => 'vehiculos_listado',
            'join'    => '
                LEFT JOIN core_tipos_vehiculos        ON core_tipos_vehiculos.idTipo              = vehiculos_listado.idTipo
                LEFT JOIN core_estados                ON core_estados.idEstado                    = vehiculos_listado.idEstado
                LEFT JOIN core_tipos_vehiculos_carga  ON core_tipos_vehiculos_carga.idTipoCarga   = vehiculos_listado.idTipoCarga',
            'where'   => 'vehiculos_listado.idVehiculo = ?',
            'params'  => [$VehiculoID['data']],
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
            'data'    => 'idTipo AS ID,Nombre',
            'table'   => 'core_tipos_vehiculos',
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
        $arrTipo = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idTipoCarga AS ID,Nombre',
            'table'   => 'core_tipos_vehiculos_carga',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams      = ['query' => $query];
        // Ejecuto la query
        $arrTipoCarga = $this->Base_GetList($xParams);

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
        if($rowData['status'] && $arrTipo['status'] && $arrTipoCarga['status'] && $arrEstado['status']){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'        => 'Resumen Vehículos',
                'PageDescription'  => 'Resumen Vehículos.',
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
                'Fnc_DataNumbers'      => $this->DataNumbers,
                'Fnc_ServerServer'     => $this->ServerServer,
                'Fnc_WidgetsMaps'      => new UIWidgetsMaps(),
                /*=========== Datos Consultados ===========*/
                'rowData'       => $rowData['data'],
                'arrTipo'       => $arrTipo['data'],
                'arrTipoCarga'  => $arrTipoCarga['data'],
                'arrEstado'     => $arrEstado['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrTipo,$arrTipoCarga,$arrEstado]);
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
        $VehiculoID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($VehiculoID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                vehiculos_listado.Nombre,
                vehiculos_listado.Marca,
                vehiculos_listado.Modelo,
                vehiculos_listado.Num_serie,
                vehiculos_listado.AnoFab,
                vehiculos_listado.Patente,
                vehiculos_listado.CapacidadPersonas,
                vehiculos_listado.Capacidad,
                vehiculos_listado.MCubicos,
                vehiculos_listado.Direccion_img,
                vehiculos_listado.Latitud,
                vehiculos_listado.Longitud,
                vehiculos_listado.Velocidad,
                vehiculos_listado.LastUpdateFecha,
                vehiculos_listado.LastUpdateHora,
                core_tipos_vehiculos.Nombre AS Tipo,
                core_tipos_vehiculos_carga.Nombre AS TipoCarga,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor',
            'table'   => 'vehiculos_listado',
            'join'    => '
                LEFT JOIN core_tipos_vehiculos        ON core_tipos_vehiculos.idTipo              = vehiculos_listado.idTipo
                LEFT JOIN core_estados                ON core_estados.idEstado                    = vehiculos_listado.idEstado
                LEFT JOIN core_tipos_vehiculos_carga  ON core_tipos_vehiculos_carga.idTipoCarga   = vehiculos_listado.idTipoCarga',
            'where'   => 'vehiculos_listado.idVehiculo = ?',
            'params'  => [$VehiculoID['data']],
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
                'Fnc_DataNumbers'      => $this->DataNumbers,
                'Fnc_WidgetsMaps'      => new UIWidgetsMaps(),
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
            'data'      => 'idEstado,idTipo,Nombre,Marca,Modelo,Num_serie,AnoFab,Patente,CapacidadPersonas,Capacidad,MCubicos,idTipoCarga,Latitud,Longitud,Velocidad,LastUpdateFecha,LastUpdateHora',
            'required'  => 'idEstado,idTipo,Nombre',
            'unique'    => 'Nombre,Num_serie,Patente',
            'encode'    => '',
            'table'     => 'vehiculos_listado',
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
            'data'      => 'idVehiculo,idEstado,idTipo,Nombre,Marca,Modelo,Num_serie,AnoFab,Patente,CapacidadPersonas,Capacidad,MCubicos,idTipoCarga,Latitud,Longitud,Velocidad,LastUpdateFecha,LastUpdateHora',
            'required'  => 'idVehiculo,idEstado,idTipo,Nombre',
            'unique'    => 'Nombre,Num_serie,Patente',
            'encode'    => '',
            'table'     => 'vehiculos_listado',
            'where'     => 'idVehiculo',
            'Post'      => $_POST,
            'files'     => [
                [
                    'Identificador' => 'Direccion_img',
                    'SubCarpeta'    => '',
                    'NombreArchivo' => '',
                    'SufijoArchivo' => 'VehiculoIMG_',
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
            'table'       => 'vehiculos_listado',
            'where'       => 'idVehiculo',
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
        $arrTableDel[] = ['files' => 'NombreArchivo', 'table' => 'vehiculos_listado_documentos'];
        $arrTableDel[] = ['files' => '',              'table' => 'vehiculos_listado_observaciones'];

        /************************************/
        // Verifico si existe
        if (!empty($arrTableDel)) {
            // Recorro
            foreach ($arrTableDel as $tblDel) {
                /************************************/
                // Se genera la query
                $query = ['files' => $tblDel['files'], 'table' => $tblDel['table'], 'where' => 'idVehiculo', 'SubCarpeta' => '', 'Post' => $dataDelete];
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
            'table'       => 'vehiculos_listado',
            'where'       => 'idVehiculo',
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
            'ValidarNumero'             => 'idVehiculo,idEstado,idTipo,idTipoCarga,AnoFab,CapacidadPersonas,Capacidad,MCubicos',
            'ValidarEntero'             => 'idVehiculo,idEstado,idTipo,idTipoCarga,AnoFab,CapacidadPersonas,Capacidad,MCubicos',
            'ValidarRut'                => '',
            'ValidarPatente'            => 'Patente',
            'ValidarFecha'              => '',
            'ValidarHora'               => '',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => 'Nombre,Marca,Modelo,Num_serie,Patente',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'Nombre,Marca,Modelo,Num_serie,Patente',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Nombre,Marca,Modelo,Num_serie',
            'ValidarEspaciosVacios'     => 'Num_serie,Patente',
            'ValidarMayusculas'         => 'Num_serie,Patente',
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
