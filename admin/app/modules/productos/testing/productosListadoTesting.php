<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class productosListadoTesting
 *
 * Pruebas del modulo `productos` - controlador `productosListado` (CRUD de productos).
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
 * fuente. El instalador se lee por reflexion (`newInstanceWithoutConstructor`) sin ejecutar
 * `InstallModule`/`UninstallModule`.
 *
 * Nota de diseno: las plantillas encadenan `require_once` entre si (List incluye formSearch,
 * UpdateList y formNew; View y Resumen incluyen Resumen-Update), de modo que cada render se
 * ordena para no depender del estado de inclusion de un proceso anterior.
 *
 * @see skills/testing/SKILL.md
 */
class productosListadoTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR        = 'productosListado';
    private const RUTA_WEB           = 'administracion/productos/listado';
    private const RUTA_WEB_LISTADO   = 'administracion/productos/listado/listAll';
    private const RUTA_WEB_SEARCH    = 'administracion/productos/listado/search';
    private const NIVELES            = [1, 2, 3, 4];
    /*=========== Controladores del modulo (instalador) ===========*/
    private const CONTROLADORES      = [
        'productosCategorias',
        'productosTipos',
        'productosListado',
        'productosListadoDocumentos',
        'productosListadoObservaciones',
    ];

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
    // Atajos de lectura del controlador y del instalador
    /*******************************************************************/
    private function fuenteControlador(): string{
        return $this->codigoFuente($this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'));
    }

    private function fuenteInstalador(): string{
        return $this->codigoFuente($this->rutaModulo('../installer/productosInstaller.php'));
    }

    /*******************************************************************/
    // Parsea getRouteDefinitions() del instalador a un mapa indexado
    // por verbo + RutaWeb (sin ejecutar InstallModule). El instalador
    // reparte las rutas por tipo de permiso (1..N), se agregan todas.
    /*******************************************************************/
    private function rutasInstalador(): array{
        $rutas = [];
        try {
            $instalador = (new ReflectionClass('productosInstaller'))->newInstanceWithoutConstructor();
            $reflector  = new ReflectionMethod('productosInstaller', 'getRouteDefinitions');
            $reflector->setAccessible(true);
            $defs = [];
            for ($type = 1; $type <= 10; $type++) {
                $lote = (array)$reflector->invoke($instalador, $type);
                if (empty($lote)) {
                    break;
                }
                $defs = array_merge($defs, $lote);
            }
        } catch (Throwable $e) {
            return [];
        }
        $mapa = [1 => 'GET', 2 => 'POST', 3 => 'DELETE', 4 => 'PUT'];
        foreach ((array)$defs as $def) {
            $verbo         = $mapa[(int)($def['idMetodo'] ?? 0)] ?? '';
            $clave         = $verbo.' '.($def['RutaWeb'] ?? '');
            $rutas[$clave] = [
                'handler'    => (string)($def['RutaController'] ?? ''),
                'nivel'      => (int)($def['idLevelLimit'] ?? 0),
                'controller' => (string)($def['Controller'] ?? ''),
            ];
        }
        return $rutas;
    }

    /*******************************************************************/
    // Lista privada de controladores declarada por el instalador
    // (RutaController() es private: se lee por reflexion)
    /*******************************************************************/
    private function listaControladoresInstalador(): array{
        try {
            $instalador = (new ReflectionClass('productosInstaller'))->newInstanceWithoutConstructor();
            $reflector  = new ReflectionMethod('productosInstaller', 'RutaController');
            $reflector->setAccessible(true);
            $cadena = (string)$reflector->invoke($instalador);
        } catch (Throwable $e) {
            return [];
        }
        preg_match_all('/"([^"]+)"/', $cadena, $matches);
        return $matches[1] ?? [];
    }

    /*******************************************************************/
    // Renderiza una vista del modulo con datos sinteticos y devuelve el
    // HTML. El CWD se mueve a admin/public porque las plantillas de los
    // inputs se resuelven con rutas relativas ('../app/templates/...').
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
    // Contrato de presentacion de un metodo (vista, errores y usuario).
    // Las vistas se componen con $this->controllerName, por eso se
    // coteja el sufijo del fichero tal y como aparece en el fuente.
    /*******************************************************************/
    private function contratoPresentacion(array &$results, string $metodo, string $vista, string $tipoVista){
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'vista_'.$metodo, str_contains($codigo, "'".$vista."'"),
            $metodo.'() no instancia la vista '.$vista);
        $this->addCase($results, 'tipoview_'.$metodo, str_contains($codigo, 'showVista('.$tipoVista.','),
            $metodo.'() no usa showVista('.$tipoVista.')');
        $this->addCase($results, 'rutavista_'.$metodo, str_contains($codigo, 'returnRutaVista('),
            $metodo.'() no resuelve la ruta de la vista');
        $this->addCase($results, 'rutaruta_'.$metodo, str_contains($codigo, '$this->controllerName'),
            $metodo.'() no referencia $this->controllerName');
        $this->addCase($results, 'errores_'.$metodo, str_contains($codigo, 'showError('),
            $metodo.'() no maneja la ruta de presentacion con showError()');
        $this->addCase($results, 'usuario_'.$metodo, str_contains($codigo, 'getUserData($f3)'),
            $metodo.'() no obtiene los datos del usuario');
    }

    /*******************************************************************/
    // Datos sinteticos usados por las vistas del modulo
    /*******************************************************************/
    private function datosSinteticos(int $nivel, bool $verDocumentos = true): array{
        return [
            'PageTitle'        => 'Listado Productos',
            'PageDescription'  => 'Listado Productos.',
            'PageAuthor'       => 'Pruebas',
            'PageKeywords'     => 'Pruebas',
            'TableTitle'       => 'Listado de Productos',
            'UserData'         => [
                'MainPathUrl'                   => '/admin/public/upload/',
                'sistemaModalSubtitle'          => 2,
                'sistemaModalCloseBTN'          => 2,
                'UserType'                      => 2,
                'productosListadoVerDocumentos' => $verDocumentos ? 2 : 1,
            ],
            'UserAccess'       => ['LevelAccess' => $nivel, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_FormInputs'   => new UIFormInputs(),
            'Fnc_Codification' => new FunctionsSecurityCodification(),
            'Fnc_DataDate'     => new FunctionsDataDate(),
            'Fnc_DataNumbers'  => new FunctionsDataNumbers(),
            'Fnc_WidgetsCommon'=> new UIWidgetsCommon(),
            'Fnc_WidgetsViews' => new UIWidgetsViews(),
            'Fnc_ServerServer' => new FunctionsServerServer(),
            'arrList'          => [[
                'idProducto'  => 1,
                'Nombre'      => 'Producto Prueba',
                'Estado'      => 'Activo',
                'EstadoColor' => 'success',
                'Tipo'        => 'Insumo',
                'Categoria'   => 'General',
            ]],
            'arrEstado'        => [['ID' => 1, 'Nombre' => 'Activo', 'Color' => 'success']],
            'arrTipo'          => [['ID' => 1, 'Nombre' => 'Insumo']],
            'arrCategoria'     => [['ID' => 1, 'Nombre' => 'General']],
            'arrUnimed'        => [['ID' => 1, 'Nombre' => 'Unidad']],
            'arrDocumentos'    => [[
                'idDocumentos'   => 1,
                'Nombre'         => 'Factura Prueba',
                'NombreArchivo'  => 'factura_prueba.pdf',
                'FVencimiento'   => '2026-12-31',
            ]],
            'arrObservaciones' => [[
                'FechaCreacion' => '2026-01-02',
                'Usuario'       => 'Tester',
                'Observacion'   => 'Observacion de prueba',
            ]],
            'rowData'          => [
                'idProducto'     => 1,
                'idEstado'       => 1,
                'idTipoProducto' => 1,
                'idCategoria'    => 1,
                'idUniMed'       => 1,
                'Nombre'         => 'Producto Prueba',
                'Marca'          => 'Marca Prueba',
                'StockLimite'    => '10.00',
                'ValorIngreso'   => '1000.00',
                'ValorEgreso'    => '800.00',
                'Descripcion'    => 'Descripcion de prueba',
                'Codigo'         => 'SKU-001',
                'Direccion_img'  => '',
                'Estado'         => 'Activo',
                'EstadoColor'    => 'success',
                'Tipo'           => 'Insumo',
                'Categoria'      => 'General',
                'UniMed'         => 'Unidad',
            ],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica los archivos obligatorios del modulo: controlador, vistas,
     * instalador, fichero de pruebas, README, protecciones `.htaccess` y
     * herencia de las clases.
     *
     * @return array Resultado por caso
     */
    public function estructuraModuloTest(){

        $results = [];

        /*====================================*/
        // Controlador
        /*====================================*/
        $rutaControlador = $this->rutaModulo('../controller/'.self::CONTROLADOR.'.php');
        $this->addCase($results, 'controlador_existe', is_file($rutaControlador),
            'no existe controller/'.self::CONTROLADOR.'.php');
        $this->addCase($results, 'controlador_clase', class_exists(self::CONTROLADOR),
            'PHP no declara la clase '.self::CONTROLADOR);
        $this->addCase($results, 'controlador_herencia',
            is_subclass_of(self::CONTROLADOR, 'ControllerBase'),
            self::CONTROLADOR.' no extiende ControllerBase');
        $fuente = $this->fuenteControlador();
        $this->addCase($results, 'controlador_constructor',
            str_contains($fuente, 'Database::getSQLConnection(ConfigDataBase::MySQL_1)')
            && str_contains($fuente, 'parent::__construct($DB_conn_1, $queryBuilder, $checkData)'),
            self::CONTROLADOR.' no replica el constructor estandar');
        $this->addCase($results, 'controlador_nombre_interno',
            str_contains($fuente, "\$this->controllerName = '".self::CONTROLADOR."'"),
            self::CONTROLADOR.' no fija controllerName con su propio nombre');

        /*====================================*/
        // Vistas del controlador
        /*====================================*/
        $arrVistas = [
            'productosListado-List.php',
            'productosListado-UpdateList.php',
            'productosListado-View.php',
            'productosListado-Resumen.php',
            'productosListado-Resumen-Update.php',
            'productosListado-formNew.php',
            'productosListado-formSearch.php',
            'productosListado-Resumen-Documentos-UpdateList.php',
            'productosListado-Resumen-Documentos-View.php',
            'productosListado-Resumen-Documentos-formNew.php',
            'productosListado-Resumen-Documentos-formEdit.php',
            'productosListado-Resumen-Observaciones-UpdateList.php',
            'productosListado-Resumen-Observaciones-View.php',
            'productosListado-Resumen-Observaciones-formNew.php',
            'productosListado-Resumen-Observaciones-formEdit.php',
        ];
        $this->addCase($results, 'hay_vistas',
            count(glob($this->rutaModulo('../views/'.self::CONTROLADOR.'-*.php'))) >= count($arrVistas),
            'hay menos vistas de '.self::CONTROLADOR.' de las esperadas');
        foreach ($arrVistas as $vista) {
            $this->addCase($results, 'vista_'.$vista, is_file($this->rutaModulo('../views/'.$vista)),
                'falta la vista '.$vista);
        }

        /*====================================*/
        // Instalador
        /*====================================*/
        $rutaInstalador = $this->rutaModulo('../installer/productosInstaller.php');
        $this->addCase($results, 'instalador_existe', is_file($rutaInstalador),
            'no existe installer/productosInstaller.php');
        $this->addCase($results, 'instalador_clase', class_exists('productosInstaller'),
            'PHP no declara la clase productosInstaller');
        $this->addCase($results, 'instalador_herencia',
            is_subclass_of('productosInstaller', 'ControllerInstaller'),
            'productosInstaller no extiende ControllerInstaller');
        $instalador = $this->fuenteInstalador();
        foreach (['ListDataModule', 'InstallModule', 'UninstallModule', 'GetCountDataModule', 'getRouteDefinitions', 'listTables', 'optimizeTables'] as $metodo) {
            $this->addCase($results, 'instalador_metodo_'.$metodo, str_contains($instalador, 'function '.$metodo.'('),
                'el instalador no declara '.$metodo.'()');
        }

        /*====================================*/
        // Fichero de pruebas, README y .htaccess
        /*====================================*/
        $rutaTesting = $this->rutaModulo(__CLASS__.'.php');
        $this->addCase($results, 'testing_existe', is_file($rutaTesting),
            'no existe testing/'.__CLASS__.'.php');
        $this->addCase($results, 'testing_clase', class_exists(__CLASS__),
            'PHP no declara la clase '.__CLASS__);
        $this->addCase($results, 'testing_herencia', is_subclass_of(__CLASS__, 'ControllerBase'),
            __CLASS__.' no extiende ControllerBase');
        $this->addCase($results, 'readme_existe', is_file($this->rutaModulo('../README.md')),
            'no existe README.md del modulo');

        foreach (['..', '../controller', '../views', '../installer', '.'] as $carpeta) {
            $ruta = $this->rutaModulo($carpeta.'/.htaccess');
            $this->addCase($results, 'htaccess_'.md5($carpeta), is_file($ruta)
                && str_contains($this->codigoFuente($ruta), 'deny from all'),
                'falta .htaccess con deny from all en '.$carpeta);
        }

        /*====================================*/
        // Soporte del servidor
        /*====================================*/
        $this->addCase($results, 'finfo_disponible', $this->finfoDisponible(),
            'el servidor no dispone de la extension finfo');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              CONTROLADOR                                    */
    /******************************************************************************/
    /**
     * Revisa por reflexion las firmas de los metodos de vista y su
     * contrato de presentacion (showVista, returnRutaVista, showError,
     * getUserData y controllerName).
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];

        /*====================================*/
        // Firmas de los metodos
        /*====================================*/
        $this->firmaMetodo($results, 'listAll', 1, 1);
        $this->firmaMetodo($results, 'UpdateList', 1, 1);
        $this->firmaMetodo($results, 'View', 2, 2);
        $this->firmaMetodo($results, 'Resumen', 2, 2);
        $this->firmaMetodo($results, 'ResumenUpdate', 2, 2);

        /*====================================*/
        // Contrato de presentacion
        /*====================================*/
        $this->contratoPresentacion($results, 'listAll', '-List.php', '1');
        $this->contratoPresentacion($results, 'UpdateList', '-UpdateList.php', '2');
        $this->contratoPresentacion($results, 'View', '-View.php', '2');
        $this->contratoPresentacion($results, 'Resumen', '-Resumen.php', '1');
        $this->contratoPresentacion($results, 'ResumenUpdate', '-Resumen-Update.php', '2');

        /*====================================*/
        // Contratos propios del CRUD
        /*====================================*/
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'filtros_updelist',
            str_contains($codigo, '$WhereData_int') && str_contains($codigo, '$WhereData_string'),
            'UpdateList() no declara los grupos de busqueda exacta y relativa');
        $this->addCase($results, 'filtros_validacion_fechas',
            str_contains($codigo, 'searchValidateDates('),
            'UpdateList() no valida el rango de fechas de busqueda');
        $this->addCase($results, 'permiso_documentos',
            str_contains($codigo, 'productosListadoVerDocumentos'),
            'View() no consulta el permiso productosListadoVerDocumentos');
        $this->addCase($results, 'respuestas_fusionadas',
            str_contains($codigo, 'mergeResponses('),
            'el controlador no fusiona las respuestas con mergeResponses()');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              ESCRITURA                                      */
    /******************************************************************************/
    /**
     * Verifica SIN ejecutarlos los metodos de escritura: verbo HTTP,
     * dataCheck, tabla, unicidad, transaccion, borrado en cascada,
     * manejo de archivos e IDs cifrados (lectura del fuente y reflexion).
     *
     * @return array Resultado por caso
     */
    public function controladorEscrituraTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Firmas de los metodos de escritura
        /*====================================*/
        foreach (['Insert', 'Update', 'Delete', 'delFiles'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 0, 0);
        }
        $this->addCase($results, 'datacheck_privado',
            method_exists(self::CONTROLADOR, 'dataCheck')
            && !(new ReflectionMethod(self::CONTROLADOR, 'dataCheck'))->isPublic(),
            'dataCheck() debe existir y ser privado');

        /*====================================*/
        // Insert: chequeo propio, tabla y respuesta con ID cifrado
        /*====================================*/
        $this->addCase($results, 'insert_datacheck', str_contains($codigo, '$this->dataCheck($_POST)'),
            'Insert() no construye el DataCheck con dataCheck($_POST)');
        $this->addCase($results, 'insert_tabla',
            (bool)preg_match("/'table'\s*=>\s*'productos_listado'/", $codigo),
            'Insert() no apunta a la tabla productos_listado');
        $this->addCase($results, 'insert_obligatorios',
            (bool)preg_match("/'required'\s*=>\s*'idEstado,idTipoProducto,idCategoria,idUniMed,Nombre'/", $codigo),
            'Insert() no declara los campos obligatorios');
        $this->addCase($results, 'insert_unicidad',
            (bool)preg_match("/'unique'\s*=>\s*'Nombre,Codigo'/", $codigo),
            'Insert() no declara la unicidad Nombre,Codigo');
        $this->addCase($results, 'insert_respuesta', str_contains($codigo, 'Response::success('),
            'Insert() no responde con Response::success()');
        $this->addCase($results, 'insert_id_cifrado', str_contains($codigo, "encryptDecrypt('encrypt'"),
            'Insert() no cifra el ID devuelto');

        /*====================================*/
        // Update: verbo POST y manejo de la imagen en base64
        /*====================================*/
        $this->addCase($results, 'update_verbo_post', str_contains($codigo, "REQUEST_METHOD'] !== 'POST'"),
            'Update() no valida el verbo HTTP POST');
        $this->addCase($results, 'update_donde',
            (bool)preg_match("/'where'\s*=>\s*'idProducto'/", $codigo),
            'Update() no delimita la escritura por idProducto');
        $this->addCase($results, 'update_archivos',
            str_contains($codigo, "'files'")
            && str_contains($codigo, 'Direccion_img')
            && str_contains($codigo, "'Base64'        => true"),
            'Update() no declara el manejo de la imagen Direccion_img en base64');

        /*====================================*/
        // Delete: verbo DELETE, transaccion y borrado en cascada
        /*====================================*/
        $this->addCase($results, 'delete_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'DELETE'"),
            'Delete() no valida el verbo HTTP DELETE');
        $this->addCase($results, 'delete_transaccion',
            str_contains($codigo, 'Base_transactionBegin()')
            && str_contains($codigo, 'Base_transactionCommit()')
            && str_contains($codigo, 'failOperation('),
            'Delete() debe abrir transaccion y revertir con failOperation');
        $this->addCase($results, 'delete_cascada_documentos',
            str_contains($codigo, 'productos_listado_documentos'),
            'Delete() no elimina los documentos relacionados');
        $this->addCase($results, 'delete_cascada_observaciones',
            str_contains($codigo, 'productos_listado_observaciones'),
            'Delete() no elimina las observaciones relacionadas');
        $this->addCase($results, 'delete_archivos',
            str_contains($codigo, "'files'       => 'Direccion_img'"),
            'Delete() no limpia la imagen del producto');

        /*====================================*/
        // delFiles: verbo PUT y limpieza del archivo
        /*====================================*/
        $this->addCase($results, 'delfiles_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'PUT'"),
            'delFiles() no valida el verbo HTTP PUT');
        $this->addCase($results, 'delfiles_archivo', str_contains($codigo, 'Base_delFiles('),
            'delFiles() no invoca Base_delFiles()');

        /*====================================*/
        // IDs descifrados antes de consultar
        /*====================================*/
        foreach (['View', 'Resumen', 'ResumenUpdate'] as $metodo) {
            $this->addCase($results, 'id_descifrado_'.$metodo,
                str_contains($codigo, "encryptDecrypt('decrypt', \$params['id'])"),
                $metodo.'() no descifra $params[\'id\']');
        }
        $this->addCase($results, 'id_valido',
            substr_count($codigo, 'isValidDecrypted(') >= 4,
            'los metodos de vista y escritura no validan con isValidDecrypted()');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                               RUTAS                                         */
    /******************************************************************************/
    /**
     * Coteja las rutas publicadas por el instalador con los controladores
     * del modulo: handlers existentes, niveles 1..4, cobertura de los 10
     * endpoints del CRUD y ausencia de controladores huerfanos en la
     * lista de desinstalacion. No instala ni desinstala nada.
     *
     * @return array Resultado por caso
     */
    public function controladorRutasTest(){

        $results = [];

        /*====================================*/
        // Rutas del instalador
        /*====================================*/
        $arrRutas = $this->rutasInstalador();
        $this->addCase($results, 'instalador_hay_rutas', count($arrRutas) > 0,
            'getRouteDefinitions(1) no devolvio ninguna ruta');

        $nPropias = 0;
        foreach ($arrRutas as $clave => $ruta) {
            if ($ruta['controller'] !== self::CONTROLADOR) {
                continue;
            }
            $nPropias++;
            /*====================================*/
            // Handler apunta a un metodo existente y publico
            /*====================================*/
            [$clase, $metodo] = array_pad(explode('->', $ruta['handler']), 2, '');
            $disponible = $clase === self::CONTROLADOR
                && method_exists($clase, $metodo)
                && (new ReflectionMethod($clase, $metodo))->isPublic();
            $this->addCase($results, 'ruta_handler_'.md5($clave), $disponible,
                'la ruta '.$clave.' apunta a '.$ruta['handler'].' que no es un metodo publico de '.self::CONTROLADOR);
            /*====================================*/
            // Nivel y prefijo web
            /*====================================*/
            $this->addCase($results, 'ruta_nivel_'.md5($clave), in_array($ruta['nivel'], self::NIVELES, true),
                'la ruta '.$clave.' declara idLevelLimit fuera del catalogo 1..4');
            $web = explode(' ', $clave, 2)[1] ?? '';
            $this->addCase($results, 'ruta_prefijo_'.md5($clave),
                $web === self::RUTA_WEB || str_starts_with($web, self::RUTA_WEB.'/'),
                'la ruta '.$clave.' no cuelga de '.self::RUTA_WEB);
        }
        $this->addCase($results, 'instalador_rutas_completas', $nPropias === 10,
            'el instalador publica '.$nPropias.' rutas de '.self::CONTROLADOR.' (esperadas 10)');

        /*====================================*/
        // Endpoints esperados del CRUD
        /*====================================*/
        $esperadas = [
            'GET '.self::RUTA_WEB_LISTADO,
            'POST '.self::RUTA_WEB_SEARCH,
            'GET '.self::RUTA_WEB.'/updateList',
            'GET '.self::RUTA_WEB.'/view/@id',
            'GET '.self::RUTA_WEB.'/resumen/@id',
            'GET '.self::RUTA_WEB.'/resumenUpdate/@id',
            'POST '.self::RUTA_WEB,
            'POST '.self::RUTA_WEB.'/update',
            'PUT '.self::RUTA_WEB.'/delFiles',
            'DELETE '.self::RUTA_WEB,
        ];
        foreach ($esperadas as $clave) {
            $this->addCase($results, 'ruta_publicada_'.md5($clave), isset($arrRutas[$clave]),
                'el instalador no publica la ruta '.$clave);
        }

        /*====================================*/
        // RutaController() cubre todos los controladores con rutas
        /*====================================*/
        $lista = $this->listaControladoresInstalador();
        foreach (self::CONTROLADORES as $controlador) {
            $this->addCase($results, 'desinstalacion_cubre_'.$controlador, in_array($controlador, $lista, true),
                'RutaController() no incluye '.$controlador.' (dejaria rutas huerfanas)');
        }
        $controllersConRutas = array_unique(array_column($arrRutas, 'controller'));
        foreach ($controllersConRutas as $controlador) {
            $this->addCase($results, 'controlador_sin_huerfano_'.$controlador,
                in_array($controlador, $lista, true),
                $controlador.' publica rutas pero no figura en RutaController()');
        }

        /*====================================*/
        // Tablas e indices declarados por el instalador
        /*====================================*/
        try {
            $instalador = (new ReflectionClass('productosInstaller'))->newInstanceWithoutConstructor();
            $tablas     = (array)(new ReflectionMethod('productosInstaller', 'listTables'))->invoke($instalador);
            $optimiza   = (array)(new ReflectionMethod('productosInstaller', 'optimizeTables'))->invoke($instalador);
        } catch (Throwable $e) {
            $tablas   = [];
            $optimiza = [];
        }
        $nombresTablas = array_column($tablas, 'table');
        foreach (['productos_categorias', 'productos_tipos', 'productos_listado', 'productos_listado_documentos', 'productos_listado_observaciones'] as $tabla) {
            $this->addCase($results, 'tabla_'.$tabla, in_array($tabla, $nombresTablas, true),
                'listTables() no crea la tabla '.$tabla);
        }
        $this->addCase($results, 'optimizaciones_distintas',
            count($optimiza) === 5 && count(array_unique($optimiza)) === 5,
            'optimizeTables() debe declarar 5 optimizaciones distintas (hay '.count($optimiza).')');
        $this->addCase($results, 'rutas_de_permiso_instaladas',
            str_contains($this->fuenteInstalador(), "'RutaController' => '".self::CONTROLADOR."'"),
            'el instalador no crea el permiso con RutaController='.self::CONTROLADOR);

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                 PERMISOS                                    */
    /******************************************************************************/
    /**
     * Verifica la sesion del usuario, el nivel de acceso del controlador y
     * el registro de la ruta en `SESSION.arrPermisos` y en el router de F3.
     * Tolera el caso de usuario de pruebas sin la ruta (nivel null).
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
            $this->addCase($results, 'ruta_registrada', $rutaOk,
                'la ruta '.self::RUTA_WEB_LISTADO.' no esta entre los permisos del usuario');
        }

        /*====================================*/
        // La ruta debe existir en el router de F3 con el verbo esperado
        // (solo se evalua si el usuario de pruebas tiene la ruta registrada)
        /*====================================*/
        $arrRoutes = $f3->get('ROUTES');
        $this->addCase($results, 'router_disponible', is_array($arrRoutes),
            'F3 no expuso la variable ROUTES');

        $esquema = [
            'ruta_en_router'       => ['/'.self::RUTA_WEB_LISTADO, 'GET',  self::CONTROLADOR.'->listAll'],
            'ruta_busqueda_router' => ['/'.self::RUTA_WEB_SEARCH,  'POST', self::CONTROLADOR.'->UpdateList'],
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
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /**
     * Renderiza `productosListado-List.php` con datos sinteticos y
     * verifica la matriz de permisos de la tabla (ver >=1, editar >=2,
     * crear >=3, borrar >=4) junto a los endpoints de la pagina.
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
        $rutaList = $this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php');

        /*====================================*/
        // Estructura de la pagina de listado (nivel 1, primer render)
        // Este render encadena por require_once formSearch, UpdateList y
        // formNew, de ahi que las aserciones de estructura vayan aqui.
        /*====================================*/
        $html = $this->renderizarVista($rutaList, $this->datosSinteticos(1), $BASE, $f3);

        $this->addCase($results, 'listado_render', $html !== '', 'la vista List no produjo HTML');
        $this->addCase($results, 'listado_contenedor', str_contains($html, 'id="listTableData"'),
            'falta el contenedor de la tabla');
        $this->addCase($results, 'listado_form_busqueda', str_contains($html, 'id="FormSearchData"'),
            'no se dibuja el formulario de busqueda');
        $this->addCase($results, 'listado_form_nuevo', str_contains($html, 'id="newFormModal"'),
            'no se dibuja el modal de alta');
        $this->addCase($results, 'listado_boton_filtrar', str_contains($html, 'data-bs-target="#formSearch"'),
            'falta el boton que despliega el filtro');
        $this->addCase($results, 'listado_titulo', str_contains($html, 'Listado de Productos'),
            'no se muestra el titulo de la tabla');
        $this->addCase($results, 'listado_columnas',
            str_contains($html, '>Tipo<') && str_contains($html, '>Categoria<')
            && str_contains($html, '>Nombre<') && str_contains($html, '>Estado<'),
            'la tabla no declara las columnas Tipo/Categoria/Nombre/Estado');
        $this->addCase($results, 'listado_endpoint_ver', str_contains($html, '/view/'),
            'falta el endpoint de detalle');
        $this->addCase($results, 'listado_endpoint_actualizar', str_contains($html, '/updateList'),
            'falta el endpoint de refresco de la tabla');
        $this->addCase($results, 'listado_endpoint_busqueda',
            str_contains($html, '/search') && str_contains($html, "'POST'"),
            'el formulario de busqueda no apunta a /search con POST');
        $this->addCase($results, 'listado_sin_crear_nivel1', !str_contains($html, 'Crear Nuevo</button>'),
            'con nivel 1 el listado ofrece crear registros');
        $this->addCase($results, 'listado_sin_editar_nivel1', !str_contains($html, 'bi-pencil-square'),
            'con nivel 1 el listado ofrece editar registros');
        $this->addCase($results, 'listado_sin_borrar_nivel1', !str_contains($html, 'bi-trash'),
            'con nivel 1 el listado ofrece borrar registros');

        /*====================================*/
        // El boton "Crear Nuevo" exige nivel >= 3 (segundo render)
        /*====================================*/
        $htmlNivel3 = $this->renderizarVista($rutaList, $this->datosSinteticos(3), $BASE, $f3);
        $this->addCase($results, 'listado_crear_nivel3', str_contains($htmlNivel3, 'Crear Nuevo</button>'),
            'con nivel 3 el listado no ofrece crear registros');

        /*====================================*/
        // Matriz de permisos de las acciones por fila
        /*====================================*/
        $rutaUpdateList = $this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php');
        $matriz = [
            1 => ['ver' => true,  'editar' => false, 'borrar' => false],
            2 => ['ver' => true,  'editar' => true,  'borrar' => false],
            3 => ['ver' => true,  'editar' => true,  'borrar' => false],
            4 => ['ver' => true,  'editar' => true,  'borrar' => true],
        ];
        foreach ($matriz as $nivel => $esperado) {
            $dataFila = $this->datosSinteticos($nivel);
            // Nombre con apóstrofo: el escape con addslashes() debe notarse
            $dataFila['arrList'][0]['Nombre'] = "O'Higgins Prueba";
            $htmlFila = $this->renderizarVista($rutaUpdateList, $dataFila, $BASE, $f3);
            $this->addCase($results, 'filas_render_nivel'.$nivel, $htmlFila !== '',
                'UpdateList no produjo HTML con nivel '.$nivel);
            $this->addCase($results, 'filas_ver_nivel'.$nivel,
                $esperado['ver'] === str_contains($htmlFila, 'bi-eye'),
                'nivel '.$nivel.' '.($esperado['ver'] ? 'no ofrece' : 'ofrece').' ver el registro');
            $this->addCase($results, 'filas_editar_nivel'.$nivel,
                $esperado['editar'] === str_contains($htmlFila, 'bi-pencil-square'),
                'nivel '.$nivel.' '.($esperado['editar'] ? 'no ofrece' : 'ofrece').' editar el registro');
            $this->addCase($results, 'filas_borrar_nivel'.$nivel,
                $esperado['borrar'] === str_contains($htmlFila, 'bi-trash'),
                'nivel '.$nivel.' '.($esperado['borrar'] ? 'no ofrece' : 'ofrece').' borrar el registro');
            $this->addCase($results, 'filas_enlace_resumen_nivel'.$nivel,
                $esperado['editar'] === str_contains($htmlFila, '/resumen/'),
                'nivel '.$nivel.' '.($esperado['editar'] ? 'no enlaza' : 'enlaza').' el resumen');
            // El enlace al resumen debe llevar el ID cifrado: se extrae el
            // token de la URL y se exige que no sea el ID plano '1'.
            $idsOk = true;
            if (preg_match_all("#/resumen/([^'\"]+)#", $htmlFila, $coincidencias)) {
                foreach ($coincidencias[1] as $token) {
                    if ($token === '1' || strlen($token) < 3) {
                        $idsOk = false;
                    }
                }
            } else {
                $idsOk = $esperado['editar'] ? false : true;
            }
            $this->addCase($results, 'filas_id_cifrado_nivel'.$nivel, $idsOk,
                'nivel '.$nivel.' pinta un ID sin cifrar en el enlace del resumen');
            $this->addCase($results, 'filas_escape_nivel'.$nivel,
                $nivel < 4
                    ? str_contains($htmlFila, "O'Higgins Prueba")
                    : str_contains($htmlFila, "O\\'Higgins"),
                'nivel '.$nivel.' '.($nivel < 4 ? 'no pinta el nombre en la celda' : 'no escapa el apóstrofo del nombre con addslashes()'));
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                             VISTAS DETALLE                                  */
    /******************************************************************************/
    /**
     * Renderiza las vistas de detalle: `-View` (modal con pestañas de
     * documentos y observaciones, condicionadas por el permiso), `-Resumen`
     * (pestañas de edición, imagen y sub-recursos) y `-Resumen-Update`
     * (ficha resumida con imagen por defecto o personalizada).
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
        // Vista View con permiso de documentos activo
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'),
            $this->datosSinteticos(2, true), $BASE, $f3);

        $this->addCase($results, 'view_render', $html !== '', 'la vista View no produjo HTML');
        $this->addCase($results, 'view_titulo', str_contains($html, 'Ver Datos'),
            'el modal no muestra el titulo Ver Datos');
        $this->addCase($results, 'view_pestana_datos', str_contains($html, 'view_tab_1'),
            'falta la pestana de datos basicos');
        $this->addCase($results, 'view_pestana_documentos', str_contains($html, 'tab_id_4'),
            'con permiso de documentos no se muestra la pestana Documentos');
        $this->addCase($results, 'view_pestana_observaciones', str_contains($html, 'tab_id_5'),
            'falta la pestana Observaciones');
        $this->addCase($results, 'view_endpoint_documentos', str_contains($html, '/documentos/view/'),
            'falta el endpoint de vista de documentos');
        $this->addCase($results, 'view_documento_presente', str_contains($html, 'Factura Prueba'),
            'el documento sintetico no se pinta');
        $this->addCase($results, 'view_vencimiento_presente', str_contains($html, '2026-12-31'),
            'no se pinta la fecha de vencimiento del documento');
        $this->addCase($results, 'view_observacion_presente',
            str_contains($html, 'Observacion de prueba') && str_contains($html, 'Tester'),
            'la observacion sintetica no se pinta');
        $this->addCase($results, 'view_ficha_resumen',
            str_contains($html, 'Stock Limite') && str_contains($html, 'Valor Ingreso')
            && str_contains($html, 'Producto Prueba') && str_contains($html, 'SKU-001'),
            'la ficha de Resumen-Update no se incluye en el modal');
        $this->addCase($results, 'view_imagen_por_defecto', str_contains($html, 'img/picture-img.jpg'),
            'sin imagen personalizada no se usa la imagen por defecto');
        $this->addCase($results, 'view_boton_cerrar', str_contains($html, 'data-bs-dismiss="modal"'),
            'falta el pie con el boton Cerrar');

        /*====================================*/
        // Vista View sin permiso de documentos
        /*====================================*/
        $htmlSinDoc = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'),
            $this->datosSinteticos(2, false), $BASE, $f3);
        $this->addCase($results, 'view_sin_pestana_documentos', !str_contains($htmlSinDoc, 'tab_id_4'),
            'sin permiso de documentos se muestra la pestana Documentos');
        $this->addCase($results, 'view_sin_accion_documento', !str_contains($htmlSinDoc, 'Ver Archivo'),
            'sin permiso de documentos se pinta la accion de ver archivo');

        /*====================================*/
        // Vista Resumen (sus require_once ya se resolvieron en View)
        /*====================================*/
        $htmlResumen = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen.php'),
            $this->datosSinteticos(2, true), $BASE, $f3);

        $this->addCase($results, 'resumen_render', $htmlResumen !== '', 'la vista Resumen no produjo HTML');
        $this->addCase($results, 'resumen_pestana_resumen', str_contains($htmlResumen, '#resumen"'),
            'falta la pestana Resumen');
        $this->addCase($results, 'resumen_pestana_editar', str_contains($htmlResumen, '#resumen-edit"'),
            'falta la pestana Editar Datos');
        $this->addCase($results, 'resumen_pestana_imagen', str_contains($htmlResumen, '#resumen-img"'),
            'falta la pestana Cambiar Imagen');
        $this->addCase($results, 'resumen_pestana_documentos', str_contains($htmlResumen, '#resumen-documentos"'),
            'con permiso de documentos falta la pestana Documentos');
        $this->addCase($results, 'resumen_pestana_observaciones', str_contains($htmlResumen, '#resumen-obs"'),
            'falta la pestana Observaciones');
        $this->addCase($results, 'resumen_form_edicion',
            str_contains($htmlResumen, 'id="FormEditData"') && str_contains($htmlResumen, 'Edit_Nombre')
            && str_contains($htmlResumen, 'Edit_idTipoProducto') && str_contains($htmlResumen, 'Edit_idCategoria')
            && str_contains($htmlResumen, 'Edit_idUniMed') && str_contains($htmlResumen, 'Edit_Codigo')
            && str_contains($htmlResumen, 'Edit_Descripcion') && str_contains($htmlResumen, 'Edit_StockLimite')
            && str_contains($htmlResumen, 'Edit_ValorIngreso') && str_contains($htmlResumen, 'Edit_ValorEgreso'),
            'el formulario de edicion no declara todos sus campos');
        $this->addCase($results, 'resumen_id_oculto', str_contains($htmlResumen, 'name="idProducto"'),
            'el formulario de edicion no transporta el idProducto oculto');
        $this->addCase($results, 'resumen_endpoint_update',
            str_contains($htmlResumen, '/update') && str_contains($htmlResumen, "'POST'"),
            'el formulario de edicion no apunta a /update con POST');
        $this->addCase($results, 'resumen_endpoint_resumen_update', str_contains($htmlResumen, '/resumenUpdate/'),
            'falta el endpoint de refresco tras editar');
        $this->addCase($results, 'resumen_endpoint_delfiles', str_contains($htmlResumen, '/delFiles'),
            'falta el endpoint de borrado de la imagen');
        $this->addCase($results, 'resumen_subrecursos_observaciones',
            str_contains($htmlResumen, 'tabObsNew(') && str_contains($htmlResumen, '/observaciones/updateList/')
            && str_contains($htmlResumen, '/observaciones/new/') && str_contains($htmlResumen, '/observaciones/view/')
            && str_contains($htmlResumen, '/observaciones/getID/'),
            'el resumen no publica las acciones de observaciones');
        $this->addCase($results, 'resumen_subrecursos_documentos',
            str_contains($htmlResumen, 'tabDocumentosLoadList') && str_contains($htmlResumen, '/documentos/updateList/')
            && str_contains($htmlResumen, 'tabDocumentosDataTable'),
            'el resumen no publica las acciones de documentos');
        $this->addCase($results, 'resumen_volver_listado',
            str_contains($htmlResumen, 'Volver') && str_contains($htmlResumen, '/listAll'),
            'falta el enlace de vuelta al listado');
        $this->addCase($results, 'resumen_titulo_imagen', str_contains($htmlResumen, 'Imagen de Producto Prueba'),
            'la pestana de imagen no nombra al producto');

        /*====================================*/
        // Resumen sin permiso de documentos
        /*====================================*/
        $htmlResumenSinDoc = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen.php'),
            $this->datosSinteticos(2, false), $BASE, $f3);
        $this->addCase($results, 'resumen_sin_pestana_documentos',
            !str_contains($htmlResumenSinDoc, '#resumen-documentos"'),
            'sin permiso de documentos se muestra la pestana Documentos');

        /*====================================*/
        // Resumen-Update directo: imagen por defecto y personalizada
        /*====================================*/
        $rutaRU = $this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen-Update.php');
        $htmlRU = $this->renderizarVista($rutaRU, $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'resumen_update_render', $htmlRU !== '', 'la vista Resumen-Update no produjo HTML');
        $this->addCase($results, 'resumen_update_imagen_default', str_contains($htmlRU, 'img/picture-img.jpg'),
            'sin imagen no se usa la imagen por defecto');
        $this->addCase($results, 'resumen_update_badge_estado', str_contains($htmlRU, 'badge-sp1-success'),
            'no se pinta el badge del estado');
        $this->addCase($results, 'resumen_update_unimed', str_contains($htmlRU, '10.00 Unidad'),
            'el stock limite no se muestra con su unidad de medida');

        $dataConImg                              = $this->datosSinteticos(2);
        $dataConImg['rowData']['Direccion_img']  = 'producto_prueba.jpg';
        $htmlRUImg = $this->renderizarVista($rutaRU, $dataConImg, $BASE, $f3);
        $this->addCase($results, 'resumen_update_imagen_propia',
            str_contains($htmlRUImg, '/admin/public/upload/producto_prueba.jpg'),
            'con imagen personalizada no se usa MainPathUrl');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                          VISTAS FORMULARIOS                                 */
    /******************************************************************************/
    /**
     * Renderiza `-formNew` y `-formSearch`: identificadores de formulario,
     * campos prefijados, endpoint y verbo de envio.
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
        // Formulario de alta
        /*====================================*/
        $htmlNew = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-formNew.php'),
            $this->datosSinteticos(3), $BASE, $f3);

        $this->addCase($results, 'form_new_render', $htmlNew !== '', 'la vista formNew no produjo HTML');
        $this->addCase($results, 'form_new_modal', str_contains($htmlNew, 'id="newFormModal"'),
            'el modal de alta no declara su identificador');
        $this->addCase($results, 'form_new_formulario',
            str_contains($htmlNew, 'id="FormNewData"') && str_contains($htmlNew, 'method="POST"'),
            'el formulario de alta no declara id ni verbo POST');
        $this->addCase($results, 'form_new_campos',
            str_contains($htmlNew, 'idTipoProducto') && str_contains($htmlNew, 'idCategoria')
            && str_contains($htmlNew, 'Nombre') && str_contains($htmlNew, 'Marca')
            && str_contains($htmlNew, 'idUniMed'),
            'el formulario de alta no declara todos sus campos');
        $this->addCase($results, 'form_new_estado_oculto', str_contains($htmlNew, 'name="idEstado"'),
            'el formulario de alta no fija el estado oculto');
        $this->addCase($results, 'form_new_destino',
            str_contains($htmlNew, '/resumen/') && str_contains($htmlNew, "'POST'"),
            'tras crear no se redirige al resumen del registro');

        /*====================================*/
        // Formulario de busqueda
        /*====================================*/
        $htmlSearch = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-formSearch.php'),
            $this->datosSinteticos(1), $BASE, $f3);

        $this->addCase($results, 'form_search_render', $htmlSearch !== '', 'la vista formSearch no produjo HTML');
        $this->addCase($results, 'form_search_formulario', str_contains($htmlSearch, 'id="FormSearchData"'),
            'el formulario de busqueda no declara su identificador');
        $this->addCase($results, 'form_search_inputs',
            str_contains($htmlSearch, 'id="Search_Nombre"') && str_contains($htmlSearch, 'id="Search_Marca"')
            && str_contains($htmlSearch, 'id="Search_Codigo"') && str_contains($htmlSearch, 'id="Search_idEstado"')
            && str_contains($htmlSearch, 'id="Search_idTipoProducto"') && str_contains($htmlSearch, 'id="Search_idCategoria"')
            && str_contains($htmlSearch, 'id="Search_idUniMed"'),
            'el formulario de busqueda no declara sus campos prefijados con Search_');
        $this->addCase($results, 'form_search_endpoint',
            str_contains($htmlSearch, '/search') && str_contains($htmlSearch, "'POST'"),
            'el formulario de busqueda no apunta a /search con POST');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos el chequeo de datos del controlador
     * (`dataCheck`), el cifrado de identificadores y la validacion de IDs
     * descifrados.
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
            $Controller = (new ReflectionClass(self::CONTROLADOR))->newInstanceWithoutConstructor();

            $Post = [
                'Nombre'         => 'Producto Prueba',
                'idEstado'       => 1,
                'idTipoProducto' => 1,
                'idCategoria'    => 1,
                'idUniMed'       => 1,
                'StockLimite'    => '10.00',
            ];
            $DataCheck = $reflector->invoke($Controller, $Post);

            $this->addCase($results, 'datacheck_es_arreglo', is_array($DataCheck),
                'dataCheck() no devolvio un arreglo');
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post,
                'dataCheck() no conserva el POST recibido');
            $this->addCase($results, 'datacheck_numericos',
                str_contains((string)($DataCheck['ValidarNumero'] ?? ''), 'StockLimite')
                && str_contains((string)($DataCheck['ValidarNumero'] ?? ''), 'ValorEgreso'),
                'dataCheck() no valida los montos como numeros');
            $this->addCase($results, 'datacheck_enteros',
                str_contains((string)($DataCheck['ValidarEntero'] ?? ''), 'idEstado')
                && str_contains((string)($DataCheck['ValidarEntero'] ?? ''), 'idTipoProducto')
                && str_contains((string)($DataCheck['ValidarEntero'] ?? ''), 'idCategoria')
                && str_contains((string)($DataCheck['ValidarEntero'] ?? ''), 'idUniMed'),
                'dataCheck() no valida los identificadores como enteros');
            $this->addCase($results, 'datacheck_largo',
                (string)($DataCheck['ValidarLargoMinimo'] ?? '') === 'Nombre,Marca,Descripcion,Codigo'
                && (int)($DataCheck['ValidarLargoMinimoN'] ?? 0) === 3
                && ($DataCheck['ValidarLargoMaximoPorCampo'] ?? []) === [
                    'Nombre' => 255,
                    'Marca' => 255,
                    'Codigo' => 255,
                ],
                'dataCheck() no fija correctamente todos los campos de largo (minimo/maximo)');
            $this->addCase($results, 'datacheck_censura',
                str_contains((string)($DataCheck['ValidarPalabrasCensuradas'] ?? ''), 'Nombre'),
                'dataCheck() no censura palabras en Nombre');
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
     * Revisa estaticamente las vistas y el controlador del modulo: ausencia
     * de rutas relativas maliciosas, de rutas absolutas del sistema de
     * ficheros, de acceso directo a la sesion desde las plantillas, de SQL
     * embebido y concatenaciones de superglobals en consultas.
     *
     * @return array Resultado por caso
     */
    public function seguridadVistasTest(){

        $results = [];

        /*====================================*/
        // Vistas involucradas
        /*====================================*/
        $arrVistas = glob($this->rutaModulo('../views/'.self::CONTROLADOR.'-*.php')) ?: [];
        $this->addCase($results, 'hay_vistas', count($arrVistas) > 0,
            'no se encontraron vistas del controlador '.self::CONTROLADOR);

        foreach ($arrVistas as $ruta) {
            $nombre = basename($ruta);
            $codigo = $this->codigoFuente($ruta);

            $this->addCase($results, 'vista_sin_retroceso_'.$nombre, !preg_match('#(?:\.\./|\.\.\\\\\\\\)#', $codigo),
                $nombre.' construye rutas con retroceso');
            $this->addCase($results, 'vista_sin_ruta_absoluta_'.$nombre,
                !preg_match('#(?:/var/www|/home/|[A-Za-z]:\\\\\\\\)#', $codigo),
                $nombre.' declara rutas absolutas del sistema de ficheros');
            $this->addCase($results, 'vista_sin_sesion_'.$nombre, !str_contains($codigo, '$_SESSION'),
                $nombre.' accede directamente a la sesion');
            $this->addCase($results, 'vista_sin_sql_'.$nombre,
                !preg_match('/\b(SELECT|INSERT|UPDATE|DELETE)\s+.+\bFROM\b/i', $codigo),
                $nombre.' embebe sentencias SQL');
        }

        /*====================================*/
        // Controlador
        /*====================================*/
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'controlador_sin_concat_sql',
            !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $codigo),
            'el controlador concatena superglobals en cadenas');
        $this->addCase($results, 'controlador_parametros_preparados',
            str_contains($codigo, "'params'"),
            'el controlador no usa la clave params (prepared statements)');
        $this->addCase($results, 'controlador_sin_eval',
            !str_contains($codigo, 'eval(') && !str_contains($codigo, 'shell_exec('),
            'el controlador usa eval o shell_exec');

        /*====================================*/
        // Escape antes de pintar
        /*====================================*/
        $vistaListado = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'));
        $this->addCase($results, 'tabla_escape', str_contains($vistaListado, 'addslashes('),
            'UpdateList no escapa el nombre del producto antes de pintarlo');
        $this->addCase($results, 'tabla_id_cifrado',
            str_contains($vistaListado, "encryptDecrypt('encrypt'"),
            'UpdateList no cifra el ID antes de componer los enlaces');
        $vistaView = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'));
        $this->addCase($results, 'fecha_estandar', str_contains($vistaView, 'fechaEstandar('),
            'View no normaliza la fecha de creacion de las observaciones');

        /************************************/
        // Retorno los datos
        return $results;

    }

}















