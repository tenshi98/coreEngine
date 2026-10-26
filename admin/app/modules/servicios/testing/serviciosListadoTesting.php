<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class serviciosListadoTesting
 *
 * Pruebas del modulo `servicios` - controlador `serviciosListado` (CRUD tipo Resumen con imagen,
 * pestanas de documentos y observaciones atendidas por otros controladores).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. El normalizador
 *    `sistemaTesteos::normalizeResult()` marca EXITOSA o ERROR segun el contenido, por lo que
 *    los textos de los casos correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Alcance: pruebas NO destructivas. `Insert()`, `Update()`, `Delete()` y `delFiles()` terminan en
 * `Response::success()`/`Response::error()` (que ejecutan `exit`) y escriben en BD o en disco, por
 * eso NO se invocan: se verifican por reflexion y lectura del fuente. El instalador se lee con
 * `newInstanceWithoutConstructor`, sin ejecutar `InstallModule`/`UninstallModule`.
 *
 * Fuera de alcance: las vistas `-Resumen-Documentos-*` y `-Resumen-Observaciones-*` pertenecen a
 * `serviciosListadoDocumentos` y `serviciosListadoObservaciones`.
 *
 * Nota de diseno: `List` y `View`/`Resumen` encadenan vistas por `require_once`; el primer render
 * de cada una va antes que los renders directos (que usan `include`, re-ejecutable).
 *
 * @see skills/testing/SKILL.md
 */
class serviciosListadoTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR      = 'serviciosListado';
    private const RUTA_WEB         = 'administracion/servicios/listado';
    private const RUTA_WEB_LISTADO = 'administracion/servicios/listado/listAll';
    private const RUTA_WEB_SEARCH  = 'administracion/servicios/listado/search';
    private const NIVELES          = [1, 2, 3, 4];
    /*=========== Vistas propias del controlador ===========*/
    private const VISTAS           = [
        'List', 'UpdateList', 'View', 'formNew', 'formSearch', 'Resumen', 'Resumen-Update',
    ];
    /*=========== Controladores del modulo (instalador) ===========*/
    private const CONTROLADORES    = [
        'serviciosCategorias',
        'serviciosListado',
        'serviciosListadoDocumentos',
        'serviciosListadoObservaciones',
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
        return $this->codigoFuente($this->rutaModulo('../installer/serviciosInstaller.php'));
    }

    /*******************************************************************/
    // Parsea getRouteDefinitions() del instalador a un mapa indexado
    // por verbo + RutaWeb. El reparto por tipo de permiso (1=categorias)
    // obliga a agregar los tipos 1..N.
    /*******************************************************************/
    private function rutasInstalador(): array{
        $rutas = [];
        try {
            $instalador = (new ReflectionClass('serviciosInstaller'))->newInstanceWithoutConstructor();
            $reflector  = new ReflectionMethod('serviciosInstaller', 'getRouteDefinitions');
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
            $instalador = (new ReflectionClass('serviciosInstaller'))->newInstanceWithoutConstructor();
            $reflector  = new ReflectionMethod('serviciosInstaller', 'RutaController');
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
    // Lectura del fuente de una vista propia del controlador
    /*******************************************************************/
    private function fuenteVista(string $sufijo): string{
        return $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-'.$sufijo.'.php'));
    }

    /*******************************************************************/
    // Cuerpo del metodo del controlador (entre su firma y su cierre),
    // para acotar las aserciones por fuente
    /*******************************************************************/
    private function cuerpoMetodo(string $metodo): string{
        try {
            $reflector = new ReflectionMethod(self::CONTROLADOR, $metodo);
        } catch (Throwable $e) {
            return '';
        }
        $lineas = file($this->rutaModulo('../controller/'.self::CONTROLADOR.'.php')) ?: [];
        $inicio = $reflector->getStartLine() - 1;
        $largo  = $reflector->getEndLine() - $inicio;
        return implode('', array_slice($lineas, $inicio, $largo));
    }

    /*******************************************************************/
    // Instancia del controlador sin constructor (logica pura, sin BD)
    /*******************************************************************/
    private function instanciaSinBD(){
        return (new ReflectionClass(self::CONTROLADOR))->newInstanceWithoutConstructor();
    }

    /*******************************************************************/
    // Ejecuta dataCheck() (privado) y pasa su contrato por CheckData,
    // devolviendo el resultado real de las validaciones
    /*******************************************************************/
    private function validarPost(array $post): array{
        $reflector = new ReflectionMethod(self::CONTROLADOR, 'dataCheck');
        $reflector->setAccessible(true);
        $DataCheck = $reflector->invoke($this->instanciaSinBD(), $post);
        return (new CheckData())->checkingData($DataCheck);
    }

    /*******************************************************************/
    // Post valido de referencia (datos sinteticos)
    /*******************************************************************/
    private function postValido(): array{
        return [
            'idEstado'     => '1',
            'idCategoria'  => '1',
            'Nombre'       => 'Servicio Prueba',
            'ValorIngreso' => '1500.50',
            'ValorEgreso'  => '1000',
            'Descripcion'  => 'Descripcion de prueba',
            'Codigo'       => 'SRV-001',
        ];
    }

    /*******************************************************************/
    // Datos sinteticos usados por las vistas del listado
    /*******************************************************************/
    private function datosSinteticos(int $nivel, bool $verDocumentos = true, string $imagen = ''): array{
        return [
            'PageTitle'        => 'Listado Servicios',
            'PageDescription'  => 'Listado Servicios.',
            'PageAuthor'       => 'Pruebas',
            'PageKeywords'     => 'Pruebas',
            'TableTitle'       => 'Listado de Servicios',
            'UserData'         => [
                'MainPathUrl'                   => '/admin/public/upload/',
                'sistemaModalSubtitle'          => 2,
                'sistemaModalCloseBTN'          => 2,
                'UserType'                      => 2,
                'serviciosListadoVerDocumentos' => $verDocumentos ? 2 : 1,
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
                'idServicio'  => 1,
                'Nombre'      => 'Servicio Prueba',
                'Estado'      => 'Activo',
                'EstadoColor' => 'success',
                'Categoria'   => 'Categoria Prueba',
            ]],
            'arrEstado'        => [['ID' => 1, 'Nombre' => 'Activo', 'Color' => 'success']],
            'arrCategoria'     => [['ID' => 1, 'Nombre' => 'Categoria Prueba']],
            'arrDocumentos'    => [[
                'idDocumentos'   => 1,
                'Nombre'         => 'Contrato Prueba',
                'NombreArchivo'  => 'contrato_prueba.pdf',
                'FVencimiento'   => '2026-12-31',
            ]],
            'arrObservaciones' => [[
                'FechaCreacion' => '2026-01-02',
                'Usuario'       => 'Tester',
                'Observacion'   => 'Observacion de prueba',
            ]],
            'rowData'          => [
                'idServicio'    => 1,
                'idEstado'      => 1,
                'idCategoria'   => 1,
                'Nombre'        => 'Servicio Prueba',
                'ValorIngreso'  => '1500.50',
                'ValorEgreso'   => '1000.00',
                'Descripcion'   => 'Descripcion de prueba',
                'Codigo'        => 'SRV-001',
                'Direccion_img' => $imagen,
                'Estado'        => 'Activo',
                'EstadoColor'   => 'success',
                'Categoria'     => 'Categoria Prueba',
            ],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica los archivos obligatorios del controlador: controlador, las 7
     * vistas propias, instalador, fichero de pruebas, README, `.htaccess` y
     * herencias.
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
        foreach (['FormInputs' => 'UIFormInputs', 'Codification' => 'FunctionsSecurityCodification',
                  'DataDate' => 'FunctionsDataDate', 'WidgetsCommon' => 'UIWidgetsCommon',
                  'WidgetsViews' => 'UIWidgetsViews'] as $propiedad => $clase) {
            $this->addCase($results, 'controlador_instancia_'.$propiedad,
                str_contains($fuente, "\$this->".$propiedad." ") && str_contains($fuente, 'new '.$clase.'()'),
                self::CONTROLADOR.' no instancia '.$clase.' en $this->'.$propiedad);
        }

        /*====================================*/
        // Vistas propias del controlador (no se cuentan las de otros)
        /*====================================*/
        foreach (self::VISTAS as $vista) {
            $archivo = self::CONTROLADOR.'-'.$vista.'.php';
            $this->addCase($results, 'vista_'.$archivo, is_file($this->rutaModulo('../views/'.$archivo)),
                'falta la vista '.$archivo);
        }
        $this->addCase($results, 'sin_vista_formEdit',
            !is_file($this->rutaModulo('../views/'.self::CONTROLADOR.'-formEdit.php')),
            'el listado edita desde Resumen: no deberia existir formEdit');

        /*====================================*/
        // Instalador
        /*====================================*/
        $rutaInstalador = $this->rutaModulo('../installer/serviciosInstaller.php');
        $this->addCase($results, 'instalador_existe', is_file($rutaInstalador),
            'no existe installer/serviciosInstaller.php');
        $this->addCase($results, 'instalador_clase', class_exists('serviciosInstaller'),
            'PHP no declara la clase serviciosInstaller');
        $this->addCase($results, 'instalador_herencia',
            is_subclass_of('serviciosInstaller', 'ControllerInstaller'),
            'serviciosInstaller no extiende ControllerInstaller');
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
     * Revisa por reflexion las firmas de los metodos de vista (`listAll`,
     * `UpdateList`, `View`, `Resumen`, `ResumenUpdate`), su contrato de
     * presentacion y las consultas que alimentan cada vista.
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
        // Consultas del listado (listAll)
        /*====================================*/
        $cuerpo = $this->cuerpoMetodo('listAll');
        $this->addCase($results, 'listall_tabla', str_contains($cuerpo, "'table'   => 'servicios_listado'"),
            'listAll() no consulta servicios_listado');
        $this->addCase($results, 'listall_joins',
            str_contains($cuerpo, 'LEFT JOIN core_estados') && str_contains($cuerpo, 'LEFT JOIN servicios_categorias'),
            'listAll() no une estados y categorias');
        $this->addCase($results, 'listall_orden',
            str_contains($cuerpo, 'servicios_listado.idEstado ASC, servicios_categorias.Nombre ASC, servicios_listado.Nombre ASC'),
            'listAll() no ordena por estado, categoria y nombre');
        $this->addCase($results, 'listall_catalogos',
            str_contains($cuerpo, "'table'   => 'core_estados'") && str_contains($cuerpo, "'table'   => 'servicios_categorias'"),
            'listAll() no carga los catalogos de estados y categorias');
        $this->addCase($results, 'listall_limite', substr_count($cuerpo, 'ConfigAPP::APP["N_MaxItems"]') === 3,
            'las 3 consultas de listAll() deben acotarse con N_MaxItems');
        $this->addCase($results, 'listall_errores_fusionados',
            str_contains($cuerpo, 'mergeResponses([$arrList,$arrEstado,$arrCategoria])'),
            'listAll() no fusiona las respuestas de sus 3 consultas');
        $this->addCase($results, 'listall_datos_vista',
            str_contains($cuerpo, "'arrEstado'") && str_contains($cuerpo, "'arrCategoria'") && str_contains($cuerpo, "'TableTitle'      => 'Listado de Servicios'"),
            'listAll() no entrega arrEstado, arrCategoria y TableTitle a la vista');

        /*====================================*/
        // Filtros de la busqueda (UpdateList)
        /*====================================*/
        $cuerpo = $this->cuerpoMetodo('UpdateList');
        $this->addCase($results, 'filtros_exactos', str_contains($cuerpo, "\$WhereData_int     = 'idEstado,idCategoria'"),
            'UpdateList() no filtra por idEstado e idCategoria (busqueda exacta)');
        $this->addCase($results, 'filtros_relativos', str_contains($cuerpo, "\$WhereData_string  = 'Nombre,Codigo'"),
            'UpdateList() no filtra por Nombre y Codigo (busqueda relativa)');
        $this->addCase($results, 'filtros_validacion_fechas', str_contains($cuerpo, 'searchValidateDates('),
            'UpdateList() no valida el rango de fechas de busqueda');
        $this->addCase($results, 'filtros_where_por_tabla',
            substr_count($cuerpo, "searchWhere(\$whereInt, \$whereParams,") === 3
            && substr_count($cuerpo, "'servicios_listado'") >= 4,
            'UpdateList() no compone el WHERE contra servicios_listado');
        $this->addCase($results, 'filtros_params_enlazados',
            str_contains($cuerpo, "'where'   => \$whereInt") && str_contains($cuerpo, "'params'  => \$whereParams"),
            'UpdateList() no enlaza los parametros del WHERE');
        $this->addCase($results, 'respuestas_fusionadas', str_contains($cuerpo, 'mergeResponses('),
            'UpdateList() no fusiona las respuestas con mergeResponses()');

        /*====================================*/
        // View: documentos condicionados por la opcion de usuario
        /*====================================*/
        $cuerpo = $this->cuerpoMetodo('View');
        $this->addCase($results, 'view_documentos_condicionados',
            str_contains($cuerpo, '$arrUserData["serviciosListadoVerDocumentos"]==2')
            && str_contains($cuerpo, "\$arrDocumentos['data']   = [];"),
            'View() no condiciona la carga de documentos a serviciosListadoVerDocumentos');
        $this->addCase($results, 'view_consulta_documentos',
            str_contains($cuerpo, "'table'   => 'servicios_listado_documentos'")
            && str_contains($cuerpo, "'where'   => 'idServicio = ?'"),
            'View() no consulta los documentos del servicio con parametro');
        $this->addCase($results, 'view_consulta_observaciones',
            str_contains($cuerpo, "'table'   => 'servicios_listado_observaciones'")
            && str_contains($cuerpo, 'LEFT JOIN usuarios_listado')
            && str_contains($cuerpo, 'servicios_listado_observaciones.idServicio = ?'),
            'View() no consulta las observaciones con su usuario y parametro');
        $this->addCase($results, 'view_errores_fusionados',
            str_contains($cuerpo, 'mergeResponses([$rowData,$arrDocumentos,$arrObservaciones])'),
            'View() no fusiona las respuestas de sus consultas');

        /*====================================*/
        // Resumen / ResumenUpdate
        /*====================================*/
        $cuerpo = $this->cuerpoMetodo('Resumen');
        $this->addCase($results, 'resumen_campos_edicion',
            str_contains($cuerpo, 'servicios_listado.idEstado,') && str_contains($cuerpo, 'servicios_listado.idCategoria,'),
            'Resumen() no consulta idEstado e idCategoria para el formulario de edicion');
        $this->addCase($results, 'resumen_catalogos',
            str_contains($cuerpo, "'arrEstado'") && str_contains($cuerpo, "'arrCategoria'"),
            'Resumen() no entrega los catalogos a la vista');
        $cuerpo = $this->cuerpoMetodo('ResumenUpdate');
        $this->addCase($results, 'resumen_update_ficha',
            str_contains($cuerpo, "'rowData'          => \$rowData['data']"),
            'ResumenUpdate() no entrega rowData a la ficha');
        $this->addCase($results, 'resumen_update_sin_catalogos',
            !str_contains($cuerpo, "'arrEstado'") && !str_contains($cuerpo, "'arrCategoria'"),
            'ResumenUpdate() solo refresca la ficha: no necesita los catalogos');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              ESCRITURA                                      */
    /******************************************************************************/
    /**
     * Verifica SIN ejecutarlos los metodos de escritura (`Insert`, `Update`,
     * `Delete`, `delFiles`): verbo HTTP, dataCheck, tabla, obligatorios,
     * unicidad, archivos, transaccion y IDs descifrados (reflexion y fuente).
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
        // Insert
        /*====================================*/
        $cuerpo = $this->cuerpoMetodo('Insert');
        $this->addCase($results, 'insert_datacheck', str_contains($cuerpo, '$this->dataCheck($_POST)'),
            'Insert() no construye el DataCheck con dataCheck($_POST)');
        $this->addCase($results, 'insert_tabla', str_contains($cuerpo, "'table'     => 'servicios_listado'"),
            'Insert() no apunta a la tabla servicios_listado');
        $this->addCase($results, 'insert_campos',
            str_contains($cuerpo, "'data'      => 'idEstado,idCategoria,Nombre,ValorIngreso,ValorEgreso,Descripcion,Codigo'"),
            'Insert() no declara los campos esperados del servicio');
        $this->addCase($results, 'insert_obligatorios',
            str_contains($cuerpo, "'required'  => 'idEstado,idCategoria,Nombre'"),
            'Insert() no declara idEstado,idCategoria,Nombre como obligatorios');
        $this->addCase($results, 'insert_unicidad',
            str_contains($cuerpo, "'unique'    => 'Nombre,Codigo'"),
            'Insert() no declara la unicidad de Nombre y Codigo');
        $this->addCase($results, 'insert_base_insert', str_contains($cuerpo, '$this->Base_insert('),
            'Insert() no delega en Base_insert()');
        $this->addCase($results, 'insert_fallo_controlado',
            str_contains($cuerpo, "\$Response['status'] === false") && str_contains($cuerpo, '$this->failOperation($Response)'),
            'Insert() no responde a una escritura rechazada con failOperation()');
        $this->addCase($results, 'insert_id_cifrado',
            str_contains($cuerpo, "encryptDecrypt('encrypt', \$Response['data'])")
            && str_contains($cuerpo, "isValidDecrypted(\$DataID, 'text')")
            && str_contains($cuerpo, 'Response::success($DataID[\'data\'])'),
            'Insert() no devuelve el ID del nuevo servicio cifrado y validado');
        $this->addCase($results, 'insert_sin_archivos', !str_contains($cuerpo, "'files'"),
            'Insert() no deberia subir archivos (la imagen se carga desde Resumen)');

        /*====================================*/
        // Update (modificar y subir imagen)
        /*====================================*/
        $cuerpo = $this->cuerpoMetodo('Update');
        $this->addCase($results, 'update_verbo_post', str_contains($cuerpo, "REQUEST_METHOD'] !== 'POST'")
            && str_contains($cuerpo, "Response::error('Error en el Request Method', 405)"),
            'Update() no rechaza con 405 un verbo distinto de POST');
        $this->addCase($results, 'update_datacheck', str_contains($cuerpo, '$this->dataCheck($_POST)'),
            'Update() no construye el DataCheck con dataCheck($_POST)');
        $this->addCase($results, 'update_tabla_donde',
            str_contains($cuerpo, "'table'     => 'servicios_listado'") && str_contains($cuerpo, "'where'     => 'idServicio'"),
            'Update() no delimita la escritura por idServicio en servicios_listado');
        $this->addCase($results, 'update_obligatorios',
            str_contains($cuerpo, "'required'  => 'idServicio,idEstado,idCategoria,Nombre'"),
            'Update() no declara idServicio,idEstado,idCategoria,Nombre como obligatorios');
        $this->addCase($results, 'update_unicidad', str_contains($cuerpo, "'unique'    => 'Nombre,Codigo'"),
            'Update() no declara la unicidad de Nombre y Codigo');
        $this->addCase($results, 'update_base_update', str_contains($cuerpo, '$this->Base_update('),
            'Update() no delega en Base_update()');
        $this->addCase($results, 'update_fallo_controlado', str_contains($cuerpo, '$this->failOperation($Response)'),
            'Update() no responde a una escritura rechazada con failOperation()');
        $this->addCase($results, 'update_imagen_identificador', str_contains($cuerpo, "'Identificador' => 'Direccion_img'"),
            'Update() no gestiona la imagen en la columna Direccion_img');
        $this->addCase($results, 'update_imagen_validacion',
            str_contains($cuerpo, "'ValidarTipo'   => 'image'") && str_contains($cuerpo, "'ValidarPeso'   => 10"),
            'Update() no valida que el archivo sea imagen de hasta 10 MB');
        $this->addCase($results, 'update_imagen_sufijo_base64',
            str_contains($cuerpo, "'SufijoArchivo' => 'ServicioIMG_'") && str_contains($cuerpo, "'Base64'        => true"),
            'Update() no nombra la imagen con el sufijo ServicioIMG_ ni usa Base64');
        $this->addCase($results, 'update_imagen_raiz_upload', str_contains($cuerpo, "'SubCarpeta'    => ''"),
            'Update() no guarda la imagen en la raiz de upload');

        /*====================================*/
        // Delete: verbo, transaccion y limpieza de dependientes
        /*====================================*/
        $cuerpo = $this->cuerpoMetodo('Delete');
        $this->addCase($results, 'delete_verbo', str_contains($cuerpo, "REQUEST_METHOD'] !== 'DELETE'")
            && str_contains($cuerpo, "Response::error('Error en el Request Method', 405)"),
            'Delete() no rechaza con 405 un verbo distinto de DELETE');
        $this->addCase($results, 'delete_lee_cuerpo', str_contains($cuerpo, 'parse_str(file_get_contents("php://input"),$dataDelete)'),
            'Delete() no lee el cuerpo DELETE con parse_str');
        $this->addCase($results, 'delete_transaccion',
            str_contains($cuerpo, 'Base_transactionBegin()') && str_contains($cuerpo, 'Base_transactionCommit()'),
            'Delete() debe abrir y confirmar una transaccion');
        $this->addCase($results, 'delete_orden_transaccion',
            strpos($cuerpo, 'Base_transactionBegin()') < strpos($cuerpo, '$this->Base_delete(')
            && strpos($cuerpo, '$this->Base_delete(') < strpos($cuerpo, 'Base_transactionCommit()'),
            'Delete() no abre la transaccion antes de borrar ni confirma al final');
        $this->addCase($results, 'delete_reversion', substr_count($cuerpo, '$this->failOperation(') === 2,
            'Delete() debe revertir con failOperation() tanto el borrado principal como el de dependientes');
        $this->addCase($results, 'delete_principal',
            str_contains($cuerpo, "'files'       => 'Direccion_img'")
            && str_contains($cuerpo, "'table'       => 'servicios_listado'")
            && str_contains($cuerpo, "'where'       => 'idServicio'"),
            'Delete() no borra servicios_listado por idServicio junto con su imagen Direccion_img');
        $this->addCase($results, 'delete_dependientes_documentos',
            str_contains($cuerpo, "['files' => 'NombreArchivo', 'table' => 'servicios_listado_documentos']"),
            'Delete() no borra los documentos del servicio con sus archivos (NombreArchivo)');
        $this->addCase($results, 'delete_dependientes_observaciones',
            str_contains($cuerpo, "['files' => '',              'table' => 'servicios_listado_observaciones']"),
            'Delete() no borra las observaciones del servicio');
        $this->addCase($results, 'delete_dependientes_por_servicio',
            str_contains($cuerpo, "'where' => 'idServicio'") && str_contains($cuerpo, "'Post' => \$dataDelete"),
            'Delete() no acota el borrado de dependientes por idServicio');
        $this->addCase($results, 'delete_respuesta', str_contains($cuerpo, 'Response::success($Response[\'data\'])'),
            'Delete() no responde con Response::success()');

        /*====================================*/
        // delFiles: borrado de la imagen
        /*====================================*/
        $cuerpo = $this->cuerpoMetodo('delFiles');
        $this->addCase($results, 'delfiles_verbo', str_contains($cuerpo, "REQUEST_METHOD'] !== 'PUT'")
            && str_contains($cuerpo, "Response::error('Error en el Request Method', 405)"),
            'delFiles() no rechaza con 405 un verbo distinto de PUT');
        $this->addCase($results, 'delfiles_lee_cuerpo', str_contains($cuerpo, 'parse_str(file_get_contents("php://input"),$dataPut)'),
            'delFiles() no lee el cuerpo PUT con parse_str');
        $this->addCase($results, 'delfiles_query',
            str_contains($cuerpo, "'files'       => 'Direccion_img'")
            && str_contains($cuerpo, "'table'       => 'servicios_listado'")
            && str_contains($cuerpo, "'where'       => 'idServicio'")
            && str_contains($cuerpo, "'SubCarpeta'  => ''"),
            'delFiles() no apunta a Direccion_img de servicios_listado por idServicio');
        $this->addCase($results, 'delfiles_base_delfiles', str_contains($cuerpo, '$this->Base_delFiles('),
            'delFiles() no delega en Base_delFiles()');
        $this->addCase($results, 'delfiles_fallo_controlado', str_contains($cuerpo, '$this->failOperation($Response)'),
            'delFiles() no responde a un rechazo con failOperation()');
        $this->addCase($results, 'delfiles_sin_transaccion', !str_contains($cuerpo, 'Base_transactionBegin()'),
            'delFiles() opera sobre una sola tabla y no abre transaccion');

        /*====================================*/
        // IDs descifrados antes de consultar
        /*====================================*/
        foreach (['View', 'Resumen', 'ResumenUpdate'] as $metodo) {
            $cuerpo = $this->cuerpoMetodo($metodo);
            $this->addCase($results, 'id_descifrado_'.$metodo,
                str_contains($cuerpo, "encryptDecrypt('decrypt', \$params['id'])")
                && str_contains($cuerpo, "isValidDecrypted(\$ServicioID, 'id')")
                && str_contains($cuerpo, "Response::error('Registro inválido', 400)"),
                $metodo.'() no descifra y valida $params[\'id\'] (400 si es invalido)');
            $this->addCase($results, 'id_parametrizado_'.$metodo,
                str_contains($cuerpo, "'where'   => 'servicios_listado.idServicio = ?'")
                && str_contains($cuerpo, "'params'  => [\$ServicioID['data']]"),
                $metodo.'() no consulta el servicio con parametro enlazado');
        }
        $this->addCase($results, 'id_valido',
            substr_count($codigo, 'isValidDecrypted(') === 4,
            'se esperan 4 validaciones de ID descifrado (View, Resumen, ResumenUpdate e Insert)');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                               RUTAS                                         */
    /******************************************************************************/
    /**
     * Coteja las 10 rutas del listado publicadas por el instalador: handlers
     * existentes, verbo y nivel esperado de cada una, prefijo
     * `administracion/servicios/listado`, tablas, permiso (tipo Resumen) y
     * cobertura de la desinstalacion. No instala ni desinstala nada.
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
            'getRouteDefinitions() no devolvio ninguna ruta');

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
        // Endpoints esperados: verbo + ruta => [handler, nivel]
        /*====================================*/
        $esperadas = [
            'GET '.self::RUTA_WEB_LISTADO            => ['listAll',       1],
            'POST '.self::RUTA_WEB_SEARCH            => ['UpdateList',    1],
            'GET '.self::RUTA_WEB.'/updateList'      => ['UpdateList',    2],
            'GET '.self::RUTA_WEB.'/view/@id'        => ['View',          1],
            'GET '.self::RUTA_WEB.'/resumen/@id'     => ['Resumen',       2],
            'GET '.self::RUTA_WEB.'/resumenUpdate/@id' => ['ResumenUpdate', 2],
            'POST '.self::RUTA_WEB                   => ['Insert',        3],
            'POST '.self::RUTA_WEB.'/update'         => ['Update',        2],
            'PUT '.self::RUTA_WEB.'/delFiles'        => ['DelFiles',      2],
            'DELETE '.self::RUTA_WEB                 => ['Delete',        4],
        ];
        foreach ($esperadas as $clave => [$handler, $nivel]) {
            $this->addCase($results, 'ruta_publicada_'.md5($clave), isset($arrRutas[$clave]),
                'el instalador no publica la ruta '.$clave);
            $this->addCase($results, 'ruta_contrato_'.md5($clave),
                ($arrRutas[$clave]['handler'] ?? '') === self::CONTROLADOR.'->'.$handler
                && ($arrRutas[$clave]['nivel'] ?? 0) === $nivel,
                'la ruta '.$clave.' debe apuntar a '.self::CONTROLADOR.'->'.$handler.' con idLevelLimit '.$nivel);
        }
        $this->addCase($results, 'rutas_sin_ruta_getID', !isset($arrRutas['GET '.self::RUTA_WEB.'/getID/@id']),
            'el listado edita desde Resumen: no deberia publicar getID');

        /*====================================*/
        // Las rutas de los sub-recursos las gestionan otros controladores
        /*====================================*/
        $subRecursos = ['observaciones' => 'serviciosListadoObservaciones', 'documentos' => 'serviciosListadoDocumentos'];
        foreach ($subRecursos as $segmento => $controlador) {
            $claves = array_filter(array_keys($arrRutas), static fn($c) => str_contains($c, self::RUTA_WEB.'/'.$segmento));
            $propios = array_filter($claves, fn($c) => $arrRutas[$c]['controller'] === $controlador);
            $this->addCase($results, 'subrecurso_'.$segmento, count($claves) > 0 && count($claves) === count($propios),
                'las rutas '.$segmento.' deben pertenecer a '.$controlador);
        }

        /*====================================*/
        // RutaController() cubre todos los controladores con rutas
        /*====================================*/
        $lista = $this->listaControladoresInstalador();
        foreach (self::CONTROLADORES as $controlador) {
            $this->addCase($results, 'desinstalacion_cubre_'.$controlador, in_array($controlador, $lista, true),
                'RutaController() no incluye '.$controlador.' (dejaria rutas huerfanas)');
        }
        $this->addCase($results, 'instalador_cuatro_controladores', count($lista) === 4,
            'RutaController() declara '.count($lista).' controladores (esperados 4)');

        /*====================================*/
        // Tablas del instalador
        /*====================================*/
        try {
            $instalador = (new ReflectionClass('serviciosInstaller'))->newInstanceWithoutConstructor();
            $tablas     = (array)(new ReflectionMethod('serviciosInstaller', 'listTables'))->invoke($instalador);
        } catch (Throwable $e) {
            $tablas = [];
        }
        $mapaTablas = array_column($tablas, null, 'table');
        $this->addCase($results, 'tabla_servicios_listado', isset($mapaTablas['servicios_listado']),
            'listTables() no crea la tabla servicios_listado');
        $this->addCase($results, 'tabla_clave_primaria',
            ($mapaTablas['servicios_listado']['primaryKey'] ?? '') === 'idServicio',
            'servicios_listado no declara idServicio como clave primaria');
        $columnas = (string)($mapaTablas['servicios_listado']['data'] ?? '');
        foreach (['idEstado', 'idCategoria', 'Nombre', 'ValorIngreso', 'ValorEgreso', 'Descripcion', 'Codigo', 'Direccion_img'] as $columna) {
            $this->addCase($results, 'tabla_columna_'.$columna, str_contains($columnas, '`'.$columna.'`'),
                'servicios_listado no declara la columna '.$columna.' que usa el controlador');
        }
        foreach (['servicios_listado_documentos', 'servicios_listado_observaciones'] as $dependiente) {
            $this->addCase($results, 'tabla_dependiente_'.$dependiente,
                isset($mapaTablas[$dependiente]) && str_contains((string)$mapaTablas[$dependiente]['data'], '`idServicio`'),
                $dependiente.' debe existir y enlazar con idServicio (Delete() la limpia)');
        }
        $this->addCase($results, 'tabla_documentos_archivo',
            str_contains((string)($mapaTablas['servicios_listado_documentos']['data'] ?? ''), '`NombreArchivo`'),
            'servicios_listado_documentos no declara NombreArchivo (columna de archivo que borra Delete())');

        /*====================================*/
        // Permiso instalado para el controlador
        /*====================================*/
        $instalador = $this->fuenteInstalador();
        $this->addCase($results, 'rutas_de_permiso_instaladas',
            str_contains($instalador, "'RutaController' => '".self::CONTROLADOR."'"),
            'el instalador no crea el permiso con RutaController='.self::CONTROLADOR);
        $this->addCase($results, 'permiso_ruta_web_instalada',
            str_contains($instalador, "'RutaWeb'        => '".self::RUTA_WEB."'"),
            'el instalador no crea el permiso con RutaWeb='.self::RUTA_WEB);
        $this->addCase($results, 'permiso_tipo_crud_resumen',
            (bool)preg_match("/'idTipo'\s*=>\s*'2'[^\]]*'RutaController'\s*=>\s*'".self::CONTROLADOR."'/s", $instalador),
            'el permiso de '.self::CONTROLADOR.' no es de tipo 2 (CRUD Resumen)');
        $this->addCase($results, 'permiso_nivel_maximo_4',
            (bool)preg_match("/'idLevelLimit'\s*=>\s*'4'[^\]]*'RutaController'\s*=>\s*'".self::CONTROLADOR."'/s", $instalador),
            'el permiso de '.self::CONTROLADOR.' no declara idLevelLimit 4');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                 PERMISOS                                    */
    /******************************************************************************/
    /**
     * Verifica la sesion del usuario, el nivel de acceso del listado y el
     * registro de las rutas en `SESSION.arrPermisos` y en el router de F3.
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
        // Las rutas deben existir en el router de F3 con el verbo esperado
        /*====================================*/
        $arrRoutes = $f3->get('ROUTES');
        $this->addCase($results, 'router_disponible', is_array($arrRoutes),
            'F3 no expuso la variable ROUTES');

        $esquema = [
            'ruta_en_router'         => ['/'.self::RUTA_WEB_LISTADO,                  'GET',    self::CONTROLADOR.'->listAll'],
            'ruta_busqueda_router'   => ['/'.self::RUTA_WEB_SEARCH,                   'POST',   self::CONTROLADOR.'->UpdateList'],
            'ruta_resumen_router'    => ['/'.self::RUTA_WEB.'/resumen/@id',           'GET',    self::CONTROLADOR.'->Resumen'],
            'ruta_insert_router'     => ['/'.self::RUTA_WEB,                          'POST',   self::CONTROLADOR.'->Insert'],
            'ruta_delfiles_router'   => ['/'.self::RUTA_WEB.'/delFiles',              'PUT',    self::CONTROLADOR.'->delFiles'],
            'ruta_borrar_router'     => ['/'.self::RUTA_WEB,                          'DELETE', self::CONTROLADOR.'->Delete'],
        ];
        foreach ($esquema as $caso => $rutaEsperada) {
            [$ruta, $verbo, $handlerEsperado] = $rutaEsperada;
            if (!is_array($arrRoutes) || !isset($arrRoutes[$ruta][0][$verbo])) {
                continue;
            }
            $handler = $arrRoutes[$ruta][0][$verbo][0] ?? '';
            // PHP resuelve los metodos sin distinguir mayusculas (el instalador publica DelFiles)
            $this->addCase($results, $caso, strcasecmp((string)$handler, $handlerEsperado) === 0,
                'la ruta '.$verbo.' '.$ruta.' apunta a '.var_export($handler, true).' en vez de '.$handlerEsperado);
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /**
     * Renderiza `serviciosListado-List.php` con datos sinteticos y verifica
     * la matriz de permisos (ver >=1, editar >=2, crear >=3, borrar >=4),
     * IDs cifrados y escape de las acciones de la tabla.
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
        // Estructura de la pagina (nivel 1, primer render: encadena
        // formSearch, UpdateList y formNew por require_once)
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
        $this->addCase($results, 'listado_titulo', str_contains($html, 'Listado de Servicios'),
            'no se muestra el titulo de la tabla');
        foreach (['Categoria', 'Nombre', 'Estado', 'Acciones'] as $columna) {
            $this->addCase($results, 'listado_columna_'.$columna, str_contains($html, '>'.$columna.'</th>'),
                'la tabla no declara la columna '.$columna);
        }
        $this->addCase($results, 'listado_fila_presente',
            str_contains($html, 'Servicio Prueba') && str_contains($html, 'Categoria Prueba'),
            'la fila sintetica no se pinta en la tabla');
        $this->addCase($results, 'listado_badge_estado',
            str_contains($html, 'badge-sp1-success') && str_contains($html, '>Activo<'),
            'no se pinta el estado con su color');
        $this->addCase($results, 'listado_endpoint_ver', str_contains($html, '/view/'),
            'falta el endpoint de detalle');
        $this->addCase($results, 'listado_endpoint_actualizar', str_contains($html, '/updateList'),
            'falta el endpoint de refresco de la tabla');
        $this->addCase($results, 'listado_endpoint_borrar',
            str_contains($html, "'DELETE'") && str_contains($html, '"idServicio": ID'),
            'el borrado no usa DELETE con idServicio');
        $this->addCase($results, 'listado_notificacion_borrado', str_contains($html, 'Dato Borrado Correctamente'),
            'el borrado no notifica el resultado al usuario');
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
            // Nombre con apostrofo: el escape con addslashes() debe notarse
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
            // La edicion enlaza a /resumen/<id cifrado> (no hay modal de edicion)
            $this->addCase($results, 'filas_editar_enlace_resumen_nivel'.$nivel,
                $esperado['editar'] === str_contains($htmlFila, '/resumen/'),
                'nivel '.$nivel.' '.($esperado['editar'] ? 'no enlaza' : 'enlaza').' al resumen para editar');
            // Los IDs viajan cifrados: se extraen los tokens y se exige que no sean el ID plano
            $tokens = [];
            if (preg_match_all("/listTable(?:DataView|DataDel)\\('([^']+)'/", $htmlFila, $c1)) {
                $tokens = array_merge($tokens, $c1[1]);
            }
            if (preg_match_all('#/resumen/([^"\']+)"#', $htmlFila, $c2)) {
                $tokens = array_merge($tokens, $c2[1]);
            }
            $idsOk = count($tokens) > 0;
            foreach ($tokens as $token) {
                if ($token === '1' || strlen($token) < 3) {
                    $idsOk = false;
                }
            }
            $this->addCase($results, 'filas_id_cifrado_nivel'.$nivel, $idsOk,
                'nivel '.$nivel.' pinta un ID sin cifrar en las acciones');
            $this->addCase($results, 'filas_escape_nivel'.$nivel,
                $nivel < 4
                    ? str_contains($htmlFila, "O'Higgins Prueba")
                    : str_contains($htmlFila, "O\\'Higgins"),
                'nivel '.$nivel.' '.($nivel < 4 ? 'no pinta el nombre en la celda' : 'no escapa el apostrofo del nombre con addslashes()'));
        }

        /*====================================*/
        // Lista vacia: solo la cabecera, sin acciones
        /*====================================*/
        $dataVacia            = $this->datosSinteticos(4);
        $dataVacia['arrList'] = [];
        $htmlVacio = $this->renderizarVista($rutaUpdateList, $dataVacia, $BASE, $f3);
        $this->addCase($results, 'filas_vacio_cabecera',
            str_contains($htmlVacio, '<thead>') && !str_contains($htmlVacio, '<td>'),
            'una lista vacia debe pintar la cabecera sin filas');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                             VISTAS DETALLE                                  */
    /******************************************************************************/
    /**
     * Renderiza `-View` (modal de lectura con pestanas), `-Resumen` (pestanas
     * de edicion, imagen, documentos y observaciones) y `-Resumen-Update`
     * (ficha), con y sin permiso de documentos y con/sin imagen.
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
        // Encadenado por fuente (los require_once no se re-ejecutan)
        /*====================================*/
        $this->addCase($results, 'view_incluye_ficha',
            str_contains($this->fuenteVista('View'), "require_once('serviciosListado-Resumen-Update.php')"),
            'View no incluye la ficha Resumen-Update');
        $this->addCase($results, 'resumen_incluye_ficha',
            str_contains($this->fuenteVista('Resumen'), "require_once('serviciosListado-Resumen-Update.php')"),
            'Resumen no incluye la ficha Resumen-Update');

        /*====================================*/
        // Vista View con permiso de documentos activo (primer render)
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'),
            $this->datosSinteticos(2, true), $BASE, $f3);

        $this->addCase($results, 'view_render', $html !== '', 'la vista View no produjo HTML');
        $this->addCase($results, 'view_titulo', str_contains($html, 'Ver Datos'),
            'el modal no muestra el titulo Ver Datos');
        $this->addCase($results, 'view_pestana_datos', str_contains($html, 'id="view_tab_1"'),
            'falta la pestana de datos basicos');
        $this->addCase($results, 'view_pestana_documentos', str_contains($html, 'id="view_tab_4"'),
            'con permiso de documentos no se muestra la pestana Documentos');
        $this->addCase($results, 'view_pestana_observaciones', str_contains($html, 'id="view_tab_5"'),
            'falta la pestana Observaciones');
        $this->addCase($results, 'view_endpoint_documentos', str_contains($html, '/documentos/view/'),
            'falta el endpoint de vista de documentos');
        $this->addCase($results, 'view_documento_presente',
            str_contains($html, 'Contrato Prueba') && str_contains($html, 'Ver Archivo'),
            'el documento sintetico no se pinta con su accion');
        $this->addCase($results, 'view_vencimiento_presente', str_contains($html, '2026-12-31'),
            'no se pinta la fecha de vencimiento del documento');
        $this->addCase($results, 'view_documento_id_cifrado',
            (bool)preg_match('/onclick="viewDocumentosView\(\'(?!1\')[^\']{3,}\'\)"/', $html),
            'el documento se abre con un ID sin cifrar');
        $this->addCase($results, 'view_observacion_presente',
            str_contains($html, 'Observacion de prueba') && str_contains($html, 'Tester'),
            'la observacion sintetica no se pinta con su autor');
        $this->addCase($results, 'view_ficha_resumen',
            str_contains($html, 'Valor Ingreso') && str_contains($html, 'Servicio Prueba')
            && str_contains($html, 'SRV-001') && str_contains($html, 'Categoria Prueba'),
            'la ficha de Resumen-Update no se incluye en el modal');
        $this->addCase($results, 'view_imagen_por_defecto', str_contains($html, 'img/picture-img.jpg'),
            'sin imagen personalizada no se usa la imagen por defecto');
        $this->addCase($results, 'view_boton_cerrar', str_contains($html, 'data-bs-dismiss="modal"'),
            'falta el pie con el boton Cerrar');

        /*====================================*/
        // Vista View sin permiso de documentos y con listas vacias
        /*====================================*/
        $dataSinDoc                     = $this->datosSinteticos(2, false);
        $dataSinDoc['arrDocumentos']    = [];
        $dataSinDoc['arrObservaciones'] = [];
        $htmlSinDoc = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'),
            $dataSinDoc, $BASE, $f3);
        $this->addCase($results, 'view_sin_pestana_documentos',
            !str_contains($htmlSinDoc, 'id="view_tab_4"') && !str_contains($htmlSinDoc, 'id="tab_id_4"'),
            'sin permiso de documentos se muestra la pestana Documentos');
        $this->addCase($results, 'view_sin_accion_documento', !str_contains($htmlSinDoc, 'Ver Archivo'),
            'sin permiso de documentos se pinta la accion de ver archivo');
        $this->addCase($results, 'view_observaciones_siempre', str_contains($htmlSinDoc, 'id="view_tab_5"'),
            'las observaciones no dependen del permiso de documentos');
        $this->addCase($results, 'view_sin_entradas', str_contains($htmlSinDoc, 'No se encontraron entradas'),
            'una lista vacia debe indicar que no hay entradas');

        /*====================================*/
        // Vista Resumen con permiso de documentos y sin imagen
        /*====================================*/
        $rutaResumen = $this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen.php');
        $htmlResumen = $this->renderizarVista($rutaResumen, $this->datosSinteticos(2, true), $BASE, $f3);

        $this->addCase($results, 'resumen_render', $htmlResumen !== '', 'la vista Resumen no produjo HTML');
        foreach (['#resumen"' => 'Resumen', '#resumen-edit"' => 'Editar Datos', '#resumen-img"' => 'Cambiar Imagen',
                  '#resumen-documentos"' => 'Documentos', '#resumen-obs"' => 'Observaciones'] as $destino => $pestana) {
            $this->addCase($results, 'resumen_pestana_'.md5($destino), str_contains($htmlResumen, 'data-bs-target="'.$destino),
                'falta la pestana '.$pestana);
        }
        $this->addCase($results, 'resumen_form_edicion',
            str_contains($htmlResumen, 'id="FormEditData"') && str_contains($htmlResumen, 'Edit_Nombre')
            && str_contains($htmlResumen, 'Edit_idCategoria') && str_contains($htmlResumen, 'Edit_Codigo')
            && str_contains($htmlResumen, 'Edit_Descripcion') && str_contains($htmlResumen, 'Edit_ValorIngreso')
            && str_contains($htmlResumen, 'Edit_ValorEgreso'),
            'el formulario de edicion no declara todos sus campos');
        $this->addCase($results, 'resumen_estado_editable',
            str_contains($htmlResumen, 'name="idEstado"'),
            'el formulario de edicion no permite cambiar el estado');
        $this->addCase($results, 'resumen_valores_actuales',
            str_contains($htmlResumen, 'Servicio Prueba') && str_contains($htmlResumen, 'SRV-001')
            && str_contains($htmlResumen, 'Descripcion de prueba'),
            'el formulario de edicion no se pinta con los valores actuales');
        $this->addCase($results, 'resumen_id_oculto', str_contains($htmlResumen, 'name="idServicio"'),
            'el formulario de edicion no transporta el idServicio oculto');
        $this->addCase($results, 'resumen_endpoint_update',
            str_contains($htmlResumen, '/update') && str_contains($htmlResumen, "'POST'"),
            'el formulario de edicion no apunta a /update con POST');
        $this->addCase($results, 'resumen_endpoint_resumen_update', str_contains($htmlResumen, '/resumenUpdate/'),
            'falta el endpoint de refresco tras editar');
        $this->addCase($results, 'resumen_notificacion_edicion', str_contains($htmlResumen, 'Datos Editados Correctamente'),
            'la edicion no notifica el resultado al usuario');
        $this->addCase($results, 'resumen_endpoint_delfiles',
            str_contains($htmlResumen, '/delFiles') && str_contains($htmlResumen, "'PUT'"),
            'falta el endpoint PUT de borrado de la imagen');
        $this->addCase($results, 'resumen_carga_imagen',
            str_contains($htmlResumen, '"idServicio": 1,"idEstado": 1,"idCategoria": 1,"Nombre": "Servicio Prueba"')
            && str_contains($htmlResumen, 'Direccion_img'),
            'sin imagen no se ofrece subirla a /update con el contexto del servicio');
        $this->addCase($results, 'resumen_sin_borrar_imagen', !str_contains($htmlResumen, 'Borrar Imagen</button>'),
            'sin imagen no deberia ofrecerse borrarla');
        $this->addCase($results, 'resumen_subrecursos_observaciones',
            str_contains($htmlResumen, 'tabObsNew(') && str_contains($htmlResumen, '/observaciones/updateList/')
            && str_contains($htmlResumen, '/observaciones/new/') && str_contains($htmlResumen, '/observaciones/view/')
            && str_contains($htmlResumen, '/observaciones/getID/'),
            'el resumen no publica las acciones de observaciones');
        $this->addCase($results, 'resumen_subrecursos_documentos',
            str_contains($htmlResumen, 'tabDocumentosLoadList') && str_contains($htmlResumen, '/documentos/updateList/')
            && str_contains($htmlResumen, 'tabDocumentosDataTable') && str_contains($htmlResumen, '/documentos/new/')
            && str_contains($htmlResumen, '/documentos/view/') && str_contains($htmlResumen, '/documentos/getID/'),
            'el resumen no publica las acciones de documentos');
        $this->addCase($results, 'resumen_id_cifrado_subrecursos',
            !preg_match("/tab(?:Obs|Documentos)New\\('1'\\)/", $htmlResumen)
            && (bool)preg_match("/tabObsNew\\('[^']{3,}'\\)/", $htmlResumen),
            'las acciones de sub-recursos reciben el ID sin cifrar');
        $this->addCase($results, 'resumen_volver_listado',
            str_contains($htmlResumen, 'Volver') && str_contains($htmlResumen, '/listAll'),
            'falta el enlace de vuelta al listado');
        $this->addCase($results, 'resumen_titulo_imagen', str_contains($htmlResumen, 'Imagen de Servicio Prueba'),
            'la pestana de imagen no nombra al servicio');
        $this->addCase($results, 'resumen_no_depende_del_nivel',
            !str_contains($this->fuenteVista('Resumen'), 'LevelAccess'),
            'Resumen deberia depender solo del nivel de la ruta (2), no de LevelAccess');

        /*====================================*/
        // Resumen sin permiso de documentos y con imagen
        /*====================================*/
        $htmlResumenImg = $this->renderizarVista($rutaResumen,
            $this->datosSinteticos(2, false, 'servicio_prueba.jpg'), $BASE, $f3);
        $this->addCase($results, 'resumen_sin_pestana_documentos',
            !str_contains($htmlResumenImg, 'data-bs-target="#resumen-documentos"')
            && !str_contains($htmlResumenImg, 'id="resumen-documentos"')
            && !str_contains($htmlResumenImg, 'function tabDocumentosLoadList'),
            'sin permiso de documentos se publica la pestana o las funciones de Documentos');
        $this->addCase($results, 'resumen_observaciones_siempre',
            str_contains($htmlResumenImg, 'id="resumen-obs"'),
            'las observaciones no dependen del permiso de documentos');
        $this->addCase($results, 'resumen_imagen_propia',
            str_contains($htmlResumenImg, '/admin/public/upload/servicio_prueba.jpg')
            && str_contains($htmlResumenImg, "delIMG('servicio_prueba.jpg')"),
            'con imagen no se pinta desde MainPathUrl ni se ofrece borrarla');
        $this->addCase($results, 'resumen_con_imagen_sin_subida',
            !str_contains($htmlResumenImg, '"idCategoria": 1,"Nombre"'),
            'con imagen no deberia ofrecerse la subida (primero se borra la actual)');

        /*====================================*/
        // Resumen-Update directo: ficha con y sin imagen
        /*====================================*/
        $rutaRU = $this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen-Update.php');
        $htmlRU = $this->renderizarVista($rutaRU, $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'resumen_update_render', $htmlRU !== '', 'la vista Resumen-Update no produjo HTML');
        $this->addCase($results, 'resumen_update_imagen_default', str_contains($htmlRU, 'img/picture-img.jpg'),
            'sin imagen no se usa la imagen por defecto');
        $this->addCase($results, 'resumen_update_badge_estado', str_contains($htmlRU, 'badge-sp1-success'),
            'no se pinta el badge del estado');
        foreach (['Categoria', 'Nombre', 'Valor Ingreso', 'Valor Egreso', 'Codigo', 'Estado', 'Descripcion'] as $campo) {
            $this->addCase($results, 'resumen_update_campo_'.md5($campo), str_contains($htmlRU, $campo),
                'la ficha no muestra el campo '.$campo);
        }
        $this->addCase($results, 'resumen_update_valores',
            str_contains($htmlRU, '1500.50') && str_contains($htmlRU, '1000.00') && str_contains($htmlRU, 'SRV-001'),
            'la ficha no muestra los valores y el codigo del servicio');

        $htmlRUImg = $this->renderizarVista($rutaRU, $this->datosSinteticos(2, true, 'servicio_prueba.jpg'), $BASE, $f3);
        $this->addCase($results, 'resumen_update_imagen_propia',
            str_contains($htmlRUImg, '/admin/public/upload/servicio_prueba.jpg'),
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
     * campos, estado oculto, endpoint y verbo de envio.
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
        $this->addCase($results, 'form_new_titulo', str_contains($htmlNew, 'Crear Nuevo'),
            'el modal de alta no muestra su titulo');
        $this->addCase($results, 'form_new_campo_categoria',
            str_contains($htmlNew, 'name="idCategoria"') && str_contains($htmlNew, 'Categoria Prueba'),
            'el formulario de alta no ofrece las categorias disponibles');
        $this->addCase($results, 'form_new_campo_nombre', str_contains($htmlNew, 'name="Nombre"'),
            'el formulario de alta no declara el campo Nombre');
        $this->addCase($results, 'form_new_estado_oculto_activo',
            (bool)preg_match('/name="idEstado"[^>]*value="1"|value="1"[^>]*name="idEstado"/', $htmlNew),
            'el alta no fija el estado oculto en 1 (Activo)');
        $this->addCase($results, 'form_new_envio_a_resumen',
            str_contains($htmlNew, "'POST'") && str_contains($htmlNew, '/resumen/'),
            'el alta no envia por POST ni redirige al resumen del servicio creado');
        $this->addCase($results, 'form_new_limpia_formulario', str_contains($htmlNew, "ClearForm:'FormNewData'"),
            'el alta no limpia el formulario tras guardar');

        /*====================================*/
        // Formulario de busqueda
        /*====================================*/
        $htmlSearch = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-formSearch.php'),
            $this->datosSinteticos(1), $BASE, $f3);

        $this->addCase($results, 'form_search_render', $htmlSearch !== '', 'la vista formSearch no produjo HTML');
        $this->addCase($results, 'form_search_formulario', str_contains($htmlSearch, 'id="FormSearchData"'),
            'el formulario de busqueda no declara su identificador');
        foreach (['Search_idCategoria', 'Search_Nombre', 'Search_Codigo', 'Search_idEstado'] as $campo) {
            $this->addCase($results, 'form_search_'.$campo, str_contains($htmlSearch, 'id="'.$campo.'"'),
                'el formulario de busqueda no declara el campo '.$campo);
        }
        $this->addCase($results, 'form_search_estados', str_contains($htmlSearch, '>Activo<'),
            'el filtro de estado no ofrece los estados disponibles');
        $this->addCase($results, 'form_search_endpoint',
            str_contains($htmlSearch, '/search') && str_contains($htmlSearch, "'POST'"),
            'el formulario de busqueda no apunta a /search con POST');
        $this->addCase($results, 'form_search_actualiza_tabla', str_contains($htmlSearch, "UpdateDivFrom : 'listTableData'"),
            'el filtro no refresca la tabla del listado');
        $this->addCase($results, 'form_search_quitar_filtro', str_contains($htmlSearch, 'deleteFilter('),
            'el formulario de busqueda no ofrece quitar el filtro');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos el chequeo de datos (`dataCheck` pasado por
     * `CheckData`), el cifrado de identificadores y la validacion de IDs
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
            // la conexion a base de datos.
            $Controller = $this->instanciaSinBD();

            $Post      = $this->postValido();
            $DataCheck = $reflector->invoke($Controller, $Post);

            $this->addCase($results, 'datacheck_es_arreglo', is_array($DataCheck),
                'dataCheck() no devolvio un arreglo');
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post,
                'dataCheck() no conserva el POST recibido');
            $this->addCase($results, 'datacheck_sin_obligatorios', (string)($DataCheck['emptyData'] ?? 'x') === '',
                'dataCheck() delega los obligatorios en Base_insert/Base_update (emptyData vacio)');
            $this->addCase($results, 'datacheck_numericos',
                (string)($DataCheck['ValidarNumero'] ?? '') === 'idEstado,idCategoria,ValorIngreso,ValorEgreso',
                'dataCheck() no valida ids y valores como numeros');
            $this->addCase($results, 'datacheck_enteros',
                (string)($DataCheck['ValidarEntero'] ?? '') === 'idEstado,idCategoria',
                'dataCheck() no valida idEstado e idCategoria como enteros');
            $this->addCase($results, 'datacheck_largo',
                (string)($DataCheck['ValidarLargoMinimo'] ?? '') === 'Nombre,Descripcion,Codigo'
                && (int)($DataCheck['ValidarLargoMinimoN'] ?? 0) === 3
                && ($DataCheck['ValidarLargoMaximoPorCampo'] ?? []) === [
                    'Nombre' => 255,
                    'Codigo' => 255,
                ],
                'dataCheck() no fija correctamente todos los campos de largo (minimo/maximo)');
            $this->addCase($results, 'datacheck_censura',
                (string)($DataCheck['ValidarPalabrasCensuradas'] ?? '') === 'Nombre,Descripcion,Codigo',
                'dataCheck() no censura palabras en Nombre, Descripcion y Codigo');
            foreach (['ValidarEmail', 'ValidarRut', 'ValidarPatente', 'ValidarFecha', 'ValidarHora', 'ValidarURL',
                      'ValidarEspaciosVacios', 'ValidarMayusculas', 'ValidarCoincidencias', 'ValidarDominioEmail',
                      'ValidarPasswordSegura', 'ValidarFechaRango', 'ValidarEdadMinima', 'ValidarJSON', 'ValidarUUID',
                      'ValidarIP', 'ValidarSoloAlfanumerico', 'ValidarSoloLetras', 'encode'] as $inactiva) {
                $this->addCase($results, 'datacheck_inactiva_'.$inactiva, (string)($DataCheck[$inactiva] ?? 'x') === '',
                    'dataCheck() no debe activar '.$inactiva.' para servicios');
            }
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_es_arreglo', false,
                'no se pudo ejecutar dataCheck() ('.$e->getMessage().')');
        }

        /*====================================*/
        // Validaciones reales: dataCheck + CheckData (sin BD)
        /*====================================*/
        try {
            $ok = $this->validarPost($this->postValido());
            $this->addCase($results, 'valida_post_correcto', ($ok['status'] ?? false) === true,
                'un servicio sintetico valido fue rechazado: '.json_encode($ok['error'] ?? []));

            // Casos invalidos: campo => valor que debe ser rechazado
            $invalidos = [
                'estado_no_numerico'     => ['idEstado', 'abc'],
                'estado_decimal'         => ['idEstado', '1.5'],
                'categoria_no_numerica'  => ['idCategoria', 'abc'],
                'categoria_decimal'      => ['idCategoria', '2.5'],
                'ingreso_no_numerico'    => ['ValorIngreso', 'mucho'],
                'egreso_no_numerico'     => ['ValorEgreso', 'poco'],
                'nombre_muy_corto'       => ['Nombre', 'Al'],
                'nombre_muy_largo'       => ['Nombre', str_repeat('a', 256)],
                'codigo_muy_corto'       => ['Codigo', 'AB'],
                'codigo_muy_largo'       => ['Codigo', str_repeat('b', 256)],
                'descripcion_muy_corta'  => ['Descripcion', 'ab'],
            ];
            foreach ($invalidos as $caso => [$campo, $valor]) {
                $post         = $this->postValido();
                $post[$campo] = $valor;
                $r            = $this->validarPost($post);
                $this->addCase($results, 'rechaza_'.$caso, ($r['status'] ?? true) === false,
                    'un '.$campo.' invalido ('.substr((string)$valor, 0, 12).') fue aceptado');
            }

            // Limites validos: el borde se acepta
            $limites = [
                'nombre_largo_minimo'  => ['Nombre', 'Abc'],
                'nombre_largo_maximo'  => ['Nombre', str_repeat('a', 255)],
                'codigo_largo_maximo'  => ['Codigo', str_repeat('b', 255)],
                'ingreso_cero'         => ['ValorIngreso', '0'],
                'egreso_decimal'       => ['ValorEgreso', '99.99'],
            ];
            foreach ($limites as $caso => [$campo, $valor]) {
                $post         = $this->postValido();
                $post[$campo] = $valor;
                $r            = $this->validarPost($post);
                $this->addCase($results, 'acepta_'.$caso, ($r['status'] ?? false) === true,
                    'un '.$campo.' valido en el limite fue rechazado: '.json_encode($r['error'] ?? []));
            }
        } catch (Throwable $e) {
            $this->addCase($results, 'valida_post_correcto', false,
                'no se pudo ejecutar CheckData ('.$e->getMessage().')');
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
            $this->addCase($results, 'cifrado_oculta_id', (string)($cifrado['data'] ?? '') !== '42',
                'el valor cifrado coincide con el ID plano');

            $descifrado = $Codification->encryptDecrypt('decrypt', (string)($cifrado['data'] ?? ''));
            $this->addCase($results, 'cifrado_ida_y_vuelta',
                ($descifrado['success'] ?? false) === true && (string)($descifrado['data'] ?? '') === '42',
                'el cifrado no devuelve el valor original');

            $basura = $Codification->encryptDecrypt('decrypt', 'valor-que-no-es-un-token');
            $this->addCase($results, 'cifrado_rechaza_token_ajeno',
                !(($basura['success'] ?? false) === true && $this->isValidDecrypted($basura, 'id')),
                'un token arbitrario no deberia pasar como ID valido');
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
     * Revisa estaticamente las 7 vistas propias y el controlador: ausencia de
     * rutas relativas maliciosas, de rutas absolutas del sistema de ficheros,
     * de acceso directo a la sesion, de SQL embebido y de concatenaciones de
     * superglobals en consultas; ademas del escape y cifrado antes de pintar.
     *
     * @return array Resultado por caso
     */
    public function seguridadVistasTest(){

        $results = [];

        /*====================================*/
        // Vistas propias (se excluyen las de Documentos y Observaciones)
        /*====================================*/
        $this->addCase($results, 'hay_vistas', count(self::VISTAS) === 7,
            'se esperan 7 vistas propias del controlador '.self::CONTROLADOR);

        foreach (self::VISTAS as $vista) {
            $nombre = self::CONTROLADOR.'-'.$vista.'.php';
            $codigo = $this->codigoFuente($this->rutaModulo('../views/'.$nombre));
            $this->addCase($results, 'vista_con_contenido_'.$nombre, $codigo !== '',
                'no se pudo leer la vista '.$nombre);

            // Las vistas encadenan con require_once('<vista>.php') relativo: se ignora ese patron
            $sinRequire = preg_replace("/require_once\\('[A-Za-z\\-]+\\.php'\\);/", '', $codigo);
            $this->addCase($results, 'vista_sin_retroceso_'.$nombre, !preg_match('#(?:\.\./|\.\.\\\\\\\\)#', $sinRequire),
                $nombre.' construye rutas con retroceso');
            $this->addCase($results, 'vista_sin_ruta_absoluta_'.$nombre,
                !preg_match('#(?:/var/www|/home/|[A-Za-z]:\\\\\\\\)#', $codigo),
                $nombre.' declara rutas absolutas del sistema de ficheros');
            $this->addCase($results, 'vista_sin_sesion_'.$nombre, !str_contains($codigo, '$_SESSION'),
                $nombre.' accede directamente a la sesion');
            $this->addCase($results, 'vista_sin_sql_'.$nombre,
                !preg_match('/\b(SELECT|INSERT|UPDATE|DELETE)\s+.+\bFROM\b/i', $codigo),
                $nombre.' embebe sentencias SQL');
            $this->addCase($results, 'vista_sin_superglobals_'.$nombre,
                !preg_match('/\$_(POST|GET|REQUEST|COOKIE|SERVER)/', $codigo),
                $nombre.' lee superglobals directamente');
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
        $this->addCase($results, 'controlador_consultas_parametrizadas',
            substr_count($codigo, 'idServicio = ?') >= 5,
            'las consultas por idServicio (View, Resumen, ResumenUpdate, documentos, observaciones) deben usar parametros');
        $this->addCase($results, 'controlador_sin_eval',
            !str_contains($codigo, 'eval(') && !str_contains($codigo, 'shell_exec(') && !str_contains($codigo, 'exec('),
            'el controlador usa eval o ejecucion de comandos');
        $this->addCase($results, 'controlador_sin_unlink_directo',
            !str_contains($codigo, 'unlink(') && !str_contains($codigo, 'file_put_contents('),
            'el controlador toca el disco directamente: debe delegar en Base_delete/Base_update');
        $this->addCase($results, 'controlador_sin_superglobals_en_consultas',
            substr_count($codigo, '$_POST') === 4,
            'solo Insert y Update (dataCheck y Post) deben leer $_POST directamente');

        /*====================================*/
        // Escape y cifrado antes de pintar
        /*====================================*/
        $vistaListado = $this->fuenteVista('UpdateList');
        $this->addCase($results, 'tabla_escape', str_contains($vistaListado, 'addslashes('),
            'UpdateList no escapa el nombre antes de pasarlo al JS de borrado');
        $this->addCase($results, 'tabla_id_cifrado',
            str_contains($vistaListado, "encryptDecrypt('encrypt'"),
            'UpdateList no cifra el ID antes de componer las acciones');
        $this->addCase($results, 'resumen_id_cifrado',
            str_contains($this->fuenteVista('Resumen'), "encryptDecrypt('encrypt'"),
            'Resumen no cifra el ID antes de componer las URL de los sub-recursos');
        $this->addCase($results, 'view_id_cifrado',
            str_contains($this->fuenteVista('View'), "encryptDecrypt('encrypt'"),
            'View no cifra el ID del documento antes de abrirlo');
        $this->addCase($results, 'view_fecha_estandar',
            str_contains($this->fuenteVista('View'), 'fechaEstandar('),
            'View no formatea la fecha de las observaciones con fechaEstandar()');

        /************************************/
        // Retorno los datos
        return $results;

    }

}
