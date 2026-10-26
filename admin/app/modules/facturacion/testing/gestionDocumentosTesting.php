<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class gestionDocumentosTesting
 *
 * Pruebas del modulo `facturacion` - controlador `gestionDocumentos` (documentos mercantiles: compras y ventas).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. Los textos de los casos
 *    correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Alcance: pruebas NO destructivas. `Insert()`, `Update()`, `Delete()`, `delFiles()` y los
 * metodos de logica de negocio `createDoc()`, `deleteDoc()` y `updateFact()` escriben en base de
 * datos (varias tablas, bodegas, transacciones) y terminan en `Response::*()` (`exit`) o consultan
 * la BD; NO se ejecutan: se verifican por reflexion y lectura del fuente. Solo se ejecuta logica
 * pura: `tsrxName()`, `TipoMov()`, `dataCheck_1..6()`, cifrado de IDs y la rama de `deleteDoc()` que
 * rechaza un ID invalido antes de tocar la base (con una conexion no nula para que jamas abra
 * transaccion).
 *
 * El controlador atiende dos tipos de documento con wrappers (`listAll_1`/`listAll_2`...): tipo 1 =
 * compras (permiso `gestionDocumentosCompras`) y tipo 2 = ventas (`gestionDocumentosVentas`); el tipo
 * 0 es el informe de busqueda (`informeDocumentos`).
 *
 * @see skills/testing/SKILL.md
 */
class gestionDocumentosTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR      = 'gestionDocumentos';
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
    private function datosSinteticos(int $nivel, string $ruta = self::RUTA_VENTAS, int $idTipo = 2): array{
        $fila = [
            'idFacturacion'        => 41,
            'N_Doc'                => 1001,
            'Documento'            => 'Factura',
            'Creacion_fecha'       => '2026-01-02',
            'idTipoEntidad'        => 2,
            'EntidadesNombre'      => '',
            'EntidadesApellido'    => '',
            'EntidadesRazonSocial' => 'Entidad Prueba SpA',
            'EntidadesNick'        => '',
            'EstadoColor'          => 'success',
            'EstadoPago'           => 'Pagado',
            'ValorTotal'           => 119000,
            'MontoPagado'          => 119000,
        ];
        return [
            'PageTitle'         => 'Listado Documentos',
            'PageDescription'   => 'Listado Documentos.',
            'PageAuthor'        => 'Pruebas',
            'PageKeywords'      => 'Pruebas',
            'TableTitle'        => 'Listado Documentos',
            'UserData'          => [
                'MainPathUrl'               => '/admin/public/upload/',
                'sistemaModalSubtitle'      => 1,
                'sistemaModalCloseBTN'      => 2,
                'UserType'                  => 2,
                'gestionDocumentosUsoBodega' => 2,
            ],
            'UserAccess'        => ['LevelAccess' => $nivel, 'RouteAccess' => $ruta],
            'idTipo'            => $idTipo,
            'Fnc_FormInputs'    => new UIFormInputs(),
            'Fnc_Codification'  => new FunctionsSecurityCodification(),
            'Fnc_DataDate'      => new FunctionsDataDate(),
            'Fnc_DataNumbers'   => new FunctionsDataNumbers(),
            'Fnc_ServerServer'  => new FunctionsServerServer(),
            'Fnc_CommonData'    => new FunctionsCommonData(),
            'Fnc_WidgetsCommon' => new UIWidgetsCommon(),
            'Fnc_WidgetsViews'  => new UIWidgetsViews(),
            'arrList'           => [$fila],
            'arrDocumentos'     => [['ID' => 1, 'Nombre' => 'Factura']],
            'arrEntidades'      => [['ID' => 1, 'Nombre' => 'Entidad Prueba SpA']],
            'arrEstadoPago'     => [['ID' => 1, 'Nombre' => 'Pagado']],
            'arrBodegas'        => [['ID' => 1, 'Nombre' => 'Bodega Prueba']],
            'arrProductos'      => [['ID' => 1, 'Nombre' => 'Producto Prueba']],
            'arrServicios'      => [['ID' => 1, 'Nombre' => 'Servicio Prueba']],
            'arrGuias'          => [['idFacturacion' => 40, 'N_Doc' => 900, 'Creacion_fecha' => '2026-01-01', 'idEntidad' => 1]],
            'arrItems'          => [],
            'arrPagos'          => [],
            'rowData'           => array_merge($fila, [
                'idTipo'            => $idTipo,
                'idDocumentos'      => 1,
                'idEntidad'         => 1,
                'idEstadoPago'      => 1,
                'fecha_auto'        => '2026-01-02',
                'Creacion_hora'     => '10:00:00',
                'Observaciones'     => 'Observacion de prueba',
                'ValorNeto'         => 100000,
                'IVA'               => 19000,
                'TipoFacturacion'   => $idTipo == 1 ? 'Compras' : 'Ventas',
                'Usuario'           => 'Tester',
                'BodegaIngreso'     => 'Bodega Prueba',
                'BodegaEgreso'      => 'Bodega Prueba',
                'EntidadesDireccion' => 'Calle Falsa 123',
                'EntidadesComuna'   => 'Providencia',
                'EntidadesCiudad'   => 'Santiago',
                'EntidadesEmail'    => 'entidad@example.test',
            ]),
        ];
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
     * Verifica los archivos del controlador `gestionDocumentos` y la convencion
     * "clase = nombre de archivo" exigida por el AUTOLOAD de Fat-Free. Esta suite
     * completa el stub vacio `gestionDocumentosTesting.php` (ya coincidia con el controlador).
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
            'instalador'           => $this->rutaModulo('../installer/gestionDocumentosInstaller.php'),
            'vista_list'           => $this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php'),
            'vista_update_list'    => $this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'),
            'vista_view'           => $this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'),
            'vista_print'          => $this->rutaModulo('../views/'.self::CONTROLADOR.'-Print.php'),
            'vista_resumen'        => $this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen.php'),
            'vista_resumen_update' => $this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen-Update.php'),
            'vista_form_new'       => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formNew.php'),
            'vista_form_search'    => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formSearch.php'),
            'testing'              => $this->rutaModulo('gestionDocumentosTesting.php'),
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
        // Convencion clase = nombre de archivo y herencias
        /*====================================*/
        $this->addCase($results, 'clase_controlador_cargada', class_exists(self::CONTROLADOR),
            'la clase '.self::CONTROLADOR.' no esta disponible');
        $this->addCase($results, 'clase_testing_cargada', class_exists('gestionDocumentosTesting'),
            'la clase gestionDocumentosTesting no esta disponible');
        $this->addCase($results, 'clase_instalador_cargada', class_exists(self::INSTALADOR),
            'la clase '.self::INSTALADOR.' no esta disponible');
        $padre = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padre === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padre.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('gestionDocumentosTesting') === 'ControllerBase',
            'el testing no extiende ControllerBase');

        /*====================================*/
        // Constructor del controlador
        /*====================================*/
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'constructor_conexion', str_contains($codigo, 'Database::getSQLConnection(ConfigDataBase::MySQL_1)'),
            'el constructor no obtiene la conexion MySQL_1');
        $this->addCase($results, 'constructor_padre', str_contains($codigo, 'parent::__construct($DB_conn_1, $queryBuilder, $checkData)'),
            'el constructor no delega en ControllerBase');
        $this->addCase($results, 'constructor_nombre', str_contains($codigo, "\$this->controllerName    = '".self::CONTROLADOR."'"),
            'controllerName no coincide con '.self::CONTROLADOR);

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                            CONTROLADOR: VISTAS                             */
    /******************************************************************************/
    /**
     * Verifica los wrappers por tipo (`listAll_1/2`, `View_0/1/2`, `Print_*`, `noPrint_*`...)
     * y el contrato de los metodos base que devuelven HTML (`listAll`, `UpdateList`, `View`,
     * `Print`, `Resumen`, `ResumenUpdate`).
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Metodos base: firma y tipo de vista que sirven
        /*====================================*/
        $this->firmaMetodo($results, 'listAll', 2, 2);
        $this->firmaMetodo($results, 'UpdateList', 2, 2);
        $this->firmaMetodo($results, 'View', 3, 3);
        $this->firmaMetodo($results, 'Print', 4, 4);
        $this->firmaMetodo($results, 'Resumen', 3, 3);
        $this->firmaMetodo($results, 'ResumenUpdate', 3, 3);

        $vistas = [
            'List'          => ['listAll', '1'],
            'UpdateList'    => ['UpdateList', '2'],
            'View'          => ['View', '2'],
            'Print'         => ['Print', '4'],
            'Resumen'       => ['Resumen', '1'],
            'Resumen-Update' => ['ResumenUpdate', '2'],
        ];
        foreach ($vistas as $archivo => $datos) {
            [$metodo, $tipo] = $datos;
            $this->addCase($results, 'vista_'.$metodo,
                (bool)preg_match("/showVista\(".$tipo.",\s*\\\$this->returnRutaVista\(__DIR__, 'app'\)\.'\/'\.\\\$this->controllerName\.'-".preg_quote($archivo, '/')."\.php'\)/", $codigo),
                $metodo.'() no sirve la vista '.$archivo.' con showVista('.$tipo.')');
            $this->addCase($results, 'vista_existe_'.$metodo, is_file($this->rutaModulo('../views/'.self::CONTROLADOR.'-'.$archivo.'.php')),
                'no existe el archivo de la vista '.self::CONTROLADOR.'-'.$archivo);
        }
        $this->addCase($results, 'rutavista_resuelta', substr_count($codigo, 'returnRutaVista(__DIR__') === 6,
            'las 6 vistas deben resolver su ruta con returnRutaVista()');
        $this->addCase($results, 'errores_showerror', substr_count($codigo, 'showError(') === 6,
            'las 6 vistas deben manejar el fallo de consulta con showError()');
        $this->addCase($results, 'fallback_mergeresponses', substr_count($codigo, 'mergeResponses(') === 6,
            'las 6 vistas deben agrupar respuestas con mergeResponses()');
        $this->addCase($results, 'nivel_en_vistas', substr_count($codigo, 'getArrLevel(') === 6,
            'las 6 vistas deben resolver el nivel de acceso con getArrLevel()');
        $this->addCase($results, 'nivel_por_tipo_tsrx', substr_count($codigo, '$tsrxName = $this->tsrxName($idTipo);') === 4,
            'listAll, UpdateList, Resumen y ResumenUpdate deben resolver el permiso con tsrxName($idTipo)');
        $this->addCase($results, 'nivel_por_tipo_switch',
            substr_count($codigo, "case 0: \$tsrxName = 'informeDocumentos';") === 2
            && substr_count($codigo, "case 1: \$tsrxName = 'gestionDocumentosCompras';") === 2
            && substr_count($codigo, "case 2: \$tsrxName = 'gestionDocumentosVentas';") === 2,
            'View y Print deben mapear el tipo 0 al informe, 1 a compras y 2 a ventas');

        /*====================================*/
        // Wrappers por tipo: [metodo => [parametros, delegado esperado]]
        /*====================================*/
        $wrappers = [
            'listAll_1'      => [1, '$this->listAll($f3, 1)'],
            'listAll_2'      => [1, '$this->listAll($f3, 2)'],
            'UpdateList_1'   => [1, '$this->UpdateList($f3, 1)'],
            'UpdateList_2'   => [1, '$this->UpdateList($f3, 2)'],
            'View_0'         => [2, '$this->View($f3, $params, 0)'],
            'View_1'         => [2, '$this->View($f3, $params, 1)'],
            'View_2'         => [2, '$this->View($f3, $params, 2)'],
            'Print_0'        => [2, '$this->Print($f3, $params, 0, 1)'],
            'Print_1'        => [2, '$this->Print($f3, $params, 1, 1)'],
            'Print_2'        => [2, '$this->Print($f3, $params, 2, 1)'],
            'noPrint_0'      => [2, '$this->Print($f3, $params, 0, 0)'],
            'noPrint_1'      => [2, '$this->Print($f3, $params, 1, 0)'],
            'noPrint_2'      => [2, '$this->Print($f3, $params, 2, 0)'],
            'noPrintDoc'     => [2, '$this->Print($f3, $params, 2, 2)'],
            'Resumen_1'      => [2, '$this->Resumen($f3, $params, 1)'],
            'Resumen_2'      => [2, '$this->Resumen($f3, $params, 2)'],
            'ResumenUpdate_1' => [2, '$this->ResumenUpdate($f3, $params, 1)'],
            'ResumenUpdate_2' => [2, '$this->ResumenUpdate($f3, $params, 2)'],
        ];
        foreach ($wrappers as $metodo => $datos) {
            [$nParams, $delegado] = $datos;
            $this->firmaMetodo($results, $metodo, $nParams, $nParams);
            $this->addCase($results, 'wrapper_'.$metodo, str_contains($codigo, $delegado),
                $metodo.'() no delega en '.$delegado);
        }

        /*====================================*/
        // Mapas de tipo: 1 = compras, 2 = ventas (por defecto ventas)
        /*====================================*/
        $this->addCase($results, 'mapa_tsrx',
            str_contains($codigo, "1 => 'gestionDocumentosCompras'") && str_contains($codigo, "2 => 'gestionDocumentosVentas'"),
            'tsrxName() no mapea 1 a Compras y 2 a Ventas');
        $this->addCase($results, 'mapa_tipo_mov',
            str_contains($codigo, "1 => 'Compras'") && str_contains($codigo, "2 => 'Ventas'"),
            'TipoMov() no mapea 1 a Compras y 2 a Ventas');

        /*====================================*/
        // Consultas del listado, busqueda y detalle
        /*====================================*/
        $this->addCase($results, 'listado_filtra_por_tipo', str_contains($codigo, "'where'   => 'facturacion_listado.idTipo = ?'"),
            'listAll() no filtra los documentos por tipo');
        $this->addCase($results, 'listado_catalogos',
            str_contains($codigo, "'table'   => 'core_documentos_mercantiles'") && str_contains($codigo, "'table'   => 'entidades_listado'")
            && str_contains($codigo, "'table'   => 'productos_listado'") && str_contains($codigo, "'table'   => 'servicios_listado'")
            && str_contains($codigo, "'table'   => 'bodegas_listado'") && str_contains($codigo, "'table'   => 'core_estados_pago'"),
            'listAll() no carga los catalogos de documentos, entidades, productos, servicios, bodegas y estados de pago');
        $this->addCase($results, 'busqueda_campos_exactos', str_contains($codigo, "'idDocumentos,idEntidad,idEstadoPago,idFacturacion'"),
            'UpdateList() no filtra por documento, entidad, estado de pago y numero');
        $this->addCase($results, 'busqueda_valida_fechas', str_contains($codigo, 'searchValidateDates('),
            'UpdateList() no valida las fechas con searchValidateDates()');
        $this->addCase($results, 'busqueda_where_preparado', substr_count($codigo, '$this->searchWhere(') === 3,
            'UpdateList() no construye el WHERE con los tres tipos de searchWhere()');
        $this->addCase($results, 'consultas_limite', substr_count($codigo, 'ConfigAPP::APP["N_MaxItems"]') === 22,
            'se esperaban 22 consultas de lista acotadas por N_MaxItems');
        $this->addCase($results, 'detalle_subrecursos',
            str_contains($codigo, "'where'   => 'facturacion_listado_productos.idFacturacion = ?'")
            && str_contains($codigo, "'where'   => 'facturacion_listado_servicios.idFacturacion = ?'")
            && str_contains($codigo, "'where'   => 'facturacion_listado_guias.idFacturacion = ?'")
            && str_contains($codigo, "'where'   => 'facturacion_listado_pagos.idFacturacion = ?'"),
            'View() y Print() no consultan productos, servicios, guias y pagos del documento');
        $this->addCase($results, 'print_datos_sistema', str_contains($codigo, "'where'   => 'core_sistemas.idSistema = ?'"),
            'Print() no consulta los datos del emisor (core_sistemas)');
        $this->addCase($results, 'print_envia_flag', str_contains($codigo, "'Imprimir'"),
            'Print() no entrega el flag Imprimir a la vista');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                         CONTROLADOR: ESCRITURA (CRUD)                      */
    /******************************************************************************/
    /**
     * Verifica `Insert`, `Update`, `Delete` y `delFiles` y la logica transaccional de
     * `createDoc`, `deleteDoc` y `updateFact` SIN ejecutarlos: validaciones previas,
     * verbos HTTP, transaccion solo cuando no recibe una conexion externa, rollback por paso
     * y respuestas al cliente.
     *
     * @return array Resultado por caso
     */
    public function controladorEscrituraTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Firma de los metodos de escritura y de logica de negocio
        /*====================================*/
        $this->firmaMetodo($results, 'Insert', 1, 1);
        $this->firmaMetodo($results, 'Update', 1, 1);
        $this->firmaMetodo($results, 'Delete', 1, 1);
        $this->firmaMetodo($results, 'delFiles', 0, 0);
        $this->firmaMetodo($results, 'createDoc', 3, 3);
        $this->firmaMetodo($results, 'deleteDoc', 3, 3);
        $this->firmaMetodo($results, 'updateFact', 5, 5);
        foreach (['createDoc', 'deleteDoc', 'updateFact'] as $metodo) {
            $reflector = new ReflectionMethod(self::CONTROLADOR, $metodo);
            $this->addCase($results, 'retorno_array_'.$metodo,
                $reflector->hasReturnType() && (string)$reflector->getReturnType() === 'array',
                $metodo.'() debe declarar retorno array');
        }
        foreach (['createDoc', 'deleteDoc', 'updateFact'] as $metodo) {
            $parametros = (new ReflectionMethod(self::CONTROLADOR, $metodo))->getParameters();
            $nueva      = $metodo === 'updateFact' ? 2 : 2;
            $this->addCase($results, 'conexion_opcional_'.$metodo,
                isset($parametros[$nueva]) && $parametros[$nueva]->isDefaultValueAvailable() && $parametros[$nueva]->getDefaultValue() === null,
                $metodo.'() debe recibir la conexion externa NewDBConn como parametro opcional (null)');
        }

        /*====================================*/
        // Insert: usuario de sesion, validaciones previas y delegacion en createDoc
        /*====================================*/
        $this->addCase($results, 'insert_usuario_sesion', str_contains($codigo, "\$_POST['idUsuario'] = \$f3->get('SESSION.DataInfo.UserID')"),
            'Insert() y Update() deben tomar idUsuario de la sesion');
        $this->addCase($results, 'insert_exige_detalle', str_contains($codigo, "'No hay nada ingresado'"),
            'Insert() no rechaza un documento sin items, productos, servicios ni guias');
        $this->addCase($results, 'insert_exige_bodega', str_contains($codigo, "'No ha seleccionado la bodega'"),
            'Insert() no exige bodega cuando hay productos');
        $this->addCase($results, 'insert_bodega_por_tipo',
            str_contains($codigo, "\$_POST['idTipo'] == 1 && !empty(\$_POST['idBodegasIngreso'])")
            && str_contains($codigo, "\$_POST['idTipo'] == 2 && !empty(\$_POST['idBodegasEgreso'])"),
            'la bodega obligatoria debe ser de ingreso en compras y de egreso en ventas');
        $this->addCase($results, 'insert_productos_repetidos', str_contains($codigo, "'Existen productos repetidos'") && str_contains($codigo, 'array_unique('),
            'Insert() no rechaza productos repetidos');
        $this->addCase($results, 'insert_rechazo_500', substr_count($codigo, 'Response::error($DataVal[\'Msg\'], 500)') === 1,
            'Insert() debe responder 500 ante una validacion previa fallida');
        $this->addCase($results, 'insert_delega_createdoc', str_contains($codigo, '$this->createDoc($_POST, $arrUserData)'),
            'Insert() no delega en createDoc()');
        $this->addCase($results, 'insert_id_cifrado', str_contains($codigo, "encryptDecrypt('encrypt', \$Response['data']['data'])"),
            'Insert() no cifra el ID devuelto');

        /*====================================*/
        // Update: verbo, calculo de semana/mes/ano, delimitacion y chequeo
        /*====================================*/
        $this->addCase($results, 'update_verbo_post', str_contains($codigo, "REQUEST_METHOD'] !== 'POST'"),
            'Update() no valida el verbo HTTP POST');
        $this->addCase($results, 'update_calcula_periodo',
            str_contains($codigo, 'fecha2NSemana(') && str_contains($codigo, 'fecha2NMes(') && str_contains($codigo, 'fecha2Ano('),
            'Update() no recalcula semana, mes y ano de la fecha de creacion');
        $this->addCase($results, 'update_donde', str_contains($codigo, "'where'     => 'idFacturacion',\n            'Post'      => \$_POST,"),
            'Update() no delimita la escritura por idFacturacion');
        $this->addCase($results, 'update_requeridos',
            str_contains($codigo, "'required'  => 'idFacturacion,idUsuario,idTipo,idEntidad,fecha_auto,idDocumentos,Creacion_fecha,idEstadoPago'"),
            'Update() no declara los campos obligatorios del documento');
        $this->addCase($results, 'update_datacheck', str_contains($codigo, '$this->dataCheck_1($_POST)'),
            'Update() no valida con dataCheck_1($_POST)');
        $this->addCase($results, 'update_base', str_contains($codigo, '$this->Base_update('),
            'Update() no escribe con Base_update()');

        /*====================================*/
        // Delete y delFiles
        /*====================================*/
        $this->addCase($results, 'delete_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'DELETE'"),
            'Delete() no valida el verbo HTTP DELETE');
        $this->addCase($results, 'delete_delega_deletedoc', str_contains($codigo, '$this->deleteDoc($dataDelete, $arrUserData)'),
            'Delete() no delega en deleteDoc()');
        $this->addCase($results, 'delfiles_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'PUT'"),
            'delFiles() no valida el verbo HTTP PUT');
        $this->addCase($results, 'delfiles_sin_archivos', !str_contains($codigo, 'Base_delFiles('),
            'delFiles() borra archivos y los documentos mercantiles no almacenan archivos propios');

        /*====================================*/
        // Transacciones: solo si no hay conexion externa; rollback ante cada fallo
        /*====================================*/
        $this->addCase($results, 'transaccion_begin_condicional', substr_count($codigo, 'if($NewDBConn === null){') >= 3,
            'createDoc, deleteDoc y updateFact deben abrir/cerrar transaccion solo si no reciben una conexion externa');
        $this->addCase($results, 'transaccion_begin', substr_count($codigo, 'Base_transactionBegin(') === 3,
            'se esperaban 3 aperturas de transaccion (createDoc, deleteDoc y updateFact)');
        $this->addCase($results, 'transaccion_commit', substr_count($codigo, 'Base_transactionCommit(') === 3,
            'se esperaban 3 confirmaciones de transaccion');
        $this->addCase($results, 'transaccion_rollback', substr_count($codigo, 'Base_transactionRollback(') === 18,
            'se esperaban 18 rollback (uno por cada paso que puede fallar, incluido el lock FOR UPDATE de updateFact)');
        $this->addCase($results, 'transaccion_misma_conexion', substr_count($codigo, "['newBDConn' => \$DBConn]") >= 20,
            'las operaciones de createDoc/deleteDoc/updateFact deben compartir la conexion $DBConn');
        $this->addCase($results, 'retorno_codigos', str_contains($codigo, "'code' => 200") && str_contains($codigo, "'code' => 500") && str_contains($codigo, "'code' => 400"),
            'la logica de negocio no retorna los codigos 200/400/500');

        /*====================================*/
        // Respuestas al cliente
        /*====================================*/
        $this->addCase($results, 'respuesta_exito', substr_count($codigo, 'Response::success(') === 4,
            'se esperaban 4 Response::success() (Insert, Update, Delete y delFiles)');
        $this->addCase($results, 'respuesta_405', substr_count($codigo, "Response::error('Error en el Request Method', 405)") === 3,
            'Update, Delete y delFiles deben responder 405 ante un verbo incorrecto');
        $this->addCase($results, 'respuesta_400', substr_count($codigo, "Response::error('Registro inválido', 400)") === 5,
            'View, Print, Resumen, ResumenUpdate e Insert deben responder 400 ante un ID invalido');
        $this->addCase($results, 'respuesta_500', substr_count($codigo, 'failOperation(') === 1,
            'Update() debe responder 500 controlado ante un fallo de base de datos');
        $this->addCase($results, 'respuesta_delegada', substr_count($codigo, "Response::error(\$Response['message'], \$Response['code']") === 2,
            'Insert() y Delete() deben propagar el codigo y mensaje de la logica de negocio');

        /*====================================*/
        // IDs cifrados en las vistas
        /*====================================*/
        $this->addCase($results, 'id_descifrado_vistas', substr_count($codigo, "encryptDecrypt('decrypt', \$params['id'])") === 4,
            'View/Print/Resumen/ResumenUpdate deben descifrar $params[\'id\']');
        $this->addCase($results, 'id_valido_vistas', substr_count($codigo, 'isValidDecrypted(') === 7,
            'se esperaban 7 validaciones isValidDecrypted() (4 vistas, Insert, deleteDoc y la validacion previa)');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                       CONTROLADOR: RUTAS E INSTALADOR                      */
    /******************************************************************************/
    /**
     * Verifica que las 25 rutas de `gestionDocumentos` (compras y ventas) y las rutas de
     * detalle del informe publicadas por el instalador apunten a metodos reales con el
     * verbo y el nivel esperados, y que la desinstalacion limpie las rutas de todos los
     * controladores del modulo.
     *
     * @return array Resultado por caso
     */
    public function controladorRutasTest(){

        $results = [];
        $rutas   = $this->rutasInstalador();
        $this->addCase($results, 'instalador_rutas_disponibles', count($rutas) > 0,
            'getRouteDefinitions() no devolvio rutas');

        /*====================================*/
        // idMetodo: 1=GET, 2=POST, 3=DELETE, 4=PUT. [clave, handler, nivel, controller]
        /*====================================*/
        $esperadas = [];
        foreach ([1 => [self::RUTA_COMPRAS, 'compras'], 2 => [self::RUTA_VENTAS, 'ventas']] as $tipo => $datos) {
            [$base, $nombre] = $datos;
            $esperadas[$nombre.'_listado']        = ['1 '.$base.'/listAll',           'gestionDocumentos->listAll_'.$tipo,        1];
            $esperadas[$nombre.'_busqueda']       = ['2 '.$base.'/search',            'gestionDocumentos->UpdateList_'.$tipo,     1];
            $esperadas[$nombre.'_refrescar']      = ['1 '.$base.'/updateList',        'gestionDocumentos->UpdateList_'.$tipo,     2];
            $esperadas[$nombre.'_ver']            = ['1 '.$base.'/view/@id',          'gestionDocumentos->View_'.$tipo,           1];
            $esperadas[$nombre.'_imprimir']       = ['1 '.$base.'/print/@id',         'gestionDocumentos->Print_'.$tipo,          1];
            $esperadas[$nombre.'_visualizar']     = ['1 '.$base.'/noPrint/@id',       'gestionDocumentos->noPrint_'.$tipo,        1];
            $esperadas[$nombre.'_resumen']        = ['1 '.$base.'/resumen/@id',       'gestionDocumentos->Resumen_'.$tipo,        2];
            $esperadas[$nombre.'_resumen_update'] = ['1 '.$base.'/resumenUpdate/@id', 'gestionDocumentos->ResumenUpdate_'.$tipo,  2];
            $esperadas[$nombre.'_crear']          = ['2 '.$base,                      'gestionDocumentos->Insert',                3];
            $esperadas[$nombre.'_editar']         = ['2 '.$base.'/update',            'gestionDocumentos->Update',                2];
            $esperadas[$nombre.'_del_files']      = ['4 '.$base.'/delFiles',          'gestionDocumentos->DelFiles',              2];
            $esperadas[$nombre.'_borrar']         = ['3 '.$base,                      'gestionDocumentos->Delete',                4];
        }
        $esperadas['ventas_documento'] = ['1 '.self::RUTA_VENTAS.'/noPrintDoc/@id', 'gestionDocumentos->noPrintDoc', 1];

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

        $propias = array_filter($rutas, static function ($r) { return $r[2] === 'gestionDocumentos'; });
        $this->addCase($results, 'rutas_propias_completas', count($propias) === 25,
            'se esperaban 25 rutas de '.self::CONTROLADOR.' y hay '.count($propias));

        /*====================================*/
        // Informe: el detalle lo atiende gestionDocumentos con el tipo 0
        /*====================================*/
        $informe = [
            'ver'      => ['1 '.self::RUTA_INFORME.'/view/@id',  'gestionDocumentos->View_0'],
            'imprimir' => ['1 '.self::RUTA_INFORME.'/print/@id', 'gestionDocumentos->Print_0'],
        ];
        foreach ($informe as $caso => $datos) {
            [$clave, $handler] = $datos;
            $existe = isset($rutas[$clave]);
            $this->addCase($results, 'informe_'.$caso, $existe, 'el instalador no publica la ruta '.$clave);
            if ($existe) {
                $this->addCase($results, 'informe_'.$caso.'_handler', strcasecmp($rutas[$clave][0], $handler) === 0,
                    'la ruta '.$clave.' apunta a '.$rutas[$clave][0].' en vez de '.$handler);
                $this->addCase($results, 'informe_'.$caso.'_solo_ver', $rutas[$clave][1] === 1,
                    'la ruta '.$clave.' del informe debe ser de nivel 1');
            }
        }

        /*====================================*/
        // Matriz de verbos: escritura nunca por GET, borrado solo por DELETE
        /*====================================*/
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
        // Instalador: permisos y desinstalacion de TODOS los controladores del modulo
        /*====================================*/
        $instalador = $this->codigoFuente($this->rutaModulo('../installer/gestionDocumentosInstaller.php'));
        foreach (['gestionDocumentosCompras' => self::RUTA_COMPRAS, 'gestionDocumentosVentas' => self::RUTA_VENTAS, 'informeDocumentos' => self::RUTA_INFORME] as $permiso => $ruta) {
            $this->addCase($results, 'instalador_permiso_'.$permiso,
                str_contains($instalador, "'RutaWeb'        => '".$ruta."'") && str_contains($instalador, "'RutaController' => '".$permiso."'"),
                'el instalador no declara el permiso '.$permiso.' con la ruta '.$ruta);
        }
        $controladoresRutas = [];
        foreach ($rutas as $datos) {
            $controladoresRutas[$datos[2]] = true;
        }
        $faltan = [];
        foreach (array_keys($controladoresRutas) as $controlador) {
            if (!str_contains($instalador, "\$RutaController .= ',\"".$controlador."\"'") && !str_contains($instalador, "\$RutaController  = '\"".$controlador."\"'")) {
                $faltan[] = $controlador;
            }
        }
        $this->addCase($results, 'instalador_desinstala_todos', count($faltan) === 0 && count($controladoresRutas) === 7,
            'RutaController() no lista los 7 controladores de las rutas (faltan: '.implode(', ', $faltan).') y dejaria rutas huerfanas al desinstalar');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                PERMISOS                                    */
    /******************************************************************************/
    /**
     * Verifica la sesion, el nivel de acceso de los permisos Compras, Ventas e Informe
     * y el registro de las rutas en el router de F3 (solo si el usuario de pruebas las tiene).
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
        // Lectura de la sesion y nivel de cada permiso del modulo
        /*====================================*/
        try {
            $this->addCase($results, 'get_user_data_arreglo', is_array($this->getUserData($f3)),
                'getUserData() no devolvio un arreglo');
        } catch (Throwable $e) {
            $this->addCase($results, 'get_user_data_arreglo', false,
                'no se pudo leer la sesion del controlador ('.$e->getMessage().')');
        }
        $permisos = [
            'gestionDocumentosCompras' => self::RUTA_COMPRAS,
            'gestionDocumentosVentas'  => self::RUTA_VENTAS,
            'informeDocumentos'        => self::RUTA_INFORME,
        ];
        foreach ($permisos as $permiso => $rutaWeb) {
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
            if ($permiso === 'informeDocumentos') {
                $this->addCase($results, 'informe_nivel_solo_ver', $nivel === null || (int)$nivel === 1,
                    'el informe se concedio con nivel '.var_export($nivel, true).' y el limite es 1');
            }
        }

        /*====================================*/
        // Rutas en SESSION.arrPermisos y en el router (solo si las tiene)
        /*====================================*/
        $arrPermisos = $f3->get('SESSION.arrPermisos');
        $this->addCase($results, 'permisos_de_ruta_presentes', $arrPermisos === null || is_array($arrPermisos),
            'SESSION.arrPermisos no es un arreglo');

        $arrRoutes = $f3->get('ROUTES');
        $this->addCase($results, 'router_disponible', is_array($arrRoutes), 'F3 no expuso la variable ROUTES');
        $esquema = [
            'compras_listado_router' => ['/'.self::RUTA_COMPRAS.'/listAll', 'GET',    self::CONTROLADOR.'->listAll_1'],
            'ventas_listado_router'  => ['/'.self::RUTA_VENTAS.'/listAll',  'GET',    self::CONTROLADOR.'->listAll_2'],
            'compras_insert_router'  => ['/'.self::RUTA_COMPRAS,            'POST',   self::CONTROLADOR.'->Insert'],
            'ventas_insert_router'   => ['/'.self::RUTA_VENTAS,             'POST',   self::CONTROLADOR.'->Insert'],
            'compras_delete_router'  => ['/'.self::RUTA_COMPRAS,            'DELETE', self::CONTROLADOR.'->Delete'],
            'ventas_delete_router'   => ['/'.self::RUTA_VENTAS,             'DELETE', self::CONTROLADOR.'->Delete'],
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
     * Renderiza `List` y `UpdateList` con datos sinteticos para compras y ventas y verifica
     * la matriz de permisos (Crear con nivel >= 3, ver >= 1, editar >= 2, borrar >= 4), el
     * ID cifrado, el nombre de la entidad, la referencia cuando no hay numero de documento
     * y el escape de comillas hacia JavaScript.
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
        // Pagina de listado: ventas y compras
        /*====================================*/
        foreach ([2 => self::RUTA_VENTAS, 1 => self::RUTA_COMPRAS] as $tipo => $ruta) {
            $etiqueta = $tipo === 1 ? 'compras' : 'ventas';
            $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php'),
                $this->datosSinteticos(1, $ruta, $tipo), $BASE, $f3);
            $this->addCase($results, 'listado_render_'.$etiqueta, $html !== '', 'la vista List de '.$etiqueta.' no produjo HTML');
            $this->addCase($results, 'listado_contenedor_'.$etiqueta, str_contains($html, 'id="listTableData"'),
                'falta el contenedor de la tabla en '.$etiqueta);
            // List incluye formSearch, UpdateList y formNew con require_once: solo la primera pasada los dibuja
            if ($tipo === 2) {
                $this->addCase($results, 'listado_form_busqueda', str_contains($html, 'id="FormSearchData"'),
                    'no se dibuja el formulario de busqueda');
                $this->addCase($results, 'listado_form_nuevo', str_contains($html, 'id="newFormModal"'),
                    'no se dibuja el modal de alta');
            }
            $this->addCase($results, 'listado_sin_crear_nivel1_'.$etiqueta, !str_contains($html, 'Crear Nuevo</button>'),
                'con nivel 1 el listado de '.$etiqueta.' ofrece crear registros');
            $this->addCase($results, 'listado_ruta_'.$etiqueta, str_contains($html, $BASE.'/'.$ruta),
                'las acciones de '.$etiqueta.' no apuntan a '.$ruta);
            $this->addCase($results, 'listado_borrado_verbo_'.$etiqueta, str_contains($html, "let Metodo      = 'DELETE'"),
                'la accion de borrar de '.$etiqueta.' no viaja con el verbo DELETE');
            $this->addCase($results, 'listado_borrado_id_'.$etiqueta, str_contains($html, '{"idFacturacion": ID}'),
                'la accion de borrar de '.$etiqueta.' no envia idFacturacion');
        }
        $htmlNivel3 = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php'),
            $this->datosSinteticos(3), $BASE, $f3);
        $this->addCase($results, 'listado_crear_nivel3', str_contains($htmlNivel3, 'Crear Nuevo</button>'),
            'con nivel 3 no se muestra el boton Crear Nuevo');
        $this->addCase($results, 'listado_modal_nivel3', str_contains($htmlNivel3, 'data-bs-target="#newFormModal"'),
            'el boton de alta no apunta al modal #newFormModal');

        /*====================================*/
        // Matriz de acciones sobre la tabla (UpdateList)
        /*====================================*/
        $rutaTabla = $this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php');
        foreach (self::NIVELES as $nivel) {
            $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos($nivel), $BASE, $f3);
            $this->addCase($results, 'tabla_render_nivel'.$nivel, $tabla !== '',
                'la vista UpdateList no produjo HTML con nivel '.$nivel);
            $this->addCase($results, 'tabla_ver_nivel'.$nivel, str_contains($tabla, 'listTableDataView(') === ($nivel >= 1),
                'la accion Ver no coincide con el nivel '.$nivel);
            $this->addCase($results, 'tabla_editar_nivel'.$nivel, str_contains($tabla, '/resumen/') === ($nivel >= 2),
                'la accion Editar no coincide con el nivel '.$nivel);
            $this->addCase($results, 'tabla_borrar_nivel'.$nivel, str_contains($tabla, 'listTableDataDel(') === ($nivel >= 4),
                'la accion Borrar no coincide con el nivel '.$nivel);
            $this->addCase($results, 'tabla_datos_nivel'.$nivel,
                str_contains($tabla, 'Factura 1001') && str_contains($tabla, 'Entidad Prueba SpA') && str_contains($tabla, 'badge-sp1-success'),
                'la tabla no pinta documento, entidad y estado de pago con nivel '.$nivel);
            $this->addCase($results, 'tabla_id_cifrado_nivel'.$nivel,
                !str_contains($tabla, "listTableDataView('41')") && !str_contains($tabla, '/resumen/41'),
                'la tabla expone idFacturacion sin cifrar con nivel '.$nivel);
        }
        $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos(4), $BASE, $f3);
        $this->addCase($results, 'tabla_columnas',
            str_contains($tabla, '>Documento<') && str_contains($tabla, '>Estado<') && str_contains($tabla, '>Valor Total<') && str_contains($tabla, '>Monto Pagado<'),
            'la tabla no declara las columnas Documento, Estado, Valor Total y Monto Pagado');
        $this->addCase($results, 'tabla_montos_formateados', str_contains($tabla, '119.000'),
            'la tabla no formatea los montos con Valores()');

        /*====================================*/
        // Nombre de la entidad y referencia cuando no hay numero de documento
        /*====================================*/
        $sinNumero = $this->datosSinteticos(4);
        $sinNumero['arrList'][0]['N_Doc'] = null;
        $tablaSinNumero = $this->renderizarVista($rutaTabla, $sinNumero, $BASE, $f3);
        $this->addCase($results, 'tabla_sin_numero_referencia', str_contains($tablaSinNumero, 'Factura nRef #41'),
            'sin N_Doc la tabla no muestra la referencia "nRef #<id>"');

        $persona = $this->datosSinteticos(4);
        $persona['arrList'][0]['idTipoEntidad']     = 1;
        $persona['arrList'][0]['EntidadesNombre']   = 'Ana';
        $persona['arrList'][0]['EntidadesApellido'] = 'Prueba';
        $persona['arrList'][0]['EntidadesNick']     = 'anita';
        $tablaPersona = $this->renderizarVista($rutaTabla, $persona, $BASE, $f3);
        $this->addCase($results, 'tabla_persona_natural', str_contains($tablaPersona, 'Prueba, Ana (anita)'),
            'la tabla no compone una persona natural como "Apellido, Nombre (Nick)"');

        $comillas = $this->datosSinteticos(4);
        $comillas['arrList'][0]['Documento'] = "Factura d'prueba";
        $tablaComillas = $this->renderizarVista($rutaTabla, $comillas, $BASE, $f3);
        $this->addCase($results, 'tabla_escapa_comillas', str_contains($tablaComillas, "Factura d\\'prueba 1001"),
            'la etiqueta del borrado no escapa las comillas simples');

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
     * Renderiza `formSearch`, `formNew` (compras y ventas), `View` y `Print` con datos
     * sinteticos y comprueba ids, campos, endpoints, verbos y la bodega segun el tipo.
     * `Resumen` y `Resumen-Update` se verifican ademas por fuente.
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
        foreach (['idDocumentos', 'N_Doc', 'idFacturacion', 'idEntidad', 'F_Inicio', 'F_Termino', 'idEstadoPago'] as $campo) {
            if (!str_contains($html, 'id="Search_'.$campo.'"')) {
                $faltan[] = $campo;
            }
        }
        $this->addCase($results, 'form_search_inputs', count($faltan) === 0,
            'el formulario de busqueda no declara los campos Search_: '.implode(', ', $faltan));
        $this->addCase($results, 'form_search_endpoint',
            str_contains($html, self::RUTA_VENTAS.'/search') && str_contains($html, "let Metodo      = 'POST'"),
            'el formulario de busqueda no apunta a /search con POST');

        /*====================================*/
        // Formulario de alta: ventas (bodega de egreso) y compras (bodega de ingreso)
        /*====================================*/
        foreach ([2 => self::RUTA_VENTAS, 1 => self::RUTA_COMPRAS] as $tipo => $ruta) {
            $etiqueta = $tipo === 1 ? 'compras' : 'ventas';
            $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-formNew.php'),
                $this->datosSinteticos(3, $ruta, $tipo), $BASE, $f3);
            $this->addCase($results, 'form_new_render_'.$etiqueta, $html !== '', 'formNew de '.$etiqueta.' no produjo HTML');
            $this->addCase($results, 'form_new_id_'.$etiqueta, str_contains($html, 'id="FormNewData"') && str_contains($html, 'id="newFormModal"'),
                'formNew de '.$etiqueta.' no usa FormNewData en #newFormModal');
            $faltan = [];
            foreach (['idDocumentos', 'N_Doc', 'idEntidad', 'idEstadoPago', 'Creacion_fecha', 'Observaciones', 'idTipo', 'fecha_auto', 'Creacion_hora'] as $campo) {
                if (!str_contains($html, 'name="'.$campo.'"')) {
                    $faltan[] = $campo;
                }
            }
            $this->addCase($results, 'form_new_campos_'.$etiqueta, count($faltan) === 0,
                'formNew de '.$etiqueta.' no declara los campos: '.implode(', ', $faltan));
            $this->addCase($results, 'form_new_tipo_'.$etiqueta,
                (bool)preg_match('/name="idTipo"[^>]*value="'.$tipo.'"|value="'.$tipo.'"[^>]*name="idTipo"/', $html),
                'formNew de '.$etiqueta.' no envia idTipo='.$tipo);
            $this->addCase($results, 'form_new_bodega_'.$etiqueta,
                str_contains($html, $tipo === 1 ? 'name="idBodegasIngreso"' : 'name="idBodegasEgreso"')
                && !str_contains($html, $tipo === 1 ? 'name="idBodegasEgreso"' : 'name="idBodegasIngreso"'),
                'formNew de '.$etiqueta.' debe pedir solo la bodega de '.($tipo === 1 ? 'ingreso' : 'egreso'));
            $this->addCase($results, 'form_new_detalle_'.$etiqueta,
                str_contains($html, 'Item_Item[]') && str_contains($html, 'Producto_idProducto[]') && str_contains($html, 'Servicio_idServicio[]'),
                'formNew de '.$etiqueta.' no declara las plantillas de items, productos y servicios');
            $this->addCase($results, 'form_new_endpoint_'.$etiqueta,
                str_contains($html, $BASE.'/'.$ruta."'") && str_contains($html, "let Metodo      = 'POST'"),
                'formNew de '.$etiqueta.' no publica en '.$ruta.' con POST');
        }
        $sinBodega = $this->datosSinteticos(3);
        $sinBodega['UserData']['gestionDocumentosUsoBodega'] = 1;
        $htmlSinBodega = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-formNew.php'), $sinBodega, $BASE, $f3);
        $this->addCase($results, 'form_new_sin_uso_bodega',
            $htmlSinBodega !== '' && !str_contains($htmlSinBodega, 'name="idBodegasEgreso"') && !str_contains($htmlSinBodega, 'name="idBodegasIngreso"'),
            'sin la marca gestionDocumentosUsoBodega el alta sigue pidiendo bodega');

        /*====================================*/
        // Modal de detalle (View)
        /*====================================*/
        $rutaView   = $this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php');
        $sinDetalle = $this->datosSinteticos(2);
        $sinDetalle['arrItems'] = [];
        $sinDetalle['arrProductos'] = [];
        $sinDetalle['arrServicios'] = [];
        $sinDetalle['arrGuias'] = [];
        $sinDetalle['arrPagos'] = [];
        $html = $this->renderizarVista($rutaView, $sinDetalle, $BASE, $f3);
        $this->addCase($results, 'view_render', $html !== '', 'la vista View no produjo HTML');
        $this->addCase($results, 'view_pestana_basica', str_contains($html, 'id="view_tab_1"'),
            'la vista View no monta la pestana de datos basicos');
        $this->addCase($results, 'view_sin_pestanas_vacias',
            !str_contains($html, 'id="view_tab_2"') && !str_contains($html, 'id="view_tab_3"') && !str_contains($html, 'id="view_tab_4"') && !str_contains($html, 'id="view_tab_5"') && !str_contains($html, 'id="view_tab_6"'),
            'la vista View monta pestanas de detalle sin datos');
        $this->addCase($results, 'view_enlace_documento', str_contains($html, self::RUTA_VENTAS.'/noPrint/'),
            'la vista View no enlaza a la visualizacion del documento');
        $this->addCase($results, 'view_enlace_imprimir', str_contains($html, self::RUTA_VENTAS.'/print/'),
            'la vista View no enlaza a la impresion del documento');
        $this->addCase($results, 'view_id_cifrado', !str_contains($html, '/noPrint/41') && !str_contains($html, '/print/41'),
            'la vista View expone idFacturacion sin cifrar');

        // Con detalle: se montan las 5 pestanas y se pintan las filas (segundo render: Resumen-Update
        // ya fue incluido con require_once, solo se evalua el marcado propio de View)
        $conDetalle = $this->datosSinteticos(2);
        $conDetalle['arrItems']     = [['Item' => 'Item de prueba', 'Number' => 2, 'ValorTotal' => 20000]];
        $conDetalle['arrProductos'] = [['Bodega' => 'Bodega Prueba', 'TipoMovimiento' => 'Egreso', 'ProductoNombre' => 'Producto Prueba', 'ProductoCantidad' => 1, 'UnidadMedida' => 'un', 'ProductoValor' => 50000]];
        $conDetalle['arrServicios'] = [['ServicioNombre' => 'Servicio Prueba', 'ServicioCantidad' => 1, 'ServicioValor' => 49000]];
        $conDetalle['arrGuias']     = [['idFacturacionRel' => 40, 'Documento' => 'Guia Despacho', 'N_Doc' => 900, 'Creacion_fecha' => '2026-01-01', 'ValorTotal' => 10000]];
        $conDetalle['arrPagos']     = [['DocPago' => 'Transferencia', 'N_Doc' => 77, 'FechaPago' => '2026-01-03', 'MontoPagado' => 119000, 'UsuarioPago' => 'Tester']];
        $htmlDetalle = $this->renderizarVista($rutaView, $conDetalle, $BASE, $f3);
        $this->addCase($results, 'view_con_detalle_render', $htmlDetalle !== '', 'la vista View con detalle no produjo HTML');
        $this->addCase($results, 'view_con_detalle_pestanas',
            str_contains($htmlDetalle, 'id="view_tab_2"') && str_contains($htmlDetalle, 'id="view_tab_3"') && str_contains($htmlDetalle, 'id="view_tab_4"')
            && str_contains($htmlDetalle, 'id="view_tab_5"') && str_contains($htmlDetalle, 'id="view_tab_6"'),
            'la vista View no monta las pestanas de items, productos, servicios, guias y pagos con datos');
        $this->addCase($results, 'view_con_detalle_filas',
            str_contains($htmlDetalle, 'Item de prueba') && str_contains($htmlDetalle, 'Producto Prueba') && str_contains($htmlDetalle, 'Servicio Prueba')
            && str_contains($htmlDetalle, 'Guia Despacho') && str_contains($htmlDetalle, 'Transferencia'),
            'la vista View no pinta las filas de items, productos, servicios, guias y pagos');
        $this->addCase($results, 'view_con_detalle_bodega', str_contains($htmlDetalle, 'Bodega Prueba') && str_contains($htmlDetalle, 'Egreso'),
            'la pestana de productos no muestra la bodega y el tipo de movimiento');

        /*====================================*/
        // Documento imprimible (Print): emisor, cliente, y modo imprimir/visualizar
        /*====================================*/
        $imprimir = $this->datosSinteticos(1);
        $imprimir['rowSistema'] = [
            'Sistema_Nombre' => 'Emisor Prueba', 'Sistema_Direccion' => 'Calle 1', 'Sistema_Email' => 'prueba@example.test',
            'Sistema_Ciudad' => 'Santiago', 'Sistema_Comuna' => 'Providencia',
            'Sistema_Fono1' => '', 'Sistema_Fono2' => '',
        ];
        $imprimir['Imprimir'] = 0;
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-Print.php'), $imprimir, $BASE, $f3);
        $this->addCase($results, 'print_render', $html !== '', 'la vista Print no produjo HTML');
        $this->addCase($results, 'print_emisor', str_contains($html, 'Emisor Prueba'), 'el documento no muestra el emisor');
        $this->addCase($results, 'print_cliente', str_contains($html, 'Entidad Prueba SpA'), 'el documento no muestra el cliente');
        $this->addCase($results, 'print_numero', str_contains($html, '1001'), 'el documento no muestra el numero');

        /*====================================*/
        // Resumen y Resumen-Update por fuente (formulario de edicion y pestanas)
        /*====================================*/
        $resumen = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen.php'));
        $this->addCase($results, 'resumen_form_edicion', str_contains($resumen, 'id="FormEditData"'),
            'el resumen no monta el formulario de edicion');
        $this->addCase($results, 'resumen_id_cifrado', str_contains($resumen, "encryptDecrypt('encrypt', \$data['rowData']['idFacturacion'])"),
            'el resumen no cifra idFacturacion para las acciones');
        $this->addCase($results, 'resumen_endpoint_update', str_contains($resumen, "/update'") && str_contains($resumen, '/resumenUpdate/'),
            'el resumen no apunta a /update y /resumenUpdate');
        $this->addCase($results, 'resumen_pestanas',
            str_contains($resumen, 'resumen-items') && str_contains($resumen, 'resumen-productos') && str_contains($resumen, 'resumen-servicios')
            && str_contains($resumen, 'resumen-guias') && str_contains($resumen, 'resumen-pagos'),
            'el resumen no monta las pestanas de items, productos, servicios, guias y pagos');
        $resumenUpdate = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen-Update.php'));
        $this->addCase($results, 'resumen_update_montos',
            str_contains($resumenUpdate, "['ValorNeto']") && str_contains($resumenUpdate, "['IVA']") && str_contains($resumenUpdate, "['ValorTotal']") && str_contains($resumenUpdate, "['MontoPagado']"),
            'el resumen no muestra neto, IVA, total y monto pagado');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos los mapas de tipo (`tsrxName`, `TipoMov`), los seis
     * chequeos `dataCheck_1..6`, la rama de `deleteDoc()` que rechaza un ID invalido antes de
     * tocar la base, el cifrado de IDs y `isValidDecrypted()`.
     *
     * @return array Resultado por caso
     */
    public function logicaValidacionTest(){

        $results = [];

        /*====================================*/
        // tsrxName / TipoMov: 1 = Compras, 2 = Ventas y por defecto Ventas
        /*====================================*/
        try {
            $controlador = $this->instanciaSinBD();
            foreach (['tsrxName' => ['gestionDocumentosCompras', 'gestionDocumentosVentas'], 'TipoMov' => ['Compras', 'Ventas']] as $metodo => $valores) {
                $reflector = new ReflectionMethod(self::CONTROLADOR, $metodo);
                $reflector->setAccessible(true);
                $this->addCase($results, $metodo.'_privado', $reflector->isPrivate(), $metodo.'() debe ser privado');
                $this->addCase($results, $metodo.'_compras', $reflector->invoke($controlador, 1) === $valores[0],
                    $metodo.'(1) debe devolver '.$valores[0]);
                $this->addCase($results, $metodo.'_ventas', $reflector->invoke($controlador, 2) === $valores[1],
                    $metodo.'(2) debe devolver '.$valores[1]);
                $this->addCase($results, $metodo.'_defecto_ventas',
                    $reflector->invoke($controlador, 0) === $valores[1] && $reflector->invoke($controlador, 99) === $valores[1] && $reflector->invoke($controlador, -1) === $valores[1],
                    $metodo.'() debe devolver '.$valores[1].' para tipos no validos (0, 99, -1)');
            }
        } catch (Throwable $e) {
            $this->addCase($results, 'tsrxName_compras', false, 'no se pudo ejecutar tsrxName/TipoMov ('.$e->getMessage().')');
        }

        /*====================================*/
        // dataCheck_1..6: contrato esperado por Base_insert/Base_update
        /*====================================*/
        try {
            $controlador = $this->instanciaSinBD();
            $esperados = [
                1 => ['ValidarEntero' => ['idUsuario', 'idTipo', 'idEntidad', 'idBodegasIngreso', 'idBodegasEgreso', 'idDocumentos', 'idEstadoPago'],
                      'ValidarNumero' => ['N_Doc', 'ValorNeto', 'IVA', 'ValorTotal', 'MontoPagado'],
                      'ValidarFecha'  => ['fecha_auto', 'Creacion_fecha']],
                2 => ['ValidarEntero' => ['idFacturacion', 'idBodegas', 'idProducto', 'idServicio'],
                      'ValidarNumero' => ['Number', 'ValorTotal'],
                      'ValidarLargoMinimo' => ['Item']],
                3 => ['ValidarEntero' => ['idFacturacion', 'idFacturacionRel']],
                4 => ['ValidarEntero' => ['idFacturacion', 'idEstadoPago']],
                5 => ['ValidarEntero' => ['idFacturacion'],
                      'ValidarNumero' => ['ValorNeto', 'IVA', 'ValorTotal', 'TotalItems', 'TotalProductos', 'TotalServicios', 'TotalGuias']],
                6 => ['ValidarEntero' => ['idFacturacion', 'idEstadoPago'],
                      'ValidarNumero' => ['ValorNeto', 'IVA', 'ValorTotal', 'TotalGuias']],
            ];
            $Post = ['idFacturacion' => 41, 'ValorTotal' => 119000];
            foreach ($esperados as $n => $campos) {
                $metodo    = 'dataCheck_'.$n;
                $reflector = new ReflectionMethod(self::CONTROLADOR, $metodo);
                $reflector->setAccessible(true);
                $DataCheck = $reflector->invoke($controlador, $Post);
                $this->addCase($results, $metodo.'_privado', $reflector->isPrivate(), $metodo.'() debe ser privado');
                $this->addCase($results, $metodo.'_post', ($DataCheck['Post'] ?? null) === $Post,
                    $metodo.'() no conserva el POST recibido');
                $this->addCase($results, $metodo.'_claves_contrato',
                    isset($DataCheck['emptyData'], $DataCheck['ValidarNumero'], $DataCheck['ValidarEntero'], $DataCheck['ValidarLargoMinimoN'], $DataCheck['ValidarLargoMaximoPorCampo']),
                    $metodo.'() no entrega las claves del contrato de CheckData');
                foreach ($campos as $clave => $lista) {
                    $presentes = array_map('trim', explode(',', (string)($DataCheck[$clave] ?? '')));
                    $faltan    = array_diff($lista, $presentes);
                    $this->addCase($results, $metodo.'_'.$clave, count($faltan) === 0,
                        $metodo.'() no incluye en '.$clave.': '.implode(', ', $faltan));
                }
            }
            $DataCheck1 = (new ReflectionMethod(self::CONTROLADOR, 'dataCheck_1'));
            $DataCheck1->setAccessible(true);
            $c1 = $DataCheck1->invoke($controlador, []);
            $this->addCase($results, 'dataCheck_1_observaciones',
                trim((string)$c1['ValidarLargoMinimo']) === 'Observaciones' && trim((string)$c1['ValidarPalabrasCensuradas']) === 'Observaciones'
                && (int)$c1['ValidarLargoMinimoN'] === 3 && (int)($c1['ValidarLargoMaximoPorCampo']['N_Doc'] ?? 0) === 60,
                'dataCheck_1() no controla el largo minimo de Observaciones ni el maximo de N_Doc');
            $this->addCase($results, 'dataCheck_1_fecha_rango', trim((string)$c1['ValidarFechaRango']) === 'fecha_auto',
                'dataCheck_1() no valida el rango de fecha_auto');
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_ejecucion', false,
                'no se pudo ejecutar dataCheck ('.$e->getMessage().')');
        }

        /*====================================*/
        // deleteDoc: un ID cifrado invalido se rechaza con 400 ANTES de tocar la base.
        // Se pasa una conexion no nula para que jamas abra transaccion.
        /*====================================*/
        try {
            $controlador = $this->instanciaSinBD();
            $conexionFalsa = new stdClass();
            $resp = $controlador->deleteDoc(['idFacturacion' => 'valor-no-cifrado'], [], $conexionFalsa);
            $this->addCase($results, 'deletedoc_id_invalido_400', is_array($resp) && ($resp['code'] ?? 0) === 400,
                'deleteDoc() con un ID no cifrado debe responder code 400 y respondio '.json_encode($resp));
            $this->addCase($results, 'deletedoc_id_invalido_mensaje', ($resp['message'] ?? '') === 'Registro inválido',
                'deleteDoc() con un ID invalido debe informar "Registro inválido"');
            $resp0 = $controlador->deleteDoc(['idFacturacion' => (new FunctionsSecurityCodification())->encryptDecrypt('encrypt', '0')['data']], [], $conexionFalsa);
            $this->addCase($results, 'deletedoc_id_cero_400', is_array($resp0) && ($resp0['code'] ?? 0) === 400,
                'deleteDoc() con el ID 0 cifrado debe responder code 400');
        } catch (Throwable $e) {
            $this->addCase($results, 'deletedoc_id_invalido_400', false,
                'no se pudo ejecutar deleteDoc() con un ID invalido ('.get_class($e).': '.$e->getMessage().')');
        }

        /*====================================*/
        // Cifrado de identificadores: ida y vuelta estable y rechazo de basura
        /*====================================*/
        try {
            $Codification = new FunctionsSecurityCodification();
            $cifrado      = $Codification->encryptDecrypt('encrypt', '41');
            $this->addCase($results, 'cifrado_genera_dato',
                ($cifrado['success'] ?? false) === true && !empty($cifrado['data']),
                'encryptDecrypt(encrypt) no produjo un valor cifrado');
            $descifrado = $Codification->encryptDecrypt('decrypt', (string)($cifrado['data'] ?? ''));
            $this->addCase($results, 'cifrado_ida_y_vuelta',
                ($descifrado['success'] ?? false) === true && (string)($descifrado['data'] ?? '') === '41',
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
     * Revisa estaticamente las vistas y el controlador: rutas relativas, rutas absolutas,
     * acceso a sesion, SQL embebido, cifrado de IDs, escape, consultas preparadas e
     * interpolacion de IDs en SQL.
     *
     * @return array Resultado por caso
     */
    public function seguridadVistasTest(){

        $results = [];

        /*====================================*/
        // Vistas propias del controlador (sin las de sub-recursos)
        /*====================================*/
        $arrVistas = glob($this->rutaModulo('../views/'.self::CONTROLADOR.'-*.php')) ?: [];
        $arrVistas = array_values(array_filter($arrVistas, static function ($r) {
            return !preg_match('/-Resumen-(Guias|Items|Pagos|Productos|Servicios)-/', basename($r));
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
        }

        /*====================================*/
        // Escape, cifrado y formato en la tabla
        /*====================================*/
        $tabla = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'));
        $this->addCase($results, 'tabla_escape_etiqueta', str_contains($tabla, 'addslashes($crud[\'Documento\']'),
            'UpdateList no escapa la etiqueta que viaja al JavaScript');
        $this->addCase($results, 'tabla_id_cifrado', str_contains($tabla, "encryptDecrypt('encrypt', \$crud['idFacturacion'])"),
            'UpdateList no cifra idFacturacion en las acciones');
        $this->addCase($results, 'tabla_fecha_estandar', str_contains($tabla, 'fechaEstandar('),
            'UpdateList no formatea la fecha con fechaEstandar()');

        /*====================================*/
        // Controlador: SQL preparado y sin concatenacion de entrada
        /*====================================*/
        $controlador = $this->fuenteControlador();
        $this->addCase($results, 'controlador_sin_concat_sql', !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $controlador),
            'el controlador concatena superglobales en una cadena SQL');
        $this->addCase($results, 'controlador_parametros_preparados', preg_match_all("/'params'\s*=>/", $controlador) === 30,
            'las 30 consultas deben declarar la clave params (consultas preparadas)');
        $this->addCase($results, 'controlador_sin_get', !str_contains($controlador, '$_GET') && !str_contains($controlador, '$_REQUEST'),
            'el controlador lee $_GET/$_REQUEST; solo debe usar $_POST y parametros de ruta cifrados');
        $this->addCase($results, 'in_con_marcadores', str_contains($controlador, "array_fill(0, count(\$ElementsIDs), '?')") && str_contains($controlador, '(int)$PostData[\'idFacturacionRel\']'),
            'la consulta IN de guias debe construirse con marcadores y IDs forzados a entero');

        /*====================================*/
        // updateFact() interpola el ID en subselects: debe forzarlo a entero antes
        /*====================================*/
        $posCast   = strpos($controlador, '$FacturacionID = (int)$FacturacionID;');
        $posSelect = strpos($controlador, "\$Data .= ',");
        $this->addCase($results, 'updatefact_id_entero', $posCast !== false && $posSelect !== false && $posCast < $posSelect,
            'updateFact() no fuerza el ID a entero antes de interpolarlo en el SQL');

        /************************************/
        // Retorno los datos
        return $results;

    }

}
