<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class coreWidgets extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;

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
		$this->FormInputs     = new UIFormInputs();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /*******************************************************************/
    // Cuadros
    /*******************************************************************/
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

        /************************************/
        // Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreWidgets-box.php');
    }

    /*******************************************************************/
    // Lineas de tiempo
    /*******************************************************************/
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

        /************************************/
        // Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreWidgets-timeLine.php');
    }

    /*******************************************************************/
    // Divisores de texto
    /*******************************************************************/
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

        /************************************/
        // Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreWidgets-textDividers.php');
    }

    /*******************************************************************/
    // Divisores
    /*******************************************************************/
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

        /************************************/
        // Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreWidgets-dividers.php');
    }

    /*******************************************************************/
    // Componentes
    /*******************************************************************/
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

        /************************************/
        // Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreWidgets-components.php');
    }

    /*******************************************************************/
    // Calendario
    /*******************************************************************/
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

        /************************************/
        // Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreWidgets-calendar.php');
    }

    /*******************************************************************/
    // Arbol de documentos
    /*******************************************************************/
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

        /************************************/
        // Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreWidgets-treeview.php');
    }

    /*******************************************************************/
    // Visor de codigos
    /*******************************************************************/
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

        /************************************/
        // Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreWidgets-codeVisor.php');
    }

    /*******************************************************************/
    // Meteorologia
    /*******************************************************************/
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

        /************************************/
        // Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreWidgets-meteo.php');
    }

    /*******************************************************************/
    // Feed de noticias
    /*******************************************************************/
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

        /************************************/
        // Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreWidgets-feed.php');
    }

    /*******************************************************************/
    // Radio
    /*******************************************************************/
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

        /************************************/
        // Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreWidgets-radio.php');
    }

    /*******************************************************************/
    // Explorador de archivos
    /*******************************************************************/
    public function fileExplorer($f3){

        /************************************/
        // Se concede el nivel de acceso para esta vista (ámbito 'coreWidgets').
        // Mismo criterio que archivosListado: el origen conoce el nivel real del usuario
        // y lo registra en sesión; los endpoints /core/fileExplorer/* solo lo validan.
        /************************************/
        $scope = ScopeAccess::grant($f3, 'coreWidgets', $this->getArrLevel($f3, $this->controllerName)['LevelAccess'] ?? 0);

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
            /*=========== Ámbito concedido ===========*/
            'AccessScope'   => $scope,
        ];

        /************************************/
        // Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreWidgets-fileExplorer.php');
    }

    /*******************************************************************/
    // Feed de noticias
    /*******************************************************************/
    public function viewsComponents($f3){

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idUsuario,Email,Numero,Rut,Patente,Fecha,Hora,Palabra,Direccion_img,File',
            'table'   => 'core_test_crud',
            'join'    => '',
            'where'   => 'idCrud = ?',
            'params'  => [1],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                core_test_crud_observaciones.Observacion,
                core_test_crud_observaciones.FechaCreacion,
                usuarios_listado.Nombre AS Usuario',
            'table'   => 'core_test_crud_observaciones',
            'join'    => 'LEFT JOIN usuarios_listado ON usuarios_listado.idUsuario = core_test_crud_observaciones.idUsuario',
            'where'   => 'core_test_crud_observaciones.idCrud = ?',
            'params'  => [1],
            'group'   => '',
            'having'  => '',
            'order'   => 'core_test_crud_observaciones.idObservaciones ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams          = ['query' => $query];
        // Ejecuto la query
        $arrObservaciones = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrObservaciones['status']){
            /************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Widgets - Componentes para Vistas',
                'PageDescription' => 'Widgets - Componentes para Vistas',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_WidgetsViews'    => new UIWidgetsViews(),
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
                'arrObservaciones' => $arrObservaciones['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/coreWidgets-viewsComponents.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrObservaciones]);
            // Despliegue de errores
            $this->showError(1, $f3, $result);
        }

    }

}
