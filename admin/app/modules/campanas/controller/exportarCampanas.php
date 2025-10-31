<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class exportarCampanas extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataNumbers;
    private $DataDate;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'exportarCampanas';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->DataNumbers    = new FunctionsDataNumbers();
		$this->DataDate       = new FunctionsDataDate();
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
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

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
            'data'    => 'idCampana AS ID,CONCAT( Nombre, " Fecha ", Fecha ) AS Nombre',
            'table'   => 'campanas_listado',
            'join'    => '',
            'where'   => 'idCampana!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Fecha DESC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams     = ['query' => $query];
        $arrCampanas = $this->Base_GetList($xParams);

        /******************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Exportar Datos',
            'PageDescription' => 'Exportar Datos.',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            'TableTitle'      => 'Exportar Datos',
            /*===========  Datos del usuario ===========*/
            'UserData'      => $UserData,
            'UserAccess'    => $arrLevel[$this->controllerName],
            /*===========   Funcionalidad   ===========*/
            'Fnc_FormInputs'   => $this->FormInputs,
            'Fnc_Codification' => $this->Codification,
            /*=========== Datos Consultados ===========*/
            'arrUsuarios'    => $arrUsuarios,
            'arrEstados'     => $arrEstados,
            'arrBodegas'     => $arrBodegas,
            'arrDocumentos'  => $arrDocumentos,
            'arrEntidades'   => $arrEntidades,
            'arrCampanas'    => $arrCampanas,
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista($UserData['TypeSession'], 1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
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
        $WhereCamp_int     = 'idCampana,idBodegas,idUsuario,idEstado';             //Datos búsqueda exacta
        $WhereCamp_string  = '';                                                   //Datos búsqueda relativa
        $WhereCamp_between = '';                                                   //Datos búsqueda Between
        $WherePart_int     = 'idDocumentos,idFacturacion,idEntidad';               //Datos búsqueda exacta
        $WherePart_string  = 'N_Doc';                                              //Datos búsqueda relativa
        $WherePart_between = 'Fecha-F_Inicio-F_Termino';                           //Datos búsqueda Between
        $whereInt          = '';                                                   //se crea cadena
        /******************************************/
        //agrego variable busqueda
        $whereInt = $this->searchWhere($whereInt, $WhereCamp_int, 'campanas_listado', 1);
        $whereInt = $this->searchWhere($whereInt, $WhereCamp_string, 'campanas_listado', 2);
        $whereInt = $this->searchWhere($whereInt, $WhereCamp_between, 'campanas_listado', 3);
        $whereInt = $this->searchWhere($whereInt, $WherePart_int, 'campanas_listado_partidas', 1);
        $whereInt = $this->searchWhere($whereInt, $WherePart_string, 'campanas_listado_partidas', 2);
        $whereInt = $this->searchWhere($whereInt, $WherePart_between, 'campanas_listado_partidas', 3);

        /******************************/
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
                bodegas_listado.Nombre AS Bodega,

                campanas_listado_partidas.idFacturacion,
                campanas_listado_partidas.Fecha,

                entidades_listado.Nombre AS EntidadNombre,
                entidades_listado.ApellidoPat AS EntidadApellido,
                entidades_listado.RazonSocial AS EntidadRazonSocial,
                entidades_listado.Nick AS EntidadNick,
                entidades_sectores.Nombre AS EntidadSector,
                entidades_listado.Direccion AS EntidadDireccion,

                core_estados_partida.Nombre AS PartidaEstado,
                core_documentos_mercantiles.Nombre AS DocumentoNombre,
                facturacion_listado.N_Doc AS DocumentoN_Doc,

                campanas_listado_partidas_productos.Cantidad,
                campanas_listado_partidas_productos.Beneficios,
                productos_listado.Nombre AS Producto,
                core_unidades_medida.Nombre AS Unimed',
            'table'   => 'campanas_listado_partidas',
            'join'    => '
                LEFT JOIN campanas_listado                      ON campanas_listado.idCampana                         = campanas_listado_partidas.idCampana
                LEFT JOIN usuarios_listado                      ON usuarios_listado.idUsuario                         = campanas_listado.idUsuario
                LEFT JOIN core_estados_apertura                 ON core_estados_apertura.idEstadoApertura             = campanas_listado.idEstado
                LEFT JOIN bodegas_listado                       ON bodegas_listado.idBodegas                          = campanas_listado.idBodegas
                LEFT JOIN entidades_listado                     ON entidades_listado.idEntidad                        = campanas_listado_partidas.idEntidad
                LEFT JOIN entidades_sectores                    ON entidades_sectores.idSector                        = entidades_listado.idSector
                LEFT JOIN core_estados_partida                  ON core_estados_partida.idEstadoPartida               = campanas_listado_partidas.idEstadoPartida
                LEFT JOIN facturacion_listado                   ON facturacion_listado.idFacturacion                  = campanas_listado_partidas.idFacturacion
                LEFT JOIN core_documentos_mercantiles           ON core_documentos_mercantiles.idDocumentos           = facturacion_listado.idDocumentos
                LEFT JOIN campanas_listado_partidas_productos   ON campanas_listado_partidas_productos.idExistencia   = campanas_listado_partidas.idExistencia
                LEFT JOIN productos_listado                     ON productos_listado.idProducto                       = campanas_listado_partidas_productos.idProducto
                LEFT JOIN core_unidades_medida                  ON core_unidades_medida.idUniMed                      = productos_listado.idUniMed',
            'where'   => $whereInt,
            'group'   => '',
            'having'  => '',
            'order'   => 'campanas_listado.Nombre ASC, campanas_listado_partidas.Fecha ASC, entidades_sectores.Nombre ASC, entidades_listado.Direccion ASC',
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
                'TableTitle'      => 'Datos a exportar',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'    => $this->Codification,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                'Fnc_DataDate'        => $this->DataDate,
                /*=========== Datos Consultados ===========*/
                'arrList'       => $arrList,
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


}