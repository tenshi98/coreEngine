<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class sistemaInstalacion extends ControllerBase {

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
        $this->controllerName  = 'Empty';
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //Resumen
    public function Resumen($f3){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Variable vacia
        $arrModules = [];

        //Arreglo con los controladores a instalar
        $array = $this->arrayModInstall();
        /******************************************/
        //Verifico si existe
        if($array){
            //recorro
            foreach ($array as $data) {
                //Se genera la query
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
        //Si hay resultados
        if(is_array($arrModules)){
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'        => 'Instalacion Modulos Plataforma',
                'PageDescription'  => 'Instalacion Modulos Plataforma.',
                'PageAuthor'       => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'     => ConfigAPP::SOFTWARE['SoftwareName'],
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*=========== Datos Consultados ===========*/
                'arrModules' => $arrModules,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 1, $this->returnRutaVista(__DIR__, 'app').'/sistemaInstalacion-Resumen.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 1, $f3);
        }
    }

    /******************************************************************************/
    //List
    public function resumenUpdate($f3){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Variable vacia
        $arrModules = [];

        //Arreglo con los controladores a instalar
        $array = $this->arrayModInstall();
        /******************************************/
        //Verifico si existe
        if($array){
            //recorro
            foreach ($array as $data) {
                //Se genera la query
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
        //Si hay resultados
        if(is_array($arrModules)){
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*=========== Datos Consultados ===========*/
                'arrModules' => $arrModules,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/sistemaInstalacion-Resumen-Update.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //View
    public function checkModuleData($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Variable vacia
        $arrModules    = [];
        $arrControlers = [];

        //Arreglo con los controladores a instalar
        $array = array($params['Controller']);
        /******************************************/
        //Verifico si existe
        if($array){
            //recorro
            foreach ($array as $data) {
                //Se genera la query
                $ListDataModule = method_exists($data, 'ListDataModule');
                //si el metodo existe
                if($ListDataModule===true){
                    $ControllerData = new $data;
                    //Se traen las rutas
                    for ($i=0; $i < 10; $i++) {
                        $arrModules[] = $ControllerData->listRouteModule($i, 0);
                    }
                }
            }
        }
        //Se eliminan valores vacios
        $arrModules = array_filter($arrModules);

        //Se parsean los datos
        if(is_array($arrModules)&&!empty($arrModules)){
            foreach ($arrModules as $key=>$modules){
                //Recorro
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


        /******************************************/
        //Se genera la query
        $query = [
            'data'    => 'idPermisos, idMetodo, RutaWeb, RutaController, Descripcion, idLevelLimit, Controller',
            'table'   => 'core_permisos_listado_rutas',
            'join'    => '',
            'where'   => 'Controller IN ('.$subWhere.')',
            'group'   => '',
            'having'  => '',
            'order'   => 'idRutas ASC',
            'limit'   => 10000
        ];
        //Ejecuto la query
        $xParams  = ['query' => $query];
        $arrRutas = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($arrModules)){
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*=========== Datos Consultados ===========*/
                'arrModules' => $arrModules,
                'arrRutas'   => $arrRutas,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/sistemaInstalacion-Resumen-checkModuleData.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //View
    public function checkModuleBBDD($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Variable vacia
        $arrModules    = [];
        $arrControlers = [];

        //Arreglo con los controladores a instalar
        $array = array($params['Controller']);
        /******************************************/
        //Verifico si existe
        if($array){
            //recorro
            foreach ($array as $data) {
                //Se genera la query
                $ListDataModule = method_exists($data, 'ListDataModule');
                //si el metodo existe
                if($ListDataModule===true){
                    $ControllerData = new $data;
                    //Se traen las rutas
                    for ($i=0; $i < 10; $i++) {
                        $arrModules[] = $ControllerData->listRouteModule($i, 0);
                    }
                }
            }
        }
        //Se eliminan valores vacios
        $arrModules = array_filter($arrModules);

        //Se parsean los datos
        if(is_array($arrModules)&&!empty($arrModules)){
            foreach ($arrModules as $key=>$modules){
                //Recorro
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


        /******************************************/
        //Se genera la query
        $query = [
            'data'    => 'idPermisos, idMetodo, RutaWeb, RutaController, Descripcion, idLevelLimit, Controller',
            'table'   => 'core_permisos_listado_rutas',
            'join'    => '',
            'where'   => 'Controller IN ('.$subWhere.')',
            'group'   => '',
            'having'  => '',
            'order'   => 'idRutas ASC',
            'limit'   => 10000
        ];
        //Ejecuto la query
        $xParams  = ['query' => $query];
        $arrRutas = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($arrModules)){
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*=========== Datos Consultados ===========*/
                'arrModules' => $arrModules,
                'arrRutas'   => $arrRutas,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/sistemaInstalacion-Resumen-checkModuleBBDD.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    /*                                  DATOS                                     */
    /******************************************************************************/
    /******************************************************************************/
    //Resumen-Update
    public function installModule(){
        //Verificacion metodo PUT
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            //Se parsean los datos
            parse_str(file_get_contents("php://input"),$dataPut);
            /******************************/
            //Se consulta
            $DataModule = method_exists($dataPut['Controller'], 'InstallModule');
            //si el metodo existe
            if($DataModule===true){
                //Se llama y ejecuta la instalacion
                $ControllerData = new $dataPut['Controller'];
                $Response       = $ControllerData->InstallModule();
                //si es la respuesta esperada
                if ($Response===true) {
                    // Devuelvo true con código 200 (OK)
                    echo Response::sendData(200, true);
                //si no lo es
                } else {
                    // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
                    echo Response::sendData(500, $Response);
                }
            }else{
                echo Response::sendData(500, "Instalador no existe");
            }
        }else {
            echo Response::sendData(500, "Error en el Request Method");
        }
    }

    /******************************************************************************/
    //Resumen-Update
    public function uninstallModule(){
        //Verificacion metodo PUT
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            //Se parsean los datos
            parse_str(file_get_contents("php://input"),$dataPut);
            /******************************/
            //Se consulta
            $DataModule = method_exists($dataPut['Controller'], 'UninstallModule');
            //si el metodo existe
            if($DataModule===true){
                //Se llama y ejecuta la instalacion
                $ControllerData = new $dataPut['Controller'];
                $Response       = $ControllerData->UninstallModule();
                //si es la respuesta esperada
                if ($Response===true) {
                    // Devuelvo true con código 200 (OK)
                    echo Response::sendData(200, true);
                //si no lo es
                } else {
                    // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
                    echo Response::sendData(500, $Response);
                }
            }else{
                echo Response::sendData(500, "Desinstalador no existe");
            }
        }else {
            echo Response::sendData(500, "Error en el Request Method");
        }
    }

    /******************************************************************************/
    /*                             EJECUCION OTROS                                */
    /******************************************************************************/
    /******************************************************************************/
    //Se listan los controladores
    public function arrayModInstall(){

        /*******************************************************/
        //Rutas
        $array = array(
            "kanbanTareasInstaller",
            "usuariosInstaller",
            "entidadesInstaller",
            "productosInstaller",
            "bodegasInstaller",
            "serviciosInstaller",
            "gestionDocumentosInstaller",
            "coreSistemaInstaller",
            "gestionCampanasInstaller",
            "tercerosEntidadesInstaller",
            "cotizacionInstaller",
            "maquinasInstaller",
            "fileExplorerInstaller"
        );

        //devuelvo
        return $array;
    }



}
