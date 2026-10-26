<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class kanbanTareasParticipantesTesting
 *
 * Pruebas del modulo `kanban` - controlador `kanbanTareasParticipantes` (alta y baja de
 * participantes de una tarea: `NewData`, `Insert`, `Delete`).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. Los textos de los casos
 *    correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Alcance: pruebas NO destructivas. `Insert()` y `Delete()` escriben en base de datos y
 * terminan en `Response::*()` (que ejecuta `exit`); `NewData()` consulta la BD y termina en
 * `showVista()`. Ninguno se invoca: se verifican por reflexion y lectura del fuente (por
 * metodo, con `cuerpoMetodo()`), y la vista `-formNew` se renderiza con datos sinteticos.
 *
 * Particularidades del controlador:
 *  - No tiene permiso propio: cuelga del permiso de `kanbanTareas`
 *    (`controllerName = 'kanbanTareas'`) y su vista se llama `kanbanTareasParticipantes-formNew`
 *    (se arma como `controllerName.'Participantes-formNew.php'`).
 *  - Sus rutas no estan bajo `.../tareas/...` sino en `.../tareasParticipantes` (la vista las
 *    arma como `RouteAccess.'Participantes'`).
 *  - Es un sub-recurso del detalle de la tarea: la vista se abre dentro del modal XL.
 *
 * @see skills/testing/SKILL.md
 */
class kanbanTareasParticipantesTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR         = 'kanbanTareasParticipantes';
    private const CONTROLADOR_PERMISO = 'kanbanTareas';
    private const INSTALADOR          = 'kanbanTareasInstaller';
    private const RUTA_WEB            = 'gestionProyectos/kanban/tareas';
    private const RUTA_WEB_BASE       = 'gestionProyectos/kanban/tareasParticipantes';
    private const RUTA_WEB_NEWDATA    = 'gestionProyectos/kanban/tareasParticipantes/newData/@id';
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
    // Datos sinteticos usados por la vista del controlador
    /*******************************************************************/
    private function datosSinteticos(int $subtitulo = 1): array{
        return [
            'UserData'         => [
                'UserID'               => 9,
                'MainPathUrl'          => '/admin/public/upload/',
                'sistemaModalSubtitle' => $subtitulo,
                'sistemaModalCloseBTN' => 2,
                'UserType'             => 2,
            ],
            'UserAccess'       => ['LevelAccess' => 2, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_FormInputs'   => new UIFormInputs(),
            'Fnc_Codification' => new FunctionsSecurityCodification(),
            'Fnc_ServerServer' => new FunctionsServerServer(),
            'rowData'          => ['idKanban' => 55, 'Titulo' => 'Tarea Sintetica Uno'],
            'arrUsuarios'      => [['ID' => 3, 'Nombre' => 'Usuario Prueba'], ['ID' => 4, 'Nombre' => 'Otro Usuario']],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica los archivos del controlador `kanbanTareasParticipantes` y la convencion
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
            'testing'         => $this->rutaModulo('kanbanTareasParticipantesTesting.php'),
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
        $this->addCase($results, 'clase_testing_cargada', class_exists('kanbanTareasParticipantesTesting'),
            'la clase kanbanTareasParticipantesTesting no esta disponible');
        $padre = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padre === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padre.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('kanbanTareasParticipantesTesting') === 'ControllerBase',
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
            'controllerName debe ser '.self::CONTROLADOR_PERMISO.' (los participantes cuelgan del permiso de las tareas)');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                            CONTROLADOR: VISTAS                             */
    /******************************************************************************/
    /**
     * Verifica el contrato de `NewData` (formulario de alta): ID cifrado, tarea de destino,
     * exclusion de los participantes que ya estan en la tarea y manejo de fallos.
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();
        $newData = $this->cuerpoMetodo('NewData');

        /*====================================*/
        // Firma: NewData($f3, $params)
        /*====================================*/
        $this->firmaMetodo($results, 'NewData', 2, 2);

        /*====================================*/
        // Sin vistas de listado: es un sub-recurso del detalle de la tarea
        /*====================================*/
        foreach (['listAll', 'UpdateList', 'View', 'GetID', 'Print'] as $metodo) {
            $this->addCase($results, 'sin_vista_'.$metodo, !method_exists(self::CONTROLADOR, $metodo),
                'kanbanTareasParticipantes no debe exponer '.$metodo.'(): el detalle lo pinta kanbanTareas');
        }

        /*====================================*/
        // Contrato de presentacion
        /*====================================*/
        $this->addCase($results, 'vista_newdata', str_contains($newData, "\$this->controllerName.'Participantes-formNew.php'"),
            'NewData() no instancia la vista kanbanTareasParticipantes-formNew');
        $this->addCase($results, 'tipoview_newdata', str_contains($newData, 'showVista(2,') && !str_contains($newData, 'showVista(1,'),
            'NewData() debe usar showVista(2) (fragmento para el modal)');
        $this->addCase($results, 'rutavista_resuelta', str_contains($newData, "returnRutaVista(__DIR__, 'app')"),
            'NewData() no resuelve la ruta de la vista con returnRutaVista()');
        $this->addCase($results, 'usuario_y_nivel_en_vista',
            str_contains($newData, 'getUserData($f3)') && str_contains($newData, 'getArrLevel($f3, $this->controllerName)'),
            'NewData() no obtiene los datos del usuario y el nivel de acceso');
        $this->addCase($results, 'newdata_maneja_fallo',
            str_contains($newData, "if(\$rowData['status'] && \$arrUsuarios['status'])") && str_contains($newData, 'mergeResponses([$rowData,$arrUsuarios])')
            && str_contains($newData, 'showError(2, $f3, $result)'),
            'NewData() debe comprobar el status de la tarea y los usuarios y usar showError(2)/mergeResponses()');

        /*====================================*/
        // ID cifrado
        /*====================================*/
        $this->addCase($results, 'id_descifrado', str_contains($newData, "encryptDecrypt('decrypt', \$params['id'])"),
            'NewData() debe descifrar el ID de la ruta');
        $this->addCase($results, 'id_validado', str_contains($newData, "isValidDecrypted(\$KanbanID, 'id')"),
            'NewData() debe validar el ID descifrado');
        $this->addCase($results, 'id_invalido_400', str_contains($newData, "Response::error('Registro inválido', 400)"),
            'un ID invalido debe responder 400');

        /*====================================*/
        // Consultas: la tarea, sus participantes actuales y los usuarios disponibles
        /*====================================*/
        $this->addCase($results, 'newdata_tarea',
            str_contains($newData, "'data'    => 'idKanban,Titulo'") && str_contains($newData, "'table'   => 'kanban_tareas'")
            && str_contains($newData, "'where'   => 'idKanban = ?'") && str_contains($newData, "'params'  => [\$KanbanID['data']]"),
            'NewData() no lee la tarea por idKanban con el ID descifrado como parametro preparado');
        $this->addCase($results, 'newdata_participantes_actuales',
            str_contains($newData, "'table'   => 'kanban_tareas_participantes'") && str_contains($newData, "'data'    => 'idUsuario'"),
            'NewData() no lee los participantes actuales de la tarea');
        $this->addCase($results, 'newdata_excluye_existentes',
            str_contains($newData, '$ElementsIDs = [0];') && str_contains($newData, "array_column(\$arrExistentes['data'], 'idUsuario')")
            && str_contains($newData, "idUsuario NOT IN ('.\$placeholders.')"),
            'NewData() debe excluir de la lista a los usuarios que ya participan (con un 0 de guarda para el NOT IN vacio)');
        $this->addCase($results, 'newdata_in_preparado',
            str_contains($newData, "implode(',', array_fill(0, count(\$ElementsIDs), '?'))") && str_contains($newData, "'params'  => array_merge([1, 1], \$ElementsIDs)"),
            'el NOT IN debe armarse con un placeholder por identificador y parametros preparados');
        $this->addCase($results, 'newdata_usuarios_activos_no_admin',
            str_contains($newData, "'idTipoUsuario != ? AND idEstado = ?") && str_contains($newData, "'order'   => 'Nombre ASC'"),
            'NewData() debe ofrecer solo usuarios activos que no son administradores, ordenados por nombre');
        $this->addCase($results, 'consultas_acotadas', substr_count($codigo, 'ConfigAPP::APP["N_MaxItems"]') === 2,
            'las dos consultas de listado deben acotar el numero de filas');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                           CONTROLADOR: ESCRITURA                           */
    /******************************************************************************/
    /**
     * Verifica `Insert` y `Delete` SIN ejecutarlos, metodo por metodo: usuario de sesion,
     * validaciones previas, escritura por participante, historial y respuestas al cliente.
     *
     * @return array Resultado por caso
     */
    public function controladorEscrituraTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Firmas: Insert($f3); Delete()
        /*====================================*/
        $this->firmaMetodo($results, 'Insert', 1, 1);
        $this->firmaMetodo($results, 'Delete', 1, 1);

        /*====================================*/
        // Insert: verbo, participantes y tarea validos antes de escribir, transaccion e historial
        /*====================================*/
        $insert = $this->cuerpoMetodo('Insert');
        $this->addCase($results, 'insert_verbo_post',
            str_contains($insert, "REQUEST_METHOD'] !== 'POST'") && str_contains($insert, "Response::error('Error en el Request Method', 405)")
            && strpos($insert, "REQUEST_METHOD'] !== 'POST'") < strpos($insert, 'Base_transactionBegin'),
            'Insert() debe validar el verbo HTTP POST (405) antes de abrir la transaccion');
        $this->addCase($results, 'insert_usuario_de_sesion', (bool)preg_match("/\\\$_POST\['idUsuario'\]\s*=\s*\\\$f3->get\('SESSION\.DataInfo\.UserID'\)/", $insert),
            'Insert() debe tomar el usuario creador de la sesion y no del formulario');
        $this->addCase($results, 'insert_exige_participantes',
            str_contains($insert, "if(\$ndata_2==0)") && str_contains($insert, "Response::error('No hay Participantes en la tarea', 400)"),
            'Insert() debe rechazar una peticion sin participantes con 400 (entrada invalida)');
        $this->addCase($results, 'insert_valida_tarea_abierta',
            str_contains($insert, "\$this->validarTareaAbierta(\$_POST['idKanban'] ?? '')")
            && strpos($insert, 'validarTareaAbierta') < strpos($insert, 'Base_transactionBegin'),
            'Insert() debe comprobar que la tarea existe y esta abierta antes de abrir la transaccion');
        $this->addCase($results, 'insert_valida_antes_de_escribir',
            strpos($insert, 'No hay Participantes') < strpos($insert, 'Base_transactionBegin') && strpos($insert, 'Base_transactionBegin') < strpos($insert, '$this->Base_insertMultiple('),
            'las validaciones deben ocurrir antes de la transaccion y las escrituras dentro de ella');
        $this->addCase($results, 'insert_filas_en_bloque',
            str_contains($insert, 'for($j2 = 0; $j2 < $ndata_2; $j2++)') && str_contains($insert, "'idKanban'  => \$_POST['idKanban']")
            && str_contains($insert, "'idUsuario' => \$_POST['idParticipante'][\$j2]") && substr_count($insert, '$this->Base_insertMultiple(') === 1,
            'Insert() debe acumular un registro por participante e insertarlos en una sola sentencia');
        $this->addCase($results, 'insert_campos',
            str_contains($insert, "'data'      => 'idKanban,idUsuario'") && str_contains($insert, "'required'  => 'idKanban,idUsuario'")
            && str_contains($insert, "'table'     => 'kanban_tareas_participantes'"),
            'Insert() no declara los campos y obligatorios del participante');
        $this->addCase($results, 'insert_chequeos', str_contains($insert, "\$this->dataCheck_1('')") && str_contains($insert, '$this->dataCheck_2($arrTareas)'),
            'Insert() debe chequear cada fila de participante (dataCheck_1) y el historial (dataCheck_2)');
        $this->addCase($results, 'insert_historial',
            str_contains($insert, "'Descripcion' => 'Se agregan nuevos encargados'") && str_contains($insert, "'table'     => 'kanban_tareas_historial'")
            && str_contains($insert, "'Fecha'       => \$this->ServerServer->fechaActual()") && str_contains($insert, "'Hora'        => \$this->ServerServer->horaActual()")
            && !str_contains($insert, 'Fecha_Actual') && !str_contains($insert, 'Hora_Actual') && str_contains($insert, "'novalidate' => true"),
            'Insert() debe registrar «Se agregan nuevos encargados» en el historial con la fecha y hora del servidor');
        $this->addCase($results, 'insert_transaccion_completa',
            substr_count($insert, 'Base_transactionBegin()') === 1 && substr_count($insert, 'Base_transactionCommit()') === 1 && substr_count($insert, 'Base_transactionRollback()') === 0
            && substr_count($insert, 'failOperation(') >= 2
            && strpos($insert, 'Base_transactionCommit()') < strpos($insert, 'Response::success('),
            'Insert() debe abrir una transaccion, revertirla ante el fallo de los participantes o del historial y confirmarla antes de responder');
        $this->addCase($results, 'insert_comprueba_resultados',
            str_contains($insert, "if (\$respParticipantes['status'] === false)") && str_contains($insert, "if (\$xInsert['status'] === false)"),
            'Insert() debe comprobar el resultado de cada escritura (antes siempre respondia 200)');
        $this->addCase($results, 'insert_respuesta_id', substr_count($insert, 'Response::success(') === 1 && str_contains($insert, "Response::success(\$_POST['idKanban'])"),
            'Insert() debe responder con el ID de la tarea');

        /*====================================*/
        // validarTareaAbierta (privado): la tarea existe y esta abierta
        /*====================================*/
        $helper = $this->cuerpoMetodo('validarTareaAbierta');
        $this->addCase($results, 'helper_tarea_abierta',
            str_contains($helper, "'where'   => 'idKanban = ?'") && str_contains($helper, "'params'  => [is_scalar(\$idKanban) ? \$idKanban : '']")
            && str_contains($helper, "Response::error('Registro inválido', 400)") && str_contains($helper, "Response::error('La tarea está cerrada', 400)")
            && str_contains($helper, "(int)\$rowData['data']['idEstadoCierre'] !== 1") && str_contains($helper, "if (empty(\$rowData['data']))"),
            'validarTareaAbierta() debe leer la tarea con parametros preparados y responder 400 si no existe o esta cerrada');

        /*====================================*/
        // Delete: verbo, IDs validados antes de tocar la BD, borrado por idParticipantes e historial
        /*====================================*/
        $delete = $this->cuerpoMetodo('Delete');
        $this->addCase($results, 'delete_verbo', str_contains($delete, "REQUEST_METHOD'] !== 'DELETE'") && str_contains($delete, "Response::error('Error en el Request Method', 405)"),
            'Delete() no valida el verbo HTTP DELETE con respuesta 405');
        $this->addCase($results, 'delete_lee_cuerpo', str_contains($delete, 'parse_str(file_get_contents("php://input")'),
            'Delete() no lee el cuerpo DELETE');
        $this->addCase($results, 'delete_ids_cifrados',
            str_contains($delete, "encryptDecrypt('decrypt', \$dataDelete['idParticipantes'] ?? '')") && str_contains($delete, "encryptDecrypt('decrypt', \$dataDelete['idKanbanDel'] ?? '')")
            && substr_count($delete, 'isValidDecrypted(') === 2 && substr_count($delete, "Response::error('Registro inválido', 400)") === 3,
            'Delete() debe descifrar y validar idParticipantes e idKanbanDel (400 ante un ID invalido) y rechazar un participante inexistente');
        $this->addCase($results, 'delete_valida_ids_antes_de_borrar',
            strpos($delete, "isValidDecrypted(\$KanbanDelID, 'id')") < strpos($delete, '$this->Base_delete(')
            && strpos($delete, "isValidDecrypted(\$ParticipantesID, 'id')") < strpos($delete, '$this->Base_delete('),
            'Delete() debe validar idParticipantes e idKanbanDel ANTES de borrar (un 400 no debe dejar el participante eliminado)');
        $this->addCase($results, 'delete_lee_nombre_antes',
            str_contains($delete, "'data'    => 'usuarios_listado.Nombre'") && str_contains($delete, "'where'   => 'kanban_tareas_participantes.idParticipantes = ?'")
            && str_contains($delete, "'params'  => [\$ParticipantesID['data']]") && strpos($delete, "'data'    => 'usuarios_listado.Nombre'") < strpos($delete, '$this->Base_delete('),
            'Delete() debe leer el nombre del participante (parametro preparado) antes de borrarlo');
        $this->addCase($results, 'delete_comprueba_lectura',
            str_contains($delete, "if (\$rowData['status'] === false)") && str_contains($delete, "if (empty(\$rowData['data']))"),
            'Delete() debe responder 500 si falla la lectura y 400 si el participante no existe');
        $this->addCase($results, 'delete_donde',
            str_contains($delete, "'where'       => 'idParticipantes'") && str_contains($delete, "'table'       => 'kanban_tareas_participantes'") && str_contains($delete, "'files'       => ''"),
            'Delete() no delimita el borrado por idParticipantes ni declara que no maneja archivos');
        $this->addCase($results, 'delete_base', str_contains($delete, '$this->Base_delete('), 'Delete() no borra con Base_delete()');
        $this->addCase($results, 'delete_historial',
            str_contains($delete, "'Encargado '.(\$rowData['data']['Nombre'] ?? '').' Borrado'") && str_contains($delete, "'table'     => 'kanban_tareas_historial'")
            && str_contains($delete, '$this->dataCheck_2($arrTareas)') && str_contains($delete, "'novalidate' => true"),
            'Delete() debe registrar «Encargado <nombre> Borrado» en el historial');
        $this->addCase($results, 'delete_usuario_y_fecha_del_servidor',
            str_contains($delete, "'idUsuario'   => \$f3->get('SESSION.DataInfo.UserID')") && str_contains($delete, "'Fecha'       => \$this->ServerServer->fechaActual()")
            && str_contains($delete, "'Hora'        => \$this->ServerServer->horaActual()")
            && !str_contains($delete, 'idUsuarioDel') && !str_contains($delete, 'Fecha_Actual') && !str_contains($delete, 'Hora_Actual'),
            'Delete() debe tomar el usuario de la sesion y la fecha/hora del servidor, no del cuerpo de la peticion');
        $this->addCase($results, 'delete_respuestas',
            substr_count($delete, 'Response::success(') === 1 && substr_count($delete, 'failOperation(') === 2,
            'Delete() debe responder 200 al terminar y 500 controlado ante un fallo de lectura o borrado');

        /*====================================*/
        // Conjunto: solo Insert usa transaccion y conteos globales
        /*====================================*/
        $this->addCase($results, 'transaccion_solo_en_insert',
            substr_count($codigo, 'Base_transactionBegin()') === 1 && substr_count($codigo, 'Base_transactionCommit()') === 1 && substr_count($codigo, 'Base_transactionRollback()') === 0,
            'solo Insert() debe usar transaccion (1 begin, 1 commit; fallos via failOperation)');
        $this->addCase($results, 'respuestas_exito', substr_count($codigo, 'Response::success(') === 2,
            'se esperaban 2 Response::success() (Insert y Delete)');
        $this->addCase($results, 'respuesta_405', substr_count($codigo, "Response::error('Error en el Request Method', 405)") === 2,
            'Insert() y Delete() deben responder 405 ante un verbo incorrecto');
        $this->addCase($results, 'historial_tabla', substr_count($codigo, "'table'     => 'kanban_tareas_historial'") === 2,
            'Insert() y Delete() deben registrar el historial');
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
     * Verifica que las 3 rutas de `kanbanTareasParticipantes` publicadas por el instalador
     * apunten a metodos reales con el verbo y el nivel esperados, que cuelguen del permiso de
     * tareas y las tablas que escribe el controlador.
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
            'formulario' => ['1 '.self::RUTA_WEB_NEWDATA, 'kanbanTareasParticipantes->NewData', 2],
            'crear'      => ['2 '.self::RUTA_WEB_BASE,    'kanbanTareasParticipantes->Insert',  2],
            'borrar'     => ['3 '.self::RUTA_WEB_BASE,    'kanbanTareasParticipantes->Delete',  2],
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
        $propias = array_filter($rutas, static function ($r) { return $r[2] === 'kanbanTareasParticipantes'; });
        $this->addCase($results, 'rutas_propias_completas', count($propias) === 3,
            'se esperaban 3 rutas de kanbanTareasParticipantes y hay '.count($propias));
        $this->addCase($results, 'rutas_en_tipo_tareas', $this->rutasDeTipo(1, 'kanbanTareasParticipantes') === 3,
            'las 3 rutas deben publicarse en el primer permiso (tareas)');
        $this->addCase($results, 'rutas_en_la_base_de_participantes',
            count(array_filter(array_keys($propias), static function ($k) { return str_contains($k, 'gestionProyectos/kanban/tareasParticipantes'); })) === 3,
            'las rutas deben colgar de gestionProyectos/kanban/tareasParticipantes (la vista las arma como RouteAccess + "Participantes")');

        /*====================================*/
        // Instalador: sin permiso propio y desinstalacion
        /*====================================*/
        $instalador = $this->codigoFuente($this->rutaModulo('../installer/'.self::INSTALADOR.'.php'));
        $this->addCase($results, 'instalador_sin_permiso_propio', !preg_match("/'RutaController'\s*=>\s*'kanbanTareasParticipantes'/", $instalador),
            'kanbanTareasParticipantes declara un permiso propio: debe colgar del permiso de kanbanTareas');
        $this->addCase($results, 'instalador_desinstala_participantes', str_contains($instalador, '"kanbanTareasParticipantes"'),
            'RutaController() no lista kanbanTareasParticipantes y dejaria rutas huerfanas al desinstalar');

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
        $this->addCase($results, 'tabla_participantes_columnas',
            $cols('kanban_tareas_participantes', ['idParticipantes', 'idKanban', 'idUsuario']),
            'kanban_tareas_participantes no tiene idParticipantes, idKanban e idUsuario');
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
     * registro de las rutas de participantes en el router de F3 (solo si el usuario las tiene).
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
            'SESSION.DataInfo.UserID debe existir: Insert() lo usa como usuario creador');

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
            'ruta_newdata_router' => ['/'.self::RUTA_WEB_NEWDATA, 'GET',    self::CONTROLADOR.'->NewData'],
            'ruta_insert_router'  => ['/'.self::RUTA_WEB_BASE,    'POST',   self::CONTROLADOR.'->Insert'],
            'ruta_delete_router'  => ['/'.self::RUTA_WEB_BASE,    'DELETE', self::CONTROLADOR.'->Delete'],
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
    /*                              VISTAS: FORMULARIO                            */
    /******************************************************************************/
    /**
     * Renderiza `kanbanTareasParticipantes-formNew.php` con datos sinteticos: formulario,
     * campos ocultos, fila dinamica de participante con el catalogo, endpoint con su verbo,
     * refresco del detalle y del tablero, boton Volver con ID cifrado y variantes del modal.
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function vistaFormularioTest($f3){

        $results = [];
        $BASE    = (string)$f3->get('BASE');
        if (trim($BASE) === '') {
            $BASE = '/admin';
        }
        $ruta = $this->rutaModulo('../views/'.self::CONTROLADOR.'-formNew.php');

        $html = $this->renderizarVista($ruta, $this->datosSinteticos(1), $BASE, $f3);
        $this->addCase($results, 'new_render', $html !== '', 'la vista formNew no produjo HTML');
        $this->addCase($results, 'new_form', str_contains($html, 'id="FormNewParticipante"'),
            'no se dibuja el formulario de alta de participantes');
        $this->addCase($results, 'new_titulo_tarea', str_contains($html, 'Participantes para') && str_contains($html, 'Tarea Sintetica Uno'),
            'el formulario no indica la tarea de destino');
        $this->addCase($results, 'new_ocultos',
            preg_match('/<input[^>]*name="idKanban"[^>]*value="55"|<input[^>]*value="55"[^>]*name="idKanban"/', $html) === 1
            && !str_contains($html, 'name="Fecha_Actual"') && !str_contains($html, 'name="Hora_Actual"'),
            'el formulario debe enviar idKanban y no la fecha ni la hora (las pone el servidor)');
        $this->addCase($results, 'new_sin_usuario_en_formulario', !str_contains($html, 'name="idUsuario"'),
            'el formulario no debe enviar idUsuario: el controlador lo toma de la sesion');
        $this->addCase($results, 'new_fila_participante',
            str_contains($html, 'name="idParticipante[]"') && str_contains($html, 'Usuario Prueba') && str_contains($html, 'Otro Usuario')
            && str_contains($html, 'participanteNew_add()') && str_contains($html, 'remove_Newparticipante'),
            'el formulario no ofrece agregar/quitar participantes (idParticipante[]) con el catalogo de usuarios');
        $this->addCase($results, 'new_sin_filas_iniciales', !preg_match('/id="insert_Newparticipante_rand_\d+"[^>]*>\s*<[^>]*name="idParticipante\[\]"/', $html),
            'el formulario no debe iniciar con filas de participante ya agregadas (se agregan con el boton)');
        $this->addCase($results, 'new_endpoint_creacion',
            str_contains($html, "let Direccion   = '".$BASE.'/'.self::RUTA_WEB."Participantes'") && str_contains($html, "let Metodo      = 'POST'"),
            'el formulario no envia por POST a '.self::RUTA_WEB_BASE);
        $this->addCase($results, 'new_refresca_detalle_y_tablero',
            str_contains($html, $BASE.'/'.self::RUTA_WEB.'/view/') && !str_contains($html, '/view/55')
            && str_contains($html, $BASE.'/'.self::RUTA_WEB.'/updateList')
            && str_contains($html, "{Div:'#modalContent-xl'") && !str_contains($html, "{Div:'#modalContent',"),
            'el formulario debe refrescar el detalle (ID cifrado, en #modalContent-xl) y el tablero al guardar');
        $this->addCase($results, 'new_volver_id_cifrado', str_contains($html, "listTableDataView('") && !str_contains($html, "listTableDataView('55')"),
            'el boton Volver debe abrir el detalle con el ID cifrado');
        $this->addCase($results, 'new_titulo_sin_subtitulo', str_contains($html, '</i> Crear Nuevo') && !str_contains($html, 'modal-subtitle'),
            'con sistemaModalSubtitle = 1 no debe dibujarse el subtitulo');
        $html2 = $this->renderizarVista($ruta, $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'new_subtitulo_modal', str_contains($html2, 'modal-subtitle') && str_contains($html2, 'Permite crear un nuevo elemento'),
            'con sistemaModalSubtitle = 2 debe dibujarse el subtitulo');
        $sinUsuarios = $this->datosSinteticos(1);
        $sinUsuarios['arrUsuarios'] = [];
        $this->addCase($results, 'new_sin_usuarios_disponibles', $this->renderizarVista($ruta, $sinUsuarios, $BASE, $f3) !== '',
            'el formulario sin usuarios disponibles no se dibuja');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos `dataCheck_1()` (participante) y `dataCheck_2()` (historial)
     * por reflexion, el chequeo real de `CheckData` y el cifrado de identificadores.
     *
     * @return array Resultado por caso
     */
    public function logicaValidacionTest(){

        $results = [];

        /*====================================*/
        // dataCheck_1/2: contrato esperado por Base_insert
        /*====================================*/
        $contratos = [
            'dataCheck_1' => ['fechas' => '',      'horas' => '',     'min' => '',            'censura' => ''],
            'dataCheck_2' => ['fechas' => 'Fecha', 'horas' => 'Hora', 'min' => 'Descripcion', 'censura' => 'Descripcion'],
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
                $this->addCase($results, $metodo.'_ids_enteros',
                    $DataCheck['ValidarNumero'] === 'idKanban,idUsuario' && $DataCheck['ValidarEntero'] === 'idKanban,idUsuario',
                    $metodo.'() no valida idKanban e idUsuario como numeros enteros');
                $this->addCase($results, $metodo.'_fechas_y_horas', $DataCheck['ValidarFecha'] === $esperado['fechas'] && $DataCheck['ValidarHora'] === $esperado['horas'],
                    $metodo.'() no valida las fechas y horas esperadas');
                $this->addCase($results, $metodo.'_textos', $DataCheck['ValidarLargoMinimo'] === $esperado['min'] && $DataCheck['ValidarPalabrasCensuradas'] === $esperado['censura'],
                    $metodo.'() no valida los textos esperados');
            } catch (Throwable $e) {
                $this->addCase($results, $metodo.'_privado', false, 'no se pudo ejecutar '.$metodo.'() ('.$e->getMessage().')');
            }
        }

        /*====================================*/
        // CheckData real sobre el participante (dataCheck_1)
        /*====================================*/
        try {
            $part = ['idKanban' => 55, 'idUsuario' => 3];
            $this->addCase($results, 'participante_acepta_valido', ($this->chequeo('dataCheck_1', $part)['status'] ?? false) === true,
                'CheckData rechazo un participante valido');
            foreach (['idKanban' => 'x', 'idUsuario' => 'x'] as $campo => $valor) {
                $this->addCase($results, 'participante_rechaza_'.$campo.'_texto', ($this->chequeo('dataCheck_1', array_merge($part, [$campo => $valor]))['status'] ?? true) === false,
                    'CheckData acepto un '.$campo.' no numerico');
            }
            $this->addCase($results, 'participante_rechaza_usuario_decimal', ($this->chequeo('dataCheck_1', array_merge($part, ['idUsuario' => '1.5']))['status'] ?? true) === false,
                'CheckData acepto un idUsuario decimal');

            /*====================================*/
            // CheckData real sobre el historial (dataCheck_2)
            /*====================================*/
            $his = ['idKanban' => 55, 'idUsuario' => 3, 'Descripcion' => 'Se agregan nuevos encargados', 'Fecha' => '2026-01-02', 'Hora' => '10:30:00'];
            $this->addCase($results, 'historial_acepta_valido', ($this->chequeo('dataCheck_2', $his)['status'] ?? false) === true,
                'CheckData rechazo un registro de historial valido');
            $this->addCase($results, 'historial_rechaza_descripcion_corta', ($this->chequeo('dataCheck_2', array_merge($his, ['Descripcion' => 'ab']))['status'] ?? true) === false,
                'CheckData acepto una descripcion de historial de 2 caracteres');
            $this->addCase($results, 'historial_rechaza_hora_invalida', ($this->chequeo('dataCheck_2', array_merge($his, ['Hora' => '99:99:99']))['status'] ?? true) === false,
                'CheckData acepto una hora invalida en el historial');
            $this->addCase($results, 'historial_rechaza_fecha_invalida', ($this->chequeo('dataCheck_2', array_merge($his, ['Fecha' => '2026-02-31']))['status'] ?? true) === false,
                'CheckData acepto una fecha invalida en el historial');
        } catch (Throwable $e) {
            $this->addCase($results, 'participante_acepta_valido', false, 'no se pudo ejecutar el chequeo ('.$e->getMessage().')');
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

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                 SEGURIDAD                                  */
    /******************************************************************************/
    /**
     * Revisa estaticamente la vista y el controlador de participantes: rutas relativas,
     * rutas absolutas, acceso a sesion, SQL embebido, cifrado de IDs, validacion del
     * formulario y consultas preparadas.
     *
     * @return array Resultado por caso
     */
    public function seguridadVistasTest(){

        $results = [];

        /*====================================*/
        // Vistas involucradas
        /*====================================*/
        $arrVistas = glob($this->rutaModulo('../views/'.self::CONTROLADOR.'-*.php')) ?: [];
        $this->addCase($results, 'hay_vistas', count($arrVistas) === 1,
            'se esperaba 1 vista de kanbanTareasParticipantes y se encontraron '.count($arrVistas));

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
            $this->addCase($results, 'vista_ids_aleatorios_'.$nombre, str_contains($codigo, "'room_'.rand(") && str_contains($codigo, "'rand_'.rand("),
                $nombre.' debe usar identificadores aleatorios para las filas dinamicas');
        }

        /*====================================*/
        // Controlador: SQL preparado y sin concatenacion de entrada
        /*====================================*/
        $controlador = $this->fuenteControlador();
        $this->addCase($results, 'controlador_sin_concat_sql', !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $controlador),
            'el controlador concatena superglobales en una cadena SQL');
        $this->addCase($results, 'controlador_consultas_con_params',
            preg_match_all('/\$this->Base_Get(?:List|ByID)\(/', $controlador) === preg_match_all("/'params'\s*=>/", $controlador)
            && preg_match_all("/'params'\s*=>/", $controlador) === 5,
            'las 5 consultas de lectura (tarea, participantes, usuarios, nombre y la validacion de tarea abierta) deben declarar la clave params');
        $this->addCase($results, 'controlador_sin_get_ni_request', !str_contains($controlador, '$_GET') && !str_contains($controlador, '$_REQUEST'),
            'el controlador lee $_GET o $_REQUEST directamente');
        $this->addCase($results, 'controlador_usuario_sesion_en_escrituras', substr_count($controlador, "SESSION.DataInfo.UserID") === 2,
            'Insert() y Delete() deben tomar el usuario de la sesion');
        $this->addCase($results, 'controlador_not_in_parametrizado', !preg_match('/NOT IN \(\s*\$/', $controlador) && str_contains($controlador, "NOT IN ('.\$placeholders.')"),
            'el NOT IN debe construirse con placeholders y no con valores concatenados');

        /************************************/
        // Retorno los datos
        return $results;

    }

}
