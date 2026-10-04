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
        // Se genera la query
        $query = [
            'data'    => 'idCrud,Email,Numero,Rut,Patente,Fecha,Hora,Palabra',
            'table'   => 'core_test_crud',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Email ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrList = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrList['status'] === true) {

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
                'arrList'         => $arrList['data'],
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
            Response::error($RespDataBetween, 500);
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
        // Se genera la query
        $query = [
            'data'    => 'idCrud,Email,Numero,Rut,Patente,Fecha,Hora,Palabra',
            'table'   => 'core_test_crud',
            'join'    => '',
            'where'   => $whereInt,
            'params'  => $whereParams,
            'group'   => '',
            'having'  => '',
            'order'   => 'Email ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrList = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrList['status'] === true) {

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
                'arrList'         => $arrList['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-UpdateList.php');
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
        // Se obtiene el ID
        $CrudID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($CrudID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idUsuario,Email,Numero,Rut,Patente,Fecha,Hora,Palabra,Direccion_img,File',
            'table'   => 'core_test_crud',
            'join'    => '',
            'where'   => 'idCrud = ?',
            'params'  => [$CrudID['data']],
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
            'data'    => 'Observacion',
            'table'   => 'core_test_crud_observaciones',
            'join'    => '',
            'where'   => 'idCrud = ?',
            'params'  => [$CrudID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'idObservaciones ASC',
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
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
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
        // Se obtiene el ID
        $CrudID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($CrudID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idCrud,idUsuario,Email,Numero,Rut,Patente,Fecha,Hora,Palabra,Direccion_img,File',
            'table'   => 'core_test_crud',
            'join'    => '',
            'where'   => 'idCrud = ?',
            'params'  => [$CrudID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

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
        // Se obtiene el ID
        $CrudID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($CrudID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idCrud,idUsuario,Email,Numero,Rut,Patente,Fecha,Hora,Palabra,Direccion_img,File',
            'table'   => 'core_test_crud',
            'join'    => '',
            'where'   => 'idCrud = ?',
            'params'  => [$CrudID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

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
    /*                                  DATOS                                     */
    /******************************************************************************/
    /*******************************************************************/
    // Insertar
    /*******************************************************************/
    public function Insert($f3){

        /************************************/
        // Usuario creador
        $_POST['idUsuario'] = $f3->get('SESSION.DataInfo.UserID');

        /************************************/
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

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
        // Preparo los datos
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        // Ejecuto la query
        $Response = $this->Base_insert($xParams);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if ($Response['status'] === false) {
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
        }

        /************************************/
        // Si es un ID numérico, encripta y envía con código 200 (OK)
        $DataID = $this->Codification->encryptDecrypt('encrypt', $Response['data']);
        if (!$this->isValidDecrypted($DataID, 'text')) {
            Response::error('Registro inválido', 400);
        }
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
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

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
        // Preparo los datos
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        // Ejecuto la query
        $Response = $this->Base_update($xParams);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if ($Response['status'] === false) {
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
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
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $Response = $this->Base_delete($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            $this->Base_transactionRollback();
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
                    $this->Base_transactionRollback();
                    Response::error('Error al operar con la Base de Datos', 500, $respDelRel['error']);
                }
            }
        }

        /************************************/
        // Se confirma la transacción
        $this->Base_transactionCommit();

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
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $Response = $this->Base_delFiles($xParams);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if ($Response['status'] === false) {
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
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => 'Email',
            'ValidarNumero'             => 'idUsuario,Numero',
            'ValidarEntero'             => 'idUsuario',
            'ValidarRut'                => 'Rut',
            'ValidarPatente'            => '',
            'ValidarFecha'              => 'Fecha',
            'ValidarHora'               => 'Hora',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => 'Email,Palabra',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'Email,Palabra',
            'ValidarLargoMaximoN'       => 255,
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
