<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class kanbanEstadosTesting
 *
 * Pruebas del modulo `kanban` - controlador `kanbanEstados` (CRUD de los tableros/estados
 * del kanban: `GetID`, `Insert`, `Update`, `Delete`).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. Los textos de los casos
 *    correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Alcance: pruebas NO destructivas. `Insert()`, `Update()` y `Delete()` escriben en base de
 * datos y terminan en `Response::*()` (que ejecuta `exit`), por lo que no se invocan: se
 * verifican por reflexion y lectura del fuente. `GetID()` consulta la BD y termina en
 * `showVista()`, tampoco se invoca; sus vistas (`formNew`/`formEdit`) se renderizan con datos
 * sinteticos.
 *
 * Particularidades del controlador:
 *  - No tiene `listAll`/`UpdateList`/`View` ni permiso propio: cuelga del permiso de
 *    `kanbanTareas` (`controllerName = 'kanbanTareas'`) y sus rutas estan en el tipo 1 del
 *    instalador.
 *  - `kanbanEstados-formNew.php` la carga `kanbanTareas-List.php` con `require_once`. Un
 *    `include` previo vuelve ese `require_once` un no-op (PHP registra el archivo como
 *    incluido), por eso `kanbanTareasTesting` debe renderizar `kanbanEstados-formNew.php`
 *    directamente y no afirmar contenido que solo llegue por ese `require_once`.
 *
 * @see skills/testing/SKILL.md
 */
class kanbanEstadosTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR         = 'kanbanEstados';
    private const CONTROLADOR_PERMISO = 'kanbanTareas';
    private const INSTALADOR          = 'kanbanTareasInstaller';
    private const RUTA_WEB            = 'gestionProyectos/kanban/tareas';
    private const RUTA_WEB_ESTADOS    = 'gestionProyectos/kanban/estados';
    private const RUTA_WEB_GETID      = 'gestionProyectos/kanban/estados/getID/@id';
    private const RUTA_WEB_UPDATE     = 'gestionProyectos/kanban/estados/update';
    // Niveles de permiso del catalogo (1=ver, 2=editar, 3=crear, 4=borrar)
    private const NIVELES             = [1, 2, 3, 4];

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
    // Renderiza una vista del modulo con datos sinteticos y devuelve el HTML.
    // El CWD se mueve a admin/public porque las plantillas de los inputs se
    // resuelven con rutas relativas ('../app/templates/...').
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
    // Contrato basico de un metodo publico del controlador
    /*******************************************************************/
    private function firmaMetodo(array &$results, string $metodo, int $minParam, int $maxParam){
        $existe = method_exists(self::CONTROLADOR, $metodo);
        $this->addCase($results, 'metodo_'.$metodo, $existe, 'no existe '.self::CONTROLADOR.'::'.$metodo.'()');
        if (!$existe) {
            return;
        }
        $reflector = new ReflectionMethod(self::CONTROLADOR, $metodo);
        $this->addCase($results, 'metodo_'.$metodo.'_publico', $reflector->isPublic(),
            $metodo.'() no es publico');
        $n = $reflector->getNumberOfParameters();
        $this->addCase($results, 'metodo_'.$metodo.'_parametros', $n >= $minParam && $n <= $maxParam,
            $metodo.'() declara '.$n.' parametros (esperado entre '.$minParam.' y '.$maxParam.')');
    }

    /*******************************************************************/
    // Rutas del instalador: [idMetodo.' '.RutaWeb => [handler, nivel, controller]]
    /*******************************************************************/
    private function rutasInstalador(): array{
        $rutas = [];
        if (!class_exists(self::INSTALADOR)) {
            return $rutas;
        }
        try {
            $instalador = (new ReflectionClass(self::INSTALADOR))->newInstanceWithoutConstructor();
            foreach (range(1, 8) as $tipo) {
                foreach ($instalador->getRouteDefinitions($tipo) as $ruta) {
                    $rutas[$ruta['idMetodo'].' '.$ruta['RutaWeb']] = [
                        $ruta['RutaController'],
                        (int)$ruta['idLevelLimit'],
                        $ruta['Controller'],
                    ];
                }
            }
        } catch (Throwable $e) {
            return [];
        }
        return $rutas;
    }

    /*******************************************************************/
    // Datos sinteticos usados por las vistas del controlador
    /*******************************************************************/
    private function datosSinteticos(int $subtitulo = 1): array{
        return [
            'UserData'         => [
                'MainPathUrl'          => '/admin/public/upload/',
                'sistemaModalSubtitle' => $subtitulo,
                'sistemaModalCloseBTN' => 2,
                'UserType'             => 2,
            ],
            'UserAccess'       => ['LevelAccess' => 4, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_FormInputs'   => new UIFormInputs(),
            'arrColores'       => [['ID' => 1, 'Nombre' => 'Color Prueba'], ['ID' => 2, 'Nombre' => 'Color Dos']],
            'arrCierre'        => [['ID' => 1, 'Nombre' => 'Cierre Prueba'], ['ID' => 2, 'Nombre' => 'Cierre Dos']],
            'rowData'          => [
                'idKanbanEstado' => 7,
                'Nombre'         => 'Tablero Sintetico',
                'idColor'        => 2,
                'idPrioridad'    => 5,
                'idCierre'       => 2,
            ],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica los archivos del controlador `kanbanEstados` y la convencion
     * "clase = nombre de archivo" exigida por el AUTOLOAD de Fat-Free.
     *
     * @return array Resultado por caso
     */
    public function estructuraModuloTest(){

        $results = [];

        /*====================================*/
        // Archivos obligatorios
        /*====================================*/
        $arrArchivos = [
            'controlador'  => $this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'),
            'instalador'   => $this->rutaModulo('../installer/'.self::INSTALADOR.'.php'),
            'vista_form_new'  => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formNew.php'),
            'vista_form_edit' => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formEdit.php'),
            'testing'      => $this->rutaModulo('kanbanEstadosTesting.php'),
        ];
        foreach ($arrArchivos as $nombre => $ruta) {
            $this->addCase($results, 'archivo_'.$nombre, is_file($ruta), 'no se encuentra '.$ruta);
        }

        /*====================================*/
        // Proteccion de acceso directo
        /*====================================*/
        $arrHtaccess = [
            'raiz'       => $this->rutaModulo('../.htaccess'),
            'controller' => $this->rutaModulo('../controller/.htaccess'),
            'testing'    => $this->rutaModulo('.htaccess'),
            'views'      => $this->rutaModulo('../views/.htaccess'),
        ];
        foreach ($arrHtaccess as $nombre => $ruta) {
            if (!is_file($ruta)) {
                $this->addCase($results, 'htaccess_'.$nombre, false, 'no se encuentra '.$ruta);
                continue;
            }
            $this->addCase($results, 'htaccess_'.$nombre,
                (stripos((string)file_get_contents($ruta), 'deny from all') !== false),
                'el archivo no contiene "deny from all"');
        }

        /*====================================*/
        // Convencion clase = nombre de archivo y herencias
        /*====================================*/
        $this->addCase($results, 'clase_controlador_cargada', class_exists(self::CONTROLADOR),
            'la clase '.self::CONTROLADOR.' no esta disponible');
        $this->addCase($results, 'clase_testing_cargada', class_exists('kanbanEstadosTesting'),
            'la clase kanbanEstadosTesting no esta disponible');
        $padre = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padre === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padre.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('kanbanEstadosTesting') === 'ControllerBase',
            'el testing no extiende ControllerBase');

        /*====================================*/
        // Constructor del controlador (el permiso es el de kanbanTareas)
        /*====================================*/
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'constructor_conexion', str_contains($codigo, 'Database::getSQLConnection(ConfigDataBase::MySQL_1)'),
            'el constructor no obtiene la conexion MySQL_1');
        $this->addCase($results, 'constructor_padre', str_contains($codigo, 'parent::__construct($DB_conn_1, $queryBuilder, $checkData)'),
            'el constructor no delega en ControllerBase');
        $this->addCase($results, 'constructor_nombre_permiso', str_contains($codigo, "\$this->controllerName = '".self::CONTROLADOR_PERMISO."'"),
            'controllerName debe ser '.self::CONTROLADOR_PERMISO.' (el estado cuelga del permiso de las tareas)');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                            CONTROLADOR: VISTAS                             */
    /******************************************************************************/
    /**
     * Verifica el contrato de `GetID` (formulario de edicion): ID cifrado, consultas de
     * catalogo, manejo de fallos y que el controlador no expone vistas de listado.
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Firma: GetID($f3, $params)
        /*====================================*/
        $this->firmaMetodo($results, 'GetID', 2, 2);

        /*====================================*/
        // Sin vistas de listado: es un sub-recurso de kanbanTareas
        /*====================================*/
        foreach (['listAll', 'UpdateList', 'View', 'Print', 'NewData'] as $metodo) {
            $this->addCase($results, 'sin_vista_'.$metodo, !method_exists(self::CONTROLADOR, $metodo),
                'kanbanEstados no debe exponer '.$metodo.'(): el listado lo pinta kanbanTareas');
        }

        /*====================================*/
        // Contrato de presentacion
        /*====================================*/
        $this->addCase($results, 'vista_getid', str_contains($codigo, "returnRutaVista(__DIR__, 'app').'/kanbanEstados-formEdit.php'"),
            'GetID() no instancia la vista kanbanEstados-formEdit');
        $this->addCase($results, 'tipoview_getid', substr_count($codigo, 'showVista(2,') === 1,
            'GetID() debe usar showVista(2) (fragmento para el modal)');
        $this->addCase($results, 'usuario_en_vista', substr_count($codigo, 'getUserData($f3)') === 1,
            'GetID() no obtiene los datos del usuario');
        $this->addCase($results, 'nivel_en_vista', substr_count($codigo, 'getArrLevel($f3, $this->controllerName)') === 1,
            'GetID() no resuelve el nivel de acceso');
        $this->addCase($results, 'getid_maneja_fallo',
            substr_count($codigo, 'showError(2, $f3, $result)') === 1
            && str_contains($codigo, 'mergeResponses([$rowData,$arrColores,$arrCierre])')
            && str_contains($codigo, "if(\$rowData['status'] && \$arrColores['status'] && \$arrCierre['status'])"),
            'GetID() debe comprobar el status de las tres consultas y usar showError(2)/mergeResponses()');

        /*====================================*/
        // ID cifrado
        /*====================================*/
        $this->addCase($results, 'id_descifrado', substr_count($codigo, "encryptDecrypt('decrypt', \$params['id'])") === 1,
            'GetID() debe descifrar el ID de la ruta');
        $this->addCase($results, 'id_validado', substr_count($codigo, "isValidDecrypted(\$KanbanEstadoID, 'id')") === 1,
            'GetID() debe validar el ID descifrado');
        $this->addCase($results, 'id_invalido_400', substr_count($codigo, "Response::error('Registro inválido', 400)") === 1,
            'un ID invalido debe responder 400');
        $this->addCase($results, 'getid_estado_inexistente_404',
            str_contains($codigo, "if(\$rowData['status'] === true && empty(\$rowData['data']))") && str_contains($codigo, "Response::error('Registro no encontrado', 404)"),
            'GetID() debe responder 404 cuando el estado no existe');

        /*====================================*/
        // Consultas: el estado y los catalogos de color y cierre
        /*====================================*/
        $this->addCase($results, 'getid_estado',
            str_contains($codigo, "'data'    => 'idKanbanEstado,Nombre,idColor,idPrioridad,idCierre'")
            && str_contains($codigo, "'where'   => 'idKanbanEstado = ?'")
            && str_contains($codigo, "'params'  => [\$KanbanEstadoID['data']]"),
            'GetID() no lee el estado por idKanbanEstado con el ID como parametro preparado');
        $this->addCase($results, 'getid_catalogo_colores',
            str_contains($codigo, "'table'   => 'core_estados_colores'") && str_contains($codigo, "'data'    => 'idColor AS ID,Nombre'"),
            'GetID() no carga el catalogo de colores');
        $this->addCase($results, 'getid_catalogo_cierre',
            str_contains($codigo, "'table'   => 'core_opciones'") && str_contains($codigo, "'data'    => 'idOpciones AS ID,Nombre'"),
            'GetID() no carga el catalogo de opciones de cierre');
        $this->addCase($results, 'consultas_acotadas', substr_count($codigo, 'ConfigAPP::APP["N_MaxItems"]') === 2,
            'las dos consultas de catalogo deben acotar el numero de filas');
        $this->addCase($results, 'catalogos_ordenados', substr_count($codigo, "'order'   => 'Nombre ASC'") === 2,
            'los catalogos de colores y cierre deben ordenarse por Nombre');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                           CONTROLADOR: ESCRITURA                           */
    /******************************************************************************/
    /**
     * Verifica `Insert`, `Update` y `Delete` SIN ejecutarlos: verbo HTTP, chequeo de datos,
     * campos obligatorios y unicos, delimitacion por idKanbanEstado y respuestas al cliente.
     * El CRUD envuelve cada escritura en una transaccion Base_transactionBegin/Commit; los fallos pasan a failOperation.
     *
     * @return array Resultado por caso
     */
    public function controladorEscrituraTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Firma de cada metodo de escritura (sin parametros)
        /*====================================*/
        foreach (['Insert', 'Update', 'Delete'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 0, 0);
        }

        /*====================================*/
        // Chequeo de datos y tabla
        /*====================================*/
        $this->addCase($results, 'datacheck_post', substr_count($codigo, '$this->dataCheck($_POST)') === 2,
            'Insert() y Update() deben construir el DataCheck con dataCheck($_POST)');
        $this->addCase($results, 'tabla_estados', preg_match_all("/'table'\s*=>\s*'kanban_estados'/", $codigo) === 4,
            'se esperaban 4 usos de kanban_estados (GetID, Insert, Update y Delete)');
        $this->addCase($results, 'sin_codificacion', substr_count($codigo, "'encode'    => ''") === 2,
            'los estados no tienen campos codificados');
        $this->addCase($results, 'nombre_unico', substr_count($codigo, "'unique'    => 'Nombre'") === 2,
            'Insert() y Update() deben exigir Nombre unico');

        /*====================================*/
        // Insert
        /*====================================*/
        $this->addCase($results, 'insert_campos', str_contains($codigo, "'data'      => 'Nombre,idColor,idPrioridad,idCierre'"),
            'Insert() no declara los campos esperados del estado');
        $this->addCase($results, 'insert_requeridos', str_contains($codigo, "'required'  => 'Nombre,idColor,idPrioridad,idCierre'"),
            'Insert() no declara los cuatro campos como obligatorios');
        $this->addCase($results, 'insert_base', str_contains($codigo, '$this->Base_insert('),
            'Insert() no escribe con Base_insert()');

        /*====================================*/
        // Update
        /*====================================*/
        $this->addCase($results, 'update_verbo_post', str_contains($codigo, "REQUEST_METHOD'] !== 'POST'"),
            'Update() no valida el verbo HTTP POST');
        $this->addCase($results, 'update_donde', str_contains($codigo, "'where'     => 'idKanbanEstado'"),
            'Update() no delimita la escritura por idKanbanEstado');
        $this->addCase($results, 'update_campos', str_contains($codigo, "'data'      => 'idKanbanEstado,Nombre,idColor,idPrioridad,idCierre'"),
            'Update() no declara los campos esperados del estado');
        $this->addCase($results, 'update_requeridos', str_contains($codigo, "'required'  => 'idKanbanEstado,Nombre,idColor,idPrioridad,idCierre'"),
            'Update() no declara los obligatorios');
        $this->addCase($results, 'update_base', str_contains($codigo, '$this->Base_update('),
            'Update() no escribe con Base_update()');

        /*====================================*/
        // Delete: verbo, delimitacion y sin archivos
        /*====================================*/
        $this->addCase($results, 'delete_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'DELETE'"),
            'Delete() no valida el verbo HTTP DELETE');
        $this->addCase($results, 'delete_donde', str_contains($codigo, "'where'       => 'idKanbanEstado'"),
            'Delete() no delimita el borrado por idKanbanEstado');
        $this->addCase($results, 'delete_sin_archivos', str_contains($codigo, "'files'       => ''"),
            'Delete() no declara que el estado no maneja archivos');
        $this->addCase($results, 'delete_base', str_contains($codigo, '$this->Base_delete('),
            'Delete() no borra con Base_delete()');
        $this->addCase($results, 'delete_lee_cuerpo', str_contains($codigo, 'parse_str(file_get_contents("php://input")'),
            'Delete() no lee el cuerpo DELETE');

        /*====================================*/
        // Transaccion: cada escritura (Insert/Update/Delete) abre la transaccion,
        // la confirma y la revierte ante el fallo de la Base de Datos
        /*====================================*/
        $this->addCase($results, 'transaccion_begin',
            substr_count($codigo, 'Base_transactionBegin()') === 3,
            'Insert/Update/Delete deben abrir transaccion');
        $this->addCase($results, 'transaccion_commit',
            substr_count($codigo, 'Base_transactionCommit()') === 3,
            'Insert/Update/Delete deben confirmar la transaccion');
        $this->addCase($results, 'transaccion_rollback',
            substr_count($codigo, 'Base_transactionRollback()') === 0,
            'Insert/Update/Delete deben revertir la transaccion ante el fallo de la Base');
        $this->addCase($results, 'transaccion_rollback_antes_de_error',
            substr_count($codigo, 'failOperation(') >= 3,
            'cada punto de fallo de Base debe invocar failOperation');
        $this->addCase($results, 'transaccion_commit_antes_de_exito',
            strpos($codigo, 'Base_transactionCommit()') < strpos($codigo, 'Response::success('),
            'la confirmacion debe ocurrir antes de responder con exito');

        /*====================================*/
        // Respuestas al cliente
        /*====================================*/
        $this->addCase($results, 'respuesta_exito', substr_count($codigo, 'Response::success(') === 3,
            'se esperaban 3 Response::success() (Insert, Update y Delete)');
        $this->addCase($results, 'respuesta_405', substr_count($codigo, "Response::error('Error en el Request Method', 405)") === 3,
            'Insert(), Update() y Delete() deben responder 405 ante un verbo incorrecto');
        $this->addCase($results, 'insert_verbo_post',
            strpos($codigo, "REQUEST_METHOD'] !== 'POST'") !== false && strpos($codigo, "REQUEST_METHOD'] !== 'POST'") < strpos($codigo, '$this->Base_insert('),
            'Insert() debe validar el verbo HTTP POST antes de escribir');
        $this->addCase($results, 'respuesta_500', substr_count($codigo, 'failOperation(') === 3,
            'se esperaban 3 puntos failOperation (Insert, Update y Delete)');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                       CONTROLADOR: RUTAS E INSTALADOR                      */
    /******************************************************************************/
    /**
     * Verifica que las 4 rutas de `kanbanEstados` publicadas por el instalador apunten a
     * metodos reales con el verbo y el nivel esperados, y que el controlador figure en la
     * lista de desinstalacion (no tiene un permiso propio).
     *
     * @return array Resultado por caso
     */
    public function controladorRutasTest(){

        $results = [];
        $rutas   = $this->rutasInstalador();
        $this->addCase($results, 'instalador_rutas_disponibles', count($rutas) > 0,
            'getRouteDefinitions() no devolvio rutas');

        /*====================================*/
        // idMetodo: 1=GET, 2=POST, 3=DELETE. [clave, handler, nivel]
        /*====================================*/
        $esperadas = [
            'getid'    => ['1 '.self::RUTA_WEB_GETID,      'kanbanEstados->GetID',  2],
            'crear'    => ['2 '.self::RUTA_WEB_ESTADOS,    'kanbanEstados->Insert', 3],
            'editar'   => ['2 '.self::RUTA_WEB_UPDATE,     'kanbanEstados->Update', 2],
            'borrar'   => ['3 '.self::RUTA_WEB_ESTADOS,    'kanbanEstados->Delete', 4],
        ];
        foreach ($esperadas as $caso => $esperada) {
            [$clave, $handler, $nivel] = $esperada;
            $existe = isset($rutas[$clave]);
            $this->addCase($results, 'ruta_'.$caso, $existe, 'el instalador no publica la ruta '.$clave);
            if (!$existe) {
                continue;
            }
            $this->addCase($results, 'ruta_'.$caso.'_handler', strcasecmp($rutas[$clave][0], $handler) === 0,
                'la ruta '.$clave.' apunta a '.$rutas[$clave][0].' en vez de '.$handler);
            $this->addCase($results, 'ruta_'.$caso.'_nivel', $rutas[$clave][1] === $nivel,
                'la ruta '.$clave.' exige nivel '.$rutas[$clave][1].' en vez de '.$nivel);
            $this->addCase($results, 'ruta_'.$caso.'_controller', $rutas[$clave][2] === self::CONTROLADOR,
                'la ruta '.$clave.' queda asignada al permiso '.$rutas[$clave][2].' en vez de '.self::CONTROLADOR);
            $partes = explode('->', $rutas[$clave][0]);
            $this->addCase($results, 'ruta_'.$caso.'_metodo_existe',
                count($partes) === 2 && method_exists($partes[0], $partes[1]),
                'el handler '.$rutas[$clave][0].' no existe');
        }

        /*====================================*/
        // Conjunto completo de rutas propias y escalera de niveles (ver < editar < crear < borrar)
        /*====================================*/
        $propias = array_filter($rutas, static function ($r) { return $r[2] === 'kanbanEstados'; });
        $this->addCase($results, 'rutas_propias_completas', count($propias) === 4,
            'se esperaban 4 rutas de kanbanEstados y hay '.count($propias));
        $this->addCase($results, 'rutas_en_tipo_tareas', $this->rutasDeTipo(1, 'kanbanEstados') === 4,
            'las 4 rutas de kanbanEstados deben publicarse en el primer permiso (tareas)');

        /*====================================*/
        // Instalador: sin permiso propio y desinstalacion
        /*====================================*/
        $instalador = $this->codigoFuente($this->rutaModulo('../installer/'.self::INSTALADOR.'.php'));
        $this->addCase($results, 'instalador_sin_permiso_propio', !preg_match("/'RutaController'\s*=>\s*'kanbanEstados'/", $instalador),
            'kanbanEstados declara un permiso propio: debe colgar del permiso de kanbanTareas');
        $this->addCase($results, 'instalador_desinstala_estados', str_contains($instalador, '"kanbanEstados"'),
            'RutaController() no lista kanbanEstados y dejaria rutas huerfanas al desinstalar');
        // La tabla creada por el instalador debe tener las columnas que escribe el controlador
        $this->addCase($results, 'instalador_tabla_estados',
            (bool)preg_match("/'table'\s*=>\s*'kanban_estados',\s*\n\s*'data'\s*=>\s*'[^\n]*`idKanbanEstado`[^\n]*`Nombre` varchar\(255\)[^\n]*`idColor`[^\n]*`idPrioridad`[^\n]*`idCierre`/", $instalador),
            'el instalador no crea kanban_estados con idKanbanEstado, Nombre varchar(255), idColor, idPrioridad e idCierre');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /*******************************************************************/
    // Cantidad de rutas de un controlador dentro de un tipo del instalador
    /*******************************************************************/
    private function rutasDeTipo(int $tipo, string $controller): int{
        try {
            $instalador = (new ReflectionClass(self::INSTALADOR))->newInstanceWithoutConstructor();
            $n = 0;
            foreach ($instalador->getRouteDefinitions($tipo) as $ruta) {
                if ($ruta['Controller'] === $controller) {
                    $n++;
                }
            }
            return $n;
        } catch (Throwable $e) {
            return -1;
        }
    }

    /******************************************************************************/
    /*                                PERMISOS                                    */
    /******************************************************************************/
    /**
     * Verifica la sesion, el nivel de acceso heredado del permiso de `kanbanTareas` y el
     * registro de las rutas de estados en el router de F3 (solo si el usuario las tiene).
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function permisosSesionTest($f3){

        $results = [];

        $UserData = $f3->get('SESSION.DataInfo');
        $this->addCase($results, 'sesion_usuario_presente', is_array($UserData) && !empty($UserData),
            'no hay datos de usuario en la sesion');

        /*====================================*/
        // Lectura de la sesion desde el controlador (usa el permiso de kanbanTareas)
        /*====================================*/
        try {
            $this->addCase($results, 'get_user_data_arreglo', is_array($this->getUserData($f3)),
                'getUserData() no devolvio un arreglo');
            $arrLevel = $this->getArrLevel($f3, self::CONTROLADOR_PERMISO);
            $this->addCase($results, 'get_arr_level_arreglo', is_array($arrLevel),
                'getArrLevel() no devolvio un arreglo');
        } catch (Throwable $e) {
            $this->addCase($results, 'get_arr_level_arreglo', false,
                'no se pudo leer la sesion del controlador ('.$e->getMessage().')');
            $arrLevel = [];
        }

        $nivel = is_array($arrLevel) ? ($arrLevel['LevelAccess'] ?? null) : null;
        $this->addCase($results, 'nivel_en_rango', $nivel === null || in_array((int)$nivel, self::NIVELES, true),
            'LevelAccess quedo fuera del catalogo 1..4');
        $this->addCase($results, 'ruta_de_acceso_coincide', $nivel === null || ($arrLevel['RouteAccess'] ?? '') === self::RUTA_WEB,
            'RouteAccess no coincide con '.self::RUTA_WEB);

        /*====================================*/
        // Rutas en SESSION.arrPermisos y en el router (solo si las tiene)
        /*====================================*/
        $arrPermisos = $f3->get('SESSION.arrPermisos');
        $this->addCase($results, 'permisos_de_ruta_presentes', $arrPermisos === null || is_array($arrPermisos),
            'SESSION.arrPermisos no es un arreglo');
        if (is_array($arrPermisos)) {
            $rutaOk = false;
            foreach ($arrPermisos as $permiso) {
                if (isset($permiso['RutaWeb']) && $permiso['RutaWeb'] === self::RUTA_WEB_GETID) {
                    $rutaOk = true;
                    break;
                }
            }
            $this->addCase($results, 'ruta_registrada', $rutaOk || $nivel === null || (int)$nivel < 2,
                'el usuario tiene nivel '.var_export($nivel, true).' pero la ruta '.self::RUTA_WEB_GETID.' no esta en sus permisos');
        }

        $arrRoutes = $f3->get('ROUTES');
        $this->addCase($results, 'router_disponible', is_array($arrRoutes), 'F3 no expuso la variable ROUTES');
        $esquema = [
            'ruta_getid_router'  => ['/'.self::RUTA_WEB_GETID,   'GET',    self::CONTROLADOR.'->GetID'],
            'ruta_insert_router' => ['/'.self::RUTA_WEB_ESTADOS, 'POST',   self::CONTROLADOR.'->Insert'],
            'ruta_update_router' => ['/'.self::RUTA_WEB_UPDATE,  'POST',   self::CONTROLADOR.'->Update'],
            'ruta_delete_router' => ['/'.self::RUTA_WEB_ESTADOS, 'DELETE', self::CONTROLADOR.'->Delete'],
        ];
        if (is_array($arrRoutes)) {
            foreach ($esquema as $caso => $rutaEsperada) {
                [$ruta, $verbo, $handlerEsperado] = $rutaEsperada;
                if (!isset($arrRoutes[$ruta])) {
                    continue;
                }
                $handler = $arrRoutes[$ruta][0][$verbo][0] ?? '';
                $this->addCase($results, $caso, strcasecmp((string)$handler, $handlerEsperado) === 0,
                    'la ruta '.$ruta.' apunta a '.var_export($handler, true).' en vez de '.$handlerEsperado);
            }
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              VISTAS: FORMULARIOS                           */
    /******************************************************************************/
    /**
     * Renderiza `kanbanEstados-formNew.php` y `kanbanEstados-formEdit.php` con datos
     * sinteticos: modal, formulario, inputs con su prefijo, catalogos, valores precargados,
     * campo oculto, endpoints con su verbo y las dos variantes del subtitulo del modal.
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function vistasFormulariosTest($f3){

        $results = [];
        $BASE    = (string)$f3->get('BASE');
        if (trim($BASE) === '') {
            $BASE = '/admin';
        }
        $rutaNew  = $this->rutaModulo('../views/'.self::CONTROLADOR.'-formNew.php');
        $rutaEdit = $this->rutaModulo('../views/'.self::CONTROLADOR.'-formEdit.php');

        /*====================================*/
        // Formulario de creacion (modal)
        /*====================================*/
        $html = $this->renderizarVista($rutaNew, $this->datosSinteticos(1), $BASE, $f3);
        $this->addCase($results, 'new_render', $html !== '', 'la vista formNew no produjo HTML');
        $this->addCase($results, 'new_modal', str_contains($html, 'id="newFormModalEstado"') && str_contains($html, 'modal-dialog'),
            'falta el modal de creacion');
        $this->addCase($results, 'new_form', str_contains($html, 'id="FormNewDataEstado"'),
            'no se dibuja el formulario de creacion');
        $this->addCase($results, 'new_inputs',
            str_contains($html, 'id="NewEstado_Nombre"') && str_contains($html, 'id="NewEstado_idColor"')
            && str_contains($html, 'id="NewEstado_idPrioridad"') && str_contains($html, 'id="NewEstado_idCierre"'),
            'el formulario no declara Nombre, idColor, idPrioridad e idCierre con prefijo NewEstado_');
        $this->addCase($results, 'new_catalogos',
            str_contains($html, 'Color Prueba') && str_contains($html, 'Cierre Prueba'),
            'el formulario no ofrece los catalogos de colores y cierre');
        $this->addCase($results, 'new_prioridad_1_a_25',
            preg_match('/<option[^>]*value="1"/', $html) === 1 && preg_match('/<option[^>]*value="25"/', $html) === 1
            && preg_match('/<option[^>]*value="26"/', $html) === 0,
            'la prioridad debe ofrecer los valores 1 a 25');
        $this->addCase($results, 'new_campos_obligatorios', substr_count($html, 'required') >= 4,
            'los cuatro campos deben marcarse como obligatorios');
        $this->addCase($results, 'new_endpoint_creacion',
            str_contains($html, $BASE.'/'.self::RUTA_WEB_ESTADOS) && str_contains($html, "let Metodo      = 'POST'"),
            'el formulario no envia por POST a '.self::RUTA_WEB_ESTADOS);
        $this->addCase($results, 'new_actualiza_lista',
            str_contains($html, $BASE.'/'.self::RUTA_WEB.'/updateList') && str_contains($html, "closeModal:'#newFormModalEstado'"),
            'el formulario no refresca el listado ni cierra el modal al guardar');
        $this->addCase($results, 'new_titulo_sin_subtitulo', str_contains($html, 'Crear Nuevo Tablero') && !str_contains($html, 'modal-subtitle'),
            'con sistemaModalSubtitle = 1 no debe dibujarse el subtitulo');

        $html2 = $this->renderizarVista($rutaNew, $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'new_subtitulo_modal', str_contains($html2, 'modal-subtitle') && str_contains($html2, 'Permite crear un nuevo elemento'),
            'con sistemaModalSubtitle = 2 debe dibujarse el subtitulo');

        /*====================================*/
        // Formulario de edicion (fragmento del modal)
        /*====================================*/
        $html = $this->renderizarVista($rutaEdit, $this->datosSinteticos(1), $BASE, $f3);
        $this->addCase($results, 'edit_render', $html !== '', 'la vista formEdit no produjo HTML');
        $this->addCase($results, 'edit_form', str_contains($html, 'id="FormEditDataEstado"'),
            'no se dibuja el formulario de edicion');
        $this->addCase($results, 'edit_inputs',
            str_contains($html, 'id="EditEstado_Nombre"') && str_contains($html, 'id="EditEstado_idColor"')
            && str_contains($html, 'id="EditEstado_idPrioridad"') && str_contains($html, 'id="EditEstado_idCierre"'),
            'el formulario no declara Nombre, idColor, idPrioridad e idCierre con prefijo EditEstado_');
        $this->addCase($results, 'edit_valor_nombre', str_contains($html, 'Tablero Sintetico'),
            'el formulario no precarga el nombre del estado');
        $this->addCase($results, 'edit_valores_seleccionados',
            preg_match('/<option[^>]*value="2"[^>]*selected/', $html) === 1 && preg_match('/<option[^>]*value="5"[^>]*selected/', $html) === 1,
            'el formulario no preselecciona el color, la prioridad y el cierre del estado');
        $this->addCase($results, 'edit_id_oculto', preg_match('/<input[^>]*name="idKanbanEstado"[^>]*value="7"|<input[^>]*value="7"[^>]*name="idKanbanEstado"/', $html) === 1,
            'el formulario no incluye el idKanbanEstado en un campo oculto');
        $this->addCase($results, 'edit_endpoint_update',
            str_contains($html, $BASE.'/'.self::RUTA_WEB_UPDATE) && str_contains($html, "let Metodo      = 'POST'"),
            'el formulario no envia por POST a '.self::RUTA_WEB_UPDATE);
        $this->addCase($results, 'edit_actualiza_lista',
            str_contains($html, $BASE.'/'.self::RUTA_WEB.'/updateList') && str_contains($html, "closeModal:'#viewModal-xl'"),
            'el formulario debe refrescar el listado y cerrar #viewModal-xl, el modal donde kanbanTareas-List lo abre');
        $this->addCase($results, 'edit_modal_coincide_con_el_que_lo_abre',
            str_contains($this->codigoFuente($this->rutaModulo('../views/kanbanTareas-List.php')), "showModal : '#viewModal-xl'")
            && str_contains($this->codigoFuente($this->rutaModulo('../views/kanbanTareas-List.php')), '/gestionProyectos/kanban/estados/getID/'),
            'kanbanTareas-List debe abrir el formulario de edicion de estados en #viewModal-xl');
        $this->addCase($results, 'edit_titulo', str_contains($html, 'Editar Información') && !str_contains($html, 'modal-subtitle'),
            'con sistemaModalSubtitle = 1 no debe dibujarse el subtitulo');

        $html2 = $this->renderizarVista($rutaEdit, $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'edit_subtitulo_modal', str_contains($html2, 'modal-subtitle') && str_contains($html2, 'Permite editar un elemento existente'),
            'con sistemaModalSubtitle = 2 debe dibujarse el subtitulo');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos `dataCheck()` (contrato de CheckData) y el cifrado de
     * identificadores que consume `GetID`.
     *
     * @return array Resultado por caso
     */
    public function logicaValidacionTest(){

        $results = [];

        /*====================================*/
        // dataCheck: contrato esperado por Base_insert/Base_update
        /*====================================*/
        try {
            $reflector = new ReflectionMethod(self::CONTROLADOR, 'dataCheck');
            $reflector->setAccessible(true);
            $Controller = (new ReflectionClass(self::CONTROLADOR))->newInstanceWithoutConstructor();

            $Post      = ['Nombre' => 'Tablero Prueba', 'idColor' => 1, 'idPrioridad' => 5, 'idCierre' => 1];
            $DataCheck = $reflector->invoke($Controller, $Post);

            $this->addCase($results, 'datacheck_es_arreglo', is_array($DataCheck), 'dataCheck() no devolvio un arreglo');
            $this->addCase($results, 'datacheck_privado', $reflector->isPrivate(), 'dataCheck() debe ser privado');
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post,
                'dataCheck() no conserva el POST recibido');
            $this->addCase($results, 'datacheck_claves_contrato',
                isset($DataCheck['emptyData'], $DataCheck['ValidarNumero'], $DataCheck['ValidarEntero'], $DataCheck['ValidarLargoMinimoN'], $DataCheck['ValidarLargoMaximoPorCampo']),
                'dataCheck() no entrega las claves del contrato de CheckData');
            $this->addCase($results, 'datacheck_ids_enteros',
                $DataCheck['ValidarEntero'] === 'idColor,idPrioridad,idCierre' && $DataCheck['ValidarNumero'] === 'idColor,idPrioridad,idCierre',
                'dataCheck() no valida idColor, idPrioridad e idCierre como numeros enteros');
            $this->addCase($results, 'datacheck_largos',
                $DataCheck['ValidarLargoMinimo'] === 'Nombre'
                && (int)$DataCheck['ValidarLargoMinimoN'] === 3 && (int)($DataCheck['ValidarLargoMaximoPorCampo']['Nombre'] ?? 0) === 255,
                'dataCheck() no limita el nombre a 3..255 caracteres');
            $this->addCase($results, 'datacheck_censura', $DataCheck['ValidarPalabrasCensuradas'] === 'Nombre',
                'dataCheck() no filtra palabras censuradas en el nombre');

            /*====================================*/
            // El chequeo real (CheckData) acepta un POST valido y rechaza uno invalido
            /*====================================*/
            $checkData = new CheckData();
            $checkOk   = $checkData->checkingData($DataCheck);
            $this->addCase($results, 'checkdata_acepta_valido', ($checkOk['status'] ?? false) === true,
                'CheckData rechazo un POST valido de estado');
            $malo = $this->callDataCheck($reflector, $Controller, ['Nombre' => 'ab', 'idColor' => 1, 'idPrioridad' => 5, 'idCierre' => 1]);
            $this->addCase($results, 'checkdata_rechaza_nombre_corto', ($malo['status'] ?? true) === false,
                'CheckData acepto un nombre de 2 caracteres (minimo 3)');
            $malo = $this->callDataCheck($reflector, $Controller, ['Nombre' => 'Tablero Prueba', 'idColor' => 'x', 'idPrioridad' => 5, 'idCierre' => 1]);
            $this->addCase($results, 'checkdata_rechaza_color_no_numerico', ($malo['status'] ?? true) === false,
                'CheckData acepto un idColor no numerico');
            $malo = $this->callDataCheck($reflector, $Controller, ['Nombre' => str_repeat('a', 256), 'idColor' => 1, 'idPrioridad' => 5, 'idCierre' => 1]);
            $this->addCase($results, 'checkdata_rechaza_nombre_largo', ($malo['status'] ?? true) === false,
                'CheckData acepto un nombre de 256 caracteres (maximo 255)');
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_es_arreglo', false,
                'no se pudo ejecutar dataCheck() ('.$e->getMessage().')');
        }

        /*====================================*/
        // Cifrado de identificadores: ida y vuelta estable y rechazo de basura
        /*====================================*/
        try {
            $Codification = new FunctionsSecurityCodification();
            $cifrado      = $Codification->encryptDecrypt('encrypt', '7');
            $this->addCase($results, 'cifrado_genera_dato',
                ($cifrado['success'] ?? false) === true && !empty($cifrado['data']),
                'encryptDecrypt(encrypt) no produjo un valor cifrado');

            $descifrado = $Codification->encryptDecrypt('decrypt', (string)($cifrado['data'] ?? ''));
            $this->addCase($results, 'cifrado_ida_y_vuelta',
                ($descifrado['success'] ?? false) === true && (string)($descifrado['data'] ?? '') === '7',
                'el cifrado no devuelve el valor original');

            $basura = $Codification->encryptDecrypt('decrypt', 'valor-no-cifrado');
            $this->addCase($results, 'cifrado_rechaza_basura',
                ($basura['success'] ?? false) === false || !$this->isValidDecrypted($basura, 'id'),
                'un ID no cifrado se acepto como valido');
        } catch (Throwable $e) {
            $this->addCase($results, 'cifrado_ida_y_vuelta', false,
                'no se pudo ejecutar el cifrado ('.$e->getMessage().')');
        }

        /*====================================*/
        // isValidDecrypted: caminos de exito y rechazo
        /*====================================*/
        $this->addCase($results, 'id_valido_ok', $this->isValidDecrypted(['success' => true, 'data' => '7'], 'id') === true,
            'un ID numerico positivo debe ser valido');
        $this->addCase($results, 'id_valido_descifro_falla', $this->isValidDecrypted(['success' => false, 'data' => '7'], 'id') === false,
            'un descifrado fallido debe rechazarse');
        $this->addCase($results, 'id_valido_texto', $this->isValidDecrypted(['success' => true, 'data' => 'abc'], 'id') === false,
            'un ID no numerico debe rechazarse para tipo id');
        $this->addCase($results, 'id_valido_cero', $this->isValidDecrypted(['success' => true, 'data' => '0'], 'id') === false,
            'el ID 0 debe rechazarse para tipo id');
        $this->addCase($results, 'id_valido_negativo', $this->isValidDecrypted(['success' => true, 'data' => '-5'], 'id') === false,
            'un ID negativo debe rechazarse para tipo id');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /*******************************************************************/
    // Ejecuta el dataCheck() privado con un POST dado y pasa el resultado por CheckData
    /*******************************************************************/
    private function callDataCheck(ReflectionMethod $reflector, $controller, array $post): array{
        $checkData = new CheckData();
        return $checkData->checkingData($reflector->invoke($controller, $post));
    }

    /******************************************************************************/
    /*                                 SEGURIDAD                                  */
    /******************************************************************************/
    /**
     * Revisa estaticamente las vistas y el controlador de estados: rutas relativas, rutas
     * absolutas, acceso a sesion, SQL embebido y consultas preparadas.
     *
     * @return array Resultado por caso
     */
    public function seguridadVistasTest(){

        $results = [];

        /*====================================*/
        // Vistas involucradas
        /*====================================*/
        $arrVistas = glob($this->rutaModulo('../views/'.self::CONTROLADOR.'-*.php')) ?: [];
        $this->addCase($results, 'hay_vistas', count($arrVistas) === 2,
            'se esperaban 2 vistas de kanbanEstados y se encontraron '.count($arrVistas));

        foreach ($arrVistas as $ruta) {
            $nombre = basename($ruta);
            $codigo = $this->codigoFuente($ruta);

            $this->addCase($results, 'vista_sin_retroceso_'.$nombre, !preg_match('#(?:\.\./|\.\.\\\\)#', $codigo),
                $nombre.' construye rutas con retroceso');
            $this->addCase($results, 'vista_sin_ruta_absoluta_'.$nombre,
                !preg_match('#(?:/var/www|/home/|[A-Za-z]:\\\\)#', $codigo),
                $nombre.' fija una ruta absoluta del servidor');
            $this->addCase($results, 'vista_sin_sesion_'.$nombre, !str_contains($codigo, '$_SESSION'),
                $nombre.' accede a la sesion de forma directa');
            $this->addCase($results, 'vista_sin_sql_'.$nombre,
                !preg_match('/\b(SELECT|INSERT|UPDATE|DELETE)\s+\w+\s+FROM\b/i', $codigo),
                $nombre.' contiene una consulta SQL embebida');
            $this->addCase($results, 'vista_valida_formulario_'.$nombre, str_contains($codigo, 'validator.checkAll(this)'),
                $nombre.' no valida el formulario en el cliente antes de enviarlo');
            $this->addCase($results, 'vista_evita_doble_envio_'.$nombre, str_contains($codigo, 'ejecutandoForm.valor'),
                $nombre.' no evita el doble envio del formulario');
        }

        /*====================================*/
        // Controlador: SQL preparado y sin concatenacion de entrada
        /*====================================*/
        $controlador = $this->fuenteControlador();
        $this->addCase($results, 'controlador_sin_concat_sql', !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $controlador),
            'el controlador concatena superglobales en una cadena SQL');
        $this->addCase($results, 'controlador_parametros_preparados', preg_match_all("/'params'\s*=>/", $controlador) === 3,
            'las 3 consultas de GetID (estado y 2 catalogos) deben declarar la clave params');
        $this->addCase($results, 'controlador_id_como_parametro', substr_count($controlador, "'params'  => [\$KanbanEstadoID['data']]") === 1,
            'la consulta del estado debe recibir el ID descifrado como parametro preparado');
        $this->addCase($results, 'controlador_post_solo_en_escritura', substr_count($controlador, '$_POST') === 4,
            'se esperaban 4 usos de $_POST (dataCheck y Post de Insert y Update)');
        $this->addCase($results, 'controlador_sin_get_ni_request', !str_contains($controlador, '$_GET') && !str_contains($controlador, '$_REQUEST'),
            'el controlador lee $_GET o $_REQUEST directamente');

        /************************************/
        // Retorno los datos
        return $results;

    }

}
