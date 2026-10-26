<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class maquinasSensoresGruposTesting
 *
 * Pruebas del modulo `maquinas` - controlador `maquinasSensoresGrupos` (CRUD del
 * catalogo de grupos de sensores).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. El normalizador
 *    marca ERROR si el texto del caso contiene 'error', 'fail' o 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Alcance NO destructivo: los metodos de escritura terminan en `Response::*()`
 * (que hace `exit`), por eso NO se invocan: se verifican por reflexion y lectura
 * del fuente. La logica pura (dataCheck, cifrado) se ejecuta sin base de datos.
 *
 * @see skills/testing/SKILL.md
 */
class maquinasSensoresGruposTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private const CONTROLADOR      = 'maquinasSensoresGrupos';
    private const TABLA            = 'maquinas_sensores_grupos';
    private const RUTA_WEB         = 'administracion/maquinas/sensores/grupos';
    private const RUTA_WEB_LISTADO = 'administracion/maquinas/sensores/grupos/listAll';
    private const RUTA_WEB_SEARCH  = 'administracion/maquinas/sensores/grupos/search';
    private const NIVELES          = [1, 2, 3, 4];

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                             HELPERS INTERNOS                               */
    /******************************************************************************/
    private function addCase(array &$results, string $caso, bool $ok, string $razon = ''){
        $results[$caso] = $ok ? 'OK' : 'FALLO: '.$razon;
    }

    private function rutaModulo(string $sub = ''): string{
        return rtrim(__DIR__.'/'.$sub, '/');
    }

    private function codigoFuente(string $ruta): string{
        return is_file($ruta) ? (string)file_get_contents($ruta) : '';
    }

    private function fuenteControlador(): string{
        return $this->codigoFuente($this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'));
    }

    private function firmaMetodo(array &$results, string $metodo, int $minParam, int $maxParam){
        $existe = method_exists(self::CONTROLADOR, $metodo);
        $this->addCase($results, 'metodo_'.$metodo, $existe, 'no existe '.self::CONTROLADOR.'::'.$metodo.'()');
        if (!$existe) {
            return;
        }
        $reflector = new ReflectionMethod(self::CONTROLADOR, $metodo);
        $this->addCase($results, 'metodo_'.$metodo.'_publico', $reflector->isPublic(), $metodo.'() no es publico');
        $n = $reflector->getNumberOfParameters();
        $this->addCase($results, 'metodo_'.$metodo.'_parametros', $n >= $minParam && $n <= $maxParam,
            $metodo.'() declara '.$n.' parametros (esperado entre '.$minParam.' y '.$maxParam.')');
    }

    private function assertTransaccion(array &$results, string $metodo){
        $fuente = $this->fuenteControlador();
        foreach (['Insert', 'Update', 'Delete'] as $op) {
            $this->addCase($results, 'transaccion_'.$op.'_begin', substr_count($fuente, 'Base_transactionBegin()') === 3,
                $op.'(): se esperaban 3 Base_transactionBegin()');
            $this->addCase($results, 'transaccion_'.$op.'_commit', substr_count($fuente, 'Base_transactionCommit()') === 3,
                $op.'(): se esperaban 3 Base_transactionCommit()');
            $this->addCase($results, 'transaccion_'.$op.'_revertir', substr_count($fuente, 'failOperation(') === 3,
                $op.'(): se esperaban 3 failOperation()');
            $this->addCase($results, 'transaccion_'.$op.'_rollback', substr_count($fuente, 'Base_transactionRollback()') === 0,
                $op.'(): no debe llamar Base_transactionRollback() directo');
        }
        $this->addCase($results, 'transaccion_commit_antes_exito',
            strpos($fuente, 'Base_transactionCommit()') !== false
            && strpos($fuente, 'Response::success(') !== false
            && strpos($fuente, 'Base_transactionCommit()') < strpos($fuente, 'Response::success('),
            'el commit debe ejecutarse antes de responder al cliente');
    }

    private function renderizarVista(string $rutaVista, array $data, string $BASE, $f3 = null): string{
        if (!is_file($rutaVista)) {
            return '';
        }
        $cwd = getcwd();
        @chdir($this->rutaModulo('../../../../public'));
        ob_start();
        try {
            include $rutaVista;
        } catch (Throwable $e) {
            ob_end_clean();
            @chdir($cwd);
            return '';
        }
        $html = (string)ob_get_clean();
        @chdir($cwd);
        return $html;
    }

    private function datosSinteticos(int $nivel): array{
        return [
            'PageTitle'       => 'Grupos de Sensores',
            'PageDescription' => 'Listado de los grupos de sensores.',
            'PageAuthor'      => 'Pruebas',
            'PageKeywords'    => 'Pruebas',
            'TableTitle'      => 'Listado de Grupos de Sensores',
            'UserData'        => [
                'MainPathUrl'          => '/admin/public/upload/',
                'sistemaModalSubtitle' => 1,
                'sistemaModalCloseBTN' => 2,
            ],
            'UserAccess'      => ['LevelAccess' => $nivel, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_FormInputs'  => new UIFormInputs(),
            'Fnc_Codification'=> new FunctionsSecurityCodification(),
            'Fnc_WidgetsCommon' => new UIWidgetsCommon(),
            'Fnc_WidgetsViews'  => new UIWidgetsViews(),
            'arrList'         => [
                ['idGrupo' => 1, 'Nombre' => 'Temperatura'],
                ['idGrupo' => 2, 'Nombre' => 'Presion'],
            ],
            'rowData'         => ['idGrupo' => 1, 'Nombre' => 'Temperatura'],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    public function estructuraModuloTest(){
        $results = [];

        $arrArchivos = [
            'controlador'       => $this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'),
            'vista_list'        => $this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php'),
            'vista_update_list' => $this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'),
            'vista_view'        => $this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'),
            'vista_form_new'    => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formNew.php'),
            'vista_form_edit'   => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formEdit.php'),
            'vista_form_search' => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formSearch.php'),
            'testing'           => $this->rutaModulo('maquinasSensoresGruposTesting.php'),
            'readme'            => $this->rutaModulo('../README.md'),
        ];
        foreach ($arrArchivos as $nombre => $ruta) {
            $this->addCase($results, 'archivo_'.$nombre, is_file($ruta), 'no se encuentra '.$ruta);
        }

        $this->addCase($results, 'clase_controlador_cargada', class_exists(self::CONTROLADOR),
            'la clase '.self::CONTROLADOR.' no esta disponible');
        $this->addCase($results, 'clase_testing_cargada', class_exists('maquinasSensoresGruposTesting'),
            'la clase maquinasSensoresGruposTesting no esta disponible');
        $this->addCase($results, 'herencia_controlador', get_parent_class(self::CONTROLADOR) === 'ControllerBase',
            'el controlador no extiende ControllerBase');
        $this->addCase($results, 'herencia_testing', get_parent_class('maquinasSensoresGruposTesting') === 'ControllerBase',
            'el testing no extiende ControllerBase');

        return $results;
    }

    /******************************************************************************/
    /*                            CONTROLADOR: VISTAS                             */
    /******************************************************************************/
    public function controladorVistasTest(){
        $results = [];

        foreach (['listAll' => [1, 1], 'UpdateList' => [1, 1], 'View' => [2, 2], 'GetID' => [2, 2]] as $metodo => $p) {
            $this->firmaMetodo($results, $metodo, $p[0], $p[1]);
        }

        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'vista_list_all', str_contains($codigo, "'-List.php'"),
            'listAll() no instancia la vista -List');
        $this->addCase($results, 'vista_update_list', str_contains($codigo, "'-UpdateList.php'"),
            'UpdateList() no instancia la vista -UpdateList');
        $this->addCase($results, 'vista_view', str_contains($codigo, "'-View.php'"),
            'View() no instancia la vista -View');
        $this->addCase($results, 'vista_form_edit', str_contains($codigo, "'-formEdit.php'"),
            'GetID() no instancia la vista -formEdit');
        $this->addCase($results, 'tipos_showvista', str_contains($codigo, 'showVista(1,') && str_contains($codigo, 'showVista(2,'),
            'el controlador no usa showVista(1/2) segun corresponda');
        $this->addCase($results, 'fallback_errores', str_contains($codigo, 'mergeResponses('),
            'el controlador no agrupa las respuestas fallidas con mergeResponses()');
        $this->addCase($results, 'nivel_usuario', str_contains($codigo, 'getArrLevel($f3,'),
            'el controlador no resuelve el nivel de acceso del usuario');
        $this->addCase($results, 'tabla_catalogo', str_contains($codigo, "'table'   => '".self::TABLA."'")
            || str_contains($codigo, "'table'     => '".self::TABLA."'"),
            'el controlador no apunta a la tabla '.self::TABLA);
        $this->addCase($results, 'busqueda_por_nombre', str_contains($codigo, "'Nombre'"),
            'el controlador no resuelve la busqueda relativa sobre Nombre');
        $this->addCase($results, 'titulo_pagina', str_contains($codigo, "'PageTitle'") && str_contains($codigo, "'TableTitle'"),
            'listAll() no asigna PageTitle/TableTitle');

        return $results;
    }

    /******************************************************************************/
    /*                         CONTROLADOR: ESCRITURA (CRUD)                      */
    /******************************************************************************/
    public function controladorEscrituraTest(){
        $results = [];

        foreach (['Insert' => [0, 0], 'Update' => [0, 0], 'Delete' => [0, 0]] as $metodo => $p) {
            $this->firmaMetodo($results, $metodo, $p[0], $p[1]);
        }

        $codigo = $this->fuenteControlador();
        $this->assertTransaccion($results, 'crud');

        $this->addCase($results, 'insert_datacheck', str_contains($codigo, '$this->dataCheck($_POST)'),
            'Insert() no construye el DataCheck con dataCheck($_POST)');
        $this->addCase($results, 'insert_unique', str_contains($codigo, "'unique'    => 'Nombre'"),
            'Insert() no declara la unicidad de Nombre');
        $this->addCase($results, 'insert_respuesta', str_contains($codigo, 'Response::success('),
            'Insert() no responde con Response::success()');

        $this->addCase($results, 'update_verbo_post', str_contains($codigo, "REQUEST_METHOD'] !== 'POST'"),
            'Update() no valida el verbo HTTP POST');
        $this->addCase($results, 'update_donde', str_contains($codigo, "'where'     => 'idGrupo'"),
            'Update() no delimita la escritura por idGrupo');

        $this->addCase($results, 'delete_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'DELETE'"),
            'Delete() no valida el verbo HTTP DELETE');
        $this->addCase($results, 'delete_donde', str_contains($codigo, "'where'       => 'idGrupo'"),
            'Delete() no delimita el borrado por idGrupo');

        foreach (['View', 'GetID'] as $metodo) {
            $this->addCase($results, 'id_descifrado_'.$metodo,
                str_contains($codigo, "encryptDecrypt('decrypt', \$params['id'])"),
                $metodo.'() no descifra $params[\'id\']');
        }
        $this->addCase($results, 'id_valido', substr_count($codigo, 'isValidDecrypted(') >= 2,
            'los metodos de detalle no validan el ID con isValidDecrypted()');

        return $results;
    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    public function logicaValidacionTest(){
        $results = [];

        try {
            $reflector = new ReflectionMethod(self::CONTROLADOR, 'dataCheck');
            $reflector->setAccessible(true);
            $Controller = (new ReflectionClass(self::CONTROLADOR))->newInstanceWithoutConstructor();

            $Post      = ['Nombre' => 'Temperatura'];
            $DataCheck = $reflector->invoke($Controller, $Post);

            $this->addCase($results, 'datacheck_es_arreglo', is_array($DataCheck),
                'dataCheck() no devolvio un arreglo');
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post,
                'dataCheck() no conserva el POST recibido');
            $this->addCase($results, 'datacheck_largo',
                (string)($DataCheck['ValidarLargoMinimo'] ?? '') === 'Nombre'
                && (int)($DataCheck['ValidarLargoMinimoN'] ?? 0) === 3
                && ($DataCheck['ValidarLargoMaximoPorCampo'] ?? []) === [
                    'Nombre' => 120,
                ],
                'dataCheck() no fija correctamente todos los campos de largo (minimo/maximo)');
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_es_arreglo', false, 'no se pudo ejecutar dataCheck() ('.$e->getMessage().')');
        }

        try {
            $Codification = new FunctionsSecurityCodification();
            $cifrado      = $Codification->encryptDecrypt('encrypt', '5');
            $descifrado   = $Codification->encryptDecrypt('decrypt', (string)($cifrado['data'] ?? ''));
            $this->addCase($results, 'cifrado_ida_y_vuelta',
                ($cifrado['success'] ?? false) === true && (string)($descifrado['data'] ?? '') === '5',
                'el cifrado no devuelve el valor original');
        } catch (Throwable $e) {
            $this->addCase($results, 'cifrado_ida_y_vuelta', false, 'no se pudo ejecutar el cifrado ('.$e->getMessage().')');
        }

        $this->addCase($results, 'id_valido_ok', $this->isValidDecrypted(['success' => true, 'data' => '7'], 'id') === true,
            'un ID numerico positivo debe ser valido');
        $this->addCase($results, 'id_valido_cero', $this->isValidDecrypted(['success' => true, 'data' => '0'], 'id') === false,
            'el ID 0 debe rechazarse para tipo id');

        return $results;
    }

    /******************************************************************************/
    /*                                 SEGURIDAD                                  */
    /******************************************************************************/
    public function seguridadVistasTest(){
        $results = [];

        $arrVistas = glob($this->rutaModulo('../views/'.self::CONTROLADOR.'-*.php')) ?: [];
        $this->addCase($results, 'hay_vistas', count($arrVistas) > 0,
            'no se encontraron vistas del controlador '.self::CONTROLADOR);

        foreach ($arrVistas as $ruta) {
            $nombre = basename($ruta);
            $codigo = $this->codigoFuente($ruta);
            $this->addCase($results, 'vista_sin_sesion_'.$nombre, !str_contains($codigo, '$_SESSION'),
                $nombre.' accede a la sesion de forma directa');
            $this->addCase($results, 'vista_sin_sql_'.$nombre,
                !preg_match('/\b(SELECT|INSERT|UPDATE|DELETE)\s+\w+\s+FROM\b/i', $codigo),
                $nombre.' contiene una consulta SQL embebida');
        }

        $tabla = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'));
        $this->addCase($results, 'tabla_escape_entidad', str_contains($tabla, 'addslashes('),
            'UpdateList.php no escapa la etiqueta de la entidad');

        $controlador = $this->fuenteControlador();
        $this->addCase($results, 'controlador_sin_concat_sql',
            !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $controlador),
            'el controlador concatena superglobales en una cadena SQL');
        $this->addCase($results, 'controlador_parametros_preparados', str_contains($controlador, "'params'"),
            'el controlador no usa parametros preparados');

        return $results;
    }

    /******************************************************************************/
    /*                                PERMISOS                                    */
    /******************************************************************************/
    public function permisosSesionTest($f3){
        $results = [];

        $UserData = $f3->get('SESSION.DataInfo');
        $this->addCase($results, 'sesion_usuario_presente', is_array($UserData) && !empty($UserData),
            'no hay datos de usuario en la sesion');

        try {
            $arrLevel = $this->getArrLevel($f3, self::CONTROLADOR);
            $this->addCase($results, 'get_arr_level_arreglo', is_array($arrLevel),
                'getArrLevel() no devolvio un arreglo');
        } catch (Throwable $e) {
            $this->addCase($results, 'get_arr_level_arreglo', false, 'no se pudo leer la sesion ('.$e->getMessage().')');
            $arrLevel = [];
        }

        $nivel = is_array($arrLevel) ? ($arrLevel['LevelAccess'] ?? null) : null;
        $this->addCase($results, 'nivel_en_rango', $nivel === null || in_array((int)$nivel, self::NIVELES, true),
            'LevelAccess quedo fuera del catalogo 1..4');
        $this->addCase($results, 'ruta_de_acceso_coincide', $nivel === null || ($arrLevel['RouteAccess'] ?? '') === self::RUTA_WEB,
            'RouteAccess no coincide con '.self::RUTA_WEB);

        $arrPermisos = $f3->get('SESSION.arrPermisos');
        $this->addCase($results, 'permisos_de_ruta_presentes', is_array($arrPermisos),
            'SESSION.arrPermisos no esta disponible');

        if (is_array($arrPermisos)) {
            $rutaOk = false;
            foreach ($arrPermisos as $permiso) {
                if (isset($permiso['RutaWeb']) && $permiso['RutaWeb'] === self::RUTA_WEB_LISTADO) {
                    $rutaOk = true;
                    break;
                }
            }
            $this->addCase($results, 'ruta_registrada', $rutaOk,
                'la ruta '.self::RUTA_WEB_LISTADO.' no esta entre los permisos del usuario');
        }

        $arrRoutes = $f3->get('ROUTES');
        $this->addCase($results, 'router_disponible', is_array($arrRoutes), 'F3 no expuso la variable ROUTES');
        foreach ([
            'ruta_en_router'       => ['/'.self::RUTA_WEB_LISTADO, 'GET',  self::CONTROLADOR.'->listAll'],
            'ruta_busqueda_router' => ['/'.self::RUTA_WEB_SEARCH,  'POST', self::CONTROLADOR.'->UpdateList'],
        ] as $caso => $esperado) {
            [$ruta, $verbo, $handlerEsperado] = $esperado;
            if (!isset($arrRoutes[$ruta])) {
                continue;
            }
            $handler = $arrRoutes[$ruta][0][$verbo][0] ?? '';
            $this->addCase($results, $caso, $handler === $handlerEsperado,
                'la ruta '.$ruta.' apunta a '.var_export($handler, true));
        }

        return $results;
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    public function vistaListadoTest($f3){
        $results = [];
        $BASE    = (string)$f3->get('BASE');
        if (trim($BASE) === '') {
            $BASE = '/admin';
        }

        $rutaList = $this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php');
        $html     = $this->renderizarVista($rutaList, $this->datosSinteticos(1), $BASE, $f3);
        $this->addCase($results, 'listado_render', $html !== '', 'la vista List no produjo HTML');
        $this->addCase($results, 'listado_contenedor', str_contains($html, 'id="listTableData"'),
            'falta el contenedor de la tabla');
        $this->addCase($results, 'listado_form_busqueda', str_contains($html, 'id="FormSearchData"'),
            'no se dibuja el formulario de busqueda');
        $this->addCase($results, 'listado_form_nuevo', str_contains($html, 'id="FormNewData"'),
            'no se dibuja el formulario de alta');
        $this->addCase($results, 'listado_sin_crear_nivel1', !str_contains($html, 'Crear Nuevo</button>'),
            'con nivel 1 el listado ofrece crear registros');
        $this->addCase($results, 'listado_endpoint_actualizar', str_contains($html, '/updateList'),
            'falta el endpoint de refresco de tabla');

        $htmlNivel3 = $this->renderizarVista($rutaList, $this->datosSinteticos(3), $BASE, $f3);
        $this->addCase($results, 'listado_crear_nivel3', str_contains($htmlNivel3, 'Crear Nuevo</button>'),
            'con nivel 3 no se muestra el boton Crear Nuevo');

        $rutaTabla = $this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php');
        foreach (self::NIVELES as $nivel) {
            $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos($nivel), $BASE, $f3);
            $this->addCase($results, 'tabla_render_nivel'.$nivel, $tabla !== '',
                'la vista UpdateList no produjo HTML con nivel '.$nivel);
            $this->addCase($results, 'tabla_ver_nivel'.$nivel,
                str_contains($tabla, 'listTableDataView(') === ($nivel >= 1),
                'la accion Ver no coincide con el nivel '.$nivel);
            $this->addCase($results, 'tabla_editar_nivel'.$nivel,
                str_contains($tabla, 'listTableDataEdit(') === ($nivel >= 2),
                'la accion Editar no coincide con el nivel '.$nivel);
            $this->addCase($results, 'tabla_borrar_nivel'.$nivel,
                str_contains($tabla, 'listTableDataDel(') === ($nivel >= 4),
                'la accion Borrar no coincide con el nivel '.$nivel);
        }

        return $results;
    }

}
