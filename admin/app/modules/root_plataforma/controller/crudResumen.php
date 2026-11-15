<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class crudResumen extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $DataDate;
    private $DataNumbers;
    private $Codification;
    private $WidgetsCommon;
    private $WidgetsViews;
    private $WidgetsPreview;

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_ADMIN);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'crudResumen';
		$this->FormInputs     = new UIFormInputs();
		$this->DataDate       = new FunctionsDataDate();
		$this->DataNumbers    = new FunctionsDataNumbers();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->WidgetsCommon  = new UIWidgetsCommon();
		$this->WidgetsViews   = new UIWidgetsViews();
		$this->WidgetsPreview = new UIWidgetsPreview();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /*******************************************************************/
    // Listar
    /*******************************************************************/
    public function listAll($f3){

        /************************************/
        // Carga incremental: offset y tamaño de bloque (N_MaxItems + 1 fila para saber si hay más)
        $page = $this->getPageParams();

        /************************************/
        // Se obtienen los datos
        $arrList = $this->getDataList('', [], $page['limit']);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrList['status'] === true) {

            /************************************/
            // Se recorta la fila extra y se calcula si quedan más datos
            $more = $this->sliceMore($arrList['data'], $page);

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Crud Resumen',
                'PageDescription' => 'Testeos del Crud Resumen.',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => 'Crud Resumen',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                'Fnc_Codification'    => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $more['rows'],
                'hasMore' => $more['hasMore'],
                'nextOffset' => $more['next'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrList]);
            // Despliegue de errores
            $this->showError(1, $f3, $result);
        }
    }

    /*******************************************************************/
    // Actualizar Listar
    /*******************************************************************/
    public function UpdateList($f3){

        /************************************/
        // Variables
        $WhereData_int     = '';                                            // Datos búsqueda exacta
        $WhereData_string  = 'Email,Numero,Rut,Patente,Fecha,Hora,Palabra'; // Datos búsqueda relativa
        $WhereData_between = '';                                            // Datos búsqueda Between
        $whereInt          = '';                                            // Se crea cadena
        $whereParams       = [];                                            // Valores bindeados asociados a $whereInt
        /************************************/
        // Se validan las fechas
        $RespDataBetween = $this->searchValidateDates($WhereData_between);
        if($RespDataBetween!=''){
            Response::error($RespDataBetween, 400);
        }
        /************************************/
        // Agrego variable busqueda
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_int, 'core_test_crud', 1);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_string, 'core_test_crud', 2);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_between, 'core_test_crud', 3);
        $whereInt = $r['where']; $whereParams = $r['params'];

        /************************************/
        // Carga incremental: offset y tamaño de bloque (N_MaxItems + 1 fila para saber si hay más)
        $page = $this->getPageParams();

        /************************************/
        // Se obtienen los datos
        $arrList = $this->getDataList($whereInt, $whereParams, $page['limit']);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrList['status'] === true) {

            /************************************/
            // Se recorta la fila extra y se calcula si quedan más datos
            $more = $this->sliceMore($arrList['data'], $page);

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'TableTitle'      => 'Crud Resumen',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                'Fnc_Codification'    => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $more['rows'],
                'hasMore' => $more['hasMore'],
                'nextOffset' => $more['next'],
            ];

            /************************************/
            // Fragmento (solo filas) para la carga incremental, o tabla completa
            if ($page['fragment']) {
                header('X-Has-More: '.($more['hasMore'] ? '1' : '0'));
                header('X-Next-Offset: '.$more['next']);
                $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-UpdateListRows.php');
            } else {
                $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-UpdateList.php');
            }
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrList]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // View
    /*******************************************************************/
    public function View($f3, $params){

        /************************************/
        // Se no existe el ID
        if (!isset($params['id'])) {
            Response::error('Registro ID inexistente', 400);
        }

        /************************************/
        // Se obtiene el ID
        $CrudID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        // Se verifica
        if (!$this->isValidDecrypted($CrudID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se obtienen los datos
        $rowData          = $this->getDataDetail($CrudID['data']);
        $arrObservaciones = $this->getDataDetailObs($CrudID['data']);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] === true && $arrObservaciones['status'] === true){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_WidgetsViews'     => $this->WidgetsViews,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
                'arrObservaciones' => $arrObservaciones['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-View.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrObservaciones]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Resumen
    /*******************************************************************/
    public function Resumen($f3, $params){

        /************************************/
        // Se no existe el ID
        if (!isset($params['id'])) {
            Response::error('Registro ID inexistente', 400);
        }

        /************************************/
        // Se obtiene el ID
        $CrudID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        // Se verifica
        if (!$this->isValidDecrypted($CrudID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se obtienen los datos
        $rowData = $this->getDataDetail($CrudID['data']);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($rowData['status'] === true) {

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'        => 'Resumen Crud',
                'PageDescription'  => 'Resumen Crud.',
                'PageAuthor'       => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'     => ConfigAPP::SOFTWARE['SoftwareName'],
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'       => $this->FormInputs,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_WidgetsViews'     => $this->WidgetsViews,
                'Fnc_WidgetsPreview'   => $this->WidgetsPreview,
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                'Fnc_Codification'     => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData]);
            // Despliegue de errores
            $this->showError(1, $f3, $result);
        }
    }

    /*******************************************************************/
    // Resumen Actualizar
    /*******************************************************************/
    public function ResumenUpdate($f3, $params){

        /************************************/
        // Se no existe el ID
        if (!isset($params['id'])) {
            Response::error('Registro ID inexistente', 400);
        }

        /************************************/
        // Se obtiene el ID
        $CrudID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        // Se verifica
        if (!$this->isValidDecrypted($CrudID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se obtienen los datos
        $rowData = $this->getDataDetail($CrudID['data']);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($rowData['status'] === true) {

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_WidgetsViews'     => $this->WidgetsViews,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Update.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    /*                            CONSULTAS INTERNAS                              */
    /******************************************************************************/
    /*******************************************************************/
    // Se obtiene la lista
    /*******************************************************************/
    private function getDataList($filter, $params = [], $limit = null){

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idCrud,Email,Numero,Rut,Patente,Fecha,Hora,Palabra',
            'table'   => 'core_test_crud',
            'join'    => '',
            'where'   => $filter,
            'params'  => $params,
            'group'   => '',
            'having'  => '',
            'order'   => 'Email ASC, idCrud ASC',
            'limit'   => ($limit ?? ConfigAPP::APP["N_MaxItems"])
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        //Se retornan los datos
        return $this->Base_GetList($xParams);
    }

    /*******************************************************************/
    // Se obtienen los detalles
    /*******************************************************************/
    private function getDataDetail($ID){

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idCrud,idUsuario,Email,Numero,Rut,Patente,Fecha,Hora,Palabra,Direccion_img,File',
            'table'   => 'core_test_crud',
            'join'    => '',
            'where'   => 'idCrud = ?',
            'params'  => [$ID],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        //Se retornan los datos
        return $this->Base_GetByID($xParams);
    }

    /*******************************************************************/
    // Se obtienen las observaciones
    /*******************************************************************/
    private function getDataDetailObs($ID){

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                core_test_crud_observaciones.idObservaciones,
                core_test_crud_observaciones.Observacion,
                core_test_crud_observaciones.FechaCreacion,
                core_test_crud_observaciones.FechaEdicion,
                usuarios_listado.Nombre AS Usuario,
                usuarios_edicion.Nombre AS UsuarioEdicion',
            'table'   => 'core_test_crud_observaciones',
            'join'    => '
                LEFT JOIN usuarios_listado                     ON usuarios_listado.idUsuario  = core_test_crud_observaciones.idUsuario
                LEFT JOIN usuarios_listado AS usuarios_edicion ON usuarios_edicion.idUsuario  = core_test_crud_observaciones.idUsuarioEdicion',
            'where'   => 'core_test_crud_observaciones.idCrud = ?',
            'params'  => [$ID],
            'group'   => '',
            'having'  => '',
            'order'   => 'core_test_crud_observaciones.idObservaciones ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        //Se retornan los datos
        return $this->Base_GetList($xParams);
    }

    /******************************************************************************/
    /*                                  DATOS                                     */
    /******************************************************************************/
    /*******************************************************************/
    // Insertar
    /*******************************************************************/
    public function Insert($f3){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Usuario creador
        $_POST['idUsuario'] = $f3->get('SESSION.DataInfo.UserID');

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idUsuario,Email,Numero,Rut,Patente,Fecha,Hora,Palabra',
            'required'  => 'idUsuario,Email,Numero,Rut,Patente,Fecha,Hora,Palabra',
            'unique'    => 'Email,Rut',
            'encode'    => '',
            'table'     => 'core_test_crud',
            'Post'      => $_POST
        ];
        /************************************/
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);
        // Preparo los datos
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        // Ejecuto la query
        $Response = $this->Base_insert($xParams);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if ($Response['status'] === false) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se diferencian los errores de validación de los de base de datos
            if (is_array($Response['error'] ?? null)) {
                Response::error('Datos inválidos', 400, $Response['error']);
            }
            // Se reporta el error
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
        }

        /************************************/
        // Confirmar transacción
        $ResponseCommit = $this->Base_transactionCommit();

        /************************************/
        // Si falla la la ejecucion, se revierte de inmediato
        if ($ResponseCommit['status'] === false) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se reporta el error
            Response::error('Error al operar con la Base de Datos', 500, $ResponseCommit['error'] ?? '');
        }

        /************************************/
        // Si es un ID numérico, encripta y envía con código 200 (OK)
        $DataID = $this->Codification->encryptDecrypt('encrypt', $Response['data']);
        // Se verifica
        if (!$this->isValidDecrypted($DataID, 'text')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($DataID['data']);

    }

    /*******************************************************************/
    // Editar por put (solo modificar datos)
    // Editar por post (modificar y subir archivos)
    /*******************************************************************/
    public function Update($f3){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Usuario creador
        $_POST['idUsuario'] = $f3->get('SESSION.DataInfo.UserID');

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idCrud,idUsuario,Email,Numero,Rut,Patente,Fecha,Hora,Palabra',
            'required'  => 'idCrud,idUsuario,Email,Numero,Rut,Patente,Fecha,Hora,Palabra',
            'unique'    => 'Email,Rut',
            'encode'    => '',
            'table'     => 'core_test_crud',
            'where'     => 'idCrud',
            'Post'      => $_POST,
            'files'     => [
                [
                    'Identificador' => 'Direccion_img',
                    'SubCarpeta'    => '',
                    'NombreArchivo' => '',
                    'SufijoArchivo' => 'crudResumenIMG_',
                    'ValidarTipo'   => 'image',
                    'ValidarPeso'   => 10,
                    'Base64'        => true
                ],
                [
                    'Identificador' => 'File',
                    'SubCarpeta'    => '',
                    'NombreArchivo' => $this->generarNombreArchivo('crudResumenFile'),
                    'SufijoArchivo' => '',
                    'ValidarTipo'   => 'word,excel,powerpoint,pdf,image,txt,zip,video,music',
                    'ValidarPeso'   => 10,
                    'Base64'        => false
                ],
            ]
        ];
        /************************************/
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);
        // Preparo los datos
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        // Ejecuto la query
        $Response = $this->Base_update($xParams);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if ($Response['status'] === false) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se diferencian los errores de validación de los de base de datos
            if (is_array($Response['error'] ?? null)) {
                Response::error('Datos inválidos', 400, $Response['error']);
            }
            // Se reporta el error
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
        }

        /************************************/
        // Confirmar transacción
        $ResponseCommit = $this->Base_transactionCommit();

        /************************************/
        // Si falla la la ejecucion, se revierte de inmediato
        if ($ResponseCommit['status'] === false) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se reporta el error
            Response::error('Error al operar con la Base de Datos', 500, $ResponseCommit['error'] ?? '');
        }

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data']);

    }

    /*******************************************************************/
    // Borrar dato y archivos
    /*******************************************************************/
    public function Delete(){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Obtener datos
        parse_str(file_get_contents("php://input"),$dataDelete);

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Se genera la query
        $query = [
            'files'       => 'Direccion_img,File',
            'table'       => 'core_test_crud',
            'where'       => 'idCrud',
            'SubCarpeta'  => '',
            'Post'        => $dataDelete
        ];
        /************************************/
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $Response = $this->Base_delete($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se diferencian los errores de validación de los de base de datos
            if (is_array($Response['error'] ?? null)) {
                Response::error('Datos inválidos', 400, $Response['error']);
            }
            // Se reporta el error
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
        }

        /************************************/
        // Listado de las tablas a eliminar los datos relacionados
        $arrTableDel  = array();
        $arrTableDel[] = ['files' => '', 'table' => 'core_test_crud_observaciones'];

        /************************************/
        // Verifico si existe
        if (!empty($arrTableDel)) {
            // Recorro
            foreach ($arrTableDel as $tblDel) {
                /************************************/
                // Se genera la query
                $query = ['files' => $tblDel['files'], 'table' => $tblDel['table'], 'where' => 'idCrud', 'SubCarpeta' => '', 'Post' => $dataDelete];
                // Preparo los datos
                $xParams    = ['query' => $query];
                // Ejecuto la query
                $respDelRel = $this->Base_delete($xParams);
                /************************************/
                // Si falla la ejecucion, se revierte de inmediato
                if ($respDelRel['status'] === false) {
                    // Se revierte la transaccion activa (no-op si no hay transaccion)
                    $this->Base_transactionRollback();
                    // Se diferencian los errores de validación de los de base de datos
                    if (is_array($respDelRel['error'] ?? null)) {
                        Response::error('Datos inválidos', 400, $respDelRel['error']);
                    }
                    // Se reporta el error
                    Response::error('Error al operar con la Base de Datos', 500, $respDelRel['error'] ?? '');
                }
            }
        }

        /************************************/
        // Se confirma la transacción
        $ResponseCommit = $this->Base_transactionCommit();

        /************************************/
        // Si falla la la ejecucion, se revierte de inmediato
        if ($ResponseCommit['status'] === false) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se reporta el error
            Response::error('Error al operar con la Base de Datos', 500, $ResponseCommit['error'] ?? '');
        }

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data']);

    }

    /*******************************************************************/
    // Borrar archivos
    /*******************************************************************/
    public function delFiles(){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Obtener datos
        parse_str(file_get_contents("php://input"),$dataPut);

        /************************************/
        // Se genera la query
        $query = [
            'files'       => 'Direccion_img,File',
            'table'       => 'core_test_crud',
            'where'       => 'idCrud',
            'SubCarpeta'  => '',
            'Post'        => $dataPut
        ];
        /************************************/
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $Response = $this->Base_delFiles($xParams);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if ($Response['status'] === false) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se diferencian los errores de validación de los de base de datos
            if (is_array($Response['error'] ?? null)) {
                Response::error('Datos inválidos', 400, $Response['error']);
            }
            // Se reporta el error
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
        }

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data']);

    }

    /******************************************************************************/
    /*                             Métodos privados                               */
    /******************************************************************************/
    /*******************************************************************/
    // Se validan los datos
    /*******************************************************************/
    private function dataCheck($POST){
        // Variables
        $DataChecking = [
            'emptyData'                  => '',
            'encode'                     => '',
            'ValidarEmail'               => 'Email',
            'ValidarNumero'              => 'idUsuario,Numero',
            'ValidarEntero'              => 'idUsuario',
            'ValidarRut'                 => 'Rut',
            'ValidarPatente'             => '',
            'ValidarFecha'               => 'Fecha',
            'ValidarHora'                => 'Hora',
            'ValidarURL'                 => '',
            'ValidarLargoMinimo'         => 'Email,Palabra',
            'ValidarLargoMinimoN'        => 3,
            'ValidarLargoMaximoPorCampo' => [
                'Email'    => 255,
                'Palabra'  => 255,
            ],
            'ValidarPalabrasCensuradas' => 'Palabra',
            'ValidarEspaciosVacios'     => 'Email',
            'ValidarMayusculas'         => 'Email',
            'ValidarCoincidencias'      => '',
            'ValidarDominioEmail'       => 'Email',
            'ValidarPasswordSegura'     => '',
            'ValidarFechaRango'         => '',
            'ValidarEdadMinima'         => '',
            'ValidarJSON'               => '',
            'ValidarUUID'               => '',
            'ValidarIP'                 => '',
            'ValidarSoloAlfanumerico'   => '',
            'ValidarSoloLetras'         => '',
            'Post'                      => $POST,
        ];
        // Retorno los datos
        return $DataChecking;
    }

}
