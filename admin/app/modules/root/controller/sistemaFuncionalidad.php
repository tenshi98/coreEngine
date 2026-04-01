<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class sistemaFuncionalidad extends ControllerBase {

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
        $this->controllerName     = 'Empty';
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //Resumen
    public function FileExplorer_updateView($f3, $params){

        /*******************************************************************/
        //Se llaman los datos
        $UserData  = $f3->get('SESSION.DataInfo');
        $arrLevel  = $f3->get('SESSION.arrLevel');

        //Se obtienen los archivos a mostrar
        $FileManager  = new FileManager();
        $files        = $FileManager->fileExplorer($params);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($files)){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'TableTitle'      => 'Explorador Archivos',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*=========== Datos Consultados ===========*/
                'files'         => $files,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/sistemaFuncionalidad-UpdateFileExplorer.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    public function FileExplorer_createFolder($f3) {
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            /*******************************************************************/
            //Se llaman los datos
            $UserData  = $f3->get('SESSION.DataInfo');
            $arrLevel  = $f3->get('SESSION.arrLevel');

            //Se obtienen los archivos a mostrar
            $FileManager  = new FileManager();
            $response     = $FileManager->createFolder($_POST);

            /*******************************************************************/
            /*                         Imprimir Datos                          */
            /*******************************************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'TableTitle'      => 'Explorador Archivos',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*=========== Datos Consultados ===========*/
                'response'      => $response,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/sistemaFuncionalidad-UpdateFileResponse.php');

        }
    }

    /******************************************************************************/
    public function FileExplorer_uploadFile($f3) {
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            /*******************************************************************/
            //Se llaman los datos
            $UserData  = $f3->get('SESSION.DataInfo');
            $arrLevel  = $f3->get('SESSION.arrLevel');

            /*******************************************************************/
            if (!isset($_FILES['file'])) {
                $response['success'] = false;
                $response['message'] = "No hay archivo";
            }else{
                //Se obtienen los archivos a mostrar
                $FileManager  = new FileManager();

                //Se generan las rutas
                $subFolder  = isset($_POST['SubRoute']) ? trim($FileManager->sanitizePath($_POST['SubRoute']), '/') : '';
                $subFolder .= isset($_POST['path']) ? '/'.trim($FileManager->sanitizePath($_POST['path']), '/') : '';

                //Arreglo
                $query = [
                    'files'     => [
                        [
                            'Identificador' => 'file',
                            'SubCarpeta'    => $subFolder,
                            'NombreArchivo' => '',
                            'SufijoArchivo' => '',
                            'ValidarTipo'   => 'word,excel,powerpoint,pdf,image,txt,zip,video,music',
                            'ValidarPeso'   => 10,
                            'Base64'        => false
                        ],
                    ]
                ];

                //Valido los archivos
                $dataFiles = $FileManager->validateFiles($_FILES, $query['files']);
                //Si todos los datos requeridos estan ok
                if ($dataFiles['success'] !== true) {
                    $response['success'] = $dataFiles['success'];
                    $response['message'] = $dataFiles['message'];
                //Si no hay errores se suben los archivos
                }else{
                    $newFileName = $FileManager->uploadFile($_FILES, $query['files']);
                    $response['success'] = $newFileName['success'];
                    $response['message'] = $newFileName['message'];
                }

            }

            /*******************************************************************/
            /*                         Imprimir Datos                          */
            /*******************************************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'TableTitle'      => 'Explorador Archivos',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*=========== Datos Consultados ===========*/
                'response'      => $response,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/sistemaFuncionalidad-UpdateFileResponse.php');

        }
    }

    /******************************************************************************/
    public function FileExplorer_delFolder($f3) {
        
    }

    /******************************************************************************/
    public function FileExplorer_delFile($f3) {
        
    }

    



}
