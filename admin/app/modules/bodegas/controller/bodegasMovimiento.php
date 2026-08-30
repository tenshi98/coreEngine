<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class bodegasMovimiento extends ControllerBase {

    /******************************************************************************/
    // Variables
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataNumbers;
    private $DataDate;
    private $ServerServer;
    private $WidgetsCommon;

    /******************************************************************************/
    //Constructor
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
    /******************************************************************************/
    //Listar Todo
    public function listAll_1($f3){$this->listAll($f3, 1);}
    public function listAll_2($f3){$this->listAll($f3, 2);}
    public function listAll_3($f3){$this->listAll($f3, 3);}
    //Listar Todo
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
    /******************************************************************************/
    //Listar Todo
    public function listAll($f3, $idTipoIngreso){
        /******************************************/
        //Se verifica movimiento
        switch ($idTipoIngreso) {
            case 1: $tsrxName = 'bodegasMovimientoIngreso';  $TipoMov = 'Ingresos a';    break;//Ingreso
            case 2: $tsrxName = 'bodegasMovimientoEgreso';   $TipoMov = 'Egresos a';     break;//Egreso
            case 3: $tsrxName = 'bodegasMovimientoTraspaso'; $TipoMov = 'Traspasos de';  break;//Traspaso
        }

        /*******************************************************************/
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
        // Ejecuto la query
        $xParams = ['query' => $query];
        $arrList = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se instancia
        $arrUserData = $this->getUserData($f3);
        // Se verifica si se tiene el permiso para visualizar el dato
        if($arrUserData["usuariosPermisosBodegas"]==2 && $arrUserData['UserType'] != 1){
            $X_join   = 'INNER JOIN bodegas_listado_permisos_usuarios ON bodegas_listado_permisos_usuarios.idBodegas = bodegas_listado.idBodegas';
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

        /*******************************************************************/
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
        // Ejecuto la query
        $xParams       = ['query' => $query];
        $arrDocumentos = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($arrList['status'] && $arrBodegas['status'] && $arrProductos['status'] && $arrDocumentos['status']){

            /******************************************/
            //Datos enviados a la pagina
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

            /******************************************/
            //Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$arrList,$arrBodegas,$arrProductos,$arrDocumentos]);
            //Muestra los errores
            $this->showError(1, $f3, $result);
        }
    }

    /******************************************************************************/
    //List
    public function UpdateList($f3, $idTipoIngreso){
        /******************************************/
        //Se verifica movimiento
        switch ($idTipoIngreso) {
            case 1: $tsrxName = 'bodegasMovimientoIngreso';  $TipoMov = 'Ingresos a';    break;//Ingreso
            case 2: $tsrxName = 'bodegasMovimientoEgreso';   $TipoMov = 'Egresos a';     break;//Egreso
            case 3: $tsrxName = 'bodegasMovimientoTraspaso'; $TipoMov = 'Traspasos de';  break;//Traspaso
        }

        /*******************************************************************/
        // Variables
        $WhereData_bod_int     = 'idMovimiento,idBodegasIngreso,idBodegasEgreso';   // Datos búsqueda exacta
        $WhereData_bod_string  = '';                                                // Datos búsqueda relativa
        $WhereData_bod_between = 'Creacion_fecha-F_Inicio-F_Termino';               // Datos búsqueda Between
        $WhereData_fac_int     = 'idDocumentos,idFacturacion';                      // Datos búsqueda exacta
        $WhereData_fac_string  = 'N_Doc';                                           // Datos búsqueda relativa
        $WhereData_fac_between = '';                                                // Datos búsqueda Between
        $whereInt              = '';                                                // Se crea cadena
        $whereParams           = [];                                                // Valores bindeados asociados a $whereInt
        /******************************************/
        // Se validan las fechas
        $RespDataBetween = $this->searchValidateDates($WhereData_bod_between);
        if($RespDataBetween!=''){
            Response::error($RespDataBetween, 500);
        }
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

        /******************************/
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
        // Ejecuto la query
        $xParams = ['query' => $query];
        $arrList = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($arrList['status']){

            /******************************************/
            //Datos enviados a la pagina
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
    public function View($f3, $params, $idTipoIngreso){
        /******************************************/
        //Se instancia
        $arrUserData = $this->getUserData($f3);

        /******************************************/
        //Se verifica movimiento
        switch ($idTipoIngreso) {
            case 1: $tsrxName = 'bodegasMovimientoIngreso'; break;//Ingreso
            case 2: $tsrxName = 'bodegasMovimientoEgreso';break;//Egreso
            case 3: $tsrxName = 'bodegasMovimientoTraspaso';break;//Traspaso
        }

        /******************************************/
        //Se crean cadenas
        $DataQuery = '
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

        /******************************************/
        // Se genera la query
        $query = [
            'data'    => $DataQuery,
            'table'   => 'bodegas_movimientos',
            'join'    => $DataJoin,
            'where'   => 'bodegas_movimientos.idMovimiento = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
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
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => 'bodegas_movimientos_productos.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams      = ['query' => $query];
        $arrProductos = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrProductos['status']){
            /******************************************/
            //Datos enviados a la pagina
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

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-View.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrProductos]);
            //Muestra los errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    //Resumen
    public function Resumen($f3, $params, $idTipoIngreso){
        /******************************************/
        //Se instancia
        $arrUserData = $this->getUserData($f3);

        /******************************************/
        //Se verifica movimiento
        switch ($idTipoIngreso) {
            case 1: $tsrxName = 'bodegasMovimientoIngreso'; break;//Ingreso
            case 2: $tsrxName = 'bodegasMovimientoEgreso';break;//Egreso
            case 3: $tsrxName = 'bodegasMovimientoTraspaso';break;//Traspaso
        }

        /******************************************/
        //Se crean cadenas
        $DataQuery = '
        bodegas_movimientos.idMovimiento,
        bodegas_movimientos.idEstadoIngreso,
        bodegas_movimientos.idBodegasIngreso,
        bodegas_movimientos.idBodegasEgreso,
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

        /******************************************/
        // Se genera la query
        $query = [
            'data'    => $DataQuery,
            'table'   => 'bodegas_movimientos',
            'join'    => $DataJoin,
            'where'   => 'bodegas_movimientos.idMovimiento = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status']){
            /******************************************/
            //Datos enviados a la pagina
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

            /******************************************/
            //Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$rowData]);
            //Muestra los errores
            $this->showError(1, $f3, $result);
        }
    }

    /******************************************************************************/
    //Resumen-Update
    public function ResumenUpdate($f3, $params, $idTipoIngreso){
        /******************************************/
        //Se instancia
        $arrUserData = $this->getUserData($f3);

        /******************************************/
        //Se verifica movimiento
        switch ($idTipoIngreso) {
            case 1: $tsrxName = 'bodegasMovimientoIngreso'; break;//Ingreso
            case 2: $tsrxName = 'bodegasMovimientoEgreso';break;//Egreso
            case 3: $tsrxName = 'bodegasMovimientoTraspaso';break;//Traspaso
        }

        /******************************************/
        //Se crean cadenas
        $DataQuery = '
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

        /******************************************/
        // Se genera la query
        $query = [
            'data'    => $DataQuery,
            'table'   => 'bodegas_movimientos',
            'join'    => $DataJoin,
            'where'   => 'bodegas_movimientos.idMovimiento = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status']){
            /******************************************/
            //Datos enviados a la pagina
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
    public function Insert($f3){

        /******************************/
        // Usuario creador
        $_POST['idUsuario'] = $f3->get('SESSION.DataInfo.UserID');

        /*******************************************************************/
        // Conteo de productos
        $ndata_1 = isset($_POST['idProducto']) ? count($_POST['idProducto']) : 0;

        /******************************/
        //generacion de errores
        if($ndata_1==0) {
            Response::error('No hay productos ingresados', 500);
        }else{

            /******************************************/
            //Se llama al movimiento de materiales
            $Response = $this->createMov($_POST);

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
    }

    /******************************************************************************/
    //Editar por put (solo modificar datos)
    //Editar por post (modificar y subir archivos)
    public function Update($f3){
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            /******************************/
            // Usuario creador
            $_POST['idUsuario'] = $f3->get('SESSION.DataInfo.UserID');

            /******************************/
            //Se genera el chequeo
            $DataCheck = $this->dataCheck_1($_POST);

            /******************************/
            // Se genera la query
            $query = [
                'data'      => 'idMovimiento,idEstadoIngreso,idBodegasIngreso,idBodegasEgreso,Creacion_fecha,Creacion_hora,Observaciones,fecha_auto,idUsuario,idFacturacion',
                'required'  => 'idEstadoIngreso,Creacion_fecha,Creacion_hora,fecha_auto',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'bodegas_movimientos',
                'where'     => 'idMovimiento',
                'Post'      => $_POST,
            ];
            // Ejecuto la query
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
            // Se genera la query
            $query = [
                'files'       => '',
                'table'       => 'bodegas_movimientos',
                'where'       => 'idMovimiento',
                'SubCarpeta'  => '',
                'Post'        => $dataDelete
            ];
            // Ejecuto la query
            $xParams  = ['query' => $query];
            $Response = $this->Base_delete($xParams);

            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response['status']){
                /************************************************/
                //Listado de las tablas a eliminar los datos relacionados
                $arrTableDel  = array();
                $arrTableDel[] = ['files' => '', 'table' => 'bodegas_movimientos_productos'];

                /************************************************/
                // Verifico si existe
                if($arrTableDel){
                    //recorro
                    foreach ($arrTableDel as $tblDel) {
                        // Se genera la query
                        $query = ['files' => $tblDel['files'], 'table' => $tblDel['table'], 'where' => 'idMovimiento', 'SubCarpeta' => '', 'Post' => $dataDelete];
                        // Ejecuto la query
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
    /*                             EJECUCION OTROS                                */
    /******************************************************************************/
    /******************************************************************************/
    //
    public function createMov($PostData){

        /*******************************************************************/
        // Variables
        $ndata_1 = isset($PostData['idProducto']) ? count($PostData['idProducto']) : 0;

        /*******************************************************************/
        //Se inicia la transacción: la reserva, sus recursos asociados y el historial deben
        //aplicarse de forma atómica (todo o nada)
        $this->Base_transactionBegin();

        /******************************/
        // Se genera la query
        $query = [
            'data'      => 'idEstadoIngreso,idBodegasIngreso,idBodegasEgreso,Creacion_fecha,Creacion_hora,Observaciones,fecha_auto,idUsuario,idFacturacion',
            'required'  => 'idEstadoIngreso,Creacion_fecha,Creacion_hora,fecha_auto,idUsuario',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'bodegas_movimientos',
            'Post'      => $PostData
        ];
        //Se genera el chequeo
        $DataCheck_1 = $this->dataCheck_1($PostData);
        // Ejecuto la query
        $xParams  = ['DataCheck' => $DataCheck_1, 'query' => $query];
        $Response = $this->Base_insert($xParams);

        /*******************************************************************/
        //Si falla la reserva principal, se revierte de inmediato
        if (!$Response['status']){
            $this->Base_transactionRollback();
            Response::error('Error al operar con la Base de Datos', 500, $Response['error']);
        }

        /******************************/
        //si es la respuesta esperada
        if ($Response['status']){

            /*******************************************************/
            //Variable
            $chainx_1      = '';
            $ElementsIDs   = [0];
            $arrProdStock  = array();
            //Recorro los productos ingresados
            if(isset($ndata_1)&&$ndata_1!=0){
                for($j1 = 0; $j1 < $ndata_1; $j1++){
                    //se obtiene el producto (bindeado, no concatenado)
                    $ElementsIDs[] = (int)$PostData['idProducto'][$j1];
                    //Se verifica movimiento
                    switch ($PostData['idEstadoIngreso']) {
                        /**************************************************************************************/
                        //Ingreso
                        case 1:
                            $chainx_1 .= ',Cantidad_idBodegas_'.(int)$PostData['idBodegasIngreso'].' AS Cantidad_1';
                            break;
                        /**************************************************************************************/
                        //Egreso
                        case 2:
                            $chainx_1 .= ',Cantidad_idBodegas_'.(int)$PostData['idBodegasEgreso'].' AS Cantidad_1';
                            break;
                        /**************************************************************************************/
                        //Traspaso
                        case 3:
                            $chainx_1 .= ',Cantidad_idBodegas_'.(int)$PostData['idBodegasIngreso'].' AS Cantidad_1';
                            $chainx_1 .= ',Cantidad_idBodegas_'.(int)$PostData['idBodegasEgreso'].' AS Cantidad_2';
                            break;
                    }
                }
            }
            // Genera un '?' por cada id de producto
            $placeholders = implode(',', array_fill(0, count($ElementsIDs), '?'));
            /******************************/
            //Se consultan los stocks
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
            // Ejecuto la query
            $xParams   = ['query' => $query];
            $arrStocks = $this->Base_GetList($xParams);

            // Si falla la consulta, se revierte de inmediato
            if (!$arrStocks['status']){
                $this->Base_transactionRollback();
                Response::error('Error al operar con la Base de Datos', 500, $arrStocks['error']);
            }

            /******************************/
            //Recorro solo si hay datos
            if ($arrStocks['status']){
                foreach ($arrStocks['data'] as $crud){
                    $arrProdStock[$crud['idProducto']]['idStocks']   = $crud['idStocks'];
                    $arrProdStock[$crud['idProducto']]['Cantidad_1'] = $crud['Cantidad_1'];
                    if(isset($crud['Cantidad_2'])&&$crud['Cantidad_2']!=''){
                        $arrProdStock[$crud['idProducto']]['Cantidad_2'] = $crud['Cantidad_2'];
                    }
                }
            }

            /******************************/
            //Recorro los productos ingresados
            if(isset($ndata_1)&&$ndata_1!=0){
                for($j1 = 0; $j1 < $ndata_1; $j1++){
                    //Se verifica movimiento
                    switch ($PostData['idEstadoIngreso']) {
                        /**************************************************************************************/
                        /**************************************************************************************/
                        //Ingreso
                        case 1:
                            /******************************/
                            // Se agrega respuesta
                            $arrTareas = [
                                'idMovimiento'    => $Response['data'],
                                'idEstadoIngreso' => $PostData['idEstadoIngreso'],
                                'idBodegas'       => $PostData['idBodegasIngreso'],
                                'idProducto'      => $PostData['idProducto'][$j1],
                                'Number'          => $PostData['Number'][$j1],
                            ];
                            /******************************/
                            // Se genera la query
                            $query = [
                                'data'      => 'idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
                                'required'  => 'idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
                                'unique'    => '',
                                'encode'    => '',
                                'table'     => 'bodegas_movimientos_productos',
                                'Post'      => $arrTareas
                            ];
                            //Se genera el chequeo
                            $DataCheck_2 = $this->DataCheck_2($arrTareas);
                            // Ejecuto la query
                            $xParams = ['DataCheck' => $DataCheck_2, 'query' => $query];
                            $xInsert = $this->Base_insert($xParams);

                            // Si falla la consulta, se revierte de inmediato
                            if (!$xInsert['status']){
                                $this->Base_transactionRollback();
                                Response::error('Error al operar con la Base de Datos', 500, $xInsert['error']);
                            }

                            /******************************/
                            //Se Actualizan los stocks
                            //verifico si existe el dato en el stock
                            if(isset($arrProdStock[$PostData['idProducto'][$j1]]['idStocks'])&&$arrProdStock[$PostData['idProducto'][$j1]]['idStocks']!=''){
                                /******************************/
                                // Se agrega respuesta
                                $arrTareas = [
                                    'idStocks'                                          => $arrProdStock[$PostData['idProducto'][$j1]]['idStocks'],
                                    'Cantidad_idBodegas_'.$PostData['idBodegasIngreso'] => ($arrProdStock[$PostData['idProducto'][$j1]]['Cantidad_1'] + $PostData['Number'][$j1]),
                                ];
                                /******************************/
                                // Se genera la query
                                $query = [
                                    'data'      => 'idStocks,Cantidad_idBodegas_'.$PostData['idBodegasIngreso'],
                                    'required'  => 'idStocks,Cantidad_idBodegas_'.$PostData['idBodegasIngreso'],
                                    'unique'    => '',
                                    'encode'    => '',
                                    'table'     => 'bodegas_productos_stocks',
                                    'where'     => 'idStocks',
                                    'Post'      => $arrTareas
                                ];
                                //Se genera el chequeo
                                $DataCheck_3 = $this->DataCheck_3($arrTareas);
                                // Ejecuto la query
                                $xParams = ['DataCheck' => $DataCheck_3, 'query' => $query];
                                $xUpdate = $this->Base_update($xParams);

                                // Si falla la consulta, se revierte de inmediato
                                if (!$xUpdate['status']){
                                    $this->Base_transactionRollback();
                                    Response::error('Error al operar con la Base de Datos', 500, $xUpdate['error']);
                                }
                            }else{
                                /******************************/
                                // Se agrega respuesta
                                $arrTareas = [
                                    'idProducto'                                        => $PostData['idProducto'][$j1],
                                    'Cantidad_idBodegas_'.$PostData['idBodegasIngreso'] => $PostData['Number'][$j1],
                                ];
                                /******************************/
                                // Se genera la query
                                $query = [
                                    'data'      => 'idProducto,Cantidad_idBodegas_'.$PostData['idBodegasIngreso'],
                                    'required'  => 'idProducto,Cantidad_idBodegas_'.$PostData['idBodegasIngreso'],
                                    'unique'    => '',
                                    'encode'    => '',
                                    'table'     => 'bodegas_productos_stocks',
                                    'Post'      => $arrTareas
                                ];
                                //Se genera el chequeo
                                $DataCheck_3 = $this->DataCheck_3($arrTareas);
                                // Ejecuto la query
                                $xParams = ['DataCheck' => $DataCheck_3, 'query' => $query];
                                $xInsert = $this->Base_insert($xParams);

                                // Si falla la consulta, se revierte de inmediato
                                if (!$xInsert['status']){
                                    $this->Base_transactionRollback();
                                    Response::error('Error al operar con la Base de Datos', 500, $xInsert['error']);
                                }
                            }
                            break;
                        /**************************************************************************************/
                        /**************************************************************************************/
                        //Egreso
                        case 2:
                            /******************************/
                            // Se agrega respuesta
                            $arrTareas = [
                                'idMovimiento'    => $Response['data'],
                                'idEstadoIngreso' => $PostData['idEstadoIngreso'],
                                'idBodegas'       => $PostData['idBodegasEgreso'],
                                'idProducto'      => $PostData['idProducto'][$j1],
                                'Number'          => $PostData['Number'][$j1],
                            ];
                            /******************************/
                            // Se genera la query
                            $query = [
                                'data'      => 'idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
                                'required'  => 'idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
                                'unique'    => '',
                                'encode'    => '',
                                'table'     => 'bodegas_movimientos_productos',
                                'Post'      => $arrTareas
                            ];
                            //Se genera el chequeo
                            $DataCheck_2 = $this->DataCheck_2($arrTareas);
                            // Ejecuto la query
                            $xParams = ['DataCheck' => $DataCheck_2, 'query' => $query];
                            $xInsert = $this->Base_insert($xParams);

                            // Si falla la consulta, se revierte de inmediato
                            if (!$xInsert['status']){
                                $this->Base_transactionRollback();
                                Response::error('Error al operar con la Base de Datos', 500, $xInsert['error']);
                            }
                            /******************************/
                            //Se Actualizan los stocks
                            //verifico si existe el dato en el stock
                            if(isset($arrProdStock[$PostData['idProducto'][$j1]]['idStocks'])&&$arrProdStock[$PostData['idProducto'][$j1]]['idStocks']!=''){
                                /******************************/
                                // Se agrega respuesta
                                $arrTareas = [
                                    'idStocks'                                         => $arrProdStock[$PostData['idProducto'][$j1]]['idStocks'],
                                    'Cantidad_idBodegas_'.$PostData['idBodegasEgreso'] => ($arrProdStock[$PostData['idProducto'][$j1]]['Cantidad_1'] - $PostData['Number'][$j1]),
                                ];
                                /******************************/
                                // Se genera la query
                                $query = [
                                    'data'      => 'idStocks,Cantidad_idBodegas_'.$PostData['idBodegasEgreso'],
                                    'required'  => 'idStocks,Cantidad_idBodegas_'.$PostData['idBodegasEgreso'],
                                    'unique'    => '',
                                    'encode'    => '',
                                    'table'     => 'bodegas_productos_stocks',
                                    'where'     => 'idStocks',
                                    'Post'      => $arrTareas
                                ];
                                //Se genera el chequeo
                                $DataCheck_3 = $this->DataCheck_3($arrTareas);
                                // Ejecuto la query
                                $xParams = ['DataCheck' => $DataCheck_3, 'query' => $query];
                                $xUpdate = $this->Base_update($xParams);

                                // Si falla la consulta, se revierte de inmediato
                                if (!$xUpdate['status']){
                                    $this->Base_transactionRollback();
                                    Response::error('Error al operar con la Base de Datos', 500, $xUpdate['error']);
                                }
                            }else{
                                /******************************/
                                // Se agrega respuesta
                                $arrTareas = [
                                    'idProducto'                                       => $PostData['idProducto'][$j1],
                                    'Cantidad_idBodegas_'.$PostData['idBodegasEgreso'] => (0 - $PostData['Number'][$j1]),
                                ];
                                /******************************/
                                // Se genera la query
                                $query = [
                                    'data'      => 'idProducto,Cantidad_idBodegas_'.$PostData['idBodegasEgreso'],
                                    'required'  => 'idProducto,Cantidad_idBodegas_'.$PostData['idBodegasEgreso'],
                                    'unique'    => '',
                                    'encode'    => '',
                                    'table'     => 'bodegas_productos_stocks',
                                    'Post'      => $arrTareas
                                ];
                                //Se genera el chequeo
                                $DataCheck_3 = $this->DataCheck_3($arrTareas);
                                // Ejecuto la query
                                $xParams = ['DataCheck' => $DataCheck_3, 'query' => $query];
                                $xInsert = $this->Base_insert($xParams);

                                // Si falla la consulta, se revierte de inmediato
                                if (!$xInsert['status']){
                                    $this->Base_transactionRollback();
                                    Response::error('Error al operar con la Base de Datos', 500, $xInsert['error']);
                                }
                            }
                            break;
                        /**************************************************************************************/
                        /**************************************************************************************/
                        //Traspaso
                        case 3:
                            /******************************/
                            // Se agrega respuesta
                            $arrTareas = [
                                'idMovimiento'    => $Response['data'],
                                'idEstadoIngreso' => 2,
                                'idBodegas'       => $PostData['idBodegasEgreso'],
                                'idProducto'      => $PostData['idProducto'][$j1],
                                'Number'          => $PostData['Number'][$j1],
                            ];
                            /******************************/
                            // Se genera la query
                            $query = [
                                'data'      => 'idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
                                'required'  => 'idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
                                'unique'    => '',
                                'encode'    => '',
                                'table'     => 'bodegas_movimientos_productos',
                                'Post'      => $arrTareas
                            ];
                            //Se genera el chequeo
                            $DataCheck_2 = $this->DataCheck_2($arrTareas);
                            // Ejecuto la query
                            $xParams = ['DataCheck' => $DataCheck_2, 'query' => $query];
                            $xInsert = $this->Base_insert($xParams);

                            // Si falla la consulta, se revierte de inmediato
                            if (!$xInsert['status']){
                                $this->Base_transactionRollback();
                                Response::error('Error al operar con la Base de Datos', 500, $xInsert['error']);
                            }
                            /************************************************************/
                            /************************************************************/
                            // Se agrega respuesta
                            $arrTareas = [
                                'idMovimiento'    => $Response['data'],
                                'idEstadoIngreso' => 1,
                                'idBodegas'       => $PostData['idBodegasIngreso'],
                                'idProducto'      => $PostData['idProducto'][$j1],
                                'Number'          => $PostData['Number'][$j1],
                            ];
                            /******************************/
                            // Se genera la query
                            $query = [
                                'data'      => 'idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
                                'required'  => 'idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
                                'unique'    => '',
                                'encode'    => '',
                                'table'     => 'bodegas_movimientos_productos',
                                'Post'      => $arrTareas
                            ];
                            //Se genera el chequeo
                            $DataCheck_2 = $this->DataCheck_2($arrTareas);
                            // Ejecuto la query
                            $xParams = ['DataCheck' => $DataCheck_2, 'query' => $query];
                            $xInsert = $this->Base_insert($xParams);

                            // Si falla la consulta, se revierte de inmediato
                            if (!$xInsert['status']){
                                $this->Base_transactionRollback();
                                Response::error('Error al operar con la Base de Datos', 500, $xInsert['error']);
                            }
                            /******************************/
                            //Se Actualizan los stocks
                            //verifico si existe el dato en el stock
                            if(isset($arrProdStock[$PostData['idProducto'][$j1]]['idStocks'])&&$arrProdStock[$PostData['idProducto'][$j1]]['idStocks']!=''){
                                /******************************/
                                // Se agrega respuesta
                                $arrTareas = [
                                    'idStocks'                                          => $arrProdStock[$PostData['idProducto'][$j1]]['idStocks'],
                                    'Cantidad_idBodegas_'.$PostData['idBodegasIngreso'] => ($arrProdStock[$PostData['idProducto'][$j1]]['Cantidad_1'] + $PostData['Number'][$j1]),
                                    'Cantidad_idBodegas_'.$PostData['idBodegasEgreso']  => ($arrProdStock[$PostData['idProducto'][$j1]]['Cantidad_2'] - $PostData['Number'][$j1]),
                                ];
                                /******************************/
                                // Se genera la query
                                $query = [
                                    'data'      => 'idStocks,Cantidad_idBodegas_'.$PostData['idBodegasIngreso'].',Cantidad_idBodegas_'.$PostData['idBodegasEgreso'],
                                    'required'  => 'idStocks,Cantidad_idBodegas_'.$PostData['idBodegasIngreso'].',Cantidad_idBodegas_'.$PostData['idBodegasEgreso'],
                                    'unique'    => '',
                                    'encode'    => '',
                                    'table'     => 'bodegas_productos_stocks',
                                    'where'     => 'idStocks',
                                    'Post'      => $arrTareas
                                ];
                                //Se genera el chequeo
                                $DataCheck_3 = $this->DataCheck_3($arrTareas);
                                // Ejecuto la query
                                $xParams = ['DataCheck' => $DataCheck_3, 'query' => $query];
                                $xUpdate = $this->Base_update($xParams);

                                // Si falla la consulta, se revierte de inmediato
                                if (!$xUpdate['status']){
                                    $this->Base_transactionRollback();
                                    Response::error('Error al operar con la Base de Datos', 500, $xUpdate['error']);
                                }
                            }else{
                                /******************************/
                                // Se agrega respuesta
                                $arrTareas = [
                                    'idProducto'                                        => $PostData['idProducto'][$j1],
                                    'Cantidad_idBodegas_'.$PostData['idBodegasIngreso'] => $PostData['Number'][$j1],
                                    'Cantidad_idBodegas_'.$PostData['idBodegasEgreso']  => (0 - $PostData['Number'][$j1]),
                                ];
                                /******************************/
                                // Se genera la query
                                $query = [
                                    'data'      => 'idProducto,Cantidad_idBodegas_'.$PostData['idBodegasIngreso'].',Cantidad_idBodegas_'.$PostData['idBodegasEgreso'],
                                    'required'  => 'idProducto,Cantidad_idBodegas_'.$PostData['idBodegasIngreso'].',Cantidad_idBodegas_'.$PostData['idBodegasEgreso'],
                                    'unique'    => '',
                                    'encode'    => '',
                                    'table'     => 'bodegas_productos_stocks',
                                    'Post'      => $arrTareas
                                ];
                                //Se genera el chequeo
                                $DataCheck_3 = $this->DataCheck_3($arrTareas);
                                // Ejecuto la query
                                $xParams = ['DataCheck' => $DataCheck_3, 'query' => $query];
                                $xInsert = $this->Base_insert($xParams);

                                // Si falla la consulta, se revierte de inmediato
                                if (!$xInsert['status']){
                                    $this->Base_transactionRollback();
                                    Response::error('Error al operar con la Base de Datos', 500, $xInsert['error']);
                                }
                            }
                            break;
                    }
                }
            }
        }

        /*******************************************************************/
        //Se confirma la transacción
        $this->Base_transactionCommit();

        /******************************/
        // Devuelvo siempre el resultado
        return $Response;

    }

    /******************************************************************************/
    /*                             Métodos privados                               */
    /******************************************************************************/
    /******************************************************************************/
    //Se validan los datos
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
        //Devuelvo
        return $DataChecking;
    }

    /******************************************************************************/
    //Se validan los datos
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
        //Devuelvo
        return $DataChecking;
    }

    /******************************************************************************/
    //Se validan los datos
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
        //Devuelvo
        return $DataChecking;
    }

}
