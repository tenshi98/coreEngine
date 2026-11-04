<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class informeProductos extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataNumbers;
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
        $this->controllerName = 'informeProductos';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->DataNumbers    = new FunctionsDataNumbers();
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
        // Se instancia
        $arrUserData = $this->getUserData($f3);

        // Se verifica si se tiene el permiso para visualizar el dato
        if($arrUserData["usuariosPermisosBodegas"] === 2 && $arrUserData['UserType'] != 1){
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

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrBodegas['status'] === true && $arrProductos['status'] === true) {

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Stock Productos',
                'PageDescription' => 'Stock Productos.',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => 'Stock Productos',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'   => $this->FormInputs,
                'Fnc_Codification' => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrBodegas'     => $arrBodegas['data'],
                'arrProductos'   => $arrProductos['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrBodegas,$arrProductos]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }

    }

    /*******************************************************************/
    // Actualizar Listar
    /*******************************************************************/
    public function UpdateList($f3){

        /************************************/
        // Variables
        $WhereData_int     = 'idBodegas';  // Datos búsqueda exacta
        $WhereData_string  = '';           // Datos búsqueda relativa
        $WhereData_between = '';           // Datos búsqueda Between
        $whereInt1         = '';           // Se crea cadena
        $whereParams1      = [];           // Valores bindeados asociados a $whereInt
        /************************************/
        // Agrego variable busqueda
        $r = $this->searchWhere($whereInt1, $whereParams1, $WhereData_int, 'bodegas_listado', 1);
        $whereInt1 = $r['where']; $whereParams1 = $r['params'];
        $r = $this->searchWhere($whereInt1, $whereParams1, $WhereData_string, 'bodegas_listado', 2);
        $whereInt1 = $r['where']; $whereParams1 = $r['params'];
        $r = $this->searchWhere($whereInt1, $whereParams1, $WhereData_between, 'bodegas_listado', 3);
        $whereInt1 = $r['where']; $whereParams1 = $r['params'];
        // Verifico si esta vacio
        $whereInt1      = $whereInt1 ? $whereInt1 . ' AND bodegas_listado.idEstado = ?' : 'bodegas_listado.idEstado = ?';
        $whereParams1[] = 1;

        /************************************/
        // Variables
        $WhereData_int     = 'idProducto';  // Datos búsqueda exacta
        $WhereData_string  = '';            // Datos búsqueda relativa
        $WhereData_between = '';            // Datos búsqueda Between
        $whereInt2         = '';            // Se crea cadena
        $whereParams2      = [];            // Valores bindeados asociados a $whereInt
        /************************************/
        // Agrego variable busqueda
        $r = $this->searchWhere($whereInt2, $whereParams2, $WhereData_int, 'bodegas_productos_stocks', 1);
        $whereInt2 = $r['where']; $whereParams2 = $r['params'];
        $r = $this->searchWhere($whereInt2, $whereParams2, $WhereData_string, 'bodegas_productos_stocks', 2);
        $whereInt2 = $r['where']; $whereParams2 = $r['params'];
        $r = $this->searchWhere($whereInt2, $whereParams2, $WhereData_between, 'bodegas_productos_stocks', 3);
        $whereInt2 = $r['where']; $whereParams2 = $r['params'];

        /************************************/
        // Se instancia
        $arrUserData = $this->getUserData($f3);

        // Se verifica si se tiene el permiso para visualizar el dato
        if($arrUserData["usuariosPermisosBodegas"] === 2 && $arrUserData['UserType'] != 1){
            $X_join         = 'INNER JOIN bodegas_listado_permisos_usuarios ON bodegas_listado_permisos_usuarios.idBodegas = bodegas_listado.idBodegas';
            $whereInt1     .= ' AND bodegas_listado_permisos_usuarios.idUsuario = ?';
            $whereParams1[] = $arrUserData['UserID'];
        // Si se permite junto con la creacion de tareas
        }else{
            $X_join = '';
        }
        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'bodegas_listado.idBodegas, bodegas_listado.Nombre',
            'table'   => 'bodegas_listado',
            'join'    => $X_join,
            'where'   => $whereInt1,
            'params'  => $whereParams1,
            'group'   => '',
            'having'  => '',
            'order'   => 'bodegas_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams    = ['query' => $query];
        // Ejecuto la query
        $arrBodegas = $this->Base_GetList($xParams);

        // Se genera la consulta
        $ActionSQL = '';
        if ($arrBodegas['status'] === true) {
            foreach ($arrBodegas['data'] as $bod) {
                $ActionSQL .= ',Cantidad_idBodegas_'.$bod['idBodegas'];
            }
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                bodegas_productos_stocks.idProducto,
                productos_listado.Nombre AS Producto,
                core_unidades_medida.Nombre AS UniMed
                '.$ActionSQL,
            'table'   => 'bodegas_productos_stocks',
            'join'    => '
                LEFT JOIN productos_listado     ON productos_listado.idProducto    = bodegas_productos_stocks.idProducto
                LEFT JOIN core_unidades_medida  ON core_unidades_medida.idUniMed   = productos_listado.idUniMed',
            'where'   => $whereInt2,
            'params'  => $whereParams2,
            'group'   => '',
            'having'  => '',
            'order'   => 'productos_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams   = ['query' => $query];
        // Ejecuto la query
        $arrStocks = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($arrBodegas['status'] === true && $arrStocks['status'] === true){

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'TableTitle'      => 'Stock Actual',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'    => $this->Codification,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'arrStocks'       => $arrStocks['data'],
                'arrBodegas'      => $arrBodegas['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-UpdateList.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrStocks,$arrBodegas]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // View
    /*******************************************************************/
    public function View($f3, $params){

        /************************************/
        // Se no existe el ID
        if (!isset($params['idProducto'], $params['idBodegas'])) {
            Response::error('Registro ID inexistente', 400);
        }

        /************************************/
        // Se obtiene el ID
        $ProductoID = $this->Codification->encryptDecrypt('decrypt', $params['idProducto']);
        // Se verifica
        if (!$this->isValidDecrypted($ProductoID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        $BodegasID  = $this->Codification->encryptDecrypt('decrypt', $params['idBodegas']);
        // Se verifica
        if (!$this->isValidDecrypted($BodegasID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se instancia
        $arrUserData = $this->getUserData($f3);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'Nombre',
            'table'   => 'productos_listado',
            'join'    => '',
            'where'   => 'idProducto = ?',
            'params'  => [$ProductoID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams     = ['query' => $query];
        // Ejecuto la query
        $rowProducto = $this->Base_GetByID($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'Nombre',
            'table'   => 'bodegas_listado',
            'join'    => '',
            'where'   => 'idBodegas = ?',
            'params'  => [$BodegasID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams   = ['query' => $query];
        // Ejecuto la query
        $rowBodega = $this->Base_GetByID($xParams);

        /************************************/
        // Se crean cadenas
        $DataQuery = '
        bodegas_movimientos_productos.idMovimiento,
        bodegas_movimientos_productos.idEstadoIngreso,
        bodegas_movimientos_productos.Number,
        bodegas_movimientos.Creacion_fecha,
        bodegas_movimientos.Creacion_hora,
        bodegas_movimientos.Observaciones,
        core_estados_ingreso.Nombre AS TipoMov,
        core_unidades_medida.Nombre AS UniMed';
        $DataJoin = '
        LEFT JOIN bodegas_movimientos   ON bodegas_movimientos.idMovimiento       = bodegas_movimientos_productos.idMovimiento
        LEFT JOIN core_estados_ingreso  ON core_estados_ingreso.idEstadoIngreso   = bodegas_movimientos_productos.idEstadoIngreso
        LEFT JOIN productos_listado     ON productos_listado.idProducto           = bodegas_movimientos_productos.idProducto
        LEFT JOIN core_unidades_medida  ON core_unidades_medida.idUniMed          = productos_listado.idUniMed';
        $DataWhere = 'bodegas_movimientos_productos.idProducto = ? AND bodegas_movimientos_productos.idBodegas = ?';

        //permite la interaccion con la bodega, para generar documentos de ingreso o egreso
        if($arrUserData["gestionDocumentosUsoBodega"] === 2){
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
            'table'   => 'bodegas_movimientos_productos',
            'join'    => $DataJoin,
            'where'   => $DataWhere,
            'params'  => [
                $ProductoID['data'],
                $BodegasID['data']
            ],
            'group'   => '',
            'having'  => '',
            'order'   => 'bodegas_movimientos.Creacion_fecha DESC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams   = ['query' => $query];
        // Ejecuto la query
        $arrStocks = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowProducto['status'] === true && $rowBodega['status'] === true && $arrStocks['status'] === true){

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_Codification'    => $this->Codification,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'rowProducto' => $rowProducto['data'],
                'rowBodega'   => $rowBodega['data'],
                'arrStocks'   => $arrStocks['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-View.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowProducto,$rowBodega,$arrStocks]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Imprimir
    /*******************************************************************/
    public function Print($f3, $params){

        /************************************/
        // Se no existe el ID
        if (!isset($params['id'])) {
            Response::error('Registro ID inexistente', 400);
        }

        /************************************/
        // Se obtiene el ID producto
        $productId = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        // Se verifica
        if (!$this->isValidDecrypted($productId, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query - PRODUCTO STOCK (SEGURO)
        // Ya no consulta kanban_tareas, kanban_tareas_tareas, kanban_tareas_participantes
        $query = [
            'data'    => '
                bodegas_productos_stocks.idStock,
                bodegas_productos_stocks.idProducto,
                productos.Titulo AS ProductoNombre,
                productos.Codigo AS ProductoCodigo,
                bodegas_productos_stocks.cantidad AS StockActual,
                bodegas_productos_stocks.cantidad_minima AS StockMinimo',
            'table'   => 'bodegas_productos_stocks',
            'join'    =>'LEFT JOIN productos ON productos.idProducto = bodegas_productos_stocks.idProducto',
            'where'   => 'bodegas_productos_stocks.idProducto = ?',
            'params'  => [$productId['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] === true){

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_Codification'     => $this->Codification,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(3, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Print.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $rowData;
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

}
