<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class cotizacionListadoProductosTesting
 *
 * Pruebas del modulo `cotizaciones` - controlador `cotizacionListadoProductos` (productos de la cotizacion).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. Los textos de los casos
 *    correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Alcance: pruebas NO destructivas. `Insert()`, `Update()` y `Delete()` abren transaccion,
 * escriben en base de datos y terminan en `Response::*()` (`exit`); se verifican por reflexion
 * y lectura del fuente. Solo se ejecuta logica pura (`dataCheck`, cifrado de IDs).
 *
 * El controlador reutiliza el nombre de permisos `cotizacionListado` ($this->controllerName) y
 * las vistas `cotizacionListado-Resumen-Productos-*`.
 *
 * @see skills/testing/SKILL.md
 */
class cotizacionListadoProductosTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR          = 'cotizacionListadoProductos';
    private const CONTROLADOR_PERMISOS = 'cotizacionListado';
    private const VISTA_BASE           = 'cotizacionListado-Resumen-Productos';
    private const RUTA_WEB             = 'cotizacionListado/ventas/listado';
    private const RUTA_WEB_PRODUCTOS       = 'cotizacionListado/ventas/listado/productos';
    // Niveles de permiso del catalogo (1=ver, 2=editar, 3=crear, 4=borrar)
    private const NIVELES              = [1, 2, 3, 4];

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
    // Contrato de presentacion de un metodo. El controlador compone la
    // vista como $this->controllerName.'-Resumen-Productos-...'
    /*******************************************************************/
    private function contratoPresentacion(array &$results, string $metodo, string $sufijoVista){
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'vista_'.$metodo, str_contains($codigo, "\$this->controllerName.'-Resumen-Productos-".$sufijoVista.".php'"),
            $metodo.'() no instancia la vista '.self::VISTA_BASE.'-'.$sufijoVista);
        $this->addCase($results, 'vista_existe_'.$metodo, is_file($this->rutaModulo('../views/'.self::VISTA_BASE.'-'.$sufijoVista.'.php')),
            'no existe el archivo de la vista '.self::VISTA_BASE.'-'.$sufijoVista);
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
            'UserData'         => [
                'MainPathUrl'          => '/admin/public/upload/',
                'sistemaModalSubtitle' => 1,
                'sistemaModalCloseBTN' => 2,
                'UserType'             => 2,
            ],
            'UserAccess'       => ['LevelAccess' => $nivel, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_FormInputs'   => new UIFormInputs(),
            'Fnc_Codification' => new FunctionsSecurityCodification(),
            'Fnc_DataNumbers'  => new FunctionsDataNumbers(),
            'arrProductos'     => [[
                'idExistencia'     => 55,
                'ProductoNombre'   => 'Producto Prueba',
                'ProductoCantidad' => 2,
                'ProductoValor'    => 20000,
                'UnidadMedida'     => 'un',
            ]],
            'rowData'          => [
                'idExistencia' => 55,
                'idCotizacion' => 77,
                'idProducto'   => 1,
                'Number'       => 2,
                'ValorTotal'   => 20000,
            ],
        ];
    }

    /*******************************************************************/
    // Datos para los formularios: arrProductos es el catalogo (ID, Nombre)
    /*******************************************************************/
    private function datosFormulario(int $nivel): array{
        $data = $this->datosSinteticos($nivel);
        $data['arrProductos'] = [['ID' => 1, 'Nombre' => 'Producto Prueba'], ['ID' => 2, 'Nombre' => 'Otro Producto']];
        return $data;
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica los archivos del controlador `cotizacionListadoProductos` y la convencion
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
            'controlador'      => $this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'),
            'vista_form_new'   => $this->rutaModulo('../views/'.self::VISTA_BASE.'-formNew.php'),
            'vista_form_edit'  => $this->rutaModulo('../views/'.self::VISTA_BASE.'-formEdit.php'),
            'vista_update_list' => $this->rutaModulo('../views/'.self::VISTA_BASE.'-UpdateList.php'),
            'testing'          => $this->rutaModulo('cotizacionListadoProductosTesting.php'),
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
        $this->addCase($results, 'clase_testing_cargada', class_exists('cotizacionListadoProductosTesting'),
            'la clase cotizacionListadoProductosTesting no esta disponible');
        $padre = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padre === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padre.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('cotizacionListadoProductosTesting') === 'ControllerBase',
            'el testing no extiende ControllerBase');

        /*====================================*/
        // Constructor del controlador
        /*====================================*/
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'constructor_conexion', str_contains($codigo, 'Database::getSQLConnection(ConfigDataBase::MySQL_1)'),
            'el constructor no obtiene la conexion MySQL_1');
        $this->addCase($results, 'constructor_padre', str_contains($codigo, 'parent::__construct($DB_conn_1, $queryBuilder, $checkData)'),
            'el constructor no delega en ControllerBase');
        $this->addCase($results, 'constructor_permisos', str_contains($codigo, "\$this->controllerName = '".self::CONTROLADOR_PERMISOS."'"),
            'controllerName debe reutilizar los permisos de '.self::CONTROLADOR_PERMISOS);

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                            CONTROLADOR: VISTAS                             */
    /******************************************************************************/
    /**
     * Verifica el contrato de los metodos que devuelven HTML (`New`, `UpdateList`,
     * `GetID`): firma, vista instanciada, tipo de vista, errores y datos de usuario.
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Firma de cada metodo de vista (F3 pasa $f3 y $params)
        /*====================================*/
        foreach (['New', 'UpdateList', 'GetID'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 2, 2);
        }

        /*====================================*/
        // Contrato de presentacion
        /*====================================*/
        $this->contratoPresentacion($results, 'New', 'formNew');
        $this->contratoPresentacion($results, 'UpdateList', 'UpdateList');
        $this->contratoPresentacion($results, 'GetID', 'formEdit');

        $this->addCase($results, 'tipoview_modal', substr_count($codigo, 'showVista(2,') === 3,
            'las tres vistas deben servirse como modal/parcial con showVista(2,)');
        $this->addCase($results, 'rutavista_resuelta', substr_count($codigo, 'returnRutaVista(__DIR__') === 3,
            'las vistas no resuelven su ruta con returnRutaVista()');
        $this->addCase($results, 'errores_showerror', substr_count($codigo, 'showError(2, $f3, $result)') === 3,
            'las vistas no maneja el fallo de consulta con showError()');
        $this->addCase($results, 'usuario_en_vistas', substr_count($codigo, 'getUserData($f3)') === 3,
            'las vistas no obtienen los datos del usuario');
        $this->addCase($results, 'nivel_en_vistas', substr_count($codigo, 'getArrLevel($f3, $this->controllerName)') === 3,
            'las vistas no resuelven el nivel de acceso');
        $this->addCase($results, 'fallback_mergeresponses', substr_count($codigo, 'mergeResponses(') === 3,
            'las vistas no agrupan respuestas con mergeResponses()');

        /*====================================*/
        // Consultas de lectura
        /*====================================*/
        $this->addCase($results, 'consulta_cotizacion_existe',
            str_contains($codigo, "'table'   => 'cotizacion_listado'") && str_contains($codigo, "'where'   => 'idCotizacion = ?'"),
            'New() no verifica que la cotizacion exista');
        $this->addCase($results, 'consulta_productos_por_cotizacion',
            str_contains($codigo, "'table'   => 'cotizacion_listado_productos'") && str_contains($codigo, "'order'   => 'cotizacion_listado_productos.idExistencia ASC'"),
            'UpdateList() no lista los productos de la cotizacion ordenados');
        $this->addCase($results, 'consulta_catalogo_productos',
            substr_count($codigo, "'table'   => 'productos_listado'") === 2,
            'New() y GetID() deben cargar el catalogo de productos activos');
        $this->addCase($results, 'consulta_unidad_medida', str_contains($codigo, 'core_unidades_medida'),
            'UpdateList() no une la unidad de medida del producto');
        $this->addCase($results, 'consulta_producto_por_existencia', str_contains($codigo, "'where'   => 'idExistencia = ?'"),
            'GetID() no consulta el producto por idExistencia');
        $this->addCase($results, 'consultas_limite', str_contains($codigo, 'ConfigAPP::APP["N_MaxItems"]'),
            'el listado no acota el numero de filas');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                         CONTROLADOR: ESCRITURA (CRUD)                      */
    /******************************************************************************/
    /**
     * Verifica `Insert`, `Update` y `Delete` SIN ejecutarlos: verbo HTTP, chequeo de datos,
     * transaccion completa, recalculo de montos de la cotizacion y ID cifrado en el borrado.
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
        $this->addCase($results, 'tabla_productos', preg_match_all("/'table'\s*=>\s*'cotizacion_listado_productos'/", $codigo) === 6,
            'se esperaban 6 usos de cotizacion_listado_productos (3 lecturas y Insert/Update/Delete)');

        /*====================================*/
        // Insert
        /*====================================*/
        $this->addCase($results, 'insert_campos', str_contains($codigo, "'data'      => 'idCotizacion,idProducto,Number,ValorTotal'"),
            'Insert() no declara los campos idCotizacion,idProducto,Number,ValorTotal');
        $this->addCase($results, 'insert_requeridos', str_contains($codigo, "'required'  => 'idCotizacion,idProducto,ValorTotal'"),
            'Insert() no declara los obligatorios idCotizacion,idProducto,ValorTotal');
        $this->addCase($results, 'insert_base', str_contains($codigo, '$this->Base_insert('),
            'Insert() no escribe con Base_insert()');

        /*====================================*/
        // Update
        /*====================================*/
        $this->addCase($results, 'update_verbo_post', str_contains($codigo, "REQUEST_METHOD'] !== 'POST'"),
            'Update() no valida el verbo HTTP POST');
        $this->addCase($results, 'update_donde', str_contains($codigo, "'where'     => 'idExistencia'"),
            'Update() no delimita la escritura por idExistencia');
        $this->addCase($results, 'update_requeridos', str_contains($codigo, "'required'  => 'idExistencia,idCotizacion,idProducto,ValorTotal'"),
            'Update() no declara los obligatorios');
        $this->addCase($results, 'update_base', str_contains($codigo, '$this->Base_update('),
            'Update() no escribe con Base_update()');

        /*====================================*/
        // Delete: verbo, ID cifrado, lectura previa de la cotizacion y borrado
        /*====================================*/
        $this->addCase($results, 'delete_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'DELETE'"),
            'Delete() no valida el verbo HTTP DELETE');
        $this->addCase($results, 'delete_id_cifrado', str_contains($codigo, "encryptDecrypt('decrypt', \$dataDelete['idExistencia'])"),
            'Delete() no descifra idExistencia');
        $this->addCase($results, 'delete_id_valido', str_contains($codigo, "isValidDecrypted(\$ExistenciaID, 'id')"),
            'Delete() no valida el ID descifrado');
        $this->addCase($results, 'delete_obtiene_cotizacion', str_contains($codigo, "\$rowFacturacion['data']['idCotizacion']"),
            'Delete() no recupera la cotizacion antes de borrar para recalcular montos');
        $this->addCase($results, 'delete_base', str_contains($codigo, '$this->Base_delete('),
            'Delete() no borra con Base_delete()');
        $this->addCase($results, 'delete_sin_archivos', str_contains($codigo, "'files'       => ''"),
            'Delete() no declara que el producto no maneja archivos');

        /*====================================*/
        // Transaccion y recalculo de montos (Insert, Update y Delete)
        /*====================================*/
        $this->addCase($results, 'transaccion_begin', substr_count($codigo, 'Base_transactionBegin()') === 3,
            'Insert/Update/Delete deben abrir transaccion');
        $this->addCase($results, 'transaccion_commit', substr_count($codigo, 'Base_transactionCommit()') === 3,
            'Insert/Update/Delete deben confirmar la transaccion');
        $this->addCase($results, 'transaccion_rollback', substr_count($codigo, 'Base_transactionRollback()') === 3,
            'se esperaban 3 rollback C intactos (Insert, Update, Delete)');
        $this->addCase($results, 'recalculo_montos', substr_count($codigo, '->updateCotizacion(2,') === 3,
            'Insert/Update/Delete deben recalcular montos con updateCotizacion(2, ...)');
        $this->addCase($results, 'recalculo_misma_conexion', substr_count($codigo, '$this->getDBConn())') === 3,
            'updateCotizacion() debe compartir la conexion de la transaccion');
        $this->addCase($results, 'recalculo_revisa_codigo', substr_count($codigo, "\$ResponseCotizacion['code'] != 200") === 3,
            'se debe revertir si updateCotizacion() no responde 200');

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
    /*                       CONTROLADOR: RUTAS E INSTALADOR                      */
    /******************************************************************************/
    /**
     * Verifica que las rutas de `cotizacionListadoProductos` publicadas por el instalador
     * apunten a metodos reales con el verbo y el nivel de permiso esperados.
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
        $esperadas = [
            'nuevo'     => ['1 '.self::RUTA_WEB_PRODUCTOS.'/new/@id',        'cotizacionListadoProductos->New',        2],
            'refrescar' => ['1 '.self::RUTA_WEB_PRODUCTOS.'/updateList/@id', 'cotizacionListadoProductos->UpdateList', 2],
            'getid'     => ['1 '.self::RUTA_WEB_PRODUCTOS.'/getID/@id',      'cotizacionListadoProductos->GetID',      2],
            'crear'     => ['2 '.self::RUTA_WEB_PRODUCTOS,                   'cotizacionListadoProductos->Insert',     2],
            'editar'    => ['2 '.self::RUTA_WEB_PRODUCTOS.'/update',         'cotizacionListadoProductos->Update',     2],
            'borrar'    => ['3 '.self::RUTA_WEB_PRODUCTOS,                   'cotizacionListadoProductos->Delete',     2],
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
        // El instalador publica exactamente 6 rutas para este controlador
        /*====================================*/
        $propias = array_filter($rutas, static function ($r) { return $r[2] === 'cotizacionListadoProductos'; });
        $this->addCase($results, 'rutas_propias_completas', count($propias) === 6,
            'se esperaban 6 rutas de '.self::CONTROLADOR.' y hay '.count($propias));

        /*====================================*/
        // Desinstalacion: el sub-recurso debe estar en RutaController()
        /*====================================*/
        $instalador = $this->codigoFuente($this->rutaModulo('../installer/cotizacionInstaller.php'));
        $this->addCase($results, 'instalador_desinstala_subrecurso', str_contains($instalador, '"cotizacionListadoProductos"'),
            'RutaController() no lista cotizacionListadoProductos y dejaria rutas huerfanas al desinstalar');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                PERMISOS                                    */
    /******************************************************************************/
    /**
     * Verifica la sesion, el nivel de acceso heredado de `cotizacionListado` y el
     * registro de las rutas de productos en el router de F3 (solo si el usuario las tiene).
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
        // Lectura de la sesion desde el controlador (con permisos de cotizacionListado)
        /*====================================*/
        try {
            $this->addCase($results, 'get_user_data_arreglo', is_array($this->getUserData($f3)),
                'getUserData() no devolvio un arreglo');
            $arrLevel = $this->getArrLevel($f3, self::CONTROLADOR_PERMISOS);
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
        $this->addCase($results, 'ruta_de_acceso_coincide', $nivel === null || ($arrLevel['RouteAccess'] ?? '') === self::RUTA_WEB,
            'RouteAccess no coincide con '.self::RUTA_WEB);

        /*====================================*/
        // Rutas de items en SESSION.arrPermisos y en el router (solo si las tiene)
        /*====================================*/
        $arrPermisos = $f3->get('SESSION.arrPermisos');
        $this->addCase($results, 'permisos_de_ruta_presentes', $arrPermisos === null || is_array($arrPermisos),
            'SESSION.arrPermisos no es un arreglo');

        $arrRoutes = $f3->get('ROUTES');
        $this->addCase($results, 'router_disponible', is_array($arrRoutes), 'F3 no expuso la variable ROUTES');

        $esquema = [
            'ruta_insert_router'  => ['/'.self::RUTA_WEB_PRODUCTOS,             'POST',   self::CONTROLADOR.'->Insert'],
            'ruta_update_router'  => ['/'.self::RUTA_WEB_PRODUCTOS.'/update',   'POST',   self::CONTROLADOR.'->Update'],
            'ruta_delete_router'  => ['/'.self::RUTA_WEB_PRODUCTOS,             'DELETE', self::CONTROLADOR.'->Delete'],
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
     * Renderiza `UpdateList`, `formNew` y `formEdit` de items con datos sinteticos y
     * verifica ids de formulario, campos, endpoints con su verbo y el ID cifrado.
     * En esta tabla las acciones no dependen del nivel (la ruta ya exige nivel 2).
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function vistasProductosTest($f3){

        $results = [];
        $BASE    = (string)$f3->get('BASE');
        if (trim($BASE) === '') {
            $BASE = '/admin';
        }

        /*====================================*/
        // Tabla de productos: se prueba con todos los niveles (no cambia el resultado)
        /*====================================*/
        $rutaTabla = $this->rutaModulo('../views/'.self::VISTA_BASE.'-UpdateList.php');
        foreach (self::NIVELES as $nivel) {
            $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos($nivel), $BASE, $f3);
            $this->addCase($results, 'tabla_render_nivel'.$nivel, $tabla !== '',
                'UpdateList no produjo HTML con nivel '.$nivel);
            $this->addCase($results, 'tabla_acciones_nivel'.$nivel,
                str_contains($tabla, 'tabProdEdit(') && str_contains($tabla, 'tabProdDel('),
                'la tabla no ofrece editar y borrar con nivel '.$nivel);
        }
        $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'tabla_datos', str_contains($tabla, 'Producto Prueba'),
            'la tabla no pinta el producto sintetico');
        $this->addCase($results, 'tabla_unidad_medida', (bool)preg_match('/2[.,]00\s+un\b/', $tabla),
            'la tabla no muestra la unidad de medida junto a la cantidad');
        $this->addCase($results, 'tabla_columnas',
            str_contains($tabla, '>Producto<') && str_contains($tabla, '>Cantidad<') && str_contains($tabla, '>Valor<'),
            'la tabla no declara las columnas Producto, Cantidad y Valor');
        $this->addCase($results, 'tabla_id_cifrado',
            !str_contains($tabla, "tabProdEdit('55')") && !str_contains($tabla, "tabProdDel( '55'"),
            'la tabla expone idExistencia sin cifrar');

        /*====================================*/
        // Tabla vacia y escape de comillas hacia JavaScript
        /*====================================*/
        $vacio = $this->datosSinteticos(2);
        $vacio['arrProductos'] = [];
        $tablaVacia = $this->renderizarVista($rutaTabla, $vacio, $BASE, $f3);
        $this->addCase($results, 'tabla_vacia', $tablaVacia !== '' && !str_contains($tablaVacia, 'tabProdEdit('),
            'la tabla sin datos no se dibuja vacia');

        $comillas = $this->datosSinteticos(2);
        $comillas['arrProductos'][0]['ProductoNombre'] = "Producto d'prueba";
        $tablaComillas = $this->renderizarVista($rutaTabla, $comillas, $BASE, $f3);
        $this->addCase($results, 'tabla_escapa_comillas', str_contains($tablaComillas, "Producto d\\'prueba"),
            'la etiqueta del borrado no escapa las comillas simples');

        /*====================================*/
        // Formulario de alta
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::VISTA_BASE.'-formNew.php'),
            $this->datosFormulario(2), $BASE, $f3);
        $this->addCase($results, 'form_new_render', $html !== '', 'formNew no produjo HTML');
        $this->addCase($results, 'form_new_id', str_contains($html, 'id="FormNewProducto"'),
            'el formulario de alta no usa el id FormNewProducto');
        $this->addCase($results, 'form_new_campos',
            str_contains($html, 'name="idProducto"') && str_contains($html, 'name="Number"') && str_contains($html, 'name="ValorTotal"'),
            'el formulario de alta no declara idProducto/Number/ValorTotal');
        $this->addCase($results, 'form_new_catalogo', str_contains($html, 'Otro Producto'),
            'el formulario de alta no ofrece el catalogo de productos');
        $this->addCase($results, 'form_new_oculto_cotizacion', str_contains($html, 'name="idCotizacion"'),
            'el formulario de alta no envia idCotizacion');
        $this->addCase($results, 'form_new_sin_existencia', !str_contains($html, 'name="idExistencia"'),
            'el formulario de alta no debe enviar idExistencia');
        $this->addCase($results, 'form_new_endpoint',
            str_contains($html, $BASE.'/'.self::RUTA_WEB_PRODUCTOS."'") && str_contains($html, "let Metodo      = 'POST'"),
            'el formulario de alta no publica en '.self::RUTA_WEB_PRODUCTOS.' con POST');
        $this->addCase($results, 'form_new_refresca_tabla', str_contains($html, '#tabProdDataTable') && str_contains($html, '/productos/updateList/'),
            'el formulario de alta no refresca la tabla de productos al guardar');
        $this->addCase($results, 'form_new_id_cifrado_refresco', !str_contains($html, '/productos/updateList/77'),
            'el refresco de la tabla expone idCotizacion sin cifrar');

        /*====================================*/
        // Formulario de edicion
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::VISTA_BASE.'-formEdit.php'),
            $this->datosFormulario(2), $BASE, $f3);
        $this->addCase($results, 'form_edit_render', $html !== '', 'formEdit no produjo HTML');
        $this->addCase($results, 'form_edit_id', str_contains($html, 'id="FormEditProducto"'),
            'el formulario de edicion no usa el id FormEditProducto');
        $this->addCase($results, 'form_edit_campos',
            str_contains($html, 'name="idProducto"') && str_contains($html, 'name="Number"') && str_contains($html, 'name="ValorTotal"'),
            'el formulario de edicion no declara idProducto/Number/ValorTotal');
        $this->addCase($results, 'form_edit_catalogo', str_contains($html, 'Otro Producto'),
            'el formulario de edicion no ofrece el catalogo de productos');
        $this->addCase($results, 'form_edit_ocultos',
            str_contains($html, 'name="idExistencia"') && str_contains($html, 'name="idCotizacion"'),
            'el formulario de edicion no envia idExistencia e idCotizacion');
        $this->addCase($results, 'form_edit_precarga', str_contains($html, 'Producto Prueba'),
            'el formulario de edicion no precarga el producto');
        $this->addCase($results, 'form_edit_endpoint',
            str_contains($html, $BASE.'/'.self::RUTA_WEB_PRODUCTOS.'/update') && str_contains($html, "let Metodo      = 'POST'"),
            'el formulario de edicion no publica en /productos/update con POST');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos `dataCheck()` del controlador, el cifrado de IDs y la
     * validacion de IDs descifrados. Confirma ademas que `idCotizacion` se valida como
     * entero antes de llegar a `updateCotizacion()`.
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

            $Post      = ['idCotizacion' => 77, 'idProducto' => 1, 'Number' => 2, 'ValorTotal' => 20000];
            $DataCheck = $reflector->invoke($Controller, $Post);

            $this->addCase($results, 'datacheck_es_arreglo', is_array($DataCheck), 'dataCheck() no devolvio un arreglo');
            $this->addCase($results, 'datacheck_privado', $reflector->isPrivate(), 'dataCheck() debe ser privado');
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post,
                'dataCheck() no conserva el POST recibido');
            $this->addCase($results, 'datacheck_ids_enteros',
                str_contains((string)($DataCheck['ValidarEntero'] ?? ''), 'idCotizacion')
                && str_contains((string)($DataCheck['ValidarEntero'] ?? ''), 'idProducto'),
                'dataCheck() no valida idCotizacion e idProducto como enteros (se usan luego en updateCotizacion)');
            $this->addCase($results, 'datacheck_numericos',
                str_contains((string)($DataCheck['ValidarNumero'] ?? ''), 'Number')
                && str_contains((string)($DataCheck['ValidarNumero'] ?? ''), 'ValorTotal'),
                'dataCheck() no valida Number y ValorTotal como numeros');
            $this->addCase($results, 'datacheck_sin_texto_libre',
                trim((string)($DataCheck['ValidarLargoMinimo'] ?? '')) === '' && trim((string)($DataCheck['ValidarPalabrasCensuradas'] ?? '')) === '',
                'dataCheck() valida texto libre pero el producto se elige de un catalogo');
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
     * Revisa estaticamente las vistas de productos y el controlador: rutas relativas,
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
            'se esperaban 3 vistas de productos y se encontraron '.count($arrVistas));

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
        $this->addCase($results, 'controlador_parametros_preparados', substr_count($controlador, "'params'") === 6,
            'las 6 consultas con filtro (3 vistas, 2 catalogos y lectura previa de Delete) deben usar parametros preparados');
        $this->addCase($results, 'controlador_sin_get', !str_contains($controlador, '$_GET') && !str_contains($controlador, '$_REQUEST'),
            'el controlador lee $_GET/$_REQUEST; solo debe usar $_POST y parametros de ruta cifrados');

        /*====================================*/
        // El recalculo de montos recibe un ID validado por dataCheck (entero) antes
        // de llegar a updateCotizacion(): el check ocurre dentro de Base_insert/Base_update
        // y el propio updateCotizacion() fuerza el tipo entero
        /*====================================*/
        $padre = $this->codigoFuente($this->rutaModulo('../controller/cotizacionListado.php'));
        $this->addCase($results, 'recalculo_id_forzado_entero', str_contains($padre, '$CotizacionID = (int)$CotizacionID;'),
            'updateCotizacion() no fuerza el ID a entero');

        /************************************/
        // Retorno los datos
        return $results;

    }

}
