<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class maquinasSensoresSubgruposTesting
 *
 * Pruebas del modulo `maquinas` - controlador `maquinasSensoresSubgrupos` (CRUD del
 * catalogo de subgrupos de sensores, dependientes de un grupo).
 *
 * Convenciones del sistema de testeos: ver `maquinasSensoresGruposTesting`.
 * Alcance NO destructivo (escritura por reflexion/fuente; logica pura sin BD).
 *
 * @see skills/testing/SKILL.md
 */
class maquinasSensoresSubgruposTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private const CONTROLADOR      = 'maquinasSensoresSubgrupos';
    private const TABLA            = 'maquinas_sensores_subgrupos';
    private const TABLA_GRUPOS     = 'maquinas_sensores_grupos';
    private const RUTA_WEB         = 'administracion/maquinas/sensores/subgrupos';
    private const RUTA_WEB_LISTADO = 'administracion/maquinas/sensores/subgrupos/listAll';
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
            'PageTitle'       => 'Subgrupos de Sensores',
            'PageDescription' => 'Listado de los subgrupos de sensores.',
            'PageAuthor'      => 'Pruebas',
            'PageKeywords'    => 'Pruebas',
            'TableTitle'      => 'Listado de Subgrupos de Sensores',
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
            'arrGrupos'       => [['ID' => 1, 'Nombre' => 'Temperatura']],
            'arrList'         => [
                'Temperatura' => [
                    ['idSubGrupo' => 1, 'Nombre' => 'Ambiente', 'Grupo' => 'Temperatura'],
                    ['idSubGrupo' => 2, 'Nombre' => 'Motor',    'Grupo' => 'Temperatura'],
                ],
            ],
            'rowData'         => ['idSubGrupo' => 1, 'idGrupo' => 1, 'Nombre' => 'Ambiente'],
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
            'testing'           => $this->rutaModulo('maquinasSensoresSubgruposTesting.php'),
        ];
        foreach ($arrArchivos as $nombre => $ruta) {
            $this->addCase($results, 'archivo_'.$nombre, is_file($ruta), 'no se encuentra '.$ruta);
        }

        $this->addCase($results, 'clase_controlador_cargada', class_exists(self::CONTROLADOR),
            'la clase '.self::CONTROLADOR.' no esta disponible');
        $this->addCase($results, 'clase_testing_cargada', class_exists('maquinasSensoresSubgruposTesting'),
            'la clase maquinasSensoresSubgruposTesting no esta disponible');
        $this->addCase($results, 'herencia_controlador', get_parent_class(self::CONTROLADOR) === 'ControllerBase',
            'el controlador no extiende ControllerBase');
        $this->addCase($results, 'herencia_testing', get_parent_class('maquinasSensoresSubgruposTesting') === 'ControllerBase',
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
        $this->addCase($results, 'join_grupos', str_contains($codigo, 'LEFT JOIN '.self::TABLA_GRUPOS),
            'el controlador no une con el catalogo de grupos');
        $this->addCase($results, 'agrupacion', str_contains($codigo, 'agruparPorClave('),
            'el controlador no agrupa el listado por grupo');
        $this->addCase($results, 'catalogo_grupos', str_contains($codigo, 'getCatalogos('),
            'el controlador no carga el catalogo de grupos para el select');

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

        foreach (['Insert', 'Update', 'Delete'] as $op) {
            $this->addCase($results, 'transaccion_'.$op.'_begin', substr_count($codigo, 'Base_transactionBegin()') === 3,
                $op.'(): se esperaban 3 Base_transactionBegin()');
            $this->addCase($results, 'transaccion_'.$op.'_commit', substr_count($codigo, 'Base_transactionCommit()') === 3,
                $op.'(): se esperaban 3 Base_transactionCommit()');
            $this->addCase($results, 'transaccion_'.$op.'_revertir', substr_count($codigo, 'failOperation(') === 3,
                $op.'(): se esperaban 3 failOperation()');
        }

        $this->addCase($results, 'insert_datacheck', str_contains($codigo, '$this->dataCheck($_POST)'),
            'Insert() no construye el DataCheck con dataCheck($_POST)');
        $this->addCase($results, 'insert_unique_compuesto', str_contains($codigo, "'unique'    => 'idGrupo-Nombre'"),
            'Insert() no declara la unicidad compuesta idGrupo-Nombre');
        $this->addCase($results, 'insert_grupo_requerido', str_contains($codigo, "'required'  => 'idGrupo,Nombre'"),
            'Insert() no exige idGrupo y Nombre');
        $this->addCase($results, 'insert_respuesta', str_contains($codigo, 'Response::success('),
            'Insert() no responde con Response::success()');

        $this->addCase($results, 'update_verbo_post', str_contains($codigo, "REQUEST_METHOD'] !== 'POST'"),
            'Update() no valida el verbo HTTP POST');
        $this->addCase($results, 'update_donde', str_contains($codigo, "'where'     => 'idSubGrupo'"),
            'Update() no delimita la escritura por idSubGrupo');

        $this->addCase($results, 'delete_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'DELETE'"),
            'Delete() no valida el verbo HTTP DELETE');
        $this->addCase($results, 'delete_donde', str_contains($codigo, "'where'       => 'idSubGrupo'"),
            'Delete() no delimita el borrado por idSubGrupo');

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

            $Post      = ['idGrupo' => 1, 'Nombre' => 'Ambiente'];
            $DataCheck = $reflector->invoke($Controller, $Post);

            $this->addCase($results, 'datacheck_es_arreglo', is_array($DataCheck),
                'dataCheck() no devolvio un arreglo');
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post,
                'dataCheck() no conserva el POST recibido');
            $this->addCase($results, 'datacheck_numericos',
                str_contains((string)($DataCheck['ValidarNumero'] ?? ''), 'idGrupo')
                && str_contains((string)($DataCheck['ValidarEntero'] ?? ''), 'idSubGrupo'),
                'dataCheck() no valida idGrupo/idSubGrupo como enteros');
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_es_arreglo', false, 'no se pudo ejecutar dataCheck() ('.$e->getMessage().')');
        }

        try {
            $CommonData = new FunctionsCommonData();
            $agrupado   = $CommonData->agruparPorClave([
                ['Nombre' => 'A', 'Grupo' => 'X'],
                ['Nombre' => 'B', 'Grupo' => 'X'],
                ['Nombre' => 'C', 'Grupo' => 'Y'],
            ], 'Grupo');
            $this->addCase($results, 'agrupar_claves', array_keys($agrupado) === ['X', 'Y'],
                'agruparPorClave() no conserva las claves de grupo');
            $this->addCase($results, 'agrupar_conteo', count($agrupado['X'] ?? []) === 2,
                'agruparPorClave() no reparte las filas por grupo');
        } catch (Throwable $e) {
            $this->addCase($results, 'agrupar_claves', false, 'no se pudo ejecutar agruparPorClave() ('.$e->getMessage().')');
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

        $rutaTabla = $this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php');
        $tabla     = $this->renderizarVista($rutaTabla, $this->datosSinteticos(4), $BASE, $f3);
        $this->addCase($results, 'tabla_render', $tabla !== '', 'la vista UpdateList no produjo HTML');
        $this->addCase($results, 'tabla_cabecera_grupo', str_contains($tabla, 'Temperatura'),
            'la tabla no muestra la cabecera del grupo');
        $this->addCase($results, 'tabla_filas', str_contains($tabla, 'Ambiente') && str_contains($tabla, 'Motor'),
            'la tabla no lista los subgrupos del grupo');
        $this->addCase($results, 'tabla_acciones', str_contains($tabla, 'listTableDataView(')
            && str_contains($tabla, 'listTableDataEdit(') && str_contains($tabla, 'listTableDataDel('),
            'la tabla no expone las acciones de detalle/edicion/borrado con nivel 4');
        $this->addCase($results, 'tabla_sin_acciones_nivel1',
            !str_contains($this->renderizarVista($rutaTabla, $this->datosSinteticos(1), $BASE, $f3), 'listTableDataDel('),
            'con nivel 1 la tabla ofrece borrar');
        $this->addCase($results, 'tabla_escape_entidad', str_contains($tabla, 'addslashes('),
            'UpdateList.php no escapa la etiqueta de la entidad');

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

        return $results;
    }

}
