<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Helper transversal de aprendizaje automático (ADR-0020).
 *
 * Carga php-ml (`vendors/libs/php-ml/`), acota muestras, controla tiempo y memoria,
 * calcula métricas y envuelve los modelos que usan los informes predictivos de
 * bodegas, reservas_servicios, campanas y tokens_listado.
 *
 * Es lógica pura: no toca la base de datos ni las rutas. Los modelos de vectores soporte están prohibidos
 * (los modelos con binarios externos no están disponibles) y no se serializan objetos php-ml.
 */
class FunctionsMachineLearning {

	/** Indica si las clases de php-ml ya se precargaron. */
	private static $_preloaded = false;

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                 Constantes                                                      */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/** Muestras por defecto que se entregan a php-ml. */
	const MAX_SAMPLES_DEFAULT = 5000;
	/** Tope absoluto de muestras. */
	const MAX_SAMPLES_HARD    = 20000;
	/** Segundos máximos de reloj por recálculo. */
	const MAX_SECONDS         = 120;
	/** Memoria máxima por recálculo. */
	const MEMORY_LIMIT        = '256M';
	/** Segundos mínimos entre dos recálculos correctos. */
	const MIN_INTERVAL_SECONDS = 60;
	/** Minutos tras los cuales una ejecución activa se considera expirada. */
	const STALE_MINUTES       = 10;
	/** Ejecuciones correctas que se conservan por informe. */
	const KEEP_RUNS           = 3;
	/** Filas por inserción múltiple al guardar resultados. */
	const INSERT_CHUNK        = 500;

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Publicos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	/**
	 * Carga la librería php-ml (idempotente).
	 *
	 * @return bool true si la librería está disponible; false si falta el autoload.
	 *
	 * @example
	 * ```php
	 * $ML = new FunctionsMachineLearning();
	 * $ML->loadPhpMl(); // true
	 * ```
	 */
	public function loadPhpMl(): bool {
		$file = __DIR__ . '/../../libs/php-ml/autoload.php';
		if (!is_file($file)) {
			return false;
		}
		// php-ml 0.10 emite E_DEPRECATED en PHP 8.x al compilar/enlazar sus clases (y el manejador de
		// errores de F3 convierte cualquier aviso en un 500). Se precargan aquí, bajo supresión acotada,
		// todas las clases utilizables (se omite SupportVectorMachine: sin binarios libsvm).
		$this->runPhpMl(function () use ($file) {
			require_once $file;
			if (!self::$_preloaded) {
				self::$_preloaded = true;
				$src = dirname($file) . '/src';
				$it  = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS));
				foreach ($it as $f) {
					$path = $f->getPathname();
					if ($f->getExtension() !== 'php' || strpos($path, 'SupportVectorMachine') !== false) {
						continue;
					}
					$cls = 'Phpml\\' . str_replace('/', '\\', substr($path, strlen($src) + 1, -4));
					class_exists($cls) || interface_exists($cls) || trait_exists($cls);
				}
			}
		});
		return true;
	}

	/******************************************************************************/
	/**
	 * Ejecuta un callable que usa php-ml con E_DEPRECATED suprimido solo durante la llamada
	 * (error_reporting se restaura siempre en `finally`). Envolver aquí cualquier uso directo de
	 * clases php-ml (RandomForest, DecisionTree, KMeans, LeastSquares...) desde controladores.
	 *
	 * @param callable $fn Código que usa php-ml.
	 *
	 * @return mixed Lo que devuelva $fn.
	 *
	 * @example
	 * ```php
	 * $rf = $ML->runPhpMl(function () use ($s, $l) { $m = new Phpml\Classification\Ensemble\RandomForest(20); $m->train($s, $l); return $m; });
	 * ```
	 */
	public function runPhpMl(callable $fn) {
		$prev = error_reporting();
		error_reporting($prev & ~E_DEPRECATED);
		try {
			return $fn();
		} finally {
			error_reporting($prev);
		}
	}

	/******************************************************************************/
	/**
	 * Recorta un conjunto de filas a un máximo, de forma determinista (paso fijo).
	 *
	 * @param array $rows Filas de origen.
	 * @param int   $max  Tope solicitado; se acota a [1, MAX_SAMPLES_HARD].
	 *
	 * @return array ['rows' => filas resultantes, 'total' => filas de origen, 'capped' => bool].
	 *
	 * @example
	 * ```php
	 * $ML->capSamples($rows, 5000); // 12.000 filas => rows 5.000, total 12.000, capped true
	 * ```
	 */
	public function capSamples(array $rows, int $max = self::MAX_SAMPLES_DEFAULT): array {
		$max   = max(1, min($max, self::MAX_SAMPLES_HARD));
		$rows  = array_values($rows);
		$total = count($rows);
		if ($total <= $max) {
			return ['rows' => $rows, 'total' => $total, 'capped' => false];
		}
		$out = [];
		for ($i = 0; $i < $max; $i++) {
			$out[] = $rows[(int) floor($i * $total / $max)];
		}
		return ['rows' => $out, 'total' => $total, 'capped' => true];
	}

	/******************************************************************************/
	/**
	 * Inicia el presupuesto de un recálculo: fija el límite de tiempo y sube la memoria si hace falta.
	 *
	 * @param int $maxSeconds Segundos máximos de reloj.
	 *
	 * @return float Marca de tiempo de inicio (microtime(true)).
	 *
	 * @example
	 * ```php
	 * $t0 = $ML->beginBudget();
	 * ```
	 */
	public function beginBudget(int $maxSeconds = self::MAX_SECONDS): float {
		// Acotado: nunca por encima de MAX_SECONDS + 15 (margen para guardar/fallar la ejecución)
		@set_time_limit(min(max($maxSeconds, 1), self::MAX_SECONDS) + 15);
		$current = $this->_toBytes((string) ini_get('memory_limit'));
		$target  = $this->_toBytes(self::MEMORY_LIMIT);
		if ($current !== -1 && $current < $target) {
			@ini_set('memory_limit', self::MEMORY_LIMIT);
		}
		return microtime(true);
	}

	/******************************************************************************/
	/**
	 * Indica si el presupuesto de tiempo y memoria sigue vigente.
	 *
	 * Límite residual: php-ml no es interrumpible; el entrenamiento (RandomForest::train, KMeans::cluster)
	 * es una única llamada bloqueante. Solo se puede comprobar el presupuesto ANTES y DESPUÉS de cada fase
	 * (los controladores deben llamar aquí entre fases); el tope duro durante una fase lo da set_time_limit
	 * (CPU, MAX_SECONDS + 15) y memory_limit. Un fatal por esos topes no es capturable.
	 *
	 * @param float $startedAt  Valor devuelto por beginBudget.
	 * @param int   $maxSeconds Segundos máximos de reloj.
	 *
	 * @return bool true mientras no se haya superado el tiempo ni el 90 % de la memoria (pico).
	 *
	 * @example
	 * ```php
	 * if (!$ML->checkBudget($t0)) { throw new RuntimeException('Tiempo agotado'); }
	 * ```
	 */
	public function checkBudget(float $startedAt, int $maxSeconds = self::MAX_SECONDS): bool {
		if ((microtime(true) - $startedAt) >= $maxSeconds) {
			return false;
		}
		$limit = $this->_toBytes((string) ini_get('memory_limit'));
		return $limit === -1 || memory_get_peak_usage(true) < $limit * 0.9;
	}

	/******************************************************************************/
	/**
	 * Divide muestras y etiquetas en entrenamiento y prueba (estratificado).
	 *
	 * @param array $samples   Matriz de muestras.
	 * @param array $labels    Etiquetas (misma longitud).
	 * @param float $testRatio Proporción de prueba; se acota a [0.1, 0.5].
	 * @param int   $seed      Semilla (php-ml la usa solo si su generador la respeta; no garantiza reproducibilidad).
	 *
	 * @return array ['trainSamples','trainLabels','testSamples','testLabels'].
	 *
	 * @example
	 * ```php
	 * $s = $ML->splitTrainTest($samples, $labels, 0.2, 1);
	 * ```
	 */
	public function splitTrainTest(array $samples, array $labels, float $testRatio = 0.2, int $seed = 1): array {
		$testRatio = max(0.1, min(0.5, $testRatio));
		$split = $this->runPhpMl(function () use ($samples, $labels, $testRatio, $seed) {
			return new Phpml\CrossValidation\StratifiedRandomSplit(new Phpml\Dataset\ArrayDataset($samples, $labels), $testRatio, $seed);
		});
		return [
			'trainSamples' => $split->getTrainSamples(),
			'trainLabels'  => $split->getTrainLabels(),
			'testSamples'  => $split->getTestSamples(),
			'testLabels'   => $split->getTestLabels(),
		];
	}

	/******************************************************************************/
	/**
	 * Métricas de clasificación binaria para la clase positiva.
	 *
	 * @param array $actual    Etiquetas reales.
	 * @param array $predicted Etiquetas predichas.
	 * @param mixed $positive  Valor de la clase positiva.
	 *
	 * @return array accuracy, precision, recall, f1 (4 decimales; 0 si hay división por cero), tp, fp, fn, tn.
	 *
	 * @example
	 * ```php
	 * $ML->classificationMetrics([1,0,1,0], [1,0,0,0], 1); // accuracy 0.75, precision 1, recall 0.5
	 * ```
	 */
	public function classificationMetrics(array $actual, array $predicted, $positive): array {
		$tp = $fp = $fn = $tn = 0;
		$actual    = array_values($actual);
		$predicted = array_values($predicted);
		$n = min(count($actual), count($predicted));
		for ($i = 0; $i < $n; $i++) {
			$a = ($actual[$i] == $positive);
			$p = ($predicted[$i] == $positive);
			if ($a && $p) { $tp++; } elseif (!$a && $p) { $fp++; } elseif ($a && !$p) { $fn++; } else { $tn++; }
		}
		$accuracy  = $n > 0 ? ($tp + $tn) / $n : 0.0;
		$precision = ($tp + $fp) > 0 ? $tp / ($tp + $fp) : 0.0;
		$recall    = ($tp + $fn) > 0 ? $tp / ($tp + $fn) : 0.0;
		$f1        = ($precision + $recall) > 0 ? 2 * $precision * $recall / ($precision + $recall) : 0.0;
		return [
			'accuracy'  => round($accuracy, 4),
			'precision' => round($precision, 4),
			'recall'    => round($recall, 4),
			'f1'        => round($f1, 4),
			'tp' => $tp, 'fp' => $fp, 'fn' => $fn, 'tn' => $tn,
		];
	}

	/******************************************************************************/
	/**
	 * Métricas de regresión (error absoluto medio y raíz del error cuadrático medio).
	 *
	 * @param array $actual    Valores reales.
	 * @param array $predicted Valores predichos.
	 *
	 * @return array ['mae' => float, 'rmse' => float, 'n' => int]; ceros si no hay datos.
	 *
	 * @example
	 * ```php
	 * $ML->regressionMetrics([1,2,3], [1,2,5]); // mae 0.6667, rmse 1.1547, n 3
	 * ```
	 */
	public function regressionMetrics(array $actual, array $predicted): array {
		$actual    = array_values($actual);
		$predicted = array_values($predicted);
		$n = min(count($actual), count($predicted));
		if ($n === 0) {
			return ['mae' => 0.0, 'rmse' => 0.0, 'n' => 0];
		}
		$abs = 0.0;
		$sq  = 0.0;
		for ($i = 0; $i < $n; $i++) {
			$d    = (float) $actual[$i] - (float) $predicted[$i];
			$abs += abs($d);
			$sq  += $d * $d;
		}
		return ['mae' => round($abs / $n, 4), 'rmse' => round(sqrt($sq / $n), 4), 'n' => $n];
	}

	/******************************************************************************/
	/**
	 * Media y desviación estándar poblacional.
	 *
	 * @param array $values Valores numéricos.
	 *
	 * @return array ['mean' => float, 'std' => float]; ceros si está vacío.
	 *
	 * @example
	 * ```php
	 * $ML->meanStd([2,4,4,4,5,5,7,9]); // mean 5, std 2
	 * ```
	 */
	public function meanStd(array $values): array {
		$n = count($values);
		if ($n === 0) {
			return ['mean' => 0.0, 'std' => 0.0];
		}
		$mean = array_sum($values) / $n;
		$acc  = 0.0;
		foreach ($values as $v) {
			$acc += ((float) $v - $mean) * ((float) $v - $mean);
		}
		return ['mean' => (float) $mean, 'std' => sqrt($acc / $n)];
	}

	/******************************************************************************/
	/**
	 * Estandariza por columna (z-score). Preserva las claves de fila.
	 *
	 * @param array      $matrix Matriz numérica (filas de igual longitud).
	 * @param array|null $stats  Opcional ['means' => [...], 'stds' => [...]] para reutilizar estadísticas.
	 *
	 * @return array ['matrix' => matriz estandarizada, 'means' => [...], 'stds' => [...]] (std 0 => 1).
	 *
	 * @example
	 * ```php
	 * $r = $ML->standardize($train);
	 * $t = $ML->standardize($test, ['means' => $r['means'], 'stds' => $r['stds']]);
	 * ```
	 */
	public function standardize(array $matrix, ?array $stats = null): array {
		if (empty($matrix)) {
			return ['matrix' => [], 'means' => [], 'stds' => []];
		}
		$first = reset($matrix);
		$cols  = count($first);
		$means = [];
		$stds  = [];
		if ($stats !== null && isset($stats['means'], $stats['stds'])) {
			$means = array_values($stats['means']);
			$stds  = array_values($stats['stds']);
		} else {
			for ($c = 0; $c < $cols; $c++) {
				$ms      = $this->meanStd(array_column(array_map('array_values', $matrix), $c));
				$means[] = $ms['mean'];
				$stds[]  = $ms['std'];
			}
		}
		foreach ($stds as $c => $s) {
			if ($s == 0) { $stds[$c] = 1.0; }
		}
		$out = [];
		foreach ($matrix as $key => $row) {
			$row = array_values($row);
			$new = [];
			for ($c = 0; $c < $cols; $c++) {
				$new[] = ((float) $row[$c] - (float) ($means[$c] ?? 0)) / (float) ($stds[$c] ?? 1);
			}
			$out[$key] = $new;
		}
		return ['matrix' => $out, 'means' => $means, 'stds' => $stds];
	}

	/******************************************************************************/
	/**
	 * Tendencia lineal (mínimos cuadrados) sobre x = 1..n y predicción para x = n+1.
	 *
	 * @param array $series Serie de valores numéricos.
	 *
	 * @return array ['slope' => float, 'intercept' => float, 'next' => float]; con n < 2: pendiente 0 y valor repetido.
	 *
	 * @example
	 * ```php
	 * $ML->linearTrend([1,2,3,4]); // slope 1, next 5
	 * ```
	 */
	public function linearTrend(array $series): array {
		$series = array_values(array_map('floatval', $series));
		$n = count($series);
		if ($n === 0) {
			return ['slope' => 0.0, 'intercept' => 0.0, 'next' => 0.0];
		}
		if ($n < 2) {
			return ['slope' => 0.0, 'intercept' => $series[0], 'next' => $series[0]];
		}
		$samples = [];
		for ($x = 1; $x <= $n; $x++) {
			$samples[] = [$x];
		}
		return $this->runPhpMl(function () use ($samples, $series, $n) {
			$model = new Phpml\Regression\LeastSquares();
			$model->train($samples, $series);
			return [
				'slope'     => (float) $model->getCoefficients()[0],
				'intercept' => (float) $model->getIntercept(),
				'next'      => (float) $model->predict([$n + 1]),
			];
		});
	}

	/******************************************************************************/
	/**
	 * Entrena un RandomForest (árboles de decisión) con E_DEPRECATED suprimido de forma acotada.
	 * Llamada bloqueante no interrumpible: comprobar checkBudget antes y después.
	 *
	 * @param array $samples  Muestras.
	 * @param array $labels   Etiquetas.
	 * @param int   $trees    Número de árboles.
	 * @param int   $maxDepth Profundidad máxima de cada árbol.
	 *
	 * @return mixed RandomForest entrenado.
	 */
	public function trainForest(array $samples, array $labels, int $trees = 20, int $maxDepth = 5) {
		return $this->runPhpMl(function () use ($samples, $labels, $trees, $maxDepth) {
			$rf = new Phpml\Classification\Ensemble\RandomForest($trees);
			$rf->setClassifer(Phpml\Classification\DecisionTree::class, ['maxDepth' => $maxDepth]);
			$rf->train($samples, $labels);
			return $rf;
		});
	}

	/******************************************************************************/
	/**
	 * Probabilidad de clase 1 como proporción de árboles de un RandomForest que votan 1
	 * (php-ml no expone probabilidades; se lee la propiedad protegida `classifiers`).
	 *
	 * @param mixed $forest Phpml\Classification\Ensemble\RandomForest ya entrenado.
	 * @param array $sample Muestra (misma estructura que el entrenamiento).
	 *
	 * @return float Valor en [0, 1]; 0.0 si no hay árboles.
	 *
	 * @example
	 * ```php
	 * $p = $ML->forestProbability($forest, [0.3, 1.2, -0.5]);
	 * ```
	 */
	public function forestProbability($forest, array $sample): float {
		$trees = (function () { return $this->classifiers; })->call($forest);
		$total = count($trees);
		if ($total === 0) {
			return 0.0;
		}
		$votes = $this->runPhpMl(function () use ($trees, $sample) {
			$v = 0;
			foreach ($trees as $tree) {
				if ($tree->predict($sample) == 1) {
					$v++;
				}
			}
			return $v;
		});
		return $votes / $total;
	}

	/******************************************************************************/
	/**
	 * Agrupa muestras con KMeans y calcula los centroides como media por cluster.
	 *
	 * @param array $samples Muestras numéricas con clave = identificador original.
	 * @param int   $k       Número de clusters; se acota a [2, 8] y a count($samples).
	 *
	 * @return array ['assign' => clave => cluster, 'centroids' => [cluster => [..]], 'sizes' => [cluster => int]].
	 *
	 * @example
	 * ```php
	 * $r = $ML->kmeansCluster([10 => [0,0], 11 => [0,1], 12 => [9,9]], 2);
	 * ```
	 */
	public function kmeansCluster(array $samples, int $k): array {
		$count = count($samples);
		if ($count === 0) {
			return ['assign' => [], 'centroids' => [], 'sizes' => []];
		}
		$k = min(max(2, min(8, $k)), $count);
		// php-ml usa random_int: la inicialización NO es reproducible (no existe semilla)
		$clusters = $this->runPhpMl(function () use ($k, $samples) {
			return (new Phpml\Clustering\KMeans($k))->cluster($samples);
		});
		$assign = [];
		$cent   = [];
		$sizes  = [];
		foreach (array_values($clusters) as $idx => $points) {
			$sizes[$idx] = count($points);
			$sum = [];
			foreach ($points as $key => $point) {
				$assign[$key] = $idx;
				foreach (array_values($point) as $c => $v) {
					$sum[$c] = ($sum[$c] ?? 0.0) + (float) $v;
				}
			}
			$cent[$idx] = array_map(function ($s) use ($idx, $sizes) { return $sizes[$idx] > 0 ? $s / $sizes[$idx] : 0.0; }, $sum);
		}
		return ['assign' => $assign, 'centroids' => $cent, 'sizes' => $sizes];
	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Privados                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	/**
	 * Convierte un valor de php.ini (p. ej. '256M') a bytes.
	 *
	 * @param string $value Valor de memory_limit.
	 *
	 * @return int Bytes; -1 si es ilimitado.
	 */
	private function _toBytes(string $value): int {
		$value = trim($value);
		if ($value === '' || $value === '-1') {
			return -1;
		}
		$unit = strtoupper(substr($value, -1));
		$num  = (int) $value;
		switch ($unit) {
			case 'G': return $num * 1024 * 1024 * 1024;
			case 'M': return $num * 1024 * 1024;
			case 'K': return $num * 1024;
		}
		return $num;
	}
}
