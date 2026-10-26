<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class kanbanTareasTareasTesting
 *
 * Pruebas del modulo `kanban` - controlador `kanbanTareasTareas` (subtareas de una tarea:
 * `NewData`, `GetID`, `Insert`, `Update`).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. Los textos de los casos
 *    correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Alcance: pruebas NO destructivas. `Insert()` y `Update()` escriben en base de datos y
 * terminan en `Response::*()` (que ejecuta `exit`); `NewData()` y `GetID()` consultan la BD y
 * terminan en `showVista()`. Ninguno se invoca: se verifican por reflexion y lectura del
 * fuente (por metodo, con `cuerpoMetodo()`), y las vistas `-formNew` y `-formEdit` se
 * renderizan con datos sinteticos.
 *
 * Particularidades del controlador:
 *  - No tiene permiso propio: cuelga del permiso de `kanbanTareas`
 *    (`controllerName = 'kanbanTareas'`); sus vistas se arman como
 *    `controllerName.'Tareas-formNew.php'` y `controllerName.'Tareas-formEdit.php'`.
 *  - Sus rutas estan en `.../tareasTareas` (la vista las arma como `RouteAccess.'Tareas'`).
 *  - No tiene `Delete`: las subtareas solo se crean y se editan (su estado pasa por `Update`).
 *  - El trabajo especifico de cada subtarea solo existe con `KanbanTareasUsoTareas = 2`.
 *
 * @see skills/testing/SKILL.md
 */
class kanbanTareasTareasTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR         = 'kanbanTareasTareas';
    private const CONTROLADOR_PERMISO = 'kanbanTareas';
    private const INSTALADOR          = 'kanbanTareasInstaller';
    private const RUTA_WEB            = 'gestionProyectos/kanban/tareas';
    private const RUTA_WEB_BASE       = 'gestionProyectos/kanban/tareasTareas';
    private const RUTA_WEB_NEWDATA    = 'gestionProyectos/kanban/tareasTareas/newData/@id';
    private const RUTA_WEB_GETID      = 'gestionProyectos/kanban/tareasTareas/getID/@id';
    private const RUTA_WEB_UPDATE     = 'gestionProyectos/kanban/tareasTareas/update';
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
    //  - $usoTareas: KanbanTareasUsoTareas (2 = trabajo especifico por subtarea)
    /*******************************************************************/
    private function datosSinteticos(int $usoTareas = 1, int $subtitulo = 1): array{
        return [
            'UserData'         => [
                'UserID'                => 9,
                'MainPathUrl'           => '/admin/public/upload/',
                'sistemaModalSubtitle'  => $subtitulo,
                'sistemaModalCloseBTN'  => 2,
                'UserType'              => 2,
                'KanbanTareasUsoTareas' => $usoTareas,
            ],
            'UserAccess'       => ['LevelAccess' => 2, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_FormInputs'   => new UIFormInputs(),
            'Fnc_Codification' => new FunctionsSecurityCodification(),
            'Fnc_ServerServer' => new FunctionsServerServer(),
            // NewData: la tarea de destino. GetID: la subtarea a editar.
            'rowData'          => [
                'idKanban'        => 55,
                'Titulo'          => 'Tarea Sintetica Uno',
                'idTareas'        => 12,
                'Tarea'           => 'Subtarea Sintetica',
                'idEstadoTrabajo' => 2,
                'idTrabajo'       => 4,
            ],
            'arrTrabajos'      => [['ID' => 4, 'Nombre' => 'Trabajo Catalogo'], ['ID' => 5, 'Nombre' => 'Otro Trabajo']],
            'arrEstadoTrabajo' => [['ID' => 1, 'Nombre' => 'Abierto Prueba'], ['ID' => 2, 'Nombre' => 'Hecho Prueba']],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica los archivos del controlador `kanbanTareasTareas` y la convencion
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
            'controlador'     => $this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'),
            'instalador'      => $this->rutaModulo('../installer/'.self::INSTALADOR.'.php'),
            'vista_form_new'  => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formNew.php'),
            'vista_form_edit' => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formEdit.php'),
            'testing'         => $this->rutaModulo('kanbanTareasTareasTesting.php'),
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
        $this->addCase($results, 'clase_testing_cargada', class_exists('kanbanTareasTareasTesting'),
            'la clase kanbanTareasTareasTesting no esta disponible');
        $padre = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padre === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padre.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('kanbanTareasTareasTesting') === 'ControllerBase',
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
            'controllerName debe ser '.self::CONTROLADOR_PERMISO.' (las subtareas cuelgan del permiso de las tareas)');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                            CONTROLADOR: VISTAS                             */
    /******************************************************************************/
    /**
     * Verifica el contrato de `NewData` (alta) y `GetID` (edicion): ID cifrado, consultas,
     * catalogo de trabajos solo con tareas especificas y manejo de fallos.
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();
        $newData = $this->cuerpoMetodo('NewData');
        $getId   = $this->cuerpoMetodo('GetID');

        /*====================================*/
        // Firmas: NewData/GetID($f3, $params)
        /*====================================*/
        foreach (['NewData', 'GetID'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 2, 2);
        }

        /*====================================*/
        // Sin vistas de listado: es un sub-recurso del detalle de la tarea
        /*====================================*/
        foreach (['listAll', 'UpdateList', 'View', 'Print', 'Delete'] as $metodo) {
            $this->addCase($results, 'sin_metodo_'.$metodo, !method_exists(self::CONTROLADOR, $metodo),
                'kanbanTareasTareas no debe exponer '.$metodo.'(): las subtareas solo se crean y se editan');
        }

        /*====================================*/
        // Contrato de presentacion
        /*====================================*/
        $this->addCase($results, 'vista_newdata', str_contains($newData, "\$this->controllerName.'Tareas-formNew.php'"),
            'NewData() no instancia la vista kanbanTareasTareas-formNew');
        $this->addCase($results, 'vista_getid', str_contains($getId, "\$this->controllerName.'Tareas-formEdit.php'"),
            'GetID() no instancia la vista kanbanTareasTareas-formEdit');
        foreach (['NewData' => $newData, 'GetID' => $getId] as $metodo => $cuerpo) {
            $this->addCase($results, 'tipoview_'.$metodo, str_contains($cuerpo, 'showVista(2,') && !str_contains($cuerpo, 'showVista(1,'),
                $metodo.'() debe usar showVista(2) (fragmento para el modal)');
            $this->addCase($results, 'rutavista_'.$metodo, str_contains($cuerpo, "returnRutaVista(__DIR__, 'app')"),
                $metodo.'() no resuelve la ruta de la vista con returnRutaVista()');
            $this->addCase($results, 'usuario_y_nivel_'.$metodo,
                str_contains($cuerpo, "'UserData'      => \$this->getUserData(\$f3)") && str_contains($cuerpo, 'getArrLevel($f3, $this->controllerName)'),
                $metodo.'() no entrega los datos del usuario y el nivel de acceso a la vista');
            $this->addCase($results, 'id_descifrado_'.$metodo, str_contains($cuerpo, "encryptDecrypt('decrypt', \$params['id'])"),
                $metodo.'() debe descifrar el ID de la ruta');
            $this->addCase($results, 'id_validado_'.$metodo, str_contains($cuerpo, 'isValidDecrypted(') && str_contains($cuerpo, "Response::error('Registro inválido', 400)"),
                $metodo.'() debe validar el ID descifrado y responder 400 ante uno invalido');
            $this->addCase($results, 'trabajos_solo_si_se_usan_'.$metodo,
                str_contains($cuerpo, '$arrUserData["KanbanTareasUsoTareas"]==2') && str_contains($cuerpo, "'table'   => 'kanban_trabajos'")
                && str_contains($cuerpo, "'where'   => 'idEstado = ?'") && str_contains($cuerpo, "\$arrTrabajos['data']   = [];"),
                $metodo.'() debe cargar los trabajos activos solo con tareas especificas (si no, lista vacia)');
        }
        $this->addCase($results, 'newdata_maneja_fallo',
            str_contains($newData, "if(\$rowData['status'] && \$arrTrabajos['status'])") && str_contains($newData, 'mergeResponses([$rowData,$arrTrabajos])')
            && str_contains($newData, 'showError(2, $f3, $result)'),
            'NewData() debe comprobar el status de la tarea y los trabajos y usar showError(2)/mergeResponses()');
        $this->addCase($results, 'getid_maneja_fallo',
            str_contains($getId, "if(\$rowData['status'] && \$arrTrabajos['status'] && \$arrEstadoTrabajo['status'])") && str_contains($getId, 'mergeResponses([$rowData,$arrTrabajos,$arrEstadoTrabajo])')
            && str_contains($getId, 'showError(2, $f3, $result)'),
            'GetID() debe comprobar el status de las tres consultas y usar showError(2)/mergeResponses()');

        /*====================================*/
        // Consultas
        /*====================================*/
        $this->addCase($results, 'newdata_tarea',
            str_contains($newData, "'data'    => 'idKanban,Titulo'") && str_contains($newData, "'table'   => 'kanban_tareas'")
            && str_contains($newData, "'where'   => 'idKanban = ?'") && str_contains($newData, "'params'  => [\$KanbanID['data']]"),
            'NewData() no lee la tarea por idKanban con el ID descifrado como parametro preparado');
        $this->addCase($results, 'getid_subtarea',
            str_contains($getId, "'data'    => 'idTareas,idKanban,Tarea,idEstadoTrabajo,idTrabajo'") && str_contains($getId, "'table'   => 'kanban_tareas_tareas'")
            && str_contains($getId, "'where'   => 'idTareas = ?'") && str_contains($getId, "'params'  => [\$TareasID['data']]"),
            'GetID() no lee la subtarea por idTareas con el ID descifrado como parametro preparado');
        $this->addCase($results, 'getid_catalogo_estados',
            str_contains($getId, "'table'   => 'core_estados_trabajos'") && str_contains($getId, "'data'    => 'idEstadoTrabajo AS ID,Nombre'")
            && str_contains($getId, "'order'   => 'idEstadoTrabajo ASC'"),
            'GetID() no carga el catalogo de estados de trabajo');
        $this->addCase($results, 'consultas_acotadas', substr_count($codigo, 'ConfigAPP::APP["N_MaxItems"]') >= 2,
            'las consultas de catalogo deben acotar el numero de filas');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                           CONTROLADOR: ESCRITURA                           */
    /******************************************************************************/
    /**
     * Verifica `Insert` y `Update` SIN ejecutarlos, metodo por metodo: usuario de sesion,
     * validaciones previas, escritura por subtarea, comparacion de valores antiguos,
     * historial y respuestas al cliente.
     *
     * @return array Resultado por caso
     */
    public function controladorEscrituraTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Firmas: Insert/Update($f3)
        /*====================================*/
        foreach (['Insert', 'Update'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 1, 1);
        }

        /*====================================*/
        // Insert: verbo, subtareas y tarea validas antes de escribir, transaccion e historial acumulado
        /*====================================*/
        $insert = $this->cuerpoMetodo('Insert');
        $this->addCase($results, 'insert_verbo_post',
            str_contains($insert, "REQUEST_METHOD'] !== 'POST'") && str_contains($insert, "Response::error('Error en el Request Method', 405)")
            && strpos($insert, "REQUEST_METHOD'] !== 'POST'") < strpos($insert, 'Base_transactionBegin'),
            'Insert() debe validar el verbo HTTP POST (405) antes de abrir la transaccion');
        $this->addCase($results, 'insert_usuario_de_sesion', (bool)preg_match("/\\\$_POST\['idUsuario'\]\s*=\s*\\\$f3->get\('SESSION\.DataInfo\.UserID'\)/", $insert),
            'Insert() debe tomar el usuario creador de la sesion y no del formulario');
        $this->addCase($results, 'insert_exige_tareas',
            str_contains($insert, "if (\$ndata_1==0)") && str_contains($insert, "Response::error('No hay Tareas nuevas creadas', 400)"),
            'Insert() debe rechazar una peticion sin subtareas con 400 (entrada invalida)');
        $this->addCase($results, 'insert_valida_tarea_abierta',
            str_contains($insert, "\$this->validarTareaAbierta(\$_POST['idKanban'] ?? '')") && strpos($insert, 'validarTareaAbierta') < strpos($insert, 'Base_transactionBegin'),
            'Insert() debe comprobar que la tarea existe y esta abierta antes de abrir la transaccion');
        $this->addCase($results, 'insert_valida_antes_de_escribir',
            strpos($insert, 'No hay Tareas nuevas creadas') < strpos($insert, 'Base_transactionBegin') && strpos($insert, 'Base_transactionBegin') < strpos($insert, '$this->Base_insertMultiple('),
            'las validaciones deben ocurrir antes de la transaccion y las escrituras dentro de ella');
        $this->addCase($results, 'insert_filas_en_bloque',
            str_contains($insert, 'for($j1 = 0; $j1 < $ndata_1; $j1++)') && str_contains($insert, "'idKanban'         => \$_POST['idKanban']")
            && str_contains($insert, "'Tarea'            => \$_POST['Tarea'][\$j1]") && str_contains($insert, "'idEstadoTrabajo'  => 1")
            && str_contains($insert, "'idTrabajo'        => \$_POST['idTrabajo'][\$j1] ?? ''") && substr_count($insert, '$this->Base_insertMultiple(') === 1,
            'Insert() debe acumular una subtarea abierta por cada texto recibido (trabajo opcional) e insertarlas en una sola sentencia');
        $this->addCase($results, 'insert_campos',
            str_contains($insert, "'data'      => 'idKanban,Tarea,idEstadoTrabajo,idTrabajo'") && str_contains($insert, "'required'  => 'idKanban,Tarea,idEstadoTrabajo'")
            && str_contains($insert, "'table'     => 'kanban_tareas_tareas'"),
            'Insert() no declara los campos y obligatorios de la subtarea (idTrabajo es opcional)');
        $this->addCase($results, 'insert_chequeos', str_contains($insert, "\$this->dataCheck_1('')") && str_contains($insert, '$this->dataCheck_2($arrTareas)'),
            'Insert() debe chequear cada fila de subtarea (dataCheck_1) y el historial (dataCheck_2)');
        $this->addCase($results, 'insert_historial',
            str_contains($insert, "\$DataTarea  = 'Nueva Tarea Asignada:'") && str_contains($insert, "\$DataTarea .= '<br/> - '.\$_POST['Tarea'][\$j1]")
            && str_contains($insert, "'Descripcion' => \$DataTarea") && str_contains($insert, "'table'     => 'kanban_tareas_historial'")
            && str_contains($insert, "'Fecha'       => \$this->ServerServer->fechaActual()") && str_contains($insert, "'Hora'        => \$this->ServerServer->horaActual()")
            && !str_contains($insert, 'Fecha_Actual') && !str_contains($insert, 'Hora_Actual') && str_contains($insert, "'novalidate' => true"),
            'Insert() debe registrar en el historial una sola entrada «Nueva Tarea Asignada» con todas las subtareas, con la fecha y hora del servidor');
        $this->addCase($results, 'insert_transaccion_completa',
            substr_count($insert, 'Base_transactionBegin()') === 1 && substr_count($insert, 'Base_transactionCommit()') === 1 && substr_count($insert, 'Base_transactionRollback()') === 0
            && substr_count($insert, 'failOperation(') >= 2
            && strpos($insert, 'Base_transactionCommit()') < strpos($insert, 'Response::success('),
            'Insert() debe abrir una transaccion, revertirla ante el fallo de las subtareas o del historial y confirmarla antes de responder');
        $this->addCase($results, 'insert_comprueba_resultados',
            str_contains($insert, "if (\$respTareas['status'] === false)") && str_contains($insert, "if (\$xInsert['status'] === false)"),
            'Insert() debe comprobar el resultado de cada escritura (antes siempre respondia 200)');
        $this->addCase($results, 'insert_respuesta_id', substr_count($insert, 'Response::success(') === 1 && str_contains($insert, "Response::success(\$_POST['idKanban'])"),
            'Insert() debe responder con el ID de la tarea');

        /*====================================*/
        // Helpers privados de validacion previa
        /*====================================*/
        $helper = $this->cuerpoMetodo('validarTareaAbierta');
        $this->addCase($results, 'helper_tarea_abierta',
            str_contains($helper, "'where'   => 'idKanban = ?'") && str_contains($helper, "'params'  => [is_scalar(\$idKanban) ? \$idKanban : '']")
            && str_contains($helper, "Response::error('Registro inválido', 400)") && str_contains($helper, "Response::error('La tarea está cerrada', 400)")
            && str_contains($helper, "(int)\$rowData['data']['idEstadoCierre'] !== 1") && str_contains($helper, "if (empty(\$rowData['data']))"),
            'validarTareaAbierta() debe leer la tarea con parametros preparados y responder 400 si no existe o esta cerrada');
        $helper2 = $this->cuerpoMetodo('validarSubtareaDeTareaAbierta');
        $this->addCase($results, 'helper_subtarea_de_tarea_abierta',
            str_contains($helper2, "'join'    => 'INNER JOIN kanban_tareas ON kanban_tareas.idKanban = kanban_tareas_tareas.idKanban'")
            && str_contains($helper2, "'where'   => 'kanban_tareas_tareas.idTareas = ? AND kanban_tareas_tareas.idKanban = ?'")
            && str_contains($helper2, "'params'  => [is_scalar(\$idTareas) ? \$idTareas : '', is_scalar(\$idKanban) ? \$idKanban : '']")
            && str_contains($helper2, "Response::error('Registro inválido', 400)") && str_contains($helper2, "Response::error('La tarea está cerrada', 400)")
            && str_contains($helper2, "(int)\$rowData['data']['idEstadoCierre'] !== 1"),
            'validarSubtareaDeTareaAbierta() debe exigir que la subtarea pertenezca a la tarea enviada y que esta este abierta (400 en otro caso)');

        /*====================================*/
        // Update: verbo, usuario de sesion, subtarea de una tarea abierta, escritura por idTareas e historial
        /*====================================*/
        $update = $this->cuerpoMetodo('Update');
        $this->addCase($results, 'update_verbo_post', str_contains($update, "REQUEST_METHOD'] !== 'POST'") && str_contains($update, "Response::error('Error en el Request Method', 405)"),
            'Update() no valida el verbo HTTP POST con respuesta 405');
        $this->addCase($results, 'update_usuario_de_sesion', (bool)preg_match("/\\\$_POST\['idUsuario'\]\s*=\s*\\\$f3->get\('SESSION\.DataInfo\.UserID'\)/", $update),
            'Update() debe tomar el usuario de la sesion y no del formulario');
        $this->addCase($results, 'update_valida_subtarea_y_tarea',
            str_contains($update, "\$this->validarSubtareaDeTareaAbierta(\$_POST['idTareas'] ?? '', \$_POST['idKanban'] ?? '')")
            && strpos($update, 'validarSubtareaDeTareaAbierta') < strpos($update, '$this->Base_update('),
            'Update() debe comprobar que la subtarea pertenece a la tarea enviada y que esta abierta antes de escribir');
        $this->addCase($results, 'update_donde', str_contains($update, "'where'     => 'idTareas'"),
            'Update() no delimita la escritura por idTareas');
        $this->addCase($results, 'update_campos',
            str_contains($update, "'data'      => 'idTareas,Tarea,idEstadoTrabajo,idTrabajo'") && str_contains($update, "'required'  => 'idTareas,Tarea,idEstadoTrabajo'")
            && str_contains($update, "'table'     => 'kanban_tareas_tareas'"),
            'Update() no declara los campos y obligatorios de la subtarea');
        $this->addCase($results, 'update_base', str_contains($update, '$this->Base_update(') && str_contains($update, '$this->dataCheck_1($_POST)'),
            'Update() debe chequear con dataCheck_1 y escribir con Base_update()');
        $this->addCase($results, 'update_falla_500', str_contains($update, "if (\$Response['status'] === false)") && str_contains($update, "failOperation("),
            'Update() debe responder 500 controlado ante un fallo de escritura');
        $this->addCase($results, 'update_catalogos_con_status',
            str_contains($update, "(\$arrEstadoTrabajo['status'] ? \$arrEstadoTrabajo['data'] : [])") && str_contains($update, "(\$arrTrabajos['status'] ? \$arrTrabajos['data'] : [])"),
            'Update() debe recorrer los catalogos solo si su consulta respondio (si no, listas vacias)');
        $this->addCase($results, 'update_compara_valores_antiguos',
            str_contains($update, "'Old_' . \$campo") && str_contains($update, "'Tarea'           => ['label' => 'Tarea']")
            && str_contains($update, "'idEstadoTrabajo' => ['label' => 'Estado'") && str_contains($update, "'idTrabajo'       => ['label' => 'Trabajo'"),
            'Update() debe comparar Tarea, Estado y Trabajo con sus valores antiguos (Old_*)');
        $this->addCase($results, 'update_historial_solo_si_cambia',
            str_contains($update, "if(\$comparacion!='')") && str_contains($update, "'Se cambian datos de la tarea:'.\$comparacion")
            && str_contains($update, "'table'     => 'kanban_tareas_historial'") && str_contains($update, "'novalidate' => true")
            && str_contains($update, "'Fecha'       => \$this->ServerServer->fechaActual()") && str_contains($update, "'Hora'        => \$this->ServerServer->horaActual()")
            && !str_contains($update, 'Fecha_Actual') && !str_contains($update, 'Hora_Actual'),
            'Update() debe registrar en el historial solo cuando hay cambios, con la fecha y hora del servidor');
        $this->addCase($results, 'update_respuesta', substr_count($update, 'Response::success(') === 1 && str_contains($update, "Response::success(\$Response['data'])"),
            'Update() debe responder 200 con el resultado');
        $this->addCase($results, 'update_sin_transaccion', !str_contains($update, 'Base_transaction'),
            'Update() no usa transaccion (una sola escritura principal): si se agrega, debe actualizarse esta prueba');

        /*====================================*/
        // Conjunto: solo Insert usa transaccion, sin borrado
        /*====================================*/
        $this->addCase($results, 'transaccion_solo_en_insert',
            substr_count($codigo, 'Base_transactionBegin()') === 1 && substr_count($codigo, 'Base_transactionCommit()') === 1 && substr_count($codigo, 'Base_transactionRollback()') === 0,
            'solo Insert() debe usar transaccion (1 begin, 1 commit; fallos via failOperation)');
        $this->addCase($results, 'sin_borrado', !str_contains($codigo, 'Base_delete') && !str_contains($codigo, "REQUEST_METHOD'] !== 'DELETE'"),
            'las subtareas no se borran desde este controlador');
        $this->addCase($results, 'respuestas_exito', substr_count($codigo, 'Response::success(') === 2,
            'se esperaban 2 Response::success() (Insert y Update)');
        $this->addCase($results, 'respuesta_405', substr_count($codigo, "Response::error('Error en el Request Method', 405)") === 2,
            'Insert() y Update() deben responder 405 ante un verbo incorrecto');
        $this->addCase($results, 'sin_fecha_ni_hora_del_cliente', !str_contains($codigo, 'Fecha_Actual') && !str_contains($codigo, 'Hora_Actual'),
            'el controlador no debe leer Fecha_Actual ni Hora_Actual del cliente: la fecha y la hora las pone el servidor');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                       CONTROLADOR: RUTAS E INSTALADOR                      */
    /******************************************************************************/
    /**
     * Verifica que las 4 rutas de `kanbanTareasTareas` publicadas por el instalador apunten a
     * metodos reales con el verbo y el nivel esperados, que cuelguen del permiso de tareas y
     * las tablas que escribe el controlador.
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
            'formulario' => ['1 '.self::RUTA_WEB_NEWDATA, 'kanbanTareasTareas->NewData', 2],
            'getid'      => ['1 '.self::RUTA_WEB_GETID,   'kanbanTareasTareas->GetID',   2],
            'crear'      => ['2 '.self::RUTA_WEB_BASE,    'kanbanTareasTareas->Insert',  2],
            'editar'     => ['2 '.self::RUTA_WEB_UPDATE,  'kanbanTareasTareas->Update',  2],
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
        // Conjunto completo de rutas propias, en el primer permiso (tareas en curso), sin borrado
        /*====================================*/
        $propias = array_filter($rutas, static function ($r) { return $r[2] === 'kanbanTareasTareas'; });
        $this->addCase($results, 'rutas_propias_completas', count($propias) === 4,
            'se esperaban 4 rutas de kanbanTareasTareas y hay '.count($propias));
        $this->addCase($results, 'rutas_en_tipo_tareas', $this->rutasDeTipo(1, 'kanbanTareasTareas') === 4,
            'las 4 rutas deben publicarse en el primer permiso (tareas)');
        $sinBorrado = true;
        foreach (array_keys($propias) as $clave) {
            if ((int)substr($clave, 0, 1) >= 3) {
                $sinBorrado = false;
            }
        }
        $this->addCase($results, 'rutas_sin_borrado_ni_put', $sinBorrado, 'las subtareas no deben publicar rutas DELETE o PUT');

        /*====================================*/
        // Instalador: sin permiso propio y desinstalacion
        /*====================================*/
        $instalador = $this->codigoFuente($this->rutaModulo('../installer/'.self::INSTALADOR.'.php'));
        $this->addCase($results, 'instalador_sin_permiso_propio', !preg_match("/'RutaController'\s*=>\s*'kanbanTareasTareas'/", $instalador),
            'kanbanTareasTareas declara un permiso propio: debe colgar del permiso de kanbanTareas');
        $this->addCase($results, 'instalador_desinstala_subtareas', str_contains($instalador, '"kanbanTareasTareas"'),
            'RutaController() no lista kanbanTareasTareas y dejaria rutas huerfanas al desinstalar');

        /*====================================*/
        // Tablas que escribe el controlador
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
        $cols = static function (string $tabla, array $columnas) use ($tablas): bool {
            foreach ($columnas as $c) {
                if (!str_contains($tablas[$tabla] ?? '', '`'.$c.'`')) {
                    return false;
                }
            }
            return true;
        };
        $this->addCase($results, 'tabla_subtareas_columnas',
            $cols('kanban_tareas_tareas', ['idTareas', 'idKanban', 'Tarea', 'idEstadoTrabajo', 'idTrabajo']),
            'kanban_tareas_tareas no tiene idTareas, idKanban, Tarea, idEstadoTrabajo e idTrabajo');
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
     * Verifica la sesion, el nivel de acceso heredado del permiso de `kanbanTareas` y el
     * registro de las rutas de subtareas en el router de F3 (solo si el usuario las tiene).
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
                if (isset($permiso['RutaWeb']) && $permiso['RutaWeb'] === self::RUTA_WEB_NEWDATA) {
                    $rutaOk = true;
                    break;
                }
            }
            $this->addCase($results, 'ruta_registrada', $rutaOk || $nivel === null || (int)$nivel < 2,
                'el usuario tiene nivel '.var_export($nivel, true).' pero la ruta '.self::RUTA_WEB_NEWDATA.' no esta en sus permisos');
        }

        $arrRoutes = $f3->get('ROUTES');
        $this->addCase($results, 'router_disponible', is_array($arrRoutes), 'F3 no expuso la variable ROUTES');
        $esquema = [
            'ruta_newdata_router' => ['/'.self::RUTA_WEB_NEWDATA, 'GET',  self::CONTROLADOR.'->NewData'],
            'ruta_getid_router'   => ['/'.self::RUTA_WEB_GETID,   'GET',  self::CONTROLADOR.'->GetID'],
            'ruta_insert_router'  => ['/'.self::RUTA_WEB_BASE,    'POST', self::CONTROLADOR.'->Insert'],
            'ruta_update_router'  => ['/'.self::RUTA_WEB_UPDATE,  'POST', self::CONTROLADOR.'->Update'],
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
     * Renderiza `kanbanTareasTareas-formNew.php` y `-formEdit.php` con datos sinteticos:
     * formularios, campos ocultos y antiguos (Old_*), trabajo solo con tareas especificas,
     * fila dinamica de subtarea, endpoints con su verbo, refresco del detalle (ID cifrado)
     * y variantes del modal.
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
        // Formulario de alta (varias subtareas en filas dinamicas)
        /*====================================*/
        $html = $this->renderizarVista($rutaNew, $this->datosSinteticos(1), $BASE, $f3);
        $this->addCase($results, 'new_render', $html !== '', 'la vista formNew no produjo HTML');
        $this->addCase($results, 'new_form', str_contains($html, 'id="FormNewTareas"'), 'no se dibuja el formulario de alta de subtareas');
        $this->addCase($results, 'new_titulo_tarea', str_contains($html, 'Tareas para') && str_contains($html, 'Tarea Sintetica Uno'),
            'el formulario no indica la tarea de destino');
        $this->addCase($results, 'new_ocultos',
            preg_match('/<input[^>]*name="idKanban"[^>]*value="55"|<input[^>]*value="55"[^>]*name="idKanban"/', $html) === 1
            && !str_contains($html, 'name="Fecha_Actual"') && !str_contains($html, 'name="Hora_Actual"'),
            'el formulario debe enviar idKanban y no la fecha ni la hora (las pone el servidor)');
        $this->addCase($results, 'new_sin_usuario_en_formulario', !str_contains($html, 'name="idUsuario"'),
            'el formulario no debe enviar idUsuario: el controlador lo toma de la sesion');
        $this->addCase($results, 'new_fila_subtarea',
            str_contains($html, 'name="Tarea[]"') && str_contains($html, 'tareaNew_add()') && str_contains($html, 'remove_Newtarea'),
            'el formulario no ofrece agregar/quitar subtareas (Tarea[])');
        $this->addCase($results, 'new_sin_trabajo_si_no_se_usa', !str_contains($html, 'name="idTrabajo[]"'),
            'sin tareas especificas no debe ofrecerse el trabajo de cada subtarea');
        $htmlTrabajo = $this->renderizarVista($rutaNew, $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'new_con_trabajo_si_se_usa', str_contains($htmlTrabajo, 'name="idTrabajo[]"') && str_contains($htmlTrabajo, 'Trabajo Catalogo'),
            'con tareas especificas debe ofrecerse el trabajo de cada subtarea con su catalogo');
        $this->addCase($results, 'new_endpoint_creacion',
            str_contains($html, "let Direccion   = '".$BASE.'/'.self::RUTA_WEB."Tareas'") && str_contains($html, "let Metodo      = 'POST'"),
            'el formulario no envia por POST a '.self::RUTA_WEB_BASE);
        $this->addCase($results, 'new_refresca_detalle',
            str_contains($html, $BASE.'/'.self::RUTA_WEB.'/view/') && !str_contains($html, '/view/55')
            && str_contains($html, "{Div:'#modalContent-xl'") && !str_contains($html, "{Div:'#modalContent',"),
            'el formulario debe refrescar el detalle (ID cifrado) en #modalContent-xl, el contenedor que existe');
        $this->addCase($results, 'new_volver_id_cifrado', str_contains($html, "listTableDataView('") && !str_contains($html, "listTableDataView('55')"),
            'el boton Volver debe abrir el detalle con el ID cifrado');
        $this->addCase($results, 'new_titulo_sin_subtitulo', str_contains($html, '</i> Crear Nuevo') && !str_contains($html, 'modal-subtitle'),
            'con sistemaModalSubtitle = 1 no debe dibujarse el subtitulo');
        $html2 = $this->renderizarVista($rutaNew, $this->datosSinteticos(1, 2), $BASE, $f3);
        $this->addCase($results, 'new_subtitulo_modal', str_contains($html2, 'modal-subtitle') && str_contains($html2, 'Permite crear un nuevo elemento'),
            'con sistemaModalSubtitle = 2 debe dibujarse el subtitulo');

        /*====================================*/
        // Formulario de edicion (una subtarea)
        /*====================================*/
        $html = $this->renderizarVista($rutaEdit, $this->datosSinteticos(1), $BASE, $f3);
        $this->addCase($results, 'edit_render', $html !== '', 'la vista formEdit no produjo HTML');
        $this->addCase($results, 'edit_form', str_contains($html, 'id="FormEditTareas"'), 'no se dibuja el formulario de edicion');
        $this->addCase($results, 'edit_inputs', str_contains($html, 'id="EditTareas_Tarea"') && str_contains($html, 'id="EditTareas_idEstadoTrabajo"'),
            'el formulario no declara Tarea e idEstadoTrabajo con prefijo EditTareas_');
        $this->addCase($results, 'edit_valores_precargados',
            str_contains($html, 'Subtarea Sintetica') && preg_match('/<option[^>]*value="2"[^>]*selected/', $html) === 1
            && str_contains($html, 'Abierto Prueba') && str_contains($html, 'Hecho Prueba'),
            'el formulario no precarga el texto ni preselecciona el estado de la subtarea con su catalogo');
        $this->addCase($results, 'edit_sin_trabajo_si_no_se_usa', !str_contains($html, 'name="idTrabajo"') && !str_contains($html, 'name="Old_idTrabajo"'),
            'sin tareas especificas no debe ofrecerse el trabajo');
        $htmlTrabajo = $this->renderizarVista($rutaEdit, $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'edit_con_trabajo_si_se_usa',
            str_contains($htmlTrabajo, 'name="idTrabajo"') && str_contains($htmlTrabajo, 'name="Old_idTrabajo"')
            && preg_match('/<option[^>]*value="4"[^>]*selected/', $htmlTrabajo) === 1,
            'con tareas especificas debe ofrecerse el trabajo preseleccionado y su valor antiguo');
        $this->addCase($results, 'edit_ocultos_antiguos',
            str_contains($html, 'name="idKanban"') && str_contains($html, 'name="idTareas"') && str_contains($html, 'name="Old_Tarea"')
            && str_contains($html, 'name="Old_idEstadoTrabajo"') && !str_contains($html, 'name="Fecha_Actual"') && !str_contains($html, 'name="Hora_Actual"'),
            'el formulario debe enviar idKanban, idTareas y los valores antiguos (Old_*), y no la fecha ni la hora (las pone el servidor)');
        $this->addCase($results, 'edit_sin_usuario_en_formulario', !str_contains($html, 'name="idUsuario"'),
            'el formulario no debe enviar idUsuario: el controlador lo toma de la sesion');
        $this->addCase($results, 'edit_endpoint_update',
            str_contains($html, "let Direccion   = '".$BASE.'/'.self::RUTA_WEB."Tareas/update'") && str_contains($html, "let Metodo      = 'POST'"),
            'el formulario no envia por POST a '.self::RUTA_WEB_UPDATE);
        $this->addCase($results, 'edit_refresca_detalle',
            str_contains($html, $BASE.'/'.self::RUTA_WEB.'/view/') && !str_contains($html, '/view/55')
            && str_contains($html, "{Div:'#modalContent-xl'") && !str_contains($html, "{Div:'#modalContent',"),
            'el formulario debe refrescar el detalle (ID cifrado) en #modalContent-xl, el contenedor que existe');
        $this->addCase($results, 'edit_volver_id_cifrado', str_contains($html, "listTableDataView('") && !str_contains($html, "listTableDataView('55')"),
            'el boton Volver debe abrir el detalle con el ID cifrado');
        $html2 = $this->renderizarVista($rutaEdit, $this->datosSinteticos(1, 2), $BASE, $f3);
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
     * Ejecuta sin base de datos `dataCheck_1()` (subtarea) y `dataCheck_2()` (historial) por
     * reflexion, el chequeo real de `CheckData` y el cifrado de identificadores.
     *
     * @return array Resultado por caso
     */
    public function logicaValidacionTest(){

        $results = [];

        /*====================================*/
        // dataCheck_1/2: contrato esperado por Base_insert/Base_update
        /*====================================*/
        $contratos = [
            'dataCheck_1' => ['numeros' => 'idKanban,idEstadoTrabajo,idTrabajo', 'fechas' => '',      'horas' => '',     'min' => 'Tarea',       'max' => 'Tarea', 'censura' => 'Tarea'],
            'dataCheck_2' => ['numeros' => 'idKanban,idUsuario',                 'fechas' => 'Fecha', 'horas' => 'Hora', 'min' => 'Descripcion', 'max' => '',      'censura' => 'Descripcion'],
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
                    isset($DataCheck['emptyData'], $DataCheck['ValidarNumero'], $DataCheck['ValidarEntero'], $DataCheck['ValidarFecha'], $DataCheck['ValidarHora'], $DataCheck['ValidarLargoMinimoN'], $DataCheck['ValidarLargoMaximoPorCampo']),
                    $metodo.'() no entrega las claves del contrato de CheckData');
                $this->addCase($results, $metodo.'_numeros_enteros',
                    $DataCheck['ValidarNumero'] === $esperado['numeros'] && $DataCheck['ValidarEntero'] === $esperado['numeros'],
                    $metodo.'() no valida '.$esperado['numeros'].' como numeros enteros');
                $this->addCase($results, $metodo.'_fechas_y_horas', $DataCheck['ValidarFecha'] === $esperado['fechas'] && $DataCheck['ValidarHora'] === $esperado['horas'],
                    $metodo.'() no valida las fechas y horas esperadas');
                $this->addCase($results, $metodo.'_textos',
                    $DataCheck['ValidarLargoMinimo'] === $esperado['min']
                    && (int)$DataCheck['ValidarLargoMinimoN'] === 3
                    && ($esperado['max'] === ''
                        ? $DataCheck['ValidarLargoMaximoPorCampo'] === []
                        : (int)($DataCheck['ValidarLargoMaximoPorCampo'][$esperado['max']] ?? 0) === 255)
                    && $DataCheck['ValidarPalabrasCensuradas'] === $esperado['censura'],
                    $metodo.'() no limita y filtra los textos esperados ('.$esperado['min'].')');
            } catch (Throwable $e) {
                $this->addCase($results, $metodo.'_privado', false, 'no se pudo ejecutar '.$metodo.'() ('.$e->getMessage().')');
            }
        }

        /*====================================*/
        // CheckData real sobre la subtarea (dataCheck_1)
        /*====================================*/
        try {
            $sub = ['idKanban' => 55, 'idEstadoTrabajo' => 1, 'idTrabajo' => 4, 'Tarea' => 'Hacer algo'];
            $this->addCase($results, 'subtarea_acepta_valida', ($this->chequeo('dataCheck_1', $sub)['status'] ?? false) === true,
                'CheckData rechazo una subtarea valida');
            $this->addCase($results, 'subtarea_acepta_trabajo_vacio', ($this->chequeo('dataCheck_1', array_merge($sub, ['idTrabajo' => '']))['status'] ?? false) === true,
                'CheckData rechazo una subtarea sin trabajo (Insert() envia idTrabajo vacio cuando no se usan tareas especificas)');
            $casos = [
                'subtarea_rechaza_texto_corto'   => ['Tarea' => 'ab'],
                'subtarea_rechaza_texto_largo'   => ['Tarea' => str_repeat('a', 256)],
                'subtarea_rechaza_trabajo_texto' => ['idTrabajo' => 'x'],
                'subtarea_rechaza_estado_decimal'=> ['idEstadoTrabajo' => '1.5'],
                'subtarea_rechaza_kanban_texto'  => ['idKanban' => 'x'],
            ];
            foreach ($casos as $caso => $cambio) {
                $this->addCase($results, $caso, ($this->chequeo('dataCheck_1', array_merge($sub, $cambio))['status'] ?? true) === false,
                    'CheckData acepto una subtarea con '.json_encode($cambio));
            }

            /*====================================*/
            // CheckData real sobre el historial (dataCheck_2)
            /*====================================*/
            $his = ['idKanban' => 55, 'idUsuario' => 3, 'Descripcion' => 'Nueva Tarea Asignada:', 'Fecha' => '2026-01-02', 'Hora' => '10:30:00'];
            $this->addCase($results, 'historial_acepta_valido', ($this->chequeo('dataCheck_2', $his)['status'] ?? false) === true,
                'CheckData rechazo un registro de historial valido');
            $this->addCase($results, 'historial_rechaza_descripcion_corta', ($this->chequeo('dataCheck_2', array_merge($his, ['Descripcion' => 'ab']))['status'] ?? true) === false,
                'CheckData acepto una descripcion de historial de 2 caracteres');
            $this->addCase($results, 'historial_rechaza_hora_invalida', ($this->chequeo('dataCheck_2', array_merge($his, ['Hora' => '99:99:99']))['status'] ?? true) === false,
                'CheckData acepto una hora invalida en el historial');
            $this->addCase($results, 'historial_rechaza_fecha_invalida', ($this->chequeo('dataCheck_2', array_merge($his, ['Fecha' => '2026-02-31']))['status'] ?? true) === false,
                'CheckData acepto una fecha invalida en el historial');
        } catch (Throwable $e) {
            $this->addCase($results, 'subtarea_acepta_valida', false, 'no se pudo ejecutar el chequeo ('.$e->getMessage().')');
        }

        /*====================================*/
        // Cifrado de identificadores: ida y vuelta estable y rechazo de basura
        /*====================================*/
        try {
            $Codification = new FunctionsSecurityCodification();
            $cifrado      = $Codification->encryptDecrypt('encrypt', '12');
            $this->addCase($results, 'cifrado_genera_dato',
                ($cifrado['success'] ?? false) === true && !empty($cifrado['data']),
                'encryptDecrypt(encrypt) no produjo un valor cifrado');

            $descifrado = $Codification->encryptDecrypt('decrypt', (string)($cifrado['data'] ?? ''));
            $this->addCase($results, 'cifrado_ida_y_vuelta',
                ($descifrado['success'] ?? false) === true && (string)($descifrado['data'] ?? '') === '12',
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

    /******************************************************************************/
    /*                                 SEGURIDAD                                  */
    /******************************************************************************/
    /**
     * Revisa estaticamente las vistas y el controlador de subtareas: rutas relativas, rutas
     * absolutas, acceso a sesion, SQL embebido, cifrado de IDs, validacion de formularios y
     * consultas preparadas.
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
            'se esperaban 2 vistas de kanbanTareasTareas y se encontraron '.count($arrVistas));

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
            $this->addCase($results, 'vista_id_cifrado_'.$nombre, str_contains($codigo, "encryptDecrypt('encrypt', \$data['rowData']['idKanban'])"),
                $nombre.' no cifra idKanban para volver al detalle');
        }
        $this->addCase($results, 'form_new_ids_aleatorios',
            str_contains($this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-formNew.php')), "'room_'.rand(")
            && str_contains($this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-formNew.php')), "'rand_'.rand("),
            'formNew debe usar identificadores aleatorios para las filas dinamicas');

        /*====================================*/
        // Controlador: SQL preparado y sin concatenacion de entrada
        /*====================================*/
        $controlador = $this->fuenteControlador();
        // La unica concatenacion permitida es el texto del historial (valor de una columna enlazado
        // como parametro preparado, no un fragmento SQL): '<br/> - '.$_POST['Tarea'][$j1]
        $sinHistorial = str_replace("\$DataTarea .= '<br/> - '.\$_POST['Tarea'][\$j1];", '', $controlador);
        $this->addCase($results, 'controlador_sin_concat_sql', !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $sinHistorial),
            'el controlador concatena superglobales en una cadena SQL (salvo el texto del historial)');
        $this->addCase($results, 'controlador_concat_historial_acotada', substr_count($controlador, "'<br/> - '.\$_POST['Tarea']") === 1 && !str_contains($controlador, "'where'   => '.\$_POST") && !str_contains($controlador, "'join'    => '.\$_POST"),
            'la unica concatenacion de entrada permitida es la del texto del historial de Insert()');
        $this->addCase($results, 'controlador_consultas_con_params',
            preg_match_all('/\$this->Base_Get(?:List|ByID)\(/', $controlador) === preg_match_all("/'params'\s*=>/", $controlador),
            'cada consulta de lectura (Base_GetList/Base_GetByID) debe declarar la clave params');
        $this->addCase($results, 'controlador_sin_get_ni_request', !str_contains($controlador, '$_GET') && !str_contains($controlador, '$_REQUEST'),
            'el controlador lee $_GET o $_REQUEST directamente');
        $this->addCase($results, 'controlador_usuario_sesion_en_escrituras', substr_count($controlador, "SESSION.DataInfo.UserID") === 2,
            'Insert() y Update() deben tomar el usuario de la sesion');

        /************************************/
        // Retorno los datos
        return $results;

    }

}
