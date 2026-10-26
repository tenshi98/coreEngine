<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class informeCotizacionTesting
 *
 * Pruebas del modulo `cotizaciones` - controlador `informeCotizacion` (informe de busqueda, solo lectura).
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
 * datos sinteticos. El detalle (`view`/`print`) lo atiende `cotizacionListado` bajo el
 * permiso de este informe.
 *
 * @see skills/testing/SKILL.md
 */
class informeCotizacionTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR      = 'informeCotizacion';
    private const RUTA_WEB         = 'cotizacionListado/informe/busqueda/listado';
    private const RUTA_WEB_LISTADO = 'cotizacionListado/informe/busqueda/listado/listAll';
    private const RUTA_WEB_SEARCH  = 'cotizacionListado/informe/busqueda/listado/search';
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
            'PageTitle'        => 'Busqueda Cotizaciones',
            'PageDescription'  => 'Busqueda Cotizaciones.',
            'PageAuthor'       => 'Pruebas',
            'PageKeywords'     => 'Pruebas',
            'TableTitle'       => 'Busqueda de Cotizaciones',
            'UserData'         => [
                'MainPathUrl'          => '/admin/public/upload/',
                'sistemaModalSubtitle' => 1,
                'sistemaModalCloseBTN' => 2,
                'UserType'             => 2,
            ],
            'UserAccess'       => ['LevelAccess' => $nivel, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_FormInputs'   => new UIFormInputs(),
            'Fnc_Codification' => new FunctionsSecurityCodification(),
            'Fnc_DataDate'     => new FunctionsDataDate(),
            'Fnc_DataNumbers'  => new FunctionsDataNumbers(),
            'arrEntidades'     => [['ID' => 1, 'Nombre' => 'Entidad Prueba SpA']],
            'arrList'          => [[
                'idCotizacion'         => 77,
                'Creacion_fecha'       => '2026-01-02',
                'ValorTotal'           => 119000,
                'idTipoEntidad'        => 2,
                'EntidadesNombre'      => '',
                'EntidadesApellido'    => '',
                'EntidadesRazonSocial' => 'Entidad Prueba SpA',
            ]],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica los archivos del controlador `informeCotizacion` y la convencion
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
            'vista_list'        => $this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php'),
            'vista_update_list' => $this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'),
            'testing'           => $this->rutaModulo('informeCotizacionTesting.php'),
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
        $this->addCase($results, 'clase_testing_cargada', class_exists('informeCotizacionTesting'),
            'la clase informeCotizacionTesting no esta disponible');
        $padre = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padre === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padre.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('informeCotizacionTesting') === 'ControllerBase',
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
     * Verifica el contrato de `listAll` y `UpdateList`, la busqueda por entidad y rango de
     * fechas, y que el informe es de solo lectura (sin metodos de escritura).
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Firma de cada metodo de vista (solo $f3)
        /*====================================*/
        foreach (['listAll', 'UpdateList'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 1, 1);
        }

        /*====================================*/
        // Contrato de presentacion
        /*====================================*/
        $this->addCase($results, 'vista_listAll', str_contains($codigo, "\$this->controllerName.'-List.php'"),
            'listAll() no instancia la vista -List');
        $this->addCase($results, 'vista_UpdateList', str_contains($codigo, "\$this->controllerName.'-UpdateList.php'"),
            'UpdateList() no instancia la vista -UpdateList');
        $this->addCase($results, 'tipoview_listAll', str_contains($codigo, 'showVista(1,'),
            'listAll() no usa showVista(1)');
        $this->addCase($results, 'tipoview_UpdateList', str_contains($codigo, 'showVista(2,'),
            'UpdateList() no usa showVista(2)');
        $this->addCase($results, 'rutavista_resuelta', substr_count($codigo, 'returnRutaVista(__DIR__') === 2,
            'las vistas no resuelven su ruta con returnRutaVista()');
        $this->addCase($results, 'usuario_en_vistas', substr_count($codigo, 'getUserData($f3)') === 2,
            'las vistas no obtienen los datos del usuario');
        $this->addCase($results, 'nivel_en_vistas', substr_count($codigo, 'getArrLevel($f3, $this->controllerName)') === 2,
            'las vistas no resuelven el nivel de acceso');
        $this->addCase($results, 'updatelist_maneja_fallo', str_contains($codigo, 'showError(2, $f3, $result)') && str_contains($codigo, 'mergeResponses('),
            'UpdateList() no maneja el fallo de consulta con showError()/mergeResponses()');
        $this->addCase($results, 'listall_maneja_fallo', substr_count($codigo, 'showError(1, $f3, $result)') === 1 && str_contains($codigo, 'mergeResponses([$arrEntidades])'),
            'listAll() debe comprobar el status de la consulta de entidades y usar showError(1)/mergeResponses()');
        $this->addCase($results, 'listado_titulo', str_contains($codigo, "'PageTitle'") && str_contains($codigo, "'TableTitle'"),
            'listAll() no asigna PageTitle y TableTitle');

        /*====================================*/
        // Catalogo de entidades activas para el filtro
        /*====================================*/
        $this->addCase($results, 'listado_catalogo_entidades',
            str_contains($codigo, "'table'   => 'entidades_listado'") && str_contains($codigo, "'where'   => 'idEstado = ?'"),
            'listAll() no carga el catalogo de entidades activas');
        $this->addCase($results, 'listado_limite', substr_count($codigo, 'ConfigAPP::APP["N_MaxItems"]') === 2,
            'las dos consultas deben acotar el numero de filas');

        /*====================================*/
        // Busqueda: columnas, validacion de fechas y WHERE preparado
        /*====================================*/
        $this->addCase($results, 'busqueda_campo_exacto', str_contains($codigo, "\$WhereData_int     = 'idEntidad';"),
            'UpdateList() no filtra por idEntidad');
        $this->addCase($results, 'busqueda_rango_fechas', str_contains($codigo, "'Creacion_fecha-F_Inicio-F_Termino'"),
            'UpdateList() no filtra por rango de fechas');
        $this->addCase($results, 'busqueda_valida_fechas', str_contains($codigo, 'searchValidateDates('),
            'UpdateList() no valida las fechas con searchValidateDates()');
        $this->addCase($results, 'busqueda_fechas_500', str_contains($codigo, 'Response::error($RespDataBetween, 500)'),
            'UpdateList() no responde 500 ante fechas invalidas');
        $this->addCase($results, 'busqueda_where_preparado', substr_count($codigo, '$this->searchWhere(') === 3,
            'UpdateList() no construye el WHERE con los tres tipos de searchWhere()');
        $this->addCase($results, 'busqueda_tabla', str_contains($codigo, "'table'   => 'cotizacion_listado'"),
            'UpdateList() no consulta cotizacion_listado');
        $this->addCase($results, 'busqueda_join_entidad', str_contains($codigo, 'LEFT JOIN entidades_listado ON entidades_listado.idEntidad = cotizacion_listado.idEntidad'),
            'UpdateList() no une la entidad de la cotizacion');
        $this->addCase($results, 'busqueda_params', str_contains($codigo, "'params'  => \$whereParams"),
            'UpdateList() no pasa los parametros preparados del WHERE');

        /*====================================*/
        // Solo lectura: sin escritura, transacciones ni respuestas de exito
        /*====================================*/
        foreach (['Insert', 'Update', 'Delete', 'delFiles'] as $metodo) {
            $this->addCase($results, 'solo_lectura_sin_'.$metodo, !method_exists(self::CONTROLADOR, $metodo),
                'el informe expone el metodo de escritura '.$metodo.'()');
        }
        $this->addCase($results, 'solo_lectura_sin_transaccion', !str_contains($codigo, 'Base_transaction') && !str_contains($codigo, 'Base_insert')
            && !str_contains($codigo, 'Base_update') && !str_contains($codigo, 'Base_delete'),
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
     * reales con el verbo y el nivel esperados: `listAll`/`search` propios y `view`/`print`
     * delegados en `cotizacionListado`.
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
            'listado'  => ['1 '.self::RUTA_WEB.'/listAll',      'informeCotizacion->listAll',    1],
            'busqueda' => ['2 '.self::RUTA_WEB.'/search',       'informeCotizacion->UpdateList', 1],
            'ver'      => ['1 '.self::RUTA_WEB.'/view/@id',     'cotizacionListado->View',       1],
            'imprimir' => ['1 '.self::RUTA_WEB.'/print/@id',    'cotizacionListado->Print',      1],
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
        $propias = array_filter($rutas, static function ($r) { return $r[2] === 'informeCotizacion'; });
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
        $instalador = $this->codigoFuente($this->rutaModulo('../installer/cotizacionInstaller.php'));
        $this->addCase($results, 'instalador_permiso_informe',
            str_contains($instalador, "'RutaWeb'        => '".self::RUTA_WEB."'")
            && str_contains($instalador, "'RutaController' => 'informeCotizacion'"),
            'el instalador no declara el permiso del informe con su ruta y controlador');
        $this->addCase($results, 'instalador_informe_solo_ver',
            (bool)preg_match("/'idLevelLimit'\s*=>\s*'1',\s*\/\/ Solo Ver\s*\n\s*'RutaWeb'\s*=>\s*'".preg_quote(self::RUTA_WEB, '/')."'/", $instalador),
            'el permiso del informe debe ser de nivel limite 1 (solo ver)');
        $this->addCase($results, 'instalador_desinstala_informe', str_contains($instalador, '"informeCotizacion"'),
            'RutaController() no lista informeCotizacion y dejaria rutas huerfanas al desinstalar');

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
     * Renderiza `informeCotizacion-List.php` e `informeCotizacion-UpdateList.php` con datos
     * sinteticos: formulario de busqueda, endpoint, tabla de resultados, nombre de la
     * entidad (persona natural y empresa), ID cifrado y matriz de niveles 1..4.
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
            str_contains($html, 'id="Search_idEntidad"') && str_contains($html, 'id="Search_F_Inicio"') && str_contains($html, 'id="Search_F_Termino"'),
            'el formulario no declara idEntidad, F_Inicio y F_Termino con prefijo Search_');
        $this->addCase($results, 'listado_catalogo', str_contains($html, 'Entidad Prueba SpA'),
            'el filtro no ofrece el catalogo de entidades');
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
                !str_contains($tabla, '/resumen/') && !str_contains($tabla, 'listTableDataDel('),
                'la tabla ofrece editar o borrar con nivel '.$nivel);
            $this->addCase($results, 'tabla_datos_nivel'.$nivel,
                str_contains($tabla, 'Entidad Prueba SpA') && str_contains($tabla, '119.000'),
                'la tabla no pinta la entidad sintetica con nivel '.$nivel);
        }
        $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos(1), $BASE, $f3);
        $this->addCase($results, 'tabla_titulo', str_contains($tabla, 'Busqueda de Cotizaciones'),
            'la tabla no muestra el titulo');
        $this->addCase($results, 'tabla_columnas',
            str_contains($tabla, '>Fecha<') && str_contains($tabla, '>Entidad<') && str_contains($tabla, '>Valor Total<'),
            'la tabla no declara las columnas Fecha, Entidad y Valor Total');
        $this->addCase($results, 'tabla_id_cifrado', !str_contains($tabla, "listTableDataView('77')"),
            'la tabla expone idCotizacion sin cifrar');
        $this->addCase($results, 'tabla_volver', str_contains($tabla, self::RUTA_WEB.'/listAll'),
            'la tabla no ofrece volver al buscador');

        /*====================================*/
        // Tabla vacia y persona natural
        /*====================================*/
        $vacio = $this->datosSinteticos(1);
        $vacio['arrList'] = [];
        $tablaVacia = $this->renderizarVista($rutaTabla, $vacio, $BASE, $f3);
        $this->addCase($results, 'tabla_vacia', $tablaVacia !== '' && str_contains($tablaVacia, '<tbody>') && !str_contains($tablaVacia, 'listTableDataView('),
            'la tabla sin resultados pinta filas o no se dibuja');

        $persona = $this->datosSinteticos(1);
        $persona['arrList'][0]['idTipoEntidad']     = 1;
        $persona['arrList'][0]['EntidadesNombre']   = 'Ana';
        $persona['arrList'][0]['EntidadesApellido'] = 'Prueba';
        $tablaPersona = $this->renderizarVista($rutaTabla, $persona, $BASE, $f3);
        $this->addCase($results, 'tabla_persona_natural', str_contains($tablaPersona, 'Prueba, Ana'),
            'la tabla no compone el nombre de una persona natural como "Apellido, Nombre"');
        // Tipo de entidad no contemplado: no debe dejar $Entidad indefinida ni arrastrar la fila anterior
        $otro = $this->datosSinteticos(1);
        $otro['arrList'][0]['idTipoEntidad']        = 3;
        $otro['arrList'][0]['EntidadesRazonSocial'] = 'No Debe Aparecer SpA';
        $tablaOtro = $this->renderizarVista($rutaTabla, $otro, $BASE, $f3);
        $this->addCase($results, 'tabla_tipo_entidad_desconocido',
            $tablaOtro !== '' && !str_contains($tablaOtro, 'No Debe Aparecer SpA') && !str_contains($tablaOtro, 'Prueba, Ana') && str_contains($tablaOtro, '<td>'.chr(10).'                                                -'),
            'un idTipoEntidad distinto de 1 y 2 debe mostrar "-" y no un valor indefinido o arrastrado');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos el cifrado de identificadores y la validacion de IDs que
     * consume el detalle del informe (`cotizacionListado->View/Print`).
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
            $cifrado      = $Codification->encryptDecrypt('encrypt', '77');
            $this->addCase($results, 'cifrado_genera_dato',
                ($cifrado['success'] ?? false) === true && !empty($cifrado['data']),
                'encryptDecrypt(encrypt) no produjo un valor cifrado');

            $descifrado = $Codification->encryptDecrypt('decrypt', (string)($cifrado['data'] ?? ''));
            $this->addCase($results, 'cifrado_ida_y_vuelta',
                ($descifrado['success'] ?? false) === true && (string)($descifrado['data'] ?? '') === '77',
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
        // Vistas involucradas
        /*====================================*/
        $arrVistas = glob($this->rutaModulo('../views/'.self::CONTROLADOR.'-*.php')) ?: [];
        $this->addCase($results, 'hay_vistas', count($arrVistas) === 2,
            'se esperaban 2 vistas del informe y se encontraron '.count($arrVistas));

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
        // Cifrado de los IDs y formato de datos en la tabla
        /*====================================*/
        $tabla = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'));
        $this->addCase($results, 'tabla_id_cifrado', str_contains($tabla, "encryptDecrypt('encrypt', \$crud['idCotizacion'])"),
            'UpdateList no cifra idCotizacion en la accion Ver');
        $this->addCase($results, 'tabla_fecha_estandar', str_contains($tabla, 'fechaEstandar('),
            'UpdateList no formatea la fecha con fechaEstandar()');
        $this->addCase($results, 'tabla_acceso_ver', str_contains($tabla, "['LevelAccess']>=1"),
            'UpdateList no condiciona la accion Ver al nivel de acceso');

        /*====================================*/
        // Controlador: SQL preparado y sin concatenacion de entrada
        /*====================================*/
        $controlador = $this->fuenteControlador();
        $this->addCase($results, 'controlador_sin_concat_sql', !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $controlador),
            'el controlador concatena superglobales en una cadena SQL');
        $this->addCase($results, 'controlador_parametros_preparados', preg_match_all("/'params'\s*=>/", $controlador) === 2,
            'las 2 consultas (catalogo y busqueda) deben declarar la clave params');
        $this->addCase($results, 'controlador_where_con_params', substr_count($controlador, "\$r['params']") === 3,
            'los tres searchWhere() deben acumular sus parametros preparados');
        $this->addCase($results, 'controlador_sin_superglobales',
            !str_contains($controlador, '$_POST') && !str_contains($controlador, '$_GET') && !str_contains($controlador, '$_REQUEST'),
            'el controlador lee superglobales directamente; la entrada debe pasar por searchWhere()');

        /************************************/
        // Retorno los datos
        return $results;

    }

}
