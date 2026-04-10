<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class coreTablas extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_ADMIN);
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
    public function normal($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Tablas - Normales',
            'PageDescription' => 'Tablas - Normales',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreTablas-normal.php');
    }

    /******************************************************************************/
    //pantalla principal
    public function dataTables($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Tablas - dataTables',
            'PageDescription' => 'Tablas - dataTables',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreTablas-dataTables.php');
    }



}
