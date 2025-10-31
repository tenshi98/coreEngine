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

        //Se instancia la vista
        $view = new View;
        echo $view->render('../app/templates/guest-header.php');                            // Header
        echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/main-login.php'); // Vista
        echo $view->render('../app/templates/guest-footer.php');                            // Footer
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

        /*******************************************************************/
        //Variables
        $MainViewData = [
            'Count_Campanas'        => 0,
            'Count_DocMercantiles'  => 0,
            'Count_Bodegas'         => 0,
        ];
        //Se asignan datos a buscar
        $menuCounters = [
            'Gestión Campañas' => [
                'Campañas - Listado'   => 'Count_Campanas',
            ],
            'Gestión Documentos Mercantiles' => [
                'Compras'  => 'Count_DocMercantiles',
                'Ventas'   => 'Count_DocMercantiles',
            ],
            'Gestión Bodegas y Productos' => [
                'Stock Productos'   => 'Count_Bodegas',
            ],
        ];
        //Se recorren los permisos y se validan
        foreach ($menuCounters as $section => $names) {
            if (!empty($arrMenu[$section])) {
                foreach ($arrMenu[$section] as $asd) {
                    if (isset($names[$asd['Nombre']])) {
                        $MainViewData[$names[$asd['Nombre']]]++;
                    }
                }
            }
        }

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

        //Se instancia la vista
        $view = new View;
        echo $view->render('../app/templates/user-header.php');                                 // Header
        echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/main-principal.php'); // Vista
        echo $view->render('../app/templates/user-footer.php');                                 // Footer
    }


}