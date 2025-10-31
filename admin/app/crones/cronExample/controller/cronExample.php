<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class cronExample extends ControllerBase {

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    //Vista - Login
    public function listData($f3){

        /******************************************/
        //Variable para las querys
        $arrQuery  = array();

        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos Consultados ===========*/
            'arrQuery' => $arrQuery,
        ];

        //Se instancia la vista
        $view = new View;
        echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/cronExample.php'); // Vista
    }

    /******************************************************************************/
    //Vista - Login
    public function listDataV2($f3){

        /******************************************/
        //Variable para las querys
        $arrQuery  = array();

        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos Consultados ===========*/
            'arrQuery' => $arrQuery,
        ];

        //Se instancia la vista
        $view = new View;
        echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/cronExample.php'); // Vista
    }


}