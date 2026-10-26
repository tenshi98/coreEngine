<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class maquinasListadoSensoresTesting
 *
 * Pruebas del modulo `maquinas` - controlador `maquinasListadoSensores` (administrador
 * de sensores por maquina, persistidos como arreglo JSON en `maquinas_listado.Sensores`).
 *
 * Convenciones del sistema de testeos: ver `maquinasSensoresGruposTesting`.
 * Alcance NO destructivo: `Insert/Update/Delete/SaveOrder` terminan en `Response::*()`
 * (que hace `exit`) y escriben en base de datos; se verifican por reflexion y fuente.
 * La logica pura del JSON (`sensorDecode/sensorEncode/sensorFind/dataCheck`) se ejecuta
 * sin base de datos.
 *
 * @see skills/testing/SKILL.md
 */
class maquinasListadoSensoresTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private const CONTROLADOR = 'maquinasListadoSensores';
    private const TABLA       = 'maquinas_listado';
    private const RUTA_WEB    = 'administracion/maquinas/listado';

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
            'controlador'   => $this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'),
            'vista_form_new' => $this->rutaModulo('../views/maquinasListado-Resumen-Sensores-formNew.php'),
            'vista_form_edit'=> $this->rutaModulo('../views/maquinasListado-Resumen-Sensores-formEdit.php'),
            'vista_update_list' => $this->rutaModulo('../views/maquinasListado-Resumen-Sensores-UpdateList.php'),
            'vista_view'    => $this->rutaModulo('../views/maquinasListado-Resumen-Sensores-View.php'),
            'resumen'       => $this->rutaModulo('../views/maquinasListado-Resumen.php'),
            'testing'       => $this->rutaModulo('maquinasListadoSensoresTesting.php'),
        ];
        foreach ($arrArchivos as $nombre => $ruta) {
            $this->addCase($results, 'archivo_'.$nombre, is_file($ruta), 'no se encuentra '.$ruta);
        }

        $this->addCase($results, 'clase_controlador_cargada', class_exists(self::CONTROLADOR),
            'la clase '.self::CONTROLADOR.' no esta disponible');
        $this->addCase($results, 'clase_testing_cargada', class_exists('maquinasListadoSensoresTesting'),
            'la clase maquinasListadoSensoresTesting no esta disponible');
        $this->addCase($results, 'herencia_controlador', get_parent_class(self::CONTROLADOR) === 'ControllerBase',
            'el controlador no extiende ControllerBase');
        $this->addCase($results, 'herencia_testing', get_parent_class('maquinasListadoSensoresTesting') === 'ControllerBase',
            'el testing no extiende ControllerBase');

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
        $this->addCase($results, 'vista_form_new', str_contains($codigo, "'-Resumen-Sensores-formNew.php'"),
            'New() no instancia la vista del modal de alta');
        $this->addCase($results, 'vista_update_list', str_contains($codigo, "'-Resumen-Sensores-UpdateList.php'"),
            'UpdateList() no instancia la vista del listado');
        $this->addCase($results, 'vista_view', str_contains($codigo, "'-Resumen-Sensores-View.php'"),
            'View() no instancia la vista de detalle');
        $this->addCase($results, 'vista_form_edit', str_contains($codigo, "'-Resumen-Sensores-formEdit.php'"),
            'GetID() no instancia la vista del modal de edicion');
        $this->addCase($results, 'namespace_listado', str_contains($codigo, "\$this->controllerName = 'maquinasListado'"),
            'se espera conservar controllerName=maquinasListado para el namespace de vistas');
        $this->addCase($results, 'fallback_errores', str_contains($codigo, 'mergeResponses('),
            'el controlador no agrupa las respuestas fallidas con mergeResponses()');
        $this->addCase($results, 'nivel_usuario', str_contains($codigo, 'getArrLevel($f3,'),
            'el controlador no resuelve el nivel de acceso del usuario');
        $this->addCase($results, 'catalogo_grupos', str_contains($codigo, "'maquinas_sensores_grupos'"),
            'el controlador no consulta el catalogo de grupos');
        $this->addCase($results, 'catalogo_subgrupos', str_contains($codigo, "'maquinas_sensores_subgrupos'"),
            'el controlador no consulta el catalogo de subgrupos');
        $this->addCase($results, 'catalogo_unimed', str_contains($codigo, "'core_unidades_medida'"),
            'el controlador no consulta el catalogo de unidades de medida');
        $this->addCase($results, 'catalogo_estados', str_contains($codigo, "'core_estados'"),
            'el controlador no consulta el catalogo de estados');
        $this->addCase($results, 'id_maquina_en_edicion', str_contains($codigo, "'idMaquina'        => \$MaquinaID['data']"),
            'GetID() no expone el idMaquina para el formulario de edicion');
        $this->addCase($results, 'uid_por_query', str_contains($codigo, "\$_GET['uid']"),
            'View()/GetID() no leen el uid del sensor por query string');

        return $results;
    }

    /******************************************************************************/
    /*                         CONTROLADOR: ESCRITURA                             */
    /******************************************************************************/
    public function controladorEscrituraTest(){
        $results = [];

        foreach (['Insert' => [0, 0], 'Update' => [0, 0], 'Delete' => [0, 0], 'SaveOrder' => [0, 0]] as $metodo => $p) {
            $this->firmaMetodo($results, $metodo, $p[0], $p[1]);
        }

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
        $this->addCase($results, 'columna_sensores', str_contains($codigo, "'Sensores' => \$this->sensorEncode("),
            'la persistencia no escribe la columna Sensores con el arreglo codificado');
        $this->addCase($results, 'helpers_json',
            str_contains($codigo, 'sensorDecode(') && str_contains($codigo, 'sensorEncode(') && str_contains($codigo, 'sensorFind('),
            'faltan los helpers sensorDecode/sensorEncode/sensorFind');
        $this->addCase($results, 'uid_identidad', str_contains($codigo, "'uid'        => (\$uid !== null && \$uid !== '') ? \$uid : uniqid()"),
            'el sensor no recibe un uid unico al construirse');

        /*====================================*/
        // Alta, edicion y baja por uid
        /*====================================*/
        $this->addCase($results, 'insert_append', str_contains($codigo, '$arrSensores[] = $sensor'),
            'Insert() no agrega el sensor al final del arreglo');
        $this->addCase($results, 'update_reemplazo', str_contains($codigo, '$arrSensores[$indice] = $sensor'),
            'Update() no reemplaza el sensor por su indice');
        $this->addCase($results, 'update_uid_buscado', str_contains($codigo, "\$this->sensorFind(\$arrSensores, \$_POST['uid']"),
            'Update() no localiza el sensor por uid');
        $this->addCase($results, 'delete_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'DELETE'"),
            'Delete() no valida el verbo HTTP DELETE');
        $this->addCase($results, 'delete_uid', str_contains($codigo, "\$this->sensorFind(\$arrSensores, \$dataDelete['uid']"),
            'Delete() no elimina el sensor por uid');
        $this->addCase($results, 'delete_compacta', str_contains($codigo, 'array_values($arrSensores)'),
            'Delete() no compacta el arreglo tras el borrado');

        /*====================================*/
        // Reordenamiento (RN-005)
        /*====================================*/
        $this->addCase($results, 'order_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'POST'"),
            'SaveOrder() no valida el verbo HTTP POST');
        $this->addCase($results, 'order_uids', str_contains($codigo, "\$_POST['uids']"),
            'SaveOrder() no recibe el arreglo de uid');
        $this->addCase($results, 'order_conserva_elementos',
            str_contains($codigo, 'count($arrOrder) !== count($arrSensores)'),
            'SaveOrder() no verifica que no falte ningun sensor');
        $this->addCase($results, 'order_descifra_maquina', str_contains($codigo, "encryptDecrypt('decrypt', \$_POST['idMaquina']"),
            'SaveOrder() no valida el idMaquina recibido');

        /*====================================*/
        // Reglas de negocio
        /*====================================*/
        $this->addCase($results, 'rn_obligatorios', str_contains($codigo, "'idUniMed'  => 'Unidad de Medida'"),
            'buildSensor() no exige los campos obligatorios (RN-001)');
        $this->addCase($results, 'rn_subgrupo_de_grupo', str_contains($codigo, 'no corresponde al grupo'),
            'buildSensor() no valida que el subgrupo pertenezca al grupo (RN-002)');
        $this->addCase($results, 'datacheck_numericos',
            str_contains($codigo, "'ValidarNumero'             => 'idGrupo,idSubGrupo,idUniMed,idEstado'"),
            'dataCheck() no valida los identificadores de catalogo como enteros');

        return $results;
    }

    /******************************************************************************/
    /*                              LOGICA PURA (JSON)                            */
    /******************************************************************************/
    public function logicaSensoresTest(){
        $results = [];

        /*====================================*/
        // sensorDecode: tolerante a vacio y a JSON invalido
        /*====================================*/
        try {
            $this->addCase($results, 'decode_vacio', $this->invocarPrivado('sensorDecode', ['']) === [],
                'sensorDecode() no devuelve arreglo vacio para entrada vacia');
            $this->addCase($results, 'decode_invalido', $this->invocarPrivado('sensorDecode', ['{no-json']) === [],
                'sensorDecode() no tolera JSON invalido');
            $decodificado = $this->invocarPrivado('sensorDecode', ['{"0":{"uid":"a1","Nombre":"T"}}']);
            $this->addCase($results, 'decode_valido', is_array($decodificado) && ($decodificado[0]['uid'] ?? '') === 'a1',
                'sensorDecode() no reconstruye el arreglo');
        } catch (Throwable $e) {
            $this->addCase($results, 'decode_vacio', false, 'no se pudo ejecutar sensorDecode() ('.$e->getMessage().')');
        }

        /*====================================*/
        // sensorEncode / sensorDecode: ida y vuelta estable
        /*====================================*/
        try {
            $sensor = ['uid' => 'x9', 'Nombre' => 'Presion', 'idGrupo' => 2, 'Grupo' => 'Presion'];
            $json   = $this->invocarPrivado('sensorEncode', [[$sensor]]);
            $vuelta = $this->invocarPrivado('sensorDecode', [$json]);
            $this->addCase($results, 'encode_es_json', is_string($json) && json_decode($json, true) !== null,
                'sensorEncode() no produjo JSON valido');
            $this->addCase($results, 'encode_unicode_libre', str_contains($json, 'Presion') && !str_contains($json, '\\u'),
                'sensorEncode() no conserva el texto sin escapar unicode');
            $this->addCase($results, 'encode_ida_y_vuelta', ($vuelta[0] ?? null) === $sensor,
                'el arreglo codificado no vuelve identico al decodificarse');
        } catch (Throwable $e) {
            $this->addCase($results, 'encode_ida_y_vuelta', false, 'no se pudo ejecutar sensorEncode() ('.$e->getMessage().')');
        }

        /*====================================*/
        // sensorFind: encontrado, ausente y uid vacio
        /*====================================*/
        try {
            $arr = [['uid' => 'a'], ['uid' => 'b'], ['uid' => 'c']];
            $this->addCase($results, 'find_indice', $this->invocarPrivado('sensorFind', [$arr, 'b']) === 1,
                'sensorFind() no devuelve el indice del uid');
            $this->addCase($results, 'find_ausente', $this->invocarPrivado('sensorFind', [$arr, 'z']) === false,
                'sensorFind() no devuelve false cuando no encuentra el uid');
            $this->addCase($results, 'find_vacio', $this->invocarPrivado('sensorFind', [$arr, '']) === false,
                'sensorFind() no rechaza un uid vacio');
        } catch (Throwable $e) {
            $this->addCase($results, 'find_indice', false, 'no se pudo ejecutar sensorFind() ('.$e->getMessage().')');
        }

        /*====================================*/
        // dataCheck / dataCheckPersist
        /*====================================*/
        try {
            $Post      = ['Nombre' => 'Temperatura', 'idGrupo' => 1, 'idSubGrupo' => 1, 'idUniMed' => 1, 'idEstado' => 1];
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

        $arrVistas = glob($this->rutaModulo('../views/maquinasListado-Resumen-Sensores-*.php')) ?: [];
        $this->addCase($results, 'hay_vistas', count($arrVistas) === 4,
            'se esperaban 4 vistas de sensores, hay '.count($arrVistas));

        foreach ($arrVistas as $ruta) {
            $nombre = basename($ruta);
            $codigo = $this->codigoFuente($ruta);
            $this->addCase($results, 'vista_sin_sesion_'.$nombre, !str_contains($codigo, '$_SESSION'),
                $nombre.' accede a la sesion de forma directa');
            $this->addCase($results, 'vista_sin_sql_'.$nombre,
                !preg_match('/\b(SELECT|INSERT|UPDATE|DELETE)\s+\w+\s+FROM\b/i', $codigo),
                $nombre.' contiene una consulta SQL embebida');
        }

        $tabla = $this->codigoFuente($this->rutaModulo('../views/maquinasListado-Resumen-Sensores-UpdateList.php'));
        $this->addCase($results, 'tabla_escape_entidad', str_contains($tabla, 'addslashes('),
            'UpdateList.php no escapa la etiqueta de la entidad');
        $this->addCase($results, 'tabla_uid_dato', str_contains($tabla, 'data-uid='),
            'UpdateList.php no expone el uid para el reordenamiento');

        $resumen = $this->codigoFuente($this->rutaModulo('../views/maquinasListado-Resumen.php'));
        $this->addCase($results, 'resumen_sortable', str_contains($resumen, 'initSensoresSortable'),
            'Resumen.php no define/reutiliza el reordenamiento de sensores');
        $this->addCase($results, 'resumen_endpoint_order', str_contains($resumen, '/sensores/order'),
            'Resumen.php no persiste el orden en el endpoint de sensores');

        $controlador = $this->fuenteControlador();
        $this->addCase($results, 'controlador_sin_concat_sql',
            !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $controlador),
            'el controlador concatena superglobales en una cadena SQL');
        $this->addCase($results, 'controlador_parametros_preparados', str_contains($controlador, "'params'"),
            'el controlador no usa parametros preparados');

        return $results;
    }

}
