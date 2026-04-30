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
        //Se instancia la libreria
        $FileManager  = new FileManager();
        $files        = $FileManager->fileExplorer($params);

        /*******************************************************************/
        /*                     Se devuelven los Datos                      */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($files)){
            /******************************************/
            //Se instancia la vista
            Response::direct($files);
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError(2, $f3);
        }
    }

    /******************************************************************************/
    public function FileExplorer_createFolder($f3) {
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            /*******************************************************************/
            //Se instancia la libreria
            $FileManager  = new FileManager();
            $response     = $FileManager->createFolder($_POST);

            /*******************************************************************/
            /*                     Se devuelven los Datos                      */
            /*******************************************************************/
            //Imprimir respuesta
            Response::fileData($response['success'], $response['message']);

        }
    }

    /******************************************************************************/
    public function FileExplorer_uploadFile($f3) {
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            /*******************************************************************/
            if (!isset($_FILES['file'])) {
                $response['success'] = false;
                $response['message'] = "No hay archivo";
            }else{
                //Se instancia la libreria
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
            /*                     Se devuelven los Datos                      */
            /*******************************************************************/
            //Imprimir respuesta
            Response::fileData($response['success'], $response['message']);

        }
    }

    /******************************************************************************/
    public function FileExplorer_delFolder($f3) {
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            /*******************************************************************/
            //Se instancia la libreria
            $FileManager  = new FileManager();
            $response     = $FileManager->deleteFolder($_POST);

            /*******************************************************************/
            /*                     Se devuelven los Datos                      */
            /*******************************************************************/
            //Imprimir respuesta
            Response::fileData($response['success'], $response['message']);

        }
    }

    /******************************************************************************/
    public function FileExplorer_delFile($f3) {
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            /*******************************************************************/
            //Se instancia la libreria
            $FileManager  = new FileManager();

            //Se generan las rutas
            $subFolder  = isset($_POST['SubRoute']) ? trim($FileManager->sanitizePath($_POST['SubRoute']), '/') : '';
            $subFolder .= isset($_POST['path']) ? '/'.trim($FileManager->sanitizePath($_POST['path']), '/') : '';

            //Se eliminan los archivos en caso de existir
            $delFile  = $FileManager->deleteFile($_POST['name'], $subFolder);

            //Si todos los datos requeridos estan ok
            if ($delFile !== true) {
                $response['success'] = false;
                $response['message'] = 'El archivo no se ha eliminado en el servidor';
            //Si no hay errores se suben los archivos
            }else{
                $response['success'] = true;
                $response['message'] = true;
            }

            /*******************************************************************/
            /*                     Se devuelven los Datos                      */
            /*******************************************************************/
            //Imprimir respuesta
            Response::fileData($response['success'], $response['message']);

        }
    }





}
