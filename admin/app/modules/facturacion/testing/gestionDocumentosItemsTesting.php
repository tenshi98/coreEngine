<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class gestionDocumentosItemsTesting
 *
 * Pruebas del modulo `facturacion` - controlador `gestionDocumentosItems` (lineas libres de un documento mercantil).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. Los textos de los casos
 *    correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Alcance: pruebas NO destructivas. `Insert()`, `Update()` y `Delete()` abren transaccion, escriben en
 * `facturacion_listado_items`, recalculan el documento con `gestionDocumentos::updateFact()` y terminan
 * en `Response::*()` (`exit`); NO se invocan: se verifican por reflexion y lectura del fuente. Solo se
 * ejecuta logica pura (`tsrxName`, `dataCheck`, cifrado de IDs).
 *
 * El controlador reutiliza permisos y vistas de `gestionDocumentos` ($this->controllerName) y atiende
 * dos tipos con wrappers (`New_1`/`New_2`...): tipo 1 = compras (`gestionDocumentosCompras`) y tipo 2 =
 * ventas (`gestionDocumentosVentas`). Las acciones de editar y borrar solo existen con el documento
 * pendiente (estado de pago 1).
 *
 * @see skills/testing/SKILL.md
 */
class gestionDocumentosItemsTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR      = 'gestionDocumentosItems';
    private const VISTA_BASE       = 'gestionDocumentos-Resumen-Items';
    private const INSTALADOR       = 'gestionDocumentosInstaller';
    private const RUTA_COMPRAS     = 'gestionDocumentos/compras/listado';
    private const RUTA_VENTAS      = 'gestionDocumentos/ventas/listado';
    private const RUTA_INFORME     = 'gestionDocumentos/informe/busqueda/listado';
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
    // Contrato de presentacion: el controlador compone la vista como
    // $this->controllerName.'-<sufijo>.php' y la sirve con el tipo indicado
    /*******************************************************************/
    private function contratoPresentacion(array &$results, string $metodo, string $sufijoVista, string $tipoVista){
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'vista_'.$metodo, str_contains($codigo, "\$this->controllerName.'-".$sufijoVista.".php'"),
            $metodo.'() no instancia la vista '.self::CONTROLADOR.'-'.$sufijoVista);
        $this->addCase($results, 'vista_existe_'.$metodo, is_file($this->rutaModulo('../views/'.self::CONTROLADOR.'-'.$sufijoVista.'.php')),
            'no existe el archivo de la vista '.self::CONTROLADOR.'-'.$sufijoVista);
        $this->addCase($results, 'tipoview_'.$metodo,
            (bool)preg_match("/showVista\(".$tipoVista.",[^;]*-".preg_quote($sufijoVista, '/')."\.php'\)/", $codigo),
            $metodo.'() no sirve la vista '.$sufijoVista.' con showVista('.$tipoVista.')');
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
            foreach ([1, 2, 3] as $tipo) {
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
    private function datosSinteticos(int $nivel, string $ruta = self::RUTA_VENTAS, int $idTipo = 2, int $estadoPago = 1): array{
        return [
            'UserData'         => [
                'MainPathUrl'          => '/admin/public/upload/',
                'sistemaModalSubtitle' => 1,
                'sistemaModalCloseBTN' => 2,
                'UserType'             => 2,
            ],
            'UserAccess'       => ['LevelAccess' => $nivel, 'RouteAccess' => $ruta],
            'idTipo'           => $idTipo,
            'Fnc_FormInputs'   => new UIFormInputs(),
            'Fnc_Codification' => new FunctionsSecurityCodification(),
            'Fnc_DataNumbers'  => new FunctionsDataNumbers(),
            'arrItems'         => [[
                'idExistencia' => 55,
                'Item'         => 'Item de prueba',
                'Number'       => 2,
                'ValorTotal'   => 20000,
            ]],
            'rowData'          => [
                'idExistencia'  => 55,
                'idFacturacion' => 41,
                'idEstadoPago'  => $estadoPago,
                'Item'          => 'Item de prueba',
                'Number'        => 2,
                'ValorTotal'    => 20000,
            ],
        ];
    }

    /*******************************************************************/
    // Instancia el controlador SIN constructor (logica pura, sin base de datos)
    /*******************************************************************/
    private function instanciaSinBD(){
        return (new ReflectionClass(self::CONTROLADOR))->newInstanceWithoutConstructor();
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica los archivos del controlador `gestionDocumentosItems` y la convencion
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
            'instalador'        => $this->rutaModulo('../installer/gestionDocumentosInstaller.php'),
            'vista_form_new'    => $this->rutaModulo('../views/'.self::VISTA_BASE.'-formNew.php'),
            'vista_form_edit'   => $this->rutaModulo('../views/'.self::VISTA_BASE.'-formEdit.php'),
            'vista_update_list' => $this->rutaModulo('../views/'.self::VISTA_BASE.'-UpdateList.php'),
            'testing'           => $this->rutaModulo('gestionDocumentosItemsTesting.php'),
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
        $this->addCase($results, 'clase_testing_cargada', class_exists('gestionDocumentosItemsTesting'),
            'la clase gestionDocumentosItemsTesting no esta disponible');
        $padre = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padre === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padre.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('gestionDocumentosItemsTesting') === 'ControllerBase',
            'el testing no extiende ControllerBase');

        /*====================================*/
        // Constructor del controlador
        /*====================================*/
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'constructor_conexion', str_contains($codigo, 'Database::getSQLConnection(ConfigDataBase::MySQL_1)'),
            'el constructor no obtiene la conexion MySQL_1');
        $this->addCase($results, 'constructor_padre', str_contains($codigo, 'parent::__construct($DB_conn_1, $queryBuilder, $checkData)'),
            'el constructor no delega en ControllerBase');
        $this->addCase($results, 'constructor_permisos', str_contains($codigo, "\$this->controllerName = 'gestionDocumentos'"),
            'controllerName debe reutilizar los permisos de gestionDocumentos');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                            CONTROLADOR: VISTAS                             */
    /******************************************************************************/
    /**
     * Verifica los wrappers por tipo (`New_1/2`, `UpdateList_1/2`, `GetID_1/2`), los metodos
     * base `New`, `UpdateList` y `GetID`, el permiso segun el tipo y las consultas de lectura.
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Metodos base y wrappers
        /*====================================*/
        foreach (['New', 'UpdateList', 'GetID'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 3, 3);
            foreach ([1, 2] as $tipo) {
                $this->firmaMetodo($results, $metodo.'_'.$tipo, 2, 2);
                $this->addCase($results, 'wrapper_'.$metodo.'_'.$tipo, str_contains($codigo, '$this->'.$metodo.'($f3, $params, '.$tipo.')'),
                    $metodo.'_'.$tipo.'() no delega en '.$metodo.'($f3, $params, '.$tipo.')');
            }
        }

        /*====================================*/
        // Contrato de presentacion
        /*====================================*/
        foreach (['New' => 'formNew', 'UpdateList' => 'UpdateList', 'GetID' => 'formEdit'] as $metodo => $sufijo) {
            $this->addCase($results, 'vista_'.$metodo, str_contains($codigo, "\$this->controllerName.'-Resumen-Items-".$sufijo.".php'"),
                $metodo.'() no instancia la vista '.self::VISTA_BASE.'-'.$sufijo);
            $this->addCase($results, 'vista_existe_'.$metodo, is_file($this->rutaModulo('../views/'.self::VISTA_BASE.'-'.$sufijo.'.php')),
                'no existe el archivo de la vista '.self::VISTA_BASE.'-'.$sufijo);
        }
        $this->addCase($results, 'tipoview_modal', substr_count($codigo, 'showVista(2,') === 3,
            'las tres vistas deben servirse como modal/parcial con showVista(2,)');
        $this->addCase($results, 'rutavista_resuelta', substr_count($codigo, 'returnRutaVista(__DIR__') === 3,
            'las vistas no resuelven su ruta con returnRutaVista()');
        $this->addCase($results, 'errores_showerror', substr_count($codigo, 'showError(2, $f3, $result)') === 3,
            'las vistas no manejan el fallo de consulta con showError()');
        $this->addCase($results, 'fallback_mergeresponses', substr_count($codigo, 'mergeResponses(') === 3,
            'las vistas no agrupan respuestas con mergeResponses()');
        $this->addCase($results, 'usuario_en_vistas', substr_count($codigo, 'getUserData($f3)') === 3,
            'las vistas no obtienen los datos del usuario');
        $this->addCase($results, 'nivel_por_tipo', substr_count($codigo, '$tsrxName = $this->tsrxName($idTipo);') === 3
            && substr_count($codigo, 'getArrLevel($f3, $tsrxName)') === 3,
            'las vistas deben resolver el permiso segun el tipo con tsrxName($idTipo)');
        $this->addCase($results, 'envia_tipo', substr_count($codigo, "'idTipo'") === 2,
            'New() y UpdateList() deben entregar idTipo a la vista (GetID no lo necesita)');

        /*====================================*/
        // Consultas de lectura
        /*====================================*/
        $this->addCase($results, 'new_documento_existe',
            str_contains($codigo, "'table'   => 'facturacion_listado'") && str_contains($codigo, "'where'   => 'idFacturacion = ?'"),
            'New() no verifica que el documento exista');
        $this->addCase($results, 'updatelist_estado_pago', str_contains($codigo, "'data'    => 'idEstadoPago'"),
            'UpdateList() no consulta el estado de pago que habilita las acciones');
        $this->addCase($results, 'updatelist_items_por_documento',
            str_contains($codigo, "'data'    => 'idExistencia,Item,Number,ValorTotal'") && str_contains($codigo, "'order'   => 'idExistencia ASC'"),
            'UpdateList() no lista los items del documento ordenados');
        $this->addCase($results, 'getid_por_existencia',
            str_contains($codigo, "'data'    => 'idExistencia,idFacturacion,Item,Number,ValorTotal'") && str_contains($codigo, "'where'   => 'idExistencia = ?'"),
            'GetID() no consulta el item por idExistencia');
        $this->addCase($results, 'consultas_limite', substr_count($codigo, 'ConfigAPP::APP["N_MaxItems"]') === 1,
            'solo UpdateList() debe acotar el numero de filas con N_MaxItems');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                       CONTROLADOR: RUTAS E INSTALADOR                      */
    /******************************************************************************/
    /**
     * Verifica que las 12 rutas de `gestionDocumentosItems` (6 de compras y 6 de ventas)
     * apunten a metodos reales con el tipo, verbo y nivel esperados.
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
        $esperadas = [];
        foreach ([1 => [self::RUTA_COMPRAS, 'compras'], 2 => [self::RUTA_VENTAS, 'ventas']] as $tipo => $datos) {
            [$base, $nombre] = $datos;
            $esperadas[$nombre.'_nuevo']     = ['1 '.$base.'/items/new/@id',        'gestionDocumentosItems->New_'.$tipo,        2];
            $esperadas[$nombre.'_refrescar'] = ['1 '.$base.'/items/updateList/@id', 'gestionDocumentosItems->UpdateList_'.$tipo, 2];
            $esperadas[$nombre.'_getid']     = ['1 '.$base.'/items/getID/@id',      'gestionDocumentosItems->GetID_'.$tipo,      2];
            $esperadas[$nombre.'_crear']     = ['2 '.$base.'/items',                'gestionDocumentosItems->Insert',            2];
            $esperadas[$nombre.'_editar']    = ['2 '.$base.'/items/update',         'gestionDocumentosItems->Update',            2];
            $esperadas[$nombre.'_borrar']    = ['3 '.$base.'/items',                'gestionDocumentosItems->Delete',            2];
        }
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

        $propias = array_filter($rutas, static function ($r) { return $r[2] === 'gestionDocumentosItems'; });
        $this->addCase($results, 'rutas_propias_completas', count($propias) === 12,
            'se esperaban 12 rutas de '.self::CONTROLADOR.' y hay '.count($propias));
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
        // Ninguna ruta debe usar el wrapper del tipo contrario a su ruta
        /*====================================*/
        $cruzadas = [];
        foreach ($propias as $clave => $datos) {
            if (str_contains($clave, '/ventas/') && preg_match('/_1$/', $datos[0])) { $cruzadas[] = $clave; }
            if (str_contains($clave, '/compras/') && preg_match('/_2$/', $datos[0])) { $cruzadas[] = $clave; }
        }
        $this->addCase($results, 'rutas_sin_tipo_cruzado', count($cruzadas) === 0,
            'rutas con el wrapper del tipo contrario: '.implode(' | ', $cruzadas));

        /*====================================*/
        // Desinstalacion: el sub-recurso debe estar en RutaController()
        /*====================================*/
        $instalador = $this->codigoFuente($this->rutaModulo('../installer/gestionDocumentosInstaller.php'));
        $this->addCase($results, 'instalador_desinstala_subrecurso', str_contains($instalador, "\$RutaController .= ',\"gestionDocumentosItems\"'"),
            'RutaController() no lista gestionDocumentosItems y dejaria rutas huerfanas al desinstalar');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                PERMISOS                                    */
    /******************************************************************************/
    /**
     * Verifica la sesion, el nivel de acceso de Compras y Ventas (permisos heredados de
     * `gestionDocumentos`) y el registro de las rutas de items en el router de F3.
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
        // Lectura de la sesion y nivel de cada permiso
        /*====================================*/
        try {
            $this->addCase($results, 'get_user_data_arreglo', is_array($this->getUserData($f3)),
                'getUserData() no devolvio un arreglo');
        } catch (Throwable $e) {
            $this->addCase($results, 'get_user_data_arreglo', false,
                'no se pudo leer la sesion del controlador ('.$e->getMessage().')');
        }
        foreach (['gestionDocumentosCompras' => self::RUTA_COMPRAS, 'gestionDocumentosVentas' => self::RUTA_VENTAS] as $permiso => $rutaWeb) {
            try {
                $arrLevel = $this->getArrLevel($f3, $permiso);
            } catch (Throwable $e) {
                $this->addCase($results, 'nivel_'.$permiso, false, 'no se pudo leer el nivel de '.$permiso.' ('.$e->getMessage().')');
                continue;
            }
            $nivel = is_array($arrLevel) ? ($arrLevel['LevelAccess'] ?? null) : null;
            $this->addCase($results, 'nivel_'.$permiso, $nivel === null || in_array((int)$nivel, self::NIVELES, true),
                'LevelAccess de '.$permiso.' quedo fuera del catalogo 1..4');
            $this->addCase($results, 'ruta_acceso_'.$permiso, $nivel === null || ($arrLevel['RouteAccess'] ?? '') === $rutaWeb,
                'RouteAccess de '.$permiso.' no coincide con '.$rutaWeb);
        }

        /*====================================*/
        // Rutas en SESSION.arrPermisos y en el router (solo si las tiene)
        /*====================================*/
        $arrPermisos = $f3->get('SESSION.arrPermisos');
        $this->addCase($results, 'permisos_de_ruta_presentes', $arrPermisos === null || is_array($arrPermisos),
            'SESSION.arrPermisos no es un arreglo');
        $arrRoutes = $f3->get('ROUTES');
        $this->addCase($results, 'router_disponible', is_array($arrRoutes), 'F3 no expuso la variable ROUTES');
        $esquema = [];
        foreach ([self::RUTA_COMPRAS => 'compras', self::RUTA_VENTAS => 'ventas'] as $ruta => $nombre) {
            $esquema[$nombre.'_insert_router'] = ['/'.$ruta.'/items',        'POST',   self::CONTROLADOR.'->Insert'];
            $esquema[$nombre.'_update_router'] = ['/'.$ruta.'/items/update', 'POST',   self::CONTROLADOR.'->Update'];
            $esquema[$nombre.'_delete_router'] = ['/'.$ruta.'/items',        'DELETE', self::CONTROLADOR.'->Delete'];
        }
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
     * Renderiza `UpdateList`, `formNew` y `formEdit` de items para compras y ventas: las acciones
     * de editar y borrar solo existen con el documento pendiente (estado de pago 1), el ID viaja
     * cifrado y los formularios publican en la ruta del tipo con su verbo.
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function vistasItemsTest($f3){

        $results = [];
        $BASE    = (string)$f3->get('BASE');
        if (trim($BASE) === '') {
            $BASE = '/admin';
        }

        /*====================================*/
        // Tabla de items: documento pendiente (estado de pago 1) frente a otros estados
        /*====================================*/
        $rutaTabla = $this->rutaModulo('../views/'.self::VISTA_BASE.'-UpdateList.php');
        foreach (self::NIVELES as $nivel) {
            $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos($nivel), $BASE, $f3);
            $this->addCase($results, 'tabla_render_nivel'.$nivel, $tabla !== '', 'UpdateList no produjo HTML con nivel '.$nivel);
            $this->addCase($results, 'tabla_acciones_nivel'.$nivel,
                str_contains($tabla, 'tabItemEdit(') && str_contains($tabla, 'tabItemDel('),
                'con el documento pendiente la tabla no ofrece editar y borrar con nivel '.$nivel);
        }
        $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos(2, self::RUTA_VENTAS, 2, 1), $BASE, $f3);
        $this->addCase($results, 'tabla_datos', str_contains($tabla, 'Item de prueba'), 'la tabla no pinta el item sintetico');
        $this->addCase($results, 'tabla_columnas',
            str_contains($tabla, '>Item<') && str_contains($tabla, '>Cantidad<') && str_contains($tabla, '>Valor<') && str_contains($tabla, '>Acciones<'),
            'la tabla no declara las columnas Item, Cantidad, Valor y Acciones');
        $this->addCase($results, 'tabla_id_cifrado',
            !str_contains($tabla, "tabItemEdit('55')") && !str_contains($tabla, "tabItemDel( '55'"),
            'la tabla expone idExistencia sin cifrar');

        foreach ([2 => 'pagado', 3 => 'anulado'] as $estado => $nombre) {
            $noPendiente = $this->renderizarVista($rutaTabla, $this->datosSinteticos(4, self::RUTA_VENTAS, 2, $estado), $BASE, $f3);
            $this->addCase($results, 'tabla_'.$nombre.'_sin_acciones',
                $noPendiente !== '' && str_contains($noPendiente, 'Item de prueba') && !str_contains($noPendiente, 'tabItemEdit(')
                && !str_contains($noPendiente, 'tabItemDel(') && !str_contains($noPendiente, '>Acciones<'),
                'con el estado de pago '.$estado.' la tabla ofrece editar o borrar items');
        }

        $vacio = $this->datosSinteticos(2);
        $vacio['arrItems'] = [];
        $tablaVacia = $this->renderizarVista($rutaTabla, $vacio, $BASE, $f3);
        $this->addCase($results, 'tabla_vacia', $tablaVacia !== '' && str_contains($tablaVacia, '<tbody>') && !str_contains($tablaVacia, 'tabItemEdit('),
            'la tabla sin datos pinta filas o no se dibuja');

        $comillas = $this->datosSinteticos(2);
        $comillas['arrItems'][0]['Item'] = "Item d'prueba";
        $tablaComillas = $this->renderizarVista($rutaTabla, $comillas, $BASE, $f3);
        $this->addCase($results, 'tabla_escapa_comillas', str_contains($tablaComillas, "Item d\\'prueba"),
            'la etiqueta del borrado no escapa las comillas simples');

        /*====================================*/
        // Formularios de alta y edicion para compras y ventas
        /*====================================*/
        foreach ([2 => self::RUTA_VENTAS, 1 => self::RUTA_COMPRAS] as $tipo => $ruta) {
            $etiqueta = $tipo === 1 ? 'compras' : 'ventas';
            $html = $this->renderizarVista($this->rutaModulo('../views/'.self::VISTA_BASE.'-formNew.php'),
                $this->datosSinteticos(2, $ruta, $tipo), $BASE, $f3);
            $this->addCase($results, 'form_new_render_'.$etiqueta, $html !== '', 'formNew de '.$etiqueta.' no produjo HTML');
            $this->addCase($results, 'form_new_id_'.$etiqueta, str_contains($html, 'id="FormNewItem"'),
                'formNew de '.$etiqueta.' no usa el id FormNewItem');
            $this->addCase($results, 'form_new_campos_'.$etiqueta,
                str_contains($html, 'name="Item"') && str_contains($html, 'name="Number"') && str_contains($html, 'name="ValorTotal"'),
                'formNew de '.$etiqueta.' no declara Item/Number/ValorTotal');
            $this->addCase($results, 'form_new_ocultos_'.$etiqueta,
                (bool)preg_match('/name="idFacturacion"[^>]*value="41"|value="41"[^>]*name="idFacturacion"/', $html) && !str_contains($html, 'name="idExistencia"'),
                'formNew de '.$etiqueta.' debe enviar idFacturacion (41) y no idExistencia');
            $this->addCase($results, 'form_new_endpoint_'.$etiqueta,
                str_contains($html, $BASE.'/'.$ruta.'/items\'') && str_contains($html, "let Metodo      = 'POST'"),
                'formNew de '.$etiqueta.' no publica en '.$ruta.'/items con POST');
            $this->addCase($results, 'form_new_refresca_tabla_'.$etiqueta,
                str_contains($html, '#tabItemDataTable') && str_contains($html, $ruta.'/items/updateList/') && !str_contains($html, '/items/updateList/41'),
                'formNew de '.$etiqueta.' no refresca la tabla con el ID cifrado');

            $html = $this->renderizarVista($this->rutaModulo('../views/'.self::VISTA_BASE.'-formEdit.php'),
                $this->datosSinteticos(2, $ruta, $tipo), $BASE, $f3);
            $this->addCase($results, 'form_edit_render_'.$etiqueta, $html !== '', 'formEdit de '.$etiqueta.' no produjo HTML');
            $this->addCase($results, 'form_edit_id_'.$etiqueta, str_contains($html, 'id="FormEditItem"'),
                'formEdit de '.$etiqueta.' no usa el id FormEditItem');
            $this->addCase($results, 'form_edit_ocultos_'.$etiqueta,
                str_contains($html, 'name="idExistencia"') && str_contains($html, 'name="idFacturacion"'),
                'formEdit de '.$etiqueta.' no envia idExistencia e idFacturacion');
            $this->addCase($results, 'form_edit_precarga_'.$etiqueta, str_contains($html, 'Item de prueba'),
                'formEdit de '.$etiqueta.' no precarga el item');
            $this->addCase($results, 'form_edit_endpoint_'.$etiqueta,
                str_contains($html, $BASE.'/'.$ruta.'/items/update') && str_contains($html, "let Metodo      = 'POST'"),
                'formEdit de '.$etiqueta.' no publica en '.$ruta.'/items/update con POST');
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                         CONTROLADOR: ESCRITURA (CRUD)                      */
    /******************************************************************************/
    /**
     * Verifica `Insert`, `Update` y `Delete` SIN ejecutarlos: verbo HTTP, chequeo de datos,
     * transaccion completa, recalculo de montos del documento y ID cifrado en el borrado.
     *
     * @return array Resultado por caso
     */
    public function controladorEscrituraTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Firma de cada metodo de escritura (sin parametros)
        /*====================================*/
        foreach (['Insert', 'Update', 'Delete'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 0, 0);
        }

        /*====================================*/
        // Tabla y chequeo de datos
        /*====================================*/
        $this->addCase($results, 'datacheck_post', substr_count($codigo, '$this->dataCheck($_POST)') === 2,
            'Insert() y Update() deben construir el DataCheck con dataCheck($_POST)');
        $this->addCase($results, 'tabla_items', preg_match_all("/'table'\s*=>\s*'facturacion_listado_items'/", $codigo) === 6,
            'se esperaban 6 usos de facturacion_listado_items (3 lecturas y Insert/Update/Delete)');

        /*====================================*/
        // Insert
        /*====================================*/
        $this->addCase($results, 'insert_campos', str_contains($codigo, "'data'      => 'idFacturacion,Item,Number,ValorTotal'"),
            'Insert() no declara los campos idFacturacion,Item,Number,ValorTotal');
        $this->addCase($results, 'insert_requeridos', str_contains($codigo, "'required'  => 'idFacturacion,Item,ValorTotal'"),
            'Insert() no declara los obligatorios idFacturacion,Item,ValorTotal');
        $this->addCase($results, 'insert_base', str_contains($codigo, '$this->Base_insert('),
            'Insert() no escribe con Base_insert()');

        /*====================================*/
        // Update
        /*====================================*/
        $this->addCase($results, 'update_verbo_post', str_contains($codigo, "REQUEST_METHOD'] !== 'POST'"),
            'Update() no valida el verbo HTTP POST');
        $this->addCase($results, 'update_donde', str_contains($codigo, "'where'     => 'idExistencia'"),
            'Update() no delimita la escritura por idExistencia');
        $this->addCase($results, 'update_requeridos', str_contains($codigo, "'required'  => 'idExistencia,idFacturacion,Item,ValorTotal'"),
            'Update() no declara los obligatorios');
        $this->addCase($results, 'update_base', str_contains($codigo, '$this->Base_update('),
            'Update() no escribe con Base_update()');

        /*====================================*/
        // Delete: verbo, ID cifrado, lectura previa del documento y borrado
        /*====================================*/
        $this->addCase($results, 'delete_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'DELETE'"),
            'Delete() no valida el verbo HTTP DELETE');
        $this->addCase($results, 'delete_id_cifrado', str_contains($codigo, "encryptDecrypt('decrypt', \$dataDelete['idExistencia'])"),
            'Delete() no descifra idExistencia');
        $this->addCase($results, 'delete_id_valido', str_contains($codigo, "isValidDecrypted(\$ExistenciaID, 'id')"),
            'Delete() no valida el ID descifrado');
        $this->addCase($results, 'delete_obtiene_documento', str_contains($codigo, "\$rowFacturacion['data']['idFacturacion']"),
            'Delete() no recupera el documento antes de borrar para recalcular montos');
        $this->addCase($results, 'delete_base', str_contains($codigo, '$this->Base_delete('),
            'Delete() no borra con Base_delete()');
        $this->addCase($results, 'delete_sin_archivos', str_contains($codigo, "'files'       => ''"),
            'Delete() no declara que el item no maneja archivos');

        /*====================================*/
        // Transaccion y recalculo de montos (Insert, Update y Delete)
        /*====================================*/
        $this->addCase($results, 'transaccion_begin', substr_count($codigo, 'Base_transactionBegin()') === 3,
            'Insert/Update/Delete deben abrir transaccion');
        $this->addCase($results, 'transaccion_commit', substr_count($codigo, 'Base_transactionCommit()') === 3,
            'Insert/Update/Delete deben confirmar la transaccion');
        $this->addCase($results, 'transaccion_rollback', substr_count($codigo, 'Base_transactionRollback()') === 3,
            'quedan 3 rollback C intactos; el resto de fallos usa failOperation');
        $this->addCase($results, 'recalculo_montos', substr_count($codigo, '->updateFact(1,') === 3,
            'Insert/Update/Delete deben recalcular montos con updateFact(1, ...)');
        $this->addCase($results, 'recalculo_misma_conexion', substr_count($codigo, '$this->getDBConn())') === 3,
            'updateFact() debe compartir la conexion de la transaccion');
        $this->addCase($results, 'recalculo_revisa_codigo', substr_count($codigo, "\$ResponseGestionDoc['code'] != 200") === 3,
            'se debe revertir si updateFact() no responde 200');

        /*====================================*/
        // Respuestas al cliente
        /*====================================*/
        $this->addCase($results, 'respuesta_exito', substr_count($codigo, 'Response::success(') === 3,
            'se esperaban 3 Response::success() (Insert, Update y Delete)');
        $this->addCase($results, 'respuesta_405', substr_count($codigo, "Response::error('Error en el Request Method', 405)") === 2,
            'Update() y Delete() deben responder 405 ante un verbo incorrecto');
        $this->addCase($results, 'respuesta_400', substr_count($codigo, "Response::error('Registro inválido', 400)") >= 4,
            'los metodos con ID cifrado deben responder 400 ante un ID invalido');
        $this->addCase($results, 'respuesta_500', substr_count($codigo, 'failOperation(') === 4,
            'los fallos de base de datos deben responder 500 controlado');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos `dataCheck()` del controlador, el cifrado de IDs y la
     * validacion de IDs descifrados. Confirma ademas que `idFacturacion` se valida como
     * entero antes de llegar a `updateFact()`.
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

            $Post      = ['idFacturacion' => 77, 'Item' => 'Item de prueba', 'Number' => 2, 'ValorTotal' => 20000];
            $DataCheck = $reflector->invoke($Controller, $Post);

            $this->addCase($results, 'datacheck_es_arreglo', is_array($DataCheck), 'dataCheck() no devolvio un arreglo');
            $this->addCase($results, 'datacheck_privado', $reflector->isPrivate(), 'dataCheck() debe ser privado');
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post,
                'dataCheck() no conserva el POST recibido');
            $this->addCase($results, 'datacheck_id_entero', str_contains((string)($DataCheck['ValidarEntero'] ?? ''), 'idFacturacion'),
                'dataCheck() no valida idFacturacion como entero (se usa luego en updateFact)');
            $this->addCase($results, 'datacheck_numericos',
                str_contains((string)($DataCheck['ValidarNumero'] ?? ''), 'Number')
                && str_contains((string)($DataCheck['ValidarNumero'] ?? ''), 'ValorTotal'),
                'dataCheck() no valida Number y ValorTotal como numeros');
            $this->addCase($results, 'datacheck_item_largo',
                str_contains((string)($DataCheck['ValidarLargoMinimo'] ?? ''), 'Item')
                && (int)($DataCheck['ValidarLargoMinimoN'] ?? 0) === 3
                && (int)($DataCheck['ValidarLargoMaximoPorCampo']['Item'] ?? 0) === 255,
                'dataCheck() no limita Item a 3..255 caracteres');
            $this->addCase($results, 'datacheck_item_censura',
                str_contains((string)($DataCheck['ValidarPalabrasCensuradas'] ?? ''), 'Item'),
                'dataCheck() no filtra palabras censuradas en Item');
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_es_arreglo', false,
                'no se pudo ejecutar dataCheck() ('.$e->getMessage().')');
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
     * Revisa estaticamente las vistas de items y el controlador: rutas relativas,
     * rutas absolutas, acceso a sesion, SQL embebido, cifrado de IDs y consultas preparadas.
     *
     * @return array Resultado por caso
     */
    public function seguridadVistasTest(){

        $results = [];

        /*====================================*/
        // Vistas involucradas
        /*====================================*/
        $arrVistas = glob($this->rutaModulo('../views/'.self::VISTA_BASE.'-*.php')) ?: [];
        $this->addCase($results, 'hay_vistas', count($arrVistas) === 3,
            'se esperaban 3 vistas de items y se encontraron '.count($arrVistas));

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
        // Escape y cifrado en la tabla
        /*====================================*/
        $tabla = $this->codigoFuente($this->rutaModulo('../views/'.self::VISTA_BASE.'-UpdateList.php'));
        $this->addCase($results, 'tabla_escape_etiqueta', str_contains($tabla, 'addslashes('),
            'UpdateList no escapa la etiqueta que viaja al JavaScript');
        $this->addCase($results, 'tabla_id_cifrado', str_contains($tabla, "encryptDecrypt('encrypt', \$crud['idExistencia'])"),
            'UpdateList no cifra idExistencia en las acciones');

        /*====================================*/
        // Controlador: SQL preparado y sin concatenacion de entrada
        /*====================================*/
        $controlador = $this->fuenteControlador();
        $this->addCase($results, 'controlador_sin_concat_sql', !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $controlador),
            'el controlador concatena superglobales en una cadena SQL');
        $this->addCase($results, 'controlador_parametros_preparados', substr_count($controlador, "'params'") === 5,
            'las 5 consultas con filtro (New 1, UpdateList 2, GetID 1 y lectura previa de Delete 1) deben usar parametros preparados');
        $this->addCase($results, 'controlador_sin_get', !str_contains($controlador, '$_GET') && !str_contains($controlador, '$_REQUEST'),
            'el controlador lee $_GET/$_REQUEST; solo debe usar $_POST y parametros de ruta cifrados');

        /*====================================*/
        // El recalculo de montos recibe un ID validado por dataCheck (entero) antes
        // de llegar a updateFact(): el check ocurre dentro de Base_insert/Base_update
        // y el propio updateFact() fuerza el tipo entero
        /*====================================*/
        $padre = $this->codigoFuente($this->rutaModulo('../controller/gestionDocumentos.php'));
        $this->addCase($results, 'recalculo_id_forzado_entero', str_contains($padre, '$FacturacionID = (int)$FacturacionID;'),
            'updateFact() no fuerza el ID a entero');

        /************************************/
        // Retorno los datos
        return $results;

    }

}
