<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class informeProductos extends ControllerBase {

    /******************************************************************************/
    // Variables
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataNumbers;
    private $DataDate;
    private $WidgetsCommon;

    /******************************************************************************/
    //Constructor
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
    /******************************************************************************/
    //Listar Todo
    public function listAll($f3){
        /*******************************************************************/
        //Se instancia
        $arrUserData = $this->getUserData($f3);
        // Se verifica si se tiene el permiso para visualizar el dato
        if($arrUserData["usuariosPermisosBodegas"]==2 && $arrUserData['UserType'] != 1){
            $X_join  = 'INNER JOIN bodegas_listado_permisos_usuarios ON bodegas_listado_permisos_usuarios.idBodegas = bodegas_listado.idBodegas';
            $X_where  = 'bodegas_listado.idEstado = ? AND bodegas_listado_permisos_usuarios.idUsuario = ?';
            $X_params = [1, $arrUserData['UserID']];
        //Si se permite junto con la creacion de tareas
        }else{
            $X_join   = '';
            $X_where  = 'bodegas_listado.idEstado = ?';
            $X_params = [1];
        }
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
        // Ejecuto la query
        $xParams    = ['query' => $query];
        $arrBodegas = $this->Base_GetList($xParams);

        /*******************************************************************/
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
        // Ejecuto la query
        $xParams      = ['query' => $query];
        $arrProductos = $this->Base_GetList($xParams);

        /******************************************/
        //Datos enviados a la pagina
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

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
    }

    /******************************************************************************/
    //List
    public function UpdateList($f3){
        /*******************************************************************/
        // Variables
        $WhereData_int     = 'idBodegas';  // Datos búsqueda exacta
        $WhereData_string  = '';           // Datos búsqueda relativa
        $WhereData_between = '';           // Datos búsqueda Between
        $whereInt1         = '';           // Se crea cadena
        $whereParams1      = [];           // Valores bindeados asociados a $whereInt
        /******************************************/
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

        /*******************************************************************/
        // Variables
        $WhereData_int     = 'idProducto';  // Datos búsqueda exacta
        $WhereData_string  = '';            // Datos búsqueda relativa
        $WhereData_between = '';            // Datos búsqueda Between
        $whereInt2         = '';            // Se crea cadena
        $whereParams2      = [];            // Valores bindeados asociados a $whereInt
        /******************************************/
        // Agrego variable busqueda
        $r = $this->searchWhere($whereInt2, $whereParams2, $WhereData_int, 'bodegas_productos_stocks', 1);
        $whereInt2 = $r['where']; $whereParams2 = $r['params'];
        $r = $this->searchWhere($whereInt2, $whereParams2, $WhereData_string, 'bodegas_productos_stocks', 2);
        $whereInt2 = $r['where']; $whereParams2 = $r['params'];
        $r = $this->searchWhere($whereInt2, $whereParams2, $WhereData_between, 'bodegas_productos_stocks', 3);
        $whereInt2 = $r['where']; $whereParams2 = $r['params'];

        /*******************************************************************/
        //Se instancia
        $arrUserData = $this->getUserData($f3);
        // Se verifica si se tiene el permiso para visualizar el dato
        if($arrUserData["usuariosPermisosBodegas"]==2 && $arrUserData['UserType'] != 1){
            $X_join         = 'INNER JOIN bodegas_listado_permisos_usuarios ON bodegas_listado_permisos_usuarios.idBodegas = bodegas_listado.idBodegas';
            $whereInt1     .= ' AND bodegas_listado_permisos_usuarios.idUsuario = ?';
            $whereParams1[] = $arrUserData['UserID'];
        //Si se permite junto con la creacion de tareas
        }else{
            $X_join = '';
        }
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
        // Ejecuto la query
        $xParams    = ['query' => $query];
        $arrBodegas = $this->Base_GetList($xParams);

        //Se genera la consulta
        $ActionSQL = '';
        if($arrBodegas['status']){
            foreach ($arrBodegas['data'] as $bod) {
                $ActionSQL .= ',Cantidad_idBodegas_'.$bod['idBodegas'];
            }
        }

        /*******************************************************************/
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
        // Ejecuto la query
        $xParams   = ['query' => $query];
        $arrStocks = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($arrBodegas['status'] && $arrStocks['status']){

            /******************************************/
            //Datos enviados a la pagina
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

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-UpdateList.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$arrStocks,$arrBodegas]);
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
        // Se genera la query
        $query = [
            'data'    => 'Nombre',
            'table'   => 'productos_listado',
            'join'    => '',
            'where'   => 'idProducto = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['idProducto'])],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Ejecuto la query
        $xParams     = ['query' => $query];
        $rowProducto = $this->Base_GetByID($xParams);

        /******************************************/
        // Se genera la query
        $query = [
            'data'    => 'Nombre',
            'table'   => 'bodegas_listado',
            'join'    => '',
            'where'   => 'idBodegas = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['idBodegas'])],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Ejecuto la query
        $xParams   = ['query' => $query];
        $rowBodega = $this->Base_GetByID($xParams);

        /******************************************/
        //Se crean cadenas
        $DataQuery = '
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

        // Se genera la query
        $query = [
            'data'    => $DataQuery,
            'table'   => 'bodegas_movimientos_productos',
            'join'    => $DataJoin,
            'where'   => $DataWhere,
            'params'  => [
                $this->Codification->encryptDecrypt('decrypt', $params['idProducto']),
                $this->Codification->encryptDecrypt('decrypt', $params['idBodegas'])
            ],
            'group'   => '',
            'having'  => '',
            'order'   => 'bodegas_movimientos.Creacion_fecha DESC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams   = ['query' => $query];
        $arrStocks = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowProducto['status'] && $rowBodega['status'] && $arrStocks['status']){

            /******************************************/
            //Datos enviados a la pagina
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

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-View.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$rowProducto,$rowBodega,$arrStocks]);
            //Muestra los errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    //View
    public function Print($f3, $params){
        /******************************************/
        // Se genera la query
        $query = [
            'data'    => '
                kanban_tareas.idKanban,
                kanban_tareas.idEstadoCierre,
                core_prioridades.Nombre AS PrioridadNombre,
                core_prioridades.Color AS PrioridadColor,
                kanban_tareas.Fecha,
                kanban_tareas.Titulo,
                kanban_tareas.Descripcion,
                core_estados_cierre.Nombre AS EstadoCierreNombre,
                core_estados_cierre.Color AS EstadoCierreColor,
                kanban_estados.Nombre AS KanbanEstado,
                core_estados_colores.Nombre AS KanbanColor',
            'table'   => 'kanban_tareas',
            'join'    => '
                LEFT JOIN core_prioridades        ON core_prioridades.idPrioridad          = kanban_tareas.idPrioridad
                LEFT JOIN kanban_estados          ON kanban_estados.idKanbanEstado         = kanban_tareas.idKanbanEstado
                LEFT JOIN core_estados_cierre     ON core_estados_cierre.idEstadoCierre    = kanban_tareas.idEstadoCierre
                LEFT JOIN core_estados_colores    ON core_estados_colores.idColor          = kanban_estados.idColor',
            'where'   => 'kanban_tareas.idKanban = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /******************************************/
        // Se genera la query
        $query = [
            'data'    => '
                kanban_tareas_tareas.idTareas,
                kanban_tareas_tareas.Tarea,
                core_estados_trabajos.Nombre AS EstadoNombre,
                core_estados_trabajos.Color AS EstadoColor,
                core_estados_trabajos.Icon AS EstadoIcon,
                kanban_trabajos.Nombre AS Trabajo',
            'table'   => 'kanban_tareas_tareas',
            'join'    => '
                LEFT JOIN core_estados_trabajos  ON core_estados_trabajos.idEstadoTrabajo = kanban_tareas_tareas.idEstadoTrabajo
                LEFT JOIN kanban_trabajos        ON kanban_trabajos.idTrabajo             = kanban_tareas_tareas.idTrabajo',
            'where'   => 'kanban_tareas_tareas.idKanban = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => 'kanban_tareas_tareas.Tarea ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams   = ['query' => $query];
        $arrTareas = $this->Base_GetList($xParams);

        /******************************************/
        // Se genera la query
        $query = [
            'data'    => '
                kanban_tareas_participantes.idParticipantes,
                usuarios_listado.Nombre AS UsuarioNombre,
                usuarios_listado.Direccion_img AS UsuarioImg',
            'table'   => 'kanban_tareas_participantes',
            'join'    => 'LEFT JOIN usuarios_listado ON usuarios_listado.idUsuario = kanban_tareas_participantes.idUsuario',
            'where'   => 'kanban_tareas_participantes.idKanban = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => 'usuarios_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams          = ['query' => $query];
        $arrParticipantes = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrTareas['status'] && $arrParticipantes['status']){

            /******************************************/
            //Datos enviados a la pagina
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
                'arrTareas'        => $arrTareas['data'],
                'arrParticipantes' => $arrParticipantes['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(3, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Print.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrTareas,$arrParticipantes]);
            //Muestra los errores
            $this->showError(2, $f3, $result);
        }
    }

}
