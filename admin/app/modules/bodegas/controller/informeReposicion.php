<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class informeReposicion
 *
 * Informe predictivo de reposicion: predice la demanda semanal de cada producto con una
 * tendencia lineal (php-ml `LeastSquares`, via `FunctionsMachineLearning::linearTrend`) y la
 * compara con el stock actual y el `StockLimite`. Tipo Informe con la excepcion de ADR-0020:
 * el metodo `Recalcular` (POST, nivel >= 2) entrena y persiste el resultado en
 * `bodegas_pred_*`; `listAll` y `UpdateList` solo leen la ultima ejecucion correcta.
 *
 * Demanda (confirmada en dev el 2026-11-13, TASK-001.2): egreso real `idEstadoIngreso = 2`; los traspasos
 * (`idEstadoIngreso = 3`) quedan excluidos por esa condicion (en dev no existen movimientos con ambas bodegas).
 *
 * @see projects/informes-predictivos-phpml/architecture/recipe-informe-predictivo.md
 */
class informeReposicion extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataNumbers;
    private $DataDate;
    private $WidgetsCommon;
    private $MachineLearning;
    /*=========== Constantes del informe ===========*/
    private const INFORME        = 'reposicion';
    private const TABLA_EJEC     = 'bodegas_pred_ejecuciones';
    private const TABLA_RES      = 'bodegas_pred_reposicion';
    private const SEMANAS_MIN    = 4;
    private const SEMANAS_MAX    = 26;
    private const SEMANAS_DEF    = 12;
    private const MIN_CON_DATOS  = 4;
    private const TOP_PRODUCTOS  = 1500;

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName  = 'informeReposicion';
        $this->FormInputs      = new UIFormInputs();
        $this->Codification    = new FunctionsSecurityCodification();
        $this->DataNumbers     = new FunctionsDataNumbers();
        $this->DataDate        = new FunctionsDataDate();
        $this->WidgetsCommon   = new UIWidgetsCommon();
        $this->MachineLearning = new FunctionsMachineLearning();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /*******************************************************************/
    // Listar (solo lookups y ultima ejecucion; no consulta resultados)
    /*******************************************************************/
    public function listAll($f3){

        /************************************/
        // Lookups y ultima ejecucion
        $arrProductos = $this->getProductos();
        $arrRun       = $this->getLastRun();

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrProductos['status'] === true && $arrRun['status'] === true) {

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Reposición Predictiva',
                'PageDescription' => 'Reposición Predictiva.',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => 'Reposición Predictiva',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'   => $this->FormInputs,
                'Fnc_Codification' => $this->Codification,
                'Fnc_DataDate'     => $this->DataDate,
                /*=========== Datos Consultados ===========*/
                'arrProductos'   => $arrProductos['data'],
                'arrRun'         => $arrRun['data'][0] ?? [],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrProductos, $arrRun]);
            // Despliegue de errores
            $this->showError(1, $f3, $result);
        }

    }

    /*******************************************************************/
    // Actualizar Listar (ultimo resultado guardado, filtrado)
    /*******************************************************************/
    public function UpdateList($f3){

        /************************************/
        // Filtro compartido con exportExcel
        $filtro      = $this->armarFiltro();
        if (isset($filtro['error'])) {
            Response::error($filtro['error'], 400);
        }
        $whereInt    = $filtro['where'];
        $whereParams = $filtro['params'];

        /************************************/
        // Carga incremental: offset y tamaño de bloque (N_MaxItems + 1 fila para saber si hay más)
        $page = $this->getPageParams();

        /************************************/
        // Ultima ejecucion correcta y su resultado (sin ejecucion => lista vacia)
        $arrRun  = $this->getLastRun();
        $arrList = ['status' => true, 'data' => []];
        if ($arrRun['status'] === true && !empty($arrRun['data'])) {
            $arrList = $this->getDataList($whereInt, $whereParams, (int)$arrRun['data'][0]['idEjecucion'], $page['limit']);
        }

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrRun['status'] === true && $arrList['status'] === true) {

            /************************************/
            // Se recorta la fila extra y se calcula si quedan más datos
            $more = $this->sliceMore($arrList['data'], $page);

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'TableTitle'      => 'Reposición Predictiva',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification' => $this->Codification,
                'Fnc_DataNumbers'  => $this->DataNumbers,
                'Fnc_DataDate'     => $this->DataDate,
                /*=========== Datos Consultados ===========*/
                'arrRun'          => $arrRun['data'][0] ?? [],
                'arrList'         => $more['rows'],
                'hasMore'         => $more['hasMore'],
                'nextOffset'      => $more['next'],
            ];

            /************************************/
            // Fragmento (solo filas) para la carga incremental, o tabla completa
            if ($page['fragment']) {
                header('X-Has-More: '.($more['hasMore'] ? '1' : '0'));
                header('X-Next-Offset: '.$more['next']);
                $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-UpdateListRows.php');
            } else {
                $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-UpdateList.php');
            }
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrRun, $arrList]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Recalcular (POST, nivel >= 2): entrena y guarda el resultado
    /*******************************************************************/
    public function Recalcular($f3){

        /************************************/
        // Solo POST (el CSRF lo valida beforeroute, ADR-0007)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Se reporta el error
            Response::error('Método no permitido', 405);
        }

        /************************************/
        // Nivel de acceso
        $UserAccess = $this->getArrLevel($f3, $this->controllerName);
        if (($UserAccess['LevelAccess'] ?? 0) < 2) {
            // Se reporta el error
            Response::error('Sin permiso', 403);
        }

        /************************************/
        // Parametros con allow-list de rango (no se corrige en silencio)
        $semanas = self::SEMANAS_DEF;
        if (isset($_POST['semanas']) && is_array($_POST['semanas'])) {
            // Un array (semanas[]=..) no es un escalar valido: 400 antes de convertirlo a texto
            // Se reporta el error
            Response::error('Parámetro inválido', 400);
        }
        if (isset($_POST['semanas']) && $_POST['semanas'] !== '') {
            $raw = trim((string)$_POST['semanas']);
            if (!ctype_digit($raw) || (int)$raw < self::SEMANAS_MIN || (int)$raw > self::SEMANAS_MAX) {
                // Se reporta el error
                Response::error('Parámetro inválido', 400);
            }
            $semanas = (int)$raw;
        }

        /************************************/
        // Libreria y presupuesto
        if ($this->MachineLearning->loadPhpMl() !== true) {
            // Se reporta el error
            Response::error('Error al recalcular', 500);
        }
        $t0 = $this->MachineLearning->beginBudget();

        /************************************/
        // Ejecucion activa (una por informe)
        $arrUserData = $this->getUserData($f3);
        $run = $this->startRun((int)$arrUserData['UserID']);

        /************************************/
        // Validacion de errores
        if ($run['status'] === false) {
            // Se reporta el error
            Response::error($run['message'], $run['code']);
        }
        $idEjecucion = (int)$run['idEjecucion'];

        /************************************/
        // Dataset -> entrenamiento -> guardado
        try {
            $demanda = $this->getDatasetDemanda($semanas);

            /************************************/
            // Validacion de errores
            if ($demanda['status'] === false) {
                // Se reporta el error
                Response::error('dataset demanda', 500);
            }
            $idsDemanda = array_values(array_unique(array_map('intval', array_column($demanda['data'], 'idProducto'))));
            $stock   = $this->getStockProductos($idsDemanda);

            /************************************/
            // Validacion de errores
            if ($stock['status'] === false) {
                // Se reporta el error
                Response::error('dataset stock', 500);
            }
            if (!$this->MachineLearning->checkBudget($t0)) {
                // Se reporta el error
                Response::error('timeout', 500);
            }

            $result = $this->trainReposicion($demanda['data'], $stock['data'], $semanas, null, (int)($demanda['total'] ?? 0));
            if ($result['muestrasOrigen'] > $result['muestras']) {
                // Truncado visible: MuestrasOrigen (productos con demanda) > Muestras (productos usados)
                error_log('informeReposicion::Recalcular: dataset truncado a '.$result['muestras'].' de '.$result['muestrasOrigen'].' productos');
            }
            unset($demanda, $stock);
            if (!$this->MachineLearning->checkBudget($t0)) {
                // Se reporta el error
                Response::error('timeout', 500);
            }

            $meta = [
                'algoritmo'      => 'LeastSquares',
                'muestras'       => $result['muestras'],
                'muestrasOrigen' => $result['muestrasOrigen'],
                'metricas'       => $result['metricas'],
                'modelo'         => $result['modelo'],
                'inicio'         => $t0,
            ];
            $saved = $this->saveRun($idEjecucion, $result['rows'], $meta);

            /************************************/
            // Validacion de errores
            if ($saved['status'] === false) {
                // Se reporta el error
                Response::error('guardado', 500);
            }
        } catch (Throwable $e) {
            $timeout = ($e->getMessage() === 'timeout');
            $this->failRun($idEjecucion, $timeout ? 'Tiempo agotado' : 'Error al recalcular');
            error_log('informeReposicion::Recalcular: '.$e->getMessage());
            // Se reporta el error
            Response::error($timeout ? 'Tiempo agotado' : 'Error al recalcular', 500);
        }

        /************************************/
        // Respuesta
        $DataID = $this->Codification->encryptDecrypt('encrypt', $idEjecucion);
        Response::success('Recalculo completado', [
            'idEjecucion'    => $DataID['data'],
            'muestras'       => $result['muestras'],
            'muestrasOrigen' => $result['muestrasOrigen'],
            'duracionMs'     => (int)round((microtime(true) - $t0) * 1000),
            'metricas'       => $result['metricas'],
        ]);
    }

    /******************************************************************************/
    /*                              EXPORTAR A EXCEL                              */
    /******************************************************************************/
    /*******************************************************************/
    // Exportar a Excel (.xlsx real, ADR-0021). POST + CSRF central (la ruta ya es POST).
    // Orden: verbo -> limite -> filtro -> ultima ejecucion -> consulta -> filas -> download().
    /*******************************************************************/
    public function exportExcel($f3, $params = null){

        /************************************/
        // Defensa en profundidad: solo POST (la ruta ya lo exige)
        $Export  = new FunctionsExportExcel();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $Export->auditReject($this->controllerName, '', 405);
            Response::error('Método no permitido', 405);
        }

        /************************************/
        // Límite específico de la exportación (Q12): 1 simultánea por usuario y 10 por minuto
        $userId  = (string) ($f3->get('SESSION.DataInfo.UserID') ?: ($_SERVER['REMOTE_ADDR'] ?? '0'));
        $limite  = $Export->acquire($userId);
        if ($limite !== '') {
            $Export->auditReject($this->controllerName, '', 429);
            Response::error($limite, 429);
        }

        /************************************/
        // Filtro compartido con UpdateList; si es inválido se libera el bloqueo, se audita (solo claves) y 400
        $filtro  = $this->armarFiltro();
        if (isset($filtro['error'])) {
            $Export->release();
            $Export->auditReject($this->controllerName, '', 400, ['idProducto', 'Alerta']);
            Response::error($filtro['error'], 400);
        }
        // Filtro válido: cuenta para el límite de frecuencia
        $Export->commit();

        /************************************/
        // Misma consulta que la vista: ultima ejecucion correcta (sin ejecucion => hoja vacia)
        $arrRun  = $this->getLastRun();
        if ($arrRun['status'] !== true) {
            $Export->release();
            $Export->auditReject($this->controllerName, '', 500, ['idProducto', 'Alerta']);
            Response::error('No se pudo generar la exportación', 500);
        }
        // Lectura por bloques hasta N_MaxExportRows (FunctionsExportExcel::maxRows(), ADR-0022)
        $maxRows = FunctionsExportExcel::maxRows();
        $arrList = ['status' => true, 'data' => []];
        if (!empty($arrRun['data'])) {
            $arrList = $this->getDataList($filtro['where'], $filtro['params'], (int) $arrRun['data'][0]['idEjecucion'], null, $maxRows);
        }
        if ($arrList['status'] !== true) {
            $Export->release();
            $Export->auditReject($this->controllerName, '', 500, ['idProducto', 'Alerta']);
            Response::error('No se pudo generar la exportación', 500);
        }
        $data = (is_array($arrList['data']) ? $arrList['data'] : []);
        // La consulta trae una fila extra solo para detectar que hay más datos
        $truncated = count($data) > $maxRows;
        if ($truncated) { $data = array_slice($data, 0, $maxRows); }

        /************************************/
        // Columnas = columnas de la vista. type: text|int|decimal|money|percent|date|datetime
        $columns = [
            ['key' => 'Producto',        'label' => 'Producto',          'type' => 'text', 'width' => 36],
            ['key' => 'UniMed',          'label' => 'Unidad',            'type' => 'text'],
            ['key' => 'DemandaMedia',    'label' => 'Demanda media',     'type' => 'decimal', 'decimals' => 2],
            ['key' => 'DemandaPredicha', 'label' => 'Demanda predicha',  'type' => 'decimal', 'decimals' => 2],
            ['key' => 'Pendiente',       'label' => 'Pendiente',         'type' => 'decimal', 'decimals' => 4],
            ['key' => 'StockActual',     'label' => 'Stock actual',      'type' => 'decimal', 'decimals' => 2],
            ['key' => 'StockLimite',     'label' => 'Stock límite',      'type' => 'decimal', 'decimals' => 2],
            ['key' => 'Cobertura',       'label' => 'Cobertura (sem.)',  'type' => 'decimal', 'decimals' => 2],
            ['key' => 'Sugerida',        'label' => 'Sugerida',          'type' => 'decimal', 'decimals' => 2],
            ['key' => 'Alerta',          'label' => 'Alerta',            'type' => 'text'],
        ];

        /************************************/
        // Filas: valores crudos (StockLimite y Cobertura pueden ser null => celda vacia); la Alerta se traduce a su texto
        $alertas = [1 => 'OK', 2 => 'Bajo límite', 3 => 'Reponer'];
        $rows = [];
        foreach ($data as $r) {
            $rows[] = [
                'Producto'        => $r['Producto'] ?? '',
                'UniMed'          => $r['UniMed'] ?? '',
                'DemandaMedia'    => $r['DemandaMedia'] ?? 0,
                'DemandaPredicha' => $r['DemandaPredicha'] ?? 0,
                'Pendiente'       => $r['Pendiente'] ?? 0,
                'StockActual'     => $r['StockActual'] ?? 0,
                'StockLimite'     => $r['StockLimite'] ?? null,
                'Cobertura'       => $r['Cobertura'] ?? null,
                'Sugerida'        => $r['Sugerida'] ?? 0,
                'Alerta'          => $alertas[(int) ($r['Alerta'] ?? 0)] ?? '-',
            ];
        }

        /************************************/
        // Descarga: audita (Q11), emite el archivo y termina. Nombre fijo del servidor.
        $userData = $this->getUserData($f3);
        $Export->download('reposicion_predictiva', [[ 'title' => 'Reposición predictiva', 'columns' => $columns, 'rows' => $rows ]], [
            'filters'   => $this->nombresFiltro($filtro['label']),
            'user'      => (string) ($userData['UserName'] ?? ''),
            'truncated' => $truncated,
            'audit'     => ['controller' => $this->controllerName, 'userId' => $userId],
        ]);
    }

    /******************************************************************************/
    /*                            CONSULTAS INTERNAS                              */
    /******************************************************************************/
    /*******************************************************************/
    // Filtro compartido por UpdateList (tabla) y exportExcel (Excel): una sola fuente de filtros
    // Devuelve ['where' => ..., 'params' => [...], 'label' => [etiqueta => valor]] (sin la ejecucion) o ['error' => mensaje] (el llamador responde 400)
    /*******************************************************************/
    private function armarFiltro(): array{

        /************************************/
        // Variables
        $WhereData_int     = 'idProducto,Alerta';  // Datos búsqueda exacta
        $WhereData_string  = '';                   // Datos búsqueda relativa
        $WhereData_between = '';                   // Datos búsqueda Between
        $whereInt          = '';                   // Se crea cadena
        $whereParams       = [];                   // Valores bindeados asociados a $whereInt
        $label             = [];                   // Filtros aplicados (hoja "Filtros" del Excel)
        /************************************/
        // H-03: un valor en forma de array (campo[]=x) es un filtro inválido (antes de cualquier (string))
        foreach (['idProducto', 'Alerta'] as $campo) {
            if (isset($_POST[$campo]) && is_array($_POST[$campo])) {
                return ['error' => 'Filtro inválido'];
            }
        }

        /************************************/
        // Agrego variable busqueda
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_int, self::TABLA_RES, 1);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_string, self::TABLA_RES, 2);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_between, self::TABLA_RES, 3);
        $whereInt = $r['where']; $whereParams = $r['params'];
        // Etiquetas de los filtros aplicados (mismas condiciones que usa searchWhere: valor presente y no vacío)
        foreach (['idProducto' => 'Producto', 'Alerta' => 'Alerta'] as $campo => $etiqueta) {
            if (isset($_POST[$campo]) && $_POST[$campo] !== '') { $label[$etiqueta] = (string) $_POST[$campo]; }
        }
        return ['where' => $whereInt, 'params' => $whereParams, 'label' => $label];
    }

    /*******************************************************************/
    // Filtros para la hoja "Filtros": sustituye ids por nombres legibles
    /*******************************************************************/
    private function nombresFiltro(array $label): array{

        if (isset($label['Alerta'])) {
            $alertas = ['1' => 'OK', '2' => 'Bajo límite', '3' => 'Reponer'];
            $label['Alerta'] = $alertas[$label['Alerta']] ?? $label['Alerta'];
        }
        if (isset($label['Producto'])) {
            $arr = $this->getProductos();
            if ($arr['status'] === true && is_array($arr['data'])) {
                foreach ($arr['data'] as $fila) {
                    if ((string) $fila['ID'] === (string) $label['Producto']) {
                        $label['Producto'] = (string) $fila['Nombre'];
                        break;
                    }
                }
            }
        }
        return $label;
    }

    /*******************************************************************/
    // Ultima ejecucion correcta (Estado = 2)
    /*******************************************************************/
    private function getLastRun(){
        $query = [
            'data'   => 'idEjecucion, idUsuario, Estado, Algoritmo, FechaInicio, FechaFin, DuracionMs, MuestrasOrigen, Muestras, Metricas, Modelo',
            'table'  => self::TABLA_EJEC,
            'join'   => '',
            'where'  => 'Informe = ? AND Estado = ?',
            'params' => [self::INFORME, 2],
            'group'  => '',
            'having' => '',
            'order'  => 'idEjecucion DESC',
            'limit'  => 1
        ];
        // Preparo los datos
        $xParams   = ['query' => $query];
        // Ejecuto la query
        return $this->Base_GetList($xParams);
    }

    /*******************************************************************/
    // Resultado de una ejecucion (solo tablas *_pred_* y lookups)
    /*******************************************************************/
    private function getDataList($filter, $params, $idEjecucion, $limit = null, ?int $maxRows = null){
        /************************************/
        // Se agrega la ejecucion al filtro
        $where  = 'bodegas_pred_reposicion.idEjecucion = ?';
        $values = [(int)$idEjecucion];
        if ($filter != '') {
            $where .= ' AND '.$filter;
            $values = array_merge($values, $params);
        }
        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                bodegas_pred_reposicion.idProducto,
                bodegas_pred_reposicion.SemanasUsadas,
                bodegas_pred_reposicion.DemandaMedia,
                bodegas_pred_reposicion.DemandaPredicha,
                bodegas_pred_reposicion.Pendiente,
                bodegas_pred_reposicion.StockActual,
                bodegas_pred_reposicion.StockLimite,
                bodegas_pred_reposicion.Cobertura,
                bodegas_pred_reposicion.Sugerida,
                bodegas_pred_reposicion.Alerta,
                productos_listado.Nombre AS Producto,
                core_unidades_medida.Nombre AS UniMed',
            'table'   => 'bodegas_pred_reposicion',
            'join'    => '
                LEFT JOIN productos_listado    ON productos_listado.idProducto  = bodegas_pred_reposicion.idProducto
                LEFT JOIN core_unidades_medida ON core_unidades_medida.idUniMed = productos_listado.idUniMed',
            'where'   => $where,
            'params'  => $values,
            'group'   => '',
            'having'  => '',
            'order'   => 'bodegas_pred_reposicion.Alerta DESC, bodegas_pred_reposicion.Sugerida DESC, bodegas_pred_reposicion.idPrediccion ASC',
            'limit'   => $limit ?? ConfigAPP::APP["N_MaxItems"]
        ];
        // Excel: lectura por bloques hasta el tope de exportación (la vista usa un bloque con offset)
        if ($maxRows !== null) {
            return $this->Base_GetListChunked($query, $maxRows);
        }
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        return $this->Base_GetList($xParams);
    }

    /*******************************************************************/
    // Lookup de productos activos para el filtro
    /*******************************************************************/
    private function getProductos(){
        $query = [
            'data'    => 'idProducto AS ID, Nombre',
            'table'   => 'productos_listado',
            'join'    => '',
            'where'   => 'idEstado = ?',
            'params'  => [1],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams   = ['query' => $query];
        // Ejecuto la query
        return $this->Base_GetList($xParams);
    }

    /******************************************************************************/
    /*                              ENTRENAMIENTO                                 */
    /******************************************************************************/
    /*******************************************************************/
    // Demanda semanal agregada por producto (nunca lineas crudas)
    /*******************************************************************/
    private function getDatasetDemanda($semanas){
        /************************************/
        // Ventana: semanas completas anteriores a la semana en curso
        $lunes = 'DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)';
        // Top de productos acotado para que productos x semanas nunca supere MAX_SAMPLES_HARD:
        // el LIMIT final no descarta semanas de un producto (A-1)
        $top   = max(1, min(self::TOP_PRODUCTOS, intdiv(FunctionsMachineLearning::MAX_SAMPLES_HARD, max(1, (int)$semanas))));
        $query = [
            'data'    => 'bodegas_movimientos_productos.idProducto, YEARWEEK(bodegas_movimientos.Creacion_fecha, 3) AS Semana, SUM(bodegas_movimientos_productos.Number) AS Demanda',
            'table'   => 'bodegas_movimientos_productos',
            'join'    => '
                INNER JOIN bodegas_movimientos ON bodegas_movimientos.idMovimiento = bodegas_movimientos_productos.idMovimiento
                INNER JOIN (
                    SELECT mp2.idProducto
                    FROM bodegas_movimientos_productos mp2
                    INNER JOIN bodegas_movimientos m2 ON m2.idMovimiento = mp2.idMovimiento
                    WHERE mp2.idEstadoIngreso = 2
                      AND m2.Creacion_fecha >= DATE_SUB('.$lunes.', INTERVAL ? WEEK)
                      AND m2.Creacion_fecha <  '.$lunes.'
                    GROUP BY mp2.idProducto
                    ORDER BY SUM(mp2.Number) DESC, mp2.idProducto ASC
                    LIMIT '.$top.'
                ) topProd ON topProd.idProducto = bodegas_movimientos_productos.idProducto',
            'where'   => '
                bodegas_movimientos_productos.idEstadoIngreso = ?
                AND bodegas_movimientos.Creacion_fecha >= DATE_SUB('.$lunes.', INTERVAL ? WEEK)
                AND bodegas_movimientos.Creacion_fecha <  '.$lunes,
            // Orden de los ? segun aparecen en el SQL: join (1), where (2, 3)
            'params'  => [(int)$semanas, 2, (int)$semanas],
            'group'   => 'bodegas_movimientos_productos.idProducto, YEARWEEK(bodegas_movimientos.Creacion_fecha, 3)',
            'having'  => '',
            'order'   => 'bodegas_movimientos_productos.idProducto ASC, Semana ASC',
            'limit'   => FunctionsMachineLearning::MAX_SAMPLES_HARD
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $Response = $this->Base_GetList($xParams);

        /************************************/
        // Validacion de errores
        if ($Response['status'] === false) {
            // Se retorna el error
            return $Response;
        }

        /************************************/
        // Total real de productos con demanda en la ventana (antes del top) para informar el truncado
        $query = [
            'data'    => 'COUNT(DISTINCT bodegas_movimientos_productos.idProducto) AS Total',
            'table'   => 'bodegas_movimientos_productos',
            'join'    => 'INNER JOIN bodegas_movimientos ON bodegas_movimientos.idMovimiento = bodegas_movimientos_productos.idMovimiento',
            'where'   => '
                bodegas_movimientos_productos.idEstadoIngreso = ?
                AND bodegas_movimientos.Creacion_fecha >= DATE_SUB('.$lunes.', INTERVAL ? WEEK)
                AND bodegas_movimientos.Creacion_fecha <  '.$lunes,
            'params'  => [2, (int)$semanas],
            'group'   => '',
            'having'  => '',
            'order'   => '',
            'limit'   => 1
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $Total   = $this->Base_GetList($xParams);

        /************************************/
        // Validacion de errores
        if ($Total['status'] === false) {
            // Se retorna el error
            return $Total;
        }

        $Response['total'] = (int)($Total['data'][0]['Total'] ?? 0);
        return $Response;
    }

    /*******************************************************************/
    // Stock actual (bodegas activas) y limite por producto
    /*******************************************************************/
    private function getStockProductos(array $idsProductos = []){
        /************************************/
        // Bodegas activas (sus ids definen las columnas Cantidad_idBodegas_N)
        $query = [
            'data'    => 'idBodegas',
            'table'   => 'bodegas_listado',
            'join'    => '',
            'where'   => 'idEstado = ?',
            'params'  => [1],
            'group'   => '',
            'having'  => '',
            'order'   => 'idBodegas ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams    = ['query' => $query];
        // Ejecuto la query
        $arrBodegas = $this->Base_GetList($xParams);

        /************************************/
        // Validacion de errores
        if ($arrBodegas['status'] === false) {
            // Se retorna el error
            return $arrBodegas;
        }

        /************************************/
        // Suma de columnas (ids casteados a entero: no vienen del usuario)
        $sumas = [];
        foreach ($arrBodegas['data'] as $bod) {
            $sumas[] = 'COALESCE(bodegas_productos_stocks.Cantidad_idBodegas_'.(int)$bod['idBodegas'].', 0)';
        }
        $StockActual = $sumas ? implode(' + ', $sumas) : '0';

        /************************************/
        // Solo el stock de los productos del dataset, por lotes bindeados (sin LIMIT que deje productos fuera, A-2)
        $filas = [];
        foreach (array_chunk(array_map('intval', $idsProductos), 500) as $lote) {
            $query = [
                'data'    => 'bodegas_productos_stocks.idProducto, productos_listado.StockLimite, ('.$StockActual.') AS StockActual',
                'table'   => 'bodegas_productos_stocks',
                'join'    => 'INNER JOIN productos_listado ON productos_listado.idProducto = bodegas_productos_stocks.idProducto',
                'where'   => 'productos_listado.idEstado = ? AND bodegas_productos_stocks.idProducto IN ('.implode(',', array_fill(0, count($lote), '?')).')',
                'params'  => array_merge([1], $lote),
                'group'   => '',
                'having'  => '',
                'order'   => '',
                'limit'   => 500
            ];
            // Preparo los datos
            $xParams  = ['query' => $query];
            // Ejecuto la query
            $Response = $this->Base_GetList($xParams);

            /************************************/
            // Validacion de errores
            if ($Response['status'] === false) {
                // Se retorna el error
                return $Response;
            }
            foreach ($Response['data'] as $fila) { $filas[] = $fila; }
        }
        return ['status' => true, 'data' => $filas];
    }

    /*******************************************************************/
    // Tendencia lineal por producto, backtest y alertas (sin BD)
    /*******************************************************************/
    /**
     * Calcula la demanda predicha, la sugerencia de reposicion y la alerta de cada producto.
     *
     * @param array $demanda Filas (idProducto, Semana YEARWEEK modo 3, Demanda) de getDatasetDemanda.
     * @param array $stock   Filas (idProducto, StockLimite, StockActual) de getStockProductos.
     * @param int   $semanas Ventana de semanas completas usada en el dataset (4-26).
     * @param mixed $hoy     Fecha de referencia (Y-m-d) para ordenar las semanas; null = hoy.
     * @param int   $totalOrigen Total real de productos con demanda antes del top (0 = el del dataset).
     *
     * @return array ['rows' => filas de bodegas_pred_reposicion (sin idEjecucion), 'metricas', 'modelo',
     *                'muestras' (productos usados), 'muestrasOrigen' (productos del dataset)].
     */
    private function trainReposicion(array $demanda, array $stock, $semanas, $hoy = null, $totalOrigen = 0){
        $ML      = $this->MachineLearning ?? new FunctionsMachineLearning();
        $semanas = (int)$semanas;

        /************************************/
        // Semanas de la ventana (YEARWEEK modo 3 = ISO: anio+semana), de la mas antigua a la mas reciente
        $lunes = new DateTime($hoy ?: 'today');
        $lunes->modify('monday this week');
        $listaSemanas = [];
        for ($i = $semanas; $i >= 1; $i--) {
            $d = clone $lunes;
            $d->modify('-'.$i.' week');
            $listaSemanas[] = (int)$d->format('oW');
        }

        /************************************/
        // Demanda agrupada por producto
        $porProducto = [];
        foreach ($demanda as $fila) {
            $porProducto[(int)$fila['idProducto']][(int)$fila['Semana']] = (float)$fila['Demanda'];
        }
        $muestrasOrigen = max(count($porProducto), (int)$totalOrigen);
        // Tope de muestras (productos), recorte determinista
        $cap         = $ML->capSamples(array_keys($porProducto));
        $productoIds = $cap['rows'];

        /************************************/
        // Stock por producto
        $stockMap = [];
        foreach ($stock as $fila) {
            $stockMap[(int)$fila['idProducto']] = $fila;
        }

        /************************************/
        // Prediccion por producto
        $rows = [];
        $realBacktest = [];
        $predBacktest = [];
        $baseBacktest = [];
        foreach ($productoIds as $idProducto) {
            // Serie ordenada con ceros en semanas sin movimiento
            $serie     = [];
            $conDatos  = 0;
            foreach ($listaSemanas as $sem) {
                $v = $porProducto[$idProducto][$sem] ?? 0.0;
                if ($v > 0) { $conDatos++; }
                $serie[] = $v;
            }
            // Minimo de semanas con datos
            if ($conDatos < self::MIN_CON_DATOS) {
                continue;
            }
            $tendencia = $ML->linearTrend($serie);
            $predicha  = max(0.0, (float)$tendencia['next']);
            $media     = array_sum($serie) / count($serie);

            // Backtest: ajusta con n-1 semanas, predice la ultima y compara con la media
            $ultima = array_pop($serie);
            $previo = $ML->linearTrend($serie);
            $realBacktest[] = $ultima;
            $predBacktest[] = max(0.0, (float)$previo['next']);
            $baseBacktest[] = array_sum($serie) / count($serie);

            // Stock, alerta y sugerencia
            $sinStock    = !isset($stockMap[$idProducto]);
            $stockActual = $sinStock ? 0.0 : (float)$stockMap[$idProducto]['StockActual'];
            $limite      = (isset($stockMap[$idProducto]) && $stockMap[$idProducto]['StockLimite'] !== null && $stockMap[$idProducto]['StockLimite'] !== '')
                           ? (float)$stockMap[$idProducto]['StockLimite'] : null;
            if ($sinStock) {
                // Sin dato de stock no se emite alerta (0 = sin dato; la vista lo muestra como '-')
                $alerta = 0;
            } elseif ($stockActual < $predicha) {
                $alerta = 3;
            } elseif ($limite !== null && $stockActual < $limite) {
                $alerta = 2;
            } else {
                $alerta = 1;
            }
            $sugerida = $sinStock ? 0.0 : max(0.0, $predicha + ($limite ?? 0.0) - $stockActual);

            $rows[] = [
                'idProducto'      => $idProducto,
                'SemanasUsadas'   => $semanas,
                'DemandaMedia'    => round($media, 2),
                'DemandaPredicha' => round($predicha, 2),
                'Pendiente'       => round((float)$tendencia['slope'], 4),
                'StockActual'     => round($stockActual, 2),
                'StockLimite'     => $limite === null ? null : round($limite, 2),
                'Cobertura'       => (!$sinStock && $predicha > 0) ? round($stockActual / $predicha, 2) : null,
                'Sugerida'        => round($sugerida, 2),
                'Alerta'          => $alerta,
            ];
        }

        /************************************/
        // Metricas del backtest frente a la media (linea base)
        $m = $ML->regressionMetrics($realBacktest, $predBacktest);
        $b = $ML->regressionMetrics($realBacktest, $baseBacktest);

        return [
            'rows'           => $rows,
            'metricas'       => ['mae' => $m['mae'], 'rmse' => $m['rmse'], 'maeBase' => $b['mae'], 'rmseBase' => $b['rmse'], 'productos' => count($rows)],
            'modelo'         => ['semanas' => $semanas, 'algoritmo' => 'LeastSquares', 'version' => '0.10.0'],
            'muestras'       => count($productoIds),
            'muestrasOrigen' => $muestrasOrigen,
        ];
    }

    /*******************************************************************/
    // Inicia una ejecucion (transaccion corta, una activa por informe)
    /*******************************************************************/
    private function startRun($idUsuario){
        /************************************/
        // Transaccion propia
        $this->Base_transactionBegin();

        /************************************/
        // Marca como expiradas las activas antiguas
        $xParams = [
            'query'     => 'UPDATE bodegas_pred_ejecuciones SET Estado = 4, Activa = NULL WHERE Informe = ? AND Activa = 1 AND FechaInicio < DATE_SUB(NOW(), INTERVAL '.(int)FunctionsMachineLearning::STALE_MINUTES.' MINUTE)',
            'newBDConn' => $this->getDBConn(),
            'showQuery' => false,
            'singleRow' => false,
            'params'    => [self::INFORME],
        ];
        $Response = $this->Base_queryExecute($xParams);

        /************************************/
        // Si falla la la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se reporta el error
            return ['status' => false, 'code' => 500, 'message' => 'Error al recalcular'];
        }

        /************************************/
        // Intervalo minimo desde la ultima ejecucion correcta
        $xParams = [
            'query'     => 'SELECT idEjecucion FROM bodegas_pred_ejecuciones WHERE Informe = ? AND Estado = 2 AND FechaFin > DATE_SUB(NOW(), INTERVAL '.(int)FunctionsMachineLearning::MIN_INTERVAL_SECONDS.' SECOND) LIMIT 1',
            'newBDConn' => $this->getDBConn(),
            'showQuery' => false,
            'singleRow' => false,
            'params'    => [self::INFORME],
        ];
        $Response = $this->Base_queryExecute($xParams);

        /************************************/
        // Si falla la la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se reporta el error
            return ['status' => false, 'code' => 500, 'message' => 'Error al recalcular'];
        }
        if (!empty($Response['data'])) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se reporta el error
            return ['status' => false, 'code' => 400, 'message' => 'Espere antes de recalcular de nuevo'];
        }

        /************************************/
        // Se crea la ejecucion activa (el indice unico (Informe, Activa) rechaza la concurrente)
        // FechaInicio con NOW() de la BD (misma hora que las comparaciones de expiracion e intervalo, A-4)
        $xParams = [
            'query'     => 'INSERT INTO bodegas_pred_ejecuciones (Informe, idUsuario, Estado, Activa, FechaInicio) VALUES (?, ?, 1, 1, NOW())',
            'newBDConn' => $this->getDBConn(),
            'showQuery' => false,
            'singleRow' => false,
            'params'    => [self::INFORME, (int)$idUsuario],
        ];
        $Response = $this->Base_queryExecute($xParams);

        /************************************/
        // Si falla la la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se reporta el error
            $interno = (string)($Response['internal_error'] ?? '');
            if (stripos($interno, 'Duplicate') !== false || strpos($interno, '1062') !== false) {
                return ['status' => false, 'code' => 400, 'message' => 'Ya hay un recálculo en curso'];
            }
            return ['status' => false, 'code' => 500, 'message' => 'Error al recalcular'];
        }
        $Response['data'] = (int)$this->getDBConn()->lastInsertId();

        /************************************/
        // Se confirma la transacción
        $Commit = $this->Base_transactionCommit();

        /************************************/
        // Si falla la la ejecucion, se revierte de inmediato
        if ($Commit['status'] === false) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se reporta el error
            return ['status' => false, 'code' => 500, 'message' => 'Error al recalcular'];
        }
        return ['status' => true, 'idEjecucion' => $Response['data']];
    }

    /*******************************************************************/
    // Guarda resultados, cierra la ejecucion y purga por retencion
    /*******************************************************************/
    private function saveRun($idEjecucion, array $rows, array $meta){
        $idEjecucion = (int)$idEjecucion;

        /************************************/
        // Transaccion propia
        $this->Base_transactionBegin();

        /************************************/
        // Resultados por lotes
        foreach (array_chunk($rows, FunctionsMachineLearning::INSERT_CHUNK) as $chunk) {
            $lote = [];
            foreach ($chunk as $row) {
                $row['idEjecucion'] = $idEjecucion;
                $lote[] = $row;
            }
            $query = [
                'data'  => 'idEjecucion,idProducto,SemanasUsadas,DemandaMedia,DemandaPredicha,Pendiente,StockActual,StockLimite,Cobertura,Sugerida,Alerta',
                'table' => 'bodegas_pred_reposicion',
                'rows'  => $lote,
            ];
            // Preparo los datos
            $xParams  = ['query' => $query];
            // Ejecuto la query
            $Response = $this->Base_insertMultiple($xParams);

            /************************************/
            // Si falla la la ejecucion, se revierte de inmediato
            if ($Response['status'] === false) {
                // Se revierte la transaccion activa (no-op si no hay transaccion)
                $this->Base_transactionRollback();
                // Se reporta el error
                return ['status' => false];
            }
        }

        /************************************/
        // Cierra la ejecucion (UPDATE propio: Activa = NULL no sale de Base_update)
        $xParams = [
            'query'     => 'UPDATE bodegas_pred_ejecuciones SET Estado = 2, Activa = NULL, Algoritmo = ?, FechaFin = NOW(), DuracionMs = ?, Muestras = ?, MuestrasOrigen = ?, Metricas = ?, Modelo = ? WHERE idEjecucion = ?',
            'newBDConn' => $this->getDBConn(),
            'showQuery' => false,
            'singleRow' => false,
            'params'    => [
                (string)$meta['algoritmo'],
                (int)round((microtime(true) - (float)$meta['inicio']) * 1000),
                (int)$meta['muestras'],
                (int)$meta['muestrasOrigen'],
                json_encode($meta['metricas']),
                json_encode($meta['modelo']),
                $idEjecucion,
            ],
        ];
        $Response = $this->Base_queryExecute($xParams);

        /************************************/
        // Si falla la la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se reporta el error
            return ['status' => false];
        }

        /************************************/
        // Purga: ejecuciones correctas posteriores a las KEEP_RUNS mas recientes
        $xParams = [
            'query'     => 'SELECT idEjecucion FROM bodegas_pred_ejecuciones WHERE Informe = ? AND Estado = 2 ORDER BY idEjecucion DESC LIMIT 18446744073709551615 OFFSET '.(int)FunctionsMachineLearning::KEEP_RUNS,
            'newBDConn' => $this->getDBConn(),
            'showQuery' => false,
            'singleRow' => false,
            'params'    => [self::INFORME],
        ];
        $Response = $this->Base_queryExecute($xParams);

        /************************************/
        // Si falla la la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se reporta el error
            return ['status' => false];
        }
        $ids = array_map('intval', array_column($Response['data'], 'idEjecucion'));
        if (!empty($ids)) {
            $in = implode(',', $ids);
            foreach (['bodegas_pred_reposicion', 'bodegas_pred_ejecuciones'] as $tabla) {
                $xParams = [
                    'query'     => 'DELETE FROM '.$tabla.' WHERE idEjecucion IN ('.$in.')',
                    'newBDConn' => $this->getDBConn(),
                    'showQuery' => false,
                    'singleRow' => false,
                    'params'    => [],
                ];
                $Response = $this->Base_queryExecute($xParams);

                /************************************/
                // Si falla la la ejecucion, se revierte de inmediato
                if ($Response['status'] === false) {
                    // Se revierte la transaccion activa (no-op si no hay transaccion)
                    $this->Base_transactionRollback();
                    // Se reporta el error
                    return ['status' => false];
                }
            }
        }

        /************************************/
        // Se confirma la transacción
        $Commit = $this->Base_transactionCommit();

        /************************************/
        // Si falla la la ejecucion, se revierte de inmediato
        if ($Commit['status'] === false) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se reporta el error
            return ['status' => false];
        }
        return ['status' => true];
    }

    /*******************************************************************/
    // Marca la ejecucion como fallida (mensaje fijo, sin excepciones)
    /*******************************************************************/
    private function failRun($idEjecucion, $mensaje){
        try {
            // Rollback defensivo por si quedo una transaccion abierta (no-op si no la hay)
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se reporta el error
            $xParams = [
                'query'     => 'UPDATE bodegas_pred_ejecuciones SET Estado = 3, Activa = NULL, FechaFin = NOW(), Mensaje = ? WHERE idEjecucion = ?',
                'newBDConn' => $this->getDBConn(),
                'showQuery' => false,
                'singleRow' => false,
                'params'    => [(string)$mensaje, (int)$idEjecucion],
            ];
            $this->Base_queryExecute($xParams);
        } catch (Throwable $e) {
            error_log('informeReposicion::failRun: '.$e->getMessage());
        }
    }

}
