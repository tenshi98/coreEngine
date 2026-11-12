<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class InternalFunctionsTokensModulosTesting
 *
 * Pruebas del modulo `tokens` - helper `InternalFunctionsTokensModulos` (modulos que el MCP puede exponer,
 * usados por la matriz de permisos del token y por la migracion del installer; ADR-0019).
 *
 * Alcance: pruebas NO destructivas y sin BD. `listExposable()` es estatico y solo lee el sistema de archivos
 * (installers y manifiestos); por eso la clase no extiende `ControllerBase` ni abre conexion (el runner solo
 * exige que la clase se llame como el archivo y que sus metodos publicos terminen en `Test`). No se escribe nada.
 *
 * @see projects/tokens-permisos-modulo/specs/technical.md §3
 * @see skills/testing/SKILL.md
 */
class InternalFunctionsTokensModulosTesting {

    /*=========== Constantes de las pruebas ===========*/
    private const CLASE = 'InternalFunctionsTokensModulos';

    /******************************************************************************/
    /*                             HELPERS INTERNOS                               */
    /******************************************************************************/
    /*******************************************************************/
    // Registra el resultado de un caso (privado: no se descubre como prueba)
    /*******************************************************************/
    private function addCase(array &$results, string $caso, bool $ok, string $razon = ''){
        $results[$caso] = $ok ? 'OK' : 'FALLO: '.$razon;
    }

    /*******************************************************************/
    // Fuente del helper (pruebas estaticas)
    /*******************************************************************/
    private function fuenteHelper(): string{
        $ruta = __DIR__.'/../functions/'.self::CLASE.'.php';
        return is_file($ruta) ? (string)file_get_contents($ruta) : '';
    }

    /******************************************************************************/
    /*                         MODULOS EXPONIBLES                                  */
    /******************************************************************************/
    /**
     * Cubre los cinco casos del helper: exclusion de modulos ocultos, tablas por modulo, paridad con
     * `ModuleScanner::buildExposable()`, llamadas repetidas y ausencia de acceso a BD.
     *
     * @return array Resultado por caso
     */
    public function listExposableTest(){

        $results = [];

        $existe = class_exists(self::CLASE);
        $this->addCase($results, 'clase_helper_existe', $existe, 'no se carga la clase '.self::CLASE.' (autoload de functions/)');
        if (!$existe) {
            return $results;
        }
        $this->addCase($results, 'metodo_listexposable_publico_estatico',
            method_exists(self::CLASE, 'listExposable') && (new ReflectionMethod(self::CLASE, 'listExposable'))->isStatic()
            && (new ReflectionMethod(self::CLASE, 'listExposable'))->isPublic(),
            self::CLASE.'::listExposable() debe ser public static');

        try {
            $lista = InternalFunctionsTokensModulos::listExposable();
        } catch (Throwable $e) {
            $this->addCase($results, 'list_exposable_ejecuta', false, 'listExposable() lanzo '.$e->getMessage());
            return $results;
        }
        $this->addCase($results, 'list_exposable_ejecuta', is_array($lista), 'listExposable() no devolvio un arreglo');

        /*====================================*/
        // (1) Excluye los modulos ocultos de McpConfig::EXCLUDED_MODULES (installer, tokens)
        /*====================================*/
        $excluidos = class_exists('McpConfig') ? McpConfig::EXCLUDED_MODULES : [];
        $this->addCase($results, 'excluidos_definidos', in_array('installer', $excluidos, true) && in_array('tokens', $excluidos, true),
            'McpConfig::EXCLUDED_MODULES debe contener installer y tokens');
        foreach ($excluidos as $modulo) {
            $this->addCase($results, 'sin_excluido_'.$modulo, !isset($lista[$modulo]), 'listExposable() no debe incluir el modulo oculto '.$modulo);
        }

        /*====================================*/
        // Forma de cada entrada
        /*====================================*/
        $forma = true;
        foreach ($lista as $clave => $m) {
            if (($m['modulo'] ?? null) !== $clave || !is_string($m['nombre'] ?? null) || !is_string($m['descripcion'] ?? null) || !is_array($m['controladores'] ?? null)) {
                $forma = false;
            }
        }
        $this->addCase($results, 'forma_entradas', $forma, 'cada entrada debe traer modulo (= clave), nombre, descripcion y controladores');

        /*====================================*/
        // (2) y (3) Paridad con ModuleScanner::buildExposable() y >= 1 tabla por modulo
        /*====================================*/
        $scannerOk = class_exists('ModuleScanner');
        $this->addCase($results, 'scanner_cargado', $scannerOk, 'el helper debe dejar cargada la clase ModuleScanner');
        if ($scannerOk) {
            $exponibles = (new ModuleScanner())->buildExposable();
            $this->addCase($results, 'paridad_modulos', array_diff(array_keys($exponibles), array_keys($lista)) === []
                && array_diff(array_keys($lista), array_keys($exponibles)) === [],
                'listExposable() y buildExposable() deben devolver los mismos modulos');
            $sinTablas = array_filter(array_keys($lista), static function ($m) use ($exponibles) { return empty($exponibles[$m]['tablas'] ?? []); });
            $this->addCase($results, 'cada_modulo_con_tablas', $sinTablas === [],
                'modulos sin tablas consultables: '.implode(', ', $sinTablas));
            $tablasConModulo = true;
            foreach ($exponibles as $modulo => $m) {
                foreach ($m['tablas'] as $t) {
                    if (($t['modulo'] ?? null) !== $modulo || array_key_exists('permisos', $t)) {
                        $tablasConModulo = false;
                    }
                }
            }
            $this->addCase($results, 'tablas_con_modulo_sin_permisos', $tablasConModulo,
                'cada tabla debe llevar su modulo y ninguna clave permisos');
        }

        /*====================================*/
        // Orden por nombre
        /*====================================*/
        $nombres = array_map(static function ($m) { return mb_strtolower($m['nombre']); }, array_values($lista));
        $ordenados = $nombres;
        sort($ordenados, SORT_STRING);
        $this->addCase($results, 'ordenado_por_nombre', $nombres === $ordenados, 'los modulos deben venir ordenados por nombre');

        /*====================================*/
        // (4) Dos llamadas: sin fallo ni redefinicion de constantes y mismo resultado
        /*====================================*/
        try {
            $segunda = InternalFunctionsTokensModulos::listExposable();
            $this->addCase($results, 'segunda_llamada_igual', $segunda === $lista, 'la segunda llamada debe devolver lo mismo');
        } catch (Throwable $e) {
            $this->addCase($results, 'segunda_llamada_igual', false, 'la segunda llamada lanzo '.$e->getMessage());
        }
        $this->addCase($results, 'constantes_mcp_definidas', defined('MCP_ROOT') && defined('MCP_MODULES_PATH'),
            'el helper debe definir MCP_ROOT y MCP_MODULES_PATH');
        $this->addCase($results, 'constantes_apuntan_al_codigo',
            defined('MCP_MODULES_PATH') && is_dir(MCP_MODULES_PATH.'tokens') && defined('MCP_ROOT') && is_file(MCP_ROOT.'/config/McpConfig.php'),
            'MCP_MODULES_PATH y MCP_ROOT deben apuntar a admin/app/modules/ y admin/MCP/');

        /*====================================*/
        // (5) No consulta la BD ni el catalogo instalado
        /*====================================*/
        $fuente = $this->fuenteHelper();
        $this->addCase($results, 'fuente_leido', $fuente !== '', 'no se pudo leer el fuente del helper');
        $codigo = preg_replace('~/\*.*?\*/|//[^\n]*~s', '', $fuente);
        $this->addCase($results, 'sin_bd', !str_contains($codigo, 'McpDb') && !str_contains($codigo, 'buildCatalog') && !str_contains($codigo, 'isInstalled')
            && !str_contains($codigo, 'QueryBuilder') && !str_contains($codigo, 'Database::'),
            'el helper no debe usar McpDb, buildCatalog, isInstalled, QueryBuilder ni Database');
        $this->addCase($results, 'sin_guarda_mcp_root', !str_contains($codigo, "defined('MCP_ROOT') || exit"),
            'el helper no debe llevar la guarda defined(MCP_ROOT) || exit (lo usa el backoffice)');

        return $results;

    }

    /******************************************************************************/
    /*                    MIGRACION DEL INSTALLER (ESTATICA)                      */
    /******************************************************************************/
    /**
     * Aserciones estaticas, sin BD, sobre el codigo de `tokensInstaller::migrarPermisosTokenAModulos()` (M1-M5):
     * orden, guarda `idPermisos`, `IF EXISTS`/`IF NOT EXISTS`, marcadores `?` en el IN y aborto con escaneo vacio.
     *
     * @return array Resultado por caso
     */
    public function migracionInstallerTest(){

        $results = [];
        $ruta    = __DIR__.'/../installer/tokensInstaller.php';
        $fuente  = is_file($ruta) ? (string)file_get_contents($ruta) : '';
        $this->addCase($results, 'installer_leido', $fuente !== '', 'no se pudo leer tokensInstaller.php');
        if ($fuente === '') {
            return $results;
        }
        $codigo = preg_replace('~/\*.*?\*/|(?<!:)//[^\n]*~s', '', $fuente);

        /*====================================*/
        // Cuerpo del orquestador
        /*====================================*/
        $ok = preg_match('~function migrarPermisosTokenAModulos\(\): bool \{(.*?)\n    \}\n~s', $codigo, $m) === 1;
        $this->addCase($results, 'orquestador_existe', $ok, 'no se encuentra migrarPermisosTokenAModulos()');
        $cuerpo = $ok ? $m[1] : '';

        $pos = [];
        foreach (['migrarAgregarModulo', 'migrarConvertirPermisos', 'migrarDeduplicar', 'migrarDescartarHuerfanos', 'migrarEliminarAntiguo'] as $i => $f) {
            $pos[$i] = strpos($cuerpo, '$this->'.$f.'()');
        }
        $this->addCase($results, 'orden_m1_a_m5', !in_array(false, $pos, true) && $pos === array_values(array_filter($pos, 'is_int')) && $pos === (function ($p) { sort($p); return $p; })($pos),
            'M1..M5 deben invocarse en ese orden');
        $this->addCase($results, 'encadenado_con_and', substr_count($cuerpo, '&&') >= 4, 'los pasos deben encadenarse con && (corta en el primer fallo)');

        $pGuarda = strpos($cuerpo, 'permisosTokenTieneFormaAntigua');
        $this->addCase($results, 'guarda_idpermisos_antes_de_m1', $pGuarda !== false && $pos[0] !== false && $pGuarda < $pos[0]
            && str_contains($codigo, "COLUMN_NAME = 'idPermisos'"), 'la guarda de la columna idPermisos debe ir antes de M1');

        /*====================================*/
        // Aborto con escaneo vacio, antes de M1 (y por tanto de M4/M5)
        /*====================================*/
        $pVacio = strpos($cuerpo, 'listExposable() === []');
        $this->addCase($results, 'aborta_con_escaneo_vacio', $pVacio !== false && $pos[0] !== false && $pVacio < $pos[0]
            && preg_match('~listExposable\(\) === \[\]\)\s*\{[^}]*return false;~s', $cuerpo) === 1,
            'con filas y listExposable() vacio debe devolverse false antes de M1');
        $this->addCase($results, 'captura_throwable_y_false', str_contains($cuerpo, 'catch (Throwable') && str_contains($cuerpo, 'return false;'),
            'una excepcion de listExposable() debe terminar en false');
        $this->addCase($results, 'registra_aborto_sin_datos', str_contains($cuerpo, 'error_log(') && !preg_match('~error_log\([^;]*(\\$_(POST|GET|SERVER)|`?Token`?\s*\.)~', $cuerpo),
            'el aborto debe registrarse con error_log, sin datos de tokens');

        /*====================================*/
        // SQL de cada paso
        /*====================================*/
        $this->addCase($results, 'm1_if_not_exists', str_contains($codigo, 'ADD COLUMN IF NOT EXISTS Modulo'), 'M1 debe usar ADD COLUMN IF NOT EXISTS');
        $this->addCase($results, 'm2_solo_modulo_null', str_contains($codigo, 'tokens_listado_permisos.Modulo IS NULL AND'), 'M2 solo debe tocar filas con Modulo IS NULL');
        $this->addCase($results, 'm2_marcadores_in', str_contains($codigo, "array_fill(0, count(\$m['controladores']), '?')") && str_contains($codigo, "IN (' . \$marcadores . ')"),
            'M2 debe enlazar los controladores con ? en el IN');
        $this->addCase($results, 'm2_sin_concatenar_datos', !preg_match("~IN \('\s*\.\s*\\\$m\[~", $codigo), 'M2 no debe concatenar controladores en el SQL');
        $this->addCase($results, 'm4_delete_modulo_null', str_contains($codigo, 'DELETE FROM tokens_listado_permisos WHERE Modulo IS NULL;'), 'M4 debe borrar solo Modulo IS NULL');
        $this->addCase($results, 'm4_registra_conteo', preg_match('~function migrarDescartarHuerfanos.*?contarPermisosToken.*?error_log~s', $codigo) === 1,
            'M4 debe contar y registrar las filas que descarta');
        $this->addCase($results, 'm5_if_exists', str_contains($codigo, 'DROP INDEX IF EXISTS uk_tkperm_token_permiso')
            && str_contains($codigo, 'DROP COLUMN IF EXISTS idPermisos') && str_contains($codigo, 'DROP COLUMN IF EXISTS idLevelLimit'),
            'M5 debe usar DROP ... IF EXISTS (reanudable)');
        $this->addCase($results, 'm5_modulo_not_null', preg_match('~MODIFY Modulo varchar\(64\)[^;]*NOT NULL;~', $codigo) === 1, 'M5 debe fijar Modulo NOT NULL');

        /*====================================*/
        // Llamada desde InstallModule: tras crear tablas y antes de las optimizaciones (indice unico)
        /*====================================*/
        $pCrear = strpos($codigo, 'Base_createTable');
        $pMig   = strpos($codigo, 'if (!$this->migrarPermisosTokenAModulos())');
        $pOpt   = strpos($codigo, 'foreach ($arrOptimizations as $query)');
        $this->addCase($results, 'install_orden_tablas_migracion_indices', $pCrear !== false && $pMig !== false && $pOpt !== false && $pCrear < $pMig && $pMig < $pOpt,
            'InstallModule: crear tablas, migrar, luego optimizaciones');

        return $results;

    }

}
