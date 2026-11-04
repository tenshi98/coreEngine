<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class vehiculosListadoTesting
 *
 * Pruebas del modulo `vehiculos` - controlador `vehiculosListado` (CRUD de vehiculos).
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
 * `Update()`, `Delete()`, `delFiles()`) terminan en `Response::success()`/`Response::error()`,
 * que ejecutan `exit`; por eso NO se invocan: se verifican por reflexion y por lectura del
 * fuente. Tampoco se ejecuta el instalador (`InstallModule()`/`UninstallModule()`): sus rutas
 * se leen del fuente. Solo se ejecuta logica pura (`dataCheck`, cifrado, `isValidDecrypted`).
 *
 * @see skills/testing/SKILL.md
 */
class vehiculosListadoTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR      = 'vehiculosListado';
    private const TABLA            = 'vehiculos_listado';
    private const RUTA_WEB         = 'administracion/vehiculos/listado';
    private const RUTA_WEB_LISTADO = 'administracion/vehiculos/listado/listAll';
    private const RUTA_WEB_SEARCH  = 'administracion/vehiculos/listado/search';
    // Niveles de permiso del catalogo (1=ver, 2=editar, 3=crear, 4=borrar)
    private const NIVELES          = [1, 2, 3, 4];
    // Verbos HTTP del instalador (idMetodo): 1=GET, 2=POST, 3=DELETE, 4=PUT
    private const VERBOS           = [1 => 'GET', 2 => 'POST', 3 => 'DELETE', 4 => 'PUT'];
    // Palabras que delatan codigo copiado de otro modulo (entidades)
    private const RESIDUOS_OTRO_MODULO = '/(TipoEntidad|ApellidoPat|ApellidoMat|RazonSocial|FNacimiento|RepLegal)/';

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
    // Fuente del controlador y del instalador
    /*******************************************************************/
    private function fuenteControlador(): string{
        return $this->codigoFuente($this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'));
    }

    private function fuenteInstalador(): string{
        return $this->codigoFuente($this->rutaModulo('../installer/vehiculosInstaller.php'));
    }

    /*******************************************************************/
    // Instancia sin ejecutar el constructor (logica pura, sin conexion a BD)
    /*******************************************************************/
    private function instanciaSinBD(string $clase){
        return (new ReflectionClass($clase))->newInstanceWithoutConstructor();
    }

    /*******************************************************************/
    // Lineas del instalador que declaran rutas de este controlador
    // Formato: [ 'idMetodo RutaWeb' => ['handler','nivel','ruta','metodo'] ]
    /*******************************************************************/
    private function rutasInstalador(): array{
        $rutas = [];
        foreach (preg_split('/\r\n|\r|\n/', $this->fuenteInstalador()) as $linea) {
            if (!str_contains($linea, "'".self::CONTROLADOR.'->')) {
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
    // Columnas de la tabla principal declaradas en el instalador
    /*******************************************************************/
    private function columnasTabla(string $tabla): array{
        $fuente = $this->fuenteInstalador();
        $patron = "/'table'\s*=>\s*'".preg_quote($tabla, '/')."'\s*,\s*'data'\s*=>\s*'([^']*)'/";
        if (preg_match($patron, $fuente, $m) !== 1) {
            return [];
        }
        preg_match_all('/`(\w+)`/', $m[1], $columnas);

        return $columnas[1] ?? [];
    }

    /*******************************************************************/
    // Renderiza una vista del modulo con datos sinteticos y devuelve el HTML.
    // - El CWD se mueve a admin/public porque las plantillas de los inputs se
    //   resuelven con rutas relativas ('../app/templates/...').
    // - Las vistas encadenan sub-vistas con require_once, que solo incluye la
    //   primera vez por proceso: se reescriben como include para que cada
    //   renderizado sea independiente del orden de ejecucion de las pruebas.
    /*******************************************************************/
    private function renderizarVista(string $rutaVista, array $data, string $BASE, $f3 = null): string{
        if (!is_file($rutaVista)) {
            return '';
        }
        $dirVistas = dirname($rutaVista).'/';
        $codigo    = (string)file_get_contents($rutaVista);
        $codigo    = preg_replace("/require_once\(\s*'([^']+)'\s*\)/", "include(\$dirVistas.'$1')", $codigo);

        $cwd = getcwd();
        @chdir($this->rutaModulo('../../../../public'));
        // Un warning de la plantilla (p. ej. clave inexistente) se convierte en excepcion para que
        // el caso se marque como fallido sin abortar el resto de la suite.
        set_error_handler(static function (int $nivel, string $mensaje, string $archivo, int $linea): bool {
            throw new ErrorException($mensaje, 0, $nivel, $archivo, $linea);
        });
        ob_start();
        try {
            eval('?>'.$codigo);
        } catch (Throwable $e) {
            ob_end_clean();
            return '';
        } finally {
            restore_error_handler();
            @chdir($cwd);
        }

        return (string)ob_get_clean();
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
    // Cuerpo (fuente) de un metodo del controlador, por reflexion
    /*******************************************************************/
    private function cuerpoMetodo(string $metodo): string{
        if (!method_exists(self::CONTROLADOR, $metodo)) {
            return '';
        }
        $reflector = new ReflectionMethod(self::CONTROLADOR, $metodo);
        $lineas    = preg_split('/\r\n|\r|\n/', $this->fuenteControlador());

        return implode("\n", array_slice($lineas, $reflector->getStartLine() - 1,
            $reflector->getEndLine() - $reflector->getStartLine() + 1));
    }

    /*******************************************************************/
    // Contrato de presentacion de un metodo (vista, errores y usuario)
    /*******************************************************************/
    private function contratoPresentacion(array &$results, string $metodo, string $vista, string $tipoVista){
        $cuerpo = $this->cuerpoMetodo($metodo);
        $this->addCase($results, 'vista_'.$metodo, str_contains($cuerpo, $vista),
            $metodo.'() no instancia la vista '.$vista);
        $this->addCase($results, 'tipoview_'.$metodo, str_contains($cuerpo, 'showVista('.$tipoVista.','),
            $metodo.'() no usa showVista('.$tipoVista.')');
        $this->addCase($results, 'rutavista_'.$metodo, str_contains($cuerpo, 'returnRutaVista('),
            $metodo.'() no resuelve la ruta de la vista');
        $this->addCase($results, 'rutaruta_'.$metodo, str_contains($cuerpo, '$this->controllerName'),
            $metodo.'() no referencia $this->controllerName');
        $this->addCase($results, 'errores_'.$metodo, str_contains($cuerpo, 'showError('),
            $metodo.'() no maneja la ruta de error con showError()');
        $this->addCase($results, 'usuario_'.$metodo, str_contains($cuerpo, 'getUserData($f3)'),
            $metodo.'() no obtiene los datos del usuario');
    }

    /*******************************************************************/
    // Datos sinteticos usados por las vistas del modulo
    /*******************************************************************/
    private function datosSinteticos(int $nivel): array{
        return [
            'PageTitle'       => 'Listado Vehiculos',
            'PageDescription' => 'Listado Vehiculos.',
            'PageAuthor'      => 'Pruebas',
            'PageKeywords'    => 'Pruebas',
            'TableTitle'      => 'Listado de Vehiculos',
            'UserData'        => [
                'MainPathUrl'          => '/admin/public/upload/',
                'sistemaModalSubtitle' => 1,
                'sistemaModalCloseBTN' => 2,
                'Config_motorMap'      => 2,
                'UserType'             => 2,
            ],
            'UserAccess'      => ['LevelAccess' => $nivel, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_FormInputs'     => new UIFormInputs(),
            'Fnc_Codification'   => new FunctionsSecurityCodification(),
            'Fnc_DataDate'       => new FunctionsDataDate(),
            'Fnc_DataNumbers'    => new FunctionsDataNumbers(),
            'Fnc_WidgetsCommon'  => new UIWidgetsCommon(),
            'Fnc_WidgetsViews'   => new UIWidgetsViews(),
            'Fnc_WidgetsMaps'    => new UIWidgetsMaps(),
            'Fnc_ServerServer'   => new FunctionsServerServer(),
            'arrList'         => [[
                'idVehiculo'  => 1,
                'Nombre'      => 'Camion Prueba',
                'Marca'       => 'MarcaPrueba',
                'Modelo'      => 'ModeloPrueba',
                'Tipo'        => 'Camion',
                'Estado'      => 'Activo',
                'EstadoColor' => 'success',
            ]],
            'arrTipo'         => [['ID' => 1, 'Nombre' => 'Camion']],
            'arrTipoCarga'    => [['ID' => 1, 'Nombre' => 'General']],
            'arrEstado'       => [['ID' => 1, 'Nombre' => 'Activo', 'Color' => 'success']],
            'arrDocumentos'   => [[
                'idDocumentos'     => 1,
                'Nombre'           => 'Permiso de Circulacion',
                'Tipo'             => 'Permiso',
                'FechaCreacion'    => '2026-01-02',
                'FechaVencimiento' => '2027-01-02',
            ]],
            'arrObservaciones' => [[
                'FechaCreacion' => '2026-01-02',
                'Usuario'       => 'Tester',
                'Observacion'   => 'Observacion de prueba',
                'FechaEdicion'  => '',
            ]],
            'rowData'         => [
                'idVehiculo'        => 1,
                'Nombre'            => 'Camion Prueba',
                'Marca'             => 'MarcaPrueba',
                'Modelo'            => 'ModeloPrueba',
                'Num_serie'         => 'SERIE0001',
                'AnoFab'            => 2020,
                'Patente'           => 'AAAA11',
                'CapacidadPersonas' => 2,
                'Capacidad'         => 1000,
                'MCubicos'          => 10,
                'Direccion_img'     => '',
                'Latitud'           => 0,
                'Longitud'          => 0,
                'Velocidad'         => 0,
                'LastUpdateFecha'   => '2026-01-02',
                'LastUpdateHora'    => '10:00:00',
                'idTipo'            => 1,
                'idEstado'          => 1,
                'idTipoCarga'       => 1,
                'Tipo'              => 'Camion',
                'TipoCarga'         => 'General',
                'Estado'            => 'Activo',
                'EstadoColor'       => 'success',
            ],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica la estructura de carpetas y archivos del modulo `vehiculos` (controlador
     * `vehiculosListado`) y la convencion "clase = nombre de archivo" exigida por el
     * AUTOLOAD de Fat-Free.
     *
     * @return array Resultado por caso
     */
    public function estructuraModuloTest(){

        $results = [];

        /*====================================*/
        // Archivos obligatorios del controlador
        /*====================================*/
        $arrArchivos = [
            'controlador'       => $this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'),
            'instalador'        => $this->rutaModulo('../installer/vehiculosInstaller.php'),
            'vista_list'        => $this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php'),
            'vista_update_list' => $this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'),
            'vista_view'        => $this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'),
            'vista_resumen'     => $this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen.php'),
            'vista_resumen_upd' => $this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen-Update.php'),
            'vista_exportar'    => $this->rutaModulo('../views/'.self::CONTROLADOR.'-Exportar.php'),
            'vista_form_new'    => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formNew.php'),
            'vista_form_search' => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formSearch.php'),
            'testing'           => $this->rutaModulo('vehiculosListadoTesting.php'),
            'readme'            => $this->rutaModulo('../README.md'),
        ];
        foreach ($arrArchivos as $nombre => $ruta) {
            $this->addCase($results, 'archivo_'.$nombre, is_file($ruta), 'no se encuentra '.$ruta);
        }

        /*====================================*/
        // Proteccion de acceso directo
        /*====================================*/
        $arrHtaccess = [
            'controller' => $this->rutaModulo('../controller/.htaccess'),
            'installer'  => $this->rutaModulo('../installer/.htaccess'),
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
        $this->addCase($results, 'clase_testing_cargada', class_exists('vehiculosListadoTesting'),
            'la clase vehiculosListadoTesting no esta disponible');

        /*====================================*/
        // Herencias esperadas por el proyecto
        /*====================================*/
        $padreController = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padreController === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padreController.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('vehiculosListadoTesting') === 'ControllerBase',
            'el testing no extiende ControllerBase');

        /*====================================*/
        // Constructor del controlador: instancias y conexion MySQL_1
        /*====================================*/
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'constructor_conexion', str_contains($codigo, 'ConfigDataBase::MySQL_1'),
            'el constructor no usa la conexion ConfigDataBase::MySQL_1');
        $this->addCase($results, 'constructor_padre', str_contains($codigo, 'parent::__construct($DB_conn_1, $queryBuilder, $checkData)'),
            'el constructor no llama a parent::__construct con la conexion, el QueryBuilder y el CheckData');
        $this->addCase($results, 'constructor_nombre', str_contains($codigo, "\$this->controllerName = '".self::CONTROLADOR."'"),
            'controllerName no coincide con el nombre de la clase');

        /*====================================*/
        // Soporte de deteccion de tipos de archivo (subida de imagenes)
        /*====================================*/
        $this->addCase($results, 'entorno_finfo', $this->finfoDisponible(),
            'la extension finfo no esta disponible para validar tipos de archivo');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                            CONTROLADOR: VISTAS                             */
    /******************************************************************************/
    /**
     * Verifica el contrato de los metodos que devuelven HTML (`listAll`, `UpdateList`,
     * `export`, `View`, `Resumen`, `ResumenUpdate`): existencia, visibilidad, firma y uso de
     * `showVista()`/`returnRutaVista()`/`getUserData()`.
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
            'export'        => [1, 1],
            'View'          => [2, 2],
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
        $this->contratoPresentacion($results, 'export', "\$this->controllerName.'-Exportar.php'", '1');
        $this->contratoPresentacion($results, 'View', "\$this->controllerName.'-View.php'", '2');
        $this->contratoPresentacion($results, 'Resumen', "\$this->controllerName.'-Resumen.php'", '1');
        $this->contratoPresentacion($results, 'ResumenUpdate', "\$this->controllerName.'-Resumen-Update.php'", '2');

        /*====================================*/
        // Metadatos y consultas de la pagina de listado
        /*====================================*/
        $listado = $this->cuerpoMetodo('listAll');
        $this->addCase($results, 'listado_titulo', str_contains($listado, "'PageTitle'"),
            'listAll() no asigna PageTitle');
        $this->addCase($results, 'listado_tabla', str_contains($listado, "'TableTitle'"),
            'listAll() no asigna TableTitle');
        $this->addCase($results, 'listado_permiso_usuario', str_contains($listado, 'getArrLevel($f3'),
            'listAll() no resuelve el nivel de acceso del usuario');
        $this->addCase($results, 'listado_fallback_errores', str_contains($listado, 'mergeResponses('),
            'listAll() no agrupa las respuestas fallidas con mergeResponses()');
        foreach (['core_tipos_vehiculos', 'core_tipos_vehiculos_carga', 'core_estados'] as $catalogo) {
            $this->addCase($results, 'listado_consulta_'.$catalogo, str_contains($listado, "'".$catalogo."'"),
                'listAll() no consulta el catalogo '.$catalogo);
        }
        $this->addCase($results, 'listado_limite', str_contains($listado, 'N_MaxItems'),
            'listAll() no limita las filas con ConfigAPP::APP["N_MaxItems"]');

        /*====================================*/
        // Busqueda: UpdateList filtra con parametros enlazados
        /*====================================*/
        $busqueda = $this->cuerpoMetodo('UpdateList');
        $this->addCase($results, 'busqueda_where_enlazado',
            str_contains($busqueda, 'searchWhere(') && str_contains($busqueda, "'params'  => \$whereParams"),
            'UpdateList() no filtra con searchWhere() y parametros enlazados');
        $this->addCase($results, 'busqueda_campos_exactos', str_contains($busqueda, "'idTipo,idTipoCarga,idEstado'"),
            'UpdateList() no declara los filtros exactos idTipo,idTipoCarga,idEstado');
        $this->addCase($results, 'busqueda_campos_texto', str_contains($busqueda, "'Nombre,Marca,Modelo'"),
            'UpdateList() no declara los filtros por texto Nombre,Marca,Modelo');

        /*====================================*/
        // Detalle: View/Resumen/ResumenUpdate descifran y validan el ID
        /*====================================*/
        foreach (['View', 'Resumen', 'ResumenUpdate'] as $metodo) {
            $cuerpo = $this->cuerpoMetodo($metodo);
            $this->addCase($results, 'id_descifrado_'.$metodo,
                str_contains($cuerpo, "encryptDecrypt('decrypt', \$params['id'])"),
                $metodo.'() no descifra $params[\'id\']');
            $this->addCase($results, 'id_validado_'.$metodo,
                str_contains($cuerpo, "isValidDecrypted(\$VehiculoID, 'id')") && str_contains($cuerpo, "Response::error('Registro inv"),
                $metodo.'() no rechaza con 400 un ID invalido');
            $this->addCase($results, 'id_enlazado_'.$metodo,
                str_contains($cuerpo, "'params'  => [\$VehiculoID['data']]"),
                $metodo.'() no enlaza el ID como parametro de la consulta');
        }

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
     * transacciones y respuestas al cliente leyendo el fuente de cada metodo.
     *
     * @return array Resultado por caso
     */
    public function controladorEscrituraTest(){

        $results = [];

        /*====================================*/
        // Firma de cada metodo de escritura
        /*====================================*/
        $arrMetodos = [
            'Insert'   => [0, 0],
            'Update'   => [0, 0],
            'Delete'   => [0, 0],
            'delFiles' => [0, 0],
        ];
        foreach ($arrMetodos as $metodo => $parametros) {
            $this->firmaMetodo($results, $metodo, $parametros[0], $parametros[1]);
        }

        /*====================================*/
        // Insert: usa el chequeo propio y responde con el ID cifrado
        /*====================================*/
        $insert = $this->cuerpoMetodo('Insert');
        $this->addCase($results, 'insert_datacheck', str_contains($insert, '$this->dataCheck($_POST)'),
            'Insert() no construye el DataCheck con dataCheck($_POST)');
        $this->addCase($results, 'insert_tabla', str_contains($insert, "'table'     => '".self::TABLA."'"),
            'Insert() no apunta a la tabla '.self::TABLA);
        $this->addCase($results, 'insert_requeridos', str_contains($insert, "'required'  => 'idEstado,idTipo,Nombre'"),
            'Insert() no exige idEstado,idTipo,Nombre');
        $this->addCase($results, 'insert_unicos', str_contains($insert, "'unique'    => 'Nombre,Num_serie,Patente'"),
            'Insert() no controla la unicidad de Nombre,Num_serie,Patente');
        $this->addCase($results, 'insert_base', str_contains($insert, 'Base_insert('),
            'Insert() no invoca Base_insert()');
        $this->addCase($results, 'insert_falla_controlada',
            str_contains($insert, "\$Response['status'] === false") && str_contains($insert, 'failOperation('),
            'Insert() no gestiona la respuesta fallida con failOperation()');
        $this->addCase($results, 'insert_respuesta', str_contains($insert, 'Response::success('),
            'Insert() no responde con Response::success()');
        $this->addCase($results, 'insert_id_cifrado', str_contains($insert, "encryptDecrypt('encrypt'"),
            'Insert() no cifra el ID devuelto');

        /*====================================*/
        // Update: verifica el verbo y gestiona archivos
        /*====================================*/
        $update = $this->cuerpoMetodo('Update');
        $this->addCase($results, 'update_verbo_post', str_contains($update, "REQUEST_METHOD'] !== 'POST'") && str_contains($update, '405'),
            'Update() no rechaza con 405 un verbo distinto de POST');
        $this->addCase($results, 'update_datacheck', str_contains($update, '$this->dataCheck($_POST)'),
            'Update() no construye el DataCheck con dataCheck($_POST)');
        $this->addCase($results, 'update_donde', str_contains($update, "'where'     => 'idVehiculo'"),
            'Update() no delimita la escritura por idVehiculo');
        $this->addCase($results, 'update_requeridos', str_contains($update, "'required'  => 'idVehiculo,idEstado,idTipo,Nombre'"),
            'Update() no exige idVehiculo,idEstado,idTipo,Nombre');
        $this->addCase($results, 'update_archivos',
            str_contains($update, "'files'") && str_contains($update, 'Direccion_img'),
            'Update() no declara el manejo del archivo Direccion_img');
        $this->addCase($results, 'update_valida_imagen',
            str_contains($update, "'ValidarTipo'   => 'image'") && str_contains($update, "'ValidarPeso'"),
            'Update() no valida el tipo y el peso de la imagen');
        $this->addCase($results, 'update_base', str_contains($update, 'Base_update('),
            'Update() no invoca Base_update()');
        $this->addCase($results, 'update_respuesta', str_contains($update, 'Response::success(') && str_contains($update, 'failOperation('),
            'Update() no responde con Response::success() ni gestiona la falla con failOperation()');

        /*====================================*/
        // Delete: verbo DELETE, transaccion y borrado en cascada
        /*====================================*/
        $delete = $this->cuerpoMetodo('Delete');
        $this->addCase($results, 'delete_verbo', str_contains($delete, "REQUEST_METHOD'] !== 'DELETE'") && str_contains($delete, '405'),
            'Delete() no rechaza con 405 un verbo distinto de DELETE');
        $this->addCase($results, 'delete_transaccion',
            str_contains($delete, 'Base_transactionBegin()')
            && str_contains($delete, 'Base_transactionCommit()')
            && str_contains($delete, 'failOperation('),
            'Delete() debe abrir y confirmar la transaccion y usar failOperation (rollback gestionado)');
        $this->addCase($results, 'delete_orden_transaccion',
            strpos($delete, 'Base_transactionBegin()') < strpos($delete, 'Base_delete(')
            && strpos($delete, 'Base_delete(') < strpos($delete, 'Base_transactionCommit()'),
            'Delete() no abre la transaccion antes de borrar ni la confirma al final');
        $this->addCase($results, 'delete_donde', str_contains($delete, "'where'       => 'idVehiculo'"),
            'Delete() no delimita el borrado por idVehiculo');
        $this->addCase($results, 'delete_cascada_documentos', str_contains($delete, 'vehiculos_listado_documentos'),
            'Delete() no elimina los documentos relacionados');
        $this->addCase($results, 'delete_cascada_observaciones', str_contains($delete, 'vehiculos_listado_observaciones'),
            'Delete() no elimina las observaciones relacionadas');
        $this->addCase($results, 'delete_archivos_documentos', str_contains($delete, "'files' => 'NombreArchivo'"),
            'Delete() no elimina el archivo fisico de los documentos');
        $this->addCase($results, 'delete_imagen', str_contains($delete, "'files'       => 'Direccion_img'"),
            'Delete() no elimina la imagen del vehiculo');
        $this->addCase($results, 'delete_respuesta', str_contains($delete, 'Response::success('),
            'Delete() no responde con Response::success()');

        /*====================================*/
        // delFiles: verbo PUT y limpieza del archivo
        /*====================================*/
        $delFiles = $this->cuerpoMetodo('delFiles');
        $this->addCase($results, 'delfiles_verbo', str_contains($delFiles, "REQUEST_METHOD'] !== 'PUT'") && str_contains($delFiles, '405'),
            'delFiles() no rechaza con 405 un verbo distinto de PUT');
        $this->addCase($results, 'delfiles_archivo', str_contains($delFiles, 'Base_delFiles('),
            'delFiles() no invoca Base_delFiles()');
        $this->addCase($results, 'delfiles_campo', str_contains($delFiles, "'files'       => 'Direccion_img'"),
            'delFiles() no limpia el campo Direccion_img');
        $this->addCase($results, 'delfiles_respuesta', str_contains($delFiles, 'Response::success(') && str_contains($delFiles, 'failOperation('),
            'delFiles() no responde con Response::success() ni gestiona la falla con failOperation()');

        /*====================================*/
        // Ningun metodo de escritura arma SQL a mano
        /*====================================*/
        foreach (['Insert' => $insert, 'Update' => $update, 'Delete' => $delete, 'delFiles' => $delFiles] as $metodo => $cuerpo) {
            $this->addCase($results, 'sin_sql_manual_'.$metodo,
                !preg_match('/\b(INSERT\s+INTO|UPDATE\s+\w+\s+SET|DELETE\s+FROM)\b/i', $cuerpo),
                $metodo.'() contiene SQL escrito a mano en vez de usar los Base_*');
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                      CONTROLADOR: RUTAS DEL INSTALADOR                     */
    /******************************************************************************/
    /**
     * Verifica, leyendo el fuente del instalador (sin ejecutarlo), que las rutas del
     * controlador `vehiculosListado` esten registradas con el verbo, el nivel de acceso
     * y el handler correctos, y que cada handler exista en el controlador.
     *
     * @return array Resultado por caso
     */
    public function controladorRutasTest(){

        $results = [];
        $rutas   = $this->rutasInstalador();

        /*====================================*/
        // El instalador declara las 11 rutas del controlador
        /*====================================*/
        $this->addCase($results, 'instalador_rutas', count($rutas) === 11,
            'el instalador declara '.count($rutas).' rutas de '.self::CONTROLADOR.' (esperadas 11)');

        /*====================================*/
        // Nivel, verbo y handler validos en cada ruta
        /*====================================*/
        $nivelOk   = true;
        $handlerOk = true;
        $metodoOk  = true;
        $prefijoOk = true;
        foreach ($rutas as $ruta) {
            if (!in_array($ruta['nivel'], self::NIVELES, true)) {
                $nivelOk = false;
            }
            $partes = explode('->', $ruta['handler']);
            if ($partes[0] !== self::CONTROLADOR || !method_exists(self::CONTROLADOR, $partes[1] ?? '')) {
                $handlerOk = false;
            }
            if (!isset(self::VERBOS[$ruta['metodo']])) {
                $metodoOk = false;
            }
            if (!str_starts_with($ruta['ruta'], self::RUTA_WEB)) {
                $prefijoOk = false;
            }
        }
        $this->addCase($results, 'instalador_nivel', $nivelOk,
            'hay rutas de '.self::CONTROLADOR.' con idLevelLimit fuera del catalogo 1..4');
        $this->addCase($results, 'instalador_handler', $handlerOk,
            'hay rutas cuyo RutaController no apunta a un metodo existente de '.self::CONTROLADOR);
        $this->addCase($results, 'instalador_verbo', $metodoOk,
            'hay rutas con idMetodo fuera de 1..4 (GET/POST/DELETE/PUT)');
        $this->addCase($results, 'instalador_prefijo', $prefijoOk,
            'hay rutas que no cuelgan de '.self::RUTA_WEB);

        /*====================================*/
        // Matriz esperada: verbo + ruta + handler + nivel minimo
        /*====================================*/
        $arrAcciones = [
            ['metodo' => 1, 'ruta' => self::RUTA_WEB.'/listAll',             'handler' => 'listAll',       'nivel' => 1],
            ['metodo' => 2, 'ruta' => self::RUTA_WEB.'/search',              'handler' => 'UpdateList',    'nivel' => 1],
            ['metodo' => 1, 'ruta' => self::RUTA_WEB.'/updateList',          'handler' => 'UpdateList',    'nivel' => 2],
            ['metodo' => 1, 'ruta' => self::RUTA_WEB.'/exportar',            'handler' => 'export',        'nivel' => 3],
            ['metodo' => 1, 'ruta' => self::RUTA_WEB.'/view/@id',            'handler' => 'View',          'nivel' => 1],
            ['metodo' => 1, 'ruta' => self::RUTA_WEB.'/resumen/@id',         'handler' => 'Resumen',       'nivel' => 2],
            ['metodo' => 1, 'ruta' => self::RUTA_WEB.'/resumenUpdate/@id',   'handler' => 'ResumenUpdate', 'nivel' => 2],
            ['metodo' => 2, 'ruta' => self::RUTA_WEB,                        'handler' => 'Insert',        'nivel' => 3],
            ['metodo' => 2, 'ruta' => self::RUTA_WEB.'/update',              'handler' => 'Update',        'nivel' => 2],
            ['metodo' => 4, 'ruta' => self::RUTA_WEB.'/delFiles',            'handler' => 'delFiles',      'nivel' => 2],
            ['metodo' => 3, 'ruta' => self::RUTA_WEB,                        'handler' => 'Delete',        'nivel' => 4],
        ];
        foreach ($arrAcciones as $accion) {
            $clave = $accion['metodo'].' '.$accion['ruta'];
            $caso  = 'ruta_'.strtolower($accion['handler']).'_'.self::VERBOS[$accion['metodo']];
            if (!isset($rutas[$clave])) {
                $this->addCase($results, $caso, false, 'el instalador no declara '.self::VERBOS[$accion['metodo']].' '.$accion['ruta']);
                continue;
            }
            $this->addCase($results, $caso, strcasecmp($rutas[$clave]['handler'], self::CONTROLADOR.'->'.$accion['handler']) === 0,
                'la ruta '.$accion['ruta'].' apunta a '.$rutas[$clave]['handler'].' en vez de '.self::CONTROLADOR.'->'.$accion['handler']);
            $this->addCase($results, $caso.'_nivel', $rutas[$clave]['nivel'] === $accion['nivel'],
                'la ruta '.$accion['ruta'].' exige nivel '.$rutas[$clave]['nivel'].' en vez de '.$accion['nivel']);
        }

        /*====================================*/
        // El verbo registrado coincide con el que valida el controlador
        // (idMetodo: 2=POST, 3=DELETE, 4=PUT)
        /*====================================*/
        $arrVerbos = ['Update' => 'POST', 'Delete' => 'DELETE', 'delFiles' => 'PUT'];
        foreach ($arrVerbos as $metodo => $verbo) {
            $registrado = '';
            foreach ($rutas as $ruta) {
                if (strcasecmp($ruta['handler'], self::CONTROLADOR.'->'.$metodo) === 0) {
                    $registrado = self::VERBOS[$ruta['metodo']] ?? '';
                }
            }
            $this->addCase($results, 'verbo_coincide_'.$metodo, $registrado === $verbo
                && str_contains($this->cuerpoMetodo($metodo), "REQUEST_METHOD'] !== '".$verbo."'"),
                $metodo.'() valida '.$verbo.' pero el instalador lo registra como '.($registrado ?: 'inexistente'));
        }

        /*====================================*/
        // Permisos y registro del modulo
        /*====================================*/
        $instalador = $this->fuenteInstalador();
        $this->addCase($results, 'instalador_permiso_ruta', str_contains($instalador, "'RutaWeb'        => '".self::RUTA_WEB."'"),
            'el permiso principal no apunta a '.self::RUTA_WEB);
        $this->addCase($results, 'instalador_permiso_controlador', str_contains($instalador, "'RutaController' => '".self::CONTROLADOR."'"),
            'el permiso principal no apunta al controlador '.self::CONTROLADOR);
        $this->addCase($results, 'instalador_permiso_nivel_4', str_contains($instalador, "'idLevelLimit'   => '4'"),
            'el permiso principal no ofrece los 4 niveles (ver/editar/crear/borrar)');
        $this->addCase($results, 'instalador_controlador_registrado', str_contains($instalador, "'\"".self::CONTROLADOR."\"'"),
            'RutaController() del instalador no lista '.self::CONTROLADOR.' (no se desinstalaria)');

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
        /*====================================*/
        $arrPermisos = $f3->get('SESSION.arrPermisos');
        $this->addCase($results, 'permisos_de_ruta_presentes', is_array($arrPermisos),
            'SESSION.arrPermisos no esta disponible');

        if (is_array($arrPermisos)) {
            $rutaOk = false;
            foreach ($arrPermisos as $permiso) {
                if (isset($permiso['RutaWeb']) && $permiso['RutaWeb'] === self::RUTA_WEB_LISTADO) {
                    $rutaOk = true;
                    break;
                }
            }
            // Un usuario sin permiso sobre el modulo no registra la ruta: no es un fallo del modulo
            $this->addCase($results, 'ruta_registrada', $rutaOk || $nivel === null,
                'la ruta '.self::RUTA_WEB_LISTADO.' no esta entre los permisos del usuario');
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
        ];
        foreach ($esquema as $caso => $rutaEsperada) {
            [$ruta, $verbo, $handlerEsperado] = $rutaEsperada;
            if (!is_array($arrRoutes) || !isset($arrRoutes[$ruta])) {
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
     * Renderiza `vehiculosListado-List.php` y `vehiculosListado-UpdateList.php` con datos
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
        $this->addCase($results, 'listado_tabla_incluida', str_contains($html, 'Camion Prueba'),
            'el listado no incluye la tabla con los registros');
        $this->addCase($results, 'listado_form_busqueda', str_contains($html, 'id="FormSearchData"'),
            'no se dibuja el formulario de busqueda');
        $this->addCase($results, 'listado_form_nuevo', str_contains($html, 'id="newFormModal"'),
            'no se dibuja el modal de alta');
        $this->addCase($results, 'listado_boton_filtrar', str_contains($html, 'data-bs-target="#formSearch"'),
            'falta el boton que despliega el filtro');
        $this->addCase($results, 'listado_sin_crear_nivel1', !str_contains($html, 'Crear Nuevo</button>'),
            'con nivel 1 el listado ofrece crear registros');
        $this->addCase($results, 'listado_sin_exportar_nivel1', !str_contains($html, '/exportar/'),
            'con nivel 1 el listado ofrece exportar datos');
        $this->addCase($results, 'listado_endpoint_ver', str_contains($html, "/view/'"),
            'falta el endpoint de detalle');
        $this->addCase($results, 'listado_endpoint_actualizar', str_contains($html, '/updateList'),
            'falta el endpoint de refresco de tabla');
        $this->addCase($results, 'listado_borrado_viaje', str_contains($html, "Metodo      = 'DELETE'"),
            'la accion de borrar no viaja con el verbo DELETE');
        $this->addCase($results, 'listado_borrado_clave', str_contains($html, '"idVehiculo": ID'),
            'la accion de borrar no envia idVehiculo');

        /*====================================*/
        // El boton de alta y la exportacion aparecen con nivel >= 3
        /*====================================*/
        $htmlNivel3 = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php'),
            $this->datosSinteticos(3), $BASE, $f3);
        $this->addCase($results, 'listado_crear_nivel3', str_contains($htmlNivel3, 'Crear Nuevo</button>'),
            'con nivel 3 no se muestra el boton Crear Nuevo');
        $this->addCase($results, 'listado_modal_nivel3', str_contains($htmlNivel3, 'data-bs-target="#newFormModal"'),
            'el boton de alta no apunta al modal #newFormModal');
        $this->addCase($results, 'listado_exportar_nivel3', str_contains($htmlNivel3, self::RUTA_WEB.'/exportar/'),
            'con nivel 3 no se ofrece el enlace de exportacion');

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
                str_contains($tabla, 'Camion Prueba') && str_contains($tabla, 'MarcaPrueba') && str_contains($tabla, 'ModeloPrueba'),
                'la tabla no pinta el registro sintetico con nivel '.$nivel);
        }

        /*====================================*/
        // La tabla vacia no rompe la vista
        /*====================================*/
        $vacio = $this->datosSinteticos(4);
        $vacio['arrList'] = [];
        $tabla = $this->renderizarVista($rutaTabla, $vacio, $BASE, $f3);
        $this->addCase($results, 'tabla_vacia_render', $tabla !== '' && str_contains($tabla, '<tbody>'),
            'la vista UpdateList no se dibuja con una lista vacia');
        $this->addCase($results, 'tabla_vacia_sin_filas', !str_contains($tabla, 'btn-group'),
            'la vista UpdateList dibuja acciones sin registros');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                        VISTAS DE DETALLE Y FORMULARIOS                     */
    /******************************************************************************/
    /**
     * Renderiza las vistas de detalle, resumen, exportacion y los dos formularios del
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
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'),
            $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'view_render', $html !== '', 'la vista View no produjo HTML');
        $this->addCase($results, 'view_pestanas',
            str_contains($html, 'id="view_tab_1"') && str_contains($html, 'id="view_tab_2"') && str_contains($html, 'id="view_tab_3"'),
            'la vista View no monta las tres pestanas (datos, documentos y observaciones)');
        $this->addCase($results, 'view_datos_basicos', str_contains($html, 'Camion Prueba') && str_contains($html, 'AAAA11'),
            'la vista View no pinta el nombre ni la patente del vehiculo');
        $this->addCase($results, 'view_documentos', str_contains($html, 'Permiso de Circulacion'),
            'la vista View no lista los documentos del vehiculo');
        $this->addCase($results, 'view_observaciones', str_contains($html, 'Observacion de prueba'),
            'la vista View no lista las observaciones del vehiculo');
        $this->addCase($results, 'view_documento_id_cifrado', !str_contains($html, "viewDocumentosView('1')"),
            'la vista View envia el ID del documento sin cifrar');
        $this->addCase($results, 'view_endpoint_documento', str_contains($html, self::RUTA_WEB.'/documentos/view/'),
            'la vista View no apunta al endpoint de detalle de documentos');
        $this->addCase($results, 'view_pie_cierre', str_contains($html, 'data-bs-dismiss="modal"'),
            'la vista View no ofrece el pie de cierre del modal');

        /*====================================*/
        // Resumen (edicion, imagen, documentos y observaciones)
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen.php'),
            $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'resumen_render', $html !== '', 'la vista Resumen no produjo HTML');
        $this->addCase($results, 'resumen_pestanas',
            str_contains($html, 'id="resumen"') && str_contains($html, 'id="resumen-edit"')
            && str_contains($html, 'id="resumen-img"') && str_contains($html, 'id="resumen-documentos"')
            && str_contains($html, 'id="resumen-obs"'),
            'la vista Resumen no monta resumen, edicion, imagen, documentos y observaciones');
        $this->addCase($results, 'resumen_form_edicion', str_contains($html, 'id="FormEditData"'),
            'la vista Resumen no dibuja el formulario FormEditData');
        $this->addCase($results, 'resumen_campos_edicion',
            str_contains($html, 'id="Edit_Nombre"') && str_contains($html, 'id="Edit_idTipo"')
            && str_contains($html, 'id="Edit_Patente"') && str_contains($html, 'id="Edit_idEstado"'),
            'el formulario de edicion no declara sus campos prefijados con Edit_');
        $this->addCase($results, 'resumen_id_oculto', str_contains($html, 'name="idVehiculo"'),
            'el formulario de edicion no envia idVehiculo');
        $this->addCase($results, 'resumen_endpoint_update',
            str_contains($html, self::RUTA_WEB.'/update') && str_contains($html, "Metodo      = 'POST'"),
            'el formulario de edicion no publica en '.self::RUTA_WEB.'/update con POST');
        $this->addCase($results, 'resumen_endpoint_delfiles',
            str_contains($html, self::RUTA_WEB.'/delFiles') && str_contains($html, "Metodo      = 'PUT'"),
            'el borrado de la imagen no usa '.self::RUTA_WEB.'/delFiles con PUT');
        $this->addCase($results, 'resumen_endpoint_subcontroladores',
            str_contains($html, self::RUTA_WEB.'/documentos/updateList/') && str_contains($html, self::RUTA_WEB.'/observaciones/updateList/'),
            'el resumen no carga las listas de documentos y observaciones');
        $this->addCase($results, 'resumen_id_cifrado', !str_contains($html, '/documentos/updateList/1\''),
            'el resumen envia el ID del vehiculo sin cifrar');

        /*====================================*/
        // Resumen-Update (ficha de solo lectura)
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen-Update.php'),
            $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'resumen_update_render', $html !== '', 'la vista Resumen-Update no produjo HTML');
        $this->addCase($results, 'resumen_update_datos',
            str_contains($html, 'Camion Prueba') && str_contains($html, 'AAAA11') && str_contains($html, 'SERIE0001'),
            'la ficha no pinta nombre, patente y numero de serie');
        $this->addCase($results, 'resumen_update_secciones',
            str_contains($html, 'Datos B') && str_contains($html, 'Caracter'),
            'la ficha no dibuja las secciones de datos basicos y caracteristicos');

        /*====================================*/
        // Formulario de alta
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-formNew.php'),
            $this->datosSinteticos(3), $BASE, $f3);
        $this->addCase($results, 'form_new_render', $html !== '', 'la vista formNew no produjo HTML');
        $this->addCase($results, 'form_new_id', str_contains($html, 'id="FormNewData"'),
            'el formulario de alta no usa el id FormNewData');
        $this->addCase($results, 'form_new_campos',
            str_contains($html, 'name="Nombre"') && str_contains($html, 'name="idTipo"')
            && str_contains($html, 'name="Marca"') && str_contains($html, 'name="Modelo"')
            && str_contains($html, 'name="Patente"') && str_contains($html, 'name="idTipoCarga"'),
            'el formulario de alta no declara Nombre/idTipo/Marca/Modelo/Patente/idTipoCarga');
        $this->addCase($results, 'form_new_oculto', str_contains($html, 'name="idEstado"'),
            'el formulario de alta no envia el estado oculto');
        $this->addCase($results, 'form_new_endpoint',
            str_contains($html, self::RUTA_WEB) && str_contains($html, "Metodo      = 'POST'"),
            'el formulario de alta no publica en '.self::RUTA_WEB.' con POST');
        $this->addCase($results, 'form_new_redireccion', str_contains($html, self::RUTA_WEB.'/resumen/'),
            'el formulario de alta no redirige al resumen del registro creado');

        /*====================================*/
        // Formulario de busqueda
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-formSearch.php'),
            $this->datosSinteticos(1), $BASE, $f3);
        $this->addCase($results, 'form_search_render', $html !== '', 'la vista formSearch no produjo HTML');
        $this->addCase($results, 'form_search_id', str_contains($html, 'id="FormSearchData"'),
            'el formulario de busqueda no usa el id FormSearchData');
        $this->addCase($results, 'form_search_inputs',
            str_contains($html, 'id="Search_Nombre"') && str_contains($html, 'id="Search_idEstado"')
            && str_contains($html, 'id="Search_idTipo"') && str_contains($html, 'id="Search_Marca"'),
            'el formulario de busqueda no declara sus campos prefijados con Search_');
        $this->addCase($results, 'form_search_endpoint',
            str_contains($html, '/search') && str_contains($html, "Metodo      = 'POST'"),
            'el formulario de busqueda no apunta a /search con POST');

        /*====================================*/
        // Exportacion
        /*====================================*/
        $datos = $this->datosSinteticos(3);
        // Fila con las columnas exactas que devuelve export()
        $datos['arrList'] = [[
            'Nombre' => 'Camion Prueba', 'Marca' => 'MarcaPrueba', 'Modelo' => 'ModeloPrueba', 'Num_serie' => 'SERIE0001',
            'AnoFab' => 2020, 'Patente' => 'AAAA11', 'CapacidadPersonas' => 2, 'Capacidad' => 1000, 'MCubicos' => 10,
            'Direccion_img' => '', 'Latitud' => 0, 'Longitud' => 0, 'Velocidad' => 0,
            'LastUpdateFecha' => '2026-01-02', 'LastUpdateHora' => '10:00:00',
            'Tipo' => 'Camion', 'TipoCarga' => 'General', 'Estado' => 'Activo', 'EstadoColor' => 'success',
        ]];
        $html  = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-Exportar.php'),
            $datos, $BASE, $f3);
        $this->addCase($results, 'exportar_datos', str_contains($html, 'Camion Prueba') && str_contains($html, 'AAAA11') && str_contains($html, 'SERIE0001'),
            'la vista Exportar no pinta nombre, patente y numero de serie del vehiculo');
        $this->addCase($results, 'exportar_render', $html !== '', 'la vista Exportar no produjo HTML');
        $this->addCase($results, 'exportar_tabla', str_contains($html, 'id="tableData"'),
            'la vista Exportar no dibuja la tabla tableData');
        $this->addCase($results, 'exportar_volver', str_contains($html, self::RUTA_WEB.'/listAll'),
            'la vista Exportar no ofrece volver al listado');
        $this->addCase($results, 'exportar_boton_nivel3', str_contains($html, 'exportTableToExcel('),
            'con nivel 3 la vista Exportar no ofrece exportar a Excel');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos el chequeo de datos del controlador (`dataCheck`),
     * el cifrado de identificadores y la validacion de IDs descifrados. Ademas cruza
     * las columnas que consulta el controlador con las tablas que crea el instalador.
     *
     * @return array Resultado por caso
     */
    public function logicaValidacionTest(){

        $results = [];

        /*====================================*/
        // dataCheck devuelve el contrato esperado por Base_insert/Base_update
        /*====================================*/
        try {
            $reflector = new ReflectionMethod(self::CONTROLADOR, 'dataCheck');
            $reflector->setAccessible(true);
            // Se crea sin constructor: dataCheck() es logica pura y no necesita
            // la conexion a base de datos del controlador.
            $Controller = $this->instanciaSinBD(self::CONTROLADOR);

            $Post      = ['Nombre' => 'Camion Prueba', 'idEstado' => 1, 'idTipo' => 1, 'Patente' => 'AAAA11'];
            $DataCheck = $reflector->invoke($Controller, $Post);

            $this->addCase($results, 'datacheck_es_arreglo', is_array($DataCheck),
                'dataCheck() no devolvio un arreglo');
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post,
                'dataCheck() no conserva el POST recibido');
            $this->addCase($results, 'datacheck_numericos',
                str_contains((string)($DataCheck['ValidarNumero'] ?? ''), 'idEstado')
                && str_contains((string)($DataCheck['ValidarNumero'] ?? ''), 'idTipo')
                && str_contains((string)($DataCheck['ValidarNumero'] ?? ''), 'Latitud')
                && str_contains((string)($DataCheck['ValidarEntero'] ?? ''), 'idTipoCarga'),
                'dataCheck() no valida los identificadores y coordenadas como numeros');
            $this->addCase($results, 'datacheck_enteros_sin_coordenadas',
                !str_contains((string)($DataCheck['ValidarEntero'] ?? ''), 'Latitud')
                && !str_contains((string)($DataCheck['ValidarEntero'] ?? ''), 'Longitud')
                && !str_contains((string)($DataCheck['ValidarEntero'] ?? ''), 'Velocidad'),
                'dataCheck() exige enteros en campos que admiten decimales (Latitud/Longitud/Velocidad)');
            $this->addCase($results, 'datacheck_patente', ($DataCheck['ValidarPatente'] ?? '') === 'Patente',
                'dataCheck() no valida el formato de la patente');
            $this->addCase($results, 'datacheck_patente_normalizada',
                ($DataCheck['ValidarMayusculas'] ?? '') === 'Num_serie,Patente'
                && ($DataCheck['ValidarEspaciosVacios'] ?? '') === 'Num_serie,Patente',
                'dataCheck() no normaliza (mayusculas, sin espacios) Num_serie y Patente');
            $this->addCase($results, 'datacheck_censura',
                ($DataCheck['ValidarPalabrasCensuradas'] ?? '') === 'Nombre,Marca,Modelo,Num_serie',
                'dataCheck() no censura palabras en Nombre,Marca,Modelo,Num_serie');
            $this->addCase($results, 'datacheck_largo',
                (string)($DataCheck['ValidarLargoMinimo'] ?? '') === 'Nombre,Marca,Modelo,Num_serie,Patente'
                && (int)($DataCheck['ValidarLargoMinimoN'] ?? 0) === 3
                && ($DataCheck['ValidarLargoMaximoPorCampo'] ?? []) === [
                    'Nombre'    => 255,
                    'Marca'     => 255,
                    'Modelo'    => 255,
                    'Num_serie' => 255,
                    'Patente'   => 120,
                ],
                'dataCheck() no fija correctamente todos los campos de largo (minimo/maximo)');

            // El largo maximo no puede superar el de la columna creada por el instalador
            $fuenteInstalador = $this->fuenteInstalador();
            $largosOk         = true;
            $detalleLargos    = '';
            foreach (($DataCheck['ValidarLargoMaximoPorCampo'] ?? []) as $campo => $largo) {
                if (preg_match('/`'.preg_quote($campo, '/').'` varchar\((\d+)\)/', $fuenteInstalador, $m) === 1 && (int)$m[1] < (int)$largo) {
                    $largosOk      = false;
                    $detalleLargos = $campo.' admite '.$largo.' pero la columna es varchar('.$m[1].')';
                }
            }
            $this->addCase($results, 'datacheck_largo_vs_columna', $largosOk,
                'el largo maximo supera el de la columna ('.$detalleLargos.')');
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_es_arreglo', false,
                'no se pudo ejecutar dataCheck() ('.$e->getMessage().')');
        }

        /*====================================*/
        // Las columnas del controlador existen en la tabla del instalador
        /*====================================*/
        $columnas = $this->columnasTabla(self::TABLA);
        $this->addCase($results, 'tabla_columnas_leidas', count($columnas) > 0,
            'no se pudieron leer las columnas de '.self::TABLA.' desde el instalador');

        if (!empty($columnas)) {
            $codigo = $this->fuenteControlador();

            // Columnas calificadas (vehiculos_listado.X) en consultas y ordenamientos
            preg_match_all('/'.self::TABLA.'\.(\w+)/', $codigo, $m);
            $desconocidas = array_values(array_diff(array_unique($m[1]), $columnas));
            $this->addCase($results, 'columnas_consultas_existen', empty($desconocidas),
                'el controlador consulta columnas inexistentes en '.self::TABLA.': '.implode(', ', $desconocidas));

            // Columnas de escritura (data/required/unique/where) de Insert y Update
            $escritura = [];
            foreach (['Insert', 'Update'] as $metodo) {
                $cuerpo = $this->cuerpoMetodo($metodo);
                foreach (['data', 'required', 'unique', 'where'] as $clave) {
                    if (preg_match("/'".$clave."'\s*=>\s*'([^']*)'/", $cuerpo, $mm) === 1) {
                        foreach (explode(',', $mm[1]) as $campo) {
                            if (trim($campo) !== '') {
                                $escritura[trim($campo)] = true;
                            }
                        }
                    }
                }
            }
            $desconocidas = array_values(array_diff(array_keys($escritura), $columnas));
            $this->addCase($results, 'columnas_escritura_existen', empty($desconocidas),
                'Insert()/Update() escriben columnas inexistentes en '.self::TABLA.': '.implode(', ', $desconocidas));

            // Todo campo validado como numerico existe como columna (o es la clave del registro)
            $cuerpoCheck = $this->cuerpoMetodo('dataCheck');
            $validados   = [];
            foreach (['ValidarNumero', 'ValidarEntero', 'ValidarLargoMinimo', 'ValidarPatente'] as $clave) {
                if (preg_match("/'".$clave."'\s*=>\s*'([^']*)'/", $cuerpoCheck, $mm) === 1) {
                    foreach (explode(',', $mm[1]) as $campo) {
                        if (trim($campo) !== '') {
                            $validados[trim($campo)] = true;
                        }
                    }
                }
            }
            $desconocidas = array_values(array_diff(array_keys($validados), $columnas));
            $this->addCase($results, 'columnas_validadas_existen', empty($desconocidas),
                'dataCheck() valida campos que no son columnas de '.self::TABLA.': '.implode(', ', $desconocidas));
        }

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
     * sesion desde las plantillas, escape de los datos que pintan el listado, SQL con
     * parametros preparados y ausencia de codigo residual de otro modulo.
     *
     * @return array Resultado por caso
     */
    public function seguridadVistasTest(){

        $results = [];

        /*====================================*/
        // Vistas involucradas (solo las de este controlador)
        /*====================================*/
        $arrVistas = array_values(array_filter(
            glob($this->rutaModulo('../views/'.self::CONTROLADOR.'-*.php')) ?: [],
            static fn(string $ruta): bool => !preg_match('/'.self::CONTROLADOR.'-(Resumen-)?(Documentos|Observaciones)-/', basename($ruta))
        ));
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
            $this->addCase($results, 'vista_sin_residuos_'.$nombre, !preg_match(self::RESIDUOS_OTRO_MODULO, $codigo, $residuo),
                $nombre.' contiene codigo de otro modulo ('.($residuo[1] ?? '').')');
        }

        /*====================================*/
        // Escape de los datos pintados en el listado
        /*====================================*/
        $tabla = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'));
        $this->addCase($results, 'tabla_escape_entidad', str_contains($tabla, 'addslashes('),
            'UpdateList.php no escapa la etiqueta del registro en la accion de borrar');
        $this->addCase($results, 'tabla_id_cifrado', str_contains($tabla, "encryptDecrypt('encrypt'"),
            'UpdateList.php no cifra el ID antes de pintarlo en las acciones');

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
        $this->addCase($results, 'controlador_sin_residuos', !preg_match(self::RESIDUOS_OTRO_MODULO, $controlador, $residuo),
            'el controlador contiene codigo de otro modulo ('.($residuo[1] ?? '').')');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                   COHERENCIA DE NIVELES (LISTADO / FILTRO)                 */
    /******************************************************************************/
    /**
     * Verifica que el boton "Quitar Filtro" de la vista de listado (visible para todo usuario,
     * sin guarda de nivel) no llame a una ruta `updateList` que exija mas nivel que la ruta
     * `listAll` que sirve la pantalla: un usuario con menos nivel no tiene la ruta registrada
     * en el router (404) y podria filtrar con `search` pero no quitar el filtro.
     *
     * @return array Resultado por caso
     */
    public function coherenciaNivelesListadoTest(){

        $results = [];
        $vista   = __DIR__.'/../views/vehiculosListado-List.php';
        $fuente  = is_file($vista) ? (string)file_get_contents($vista) : '';

        /*====================================*/
        // La vista llama a updateList desde deleteFilter() y el boton no tiene guarda de nivel
        /*====================================*/
        $usaUpdateList = preg_match('/function deleteFilter.*?\/updateList/s', $fuente) === 1;
        $results['quitar_filtro_usa_updatelist'] = $usaUpdateList
            ? 'OK' : 'FALLO: la vista vehiculosListado-List.php ya no refresca con updateList desde deleteFilter()';

        /*====================================*/
        // Nivel de listAll y de updateList por cada ruta base del controlador
        /*====================================*/
        $niveles = $this->nivelesListadoRutas('vehiculosListado');
        $results['rutas_listado_leidas'] = !empty($niveles)
            ? 'OK' : 'FALLO: no se encontraron rutas listAll/updateList de vehiculosListado en el instalador';

        foreach ($niveles as $base => $n) {
            if (!isset($n['listAll'], $n['updateList'])) {
                continue;
            }
            $results['quitar_filtro_'.str_replace('/', '_', $base)] = $n['updateList'] <= $n['listAll']
                ? 'OK'
                : 'FALLO: '.$base.'/updateList exige nivel '.$n['updateList'].' pero '.$base.'/listAll es nivel '.$n['listAll'].'; el boton "Quitar Filtro" devuelve 404 a un usuario de nivel '.$n['listAll'];
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /*******************************************************************/
    // Niveles (idLevelLimit) de las rutas GET listAll y updateList de un controlador,
    // leidos del fuente del instalador. Formato: [ruta base => ['listAll' => n, 'updateList' => n]]
    /*******************************************************************/
    private function nivelesListadoRutas(string $controlador): array{
        $niveles = [];
        foreach (glob(__DIR__.'/../installer/*Installer.php') ?: [] as $instalador) {
            foreach (preg_split('/\r\n|\r|\n/', (string)file_get_contents($instalador)) as $linea) {
                if (!str_contains($linea, "'".$controlador.'->')) {
                    continue;
                }
                if (preg_match("/'idMetodo'\s*=>\s*1\s*,\s*'RutaWeb'\s*=>\s*'([^']+)'.*?'idLevelLimit'\s*=>\s*'?(\d+)'?/", $linea, $m) !== 1) {
                    continue;
                }
                foreach (['listAll', 'updateList'] as $accion) {
                    if (str_ends_with($m[1], '/'.$accion)) {
                        $niveles[substr($m[1], 0, -strlen($accion) - 1)][$accion] = (int)$m[2];
                    }
                }
            }
        }

        return $niveles;
    }

}
