<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class gestionDocumentos extends ControllerBase {

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
        $this->controllerName    = 'gestionDocumentos';
		$this->FormInputs        = new UIFormInputs();
		$this->Codification      = new FunctionsSecurityCodification();
		$this->DataNumbers       = new FunctionsDataNumbers();
		$this->DataDate          = new FunctionsDataDate();
		$this->ServerServer      = new FunctionsServerServer();
		$this->CommonData        = new FunctionsCommonData();
		$this->WidgetsCommon     = new UIWidgetsCommon();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /*******************************************************************/
    // Listar
    /*******************************************************************/
    public function listAll_1($f3){$this->listAll($f3, 1);}
    public function listAll_2($f3){$this->listAll($f3, 2);}
    // Listar Todo
    public function UpdateList_1($f3){$this->UpdateList($f3, 1);}
    public function UpdateList_2($f3){$this->UpdateList($f3, 2);}
    //View
    public function View_0($f3, $params){$this->View($f3, $params, 0);}
    public function View_1($f3, $params){$this->View($f3, $params, 1);}
    public function View_2($f3, $params){$this->View($f3, $params, 2);}
    //imprimir
    public function Print_0($f3, $params){$this->Print($f3, $params, 0, 1);}
    public function Print_1($f3, $params){$this->Print($f3, $params, 1, 1);}
    public function Print_2($f3, $params){$this->Print($f3, $params, 2, 1);}
    //ver documento
    public function noPrint_0($f3, $params){$this->Print($f3, $params, 0, 0);}
    public function noPrint_1($f3, $params){$this->Print($f3, $params, 1, 0);}
    public function noPrint_2($f3, $params){$this->Print($f3, $params, 2, 0);}
    public function noPrintDoc($f3, $params){$this->Print($f3, $params, 2, 2);}
    //Resumen
    public function Resumen_1($f3, $params){$this->Resumen($f3, $params, 1);}
    public function Resumen_2($f3, $params){$this->Resumen($f3, $params, 2);}
    //Resumen-Update
    public function ResumenUpdate_1($f3, $params){$this->ResumenUpdate($f3, $params, 1);}
    public function ResumenUpdate_2($f3, $params){$this->ResumenUpdate($f3, $params, 2);}

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /*******************************************************************/
    // Listar
    /*******************************************************************/
    public function listAll($f3, $idTipo){

        /************************************/
        // Se verifica movimiento
        $tsrxName = $this->tsrxName($idTipo);
        $TipoMov  = $this->TipoMov($idTipo);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                facturacion_listado.idFacturacion,
                facturacion_listado.N_Doc,
                facturacion_listado.Creacion_fecha,
                facturacion_listado.ValorTotal,
                facturacion_listado.MontoPagado,

                entidades_listado.idTipoEntidad,
                entidades_listado.Nombre AS EntidadesNombre,
                entidades_listado.ApellidoPat AS EntidadesApellido,
                entidades_listado.RazonSocial AS EntidadesRazonSocial,
                entidades_listado.Nick AS EntidadesNick,
                core_documentos_mercantiles.Nombre AS Documento,
                core_estados_pago.Nombre AS EstadoPago,
                core_estados_pago.Color AS EstadoColor',
            'table'   => 'facturacion_listado',
            'join'    => '
                LEFT JOIN entidades_listado             ON entidades_listado.idEntidad                = facturacion_listado.idEntidad
                LEFT JOIN core_documentos_mercantiles   ON core_documentos_mercantiles.idDocumentos   = facturacion_listado.idDocumentos
                LEFT JOIN core_estados_pago             ON core_estados_pago.idEstadoPago             = facturacion_listado.idEstadoPago',
            'where'   => 'facturacion_listado.idTipo = ?',
            'params'  => [$idTipo],
            'group'   => '',
            'having'  => '',
            'order'   => 'facturacion_listado.Creacion_fecha DESC, facturacion_listado.N_Doc DESC, facturacion_listado.idFacturacion DESC, entidades_listado.ApellidoPat ASC, entidades_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrList = $this->Base_GetList($xParams);

        /************************************/
        // Se instancia
        $arrUserData = $this->getUserData($f3);

        // Se verifica si se tiene el permiso para visualizar el dato
        if($arrUserData["usuariosPermisosBodegas"]==2 && $arrUserData['UserType'] != 1){
            $X_join   = 'INNER JOIN bodegas_listado_permisos_usuarios ON bodegas_listado_permisos_usuarios.idBodegas = bodegas_listado.idBodegas';
            $X_where  = 'bodegas_listado.idEstado = ? AND bodegas_listado_permisos_usuarios.idUsuario = ?';
            $X_params = [1, $arrUserData['UserID']];
        // Si se permite junto con la creacion de tareas
        }else{
            $X_join  = '';
            $X_where = 'bodegas_listado.idEstado = ?';
            $X_params = [1];
        }
        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'bodegas_listado.idBodegas AS ID, bodegas_listado.Nombre',
            'table'   => 'bodegas_listado',
            'join'    => $X_join,
            'where'   => $X_where,
            'params'  => $X_params,
            'group'   => '',
            'having'  => '',
            'order'   => 'bodegas_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams    = ['query' => $query];
        // Ejecuto la query
        $arrBodegas = $this->Base_GetList($xParams);

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
            'data'    => 'idEntidad AS ID,CONCAT(CASE idTipoEntidad WHEN 1 THEN CONCAT_WS(" ", Nombre, ApellidoPat) WHEN 2 THEN RazonSocial END,IF(Nick IS NULL OR Nick = "","",CONCAT(" (", Nick, ")"))) AS Nombre',
            'table'   => 'entidades_listado',
            'join'    => '',
            'where'   => 'idEstado = ? AND idTipo = ?',
            'params'  => [1, $idTipo],
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
            'data'    => 'idDocumentos AS ID,Nombre',
            'table'   => 'core_documentos_mercantiles',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams       = ['query' => $query];
        // Ejecuto la query
        $arrDocumentos = $this->Base_GetList($xParams);

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

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idFacturacion, N_Doc, Creacion_fecha, idEntidad',
            'table'   => 'facturacion_listado',
            'join'    => '',
            'where'   => 'idDocumentos = ? AND idEstadoPago = ?',
            'params'  => [3, 1],
            'group'   => '',
            'having'  => '',
            'order'   => 'idFacturacion ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $arrGuias = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idEstadoPago AS ID,Nombre',
            'table'   => 'core_estados_pago',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams       = ['query' => $query];
        // Ejecuto la query
        $arrEstadoPago = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($arrList['status'] && $arrBodegas['status'] && $arrProductos['status'] && $arrEntidades['status'] && $arrDocumentos['status'] && $arrServicios['status'] && $arrGuias['status'] && $arrEstadoPago['status']){

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => $TipoMov,
                'PageDescription' => $TipoMov,
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => $TipoMov,
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $tsrxName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_Codification'    => $this->Codification,
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                'Fnc_ServerServer'    => $this->ServerServer,
                'Fnc_CommonData'      => $this->CommonData,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList['data'],
                'arrBodegas'      => $arrBodegas['data'],
                'arrProductos'    => $arrProductos['data'],
                'arrEntidades'    => $arrEntidades['data'],
                'arrDocumentos'   => $arrDocumentos['data'],
                'arrServicios'    => $arrServicios['data'],
                'arrGuias'        => $arrGuias['data'],
                'arrEstadoPago'   => $arrEstadoPago['data'],
                'idTipo'          => $idTipo,

            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrList,$arrBodegas,$arrProductos,$arrEntidades,$arrDocumentos,$arrServicios,$arrGuias,$arrEstadoPago]);
            // Despliegue de errores
            $this->showError(1, $f3, $result);
        }
    }

    /*******************************************************************/
    // Actualizar Listar
    /*******************************************************************/
    public function UpdateList($f3, $idTipo){

        /************************************/
        // Se verifica movimiento
        $tsrxName = $this->tsrxName($idTipo);
        $TipoMov  = $this->TipoMov($idTipo);

        /************************************/
        // Variables
        $WhereData_int     = 'idDocumentos,idEntidad,idEstadoPago,idFacturacion';  // Datos búsqueda exacta
        $WhereData_string  = 'N_Doc';                                              // Datos búsqueda relativa
        $WhereData_between = 'Creacion_fecha-F_Inicio-F_Termino';                  // Datos búsqueda Between
        $whereInt          = '';                                                   // Se crea cadena
        $whereParams       = [];                                                   // Valores bindeados asociados a $whereInt
        /************************************/
        // Se validan las fechas
        $RespDataBetween = $this->searchValidateDates($WhereData_between);
        if($RespDataBetween!=''){
            Response::error($RespDataBetween, 500);
        }
        /************************************/
        // Agrego variable busqueda
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_int, 'facturacion_listado', 1);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_string, 'facturacion_listado', 2);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_between, 'facturacion_listado', 3);
        $whereInt = $r['where']; $whereParams = $r['params'];
        // Verifico si esta vacio
        $whereInt   .= ($whereInt ? ' AND ' : '') . 'facturacion_listado.idTipo = ?';
        $whereParams = array_merge($whereParams, [$idTipo]);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                facturacion_listado.idFacturacion,
                facturacion_listado.N_Doc,
                facturacion_listado.Creacion_fecha,
                facturacion_listado.ValorTotal,
                facturacion_listado.MontoPagado,

                entidades_listado.idTipoEntidad,
                entidades_listado.Nombre AS EntidadesNombre,
                entidades_listado.ApellidoPat AS EntidadesApellido,
                entidades_listado.RazonSocial AS EntidadesRazonSocial,
                entidades_listado.Nick AS EntidadesNick,
                core_documentos_mercantiles.Nombre AS Documento,
                core_estados_pago.Nombre AS EstadoPago,
                core_estados_pago.Color AS EstadoColor',
            'table'   => 'facturacion_listado',
            'join'    => '
                LEFT JOIN entidades_listado             ON entidades_listado.idEntidad                = facturacion_listado.idEntidad
                LEFT JOIN core_documentos_mercantiles   ON core_documentos_mercantiles.idDocumentos   = facturacion_listado.idDocumentos
                LEFT JOIN core_estados_pago             ON core_estados_pago.idEstadoPago             = facturacion_listado.idEstadoPago',
            'where'   => $whereInt,
            'params'  => $whereParams,
            'group'   => '',
            'having'  => '',
            'order'   => 'facturacion_listado.Creacion_fecha DESC, facturacion_listado.N_Doc DESC, facturacion_listado.idFacturacion DESC, entidades_listado.ApellidoPat ASC, entidades_listado.Nombre ASC',
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
                'TableTitle'      => $TipoMov,
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $tsrxName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'     => $this->Codification,
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'arrList'  => $arrList['data'],
                'idTipo'   => $idTipo,
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
    public function View($f3, $params, $idTipo){

        /************************************/
        // Se obtiene el ID
        $FacturacionID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($FacturacionID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se verifica movimiento
        switch ($idTipo) {
            case 0: $tsrxName = 'informeDocumentos';break;         //informe Documentos
            case 1: $tsrxName = 'gestionDocumentosCompras';break;  //Compras
            case 2: $tsrxName = 'gestionDocumentosVentas';break;   //Ventas
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                facturacion_listado.idFacturacion,
                facturacion_listado.idTipo,
                facturacion_listado.N_Doc,
                facturacion_listado.Creacion_fecha,
                facturacion_listado.Creacion_hora,
                facturacion_listado.Observaciones,
                facturacion_listado.ValorNeto,
                facturacion_listado.IVA,
                facturacion_listado.ValorTotal,
                facturacion_listado.MontoPagado,

                core_facturacion_tipo.Nombre AS TipoFacturacion,
                entidades_listado.idTipoEntidad,
                entidades_listado.Nombre AS EntidadesNombre,
                entidades_listado.ApellidoPat AS EntidadesApellido,
                entidades_listado.RazonSocial AS EntidadesRazonSocial,
                entidades_listado.Nick AS EntidadesNick,
                BodIngreso.Nombre AS BodegaIngreso,
                BodEgreso.Nombre AS BodegaEgreso,
                core_documentos_mercantiles.Nombre AS Documento,
                core_estados_pago.Nombre AS EstadoPago,
                core_estados_pago.Color AS EstadoColor,
                usuarios_listado.Nombre AS Usuario',
            'table'   => 'facturacion_listado',
            'join'    => '
                LEFT JOIN core_facturacion_tipo         ON core_facturacion_tipo.idTipo               = facturacion_listado.idTipo
                LEFT JOIN entidades_listado             ON entidades_listado.idEntidad                = facturacion_listado.idEntidad
                LEFT JOIN bodegas_listado BodIngreso    ON BodIngreso.idBodegas                       = facturacion_listado.idBodegasIngreso
                LEFT JOIN bodegas_listado BodEgreso     ON BodEgreso.idBodegas                        = facturacion_listado.idBodegasEgreso
                LEFT JOIN core_documentos_mercantiles   ON core_documentos_mercantiles.idDocumentos   = facturacion_listado.idDocumentos
                LEFT JOIN core_estados_pago             ON core_estados_pago.idEstadoPago             = facturacion_listado.idEstadoPago
                LEFT JOIN usuarios_listado              ON usuarios_listado.idUsuario                 = facturacion_listado.idUsuario',
            'where'   => 'facturacion_listado.idFacturacion = ?',
            'params'  => [$FacturacionID['data']],
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
            'table'   => 'facturacion_listado_items',
            'join'    => '',
            'where'   => 'idFacturacion = ?',
            'params'  => [$FacturacionID['data']],
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
                core_estados_ingreso.Nombre AS TipoMovimiento,
                bodegas_listado.Nombre AS Bodega,
                productos_listado.Nombre AS ProductoNombre,
                facturacion_listado_productos.Number AS ProductoCantidad,
                facturacion_listado_productos.ValorTotal AS ProductoValor,
                core_unidades_medida.Nombre AS UnidadMedida',
            'table'   => 'facturacion_listado_productos',
            'join'    => '
                LEFT JOIN core_estados_ingreso  ON core_estados_ingreso.idEstadoIngreso  = facturacion_listado_productos.idEstadoIngreso
                LEFT JOIN bodegas_listado       ON bodegas_listado.idBodegas             = facturacion_listado_productos.idBodegas
                LEFT JOIN productos_listado     ON productos_listado.idProducto          = facturacion_listado_productos.idProducto
                LEFT JOIN core_unidades_medida  ON core_unidades_medida.idUniMed         = productos_listado.idUniMed',
            'where'   => 'facturacion_listado_productos.idFacturacion = ?',
            'params'  => [$FacturacionID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'facturacion_listado_productos.idExistencia ASC',
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
                facturacion_listado_servicios.Number AS ServicioCantidad,
                facturacion_listado_servicios.ValorTotal AS ServicioValor',
            'table'   => 'facturacion_listado_servicios',
            'join'    => 'LEFT JOIN servicios_listado  ON servicios_listado.idServicio  = facturacion_listado_servicios.idServicio',
            'where'   => 'facturacion_listado_servicios.idFacturacion = ?',
            'params'  => [$FacturacionID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'facturacion_listado_servicios.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams      = ['query' => $query];
        // Ejecuto la query
        $arrServicios = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                facturacion_listado_guias.idFacturacionRel,
                core_documentos_mercantiles.Nombre AS Documento,
                facturacion_listado.N_Doc,
                facturacion_listado.Creacion_fecha,
                facturacion_listado.ValorTotal',
            'table'   => 'facturacion_listado_guias',
            'join'    => '
                LEFT JOIN facturacion_listado          ON facturacion_listado.idFacturacion         = facturacion_listado_guias.idFacturacionRel
                LEFT JOIN core_documentos_mercantiles  ON core_documentos_mercantiles.idDocumentos  = facturacion_listado.idDocumentos',
            'where'   => 'facturacion_listado_guias.idFacturacion = ?',
            'params'  => [$FacturacionID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'facturacion_listado_guias.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $arrGuias = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                usuarios_listado.Nombre AS UsuarioPago,
                core_documentos_pago.Nombre AS DocPago,
                facturacion_listado_pagos.N_Doc,
                facturacion_listado_pagos.MontoPagado,
                facturacion_listado_pagos.FechaPago',
            'table'   => 'facturacion_listado_pagos',
            'join'    => '
                LEFT JOIN usuarios_listado     ON usuarios_listado.idUsuario            = facturacion_listado_pagos.idUsuario
                LEFT JOIN core_documentos_pago ON core_documentos_pago.idDocumentoPago  = facturacion_listado_pagos.idDocumentoPago',
            'where'   => 'facturacion_listado_pagos.idFacturacion = ?',
            'params'  => [$FacturacionID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'facturacion_listado_pagos.idPago ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $arrPagos = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrItems['status'] && $arrProductos['status'] && $arrServicios['status'] && $arrGuias['status'] && $arrPagos['status']){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $tsrxName),
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
                'arrGuias'         => $arrGuias['data'],
                'arrPagos'         => $arrPagos['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-View.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrItems,$arrProductos,$arrServicios,$arrGuias,$arrPagos]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Imprimir
    /*******************************************************************/
    public function Print($f3, $params, $idTipo, $Imprimir){

        /************************************/
        // Se obtiene el ID
        $FacturacionID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($FacturacionID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se verifica movimiento
        switch ($idTipo) {
            case 0: $tsrxName = 'informeDocumentos';break;         //informe Documentos
            case 1: $tsrxName = 'gestionDocumentosCompras';break;  //Compras
            case 2: $tsrxName = 'gestionDocumentosVentas';break;   //Ventas
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                facturacion_listado.idFacturacion,
                facturacion_listado.idTipo,
                facturacion_listado.N_Doc,
                facturacion_listado.Creacion_fecha,
                facturacion_listado.Creacion_hora,
                facturacion_listado.Observaciones,
                facturacion_listado.ValorNeto,
                facturacion_listado.IVA,
                facturacion_listado.ValorTotal,

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
                entidades_listado.Fono2 AS EntidadesFono2,

                core_facturacion_tipo.Nombre AS TipoFacturacion,
                BodIngreso.Nombre AS BodegaIngreso,
                BodEgreso.Nombre AS BodegaEgreso,
                core_documentos_mercantiles.Nombre AS Documento',
            'table'   => 'facturacion_listado',
            'join'    => '
                LEFT JOIN core_facturacion_tipo         ON core_facturacion_tipo.idTipo               = facturacion_listado.idTipo
                LEFT JOIN entidades_listado             ON entidades_listado.idEntidad                = facturacion_listado.idEntidad
                LEFT JOIN core_ubicacion_ciudad         ON core_ubicacion_ciudad.idCiudad             = entidades_listado.idCiudad
                LEFT JOIN core_ubicacion_comunas        ON core_ubicacion_comunas.idComuna            = entidades_listado.idComuna
                LEFT JOIN bodegas_listado BodIngreso    ON BodIngreso.idBodegas                       = facturacion_listado.idBodegasIngreso
                LEFT JOIN bodegas_listado BodEgreso     ON BodEgreso.idBodegas                        = facturacion_listado.idBodegasEgreso
                LEFT JOIN core_documentos_mercantiles   ON core_documentos_mercantiles.idDocumentos   = facturacion_listado.idDocumentos',
            'where'   => 'facturacion_listado.idFacturacion = ?',
            'params'  => [$FacturacionID['data']],
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
            'table'   => 'facturacion_listado_items',
            'join'    => '',
            'where'   => 'idFacturacion = ?',
            'params'  => [$FacturacionID['data']],
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
                core_estados_ingreso.Nombre AS TipoMovimiento,
                bodegas_listado.Nombre AS Bodega,
                productos_listado.Nombre AS ProductoNombre,
                facturacion_listado_productos.Number AS ProductoCantidad,
                facturacion_listado_productos.ValorTotal AS ProductoValor,
                core_unidades_medida.Nombre AS UnidadMedida',
            'table'   => 'facturacion_listado_productos',
            'join'    => '
                LEFT JOIN core_estados_ingreso  ON core_estados_ingreso.idEstadoIngreso  = facturacion_listado_productos.idEstadoIngreso
                LEFT JOIN bodegas_listado       ON bodegas_listado.idBodegas             = facturacion_listado_productos.idBodegas
                LEFT JOIN productos_listado     ON productos_listado.idProducto          = facturacion_listado_productos.idProducto
                LEFT JOIN core_unidades_medida  ON core_unidades_medida.idUniMed         = productos_listado.idUniMed',
            'where'   => 'facturacion_listado_productos.idFacturacion = ?',
            'params'  => [$FacturacionID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'facturacion_listado_productos.idExistencia ASC',
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
                facturacion_listado_servicios.Number AS ServicioCantidad,
                facturacion_listado_servicios.ValorTotal AS ServicioValor',
            'table'   => 'facturacion_listado_servicios',
            'join'    => 'LEFT JOIN servicios_listado  ON servicios_listado.idServicio  = facturacion_listado_servicios.idServicio',
            'where'   => 'facturacion_listado_servicios.idFacturacion = ?',
            'params'  => [$FacturacionID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'facturacion_listado_servicios.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams      = ['query' => $query];
        // Ejecuto la query
        $arrServicios = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                facturacion_listado_guias.idFacturacionRel,
                core_documentos_mercantiles.Nombre AS Documento,
                facturacion_listado.N_Doc,
                facturacion_listado.Creacion_fecha,
                facturacion_listado.ValorTotal',
            'table'   => 'facturacion_listado_guias',
            'join'    => '
                LEFT JOIN facturacion_listado          ON facturacion_listado.idFacturacion         = facturacion_listado_guias.idFacturacionRel
                LEFT JOIN core_documentos_mercantiles  ON core_documentos_mercantiles.idDocumentos  = facturacion_listado.idDocumentos',
            'where'   => 'facturacion_listado_guias.idFacturacion = ?',
            'params'  => [$FacturacionID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'facturacion_listado_guias.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $arrGuias = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                usuarios_listado.Nombre AS UsuarioPago,
                core_documentos_pago.Nombre AS DocPago,
                facturacion_listado_pagos.N_Doc,
                facturacion_listado_pagos.MontoPagado,
                facturacion_listado_pagos.FechaPago',
            'table'   => 'facturacion_listado_pagos',
            'join'    => '
                LEFT JOIN usuarios_listado     ON usuarios_listado.idUsuario            = facturacion_listado_pagos.idUsuario
                LEFT JOIN core_documentos_pago ON core_documentos_pago.idDocumentoPago  = facturacion_listado_pagos.idDocumentoPago',
            'where'   => 'facturacion_listado_pagos.idFacturacion = ?',
            'params'  => [$FacturacionID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'facturacion_listado_pagos.idPago ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $arrPagos = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $rowSistema['status'] && $arrItems['status'] && $arrProductos['status'] && $arrServicios['status'] && $arrGuias['status'] && $arrPagos['status']){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $tsrxName),
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
                'arrGuias'         => $arrGuias['data'],
                'arrPagos'         => $arrPagos['data'],
                'Imprimir'         => $Imprimir,
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(4, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Print.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$rowSistema,$arrItems,$arrProductos,$arrServicios,$arrGuias,$arrPagos]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Resumen
    /*******************************************************************/
    public function Resumen($f3, $params, $idTipo){

        /************************************/
        // Se verifica movimiento
        $tsrxName = $this->tsrxName($idTipo);

        /************************************/
        // Se obtiene el ID
        $FacturacionID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($FacturacionID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                facturacion_listado.idFacturacion,
                facturacion_listado.idTipo,
                facturacion_listado.idEntidad,
                facturacion_listado.idBodegasIngreso,
                facturacion_listado.idBodegasEgreso,
                facturacion_listado.idDocumentos,
                facturacion_listado.N_Doc,
                facturacion_listado.Creacion_fecha,
                facturacion_listado.Creacion_hora,
                facturacion_listado.Observaciones,
                facturacion_listado.ValorNeto,
                facturacion_listado.IVA,
                facturacion_listado.ValorTotal,
                facturacion_listado.MontoPagado,
                facturacion_listado.idEstadoPago,
                facturacion_listado.fecha_auto,

                core_facturacion_tipo.Nombre AS TipoFacturacion,
                entidades_listado.idTipoEntidad,
                entidades_listado.Nombre AS EntidadesNombre,
                entidades_listado.ApellidoPat AS EntidadesApellido,
                entidades_listado.RazonSocial AS EntidadesRazonSocial,
                entidades_listado.Nick AS EntidadesNick,
                BodIngreso.Nombre AS BodegaIngreso,
                BodEgreso.Nombre AS BodegaEgreso,
                core_documentos_mercantiles.Nombre AS Documento,
                core_estados_pago.Nombre AS EstadoPago,
                core_estados_pago.Color AS EstadoColor,
                usuarios_listado.Nombre AS Usuario',
            'table'   => 'facturacion_listado',
            'join'    => '
                LEFT JOIN core_facturacion_tipo         ON core_facturacion_tipo.idTipo               = facturacion_listado.idTipo
                LEFT JOIN entidades_listado             ON entidades_listado.idEntidad                = facturacion_listado.idEntidad
                LEFT JOIN bodegas_listado BodIngreso    ON BodIngreso.idBodegas                       = facturacion_listado.idBodegasIngreso
                LEFT JOIN bodegas_listado BodEgreso     ON BodEgreso.idBodegas                        = facturacion_listado.idBodegasEgreso
                LEFT JOIN core_documentos_mercantiles   ON core_documentos_mercantiles.idDocumentos   = facturacion_listado.idDocumentos
                LEFT JOIN core_estados_pago             ON core_estados_pago.idEstadoPago             = facturacion_listado.idEstadoPago
                LEFT JOIN usuarios_listado              ON usuarios_listado.idUsuario                 = facturacion_listado.idUsuario',
            'where'   => 'facturacion_listado.idFacturacion = ?',
            'params'  => [$FacturacionID['data']],
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
            'params'  => [1, $idTipo],
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
            'data'    => 'idDocumentos AS ID,Nombre',
            'table'   => 'core_documentos_mercantiles',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams       = ['query' => $query];
        // Ejecuto la query
        $arrDocumentos = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrEntidades['status'] && $arrDocumentos['status']){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'        => 'Resumen Facturación',
                'PageDescription'  => 'Resumen Facturación.',
                'PageAuthor'       => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'     => ConfigAPP::SOFTWARE['SoftwareName'],
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $tsrxName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'       => $this->FormInputs,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_Codification'     => $this->Codification,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData['data'],
                'arrEntidades'    => $arrEntidades['data'],
                'arrDocumentos'   => $arrDocumentos['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrEntidades,$arrDocumentos]);
            // Despliegue de errores
            $this->showError(1, $f3, $result);
        }
    }

    /*******************************************************************/
    // Resumen Actualizar
    /*******************************************************************/
    public function ResumenUpdate($f3, $params, $idTipo){

        /************************************/
        // Se verifica movimiento
        $tsrxName = $this->tsrxName($idTipo);

        /************************************/
        // Se obtiene el ID
        $FacturacionID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($FacturacionID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                facturacion_listado.idFacturacion,
                facturacion_listado.idTipo,
                facturacion_listado.N_Doc,
                facturacion_listado.Creacion_fecha,
                facturacion_listado.Creacion_hora,
                facturacion_listado.Observaciones,
                facturacion_listado.ValorNeto,
                facturacion_listado.IVA,
                facturacion_listado.ValorTotal,
                facturacion_listado.MontoPagado,

                core_facturacion_tipo.Nombre AS TipoFacturacion,
                entidades_listado.idTipoEntidad,
                entidades_listado.Nombre AS EntidadesNombre,
                entidades_listado.ApellidoPat AS EntidadesApellido,
                entidades_listado.RazonSocial AS EntidadesRazonSocial,
                entidades_listado.Nick AS EntidadesNick,
                BodIngreso.Nombre AS BodegaIngreso,
                BodEgreso.Nombre AS BodegaEgreso,
                core_documentos_mercantiles.Nombre AS Documento,
                core_estados_pago.Nombre AS EstadoPago,
                core_estados_pago.Color AS EstadoColor,
                usuarios_listado.Nombre AS Usuario',
            'table'   => 'facturacion_listado',
            'join'    => '
                LEFT JOIN core_facturacion_tipo         ON core_facturacion_tipo.idTipo               = facturacion_listado.idTipo
                LEFT JOIN entidades_listado             ON entidades_listado.idEntidad                = facturacion_listado.idEntidad
                LEFT JOIN bodegas_listado BodIngreso    ON BodIngreso.idBodegas                       = facturacion_listado.idBodegasIngreso
                LEFT JOIN bodegas_listado BodEgreso     ON BodEgreso.idBodegas                        = facturacion_listado.idBodegasEgreso
                LEFT JOIN core_documentos_mercantiles   ON core_documentos_mercantiles.idDocumentos   = facturacion_listado.idDocumentos
                LEFT JOIN core_estados_pago             ON core_estados_pago.idEstadoPago             = facturacion_listado.idEstadoPago
                LEFT JOIN usuarios_listado              ON usuarios_listado.idUsuario                 = facturacion_listado.idUsuario',
            'where'   => 'facturacion_listado.idFacturacion = ?',
            'params'  => [$FacturacionID['data']],
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
                'UserAccess'    => $this->getArrLevel($f3, $tsrxName),
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
        // Se instancia
        $arrUserData = $this->getUserData($f3);

        /************************************/
        // Variables
        $ndata_1 = isset($_POST['Item_Item'])           ? count($_POST['Item_Item'])            : 0;
        $ndata_2 = isset($_POST['Producto_idProducto']) ? count($_POST['Producto_idProducto'])  : 0;
        $ndata_3 = isset($_POST['Servicio_idServicio']) ? count($_POST['Servicio_idServicio'])  : 0;
        $ndata_4 = isset($_POST['idFacturacionRel'])    ? count($_POST['idFacturacionRel'])     : 0;

        // Variables para validaciones
        $DataVal['Count'] = $ndata_1 + $ndata_2 + $ndata_3 + $ndata_4;
        $DataVal['Msg']   = 'No hay nada ingresado';

        // Validar selección de bodega si hay productos
        if ($ndata_2 != 0 && isset($_POST['idTipo'])) {
            $bodegaSeleccionada = (
                ($_POST['idTipo'] == 1 && !empty($_POST['idBodegasIngreso'])) ||
                ($_POST['idTipo'] == 2 && !empty($_POST['idBodegasEgreso']))
            );
            if (!$bodegaSeleccionada) {
                $DataVal['Count'] = 0;
                $DataVal['Msg']   = 'No ha seleccionado la bodega';
            }
        }

        // Validar que no existan productos repetidos
        if ($ndata_2 != 0) {
            $productosUnicos = array_unique($_POST['Producto_idProducto']);
            if (count($productosUnicos) != $ndata_2) {
                $DataVal['Count'] = 0;
                $DataVal['Msg']   = 'Existen productos repetidos';
            }
        }

        /************************************/
        // Generacion de errores
        if($DataVal['Count']==0) {
            Response::error($DataVal['Msg'], 500);
        }

        /************************************/
        // Se llama al movimiento de materiales
        $Response = $this->createDoc($_POST, $arrUserData);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if($Response['code'] != 200){
            Response::error($Response['message'], $Response['code'], $Response['error'] ?? '');
        }

        /************************************/
        // Si es un ID numérico, encripta y envía con código 200 (OK)
        $DataID = $this->Codification->encryptDecrypt('encrypt', $Response['data']['data']);
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
            'data'      => 'idFacturacion,idUsuario,idTipo,idEntidad,idBodegasIngreso,idBodegasEgreso,fecha_auto,idDocumentos,N_Doc,Creacion_fecha,Creacion_Semana,Creacion_mes,Creacion_ano,Creacion_hora,Observaciones,ValorNeto,IVA,ValorTotal,TotalItems,TotalProductos,TotalServicios,TotalGuias,idEstadoPago,MontoPagado',
            'required'  => 'idFacturacion,idUsuario,idTipo,idEntidad,fecha_auto,idDocumentos,Creacion_fecha,idEstadoPago',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'facturacion_listado',
            'where'     => 'idFacturacion',
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
    public function Delete($f3){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Obtener datos
        parse_str(file_get_contents("php://input"),$dataDelete);

        /************************************/
        // Se instancia
        $arrUserData = $this->getUserData($f3);

        /************************************/
        // Se llama a la eliminacion de movimiento de materiales
        $Response = $this->deleteDoc($dataDelete, $arrUserData);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if($Response['code'] != 200){
            Response::error($Response['message'], $Response['code'], $Response['error'] ?? '');
        }

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data']['data']);

    }


    /******************************************************************************/
    /*                             EJECUCION OTROS                                */
    /******************************************************************************/
    /*******************************************************************/
    // Se crea la facturacion
    /*******************************************************************/
    public function createDoc($PostData, $arrUserData, $NewDBConn = null): array {

        /************************************/
        // Verifico si se ejecuta otro hilo
        $DBConn = $NewDBConn ?? $this->getDBConn();

        /*******************************************************/
        /*                VALIDACIONES INICIALES               */
        /*******************************************************/
        // Datos
        $ndata_1 = isset($PostData['Item_Item'])           ? count($PostData['Item_Item'])            : 0;
        $ndata_2 = isset($PostData['Producto_idProducto']) ? count($PostData['Producto_idProducto'])  : 0;
        $ndata_3 = isset($PostData['Servicio_idServicio']) ? count($PostData['Servicio_idServicio'])  : 0;
        $ndata_4 = isset($PostData['idFacturacionRel'])    ? count($PostData['idFacturacionRel'])     : 0;

        /************************************/
        // Se inicializan Variables
        $x_ValorTotal     = 0;
        $x_TotalItems     = 0;
        $x_TotalProductos = 0;
        $x_TotalServicios = 0;

        /************************************/
        // Se inicia la transacción solo si corresponde
        if($NewDBConn === null){
            $this->Base_transactionBegin(['newBDConn' => $DBConn]);
        }

        /*******************************************************/
        /*                        CALCULOS                     */
        /*******************************************************/
        // Calcular Total Items
        if(isset($ndata_1)&&$ndata_1!=0){
            // Recorro los items
            for($j1 = 0; $j1 < $ndata_1; $j1++){
                $valorTotal    = (float)$PostData['Item_ValorTotal'][$j1];
                $x_ValorTotal += $valorTotal;
                $x_TotalItems += $valorTotal;
            }
        }
        // Calcular Total Productos
        if(isset($ndata_2)&&$ndata_2!=0){
            // Recorro los items
            for($j1 = 0; $j1 < $ndata_2; $j1++){
                $valorTotal        = (float)$PostData['Producto_ValorTotal'][$j1];
                $x_ValorTotal     += $valorTotal;
                $x_TotalProductos += $valorTotal;
            }
        }
        // Calcular Total Servicios
        if(isset($ndata_3)&&$ndata_3!=0){
            // Recorro los items
            for($j1 = 0; $j1 < $ndata_3; $j1++){
                $valorTotal        = (float)$PostData['Servicio_ValorTotal'][$j1];
                $x_ValorTotal     += $valorTotal;
                $x_TotalServicios += $valorTotal;
            }
        }
        // Calcular Total Guias de despacho
        if(isset($ndata_4)&&$ndata_4!=0){
            /************************************/
            // Variable
            $ElementsIDs  = [0];
            // Recorro los productos ingresados
            for($j1 = 0; $j1 < $ndata_4; $j1++){
                //se obtiene el producto (bindeado, no concatenado)
                $ElementsIDs[] = (int)$PostData['idFacturacionRel'][$j1];
            }
            // Genera un '?' por cada id de producto
            $placeholders = implode(',', array_fill(0, count($ElementsIDs), '?'));

            /************************************/
            // Se genera la query
            $query = [
                'data'    => 'SUM(ValorTotal) AS Total',
                'table'   => 'facturacion_listado',
                'join'    => '',
                'where'   => 'idFacturacion IN ('.$placeholders.')',
                'params'  => $ElementsIDs,
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
            //Se suman los totales de las guias
            $x_ValorTotal += (float)$rowData['data']['Total'];

        }

        /*******************************************************/
        /*             GENERAR DATOS DE FACTURACION            */
        /*******************************************************/
        // Se generan datos
        $PostData['ValorNeto']       = ($x_ValorTotal/1.19);
        $PostData['IVA']             = $x_ValorTotal - ($x_ValorTotal/1.19);
        $PostData['ValorTotal']      = $x_ValorTotal;
        $PostData['TotalItems']      = $x_TotalItems;
        $PostData['TotalProductos']  = $x_TotalProductos;
        $PostData['TotalServicios']  = $x_TotalServicios;
        // Se generan las fechas en caso de existir
        if(isset($PostData['Creacion_fecha'])&&$PostData['Creacion_fecha']!=''){
            $PostData['Creacion_Semana']  = $this->DataDate->fecha2NSemana($PostData['Creacion_fecha']);
            $PostData['Creacion_mes']     = $this->DataDate->fecha2NMes($PostData['Creacion_fecha']);
            $PostData['Creacion_ano']     = $this->DataDate->fecha2Ano($PostData['Creacion_fecha']);
        }

        /*******************************************************/
        /*              CREAR DOCUMENTO PRINCIPAL              */
        /*******************************************************/
        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idUsuario,idTipo,idEntidad,idBodegasIngreso,idBodegasEgreso,fecha_auto,idDocumentos,N_Doc,Creacion_fecha,Creacion_Semana,Creacion_mes,Creacion_ano,Creacion_hora,Observaciones,ValorNeto,IVA,ValorTotal,TotalItems,TotalProductos,TotalServicios,idEstadoPago,MontoPagado',
            'required'  => 'idUsuario,idTipo,idEntidad,fecha_auto,idDocumentos,Creacion_fecha,idEstadoPago',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'facturacion_listado',
            'Post'      => $PostData
        ];
        /************************************/
        // Se genera el chequeo
        $dataCheck_1 = $this->dataCheck_1($PostData);
        // Preparo los datos
        $xParams  = ['DataCheck' => $dataCheck_1, 'query' => $query, 'newBDConn' => $DBConn];
        // Ejecuto la query
        $Response = $this->Base_insert($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            $this->Base_transactionRollback(['newBDConn' => $DBConn]);
            return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $Response['error']];
        }

        /*******************************************************/
        /*                 SE AGREGAN DETALLES                 */
        /*******************************************************/
        /************************************/
        // Items
        if(isset($ndata_1)&&$ndata_1!=0){
            // Se acumulan las filas a insertar para evitar un INSERT por cada items (N+1)
            $rowsItems = [];
            // Recorro los items
            for($j1 = 0; $j1 < $ndata_1; $j1++){
                /************************************/
                // Se agrega respuesta
                $rowsItems[] = [
                    'idFacturacion' => $Response['data'],
                    'Item'          => $PostData['Item_Item'][$j1],
                    'Number'        => $PostData['Item_Number'][$j1],
                    'ValorTotal'    => $PostData['Item_ValorTotal'][$j1],
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
                    'data'      => 'idFacturacion,Item,Number,ValorTotal',
                    'required'  => 'idFacturacion,Item,Number,ValorTotal',
                    'table'     => 'facturacion_listado_items',
                    'rows'      => $rowsItems
                ];
                // Preparo los datos
                $xParams   = ['DataCheck' => $DataCheck, 'query' => $query, 'newBDConn' => $DBConn];
                // Ejecuto la query
                $respItems = $this->Base_insertMultiple($xParams);
                /************************************/
                // Si falla la ejecucion, se revierte de inmediato
                if ($respItems['status'] === false) {
                    $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                    return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $respItems['error']];
                }
            }
        }

        /************************************/
        // Productos
        if(isset($ndata_2)&&$ndata_2!=0){
            // Se acumulan las filas a insertar para evitar un INSERT por cada productos (N+1)
            $rowsProductos = [];
            /************************************/
            // Determinar la bodega según el tipo
            $idBodegas = 0;
            if (isset($PostData['idTipo'])) {
                if ($PostData['idTipo'] == 1 && isset($PostData['idBodegasIngreso'])) {
                    $idBodegas = $PostData['idBodegasIngreso'];
                } elseif ($PostData['idTipo'] == 2 && isset($PostData['idBodegasEgreso'])) {
                    $idBodegas = $PostData['idBodegasEgreso'];
                }
            }

            /************************************/
            // Recorro los items
            for($j1 = 0; $j1 < $ndata_2; $j1++){
                /************************************/
                // Se agrega respuesta
                $rowsProductos[] = [
                    'idFacturacion'   => $Response['data'],
                    'idEstadoIngreso' => $PostData['idTipo'],
                    'idBodegas'       => $idBodegas,
                    'idProducto'      => $PostData['Producto_idProducto'][$j1],
                    'Number'          => $PostData['Producto_Number'][$j1],
                    'ValorTotal'      => $PostData['Producto_ValorTotal'][$j1],
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
                    'data'      => 'idFacturacion,idEstadoIngreso,idBodegas,idProducto,Number,ValorTotal',
                    'required'  => 'idFacturacion,idEstadoIngreso,idBodegas,idProducto,Number,ValorTotal',
                    'table'     => 'facturacion_listado_productos',
                    'rows'      => $rowsProductos
                ];
                // Preparo los datos
                $xParams       = ['DataCheck' => $DataCheck, 'query' => $query, 'newBDConn' => $DBConn];
                // Ejecuto la query
                $respProductos = $this->Base_insertMultiple($xParams);
                /************************************/
                // Si falla la ejecucion, se revierte de inmediato
                if ($respProductos['status'] === false) {
                    $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                    return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $respProductos['error']];
                }
            }
            /************************************/
            //Movimiento de bodegas
            //permite la interaccion con la bodega, para generar documentos de ingreso o egreso
            if($arrUserData["gestionDocumentosUsoBodega"]==2){
                // Variable
                $PostMovProd = array();
                // Se generan los datos
                $PostMovProd['idEstadoIngreso']  = $PostData['idTipo'];
                $PostMovProd['idBodegasIngreso'] = (isset($PostData['idBodegasIngreso']) && $PostData['idBodegasIngreso'] !== '' ? $PostData['idBodegasIngreso'] : '');
                $PostMovProd['idBodegasEgreso']  = (isset($PostData['idBodegasEgreso']) && $PostData['idBodegasEgreso'] !== '' ? $PostData['idBodegasEgreso'] : '');
                $PostMovProd['Creacion_fecha']   = $PostData['Creacion_fecha'];
                $PostMovProd['Creacion_hora']    = $PostData['Creacion_hora'];
                $PostMovProd['Observaciones']    = 'Movimiento generado desde una facturacion';
                $PostMovProd['fecha_auto']       = $PostData['fecha_auto'];
                $PostMovProd['idUsuario']        = $PostData['idUsuario'];
                $PostMovProd['idFacturacion']    = $Response['data'];
                // Productos
                $PostMovProd['idProducto']       = $PostData['Producto_idProducto'];
                $PostMovProd['Number']           = $PostData['Producto_Number'];

                /************************************/
                // Se instancia
                $bodegasMovimiento = new bodegasMovimiento();
                $bodegasMov        = $bodegasMovimiento->createMov($PostMovProd, $DBConn);
                // Si falla la ejecucion, se revierte de inmediato
                if($bodegasMov['code'] != 200){
                    $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                    return $bodegasMov;
                }

            }
        }

        /************************************/
        // Servicios
        if(isset($ndata_3)&&$ndata_3!=0){
            // Se acumulan las filas a insertar para evitar un INSERT por cada servicios (N+1)
            $rowsServicios = [];
            // Recorro los items
            for($j1 = 0; $j1 < $ndata_3; $j1++){
                /************************************/
                // Se agrega respuesta
                $rowsServicios[] = [
                    'idFacturacion' => $Response['data'],
                    'idServicio'    => $PostData['Servicio_idServicio'][$j1],
                    'Number'        => $PostData['Servicio_Number'][$j1],
                    'ValorTotal'    => $PostData['Servicio_ValorTotal'][$j1],
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
                    'data'      => 'idFacturacion,idServicio,Number,ValorTotal',
                    'required'  => 'idFacturacion,idServicio,Number,ValorTotal',
                    'table'     => 'facturacion_listado_servicios',
                    'rows'      => $rowsServicios
                ];
                // Preparo los datos
                $xParams       = ['DataCheck' => $DataCheck, 'query' => $query, 'newBDConn' => $DBConn];
                // Ejecuto la query
                $respServicios = $this->Base_insertMultiple($xParams);
                /************************************/
                // Si falla la ejecucion, se revierte de inmediato
                if ($respServicios['status'] === false) {
                    $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                    return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $respServicios['error']];
                }
            }
        }

        /************************************/
        //Guias
        if(isset($ndata_4)&&$ndata_4!=0){
            // Recorro los items
            for($j1 = 0; $j1 < $ndata_4; $j1++){
                /************************************/
                // Se agrega respuesta
                $arrTareas = [
                    'idFacturacion'    => $Response['data'],
                    'idFacturacionRel' => $PostData['idFacturacionRel'][$j1],
                ];
                /************************************/
                // Se genera la query
                $query = [
                    'data'      => 'idFacturacion,idFacturacionRel',
                    'required'  => 'idFacturacion,idFacturacionRel',
                    'unique'    => '',
                    'encode'    => '',
                    'table'     => 'facturacion_listado_guias',
                    'Post'      => $arrTareas
                ];
                /************************************/
                // Se genera el chequeo
                $dataCheck_3 = $this->dataCheck_3($arrTareas);
                // Preparo los datos
                $xParams       = ['DataCheck' => $dataCheck_3, 'query' => $query, 'newBDConn' => $DBConn];
                // Ejecuto la query
                $ResponseGuias = $this->Base_insert($xParams);

                /************************************/
                // Si falla la ejecucion, se revierte de inmediato
                if ($ResponseGuias['status'] === false) {
                    $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                    return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $ResponseGuias['error']];
                }

                /************************************/
                // Se actualiza la relacion con la guia
                $arrTareas = [
                    'idFacturacion' => $PostData['idFacturacionRel'][$j1], // Documento DTE
                    'idEstadoPago'  => 2,                                  // Utilizado
                ];
                /************************************/
                // Se genera la query
                $query = [
                    'data'      => 'idFacturacion,idEstadoPago',
                    'required'  => 'idFacturacion,idEstadoPago',
                    'unique'    => '',
                    'encode'    => '',
                    'table'     => 'facturacion_listado',
                    'where'     => 'idFacturacion',
                    'Post'      => $arrTareas
                ];
                /************************************/
                // Se genera el chequeo
                $dataCheck_4 = $this->dataCheck_4($arrTareas);
                // Preparo los datos
                $xParams = ['DataCheck' => $dataCheck_4, 'query' => $query, 'newBDConn' => $DBConn];
                // Ejecuto la query
                $xUpdate = $this->Base_update($xParams);

                /************************************/
                // Si falla la ejecucion, se revierte de inmediato
                if ($xUpdate['status'] === false) {
                    $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                    return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $xUpdate['error']];
                }
            }
        }

        /************************************/
        // Confirmar transacción solo si corresponde
        if($NewDBConn === null){
            $this->Base_transactionCommit(['newBDConn' => $DBConn]);
        }

        /************************************/
        // Retorno los datos
        return ['code' => 200, 'data' => $Response];

    }

    /*******************************************************************/
    // Se crea la facturacion
    /*******************************************************************/
    public function deleteDoc($PostData, $arrUserData, $NewDBConn = null): array {

        /************************************/
        // Verifico si se ejecuta otro hilo
        $DBConn = $NewDBConn ?? $this->getDBConn();

        /************************************/
        // Se obtiene el ID
        $FacturacionID = $this->Codification->encryptDecrypt('decrypt', $PostData['idFacturacion']);
        if (!$this->isValidDecrypted($FacturacionID, 'id')) {
            return ['code' => 400, 'message' => 'Registro inválido'];
        }

        /************************************/
        // Se inicia la transacción solo si corresponde
        if($NewDBConn === null){
            $this->Base_transactionBegin(['newBDConn' => $DBConn]);
        }

        /*******************************************************/
        /*            ELIMINACION GUIA RELACIONADA             */
        /*******************************************************/
        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idFacturacion,idFacturacionRel',
            'table'   => 'facturacion_listado_guias',
            'join'    => '',
            'where'   => 'idFacturacion = ?',
            'params'  => [$FacturacionID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'idFacturacionRel ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams   = ['query' => $query, 'newBDConn' => $DBConn];
        // Ejecuto la query
        $arrGuias  = $this->Base_GetList($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($arrGuias['status'] === false) {
            $this->Base_transactionRollback(['newBDConn' => $DBConn]);
            return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $arrGuias['error']];
        }

        /*******************************************************/
        /*             ELIMINACION DOCUMENTO DTE               */
        /*******************************************************/
        /************************************/
        // Se genera la query
        $query = [
            'files'       => '',
            'table'       => 'facturacion_listado',
            'where'       => 'idFacturacion',
            'SubCarpeta'  => '',
            'Post'        => $PostData
        ];
        // Preparo los datos
        $xParams  = ['query' => $query, 'newBDConn' => $DBConn];
        // Ejecuto la query
        $Response = $this->Base_delete($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            $this->Base_transactionRollback(['newBDConn' => $DBConn]);
            return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $Response['error']];
        }

        /************************************/
        // Listado de las tablas a eliminar los datos relacionados
        $arrTableDel  = array();
        $arrTableDel[] = ['files' => '', 'table' => 'facturacion_listado_guias'];
        $arrTableDel[] = ['files' => '', 'table' => 'facturacion_listado_items'];
        $arrTableDel[] = ['files' => '', 'table' => 'facturacion_listado_productos'];
        $arrTableDel[] = ['files' => '', 'table' => 'facturacion_listado_servicios'];
        $arrTableDel[] = ['files' => '', 'table' => 'facturacion_listado_pagos'];

        /************************************/
        // Verifico si existe
        if (!empty($arrTableDel)) {
            // Recorro
            foreach ($arrTableDel as $tblDel) {
                /************************************/
                // Se genera la query
                $query = ['files' => $tblDel['files'], 'table' => $tblDel['table'], 'where' => 'idFacturacion', 'SubCarpeta' => '', 'Post' => $PostData];
                // Preparo los datos
                $xParams    = ['query' => $query, 'newBDConn' => $DBConn];
                // Ejecuto la query
                $respDelRel = $this->Base_delete($xParams);
                /************************************/
                // Si falla la ejecucion, se revierte de inmediato
                if ($respDelRel['status'] === false) {
                    $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                    return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $Response['error']];
                }
            }
        }

        /************************************/
        // Eliminacion de la GUIA
        if ($arrGuias['status'] === true) {
            // Recorro los datos
            foreach($arrGuias['data'] as $guias){
                /************************************/
                // Se actualiza la relacion con la guia
                $arrTareas = [
                    'idFacturacion' => $guias['idFacturacionRel'], // Documento DTE
                    'idEstadoPago'  => 1,                          // No utilizado
                ];
                /************************************/
                // Se genera la query
                $query = [
                    'data'      => 'idFacturacion,idEstadoPago',
                    'required'  => 'idFacturacion,idEstadoPago',
                    'unique'    => '',
                    'encode'    => '',
                    'table'     => 'facturacion_listado',
                    'where'     => 'idFacturacion',
                    'Post'      => $arrTareas
                ];
                /************************************/
                // Se genera el chequeo
                $dataCheck_4 = $this->dataCheck_4($arrTareas);
                // Preparo los datos
                $xParams = ['DataCheck' => $dataCheck_4, 'query' => $query, 'newBDConn' => $DBConn];
                // Ejecuto la query
                $xUpdate = $this->Base_update($xParams);

                /************************************/
                // Si falla la ejecucion, se revierte de inmediato
                if ($xUpdate['status'] === false) {
                    $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                    return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $xUpdate['error']];
                }
            }
        }

        /**********************************************************************/
        //Movimiento de bodegas
        //permite la interaccion con la bodega, para generar documentos de ingreso o egreso
        if($arrUserData["gestionDocumentosUsoBodega"]==2){

            /************************************/
            // Datos del movimiento
            $query = [
                'data'    => 'idMovimiento',
                'table'   => 'bodegas_movimientos',
                'join'    => '',
                'where'   => 'idFacturacion = ?',
                'params'  => [$FacturacionID['data']],
                'group'   => '',
                'having'  => '',
                'order'   => ''
            ];
            // Preparo los datos
            $xParams       = ['query' => $query, 'newBDConn' => $DBConn];
            // Ejecuto la query
            $rowMovimiento = $this->Base_GetByID($xParams);

            /************************************/
            // Se obtiene el ID
            $MovimientoID_Del = $this->Codification->encryptDecrypt('encrypt', $rowMovimiento['data']['idMovimiento']);
            if (!$this->isValidDecrypted($MovimientoID_Del, 'text')) {
                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                return ['code' => 400, 'message' => 'Registro inválido'];
            }
            // Se agrega respuesta
            $arrTareas = ['idMovimiento' => $MovimientoID_Del['data'],];

            /************************************/
            // Se instancia
            $bodegasMovimiento = new bodegasMovimiento();
            $bodegasMov        = $bodegasMovimiento->removeMov($arrTareas, $DBConn);
            // Si falla la ejecucion, se revierte de inmediato
            if($bodegasMov['code'] != 200){
                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                return $bodegasMov;
            }

        }

        /************************************/
        // Confirmar transacción solo si corresponde
        if($NewDBConn === null){
            $this->Base_transactionCommit(['newBDConn' => $DBConn]);
        }

        /************************************/
        // Retorno los datos
        return ['code' => 200, 'data' => $Response];

    }

    /*******************************************************************/
    // Se actualizan los montos
    /*******************************************************************/
    public function updateFact($Tipo, $FacturacionID, $NewDBConn = null, $EstadoPagoID = 0, $FacturacionRelID = 0): array{

        /************************************/
        // Verifico si se ejecuta otro hilo
        $DBConn = $NewDBConn ?? $this->getDBConn();

        /************************************/
        // Se abre cadena
        $Data = 'facturacion_listado.idFacturacion';

        /************************************/
        // Se cambia la query dependiendo de el tipo
        switch ($Tipo) {
            /************************************/
            // Items
            case 1:
                $Data .= ',
                facturacion_listado.TotalProductos,
                facturacion_listado.TotalServicios,
                facturacion_listado.TotalGuias,
                (SELECT SUM(ValorTotal) FROM facturacion_listado_items     WHERE idFacturacion='.$FacturacionID.') AS TotalItems';
                break;
            /************************************/
            // Productos
            case 2:
                $Data .= ',
                facturacion_listado.TotalItems,
                facturacion_listado.TotalServicios,
                facturacion_listado.TotalGuias,
                (SELECT SUM(ValorTotal) FROM facturacion_listado_productos WHERE idFacturacion='.$FacturacionID.') AS TotalProductos';
                break;
            /************************************/
            // Servicios
            case 3:
                $Data .= ',
                facturacion_listado.TotalItems,
                facturacion_listado.TotalProductos,
                facturacion_listado.TotalGuias,
                (SELECT SUM(ValorTotal) FROM facturacion_listado_servicios WHERE idFacturacion='.$FacturacionID.') AS TotalServicios';
                break;
            /************************************/
            //Guias
            case 4:
                $Data .= ',
                facturacion_listado.TotalItems,
                facturacion_listado.TotalProductos,
                facturacion_listado.TotalServicios,
                (SELECT SUM(facturacion_listado.ValorTotal) FROM facturacion_listado_guias LEFT JOIN facturacion_listado ON facturacion_listado.idFacturacion = facturacion_listado_guias.idFacturacionRel WHERE facturacion_listado_guias.idFacturacion='.$FacturacionID.') AS TotalGuias';
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
            'table'   => 'facturacion_listado',
            'join'    => '',
            'where'   => 'idFacturacion = ?',
            'params'  => [$FacturacionID],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query, 'newBDConn' => $DBConn];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($rowData['status'] === false) {
            $this->Base_transactionRollback(['newBDConn' => $DBConn]);
            return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $rowData['error']];
        }

        /************************************/
        // Calculo
        $x_ValorTotal = ($rowData['data']['TotalItems'] ?? 0) + ($rowData['data']['TotalProductos'] ?? 0) + ($rowData['data']['TotalServicios'] ?? 0) + ($rowData['data']['TotalGuias'] ?? 0);
        // Se agrega respuesta
        $arrTareas = [
            'idFacturacion'   => $FacturacionID,
            'ValorNeto'       => ($x_ValorTotal/1.19),
            'IVA'             => $x_ValorTotal - ($x_ValorTotal/1.19),
            'ValorTotal'      => $x_ValorTotal,
            'TotalItems'      => $rowData['data']['TotalItems'] ?? 0,
            'TotalProductos'  => $rowData['data']['TotalProductos'] ?? 0,
            'TotalServicios'  => $rowData['data']['TotalServicios'] ?? 0,
            'TotalGuias'      => $rowData['data']['TotalGuias'] ?? 0,
        ];
        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idFacturacion,ValorNeto,IVA,ValorTotal,TotalItems,TotalProductos,TotalServicios,TotalGuias',
            'required'  => 'idFacturacion,ValorNeto,IVA,ValorTotal,TotalItems,TotalProductos,TotalServicios,TotalGuias',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'facturacion_listado',
            'where'     => 'idFacturacion',
            'Post'      => $arrTareas
        ];
        /************************************/
        // Se genera el chequeo
        $dataCheck_5 = $this->dataCheck_5($arrTareas);
        // Preparo los datos
        $xParams  = ['DataCheck' => $dataCheck_5, 'query' => $query, 'newBDConn' => $DBConn];
        // Ejecuto la query
        $Response = $this->Base_update($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            $this->Base_transactionRollback(['newBDConn' => $DBConn]);
            return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $Response['error']];
        }

        /************************************/
        // Verifico si hay datos
        if ($EstadoPagoID != 0 && $FacturacionRelID != 0){
            /************************************/
            //Se acambia el estado
            $arrTareas = [
                'idFacturacion' => $FacturacionRelID,
                'idEstadoPago'  => $EstadoPagoID,
            ];
            /************************************/
            // Se genera la query
            $query = [
                'data'      => 'idFacturacion,idEstadoPago',
                'required'  => 'idFacturacion,idEstadoPago',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'facturacion_listado',
                'where'     => 'idFacturacion',
                'Post'      => $arrTareas
            ];
            /************************************/
            // Se genera el chequeo
            $dataCheck_6 = $this->dataCheck_6($arrTareas);
            // Preparo los datos
            $xParams  = ['DataCheck' => $dataCheck_6, 'query' => $query, 'newBDConn' => $DBConn];
            // Ejecuto la query
            $Response = $this->Base_update($xParams);

            /************************************/
            // Si falla la ejecucion, se revierte de inmediato
            if ($Response['status'] === false) {
                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $Response['error']];
            }
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
            'ValidarNumero'             => 'idUsuario,idTipo,idEntidad,idBodegasIngreso,idBodegasEgreso,idDocumentos,N_Doc,Creacion_Semana,Creacion_mes,Creacion_ano,ValorNeto,IVA,ValorTotal,TotalItems,TotalProductos,TotalServicios,idEstadoPago,MontoPagado',
            'ValidarEntero'             => 'idUsuario,idTipo,idEntidad,idBodegasIngreso,idBodegasEgreso,idDocumentos,idEstadoPago',
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
            'ValidarNumero'             => 'idFacturacion,idEstadoIngreso,idBodegas,idProducto,idServicio,Number,ValorTotal',
            'ValidarEntero'             => 'idFacturacion,idEstadoIngreso,idBodegas,idProducto,idServicio',
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
            'ValidarNumero'             => 'idFacturacion,idFacturacionRel',
            'ValidarEntero'             => 'idFacturacion,idFacturacionRel',
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

    /*******************************************************************/
    // Se validan los datos
    /*******************************************************************/
    private function dataCheck_4($POST){
        // Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => '',
            'ValidarNumero'             => 'idFacturacion,idEstadoPago',
            'ValidarEntero'             => 'idFacturacion,idEstadoPago',
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

    /*******************************************************************/
    // Se validan los datos
    /*******************************************************************/
    private function dataCheck_5($POST){
        // Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => '',
            'ValidarNumero'             => 'idFacturacion,ValorNeto,IVA,ValorTotal,TotalItems,TotalProductos,TotalServicios,TotalGuias',
            'ValidarEntero'             => 'idFacturacion',
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

    /*******************************************************************/
    // Se validan los datos
    /*******************************************************************/
    private function dataCheck_6($POST){
        // Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => '',
            'ValidarNumero'             => 'idFacturacion,idEstadoPago,ValorNeto,IVA,ValorTotal,TotalItems,TotalProductos,TotalServicios,TotalGuias',
            'ValidarEntero'             => 'idFacturacion,idEstadoPago',
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

    /*******************************************************************/
    // Se validan los datos
    /*******************************************************************/
    private function tsrxName(int $idTipo): string{
        // Normalizar y mapear tipo a nombre de permiso (más eficiente que switch)
        $tsrxMap = [
            1 => 'gestionDocumentosCompras',
            2 => 'gestionDocumentosVentas'
        ];
        // Por defecto usar ventas si no viene un tipo válido
        return $tsrxMap[$idTipo] ?? $tsrxMap[2];
    }

    /*******************************************************************/
    // Se validan los datos
    /*******************************************************************/
    private function TipoMov(int $idTipo): string{
        // Normalizar y mapear tipo a nombre de permiso (más eficiente que switch)
        $tsrxMap = [
            1 => 'Compras',
            2 => 'Ventas'
        ];
        // Por defecto usar ventas si no viene un tipo válido
        return $tsrxMap[$idTipo] ?? $tsrxMap[2];
    }

}
