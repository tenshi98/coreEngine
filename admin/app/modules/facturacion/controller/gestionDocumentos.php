<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class gestionDocumentos extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataNumbers;
    private $DataDate;
    private $ServerServer;
    private $CommonData;
    private $WidgetsCommon;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
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
    /******************************************************************************/
    //Listar Todo
    public function listAll_1($f3){$this->listAll($f3, 1);}
    public function listAll_2($f3){$this->listAll($f3, 2);}
    //Listar Todo
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
    //Resumen
    public function Resumen_1($f3, $params){$this->Resumen($f3, $params, 1);}
    public function Resumen_2($f3, $params){$this->Resumen($f3, $params, 2);}
    //Resumen-Update
    public function ResumenUpdate_1($f3, $params){$this->ResumenUpdate($f3, $params, 1);}
    public function ResumenUpdate_2($f3, $params){$this->ResumenUpdate($f3, $params, 2);}

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //Listar Todo
    public function listAll($f3, $idTipo){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se verifica movimiento
        switch ($idTipo) {
            case 1: $tsrxName = 'gestionDocumentosCompras';  $TipoMov = 'Compras';    break;//Compras
            case 2: $tsrxName = 'gestionDocumentosVentas';   $TipoMov = 'Ventas';     break;//Ventas
        }

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => '
                facturacion_listado.idFacturacion,
                facturacion_listado.N_Doc,
                facturacion_listado.Creacion_fecha,
                facturacion_listado.ValorTotal,
                facturacion_listado.MontoPagado,

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
            'where'   => 'facturacion_listado.idTipo = "'.$idTipo.'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'facturacion_listado.Creacion_fecha DESC, facturacion_listado.N_Doc DESC, facturacion_listado.idFacturacion DESC',
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
            'data'    => 'idEntidad AS ID,CONCAT((CASE WHEN ( Nombre = "" OR Nombre IS NULL ) THEN RazonSocial ELSE CONCAT(Nombre,IFNULL( CONCAT( " ", ApellidoPat ), "" )) END ),CASE WHEN ( Nick = "" OR Nick IS NULL ) THEN "" ELSE CONCAT( " (", Nick, ")" ) END ) AS Nombre ',
            'table'   => 'entidades_listado',
            'join'    => '',
            'where'   => 'idEstado=1 AND idTipo='.$idTipo,
            'group'   => '',
            'having'  => '',
            'order'   => 'ApellidoPat ASC,Nombre ASC,RazonSocial ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams      = ['query' => $query];
        $arrEntidades = $this->Base_GetList($xParams);

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
        //Se genera la query
        $query = [
            'data'    => 'idServicio AS ID,Nombre',
            'table'   => 'servicios_listado',
            'join'    => '',
            'where'   => 'idEstado=1',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams      = ['query' => $query];
        $arrServicios = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idFacturacion, N_Doc, Creacion_fecha, idEntidad',
            'table'   => 'facturacion_listado',
            'join'    => '',
            'where'   => 'idDocumentos=3 AND idEstadoPago=1',
            'group'   => '',
            'having'  => '',
            'order'   => 'idFacturacion ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams  = ['query' => $query];
        $arrGuias = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idEstadoPago AS ID,Nombre',
            'table'   => 'core_estados_pago',
            'join'    => '',
            'where'   => 'idEstadoPago!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams       = ['query' => $query];
        $arrEstadoPago = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($arrList)){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => $TipoMov,
                'PageDescription' => $TipoMov,
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => $TipoMov,
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$tsrxName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_Codification'    => $this->Codification,
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                'Fnc_ServerServer'    => $this->ServerServer,
                'Fnc_CommonData'      => $this->CommonData,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList,
                'arrBodegas'      => $arrBodegas,
                'arrProductos'    => $arrProductos,
                'arrEntidades'    => $arrEntidades,
                'arrDocumentos'   => $arrDocumentos,
                'arrServicios'    => $arrServicios,
                'arrGuias'        => $arrGuias,
                'arrEstadoPago'   => $arrEstadoPago,
                'idTipo'          => $idTipo,

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
    public function UpdateList($f3, $idTipo){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se verifica movimiento
        switch ($idTipo) {
            case 1: $tsrxName = 'gestionDocumentosCompras';  $TipoMov = 'Compras';    break;//Compras
            case 2: $tsrxName = 'gestionDocumentosVentas';   $TipoMov = 'Ventas';     break;//Ventas
        }

        /*******************************************************************/
        //Variables
        $WhereData_int     = 'idDocumentos,idEntidad,idEstadoPago,idFacturacion';  //Datos búsqueda exacta
        $WhereData_string  = 'N_Doc';                                              //Datos búsqueda relativa
        $WhereData_between = 'Creacion_fecha-F_Inicio-F_Termino';                  //Datos búsqueda Between
        $whereInt          = '';                                                   //se crea cadena
        /******************************************/
        //agrego variable busqueda
        $whereInt = $this->searchWhere($whereInt, $WhereData_int, 'facturacion_listado', 1);
        $whereInt = $this->searchWhere($whereInt, $WhereData_string, 'facturacion_listado', 2);
        $whereInt = $this->searchWhere($whereInt, $WhereData_between, 'facturacion_listado', 3);
        //Verifico si esta vacio
        $whereInt2 = $whereInt ? $whereInt . ' AND facturacion_listado.idTipo = "'.$idTipo.'"' : 'facturacion_listado.idTipo = "'.$idTipo.'"';

        /******************************/
        //Se genera la query
        $query = [
            'data'    => '
                facturacion_listado.idFacturacion,
                facturacion_listado.N_Doc,
                facturacion_listado.Creacion_fecha,
                facturacion_listado.ValorTotal,
                facturacion_listado.MontoPagado,

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
            'where'   => $whereInt2,
            'group'   => '',
            'having'  => '',
            'order'   => 'facturacion_listado.Creacion_fecha DESC, facturacion_listado.N_Doc DESC, facturacion_listado.idFacturacion DESC',
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
                'TableTitle'      => $TipoMov,
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$tsrxName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'     => $this->Codification,
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'arrList'  => $arrList,
                'idTipo'   => $idTipo,
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
    public function View($f3, $params, $idTipo){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se verifica movimiento
        switch ($idTipo) {
            case 0: $tsrxName = 'informeDocumentos';break;         //informe Documentos
            case 1: $tsrxName = 'gestionDocumentosCompras';break;  //Compras
            case 2: $tsrxName = 'gestionDocumentosVentas';break;   //Ventas
        }

        /******************************************/
        //Se genera la query
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
            'where'   => 'facturacion_listado.idFacturacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
            'data'    => 'Item,Number,ValorTotal',
            'table'   => 'facturacion_listado_items',
            'join'    => '',
            'where'   => 'idFacturacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams  = ['query' => $query];
        $arrItems = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
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
            'where'   => 'facturacion_listado_productos.idFacturacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'facturacion_listado_productos.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams      = ['query' => $query];
        $arrProductos = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => '
                servicios_listado.Nombre AS ServicioNombre,
                facturacion_listado_servicios.Number AS ServicioCantidad,
                facturacion_listado_servicios.ValorTotal AS ServicioValor',
            'table'   => 'facturacion_listado_servicios',
            'join'    => 'LEFT JOIN servicios_listado  ON servicios_listado.idServicio  = facturacion_listado_servicios.idServicio',
            'where'   => 'facturacion_listado_servicios.idFacturacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'facturacion_listado_servicios.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams      = ['query' => $query];
        $arrServicios = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
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
            'where'   => 'facturacion_listado_guias.idFacturacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'facturacion_listado_guias.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams  = ['query' => $query];
        $arrGuias = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
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
            'where'   => 'facturacion_listado_pagos.idFacturacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'facturacion_listado_pagos.idPago ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams  = ['query' => $query];
        $arrPagos = $this->Base_GetList($xParams);

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
                'Fnc_Codification'     => $this->Codification,
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData,
                'arrItems'         => $arrItems,
                'arrProductos'     => $arrProductos,
                'arrServicios'     => $arrServicios,
                'arrGuias'         => $arrGuias,
                'arrPagos'         => $arrPagos,
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
    //Print
    public function Print($f3, $params, $idTipo, $Imprimir){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se verifica movimiento
        switch ($idTipo) {
            case 0: $tsrxName = 'informeDocumentos';break;         //informe Documentos
            case 1: $tsrxName = 'gestionDocumentosCompras';break;  //Compras
            case 2: $tsrxName = 'gestionDocumentosVentas';break;   //Ventas
        }

        /******************************************/
        //Se genera la query
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
            'where'   => 'facturacion_listado.idFacturacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /******************************************/
        //Se genera la query
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
            'where'   => 'core_sistemas.idSistema = "1"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams    = ['query' => $query];
        $rowSistema = $this->Base_GetByID($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'Item,Number,ValorTotal',
            'table'   => 'facturacion_listado_items',
            'join'    => '',
            'where'   => 'idFacturacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams  = ['query' => $query];
        $arrItems = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
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
            'where'   => 'facturacion_listado_productos.idFacturacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'facturacion_listado_productos.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams      = ['query' => $query];
        $arrProductos = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => '
                servicios_listado.Nombre AS ServicioNombre,
                facturacion_listado_servicios.Number AS ServicioCantidad,
                facturacion_listado_servicios.ValorTotal AS ServicioValor',
            'table'   => 'facturacion_listado_servicios',
            'join'    => 'LEFT JOIN servicios_listado  ON servicios_listado.idServicio  = facturacion_listado_servicios.idServicio',
            'where'   => 'facturacion_listado_servicios.idFacturacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'facturacion_listado_servicios.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams      = ['query' => $query];
        $arrServicios = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
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
            'where'   => 'facturacion_listado_guias.idFacturacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'facturacion_listado_guias.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams  = ['query' => $query];
        $arrGuias = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
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
            'where'   => 'facturacion_listado_pagos.idFacturacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'facturacion_listado_pagos.idPago ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams  = ['query' => $query];
        $arrPagos = $this->Base_GetList($xParams);

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
                'Fnc_DataNumbers'      => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData,
                'rowSistema'       => $rowSistema,
                'arrItems'         => $arrItems,
                'arrProductos'     => $arrProductos,
                'arrServicios'     => $arrServicios,
                'arrGuias'         => $arrGuias,
                'arrPagos'         => $arrPagos,
                'Imprimir'         => $Imprimir,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 4, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Print.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //Resumen
    public function Resumen($f3, $params, $idTipo){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se verifica movimiento
        switch ($idTipo) {
            case 1: $tsrxName = 'gestionDocumentosCompras'; break; //Compras
            case 2: $tsrxName = 'gestionDocumentosVentas';break;   //Ventas
        }

        /******************************************/
        //Se genera la query
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

                core_facturacion_tipo.Nombre AS TipoFacturacion,
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
            'where'   => 'facturacion_listado.idFacturacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
            'data'    => 'idEntidad AS ID,CONCAT((CASE WHEN ( Nombre = "" OR Nombre IS NULL ) THEN RazonSocial ELSE CONCAT(Nombre,IFNULL( CONCAT( " ", ApellidoPat ), "" )) END ),CASE WHEN ( Nick = "" OR Nick IS NULL ) THEN "" ELSE CONCAT( " (", Nick, ")" ) END ) AS Nombre ',
            'table'   => 'entidades_listado',
            'join'    => '',
            'where'   => 'idEstado=1 AND idTipo='.$idTipo,
            'group'   => '',
            'having'  => '',
            'order'   => 'ApellidoPat ASC,Nombre ASC,RazonSocial ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams      = ['query' => $query];
        $arrEntidades = $this->Base_GetList($xParams);

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
        if ($rowData!==false) {
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'        => 'Resumen Facturación',
                'PageDescription'  => 'Resumen Facturación.',
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
                'arrEntidades'    => $arrEntidades,
                'arrDocumentos'   => $arrDocumentos,
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
    public function ResumenUpdate($f3, $params, $idTipo){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se verifica movimiento
        switch ($idTipo) {
            case 1: $tsrxName = 'gestionDocumentosCompras'; break; //Compras
            case 2: $tsrxName = 'gestionDocumentosVentas';break;   //Ventas
        }

        /******************************************/
        //Se genera la query
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
            'where'   => 'facturacion_listado.idFacturacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
                'Fnc_DataNumbers'      => $this->DataNumbers,
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
    public function Insert($f3){

        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');

        /*******************************************************************/
        //variables
        $ndata_1 = isset($_POST['Item_Item']) ? count($_POST['Item_Item']) : 0;
        $ndata_2 = isset($_POST['Producto_idProducto']) ? count($_POST['Producto_idProducto']) : 0;
        $ndata_3 = isset($_POST['Servicio_idServicio']) ? count($_POST['Servicio_idServicio']) : 0;
        $ndata_4 = isset($_POST['idFacturacionRel']) ? count($_POST['idFacturacionRel']) : 0;

        //var para validaciones
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

        //generacion de errores
        if($DataVal['Count']==0) {
            echo Response::sendData(500, $DataVal['Msg']);
        }else{

            /******************************************/
            //Se llama al movimiento de materiales
            $Response = $this->createDoc($_POST, $UserData);

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
            //Verifico si existe
            if(isset($_POST['Creacion_fecha'])&&$_POST['Creacion_fecha']!=''){
                $_POST['Creacion_Semana']  = $this->DataDate->fecha2NSemana($_POST['Creacion_fecha']);
                $_POST['Creacion_mes']     = $this->DataDate->fecha2NMes($_POST['Creacion_fecha']);
                $_POST['Creacion_ano']     = $this->DataDate->fecha2Ano($_POST['Creacion_fecha']);
            }

            /******************************/
            //Se genera la query
            $query = [
                'data'      => 'idFacturacion,idUsuario,idTipo,idEntidad,idBodegasIngreso,idBodegasEgreso,fecha_auto,idDocumentos,N_Doc,Creacion_fecha,Creacion_Semana,Creacion_mes,Creacion_ano,Creacion_hora,Observaciones,ValorNeto,IVA,ValorTotal,TotalItems,TotalProductos,TotalServicios,TotalGuias,idEstadoPago,MontoPagado',
                'required'  => 'idUsuario,idTipo,idEntidad,fecha_auto,idDocumentos,Creacion_fecha,idEstadoPago',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'facturacion_listado',
                'where'     => 'idFacturacion',
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
                'table'       => 'facturacion_listado',
                'where'       => 'idFacturacion',
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
                $arrTableDel[] = ['files' => '', 'table' => 'facturacion_listado_guias'];
                $arrTableDel[] = ['files' => '', 'table' => 'facturacion_listado_items'];
                $arrTableDel[] = ['files' => '', 'table' => 'facturacion_listado_productos'];
                $arrTableDel[] = ['files' => '', 'table' => 'facturacion_listado_servicios'];
                $arrTableDel[] = ['files' => '', 'table' => 'facturacion_listado_pagos'];

                /************************************************/
                //Verifico si existe
                if($arrTableDel){
                    //recorro
                    foreach ($arrTableDel as $tblDel) {
                        //Se genera la query
                        $query = ['files' => $tblDel['files'], 'table' => $tblDel['table'], 'where' => 'idFacturacion', 'SubCarpeta' => '', 'Post' => $dataDelete];
                        //Ejecuto la query
                        $xParams     = ['query' => $query];
                        $ResponseDel = $this->Base_delete($xParams);
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
    public function createDoc($PostData, $UserData){

        /*******************************************************************/
        //variables
        $ndata_1 = isset($PostData['Item_Item']) ? count($PostData['Item_Item']) : 0;
        $ndata_2 = isset($PostData['Producto_idProducto']) ? count($PostData['Producto_idProducto']) : 0;
        $ndata_3 = isset($PostData['Servicio_idServicio']) ? count($PostData['Servicio_idServicio']) : 0;
        $ndata_4 = isset($PostData['idFacturacionRel']) ? count($PostData['idFacturacionRel']) : 0;

        /******************************/
        //Se genera el chequeo
        $DataCheck = $this->dataCheck($PostData);

        /******************************/
        //Variables
        $x_ValorTotal     = 0;
        $x_TotalItems     = 0;
        $x_TotalProductos = 0;
        $x_TotalServicios = 0;
        /*******************************************************/
        //Items
        if(isset($ndata_1)&&$ndata_1!=0){
            //recorro los items
            for($j1 = 0; $j1 < $ndata_1; $j1++){
                $x_ValorTotal = $x_ValorTotal + $PostData['Item_ValorTotal'][$j1];
                $x_TotalItems = $x_TotalItems + $PostData['Item_ValorTotal'][$j1];
            }
        }
        //Productos
        if(isset($ndata_2)&&$ndata_2!=0){
            //recorro los items
            for($j1 = 0; $j1 < $ndata_2; $j1++){
                $x_ValorTotal     = $x_ValorTotal + $PostData['Producto_ValorTotal'][$j1];
                $x_TotalProductos = $x_TotalProductos + $PostData['Producto_ValorTotal'][$j1];
            }
        }
        //Servicios
        if(isset($ndata_3)&&$ndata_3!=0){
            //recorro los items
            for($j1 = 0; $j1 < $ndata_3; $j1++){
                $x_ValorTotal     = $x_ValorTotal + $PostData['Servicio_ValorTotal'][$j1];
                $x_TotalServicios = $x_TotalServicios + $PostData['Servicio_ValorTotal'][$j1];
            }
        }
        //Guias de despacho
        if(isset($ndata_4)&&$ndata_4!=0){
            /******************************************/
            //Se arma cadena
            $chainx_where = 'idFacturacion IN (999999999';
            //recorro los items
            for($j1 = 0; $j1 < $ndata_4; $j1++){
                $chainx_where .= ','.$PostData['idFacturacionRel'][$j1];
            }
            $chainx_where .= ')';

            /******************************************/
            //Se genera la query
            $query = [
                'data'    => 'SUM(ValorTotal) AS Total',
                'table'   => 'facturacion_listado',
                'join'    => '',
                'where'   => $chainx_where,
                'group'   => '',
                'having'  => '',
                'order'   => ''
            ];
            //Ejecuto la query
            $xParams = ['query' => $query];
            $rowData = $this->Base_GetByID($xParams);

            /******************************************/
            //Se suman los totales de las guias
            $x_ValorTotal = $x_ValorTotal + $rowData['Total'];
        }

        /*******************************************************/
        //Se generan datos
        $PostData['ValorNeto']       = ($x_ValorTotal/1.19);
        $PostData['IVA']             = $x_ValorTotal - ($x_ValorTotal/1.19);
        $PostData['ValorTotal']      = $x_ValorTotal;
        $PostData['TotalItems']      = $x_TotalItems;
        $PostData['TotalProductos']  = $x_TotalProductos;
        $PostData['TotalServicios']  = $x_TotalServicios;
        //Verifico si existe
        if(isset($PostData['Creacion_fecha'])&&$PostData['Creacion_fecha']!=''){
            $PostData['Creacion_Semana']  = $this->DataDate->fecha2NSemana($PostData['Creacion_fecha']);
            $PostData['Creacion_mes']     = $this->DataDate->fecha2NMes($PostData['Creacion_fecha']);
            $PostData['Creacion_ano']     = $this->DataDate->fecha2Ano($PostData['Creacion_fecha']);
        }

        /******************************/
        //Se genera la query
        $query = [
            'data'      => 'idUsuario,idTipo,idEntidad,idBodegasIngreso,idBodegasEgreso,fecha_auto,idDocumentos,N_Doc,Creacion_fecha,Creacion_Semana,Creacion_mes,Creacion_ano,Creacion_hora,Observaciones,ValorNeto,IVA,ValorTotal,TotalItems,TotalProductos,TotalServicios,idEstadoPago,MontoPagado',
            'required'  => 'idUsuario,idTipo,idEntidad,fecha_auto,idDocumentos,Creacion_fecha,idEstadoPago',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'facturacion_listado',
            'Post'      => $PostData
        ];

        //Ejecuto la query
        //Ejecuto la query
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        $Response = $this->Base_insert($xParams);

        /******************************/
        // Se asume que $Response contendrá un array de errores/datos, un ID numérico o algún otro valor.
        if (is_numeric($Response)) {
            /*******************************************************/
            //Items
            if(isset($ndata_1)&&$ndata_1!=0){
                //recorro los items
                for($j1 = 0; $j1 < $ndata_1; $j1++){
                    /******************************/
                    //Se agrega respuesta
                    $arrTareas = [
                        'idFacturacion' => $Response,
                        'Item'          => $PostData['Item_Item'][$j1],
                        'Number'        => $PostData['Item_Number'][$j1],
                        'ValorTotal'    => $PostData['Item_ValorTotal'][$j1],
                    ];
                    /******************************/
                    //Se genera la query
                    $query = [
                        'data'      => 'idFacturacion,Item,Number,ValorTotal',
                        'required'  => 'idFacturacion,Item,Number,ValorTotal',
                        'unique'    => '',
                        'encode'    => '',
                        'table'     => 'facturacion_listado_items',
                        'Post'      => $arrTareas
                    ];
                    //Ejecuto la query
                    $xParams       = ['DataCheck' => '', 'query' => $query];
                    $ResponseItems = $this->Base_insert($xParams);
                }
            }

            /*******************************************************/
            //Productos
            if(isset($ndata_2)&&$ndata_2!=0){
                /**********************************************************************/
                // Determinar la bodega según el tipo
                $idBodegas = 0;
                if (isset($PostData['idTipo'])) {
                    if ($PostData['idTipo'] == 1 && isset($PostData['idBodegasIngreso'])) {
                        $idBodegas = $PostData['idBodegasIngreso'];
                    } elseif ($PostData['idTipo'] == 2 && isset($PostData['idBodegasEgreso'])) {
                        $idBodegas = $PostData['idBodegasEgreso'];
                    }
                }

                /******************************/
                //recorro los items
                for($j1 = 0; $j1 < $ndata_2; $j1++){
                    /******************************/
                    //Se agrega respuesta
                    $arrTareas = [
                        'idFacturacion'   => $Response,
                        'idEstadoIngreso' => $PostData['idTipo'],
                        'idBodegas'       => $idBodegas,
                        'idProducto'      => $PostData['Producto_idProducto'][$j1],
                        'Number'          => $PostData['Producto_Number'][$j1],
                        'ValorTotal'      => $PostData['Producto_ValorTotal'][$j1],
                    ];
                    /******************************/
                    //Se genera la query
                    $query = [
                        'data'      => 'idFacturacion,idEstadoIngreso,idBodegas,idProducto,Number,ValorTotal',
                        'required'  => 'idFacturacion,idEstadoIngreso,idBodegas,idProducto,Number,ValorTotal',
                        'unique'    => '',
                        'encode'    => '',
                        'table'     => 'facturacion_listado_productos',
                        'Post'      => $arrTareas
                    ];
                    //Ejecuto la query
                    $xParams           = ['DataCheck' => '', 'query' => $query];
                    $ResponseProductos = $this->Base_insert($xParams);
                }
                /**********************************************************************/
                //Movimiento de bodegas
                //permite la interaccion con la bodega, para generar documentos de ingreso o egreso
                if($UserData["gestionDocumentosUsoBodega"]==2){
                    //Variable
                    $PostMovProd = array();
                    //Se generan los datos
                    $PostMovProd['idEstadoIngreso']  = $PostData['idTipo'];
                    $PostMovProd['idBodegasIngreso'] = (isset($PostData['idBodegasIngreso']) && $PostData['idBodegasIngreso'] !== '' ? $PostData['idBodegasIngreso'] : '');
                    $PostMovProd['idBodegasEgreso']  = (isset($PostData['idBodegasEgreso']) && $PostData['idBodegasEgreso'] !== '' ? $PostData['idBodegasEgreso'] : '');
                    $PostMovProd['Creacion_fecha']   = $PostData['Creacion_fecha'];
                    $PostMovProd['Creacion_hora']    = $PostData['Creacion_hora'];
                    $PostMovProd['Observaciones']    = 'Movimiento generado desde una facturacion';
                    $PostMovProd['fecha_auto']       = $PostData['fecha_auto'];
                    $PostMovProd['idUsuario']        = $PostData['idUsuario'];
                    $PostMovProd['idFacturacion']    = $Response;
                    //productos
                    $PostMovProd['idProducto']       = $PostData['Producto_idProducto'];
                    $PostMovProd['Number']           = $PostData['Producto_Number'];

                    /*******************************************************/
                    //Se instancia
                    $bodegasMovimiento = new bodegasMovimiento();
                    $DepData1  = $bodegasMovimiento->createMov($PostMovProd);

                }
            }

            /*******************************************************/
            //Servicios
            if(isset($ndata_3)&&$ndata_3!=0){
                //recorro los items
                for($j1 = 0; $j1 < $ndata_3; $j1++){
                    /******************************/
                    //Se agrega respuesta
                    $arrTareas = [
                        'idFacturacion' => $Response,
                        'idServicio'    => $PostData['Servicio_idServicio'][$j1],
                        'Number'        => $PostData['Servicio_Number'][$j1],
                        'ValorTotal'    => $PostData['Servicio_ValorTotal'][$j1],
                    ];
                    /******************************/
                    //Se genera la query
                    $query = [
                        'data'      => 'idFacturacion,idServicio,Number,ValorTotal',
                        'required'  => 'idFacturacion,idServicio,Number,ValorTotal',
                        'unique'    => '',
                        'encode'    => '',
                        'table'     => 'facturacion_listado_servicios',
                        'Post'      => $arrTareas
                    ];
                    //Ejecuto la query
                    $xParams           = ['DataCheck' => '', 'query' => $query];
                    $ResponseServicios = $this->Base_insert($xParams);
                }
            }

            /*******************************************************/
            //Guias
            if(isset($ndata_4)&&$ndata_4!=0){
                //recorro los items
                for($j1 = 0; $j1 < $ndata_4; $j1++){
                    /******************************/
                    //Se agrega respuesta
                    $arrTareas = [
                        'idFacturacion'    => $Response,
                        'idFacturacionRel' => $PostData['idFacturacionRel'][$j1],
                    ];
                    /******************************/
                    //Se genera la query
                    $query = [
                        'data'      => 'idFacturacion,idFacturacionRel',
                        'required'  => 'idFacturacion,idFacturacionRel',
                        'unique'    => '',
                        'encode'    => '',
                        'table'     => 'facturacion_listado_guias',
                        'Post'      => $arrTareas
                    ];
                    //Ejecuto la query
                    $xParams       = ['DataCheck' => '', 'query' => $query];
                    $ResponseGuias = $this->Base_insert($xParams);
                    /******************************/
                    //Verifico si hay respuesta para actualizar datos
                    if(is_array($ResponseGuias)){
                    //si no hay resultados
                    }else{
                        //si es la respuesta esperada
                        if (is_numeric($ResponseGuias)===true) {
                            /******************************/
                            //Se agrega respuesta
                            $arrTareas = [
                                'idFacturacion' => $PostData['idFacturacionRel'][$j1],
                                'idEstadoPago'  => 2,
                            ];
                            /******************************/
                            //Se genera la query
                            $query = [
                                'data'      => 'idFacturacion,idEstadoPago',
                                'required'  => 'idFacturacion,idEstadoPago',
                                'unique'    => '',
                                'encode'    => '',
                                'table'     => 'facturacion_listado',
                                'where'     => 'idFacturacion',
                                'Post'      => $arrTareas
                            ];
                            //Ejecuto la query
                            $xParams             = ['DataCheck' => '', 'query' => $query];
                            $ResponseGuiasUpdate = $this->Base_update($xParams);

                        }
                    }
                }
            }
        }

        /******************************/
        // Devuelvo siempre el resultado
        return $Response;

    }

    /******************************************************************************/
    //Se actualizan los montos
    public function updateFact($Tipo, $FacturacionID){
        /********************************************************/
        //Se cambia la query dependiendo de el tipo
        switch ($Tipo) {
            /******************************/
            //Items
            case 1:
                $Data = '
                cotizacion_listado.idCotizacion,
                cotizacion_listado.TotalProductos,
                cotizacion_listado.TotalServicios,
                cotizacion_listado.TotalGuias,
                (SELECT SUM(ValorTotal) FROM facturacion_listado_items     WHERE idFacturacion='.$FacturacionID.') AS TotalItems';
                break;
            /******************************/
            //Productos
            case 2:
                $Data = '
                cotizacion_listado.idCotizacion,
                cotizacion_listado.TotalItems,
                cotizacion_listado.TotalServicios,
                cotizacion_listado.TotalGuias,
                (SELECT SUM(ValorTotal) FROM facturacion_listado_productos WHERE idFacturacion='.$FacturacionID.') AS TotalProductos';
                break;
            /******************************/
            //Servicios
            case 3:
                $Data = '
                cotizacion_listado.idCotizacion,
                cotizacion_listado.TotalItems,
                cotizacion_listado.TotalProductos,
                cotizacion_listado.TotalGuias,
                (SELECT SUM(ValorTotal) FROM facturacion_listado_servicios WHERE idFacturacion='.$FacturacionID.') AS TotalServicios';
                break;
            /******************************/
            //Guias
            case 4:
                $Data = '
                cotizacion_listado.idCotizacion,
                cotizacion_listado.TotalItems,
                cotizacion_listado.TotalProductos,
                cotizacion_listado.TotalServicios,
                (SELECT SUM(facturacion_listado.ValorTotal) FROM facturacion_listado_guias LEFT JOIN facturacion_listado ON facturacion_listado.idFacturacion = facturacion_listado_guias.idFacturacionRel WHERE facturacion_listado_guias.idFacturacion='.$FacturacionID.') AS TotalGuias';
                break;
        }
        /******************************************/
        //Se genera la query
        $query = [
            'data'    => $Data,
            'table'   => 'facturacion_listado',
            'join'    => '',
            'where'   => 'idFacturacion = "'.$FacturacionID.'"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /******************************/
        //Calculo
        $x_ValorTotal = $rowData['TotalItems'] + $rowData['TotalProductos'] + $rowData['TotalServicios'] + $rowData['TotalGuias'];
        //Se agrega respuesta
        $arrTareas = [
            'idFacturacion'   => $FacturacionID,
            'ValorNeto'       => ($x_ValorTotal/1.19),
            'IVA'             => $x_ValorTotal - ($x_ValorTotal/1.19),
            'ValorTotal'      => $x_ValorTotal,
            'TotalItems'      => $rowData['TotalItems'],
            'TotalProductos'  => $rowData['TotalProductos'],
            'TotalServicios'  => $rowData['TotalServicios'],
            'TotalGuias'      => $rowData['TotalGuias'],
        ];
        /******************************/
        //Se genera la query
        $query = [
            'data'      => 'idFacturacion,ValorNeto,IVA,ValorTotal,TotalItems,TotalProductos,TotalServicios,TotalGuias',
            'required'  => 'idFacturacion,ValorNeto,IVA,ValorTotal,TotalItems,TotalProductos,TotalServicios,TotalGuias',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'facturacion_listado',
            'where'     => 'idFacturacion',
            'Post'      => $arrTareas
        ];
        //Ejecuto la query
        $xParams       = ['DataCheck' => '', 'query' => $query];
        $ResponseTarea = $this->Base_update($xParams);
    }

}