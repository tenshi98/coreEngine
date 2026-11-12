<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class tokensListadoOauthAccesos extends ControllerBase {

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
    // Actualizar Listar (accesos OAuth emitidos con el token)
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
        $arrAccesos = $this->getAccesos((int) $TokenID['data']);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($rowData['status'] === true && $arrAccesos['status'] === true) {

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'  => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrAccesos'    => $arrAccesos['data'],
                'TokenID'       => $params['id'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-OauthAccesos-UpdateList.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData, $arrAccesos]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    /*                            CONSULTAS INTERNAS                              */
    /******************************************************************************/
    /*******************************************************************/
    // Accesos OAuth emitidos con el token (nunca se selecciona el valor del Token)
    /*******************************************************************/
    private function getAccesos(int $idToken){

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                tokens_listado.idToken,
                tokens_listado.FechaCreacion,
                tokens_listado.FechaExpiracion,
                (tokens_listado.FechaExpiracion < NOW()) AS Expirado,
                tokens_oauth_clientes.Nombre AS Cliente,
                (SELECT MAX(r.FechaExpiracion) FROM tokens_oauth_refresh r WHERE r.idTokenAcceso = tokens_listado.idToken AND r.Estado = 1 AND r.FechaExpiracion >= NOW()) AS RefreshExpira',
            'table'   => 'tokens_listado',
            'join'    => 'LEFT JOIN tokens_oauth_clientes ON tokens_oauth_clientes.idCliente = tokens_listado.idClienteOAuth',
            'where'   => 'tokens_listado.idTokenPadre = ? AND tokens_listado.Origen = ?',
            'params'  => [$idToken, 2],
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

    /******************************************************************************/
    /*                                  DATOS                                     */
    /******************************************************************************/
    /*******************************************************************/
    // Revocar un acceso OAuth emitido (con su familia de refresh)
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
        // Se descifra y valida el identificador (llega cifrado)
        $AccesoID = $this->Codification->encryptDecrypt('decrypt', $dataDelete['idToken'] ?? '');
        if (!$this->isValidDecrypted($AccesoID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Guarda: solo se revocan accesos OAuth emitidos (Origen = 2)
        $queryGuarda = [
            'data'    => 'idToken',
            'table'   => 'tokens_listado',
            'join'    => '',
            'where'   => 'idToken = ? AND Origen = ?',
            'params'  => [(int) $AccesoID['data'], 2],
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
        // Si no existe o es un token manual, se revierte
        if (empty($ResponseGuarda['data'])) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se reporta el error
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Consultas de la revocación (en este orden): refresh del acceso y el acceso
        $arrCascada = [
            'DELETE FROM tokens_oauth_refresh WHERE idTokenAcceso = ?',
            'DELETE FROM tokens_listado WHERE idToken = ? AND Origen = 2',
        ];
        // Recorro
        foreach ($arrCascada as $ActionSQL) {
            // Preparo los datos
            $xParams = [
                'query'     => $ActionSQL,
                'newBDConn' => $this->getDBConn(),
                'showQuery' => false,
                'singleRow' => false,
                'params'    => [(int) $AccesoID['data']],
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
