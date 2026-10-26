<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class kanbanTablerosTesting
 *
 * Pruebas del modulo `kanban` - controlador `kanbanTableros` (CRUD de los tableros kanban:
 * `listAll`, `UpdateList`, `View`, `GetID`, `Insert`, `Update`, `Delete`).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. Los textos de los casos
 *    correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Alcance: pruebas NO destructivas. `Insert()`, `Update()` y `Delete()` escriben en base de
 * datos y terminan en `Response::*()` (que ejecuta `exit`); `listAll`, `UpdateList`, `View` y
 * `GetID` consultan la BD y terminan en `showVista()`. Ninguno se invoca: se verifican por
 * reflexion y lectura del fuente, y las vistas se renderizan con datos sinteticos.
 *
 * Particularidad: `kanbanTableros` administra la misma tabla `kanban_estados` que el
 * sub-recurso `kanbanEstados`, pero con permiso, vistas y rutas propias. La vista `-List`
 * incluye `-formSearch`, `-UpdateList` y `-formNew` con `require_once`: como un `include`
 * previo de uno de esos archivos vuelve ese `require_once` un no-op, el contenido de las
 * sub-vistas se verifica por fuente y se renderiza cada una directamente.
 *
 * @see skills/testing/SKILL.md
 */
class kanbanTablerosTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR      = 'kanbanTableros';
    private const INSTALADOR       = 'kanbanTareasInstaller';
    private const RUTA_WEB         = 'mantencionSistema/kanban/tableros';
    private const RUTA_WEB_LISTADO = 'mantencionSistema/kanban/tableros/listAll';
    private const RUTA_WEB_SEARCH  = 'mantencionSistema/kanban/tableros/search';
    private const RUTA_WEB_UPDLIST = 'mantencionSistema/kanban/tableros/updateList';
    private const RUTA_WEB_VIEW    = 'mantencionSistema/kanban/tableros/view/@id';
    private const RUTA_WEB_GETID   = 'mantencionSistema/kanban/tableros/getID/@id';
    private const RUTA_WEB_UPDATE  = 'mantencionSistema/kanban/tableros/update';
    // Niveles de permiso del catalogo (1=ver, 2=editar, 3=crear, 4=borrar)
    private const NIVELES          = [1, 2, 3, 4];

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

    /*******************************************************************/
    // Ejecuta el dataCheck() privado con un POST dado y pasa el resultado por CheckData
    /*******************************************************************/
    private function callDataCheck(ReflectionMethod $reflector, $controller, array $post): array{
        $checkData = new CheckData();
        return $checkData->checkingData($reflector->invoke($controller, $post));
    }

    /*******************************************************************/
    // Datos sinteticos usados por las vistas del controlador
    /*******************************************************************/
    private function datosSinteticos(int $nivel, int $subtitulo = 1): array{
        return [
            'PageTitle'        => 'Tableros Kanban',
            'PageDescription'  => 'Tableros Kanban.',
            'PageAuthor'       => 'Pruebas',
            'PageKeywords'     => 'Pruebas',
            'TableTitle'       => 'Listado Tableros Kanban',
            'UserData'         => [
                'MainPathUrl'          => '/admin/public/upload/',
                'sistemaModalSubtitle' => $subtitulo,
                'sistemaModalCloseBTN' => 2,
                'UserType'             => 2,
            ],
            'UserAccess'       => ['LevelAccess' => $nivel, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_FormInputs'   => new UIFormInputs(),
            'Fnc_Codification' => new FunctionsSecurityCodification(),
            'Fnc_WidgetsCommon'=> new UIWidgetsCommon(),
            'Fnc_WidgetsViews'  => new UIWidgetsViews(),
            'arrColores'       => [['ID' => 1, 'Nombre' => 'Color Prueba'], ['ID' => 2, 'Nombre' => 'Color Dos']],
            'arrCierre'        => [['ID' => 1, 'Nombre' => 'Cierre Prueba'], ['ID' => 2, 'Nombre' => 'Cierre Dos']],
            'arrList'          => [[
                'idKanbanEstado' => 7,
                'idPrioridad'    => 5,
                'Nombre'         => "Tablero D'Prueba",
                'Color'          => 'primary',
                'Cierre'         => 'Cierre Prueba',
                'CierreColor'    => 'success',
            ]],
            'rowData'          => [
                'idKanbanEstado' => 7,
                'Nombre'         => 'Tablero Sintetico',
                'idColor'        => 2,
                'idPrioridad'    => 5,
                'idCierre'       => 2,
                'Color'          => 'primary',
                'Cierre'         => 'Cierre Prueba',
            ],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica los archivos del controlador `kanbanTableros` y la convencion
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
            'controlador'       => $this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'),
            'instalador'        => $this->rutaModulo('../installer/'.self::INSTALADOR.'.php'),
            'vista_list'        => $this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php'),
            'vista_update_list' => $this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'),
            'vista_view'        => $this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'),
            'vista_form_new'    => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formNew.php'),
            'vista_form_edit'   => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formEdit.php'),
            'vista_form_search' => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formSearch.php'),
            'testing'           => $this->rutaModulo('kanbanTablerosTesting.php'),
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
        $this->addCase($results, 'clase_testing_cargada', class_exists('kanbanTablerosTesting'),
            'la clase kanbanTablerosTesting no esta disponible');
        $padre = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padre === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padre.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('kanbanTablerosTesting') === 'ControllerBase',
            'el testing no extiende ControllerBase');

        /*====================================*/
        // Constructor del controlador
        /*====================================*/
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'constructor_conexion', str_contains($codigo, 'Database::getSQLConnection(ConfigDataBase::MySQL_1)'),
            'el constructor no obtiene la conexion MySQL_1');
        $this->addCase($results, 'constructor_padre', str_contains($codigo, 'parent::__construct($DB_conn_1, $queryBuilder, $checkData)'),
            'el constructor no delega en ControllerBase');
        $this->addCase($results, 'constructor_nombre', str_contains($codigo, "\$this->controllerName = '".self::CONTROLADOR."'"),
            'controllerName no coincide con '.self::CONTROLADOR);

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                            CONTROLADOR: VISTAS                             */
    /******************************************************************************/
    /**
     * Verifica el contrato de `listAll`, `UpdateList`, `View` y `GetID`: presentacion,
     * manejo de fallos, ID cifrado, busqueda por nombre y consultas de catalogo.
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Firma de cada metodo de vista: listAll/UpdateList($f3), View/GetID($f3, $params)
        /*====================================*/
        foreach (['listAll', 'UpdateList'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 1, 1);
        }
        foreach (['View', 'GetID'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 2, 2);
        }

        /*====================================*/
        // Contrato de presentacion
        /*====================================*/
        foreach (['List' => 'listAll', 'UpdateList' => 'UpdateList', 'View' => 'View', 'formEdit' => 'GetID'] as $vista => $metodo) {
            $this->addCase($results, 'vista_'.$metodo, str_contains($codigo, "\$this->controllerName.'-".$vista.".php'"),
                $metodo.'() no instancia la vista -'.$vista);
        }
        $this->addCase($results, 'tipoview_listAll', substr_count($codigo, 'showVista(1,') === 1,
            'listAll() debe usar showVista(1) (pagina completa)');
        $this->addCase($results, 'tipoview_fragmentos', substr_count($codigo, 'showVista(2,') === 3,
            'UpdateList(), View() y GetID() deben usar showVista(2) (fragmentos)');
        $this->addCase($results, 'rutavista_resuelta', substr_count($codigo, 'returnRutaVista(__DIR__') === 4,
            'las cuatro vistas deben resolver su ruta con returnRutaVista()');
        $this->addCase($results, 'usuario_en_vistas', substr_count($codigo, 'getUserData($f3)') === 4,
            'las vistas no obtienen los datos del usuario');
        $this->addCase($results, 'nivel_en_vistas', substr_count($codigo, 'getArrLevel($f3, $this->controllerName)') === 4,
            'las vistas no resuelven el nivel de acceso');
        $this->addCase($results, 'listall_maneja_fallo',
            substr_count($codigo, 'showError(1, $f3, $result)') === 1
            && str_contains($codigo, 'mergeResponses([$arrList,$arrColores,$arrCierre])')
            && str_contains($codigo, "if(\$arrList['status'] && \$arrColores['status'] && \$arrCierre['status'])"),
            'listAll() debe comprobar el status de sus tres consultas y usar showError(1)/mergeResponses()');
        $this->addCase($results, 'fragmentos_manejan_fallo',
            substr_count($codigo, 'showError(2, $f3, $result)') === 3 && substr_count($codigo, 'mergeResponses(') === 4,
            'UpdateList(), View() y GetID() deben manejar el fallo de consulta con showError(2)/mergeResponses()');
        $this->addCase($results, 'listado_titulo', str_contains($codigo, "'PageTitle'") && substr_count($codigo, "'TableTitle'") === 2,
            'listAll() y UpdateList() deben asignar el titulo de la tabla');

        /*====================================*/
        // Listado: tablero con su color y opcion de cierre, ordenado por prioridad
        /*====================================*/
        $this->addCase($results, 'listado_joins',
            substr_count($codigo, 'LEFT JOIN core_estados_colores   ON core_estados_colores.idColor = kanban_estados.idColor') === 3
            && substr_count($codigo, 'LEFT JOIN core_opciones          ON core_opciones.idOpciones     = kanban_estados.idCierre') === 3,
            'listAll(), UpdateList() y View() deben unir el color y la opcion de cierre');
        $this->addCase($results, 'listado_orden_prioridad', substr_count($codigo, "'order'   => 'kanban_estados.idPrioridad ASC'") === 2,
            'listAll() y UpdateList() deben ordenar por prioridad');
        $this->addCase($results, 'listado_catalogos',
            str_contains($codigo, "'table'   => 'core_estados_colores'") && str_contains($codigo, "'table'   => 'core_opciones'")
            && substr_count($codigo, "'order'   => 'Nombre ASC'") === 4,
            'listAll() y GetID() deben cargar los catalogos de colores y cierre ordenados por Nombre');
        $this->addCase($results, 'consultas_acotadas', substr_count($codigo, 'ConfigAPP::APP["N_MaxItems"]') === 6,
            'las 6 consultas de listado deben acotar el numero de filas');

        /*====================================*/
        // Busqueda: columnas, validacion de fechas y WHERE preparado
        /*====================================*/
        $this->addCase($results, 'busqueda_campo_relativo', str_contains($codigo, "\$WhereData_string  = 'Nombre';"),
            'UpdateList() no filtra por Nombre');
        $this->addCase($results, 'busqueda_sin_exactos_ni_rango',
            str_contains($codigo, "\$WhereData_int     = '';") && str_contains($codigo, "\$WhereData_between = '';"),
            'UpdateList() solo debe buscar por Nombre (sin campos exactos ni rangos)');
        $this->addCase($results, 'busqueda_valida_fechas', str_contains($codigo, 'searchValidateDates(') && str_contains($codigo, 'Response::error($RespDataBetween, 500)'),
            'UpdateList() no valida las fechas con searchValidateDates() ni responde 500');
        $this->addCase($results, 'busqueda_where_preparado', substr_count($codigo, '$this->searchWhere(') === 3,
            'UpdateList() no construye el WHERE con los tres tipos de searchWhere()');
        $this->addCase($results, 'busqueda_tabla', substr_count($codigo, "'kanban_estados', ") === 3,
            'los tres searchWhere() deben operar sobre kanban_estados');
        $this->addCase($results, 'busqueda_params', str_contains($codigo, "'params'  => \$whereParams"),
            'UpdateList() no pasa los parametros preparados del WHERE');

        /*====================================*/
        // ID cifrado en View y GetID
        /*====================================*/
        $this->addCase($results, 'id_descifrado', substr_count($codigo, "encryptDecrypt('decrypt', \$params['id'])") === 2,
            'View() y GetID() deben descifrar el ID de la ruta');
        $this->addCase($results, 'id_validado', substr_count($codigo, "isValidDecrypted(\$KanbanEstadoID, 'id')") === 2,
            'View() y GetID() deben validar el ID descifrado');
        $this->addCase($results, 'id_invalido_400', substr_count($codigo, "Response::error('Registro inválido', 400)") === 2,
            'un ID invalido debe responder 400 en View() y GetID()');
        $this->addCase($results, 'detalle_por_id',
            str_contains($codigo, "'where'   => 'kanban_estados.idKanbanEstado = ?'") && str_contains($codigo, "'where'   => 'idKanbanEstado = ?'"),
            'View() y GetID() deben filtrar el estado por idKanbanEstado');

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
        $this->addCase($results, 'tabla_estados', preg_match_all("/'table'\s*=>\s*'kanban_estados'/", $codigo) === 7,
            'se esperaban 7 usos de kanban_estados (4 lecturas y Insert/Update/Delete)');
        $this->addCase($results, 'sin_codificacion', substr_count($codigo, "'encode'    => ''") === 2,
            'los tableros no tienen campos codificados');
        $this->addCase($results, 'nombre_unico', substr_count($codigo, "'unique'    => 'Nombre'") === 2,
            'Insert() y Update() deben exigir Nombre unico');

        /*====================================*/
        // Insert
        /*====================================*/
        $this->addCase($results, 'insert_campos', str_contains($codigo, "'data'      => 'Nombre,idColor,idPrioridad,idCierre'"),
            'Insert() no declara los campos esperados del tablero');
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
            'Update() no declara los campos esperados del tablero');
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
            'Delete() no declara que el tablero no maneja archivos');
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
     * Verifica que las 8 rutas de `kanbanTableros` publicadas por el instalador apunten a
     * metodos reales con el verbo y el nivel esperados, y el permiso CRUD del instalador.
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
            'listado'     => ['1 '.self::RUTA_WEB_LISTADO, 'kanbanTableros->listAll',    1],
            'busqueda'    => ['2 '.self::RUTA_WEB_SEARCH,  'kanbanTableros->UpdateList', 1],
            'actualizar'  => ['1 '.self::RUTA_WEB_UPDLIST, 'kanbanTableros->UpdateList', 2],
            'ver'         => ['1 '.self::RUTA_WEB_VIEW,    'kanbanTableros->View',       1],
            'getid'       => ['1 '.self::RUTA_WEB_GETID,   'kanbanTableros->GetID',      2],
            'crear'       => ['2 '.self::RUTA_WEB,         'kanbanTableros->Insert',     3],
            'editar'      => ['2 '.self::RUTA_WEB_UPDATE,  'kanbanTableros->Update',     2],
            'borrar'      => ['3 '.self::RUTA_WEB,         'kanbanTableros->Delete',     4],
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
        // Conjunto completo de rutas propias, en el permiso propio (tipo 3)
        /*====================================*/
        $propias = array_filter($rutas, static function ($r) { return $r[2] === 'kanbanTableros'; });
        $this->addCase($results, 'rutas_propias_completas', count($propias) === 8,
            'se esperaban 8 rutas de kanbanTableros y hay '.count($propias));
        $this->addCase($results, 'rutas_en_su_permiso', $this->rutasDeTipo(3, 'kanbanTableros') === 8,
            'las 8 rutas de kanbanTableros deben publicarse en el tercer permiso del instalador');

        /*====================================*/
        // Permiso del CRUD en el instalador y desinstalacion
        /*====================================*/
        $instalador = $this->codigoFuente($this->rutaModulo('../installer/'.self::INSTALADOR.'.php'));
        $this->addCase($results, 'instalador_permiso_tableros',
            (bool)preg_match("/'RutaWeb'\s*=>\s*'".preg_quote(self::RUTA_WEB, '/')."'/", $instalador)
            && (bool)preg_match("/'RutaController'\s*=>\s*'kanbanTableros'/", $instalador),
            'el instalador no declara el permiso de tableros con su ruta y controlador');
        $this->addCase($results, 'instalador_tableros_categoria_administracion',
            (bool)preg_match("/'idPermisosCat'\s*=>\s*'1',\s*\/\/ Administraci\S+\s*\n\s*'idEstado'[^\n]*\n\s*'idTipo'\s*=>\s*'1',\s*\/\/ Crud Normal\s*\n\s*'Nombre'\s*=>\s*'Proyectos - Tableros'/", $instalador),
            'el permiso de tableros debe ser un Crud Normal de la categoria Administracion');
        $this->addCase($results, 'instalador_tableros_nivel_completo',
            (bool)preg_match("/'idLevelLimit'\s*=>\s*'4',[^\n]*\n\s*'RutaWeb'\s*=>\s*'".preg_quote(self::RUTA_WEB, '/')."'/", $instalador),
            'el permiso de tableros debe tener nivel limite 4 (ver/editar/crear/borrar)');
        $this->addCase($results, 'instalador_desinstala_tableros', str_contains($instalador, '"kanbanTableros"'),
            'RutaController() no lista kanbanTableros y dejaria rutas huerfanas al desinstalar');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                PERMISOS                                    */
    /******************************************************************************/
    /**
     * Verifica la sesion, el nivel de acceso de tableros y el registro de sus rutas en el
     * router de F3 (solo si el usuario de pruebas las tiene).
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
        // Lectura de la sesion desde el controlador
        /*====================================*/
        try {
            $this->addCase($results, 'get_user_data_arreglo', is_array($this->getUserData($f3)),
                'getUserData() no devolvio un arreglo');
            $arrLevel = $this->getArrLevel($f3, self::CONTROLADOR);
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
                if (isset($permiso['RutaWeb']) && $permiso['RutaWeb'] === self::RUTA_WEB_LISTADO) {
                    $rutaOk = true;
                    break;
                }
            }
            $this->addCase($results, 'ruta_registrada', $rutaOk || $nivel === null,
                'el usuario tiene nivel '.var_export($nivel, true).' pero la ruta '.self::RUTA_WEB_LISTADO.' no esta en sus permisos');
        }

        $arrRoutes = $f3->get('ROUTES');
        $this->addCase($results, 'router_disponible', is_array($arrRoutes), 'F3 no expuso la variable ROUTES');
        $esquema = [
            'ruta_listado_router'    => ['/'.self::RUTA_WEB_LISTADO, 'GET',    self::CONTROLADOR.'->listAll'],
            'ruta_busqueda_router'   => ['/'.self::RUTA_WEB_SEARCH,  'POST',   self::CONTROLADOR.'->UpdateList'],
            'ruta_actualizar_router' => ['/'.self::RUTA_WEB_UPDLIST, 'GET',    self::CONTROLADOR.'->UpdateList'],
            'ruta_ver_router'        => ['/'.self::RUTA_WEB_VIEW,    'GET',    self::CONTROLADOR.'->View'],
            'ruta_getid_router'      => ['/'.self::RUTA_WEB_GETID,   'GET',    self::CONTROLADOR.'->GetID'],
            'ruta_insert_router'     => ['/'.self::RUTA_WEB,         'POST',   self::CONTROLADOR.'->Insert'],
            'ruta_update_router'     => ['/'.self::RUTA_WEB_UPDATE,  'POST',   self::CONTROLADOR.'->Update'],
            'ruta_delete_router'     => ['/'.self::RUTA_WEB,         'DELETE', self::CONTROLADOR.'->Delete'],
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
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /**
     * Renderiza `kanbanTableros-List.php`, `-formSearch.php` y `-UpdateList.php` con datos
     * sinteticos: acciones de la pagina por nivel, formulario de busqueda, endpoints, tabla
     * con la matriz de niveles 1..4, ID cifrado, nombre escapado y tabla vacia.
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function vistaListadoTest($f3){

        $results = [];
        $BASE    = (string)$f3->get('BASE');
        if (trim($BASE) === '') {
            $BASE = '/admin';
        }
        $rutaList = $this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php');
        $codList  = $this->codigoFuente($rutaList);

        /*====================================*/
        // Pagina: acciones segun el nivel y endpoints del JavaScript
        /*====================================*/
        $html = $this->renderizarVista($rutaList, $this->datosSinteticos(4), $BASE, $f3);
        $this->addCase($results, 'listado_render', $html !== '', 'la vista List no produjo HTML');
        $this->addCase($results, 'listado_titulo', str_contains($html, 'Listado Tableros Kanban'),
            'la pagina no muestra el titulo de la tabla');
        $this->addCase($results, 'listado_contenedor_tabla', str_contains($html, 'id="listTableData"'),
            'falta el contenedor donde se refresca la tabla');
        $this->addCase($results, 'listado_filtros', str_contains($html, 'data-bs-target="#formSearch"') && str_contains($html, 'deleteFilter()'),
            'faltan los botones Filtrar y Quitar Filtro');
        $this->addCase($results, 'listado_endpoints_js',
            str_contains($html, $BASE.'/'.self::RUTA_WEB.'/view/') && str_contains($html, $BASE.'/'.self::RUTA_WEB.'/getID/')
            && str_contains($html, $BASE.'/'.self::RUTA_WEB.'/updateList'),
            'el JavaScript no apunta a view, getID y updateList');
        $this->addCase($results, 'listado_borrado_delete',
            str_contains($html, "let Metodo      = 'DELETE'") && str_contains($html, '{"idKanbanEstado": ID}'),
            'el borrado no usa DELETE con idKanbanEstado');
        $this->addCase($results, 'listado_borrado_confirma', str_contains($html, 'Swal.fire') && str_contains($html, 'isConfirmed'),
            'el borrado no pide confirmacion al usuario');
        foreach (self::NIVELES as $nivel) {
            $pagina = $this->renderizarVista($rutaList, $this->datosSinteticos($nivel), $BASE, $f3);
            $this->addCase($results, 'listado_crear_nivel'.$nivel,
                str_contains($pagina, 'data-bs-target="#newFormModal"') === ($nivel >= 3),
                'el boton Crear Nuevo debe mostrarse solo con nivel >= 3 (nivel '.$nivel.')');
        }

        /*====================================*/
        // Sub-vistas que incluye la pagina (por fuente: el require_once no se repite)
        /*====================================*/
        foreach (['formSearch', 'UpdateList', 'formNew'] as $sub) {
            $this->addCase($results, 'listado_incluye_'.$sub, str_contains($codList, "require_once('kanbanTableros-".$sub.".php')"),
                'la pagina no incluye kanbanTableros-'.$sub.'.php');
        }

        /*====================================*/
        // Formulario de busqueda
        /*====================================*/
        $buscar = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-formSearch.php'),
            $this->datosSinteticos(1), $BASE, $f3);
        $this->addCase($results, 'busqueda_render', $buscar !== '', 'la vista formSearch no produjo HTML');
        $this->addCase($results, 'busqueda_form', str_contains($buscar, 'id="FormSearchData"') && str_contains($buscar, 'id="formSearch"'),
            'no se dibuja el formulario de busqueda colapsable');
        $this->addCase($results, 'busqueda_input_nombre', str_contains($buscar, 'id="Search_Nombre"'),
            'el formulario no declara el campo Nombre con prefijo Search_');
        $this->addCase($results, 'busqueda_endpoint',
            str_contains($buscar, $BASE.'/'.self::RUTA_WEB.'/search') && str_contains($buscar, "let Metodo      = 'POST'"),
            'el formulario no apunta a /search con POST');
        $this->addCase($results, 'busqueda_botones', str_contains($buscar, 'Quitar Filtro') && str_contains($buscar, 'Filtrar'),
            'el formulario no ofrece Filtrar y Quitar Filtro');

        /*====================================*/
        // Tabla de resultados: matriz de niveles 1..4 (ver >=1, editar >=2, borrar >=4)
        /*====================================*/
        $rutaTabla = $this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php');
        foreach (self::NIVELES as $nivel) {
            $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos($nivel), $BASE, $f3);
            $this->addCase($results, 'tabla_render_nivel'.$nivel, $tabla !== '',
                'UpdateList no produjo HTML con nivel '.$nivel);
            $this->addCase($results, 'tabla_ver_nivel'.$nivel, str_contains($tabla, 'listTableDataView('),
                'la accion Ver debe estar disponible con nivel '.$nivel);
            $this->addCase($results, 'tabla_editar_nivel'.$nivel, str_contains($tabla, 'listTableDataEdit(') === ($nivel >= 2),
                'la accion Editar debe mostrarse solo con nivel >= 2 (nivel '.$nivel.')');
            $this->addCase($results, 'tabla_borrar_nivel'.$nivel, str_contains($tabla, 'listTableDataDel(') === ($nivel >= 4),
                'la accion Borrar debe mostrarse solo con nivel >= 4 (nivel '.$nivel.')');
            $this->addCase($results, 'tabla_datos_nivel'.$nivel,
                str_contains($tabla, 'Cierre Prueba') && str_contains($tabla, 'badge-sp1-primary') && str_contains($tabla, 'badge-sp1-success'),
                'la tabla no pinta el tablero sintetico con nivel '.$nivel);
        }
        $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos(4), $BASE, $f3);
        $this->addCase($results, 'tabla_columnas',
            str_contains($tabla, '>Nombre Tablero<') && str_contains($tabla, '>Color Icono<') && str_contains($tabla, '>Prioridad<')
            && str_contains($tabla, 'Permite Cierre de la Tarea'),
            'la tabla no declara las columnas del tablero');
        $this->addCase($results, 'tabla_valores', str_contains($tabla, '<td>5</td>'),
            'la tabla no pinta la prioridad del tablero');
        $this->addCase($results, 'tabla_encabezados_coinciden_con_valores',
            strpos($tabla, '>Prioridad<') < strpos($tabla, '>Color Icono<') && strpos($tabla, '<td>5</td>') < strpos($tabla, 'badge-sp1 badge-sp1-primary'),
            'la columna Prioridad debe mostrar el numero de prioridad y la columna Color Icono el color (antes estaban cruzadas)');
        $this->addCase($results, 'tabla_id_cifrado', !str_contains($tabla, "listTableDataView('7')") && !str_contains($tabla, "listTableDataEdit('7')")
            && !str_contains($tabla, "listTableDataDel('7'"),
            'la tabla expone idKanbanEstado sin cifrar');
        $this->addCase($results, 'tabla_nombre_escapado_para_js', str_contains($tabla, "Tablero D\\'Prueba"),
            'el nombre con comilla simple debe escaparse (addslashes) en el onclick de borrar');

        /*====================================*/
        // Tabla vacia
        /*====================================*/
        $vacio = $this->datosSinteticos(4);
        $vacio['arrList'] = [];
        $tablaVacia = $this->renderizarVista($rutaTabla, $vacio, $BASE, $f3);
        $this->addCase($results, 'tabla_vacia', $tablaVacia !== '' && str_contains($tablaVacia, '<tbody>') && !str_contains($tablaVacia, 'listTableDataView('),
            'la tabla sin resultados pinta filas o no se dibuja');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              VISTAS: FORMULARIOS                           */
    /******************************************************************************/
    /**
     * Renderiza `kanbanTableros-formNew.php`, `-formEdit.php` y `-View.php` con datos
     * sinteticos: modal, formulario, inputs con su prefijo, catalogos, valores
     * preseleccionados, campo oculto, endpoints con su verbo y variantes del modal.
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
        $rutaView = $this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php');

        /*====================================*/
        // Formulario de creacion (modal)
        /*====================================*/
        $html = $this->renderizarVista($rutaNew, $this->datosSinteticos(3), $BASE, $f3);
        $this->addCase($results, 'new_render', $html !== '', 'la vista formNew no produjo HTML');
        $this->addCase($results, 'new_modal', str_contains($html, 'id="newFormModal"') && str_contains($html, 'modal-dialog'),
            'falta el modal de creacion');
        $this->addCase($results, 'new_form', str_contains($html, 'id="FormNewData"'),
            'no se dibuja el formulario de creacion');
        $this->addCase($results, 'new_inputs',
            str_contains($html, 'id="NewEstado_Nombre"') && str_contains($html, 'id="NewEstado_idColor"')
            && str_contains($html, 'id="NewEstado_idPrioridad"') && str_contains($html, 'id="NewEstado_idCierre"'),
            'el formulario no declara Nombre, idColor, idPrioridad e idCierre con prefijo NewEstado_');
        $this->addCase($results, 'new_catalogos', str_contains($html, 'Color Prueba') && str_contains($html, 'Cierre Prueba'),
            'el formulario no ofrece los catalogos de colores y cierre');
        $this->addCase($results, 'new_prioridad_1_a_25',
            preg_match('/<option[^>]*value="1"/', $html) === 1 && preg_match('/<option[^>]*value="25"/', $html) === 1
            && preg_match('/<option[^>]*value="26"/', $html) === 0,
            'la prioridad debe ofrecer los valores 1 a 25');
        $this->addCase($results, 'new_endpoint_creacion',
            str_contains($html, "let Direccion   = '".$BASE.'/'.self::RUTA_WEB."'") && str_contains($html, "let Metodo      = 'POST'"),
            'el formulario no envia por POST a '.self::RUTA_WEB);
        $this->addCase($results, 'new_actualiza_lista',
            str_contains($html, $BASE.'/'.self::RUTA_WEB.'/updateList') && str_contains($html, "closeModal:'#newFormModal'") && str_contains($html, "ClearForm:'FormNewData'"),
            'el formulario no refresca el listado, cierra el modal ni limpia el formulario');
        $this->addCase($results, 'new_titulo_sin_subtitulo', str_contains($html, '</i> Crear Nuevo') && !str_contains($html, 'modal-subtitle'),
            'con sistemaModalSubtitle = 1 no debe dibujarse el subtitulo');
        $html2 = $this->renderizarVista($rutaNew, $this->datosSinteticos(3, 2), $BASE, $f3);
        $this->addCase($results, 'new_subtitulo_modal', str_contains($html2, 'modal-subtitle') && str_contains($html2, 'Permite crear un nuevo elemento'),
            'con sistemaModalSubtitle = 2 debe dibujarse el subtitulo');

        /*====================================*/
        // Formulario de edicion (fragmento del modal)
        /*====================================*/
        $html = $this->renderizarVista($rutaEdit, $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'edit_render', $html !== '', 'la vista formEdit no produjo HTML');
        $this->addCase($results, 'edit_form', str_contains($html, 'id="FormEditData"'),
            'no se dibuja el formulario de edicion');
        $this->addCase($results, 'edit_inputs',
            str_contains($html, 'id="EditEstado_Nombre"') && str_contains($html, 'id="EditEstado_idColor"')
            && str_contains($html, 'id="EditEstado_idPrioridad"') && str_contains($html, 'id="EditEstado_idCierre"'),
            'el formulario no declara Nombre, idColor, idPrioridad e idCierre con prefijo EditEstado_');
        $this->addCase($results, 'edit_valor_nombre', str_contains($html, 'Tablero Sintetico'),
            'el formulario no precarga el nombre del tablero');
        $this->addCase($results, 'edit_valores_seleccionados',
            preg_match('/<option[^>]*value="2"[^>]*selected/', $html) === 1 && preg_match('/<option[^>]*value="5"[^>]*selected/', $html) === 1,
            'el formulario no preselecciona el color, la prioridad y el cierre del tablero');
        $this->addCase($results, 'edit_id_oculto', preg_match('/<input[^>]*name="idKanbanEstado"[^>]*value="7"|<input[^>]*value="7"[^>]*name="idKanbanEstado"/', $html) === 1,
            'el formulario no incluye el idKanbanEstado en un campo oculto');
        $this->addCase($results, 'edit_endpoint_update',
            str_contains($html, "let Direccion   = '".$BASE.'/'.self::RUTA_WEB."/update'") && str_contains($html, "let Metodo      = 'POST'"),
            'el formulario no envia por POST a '.self::RUTA_WEB_UPDATE);
        $this->addCase($results, 'edit_actualiza_lista',
            str_contains($html, $BASE.'/'.self::RUTA_WEB.'/updateList') && str_contains($html, "closeModal:'#viewModal-lg'"),
            'el formulario no refresca el listado ni cierra el modal que lo contiene (viewModal-lg)');
        $html2 = $this->renderizarVista($rutaEdit, $this->datosSinteticos(2, 2), $BASE, $f3);
        $this->addCase($results, 'edit_subtitulo_modal', str_contains($html2, 'modal-subtitle') && str_contains($html2, 'Permite editar un elemento existente'),
            'con sistemaModalSubtitle = 2 debe dibujarse el subtitulo');

        /*====================================*/
        // Detalle (modal de solo lectura)
        /*====================================*/
        $html = $this->renderizarVista($rutaView, $this->datosSinteticos(1), $BASE, $f3);
        $this->addCase($results, 'view_render', $html !== '', 'la vista View no produjo HTML');
        $this->addCase($results, 'view_datos',
            str_contains($html, 'Tablero Sintetico') && str_contains($html, 'Cierre Prueba') && str_contains($html, 'badge-sp1-primary'),
            'el detalle no muestra el nombre, el cierre y el color del tablero');
        $this->addCase($results, 'view_etiquetas_coinciden_con_valores',
            (bool)preg_match("/'Title' => 'Prioridad',\s*'Text' => \\\$data\['rowData'\]\['idPrioridad'\]/", $this->codigoFuente($rutaView))
            && (bool)preg_match("/'Title' => 'Color Icono',\s*'Text' => '<span class=\"badge-sp1 badge-sp1-'/", $this->codigoFuente($rutaView)),
            'en el detalle, Prioridad debe mostrar idPrioridad y Color Icono el badge del color (antes estaban cruzados)');
        $this->addCase($results, 'view_titulo', str_contains($html, 'Ver Datos') && !str_contains($html, 'modal-subtitle'),
            'con sistemaModalSubtitle = 1 debe mostrar solo el titulo');
        $this->addCase($results, 'view_boton_cerrar', str_contains($html, 'data-bs-dismiss="modal"') && str_contains($html, 'modal-footer'),
            'con sistemaModalCloseBTN = 2 debe dibujarse el pie con el boton Cerrar');
        $datos = $this->datosSinteticos(1, 2);
        $datos['UserData']['sistemaModalCloseBTN'] = 1;
        $html2 = $this->renderizarVista($rutaView, $datos, $BASE, $f3);
        $this->addCase($results, 'view_subtitulo_modal', str_contains($html2, 'modal-subtitle') && str_contains($html2, 'Permite visualizar los datos'),
            'con sistemaModalSubtitle = 2 debe dibujarse el subtitulo');
        $this->addCase($results, 'view_sin_boton_cerrar', !str_contains($html2, 'modal-footer') && str_contains($html2, 'max-height: 80vh'),
            'con sistemaModalCloseBTN distinto de 2 no debe dibujarse el pie del modal');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos `dataCheck()` (contrato de CheckData) y el cifrado de
     * identificadores que consumen `View` y `GetID`.
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
                'CheckData rechazo un POST valido de tablero');
            $malo = $this->callDataCheck($reflector, $Controller, ['Nombre' => 'ab', 'idColor' => 1, 'idPrioridad' => 5, 'idCierre' => 1]);
            $this->addCase($results, 'checkdata_rechaza_nombre_corto', ($malo['status'] ?? true) === false,
                'CheckData acepto un nombre de 2 caracteres (minimo 3)');
            $malo = $this->callDataCheck($reflector, $Controller, ['Nombre' => 'Tablero Prueba', 'idColor' => 'x', 'idPrioridad' => 5, 'idCierre' => 1]);
            $this->addCase($results, 'checkdata_rechaza_color_no_numerico', ($malo['status'] ?? true) === false,
                'CheckData acepto un idColor no numerico');
            $malo = $this->callDataCheck($reflector, $Controller, ['Nombre' => 'Tablero Prueba', 'idColor' => 1, 'idPrioridad' => '2.5', 'idCierre' => 1]);
            $this->addCase($results, 'checkdata_rechaza_prioridad_decimal', ($malo['status'] ?? true) === false,
                'CheckData acepto una prioridad decimal (debe ser entera)');
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

        /*====================================*/
        // Metodos heredados de busqueda disponibles para el controlador
        /*====================================*/
        foreach (['searchWhere', 'searchValidateDates'] as $metodo) {
            $this->addCase($results, 'busqueda_heredada_'.$metodo, method_exists('ControllerBase', $metodo),
                'ControllerBase no ofrece '.$metodo.'()');
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                 SEGURIDAD                                  */
    /******************************************************************************/
    /**
     * Revisa estaticamente las vistas y el controlador de tableros: rutas relativas, rutas
     * absolutas, acceso a sesion, SQL embebido, cifrado de IDs y consultas preparadas.
     *
     * @return array Resultado por caso
     */
    public function seguridadVistasTest(){

        $results = [];

        /*====================================*/
        // Vistas involucradas
        /*====================================*/
        $arrVistas = glob($this->rutaModulo('../views/'.self::CONTROLADOR.'-*.php')) ?: [];
        $this->addCase($results, 'hay_vistas', count($arrVistas) === 6,
            'se esperaban 6 vistas de kanbanTableros y se encontraron '.count($arrVistas));

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
        }

        /*====================================*/
        // Cifrado de los IDs y acciones por nivel en la tabla
        /*====================================*/
        $tabla = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'));
        $this->addCase($results, 'tabla_id_cifrado', str_contains($tabla, "encryptDecrypt('encrypt', \$crud['idKanbanEstado'])"),
            'UpdateList no cifra idKanbanEstado en las acciones');
        $this->addCase($results, 'tabla_acciones_por_nivel',
            str_contains($tabla, '$level >= 1') && str_contains($tabla, '$level >= 2') && str_contains($tabla, '$level >= 4'),
            'UpdateList no condiciona Ver, Editar y Borrar a los niveles 1, 2 y 4');
        $this->addCase($results, 'tabla_escapa_nombre_js', str_contains($tabla, "addslashes(\$crud['Nombre'])"),
            'UpdateList no escapa el nombre que pasa al onclick de borrar');
        $lista = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php'));
        $this->addCase($results, 'lista_crear_por_nivel', str_contains($lista, "['LevelAccess']>=3"),
            'List no condiciona el boton Crear Nuevo al nivel 3');

        /*====================================*/
        // Formularios: validacion de cliente y anti doble envio
        /*====================================*/
        foreach (['formNew', 'formEdit', 'formSearch'] as $form) {
            $codigo = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-'.$form.'.php'));
            $this->addCase($results, 'form_valida_cliente_'.$form, str_contains($codigo, 'validator.checkAll(this)'),
                $form.' no valida el formulario en el cliente antes de enviarlo');
            $this->addCase($results, 'form_evita_doble_envio_'.$form, str_contains($codigo, 'ejecutandoForm.valor'),
                $form.' no evita el doble envio del formulario');
        }

        /*====================================*/
        // Controlador: SQL preparado y sin concatenacion de entrada
        /*====================================*/
        $controlador = $this->fuenteControlador();
        $this->addCase($results, 'controlador_sin_concat_sql', !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $controlador),
            'el controlador concatena superglobales en una cadena SQL');
        $this->addCase($results, 'controlador_parametros_preparados', preg_match_all("/'params'\s*=>/", $controlador) === 8,
            'las 8 consultas (3 de listAll, 1 de UpdateList, 1 de View y 3 de GetID) deben declarar la clave params');
        $this->addCase($results, 'controlador_where_con_params', substr_count($controlador, "\$r['params']") === 3,
            'los tres searchWhere() deben acumular sus parametros preparados');
        $this->addCase($results, 'controlador_id_como_parametro', substr_count($controlador, "'params'  => [\$KanbanEstadoID['data']]") === 2,
            'las consultas de View y GetID deben recibir el ID descifrado como parametro preparado');
        $this->addCase($results, 'controlador_post_solo_en_escritura', substr_count($controlador, '$_POST') === 4,
            'se esperaban 4 usos de $_POST (dataCheck y Post de Insert y Update)');
        $this->addCase($results, 'controlador_sin_get_ni_request', !str_contains($controlador, '$_GET') && !str_contains($controlador, '$_REQUEST'),
            'el controlador lee $_GET o $_REQUEST directamente');

        /************************************/
        // Retorno los datos
        return $results;

    }

}
