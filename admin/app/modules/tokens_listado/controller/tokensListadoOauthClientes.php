<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class tokensListadoOauthClientes extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $Codification;

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
        $this->Codification   = new FunctionsSecurityCodification();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /*******************************************************************/
    // Actualizar Listar (clientes OAuth que usan el token)
    /*******************************************************************/
    public function UpdateList($f3, $params){

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
        // Se verifica que el token exista y sea manual (Origen = 1)
        $query = [
            'data'    => 'idToken',
            'table'   => 'tokens_listado',
            'join'    => '',
            'where'   => 'idToken = ? AND Origen = ?',
            'params'  => [(int) $TokenID['data'], 1],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);
        // Se verifica
        if ($rowData['status'] === true && empty($rowData['data'])) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se obtienen los datos
        $arrClientes = $this->getClientes((int) $TokenID['data']);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($rowData['status'] === true && $arrClientes['status'] === true) {

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'  => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrClientes'   => $arrClientes['data'],
                'TokenID'       => $params['id'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-OauthClientes-UpdateList.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData, $arrClientes]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    /*                            CONSULTAS INTERNAS                              */
    /******************************************************************************/
    /*******************************************************************/
    // Clientes que usan el token (vía sus accesos hijos)
    /*******************************************************************/
    private function getClientes(int $idToken){

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                tokens_oauth_clientes.idCliente,
                tokens_oauth_clientes.Nombre,
                tokens_oauth_clientes.ClientId,
                tokens_oauth_clientes.AuthMethod,
                tokens_oauth_clientes.RedirectUris,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor,
                COUNT(tokens_listado.idToken) AS NAccesos,
                MAX(tokens_listado.FechaCreacion) AS UltimaConcesion',
            'table'   => 'tokens_listado',
            'join'    => 'INNER JOIN tokens_oauth_clientes ON tokens_oauth_clientes.idCliente = tokens_listado.idClienteOAuth
                          LEFT JOIN core_estados ON core_estados.idEstado = tokens_oauth_clientes.idEstado',
            'where'   => 'tokens_listado.idTokenPadre = ? AND tokens_listado.Origen = ?',
            'params'  => [$idToken, 2],
            'group'   => 'tokens_oauth_clientes.idCliente',
            'having'  => '',
            'order'   => 'tokens_oauth_clientes.Nombre ASC',
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
    // Revocar los accesos de un cliente con el token
    /*******************************************************************/
    public function Revocar(){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Se descifran y validan los identificadores (llegan cifrados)
        $ClienteID = $this->Codification->encryptDecrypt('decrypt', $_POST['idCliente'] ?? '');
        if (!$this->isValidDecrypted($ClienteID, 'id')) {
            Response::error('Registro inválido', 400);
        }
        $TokenID = $this->Codification->encryptDecrypt('decrypt', $_POST['idToken'] ?? '');
        if (!$this->isValidDecrypted($TokenID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Consultas de la cascada (en este orden): refresh y accesos hijos del cliente con el token
        $arrCascada = [
            'DELETE FROM tokens_oauth_refresh WHERE idTokenAcceso IN (SELECT idToken FROM tokens_listado WHERE idClienteOAuth = ? AND idTokenPadre = ? AND Origen = 2)',
            'DELETE FROM tokens_listado WHERE idClienteOAuth = ? AND idTokenPadre = ? AND Origen = 2',
        ];
        // Recorro
        foreach ($arrCascada as $ActionSQL) {
            // Preparo los datos
            $xParams = [
                'query'     => $ActionSQL,
                'newBDConn' => $this->getDBConn(),
                'showQuery' => false,
                'singleRow' => false,
                'params'    => [(int) $ClienteID['data'], (int) $TokenID['data']],
            ];
            // Ejecuto la query
            $Response = $this->Base_queryExecute($xParams);
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
        // Devuelvo true con código 200 (OK)
        Response::success(true);

    }

}
