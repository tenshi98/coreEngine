<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class kanbanTareasTesting
 *
 * Pruebas del modulo `kanban` - controlador `kanbanTareas` (tablero y listado de tareas:
 * `listAll`, `UpdateList`, `UpdateTableList`, `View`, `Print`, `GetID`, `Insert`, `Update`,
 * `ChangeStatus`, `Delete`).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. Los textos de los casos
 *    correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Alcance: pruebas NO destructivas. `Insert()`, `Update()`, `ChangeStatus()` y `Delete()`
 * escriben en base de datos (con transaccion en `Insert` y `Delete`) y terminan en
 * `Response::*()` (que ejecuta `exit`); las vistas consultan la BD y terminan en
 * `showVista()`. Ninguno se invoca: se verifican por reflexion y lectura del fuente (por
 * metodo, con `cuerpoMetodo()`), y las vistas se renderizan con datos sinteticos.
 *
 * Particularidades del controlador:
 *  - Cuelga del permiso «Tareas en Curso» y comparte ese permiso con `kanbanEstados`,
 *    `kanbanTareasTareas` y `kanbanTareasParticipantes`.
 *  - Varias vistas se encadenan con `require_once` (`-List` -> `-UpdateList`,
 *    `-formNew`, `kanbanEstados-formNew`; `-UpdateList` -> `-formSearch`,
 *    `-UpdateTableList`; `-Print` -> `common-Print`). Un `include` previo de un archivo
 *    vuelve su `require_once` posterior un no-op, por lo que el contenido de cada
 *    sub-vista se verifica renderizandola directamente (o por fuente) y no a traves del
 *    envoltorio. Las suites de `informeTareas` y `kanbanEstados` ya incluyen
 *    `common-Print.php` y `kanbanEstados-formNew.php` antes de esta (orden alfabetico).
 *
 * @see skills/testing/SKILL.md
 */
class kanbanTareasTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR      = 'kanbanTareas';
    private const INSTALADOR       = 'kanbanTareasInstaller';
    private const RUTA_WEB         = 'gestionProyectos/kanban/tareas';
    private const RUTA_WEB_LISTADO = 'gestionProyectos/kanban/tareas/listAll';
    private const RUTA_WEB_SEARCH  = 'gestionProyectos/kanban/tareas/search';
    private const RUTA_WEB_UPDLIST = 'gestionProyectos/kanban/tareas/updateList';
    private const RUTA_WEB_UPDTBL  = 'gestionProyectos/kanban/tareas/updateTableList';
    private const RUTA_WEB_VIEW    = 'gestionProyectos/kanban/tareas/view/@id';
    private const RUTA_WEB_GETID   = 'gestionProyectos/kanban/tareas/getID/@id';
    private const RUTA_WEB_UPDATE  = 'gestionProyectos/kanban/tareas/update';
    private const RUTA_WEB_STATUS  = 'gestionProyectos/kanban/tareas/changeStatus';
    private const RUTA_WEB_PRINT   = 'gestionProyectos/kanban/tareas/print/@id';
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
    // Cuerpo (fuente) de un metodo del controlador, para pruebas estaticas por metodo
    /*******************************************************************/
    private function cuerpoMetodo(string $metodo): string{
        if (!method_exists(self::CONTROLADOR, $metodo)) {
            return '';
        }
        $r      = new ReflectionMethod(self::CONTROLADOR, $metodo);
        $lineas = is_file((string)$r->getFileName()) ? file((string)$r->getFileName()) : [];
        return implode('', array_slice($lineas, $r->getStartLine() - 1, $r->getEndLine() - $r->getStartLine() + 1));
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
    // Ejecuta un dataCheck_N() privado con un POST dado y pasa el resultado por CheckData
    /*******************************************************************/
    private function chequeo(string $metodo, array $post): array{
        $reflector  = new ReflectionMethod(self::CONTROLADOR, $metodo);
        $reflector->setAccessible(true);
        $controller = (new ReflectionClass(self::CONTROLADOR))->newInstanceWithoutConstructor();
        $checkData  = new CheckData();
        return $checkData->checkingData($reflector->invoke($controller, $post));
    }

    /*******************************************************************/
    // Datos sinteticos usados por las vistas del controlador
    //  - $nivel: LevelAccess (1..4)
    //  - $adminTab: KanbanTareasAdminTabIndepend (2 = tableros en otra pestana)
    //  - $usoTareas: KanbanTareasUsoTareas (2 = tareas especificas por trabajo)
    /*******************************************************************/
    private function datosSinteticos(int $nivel, int $adminTab = 1, int $usoTareas = 1, int $subtitulo = 1): array{
        $fila = static function (int $id, int $estado, string $titulo, string $fecha, ?string $usuario, ?string $img): array {
            return [
                'ID'              => $id,
                'idKanban'        => $id,
                'idKanbanEstado'  => $estado,
                'PrioridadNombre' => 'Prioridad Prueba',
                'PrioridadColor'  => 'danger',
                'Fecha'           => $fecha,
                'Titulo'          => $titulo,
                'UsuarioNombre'   => $usuario,
                'UsuarioImg'      => $img,
                'KanbanEstado'    => 'Estado '.$estado,
                'KanbanColor'     => 'primary',
            ];
        };
        $tareaView = [
            ['idTareas' => 1, 'Tarea' => 'Subtarea Uno', 'EstadoNombre' => 'Hecha', 'EstadoColor' => 'success', 'EstadoIcon' => 'bi bi-check', 'Trabajo' => 'Trabajo Prueba'],
        ];
        return [
            'PageTitle'        => 'Listado de Tareas',
            'PageDescription'  => 'Listado de Tareas pendientes de finalizacion.',
            'PageAuthor'       => 'Pruebas',
            'PageKeywords'     => 'Pruebas',
            'TableTitle'       => 'Listado de Tareas',
            'UserData'         => [
                'UserID'                       => 9,
                'MainPathUrl'                  => '/admin/public/upload/',
                'sistemaModalSubtitle'         => $subtitulo,
                'sistemaModalCloseBTN'         => 2,
                'UserType'                     => 2,
                'KanbanTareasAdminTabIndepend' => $adminTab,
                'KanbanTareasUsoTareas'        => $usoTareas,
            ],
            'UserAccess'       => ['LevelAccess' => $nivel, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_FormInputs'   => new UIFormInputs(),
            'Fnc_DataDate'     => new FunctionsDataDate(),
            'Fnc_Codification' => new FunctionsSecurityCodification(),
            'Fnc_ServerServer' => new FunctionsServerServer(),
            'Fnc_WidgetsCommon'=> new UIWidgetsCommon(),
            'Fnc_WidgetsViews'  => new UIWidgetsViews(),
            // Catalogos
            'arrPrioridad'     => [['ID' => 1, 'Nombre' => 'Prioridad Prueba']],
            'arrEstadoCierre'  => [['ID' => 1, 'Nombre' => 'Abierta Prueba'], ['ID' => 2, 'Nombre' => 'Cerrada Prueba']],
            'arrColores'       => [['ID' => 1, 'Nombre' => 'Color Prueba']],
            'arrCierre'        => [['ID' => 1, 'Nombre' => 'Cierre Prueba']],
            'arrUsuarios'      => [['ID' => 3, 'Nombre' => 'Usuario Prueba']],
            'arrTrabajos'      => [['ID' => 4, 'Nombre' => 'Trabajo Catalogo']],
            // Tablero: dos columnas (estados) y tareas agrupadas por ID
            'arrList'          => [
                ['idKanbanEstado' => 1, 'Nombre' => "Por D'Hacer", 'Color' => 'warning'],
                ['idKanbanEstado' => 2, 'Nombre' => 'Hecho',       'Color' => 'success'],
            ],
            'arrTareas'        => [
                55 => [
                    $fila(55, 1, 'Tarea Futura',  '2099-01-02', 'Ana Prueba',   'ana.png'),
                    $fila(55, 1, 'Tarea Futura',  '2099-01-02', 'Luis Sinfoto', ''),
                ],
                56 => [
                    $fila(56, 2, 'Tarea Vencida', '2000-01-02', null, null),
                ],
            ],
            // Detalle (View, Print, formEdit)
            'rowData'          => [
                'idKanban'           => 55,
                'idKanbanEstado'     => 1,
                'idEstadoCierre'     => 1,
                'idPrioridad'        => 1,
                'idUsuario'          => 9,
                'idCierre'           => 1,
                'Fecha'              => '2099-01-02',
                'Titulo'             => 'Tarea Sintetica Uno',
                'Descripcion'        => 'Descripcion sintetica de la tarea',
                'PrioridadNombre'    => 'Prioridad Prueba',
                'PrioridadColor'     => 'danger',
                'EstadoCierreNombre' => 'Abierta Prueba',
                'EstadoCierreColor'  => 'success',
                'KanbanEstado'       => 'Estado Prueba',
                'KanbanColor'        => 'primary',
            ],
            'arrParticipantes' => [
                ['idParticipantes' => 1, 'UsuarioNombre' => 'Ana Prueba',   'UsuarioImg' => 'ana.png'],
                ['idParticipantes' => 2, 'UsuarioNombre' => 'Luis Sinfoto', 'UsuarioImg' => ''],
            ],
            'arrHistorial'     => [[
                // El historial se guarda con el <br> escapado como entidad HTML; la vista lo restituye
                'Descripcion'   => 'Linea uno&lt;br/&gt;Linea dos',
                'Fecha'         => '2026-01-03',
                'Hora'          => '10:30:00',
                'UsuarioNombre' => 'Ana Prueba',
                'UsuarioImg'    => 'ana.png',
            ]],
            'arrTareasDetalle' => $tareaView,
        ];
    }

    /*******************************************************************/
    // Datos para View/Print: arrTareas pasa a ser la lista de subtareas
    /*******************************************************************/
    private function datosDetalle(int $nivel, int $usoTareas = 1, int $subtitulo = 1, int $idEstadoCierre = 1): array{
        $datos = $this->datosSinteticos($nivel, 1, $usoTareas, $subtitulo);
        $datos['arrTareas'] = $datos['arrTareasDetalle'];
        $datos['rowData']['idEstadoCierre'] = $idEstadoCierre;
        return $datos;
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica los archivos del controlador `kanbanTareas` y la convencion
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
            'controlador'           => $this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'),
            'instalador'            => $this->rutaModulo('../installer/'.self::INSTALADOR.'.php'),
            'vista_list'            => $this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php'),
            'vista_update_list'     => $this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'),
            'vista_update_table'    => $this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateTableList.php'),
            'vista_view'            => $this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'),
            'vista_print'           => $this->rutaModulo('../views/'.self::CONTROLADOR.'-Print.php'),
            'vista_form_new'        => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formNew.php'),
            'vista_form_edit'       => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formEdit.php'),
            'vista_form_search'     => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formSearch.php'),
            'vista_common_print'    => $this->rutaModulo('../views/common-Print.php'),
            'vista_estados_form_new'=> $this->rutaModulo('../views/kanbanEstados-formNew.php'),
            'testing'               => $this->rutaModulo('kanbanTareasTesting.php'),
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
        $this->addCase($results, 'clase_testing_cargada', class_exists('kanbanTareasTesting'),
            'la clase kanbanTareasTesting no esta disponible');
        $padre = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padre === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padre.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('kanbanTareasTesting') === 'ControllerBase',
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
     * Verifica el contrato de las seis vistas del controlador (`listAll`, `UpdateList`,
     * `UpdateTableList`, `View`, `Print`, `GetID`): presentacion, manejo de fallos, ID
     * cifrado, consultas del tablero y busqueda del listado.
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Firmas: listAll/UpdateList/UpdateTableList($f3), View/Print/GetID($f3, $params)
        /*====================================*/
        foreach (['listAll', 'UpdateList', 'UpdateTableList'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 1, 1);
        }
        foreach (['View', 'Print', 'GetID'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 2, 2);
        }

        /*====================================*/
        // Contrato de presentacion
        /*====================================*/
        $vistas = ['listAll' => 'List', 'UpdateList' => 'UpdateList', 'UpdateTableList' => 'UpdateTableList', 'View' => 'View', 'Print' => 'Print', 'GetID' => 'formEdit'];
        foreach ($vistas as $metodo => $vista) {
            $this->addCase($results, 'vista_'.$metodo, str_contains($this->cuerpoMetodo($metodo), "\$this->controllerName.'-".$vista.".php'"),
                $metodo.'() no instancia la vista -'.$vista);
        }
        $this->addCase($results, 'tipoview_listAll', str_contains($this->cuerpoMetodo('listAll'), 'showVista(1,'),
            'listAll() debe usar showVista(1) (pagina completa)');
        foreach (['UpdateList', 'UpdateTableList', 'View', 'GetID'] as $metodo) {
            $this->addCase($results, 'tipoview_'.$metodo, str_contains($this->cuerpoMetodo($metodo), 'showVista(2,'),
                $metodo.'() debe usar showVista(2) (fragmento)');
        }
        $this->addCase($results, 'tipoview_Print', str_contains($this->cuerpoMetodo('Print'), 'showVista(3,'),
            'Print() debe usar showVista(3) (pantalla de impresion)');
        $this->addCase($results, 'rutavista_resuelta', substr_count($codigo, 'returnRutaVista(__DIR__') === 6,
            'las seis vistas deben resolver su ruta con returnRutaVista()');
        $this->addCase($results, 'usuario_en_vistas', substr_count($codigo, 'getUserData($f3)') === 7,
            'las vistas no obtienen los datos del usuario (listAll lo pide dos veces)');
        $this->addCase($results, 'nivel_en_vistas', substr_count($codigo, 'getArrLevel($f3, $this->controllerName)') === 6,
            'las vistas no resuelven el nivel de acceso');
        $this->addCase($results, 'listall_maneja_fallo',
            substr_count($codigo, 'showError(1, $f3, $result)') === 1
            && str_contains($codigo, 'mergeResponses([$arrList,$arrTareas,$arrColores,$arrPrioridad,$arrCierre,$arrEstadoCierre,$arrUsuarios,$arrTrabajos])'),
            'listAll() debe comprobar el status de sus ocho consultas y usar showError(1)/mergeResponses()');
        $this->addCase($results, 'fragmentos_manejan_fallo',
            substr_count($codigo, 'showError(2, $f3, $result)') === 5 && substr_count($codigo, 'mergeResponses(') === 6,
            'UpdateList, UpdateTableList, View, Print y GetID deben manejar el fallo con showError(2)/mergeResponses()');

        /*====================================*/
        // listAll: tablero abierto, catalogos y opciones del sistema
        /*====================================*/
        $listAll = $this->cuerpoMetodo('listAll');
        $this->addCase($results, 'listall_solo_tareas_abiertas',
            str_contains($listAll, "'where'   => 'kanban_tareas.idEstadoCierre = ?'") && str_contains($listAll, "'params'  => [1]"),
            'listAll() debe cargar en el tablero solo las tareas abiertas (idEstadoCierre = 1)');
        $this->addCase($results, 'listall_columnas_por_prioridad', str_contains($listAll, "'order'   => 'kanban_estados.idPrioridad ASC'"),
            'listAll() debe ordenar las columnas del tablero por prioridad');
        $this->addCase($results, 'listall_usuarios_activos_no_admin',
            str_contains($listAll, "'where'   => 'idTipoUsuario != ? AND idEstado = ?'") && str_contains($listAll, "'params'  => [1, 1]"),
            'listAll() debe ofrecer como participantes solo usuarios activos que no son administradores');
        $this->addCase($results, 'listall_opcion_tableros_independientes',
            str_contains($listAll, '$arrUserData["KanbanTareasAdminTabIndepend"]==2') && str_contains($listAll, '$arrColores[\'data\']     = [];'),
            'listAll() no omite los catalogos de tableros cuando se administran por separado');
        $this->addCase($results, 'listall_opcion_trabajos',
            str_contains($listAll, '$arrUserData["KanbanTareasUsoTareas"]==2') && str_contains($listAll, "'table'   => 'kanban_trabajos'")
            && str_contains($listAll, "'where'   => 'idEstado = ?'"),
            'listAll() debe cargar los trabajos activos solo si se usan tareas especificas');
        $this->addCase($results, 'listall_agrupa_por_id', str_contains($listAll, "agruparPorClave (\$arrTareas['data'], 'ID' )"),
            'listAll() debe agrupar las filas por ID de tarea');
        $this->addCase($results, 'listall_titulo', str_contains($listAll, "'PageTitle'") && str_contains($listAll, "'TableTitle'"),
            'listAll() no asigna PageTitle y TableTitle');
        $this->addCase($results, 'updatelist_mismo_tablero',
            str_contains($this->cuerpoMetodo('UpdateList'), "'where'   => 'kanban_tareas.idEstadoCierre = ?'")
            && str_contains($this->cuerpoMetodo('UpdateList'), "agruparPorClave (\$arrTareas['data'], 'ID' )"),
            'UpdateList() debe cargar el mismo tablero (tareas abiertas agrupadas) que listAll()');

        /*====================================*/
        // UpdateTableList: busqueda por prioridad, fecha, titulo y estado de cierre
        /*====================================*/
        $tabla = $this->cuerpoMetodo('UpdateTableList');
        $this->addCase($results, 'busqueda_campo_exacto', str_contains($tabla, "\$WhereData_int     = 'idPrioridad,Fecha,idEstadoCierre';"),
            'UpdateTableList() no filtra por idPrioridad, Fecha e idEstadoCierre');
        $this->addCase($results, 'busqueda_campo_relativo', str_contains($tabla, "\$WhereData_string  = 'Titulo';"),
            'UpdateTableList() no filtra por Titulo');
        $this->addCase($results, 'busqueda_valida_fechas', str_contains($tabla, 'searchValidateDates(') && str_contains($tabla, 'Response::error($RespDataBetween, 500)'),
            'UpdateTableList() no valida las fechas con searchValidateDates() ni responde 500');
        $this->addCase($results, 'busqueda_where_preparado', substr_count($tabla, '$this->searchWhere(') === 3 && substr_count($tabla, "'kanban_tareas', ") === 3,
            'UpdateTableList() no construye el WHERE con los tres tipos de searchWhere() sobre kanban_tareas');
        $this->addCase($results, 'busqueda_params', str_contains($tabla, "'params'  => \$whereParams") && str_contains($tabla, "'where'   => \$whereInt"),
            'UpdateTableList() no pasa el WHERE y los parametros preparados');
        $this->addCase($results, 'busqueda_incluye_cerradas', !str_contains($tabla, 'idEstadoCierre = ?'),
            'la busqueda del listado debe incluir tambien las tareas cerradas (sin fijar idEstadoCierre)');
        $this->addCase($results, 'busqueda_agrupa_por_id', str_contains($tabla, "agruparPorClave (\$arrTareas['data'], 'ID' )"),
            'UpdateTableList() debe agrupar las filas por ID de tarea');

        /*====================================*/
        // ID cifrado en View, Print y GetID
        /*====================================*/
        foreach (['View', 'Print', 'GetID'] as $metodo) {
            $cuerpo = $this->cuerpoMetodo($metodo);
            $this->addCase($results, 'id_descifrado_'.$metodo, str_contains($cuerpo, "encryptDecrypt('decrypt', \$params['id'])"),
                $metodo.'() debe descifrar el ID de la ruta');
            $this->addCase($results, 'id_validado_'.$metodo, str_contains($cuerpo, "isValidDecrypted(\$KanbanID, 'id')"),
                $metodo.'() debe validar el ID descifrado');
            $this->addCase($results, 'id_invalido_400_'.$metodo, str_contains($cuerpo, "Response::error('Registro inválido', 400)"),
                'un ID invalido debe responder 400 en '.$metodo.'()');
        }
        $this->addCase($results, 'detalle_por_id',
            substr_count($codigo, "'where'   => 'kanban_tareas.idKanban = ?'") === 3 && substr_count($codigo, "'params'  => [\$KanbanID['data']]") === 8,
            'View, Print y GetID deben filtrar por idKanban con el ID descifrado como parametro preparado');
        $this->addCase($results, 'view_historial', str_contains($this->cuerpoMetodo('View'), "'table'   => 'kanban_tareas_historial'"),
            'View() no consulta el historial de la tarea');
        $this->addCase($results, 'print_sin_historial', !str_contains($this->cuerpoMetodo('Print'), 'kanban_tareas_historial'),
            'Print() no debe consultar el historial (la impresion no lo muestra)');
        $this->addCase($results, 'getid_permite_cierre',
            str_contains($this->cuerpoMetodo('GetID'), "if(\$rowData['status'] === true && (\$rowData['data']['idCierre'] ?? null)==1)") && str_contains($this->cuerpoMetodo('GetID'), 'kanban_estados.idCierre'),
            'GetID() debe cargar los estados de cierre solo si la lectura fue correcta y el tablero permite el cierre');
        $this->addCase($results, 'getid_tarea_inexistente_404',
            str_contains($this->cuerpoMetodo('GetID'), "if(\$rowData['status'] === true && empty(\$rowData['data']))") && str_contains($this->cuerpoMetodo('GetID'), "Response::error('Registro no encontrado', 404)")
            && strpos($this->cuerpoMetodo('GetID'), 'Registro no encontrado') < strpos($this->cuerpoMetodo('GetID'), "['idCierre'] ?? null"),
            'GetID() debe responder 404 si la tarea no existe, antes de leer idCierre');
        $this->addCase($results, 'view_historial_cronologico',
            str_contains($this->cuerpoMetodo('View'), "'order'   => 'kanban_tareas_historial.Fecha ASC, kanban_tareas_historial.Hora ASC'"),
            'el historial de View() debe ordenarse cronologicamente (Fecha y Hora ascendentes)');
        $this->addCase($results, 'consultas_acotadas', substr_count($codigo, 'ConfigAPP::APP["N_MaxItems"]') === 22,
            'las 22 consultas de listado deben acotar el numero de filas');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                           CONTROLADOR: ESCRITURA                           */
    /******************************************************************************/
    /**
     * Verifica `Insert`, `Update`, `ChangeStatus` y `Delete` SIN ejecutarlos, metodo por
     * metodo: verbo HTTP, usuario tomado de la sesion, chequeo de datos, transacciones,
     * historial y respuestas al cliente.
     *
     * @return array Resultado por caso
     */
    public function controladorEscrituraTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Firmas: Insert/Update($f3); ChangeStatus/Delete()
        /*====================================*/
        foreach (['Insert', 'Update'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 1, 1);
        }
        $this->firmaMetodo($results, 'ChangeStatus', 1, 1);
        $this->firmaMetodo($results, 'Delete', 0, 0);

        /*====================================*/
        // Insert: participantes obligatorios, transaccion completa e historial
        /*====================================*/
        $insert = $this->cuerpoMetodo('Insert');
        $this->addCase($results, 'insert_verbo_post',
            str_contains($insert, "REQUEST_METHOD'] !== 'POST'") && strpos($insert, "REQUEST_METHOD'] !== 'POST'") < strpos($insert, 'Base_transactionBegin'),
            'Insert() debe validar el verbo HTTP POST (405) antes de abrir la transaccion');
        $this->addCase($results, 'insert_usuario_de_sesion', (bool)preg_match("/\\\$_POST\['idUsuario'\]\s*=\s*\\\$f3->get\('SESSION\.DataInfo\.UserID'\)/", $insert),
            'Insert() debe tomar el usuario creador de la sesion y no del formulario');
        $this->addCase($results, 'insert_fecha_creacion_del_servidor',
            (bool)preg_match("/\\\$_POST\['FechaCreacion'\]\s*=\s*\\\$this->ServerServer->fechaActual\(\)/", $insert),
            'Insert() debe fijar FechaCreacion con la fecha del servidor y no la del formulario');
        $this->addCase($results, 'insert_exige_participantes',
            str_contains($insert, "if(\$ndata_2==0)") && str_contains($insert, "Response::error('No hay Participantes en la tarea', 400)"),
            'Insert() debe rechazar una tarea sin participantes con 400 (entrada invalida)');
        $this->addCase($results, 'insert_valida_antes_de_transaccion',
            strpos($insert, 'No hay Participantes') !== false && strpos($insert, 'Base_transactionBegin') !== false
            && strpos($insert, 'No hay Participantes') < strpos($insert, 'Base_transactionBegin'),
            'la validacion de participantes debe ocurrir antes de abrir la transaccion');
        $this->addCase($results, 'insert_transaccion_completa',
            substr_count($insert, 'Base_transactionBegin()') === 1 && substr_count($insert, 'Base_transactionCommit()') === 1
            && substr_count($insert, 'Base_transactionRollback()') === 0,
            'Insert() debe abrir una transaccion, confirmarla una vez y revertirla ante cada uno de los 4 fallos');
        $this->addCase($results, 'insert_rollback_antes_de_error',
            substr_count($insert, 'failOperation(') >= 4,
            'cada fallo de Insert() debe pasar por failOperation');
        $this->addCase($results, 'insert_commit_antes_de_exito',
            strpos($insert, 'Base_transactionCommit()') < strpos($insert, 'Response::success('),
            'Insert() debe confirmar la transaccion antes de responder con exito');
        $this->addCase($results, 'insert_tarea_campos',
            str_contains($insert, "'data'      => 'idKanbanEstado,idEstadoCierre,idPrioridad,idUsuario,Fecha,Titulo,Descripcion,FechaCreacion'")
            && str_contains($insert, "'required'  => 'idKanbanEstado,idEstadoCierre,idPrioridad,idUsuario,Fecha,Titulo,Descripcion,FechaCreacion'")
            && str_contains($insert, "'table'     => 'kanban_tareas'"),
            'Insert() no declara los campos y obligatorios de la tarea');
        $this->addCase($results, 'insert_chequeos',
            str_contains($insert, '$this->dataCheck_1($_POST)') && substr_count($insert, '$this->dataCheck_2(') === 2 && str_contains($insert, '$this->dataCheck_3($arrTareas)'),
            'Insert() debe chequear la tarea, las filas relacionadas y el historial');
        $this->addCase($results, 'insert_filas_en_bloque',
            substr_count($insert, '$this->Base_insertMultiple(') === 2 && str_contains($insert, "'table'     => 'kanban_tareas_tareas'")
            && str_contains($insert, "'table'     => 'kanban_tareas_participantes'") && str_contains($insert, "'idEstadoTrabajo'  => 1"),
            'Insert() debe insertar subtareas (estado abierto) y participantes en bloque');
        $this->addCase($results, 'insert_historial',
            str_contains($insert, "'Descripcion' => 'Tarea Creada'") && str_contains($insert, "'table'     => 'kanban_tareas_historial'")
            && str_contains($insert, "'Fecha'       => \$this->ServerServer->fechaActual()") && str_contains($insert, "'Hora'        => \$this->ServerServer->horaActual()")
            && !str_contains($insert, 'Fecha_Actual') && !str_contains($insert, 'Hora_Actual'),
            'Insert() debe registrar «Tarea Creada» en el historial con la fecha y hora del servidor');
        $this->addCase($results, 'insert_respuesta_id', str_contains($insert, "Response::success(\$Response['data'])"),
            'Insert() debe responder con el ID de la tarea creada');

        /*====================================*/
        // Update: verbo, usuario de sesion, escritura por idKanban e historial de cambios
        /*====================================*/
        $update = $this->cuerpoMetodo('Update');
        $this->addCase($results, 'update_verbo_post', str_contains($update, "REQUEST_METHOD'] !== 'POST'") && str_contains($update, "Response::error('Error en el Request Method', 405)"),
            'Update() no valida el verbo HTTP POST con respuesta 405');
        $this->addCase($results, 'update_usuario_de_sesion', (bool)preg_match("/\\\$_POST\['idUsuario'\]\s*=\s*\\\$f3->get\('SESSION\.DataInfo\.UserID'\)/", $update),
            'Update() debe tomar el usuario de la sesion y no del formulario');
        $this->addCase($results, 'update_donde', str_contains($update, "'where'     => 'idKanban'"),
            'Update() no delimita la escritura por idKanban');
        $this->addCase($results, 'update_campos',
            str_contains($update, "'data'      => 'idKanban,idKanbanEstado,idEstadoCierre,idPrioridad,idUsuario,Fecha,Titulo,Descripcion,FechaCreacion'")
            && str_contains($update, "'required'  => 'idKanban,idPrioridad,Fecha,Titulo,Descripcion'"),
            'Update() no declara los campos y obligatorios de la tarea');
        $this->addCase($results, 'update_base', str_contains($update, '$this->Base_update(') && str_contains($update, '$this->dataCheck_1($_POST)'),
            'Update() debe chequear con dataCheck_1 y escribir con Base_update()');
        $this->addCase($results, 'update_compara_valores_antiguos',
            str_contains($update, "'Old_' . \$campo") && str_contains($update, "'idPrioridad'    => ['label' => 'prioridad'")
            && str_contains($update, "'idEstadoCierre' => ['label' => 'estado de cierre'"),
            'Update() debe comparar los campos con sus valores antiguos (Old_*)');
        $this->addCase($results, 'update_historial_solo_si_cambia',
            str_contains($update, "if(\$comparacion!='')") && str_contains($update, "'Se cambian datos basicos de la tarea:'.\$comparacion")
            && str_contains($update, "'Fecha'       => \$this->ServerServer->fechaActual()") && str_contains($update, "'Hora'        => \$this->ServerServer->horaActual()")
            && !str_contains($update, 'Fecha_Actual') && !str_contains($update, 'Hora_Actual'),
            'Update() debe registrar en el historial solo cuando hay cambios, con la fecha y hora del servidor');
        $this->addCase($results, 'update_transaccion_completa',
            substr_count($update, 'Base_transactionBegin()') === 1 && substr_count($update, 'Base_transactionCommit()') === 1 && substr_count($update, 'Base_transactionRollback()') === 0
            && substr_count($update, 'failOperation(') >= 2
            && strpos($update, 'Base_transactionBegin()') < strpos($update, '$this->Base_update(') && strpos($update, 'Base_transactionCommit()') < strpos($update, 'Response::success('),
            'Update() debe abrir una transaccion antes de escribir, revertirla ante el fallo de la tarea o del historial y confirmarla antes de responder');
        $this->addCase($results, 'update_historial_con_resultado', str_contains($update, "\$xInsert = \$this->Base_insert(") && str_contains($update, "if (\$xInsert['status'] === false)"),
            'Update() debe comprobar el resultado del historial (y revertir si falla)');
        $this->addCase($results, 'update_respuesta', str_contains($update, "Response::success(\$Response['data'])") && str_contains($update, "failOperation("),
            'Update() debe responder 200 con el resultado y 500 controlado ante un fallo');

        /*====================================*/
        // ChangeStatus: verbo, IDs cifrados, solo escribe si cambia el tablero
        /*====================================*/
        $status = $this->cuerpoMetodo('ChangeStatus');
        $this->addCase($results, 'status_verbo_post', str_contains($status, "REQUEST_METHOD'] !== 'POST'") && str_contains($status, "Response::error('Error en el Request Method', 405)"),
            'ChangeStatus() no valida el verbo HTTP POST con respuesta 405');
        $this->addCase($results, 'status_ids_cifrados',
            str_contains($status, "encryptDecrypt('decrypt', \$_POST['idKanban'] ?? '')") && str_contains($status, "encryptDecrypt('decrypt', \$_POST['idKanbanEstado'] ?? '')")
            && substr_count($status, "isValidDecrypted(") === 2 && substr_count($status, "Response::error('Registro inválido', 400)") === 3,
            'ChangeStatus() debe descifrar y validar idKanban e idKanbanEstado, con 400 ante un ID invalido (y una tarea inexistente)');
        $this->addCase($results, 'status_lee_estado_actual',
            str_contains($status, "'data'    => 'idKanbanEstado'") && str_contains($status, "'where'   => 'idKanban = ?'") && str_contains($status, "'params'  => [\$idKanban]"),
            'ChangeStatus() debe leer el tablero actual de la tarea con parametros preparados');
        $this->addCase($results, 'status_usuario_de_sesion',
            str_contains($status, "\$idUsuario = \$f3->get('SESSION.DataInfo.UserID')") && str_contains($status, "'idUsuario'   => \$idUsuario"),
            'ChangeStatus() debe tomar el usuario de la sesion para el historial');
        $this->addCase($results, 'status_sin_datos_del_cliente',
            !str_contains($status, "\$_POST['idUsuario']") && !str_contains($status, 'Fecha_Actual') && !str_contains($status, 'Hora_Actual')
            && str_contains($status, "'Fecha'       => \$this->ServerServer->fechaActual()") && str_contains($status, "'Hora'        => \$this->ServerServer->horaActual()"),
            'ChangeStatus() no debe usar el usuario, la fecha ni la hora enviados por el cliente');
        $this->addCase($results, 'status_lectura_con_resultado',
            str_contains($status, "if (\$rowData['status'] === false)") && str_contains($status, 'if (empty($rowData[\'data\']))'),
            'ChangeStatus() debe responder 500 si falla la lectura y 400 si la tarea no existe');
        $this->addCase($results, 'status_sin_cambio_responde_200',
            str_contains($status, "if(\$rowData['data']['idKanbanEstado']==\$idKanbanEstado){") && str_contains($status, "Response::success(\$idKanban)")
            && strpos($status, "Response::success(\$idKanban)") < strpos($status, '$this->Base_update('),
            'ChangeStatus() debe responder 200 sin escribir cuando el tablero destino es el mismo');
        $this->addCase($results, 'status_escritura',
            str_contains($status, "'data'      => 'idKanban,idKanbanEstado'") && str_contains($status, "'where'     => 'idKanban'")
            && str_contains($status, '$this->Base_update(') && str_contains($status, '$this->dataCheck_1($arrTareas)'),
            'ChangeStatus() debe actualizar solo idKanbanEstado delimitado por idKanban');
        $this->addCase($results, 'status_historial', str_contains($status, "'Descripcion' => 'Tarea Actualizada de tablero'") && str_contains($status, "'table'     => 'kanban_tareas_historial'"),
            'ChangeStatus() debe registrar el cambio de tablero en el historial');
        $this->addCase($results, 'status_respuesta', substr_count($status, 'Response::success(') === 2 && substr_count($status, 'failOperation(') === 2,
            'ChangeStatus() debe responder 200 (sin cambios o tras actualizar) y 500 controlado ante un fallo de lectura o escritura');

        /*====================================*/
        // Delete: verbo, transaccion y borrado en cascada
        /*====================================*/
        $delete = $this->cuerpoMetodo('Delete');
        $this->addCase($results, 'delete_verbo', str_contains($delete, "REQUEST_METHOD'] !== 'DELETE'") && str_contains($delete, "Response::error('Error en el Request Method', 405)"),
            'Delete() no valida el verbo HTTP DELETE con respuesta 405');
        $this->addCase($results, 'delete_lee_cuerpo', str_contains($delete, 'parse_str(file_get_contents("php://input")'),
            'Delete() no lee el cuerpo DELETE');
        $this->addCase($results, 'delete_donde', str_contains($delete, "'where'       => 'idKanban'") && str_contains($delete, "'table'       => 'kanban_tareas'"),
            'Delete() no delimita el borrado de la tarea por idKanban');
        $this->addCase($results, 'delete_cascada',
            str_contains($delete, "'table' => 'kanban_tareas_historial'") && str_contains($delete, "'table' => 'kanban_tareas_participantes'")
            && str_contains($delete, "'table' => 'kanban_tareas_tareas'") && str_contains($delete, "'where' => 'idKanban'"),
            'Delete() debe borrar tambien el historial, los participantes y las subtareas de la tarea');
        $this->addCase($results, 'delete_sin_archivos', substr_count($delete, "'files' => ''") === 3 && str_contains($delete, "'files'       => ''"),
            'Delete() no declara que la tarea y sus tablas relacionadas no manejan archivos');
        $this->addCase($results, 'delete_transaccion_completa',
            substr_count($delete, 'Base_transactionBegin()') === 1 && substr_count($delete, 'Base_transactionCommit()') === 1 && substr_count($delete, 'Base_transactionRollback()') === 0
            && substr_count($delete, 'failOperation(') >= 2,
            'Delete() debe abrir una transaccion, confirmarla una vez y revertirla ante cada uno de los 2 fallos');
        $this->addCase($results, 'delete_commit_antes_de_exito', strpos($delete, 'Base_transactionCommit()') < strpos($delete, 'Response::success('),
            'Delete() debe confirmar la transaccion antes de responder con exito');

        /*====================================*/
        // Conjunto: las transacciones del controlador son solo las de Insert y Delete
        /*====================================*/
        $this->addCase($results, 'transacciones_insert_update_delete',
            substr_count($codigo, 'Base_transactionBegin()') === 3 && substr_count($codigo, 'Base_transactionCommit()') === 3 && substr_count($codigo, 'Base_transactionRollback()') === 0,
            'se esperaban 3 transacciones (Insert, Update y Delete) con 10 failOperation y 0 rollback inline');
        $this->addCase($results, 'respuestas_exito', substr_count($codigo, 'Response::success(') === 5,
            'se esperaban 5 Response::success() (Insert, Update, ChangeStatus x2 y Delete)');
        $this->addCase($results, 'respuestas_405', substr_count($codigo, "Response::error('Error en el Request Method', 405)") === 4,
            'Insert, Update, ChangeStatus y Delete deben responder 405 ante un verbo incorrecto');
        $this->addCase($results, 'respuestas_500', substr_count($codigo, 'failOperation(') === 10,
            'se esperaban 10 puntos failOperation');
        $this->addCase($results, 'sin_fecha_ni_hora_del_cliente', !str_contains($codigo, 'Fecha_Actual') && !str_contains($codigo, 'Hora_Actual'),
            'el controlador no debe leer Fecha_Actual ni Hora_Actual del cliente: la fecha y la hora las pone el servidor');
        $this->addCase($results, 'historial_sin_validar_en_cambios', substr_count($codigo, "'novalidate' => true") === 2,
            'el historial de Update y ChangeStatus debe insertarse con novalidate (se valida la entrada principal)');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                       CONTROLADOR: RUTAS E INSTALADOR                      */
    /******************************************************************************/
    /**
     * Verifica que las 11 rutas de `kanbanTareas` publicadas por el instalador apunten a
     * metodos reales con el verbo y el nivel esperados, el permiso «Tareas en Curso» y las
     * tablas que crea el instalador.
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
            'listado'      => ['1 '.self::RUTA_WEB_LISTADO, 'kanbanTareas->listAll',         1],
            'busqueda'     => ['2 '.self::RUTA_WEB_SEARCH,  'kanbanTareas->UpdateTableList', 1],
            'actualizar'   => ['1 '.self::RUTA_WEB_UPDLIST, 'kanbanTareas->UpdateList',      2],
            'tabla'        => ['1 '.self::RUTA_WEB_UPDTBL,  'kanbanTareas->UpdateTableList', 1],
            'ver'          => ['1 '.self::RUTA_WEB_VIEW,    'kanbanTareas->View',            1],
            'getid'        => ['1 '.self::RUTA_WEB_GETID,   'kanbanTareas->GetID',           2],
            'crear'        => ['2 '.self::RUTA_WEB,         'kanbanTareas->Insert',          3],
            'editar'       => ['2 '.self::RUTA_WEB_UPDATE,  'kanbanTareas->Update',          2],
            'borrar'       => ['3 '.self::RUTA_WEB,         'kanbanTareas->Delete',          4],
            'cambiar'      => ['2 '.self::RUTA_WEB_STATUS,  'kanbanTareas->ChangeStatus',    2],
            'imprimir'     => ['1 '.self::RUTA_WEB_PRINT,   'kanbanTareas->Print',           1],
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
        // Conjunto completo de rutas propias, en el primer permiso (tareas en curso)
        /*====================================*/
        $propias = array_filter($rutas, static function ($r) { return $r[2] === 'kanbanTareas'; });
        $this->addCase($results, 'rutas_propias_completas', count($propias) === 11,
            'se esperaban 11 rutas de kanbanTareas y hay '.count($propias));
        $this->addCase($results, 'rutas_en_su_permiso', $this->rutasDeTipo(1, 'kanbanTareas') === 11,
            'las 11 rutas de kanbanTareas deben publicarse en el primer permiso del instalador');
        $this->addCase($results, 'rutas_escritura_con_nivel_minimo',
            ($rutas['2 '.self::RUTA_WEB][1] ?? 0) >= 3 && ($rutas['3 '.self::RUTA_WEB][1] ?? 0) === 4 && ($rutas['2 '.self::RUTA_WEB_STATUS][1] ?? 0) >= 2,
            'crear exige nivel >= 3, borrar nivel 4 y cambiar de tablero nivel >= 2');

        /*====================================*/
        // Permiso «Tareas en Curso» y desinstalacion
        /*====================================*/
        $instalador = $this->codigoFuente($this->rutaModulo('../installer/'.self::INSTALADOR.'.php'));
        $this->addCase($results, 'instalador_permiso_tareas',
            (bool)preg_match("/'RutaWeb'\s*=>\s*'".preg_quote(self::RUTA_WEB, '/')."'/", $instalador)
            && (bool)preg_match("/'RutaController'\s*=>\s*'kanbanTareas'/", $instalador),
            'el instalador no declara el permiso de tareas con su ruta y controlador');
        $this->addCase($results, 'instalador_tareas_crud_gestion_proyectos',
            (bool)preg_match("/'idPermisosCat'\s*=>\s*'2',\s*\/\/ Gesti\S+ Proyectos\s*\n\s*'idEstado'[^\n]*\n\s*'idTipo'\s*=>\s*'1',\s*\/\/ Crud Normal\s*\n\s*'Nombre'\s*=>\s*'Tareas en Curso'/", $instalador),
            'el permiso de tareas debe ser un Crud Normal de la categoria Gestion Proyectos');
        $this->addCase($results, 'instalador_tareas_nivel_completo',
            (bool)preg_match("/'idLevelLimit'\s*=>\s*'4',[^\n]*\n\s*'RutaWeb'\s*=>\s*'".preg_quote(self::RUTA_WEB, '/')."'/", $instalador),
            'el permiso de tareas debe tener nivel limite 4 (ver/editar/crear/borrar)');
        $this->addCase($results, 'instalador_desinstala_tareas', str_contains($instalador, '"kanbanTareas"'),
            'RutaController() no lista kanbanTareas y dejaria rutas huerfanas al desinstalar');

        /*====================================*/
        // Tablas que crea el instalador y columnas que escribe el controlador
        /*====================================*/
        $tablas = [];
        try {
            $obj = (new ReflectionClass(self::INSTALADOR))->newInstanceWithoutConstructor();
            foreach ($obj->listTables() as $t) {
                $tablas[$t['table']] = (string)$t['data'];
            }
        } catch (Throwable $e) {
            $tablas = [];
        }
        $this->addCase($results, 'instalador_crea_tablas',
            isset($tablas['kanban_tareas'], $tablas['kanban_tareas_tareas'], $tablas['kanban_tareas_participantes'], $tablas['kanban_tareas_historial']),
            'el instalador no crea kanban_tareas, kanban_tareas_tareas, kanban_tareas_participantes y kanban_tareas_historial');
        $cols = static function (string $tabla, array $columnas) use ($tablas): bool {
            foreach ($columnas as $c) {
                if (!str_contains($tablas[$tabla] ?? '', '`'.$c.'`')) {
                    return false;
                }
            }
            return true;
        };
        $this->addCase($results, 'tabla_tareas_columnas',
            $cols('kanban_tareas', ['idKanban', 'idKanbanEstado', 'idEstadoCierre', 'idPrioridad', 'idUsuario', 'Fecha', 'Titulo', 'Descripcion', 'FechaCreacion']),
            'kanban_tareas no tiene las columnas que escribe Insert()/Update()');
        $this->addCase($results, 'tabla_subtareas_columnas',
            $cols('kanban_tareas_tareas', ['idKanban', 'Tarea', 'idEstadoTrabajo', 'idTrabajo']),
            'kanban_tareas_tareas no tiene las columnas que escribe Insert()');
        $this->addCase($results, 'tabla_participantes_columnas',
            $cols('kanban_tareas_participantes', ['idKanban', 'idUsuario']),
            'kanban_tareas_participantes no tiene las columnas que escribe Insert()');
        $this->addCase($results, 'tabla_historial_columnas',
            $cols('kanban_tareas_historial', ['idKanban', 'idUsuario', 'Descripcion', 'Fecha', 'Hora']),
            'kanban_tareas_historial no tiene las columnas que escribe el historial');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                PERMISOS                                    */
    /******************************************************************************/
    /**
     * Verifica la sesion, el nivel de acceso de tareas y el registro de sus rutas en el
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
        $this->addCase($results, 'sesion_usuario_id', !is_array($UserData) || empty($UserData) || (isset($UserData['UserID']) && (int)$UserData['UserID'] > 0),
            'SESSION.DataInfo.UserID debe existir: Insert() y Update() lo usan como usuario creador');

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
            'ruta_busqueda_router'   => ['/'.self::RUTA_WEB_SEARCH,  'POST',   self::CONTROLADOR.'->UpdateTableList'],
            'ruta_actualizar_router' => ['/'.self::RUTA_WEB_UPDLIST, 'GET',    self::CONTROLADOR.'->UpdateList'],
            'ruta_tabla_router'      => ['/'.self::RUTA_WEB_UPDTBL,  'GET',    self::CONTROLADOR.'->UpdateTableList'],
            'ruta_ver_router'        => ['/'.self::RUTA_WEB_VIEW,    'GET',    self::CONTROLADOR.'->View'],
            'ruta_getid_router'      => ['/'.self::RUTA_WEB_GETID,   'GET',    self::CONTROLADOR.'->GetID'],
            'ruta_insert_router'     => ['/'.self::RUTA_WEB,         'POST',   self::CONTROLADOR.'->Insert'],
            'ruta_update_router'     => ['/'.self::RUTA_WEB_UPDATE,  'POST',   self::CONTROLADOR.'->Update'],
            'ruta_delete_router'     => ['/'.self::RUTA_WEB,         'DELETE', self::CONTROLADOR.'->Delete'],
            'ruta_cambiar_router'    => ['/'.self::RUTA_WEB_STATUS,  'POST',   self::CONTROLADOR.'->ChangeStatus'],
            'ruta_imprimir_router'   => ['/'.self::RUTA_WEB_PRINT,   'GET',    self::CONTROLADOR.'->Print'],
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
    /*                              VISTAS: TABLERO                               */
    /******************************************************************************/
    /**
     * Renderiza `kanbanTareas-List.php` (pagina), `-UpdateList.php` (tablero),
     * `-UpdateTableList.php` (listado) y `-formSearch.php` con datos sinteticos: botones
     * de crear segun nivel y opcion de tableros independientes, columnas y tarjetas con ID
     * cifrado, color segun vencimiento, participantes, matriz de niveles 1..4 y busqueda.
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
        $rutaList  = $this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php');
        $rutaBoard = $this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php');
        $rutaTable = $this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateTableList.php');
        $rutaForm  = $this->rutaModulo('../views/'.self::CONTROLADOR.'-formSearch.php');
        $codList   = $this->codigoFuente($rutaList);
        $codBoard  = $this->codigoFuente($rutaBoard);

        /*====================================*/
        // Pagina: botones de crear segun nivel (>= 3) y opcion de tableros independientes
        /*====================================*/
        $html = $this->renderizarVista($rutaList, $this->datosSinteticos(4, 1), $BASE, $f3);
        $this->addCase($results, 'listado_render', $html !== '', 'la vista List no produjo HTML');
        $this->addCase($results, 'listado_titulo', str_contains($html, 'Listado de Tareas'), 'la pagina no muestra el titulo');
        $this->addCase($results, 'listado_pestanas',
            str_contains($html, 'data-bs-target="#tab_kanban_1"') && str_contains($html, 'data-bs-target="#tab_kanban_2"') && str_contains($html, 'id="listTableData"'),
            'la pagina no dibuja las pestanas Tablero y Listado ni el contenedor listTableData');
        foreach (self::NIVELES as $nivel) {
            foreach ([1, 2] as $adminTab) {
                $pagina = $this->renderizarVista($rutaList, $this->datosSinteticos($nivel, $adminTab), $BASE, $f3);
                $this->addCase($results, 'listado_crear_tarea_nivel'.$nivel.'_admin'.$adminTab,
                    str_contains($pagina, 'data-bs-target="#newFormModal"') === ($nivel >= 3),
                    'el boton Crear Tarea debe mostrarse solo con nivel >= 3 (nivel '.$nivel.')');
                $this->addCase($results, 'listado_crear_tablero_nivel'.$nivel.'_admin'.$adminTab,
                    str_contains($pagina, 'data-bs-target="#newFormModalEstado"') === ($nivel >= 3 && $adminTab !== 2),
                    'el boton Crear Tablero debe mostrarse solo con nivel >= 3 y tableros no independientes (nivel '.$nivel.', admin '.$adminTab.')');
                $this->addCase($results, 'listado_js_tableros_admin'.$adminTab.'_nivel'.$nivel,
                    str_contains($pagina, 'function EditTabla(') === ($adminTab !== 2) && str_contains($pagina, 'function delTabla(') === ($adminTab !== 2),
                    'las funciones de editar/borrar tableros solo deben existir si los tableros no son independientes (admin '.$adminTab.')');
            }
        }

        /*====================================*/
        // JavaScript de la pagina: endpoints, borrado, arrastre y cambio de tablero
        /*====================================*/
        $this->addCase($results, 'listado_endpoints_js',
            str_contains($html, $BASE.'/'.self::RUTA_WEB.'/view/') && str_contains($html, $BASE.'/'.self::RUTA_WEB.'/getID/')
            && str_contains($html, $BASE.'/'.self::RUTA_WEB.'/updateList') && str_contains($html, $BASE.'/'.self::RUTA_WEB.'Tareas/newData/')
            && str_contains($html, $BASE.'/'.self::RUTA_WEB.'Participantes/newData/'),
            'el JavaScript no apunta a view, getID, updateList y a los formularios de tareas y participantes');
        $this->addCase($results, 'listado_borrado_delete',
            str_contains($html, "let Metodo      = 'DELETE'") && str_contains($html, '{"idKanban": ID}') && str_contains($html, 'Swal.fire') && str_contains($html, 'isConfirmed'),
            'el borrado de tareas no usa DELETE con idKanban y confirmacion');
        $this->addCase($results, 'listado_sin_datos_del_cliente',
            !str_contains($html, 'idUsuarioDel') && !str_contains($html, 'Fecha_Actual') && !str_contains($html, 'Hora_Actual') && !str_contains($html, '"idUsuario"'),
            'el JavaScript no debe enviar usuario, fecha ni hora: los pone el servidor');
        $this->addCase($results, 'listado_sin_console_log', !str_contains($html, 'console.log'),
            'el JavaScript de la pagina no debe dejar console.log de depuracion');
        $this->addCase($results, 'listado_borrar_participante',
            str_contains($html, $BASE.'/'.self::RUTA_WEB.'Participantes') && str_contains($html, '"idParticipantes": ID') && str_contains($html, '"idKanbanDel": ID2'),
            'el borrado de participantes no envia idParticipantes e idKanbanDel');
        $this->addCase($results, 'listado_cambio_tablero',
            str_contains($html, $BASE.'/'.self::RUTA_WEB.'/changeStatus') && str_contains($html, '"idKanban": Objeto') && str_contains($html, '"idKanbanEstado": Destino'),
            'el arrastre no envia el cambio de tablero a /changeStatus con idKanban e idKanbanEstado');
        $this->addCase($results, 'listado_arrastre',
            str_contains($html, 'dragstart') && str_contains($html, 'dragend') && str_contains($html, 'dragover') && str_contains($html, 'drop') && str_contains($html, 'call_1();'),
            'falta la logica de arrastrar y soltar tareas entre tableros');
        $this->addCase($results, 'listado_quitar_filtro',
            str_contains($html, $BASE.'/'.self::RUTA_WEB.'/updateTableList') && str_contains($html, '#listTableDataList'),
            'Quitar filtro no recarga el listado desde /updateTableList');

        /*====================================*/
        // Sub-vistas que encadena la pagina (por fuente: el require_once no se repite)
        /*====================================*/
        $this->addCase($results, 'listado_incluye_tablero', str_contains($codList, "require_once('kanbanTareas-UpdateList.php')"),
            'la pagina no incluye kanbanTareas-UpdateList.php');
        $this->addCase($results, 'listado_incluye_form_new', str_contains($codList, "require_once('kanbanTareas-formNew.php')"),
            'la pagina no incluye kanbanTareas-formNew.php');
        $this->addCase($results, 'listado_incluye_estados_form_new',
            str_contains($codList, "require_once('kanbanEstados-formNew.php')") && str_contains($codList, '$data[\'UserData\']["KanbanTareasAdminTabIndepend"]!=2'),
            'la pagina debe incluir kanbanEstados-formNew.php solo si los tableros no son independientes');
        $this->addCase($results, 'tablero_incluye_busqueda',
            str_contains($codBoard, "require_once('kanbanTareas-formSearch.php')") && str_contains($codBoard, "require_once('kanbanTareas-UpdateTableList.php')"),
            'el tablero no incluye el formulario de busqueda y el listado de la segunda pestana');

        /*====================================*/
        // Tablero: matriz de niveles y opcion de tableros independientes
        /*====================================*/
        foreach (self::NIVELES as $nivel) {
            foreach ([1, 2] as $adminTab) {
                $tablero = $this->renderizarVista($rutaBoard, $this->datosSinteticos($nivel, $adminTab), $BASE, $f3);
                $this->addCase($results, 'tablero_render_nivel'.$nivel.'_admin'.$adminTab, $tablero !== '',
                    'UpdateList no produjo HTML con nivel '.$nivel);
                $this->addCase($results, 'tablero_ver_nivel'.$nivel.'_admin'.$adminTab, str_contains($tablero, 'listTableDataView('),
                    'la accion Ver tarea debe estar disponible con nivel '.$nivel);
                $this->addCase($results, 'tablero_borrar_tarea_nivel'.$nivel.'_admin'.$adminTab, str_contains($tablero, 'listTableDataDel(') === ($nivel >= 4),
                    'la accion Borrar tarea debe mostrarse solo con nivel >= 4 (nivel '.$nivel.')');
                $this->addCase($results, 'tablero_editar_tablero_nivel'.$nivel.'_admin'.$adminTab, str_contains($tablero, 'EditTabla(') === ($nivel >= 2 && $adminTab !== 2),
                    'Editar tablero debe mostrarse solo con nivel >= 2 y tableros no independientes (nivel '.$nivel.', admin '.$adminTab.')');
                $this->addCase($results, 'tablero_borrar_tablero_nivel'.$nivel.'_admin'.$adminTab, str_contains($tablero, 'delTabla(') === ($nivel >= 4 && $adminTab !== 2),
                    'Borrar tablero debe mostrarse solo con nivel >= 4 y tableros no independientes (nivel '.$nivel.', admin '.$adminTab.')');
            }
        }
        $tablero = $this->renderizarVista($rutaBoard, $this->datosSinteticos(4, 1), $BASE, $f3);
        $this->addCase($results, 'tablero_columnas',
            str_contains($tablero, 'id="tab_kanban_1"') && substr_count($tablero, 'class="kanban status"') === 2 && str_contains($tablero, 'badge-sp1-warning') && str_contains($tablero, 'badge-sp1-success'),
            'el tablero no dibuja una columna por estado con su color');
        $this->addCase($results, 'tablero_tareas_en_su_columna',
            substr_count($tablero, 'draggable="true"') === 2 && str_contains($tablero, 'Tarea Futura') && str_contains($tablero, 'Tarea Vencida'),
            'cada tarea debe dibujarse como una tarjeta arrastrable en su columna');
        $this->addCase($results, 'tablero_color_vencimiento',
            substr_count($tablero, 'task-ok') === 1 && substr_count($tablero, 'task-problem') === 1,
            'la tarjeta de una tarea con fecha futura debe ser task-ok y la vencida task-problem');
        $this->addCase($results, 'tablero_ids_cifrados',
            !str_contains($tablero, 'id="55"') && !str_contains($tablero, 'id="56"') && !str_contains($tablero, 'id="1"') && !str_contains($tablero, "listTableDataView('55')")
            && preg_match_all('/class="task [^"]*todo" draggable="true" id="[^"]{8,}"/', $tablero) === 2,
            'las tarjetas y columnas deben usar IDs cifrados (no idKanban ni idKanbanEstado en claro)');
        $this->addCase($results, 'tablero_participantes',
            str_contains($tablero, 'data-title="Ana Prueba"') && str_contains($tablero, '/admin/public/upload/ana.png')
            && str_contains($tablero, 'data-title="Luis Sinfoto"') && str_contains($tablero, $BASE.'/img/profile-img.jpg'),
            'la tarjeta no muestra los participantes con su foto o la imagen por defecto');
        $this->addCase($results, 'tablero_nombre_escapado_para_js', str_contains($tablero, "Por D\\'Hacer"),
            'el nombre de columna con comilla simple debe escaparse (addslashes) en el onclick de borrar');
        $this->addCase($results, 'tablero_segunda_pestana', str_contains($codBoard, 'id="tab_kanban_2"') && str_contains($codBoard, 'id="listTableDataList"'),
            'el tablero no declara la segunda pestana con el contenedor listTableDataList');
        $vacio = $this->datosSinteticos(4, 1);
        $vacio['arrList'] = [];
        $vacio['arrTareas'] = [];
        $this->addCase($results, 'tablero_vacio', $this->renderizarVista($rutaBoard, $vacio, $BASE, $f3) !== '',
            'el tablero sin columnas ni tareas no se dibuja');
        $sinTareas = $this->datosSinteticos(4, 1);
        $sinTareas['arrTareas'] = [];
        $htmlSinTareas = $this->renderizarVista($rutaBoard, $sinTareas, $BASE, $f3);
        $this->addCase($results, 'tablero_columnas_sin_tareas', substr_count($htmlSinTareas, 'class="kanban status"') === 2 && !str_contains($htmlSinTareas, 'draggable="true"'),
            'sin tareas el tablero debe dibujar las columnas vacias');

        /*====================================*/
        // Listado (segunda pestana): matriz de niveles y datos
        /*====================================*/
        foreach (self::NIVELES as $nivel) {
            $tabla = $this->renderizarVista($rutaTable, $this->datosSinteticos($nivel), $BASE, $f3);
            $this->addCase($results, 'listado_tabla_render_nivel'.$nivel, $tabla !== '', 'UpdateTableList no produjo HTML con nivel '.$nivel);
            $this->addCase($results, 'listado_tabla_ver_nivel'.$nivel, str_contains($tabla, 'listTableDataView('),
                'la accion Ver debe estar disponible con nivel '.$nivel);
            $this->addCase($results, 'listado_tabla_borrar_nivel'.$nivel, str_contains($tabla, 'listTableDataDel(') === ($nivel >= 4),
                'la accion Borrar debe mostrarse solo con nivel >= 4 (nivel '.$nivel.')');
            $this->addCase($results, 'listado_tabla_sin_editar_nivel'.$nivel, !str_contains($tabla, 'listTableDataEdit('),
                'el listado no ofrece editar (se edita desde el detalle)');
        }
        $tabla = $this->renderizarVista($rutaTable, $this->datosSinteticos(4), $BASE, $f3);
        $this->addCase($results, 'listado_tabla_columnas',
            str_contains($tabla, '>Estado<') && str_contains($tabla, '>Prioridad<') && str_contains($tabla, '>Fecha<') && str_contains($tabla, '>Titulo<') && str_contains($tabla, '>Participantes<'),
            'la tabla no declara las columnas Estado, Prioridad, Fecha, Titulo y Participantes');
        $this->addCase($results, 'listado_tabla_filas', substr_count($tabla, 'listTableDataView(') === 2 && str_contains($tabla, 'Tarea Futura') && str_contains($tabla, 'Tarea Vencida'),
            'la tabla debe tener una fila por tarea (aunque tenga varios participantes)');
        $this->addCase($results, 'listado_tabla_id_cifrado', !str_contains($tabla, "listTableDataView('55')") && !str_contains($tabla, "listTableDataDel('55'"),
            'la tabla expone idKanban sin cifrar');
        $this->addCase($results, 'listado_tabla_participantes',
            str_contains($tabla, 'data-title="Ana Prueba"') && str_contains($tabla, '/admin/public/upload/ana.png') && str_contains($tabla, $BASE.'/img/profile-img.jpg'),
            'la tabla no muestra los participantes con su foto o la imagen por defecto');
        $vacioT = $this->datosSinteticos(4);
        $vacioT['arrTareas'] = [];
        $tablaVacia = $this->renderizarVista($rutaTable, $vacioT, $BASE, $f3);
        $this->addCase($results, 'listado_tabla_vacia', $tablaVacia !== '' && str_contains($tablaVacia, '<tbody>') && !str_contains($tablaVacia, 'listTableDataView('),
            'la tabla sin resultados pinta filas o no se dibuja');

        /*====================================*/
        // Formulario de busqueda
        /*====================================*/
        $buscar = $this->renderizarVista($rutaForm, $this->datosSinteticos(1), $BASE, $f3);
        $this->addCase($results, 'busqueda_render', $buscar !== '', 'la vista formSearch no produjo HTML');
        $this->addCase($results, 'busqueda_form', str_contains($buscar, 'id="FormSearchData"') && str_contains($buscar, 'id="formSearch"'),
            'no se dibuja el formulario de busqueda colapsable');
        $this->addCase($results, 'busqueda_inputs',
            str_contains($buscar, 'id="Search_idPrioridad"') && str_contains($buscar, 'id="Search_Fecha"') && str_contains($buscar, 'id="Search_Titulo"') && str_contains($buscar, 'id="Search_idEstadoCierre"'),
            'el formulario no declara idPrioridad, Fecha, Titulo e idEstadoCierre con prefijo Search_');
        $this->addCase($results, 'busqueda_catalogos', str_contains($buscar, 'Prioridad Prueba') && str_contains($buscar, 'Cerrada Prueba'),
            'el filtro no ofrece los catalogos de prioridades y estados de cierre');
        $this->addCase($results, 'busqueda_endpoint',
            str_contains($buscar, $BASE.'/'.self::RUTA_WEB.'/search') && str_contains($buscar, "let Metodo      = 'POST'") && str_contains($buscar, "UpdateDivFrom : 'listTableDataList'"),
            'el formulario no envia por POST a /search ni refresca listTableDataList');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              VISTAS: FORMULARIOS                           */
    /******************************************************************************/
    /**
     * Renderiza `kanbanTareas-formNew.php` y `-formEdit.php` con datos sinteticos: modal,
     * campos, ocultos de sistema (tablero inicial, estado abierto, fechas), filas
     * dinamicas de subtareas y participantes, estado de cierre solo si el tablero lo
     * permite, valores antiguos (Old_*), endpoints con su verbo y variantes del modal.
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
        // Formulario de creacion (modal XL)
        /*====================================*/
        $html = $this->renderizarVista($rutaNew, $this->datosSinteticos(3, 1, 1), $BASE, $f3);
        $this->addCase($results, 'new_render', $html !== '', 'la vista formNew no produjo HTML');
        $this->addCase($results, 'new_modal', str_contains($html, 'id="newFormModal"') && str_contains($html, 'modal-xl'),
            'falta el modal XL de creacion');
        $this->addCase($results, 'new_form', str_contains($html, 'id="FormNewData"'),
            'no se dibuja el formulario de creacion');
        $this->addCase($results, 'new_inputs',
            str_contains($html, 'id="NewTarea_idPrioridad"') && str_contains($html, 'id="NewTarea_Fecha"')
            && str_contains($html, 'id="NewTarea_Titulo"') && str_contains($html, 'id="NewTarea_Descripcion"'),
            'el formulario no declara Prioridad, Fecha, Titulo y Descripcion con prefijo NewTarea_');
        $this->addCase($results, 'new_catalogo_prioridad', str_contains($html, 'Prioridad Prueba'), 'el formulario no ofrece el catalogo de prioridades');
        $this->addCase($results, 'new_ocultos_sistema',
            preg_match('/name="idKanbanEstado"[^>]*value="1"|value="1"[^>]*name="idKanbanEstado"/', $html) === 1
            && preg_match('/name="idEstadoCierre"[^>]*value="1"|value="1"[^>]*name="idEstadoCierre"/', $html) === 1
            && !str_contains($html, 'name="FechaCreacion"') && !str_contains($html, 'name="Fecha_Actual"') && !str_contains($html, 'name="Hora_Actual"'),
            'la tarea nueva debe iniciar en el primer tablero (1) y abierta (1); la fecha de creacion, la fecha y la hora no se envian (las pone el servidor)');
        $this->addCase($results, 'new_sin_usuario_en_formulario', !str_contains($html, 'name="idUsuario"'),
            'el formulario no debe enviar idUsuario: el controlador lo toma de la sesion');
        $this->addCase($results, 'new_filas_dinamicas',
            str_contains($html, 'name="Tarea[]"') && str_contains($html, 'name="idParticipante[]"') && str_contains($html, 'tarea_add()') && str_contains($html, 'participante_add()')
            && str_contains($html, 'Usuario Prueba'),
            'el formulario no ofrece agregar subtareas (Tarea[]) y participantes (idParticipante[]) con el catalogo de usuarios');
        $this->addCase($results, 'new_sin_trabajo_si_no_se_usa', !str_contains($html, 'name="idTrabajo[]"'),
            'sin tareas especificas no debe ofrecerse el trabajo de cada subtarea');
        $htmlTrabajo = $this->renderizarVista($rutaNew, $this->datosSinteticos(3, 1, 2), $BASE, $f3);
        $this->addCase($results, 'new_con_trabajo_si_se_usa', str_contains($htmlTrabajo, 'name="idTrabajo[]"') && str_contains($htmlTrabajo, 'Trabajo Catalogo'),
            'con tareas especificas debe ofrecerse el trabajo de cada subtarea con su catalogo');
        $this->addCase($results, 'new_endpoint_creacion',
            str_contains($html, "let Direccion   = '".$BASE.'/'.self::RUTA_WEB."'") && str_contains($html, "let Metodo      = 'POST'"),
            'el formulario no envia por POST a '.self::RUTA_WEB);
        $this->addCase($results, 'new_actualiza_tablero',
            str_contains($html, $BASE.'/'.self::RUTA_WEB.'/updateList') && str_contains($html, "closeModal:'#newFormModal'") && str_contains($html, "callFNC:'call_1'") && str_contains($html, "ClearForm:'FormNewData'"),
            'el formulario no refresca el tablero (rearmando el arrastre), cierra el modal ni limpia el formulario');
        $this->addCase($results, 'new_quita_filas', str_contains($html, 'remove_tarea') && str_contains($html, 'remove_participante'),
            'el formulario no permite quitar subtareas y participantes agregados');
        $this->addCase($results, 'new_titulo_sin_subtitulo', str_contains($html, '</i> Crear Nuevo') && !str_contains($html, 'modal-subtitle'),
            'con sistemaModalSubtitle = 1 no debe dibujarse el subtitulo');
        $html2 = $this->renderizarVista($rutaNew, $this->datosSinteticos(3, 1, 1, 2), $BASE, $f3);
        $this->addCase($results, 'new_subtitulo_modal', str_contains($html2, 'modal-subtitle') && str_contains($html2, 'Permite crear un nuevo elemento'),
            'con sistemaModalSubtitle = 2 debe dibujarse el subtitulo');

        /*====================================*/
        // Formulario de edicion: estado de cierre solo si el tablero lo permite
        /*====================================*/
        $datos = $this->datosSinteticos(2);
        $datos['rowData']['idCierre'] = 1;
        $html = $this->renderizarVista($rutaEdit, $datos, $BASE, $f3);
        $this->addCase($results, 'edit_render', $html !== '', 'la vista formEdit no produjo HTML');
        $this->addCase($results, 'edit_form', str_contains($html, 'id="FormEditData"'), 'no se dibuja el formulario de edicion');
        $this->addCase($results, 'edit_inputs',
            str_contains($html, 'id="EditTarea_idPrioridad"') && str_contains($html, 'id="EditTarea_Fecha"') && str_contains($html, 'id="EditTarea_Titulo"') && str_contains($html, 'id="EditTarea_Descripcion"'),
            'el formulario no declara Prioridad, Fecha, Titulo y Descripcion con prefijo EditTarea_');
        $this->addCase($results, 'edit_valores_precargados',
            str_contains($html, 'Tarea Sintetica Uno') && str_contains($html, 'Descripcion sintetica de la tarea') && str_contains($html, '2099-01-02'),
            'el formulario no precarga titulo, descripcion y fecha');
        $this->addCase($results, 'edit_estado_cierre_si_permite',
            str_contains($html, 'id="EditTarea_idEstadoCierre"') && str_contains($html, 'Cerrada Prueba') && str_contains($html, 'name="Old_idEstadoCierre"'),
            'si el tablero permite el cierre debe ofrecerse el estado de cierre con su valor antiguo');
        $datos['rowData']['idCierre'] = 2;
        $htmlSinCierre = $this->renderizarVista($rutaEdit, $datos, $BASE, $f3);
        $this->addCase($results, 'edit_sin_estado_cierre_si_no_permite',
            !str_contains($htmlSinCierre, 'EditTarea_idEstadoCierre') && !str_contains($htmlSinCierre, 'Old_idEstadoCierre') && str_contains($htmlSinCierre, 'id="FormEditData"'),
            'si el tablero no permite el cierre no debe ofrecerse el estado de cierre');
        $this->addCase($results, 'edit_ocultos_antiguos',
            str_contains($html, 'name="idKanban"') && str_contains($html, 'name="Old_idPrioridad"') && str_contains($html, 'name="Old_Fecha"')
            && str_contains($html, 'name="Old_Titulo"') && str_contains($html, 'name="Old_Descripcion"') && !str_contains($html, 'name="Fecha_Actual"') && !str_contains($html, 'name="Hora_Actual"'),
            'el formulario debe enviar idKanban y los valores antiguos (Old_*), y no la fecha ni la hora (las pone el servidor)');
        $this->addCase($results, 'edit_sin_usuario_en_formulario', !str_contains($html, 'name="idUsuario"'),
            'el formulario no debe enviar idUsuario: el controlador lo toma de la sesion');
        $this->addCase($results, 'edit_endpoint_update',
            str_contains($html, "let Direccion   = '".$BASE.'/'.self::RUTA_WEB."/update'") && str_contains($html, "let Metodo      = 'POST'"),
            'el formulario no envia por POST a '.self::RUTA_WEB_UPDATE);
        $this->addCase($results, 'edit_actualiza_tablero_y_detalle',
            str_contains($html, $BASE.'/'.self::RUTA_WEB.'/updateList') && str_contains($html, "callFNC:'call_1'") && str_contains($html, $BASE.'/'.self::RUTA_WEB.'/view/')
            && str_contains($html, "{Div:'#modalContent-xl'") && !str_contains($html, "{Div:'#modalContent',"),
            'el formulario debe refrescar el tablero y el detalle en #modalContent-xl (el contenedor que existe)');
        $this->addCase($results, 'edit_volver_id_cifrado', str_contains($html, "listTableDataView('") && !str_contains($html, "listTableDataView('55')"),
            'el boton Volver debe abrir el detalle con el ID cifrado');
        $html2 = $this->renderizarVista($rutaEdit, $this->datosSinteticos(2, 1, 1, 2), $BASE, $f3);
        $this->addCase($results, 'edit_subtitulo_modal', str_contains($html2, 'modal-subtitle') && str_contains($html2, 'Permite editar un elemento existente'),
            'con sistemaModalSubtitle = 2 debe dibujarse el subtitulo');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                          VISTAS: DETALLE E IMPRESION                       */
    /******************************************************************************/
    /**
     * Renderiza el detalle (`kanbanTareas-View.php`) y la vista compartida de impresion
     * (`common-Print.php`) con datos sinteticos: acciones por nivel y por estado de cierre
     * de la tarea, subtareas con y sin trabajo, participantes, historial, enlace de
     * impresion cifrado y variantes del modal.
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function vistasDetalleTest($f3){

        $results = [];
        $BASE    = (string)$f3->get('BASE');
        if (trim($BASE) === '') {
            $BASE = '/admin';
        }
        $rutaView  = $this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php');
        $rutaPrint = $this->rutaModulo('../views/common-Print.php');

        /*====================================*/
        // Detalle de una tarea abierta (nivel 2: se puede editar)
        /*====================================*/
        $html = $this->renderizarVista($rutaView, $this->datosDetalle(2, 1, 1, 1), $BASE, $f3);
        $this->addCase($results, 'view_render', $html !== '', 'la vista View no produjo HTML');
        $this->addCase($results, 'view_datos_tarea',
            str_contains($html, 'Tarea Sintetica Uno') && str_contains($html, 'Descripcion sintetica de la tarea') && (str_contains($html, '02-01-2099') || str_contains($html, '02/01/2099')),
            'el detalle no muestra titulo, descripcion y fecha de la tarea');
        $this->addCase($results, 'view_badges',
            str_contains($html, 'Prioridad: Prioridad Prueba') && str_contains($html, 'Tablero: Estado Prueba') && str_contains($html, 'Estado Cierre: Abierta Prueba'),
            'el detalle no muestra prioridad, tablero y estado de cierre');
        $this->addCase($results, 'view_participantes',
            str_contains($html, 'Ana Prueba') && str_contains($html, '/admin/public/upload/ana.png') && str_contains($html, 'Luis Sinfoto') && str_contains($html, $BASE.'/img/profile-img.jpg'),
            'el detalle no lista los participantes con su foto o la imagen por defecto');
        $this->addCase($results, 'view_historial',
            str_contains($html, 'Historial') && str_contains($html, '10:30:00') && str_contains($html, 'Linea uno<br>Linea dos'),
            'el detalle no muestra el historial con sus saltos de linea');
        $this->addCase($results, 'view_imprimir_cifrado',
            str_contains($html, self::RUTA_WEB.'/print/') && !str_contains($html, self::RUTA_WEB.'/print/55"'),
            'el enlace de impresion no existe o expone idKanban sin cifrar');
        $this->addCase($results, 'view_acciones_tarea_abierta_nivel2',
            str_contains($html, 'listTableDataEdit(') && str_contains($html, 'tareas_Add(') && str_contains($html, 'tareas_Edit(')
            && str_contains($html, 'participantes_Add(') && str_contains($html, 'participantes_Del('),
            'una tarea abierta con nivel 2 debe ofrecer editar, agregar/editar subtareas y agregar/borrar participantes');
        $participantes = (preg_match_all("/participantes_Del\\('([^']+)', '([^']+)'\\)/", $html, $m) > 0) ? $m : [[], [], []];
        $idTarea       = (preg_match('#'.preg_quote(self::RUTA_WEB, '#').'/print/([^"]+)"#', $html, $mp) === 1) ? $mp[1] : '';
        $this->addCase($results, 'view_borrar_participante_ids',
            count($participantes[1]) === 2 && $idTarea !== ''
            && $participantes[1][0] !== $participantes[1][1] && $participantes[2][0] === $idTarea && $participantes[2][1] === $idTarea
            && !in_array($idTarea, $participantes[1], true),
            'participantes_Del() debe recibir el ID del participante y el ID de la tarea (distintos): antes se pasaba dos veces el del participante');
        $this->addCase($results, 'view_ids_cifrados_en_acciones',
            !str_contains($html, "tareas_Edit('1')") && !str_contains($html, "participantes_Del('1'") && !str_contains($html, "tareas_Add('55')") && !str_contains($html, "listTableDataEdit('55')"),
            'las acciones del detalle exponen identificadores sin cifrar');

        /*====================================*/
        // Matriz de niveles y estado de cierre: las acciones exigen nivel >= 2 y tarea abierta
        /*====================================*/
        foreach (self::NIVELES as $nivel) {
            $abierta = $this->renderizarVista($rutaView, $this->datosDetalle($nivel, 1, 1, 1), $BASE, $f3);
            $cerrada = $this->renderizarVista($rutaView, $this->datosDetalle($nivel, 1, 1, 2), $BASE, $f3);
            $acciones = ['listTableDataEdit(', 'tareas_Add(', 'tareas_Edit(', 'participantes_Add(', 'participantes_Del('];
            $okAbierta = true;
            $okCerrada = true;
            foreach ($acciones as $accion) {
                if (str_contains($abierta, $accion) !== ($nivel >= 2)) {
                    $okAbierta = false;
                }
                if (str_contains($cerrada, $accion)) {
                    $okCerrada = false;
                }
            }
            $this->addCase($results, 'view_acciones_tarea_abierta_nivel'.$nivel, $okAbierta,
                'con tarea abierta las acciones de edicion deben mostrarse solo con nivel >= 2 (nivel '.$nivel.')');
            $this->addCase($results, 'view_sin_acciones_tarea_cerrada_nivel'.$nivel, $okCerrada && str_contains($cerrada, 'Estado Cierre'),
                'una tarea cerrada no debe ofrecer acciones de edicion (nivel '.$nivel.')');
        }

        /*====================================*/
        // Subtareas con y sin trabajo especifico y variantes del modal
        /*====================================*/
        $this->addCase($results, 'view_tareas_sin_trabajo',
            str_contains($html, 'Subtarea Uno') && !str_contains($html, 'Trabajo Prueba') && str_contains($html, 'bi bi-check'),
            'sin tareas especificas el detalle debe mostrar la subtarea con su estado y sin el trabajo');
        $htmlTrabajo = $this->renderizarVista($rutaView, $this->datosDetalle(2, 2, 2, 1), $BASE, $f3);
        $this->addCase($results, 'view_tareas_con_trabajo', str_contains($htmlTrabajo, 'Trabajo Prueba') && str_contains($htmlTrabajo, 'Subtarea Uno'),
            'con tareas especificas el detalle debe mostrar el trabajo y la subtarea');
        $this->addCase($results, 'view_subtitulo_modal', str_contains($htmlTrabajo, 'modal-subtitle') && str_contains($htmlTrabajo, 'Permite visualizar los datos'),
            'con sistemaModalSubtitle = 2 debe dibujarse el subtitulo');
        $this->addCase($results, 'view_boton_cerrar', str_contains($html, 'data-bs-dismiss="modal"') && str_contains($html, 'modal-footer'),
            'con sistemaModalCloseBTN = 2 debe dibujarse el pie con el boton Cerrar');
        $sinCerrar = $this->datosDetalle(2, 1, 1, 1);
        $sinCerrar['UserData']['sistemaModalCloseBTN'] = 1;
        $htmlSinCerrar = $this->renderizarVista($rutaView, $sinCerrar, $BASE, $f3);
        $this->addCase($results, 'view_sin_boton_cerrar', !str_contains($htmlSinCerrar, 'modal-footer') && str_contains($htmlSinCerrar, 'max-height: 80vh'),
            'con sistemaModalCloseBTN distinto de 2 no debe dibujarse el pie del modal');
        $vacio = $this->datosDetalle(2);
        $vacio['arrTareas'] = [];
        $vacio['arrParticipantes'] = [];
        $vacio['arrHistorial'] = [];
        $htmlVacio = $this->renderizarVista($rutaView, $vacio, $BASE, $f3);
        $this->addCase($results, 'view_listas_vacias', $htmlVacio !== '' && str_contains($htmlVacio, 'Tarea Sintetica Uno'),
            'el detalle de una tarea sin subtareas, participantes ni historial no se dibuja');

        /*====================================*/
        // Impresion: vista compartida common-Print.php (se renderiza directamente, no su envoltorio)
        /*====================================*/
        $print = $this->renderizarVista($rutaPrint, $this->datosDetalle(1, 1), $BASE, $f3);
        $this->addCase($results, 'print_render', $print !== '', 'common-Print.php no produjo HTML');
        $this->addCase($results, 'print_datos',
            str_contains($print, 'Tarea Sintetica Uno') && str_contains($print, 'Prioridad: Prioridad Prueba') && str_contains($print, 'Descripcion sintetica de la tarea')
            && str_contains($print, 'Subtarea Uno') && str_contains($print, 'Ana Prueba') && str_contains($print, 'Luis Sinfoto'),
            'la impresion no muestra los datos de la tarea, sus subtareas y participantes');
        $this->addCase($results, 'print_sin_historial_ni_acciones',
            !str_contains($print, 'Historial') && !str_contains($print, 'onclick=') && !str_contains($print, 'Trabajo Prueba'),
            'la impresion no debe incluir historial ni botones de accion');
        $printTrabajo = $this->renderizarVista($rutaPrint, $this->datosDetalle(1, 2), $BASE, $f3);
        $this->addCase($results, 'print_con_trabajo', str_contains($printTrabajo, 'Trabajo Prueba') && str_contains($printTrabajo, 'Subtarea Uno'),
            'con tareas especificas la impresion debe mostrar el trabajo y la subtarea');
        $this->addCase($results, 'print_envoltorio_delega',
            str_contains($this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-Print.php')), "require_once('common-Print.php')"),
            'kanbanTareas-Print.php no delega en common-Print.php');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos los tres `dataCheck_N()` por reflexion y el chequeo real de
     * `CheckData` (tarea, subtareas e historial), el cifrado de identificadores y los
     * metodos heredados de busqueda y agrupacion.
     *
     * @return array Resultado por caso
     */
    public function logicaValidacionTest(){

        $results = [];

        /*====================================*/
        // dataCheck_1/2/3: contrato esperado por Base_insert/Base_update
        /*====================================*/
        $contratos = [
            'dataCheck_1' => ['numeros' => 'idKanban,idKanbanEstado,idEstadoCierre,idPrioridad,idUsuario', 'fechas' => 'Fecha,FechaCreacion', 'min' => 'Titulo,Descripcion', 'max' => 'Titulo', 'censura' => 'Titulo,Descripcion'],
            'dataCheck_2' => ['numeros' => 'idKanban,idEstadoTrabajo,idTrabajo,idUsuario',                'fechas' => '',                    'min' => 'Tarea',              'max' => 'Tarea',  'censura' => 'Tarea'],
            'dataCheck_3' => ['numeros' => 'idKanban,idUsuario',                                          'fechas' => 'Fecha',               'min' => 'Descripcion',        'max' => '',       'censura' => 'Descripcion'],
        ];
        foreach ($contratos as $metodo => $esperado) {
            try {
                $reflector = new ReflectionMethod(self::CONTROLADOR, $metodo);
                $reflector->setAccessible(true);
                $Controller = (new ReflectionClass(self::CONTROLADOR))->newInstanceWithoutConstructor();
                $Post       = ['idKanban' => 1];
                $DataCheck  = $reflector->invoke($Controller, $Post);

                $this->addCase($results, $metodo.'_privado', $reflector->isPrivate(), $metodo.'() debe ser privado');
                $this->addCase($results, $metodo.'_conserva_post', is_array($DataCheck) && ($DataCheck['Post'] ?? null) === $Post,
                    $metodo.'() no conserva el POST recibido');
                $this->addCase($results, $metodo.'_claves_contrato',
                    isset($DataCheck['emptyData'], $DataCheck['ValidarNumero'], $DataCheck['ValidarEntero'], $DataCheck['ValidarFecha'], $DataCheck['ValidarLargoMinimoN'], $DataCheck['ValidarLargoMaximoPorCampo']),
                    $metodo.'() no entrega las claves del contrato de CheckData');
                $this->addCase($results, $metodo.'_numeros_enteros',
                    $DataCheck['ValidarNumero'] === $esperado['numeros'] && $DataCheck['ValidarEntero'] === $esperado['numeros'],
                    $metodo.'() no valida '.$esperado['numeros'].' como numeros enteros');
                $this->addCase($results, $metodo.'_fechas', $DataCheck['ValidarFecha'] === $esperado['fechas'],
                    $metodo.'() no valida las fechas esperadas ('.$esperado['fechas'].')');
                $this->addCase($results, $metodo.'_largos',
                    $DataCheck['ValidarLargoMinimo'] === $esperado['min']
                    && (int)$DataCheck['ValidarLargoMinimoN'] === 3
                    && ($esperado['max'] === ''
                        ? $DataCheck['ValidarLargoMaximoPorCampo'] === []
                        : (int)($DataCheck['ValidarLargoMaximoPorCampo'][$esperado['max']] ?? 0) === 255),
                    $metodo.'() no limita los textos a 3..255 caracteres');
                $this->addCase($results, $metodo.'_censura', $DataCheck['ValidarPalabrasCensuradas'] === $esperado['censura'],
                    $metodo.'() no filtra palabras censuradas en '.$esperado['censura']);
            } catch (Throwable $e) {
                $this->addCase($results, $metodo.'_privado', false, 'no se pudo ejecutar '.$metodo.'() ('.$e->getMessage().')');
            }
        }

        /*====================================*/
        // CheckData real sobre dataCheck_1 (tarea)
        /*====================================*/
        try {
            $tarea = ['idKanbanEstado' => 1, 'idEstadoCierre' => 1, 'idPrioridad' => 2, 'idUsuario' => 3, 'Fecha' => '2099-12-31', 'FechaCreacion' => '2026-01-02', 'Titulo' => 'Titulo de prueba', 'Descripcion' => 'Descripcion de prueba'];
            $this->addCase($results, 'tarea_acepta_valida', ($this->chequeo('dataCheck_1', $tarea)['status'] ?? false) === true,
                'CheckData rechazo una tarea valida');
            $casos = [
                'tarea_rechaza_titulo_corto'     => ['Titulo' => 'ab'],
                'tarea_rechaza_titulo_largo'     => ['Titulo' => str_repeat('a', 256)],
                'tarea_rechaza_descripcion_corta'=> ['Descripcion' => 'ab'],
                'tarea_rechaza_fecha_invalida'   => ['Fecha' => '2026-13-45'],
                'tarea_rechaza_prioridad_texto'  => ['idPrioridad' => 'x'],
                'tarea_rechaza_estado_decimal'   => ['idKanbanEstado' => '1.5'],
            ];
            foreach ($casos as $caso => $cambio) {
                $this->addCase($results, $caso, ($this->chequeo('dataCheck_1', array_merge($tarea, $cambio))['status'] ?? true) === false,
                    'CheckData acepto una tarea con '.json_encode($cambio));
            }
        } catch (Throwable $e) {
            $this->addCase($results, 'tarea_acepta_valida', false, 'no se pudo ejecutar el chequeo de la tarea ('.$e->getMessage().')');
        }

        /*====================================*/
        // CheckData real sobre dataCheck_2 (subtareas) y dataCheck_3 (historial)
        /*====================================*/
        try {
            $sub = ['idKanban' => 1, 'idEstadoTrabajo' => 1, 'idTrabajo' => 4, 'Tarea' => 'Hacer algo'];
            $this->addCase($results, 'subtarea_acepta_valida', ($this->chequeo('dataCheck_2', $sub)['status'] ?? false) === true,
                'CheckData rechazo una subtarea valida');
            $this->addCase($results, 'subtarea_rechaza_texto_corto', ($this->chequeo('dataCheck_2', array_merge($sub, ['Tarea' => 'ab']))['status'] ?? true) === false,
                'CheckData acepto una subtarea de 2 caracteres');
            $this->addCase($results, 'subtarea_rechaza_trabajo_texto', ($this->chequeo('dataCheck_2', array_merge($sub, ['idTrabajo' => 'x']))['status'] ?? true) === false,
                'CheckData acepto un idTrabajo no numerico');

            $his = ['idKanban' => 1, 'idUsuario' => 3, 'Descripcion' => 'Tarea Creada', 'Fecha' => '2026-01-02', 'Hora' => '10:30:00'];
            $this->addCase($results, 'historial_acepta_valido', ($this->chequeo('dataCheck_3', $his)['status'] ?? false) === true,
                'CheckData rechazo un registro de historial valido');
            $this->addCase($results, 'historial_rechaza_descripcion_corta', ($this->chequeo('dataCheck_3', array_merge($his, ['Descripcion' => 'ab']))['status'] ?? true) === false,
                'CheckData acepto una descripcion de historial de 2 caracteres');
            $this->addCase($results, 'historial_rechaza_hora_invalida', ($this->chequeo('dataCheck_3', array_merge($his, ['Hora' => '99:99:99']))['status'] ?? true) === false,
                'CheckData acepto una hora invalida en el historial');
            $this->addCase($results, 'historial_rechaza_fecha_invalida', ($this->chequeo('dataCheck_3', array_merge($his, ['Fecha' => '2026-02-31']))['status'] ?? true) === false,
                'CheckData acepto una fecha invalida en el historial');
        } catch (Throwable $e) {
            $this->addCase($results, 'subtarea_acepta_valida', false, 'no se pudo ejecutar el chequeo de subtareas e historial ('.$e->getMessage().')');
        }

        /*====================================*/
        // Cifrado de identificadores: ida y vuelta estable y rechazo de basura
        /*====================================*/
        try {
            $Codification = new FunctionsSecurityCodification();
            $cifrado      = $Codification->encryptDecrypt('encrypt', '55');
            $this->addCase($results, 'cifrado_genera_dato',
                ($cifrado['success'] ?? false) === true && !empty($cifrado['data']),
                'encryptDecrypt(encrypt) no produjo un valor cifrado');

            $descifrado = $Codification->encryptDecrypt('decrypt', (string)($cifrado['data'] ?? ''));
            $this->addCase($results, 'cifrado_ida_y_vuelta',
                ($descifrado['success'] ?? false) === true && (string)($descifrado['data'] ?? '') === '55',
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
        // Metodos heredados y agrupacion de filas por ID (tablero)
        /*====================================*/
        foreach (['searchWhere', 'searchValidateDates'] as $metodo) {
            $this->addCase($results, 'busqueda_heredada_'.$metodo, method_exists('ControllerBase', $metodo),
                'ControllerBase no ofrece '.$metodo.'()');
        }
        try {
            $CommonData = new FunctionsCommonData();
            $agrupado   = $CommonData->agruparPorClave([
                ['ID' => 1, 'UsuarioNombre' => 'A'], ['ID' => 1, 'UsuarioNombre' => 'B'], ['ID' => 2, 'UsuarioNombre' => 'C'],
            ], 'ID');
            $this->addCase($results, 'agrupar_por_id',
                is_array($agrupado) && count($agrupado) === 2 && count($agrupado[1] ?? []) === 2 && count($agrupado[2] ?? []) === 1,
                'agruparPorClave() no agrupa las filas de una tarea con varios participantes');
        } catch (Throwable $e) {
            $this->addCase($results, 'agrupar_por_id', false, 'no se pudo ejecutar agruparPorClave() ('.$e->getMessage().')');
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                 SEGURIDAD                                  */
    /******************************************************************************/
    /**
     * Revisa estaticamente las vistas y el controlador de tareas: rutas relativas, rutas
     * absolutas, acceso a sesion, SQL embebido, cifrado de IDs, validacion de formularios
     * y consultas preparadas.
     *
     * @return array Resultado por caso
     */
    public function seguridadVistasTest(){

        $results = [];

        /*====================================*/
        // Vistas involucradas (las 8 propias mas la compartida common-Print.php)
        /*====================================*/
        $arrVistas = glob($this->rutaModulo('../views/'.self::CONTROLADOR.'-*.php')) ?: [];
        $this->addCase($results, 'hay_vistas', count($arrVistas) === 8,
            'se esperaban 8 vistas de kanbanTareas y se encontraron '.count($arrVistas));
        $arrVistas[] = $this->rutaModulo('../views/common-Print.php');
        $arrVistas[] = $this->rutaModulo('../views/kanbanEstados-formNew.php');

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
        // Cifrado de los IDs y acciones por nivel
        /*====================================*/
        $tablero = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'));
        $this->addCase($results, 'tablero_ids_cifrados',
            str_contains($tablero, "encryptDecrypt('encrypt', \$crud['idKanbanEstado'])") && str_contains($tablero, "encryptDecrypt('encrypt', \$Tareas)"),
            'UpdateList no cifra idKanbanEstado ni el ID de la tarea');
        $this->addCase($results, 'tablero_acciones_por_nivel',
            str_contains($tablero, '$level >= 1') && str_contains($tablero, '$level >= 2') && str_contains($tablero, '$level >= 4'),
            'UpdateList no condiciona Ver, Editar y Borrar a los niveles 1, 2 y 4');
        $this->addCase($results, 'tablero_escapa_nombres_js',
            str_contains($tablero, "addslashes(\$crud['Nombre'])") && str_contains($tablero, "addslashes(\$tarea[0]['Titulo'])"),
            'UpdateList no escapa los nombres que pasa a los onclick de borrar');
        $listado = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateTableList.php'));
        $this->addCase($results, 'listado_ids_cifrados', str_contains($listado, "encryptDecrypt('encrypt', \$Tareas)"),
            'UpdateTableList no cifra el ID de la tarea');
        $this->addCase($results, 'listado_escapa_titulo_js', str_contains($listado, "addslashes(\$tarea[0]['Titulo'])"),
            'UpdateTableList no escapa el titulo que pasa al onclick de borrar');
        $detalle = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'));
        $this->addCase($results, 'detalle_ids_cifrados',
            str_contains($detalle, "encryptDecrypt('encrypt', \$data['rowData']['idKanban'])") && str_contains($detalle, "encryptDecrypt('encrypt', \$task['idTareas'])")
            && str_contains($detalle, "encryptDecrypt('encrypt', \$task['idParticipantes'])"),
            'View no cifra idKanban, idTareas ni idParticipantes en las acciones');
        $this->addCase($results, 'detalle_acciones_por_nivel_y_cierre',
            substr_count($detalle, "\$data['UserAccess']['LevelAccess']>=2") >= 5 && substr_count($detalle, "\$data['rowData']['idEstadoCierre']==1") >= 5,
            'View no condiciona las acciones de edicion al nivel 2 y a la tarea abierta');
        $lista = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php'));
        $this->addCase($results, 'lista_crear_por_nivel', str_contains($lista, "['LevelAccess']>=3"),
            'List no condiciona los botones Crear al nivel 3');
        $formNew = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-formNew.php'));
        $formEdit = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-formEdit.php'));
        $this->addCase($results, 'formularios_sin_usuario_oculto', !str_contains($formNew, "'Name' => 'idUsuario'") && !str_contains($formEdit, "'Name' => 'idUsuario'"),
            'los formularios de crear y editar no deben enviar idUsuario (lo toma el controlador de la sesion)');
        $this->addCase($results, 'formulario_new_numero_aleatorio', str_contains($formNew, "'room_'.rand(") && str_contains($formNew, "'rand_'.rand("),
            'formNew debe usar identificadores aleatorios para las filas dinamicas');

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
        $this->addCase($results, 'controlador_consultas_con_params',
            preg_match_all('/\$this->Base_Get(?:List|ByID)\(/', $controlador) === preg_match_all("/'params'\s*=>/", $controlador),
            'cada consulta de lectura (Base_GetList/Base_GetByID) debe declarar la clave params');
        $this->addCase($results, 'controlador_where_con_params', substr_count($controlador, "\$r['params']") === 3,
            'los tres searchWhere() deben acumular sus parametros preparados');
        $this->addCase($results, 'controlador_sin_get_ni_request', !str_contains($controlador, '$_GET') && !str_contains($controlador, '$_REQUEST'),
            'el controlador lee $_GET o $_REQUEST directamente');
        $this->addCase($results, 'controlador_usuario_sesion_en_escrituras', substr_count($controlador, "SESSION.DataInfo.UserID") === 3,
            'Insert(), Update() y ChangeStatus() deben tomar el usuario de la sesion');

        /************************************/
        // Retorno los datos
        return $results;

    }

}
