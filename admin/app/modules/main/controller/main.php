<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class main extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $ServerServer;
    private $DataDate;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
		$this->ServerServer = new FunctionsServerServer();
		$this->DataDate     = new FunctionsDataDate();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //Vista - Login
    public function login($f3){

        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Iniciar Sesión',
            'PageDescription' => 'Iniciar Sesión',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],

        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, 0, $this->returnRutaVista(__DIR__, 'app').'/main-login.php');
    }

    /******************************************************************************/
    //Recuperar Contraseña
    public function error404($f3){

        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Página de error',
            'PageDescription' => 'Página de error',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
        ];

        //Se instancia la vista
        $view = new View;
        echo $view->render('../app/templates/pages-error404.php'); // Header
    }

    /******************************************************************************/
    //pantalla principal
    public function principal($f3){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrMenu  = $f3->get('SESSION.arrMenu');

        /******************************************/
        //Variable vacia
        $MainViewData = [];
        $menuCounters = [];

        //Arreglo con los controladores con widgets, considerar que desde aqui se ordenan
        $array = $this->arrayWidgetViews();
        /******************************************/
        //Verifico si existe
        if($array){
            //recorro
            foreach ($array as $data) {
                //Se genera la query
                $loadWidgets = method_exists($data, 'loadWidgets');
                //si el metodo existe
                if($loadWidgets===true){
                    $ControllerData = new $data;
                    $arrModules     = $ControllerData->loadWidgets();
                    //Permisos
                    $menuCounters[$arrModules['Menu_Name']] = $arrModules['Menu_Value'];
                }
            }
        }

        //Se recorren los permisos y se validan
        foreach ($menuCounters as $section => $names) {
            //Verifico si existen datos del menu
            if (!empty($arrMenu[$section])) {
                //Recorro el menu
                foreach ($arrMenu[$section] as $asd) {
                    if (isset($names[$asd['Nombre']])) {
                        $MainViewData[] = $names[$asd['Nombre']]; //Se guardan las URL
                    }
                }
            }
        }

        //Se filtran para obtener datos unicos
        $MainViewData = array_values(array_unique($MainViewData));

        /******************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Principal',
            'PageDescription' => 'Principal',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'        => $UserData,
            /*===========   Funcionalidad   ===========*/
            'Fnc_ServerServer'  => $this->ServerServer,
            'Fnc_DataDate'      => $this->DataDate,
            /*=========== Datos Consultados ===========*/
            'MainViewData'    => $MainViewData,
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista($UserData['TypeSession'], 1, $this->returnRutaVista(__DIR__, 'app').'/main-principal.php');
    }
    /******************************************************************************/
    //Se listan los controladores
    public function arrayWidgetViews(){

        /*******************************************************/
        //Rutas
        $array = array(
            "gestionDocumentosWidgets",
            "bodegasWidgets",
            "gestionCampanasWidgets",
        );

        //devuelvo
        return $array;
    }


}
