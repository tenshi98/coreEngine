<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class tercerosEntidadesListadoTesting
 *
 * Pruebas del modulo `terceros` - controlador `tercerosEntidadesListado` (listado de clientes:
 * consulta, filtro, resumen y edicion de datos basicos).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. El normalizador
 *    `sistemaTesteos::normalizeResult()` marca EXITOSA o ERROR segun el contenido, por lo que
 *    los textos de los casos correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Alcance: pruebas NO destructivas. `Update()` escribe en base de datos y termina en
 * `Response::success()`/`Response::error()` (que ejecutan `exit`); por eso NO se invoca: se
 * verifica por reflexion y por lectura del fuente. `InstallModule()`/`UninstallModule()` del
 * instalador tampoco se ejecutan: solo se lee `getRouteDefinitions()` sin conexion a BD.
 *
 * Nota: las vistas del modulo comparten el prefijo `tercerosEntidadesListado-` con los
 * sub-controladores (Planes, Usuarios, Maquinas...). Por eso las pruebas de seguridad usan una
 * lista explicita de las vistas propias de este controlador y no un glob del prefijo.
 *
 * @see skills/testing/SKILL.md
 */
class tercerosEntidadesListadoTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR      = 'tercerosEntidadesListado';
    private const INSTALADOR       = 'tercerosEntidadesInstaller';
    private const RUTA_WEB         = 'serviciosTerceros/entidades/listado';
    private const RUTA_WEB_LISTADO = 'serviciosTerceros/entidades/listado/listAll';
    private const RUTA_WEB_SEARCH  = 'serviciosTerceros/entidades/listado/search';
    private const RUTA_WEB_UPDATE  = 'serviciosTerceros/entidades/listado/update';
    // Niveles de permiso del catalogo (1=ver, 2=editar, 3=crear, 4=borrar)
    private const NIVELES          = [1, 2, 3, 4];
    // Vistas propias de este controlador (el prefijo es compartido con los sub-controladores)
    private const VISTAS_PROPIAS   = ['List', 'UpdateList', 'UpdateListRows', 'View', 'Resumen', 'Resumen-Update', 'formSearch'];

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
    // Fuente del controlador completo
    /*******************************************************************/
    private function fuenteControlador(): string{
        return $this->codigoFuente($this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'));
    }

    /*******************************************************************/
    // Fuente de UN metodo del controlador (para no confundir metodos entre si)
    /*******************************************************************/
    private function cuerpoMetodo(string $metodo): string{
        if (!method_exists(self::CONTROLADOR, $metodo)) {
            return '';
        }
        $reflector = new ReflectionMethod(self::CONTROLADOR, $metodo);
        $lineas    = file($reflector->getFileName(), FILE_IGNORE_NEW_LINES) ?: [];
        $inicio    = $reflector->getStartLine() - 1;
        $largo     = $reflector->getEndLine() - $inicio;

        return implode("\n", array_slice($lineas, $inicio, $largo));
    }

    /*******************************************************************/
    // Rutas del instalador para este controlador (sin conexion a BD):
    // [idMetodo.' '.RutaWeb => [handler, nivel]]
    /*******************************************************************/
    private function rutasInstalador(): array{
        $instalador = (new ReflectionClass(self::INSTALADOR))->newInstanceWithoutConstructor();
        $rutas      = [];
        foreach ($instalador->getRouteDefinitions(1) as $ruta) {
            if (($ruta['Controller'] ?? '') !== self::CONTROLADOR) {
                continue;
            }
            $rutas[$ruta['idMetodo'].' '.$ruta['RutaWeb']] = [$ruta['RutaController'], (int)$ruta['idLevelLimit']];
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
    // Ruta de una vista propia del controlador
    /*******************************************************************/
    private function rutaVista(string $sufijo): string{
        return $this->rutaModulo('../views/'.self::CONTROLADOR.'-'.$sufijo.'.php');
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
    // Contrato de presentacion de un metodo (vista, errores y usuario),
    // evaluado sobre el cuerpo del propio metodo
    /*******************************************************************/
    private function contratoPresentacion(array &$results, string $metodo, string $vista, string $tipoVista){
        $codigo = $this->cuerpoMetodo($metodo);
        $this->addCase($results, 'vista_'.$metodo, str_contains($codigo, $vista),
            $metodo.'() no instancia la vista '.$vista);
        $this->addCase($results, 'tipoview_'.$metodo, str_contains($codigo, 'showVista('.$tipoVista.','),
            $metodo.'() no usa showVista('.$tipoVista.')');
        $this->addCase($results, 'rutavista_'.$metodo, str_contains($codigo, 'returnRutaVista('),
            $metodo.'() no resuelve la ruta de la vista');
        $this->addCase($results, 'rutaruta_'.$metodo, str_contains($codigo, '$this->controllerName'),
            $metodo.'() no referencia $this->controllerName');
        $this->addCase($results, 'errores_'.$metodo, str_contains($codigo, 'showError('.$tipoVista.','),
            $metodo.'() no maneja la ruta de error con showError('.$tipoVista.')');
        $this->addCase($results, 'usuario_'.$metodo, str_contains($codigo, 'getUserData($f3)'),
            $metodo.'() no obtiene los datos del usuario');
        $this->addCase($results, 'nivel_'.$metodo, str_contains($codigo, 'getArrLevel($f3, $this->controllerName)'),
            $metodo.'() no resuelve el nivel de acceso del usuario');
    }

    /*******************************************************************/
    // Datos sinteticos usados por las vistas del controlador
    /*******************************************************************/
    private function datosSinteticos(int $nivel, int $planes = 2, int $usuarios = 2, int $maquinas = 2): array{
        return [
            'PageTitle'       => 'Listado Clientes',
            'PageDescription' => 'Listado Clientes.',
            'PageAuthor'      => 'Pruebas',
            'PageKeywords'    => 'Pruebas',
            'TableTitle'      => 'Listado Clientes',
            'UserData'        => [
                'MainPathUrl'                => '/admin/public/upload/',
                'sistemaModalSubtitle'       => 1,
                'sistemaModalCloseBTN'       => 2,
                'UserType'                   => 2,
                'entidadesListadoUsoPlanes'    => $planes,
                'entidadesListadoUsoUsuarios'  => $usuarios,
                'entidadesListadoUsoMaquinas'  => $maquinas,
                'entidadesListadoUsoPassword'  => 2,
            ],
            'UserAccess'      => ['LevelAccess' => $nivel, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_FormInputs'    => new UIFormInputs(),
            'Fnc_Codification'  => new FunctionsSecurityCodification(),
            'Fnc_DataDate'      => new FunctionsDataDate(),
            'Fnc_DataNumbers'   => new FunctionsDataNumbers(),
            'Fnc_WidgetsCommon' => new UIWidgetsCommon(),
            'Fnc_WidgetsViews'  => new UIWidgetsViews(),
            'arrList'         => [
                [
                    'idEntidad'     => 1,
                    'idTipoEntidad' => 1,
                    'Nombre'        => 'Juan',
                    'ApellidoPat'   => 'Perez',
                    'ApellidoMat'   => 'Soto',
                    'RazonSocial'   => '',
                    'Nick'          => 'jperez',
                    'Estado'        => 'Activo',
                    'EstadoColor'   => 'success',
                    'Sector'        => 'Retail',
                    'TipoEntidad'   => 'Persona Natural',
                ],
                [
                    'idEntidad'     => 2,
                    'idTipoEntidad' => 2,
                    'Nombre'        => '',
                    'ApellidoPat'   => '',
                    'ApellidoMat'   => '',
                    'RazonSocial'   => 'Empresa Demo SpA',
                    'Nick'          => '',
                    'Estado'        => 'Inactivo',
                    'EstadoColor'   => 'danger',
                    'Sector'        => 'Mineria',
                    'TipoEntidad'   => 'Empresa',
                ],
            ],
            'arrTipoEntidad'  => [['ID' => 1, 'Nombre' => 'Persona Natural'], ['ID' => 2, 'Nombre' => 'Empresa']],
            'arrSexo'         => [['ID' => 1, 'Nombre' => 'Masculino']],
            'arrCiudad'       => [['ID' => 1, 'Nombre' => 'Santiago']],
            'arrComuna'       => [['ID1' => 1, 'ID2' => 1, 'Nombre' => 'Providencia']],
            'arrSector'       => [['ID' => 1, 'Nombre' => 'Retail']],
            'arrEstado'       => [['ID' => 1, 'Nombre' => 'Activo', 'Color' => 'success']],
            'arrPlanes'       => [[
                'idPlan'      => 1,
                'Fecha'       => '2026-01-02',
                'Monto'       => 15000,
                'Servicio'    => 'Servicio Demo',
                'Estado'      => 'Activo',
                'EstadoColor' => 'success',
            ]],
            'arrUsuarios'     => [[
                'email'         => 'usuario.demo@example.com',
                'Nombre'        => 'Usuario Demo',
                'Ultimo_acceso' => '2026-01-03',
                'Estado'        => 'Activo',
                'EstadoColor'   => 'success',
            ]],
            'rowData'         => [
                'idEntidad'     => 1,
                'idTipoEntidad' => 1,
                'Nombre'        => 'Juan',
                'ApellidoPat'   => 'Perez',
                'ApellidoMat'   => 'Soto',
                'RazonSocial'   => 'Empresa Demo SpA',
                'Nick'          => 'jperez',
                'Direccion_img' => '',
                'Sexo'          => 'Masculino',
                'TipoEntidad'   => 'Persona Natural',
                'Rut'           => '11111111-1',
                'Direccion'     => 'Av. Siempreviva 742',
                'FNacimiento'   => '1990-05-20',
                'Email'         => 'juan@example.com',
                'Fono1'         => '56911111111',
                'Fono2'         => '56222222222',
                'Web'           => 'https://example.com',
                'Giro'          => 'Giro de prueba',
                'RepLegalNombre' => 'Representante Demo',
                'RepLegalRut'   => '22222222-2',
                'RepLegalEmail' => 'rep@example.com',
                'RepLegalFono1' => '56933333333',
                'RepLegalFono2' => '56244444444',
                'Social_X'      => 'https://x.com/demo',
                'Social_Facebook'  => 'https://facebook.com/demo',
                'Social_Instagram' => 'https://instagram.com/demo',
                'Social_Linkedin'  => 'https://linkedin.com/in/demo',
                'idEstado'      => 1,
                'idSector'      => 1,
                'idSexo'        => 1,
                'idTipo'        => 2,
                'idCiudad'      => 1,
                'idComuna'      => 1,
            ],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica la estructura de carpetas y archivos del modulo `terceros` para el controlador
     * `tercerosEntidadesListado` y la convencion "clase = nombre de archivo" exigida por el
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
            'controlador' => $this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'),
            'instalador'  => $this->rutaModulo('../installer/'.self::INSTALADOR.'.php'),
            'testing'     => $this->rutaModulo('tercerosEntidadesListadoTesting.php'),
            'readme'      => $this->rutaModulo('../README.md'),
        ];
        foreach (self::VISTAS_PROPIAS as $vista) {
            $arrArchivos['vista_'.$vista] = $this->rutaVista($vista);
        }
        foreach ($arrArchivos as $nombre => $ruta) {
            $this->addCase($results, 'archivo_'.$nombre, is_file($ruta), 'no se encuentra '.$ruta);
        }

        /*====================================*/
        // Proteccion de acceso directo
        /*====================================*/
        $arrHtaccess = [
            'raiz'       => $this->rutaModulo('../.htaccess'),
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
        $this->addCase($results, 'clase_instalador_cargada', class_exists(self::INSTALADOR),
            'la clase '.self::INSTALADOR.' no esta disponible');
        $this->addCase($results, 'clase_testing_cargada', class_exists('tercerosEntidadesListadoTesting'),
            'la clase tercerosEntidadesListadoTesting no esta disponible');

        /*====================================*/
        // Herencias esperadas por el proyecto
        /*====================================*/
        $padreController = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padreController === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padreController.')');
        $padreInstalador = class_exists(self::INSTALADOR) ? get_parent_class(self::INSTALADOR) : '';
        $this->addCase($results, 'herencia_instalador', $padreInstalador === 'ControllerInstaller',
            'el instalador no extiende ControllerInstaller (extiende '.$padreInstalador.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('tercerosEntidadesListadoTesting') === 'ControllerBase',
            'el testing no extiende ControllerBase');

        /*====================================*/
        // El constructor del controlador arma las dependencias de las vistas
        /*====================================*/
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'constructor_conexion', str_contains($codigo, 'ConfigDataBase::MySQL_1'),
            'el constructor no usa la conexion ConfigDataBase::MySQL_1');
        $this->addCase($results, 'constructor_nombre',
            str_contains($codigo, "\$this->controllerName = '".self::CONTROLADOR."'"),
            'controllerName no coincide con el nombre de la clase');
        $this->addCase($results, 'constructor_padre', str_contains($codigo, 'parent::__construct($DB_conn_1, $queryBuilder, $checkData)'),
            'el constructor no delega en ControllerBase');

        /*====================================*/
        // Soporte de deteccion de tipos de archivo (subida de imagen Direccion_img)
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
     * `View`, `Resumen`, `ResumenUpdate`): existencia, visibilidad, firma y uso de
     * `showVista()`/`returnRutaVista()`/`getUserData()`, evaluados sobre el cuerpo de cada metodo.
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
        $this->contratoPresentacion($results, 'Resumen', "\$this->controllerName.'-Resumen.php'", '1');
        $this->contratoPresentacion($results, 'ResumenUpdate', "\$this->controllerName.'-Resumen-Update.php'", '2');

        /*====================================*/
        // listAll: metadatos de la pagina y catalogos del filtro
        /*====================================*/
        $listAll = $this->cuerpoMetodo('listAll');
        $this->addCase($results, 'listado_titulo', str_contains($listAll, "'PageTitle'"),
            'listAll() no asigna PageTitle');
        $this->addCase($results, 'listado_tabla', str_contains($listAll, "'TableTitle'"),
            'listAll() no asigna TableTitle');
        $this->addCase($results, 'listado_fallback_errores', str_contains($listAll, 'mergeResponses('),
            'listAll() no agrupa las respuestas fallidas con mergeResponses()');
        $this->addCase($results, 'listado_solo_clientes', str_contains($listAll, "'where'   => 'entidades_listado.idTipo = ?'"),
            'listAll() no limita el listado al tipo de entidad cliente (idTipo)');
        $this->addCase($results, 'listado_limite', str_contains($listAll, 'N_MaxItems') && str_contains($listAll, "'limit'   => \$page['limit']") && str_contains($listAll, '$this->getPageParams()') && str_contains($listAll, '$this->sliceMore('),
            'listAll() debe acotar la lista principal con getPageParams()/sliceMore() (carga incremental, ADR-0022) y los catalogos con N_MaxItems');
        $catalogos = ['core_estados', 'entidades_sectores', 'core_sexo', 'core_tipos_entidades', 'core_ubicacion_ciudad', 'core_ubicacion_comunas'];
        foreach ($catalogos as $tabla) {
            $this->addCase($results, 'listado_catalogo_'.$tabla, str_contains($listAll, "'table'   => '".$tabla."'"),
                'listAll() no consulta el catalogo '.$tabla);
        }
        foreach (['arrList', 'arrTipoEntidad', 'arrSexo', 'arrCiudad', 'arrComuna', 'arrSector', 'arrEstado'] as $clave) {
            $this->addCase($results, 'listado_entrega_'.$clave, str_contains($listAll, "'".$clave."'"),
                'listAll() no entrega '.$clave.' a la vista');
        }

        /*====================================*/
        // Resumen: entrega los datos y catalogos que necesita el formulario de edicion
        /*====================================*/
        $resumen = $this->cuerpoMetodo('Resumen');
        foreach (['arrEstado', 'arrSector', 'arrSexo', 'arrTipoEntidad', 'arrCiudad', 'arrComuna'] as $clave) {
            $this->addCase($results, 'resumen_entrega_'.$clave, str_contains($resumen, "'".$clave."'"),
                'Resumen() no entrega '.$clave.' a la vista');
        }
        $this->addCase($results, 'resumen_fallback_catalogos',
            str_contains($resumen, 'mergeResponses([$rowData,$arrEstado,$arrSector,$arrSexo,$arrTipoEntidad,$arrCiudad,$arrComuna])'),
            'Resumen() no agrupa los errores de los catalogos con mergeResponses()');
        $columnasEdicion = ['Rut', 'Direccion', 'FNacimiento', 'Email', 'Fono1', 'Fono2', 'Web', 'Giro', 'RepLegalNombre', 'RepLegalRut',
                            'RepLegalEmail', 'RepLegalFono1', 'RepLegalFono2', 'Social_X', 'Social_Facebook', 'Social_Instagram',
                            'Social_Linkedin', 'idEstado', 'idSector', 'idSexo', 'idTipo', 'idTipoEntidad', 'idCiudad', 'idComuna'];
        $faltantes = [];
        foreach ($columnasEdicion as $columna) {
            if (!str_contains($resumen, 'entidades_listado.'.$columna.',')) {
                $faltantes[] = $columna;
            }
        }
        $this->addCase($results, 'resumen_columnas_edicion', $faltantes === [],
            'Resumen() no consulta las columnas: '.implode(',', $faltantes));

        /*====================================*/
        // UpdateList: el filtro y el limite al tipo cliente se combinan
        /*====================================*/
        $updateList = $this->cuerpoMetodo('UpdateList');
        $this->addCase($results, 'filtro_valida_fechas', str_contains($updateList, 'searchValidateDates('),
            'UpdateList() no valida las fechas de busqueda');
        $this->addCase($results, 'filtro_where_exacto', str_contains($updateList, "'entidades_listado', 1)"),
            'UpdateList() no aplica la busqueda exacta (searchWhere tipo 1)');
        $this->addCase($results, 'filtro_where_relativo', str_contains($updateList, "'entidades_listado', 2)"),
            'UpdateList() no aplica la busqueda relativa (searchWhere tipo 2)');
        $this->addCase($results, 'filtro_mantiene_tipo_cliente',
            str_contains($updateList, "'entidades_listado.idTipo = ?'") && str_contains($updateList, 'array_merge($whereParams, [2])'),
            'UpdateList() no fuerza idTipo=2 como parametro preparado');
        $this->addCase($results, 'filtro_parametros', str_contains($updateList, "'params'  => \$whereParams"),
            'UpdateList() no envia los valores del filtro como parametros preparados');

        /*====================================*/
        // View: los datos de planes y usuarios dependen de permisos del usuario
        /*====================================*/
        $view = $this->cuerpoMetodo('View');
        $this->addCase($results, 'view_permiso_planes', str_contains($view, '$arrUserData["entidadesListadoUsoPlanes"]==2'),
            'View() no condiciona los planes al permiso entidadesListadoUsoPlanes');
        $this->addCase($results, 'view_permiso_usuarios', str_contains($view, '$arrUserData["entidadesListadoUsoUsuarios"]==2'),
            'View() no condiciona los usuarios al permiso entidadesListadoUsoUsuarios');
        $this->addCase($results, 'view_sin_permiso_vacio',
            substr_count($view, "['data']   = [];") >= 2,
            'View() debe devolver listas vacias cuando el usuario no tiene el permiso');
        $this->addCase($results, 'view_entrega_listas',
            str_contains($view, "'arrPlanes'") && str_contains($view, "'arrUsuarios'") && str_contains($view, "'rowData'"),
            'View() no entrega rowData, arrPlanes y arrUsuarios a la vista');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                         CONTROLADOR: ESCRITURA                             */
    /******************************************************************************/
    /**
     * Verifica el contrato de `Update()` (unico metodo de escritura del controlador) SIN
     * ejecutarlo: termina en `Response::*()` (que hace `exit`) y escribe en base de datos.
     * Se comprueba firma, verbo HTTP, chequeo de datos, transaccion y manejo de la imagen.
     * Tambien se verifica que el controlador NO exponga Insert/Delete (el alta y el borrado
     * no pertenecen a este modulo).
     *
     * @return array Resultado por caso
     */
    public function controladorEscrituraTest(){

        $results = [];

        /*====================================*/
        // Firma de Update
        /*====================================*/
        $this->firmaMetodo($results, 'Update', 0, 0);
        $update = $this->cuerpoMetodo('Update');

        /*====================================*/
        // Verbo HTTP y respuesta de verbo invalido
        /*====================================*/
        $this->addCase($results, 'update_verbo_post', str_contains($update, "REQUEST_METHOD'] !== 'POST'"),
            'Update() no valida el verbo HTTP POST');
        $this->addCase($results, 'update_verbo_405', str_contains($update, "Response::error('Error en el Request Method', 405)"),
            'Update() no responde 405 ante un verbo invalido');

        /*====================================*/
        // Chequeo de datos y definicion de la escritura
        /*====================================*/
        $this->addCase($results, 'update_datacheck', str_contains($update, '$this->dataCheck($_POST)'),
            'Update() no construye el DataCheck con dataCheck($_POST)');
        $this->addCase($results, 'update_tabla', str_contains($update, "'table'     => 'entidades_listado'"),
            'Update() no apunta a la tabla entidades_listado');
        $this->addCase($results, 'update_donde', str_contains($update, "'where'     => 'idEntidad'"),
            'Update() no delimita la escritura por idEntidad');
        $this->addCase($results, 'update_requeridos', str_contains($update, "'required'  => 'idEntidad,idEstado,idTipo,idTipoEntidad'"),
            'Update() no exige idEntidad, idEstado, idTipo e idTipoEntidad');
        $this->addCase($results, 'update_unicos', str_contains($update, "'unique'    => 'Rut,Email,Fono1,Fono2'"),
            'Update() no valida la unicidad de Rut, Email, Fono1 y Fono2');
        $this->addCase($results, 'update_password_codificada', str_contains($update, "'encode'    => 'password'"),
            'Update() no codifica la clave antes de guardarla');
        $this->addCase($results, 'update_columnas_sensibles',
            str_contains($update, 'idEntidad,idEstado,idSector') && str_contains($update, 'PasswordForzarCambio,PasswordUltimoCambio'),
            'Update() no declara la lista de columnas editables');
        $this->addCase($results, 'update_post_en_query', str_contains($update, "'Post'      => \$_POST"),
            'Update() no pasa el POST al constructor de la query');

        /*====================================*/
        // Cambio de clave: obliga a cambiarla en el primer ingreso
        /*====================================*/
        $this->addCase($results, 'update_clave_forzar_cambio',
            str_contains($update, "\$_POST['PasswordForzarCambio'] = 1") && str_contains($update, "\$_POST['PasswordUltimoCambio']"),
            'Update() no fuerza el cambio de clave tras modificarla');
        $this->addCase($results, 'update_clave_solo_si_viene',
            str_contains($update, "isset(\$_POST['password'])&&\$_POST['password']!=''"),
            'Update() fuerza el cambio de clave aunque no se haya enviado una nueva');

        /*====================================*/
        // Imagen de la entidad (Direccion_img)
        /*====================================*/
        $this->addCase($results, 'update_archivo_imagen',
            str_contains($update, "'files'") && str_contains($update, "'Identificador' => 'Direccion_img'"),
            'Update() no declara el manejo del archivo Direccion_img');
        $this->addCase($results, 'update_archivo_valida_tipo', str_contains($update, "'ValidarTipo'   => 'image'"),
            'Update() no restringe el archivo a imagenes');
        $this->addCase($results, 'update_archivo_valida_peso', str_contains($update, "'ValidarPeso'   => 10"),
            'Update() no limita el peso del archivo');

        /*====================================*/
        // Transaccion completa: begin -> update -> failOperation -> commit -> respuesta
        /*====================================*/
        $posBegin  = (int)strpos($update, 'Base_transactionBegin()');
        $posUpdate = (int)strpos($update, '$this->Base_update(');
        $posFail   = (int)strpos($update, 'failOperation(');
        $posCommit = (int)strpos($update, 'Base_transactionCommit()');
        $posResp   = (int)strpos($update, 'Response::success(');
        $this->addCase($results, 'update_transaccion_completa',
            $posBegin > 0 && $posUpdate > 0 && $posFail > 0 && $posCommit > 0 && $posResp > 0,
            'Update() debe usar Base_transactionBegin, Base_update, failOperation, Base_transactionCommit y Response::success');
        $this->addCase($results, 'update_transaccion_orden',
            $posBegin < $posUpdate && $posUpdate < $posFail && $posFail < $posCommit && $posCommit < $posResp,
            'Update() ejecuta la transaccion en un orden incorrecto');
        $this->addCase($results, 'update_revierte_si_falla', str_contains($update, "['status'] === false"),
            'Update() no revisa el estado de Base_update antes de confirmar');

        /*====================================*/
        // El controlador no expone alta ni borrado
        /*====================================*/
        foreach (['Insert', 'Delete', 'delFiles'] as $metodo) {
            $this->addCase($results, 'sin_'.$metodo, !method_exists(self::CONTROLADOR, $metodo),
                'el controlador expone '.$metodo.'(), que no forma parte de este modulo');
        }

        /*====================================*/
        // Identificadores cifrados: se descifran y validan antes de consultar
        /*====================================*/
        foreach (['View', 'Resumen', 'ResumenUpdate'] as $metodo) {
            $cuerpo = $this->cuerpoMetodo($metodo);
            $this->addCase($results, 'id_descifrado_'.$metodo,
                str_contains($cuerpo, "encryptDecrypt('decrypt', \$params['id'])"),
                $metodo.'() no descifra $params[\'id\']');
            $this->addCase($results, 'id_valido_'.$metodo, str_contains($cuerpo, "isValidDecrypted(\$EntidadID, 'id')"),
                $metodo.'() no valida el ID con isValidDecrypted()');
            $this->addCase($results, 'id_invalido_400_'.$metodo, str_contains($cuerpo, "Response::error('Registro inválido', 400)"),
                $metodo.'() no responde 400 ante un ID invalido');
            $this->addCase($results, 'id_parametro_preparado_'.$metodo,
                str_contains($cuerpo, "'params'  => [\$EntidadID['data']]"),
                $metodo.'() no consulta con el ID como parametro preparado');
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                       CONTROLADOR: RUTAS DEL INSTALADOR                    */
    /******************************************************************************/
    /**
     * Compara las rutas que registra `tercerosEntidadesInstaller::getRouteDefinitions()` para este
     * controlador con las esperadas: verbo, handler, nivel de permiso. La lectura se hace sin
     * conexion a base de datos y NO se ejecuta `InstallModule()`.
     *
     * @return array Resultado por caso
     */
    public function controladorRutasTest(){

        $results = [];

        try {
            $rutas = $this->rutasInstalador();
        } catch (Throwable $e) {
            $this->addCase($results, 'instalador_rutas_legibles', false,
                'no se pudieron leer las rutas del instalador ('.$e->getMessage().')');
            return $results;
        }
        $this->addCase($results, 'instalador_rutas_legibles', count($rutas) > 0,
            'el instalador no registra rutas para '.self::CONTROLADOR);

        /*====================================*/
        // Rutas esperadas: 'verbo ruta' => [handler, nivel]  (1=GET, 2=POST, 3=DELETE)
        /*====================================*/
        $esperadas = [
            '1 '.self::RUTA_WEB.'/listAll'          => [self::CONTROLADOR.'->listAll', 1],
            '2 '.self::RUTA_WEB.'/search'           => [self::CONTROLADOR.'->UpdateList', 1],
            '1 '.self::RUTA_WEB.'/updateList'       => [self::CONTROLADOR.'->UpdateList', 2],
            '1 '.self::RUTA_WEB.'/view/@id'         => [self::CONTROLADOR.'->View', 1],
            '1 '.self::RUTA_WEB.'/resumen/@id'      => [self::CONTROLADOR.'->Resumen', 2],
            '1 '.self::RUTA_WEB.'/resumenUpdate/@id' => [self::CONTROLADOR.'->ResumenUpdate', 2],
            '2 '.self::RUTA_WEB.'/update'           => [self::CONTROLADOR.'->Update', 2],
        ];
        foreach ($esperadas as $clave => $esperado) {
            $this->addCase($results, 'ruta_registrada_'.$clave, isset($rutas[$clave]),
                'el instalador no registra la ruta '.$clave);
            if (!isset($rutas[$clave])) {
                continue;
            }
            $this->addCase($results, 'ruta_handler_'.$clave, $rutas[$clave][0] === $esperado[0],
                'la ruta '.$clave.' apunta a '.$rutas[$clave][0].' en vez de '.$esperado[0]);
            $this->addCase($results, 'ruta_nivel_'.$clave, $rutas[$clave][1] === $esperado[1],
                'la ruta '.$clave.' exige nivel '.$rutas[$clave][1].' en vez de '.$esperado[1]);
        }
        $this->addCase($results, 'rutas_sin_extras', count($rutas) === count($esperadas),
            'el instalador registra '.count($rutas).' rutas y se esperaban '.count($esperadas));

        /*====================================*/
        // Reglas generales de las rutas
        /*====================================*/
        $sinBorrado = true;
        $nivelesOk  = true;
        $existenMetodos = true;
        foreach ($rutas as $clave => [$handler, $nivel]) {
            if (str_starts_with($clave, '3 ')) {
                $sinBorrado = false;
            }
            if (!in_array($nivel, self::NIVELES, true)) {
                $nivelesOk = false;
            }
            [$clase, $metodo] = explode('->', $handler) + [1 => ''];
            if ($clase !== self::CONTROLADOR || !method_exists($clase, $metodo)) {
                $existenMetodos = false;
            }
        }
        $this->addCase($results, 'rutas_sin_verbo_delete', $sinBorrado, 'se registra una ruta DELETE para un controlador sin borrado');
        $this->addCase($results, 'rutas_niveles_catalogo', $nivelesOk, 'una ruta usa un nivel fuera del catalogo 1..4');
        $this->addCase($results, 'rutas_handlers_existen', $existenMetodos, 'una ruta apunta a un metodo inexistente del controlador');

        /*====================================*/
        // El permiso raiz instalado coincide con la ruta y el controlador
        /*====================================*/
        $instalador = $this->codigoFuente($this->rutaModulo('../installer/'.self::INSTALADOR.'.php'));
        $this->addCase($results, 'permiso_ruta_web', str_contains($instalador, "'RutaWeb'        => '".self::RUTA_WEB."'"),
            'el permiso raiz del instalador no usa la ruta '.self::RUTA_WEB);
        $this->addCase($results, 'permiso_controlador', str_contains($instalador, "'RutaController' => '".self::CONTROLADOR."'"),
            'el permiso raiz del instalador no apunta a '.self::CONTROLADOR);
        $this->addCase($results, 'permiso_nivel_maximo', str_contains($instalador, "'idLevelLimit'   => '4'"),
            'el permiso raiz del instalador no declara el limite de niveles 4');
        $this->addCase($results, 'instalador_lista_controlador', str_contains($instalador, '"'.self::CONTROLADOR.'"'),
            'RutaController() del instalador no incluye '.self::CONTROLADOR);

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
            $arrUserData = [];
            $arrLevel    = [];
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
        // Banderas de modulo que consumen las vistas (valen 1 o 2 cuando existen)
        /*====================================*/
        foreach (['entidadesListadoUsoPlanes', 'entidadesListadoUsoUsuarios', 'entidadesListadoUsoMaquinas'] as $bandera) {
            $valor = is_array($arrUserData) ? ($arrUserData[$bandera] ?? null) : null;
            $this->addCase($results, 'bandera_'.$bandera, $valor === null || in_array((int)$valor, [1, 2], true),
                'la bandera '.$bandera.' tiene un valor fuera de 1/2');
        }

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
            $this->addCase($results, 'ruta_registrada', $rutaOk,
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
            'ruta_update_router'    => ['/'.self::RUTA_WEB_UPDATE,  'POST', self::CONTROLADOR.'->Update'],
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
     * Renderiza `tercerosEntidadesListado-List.php` y `-UpdateList.php` con datos sinteticos y
     * verifica la matriz de permisos de la tabla: ver con nivel >= 1, editar con >= 2 y que en
     * NINGUN nivel aparezca la accion de borrar (el modulo no borra desde el listado).
     *
     * Las inclusiones anidadas de las vistas usan `require_once`, por lo que en `List` solo se
     * valida su propio marcado; `UpdateList` y `formSearch` se renderizan por separado.
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
        $html = $this->renderizarVista($this->rutaVista('List'), $this->datosSinteticos(1), $BASE, $f3);

        $this->addCase($results, 'listado_render', $html !== '', 'la vista List no produjo HTML');
        $this->addCase($results, 'listado_contenedor',
            str_contains($html, 'id="listTableData"'), 'falta el contenedor de la tabla');
        $this->addCase($results, 'listado_titulo', str_contains($html, 'Listado Clientes'),
            'no se pinta el titulo de la tabla');
        $this->addCase($results, 'listado_boton_filtrar', str_contains($html, 'data-bs-target="#formSearch"'),
            'falta el boton que despliega el filtro');
        $this->addCase($results, 'listado_boton_quitar_filtro', str_contains($html, 'onclick="deleteFilter()"'),
            'falta el boton para quitar el filtro');
        $this->addCase($results, 'listado_endpoint_ver', str_contains($html, self::RUTA_WEB.'/view/'),
            'falta el endpoint de detalle');
        $this->addCase($results, 'listado_endpoint_actualizar', str_contains($html, self::RUTA_WEB.'/updateList'),
            'falta el endpoint de refresco de tabla');
        $this->addCase($results, 'listado_sin_crear', !str_contains($html, 'Crear Nuevo'),
            'el listado ofrece crear registros y el modulo no tiene alta');
        $this->addCase($results, 'listado_sin_codigo_borrado',
            !str_contains($html, 'listTableDataDel') && !str_contains($html, "'DELETE'"),
            'la vista List conserva codigo de borrado y el modulo no borra');

        /*====================================*/
        // Matriz de acciones sobre la tabla (UpdateList)
        /*====================================*/
        $rutaTabla = $this->rutaVista('UpdateList');
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
            $this->addCase($results, 'tabla_sin_borrar_nivel'.$nivel,
                !str_contains($tabla, 'listTableDataDel('),
                'la tabla ofrece borrar con nivel '.$nivel.' y el modulo no borra');
            $this->addCase($results, 'tabla_ids_cifrados_nivel'.$nivel,
                !str_contains($tabla, "listTableDataView('1')") && !str_contains($tabla, "listTableDataView('2')"),
                'la tabla expone el ID real de la entidad con nivel '.$nivel);
        }

        /*====================================*/
        // Contenido de la tabla: persona natural y empresa
        /*====================================*/
        $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'tabla_cabeceras',
            str_contains($tabla, 'Tipo Entidad') && str_contains($tabla, 'Sector') && str_contains($tabla, 'Estado'),
            'la tabla no declara las cabeceras Tipo Entidad, Sector y Estado');
        $this->addCase($results, 'tabla_persona_natural', str_contains($tabla, 'Perez Soto, Juan'),
            'la persona natural no se pinta como "ApellidoPat ApellidoMat, Nombre"');
        $this->addCase($results, 'tabla_nick', str_contains($tabla, '<strong>jperez</strong> | '),
            'el nick no se antepone al nombre');
        $this->addCase($results, 'tabla_empresa', str_contains($tabla, 'Empresa Demo SpA'),
            'la empresa no se pinta con su razon social');
        $this->addCase($results, 'tabla_empresa_sin_nick', !str_contains($tabla, '<strong></strong>'),
            'una entidad sin nick pinta un nick vacio');
        $this->addCase($results, 'tabla_sector', str_contains($tabla, 'Retail') && str_contains($tabla, 'Mineria'),
            'la tabla no pinta el sector de cada entidad');
        $this->addCase($results, 'tabla_estado_color',
            str_contains($tabla, 'badge-sp1-success') && str_contains($tabla, 'badge-sp1-danger'),
            'la tabla no pinta el estado con su color');

        /*====================================*/
        // Rama sin datos: la tabla se dibuja vacia sin romperse
        /*====================================*/
        $vacio = $this->datosSinteticos(2);
        $vacio['arrList'] = [];
        $tablaVacia = $this->renderizarVista($rutaTabla, $vacio, $BASE, $f3);
        $this->addCase($results, 'tabla_vacia_render', str_contains($tablaVacia, '<tbody>'),
            'la tabla sin datos no se dibuja');
        $this->addCase($results, 'tabla_vacia_sin_filas', !str_contains($tablaVacia, '<td>'),
            'la tabla sin datos dibuja filas');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                        VISTAS DE DETALLE Y FORMULARIOS                     */
    /******************************************************************************/
    /**
     * Renderiza las vistas de detalle (`View`, `Resumen`, `Resumen-Update`) y el formulario de
     * busqueda con datos sinteticos, y comprueba que las pestanas de planes, usuarios y maquinas
     * solo aparecen cuando el usuario tiene el permiso de modulo correspondiente (valor 2).
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
        // Modal de detalle (View): con todos los permisos de modulo
        /*====================================*/
        $html = $this->renderizarVista($this->rutaVista('View'), $this->datosSinteticos(2, 2, 2, 2), $BASE, $f3);
        $this->addCase($results, 'view_render', $html !== '', 'la vista View no produjo HTML');
        $this->addCase($results, 'view_pestana_basicos', str_contains($html, 'id="view_tab_1"'),
            'la vista View no monta la pestana de datos basicos');
        $this->addCase($results, 'view_pestana_planes', str_contains($html, 'id="view_tab_2"'),
            'la vista View no monta la pestana de planes con permiso');
        $this->addCase($results, 'view_pestana_usuarios', str_contains($html, 'id="view_tab_3"'),
            'la vista View no monta la pestana de usuarios con permiso');
        $this->addCase($results, 'view_plan_pintado', str_contains($html, 'Servicio Demo'),
            'la vista View no pinta los planes de la entidad');
        $this->addCase($results, 'view_usuario_pintado', str_contains($html, 'usuario.demo@example.com'),
            'la vista View no pinta los usuarios de la entidad');
        $this->addCase($results, 'view_pie_cierre', str_contains($html, 'data-bs-dismiss="modal"'),
            'la vista View no ofrece el pie de cierre del modal');

        /*====================================*/
        // View sin permisos de modulo: solo datos basicos
        /*====================================*/
        $html = $this->renderizarVista($this->rutaVista('View'), $this->datosSinteticos(2, 1, 1, 1), $BASE, $f3);
        $this->addCase($results, 'view_sin_permiso_planes', !str_contains($html, 'id="view_tab_2"') && !str_contains($html, 'Servicio Demo'),
            'la vista View muestra planes sin permiso');
        $this->addCase($results, 'view_sin_permiso_usuarios', !str_contains($html, 'id="view_tab_3"') && !str_contains($html, 'usuario.demo@example.com'),
            'la vista View muestra usuarios sin permiso');
        $this->addCase($results, 'view_sin_permiso_basicos', str_contains($html, 'id="view_tab_1"'),
            'la vista View no conserva los datos basicos sin permisos de modulo');

        /*====================================*/
        // Resumen: pestanas condicionadas por permiso de modulo
        /*====================================*/
        $html = $this->renderizarVista($this->rutaVista('Resumen'), $this->datosSinteticos(2, 2, 2, 2), $BASE, $f3);
        $this->addCase($results, 'resumen_render', $html !== '', 'la vista Resumen no produjo HTML');
        $this->addCase($results, 'resumen_pestanas_base',
            str_contains($html, 'data-bs-target="#resumen"') && str_contains($html, 'data-bs-target="#resumen-edit"'),
            'la vista Resumen no monta las pestanas Resumen y Editar Datos');
        $this->addCase($results, 'resumen_pestanas_modulo',
            str_contains($html, 'id="resumen-planes"') && str_contains($html, 'id="resumen-usuarios"') && str_contains($html, 'id="resumen-maquinas"'),
            'la vista Resumen no monta las pestanas de planes, usuarios y maquinas con permiso');
        $this->addCase($results, 'resumen_contenedores_tabla',
            str_contains($html, 'id="tabPlanesDataTable"') && str_contains($html, 'id="tabUsuariosDataTable"') && str_contains($html, 'id="tabMaquinasDataTable"'),
            'la vista Resumen no declara los contenedores de las tablas de cada pestana');
        $this->addCase($results, 'resumen_endpoints_hijos',
            str_contains($html, self::RUTA_WEB.'/planes/updateList/')
            && str_contains($html, self::RUTA_WEB.'/usuarios/updateList/')
            && str_contains($html, self::RUTA_WEB.'/maquinas/updateList/'),
            'la vista Resumen no apunta a los endpoints de planes, usuarios y maquinas');
        $this->addCase($results, 'resumen_endpoint_update',
            str_contains($html, self::RUTA_WEB.'/update') && str_contains($html, "Metodo      = 'POST'"),
            'la vista Resumen no envia la edicion a /update con POST');
        $this->addCase($results, 'resumen_endpoint_refresco', str_contains($html, self::RUTA_WEB.'/resumenUpdate/'),
            'la vista Resumen no refresca el resumen con /resumenUpdate/');
        $this->addCase($results, 'resumen_volver', str_contains($html, self::RUTA_WEB.'/listAll'),
            'la vista Resumen no ofrece volver al listado');
        $this->addCase($results, 'resumen_id_cifrado', !str_contains($html, "tabPlanesNew('1')"),
            'la vista Resumen expone el ID real de la entidad');

        /*====================================*/
        // Resumen: formulario de edicion de datos del cliente
        /*====================================*/
        $this->addCase($results, 'resumen_form_edicion_id', str_contains($html, 'id="FormEditData"'),
            'la vista Resumen no dibuja el formulario FormEditData');
        $camposEdicion = ['idTipoEntidad', 'Nombre', 'ApellidoPat', 'ApellidoMat', 'Nick', 'idSexo', 'FNacimiento', 'RazonSocial', 'Web',
                          'Giro', 'idCiudad', 'idComuna', 'Direccion', 'idSector', 'Email', 'Rut', 'Fono1', 'Fono2', 'RepLegalNombre',
                          'RepLegalRut', 'RepLegalEmail', 'RepLegalFono1', 'RepLegalFono2', 'Social_X', 'Social_Facebook',
                          'Social_Instagram', 'Social_Linkedin', 'idEstado'];
        foreach ($camposEdicion as $campo) {
            // Los telefonos usan un selector de pais: el id lo arma el componente, el name se mantiene
            $declarado = str_contains($html, 'id="Edit_'.$campo.'"') || str_contains($html, 'name="'.$campo.'"');
            $this->addCase($results, 'resumen_form_campo_'.$campo, $declarado,
                'el formulario de edicion no declara el campo '.$campo);
        }
        $this->addCase($results, 'resumen_form_oculto_entidad', str_contains($html, 'name="idEntidad"'),
            'el formulario de edicion no envia idEntidad');
        $this->addCase($results, 'resumen_form_oculto_tipo', str_contains($html, 'name="idTipo"'),
            'el formulario de edicion no envia idTipo (Update lo exige)');
        $this->addCase($results, 'resumen_form_valores_actuales',
            str_contains($html, 'value="Perez"') && str_contains($html, 'value="juan@example.com"') && str_contains($html, 'value="11111111-1"'),
            'el formulario de edicion no precarga los valores actuales del cliente');
        $this->addCase($results, 'resumen_form_password', str_contains($html, 'id="Edit_password"'),
            'el formulario de edicion no ofrece la clave con permiso entidadesListadoUsoPassword');
        $this->addCase($results, 'resumen_form_guardar', str_contains($html, 'Guardar Cambios'),
            'el formulario de edicion no tiene boton para guardar');
        $this->addCase($results, 'resumen_form_sin_placeholder', !preg_match('/id="resumen-edit">\s*asd\s*</', $html),
            'la pestana Editar Datos conserva el texto de relleno');

        $sinPassword = $this->datosSinteticos(2);
        $sinPassword['UserData']['entidadesListadoUsoPassword'] = 1;
        $htmlSinPassword = $this->renderizarVista($this->rutaVista('Resumen'), $sinPassword, $BASE, $f3);
        $this->addCase($results, 'resumen_form_sin_permiso_password', !str_contains($htmlSinPassword, 'id="Edit_password"'),
            'el formulario de edicion ofrece la clave sin permiso entidadesListadoUsoPassword');

        /*====================================*/
        // Resumen sin permisos de modulo: ninguna pestana extra
        /*====================================*/
        $html = $this->renderizarVista($this->rutaVista('Resumen'), $this->datosSinteticos(2, 1, 1, 1), $BASE, $f3);
        $this->addCase($results, 'resumen_sin_permisos_modulo',
            !str_contains($html, 'id="resumen-planes"') && !str_contains($html, 'id="resumen-usuarios"') && !str_contains($html, 'id="resumen-maquinas"'),
            'la vista Resumen muestra pestanas de modulo sin permiso');
        $this->addCase($results, 'resumen_sin_permisos_js',
            !str_contains($html, 'function tabPlanesLoadList') && !str_contains($html, 'function tabUsuariosLoadList') && !str_contains($html, 'function tabMaquinasLoadList'),
            'la vista Resumen carga el JavaScript de pestanas sin permiso');

        /*====================================*/
        // Resumen con un solo permiso: solo esa pestana
        /*====================================*/
        $html = $this->renderizarVista($this->rutaVista('Resumen'), $this->datosSinteticos(2, 2, 1, 1), $BASE, $f3);
        $this->addCase($results, 'resumen_solo_planes',
            str_contains($html, 'id="resumen-planes"') && !str_contains($html, 'id="resumen-usuarios"') && !str_contains($html, 'id="resumen-maquinas"'),
            'la vista Resumen no aisla la pestana de planes cuando solo ese permiso existe');

        /*====================================*/
        // Resumen-Update: persona natural y empresa
        /*====================================*/
        $html = $this->renderizarVista($this->rutaVista('Resumen-Update'), $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'resumen_update_render', $html !== '', 'la vista Resumen-Update no produjo HTML');
        $this->addCase($results, 'resumen_update_persona', str_contains($html, 'Perez Soto, Juan'),
            'Resumen-Update no pinta el nombre de la persona natural');
        $this->addCase($results, 'resumen_update_sexo', str_contains($html, 'Masculino'),
            'Resumen-Update no pinta el sexo de la persona natural');
        $this->addCase($results, 'resumen_update_imagen_defecto', str_contains($html, $BASE.'/img/picture-img.jpg'),
            'Resumen-Update no usa la imagen por defecto cuando no hay imagen');

        $empresa = $this->datosSinteticos(2);
        $empresa['rowData']['idTipoEntidad'] = 2;
        $empresa['rowData']['TipoEntidad']   = 'Empresa';
        $empresa['rowData']['Direccion_img'] = 'logo_demo.png';
        $html = $this->renderizarVista($this->rutaVista('Resumen-Update'), $empresa, $BASE, $f3);
        $this->addCase($results, 'resumen_update_empresa', str_contains($html, 'Empresa Demo SpA'),
            'Resumen-Update no pinta la razon social de la empresa');
        $this->addCase($results, 'resumen_update_empresa_sin_sexo', !str_contains($html, 'Masculino'),
            'Resumen-Update pinta el sexo en una empresa');
        $this->addCase($results, 'resumen_update_imagen_propia', str_contains($html, '/admin/public/upload/logo_demo.png'),
            'Resumen-Update no pinta la imagen cargada de la entidad');

        /*====================================*/
        // Formulario de busqueda
        /*====================================*/
        $html = $this->renderizarVista($this->rutaVista('formSearch'), $this->datosSinteticos(1), $BASE, $f3);
        $this->addCase($results, 'form_search_render', $html !== '', 'la vista formSearch no produjo HTML');
        $this->addCase($results, 'form_search_id', str_contains($html, 'id="FormSearchData"'),
            'el formulario de busqueda no usa el id FormSearchData');
        $camposBusqueda = ['idTipoEntidad', 'Nombre', 'ApellidoPat', 'ApellidoMat', 'Nick', 'idSexo', 'FNacimiento',
                           'RazonSocial', 'idCiudad', 'idComuna', 'Direccion', 'idSector', 'Email', 'idEstado'];
        foreach ($camposBusqueda as $campo) {
            $this->addCase($results, 'form_search_campo_'.$campo, str_contains($html, 'id="Search_'.$campo.'"'),
                'el formulario de busqueda no declara el campo Search_'.$campo);
        }
        $this->addCase($results, 'form_search_endpoint',
            str_contains($html, self::RUTA_WEB.'/search') && str_contains($html, "Metodo      = 'POST'"),
            'el formulario de busqueda no apunta a /search con POST');
        $this->addCase($results, 'form_search_quitar_filtro', str_contains($html, "deleteFilter('.collapse')"),
            'el formulario de busqueda no ofrece quitar el filtro');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos el chequeo de datos del controlador (`dataCheck`), el cifrado
     * de identificadores y la validacion de IDs descifrados.
     *
     * @return array Resultado por caso
     */
    public function logicaValidacionTest(){

        $results = [];

        /*====================================*/
        // dataCheck devuelve el contrato esperado por Base_update
        /*====================================*/
        try {
            $reflector = new ReflectionMethod(self::CONTROLADOR, 'dataCheck');
            $reflector->setAccessible(true);
            // Se crea sin constructor: dataCheck() es logica pura y no necesita
            // la conexion a base de datos del controlador.
            $Controller = (new ReflectionClass(self::CONTROLADOR))->newInstanceWithoutConstructor();

            $Post      = ['Nombre' => 'Juan', 'idEstado' => 1, 'idCiudad' => 1, 'idComuna' => 1, 'Email' => 'juan@example.com'];
            $DataCheck = $reflector->invoke($Controller, $Post);

            $this->addCase($results, 'datacheck_es_arreglo', is_array($DataCheck),
                'dataCheck() no devolvio un arreglo');
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post,
                'dataCheck() no conserva el POST recibido');
            $this->addCase($results, 'datacheck_email',
                (string)($DataCheck['ValidarEmail'] ?? '') === 'Email,RepLegalEmail',
                'dataCheck() no valida Email y RepLegalEmail como correos');
            $this->addCase($results, 'datacheck_dominio_email',
                (string)($DataCheck['ValidarDominioEmail'] ?? '') === 'Email,RepLegalEmail',
                'dataCheck() no valida el dominio de los correos');
            $this->addCase($results, 'datacheck_email_minusculas',
                (string)($DataCheck['ValidarMayusculas'] ?? '') === 'Email,RepLegalEmail',
                'dataCheck() no rechaza mayusculas en los correos');
            $this->addCase($results, 'datacheck_enteros',
                (string)($DataCheck['ValidarEntero'] ?? '') === 'idEstado,idCiudad,idComuna,idSector,idSexo,idTipo,idTipoEntidad',
                'dataCheck() no valida los identificadores como numeros enteros');
            $this->addCase($results, 'datacheck_telefonos',
                (string)($DataCheck['ValidarNumero'] ?? '') === 'Fono1,Fono2,RepLegalFono1,RepLegalFono2',
                'dataCheck() no valida los telefonos como numericos');
            $this->addCase($results, 'datacheck_rut',
                (string)($DataCheck['ValidarRut'] ?? '') === 'Rut,RepLegalRut',
                'dataCheck() no valida Rut y RepLegalRut');
            $this->addCase($results, 'datacheck_fecha', (string)($DataCheck['ValidarFecha'] ?? '') === 'FNacimiento',
                'dataCheck() no valida FNacimiento como fecha');
            $this->addCase($results, 'datacheck_urls',
                (string)($DataCheck['ValidarURL'] ?? '') === 'Web,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin',
                'dataCheck() no valida las URL de la entidad');
            $this->addCase($results, 'datacheck_espacios',
                (string)($DataCheck['ValidarEspaciosVacios'] ?? '') === 'Email,RepLegalEmail,Web,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin',
                'dataCheck() no rechaza espacios en correos y URL');
            $this->addCase($results, 'datacheck_palabras_censuradas',
                (string)($DataCheck['ValidarPalabrasCensuradas'] ?? '') === 'Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Direccion,Giro,RepLegalNombre',
                'dataCheck() no filtra palabras censuradas en los textos libres');
            $this->addCase($results, 'datacheck_largo_minimo',
                (int)($DataCheck['ValidarLargoMinimoN'] ?? 0) === 3
                && str_contains((string)($DataCheck['ValidarLargoMinimo'] ?? ''), 'Nombre')
                && str_contains((string)($DataCheck['ValidarLargoMinimo'] ?? ''), 'RazonSocial'),
                'dataCheck() no fija el largo minimo 3 para los textos');

            /*====================================*/
            // Largos maximos: deben respetar el tamano de las columnas de la tabla
            /*====================================*/
            $maximos = $DataCheck['ValidarLargoMaximoPorCampo'] ?? [];
            $esperados = ['Rut' => 15, 'RepLegalRut' => 15, 'Fono1' => 15, 'Fono2' => 15, 'Email' => 255, 'Nombre' => 255,
                          'RazonSocial' => 255, 'Web' => 255, 'IP_Client' => 120, 'Agent_Transp' => 240];
            foreach ($esperados as $campo => $largo) {
                $this->addCase($results, 'datacheck_maximo_'.$campo, (int)($maximos[$campo] ?? 0) === $largo,
                    'dataCheck() no limita '.$campo.' a '.$largo.' caracteres');
            }

            /*====================================*/
            // Todo campo con largo minimo debe tener tambien largo maximo
            /*====================================*/
            $conMinimo = array_filter(explode(',', (string)($DataCheck['ValidarLargoMinimo'] ?? '')));
            $sinMaximo = array_values(array_filter($conMinimo, static fn($c) => !isset($maximos[$c])));
            $this->addCase($results, 'datacheck_minimo_con_maximo', $sinMaximo === [],
                'campos con largo minimo y sin maximo: '.implode(',', $sinMaximo));

            /*====================================*/
            // Los campos validados como enteros son columnas editables de Update()
            /*====================================*/
            $update    = $this->cuerpoMetodo('Update');
            $faltantes = [];
            foreach (explode(',', (string)($DataCheck['ValidarEntero'] ?? '')) as $campo) {
                if (!preg_match('/[\',]'.preg_quote($campo, '/').'[,\']/', $update)) {
                    $faltantes[] = $campo;
                }
            }
            $this->addCase($results, 'datacheck_enteros_editables', $faltantes === [],
                'campos validados que Update() no edita: '.implode(',', $faltantes));
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_es_arreglo', false,
                'no se pudo ejecutar dataCheck() ('.$e->getMessage().')');
        }

        /*====================================*/
        // dataCheck es privado: no es accesible desde fuera del controlador
        /*====================================*/
        $this->addCase($results, 'datacheck_privado',
            method_exists(self::CONTROLADOR, 'dataCheck') && (new ReflectionMethod(self::CONTROLADOR, 'dataCheck'))->isPrivate(),
            'dataCheck() debe ser privado');

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

            $basura = $Codification->encryptDecrypt('decrypt', 'valor-no-cifrado');
            $this->addCase($results, 'cifrado_rechaza_basura', ($basura['success'] ?? false) === false,
                'un valor no cifrado no debe descifrarse');
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
     * Revisa estaticamente las vistas propias del controlador y el propio controlador:
     * ausencia de rutas relativas maliciosas, de rutas absolutas del sistema de ficheros, de
     * acceso directo a la sesion desde las plantillas, de SQL embebido y de concatenacion de
     * superglobales en consultas.
     *
     * @return array Resultado por caso
     */
    public function seguridadVistasTest(){

        $results = [];

        /*====================================*/
        // Vistas propias (el prefijo es compartido con los sub-controladores)
        /*====================================*/
        $arrVistas = [];
        foreach (self::VISTAS_PROPIAS as $vista) {
            $arrVistas[] = $this->rutaVista($vista);
        }
        $this->addCase($results, 'hay_vistas', count(array_filter($arrVistas, 'is_file')) === count(self::VISTAS_PROPIAS),
            'faltan vistas del controlador '.self::CONTROLADOR);

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
            $this->addCase($results, 'vista_sin_sql_'.$nombre, !preg_match('/\b(SELECT|INSERT|UPDATE|DELETE)\s+\w+\s+FROM\b/i', $codigo),
                $nombre.' contiene una consulta SQL embebida');
            $this->addCase($results, 'vista_sin_superglobales_'.$nombre, !preg_match('/\$_(POST|GET|REQUEST|COOKIE|SERVER)\b/', $codigo),
                $nombre.' lee superglobales de la peticion');
        }

        /*====================================*/
        // Los identificadores de la tabla viajan cifrados
        /*====================================*/
        // La tabla y sus filas (parcial -UpdateListRows, ADR-0022)
        $tabla = $this->codigoFuente($this->rutaVista('UpdateList'))
            . $this->codigoFuente($this->rutaVista('UpdateListRows'));
        $this->addCase($results, 'tabla_id_cifrado', str_contains($tabla, "encryptDecrypt('encrypt'"),
            'UpdateList.php no cifra el ID de la entidad antes de pintarlo');

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
        $this->addCase($results, 'controlador_sin_interpolar_id',
            !preg_match('/[\'"]\s*\.\s*\$EntidadID/', $controlador),
            'el controlador concatena el ID descifrado en una cadena');
        $this->addCase($results, 'controlador_sin_acceso_session', !str_contains($controlador, '$_SESSION'),
            'el controlador lee $_SESSION directamente en vez de getUserData()');
        $this->addCase($results, 'controlador_sin_eval', !preg_match('/\b(eval|exec|shell_exec|system|passthru)\s*\(/', $controlador),
            'el controlador usa funciones de ejecucion de codigo');

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
        $vista   = __DIR__.'/../views/tercerosEntidadesListado-List.php';
        $fuente  = is_file($vista) ? (string)file_get_contents($vista) : '';

        /*====================================*/
        // La vista llama a updateList desde deleteFilter() y el boton no tiene guarda de nivel
        /*====================================*/
        $usaUpdateList = preg_match('/function deleteFilter.*?\/updateList/s', $fuente) === 1;
        $results['quitar_filtro_usa_updatelist'] = $usaUpdateList
            ? 'OK' : 'FALLO: la vista tercerosEntidadesListado-List.php ya no refresca con updateList desde deleteFilter()';

        /*====================================*/
        // Nivel de listAll y de updateList por cada ruta base del controlador
        /*====================================*/
        $niveles = $this->nivelesListadoRutas('tercerosEntidadesListado');
        $results['rutas_listado_leidas'] = !empty($niveles)
            ? 'OK' : 'FALLO: no se encontraron rutas listAll/updateList de tercerosEntidadesListado en el instalador';

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
