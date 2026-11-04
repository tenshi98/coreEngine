<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class maquinasListadoAlarmasTesting
 *
 * Pruebas del modulo `maquinas` - controlador `maquinasListadoAlarmas` (administrador
 * de alarmas personalizadas por maquina, persistidas como arreglo JSON en
 * `maquinas_listado.Alarmas`).
 *
 * Convenciones del sistema de testeos: ver `maquinasListadoSensoresTesting`.
 * Alcance NO destructivo: `Insert/Update/Delete` terminan en `Response::*()`
 * (que hace `exit`) y escriben en base de datos; se verifican por reflexion y fuente.
 * La logica pura del JSON (`alarmaDecode/alarmaEncode/alarmaFind`) y las
 * validaciones auxiliares se ejecutan sin base de datos.
 *
 * @see skills/testing/SKILL.md
 */
class maquinasListadoAlarmasTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private const CONTROLADOR = 'maquinasListadoAlarmas';
    private const TABLA       = 'maquinas_listado';

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

    private function instanciaSinConstructor(){
        return (new ReflectionClass(self::CONTROLADOR))->newInstanceWithoutConstructor();
    }

    private function invocarPrivado(string $metodo, array $args){
        $reflector = new ReflectionMethod(self::CONTROLADOR, $metodo);
        $reflector->setAccessible(true);
        return $reflector->invokeArgs($this->instanciaSinConstructor(), $args);
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    public function estructuraModuloTest(){
        $results = [];

        $arrArchivos = [
            'controlador'        => $this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'),
            'vista_form_new'     => $this->rutaModulo('../views/maquinasListado-Resumen-Alarmas-formNew.php'),
            'vista_form_edit'    => $this->rutaModulo('../views/maquinasListado-Resumen-Alarmas-formEdit.php'),
            'vista_update_list'  => $this->rutaModulo('../views/maquinasListado-Resumen-Alarmas-UpdateList.php'),
            'vista_view'         => $this->rutaModulo('../views/maquinasListado-Resumen-Alarmas-View.php'),
            'resumen'            => $this->rutaModulo('../views/maquinasListado-Resumen.php'),
            'testing'            => $this->rutaModulo('maquinasListadoAlarmasTesting.php'),
        ];
        foreach ($arrArchivos as $nombre => $ruta) {
            $this->addCase($results, 'archivo_'.$nombre, is_file($ruta), 'no se encuentra '.$ruta);
        }

        $this->addCase($results, 'clase_controlador_cargada', class_exists(self::CONTROLADOR),
            'la clase '.self::CONTROLADOR.' no esta disponible');
        $this->addCase($results, 'clase_testing_cargada', class_exists('maquinasListadoAlarmasTesting'),
            'la clase maquinasListadoAlarmasTesting no esta disponible');
        $this->addCase($results, 'herencia_controlador', get_parent_class(self::CONTROLADOR) === 'ControllerBase',
            'el controlador no extiende ControllerBase');
        $this->addCase($results, 'herencia_testing', get_parent_class('maquinasListadoAlarmasTesting') === 'ControllerBase',
            'el testing no extiende ControllerBase');

        /*====================================*/
        // Esquema: columna JSON y catalogos
        /*====================================*/
        $installer = $this->codigoFuente($this->rutaModulo('../installer/maquinasInstaller.php'));
        $this->addCase($results, 'installer_columna_alarmas', str_contains($installer, '`Alarmas` longtext'),
            'el instalador no agrega la columna Alarmas');
        $this->addCase($results, 'installer_catalogo_tipos', str_contains($installer, 'core_tipos_alarmas'),
            'el instalador no crea el catalogo core_tipos_alarmas');
        $this->addCase($results, 'installer_catalogo_prioridades', str_contains($installer, 'core_tipos_alarmas_prioridad'),
            'el instalador no crea el catalogo core_tipos_alarmas_prioridad');

        $installSql = $this->codigoFuente($this->rutaModulo('../../installer/files/install.sql'));
        $this->addCase($results, 'install_sql_tipos', str_contains($installSql, 'INSERT INTO `core_tipos_alarmas`'),
            'install.sql no siembra los tipos de alarma');
        $this->addCase($results, 'install_sql_prioridades', str_contains($installSql, 'INSERT INTO `core_tipos_alarmas_prioridad`'),
            'install.sql no siembra las prioridades de alarma');

        return $results;
    }

    /******************************************************************************/
    /*                            CONTROLADOR: VISTAS                             */
    /******************************************************************************/
    public function controladorVistasTest(){
        $results = [];

        foreach (['New' => [2, 2], 'UpdateList' => [2, 2], 'View' => [2, 2], 'GetID' => [2, 2]] as $metodo => $p) {
            $this->firmaMetodo($results, $metodo, $p[0], $p[1]);
        }

        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'vista_form_new', str_contains($codigo, "'-Resumen-Alarmas-formNew.php'"),
            'New() no instancia la vista del modal de alta');
        $this->addCase($results, 'vista_update_list', str_contains($codigo, "'-Resumen-Alarmas-UpdateList.php'"),
            'UpdateList() no instancia la vista del listado');
        $this->addCase($results, 'vista_view', str_contains($codigo, "'-Resumen-Alarmas-View.php'"),
            'View() no instancia la vista de detalle');
        $this->addCase($results, 'vista_form_edit', str_contains($codigo, "'-Resumen-Alarmas-formEdit.php'"),
            'GetID() no instancia la vista del modal de edicion');
        $this->addCase($results, 'namespace_listado', str_contains($codigo, "\$this->controllerName = 'maquinasListado'"),
            'se espera conservar controllerName=maquinasListado para el namespace de vistas');
        $this->addCase($results, 'fallback_errores', str_contains($codigo, 'mergeResponses('),
            'el controlador no agrupa las respuestas fallidas con mergeResponses()');
        $this->addCase($results, 'nivel_usuario', str_contains($codigo, 'getArrLevel($f3,'),
            'el controlador no resuelve el nivel de acceso del usuario');
        $this->addCase($results, 'catalogo_tipos', str_contains($codigo, "'core_tipos_alarmas'"),
            'el controlador no consulta el catalogo de tipos de alarma');
        $this->addCase($results, 'catalogo_prioridades', str_contains($codigo, "'core_tipos_alarmas_prioridad'"),
            'el controlador no consulta el catalogo de prioridades');
        $this->addCase($results, 'catalogo_unimed', str_contains($codigo, "'core_unidades_medida'"),
            'el controlador no consulta el catalogo de unidades de medida');
        $this->addCase($results, 'catalogo_estados', str_contains($codigo, "'core_estados'"),
            'el controlador no consulta el catalogo de estados');
        $this->addCase($results, 'id_maquina_en_edicion', str_contains($codigo, "'idMaquina'        => \$MaquinaID['data']"),
            'GetID() no expone el idMaquina para el formulario de edicion');
        $this->addCase($results, 'uid_por_query', str_contains($codigo, "\$_GET['uid']"),
            'View()/GetID() no leen el uid de la alarma por query string');

        return $results;
    }

    /******************************************************************************/
    /*                         CONTROLADOR: ESCRITURA                             */
    /******************************************************************************/
    public function controladorEscrituraTest(){
        $results = [];

        foreach (['Insert' => [0, 0], 'Update' => [0, 0], 'Delete' => [0, 0]] as $metodo => $p) {
            $this->firmaMetodo($results, $metodo, $p[0], $p[1]);
        }
        $this->addCase($results, 'sin_save_order', !method_exists(self::CONTROLADOR, 'SaveOrder'),
            'las alarmas no deben tener reordenamiento (no SaveOrder)');

        $codigo = $this->fuenteControlador();

        /*====================================*/
        // Persistencia transaccional del arreglo JSON
        /*====================================*/
        $this->addCase($results, 'transaccion_begin', substr_count($codigo, 'Base_transactionBegin()') >= 1,
            'la persistencia no abre transaccion');
        $this->addCase($results, 'transaccion_commit', substr_count($codigo, 'Base_transactionCommit()') >= 1,
            'la persistencia no confirma la transaccion');
        $this->addCase($results, 'transaccion_revertir', substr_count($codigo, 'failOperation(') >= 1,
            'la persistencia no revierte con failOperation()');
        $this->addCase($results, 'transaccion_rollback_directo', substr_count($codigo, 'Base_transactionRollback()') === 0,
            'no debe llamar Base_transactionRollback() directo');
        $posCommit = strpos($codigo, 'Base_transactionCommit()');
        $this->addCase($results, 'commit_antes_exito',
            $posCommit !== false && strpos($codigo, 'return $Response;', $posCommit) !== false,
            'la persistencia no confirma la transaccion antes de retornar la respuesta');

        /*====================================*/
        // Contrato del arreglo JSON
        /*====================================*/
        $this->addCase($results, 'tabla_destino', str_contains($codigo, "'table'     => '".self::TABLA."'"),
            'el controlador no persiste en '.self::TABLA);
        $this->addCase($results, 'columna_alarmas', str_contains($codigo, "'Alarmas' => \$this->alarmaEncode("),
            'la persistencia no escribe la columna Alarmas con el arreglo codificado');
        $this->addCase($results, 'helpers_json',
            str_contains($codigo, 'alarmaDecode(') && str_contains($codigo, 'alarmaEncode(') && str_contains($codigo, 'alarmaFind('),
            'faltan los helpers alarmaDecode/alarmaEncode/alarmaFind');
        $this->addCase($results, 'uid_identidad', str_contains($codigo, "'uid'                  => (\$uid !== null && \$uid !== '') ? \$uid : uniqid()"),
            'la alarma no recibe un uid unico al construirse');

        /*====================================*/
        // Alta, edicion y baja por uid
        /*====================================*/
        $this->addCase($results, 'insert_append', str_contains($codigo, '$arrAlarmas[] = $alarma'),
            'Insert() no agrega la alarma al final del arreglo');
        $this->addCase($results, 'update_reemplazo', str_contains($codigo, '$arrAlarmas[$indice] = $alarma'),
            'Update() no reemplaza la alarma por su indice');
        $this->addCase($results, 'update_uid_buscado', str_contains($codigo, "\$this->alarmaFind(\$arrAlarmas, \$uid)"),
            'Update() no localiza la alarma por uid');
        $this->addCase($results, 'insert_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'POST'"),
            'Insert()/Update() no validan el verbo HTTP POST');
        $this->addCase($results, 'delete_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'DELETE'"),
            'Delete() no valida el verbo HTTP DELETE');
        $this->addCase($results, 'delete_uid', str_contains($codigo, "\$this->alarmaFind(\$arrAlarmas, \$dataDelete['uid'] ?? '')"),
            'Delete() no elimina la alarma por uid');
        $this->addCase($results, 'delete_compacta', str_contains($codigo, 'array_values($arrAlarmas)'),
            'Delete() no compacta el arreglo tras el borrado');
        $this->addCase($results, 'delete_id_maquina', str_contains($codigo, "\$dataDelete['idMaquina']"),
            'Delete() no recibe el idMaquina cifrado en el cuerpo');

        /*====================================*/
        // Reglas de negocio
        /*====================================*/
        $this->addCase($results, 'rn_obligatorios', str_contains($codigo, "'MaxErrores'   => 'N° Máximo Errores'"),
            'buildAlarma() no exige los campos obligatorios (RN-001)');
        $this->addCase($results, 'rn_sensor_activo', str_contains($codigo, "'El sensor seleccionado no es válido o no está activo'"),
            'buildAlarma() no valida que el sensor exista y este activo (RN-002/RN-003)');
        $this->addCase($results, 'rn_sensor_unico', str_contains($codigo, "'El sensor seleccionado está repetido'"),
            'buildAlarma() no impide sensores repetidos (RN-004)');
        $this->addCase($results, 'rn_rango', str_contains($codigo, 'if ($inicio > $termino)'),
            'buildAlarma() no valida el orden de los rangos (RN-009)');
        $this->addCase($results, 'rn_porcentaje', str_contains($codigo, '$porcentaje < 0 || $porcentaje > 100'),
            'buildAlarma() no valida el porcentaje entre 0 y 100 (RN-010)');
        $this->addCase($results, 'rn_horas', str_contains($codigo, 'horaValida('),
            'buildAlarma() no valida el formato de horas (RN-011)');
        $this->addCase($results, 'datacheck_numericos',
            str_contains($codigo, "'ValidarNumero'              => 'idMaquina,idTipoAlarma,idPrioridad,idUniMed,idEstado'"),
            'dataCheck() no valida los identificadores de catalogo como enteros');

        return $results;
    }

    /******************************************************************************/
    /*                              LOGICA PURA (JSON)                            */
    /******************************************************************************/
    public function logicaAlarmasTest(){
        $results = [];

        /*====================================*/
        // alarmaDecode: tolerante a vacio y a JSON invalido
        /*====================================*/
        try {
            $this->addCase($results, 'decode_vacio', $this->invocarPrivado('alarmaDecode', ['']) === [],
                'alarmaDecode() no devuelve arreglo vacio para entrada vacia');
            $this->addCase($results, 'decode_invalido', $this->invocarPrivado('alarmaDecode', ['{no-json']) === [],
                'alarmaDecode() no tolera JSON invalido');
            $decodificado = $this->invocarPrivado('alarmaDecode', ['[{"uid":"a1","Nombre":"T"}]']);
            $this->addCase($results, 'decode_valido', is_array($decodificado) && ($decodificado[0]['uid'] ?? '') === 'a1',
                'alarmaDecode() no reconstruye el arreglo');
        } catch (Throwable $e) {
            $this->addCase($results, 'decode_vacio', false, 'no se pudo ejecutar alarmaDecode() ('.$e->getMessage().')');
        }

        /*====================================*/
        // alarmaEncode / alarmaDecode: ida y vuelta estable
        /*====================================*/
        try {
            $alarma = ['uid' => 'x9', 'Nombre' => 'Presión', 'idTipoAlarma' => 4, 'TipoAlarma' => 'Errores Conjuntos'];
            $json   = $this->invocarPrivado('alarmaEncode', [[$alarma]]);
            $vuelta = $this->invocarPrivado('alarmaDecode', [$json]);
            $this->addCase($results, 'encode_es_json', is_string($json) && json_decode($json, true) !== null,
                'alarmaEncode() no produjo JSON valido');
            $this->addCase($results, 'encode_unicode_libre', str_contains($json, 'Presión') && !str_contains($json, '\\u'),
                'alarmaEncode() no conserva el texto sin escapar unicode');
            $this->addCase($results, 'encode_ida_y_vuelta', ($vuelta[0] ?? null) === $alarma,
                'el arreglo codificado no vuelve identico al decodificarse');
        } catch (Throwable $e) {
            $this->addCase($results, 'encode_ida_y_vuelta', false, 'no se pudo ejecutar alarmaEncode() ('.$e->getMessage().')');
        }

        /*====================================*/
        // alarmaFind: encontrado, ausente y uid vacio
        /*====================================*/
        try {
            $arr = [['uid' => 'a'], ['uid' => 'b'], ['uid' => 'c']];
            $this->addCase($results, 'find_indice', $this->invocarPrivado('alarmaFind', [$arr, 'b']) === 1,
                'alarmaFind() no devuelve el indice del uid');
            $this->addCase($results, 'find_ausente', $this->invocarPrivado('alarmaFind', [$arr, 'z']) === false,
                'alarmaFind() no devuelve false cuando no encuentra el uid');
            $this->addCase($results, 'find_vacio', $this->invocarPrivado('alarmaFind', [$arr, '']) === false,
                'alarmaFind() no rechaza un uid vacio');
        } catch (Throwable $e) {
            $this->addCase($results, 'find_indice', false, 'no se pudo ejecutar alarmaFind() ('.$e->getMessage().')');
        }

        /*====================================*/
        // filaVacia / valorDecimal / horaValida
        /*====================================*/
        try {
            $vacia = ['RangoInicio' => [''], 'RangoTermino' => [''], 'ValorEspecifico' => [''], 'ValorEncendido' => ['']];
            $this->addCase($results, 'fila_vacia', $this->invocarPrivado('filaVacia', [$vacia, 0]) === true,
                'filaVacia() no detecta una fila sin datos');
            $conDato = $vacia;
            $conDato['ValorEspecifico'] = ['5'];
            $this->addCase($results, 'fila_con_dato', $this->invocarPrivado('filaVacia', [$conDato, 0]) === false,
                'filaVacia() no detecta una fila con datos');

            $this->addCase($results, 'decimal_entero', $this->invocarPrivado('valorDecimal', ['12']) === 12,
                'valorDecimal() no convierte enteros');
            $this->addCase($results, 'decimal_coma', $this->invocarPrivado('valorDecimal', ['12,5']) === 12.5,
                'valorDecimal() no acepta coma decimal');
            $this->addCase($results, 'decimal_invalido', $this->invocarPrivado('valorDecimal', ['abc']) === false,
                'valorDecimal() no rechaza valores no numericos');

            $this->addCase($results, 'hora_valida', $this->invocarPrivado('horaValida', ['23:59']) === true,
                'horaValida() no acepta HH:mm valido');
            $this->addCase($results, 'hora_invalida', $this->invocarPrivado('horaValida', ['24:00']) === false,
                'horaValida() no rechaza horas fuera de rango');
        } catch (Throwable $e) {
            $this->addCase($results, 'fila_vacia', false, 'no se pudieron ejecutar los auxiliares ('.$e->getMessage().')');
        }

        /*====================================*/
        // sensoresSelect: transforma uid/Nombre al formato ID/Nombre
        /*====================================*/
        try {
            $select = $this->invocarPrivado('sensoresSelect', [[['uid' => 's1', 'Nombre' => 'Temperatura']]]);
            $this->addCase($results, 'sensores_select',
                ($select[0]['ID'] ?? null) === 's1' && ($select[0]['Nombre'] ?? null) === 'Temperatura',
                'sensoresSelect() no transforma los sensores al formato de los widgets');
        } catch (Throwable $e) {
            $this->addCase($results, 'sensores_select', false, 'no se pudo ejecutar sensoresSelect() ('.$e->getMessage().')');
        }

        /*====================================*/
        // dataCheck / dataCheckPersist
        /*====================================*/
        try {
            $Post      = ['Nombre' => 'Temperatura', 'idMaquina' => 1, 'idTipoAlarma' => 1, 'idPrioridad' => 1, 'idUniMed' => 1, 'idEstado' => 1, 'MaxErrores' => 3];
            $DataCheck = $this->invocarPrivado('dataCheck', [$Post]);
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post,
                'dataCheck() no conserva el POST recibido');
            $this->addCase($results, 'datacheck_largo',
                (string)($DataCheck['ValidarLargoMinimo'] ?? '') === 'Nombre'
                && (int)($DataCheck['ValidarLargoMinimoN'] ?? 0) === 3
                && ($DataCheck['ValidarLargoMaximoPorCampo'] ?? []) === [
                    'Nombre' => 120,
                ],
                'dataCheck() no fija correctamente todos los campos de largo (minimo/maximo)');

            $Persist = $this->invocarPrivado('dataCheckPersist', []);
            $this->addCase($results, 'datacheck_persist_sin_texto',
                ($Persist['ValidarLargoMinimo'] ?? '') === '' && ($Persist['ValidarNumero'] ?? '') === '',
                'dataCheckPersist() no deja vacias las validaciones de texto (persistencia del JSON)');
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_post', false, 'no se pudo ejecutar el chequeo ('.$e->getMessage().')');
        }

        return $results;
    }

    /******************************************************************************/
    /*                                 SEGURIDAD                                  */
    /******************************************************************************/
    public function seguridadVistasTest(){
        $results = [];

        $arrVistas = glob($this->rutaModulo('../views/maquinasListado-Resumen-Alarmas-*.php')) ?: [];
        $this->addCase($results, 'hay_vistas', count($arrVistas) === 4,
            'se esperaban 4 vistas de alarmas, hay '.count($arrVistas));

        foreach ($arrVistas as $ruta) {
            $nombre = basename($ruta);
            $codigo = $this->codigoFuente($ruta);
            $this->addCase($results, 'vista_sin_sesion_'.$nombre, !str_contains($codigo, '$_SESSION'),
                $nombre.' accede a la sesion de forma directa');
            $this->addCase($results, 'vista_sin_sql_'.$nombre,
                !preg_match('/\b(SELECT|INSERT|UPDATE|DELETE)\s+\w+\s+FROM\b/i', $codigo),
                $nombre.' contiene una consulta SQL embebida');
        }

        $formNew = $this->codigoFuente($this->rutaModulo('../views/maquinasListado-Resumen-Alarmas-formNew.php'));
        $formEdit = $this->codigoFuente($this->rutaModulo('../views/maquinasListado-Resumen-Alarmas-formEdit.php'));
        foreach (['formNew' => $formNew, 'formEdit' => $formEdit] as $nombre => $codigo) {
            $this->addCase($results, 'validator_'.$nombre, str_contains($codigo, 'validator.checkAll(this)'),
                $nombre.' no valida con validator.checkAll(this)');
            $this->addCase($results, 'senddata_'.$nombre, str_contains($codigo, 'SendDataForms('),
                $nombre.' no envia con SendDataForms()');
            $this->addCase($results, 'id_maquina_oculto_'.$nombre, str_contains($codigo, "'idMaquina'"),
                $nombre.' no incluye el idMaquina oculto');
        }
        $this->addCase($results, 'uid_oculto_formEdit', str_contains($formEdit, "'uid'"),
            'formEdit no incluye el uid oculto de la alarma');

        $tabla = $this->codigoFuente($this->rutaModulo('../views/maquinasListado-Resumen-Alarmas-UpdateList.php'));
        $this->addCase($results, 'tabla_escape_entidad', str_contains($tabla, 'addslashes('),
            'UpdateList.php no escapa la etiqueta de la entidad');
        $this->addCase($results, 'tabla_acciones_uid',
            str_contains($tabla, "tabAlarmasView('") && str_contains($tabla, "tabAlarmasEdit('") && str_contains($tabla, "tabAlarmasDel('"),
            'UpdateList.php no expone las acciones por uid');
        $this->addCase($results, 'tabla_sin_datatable', stripos($tabla, 'DataTable') === false,
            'UpdateList.php no debe usar DataTable');
        $this->addCase($results, 'tabla_sin_arrastre', stripos($tabla, 'sortable') === false,
            'las alarmas no deben tener reordenamiento por arrastre');

        $view = $this->codigoFuente($this->rutaModulo('../views/maquinasListado-Resumen-Alarmas-View.php'));
        $this->addCase($results, 'view_widgets', str_contains($view, 'tittle_v2(') && str_contains($view, 'table_v6('),
            'View.php no usa los widgets de detalle');

        $resumen = $this->codigoFuente($this->rutaModulo('../views/maquinasListado-Resumen.php'));
        $this->addCase($results, 'resumen_view_uid', str_contains($resumen, '/alarmas/view/') && str_contains($resumen, 'encodeURIComponent(uid)'),
            'Resumen.php no envia el uid de la alarma al detalle');
        $this->addCase($results, 'resumen_edit_uid', str_contains($resumen, '/alarmas/getID/') && str_contains($resumen, 'encodeURIComponent(uid)'),
            'Resumen.php no envia el uid de la alarma a la edicion');
        $this->addCase($results, 'resumen_delete_uid',
            str_contains($resumen, '"idMaquina"') && str_contains($resumen, '"uid": uid'),
            'Resumen.php no envia idMaquina/uid al borrado');
        $this->addCase($results, 'resumen_sin_id_alarmas', !str_contains($resumen, 'idAlarmas'),
            'Resumen.php conserva referencias al idAlarmas de Documentos');
        $this->addCase($results, 'resumen_refresca_tab', str_contains($resumen, '#tabAlarmasDataTable'),
            'Resumen.php no refresca el contenedor del listado de alarmas');

        $controlador = $this->fuenteControlador();
        $this->addCase($results, 'controlador_sin_concat_sql',
            !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $controlador),
            'el controlador concatena superglobales en una cadena SQL');
        $this->addCase($results, 'controlador_parametros_preparados', str_contains($controlador, "'params'"),
            'el controlador no usa parametros preparados');

        return $results;
    }

}
