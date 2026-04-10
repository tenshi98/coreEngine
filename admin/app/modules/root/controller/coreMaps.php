<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class coreMaps extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;
    private $WidgetsMaps;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_ADMIN);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'Empty';
		$this->WidgetsMaps    = new UIWidgetsMaps();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //pantalla principal
    public function googleMaps($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Mapas - Google Maps',
            'PageDescription' => 'Mapas - Google Maps',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========   Funcionalidad   ===========*/
            'Fnc_WidgetsMaps'  => $this->WidgetsMaps,
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreMaps-googleMaps.php');
    }

    /******************************************************************************/
    //pantalla principal
    public function leafletMaps($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Mapas - Leaflet Maps',
            'PageDescription' => 'Mapas - Leaflet Maps',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========   Funcionalidad   ===========*/
            'Fnc_WidgetsMaps'  => $this->WidgetsMaps,
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreMaps-leafletMaps.php');
    }


}
