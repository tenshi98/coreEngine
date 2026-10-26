<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class gestionDocumentosGuiasTesting
 *
 * Pruebas del modulo `facturacion` - controlador `gestionDocumentosGuias` (guias de despacho asociadas a un documento).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. Los textos de los casos
 *    correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Alcance: pruebas NO destructivas. `Insert()` y `Delete()` abren transaccion, escriben en
 * `facturacion_listado_guias`, recalculan el documento con `gestionDocumentos::updateFact()` y terminan
 * en `Response::*()` (`exit`); NO se invocan: se verifican por reflexion y lectura del fuente. Solo se
 * ejecuta logica pura (`tsrxName`, `dataCheck_1`, cifrado de IDs).
 *
 * El controlador reutiliza permisos y vistas de `gestionDocumentos` ($this->controllerName) y atiende
 * dos tipos con wrappers: tipo 1 = compras (`gestionDocumentosCompras`) y tipo 2 = ventas
 * (`gestionDocumentosVentas`). Las guias de despacho son documentos mercantiles tipo 3 que se asocian
 * a un documento (el selector ofrece solo las pendientes de la misma entidad).
 *
 * @see skills/testing/SKILL.md
 */
class gestionDocumentosGuiasTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR      = 'gestionDocumentosGuias';
    private const VISTA_BASE       = 'gestionDocumentos-Resumen-Guias';
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
            'Fnc_DataDate'     => new FunctionsDataDate(),
            'Fnc_DataNumbers'  => new FunctionsDataNumbers(),
            'rowData'          => ['idFacturacion' => 41, 'idEntidad' => 1, 'idEstadoPago' => $estadoPago],
            // Para formNew es el catalogo (ID, Nombre); para UpdateList son las guias asociadas
            'arrGuias'         => [[
                'idExistencia'     => 55,
                'idFacturacionRel' => 40,
                'Documento'        => 'Guia Despacho',
                'N_Doc'            => 900,
                'Creacion_fecha'   => '2026-01-01',
                'ValorTotal'       => 10000,
            ]],
        ];
    }

    /*******************************************************************/
    // Datos del formulario de alta: arrGuias es el catalogo (ID, Nombre)
    /*******************************************************************/
    private function datosFormulario(string $ruta = self::RUTA_VENTAS, int $idTipo = 2): array{
        $data = $this->datosSinteticos(2, $ruta, $idTipo);
        $data['arrGuias'] = [['ID' => 40, 'Nombre' => 'Guia Despacho 900 Fecha 2026-01-01'], ['ID' => 39, 'Nombre' => 'Guia Despacho nRef 39']];
        return $data;
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
     * Verifica los archivos del controlador `gestionDocumentosGuias` y la convencion
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
            'vista_update_list' => $this->rutaModulo('../views/'.self::VISTA_BASE.'-UpdateList.php'),
            'testing'           => $this->rutaModulo('gestionDocumentosGuiasTesting.php'),
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
        $this->addCase($results, 'clase_testing_cargada', class_exists('gestionDocumentosGuiasTesting'),
            'la clase gestionDocumentosGuiasTesting no esta disponible');
        $padre = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padre === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padre.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('gestionDocumentosGuiasTesting') === 'ControllerBase',
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
     * Verifica los wrappers por tipo (`New_1/2`, `UpdateList_1/2`), los metodos base `New` y
     * `UpdateList`, el permiso segun el tipo y las consultas de lectura (incluido el filtro de
     * guias pendientes de la misma entidad).
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Metodos base y wrappers
        /*====================================*/
        $this->firmaMetodo($results, 'New', 3, 3);
        $this->firmaMetodo($results, 'UpdateList', 3, 3);
        foreach (['New_1' => '$this->New($f3, $params, 1)', 'New_2' => '$this->New($f3, $params, 2)',
                  'UpdateList_1' => '$this->UpdateList($f3, $params, 1)', 'UpdateList_2' => '$this->UpdateList($f3, $params, 2)'] as $metodo => $delegado) {
            $this->firmaMetodo($results, $metodo, 2, 2);
            $this->addCase($results, 'wrapper_'.$metodo, str_contains($codigo, $delegado), $metodo.'() no delega en '.$delegado);
        }

        /*====================================*/
        // Contrato de presentacion
        /*====================================*/
        foreach (['New' => 'formNew', 'UpdateList' => 'UpdateList'] as $metodo => $sufijo) {
            $this->addCase($results, 'vista_'.$metodo, str_contains($codigo, "\$this->controllerName.'-Resumen-Guias-".$sufijo.".php'"),
                $metodo.'() no instancia la vista '.self::VISTA_BASE.'-'.$sufijo);
        }
        $this->addCase($results, 'tipoview_modal', substr_count($codigo, 'showVista(2,') === 2,
            'las dos vistas deben servirse como modal/parcial con showVista(2,)');
        $this->addCase($results, 'rutavista_resuelta', substr_count($codigo, 'returnRutaVista(__DIR__') === 2,
            'las vistas no resuelven su ruta con returnRutaVista()');
        $this->addCase($results, 'errores_showerror', substr_count($codigo, 'showError(2, $f3, $result)') === 2,
            'las vistas no manejan el fallo de consulta con showError()');
        $this->addCase($results, 'fallback_mergeresponses', substr_count($codigo, 'mergeResponses(') === 2,
            'las vistas no agrupan respuestas con mergeResponses()');
        $this->addCase($results, 'usuario_en_vistas', substr_count($codigo, 'getUserData($f3)') === 2,
            'las vistas no obtienen los datos del usuario');
        $this->addCase($results, 'nivel_por_tipo', substr_count($codigo, '$tsrxName = $this->tsrxName($idTipo);') === 2
            && substr_count($codigo, 'getArrLevel($f3, $tsrxName)') === 2,
            'las vistas deben resolver el permiso segun el tipo con tsrxName($idTipo)');

        /*====================================*/
        // Consultas de lectura
        /*====================================*/
        $this->addCase($results, 'new_documento_existe',
            str_contains($codigo, "'data'    => 'idFacturacion,idEntidad'") && str_contains($codigo, "'where'   => 'idFacturacion = ?'"),
            'New() no consulta el documento y su entidad');
        $this->addCase($results, 'new_guias_pendientes_de_la_entidad',
            str_contains($codigo, "'where'   => 'idDocumentos = ? AND idEstadoPago = ? AND idEntidad = ?'")
            && str_contains($codigo, "'params'  => [3, 1, \$rowData['data']['idEntidad'] ?? 0]"),
            'New() debe ofrecer las guias (documento 3) pendientes (estado 1) de la entidad leida de $rowData[\'data\']');
        $this->addCase($results, 'new_no_lee_entidad_plana', !str_contains($codigo, "\$rowData['idEntidad']"),
            'New() lee $rowData[\'idEntidad\'] (la fila vive en $rowData[\'data\']) y el selector de guias quedaria vacio');
        $this->addCase($results, 'updatelist_estado_pago',
            str_contains($codigo, "'data'    => 'idEstadoPago'") && str_contains($codigo, "'where'   => 'facturacion_listado_guias.idFacturacion = ?'"),
            'UpdateList() no consulta el estado de pago ni las guias del documento');
        $this->addCase($results, 'updatelist_une_documento',
            str_contains($codigo, 'LEFT JOIN facturacion_listado          ON facturacion_listado.idFacturacion         = facturacion_listado_guias.idFacturacionRel')
            && str_contains($codigo, 'LEFT JOIN core_documentos_mercantiles'),
            'UpdateList() no une la guia con su documento y el tipo de documento');
        $this->addCase($results, 'updatelist_ordenado', str_contains($codigo, "'order'   => 'facturacion_listado_guias.idExistencia ASC'"),
            'UpdateList() no ordena las guias');
        $this->addCase($results, 'consultas_limite', substr_count($codigo, 'ConfigAPP::APP["N_MaxItems"]') === 2,
            'se esperaban 2 consultas de lista acotadas por N_MaxItems');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                         CONTROLADOR: ESCRITURA (CRUD)                      */
    /******************************************************************************/
    /**
     * Verifica `Insert` y `Delete` SIN ejecutarlos: transaccion completa, rollback por paso,
     * recalculo del documento con `updateFact()` (estado de pago de la guia 2 al asociar y 1
     * al quitar), ID cifrado en el borrado y respuestas. Las guias no se editan: no hay `Update`.
     *
     * @return array Resultado por caso
     */
    public function controladorEscrituraTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Firma de los metodos de escritura (sin parametros) y ausencia de Update
        /*====================================*/
        foreach (['Insert', 'Delete'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 0, 0);
        }
        $this->addCase($results, 'sin_update', !method_exists(self::CONTROLADOR, 'Update'),
            'una guia asociada no se edita: no debe existir Update()');

        /*====================================*/
        // Tabla, chequeo de datos y campos
        /*====================================*/
        $this->addCase($results, 'tabla_guias', preg_match_all("/'table'\s*=>\s*'facturacion_listado_guias'/", $codigo) === 4,
            'se esperaban 4 usos de facturacion_listado_guias (lista, lectura previa, Insert y Delete)');
        $this->addCase($results, 'insert_datacheck', str_contains($codigo, '$this->dataCheck_1($_POST)'),
            'Insert() no valida con dataCheck_1($_POST)');
        $this->addCase($results, 'insert_campos',
            str_contains($codigo, "'data'      => 'idFacturacion,idFacturacionRel'") && str_contains($codigo, "'required'  => 'idFacturacion,idFacturacionRel'"),
            'Insert() no declara idFacturacion e idFacturacionRel como campos y obligatorios');
        $this->addCase($results, 'insert_base', str_contains($codigo, '$this->Base_insert('),
            'Insert() no escribe con Base_insert()');

        /*====================================*/
        // Delete: verbo, ID cifrado, lectura previa y borrado
        /*====================================*/
        $this->addCase($results, 'delete_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'DELETE'"),
            'Delete() no valida el verbo HTTP DELETE');
        $this->addCase($results, 'delete_id_cifrado', str_contains($codigo, "encryptDecrypt('decrypt', \$dataDelete['idExistencia'])"),
            'Delete() no descifra idExistencia');
        $this->addCase($results, 'delete_id_valido', str_contains($codigo, "isValidDecrypted(\$ExistenciaID, 'id')"),
            'Delete() no valida el ID descifrado');
        $this->addCase($results, 'delete_lectura_previa',
            str_contains($codigo, "'data'    => 'idFacturacion,idFacturacionRel'") && str_contains($codigo, "'where'   => 'idExistencia = ?'"),
            'Delete() no lee la guia (documento y guia relacionada) antes de borrar');
        $this->addCase($results, 'delete_donde', str_contains($codigo, "'where'       => 'idExistencia'"),
            'Delete() no delimita el borrado por idExistencia');
        $this->addCase($results, 'delete_sin_archivos', str_contains($codigo, "'files'       => ''"),
            'Delete() no declara que la guia no maneja archivos');
        $this->addCase($results, 'delete_base', str_contains($codigo, '$this->Base_delete('),
            'Delete() no borra con Base_delete()');

        /*====================================*/
        // Transaccion y recalculo del documento (Insert y Delete)
        /*====================================*/
        $this->addCase($results, 'transaccion_begin', substr_count($codigo, 'Base_transactionBegin()') === 2,
            'Insert y Delete deben abrir transaccion');
        $this->addCase($results, 'transaccion_commit', substr_count($codigo, 'Base_transactionCommit()') === 2,
            'Insert y Delete deben confirmar la transaccion');
        $this->addCase($results, 'transaccion_rollback', substr_count($codigo, 'Base_transactionRollback()') === 2,
            'quedan 2 rollback C intactos; el resto de fallos usa failOperation');
        $this->addCase($results, 'recalculo_insert',
            str_contains($codigo, "updateFact(2, \$_POST['idFacturacion'], \$this->getDBConn(), 2, \$_POST['idFacturacionRel'])"),
            'Insert() debe recalcular el documento marcando la guia como asociada (estado de pago 2)');
        $this->addCase($results, 'recalculo_delete',
            str_contains($codigo, "updateFact(2, \$rowFacturacion['data']['idFacturacion'], \$this->getDBConn(), 1, \$rowFacturacion['data']['idFacturacionRel'])"),
            'Delete() debe recalcular el documento devolviendo la guia a pendiente (estado de pago 1)');
        $this->addCase($results, 'recalculo_revisa_codigo', substr_count($codigo, "\$ResponseGestionDoc['code'] != 200") === 2,
            'se debe revertir si updateFact() no responde 200');
        $this->addCase($results, 'recalculo_misma_conexion', substr_count($codigo, '$this->getDBConn()') === 2,
            'updateFact() debe compartir la conexion de la transaccion');

        /*====================================*/
        // Respuestas al cliente
        /*====================================*/
        $this->addCase($results, 'respuesta_exito', substr_count($codigo, 'Response::success(') === 2,
            'se esperaban 2 Response::success() (Insert y Delete)');
        $this->addCase($results, 'respuesta_405', substr_count($codigo, "Response::error('Error en el Request Method', 405)") === 1,
            'Delete() debe responder 405 ante un verbo incorrecto');
        $this->addCase($results, 'respuesta_400', substr_count($codigo, "Response::error('Registro inválido', 400)") === 3,
            'New, UpdateList y Delete deben responder 400 ante un ID invalido');
        $this->addCase($results, 'respuesta_500', substr_count($codigo, 'failOperation(') === 3,
            'se esperaban 3 puntos failOperation (Insert 1 y Delete 2)');
        $this->addCase($results, 'respuesta_delegada', substr_count($codigo, 'Response::error($ResponseGestionDoc[\'message\'], $ResponseGestionDoc[\'code\']') === 2,
            'Insert y Delete deben propagar el codigo y mensaje de updateFact()');
        $this->addCase($results, 'id_descifrado_vistas', substr_count($codigo, "encryptDecrypt('decrypt', \$params['id'])") === 2,
            'New y UpdateList deben descifrar $params[\'id\']');
        $this->addCase($results, 'id_valido_total', substr_count($codigo, 'isValidDecrypted(') === 3,
            'se esperaban 3 validaciones isValidDecrypted() (New, UpdateList y Delete)');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                       CONTROLADOR: RUTAS E INSTALADOR                      */
    /******************************************************************************/
    /**
     * Verifica que las 8 rutas de `gestionDocumentosGuias` (4 de compras y 4 de ventas)
     * apunten a metodos reales con el tipo, verbo y nivel esperados: las rutas de ventas
     * deben usar los wrappers del tipo 2 y las de compras los del tipo 1.
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
            $esperadas[$nombre.'_nuevo']     = ['1 '.$base.'/guias/new/@id',        'gestionDocumentosGuias->New_'.$tipo,        2];
            $esperadas[$nombre.'_refrescar'] = ['1 '.$base.'/guias/updateList/@id', 'gestionDocumentosGuias->UpdateList_'.$tipo, 2];
            $esperadas[$nombre.'_crear']     = ['2 '.$base.'/guias',                'gestionDocumentosGuias->Insert',            2];
            $esperadas[$nombre.'_borrar']    = ['3 '.$base.'/guias',                'gestionDocumentosGuias->Delete',            2];
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

        $propias = array_filter($rutas, static function ($r) { return $r[2] === 'gestionDocumentosGuias'; });
        $this->addCase($results, 'rutas_propias_completas', count($propias) === 8,
            'se esperaban 8 rutas de '.self::CONTROLADOR.' y hay '.count($propias));
        foreach ($propias as $clave => $datos) {
            $verbo = (int)substr($clave, 0, 1);
            if (str_ends_with($datos[0], '->Insert')) {
                $this->addCase($results, 'verbo_insert_'.md5($clave), $verbo === 2, $datos[0].' debe publicarse con POST');
            }
            if (str_ends_with($datos[0], '->Delete')) {
                $this->addCase($results, 'verbo_borrado_'.md5($clave), $verbo === 3, $datos[0].' debe publicarse con DELETE');
            }
        }

        /*====================================*/
        // Ninguna ruta de guias debe usar el wrapper del tipo contrario a su ruta
        /*====================================*/
        $cruzadas = [];
        foreach ($propias as $clave => $datos) {
            $esVentas  = str_contains($clave, '/ventas/');
            $esCompras = str_contains($clave, '/compras/');
            if ($esVentas && preg_match('/_1$/', $datos[0])) { $cruzadas[] = $clave; }
            if ($esCompras && preg_match('/_2$/', $datos[0])) { $cruzadas[] = $clave; }
        }
        $this->addCase($results, 'rutas_sin_tipo_cruzado', count($cruzadas) === 0,
            'rutas con el wrapper del tipo contrario: '.implode(' | ', $cruzadas));

        /*====================================*/
        // Desinstalacion: el sub-recurso debe estar en RutaController()
        /*====================================*/
        $instalador = $this->codigoFuente($this->rutaModulo('../installer/gestionDocumentosInstaller.php'));
        $this->addCase($results, 'instalador_desinstala_subrecurso', str_contains($instalador, "\$RutaController .= ',\"gestionDocumentosGuias\"'"),
            'RutaController() no lista gestionDocumentosGuias y dejaria rutas huerfanas al desinstalar');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                PERMISOS                                    */
    /******************************************************************************/
    /**
     * Verifica la sesion, el nivel de acceso de Compras y Ventas (permisos heredados de
     * `gestionDocumentos`) y el registro de las rutas de guias en el router de F3.
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
        $esquema = [
            'compras_insert_router' => ['/'.self::RUTA_COMPRAS.'/guias', 'POST',   self::CONTROLADOR.'->Insert'],
            'ventas_insert_router'  => ['/'.self::RUTA_VENTAS.'/guias',  'POST',   self::CONTROLADOR.'->Insert'],
            'compras_delete_router' => ['/'.self::RUTA_COMPRAS.'/guias', 'DELETE', self::CONTROLADOR.'->Delete'],
            'ventas_delete_router'  => ['/'.self::RUTA_VENTAS.'/guias',  'DELETE', self::CONTROLADOR.'->Delete'],
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
     * Renderiza `UpdateList` y `formNew` de guias con datos sinteticos para compras y ventas:
     * las acciones de borrar solo existen cuando el documento esta pendiente (estado de pago 1),
     * el ID viaja cifrado y los formularios publican en la ruta del tipo con su verbo.
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function vistasGuiasTest($f3){

        $results = [];
        $BASE    = (string)$f3->get('BASE');
        if (trim($BASE) === '') {
            $BASE = '/admin';
        }

        /*====================================*/
        // Tabla de guias: documento pendiente (estado de pago 1) frente a otros estados
        /*====================================*/
        $rutaTabla = $this->rutaModulo('../views/'.self::VISTA_BASE.'-UpdateList.php');
        $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos(2, self::RUTA_VENTAS, 2, 1), $BASE, $f3);
        $this->addCase($results, 'tabla_render', $tabla !== '', 'UpdateList no produjo HTML');
        $this->addCase($results, 'tabla_datos', str_contains($tabla, 'Guia Despacho 900') && str_contains($tabla, '01-01-2026'),
            'la tabla no pinta el documento y la fecha de la guia');
        $this->addCase($results, 'tabla_monto_formateado', str_contains($tabla, '10.000'),
            'la tabla no formatea el valor con Valores()');
        $this->addCase($results, 'tabla_columnas',
            str_contains($tabla, '>Fecha<') && str_contains($tabla, '>Documento<') && str_contains($tabla, '>Valor<'),
            'la tabla no declara las columnas Fecha, Documento y Valor');
        $this->addCase($results, 'tabla_pendiente_ofrece_borrar', str_contains($tabla, 'tabGuiaDel(') && str_contains($tabla, '>Acciones<'),
            'con el documento pendiente la tabla debe ofrecer borrar la guia');
        $this->addCase($results, 'tabla_id_cifrado', !str_contains($tabla, "tabGuiaDel('55'"),
            'la tabla expone idExistencia sin cifrar');

        $pagado = $this->renderizarVista($rutaTabla, $this->datosSinteticos(2, self::RUTA_VENTAS, 2, 2), $BASE, $f3);
        $this->addCase($results, 'tabla_no_pendiente_sin_acciones',
            $pagado !== '' && str_contains($pagado, 'Guia Despacho 900') && !str_contains($pagado, 'tabGuiaDel(') && !str_contains($pagado, '>Acciones<'),
            'con el documento no pendiente la tabla ofrece borrar guias');

        $sinNumero = $this->datosSinteticos(2);
        $sinNumero['arrGuias'][0]['N_Doc'] = null;
        $tablaSinNumero = $this->renderizarVista($rutaTabla, $sinNumero, $BASE, $f3);
        $this->addCase($results, 'tabla_sin_numero_referencia', str_contains($tablaSinNumero, 'Guia Despacho nRef #40'),
            'sin N_Doc la tabla no muestra la referencia "nRef #<id guia>"');

        $vacio = $this->datosSinteticos(2);
        $vacio['arrGuias'] = [];
        $tablaVacia = $this->renderizarVista($rutaTabla, $vacio, $BASE, $f3);
        $this->addCase($results, 'tabla_vacia', $tablaVacia !== '' && str_contains($tablaVacia, '<tbody>') && !str_contains($tablaVacia, 'tabGuiaDel('),
            'la tabla sin datos pinta filas o no se dibuja');

        /*====================================*/
        // Formulario de alta (compras y ventas): selector de guias y ocultos
        /*====================================*/
        foreach ([2 => self::RUTA_VENTAS, 1 => self::RUTA_COMPRAS] as $tipo => $ruta) {
            $etiqueta = $tipo === 1 ? 'compras' : 'ventas';
            $html = $this->renderizarVista($this->rutaModulo('../views/'.self::VISTA_BASE.'-formNew.php'),
                $this->datosFormulario($ruta, $tipo), $BASE, $f3);
            $this->addCase($results, 'form_new_render_'.$etiqueta, $html !== '', 'formNew de '.$etiqueta.' no produjo HTML');
            $this->addCase($results, 'form_new_id_'.$etiqueta, str_contains($html, 'id="FormNewGuia"'),
                'formNew de '.$etiqueta.' no usa el id FormNewGuia');
            $this->addCase($results, 'form_new_selector_'.$etiqueta,
                str_contains($html, 'name="idFacturacionRel"') && str_contains($html, 'Guia Despacho nRef 39'),
                'formNew de '.$etiqueta.' no ofrece el catalogo de guias');
            $this->addCase($results, 'form_new_oculto_'.$etiqueta,
                (bool)preg_match('/name="idFacturacion"[^>]*value="41"|value="41"[^>]*name="idFacturacion"/', $html),
                'formNew de '.$etiqueta.' no envia idFacturacion (41) como campo oculto');
            $this->addCase($results, 'form_new_endpoint_'.$etiqueta,
                str_contains($html, $BASE.'/'.$ruta.'/guias\'') && str_contains($html, "let Metodo      = 'POST'"),
                'formNew de '.$etiqueta.' no publica en '.$ruta.'/guias con POST');
            $this->addCase($results, 'form_new_refresca_tabla_'.$etiqueta,
                str_contains($html, '#tabGuiaDataTable') && str_contains($html, $ruta.'/guias/updateList/'),
                'formNew de '.$etiqueta.' no refresca la tabla de guias al guardar');
            $this->addCase($results, 'form_new_id_cifrado_refresco_'.$etiqueta, !str_contains($html, '/guias/updateList/41'),
                'el refresco de la tabla de '.$etiqueta.' expone idFacturacion sin cifrar');
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos `tsrxName()`, `dataCheck_1()`, el cifrado de IDs y la
     * validacion de IDs descifrados.
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
        // dataCheck_1: contrato esperado por Base_insert
        /*====================================*/
        try {
            $reflector = new ReflectionMethod(self::CONTROLADOR, 'dataCheck_1');
            $reflector->setAccessible(true);
            $Post      = ['idFacturacion' => 41, 'idFacturacionRel' => 40];
            $DataCheck = $reflector->invoke($this->instanciaSinBD(), $Post);
            $this->addCase($results, 'datacheck_es_arreglo', is_array($DataCheck), 'dataCheck_1() no devolvio un arreglo');
            $this->addCase($results, 'datacheck_privado', $reflector->isPrivate(), 'dataCheck_1() debe ser privado');
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post,
                'dataCheck_1() no conserva el POST recibido');
            $this->addCase($results, 'datacheck_claves_contrato',
                isset($DataCheck['emptyData'], $DataCheck['ValidarNumero'], $DataCheck['ValidarEntero'], $DataCheck['ValidarLargoMinimoN'], $DataCheck['ValidarLargoMaximoPorCampo']),
                'dataCheck_1() no entrega las claves del contrato de CheckData');
            $this->addCase($results, 'datacheck_ids_enteros',
                trim((string)$DataCheck['ValidarEntero']) === 'idFacturacion,idFacturacionRel' && trim((string)$DataCheck['ValidarNumero']) === 'idFacturacion,idFacturacionRel',
                'dataCheck_1() no valida idFacturacion e idFacturacionRel como enteros');
            $this->addCase($results, 'datacheck_sin_otros_validadores',
                trim((string)$DataCheck['ValidarEmail']) === '' && trim((string)$DataCheck['ValidarFecha']) === '' && trim((string)$DataCheck['ValidarLargoMinimo']) === '',
                'dataCheck_1() valida email, fechas o textos que la guia no tiene');
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_es_arreglo', false, 'no se pudo ejecutar dataCheck_1() ('.$e->getMessage().')');
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
     * Revisa estaticamente las vistas de guias y el controlador: rutas relativas, rutas
     * absolutas, acceso a sesion, SQL embebido, cifrado de IDs y consultas preparadas.
     *
     * @return array Resultado por caso
     */
    public function seguridadVistasTest(){

        $results = [];

        /*====================================*/
        // Vistas involucradas
        /*====================================*/
        $arrVistas = glob($this->rutaModulo('../views/'.self::VISTA_BASE.'-*.php')) ?: [];
        $this->addCase($results, 'hay_vistas', count($arrVistas) === 2,
            'se esperaban 2 vistas de guias y se encontraron '.count($arrVistas));
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
            'UpdateList no cifra idExistencia en la accion de borrar');
        $this->addCase($results, 'tabla_fecha_estandar', str_contains($tabla, 'fechaEstandar('),
            'UpdateList no formatea la fecha con fechaEstandar()');
        $this->addCase($results, 'tabla_acciones_solo_pendiente', substr_count($tabla, "\$data['rowData']['idEstadoPago']==1") === 2,
            'las acciones de borrar solo deben mostrarse con el documento pendiente (cabecera y fila)');

        /*====================================*/
        // Controlador: SQL preparado y sin concatenacion de entrada
        /*====================================*/
        $controlador = $this->fuenteControlador();
        $this->addCase($results, 'controlador_sin_concat_sql', !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $controlador),
            'el controlador concatena superglobales en una cadena SQL');
        $this->addCase($results, 'controlador_parametros_preparados', preg_match_all("/'params'\s*=>/", $controlador) === 5,
            'las 5 consultas deben declarar la clave params (consultas preparadas)');
        $this->addCase($results, 'controlador_sin_get', !str_contains($controlador, '$_GET') && !str_contains($controlador, '$_REQUEST'),
            'el controlador lee $_GET/$_REQUEST; solo debe usar $_POST y parametros de ruta cifrados');

        /************************************/
        // Retorno los datos
        return $results;

    }

}
