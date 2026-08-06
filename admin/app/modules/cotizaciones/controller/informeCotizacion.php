<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class informeCotizacion extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataDate;
    private $DataNumbers;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'informeCotizacion';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->DataDate       = new FunctionsDataDate();
		$this->DataNumbers    = new FunctionsDataNumbers();
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
        //Se genera la query
        $query = [
            'data'    => 'idEntidad AS ID,CONCAT(CASE idTipoEntidad WHEN 1 THEN CONCAT_WS(" ", Nombre, ApellidoPat) WHEN 2 THEN RazonSocial END,IF(Nick IS NULL OR Nick = "","",CONCAT(" (", Nick, ")"))) AS Nombre',
            'table'   => 'entidades_listado',
            'join'    => '',
            'where'   => 'idEstado=1',
            'group'   => '',
            'having'  => '',
            'order'   => 'ApellidoPat ASC,Nombre ASC,RazonSocial ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams      = ['query' => $query];
        $arrEntidades = $this->Base_GetList($xParams);

        /******************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Búsqueda Cotizaciones',
            'PageDescription' => 'Búsqueda Cotizaciones.',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            'TableTitle'      => 'Búsqueda Cotizaciones',
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
            /*===========   Funcionalidad   ===========*/
            'Fnc_FormInputs'   => $this->FormInputs,
            'Fnc_Codification' => $this->Codification,
            /*=========== Datos Consultados ===========*/
            'arrEntidades'    => $arrEntidades['data'],
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
    }

    /******************************************************************************/
    //List
    public function UpdateList($f3){
        /*******************************************************************/
        //Variables
        $WhereData_int     = 'idEntidad';                          //Datos búsqueda exacta
        $WhereData_string  = '';                                   //Datos búsqueda relativa
        $WhereData_between = 'Creacion_fecha-F_Inicio-F_Termino';  //Datos búsqueda Between
        $whereInt          = '';                                   //se crea cadena
        /******************************************/
        //agrego variable busqueda
        $whereInt = $this->searchWhere($whereInt, $WhereData_int, 'cotizacion_listado', 1);
        $whereInt = $this->searchWhere($whereInt, $WhereData_string, 'cotizacion_listado', 2);
        $whereInt = $this->searchWhere($whereInt, $WhereData_between, 'cotizacion_listado', 3);

        /******************************/
        //Se genera la query
        $query = [
            'data'    => '
                cotizacion_listado.idCotizacion,
                cotizacion_listado.Creacion_fecha,
                cotizacion_listado.ValorTotal,

                entidades_listado.idTipoEntidad,
                entidades_listado.Nombre AS EntidadesNombre,
                entidades_listado.ApellidoPat AS EntidadesApellido,
                entidades_listado.RazonSocial AS EntidadesRazonSocial',
            'table'   => 'cotizacion_listado',
            'join'    => 'LEFT JOIN entidades_listado ON entidades_listado.idEntidad = cotizacion_listado.idEntidad',
            'where'   => $whereInt,
            'group'   => '',
            'having'  => '',
            'order'   => 'cotizacion_listado.Creacion_fecha DESC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $arrList = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if($arrList['status']){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'TableTitle'      => 'Búsqueda de Cotizaciones',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'     => $this->Codification,
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'arrList'       => $arrList['data'],
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

}
