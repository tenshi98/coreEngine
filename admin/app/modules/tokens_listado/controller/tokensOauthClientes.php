<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class tokensOauthClientes extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $WidgetsCommon;
    private $WidgetsViews;

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'tokensOauthClientes';
        $this->FormInputs     = new UIFormInputs();
        $this->Codification   = new FunctionsSecurityCodification();
        $this->WidgetsCommon  = new UIWidgetsCommon();
        $this->WidgetsViews   = new UIWidgetsViews();
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
        $arrList = $this->getDataList('', []);

        /************************************/
        // Lookup de estados
        $queryEstado = [
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
        $xParamsEstado = ['query' => $queryEstado];
        $arrEstado     = $this->Base_GetList($xParamsEstado);

        /************************************/
        // Conteo de clientes que nunca obtuvieron acceso (aviso de la vista)
        $queryNSinUso = [
            'data'    => 'COUNT(*) AS NSinUso',
            'table'   => 'tokens_oauth_clientes',
            'join'    => '',
            'where'   => 'FechaUltimoUso IS NULL',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        $xParamsNSinUso = ['query' => $queryNSinUso];
        $arrNSinUso     = $this->Base_GetByID($xParamsNSinUso);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrList['status'] === true && $arrEstado['status'] === true && $arrNSinUso['status'] === true) {

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Clientes OAuth',
                'PageDescription' => 'Clientes OAuth.',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => 'Clientes OAuth',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_Codification'    => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList['data'],
                'arrEstado'       => $arrEstado['data'],
                'NSinUso'         => (int) ($arrNSinUso['data']['NSinUso'] ?? 0),
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrList, $arrEstado, $arrNSinUso]);
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
        $WhereData_int     = 'idEstado';       // Datos búsqueda exacta
        $WhereData_string  = 'Nombre,ClientId'; // Datos búsqueda relativa
        $WhereData_between = '';               // Datos búsqueda Between
        $whereInt          = '';               // Se crea cadena
        $whereParams       = [];               // Valores bindeados asociados a $whereInt
        /************************************/
        // Se validan las fechas
        $RespDataBetween = $this->searchValidateDates($WhereData_between);
        if($RespDataBetween!=''){
            Response::error($RespDataBetween, 500);
        }
        /************************************/
        // Agrego variable busqueda
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_int, 'tokens_oauth_clientes', 1);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_string, 'tokens_oauth_clientes', 2);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_between, 'tokens_oauth_clientes', 3);
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
                'TableTitle'    => 'Clientes OAuth',
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
        $ClienteID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        // Se verifica
        if (!$this->isValidDecrypted($ClienteID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query (nunca se selecciona el hash del secreto)
        $query = [
            'data'    => '
                tokens_oauth_clientes.idCliente,
                tokens_oauth_clientes.ClientId,
                tokens_oauth_clientes.AuthMethod,
                tokens_oauth_clientes.Nombre,
                tokens_oauth_clientes.RedirectUris,
                tokens_oauth_clientes.Metadatos,
                tokens_oauth_clientes.idEstado,
                tokens_oauth_clientes.FechaRegistro,
                tokens_oauth_clientes.IPRegistro,
                tokens_oauth_clientes.IPClave,
                tokens_oauth_clientes.FechaUltimoUso,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor',
            'table'   => 'tokens_oauth_clientes',
            'join'    => 'LEFT JOIN core_estados ON core_estados.idEstado = tokens_oauth_clientes.idEstado',
            'where'   => 'tokens_oauth_clientes.idCliente = ?',
            'params'  => [$ClienteID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        /************************************/
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /************************************/
        // Fila vacía (id inexistente)
        if ($rowData['status'] === true && empty($rowData['data'])) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se decodifican los JSON (URI registradas y metadatos declarados por el cliente)
        $arrRedirectUris = [];
        $arrMetadatos    = [];
        if ($rowData['status'] === true) {
            $decUris = json_decode((string) ($rowData['data']['RedirectUris'] ?? ''), true);
            $decMeta = json_decode((string) ($rowData['data']['Metadatos'] ?? ''), true);
            $arrRedirectUris = is_array($decUris) ? $decUris : [];
            $arrMetadatos    = is_array($decMeta) ? $decMeta : [];
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
                'arrRedirectUris' => $arrRedirectUris,
                'arrMetadatos'    => $arrMetadatos,
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-View.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Editar
    /*******************************************************************/
    public function GetID($f3, $params){

        /************************************/
        // Se no existe el ID
        if (!isset($params['id'])) {
            Response::error('Registro ID inexistente', 400);
        }

        /************************************/
        // Se obtiene el ID
        $ClienteID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        // Se verifica
        if (!$this->isValidDecrypted($ClienteID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idCliente,Nombre,ClientId,idEstado',
            'table'   => 'tokens_oauth_clientes',
            'join'    => '',
            'where'   => 'idCliente = ?',
            'params'  => [$ClienteID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        /************************************/
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /************************************/
        // Fila vacía (id inexistente)
        if ($rowData['status'] === true && empty($rowData['data'])) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Lookup de estados
        $queryEstado = [
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
        $xParamsEstado = ['query' => $queryEstado];
        $arrEstado     = $this->Base_GetList($xParamsEstado);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($rowData['status'] === true && $arrEstado['status'] === true) {

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs' => $this->FormInputs,
                /*=========== Datos Consultados ===========*/
                'rowData'    => $rowData['data'],
                'arrEstado'  => $arrEstado['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-formEdit.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData, $arrEstado]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    /*                                  DATOS                                     */
    /******************************************************************************/
    /*******************************************************************/
    // Se obtiene el listado (sin el hash del secreto)
    /*******************************************************************/
    private function getDataList($filter, $params = []){

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                tokens_oauth_clientes.idCliente,
                tokens_oauth_clientes.Nombre,
                tokens_oauth_clientes.ClientId,
                tokens_oauth_clientes.AuthMethod,
                tokens_oauth_clientes.idEstado,
                tokens_oauth_clientes.FechaRegistro,
                tokens_oauth_clientes.IPRegistro,
                tokens_oauth_clientes.FechaUltimoUso,
                (SELECT COUNT(*) FROM tokens_listado t WHERE t.idClienteOAuth = tokens_oauth_clientes.idCliente) AS NAccesos,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor',
            'table'   => 'tokens_oauth_clientes',
            'join'    => 'LEFT JOIN core_estados ON core_estados.idEstado = tokens_oauth_clientes.idEstado',
            'where'   => $filter,
            'params'  => $params,
            'group'   => '',
            'having'  => '',
            'order'   => 'tokens_oauth_clientes.FechaRegistro DESC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        /************************************/
        // Preparo los datos
        $xParams = ['query' => $query];
        // Se retornan los datos
        return $this->Base_GetList($xParams);
    }

    /*******************************************************************/
    // Editar por post (solo el estado; desactivar revoca los accesos)
    /*******************************************************************/
    public function Update(){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // El POST se reduce a idCliente e idEstado: el resto (incluida la auditoría) se descarta
        $_POST = array_intersect_key($_POST, array_flip(['idCliente', 'idEstado']));

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idCliente,idEstado',
            'required'  => 'idCliente,idEstado',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'tokens_oauth_clientes',
            'where'     => 'idCliente',
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
        // Cascada al desactivar: se revocan refresh, códigos y accesos del cliente
        if ((int) ($_POST['idEstado'] ?? 0) !== 1) {
            // Consultas de la cascada (en este orden)
            $arrCascada = [
                'DELETE FROM tokens_oauth_refresh WHERE idCliente = ?',
                'DELETE FROM tokens_oauth_codigos WHERE idCliente = ?',
                'DELETE FROM tokens_listado WHERE idClienteOAuth = ? AND Origen = 2',
            ];
            // Recorro
            foreach ($arrCascada as $ActionSQL) {
                // Preparo los datos
                $xParamsSQL = [
                    'query'     => $ActionSQL,
                    'newBDConn' => $this->getDBConn(),
                    'showQuery' => false,
                    'singleRow' => false,
                    'params'    => [(int) $_POST['idCliente']],
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
        $ClienteID = $this->Codification->encryptDecrypt('decrypt', $dataDelete['idCliente'] ?? '');
        if (!$this->isValidDecrypted($ClienteID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Se genera la query
        $query = [
            'files'       => '',
            'table'       => 'tokens_oauth_clientes',
            'where'       => 'idCliente',
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
        // Cascada OAuth: refresh, códigos y accesos del cliente (en este orden)
        $arrCascada = [
            'DELETE FROM tokens_oauth_refresh WHERE idCliente = ?',
            'DELETE FROM tokens_oauth_codigos WHERE idCliente = ?',
            'DELETE FROM tokens_listado WHERE idClienteOAuth = ? AND Origen = 2',
        ];
        // Recorro
        foreach ($arrCascada as $ActionSQL) {
            // Preparo los datos
            $xParamsSQL = [
                'query'     => $ActionSQL,
                'newBDConn' => $this->getDBConn(),
                'showQuery' => false,
                'singleRow' => false,
                'params'    => [(int) $ClienteID['data']],
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
            'emptyData'                  => 'idCliente,idEstado',
            'encode'                     => '',
            'ValidarEmail'               => '',
            'ValidarNumero'              => 'idCliente,idEstado',
            'ValidarEntero'              => 'idCliente,idEstado',
            'ValidarRut'                 => '',
            'ValidarPatente'             => '',
            'ValidarFecha'               => '',
            'ValidarHora'                => '',
            'ValidarURL'                 => '',
            'ValidarLargoMinimo'         => '',
            'ValidarLargoMinimoN'        => 3,
            'ValidarLargoMaximoPorCampo' => [
            ],
            'ValidarPalabrasCensuradas'  => '',
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
