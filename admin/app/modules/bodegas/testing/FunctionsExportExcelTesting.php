<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class FunctionsExportExcelTesting
 *
 * Pruebas del helper transversal `FunctionsExportExcel` (`vendors/application/functions/`,
 * ADR-0021). Se aloja en el modulo `bodegas` junto a `FunctionsMachineLearningTesting` (ADR-0008).
 *
 * Alcance: pruebas NO destructivas y sin base de datos: el helper es logica pura. Genera archivos
 * .xlsx temporales (borrados al terminar) y los relee con `IOFactory` solo en la prueba (nunca en el helper).
 * `download()` no se prueba directamente porque emite cabeceras y termina la peticion (`exit`);
 * se prueba `build()`, que es lo que `download()` sirve.
 *
 * Convenciones del sistema de testeos: toda prueba es un metodo publico que termina en "Test"
 * y devuelve [caso => 'OK'|'FALLO: razon'] (docs/architecture/testing/contract.md).
 *
 * @see skills/testing/SKILL.md
 */
class FunctionsExportExcelTesting extends ControllerBase {

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
    private function addCase(array &$results, string $caso, bool $ok, string $razon = ''){
        $results[$caso] = $ok ? 'OK' : 'FALLO: '.$razon;
    }

    /** Construye un libro, lo relee y borra el temporal. Devuelve [Spreadsheet, meta]. */
    private function construirYLeer(array $sheets, array $options = []): array{
        $Export = new FunctionsExportExcel();
        $path   = $Export->build($sheets, $options);
        try {
            $book = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        } finally {
            @unlink($path);
        }
        return [$book, $Export->lastMeta()];
    }

    private function hojaTexto(array $rows, array $extra = []): array{
        return array_merge([
            'title'   => 'Datos',
            'columns' => [['key' => 'a', 'label' => 'A', 'type' => 'text']],
            'rows'    => $rows,
        ], $extra);
    }

    /******************************************************************************/
    /*                                  PRUEBAS                                   */
    /******************************************************************************/
    /**
     * Carga: archivo, clase, constantes con los valores de la spec y metodos publicos.
     */
    public function cargaTest(){
        $results = [];
        $ruta    = dirname(__DIR__, 5).'/vendors/application/functions/FunctionsExportExcel.php';
        $this->addCase($results, 'archivo_existe', is_file($ruta), 'no existe '.$ruta);
        $this->addCase($results, 'clase_existe', class_exists('FunctionsExportExcel'), 'la clase no se autocarga');
        $Export = new FunctionsExportExcel();
        $this->addCase($results, 'carga_true', $Export->loadPhpSpreadsheet() === true, 'loadPhpSpreadsheet debe devolver true');
        $this->addCase($results, 'carga_idempotente', $Export->loadPhpSpreadsheet() === true, 'segunda carga debe devolver true');

        $esperadas = ['MAX_ROWS' => 2500, 'MAX_COLS' => 60, 'MEMORY_LIMIT' => '256M', 'MAX_SECONDS' => 60, 'COL_WIDTH_MIN' => 8, 'COL_WIDTH_MAX' => 60];
        $const = (new ReflectionClass('FunctionsExportExcel'))->getConstants();
        foreach ($esperadas as $nombre => $valor) {
            $this->addCase($results, 'constante_'.$nombre, ($const[$nombre] ?? null) === $valor, $nombre.' debe valer '.var_export($valor, true));
        }
        $this->addCase($results, 'max_rows_igual_n_maxitems', FunctionsExportExcel::MAX_ROWS === ConfigAPP::APP['N_MaxItems'], 'MAX_ROWS debe coincidir con N_MaxItems');

        $fuente = is_file($ruta) ? (string) file_get_contents($ruta) : '';
        $this->addCase($results, 'sin_setCellValue_texto', !preg_match('/->setCellValue\(/', $fuente), 'solo se permite setCellValueExplicit');
        $this->addCase($results, 'sin_lectores', stripos($fuente, 'IOFactory') === false && stripos($fuente, 'Reader\\') === false, 'el helper no puede leer archivos');
        $this->addCase($results, 'sin_gd_requerida', !in_array('gd', $Export->missingExtensions(), true), 'gd no es requerida');
        return $results;
    }

    /**
     * missingExtensions: devuelve una lista y [] cuando el entorno esta completo.
     */
    public function extensionesTest(){
        $results = [];
        $Export  = new FunctionsExportExcel();
        $faltan  = $Export->missingExtensions();
        $this->addCase($results, 'es_lista', is_array($faltan), 'debe devolver un array');
        $this->addCase($results, 'entorno_completo', $faltan === [], 'faltan extensiones: '.implode(',', $faltan));
        return $results;
    }

    /**
     * sanitizeFileName: solo [A-Za-z0-9_-], <= 60 y respaldo 'exportacion'.
     */
    public function nombreSaneadoTest(){
        $results = [];
        $Export  = new FunctionsExportExcel();
        $n = $Export->sanitizeFileName('../x y.php');
        $this->addCase($results, 'nombre_saneado', $n === 'x_y_php', 'se obtuvo '.$n);
        $this->addCase($results, 'sin_separadores', !preg_match('#[/\\\\.\s]#', $Export->sanitizeFileName('a/b\\c.d e')), 'quedan separadores, puntos o espacios');
        $this->addCase($results, 'vacio_respaldo', $Export->sanitizeFileName('') === 'exportacion', 'vacio debe dar exportacion');
        $this->addCase($results, 'solo_simbolos_respaldo', $Export->sanitizeFileName('../..//') === 'exportacion', 'solo simbolos debe dar exportacion');
        $this->addCase($results, 'largo_60', strlen($Export->sanitizeFileName(str_repeat('a', 200))) <= 60, 'debe ser <= 60');
        $this->addCase($results, 'conserva_validos', $Export->sanitizeFileName('reservas_servicios-2026') === 'reservas_servicios-2026', 'no debe alterar un nombre valido');
        return $results;
    }

    /**
     * sanitizeText: quotePrefix en = + - @ tab y retorno; control chars; recorte.
     */
    public function formulasTest(){
        $results = [];
        $Export  = new FunctionsExportExcel();
        $casos = [
            'formula_igual' => '=1+1', 'formula_mas' => '+SUM(A1)', 'formula_menos' => '-2+3',
            'formula_arroba' => '@SUM(1)', 'formula_tab' => "\t=1", 'formula_cr' => "\r=1",
            'formula_hyperlink' => '=HYPERLINK("http://x","clic")',
        ];
        foreach ($casos as $caso => $valor) {
            [$t, $q] = $Export->sanitizeText($valor);
            $this->addCase($results, $caso.'_quote', $q === true, 'debe marcar quotePrefix');
        }
        [$t, $q] = $Export->sanitizeText('Ana');
        $this->addCase($results, 'texto_normal_sin_quote', $q === false && $t === 'Ana', 'Ana no debe llevar prefijo');
        [$t] = $Export->sanitizeText("a\x00b\x08c\x1Fd");
        $this->addCase($results, 'control_chars_fuera', $t === 'abcd', 'se obtuvo '.bin2hex($t));
        [$t] = $Export->sanitizeText("a\tb\nc");
        $this->addCase($results, 'tab_y_salto_internos_se_conservan', $t === "a\tb\nc", 'tab/salto internos deben conservarse');

        // En el libro: el texto queda como cadena (no formula) y con quotePrefix
        [$book] = $this->construirYLeer([$this->hojaTexto([['a' => '=1+1'], ['a' => '+SUM(A1)'], ['a' => '-2+3'], ['a' => '@SUM(1)'], ['a' => "\t=1"], ['a' => '=HYPERLINK("http://x","clic")']])]);
        $ws = $book->getSheet(0);
        $this->addCase($results, 'libro_sin_formulas', !in_array(true, array_map(function ($r) use ($ws) { return $ws->getCell('A'.$r)->isFormula(); }, range(2, 7)), true), 'una celda quedo como formula');
        $this->addCase($results, 'libro_valor_literal', $ws->getCell('A2')->getValue() === '=1+1' && $ws->getCell('A7')->getValue() === '=HYPERLINK("http://x","clic")', 'el texto debe conservarse literal');
        $this->addCase($results, 'libro_quote_prefix', $ws->getStyle('A2')->getQuotePrefix() === true && $ws->getStyle('A5')->getQuotePrefix() === true, 'falta quotePrefix');
        $this->addCase($results, 'libro_sin_hyperlink', $ws->getCell('A7')->getHyperlink()->getUrl() === '', 'no debe crear hipervinculo');
        return $results;
    }

    /**
     * Celda > 32.767 caracteres: se recorta.
     */
    public function celdaLargaTest(){
        $results = [];
        $Export  = new FunctionsExportExcel();
        [$t] = $Export->sanitizeText(str_repeat('á', 40000));
        $this->addCase($results, 'celda_larga_recortada', mb_strlen($t, 'UTF-8') === 32767, 'longitud '.mb_strlen($t, 'UTF-8'));
        [$book] = $this->construirYLeer([$this->hojaTexto([['a' => str_repeat('x', 40000)]])]);
        $this->addCase($results, 'celda_larga_en_libro', strlen((string) $book->getSheet(0)->getCell('A2')->getValue()) === 32767, 'la celda del libro debe medir 32767');
        return $results;
    }

    /**
     * Tipos: texto, entero, decimal, importe, porcentaje, fecha y fecha-hora se relee con el tipo correcto.
     */
    public function tiposTest(){
        $results = [];
        $sheet = [
            'title'   => 'Tipos',
            'columns' => [
                ['key' => 'T', 'label' => 'Texto',  'type' => 'text'],
                ['key' => 'I', 'label' => 'Entero', 'type' => 'int'],
                ['key' => 'D', 'label' => 'Decimal', 'type' => 'decimal', 'decimals' => 2],
                ['key' => 'M', 'label' => 'Importe', 'type' => 'money', 'decimals' => 0],
                ['key' => 'P', 'label' => 'Porcentaje', 'type' => 'percent'],
                ['key' => 'F', 'label' => 'Fecha', 'type' => 'date'],
                ['key' => 'H', 'label' => 'Fecha hora', 'type' => 'datetime'],
            ],
            'rows' => [
                ['T' => 'Ana', 'I' => '12', 'D' => '3.5', 'M' => 12000, 'P' => 0.42, 'F' => '2026-11-10', 'H' => '2026-11-10 14:30:00'],
                ['T' => 'Luis', 'I' => 'abc', 'D' => null, 'M' => '', 'P' => 'x', 'F' => '0000-00-00', 'H' => null],
            ],
        ];
        [$book] = $this->construirYLeer([$sheet]);
        $ws = $book->getSheet(0);
        $this->addCase($results, 'cabecera', $ws->getCell('A1')->getValue() === 'Texto' && $ws->getCell('G1')->getValue() === 'Fecha hora', 'cabecera incorrecta');
        $this->addCase($results, 'texto', $ws->getCell('A2')->getValue() === 'Ana', 'texto incorrecto');
        $this->addCase($results, 'entero_numerico', $ws->getCell('B2')->getValue() == 12 && !is_string($ws->getCell('B2')->getValue()), 'entero debe ser numerico');
        $this->addCase($results, 'decimal_numerico', abs((float) $ws->getCell('C2')->getValue() - 3.5) < 0.0001 && $ws->getStyle('C2')->getNumberFormat()->getFormatCode() === '0.00', 'decimal/formato incorrecto');
        $this->addCase($results, 'importe_numerico', $ws->getCell('D2')->getValue() == 12000 && $ws->getStyle('D2')->getNumberFormat()->getFormatCode() === '#,##0', 'importe/formato incorrecto');
        $this->addCase($results, 'porcentaje', abs((float) $ws->getCell('E2')->getValue() - 0.42) < 0.0001 && strpos($ws->getStyle('E2')->getNumberFormat()->getFormatCode(), '%') !== false, 'porcentaje incorrecto');
        $this->addCase($results, 'fecha_serial', \PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($ws->getCell('F2')) && $ws->getCell('F2')->getFormattedValue() === '10-11-2026', 'fecha incorrecta: '.$ws->getCell('F2')->getFormattedValue());
        $this->addCase($results, 'fecha_hora', $ws->getCell('G2')->getFormattedValue() === '10-11-2026 14:30', 'fecha hora incorrecta: '.$ws->getCell('G2')->getFormattedValue());
        $this->addCase($results, 'no_numerico_vacio', $ws->getCell('B3')->getValue() === null && $ws->getCell('E3')->getValue() === null, 'un no numerico en columna numerica debe quedar vacio');
        $this->addCase($results, 'nulos_y_cero_fecha_vacios', $ws->getCell('F3')->getValue() === null && $ws->getCell('G3')->getValue() === null && $ws->getCell('C3')->getValue() === null, 'null y 0000-00-00 deben quedar vacios');
        $this->addCase($results, 'cabecera_congelada', $ws->getFreezePane() === 'A2', 'cabecera no congelada: '.var_export($ws->getFreezePane(), true));
        $this->addCase($results, 'autofiltro', $ws->getAutoFilter()->getRange() === 'A1:G3', 'autofiltro: '.$ws->getAutoFilter()->getRange());
        return $results;
    }

    /**
     * Grupos y fila de total.
     */
    public function gruposYTotalTest(){
        $results = [];
        $sheet = [
            'title'   => 'Resumen',
            'columns' => [['key' => 'N', 'label' => 'Nombre', 'type' => 'text'], ['key' => 'C', 'label' => 'Costo', 'type' => 'money'], ['key' => 'B', 'label' => 'Beneficio', 'type' => 'money']],
            'rows'    => [['N' => 'a', 'C' => 10, 'B' => 5], ['N' => 'b', 'C' => 20, 'B' => 7]],
            'groups'  => [['label' => 'Campaña', 'from' => 0, 'to' => 0], ['label' => 'Importes', 'from' => 1, 'to' => 2]],
            'totals'  => ['label' => 'Total General', 'labelCols' => 1, 'sum' => ['C', 'B']],
        ];
        [$book] = $this->construirYLeer([$sheet]);
        $ws = $book->getSheet(0);
        $this->addCase($results, 'fila_grupos', $ws->getCell('A1')->getValue() === 'Campaña' && $ws->getCell('B1')->getValue() === 'Importes', 'grupos incorrectos');
        $this->addCase($results, 'cabecera_tras_grupos', $ws->getCell('B2')->getValue() === 'Costo', 'cabecera debe estar en la fila 2');
        $this->addCase($results, 'fila_total', $ws->getCell('A5')->getValue() === 'Total General' && $ws->getCell('B5')->getValue() == 30 && $ws->getCell('C5')->getValue() == 12, 'total incorrecto');
        $this->addCase($results, 'cabecera_congelada', $ws->getFreezePane() === 'A3', 'debe congelar bajo la cabecera: '.var_export($ws->getFreezePane(), true));
        return $results;
    }

    /**
     * Cero filas: libro valido con cabecera y sin autofiltro de datos.
     */
    public function ceroFilasTest(){
        $results = [];
        [$book, $meta] = $this->construirYLeer([$this->hojaTexto([])]);
        $ws = $book->getSheet(0);
        $this->addCase($results, 'cabecera_presente', $ws->getCell('A1')->getValue() === 'A', 'falta la cabecera');
        $this->addCase($results, 'sin_filas', $ws->getCell('A2')->getValue() === null, 'no debe haber datos');
        $this->addCase($results, 'meta_cero', $meta['rows'] === 0 && $meta['truncated'] === false, 'meta incorrecta');
        return $results;
    }

    /**
     * Tope de filas: 5000 filas simuladas se recortan a MAX_ROWS y se marca truncado.
     */
    public function topeTest(){
        $results = [];
        $rows = [];
        for ($i = 1; $i <= 5000; $i++) { $rows[] = ['a' => 'fila '.$i]; }
        [$book, $meta] = $this->construirYLeer([$this->hojaTexto($rows)], ['limit' => FunctionsExportExcel::MAX_ROWS]);
        $ws = $book->getSheet(0);
        $this->addCase($results, 'max_rows_acotado', FunctionsExportExcel::maxRows() >= FunctionsExportExcel::MAX_ROWS && FunctionsExportExcel::maxRows() <= FunctionsExportExcel::HARD_MAX_ROWS, 'maxRows() fuera de rango');
        $this->addCase($results, 'recorta_a_2500', $ws->getHighestRow() === FunctionsExportExcel::MAX_ROWS + 1, 'filas: '.$ws->getHighestRow());
        $this->addCase($results, 'meta_truncado', $meta['truncated'] === true && $meta['rows'] === FunctionsExportExcel::MAX_ROWS, 'meta: '.json_encode($meta));
        [$book, $meta] = $this->construirYLeer([$this->hojaTexto([['a' => 'x']])], ['truncated' => true, 'limit' => 100]);
        $this->addCase($results, 'truncado_declarado', $meta['truncated'] === true, 'la opcion truncated debe propagarse');
        [, $meta] = $this->construirYLeer([$this->hojaTexto(array_fill(0, 150, ['a' => 'x']))], ['limit' => 100]);
        $this->addCase($results, 'limite_propio', $meta['rows'] === 100 && $meta['truncated'] === true, 'limit propio no aplicado');
        // Columnas: tope MAX_COLS
        $cols = []; $row = [];
        for ($i = 0; $i < 80; $i++) { $cols[] = ['key' => 'c'.$i, 'label' => 'C'.$i, 'type' => 'int']; $row['c'.$i] = $i; }
        [$book] = $this->construirYLeer([['title' => 'Anchas', 'columns' => $cols, 'rows' => [$row]]]);
        $this->addCase($results, 'tope_columnas', $book->getSheet(0)->getHighestColumn() === 'BH', 'ultima columna: '.$book->getSheet(0)->getHighestColumn());
        return $results;
    }

    /**
     * Hoja Filtros: etiquetas, usuario, filas y aviso de truncado.
     */
    public function hojaFiltrosTest(){
        $results = [];
        [$book] = $this->construirYLeer([$this->hojaTexto([['a' => 'x']])], ['filters' => ['Desde' => '01-11-2026', 'Estado' => '=cmd'], 'user' => 'Admin Prueba', 'truncated' => true, 'limit' => 2500, 'info' => ['Modelo' => 'Regresion']]);
        $ws = $book->getSheetByName('Filtros');
        $this->addCase($results, 'hoja_existe', $ws !== null, 'falta la hoja Filtros');
        $texto = '';
        if ($ws !== null) {
            foreach ($ws->toArray() as $fila) { $texto .= implode('|', array_map('strval', $fila))."\n"; }
        }
        $this->addCase($results, 'etiquetas_filtros', strpos($texto, 'Desde|01-11-2026') !== false && strpos($texto, 'Estado|=cmd') !== false, 'faltan filtros');
        $this->addCase($results, 'usuario', strpos($texto, 'Usuario|Admin Prueba') !== false, 'falta el usuario');
        $this->addCase($results, 'aviso_truncado', strpos($texto, 'truncado') !== false, 'falta el aviso de truncado');
        $this->addCase($results, 'info_predictiva', strpos($texto, 'Modelo|Regresion') !== false, 'falta la info');
        $this->addCase($results, 'filtro_formula_no_es_formula', $ws !== null && $ws->getCell('B7')->isFormula() === false, 'un filtro con = no puede ser formula');
        $this->addCase($results, 'datos_primera_hoja', $book->getSheetNames()[0] === 'Datos', 'la hoja activa debe ser Datos');
        [$book] = $this->construirYLeer([$this->hojaTexto([])], ['info' => false]);
        $this->addCase($results, 'sin_hoja_si_info_false', $book->getSheetByName('Filtros') === null, 'info=false no debe crear Filtros');
        [$book] = $this->construirYLeer([$this->hojaTexto([])]);
        $this->addCase($results, 'por_defecto_con_hoja', $book->getSheetByName('Filtros') !== null, 'por defecto debe existir Filtros');
        return $results;
    }

    /**
     * Relectura: el archivo es un zip OOXML valido; titulos saneados y unicos; varias hojas.
     */
    public function relecturaTest(){
        $results = [];
        $Export = new FunctionsExportExcel();
        $path = $Export->build([
            $this->hojaTexto([['a' => 'x']], ['title' => 'Reservas/2026: [A]*?']),
            $this->hojaTexto([['a' => 'y']], ['title' => 'Reservas2026 A']),
            $this->hojaTexto([['a' => 'z']], ['title' => str_repeat('T', 50)]),
        ], ['filters' => ['k' => 'v']]);
        try {
            $this->addCase($results, 'archivo_creado', is_file($path) && filesize($path) > 0, 'no se creo el archivo');
            $zip = new ZipArchive();
            $ok  = $zip->open($path) === true;
            $this->addCase($results, 'zip_abre', $ok, 'ZipArchive no abre el archivo');
            $this->addCase($results, 'contiene_workbook', $ok && $zip->locateName('xl/workbook.xml') !== false && $zip->locateName('[Content_Types].xml') !== false, 'no es un OOXML valido');
            $this->addCase($results, 'sin_macros_ni_imagenes', $ok && $zip->locateName('xl/vbaProject.bin') === false && $zip->locateName('xl/media/') === false, 'no debe haber macros ni imagenes');
            if ($ok) { $zip->close(); }
            $book = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            $nombres = $book->getSheetNames();
            $this->addCase($results, 'cuatro_hojas', count($nombres) === 4, 'hojas: '.implode(',', $nombres));
            $this->addCase($results, 'titulos_validos', !preg_match('#[\[\]:*?/\\\\]#', implode('', $nombres)) && max(array_map('strlen', $nombres)) <= 31, 'titulos: '.implode(',', $nombres));
            $this->addCase($results, 'titulos_unicos', count(array_unique(array_map('strtolower', $nombres))) === count($nombres), 'hay titulos repetidos');
            $this->addCase($results, 'relee_valor', $book->getSheet(1)->getCell('A2')->getValue() === 'y', 'valor de la 2a hoja incorrecto');
        } finally {
            @unlink($path);
        }
        return $results;
    }

    /**
     * Limite de la exportacion (Q12): una simultanea por usuario y maximo por ventana; usuarios independientes.
     */
    public function limiteExportacionTest(){
        $results = [];
        $uid  = 'TESTING-'.bin2hex(random_bytes(6));
        $uid2 = $uid.'-otro';
        $limpiar = static function (string $u) {
            $base = rtrim(sys_get_temp_dir(), '/').'/xlsxguard_'.substr(hash('sha256', $u), 0, 24);
            @unlink($base.'.lock');
            @unlink($base.'.json');
        };
        try {
            $A = new FunctionsExportExcel();
            $this->addCase($results, 'acquire_primera_ok', $A->acquire($uid) === '', 'la primera exportacion debe permitirse');
            $B = new FunctionsExportExcel();
            $this->addCase($results, 'acquire_simultanea_bloqueada', stripos($B->acquire($uid), 'en curso') !== false, 'una segunda exportacion simultanea del mismo usuario debe rechazarse');
            $C = new FunctionsExportExcel();
            $this->addCase($results, 'acquire_otro_usuario_libre', $C->acquire($uid2) === '', 'otro usuario no debe verse afectado');
            $C->release();
            $A->release();
            $D = new FunctionsExportExcel();
            $this->addCase($results, 'acquire_tras_release_ok', $D->acquire($uid) === '', 'liberado el bloqueo debe permitirse otra');
            $D->release();

            // Rechazos antes de commit() no gastan cuota: 6 intentos con release() sin confirmar siguen permitidos
            $uid4 = $uid.'-sincommit';
            $sin = [];
            for ($i = 0; $i < 6; $i++) {
                $X = new FunctionsExportExcel();
                $sin[] = $X->acquire($uid4, 3, 60);
                $X->release();
            }
            $this->addCase($results, 'acquire_sin_commit_no_gasta_cuota', $sin === array_fill(0, 6, ''), 'acquire+release sin commit (400/500) no debe consumir la ventana');
            $X = new FunctionsExportExcel();
            $X->acquire($uid4, 3, 60);
            $X->release();
            $X->commit();
            $Y = new FunctionsExportExcel();
            $this->addCase($results, 'commit_tras_release_no_cuenta', $Y->acquire($uid4, 3, 60) === '', 'commit() tras release() no debe apuntar marca');
            $Y->release();
            $limpiar($uid4);

            // Ventana: maximo 3 confirmadas en 60 s; la 4a se rechaza y libera el bloqueo
            $uid3 = $uid.'-ventana';
            $res = [];
            for ($i = 0; $i < 5; $i++) {
                $X = new FunctionsExportExcel();
                $res[] = $X->acquire($uid3, 3, 60);
                if ($res[$i] === '') {
                    $X->commit();
                    $X->commit(); // idempotente: no cuenta doble
                }
                $X->release();
            }
            $this->addCase($results, 'acquire_ventana_permite_hasta_max', $res[0] === '' && $res[1] === '' && $res[2] === '', 'las 3 primeras deben permitirse');
            $this->addCase($results, 'acquire_ventana_rechaza_exceso', stripos($res[3], 'demasiadas') !== false && stripos($res[4], 'demasiadas') !== false, 'a partir de la 4a debe rechazarse (429)');
            $json = json_decode((string) @file_get_contents(rtrim(sys_get_temp_dir(), '/').'/xlsxguard_'.substr(hash('sha256', $uid3), 0, 24).'.json'), true);
            $this->addCase($results, 'commit_idempotente', is_array($json) && count($json) === 3, 'commit() repetido no debe contar dos veces (3 marcas esperadas)');
            $Z = new FunctionsExportExcel();
            $this->addCase($results, 'acquire_429_libera_bloqueo', stripos($Z->acquire($uid3, 3, 60), 'demasiadas') !== false && (new FunctionsExportExcel())->acquire($uid3.'-nolock') === '', 'el 429 no debe dejar bloqueo tomado ni afectar a otros');

            // H-01: un .lock antiguo (>1 h) no se borra en la limpieza y la concurrencia sigue activa
            $uid5 = $uid.'-h01';
            $base5 = rtrim(sys_get_temp_dir(), '/').'/xlsxguard_'.substr(hash('sha256', $uid5), 0, 24);
            $P = new FunctionsExportExcel();
            $this->addCase($results, 'h01_acquire_previo', $P->acquire($uid5) === '', 'primer acquire permitido');
            @touch($base5.'.lock', time() - 7200);
            @touch($base5.'.json', time() - 7200);
            $rm = new ReflectionMethod('FunctionsExportExcel', 'cleanOldTemps');
            $rm->invoke(new FunctionsExportExcel());
            $this->addCase($results, 'h01_cleanOldTemps_no_borra_lock', is_file($base5.'.lock') && is_file($base5.'.json'), 'cleanOldTemps no debe borrar xlsxguard_* antiguos');
            $Q = new FunctionsExportExcel();
            $this->addCase($results, 'h01_segundo_acquire_429_tras_limpieza', stripos($Q->acquire($uid5), 'en curso') !== false, 'tras la limpieza la concurrencia debe seguir bloqueando');
            $P->release();
            $limpiar($uid5);
            $limpiar($uid3);
        } finally {
            $limpiar($uid);
            $limpiar($uid2);
        }
        return $results;
    }

    /**
     * Auditoria (Q11): `audit()` registra solo metadatos con AuditLogger y `download()` la pide con las filas reales.
     * Estatica: no se escribe en el log de auditoria real desde la suite.
     */
    public function auditoriaExportacionTest(){
        $results = [];
        $src = (string) file_get_contents(dirname(__DIR__, 5).'/vendors/application/functions/FunctionsExportExcel.php');
        $this->addCase($results, 'audit_metodo_publico', method_exists('FunctionsExportExcel', 'audit') && (new ReflectionMethod('FunctionsExportExcel', 'audit'))->isPublic(), 'audit() debe ser publico');
        $this->addCase($results, 'audit_usa_AuditLogger', str_contains($src, "AuditLogger::log(\n\t\t\t'export_excel'"), 'audit() debe registrar el evento export_excel con AuditLogger');
        $this->addCase($results, 'audit_solo_claves_de_filtros', str_contains($src, 'array_keys($filters)') && !str_contains($src, 'implode(\',\', $filters)'), 'solo deben registrarse las claves de los filtros, nunca sus valores');
        $this->addCase($results, 'audit_filas_y_truncado', str_contains($src, "' filas=' . (int) \$meta['rows']") && str_contains($src, "' truncado='"), 'debe registrar el numero de filas y el truncado');
        $this->addCase($results, 'download_llama_commit', str_contains($src, '$this->commit();') && strpos($src, '$this->commit();') < strpos($src, '$this->build($sheets'), 'download() debe confirmar el limite (si el controlador no lo hizo) antes de generar');
        $this->addCase($results, 'auditReject_publico', method_exists('FunctionsExportExcel', 'auditReject') && (new ReflectionMethod('FunctionsExportExcel', 'auditReject'))->isPublic(), 'auditReject() debe ser publico');
        $E = new FunctionsExportExcel();
        $rd = new ReflectionMethod('FunctionsExportExcel', 'rejectDetail');
        $valor = 'SECRETO-12345';
        $det = $rd->invoke($E, 'ctrl', '../a/b?x=1<script>', 400, ['fecha' => $valor, 'idCliente' => $valor]);
        $this->addCase($results, 'auditReject_no_guarda_valores', !str_contains($det, $valor) && str_contains($det, 'filtros=fecha,idCliente') && str_contains($det, 'codigo=400'), 'solo claves de filtro y codigo: '.$det);
        $det2 = $rd->invoke($E, 'ctrl', '/a/b', 429, ['fecha', 'idCliente']);
        $this->addCase($results, 'auditReject_lista_de_claves', str_contains($det2, 'filtros=fecha,idCliente') && str_contains($det2, 'ruta=/a/b'), $det2);
        $this->addCase($results, 'auditReject_ruta_saneada', (bool) preg_match('~ruta=[A-Za-z0-9_/-]* codigo~', $det), 'la ruta debe quedar en [A-Za-z0-9_/-]: '.$det);
        $this->addCase($results, 'auditReject_evento', str_contains($src, "AuditLogger::log('export_excel_rechazo'"), 'debe registrar el evento export_excel_rechazo');
        $this->addCase($results, 'download_audita_antes_de_emitir', strpos($src, "\$options['audit']") !== false && strpos($src, "\$options['audit']") < strpos($src, "header('Content-Type: application/vnd.openxmlformats"), 'download() debe auditar antes de emitir el archivo');
        return $results;
    }
}
