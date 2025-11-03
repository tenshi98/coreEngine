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
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
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
    //pantalla principal
    public function box($f3){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Widgets - Box',
            'PageDescription' => 'Widgets - Box',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $UserData,
            'UserAccess'    => $arrLevel[$this->controllerName],
        ];


        //Se instancia la vista
        $view = new View;
        echo $view->render('../app/templates/user-header.php');                                         // Header
        echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/coreWidgets-box.php');    // Vista
        echo $view->render('../app/templates/user-footer.php');                                         // Footer
    }

    /******************************************************************************/
    //pantalla principal
    public function timeLine($f3){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Widgets - Time Line',
            'PageDescription' => 'Widgets - Time Line',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $UserData,
            'UserAccess'    => $arrLevel[$this->controllerName],
        ];


        //Se instancia la vista
        $view = new View;
        echo $view->render('../app/templates/user-header.php');                                         // Header
        echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/coreWidgets-timeLine.php');    // Vista
        echo $view->render('../app/templates/user-footer.php');                                         // Footer
    }

    /******************************************************************************/
    //pantalla principal
    public function textDividers($f3){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Widgets - Text Divider',
            'PageDescription' => 'Widgets - Text Divider',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $UserData,
            'UserAccess'    => $arrLevel[$this->controllerName],
        ];


        //Se instancia la vista
        $view = new View;
        echo $view->render('../app/templates/user-header.php');                                         // Header
        echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/coreWidgets-textDividers.php');    // Vista
        echo $view->render('../app/templates/user-footer.php');                                         // Footer
    }

    /******************************************************************************/
    //pantalla principal
    public function components($f3){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Widgets - Componentes Web',
            'PageDescription' => 'Widgets - Componentes Web',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $UserData,
            'UserAccess'    => $arrLevel[$this->controllerName],
        ];


        //Se instancia la vista
        $view = new View;
        echo $view->render('../app/templates/user-header.php');                                         // Header
        echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/coreWidgets-components.php');    // Vista
        echo $view->render('../app/templates/user-footer.php');                                         // Footer
    }

    /******************************************************************************/
    //pantalla principal
    public function calendar($f3){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Widgets - Calendario',
            'PageDescription' => 'Widgets - Calendario',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $UserData,
            'UserAccess'    => $arrLevel[$this->controllerName],
            /*===========   Funcionalidad   ===========*/
            'Fnc_FormInputs'    => $this->FormInputs,
        ];


        //Se instancia la vista
        $view = new View;
        echo $view->render('../app/templates/user-header.php');                                         // Header
        echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/coreWidgets-calendar.php');   // Vista
        echo $view->render('../app/templates/user-footer.php');                                         // Footer
    }

}
