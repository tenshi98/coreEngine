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
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
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
                if($ListDataModule==true){
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
            $view = new View;
            echo $view->render('../app/templates/user-header.php');                                              // Header
            echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/sistemaInstalacion-Resumen.php');  // Vista
            echo $view->render('../app/templates/user-footer.php');                                              // Footer
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
                if($ListDataModule==true){
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
            $view = new View;
            echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/sistemaInstalacion-Resumen-Update.php');  // Vista
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 1, $f3);
        }
    }

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
            if($DataModule==true){
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
            if($DataModule==true){
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
            "maquinasInstaller"
        );

        //devuelvo
        return $array;
    }



}