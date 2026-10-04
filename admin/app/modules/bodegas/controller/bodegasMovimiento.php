<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class bodegasMovimiento extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataNumbers;
    private $DataDate;
    private $ServerServer;
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
        $this->controllerName = 'bodegasMovimiento';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->DataNumbers    = new FunctionsDataNumbers();
		$this->DataDate       = new FunctionsDataDate();
		$this->ServerServer   = new FunctionsServerServer();
		$this->WidgetsCommon  = new UIWidgetsCommon();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                   RUTAS                                    */
    /******************************************************************************/
    /*******************************************************************/
    // Listar
    /*******************************************************************/
    public function listAll_1($f3){$this->listAll($f3, 1);}
    public function listAll_2($f3){$this->listAll($f3, 2);}
    public function listAll_3($f3){$this->listAll($f3, 3);}
    // Listar Todo
    public function UpdateList_1($f3){$this->UpdateList($f3, 1);}
    public function UpdateList_2($f3){$this->UpdateList($f3, 2);}
    public function UpdateList_3($f3){$this->UpdateList($f3, 3);}
    //View
    public function View_1($f3, $params){$this->View($f3, $params, 1);}
    public function View_2($f3, $params){$this->View($f3, $params, 2);}
    public function View_3($f3, $params){$this->View($f3, $params, 3);}
    //Resumen
    public function Resumen_1($f3, $params){$this->Resumen($f3, $params, 1);}
    public function Resumen_2($f3, $params){$this->Resumen($f3, $params, 2);}
    public function Resumen_3($f3, $params){$this->Resumen($f3, $params, 3);}
    //Resumen-Update
    public function ResumenUpdate_1($f3, $params){$this->ResumenUpdate($f3, $params, 1);}
    public function ResumenUpdate_2($f3, $params){$this->ResumenUpdate($f3, $params, 2);}
    public function ResumenUpdate_3($f3, $params){$this->ResumenUpdate($f3, $params, 3);}

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /*******************************************************************/
    // Listar
    /*******************************************************************/
    public function listAll($f3, $idTipoIngreso){

        /************************************/
        // Se verifica movimiento
        switch ($idTipoIngreso) {
            case 1: $tsrxName = 'bodegasMovimientoIngreso';  $TipoMov = 'Ingresos a';    break;//Ingreso
            case 2: $tsrxName = 'bodegasMovimientoEgreso';   $TipoMov = 'Egresos a';     break;//Egreso
            case 3: $tsrxName = 'bodegasMovimientoTraspaso'; $TipoMov = 'Traspasos de';  break;//Traspaso
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                bodegas_movimientos.idMovimiento,
                bodegas_movimientos.Creacion_fecha,
                bodegas_movimientos.Creacion_hora,
                bodegas_movimientos.Observaciones,
                BodIngreso.Nombre AS BodegaIngreso,
                BodEgreso.Nombre AS BodegaEgreso',
            'table'   => 'bodegas_movimientos',
            'join'    => '
                LEFT JOIN bodegas_listado BodIngreso  ON BodIngreso.idBodegas  = bodegas_movimientos.idBodegasIngreso
                LEFT JOIN bodegas_listado BodEgreso   ON BodEgreso.idBodegas   = bodegas_movimientos.idBodegasEgreso',
            'where'   => 'bodegas_movimientos.idEstadoIngreso = ?',
            'params'  => [$idTipoIngreso],
            'group'   => '',
            'having'  => '',
            'order'   => 'bodegas_movimientos.Creacion_fecha DESC, bodegas_movimientos.Creacion_hora DESC',
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
            $X_join   = '';
            $X_where  = 'bodegas_listado.idEstado = ?';
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
        if($arrList['status'] && $arrBodegas['status'] && $arrProductos['status'] && $arrDocumentos['status']){

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => $TipoMov.' Bodegas',
                'PageDescription' => $TipoMov.' Bodegas.',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => $TipoMov.' Bodegas',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $tsrxName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_Codification'    => $this->Codification,
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                'Fnc_ServerServer'    => $this->ServerServer,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList['data'],
                'arrBodegas'      => $arrBodegas['data'],
                'arrProductos'    => $arrProductos['data'],
                'arrDocumentos'   => $arrDocumentos['data'],
                'idTipoIngreso'   => $idTipoIngreso,

            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrList,$arrBodegas,$arrProductos,$arrDocumentos]);
            // Despliegue de errores
            $this->showError(1, $f3, $result);
        }
    }

    /*******************************************************************/
    // Actualizar Listar
    /*******************************************************************/
    public function UpdateList($f3, $idTipoIngreso){

        /************************************/
        // Se verifica movimiento
        switch ($idTipoIngreso) {
            case 1: $tsrxName = 'bodegasMovimientoIngreso';  $TipoMov = 'Ingresos a';    break;//Ingreso
            case 2: $tsrxName = 'bodegasMovimientoEgreso';   $TipoMov = 'Egresos a';     break;//Egreso
            case 3: $tsrxName = 'bodegasMovimientoTraspaso'; $TipoMov = 'Traspasos de';  break;//Traspaso
        }

        /************************************/
        // Variables
        $WhereData_bod_int     = 'idMovimiento,idBodegasIngreso,idBodegasEgreso';   // Datos búsqueda exacta
        $WhereData_bod_string  = '';                                                // Datos búsqueda relativa
        $WhereData_bod_between = 'Creacion_fecha-F_Inicio-F_Termino';               // Datos búsqueda Between
        $WhereData_fac_int     = 'idDocumentos,idFacturacion';                      // Datos búsqueda exacta
        $WhereData_fac_string  = 'N_Doc';                                           // Datos búsqueda relativa
        $WhereData_fac_between = '';                                                // Datos búsqueda Between
        $whereInt              = '';                                                // Se crea cadena
        $whereParams           = [];                                                // Valores bindeados asociados a $whereInt
        /************************************/
        // Se validan las fechas
        $RespDataBetween = $this->searchValidateDates($WhereData_bod_between);
        if($RespDataBetween!=''){
            Response::error($RespDataBetween, 500);
        }
        /************************************/
        // Agrego variable busqueda
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_bod_int, 'bodegas_movimientos', 1);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_bod_string, 'bodegas_movimientos', 2);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_bod_between, 'bodegas_movimientos', 3);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_fac_int, 'facturacion_listado', 1);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_fac_string, 'facturacion_listado', 2);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_fac_between, 'facturacion_listado', 3);
        $whereInt = $r['where']; $whereParams = $r['params'];
        // Verifico si esta vacio
        $whereInt   .= ($whereInt ? ' AND ' : '') . 'bodegas_movimientos.idEstadoIngreso = ?';
        $whereParams = array_merge($whereParams, [$idTipoIngreso]);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                bodegas_movimientos.idMovimiento,
                bodegas_movimientos.Creacion_fecha,
                bodegas_movimientos.Creacion_hora,
                bodegas_movimientos.Observaciones,
                BodIngreso.Nombre AS BodegaIngreso,
                BodEgreso.Nombre AS BodegaEgreso',
            'table'   => 'bodegas_movimientos',
            'join'    => '
                LEFT JOIN bodegas_listado BodIngreso  ON BodIngreso.idBodegas                = bodegas_movimientos.idBodegasIngreso
                LEFT JOIN bodegas_listado BodEgreso   ON BodEgreso.idBodegas                 = bodegas_movimientos.idBodegasEgreso
                LEFT JOIN facturacion_listado         ON facturacion_listado.idFacturacion   = bodegas_movimientos.idFacturacion',
            'where'   => $whereInt,
            'params'  => $whereParams,
            'group'   => '',
            'having'  => '',
            'order'   => 'bodegas_movimientos.Creacion_fecha DESC, bodegas_movimientos.Creacion_hora DESC',
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
                'TableTitle'      => $TipoMov.' Bodegas',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $tsrxName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'     => $this->Codification,
                'Fnc_DataDate'         => $this->DataDate,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList['data'],
                'idTipoIngreso'   => $idTipoIngreso,
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
    public function View($f3, $params, $idTipoIngreso){

        /************************************/
        // Se instancia
        $arrUserData = $this->getUserData($f3);

        /************************************/
        // Se obtiene el ID
        $MovimientoID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($MovimientoID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se verifica movimiento
        switch ($idTipoIngreso) {
            case 1: $tsrxName = 'bodegasMovimientoIngreso'; break;//Ingreso
            case 2: $tsrxName = 'bodegasMovimientoEgreso';break;//Egreso
            case 3: $tsrxName = 'bodegasMovimientoTraspaso';break;//Traspaso
        }

        /************************************/
        // Se crean cadenas
        $DataQuery = '
        bodegas_movimientos.idMovimiento,
        bodegas_movimientos.idEstadoIngreso,
        bodegas_movimientos.Creacion_fecha,
        bodegas_movimientos.Creacion_hora,
        bodegas_movimientos.Observaciones,

        core_estados_ingreso.Nombre AS TipoMovimiento,
        BodIngreso.Nombre AS BodegaIngreso,
        BodEgreso.Nombre AS BodegaEgreso,
        usuarios_listado.Nombre AS UsuarioNombre';
        $DataJoin = '
        LEFT JOIN core_estados_ingreso        ON core_estados_ingreso.idEstadoIngreso  = bodegas_movimientos.idEstadoIngreso
        LEFT JOIN bodegas_listado BodIngreso  ON BodIngreso.idBodegas                  = bodegas_movimientos.idBodegasIngreso
        LEFT JOIN bodegas_listado BodEgreso   ON BodEgreso.idBodegas                   = bodegas_movimientos.idBodegasEgreso
        LEFT JOIN usuarios_listado            ON usuarios_listado.idUsuario            = bodegas_movimientos.idUsuario';

        //permite la interaccion con la bodega, para generar documentos de ingreso o egreso
        if($arrUserData["gestionDocumentosUsoBodega"]==2){
            $DataQuery .= '
            ,bodegas_movimientos.idFacturacion
            ,facturacion_listado.N_Doc
            ,facturacion_listado.idTipo
            ,core_documentos_mercantiles.Nombre AS Documento';
            $DataJoin  .= '
            LEFT JOIN facturacion_listado           ON facturacion_listado.idFacturacion          = bodegas_movimientos.idFacturacion
            LEFT JOIN core_documentos_mercantiles   ON core_documentos_mercantiles.idDocumentos   = facturacion_listado.idDocumentos';
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => $DataQuery,
            'table'   => 'bodegas_movimientos',
            'join'    => $DataJoin,
            'where'   => 'bodegas_movimientos.idMovimiento = ?',
            'params'  => [$MovimientoID['data']],
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
                core_estados_ingreso.Nombre AS TipoMovimiento,
                bodegas_listado.Nombre AS Bodega,
                productos_listado.Nombre AS ProductoNombre,
                bodegas_movimientos_productos.Number AS ProductoCantidad,
                core_unidades_medida.Nombre AS UnidadMedida',
            'table'   => 'bodegas_movimientos_productos',
            'join'    => '
                LEFT JOIN core_estados_ingreso  ON core_estados_ingreso.idEstadoIngreso  = bodegas_movimientos_productos.idEstadoIngreso
                LEFT JOIN bodegas_listado       ON bodegas_listado.idBodegas             = bodegas_movimientos_productos.idBodegas
                LEFT JOIN productos_listado     ON productos_listado.idProducto          = bodegas_movimientos_productos.idProducto
                LEFT JOIN core_unidades_medida  ON core_unidades_medida.idUniMed         = productos_listado.idUniMed',
            'where'   => 'bodegas_movimientos_productos.idMovimiento = ?',
            'params'  => [$MovimientoID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'bodegas_movimientos_productos.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams      = ['query' => $query];
        // Ejecuto la query
        $arrProductos = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrProductos['status']){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $tsrxName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_Codification'     => $this->Codification,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
                'arrProductos'     => $arrProductos['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-View.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrProductos]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Resumen
    /*******************************************************************/
    public function Resumen($f3, $params, $idTipoIngreso){

        /************************************/
        // Se instancia
        $arrUserData = $this->getUserData($f3);

        /************************************/
        // Se obtiene el ID
        $MovimientoID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($MovimientoID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se verifica movimiento
        switch ($idTipoIngreso) {
            case 1: $tsrxName = 'bodegasMovimientoIngreso'; break;//Ingreso
            case 2: $tsrxName = 'bodegasMovimientoEgreso';break;//Egreso
            case 3: $tsrxName = 'bodegasMovimientoTraspaso';break;//Traspaso
        }

        /************************************/
        // Se crean cadenas
        $DataQuery = '
        bodegas_movimientos.idMovimiento,
        bodegas_movimientos.idEstadoIngreso,
        bodegas_movimientos.idBodegasIngreso,
        bodegas_movimientos.idBodegasEgreso,
        bodegas_movimientos.Creacion_fecha,
        bodegas_movimientos.Creacion_hora,
        bodegas_movimientos.Observaciones,
        bodegas_movimientos.fecha_auto,

        core_estados_ingreso.Nombre AS TipoMovimiento,
        BodIngreso.Nombre AS BodegaIngreso,
        BodEgreso.Nombre AS BodegaEgreso,
        usuarios_listado.Nombre AS UsuarioNombre';
        $DataJoin = '
        LEFT JOIN core_estados_ingreso        ON core_estados_ingreso.idEstadoIngreso  = bodegas_movimientos.idEstadoIngreso
        LEFT JOIN bodegas_listado BodIngreso  ON BodIngreso.idBodegas                  = bodegas_movimientos.idBodegasIngreso
        LEFT JOIN bodegas_listado BodEgreso   ON BodEgreso.idBodegas                   = bodegas_movimientos.idBodegasEgreso
        LEFT JOIN usuarios_listado            ON usuarios_listado.idUsuario            = bodegas_movimientos.idUsuario';

        //permite la interaccion con la bodega, para generar documentos de ingreso o egreso
        if($arrUserData["gestionDocumentosUsoBodega"]==2){
            $DataQuery .= '
            ,bodegas_movimientos.idFacturacion
            ,facturacion_listado.N_Doc
            ,facturacion_listado.idTipo
            ,core_documentos_mercantiles.Nombre AS Documento';
            $DataJoin  .= '
            LEFT JOIN facturacion_listado           ON facturacion_listado.idFacturacion          = bodegas_movimientos.idFacturacion
            LEFT JOIN core_documentos_mercantiles   ON core_documentos_mercantiles.idDocumentos   = facturacion_listado.idDocumentos';
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => $DataQuery,
            'table'   => 'bodegas_movimientos',
            'join'    => $DataJoin,
            'where'   => 'bodegas_movimientos.idMovimiento = ?',
            'params'  => [$MovimientoID['data']],
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
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'        => 'Resumen Movimiento',
                'PageDescription'  => 'Resumen Movimiento.',
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
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData]);
            // Despliegue de errores
            $this->showError(1, $f3, $result);
        }
    }

    /*******************************************************************/
    // Resumen Actualizar
    /*******************************************************************/
    public function ResumenUpdate($f3, $params, $idTipoIngreso){

        /************************************/
        // Se instancia
        $arrUserData = $this->getUserData($f3);

        /************************************/
        // Se obtiene el ID
        $MovimientoID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($MovimientoID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se verifica movimiento
        switch ($idTipoIngreso) {
            case 1: $tsrxName = 'bodegasMovimientoIngreso'; break;//Ingreso
            case 2: $tsrxName = 'bodegasMovimientoEgreso';break;//Egreso
            case 3: $tsrxName = 'bodegasMovimientoTraspaso';break;//Traspaso
        }

        /************************************/
        // Se crean cadenas
        $DataQuery = '
        bodegas_movimientos.idMovimiento,
        bodegas_movimientos.idEstadoIngreso,
        bodegas_movimientos.Creacion_fecha,
        bodegas_movimientos.Creacion_hora,
        bodegas_movimientos.Observaciones,

        core_estados_ingreso.Nombre AS TipoMovimiento,
        BodIngreso.Nombre AS BodegaIngreso,
        BodEgreso.Nombre AS BodegaEgreso,
        usuarios_listado.Nombre AS UsuarioNombre';
        $DataJoin = '
        LEFT JOIN core_estados_ingreso        ON core_estados_ingreso.idEstadoIngreso  = bodegas_movimientos.idEstadoIngreso
        LEFT JOIN bodegas_listado BodIngreso  ON BodIngreso.idBodegas                  = bodegas_movimientos.idBodegasIngreso
        LEFT JOIN bodegas_listado BodEgreso   ON BodEgreso.idBodegas                   = bodegas_movimientos.idBodegasEgreso
        LEFT JOIN usuarios_listado            ON usuarios_listado.idUsuario            = bodegas_movimientos.idUsuario';

        //permite la interaccion con la bodega, para generar documentos de ingreso o egreso
        if($arrUserData["gestionDocumentosUsoBodega"]==2){
            $DataQuery .= '
            ,bodegas_movimientos.idFacturacion
            ,facturacion_listado.N_Doc
            ,facturacion_listado.idTipo
            ,core_documentos_mercantiles.Nombre AS Documento';
            $DataJoin  .= '
            LEFT JOIN facturacion_listado           ON facturacion_listado.idFacturacion          = bodegas_movimientos.idFacturacion
            LEFT JOIN core_documentos_mercantiles   ON core_documentos_mercantiles.idDocumentos   = facturacion_listado.idDocumentos';
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => $DataQuery,
            'table'   => 'bodegas_movimientos',
            'join'    => $DataJoin,
            'where'   => 'bodegas_movimientos.idMovimiento = ?',
            'params'  => [$MovimientoID['data']],
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
        // Conteo de productos
        $ndata_1 = isset($_POST['idProducto']) ? count($_POST['idProducto']) : 0;

        /************************************/
        // Generacion de errores
        if ($ndata_1==0) {
            Response::error('No hay productos ingresados', 500);
        }

        /************************************/
        // Se llama al movimiento de materiales
        $Response = $this->createMov($_POST);

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
        // Se genera el chequeo
        $DataCheck = $this->dataCheck_1($_POST);

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idMovimiento,idEstadoIngreso,idBodegasIngreso,idBodegasEgreso,Creacion_fecha,Creacion_hora,Observaciones,fecha_auto,idUsuario,idFacturacion',
            'required'  => 'idMovimiento,idEstadoIngreso,Creacion_fecha,Creacion_hora,fecha_auto,idUsuario',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'bodegas_movimientos',
            'where'     => 'idMovimiento',
            'Post'      => $_POST,
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
        // Se llama a la eliminacion de movimiento de materiales
        $Response = $this->removeMov($dataDelete);

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
    // Creación del movimiento de mercaderías
    /*******************************************************************/
    /**
     * Tipos de movimiento permitidos:
     * 1 = Ingreso
     * 2 = Egreso
     * 3 = Traspaso
     */
    public function createMov($PostData, $NewDBConn = null): array {

        /************************************/
        // Verifico si se ejecuta otro hilo
        $DBConn = $NewDBConn ?? $this->getDBConn();

        /*******************************************************/
        /*                VALIDACIONES INICIALES               */
        /*******************************************************/
        if (!isset($PostData['idProducto']) || !is_array($PostData['idProducto']) || empty($PostData['idProducto'])) {
            return ['code' => 400, 'message' => 'No se recibieron productos para el movimiento'];
        }

        if (!isset($PostData['Number']) || !is_array($PostData['Number'])) {
            return ['code' => 400, 'message' => 'No se recibieron cantidades para el movimiento'];
        }

        /************************************/
        // Normalización de valores
        $idEstadoIngreso = (int)($PostData['idEstadoIngreso'] ?? 0);
        $idBodegaIngreso = (int)($PostData['idBodegasIngreso'] ?? 0);
        $idBodegaEgreso  = (int)($PostData['idBodegasEgreso'] ?? 0);
        $ndata_1         = count($PostData['idProducto']);

        /*******************************************************/
        /*           VALIDACION DEL TIPO DE MOVIMIENTO         */
        /*******************************************************/
        if (!in_array($idEstadoIngreso, [1, 2, 3], true)) {
            return ['code' => 400, 'message' => 'Tipo de movimiento inválido'];
        }

        /*******************************************************/
        /*                VALIDACION DE BODEGAS                */
        /*******************************************************/
        if ($idEstadoIngreso === 1 && $idBodegaIngreso <= 0) {
            return ['code' => 400, 'message' => 'Debe indicar una bodega de ingreso válida'];
        }

        if ($idEstadoIngreso === 2 && $idBodegaEgreso <= 0) {
            return ['code' => 400, 'message' => 'Debe indicar una bodega de egreso válida'];
        }

        if ($idEstadoIngreso === 3) {
            if ($idBodegaIngreso <= 0 || $idBodegaEgreso <= 0) {
                return ['code' => 400, 'message' => 'Debe indicar las bodegas de origen y destino'];
            }

            if ($idBodegaIngreso === $idBodegaEgreso) {
                return ['code' => 400, 'message' => 'La bodega de origen y destino no pueden ser iguales'];
            }
        }

        /*******************************************************/
        /*         VALIDACION DE PRODUCTOS Y CANTIDADES        */
        /*******************************************************/
        for ($j1 = 0; $j1 < $ndata_1; $j1++) {

            if (!isset($PostData['idProducto'][$j1]) || (int)$PostData['idProducto'][$j1] <= 0) {
                return ['code' => 400, 'message' => 'Se recibió un producto inválido'];
            }

            if (!isset($PostData['Number'][$j1])) {
                return ['code' => 400, 'message' => 'Falta la cantidad del producto'];
            }

            $cantidad = (float)$PostData['Number'][$j1];

            if ($cantidad <= 0) {
                return ['code' => 400, 'message' => 'La cantidad debe ser mayor que cero'];
            }
        }

        /************************************/
        // Se inicia la transacción solo si corresponde
        if($NewDBConn === null){
            $this->Base_transactionBegin(['newBDConn' => $DBConn]);
        }

        /************************************/
        // Ejecucion
        try {

            /*******************************************************/
            /*              CREAR MOVIMIENTO PRINCIPAL             */
            /*******************************************************/
            /************************************/
            // Se genera la query
            $query = [
                'data'      => 'idEstadoIngreso,idBodegasIngreso,idBodegasEgreso,Creacion_fecha,Creacion_hora,Observaciones,fecha_auto,idUsuario,idFacturacion',
                'required'  => 'idEstadoIngreso,Creacion_fecha,Creacion_hora,fecha_auto,idUsuario',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'bodegas_movimientos',
                'Post'      => $PostData
            ];
            /************************************/
            // Se genera el chequeo
            $DataCheck_1 = $this->dataCheck_1($PostData);
            // Preparo los datos
            $xParams  = ['DataCheck' => $DataCheck_1, 'query' => $query, 'newBDConn' => $DBConn];
            // Ejecuto la query
            $Response = $this->Base_insert($xParams);

            /************************************/
            // Si falla la ejecucion, se revierte de inmediato
            if ($Response['status'] === false) {
                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $Response['error']];
            }

            /*******************************************************/
            /*            OBTENER IDS UNICOS DE PRODUCTOS          */
            /*******************************************************/
            $ElementsIDs = array_values(array_unique(array_map('intval', $PostData['idProducto'])));

            /*******************************************************/
            /*             CONSTRUIR COLUMNAS DE STOCK             */
            /*******************************************************/
            // Variable
            $chainx_1 = '';
            // Se verifica movimiento
            switch ($idEstadoIngreso) {
                case 1: $chainx_1 = ',Cantidad_idBodegas_' . $idBodegaIngreso . ' AS Cantidad_1'; break; // Ingreso
                case 2: $chainx_1 = ',Cantidad_idBodegas_' . $idBodegaEgreso . ' AS Cantidad_1'; break;  // Egreso
                case 3:
                    // Traspaso
                    $chainx_1 = ',Cantidad_idBodegas_' . $idBodegaIngreso . ' AS Cantidad_1';
                    $chainx_1 .= ',Cantidad_idBodegas_' . $idBodegaEgreso . ' AS Cantidad_2';
                    break;
                default:
                    $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                    return ['code' => 400, 'message' => 'Tipo de movimiento inválido'];

            }

            /*******************************************************/
            /*               OBTENER STOCKS ACTUALES               */
            /*******************************************************/
            // Genera un '?' por cada id de producto
            $placeholders = implode(',', array_fill(0, count($ElementsIDs), '?'));

            /************************************/
            // Se consultan los stocks
            $query = [
                'data'    => 'idStocks,idProducto'.$chainx_1,
                'table'   => 'bodegas_productos_stocks',
                'join'    => '',
                'where'   => 'idProducto IN ('.$placeholders.')',
                'params'  => $ElementsIDs,
                'group'   => '',
                'having'  => '',
                'order'   => 'idProducto ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            // Preparo los datos
            $xParams   = ['query' => $query, 'newBDConn' => $DBConn];
            // Ejecuto la query
            $arrStocks = $this->Base_GetList($xParams);

            /************************************/
            // Si falla la ejecucion, se revierte de inmediato
            if ($arrStocks['status'] === false){
                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $Response['error']];
            }

            /*******************************************************/
            /*              PREPARAR STOCKS EN MEMORIA             */
            /*******************************************************/
            $arrProdStock = [];
            foreach ($arrStocks['data'] as $crud) {
                $idProducto = (int)$crud['idProducto'];
                $arrProdStock[$idProducto] = [
                    'idStocks'   => $crud['idStocks'] ?? null,
                    'Cantidad_1' => (float)($crud['Cantidad_1'] ?? 0),
                    'Cantidad_2' => (float)($crud['Cantidad_2'] ?? 0)
                ];
            }

            /*******************************************************/
            /*                  PROCESAR PRODUCTOS                 */
            /*******************************************************/
            // Recorro los productos ingresados
            for ($j1 = 0; $j1 < $ndata_1; $j1++) {

                $idProducto = (int)$PostData['idProducto'][$j1];
                $cantidad   = (float)$PostData['Number'][$j1];

                /*******************************************************/
                /*           SI NO EXISTE STOCK, SE INICIALIZA         */
                /*******************************************************/
                if (!isset($arrProdStock[$idProducto])) {
                    $arrProdStock[$idProducto] = [
                        'idStocks'   => null,
                        'Cantidad_1' => 0,
                        'Cantidad_2' => 0
                    ];
                }

                /*******************************************************/
                /*                        INGRESO                      */
                /*******************************************************/
                switch ($idEstadoIngreso) {
                    /************************************/
                    /*             INGRESO              */
                    /************************************/
                    case 1:
                        /************************************/
                        // Registrar producto del movimiento
                        $arrTareas = [
                            'idMovimiento'    => $Response['data'],
                            'idEstadoIngreso' => 1,
                            'idBodegas'       => $idBodegaIngreso,
                            'idProducto'      => $idProducto,
                            'Number'          => $cantidad
                        ];
                        $insProdMov = $this->insertarProductoMovimiento($arrTareas, $DBConn);
                        if($insProdMov['code'] != 200){
                            $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                            return $insProdMov;
                        }

                        /************************************/
                        // Calcular nuevo stock
                        $stockActual = $arrProdStock[$idProducto]['Cantidad_1'];
                        $nuevoStock  = $stockActual + $cantidad;

                        /************************************/
                        // Actualizar / crear stock
                        $guardarStock = $this->guardarStock( $arrProdStock[$idProducto]['idStocks'], $idProducto, $idBodegaIngreso, $nuevoStock, $DBConn);
                        if($guardarStock['code'] != 200){
                            $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                            return $guardarStock;
                        }

                        /********************************************************/
                        // IMPORTANTE:
                        // actualizar memoria para soportar productos repetidos
                        /********************************************************/
                        $arrProdStock[$idProducto]['Cantidad_1'] = $nuevoStock;
                        break;
                    /************************************/
                    /*              EGRESO              */
                    /************************************/
                    case 2:
                        /************************************/
                        // Registrar producto del movimiento
                        $arrTareas = [
                            'idMovimiento'    => $Response['data'],
                            'idEstadoIngreso' => 2,
                            'idBodegas'       => $idBodegaEgreso,
                            'idProducto'      => $idProducto,
                            'Number'          => $cantidad
                        ];
                        $insProdMov = $this->insertarProductoMovimiento($arrTareas, $DBConn);
                        if($insProdMov['code'] != 200){
                            $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                            return $insProdMov;
                        }

                        /************************************/
                        // Stock actual
                        $stockActual = $arrProdStock[$idProducto]['Cantidad_1'];

                        /************************************/
                        // Validar stock suficiente
                        if ($cantidad > $stockActual) {
                            $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                            return ['code' => 400, 'message' => 'Stock insuficiente para el producto ' . $idProducto];
                        }
                        $nuevoStock = $stockActual - $cantidad;

                        /************************************/
                        // Actualizar / crear stock
                        $guardarStock = $this->guardarStock( $arrProdStock[$idProducto]['idStocks'], $idProducto, $idBodegaEgreso, $nuevoStock, $DBConn);
                        if($guardarStock['code'] != 200){
                            $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                            return $guardarStock;
                        }

                        /************************************/
                        // Actualizar memoria
                        $arrProdStock[$idProducto]['Cantidad_1'] = $nuevoStock;
                        break;
                    /************************************/
                    /*             TRASPASO             */
                    /************************************/
                    case 3:
                        /************************************/
                        // Stock origen
                        $stockOrigen = $arrProdStock[$idProducto]['Cantidad_2'];

                        /* Cantidad_2 corresponde a la bodega de egreso/origen. */
                        if ($cantidad > $stockOrigen) {
                            $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                            return ['code' => 400, 'message' => 'Stock insuficiente para realizar el traspaso del producto '. $idProducto];
                        }

                        /************************************/
                        // Registrar EGRESO
                        $arrTareas = [
                            'idMovimiento'    => $Response['data'],
                            'idEstadoIngreso' => 2,
                            'idBodegas'       => $idBodegaEgreso,
                            'idProducto'      => $idProducto,
                            'Number'          => $cantidad
                        ];
                        $insProdMov = $this->insertarProductoMovimiento($arrTareas, $DBConn);
                        if($insProdMov['code'] != 200){
                            $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                            return $insProdMov;
                        }

                        /************************************/
                        // Registrar INGRESO
                        $arrTareas = [
                            'idMovimiento'    => $Response['data'],
                            'idEstadoIngreso' => 1,
                            'idBodegas'       => $idBodegaIngreso,
                            'idProducto'      => $idProducto,
                            'Number'          => $cantidad
                        ];
                        $insProdMov = $this->insertarProductoMovimiento($arrTareas, $DBConn);
                        if($insProdMov['code'] != 200){
                            $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                            return $insProdMov;
                        }

                        /************************************/
                        // Calcular nuevos stocks
                        $stockDestino       = $arrProdStock[$idProducto]['Cantidad_1'];
                        $nuevoStockDestino  = $stockDestino + $cantidad;
                        $nuevoStockOrigen   = $stockOrigen - $cantidad;

                        /************************************/
                        // Actualizar stock
                        $guardarStockTraspaso = $this->guardarStockTraspaso($arrProdStock[$idProducto]['idStocks'], $idProducto, $idBodegaIngreso, $nuevoStockDestino, $idBodegaEgreso, $nuevoStockOrigen, $DBConn);
                        if($guardarStockTraspaso['code'] != 200){
                            $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                            return $guardarStockTraspaso;
                        }

                        /********************************************************/
                        // Actualizar memoria
                        //
                        // Esto es fundamental si el mismo producto aparece
                        // nuevamente en el array.
                        /********************************************************/
                        $arrProdStock[$idProducto]['Cantidad_1'] = $nuevoStockDestino;
                        $arrProdStock[$idProducto]['Cantidad_2'] = $nuevoStockOrigen;
                        break;
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

        } catch (\Throwable $e) {

            /************************************/
            // Se revierte toda la operación ante cualquier error
            $this->Base_transactionRollback(['newBDConn' => $DBConn]);
            return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $e->getMessage()];

        }

    }

    /*******************************************************************/
    // Eliminacion del movimiento de mercaderias
    /*******************************************************************/
    public function removeMov($PostData, $NewDBConn = null): array {

        /************************************/
        // Verifico si se ejecuta otro hilo
        $DBConn = $NewDBConn ?? $this->getDBConn();

        /************************************/
        // Se inicia la transacción solo si corresponde
        if($NewDBConn === null){
            $this->Base_transactionBegin(['newBDConn' => $DBConn]);
        }

        /************************************/
        // Ejecucion
        try {

            /************************************/
            // Se obtiene el ID real del movimiento
            $MovimientoID = $this->Codification->encryptDecrypt('decrypt', $PostData['idMovimiento']);
            if (!$this->isValidDecrypted($MovimientoID, 'id')) {
                return ['code' => 400, 'message' => 'Registro inválido.'];
            }

            /************************************/
            // Consulta de productos asociados al movimiento
            // (debe ejecutarse antes de eliminar el registro principal,
            // ya que necesita leer sus datos para revertir el stock)
            $query = [
                'data' => '
                    bodegas_movimientos.idBodegasIngreso,
                    bodegas_movimientos.idBodegasEgreso,
                    bodegas_movimientos_productos.idEstadoIngreso,
                    bodegas_movimientos_productos.idProducto,
                    bodegas_movimientos_productos.Number
                ',
                'table' => 'bodegas_movimientos',
                'join' => 'LEFT JOIN bodegas_movimientos_productos ON bodegas_movimientos_productos.idMovimiento = bodegas_movimientos.idMovimiento',
                'where'  => 'bodegas_movimientos.idMovimiento = ?',
                'params' => [$MovimientoID['data']],
                'group'  => '',
                'having' => '',
                'order'  => 'bodegas_movimientos_productos.idProducto ASC',
                'limit'  => ConfigAPP::APP["N_MaxItems"]
            ];
            // Preparo los datos
            $xParams      = ['query' => $query, 'newBDConn' => $DBConn];
            // Ejecuto la query
            $arrProductos = $this->Base_GetList($xParams);

            /************************************/
            // Si falla la ejecucion, se revierte de inmediato
            if ($arrProductos['status'] === false) {
                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $arrProductos['error']];
            }

            /************************************/
            // Si existen productos, se procesan los stocks
            if (!empty($arrProductos['data'])) {

                /*
                * Se obtienen:
                * - IDs únicos de productos.
                * - Bodegas involucradas.
                * - Columnas de stock necesarias.
                */
                $ElementsIDs   = [];
                $columnasStock = [];

                foreach ($arrProductos['data'] as $producto) {

                    $idProducto = (int)$producto['idProducto'];
                    $idIngreso  = (int)$producto['idBodegasIngreso'];
                    $idEgreso   = (int)$producto['idBodegasEgreso'];

                    $ElementsIDs[$idProducto] = $idProducto;

                    switch ((int)$producto['idEstadoIngreso']) {
                        /************************************/
                        // Ingreso
                        case 1: $columnasStock[] = 'Cantidad_idBodegas_' . $idIngreso . ' AS Cantidad_1'; break;
                        /************************************/
                        // Egreso
                        case 2: $columnasStock[] = 'Cantidad_idBodegas_' . $idEgreso . ' AS Cantidad_1'; break;
                        /************************************/
                        // Traspaso
                        case 3:
                            $columnasStock[] = 'Cantidad_idBodegas_' . $idIngreso . ' AS Cantidad_1';
                            $columnasStock[] = 'Cantidad_idBodegas_' . $idEgreso . ' AS Cantidad_2';
                            break;
                        /************************************/
                        default:
                            $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                            return ['code' => 400, 'message' => 'Estado de movimiento no válido: ' . $producto['idEstadoIngreso']];

                    }
                }

                /*
                * Se eliminan columnas repetidas.
                * Esto es importante cuando existen varios productos
                * o movimientos utilizando las mismas bodegas.
                */
                $columnasStock = array_unique($columnasStock);
                $ElementsIDs   = array_values($ElementsIDs);

                /************************************/
                // Consulta de stocks
                $placeholders = implode(',', array_fill(0, count($ElementsIDs), '?'));

                /************************************/
                // Se consultan los stocks
                $query = [
                    'data'   => 'idStocks,idProducto,' . implode(',', $columnasStock),
                    'table'  => 'bodegas_productos_stocks',
                    'join'   => '',
                    'where'  => 'idProducto IN (' . $placeholders . ')',
                    'params' => $ElementsIDs,
                    'group'  => '',
                    'having' => '',
                    'order'  => 'idProducto ASC',
                    'limit'  => ConfigAPP::APP["N_MaxItems"]
                ];
                // Preparo los datos
                $xParams   = ['query' => $query, 'newBDConn' => $DBConn];
                // Ejecuto la query
                $arrStocks = $this->Base_GetList($xParams);

                /************************************/
                // Si falla la ejecucion, se revierte de inmediato
                if ($arrStocks['status'] === false) {
                    $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                    return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $arrStocks['error']];
                }

                /************************************/
                // Se estructura el stock para acceso rápido
                $arrProdStock = [];

                foreach ($arrStocks['data'] as $stock) {

                    $idProducto = (int)$stock['idProducto'];

                    $arrProdStock[$idProducto] = [
                        'idStocks'   => $stock['idStocks'],
                        'Cantidad_1' => $stock['Cantidad_1']
                    ];

                    if (isset($stock['Cantidad_2']) && $stock['Cantidad_2'] !== '') {
                        $arrProdStock[$idProducto]['Cantidad_2'] = $stock['Cantidad_2'];
                    }
                }


                /************************************/
                // Se revierten los efectos del movimiento sobre el stock
                foreach ($arrProductos['data'] as $producto) {

                    $idProducto = (int)$producto['idProducto'];
                    $estado     = (int)$producto['idEstadoIngreso'];
                    $cantidad   = (float)$producto['Number'];

                    /*
                    * Si no existe registro de stock, no se puede
                    * reconstruir correctamente el movimiento.
                    */
                    if (!isset($arrProdStock[$idProducto]) || empty($arrProdStock[$idProducto]['idStocks'])) {
                        $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                        return ['code' => 400, 'message' => 'No existe stock para el producto: ' . $idProducto];
                    }

                    $idStocks  = $arrProdStock[$idProducto]['idStocks'];
                    $idIngreso = (int)$producto['idBodegasIngreso'];
                    $idEgreso  = (int)$producto['idBodegasEgreso'];

                    /************************************/
                    // Se determina la modificación de stock
                    switch ($estado) {

                        /*******************************************************/
                        /*                       INGRESO                       */
                        /*******************************************************/
                        // Al eliminar un ingreso, se resta la cantidad.
                        case 1:

                            $campoStock    = 'Cantidad_idBodegas_' . $idIngreso;
                            $nuevaCantidad = $arrProdStock[$idProducto]['Cantidad_1'] - $cantidad;

                            $arrTareas = [
                                'idStocks'    => $idStocks,
                                $campoStock   => $nuevaCantidad
                            ];

                            // Se actualiza el stock actual
                            $actualizarStock = $this->actualizarStock($arrTareas, $campoStock, $DBConn);
                            if($actualizarStock['code'] != 200){
                                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                                return $actualizarStock;
                            }

                            /*
                            * Se actualiza el valor en memoria por si el
                            * mismo producto aparece más de una vez.
                            */
                            $arrProdStock[$idProducto]['Cantidad_1'] = $nuevaCantidad;

                            break;

                        /*******************************************************/
                        /*                        EGRESO                       */
                        /*******************************************************/
                        // Al eliminar un egreso, se devuelve la cantidad.
                        case 2:

                            $campoStock    = 'Cantidad_idBodegas_' . $idEgreso;
                            $nuevaCantidad = $arrProdStock[$idProducto]['Cantidad_1'] + $cantidad;

                            $arrTareas = [
                                'idStocks'  => $idStocks,
                                $campoStock => $nuevaCantidad
                            ];

                            // Se actualiza el stock actual
                            $actualizarStock = $this->actualizarStock($arrTareas, $campoStock, $DBConn);
                            if($actualizarStock['code'] != 200){
                                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                                return $actualizarStock;
                            }

                            /*
                            * Se actualiza el valor en memoria.
                            */
                            $arrProdStock[$idProducto]['Cantidad_1'] = $nuevaCantidad;

                            break;

                        /*******************************************************/
                        /*                       TRASPASO                      */
                        /*******************************************************/
                        // Al eliminar un traspaso:
                        // - Se devuelve stock a la bodega de origen.
                        // - Se resta stock de la bodega destino.
                        case 3:

                            $campoIngreso         = 'Cantidad_idBodegas_' . $idIngreso;
                            $campoEgreso          = 'Cantidad_idBodegas_' . $idEgreso;
                            $nuevaCantidadIngreso = $arrProdStock[$idProducto]['Cantidad_1'] + $cantidad;
                            $cantidadEgreso       = $arrProdStock[$idProducto]['Cantidad_2'] ?? 0;
                            $nuevaCantidadEgreso  = $cantidadEgreso - $cantidad;

                            $arrTareas = [
                                'idStocks'        => $idStocks,
                                $campoIngreso     => $nuevaCantidadIngreso,
                                $campoEgreso      => $nuevaCantidadEgreso
                            ];

                            // Se actualiza el stock actual
                            $actualizarStock = $this->actualizarStock($arrTareas, $campoIngreso . ',' . $campoEgreso, $DBConn);
                            if($actualizarStock['code'] != 200){
                                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                                return $actualizarStock;
                            }

                            /*
                            * Se actualizan los valores en memoria.
                            */
                            $arrProdStock[$idProducto]['Cantidad_1'] = $nuevaCantidadIngreso;
                            $arrProdStock[$idProducto]['Cantidad_2'] = $nuevaCantidadEgreso;

                            break;

                        /************************************/
                        default:
                            $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                            return ['code' => 400, 'message' => 'Estado de movimiento no válido: ' . $estado];

                    }
                }
            }

            /************************************/
            // Listado de las tablas a eliminar los datos relacionados
            $arrTableDel  = array();
            $arrTableDel[] = ['files' => '', 'table' => 'bodegas_movimientos_productos'];

            /************************************/
            // Recorro
            foreach ($arrTableDel as $tblDel) {
                /************************************/
                // Se genera la query
                $query = ['files' => $tblDel['files'], 'table' => $tblDel['table'], 'where' => 'idMovimiento', 'SubCarpeta' => '', 'Post' => $PostData];
                // Preparo los datos
                $xParams    = ['query' => $query, 'newBDConn' => $DBConn];
                // Ejecuto la query
                $respDelRel = $this->Base_delete($xParams);
                /************************************/
                // Si falla la ejecucion, se revierte de inmediato
                if ($respDelRel['status'] === false) {
                    $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                    return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $respDelRel['error']];
                }
            }

            /************************************/
            // Elimina el movimiento principal
            $query = [
                'files'      => '',
                'table'      => 'bodegas_movimientos',
                'where'      => 'idMovimiento',
                'SubCarpeta' => '',
                'Post'       => $PostData
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
            // Confirmar transacción solo si corresponde
            if($NewDBConn === null){
                $this->Base_transactionCommit(['newBDConn' => $DBConn]);
            }

            /************************************/
            // Retorno los datos
            return ['code' => 200, 'data' => $Response];

        } catch (Throwable $e) {

            /************************************/
            // Se revierte toda la operación ante cualquier error
            $this->Base_transactionRollback(['newBDConn' => $DBConn]);
            return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $e->getMessage()];

        }

    }

    /*******************************************************************/
    // Inserta un producto asociado al movimiento.
    /*******************************************************************/
    public function insertarProductoMovimiento(array $arrTareas, $DBConn): array {

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
            'required'  => 'idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'bodegas_movimientos_productos',
            'Post'      => $arrTareas
        ];

        /************************************/
        // Se genera el chequeo
        $DataCheck_2 = $this->DataCheck_2($arrTareas);
        // Preparo los datos
        $xParams  = ['DataCheck' => $DataCheck_2, 'query' => $query, 'newBDConn' => $DBConn];
        // Ejecuto la query
        $Response = $this->Base_insert($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $Response['error']];
        }

        /************************************/
        // Retorno los datos
        return ['code' => 200, 'data' => $Response];
    }

    /*******************************************************************/
    // Actualiza un stock existente o crea uno nuevo.
    //  Esta versión está preparada para una sola bodega.
    /*******************************************************************/
    public function guardarStock($idStocks, int $idProducto, int $idBodega, float $cantidad, $DBConn): array {

        /************************************/
        // Se abre cadena
        $campoStock = 'Cantidad_idBodegas_' . $idBodega;

        /***************************************************************/
        // Stock existente
        /***************************************************************/
        if (!empty($idStocks)) {

            /************************************/
            // Se crean los datos
            $arrTareas = [
                'idStocks'   => $idStocks,
                $campoStock  => $cantidad
            ];

            /************************************/
            // Se genera la query
            $query = [
                'data'     => 'idStocks,' . $campoStock,
                'required' => 'idStocks,' . $campoStock,
                'unique'   => '',
                'encode'   => '',
                'table'    => 'bodegas_productos_stocks',
                'where'    => 'idStocks',
                'Post'     => $arrTareas
            ];

            /************************************/
            // Se genera el chequeo
            $DataCheck_3 = $this->DataCheck_3($arrTareas);
            // Preparo los datos
            $xParams  = ['DataCheck' => $DataCheck_3, 'query' => $query, 'newBDConn' => $DBConn];
            // Ejecuto la query
            $Response = $this->Base_update($xParams);

            /************************************/
            // Si falla la ejecucion, se revierte de inmediato
            if ($Response['status'] === false) {
                return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $Response['error']];
            }

            /************************************/
            // Retorno los datos
            return ['code' => 200, 'data' => $Response];
        }

        /***************************************************************/
        // Stock inexistente
        /***************************************************************/
        // Se crean los datos
        $arrTareas = [
            'idProducto' => $idProducto,
            $campoStock  => $cantidad
        ];

        /************************************/
        // Se genera la query
        $query = [
            'data'     => 'idProducto,' . $campoStock,
            'required' => 'idProducto,' . $campoStock,
            'unique'   => '',
            'encode'   => '',
            'table'    => 'bodegas_productos_stocks',
            'Post'     => $arrTareas
        ];

        /************************************/
        // Se genera el chequeo
        $DataCheck_3 = $this->DataCheck_3($arrTareas);
        // Preparo los datos
        $xParams = ['DataCheck' => $DataCheck_3, 'query' => $query, 'newBDConn' => $DBConn];
        // Ejecuto la query
        $Response = $this->Base_insert($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $Response['error']];
        }

        /************************************/
        // Retorno los datos
        return ['code' => 200, 'data' => $Response];
    }

    /*******************************************************************/
    // Actualiza dos bodegas en un traspaso.
    /*******************************************************************/
    public function guardarStockTraspaso($idStocks, int $idProducto, int $idBodegaIngreso, float $cantidadIngreso,
                                            int $idBodegaEgreso, float $cantidadEgreso, $DBConn): array {

        $campoIngreso = 'Cantidad_idBodegas_' . $idBodegaIngreso;
        $campoEgreso  = 'Cantidad_idBodegas_' . $idBodegaEgreso;

        /***************************************************************/
        // Stock existente
        /***************************************************************/

        if (!empty($idStocks)) {

            /************************************/
            // Se crean los datos
            $arrTareas = [
                'idStocks'   => $idStocks,
                $campoIngreso => $cantidadIngreso,
                $campoEgreso  => $cantidadEgreso
            ];

            /************************************/
            // Se genera la query
            $query = [
                'data'     => 'idStocks,' . $campoIngreso . ',' . $campoEgreso,
                'required' => 'idStocks,' . $campoIngreso . ',' . $campoEgreso,
                'unique'   => '',
                'encode'   => '',
                'table'    => 'bodegas_productos_stocks',
                'where'    => 'idStocks',
                'Post'     => $arrTareas
            ];

            /************************************/
            // Se genera el chequeo
            $DataCheck_3 = $this->DataCheck_3($arrTareas);
            // Preparo los datos
            $xParams = ['DataCheck' => $DataCheck_3, 'query' => $query, 'newBDConn' => $DBConn];
            // Ejecuto la query
            $Response = $this->Base_update($xParams);

            /************************************/
            // Si falla la ejecucion, se revierte de inmediato
            if ($Response['status'] === false) {
                return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $Response['error']];
            }

            /************************************/
            // Retorno los datos
            return ['code' => 200, 'data' => $Response];
        }

        /***************************************************************/
        // Stock inexistente
        /***************************************************************/
        // Se crean los datos
        $arrTareas = [
            'idProducto' => $idProducto,
            $campoIngreso => $cantidadIngreso,
            $campoEgreso  => $cantidadEgreso
        ];

        /************************************/
        // Se genera la query
        $query = [
            'data'     => 'idProducto,' . $campoIngreso . ',' . $campoEgreso,
            'required' => 'idProducto,' . $campoIngreso . ',' . $campoEgreso,
            'unique'   => '',
            'encode'   => '',
            'table'    => 'bodegas_productos_stocks',
            'Post'      => $arrTareas
        ];

        /************************************/
        // Se genera el chequeo
        $DataCheck_3 = $this->DataCheck_3($arrTareas);
        // Preparo los datos
        $xParams = ['DataCheck' => $DataCheck_3, 'query' => $query, 'newBDConn' => $DBConn];
        // Ejecuto la query
        $Response = $this->Base_insert($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $Response['error']];
        }

        /************************************/
        // Retorno los datos
        return ['code' => 200, 'data' => $Response];
    }

    /*******************************************************************/
    // Actualiza el stock
    /*******************************************************************/
    public function actualizarStock(array $arrTareas, string $campos, $DBConn): array {

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idStocks,' . $campos,
            'required'  => 'idStocks,' . $campos,
            'unique'    => '',
            'encode'    => '',
            'table'     => 'bodegas_productos_stocks',
            'where'     => 'idStocks',
            'Post'      => $arrTareas
        ];

        /************************************/
        // Se genera el chequeo
        $DataCheck_3 = $this->DataCheck_3($arrTareas);
        // Preparo los datos
        $xParams  = ['DataCheck' => $DataCheck_3, 'query' => $query, 'newBDConn' => $DBConn];
        // Ejecuto la query
        $Response = $this->Base_update($xParams);

        /************************************/
        // Si falla la la ejecucion
        if ($Response['status'] === false) {
            return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $Response['error']];
        }

        /************************************/
        // Retorno los datos
        return ['code' => 200, 'data' => $Response];

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
            'ValidarNumero'             => 'idEstadoIngreso,idBodegasIngreso,idBodegasEgreso,idUsuario,idFacturacion',
            'ValidarEntero'             => 'idEstadoIngreso,idBodegasIngreso,idBodegasEgreso,idUsuario,idFacturacion',
            'ValidarRut'                => '',
            'ValidarPatente'            => '',
            'ValidarFecha'              => 'Creacion_fecha,fecha_auto',
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
            'ValidarNumero'             => 'idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
            'ValidarEntero'             => 'idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
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
    private function dataCheck_3($POST){
        // Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => '',
            'ValidarNumero'             => 'idStocks,idProducto',
            'ValidarEntero'             => 'idStocks,idProducto',
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
