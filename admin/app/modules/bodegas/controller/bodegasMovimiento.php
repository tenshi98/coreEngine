<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class bodegasMovimiento extends ControllerBase {

    /******************************************************************************/
    //Variables
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
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
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
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se verifica movimiento
        switch ($idTipoIngreso) {
            case 1: $tsrxName = 'bodegasMovimientoIngreso';  $TipoMov = 'Ingresos a';    break;//Ingreso
            case 2: $tsrxName = 'bodegasMovimientoEgreso';   $TipoMov = 'Egresos a';     break;//Egreso
            case 3: $tsrxName = 'bodegasMovimientoTraspaso'; $TipoMov = 'Traspasos de';  break;//Traspaso
        }

        /*******************************************************************/
        //Se genera la query
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
            'where'   => 'bodegas_movimientos.idEstadoIngreso = "'.$idTipoIngreso.'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'bodegas_movimientos.Creacion_fecha DESC, bodegas_movimientos.Creacion_hora DESC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $arrList = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idBodegas AS ID,Nombre',
            'table'   => 'bodegas_listado',
            'join'    => '',
            'where'   => 'idEstado=1',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams    = ['query' => $query];
        $arrBodegas = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idProducto AS ID,Nombre',
            'table'   => 'productos_listado',
            'join'    => '',
            'where'   => 'idEstado=1',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams      = ['query' => $query];
        $arrProductos = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idDocumentos AS ID,Nombre',
            'table'   => 'core_documentos_mercantiles',
            'join'    => '',
            'where'   => 'idDocumentos!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams       = ['query' => $query];
        $arrDocumentos = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($arrList)){

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
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$tsrxName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_Codification'    => $this->Codification,
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                'Fnc_ServerServer'    => $this->ServerServer,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList,
                'arrBodegas'      => $arrBodegas,
                'arrProductos'    => $arrProductos,
                'idTipoIngreso'   => $idTipoIngreso,
                'arrDocumentos'   => $arrDocumentos,

            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 1, $f3);
        }
    }

    /******************************************************************************/
    //List
    public function UpdateList($f3, $idTipoIngreso){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se verifica movimiento
        switch ($idTipoIngreso) {
            case 1: $tsrxName = 'bodegasMovimientoIngreso';  $TipoMov = 'Ingresos a';    break;//Ingreso
            case 2: $tsrxName = 'bodegasMovimientoEgreso';   $TipoMov = 'Egresos a';     break;//Egreso
            case 3: $tsrxName = 'bodegasMovimientoTraspaso'; $TipoMov = 'Traspasos de';  break;//Traspaso
        }

        /*******************************************************************/
        //Variables
        $WhereData_bod_int     = 'idMovimiento,idBodegasIngreso,idBodegasEgreso';   //Datos búsqueda exacta
        $WhereData_bod_string  = '';                                                //Datos búsqueda relativa
        $WhereData_bod_between = 'Creacion_fecha-F_Inicio-F_Termino';               //Datos búsqueda Between
        $WhereData_fac_int     = 'idDocumentos,idFacturacion';                      //Datos búsqueda exacta
        $WhereData_fac_string  = 'N_Doc';                                           //Datos búsqueda relativa
        $WhereData_fac_between = '';                                                //Datos búsqueda Between
        $whereInt              = '';                                                    //se crea cadena
        /******************************************/
        //agrego variable busqueda
        $whereInt = $this->searchWhere($whereInt, $WhereData_bod_int, 'bodegas_movimientos', 1);
        $whereInt = $this->searchWhere($whereInt, $WhereData_bod_string, 'bodegas_movimientos', 2);
        $whereInt = $this->searchWhere($whereInt, $WhereData_bod_between, 'bodegas_movimientos', 3);
        $whereInt = $this->searchWhere($whereInt, $WhereData_fac_int, 'facturacion_listado', 1);
        $whereInt = $this->searchWhere($whereInt, $WhereData_fac_string, 'facturacion_listado', 2);
        $whereInt = $this->searchWhere($whereInt, $WhereData_fac_between, 'facturacion_listado', 3);
        //Verifico si esta vacio
        $whereInt2 = $whereInt ? $whereInt . ' AND bodegas_movimientos.idEstadoIngreso = "'.$idTipoIngreso.'"' : 'bodegas_movimientos.idEstadoIngreso = "'.$idTipoIngreso.'"';

        /******************************/
        //Se genera la query
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
            'where'   => $whereInt2,
            'group'   => '',
            'having'  => '',
            'order'   => 'bodegas_movimientos.Creacion_fecha DESC, bodegas_movimientos.Creacion_hora DESC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $arrList = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($arrList)){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'TableTitle'      => $TipoMov.' Bodegas',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$tsrxName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'     => $this->Codification,
                'Fnc_DataDate'         => $this->DataDate,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList,
                'idTipoIngreso'   => $idTipoIngreso,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-UpdateList.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //View
    public function View($f3, $params, $idTipoIngreso){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

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
        if($UserData["gestionDocumentosUsoBodega"]==2){
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
        //Se genera la query
        $query = [
            'data'    => $DataQuery,
            'table'   => 'bodegas_movimientos',
            'join'    => $DataJoin,
            'where'   => 'bodegas_movimientos.idMovimiento = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
            'where'   => 'bodegas_movimientos_productos.idMovimiento = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'bodegas_movimientos_productos.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams      = ['query' => $query];
        $arrProductos = $this->Base_GetList($xParams);

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
                'UserAccess'    => $arrLevel[$tsrxName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_Codification'     => $this->Codification,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData,
                'arrProductos'     => $arrProductos,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-View.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //Resumen
    public function Resumen($f3, $params, $idTipoIngreso){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

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
        if($UserData["gestionDocumentosUsoBodega"]==2){
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
        //Se genera la query
        $query = [
            'data'    => $DataQuery,
            'table'   => 'bodegas_movimientos',
            'join'    => $DataJoin,
            'where'   => 'bodegas_movimientos.idMovimiento = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
        if ($rowData!==false) {
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'        => 'Resumen Movimiento',
                'PageDescription'  => 'Resumen Movimiento.',
                'PageAuthor'       => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'     => ConfigAPP::SOFTWARE['SoftwareName'],
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$tsrxName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'       => $this->FormInputs,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_Codification'     => $this->Codification,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 1, $f3);
        }
    }

    /******************************************************************************/
    //Resumen-Update
    public function ResumenUpdate($f3, $params, $idTipoIngreso){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

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
        if($UserData["gestionDocumentosUsoBodega"]==2){
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
        //Se genera la query
        $query = [
            'data'    => $DataQuery,
            'table'   => 'bodegas_movimientos',
            'join'    => $DataJoin,
            'where'   => 'bodegas_movimientos.idMovimiento = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
        if ($rowData!==false) {
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$tsrxName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Update.php');
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

        /*******************************************************************/
        // Conteo de productos
        $ndata_1 = isset($_POST['idProducto']) ? count($_POST['idProducto']) : 0;

        /******************************/
        //generacion de errores
        if($ndata_1==0) {
            echo Response::sendData(500, 'No hay productos ingresados');
        }else{

            /******************************************/
            //Se llama al movimiento de materiales
            $Response = $this->createMov($_POST);

            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un ID numérico o algún otro valor.
            if (is_numeric($Response)) {
                // Si es un ID numérico, encripta y envía con código 200 (OK)
                $Data = $this->Codification->encryptDecrypt('encrypt', $Response);
                echo Response::sendData(200, $Data);
            } else {
                // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
                // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
                echo Response::sendData(500, $Response);
            }

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
                'data'      => 'idEstadoIngreso,idBodegasIngreso,idBodegasEgreso,Creacion_fecha,Creacion_hora,Observaciones,fecha_auto,idUsuario,idFacturacion',
                'required'  => 'idEstadoIngreso,Creacion_fecha,Creacion_hora,fecha_auto',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'bodegas_movimientos',
                'where'     => 'idMovimiento',
                'Post'      => $_POST,
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
                'table'       => 'bodegas_movimientos',
                'where'       => 'idMovimiento',
                'SubCarpeta'  => '',
                'Post'        => $dataDelete
            ];
            //Ejecuto la query
            $xParams  = ['query' => $query];
            $Response = $this->Base_delete($xParams);

            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response===true) {
                /************************************************/
                //Listado de las tablas a eliminar los datos relacionados
                $arrTableDel  = array();
                $arrTableDel[] = ['files' => '', 'table' => 'bodegas_movimientos_productos'];

                /************************************************/
                //Verifico si existe
                if($arrTableDel){
                    //recorro
                    foreach ($arrTableDel as $tblDel) {
                        //Se genera la query
                        $query = ['files' => $tblDel['files'], 'table' => $tblDel['table'], 'where' => 'idMovimiento', 'SubCarpeta' => '', 'Post' => $dataDelete];
                        //Ejecuto la query
                        $xParams = ['query' => $query];
                        $this->Base_delete($xParams);
                    }
                }

                /******************************/
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
            'ValidarEmail'              => '',
            'ValidarNumero'             => '',
            'ValidarEntero'             => '',
            'ValidarRut'                => '',
            'ValidarPatente'            => '',
            'ValidarFecha'              => 'fecha_auto,Creacion_fecha',
            'ValidarHora'               => '',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => 'Observaciones',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => '',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Observaciones',
            'ValidarEspaciosVacios'     => '',
            'ValidarMayusculas'         => '',
            'ValidarCoincidencias'      => '',
            'Post'                      => $POST,
        ];
        //Devuelvo
        return $DataChecking;
    }

    /******************************************************************************/
    /*                             EJECUCION OTROS                                */
    /******************************************************************************/
    public function createMov($PostData){

        /*******************************************************************/
        //variables
        $ndata_1 = isset($PostData['idProducto']) ? count($PostData['idProducto']) : 0;

        /******************************/
        //Se genera el chequeo
        $DataCheck = $this->dataCheck($PostData);

        /******************************/
        //Se genera la query
        $query = [
            'data'      => 'idEstadoIngreso,idBodegasIngreso,idBodegasEgreso,Creacion_fecha,Creacion_hora,Observaciones,fecha_auto,idUsuario,idFacturacion',
            'required'  => 'idEstadoIngreso,Creacion_fecha,Creacion_hora,fecha_auto,idUsuario',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'bodegas_movimientos',
            'Post'      => $PostData
        ];
        //Ejecuto la query
        //Ejecuto la query
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        $Response = $this->Base_insert($xParams);

        /******************************/
        //si es la respuesta esperada
        if (is_numeric($Response)) {

            /*******************************************************/
            //Variable
            $chainx_1      = '';
            $chainx_2      = '0';
            $arrProdStock  = array();
            //Recorro los productos ingresados
            if(isset($ndata_1)&&$ndata_1!=0){
                for($j1 = 0; $j1 < $ndata_1; $j1++){
                    //se obtiene el producto
                    $chainx_2 .= ','.$PostData['idProducto'][$j1];
                    //Se verifica movimiento
                    switch ($PostData['idEstadoIngreso']) {
                        /**************************************************************************************/
                        //Ingreso
                        case 1:
                            $chainx_1 .= ',Cantidad_idBodegas_'.$PostData['idBodegasIngreso'].' AS Cantidad_1';
                            break;
                        /**************************************************************************************/
                        //Egreso
                        case 2:
                            $chainx_1 .= ',Cantidad_idBodegas_'.$PostData['idBodegasEgreso'].' AS Cantidad_1';
                            break;
                        /**************************************************************************************/
                        //Traspaso
                        case 3:
                            $chainx_1 .= ',Cantidad_idBodegas_'.$PostData['idBodegasIngreso'].' AS Cantidad_1';
                            $chainx_1 .= ',Cantidad_idBodegas_'.$PostData['idBodegasEgreso'].' AS Cantidad_2';
                            break;
                    }
                }
            }
            /******************************/
            //Se consultan los stocks
            $query = [
                'data'    => 'idStocks,idProducto'.$chainx_1,
                'table'   => 'bodegas_productos_stocks',
                'join'    => '',
                'where'   => 'idProducto IN ('.$chainx_2.')',
                'group'   => '',
                'having'  => '',
                'order'   => 'idProducto ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            //Ejecuto la query
            $xParams   = ['query' => $query];
            $arrStocks = $this->Base_GetList($xParams);

            /******************************/
            //Recorro
            foreach($arrStocks AS $crud){
                $arrProdStock[$crud['idProducto']]['idStocks']   = $crud['idStocks'];
                $arrProdStock[$crud['idProducto']]['Cantidad_1'] = $crud['Cantidad_1'];
                if(isset($crud['Cantidad_2'])&&$crud['Cantidad_2']!=''){
                    $arrProdStock[$crud['idProducto']]['Cantidad_2'] = $crud['Cantidad_2'];
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
                            //Se agrega respuesta
                            $arrTareas = [
                                'idMovimiento'    => $Response,
                                'idEstadoIngreso' => $PostData['idEstadoIngreso'],
                                'idBodegas'       => $PostData['idBodegasIngreso'],
                                'idProducto'      => $PostData['idProducto'][$j1],
                                'Number'          => $PostData['Number'][$j1],
                            ];
                            /******************************/
                            //Se genera la query
                            $query = [
                                'data'      => 'idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
                                'required'  => 'idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
                                'unique'    => '',
                                'encode'    => '',
                                'table'     => 'bodegas_movimientos_productos',
                                'Post'      => $arrTareas
                            ];
                            //Ejecuto la query
                            $xParams = ['DataCheck' => '', 'query' => $query];
                            $this->Base_insert($xParams);
                            /******************************/
                            //Se Actualizan los stocks
                            //verifico si existe el dato en el stock
                            if(isset($arrProdStock[$PostData['idProducto'][$j1]]['idStocks'])&&$arrProdStock[$PostData['idProducto'][$j1]]['idStocks']!=''){
                                /******************************/
                                //Se agrega respuesta
                                $arrTareas = [
                                    'idStocks'                                          => $arrProdStock[$PostData['idProducto'][$j1]]['idStocks'],
                                    'Cantidad_idBodegas_'.$PostData['idBodegasIngreso'] => ($arrProdStock[$PostData['idProducto'][$j1]]['Cantidad_1'] + $PostData['Number'][$j1]),
                                ];
                                /******************************/
                                //Se genera la query
                                $query = [
                                    'data'      => 'idStocks,Cantidad_idBodegas_'.$PostData['idBodegasIngreso'],
                                    'required'  => 'idStocks,Cantidad_idBodegas_'.$PostData['idBodegasIngreso'],
                                    'unique'    => '',
                                    'encode'    => '',
                                    'table'     => 'bodegas_productos_stocks',
                                    'where'     => 'idStocks',
                                    'Post'      => $arrTareas
                                ];
                                //Ejecuto la query
                                $xParams = ['DataCheck' => '', 'query' => $query];
                                $this->Base_update($xParams);

                            }else{
                                /******************************/
                                //Se agrega respuesta
                                $arrTareas = [
                                    'idProducto'                                        => $PostData['idProducto'][$j1],
                                    'Cantidad_idBodegas_'.$PostData['idBodegasIngreso'] => $PostData['Number'][$j1],
                                ];
                                /******************************/
                                //Se genera la query
                                $query = [
                                    'data'      => 'idProducto,Cantidad_idBodegas_'.$PostData['idBodegasIngreso'],
                                    'required'  => 'idProducto,Cantidad_idBodegas_'.$PostData['idBodegasIngreso'],
                                    'unique'    => '',
                                    'encode'    => '',
                                    'table'     => 'bodegas_productos_stocks',
                                    'Post'      => $arrTareas
                                ];
                                //Ejecuto la query
                                $xParams = ['DataCheck' => '', 'query' => $query];
                                $this->Base_insert($xParams);
                            }
                            break;
                        /**************************************************************************************/
                        /**************************************************************************************/
                        //Egreso
                        case 2:
                            /******************************/
                            //Se agrega respuesta
                            $arrTareas = [
                                'idMovimiento'    => $Response,
                                'idEstadoIngreso' => $PostData['idEstadoIngreso'],
                                'idBodegas'       => $PostData['idBodegasEgreso'],
                                'idProducto'      => $PostData['idProducto'][$j1],
                                'Number'          => $PostData['Number'][$j1],
                            ];
                            /******************************/
                            //Se genera la query
                            $query = [
                                'data'      => 'idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
                                'required'  => 'idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
                                'unique'    => '',
                                'encode'    => '',
                                'table'     => 'bodegas_movimientos_productos',
                                'Post'      => $arrTareas
                            ];
                            //Ejecuto la query
                            $xParams = ['DataCheck' => '', 'query' => $query];
                            $this->Base_insert($xParams);
                            /******************************/
                            //Se Actualizan los stocks
                            //verifico si existe el dato en el stock
                            if(isset($arrProdStock[$PostData['idProducto'][$j1]]['idStocks'])&&$arrProdStock[$PostData['idProducto'][$j1]]['idStocks']!=''){
                                /******************************/
                                //Se agrega respuesta
                                $arrTareas = [
                                    'idStocks'                                         => $arrProdStock[$PostData['idProducto'][$j1]]['idStocks'],
                                    'Cantidad_idBodegas_'.$PostData['idBodegasEgreso'] => ($arrProdStock[$PostData['idProducto'][$j1]]['Cantidad_1'] - $PostData['Number'][$j1]),
                                ];
                                /******************************/
                                //Se genera la query
                                $query = [
                                    'data'      => 'idStocks,Cantidad_idBodegas_'.$PostData['idBodegasEgreso'],
                                    'required'  => 'idStocks,Cantidad_idBodegas_'.$PostData['idBodegasEgreso'],
                                    'unique'    => '',
                                    'encode'    => '',
                                    'table'     => 'bodegas_productos_stocks',
                                    'where'     => 'idStocks',
                                    'Post'      => $arrTareas
                                ];
                                //Ejecuto la query
                                $xParams = ['DataCheck' => '', 'query' => $query];
                                $this->Base_update($xParams);
                            }else{
                                /******************************/
                                //Se agrega respuesta
                                $arrTareas = [
                                    'idProducto'                                       => $PostData['idProducto'][$j1],
                                    'Cantidad_idBodegas_'.$PostData['idBodegasEgreso'] => (0 - $PostData['Number'][$j1]),
                                ];
                                /******************************/
                                //Se genera la query
                                $query = [
                                    'data'      => 'idProducto,Cantidad_idBodegas_'.$PostData['idBodegasEgreso'],
                                    'required'  => 'idProducto,Cantidad_idBodegas_'.$PostData['idBodegasEgreso'],
                                    'unique'    => '',
                                    'encode'    => '',
                                    'table'     => 'bodegas_productos_stocks',
                                    'Post'      => $arrTareas
                                ];
                                //Ejecuto la query
                                $xParams = ['DataCheck' => '', 'query' => $query];
                                $this->Base_insert($xParams);
                            }
                            break;
                        /**************************************************************************************/
                        /**************************************************************************************/
                        //Traspaso
                        case 3:
                            /******************************/
                            //Se agrega respuesta
                            $arrTareas = [
                                'idMovimiento'    => $Response,
                                'idEstadoIngreso' => 2,
                                'idBodegas'       => $PostData['idBodegasEgreso'],
                                'idProducto'      => $PostData['idProducto'][$j1],
                                'Number'          => $PostData['Number'][$j1],
                            ];
                            /******************************/
                            //Se genera la query
                            $query = [
                                'data'      => 'idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
                                'required'  => 'idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
                                'unique'    => '',
                                'encode'    => '',
                                'table'     => 'bodegas_movimientos_productos',
                                'Post'      => $arrTareas
                            ];
                            //Ejecuto la query
                            $xParams = ['DataCheck' => '', 'query' => $query];
                            $this->Base_insert($xParams);
                            /************************************************************/
                            /************************************************************/
                            //Se agrega respuesta
                            $arrTareas = [
                                'idMovimiento'    => $Response,
                                'idEstadoIngreso' => 1,
                                'idBodegas'       => $PostData['idBodegasIngreso'],
                                'idProducto'      => $PostData['idProducto'][$j1],
                                'Number'          => $PostData['Number'][$j1],
                            ];
                            /******************************/
                            //Se genera la query
                            $query = [
                                'data'      => 'idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
                                'required'  => 'idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
                                'unique'    => '',
                                'encode'    => '',
                                'table'     => 'bodegas_movimientos_productos',
                                'Post'      => $arrTareas
                            ];
                            //Ejecuto la query
                            $xParams = ['DataCheck' => '', 'query' => $query];
                            $this->Base_insert($xParams);
                            /******************************/
                            //Se Actualizan los stocks
                            //verifico si existe el dato en el stock
                            if(isset($arrProdStock[$PostData['idProducto'][$j1]]['idStocks'])&&$arrProdStock[$PostData['idProducto'][$j1]]['idStocks']!=''){
                                /******************************/
                                //Se agrega respuesta
                                $arrTareas = [
                                    'idStocks'                                          => $arrProdStock[$PostData['idProducto'][$j1]]['idStocks'],
                                    'Cantidad_idBodegas_'.$PostData['idBodegasIngreso'] => ($arrProdStock[$PostData['idProducto'][$j1]]['Cantidad_1'] + $PostData['Number'][$j1]),
                                    'Cantidad_idBodegas_'.$PostData['idBodegasEgreso']  => ($arrProdStock[$PostData['idProducto'][$j1]]['Cantidad_2'] - $PostData['Number'][$j1]),
                                ];
                                /******************************/
                                //Se genera la query
                                $query = [
                                    'data'      => 'idStocks,Cantidad_idBodegas_'.$PostData['idBodegasIngreso'].',Cantidad_idBodegas_'.$PostData['idBodegasEgreso'],
                                    'required'  => 'idStocks,Cantidad_idBodegas_'.$PostData['idBodegasIngreso'].',Cantidad_idBodegas_'.$PostData['idBodegasEgreso'],
                                    'unique'    => '',
                                    'encode'    => '',
                                    'table'     => 'bodegas_productos_stocks',
                                    'where'     => 'idStocks',
                                    'Post'      => $arrTareas
                                ];
                                //Ejecuto la query
                                $xParams = ['DataCheck' => '', 'query' => $query];
                                $this->Base_update($xParams);
                            }else{
                                /******************************/
                                //Se agrega respuesta
                                $arrTareas = [
                                    'idProducto'                                        => $PostData['idProducto'][$j1],
                                    'Cantidad_idBodegas_'.$PostData['idBodegasIngreso'] => $PostData['Number'][$j1],
                                    'Cantidad_idBodegas_'.$PostData['idBodegasEgreso']  => (0 - $PostData['Number'][$j1]),
                                ];
                                /******************************/
                                //Se genera la query
                                $query = [
                                    'data'      => 'idProducto,Cantidad_idBodegas_'.$PostData['idBodegasIngreso'].',Cantidad_idBodegas_'.$PostData['idBodegasEgreso'],
                                    'required'  => 'idProducto,Cantidad_idBodegas_'.$PostData['idBodegasIngreso'].',Cantidad_idBodegas_'.$PostData['idBodegasEgreso'],
                                    'unique'    => '',
                                    'encode'    => '',
                                    'table'     => 'bodegas_productos_stocks',
                                    'Post'      => $arrTareas
                                ];
                                //Ejecuto la query
                                $xParams = ['DataCheck' => '', 'query' => $query];
                                $this->Base_insert($xParams);
                            }
                            break;
                    }
                }
            }
        }

        /******************************/
        // Devuelvo siempre el resultado
        return $Response;

    }

}
