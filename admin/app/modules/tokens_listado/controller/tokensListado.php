<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class tokensListado extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $WidgetsCommon;
    private $WidgetsViews;
    private $CommonData;

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'tokensListado';
        $this->FormInputs     = new UIFormInputs();
        $this->Codification   = new FunctionsSecurityCodification();
        $this->WidgetsCommon  = new UIWidgetsCommon();
        $this->WidgetsViews   = new UIWidgetsViews();
        $this->CommonData     = new FunctionsCommonData();
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
        // Se obtienen los datos
        $arrList    = $this->getDataList('', []);
        $arrEstado  = $this->getEstado();
        $arrUsuario = $this->getUsuario();

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrList['status'] === true && $arrEstado['status'] === true && $arrUsuario['status'] === true) {

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Tokens',
                'PageDescription' => 'Tokens.',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => 'Tokens',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_Codification'    => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList['data'],
                'arrEstado'       => $arrEstado['data'],
                'arrUsuario'      => $arrUsuario['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrList, $arrEstado, $arrUsuario]);
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
        $WhereData_int     = 'idEstado,idUsuario'; // Datos búsqueda exacta
        $WhereData_string  = 'Nombre';             // Datos búsqueda relativa
        $WhereData_between = '';                   // Datos búsqueda Between
        $whereInt          = '';                   // Se crea cadena
        $whereParams       = [];                   // Valores bindeados asociados a $whereInt
        /************************************/
        // Se validan las fechas
        $RespDataBetween = $this->searchValidateDates($WhereData_between);
        if($RespDataBetween!=''){
            Response::error($RespDataBetween, 500);
        }
        /************************************/
        // Agrego variable busqueda
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_int, 'tokens_listado', 1);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_string, 'tokens_listado', 2);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_between, 'tokens_listado', 3);
        $whereInt = $r['where']; $whereParams = $r['params'];

        /************************************/
        // Se obtienen los datos
        $arrList = $this->getDataList($whereInt, $whereParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrList['status'] === true) {

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'TableTitle'    => 'Tokens',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'  => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrList'       => $arrList['data'],
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
        // Se no existe el ID
        if (!isset($params['id'])) {
            Response::error('Registro ID inexistente', 400);
        }

        /************************************/
        // Se obtiene el ID
        $TokenID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        // Se verifica
        if (!$this->isValidDecrypted($TokenID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se obtienen los datos
        $rowData     = $this->getDataDetail($TokenID['data']);
        $arrModulos = $this->getDataDetailModulos($TokenID['data']);

        /************************************/
        // Fila vacía (id inexistente o de un token hijo)
        if ($rowData['status'] === true && empty($rowData['data'])) {
            Response::error('Registro inválido', 400);
        }

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($rowData['status'] === true && $arrModulos['status'] === true) {

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_WidgetsCommon'   => $this->WidgetsCommon,
                'Fnc_WidgetsViews'    => $this->WidgetsViews,
                'Fnc_CommonData'      => $this->CommonData,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData['data'],
                'arrModulos'      => $arrModulos['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-View.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData, $arrModulos]);
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
        $TokenID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        // Se verifica
        if (!$this->isValidDecrypted($TokenID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se obtienen los datos
        $rowData   = $this->getDataDetail($TokenID['data']);
        $arrEstado = $this->getEstado();

        /************************************/
        // Fila vacía (id inexistente o de un token hijo)
        if ($rowData['status'] === true && empty($rowData['data'])) {
            Response::error('Registro inválido', 400);
        }

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($rowData['status'] === true && $arrEstado['status'] === true) {

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'        => 'Resumen Token',
                'PageDescription'  => 'Resumen Token.',
                'PageAuthor'       => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'     => ConfigAPP::SOFTWARE['SoftwareName'],
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_WidgetsCommon'   => $this->WidgetsCommon,
                'Fnc_WidgetsViews'    => $this->WidgetsViews,
                'Fnc_Codification'    => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData['data'],
                'arrEstado'       => $arrEstado['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData, $arrEstado]);
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
        $TokenID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        // Se verifica
        if (!$this->isValidDecrypted($TokenID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se obtienen los datos
        $rowData = $this->getDataDetail($TokenID['data']);

        /************************************/
        // Fila vacía (id inexistente o de un token hijo)
        if ($rowData['status'] === true && empty($rowData['data'])) {
            Response::error('Registro inválido', 400);
        }

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
                'Fnc_WidgetsCommon'   => $this->WidgetsCommon,
                'Fnc_WidgetsViews'    => $this->WidgetsViews,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData['data'],
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
    // Campos de lectura (listado y detalle)
    /*******************************************************************/
    private function getDataFields(){
        return '
            tokens_listado.idToken,
            tokens_listado.Nombre,
            tokens_listado.Token,
            tokens_listado.FechaCreacion,
            tokens_listado.FechaCaducidad,
            tokens_listado.idEstado,
            usuarios_listado.Nombre AS Usuario,
            core_estados.Nombre AS Estado,
            core_estados.Color AS EstadoColor,
            (tokens_listado.FechaCaducidad IS NOT NULL AND tokens_listado.FechaCaducidad < CURDATE()) AS Caducado,
            tokens_listado.Origen';
    }

    /*******************************************************************/
    // Joins de lectura (listado y detalle)
    /*******************************************************************/
    private function getDataJoin(){
        return 'LEFT JOIN usuarios_listado ON usuarios_listado.idUsuario = tokens_listado.idUsuario
                LEFT JOIN core_estados ON core_estados.idEstado = tokens_listado.idEstado';
    }

    /*******************************************************************/
    // Se obtiene el listado (solo tokens manuales, Origen = 1)
    /*******************************************************************/
    private function getDataList($filter, $params = []){

        /************************************/
        // Se genera la query
        $query = [
            'data'    => $this->getDataFields(),
            'table'   => 'tokens_listado',
            'join'    => $this->getDataJoin(),
            'where'   => 'tokens_listado.Origen = ?'.($filter !== '' ? ' AND '.$filter : ''),
            'params'  => array_merge([1], $params),
            'group'   => '',
            'having'  => '',
            'order'   => 'tokens_listado.FechaCreacion DESC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        /************************************/
        // Preparo los datos
        $xParams = ['query' => $query];
        // Se retornan los datos
        return $this->Base_GetList($xParams);
    }

    /*******************************************************************/
    // Se obtienen los detalles (token manual con sus conteos)
    /*******************************************************************/
    private function getDataDetail($ID){

        /************************************/
        // Se genera la query
        $query = [
            'data'    => $this->getDataFields().',
                (SELECT COUNT(*) FROM tokens_listado_permisos WHERE tokens_listado_permisos.idToken = tokens_listado.idToken) AS NModulos,
                (SELECT COUNT(DISTINCT hijos.idClienteOAuth) FROM tokens_listado AS hijos WHERE hijos.idTokenPadre = tokens_listado.idToken AND hijos.Origen = 2) AS NClientes,
                (SELECT COUNT(*) FROM tokens_listado AS hijos WHERE hijos.idTokenPadre = tokens_listado.idToken AND hijos.Origen = 2 AND hijos.FechaExpiracion >= NOW()) AS NAccesos',
            'table'   => 'tokens_listado',
            'join'    => $this->getDataJoin(),
            'where'   => 'tokens_listado.idToken = ? AND tokens_listado.Origen = ?',
            'params'  => [$ID, 1],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        /************************************/
        // Preparo los datos
        $xParams = ['query' => $query];
        // Se retornan los datos
        return $this->Base_GetByID($xParams);
    }

    /*******************************************************************/
    // Se obtienen los modulos asignados al token
    /*******************************************************************/
    private function getDataDetailModulos($ID){

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'Modulo',
            'table'   => 'tokens_listado_permisos',
            'join'    => '',
            'where'   => 'idToken = ?',
            'params'  => [$ID],
            'group'   => '',
            'having'  => '',
            'order'   => 'Modulo ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        /************************************/
        // Preparo los datos
        $xParams = ['query' => $query];
        // Se consultan los datos
        $resp = $this->Base_GetList($xParams);
        // Si falla la consulta
        if (($resp['status'] ?? false) !== true) {
            return $resp;
        }
        /************************************/
        // Se enriquece con el nombre del modulo (si ya no es exponible, se muestra la carpeta)
        try {
            $exponibles = InternalFunctionsTokensModulos::listExposable();
        } catch (Throwable $e) {
            $exponibles = [];
        }
        foreach ($resp['data'] as $i => $fila) {
            $resp['data'][$i]['nombre'] = $exponibles[$fila['Modulo']]['nombre'] ?? $fila['Modulo'];
        }
        // Se retornan los datos
        return $resp;
    }

    /*******************************************************************/
    // Lookup de estados
    /*******************************************************************/
    private function getEstado(){

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idEstado AS ID,Nombre',
            'table'   => 'core_estados',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        /************************************/
        // Preparo los datos
        $xParams = ['query' => $query];
        // Se retornan los datos
        return $this->Base_GetList($xParams);
    }

    /*******************************************************************/
    // Lookup de usuarios
    /*******************************************************************/
    private function getUsuario(){

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idUsuario AS ID,Nombre',
            'table'   => 'usuarios_listado',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        /************************************/
        // Preparo los datos
        $xParams = ['query' => $query];
        // Se retornan los datos
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
        // Campos de auditoría OAuth: los fija el servidor, nunca se aceptan del formulario
        unset($_POST['Origen'], $_POST['idTokenPadre'], $_POST['idClienteOAuth'], $_POST['FechaExpiracion']);

        /************************************/
        // Datos fijados por el servidor (nunca se aceptan del formulario)
        $tok = (new FunctionsSecurityPasswords())->tokenBin2Hex(64);
        /************************************/
        // Se hacen las validaciones
        if (($tok['success'] ?? false) !== true) {
            Response::error('No se pudo generar el token', 500);
        }

        /************************************/
        // Se crean los datos
        $_POST['idUsuario']     = $f3->get('SESSION.DataInfo.UserID');
        $_POST['FechaCreacion'] = date('Y-m-d H:i:s');
        $_POST['Token']         = $tok['data'];

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idUsuario,Nombre,Token,FechaCreacion,idEstado,FechaCaducidad',
            'required'  => 'idUsuario,Nombre,Token,FechaCreacion,idEstado',
            'unique'    => 'Token',
            'encode'    => '',
            'table'     => 'tokens_listado',
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
    // Editar por post (nombre, estado y caducidad)
    /*******************************************************************/
    public function Update(){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Campos de auditoría OAuth: los fija el servidor, nunca se aceptan del formulario
        unset($_POST['Origen'], $_POST['idTokenPadre'], $_POST['idClienteOAuth'], $_POST['FechaExpiracion']);

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Guarda: solo se editan tokens manuales (Origen = 1)
        $queryGuarda = [
            'data'    => 'idToken',
            'table'   => 'tokens_listado',
            'join'    => '',
            'where'   => 'idToken = ? AND Origen = ?',
            'params'  => [(int) ($_POST['idToken'] ?? 0), 1],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        $xParamsGuarda  = ['query' => $queryGuarda];
        $ResponseGuarda = $this->Base_GetByID($xParamsGuarda);
        // Si falla la consulta, se revierte y se reporta el error
        if ($ResponseGuarda['status'] === false) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se reporta el error
            Response::error('Error al operar con la Base de Datos', 500, $ResponseGuarda['error'] ?? '');
        }
        // Si no existe o es un token hijo, se revierte
        if (empty($ResponseGuarda['data'])) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se reporta el error
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idToken,Nombre,idEstado,FechaCaducidad',
            'required'  => 'idToken,Nombre,idEstado',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'tokens_listado',
            'where'     => 'idToken',
            'Post'      => $_POST
        ];
        /************************************/
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);
        // Preparo los datos
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        // Ejecuto la query
        $Response = $this->Base_update($xParams);

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
        // Cascada al desactivar: se revocan refresh, accesos hijos y códigos del token
        if ((int) ($_POST['idEstado'] ?? 0) !== 1) {
            // Consultas de la cascada (en este orden)
            $arrCascada = [
                'DELETE FROM tokens_oauth_refresh WHERE idTokenAcceso IN (SELECT idToken FROM tokens_listado WHERE idTokenPadre = ? AND Origen = 2)',
                'DELETE FROM tokens_listado WHERE idTokenPadre = ? AND Origen = 2',
                'DELETE FROM tokens_oauth_codigos WHERE idToken = ?',
            ];
            // Recorro
            foreach ($arrCascada as $ActionSQL) {
                // Preparo los datos
                $xParamsSQL = [
                    'query'     => $ActionSQL,
                    'newBDConn' => $this->getDBConn(),
                    'showQuery' => false,
                    'singleRow' => false,
                    'params'    => [(int) $_POST['idToken']],
                ];
                // Ejecuto la query
                $respCascada = $this->Base_queryExecute($xParamsSQL);
                // Si falla la ejecucion, se revierte de inmediato
                if ($respCascada['status'] === false) {
                    // Se revierte la transaccion activa (no-op si no hay transaccion)
                    $this->Base_transactionRollback();
                    // Se diferencian los errores de validación de los de base de datos
                    if (is_array($respCascada['error'] ?? null)) {
                        Response::error('Datos inválidos', 400, $respCascada['error']);
                    }
                    // Se reporta el error
                    Response::error('Error al operar con la Base de Datos', 500, $respCascada['error'] ?? '');
                }
            }
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
    // Borrar dato (con la cascada OAuth)
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
        // Se descifra y valida el identificador
        $TokenID = $this->Codification->encryptDecrypt('decrypt', $dataDelete['idToken'] ?? '');
        if (!$this->isValidDecrypted($TokenID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Guarda: solo se borran tokens manuales (Origen = 1)
        $queryGuarda = [
            'data'    => 'idToken',
            'table'   => 'tokens_listado',
            'join'    => '',
            'where'   => 'idToken = ? AND Origen = ?',
            'params'  => [(int) $TokenID['data'], 1],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        $xParamsGuarda  = ['query' => $queryGuarda];
        $ResponseGuarda = $this->Base_GetByID($xParamsGuarda);
        // Si falla la consulta, se revierte y se reporta el error
        if ($ResponseGuarda['status'] === false) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se reporta el error
            Response::error('Error al operar con la Base de Datos', 500, $ResponseGuarda['error'] ?? '');
        }
        // Si no existe o es un token hijo, se revierte
        if (empty($ResponseGuarda['data'])) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se reporta el error
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'files'       => '',
            'table'       => 'tokens_listado',
            'where'       => 'idToken',
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
        $arrTableDel   = array();
        $arrTableDel[] = ['files' => '', 'table' => 'tokens_listado_permisos'];

        /************************************/
        // Verifico si existe
        if (!empty($arrTableDel)) {
            // Recorro
            foreach ($arrTableDel as $tblDel) {
                /************************************/
                // Se genera la query
                $query = ['files' => $tblDel['files'], 'table' => $tblDel['table'], 'where' => 'idToken', 'SubCarpeta' => '', 'Post' => $dataDelete];
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
        // Cascada OAuth: refresh, accesos hijos y códigos del token (en este orden)
        $arrCascada = [
            'DELETE FROM tokens_oauth_refresh WHERE idTokenAcceso IN (SELECT idToken FROM tokens_listado WHERE idTokenPadre = ? AND Origen = 2)',
            'DELETE FROM tokens_listado WHERE idTokenPadre = ? AND Origen = 2',
            'DELETE FROM tokens_oauth_codigos WHERE idToken = ?',
        ];
        // Recorro
        foreach ($arrCascada as $ActionSQL) {
            // Preparo los datos
            $xParamsSQL = [
                'query'     => $ActionSQL,
                'newBDConn' => $this->getDBConn(),
                'showQuery' => false,
                'singleRow' => false,
                'params'    => [(int) $TokenID['data']],
            ];
            // Ejecuto la query
            $respCascada = $this->Base_queryExecute($xParamsSQL);
            /************************************/
            // Si falla la ejecucion, se revierte de inmediato
            if ($respCascada['status'] === false) {
                // Se revierte la transaccion activa (no-op si no hay transaccion)
                $this->Base_transactionRollback();
                // Se diferencian los errores de validación de los de base de datos
                if (is_array($respCascada['error'] ?? null)) {
                    Response::error('Datos inválidos', 400, $respCascada['error']);
                }
                // Se reporta el error
                Response::error('Error al operar con la Base de Datos', 500, $respCascada['error'] ?? '');
            }
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

    /******************************************************************************/
    /*                             Métodos privados                               */
    /******************************************************************************/
    /*******************************************************************/
    // Se validan los datos
    /*******************************************************************/
    private function dataCheck($POST){
        // Variables
        $DataChecking = [
            'emptyData'                  => 'idEstado',
            'encode'                     => '',
            'ValidarEmail'               => '',
            'ValidarNumero'              => 'idEstado',
            'ValidarEntero'              => 'idEstado',
            'ValidarRut'                 => '',
            'ValidarPatente'             => '',
            'ValidarFecha'               => '',
            'ValidarHora'                => '',
            'ValidarURL'                 => '',
            'ValidarLargoMinimo'         => 'Nombre',
            'ValidarLargoMinimoN'        => 3,
            'ValidarLargoMaximoPorCampo' => [
                'Nombre' => 255,
            ],
            'ValidarFechaFormato'        => [
                'FechaCaducidad' => 'Y-m-d'
            ],
            'ValidarPalabrasCensuradas'  => 'Nombre',
            'ValidarEspaciosVacios'      => '',
            'ValidarMayusculas'          => '',
            'ValidarCoincidencias'       => '',
            'ValidarDominioEmail'        => '',
            'ValidarPasswordSegura'      => '',
            'ValidarFechaRango'          => '',
            'ValidarEdadMinima'          => '',
            'ValidarJSON'                => '',
            'ValidarUUID'                => '',
            'ValidarIP'                  => '',
            'ValidarSoloAlfanumerico'    => '',
            'ValidarSoloLetras'          => '',
            'Post'                       => $POST,
        ];
        // Retorno los datos
        return $DataChecking;
    }

}
