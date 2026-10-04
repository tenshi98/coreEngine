<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class cotizacionListado extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataNumbers;
    private $DataDate;
    private $ServerServer;
    private $CommonData;
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
        $this->controllerName = 'cotizacionListado';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->DataNumbers    = new FunctionsDataNumbers();
		$this->DataDate       = new FunctionsDataDate();
		$this->ServerServer   = new FunctionsServerServer();
		$this->CommonData     = new FunctionsCommonData();
		$this->WidgetsCommon  = new UIWidgetsCommon();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //imprimir
    public function Print($f3, $params){$this->Printer($f3, $params, 1);}
    //ver documento
    public function noPrint($f3, $params){$this->Printer($f3, $params, 0);}

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
                cotizacion_listado.idCotizacion,
                cotizacion_listado.Creacion_fecha,
                cotizacion_listado.ValorTotal,

                entidades_listado.idTipoEntidad,
                entidades_listado.Nombre AS EntidadesNombre,
                entidades_listado.ApellidoPat AS EntidadesApellido,
                entidades_listado.RazonSocial AS EntidadesRazonSocial,
                entidades_listado.Nick AS EntidadesNick',
            'table'   => 'cotizacion_listado',
            'join'    => 'LEFT JOIN entidades_listado ON entidades_listado.idEntidad = cotizacion_listado.idEntidad',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'cotizacion_listado.Creacion_fecha DESC, cotizacion_listado.idCotizacion DESC, entidades_listado.ApellidoPat ASC, entidades_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrList = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idEntidad AS ID,CONCAT(CASE idTipoEntidad WHEN 1 THEN CONCAT_WS(" ", Nombre, ApellidoPat) WHEN 2 THEN RazonSocial END,IF(Nick IS NULL OR Nick = "","",CONCAT(" (", Nick, ")"))) AS Nombre',
            'table'   => 'entidades_listado',
            'join'    => '',
            'where'   => 'idEstado = ? AND idTipo = ?',
            'params'  => [1, 2],
            'group'   => '',
            'having'  => '',
            'order'   => 'ApellidoPat ASC,Nombre ASC,RazonSocial ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams      = ['query' => $query];
        // Ejecuto la query
        $arrEntidades = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idProducto AS ID,Nombre',
            'table'   => 'productos_listado',
            'join'    => '',
            'where'   => 'idEstado = ?',
            'params'  => [1],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams      = ['query' => $query];
        // Ejecuto la query
        $arrProductos = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idServicio AS ID,Nombre',
            'table'   => 'servicios_listado',
            'join'    => '',
            'where'   => 'idEstado = ?',
            'params'  => [1],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams      = ['query' => $query];
        // Ejecuto la query
        $arrServicios = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($arrList['status'] && $arrEntidades['status'] && $arrProductos['status'] && $arrServicios['status']){

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Listado Cotizaciones',
                'PageDescription' => 'Listado Cotizaciones',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => 'Listado Cotizaciones',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_Codification'    => $this->Codification,
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                'Fnc_ServerServer'    => $this->ServerServer,
                'Fnc_CommonData'      => $this->CommonData,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList['data'],
                'arrEntidades'    => $arrEntidades['data'],
                'arrProductos'    => $arrProductos['data'],
                'arrServicios'    => $arrServicios['data'],

            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrList,$arrEntidades,$arrProductos,$arrServicios]);
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
        $WhereData_int     = 'idEntidad,idCotizacion';                   // Datos búsqueda exacta
        $WhereData_string  = '';                                         // Datos búsqueda relativa
        $WhereData_between = 'Creacion_fecha-F_Inicio-F_Termino';        // Datos búsqueda Between
        $whereInt          = '';                                         // Se crea cadena
        $whereParams       = [];                                         // Valores bindeados asociados a $whereInt
        /************************************/
        // Se validan las fechas
        $RespDataBetween = $this->searchValidateDates($WhereData_between);
        if($RespDataBetween!=''){
            Response::error($RespDataBetween, 500);
        }
        /************************************/
        // Agrego variable busqueda
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_int, 'cotizacion_listado', 1);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_string, 'cotizacion_listado', 2);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_between, 'cotizacion_listado', 3);
        $whereInt = $r['where']; $whereParams = $r['params'];

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                cotizacion_listado.idCotizacion,
                cotizacion_listado.Creacion_fecha,
                cotizacion_listado.ValorTotal,

                entidades_listado.idTipoEntidad,
                entidades_listado.Nombre AS EntidadesNombre,
                entidades_listado.ApellidoPat AS EntidadesApellido,
                entidades_listado.RazonSocial AS EntidadesRazonSocial,
                entidades_listado.Nick AS EntidadesNick',
            'table'   => 'cotizacion_listado',
            'join'    => 'LEFT JOIN entidades_listado ON entidades_listado.idEntidad = cotizacion_listado.idEntidad',
            'where'   => $whereInt,
            'params'  => $whereParams,
            'group'   => '',
            'having'  => '',
            'order'   => 'cotizacion_listado.Creacion_fecha DESC, cotizacion_listado.idCotizacion DESC, entidades_listado.ApellidoPat ASC, entidades_listado.Nombre ASC',
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
                'TableTitle'      => 'Listado Cotizaciones',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'     => $this->Codification,
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'arrList'  => $arrList['data'],
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
        // Se obtiene el ID
        $CotizacionID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($CotizacionID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                cotizacion_listado.idCotizacion,
                cotizacion_listado.Creacion_fecha,
                cotizacion_listado.Creacion_hora,
                cotizacion_listado.Observaciones,
                cotizacion_listado.ValorNeto,
                cotizacion_listado.IVA,
                cotizacion_listado.ValorTotal,

                entidades_listado.idTipoEntidad,
                entidades_listado.Nombre AS EntidadesNombre,
                entidades_listado.ApellidoPat AS EntidadesApellido,
                entidades_listado.RazonSocial AS EntidadesRazonSocial,
                entidades_listado.Nick AS EntidadesNick,
                usuarios_listado.Nombre AS Usuario',
            'table'   => 'cotizacion_listado',
            'join'    => '
                LEFT JOIN entidades_listado  ON entidades_listado.idEntidad  = cotizacion_listado.idEntidad
                LEFT JOIN usuarios_listado   ON usuarios_listado.idUsuario   = cotizacion_listado.idUsuario',
            'where'   => 'cotizacion_listado.idCotizacion = ?',
            'params'  => [$CotizacionID['data']],
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
            'data'    => 'Item,Number,ValorTotal',
            'table'   => 'cotizacion_listado_items',
            'join'    => '',
            'where'   => 'idCotizacion = ?',
            'params'  => [$CotizacionID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $arrItems = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                productos_listado.Nombre AS ProductoNombre,
                cotizacion_listado_productos.Number AS ProductoCantidad,
                cotizacion_listado_productos.ValorTotal AS ProductoValor,
                core_unidades_medida.Nombre AS UnidadMedida',
            'table'   => 'cotizacion_listado_productos',
            'join'    => '
                LEFT JOIN productos_listado     ON productos_listado.idProducto    = cotizacion_listado_productos.idProducto
                LEFT JOIN core_unidades_medida  ON core_unidades_medida.idUniMed   = productos_listado.idUniMed',
            'where'   => 'cotizacion_listado_productos.idCotizacion = ?',
            'params'  => [$CotizacionID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'cotizacion_listado_productos.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams      = ['query' => $query];
        // Ejecuto la query
        $arrProductos = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                servicios_listado.Nombre AS ServicioNombre,
                cotizacion_listado_servicios.Number AS ServicioCantidad,
                cotizacion_listado_servicios.ValorTotal AS ServicioValor',
            'table'   => 'cotizacion_listado_servicios',
            'join'    => 'LEFT JOIN servicios_listado  ON servicios_listado.idServicio  = cotizacion_listado_servicios.idServicio',
            'where'   => 'cotizacion_listado_servicios.idCotizacion = ?',
            'params'  => [$CotizacionID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'cotizacion_listado_servicios.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams      = ['query' => $query];
        // Ejecuto la query
        $arrServicios = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrItems['status'] && $arrProductos['status'] && $arrServicios['status']){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'     => $this->Codification,
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
                'arrItems'         => $arrItems['data'],
                'arrProductos'     => $arrProductos['data'],
                'arrServicios'     => $arrServicios['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-View.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrItems,$arrProductos,$arrServicios]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Imprimir
    /*******************************************************************/
    public function Printer($f3, $params, $Imprimir){

        /************************************/
        // Se obtiene el ID
        $CotizacionID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($CotizacionID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                cotizacion_listado.idCotizacion,
                cotizacion_listado.Creacion_fecha,
                cotizacion_listado.Creacion_hora,
                cotizacion_listado.Observaciones,
                cotizacion_listado.ValorNeto,
                cotizacion_listado.IVA,
                cotizacion_listado.ValorTotal,

                entidades_listado.idTipoEntidad,
                entidades_listado.Nombre AS EntidadesNombre,
                entidades_listado.ApellidoPat AS EntidadesApellido,
                entidades_listado.RazonSocial AS EntidadesRazonSocial,
                entidades_listado.Nick AS EntidadesNick,
                core_ubicacion_ciudad.Nombre AS EntidadesCiudad,
                core_ubicacion_comunas.Nombre AS EntidadesComuna,
                entidades_listado.Direccion AS EntidadesDireccion,
                entidades_listado.Email AS EntidadesEmail,
                entidades_listado.Fono1 AS EntidadesFono1,
                entidades_listado.Fono2 AS EntidadesFono2',
            'table'   => 'cotizacion_listado',
            'join'    => '
                LEFT JOIN entidades_listado       ON entidades_listado.idEntidad      = cotizacion_listado.idEntidad
                LEFT JOIN core_ubicacion_ciudad   ON core_ubicacion_ciudad.idCiudad   = entidades_listado.idCiudad
                LEFT JOIN core_ubicacion_comunas  ON core_ubicacion_comunas.idComuna  = entidades_listado.idComuna',
            'where'   => 'cotizacion_listado.idCotizacion = ?',
            'params'  => [$CotizacionID['data']],
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
                core_sistemas.Sistema_Nombre AS Sistema_Nombre,
                core_sistemas.Sistema_Direccion AS Sistema_Direccion,
                core_sistemas.Sistema_Email AS Sistema_Email,
                core_sistemas.Contacto_Fono1 AS Sistema_Fono1,
                core_sistemas.Contacto_Fono2 AS Sistema_Fono2,
                core_ubicacion_ciudad.Nombre AS Sistema_Ciudad,
                core_ubicacion_comunas.Nombre AS Sistema_Comuna',
            'table'   => 'core_sistemas',
            'join'    => '
                LEFT JOIN core_ubicacion_ciudad   ON core_ubicacion_ciudad.idCiudad    = core_sistemas.Sistema_idCiudad
                LEFT JOIN core_ubicacion_comunas  ON core_ubicacion_comunas.idComuna   = core_sistemas.Sistema_idComuna',
            'where'   => 'core_sistemas.idSistema = ?',
            'params'  => [1],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams    = ['query' => $query];
        // Ejecuto la query
        $rowSistema = $this->Base_GetByID($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'Item,Number,ValorTotal',
            'table'   => 'cotizacion_listado_items',
            'join'    => '',
            'where'   => 'idCotizacion = ?',
            'params'  => [$CotizacionID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $arrItems = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                productos_listado.Nombre AS ProductoNombre,
                cotizacion_listado_productos.Number AS ProductoCantidad,
                cotizacion_listado_productos.ValorTotal AS ProductoValor,
                core_unidades_medida.Nombre AS UnidadMedida',
            'table'   => 'cotizacion_listado_productos',
            'join'    => '
                LEFT JOIN productos_listado     ON productos_listado.idProducto   = cotizacion_listado_productos.idProducto
                LEFT JOIN core_unidades_medida  ON core_unidades_medida.idUniMed  = productos_listado.idUniMed',
            'where'   => 'cotizacion_listado_productos.idCotizacion = ?',
            'params'  => [$CotizacionID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'cotizacion_listado_productos.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams      = ['query' => $query];
        // Ejecuto la query
        $arrProductos = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                servicios_listado.Nombre AS ServicioNombre,
                cotizacion_listado_servicios.Number AS ServicioCantidad,
                cotizacion_listado_servicios.ValorTotal AS ServicioValor',
            'table'   => 'cotizacion_listado_servicios',
            'join'    => 'LEFT JOIN servicios_listado  ON servicios_listado.idServicio  = cotizacion_listado_servicios.idServicio',
            'where'   => 'cotizacion_listado_servicios.idCotizacion = ?',
            'params'  => [$CotizacionID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'cotizacion_listado_servicios.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams      = ['query' => $query];
        // Ejecuto la query
        $arrServicios = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $rowSistema['status'] && $arrItems['status'] && $arrProductos['status'] && $arrServicios['status']){
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
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
                'rowSistema'       => $rowSistema['data'],
                'arrItems'         => $arrItems['data'],
                'arrProductos'     => $arrProductos['data'],
                'arrServicios'     => $arrServicios['data'],
                'Imprimir'         => $Imprimir,
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(4, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Print.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$rowSistema,$arrItems,$arrProductos,$arrServicios]);
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
        $CotizacionID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($CotizacionID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                cotizacion_listado.idCotizacion,
                cotizacion_listado.idEntidad,
                cotizacion_listado.Creacion_fecha,
                cotizacion_listado.Creacion_hora,
                cotizacion_listado.Observaciones,
                cotizacion_listado.ValorNeto,
                cotizacion_listado.IVA,
                cotizacion_listado.ValorTotal,
                cotizacion_listado.fecha_auto,

                entidades_listado.idTipoEntidad,
                entidades_listado.Nombre AS EntidadesNombre,
                entidades_listado.ApellidoPat AS EntidadesApellido,
                entidades_listado.RazonSocial AS EntidadesRazonSocial,
                entidades_listado.Nick AS EntidadesNick,
                usuarios_listado.Nombre AS Usuario',
            'table'   => 'cotizacion_listado',
            'join'    => '
                LEFT JOIN entidades_listado  ON entidades_listado.idEntidad = cotizacion_listado.idEntidad
                LEFT JOIN usuarios_listado   ON usuarios_listado.idUsuario  = cotizacion_listado.idUsuario',
            'where'   => 'cotizacion_listado.idCotizacion = ?',
            'params'  => [$CotizacionID['data']],
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
            'data'    => 'idEntidad AS ID,CONCAT(CASE idTipoEntidad WHEN 1 THEN CONCAT_WS(" ", Nombre, ApellidoPat) WHEN 2 THEN RazonSocial END,IF(Nick IS NULL OR Nick = "","",CONCAT(" (", Nick, ")"))) AS Nombre',
            'table'   => 'entidades_listado',
            'join'    => '',
            'where'   => 'idEstado = ? AND idTipo = ?',
            'params'  => [1, 2],
            'group'   => '',
            'having'  => '',
            'order'   => 'ApellidoPat ASC,Nombre ASC,RazonSocial ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams      = ['query' => $query];
        // Ejecuto la query
        $arrEntidades = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrEntidades['status']){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'        => 'Resumen Cotización',
                'PageDescription'  => 'Resumen Cotización.',
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
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData['data'],
                'arrEntidades'    => $arrEntidades['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrEntidades]);
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
        $CotizacionID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($CotizacionID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                cotizacion_listado.idCotizacion,
                cotizacion_listado.Creacion_fecha,
                cotizacion_listado.Creacion_hora,
                cotizacion_listado.Observaciones,
                cotizacion_listado.ValorNeto,
                cotizacion_listado.IVA,
                cotizacion_listado.ValorTotal,

                entidades_listado.idTipoEntidad,
                entidades_listado.Nombre AS EntidadesNombre,
                entidades_listado.ApellidoPat AS EntidadesApellido,
                entidades_listado.RazonSocial AS EntidadesRazonSocial,
                entidades_listado.Nick AS EntidadesNick,
                usuarios_listado.Nombre AS Usuario',
            'table'   => 'cotizacion_listado',
            'join'    => '
                LEFT JOIN entidades_listado  ON entidades_listado.idEntidad   = cotizacion_listado.idEntidad
                LEFT JOIN usuarios_listado   ON usuarios_listado.idUsuario    = cotizacion_listado.idUsuario',
            'where'   => 'cotizacion_listado.idCotizacion = ?',
            'params'  => [$CotizacionID['data']],
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
    public function Insert($f3){

        /************************************/
        // Usuario creador
        $_POST['idUsuario'] = $f3->get('SESSION.DataInfo.UserID');

        /************************************/
        // Variables
        $ndata_1 = isset($_POST['Item_Item']) ? count($_POST['Item_Item']) : 0;
        $ndata_2 = isset($_POST['Producto_idProducto']) ? count($_POST['Producto_idProducto']) : 0;
        $ndata_3 = isset($_POST['Servicio_idServicio']) ? count($_POST['Servicio_idServicio']) : 0;

        // Variables para validaciones
        $DataVal['Count'] = $ndata_1 + $ndata_2 + $ndata_3;
        $DataVal['Msg']   = 'No hay nada ingresado';

        /************************************/
        // Generacion de errores
        if($DataVal['Count']==0) {
            Response::error($DataVal['Msg'], 500);
        }

        /************************************/
        // Variables
        $x_ValorTotal     = 0;
        $x_TotalItems     = 0;
        $x_TotalProductos = 0;
        $x_TotalServicios = 0;
        /************************************/
        // Items
        if(isset($ndata_1)&&$ndata_1!=0){
            // Recorro los items
            for($j1 = 0; $j1 < $ndata_1; $j1++){
                $x_ValorTotal = (isset($_POST['Item_ValorTotal'][$j1])) ? $x_ValorTotal + $_POST['Item_ValorTotal'][$j1] : $x_ValorTotal;
                $x_TotalItems = (isset($_POST['Item_ValorTotal'][$j1])) ? $x_TotalItems + $_POST['Item_ValorTotal'][$j1] : $x_TotalItems;
            }
        }
        // Productos
        if(isset($ndata_2)&&$ndata_2!=0){
            // Recorro los items
            for($j1 = 0; $j1 < $ndata_2; $j1++){
                $x_ValorTotal     = (isset($_POST['Producto_ValorTotal'][$j1])) ? $x_ValorTotal + $_POST['Producto_ValorTotal'][$j1] : $x_ValorTotal;
                $x_TotalProductos = (isset($_POST['Producto_ValorTotal'][$j1])) ? $x_TotalProductos + $_POST['Producto_ValorTotal'][$j1] : $x_TotalProductos;
            }
        }
        // Servicios
        if(isset($ndata_3)&&$ndata_3!=0){
            // Recorro los items
            for($j1 = 0; $j1 < $ndata_3; $j1++){
                $x_ValorTotal     = (isset($_POST['Servicio_ValorTotal'][$j1])) ? $x_ValorTotal + $_POST['Servicio_ValorTotal'][$j1] : $x_ValorTotal;
                $x_TotalServicios = (isset($_POST['Servicio_ValorTotal'][$j1])) ? $x_TotalServicios + $_POST['Servicio_ValorTotal'][$j1] : $x_TotalServicios;
            }
        }

        /************************************/
        // Se generan datos
        $_POST['ValorNeto']       = ($x_ValorTotal/1.19);
        $_POST['IVA']             = $x_ValorTotal - ($x_ValorTotal/1.19);
        $_POST['ValorTotal']      = $x_ValorTotal;
        $_POST['TotalItems']      = $x_TotalItems;
        $_POST['TotalProductos']  = $x_TotalProductos;
        $_POST['TotalServicios']  = $x_TotalServicios;
        // Verifico si existe
        if(isset($_POST['Creacion_fecha'])&&$_POST['Creacion_fecha']!=''){
            $_POST['Creacion_Semana']  = $this->DataDate->fecha2NSemana($_POST['Creacion_fecha']);
            $_POST['Creacion_mes']     = $this->DataDate->fecha2NMes($_POST['Creacion_fecha']);
            $_POST['Creacion_ano']     = $this->DataDate->fecha2Ano($_POST['Creacion_fecha']);
        }

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idUsuario,idEntidad,fecha_auto,Creacion_fecha,Creacion_Semana,Creacion_mes,Creacion_ano,Creacion_hora,Observaciones,ValorNeto,IVA,ValorTotal,TotalItems,TotalProductos,TotalServicios',
            'required'  => 'idUsuario,idEntidad,fecha_auto,Creacion_fecha',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'cotizacion_listado',
            'Post'      => $_POST
        ];
        /************************************/
        // Se genera el chequeo
        $dataCheck_1 = $this->dataCheck_1($_POST);
        // Preparo los datos
        $xParams  = ['DataCheck' => $dataCheck_1, 'query' => $query];
        // Ejecuto la query
        $Response = $this->Base_insert($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            $this->Base_transactionRollback();
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
        }

        /************************************/
        // Items
        if(isset($ndata_1)&&$ndata_1!=0){
            /************************************/
            // Se acumulan las filas a insertar para evitar un INSERT por cada item (N+1)
            $rowsItems = [];
            /************************************/
            // Recorro los items
            for($j1 = 0; $j1 < $ndata_1; $j1++){
                /************************************/
                // Se agrega respuesta
                $rowsItems[] = [
                    'idCotizacion'  => $Response['data'],
                    'Item'          => $_POST['Item_Item'][$j1],
                    'Number'        => $_POST['Item_Number'][$j1],
                    'ValorTotal'    => $_POST['Item_ValorTotal'][$j1],
                ];
            }

            /************************************/
            // Si hay datos marcados, se insertan todos en una sola sentencia
            if ($rowsItems){
                /************************************/
                // Se genera el chequeo
                $DataCheck = $this->dataCheck_2('');
                /************************************/
                // Se genera la query
                $query = [
                    'data'      => 'idCotizacion,Item,Number,ValorTotal',
                    'required'  => 'idCotizacion,Item,Number,ValorTotal',
                    'table'     => 'cotizacion_listado_items',
                    'rows'      => $rowsItems
                ];
                // Preparo los datos
                $xParams   = ['DataCheck' => $DataCheck, 'query' => $query];
                // Ejecuto la query
                $respItems = $this->Base_insertMultiple($xParams);
                /************************************/
                // Si falla la ejecucion, se revierte de inmediato
                if ($respItems['status'] === false) {
                    $this->Base_transactionRollback();
                    Response::error('Error al operar con la Base de Datos', 500, $respItems['error']);
                }
            }
        }

        /*******************************************************/
        // Productos
        if(isset($ndata_2)&&$ndata_2!=0){
            /************************************/
            // Se acumulan las filas a insertar para evitar un INSERT por cada producto (N+1)
            $rowsProductos = [];
            /************************************/
            // Recorro los items
            for($j1 = 0; $j1 < $ndata_2; $j1++){
                /************************************/
                // Se agrega respuesta
                $rowsProductos[] = [
                    'idCotizacion'    => $Response['data'],
                    'idProducto'      => $_POST['Producto_idProducto'][$j1],
                    'Number'          => $_POST['Producto_Number'][$j1],
                    'ValorTotal'      => $_POST['Producto_ValorTotal'][$j1],
                ];
            }

            /************************************/
            // Si hay datos marcados, se insertan todos en una sola sentencia
            if ($rowsProductos){
                /************************************/
                // Se genera el chequeo
                $DataCheck = $this->dataCheck_2('');
                /************************************/
                // Se genera la query
                $query = [
                    'data'      => 'idCotizacion,idProducto,Number,ValorTotal',
                    'required'  => 'idCotizacion,idProducto,Number,ValorTotal',
                    'table'     => 'cotizacion_listado_productos',
                    'rows'      => $rowsProductos
                ];
                // Preparo los datos
                $xParams      = ['DataCheck' => $DataCheck, 'query' => $query];
                // Ejecuto la query
                $respProductos = $this->Base_insertMultiple($xParams);
                /************************************/
                // Si falla la ejecucion, se revierte de inmediato
                if ($respProductos['status'] === false) {
                    $this->Base_transactionRollback();
                    Response::error('Error al operar con la Base de Datos', 500, $respProductos['error']);
                }
            }
        }

        /*******************************************************/
        // Servicios
        if(isset($ndata_3)&&$ndata_3!=0){
            /************************************/
            // Se acumulan las filas a insertar para evitar un INSERT por cada servicio (N+1)
            $rowsServicios = [];
            /************************************/
            // Recorro los items
            for($j1 = 0; $j1 < $ndata_3; $j1++){
                /************************************/
                // Se agrega respuesta
                $rowsServicios[] = [
                    'idCotizacion'  => $Response['data'],
                    'idServicio'    => $_POST['Servicio_idServicio'][$j1],
                    'Number'        => $_POST['Servicio_Number'][$j1],
                    'ValorTotal'    => $_POST['Servicio_ValorTotal'][$j1],
                ];
            }

            /************************************/
            // Si hay datos marcados, se insertan todos en una sola sentencia
            if ($rowsServicios){
                /************************************/
                // Se genera el chequeo
                $DataCheck = $this->dataCheck_2('');
                /************************************/
                // Se genera la query
                $query = [
                    'data'      => 'idCotizacion,idServicio,Number,ValorTotal',
                    'required'  => 'idCotizacion,idServicio,Number,ValorTotal',
                    'table'     => 'cotizacion_listado_servicios',
                    'rows'      => $rowsServicios
                ];
                // Preparo los datos
                $xParams       = ['DataCheck' => $DataCheck, 'query' => $query];
                // Ejecuto la query
                $respServicios = $this->Base_insertMultiple($xParams);
                /************************************/
                // Si falla la ejecucion, se revierte de inmediato
                if ($respServicios['status'] === false) {
                    $this->Base_transactionRollback();
                    Response::error('Error al operar con la Base de Datos', 500, $respServicios['error']);
                }
            }
        }

        /************************************/
        // Se confirma la transacción
        $this->Base_transactionCommit();

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
    public function Update($f3){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Usuario creador
        $_POST['idUsuario'] = $f3->get('SESSION.DataInfo.UserID');

        /************************************/
        // Verifico si existe
        if(isset($_POST['Creacion_fecha'])&&$_POST['Creacion_fecha']!=''){
            $_POST['Creacion_Semana']  = $this->DataDate->fecha2NSemana($_POST['Creacion_fecha']);
            $_POST['Creacion_mes']     = $this->DataDate->fecha2NMes($_POST['Creacion_fecha']);
            $_POST['Creacion_ano']     = $this->DataDate->fecha2Ano($_POST['Creacion_fecha']);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idCotizacion,idUsuario,idEntidad,fecha_auto,Creacion_fecha,Creacion_Semana,Creacion_mes,Creacion_ano,Creacion_hora,Observaciones,ValorNeto,IVA,ValorTotal,TotalItems,TotalProductos,TotalServicios,TotalGuias',
            'required'  => 'idCotizacion,idUsuario,idEntidad,fecha_auto,Creacion_fecha',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'cotizacion_listado',
            'where'     => 'idCotizacion',
            'Post'      => $_POST,
        ];
        /************************************/
        // Se genera el chequeo
        $dataCheck_1 = $this->dataCheck_1($_POST);
        // Preparo los datos
        $xParams  = ['DataCheck' => $dataCheck_1, 'query' => $query];
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
            'files'       => '',
            'table'       => 'cotizacion_listado',
            'where'       => 'idCotizacion',
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
        $arrTableDel[] = ['files' => '', 'table' => 'cotizacion_listado_items'];
        $arrTableDel[] = ['files' => '', 'table' => 'cotizacion_listado_productos'];
        $arrTableDel[] = ['files' => '', 'table' => 'cotizacion_listado_servicios'];

        /************************************/
        // Verifico si existe
        if (!empty($arrTableDel)) {
            // Recorro
            foreach ($arrTableDel as $tblDel) {
                /************************************/
                // Se genera la query
                $query = ['files' => $tblDel['files'], 'table' => $tblDel['table'], 'where' => 'idCotizacion', 'SubCarpeta' => '', 'Post' => $dataDelete];
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

    /******************************************************************************/
    /*                             EJECUCION OTROS                                */
    /******************************************************************************/
    /*******************************************************************/
    // Se actualizan los montos
    /*******************************************************************/
    public function updateCotizacion($Tipo, $CotizacionID, $NewDBConn = null): array{

        /************************************/
        // Verifico si se ejecuta otro hilo
        $DBConn = $NewDBConn ?? $this->getDBConn();

        /************************************/
        // Se abre cadena
        $Data = 'cotizacion_listado.idCotizacion';

        /************************************/
        // Se cambia la query dependiendo de el tipo
        switch ($Tipo) {
            /************************************/
            // Items
            case 1:
                $Data .= ',
                cotizacion_listado.TotalProductos,
                cotizacion_listado.TotalServicios,
                (SELECT SUM(ValorTotal) FROM cotizacion_listado_items     WHERE idCotizacion='.$CotizacionID.') AS TotalItems';
                break;
            /************************************/
            // Productos
            case 2:
                $Data .= ',
                cotizacion_listado.TotalItems,
                cotizacion_listado.TotalServicios,
                (SELECT SUM(ValorTotal) FROM cotizacion_listado_productos WHERE idCotizacion='.$CotizacionID.') AS TotalProductos';
                break;
            /************************************/
            // Servicios
            case 3:
                $Data .= ',
                cotizacion_listado.TotalItems,
                cotizacion_listado.TotalProductos,
                (SELECT SUM(ValorTotal) FROM cotizacion_listado_servicios WHERE idCotizacion='.$CotizacionID.') AS TotalServicios';
                break;
        }

        /************************************/
        // Se inicia la transacción solo si corresponde
        if($NewDBConn === null){
            $this->Base_transactionBegin(['newBDConn' => $DBConn]);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => $Data,
            'table'   => 'cotizacion_listado',
            'join'    => '',
            'where'   => 'idCotizacion = ?',
            'params'  => [$CotizacionID],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query, 'newBDConn' => $DBConn];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if ($rowData['status'] === false) {
            $this->Base_transactionRollback(['newBDConn' => $DBConn]);
            return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $rowData['error']];
        }

        /************************************/
        // Calculo
        $x_ValorTotal = ($rowData['data']['TotalItems'] ?? 0) + ($rowData['data']['TotalProductos'] ?? 0) + ($rowData['data']['TotalServicios'] ?? 0);
        // Se agrega respuesta
        $arrTareas = [
            'idCotizacion'    => $CotizacionID,
            'ValorNeto'       => ($x_ValorTotal/1.19),
            'IVA'             => $x_ValorTotal - ($x_ValorTotal/1.19),
            'ValorTotal'      => $x_ValorTotal,
            'TotalItems'      => $rowData['data']['TotalItems'] ?? 0,
            'TotalProductos'  => $rowData['data']['TotalProductos'] ?? 0,
            'TotalServicios'  => $rowData['data']['TotalServicios'] ?? 0,
        ];
        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idCotizacion,ValorNeto,IVA,ValorTotal,TotalItems,TotalProductos,TotalServicios',
            'required'  => 'idCotizacion,ValorNeto,IVA,ValorTotal,TotalItems,TotalProductos,TotalServicios',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'cotizacion_listado',
            'where'     => 'idCotizacion',
            'Post'      => $arrTareas
        ];
        /************************************/
        // Se genera el chequeo
        $dataCheck_3 = $this->dataCheck_3($arrTareas);
        // Preparo los datos
        $xParams = ['DataCheck' => $dataCheck_3, 'query' => $query, 'newBDConn' => $DBConn];
        // Ejecuto la query
        $Response = $this->Base_update($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            $this->Base_transactionRollback(['newBDConn' => $DBConn]);
            return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $Response['error']];
        }

        /************************************/
        // Confirmar transacción solo si corresponde
        if($NewDBConn === null){
            $this->Base_transactionCommit(['newBDConn' => $DBConn]);
        }

        /************************************/
        // Retorno los datos
        return ['code' => 200, 'data' => 'OK'];

    }

    /******************************************************************************/
    /*                             Métodos privados                               */
    /******************************************************************************/
    /*******************************************************************/
    // Se validan los datos
    /*******************************************************************/
    private function dataCheck_1($POST){
        // Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => '',
            'ValidarNumero'             => 'idUsuario,idEntidad,Creacion_Semana,Creacion_mes,Creacion_ano,ValorNeto,IVA,ValorTotal,TotalItems,TotalProductos,TotalServicios',
            'ValidarEntero'             => 'idUsuario,idEntidad',
            'ValidarRut'                => '',
            'ValidarPatente'            => '',
            'ValidarFecha'              => 'fecha_auto,Creacion_fecha',
            'ValidarHora'               => 'Creacion_hora',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => 'Observaciones',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => '',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Observaciones',
            'ValidarEspaciosVacios'     => '',
            'ValidarMayusculas'         => '',
            'ValidarCoincidencias'      => '',
            'ValidarDominioEmail'       => '',
            'ValidarPasswordSegura'     => '',
            'ValidarFechaRango'         => 'fecha_auto',
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

    /*******************************************************************/
    // Se validan los datos
    /*******************************************************************/
    private function dataCheck_2($POST){
        // Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => '',
            'ValidarNumero'             => 'idCotizacion,idProducto,idServicio,Number,ValorTotal',
            'ValidarEntero'             => 'idCotizacion,idProducto,idServicio',
            'ValidarRut'                => '',
            'ValidarPatente'            => '',
            'ValidarFecha'              => '',
            'ValidarHora'               => '',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => 'Item',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'Item',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Item',
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

    /*******************************************************************/
    // Se validan los datos
    /*******************************************************************/
    private function dataCheck_3($POST){
        // Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => '',
            'ValidarNumero'             => 'idCotizacion,ValorNeto,IVA,ValorTotal,TotalItems,TotalProductos,TotalServicios',
            'ValidarEntero'             => 'idCotizacion',
            'ValidarRut'                => '',
            'ValidarPatente'            => '',
            'ValidarFecha'              => '',
            'ValidarHora'               => '',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => '',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => '',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => '',
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
