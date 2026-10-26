<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class informeProductosTesting
 *
 * Pruebas del modulo `bodegas` - controlador `informeProductos` (reporte de stock
 * actual de los productos en las bodegas, con detalle y pantalla de impresion).
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
 *  - El controlador es de solo lectura: no declara `Insert/Update/Delete`, ni transacciones,
 *    ni `Response::success()`. Por eso las pruebas ejecutan sus vistas y verifican el resto
 *    del contrato estaticamente.
 *
 * @see skills/testing/SKILL.md
 */
class informeProductosTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR = 'informeProductos';
    private const RUTA_WEB    = 'gestionBodegas/productos/listado';
    // Niveles de permiso del catalogo (1=ver, 2=editar, 3=crear, 4=borrar)
    private const NIVELES     = [1, 2, 3, 4];

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
    // Formato: [ 'idMetodo RutaWeb' => ['handler','nivel'] ]
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
    private function renderizarVista(string $rutaVista, array $data, string $BASE, $f3 = null, ?string $dirTrabajo = null): string{
        if (!is_file($rutaVista)) {
            return '';
        }
        $cwd = getcwd();
        @chdir($dirTrabajo ?? $this->rutaModulo('../../../../public'));
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
    // Datos sinteticos usados por las vistas del informe
    /*******************************************************************/
    private function datosSinteticos(): array{
        return [
            'PageTitle'       => 'Stock Productos',
            'PageDescription' => 'Stock Productos.',
            'PageAuthor'      => 'Pruebas',
            'PageKeywords'    => 'Pruebas',
            'TableTitle'      => 'Stock Actual',
            'UserData' => [
                'MainPathUrl'                => '/admin/public/upload/',
                'sistemaModalSubtitle'       => 1,
                'sistemaModalCloseBTN'       => 2,
                'usuariosPermisosBodegas'    => 1,
                'UserType'                   => 2,
                'gestionDocumentosUsoBodega' => 1,
            ],
            'UserAccess' => [
                'LevelAccess' => 1,
                'RouteAccess' => self::RUTA_WEB,
            ],
            'Fnc_FormInputs'   => new UIFormInputs(),
            'Fnc_Codification' => new FunctionsSecurityCodification(),
            'Fnc_DataDate'     => new FunctionsDataDate(),
            'Fnc_DataNumbers'  => new FunctionsDataNumbers(),
            'Fnc_WidgetsCommon'=> new UIWidgetsCommon(),
            'Fnc_WidgetsViews'  => new UIWidgetsViews(),
            'arrBodegas' => [
                ['ID' => 1, 'idBodegas' => 1, 'Nombre' => 'Bodega Central'],
                ['ID' => 2, 'idBodegas' => 2, 'Nombre' => 'Bodega Norte'],
            ],
            'arrProductos' => [['ID' => 3, 'Nombre' => 'Producto Demo']],
            'arrStocks' => [
                [
                    'idProducto'          => 3,
                    'Producto'            => 'Producto Demo',
                    'UniMed'              => 'Un',
                    'Cantidad_idBodegas_1' => 10,
                    'Cantidad_idBodegas_2' => 4,
                ],
            ],
            'rowProducto' => ['idProducto' => 3, 'Nombre' => 'Producto Demo'],
            'rowBodega'   => ['idBodegas' => 1, 'Nombre' => 'Bodega Central'],
        ];
    }

    /*******************************************************************/
    // Fila sintetica de movimientos del producto (vista View)
    /*******************************************************************/
    private function filaMovimiento(int $tipo): array{
        return [
            'idMovimiento'    => 11,
            'idEstadoIngreso' => $tipo,
            'idFacturacion'   => 7,
            'idTipo'          => 1,
            'Documento'       => 'Factura',
            'N_Doc'           => '0001',
            'Creacion_fecha'  => '2026-01-02',
            'Creacion_hora'   => '10:00:00',
            'Observaciones'   => 'Observaciones de prueba',
            'TipoMov'         => $tipo === 1 ? 'Ingreso' : 'Egreso',
            'Number'          => 5,
            'UniMed'          => 'Un',
        ];
    }

    /*******************************************************************/
    // Datos sinteticos de la pantalla de impresion (plantilla comun)
    /*******************************************************************/
    private function datosImpresion(): array{
        return [
            'PageTitle' => 'Imprimir',
            'BASE'      => '/admin',
            'UserData' => [
                'MainPathUrl'          => '/admin/public/upload/',
                'KanbanTareasUsoTareas' => 1,
                'sistemaModalSubtitle' => 1,
                'sistemaModalCloseBTN' => 2,
            ],
            'UserAccess' => [
                'LevelAccess' => 1,
                'RouteAccess' => self::RUTA_WEB,
            ],
            'Fnc_DataDate'      => new FunctionsDataDate(),
            'Fnc_Codification'  => new FunctionsSecurityCodification(),
            'Fnc_DataNumbers'   => new FunctionsDataNumbers(),
            'Fnc_WidgetsCommon' => new UIWidgetsCommon(),
            'Fnc_WidgetsViews'  => new UIWidgetsViews(),
            'rowData' => [
                'PrioridadColor'     => '1',
                'PrioridadNombre'    => 'Alta',
                'KanbanColor'        => '3',
                'KanbanEstado'       => 'Pendiente',
                'EstadoCierreColor'  => '5',
                'EstadoCierreNombre' => 'Abierto',
                'Fecha'              => '2026-01-02',
                'Titulo'             => 'Titulo de impresion',
                'Descripcion'        => 'Descripcion de impresion',
            ],
            'arrTareas' => [[
                'Tarea'        => 'Tarea de prueba',
                'Trabajo'      => 'Trabajo de prueba',
                'EstadoColor'  => '1',
                'EstadoIcon'   => 'bi-check2',
                'EstadoNombre' => 'Hecha',
            ]],
            'arrParticipantes' => [[
                'idParticipantes' => 1,
                'UsuarioNombre'   => 'Victor Reyes',
                'UsuarioImg'      => '',
            ]],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica la estructura de carpetas y archivos del controlador de informes y
     * la convencion "clase = nombre de archivo" exigida por el AUTOLOAD de Fat-Free.
     *
     * @return array Resultado por caso
     */
    public function estructuraModuloTest(){

        $results = [];

        /*====================================*/
        // Archivos obligatorios
        /*====================================*/
        $arrArchivos = [
            'controlador'   => $this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'),
            'vista_list'    => $this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php'),
            'vista_listado' => $this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'),
            'vista_view'    => $this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'),
            'vista_print'   => $this->rutaModulo('../views/'.self::CONTROLADOR.'-Print.php'),
            'vista_comun'   => $this->rutaModulo('../views/common-Print.php'),
            'testing'       => $this->rutaModulo('informeProductosTesting.php'),
            'instalador'    => $this->rutaModulo('../installer/bodegasInstaller.php'),
            'readme'        => $this->rutaModulo('../README.md'),
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
        $this->addCase($results, 'clase_testing_cargada', class_exists('informeProductosTesting'),
            'la clase informeProductosTesting no esta disponible');

        /*====================================*/
        // Herencias esperadas por el proyecto
        /*====================================*/
        $padreController = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padreController === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padreController.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('informeProductosTesting') === 'ControllerBase',
            'el testing no extiende ControllerBase');

        /*====================================*/
        // El nombre de la vista coincide con el controlador
        /*====================================*/
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'nombre_vista_propia',
            str_contains($codigo, "\$this->controllerName = '".self::CONTROLADOR."'"),
            'el controlador no declara controllerName = '.self::CONTROLADOR);

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
    /*                         CONTROLADOR: RUTAS Y PERMISOS                      */
    /******************************************************************************/
    /**
     * Verifica las firmas de `listAll`, `UpdateList`, `View` y `Print`, la ausencia
     * de wrappers por nivel (el informe no pagina por tipo de movimiento) y el
     * registro de las 4 rutas del instalador con nivel de solo lectura.
     *
     * @return array Resultado por caso
     */
    public function controladorRutasTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Firma de cada metodo publico
        /*====================================*/
        $this->firmaMetodo($results, 'listAll', 1, 1);
        $this->firmaMetodo($results, 'UpdateList', 1, 1);
        $this->firmaMetodo($results, 'View', 2, 2);
        $this->firmaMetodo($results, 'Print', 2, 2);

        /*====================================*/
        // Sin wrappers de nivel: las rutas apuntan directo al metodo
        /*====================================*/
        foreach (['listAll_1', 'UpdateList_1', 'View_1', 'Print_1'] as $metodo) {
            $this->addCase($results, 'sin_wrapper_'.$metodo, !method_exists(self::CONTROLADOR, $metodo),
                'existe un wrapper innecesario '.self::CONTROLADOR.'::'.$metodo.'()');
        }

        /*====================================*/
        // El instalador registra 4 rutas con nivel 1
        /*====================================*/
        $rutas = $this->rutasInstalador();
        $this->addCase($results, 'instalador_rutas', count($rutas) === 4,
            'el instalador declara '.count($rutas).' rutas de '.self::CONTROLADOR.' (esperadas 4)');

        $nivelOk   = true;
        $handlerOk = true;
        foreach ($rutas as $ruta) {
            if ($ruta['nivel'] !== 1) {
                $nivelOk = false;
            }
            if (!str_starts_with($ruta['handler'], self::CONTROLADOR.'->')) {
                $handlerOk = false;
            }
        }
        $this->addCase($results, 'instalador_nivel', $nivelOk,
            'hay rutas de '.self::CONTROLADOR.' sin idLevelLimit 1 (solo lectura)');
        $this->addCase($results, 'instalador_handler', $handlerOk,
            'hay rutas cuyo RutaController no apunta a '.self::CONTROLADOR);

        $arrAcciones = [
            ['metodo' => 1, 'ruta' => self::RUTA_WEB.'/listAll', 'handler' => 'listAll'],
            ['metodo' => 2, 'ruta' => self::RUTA_WEB.'/search',  'handler' => 'UpdateList'],
            ['metodo' => 1, 'ruta' => self::RUTA_WEB.'/view/@idProducto/@idBodegas', 'handler' => 'View'],
            ['metodo' => 1, 'ruta' => self::RUTA_WEB.'/print/@id', 'handler' => 'Print'],
        ];
        foreach ($arrAcciones as $accion) {
            $clave = $accion['metodo'].' '.$accion['ruta'];
            $ok    = isset($rutas[$clave]) && $rutas[$clave]['handler'] === self::CONTROLADOR.'->'.$accion['handler'];
            $this->addCase($results, 'ruta_'.md5($clave), $ok,
                'el instalador no registra '.$accion['ruta'].' apuntando a '.$accion['handler']);
        }

        /*====================================*/
        // Transaccion declarada en el instalador (nombre, ruta y alcance)
        /*====================================*/
        $instalador = $this->fuenteInstalador();
        $bloque = '';
        if (preg_match("/'Nombre'\s*=>\s*'Stock Productos'.*?\];/s", $instalador, $m) === 1) {
            $bloque = $m[0];
        }
        $this->addCase($results, 'transaccion_declarada',
            $bloque !== ''
            && preg_match("/'RutaWeb'\s*=>\s*'".preg_quote(self::RUTA_WEB, '/')."'/", $bloque) === 1
            && preg_match("/'RutaController'\s*=>\s*'".preg_quote(self::CONTROLADOR, '/')."'/", $bloque) === 1,
            'el instalador no declara la transaccion Stock Productos');
        $this->addCase($results, 'transaccion_solo_ver',
            preg_match("/'idLevelLimit'\s*=>\s*'1'/", $bloque) === 1,
            'la transaccion del informe no esta limitada a solo ver');
        $this->addCase($results, 'controlador_registrado',
            str_contains($instalador, "\$RutaController .= ',\"".self::CONTROLADOR."\"';"),
            'el controlador no entra en la lista RutaController() del instalador');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                            CONTROLADOR: VISTAS                             */
    /******************************************************************************/
    /**
     * Verifica el contrato de presentacion de las cuatro vistas y que el controlador
     * se mantenga en modo solo lectura (sin escritura ni transacciones).
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Contrato de presentacion
        /*====================================*/
        $this->addCase($results, 'vista_list',
            str_contains($codigo, "\$this->controllerName.'-List.php'"),
            'listAll() no instancia la vista List');
        $this->addCase($results, 'vista_update_list',
            str_contains($codigo, "\$this->controllerName.'-UpdateList.php'"),
            'UpdateList() no instancia la vista UpdateList');
        $this->addCase($results, 'vista_view',
            str_contains($codigo, "\$this->controllerName.'-View.php'"),
            'View() no instancia la vista View');
        $this->addCase($results, 'vista_print',
            str_contains($codigo, "\$this->controllerName.'-Print.php'"),
            'Print() no instancia la vista Print');

        $this->addCase($results, 'tipoview_pagina', str_contains($codigo, 'showVista(1,'),
            'listAll() no renderiza pagina completa con showVista(1)');
        $this->addCase($results, 'tipoview_modal', substr_count($codigo, 'showVista(2,') === 2,
            'UpdateList() y View() no renderizan modal con showVista(2)');
        $this->addCase($results, 'tipoview_impresion', str_contains($codigo, 'showVista(3,'),
            'Print() no renderiza la pantalla de impresion con showVista(3)');
        $this->addCase($results, 'rutavista', substr_count($codigo, 'returnRutaVista(') === 4,
            'los cuatro metodos no resuelven la ruta con returnRutaVista()');
        $this->addCase($results, 'errores_ruta', substr_count($codigo, 'showError(2,') === 3,
            'los metodos de modal no manejan la ruta de error con showError()');
        $this->addCase($results, 'usuario', substr_count($codigo, 'getUserData($f3)') >= 4,
            'el controlador no obtiene los datos del usuario');
        $this->addCase($results, 'permisos_informe',
            substr_count($codigo, 'getArrLevel($f3, $this->controllerName)') === 4,
            'el controlador no resuelve los permisos del informe en cada metodo');
        $this->addCase($results, 'metadatos_pagina',
            str_contains($codigo, "'PageTitle'") && str_contains($codigo, "'TableTitle'"),
            'listAll() no asigna PageTitle y TableTitle');
        $this->addCase($results, 'fallback_errores', substr_count($codigo, 'mergeResponses(') === 3,
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

        /*====================================*/
        // Identificadores descifrados antes de operar
        /*====================================*/
        $this->addCase($results, 'id_cifrado', substr_count($codigo, 'isValidDecrypted(') === 3,
            'View() y Print() no validan el identificador descifrado');
        $this->addCase($results, 'respuesta_400',
            substr_count($codigo, "'Registro inválido', 400") >= 3,
            'los metodos no rechazan identificadores invalidos con 400');

        /*====================================*/
        // Informe de solo lectura: sin escritura ni transacciones
        /*====================================*/
        $this->addCase($results, 'sin_escritura',
            !str_contains($codigo, 'Response::success(') && !str_contains($codigo, 'Base_insert('),
            'el informe declara operaciones de escritura');
        $this->addCase($results, 'sin_transaccion', !str_contains($codigo, 'Base_transaction'),
            'el informe abre transacciones de base de datos');
        $this->addCase($results, 'sin_archivos', !str_contains($codigo, 'delFiles'),
            'el informe declara borrado de archivos');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                  PERMISOS                                  */
    /******************************************************************************/
    /**
     * Verifica la sesion del usuario y el nivel de acceso de la transaccion del
     * informe de stock.
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
        // La transaccion existe con su nivel y ruta
        /*====================================*/
        $nivel = is_array($arrLevel) && $arrLevel !== [] ? ($arrLevel['LevelAccess'] ?? null) : null;
        $this->addCase($results, 'transaccion_presente', is_array($arrLevel) && $arrLevel !== [],
            'SESSION.arrLevel no contiene '.self::CONTROLADOR);
        $this->addCase($results, 'nivel_rango',
            $nivel === null || in_array((int)$nivel, self::NIVELES, true),
            'LevelAccess de '.self::CONTROLADOR.' quedo fuera del catalogo 1..4');
        $this->addCase($results, 'nivel_solo_lectura', $nivel === null || (int)$nivel === 1,
            'el informe de stock no esta publicado con nivel 1 (solo ver)');
        $this->addCase($results, 'ruta_transaccion',
            $nivel === null || ($arrLevel['RouteAccess'] ?? '') === self::RUTA_WEB,
            'RouteAccess de '.self::CONTROLADOR.' no coincide con '.self::RUTA_WEB);

        /*====================================*/
        // Registro de la ruta en SESSION.arrPermisos
        /*====================================*/
        $arrPermisos = $f3->get('SESSION.arrPermisos');
        $this->addCase($results, 'permisos_de_ruta_presentes', is_array($arrPermisos),
            'SESSION.arrPermisos no esta disponible');

        if (is_array($arrPermisos)) {
            $rutaOk = false;
            foreach ($arrPermisos as $permiso) {
                $ruta = (string)($permiso['RutaWeb'] ?? '');
                $ctrl = (string)($permiso['RutaController'] ?? '');
                if (str_starts_with($ruta, self::RUTA_WEB.'/') && str_starts_with($ctrl, self::CONTROLADOR.'->')) {
                    $rutaOk = true;
                    break;
                }
            }
            $this->addCase($results, 'ruta_registrada', $rutaOk,
                'la ruta '.self::RUTA_WEB.' no esta entre los permisos del usuario');
        }

        /*====================================*/
        // Rutas en el router de F3 (solo si el usuario las tiene registradas)
        /*====================================*/
        $arrRoutes = $f3->get('ROUTES');
        $this->addCase($results, 'router_disponible', is_array($arrRoutes),
            'F3 no expuso la variable ROUTES');

        $esquema = [
            'ruta_list'  => ['/'.self::RUTA_WEB.'/listAll',  'GET',  self::CONTROLADOR.'->listAll'],
            'ruta_search'=> ['/'.self::RUTA_WEB.'/search',   'POST', self::CONTROLADOR.'->UpdateList'],
            'ruta_view'  => ['/'.self::RUTA_WEB.'/view/@idProducto/@idBodegas', 'GET', self::CONTROLADOR.'->View'],
            'ruta_print' => ['/'.self::RUTA_WEB.'/print/@id', 'GET', self::CONTROLADOR.'->Print'],
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
    /*                        VISTA: FILTRO Y MATRIZ DE STOCK                    */
    /******************************************************************************/
    /**
     * Renderiza la pagina de filtro y la matriz de stock por bodega con datos
     * sinteticos y verifica los endpoints de busqueda y de detalle.
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
        $datos = $this->datosSinteticos();

        /*====================================*/
        // Pagina de filtro (listAll)
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php'),
            $datos, $BASE, $f3);

        $this->addCase($results, 'listado_render', $html !== '', 'la vista List no produjo HTML');
        $this->addCase($results, 'listado_contenedor', str_contains($html, 'id="listTableData"'),
            'falta el contenedor de la tabla');
        $this->addCase($results, 'listado_form_busqueda', str_contains($html, 'id="FormSearchData"'),
            'no se dibuja el formulario de filtro');
        $this->addCase($results, 'listado_selector_bodega', str_contains($html, 'id="Search_idBodegas"'),
            'no se dibuja el selector de bodegas');
        $this->addCase($results, 'listado_selector_producto', str_contains($html, 'id="Search_idProducto"'),
            'no se dibuja el selector de productos');
        $this->addCase($results, 'listado_boton_filtrar', str_contains($html, 'Filtrar'),
            'el formulario de filtro no ofrece enviar');
        $this->addCase($results, 'listado_endpoint_search',
            str_contains($html, '/search') && str_contains($html, "'POST'"),
            'el formulario de filtro no publica en la ruta de busqueda');
        $this->addCase($results, 'listado_sin_escritura',
            !str_contains($html, 'Crear Nuevo') && !str_contains($html, 'listTableDataDel('),
            'el informe ofrece acciones de escritura');
        $this->addCase($results, 'listado_endpoint_ver',
            str_contains($html, 'listTableDataView(') && str_contains($html, "/view/'"),
            'falta el endpoint de detalle por producto y bodega');

        /*====================================*/
        // Matriz de stock (UpdateList)
        /*====================================*/
        $rutaTabla = $this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php');
        $tabla = $this->renderizarVista($rutaTabla, $datos, $BASE, $f3);

        $this->addCase($results, 'matriz_render', $tabla !== '', 'la vista UpdateList no produjo HTML');
        $this->addCase($results, 'matriz_titulo', str_contains($tabla, 'Stock Actual'),
            'la matriz no pinta el titulo de la tabla');
        $this->addCase($results, 'matriz_columna_producto', str_contains($tabla, 'Producto'),
            'la matriz no declara la columna Producto');
        $this->addCase($results, 'matriz_columnas_bodega',
            str_contains($tabla, 'Bodega Central') && str_contains($tabla, 'Bodega Norte'),
            'la matriz no pinta una columna por bodega');
        $this->addCase($results, 'matriz_fila', str_contains($tabla, 'Producto Demo'),
            'la matriz no pinta el producto del stock');
        $this->addCase($results, 'matriz_ver',
            preg_match('/listTableDataView\(\'[A-Za-z0-9_-]{10,}\',\s*\'[A-Za-z0-9_-]{10,}\'\)/', $tabla) === 1,
            'la matriz no envia los identificadores cifrados al detalle');
        $this->addCase($results, 'matriz_volver',
            str_contains($tabla, '/listAll') && str_contains($tabla, 'Volver'),
            'la matriz no ofrece volver al filtro');
        $this->addCase($results, 'matriz_sin_escritura', !str_contains($tabla, 'listTableDataDel('),
            'la matriz ofrece borrar registros');

        /*====================================*/
        // Matriz sin datos
        /*====================================*/
        $vacio = $datos;
        $vacio['arrStocks'] = [];
        $tablaVacia = $this->renderizarVista($rutaTabla, $vacio, $BASE, $f3);
        $this->addCase($results, 'matriz_vacia',
            $tablaVacia !== '' && !str_contains($tablaVacia, 'listTableDataView('),
            'la matriz vacia dibuja botones de detalle sobre filas inexistentes');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                      VISTA: DETALLE Y PANTALLA DE IMPRESION                */
    /******************************************************************************/
    /**
     * Renderiza el modal de movimientos de un producto en una bodega y la
     * pantalla de impresion, con y sin datos.
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function vistaDetalleTest($f3){

        $results = [];
        $BASE    = (string)$f3->get('BASE');
        if (trim($BASE) === '') {
            $BASE = '/admin';
        }
        $datos = $this->datosSinteticos();

        /*====================================*/
        // Modal de detalle con movimientos
        /*====================================*/
        $datos['arrStocks']   = [$this->filaMovimiento(1), $this->filaMovimiento(2)];
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'),
            $datos, $BASE, $f3);

        $this->addCase($results, 'view_render', $html !== '', 'la vista View no produjo HTML');
        $this->addCase($results, 'view_titulo',
            str_contains($html, 'Ver Movimientos de Producto Demo en Bodega Central'),
            'la vista View no titula con el producto y la bodega');
        $this->addCase($results, 'view_columnas',
            str_contains($html, 'Doc Mov')
            && str_contains($html, 'Fecha')
            && str_contains($html, 'Hora')
            && str_contains($html, 'Tipo Movimiento')
            && str_contains($html, 'Ingreso')
            && str_contains($html, 'Egreso'),
            'la vista View no declara sus columnas');
        $this->addCase($results, 'view_filas',
            str_contains($html, 'nRef #11') && str_contains($html, 'Ingreso') && str_contains($html, 'Egreso'),
            'la vista View no pinta los movimientos');
        $this->addCase($results, 'view_fecha', str_contains($html, '02-01-2026'),
            'la vista View no normaliza la fecha con fechaEstandar()');
        $this->addCase($results, 'view_sin_escritura', !str_contains($html, 'listTableDataDel('),
            'la vista View ofrece borrar registros');

        /*====================================*/
        // Columna de documento asociado
        /*====================================*/
        $conDocumento = $datos;
        $conDocumento['UserData']['gestionDocumentosUsoBodega'] = 2;
        $conDocumento['arrStocks'] = [$this->filaMovimiento(1)];
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'),
            $conDocumento, $BASE, $f3);
        $this->addCase($results, 'view_documento_columna', str_contains($html, '<th scope="col">Documento</th>'),
            'con gestionDocumentosUsoBodega la vista no muestra la columna Documento');
        $this->addCase($results, 'view_documento_dato', str_contains($html, 'Factura 0001'),
            'la vista no pinta el documento asociado al movimiento');

        $sinDocumento = $datos;
        $sinDocumento['UserData']['gestionDocumentosUsoBodega'] = 1;
        $sinDocumento['arrStocks'] = [$this->filaMovimiento(1)];
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'),
            $sinDocumento, $BASE, $f3);
        $this->addCase($results, 'view_sin_documento_columna',
            !str_contains($html, '<th scope="col">Documento</th>'),
            'sin gestionDocumentosUsoBodega la vista muestra la columna Documento');

        /*====================================*/
        // Movimiento sin documento asociado (solo observaciones)
        /*====================================*/
        $filaObs = $this->filaMovimiento(1);
        $filaObs['idTipo'] = '';
        $conObs = $datos;
        $conObs['UserData']['gestionDocumentosUsoBodega'] = 2;
        $conObs['arrStocks'] = [$filaObs];
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'),
            $conObs, $BASE, $f3);
        $this->addCase($results, 'view_filas_observaciones', str_contains($html, 'Observaciones de prueba'),
            'la vista View no pinta las observaciones del movimiento');

        /*====================================*/
        // Modal sin movimientos
        /*====================================*/
        $vacia = $datos;
        $vacia['arrStocks'] = [];
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'),
            $vacia, $BASE, $f3);
        $this->addCase($results, 'view_vacia', str_contains($html, 'No se encontraron entradas'),
            'la vista View no muestra el mensaje de lista vacia');
        $this->addCase($results, 'view_pie_cierre', str_contains($html, 'Cerrar'),
            'la vista View no ofrece cerrar el modal');

        /*====================================*/
        // Pantalla de impresion
        /*====================================*/
        $rutaPrint = $this->rutaModulo('../views/'.self::CONTROLADOR.'-Print.php');
        $fuentePrint = $this->codigoFuente($rutaPrint);
        $this->addCase($results, 'print_usa_plantilla_comun',
            str_contains($fuentePrint, "require_once('common-Print.php')"),
            'Print.php no comparte la plantilla common-Print.php');

        $html = $this->renderizarVista($rutaPrint, $this->datosImpresion(), $BASE, $f3,
            $this->rutaModulo('../views'));

        $this->addCase($results, 'print_render', $html !== '', 'la pantalla de impresion no produjo HTML');
        $this->addCase($results, 'print_contenedor', str_contains($html, 'kanban-container'),
            'la impresion no monta el contenedor comun');
        $this->addCase($results, 'print_titulo', str_contains($html, 'Titulo de impresion'),
            'la impresion no pinta el titulo');
        $this->addCase($results, 'print_tareas', str_contains($html, 'Tarea de prueba'),
            'la impresion no pinta las tareas asociadas');
        $this->addCase($results, 'print_participantes', str_contains($html, 'Victor Reyes'),
            'la impresion no pinta los participantes');

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
        $this->addCase($results, 'hay_vistas', count($arrVistas) === 4,
            'se encontraron '.count($arrVistas).' vistas (esperadas 4)');

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
        // La plantilla comun tambien queda cubierta
        /*====================================*/
        $comun = $this->codigoFuente($this->rutaModulo('../views/common-Print.php'));
        $this->addCase($results, 'comun_sin_sesion', !str_contains($comun, '$_SESSION'),
            'common-Print.php accede a la sesion de forma directa');
        $this->addCase($results, 'comun_sin_sql',
            !preg_match('/\b(SELECT|INSERT|UPDATE|DELETE)\s+\w+\s+FROM\b/i', $comun),
            'common-Print.php contiene una consulta SQL embebida');

        /*====================================*/
        // Escape de los datos pintados en el detalle
        /*====================================*/
        $tabla = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'));
        $this->addCase($results, 'matriz_escape_identificador',
            str_contains($tabla, "encryptDecrypt('encrypt'"),
            'UpdateList.php no cifra los identificadores antes de pintarlos');

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
