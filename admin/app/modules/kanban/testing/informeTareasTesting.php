<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class informeTareasTesting
 *
 * Pruebas del modulo `kanban` - controlador `informeTareas` (informe de tareas, solo lectura).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. Los textos de los casos
 *    correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Alcance: pruebas NO destructivas. El controlador no escribe en base de datos; aun asi sus
 * metodos consultan la BD y terminan en `showVista()`/`Response::error()`, por lo que no se
 * invocan: se verifican por reflexion y lectura del fuente, y se renderizan las vistas con
 * datos sinteticos. A diferencia de otros informes, `view` y `print` son atendidos por este
 * mismo controlador. La vista `-Print` solo hace `require_once('common-Print.php')`, vista
 * compartida con `kanbanTareas`: por eso se renderiza `common-Print.php` directamente y el
 * envoltorio solo se verifica por fuente. OJO: un `include` previo de un archivo vuelve un
 * `require_once` posterior de ese mismo archivo en un no-op (PHP los registra como incluidos),
 * asi que otras suites de este modulo que se ejecuten despues en la misma peticion del runner
 * deben renderizar `common-Print.php` directamente y no sus envoltorios `-Print`.
 *
 * @see skills/testing/SKILL.md
 */
class informeTareasTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR      = 'informeTareas';
    private const INSTALADOR       = 'kanbanTareasInstaller';
    private const RUTA_WEB         = 'gestionProyectos/kanban/informeTareas';
    private const RUTA_WEB_LISTADO = 'gestionProyectos/kanban/informeTareas/listAll';
    private const RUTA_WEB_SEARCH  = 'gestionProyectos/kanban/informeTareas/search';
    private const RUTA_WEB_VIEW    = 'gestionProyectos/kanban/informeTareas/view/@id';
    private const RUTA_WEB_PRINT   = 'gestionProyectos/kanban/informeTareas/print/@id';
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
    // Datos sinteticos usados por las vistas del controlador
    /*******************************************************************/
    private function datosSinteticos(int $nivel, int $usoTareas = 1): array{
        return [
            'PageTitle'        => 'Informes',
            'PageDescription'  => 'Testeos de Informes.',
            'PageAuthor'       => 'Pruebas',
            'PageKeywords'     => 'Pruebas',
            'TableTitle'       => 'Listado de Tareas',
            'UserData'         => [
                'MainPathUrl'          => '/admin/public/upload/',
                'sistemaModalSubtitle' => 1,
                'sistemaModalCloseBTN' => 2,
                'UserType'             => 2,
                'KanbanTareasUsoTareas'=> $usoTareas,
            ],
            'UserAccess'       => ['LevelAccess' => $nivel, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_FormInputs'   => new UIFormInputs(),
            'Fnc_Codification' => new FunctionsSecurityCodification(),
            'Fnc_DataDate'     => new FunctionsDataDate(),
            'Fnc_WidgetsCommon'=> new UIWidgetsCommon(),
            'Fnc_WidgetsViews'  => new UIWidgetsViews(),
            'arrPrioridad'     => [['ID' => 1, 'Nombre' => 'Prioridad Prueba']],
            'arrEstadoCierre'  => [['ID' => 1, 'Nombre' => 'Cierre Prueba']],
            // Tareas agrupadas por ID (como las entrega CommonData::agruparPorClave)
            'arrTareas'        => [
                55 => [
                    [
                        'ID'              => 55,
                        'idKanban'        => 55,
                        'idKanbanEstado'  => 3,
                        'PrioridadNombre' => 'Prioridad Prueba',
                        'PrioridadColor'  => 'danger',
                        'Fecha'           => '2026-01-02',
                        'Titulo'          => 'Tarea Sintetica Uno',
                        'UsuarioNombre'   => 'Ana Prueba',
                        'UsuarioImg'      => 'ana.png',
                        'KanbanEstado'    => 'Estado Prueba',
                        'KanbanColor'     => 'primary',
                    ],
                    [
                        'ID'              => 55,
                        'idKanban'        => 55,
                        'idKanbanEstado'  => 3,
                        'PrioridadNombre' => 'Prioridad Prueba',
                        'PrioridadColor'  => 'danger',
                        'Fecha'           => '2026-01-02',
                        'Titulo'          => 'Tarea Sintetica Uno',
                        'UsuarioNombre'   => 'Luis Sinfoto',
                        'UsuarioImg'      => '',
                        'KanbanEstado'    => 'Estado Prueba',
                        'KanbanColor'     => 'primary',
                    ],
                ],
            ],
            // Detalle de una tarea (vistas View y Print)
            'rowData'          => [
                'idKanban'           => 55,
                'idEstadoCierre'     => 1,
                'PrioridadNombre'    => 'Prioridad Prueba',
                'PrioridadColor'     => 'danger',
                'Fecha'              => '2026-01-02',
                'Titulo'             => 'Tarea Sintetica Uno',
                'Descripcion'        => 'Descripcion sintetica de la tarea',
                'EstadoCierreNombre' => 'Cierre Prueba',
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
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica los archivos del controlador `informeTareas` y la convencion
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
            'vista_print'       => $this->rutaModulo('../views/'.self::CONTROLADOR.'-Print.php'),
            'vista_common'      => $this->rutaModulo('../views/common-Print.php'),
            'testing'           => $this->rutaModulo('informeTareasTesting.php'),
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
        $this->addCase($results, 'clase_testing_cargada', class_exists('informeTareasTesting'),
            'la clase informeTareasTesting no esta disponible');
        $padre = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padre === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padre.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('informeTareasTesting') === 'ControllerBase',
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
     * Verifica el contrato de `listAll`, `UpdateList`, `View` y `Print`, la busqueda por
     * prioridad/fecha/titulo/estado de cierre, el descifrado del ID y que el informe es
     * de solo lectura (sin metodos de escritura).
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Firma de cada metodo de vista: listAll/UpdateList($f3), View/Print($f3, $params)
        /*====================================*/
        foreach (['listAll', 'UpdateList'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 1, 1);
        }
        foreach (['View', 'Print'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 2, 2);
        }

        /*====================================*/
        // Contrato de presentacion
        /*====================================*/
        foreach (['List' => 'listAll', 'UpdateList' => 'UpdateList', 'View' => 'View', 'Print' => 'Print'] as $vista => $metodo) {
            $this->addCase($results, 'vista_'.$metodo, str_contains($codigo, "\$this->controllerName.'-".$vista.".php'"),
                $metodo.'() no instancia la vista -'.$vista);
        }
        $this->addCase($results, 'tipoview_listAll', str_contains($codigo, 'showVista(1,'),
            'listAll() no usa showVista(1)');
        $this->addCase($results, 'tipoview_UpdateList_View', substr_count($codigo, 'showVista(2,') === 2,
            'UpdateList() y View() deben usar showVista(2)');
        $this->addCase($results, 'tipoview_Print', substr_count($codigo, 'showVista(3,') === 1,
            'Print() no usa showVista(3) (pantalla de impresion)');
        $this->addCase($results, 'rutavista_resuelta', substr_count($codigo, 'returnRutaVista(__DIR__') === 4,
            'las cuatro vistas deben resolver su ruta con returnRutaVista()');
        $this->addCase($results, 'usuario_en_vistas', substr_count($codigo, 'getUserData($f3)') === 4,
            'las vistas no obtienen los datos del usuario');
        $this->addCase($results, 'nivel_en_vistas', substr_count($codigo, 'getArrLevel($f3, $this->controllerName)') === 4,
            'las vistas no resuelven el nivel de acceso');
        $this->addCase($results, 'fallo_consulta_manejado',
            substr_count($codigo, 'showError(2, $f3, $result)') === 3 && substr_count($codigo, 'mergeResponses(') === 4,
            'UpdateList(), View() y Print() deben manejar el fallo de consulta con showError()/mergeResponses()');
        $this->addCase($results, 'listall_maneja_fallo',
            str_contains($codigo, "if(\$arrPrioridad['status'] && \$arrEstadoCierre['status'])") && str_contains($codigo, 'mergeResponses([$arrPrioridad,$arrEstadoCierre])')
            && substr_count($codigo, 'showError(1, $f3, $result)') === 1,
            'listAll() debe comprobar el status de sus catalogos y usar showError(1)/mergeResponses()');
        $this->addCase($results, 'listado_titulo', str_contains($codigo, "'PageTitle'") && str_contains($codigo, "'TableTitle'"),
            'listAll() no asigna PageTitle y TableTitle');

        /*====================================*/
        // Catalogos del filtro (prioridades y estados de cierre)
        /*====================================*/
        $this->addCase($results, 'listado_catalogo_prioridad',
            str_contains($codigo, "'table'   => 'core_prioridades'") && str_contains($codigo, "'data'    => 'idPrioridad AS ID,Nombre'"),
            'listAll() no carga el catalogo de prioridades');
        $this->addCase($results, 'listado_catalogo_estado_cierre',
            str_contains($codigo, "'table'   => 'core_estados_cierre'") && str_contains($codigo, "'data'    => 'idEstadoCierre AS ID,Nombre'"),
            'listAll() no carga el catalogo de estados de cierre');
        $this->addCase($results, 'consultas_acotadas', substr_count($codigo, 'ConfigAPP::APP["N_MaxItems"]') === 8,
            'las 8 consultas de listado deben acotar el numero de filas');

        /*====================================*/
        // Busqueda: columnas, validacion de fechas y WHERE preparado
        /*====================================*/
        $this->addCase($results, 'busqueda_campo_exacto', str_contains($codigo, "\$WhereData_int     = 'idPrioridad,Fecha,idEstadoCierre';"),
            'UpdateList() no filtra por idPrioridad, Fecha e idEstadoCierre');
        $this->addCase($results, 'busqueda_campo_relativo', str_contains($codigo, "\$WhereData_string  = 'Titulo';"),
            'UpdateList() no filtra por Titulo');
        $this->addCase($results, 'busqueda_valida_fechas', str_contains($codigo, 'searchValidateDates('),
            'UpdateList() no valida las fechas con searchValidateDates()');
        $this->addCase($results, 'busqueda_fechas_500', str_contains($codigo, 'Response::error($RespDataBetween, 500)'),
            'UpdateList() no responde 500 ante fechas invalidas');
        $this->addCase($results, 'busqueda_where_preparado', substr_count($codigo, '$this->searchWhere(') === 3,
            'UpdateList() no construye el WHERE con los tres tipos de searchWhere()');
        $this->addCase($results, 'busqueda_tabla', substr_count($codigo, "'kanban_tareas', ") === 3 && str_contains($codigo, "'table'   => 'kanban_tareas',"),
            'UpdateList() no consulta kanban_tareas');
        $this->addCase($results, 'busqueda_joins',
            str_contains($codigo, 'LEFT JOIN kanban_tareas_participantes  ON kanban_tareas_participantes.idKanban = kanban_tareas.idKanban')
            && str_contains($codigo, 'LEFT JOIN usuarios_listado             ON usuarios_listado.idUsuario           = kanban_tareas_participantes.idUsuario'),
            'UpdateList() no une los participantes de cada tarea');
        $this->addCase($results, 'busqueda_params', str_contains($codigo, "'params'  => \$whereParams"),
            'UpdateList() no pasa los parametros preparados del WHERE');
        $this->addCase($results, 'busqueda_agrupa_por_id', str_contains($codigo, "agruparPorClave (\$arrTareas['data'], 'ID' )"),
            'UpdateList() no agrupa las filas por ID de tarea');

        /*====================================*/
        // Detalle e impresion: ID cifrado y consultas por tarea
        /*====================================*/
        $this->addCase($results, 'id_descifrado', substr_count($codigo, "encryptDecrypt('decrypt', \$params['id'])") === 2,
            'View() y Print() deben descifrar el ID de la ruta');
        $this->addCase($results, 'id_validado', substr_count($codigo, "isValidDecrypted(\$KanbanID, 'id')") === 2,
            'View() y Print() deben validar el ID descifrado');
        $this->addCase($results, 'id_invalido_400', substr_count($codigo, "Response::error('Registro inválido', 400)") === 2,
            'un ID invalido debe responder 400 en View() y Print()');
        $this->addCase($results, 'detalle_tablas',
            str_contains($codigo, "'table'   => 'kanban_tareas_tareas'") && str_contains($codigo, "'table'   => 'kanban_tareas_participantes'"),
            'el detalle no consulta las tareas y los participantes');
        $this->addCase($results, 'view_historial', str_contains($codigo, "'table'   => 'kanban_tareas_historial'"),
            'View() no consulta el historial de la tarea');
        $this->addCase($results, 'view_historial_cronologico',
            substr_count($codigo, "'order'   => 'kanban_tareas_historial.Fecha ASC, kanban_tareas_historial.Hora ASC'") === 1,
            'el historial de View() debe ordenarse cronologicamente (Fecha y Hora ascendentes)');
        $this->addCase($results, 'detalle_por_id', substr_count($codigo, "'where'   => 'kanban_tareas.idKanban = ?'") === 2,
            'el detalle de View() y Print() debe filtrar la tarea por idKanban');

        /*====================================*/
        // Solo lectura: sin escritura, transacciones ni respuestas de exito
        /*====================================*/
        foreach (['Insert', 'Update', 'Delete', 'delFiles'] as $metodo) {
            $this->addCase($results, 'solo_lectura_sin_'.$metodo, !method_exists(self::CONTROLADOR, $metodo),
                'el informe expone el metodo de escritura '.$metodo.'()');
        }
        $this->addCase($results, 'solo_lectura_sin_transaccion', !str_contains($codigo, 'Base_transaction') && !str_contains($codigo, 'Base_insert')
            && !str_contains($codigo, 'Base_update') && !str_contains($codigo, 'Base_delete') && !str_contains($codigo, 'Response::success'),
            'el informe contiene operaciones de escritura o transacciones');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                       CONTROLADOR: RUTAS E INSTALADOR                      */
    /******************************************************************************/
    /**
     * Verifica que las rutas del informe publicadas por el instalador apunten a metodos
     * reales con el verbo y el nivel esperados (todas propias del controlador).
     *
     * @return array Resultado por caso
     */
    public function controladorRutasTest(){

        $results = [];
        $rutas   = $this->rutasInstalador();
        $this->addCase($results, 'instalador_rutas_disponibles', count($rutas) > 0,
            'getRouteDefinitions() no devolvio rutas');

        /*====================================*/
        // idMetodo: 1=GET, 2=POST. [clave, handler, nivel]
        /*====================================*/
        $esperadas = [
            'listado'  => ['1 '.self::RUTA_WEB_LISTADO, 'informeTareas->listAll',    1],
            'busqueda' => ['2 '.self::RUTA_WEB_SEARCH,  'informeTareas->UpdateList', 1],
            'ver'      => ['1 '.self::RUTA_WEB_VIEW,    'informeTareas->View',       1],
            'imprimir' => ['1 '.self::RUTA_WEB_PRINT,   'informeTareas->Print',      1],
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
        // El informe es de solo lectura: todas sus rutas son de nivel 1 y GET/POST de busqueda
        /*====================================*/
        $propias = array_filter($rutas, static function ($r) { return $r[2] === 'informeTareas'; });
        $this->addCase($results, 'rutas_propias_completas', count($propias) === 4,
            'se esperaban 4 rutas del informe y hay '.count($propias));
        $soloNivel1 = true;
        $sinBorrado = true;
        foreach ($propias as $clave => $datos) {
            if ($datos[1] !== 1) { $soloNivel1 = false; }
            if ((int)substr($clave, 0, 1) >= 3) { $sinBorrado = false; }
        }
        $this->addCase($results, 'rutas_solo_nivel_1', $soloNivel1, 'alguna ruta del informe exige un nivel distinto de 1');
        $this->addCase($results, 'rutas_sin_borrado_ni_put', $sinBorrado, 'el informe publica rutas DELETE o PUT');

        /*====================================*/
        // Permiso del informe en el instalador y desinstalacion
        /*====================================*/
        $instalador = $this->codigoFuente($this->rutaModulo('../installer/'.self::INSTALADOR.'.php'));
        $this->addCase($results, 'instalador_permiso_informe',
            (bool)preg_match("/'RutaWeb'\s*=>\s*'".preg_quote(self::RUTA_WEB, '/')."'/", $instalador)
            && (bool)preg_match("/'RutaController'\s*=>\s*'informeTareas'/", $instalador),
            'el instalador no declara el permiso del informe con su ruta y controlador');
        $this->addCase($results, 'instalador_informe_tipo_informe',
            (bool)preg_match("/'idTipo'\s*=>\s*'3',\s*\/\/ Informe\s*\n\s*'Nombre'\s*=>\s*'Informe Tareas'/", $instalador),
            'el permiso del informe debe ser de tipo 3 (Informe)');
        $this->addCase($results, 'instalador_informe_solo_ver',
            (bool)preg_match("/'idLevelLimit'\s*=>\s*'1',\s*\/\/ Solo Ver\s*\n\s*'RutaWeb'\s*=>\s*'".preg_quote(self::RUTA_WEB, '/')."'/", $instalador),
            'el permiso del informe debe ser de nivel limite 1 (solo ver)');
        $this->addCase($results, 'instalador_desinstala_informe', str_contains($instalador, '"informeTareas"'),
            'RutaController() no lista informeTareas y dejaria rutas huerfanas al desinstalar');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                PERMISOS                                    */
    /******************************************************************************/
    /**
     * Verifica la sesion, el nivel de acceso del informe y el registro de sus rutas en
     * el router de F3 (solo si el usuario de pruebas las tiene).
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
        $this->addCase($results, 'informe_nivel_solo_ver', $nivel === null || (int)$nivel === 1,
            'el informe se concedio con nivel '.var_export($nivel, true).' y el limite es 1');
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
            'ruta_listado_router'  => ['/'.self::RUTA_WEB_LISTADO, 'GET',  self::CONTROLADOR.'->listAll'],
            'ruta_busqueda_router' => ['/'.self::RUTA_WEB_SEARCH,  'POST', self::CONTROLADOR.'->UpdateList'],
            'ruta_ver_router'      => ['/'.self::RUTA_WEB_VIEW,    'GET',  self::CONTROLADOR.'->View'],
            'ruta_imprimir_router' => ['/'.self::RUTA_WEB_PRINT,   'GET',  self::CONTROLADOR.'->Print'],
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
     * Renderiza `informeTareas-List.php` e `informeTareas-UpdateList.php` con datos
     * sinteticos: formulario de busqueda, endpoints, tabla de resultados, participantes
     * (con y sin foto), ID cifrado y matriz de niveles 1..4.
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

        /*====================================*/
        // Pagina de busqueda
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php'),
            $this->datosSinteticos(1), $BASE, $f3);
        $this->addCase($results, 'listado_render', $html !== '', 'la vista List no produjo HTML');
        $this->addCase($results, 'listado_contenedor', str_contains($html, 'id="listTableData"'),
            'falta el contenedor donde se pinta el resultado');
        $this->addCase($results, 'listado_form_busqueda', str_contains($html, 'id="FormSearchData"'),
            'no se dibuja el formulario de busqueda');
        $this->addCase($results, 'listado_inputs',
            str_contains($html, 'id="Search_idPrioridad"') && str_contains($html, 'id="Search_Fecha"')
            && str_contains($html, 'id="Search_Titulo"') && str_contains($html, 'id="Search_idEstadoCierre"'),
            'el formulario no declara idPrioridad, Fecha, Titulo e idEstadoCierre con prefijo Search_');
        $this->addCase($results, 'listado_catalogos',
            str_contains($html, 'Prioridad Prueba') && str_contains($html, 'Cierre Prueba'),
            'el filtro no ofrece los catalogos de prioridades y estados de cierre');
        $this->addCase($results, 'listado_endpoint_busqueda',
            str_contains($html, $BASE.'/'.self::RUTA_WEB.'/search') && str_contains($html, "let Metodo      = 'POST'"),
            'el formulario no apunta a /search con POST');
        $this->addCase($results, 'listado_endpoint_ver', str_contains($html, self::RUTA_WEB.'/view/'),
            'falta el endpoint de detalle');
        $this->addCase($results, 'listado_sin_crear', !str_contains($html, 'Crear Nuevo') && !str_contains($html, 'DELETE'),
            'el informe ofrece crear o borrar registros');

        /*====================================*/
        // Tabla de resultados: matriz de niveles 1..4 (el informe solo ofrece "Ver")
        /*====================================*/
        $rutaTabla = $this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php');
        foreach (self::NIVELES as $nivel) {
            $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos($nivel), $BASE, $f3);
            $this->addCase($results, 'tabla_render_nivel'.$nivel, $tabla !== '',
                'UpdateList no produjo HTML con nivel '.$nivel);
            $this->addCase($results, 'tabla_ver_nivel'.$nivel, str_contains($tabla, 'listTableDataView('),
                'la accion Ver debe estar disponible con nivel '.$nivel);
            $this->addCase($results, 'tabla_solo_ver_nivel'.$nivel,
                !str_contains($tabla, '/resumen/') && !str_contains($tabla, 'listTableDataDel(') && !str_contains($tabla, 'listTableDataEdit('),
                'la tabla ofrece editar o borrar con nivel '.$nivel);
            $this->addCase($results, 'tabla_datos_nivel'.$nivel,
                str_contains($tabla, 'Tarea Sintetica Uno') && str_contains($tabla, 'Estado Prueba'),
                'la tabla no pinta la tarea sintetica con nivel '.$nivel);
        }
        $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos(1), $BASE, $f3);
        $this->addCase($results, 'tabla_titulo', str_contains($tabla, 'Listado de Tareas'),
            'la tabla no muestra el titulo');
        $this->addCase($results, 'tabla_columnas',
            str_contains($tabla, '>Estado<') && str_contains($tabla, '>Prioridad<') && str_contains($tabla, '>Fecha<')
            && str_contains($tabla, '>Titulo<') && str_contains($tabla, '>Participantes<'),
            'la tabla no declara las columnas Estado, Prioridad, Fecha, Titulo y Participantes');
        $this->addCase($results, 'tabla_badges',
            str_contains($tabla, 'badge-sp1 badge-sp1-primary') && str_contains($tabla, 'badge-sp1 badge-sp1-danger'),
            'la tabla no pinta los colores del estado y la prioridad');
        $this->addCase($results, 'tabla_fecha_estandar', str_contains($tabla, '02-01-2026') || str_contains($tabla, '02/01/2026'),
            'la tabla no formatea la fecha con el formato estandar');
        $this->addCase($results, 'tabla_id_cifrado', !str_contains($tabla, "listTableDataView('55')"),
            'la tabla expone idKanban sin cifrar');
        $this->addCase($results, 'tabla_una_fila_por_tarea', substr_count($tabla, "listTableDataView(") === 1,
            'una tarea con dos participantes debe pintarse en una sola fila');
        $this->addCase($results, 'tabla_volver', str_contains($tabla, self::RUTA_WEB.'/listAll'),
            'la tabla no ofrece volver al buscador');

        /*====================================*/
        // Participantes: con foto, sin foto (imagen por defecto) y sin usuario
        /*====================================*/
        $this->addCase($results, 'tabla_participante_con_foto',
            str_contains($tabla, 'data-title="Ana Prueba"') && str_contains($tabla, '/admin/public/upload/ana.png'),
            'el participante con foto no usa MainPathUrl + imagen');
        $this->addCase($results, 'tabla_participante_sin_foto',
            str_contains($tabla, 'data-title="Luis Sinfoto"') && str_contains($tabla, $BASE.'/img/profile-img.jpg'),
            'el participante sin foto no usa la imagen por defecto');

        $sinUsuario = $this->datosSinteticos(1);
        $sinUsuario['arrTareas'][55] = [$sinUsuario['arrTareas'][55][0]];
        $sinUsuario['arrTareas'][55][0]['UsuarioNombre'] = null;
        $sinUsuario['arrTareas'][55][0]['UsuarioImg']    = null;
        $tablaSinUsuario = $this->renderizarVista($rutaTabla, $sinUsuario, $BASE, $f3);
        $this->addCase($results, 'tabla_tarea_sin_participantes',
            $tablaSinUsuario !== '' && str_contains($tablaSinUsuario, 'Tarea Sintetica Uno') && !str_contains($tablaSinUsuario, 'rounded-circle'),
            'una tarea sin participantes debe pintarse sin imagenes de usuario');

        /*====================================*/
        // Tabla vacia
        /*====================================*/
        $vacio = $this->datosSinteticos(1);
        $vacio['arrTareas'] = [];
        $tablaVacia = $this->renderizarVista($rutaTabla, $vacio, $BASE, $f3);
        $this->addCase($results, 'tabla_vacia', $tablaVacia !== '' && str_contains($tablaVacia, '<tbody>') && !str_contains($tablaVacia, 'listTableDataView('),
            'la tabla sin resultados pinta filas o no se dibuja');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                          VISTAS: DETALLE E IMPRESION                       */
    /******************************************************************************/
    /**
     * Renderiza el detalle (`informeTareas-View.php`) y la vista compartida de impresion
     * (`common-Print.php`) con datos sinteticos, con y sin tareas especificas
     * (`KanbanTareasUsoTareas`), y comprueba que `informeTareas-Print.php` delega en ella.
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
        // Detalle sin tareas especificas
        /*====================================*/
        $datos = $this->datosSinteticos(1, 1);
        $datos['arrTareas'] = [
            ['idTareas' => 1, 'Tarea' => 'Subtarea Uno', 'EstadoNombre' => 'Hecha', 'EstadoColor' => 'success', 'EstadoIcon' => 'bi bi-check', 'Trabajo' => 'Trabajo Prueba'],
        ];
        $html = $this->renderizarVista($rutaView, $datos, $BASE, $f3);
        $this->addCase($results, 'view_render', $html !== '', 'la vista View no produjo HTML');
        $this->addCase($results, 'view_cabecera_modal', str_contains($html, 'modal-header') && str_contains($html, 'Ver Datos'),
            'el detalle no dibuja la cabecera del modal');
        $this->addCase($results, 'view_datos_tarea',
            str_contains($html, 'Tarea Sintetica Uno') && str_contains($html, 'Descripcion sintetica de la tarea')
            && str_contains($html, '02-01-2026'),
            'el detalle no muestra titulo, descripcion y fecha de la tarea');
        $this->addCase($results, 'view_badges',
            str_contains($html, 'Prioridad: Prioridad Prueba') && str_contains($html, 'Tablero: Estado Prueba')
            && str_contains($html, 'Estado Cierre: Cierre Prueba'),
            'el detalle no muestra prioridad, tablero y estado de cierre');
        $this->addCase($results, 'view_tareas_sin_trabajo',
            str_contains($html, 'Subtarea Uno') && !str_contains($html, 'Trabajo Prueba') && str_contains($html, 'bi bi-check'),
            'sin tareas especificas el detalle no debe mostrar el trabajo y si la subtarea con su estado');
        $this->addCase($results, 'view_participantes',
            str_contains($html, 'Ana Prueba') && str_contains($html, '/admin/public/upload/ana.png')
            && str_contains($html, 'Luis Sinfoto') && str_contains($html, $BASE.'/img/profile-img.jpg'),
            'el detalle no lista los participantes con su foto o la imagen por defecto');
        $this->addCase($results, 'view_historial',
            str_contains($html, 'Historial') && str_contains($html, '10:30:00') && str_contains($html, 'Linea uno<br>Linea dos'),
            'el detalle no muestra el historial con sus saltos de linea');
        $this->addCase($results, 'view_imprimir_cifrado',
            str_contains($html, self::RUTA_WEB.'/print/') && !str_contains($html, self::RUTA_WEB.'/print/55"'),
            'el enlace de impresion no existe o expone idKanban sin cifrar');
        $this->addCase($results, 'view_boton_cerrar', str_contains($html, 'data-bs-dismiss="modal"') && str_contains($html, 'modal-footer'),
            'con sistemaModalCloseBTN = 2 debe dibujarse el pie con el boton Cerrar');

        /*====================================*/
        // Detalle con tareas especificas y variantes del modal
        /*====================================*/
        $datos = $this->datosSinteticos(1, 2);
        $datos['arrTareas'] = [
            ['idTareas' => 1, 'Tarea' => 'Subtarea Uno', 'EstadoNombre' => 'Hecha', 'EstadoColor' => 'success', 'EstadoIcon' => 'bi bi-check', 'Trabajo' => 'Trabajo Prueba'],
        ];
        $datos['UserData']['sistemaModalSubtitle'] = 2;
        $datos['UserData']['sistemaModalCloseBTN'] = 1;
        $html = $this->renderizarVista($rutaView, $datos, $BASE, $f3);
        $this->addCase($results, 'view_tareas_con_trabajo', str_contains($html, 'Trabajo Prueba') && str_contains($html, 'Subtarea Uno'),
            'con tareas especificas el detalle debe mostrar el trabajo y la subtarea');
        $this->addCase($results, 'view_subtitulo_modal', str_contains($html, 'modal-subtitle') && str_contains($html, 'Permite visualizar los datos'),
            'con sistemaModalSubtitle = 2 debe dibujarse el subtitulo');
        $this->addCase($results, 'view_sin_boton_cerrar', !str_contains($html, 'modal-footer') && str_contains($html, 'max-height: 80vh'),
            'con sistemaModalCloseBTN distinto de 2 no debe dibujarse el pie del modal');

        /*====================================*/
        // Listas vacias: el detalle no debe fallar
        /*====================================*/
        $vacio = $this->datosSinteticos(1, 1);
        $vacio['arrTareas'] = [];
        $vacio['arrParticipantes'] = [];
        $vacio['arrHistorial'] = [];
        $htmlVacio = $this->renderizarVista($rutaView, $vacio, $BASE, $f3);
        $this->addCase($results, 'view_listas_vacias', $htmlVacio !== '' && str_contains($htmlVacio, 'Tarea Sintetica Uno'),
            'el detalle de una tarea sin subtareas, participantes ni historial no se dibuja');

        /*====================================*/
        // Impresion: vista compartida common-Print.php (se renderiza directamente, no su envoltorio)
        /*====================================*/
        $datos = $this->datosSinteticos(1, 1);
        $datos['arrTareas'] = [
            ['idTareas' => 1, 'Tarea' => 'Subtarea Uno', 'EstadoNombre' => 'Hecha', 'EstadoColor' => 'success', 'EstadoIcon' => 'bi bi-check', 'Trabajo' => 'Trabajo Prueba'],
        ];
        $print = $this->renderizarVista($rutaPrint, $datos, $BASE, $f3);
        $this->addCase($results, 'print_render', $print !== '', 'common-Print.php no produjo HTML');
        $this->addCase($results, 'print_datos',
            str_contains($print, 'Tarea Sintetica Uno') && str_contains($print, 'Prioridad: Prioridad Prueba')
            && str_contains($print, 'Descripcion sintetica de la tarea') && str_contains($print, 'Subtarea Uno')
            && str_contains($print, 'Ana Prueba') && str_contains($print, 'Luis Sinfoto'),
            'la impresion no muestra los datos de la tarea, sus subtareas y participantes');
        $this->addCase($results, 'print_sin_trabajo', !str_contains($print, 'Trabajo Prueba'),
            'sin tareas especificas la impresion no debe mostrar el trabajo');
        $this->addCase($results, 'print_sin_historial', !str_contains($print, 'Historial'),
            'la impresion no incluye el historial (solo el detalle lo muestra)');
        $datos = $this->datosSinteticos(1, 2);
        $datos['arrTareas'] = [
            ['idTareas' => 1, 'Tarea' => 'Subtarea Uno', 'EstadoNombre' => 'Hecha', 'EstadoColor' => 'success', 'EstadoIcon' => 'bi bi-check', 'Trabajo' => 'Trabajo Prueba'],
        ];
        $printTrabajo = $this->renderizarVista($rutaPrint, $datos, $BASE, $f3);
        $this->addCase($results, 'print_con_trabajo', str_contains($printTrabajo, 'Trabajo Prueba') && str_contains($printTrabajo, 'Subtarea Uno'),
            'con tareas especificas la impresion debe mostrar el trabajo y la subtarea');
        $this->addCase($results, 'print_envoltorio_delega',
            str_contains($this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-Print.php')), "require_once('common-Print.php')"),
            'informeTareas-Print.php no delega en common-Print.php');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos el cifrado de identificadores y la validacion de IDs que
     * consumen `View` y `Print`, y comprueba que el informe no define chequeos propios.
     *
     * @return array Resultado por caso
     */
    public function logicaValidacionTest(){

        $results = [];

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

        /*====================================*/
        // El informe no define chequeos propios: la entrada de busqueda va por searchWhere()
        /*====================================*/
        $this->addCase($results, 'sin_datacheck_propio', !method_exists(self::CONTROLADOR, 'dataCheck') && !(new ReflectionClass(self::CONTROLADOR))->hasMethod('dataCheck_1'),
            'el informe define dataCheck pero no escribe datos');

        /*====================================*/
        // Metodos heredados de busqueda disponibles para el controlador
        /*====================================*/
        foreach (['searchWhere', 'searchValidateDates'] as $metodo) {
            $this->addCase($results, 'busqueda_heredada_'.$metodo, method_exists('ControllerBase', $metodo),
                'ControllerBase no ofrece '.$metodo.'()');
        }

        /*====================================*/
        // Agrupacion de filas por ID usada por UpdateList (CommonData::agruparPorClave)
        /*====================================*/
        try {
            $CommonData = new FunctionsCommonData();
            $agrupado   = $CommonData->agruparPorClave([
                ['ID' => 1, 'UsuarioNombre' => 'A'],
                ['ID' => 1, 'UsuarioNombre' => 'B'],
                ['ID' => 2, 'UsuarioNombre' => 'C'],
            ], 'ID');
            $this->addCase($results, 'agrupar_por_id',
                is_array($agrupado) && count($agrupado) === 2 && count($agrupado[1] ?? []) === 2 && count($agrupado[2] ?? []) === 1,
                'agruparPorClave() no agrupa las filas de una tarea con varios participantes');
        } catch (Throwable $e) {
            $this->addCase($results, 'agrupar_por_id', false,
                'no se pudo ejecutar agruparPorClave() ('.$e->getMessage().')');
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                 SEGURIDAD                                  */
    /******************************************************************************/
    /**
     * Revisa estaticamente las vistas y el controlador del informe: rutas relativas,
     * rutas absolutas, acceso a sesion, SQL embebido, cifrado de IDs y consultas preparadas.
     *
     * @return array Resultado por caso
     */
    public function seguridadVistasTest(){

        $results = [];

        /*====================================*/
        // Vistas involucradas (las 4 propias mas la compartida common-Print.php)
        /*====================================*/
        $arrVistas = glob($this->rutaModulo('../views/'.self::CONTROLADOR.'-*.php')) ?: [];
        $this->addCase($results, 'hay_vistas', count($arrVistas) === 4,
            'se esperaban 4 vistas del informe y se encontraron '.count($arrVistas));
        $arrVistas[] = $this->rutaModulo('../views/common-Print.php');

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
        // Cifrado de los IDs y formato de datos en la tabla y el detalle
        /*====================================*/
        $tabla = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'));
        $this->addCase($results, 'tabla_id_cifrado', str_contains($tabla, "encryptDecrypt('encrypt', \$Tareas)"),
            'UpdateList no cifra el ID de la tarea en la accion Ver');
        $this->addCase($results, 'tabla_fecha_estandar', str_contains($tabla, 'fechaEstandar('),
            'UpdateList no formatea la fecha con fechaEstandar()');
        $this->addCase($results, 'tabla_acceso_ver', str_contains($tabla, "['LevelAccess']>=1"),
            'UpdateList no condiciona la accion Ver al nivel de acceso');
        $detalle = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'));
        $this->addCase($results, 'detalle_id_cifrado', str_contains($detalle, "encryptDecrypt('encrypt', \$data['rowData']['idKanban'])"),
            'View no cifra idKanban en el enlace de impresion');
        $this->addCase($results, 'detalle_fecha_estandar', substr_count($detalle, 'fechaEstandar(') === 2,
            'View no formatea con fechaEstandar() la fecha de la tarea y del historial');

        /*====================================*/
        // Controlador: SQL preparado y sin concatenacion de entrada
        /*====================================*/
        $controlador = $this->fuenteControlador();
        $this->addCase($results, 'controlador_sin_concat_sql', !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $controlador),
            'el controlador concatena superglobales en una cadena SQL');
        $this->addCase($results, 'controlador_parametros_preparados', preg_match_all("/'params'\s*=>/", $controlador) === 10,
            'las 10 consultas (2 catalogos, busqueda, 4 de View y 3 de Print) deben declarar la clave params');
        $this->addCase($results, 'controlador_where_con_params', substr_count($controlador, "\$r['params']") === 3,
            'los tres searchWhere() deben acumular sus parametros preparados');
        $this->addCase($results, 'controlador_id_como_parametro', substr_count($controlador, "'params'  => [\$KanbanID['data']]") === 7,
            'las 7 consultas de detalle deben recibir el ID descifrado como parametro preparado');
        $this->addCase($results, 'controlador_sin_superglobales',
            !str_contains($controlador, '$_POST') && !str_contains($controlador, '$_GET') && !str_contains($controlador, '$_REQUEST'),
            'el controlador lee superglobales directamente; la entrada debe pasar por searchWhere()');

        /************************************/
        // Retorno los datos
        return $results;

    }

}
