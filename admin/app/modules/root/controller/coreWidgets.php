<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class coreWidgets extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;
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
		$this->FormInputs     = new UIFormInputs();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    public function box($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Widgets - Box',
            'PageDescription' => 'Widgets - Box',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreWidgets-box.php');
    }

    /******************************************************************************/
    public function timeLine($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Widgets - Time Line',
            'PageDescription' => 'Widgets - Time Line',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreWidgets-timeLine.php');
    }

    /******************************************************************************/
    public function textDividers($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Widgets - Text Divider',
            'PageDescription' => 'Widgets - Text Divider',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreWidgets-textDividers.php');
    }

    /******************************************************************************/
    public function dividers($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Widgets - Divider',
            'PageDescription' => 'Widgets - Divider',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreWidgets-dividers.php');
    }

    /******************************************************************************/
    public function components($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Widgets - Componentes Web',
            'PageDescription' => 'Widgets - Componentes Web',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
            /*===========   Funcionalidad   ===========*/
            'Fnc_WidgetsCommon'    => new UIWidgetsCommon(),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreWidgets-components.php');
    }

    /******************************************************************************/
    public function calendar($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Widgets - Calendario',
            'PageDescription' => 'Widgets - Calendario',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
            /*===========   Funcionalidad   ===========*/
            'Fnc_FormInputs'    => $this->FormInputs,
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreWidgets-calendar.php');
    }

    /******************************************************************************/
    public function treeview($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Widgets - Treeview',
            'PageDescription' => 'Widgets - Treeview',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
            /*===========   Funcionalidad   ===========*/
            'Fnc_FormInputs'    => $this->FormInputs,
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreWidgets-treeview.php');
    }

    /******************************************************************************/
    public function codeVisor($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Widgets - Visor de Codigo',
            'PageDescription' => 'Widgets - Visor de Codigo',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
            /*===========   Funcionalidad   ===========*/
            'Fnc_WidgetsCommon'    => new UIWidgetsCommon(),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreWidgets-codeVisor.php');
    }

    /******************************************************************************/
    public function meteo($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Widgets - Widget Meteorologico',
            'PageDescription' => 'Widgets - Widget Meteorologico',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
            /*===========   Funcionalidad   ===========*/
            'Fnc_WidgetsCommon'    => new UIWidgetsCommon(),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreWidgets-meteo.php');
    }

    /******************************************************************************/
    public function feed($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Widgets - Feed de noticias',
            'PageDescription' => 'Widgets - Feed de noticias',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
            /*===========   Funcionalidad   ===========*/
            'Fnc_WidgetsCommon'    => new UIWidgetsCommon(),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreWidgets-feed.php');
    }

    /******************************************************************************/
    public function radio($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Widgets - Radio Player',
            'PageDescription' => 'Widgets - Radio Player',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
            /*===========   Funcionalidad   ===========*/
            'Fnc_WidgetsCommon'    => new UIWidgetsCommon(),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreWidgets-radio.php');
    }

    /******************************************************************************/
    public function fileExplorer($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Widgets - Explorador Archivos',
            'PageDescription' => 'Widgets - Explorador Archivos',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
            /*===========   Funcionalidad   ===========*/
            'Fnc_WidgetsCommon'    => new UIWidgetsCommon(),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreWidgets-fileExplorer.php');
    }

}
