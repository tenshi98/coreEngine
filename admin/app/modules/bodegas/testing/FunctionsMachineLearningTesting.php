<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class FunctionsMachineLearningTesting
 *
 * Pruebas del helper transversal `FunctionsMachineLearning` (`vendors/application/functions/`,
 * ADR-0020). Se aloja en el modulo `bodegas` por ser el primer consumidor (ADR-0008).
 *
 * Alcance: pruebas NO destructivas y sin base de datos: el helper es logica pura. Cada metodo
 * publico del helper tiene al menos un caso (incluye topes y division por cero).
 *
 * Convenciones del sistema de testeos: toda prueba es un metodo publico que termina en "Test"
 * y devuelve [caso => 'OK'|'FALLO: razon'] (docs/architecture/testing/contract.md).
 *
 * @see skills/testing/SKILL.md
 */
class FunctionsMachineLearningTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR = 'FunctionsMachineLearning';
    // Tolerancia numerica de las comparaciones
    private const DELTA       = 0.0001;

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
    // Compara dos numeros con tolerancia
    /*******************************************************************/
    private function cerca($a, $b): bool{
        return abs((float) $a - (float) $b) < self::DELTA;
    }

    /*******************************************************************/
    // Instancia del helper con php-ml cargado
    /*******************************************************************/
    private function helper(): FunctionsMachineLearning{
        $ML = new FunctionsMachineLearning();
        $ML->loadPhpMl();
        return $ML;
    }

    /*******************************************************************/
    // Conjunto sintetico de dos clases separables (clase 1 con x alto)
    /*******************************************************************/
    private function datosSeparables(int $n): array{
        mt_srand(7);
        $samples = [];
        $labels  = [];
        for ($i = 0; $i < $n; $i++) {
            $clase     = $i % 2;
            $samples[] = [$clase ? mt_rand(20, 30) : mt_rand(0, 10), mt_rand(0, 5)];
            $labels[]  = $clase;
        }
        return [$samples, $labels];
    }

    /******************************************************************************/
    /*                                  PRUEBAS                                   */
    /******************************************************************************/
    /**
     * Estructura: archivo, clase, 12 metodos publicos y 8 constantes con los valores de la spec.
     */
    public function estructuraHelperTest(){
        $results = [];
        $ruta    = dirname(__DIR__, 5).'/vendors/application/functions/FunctionsMachineLearning.php';
        $this->addCase($results, 'archivo_existe', is_file($ruta), 'no existe '.$ruta);
        $this->addCase($results, 'clase_existe', class_exists('FunctionsMachineLearning'), 'la clase no se autocarga');

        $esperadas = [
            'MAX_SAMPLES_DEFAULT' => 5000, 'MAX_SAMPLES_HARD' => 20000, 'MAX_SECONDS' => 120,
            'MEMORY_LIMIT' => '256M', 'MIN_INTERVAL_SECONDS' => 60, 'STALE_MINUTES' => 10,
            'KEEP_RUNS' => 3, 'INSERT_CHUNK' => 500,
        ];
        $ref = new ReflectionClass('FunctionsMachineLearning');
        $const = $ref->getConstants();
        foreach ($esperadas as $nombre => $valor) {
            $this->addCase($results, 'constante_'.$nombre, ($const[$nombre] ?? null) === $valor, $nombre.' debe valer '.var_export($valor, true));
        }

        $publicos = array_filter($ref->getMethods(ReflectionMethod::IS_PUBLIC), function ($m) { return $m->class === 'FunctionsMachineLearning'; });
        $this->addCase($results, 'doce_metodos_publicos', count($publicos) === 12, 'se esperaban 12 y hay '.count($publicos));

        $fuente = is_file($ruta) ? (string) file_get_contents($ruta) : '';
        $this->addCase($results, 'sin_unserialize', stripos($fuente, 'unserialize') === false, 'el helper no puede usar unserialize');
        $this->addCase($results, 'sin_modelos_libsvm', !preg_match('/Phpml\\\\\\\\?[A-Za-z\\\\\\\\]*\\\\\\\\?(SVC|SVR)\b/', $fuente), 'el helper no puede usar SVC/SVR');
        return $results;
    }

    /**
     * loadPhpMl: carga la libreria y es idempotente.
     */
    public function cargaLibreriaTest(){
        $results = [];
        $ML = new FunctionsMachineLearning();
        $this->addCase($results, 'loadPhpMl_true', $ML->loadPhpMl() === true, 'debe devolver true con la libreria presente');
        $this->addCase($results, 'loadPhpMl_idempotente', $ML->loadPhpMl() === true, 'la segunda carga debe devolver true');
        $this->addCase($results, 'kmeans_disponible', class_exists('Phpml\\Clustering\\KMeans'), 'Phpml\\Clustering\\KMeans no se carga');
        $this->addCase($results, 'randomforest_disponible', class_exists('Phpml\\Classification\\Ensemble\\RandomForest'), 'RandomForest no se carga');
        return $results;
    }

    /**
     * capSamples: recorte determinista y topes.
     */
    public function capSamplesTest(){
        $results = [];
        $ML = $this->helper();
        $r = $ML->capSamples(range(1, 12000), 5000);
        $this->addCase($results, 'recorta_12000_a_5000', count($r['rows']) === 5000 && $r['total'] === 12000 && $r['capped'] === true, 'rows='.count($r['rows']).' total='.$r['total']);
        $r2 = $ML->capSamples(range(1, 12000), 5000);
        $this->addCase($results, 'recorte_determinista', $r['rows'] === $r2['rows'], 'dos llamadas iguales deben dar el mismo recorte');
        $this->addCase($results, 'conserva_orden', $r['rows'] === array_values(array_unique($r['rows'])) && $r['rows'] === (function ($x) { sort($x); return $x; })($r['rows']), 'el recorte debe conservar el orden y no repetir filas');
        $s = $ML->capSamples([1, 2, 3], 5000);
        $this->addCase($results, 'sin_recorte_si_cabe', $s['rows'] === [1, 2, 3] && $s['capped'] === false && $s['total'] === 3, 'no debe recortar 3 filas');
        $hard = $ML->capSamples(range(1, 25000), 99999);
        $this->addCase($results, 'tope_duro_20000', count($hard['rows']) === 20000 && $hard['capped'] === true, 'rows='.count($hard['rows']));
        $min = $ML->capSamples([1, 2, 3], 0);
        $this->addCase($results, 'minimo_1', count($min['rows']) === 1, 'max 0 debe acotarse a 1; rows='.count($min['rows']));
        $vacio = $ML->capSamples([], 10);
        $this->addCase($results, 'vacio', $vacio['rows'] === [] && $vacio['total'] === 0 && $vacio['capped'] === false, 'vacio debe devolver rows [] total 0');
        return $results;
    }

    /**
     * beginBudget y checkBudget: presupuesto de tiempo.
     */
    public function presupuestoTest(){
        $results = [];
        $ML = new FunctionsMachineLearning();
        $antes = microtime(true);
        $t0 = $ML->beginBudget(30);
        $this->addCase($results, 'beginBudget_float_microtime', is_float($t0) && $t0 >= $antes && $t0 <= microtime(true), 'debe devolver microtime(true)');
        $this->addCase($results, 'checkBudget_dentro_del_tiempo', $ML->checkBudget($t0, 30) === true, 'recien iniciado debe ser true');
        $this->addCase($results, 'checkBudget_tiempo_superado', $ML->checkBudget(microtime(true) - 100, 30) === false, 'con 100 s transcurridos y tope 30 debe ser false');
        $this->addCase($results, 'checkBudget_por_defecto', $ML->checkBudget(microtime(true) - 200) === false, 'con 200 s y tope por defecto 120 debe ser false');
        $limite = (string) ini_get('memory_limit');
        $ok = $limite === '-1' || $limite === '' || (int) $limite >= 256 || preg_match('/[GgKk]$/', $limite) === 1;
        $this->addCase($results, 'memoria_no_inferior_256M', $ok, 'memory_limit='.$limite);
        return $results;
    }

    /**
     * splitTrainTest: particion 80/20 estratificada.
     */
    public function particionTest(){
        $results = [];
        $ML = $this->helper();
        [$s, $l] = $this->datosSeparables(100);
        $p = $ML->splitTrainTest($s, $l, 0.2, 1);
        $nTr = count($p['trainSamples']);
        $nTe = count($p['testSamples']);
        $this->addCase($results, 'particion_80_20', $nTr >= 78 && $nTr <= 82 && $nTe >= 18 && $nTe <= 22 && ($nTr + $nTe) === 100, 'train='.$nTr.' test='.$nTe);
        $this->addCase($results, 'etiquetas_alineadas', count($p['trainLabels']) === $nTr && count($p['testLabels']) === $nTe, 'etiquetas y muestras deben tener igual longitud');
        $this->addCase($results, 'prueba_con_ambas_clases', count(array_unique($p['testLabels'])) === 2, 'la prueba debe contener ambas clases');
        $this->addCase($results, 'entrenamiento_con_ambas_clases', count(array_unique($p['trainLabels'])) === 2, 'el entrenamiento debe contener ambas clases');
        $acot = $ML->splitTrainTest($s, $l, 0.9, 1);
        $this->addCase($results, 'ratio_acotado_a_0_5', count($acot['testSamples']) <= 55, 'ratio 0.9 debe acotarse a 0.5; test='.count($acot['testSamples']));
        return $results;
    }

    /**
     * classificationMetrics: metricas binarias y division por cero.
     */
    public function metricasClasificacionTest(){
        $results = [];
        $ML = new FunctionsMachineLearning();
        $m = $ML->classificationMetrics([1, 0, 1, 0], [1, 0, 0, 0], 1);
        $this->addCase($results, 'accuracy_0_75', $this->cerca($m['accuracy'], 0.75), 'accuracy='.$m['accuracy']);
        $this->addCase($results, 'precision_1', $this->cerca($m['precision'], 1.0), 'precision='.$m['precision']);
        $this->addCase($results, 'recall_0_5', $this->cerca($m['recall'], 0.5), 'recall='.$m['recall']);
        $this->addCase($results, 'f1_0_6667', $this->cerca($m['f1'], 0.6667), 'f1='.$m['f1']);
        $this->addCase($results, 'matriz_confusion', $m['tp'] === 1 && $m['fp'] === 0 && $m['fn'] === 1 && $m['tn'] === 2, 'tp/fp/fn/tn='.$m['tp'].'/'.$m['fp'].'/'.$m['fn'].'/'.$m['tn']);
        $z = $ML->classificationMetrics([0, 0, 0], [0, 0, 0], 1);
        $this->addCase($results, 'sin_positivos_no_divide_por_cero', $z['precision'] === 0.0 && $z['recall'] === 0.0 && $z['f1'] === 0.0 && $this->cerca($z['accuracy'], 1.0), 'sin clase positiva precision/recall/f1 deben ser 0');
        $v = $ML->classificationMetrics([], [], 1);
        $this->addCase($results, 'vacio_ceros', $v['accuracy'] === 0.0 && $v['tp'] === 0, 'vacio debe devolver ceros');
        return $results;
    }

    /**
     * regressionMetrics: MAE y RMSE.
     */
    public function metricasRegresionTest(){
        $results = [];
        $ML = new FunctionsMachineLearning();
        $m = $ML->regressionMetrics([1, 2, 3], [1, 2, 5]);
        $this->addCase($results, 'mae_0_6667', $this->cerca($m['mae'], 0.6667), 'mae='.$m['mae']);
        $this->addCase($results, 'rmse_1_1547', $this->cerca($m['rmse'], 1.1547), 'rmse='.$m['rmse']);
        $this->addCase($results, 'n_3', $m['n'] === 3, 'n='.$m['n']);
        $v = $ML->regressionMetrics([], []);
        $this->addCase($results, 'vacio_ceros', $v['mae'] === 0.0 && $v['rmse'] === 0.0 && $v['n'] === 0, 'vacio debe devolver ceros');
        return $results;
    }

    /**
     * meanStd y standardize: media, desviacion poblacional y z-score.
     */
    public function estadisticaTest(){
        $results = [];
        $ML = new FunctionsMachineLearning();
        $r = $ML->meanStd([2, 4, 4, 4, 5, 5, 7, 9]);
        $this->addCase($results, 'meanStd_media_5', $this->cerca($r['mean'], 5), 'mean='.$r['mean']);
        $this->addCase($results, 'meanStd_std_2', $this->cerca($r['std'], 2), 'std='.$r['std']);
        $v = $ML->meanStd([]);
        $this->addCase($results, 'meanStd_vacio', $v['mean'] === 0.0 && $v['std'] === 0.0, 'vacio debe devolver ceros');

        $matriz = ['a' => [1, 100], 'b' => [2, 200], 'c' => [3, 300], 'd' => [4, 400]];
        $s = $ML->standardize($matriz);
        $this->addCase($results, 'standardize_preserva_claves', array_keys($s['matrix']) === ['a', 'b', 'c', 'd'], 'debe conservar las claves de fila');
        $okMedia = true;
        $okStd   = true;
        for ($c = 0; $c < 2; $c++) {
            $ms = $ML->meanStd(array_column($s['matrix'], $c));
            if (!$this->cerca($ms['mean'], 0)) { $okMedia = false; }
            if (!$this->cerca($ms['std'], 1)) { $okStd = false; }
        }
        $this->addCase($results, 'standardize_media_cero', $okMedia, 'cada columna debe tener media ~0');
        $this->addCase($results, 'standardize_std_uno', $okStd, 'cada columna debe tener std ~1');
        $this->addCase($results, 'standardize_devuelve_stats', count($s['means']) === 2 && count($s['stds']) === 2, 'debe devolver means y stds por columna');

        $const = $ML->standardize([[5, 1], [5, 2], [5, 3]]);
        $this->addCase($results, 'standardize_std_cero_usa_uno', $this->cerca($const['stds'][0], 1) && $this->cerca($const['matrix'][0][0], 0), 'columna constante: std 1 y valor 0');

        $reuse = $ML->standardize([[2.5, 250]], ['means' => $s['means'], 'stds' => $s['stds']]);
        $this->addCase($results, 'standardize_reutiliza_stats', $this->cerca($reuse['matrix'][0][0], 0) && $this->cerca($reuse['matrix'][0][1], 0), 'con las estadisticas de entrenamiento 2.5/250 son la media (0)');
        $e = $ML->standardize([]);
        $this->addCase($results, 'standardize_vacio', $e['matrix'] === [], 'vacio debe devolver matriz vacia');
        return $results;
    }

    /**
     * linearTrend: tendencia lineal y prediccion del siguiente punto.
     */
    public function tendenciaLinealTest(){
        $results = [];
        $ML = $this->helper();
        $t = $ML->linearTrend([1, 2, 3, 4]);
        $this->addCase($results, 'pendiente_1', $this->cerca($t['slope'], 1), 'slope='.$t['slope']);
        $this->addCase($results, 'intercepto_0', $this->cerca($t['intercept'], 0), 'intercept='.$t['intercept']);
        $this->addCase($results, 'siguiente_5', $this->cerca($t['next'], 5), 'next='.$t['next']);
        $d = $ML->linearTrend([10, 8, 6, 4]);
        $this->addCase($results, 'pendiente_negativa', $this->cerca($d['slope'], -2) && $this->cerca($d['next'], 2), 'slope='.$d['slope'].' next='.$d['next']);
        $u = $ML->linearTrend([7]);
        $this->addCase($results, 'un_punto_repite_valor', $u['slope'] === 0.0 && $this->cerca($u['next'], 7), 'n<2: pendiente 0 y next = valor');
        $e = $ML->linearTrend([]);
        $this->addCase($results, 'vacio_ceros', $e['slope'] === 0.0 && $e['next'] === 0.0, 'vacio debe devolver ceros');
        return $results;
    }

    /**
     * forestProbability: proporcion de arboles que votan 1.
     */
    public function probabilidadBosqueTest(){
        $results = [];
        $ML = $this->helper();
        [$s, $l] = $this->datosSeparables(120);
        $forest = new Phpml\Classification\Ensemble\RandomForest(15);
        $forest->train($s, $l);
        $alta = $ML->forestProbability($forest, [28, 2]);
        $baja = $ML->forestProbability($forest, [2, 2]);
        $this->addCase($results, 'probabilidad_en_rango', $alta >= 0.0 && $alta <= 1.0 && $baja >= 0.0 && $baja <= 1.0, 'alta='.$alta.' baja='.$baja);
        $this->addCase($results, 'clase_1_probable', $alta > 0.5, 'una muestra de clase 1 debe superar 0.5; p='.$alta);
        $this->addCase($results, 'clase_0_improbable', $baja < 0.5, 'una muestra de clase 0 debe quedar bajo 0.5; p='.$baja);
        $vacio = new Phpml\Classification\Ensemble\RandomForest(5);
        $this->addCase($results, 'sin_arboles_cero', $ML->forestProbability($vacio, [1, 1]) === 0.0, 'un bosque sin entrenar debe devolver 0.0');
        return $results;
    }

    /**
     * kmeansCluster: agrupacion, centroides y tamanos.
     */
    public function agrupacionKmeansTest(){
        $results = [];
        $ML = $this->helper();
        $pts = [];
        foreach ([[0, 0], [50, 50], [100, 0]] as $g => $base) {
            for ($i = 0; $i < 6; $i++) {
                $pts[1000 * ($g + 1) + $i] = [$base[0] + $i * 0.1, $base[1] + $i * 0.1];
            }
        }
        $r = $ML->kmeansCluster($pts, 3);
        $this->addCase($results, 'tres_clusters', count($r['sizes']) === 3, 'clusters='.count($r['sizes']));
        $this->addCase($results, 'tamanos_6', array_values($r['sizes']) === [6, 6, 6], 'sizes='.json_encode($r['sizes']));
        $this->addCase($results, 'asigna_todas_las_claves', count($r['assign']) === 18 && isset($r['assign'][1000], $r['assign'][3005]), 'assign='.count($r['assign']));
        $mismo = $r['assign'][1000] === $r['assign'][1005] && $r['assign'][2000] === $r['assign'][2005] && $r['assign'][1000] !== $r['assign'][2000] && $r['assign'][2000] !== $r['assign'][3000] && $r['assign'][1000] !== $r['assign'][3000];
        $this->addCase($results, 'grupos_separados', $mismo, 'cada grupo debe quedar en su propio cluster');
        $cent = $r['centroids'][$r['assign'][2000]];
        $this->addCase($results, 'centroide_media', $this->cerca($cent[0], 50.25) && $this->cerca($cent[1], 50.25), 'centroide='.json_encode($cent));
        $acot = $ML->kmeansCluster([1 => [0, 0], 2 => [1, 1]], 8);
        $this->addCase($results, 'k_acotado_a_muestras', count($acot['sizes']) === 2, 'k debe acotarse a count(samples)=2; clusters='.count($acot['sizes']));
        $vacio = $ML->kmeansCluster([], 3);
        $this->addCase($results, 'vacio', $vacio['assign'] === [] && $vacio['sizes'] === [], 'vacio debe devolver estructuras vacias');
        return $results;
    }

    /**
     * H-1: php-ml no debe lanzar bajo E_ALL con un manejador estricto (todo error => excepcion),
     * equivalente al manejador de F3. Cubre RandomForest/DecisionTree (riesgo de cancelacion) y KMeans.
     */
    public function sinAvisosDeprecatedTest(){
        $results = [];
        $prevRep = error_reporting(E_ALL);
        set_error_handler(function ($no, $msg, $file, $line) {
            if (!(error_reporting() & $no)) { return false; } // como el manejador de F3
            throw new ErrorException($msg, 0, $no, $file, $line);
        });
        try {
            $ML = new FunctionsMachineLearning();
            $ML->loadPhpMl();
            [$s, $l] = $this->datosSeparables(80);
            try {
                $rf = $ML->trainForest($s, $l, 5, 4);
                $p  = $ML->forestProbability($rf, [28, 2]);
                $this->addCase($results, 'entrenar_bosque_sin_errores', $p >= 0.0 && $p <= 1.0, 'p fuera de rango');
            } catch (Throwable $e) {
                $this->addCase($results, 'entrenar_bosque_sin_errores', false, $e->getMessage());
            }
            try {
                $r = $ML->kmeansCluster([1 => [0, 0], 2 => [0, 1], 3 => [9, 9], 4 => [9, 8]], 2);
                $this->addCase($results, 'kmeans_sin_errores', count($r['sizes']) === 2, 'clusters='.count($r['sizes']));
            } catch (Throwable $e) {
                $this->addCase($results, 'kmeans_sin_errores', false, $e->getMessage());
            }
            try {
                $t = $ML->linearTrend([1, 2, 3, 4]);
                $this->addCase($results, 'tendencia_sin_errores', $this->cerca($t['next'], 5), 'next='.$t['next']);
            } catch (Throwable $e) {
                $this->addCase($results, 'tendencia_sin_errores', false, $e->getMessage());
            }
            $this->addCase($results, 'error_reporting_restaurado', error_reporting() === E_ALL, 'error_reporting debe volver a E_ALL');
        } finally {
            restore_error_handler();
            error_reporting($prevRep);
        }
        return $results;
    }
}
