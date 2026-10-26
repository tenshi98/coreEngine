<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class cotizacionListadoTesting
 *
 * Pruebas del modulo `cotizaciones` - controlador `cotizacionListado` (cabecera de la cotizacion).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. El normalizador
 *    `sistemaTesteos::normalizeResult()` marca EXITOSA o ERROR segun el contenido, por lo que
 *    los textos de los casos correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Alcance: pruebas NO destructivas. Los metodos que escriben en base de datos (`Insert()`,
 * `Update()`, `Delete()`, `delFiles()`) y el que recalcula montos (`updateCotizacion()`)
 * terminan en `Response::success()`/`Response::error()` o abren transacciones; por eso NO se
 * invocan: se verifican por reflexion y por lectura del fuente. `updateCotizacion()` solo se
 * ejecuta con un `$NewDBConn` no nulo (jamas abre transaccion) y sin tocar la base.
 *
 * @see skills/testing/SKILL.md
 */
class cotizacionListadoTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR      = 'cotizacionListado';
    private const RUTA_WEB         = 'cotizacionListado/ventas/listado';
    private const RUTA_WEB_LISTADO = 'cotizacionListado/ventas/listado/listAll';
    private const RUTA_WEB_SEARCH  = 'cotizacionListado/ventas/listado/search';
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
    // Fuente del instalador del modulo
    /*******************************************************************/
    private function fuenteInstalador(): string{
        return $this->codigoFuente($this->rutaModulo('../installer/cotizacionInstaller.php'));
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
    // Contrato de presentacion de un metodo (vista, errores y usuario)
    /*******************************************************************/
    private function contratoPresentacion(array &$results, string $metodo, string $vista, string $tipoVista){
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'vista_'.$metodo, str_contains($codigo, $vista),
            $metodo.'() no instancia la vista '.$vista);
        $this->addCase($results, 'tipoview_'.$metodo, str_contains($codigo, 'showVista('.$tipoVista.','),
            $metodo.'() no usa showVista('.$tipoVista.')');
        $this->addCase($results, 'rutavista_'.$metodo, str_contains($codigo, 'returnRutaVista('),
            $metodo.'() no resuelve la ruta de la vista');
        $this->addCase($results, 'rutaruta_'.$metodo, str_contains($codigo, '$this->controllerName'),
            $metodo.'() no referencia $this->controllerName');
        $this->addCase($results, 'errores_'.$metodo, str_contains($codigo, 'showError('),
            $metodo.'() no maneja la ruta de error con showError()');
        $this->addCase($results, 'usuario_'.$metodo, str_contains($codigo, 'getUserData($f3)'),
            $metodo.'() no obtiene los datos del usuario');
    }

    /*******************************************************************/
    // Rutas del instalador: [idMetodo.' '.RutaWeb => [handler, nivel, controller]]
    // (se lee getRouteDefinitions() sin tocar la base de datos)
    /*******************************************************************/
    private function rutasInstalador(): array{
        $rutas = [];
        if (!class_exists('cotizacionInstaller')) {
            return $rutas;
        }
        try {
            $instalador = (new ReflectionClass('cotizacionInstaller'))->newInstanceWithoutConstructor();
            foreach ([1, 2] as $tipo) {
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
    private function datosSinteticos(int $nivel): array{
        return [
            'PageTitle'       => 'Listado Cotizaciones',
            'PageDescription' => 'Listado Cotizaciones',
            'PageAuthor'      => 'Pruebas',
            'PageKeywords'    => 'Pruebas',
            'TableTitle'      => 'Listado Cotizaciones',
            'UserData'        => [
                'MainPathUrl'          => '/admin/public/upload/',
                'sistemaModalSubtitle' => 1,
                'sistemaModalCloseBTN' => 2,
                'UserType'             => 2,
            ],
            'UserAccess'      => ['LevelAccess' => $nivel, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_FormInputs'     => new UIFormInputs(),
            'Fnc_Codification'   => new FunctionsSecurityCodification(),
            'Fnc_DataDate'       => new FunctionsDataDate(),
            'Fnc_DataNumbers'    => new FunctionsDataNumbers(),
            'Fnc_WidgetsCommon'  => new UIWidgetsCommon(),
            'Fnc_WidgetsViews'  => new UIWidgetsViews(),
            'Fnc_ServerServer'   => new FunctionsServerServer(),
            'arrList'         => [[
                'idCotizacion'        => 77,
                'Creacion_fecha'      => '2026-01-02',
                'ValorTotal'          => 119000,
                'idTipoEntidad'       => 2,
                'EntidadesNombre'     => '',
                'EntidadesApellido'   => '',
                'EntidadesRazonSocial' => 'Entidad Prueba SpA',
                'EntidadesNick'       => '',
            ]],
            'arrEntidades'    => [['ID' => 1, 'Nombre' => 'Entidad Prueba SpA']],
            'arrProductos'    => [['ID' => 1, 'Nombre' => 'Producto Prueba']],
            'arrServicios'    => [['ID' => 1, 'Nombre' => 'Servicio Prueba']],
            'arrItems'        => [['Item' => 'Item de prueba', 'Number' => 2, 'ValorTotal' => 20000]],
            'rowData'         => [
                'idCotizacion'        => 77,
                'idEntidad'           => 1,
                'Creacion_fecha'      => '2026-01-02',
                'Creacion_hora'       => '10:00:00',
                'fecha_auto'          => '2026-01-02',
                'Observaciones'       => 'Observacion de prueba',
                'ValorNeto'           => 100000,
                'IVA'                 => 19000,
                'ValorTotal'          => 119000,
                'idTipoEntidad'       => 2,
                'EntidadesNombre'     => '',
                'EntidadesApellido'   => '',
                'EntidadesRazonSocial' => 'Entidad Prueba SpA',
                'EntidadesNick'       => '',
                'Usuario'             => 'Tester',
            ],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica la estructura de carpetas y archivos del controlador `cotizacionListado`
     * y la convencion "clase = nombre de archivo" exigida por el AUTOLOAD de Fat-Free.
     *
     * @return array Resultado por caso
     */
    public function estructuraModuloTest(){

        $results = [];

        /*====================================*/
        // Archivos obligatorios del controlador
        /*====================================*/
        $arrArchivos = [
            'controlador'          => $this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'),
            'instalador'           => $this->rutaModulo('../installer/cotizacionInstaller.php'),
            'vista_list'           => $this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php'),
            'vista_update_list'    => $this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'),
            'vista_view'           => $this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'),
            'vista_print'          => $this->rutaModulo('../views/'.self::CONTROLADOR.'-Print.php'),
            'vista_resumen'        => $this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen.php'),
            'vista_resumen_update' => $this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen-Update.php'),
            'vista_form_new'       => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formNew.php'),
            'vista_form_search'    => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formSearch.php'),
            'testing'              => $this->rutaModulo('cotizacionListadoTesting.php'),
            'readme'               => $this->rutaModulo('../README.md'),
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
            $contenido = (string)file_get_contents($ruta);
            $this->addCase($results, 'htaccess_'.$nombre,
                (stripos($contenido, 'deny from all') !== false),
                'el archivo no contiene "deny from all"'
            );
        }

        /*====================================*/
        // Convencion clase = nombre de archivo
        /*====================================*/
        $this->addCase($results, 'clase_controlador_cargada', class_exists(self::CONTROLADOR),
            'la clase '.self::CONTROLADOR.' no esta disponible');
        $this->addCase($results, 'clase_testing_cargada', class_exists('cotizacionListadoTesting'),
            'la clase cotizacionListadoTesting no esta disponible');
        $this->addCase($results, 'clase_instalador_cargada', class_exists('cotizacionInstaller'),
            'la clase cotizacionInstaller no esta disponible');

        /*====================================*/
        // Herencias esperadas por el proyecto
        /*====================================*/
        $padreController = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padreController === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padreController.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('cotizacionListadoTesting') === 'ControllerBase',
            'el testing no extiende ControllerBase');

        /*====================================*/
        // Constructor del controlador: conexion MySQL_1 y clase padre
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
     * Verifica el contrato de los metodos que devuelven HTML (`listAll`, `UpdateList`,
     * `View`, `Printer`/`Print`/`noPrint`, `Resumen`, `ResumenUpdate`): existencia,
     * visibilidad, firma y uso de `showVista()`/`returnRutaVista()`/`getUserData()`.
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];

        /*====================================*/
        // Firma de cada metodo de vista
        /*====================================*/
        $arrMetodos = [
            'listAll'       => [1, 1],
            'UpdateList'    => [1, 1],
            'View'          => [2, 2],
            'Print'         => [2, 2],
            'noPrint'       => [2, 2],
            'Printer'       => [3, 3],
            'Resumen'       => [2, 2],
            'ResumenUpdate' => [2, 2],
        ];
        foreach ($arrMetodos as $metodo => $parametros) {
            $this->firmaMetodo($results, $metodo, $parametros[0], $parametros[1]);
        }

        /*====================================*/
        // Contrato de presentacion de cada metodo
        /*====================================*/
        $this->contratoPresentacion($results, 'listAll', "\$this->controllerName.'-List.php'", '1');
        $this->contratoPresentacion($results, 'UpdateList', "\$this->controllerName.'-UpdateList.php'", '2');
        $this->contratoPresentacion($results, 'View', "\$this->controllerName.'-View.php'", '2');
        $this->contratoPresentacion($results, 'Printer', "\$this->controllerName.'-Print.php'", '4');
        $this->contratoPresentacion($results, 'Resumen', "\$this->controllerName.'-Resumen.php'", '1');
        $this->contratoPresentacion($results, 'ResumenUpdate', "\$this->controllerName.'-Resumen-Update.php'", '2');

        /*====================================*/
        // Print/noPrint delegan en Printer() con el flag de impresion
        /*====================================*/
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'print_delega_imprimir', str_contains($codigo, '$this->Printer($f3, $params, 1)'),
            'Print() no delega en Printer() con el flag 1');
        $this->addCase($results, 'noprint_delega_visualizar', str_contains($codigo, '$this->Printer($f3, $params, 0)'),
            'noPrint() no delega en Printer() con el flag 0');
        $this->addCase($results, 'print_envia_flag', str_contains($codigo, "'Imprimir'         => \$Imprimir"),
            'Printer() no entrega el flag Imprimir a la vista');

        /*====================================*/
        // Metadatos y consultas del listado
        /*====================================*/
        $this->addCase($results, 'listado_titulo', str_contains($codigo, "'PageTitle'"),
            'listAll() no asigna PageTitle');
        $this->addCase($results, 'listado_tabla', str_contains($codigo, "'TableTitle'"),
            'listAll() no asigna TableTitle');
        $this->addCase($results, 'listado_permiso_usuario', str_contains($codigo, 'getArrLevel($f3, $this->controllerName)'),
            'las vistas no resuelven el nivel de acceso del usuario');
        $this->addCase($results, 'listado_fallback_errores', str_contains($codigo, 'mergeResponses('),
            'las vistas no agrupan las respuestas fallidas con mergeResponses()');
        $this->addCase($results, 'listado_consulta_cotizaciones', str_contains($codigo, "'table'   => 'cotizacion_listado'"),
            'listAll() no consulta cotizacion_listado');
        $this->addCase($results, 'listado_consulta_catalogos',
            str_contains($codigo, "'table'   => 'entidades_listado'")
            && str_contains($codigo, "'table'   => 'productos_listado'")
            && str_contains($codigo, "'table'   => 'servicios_listado'"),
            'listAll() no consulta los catalogos de entidades, productos y servicios');
        $this->addCase($results, 'listado_limite', str_contains($codigo, 'ConfigAPP::APP["N_MaxItems"]'),
            'las consultas del listado no acotan el numero de filas');

        /*====================================*/
        // Busqueda: columnas y validacion de fechas
        /*====================================*/
        $this->addCase($results, 'busqueda_campos_exactos', str_contains($codigo, "'idEntidad,idCotizacion'"),
            'UpdateList() no filtra por idEntidad e idCotizacion');
        $this->addCase($results, 'busqueda_rango_fechas', str_contains($codigo, "'Creacion_fecha-F_Inicio-F_Termino'"),
            'UpdateList() no filtra por rango de fechas');
        $this->addCase($results, 'busqueda_valida_fechas', str_contains($codigo, 'searchValidateDates('),
            'UpdateList() no valida las fechas con searchValidateDates()');
        $this->addCase($results, 'busqueda_where_preparado', substr_count($codigo, '$this->searchWhere(') === 3,
            'UpdateList() no construye el WHERE con los tres tipos de searchWhere()');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                         CONTROLADOR: ESCRITURA (CRUD)                      */
    /******************************************************************************/
    /**
     * Verifica el contrato de los metodos de escritura (`Insert`, `Update`, `Delete`,
     * `delFiles`) SIN ejecutarlos: terminan en `Response::*()` (que hace `exit`) y
     * escriben en base de datos. Se comprueba existencia, visibilidad, verbo HTTP,
     * transacciones y respuestas al cliente leyendo el fuente.
     *
     * @return array Resultado por caso
     */
    public function controladorEscrituraTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Firma de cada metodo de escritura
        /*====================================*/
        $arrMetodos = [
            'Insert'   => [1, 1],
            'Update'   => [1, 1],
            'Delete'   => [0, 0],
            'delFiles' => [0, 0],
        ];
        foreach ($arrMetodos as $metodo => $parametros) {
            $this->firmaMetodo($results, $metodo, $parametros[0], $parametros[1]);
        }

        /*====================================*/
        // Insert: usuario de sesion, chequeo, transaccion y respuesta cifrada
        /*====================================*/
        $this->addCase($results, 'insert_usuario_sesion', str_contains($codigo, "\$_POST['idUsuario'] = \$f3->get('SESSION.DataInfo.UserID')"),
            'Insert() no toma el usuario creador de la sesion');
        $this->addCase($results, 'insert_exige_detalle', str_contains($codigo, "'No hay nada ingresado'"),
            'Insert() no rechaza una cotizacion sin items, productos ni servicios');
        $this->addCase($results, 'insert_datacheck', str_contains($codigo, '$this->dataCheck_1($_POST)'),
            'Insert() no construye el DataCheck con dataCheck_1($_POST)');
        $this->addCase($results, 'insert_tabla', str_contains($codigo, "'table'     => 'cotizacion_listado'"),
            'Insert() no apunta a la tabla cotizacion_listado');
        $this->addCase($results, 'insert_requeridos',
            str_contains($codigo, "'required'  => 'idUsuario,idEntidad,fecha_auto,Creacion_fecha'"),
            'Insert() no declara los campos obligatorios de la cabecera');
        $this->addCase($results, 'insert_transaccion',
            str_contains($codigo, 'Base_transactionBegin()')
            && str_contains($codigo, 'Base_transactionCommit()')
            && str_contains($codigo, 'failOperation('),
            'el controlador debe abrir/confirmar transaccion y fallar con failOperation');
        $this->addCase($results, 'insert_rollback_por_paso', substr_count($codigo, 'failOperation(') >= 6,
            'Insert()/Delete() deben revertir con failOperation en cada paso que puede fallar');
        $this->addCase($results, 'insert_detalle_masivo', substr_count($codigo, 'Base_insertMultiple(') === 3,
            'Insert() no inserta items, productos y servicios con Base_insertMultiple()');
        $this->addCase($results, 'insert_tablas_detalle',
            str_contains($codigo, "'table'     => 'cotizacion_listado_items'")
            && str_contains($codigo, "'table'     => 'cotizacion_listado_productos'")
            && str_contains($codigo, "'table'     => 'cotizacion_listado_servicios'"),
            'Insert() no persiste las tres tablas de detalle');
        $this->addCase($results, 'insert_iva', str_contains($codigo, '$x_ValorTotal/1.19'),
            'Insert() no calcula el neto con IVA 19%');
        $this->addCase($results, 'insert_respuesta', substr_count($codigo, 'Response::success(') === 4,
            'se esperaban 4 Response::success() (Insert, Update, Delete y delFiles)');
        $this->addCase($results, 'insert_id_cifrado', str_contains($codigo, "encryptDecrypt('encrypt', \$Response['data'])"),
            'Insert() no cifra el ID devuelto');

        /*====================================*/
        // Update: verifica el verbo y delimita la escritura
        /*====================================*/
        $this->addCase($results, 'update_verbo_post', str_contains($codigo, "REQUEST_METHOD'] !== 'POST'"),
            'Update() no valida el verbo HTTP POST');
        $this->addCase($results, 'update_donde', str_contains($codigo, "'where'     => 'idCotizacion'"),
            'Update() no delimita la escritura por idCotizacion');
        $this->addCase($results, 'update_datacheck', substr_count($codigo, '$this->dataCheck_1($_POST)') >= 2,
            'Update() no valida con dataCheck_1($_POST)');
        $this->addCase($results, 'update_base', str_contains($codigo, '$this->Base_update('),
            'Update() no escribe con Base_update()');

        /*====================================*/
        // Delete: verbo DELETE, transaccion y borrado en cascada
        /*====================================*/
        $this->addCase($results, 'delete_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'DELETE'"),
            'Delete() no valida el verbo HTTP DELETE');
        $this->addCase($results, 'delete_cascada',
            str_contains($codigo, "['files' => '', 'table' => 'cotizacion_listado_items']")
            && str_contains($codigo, "['files' => '', 'table' => 'cotizacion_listado_productos']")
            && str_contains($codigo, "['files' => '', 'table' => 'cotizacion_listado_servicios']"),
            'Delete() no elimina el detalle (items, productos y servicios)');
        $this->addCase($results, 'delete_cabecera', str_contains($codigo, '$this->Base_delete('),
            'Delete() no borra con Base_delete()');

        /*====================================*/
        // delFiles: verbo PUT y respuesta sin archivos propios
        /*====================================*/
        $this->addCase($results, 'delfiles_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'PUT'"),
            'delFiles() no valida el verbo HTTP PUT');
        $this->addCase($results, 'delfiles_sin_archivos', !str_contains($codigo, 'Base_delFiles('),
            'delFiles() borra archivos y el modulo no almacena archivos propios');

        /*====================================*/
        // Respuestas ante verbo incorrecto: 405 en Update, Delete y delFiles
        /*====================================*/
        $this->addCase($results, 'verbo_incorrecto_405',
            substr_count($codigo, "Response::error('Error en el Request Method', 405)") === 3,
            'Update/Delete/delFiles deben responder 405 ante un verbo incorrecto');

        /*====================================*/
        // Verificacion de IDs descifrados antes de consultar
        /*====================================*/
        foreach (['View', 'Printer', 'Resumen', 'ResumenUpdate'] as $metodo) {
            $this->addCase($results, 'id_descifrado_'.$metodo,
                substr_count($codigo, "encryptDecrypt('decrypt', \$params['id'])") >= 4,
                $metodo.'() no descifra $params[\'id\']');
        }
        $this->addCase($results, 'id_valido', substr_count($codigo, 'isValidDecrypted(') >= 5,
            'los metodos de vista no validan el ID con isValidDecrypted()');
        $this->addCase($results, 'id_invalido_400', substr_count($codigo, "Response::error('Registro inválido', 400)") >= 5,
            'los metodos de vista no responden 400 ante un ID invalido');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                       CONTROLADOR: RUTAS E INSTALADOR                      */
    /******************************************************************************/
    /**
     * Verifica que las rutas de `cotizacionListado` publicadas por el instalador
     * apunten a metodos reales con el verbo y el nivel de permiso esperados.
     * No toca la base de datos: solo se lee `getRouteDefinitions()`.
     *
     * @return array Resultado por caso
     */
    public function controladorRutasTest(){

        $results = [];
        $rutas   = $this->rutasInstalador();
        $this->addCase($results, 'instalador_rutas_disponibles', count($rutas) > 0,
            'getRouteDefinitions() no devolvio rutas');

        /*====================================*/
        // idMetodo: 1=GET, 2=POST, 3=DELETE, 4=PUT
        // [clave, handler esperado, nivel esperado]
        /*====================================*/
        $esperadas = [
            'listado'        => ['1 '.self::RUTA_WEB.'/listAll',         'cotizacionListado->listAll',      1],
            'busqueda'       => ['2 '.self::RUTA_WEB.'/search',          'cotizacionListado->UpdateList',   1],
            'refrescar'      => ['1 '.self::RUTA_WEB.'/updateList',      'cotizacionListado->UpdateList',   2],
            'ver'            => ['1 '.self::RUTA_WEB.'/view/@id',        'cotizacionListado->View',         1],
            'imprimir'       => ['1 '.self::RUTA_WEB.'/print/@id',       'cotizacionListado->Print',        1],
            'visualizar'     => ['1 '.self::RUTA_WEB.'/noPrint/@id',     'cotizacionListado->noPrint',      1],
            'resumen'        => ['1 '.self::RUTA_WEB.'/resumen/@id',     'cotizacionListado->Resumen',      2],
            'resumen_update' => ['1 '.self::RUTA_WEB.'/resumenUpdate/@id', 'cotizacionListado->ResumenUpdate', 2],
            'crear'          => ['2 '.self::RUTA_WEB,                    'cotizacionListado->Insert',       3],
            'editar'         => ['2 '.self::RUTA_WEB.'/update',          'cotizacionListado->Update',       2],
            'del_files'      => ['4 '.self::RUTA_WEB.'/delFiles',        'cotizacionListado->DelFiles',     2],
            'borrar'         => ['3 '.self::RUTA_WEB,                    'cotizacionListado->Delete',       4],
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
                'la ruta '.$clave.' queda asignada a '.$rutas[$clave][2]);
        }

        /*====================================*/
        // Todo handler del controlador debe existir (verifica metodos huerfanos)
        /*====================================*/
        foreach ($rutas as $clave => $datos) {
            if ($datos[2] !== self::CONTROLADOR) {
                continue;
            }
            $partes = explode('->', $datos[0]);
            $this->addCase($results, 'handler_existe_'.md5($clave),
                count($partes) === 2 && method_exists($partes[0], $partes[1]),
                'la ruta '.$clave.' apunta a un metodo inexistente ('.$datos[0].')');
        }

        /*====================================*/
        // Matriz de verbos: escritura nunca por GET, borrado solo por DELETE
        /*====================================*/
        foreach ($rutas as $clave => $datos) {
            if ($datos[2] !== self::CONTROLADOR) {
                continue;
            }
            $verbo = (int)substr($clave, 0, 1);
            $handler = $datos[0];
            if (str_ends_with($handler, '->Insert') || str_ends_with($handler, '->Update')) {
                $this->addCase($results, 'verbo_escritura_'.md5($clave), $verbo === 2,
                    $handler.' debe publicarse con POST');
            }
            if (str_ends_with($handler, '->Delete')) {
                $this->addCase($results, 'verbo_borrado_'.md5($clave), $verbo === 3,
                    $handler.' debe publicarse con DELETE');
            }
        }

        /*====================================*/
        // Instalador: el controlador se desinstala con el modulo
        /*====================================*/
        $instalador = $this->fuenteInstalador();
        $this->addCase($results, 'instalador_ruta_controlador', str_contains($instalador, "'RutaController' => 'cotizacionListado'"),
            'el permiso del modulo no declara el controlador cotizacionListado');
        $this->addCase($results, 'instalador_desinstala_controlador', str_contains($instalador, "\$RutaController  = '\"cotizacionListado\"'"),
            'RutaController() del instalador no lista cotizacionListado y dejaria rutas huerfanas');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                PERMISOS                                    */
    /******************************************************************************/
    /**
     * Verifica la sesion del usuario, el nivel de acceso del controlador y el
     * registro de la ruta en `SESSION.arrPermisos` y en el router de F3.
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function permisosSesionTest($f3){

        $results = [];

        /*====================================*/
        // Datos del usuario en sesion
        /*====================================*/
        $UserData = $f3->get('SESSION.DataInfo');
        $this->addCase($results, 'sesion_usuario_presente', is_array($UserData) && !empty($UserData),
            'no hay datos de usuario en la sesion');

        /*====================================*/
        // Lectura de la sesion desde el punto de vista del controlador
        // (getUserData/getArrLevel son protected en ControllerBase y se
        // prueban sobre la propia instancia de testing, sin tocar la BD)
        /*====================================*/
        try {
            $arrUserData = $this->getUserData($f3);
            $this->addCase($results, 'get_user_data_arreglo', is_array($arrUserData),
                'getUserData() no devolvio un arreglo');

            $arrLevel = $this->getArrLevel($f3, self::CONTROLADOR);
            $this->addCase($results, 'get_arr_level_arreglo', is_array($arrLevel),
                'getArrLevel() no devolvio un arreglo');
        } catch (Throwable $e) {
            $this->addCase($results, 'get_arr_level_arreglo', false,
                'no se pudo leer la sesion del controlador ('.$e->getMessage().')');
            $arrLevel = [];
        }

        /*====================================*/
        // El nivel otorgado debe estar en el catalogo 1..4
        /*====================================*/
        $nivel = is_array($arrLevel) ? ($arrLevel['LevelAccess'] ?? null) : null;
        $this->addCase($results, 'nivel_en_rango', $nivel === null || in_array((int)$nivel, self::NIVELES, true),
            'LevelAccess quedo fuera del catalogo 1..4');
        $this->addCase($results, 'ruta_de_acceso_coincide', $nivel === null || ($arrLevel['RouteAccess'] ?? '') === self::RUTA_WEB,
            'RouteAccess no coincide con '.self::RUTA_WEB);

        /*====================================*/
        // Registro dinamico de la ruta desde SESSION.arrPermisos
        // (el usuario de pruebas puede no tener el modulo: solo se evalua si hay permisos)
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
            // Si el usuario no tiene la ruta, el nivel de acceso debe ser coherente (null)
            $this->addCase($results, 'ruta_registrada', $rutaOk || $nivel === null,
                'el usuario tiene nivel '.var_export($nivel, true).' pero la ruta '.self::RUTA_WEB_LISTADO.' no esta en sus permisos');
        }

        /*====================================*/
        // La ruta debe existir en el router de F3 con el verbo esperado
        // (solo se evalua si el usuario de pruebas tiene la ruta registrada:
        // el router de F3 se arma con SESSION.arrPermisos)
        /*====================================*/
        $arrRoutes = $f3->get('ROUTES');
        $this->addCase($results, 'router_disponible', is_array($arrRoutes),
            'F3 no expuso la variable ROUTES');

        $esquema = [
            'ruta_en_router'        => ['/'.self::RUTA_WEB_LISTADO, 'GET',  self::CONTROLADOR.'->listAll'],
            'ruta_busqueda_router'  => ['/'.self::RUTA_WEB_SEARCH,  'POST', self::CONTROLADOR.'->UpdateList'],
            'ruta_insert_router'    => ['/'.self::RUTA_WEB,         'POST', self::CONTROLADOR.'->Insert'],
            'ruta_delete_router'    => ['/'.self::RUTA_WEB,         'DELETE', self::CONTROLADOR.'->Delete'],
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
     * Renderiza `cotizacionListado-List.php` y `cotizacionListado-UpdateList.php` con datos
     * sinteticos y verifica la matriz de permisos: el boton "Crear Nuevo" solo aparece
     * con nivel >= 3, ver con >= 1, editar con >= 2 y borrar con >= 4.
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
        // Estructura de la pagina de listado (nivel 1)
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php'),
            $this->datosSinteticos(1), $BASE, $f3);

        $this->addCase($results, 'listado_render', $html !== '', 'la vista List no produjo HTML');
        $this->addCase($results, 'listado_contenedor',
            str_contains($html, 'id="listTableData"'), 'falta el contenedor de la tabla');
        $this->addCase($results, 'listado_boton_filtrar', str_contains($html, 'data-bs-target="#formSearch"'),
            'falta el boton que despliega el filtro');
        $this->addCase($results, 'listado_quitar_filtro', str_contains($html, 'onclick="deleteFilter()"'),
            'falta el boton para quitar el filtro');
        $this->addCase($results, 'listado_sin_crear_nivel1', !str_contains($html, 'Crear Nuevo</button>'),
            'con nivel 1 el listado ofrece crear registros');
        $this->addCase($results, 'listado_endpoint_ver', str_contains($html, '/view/'),
            'falta el endpoint de detalle');
        $this->addCase($results, 'listado_endpoint_actualizar', str_contains($html, '/updateList'),
            'falta el endpoint de refresco de tabla');
        $this->addCase($results, 'listado_borrado_verbo', str_contains($html, "let Metodo      = 'DELETE'"),
            'la accion de borrar no viaja con el verbo DELETE');
        $this->addCase($results, 'listado_borrado_id', str_contains($html, '{"idCotizacion": ID}'),
            'la accion de borrar no envia idCotizacion');
        $this->addCase($results, 'listado_ruta_modulo', str_contains($html, $BASE.'/'.self::RUTA_WEB),
            'las acciones no apuntan a la ruta '.self::RUTA_WEB);

        /*====================================*/
        // El boton de alta aparece con nivel >= 3
        /*====================================*/
        $htmlNivel3 = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php'),
            $this->datosSinteticos(3), $BASE, $f3);
        $this->addCase($results, 'listado_crear_nivel3', str_contains($htmlNivel3, 'Crear Nuevo</button>'),
            'con nivel 3 no se muestra el boton Crear Nuevo');
        $this->addCase($results, 'listado_modal_nivel3', str_contains($htmlNivel3, 'data-bs-target="#newFormModal"'),
            'el boton de alta no apunta al modal #newFormModal');

        /*====================================*/
        // Matriz de acciones sobre la tabla (UpdateList)
        /*====================================*/
        $rutaTabla = $this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php');
        foreach (self::NIVELES as $nivel) {
            $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos($nivel), $BASE, $f3);

            $this->addCase($results, 'tabla_render_nivel'.$nivel, $tabla !== '',
                'la vista UpdateList no produjo HTML con nivel '.$nivel);
            $this->addCase($results, 'tabla_ver_nivel'.$nivel,
                str_contains($tabla, 'listTableDataView(') === ($nivel >= 1),
                'la accion Ver no coincide con el nivel '.$nivel);
            $this->addCase($results, 'tabla_editar_nivel'.$nivel,
                str_contains($tabla, '/resumen/') === ($nivel >= 2),
                'la accion Editar no coincide con el nivel '.$nivel);
            $this->addCase($results, 'tabla_borrar_nivel'.$nivel,
                str_contains($tabla, 'listTableDataDel(') === ($nivel >= 4),
                'la accion Borrar no coincide con el nivel '.$nivel);
            $this->addCase($results, 'tabla_datos_nivel'.$nivel,
                str_contains($tabla, 'Cotización N° 77') && str_contains($tabla, 'Entidad Prueba SpA'),
                'la tabla no pinta el registro sintetico con nivel '.$nivel);
            $this->addCase($results, 'tabla_id_cifrado_nivel'.$nivel,
                !str_contains($tabla, "listTableDataView('77')") && !str_contains($tabla, '/resumen/77'),
                'la tabla expone el ID sin cifrar con nivel '.$nivel);
        }

        /*====================================*/
        // Tabla vacia: sin filas ni avisos de PHP
        /*====================================*/
        $vacio = $this->datosSinteticos(4);
        $vacio['arrList'] = [];
        $tablaVacia = $this->renderizarVista($rutaTabla, $vacio, $BASE, $f3);
        $this->addCase($results, 'tabla_vacia_render', $tablaVacia !== '' && str_contains($tablaVacia, '<tbody>'),
            'la tabla sin datos no se dibuja');
        $this->addCase($results, 'tabla_vacia_sin_filas', !str_contains($tablaVacia, 'Cotización N°'),
            'la tabla sin datos pinta filas');

        /*====================================*/
        // Entidad persona natural: "Apellido, Nombre (Nick)"
        /*====================================*/
        $persona = $this->datosSinteticos(1);
        $persona['arrList'][0]['idTipoEntidad']   = 1;
        $persona['arrList'][0]['EntidadesNombre']   = 'Ana';
        $persona['arrList'][0]['EntidadesApellido'] = 'Prueba';
        $persona['arrList'][0]['EntidadesNick']     = 'anita';
        $tablaPersona = $this->renderizarVista($rutaTabla, $persona, $BASE, $f3);
        $this->addCase($results, 'tabla_persona_natural', str_contains($tablaPersona, 'Prueba, Ana (anita)'),
            'la tabla no compone el nombre de una persona natural');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                        VISTAS DE DETALLE Y FORMULARIOS                     */
    /******************************************************************************/
    /**
     * Renderiza el detalle, el resumen, el documento imprimible y los formularios del
     * controlador con datos sinteticos y comprueba que conservan el contrato de ids,
     * endpoints y componentes que consume el JavaScript del modulo.
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

        /*====================================*/
        // Modal de detalle (View)
        /*====================================*/
        $detalle = $this->datosSinteticos(2);
        $detalle['arrProductos'] = [['ProductoNombre' => 'Producto Prueba', 'ProductoCantidad' => 1, 'ProductoValor' => 50000, 'UnidadMedida' => 'un']];
        $detalle['arrServicios'] = [['ServicioNombre' => 'Servicio Prueba', 'ServicioCantidad' => 1, 'ServicioValor' => 49000]];
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'),
            $detalle, $BASE, $f3);
        $this->addCase($results, 'view_render', $html !== '', 'la vista View no produjo HTML');
        $this->addCase($results, 'view_pestana_basica', str_contains($html, 'id="view_tab_1"'),
            'la vista View no monta la pestana de datos basicos');
        $this->addCase($results, 'view_pestanas_detalle',
            str_contains($html, 'id="view_tab_2"') && str_contains($html, 'id="view_tab_3"') && str_contains($html, 'id="view_tab_4"'),
            'la vista View no monta las pestanas de items, productos y servicios con datos');
        $this->addCase($results, 'view_detalle_pintado',
            str_contains($html, 'Item de prueba') && str_contains($html, 'Producto Prueba') && str_contains($html, 'Servicio Prueba'),
            'la vista View no pinta el detalle sintetico');

        /*====================================*/
        // Sin detalle no se montan las pestanas de items, productos ni servicios
        /*====================================*/
        $sinDetalle = $this->datosSinteticos(2);
        $sinDetalle['arrItems'] = [];
        $sinDetalle['arrProductos'] = [];
        $sinDetalle['arrServicios'] = [];
        $htmlVacio = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'),
            $sinDetalle, $BASE, $f3);
        $this->addCase($results, 'view_sin_detalle',
            $htmlVacio !== '' && !str_contains($htmlVacio, 'id="view_tab_2"') && !str_contains($htmlVacio, 'id="view_tab_3"') && !str_contains($htmlVacio, 'id="view_tab_4"'),
            'la vista View monta pestanas de detalle cuando no hay datos');
        $this->addCase($results, 'view_enlace_documento', str_contains($html, self::RUTA_WEB.'/noPrint/'),
            'la vista View no enlaza al documento');
        $this->addCase($results, 'view_enlace_imprimir', str_contains($html, self::RUTA_WEB.'/print/'),
            'la vista View no enlaza a la impresion');
        $this->addCase($results, 'view_pie_cierre', str_contains($html, 'data-bs-dismiss="modal"'),
            'la vista View no ofrece el pie de cierre del modal');

        /*====================================*/
        // Formulario de alta
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-formNew.php'),
            $this->datosSinteticos(3), $BASE, $f3);
        $this->addCase($results, 'form_new_render', $html !== '', 'la vista formNew no produjo HTML');
        $this->addCase($results, 'form_new_id', str_contains($html, 'id="FormNewData"'),
            'el formulario de alta no usa el id FormNewData');
        $this->addCase($results, 'form_new_modal', str_contains($html, 'id="newFormModal"'),
            'el formulario de alta no se monta en #newFormModal');
        $this->addCase($results, 'form_new_campos',
            str_contains($html, 'name="idEntidad"') && str_contains($html, 'name="Creacion_fecha"') && str_contains($html, 'name="Observaciones"'),
            'el formulario de alta no declara idEntidad/Creacion_fecha/Observaciones');
        $this->addCase($results, 'form_new_ocultos',
            str_contains($html, 'name="fecha_auto"') && str_contains($html, 'name="Creacion_hora"'),
            'el formulario de alta no envia fecha_auto y Creacion_hora');
        $this->addCase($results, 'form_new_detalle',
            str_contains($html, 'name="Item_Item[]"')
            && str_contains($html, 'name="Producto_idProducto[]"')
            && str_contains($html, 'name="Servicio_idServicio[]"'),
            'el formulario de alta no declara las plantillas de items, productos y servicios');
        $this->addCase($results, 'form_new_endpoint',
            str_contains($html, $BASE.'/'.self::RUTA_WEB) && str_contains($html, "let Metodo      = 'POST'"),
            'el formulario de alta no publica en '.self::RUTA_WEB.' con POST');
        $this->addCase($results, 'form_new_redirige_resumen', str_contains($html, self::RUTA_WEB.'/resumen/'),
            'el formulario de alta no redirige al resumen del nuevo documento');

        /*====================================*/
        // Formulario de busqueda
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-formSearch.php'),
            $this->datosSinteticos(1), $BASE, $f3);
        $this->addCase($results, 'form_search_render', $html !== '', 'la vista formSearch no produjo HTML');
        $this->addCase($results, 'form_search_id', str_contains($html, 'id="FormSearchData"'),
            'el formulario de busqueda no usa el id FormSearchData');
        $this->addCase($results, 'form_search_inputs',
            str_contains($html, 'id="Search_idCotizacion"')
            && str_contains($html, 'id="Search_idEntidad"')
            && str_contains($html, 'id="Search_F_Inicio"')
            && str_contains($html, 'id="Search_F_Termino"'),
            'el formulario de busqueda no declara sus campos prefijados con Search_');
        $this->addCase($results, 'form_search_endpoint',
            str_contains($html, self::RUTA_WEB.'/search') && str_contains($html, "let Metodo      = 'POST'"),
            'el formulario de busqueda no apunta a /search con POST');

        /*====================================*/
        // Resumen (edicion + pestanas de detalle)
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen.php'),
            $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'resumen_render', $html !== '', 'la vista Resumen no produjo HTML');
        $this->addCase($results, 'resumen_form_edicion', str_contains($html, 'id="FormEditData"'),
            'el resumen no monta el formulario de edicion');
        $this->addCase($results, 'resumen_id_oculto', str_contains($html, 'name="idCotizacion"'),
            'el formulario de edicion no envia idCotizacion');
        $this->addCase($results, 'resumen_pestanas',
            str_contains($html, 'id="resumen-items"') && str_contains($html, 'id="resumen-productos"') && str_contains($html, 'id="resumen-servicios"'),
            'el resumen no monta las pestanas de items, productos y servicios');
        $this->addCase($results, 'resumen_contenedores',
            str_contains($html, 'id="tabItemDataTable"') && str_contains($html, 'id="tabProdDataTable"') && str_contains($html, 'id="tabServDataTable"'),
            'el resumen no monta los contenedores de las tablas de detalle');
        $this->addCase($results, 'resumen_endpoint_update',
            str_contains($html, self::RUTA_WEB.'/update') && str_contains($html, '/resumenUpdate/'),
            'el resumen no apunta a /update y /resumenUpdate');
        $this->addCase($results, 'resumen_id_cifrado', !str_contains($html, "tabItemNew('77')"),
            'el resumen entrega el ID sin cifrar a las acciones de detalle');
        $this->addCase($results, 'resumen_volver', str_contains($html, self::RUTA_WEB.'/listAll'),
            'el resumen no ofrece el enlace para volver al listado');

        /*====================================*/
        // Documento imprimible (Print) con datos del emisor
        /*====================================*/
        $imprimir = $this->datosSinteticos(1);
        $imprimir['rowSistema'] = [
            'Sistema_Nombre' => 'Emisor Prueba', 'Sistema_Direccion' => 'Calle 1', 'Sistema_Email' => 'prueba@example.test',
            'Sistema_Fono1' => '', 'Sistema_Fono2' => '', 'Sistema_Ciudad' => 'Santiago', 'Sistema_Comuna' => 'Providencia',
        ];
        $imprimir['rowData']['EntidadesDireccion'] = 'Calle 2';
        $imprimir['rowData']['EntidadesComuna']    = 'Providencia';
        $imprimir['rowData']['EntidadesCiudad']    = 'Santiago';
        $imprimir['Imprimir'] = 0;
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-Print.php'),
            $imprimir, $BASE, $f3);
        $this->addCase($results, 'print_render', $html !== '', 'la vista Print no produjo HTML');
        $this->addCase($results, 'print_emisor', str_contains($html, 'Emisor Prueba'),
            'el documento no muestra el emisor');
        $this->addCase($results, 'print_cliente', str_contains($html, 'Entidad Prueba SpA'),
            'el documento no muestra el cliente');
        $this->addCase($results, 'print_numero', str_contains($html, '77'),
            'el documento no muestra el numero de cotizacion');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos los chequeos de datos del controlador (`dataCheck_1/2/3`),
     * el cifrado de identificadores, la validacion de IDs descifrados y las ramas de
     * `updateCotizacion()` que no tocan la base.
     *
     * @return array Resultado por caso
     */
    public function logicaValidacionTest(){

        $results = [];

        /*====================================*/
        // dataCheck_1/2/3 devuelven el contrato esperado por Base_insert/Base_update
        // (se crea sin constructor: son logica pura y no necesitan conexion)
        /*====================================*/
        $Controller = null;
        try {
            $Controller = (new ReflectionClass(self::CONTROLADOR))->newInstanceWithoutConstructor();
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_instancia', false,
                'no se pudo instanciar el controlador sin constructor ('.$e->getMessage().')');
        }

        if ($Controller !== null) {
            $Post = ['idUsuario' => 1, 'idEntidad' => 1, 'Creacion_fecha' => '2026-01-02', 'ValorTotal' => 119000];
            $largos = ['dataCheck_1' => [], 'dataCheck_2' => ['Item' => 255], 'dataCheck_3' => []];
            foreach (['dataCheck_1', 'dataCheck_2', 'dataCheck_3'] as $metodo) {
                try {
                    $reflector = new ReflectionMethod(self::CONTROLADOR, $metodo);
                    $reflector->setAccessible(true);
                    $DataCheck = $reflector->invoke($Controller, $Post);

                    $this->addCase($results, $metodo.'_es_arreglo', is_array($DataCheck),
                        $metodo.'() no devolvio un arreglo');
                    $this->addCase($results, $metodo.'_post', ($DataCheck['Post'] ?? null) === $Post,
                        $metodo.'() no conserva el POST recibido');
                    $this->addCase($results, $metodo.'_claves_contrato',
                        isset($DataCheck['emptyData'], $DataCheck['ValidarNumero'], $DataCheck['ValidarEntero'], $DataCheck['ValidarLargoMinimoN'], $DataCheck['ValidarLargoMaximoPorCampo']),
                        $metodo.'() no entrega las claves del contrato de CheckData');
                    $this->addCase($results, $metodo.'_largos',
                        (int)($DataCheck['ValidarLargoMinimoN'] ?? 0) === 3
                        && ($DataCheck['ValidarLargoMaximoPorCampo'] ?? null) === ($largos[$metodo] ?? null),
                        $metodo.'() no fija los limites de largo esperados');
                } catch (Throwable $e) {
                    $this->addCase($results, $metodo.'_es_arreglo', false,
                        'no se pudo ejecutar '.$metodo.'() ('.$e->getMessage().')');
                }
            }

            /*====================================*/
            // Contenido especifico de cada chequeo
            /*====================================*/
            try {
                $r1 = new ReflectionMethod(self::CONTROLADOR, 'dataCheck_1'); $r1->setAccessible(true);
                $c1 = $r1->invoke($Controller, []);
                $this->addCase($results, 'datacheck_1_identificadores',
                    str_contains((string)$c1['ValidarEntero'], 'idUsuario') && str_contains((string)$c1['ValidarEntero'], 'idEntidad'),
                    'dataCheck_1() no valida idUsuario e idEntidad como enteros');
                $this->addCase($results, 'datacheck_1_fechas',
                    str_contains((string)$c1['ValidarFecha'], 'Creacion_fecha') && str_contains((string)$c1['ValidarFecha'], 'fecha_auto'),
                    'dataCheck_1() no valida las fechas de la cabecera');
                $this->addCase($results, 'datacheck_1_montos',
                    str_contains((string)$c1['ValidarNumero'], 'ValorNeto') && str_contains((string)$c1['ValidarNumero'], 'IVA') && str_contains((string)$c1['ValidarNumero'], 'ValorTotal'),
                    'dataCheck_1() no valida los montos como numeros');
                $this->addCase($results, 'datacheck_1_observaciones',
                    str_contains((string)$c1['ValidarLargoMinimo'], 'Observaciones') && str_contains((string)$c1['ValidarPalabrasCensuradas'], 'Observaciones'),
                    'dataCheck_1() no controla el largo ni las palabras censuradas de Observaciones');

                $r2 = new ReflectionMethod(self::CONTROLADOR, 'dataCheck_2'); $r2->setAccessible(true);
                $c2 = $r2->invoke($Controller, []);
                $this->addCase($results, 'datacheck_2_detalle',
                    str_contains((string)$c2['ValidarEntero'], 'idCotizacion') && str_contains((string)$c2['ValidarEntero'], 'idProducto') && str_contains((string)$c2['ValidarEntero'], 'idServicio'),
                    'dataCheck_2() no valida los identificadores del detalle como enteros');
                $this->addCase($results, 'datacheck_2_item',
                    str_contains((string)$c2['ValidarLargoMinimo'], 'Item') && (int)($c2['ValidarLargoMaximoPorCampo']['Item'] ?? 0) === 255,
                    'dataCheck_2() no controla el largo del texto del item');

                $r3 = new ReflectionMethod(self::CONTROLADOR, 'dataCheck_3'); $r3->setAccessible(true);
                $c3 = $r3->invoke($Controller, []);
                $this->addCase($results, 'datacheck_3_totales',
                    str_contains((string)$c3['ValidarNumero'], 'TotalItems') && str_contains((string)$c3['ValidarNumero'], 'TotalProductos') && str_contains((string)$c3['ValidarNumero'], 'TotalServicios'),
                    'dataCheck_3() no valida los totales por tipo de detalle');
            } catch (Throwable $e) {
                $this->addCase($results, 'datacheck_contenido', false,
                    'no se pudo revisar el contenido de los dataCheck ('.$e->getMessage().')');
            }

            /*====================================*/
            // Los dataCheck son privados: no deben exponerse como API publica
            /*====================================*/
            foreach (['dataCheck_1', 'dataCheck_2', 'dataCheck_3'] as $metodo) {
                $this->addCase($results, $metodo.'_privado', (new ReflectionMethod(self::CONTROLADOR, $metodo))->isPrivate(),
                    $metodo.'() debe ser privado');
            }
        }

        /*====================================*/
        // updateCotizacion: firma publica y retorno tipado (no se ejecuta: consulta la base)
        /*====================================*/
        $existeUpdate = method_exists(self::CONTROLADOR, 'updateCotizacion');
        $this->addCase($results, 'updatecotizacion_existe', $existeUpdate,
            'no existe '.self::CONTROLADOR.'::updateCotizacion()');
        if ($existeUpdate) {
            $reflector = new ReflectionMethod(self::CONTROLADOR, 'updateCotizacion');
            $this->addCase($results, 'updatecotizacion_publico', $reflector->isPublic(),
                'updateCotizacion() no es publico (lo consumen los sub-recursos)');
            $this->addCase($results, 'updatecotizacion_parametros', $reflector->getNumberOfParameters() === 3,
                'updateCotizacion() debe declarar Tipo, CotizacionID y NewDBConn');
            $this->addCase($results, 'updatecotizacion_retorno_array',
                $reflector->hasReturnType() && (string)$reflector->getReturnType() === 'array',
                'updateCotizacion() debe declarar retorno array');
            $this->addCase($results, 'updatecotizacion_conexion_opcional',
                $reflector->getParameters()[2]->isDefaultValueAvailable() && $reflector->getParameters()[2]->getDefaultValue() === null,
                'el parametro NewDBConn debe ser opcional (null)');
        }

        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'updatecotizacion_tipos',
            str_contains($codigo, 'case 1:') && str_contains($codigo, 'case 2:') && str_contains($codigo, 'case 3:'),
            'updateCotizacion() no distingue los tipos 1 (items), 2 (productos) y 3 (servicios)');
        $this->addCase($results, 'updatecotizacion_transaccion_condicional',
            str_contains($codigo, 'if($NewDBConn === null){'),
            'updateCotizacion() debe abrir/cerrar transaccion solo cuando no recibe una conexion externa');
        $this->addCase($results, 'updatecotizacion_codigos',
            str_contains($codigo, "'code' => 500") && str_contains($codigo, "'code' => 200, 'data' => 'OK'"),
            'updateCotizacion() no retorna los codigos 500/200');
        $this->addCase($results, 'updatecotizacion_recalcula_iva',
            str_contains($codigo, "'ValorNeto'       => (\$x_ValorTotal/1.19)"),
            'updateCotizacion() no recalcula el neto con IVA 19%');

        /*====================================*/
        // Cifrado de identificadores: ida y vuelta estable
        /*====================================*/
        try {
            $Codification = new FunctionsSecurityCodification();
            $cifrado      = $Codification->encryptDecrypt('encrypt', '42');
            $this->addCase($results, 'cifrado_genera_dato',
                ($cifrado['success'] ?? false) === true && !empty($cifrado['data']),
                'encryptDecrypt(encrypt) no produjo un valor cifrado');

            $descifrado = $Codification->encryptDecrypt('decrypt', (string)($cifrado['data'] ?? ''));
            $this->addCase($results, 'cifrado_ida_y_vuelta',
                ($descifrado['success'] ?? false) === true && (string)($descifrado['data'] ?? '') === '42',
                'el cifrado no devuelve el valor original');
        } catch (Throwable $e) {
            $this->addCase($results, 'cifrado_ida_y_vuelta', false,
                'no se pudo ejecutar el cifrado ('.$e->getMessage().')');
        }

        /*====================================*/
        // isValidDecrypted: los caminos posibles
        /*====================================*/
        $this->addCase($results, 'id_valido_ok', $this->isValidDecrypted(['success' => true, 'data' => '7'], 'id') === true,
            'un ID numerico positivo debe ser valido');
        $this->addCase($results, 'id_valido_descifro_falla', $this->isValidDecrypted(['success' => false, 'data' => '7'], 'id') === false,
            'un descifrado fallido debe rechazarse');
        $this->addCase($results, 'id_valido_texto', $this->isValidDecrypted(['success' => true, 'data' => 'abc'], 'id') === false,
            'un ID no numerico debe rechazarse para tipo id');
        $this->addCase($results, 'id_valido_cero', $this->isValidDecrypted(['success' => true, 'data' => '0'], 'id') === false,
            'el ID 0 debe rechazarse para tipo id');
        $this->addCase($results, 'id_valido_tipo_texto', $this->isValidDecrypted(['success' => true, 'data' => 'abc'], 'text') === true,
            'un texto no vacio debe ser valido para tipo text');
        $this->addCase($results, 'id_valido_tipo_desconocido', $this->isValidDecrypted(['success' => true, 'data' => 'abc'], 'otro') === false,
            'un tipo de dato desconocido debe rechazarse');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                 SEGURIDAD                                  */
    /******************************************************************************/
    /**
     * Revisa estaticamente las vistas y el controlador: ausencia de rutas relativas
     * maliciosas, de rutas absolutas del sistema de ficheros, de acceso directo a la
     * sesion desde las plantillas y escape de los datos que pintan el listado.
     *
     * @return array Resultado por caso
     */
    public function seguridadVistasTest(){

        $results = [];

        /*====================================*/
        // Vistas involucradas (solo las del controlador: el glob no incluye sub-recursos)
        /*====================================*/
        $arrVistas = glob($this->rutaModulo('../views/'.self::CONTROLADOR.'-*.php')) ?: [];
        $this->addCase($results, 'hay_vistas', count($arrVistas) > 0,
            'no se encontraron vistas del controlador '.self::CONTROLADOR);

        foreach ($arrVistas as $ruta) {
            $nombre  = basename($ruta);
            $codigo  = $this->codigoFuente($ruta);

            $this->addCase($results, 'vista_sin_retroceso_'.$nombre, !preg_match('#(?:\.\./|\.\.\\\\)#', $codigo),
                $nombre.' construye rutas con retroceso');
            $this->addCase($results, 'vista_sin_ruta_absoluta_'.$nombre,
                !preg_match('#(?:/var/www|/home/|[A-Za-z]:\\\\)#', $codigo),
                $nombre.' fija una ruta absoluta del servidor');
            $this->addCase($results, 'vista_sin_sesion_'.$nombre, !str_contains($codigo, '$_SESSION'),
                $nombre.' accede a la sesion de forma directa');
            $this->addCase($results, 'vista_sin_sql_'.$nombre, !preg_match('/\b(SELECT|INSERT|UPDATE|DELETE)\s+\w+\s+FROM\b/i', $codigo),
                $nombre.' contiene una consulta SQL embebida');
        }

        /*====================================*/
        // Escape y cifrado de los datos pintados en el listado
        /*====================================*/
        $tabla = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'));
        $this->addCase($results, 'tabla_escape_documento', str_contains($tabla, 'addslashes('),
            'UpdateList.php no escapa la etiqueta del documento que viaja al JavaScript');
        $this->addCase($results, 'tabla_fecha_estandar', str_contains($tabla, 'fechaEstandar('),
            'UpdateList.php no formatea la fecha con fechaEstandar()');
        $this->addCase($results, 'tabla_id_cifrado', str_contains($tabla, "encryptDecrypt('encrypt'"),
            'UpdateList.php no cifra el ID de las acciones');

        /*====================================*/
        // El controlador no concatena SQL desde el cliente
        /*====================================*/
        $controlador = $this->fuenteControlador();
        $this->addCase($results, 'controlador_sin_concat_sql',
            !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $controlador),
            'el controlador concatena superglobales en una cadena SQL');
        $this->addCase($results, 'controlador_parametros_preparados',
            str_contains($controlador, "'params'"),
            'el controlador no usa parametros preparados');
        $this->addCase($results, 'controlador_consultas_con_params',
            substr_count($controlador, "'params'") >= 10,
            'las consultas de lectura del controlador no usan parametros preparados');

        /*====================================*/
        // updateCotizacion() interpola el ID en subselects: debe forzarlo a entero
        // antes de armar la cadena (los sub-recursos lo reciben de $_POST)
        /*====================================*/
        $posCast   = strpos($controlador, '$CotizacionID = (int)$CotizacionID;');
        $posSelect = strpos($controlador, "\$Data .= ',");
        $this->addCase($results, 'updatecotizacion_id_entero',
            $posCast !== false && $posSelect !== false && $posCast < $posSelect,
            'updateCotizacion() no fuerza el ID a entero antes de interpolarlo en el SQL');
        $this->addCase($results, 'updatecotizacion_sin_id_crudo_en_where',
            !preg_match('/WHERE idCotizacion=\'\.\$_(POST|GET|REQUEST)/', $controlador),
            'updateCotizacion() interpola una superglobal en el SQL');

        /************************************/
        // Retorno los datos
        return $results;

    }

}
