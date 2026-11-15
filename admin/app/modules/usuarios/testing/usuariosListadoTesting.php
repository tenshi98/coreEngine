<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class usuariosListadoTesting
 *
 * Pruebas del modulo `usuarios` - controlador `usuariosListado` (CRUD de usuarios y pantalla de resumen).
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
 * fuente. Tampoco se ejecutan `InstallModule()`/`UninstallModule()`. Solo se ejecutan rutas
 * puras (`dataCheck`, cifrado, `isValidDecrypted`, `getRouteDefinitions`) y el render de vistas
 * con datos sinteticos.
 *
 * @see skills/testing/SKILL.md
 */
class usuariosListadoTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR      = 'usuariosListado';
    private const INSTALADOR       = 'usuariosInstaller';
    private const RUTA_WEB         = 'administracion/usuarios';
    private const RUTA_WEB_LISTADO = 'administracion/usuarios/listAll';
    private const RUTA_WEB_SEARCH  = 'administracion/usuarios/search';
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
    // Fuente del instalador del modulo
    /*******************************************************************/
    private function fuenteInstalador(): string{
        return $this->codigoFuente($this->rutaModulo('../installer/'.self::INSTALADOR.'.php'));
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
            foreach ($instalador->getRouteDefinitions(1) as $ruta) {
                $rutas[$ruta['idMetodo'].' '.$ruta['RutaWeb']] = [
                    $ruta['RutaController'],
                    (int)$ruta['idLevelLimit'],
                    $ruta['Controller'],
                ];
            }
        } catch (Throwable $e) {
            return [];
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
    // Cuerpo (fuente) de un metodo del controlador, para pruebas estaticas
    /*******************************************************************/
    private function cuerpoMetodo(string $metodo): string{
        if (!method_exists(self::CONTROLADOR, $metodo)) {
            return '';
        }
        $r      = new ReflectionMethod(self::CONTROLADOR, $metodo);
        $lineas = file($r->getFileName()) ?: [];
        return implode('', array_slice($lineas, $r->getStartLine() - 1, $r->getEndLine() - $r->getStartLine() + 1));
    }

    /*******************************************************************/
    // Datos sinteticos usados por las vistas del modulo
    /*******************************************************************/
    private function datosSinteticos(int $nivel, int $permisosExtra = 2): array{
        return [
            'PageTitle'       => 'Listado de Usuarios',
            'PageDescription' => 'Listado de Usuarios.',
            'PageAuthor'      => 'Pruebas',
            'PageKeywords'    => 'Pruebas',
            'TableTitle'      => 'Listado de Usuarios',
            'UserData'        => [
                'MainPathUrl'              => '/admin/public/upload/',
                'sistemaModalSubtitle'     => 1,
                'sistemaModalCloseBTN'     => 2,
                'usuariosPermisosBodegas'  => $permisosExtra,
                'usuariosPermisosMaquinas' => $permisosExtra,
                'UserType'                 => 2,
            ],
            'UserAccess'      => ['LevelAccess' => $nivel, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_FormInputs'  => new UIFormInputs(),
            'Fnc_Codification' => new FunctionsSecurityCodification(),
            'Fnc_DataDate'    => new FunctionsDataDate(),
            'Fnc_DataNumbers' => new FunctionsDataNumbers(),
            'Fnc_WidgetsCommon' => new UIWidgetsCommon(),
            'Fnc_WidgetsViews'  => new UIWidgetsViews(),
            'Fnc_CommonData'  => new FunctionsCommonData(),
            'arrList'         => [[
                'idUsuario'     => 7,
                'email'         => 'tester@example.com',
                'Nombre'        => 'Usuario Prueba',
                'Ultimo_acceso' => '2026-01-02 10:00:00',
                'Estado'        => 'Activo',
                'EstadoColor'   => 'success',
            ]],
            'arrEstado'       => [['ID' => 1, 'Nombre' => 'Activo']],
            'arrCiudad'       => [['ID' => 1, 'Nombre' => 'Santiago']],
            'arrComuna'       => [['ID1' => 1, 'ID2' => 1, 'Nombre' => 'Providencia']],
            'arrTipoUsuario'  => [['ID' => 2, 'Nombre' => 'Administrador']],
            'arrPosicion'     => [['ID' => 2, 'Nombre' => 'Superior']],
            'arrPermisos'     => [['Permiso' => 'Usuarios - Listado', 'Nivel' => 'Ver']],
            'arrPermisosBodegas'  => [['Nombre' => 'Bodega Central']],
            'arrPermisosMaquinas' => [['Nombre' => 'Maquina Uno']],
            'arrObservaciones' => [[
                'FechaCreacion' => '2026-01-02',
                'Usuario'       => 'Tester',
                'Observacion'   => 'Observacion de prueba',
            ]],
            'rowData'         => [
                'idUsuario'        => 7,
                'idTipoUsuario'    => 2,
                'idEstado'         => 1,
                'idCiudad'         => 1,
                'idComuna'         => 1,
                'idMenuPosicion'   => 2,
                'email'            => 'tester@example.com',
                'Nombre'           => 'Usuario Prueba',
                'Rut'              => '11.111.111-1',
                'fNacimiento'      => '1990-05-10',
                'Fono'             => '+56911265984',
                'Direccion'        => 'Av. Siempreviva 742',
                'Direccion_img'    => '',
                'Social_X'         => 'https://x.com/prueba',
                'Social_Facebook'  => 'https://facebook.com/prueba',
                'Social_Instagram' => 'https://instagram.com/prueba',
                'Social_Linkedin'  => 'https://linkedin.com/in/prueba',
                'TipoUsuario'      => 'Administrador',
                'Estado'           => 'Activo',
                'EstadoColor'      => 'success',
                'Ciudad'           => 'Santiago',
                'Comuna'           => 'Providencia',
                'MenuPosicion'     => 'Superior',
            ],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica la estructura de carpetas y archivos del modulo `usuarios` (controlador
     * `usuariosListado`) y la convencion "clase = nombre de archivo" exigida por el
     * AUTOLOAD de Fat-Free.
     *
     * @return array Resultado por caso
     */
    public function estructuraModuloTest(){

        $results = [];

        /*====================================*/
        // Archivos obligatorios del modulo
        /*====================================*/
        $arrArchivos = [
            'controlador'       => $this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'),
            'instalador'        => $this->rutaModulo('../installer/'.self::INSTALADOR.'.php'),
            'vista_list'        => $this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php'),
            'vista_update_list' => $this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'),
            'vista_view'        => $this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'),
            'vista_resumen'     => $this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen.php'),
            'vista_resumen_upd' => $this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen-Update.php'),
            'vista_form_new'    => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formNew.php'),
            'vista_form_search' => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formSearch.php'),
            'testing'           => $this->rutaModulo('usuariosListadoTesting.php'),
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
        $this->addCase($results, 'clase_testing_cargada', class_exists('usuariosListadoTesting'),
            'la clase usuariosListadoTesting no esta disponible');

        /*====================================*/
        // Herencias esperadas por el proyecto
        /*====================================*/
        $padreController = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padreController === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padreController.')');
        $padreInstalador = class_exists(self::INSTALADOR) ? get_parent_class(self::INSTALADOR) : '';
        $this->addCase($results, 'herencia_instalador', $padreInstalador === 'ControllerInstaller',
            'el instalador no extiende ControllerInstaller (extiende '.$padreInstalador.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('usuariosListadoTesting') === 'ControllerBase',
            'el testing no extiende ControllerBase');

        /*====================================*/
        // Constructor del controlador: conexion MySQL_1 y datos de la clase padre
        /*====================================*/
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'constructor_conexion', str_contains($codigo, 'Database::getSQLConnection(ConfigDataBase::MySQL_1)'),
            'el constructor no abre la conexion MySQL_1');
        $this->addCase($results, 'constructor_padre', str_contains($codigo, 'parent::__construct($DB_conn_1, $queryBuilder, $checkData)'),
            'el constructor no delega en ControllerBase');
        $this->addCase($results, 'constructor_nombre', str_contains($codigo, "\$this->controllerName = 'usuariosListado'"),
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
     * `View`, `Resumen`, `ResumenUpdate`): existencia, visibilidad, firma y uso de
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
        // Metadatos y datos de la pagina de listado
        /*====================================*/
        $listAll = $this->cuerpoMetodo('listAll');
        $this->addCase($results, 'listado_titulo', str_contains($listAll, "'PageTitle'"),
            'listAll() no asigna PageTitle');
        $this->addCase($results, 'listado_tabla', str_contains($listAll, "'TableTitle'"),
            'listAll() no asigna TableTitle');
        $this->addCase($results, 'listado_permiso_usuario', str_contains($listAll, 'getArrLevel($f3, $this->controllerName)'),
            'listAll() no resuelve el nivel de acceso del usuario');
        $this->addCase($results, 'listado_fallback_errores', str_contains($listAll, 'mergeResponses('),
            'listAll() no agrupa las respuestas fallidas con mergeResponses()');
        $this->addCase($results, 'listado_consulta_ubicacion',
            str_contains($listAll, 'core_ubicacion_ciudad') && str_contains($listAll, 'core_ubicacion_comunas'),
            'listAll() no consulta ciudades y comunas');
        $this->addCase($results, 'listado_consulta_tipos', str_contains($listAll, 'core_tipos_usuario'),
            'listAll() no consulta el catalogo de tipos de usuario');
        $this->addCase($results, 'listado_excluye_superadmin',
            str_contains($listAll, 'usuarios_listado.idTipoUsuario!=1') && str_contains($listAll, "'idTipoUsuario != ?'"),
            'listAll() debe excluir al tipo de usuario 1 del listado y del catalogo de tipos');

        /*====================================*/
        // Busqueda (UpdateList): filtros parametrizados y exclusion del superadmin
        /*====================================*/
        $updateList = $this->cuerpoMetodo('UpdateList');
        $this->addCase($results, 'busqueda_filtros',
            str_contains($updateList, "'idTipoUsuario'") && str_contains($updateList, "'email,Nombre'"),
            'UpdateList() no filtra por tipo de usuario, email y nombre');
        $this->addCase($results, 'busqueda_searchwhere', substr_count($updateList, '$this->searchWhere(') === 3,
            'UpdateList() debe construir el WHERE con searchWhere() para exacta, relativa y between');
        $this->addCase($results, 'busqueda_fechas', str_contains($updateList, 'searchValidateDates('),
            'UpdateList() no valida las fechas de busqueda');
        $this->addCase($results, 'busqueda_excluye_superadmin',
            str_contains($updateList, 'usuarios_listado.idTipoUsuario != ?') && str_contains($updateList, 'array_merge($whereParams, [1])'),
            'UpdateList() no excluye al tipo de usuario 1 con parametros preparados');

        /*====================================*/
        // View / Resumen: permisos de bodegas y maquinas dependen del usuario en sesion
        /*====================================*/
        foreach (['View', 'Resumen'] as $metodo) {
            $cuerpo = $this->cuerpoMetodo($metodo);
            $this->addCase($results, 'permisos_bodegas_'.$metodo,
                str_contains($cuerpo, '$arrUserData["usuariosPermisosBodegas"]==2'),
                $metodo.'() no condiciona las bodegas a usuariosPermisosBodegas');
            $this->addCase($results, 'permisos_maquinas_'.$metodo,
                str_contains($cuerpo, '$arrUserData["usuariosPermisosMaquinas"]==2'),
                $metodo.'() no condiciona las maquinas a usuariosPermisosMaquinas');
            $this->addCase($results, 'fallback_vacio_'.$metodo,
                str_contains($cuerpo, "['status'] = true") && str_contains($cuerpo, "['data']   = []"),
                $metodo.'() no entrega un resultado vacio cuando no aplican bodegas/maquinas');
        }
        $this->addCase($results, 'resumen_catalogos',
            str_contains($this->cuerpoMetodo('Resumen'), 'core_estados') && str_contains($this->cuerpoMetodo('Resumen'), 'core_posicion_menu'),
            'Resumen() no consulta estados y posicion del menu');

        /*====================================*/
        // Consultas internas: detalle por ID con parametros
        /*====================================*/
        $detalle = $this->cuerpoMetodo('getDataDetail');
        $this->addCase($results, 'detalle_privado',
            method_exists(self::CONTROLADOR, 'getDataDetail') && (new ReflectionMethod(self::CONTROLADOR, 'getDataDetail'))->isPrivate(),
            'getDataDetail() debe ser privado');
        $this->addCase($results, 'detalle_parametrizado',
            str_contains($detalle, "'where'   => 'usuarios_listado.idUsuario = ?'") && str_contains($detalle, "'params'  => [\$ID]"),
            'getDataDetail() no filtra por idUsuario con parametros preparados');
        $this->addCase($results, 'detalle_sin_password', !str_contains($detalle, 'password'),
            'getDataDetail() no debe exponer el campo password');
        $lista = $this->cuerpoMetodo('getDataList');
        $this->addCase($results, 'lista_sin_password', !str_contains($lista, 'password'),
            'getDataList() no debe exponer el campo password');

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
        // Insert: usa el chequeo propio, fuerza el cambio de password y responde con el ID cifrado
        /*====================================*/
        $insert = $this->cuerpoMetodo('Insert');
        $this->addCase($results, 'insert_datacheck', str_contains($insert, '$this->dataCheck($_POST)'),
            'Insert() no construye el DataCheck con dataCheck($_POST)');
        $this->addCase($results, 'insert_tabla', str_contains($insert, "'table'     => 'usuarios_listado'"),
            'Insert() no apunta a la tabla usuarios_listado');
        $this->addCase($results, 'insert_requeridos',
            str_contains($insert, "'required'  => 'password,idTipoUsuario,idEstado,email,Nombre,idMenuPosicion'"),
            'Insert() no declara los campos obligatorios del usuario');
        $this->addCase($results, 'insert_email_unico', str_contains($insert, "'unique'    => 'email'"),
            'Insert() no exige email unico');
        $this->addCase($results, 'insert_password_cifrada', str_contains($insert, "'encode'    => 'password'"),
            'Insert() no cifra la password antes de guardarla');
        $this->addCase($results, 'insert_password_forzar_cambio',
            str_contains($insert, "\$_POST['PasswordForzarCambio'] = 1") && str_contains($insert, "\$_POST['PasswordUltimoCambio']"),
            'Insert() no fuerza el cambio de password en el primer ingreso');
        $this->addCase($results, 'insert_falla_gestionada', str_contains($insert, 'failOperation('),
            'Insert() no gestiona la falla con failOperation()');
        $this->addCase($results, 'insert_respuesta', str_contains($insert, 'Response::success('),
            'Insert() no responde con Response::success()');
        $this->addCase($results, 'insert_id_cifrado', str_contains($insert, "encryptDecrypt('encrypt'"),
            'Insert() no cifra el ID devuelto');

        /*====================================*/
        // Update: verifica el verbo, fuerza el cambio de password y gestiona archivos
        /*====================================*/
        $update = $this->cuerpoMetodo('Update');
        $this->addCase($results, 'update_verbo_post', str_contains($update, "REQUEST_METHOD'] !== 'POST'"),
            'Update() no valida el verbo HTTP POST');
        $this->addCase($results, 'update_405', str_contains($update, "405"),
            'Update() no responde 405 ante un verbo incorrecto');
        $this->addCase($results, 'update_donde', str_contains($update, "'where'     => 'idUsuario'"),
            'Update() no delimita la escritura por idUsuario');
        $this->addCase($results, 'update_requeridos',
            str_contains($update, "'required'  => 'idUsuario,idTipoUsuario,idEstado,email,Nombre,idMenuPosicion'"),
            'Update() no declara los campos obligatorios del usuario');
        $this->addCase($results, 'update_email_unico', str_contains($update, "'unique'    => 'email'"),
            'Update() no exige email unico');
        $this->addCase($results, 'update_password_condicional',
            str_contains($update, "isset(\$_POST['password'])&&\$_POST['password']!=''")
            && str_contains($update, "\$_POST['PasswordForzarCambio'] = 1"),
            'Update() solo debe forzar el cambio de password cuando se envia una nueva');
        $this->addCase($results, 'update_archivos',
            str_contains($update, "'files'") && str_contains($update, 'Direccion_img'),
            'Update() no declara el manejo del archivo Direccion_img');
        $this->addCase($results, 'update_archivos_imagen',
            str_contains($update, "'ValidarTipo'   => 'image'") && str_contains($update, "'ValidarPeso'   => 10"),
            'Update() no restringe la subida a imagenes con peso maximo');
        $this->addCase($results, 'update_falla_gestionada', str_contains($update, 'failOperation('),
            'Update() no gestiona la falla con failOperation()');

        /*====================================*/
        // Delete: verbo DELETE, transaccion y borrado en cascada
        /*====================================*/
        $delete = $this->cuerpoMetodo('Delete');
        $this->addCase($results, 'delete_verbo', str_contains($delete, "REQUEST_METHOD'] !== 'DELETE'"),
            'Delete() no valida el verbo HTTP DELETE');
        $this->addCase($results, 'delete_transaccion',
            str_contains($delete, 'Base_transactionBegin()')
            && str_contains($delete, 'Base_transactionCommit()')
            && str_contains($delete, 'failOperation('),
            'Delete() debe usar Begin/Commit y failOperation (rollback gestionado)');
        $this->addCase($results, 'delete_orden_transaccion',
            strpos($delete, 'Base_transactionBegin()') < strpos($delete, 'Base_delete(')
            && strpos($delete, 'Base_delete(') < strpos($delete, 'Base_transactionCommit()'),
            'Delete() debe abrir la transaccion antes de borrar y confirmarla al final');
        $this->addCase($results, 'delete_archivo_asociado',
            str_contains($delete, "'files'       => 'Direccion_img'"),
            'Delete() no elimina la imagen asociada al usuario');
        $this->addCase($results, 'delete_cascada', str_contains($delete, 'usuarios_listado_observaciones'),
            'Delete() no elimina las observaciones relacionadas');
        $this->addCase($results, 'delete_por_usuario', str_contains($delete, "'where'       => 'idUsuario'"),
            'Delete() no delimita el borrado por idUsuario');

        /*====================================*/
        // delFiles: verbo PUT y limpieza del archivo
        /*====================================*/
        $delFiles = $this->cuerpoMetodo('delFiles');
        $this->addCase($results, 'delfiles_verbo', str_contains($delFiles, "REQUEST_METHOD'] !== 'PUT'"),
            'delFiles() no valida el verbo HTTP PUT');
        $this->addCase($results, 'delfiles_archivo', str_contains($delFiles, 'Base_delFiles('),
            'delFiles() no invoca Base_delFiles()');
        $this->addCase($results, 'delfiles_columna', str_contains($delFiles, "'files'       => 'Direccion_img'"),
            'delFiles() no apunta a la columna Direccion_img');

        /*====================================*/
        // Verificacion de IDs descifrados antes de consultar
        /*====================================*/
        foreach (['View', 'Resumen', 'ResumenUpdate'] as $metodo) {
            $cuerpo = $this->cuerpoMetodo($metodo);
            $this->addCase($results, 'id_descifrado_'.$metodo,
                str_contains($cuerpo, "encryptDecrypt('decrypt', \$params['id'])"),
                $metodo.'() no descifra $params[\'id\']');
            $this->addCase($results, 'id_validado_'.$metodo,
                str_contains($cuerpo, "isValidDecrypted(\$UsuarioID, 'id')") && str_contains($cuerpo, "Response::error('Registro inválido', 400)"),
                $metodo.'() no rechaza con 400 un ID invalido');
            // El ID se valida antes de consultar la base de datos
            $this->addCase($results, 'id_antes_de_consulta_'.$metodo,
                strpos($cuerpo, 'isValidDecrypted(') < strpos($cuerpo, 'getDataDetail('),
                $metodo.'() consulta la base de datos antes de validar el ID');
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                       CONTROLADOR: RUTAS E INSTALADOR                      */
    /******************************************************************************/
    /**
     * Verifica que las 10 rutas de `usuariosListado` publicadas por el instalador
     * apunten a metodos reales con el verbo, nivel y controlador esperados, y que el
     * instalador declare la ruta raiz del modulo.
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
            'listado'         => ['1 '.self::RUTA_WEB.'/listAll',              'usuariosListado->listAll',       1],
            'buscar'          => ['2 '.self::RUTA_WEB.'/search',               'usuariosListado->UpdateList',    1],
            'refrescar'       => ['1 '.self::RUTA_WEB.'/updateList',           'usuariosListado->UpdateList',    2],
            'ver'             => ['1 '.self::RUTA_WEB.'/view/@id',             'usuariosListado->View',          1],
            'resumen'         => ['1 '.self::RUTA_WEB.'/resumen/@id',          'usuariosListado->Resumen',       2],
            'resumen_update'  => ['1 '.self::RUTA_WEB.'/resumenUpdate/@id',    'usuariosListado->ResumenUpdate', 2],
            'crear'           => ['2 '.self::RUTA_WEB,                         'usuariosListado->Insert',        3],
            'editar'          => ['2 '.self::RUTA_WEB.'/update',               'usuariosListado->Update',        2],
            'borrar_archivos' => ['4 '.self::RUTA_WEB.'/delFiles',             'usuariosListado->delFiles',      2],
            'borrar'          => ['3 '.self::RUTA_WEB,                         'usuariosListado->Delete',        4],
        ];
        foreach ($esperadas as $caso => $esperada) {
            [$clave, $handler, $nivel] = $esperada;
            $existe = isset($rutas[$clave]);
            $this->addCase($results, 'ruta_'.$caso, $existe, 'el instalador no publica la ruta '.$clave);
            if (!$existe) {
                continue;
            }
            // PHP no distingue mayusculas en nombres de metodo: se compara sin distinguirlas
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
        // Rutas propias: cantidad y verbos coherentes con el metodo
        /*====================================*/
        $propias = array_filter($rutas, static function ($r) { return $r[2] === 'usuariosListado'; });
        $this->addCase($results, 'rutas_propias_completas', count($propias) === count($esperadas),
            'se esperaban '.count($esperadas).' rutas de '.self::CONTROLADOR.' y hay '.count($propias));
        foreach ($propias as $clave => $datos) {
            $verbo = (int)substr($clave, 0, 1);
            if (str_ends_with($datos[0], '->Insert') || str_ends_with($datos[0], '->Update')) {
                $this->addCase($results, 'verbo_escritura_'.md5($clave), $verbo === 2, $datos[0].' debe publicarse con POST');
            }
            if (str_ends_with($datos[0], '->Delete')) {
                $this->addCase($results, 'verbo_borrado_'.md5($clave), $verbo === 3, $datos[0].' debe publicarse con DELETE');
            }
            if (strcasecmp(substr($datos[0], -9), '->delFiles') === 0) {
                $this->addCase($results, 'verbo_archivos_'.md5($clave), $verbo === 4, $datos[0].' debe publicarse con PUT');
            }
        }

        /*====================================*/
        // Niveles: toda ruta que escribe exige al menos editar (2). La busqueda
        // (`/search`) viaja por POST pero solo lee, por eso se excluye.
        /*====================================*/
        $escrituraSinNivel = [];
        foreach ($propias as $clave => $datos) {
            if (str_ends_with($clave, '/search')) {
                continue;
            }
            if ((int)substr($clave, 0, 1) !== 1 && $datos[1] < 2) {
                $escrituraSinNivel[] = $clave;
            }
        }
        $this->addCase($results, 'rutas_escritura_nivel_minimo', count($escrituraSinNivel) === 0,
            'rutas de escritura con nivel inferior a 2: '.implode(' | ', $escrituraSinNivel));

        /*====================================*/
        // Ruta raiz y controlador registrados por InstallModule()
        /*====================================*/
        $instalador = $this->fuenteInstalador();
        $this->addCase($results, 'instalador_ruta_raiz', str_contains($instalador, "'RutaWeb'        => 'administracion/usuarios'"),
            'InstallModule() no registra la ruta raiz '.self::RUTA_WEB);
        $this->addCase($results, 'instalador_controlador', str_contains($instalador, "'RutaController' => 'usuariosListado'"),
            'InstallModule() no registra el controlador usuariosListado');
        $this->addCase($results, 'instalador_nivel_maximo', str_contains($instalador, "'idLevelLimit'   => '3'"),
            'InstallModule() debe declarar el limite de nivel 3 (ver / editar / crear)');
        $this->addCase($results, 'instalador_transaccion',
            str_contains($instalador, 'Base_transactionBegin()') && str_contains($instalador, 'Base_transactionCommit()'),
            'InstallModule() debe ejecutarse dentro de una transaccion');

        /*====================================*/
        // Desinstalacion: el controlador principal debe estar en RutaController()
        /*====================================*/
        $this->addCase($results, 'instalador_desinstala_controlador',
            str_contains($instalador, "\$RutaController  = '\"usuariosListado\"'"),
            'RutaController() no lista usuariosListado y dejaria rutas huerfanas al desinstalar');

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
        // Claves de usuario que consumen View/Resumen (bodegas y maquinas)
        /*====================================*/
        if (is_array($UserData) && !empty($UserData)) {
            foreach (['usuariosPermisosBodegas', 'usuariosPermisosMaquinas'] as $clave) {
                $valor = $UserData[$clave] ?? null;
                $this->addCase($results, 'sesion_'.$clave, $valor === null || in_array((int)$valor, [1, 2], true),
                    'la clave '.$clave.' de la sesion debe valer 1 o 2 (vale '.var_export($valor, true).')');
            }
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
            // Un usuario sin acceso al modulo (nivel null) no tiene la ruta: no es un defecto
            $this->addCase($results, 'ruta_registrada', $nivel === null || $rutaOk,
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
     * Renderiza `usuariosListado-List.php` y `usuariosListado-UpdateList.php` con datos
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
        // Los formularios se incluyen con require_once: su contenido se prueba
        // al renderizarlos por separado (vistasDetalleTest), no a traves de List.
        /*====================================*/
        $rutaList = $this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php');
        $html     = $this->renderizarVista($rutaList, $this->datosSinteticos(1), $BASE, $f3);

        $this->addCase($results, 'listado_render', $html !== '', 'la vista List no produjo HTML');
        $this->addCase($results, 'listado_contenedor',
            str_contains($html, 'id="listTableData"'), 'falta el contenedor de la tabla');
        $this->addCase($results, 'listado_titulo_tabla', str_contains($html, 'Listado de Usuarios'),
            'no se pinta el titulo de la tabla');
        $this->addCase($results, 'listado_boton_filtrar', str_contains($html, 'data-bs-target="#formSearch"'),
            'falta el boton que despliega el filtro');
        $this->addCase($results, 'listado_boton_quitar_filtro', str_contains($html, 'onclick="deleteFilter()"'),
            'falta el boton que quita el filtro');
        $this->addCase($results, 'listado_sin_crear_nivel1', !str_contains($html, 'Crear Nuevo</button>'),
            'con nivel 1 el listado ofrece crear registros');
        $this->addCase($results, 'listado_endpoint_ver', str_contains($html, '/view/'),
            'falta el endpoint de detalle');
        $this->addCase($results, 'listado_endpoint_actualizar', str_contains($html, '/updateList'),
            'falta el endpoint de refresco de tabla');
        $this->addCase($results, 'listado_ruta_modulo', str_contains($html, $BASE.'/'.self::RUTA_WEB),
            'los endpoints no se construyen con la ruta del modulo');
        $this->addCase($results, 'listado_borrado_verbo', str_contains($html, "Metodo      = 'DELETE'"),
            'la accion de borrar no viaja con el verbo DELETE');
        $this->addCase($results, 'listado_borrado_id', str_contains($html, '{"idUsuario": ID}'),
            'la accion de borrar no envia idUsuario');

        /*====================================*/
        // List incluye sus formularios y la tabla
        /*====================================*/
        $fuenteList = $this->codigoFuente($rutaList);
        foreach (['formSearch', 'UpdateList', 'formNew'] as $parte) {
            $this->addCase($results, 'listado_incluye_'.$parte,
                str_contains($fuenteList, "require_once('".self::CONTROLADOR."-".$parte.".php')"),
                'List.php no incluye '.self::CONTROLADOR.'-'.$parte.'.php');
        }

        /*====================================*/
        // El boton de alta aparece con nivel >= 3
        /*====================================*/
        $htmlNivel3 = $this->renderizarVista($rutaList, $this->datosSinteticos(3), $BASE, $f3);
        $this->addCase($results, 'listado_crear_nivel3', str_contains($htmlNivel3, 'Crear Nuevo</button>'),
            'con nivel 3 no se muestra el boton Crear Nuevo');
        $this->addCase($results, 'listado_modal_nivel3', str_contains($htmlNivel3, 'data-bs-target="#newFormModal"'),
            'el boton de alta no apunta al modal #newFormModal');
        $htmlNivel2 = $this->renderizarVista($rutaList, $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'listado_sin_crear_nivel2', !str_contains($htmlNivel2, 'Crear Nuevo</button>'),
            'con nivel 2 el listado ofrece crear registros');

        /*====================================*/
        // Matriz de acciones sobre la tabla (UpdateList)
        /*====================================*/
        $rutaTabla = $this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php');
        $cifrado   = (new FunctionsSecurityCodification())->encryptDecrypt('encrypt', 7);
        $idCifrado = (string)($cifrado['data'] ?? '');
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
                str_contains($tabla, 'tester@example.com') && str_contains($tabla, 'Usuario Prueba'),
                'la tabla no pinta el registro sintetico con nivel '.$nivel);
            $this->addCase($results, 'tabla_estado_nivel'.$nivel,
                str_contains($tabla, 'badge-sp1-success') && str_contains($tabla, 'Activo'),
                'la tabla no pinta el estado del usuario con nivel '.$nivel);
            // El ID del usuario nunca viaja en claro hacia las acciones
            $this->addCase($results, 'tabla_id_cifrado_nivel'.$nivel,
                $idCifrado !== '' && !str_contains($tabla, "listTableDataView('7')") && !str_contains($tabla, '/resumen/7'),
                'la tabla expone el ID del usuario sin cifrar con nivel '.$nivel);
        }

        /*====================================*/
        // Tabla sin datos: cabecera presente y ninguna accion
        /*====================================*/
        $vacio             = $this->datosSinteticos(4);
        $vacio['arrList']  = [];
        $tablaVacia        = $this->renderizarVista($rutaTabla, $vacio, $BASE, $f3);
        $this->addCase($results, 'tabla_vacia_cabecera', str_contains($tablaVacia, '<th scope="col">Email</th>'),
            'la tabla sin datos no dibuja la cabecera');
        $this->addCase($results, 'tabla_vacia_sin_acciones', !str_contains($tablaVacia, 'listTableDataView('),
            'la tabla sin datos no debe ofrecer acciones');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                        VISTAS DE DETALLE Y FORMULARIOS                     */
    /******************************************************************************/
    /**
     * Renderiza las vistas de detalle, los dos formularios del listado y la ficha de
     * resumen con datos sinteticos y comprueba que conservan el contrato de ids,
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
        // La pestana de datos incluye Resumen-Update con require_once: se prueba aparte.
        /*====================================*/
        $rutaView = $this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php');
        $html     = $this->renderizarVista($rutaView, $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'view_render', $html !== '', 'la vista View no produjo HTML');
        $this->addCase($results, 'view_pestanas',
            str_contains($html, 'id="view_tab_1"') && str_contains($html, 'id="view_tab_2"') && str_contains($html, 'id="view_tab_3"'),
            'la vista View no monta las pestanas de datos, permisos y observaciones');
        $this->addCase($results, 'view_pestanas_extra',
            str_contains($html, 'id="view_tab_4"') && str_contains($html, 'id="view_tab_5"'),
            'con permisos de bodegas y maquinas activos la vista View debe montar sus pestanas');
        $this->addCase($results, 'view_permisos', str_contains($html, 'Usuarios - Listado'),
            'la vista View no pinta los permisos del usuario');
        $this->addCase($results, 'view_observaciones', str_contains($html, 'Observacion de prueba'),
            'la vista View no pinta las observaciones');
        $this->addCase($results, 'view_bodegas_maquinas',
            str_contains($html, 'Bodega Central') && str_contains($html, 'Maquina Uno'),
            'la vista View no pinta los permisos de bodegas y maquinas');
        $this->addCase($results, 'view_pie_cierre', str_contains($html, 'data-bs-dismiss="modal"'),
            'la vista View no ofrece el pie de cierre del modal');

        // Sin permisos de bodegas/maquinas en sesion las pestanas extra no existen
        $htmlSinExtras = $this->renderizarVista($rutaView, $this->datosSinteticos(2, 1), $BASE, $f3);
        $this->addCase($results, 'view_sin_pestanas_extra',
            $htmlSinExtras !== '' && !str_contains($htmlSinExtras, 'id="view_tab_4"') && !str_contains($htmlSinExtras, 'id="view_tab_5"'),
            'sin usuariosPermisosBodegas/Maquinas la vista View no debe montar las pestanas 4 y 5');

        // Con listas vacias se informa en lugar de dejar la tabla en blanco
        $vacio                       = $this->datosSinteticos(2);
        $vacio['arrPermisos']        = [];
        $vacio['arrObservaciones']   = [];
        $htmlVacio = $this->renderizarVista($rutaView, $vacio, $BASE, $f3);
        $this->addCase($results, 'view_sin_datos', str_contains($htmlVacio, 'No se encontraron entradas'),
            'la vista View no informa cuando no hay permisos ni observaciones');

        /*====================================*/
        // Ficha de datos del usuario (Resumen-Update)
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen-Update.php'),
            $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'resumen_update_render', $html !== '', 'la vista Resumen-Update no produjo HTML');
        $this->addCase($results, 'resumen_update_datos',
            str_contains($html, 'tester@example.com') && str_contains($html, 'Usuario Prueba') && str_contains($html, 'Administrador'),
            'la ficha no pinta email, nombre y tipo de usuario');
        $this->addCase($results, 'resumen_update_secciones',
            str_contains($html, 'Datos del Perfil') && str_contains($html, 'Datos Personales')
            && str_contains($html, 'Social') && str_contains($html, 'Opciones'),
            'la ficha no dibuja todas sus secciones');
        $this->addCase($results, 'resumen_update_ubicacion',
            str_contains($html, 'Santiago') && str_contains($html, 'Providencia') && str_contains($html, 'Av. Siempreviva 742'),
            'la ficha no pinta ciudad, comuna y direccion');
        $this->addCase($results, 'resumen_update_imagen_defecto', str_contains($html, $BASE.'/img/picture-img.jpg'),
            'sin imagen propia la ficha debe usar la imagen por defecto');

        $conImagen                          = $this->datosSinteticos(2);
        $conImagen['rowData']['Direccion_img'] = 'usuario_7.png';
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen-Update.php'),
            $conImagen, $BASE, $f3);
        $this->addCase($results, 'resumen_update_imagen_propia', str_contains($html, '/admin/public/upload/usuario_7.png'),
            'con imagen propia la ficha debe apuntar a MainPathUrl + Direccion_img');

        /*====================================*/
        // Formulario de alta
        /*====================================*/
        $rutaNew = $this->rutaModulo('../views/'.self::CONTROLADOR.'-formNew.php');
        $html    = $this->renderizarVista($rutaNew, $this->datosSinteticos(3), $BASE, $f3);
        $this->addCase($results, 'form_new_render', $html !== '', 'la vista formNew no produjo HTML');
        $this->addCase($results, 'form_new_id', str_contains($html, 'id="FormNewData"'),
            'el formulario de alta no usa el id FormNewData');
        $this->addCase($results, 'form_new_modal', str_contains($html, 'id="newFormModal"'),
            'el formulario de alta no vive en el modal #newFormModal');
        $this->addCase($results, 'form_new_campos',
            str_contains($html, 'name="email"') && str_contains($html, 'name="Nombre"')
            && str_contains($html, 'name="Rut"') && str_contains($html, 'name="Direccion"'),
            'el formulario de alta no declara email/Nombre/Rut/Direccion');
        $this->addCase($results, 'form_new_selects',
            str_contains($html, 'name="idCiudad"') && str_contains($html, 'name="idComuna"') && str_contains($html, 'name="idTipoUsuario"'),
            'el formulario de alta no declara ciudad, comuna y tipo de usuario');
        $this->addCase($results, 'form_new_ocultos',
            str_contains($html, 'name="idEstado"') && str_contains($html, 'name="idMenuPosicion"'),
            'el formulario de alta no envia el estado y la posicion del menu ocultos');
        $this->addCase($results, 'form_new_sin_password', !str_contains($html, 'name="password"'),
            'el alta no debe pedir password: el servidor asigna la inicial');
        $this->addCase($results, 'form_new_aviso_password', str_contains($html, '1234'),
            'el alta no informa cual es la contrasena inicial');
        $this->addCase($results, 'form_new_endpoint',
            str_contains($html, $BASE.'/'.self::RUTA_WEB) && str_contains($html, "Metodo      = 'POST'"),
            'el formulario de alta no publica en '.self::RUTA_WEB.' con POST');
        $this->addCase($results, 'form_new_destino', str_contains($html, "DestinoFrom:'".$BASE.'/'.self::RUTA_WEB."/resumen/'"),
            'tras crear, el formulario debe redirigir a la pantalla de resumen');
        $this->addCase($results, 'form_new_boton_cerrar', str_contains($html, 'data-bs-dismiss="modal"'),
            'el formulario de alta no ofrece el boton Cerrar');

        /*====================================*/
        // Formulario de busqueda
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-formSearch.php'),
            $this->datosSinteticos(1), $BASE, $f3);
        $this->addCase($results, 'form_search_render', $html !== '', 'la vista formSearch no produjo HTML');
        $this->addCase($results, 'form_search_id', str_contains($html, 'id="FormSearchData"'),
            'el formulario de busqueda no usa el id FormSearchData');
        $this->addCase($results, 'form_search_inputs',
            str_contains($html, 'id="Search_email"') && str_contains($html, 'id="Search_Nombre"') && str_contains($html, 'id="Search_idTipoUsuario"'),
            'el formulario de busqueda no declara sus campos prefijados con Search_');
        $this->addCase($results, 'form_search_endpoint',
            str_contains($html, $BASE.'/'.self::RUTA_WEB.'/search') && str_contains($html, "Metodo      = 'POST'"),
            'el formulario de busqueda no apunta a /search con POST');
        $this->addCase($results, 'form_search_destino',
            str_contains($html, "UpdateDivFrom : 'listTableData'"),
            'el formulario de busqueda no refresca la tabla #listTableData');

        /*====================================*/
        // Pantalla de resumen: estatico (sus tres pestanas de permisos se incluyen
        // con require_once y pertenecen a los controladores de permisos)
        /*====================================*/
        $resumen = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen.php'));
        $this->addCase($results, 'resumen_fuente_disponible', $resumen !== '', 'no se pudo leer la vista Resumen');
        $this->addCase($results, 'resumen_pestanas',
            str_contains($resumen, '#resumen"') && str_contains($resumen, '#resumen-edit"')
            && str_contains($resumen, '#resumen-img"') && str_contains($resumen, '#resumen-permisos"')
            && str_contains($resumen, '#resumen-obs"'),
            'Resumen no declara las pestanas resumen, edicion, imagen, permisos y observaciones');
        $this->addCase($results, 'resumen_pestanas_condicionales',
            str_contains($resumen, '$data[\'UserData\']["usuariosPermisosBodegas"]==2')
            && str_contains($resumen, '$data[\'UserData\']["usuariosPermisosMaquinas"]==2'),
            'Resumen no condiciona las pestanas de bodegas y maquinas');
        $this->addCase($results, 'resumen_form_edicion', str_contains($resumen, 'id="FormEditData"'),
            'Resumen no declara el formulario FormEditData');
        $this->addCase($results, 'resumen_edicion_endpoint',
            str_contains($resumen, "'/update'") && str_contains($resumen, "Metodo      = 'POST'"),
            'Resumen no edita con POST contra /update');
        $this->addCase($results, 'resumen_refresco',
            str_contains($resumen, "'/resumenUpdate/'.\$DataID['data']"),
            'Resumen no refresca la ficha con /resumenUpdate/<id cifrado>');
        $this->addCase($results, 'resumen_borrar_imagen',
            str_contains($resumen, "Metodo      = 'PUT'") && str_contains($resumen, "'/delFiles'"),
            'Resumen no borra la imagen con PUT contra /delFiles');
        $this->addCase($results, 'resumen_observaciones',
            str_contains($resumen, '/observaciones/updateList/') && str_contains($resumen, '/observaciones/new/')
            && str_contains($resumen, '/observaciones/view/') && str_contains($resumen, '/observaciones/getID/'),
            'Resumen no enlaza los endpoints de observaciones');
        $this->addCase($results, 'resumen_observaciones_borrar',
            str_contains($resumen, "Metodo      = 'DELETE'") && str_contains($resumen, "'/observaciones'"),
            'Resumen no borra observaciones con DELETE');
        $this->addCase($results, 'resumen_id_cifrado',
            str_contains($resumen, "encryptDecrypt('encrypt', \$data['rowData']['idUsuario'])"),
            'Resumen no cifra el ID del usuario para las acciones');
        $this->addCase($results, 'resumen_password_opcional',
            str_contains($resumen, "'Name' => 'password'") && str_contains($resumen, "'Required' => 1"),
            'el campo password del resumen debe ser opcional');
        foreach (['Permisos-List', 'PermisosBodegas-List', 'PermisosMaquinas-List'] as $parte) {
            $this->addCase($results, 'resumen_incluye_'.$parte,
                str_contains($resumen, "require_once('".self::CONTROLADOR."-Resumen-".$parte.".php')")
                && is_file($this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen-'.$parte.'.php')),
                'Resumen no incluye una vista existente '.self::CONTROLADOR.'-Resumen-'.$parte.'.php');
        }
        $this->addCase($results, 'resumen_volver',
            str_contains($resumen, "/listAll'") && str_contains($resumen, 'Volver'),
            'Resumen no ofrece volver al listado');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos el chequeo de datos del controlador (`dataCheck`),
     * el cifrado de identificadores y la validacion de IDs descifrados.
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
            // Se crea sin constructor: dataCheck() es logica pura y no necesita
            // la conexion a base de datos del controlador.
            $Controller = (new ReflectionClass(self::CONTROLADOR))->newInstanceWithoutConstructor();

            $Post      = ['email' => 'tester@example.com', 'Nombre' => 'Usuario Prueba', 'idEstado' => 1, 'idTipoUsuario' => 2];
            $DataCheck = $reflector->invoke($Controller, $Post);

            $this->addCase($results, 'datacheck_es_arreglo', is_array($DataCheck),
                'dataCheck() no devolvio un arreglo');
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post,
                'dataCheck() no conserva el POST recibido');
            $this->addCase($results, 'datacheck_email',
                ($DataCheck['ValidarEmail'] ?? '') === 'email'
                && ($DataCheck['ValidarDominioEmail'] ?? '') === 'email'
                && ($DataCheck['ValidarMayusculas'] ?? '') === 'email',
                'dataCheck() no valida el email (formato, dominio y mayusculas)');
            $this->addCase($results, 'datacheck_numericos',
                ($DataCheck['ValidarNumero'] ?? '') === 'idTipoUsuario,idEstado,Fono,idCiudad,idComuna,idMenuPosicion'
                && ($DataCheck['ValidarEntero'] ?? '') === 'idTipoUsuario,idEstado,idCiudad,idComuna,idMenuPosicion',
                'dataCheck() no valida los identificadores como numeros enteros');
            $this->addCase($results, 'datacheck_rut', ($DataCheck['ValidarRut'] ?? '') === 'Rut',
                'dataCheck() no valida el Rut');
            $this->addCase($results, 'datacheck_fechas', ($DataCheck['ValidarFecha'] ?? '') === 'fNacimiento,Ultimo_acceso',
                'dataCheck() no valida las fechas del usuario');
            $this->addCase($results, 'datacheck_urls',
                ($DataCheck['ValidarURL'] ?? '') === 'Social_X,Social_Facebook,Social_Instagram,Social_Linkedin',
                'dataCheck() no valida las URL de redes sociales');
            $this->addCase($results, 'datacheck_ip', ($DataCheck['ValidarIP'] ?? '') === 'IP_Client',
                'dataCheck() no valida la IP del cliente');
            $this->addCase($results, 'datacheck_largo_minimo',
                ($DataCheck['ValidarLargoMinimo'] ?? '') === 'email,Nombre,Direccion'
                && (int)($DataCheck['ValidarLargoMinimoN'] ?? 0) === 3,
                'dataCheck() no fija el largo minimo de email/Nombre/Direccion');
            $this->addCase($results, 'datacheck_largo_maximo',
                ($DataCheck['ValidarLargoMaximoPorCampo'] ?? []) === [
                    'Rut'              => 13,
                    'Fono'             => 15,
                    'Social_X'         => 255,
                    'Social_Facebook'  => 255,
                    'Social_Instagram' => 255,
                    'Social_Linkedin'  => 255,
                    'Agent_Transp'     => 240,
                    'IP_Client'        => 120,
                    'email'            => 60,
                    'Nombre'           => 60,
                    'Direccion'        => 60,
                ],
                'dataCheck() no fija los largos maximos por campo segun el esquema');
            $this->addCase($results, 'datacheck_texto_limpio',
                ($DataCheck['ValidarPalabrasCensuradas'] ?? '') === 'Nombre,Direccion'
                && ($DataCheck['ValidarEspaciosVacios'] ?? '') === 'email,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin',
                'dataCheck() no censura palabras ni rechaza espacios en campos sensibles');

            // Todas las claves de validacion deben ser cadenas (o el arreglo de largos / el POST)
            $mal = [];
            foreach ($DataCheck as $clave => $valor) {
                if (in_array($clave, ['Post', 'ValidarLargoMaximoPorCampo', 'ValidarLargoMinimoN'], true)) {
                    continue;
                }
                if (!is_string($valor)) {
                    $mal[] = $clave;
                }
            }
            $this->addCase($results, 'datacheck_claves_texto', count($mal) === 0,
                'claves de dataCheck() que no son texto: '.implode(', ', $mal));

            // El POST devuelto es el recibido, sin modificaciones
            $otro = $reflector->invoke($Controller, ['Nombre' => 'X']);
            $this->addCase($results, 'datacheck_post_independiente', ($otro['Post'] ?? null) === ['Nombre' => 'X'],
                'dataCheck() no debe arrastrar datos de una invocacion anterior');
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_es_arreglo', false,
                'no se pudo ejecutar dataCheck() ('.$e->getMessage().')');
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
            $this->addCase($results, 'cifrado_oculta_valor', (string)($cifrado['data'] ?? '') !== '42',
                'el valor cifrado no debe coincidir con el ID original');

            $descifrado = $Codification->encryptDecrypt('decrypt', (string)($cifrado['data'] ?? ''));
            $this->addCase($results, 'cifrado_ida_y_vuelta',
                ($descifrado['success'] ?? false) === true && (string)($descifrado['data'] ?? '') === '42',
                'el cifrado no devuelve el valor original');

            // Un ID manipulado no debe poder validarse como identificador
            $manipulado = $Codification->encryptDecrypt('decrypt', 'valor-manipulado');
            $this->addCase($results, 'cifrado_rechaza_manipulado',
                $this->isValidDecrypted((array)$manipulado, 'id') === false,
                'un ID manipulado no debe validarse como identificador');
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
        $this->addCase($results, 'id_valido_sin_dato', $this->isValidDecrypted(['success' => true], 'id') === false,
            'un descifrado sin dato debe rechazarse');
        $this->addCase($results, 'id_valido_texto', $this->isValidDecrypted(['success' => true, 'data' => 'abc'], 'id') === false,
            'un ID no numerico debe rechazarse para tipo id');
        $this->addCase($results, 'id_valido_cero', $this->isValidDecrypted(['success' => true, 'data' => '0'], 'id') === false,
            'el ID 0 debe rechazarse para tipo id');
        $this->addCase($results, 'id_valido_negativo', $this->isValidDecrypted(['success' => true, 'data' => '-5'], 'id') === false,
            'un ID negativo debe rechazarse para tipo id');
        $this->addCase($results, 'id_valido_tipo_texto', $this->isValidDecrypted(['success' => true, 'data' => 'abc'], 'text') === true,
            'un texto no vacio debe ser valido para tipo text');
        $this->addCase($results, 'id_valido_texto_vacio', $this->isValidDecrypted(['success' => true, 'data' => '  '], 'text') === false,
            'un texto en blanco debe rechazarse para tipo text');
        $this->addCase($results, 'id_valido_tipo_desconocido', $this->isValidDecrypted(['success' => true, 'data' => 'abc'], 'otro') === false,
            'un tipo de dato desconocido debe rechazarse');

        /*====================================*/
        // Rutas de validacion de los metodos de escritura: el verbo incorrecto se
        // atiende antes de tocar la base de datos (solo lectura del fuente, porque
        // Response::error() hace exit)
        /*====================================*/
        foreach (['Update' => 'POST', 'Delete' => 'DELETE', 'delFiles' => 'PUT'] as $metodo => $verbo) {
            $cuerpo = $this->cuerpoMetodo($metodo);
            $posVerbo = strpos($cuerpo, "REQUEST_METHOD'] !== '".$verbo."'");
            $posQuery = strpos($cuerpo, '$query = [');
            $this->addCase($results, 'verbo_antes_de_escribir_'.$metodo,
                $posVerbo !== false && $posQuery !== false && $posVerbo < $posQuery,
                $metodo.'() debe validar el verbo '.$verbo.' antes de armar la consulta');
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                 SEGURIDAD                                  */
    /******************************************************************************/
    /**
     * Revisa estaticamente las vistas y el controlador del modulo: ausencia de
     * rutas relativas maliciosas, de rutas absolutas del sistema de ficheros, de
     * acceso directo a la sesion desde las plantillas, escape de los datos que
     * pintan el listado y manejo seguro de credenciales en el controlador.
     *
     * @return array Resultado por caso
     */
    public function seguridadVistasTest(){

        $results = [];

        /*====================================*/
        // Vistas involucradas
        /*====================================*/
        $arrVistas = glob($this->rutaModulo('../views/'.self::CONTROLADOR.'-*.php')) ?: [];
        $this->addCase($results, 'hay_vistas', count($arrVistas) >= 7,
            'se esperaban al menos 7 vistas del controlador '.self::CONTROLADOR.' y hay '.count($arrVistas));

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
            $this->addCase($results, 'vista_sin_password_'.$nombre,
                !preg_match('/\$data\[[\'"]rowData[\'"]\]\[[\'"]password[\'"]\]/', $codigo),
                $nombre.' pinta el hash de la password del usuario');
        }

        /*====================================*/
        // Escape e identificadores en el listado
        /*====================================*/
        // La tabla y sus filas (parcial -UpdateListRows, ADR-0022)
        $tabla = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'))
            . $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateListRows.php'));
        $this->addCase($results, 'tabla_escape_entidad', str_contains($tabla, 'addslashes('),
            'UpdateList.php no escapa la etiqueta de la entidad usada en el JavaScript de borrado');
        $this->addCase($results, 'tabla_id_cifrado', str_contains($tabla, "encryptDecrypt('encrypt', \$crud['idUsuario'])"),
            'UpdateList.php no cifra el ID antes de pintarlo en las acciones');
        $this->addCase($results, 'tabla_fecha_formateada', str_contains($tabla, 'fechaEstandar('),
            'UpdateList.php no formatea la fecha del ultimo acceso');
        $this->addCase($results, 'tabla_acciones_por_nivel',
            str_contains($tabla, '$level >= 1') && str_contains($tabla, '$level >= 2') && str_contains($tabla, '$level >= 4'),
            'UpdateList.php no condiciona las acciones al nivel de acceso');

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
        $this->addCase($results, 'controlador_sin_get_directo',
            !preg_match('/\$_(GET|REQUEST)\b/', $controlador),
            'el controlador lee $_GET/$_REQUEST directamente');

        /*====================================*/
        // Los IDs interpolados en subconsultas de Resumen solo llegan ya validados
        // como enteros positivos (isValidDecrypted tipo id), nunca como texto libre
        /*====================================*/
        $resumen = $this->cuerpoMetodo('Resumen');
        $this->addCase($results, 'resumen_interpolacion_validada',
            substr_count($resumen, "'.\$UsuarioID['data'].'") === 3
            && strpos($resumen, "isValidDecrypted(\$UsuarioID, 'id')") < strpos($resumen, "'.\$UsuarioID['data'].'"),
            'las subconsultas de Resumen() deben interpolar solo el ID validado como entero positivo');
        $this->addCase($results, 'resumen_where_parametrizado',
            substr_count($resumen, "'params'  => [1]") >= 3,
            'las consultas de catalogo de Resumen() deben usar parametros preparados');

        /*====================================*/
        // Credenciales: la password nunca viaja en claro ni se devuelve
        /*====================================*/
        $insert = $this->cuerpoMetodo('Insert');
        $update = $this->cuerpoMetodo('Update');
        $this->addCase($results, 'password_cifrada_en_escritura',
            str_contains($insert, "'encode'    => 'password'") && str_contains($update, "'encode'    => 'password'"),
            'Insert()/Update() deben cifrar la password con el parametro encode');
        $this->addCase($results, 'password_no_responde',
            !str_contains($insert, "Response::success(\$_POST") && !str_contains($update, "Response::success(\$_POST"),
            'Insert()/Update() no deben devolver el POST (con la password) al cliente');
        $this->addCase($results, 'password_cambio_forzado_alta',
            str_contains($insert, "\$_POST['PasswordForzarCambio'] = 1"),
            'la password inicial del alta debe forzar el cambio en el primer ingreso');
        $this->addCase($results, 'password_no_se_registra',
            !preg_match('/(error_log|var_dump|print_r|echo)\s*\(?[^;]*\$_POST/', $controlador),
            'el controlador no debe volcar el POST (con la password) a logs ni a la salida');

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
        $vista   = __DIR__.'/../views/usuariosListado-List.php';
        $fuente  = is_file($vista) ? (string)file_get_contents($vista) : '';

        /*====================================*/
        // La vista llama a updateList desde deleteFilter() y el boton no tiene guarda de nivel
        /*====================================*/
        $usaUpdateList = preg_match('/function deleteFilter.*?\/updateList/s', $fuente) === 1;
        $results['quitar_filtro_usa_updatelist'] = $usaUpdateList
            ? 'OK' : 'FALLO: la vista usuariosListado-List.php ya no refresca con updateList desde deleteFilter()';

        /*====================================*/
        // Nivel de listAll y de updateList por cada ruta base del controlador
        /*====================================*/
        $niveles = $this->nivelesListadoRutas('usuariosListado');
        $results['rutas_listado_leidas'] = !empty($niveles)
            ? 'OK' : 'FALLO: no se encontraron rutas listAll/updateList de usuariosListado en el instalador';

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
