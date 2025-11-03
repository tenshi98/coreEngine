<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class gestionCampanasPartidas extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $ServerServer;
    private $DataOperations;
    private $DataNumbers;
    private $DataDate;
    private $CommonData;
    private $Notifications;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName  = 'gestionCampanas';
		$this->FormInputs      = new UIFormInputs();
		$this->Codification    = new FunctionsSecurityCodification();
		$this->ServerServer    = new FunctionsServerServer();
		$this->DataOperations  = new FunctionsDataOperations();
		$this->DataNumbers     = new FunctionsDataNumbers();
		$this->DataDate        = new FunctionsDataDate();
		$this->CommonData      = new FunctionsCommonData();
		$this->Notifications   = new FunctionsServerSocial();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //Crear nuevo
    public function New_unique($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => 'idCampana',
            'table'   => 'campanas_listado',
            'join'    => '',
            'where'   => 'idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
                'Fnc_FormInputs'       => $this->FormInputs,
                'Fnc_Codification'     => $this->Codification,
                'Fnc_ServerServer'     => $this->ServerServer,
                /*=========== Datos Consultados ===========*/
                'rowData'           => $rowData,
                'arrEntidades'      => $arrEntidades,
                'arrProductos'      => $arrProductos,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Partidas-formNew.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //Crear nuevo
    public function New_step1($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => 'idCampana',
            'table'   => 'campanas_listado',
            'join'    => '',
            'where'   => 'idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
            'data'    => 'idSector AS ID,Nombre',
            'table'   => 'entidades_sectores',
            'join'    => '',
            'where'   => 'idSector!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrSector = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idTipoEntidad AS ID,Nombre',
            'table'   => 'core_tipos_entidades',
            'join'    => '',
            'where'   => 'idTipoEntidad!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams        = ['query' => $query];
        $arrTipoEntidad = $this->Base_GetList($xParams);

        /******************************/
        //Se genera la query
        $query = [
            'data'    => 'idCiudad AS ID,Nombre',
            'table'   => 'core_ubicacion_ciudad',
            'join'    => '',
            'where'   => '',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrCiudad = $this->Base_GetList($xParams);

        /******************************/
        //Se genera la query
        $query = [
            'data'    => 'idComuna AS ID1, idCiudad AS ID2, Nombre',
            'table'   => 'core_ubicacion_comunas',
            'join'    => '',
            'where'   => '',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrComuna = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idOpciones AS ID,Nombre',
            'table'   => 'core_opciones',
            'join'    => '',
            'where'   => 'idOpciones!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams     = ['query' => $query];
        $arrOpciones = $this->Base_GetList($xParams);

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
                'Fnc_FormInputs'       => $this->FormInputs,
                'Fnc_Codification'     => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'rowData'           => $rowData,
                'arrSector'         => $arrSector,
                'arrTipoEntidad'    => $arrTipoEntidad,
                'arrCiudad'         => $arrCiudad,
                'arrComuna'         => $arrComuna,
                'arrOpciones'       => $arrOpciones,
                'arrProductos'      => $arrProductos,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Partidas-formNew-step1.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //Crear nuevo
    public function New_step2($f3){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => 'idCampana',
            'table'   => 'campanas_listado',
            'join'    => '',
            'where'   => 'idCampana = "'.$_POST['idCampana'].'"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        //Variables
        $WhereData_int     = 'idTipoEntidad,idSector,idCiudad,idComuna';                   //Datos búsqueda exacta
        $WhereData_string  = 'Nombre,ApellidoPat,ApellidoMat,Nick,RazonSocial,Direccion';  //Datos búsqueda relativa
        $WhereData_between = '';                                                           //Datos búsqueda Between
        $whereInt          = 'entidades_listado.idEstado=1';                               //Solo entidades activas
        $whereInt         .= ' AND entidades_listado.idTipo=2';                            //Solo del tipo cliente
        /******************************************/
        //agrego variable busqueda
        $whereInt = $this->searchWhere($whereInt, $WhereData_int, 'entidades_listado', 1);
        $whereInt = $this->searchWhere($whereInt, $WhereData_string, 'entidades_listado', 2);
        $whereInt = $this->searchWhere($whereInt, $WhereData_between, 'entidades_listado', 3);
        //Verifico si esta vacio
        $DiasCreacion = $this->DataOperations->restarDias($this->ServerServer->fechaActual(),$_POST['nDias']);
        //Se verifica si se necesita telefono
        if(isset($_POST['idTieneFono'])){
            switch ($_POST['idTieneFono']) {
                case 1: $whereInt = $whereInt ? $whereInt . ' AND entidades_listado.Fono1!=""' : 'entidades_listado.Fono1!=""'; break;//Tiene Fono
                case 2: $whereInt = $whereInt ? $whereInt . ' AND entidades_listado.Fono1=""' : 'entidades_listado.Fono1=""';   break;//No tiene fono
            }
        }
        //Filtro de fecha
        $whereInt1 = $whereInt ? $whereInt . ' AND facturacion_listado.Creacion_fecha>="'.$DiasCreacion.'"' : 'facturacion_listado.Creacion_fecha>="'.$DiasCreacion.'"';

        /******************************/
        //Se genera la query
        $query = [
            'data'    => 'facturacion_listado.idEntidad',
            'table'   => 'facturacion_listado',
            'join'    => 'LEFT JOIN entidades_listado  ON entidades_listado.idEntidad = facturacion_listado.idEntidad',
            'where'   => $whereInt1,
            'group'   => '',
            'having'  => '',
            'order'   => 'facturacion_listado.idEntidad ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrOut    = $this->Base_GetList($xParams);

        /******************************/
        $arrOut2 = array();
        //Se verifica si se dejan fuera los repetidos
        if(isset($_POST['idRepetidos'])&&$_POST['idRepetidos']==1){
            //Se genera la query
            $query = [
                'data'    => 'idEntidad',
                'table'   => 'campanas_listado_partidas',
                'join'    => '',
                'where'   => 'idCampana = "'.$_POST['idCampana'].'"',
                'group'   => '',
                'having'  => '',
                'order'   => 'idEntidad ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            //Ejecuto la query
            $xParams   = ['query' => $query];
            $arrOut2   = $this->Base_GetList($xParams);
        }

        /******************************/
        //Se crea cadena con los elementos a ignorar
        $whereInt2  = $whereInt;
        $whereInt2 .= '  AND entidades_listado.idEntidad NOT IN (0';
        //Se genera la consulta
        if (!empty($arrOut)) {  $whereInt2 .= ',' . implode(',', array_column($arrOut, 'idEntidad'));}
        if (!empty($arrOut2)) { $whereInt2 .= ',' . implode(',', array_column($arrOut2, 'idEntidad'));}
        $whereInt2 .= ')';

        /******************************/
        //Se genera la query
        $query = [
            'data'    => '
                entidades_listado.idEntidad,
                entidades_listado.idTipoEntidad,
                entidades_listado.Nombre,
                entidades_listado.ApellidoPat,
                entidades_listado.ApellidoMat,
                entidades_listado.RazonSocial,
                entidades_listado.Nick,
                entidades_sectores.Nombre AS Sector,
                core_tipos_entidades.Nombre AS TipoEntidad',
            'table'   => 'entidades_listado',
            'join'    => '
                LEFT JOIN entidades_sectores   ON entidades_sectores.idSector         = entidades_listado.idSector
                LEFT JOIN core_tipos_entidades ON core_tipos_entidades.idTipoEntidad  = entidades_listado.idTipoEntidad',
            'where'   => $whereInt2,
            'group'   => '',
            'having'  => '',
            'order'   => 'entidades_listado.ApellidoPat ASC, entidades_listado.Nombre ASC, entidades_listado.RazonSocial ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $arrList = $this->Base_GetList($xParams);

        /*******************************************************************/
        //variables
        $ndata_1   = isset($_POST['Producto_idProducto']) ? count($_POST['Producto_idProducto']) : 0;
        $ProdSelec = '0';

        /******************************/
        //recorro los items
        if(isset($ndata_1)&&$ndata_1!=0){
            for($j1 = 0; $j1 < $ndata_1; $j1++){
                $ProdSelec .= ','.$_POST['Producto_idProducto'][$j1];
            }
        }


        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($arrList)){
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'       => $this->FormInputs,
                'Fnc_Codification'     => $this->Codification,
                'Fnc_ServerServer'     => $this->ServerServer,
                /*=========== Datos Consultados ===========*/
                'rowData'     => $rowData,
                'arrList'     => $arrList,
                'ProdSelec'   => $ProdSelec,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Partidas-formNew-step2.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //List
    public function UpdateList($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => 'idEstado',
            'table'   => 'campanas_listado',
            'join'    => '',
            'where'   => 'idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
                campanas_listado_partidas.idExistencia,
                campanas_listado_partidas.idCampana,
                campanas_listado_partidas.Fecha,
                campanas_listado_partidas.idEstadoPartida,
                campanas_listado_partidas.idFacturacion,
                entidades_listado.Nombre AS EntidadNombre,
                entidades_listado.ApellidoPat AS EntidadApellido,
                entidades_listado.RazonSocial AS EntidadRazonSocial,
                entidades_listado.Nick AS EntidadNick,
                entidades_listado.Fono1 AS EntidadFono1,
                entidades_sectores.Nombre AS EntidadSector,
                entidades_listado.Direccion AS EntidadDireccion,
                core_estados_partida.Nombre AS EstadoPartida,
                core_estados_partida.Color AS EstadoPartidaColor,
                core_documentos_mercantiles.Nombre AS DocumentoNombre,
                facturacion_listado.N_Doc AS DocumentoN_Doc',
            'table'   => 'campanas_listado_partidas',
            'join'    => '
                LEFT JOIN entidades_listado             ON entidades_listado.idEntidad                = campanas_listado_partidas.idEntidad
                LEFT JOIN entidades_sectores            ON entidades_sectores.idSector                = entidades_listado.idSector
                LEFT JOIN core_estados_partida          ON core_estados_partida.idEstadoPartida       = campanas_listado_partidas.idEstadoPartida
                LEFT JOIN facturacion_listado           ON facturacion_listado.idFacturacion          = campanas_listado_partidas.idFacturacion
                LEFT JOIN core_documentos_mercantiles   ON core_documentos_mercantiles.idDocumentos   = facturacion_listado.idDocumentos',
            'where'   => 'campanas_listado_partidas.idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'" AND campanas_listado_partidas.idEstadoPartida!=6',
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
                campanas_listado_partidas_productos.idProducto,
                campanas_listado_partidas_productos.Cantidad,
                campanas_listado_partidas_productos.Beneficios,
                campanas_listado_partidas.idExistencia,
                productos_listado.Nombre AS Producto,
                core_unidades_medida.Nombre AS Unimed',
            'table'   => 'campanas_listado_partidas',
            'join'    => '
                LEFT JOIN campanas_listado_partidas_productos   ON campanas_listado_partidas_productos.idExistencia  = campanas_listado_partidas.idExistencia
                LEFT JOIN productos_listado                     ON productos_listado.idProducto                      = campanas_listado_partidas_productos.idProducto
                LEFT JOIN core_unidades_medida                  ON core_unidades_medida.idUniMed                     = productos_listado.idUniMed',
            'where'   => 'campanas_listado_partidas.idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'" AND campanas_listado_partidas.idEstadoPartida!=6',
            'group'   => '',
            'having'  => '',
            'order'   => 'campanas_listado_partidas.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams         = ['query' => $query];
        $arrPartidasProd = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($arrPartidas)){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'    => $this->Codification,
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                'Fnc_CommonData'      => $this->CommonData,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData,
                'arrPartidas'      => $arrPartidas,
                'arrPartidasProd'  => $arrPartidasProd,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Partidas-UpdateList.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //List
    public function UpdateListFin($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => 'idEstado',
            'table'   => 'campanas_listado',
            'join'    => '',
            'where'   => 'idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
                campanas_listado_partidas.idExistencia,
                campanas_listado_partidas.VentaFecha,
                campanas_listado_partidas.idEstadoPartida,
                campanas_listado_partidas.idFacturacion,
                entidades_listado.Nombre AS EntidadNombre,
                entidades_listado.ApellidoPat AS EntidadApellido,
                entidades_listado.RazonSocial AS EntidadRazonSocial,
                entidades_listado.Nick AS EntidadNick,
                entidades_sectores.Nombre AS EntidadSector,
                entidades_listado.Direccion AS EntidadDireccion,
                core_estados_partida.Nombre AS EstadoPartida,
                core_documentos_mercantiles.Nombre AS DocumentoNombre,
                facturacion_listado.N_Doc AS DocumentoN_Doc',
            'table'   => 'campanas_listado_partidas',
            'join'    => '
                LEFT JOIN entidades_listado             ON entidades_listado.idEntidad                = campanas_listado_partidas.idEntidad
                LEFT JOIN entidades_sectores            ON entidades_sectores.idSector                = entidades_listado.idSector
                LEFT JOIN core_estados_partida          ON core_estados_partida.idEstadoPartida       = campanas_listado_partidas.idEstadoPartida
                LEFT JOIN facturacion_listado           ON facturacion_listado.idFacturacion          = campanas_listado_partidas.idFacturacion
                LEFT JOIN core_documentos_mercantiles   ON core_documentos_mercantiles.idDocumentos   = facturacion_listado.idDocumentos',
            'where'   => 'campanas_listado_partidas.idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'" AND campanas_listado_partidas.idEstadoPartida = 6',
            'group'   => '',
            'having'  => '',
            'order'   => 'campanas_listado_partidas.VentaFecha ASC, entidades_sectores.Nombre ASC, entidades_listado.Direccion ASC, campanas_listado_partidas.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams     = ['query' => $query];
        $arrPartidas = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => '
                campanas_listado_partidas_productos.idProducto,
                campanas_listado_partidas_productos.Cantidad,
                campanas_listado_partidas_productos.Beneficios,
                campanas_listado_partidas.idExistencia,
                productos_listado.Nombre AS Producto,
                core_unidades_medida.Nombre AS Unimed',
            'table'   => 'campanas_listado_partidas',
            'join'    => '
                LEFT JOIN campanas_listado_partidas_productos   ON campanas_listado_partidas_productos.idExistencia  = campanas_listado_partidas.idExistencia
                LEFT JOIN productos_listado                     ON productos_listado.idProducto                      = campanas_listado_partidas_productos.idProducto
                LEFT JOIN core_unidades_medida                  ON core_unidades_medida.idUniMed                     = productos_listado.idUniMed',
            'where'   => 'campanas_listado_partidas.idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'" AND campanas_listado_partidas.idEstadoPartida = 6',
            'group'   => '',
            'having'  => '',
            'order'   => 'campanas_listado_partidas.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams         = ['query' => $query];
        $arrPartidasProd = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($arrPartidas)){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'    => $this->Codification,
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                'Fnc_CommonData'      => $this->CommonData,
                /*=========== Datos Consultados ===========*/
                'rowData'            => $rowData,
                'arrPartidas'        => $arrPartidas,
                'arrPartidasProd'    => $arrPartidasProd,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-PartidasFin-UpdateList.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //Edit
    public function GetID($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => '
                campanas_listado_partidas.idExistencia,
                campanas_listado_partidas.idCampana,
                campanas_listado_partidas.Fecha,
                campanas_listado_partidas.idEntidad,
                campanas_listado_partidas.idEstadoPartida,
                campanas_listado_partidas.idFacturacion,
                campanas_listado.idBodegas,
                entidades_listado.Nombre AS EntidadNombre,
                entidades_listado.ApellidoPat AS EntidadApellido,
                entidades_listado.RazonSocial AS EntidadRazonSocial,
                entidades_listado.Nick AS EntidadNick,
                entidades_sectores.Nombre AS EntidadSector,
                entidades_listado.Direccion AS EntidadDireccion,
                core_estados_partida.Nombre AS EstadoPartida,
                entidades_listado.Fono1 AS EntidadFono',
            'table'   => 'campanas_listado_partidas',
            'join'    => '
                LEFT JOIN campanas_listado       ON campanas_listado.idCampana             = campanas_listado_partidas.idCampana
                LEFT JOIN entidades_listado      ON entidades_listado.idEntidad            = campanas_listado_partidas.idEntidad
                LEFT JOIN entidades_sectores     ON entidades_sectores.idSector            = entidades_listado.idSector
                LEFT JOIN core_estados_partida   ON core_estados_partida.idEstadoPartida   = campanas_listado_partidas.idEstadoPartida',
            'where'   => 'campanas_listado_partidas.idExistencia = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
                campanas_listado_partidas_productos.idProdCamp,
                campanas_listado_partidas_productos.idProducto,
                campanas_listado_partidas_productos.Cantidad,
                campanas_listado_partidas_productos.Beneficios,
                productos_listado.Nombre AS Producto',
            'table'   => 'campanas_listado_partidas_productos',
            'join'    => 'LEFT JOIN productos_listado ON productos_listado.idProducto = campanas_listado_partidas_productos.idProducto',
            'where'   => 'campanas_listado_partidas_productos.idExistencia = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
            'data'    => 'idEstadoPartida AS ID,Nombre',
            'table'   => 'core_estados_partida',
            'join'    => '',
            'where'   => 'idEstadoPartida>='.$rowData['idEstadoPartida'].' AND idEstadoPartida NOT IN (3)',
            'group'   => '',
            'having'  => '',
            'order'   => 'idEstadoPartida ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams    = ['query' => $query];
        $arrEstados = $this->Base_GetList($xParams);

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
            'data'    => 'idEstadoPago AS ID,Nombre',
            'table'   => 'core_estados_pago',
            'join'    => '',
            'where'   => 'idEstadoPago!=3',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams       = ['query' => $query];
        $arrEstadoPago = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idDocumentoPago AS ID,Nombre',
            'table'   => 'core_documentos_pago',
            'join'    => '',
            'where'   => 'idDocumentoPago!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams          = ['query' => $query];
        $arrDocumentoPago = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se filtran los ya existentes
        $X_Where = 'idEstado=1  AND idProducto NOT IN (0';
        //Se genera la consulta
        if (!empty($arrPartidasProd)) {$X_Where .= ',' . implode(',', array_column($arrPartidasProd, 'idProducto'));}
        $X_Where .= ')';
        //Se genera la query
        $query = [
            'data'    => 'idProducto AS ID,Nombre',
            'table'   => 'productos_listado',
            'join'    => '',
            'where'   => $X_Where,
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
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
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'    => $this->FormInputs,
                'Fnc_Codification'  => $this->Codification,
                'Fnc_DataNumbers'   => $this->DataNumbers,
                'Fnc_ServerServer'  => $this->ServerServer,
                'Fnc_DataDate'      => $this->DataDate,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData,
                'arrEstados'       => $arrEstados,
                'arrDocumentos'    => $arrDocumentos,
                'arrEstadoPago'    => $arrEstadoPago,
                'arrDocumentoPago' => $arrDocumentoPago,
                'arrPartidasProd'  => $arrPartidasProd,
                'arrProductos'     => $arrProductos,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Partidas-formEdit.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //Edit
    public function GetIDFinalizadas($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => '
                campanas_listado_partidas.idExistencia,
                campanas_listado_partidas.idCampana,

                campanas_listado_partidas.Fecha,
                campanas_listado_partidas.idEntidad,
                entidades_listado.Nombre AS EntidadNombre,
                entidades_listado.ApellidoPat AS EntidadApellido,
                entidades_listado.RazonSocial AS EntidadRazonSocial,
                entidades_listado.Nick AS EntidadNick,
                entidades_sectores.Nombre AS EntidadSector,
                entidades_listado.Direccion AS EntidadDireccion,
                core_estados_partida.Nombre AS EstadoPartida,

                campanas_listado_partidas.idFacturacion,
                facturacion_listado.N_Doc,
                facturacion_listado.Creacion_fecha,
                facturacion_listado.Observaciones,
                core_documentos_mercantiles.Nombre AS Documento',
            'table'   => 'campanas_listado_partidas',
            'join'    => '
                LEFT JOIN campanas_listado              ON campanas_listado.idCampana                 = campanas_listado_partidas.idCampana
                LEFT JOIN entidades_listado             ON entidades_listado.idEntidad                = campanas_listado_partidas.idEntidad
                LEFT JOIN entidades_sectores            ON entidades_sectores.idSector                = entidades_listado.idSector
                LEFT JOIN core_estados_partida          ON core_estados_partida.idEstadoPartida       = campanas_listado_partidas.idEstadoPartida
                LEFT JOIN facturacion_listado           ON facturacion_listado.idFacturacion          = campanas_listado_partidas.idFacturacion
                LEFT JOIN core_documentos_mercantiles   ON core_documentos_mercantiles.idDocumentos   = facturacion_listado.idDocumentos',
            'where'   => 'campanas_listado_partidas.idExistencia = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
                campanas_listado_partidas_productos.idProdCamp,
                campanas_listado_partidas_productos.idProducto,
                campanas_listado_partidas_productos.Cantidad,
                campanas_listado_partidas_productos.Beneficios,
                productos_listado.Nombre AS Producto',
            'table'   => 'campanas_listado_partidas_productos',
            'join'    => 'LEFT JOIN productos_listado ON productos_listado.idProducto = campanas_listado_partidas_productos.idProducto',
            'where'   => 'campanas_listado_partidas_productos.idExistencia = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'    => $this->FormInputs,
                'Fnc_Codification'  => $this->Codification,
                'Fnc_DataNumbers'   => $this->DataNumbers,
                'Fnc_ServerServer'  => $this->ServerServer,
                'Fnc_DataDate'      => $this->DataDate,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData,
                'arrPartidasProd'  => $arrPartidasProd,
                'arrEntidades'     => $arrEntidades,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-PartidasFin-formEdit.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //Edit
    public function sendCampanaMassiveForm($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se arman los datos
        $rowData['idCampana']       = $this->Codification->encryptDecrypt('decrypt', $params['CampanaID']);
        $rowData['Fecha']           = $this->Codification->encryptDecrypt('decrypt', $params['Fecha']);
        $rowData['idEstadoPartida'] = $this->Codification->encryptDecrypt('decrypt', $params['PartidaID']);

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
                'Fnc_FormInputs'    => $this->FormInputs,
                'Fnc_DataDate'      => $this->DataDate,
                'Fnc_Codification'  => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Partidas-sendMassive.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }

    }

    /******************************************************************************/
    //Edit
    public function sendCampanaWhatsappForm($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => '
                campanas_listado_partidas.idExistencia,
                campanas_listado_partidas.idCampana,

                campanas_listado_partidas.Fecha,
                entidades_listado.Nombre AS EntidadNombre,
                entidades_listado.ApellidoPat AS EntidadApellido,
                entidades_listado.RazonSocial AS EntidadRazonSocial,
                entidades_listado.Nick AS EntidadNick',
            'table'   => 'campanas_listado_partidas',
            'join'    => '
                LEFT JOIN campanas_listado   ON campanas_listado.idCampana   = campanas_listado_partidas.idCampana
                LEFT JOIN entidades_listado  ON entidades_listado.idEntidad  = campanas_listado_partidas.idEntidad',
            'where'   => 'campanas_listado_partidas.idExistencia = "'.$this->Codification->encryptDecrypt('decrypt', $params['ExistenciaID']).'"',
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
                'Fnc_FormInputs'    => $this->FormInputs,
                'Fnc_Codification'  => $this->Codification,
                'Fnc_DataDate'      => $this->DataDate,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Partidas-sendWhatsapp.php');
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
        //variables
        $ndata_1 = isset($_POST['Clients_idEntidad']) ? count($_POST['Clients_idEntidad']) : 0;

        //var para validaciones
        $DataVal['Count'] = $ndata_1;
        $DataVal['Msg']   = 'No hay nada ingresado';

        //generacion de errores
        if($DataVal['Count']==0) {
            echo Response::sendData(500, $DataVal['Msg']);
        }else{
            /******************************************/
            //Verifico si existe
            if(isset($_POST['Fecha'])&&$_POST['Fecha']!=''){
                $_POST['Fecha_Dia']  = $this->DataDate->fecha2NdiaMes($_POST['Fecha']);
                $_POST['Fecha_Mes']  = $this->DataDate->fecha2NMes($_POST['Fecha']);
                $_POST['Fecha_Ano']  = $this->DataDate->fecha2Ano($_POST['Fecha']);
            }
            /*******************************************************/
            //Items
            if(isset($ndata_1)&&$ndata_1!=0){
                //recorro los items
                for($j1 = 0; $j1 < $ndata_1; $j1++){
                    /******************************/
                    //Se agrega respuesta
                    $arrTareas = [
                        'idCampana'        => $_POST['idCampana'],
                        'Fecha'            => $_POST['Fecha'],
                        'Fecha_Dia'        => $_POST['Fecha_Dia'],
                        'Fecha_Mes'        => $_POST['Fecha_Mes'],
                        'Fecha_Ano'        => $_POST['Fecha_Ano'],
                        'idEntidad'        => $_POST['Clients_idEntidad'][$j1],
                        'idEstadoPartida'  => $_POST['idEstadoPartida'],
                        'idUsuario'        => $_POST['idUsuario'],
                    ];
                    /******************************/
                    //Se genera la query
                    $query = [
                        'data'      => 'idCampana,Fecha,Fecha_Dia,Fecha_Mes,Fecha_Ano,idEntidad,Cantidad,Beneficios,idEstadoPartida,idFacturacion,idUsuario',
                        'required'  => 'idCampana,Fecha,Fecha_Dia,Fecha_Mes,Fecha_Ano,idEntidad,idEstadoPartida,idUsuario',
                        'unique'    => 'idCampana-Fecha-idEntidad',
                        'encode'    => '',
                        'table'     => 'campanas_listado_partidas',
                        'Post'      => $arrTareas
                    ];
                    //Ejecuto la query
                    $xParams       = ['DataCheck' => '', 'query' => $query];
                    $ResponseItems = $this->Base_insert($xParams);

                    /******************************/
                    //Solo si devuelve un ID, en caso contrario devuelve una alerta que no se mostrara debido a que esta en un bucle
                    if (is_numeric($ResponseItems)) {
                        //Variables
                        $ndata_2 = explode(",", $_POST['ProdSelec']);
                        //recorro los items
                        foreach($ndata_2 as $idProducto){
                            if($idProducto!=0){
                                /******************************/
                                //Se agrega respuesta
                                $arrTareasProd = [
                                    'idExistencia'   => $ResponseItems,
                                    'idCampana'      => $_POST['idCampana'],
                                    'idProducto'     => $idProducto,
                                ];
                                /******************************/
                                //Se genera la query
                                $query = [
                                    'data'      => 'idExistencia,idCampana,idProducto',
                                    'required'  => 'idExistencia,idCampana,idProducto',
                                    'unique'    => '',
                                    'encode'    => '',
                                    'table'     => 'campanas_listado_partidas_productos',
                                    'Post'      => $arrTareasProd
                                ];
                                //Ejecuto la query
                                $xParams = ['DataCheck' => '', 'query' => $query];
                                $this->Base_insert($xParams);

                            }
                        }
                    }
                }
                /******************************************/
                // Devuelvo true con código 200 (OK)
                echo Response::sendData(200, true);
            }
        }
    }
    /******************************************************************************/
    //Crear
    public function InsertUnique(){

        /*******************************************************************/
        //variables
        $ndata_1 = isset($_POST['Producto_idProducto']) ? count($_POST['Producto_idProducto']) : 0;

        if($ndata_1==0) {
            echo Response::sendData(500, 'No hay nada ingresado');
        }else{
            /******************************/
            //Verifico si existe
            if(isset($_POST['Fecha'])&&$_POST['Fecha']!=''){
                $_POST['Fecha_Dia']  = $this->DataDate->fecha2NdiaMes($_POST['Fecha']);
                $_POST['Fecha_Mes']  = $this->DataDate->fecha2NMes($_POST['Fecha']);
                $_POST['Fecha_Ano']  = $this->DataDate->fecha2Ano($_POST['Fecha']);
            }

            /******************************/
            //Se agrega respuesta
            $arrTareas = [
                'idCampana'        => $_POST['idCampana'],
                'Fecha'            => $_POST['Fecha'],
                'Fecha_Dia'        => $_POST['Fecha_Dia'],
                'Fecha_Mes'        => $_POST['Fecha_Mes'],
                'Fecha_Ano'        => $_POST['Fecha_Ano'],
                'idEntidad'        => $_POST['idEntidad'],
                'idEstadoPartida'  => $_POST['idEstadoPartida'],
                'idUsuario'        => $_POST['idUsuario'],
            ];
            /******************************/
            //Se genera la query
            $query = [
                'data'      => 'idCampana,Fecha,Fecha_Dia,Fecha_Mes,Fecha_Ano,idEntidad,Cantidad,Beneficios,idEstadoPartida,idFacturacion,idUsuario',
                'required'  => 'idCampana,Fecha,Fecha_Dia,Fecha_Mes,Fecha_Ano,idEntidad,idEstadoPartida,idUsuario',
                'unique'    => 'idCampana-Fecha-idEntidad-idEstadoPartida!=6',
                'encode'    => '',
                'table'     => 'campanas_listado_partidas',
                'Post'      => $arrTareas
            ];
            //Ejecuto la query
            $xParams  = ['DataCheck' => '', 'query' => $query];
            $Response = $this->Base_insert($xParams);

            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un ID numérico o algún otro valor.
            if (is_numeric($Response)) {

                /******************************/
                //recorro los items
                for($j1 = 0; $j1 < $ndata_1; $j1++){
                    /******************************/
                    //Se agrega respuesta
                    $arrTareas = [
                        'idExistencia'   => $Response,
                        'idCampana'      => $_POST['idCampana'],
                        'idProducto'     => $_POST['Producto_idProducto'][$j1],
                    ];
                    /******************************/
                    //Se genera la query
                    $query = [
                        'data'      => 'idExistencia,idCampana,idProducto',
                        'required'  => 'idExistencia,idCampana,idProducto',
                        'unique'    => '',
                        'encode'    => '',
                        'table'     => 'campanas_listado_partidas_productos',
                        'Post'      => $arrTareas
                    ];
                    //Ejecuto la query
                    $xParams = ['DataCheck' => '', 'query' => $query];
                    $this->Base_insert($xParams);
                }

                /******************************************/
                // Devuelvo true con código 200 (OK)
                echo Response::sendData(200, true);
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
    public function Update($f3){
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            /*******************************************************************/
            //Se llaman los datos
            $UserData = $f3->get('SESSION.DataInfo');

            /******************************/
            //Se genera el chequeo
            $DataCheck = $this->dataCheck($_POST);

            /******************************************/
            //Variables
            $nProds     = isset($_POST['Producto_idProducto']) ? count($_POST['Producto_idProducto']) : 0;       //Productos al inicio de la campaña
            $nProdsNew  = isset($_POST['NewProducto_idProducto']) ? count($_POST['NewProducto_idProducto']) : 0; //Productos agregados despues
            $Beneficios = 0;
            //recorro los items
            for($j1 = 0; $j1 < $nProds; $j1++){
                /******************************/
                //Si no se elimina el producto
                if($_POST['Producto_Eliminar'][$j1]==1&&$_POST['Producto_Valor'][$j1]!=''){
                    /******************************/
                    //Sumo
                    $Beneficios = (isset($_POST['Producto_Valor'][$j1])) ? $Beneficios + $_POST['Producto_Valor'][$j1] : $Beneficios;
                    /******************************/
                    //Se agrega respuesta
                    $arrTareas = [
                        'idProdCamp'  => $_POST['Producto_idProdCamp'][$j1],
                        'idProducto'  => $_POST['Producto_idProducto'][$j1],
                        'Cantidad'    => $_POST['Producto_Cantidad'][$j1],
                        'Beneficios'  => $_POST['Producto_Valor'][$j1],
                    ];
                    /******************************/
                    //Se genera la query
                    $query = [
                        'data'      => 'idProdCamp,idProducto,Cantidad,Beneficios',
                        'required'  => 'idProdCamp',
                        'unique'    => '',
                        'encode'    => '',
                        'table'     => 'campanas_listado_partidas_productos',
                        'where'     => 'idProdCamp',
                        'Post'      => $arrTareas
                    ];
                    //Ejecuto la query
                    $xParams = ['DataCheck' => $DataCheck, 'query' => $query];
                    $this->Base_update($xParams);

                /******************************/
                //Si se elimina producto
                }elseif($_POST['Producto_Eliminar'][$j1]==2){
                    /***************************************/
                    //Se elimina producto en la partida
                    $ActionSQL  = 'DELETE FROM `campanas_listado_partidas_productos` WHERE `idProdCamp` = '.$_POST['Producto_idProdCamp'][$j1];
                    //Se ejecuta la query
                    $xParams = ['query' => $ActionSQL];
                    $this->Base_queryExecute($xParams);
                }
            }
            //Verifico si hay elementos
            if($nProdsNew!=0){
                //recorro los items
                for($j1 = 0; $j1 < $nProdsNew; $j1++){
                    /******************************/
                    //Sumo
                    $Beneficios = (isset($_POST['Producto_Valor'][$j1])) ? $Beneficios + $_POST['Producto_Valor'][$j1] : $Beneficios;
                    /******************************/
                    //Se agrega respuesta
                    $arrTareasProd = [
                        'idExistencia' => $_POST['idExistencia'],
                        'idCampana'    => $_POST['idCampana'],
                        'idProducto'   => $_POST['NewProducto_idProducto'][$j1],
                        'Cantidad'     => $_POST['NewProducto_Cantidad'][$j1],
                        'Beneficios'   => $_POST['NewProducto_Valor'][$j1],
                    ];
                    /******************************/
                    //Se genera la query
                    $query = [
                        'data'      => 'idExistencia,idCampana,idProducto,Cantidad,Beneficios',
                        'required'  => 'idExistencia,idCampana,idProducto,Cantidad,Beneficios',
                        'unique'    => '',
                        'encode'    => '',
                        'table'     => 'campanas_listado_partidas_productos',
                        'Post'      => $arrTareasProd
                    ];
                    //Ejecuto la query
                    $xParams = ['DataCheck' => '', 'query' => $query];
                    $this->Base_insert($xParams);
                }
            }

            /******************************************/
            //Compra entregada
            if(isset($_POST['idEstadoPartida'])&&$_POST['idEstadoPartida']==6){

                /*******************************************************/
                //Se generan datos
                $_POST['ValorNeto']       = ($Beneficios/1.19);
                $_POST['IVA']             = $Beneficios - ($Beneficios/1.19);
                $_POST['ValorTotal']      = $Beneficios;
                $_POST['TotalProductos']  = $Beneficios;
                //Verifico si existe
                if(isset($_POST['Creacion_fecha'])&&$_POST['Creacion_fecha']!=''){
                    $_POST['Creacion_Semana']  = $this->DataDate->fecha2NSemana($_POST['Creacion_fecha']);
                    $_POST['Creacion_mes']     = $this->DataDate->fecha2NMes($_POST['Creacion_fecha']);
                    $_POST['Creacion_ano']     = $this->DataDate->fecha2Ano($_POST['Creacion_fecha']);
                }
                //Pagado
                if(isset($_POST['idEstadoPago'])&&$_POST['idEstadoPago']==2){
                    $_POST['MontoPagado']  = $Beneficios;
                }

                /******************************/
                //Se genera la query
                $query = [
                    'data'      => 'idUsuario,idTipo,idEntidad,idBodegasIngreso,idBodegasEgreso,fecha_auto,idDocumentos,N_Doc,Creacion_fecha,Creacion_Semana,Creacion_mes,Creacion_ano,Creacion_hora,Observaciones,ValorNeto,IVA,ValorTotal,TotalItems,TotalProductos,TotalServicios,idEstadoPago,MontoPagado',
                    'required'  => 'idUsuario,idTipo,idEntidad,fecha_auto,idDocumentos,Creacion_fecha,idEstadoPago',
                    'unique'    => '',
                    'encode'    => '',
                    'table'     => 'facturacion_listado',
                    'Post'      => $_POST
                ];

                //Ejecuto la query
                $xParams       = ['DataCheck' => '', 'query' => $query];
                $FacturacionID = $this->Base_insert($xParams);

                /*******************************************************/
                //Productos
                for($j1 = 0; $j1 < $nProds; $j1++){
                    /******************************/
                    //Se agrega respuesta
                    $arrTareas = [
                        'idFacturacion'   => $FacturacionID,
                        'idEstadoIngreso' => $_POST['idTipo'],
                        'idBodegas'       => $_POST['idBodegasEgreso'],
                        'idProducto'      => $_POST['Producto_idProducto'][$j1],
                        'Number'          => $_POST['Producto_Cantidad'][$j1],
                        'ValorTotal'      => $_POST['Producto_Valor'][$j1],
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
                    $xParams = ['DataCheck' => '', 'query' => $query];
                    $this->Base_insert($xParams);
                }
                //Verifico si hay elementos
                if($nProdsNew!=0){
                    //recorro los items
                    for($j1 = 0; $j1 < $nProdsNew; $j1++){
                        /******************************/
                        //Se agrega respuesta
                        $arrTareas = [
                            'idFacturacion'   => $FacturacionID,
                            'idEstadoIngreso' => $_POST['idTipo'],
                            'idBodegas'       => $_POST['idBodegasEgreso'],
                            'idProducto'      => $_POST['NewProducto_idProducto'][$j1],
                            'Number'          => $_POST['NewProducto_Cantidad'][$j1],
                            'ValorTotal'      => $_POST['NewProducto_Valor'][$j1],
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
                        $xParams = ['DataCheck' => '', 'query' => $query];
                        $this->Base_insert($xParams);
                    }
                }

                /**********************************************************************/
                //Movimiento de bodegas
                //permite la interaccion con la bodega, para generar documentos de ingreso o egreso
                if($UserData["gestionDocumentosUsoBodega"]==2){
                    $this->createMovBodega($_POST, $FacturacionID, $nProds, $nProdsNew);
                }
                /**********************************************************************/
                //Pagado
                if(isset($_POST['idEstadoPago'])&&$_POST['idEstadoPago']==2){

                    /*******************************************************/
                    //pagos
                    //Se agrega respuesta
                    $arrTareas = [
                        'idFacturacion'    => $FacturacionID,
                        'idUsuario'        => $_POST['idUsuario'],
                        'idDocumentoPago'  => $_POST['idDocumentoPago'],
                        'N_Doc'            => $_POST['N_DocPago'],
                        'MontoPagado'      => $_POST['MontoPagado'],
                        'FechaPago'        => $_POST['FechaPago'],
                    ];
                    /******************************/
                    //Se genera la query
                    $query = [
                        'data'      => 'idFacturacion,idUsuario,idDocumentoPago,N_Doc,MontoPagado,FechaPago',
                        'required'  => 'idFacturacion,idUsuario,idDocumentoPago,MontoPagado,FechaPago',
                        'unique'    => '',
                        'encode'    => '',
                        'table'     => 'facturacion_listado_pagos',
                        'Post'      => $arrTareas
                    ];
                    //Ejecuto la query
                    $xParams = ['DataCheck' => '', 'query' => $query];
                    $this->Base_insert($xParams);

                }
            }else{
                $FacturacionID = 0;
            }

            /******************************************/
            //id de la facturacion
            $_POST['idFacturacion']  = $FacturacionID;
            $_POST['Beneficios']     = $Beneficios;
            /******************************************/
            //Verifico si existe
            if(isset($_POST['Fecha'])&&$_POST['Fecha']!=''){
                $_POST['Fecha_Dia']  = $this->DataDate->fecha2NdiaMes($_POST['Fecha']);
                $_POST['Fecha_Mes']  = $this->DataDate->fecha2NMes($_POST['Fecha']);
                $_POST['Fecha_Ano']  = $this->DataDate->fecha2Ano($_POST['Fecha']);
            }
            /******************************************/
            //Verifico si existe
            if(isset($_POST['Creacion_fecha'])&&$_POST['Creacion_fecha']!=''){
                //Se crean variables
                $_POST['VentaFecha']      = $_POST['Creacion_fecha'];
                $_POST['VentaFecha_Dia']  = $this->DataDate->fecha2NdiaMes($_POST['Creacion_fecha']);
                $_POST['VentaFecha_Mes']  = $this->DataDate->fecha2NMes($_POST['Creacion_fecha']);
                $_POST['VentaFecha_Ano']  = $this->DataDate->fecha2Ano($_POST['Creacion_fecha']);
            }
            /******************************************/
            //Compra confirmada
            if(isset($_POST['idEstadoPartida'])&&$_POST['idEstadoPartida']==4){
                //Se crean variables
                $_POST['ConfirmacionFecha']      = $_POST['fecha_auto'];
                $_POST['ConfirmacionFecha_Dia']  = $this->DataDate->fecha2NdiaMes($_POST['fecha_auto']);
                $_POST['ConfirmacionFecha_Mes']  = $this->DataDate->fecha2NMes($_POST['fecha_auto']);
                $_POST['ConfirmacionFecha_Ano']  = $this->DataDate->fecha2Ano($_POST['fecha_auto']);
            }

            /******************************/
            //Se genera la query
            $query = [
                'data'      => 'idExistencia,idCampana,Fecha,Fecha_Dia,Fecha_Mes,Fecha_Ano,idEntidad,Beneficios,idEstadoPartida,idFacturacion,idUsuario,VentaFecha,VentaFecha_Dia,VentaFecha_Mes,VentaFecha_Ano,ConfirmacionFecha,ConfirmacionFecha_Dia,ConfirmacionFecha_Mes,ConfirmacionFecha_Ano',
                'required'  => 'idCampana,Fecha,Fecha_Dia,Fecha_Mes,Fecha_Ano,idEntidad,idEstadoPartida,idUsuario',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'campanas_listado_partidas',
                'where'     => 'idExistencia',
                'Post'      => $_POST
            ];
            //Ejecuto la query
            $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
            $Response = $this->Base_update($xParams);

            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response===true) {
                /******************************************/
                //Se actualizan los datos del costo
                $gestionCampanas = new gestionCampanas();
                $gestionCampanas->updateCostos(2, $_POST['idCampana']);
                /******************************************/
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
    //Editar por put (solo modificar datos)
    //Editar por post (modificar y subir archivos)
    public function UpdateFinalizadas($f3){
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            /*******************************************************************/
            //Se llaman los datos
            $UserData = $f3->get('SESSION.DataInfo');

            /******************************************/
            //Verifico si hay datos
            if(isset($_POST['idFacturacion'])&&$_POST['idFacturacion']!=''){

                /*******************************************************/
                /*                      PRODUCTOS                      */
                /*******************************************************/
                /******************************************/
                //Obtengo total de items
                $nProds    = isset($_POST['Producto_idProducto']) ? count($_POST['Producto_idProducto']) : 0;
                $countProd = 0;
                $totalProd = 0;

                /******************************************/
                //recorro los items
                for($j1 = 0; $j1 < $nProds; $j1++){
                    //Sumo el total del valor de los productos
                    $totalProd = (isset($_POST['Producto_Valor'][$j1])) ? $totalProd + $_POST['Producto_Valor'][$j1] : $totalProd;
                    //Solo si hay cambios
                    if(isset($_POST['Producto_Cantidad'][$j1],$_POST['Old_Producto_Cantidad'][$j1],$_POST['Producto_Valor'][$j1],$_POST['Old_Producto_Valor'][$j1])&&($_POST['Producto_Cantidad'][$j1]!=$_POST['Old_Producto_Cantidad'][$j1] || $_POST['Producto_Valor'][$j1]!=$_POST['Old_Producto_Valor'][$j1])){
                        /******************************/
                        //Se agrega respuesta
                        $arrTareas = [
                            'idProdCamp'  => $_POST['Producto_idProdCamp'][$j1],
                            'idProducto'  => $_POST['Producto_idProducto'][$j1],
                            'Cantidad'    => $_POST['Producto_Cantidad'][$j1],
                            'Beneficios'  => $_POST['Producto_Valor'][$j1],
                        ];
                        /******************************/
                        //Se genera la query
                        $query = [
                            'data'      => 'idProdCamp,idProducto,Cantidad,Beneficios',
                            'required'  => 'idProdCamp',
                            'unique'    => '',
                            'encode'    => '',
                            'table'     => 'campanas_listado_partidas_productos',
                            'where'     => 'idProdCamp',
                            'Post'      => $arrTareas
                        ];
                        //Ejecuto la query
                        $xParams = ['DataCheck' => '', 'query' => $query];
                        $this->Base_update($xParams);
                        /******************************/
                        //Cuento
                        $countProd++;
                    }
                }

                /*******************************************************/
                /*                      PARTIDA                        */
                /*******************************************************/
                //Variable para la actualizacion de la partida
                $chainUpdate  = 'idExistencia = "'.$_POST['idExistencia'].'"';
                $chainUpdate .= ', Beneficios = "'.$totalProd.'"';

                /*******************************************************/
                /*                    FECHA PARTIDA                    */
                /*******************************************************/
                //Solo si hay cambios
                if(isset($_POST['Fecha'],$_POST['Old_Fecha'])&&$_POST['Fecha']!=$_POST['Old_Fecha']){
                    /***************************************/
                    //Se actualiza la Fecha en la partida
                    $chainUpdate .= ', Fecha = "'.$_POST['Fecha'].'"';
                }

                /*******************************************************/
                /*                      ENTIDADES                      */
                /*******************************************************/
                //Solo si hay cambios
                if(isset($_POST['idEntidad'],$_POST['Old_idEntidad'])&&$_POST['idEntidad']!=$_POST['Old_idEntidad']){
                    /***************************************/
                    //Se actualiza la entidad en la partida
                    $chainUpdate .= ', idEntidad = "'.$_POST['idEntidad'].'"';
                    /***************************************/
                    //Se actualiza la entidad en la partida
                    $ActionSQL  = 'UPDATE `facturacion_listado` SET idEntidad = "'.$_POST['idEntidad'].'" WHERE idFacturacion = "'.$_POST['idFacturacion'].'" ';
                    //Se ejecuta la query
                    $xParams = ['query' => $ActionSQL];
                    $this->Base_queryExecute($xParams);
                }

                /*******************************************************/
                /*                 ACTUALIZACION PARTIDA               */
                /*******************************************************/
                //Se actualiza la entidad en la partida
                $ActionSQL  = 'UPDATE `campanas_listado_partidas` SET '.$chainUpdate.' WHERE idExistencia = "'.$_POST['idExistencia'].'" ';
                //Se ejecuta la query
                $xParams = ['query' => $ActionSQL];
                $this->Base_queryExecute($xParams);

                /******************************************/
                //Se Actualizan datos solo si hay cambios
                if($countProd!=0){

                    /*******************************************************/
                    /*                      FACTURAS                       */
                    /*******************************************************/
                    //Se actualizan los datos de los productos en la factura
                    for($j1 = 0; $j1 < $nProds; $j1++){
                        //Solo si hay cambios
                        if(isset($_POST['Producto_Cantidad'][$j1],$_POST['Old_Producto_Cantidad'][$j1],$_POST['Producto_Valor'][$j1],$_POST['Old_Producto_Valor'][$j1])&&($_POST['Producto_Cantidad'][$j1]!=$_POST['Old_Producto_Cantidad'][$j1] || $_POST['Producto_Valor'][$j1]!=$_POST['Old_Producto_Valor'][$j1])){
                            /***************************************/
                            //Se actualizan las cantidades en las facturas
                            $ActionSQL  = 'UPDATE `facturacion_listado_productos` ';
                            $ActionSQL .= 'SET Number = "'.$_POST['Producto_Cantidad'][$j1].'", ValorTotal = "'.$_POST['Producto_Valor'][$j1].'"';
                            $ActionSQL .= 'WHERE';
                            $ActionSQL .= ' idFacturacion  = "'.$_POST['idFacturacion'].'"';
                            $ActionSQL .= ' AND idProducto = "'.$_POST['Producto_idProducto'][$j1].'"';
                            $ActionSQL .= ' AND Number     = "'.$_POST['Old_Producto_Cantidad'][$j1].'"';
                            $ActionSQL .= ' AND ValorTotal = "'.$_POST['Old_Producto_Valor'][$j1].'"';
                            //Se ejecuta la query
                            $xParams = ['query' => $ActionSQL];
                            $this->Base_queryExecute($xParams);
                        }
                    }
                    /******************************************/
                    //Se consultan los datos
                    $query = [
                        'data'    => '
                            idEstadoPago,
                            (SELECT SUM(ValorTotal) FROM facturacion_listado_productos  WHERE idFacturacion='.$_POST['idFacturacion'].') AS ValorTotal,
                            (SELECT idPago          FROM facturacion_listado_pagos      WHERE idFacturacion='.$_POST['idFacturacion'].' LIMIT 1) AS idPago,
                            (SELECT idMovimiento    FROM bodegas_movimientos            WHERE idFacturacion='.$_POST['idFacturacion'].' LIMIT 1) AS idMovimiento,
                            (SELECT idBodegasEgreso FROM bodegas_movimientos            WHERE idFacturacion='.$_POST['idFacturacion'].' LIMIT 1) AS BodegaID',
                        'table'   => 'facturacion_listado',
                        'join'    => '',
                        'where'   => 'idFacturacion = "'.$_POST['idFacturacion'].'"',
                        'group'   => '',
                        'having'  => '',
                        'order'   => ''
                    ];
                    //Ejecuto la query
                    $xParams = ['query' => $query];
                    $rowFact = $this->Base_GetByID($xParams);
                    /***************************************/
                    //Se actualizan los datos
                    $Fact_ValorNeto       = ($rowFact['ValorTotal']/1.19);
                    $Fact_IVA             = $rowFact['ValorTotal'] - $Fact_ValorNeto;
                    $Fact_ValorTotal      = $rowFact['ValorTotal'];
                    $Fact_TotalProductos  = $rowFact['ValorTotal'];
                    //Se actualizan las cantidades en las facturas
                    $ActionSQL  = 'UPDATE `facturacion_listado` ';
                    $ActionSQL .= 'SET';
                    $ActionSQL .= '  ValorNeto      = "'.$Fact_ValorNeto.'"';
                    $ActionSQL .= ', IVA            = "'.$Fact_IVA.'"';
                    $ActionSQL .= ', ValorTotal     = "'.$Fact_ValorTotal.'"';
                    $ActionSQL .= ', TotalProductos = "'.$Fact_TotalProductos.'"';
                    //Si esta pagado
                    if($rowFact['idEstadoPago']==2){
                        $ActionSQL .= ', MontoPagado = "'.$Fact_ValorTotal.'"';
                    }
                    $ActionSQL .= 'WHERE idFacturacion = "'.$_POST['idFacturacion'].'"';
                    //Se ejecuta la query
                    $xParams = ['query' => $ActionSQL];
                    $this->Base_queryExecute($xParams);
                    /***************************************/
                    //Se actualizan los datos solo si estan pagados
                    if(isset($rowFact['idPago'])&&$rowFact['idPago']!=''&&$rowFact['idEstadoPago']==2){
                        //Se actualizan las cantidades en las facturas
                        $ActionSQL  = 'UPDATE `facturacion_listado_pagos` SET MontoPagado = "'.$Fact_ValorTotal.'" WHERE idPago = "'.$rowFact['idPago'].'"';
                        //Se ejecuta la query
                        $xParams = ['query' => $ActionSQL];
                        $this->Base_queryExecute($xParams);
                    }

                    /*******************************************************/
                    //permite la interaccion con la bodega, para generar documentos de ingreso o egreso
                    if($UserData["gestionDocumentosUsoBodega"]==2){
                        /*******************************************************/
                        /*                    MOVIMIENTOS                      */
                        /*******************************************************/
                        //Se actualizan los datos de los productos en los movimientos
                        for($j1 = 0; $j1 < $nProds; $j1++){
                            //Solo si hay cambios
                            if(isset($_POST['Producto_Cantidad'][$j1],$_POST['Old_Producto_Cantidad'][$j1])&&$_POST['Producto_Cantidad'][$j1]!=$_POST['Old_Producto_Cantidad'][$j1]){
                                /***************************************/
                                //Se actualizan las cantidades en las facturas
                                $ActionSQL  = 'UPDATE `bodegas_movimientos_productos` ';
                                $ActionSQL .= 'SET Number = "'.$_POST['Producto_Cantidad'][$j1].'"';
                                $ActionSQL .= 'WHERE idMovimiento = "'.$rowFact['idMovimiento'].'" AND idProducto = "'.$_POST['Producto_idProducto'][$j1].'"';
                                //Se ejecuta la query
                                $xParams = ['query' => $ActionSQL];
                                $this->Base_queryExecute($xParams);
                            }
                        }
                        /*******************************************************/
                        /*                       STOCKS                        */
                        /*******************************************************/
                        /******************************/
                        //Variables
                        $chainx        = '0';
                        $arrProdStock  = array();
                        /******************************/
                        //Se recorren los productos
                        for($j1 = 0; $j1 < $nProds; $j1++){
                            $chainx .= ','.$_POST['Producto_idProducto'][$j1];
                        }
                        /******************************/
                        //Se consultan los stocks
                        $query = [
                            'data'    => 'idStocks,idProducto,Cantidad_idBodegas_'.$rowFact['BodegaID'].' AS Cantidad',
                            'table'   => 'bodegas_productos_stocks',
                            'join'    => '',
                            'where'   => 'idProducto IN ('.$chainx.')',
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
                        foreach($arrStocks as $crud){
                            $arrProdStock[$crud['idProducto']]['idStocks'] = $crud['idStocks'];
                            $arrProdStock[$crud['idProducto']]['Cantidad'] = $crud['Cantidad'];
                        }

                        /******************************/
                        //Se recorren los productos
                        for($j1 = 0; $j1 < $nProds; $j1++){
                            /******************************/
                            //Variables
                            $ProductoID  = $_POST['Producto_idProducto'][$j1];
                            $NewCantidad = $arrProdStock[$ProductoID]['Cantidad'] + ($_POST['Old_Producto_Cantidad'][$j1] - $_POST['Producto_Cantidad'][$j1]);

                            /******************************/
                            //Se Actualizan los stocks
                            //verifico si existe el dato en el stock
                            if(isset($arrProdStock[$ProductoID]['idStocks'])&&$arrProdStock[$ProductoID]['idStocks']!=''){
                                /******************************/
                                //Se agrega respuesta
                                $arrTareas = [
                                    'idStocks'                                 => $arrProdStock[$ProductoID]['idStocks'],
                                    'Cantidad_idBodegas_'.$rowFact['BodegaID'] => $NewCantidad,
                                ];
                                /******************************/
                                //Se genera la query
                                $query = [
                                    'data'      => 'idStocks,Cantidad_idBodegas_'.$rowFact['BodegaID'],
                                    'required'  => 'idStocks,Cantidad_idBodegas_'.$rowFact['BodegaID'],
                                    'unique'    => '',
                                    'encode'    => '',
                                    'table'     => 'bodegas_productos_stocks',
                                    'where'     => 'idStocks',
                                    'Post'      => $arrTareas
                                ];
                                //Ejecuto la query
                                $xParams = ['DataCheck' => '', 'query' => $query];
                                $this->Base_update($xParams);
                            }
                        }
                    }
                }

                /******************************************/
                //Se actualizan los datos del costo
                $gestionCampanas   = new gestionCampanas();
                $gestionCampanas->updateCostos(2, $_POST['idCampana']);
                /******************************************/
                // Devuelvo true con código 200 (OK)
                echo Response::sendData(200, true);
            }
        }else {
            // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
            echo Response::sendData(500, "Error en el Request Method");
        }
    }

    /******************************************************************************/
    //Editar por put (solo modificar datos)
    //Editar por post (modificar y subir archivos)
    public function sendCampanaWhatsapp($f3){
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            /*******************************************************************/
            //Se llaman los datos
            $UserData = $f3->get('SESSION.DataInfo');

            /******************************************/
            //Se genera la query
            $query = [
                'data'    => '
                    campanas_listado_partidas.idExistencia,
                    campanas_listado_partidas.idCampana,

                    campanas_listado_partidas.Fecha,
                    entidades_listado.Nombre AS EntidadNombre,
                    entidades_listado.ApellidoPat AS EntidadApellido,
                    entidades_listado.RazonSocial AS EntidadRazonSocial,
                    entidades_listado.Nick AS EntidadNick',
                'table'   => 'campanas_listado_partidas',
                'join'    => '
                    LEFT JOIN campanas_listado   ON campanas_listado.idCampana   = campanas_listado_partidas.idCampana
                    LEFT JOIN entidades_listado  ON entidades_listado.idEntidad  = campanas_listado_partidas.idEntidad',
                'where'   => 'campanas_listado_partidas.idExistencia = "'.$_POST['idExistencia'].'"',
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

                /***************************************/
                //Se obtiene el nombre o la razón social
                $EntidadWsp = '';
                if (isset($rowData['EntidadNick'])&&$rowData['EntidadNick']!='') {
                    $EntidadWsp  = $rowData['EntidadNick'];
                }else{
                    if(isset($rowData['EntidadNombre'])&&$rowData['EntidadNombre']!=''){
                        $EntidadWsp = $rowData['EntidadNombre'].' '.$rowData['EntidadApellido'];
                    }else{
                        $EntidadWsp = $rowData['EntidadRazonSocial'];
                    }
                }
                /***************************************/
                //Se generan datos
                $WSP_encryptedId      = $this->Codification->simpleEncode($rowData['idExistencia'], 8080);
                $Config['Token']      = $UserData["Config_WhatsappToken"];
                $Config['InstanceId'] = $UserData["Config_WhatsappInstanceId"];
                $Config['Type']       = 2;
                $Config['namespace']  = '512f752c_ac4f_45a8_b5b5_2adcfe3ed73a';
                $Config['template']   = '1tek_alerta_1';
                $WSP_Body['Phone']    = $this->DataNumbers->normalizarPhone($rowData['EntidadFono1']);
                $WSP_Body['Entidad']  = $EntidadWsp;
                $WSP_Body['Mensaje']  = $_POST['Mensaje'];
                $WSP_Body['Link']     = ConfigAPP::SOFTWARE["URL"].'/partida/'.$WSP_encryptedId;

                /***************************************/
                //Se envia notificacion
                $Result = $this->Notifications->sendWhatsappTemplate($Config, $WSP_Body);

                /***************************************/
                // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
                if ($Result['success']===true) {
                    /***************************************/
                    //si se envia correctamente se actualizan registros
                    //Se arma la query
                    $ActionSQL = 'UPDATE `campanas_listado_partidas` SET `idEstadoPartida`=2 WHERE idExistencia = "'.$_POST['idExistencia'].'"';
                    /******************************************/
                    //Se ejecuta la query
                    $xParams = ['query' => $ActionSQL];
                    $this->Base_queryExecute($xParams);

                    /***************************************/
                    // Devuelvo $Response con código 200 (OK)
                    echo Response::sendData(200, $Result['success']);
                } else {
                    // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
                    // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
                    echo Response::sendData(500, $Result['error']);
                }

            /*******************************************************************/
            //si no hay resultados
            } else {
                echo Response::sendData(500, 'No hay datos filtrados');
            }
        }else {
            echo Response::sendData(500, "Error en el Request Method");
        }
    }

    /******************************************************************************/
    //Editar por put (solo modificar datos)
    //Editar por post (modificar y subir archivos)
    public function sendCampanaMassive($f3){
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            /*******************************************************************/
            //Se llaman los datos
            $UserData = $f3->get('SESSION.DataInfo');

            /******************************/
            //Se genera la query
            $query = [
                'data'    => '
                    campanas_listado_partidas.idExistencia,
                    entidades_listado.Nombre AS EntidadNombre,
                    entidades_listado.ApellidoPat AS EntidadApellido,
                    entidades_listado.RazonSocial AS EntidadRazonSocial,
                    entidades_listado.Nick AS EntidadNick,
                    entidades_listado.Fono1 AS EntidadFono1',
                'table'   => 'campanas_listado_partidas',
                'join'    => 'LEFT JOIN entidades_listado ON entidades_listado.idEntidad = campanas_listado_partidas.idEntidad',
                'where'   => 'campanas_listado_partidas.idCampana = "'.$_POST['idCampana'].'" AND campanas_listado_partidas.Fecha = "'.$_POST['Fecha'].'" AND campanas_listado_partidas.idEstadoPartida = "'.$_POST['idEstadoPartida'].'"',
                'group'   => '',
                'having'  => '',
                'order'   => 'campanas_listado_partidas.idExistencia ASC',
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

                /***************************************/
                //Variable
                $Count    = 0;
                $UpdateID = '0';

                /***************************************/
                //se recorren los datos dentro de la categoría
                foreach ($arrList as $crud){
                    /***************************************/
                    //Se obtiene el nombre o la razón social
                    $EntidadWsp = '';
                    if (isset($crud['EntidadNick'])&&$crud['EntidadNick']!='') {
                        $EntidadWsp  = $crud['EntidadNick'];
                    }else{
                        if(isset($crud['EntidadNombre'])&&$crud['EntidadNombre']!=''){
                            $EntidadWsp = $crud['EntidadNombre'].' '.$crud['EntidadApellido'];
                        }else{
                            $EntidadWsp = $crud['EntidadRazonSocial'];
                        }
                    }
                    /***************************************/
                    //Se generan datos
                    $WSP_encryptedId      = $this->Codification->simpleEncode($crud['idExistencia'], 8080);
                    $Config['Token']      = $UserData["Config_WhatsappToken"];
                    $Config['InstanceId'] = $UserData["Config_WhatsappInstanceId"];
                    $Config['Type']       = 2;
                    $Config['namespace']  = '512f752c_ac4f_45a8_b5b5_2adcfe3ed73a';
                    $Config['template']   = '1tek_alerta_1';
                    $WSP_Body['Phone']    = $this->DataNumbers->normalizarPhone($crud['EntidadFono1']);
                    $WSP_Body['Entidad']  = $EntidadWsp;
                    $WSP_Body['Mensaje']  = $_POST['Mensaje'];
                    $WSP_Body['Link']     = ConfigAPP::SOFTWARE["URL"].'/partida/'.$WSP_encryptedId;

                    /***************************************/
                    //Se envia notificacion
                    $Result = $this->Notifications->sendWhatsappTemplate($Config, $WSP_Body);

                    /***************************************/
                    //si se envia correctamente
                    if($Result['success']===true){
                        $UpdateID .= ','.$crud['idExistencia'];
                        $Count++;
                    }
                }
                /***************************************/
                //Si se enviaron mensajes
                if($Count!=0){
                    /***************************************/
                    //si se envia correctamente se actualizan registros
                    //Se arma la query
                    $ActionSQL = 'UPDATE `campanas_listado_partidas` SET `idEstadoPartida`=2 WHERE idExistencia IN ('.$UpdateID.')';
                    /******************************************/
                    //Ejecuto la query
                    //Se ejecuta la query
                    $xParams = ['query' => $ActionSQL];
                    $this->Base_queryExecute($xParams);

                    /***************************************/
                    // Devuelvo true con código 200 (OK)
                    echo Response::sendData(200, true);
                }else{
                    // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
                    echo Response::sendData(500, 'No se enviaron datos');
                }
            /*******************************************************************/
            //si no hay resultados
            } else {
                echo Response::sendData(500, 'No hay datos filtrados');
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

            /******************************************/
            //Se parsean los datos
            parse_str(file_get_contents("php://input"),$dataDelete);

            /*******************************************************/
            /*                      CONSULTAS                      */
            /*******************************************************/
            /******************************************/
            //Datos de la partida
            $query = [
                'data'    => 'idCampana,idEstadoPartida,idFacturacion',
                'table'   => 'campanas_listado_partidas',
                'join'    => '',
                'where'   => 'idExistencia = "'.$this->Codification->encryptDecrypt('decrypt', $dataDelete['idExistencia']).'"',
                'group'   => '',
                'having'  => '',
                'order'   => ''
            ];
            //Ejecuto la query
            $xParams    = ['query' => $query];
            $rowCampana = $this->Base_GetByID($xParams);

            /******************************************/
            //Dependiendo del estado se borran los datos
            switch ($rowCampana['idEstadoPartida']) {
                /*********************************
                1 - Partida Creada
                2 - Partida Enviada
                3 - Partida Revisada
                4 - Partida Confirmada
                5 - Partida Rechazada */
                case 1:
                case 2:
                case 3:
                case 4:
                case 5:
                    /*******************************************************/
                    /*                 ELIMINACION DATOS                   */
                    /*******************************************************/
                    //Se borran los datos
                    $ActionSQL = 'DELETE FROM `campanas_listado_partidas` WHERE idExistencia = "'.$this->Codification->encryptDecrypt('decrypt', $dataDelete['idExistencia']).'"';
                    //Se ejecuta la query
                    $xParams = ['query' => $ActionSQL];
                    $this->Base_queryExecute($xParams);
                    //Se borran los datos
                    $ActionSQL = 'DELETE FROM `campanas_listado_partidas_productos` WHERE idExistencia = "'.$this->Codification->encryptDecrypt('decrypt', $dataDelete['idExistencia']).'"';
                    //Se ejecuta la query
                    $xParams = ['query' => $ActionSQL];
                    $this->Base_queryExecute($xParams);
                    break;
                /*********************************
                6 - Partida Entregada */
                case 6:
                    /*******************************************************/
                    /*                 ELIMINACION DATOS                   */
                    /*******************************************************/
                    //Se borran los datos
                    $ActionSQL = 'DELETE FROM `campanas_listado_partidas` WHERE idExistencia = "'.$this->Codification->encryptDecrypt('decrypt', $dataDelete['idExistencia']).'"';
                    //Se ejecuta la query
                    $xParams = ['query' => $ActionSQL];
                    $this->Base_queryExecute($xParams);
                    //Se borran los datos
                    $ActionSQL = 'DELETE FROM `campanas_listado_partidas_productos` WHERE idExistencia = "'.$this->Codification->encryptDecrypt('decrypt', $dataDelete['idExistencia']).'"';
                    //Se ejecuta la query
                    $xParams = ['query' => $ActionSQL];
                    $this->Base_queryExecute($xParams);
                    //Verifico su existencia
                    if(isset($rowCampana['idFacturacion'])&&$rowCampana['idFacturacion']!=''){
                        /*******************************************************/
                        /*                     CONSULTAS                       */
                        /*******************************************************/
                        //Datos de los productos
                        $query = [
                            'data'    => 'idBodegas,idProducto,Number',
                            'table'   => 'facturacion_listado_productos',
                            'join'    => '',
                            'where'   => 'idFacturacion = "'.$rowCampana['idFacturacion'].'"',
                            'group'   => '',
                            'having'  => '',
                            'order'   => 'idProducto ASC',
                            'limit'   => ConfigAPP::APP["N_MaxItems"]
                        ];
                        //Ejecuto la query
                        $xParams      = ['query' => $query];
                        $arrProductos = $this->Base_GetList($xParams);

                        /******************************************/
                        //Datos de la partida
                        $query = [
                            'data'    => 'idMovimiento',
                            'table'   => 'bodegas_movimientos',
                            'join'    => '',
                            'where'   => 'idFacturacion = "'.$rowCampana['idFacturacion'].'"',
                            'group'   => '',
                            'having'  => '',
                            'order'   => ''
                        ];
                        //Ejecuto la query
                        $xParams       = ['query' => $query];
                        $rowMovimiento = $this->Base_GetByID($xParams);

                        /******************************************/
                        //Stocks
                        /******************************/
                        //Variables
                        $chainx        = '0';
                        $arrProdStock  = array();
                        $BodegasID     = 0;
                        /******************************/
                        //Se recorren los productos
                        foreach($arrProductos AS $crud){
                            $chainx    .= ','.$crud['idProducto'];
                            $BodegasID  = $crud['idBodegas'];
                        }
                        /******************************/
                        //Se consultan los stocks
                        $query = [
                            'data'    => 'idStocks,idProducto,Cantidad_idBodegas_'.$BodegasID.' AS Cantidad',
                            'table'   => 'bodegas_productos_stocks',
                            'join'    => '',
                            'where'   => 'idProducto IN ('.$chainx.')',
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
                        foreach($arrStocks as $crud){
                            $arrProdStock[$crud['idProducto']]['idStocks'] = $crud['idStocks'];
                            $arrProdStock[$crud['idProducto']]['Cantidad'] = $crud['Cantidad'];
                        }

                        /*******************************************************/
                        /*                 ELIMINACION DATOS                   */
                        /*******************************************************/
                        $arrTableDel  = array();
                        $arrTableDel[] = ['table' => 'DELETE FROM `facturacion_listado` WHERE idFacturacion = "'.$rowCampana['idFacturacion'].'"'];
                        $arrTableDel[] = ['table' => 'DELETE FROM `facturacion_listado_productos` WHERE idFacturacion = "'.$rowCampana['idFacturacion'].'"'];
                        $arrTableDel[] = ['table' => 'DELETE FROM `facturacion_listado_pagos` WHERE idFacturacion = "'.$rowCampana['idFacturacion'].'"'];
                        $arrTableDel[] = ['table' => 'DELETE FROM `bodegas_movimientos` WHERE idMovimiento = "'.$rowMovimiento['idMovimiento'].'"'];
                        $arrTableDel[] = ['table' => 'DELETE FROM `bodegas_movimientos_productos` WHERE idMovimiento = "'.$rowMovimiento['idMovimiento'].'"'];

                        /************************************************/
                        //Verifico si existe
                        if($arrTableDel){
                            //recorro
                            foreach ($arrTableDel as $ActionSQL) {
                                //Se ejecuta la query
                                $xParams = ['query' => $ActionSQL];
                                $this->Base_queryExecute($xParams);
                            }
                        }

                        /*******************************************************/
                        /*                ACTUALIZACION DATOS                  */
                        /*******************************************************/
                        /******************************/
                        //Se recorren los productos
                        foreach($arrProductos as $crud){
                            /******************************/
                            //Se suma la cantidad
                            $NewCantidad = $arrProdStock[$crud['idProducto']]['Cantidad'] + $crud['Number'];
                            /******************************/
                            //Se Actualizan los stocks
                            //verifico si existe el dato en el stock
                            if(isset($arrProdStock[$crud['idProducto']]['idStocks'])&&$arrProdStock[$crud['idProducto']]['idStocks']!=''){
                                /******************************/
                                //Se agrega respuesta
                                $arrTareas = [
                                    'idStocks'                               => $arrProdStock[$crud['idProducto']]['idStocks'],
                                    'Cantidad_idBodegas_'.$crud['idBodegas'] => $NewCantidad,
                                ];
                                /******************************/
                                //Se genera la query
                                $query = [
                                    'data'      => 'idStocks,Cantidad_idBodegas_'.$crud['idBodegas'],
                                    'required'  => 'idStocks,Cantidad_idBodegas_'.$crud['idBodegas'],
                                    'unique'    => '',
                                    'encode'    => '',
                                    'table'     => 'bodegas_productos_stocks',
                                    'where'     => 'idStocks',
                                    'Post'      => $arrTareas
                                ];
                                //Ejecuto la query
                                $xParams = ['DataCheck' => '', 'query' => $query];
                                $this->Base_update($xParams);
                            }
                        }
                    }

                    break;
            }
            //Se actualizan los datos del costo
		    $gestionCampanas   = new gestionCampanas();
            $gestionCampanas->updateCostos(2, $rowCampana['idCampana']);

            /******************************************/
            // Devuelvo true con código 200 (OK)
            echo Response::sendData(200, true);
        }else {
            // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
            echo Response::sendData(500, "Error en el Request Method");
        }
    }

    /******************************************************************************/
    //Borrar dato y archivos
    public function DeleteMassive(){
        //Verificacion metodo PUT
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

            /******************************************/
            //Se parsean los datos
            parse_str(file_get_contents("php://input"),$dataDelete);

            /******************************************/
            //Se genera la query
            $query = [
                'data'    => 'idCampana',
                'table'   => 'campanas_listado_partidas',
                'join'    => '',
                'where'   => 'idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $dataDelete['idCampana']).'"',
                'group'   => '',
                'having'  => '',
                'order'   => ''
            ];
            //Ejecuto la query
            $xParams    = ['query' => $query];
            $rowCampana = $this->Base_GetByID($xParams);

            /*******************************************************/
            /*      SE ELIMINAN LOS PRODUCTOS EN LAS PARTIDAS      */
            /*******************************************************/
            /******************************/
            //Se genera la query
            $query = [
                'data'    => 'idExistencia',
                'table'   => 'campanas_listado_partidas',
                'join'    => '',
                'where'   => 'Fecha = "'.$this->Codification->encryptDecrypt('decrypt', $dataDelete['Fecha']).'" AND idEstadoPartida = "'.$this->Codification->encryptDecrypt('decrypt', $dataDelete['idEstadoPartida']).'"',
                'group'   => '',
                'having'  => '',
                'order'   => 'idExistencia ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            //Ejecuto la query
            $xParams     = ['query' => $query];
            $arrPermisos = $this->Base_GetList($xParams);
            /******************************/
            //Se arma la query
            $ActionSQL = 'DELETE FROM `campanas_listado_partidas_productos` WHERE idExistencia IN (0';
            //Se genera la consulta
            if (!empty($arrPermisos)) {$ActionSQL .= ',' . implode(',', array_column($arrPermisos, 'idExistencia'));}
            $ActionSQL .= ')';
            /******************************************/
            //Se ejecuta la query
            $xParams = ['query' => $ActionSQL];
            $this->Base_queryExecute($xParams);

            /*******************************************************/
            /*              SE ELIMINAN LAS PARTIDAS               */
            /*******************************************************/
            /******************************/
            //Se genera la query
            $query = [
                'files'       => '',
                'table'       => 'campanas_listado_partidas',
                'where'       => 'Fecha,idEstadoPartida',
                'SubCarpeta'  => '',
                'Post'        => $dataDelete
            ];
            //Ejecuto la query
            $xParams  = ['query' => $query];
            $Response = $this->Base_delete($xParams);

            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response===true) {
                /******************************************/
                //Se actualizan los datos del costo
                $gestionCampanas = new gestionCampanas();
                $gestionCampanas->updateCostos(2, $rowCampana['idCampana']);
                /******************************************/
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
            'Post'                      => $POST,
        ];
        //Devuelvo
        return $DataChecking;
    }

    /******************************************************************************/
    /*                             EJECUCION OTROS                                */
    /******************************************************************************/
    /******************************************************************************/
    //Se crea movimiento de bodega
    private function createMovBodega($PostData, $FacturacionID, $nProds, $nProdsNew){
        /******************************/
        //Se agrega respuesta
        $arrTareas = [
            'idEstadoIngreso'  => $PostData['idTipo'],
            'idBodegasIngreso' => (isset($PostData['idBodegasIngreso']) && $PostData['idBodegasIngreso'] !== '' ? $PostData['idBodegasIngreso'] : ''),
            'idBodegasEgreso'  => (isset($PostData['idBodegasEgreso']) && $PostData['idBodegasEgreso'] !== '' ? $PostData['idBodegasEgreso'] : ''),
            'Creacion_fecha'   => $PostData['Creacion_fecha'],
            'Creacion_hora'    => $PostData['Creacion_hora'],
            'Observaciones'    => 'Movimiento generado desde una facturacion',
            'fecha_auto'       => $PostData['fecha_auto'],
            'idUsuario'        => $PostData['idUsuario'],
            'idFacturacion'    => $FacturacionID,
        ];
        /******************************/
        //Se genera la query
        $query = [
            'data'      => 'idEstadoIngreso,idBodegasIngreso,idBodegasEgreso,Creacion_fecha,Creacion_hora,Observaciones,fecha_auto,idUsuario,idFacturacion',
            'required'  => 'idEstadoIngreso,Creacion_fecha,Creacion_hora,fecha_auto,idUsuario',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'bodegas_movimientos',
            'Post'      => $arrTareas
        ];
        //Ejecuto la query
        $xParams            = ['DataCheck' => '', 'query' => $query];
        $ResponseMovimiento = $this->Base_insert($xParams);

        /******************************/
        // Se asume que $Response contendrá un array de errores/datos, un ID numérico o algún otro valor.
        if (is_numeric($ResponseMovimiento)) {
            /*******************************************************/
            //Variable
            $chainx_1      = '';
            $chainx_2      = '0';
            $arrProdStock  = array();
            $BodegasID     = $PostData['idBodegasEgreso'];
            /*******************************************************/
            //se obtiene el producto
            $chainx_1 .= ',Cantidad_idBodegas_'.$BodegasID.' AS Cantidad';
            //Se recorren los productos
            for($j1 = 0; $j1 < $nProds; $j1++){
                $chainx_2 .= ','.$PostData['Producto_idProducto'][$j1];
            }
            //Se recorren los productos agregados despues
            for($j1 = 0; $j1 < $nProdsNew; $j1++){
                $chainx_2 .= ','.$PostData['NewProducto_idProducto'][$j1];
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
                $arrProdStock[$crud['idProducto']]['Cantidad']   = $crud['Cantidad'];
            }

            /******************************/
            //Se recorren los productos
            for($j1 = 0; $j1 < $nProds; $j1++){
                /******************************/
                //Variables
                $ProductoID       = $_POST['Producto_idProducto'][$j1];
                $ProductoCantidad = $_POST['Producto_Cantidad'][$j1];
                /******************************/
                //Se Actualizan los stocks
                //verifico si existe el dato en el stock
                if(isset($arrProdStock[$ProductoID]['idStocks'])&&$arrProdStock[$ProductoID]['idStocks']!=''){
                    $Cantidad_idBodegas = $arrProdStock[$ProductoID]['Cantidad'] - $ProductoCantidad;
                }else{
                    $Cantidad_idBodegas = 0 - $ProductoCantidad;
                }

                /******************************/
                //Se agrega respuesta
                $arrTareas = [
                    'idMovimiento'    => $ResponseMovimiento,
                    'idEstadoIngreso' => $PostData['idTipo'],
                    'idBodegas'       => $BodegasID,
                    'idProducto'      => $ProductoID,
                    'Number'          => $ProductoCantidad,
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
                if(isset($arrProdStock[$ProductoID]['idStocks'])&&$arrProdStock[$ProductoID]['idStocks']!=''){
                    /******************************/
                    //Se agrega respuesta
                    $arrTareas = [
                        'idStocks'                       => $arrProdStock[$ProductoID]['idStocks'],
                        'Cantidad_idBodegas_'.$BodegasID => $Cantidad_idBodegas,
                    ];
                    /******************************/
                    //Se genera la query
                    $query = [
                        'data'      => 'idStocks,Cantidad_idBodegas_'.$BodegasID,
                        'required'  => 'idStocks,Cantidad_idBodegas_'.$BodegasID,
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
                        'idProducto'                     => $ProductoID,
                        'Cantidad_idBodegas_'.$BodegasID => $Cantidad_idBodegas,
                    ];
                    /******************************/
                    //Se genera la query
                    $query = [
                        'data'      => 'idProducto,Cantidad_idBodegas_'.$BodegasID,
                        'required'  => 'idProducto,Cantidad_idBodegas_'.$BodegasID,
                        'unique'    => '',
                        'encode'    => '',
                        'table'     => 'bodegas_productos_stocks',
                        'Post'      => $arrTareas
                    ];
                    //Ejecuto la query
                    $xParams = ['DataCheck' => '', 'query' => $query];
                    $this->Base_insert($xParams);

                }
            }

            /******************************/
            //Se recorren los productos
            for($j1 = 0; $j1 < $nProdsNew; $j1++){
                /******************************/
                //Variables
                $ProductoID       = $_POST['NewProducto_idProducto'][$j1];
                $ProductoCantidad = $_POST['NewProducto_Cantidad'][$j1];
                /******************************/
                //Se Actualizan los stocks
                //verifico si existe el dato en el stock
                if(isset($arrProdStock[$ProductoID]['idStocks'])&&$arrProdStock[$ProductoID]['idStocks']!=''){
                    $Cantidad_idBodegas = $arrProdStock[$ProductoID]['Cantidad'] - $ProductoCantidad;
                }else{
                    $Cantidad_idBodegas = 0 - $ProductoCantidad;
                }

                /******************************/
                //Se agrega respuesta
                $arrTareas = [
                    'idMovimiento'    => $ResponseMovimiento,
                    'idEstadoIngreso' => $PostData['idTipo'],
                    'idBodegas'       => $BodegasID,
                    'idProducto'      => $ProductoID,
                    'Number'          => $ProductoCantidad,
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
                if(isset($arrProdStock[$ProductoID]['idStocks'])&&$arrProdStock[$ProductoID]['idStocks']!=''){
                    /******************************/
                    //Se agrega respuesta
                    $arrTareas = [
                        'idStocks'                       => $arrProdStock[$ProductoID]['idStocks'],
                        'Cantidad_idBodegas_'.$BodegasID => $Cantidad_idBodegas,
                    ];
                    /******************************/
                    //Se genera la query
                    $query = [
                        'data'      => 'idStocks,Cantidad_idBodegas_'.$BodegasID,
                        'required'  => 'idStocks,Cantidad_idBodegas_'.$BodegasID,
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
                        'idProducto'                     => $ProductoID,
                        'Cantidad_idBodegas_'.$BodegasID => $Cantidad_idBodegas,
                    ];
                    /******************************/
                    //Se genera la query
                    $query = [
                        'data'      => 'idProducto,Cantidad_idBodegas_'.$BodegasID,
                        'required'  => 'idProducto,Cantidad_idBodegas_'.$BodegasID,
                        'unique'    => '',
                        'encode'    => '',
                        'table'     => 'bodegas_productos_stocks',
                        'Post'      => $arrTareas
                    ];
                    //Ejecuto la query
                    $xParams = ['DataCheck' => '', 'query' => $query];
                    $this->Base_insert($xParams);
                }
            }
        }
    }


}
