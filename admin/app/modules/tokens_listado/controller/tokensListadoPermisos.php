<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class tokensListadoPermisos extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $Codification;
    private $ServerServer;
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
        $this->Codification   = new FunctionsSecurityCodification();
        $this->ServerServer   = new FunctionsServerServer();
        $this->CommonData     = new FunctionsCommonData();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /*******************************************************************/
    // Actualizar Listar (matriz de permisos del token)
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
        // Se verifica que el token exista
        if (!$this->tokenExists((int)$TokenID['data'])) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se obtienen los datos
        $arrModulos = $this->getModulos((int)$TokenID['data']);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrModulos['status'] === true) {

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_CommonData'  => $this->CommonData,
                'Fnc_FormInputs'  => new UIFormInputs(),
                /*=========== Datos Consultados ===========*/
                'rowData'       => ['idToken' => $params['id']],
                'arrModulos'    => $arrModulos['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Permisos-List.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrModulos]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    /*                                  DATOS                                     */
    /******************************************************************************/
    /*******************************************************************/
    // Editar por post (matriz de permisos del token)
    /*******************************************************************/
    public function Update(){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Se obtiene el ID del token (llega cifrado)
        $TokenID = $this->Codification->encryptDecrypt('decrypt', $_POST['idToken'] ?? '');
        // Se verifica
        if (!$this->isValidDecrypted($TokenID, 'id')) {
            Response::error('Registro inválido', 400);
        }
        // Se guarda el ID plano
        $idToken = (int)$TokenID['data'];
        // Se verifica que el token exista
        if (!$this->tokenExists($idToken)) {
            Response::error('Registro inválido', 400);
        }

        /*******************************************************************/
        // Se traen los modulos exponibles e instalados con su estado para el token (S-4: los demas se ignoran)
        $arrModulos = $this->getModulos($idToken);
        // Si falla la consulta
        if ($arrModulos['status'] !== true) {
            Response::error('Error al operar con la Base de Datos', 500, $arrModulos['error'] ?? '');
        }

        /************************************/
        // Se clasifican los modulos (un switch ausente se trata como inactivo)
        $aBorrar   = [];
        $aInsertar = [];
        foreach ($arrModulos['data'] as $modulo) {
            // Se verifica si esta marcado (2 = marcado)
            $marcado = ((int)($_POST['switch_'.$modulo['modulo']] ?? 1) === 2);
            if (!$marcado && $modulo['activo'] === 1) {
                $aBorrar[] = $modulo['modulo'];
            } elseif ($marcado && $modulo['activo'] === 0) {
                $aInsertar[] = $modulo['modulo'];
            }
        }

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Se borran los modulos desmarcados
        foreach ($aBorrar as $nombreModulo) {
            // Se cifran los identificadores
            $TokenIDCod  = $this->Codification->encryptDecrypt('encrypt', $idToken);
            $ModuloIDCod = $this->Codification->encryptDecrypt('encrypt', $nombreModulo);
            // Se verifica
            if (!$this->isValidDecrypted($TokenIDCod, 'text') || !$this->isValidDecrypted($ModuloIDCod, 'text')) {
                // Se revierte la transaccion activa (no-op si no hay transaccion)
                $this->Base_transactionRollback();
                // Se reporta el error
                Response::error('Registro inválido', 400);
            }
            /************************************/
            // Se genera la query
            $query = [
                'files'       => '',
                'table'       => 'tokens_listado_permisos',
                'where'       => 'idToken,Modulo',
                'SubCarpeta'  => '',
                'Post'        => [
                    'idToken' => $TokenIDCod['data'],
                    'Modulo'  => $ModuloIDCod['data'],
                ]
            ];
            // Preparo los datos
            $xParams = ['query' => $query];
            // Ejecuto la query
            $respDel = $this->Base_delete($xParams);
            // Si falla la ejecucion, se revierte de inmediato
            if ($respDel['status'] === false) {
                // Se revierte la transaccion activa (no-op si no hay transaccion)
                $this->Base_transactionRollback();
                // Se reporta el error
                Response::error('Error al operar con la Base de Datos', 500, $respDel['error'] ?? '');
            }
        }

        /************************************/
        // Si hay modulos nuevos marcados, se insertan todos en una sola sentencia
        if ($aInsertar){
            /************************************/
            // Se acumulan las filas
            $rowsModulosNuevos = [];
            foreach ($aInsertar as $nombreModulo) {
                $rowsModulosNuevos[] = [
                    'idToken'       => $idToken,
                    'Modulo'        => $nombreModulo,
                    'fechaCreacion' => $this->ServerServer->fechaActual(),
                ];
            }
            /************************************/
            // Se genera la query
            $query = [
                'data'      => 'idToken,Modulo,fechaCreacion',
                'required'  => 'idToken,Modulo',
                'table'     => 'tokens_listado_permisos',
                'rows'      => $rowsModulosNuevos
            ];
            /************************************/
            // Se genera el chequeo
            $DataCheck = $this->dataCheck('');
            // Preparo los datos
            $xParams = ['DataCheck' => $DataCheck, 'query' => $query];
            // Ejecuto la query
            $respIns = $this->Base_insertMultiple($xParams);
            /************************************/
            // Si falla la ejecucion, se revierte de inmediato
            if ($respIns['status'] === false) {
                // Se revierte la transaccion activa (no-op si no hay transaccion)
                $this->Base_transactionRollback();
                // Se diferencian los errores de validación y de clave duplicada de los de base de datos
                if (is_array($respIns['error'] ?? null)) {
                    Response::error('Datos inválidos', 400, $respIns['error']);
                }
                if (is_string($respIns['data'] ?? null) && preg_match('/\b(1062|23000)\b/', $respIns['data'])) {
                    Response::error('Módulo duplicado', 400);
                }
                // Se reporta el error
                Response::error('Error al operar con la Base de Datos', 500, $respIns['error'] ?? '');
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
        // Devuelvo true con código 200 (OK)
        Response::success(true);

    }

    /******************************************************************************/
    /*                             Métodos privados                               */
    /******************************************************************************/
    /*******************************************************************/
    // Se obtienen los controladores con alguna ruta instalada (modulo instalado)
    /*******************************************************************/
    private function getModulosInstalados(){

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'DISTINCT Controller',
            'table'   => 'core_permisos_listado_rutas',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => '',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        /************************************/
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $resp = $this->Base_GetList($xParams);
        // Si falla la consulta
        if (($resp['status'] ?? false) !== true) {
            return $resp;
        }
        // Se retornan solo los nombres de controlador
        return ['status' => true, 'data' => array_column($resp['data'], 'Controller')];
    }

    /*******************************************************************/
    // Se obtienen los modulos exponibles por el MCP e instalados, con su estado para el token
    /*******************************************************************/
    private function getModulos(int $idToken){

        /************************************/
        // Controladores instalados
        $instalados = $this->getModulosInstalados();
        if ($instalados['status'] !== true) {
            return $instalados;
        }

        /************************************/
        // Modulos asignados al token
        $query = [
            'data'    => 'Modulo',
            'table'   => 'tokens_listado_permisos',
            'join'    => '',
            'where'   => 'idToken = ?',
            'params'  => [$idToken],
            'group'   => '',
            'having'  => '',
            'order'   => '',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        $xParams  = ['query' => $query];
        $asignados = $this->Base_GetList($xParams);
        if (($asignados['status'] ?? false) !== true) {
            return $asignados;
        }
        $activos = array_flip(array_column($asignados['data'], 'Modulo'));

        /************************************/
        // Modulos exponibles (sin BD) que ademas estan instalados
        try {
            $exponibles = InternalFunctionsTokensModulos::listExposable();
        } catch (Throwable $e) {
            error_log('tokensListadoPermisos::getModulos: '.$e->getMessage());
            return ['status' => false, 'error' => 'No se pudo listar los módulos'];
        }
        $data = [];
        foreach ($exponibles as $modulo => $m) {
            if (!array_intersect($m['controladores'], $instalados['data'])) {
                continue;
            }
            $data[] = [
                'modulo'      => $m['modulo'],
                'nombre'      => $m['nombre'],
                'descripcion' => $m['descripcion'],
                'activo'      => isset($activos[$modulo]) ? 1 : 0,
            ];
        }

        /************************************/
        // Se retornan los datos (ya vienen ordenados por nombre)
        return ['status' => true, 'data' => $data];
    }

    /*******************************************************************/
    // Se verifica que el token exista y sea manual (un hijo no tiene matriz)
    /*******************************************************************/
    private function tokenExists(int $idToken): bool {

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idToken',
            'table'   => 'tokens_listado',
            'join'    => '',
            'where'   => 'idToken = ? AND Origen = ?',
            'params'  => [$idToken, 1],
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
        // Existe si la consulta fue correcta y devolvió una fila
        return ($rowData['status'] ?? false) === true && !empty($rowData['data']);
    }

    /*******************************************************************/
    // Se validan los datos
    /*******************************************************************/
    private function dataCheck($POST){
        // Variables
        $DataChecking = [
            'emptyData'                  => '',
            'encode'                     => '',
            'ValidarEmail'               => '',
            'ValidarNumero'              => 'idToken',
            'ValidarEntero'              => 'idToken',
            'ValidarRut'                 => '',
            'ValidarPatente'             => '',
            'ValidarFecha'               => '',
            'ValidarHora'                => '',
            'ValidarURL'                 => '',
            'ValidarLargoMinimo'         => '',
            'ValidarLargoMinimoN'        => 3,
            'ValidarLargoMaximoPorCampo' => ['Modulo' => 64],
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
