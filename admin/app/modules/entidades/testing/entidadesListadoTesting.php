<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class entidadesListadoTesting
 *
 * Pruebas del modulo `entidades` - controlador `entidadesListado` (CRUD de entidades).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. Los textos de los casos
 *    correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Alcance: pruebas NO destructivas. `Insert()`, `Update()`, `Delete()` y `delFiles()` escriben
 * en base de datos, geolocalizan por red (`FunctionsLocation::geocodeAddress`), gestionan
 * archivos y terminan en `Response::*()` (`exit`); NO se invocan: se verifican por reflexion y
 * lectura del fuente. Solo se ejecuta logica pura (`dataCheck`, cifrado de IDs).
 *
 * Esta suite sustituye al stub vacio `entidadesTesting.php` (la clase debe llamarse como el
 * archivo y el archivo como el controlador).
 *
 * @see skills/testing/SKILL.md
 */
class entidadesListadoTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR      = 'entidadesListado';
    private const RUTA_WEB         = 'administracion/entidades/listado';
    private const RUTA_WEB_LISTADO = 'administracion/entidades/listado/listAll';
    private const RUTA_WEB_SEARCH  = 'administracion/entidades/listado/search';
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
    // Contrato de presentacion de un metodo (vista instanciada)
    /*******************************************************************/
    private function contratoPresentacion(array &$results, string $metodo, string $archivoVista, string $tipoVista){
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'vista_'.$metodo, str_contains($codigo, "\$this->controllerName.'-".$archivoVista.".php'"),
            $metodo.'() no instancia la vista '.self::CONTROLADOR.'-'.$archivoVista);
        $this->addCase($results, 'vista_existe_'.$metodo, is_file($this->rutaModulo('../views/'.self::CONTROLADOR.'-'.$archivoVista.'.php')),
            'no existe el archivo de la vista '.self::CONTROLADOR.'-'.$archivoVista);
        $this->addCase($results, 'tipoview_'.$metodo,
            (bool)preg_match("/showVista\(".$tipoVista.",[^;]*-".preg_quote($archivoVista, '/')."\.php'\)/", $codigo),
            $metodo.'() no sirve la vista '.$archivoVista.' con showVista('.$tipoVista.')');
    }

    /*******************************************************************/
    // Rutas del instalador: [idMetodo.' '.RutaWeb => [handler, nivel, controller]]
    /*******************************************************************/
    private function rutasInstalador(): array{
        $rutas = [];
        if (!class_exists('entidadesInstaller')) {
            return $rutas;
        }
        try {
            $instalador = (new ReflectionClass('entidadesInstaller'))->newInstanceWithoutConstructor();
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
            'PageTitle'        => 'Listado Entidades',
            'PageDescription'  => 'Listado Entidades.',
            'PageAuthor'       => 'Pruebas',
            'PageKeywords'     => 'Pruebas',
            'TableTitle'       => 'Listado Entidades',
            'UserData'         => [
                'MainPathUrl'                 => '/admin/public/upload/',
                'sistemaModalSubtitle'        => 1,
                'sistemaModalCloseBTN'        => 2,
                'UserType'                    => 2,
                'entidadesListadoVerCargas'      => 2,
                'entidadesListadoVerContactos'   => 2,
                'entidadesListadoVerDocumentos'  => 2,
                'entidadesListadoUsoPassword'    => 2,
            ],
            'UserAccess'       => ['LevelAccess' => $nivel, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_FormInputs'   => new UIFormInputs(),
            'Fnc_Codification' => new FunctionsSecurityCodification(),
            'Fnc_DataDate'     => new FunctionsDataDate(),
            'Fnc_DataNumbers'  => new FunctionsDataNumbers(),
            'Fnc_WidgetsCommon' => new UIWidgetsCommon(),
            'Fnc_WidgetsViews'  => new UIWidgetsViews(),
            'arrList'          => [[
                'idEntidad'     => 91,
                'idTipoEntidad' => 2,
                'Nombre'        => '',
                'ApellidoPat'   => '',
                'ApellidoMat'   => '',
                'RazonSocial'   => 'Entidad Prueba SpA',
                'Nick'          => 'prueba',
                'Estado'        => 'Activo',
                'EstadoColor'   => 'success',
                'Sector'        => 'Sector Prueba',
                'Tipo'          => 'Cliente',
                'TipoEntidad'   => 'Empresa',
            ]],
            'arrEstado'        => [['ID' => 1, 'Nombre' => 'Activo']],
            'arrSector'        => [['ID' => 1, 'Nombre' => 'Sector Prueba']],
            'arrSexo'          => [['ID' => 1, 'Nombre' => 'Masculino']],
            'arrTipo'          => [['ID' => 1, 'Nombre' => 'Cliente']],
            'arrTipoEntidad'   => [['ID' => 1, 'Nombre' => 'Persona Natural'], ['ID' => 2, 'Nombre' => 'Empresa']],
            'arrCiudad'        => [['ID' => 1, 'Nombre' => 'Santiago']],
            'arrComuna'        => [['ID1' => 1, 'ID2' => 1, 'Nombre' => 'Providencia']],
            'arrCargas'        => [],
            'arrContactos'     => [],
            'arrDocumentos'    => [],
            'arrObservaciones' => [[
                'FechaCreacion' => '2026-01-02',
                'Usuario'       => 'Tester',
                'Observacion'   => 'Observacion de prueba',
            ]],
            'rowData'          => [
                'idEntidad'     => 91,
                'idEstado'      => 1,
                'idTipo'        => 1,
                'idTipoEntidad' => 2,
                'Nombre'        => '',
                'ApellidoPat'   => '',
                'ApellidoMat'   => '',
                'RazonSocial'   => 'Entidad Prueba SpA',
                'Nick'          => 'prueba',
                'Rut'           => '76.000.000-0',
                'Direccion'     => 'Calle Falsa 123',
                'Direccion_img' => '',
                'Email'         => 'prueba@example.test',
                'Fono1'         => '+56911111111',
                'Fono2'         => '',
                'Web'           => '',
                'Giro'          => 'Giro de prueba',
                'Latitud'       => '0',
                'Longitud'      => '0',
                'Estado'        => 'Activo',
                'EstadoColor'   => 'success',
                'Sector'        => 'Sector Prueba',
                'Tipo'          => 'Cliente',
                'TipoEntidad'   => 'Empresa',
                'Ciudad'        => 'Santiago',
                'Comuna'        => 'Providencia',
                'Sexo'          => '',
                'FNacimiento'   => '',
                'RepLegalNombre' => '',
                'RepLegalRut'   => '',
                'RepLegalEmail' => '',
                'RepLegalFono1' => '',
                'RepLegalFono2' => '',
                'Social_X'      => '',
                'Social_Facebook'  => '',
                'Social_Instagram' => '',
                'Social_Linkedin'  => '',
                'Ultimo_acceso' => '',
            ],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica los archivos del controlador `entidadesListado` y la convencion
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
            'controlador'          => $this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'),
            'instalador'           => $this->rutaModulo('../installer/entidadesInstaller.php'),
            'vista_list'           => $this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php'),
            'vista_update_list'    => $this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'),
            'vista_view'           => $this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'),
            'vista_exportar'       => $this->rutaModulo('../views/'.self::CONTROLADOR.'-Exportar.php'),
            'vista_resumen'        => $this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen.php'),
            'vista_resumen_update' => $this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen-Update.php'),
            'vista_form_new'       => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formNew.php'),
            'vista_form_search'    => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formSearch.php'),
            'testing'              => $this->rutaModulo('entidadesListadoTesting.php'),
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
            $this->addCase($results, 'htaccess_'.$nombre,
                (stripos((string)file_get_contents($ruta), 'deny from all') !== false),
                'el archivo no contiene "deny from all"');
        }

        /*====================================*/
        // Convencion clase = nombre de archivo, herencias y stub retirado
        /*====================================*/
        $this->addCase($results, 'clase_controlador_cargada', class_exists(self::CONTROLADOR),
            'la clase '.self::CONTROLADOR.' no esta disponible');
        $this->addCase($results, 'clase_testing_cargada', class_exists('entidadesListadoTesting'),
            'la clase entidadesListadoTesting no esta disponible');
        $this->addCase($results, 'clase_instalador_cargada', class_exists('entidadesInstaller'),
            'la clase entidadesInstaller no esta disponible');
        $padre = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padre === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padre.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('entidadesListadoTesting') === 'ControllerBase',
            'el testing no extiende ControllerBase');
        $this->addCase($results, 'stub_vacio_retirado', !is_file($this->rutaModulo('entidadesTesting.php')),
            'sigue existiendo el stub vacio entidadesTesting.php (el runner lo marcaria sin pruebas)');

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

        /*====================================*/
        // Entorno: subida de imagen de la entidad valida el tipo real
        /*====================================*/
        $this->addCase($results, 'entorno_finfo', class_exists('finfo'),
            'la extension finfo no esta disponible para validar la imagen de la entidad');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                            CONTROLADOR: VISTAS                             */
    /******************************************************************************/
    /**
     * Verifica el contrato de los metodos que devuelven HTML (`listAll`, `UpdateList`,
     * `export`, `View`, `Resumen`, `ResumenUpdate`) y de la busqueda del listado.
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

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
        $this->contratoPresentacion($results, 'listAll', 'List', '1');
        $this->contratoPresentacion($results, 'UpdateList', 'UpdateList', '2');
        $this->contratoPresentacion($results, 'export', 'Exportar', '1');
        $this->contratoPresentacion($results, 'View', 'View', '2');
        $this->contratoPresentacion($results, 'Resumen', 'Resumen', '1');
        $this->contratoPresentacion($results, 'ResumenUpdate', 'Resumen-Update', '2');

        $this->addCase($results, 'rutavista_resuelta', substr_count($codigo, 'returnRutaVista(__DIR__') === 6,
            'las 6 vistas deben resolver su ruta con returnRutaVista()');
        $this->addCase($results, 'errores_showerror', substr_count($codigo, 'showError(') === 6,
            'las 6 vistas deben manejar el fallo de consulta con showError()');
        $this->addCase($results, 'fallback_mergeresponses', substr_count($codigo, 'mergeResponses(') === 6,
            'las 6 vistas deben agrupar respuestas con mergeResponses()');
        $this->addCase($results, 'nivel_en_vistas', substr_count($codigo, 'getArrLevel($f3, $this->controllerName)') === 6,
            'las 6 vistas deben resolver el nivel de acceso');
        $this->addCase($results, 'usuario_en_vistas', substr_count($codigo, 'getUserData($f3)') === 7,
            'se esperaban 7 lecturas de getUserData($f3) (6 vistas y View)');

        /*====================================*/
        // Metadatos y catalogos del listado
        /*====================================*/
        $this->addCase($results, 'listado_titulo', str_contains($codigo, "'PageTitle'") && str_contains($codigo, "'TableTitle'"),
            'listAll() no asigna PageTitle y TableTitle');
        $this->addCase($results, 'listado_tabla_principal', str_contains($codigo, "'table'   => 'entidades_listado'"),
            'el listado no consulta entidades_listado');
        $catalogos = ['core_estados', 'entidades_sectores', 'core_sexo', 'core_tipos_entidad', 'core_tipos_entidades', 'core_ubicacion_ciudad', 'core_ubicacion_comunas'];
        $faltan = [];
        foreach ($catalogos as $tabla) {
            if (!str_contains($codigo, "'table'   => '".$tabla."'")) {
                $faltan[] = $tabla;
            }
        }
        $this->addCase($results, 'listado_catalogos', count($faltan) === 0,
            'listAll() no carga los catalogos: '.implode(', ', $faltan));
        $this->addCase($results, 'listado_catalogos_estado_exito',
            str_contains($codigo, '$arrList[\'status\'] && $arrEstado[\'status\'] && $arrSector[\'status\'] && $arrSexo[\'status\'] && $arrTipo[\'status\'] && $arrTipoEntidad[\'status\'] && $arrCiudad[\'status\'] && $arrComuna[\'status\']'),
            'listAll() no exige que todas las consultas hayan respondido antes de pintar');
        $this->addCase($results, 'consultas_limite', substr_count($codigo, 'ConfigAPP::APP["N_MaxItems"]') === 21,
            'se esperaban 21 consultas de lista acotadas por N_MaxItems');

        /*====================================*/
        // Busqueda: columnas, validacion de fechas y WHERE preparado
        /*====================================*/
        $this->addCase($results, 'busqueda_campos_exactos',
            str_contains($codigo, "'idEstado,idSector,idSexo,idTipo,idTipoEntidad,idCiudad,idComuna,FNacimiento'"),
            'UpdateList() no filtra por los campos exactos del formulario de busqueda');
        $this->addCase($results, 'busqueda_campos_texto',
            str_contains($codigo, "'Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Direccion,Email'"),
            'UpdateList() no filtra por los campos de texto del formulario de busqueda');
        $this->addCase($results, 'busqueda_valida_fechas', str_contains($codigo, 'searchValidateDates('),
            'UpdateList() no valida las fechas con searchValidateDates()');
        $this->addCase($results, 'busqueda_where_preparado', substr_count($codigo, '$this->searchWhere(') === 3,
            'UpdateList() no construye el WHERE con los tres tipos de searchWhere()');
        $this->addCase($results, 'busqueda_params', str_contains($codigo, "'params'  => \$whereParams"),
            'UpdateList() no pasa los parametros preparados del WHERE');

        /*====================================*/
        // Exportar: solo lectura y sin limite de campos sensibles
        /*====================================*/
        $export = substr($codigo, (int)strpos($codigo, 'public function export('), 4500);
        $this->addCase($results, 'export_sin_password', !str_contains($export, 'entidades_listado.password'),
            'export() consulta la contrasena de la entidad');
        $this->addCase($results, 'export_sin_escritura', !str_contains($export, 'Base_insert') && !str_contains($export, 'Base_update') && !str_contains($export, 'Base_delete'),
            'export() contiene operaciones de escritura');

        /*====================================*/
        // Detalle: tabs condicionadas por permisos y sub-recursos
        /*====================================*/
        foreach (['entidades_listado_cargas', 'entidades_listado_contactos', 'entidades_listado_documentos', 'entidades_listado_observaciones'] as $tabla) {
            $this->addCase($results, 'view_consulta_'.$tabla, str_contains($codigo, "'table'   => '".$tabla."'"),
                'View() no consulta '.$tabla);
        }
        $this->addCase($results, 'view_cargas_condicionadas', str_contains($codigo, "entidadesListadoVerCargas"),
            'View() no condiciona las cargas al permiso entidadesListadoVerCargas');
        $this->addCase($results, 'view_contactos_condicionados', str_contains($codigo, "entidadesListadoVerContactos"),
            'View() no condiciona los contactos al permiso entidadesListadoVerContactos');
        $this->addCase($results, 'view_documentos_condicionados', str_contains($codigo, "entidadesListadoVerDocumentos"),
            'View() no condiciona los documentos al permiso entidadesListadoVerDocumentos');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                         CONTROLADOR: ESCRITURA (CRUD)                      */
    /******************************************************************************/
    /**
     * Verifica `Insert`, `Update`, `Delete` y `delFiles` SIN ejecutarlos: verbo HTTP,
     * chequeo de datos, unicidad, cifrado del password, geolocalizacion, subida de imagen,
     * borrado en cascada transaccional y respuestas al cliente.
     *
     * @return array Resultado por caso
     */
    public function controladorEscrituraTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Firma de cada metodo de escritura (sin parametros)
        /*====================================*/
        foreach (['Insert', 'Update', 'Delete', 'delFiles'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 0, 0);
        }

        /*====================================*/
        // Chequeo de datos y tabla
        /*====================================*/
        $this->addCase($results, 'datacheck_post', substr_count($codigo, '$this->dataCheck($_POST)') === 2,
            'Insert() y Update() deben construir el DataCheck con dataCheck($_POST)');
        $this->addCase($results, 'tabla_entidades', substr_count($codigo, "'table'     => 'entidades_listado'") === 2,
            'Insert() y Update() deben apuntar a entidades_listado');
        $this->addCase($results, 'unicidad_rut_email', substr_count($codigo, "'unique'    => 'Rut,Email'") === 2,
            'Insert() y Update() deben exigir unicidad de Rut y Email');
        $this->addCase($results, 'password_inicial_en_servidor', substr_count($codigo, "\$_POST['password']             = '1234';") === 1,
            'Insert() debe asignar la contrasena inicial en el servidor (una sola vez)');
        $this->addCase($results, 'password_forzar_cambio', substr_count($codigo, "\$_POST['PasswordForzarCambio'] = 1;") === 2
            && substr_count($codigo, "\$_POST['PasswordUltimoCambio'] = date('Y-m-d H:i:s');") === 2
            && substr_count($codigo, 'PasswordForzarCambio,PasswordUltimoCambio') === 2,
            'Insert() y Update() deben marcar el cambio forzado y registrar la fecha del cambio de password');
        $this->addCase($results, 'password_cifrado', substr_count($codigo, "'encode'    => 'password'") === 2,
            'Insert() y Update() deben codificar el campo password');

        /*====================================*/
        // Insert
        /*====================================*/
        $this->addCase($results, 'insert_requeridos', str_contains($codigo, "'required'  => 'idEstado,idTipo,idTipoEntidad,password'"),
            'Insert() no declara los obligatorios idEstado,idTipo,idTipoEntidad,password');
        $this->addCase($results, 'insert_base', str_contains($codigo, '$this->Base_insert('),
            'Insert() no escribe con Base_insert()');
        $this->addCase($results, 'insert_id_cifrado', str_contains($codigo, "encryptDecrypt('encrypt', \$Response['data'])"),
            'Insert() no cifra el ID devuelto');
        $this->addCase($results, 'insert_valida_id_cifrado', str_contains($codigo, "isValidDecrypted(\$DataID, 'text')"),
            'Insert() no valida el ID cifrado antes de responder');

        /*====================================*/
        // Geolocalizacion (no se ejecuta: consulta un servicio externo)
        /*====================================*/
        $this->addCase($results, 'geocodifica_insert_update', substr_count($codigo, 'geocodeAddress(') === 2,
            'Insert() y Update() deben geocodificar la direccion');
        $this->addCase($results, 'geocodifica_pais', substr_count($codigo, "\$Ubicacion .= ', Chile';") === 2,
            'la direccion a geocodificar debe incluir el pais');
        $this->addCase($results, 'geocodifica_respaldo', substr_count($codigo, "\$_SESSION['DataInfo']['Latitud']") === 2,
            'si no hay geocodificacion se debe usar la ubicacion por defecto del usuario');
        $this->addCase($results, 'geocodifica_comuna_ciudad_preparado',
            str_contains($codigo, "'where'   => 'idComuna = ?'") && str_contains($codigo, "'where'   => 'idCiudad = ?'"),
            'la busqueda de comuna y ciudad no usa parametros preparados');

        /*====================================*/
        // Update: verbo, delimitacion y subida de imagen
        /*====================================*/
        $this->addCase($results, 'update_verbo_post', str_contains($codigo, "REQUEST_METHOD'] !== 'POST'"),
            'Update() no valida el verbo HTTP POST');
        $this->addCase($results, 'update_donde', str_contains($codigo, "'where'     => 'idEntidad'"),
            'Update() no delimita la escritura por idEntidad');
        $this->addCase($results, 'update_requeridos', str_contains($codigo, "'required'  => 'idEntidad,idEstado,idTipo,idTipoEntidad'"),
            'Update() no declara los obligatorios');
        $this->addCase($results, 'update_base', str_contains($codigo, '$this->Base_update('),
            'Update() no escribe con Base_update()');
        $this->addCase($results, 'update_imagen_identificador', str_contains($codigo, "'Identificador' => 'Direccion_img'"),
            'Update() no declara la subida de Direccion_img');
        $this->addCase($results, 'update_imagen_tipo', str_contains($codigo, "'ValidarTipo'   => 'image'"),
            'la subida de Direccion_img no valida que sea una imagen');
        $this->addCase($results, 'update_imagen_peso', str_contains($codigo, "'ValidarPeso'   => 10"),
            'la subida de Direccion_img no limita el peso (10)');

        /*====================================*/
        // Delete: verbo, transaccion y borrado en cascada
        /*====================================*/
        $this->addCase($results, 'delete_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'DELETE'"),
            'Delete() no valida el verbo HTTP DELETE');
        $this->addCase($results, 'delete_transaccion',
            substr_count($codigo, 'Base_transactionBegin()') === 1
            && substr_count($codigo, 'Base_transactionCommit()') === 1
            && substr_count($codigo, 'Base_transactionRollback()') === 0,
            'Delete() debe abrir una transaccion, confirmarla; los fallos pasan a failOperation (0 rollback inline)');
        $this->addCase($results, 'delete_cascada',
            str_contains($codigo, "'table' => 'entidades_listado_cargas'")
            && str_contains($codigo, "'table' => 'entidades_listado_contactos'")
            && str_contains($codigo, "'table' => 'entidades_listado_documentos'")
            && str_contains($codigo, "'table' => 'entidades_listado_observaciones'"),
            'Delete() no elimina cargas, contactos, documentos y observaciones relacionados');
        $this->addCase($results, 'delete_archivos',
            str_contains($codigo, "'files'       => 'Direccion_img'") && str_contains($codigo, "'files' => 'NombreArchivo'"),
            'Delete() no elimina la imagen de la entidad ni los archivos de sus documentos');
        $this->addCase($results, 'delete_base', substr_count($codigo, '$this->Base_delete(') === 2,
            'Delete() debe borrar la cabecera y cada relacionada con Base_delete()');

        /*====================================*/
        // delFiles: verbo PUT y borrado de la imagen
        /*====================================*/
        $this->addCase($results, 'delfiles_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'PUT'"),
            'delFiles() no valida el verbo HTTP PUT');
        $this->addCase($results, 'delfiles_base', str_contains($codigo, '$this->Base_delFiles('),
            'delFiles() no invoca Base_delFiles()');

        /*====================================*/
        // Respuestas al cliente
        /*====================================*/
        $this->addCase($results, 'respuesta_exito', substr_count($codigo, 'Response::success(') === 4,
            'se esperaban 4 Response::success() (Insert, Update, Delete y delFiles)');
        $this->addCase($results, 'respuesta_405', substr_count($codigo, "Response::error('Error en el Request Method', 405)") === 3,
            'Update/Delete/delFiles deben responder 405 ante un verbo incorrecto');
        $this->addCase($results, 'respuesta_400', substr_count($codigo, "Response::error('Registro inválido', 400)") === 4,
            'View/Resumen/ResumenUpdate e Insert deben responder 400 ante un ID invalido');
        $this->addCase($results, 'respuesta_500', substr_count($codigo, 'failOperation(') === 5,
            'se esperaban 5 puntos failOperation (Insert, Update, Delete x2 y delFiles)');

        /*====================================*/
        // IDs cifrados en las vistas
        /*====================================*/
        $this->addCase($results, 'id_descifrado_vistas', substr_count($codigo, "encryptDecrypt('decrypt', \$params['id'])") === 3,
            'View/Resumen/ResumenUpdate deben descifrar $params[\'id\']');
        $this->addCase($results, 'id_valido_vistas', substr_count($codigo, "isValidDecrypted(\$EntidadID, 'id')") === 3,
            'View/Resumen/ResumenUpdate deben validar el ID con isValidDecrypted()');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                       CONTROLADOR: RUTAS E INSTALADOR                      */
    /******************************************************************************/
    /**
     * Verifica que las rutas de `entidadesListado` publicadas por el instalador apunten a
     * metodos reales con el verbo y el nivel esperados, y que la desinstalacion las incluya.
     *
     * @return array Resultado por caso
     */
    public function controladorRutasTest(){

        $results = [];
        $rutas   = $this->rutasInstalador();
        $this->addCase($results, 'instalador_rutas_disponibles', count($rutas) > 0,
            'getRouteDefinitions() no devolvio rutas');

        /*====================================*/
        // idMetodo: 1=GET, 2=POST, 3=DELETE, 4=PUT. [clave, handler, nivel]
        /*====================================*/
        $esperadas = [
            'listado'        => ['1 '.self::RUTA_WEB.'/listAll',          'entidadesListado->listAll',       1],
            'busqueda'       => ['2 '.self::RUTA_WEB.'/search',           'entidadesListado->UpdateList',    1],
            'refrescar'      => ['1 '.self::RUTA_WEB.'/updateList',       'entidadesListado->UpdateList',    2],
            'exportar'       => ['1 '.self::RUTA_WEB.'/exportar',         'entidadesListado->export',        3],
            'ver'            => ['1 '.self::RUTA_WEB.'/view/@id',         'entidadesListado->View',          1],
            'resumen'        => ['1 '.self::RUTA_WEB.'/resumen/@id',      'entidadesListado->Resumen',       2],
            'resumen_update' => ['1 '.self::RUTA_WEB.'/resumenUpdate/@id', 'entidadesListado->ResumenUpdate', 2],
            'crear'          => ['2 '.self::RUTA_WEB,                     'entidadesListado->Insert',        3],
            'editar'         => ['2 '.self::RUTA_WEB.'/update',           'entidadesListado->Update',        2],
            'del_files'      => ['4 '.self::RUTA_WEB.'/delFiles',         'entidadesListado->DelFiles',      2],
            'borrar'         => ['3 '.self::RUTA_WEB,                     'entidadesListado->Delete',        4],
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
            $partes = explode('->', $rutas[$clave][0]);
            $this->addCase($results, 'ruta_'.$caso.'_metodo_existe',
                count($partes) === 2 && method_exists($partes[0], $partes[1]),
                'el handler '.$rutas[$clave][0].' no existe');
        }

        /*====================================*/
        // Rutas propias del controlador y matriz de verbos
        /*====================================*/
        $propias = array_filter($rutas, static function ($r) { return $r[2] === 'entidadesListado'; });
        $this->addCase($results, 'rutas_propias_completas', count($propias) === 11,
            'se esperaban 11 rutas de '.self::CONTROLADOR.' y hay '.count($propias));
        foreach ($propias as $clave => $datos) {
            $verbo = (int)substr($clave, 0, 1);
            if (str_ends_with($datos[0], '->Insert') || str_ends_with($datos[0], '->Update')) {
                $this->addCase($results, 'verbo_escritura_'.md5($clave), $verbo === 2, $datos[0].' debe publicarse con POST');
            }
            if (str_ends_with($datos[0], '->Delete')) {
                $this->addCase($results, 'verbo_borrado_'.md5($clave), $verbo === 3, $datos[0].' debe publicarse con DELETE');
            }
        }

        /*====================================*/
        // Instalador: permiso y desinstalacion
        /*====================================*/
        $instalador = $this->codigoFuente($this->rutaModulo('../installer/entidadesInstaller.php'));
        $this->addCase($results, 'instalador_permiso',
            str_contains($instalador, "'RutaWeb'        => '".self::RUTA_WEB."'")
            && str_contains($instalador, "'RutaController' => 'entidadesListado'"),
            'el instalador no declara el permiso con su ruta y controlador');
        $this->addCase($results, 'instalador_desinstala_controlador', str_contains($instalador, "\$RutaController .= ',\"entidadesListado\"'"),
            'RutaController() no lista entidadesListado y dejaria rutas huerfanas al desinstalar');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                PERMISOS                                    */
    /******************************************************************************/
    /**
     * Verifica la sesion, el nivel de acceso del controlador y el registro de sus rutas
     * en el router de F3 (solo si el usuario de pruebas las tiene).
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
            $arrUserData = $this->getUserData($f3);
            $this->addCase($results, 'get_user_data_arreglo', is_array($arrUserData),
                'getUserData() no devolvio un arreglo');
            $arrLevel = $this->getArrLevel($f3, self::CONTROLADOR);
            $this->addCase($results, 'get_arr_level_arreglo', is_array($arrLevel),
                'getArrLevel() no devolvio un arreglo');
        } catch (Throwable $e) {
            $this->addCase($results, 'get_arr_level_arreglo', false,
                'no se pudo leer la sesion del controlador ('.$e->getMessage().')');
            $arrUserData = [];
            $arrLevel    = [];
        }

        $nivel = is_array($arrLevel) ? ($arrLevel['LevelAccess'] ?? null) : null;
        $this->addCase($results, 'nivel_en_rango', $nivel === null || in_array((int)$nivel, self::NIVELES, true),
            'LevelAccess quedo fuera del catalogo 1..4');
        $this->addCase($results, 'ruta_de_acceso_coincide', $nivel === null || ($arrLevel['RouteAccess'] ?? '') === self::RUTA_WEB,
            'RouteAccess no coincide con '.self::RUTA_WEB);

        /*====================================*/
        // Marcas de modulo que condicionan las pestanas del detalle
        // (si el usuario tiene datos de sesion deben existir las cuatro claves)
        /*====================================*/
        if (is_array($arrUserData) && !empty($arrUserData) && is_array($UserData) && !empty($UserData)) {
            foreach (['entidadesListadoVerCargas', 'entidadesListadoVerContactos', 'entidadesListadoVerDocumentos', 'entidadesListadoUsoPassword'] as $marca) {
                $this->addCase($results, 'marca_'.$marca, array_key_exists($marca, $arrUserData),
                    'getUserData() no entrega la marca '.$marca.' que consumen las vistas');
            }
        }

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
            'ruta_listado_router'  => ['/'.self::RUTA_WEB_LISTADO, 'GET',    self::CONTROLADOR.'->listAll'],
            'ruta_busqueda_router' => ['/'.self::RUTA_WEB_SEARCH,  'POST',   self::CONTROLADOR.'->UpdateList'],
            'ruta_insert_router'   => ['/'.self::RUTA_WEB,         'POST',   self::CONTROLADOR.'->Insert'],
            'ruta_delete_router'   => ['/'.self::RUTA_WEB,         'DELETE', self::CONTROLADOR.'->Delete'],
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
     * Renderiza `List` y `UpdateList` con datos sinteticos y verifica la matriz de permisos
     * (Crear/Exportar con nivel >= 3, ver >= 1, editar >= 2, borrar >= 4), el nombre de la
     * entidad (persona natural y empresa), el ID cifrado y la tabla vacia.
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
        $this->addCase($results, 'listado_boton_filtrar', str_contains($html, 'data-bs-target="#formSearch"'),
            'falta el boton que despliega el filtro');
        $this->addCase($results, 'listado_sin_crear_nivel1', !str_contains($html, 'Crear Nuevo</button>'),
            'con nivel 1 el listado ofrece crear registros');
        $this->addCase($results, 'listado_sin_exportar_nivel1', !str_contains($html, '/exportar/'),
            'con nivel 1 el listado ofrece exportar datos');
        $this->addCase($results, 'listado_endpoint_ver', str_contains($html, '/view/'),
            'falta el endpoint de detalle');
        $this->addCase($results, 'listado_endpoint_actualizar', str_contains($html, '/updateList'),
            'falta el endpoint de refresco de tabla');
        $this->addCase($results, 'listado_borrado_verbo', str_contains($html, "let Metodo      = 'DELETE'"),
            'la accion de borrar no viaja con el verbo DELETE');
        $this->addCase($results, 'listado_borrado_id', str_contains($html, '{"idEntidad": ID}'),
            'la accion de borrar no envia idEntidad');

        /*====================================*/
        // Crear y exportar aparecen con nivel >= 3
        /*====================================*/
        $htmlNivel3 = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php'),
            $this->datosSinteticos(3), $BASE, $f3);
        $this->addCase($results, 'listado_crear_nivel3', str_contains($htmlNivel3, 'Crear Nuevo</button>'),
            'con nivel 3 no se muestra el boton Crear Nuevo');
        $this->addCase($results, 'listado_modal_nivel3', str_contains($htmlNivel3, 'data-bs-target="#newFormModal"'),
            'el boton de alta no apunta al modal #newFormModal');
        $this->addCase($results, 'listado_exportar_nivel3', str_contains($htmlNivel3, self::RUTA_WEB.'/exportar/'),
            'con nivel 3 no se ofrece exportar datos');

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
                str_contains($tabla, 'Entidad Prueba SpA') && str_contains($tabla, 'Sector Prueba') && str_contains($tabla, 'badge-sp1-success'),
                'la tabla no pinta la entidad, el sector y el estado sinteticos con nivel '.$nivel);
            $this->addCase($results, 'tabla_id_cifrado_nivel'.$nivel,
                !str_contains($tabla, "listTableDataView('91')") && !str_contains($tabla, '/resumen/91'),
                'la tabla expone el ID sin cifrar con nivel '.$nivel);
        }

        /*====================================*/
        // Nombre de la entidad: empresa con nick, persona natural y comillas
        /*====================================*/
        $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos(4), $BASE, $f3);
        $this->addCase($results, 'tabla_nick_destacado', str_contains($tabla, '<strong>prueba</strong> | Entidad Prueba SpA'),
            'la tabla no antepone el nick destacado a la razon social');

        $persona = $this->datosSinteticos(1);
        $persona['arrList'][0]['idTipoEntidad'] = 1;
        $persona['arrList'][0]['Nombre']        = 'Ana';
        $persona['arrList'][0]['ApellidoPat']   = 'Prueba';
        $persona['arrList'][0]['ApellidoMat']   = 'Test';
        $persona['arrList'][0]['Nick']          = '';
        $tablaPersona = $this->renderizarVista($rutaTabla, $persona, $BASE, $f3);
        $this->addCase($results, 'tabla_persona_natural', str_contains($tablaPersona, 'Prueba Test, Ana'),
            'la tabla no compone una persona natural como "ApellidoPat ApellidoMat, Nombre"');

        $comillas = $this->datosSinteticos(4);
        $comillas['arrList'][0]['RazonSocial'] = "Entidad d'Prueba";
        $tablaComillas = $this->renderizarVista($rutaTabla, $comillas, $BASE, $f3);
        $this->addCase($results, 'tabla_escapa_comillas', str_contains($tablaComillas, "Entidad d\\'Prueba"),
            'la etiqueta del borrado no escapa las comillas simples');

        /*====================================*/
        // Tabla vacia
        /*====================================*/
        $vacio = $this->datosSinteticos(4);
        $vacio['arrList'] = [];
        $tablaVacia = $this->renderizarVista($rutaTabla, $vacio, $BASE, $f3);
        $this->addCase($results, 'tabla_vacia', $tablaVacia !== '' && str_contains($tablaVacia, '<tbody>') && !str_contains($tablaVacia, 'listTableDataView('),
            'la tabla sin datos pinta filas o no se dibuja');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                        VISTAS DE DETALLE Y FORMULARIOS                     */
    /******************************************************************************/
    /**
     * Renderiza `formSearch`, `formNew`, `View` y `Exportar` con datos sinteticos y
     * comprueba el contrato de ids, campos, endpoints, verbos y pestanas por permiso.
     * Seguridad: el formulario de alta ya no envia la contrasena inicial; Insert() la asigna
     * en el servidor y marca PasswordForzarCambio; el cambio real se hace desde el Resumen
     * con la marca entidadesListadoUsoPassword.
     * `Resumen` y `Resumen-Update` (mapas y subida de imagen) se verifican por fuente.
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
        // Formulario de busqueda
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-formSearch.php'),
            $this->datosSinteticos(1), $BASE, $f3);
        $this->addCase($results, 'form_search_render', $html !== '', 'la vista formSearch no produjo HTML');
        $this->addCase($results, 'form_search_id', str_contains($html, 'id="FormSearchData"'),
            'el formulario de busqueda no usa el id FormSearchData');
        $faltan = [];
        foreach (['idTipo', 'idTipoEntidad', 'Nombre', 'ApellidoPat', 'ApellidoMat', 'Nick', 'idSexo', 'FNacimiento', 'RazonSocial', 'idCiudad', 'idComuna', 'Direccion', 'idSector', 'Email', 'idEstado'] as $campo) {
            if (!str_contains($html, 'id="Search_'.$campo.'"')) {
                $faltan[] = $campo;
            }
        }
        $this->addCase($results, 'form_search_inputs', count($faltan) === 0,
            'el formulario de busqueda no declara los campos Search_: '.implode(', ', $faltan));
        $this->addCase($results, 'form_search_endpoint',
            str_contains($html, self::RUTA_WEB.'/search') && str_contains($html, "let Metodo      = 'POST'"),
            'el formulario de busqueda no apunta a /search con POST');
        $this->addCase($results, 'form_search_dependiente', str_contains($html, 'Search_idCiudad') && str_contains($html, 'Search_idComuna'),
            'el filtro no ofrece ciudad y comuna dependientes');

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
        $faltan = [];
        foreach (['idTipo', 'idTipoEntidad', 'Nombre', 'ApellidoPat', 'ApellidoMat', 'RazonSocial', 'Rut', 'Email', 'idCiudad', 'idComuna', 'Direccion', 'idSector', 'idEstado'] as $campo) {
            if (!str_contains($html, 'name="'.$campo.'"')) {
                $faltan[] = $campo;
            }
        }
        $this->addCase($results, 'form_new_campos', count($faltan) === 0,
            'el formulario de alta no declara los campos: '.implode(', ', $faltan));
        $this->addCase($results, 'form_new_endpoint',
            str_contains($html, $BASE.'/'.self::RUTA_WEB."'") && str_contains($html, "let Metodo      = 'POST'"),
            'el formulario de alta no publica en '.self::RUTA_WEB.' con POST');
        $this->addCase($results, 'form_new_redirige_resumen', str_contains($html, self::RUTA_WEB.'/resumen/'),
            'el formulario de alta no redirige al resumen de la nueva entidad');
        // La contrasena inicial ya no viaja en el formulario: la asigna Insert() en el servidor
        $this->addCase($results, 'form_new_sin_password',
            !str_contains($html, 'name="password"') && !str_contains($html, '1234'),
            'el formulario de alta no debe enviar ni exponer la contrasena inicial');

        /*====================================*/
        // Modal de detalle (View): pestanas condicionadas por permisos de modulo
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'),
            $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'view_render', $html !== '', 'la vista View no produjo HTML');
        $this->addCase($results, 'view_pestana_basica', str_contains($html, 'id="view_tab_1"'),
            'la vista View no monta la pestana de datos basicos');
        $this->addCase($results, 'view_pestana_contactos', str_contains($html, 'id="view_tab_3"'),
            'con permiso de contactos la vista View no monta su pestana');
        $this->addCase($results, 'view_pestana_documentos', str_contains($html, 'id="view_tab_4"'),
            'con permiso de documentos la vista View no monta su pestana');
        $this->addCase($results, 'view_pestana_observaciones', str_contains($html, 'id="view_tab_5"') && str_contains($html, 'Observacion de prueba'),
            'la vista View no monta y pinta las observaciones');
        $this->addCase($results, 'view_sin_cargas_si_no_es_tipo_3', !str_contains($html, 'id="view_tab_2"'),
            'la pestana de cargas solo debe aparecer para entidades de tipo 3');

        $sinPermisos = $this->datosSinteticos(2);
        $sinPermisos['UserData']['entidadesListadoVerCargas']     = 1;
        $sinPermisos['UserData']['entidadesListadoVerContactos']  = 1;
        $sinPermisos['UserData']['entidadesListadoVerDocumentos'] = 1;
        $sinPermisos['rowData']['idTipo']                         = 3;
        $htmlSin = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'), $sinPermisos, $BASE, $f3);
        $this->addCase($results, 'view_sin_permisos_sin_pestanas',
            $htmlSin !== '' && !str_contains($htmlSin, 'id="view_tab_2"') && !str_contains($htmlSin, 'id="view_tab_3"') && !str_contains($htmlSin, 'id="view_tab_4"'),
            'sin las marcas de modulo la vista View sigue mostrando cargas, contactos o documentos');

        $conCargas = $this->datosSinteticos(2);
        $conCargas['rowData']['idTipo'] = 3;
        $htmlCargas = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'), $conCargas, $BASE, $f3);
        $this->addCase($results, 'view_cargas_tipo_3', str_contains($htmlCargas, 'id="view_tab_2"'),
            'con permiso y entidad de tipo 3 la vista View no monta la pestana de cargas');

        /*====================================*/
        // Exportar: tabla completa, accion solo con nivel >= 3
        /*====================================*/
        $rutaExport = $this->rutaModulo('../views/'.self::CONTROLADOR.'-Exportar.php');
        $datosExport = $this->datosSinteticos(3);
        $datosExport['arrList'] = [array_merge($datosExport['rowData'], ['Estado' => 'Activo'])];
        $html = $this->renderizarVista($rutaExport, $datosExport, $BASE, $f3);
        $this->addCase($results, 'export_render', $html !== '' && str_contains($html, 'id="tableData"'),
            'la vista Exportar no dibuja la tabla tableData');
        $this->addCase($results, 'export_columnas',
            str_contains($html, '>Rut<') && str_contains($html, '>Email<') && str_contains($html, '>Estado<'),
            'la vista Exportar no declara las columnas Rut, Email y Estado');
        $this->addCase($results, 'export_datos', str_contains($html, 'Entidad Prueba SpA') && str_contains($html, '76.000.000-0'),
            'la vista Exportar no pinta la entidad sintetica');
        $this->addCase($results, 'export_boton_nivel3', str_contains($html, "exportTableToExcel('tableData', 'Entidades')"),
            'con nivel 3 la vista Exportar no ofrece exportar a Excel');
        $datosExport1 = $datosExport;
        $datosExport1['UserAccess']['LevelAccess'] = 1;
        $html1 = $this->renderizarVista($rutaExport, $datosExport1, $BASE, $f3);
        $this->addCase($results, 'export_sin_boton_nivel1', $html1 !== '' && !str_contains($html1, 'exportTableToExcel('),
            'con nivel 1 la vista Exportar ofrece exportar a Excel');
        $this->addCase($results, 'export_sin_password', !str_contains($html, 'Contraseña') && !stripos($html, 'password'),
            'la vista Exportar expone la contrasena');

        /*====================================*/
        // Resumen y Resumen-Update por fuente (mapas externos y subida de imagen)
        /*====================================*/
        $resumen = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen.php'));
        $this->addCase($results, 'resumen_form_edicion', str_contains($resumen, 'id="FormEditData"'),
            'el resumen no monta el formulario de edicion');
        $this->addCase($results, 'resumen_id_oculto', str_contains($resumen, "'Name' => 'idEntidad'"),
            'el formulario de edicion no envia idEntidad');
        $this->addCase($results, 'resumen_endpoint_update', str_contains($resumen, "/update'") && str_contains($resumen, '/resumenUpdate/'),
            'el resumen no apunta a /update y /resumenUpdate');
        $this->addCase($results, 'resumen_id_cifrado', str_contains($resumen, "encryptDecrypt('encrypt', \$data['rowData']['idEntidad'])"),
            'el resumen no cifra idEntidad para las acciones');
        $this->addCase($results, 'resumen_password_condicionado', str_contains($resumen, 'entidadesListadoUsoPassword'),
            'el cambio de contrasena no depende de la marca entidadesListadoUsoPassword');
        $this->addCase($results, 'resumen_imagen',
            str_contains($resumen, "'Name' => 'Direccion_img'") && str_contains($resumen, 'delIMG('),
            'el resumen no ofrece subir y borrar la imagen de la entidad');
        $this->addCase($results, 'resumen_pestanas_subrecursos',
            str_contains($resumen, 'tabContactosLoadList()') && str_contains($resumen, 'tabDocumentosLoadList()'),
            'el resumen no carga las pestanas de contactos y documentos');

        $resumenUpdate = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen-Update.php'));
        $this->addCase($results, 'resumen_update_nombre_por_tipo',
            str_contains($resumenUpdate, 'case 1:') && str_contains($resumenUpdate, 'case 2:'),
            'el resumen no distingue persona natural (1) de empresa (2)');
        $this->addCase($results, 'resumen_update_mapa_condicionado', str_contains($resumenUpdate, "!='0'") && str_contains($resumenUpdate, 'Config_motorMap'),
            'el resumen no condiciona el mapa a coordenadas validas y al motor configurado');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos `dataCheck()` del controlador, el cifrado de IDs y la
     * validacion de IDs descifrados.
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

            $Post      = ['idEstado' => 1, 'idTipo' => 1, 'idTipoEntidad' => 2, 'RazonSocial' => 'Entidad Prueba SpA', 'Email' => 'prueba@example.test'];
            $DataCheck = $reflector->invoke($Controller, $Post);

            $this->addCase($results, 'datacheck_es_arreglo', is_array($DataCheck), 'dataCheck() no devolvio un arreglo');
            $this->addCase($results, 'datacheck_privado', $reflector->isPrivate(), 'dataCheck() debe ser privado');
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post,
                'dataCheck() no conserva el POST recibido');
            $this->addCase($results, 'datacheck_claves_contrato',
                isset($DataCheck['emptyData'], $DataCheck['ValidarEmail'], $DataCheck['ValidarNumero'], $DataCheck['ValidarEntero'], $DataCheck['ValidarRut'], $DataCheck['ValidarLargoMinimoN'], $DataCheck['ValidarLargoMaximoPorCampo']),
                'dataCheck() no entrega las claves del contrato de CheckData');
            $this->addCase($results, 'datacheck_emails',
                str_contains((string)$DataCheck['ValidarEmail'], 'Email') && str_contains((string)$DataCheck['ValidarEmail'], 'RepLegalEmail')
                && str_contains((string)$DataCheck['ValidarDominioEmail'], 'Email'),
                'dataCheck() no valida el formato y el dominio de Email y RepLegalEmail');
            $this->addCase($results, 'datacheck_ruts',
                str_contains((string)$DataCheck['ValidarRut'], 'Rut') && str_contains((string)$DataCheck['ValidarRut'], 'RepLegalRut'),
                'dataCheck() no valida Rut y RepLegalRut');
            $this->addCase($results, 'datacheck_ids_enteros',
                str_contains((string)$DataCheck['ValidarEntero'], 'idEstado') && str_contains((string)$DataCheck['ValidarEntero'], 'idTipoEntidad')
                && str_contains((string)$DataCheck['ValidarEntero'], 'idCiudad') && str_contains((string)$DataCheck['ValidarEntero'], 'idComuna'),
                'dataCheck() no valida los identificadores como enteros');
            $this->addCase($results, 'datacheck_telefonos',
                str_contains((string)$DataCheck['ValidarNumero'], 'Fono1') && str_contains((string)$DataCheck['ValidarNumero'], 'RepLegalFono2'),
                'dataCheck() no valida los telefonos como numeros');
            $this->addCase($results, 'datacheck_urls',
                str_contains((string)$DataCheck['ValidarURL'], 'Web') && str_contains((string)$DataCheck['ValidarURL'], 'Social_Linkedin'),
                'dataCheck() no valida las URL de la web y las redes sociales');
            $this->addCase($results, 'datacheck_fecha_nacimiento',
                str_contains((string)$DataCheck['ValidarFecha'], 'FNacimiento') && str_contains((string)$DataCheck['ValidarFechaRango'], 'FNacimiento'),
                'dataCheck() no valida la fecha de nacimiento y su rango');
            $this->addCase($results, 'datacheck_largos',
                (int)($DataCheck['ValidarLargoMinimoN'] ?? 0) === 3
                && (int)($DataCheck['ValidarLargoMaximoPorCampo']['password'] ?? 0) === 255
                && str_contains((string)$DataCheck['ValidarLargoMinimo'], 'password'),
                'dataCheck() no fija los limites de largo 3..255 incluyendo password');
            $this->addCase($results, 'datacheck_censura',
                str_contains((string)$DataCheck['ValidarPalabrasCensuradas'], 'RazonSocial') && str_contains((string)$DataCheck['ValidarPalabrasCensuradas'], 'Nombre'),
                'dataCheck() no filtra palabras censuradas en nombres y razon social');
            $this->addCase($results, 'datacheck_sin_espacios_password', str_contains((string)$DataCheck['ValidarEspaciosVacios'], 'password'),
                'dataCheck() no rechaza espacios en el password');
            $this->addCase($results, 'datacheck_ip_cliente', str_contains((string)$DataCheck['ValidarIP'], 'IP_Client'),
                'dataCheck() no valida la IP del cliente');
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_es_arreglo', false,
                'no se pudo ejecutar dataCheck() ('.$e->getMessage().')');
        }

        /*====================================*/
        // Cifrado de identificadores: ida y vuelta estable y rechazo de basura
        /*====================================*/
        try {
            $Codification = new FunctionsSecurityCodification();
            $cifrado      = $Codification->encryptDecrypt('encrypt', '91');
            $this->addCase($results, 'cifrado_genera_dato',
                ($cifrado['success'] ?? false) === true && !empty($cifrado['data']),
                'encryptDecrypt(encrypt) no produjo un valor cifrado');

            $descifrado = $Codification->encryptDecrypt('decrypt', (string)($cifrado['data'] ?? ''));
            $this->addCase($results, 'cifrado_ida_y_vuelta',
                ($descifrado['success'] ?? false) === true && (string)($descifrado['data'] ?? '') === '91',
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
        $this->addCase($results, 'id_valido_tipo_texto', $this->isValidDecrypted(['success' => true, 'data' => 'abc'], 'text') === true,
            'un texto no vacio debe ser valido para tipo text');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                 SEGURIDAD                                  */
    /******************************************************************************/
    /**
     * Revisa estaticamente las vistas y el controlador: rutas relativas, rutas absolutas,
     * acceso a sesion, SQL embebido, cifrado de IDs, escape y consultas preparadas, y que
     * la contrasena nunca se consulte ni se pinte.
     *
     * @return array Resultado por caso
     */
    public function seguridadVistasTest(){

        $results = [];

        /*====================================*/
        // Vistas involucradas (solo las del controlador, no las de sub-recursos)
        /*====================================*/
        $arrVistas = glob($this->rutaModulo('../views/'.self::CONTROLADOR.'-*.php')) ?: [];
        $arrVistas = array_values(array_filter($arrVistas, static function ($r) {
            return !preg_match('/-Resumen-(Cargas|Contactos|Documentos|Observaciones)-/', basename($r));
        }));
        $this->addCase($results, 'hay_vistas', count($arrVistas) === 8,
            'se esperaban 8 vistas propias de '.self::CONTROLADOR.' y se encontraron '.count($arrVistas));

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
            $this->addCase($results, 'vista_sin_password_'.$nombre,
                !preg_match('/rowData\'\]\[\'password\'\]|crud\[\'password\'\]/i', $codigo),
                $nombre.' lee el hash de la contrasena');
        }

        /*====================================*/
        // Escape y cifrado en la tabla
        /*====================================*/
        $tabla = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'));
        $this->addCase($results, 'tabla_escape_etiqueta', str_contains($tabla, 'addslashes($Entidad)'),
            'UpdateList no escapa la etiqueta que viaja al JavaScript');
        $this->addCase($results, 'tabla_id_cifrado', str_contains($tabla, "encryptDecrypt('encrypt', \$crud['idEntidad'])"),
            'UpdateList no cifra idEntidad en las acciones');

        /*====================================*/
        // Controlador: SQL preparado y sin concatenacion de entrada
        /*====================================*/
        $controlador = $this->fuenteControlador();
        $this->addCase($results, 'controlador_sin_concat_sql', !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $controlador),
            'el controlador concatena superglobales en una cadena SQL');
        $this->addCase($results, 'controlador_parametros_preparados', preg_match_all("/'params'\s*=>/", $controlador) === 28,
            'las 28 consultas deben declarar la clave params (consultas preparadas)');
        $this->addCase($results, 'controlador_sin_get', !str_contains($controlador, '$_GET') && !str_contains($controlador, '$_REQUEST'),
            'el controlador lee $_GET/$_REQUEST; solo debe usar $_POST y parametros de ruta cifrados');
        $this->addCase($results, 'controlador_sin_password_en_consultas',
            !preg_match("/'data'\s*=>\s*'[^']*entidades_listado\.password/s", $controlador),
            'alguna consulta de lectura trae la contrasena de la entidad');

        /************************************/
        // Retorno los datos
        return $results;

    }

}
