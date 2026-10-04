<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class corePaginas extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_ADMIN);
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
    /*******************************************************************/
    // Pagina Error 404
    /*******************************************************************/
    public function error404($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Páginas - Error 404',
            'PageDescription' => 'Páginas - Error 404',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
        ];

        /************************************/
        // Se instancia la vista
        $view     = new View;
        echo $view->render('../app/templates/pages-error404.php'); // Header
    }

    /*******************************************************************/
    // Pagina Error 5xx
    /*******************************************************************/
    public function error5xx($f3){
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Páginas - Error 5xx',
            'PageDescription' => 'Páginas - Error 5xx',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
            /*===========  Detalle del error ===========*/
            //La plantilla user-error.php requiere esta información para
            //mostrar el mensaje (usuario estándar) o la traza (superadmin)
            'dataError'     => [
                'errors' => [
                    'Se ha producido un error interno al procesar la solicitud.',
                    'El sistema no pudo completar la operación solicitada.',
                ],
                'data' => [
                    'HTTP 500 - Internal Server Error',
                    'Origen: Core/Paginas/error5xx',
                    'Detalle: Página de referencia del gestor de errores del sistema.',
                ],
            ],
        ];

        /************************************/
        // Se instancia la vista
        $view     = new View;
        echo $view->render('../app/templates/user-header.php');
        echo $view->render('../app/templates/user-error.php');
        echo $view->render('../app/templates/user-footer.php');
    }


}
