<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class coreFormularios extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;
    private $DBConn;
    private $QBuilder;
    private $FormInputs;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_ADMIN);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'Empty';
        $this->DBConn         = $DB_conn_1;
        $this->QBuilder       = $queryBuilder;
		$this->FormInputs     = new UIFormInputs();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //pantalla principal
    public function Formularios($f3){
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
        $arrCiudad = $this->QBuilder->queryArray($query, $this->DBConn);

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
        $arrComuna = $this->QBuilder->queryArray($query, $this->DBConn);

        /******************************/
        //Se genera la query
        $query = [
            'data'    => '
                core_ubicacion_comunas.idComuna AS ID1,
                core_ubicacion_comunas.idCiudad AS ID2,
                core_ubicacion_comunas.Nombre AS Nombre1,
                core_ubicacion_ciudad.Nombre AS Nombre2',
            'table'   => 'core_ubicacion_comunas',
            'join'    => 'LEFT JOIN core_ubicacion_ciudad ON core_ubicacion_ciudad.idCiudad = core_ubicacion_comunas.idCiudad',
            'where'   => '',
            'group'   => '',
            'having'  => '',
            'order'   => 'core_ubicacion_ciudad.Nombre ASC, core_ubicacion_comunas.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $arrGroup = $this->QBuilder->queryArray($query, $this->DBConn);

        /******************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Formularios - Inputs',
            'PageDescription' => 'Formularios - Inputs',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
            /*===========   Funcionalidad   ===========*/
            'Fnc_FormInputs'  => $this->FormInputs,
            /*=========== Datos Consultados ===========*/
            'arrCiudad'       => $arrCiudad['data'],
            'arrComuna'       => $arrComuna['data'],
            'arrGroup'        => $arrGroup['data'],
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreFormularios-form.php');
    }

}
