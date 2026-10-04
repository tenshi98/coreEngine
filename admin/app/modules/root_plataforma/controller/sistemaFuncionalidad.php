<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class sistemaFuncionalidad extends ControllerBase {

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
        $this->controllerName     = 'Empty';
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /**************************************************************************************/
    /**
     * Niveles de acceso exigidos por cada operacion del explorador de archivos.
     * * Coherentes con los umbrales que ya aplica el widget en la UI
     *   (`UIWidgetsCommon.php:1683,1713,2189,2534,2585,2658,2711`):
     *   1 = solo ver, 2 = subir archivos / crear carpetas, 3 = borrar.
     */
    private const NIVEL_VER       = 1;
    private const NIVEL_ESCRITURA = 2;   // subir archivos y crear carpetas
    private const NIVEL_BORRADO   = 3;   // eliminar archivos y carpetas

    /**************************************************************************************/
    /*                              MÉTODOS PRIVADOS                                     */
    /**************************************************************************************/
    /**
     * Verifica que el usuario tenga concedidos los permisos minimos exigidos por la operacion.
     *
     * El nivel de acceso NO se recibe del cliente como dato confiable: el modulo que
     * implementa la pantalla lo concede en la sesion mediante `ScopeAccess::grant()`
     * al renderizar la vista, y aqui solo se valida esa concesion (`ScopeAccess::check()`).
     *
     * El parametro `AccessScope` viaja en la peticion unicamente para *seleccionar* una
     * concesion ya existente; no puede crear ni elevar ninguna. Un scope desconocido o
     * sin concesion previa se deniega (fail-closed).
     *
     * @param \Base $f3        Instancia de Fat-Free Framework.
     * @param int   $minLevel   Nivel minimo requerido por la operacion.
     * @param bool  $isJson403  true para responder JSON 403 (GET), false para el
     *                         contrato `fileData` que consume el widget (POST).
     * @param array $params     Tokens de ruta (`@scope`). Fat-Free no los expone en
     *                         $_GET, por lo que el GET los entrega por aqui.
     *
     * @return void Corta la ejecucion si el usuario no esta autorizado.
     */
    private function requireScope($f3, int $minLevel, bool $isJson403 = false, array $params = []){

        // Se recupera el ambito: cuerpo POST, query string o token de ruta
        $scope = $_POST['AccessScope'] ?? ($_GET['AccessScope'] ?? ($params['scope'] ?? ''));

        // El control se resuelve en el servidor: no se confia en el valor del cliente
        if (!ScopeAccess::check($f3, $scope, $minLevel)) {

            // Se registra el intento para trazabilidad (mismo criterio que el guard CSRF)
            AuditLogger::log(
                'AUTHZ',
                'fileExplorer: sin autorizacion. Ambito='.var_export($scope, true)
                .' minimo='.$minLevel.' usuario='.($f3->get('SESSION.DataInfo.UserID') ?: '0')
            );

            // Se corta la ejecucion con el formato que espera el cliente
            if ($isJson403) {
                Response::error('No tiene permisos para acceder al explorador de archivos', 403);
            }
            Response::fileData(false, 'No tiene permisos para realizar esta acción');
        }

    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /*******************************************************************/
    // Explorador de Archivos
    /*******************************************************************/
    public function FileExplorer_updateView($f3, $params){

        /************************************/
        // Autorizacion: nivel minimo "ver" (fail-closed)
        /************************************/
        $this->requireScope($f3, self::NIVEL_VER, true, is_array($params) ? $params : []);

        /************************************/
        // Se instancia la libreria
        $FileManager  = new FileManager();
        $files        = $FileManager->fileExplorer($params);

        /*******************************************************************/
        /*                     Se devuelven los Datos                      */
        /*******************************************************************/
        // Si hay resultados
        if(is_array($files)){
            /************************************/
            // Se instancia la vista
            Response::direct($files);
        /************************************/
        // Si no hay resultados
        } else {
            // Despliegue de errores
            $this->showError(2, $f3);
        }
    }

    /*******************************************************************/
    // Creacion de Carpetas
    /*******************************************************************/
    public function FileExplorer_createFolder($f3) {

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Autorizacion: nivel minimo "crear carpeta" (fail-closed)
        /************************************/
        $this->requireScope($f3, self::NIVEL_ESCRITURA);

        /************************************/
        // Se instancia la libreria
        $FileManager  = new FileManager();
        $response     = $FileManager->createFolder($_POST);

        /*******************************************************************/
        /*                     Se devuelven los Datos                      */
        /*******************************************************************/
        //Imprimir respuesta
        Response::fileData($response['success'], $response['message']);

    }

    /*******************************************************************/
    // Subida del Archivo
    /*******************************************************************/
    public function FileExplorer_uploadFile($f3) {

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Autorizacion: nivel minimo "subir archivos" (fail-closed)
        /************************************/
        $this->requireScope($f3, self::NIVEL_ESCRITURA);

        /*******************************************************************/
        if (!isset($_FILES['file'])) {
            $response['success'] = false;
            $response['message'] = "No hay archivo";
        }else{
            // Se instancia la libreria
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

    /*******************************************************************/
    // Eliminacion de Carpetas
    /*******************************************************************/
    public function FileExplorer_delFolder($f3) {

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Autorizacion: nivel minimo "borrar carpetas" (fail-closed)
        /************************************/
        $this->requireScope($f3, self::NIVEL_BORRADO);

        /************************************/
        // Se instancia la libreria
        $FileManager  = new FileManager();
        $response     = $FileManager->deleteFolder($_POST);

        /*******************************************************************/
        /*                     Se devuelven los Datos                      */
        /*******************************************************************/
        //Imprimir respuesta
        Response::fileData($response['success'], $response['message']);

    }

    /*******************************************************************/
    // Eliminacion del Archivo
    /*******************************************************************/
    public function FileExplorer_delFile($f3) {

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Autorizacion: nivel minimo "borrar archivos" (fail-closed)
        /************************************/
        $this->requireScope($f3, self::NIVEL_BORRADO);

        /************************************/
        // Se instancia la libreria
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
