<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class archivosListado extends ControllerFiles {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*================== Instancias =================*/
        $this->controllerName = 'archivosListado';
        /*========== Datos para la clase padre ==========*/
        parent::__construct();
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /*******************************************************************/
    // Listar
    /*******************************************************************/
    public function listAll($f3){

        /************************************/
        // Se concede el nivel de acceso para este módulo (ámbito 'archivosListado').
        // Es el punto de ORIGEN de la autorización: aquí es donde se conoce el nivel real
        // del usuario. Los endpoints genéricos /core/fileExplorer/* solo validan esta
        // concesión (ScopeAccess::check) y nunca confían en un nivel enviado por el cliente.
        /************************************/
        ScopeAccess::grant($f3, $this->controllerName, $this->getArrLevel($f3, $this->controllerName)['LevelAccess'] ?? 0);

        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Explorador Archivos',
            'PageDescription' => 'Explorador Archivos',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
            /*===========   Funcionalidad   ===========*/
            'Fnc_WidgetsCommon'    => new UIWidgetsCommon(),
        ];

        /************************************/
        // Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');

    }

}
