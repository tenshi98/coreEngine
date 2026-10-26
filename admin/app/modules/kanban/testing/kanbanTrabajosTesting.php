<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class kanbanTrabajosTesting
 *
 * Pruebas del modulo `kanban` - controlador `kanbanTrabajos` (catalogo de trabajos
 * especificos para las subtareas: `listAll`, `UpdateList`, `View`, `GetID`, `Insert`,
 * `Update`, `Delete`).
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
 * Particularidades del controlador:
 *  - Su permiso en el instalador se llama «Proyectos - Trabajos» (antes «Proyectos - Tareas»,
 *    nombre heredado que confundia con la pantalla de tareas); la ruta es
 *    `administracion/kanban/trabajos`.
 *  - `Delete()` no borra un trabajo asignado a subtareas (responde 400): se debe desactivar.
 *  - La vista `-List` incluye `-formSearch`, `-UpdateList` y `-formNew` con `require_once`: como
 *    un `include` previo de uno de esos archivos vuelve ese `require_once` un no-op, el
 *    contenido de las sub-vistas se verifica renderizando cada una directamente y la cadena de
 *    includes por fuente.
 *  - El alta crea siempre el trabajo activo (`idEstado = 1` oculto); el estado solo se cambia
 *    al editar.
 *
 * @see skills/testing/SKILL.md
 */
class kanbanTrabajosTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR      = 'kanbanTrabajos';
    private const INSTALADOR       = 'kanbanTareasInstaller';
    private const RUTA_WEB         = 'administracion/kanban/trabajos';
    private const RUTA_WEB_LISTADO = 'administracion/kanban/trabajos/listAll';
    private const RUTA_WEB_SEARCH  = 'administracion/kanban/trabajos/search';
    private const RUTA_WEB_UPDLIST = 'administracion/kanban/trabajos/updateList';
    private const RUTA_WEB_VIEW    = 'administracion/kanban/trabajos/view/@id';
    private const RUTA_WEB_GETID   = 'administracion/kanban/trabajos/getID/@id';
    private const RUTA_WEB_UPDATE  = 'administracion/kanban/trabajos/update';
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
    // Ejecuta el dataCheck() privado con un POST dado y pasa el resultado por CheckData
    /*******************************************************************/
    private function chequeo(array $post): array{
        $reflector  = new ReflectionMethod(self::CONTROLADOR, 'dataCheck');
        $reflector->setAccessible(true);
        $controller = (new ReflectionClass(self::CONTROLADOR))->newInstanceWithoutConstructor();
        $checkData  = new CheckData();
        return $checkData->checkingData($reflector->invoke($controller, $post));
    }

    /*******************************************************************/
    // Datos sinteticos usados por las vistas del controlador
    /*******************************************************************/
    private function datosSinteticos(int $nivel, int $subtitulo = 1): array{
        return [
            'PageTitle'        => 'Trabajos Kanban',
            'PageDescription'  => 'Trabajos Kanban.',
            'PageAuthor'       => 'Pruebas',
            'PageKeywords'     => 'Pruebas',
            'TableTitle'       => 'Listado Trabajos Kanban',
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
            'arrEstado'        => [['ID' => 1, 'Nombre' => 'Activo Prueba'], ['ID' => 2, 'Nombre' => 'Inactivo Prueba']],
            'arrList'          => [[
                'idTrabajo'   => 7,
                'Nombre'      => "Trabajo D'Prueba",
                'Estado'      => 'Activo Prueba',
                'EstadoColor' => 'success',
            ]],
            'rowData'          => [
                'idTrabajo'   => 7,
                'Nombre'      => 'Trabajo Sintetico',
                'idEstado'    => 2,
                'Estado'      => 'Activo Prueba',
                'EstadoColor' => 'success',
            ],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica los archivos del controlador `kanbanTrabajos` y la convencion
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
            'testing'           => $this->rutaModulo('kanbanTrabajosTesting.php'),
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
        $this->addCase($results, 'clase_testing_cargada', class_exists('kanbanTrabajosTesting'),
            'la clase kanbanTrabajosTesting no esta disponible');
        $padre = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padre === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padre.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('kanbanTrabajosTesting') === 'ControllerBase',
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
     * manejo de fallos, ID cifrado, busqueda por nombre y estado y consultas de catalogo.
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
            && str_contains($codigo, 'mergeResponses([$arrList,$arrEstado])')
            && str_contains($codigo, "if(\$arrList['status'] && \$arrEstado['status'])"),
            'listAll() debe comprobar el status de sus dos consultas y usar showError(1)/mergeResponses()');
        $this->addCase($results, 'fragmentos_manejan_fallo',
            substr_count($codigo, 'showError(2, $f3, $result)') === 3 && substr_count($codigo, 'mergeResponses(') === 4
            && str_contains($codigo, 'mergeResponses([$rowData,$arrEstado])'),
            'UpdateList(), View() y GetID() deben manejar el fallo de consulta con showError(2)/mergeResponses()');
        $this->addCase($results, 'listado_titulo', str_contains($codigo, "'PageTitle'") && substr_count($codigo, "'TableTitle'") === 2,
            'listAll() y UpdateList() deben asignar el titulo de la tabla');

        /*====================================*/
        // Listado: trabajo con su estado, ordenado por nombre, y catalogo de estados
        /*====================================*/
        $this->addCase($results, 'listado_join_estado',
            substr_count($codigo, 'LEFT JOIN core_estados   ON core_estados.idEstado = kanban_trabajos.idEstado') === 3
            && substr_count($codigo, 'core_estados.Color AS EstadoColor') === 3,
            'listAll(), UpdateList() y View() deben unir el estado del trabajo con su color');
        $this->addCase($results, 'listado_orden_nombre', substr_count($codigo, "'order'   => 'kanban_trabajos.Nombre ASC'") === 2,
            'listAll() y UpdateList() deben ordenar por nombre');
        $this->addCase($results, 'view_sin_orden_sobrante',
            !str_contains($this->cuerpoMetodo('View'), "'order'   => 'kanban_trabajos.Nombre ASC'") && str_contains($this->cuerpoMetodo('View'), "'order'   => ''"),
            'View() lee un solo registro: no debe ordenar');
        $this->addCase($results, 'titulos_de_trabajos',
            substr_count($codigo, "'PageTitle'       => 'Trabajos Kanban'") === 1 && substr_count($codigo, 'Listado Trabajos Kanban') === 2 && !str_contains($codigo, 'Listado Tareas Kanban'),
            'las pantallas de trabajos deben titularse «Trabajos Kanban» / «Listado Trabajos Kanban» (no «Tareas»)');
        $this->addCase($results, 'listado_catalogo_estados',
            substr_count($codigo, "'table'   => 'core_estados'") === 2 && substr_count($codigo, "'data'    => 'idEstado AS ID,Nombre'") === 2
            && substr_count($codigo, "'order'   => 'Nombre ASC'") === 2,
            'listAll() y GetID() deben cargar el catalogo de estados ordenado por Nombre');
        $this->addCase($results, 'consultas_acotadas', substr_count($codigo, 'ConfigAPP::APP["N_MaxItems"]') === 4,
            'las 4 consultas de listado deben acotar el numero de filas');

        /*====================================*/
        // Busqueda: columnas, validacion de fechas y WHERE preparado
        /*====================================*/
        $this->addCase($results, 'busqueda_campo_exacto', str_contains($codigo, "\$WhereData_int     = 'idEstado';"),
            'UpdateList() no filtra por idEstado');
        $this->addCase($results, 'busqueda_campo_relativo', str_contains($codigo, "\$WhereData_string  = 'Nombre';"),
            'UpdateList() no filtra por Nombre');
        $this->addCase($results, 'busqueda_valida_fechas', str_contains($codigo, 'searchValidateDates(') && str_contains($codigo, 'Response::error($RespDataBetween, 500)'),
            'UpdateList() no valida las fechas con searchValidateDates() ni responde 500');
        $this->addCase($results, 'busqueda_where_preparado', substr_count($codigo, '$this->searchWhere(') === 3 && substr_count($codigo, "'kanban_trabajos', ") === 3,
            'UpdateList() no construye el WHERE con los tres tipos de searchWhere() sobre kanban_trabajos');
        $this->addCase($results, 'busqueda_params', str_contains($codigo, "'params'  => \$whereParams") && str_contains($codigo, "'where'   => \$whereInt"),
            'UpdateList() no pasa el WHERE y los parametros preparados');

        /*====================================*/
        // ID cifrado en View y GetID
        /*====================================*/
        $this->addCase($results, 'id_descifrado', substr_count($codigo, "encryptDecrypt('decrypt', \$params['id'])") === 2,
            'View() y GetID() deben descifrar el ID de la ruta');
        $this->addCase($results, 'id_validado', substr_count($codigo, "isValidDecrypted(\$TrabajoID, 'id')") === 3,
            'View(), GetID() y Delete() deben validar el ID descifrado');
        $this->addCase($results, 'id_invalido_400', substr_count($codigo, "Response::error('Registro inválido', 400)") === 3,
            'un ID invalido debe responder 400 en View(), GetID() y Delete()');
        $this->addCase($results, 'detalle_por_id',
            str_contains($codigo, "'where'   => 'kanban_trabajos.idTrabajo = ?'") && str_contains($codigo, "'where'   => 'idTrabajo = ?'")
            && substr_count($codigo, "'params'  => [\$TrabajoID['data']]") === 3,
            'View(), GetID() y la comprobacion de Delete() deben filtrar por idTrabajo con el ID descifrado como parametro preparado');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                           CONTROLADOR: ESCRITURA                           */
    /******************************************************************************/
    /**
     * Verifica `Insert`, `Update` y `Delete` SIN ejecutarlos: verbo HTTP, chequeo de datos,
     * campos obligatorios y unicos, delimitacion por idTrabajo y respuestas al cliente. El
     * CRUD envuelve cada escritura en una transaccion Base_transactionBegin/Commit; los fallos pasan a failOperation.
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
        $this->addCase($results, 'tabla_trabajos', preg_match_all("/'table'\s*=>\s*'kanban_trabajos'/", $codigo) === 7,
            'se esperaban 7 usos de kanban_trabajos (4 lecturas y Insert/Update/Delete)');
        $this->addCase($results, 'sin_codificacion', substr_count($codigo, "'encode'    => ''") === 2,
            'los trabajos no tienen campos codificados');
        $this->addCase($results, 'nombre_unico', substr_count($codigo, "'unique'    => 'Nombre'") === 2,
            'Insert() y Update() deben exigir Nombre unico');

        /*====================================*/
        // Insert
        /*====================================*/
        $this->addCase($results, 'insert_campos', str_contains($codigo, "'data'      => 'Nombre,idEstado'"),
            'Insert() no declara los campos esperados del trabajo');
        $this->addCase($results, 'insert_requeridos', str_contains($codigo, "'required'  => 'Nombre,idEstado'"),
            'Insert() no declara Nombre e idEstado como obligatorios');
        $this->addCase($results, 'insert_base', str_contains($codigo, '$this->Base_insert('),
            'Insert() no escribe con Base_insert()');

        /*====================================*/
        // Update
        /*====================================*/
        $this->addCase($results, 'update_verbo_post', str_contains($codigo, "REQUEST_METHOD'] !== 'POST'"),
            'Update() no valida el verbo HTTP POST');
        $this->addCase($results, 'update_donde', str_contains($codigo, "'where'     => 'idTrabajo'"),
            'Update() no delimita la escritura por idTrabajo');
        $this->addCase($results, 'update_campos', str_contains($codigo, "'data'      => 'idTrabajo,Nombre,idEstado'"),
            'Update() no declara los campos esperados del trabajo');
        $this->addCase($results, 'update_requeridos', str_contains($codigo, "'required'  => 'idTrabajo,Nombre,idEstado'"),
            'Update() no declara los obligatorios');
        $this->addCase($results, 'update_base', str_contains($codigo, '$this->Base_update('),
            'Update() no escribe con Base_update()');

        /*====================================*/
        // Delete: verbo, delimitacion y sin archivos
        /*====================================*/
        $this->addCase($results, 'delete_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'DELETE'"),
            'Delete() no valida el verbo HTTP DELETE');
        $this->addCase($results, 'delete_donde', str_contains($codigo, "'where'       => 'idTrabajo'"),
            'Delete() no delimita el borrado por idTrabajo');
        $this->addCase($results, 'delete_sin_archivos', str_contains($codigo, "'files'       => ''"),
            'Delete() no declara que el trabajo no maneja archivos');
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
        $this->addCase($results, 'respuesta_500', substr_count($codigo, 'failOperation(') === 4,
            'se esperaban 4 puntos failOperation (Insert, Update, Delete y la comprobacion de uso)');

        /*====================================*/
        // Delete: no se borra un trabajo asignado a subtareas
        /*====================================*/
        $delete = $this->cuerpoMetodo('Delete');
        $this->addCase($results, 'delete_id_cifrado',
            str_contains($delete, "encryptDecrypt('decrypt', \$dataDelete['idTrabajo'] ?? '')") && str_contains($delete, "isValidDecrypted(\$TrabajoID, 'id')")
            && str_contains($delete, "Response::error('Registro inválido', 400)"),
            'Delete() debe descifrar y validar el ID del trabajo (400 si es invalido)');
        $this->addCase($results, 'delete_comprueba_uso',
            str_contains($delete, "'table'   => 'kanban_tareas_tareas'") && str_contains($delete, "'where'   => 'idTrabajo = ?'")
            && str_contains($delete, "'params'  => [\$TrabajoID['data']]") && str_contains($delete, '$this->Base_GetCountData(')
            && str_contains($delete, "if (\$nUso['status'] === false)") && str_contains($delete, "if ((int)\$nUso['data'] > 0)"),
            'Delete() debe contar las subtareas que usan el trabajo (parametro preparado) y comprobar el resultado');
        $this->addCase($results, 'delete_bloquea_si_esta_en_uso',
            str_contains($delete, "Response::error('El trabajo está asignado a subtareas y no se puede borrar. Desactívelo cambiando su estado.', 400)")
            && strpos($delete, "(int)\$nUso['data'] > 0") < strpos($delete, '$this->Base_delete('),
            'Delete() debe responder 400 y no borrar si el trabajo esta asignado a subtareas');
        $this->addCase($results, 'delete_valida_antes_de_borrar',
            strpos($delete, "isValidDecrypted(\$TrabajoID, 'id')") < strpos($delete, '$this->Base_GetCountData(')
            && strpos($delete, '$this->Base_GetCountData(') < strpos($delete, '$this->Base_delete('),
            'Delete() debe validar el ID y comprobar el uso antes de borrar');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                       CONTROLADOR: RUTAS E INSTALADOR                      */
    /******************************************************************************/
    /**
     * Verifica que las 8 rutas de `kanbanTrabajos` publicadas por el instalador apunten a
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
            'listado'     => ['1 '.self::RUTA_WEB_LISTADO, 'kanbanTrabajos->listAll',    1],
            'busqueda'    => ['2 '.self::RUTA_WEB_SEARCH,  'kanbanTrabajos->UpdateList', 1],
            'actualizar'  => ['1 '.self::RUTA_WEB_UPDLIST, 'kanbanTrabajos->UpdateList', 2],
            'ver'         => ['1 '.self::RUTA_WEB_VIEW,    'kanbanTrabajos->View',       1],
            'getid'       => ['1 '.self::RUTA_WEB_GETID,   'kanbanTrabajos->GetID',      2],
            'crear'       => ['2 '.self::RUTA_WEB,         'kanbanTrabajos->Insert',     3],
            'editar'      => ['2 '.self::RUTA_WEB_UPDATE,  'kanbanTrabajos->Update',     2],
            'borrar'      => ['3 '.self::RUTA_WEB,         'kanbanTrabajos->Delete',     4],
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
        // Conjunto completo de rutas propias, en el permiso propio (tipo 4)
        /*====================================*/
        $propias = array_filter($rutas, static function ($r) { return $r[2] === 'kanbanTrabajos'; });
        $this->addCase($results, 'rutas_propias_completas', count($propias) === 8,
            'se esperaban 8 rutas de kanbanTrabajos y hay '.count($propias));
        $this->addCase($results, 'rutas_en_su_permiso', $this->rutasDeTipo(4, 'kanbanTrabajos') === 8,
            'las 8 rutas de kanbanTrabajos deben publicarse en el cuarto permiso del instalador');

        /*====================================*/
        // Permiso del CRUD en el instalador y desinstalacion
        /*====================================*/
        $instalador = $this->codigoFuente($this->rutaModulo('../installer/'.self::INSTALADOR.'.php'));
        $this->addCase($results, 'instalador_permiso_trabajos',
            (bool)preg_match("/'RutaWeb'\s*=>\s*'".preg_quote(self::RUTA_WEB, '/')."'/", $instalador)
            && (bool)preg_match("/'RutaController'\s*=>\s*'kanbanTrabajos'/", $instalador),
            'el instalador no declara el permiso de trabajos con su ruta y controlador');
        $this->addCase($results, 'instalador_trabajos_categoria_administracion',
            (bool)preg_match("/'idPermisosCat'\s*=>\s*'1',\s*\/\/ Administraci\S+\s*\n\s*'idEstado'[^\n]*\n\s*'idTipo'\s*=>\s*'1',\s*\/\/ Crud Normal\s*\n\s*'Nombre'\s*=>\s*'Proyectos - Trabajos'/", $instalador),
            'el permiso de trabajos debe llamarse «Proyectos - Trabajos» y ser un Crud Normal de la categoria Administracion');
        $this->addCase($results, 'instalador_trabajos_nivel_completo',
            (bool)preg_match("/'idLevelLimit'\s*=>\s*'4',[^\n]*\n\s*'RutaWeb'\s*=>\s*'".preg_quote(self::RUTA_WEB, '/')."'/", $instalador),
            'el permiso de trabajos debe tener nivel limite 4 (ver/editar/crear/borrar)');
        $this->addCase($results, 'instalador_desinstala_trabajos', str_contains($instalador, '"kanbanTrabajos"'),
            'RutaController() no lista kanbanTrabajos y dejaria rutas huerfanas al desinstalar');

        /*====================================*/
        // Tabla que crea el instalador y columnas que escribe el controlador
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
        $this->addCase($results, 'instalador_tabla_trabajos',
            isset($tablas['kanban_trabajos']) && str_contains($tablas['kanban_trabajos'], '`idTrabajo`') && str_contains($tablas['kanban_trabajos'], '`Nombre` varchar(255)')
            && str_contains($tablas['kanban_trabajos'], '`idEstado`'),
            'el instalador no crea kanban_trabajos con idTrabajo, Nombre varchar(255) e idEstado');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                PERMISOS                                    */
    /******************************************************************************/
    /**
     * Verifica la sesion, el nivel de acceso de trabajos y el registro de sus rutas en el
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
     * Renderiza `kanbanTrabajos-List.php`, `-formSearch.php` y `-UpdateList.php` con datos
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
        $this->addCase($results, 'listado_titulo', str_contains($html, 'Listado Trabajos Kanban'),
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
            str_contains($html, "let Metodo      = 'DELETE'") && str_contains($html, '{"idTrabajo": ID}'),
            'el borrado no usa DELETE con idTrabajo');
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
            $this->addCase($results, 'listado_incluye_'.$sub, str_contains($codList, "require_once('kanbanTrabajos-".$sub.".php')"),
                'la pagina no incluye kanbanTrabajos-'.$sub.'.php');
        }

        /*====================================*/
        // Formulario de busqueda
        /*====================================*/
        $buscar = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-formSearch.php'),
            $this->datosSinteticos(1), $BASE, $f3);
        $this->addCase($results, 'busqueda_render', $buscar !== '', 'la vista formSearch no produjo HTML');
        $this->addCase($results, 'busqueda_form', str_contains($buscar, 'id="FormSearchData"') && str_contains($buscar, 'id="formSearch"'),
            'no se dibuja el formulario de busqueda colapsable');
        $this->addCase($results, 'busqueda_inputs', str_contains($buscar, 'id="Search_Nombre"') && str_contains($buscar, 'id="Search_idEstado"'),
            'el formulario no declara Nombre e idEstado con prefijo Search_');
        $this->addCase($results, 'busqueda_catalogo_estados', str_contains($buscar, 'Activo Prueba') && str_contains($buscar, 'Inactivo Prueba'),
            'el filtro no ofrece el catalogo de estados');
        $this->addCase($results, 'busqueda_endpoint',
            str_contains($buscar, $BASE.'/'.self::RUTA_WEB.'/search') && str_contains($buscar, "let Metodo      = 'POST'") && str_contains($buscar, "UpdateDivFrom : 'listTableData'"),
            'el formulario no envia por POST a /search ni refresca listTableData');
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
                str_contains($tabla, 'Activo Prueba') && str_contains($tabla, 'badge-sp1-success'),
                'la tabla no pinta el trabajo sintetico con nivel '.$nivel);
        }
        $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos(4), $BASE, $f3);
        $this->addCase($results, 'tabla_columnas',
            str_contains($tabla, '>Nombre<') && str_contains($tabla, '>Estado<') && str_contains($tabla, '>Acciones<'),
            'la tabla no declara las columnas Nombre, Estado y Acciones');
        $this->addCase($results, 'tabla_valores', str_contains($tabla, "<td>Trabajo D'Prueba</td>"),
            'la tabla no pinta el nombre del trabajo');
        $this->addCase($results, 'tabla_id_cifrado', !str_contains($tabla, "listTableDataView('7')") && !str_contains($tabla, "listTableDataEdit('7')")
            && !str_contains($tabla, "listTableDataDel('7'"),
            'la tabla expone idTrabajo sin cifrar');
        $this->addCase($results, 'tabla_nombre_escapado_para_js', str_contains($tabla, "Trabajo D\\'Prueba"),
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
     * Renderiza `kanbanTrabajos-formNew.php`, `-formEdit.php` y `-View.php` con datos
     * sinteticos: modal, formulario, inputs con su prefijo, estado inicial oculto, estado
     * preseleccionado al editar, campo oculto, endpoints con su verbo y variantes del modal.
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
        // Formulario de creacion (modal): el trabajo nace activo (idEstado = 1 oculto)
        /*====================================*/
        $html = $this->renderizarVista($rutaNew, $this->datosSinteticos(3), $BASE, $f3);
        $this->addCase($results, 'new_render', $html !== '', 'la vista formNew no produjo HTML');
        $this->addCase($results, 'new_modal', str_contains($html, 'id="newFormModal"') && str_contains($html, 'modal-dialog'),
            'falta el modal de creacion');
        $this->addCase($results, 'new_form', str_contains($html, 'id="FormNewData"'),
            'no se dibuja el formulario de creacion');
        $this->addCase($results, 'new_input_nombre', str_contains($html, 'id="NewEstado_Nombre"'),
            'el formulario no declara Nombre con prefijo NewEstado_');
        $this->addCase($results, 'new_estado_inicial_activo',
            preg_match('/<input[^>]*name="idEstado"[^>]*value="1"|<input[^>]*value="1"[^>]*name="idEstado"/', $html) === 1 && !str_contains($html, 'id="NewEstado_idEstado"'),
            'el trabajo nuevo debe crearse activo (idEstado = 1 oculto) sin ofrecer el estado');
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
        $this->addCase($results, 'edit_inputs', str_contains($html, 'id="EditEstado_Nombre"') && str_contains($html, 'id="EditEstado_idEstado"'),
            'el formulario no declara Nombre e idEstado con prefijo EditEstado_');
        $this->addCase($results, 'edit_valor_nombre', str_contains($html, 'Trabajo Sintetico'),
            'el formulario no precarga el nombre del trabajo');
        $this->addCase($results, 'edit_estado_seleccionado',
            preg_match('/<option[^>]*value="2"[^>]*selected/', $html) === 1 && str_contains($html, 'Activo Prueba') && str_contains($html, 'Inactivo Prueba'),
            'el formulario no ofrece el catalogo de estados con el estado del trabajo preseleccionado');
        $this->addCase($results, 'edit_id_oculto', preg_match('/<input[^>]*name="idTrabajo"[^>]*value="7"|<input[^>]*value="7"[^>]*name="idTrabajo"/', $html) === 1,
            'el formulario no incluye el idTrabajo en un campo oculto');
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
            str_contains($html, 'Trabajo Sintetico') && str_contains($html, 'Activo Prueba') && str_contains($html, 'badge-sp1-success'),
            'el detalle no muestra el nombre y el estado del trabajo con su color');
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

            $Post      = ['Nombre' => 'Trabajo Prueba', 'idEstado' => 1];
            $DataCheck = $reflector->invoke($Controller, $Post);

            $this->addCase($results, 'datacheck_es_arreglo', is_array($DataCheck), 'dataCheck() no devolvio un arreglo');
            $this->addCase($results, 'datacheck_privado', $reflector->isPrivate(), 'dataCheck() debe ser privado');
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post,
                'dataCheck() no conserva el POST recibido');
            $this->addCase($results, 'datacheck_claves_contrato',
                isset($DataCheck['emptyData'], $DataCheck['ValidarNumero'], $DataCheck['ValidarEntero'], $DataCheck['ValidarLargoMinimoN'], $DataCheck['ValidarLargoMaximoPorCampo']),
                'dataCheck() no entrega las claves del contrato de CheckData');
            $this->addCase($results, 'datacheck_estado_entero',
                $DataCheck['ValidarEntero'] === 'idEstado' && $DataCheck['ValidarNumero'] === 'idEstado',
                'dataCheck() no valida idEstado como numero entero');
            $this->addCase($results, 'datacheck_largos',
                $DataCheck['ValidarLargoMinimo'] === 'Nombre'
                && (int)$DataCheck['ValidarLargoMinimoN'] === 3 && (int)($DataCheck['ValidarLargoMaximoPorCampo']['Nombre'] ?? 0) === 255,
                'dataCheck() no limita el nombre a 3..255 caracteres');
            $this->addCase($results, 'datacheck_censura', $DataCheck['ValidarPalabrasCensuradas'] === 'Nombre',
                'dataCheck() no filtra palabras censuradas en el nombre');

            /*====================================*/
            // El chequeo real (CheckData) acepta un POST valido y rechaza uno invalido
            /*====================================*/
            $this->addCase($results, 'checkdata_acepta_valido', ($this->chequeo($Post)['status'] ?? false) === true,
                'CheckData rechazo un POST valido de trabajo');
            $casos = [
                'checkdata_rechaza_nombre_corto'   => ['Nombre' => 'ab'],
                'checkdata_rechaza_nombre_largo'   => ['Nombre' => str_repeat('a', 256)],
                'checkdata_rechaza_estado_texto'   => ['idEstado' => 'x'],
                'checkdata_rechaza_estado_decimal' => ['idEstado' => '1.5'],
            ];
            foreach ($casos as $caso => $cambio) {
                $this->addCase($results, $caso, ($this->chequeo(array_merge($Post, $cambio))['status'] ?? true) === false,
                    'CheckData acepto un trabajo con '.json_encode($cambio));
            }
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
     * Revisa estaticamente las vistas y el controlador de trabajos: rutas relativas, rutas
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
            'se esperaban 6 vistas de kanbanTrabajos y se encontraron '.count($arrVistas));

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
        $this->addCase($results, 'tabla_id_cifrado', str_contains($tabla, "encryptDecrypt('encrypt', \$crud['idTrabajo'])"),
            'UpdateList no cifra idTrabajo en las acciones');
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
        $this->addCase($results, 'controlador_consultas_con_params',
            preg_match_all('/\$this->Base_Get(?:List|ByID|CountData)\(/', $controlador) === preg_match_all("/'params'\s*=>/", $controlador)
            && preg_match_all("/'params'\s*=>/", $controlador) === 7,
            'las 7 consultas de lectura (2 de listAll, 1 de UpdateList, 1 de View, 2 de GetID y el conteo de uso de Delete) deben declarar la clave params');
        $this->addCase($results, 'controlador_where_con_params', substr_count($controlador, "\$r['params']") === 3,
            'los tres searchWhere() deben acumular sus parametros preparados');
        $this->addCase($results, 'controlador_sin_get_ni_request', !str_contains($controlador, '$_GET') && !str_contains($controlador, '$_REQUEST'),
            'el controlador lee $_GET o $_REQUEST directamente');
        $this->addCase($results, 'controlador_post_solo_en_escritura', substr_count($controlador, '$_POST') === 4,
            'se esperaban 4 usos de $_POST (dataCheck y Post de Insert y Update)');

        /************************************/
        // Retorno los datos
        return $results;

    }

}
