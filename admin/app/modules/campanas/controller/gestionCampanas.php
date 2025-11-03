<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class gestionCampanas extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataNumbers;
    private $DataDate;
    private $ServerServer;
    private $WidgetsCommon;
    private $CommonData;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'gestionCampanas';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->DataNumbers    = new FunctionsDataNumbers();
		$this->DataDate       = new FunctionsDataDate();
		$this->ServerServer   = new FunctionsServerServer();
		$this->WidgetsCommon  = new UIWidgetsCommon();
		$this->CommonData     = new FunctionsCommonData();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  RUTAS                                     */
    /******************************************************************************/
    /******************************************************************************/
    //imprimir
    public function Print($f3, $params){$this->Printer($f3, $params, 1);}
    //ver documento
    public function noPrint($f3, $params){$this->Printer($f3, $params, 0);}

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //Listar Todo
    public function listAll($f3){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => '
                campanas_listado.idCampana,
                campanas_listado.fecha_auto,
                campanas_listado.Fecha,
                campanas_listado.Nombre,
                campanas_listado.Costos,
                campanas_listado.Beneficios,
                campanas_listado.Perdidas,
                campanas_listado.Margen,
                usuarios_listado.Nombre AS Creador,
                core_estados_apertura.Nombre AS EstadoNombre,
                core_estados_apertura.Color AS EstadoColor',
            'table'   => 'campanas_listado',
            'join'    => '
                LEFT JOIN usuarios_listado        ON usuarios_listado.idUsuario               = campanas_listado.idUsuario
                LEFT JOIN core_estados_apertura   ON core_estados_apertura.idEstadoApertura   = campanas_listado.idEstado',
            'where'   => '',
            'group'   => '',
            'having'  => '',
            'order'   => 'campanas_listado.idEstado ASC, campanas_listado.Fecha DESC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $arrList = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idUsuario AS ID,Nombre',
            'table'   => 'usuarios_listado',
            'join'    => '',
            'where'   => 'idTipoUsuario!=1',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams     = ['query' => $query];
        $arrUsuarios = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idEstadoApertura AS ID,Nombre',
            'table'   => 'core_estados_apertura',
            'join'    => '',
            'where'   => 'idEstadoApertura!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams    = ['query' => $query];
        $arrEstados = $this->Base_GetList($xParams);

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
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($arrList)){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Listado Campañas',
                'PageDescription' => 'Listado Campañas',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => 'Listado Campañas',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_Codification'    => $this->Codification,
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                'Fnc_ServerServer'    => $this->ServerServer,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList,
                'arrUsuarios'     => $arrUsuarios,
                'arrEstados'      => $arrEstados,
                'arrBodegas'      => $arrBodegas,
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
    public function UpdateList($f3){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /*******************************************************************/
        //Variables
        $WhereData_int     = 'idUsuario,idEstado,idBodegas';             //Datos búsqueda exacta
        $WhereData_string  = 'Nombre';                                   //Datos búsqueda relativa
        $WhereData_between = 'Fecha-Fecha_Inicio-Fecha_Termino';         //Datos búsqueda Between
        $whereInt          = '';                                         //se crea cadena
        /******************************************/
        //agrego variable busqueda
        $whereInt = $this->searchWhere($whereInt, $WhereData_int, 'campanas_listado', 1);
        $whereInt = $this->searchWhere($whereInt, $WhereData_string, 'campanas_listado', 2);
        $whereInt = $this->searchWhere($whereInt, $WhereData_between, 'campanas_listado', 3);
        //Verifico si esta vacio
        $whereInt2 = $whereInt ? $whereInt . ' AND campanas_listado.idCampana!=0' : 'campanas_listado.idCampana!=0';

        /******************************/
        //Se genera la query
        $query = [
            'data'    => '
                campanas_listado.idCampana,
                campanas_listado.fecha_auto,
                campanas_listado.Fecha,
                campanas_listado.Nombre,
                campanas_listado.Costos,
                campanas_listado.Beneficios,
                campanas_listado.Perdidas,
                campanas_listado.Margen,
                usuarios_listado.Nombre AS Creador,
                core_estados_apertura.Nombre AS EstadoNombre,
                core_estados_apertura.Color AS EstadoColor',
            'table'   => 'campanas_listado',
            'join'    => '
                LEFT JOIN usuarios_listado        ON usuarios_listado.idUsuario               = campanas_listado.idUsuario
                LEFT JOIN core_estados_apertura   ON core_estados_apertura.idEstadoApertura   = campanas_listado.idEstado',
            'where'   => $whereInt2,
            'group'   => '',
            'having'  => '',
            'order'   => 'campanas_listado.idEstado ASC, campanas_listado.Fecha DESC',
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
                'TableTitle'      => 'Listado Campañas',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'     => $this->Codification,
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'arrList'     => $arrList,
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
    public function View($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => '
                campanas_listado.idCampana,
                campanas_listado.fecha_auto,
                campanas_listado.Fecha,
                campanas_listado.Nombre,
                campanas_listado.Observaciones,
                campanas_listado.Costos,
                campanas_listado.Beneficios,
                campanas_listado.Perdidas,
                campanas_listado.Margen,
                usuarios_listado.Nombre AS Creador,
                core_estados_apertura.Nombre AS EstadoNombre,
                core_estados_apertura.Color AS EstadoColor,
                bodegas_listado.Nombre AS Bodega',
            'table'   => 'campanas_listado',
            'join'    => '
                LEFT JOIN usuarios_listado        ON usuarios_listado.idUsuario               = campanas_listado.idUsuario
                LEFT JOIN core_estados_apertura   ON core_estados_apertura.idEstadoApertura   = campanas_listado.idEstado
                LEFT JOIN bodegas_listado         ON bodegas_listado.idBodegas                = campanas_listado.idBodegas',
            'where'   => 'campanas_listado.idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
                campanas_listado_costos.Fecha,
                campanas_listado_costos.Item,
                campanas_listado_costos.Costos,
                campanas_listado_costos.idFacturacion,
                core_documentos_mercantiles.Nombre AS DocumentoNombre,
                facturacion_listado.N_Doc AS DocumentoN_Doc',
            'table'   => 'campanas_listado_costos',
            'join'    => '
                LEFT JOIN facturacion_listado           ON facturacion_listado.idFacturacion          = campanas_listado_costos.idFacturacion
                LEFT JOIN core_documentos_mercantiles   ON core_documentos_mercantiles.idDocumentos   = facturacion_listado.idDocumentos',
            'where'   => 'campanas_listado_costos.idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'campanas_listado_costos.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrCostos = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => '
                campanas_listado_partidas.idExistencia,
                campanas_listado_partidas.Fecha,
                entidades_listado.Nombre AS EntidadNombre,
                entidades_listado.ApellidoPat AS EntidadApellido,
                entidades_listado.RazonSocial AS EntidadRazonSocial,
                entidades_listado.Nick AS EntidadNick,
                entidades_sectores.Nombre AS EntidadSector,
                entidades_listado.Direccion AS EntidadDireccion,
                core_estados_partida.Nombre AS EstadoPartida,
                core_documentos_mercantiles.Nombre AS DocumentoNombre,
                facturacion_listado.N_Doc AS DocumentoN_Doc,
                campanas_listado_partidas.idFacturacion',
            'table'   => 'campanas_listado_partidas',
            'join'    => '
                LEFT JOIN entidades_listado                     ON entidades_listado.idEntidad                       = campanas_listado_partidas.idEntidad
                LEFT JOIN entidades_sectores                    ON entidades_sectores.idSector                       = entidades_listado.idSector
                LEFT JOIN core_estados_partida                  ON core_estados_partida.idEstadoPartida              = campanas_listado_partidas.idEstadoPartida
                LEFT JOIN facturacion_listado                   ON facturacion_listado.idFacturacion                 = campanas_listado_partidas.idFacturacion
                LEFT JOIN core_documentos_mercantiles           ON core_documentos_mercantiles.idDocumentos          = facturacion_listado.idDocumentos',
            'where'   => 'campanas_listado_partidas.idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'campanas_listado_partidas.Fecha ASC, entidades_sectores.Nombre ASC, entidades_listado.Direccion ASC, campanas_listado_partidas.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams     = ['query' => $query];
        $arrPartidas = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => '
                campanas_listado_partidas_productos.idExistencia,
                campanas_listado_partidas_productos.Cantidad,
                campanas_listado_partidas_productos.Beneficios,
                productos_listado.Nombre AS Producto,
                core_unidades_medida.Nombre AS Unimed',
            'table'   => 'campanas_listado_partidas_productos',
            'join'    => '
                LEFT JOIN productos_listado     ON productos_listado.idProducto    = campanas_listado_partidas_productos.idProducto
                LEFT JOIN core_unidades_medida  ON core_unidades_medida.idUniMed   = productos_listado.idUniMed',
            'where'   => 'campanas_listado_partidas_productos.idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'campanas_listado_partidas_productos.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams         = ['query' => $query];
        $arrPartidasProd = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => '
                campanas_listado_perdidas.idExistencia,
                campanas_listado_perdidas.Fecha,
                campanas_listado_perdidas.Item,
                campanas_listado_perdidas.Cantidad,
                campanas_listado_perdidas.Perdidas,
                campanas_listado_perdidas.idMovimiento,
                productos_listado.Nombre AS Producto,
                core_unidades_medida.Nombre AS Unimed',
            'table'   => 'campanas_listado_perdidas',
            'join'    => '
                LEFT JOIN productos_listado      ON productos_listado.idProducto    = campanas_listado_perdidas.idProducto
                LEFT JOIN core_unidades_medida   ON core_unidades_medida.idUniMed   = productos_listado.idUniMed',
            'where'   => 'campanas_listado_perdidas.idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'campanas_listado_perdidas.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams     = ['query' => $query];
        $arrPerdidas = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => '
                COUNT(campanas_listado_partidas.idEstadoPartida) AS Cuenta,
                campanas_listado_partidas.idEstadoPartida,
                core_estados_partida.Nombre AS EstadoPartida',
            'table'   => 'campanas_listado_partidas',
            'join'    => 'LEFT JOIN core_estados_partida ON core_estados_partida.idEstadoPartida = campanas_listado_partidas.idEstadoPartida',
            'where'   => 'campanas_listado_partidas.idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => 'campanas_listado_partidas.idEstadoPartida',
            'having'  => '',
            'order'   => 'campanas_listado_partidas.idEstadoPartida ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams         = ['query' => $query];
        $arrEstadisticas = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => '
                campanas_listado_partidas.VentaFecha,
                campanas_listado_partidas_productos.idProducto,
                SUM(campanas_listado_partidas_productos.Cantidad) AS Total,
                SUM(campanas_listado_partidas_productos.Beneficios) AS Valor,
                productos_listado.Nombre AS Producto,
                core_unidades_medida.Nombre AS Unimed',
            'table'   => 'campanas_listado_partidas',
            'join'    => '
                LEFT JOIN campanas_listado_partidas_productos   ON campanas_listado_partidas_productos.idExistencia  = campanas_listado_partidas.idExistencia
                LEFT JOIN productos_listado                     ON productos_listado.idProducto                      = campanas_listado_partidas_productos.idProducto
                LEFT JOIN core_unidades_medida                  ON core_unidades_medida.idUniMed                     = productos_listado.idUniMed',
            'where'   => 'campanas_listado_partidas.idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'" AND campanas_listado_partidas.idEstadoPartida = 6',
            'group'   => 'campanas_listado_partidas.VentaFecha, campanas_listado_partidas_productos.idProducto',
            'having'  => '',
            'order'   => 'campanas_listado_partidas.VentaFecha ASC, campanas_listado_partidas_productos.idProducto ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams       = ['query' => $query];
        $arrDataVentas = $this->Base_GetList($xParams);

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
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'     => $this->Codification,
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                'Fnc_CommonData'       => $this->CommonData,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData,
                'arrCostos'       => $arrCostos,
                'arrPartidas'     => $arrPartidas,
                'arrPartidasProd' => $arrPartidasProd,
                'arrPerdidas'     => $arrPerdidas,
                'arrEstadisticas' => $arrEstadisticas,
                'arrDataVentas'   => $arrDataVentas,
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
    public function Printer($f3, $params, $Imprimir){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => '
                campanas_listado.fecha_auto,
                campanas_listado.Fecha,
                campanas_listado.Nombre,
                campanas_listado.Observaciones,
                campanas_listado.Costos,
                campanas_listado.Beneficios,
                campanas_listado.Perdidas,
                campanas_listado.Margen,
                usuarios_listado.Nombre AS Creador,
                core_estados_apertura.Nombre AS EstadoNombre,
                core_estados_apertura.Color AS EstadoColor,
                bodegas_listado.Nombre AS Bodega',
            'table'   => 'campanas_listado',
            'join'    => '
                LEFT JOIN usuarios_listado        ON usuarios_listado.idUsuario               = campanas_listado.idUsuario
                LEFT JOIN core_estados_apertura   ON core_estados_apertura.idEstadoApertura   = campanas_listado.idEstado
                LEFT JOIN bodegas_listado         ON bodegas_listado.idBodegas                = campanas_listado.idBodegas',
            'where'   => 'campanas_listado.idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
                campanas_listado_costos.Fecha,
                campanas_listado_costos.Item,
                campanas_listado_costos.Costos,
                campanas_listado_costos.idFacturacion,
                core_documentos_mercantiles.Nombre AS DocumentoNombre,
                facturacion_listado.N_Doc AS DocumentoN_Doc',
            'table'   => 'campanas_listado_costos',
            'join'    => '
                LEFT JOIN facturacion_listado           ON facturacion_listado.idFacturacion          = campanas_listado_costos.idFacturacion
                LEFT JOIN core_documentos_mercantiles   ON core_documentos_mercantiles.idDocumentos   = facturacion_listado.idDocumentos',
            'where'   => 'campanas_listado_costos.idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrCostos = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => '
                campanas_listado_partidas.idExistencia,
                campanas_listado_partidas.Fecha,
                entidades_listado.Nombre AS EntidadNombre,
                entidades_listado.ApellidoPat AS EntidadApellido,
                entidades_listado.RazonSocial AS EntidadRazonSocial,
                entidades_listado.Nick AS EntidadNick,
                entidades_sectores.Nombre AS EntidadSector,
                entidades_listado.Direccion AS EntidadDireccion,
                core_estados_partida.Nombre AS EstadoPartida,
                core_documentos_mercantiles.Nombre AS DocumentoNombre,
                facturacion_listado.N_Doc AS DocumentoN_Doc,
                campanas_listado_partidas.idFacturacion',
            'table'   => 'campanas_listado_partidas',
            'join'    => '
                LEFT JOIN entidades_listado                     ON entidades_listado.idEntidad                       = campanas_listado_partidas.idEntidad
                LEFT JOIN entidades_sectores                    ON entidades_sectores.idSector                       = entidades_listado.idSector
                LEFT JOIN core_estados_partida                  ON core_estados_partida.idEstadoPartida              = campanas_listado_partidas.idEstadoPartida
                LEFT JOIN facturacion_listado                   ON facturacion_listado.idFacturacion                 = campanas_listado_partidas.idFacturacion
                LEFT JOIN core_documentos_mercantiles           ON core_documentos_mercantiles.idDocumentos          = facturacion_listado.idDocumentos',
            'where'   => 'campanas_listado_partidas.idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'campanas_listado_partidas.Fecha ASC, entidades_sectores.Nombre ASC, entidades_listado.Direccion ASC, campanas_listado_partidas.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams     = ['query' => $query];
        $arrPartidas = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => '
                campanas_listado_partidas_productos.idExistencia,
                campanas_listado_partidas_productos.Cantidad,
                campanas_listado_partidas_productos.Beneficios,
                productos_listado.Nombre AS Producto,
                core_unidades_medida.Nombre AS Unimed',
            'table'   => 'campanas_listado_partidas_productos',
            'join'    => '
                LEFT JOIN productos_listado     ON productos_listado.idProducto    = campanas_listado_partidas_productos.idProducto
                LEFT JOIN core_unidades_medida  ON core_unidades_medida.idUniMed   = productos_listado.idUniMed',
            'where'   => 'campanas_listado_partidas_productos.idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'campanas_listado_partidas_productos.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams         = ['query' => $query];
        $arrPartidasProd = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => '
                campanas_listado_perdidas.idExistencia,
                campanas_listado_perdidas.Fecha,
                campanas_listado_perdidas.Item,
                campanas_listado_perdidas.Cantidad,
                campanas_listado_perdidas.Perdidas,
                campanas_listado_perdidas.idMovimiento,
                productos_listado.Nombre AS Producto,
                core_unidades_medida.Nombre AS Unimed',
            'table'   => 'campanas_listado_perdidas',
            'join'    => '
                LEFT JOIN productos_listado      ON productos_listado.idProducto    = campanas_listado_perdidas.idProducto
                LEFT JOIN core_unidades_medida   ON core_unidades_medida.idUniMed   = productos_listado.idUniMed',
            'where'   => 'campanas_listado_perdidas.idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'campanas_listado_perdidas.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams     = ['query' => $query];
        $arrPerdidas = $this->Base_GetList($xParams);

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
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                'Fnc_CommonData'       => $this->CommonData,
                /*=========== Datos Consultados ===========*/
                'rowData'           => $rowData,
                'arrCostos'         => $arrCostos,
                'arrPartidas'       => $arrPartidas,
                'arrPartidasProd'   => $arrPartidasProd,
                'arrPerdidas'       => $arrPerdidas,
                'Imprimir'          => $Imprimir,
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
    public function Resumen($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => '
                campanas_listado.idCampana,
                campanas_listado.idEstado,
                campanas_listado.fecha_auto,
                campanas_listado.Fecha,
                campanas_listado.Nombre,
                campanas_listado.Observaciones,
                campanas_listado.Costos,
                campanas_listado.Beneficios,
                campanas_listado.Perdidas,
                campanas_listado.Margen,
                campanas_listado.idBodegas,
                usuarios_listado.Nombre AS Creador,
                core_estados_apertura.Nombre AS EstadoNombre,
                core_estados_apertura.Color AS EstadoColor,
                bodegas_listado.Nombre AS Bodega',
            'table'   => 'campanas_listado',
            'join'    => '
                LEFT JOIN usuarios_listado        ON usuarios_listado.idUsuario               = campanas_listado.idUsuario
                LEFT JOIN core_estados_apertura   ON core_estados_apertura.idEstadoApertura   = campanas_listado.idEstado
                LEFT JOIN bodegas_listado         ON bodegas_listado.idBodegas                = campanas_listado.idBodegas',
            'where'   => 'campanas_listado.idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
            'data'    => 'idEstadoApertura AS ID,Nombre',
            'table'   => 'core_estados_apertura',
            'join'    => '',
            'where'   => 'idEstadoApertura!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams    = ['query' => $query];
        $arrEstados = $this->Base_GetList($xParams);

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
            'data'    => '
                COUNT(campanas_listado_partidas.idEstadoPartida) AS Cuenta,
                campanas_listado_partidas.idEstadoPartida,
                core_estados_partida.Nombre AS EstadoPartida',
            'table'   => 'campanas_listado_partidas',
            'join'    => 'LEFT JOIN core_estados_partida ON core_estados_partida.idEstadoPartida = campanas_listado_partidas.idEstadoPartida',
            'where'   => 'campanas_listado_partidas.idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => 'campanas_listado_partidas.idEstadoPartida',
            'having'  => '',
            'order'   => 'campanas_listado_partidas.idEstadoPartida ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams         = ['query' => $query];
        $arrEstadisticas = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => '
                campanas_listado_partidas.VentaFecha,
                campanas_listado_partidas_productos.idProducto,
                SUM(campanas_listado_partidas_productos.Cantidad) AS Total,
                SUM(campanas_listado_partidas_productos.Beneficios) AS Valor,
                productos_listado.Nombre AS Producto,
                core_unidades_medida.Nombre AS Unimed',
            'table'   => 'campanas_listado_partidas',
            'join'    => '
                LEFT JOIN campanas_listado_partidas_productos   ON campanas_listado_partidas_productos.idExistencia  = campanas_listado_partidas.idExistencia
                LEFT JOIN productos_listado                     ON productos_listado.idProducto                      = campanas_listado_partidas_productos.idProducto
                LEFT JOIN core_unidades_medida                  ON core_unidades_medida.idUniMed                     = productos_listado.idUniMed',
            'where'   => 'campanas_listado_partidas.idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'" AND campanas_listado_partidas.idEstadoPartida = 6',
            'group'   => 'campanas_listado_partidas.VentaFecha, campanas_listado_partidas_productos.idProducto',
            'having'  => '',
            'order'   => 'campanas_listado_partidas.VentaFecha ASC, campanas_listado_partidas_productos.idProducto ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams       = ['query' => $query];
        $arrDataVentas = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if ($rowData!==false) {
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'        => 'Resumen Campaña '.$rowData['Nombre'],
                'PageDescription'  => 'Resumen Campañas.',
                'PageAuthor'       => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'     => ConfigAPP::SOFTWARE['SoftwareName'],
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'       => $this->FormInputs,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_Codification'     => $this->Codification,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData,
                'arrEstados'       => $arrEstados,
                'arrBodegas'       => $arrBodegas,
                'arrEstadisticas'  => $arrEstadisticas,
                'arrDataVentas'    => $arrDataVentas,
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
    public function ResumenUpdate($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => '
                campanas_listado.idCampana,
                campanas_listado.fecha_auto,
                campanas_listado.Fecha,
                campanas_listado.Nombre,
                campanas_listado.Observaciones,
                campanas_listado.Costos,
                campanas_listado.Beneficios,
                campanas_listado.Perdidas,
                campanas_listado.Margen,
                usuarios_listado.Nombre AS Creador,
                core_estados_apertura.Nombre AS EstadoNombre,
                core_estados_apertura.Color AS EstadoColor,
                bodegas_listado.Nombre AS Bodega',
            'table'   => 'campanas_listado',
            'join'    => '
                LEFT JOIN usuarios_listado        ON usuarios_listado.idUsuario               = campanas_listado.idUsuario
                LEFT JOIN core_estados_apertura   ON core_estados_apertura.idEstadoApertura   = campanas_listado.idEstado
                LEFT JOIN bodegas_listado         ON bodegas_listado.idBodegas                = campanas_listado.idBodegas',
            'where'   => 'campanas_listado.idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
                COUNT(campanas_listado_partidas.idEstadoPartida) AS Cuenta,
                campanas_listado_partidas.idEstadoPartida,
                core_estados_partida.Nombre AS EstadoPartida',
            'table'   => 'campanas_listado_partidas',
            'join'    => 'LEFT JOIN core_estados_partida ON core_estados_partida.idEstadoPartida = campanas_listado_partidas.idEstadoPartida',
            'where'   => 'campanas_listado_partidas.idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => 'campanas_listado_partidas.idEstadoPartida',
            'having'  => '',
            'order'   => 'campanas_listado_partidas.idEstadoPartida ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams         = ['query' => $query];
        $arrEstadisticas = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => '
                campanas_listado_partidas.VentaFecha,
                campanas_listado_partidas_productos.idProducto,
                SUM(campanas_listado_partidas_productos.Cantidad) AS Total,
                SUM(campanas_listado_partidas_productos.Beneficios) AS Valor,
                productos_listado.Nombre AS Producto,
                core_unidades_medida.Nombre AS Unimed',
            'table'   => 'campanas_listado_partidas',
            'join'    => '
                LEFT JOIN campanas_listado_partidas_productos   ON campanas_listado_partidas_productos.idExistencia  = campanas_listado_partidas.idExistencia
                LEFT JOIN productos_listado                     ON productos_listado.idProducto                      = campanas_listado_partidas_productos.idProducto
                LEFT JOIN core_unidades_medida                  ON core_unidades_medida.idUniMed                     = productos_listado.idUniMed',
            'where'   => 'campanas_listado_partidas.idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'" AND campanas_listado_partidas.idEstadoPartida = 6',
            'group'   => 'campanas_listado_partidas.VentaFecha, campanas_listado_partidas_productos.idProducto',
            'having'  => '',
            'order'   => 'campanas_listado_partidas.VentaFecha ASC, campanas_listado_partidas_productos.idProducto ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams       = ['query' => $query];
        $arrDataVentas = $this->Base_GetList($xParams);

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
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData,
                'arrEstadisticas'  => $arrEstadisticas,
                'arrDataVentas'    => $arrDataVentas,
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

        /******************************/
        //Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /******************************/
        //Verifico si existe
        if(isset($_POST['Fecha'])&&$_POST['Fecha']!=''){
            $_POST['Fecha_Dia']  = $this->DataDate->fecha2NdiaMes($_POST['Fecha']);
            $_POST['Fecha_Mes']  = $this->DataDate->fecha2NMes($_POST['Fecha']);
            $_POST['Fecha_Ano']  = $this->DataDate->fecha2Ano($_POST['Fecha']);
        }

        /******************************/
        //Se genera la query
        $query = [
            'data'      => 'idUsuario,idEstado,fecha_auto,Fecha,Fecha_Dia,Fecha_Mes,Fecha_Ano,Nombre,Observaciones,Costos,Beneficios,Perdidas,Margen,idBodegas',
            'required'  => 'idUsuario,idEstado,fecha_auto,Fecha,Fecha_Dia,Fecha_Mes,Fecha_Ano,Nombre,idBodegas',
            'unique'    => 'Nombre',
            'encode'    => '',
            'table'     => 'campanas_listado',
            'Post'      => $_POST
        ];
        //Ejecuto la query
        //Ejecuto la query
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        $Response = $this->Base_insert($xParams);

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
            if(isset($_POST['Fecha'])&&$_POST['Fecha']!=''){
                $_POST['Fecha_Dia']  = $this->DataDate->fecha2NdiaMes($_POST['Fecha']);
                $_POST['Fecha_Mes']  = $this->DataDate->fecha2NMes($_POST['Fecha']);
                $_POST['Fecha_Ano']  = $this->DataDate->fecha2Ano($_POST['Fecha']);
            }

            /******************************/
            //Se genera la query
            $query = [
                'data'      => 'idCampana,idUsuario,idEstado,fecha_auto,Fecha,Fecha_Dia,Fecha_Mes,Fecha_Ano,Nombre,Observaciones,Costos,Beneficios,Perdidas,Margen,idBodegas',
                'required'  => 'idUsuario,idEstado,fecha_auto,Fecha,Fecha_Dia,Fecha_Mes,Fecha_Ano,Nombre,idBodegas',
                'unique'    => 'Nombre',
                'encode'    => '',
                'table'     => 'campanas_listado',
                'where'     => 'idCampana',
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
                'table'       => 'campanas_listado',
                'where'       => 'idCampana',
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
                $arrTableDel[] = ['files' => '', 'table' => 'campanas_listado_costos'];
                $arrTableDel[] = ['files' => '', 'table' => 'campanas_listado_partidas'];
                $arrTableDel[] = ['files' => '', 'table' => 'campanas_listado_partidas_productos'];
                $arrTableDel[] = ['files' => '', 'table' => 'campanas_listado_perdidas'];

                /************************************************/
                //Verifico si existe
                if($arrTableDel){
                    //recorro
                    foreach ($arrTableDel as $tblDel) {
                        //Se genera la query
                        $query = ['files' => $tblDel['files'], 'table' => $tblDel['table'], 'where' => 'idCampana', 'SubCarpeta' => '', 'Post' => $dataDelete];
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
            'ValidarFecha'              => 'fecha_auto,Fecha',
            'ValidarHora'               => '',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => 'Nombre,Observaciones',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'Nombre',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Nombre,Observaciones',
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
    //Se actualizan los montos
    public function updateCostos($Tipo, $CampanaID){
        /********************************************************/
        //Se cambia la query dependiendo de el tipo
        switch ($Tipo) {
            /******************************/
            //Costos
            case 1:
                $Data = '
                campanas_listado.idCampana,
                campanas_listado.Beneficios AS TotalBeneficios,
                campanas_listado.Perdidas AS TotalPerdidas,
                (SELECT SUM(Costos) FROM campanas_listado_costos WHERE idCampana='.$CampanaID.') AS TotalCostos';
                break;
            /******************************/
            //Partidas
            case 2:
                $Data = '
                campanas_listado.idCampana,
                campanas_listado.Costos AS TotalCostos,
                campanas_listado.Perdidas AS TotalPerdidas,
                (SELECT SUM(Beneficios) FROM campanas_listado_partidas  WHERE idCampana='.$CampanaID.' AND idEstadoPartida=6) AS TotalBeneficios';
                break;
            /******************************/
            //Perdidas
            case 3:
                $Data = '
                campanas_listado.idCampana,
                campanas_listado.Costos AS TotalCostos,
                campanas_listado.Beneficios AS TotalBeneficios,
                (SELECT SUM(Perdidas) FROM campanas_listado_perdidas  WHERE idCampana='.$CampanaID.') AS TotalPerdidas';
                break;
        }
        /******************************************/
        //Se genera la query
        $query = [
            'data'    => $Data,
            'table'   => 'campanas_listado',
            'join'    => '',
            'where'   => 'idCampana = "'.$CampanaID.'"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /********************************************************/
        //Se agrega respuesta
        $arrTareas = [
            'idCampana'   => $CampanaID,
            'Costos'      => $rowData['TotalCostos'],
            'Beneficios'  => $rowData['TotalBeneficios'],
            'Perdidas'    => $rowData['TotalPerdidas'],
            'Margen'      => $rowData['TotalBeneficios'] - $rowData['TotalCostos'],
        ];
        /******************************/
        //Se genera la query
        $query = [
            'data'      => 'idCampana,Costos,Beneficios,Perdidas,Margen',
            'required'  => 'idCampana,Costos,Beneficios,Perdidas,Margen',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'campanas_listado',
            'where'     => 'idCampana',
            'Post'      => $arrTareas
        ];
        //Ejecuto la query
        $xParams = ['DataCheck' => '', 'query' => $query];
        $this->Base_update($xParams);

    }

}
