<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class informeReposicionTesting
 *
 * Pruebas del modulo `bodegas` - controlador `informeReposicion` (informe predictivo de reposicion,
 * ADR-0020: tipo Informe con el metodo `Recalcular`).
 *
 * Convenciones del sistema de testeos: toda prueba es un metodo publico que termina en "Test"
 * y devuelve [caso => 'OK'|'FALLO: razon'] (docs/architecture/testing/contract.md).
 *
 * Alcance: pruebas NO destructivas y sin escribir en la base de datos.
 *  - `Recalcular` NO se ejecuta (entrena y escribe en `bodegas_pred_*`, y `Response::error` termina
 *    el proceso): su contrato (POST, nivel >= 2, 405/403/400, transacciones) se verifica sobre el fuente.
 *  - `trainReposicion` (logica pura, sin BD) se ejecuta con datos sinteticos.
 *  - Las vistas se renderizan con datos sinteticos.
 *
 * @see skills/testing/SKILL.md
 */
class informeReposicionTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR = 'informeReposicion';
    private const RUTA_WEB    = 'gestionBodegas/productos/reposicion';
    // Tolerancia numerica de las comparaciones
    private const DELTA       = 0.01;

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

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
    // Ruta absoluta dentro del modulo (la carpeta testing/ es la base)
    /*******************************************************************/
    private function rutaModulo(string $sub = ''): string{
        return rtrim(__DIR__.'/'.$sub, '/');
    }

    /*******************************************************************/
    // Lectura del fuente de un archivo del modulo
    /*******************************************************************/
    private function codigoFuente(string $ruta): string{
        return is_file($ruta) ? (string)file_get_contents($ruta) : '';
    }

    /*******************************************************************/
    // Fuente del controlador bajo prueba
    /*******************************************************************/
    private function fuenteControlador(): string{
        return $this->codigoFuente($this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'));
    }

    /*******************************************************************/
    // Fuente del instalador del modulo (registro de rutas, permisos y tablas)
    /*******************************************************************/
    private function fuenteInstalador(): string{
        return $this->codigoFuente($this->rutaModulo('../installer/bodegasInstaller.php'));
    }

    /*******************************************************************/
    // Cuerpo (aproximado) de un metodo del controlador a partir de su fuente
    /*******************************************************************/
    private function cuerpoMetodo(string $codigo, string $metodo): string{
        $pos = preg_match('/function\s+'.preg_quote($metodo, '/').'\s*\(/', $codigo, $m, PREG_OFFSET_CAPTURE) === 1 ? $m[0][1] : false;
        if ($pos === false) {
            return '';
        }
        $sig = preg_match('/\n\s*(public|private|protected)\s+function\s/', $codigo, $n, PREG_OFFSET_CAPTURE, $pos + 10) === 1 ? $n[0][1] : strlen($codigo);
        return substr($codigo, $pos, $sig - $pos);
    }

    /*******************************************************************/
    // Orden de los pasos de un metodo (posiciones crecientes en el cuerpo)
    /*******************************************************************/
    private function ordenado(string $cuerpo, array $pasos): bool{
        $pos = -1;
        foreach ($pasos as $paso) {
            $p = strpos($cuerpo, $paso);
            if ($p === false || $p <= $pos) {
                return false;
            }
            $pos = $p;
        }
        return true;
    }

    /*******************************************************************/
    // Rutas del instalador de este controlador: [ 'idMetodo RutaWeb' => [handler, nivel] ]
    /*******************************************************************/
    private function rutasInstalador(): array{
        $rutas = [];
        foreach (preg_split('/\r\n|\r|\n/', $this->fuenteInstalador()) as $linea) {
            if (!str_contains($linea, self::CONTROLADOR.'->')) {
                continue;
            }
            $ok = preg_match(
                "/'idMetodo'\s*=>\s*(\d+)\s*,\s*'RutaWeb'\s*=>\s*'([^']+)'\s*,\s*'RutaController'\s*=>\s*'([^']+)'.*?'idLevelLimit'\s*=>\s*'?(\d+)'?/",
                $linea, $m
            );
            if ($ok === 1) {
                $rutas[$m[1].' '.$m[2]] = ['handler' => $m[3], 'nivel' => (int)$m[4]];
            }
        }
        return $rutas;
    }

    /*******************************************************************/
    // Renderiza una vista del modulo con datos sinteticos y devuelve el HTML
    // (el CWD se mueve a admin/public: las plantillas de los inputs usan rutas relativas)
    /*******************************************************************/
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

    /*******************************************************************/
    // Datos sinteticos de las vistas (nivel de acceso y ejecucion parametrizables)
    /*******************************************************************/
    private function datosSinteticos(int $nivel, bool $conEjecucion): array{
        return [
            'PageTitle'       => 'Reposición Predictiva',
            'TableTitle'      => 'Reposición Predictiva',
            'UserData' => [
                'MainPathUrl'          => '/admin/public/upload/',
                'sistemaModalSubtitle' => 1,
                'sistemaModalCloseBTN' => 2,
            ],
            'UserAccess' => ['LevelAccess' => $nivel, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_FormInputs'   => new UIFormInputs(),
            'Fnc_Codification' => new FunctionsSecurityCodification(),
            'Fnc_DataDate'     => new FunctionsDataDate(),
            'Fnc_DataNumbers'  => new FunctionsDataNumbers(),
            'arrProductos'     => [['ID' => 3, 'Nombre' => 'Producto Demo']],
            'arrRun'           => $conEjecucion ? [
                'idEjecucion' => 5, 'idUsuario' => 1, 'Estado' => 2, 'Algoritmo' => 'LeastSquares',
                'FechaInicio' => '2026-11-12 10:00:00', 'FechaFin' => '2026-11-12 10:00:08', 'DuracionMs' => 8420,
                'MuestrasOrigen' => 12, 'Muestras' => 10,
                'Metricas' => json_encode(['mae' => 1.5, 'rmse' => 2.25, 'maeBase' => 2.5, 'rmseBase' => 3.5, 'productos' => 10]),
                'Modelo'   => json_encode(['semanas' => 12]),
            ] : [],
            'arrList' => $conEjecucion ? [
                ['idProducto' => 3, 'SemanasUsadas' => 12, 'DemandaMedia' => '10.00', 'DemandaPredicha' => '12.50', 'Pendiente' => '0.5000',
                 'StockActual' => '5.00', 'StockLimite' => null, 'Cobertura' => '0.40', 'Sugerida' => '7.50', 'Alerta' => 3,
                 'Producto' => 'Producto <Demo>', 'UniMed' => 'Un'],
            ] : [],
        ];
    }

    /*******************************************************************/
    // Semanas YEARWEEK(modo 3) de la ventana, de la mas antigua a la mas reciente
    /*******************************************************************/
    private function semanasVentana(string $hoy, int $n): array{
        $lunes = new DateTime($hoy);
        $lunes->modify('monday this week');
        $out = [];
        for ($i = $n; $i >= 1; $i--) {
            $d = clone $lunes;
            $d->modify('-'.$i.' week');
            $out[] = (int)$d->format('oW');
        }
        return $out;
    }

    /******************************************************************************/
    /*                           ESTRUCTURA Y CONTRATO                            */
    /******************************************************************************/
    /**
     * Archivos del controlador y metodos publicos exactos (__construct, listAll, UpdateList, Recalcular).
     */
    public function estructuraContratoTest(){
        $results = [];
        $codigo  = $this->fuenteControlador();

        foreach ([
            'controlador' => '../controller/'.self::CONTROLADOR.'.php',
            'vista_list'  => '../views/'.self::CONTROLADOR.'-List.php',
            'vista_lista' => '../views/'.self::CONTROLADOR.'-UpdateList.php',
            'testing'     => 'informeReposicionTesting.php',
        ] as $nombre => $ruta) {
            $this->addCase($results, 'archivo_'.$nombre, is_file($this->rutaModulo($ruta)), 'no existe '.$ruta);
        }

        $this->addCase($results, 'clase_existe', class_exists(self::CONTROLADOR), 'la clase no se autocarga');
        if (class_exists(self::CONTROLADOR)) {
            $ref      = new ReflectionClass(self::CONTROLADOR);
            $publicos = [];
            foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $m) {
                if ($m->class === self::CONTROLADOR) { $publicos[] = $m->getName(); }
            }
            sort($publicos);
            $this->addCase($results, 'metodos_publicos', $publicos === ['Recalcular', 'UpdateList', '__construct', 'exportExcel', 'listAll'],
                'metodos publicos: '.implode(',', $publicos));
            foreach (['armarFiltro', 'nombresFiltro', 'getLastRun', 'getDataList', 'getProductos', 'getDatasetDemanda', 'getStockProductos', 'trainReposicion', 'startRun', 'saveRun', 'failRun'] as $privado) {
                $ok = method_exists(self::CONTROLADOR, $privado) && (new ReflectionMethod(self::CONTROLADOR, $privado))->isPrivate();
                $this->addCase($results, 'privado_'.$privado, $ok, $privado.'() debe existir y ser privado');
            }
        }

        // Sin View/Print ni otros metodos de escritura (v1)
        $this->addCase($results, 'sin_view_print_insert',
            !preg_match('/function\s+(View|Print|Insert|Update|Delete|GetID)\s*\(/', $codigo),
            'el informe declara metodos que no corresponden');

        // listAll solo lookups y ultima ejecucion
        $listAll = $this->cuerpoMetodo($codigo, 'listAll');
        $this->addCase($results, 'listAll_sin_getDataList', $listAll !== '' && !str_contains($listAll, 'getDataList('), 'listAll no debe leer el resultado');
        $this->addCase($results, 'listAll_publica_arrRun', str_contains($listAll, "'arrRun'") && str_contains($listAll, 'getLastRun('), 'listAll debe publicar arrRun');

        // UpdateList: filtros y ultima ejecucion
        $upd = $this->cuerpoMetodo($codigo, 'UpdateList');
        $arm = $this->cuerpoMetodo($codigo, 'armarFiltro');
        $this->addCase($results, 'UpdateList_filtros', str_contains($arm, "'idProducto,Alerta'") && str_contains($arm, 'TABLA_RES') && str_contains($upd, '$this->armarFiltro()'), 'filtros idProducto,Alerta (en armarFiltro, usado por UpdateList)');
        $this->addCase($results, 'UpdateList_arrRun', str_contains($upd, "'arrRun'") && str_contains($upd, 'getLastRun('), 'UpdateList debe publicar arrRun');
        $this->addCase($results, 'UpdateList_showVista', str_contains($upd, 'showVista(2,') && str_contains($upd, 'showError(2,'), 'showVista(2)/showError(2)');

        return $results;
    }

    /******************************************************************************/
    /*                       RECALCULAR: CONTRATO (ESTATICO)                      */
    /******************************************************************************/
    /**
     * Recalcular: solo POST (405), nivel >= 2 (403), parametros con allow-list (400), orden de las
     * comprobaciones, transacciones cortas y mensajes fijos. No se ejecuta (escribe en la BD).
     */
    public function recalcularContratoTest(){
        $results = [];
        $codigo  = $this->fuenteControlador();
        $rec     = $this->cuerpoMetodo($codigo, 'Recalcular');
        $this->addCase($results, 'recalcular_existe', $rec !== '', 'no se encuentra Recalcular');

        $posPost  = strpos($rec, "REQUEST_METHOD'] !== 'POST'");
        $posNivel = strpos($rec, "LevelAccess'] ?? 0) < 2");
        $posParam = strpos($rec, 'Parámetro inválido');
        $posStart = strpos($rec, 'startRun(');
        $this->addCase($results, 'POST_405', $posPost !== false && str_contains($rec, "'Método no permitido', 405"), 'falta el 405 para metodos distintos de POST');
        $this->addCase($results, 'nivel2_403', $posNivel !== false && str_contains($rec, "'Sin permiso', 403"), 'falta el 403 para nivel < 2');
        $this->addCase($results, 'parametro_400', $posParam !== false && str_contains($rec, 'ctype_digit') && str_contains($rec, '400'), 'falta la validacion del parametro semanas');
        $this->addCase($results, 'rango_semanas', str_contains($codigo, 'SEMANAS_MIN    = 4') && str_contains($codigo, 'SEMANAS_MAX    = 26') && str_contains($codigo, 'SEMANAS_DEF    = 12'), 'rango 4-26 defecto 12');
        $this->addCase($results, 'orden_comprobaciones',
            $posPost !== false && $posNivel !== false && $posParam !== false && $posStart !== false
            && $posPost < $posNivel && $posNivel < $posParam && $posParam < $posStart,
            'orden esperado: metodo, nivel, parametros, startRun');
        $this->addCase($results, 'presupuesto', str_contains($rec, 'loadPhpMl(') && str_contains($rec, 'beginBudget(') && substr_count($rec, 'checkBudget(') >= 2, 'loadPhpMl, beginBudget y checkBudget entre fases');
        $this->addCase($results, 'try_failRun', str_contains($rec, 'catch (Throwable') && str_contains($rec, 'failRun('), 'falta try/catch con failRun');
        $this->addCase($results, 'mensajes_fijos', str_contains($rec, "'Tiempo agotado'") && str_contains($rec, "'Error al recalcular'") && str_contains($rec, "'Recalculo completado'"), 'mensajes fijos de specs/api.md');
        $this->addCase($results, 'no_filtra_excepcion', !preg_match('/Response::error\([^;]*getMessage/', $rec), 'la respuesta no puede incluir el mensaje de la excepcion');
        $this->addCase($results, 'id_cifrado', str_contains($rec, "encryptDecrypt('encrypt'"), 'idEjecucion debe cifrarse en la respuesta');

        // startRun: transaccion corta, expiradas, intervalo minimo, indice unico
        $start = $this->cuerpoMetodo($codigo, 'startRun');
        $this->addCase($results, 'startRun_transaccion', str_contains($start, 'Base_transactionBegin(') && str_contains($start, 'Base_transactionCommit(') && str_contains($start, 'Base_transactionRollback('), 'startRun sin transaccion completa');
        $this->addCase($results, 'startRun_expiradas', str_contains($start, 'Estado = 4') && str_contains($start, 'STALE_MINUTES'), 'no marca expiradas');
        $this->addCase($results, 'startRun_intervalo', str_contains($start, 'MIN_INTERVAL_SECONDS') && str_contains($start, 'Espere antes de recalcular de nuevo'), 'falta el intervalo minimo');
        $this->addCase($results, 'startRun_concurrencia', str_contains($start, 'Ya hay un recálculo en curso') && str_contains($start, 'Duplicate'), 'falta el rechazo de la ejecucion concurrente');
        $this->addCase($results, 'startRun_fechainicio_bd', str_contains($start, 'FechaInicio) VALUES (?, ?, 1, 1, NOW())') && !str_contains($start, "date('Y-m-d"), 'FechaInicio debe fijarla NOW() de la BD (A-4)');
        $this->addCase($results, 'failRun_rollback', str_contains($this->cuerpoMetodo($codigo, 'failRun'), 'Base_transactionRollback('), 'failRun debe hacer rollback defensivo');
        $this->addCase($results, 'semanas_array_400', str_contains($rec, 'is_array($_POST[\'semanas\'])'), 'semanas como array debe dar 400 (L-2)');
        $this->addCase($results, 'startRun_sin_bind_manual', !preg_match('/\$_POST|\$_GET/', $start), 'startRun no debe leer la peticion');

        // saveRun: lotes, cierre, purga y rollback
        $save = $this->cuerpoMetodo($codigo, 'saveRun');
        $this->addCase($results, 'saveRun_lotes', str_contains($save, 'array_chunk(') && str_contains($save, 'INSERT_CHUNK') && str_contains($save, 'Base_insertMultiple('), 'insercion por lotes');
        $this->addCase($results, 'saveRun_cierre', str_contains($save, 'Estado = 2, Activa = NULL') && str_contains($save, 'json_encode('), 'cierre de la ejecucion');
        $this->addCase($results, 'saveRun_purga', str_contains($save, 'KEEP_RUNS') && str_contains($save, 'array_map(\'intval\'') && str_contains($save, 'DELETE FROM'), 'purga por retencion con ids enteros');
        $this->addCase($results, 'saveRun_transaccion', str_contains($save, 'Base_transactionBegin(') && str_contains($save, 'Base_transactionCommit(') && substr_count($save, 'Base_transactionRollback(') >= 3, 'rollback ante cada fallo');

        // failRun
        $fail = $this->cuerpoMetodo($codigo, 'failRun');
        $this->addCase($results, 'failRun_estado3', str_contains($fail, 'Estado = 3, Activa = NULL') && str_contains($fail, 'Mensaje = ?') && !str_contains($fail, 'Base_transactionBegin('), 'failRun deja Estado 3 sin transaccion');

        return $results;
    }

    /******************************************************************************/
    /*                       CONSULTAS: SIN LECTURAS CRUDAS                       */
    /******************************************************************************/
    /**
     * Lecturas: el dataset agrega en SQL (GROUP BY, LIMIT, ventana, bindeo), la lista lee solo
     * `bodegas_pred_*` + lookups, sin SELECT * ni lecturas crudas.
     */
    public function consultasSinLecturasCrudasTest(){
        $results = [];
        $codigo  = $this->fuenteControlador();

        $dem = $this->cuerpoMetodo($codigo, 'getDatasetDemanda');
        $this->addCase($results, 'demanda_group_by', str_contains($dem, "'group'   => 'bodegas_movimientos_productos.idProducto, YEARWEEK("), 'la demanda debe agruparse por producto y semana');
        $this->addCase($results, 'demanda_limit', str_contains($dem, 'MAX_SAMPLES_HARD') && str_contains($dem, 'TOP_PRODUCTOS'), 'LIMIT 20000 y top 1.500 productos');
        $this->addCase($results, 'demanda_ventana', substr_count($dem, 'INTERVAL ? WEEK') >= 2 && str_contains($dem, 'WEEKDAY(CURDATE())'), 'ventana de semanas completas');
        $this->addCase($results, 'demanda_criterio', str_contains($dem, 'idEstadoIngreso = ?') && str_contains($dem, 'mp2.idEstadoIngreso = 2') && !str_contains($dem, 'idBodegasIngreso'), 'egreso (idEstadoIngreso 2, los traspasos son 3) sin condicion de bodega nula (A-3)');
        $this->addCase($results, 'demanda_orden_y_tope', str_contains($dem, 'intdiv(FunctionsMachineLearning::MAX_SAMPLES_HARD') && str_contains($dem, "'order'   => 'bodegas_movimientos_productos.idProducto ASC, Semana ASC'") && str_contains($dem, 'COUNT(DISTINCT'), 'top = 20000/semanas, ORDER BY y total real (A-1)');
        $this->addCase($results, 'demanda_bindeada', str_contains($dem, "'params'  => [(int)\$semanas, 2, (int)\$semanas]"), 'parametros bindeados en el orden de los ?');

        $stk = $this->cuerpoMetodo($codigo, 'getStockProductos');
        $this->addCase($results, 'stock_limit', str_contains($stk, "'limit'   => 500") && str_contains($stk, 'IN ('), 'el stock se lee solo de los productos del dataset, por lotes (A-2)');
        $this->addCase($results, 'stock_ids_enteros', str_contains($stk, '(int)$bod[\'idBodegas\']'), 'las columnas de bodega usan ids casteados');

        $lista = $this->cuerpoMetodo($codigo, 'getDataList');
        $this->addCase($results, 'lista_solo_pred', str_contains($lista, "'table'   => 'bodegas_pred_reposicion'") && !str_contains($lista, 'bodegas_movimientos'), 'la lista solo lee bodegas_pred_reposicion + lookups');
        $this->addCase($results, 'lista_limit', str_contains($lista, '$limit ?? ConfigAPP::APP["N_MaxItems"]'), 'la lista lleva LIMIT (bloque pedido o N_MaxItems por defecto)');
        $updM = $this->cuerpoMetodo($codigo, 'UpdateList');
        $this->addCase($results, 'lista_carga_incremental', str_contains($lista, 'bodegas_pred_reposicion.idPrediccion ASC') && str_contains($updM, '$this->getPageParams()') && str_contains($updM, '$this->sliceMore(') && str_contains($updM, "header('X-Has-More: ") && str_contains($updM, "'-UpdateListRows.php'"), 'la lista pagina por bloques con orden determinista (PK al final) y UpdateList sirve el fragmento de filas (ADR-0022)');
        $this->addCase($results, 'lista_orden', str_contains($lista, 'Alerta DESC') && str_contains($lista, 'Sugerida DESC'), 'orden por alerta y sugerida');

        $this->addCase($results, 'sin_select_asterisco', !preg_match('/SELECT\s+\*/i', $codigo), 'SELECT * prohibido');
        $this->addCase($results, 'last_run_estado2', preg_match("/'Informe = \? AND Estado = \?',\s*'params'\s*=>\s*\[self::INFORME, 2\]/", $this->cuerpoMetodo($codigo, 'getLastRun')) === 1, 'getLastRun lee Estado = 2');
        // Las lecturas de movimientos solo existen en getDatasetDemanda (llamado desde Recalcular)
        $this->addCase($results, 'movimientos_solo_en_dataset',
            substr_count($codigo, "'bodegas_movimientos_productos'") === substr_count($dem, "'bodegas_movimientos_productos'") && str_contains($dem, 'bodegas_movimientos_productos'),
            'la tabla de movimientos solo se consulta en getDatasetDemanda');

        return $results;
    }

    /******************************************************************************/
    /*                       INSTALADOR: PERMISO, RUTAS Y TABLAS                  */
    /******************************************************************************/
    /**
     * El installer registra permiso (idTipo 3, idLevelLimit 2), 3 rutas (1/1/2), el controlador,
     * las 2 tablas `bodegas_pred_*`, sus indices y las columnas externas de mapTables.
     */
    public function instaladorTest(){
        $results = [];
        $inst    = $this->fuenteInstalador();

        // Permiso
        $bloque = preg_match("/\\\$arrPermisos\[\]\s*=\s*\[(?:(?!\\\$arrPermisos).)*?'Nombre'\s*=>\s*'Reposición Predictiva'.*?\];/s", $inst, $m) === 1 ? $m[0] : '';
        $this->addCase($results, 'permiso_declarado', $bloque !== '', 'falta el permiso Reposición Predictiva');
        $this->addCase($results, 'permiso_idTipo3', preg_match("/'idTipo'\s*=>\s*'3'/", $bloque) === 1, 'idTipo 3 (Informe)');
        $this->addCase($results, 'permiso_nivel2', preg_match("/'idLevelLimit'\s*=>\s*'2'/", $bloque) === 1, 'idLevelLimit 2 (excepcion ADR-0020)');
        $this->addCase($results, 'permiso_ruta', str_contains($bloque, "'".self::RUTA_WEB."'") && str_contains($bloque, "'".self::CONTROLADOR."'"), 'RutaWeb / RutaController');
        $this->addCase($results, 'permiso_categoria', preg_match("/'idPermisosCat'\s*=>\s*'3'/", $bloque) === 1, 'idPermisosCat 3');

        // Rutas
        $rutas = $this->rutasInstalador();
        $this->addCase($results, 'rutas_tres', count($rutas) === 4, 'se esperaban 4 rutas y hay '.count($rutas));
        $esperadas = [
            '1 '.self::RUTA_WEB.'/listAll'    => [self::CONTROLADOR.'->listAll', 1],
            '2 '.self::RUTA_WEB.'/search'     => [self::CONTROLADOR.'->UpdateList', 1],
            '2 '.self::RUTA_WEB.'/recalcular' => [self::CONTROLADOR.'->Recalcular', 2],
            '2 '.self::RUTA_WEB.'/exportExcel' => [self::CONTROLADOR.'->exportExcel', 1],
        ];
        foreach ($esperadas as $clave => [$handler, $nivel]) {
            $ok = isset($rutas[$clave]) && $rutas[$clave]['handler'] === $handler && $rutas[$clave]['nivel'] === $nivel;
            $this->addCase($results, 'ruta_'.md5($clave), $ok, 'falta '.$clave.' -> '.$handler.' nivel '.$nivel);
        }

        // Coherencia: el indice del match debe ser el orden del permiso (6.o $arrPermisos[])
        $this->addCase($results, 'match_indice_permiso', substr_count($inst, '$arrPermisos[] = [') === 6 && preg_match("/\n\s*6 => \[\s*\n[^\n]*informeReposicion->listAll/", $inst) === 1,
            'el bloque match 6 debe corresponder al 6.o permiso');

        // Controlador registrado
        $this->addCase($results, 'controlador_registrado', str_contains($inst, "\$RutaController .= ',".self::CONTROLADOR."';"), 'RutaController() no incluye el informe');

        // Tablas e indices
        $this->addCase($results, 'tabla_ejecuciones', str_contains($inst, "'table'      => 'bodegas_pred_ejecuciones'") && str_contains($inst, "'primaryKey' => 'idEjecucion'"), 'listTables: ejecuciones');
        $this->addCase($results, 'tabla_reposicion', str_contains($inst, "'table'      => 'bodegas_pred_reposicion'") && str_contains($inst, "'primaryKey' => 'idPrediccion'"), 'listTables: reposicion');
        $this->addCase($results, 'indice_unico_activa', str_contains($inst, 'ADD UNIQUE INDEX IF NOT EXISTS uk_bej_activa (Informe, Activa)'), 'indice unico (Informe, Activa)');
        $this->addCase($results, 'indices_idempotentes',
            str_contains($inst, 'ALTER TABLE bodegas_pred_ejecuciones ADD UNIQUE INDEX IF NOT EXISTS') && str_contains($inst, 'ALTER TABLE bodegas_pred_reposicion ADD INDEX IF NOT EXISTS idx_bpred_rep_ejecucion (idEjecucion, Alerta)'),
            'ALTER con IF NOT EXISTS (ADR-0013)');
        $this->addCase($results, 'mapTables_columnas', str_contains($inst, 'productos_listado(idProducto,idUniMed,Nombre,StockLimite,idEstado)') && str_contains($inst, 'minifyArrayTables($arrTables)'), 'mapTables con StockLimite e idEstado');

        // Estructura real (lo que expone el instalador en ejecucion)
        if (class_exists('bodegasInstaller') && method_exists('bodegasInstaller', 'getRouteDefinitions')) {
            try {
                $def = (new ReflectionClass('bodegasInstaller'))->newInstanceWithoutConstructor()->getRouteDefinitions(6);
                $this->addCase($results, 'definiciones_tipo6', count($def) === 4, 'getRouteDefinitions(6) devuelve '.count($def).' rutas');
            } catch (Throwable $e) {
                $this->addCase($results, 'definiciones_tipo6', false, 'no se pudo leer getRouteDefinitions(6)');
            }
        }

        return $results;
    }

    /******************************************************************************/
    /*                    ENTRENAMIENTO: DATOS SINTETICOS                         */
    /******************************************************************************/
    /**
     * trainReposicion con 6 productos sinteticos: alertas 1/2/3, sugerida, pendiente, exclusion por
     * pocas semanas, producto sin stock y metricas. Logica pura, sin base de datos.
     */
    public function trainReposicionTest(){
        $results = [];
        $ML = new FunctionsMachineLearning();
        if ($ML->loadPhpMl() !== true) {
            $this->addCase($results, 'php_ml_disponible', false, 'php-ml no se pudo cargar');
            return $results;
        }
        if (!method_exists(self::CONTROLADOR, 'trainReposicion')) {
            $this->addCase($results, 'train_existe', false, 'no existe trainReposicion');
            return $results;
        }

        $hoy     = '2026-11-12';
        $semanas = 8;
        $lista   = $this->semanasVentana($hoy, $semanas);
        $demanda = [];
        $serie = function (int $idProducto, array $valores) use (&$demanda, $lista) {
            foreach ($valores as $i => $v) {
                if ($v > 0) { $demanda[] = ['idProducto' => $idProducto, 'Semana' => $lista[$i], 'Demanda' => $v]; }
            }
        };
        $serie(1, array_fill(0, 8, 10));        // estable, stock 5, sin limite => alerta 3
        $serie(2, array_fill(0, 8, 10));        // estable, stock 50, limite 100 => alerta 2
        $serie(3, array_fill(0, 8, 10));        // estable, stock 200, limite 100 => alerta 1
        $serie(4, [0, 0, 0, 0, 0, 0, 5, 5]);    // solo 2 semanas con datos => excluido
        $serie(5, [1, 2, 3, 4, 5, 6, 7, 8]);    // tendencia creciente => pendiente 1, predicha 9
        $serie(6, array_fill(0, 8, 4));         // sin fila de stock => sin alerta (0)
        $stock = [
            ['idProducto' => 1, 'StockLimite' => null,  'StockActual' => '5'],
            ['idProducto' => 2, 'StockLimite' => '100', 'StockActual' => '50'],
            ['idProducto' => 3, 'StockLimite' => '100', 'StockActual' => '200'],
            ['idProducto' => 5, 'StockLimite' => null,  'StockActual' => '0'],
        ];

        $ctl = (new ReflectionClass(self::CONTROLADOR))->newInstanceWithoutConstructor();
        $m   = new ReflectionMethod(self::CONTROLADOR, 'trainReposicion');
        $m->setAccessible(true);
        $res = $m->invoke($ctl, $demanda, $stock, $semanas, $hoy);

        $porId = [];
        foreach ($res['rows'] as $fila) { $porId[$fila['idProducto']] = $fila; }

        $this->addCase($results, 'muestras', $res['muestrasOrigen'] === 6 && $res['muestras'] === 6, 'muestras '.$res['muestras'].' de '.$res['muestrasOrigen']);
        $this->addCase($results, 'excluye_pocas_semanas', count($res['rows']) === 5 && !isset($porId[4]), 'el producto con 2 semanas con datos debe excluirse');
        $this->addCase($results, 'alerta_3_bajo_demanda', ($porId[1]['Alerta'] ?? 0) === 3, 'producto 1 debe tener alerta 3');
        $this->addCase($results, 'alerta_2_bajo_limite', ($porId[2]['Alerta'] ?? 0) === 2, 'producto 2 debe tener alerta 2');
        $this->addCase($results, 'alerta_1_ok', ($porId[3]['Alerta'] ?? 0) === 1, 'producto 3 debe tener alerta 1');
        $this->addCase($results, 'sin_stock_sin_alerta', ($porId[6]['Alerta'] ?? -1) === 0 && (float)($porId[6]['Sugerida'] ?? 1) === 0.0 && array_key_exists('Cobertura', $porId[6]) && $porId[6]['Cobertura'] === null, 'sin fila de stock => alerta 0 (sin dato), sin sugerida (A-2)');
        $r2 = $m->invoke($ctl, $demanda, $stock, $semanas, $hoy, 40);
        $this->addCase($results, 'muestras_origen_total', $r2['muestrasOrigen'] === 40 && $r2['muestras'] === 6, 'muestrasOrigen refleja el total real previo al top (A-1)');
        $this->addCase($results, 'demanda_predicha_estable', abs(($porId[1]['DemandaPredicha'] ?? 0) - 10) < self::DELTA && abs(($porId[1]['DemandaMedia'] ?? 0) - 10) < self::DELTA, 'serie constante => predicha 10');
        $this->addCase($results, 'tendencia_creciente', abs(($porId[5]['Pendiente'] ?? 0) - 1) < self::DELTA && abs(($porId[5]['DemandaPredicha'] ?? 0) - 9) < self::DELTA, 'serie 1..8 => pendiente 1 y predicha 9');
        $this->addCase($results, 'sugerida_con_limite', abs(($porId[2]['Sugerida'] ?? 0) - 60) < self::DELTA, 'sugerida = 10 + 100 - 50');
        $this->addCase($results, 'sugerida_sin_negativos', abs(($porId[3]['Sugerida'] ?? -1)) < self::DELTA, 'sugerida no puede ser negativa');
        $this->addCase($results, 'sugerida_sin_limite', abs(($porId[1]['Sugerida'] ?? 0) - 5) < self::DELTA, 'sugerida = 10 + 0 - 5');
        $this->addCase($results, 'cobertura', abs(($porId[2]['Cobertura'] ?? 0) - 5) < self::DELTA && ($porId[2]['StockLimite'] ?? null) === 100.0, 'cobertura = stock / predicha (5 semanas)');
        $this->addCase($results, 'semanas_usadas', ($porId[1]['SemanasUsadas'] ?? 0) === $semanas, 'SemanasUsadas');

        $columnas = ['idProducto', 'SemanasUsadas', 'DemandaMedia', 'DemandaPredicha', 'Pendiente', 'StockActual', 'StockLimite', 'Cobertura', 'Sugerida', 'Alerta'];
        $this->addCase($results, 'columnas_fila', isset($res['rows'][0]) && array_keys($res['rows'][0]) === $columnas, 'las filas deben coincidir con las columnas de bodegas_pred_reposicion');
        $met = $res['metricas'];
        $this->addCase($results, 'metricas_claves', isset($met['mae'], $met['rmse'], $met['maeBase'], $met['rmseBase'], $met['productos']) && $met['productos'] === 5, 'metricas {mae,rmse,maeBase,rmseBase,productos}');
        // Series estables: el backtest acierta, el error del modelo no supera el de la linea base
        $this->addCase($results, 'metricas_vs_base', $met['mae'] <= $met['maeBase'] + self::DELTA + 1.0, 'MAE del modelo vs linea base');
        $this->addCase($results, 'modelo_claves', ($res['modelo']['semanas'] ?? 0) === $semanas && ($res['modelo']['algoritmo'] ?? '') === 'LeastSquares' && isset($res['modelo']['version']), 'modelo {semanas,algoritmo,version}');

        // Sin datos: sin filas ni errores
        $vacio = $m->invoke($ctl, [], [], $semanas, $hoy);
        $this->addCase($results, 'sin_datos', $vacio['rows'] === [] && $vacio['muestras'] === 0, 'sin demanda no hay filas');

        return $results;
    }

    /******************************************************************************/
    /*                       EXPORTAR A EXCEL (ADR-0021)                          */
    /******************************************************************************/
    /**
     * exportExcel real: POST, orden verbo -> limite -> filtro -> ultima ejecucion -> consulta -> descarga,
     * filtro compartido (armarFiltro), columnas = cabecera de la vista, boton exportExcelServer y ruta POST nivel 1.
     * Analisis estatico del fuente (la descarga emite cabeceras y termina el proceso).
     */
    public function exportacionServidorTest(){
        $results = [];
        $src = $this->fuenteControlador();
        $exp = $this->cuerpoMetodo($src, 'exportExcel');
        $upd = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'));
        $lis = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php'));

        /*=========== Metodo: POST, orden y reglas ===========*/
        $this->addCase($results, 'export_metodo_post', str_contains($exp, "\$_SERVER['REQUEST_METHOD'] !== 'POST'") && str_contains($exp, "Response::error('Método no permitido', 405)"), 'exportExcel debe exigir POST (405)');
        $this->addCase($results, 'export_usa_armarFiltro', str_contains($exp, '$this->armarFiltro()') && str_contains($exp, "\$this->getDataList(\$filtro['where'], \$filtro['params'], (int)"), 'exportExcel debe usar el mismo armarFiltro y getDataList (con la ultima ejecucion) que UpdateList');
        $this->addCase($results, 'export_misma_fuente_filtro', substr_count($src, '$this->armarFiltro()') === 2 && substr_count($src, '$this->searchWhere(') === 3, 'los filtros deben definirse una sola vez (armarFiltro)');
        $this->addCase($results, 'export_orden_limite_filtro_consulta', $this->ordenado($exp, ['REQUEST_METHOD', '->acquire(', '429', '$this->armarFiltro()', '$this->getLastRun()', '$this->getDataList(', '->download(']), 'orden: verbo, limite (429), filtro (400), ejecucion, consulta, descarga');
        $this->addCase($results, 'export_auditReject_405', $this->ordenado($exp, ['REQUEST_METHOD', "->auditReject(\$this->controllerName, '', 405)", "Response::error('Método no permitido', 405)"]), 'el 405 debe auditarse con auditReject antes de responder');
        $this->addCase($results, 'export_auditReject_429', $this->ordenado($exp, ['->acquire(', "->auditReject(\$this->controllerName, '', 429)", 'Response::error($limite, 429)']), 'el 429 debe auditarse con auditReject (acquire ya libero)');
        $this->addCase($results, 'export_400_release_audit', $this->ordenado($exp, ['$this->armarFiltro()', '$Export->release()', "->auditReject(\$this->controllerName, '', 400, ['idProducto', 'Alerta'])", "Response::error(\$filtro['error'], 400)"]), 'el filtro invalido debe liberar, auditar (solo claves) y responder 400');
        $this->addCase($results, 'export_commit_tras_filtro', $this->ordenado($exp, ['$this->armarFiltro()', '$Export->commit()', '->download(']) && substr_count($exp, '$Export->commit()') === 1, 'commit() una vez, tras validar el filtro y antes de la consulta/descarga');
        $n500 = substr_count($exp, "->auditReject(\$this->controllerName, '', 500, ['idProducto', 'Alerta'])");
        $this->addCase($results, 'export_500_release_audit', $n500 >= 1 && substr_count($exp, '$Export->release();') === 1 + $n500 && substr_count($exp, "Response::error('No se pudo generar la exportación', 500)") === $n500, 'cada 500 debe liberar y auditar (solo claves) antes del error generico');
        $this->addCase($results, 'export_auditReject_sin_valores', !str_contains($exp, 'auditReject($this->controllerName, \'\', 400, $_POST') && !str_contains($exp, 'array_values($_POST'), 'auditReject nunca recibe valores de filtro');
        $this->addCase($results, 'export_consulta_error_500', str_contains($exp, "Response::error('No se pudo generar la exportación', 500)"), 'un fallo de consulta debe responder 500 generico');
        $this->addCase($results, 'export_sin_ejecucion_hoja_vacia', str_contains($exp, "\$arrList = ['status' => true, 'data' => []];") && str_contains($exp, '!empty($arrRun[\'data\'])'), 'sin ejecucion correcta la hoja sale vacia (como la vista)');
        $this->addCase($results, 'export_limite_Q12', str_contains($exp, '$Export->acquire($userId)') && str_contains($exp, 'Response::error($limite, 429)'), 'limite de frecuencia/concurrencia con 429');
        $this->addCase($results, 'export_auditoria_Q11', str_contains($exp, "'audit'     => ['controller' => \$this->controllerName, 'userId' => \$userId]"), 'auditoria (AuditLogger) al descargar');
        $this->addCase($results, 'export_truncado', str_contains($exp, 'count($data) > $maxRows') && str_contains($exp, 'FunctionsExportExcel::maxRows()') && str_contains($exp, "'truncated' => \$truncated,"), 'aviso de truncado al superar el tope N_MaxExportRows (ADR-0022)');
        $this->addCase($results, 'export_lectura_por_bloques', str_contains($this->cuerpoMetodo($src, 'getDataList'), '$this->Base_GetListChunked($query, $maxRows)') && str_contains($exp, "null, \$maxRows)"), 'la consulta de exportacion se lee por bloques con Base_GetListChunked');
        $this->addCase($results, 'export_hoja_filtros', str_contains($exp, "'filters'   => \$this->nombresFiltro(\$filtro['label'])") && str_contains($exp, "'user'      => (string) (\$userData['UserName']"), 'hoja Filtros con filtros aplicados y usuario');
        $this->addCase($results, 'export_nombre_fijo', str_contains($exp, "->download('reposicion_predictiva'") && !preg_match('/download\(\s*\$_(POST|GET|REQUEST)/', $exp), 'el nombre del archivo lo fija el servidor');
        $this->addCase($results, 'export_sin_formatear_en_php', !str_contains($exp, 'number_format') && !str_contains($exp, 'htmlspecialchars') && !str_contains($exp, 'fechaEstandar'), 'filas crudas, sin formatear en PHP');
        $this->addCase($results, 'export_sin_legado', !preg_match('/PhpSpreadsheet|PHPExcel|fputcsv|ZipArchive|Content-Disposition/', $src) && str_contains($src, 'new FunctionsExportExcel()'), 'el controlador usa el helper; sin librerias ni cabeceras de descarga');

        /*=========== Columnas = columnas de la vista ===========*/
        $claves = ['Producto', 'UniMed', 'DemandaMedia', 'DemandaPredicha', 'Pendiente', 'StockActual', 'StockLimite', 'Cobertura', 'Sugerida', 'Alerta'];
        $etiq   = ['Producto', 'Unidad', 'Demanda media', 'Demanda predicha', 'Pendiente', 'Stock actual', 'Stock límite', 'Cobertura (sem.)', 'Sugerida', 'Alerta'];
        $okCols = preg_match_all("/\['key' => '([A-Za-z]+)',\s+'label' => '([^']+)',\s+'type' => '([a-z]+)'/", $exp, $m) === 10 && $m[1] === $claves;
        $this->addCase($results, 'export_columnas_vista', $okCols, 'exportExcel debe declarar las 10 columnas de la vista, en su orden');
        $this->addCase($results, 'export_columnas_etiquetas', $okCols && $m[2] === $etiq, 'las etiquetas deben ser las cabeceras de la vista');
        $this->addCase($results, 'export_columnas_tipos', $okCols && $m[3] === ['text', 'text', 'decimal', 'decimal', 'decimal', 'decimal', 'decimal', 'decimal', 'decimal', 'text'], 'tipos: texto y decimal');
        $okVista = true;
        foreach ($etiq as $c) {
            $okVista = $okVista && (bool) preg_match('/<th scope="col"[^>]*>'.preg_quote($c, '/').'<\/th>/u', $upd);
        }
        $this->addCase($results, 'export_columnas_igual_cabecera_vista', $okVista, 'cada columna exportada debe existir en la cabecera de la tabla visible');

        /*=========== Vista: boton y sin patron anterior ===========*/
        $this->addCase($results, 'export_boton_UpdateList', substr_count($upd, 'onclick="exportExcelServer(this)"') === 1 && str_contains($upd, 'data-export-filename="reposicion_predictiva"') && str_contains($upd, "/exportExcel'") && str_contains($upd, 'data-export-url="'), 'UpdateList debe tener un boton exportExcelServer');
        $this->addCase($results, 'export_boton_url_RouteAccess', str_contains($upd, "\$BASE.'/'.\$data['UserAccess']['RouteAccess'].'/exportExcel'"), 'la URL del boton sale de BASE + RouteAccess');
        $this->addCase($results, 'export_boton_no_en_List', !str_contains($lis, 'exportExcelServer') && !str_contains($lis, 'exportTableToExcel') && !str_contains($lis, 'data-export-url'), 'el boton no va en List');
        $this->addCase($results, 'export_sin_tableData', !str_contains($upd, 'tableData') && !str_contains($upd, 'class="d-none"') && substr_count($upd, '<table') === 1, 'no debe quedar la tabla oculta de exportacion');
        $this->addCase($results, 'export_sin_exportTableToExcel', !str_contains($upd, 'exportTableToExcel') && !str_contains($lis, 'exportTableToExcel'), 'las vistas no deben llamar a exportTableToExcel');
        $this->addCase($results, 'export_tabla_visible_con_datatable', str_contains($upd, '<table class="table table-sm table-hover datatable"') && str_contains($upd, 'data-more-url=') && str_contains($upd, 'data-next-offset='), 'la tabla visible sigue usando datatable con carga incremental');
        $js = $this->codigoFuente($this->rutaModulo('../../../../public/js/functions_v2.js'));
        $this->addCase($results, 'export_funcion_js_existe', str_contains($js, 'function exportExcelServer(btn)') && str_contains($js, 'function guardarFiltrosExport('), 'exportExcelServer y guardarFiltrosExport deben existir en functions_v2.js');
        $this->addCase($results, 'export_ruta_nivel_1', ($this->rutasInstalador()['2 '.self::RUTA_WEB.'/exportExcel'] ?? [])['nivel'] === 1, 'la ruta exportExcel debe tener idLevelLimit 1 (igual que la vista)');
        $this->addCase($results, 'ruta_exportExcel_unica_post', count(array_filter(array_keys($this->rutasInstalador()), fn($k) => str_contains($k, 'exportExcel'))) === 1, 'una unica ruta exportExcel, idMetodo 2 (POST), sin @id');
        $this->addCase($results, 'export_helper_disponible', class_exists('FunctionsExportExcel') && method_exists('FunctionsExportExcel', 'download') && method_exists('FunctionsExportExcel', 'acquire'), 'el helper FunctionsExportExcel debe estar disponible');
        return $results;
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /**
     * Renderiza `-List` y `-UpdateList`: filtro, boton Recalcular segun nivel, aviso sin ejecucion,
     * panel de ultima ejecucion, tabla, escape y exportacion a Excel del servidor.
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     */
    public function vistasTest($f3){
        $results = [];
        $BASE    = trim((string)$f3->get('BASE')) !== '' ? (string)$f3->get('BASE') : '/admin';
        $vList   = $this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php');
        $vLista  = $this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php');

        // -List nivel 1 con ejecucion
        $html1 = $this->renderizarVista($vList, $this->datosSinteticos(1, true), $BASE, $f3);
        $this->addCase($results, 'list_render', $html1 !== '', 'la vista List no produjo HTML');
        $this->addCase($results, 'list_filtro', str_contains($html1, 'id="FormSearchData"') && str_contains($html1, 'id="Search_idProducto"') && str_contains($html1, 'id="Search_Alerta"'), 'filtro por producto y alerta');
        $this->addCase($results, 'list_search_post', str_contains($html1, '/search') && str_contains($html1, "'POST'"), 'el filtro publica en search');
        $this->addCase($results, 'list_nivel1_sin_boton', !str_contains($html1, 'btnRecalcular') && !str_contains($html1, '/recalcular') && !str_contains($html1, 'Recalcular'), 'con nivel 1 no debe haber boton ni ruta de recalculo');
        $this->addCase($results, 'list_cabecera_ejecucion', str_contains($html1, 'LeastSquares') && str_contains($html1, '10 de 12'), 'cabecera de la ultima ejecucion');

        // -List nivel 2
        $html2 = $this->renderizarVista($vList, $this->datosSinteticos(2, true), $BASE, $f3);
        $this->addCase($results, 'list_nivel2_boton', str_contains($html2, 'id="btnRecalcular"') && str_contains($html2, '/recalcular') && str_contains($html2, 'id="Recalc_semanas"'), 'con nivel 2 se muestra el boton y el select de semanas');
        $this->addCase($results, 'list_confirmacion', str_contains($html2, 'Swal.fire') && str_contains($html2, "'POST'") && str_contains($html2, 'closeObject'), 'confirmacion, POST y loader');
        $this->addCase($results, 'list_sin_csrf_manual', !str_contains($html2, '_token') && !str_contains($html2, 'X-CSRF-Token'), 'el token lo agrega functions_v2.js');

        // -List sin ejecucion
        $html0 = $this->renderizarVista($vList, $this->datosSinteticos(1, false), $BASE, $f3);
        $this->addCase($results, 'list_aviso_sin_ejecucion', str_contains($html0, 'Sin resultados; pulse Recalcular'), 'aviso RF-007');

        // -UpdateList con resultados
        $tabla = $this->renderizarVista($vLista, $this->datosSinteticos(1, true), $BASE, $f3);
        $this->addCase($results, 'tabla_render', $tabla !== '', 'la vista UpdateList no produjo HTML');
        foreach (['Producto', 'Unidad', 'Demanda media', 'Demanda predicha', 'Pendiente', 'Stock actual', 'Stock límite', 'Cobertura', 'Sugerida', 'Alerta'] as $col) {
            $this->addCase($results, 'tabla_col_'.md5($col), str_contains($tabla, $col), 'falta la columna '.$col);
        }
        $this->addCase($results, 'tabla_badge', str_contains($tabla, 'badge bg-danger') && str_contains($tabla, 'Reponer'), 'badge de alerta 3');
        $this->addCase($results, 'tabla_escape', str_contains($tabla, 'Producto &lt;Demo&gt;') && !str_contains($tabla, 'Producto <Demo>'), 'el nombre debe escaparse');
        $this->addCase($results, 'tabla_panel', str_contains($tabla, 'MAE') && str_contains($tabla, 'RMSE') && str_contains($tabla, 'línea base') && str_contains($tabla, '10 de 12'), 'panel con metricas y linea base');
        $this->addCase($results, 'tabla_excel', substr_count($tabla, 'onclick="exportExcelServer(this)"') === 1 && str_contains($tabla, 'data-export-filename="reposicion_predictiva"') && !str_contains($tabla, 'exportTableToExcel') && !str_contains($tabla, 'tableData') && substr_count($tabla, '<table') === 1, 'exportacion a Excel del servidor, sin tabla oculta');
        $this->addCase($results, 'render_boton_exportar_nivel_1', str_contains($tabla, 'data-export-url="'.$BASE.'/'.self::RUTA_WEB.'/exportExcel"'), 'el boton exportar apunta a <RouteAccess>/exportExcel con nivel 1');
        $this->addCase($results, 'tabla_sin_ids', !str_contains($tabla, 'idProducto'), 'no se emiten ids al navegador');

        // -UpdateList sin ejecucion
        $vacia = $this->renderizarVista($vLista, $this->datosSinteticos(1, false), $BASE, $f3);
        $this->addCase($results, 'tabla_aviso_sin_ejecucion', str_contains($vacia, 'Sin resultados; pulse Recalcular'), 'aviso sin ejecucion');

        return $results;
    }
    /**
     * H-03: un valor array en cualquier campo del filtro (campo[]=1) devuelve 'error' (400), nunca "Array to string conversion".
     * Se invoca armarFiltro() real por reflexion con $_POST controlado.
     */
    public function exportacionFiltroArrayTest(){
        $results = [];
        $Controller = new informeReposicion();
        $m = new ReflectionMethod(self::CONTROLADOR, 'armarFiltro');
        $m->setAccessible(true);
        $post = $_POST;
        $avisos = [];
        set_error_handler(function ($no, $str) use (&$avisos) { $avisos[] = $str; return true; });
        foreach (['idProducto', 'Alerta'] as $campo) {
            $_POST = [$campo => ['1']];
            $f = $m->invoke($Controller);
            $this->addCase($results, 'armarFiltro_array_'.$campo, ($f['error'] ?? null) === 'Filtro inválido', 'el campo '.$campo.' en forma de array debe dar error Filtro invalido (400)');
        }
        restore_error_handler();
        $_POST = $post;
        $this->addCase($results, 'armarFiltro_array_sin_avisos', $avisos === [], 'no debe emitirse "Array to string conversion" ('.implode('|', $avisos).')');
        $arm = $this->cuerpoMetodo($this->fuenteControlador(), 'armarFiltro');
        $this->addCase($results, 'armarFiltro_array_antes_de_string', strpos($arm, 'is_array($_POST[$campo])') !== false && strpos($arm, 'is_array($_POST[$campo])') < strpos($arm, '(string) $_POST'), 'is_array debe comprobarse antes de (string)');
        return $results;
    }
}
