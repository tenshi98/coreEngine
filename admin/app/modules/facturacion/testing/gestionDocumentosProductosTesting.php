<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class gestionDocumentosProductosTesting
 *
 * Pruebas del modulo `facturacion` - controlador `gestionDocumentosProductos` (productos de un documento mercantil).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. Los textos de los casos
 *    correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Alcance: pruebas NO destructivas. `Insert()`, `Update()` y `Delete()` delegan en `InsertProds()`,
 * `UpdateProds()` y `DeleteProds()`, que abren transaccion, escriben en `facturacion_listado_productos`,
 * recalculan el documento (`gestionDocumentos::updateFact()`) y, con la marca `gestionDocumentosUsoBodega`,
 * mueven stock en bodegas (`bodegasMovimientoProductos::insertMov/updateMov/deleteMov`). NO se ejecutan: se
 * verifican por reflexion y lectura del fuente. Solo se ejecuta logica pura (`tsrxName`, `dataCheck`, cifrado
 * de IDs y la rama de `DeleteProds()` que rechaza un ID invalido antes de tocar la base).
 *
 * El controlador reutiliza permisos y vistas de `gestionDocumentos` ($this->controllerName) y atiende dos
 * tipos con wrappers: tipo 1 = compras (ingreso a bodega) y tipo 2 = ventas (egreso de bodega).
 *
 * @see skills/testing/SKILL.md
 */
class gestionDocumentosProductosTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR      = 'gestionDocumentosProductos';
    private const VISTA_BASE       = 'gestionDocumentos-Resumen-Productos';
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
            'arrBodegas'       => [['ID' => 1, 'Nombre' => 'Bodega Prueba']],
            'arrProductos'     => [[
                'idExistencia'     => 55,
                'TipoMovimiento'   => 'Egreso',
                'Bodega'           => 'Bodega Prueba',
                'ProductoNombre'   => 'Producto Prueba',
                'ProductoCantidad' => 2,
                'UnidadMedida'     => 'un',
                'ProductoValor'    => 50000,
            ]],
            'rowData'          => [
                'idExistencia'    => 55,
                'idFacturacion'   => 41,
                'idEstadoPago'    => $estadoPago,
                'idEstadoIngreso' => $idTipo,
                'idBodegas'       => 1,
                'idProducto'      => 1,
                'TipoMovimiento'  => 'Egreso',
                'Bodega'          => 'Bodega Prueba',
                'ProductoNombre'  => 'Producto Prueba',
                'Number'          => 2,
                'ValorTotal'      => 50000,
            ],
        ];
    }

    /*******************************************************************/
    // Datos del formulario de alta: arrProductos es el catalogo (ID, Nombre)
    /*******************************************************************/
    private function datosFormulario(string $ruta, int $idTipo): array{
        $data = $this->datosSinteticos(2, $ruta, $idTipo);
        $data['arrProductos'] = [['ID' => 1, 'Nombre' => 'Producto Prueba'], ['ID' => 2, 'Nombre' => 'Otro Producto']];
        return $data;
    }

    /*******************************************************************/
    // Instancia el controlador SIN constructor (logica pura, sin base de datos)
    /*******************************************************************/
    private function instanciaSinBD(){
        $controlador = (new ReflectionClass(self::CONTROLADOR))->newInstanceWithoutConstructor();
        $propiedad   = new ReflectionProperty(self::CONTROLADOR, 'Codification');
        $propiedad->setAccessible(true);
        $propiedad->setValue($controlador, new FunctionsSecurityCodification());
        return $controlador;
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica los archivos del controlador `gestionDocumentosProductos` y la convencion
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
            'testing'           => $this->rutaModulo('gestionDocumentosProductosTesting.php'),
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
        $this->addCase($results, 'clase_testing_cargada', class_exists('gestionDocumentosProductosTesting'),
            'la clase gestionDocumentosProductosTesting no esta disponible');
        $padre = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padre === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padre.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('gestionDocumentosProductosTesting') === 'ControllerBase',
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
     * base `New`, `UpdateList` y `GetID`, el permiso segun el tipo y las consultas de lectura
     * (bodegas visibles segun los permisos del usuario, productos activos, detalle con bodega
     * y tipo de movimiento).
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
            $this->addCase($results, 'vista_'.$metodo, str_contains($codigo, "\$this->controllerName.'-Resumen-Productos-".$sufijo.".php'"),
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
        $this->addCase($results, 'nivel_por_tipo', substr_count($codigo, '$tsrxName = $this->tsrxName($idTipo);') === 3
            && substr_count($codigo, 'getArrLevel($f3, $tsrxName)') === 3,
            'las vistas deben resolver el permiso segun el tipo con tsrxName($idTipo)');

        /*====================================*/
        // Consultas de lectura
        /*====================================*/
        $this->addCase($results, 'new_documento_existe',
            str_contains($codigo, "'table'   => 'facturacion_listado'") && str_contains($codigo, "'where'   => 'idFacturacion = ?'"),
            'New() no verifica que el documento exista');
        $this->addCase($results, 'new_bodegas_activas', str_contains($codigo, "'where'   => \$X_where") && str_contains($codigo, "'bodegas_listado.idEstado = ?'"),
            'New() no lista las bodegas activas');
        $this->addCase($results, 'new_bodegas_por_permiso_usuario',
            str_contains($codigo, "\$arrUserData[\"usuariosPermisosBodegas\"]==2 && \$arrUserData['UserType'] != 1")
            && str_contains($codigo, 'bodegas_listado_permisos_usuarios.idUsuario = ?') && str_contains($codigo, "[1, \$arrUserData['UserID']]"),
            'New() debe limitar las bodegas a las del usuario cuando usuariosPermisosBodegas esta activo y no es administrador');
        $this->addCase($results, 'new_productos_activos',
            str_contains($codigo, "'table'   => 'productos_listado'") && str_contains($codigo, "'params'  => [1]"),
            'New() no lista los productos activos');
        $this->addCase($results, 'updatelist_estado_pago', str_contains($codigo, "'data'    => 'idEstadoPago'"),
            'UpdateList() no consulta el estado de pago que habilita las acciones');
        $this->addCase($results, 'getid_detalle_con_bodega',
            str_contains($codigo, 'LEFT JOIN core_estados_ingreso  ON core_estados_ingreso.idEstadoIngreso  = facturacion_listado_productos.idEstadoIngreso')
            && str_contains($codigo, 'LEFT JOIN bodegas_listado       ON bodegas_listado.idBodegas             = facturacion_listado_productos.idBodegas')
            && str_contains($codigo, "'where'   => 'facturacion_listado_productos.idExistencia = ?'"),
            'GetID() no une el tipo de movimiento, la bodega y el producto del detalle');
        $this->addCase($results, 'consultas_limite', substr_count($codigo, 'ConfigAPP::APP["N_MaxItems"]') === 3,
            'se esperaban 3 consultas de lista acotadas por N_MaxItems');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                         CONTROLADOR: ESCRITURA (CRUD)                      */
    /******************************************************************************/
    /**
     * Verifica `Insert`, `Update` y `Delete` y su logica transaccional (`InsertProds`,
     * `UpdateProds`, `DeleteProds`) SIN ejecutarlos: verbos, validaciones, transaccion
     * completa con rollback por paso, recalculo del documento y movimiento de bodega solo
     * cuando la marca `gestionDocumentosUsoBodega` esta activa.
     *
     * @return array Resultado por caso
     */
    public function controladorEscrituraTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Firma de los metodos HTTP y de la logica de negocio
        /*====================================*/
        foreach (['Insert', 'Update', 'Delete'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 1, 1);
        }
        foreach (['InsertProds', 'UpdateProds', 'DeleteProds'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 2, 2);
            $reflector = new ReflectionMethod(self::CONTROLADOR, $metodo);
            $this->addCase($results, 'retorno_array_'.$metodo, $reflector->hasReturnType() && (string)$reflector->getReturnType() === 'array',
                $metodo.'() debe declarar retorno array');
        }

        /*====================================*/
        // Delegacion y verbos
        /*====================================*/
        $this->addCase($results, 'insert_delega', str_contains($codigo, '$this->InsertProds($f3, $_POST)'),
            'Insert() no delega en InsertProds()');
        $this->addCase($results, 'update_delega', str_contains($codigo, '$this->UpdateProds($f3, $_POST)'),
            'Update() no delega en UpdateProds()');
        $this->addCase($results, 'delete_delega', str_contains($codigo, '$this->DeleteProds($f3, $dataDelete)'),
            'Delete() no delega en DeleteProds()');
        $this->addCase($results, 'update_verbo_post', str_contains($codigo, "REQUEST_METHOD'] !== 'POST'"),
            'Update() no valida el verbo HTTP POST');
        $this->addCase($results, 'delete_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'DELETE'"),
            'Delete() no valida el verbo HTTP DELETE');
        $this->addCase($results, 'respuesta_http_propaga', substr_count($codigo, "Response::error(\$Response['message'], \$Response['code'], \$Response['error'] ?? '')") === 3,
            'Insert, Update y Delete deben propagar el codigo y mensaje de la logica de negocio');
        $this->addCase($results, 'respuesta_exito', substr_count($codigo, "Response::success(\$Response['data']['data'])") === 3,
            'se esperaban 3 Response::success() (Insert, Update y Delete)');
        $this->addCase($results, 'respuesta_405', substr_count($codigo, "Response::error('Error en el Request Method', 405)") === 2,
            'Update() y Delete() deben responder 405 ante un verbo incorrecto');

        /*====================================*/
        // Tabla, chequeo de datos y campos
        /*====================================*/
        $this->addCase($results, 'tabla_productos', preg_match_all("/'table'\s*=>\s*'facturacion_listado_productos'/", $codigo) === 6,
            'se esperaban 6 usos de facturacion_listado_productos');
        $this->addCase($results, 'datacheck_en_escritura', substr_count($codigo, '$this->dataCheck($PostData)') === 2,
            'InsertProds() y UpdateProds() deben validar con dataCheck($PostData)');
        $this->addCase($results, 'insert_campos',
            str_contains($codigo, "'data'      => 'idFacturacion,idEstadoIngreso,idBodegas,idProducto,Number,ValorTotal'")
            && str_contains($codigo, "'required'  => 'idFacturacion,idEstadoIngreso,idBodegas,idProducto,ValorTotal'"),
            'InsertProds() no declara los campos y obligatorios del producto');
        $this->addCase($results, 'update_campos',
            str_contains($codigo, "'data'      => 'idExistencia,idFacturacion,idEstadoIngreso,idBodegas,idProducto,Number,ValorTotal'")
            && str_contains($codigo, "'required'  => 'idExistencia,idFacturacion,idEstadoIngreso,idBodegas,idProducto,ValorTotal'")
            && str_contains($codigo, "'where'     => 'idExistencia'"),
            'UpdateProds() no declara los campos, obligatorios y la delimitacion por idExistencia');

        /*====================================*/
        // Delete: ID cifrado, lectura previa, borrado y recalculo
        /*====================================*/
        $this->addCase($results, 'delete_id_cifrado', str_contains($codigo, "encryptDecrypt('decrypt', \$PostData['idExistencia'])"),
            'DeleteProds() no descifra idExistencia');
        $this->addCase($results, 'delete_id_invalido_400', str_contains($codigo, "isValidDecrypted(\$ExistenciaID, 'id')") && substr_count($codigo, "return ['code' => 400, 'message' => 'Registro inválido']") === 2,
            'DeleteProds() debe rechazar un ID invalido con code 400 (y el movimiento de bodega tambien)');
        $this->addCase($results, 'delete_lectura_previa', str_contains($codigo, "'data'    => 'idFacturacion, idProducto'") && str_contains($codigo, "'where'   => 'idExistencia = ?'"),
            'DeleteProds() no lee el producto antes de borrar para recalcular el documento y la bodega');
        $this->addCase($results, 'delete_donde', str_contains($codigo, "'where'       => 'idExistencia'"),
            'DeleteProds() no delimita el borrado por idExistencia');
        $this->addCase($results, 'delete_sin_archivos', str_contains($codigo, "'files'       => ''"),
            'DeleteProds() no declara que el producto no maneja archivos');

        /*====================================*/
        // Transaccion y recalculo del documento
        /*====================================*/
        $this->addCase($results, 'transaccion_begin', substr_count($codigo, 'Base_transactionBegin(') === 3,
            'se esperaban 3 aperturas de transaccion (Insert, Update y Delete)');
        $this->addCase($results, 'transaccion_commit', substr_count($codigo, 'Base_transactionCommit(') === 3,
            'se esperaban 3 confirmaciones de transaccion');
        $this->addCase($results, 'transaccion_rollback', substr_count($codigo, 'Base_transactionRollback(') === 14,
            'se esperaban 14 rollback (uno por cada paso que puede fallar)');
        // Con bodega activa, el movimiento de bodega se comprueba antes de usarlo (Insert, Update y Delete)
        $this->addCase($results, 'movimiento_bodega_comprobado',
            substr_count($codigo, "if (\$rowMovimiento['status'] !== true || empty(\$rowMovimiento['data'])) {") === 3
            && substr_count($codigo, "'message' => 'No se encontró el movimiento de bodega del documento'") === 3
            && preg_match_all("/Base_GetByID\(\\\$xParams\);\s*\/\*+\/\s*\/\/ Se verifica que exista el movimiento de bodega del documento/", $codigo) === 3,
            'InsertProds, UpdateProds y DeleteProds deben comprobar el movimiento de bodega (404 y rollback) justo tras leerlo');
        $this->addCase($results, 'recalculo_documento', substr_count($codigo, '->updateFact(2,') === 3,
            'Insert/Update/Delete deben recalcular el documento con updateFact(2, ...)');
        $this->addCase($results, 'recalculo_misma_conexion', substr_count($codigo, '$this->getDBConn())') >= 6,
            'updateFact() y los movimientos de bodega deben compartir la conexion de la transaccion');
        $this->addCase($results, 'recalculo_revisa_codigo', substr_count($codigo, "\$ResponseGestionDoc['code'] != 200") === 3,
            'se debe revertir si updateFact() no responde 200');

        /*====================================*/
        // Movimiento de bodega: solo con la marca gestionDocumentosUsoBodega
        /*====================================*/
        $this->addCase($results, 'bodega_solo_con_marca', substr_count($codigo, '$arrUserData["gestionDocumentosUsoBodega"]==2') === 3,
            'los movimientos de bodega deben ejecutarse solo con gestionDocumentosUsoBodega activa en Insert, Update y Delete');
        $this->addCase($results, 'bodega_movimientos',
            str_contains($codigo, '->insertMov($PostMovProd, $this->getDBConn())') && str_contains($codigo, '->updateMov($PostMovProd, $this->getDBConn())')
            && str_contains($codigo, '->deleteMov($PostMovProd, $this->getDBConn())'),
            'Insert, Update y Delete deben mover stock con insertMov, updateMov y deleteMov sobre la conexion de la transaccion');
        $this->addCase($results, 'bodega_revierte_si_falla', substr_count($codigo, "\$ResponseBodMovProd['code'] != 200") === 3,
            'se debe revertir si el movimiento de bodega no responde 200');
        $this->addCase($results, 'bodega_segun_tipo',
            str_contains($codigo, "case 1: \$PostMovProd['idBodegas'] = \$rowMovimiento['data']['idBodegasIngreso']; break;")
            && str_contains($codigo, "case 2: \$PostMovProd['idBodegas'] = \$rowMovimiento['data']['idBodegasEgreso']; break;")
            && !str_contains($codigo, "\$PostMovProd['idBodega'] ="),
            'InsertProds() debe usar la bodega de ingreso en compras (1) y la de egreso en ventas (2), con la clave idBodegas que lee insertMov()');
        $this->addCase($results, 'bodega_update_conserva_cantidad_anterior',
            str_contains($codigo, "\$PostMovProd['NumberOld']       = \$rowMovimiento['data']['Number']"),
            'UpdateProds() debe informar la cantidad anterior para ajustar el stock');
        $this->addCase($results, 'bodega_delete_id_cifrado',
            str_contains($codigo, "encryptDecrypt('encrypt', \$rowMovimiento['data']['idExistencia'])"),
            'DeleteProds() debe entregar el idExistencia del movimiento cifrado a deleteMov()');

        /*====================================*/
        // Respuestas y IDs cifrados en las vistas
        /*====================================*/
        $this->addCase($results, 'respuesta_400_vistas', substr_count($codigo, "Response::error('Registro inválido', 400)") === 3,
            'New, UpdateList y GetID deben responder 400 ante un ID invalido');
        $this->addCase($results, 'id_descifrado_vistas', substr_count($codigo, "encryptDecrypt('decrypt', \$params['id'])") === 3,
            'New, UpdateList y GetID deben descifrar $params[\'id\']');
        $this->addCase($results, 'id_valido_total', substr_count($codigo, 'isValidDecrypted(') === 5,
            'se esperaban 5 validaciones isValidDecrypted() (3 vistas, Delete y el movimiento de bodega)');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                       CONTROLADOR: RUTAS E INSTALADOR                      */
    /******************************************************************************/
    /**
     * Verifica que las 12 rutas de `gestionDocumentosProductos` (6 de compras y 6 de ventas)
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
            $esperadas[$nombre.'_nuevo']     = ['1 '.$base.'/productos/new/@id',        'gestionDocumentosProductos->New_'.$tipo,        2];
            $esperadas[$nombre.'_refrescar'] = ['1 '.$base.'/productos/updateList/@id', 'gestionDocumentosProductos->UpdateList_'.$tipo, 2];
            $esperadas[$nombre.'_getid']     = ['1 '.$base.'/productos/getID/@id',      'gestionDocumentosProductos->GetID_'.$tipo,      2];
            $esperadas[$nombre.'_crear']     = ['2 '.$base.'/productos',                'gestionDocumentosProductos->Insert',            2];
            $esperadas[$nombre.'_editar']    = ['2 '.$base.'/productos/update',         'gestionDocumentosProductos->Update',            2];
            $esperadas[$nombre.'_borrar']    = ['3 '.$base.'/productos',                'gestionDocumentosProductos->Delete',            2];
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

        $propias = array_filter($rutas, static function ($r) { return $r[2] === 'gestionDocumentosProductos'; });
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
        $this->addCase($results, 'instalador_desinstala_subrecurso', str_contains($instalador, "\$RutaController .= ',\"gestionDocumentosProductos\"'"),
            'RutaController() no lista gestionDocumentosProductos y dejaria rutas huerfanas al desinstalar');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                PERMISOS                                    */
    /******************************************************************************/
    /**
     * Verifica la sesion, el nivel de acceso de Compras y Ventas (permisos heredados de
     * `gestionDocumentos`) y el registro de las rutas de productos en el router de F3.
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
            $esquema[$nombre.'_insert_router'] = ['/'.$ruta.'/productos',        'POST',   self::CONTROLADOR.'->Insert'];
            $esquema[$nombre.'_update_router'] = ['/'.$ruta.'/productos/update', 'POST',   self::CONTROLADOR.'->Update'];
            $esquema[$nombre.'_delete_router'] = ['/'.$ruta.'/productos',        'DELETE', self::CONTROLADOR.'->Delete'];
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
     * Renderiza `UpdateList`, `formNew` y `formEdit` de productos para compras y ventas: las
     * acciones solo existen con el documento pendiente (estado de pago 1), el ID viaja cifrado,
     * el alta envia el estado de ingreso del tipo (1 compras, 2 ventas) y los formularios
     * publican en la ruta del tipo con su verbo.
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
        // Tabla de productos: documento pendiente (estado de pago 1) frente a otros estados
        /*====================================*/
        $rutaTabla = $this->rutaModulo('../views/'.self::VISTA_BASE.'-UpdateList.php');
        foreach (self::NIVELES as $nivel) {
            $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos($nivel), $BASE, $f3);
            $this->addCase($results, 'tabla_render_nivel'.$nivel, $tabla !== '', 'UpdateList no produjo HTML con nivel '.$nivel);
            $this->addCase($results, 'tabla_acciones_nivel'.$nivel,
                str_contains($tabla, 'tabProdEdit(') && str_contains($tabla, 'tabProdDel('),
                'con el documento pendiente la tabla no ofrece editar y borrar con nivel '.$nivel);
        }
        $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos(2, self::RUTA_VENTAS, 2, 1), $BASE, $f3);
        $this->addCase($results, 'tabla_datos',
            str_contains($tabla, 'Egreso') && str_contains($tabla, 'Bodega Prueba') && str_contains($tabla, 'Producto Prueba'),
            'la tabla no pinta el tipo de movimiento, la bodega y el producto');
        $this->addCase($results, 'tabla_cantidad_con_unidad', (bool)preg_match('/2[.,]00\s+un\b/', $tabla),
            'la tabla no muestra la cantidad con su unidad de medida');
        $this->addCase($results, 'tabla_monto_formateado', str_contains($tabla, '50.000'),
            'la tabla no formatea el valor con Valores()');
        $this->addCase($results, 'tabla_columnas',
            str_contains($tabla, '>Tipo Movimiento<') && str_contains($tabla, '>Bodega<') && str_contains($tabla, '>Producto<')
            && str_contains($tabla, '>Cantidad<') && str_contains($tabla, '>Valor<') && str_contains($tabla, '>Acciones<'),
            'la tabla no declara las columnas Tipo Movimiento, Bodega, Producto, Cantidad, Valor y Acciones');
        $this->addCase($results, 'tabla_id_cifrado',
            !str_contains($tabla, "tabProdEdit('55')") && !str_contains($tabla, "tabProdDel( '55'"),
            'la tabla expone idExistencia sin cifrar');

        foreach ([2 => 'pagado', 3 => 'anulado'] as $estado => $nombre) {
            $noPendiente = $this->renderizarVista($rutaTabla, $this->datosSinteticos(4, self::RUTA_VENTAS, 2, $estado), $BASE, $f3);
            $this->addCase($results, 'tabla_'.$nombre.'_sin_acciones',
                $noPendiente !== '' && str_contains($noPendiente, 'Producto Prueba') && !str_contains($noPendiente, 'tabProdEdit(')
                && !str_contains($noPendiente, 'tabProdDel(') && !str_contains($noPendiente, '>Acciones<'),
                'con el estado de pago '.$estado.' la tabla ofrece editar o borrar productos');
        }

        $vacio = $this->datosSinteticos(2);
        $vacio['arrProductos'] = [];
        $tablaVacia = $this->renderizarVista($rutaTabla, $vacio, $BASE, $f3);
        $this->addCase($results, 'tabla_vacia', $tablaVacia !== '' && str_contains($tablaVacia, '<tbody>') && !str_contains($tablaVacia, 'tabProdEdit('),
            'la tabla sin datos pinta filas o no se dibuja');

        /*====================================*/
        // Formularios de alta y edicion para compras y ventas
        /*====================================*/
        foreach ([2 => self::RUTA_VENTAS, 1 => self::RUTA_COMPRAS] as $tipo => $ruta) {
            $etiqueta = $tipo === 1 ? 'compras' : 'ventas';
            $html = $this->renderizarVista($this->rutaModulo('../views/'.self::VISTA_BASE.'-formNew.php'),
                $this->datosFormulario($ruta, $tipo), $BASE, $f3);
            $this->addCase($results, 'form_new_render_'.$etiqueta, $html !== '', 'formNew de '.$etiqueta.' no produjo HTML');
            $this->addCase($results, 'form_new_id_'.$etiqueta, str_contains($html, 'id="FormNewProducto"'),
                'formNew de '.$etiqueta.' no usa el id FormNewProducto');
            $this->addCase($results, 'form_new_campos_'.$etiqueta,
                str_contains($html, 'name="idBodegas"') && str_contains($html, 'name="idProducto"') && str_contains($html, 'name="Number"') && str_contains($html, 'name="ValorTotal"'),
                'formNew de '.$etiqueta.' no declara bodega, producto, cantidad y valor');
            $this->addCase($results, 'form_new_catalogos_'.$etiqueta,
                str_contains($html, 'Bodega Prueba') && str_contains($html, 'Otro Producto'),
                'formNew de '.$etiqueta.' no ofrece los catalogos de bodegas y productos');
            $this->addCase($results, 'form_new_ocultos_'.$etiqueta,
                (bool)preg_match('/name="idFacturacion"[^>]*value="41"|value="41"[^>]*name="idFacturacion"/', $html)
                && (bool)preg_match('/name="idEstadoIngreso"[^>]*value="'.$tipo.'"|value="'.$tipo.'"[^>]*name="idEstadoIngreso"/', $html)
                && !str_contains($html, 'name="idExistencia"'),
                'formNew de '.$etiqueta.' debe enviar idFacturacion (41) e idEstadoIngreso ('.$tipo.') y no idExistencia');
            $this->addCase($results, 'form_new_endpoint_'.$etiqueta,
                str_contains($html, $BASE.'/'.$ruta.'/productos\'') && str_contains($html, "let Metodo      = 'POST'"),
                'formNew de '.$etiqueta.' no publica en '.$ruta.'/productos con POST');
            $this->addCase($results, 'form_new_refresca_tabla_'.$etiqueta,
                str_contains($html, '#tabProdDataTable') && str_contains($html, $ruta.'/productos/updateList/') && !str_contains($html, '/productos/updateList/41'),
                'formNew de '.$etiqueta.' no refresca la tabla con el ID cifrado');

            $html = $this->renderizarVista($this->rutaModulo('../views/'.self::VISTA_BASE.'-formEdit.php'),
                $this->datosSinteticos(2, $ruta, $tipo), $BASE, $f3);
            $this->addCase($results, 'form_edit_render_'.$etiqueta, $html !== '', 'formEdit de '.$etiqueta.' no produjo HTML');
            $this->addCase($results, 'form_edit_id_'.$etiqueta, str_contains($html, 'id="FormEditProducto"'),
                'formEdit de '.$etiqueta.' no usa el id FormEditProducto');
            $this->addCase($results, 'form_edit_ocultos_'.$etiqueta,
                str_contains($html, 'name="idExistencia"') && str_contains($html, 'name="idFacturacion"') && str_contains($html, 'name="idEstadoIngreso"')
                && str_contains($html, 'name="idBodegas"') && str_contains($html, 'name="idProducto"'),
                'formEdit de '.$etiqueta.' no envia idExistencia, idFacturacion, idEstadoIngreso, idBodegas e idProducto');
            $this->addCase($results, 'form_edit_informativos_'.$etiqueta,
                str_contains($html, 'name="TipoMovimientoFake"') && str_contains($html, 'name="BodegaFake"') && str_contains($html, 'name="ProductoFake"')
                && str_contains($html, 'Bodega Prueba') && str_contains($html, 'Producto Prueba'),
                'formEdit de '.$etiqueta.' no muestra el movimiento, la bodega y el producto como datos informativos');
            $this->addCase($results, 'form_edit_editables_'.$etiqueta,
                str_contains($html, 'name="Number"') && str_contains($html, 'name="ValorTotal"')
                && !preg_match('/<(select|input)[^>]*name="(idBodegas|idProducto)"[^>]*type="(text|number)"/', $html),
                'formEdit de '.$etiqueta.' solo debe permitir editar cantidad y valor');
            $this->addCase($results, 'form_edit_endpoint_'.$etiqueta,
                str_contains($html, $BASE.'/'.$ruta.'/productos/update') && str_contains($html, "let Metodo      = 'POST'"),
                'formEdit de '.$etiqueta.' no publica en '.$ruta.'/productos/update con POST');
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos `tsrxName()`, `dataCheck()`, la rama de `DeleteProds()` que
     * rechaza un ID invalido antes de tocar la base (con una conexion que nunca se usa), el
     * cifrado de IDs y la validacion de IDs descifrados.
     *
     * @return array Resultado por caso
     */
    public function logicaValidacionTest(){

        $results = [];

        /*====================================*/
        // tsrxName: 1 = Compras, 2 = Ventas y por defecto Ventas
        /*====================================*/
        try {
            $controlador = $this->instanciaSinBD();
            $reflector   = new ReflectionMethod(self::CONTROLADOR, 'tsrxName');
            $reflector->setAccessible(true);
            $this->addCase($results, 'tsrxname_privado', $reflector->isPrivate(), 'tsrxName() debe ser privado');
            $this->addCase($results, 'tsrxname_compras', $reflector->invoke($controlador, 1) === 'gestionDocumentosCompras',
                'tsrxName(1) debe devolver gestionDocumentosCompras');
            $this->addCase($results, 'tsrxname_ventas', $reflector->invoke($controlador, 2) === 'gestionDocumentosVentas',
                'tsrxName(2) debe devolver gestionDocumentosVentas');
            $this->addCase($results, 'tsrxname_defecto_ventas',
                $reflector->invoke($controlador, 0) === 'gestionDocumentosVentas' && $reflector->invoke($controlador, 99) === 'gestionDocumentosVentas',
                'tsrxName() debe devolver gestionDocumentosVentas para tipos no validos');
        } catch (Throwable $e) {
            $this->addCase($results, 'tsrxname_compras', false, 'no se pudo ejecutar tsrxName() ('.$e->getMessage().')');
        }

        /*====================================*/
        // dataCheck: contrato esperado por Base_insert/Base_update
        /*====================================*/
        try {
            $reflector = new ReflectionMethod(self::CONTROLADOR, 'dataCheck');
            $reflector->setAccessible(true);
            $Post      = ['idFacturacion' => 41, 'idEstadoIngreso' => 2, 'idBodegas' => 1, 'idProducto' => 1, 'Number' => 2, 'ValorTotal' => 50000];
            $DataCheck = $reflector->invoke($this->instanciaSinBD(), $Post);
            $this->addCase($results, 'datacheck_es_arreglo', is_array($DataCheck), 'dataCheck() no devolvio un arreglo');
            $this->addCase($results, 'datacheck_privado', $reflector->isPrivate(), 'dataCheck() debe ser privado');
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post,
                'dataCheck() no conserva el POST recibido');
            $this->addCase($results, 'datacheck_claves_contrato',
                isset($DataCheck['emptyData'], $DataCheck['ValidarNumero'], $DataCheck['ValidarEntero'], $DataCheck['ValidarLargoMinimoN'], $DataCheck['ValidarLargoMaximoPorCampo']),
                'dataCheck() no entrega las claves del contrato de CheckData');
            $this->addCase($results, 'datacheck_ids_enteros',
                trim((string)$DataCheck['ValidarEntero']) === 'idFacturacion,idEstadoIngreso,idBodegas,idProducto',
                'dataCheck() no valida los identificadores como enteros');
            $this->addCase($results, 'datacheck_numeros',
                trim((string)$DataCheck['ValidarNumero']) === 'idFacturacion,idEstadoIngreso,idBodegas,idProducto,Number,ValorTotal',
                'dataCheck() no valida identificadores, cantidad y valor como numeros');
            $this->addCase($results, 'datacheck_sin_textos',
                trim((string)$DataCheck['ValidarLargoMinimo']) === '' && trim((string)$DataCheck['ValidarPalabrasCensuradas']) === '' && trim((string)$DataCheck['ValidarFecha']) === '',
                'dataCheck() valida textos o fechas que el producto no tiene');
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_es_arreglo', false, 'no se pudo ejecutar dataCheck() ('.$e->getMessage().')');
        }

        /*====================================*/
        // DeleteProds: un ID cifrado invalido se rechaza con 400 ANTES de tocar la base.
        // La rama devuelve antes de Base_transactionBegin(), por lo que la conexion nunca se usa.
        /*====================================*/
        try {
            $controlador = $this->instanciaSinBD();
            $metodo = new ReflectionMethod(self::CONTROLADOR, 'DeleteProds');
            $f3Falso = new class { public function get($clave){ return ['gestionDocumentosUsoBodega' => 1]; } };
            $resp = null;
            try {
                $resp = $metodo->invoke($controlador, $f3Falso, ['idExistencia' => 'valor-no-cifrado']);
            } catch (Throwable $e) {
                $resp = ['code' => 0, 'message' => get_class($e).': '.$e->getMessage()];
            }
            $this->addCase($results, 'deleteprods_id_invalido_400', is_array($resp) && ($resp['code'] ?? 0) === 400,
                'DeleteProds() con un ID no cifrado debe responder code 400 y respondio '.json_encode($resp));
            $this->addCase($results, 'deleteprods_id_invalido_mensaje', is_array($resp) && ($resp['message'] ?? '') === 'Registro inválido',
                'DeleteProds() con un ID invalido debe informar "Registro inválido"');
        } catch (Throwable $e) {
            $this->addCase($results, 'deleteprods_id_invalido_400', false,
                'no se pudo ejecutar DeleteProds() con un ID invalido ('.get_class($e).': '.$e->getMessage().')');
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
            $this->addCase($results, 'cifrado_ida_y_vuelta', false, 'no se pudo ejecutar el cifrado ('.$e->getMessage().')');
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
     * Revisa estaticamente las vistas de productos y el controlador: rutas relativas, rutas
     * absolutas, acceso a sesion, SQL embebido, cifrado de IDs, consultas preparadas y que
     * ningun dato del cliente se concatene en el SQL.
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
        // Cifrado y formato en la tabla
        /*====================================*/
        $tabla = $this->codigoFuente($this->rutaModulo('../views/'.self::VISTA_BASE.'-UpdateList.php'));
        $this->addCase($results, 'tabla_id_cifrado', str_contains($tabla, "encryptDecrypt('encrypt', \$crud['idExistencia'])"),
            'UpdateList no cifra idExistencia en las acciones');
        $this->addCase($results, 'tabla_cantidades_formateadas', str_contains($tabla, 'Cantidades(') && str_contains($tabla, 'Valores('),
            'UpdateList no formatea cantidades y valores');
        $this->addCase($results, 'tabla_acciones_solo_pendiente', substr_count($tabla, "\$data['rowData']['idEstadoPago']==1") === 2,
            'las acciones solo deben mostrarse con el documento pendiente (cabecera y fila)');

        /*====================================*/
        // Controlador: SQL preparado y sin concatenacion de entrada del cliente
        /*====================================*/
        $controlador = $this->fuenteControlador();
        $this->addCase($results, 'controlador_sin_concat_sql', !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $controlador),
            'el controlador concatena superglobales en una cadena SQL');
        $this->addCase($results, 'controlador_sin_concat_datos', !preg_match('/\'\s*\.\s*\$(PostData|Data|dataDelete|rowMovimiento|rowFacturacion)\[/', $controlador),
            'el controlador concatena datos del cliente o de consultas en una cadena SQL');
        $this->addCase($results, 'controlador_parametros_preparados', preg_match_all("/'params'\s*=>/", $controlador) === 10,
            'las 10 consultas con filtro deben declarar la clave params (consultas preparadas)');
        $this->addCase($results, 'controlador_sin_get', !str_contains($controlador, '$_GET') && !str_contains($controlador, '$_REQUEST'),
            'el controlador lee $_GET/$_REQUEST; solo debe usar $_POST y parametros de ruta cifrados');
        $padre = $this->codigoFuente($this->rutaModulo('../controller/gestionDocumentos.php'));
        $this->addCase($results, 'recalculo_id_forzado_entero', str_contains($padre, '$FacturacionID = (int)$FacturacionID;'),
            'updateFact() no fuerza el ID a entero (recibe el idFacturacion del cliente)');

        /************************************/
        // Retorno los datos
        return $results;

    }

}
