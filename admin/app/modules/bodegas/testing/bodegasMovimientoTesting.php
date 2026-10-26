<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class bodegasMovimientoTesting
 *
 * Pruebas del modulo `bodegas` - controlador `bodegasMovimiento` (ingresos,
 * egresos y traspasos de mercaderia entre bodegas).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. El normalizador
 *    `sistemaTesteos::normalizeResult()` marca EXITOSA o ERROR segun el contenido, por lo que
 *    los textos de los casos correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Alcance: pruebas NO destructivas.
 *  - `Insert()`, `Update()`, `Delete()` y `delFiles()` terminan en `Response::*()` (que hace
 *    `exit`) y escriben en base de datos: solo se verifican por reflexion y lectura del fuente.
 *  - `createMov()` y `removeMov()` SI se ejecutan, pero unicamente por sus ramas de validacion
 *    previas, que retornan codigo 400 antes de abrir transaccion. Se les pasa un `$NewDBConn`
 *    no nulo para que jamas entren en `Base_transactionBegin()`.
 *
 * @see skills/testing/SKILL.md
 */
class bodegasMovimientoTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR        = 'bodegasMovimiento';
    private const RUTA_INGRESOS      = 'gestionBodegas/ingresos/listado';
    private const RUTA_EGRESOS       = 'gestionBodegas/egresos/listado';
    private const RUTA_TRASPASO      = 'gestionBodegas/traspaso/listado';
    // Claves de SESSION.arrLevel y nombres de transaccion por tipo de movimiento
    private const TRANSACCIONES      = [
        1 => ['clave' => 'bodegasMovimientoIngreso',  'ruta' => 'gestionBodegas/ingresos/listado',  'titulo' => 'Ingresos a'],
        2 => ['clave' => 'bodegasMovimientoEgreso',   'ruta' => 'gestionBodegas/egresos/listado',   'titulo' => 'Egresos a'],
        3 => ['clave' => 'bodegasMovimientoTraspaso', 'ruta' => 'gestionBodegas/traspaso/listado',  'titulo' => 'Traspasos de'],
    ];
    // Niveles de permiso del catalogo (1=ver, 2=editar, 3=crear, 4=borrar)
    private const NIVELES            = [1, 2, 3, 4];

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
    /*                                 EJECUCION                                  */
    /******************************************************************************/


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
    // Indica si la clase finfo esta disponible en el servidor
    /*******************************************************************/
    private function finfoDisponible(): bool{
        return class_exists('finfo');
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
    // Instancia del controlador SIN constructor (evita la conexion a BD
    // para las pruebas de logica pura)
    /*******************************************************************/
    private function instanciaSinBD(string $clase){
        return (new ReflectionClass($clase))->newInstanceWithoutConstructor();
    }

    /*******************************************************************/
    // Payload sintetico de movimiento (se completa segun el caso)
    /*******************************************************************/
    private function payloadMovimiento(): array{
        return [
            'idEstadoIngreso'  => 1,
            'idBodegasIngreso' => 1,
            'idBodegasEgreso'  => 0,
            'Creacion_fecha'   => '2026-01-02',
            'Creacion_hora'    => '10:00:00',
            'Observaciones'    => 'Observaciones de prueba',
            'fecha_auto'       => '2026-01-02',
            'idUsuario'        => 7,
            'idFacturacion'    => 0,
            'idProducto'       => [3],
            'Number'           => [5],
        ];
    }

    /*******************************************************************/
    // Datos sinteticos usados por las vistas del modulo
    /*******************************************************************/
    private function datosSinteticos(int $nivel, int $tipo = 1): array{
        return [
            'PageTitle'      => self::TRANSACCIONES[$tipo]['titulo'].' Bodegas',
            'PageDescription' => 'Pruebas',
            'PageAuthor'     => 'Pruebas',
            'PageKeywords'   => 'Pruebas',
            'TableTitle'     => self::TRANSACCIONES[$tipo]['titulo'].' Bodegas',
            'UserData'       => [
                'MainPathUrl'              => '/admin/public/upload/',
                'sistemaModalSubtitle'     => 1,
                'sistemaModalCloseBTN'     => 2,
                'usuariosPermisosBodegas'  => 1,
                'UserType'                 => 2,
                'gestionDocumentosUsoBodega' => 1,
            ],
            'UserAccess'     => [
                'LevelAccess'  => $nivel,
                'RouteAccess'  => self::TRANSACCIONES[$tipo]['ruta'],
            ],
            'Fnc_FormInputs'   => new UIFormInputs(),
            'Fnc_Codification' => new FunctionsSecurityCodification(),
            'Fnc_DataDate'     => new FunctionsDataDate(),
            'Fnc_DataNumbers'  => new FunctionsDataNumbers(),
            'Fnc_WidgetsCommon' => new UIWidgetsCommon(),
            'Fnc_WidgetsViews'  => new UIWidgetsViews(),
            'Fnc_ServerServer'  => new FunctionsServerServer(),
            'idTipoIngreso'   => $tipo,
            'arrList'         => [[
                'idMovimiento'    => 11,
                'Creacion_fecha'  => '2026-01-02',
                'Creacion_hora'   => '10:00:00',
                'Observaciones'   => 'Observaciones de prueba',
                'BodegaIngreso'   => 'Bodega Central',
                'BodegaEgreso'    => '',
            ]],
            'arrBodegas'      => [['ID' => 1, 'Nombre' => 'Bodega Central'], ['ID' => 2, 'Nombre' => 'Bodega Norte']],
            'arrProductos'    => [['ID' => 3, 'Nombre' => 'Producto Demo']],
            'arrDocumentos'   => [['ID' => 1, 'Nombre' => 'Factura']],
            'arrTipoMov'      => [['ID' => 1, 'Nombre' => 'Ingreso']],
            'arrProductosLista' => [],
            'rowData'         => [
                'idMovimiento'     => 11,
                'idEstadoIngreso'  => $tipo,
                'idBodegasIngreso' => 1,
                'idBodegasEgreso'  => 2,
                'idBodegas'        => 1,
                'idProducto'       => 3,
                'idExistencia'     => 4,
                'Number'           => 5,
                'Creacion_fecha'   => '2026-01-02',
                'Creacion_hora'    => '10:00:00',
                'fecha_auto'       => '2026-01-02',
                'Observaciones'    => 'Observaciones de prueba',
                'TipoMovimiento'   => 'Ingreso',
                'BodegaIngreso'    => 'Bodega Central',
                'BodegaEgreso'     => 'Bodega Norte',
                'Bodega'           => 'Bodega Central',
                'ProductoNombre'   => 'Producto Demo',
            ],
            'arrProductosDetalle' => [[
                'idExistencia'     => 4,
                'idMovimiento'     => 11,
                'TipoMovimiento'   => 'Ingreso',
                'Bodega'           => 'Bodega Central',
                'ProductoNombre'   => 'Producto Demo',
                'ProductoCantidad' => 5,
                'UnidadMedida'     => 'Un',
            ]],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica la estructura de carpetas y archivos del controlador de movimientos
     * y la convencion "clase = nombre de archivo" exigida por el AUTOLOAD de Fat-Free.
     *
     * @return array Resultado por caso
     */
    public function estructuraModuloTest(){

        $results = [];

        /*====================================*/
        // Archivos obligatorios
        /*====================================*/
        $arrArchivos = [
            'controlador'        => $this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'),
            'vista_list'         => $this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php'),
            'vista_update_list'  => $this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'),
            'vista_view'         => $this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'),
            'vista_resumen'      => $this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen.php'),
            'vista_form_new'     => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formNew.php'),
            'vista_form_search'  => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formSearch.php'),
            'vista_prod_list'    => $this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen-Productos-UpdateList.php'),
            'testing'            => $this->rutaModulo('bodegasMovimientoTesting.php'),
            'readme'             => $this->rutaModulo('../README.md'),
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
        $this->addCase($results, 'clase_testing_cargada', class_exists('bodegasMovimientoTesting'),
            'la clase bodegasMovimientoTesting no esta disponible');

        /*====================================*/
        // Herencias esperadas por el proyecto
        /*====================================*/
        $padreController = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padreController === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padreController.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('bodegasMovimientoTesting') === 'ControllerBase',
            'el testing no extiende ControllerBase');

        /*====================================*/
        // Soporte de deteccion de tipos de archivo
        /*====================================*/
        $this->addCase($results, 'entorno_finfo', $this->finfoDisponible(),
            'la extension finfo no esta disponible para validar tipos de archivo');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                          CONTROLADOR: RUTAS POR TIPO                       */
    /******************************************************************************/
    /**
     * Verifica que los wrappers `*_1`, `*_2` y `*_3` existan, sean publicos y
     * deleguen en el metodo generico con el tipo de movimiento correcto.
     *
     * @return array Resultado por caso
     */
    public function controladorRutasTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Wrappers por tipo de movimiento
        /*====================================*/
        $arrWrappers = [
            'listAll'        => 1,
            'UpdateList'     => 1,
            'View'           => 1,
            'Resumen'        => 1,
            'ResumenUpdate'  => 1,
        ];
        foreach ($arrWrappers as $base => $parametros) {
            foreach (self::TRANSACCIONES as $tipo => $transaccion) {
                $metodo = $base.'_'.$tipo;
                $existe = method_exists(self::CONTROLADOR, $metodo);
                $this->addCase($results, 'wrapper_'.$metodo, $existe,
                    'no existe '.self::CONTROLADOR.'::'.$metodo.'()');
                if (!$existe) {
                    continue;
                }
                $reflector = new ReflectionMethod(self::CONTROLADOR, $metodo);
                $this->addCase($results, 'wrapper_'.$metodo.'_publico', $reflector->isPublic(),
                    $metodo.'() no es publico');
                $this->addCase($results, 'wrapper_'.$metodo.'_delega',
                    str_contains($codigo, '$this->'.$base.'($f3, $params, '.$tipo.')')
                    || str_contains($codigo, '$this->'.$base.'($f3, '.$tipo.')'),
                    $metodo.'() no delega en '.$base.'() con el tipo '.$tipo);
            }
        }

        /*====================================*/
        // El metodo generico resuelve transaccion y titulo por tipo
        /*====================================*/
        foreach (self::TRANSACCIONES as $tipo => $transaccion) {
            $this->addCase($results, 'transaccion_tipo_'.$tipo,
                str_contains($codigo, "'".$transaccion['clave']."'"),
                'el controlador no declara la transaccion '.$transaccion['clave']);
            $this->addCase($results, 'titulo_tipo_'.$tipo,
                str_contains($codigo, "'".$transaccion['titulo']."'"),
                'el controlador no declara el titulo '.$transaccion['titulo']);
        }

        /*====================================*/
        // Metodo generico con firma correcta
        /*====================================*/
        $this->firmaMetodo($results, 'listAll', 2, 2);
        $this->firmaMetodo($results, 'UpdateList', 2, 2);
        $this->firmaMetodo($results, 'View', 3, 3);
        $this->firmaMetodo($results, 'Resumen', 3, 3);
        $this->firmaMetodo($results, 'ResumenUpdate', 3, 3);

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                            CONTROLADOR: VISTAS                             */
    /******************************************************************************/
    /**
     * Verifica el contrato de presentacion de los metodos que devuelven HTML.
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Contrato de presentacion
        /*====================================*/
        $this->addCase($results, 'vista_list', str_contains($codigo, "\$this->controllerName.'-List.php'"),
            'listAll() no instancia la vista List');
        $this->addCase($results, 'vista_update_list', str_contains($codigo, "\$this->controllerName.'-UpdateList.php'"),
            'UpdateList() no instancia la vista UpdateList');
        $this->addCase($results, 'vista_view', str_contains($codigo, "\$this->controllerName.'-View.php'"),
            'View() no instancia la vista View');
        $this->addCase($results, 'vista_resumen', str_contains($codigo, "\$this->controllerName.'-Resumen.php'"),
            'Resumen() no instancia la vista Resumen');
        $this->addCase($results, 'vista_resumen_update', str_contains($codigo, "\$this->controllerName.'-Resumen-Update.php'"),
            'ResumenUpdate() no instancia la vista Resumen-Update');

        $this->addCase($results, 'tipoview_pagina', str_contains($codigo, 'showVista(1,'),
            'listAll() no renderiza pagina completa con showVista(1)');
        $this->addCase($results, 'tipoview_modal', str_contains($codigo, 'showVista(2,'),
            'los metodos de detalle no renderizan modal con showVista(2)');
        $this->addCase($results, 'rutavista', str_contains($codigo, 'returnRutaVista('),
            'los metodos no resuelven la ruta con returnRutaVista()');
        $this->addCase($results, 'errores_ruta', str_contains($codigo, 'showError(1,') && str_contains($codigo, 'showError(2,'),
            'los metodos no manejan la ruta de error con showError()');
        $this->addCase($results, 'usuario', str_contains($codigo, 'getUserData($f3)'),
            'el controlador no obtiene los datos del usuario');
        $this->addCase($results, 'permisos_transaccion', str_contains($codigo, 'getArrLevel($f3, $tsrxName)'),
            'el controlador no resuelve los permisos con el nombre de la transaccion');
        $this->addCase($results, 'metadatos_pagina',
            str_contains($codigo, "'PageTitle'") && str_contains($codigo, "'TableTitle'"),
            'listAll() no asigna PageTitle y TableTitle');
        $this->addCase($results, 'fallback_errores', str_contains($codigo, 'mergeResponses('),
            'el controlador no agrupa las respuestas fallidas con mergeResponses()');

        /*====================================*/
        // Filtro de permisos por usuario sobre las bodegas
        /*====================================*/
        $this->addCase($results, 'filtro_permisos_bodegas',
            str_contains($codigo, 'bodegas_listado_permisos_usuarios'),
            'el controlador no aplica el filtro de permisos por bodega');
        $this->addCase($results, 'filtro_clave_permisos',
            str_contains($codigo, 'usuariosPermisosBodegas'),
            'el controlador no consulta la marca usuariosPermisosBodegas');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                         CONTROLADOR: ESCRITURA (CRUD)                      */
    /******************************************************************************/
    /**
     * Verifica el contrato de `Insert`, `Update`, `Delete` y `delFiles` SIN ejecutarlos.
     *
     * @return array Resultado por caso
     */
    public function controladorEscrituraTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Firma de cada metodo de escritura
        /*====================================*/
        $this->firmaMetodo($results, 'Insert', 1, 1);
        $this->firmaMetodo($results, 'Update', 1, 1);
        $this->firmaMetodo($results, 'Delete', 0, 0);
        $this->firmaMetodo($results, 'delFiles', 0, 0);

        /*====================================*/
        // Insert delega en la logica transaccional createMov
        /*====================================*/
        $this->addCase($results, 'insert_llama_create', str_contains($codigo, 'createMov('),
            'Insert() no invoca a createMov()');
        $this->addCase($results, 'insert_respuesta', str_contains($codigo, 'Response::success('),
            'Insert() no responde con Response::success()');

        /*====================================*/
        // Update y Delete delegan en la logica transaccional removeMov
        /*====================================*/
        $this->addCase($results, 'update_llama_remove', str_contains($codigo, 'removeMov('),
            'Update() no invoca a removeMov()');
        $this->addCase($results, 'delete_llama_remove', substr_count($codigo, 'removeMov(') >= 2,
            'Delete() no invoca a removeMov()');

        /*====================================*/
        // Verbo HTTP en Update/Delete/delFiles
        /*====================================*/
        $this->addCase($results, 'update_verbo_post', str_contains($codigo, "REQUEST_METHOD'] !== 'POST'"),
            'Update() no valida el verbo HTTP POST');
        $this->addCase($results, 'delete_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'DELETE'"),
            'Delete() no valida el verbo HTTP DELETE');
        $this->addCase($results, 'delfiles_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'PUT'"),
            'delFiles() no valida el verbo HTTP PUT');

        /*====================================*/
        // createMov/removeMov siempre compensan la transaccion
        /*====================================*/
        $this->addCase($results, 'create_transaccion',
            str_contains($codigo, 'Base_transactionBegin(')
            && str_contains($codigo, 'Base_transactionCommit(')
            && str_contains($codigo, 'Base_transactionRollback('),
            'createMov() no maneja la transaccion completa');
        $this->addCase($results, 'create_respuesta_200', str_contains($codigo, "'code' => 200"),
            'createMov() no devuelve codigo 200 en el camino feliz');
        $this->addCase($results, 'create_respuesta_400', substr_count($codigo, "'code' => 400") >= 5,
            'createMov() no valida las entradas con codigo 400');

        /*====================================*/
        // Los identificadores se descifran antes de operar
        /*====================================*/
        $this->addCase($results, 'create_sin_descifrar',
            !str_contains($codigo, "createMov(\$this->Codification"),
            'createMov() recibe identificadores ya descifrados fuera de la capa transaccional');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA DE MOVIMIENTOS                         */
    /******************************************************************************/
    /**
     * Ejecuta `createMov()` y `removeMov()` unicamente por sus ramas de validacion
     * previas: devuelven codigo 400 sin abrir transaccion ni tocar la base de datos.
     *
     * @return array Resultado por caso
     */
    public function logicaMovimientosTest(){

        $results = [];
        $Control = $this->instanciaSinBD(self::CONTROLADOR);
        // NewDBConn no nulo => createMov()/removeMov() jamas llaman a Base_transactionBegin()
        $sinTransaccion = new stdClass();

        /*====================================*/
        // createMov: sin productos
        /*====================================*/
        $vacio = $this->payloadMovimiento();
        unset($vacio['idProducto']);
        $respuesta = $Control->createMov($vacio, $sinTransaccion);
        $this->addCase($results, 'create_sin_productos',
            ($respuesta['code'] ?? 0) === 400 && str_contains((string)($respuesta['message'] ?? ''), 'productos'),
            'createMov() no rechaza un movimiento sin productos');

        /*====================================*/
        // createMov: cantidades ausentes
        /*====================================*/
        $sinCantidades = $this->payloadMovimiento();
        unset($sinCantidades['Number']);
        $respuesta = $Control->createMov($sinCantidades, $sinTransaccion);
        $this->addCase($results, 'create_sin_cantidades',
            ($respuesta['code'] ?? 0) === 400 && str_contains((string)($respuesta['message'] ?? ''), 'cantidades'),
            'createMov() no rechaza un movimiento sin cantidades');

        /*====================================*/
        // createMov: tipo de movimiento fuera de catalogo
        /*====================================*/
        $tipoInvalido = $this->payloadMovimiento();
        $tipoInvalido['idEstadoIngreso'] = 9;
        $respuesta = $Control->createMov($tipoInvalido, $sinTransaccion);
        $this->addCase($results, 'create_tipo_invalido',
            ($respuesta['code'] ?? 0) === 400 && str_contains((string)($respuesta['message'] ?? ''), 'Tipo de movimiento'),
            'createMov() no rechaza un tipo de movimiento fuera de catalogo');

        /*====================================*/
        // createMov: ingreso sin bodega de destino
        /*====================================*/
        $sinIngreso = $this->payloadMovimiento();
        $sinIngreso['idEstadoIngreso']  = 1;
        $sinIngreso['idBodegasIngreso'] = 0;
        $respuesta = $Control->createMov($sinIngreso, $sinTransaccion);
        $this->addCase($results, 'create_ingreso_sin_bodega',
            ($respuesta['code'] ?? 0) === 400 && str_contains((string)($respuesta['message'] ?? ''), 'bodega de ingreso'),
            'createMov() no exige bodega de ingreso en un ingreso');

        /*====================================*/
        // createMov: egreso sin bodega de origen
        /*====================================*/
        $sinEgreso = $this->payloadMovimiento();
        $sinEgreso['idEstadoIngreso'] = 2;
        $sinEgreso['idBodegasEgreso'] = 0;
        $respuesta = $Control->createMov($sinEgreso, $sinTransaccion);
        $this->addCase($results, 'create_egreso_sin_bodega',
            ($respuesta['code'] ?? 0) === 400 && str_contains((string)($respuesta['message'] ?? ''), 'bodega de egreso'),
            'createMov() no exige bodega de origen en un egreso');

        /*====================================*/
        // createMov: traspaso con origen y destino iguales
        /*====================================*/
        $traspasoIgual = $this->payloadMovimiento();
        $traspasoIgual['idEstadoIngreso']  = 3;
        $traspasoIgual['idBodegasIngreso'] = 1;
        $traspasoIgual['idBodegasEgreso']  = 1;
        $respuesta = $Control->createMov($traspasoIgual, $sinTransaccion);
        $this->addCase($results, 'create_traspaso_igual',
            ($respuesta['code'] ?? 0) === 400 && str_contains((string)($respuesta['message'] ?? ''), 'iguales'),
            'createMov() permite traspasar dentro de la misma bodega');

        /*====================================*/
        // createMov: producto invalido
        /*====================================*/
        $productoInvalido = $this->payloadMovimiento();
        $productoInvalido['idProducto'] = [0];
        $respuesta = $Control->createMov($productoInvalido, $sinTransaccion);
        $this->addCase($results, 'create_producto_invalido',
            ($respuesta['code'] ?? 0) === 400 && str_contains((string)($respuesta['message'] ?? ''), 'producto'),
            'createMov() no rechaza un producto sin identificador');

        /*====================================*/
        // createMov: cantidad sin valor declarado
        /*====================================*/
        $sinCantidad = $this->payloadMovimiento();
        $sinCantidad['Number'] = [];
        $respuesta = $Control->createMov($sinCantidad, $sinTransaccion);
        $this->addCase($results, 'create_cantidad_ausente',
            ($respuesta['code'] ?? 0) === 400 && str_contains((string)($respuesta['message'] ?? ''), 'cantidad'),
            'createMov() no rechaza un producto sin cantidad declarada');

        /*====================================*/
        // createMov: cantidad no positiva
        /*====================================*/
        $cantidadCero = $this->payloadMovimiento();
        $cantidadCero['Number'] = [0];
        $respuesta = $Control->createMov($cantidadCero, $sinTransaccion);
        $this->addCase($results, 'create_cantidad_cero',
            ($respuesta['code'] ?? 0) === 400 && str_contains((string)($respuesta['message'] ?? ''), 'mayor que cero'),
            'createMov() acepta cantidades no positivas');

        /*====================================*/
        // removeMov: identificador que no es un ID cifrado
        /*====================================*/
        $ControlMov = new (self::CONTROLADOR)();
        $respuesta = $ControlMov->removeMov(['idMovimiento' => 'zzz'], $sinTransaccion);
        $this->addCase($results, 'remove_id_invalido',
            ($respuesta['code'] ?? 0) === 400,
            'removeMov() no rechaza un identificador que no es un ID cifrado');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              CHEQUEOS DE DATOS                             */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos los tres `dataCheck_*` del controlador y comprueba
     * que cada uno cubre los campos que su consulta correspondiente declara.
     *
     * @return array Resultado por caso
     */
    public function chequeoDatosTest(){

        $results = [];
        $Control = $this->instanciaSinBD(self::CONTROLADOR);

        $arrChequeos = [
            'dataCheck_1' => ['ValidarNumero' => 'idEstadoIngreso', 'ValidarFecha' => 'Creacion_fecha'],
            'dataCheck_2' => ['ValidarNumero' => 'idMovimiento',    'ValidarEntero' => 'idProducto'],
            'dataCheck_3' => ['ValidarNumero' => 'idStocks',        'ValidarEntero' => 'idProducto'],
        ];
        foreach ($arrChequeos as $metodo => $esperado) {
            $existe = method_exists(self::CONTROLADOR, $metodo);
            $this->addCase($results, 'existe_'.$metodo, $existe,
                'no existe '.self::CONTROLADOR.'::'.$metodo.'()');
            if (!$existe) {
                continue;
            }
            $reflector = new ReflectionMethod(self::CONTROLADOR, $metodo);
            $reflector->setAccessible(true);
            $Post      = ['idEstadoIngreso' => 1, 'idMovimiento' => 11, 'idStocks' => 1, 'idProducto' => 3];
            $DataCheck = $reflector->invoke($Control, $Post);

            $this->addCase($results, $metodo.'_es_arreglo', is_array($DataCheck),
                $metodo.'() no devolvio un arreglo');
            $this->addCase($results, $metodo.'_post', ($DataCheck['Post'] ?? null) === $Post,
                $metodo.'() no conserva el POST recibido');
            foreach ($esperado as $regla => $campo) {
                $this->addCase($results, $metodo.'_'.$regla,
                    str_contains((string)($DataCheck[$regla] ?? ''), $campo),
                    $metodo.'() no aplica '.$regla.' sobre '.$campo);
            }
            $this->addCase($results, $metodo.'_largo_minimo', (int)($DataCheck['ValidarLargoMinimoN'] ?? 0) === 3,
                $metodo.'() no fija el largo minimo en 3');
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                PERMISOS                                    */
    /******************************************************************************/
    /**
     * Verifica la sesion del usuario y el nivel de acceso de las tres transacciones
     * de movimiento (ingreso, egreso y traspaso).
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
        // Lectura de la sesion desde el controlador
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
        // Cada transaccion publica su propio nivel y ruta
        /*====================================*/
        foreach (self::TRANSACCIONES as $tipo => $transaccion) {
            $datos = $this->getArrLevel($f3, $transaccion['clave']);
            $nivel = is_array($datos) && $datos !== [] ? ($datos['LevelAccess'] ?? null) : null;

            $this->addCase($results, 'transaccion_presente_'.$tipo, is_array($datos) && $datos !== [],
                'SESSION.arrLevel no contiene '.$transaccion['clave']);
            $this->addCase($results, 'nivel_rango_'.$tipo,
                $nivel === null || in_array((int)$nivel, self::NIVELES, true),
                'LevelAccess de '.$transaccion['clave'].' quedo fuera del catalogo 1..4');
            $this->addCase($results, 'ruta_transaccion_'.$tipo,
                $nivel === null || ($datos['RouteAccess'] ?? '') === $transaccion['ruta'],
                'RouteAccess de '.$transaccion['clave'].' no coincide con '.$transaccion['ruta']);
        }

        /*====================================*/
        // Registro dinamico de la ruta de ingresos en SESSION.arrPermisos
        /*====================================*/
        $arrPermisos = $f3->get('SESSION.arrPermisos');
        $this->addCase($results, 'permisos_de_ruta_presentes', is_array($arrPermisos),
            'SESSION.arrPermisos no esta disponible');

        if (is_array($arrPermisos)) {
            $rutaOk = false;
            foreach ($arrPermisos as $permiso) {
                if (isset($permiso['RutaWeb']) && $permiso['RutaWeb'] === self::RUTA_INGRESOS.'/listAll') {
                    $rutaOk = true;
                    break;
                }
            }
            $this->addCase($results, 'ruta_registrada', $rutaOk,
                'la ruta '.self::RUTA_INGRESOS.'/listAll no esta entre los permisos del usuario');
        }

        /*====================================*/
        // Rutas en el router de F3 (solo si el usuario las tiene registradas)
        /*====================================*/
        $arrRoutes = $f3->get('ROUTES');
        $this->addCase($results, 'router_disponible', is_array($arrRoutes),
            'F3 no expuso la variable ROUTES');

        $esquema = [
            'ruta_ingresos_router'  => ['/'.self::RUTA_INGRESOS.'/listAll',  'GET',  self::CONTROLADOR.'->listAll_1'],
            'ruta_egresos_router'   => ['/'.self::RUTA_EGRESOS.'/listAll',   'GET',  self::CONTROLADOR.'->listAll_2'],
            'ruta_traspaso_router'  => ['/'.self::RUTA_TRASPASO.'/listAll',  'GET',  self::CONTROLADOR.'->listAll_3'],
        ];
        foreach ($esquema as $caso => $rutaEsperada) {
            [$ruta, $verbo, $handlerEsperado] = $rutaEsperada;
            if (!isset($arrRoutes[$ruta])) {
                continue;
            }
            $handler = $arrRoutes[$ruta][0][$verbo][0] ?? '';
            $this->addCase($results, $caso, $handler === $handlerEsperado,
                'la ruta '.$ruta.' apunta a '.var_export($handler, true).' en vez de '.$handlerEsperado);
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /**
     * Renderiza la pagina de listado con datos sinteticos y verifica la matriz de
     * permisos (crear con nivel >= 3) y las acciones de la tabla (1/2/4).
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
        $this->addCase($results, 'listado_contenedor', str_contains($html, 'id="listTableData"'),
            'falta el contenedor de la tabla');
        $this->addCase($results, 'listado_form_busqueda', str_contains($html, 'id="FormSearchData"'),
            'no se dibuja el formulario de busqueda');
        $this->addCase($results, 'listado_form_nuevo', str_contains($html, 'id="newFormModal"'),
            'no se dibuja el modal de alta');
        $this->addCase($results, 'listado_sin_crear_nivel1', !str_contains($html, 'Crear Nuevo</button>'),
            'con nivel 1 el listado ofrece crear registros');
        $this->addCase($results, 'listado_endpoint_ver', str_contains($html, "/view/'"),
            'falta el endpoint de detalle');
        $this->addCase($results, 'listado_endpoint_actualizar', str_contains($html, '/updateList'),
            'falta el endpoint de refresco de tabla');
        $this->addCase($results, 'listado_borrado_viaje', str_contains($html, "Metodo      = 'DELETE'"),
            'la accion de borrar no viaja con el verbo DELETE');

        /*====================================*/
        // El boton de alta aparece con nivel >= 3
        /*====================================*/
        $htmlNivel3 = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php'),
            $this->datosSinteticos(3), $BASE, $f3);
        $this->addCase($results, 'listado_crear_nivel3', str_contains($htmlNivel3, 'Crear Nuevo</button>'),
            'con nivel 3 no se muestra el boton Crear Nuevo');

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
            $this->addCase($results, 'tabla_datos_nivel'.$nivel, str_contains($tabla, 'Bodega Central'),
                'la tabla no pinta la bodega de origen con nivel '.$nivel);
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                     VISTAS DE DETALLE, RESUMEN Y FORMULARIOS               */
    /******************************************************************************/
    /**
     * Renderiza el modal de detalle, el resumen y los formularios del modulo con
     * datos sinteticos para cada tipo de movimiento.
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
        $datos          = $this->datosSinteticos(2);
        $datos['arrProductos'] = $datos['arrProductosLista'] = $datos['arrProductosDetalle'];
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'),
            $datos, $BASE, $f3);
        $this->addCase($results, 'view_render', $html !== '', 'la vista View no produjo HTML');
        $this->addCase($results, 'view_pestanas',
            str_contains($html, 'id="view_tab_1"') && str_contains($html, 'id="view_tab_5"'),
            'la vista View no monta las pestanas Datos Basicos y Movimientos');
        $this->addCase($results, 'view_producto', str_contains($html, 'Producto Demo'),
            'la vista View no pinta el producto del movimiento');
        $this->addCase($results, 'view_cantidad', str_contains($html, 'Un'),
            'la vista View no muestra la unidad de medida');

        /*====================================*/
        // Vista vacia
        /*====================================*/
        $vacia          = $this->datosSinteticos(2);
        $vacia['arrProductos'] = [];
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'),
            $vacia, $BASE, $f3);
        $this->addCase($results, 'view_vacia', str_contains($html, 'No se encontraron entradas'),
            'la vista View no muestra el mensaje de lista vacia');

        /*====================================*/
        // Resumen del movimiento
        /*====================================*/
        foreach (self::TRANSACCIONES as $tipo => $transaccion) {
            $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen.php'),
                $this->datosSinteticos(2, $tipo), $BASE, $f3);
            $this->addCase($results, 'resumen_render_tipo_'.$tipo, $html !== '',
                'la vista Resumen no produjo HTML para el tipo '.$tipo);
            $this->addCase($results, 'resumen_form_tipo_'.$tipo, str_contains($html, 'id="FormEditData"'),
                'el resumen del tipo '.$tipo.' no declara el formulario FormEditData');
            $this->addCase($results, 'resumen_ocultos_tipo_'.$tipo,
                str_contains($html, 'name="idMovimiento"') && str_contains($html, 'name="idEstadoIngreso"'),
                'el resumen del tipo '.$tipo.' no arrastra los identificadores ocultos');
            $this->addCase($results, 'resumen_endpoint_tipo_'.$tipo,
                str_contains($html, self::TRANSACCIONES[$tipo]['ruta']) && str_contains($html, '/update'),
                'el resumen del tipo '.$tipo.' no apunta a su ruta de edicion');
        }

        /*====================================*/
        // Tabla de productos del resumen
        /*====================================*/
        $datos = $this->datosSinteticos(2);
        $datos['arrProductos'] = $datos['arrProductosDetalle'];
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen-Productos-UpdateList.php'),
            $datos, $BASE, $f3);
        $this->addCase($results, 'productos_render', $html !== '', 'la tabla de productos no produjo HTML');
        $this->addCase($results, 'productos_columnas',
            str_contains($html, 'Tipo Movimiento') && str_contains($html, 'Cantidad') && str_contains($html, 'Acciones'),
            'la tabla de productos no declara sus columnas');
        $this->addCase($results, 'productos_acciones',
            str_contains($html, 'tabProdEdit(') && str_contains($html, 'tabProdDel('),
            'la tabla de productos no publica editar ni borrar');
        $this->addCase($results, 'productos_id_cifrado',
            preg_match('/tabProdEdit\(\'[A-Za-z0-9_-]{10,}\'\)/', $html) === 1,
            'la tabla de productos no envia el ID cifrado');

        /*====================================*/
        // Formulario de alta
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-formNew.php'),
            $this->datosSinteticos(3), $BASE, $f3);
        $this->addCase($results, 'form_new_render', $html !== '', 'la vista formNew no produjo HTML');
        $this->addCase($results, 'form_new_id', str_contains($html, 'id="FormNewData"'),
            'el formulario de alta no usa el id FormNewData');
        $this->addCase($results, 'form_new_fecha', str_contains($html, 'name="Creacion_fecha"'),
            'el formulario de alta no declara la fecha del movimiento');
        $this->addCase($results, 'form_new_oculto', str_contains($html, 'name="idEstadoIngreso"'),
            'el formulario de alta no arrastra el tipo de movimiento');
        $this->addCase($results, 'form_new_producto', str_contains($html, 'name="idProducto[]"'),
            'el formulario de alta no declara la lista de productos');
        $this->addCase($results, 'form_new_endpoint',
            str_contains($html, self::RUTA_INGRESOS) && str_contains($html, "Metodo      = 'POST'"),
            'el formulario de alta no publica en '.self::RUTA_INGRESOS.' con POST');

        /*====================================*/
        // Formulario de busqueda (varia por tipo de movimiento)
        /*====================================*/
        foreach (self::TRANSACCIONES as $tipo => $transaccion) {
            $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-formSearch.php'),
                $this->datosSinteticos(1, $tipo), $BASE, $f3);
            $this->addCase($results, 'form_search_render_tipo_'.$tipo, $html !== '',
                'la vista formSearch no produjo HTML para el tipo '.$tipo);
            $this->addCase($results, 'form_search_id_tipo_'.$tipo, str_contains($html, 'id="FormSearchData"'),
                'el formulario de busqueda del tipo '.$tipo.' no usa el id FormSearchData');
            $this->addCase($results, 'form_search_fechas_tipo_'.$tipo,
                str_contains($html, 'id="Search_F_Inicio"') && str_contains($html, 'id="Search_F_Termino"'),
                'el formulario de busqueda del tipo '.$tipo.' no declara el rango de fechas');
        }

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
     * sesion desde las plantillas y de SQL concatenado.
     *
     * @return array Resultado por caso
     */
    public function seguridadVistasTest(){

        $results = [];

        /*====================================*/
        // Vistas involucradas
        /*====================================*/
        $arrVistas = glob($this->rutaModulo('../views/'.self::CONTROLADOR.'-*.php')) ?: [];
        $this->addCase($results, 'hay_vistas', count($arrVistas) >= 10,
            'se encontraron '.count($arrVistas).' vistas (esperadas al menos 10)');

        foreach ($arrVistas as $ruta) {
            $nombre = basename($ruta);
            $codigo = $this->codigoFuente($ruta);

            $this->addCase($results, 'vista_sin_retroceso_'.$nombre, !str_contains($codigo, '..'),
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
        // Escape de los datos pintados en el listado
        /*====================================*/
        $tabla = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'));
        $this->addCase($results, 'tabla_escape_entidad', str_contains($tabla, 'addslashes('),
            'UpdateList.php no escapa la etiqueta de la entidad');
        $this->addCase($results, 'tabla_escape_fecha', str_contains($tabla, 'fechaEstandar('),
            'UpdateList.php no normaliza la fecha antes de pintarla');

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

        /************************************/
        // Retorno los datos
        return $results;

    }

}
