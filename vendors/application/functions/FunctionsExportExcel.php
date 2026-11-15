<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Helper transversal de exportación a Excel real (.xlsx) generado en servidor (ADR-0021).
 *
 * Carga PhpSpreadsheet (`vendors/libs/PhpSpreadsheet/vendor/autoload.php`), comprueba las extensiones de PHP,
 * escribe hojas tipadas (texto, entero, decimal, importe, porcentaje, fecha), neutraliza la inyección de
 * fórmulas y emite la descarga. Lo usan los controladores de informes y listados con un método `exportExcel`.
 *
 * Es lógica pura: no toca la base de datos ni las rutas, y no hereda de ninguna clase. Solo usa `Writer\Xlsx`
 * (nunca un lector de archivos subidos). Todo texto se escribe con `setCellValueExplicit(TYPE_STRING)`.
 */
class FunctionsExportExcel {

	/** Metadatos de la última construcción (filas escritas y truncado). */
	private $_meta = ['rows' => 0, 'truncated' => false];

	/** Manejador del bloqueo de concurrencia (se libera al terminar la petición, también tras `exit`). */
	private $_lockHandle = null;

	/** Estado de la frecuencia pendiente de confirmar: ['base','max','window','committed'] o null. */
	private $_rate = null;

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                 Constantes                                                      */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/** Filas por bloque de lectura y mínimo del tope de exportación (= ConfigAPP::APP['N_MaxItems']). El tope real es maxRows(). */
	const MAX_ROWS      = 2500;
	/** Techo absoluto del tope de exportación (ConfigAPP::APP['N_MaxExportRows'] no puede superarlo). */
	const HARD_MAX_ROWS = 100000;
	/** Memoria máxima admitida al escalar el presupuesto con el tamaño del libro. */
	const MEMORY_LIMIT_MAX_MB = 1024;
	/** Segundos máximos admitidos al escalar el presupuesto con el tamaño del libro. */
	const MAX_SECONDS_HARD    = 300;
	/** Columnas máximas por hoja. */
	const MAX_COLS      = 60;
	/** Memoria máxima durante la generación. */
	const MEMORY_LIMIT  = '256M';
	/** Segundos máximos de reloj por exportación. */
	const MAX_SECONDS   = 60;
	/** Ancho mínimo de columna. */
	const COL_WIDTH_MIN = 8;
	/** Ancho máximo de columna. */
	const COL_WIDTH_MAX = 60;
	/** Exportaciones por usuario permitidas en la ventana (Q12). */
	const RATE_MAX      = 10;
	/** Ventana, en segundos, del límite de frecuencia por usuario (Q12). */
	const RATE_WINDOW   = 60;

	/** Extensiones de PHP exigidas por la librería (sin gd: solo se usa para imágenes). */
	private const REQUIRED_EXTENSIONS = [
		'ctype', 'dom', 'fileinfo', 'filter', 'iconv', 'libxml', 'mbstring',
		'simplexml', 'xml', 'xmlreader', 'xmlwriter', 'zip', 'zlib',
	];

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Publicos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	/**
	 * Carga la librería PhpSpreadsheet (idempotente).
	 *
	 * @return bool true si la librería está disponible; false si falta el autoload.
	 *
	 * @example
	 * ```php
	 * $Export = new FunctionsExportExcel();
	 * $Export->loadPhpSpreadsheet(); // true
	 * ```
	 */
	public function loadPhpSpreadsheet(): bool {
		static $loaded = false;
		if ($loaded) {
			return true;
		}
		$file = __DIR__ . '/../../libs/PhpSpreadsheet/vendor/autoload.php';
		if (!is_file($file)) {
			return false;
		}
		require_once $file;
		return $loaded = class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet');
	}

	/******************************************************************************/
	/**
	 * Devuelve las extensiones de PHP requeridas que no están cargadas.
	 *
	 * @return array Lista de nombres; [] si están todas.
	 *
	 * @example
	 * ```php
	 * $Export->missingExtensions(); // []
	 * ```
	 */
	public function missingExtensions(): array {
		$missing = [];
		foreach (self::REQUIRED_EXTENSIONS as $ext) {
			if (!extension_loaded($ext)) {
				$missing[] = $ext;
			}
		}
		return $missing;
	}

	/******************************************************************************/
	/**
	 * Sanea un nombre de archivo a [A-Za-z0-9_-] (máximo 60), 'exportacion' si queda vacío.
	 *
	 * @param string $name Nombre propuesto (sin extensión).
	 * @return string Nombre seguro, sin separadores ni puntos.
	 *
	 * @example
	 * ```php
	 * $Export->sanitizeFileName('../x y.php'); // 'x_y_php'
	 * ```
	 */
	public function sanitizeFileName(string $name): string {
		$name = preg_replace('/[^A-Za-z0-9_-]+/', '_', $name);
		$name = trim((string) $name, '_-');
		$name = substr($name, 0, 60);
		$name = trim($name, '_-');
		return $name === '' ? 'exportacion' : $name;
	}

	/******************************************************************************/
	/**
	 * Sanea un texto para una celda: quita caracteres de control (XML inválido), recorta a 32.767
	 * y señala si debe llevar quotePrefix (empieza por = + - @ tabulador o retorno de carro).
	 *
	 * @param string $v Texto crudo.
	 * @return array [string texto, bool quotePrefix]
	 *
	 * @example
	 * ```php
	 * $Export->sanitizeText('=1+1'); // ['=1+1', true]
	 * ```
	 */
	public function sanitizeText(string $v): array {
		$v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $v);
		$v = (string) $v;
		if (function_exists('mb_substr')) {
			if (mb_strlen($v, 'UTF-8') > 32767) {
				$v = mb_substr($v, 0, 32767, 'UTF-8');
			}
		} elseif (strlen($v) > 32767) {
			$v = substr($v, 0, 32767);
		}
		$quote = $v !== '' && strpos("=+-@\t\r", $v[0]) !== false;
		return [$v, $quote];
	}

	/******************************************************************************/
	/**
	 * Tope de filas de una exportación: ConfigAPP::APP['N_MaxExportRows'] acotado entre
	 * MAX_ROWS (N_MaxItems) y HARD_MAX_ROWS. Sin configuración vale MAX_ROWS.
	 *
	 * @return int Filas máximas por hoja.
	 *
	 * @example
	 * ```php
	 * $max = FunctionsExportExcel::maxRows(); // 20000 por defecto
	 * ```
	 */
	public static function maxRows(): int {
		$cfg = (class_exists('ConfigAPP') && isset(ConfigAPP::APP['N_MaxExportRows']))
			? (int) ConfigAPP::APP['N_MaxExportRows']
			: self::MAX_ROWS;
		return max(self::MAX_ROWS, min($cfg, self::HARD_MAX_ROWS));
	}

	/******************************************************************************/
	/**
	 * Memoria y segundos para generar el libro: los mínimos (MEMORY_LIMIT / MAX_SECONDS)
	 * crecen con el número de celdas (~2 KB y 1 s cada 4000 celdas) hasta los máximos.
	 *
	 * @param array $sheets Hojas (ver `build`).
	 * @return array ['memory' => string ej. '512M', 'seconds' => int]
	 */
	public function resourceBudget(array $sheets): array {
		$cells = 0;
		foreach ($sheets as $spec) {
			$cells += count($spec['rows'] ?? []) * min(count($spec['columns'] ?? []), self::MAX_COLS);
		}
		$minMb = (int) ($this->toBytes(self::MEMORY_LIMIT) / 1048576);
		$mb    = max($minMb, min(self::MEMORY_LIMIT_MAX_MB, 64 + (int) ceil($cells * 2 / 1024)));
		$secs  = max(self::MAX_SECONDS, min(self::MAX_SECONDS_HARD, 30 + (int) ceil($cells / 4000)));
		return ['memory' => $mb . 'M', 'seconds' => $secs];
	}

	/******************************************************************************/
	/**
	 * Construye el libro y lo guarda en un temporal. Útil en pruebas; `download` lo usa internamente.
	 * Tras la llamada, `lastMeta()` informa de las filas escritas y si hubo truncado.
	 *
	 * @param array $sheets  Hojas (ver especificación en specs/technical.md §2): title, columns, rows, groups?, totals?.
	 * @param array $options filters, user, truncated, limit, info, autofilter.
	 * @return string Ruta del .xlsx temporal (el llamador lo borra).
	 * @throws \Throwable Si la librería no está disponible o falla la escritura.
	 *
	 * @example
	 * ```php
	 * $path = $Export->build([[ 'title' => 'Datos', 'columns' => [['key' => 'a', 'label' => 'A', 'type' => 'text']], 'rows' => [['a' => 'x']] ]]);
	 * ```
	 */
	public function build(array $sheets, array $options = []): string {
		if (!$this->loadPhpSpreadsheet()) {
			throw new RuntimeException('PhpSpreadsheet no disponible');
		}
		$max   = self::maxRows();
		$limit = (int) ($options['limit'] ?? $max);
		$limit = ($limit < 1 || $limit > $max) ? $max : $limit;

		$book = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$book->removeSheetByIndex(0);
		$total     = 0;
		$truncated = !empty($options['truncated']);
		$usados    = [];
		foreach ($sheets as $i => $spec) {
			$ws = $book->createSheet();
			$ws->setTitle($this->uniqueTitle((string) ($spec['title'] ?? ''), $i, $usados));
			$res        = $this->writeSheet($ws, $spec, $limit);
			$total     += $res['rows'];
			$truncated  = $truncated || $res['truncated'];
		}
		if ($book->getSheetCount() === 0) {
			$book->createSheet()->setTitle('Datos');
		}
		$this->_meta = ['rows' => $total, 'truncated' => $truncated];

		if (($options['info'] ?? true) !== false) {
			$ws = $book->createSheet();
			$ws->setTitle($this->uniqueTitle('Filtros', count($sheets), $usados));
			$this->writeInfoSheet($ws, $options, $total, $truncated, $limit);
		}
		$book->setActiveSheetIndex(0);

		$path = tempnam(sys_get_temp_dir(), 'xlsx');
		if ($path === false) {
			throw new RuntimeException('No se pudo crear el temporal');
		}
		try {
			$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book);
			$writer->setPreCalculateFormulas(false);
			$writer->save($path);
		} catch (\Throwable $e) {
			@unlink($path);
			throw $e;
		} finally {
			$book->disconnectWorksheets();
			unset($book);
		}
		return $path;
	}

	/******************************************************************************/
	/**
	 * Metadatos de la última construcción.
	 *
	 * @return array ['rows' => int, 'truncated' => bool]
	 */
	public function lastMeta(): array {
		return $this->_meta;
	}

	/******************************************************************************/
	/**
	 * Límite específico de la exportación (Q12): una exportación simultánea por usuario y un máximo de
	 * `RATE_MAX` por `RATE_WINDOW` segundos. Se llama al inicio de `exportExcel`, antes de consultar la BD.
	 *
	 * `acquire()` NO gasta cuota: toma el bloqueo de concurrencia (`flock` no bloqueante sobre un temporal por
	 * usuario) y comprueba que la ventana no esté agotada. La marca de frecuencia solo se apunta con `commit()`
	 * (o con `download()` si el controlador no la confirmó), de modo que los rechazos 405/400/500 no consumen
	 * los 10/min. El bloqueo lo libera `release()` o, en su defecto, el sistema al terminar la petición.
	 * La frecuencia se guarda en otro temporal (`xlsxguard_*.json`) como lista de marcas de tiempo; los archivos
	 * `xlsxguard_*` no los borra `cleanOldTemps()` (H-01). Independiente del `RateLimiter` global. Fail-open si no
	 * se puede escribir en el temporal. Si devuelve un mensaje (429) el bloqueo ya queda liberado.
	 *
	 * @param string $userKey        Identificador del usuario (UserID de sesión; o la IP si no hay).
	 * @param int    $maxPerWindow   Exportaciones permitidas en la ventana.
	 * @param int    $windowSeconds  Ventana en segundos.
	 * @return string '' si puede continuar; si no, el mensaje (genérico) para `Response::error(..., 429)`.
	 *
	 * @example
	 * ```php
	 * $Export  = new FunctionsExportExcel();
	 * $limite  = $Export->acquire($userId);
	 * if ($limite !== '') { $Export->auditReject($this->controllerName, $ruta, 429); Response::error($limite, 429); }
	 * $filtro  = $this->armarFiltro();            // si es inválido: $Export->release() + auditReject(..., 400) + 400
	 * $Export->commit();                          // filtro válido: ahora sí cuenta para el límite
	 * // ... consulta ... $Export->download(...)
	 * ```
	 */
	public function acquire(string $userKey, int $maxPerWindow = self::RATE_MAX, int $windowSeconds = self::RATE_WINDOW): string {
		$this->_rate = null;
		$base = rtrim(sys_get_temp_dir(), '/') . '/xlsxguard_' . substr(hash('sha256', $userKey), 0, 24);

		// Concurrencia: una exportación simultánea por usuario
		$lock = @fopen($base . '.lock', 'c');
		if ($lock !== false) {
			if (!flock($lock, LOCK_EX | LOCK_NB)) {
				fclose($lock);
				return 'Ya hay una exportación en curso. Espere a que termine.';
			}
			$this->_lockHandle = $lock;
		}

		// Frecuencia: solo se comprueba (la marca se apunta en commit())
		$fp = @fopen($base . '.json', 'c+');
		if ($fp === false) {
			return '';
		}
		$blocked = false;
		if (flock($fp, LOCK_SH)) {
			$marks   = $this->validMarks((string) stream_get_contents($fp), time(), $windowSeconds);
			$blocked = count($marks) >= $maxPerWindow;
			flock($fp, LOCK_UN);
		}
		fclose($fp);
		if ($blocked) {
			$this->release();
			return 'Demasiadas exportaciones en poco tiempo. Intente nuevamente en un minuto.';
		}
		$this->_rate = ['base' => $base, 'max' => $maxPerWindow, 'window' => $windowSeconds, 'committed' => false];
		return '';
	}

	/******************************************************************************/
	/**
	 * Confirma la exportación y apunta la marca de frecuencia (Q12). Se llama tras validar el filtro con éxito y
	 * antes de la consulta/descarga. Idempotente: una segunda llamada (o la de `download()`) no cuenta de nuevo.
	 * No hace nada si `acquire()` no concedió el paso o si ya se llamó a `release()`.
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * $filtro = $this->armarFiltro();
	 * $Export->commit();
	 * ```
	 */
	public function commit(): void {
		if ($this->_rate === null || $this->_rate['committed']) {
			return;
		}
		$this->_rate['committed'] = true;
		$fp = @fopen($this->_rate['base'] . '.json', 'c+');
		if ($fp === false) {
			return;
		}
		if (flock($fp, LOCK_EX)) {
			$now     = time();
			$marks   = $this->validMarks((string) stream_get_contents($fp), $now, (int) $this->_rate['window']);
			$marks[] = $now;
			ftruncate($fp, 0);
			rewind($fp);
			fwrite($fp, json_encode($marks));
			fflush($fp);
			flock($fp, LOCK_UN);
		}
		fclose($fp);
	}

	/******************************************************************************/
	/**
	 * Libera siempre el bloqueo de concurrencia tomado por `acquire` y descarta una confirmación pendiente
	 * (tras `release()`, `commit()` no cuenta). Llamar en todo rechazo posterior a `acquire` (400/500). `download()`
	 * termina con `exit` y el sistema lo libera.
	 *
	 * @return void
	 */
	public function release(): void {
		if ($this->_lockHandle !== null) {
			@flock($this->_lockHandle, LOCK_UN);
			@fclose($this->_lockHandle);
			$this->_lockHandle = null;
		}
		$this->_rate = null;
	}

	/******************************************************************************/
	/**
	 * Registra la exportación en `AuditLogger` (Q11). Solo metadatos: controlador, usuario, ruta, nº de filas,
	 * truncado y CLAVES de los filtros usados; nunca los valores (pueden ser datos personales) ni el contenido.
	 *
	 * @param string $controller Nombre del controlador (p. ej. `informeReservaServicio`).
	 * @param string $userId     UserID de sesión.
	 * @param array  $filters    Filtros legibles (`label` de `armarFiltro`): solo se registran sus claves.
	 * @return void
	 */
	public function audit(string $controller, string $userId, array $filters = []): void {
		if (!class_exists('AuditLogger')) {
			return;
		}
		$meta = $this->_meta;
		AuditLogger::log(
			'export_excel',
			'controlador=' . $controller
			. ' usuario=' . ($userId !== '' ? $userId : '0')
			. ' ruta=' . $this->routePath()
			. ' filas=' . (int) $meta['rows']
			. ' truncado=' . ($meta['truncated'] ? '1' : '0')
			. ' filtros=' . $this->filterKeys($filters)
		);
	}

	/******************************************************************************/
	/**
	 * Registra en `AuditLogger` un rechazo de la exportación (H-04): evento `export_excel_rechazo` con
	 * controlador, ruta (mismo saneado que `audit`), código HTTP y SOLO las claves de los filtros (nunca valores).
	 *
	 * @param string $controller Nombre del controlador.
	 * @param string $route      Ruta; si es '' se toma de `REQUEST_URI`. Se sanea a [A-Za-z0-9_/-].
	 * @param int    $code       Código devuelto (405, 429, 400, 500...).
	 * @param array  $filterKeys Claves de filtro (lista de nombres, o array asociativo: se usan sus claves; los valores se ignoran).
	 * @return void
	 *
	 * @example
	 * ```php
	 * $Export->release();
	 * $Export->auditReject($this->controllerName, '', 400, array_keys($_POST));   // solo claves
	 * Response::error('Filtro inválido', 400);
	 * ```
	 */
	public function auditReject(string $controller, string $route, int $code, array $filterKeys = []): void {
		if (!class_exists('AuditLogger')) {
			return;
		}
		AuditLogger::log('export_excel_rechazo', $this->rejectDetail($controller, $route, $code, $filterKeys));
	}

	/******************************************************************************/
	/**
	 * Genera el .xlsx y lo emite como descarga (cabeceras + archivo + exit). Ante cualquier fallo
	 * responde un JSON genérico (503 si la librería/extensiones faltan; 500 en otro caso).
	 *
	 * @param string $fileName Nombre sin extensión (se sanea).
	 * @param array  $sheets   Hojas (ver `build`).
	 * @param array  $options  Opciones (ver `build`) más `'audit' => ['controller' => string, 'userId' => string]`
	 *                         para registrar la exportación en `AuditLogger` (Q11) con las filas reales.
	 * @return void No retorna: termina la petición.
	 */
	public function download(string $fileName, array $sheets, array $options = []): void {
		// Presupuesto proporcional al tamaño del libro (celdas = filas x columnas)
		$budget = $this->resourceBudget($sheets);
		$mem = ini_get('memory_limit');
		if ($mem !== '-1' && $this->toBytes((string) $mem) < $this->toBytes($budget['memory'])) {
			@ini_set('memory_limit', $budget['memory']);
		}
		@set_time_limit($budget['seconds']);

		$missing = $this->loadPhpSpreadsheet() ? $this->missingExtensions() : ['PhpSpreadsheet'];
		if (!empty($missing)) {
			error_log('FunctionsExportExcel: exportación no disponible, falta: ' . implode(',', $missing));
			Response::error('Exportación no disponible', 503);
			return;
		}

		// Compatibilidad: si el controlador no confirmó el límite, se cuenta aquí (una sola vez)
		$this->commit();

		$this->cleanOldTemps();
		$path = null;
		try {
			$path = $this->build($sheets, $options);
			$meta = $this->_meta;
			// Q11: auditoría (solo metadatos) una vez conocidas las filas reales y antes de emitir el archivo
			if (!empty($options['audit']) && is_array($options['audit'])) {
				$this->audit((string) ($options['audit']['controller'] ?? ''), (string) ($options['audit']['userId'] ?? ''), (array) ($options['filters'] ?? []));
			}
			$size = (int) filesize($path);
			$name = $this->sanitizeFileName($fileName) . '.xlsx';

			while (ob_get_level() > 0) {
				@ob_end_clean();
			}
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment; filename="' . $name . '"; filename*=UTF-8\'\'' . rawurlencode($name));
			header('Content-Length: ' . $size);
			header('Cache-Control: no-store, max-age=0');
			header('Pragma: no-cache');
			header('X-Content-Type-Options: nosniff');
			header('X-Export-Truncated: ' . ($meta['truncated'] ? '1' : '0'));
			header('X-Export-Rows: ' . (int) $meta['rows']);
			if (!empty($_SESSION['csrf_token'])) {
				header('X-CSRF-Token: ' . $_SESSION['csrf_token']);
			}
			readfile($path);
		} catch (\Throwable $e) {
			if ($path !== null) {
				@unlink($path);
				$path = null;
			}
			error_log('FunctionsExportExcel: ' . get_class($e) . ': ' . $e->getMessage());
			Response::error('No se pudo generar el archivo', 500);
			return;
		} finally {
			if ($path !== null) {
				@unlink($path);
			}
		}
		exit;
	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Privados                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	/**
	 * Escribe una hoja: grupos, cabecera, filas tipadas, formatos, anchos, congelado, autofiltro y total.
	 *
	 * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws Hoja destino.
	 * @param array $spec  Especificación de la hoja.
	 * @param int   $limit Filas máximas.
	 * @return array ['rows' => int, 'truncated' => bool]
	 */
	private function writeSheet($ws, array $spec, int $limit): array {
		$Coord = '\\PhpOffice\\PhpSpreadsheet\\Cell\\Coordinate';
		$columns = array_slice(array_values($spec['columns'] ?? []), 0, self::MAX_COLS);
		$rows    = array_values($spec['rows'] ?? []);
		$truncated = count($rows) > $limit;
		if ($truncated) {
			$rows = array_slice($rows, 0, $limit);
		}
		$nCols = count($columns);
		$r     = 1;

		// Fila de grupos (opcional)
		if (!empty($spec['groups']) && $nCols > 0) {
			foreach ($spec['groups'] as $g) {
				$from = max(0, (int) ($g['from'] ?? 0));
				$to   = min($nCols - 1, (int) ($g['to'] ?? $from));
				if ($from > $to) {
					continue;
				}
				$a = $Coord::stringFromColumnIndex($from + 1) . $r;
				$this->setText($ws, $a, (string) ($g['label'] ?? ''));
				if ($to > $from) {
					$ws->mergeCells($a . ':' . $Coord::stringFromColumnIndex($to + 1) . $r);
				}
				$ws->getStyle($a)->getFont()->setBold(true);
				$ws->getStyle($a)->getAlignment()->setHorizontal('center');
			}
			$r++;
		}

		// Cabecera
		$headerRow = $r;
		$widths    = [];
		foreach ($columns as $c => $col) {
			$label = (string) ($col['label'] ?? $col['key'] ?? '');
			$this->setText($ws, $Coord::stringFromColumnIndex($c + 1) . $headerRow, $label);
			$widths[$c] = $this->strLen($label);
		}
		if ($nCols > 0) {
			$rng = 'A' . $headerRow . ':' . $Coord::stringFromColumnIndex($nCols) . $headerRow;
			$ws->getStyle($rng)->getFont()->setBold(true);
			$ws->getStyle($rng)->getFill()->setFillType('solid')->getStartColor()->setARGB('FFE9ECEF');
		}

		// Filas
		$r++;
		$firstData = $r;
		$sums      = [];
		foreach ($rows as $n => $row) {
			foreach ($columns as $c => $col) {
				$key  = $col['key'] ?? null;
				$val  = ($key !== null && is_array($row)) ? ($row[$key] ?? null) : null;
				$cell = $Coord::stringFromColumnIndex($c + 1) . $r;
				$len  = $this->writeCell($ws, $cell, $val, $col, $sums, $c);
				if ($n < 200 && $len > $widths[$c]) {
					$widths[$c] = $len;
				}
			}
			$r++;
		}
		$lastData = $r - 1;

		// Fila de total (valores numéricos propios, sin fórmulas)
		if (!empty($spec['totals']) && $nCols > 0) {
			$tot = $spec['totals'];
			$this->setText($ws, 'A' . $r, (string) ($tot['label'] ?? 'Total'));
			foreach ((array) ($tot['sum'] ?? []) as $k) {
				foreach ($columns as $c => $col) {
					if (($col['key'] ?? null) === $k) {
						$cell = $Coord::stringFromColumnIndex($c + 1) . $r;
						$ws->setCellValueExplicit($cell, (float) ($sums[$c] ?? 0), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
						$this->applyFormat($ws, $cell, $col);
					}
				}
			}
			$ws->getStyle('A' . $r . ':' . $Coord::stringFromColumnIndex($nCols) . $r)->getFont()->setBold(true);
		}

		// Anchos, congelado, autofiltro
		foreach ($columns as $c => $col) {
			$w = isset($col['width']) ? (float) $col['width'] : min(max($widths[$c] + 2, self::COL_WIDTH_MIN), self::COL_WIDTH_MAX);
			$ws->getColumnDimension($Coord::stringFromColumnIndex($c + 1))->setWidth(max(self::COL_WIDTH_MIN, min($w, self::COL_WIDTH_MAX)));
		}
		$ws->freezePane('A' . ($headerRow + 1));
		if ($nCols > 0 && $lastData >= $firstData && ($spec['autofilter'] ?? true)) {
			$ws->setAutoFilter('A' . $headerRow . ':' . $Coord::stringFromColumnIndex($nCols) . $lastData);
		}
		return ['rows' => count($rows), 'truncated' => $truncated];
	}

	/******************************************************************************/
	/**
	 * Escribe la hoja de información (filtros, usuario, fecha, filas, truncado e info predictiva).
	 *
	 * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws Hoja destino.
	 * @param array $options Opciones de `build`.
	 * @param int   $rows    Filas exportadas.
	 * @param bool  $truncated Si el resultado se truncó.
	 * @param int   $limit   Tope aplicado.
	 * @return void
	 */
	private function writeInfoSheet($ws, array $options, int $rows, bool $truncated, int $limit): void {
		$lines = [['Exportación', date('d-m-Y H:i')]];
		if (!empty($options['user'])) {
			$lines[] = ['Usuario', (string) $options['user']];
		}
		$lines[] = ['Filas exportadas', (string) $rows];
		if ($truncated) {
			$lines[] = ['Aviso', 'Resultado truncado a ' . $limit . ' filas: acote los filtros para obtener el resto'];
		}
		$lines[] = ['', ''];
		$lines[] = ['Filtros aplicados', ''];
		$filters = (array) ($options['filters'] ?? []);
		if (empty($filters)) {
			$lines[] = ['(sin filtros)', ''];
		}
		foreach ($filters as $k => $v) {
			$lines[] = [(string) $k, is_array($v) ? implode(', ', array_map('strval', $v)) : (string) $v];
		}
		if (!empty($options['info'])) {
			$lines[] = ['', ''];
			foreach ((array) $options['info'] as $k => $v) {
				$lines[] = [(string) $k, is_array($v) ? implode(', ', array_map('strval', $v)) : (string) $v];
			}
		}
		$r = 1;
		foreach ($lines as $l) {
			$this->setText($ws, 'A' . $r, $l[0]);
			$this->setText($ws, 'B' . $r, $l[1]);
			$ws->getStyle('A' . $r)->getFont()->setBold(true);
			$r++;
		}
		$ws->getColumnDimension('A')->setWidth(24);
		$ws->getColumnDimension('B')->setWidth(60);
	}

	/******************************************************************************/
	/**
	 * Escribe una celda de datos según el tipo de la columna. Devuelve la longitud visible (para el ancho).
	 */
	private function writeCell($ws, string $cell, $val, array $col, array &$sums, int $c): int {
		$type = $col['type'] ?? 'text';
		if ($val === null || $val === '' || is_array($val) || is_object($val) && !($val instanceof \DateTimeInterface)) {
			return 0;
		}
		switch ($type) {
			case 'int':
			case 'decimal':
			case 'money':
			case 'percent':
				if (!is_numeric($val)) {
					return 0;
				}
				$num = (float) $val;
				if ($type === 'percent' && (float) ($col['percentScale'] ?? 1) === 100.0) {
					$num = $num / 100;
				}
				$ws->setCellValueExplicit($cell, $type === 'int' ? (int) $num : $num, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
				$this->applyFormat($ws, $cell, $col);
				$sums[$c] = ($sums[$c] ?? 0) + $num;
				return strlen((string) $val) + 2;
			case 'date':
			case 'datetime':
				$dt = $this->toDateTime($val);
				if ($dt === null) {
					return 0;
				}
				$ws->setCellValueExplicit($cell, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($dt), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
				$this->applyFormat($ws, $cell, $col);
				return $type === 'date' ? 10 : 16;
			default:
				$len = $this->setText($ws, $cell, is_bool($val) ? ($val ? '1' : '0') : (string) $val);
				return $len;
		}
	}

	/******************************************************************************/
	/** Escribe un texto con TYPE_STRING y quotePrefix si procede. Devuelve su longitud (máx. de línea). */
	private function setText($ws, string $cell, string $text): int {
		[$t, $quote] = $this->sanitizeText($text);
		if ($t === '') {
			return 0;
		}
		$ws->setCellValueExplicit($cell, $t, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
		if ($quote) {
			$ws->getStyle($cell)->setQuotePrefix(true);
		}
		return $this->strLen($t);
	}

	/** Aplica el formato numérico/fecha de la columna. */
	private function applyFormat($ws, string $cell, array $col): void {
		$type = $col['type'] ?? 'text';
		$dec  = isset($col['decimals']) ? max(0, (int) $col['decimals']) : ($type === 'decimal' ? 2 : ($type === 'percent' ? 2 : 0));
		$fmt  = null;
		switch ($type) {
			case 'int':      $fmt = '0'; break;
			case 'decimal':  $fmt = $dec > 0 ? '0.' . str_repeat('0', $dec) : '0'; break;
			case 'money':    $fmt = $dec > 0 ? '#,##0.' . str_repeat('0', $dec) : '#,##0'; break;
			case 'percent':  $fmt = $dec > 0 ? '0.' . str_repeat('0', $dec) . '%' : '0%'; break;
			case 'date':     $fmt = 'dd-mm-yyyy'; break;
			case 'datetime': $fmt = 'dd-mm-yyyy hh:mm'; break;
		}
		if ($fmt !== null) {
			$ws->getStyle($cell)->getNumberFormat()->setFormatCode($fmt);
		}
	}

	/** Convierte a DateTime; '0000-00-00' y valores inválidos -> null. */
	private function toDateTime($val): ?\DateTimeInterface {
		if ($val instanceof \DateTimeInterface) {
			return $val;
		}
		$s = trim((string) $val);
		if ($s === '' || strpos($s, '0000-00-00') === 0) {
			return null;
		}
		try {
			return new \DateTime($s);
		} catch (\Throwable $e) {
			return null;
		}
	}

	/** Título de hoja válido (sin []:*?/\ y <= 31) y único. */
	private function uniqueTitle(string $title, int $i, array &$usados): string {
		$t = trim((string) preg_replace('/[\[\]:*?\/\\\\]/', '', $title));
		$t = function_exists('mb_substr') ? mb_substr($t, 0, 31, 'UTF-8') : substr($t, 0, 31);
		if ($t === '') {
			$t = 'Hoja' . ($i + 1);
		}
		$base = $t;
		$n    = 2;
		while (in_array(strtolower($t), $usados, true)) {
			$suf = ' (' . $n++ . ')';
			$t   = (function_exists('mb_substr') ? mb_substr($base, 0, 31 - strlen($suf), 'UTF-8') : substr($base, 0, 31 - strlen($suf))) . $suf;
		}
		$usados[] = strtolower($t);
		return $t;
	}

	/** Longitud visible de un texto (línea más larga). */
	private function strLen(string $s): int {
		$max = 0;
		foreach (explode("\n", $s) as $line) {
			$l   = function_exists('mb_strlen') ? mb_strlen($line, 'UTF-8') : strlen($line);
			$max = max($max, $l);
		}
		return $max;
	}

	/** Convierte '256M' a bytes. */
	private function toBytes(string $v): int {
		$v = trim($v);
		$n = (int) $v;
		switch (strtoupper(substr($v, -1))) {
			case 'G': $n *= 1024;
			// no break
			case 'M': $n *= 1024;
			// no break
			case 'K': $n *= 1024;
		}
		return $n;
	}

	/** Marcas de tiempo enteras vigentes dentro de la ventana. */
	private function validMarks(string $json, int $now, int $window): array {
		$marks = json_decode($json, true);
		return is_array($marks) ? array_values(array_filter($marks, static function ($t) use ($now, $window) {
			return is_int($t) && ($now - $t) < $window;
		})) : [];
	}

	/** Ruta saneada a [A-Za-z0-9_/-] (la dada, o la de REQUEST_URI si está vacía). */
	private function routePath(string $route = ''): string {
		$raw = $route !== '' ? $route : (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
		return (string) preg_replace('/[^A-Za-z0-9_\/\-]/', '', $raw);
	}

	/** Claves de filtro saneadas separadas por coma; las claves de un array asociativo, los valores de una lista. */
	private function filterKeys(array $filters): string {
		return implode(',', array_map(static function ($k) {
			return preg_replace('/[^A-Za-z0-9_ ]/', '', (string) $k);
		}, array_keys($filters)));
	}

	/** Detalle del registro de rechazo (separado para poder probarlo sin escribir en el log real). */
	private function rejectDetail(string $controller, string $route, int $code, array $filterKeys): string {
		$names = array_is_list($filterKeys) ? array_filter($filterKeys, 'is_string') : array_keys($filterKeys);
		return 'controlador=' . preg_replace('/[^A-Za-z0-9_]/', '', $controller)
			. ' ruta=' . $this->routePath($route)
			. ' codigo=' . $code
			. ' filtros=' . $this->filterKeys(array_flip(array_values($names)));
	}

	/** Borra temporales `xlsx*` huérfanos de más de una hora (nunca los `xlsxguard_*` del límite: desvincular un .lock en uso anula la concurrencia). */
	private function cleanOldTemps(): void {
		$files = @glob(rtrim(sys_get_temp_dir(), '/') . '/xlsx*');
		if (!is_array($files)) {
			return;
		}
		foreach ($files as $f) {
			if (strpos(basename($f), 'xlsxguard_') === 0) {
				continue;
			}
			if (is_file($f) && (time() - (int) @filemtime($f)) > 3600) {
				@unlink($f);
			}
		}
	}
}
