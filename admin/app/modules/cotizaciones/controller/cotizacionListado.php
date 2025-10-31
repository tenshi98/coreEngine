<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class cotizacionListado extends ControllerBase {

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
                cotizacion_listado.idCotizacion,
                cotizacion_listado.Creacion_fecha,
                cotizacion_listado.ValorTotal,

                entidades_listado.Nombre AS EntidadesNombre,
                entidades_listado.ApellidoPat AS EntidadesApellido,
                entidades_listado.RazonSocial AS EntidadesRazonSocial,
                entidades_listado.Nick AS EntidadesNick',
            'table'   => 'cotizacion_listado',
            'join'    => 'LEFT JOIN entidades_listado ON entidades_listado.idEntidad = cotizacion_listado.idEntidad',
            'where'   => 'cotizacion_listado.idCotizacion != 0',
            'group'   => '',
            'having'  => '',
            'order'   => 'cotizacion_listado.Creacion_fecha DESC, cotizacion_listado.idCotizacion DESC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $arrList = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idEntidad AS ID,CONCAT((CASE WHEN ( Nombre = "" OR Nombre IS NULL ) THEN RazonSocial ELSE CONCAT(Nombre,IFNULL( CONCAT( " ", ApellidoPat ), "" )) END ),CASE WHEN ( Nick = "" OR Nick IS NULL ) THEN "" ELSE CONCAT( " (", Nick, ")" ) END ) AS Nombre ',
            'table'   => 'entidades_listado',
            'join'    => '',
            'where'   => 'idEstado=1 AND idTipo=2',
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
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($arrList)){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Listado Cotizaciones',
                'PageDescription' => 'Listado Cotizaciones',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => 'Listado Cotizaciones',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_Codification'    => $this->Codification,
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                'Fnc_ServerServer'    => $this->ServerServer,
                'Fnc_CommonData'      => $this->CommonData,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList,
                'arrEntidades'    => $arrEntidades,
                'arrProductos'    => $arrProductos,
                'arrServicios'    => $arrServicios,

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
        $WhereData_int     = 'idEntidad,idCotizacion';                   //Datos búsqueda exacta
        $WhereData_string  = '';                                         //Datos búsqueda relativa
        $WhereData_between = 'Creacion_fecha-F_Inicio-F_Termino';        //Datos búsqueda Between
        $whereInt          = '';                                         //se crea cadena
        /******************************************/
        //agrego variable busqueda
        $whereInt = $this->searchWhere($whereInt, $WhereData_int, 'cotizacion_listado', 1);
        $whereInt = $this->searchWhere($whereInt, $WhereData_string, 'cotizacion_listado', 2);
        $whereInt = $this->searchWhere($whereInt, $WhereData_between, 'cotizacion_listado', 3);
        //Verifico si esta vacio
        $whereInt2 = $whereInt ? $whereInt . ' AND cotizacion_listado.idCotizacion != 0' : 'cotizacion_listado.idCotizacion != 0';

        /******************************/
        //Se genera la query
        $query = [
            'data'    => '
                cotizacion_listado.idCotizacion,
                cotizacion_listado.Creacion_fecha,
                cotizacion_listado.ValorTotal,

                entidades_listado.Nombre AS EntidadesNombre,
                entidades_listado.ApellidoPat AS EntidadesApellido,
                entidades_listado.RazonSocial AS EntidadesRazonSocial,
                entidades_listado.Nick AS EntidadesNick',
            'table'   => 'cotizacion_listado',
            'join'    => 'LEFT JOIN entidades_listado ON entidades_listado.idEntidad = cotizacion_listado.idEntidad',
            'where'   => $whereInt2,
            'group'   => '',
            'having'  => '',
            'order'   => 'cotizacion_listado.Creacion_fecha DESC, cotizacion_listado.idCotizacion DESC',
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
                'TableTitle'      => 'Listado Cotizaciones',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'     => $this->Codification,
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'arrList'  => $arrList,
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
                cotizacion_listado.idCotizacion,
                cotizacion_listado.Creacion_fecha,
                cotizacion_listado.Creacion_hora,
                cotizacion_listado.Observaciones,
                cotizacion_listado.ValorNeto,
                cotizacion_listado.IVA,
                cotizacion_listado.ValorTotal,

                entidades_listado.Nombre AS EntidadesNombre,
                entidades_listado.ApellidoPat AS EntidadesApellido,
                entidades_listado.RazonSocial AS EntidadesRazonSocial,
                entidades_listado.Nick AS EntidadesNick,
                usuarios_listado.Nombre AS Usuario',
            'table'   => 'cotizacion_listado',
            'join'    => '
                LEFT JOIN entidades_listado  ON entidades_listado.idEntidad  = cotizacion_listado.idEntidad
                LEFT JOIN usuarios_listado   ON usuarios_listado.idUsuario   = cotizacion_listado.idUsuario',
            'where'   => 'cotizacion_listado.idCotizacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
            'table'   => 'cotizacion_listado_items',
            'join'    => '',
            'where'   => 'idCotizacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
                productos_listado.Nombre AS ProductoNombre,
                cotizacion_listado_productos.Number AS ProductoCantidad,
                cotizacion_listado_productos.ValorTotal AS ProductoValor,
                core_unidades_medida.Nombre AS UnidadMedida',
            'table'   => 'cotizacion_listado_productos',
            'join'    => '
                LEFT JOIN productos_listado     ON productos_listado.idProducto    = cotizacion_listado_productos.idProducto
                LEFT JOIN core_unidades_medida  ON core_unidades_medida.idUniMed   = productos_listado.idUniMed',
            'where'   => 'cotizacion_listado_productos.idCotizacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'cotizacion_listado_productos.idExistencia ASC',
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
                cotizacion_listado_servicios.Number AS ServicioCantidad,
                cotizacion_listado_servicios.ValorTotal AS ServicioValor',
            'table'   => 'cotizacion_listado_servicios',
            'join'    => 'LEFT JOIN servicios_listado  ON servicios_listado.idServicio  = cotizacion_listado_servicios.idServicio',
            'where'   => 'cotizacion_listado_servicios.idCotizacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'cotizacion_listado_servicios.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams      = ['query' => $query];
        $arrServicios = $this->Base_GetList($xParams);

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
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData,
                'arrItems'         => $arrItems,
                'arrProductos'     => $arrProductos,
                'arrServicios'     => $arrServicios,
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
                cotizacion_listado.idCotizacion,
                cotizacion_listado.Creacion_fecha,
                cotizacion_listado.Creacion_hora,
                cotizacion_listado.Observaciones,
                cotizacion_listado.ValorNeto,
                cotizacion_listado.IVA,
                cotizacion_listado.ValorTotal,

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
            'where'   => 'cotizacion_listado.idCotizacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
            'table'   => 'cotizacion_listado_items',
            'join'    => '',
            'where'   => 'idCotizacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
                productos_listado.Nombre AS ProductoNombre,
                cotizacion_listado_productos.Number AS ProductoCantidad,
                cotizacion_listado_productos.ValorTotal AS ProductoValor,
                core_unidades_medida.Nombre AS UnidadMedida',
            'table'   => 'cotizacion_listado_productos',
            'join'    => '
                LEFT JOIN productos_listado     ON productos_listado.idProducto   = cotizacion_listado_productos.idProducto
                LEFT JOIN core_unidades_medida  ON core_unidades_medida.idUniMed  = productos_listado.idUniMed',
            'where'   => 'cotizacion_listado_productos.idCotizacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'cotizacion_listado_productos.idExistencia ASC',
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
                cotizacion_listado_servicios.Number AS ServicioCantidad,
                cotizacion_listado_servicios.ValorTotal AS ServicioValor',
            'table'   => 'cotizacion_listado_servicios',
            'join'    => 'LEFT JOIN servicios_listado  ON servicios_listado.idServicio  = cotizacion_listado_servicios.idServicio',
            'where'   => 'cotizacion_listado_servicios.idCotizacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'cotizacion_listado_servicios.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams      = ['query' => $query];
        $arrServicios = $this->Base_GetList($xParams);

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
                'rowSistema'       => $rowSistema,
                'arrItems'         => $arrItems,
                'arrProductos'     => $arrProductos,
                'arrServicios'     => $arrServicios,
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
    public function Resumen($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
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

                entidades_listado.Nombre AS EntidadesNombre,
                entidades_listado.ApellidoPat AS EntidadesApellido,
                entidades_listado.RazonSocial AS EntidadesRazonSocial,
                entidades_listado.Nick AS EntidadesNick,
                usuarios_listado.Nombre AS Usuario',
            'table'   => 'cotizacion_listado',
            'join'    => '
                LEFT JOIN entidades_listado  ON entidades_listado.idEntidad = cotizacion_listado.idEntidad
                LEFT JOIN usuarios_listado   ON usuarios_listado.idUsuario  = cotizacion_listado.idUsuario',
            'where'   => 'cotizacion_listado.idCotizacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
            'where'   => 'idEstado=1 AND idTipo=2',
            'group'   => '',
            'having'  => '',
            'order'   => 'ApellidoPat ASC,Nombre ASC,RazonSocial ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams      = ['query' => $query];
        $arrEntidades = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if ($rowData!==false) {
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'        => 'Resumen Cotización',
                'PageDescription'  => 'Resumen Cotización.',
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
                'rowData'         => $rowData,
                'arrEntidades'    => $arrEntidades,
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
                cotizacion_listado.idCotizacion,
                cotizacion_listado.Creacion_fecha,
                cotizacion_listado.Creacion_hora,
                cotizacion_listado.Observaciones,
                cotizacion_listado.ValorNeto,
                cotizacion_listado.IVA,
                cotizacion_listado.ValorTotal,

                entidades_listado.Nombre AS EntidadesNombre,
                entidades_listado.ApellidoPat AS EntidadesApellido,
                entidades_listado.RazonSocial AS EntidadesRazonSocial,
                entidades_listado.Nick AS EntidadesNick,
                usuarios_listado.Nombre AS Usuario',
            'table'   => 'cotizacion_listado',
            'join'    => '
                LEFT JOIN entidades_listado  ON entidades_listado.idEntidad   = cotizacion_listado.idEntidad
                LEFT JOIN usuarios_listado   ON usuarios_listado.idUsuario    = cotizacion_listado.idUsuario',
            'where'   => 'cotizacion_listado.idCotizacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
                'UserAccess'    => $arrLevel[$this->controllerName],
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

        //var para validaciones
        $DataVal['Count'] = $ndata_1 + $ndata_2 + $ndata_3;
        $DataVal['Msg']   = 'No hay nada ingresado';

        //generacion de errores
        if($DataVal['Count']==0) {
            echo Response::sendData(500, $DataVal['Msg']);
        }else{

            /******************************/
            //Se genera el chequeo
            $DataCheck = $this->dataCheck($_POST);

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
                    $x_ValorTotal = (isset($_POST['Item_ValorTotal'][$j1])) ? $x_ValorTotal + $_POST['Item_ValorTotal'][$j1] : $x_ValorTotal;
                    $x_TotalItems = (isset($_POST['Item_ValorTotal'][$j1])) ? $x_TotalItems + $_POST['Item_ValorTotal'][$j1] : $x_TotalItems;
                }
            }
            //Productos
            if(isset($ndata_2)&&$ndata_2!=0){
                //recorro los items
                for($j1 = 0; $j1 < $ndata_2; $j1++){
                    $x_ValorTotal     = (isset($_POST['Producto_ValorTotal'][$j1])) ? $x_ValorTotal + $_POST['Producto_ValorTotal'][$j1] : $x_ValorTotal;
                    $x_TotalProductos = (isset($_POST['Producto_ValorTotal'][$j1])) ? $x_TotalProductos + $_POST['Producto_ValorTotal'][$j1] : $x_TotalProductos;
                }
            }
            //Servicios
            if(isset($ndata_3)&&$ndata_3!=0){
                //recorro los items
                for($j1 = 0; $j1 < $ndata_3; $j1++){
                    $x_ValorTotal     = (isset($_POST['Servicio_ValorTotal'][$j1])) ? $x_ValorTotal + $_POST['Servicio_ValorTotal'][$j1] : $x_ValorTotal;
                    $x_TotalServicios = (isset($_POST['Servicio_ValorTotal'][$j1])) ? $x_TotalServicios + $_POST['Servicio_ValorTotal'][$j1] : $x_TotalServicios;
                }
            }

            /*******************************************************/
            //Se generan datos
            $_POST['ValorNeto']       = ($x_ValorTotal/1.19);
            $_POST['IVA']             = $x_ValorTotal - ($x_ValorTotal/1.19);
            $_POST['ValorTotal']      = $x_ValorTotal;
            $_POST['TotalItems']      = $x_TotalItems;
            $_POST['TotalProductos']  = $x_TotalProductos;
            $_POST['TotalServicios']  = $x_TotalServicios;
            //Verifico si existe
            if(isset($_POST['Creacion_fecha'])&&$_POST['Creacion_fecha']!=''){
                $_POST['Creacion_Semana']  = $this->DataDate->fecha2NSemana($_POST['Creacion_fecha']);
                $_POST['Creacion_mes']     = $this->DataDate->fecha2NMes($_POST['Creacion_fecha']);
                $_POST['Creacion_ano']     = $this->DataDate->fecha2Ano($_POST['Creacion_fecha']);
            }

            /******************************/
            //Se genera la query
            $query = [
                'data'      => 'idUsuario,idEntidad,fecha_auto,Creacion_fecha,Creacion_Semana,Creacion_mes,Creacion_ano,Creacion_hora,Observaciones,ValorNeto,IVA,ValorTotal,TotalItems,TotalProductos,TotalServicios',
                'required'  => 'idUsuario,idEntidad,fecha_auto,Creacion_fecha',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'cotizacion_listado',
                'Post'      => $_POST
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
                            'idCotizacion' => $Response,
                            'Item'          => $_POST['Item_Item'][$j1],
                            'Number'        => $_POST['Item_Number'][$j1],
                            'ValorTotal'    => $_POST['Item_ValorTotal'][$j1],
                        ];
                        /******************************/
                        //Se genera la query
                        $query = [
                            'data'      => 'idCotizacion,Item,Number,ValorTotal',
                            'required'  => 'idCotizacion,Item,Number,ValorTotal',
                            'unique'    => '',
                            'encode'    => '',
                            'table'     => 'cotizacion_listado_items',
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

                    /******************************/
                    //recorro los items
                    for($j1 = 0; $j1 < $ndata_2; $j1++){
                        /******************************/
                        //Se agrega respuesta
                        $arrTareas = [
                            'idCotizacion'   => $Response,
                            'idProducto'      => $_POST['Producto_idProducto'][$j1],
                            'Number'          => $_POST['Producto_Number'][$j1],
                            'ValorTotal'      => $_POST['Producto_ValorTotal'][$j1],
                        ];
                        /******************************/
                        //Se genera la query
                        $query = [
                            'data'      => 'idCotizacion,idProducto,Number,ValorTotal',
                            'required'  => 'idCotizacion,idProducto,Number,ValorTotal',
                            'unique'    => '',
                            'encode'    => '',
                            'table'     => 'cotizacion_listado_productos',
                            'Post'      => $arrTareas
                        ];
                        //Ejecuto la query
                        $xParams           = ['DataCheck' => '', 'query' => $query];
                        $ResponseProductos = $this->Base_insert($xParams);
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
                            'idCotizacion' => $Response,
                            'idServicio'    => $_POST['Servicio_idServicio'][$j1],
                            'Number'        => $_POST['Servicio_Number'][$j1],
                            'ValorTotal'    => $_POST['Servicio_ValorTotal'][$j1],
                        ];
                        /******************************/
                        //Se genera la query
                        $query = [
                            'data'      => 'idCotizacion,idServicio,Number,ValorTotal',
                            'required'  => 'idCotizacion,idServicio,Number,ValorTotal',
                            'unique'    => '',
                            'encode'    => '',
                            'table'     => 'cotizacion_listado_servicios',
                            'Post'      => $arrTareas
                        ];
                        //Ejecuto la query
                        $xParams           = ['DataCheck' => '', 'query' => $query];
                        $ResponseServicios = $this->Base_insert($xParams);
                    }
                }

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
                'data'      => 'idCotizacion,idUsuario,idEntidad,fecha_auto,Creacion_fecha,Creacion_Semana,Creacion_mes,Creacion_ano,Creacion_hora,Observaciones,ValorNeto,IVA,ValorTotal,TotalItems,TotalProductos,TotalServicios,TotalGuias',
                'required'  => 'idUsuario,idEntidad,fecha_auto,Creacion_fecha',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'cotizacion_listado',
                'where'     => 'idCotizacion',
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
                'table'       => 'cotizacion_listado',
                'where'       => 'idCotizacion',
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
                $arrTableDel[] = ['files' => '', 'table' => 'cotizacion_listado_items'];
                $arrTableDel[] = ['files' => '', 'table' => 'cotizacion_listado_productos'];
                $arrTableDel[] = ['files' => '', 'table' => 'cotizacion_listado_servicios'];

                /************************************************/
                //Verifico si existe
                if($arrTableDel){
                    //recorro
                    foreach ($arrTableDel as $tblDel) {
                        //Se genera la query
                        $query = ['files' => $tblDel['files'], 'table' => $tblDel['table'], 'where' => 'idCotizacion', 'SubCarpeta' => '', 'Post' => $dataDelete];
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
    /******************************************************************************/
    //Se actualizan los montos
    public function updateFact($Tipo, $CotizacionID){
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
                (SELECT SUM(ValorTotal) FROM cotizacion_listado_items     WHERE idCotizacion='.$CotizacionID.') AS TotalItems';
                break;
            /******************************/
            //Productos
            case 2:
                $Data = '
                cotizacion_listado.idCotizacion,
                cotizacion_listado.TotalItems,
                cotizacion_listado.TotalServicios,
                (SELECT SUM(ValorTotal) FROM cotizacion_listado_productos WHERE idCotizacion='.$CotizacionID.') AS TotalProductos';
                break;
            /******************************/
            //Servicios
            case 3:
                $Data = '
                cotizacion_listado.idCotizacion,
                cotizacion_listado.TotalItems,
                cotizacion_listado.TotalProductos,
                (SELECT SUM(ValorTotal) FROM cotizacion_listado_servicios WHERE idCotizacion='.$CotizacionID.') AS TotalServicios';
                break;
        }

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => $Data,
            'table'   => 'cotizacion_listado',
            'join'    => '',
            'where'   => 'idCotizacion = "'.$CotizacionID.'"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /******************************/
        //Calculo
        $x_ValorTotal = $rowData['TotalItems'] + $rowData['TotalProductos'] + $rowData['TotalServicios'];
        //Se agrega respuesta
        $arrTareas = [
            'idCotizacion'   => $CotizacionID,
            'ValorNeto'       => ($x_ValorTotal/1.19),
            'IVA'             => $x_ValorTotal - ($x_ValorTotal/1.19),
            'ValorTotal'      => $x_ValorTotal,
            'TotalItems'      => $rowData['TotalItems'],
            'TotalProductos'  => $rowData['TotalProductos'],
            'TotalServicios'  => $rowData['TotalServicios'],
        ];
        /******************************/
        //Se genera la query
        $query = [
            'data'      => 'idCotizacion,ValorNeto,IVA,ValorTotal,TotalItems,TotalProductos,TotalServicios',
            'required'  => 'idCotizacion,ValorNeto,IVA,ValorTotal,TotalItems,TotalProductos,TotalServicios',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'cotizacion_listado',
            'where'     => 'idCotizacion',
            'Post'      => $arrTareas
        ];
        //Ejecuto la query
        $xParams       = ['DataCheck' => '', 'query' => $query];
        $ResponseTarea = $this->Base_update($xParams);
    }

}