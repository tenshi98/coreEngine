<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class coreGraficos extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'Empty';
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //pantalla principal
    public function apexcharts($f3){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Gráficos - Apexcharts',
            'PageDescription' => 'Gráficos - Apexcharts',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $UserData,
            'UserAccess'    => $arrLevel[$this->controllerName],
        ];


        //Se instancia la vista
        $view = new View;
        echo $view->render('../app/templates/user-header.php');                                            // Header
        echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/coreGraficos-apexcharts.php');   // Vista
        echo $view->render('../app/templates/user-footer.php');                                            // Footer
    }

    /******************************************************************************/
    //pantalla principal
    public function chartjs($f3){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Gráficos - Chartjs',
            'PageDescription' => 'Gráficos - Chartjs',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $UserData,
            'UserAccess'    => $arrLevel[$this->controllerName],
        ];


        //Se instancia la vista
        $view = new View;
        echo $view->render('../app/templates/user-header.php');                                         // Header
        echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/coreGraficos-chartjs.php');   // Vista
        echo $view->render('../app/templates/user-footer.php');                                         // Footer
    }

    /******************************************************************************/
    //pantalla principal
    public function echarts($f3){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Gráficos - Echarts',
            'PageDescription' => 'Gráficos - Echarts',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $UserData,
            'UserAccess'    => $arrLevel[$this->controllerName],
        ];


        //Se instancia la vista
        $view = new View;
        echo $view->render('../app/templates/user-header.php');                                         // Header
        echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/coreGraficos-echarts.php');   // Vista
        echo $view->render('../app/templates/user-footer.php');                                         // Footer
    }


}
