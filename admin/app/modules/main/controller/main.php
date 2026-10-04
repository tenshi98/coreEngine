<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class main extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $ServerServer;
    private $DataDate;

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
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
    /*******************************************************************/
    // Vista - Login
    /*******************************************************************/
    public function login($f3){

        /************************************/
        //Se cargan los datos de la plataforma
        $query = [
            'data'   => 'Sistema_idTema',
            'table'  => 'core_sistemas',
            'join'   => '',
            'where'  => 'idSistema = ?',
            'params'  => [1],
            'group'  => '',
            'having' => '',
            'order'  => ''
        ];
        //Verifico si hay un dato
        $xParams     = ['query' => $query];
        // Ejecuto la query
        $rowOpciones = $this->Base_GetByID($xParams);

        /************************************/
        // Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Iniciar Sesión',
            'PageDescription' => 'Iniciar Sesión',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*=========== Datos Consultados ===========*/
            'rowOpciones'    => $rowOpciones['data'],

        ];

        /************************************/
        // Se instancia la vista
        $view     = new View;
        echo $view->render('../app/templates/guest-header.php');
        echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/main-login.php');
        echo $view->render('../app/templates/guest-footer.php');

    }

    /*******************************************************************/
    // Recuperar Contraseña
    /*******************************************************************/
    public function error404($f3){

        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Página de error',
            'PageDescription' => 'Página de error',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
        ];

        // Se instancia la vista
        $view = new View;
        echo $view->render('../app/templates/pages-error404.php'); // Header
    }

    /*******************************************************************/
    // pantalla principal
    /*******************************************************************/
    public function principal($f3){

        /************************************/
        // Se llaman los datos
        $arrMenu  = $f3->get('SESSION.arrMenu');

        /************************************/
        // Variable vacia
        $MainViewData = [];
        $menuCounters = [];

        //Arreglo con los controladores con widgets, considerar que desde aqui se ordenan
        $array = $this->arrayWidgetViews();
        /************************************/
        // Verifico si existe
        if(is_array($array) && count($array)>0){
            // Recorro los datos
            foreach ($array as $data) {
                /************************************/
                // Se genera la query
                $loadWidgets = method_exists($data, 'loadWidgets');
                //si el metodo existe
                if($loadWidgets===true){
                    // Se instancia SIN ejecutar el constructor: loadWidgets() solo devuelve
                    // un arreglo estatico y no usa la Base de Datos. Ejecutar el constructor
                    // abriria una conexion PDO por cada modulo, agotando el limite de
                    // conexiones del servidor (SQLSTATE[HY000] [1040] Too many connections).
                    $arrModules = $this->loadWidgetsSinConexion($data);
                    //Permisos
                    if(is_array($arrModules) && isset($arrModules['Menu_Name'], $arrModules['Menu_Value']) && is_array($arrModules['Menu_Value'])){
                        $menuCounters[$arrModules['Menu_Name']] = $arrModules['Menu_Value'];
                    }
                }
            }
        }

        /************************************/
        // Se recorren los permisos y se validan
        // (Se normaliza $arrMenu a array para tolerar sesiones vacias o corruptes)
        $arrMenu = is_array($arrMenu) ? $arrMenu : [];
        foreach ($menuCounters as $section => $names) {
            // Verifico si existen datos del menu
            if (!empty($arrMenu[$section])) {
                // Recorro el menu
                foreach ($arrMenu[$section] as $asd) {
                    if (isset($names[$asd['Nombre']])) {
                        $MainViewData[] = $names[$asd['Nombre']]; //Se guardan las URL
                    }
                }
            }
        }

        //Se filtran para obtener datos unicos
        $MainViewData = array_values(array_unique($MainViewData));

        /************************************/
        // Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Principal',
            'PageDescription' => 'Principal',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'        => $this->getUserData($f3),
            /*===========   Funcionalidad   ===========*/
            'Fnc_ServerServer'    => $this->ServerServer,
            'Fnc_DataDate'        => $this->DataDate,
            'Fnc_WidgetsCommon'   => new UIWidgetsCommon(),
            /*=========== Datos Consultados ===========*/
            'MainViewData'    => $MainViewData,
        ];

        /************************************/
        // Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/main-principal.php');
    }

    /*******************************************************************/
    // Se listan los controladores
    /*******************************************************************/
    public function arrayWidgetViews(){

        /*******************************************************/
        // Variable vacia
        $array = array();

        /*******************************************************/
        // Carpeta raiz de los modulos (ruta ABSOLUTA resuelta con realpath para
        // no depender del directorio de trabajo actual, que puede diferir entre
        // localhost y produccion)
        $modulesPath = realpath(__DIR__ . '/../../');

        /*******************************************************/
        // Si no se pudo resolver la ruta (permisos, open_basedir, symlinks) se registra
        if($modulesPath===false || !is_dir($modulesPath)){
            error_log('[coreEngine][arrayWidgetViews] No se pudo resolver la ruta de modulos desde ' . __DIR__);
            return $array;
        }

        /*******************************************************/
        // Se escanean los archivos *Widgets.php de cada modulo
        $files = glob($modulesPath . '/*/widgets/*Widgets.php');

        /*******************************************************/
        // glob() devuelve false ante error (tipicamente open_basedir en produccion)
        if(!is_array($files)){
            error_log('[coreEngine][arrayWidgetViews] glob() no pudo listar widgets en ' . $modulesPath);
            return $array;
        }

        /*******************************************************/
        // Orden alfabetico para que el orden de los widgets sea estable
        sort($files);

        /*******************************************************/
        // Recorro los archivos encontrados
        foreach ($files as $file) {
            /************************************/
            // Nombre de la clase (igual al nombre del archivo)
            $class = basename($file, '.php');

            /************************************/
            // Se carga el archivo de forma explicita: class_exists() delegaba en el
            // autoload de F3, que resuelve rutas RELATIVAS al directorio de trabajo.
            if(!class_exists($class, false)){
                require_once $file;
            }

            /************************************/
            // Se valida que la clase se haya cargado y tenga el metodo loadWidgets
            if (!class_exists($class)) {
                error_log('[coreEngine][arrayWidgetViews] Widget ignorado (clase inexistente): ' . $class);
                continue;
            }
            if (!method_exists($class, 'loadWidgets')) {
                error_log('[coreEngine][arrayWidgetViews] Widget ignorado (sin loadWidgets): ' . $class);
                continue;
            }
            $array[] = $class;
        }

        // Retorno los datos
        return $array;
    }

    /************************************************************************************************************/
    /**
     * Invoca loadWidgets() de un controlador de widgets SIN ejecutar su constructor.
     *
     * Los controladores de widgets extienden de ControllerBase y su constructor abre
     * una conexion PDO a la Base de Datos (Database::getSQLConnection). La pantalla
     * principal solo necesita leer el arreglo estatico que devuelve loadWidgets(),
     * por lo que instanciarlos "normalmente" abre una conexion por cada modulo
     * instalado y puede agotar el limite max_user_connections del servidor.
     *
     * Se usa ReflectionClass::newInstanceWithoutConstructor() para obtener la
     * instancia sin disparar el constructor. Si algun dia loadWidgets() llegara a
     * depender del estado del objeto, el error queda registrado en el log y ese
     * modulo se omite (no se degrada al resto de la pagina).
     *
     * @param string $Class Nombre de la clase controladora del widget.
     *
     * @return array Arreglo con Menu_Name y Menu_Value, o [] si no fue posible obtenerlo.
     */
    private function loadWidgetsSinConexion($Class){

        /********************** Si todo esta ok **********************/
        // Validacion temprana: la clase debe existir y exponer el metodo
        if(!is_string($Class) || $Class==='' || !class_exists($Class) || !method_exists($Class, 'loadWidgets')){
            return [];
        }

        /********************** Instancia sin constructor **********************/
        try {
            $reflection = new ReflectionClass($Class);
            $instancia  = $reflection->newInstanceWithoutConstructor();
            $resultado  = $instancia->loadWidgets();
        } catch (Throwable $e) {
            error_log('[coreEngine][loadWidgetsSinConexion] ' . $Class . ' -> ' . $e->getMessage());
            return [];
        }

        /**********************  Retorno datos  **********************/
        return is_array($resultado) ? $resultado : [];
    }


}
