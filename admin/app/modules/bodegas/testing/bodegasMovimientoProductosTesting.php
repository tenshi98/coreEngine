<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class bodegasMovimientoProductosTesting
 *
 * Pruebas del modulo `bodegas` - controlador `bodegasMovimientoProductos`
 * (productos asociados a un movimiento de ingreso, egreso o traspaso).
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
 *  - `Insert()`, `Update()` y `Delete()` terminan en `Response::*()` (que hace `exit`) y
 *    escriben en base de datos: solo se verifican por reflexion y lectura del fuente.
 *  - `insertMov()`, `updateMov()` y `deleteMov()` SI se ejecutan, pero unicamente por sus
 *    ramas de validacion previas, que retornan codigo 400 antes de abrir transaccion. Se les
 *    pasa un `$NewDBConn` no nulo para que jamas entren en `Base_transactionBegin()`.
 *    En `deleteMov()` ademas se inyecta `$Codification` por reflexion, porque se construye
 *    sin constructor.
 *
 * @see skills/testing/SKILL.md
 */
class bodegasMovimientoProductosTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR   = 'bodegasMovimientoProductos';
    // El controlador reutiliza las vistas y el nombre de transaccion de `bodegasMovimiento`
    private const VISTA_BASE    = 'bodegasMovimiento';
    private const TIPOS         = [
        1 => ['clave' => 'bodegasMovimientoIngreso',  'ruta' => 'gestionBodegas/ingresos/listado'],
        2 => ['clave' => 'bodegasMovimientoEgreso',   'ruta' => 'gestionBodegas/egresos/listado'],
        3 => ['clave' => 'bodegasMovimientoTraspaso', 'ruta' => 'gestionBodegas/traspaso/listado'],
    ];
    // Niveles de permiso del catalogo (1=ver, 2=editar, 3=crear, 4=borrar)
    private const NIVELES       = [1, 2, 3, 4];

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
    // Fuente del instalador del modulo (registro de rutas y permisos)
    /*******************************************************************/
    private function fuenteInstalador(): string{
        return $this->codigoFuente($this->rutaModulo('../installer/bodegasInstaller.php'));
    }

    /*******************************************************************/
    // Lineas del instalador que declaran rutas de este controlador
    // Formato: [ 'idMetodo RutaWeb' => ['handler','Controller','nivel'] ]
    /*******************************************************************/
    private function rutasInstalador(): array{
        $rutas = [];
        foreach (preg_split('/\r\n|\r|\n/', $this->fuenteInstalador()) as $linea) {
            if (!str_contains($linea, self::CONTROLADOR.'->')) {
                continue;
            }
            $ok = preg_match(
                "/'idMetodo'\s*=>\s*(\d+)\s*,\s*'RutaWeb'\s*=>\s*'([^']+)'\s*,\s*'RutaController'\s*=>\s*'([^']+)'.*?'idLevelLimit'\s*=>\s*'?(\d+)'?/",
                $linea, $m
            );
            if ($ok !== 1) {
                continue;
            }
            $rutas[$m[1].' '.$m[2]] = [
                'handler' => $m[3],
                'nivel'   => (int)$m[4],
                'ruta'    => $m[2],
                'metodo'  => (int)$m[1],
            ];
        }

        return $rutas;
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
    // Payload sintetico de producto de movimiento
    /*******************************************************************/
    private function payloadProducto(int $tipo = 1): array{
        return [
            'idMovimiento'    => 11,
            'idEstadoIngreso' => $tipo === 3 ? 1 : $tipo,
            'idBodegas'       => 1,
            'idPosicion'      => 0,
            'idProducto'      => 3,
            'Number'          => '5,5',
            'NumberOld'       => '5,5',
            'idExistencia'    => 4,
        ];
    }

    /*******************************************************************/
    // Datos sinteticos usados por las vistas del modulo
    /*******************************************************************/
    private function datosSinteticos(int $tipo = 1): array{
        return [
            'UserData' => [
                'MainPathUrl'                => '/admin/public/upload/',
                'sistemaModalSubtitle'       => 1,
                'sistemaModalCloseBTN'       => 2,
                'usuariosPermisosBodegas'    => 1,
                'UserType'                   => 2,
                'gestionDocumentosUsoBodega' => 1,
            ],
            'UserAccess' => [
                'LevelAccess' => 2,
                'RouteAccess' => self::TIPOS[$tipo]['ruta'],
            ],
            'Fnc_FormInputs'    => new UIFormInputs(),
            'Fnc_Codification'  => new FunctionsSecurityCodification(),
            'Fnc_DataDate'      => new FunctionsDataDate(),
            'Fnc_DataNumbers'   => new FunctionsDataNumbers(),
            'Fnc_WidgetsCommon' => new UIWidgetsCommon(),
            'Fnc_WidgetsViews'  => new UIWidgetsViews(),
            'idTipoIngreso'     => $tipo,
            'rowData' => [
                'idExistencia'    => 4,
                'idMovimiento'    => 11,
                'idEstadoIngreso' => $tipo,
                'idBodegas'       => 1,
                'idBodegasIngreso'=> 1,
                'idBodegasEgreso' => 2,
                'idPosicion'      => 7,
                'idProducto'      => 3,
                'Number'          => 5,
                'TipoMovimiento'  => 'Ingreso',
                'Bodega'          => 'Bodega Central',
                'ProductoNombre'  => 'Producto Demo',
            ],
            'arrBodegas' => [
                ['ID' => 1, 'idBodegas' => 1, 'Nombre' => 'Bodega Central'],
                ['ID' => 2, 'idBodegas' => 2, 'Nombre' => 'Bodega Norte'],
            ],
            'arrProductos' => [['ID' => 3, 'Nombre' => 'Producto Demo']],
            'arrTipoMov'   => [['ID' => 1, 'Nombre' => 'Ingreso']],
            'arrPosicion'  => [
                ['ID' => 7, 'Nombre' => 'P-01'],
                ['ID' => 8, 'Nombre' => 'P-02'],
            ],
            'arrProductosDetalle' => [[
                'idExistencia'     => 4,
                'idMovimiento'     => 11,
                'idEstadoIngreso'  => $tipo,
                'idBodegas'        => 1,
                'idPosicion'       => 7,
                'idProducto'       => 3,
                'Number'           => 5,
                'TipoMovimiento'   => 'Ingreso',
                'Bodega'           => 'Bodega Central',
                'ProductoNombre'   => 'Producto Demo',
                'ProductoCantidad' => 5,
                'Posicion'         => 'P-01',
                'UnidadMedida'     => 'Un',
            ]],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica la estructura de carpetas y archivos del controlador de productos
     * de movimiento y la convencion "clase = nombre de archivo" exigida por el
     * AUTOLOAD de Fat-Free.
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
            'vista_form_new'    => $this->rutaModulo('../views/'.self::VISTA_BASE.'-Resumen-Productos-formNew.php'),
            'vista_form_edit'   => $this->rutaModulo('../views/'.self::VISTA_BASE.'-Resumen-Productos-formEdit.php'),
            'vista_list'        => $this->rutaModulo('../views/'.self::VISTA_BASE.'-Resumen-Productos-UpdateList.php'),
            'testing'           => $this->rutaModulo('bodegasMovimientoProductosTesting.php'),
            'instalador'        => $this->rutaModulo('../installer/bodegasInstaller.php'),
            'readme'            => $this->rutaModulo('../README.md'),
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
        $this->addCase($results, 'clase_testing_cargada', class_exists('bodegasMovimientoProductosTesting'),
            'la clase bodegasMovimientoProductosTesting no esta disponible');

        /*====================================*/
        // Herencias esperadas por el proyecto
        /*====================================*/
        $padreController = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padreController === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padreController.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('bodegasMovimientoProductosTesting') === 'ControllerBase',
            'el testing no extiende ControllerBase');

        /*====================================*/
        // El controlador reutiliza las vistas de `bodegasMovimiento`
        /*====================================*/
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'nombre_vista_compartida',
            str_contains($codigo, "\$this->controllerName = '".self::VISTA_BASE."'"),
            'el controlador no declara controllerName = '.self::VISTA_BASE);

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
    /*                     CONTROLADOR: RUTAS POR TIPO DE MOVIMIENTO               */
    /******************************************************************************/
    /**
     * Verifica que los wrappers `*_1`, `*_2` y `*_3` existan, sean publicos y
     * deleguen en el metodo generico con el tipo de movimiento correcto, y que el
     * instalador registre las 18 rutas del controlador (6 acciones x 3 tipos) con
     * nivel de permiso 2.
     *
     * @return array Resultado por caso
     */
    public function controladorRutasTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Wrappers por tipo de movimiento
        /*====================================*/
        $arrWrappers = ['New', 'UpdateList', 'GetID'];
        foreach ($arrWrappers as $base) {
            foreach (self::TIPOS as $tipo => $transaccion) {
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
                    str_contains($codigo, '$this->'.$base.'($f3, $params, '.$tipo.')'),
                    $metodo.'() no delega en '.$base.'() con el tipo '.$tipo);
            }
        }

        /*====================================*/
        // El metodo generico resuelve la transaccion por tipo
        /*====================================*/
        foreach (self::TIPOS as $tipo => $transaccion) {
            $this->addCase($results, 'transaccion_tipo_'.$tipo,
                str_contains($codigo, "'".$transaccion['clave']."'"),
                'el controlador no declara la transaccion '.$transaccion['clave']);
        }

        /*====================================*/
        // Metodo generico con firma correcta
        /*====================================*/
        $this->firmaMetodo($results, 'New', 3, 3);
        $this->firmaMetodo($results, 'UpdateList', 3, 3);
        $this->firmaMetodo($results, 'GetID', 3, 3);

        /*====================================*/
        // El instalador registra las 18 rutas con nivel 2
        /*====================================*/
        $rutas = $this->rutasInstalador();
        $this->addCase($results, 'instalador_rutas', count($rutas) === 18,
            'el instalador declara '.count($rutas).' rutas de '.self::CONTROLADOR.' (esperadas 18)');

        $nivelOk = true;
        $handlerOk = true;
        foreach ($rutas as $ruta) {
            if ($ruta['nivel'] !== 2) {
                $nivelOk = false;
            }
            if (!str_starts_with($ruta['handler'], self::CONTROLADOR.'->')) {
                $handlerOk = false;
            }
        }
        $this->addCase($results, 'instalador_nivel', $nivelOk,
            'hay rutas de '.self::CONTROLADOR.' sin idLevelLimit 2');
        $this->addCase($results, 'instalador_handler', $handlerOk,
            'hay rutas cuyo RutaController no apunta a '.self::CONTROLADOR);

        // Acciones esperadas por cada tipo de movimiento
        foreach (self::TIPOS as $tipo => $transaccion) {
            $base = $transaccion['ruta'].'/productos';
            $arrAcciones = [
                ['metodo' => 1, 'ruta' => $base.'/new/@id'],
                ['metodo' => 1, 'ruta' => $base.'/updateList/@id'],
                ['metodo' => 1, 'ruta' => $base.'/getID/@id'],
                ['metodo' => 2, 'ruta' => $base],
                ['metodo' => 3, 'ruta' => $base],
                ['metodo' => 2, 'ruta' => $base.'/update'],
            ];
            foreach ($arrAcciones as $accion) {
                $clave = $accion['metodo'].' '.$accion['ruta'];
                $this->addCase($results, 'ruta_tipo_'.$tipo.'_'.md5($clave),
                    isset($rutas[$clave]),
                    'el instalador no registra '.$accion['ruta'].' (metodo '.$accion['metodo'].')');
            }
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                            CONTROLADOR: VISTAS                             */
    /******************************************************************************/
    /**
     * Verifica el contrato de presentacion de los metodos que devuelven HTML y el
     * rechazo de identificadores no cifrados.
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Contrato de presentacion
        /*====================================*/
        $this->addCase($results, 'vista_form_new',
            str_contains($codigo, "\$this->controllerName.'-Resumen-Productos-formNew.php'"),
            'New() no instancia la vista de alta');
        $this->addCase($results, 'vista_update_list',
            str_contains($codigo, "\$this->controllerName.'-Resumen-Productos-UpdateList.php'"),
            'UpdateList() no instancia la tabla de productos');
        $this->addCase($results, 'vista_form_edit',
            str_contains($codigo, "\$this->controllerName.'-Resumen-Productos-formEdit.php'"),
            'GetID() no instancia la vista de edicion');

        $this->addCase($results, 'tipoview_modal', str_contains($codigo, 'showVista(2,'),
            'los tres metodos renderizan modal con showVista(2)');
        $this->addCase($results, 'sin_pagina_completa', !str_contains($codigo, 'showVista(1,'),
            'el controlador renderiza una pagina completa en vez de un modal');
        $this->addCase($results, 'rutavista', str_contains($codigo, 'returnRutaVista('),
            'los metodos no resuelven la ruta con returnRutaVista()');
        $this->addCase($results, 'errores_ruta', str_contains($codigo, 'showError(2,'),
            'los metodos no manejan la ruta de error con showError()');
        $this->addCase($results, 'usuario', str_contains($codigo, 'getUserData($f3)'),
            'el controlador no obtiene los datos del usuario');
        $this->addCase($results, 'permisos_transaccion',
            str_contains($codigo, 'getArrLevel($f3, $tsrxName)') && substr_count($codigo, 'getArrLevel($f3, $tsrxName)') === 3,
            'el controlador no resuelve los permisos por cada tipo de movimiento');
        $this->addCase($results, 'fallback_errores', str_contains($codigo, 'mergeResponses('),
            'el controlador no agrupa las respuestas fallidas con mergeResponses()');

        /*====================================*/
        // Identificadores descifrados antes de operar
        /*====================================*/
        $this->addCase($results, 'id_cifrado',
            substr_count($codigo, "isValidDecrypted(") >= 3,
            'los metodos de vista no validan el identificador descifrado');
        $this->addCase($results, 'respuesta_400',
            substr_count($codigo, "'Registro inválido', 400") >= 3,
            'los metodos de vista no rechazan identificadores invalidos con 400');

        /*====================================*/
        // Filtro de permisos por usuario sobre las bodegas
        /*====================================*/
        $this->addCase($results, 'filtro_permisos_bodegas',
            str_contains($codigo, 'bodegas_listado_permisos_usuarios'),
            'el controlador no aplica el filtro de permisos por bodega');
        $this->addCase($results, 'filtro_clave_permisos',
            str_contains($codigo, 'usuariosPermisosBodegas'),
            'el controlador no consulta la marca usuariosPermisosBodegas');

        /*====================================*/
        // El detalle de productos no pagina completa ni publica menu
        /*====================================*/
        $this->addCase($results, 'sin_metadatos_pagina', !str_contains($codigo, "'PageTitle'"),
            'los modales declaran metadatos de pagina propios');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                         CONTROLADOR: ESCRITURA (CRUD)                      */
    /******************************************************************************/
    /**
     * Verifica el contrato de `Insert`, `Update` y `Delete` SIN ejecutarlos y el
     * ciclo transaccional de `insertMov`, `updateMov` y `deleteMov`.
     *
     * @return array Resultado por caso
     */
    public function controladorEscrituraTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Firma de cada metodo de escritura
        /*====================================*/
        $this->firmaMetodo($results, 'Insert', 0, 0);
        $this->firmaMetodo($results, 'Update', 0, 0);
        $this->firmaMetodo($results, 'Delete', 0, 0);
        $this->firmaMetodo($results, 'insertMov', 1, 2);
        $this->firmaMetodo($results, 'updateMov', 1, 2);
        $this->firmaMetodo($results, 'deleteMov', 1, 2);

        /*====================================*/
        // Cada endpoint delega en su logica transaccional
        /*====================================*/
        $this->addCase($results, 'insert_llama_mov', str_contains($codigo, 'insertMov($_POST)'),
            'Insert() no invoca a insertMov($_POST)');
        $this->addCase($results, 'update_llama_mov', str_contains($codigo, 'updateMov($_POST)'),
            'Update() no invoca a updateMov($_POST)');
        $this->addCase($results, 'delete_llama_mov', str_contains($codigo, 'deleteMov($dataDelete)'),
            'Delete() no invoca a deleteMov($dataDelete)');
        $this->addCase($results, 'delete_parsea_body',
            str_contains($codigo, "file_get_contents('php://input')"),
            'Delete() no lee el cuerpo de la peticion DELETE');

        /*====================================*/
        // Verbo HTTP en Update/Delete
        /*====================================*/
        $this->addCase($results, 'update_verbo_post', str_contains($codigo, "REQUEST_METHOD'] !== 'POST'"),
            'Update() no valida el verbo HTTP POST');
        $this->addCase($results, 'delete_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'DELETE'"),
            'Delete() no valida el verbo HTTP DELETE');
        $this->addCase($results, 'insert_respuesta', str_contains($codigo, 'Response::success('),
            'los endpoints no responden con Response::success()');

        /*====================================*/
        // Transaccion completa y solo cuando corresponde
        /*====================================*/
        $this->addCase($results, 'create_transaccion',
            str_contains($codigo, 'Base_transactionBegin(')
            && str_contains($codigo, 'Base_transactionCommit(')
            && str_contains($codigo, 'Base_transactionRollback('),
            'los metodos transaccionales no manejan la transaccion completa');
        $this->addCase($results, 'transaccion_condicionada',
            substr_count($codigo, 'if($NewDBConn === null){') === 6,
            'no todas las aperturas y cierres de transaccion verifican si la conexion es externa');
        $this->addCase($results, 'create_respuesta_200', substr_count($codigo, "'code' => 200") >= 3,
            'los metodos transaccionales no devuelven codigo 200 en el camino feliz');
        $this->addCase($results, 'create_respuesta_400', substr_count($codigo, "'code' => 400") >= 15,
            'los metodos transaccionales no validan las entradas con codigo 400');
        $this->addCase($results, 'respuesta_404', str_contains($codigo, "'code' => 404"),
            'deleteMov() no informa un producto inexistente con 404');

        /*====================================*/
        // Los endpoints no abren transaccion por su cuenta
        /*====================================*/
        $this->addCase($results, 'transaccion_solo_en_mov',
            substr_count($codigo, 'Base_transactionBegin(') === 3,
            'Insert/Update/Delete abren transaccion fuera de *Mov()');

        /*====================================*/
        // Reversion de stock en ambos sentidos
        /*====================================*/
        $this->addCase($results, 'stock_guardar', substr_count($codigo, 'guardarStock(') === 2,
            'insertMov()/updateMov() no actualizan el stock calculado');
        $this->addCase($results, 'stock_actualizar', substr_count($codigo, 'actualizarStock(') === 2,
            'deleteMov() no revierte el stock de ingreso ni de egreso');
        $this->addCase($results, 'stock_egreso_insuficiente',
            str_contains($codigo, 'Stock insuficiente'),
            'insertMov()/updateMov() no validan stock insuficiente en egreso');

        /*====================================*/
        // La posicion es opcional pero validada contra la bodega
        /*====================================*/
        $this->addCase($results, 'posicion_helper',
            substr_count($codigo, 'existePosicionEnBodega(') === 3,
            'el controlador no declara y usa existePosicionEnBodega() en insertMov() y updateMov()');
        $this->addCase($results, 'posicion_mensaje_error',
            substr_count($codigo, 'La posición indicada no existe o no pertenece a la bodega seleccionada') === 2,
            'insertMov()/updateMov() no rechazan una posicion ajena a la bodega');
        $this->addCase($results, 'posicion_opcional',
            substr_count($codigo, "'idPosicion'      => (\$idPosicion > 0) ? \$idPosicion : ''") === 2,
            'la posicion no se guarda como campo opcional en insert y update');
        $this->addCase($results, 'posicion_vacia_limpia_null',
            str_contains($codigo, "array_key_exists('idPosicion', \$PostData) && \$idPosicion <= 0")
            && str_contains($codigo, 'UPDATE bodegas_movimientos_productos SET idPosicion = NULL WHERE idExistencia = ?'),
            'updateMov() no limpia la posicion cuando el formulario envia la clave vacia');

        /*====================================*/
        // La clave decimal chilena se normaliza
        /*====================================*/
        $this->addCase($results, 'coma_decimal',
            substr_count($codigo, "str_replace(',', '.',") >= 2,
            'los metodos no normalizan la coma decimal antes de castear');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA DE PRODUCTOS                           */
    /******************************************************************************/
    /**
     * Ejecuta `insertMov()`, `updateMov()` y `deleteMov()` unicamente por sus ramas
     * de validacion previas: devuelven codigo 400 sin abrir transaccion ni tocar la
     * base de datos.
     *
     * @return array Resultado por caso
     */
    public function logicaMovimientosTest(){

        $results = [];
        $Control = $this->instanciaSinBD(self::CONTROLADOR);

        // Codification es privada y el controlador se construye sin constructor
        $propiedad = new ReflectionProperty(self::CONTROLADOR, 'Codification');
        $propiedad->setAccessible(true);
        $propiedad->setValue($Control, new FunctionsSecurityCodification());

        // NewDBConn no nulo => *Mov() jamas llaman a Base_transactionBegin()
        $sinTransaccion = new stdClass();

        /*====================================*/
        // insertMov: validaciones
        /*====================================*/
        $arrInsert = [
            'insert_movimiento_invalido' => ['idMovimiento' => 0, 'esperado' => 'Movimiento inválido'],
            'insert_tipo_invalido'       => ['idEstadoIngreso' => 3, 'esperado' => 'Tipo de movimiento inválido'],
            'insert_bodega_invalida'     => ['idBodegas' => 0, 'esperado' => 'bodega válida'],
            'insert_producto_invalido'   => ['idProducto' => 0, 'esperado' => 'Producto inválido'],
            'insert_cantidad_invalida'   => ['Number' => 0, 'esperado' => 'mayor que cero'],
        ];
        foreach ($arrInsert as $caso => $defecto) {
            $payload = $this->payloadProducto();
            foreach ($defecto as $campo => $valor) {
                if ($campo === 'esperado') {
                    continue;
                }
                $payload[$campo] = $valor;
            }
            $respuesta = $Control->insertMov($payload, $sinTransaccion);
            $this->addCase($results, $caso,
                ($respuesta['code'] ?? 0) === 400
                && str_contains((string)($respuesta['message'] ?? ''), $defecto['esperado']),
                'insertMov() no rechaza '.$caso.' (code '.($respuesta['code'] ?? 'sin codigo').')');
        }

        /************************************/
        // La clave singular 'idBodega' (error historico de facturacion) no se acepta:
        // insertMov() unicamente lee 'idBodegas' y debe rechazar el payload sin ella.
        $payloadSingular = $this->payloadProducto();
        unset($payloadSingular['idBodegas']);
        $payloadSingular['idBodega'] = 1;
        $respuesta = $Control->insertMov($payloadSingular, $sinTransaccion);
        $this->addCase($results, 'insert_rechaza_idBodega_singular',
            ($respuesta['code'] ?? 0) === 400
            && str_contains((string)($respuesta['message'] ?? ''), 'bodega válida'),
            'insertMov() acepta la clave singular idBodega en vez de exigir idBodegas');

        /*====================================*/
        // updateMov: validaciones
        /*====================================*/
        $arrUpdate = [
            'update_registro_invalido'    => ['idExistencia' => 0, 'esperado' => 'Registro inválido'],
            'update_movimiento_invalido'  => ['idMovimiento' => 0, 'esperado' => 'Movimiento inválido'],
            'update_tipo_invalido'        => ['idEstadoIngreso' => 3, 'esperado' => 'Tipo de movimiento inválido'],
            'update_bodega_invalida'      => ['idBodegas' => 0, 'esperado' => 'bodega válida'],
            'update_producto_invalido'    => ['idProducto' => 0, 'esperado' => 'Producto inválido'],
            'update_cantidad_invalida'    => ['Number' => 0, 'esperado' => 'mayor que cero'],
            'update_cantidad_anterior'    => ['NumberOld' => -1, 'esperado' => 'cantidad anterior'],
        ];
        foreach ($arrUpdate as $caso => $defecto) {
            $payload = $this->payloadProducto();
            foreach ($defecto as $campo => $valor) {
                if ($campo === 'esperado') {
                    continue;
                }
                $payload[$campo] = $valor;
            }
            $respuesta = $Control->updateMov($payload, $sinTransaccion);
            $this->addCase($results, $caso,
                ($respuesta['code'] ?? 0) === 400
                && str_contains((string)($respuesta['message'] ?? ''), $defecto['esperado']),
                'updateMov() no rechaza '.$caso.' (code '.($respuesta['code'] ?? 'sin codigo').')');
        }

        /*====================================*/
        // deleteMov: identificador que no es un ID cifrado
        /*====================================*/
        $respuesta = $Control->deleteMov(['idExistencia' => 'zzz'], $sinTransaccion);
        $this->addCase($results, 'delete_id_invalido',
            ($respuesta['code'] ?? 0) === 400,
            'deleteMov() no rechaza un identificador que no es un ID cifrado');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              CHEQUEOS DE DATOS                             */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos el `dataCheck_1` privado del controlador y comprueba
     * que cubra los campos que la insercion y la edicion declaran.
     *
     * @return array Resultado por caso
     */
    public function chequeoDatosTest(){

        $results = [];
        $Control = $this->instanciaSinBD(self::CONTROLADOR);

        $existe = method_exists(self::CONTROLADOR, 'dataCheck_1');
        $this->addCase($results, 'existe_dataCheck_1', $existe,
            'no existe '.self::CONTROLADOR.'::dataCheck_1()');
        if (!$existe) {
            return $results;
        }

        $reflector = new ReflectionMethod(self::CONTROLADOR, 'dataCheck_1');
        $reflector->setAccessible(true);
        $Post      = $this->payloadProducto();
        $DataCheck = $reflector->invoke($Control, $Post);

        $this->addCase($results, 'dataCheck_1_es_arreglo', is_array($DataCheck),
            'dataCheck_1() no devolvio un arreglo');
        $this->addCase($results, 'dataCheck_1_post', ($DataCheck['Post'] ?? null) === $Post,
            'dataCheck_1() no conserva el POST recibido');
        $this->addCase($results, 'dataCheck_1_validar_numero',
            str_contains((string)($DataCheck['ValidarNumero'] ?? ''), 'idMovimiento,idEstadoIngreso,idBodegas,idPosicion,idProducto,Number'),
            'dataCheck_1() no valida los numeros del producto');
        $this->addCase($results, 'dataCheck_1_validar_entero',
            str_contains((string)($DataCheck['ValidarEntero'] ?? ''), 'idMovimiento,idEstadoIngreso,idBodegas,idPosicion,idProducto'),
            'dataCheck_1() no valida los enteros del producto');
        $this->addCase($results, 'dataCheck_1_largo_minimo', (int)($DataCheck['ValidarLargoMinimoN'] ?? 0) === 3,
            'dataCheck_1() no fija el largo minimo en 3');
        $this->addCase($results, 'dataCheck_1_largo_maximo', ($DataCheck['ValidarLargoMaximoPorCampo'] ?? null) === [],
            'dataCheck_1() no deberia fijar largos maximos por campo en el detalle');

        // Insert() y Update() pasan por el mismo chequeo (no distinguen mayusculas PHP)
        $this->addCase($results, 'dataCheck_usado',
            str_contains($this->fuenteControlador(), 'dataCheck_1(') && str_contains($this->fuenteControlador(), 'DataCheck_1('),
            'insertMov()/updateMov() no invocan al chequeo de datos');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                  PERMISOS                                  */
    /******************************************************************************/
    /**
     * Verifica la sesion del usuario y el nivel de acceso de las tres transacciones
     * de movimiento bajo las que se publican las rutas de productos.
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
        }

        /*====================================*/
        // Cada transaccion publica su propio nivel y ruta
        /*====================================*/
        foreach (self::TIPOS as $tipo => $transaccion) {
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
        // Registro de las rutas de productos en SESSION.arrPermisos
        /*====================================*/
        $arrPermisos = $f3->get('SESSION.arrPermisos');
        $this->addCase($results, 'permisos_de_ruta_presentes', is_array($arrPermisos),
            'SESSION.arrPermisos no esta disponible');

        if (is_array($arrPermisos)) {
            foreach (self::TIPOS as $tipo => $transaccion) {
                $encontrada = false;
                foreach ($arrPermisos as $permiso) {
                    if (isset($permiso['RutaWeb'])
                        && str_starts_with($permiso['RutaWeb'], $transaccion['ruta'].'/productos')) {
                        $encontrada = true;
                        break;
                    }
                }
                $this->addCase($results, 'ruta_registrada_'.$tipo, $encontrada,
                    'ninguna ruta de '.$transaccion['ruta'].'/productos esta entre los permisos del usuario');
            }
        }

        /*====================================*/
        // Rutas en el router de F3 (solo si el usuario las tiene registradas)
        /*====================================*/
        $arrRoutes = $f3->get('ROUTES');
        $this->addCase($results, 'router_disponible', is_array($arrRoutes),
            'F3 no expuso la variable ROUTES');

        if (is_array($arrRoutes)) {
            foreach (self::TIPOS as $tipo => $transaccion) {
                $base = $transaccion['ruta'].'/productos';
                $esquema = [
                    'ruta_new_'.$tipo      => ['/'.$base.'/new/@id',           'GET',  self::CONTROLADOR.'->New_'.$tipo],
                    'ruta_list_'.$tipo     => ['/'.$base.'/updateList/@id',    'GET',  self::CONTROLADOR.'->UpdateList_'.$tipo],
                    'ruta_get_'.$tipo      => ['/'.$base.'/getID/@id',         'GET',  self::CONTROLADOR.'->GetID_'.$tipo],
                    'ruta_insert_'.$tipo   => ['/'.$base,                      'POST', self::CONTROLADOR.'->Insert'],
                    'ruta_update_'.$tipo   => ['/'.$base.'/update',            'POST', self::CONTROLADOR.'->Update'],
                    'ruta_delete_'.$tipo   => ['/'.$base,                      'DELETE', self::CONTROLADOR.'->Delete'],
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
     * Renderiza el modal de alta, el de edicion y la tabla de productos con datos
     * sinteticos para cada tipo de movimiento.
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

        /*====================================*/
        // Modal de alta (New)
        /*====================================*/
        foreach (self::TIPOS as $tipo => $transaccion) {
            $html = $this->renderizarVista(
                $this->rutaModulo('../views/'.self::VISTA_BASE.'-Resumen-Productos-formNew.php'),
                $this->datosSinteticos($tipo), $BASE, $f3);

            $this->addCase($results, 'form_new_render_tipo_'.$tipo, $html !== '',
                'la vista formNew no produjo HTML para el tipo '.$tipo);
            $this->addCase($results, 'form_new_id_tipo_'.$tipo, str_contains($html, 'id="FormNewMovimiento"'),
                'el formulario de alta del tipo '.$tipo.' no usa el id FormNewMovimiento');
            $this->addCase($results, 'form_new_producto_tipo_'.$tipo, str_contains($html, 'id="NewProd_idProducto"'),
                'el formulario de alta del tipo '.$tipo.' no declara el selector de productos');
            $this->addCase($results, 'form_new_cantidad_tipo_'.$tipo,
                str_contains($html, 'id="NewProd_Number"') && str_contains($html, 'name="Number"'),
                'el formulario de alta del tipo '.$tipo.' no declara la cantidad');
            $this->addCase($results, 'form_new_posicion_tipo_'.$tipo,
                str_contains($html, 'id="NewProd_idPosicion"') && str_contains($html, 'name="idPosicion"'),
                'el formulario de alta del tipo '.$tipo.' no declara el selector de posiciones');
            $this->addCase($results, 'form_new_oculto_tipo_'.$tipo, str_contains($html, 'name="idMovimiento"'),
                'el formulario de alta del tipo '.$tipo.' no arrastra el movimiento');
            $this->addCase($results, 'form_new_endpoint_tipo_'.$tipo,
                str_contains($html, $transaccion['ruta'].'/productos')
                && str_contains($html, "'POST'")
                && str_contains($html, '/productos/updateList/'),
                'el formulario de alta del tipo '.$tipo.' no publica en su ruta de productos');
        }

        /*====================================*/
        // Ocultos de bodega y estado segun el tipo
        /*====================================*/
        $htmlIngreso = $this->renderizarVista(
            $this->rutaModulo('../views/'.self::VISTA_BASE.'-Resumen-Productos-formNew.php'),
            $this->datosSinteticos(1), $BASE, $f3);
        $this->addCase($results, 'form_new_ingreso_ocultos',
            str_contains($htmlIngreso, 'name="idBodegas"') && str_contains($htmlIngreso, 'name="idEstadoIngreso"'),
            'el alta de ingreso no fija bodega y estado ocultos');

        $htmlTraspaso = $this->renderizarVista(
            $this->rutaModulo('../views/'.self::VISTA_BASE.'-Resumen-Productos-formNew.php'),
            $this->datosSinteticos(3), $BASE, $f3);
        $this->addCase($results, 'form_new_traspaso_selects',
            str_contains($htmlTraspaso, 'id="NewProd_idEstadoIngreso"') && str_contains($htmlTraspaso, 'id="NewProd_idBodegas"'),
            'el alta de traspaso no ofrece selector de tipo y bodega');

        /*====================================*/
        // Modal de edicion (GetID)
        /*====================================*/
        $html = $this->renderizarVista(
            $this->rutaModulo('../views/'.self::VISTA_BASE.'-Resumen-Productos-formEdit.php'),
            $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'form_edit_render', $html !== '', 'la vista formEdit no produjo HTML');
        $this->addCase($results, 'form_edit_id', str_contains($html, 'id="FormEditMovimiento"'),
            'el formulario de edicion no usa el id FormEditMovimiento');
        $this->addCase($results, 'form_edit_ocultos',
            str_contains($html, 'name="idExistencia"')
            && str_contains($html, 'name="idMovimiento"')
            && str_contains($html, 'name="NumberOld"'),
            'el formulario de edicion no arrastra identificadores ni la cantidad anterior');
        $this->addCase($results, 'form_edit_solo_lectura',
            str_contains($html, 'name="TipoMovimientoFake"') && str_contains($html, 'name="ProductoFake"'),
            'el formulario de edicion no pinta los campos de solo lectura');
        $this->addCase($results, 'form_edit_endpoint',
            str_contains($html, '/productos/update') && str_contains($html, "'POST'"),
            'el formulario de edicion no publica en la ruta de actualizacion');
        $this->addCase($results, 'form_edit_cantidad_inicial',
            str_contains($html, 'id="EditProd_Number"'),
            'el formulario de edicion no declara la cantidad editable');
        $this->addCase($results, 'form_edit_posicion',
            str_contains($html, 'id="EditProd_idPosicion"') && str_contains($html, 'name="idPosicion"'),
            'el formulario de edicion no declara el selector de posiciones');

        /*====================================*/
        // Tabla de productos (UpdateList)
        /*====================================*/
        $rutaTabla = $this->rutaModulo('../views/'.self::VISTA_BASE.'-Resumen-Productos-UpdateList.php');
        $datos = $this->datosSinteticos(2);
        $datos['arrProductos'] = $datos['arrProductosDetalle'];
        $html = $this->renderizarVista($rutaTabla, $datos, $BASE, $f3);
        $this->addCase($results, 'tabla_render', $html !== '', 'la vista UpdateList no produjo HTML');
        $this->addCase($results, 'tabla_columnas',
            str_contains($html, 'Tipo Movimiento')
            && str_contains($html, 'Bodega')
            && str_contains($html, 'Producto')
            && str_contains($html, 'Posición')
            && str_contains($html, 'Cantidad')
            && str_contains($html, 'Acciones'),
            'la tabla de productos no declara sus columnas');
        $this->addCase($results, 'tabla_datos', str_contains($html, 'Producto Demo') && str_contains($html, 'Bodega Central') && str_contains($html, 'P-01'),
            'la tabla de productos no pinta los datos de la fila');
        $this->addCase($results, 'tabla_acciones',
            str_contains($html, 'tabProdEdit(') && str_contains($html, 'tabProdDel('),
            'la tabla de productos no publica editar ni borrar');
        $this->addCase($results, 'tabla_id_cifrado',
            preg_match('/tabProdEdit\(\'[A-Za-z0-9_-]{10,}\'\)/', $html) === 1,
            'la tabla de productos no envia el ID cifrado');
        $this->addCase($results, 'tabla_escape_entidad', str_contains($this->codigoFuente($rutaTabla), 'addslashes('),
            'la tabla de productos no escapa el nombre de la entidad');

        /*====================================*/
        // Tabla vacia
        /*====================================*/
        $vacia = $this->datosSinteticos(2);
        $vacia['arrProductos'] = [];
        $html = $this->renderizarVista($rutaTabla, $vacia, $BASE, $f3);
        $this->addCase($results, 'tabla_vacia', $html !== '' && !str_contains($html, 'tabProdEdit('),
            'la tabla vacia dibuja acciones sobre filas inexistentes');

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
        // Vistas involucradas (las 10 vistas del modulo base)
        /*====================================*/
        $arrVistas = glob($this->rutaModulo('../views/'.self::VISTA_BASE.'-*.php')) ?: [];
        $this->addCase($results, 'hay_vistas', count($arrVistas) >= 10,
            'se encontraron '.count($arrVistas).' vistas (esperadas al menos 10)');

        $arrPropias = [
            $this->rutaModulo('../views/'.self::VISTA_BASE.'-Resumen-Productos-formNew.php'),
            $this->rutaModulo('../views/'.self::VISTA_BASE.'-Resumen-Productos-formEdit.php'),
            $this->rutaModulo('../views/'.self::VISTA_BASE.'-Resumen-Productos-UpdateList.php'),
        ];
        foreach ($arrPropias as $ruta) {
            $arrVistas[] = $ruta;
        }

        foreach (array_unique($arrVistas) as $ruta) {
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
        // Escape de los datos pintados en la tabla de productos
        /*====================================*/
        $tabla = $this->codigoFuente($this->rutaModulo('../views/'.self::VISTA_BASE.'-Resumen-Productos-UpdateList.php'));
        $this->addCase($results, 'tabla_escape_fecha_o_entidad',
            str_contains($tabla, 'addslashes('),
            'la tabla de productos no escapa la entidad');

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
        $this->addCase($results, 'controlador_sin_ruta_salida',
            !preg_match('/\.\s*\$_(POST|GET|REQUEST).*header\s*\(/i', $controlador),
            'el controlador redirecciona con datos del cliente');

        /************************************/
        // Retorno los datos
        return $results;

    }

}
