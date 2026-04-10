<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class coreComponentes extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;
    private $WidgetsCommon;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_ADMIN);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'Empty';
		$this->WidgetsCommon  = new UIWidgetsCommon();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //pantalla principal
    public function acordeon($f3){
        /******************************************/
        //Datos a utilizar
        $arrData = [
            ['Title' => 'Titulo 1', 'Body' => '<strong>Lorem ipsum dolor sit amet,</strong> consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. <code>Ut enim ad minim veniam,</code> quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',],
            ['Title' => 'Titulo 2', 'Body' => '<strong>Lorem ipsum dolor sit amet,</strong> consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. <code>Ut enim ad minim veniam,</code> quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',],
            ['Title' => 'Titulo 3', 'Body' => '<strong>Lorem ipsum dolor sit amet,</strong> consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. <code>Ut enim ad minim veniam,</code> quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',],
            ['Title' => 'Titulo 4', 'Body' => '<strong>Lorem ipsum dolor sit amet,</strong> consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. <code>Ut enim ad minim veniam,</code> quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',]
        ];

        /******************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Componentes - Acordeon',
            'PageDescription' => 'Componentes - Acordeon',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
            /*===========   Funcionalidad   ===========*/
            'Fnc_WidgetsCommon'   => $this->WidgetsCommon,
            /*=========== Datos Consultados ===========*/
            'arrData'         => $arrData,
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreComponentes-acordeon.php');
    }

    /******************************************************************************/
    //pantalla principal
    public function alertas($f3){
        /******************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Componentes - Alertas',
            'PageDescription' => 'Componentes - Alertas',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
            /*===========   Funcionalidad   ===========*/
            'Fnc_WidgetsCommon'   => $this->WidgetsCommon,
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreComponentes-alertas.php');
    }

    /******************************************************************************/
    //pantalla principal
    public function badges($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Componentes - Badges',
            'PageDescription' => 'Componentes - Badges',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreComponentes-badges.php');
    }

    /******************************************************************************/
    //pantalla principal
    public function breadcrumbs($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Componentes - Breadcrumbs',
            'PageDescription' => 'Componentes - Breadcrumbs',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreComponentes-breadcrumbs.php');
    }

    /******************************************************************************/
    //pantalla principal
    public function buttons($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Componentes - Buttons',
            'PageDescription' => 'Componentes - Buttons',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreComponentes-buttons.php');
    }

    /******************************************************************************/
    //pantalla principal
    public function cards($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Componentes - Cards',
            'PageDescription' => 'Componentes - Cards',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreComponentes-cards.php');
    }

    /******************************************************************************/
    //pantalla principal
    public function carousel($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Componentes - Carousel',
            'PageDescription' => 'Componentes - Carousel',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreComponentes-carousel.php');
    }

    /******************************************************************************/
    //pantalla principal
    public function colors($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Componentes - Colors',
            'PageDescription' => 'Componentes - Colors',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreComponentes-colors.php');
    }

    /******************************************************************************/
    //pantalla principal
    public function icons($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Componentes - Icons',
            'PageDescription' => 'Componentes - Icons',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreComponentes-icons.php');
    }

    /******************************************************************************/
    //pantalla principal
    public function listgroup($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Componentes - Listgroup',
            'PageDescription' => 'Componentes - Listgroup',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreComponentes-listgroup.php');
    }

    /******************************************************************************/
    //pantalla principal
    public function modal($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Componentes - Modal',
            'PageDescription' => 'Componentes - Modal',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreComponentes-modal.php');
    }

    /******************************************************************************/
    //pantalla principal
    public function pagination($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Componentes - Pagination',
            'PageDescription' => 'Componentes - Pagination',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreComponentes-pagination.php');
    }

    /******************************************************************************/
    //pantalla principal
    public function popovers($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Componentes - Popovers',
            'PageDescription' => 'Componentes - Popovers',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreComponentes-popovers.php');
    }

    /******************************************************************************/
    //pantalla principal
    public function progress($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Componentes - Progress',
            'PageDescription' => 'Componentes - Progress',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreComponentes-progress.php');
    }

    /******************************************************************************/
    //pantalla principal
    public function ribbons($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Componentes - Ribbons',
            'PageDescription' => 'Componentes - Ribbons',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreComponentes-ribbons.php');
    }

    /******************************************************************************/
    //pantalla principal
    public function spinners($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Componentes - Spinners',
            'PageDescription' => 'Componentes - Spinners',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreComponentes-spinners.php');
    }

    /******************************************************************************/
    //pantalla principal
    public function tabs($f3){
        /******************************************/
        //Datos a utilizar
        $arrData = [
            ['Title' => 'Titulo 1', 'Body' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',],
            ['Title' => 'Titulo 2', 'Body' => 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet.',],
            ['Title' => 'Titulo 3', 'Body' => 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. At harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod.',],
        ];

        /******************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Componentes - Tabs',
            'PageDescription' => 'Componentes - Tabs',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
            /*===========   Funcionalidad   ===========*/
            'Fnc_WidgetsCommon'   => $this->WidgetsCommon,
            /*=========== Datos Consultados ===========*/
            'arrData'         => $arrData,
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreComponentes-tabs.php');
    }

    /******************************************************************************/
    //pantalla principal
    public function tooltips($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Componentes - Tooltips',
            'PageDescription' => 'Componentes - Tooltips',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreComponentes-tooltips.php');
    }

}
