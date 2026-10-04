<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class sistemaInstalacion extends ControllerBase {

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
        $this->controllerName  = 'Empty';
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /*******************************************************************/
    // Resumen
    /*******************************************************************/
    public function Resumen($f3){
        /************************************/
        // Variable vacia
        $arrModules = [];

        //Arreglo con los controladores a instalar
        $array = $this->arrayModInstall();
        /************************************/
        // Verifico si existe
        if($array){
            // Recorro los datos
            foreach ($array as $data) {
                /************************************/
                // Se genera la query
                $ListDataModule = method_exists($data, 'ListDataModule');
                //si el metodo existe
                if($ListDataModule===true){
                    $ControllerData = new $data;
                    $arrModules[]   = $ControllerData->ListDataModule();
                }
            }
        }

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if(is_array($arrModules)){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'        => 'Instalacion Modulos Plataforma',
                'PageDescription'  => 'Instalacion Modulos Plataforma.',
                'PageAuthor'       => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'     => ConfigAPP::SOFTWARE['SoftwareName'],
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*=========== Datos Consultados ===========*/
                'arrModules' => $arrModules,
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/sistemaInstalacion-Resumen.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrModules]);
            // Despliegue de errores
            $this->showError(1, $f3, $result);
        }
    }

    /*******************************************************************/
    // Listar
    /*******************************************************************/
    public function resumenUpdate($f3){
        /************************************/
        // Variable vacia
        $arrModules = [];

        //Arreglo con los controladores a instalar
        $array = $this->arrayModInstall();
        /************************************/
        // Verifico si existe
        if($array){
            // Recorro los datos
            foreach ($array as $data) {
                /************************************/
                // Se genera la query
                $ListDataModule = method_exists($data, 'ListDataModule');
                //si el metodo existe
                if($ListDataModule===true){
                    $ControllerData = new $data;
                    $arrModules[]   = $ControllerData->ListDataModule();
                }
            }
        }

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if(is_array($arrModules)){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*=========== Datos Consultados ===========*/
                'arrModules' => $arrModules,
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/sistemaInstalacion-Resumen-Update.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrModules]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // View
    /*******************************************************************/
    public function checkModuleData($f3, $params){
        /************************************/
        // Variable vacia
        $arrModules    = [];
        $arrControlers = [];

        //Arreglo con los controladores a instalar
        $array = array($params['Controller']);
        /************************************/
        // Verifico si existe
        if($array){
            // Recorro los datos
            foreach ($array as $data) {
                /************************************/
                // Se genera la query
                $ListDataModule = method_exists($data, 'ListDataModule');
                //si el metodo existe
                if($ListDataModule===true){
                    $ControllerData = new $data;
                    //Se traen las rutas dinamicamente
                    $i = 1;
                    while (true) {
                        $routes = $ControllerData->getRouteDefinitions($i);
                        if (empty($routes)) {
                            break;
                        }
                        $arrModules[] = $routes;
                        $i++;
                    }
                }
            }
        }
        //Se eliminan valores vacios
        $arrModules = array_filter($arrModules);

        /************************************/
        // Obtener datos
        if(is_array($arrModules)&&!empty($arrModules)){
            foreach ($arrModules as $key=>$modules){
                // Recorro
                foreach($modules as $crud){
                    if(isset($crud['idMetodo'])&&$crud['idMetodo']!=''){
                        $arrControlers[] = '"'.$crud['Controller'].'"';
                    }
                }
            }
        }
        //Se eliminan duplicados
        $arrControlers = array_unique($arrControlers);
        //Se filtran los controladores
        $subWhere   = $arrControlers ? implode(',', $arrControlers) : '';

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idPermisos, idMetodo, RutaWeb, RutaController, Descripcion, idLevelLimit, Controller',
            'table'   => 'core_permisos_listado_rutas',
            'join'    => '',
            'where'   => 'Controller IN ('.$subWhere.')',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'idRutas ASC',
            'limit'   => 9999
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $arrRutas = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if(is_array($arrModules) && $arrRutas['status']){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*=========== Datos Consultados ===========*/
                'arrModules' => $arrModules,
                'arrRutas'   => $arrRutas['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/sistemaInstalacion-Resumen-checkModuleData.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrRutas]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // View
    /*******************************************************************/
    public function checkModuleBBDD($f3, $params){
        /************************************/
        // Variable vacia
        $arrModules    = [];
        $arrControlers = [];

        //Arreglo con los controladores a instalar
        $array = array($params['Controller']);
        /************************************/
        // Verifico si existe
        if($array){
            // Recorro los datos
            foreach ($array as $data) {
                /************************************/
                // Se genera la query
                $ListDataModule = method_exists($data, 'ListDataModule');
                //si el metodo existe
                if($ListDataModule===true){
                    $ControllerData = new $data;
                    //Se traen las rutas dinamicamente
                    $i = 1;
                    while (true) {
                        $routes = $ControllerData->getRouteDefinitions($i);
                        if (empty($routes)) {
                            break;
                        }
                        $arrModules[] = $routes;
                        $i++;
                    }
                }
            }
        }
        //Se eliminan valores vacios
        $arrModules = array_filter($arrModules);

        /************************************/
        // Obtener datos
        if(is_array($arrModules)&&!empty($arrModules)){
            foreach ($arrModules as $key=>$modules){
                // Recorro
                foreach($modules as $crud){
                    if(isset($crud['idMetodo'])&&$crud['idMetodo']!=''){
                        $arrControlers[] = '"'.$crud['Controller'].'"';
                    }
                }
            }
        }
        //Se eliminan duplicados
        $arrControlers = array_unique($arrControlers);
        //Se filtran los controladores
        $subWhere   = $arrControlers ? implode(',', $arrControlers) : '';

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idPermisos, idMetodo, RutaWeb, RutaController, Descripcion, idLevelLimit, Controller',
            'table'   => 'core_permisos_listado_rutas',
            'join'    => '',
            'where'   => 'Controller IN ('.$subWhere.')',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'idRutas ASC',
            'limit'   => 9999
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $arrRutas = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if(is_array($arrModules) && $arrRutas['status']){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*=========== Datos Consultados ===========*/
                'arrModules' => $arrModules,
                'arrRutas'   => $arrRutas['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/sistemaInstalacion-Resumen-checkModuleBBDD.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrRutas]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    /*                                  DATOS                                     */
    /******************************************************************************/
    /*******************************************************************/
    // Intalacion del Modulo
    /*******************************************************************/
    public function installModule(){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Obtener datos
        parse_str(file_get_contents("php://input"),$dataPut);

        /************************************/
        //Se consulta
        $DataModule = method_exists($dataPut['Controller'], 'InstallModule');
        //si el metodo existe
        if($DataModule===true){
            //Se llama y ejecuta la instalacion
            $ControllerData = new $dataPut['Controller'];
            $Response       = $ControllerData->InstallModule();
            //si es la respuesta esperada
            if ($Response){
                // Devuelvo true con código 200 (OK)
                Response::success(true);
            //si no lo es
            } else {
                // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
                // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
                Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
            }
        }else{
            Response::error('Instalador no existe', 500);
        }

    }

    /*******************************************************************/
    // Resumen Actualizar
    /*******************************************************************/
    public function uninstallModule(){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Obtener datos
        parse_str(file_get_contents("php://input"),$dataPut);

        /************************************/
        //Se consulta
        $DataModule = method_exists($dataPut['Controller'], 'UninstallModule');
        //si el metodo existe
        if($DataModule===true){
            //Se llama y ejecuta la instalacion
            $ControllerData = new $dataPut['Controller'];
            $Response       = $ControllerData->UninstallModule();
            //si es la respuesta esperada
            if ($Response){
                // Devuelvo true con código 200 (OK)
                Response::success(true);
            //si no lo es
            } else {
                // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
                Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
            }
        }else{
            Response::error('Desinstalador no existe', 500);
        }

    }

    /******************************************************************************/
    /*                             EJECUCION OTROS                                */
    /******************************************************************************/
    /*******************************************************************/
    // Se listan los controladores
    /*******************************************************************/
    public function arrayModInstall(){

        /*******************************************************/
        // Variable vacia
        $array = array();

        /*******************************************************/
        // Carpeta raiz de los modulos
        $modulesPath = __DIR__ . '/../../';

        /*******************************************************/
        // Se escanean los archivos *Installer.php de cada modulo
        // (se excluye el modulo "installer", que es el instalador de la plataforma)
        $files = glob($modulesPath . '*/installer/*Installer.php');

        /*******************************************************/
        // Recorro los archivos encontrados
        foreach ($files as $file) {
            /************************************/
            // Se excluye el modulo raiz "installer"
            $moduleDir = basename(dirname(dirname($file)));
            if ($moduleDir === 'installer') {
                continue;
            }

            /************************************/
            // Nombre de la clase (igual al nombre del archivo)
            $class = basename($file, '.php');

            /************************************/
            // Se valida que la clase exista (autoload) y sea un instalador valido
            if (
                class_exists($class) &&
                (
                    method_exists($class, 'ListDataModule') ||
                    method_exists($class, 'InstallModule') ||
                    method_exists($class, 'UninstallModule')
                )
            ) {
                $array[] = $class;
            }
        }

        //Ordenar Alfabeticamente
        sort($array);

        // Retorno los datos
        return $array;
    }



}
