<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class gestionDocumentosPagosTesting
 *
 * Pruebas del modulo `facturacion` - controlador `gestionDocumentosPagos` (pagos de un documento mercantil).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. Los textos de los casos
 *    correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Alcance: pruebas NO destructivas. `Insert()`, `Update()` y `Delete()` abren transaccion, escriben en
 * `facturacion_listado_pagos`, recalculan el estado de pago del documento (`updatePago()`) y terminan en
 * `Response::*()` (`exit`); `insertPago()` y `updatePago()` consultan y escriben en la base. NO se ejecutan: se
 * verifican por reflexion y lectura del fuente. Solo se ejecuta logica pura (`tsrxName`, `dataCheck_1/2`,
 * cifrado de IDs).
 *
 * El controlador reutiliza permisos y vistas de `gestionDocumentos` ($this->controllerName) y atiende dos
 * tipos con wrappers: tipo 1 = compras y tipo 2 = ventas. Un documento queda pagado (estado 2) cuando los
 * pagos igualan o superan su valor total; mientras esta pendiente (estado 1) se pueden editar y borrar pagos.
 *
 * @see skills/testing/SKILL.md
 */
class gestionDocumentosPagosTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR      = 'gestionDocumentosPagos';
    private const VISTA_BASE       = 'gestionDocumentos-Resumen-Pagos';
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
            'Fnc_ServerServer' => new FunctionsServerServer(),
            'arrDocumentoPago' => [['ID' => 1, 'Nombre' => 'Transferencia'], ['ID' => 2, 'Nombre' => 'Efectivo']],
            'arrPagos'         => [[
                'idPago'      => 66,
                'N_Doc'       => '77',
                'MontoPagado' => 50000,
                'FechaPago'   => '2026-01-03',
                'UsuarioPago' => 'Tester',
                'DocPago'     => 'Transferencia',
            ]],
            'rowData'          => [
                'idPago'          => 66,
                'idFacturacion'   => 41,
                'idEstadoPago'    => $estadoPago,
                'idDocumentoPago' => 1,
                'N_Doc'           => '77',
                'MontoPagado'     => 50000,
                'FechaPago'       => '2026-01-03',
                'UsuarioPago'     => 'Tester',
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
     * Verifica los archivos del controlador `gestionDocumentosPagos` y la convencion
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
            'testing'           => $this->rutaModulo('gestionDocumentosPagosTesting.php'),
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
        $this->addCase($results, 'clase_testing_cargada', class_exists('gestionDocumentosPagosTesting'),
            'la clase gestionDocumentosPagosTesting no esta disponible');
        $padre = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padre === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padre.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('gestionDocumentosPagosTesting') === 'ControllerBase',
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
            $this->addCase($results, 'vista_'.$metodo, str_contains($codigo, "\$this->controllerName.'-Resumen-Pagos-".$sufijo.".php'"),
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

        /*====================================*/
        // Consultas de lectura
        /*====================================*/
        $this->addCase($results, 'new_documento_existe',
            str_contains($codigo, "'table'   => 'facturacion_listado'") && str_contains($codigo, "'where'   => 'idFacturacion = ?'"),
            'New() no verifica que el documento exista');
        $this->addCase($results, 'catalogo_documentos_pago', str_contains($codigo, "'table'   => 'core_documentos_pago'"),
            'New() y GetID() deben cargar el catalogo de documentos de pago');
        $this->addCase($results, 'updatelist_estado_pago', str_contains($codigo, "'data'    => 'idEstadoPago'"),
            'UpdateList() no consulta el estado de pago que habilita las acciones');
        $this->addCase($results, 'updatelist_une_usuario_y_documento',
            str_contains($codigo, 'LEFT JOIN usuarios_listado     ON usuarios_listado.idUsuario            = facturacion_listado_pagos.idUsuario')
            && str_contains($codigo, 'LEFT JOIN core_documentos_pago ON core_documentos_pago.idDocumentoPago  = facturacion_listado_pagos.idDocumentoPago'),
            'UpdateList() no une el usuario y el documento de pago');
        $this->addCase($results, 'updatelist_orden', str_contains($codigo, "'order'   => 'facturacion_listado_pagos.idPago ASC'"),
            'UpdateList() no ordena los pagos cronologicamente');
        $this->addCase($results, 'getid_por_pago', str_contains($codigo, "'where'   => 'facturacion_listado_pagos.idPago = ?'"),
            'GetID() no consulta el pago por idPago');
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
     * Verifica `Insert`, `Update`, `Delete` y la logica `insertPago()`/`updatePago()` SIN
     * ejecutarlos: usuario de sesion, validacion de que el pago no supere el valor del documento,
     * transacciones (propias o la de la conexion externa), rollback por paso, recalculo del estado
     * de pago (2 pagado, 1 pendiente) y respuestas al cliente.
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
        $this->firmaMetodo($results, 'Delete', 0, 0);
        $this->firmaMetodo($results, 'insertPago', 1, 2);
        $this->firmaMetodo($results, 'updatePago', 1, 2);
        $reflector = new ReflectionMethod(self::CONTROLADOR, 'updatePago');
        $this->addCase($results, 'updatepago_retorno_array', $reflector->hasReturnType() && (string)$reflector->getReturnType() === 'array',
            'updatePago() debe declarar retorno array');
        foreach (['insertPago' => 1, 'updatePago' => 1] as $metodo => $posicion) {
            $parametros = (new ReflectionMethod(self::CONTROLADOR, $metodo))->getParameters();
            $this->addCase($results, 'conexion_opcional_'.$metodo,
                isset($parametros[$posicion]) && $parametros[$posicion]->isDefaultValueAvailable() && $parametros[$posicion]->getDefaultValue() === null,
                $metodo.'() debe recibir la conexion externa NewDBConn como parametro opcional (null)');
        }

        /*====================================*/
        // Tabla, chequeos y campos
        /*====================================*/
        $this->addCase($results, 'tabla_pagos', preg_match_all("/'table'\s*=>\s*'facturacion_listado_pagos'/", $codigo) === 6,
            'se esperaban 6 usos de facturacion_listado_pagos');
        $this->addCase($results, 'datacheck_1_en_escritura', substr_count($codigo, '$this->dataCheck_1(') === 2,
            'Update() e insertPago() deben validar el pago con dataCheck_1');
        $this->addCase($results, 'datacheck_2_en_estado', substr_count($codigo, '$this->dataCheck_2($arrTareas)') === 1,
            'updatePago() debe validar el estado de pago con dataCheck_2($arrTareas)');
        $this->addCase($results, 'insert_campos',
            str_contains($codigo, "'data'      => 'idFacturacion,idUsuario,idDocumentoPago,N_Doc,MontoPagado,FechaPago'")
            && str_contains($codigo, "'required'  => 'idFacturacion,idUsuario,idDocumentoPago,MontoPagado,FechaPago'"),
            'insertPago() no declara los campos y obligatorios del pago');
        $this->addCase($results, 'update_campos',
            str_contains($codigo, "'data'      => 'idPago,idFacturacion,idUsuario,idDocumentoPago,N_Doc,MontoPagado,FechaPago'")
            && str_contains($codigo, "'required'  => 'idPago,idFacturacion,idUsuario,idDocumentoPago,MontoPagado,FechaPago'")
            && str_contains($codigo, "'where'     => 'idPago'"),
            'Update() no declara los campos, obligatorios y la delimitacion por idPago');

        /*====================================*/
        // Insert y Update: usuario de sesion, verbo y delegacion
        /*====================================*/
        $this->addCase($results, 'usuario_de_sesion', substr_count($codigo, "\$_POST['idUsuario'] = \$f3->get('SESSION.DataInfo.UserID');") === 2,
            'Insert() y Update() deben tomar idUsuario de la sesion y no del formulario');
        $this->addCase($results, 'insert_delega_insertpago', str_contains($codigo, '$this->insertPago($_POST)'),
            'Insert() no delega en insertPago()');
        $this->addCase($results, 'update_verbo_post', str_contains($codigo, "REQUEST_METHOD'] !== 'POST'"),
            'Update() no valida el verbo HTTP POST');
        $this->addCase($results, 'update_base', str_contains($codigo, '$this->Base_update('),
            'Update() no escribe con Base_update()');

        /*====================================*/
        // El pago no puede superar el valor del documento (Update e insertPago)
        /*====================================*/
        $this->addCase($results, 'valida_sobrepago',
            substr_count($codigo, "\$rowData['data']['ValorTotal']<(\$rowData['data']['MontoPagado']+") === 2,
            'Update() e insertPago() deben rechazar un pago que supere el valor total del documento');
        // El monto debe ser numerico ANTES de compararlo con el saldo (Update e insertPago)
        $this->addCase($results, 'monto_numerico_antes_de_comparar',
            (bool)preg_match("/is_numeric\(\\\$_POST\['MontoPagado'\]\).*?Response::error\('El monto ingresado no es un número válido', 500\);.*?ValorTotal'\]<\(/s", $codigo)
            && (bool)preg_match("/is_numeric\(\\\$Data\['MontoPagado'\]\).*?'message' => 'El monto ingresado no es un número válido'.*?ValorTotal'\]<\(/s", $codigo)
            && substr_count($codigo, 'El monto ingresado no es un número válido') === 2,
            'Update() e insertPago() deben validar que MontoPagado sea numerico antes de compararlo con el saldo');
        $this->addCase($results, 'sobrepago_mensaje', substr_count($codigo, 'Ha ingresado un monto superior al valor total del documento') === 2,
            'el rechazo por monto superior debe informar el motivo');
        $this->addCase($results, 'update_excluye_pago_actual', str_contains($codigo, "AND idPago!='.(int)\$_POST['idPago']"),
            'Update() debe excluir el pago editado del acumulado pagado');

        /*====================================*/
        // Estado de pago: 2 si los pagos igualan o superan el total, 1 en otro caso
        /*====================================*/
        $this->addCase($results, 'estado_pagado',
            str_contains($codigo, "\$rowData['data']['ValorTotal']<=\$rowData['data']['MontoPagado']") && str_contains($codigo, '$idEstadoPago = 2;'),
            'updatePago() no marca el documento como pagado (2) cuando los pagos igualan o superan el total');
        $this->addCase($results, 'estado_pendiente', str_contains($codigo, '$idEstadoPago = 1;'),
            'updatePago() no devuelve el documento a pendiente (1) en otro caso');
        $this->addCase($results, 'estado_actualiza_documento',
            str_contains($codigo, "'data'      => 'idFacturacion,idEstadoPago,MontoPagado'") && str_contains($codigo, "'table'     => 'facturacion_listado'"),
            'updatePago() no actualiza idEstadoPago y MontoPagado del documento');
        $this->addCase($results, 'updatepago_conexion', substr_count($codigo, "'newBDConn' => \$DBConn") >= 2,
            'updatePago() debe operar sobre la conexion recibida');

        /*====================================*/
        // Delete: verbo, ID cifrado, lectura previa, borrado y recalculo
        /*====================================*/
        $this->addCase($results, 'delete_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'DELETE'"),
            'Delete() no valida el verbo HTTP DELETE');
        $this->addCase($results, 'delete_id_cifrado', str_contains($codigo, "encryptDecrypt('decrypt', \$dataDelete['idPago'])"),
            'Delete() no descifra idPago');
        $this->addCase($results, 'delete_id_valido', str_contains($codigo, "isValidDecrypted(\$PagoID, 'id')"),
            'Delete() no valida el ID descifrado');
        $this->addCase($results, 'delete_lectura_previa', str_contains($codigo, "'where'   => 'idPago = ?'"),
            'Delete() no lee el pago antes de borrar para recalcular el documento');
        $this->addCase($results, 'delete_donde', str_contains($codigo, "'where'       => 'idPago'"),
            'Delete() no delimita el borrado por idPago');
        $this->addCase($results, 'delete_sin_archivos', str_contains($codigo, "'files'       => ''"),
            'Delete() no declara que el pago no maneja archivos');
        $this->addCase($results, 'delete_recalcula', str_contains($codigo, "\$this->updatePago(\$rowFacturacion['data']['idFacturacion'], \$this->getDBConn())"),
            'Delete() no recalcula el estado de pago del documento');

        /*====================================*/
        // Transacciones: propias en Update/Delete; en insertPago solo sin conexion externa
        /*====================================*/
        $this->addCase($results, 'transaccion_begin', substr_count($codigo, 'Base_transactionBegin(') === 3,
            'se esperaban 3 aperturas de transaccion (Update, Delete e insertPago)');
        $this->addCase($results, 'transaccion_commit', substr_count($codigo, 'Base_transactionCommit(') === 3,
            'se esperaban 3 confirmaciones de transaccion');
        $this->addCase($results, 'transaccion_rollback', substr_count($codigo, 'Base_transactionRollback(') === 10,
            'quedan 10 rollback (C y lock FOR UPDATE intactos); A/B migrados a failOperation');
        $this->addCase($results, 'insertpago_transaccion_condicional', substr_count($codigo, 'if($NewDBConn === null){') === 3,
            'insertPago() debe abrir, revertir (lock FOR UPDATE) y cerrar transaccion solo si no recibe una conexion externa');
        $this->addCase($results, 'recalculo_revisa_codigo', substr_count($codigo, "\$ResponseUp['code'] != 200") === 3
            && substr_count($codigo, "\$ResponsePago['code'] != 200") === 1,
            'se debe revertir si updatePago() no responde 200');

        /*====================================*/
        // Respuestas al cliente
        /*====================================*/
        $this->addCase($results, 'respuesta_exito', substr_count($codigo, 'Response::success(') === 3,
            'se esperaban 3 Response::success() (Insert, Update y Delete)');
        $this->addCase($results, 'respuesta_405', substr_count($codigo, "Response::error('Error en el Request Method', 405)") === 2,
            'Update() y Delete() deben responder 405 ante un verbo incorrecto');
        $this->addCase($results, 'respuesta_400', substr_count($codigo, "Response::error('Registro inválido', 400)") === 4,
            'New, UpdateList, GetID y Delete deben responder 400 ante un ID invalido');
        $this->addCase($results, 'respuesta_500', substr_count($codigo, 'failOperation(') === 4,
            'se esperaban 4 puntos failOperation en las rutas HTTP (Insert, Update x2 y Delete)');
        // Update(): si falla la lectura previa del documento debe responder error HTTP, no devolver un array
        $iniUpdate  = strpos($codigo, 'public function Update(');
        $finUpdate  = strpos($codigo, 'public function Delete(');
        $cuerpoUpd  = ($iniUpdate !== false && $finUpdate !== false && $finUpdate > $iniUpdate) ? substr($codigo, $iniUpdate, $finUpdate - $iniUpdate) : '';
        $this->addCase($results, 'update_lectura_previa_responde_error',
            $cuerpoUpd !== ''
            && substr_count($cuerpoUpd, "failOperation(\$rowData)") === 1
            && !str_contains($cuerpoUpd, 'return ['),
            'Update() debe responder failOperation (500) si falla la lectura previa y no devolver un array');
        $this->addCase($results, 'id_descifrado_vistas', substr_count($codigo, "encryptDecrypt('decrypt', \$params['id'])") === 3,
            'New, UpdateList y GetID deben descifrar $params[\'id\']');
        $this->addCase($results, 'id_valido_total', substr_count($codigo, 'isValidDecrypted(') === 4,
            'se esperaban 4 validaciones isValidDecrypted() (New, UpdateList, GetID y Delete)');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                       CONTROLADOR: RUTAS E INSTALADOR                      */
    /******************************************************************************/
    /**
     * Verifica que las 12 rutas de `gestionDocumentosPagos` (6 de compras y 6 de ventas)
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
            $esperadas[$nombre.'_nuevo']     = ['1 '.$base.'/pagos/new/@id',        'gestionDocumentosPagos->New_'.$tipo,        2];
            $esperadas[$nombre.'_refrescar'] = ['1 '.$base.'/pagos/updateList/@id', 'gestionDocumentosPagos->UpdateList_'.$tipo, 2];
            $esperadas[$nombre.'_getid']     = ['1 '.$base.'/pagos/getID/@id',      'gestionDocumentosPagos->GetID_'.$tipo,      2];
            $esperadas[$nombre.'_crear']     = ['2 '.$base.'/pagos',                'gestionDocumentosPagos->Insert',            2];
            $esperadas[$nombre.'_editar']    = ['2 '.$base.'/pagos/update',         'gestionDocumentosPagos->Update',            2];
            $esperadas[$nombre.'_borrar']    = ['3 '.$base.'/pagos',                'gestionDocumentosPagos->Delete',            2];
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

        $propias = array_filter($rutas, static function ($r) { return $r[2] === 'gestionDocumentosPagos'; });
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
        $this->addCase($results, 'instalador_desinstala_subrecurso', str_contains($instalador, "\$RutaController .= ',\"gestionDocumentosPagos\"'"),
            'RutaController() no lista gestionDocumentosPagos y dejaria rutas huerfanas al desinstalar');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                PERMISOS                                    */
    /******************************************************************************/
    /**
     * Verifica la sesion, el nivel de acceso de Compras y Ventas (permisos heredados de
     * `gestionDocumentos`) y el registro de las rutas de pagos en el router de F3.
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
            $esquema[$nombre.'_insert_router'] = ['/'.$ruta.'/pagos',        'POST',   self::CONTROLADOR.'->Insert'];
            $esquema[$nombre.'_update_router'] = ['/'.$ruta.'/pagos/update', 'POST',   self::CONTROLADOR.'->Update'];
            $esquema[$nombre.'_delete_router'] = ['/'.$ruta.'/pagos',        'DELETE', self::CONTROLADOR.'->Delete'];
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
     * Renderiza `UpdateList`, `formNew` y `formEdit` de pagos para compras y ventas: las acciones
     * de editar y borrar solo existen con el documento pendiente (estado de pago 1), el ID viaja
     * cifrado y los formularios publican en la ruta del tipo con su verbo.
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function vistasPagosTest($f3){

        $results = [];
        $BASE    = (string)$f3->get('BASE');
        if (trim($BASE) === '') {
            $BASE = '/admin';
        }

        /*====================================*/
        // Tabla de pagos: documento pendiente (estado de pago 1) frente a otros estados
        /*====================================*/
        $rutaTabla = $this->rutaModulo('../views/'.self::VISTA_BASE.'-UpdateList.php');
        foreach (self::NIVELES as $nivel) {
            $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos($nivel), $BASE, $f3);
            $this->addCase($results, 'tabla_render_nivel'.$nivel, $tabla !== '', 'UpdateList no produjo HTML con nivel '.$nivel);
            $this->addCase($results, 'tabla_acciones_nivel'.$nivel,
                str_contains($tabla, 'tabPagoEdit(') && str_contains($tabla, 'tabPagoDel('),
                'con el documento pendiente la tabla no ofrece editar y borrar con nivel '.$nivel);
        }
        $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos(2, self::RUTA_VENTAS, 2, 1), $BASE, $f3);
        $this->addCase($results, 'tabla_datos',
            str_contains($tabla, 'Tester') && str_contains($tabla, 'Transferencia 77') && str_contains($tabla, '03-01-2026'),
            'la tabla no pinta usuario, documento de pago y fecha formateada');
        $this->addCase($results, 'tabla_monto_formateado', str_contains($tabla, '50.000'),
            'la tabla no formatea el monto con Valores()');
        $this->addCase($results, 'tabla_columnas',
            str_contains($tabla, '>Fecha Pago<') && str_contains($tabla, '>Usuario Pago<') && str_contains($tabla, '>Documento Pago<')
            && str_contains($tabla, '>Monto pago<') && str_contains($tabla, '>Acciones<'),
            'la tabla no declara las columnas Fecha Pago, Usuario Pago, Documento Pago, Monto pago y Acciones');
        $this->addCase($results, 'tabla_id_cifrado',
            !str_contains($tabla, "tabPagoEdit('66')") && !str_contains($tabla, "tabPagoDel( '66'"),
            'la tabla expone idPago sin cifrar');

        foreach ([2 => 'pagado', 3 => 'anulado'] as $estado => $nombre) {
            $noPendiente = $this->renderizarVista($rutaTabla, $this->datosSinteticos(4, self::RUTA_VENTAS, 2, $estado), $BASE, $f3);
            $this->addCase($results, 'tabla_'.$nombre.'_sin_acciones',
                $noPendiente !== '' && str_contains($noPendiente, 'Transferencia 77') && !str_contains($noPendiente, 'tabPagoEdit(')
                && !str_contains($noPendiente, 'tabPagoDel(') && !str_contains($noPendiente, '>Acciones<'),
                'con el estado de pago '.$estado.' la tabla ofrece editar o borrar pagos');
        }

        $vacio = $this->datosSinteticos(2);
        $vacio['arrPagos'] = [];
        $tablaVacia = $this->renderizarVista($rutaTabla, $vacio, $BASE, $f3);
        $this->addCase($results, 'tabla_vacia', $tablaVacia !== '' && str_contains($tablaVacia, '<tbody>') && !str_contains($tablaVacia, 'tabPagoEdit('),
            'la tabla sin datos pinta filas o no se dibuja');

        $comillas = $this->datosSinteticos(2);
        $comillas['arrPagos'][0]['DocPago'] = "Pago d'prueba";
        $tablaComillas = $this->renderizarVista($rutaTabla, $comillas, $BASE, $f3);
        $this->addCase($results, 'tabla_escapa_comillas', str_contains($tablaComillas, "Pago d\\'prueba 77"),
            'la etiqueta del borrado no escapa las comillas simples');

        /*====================================*/
        // Formularios de alta y edicion para compras y ventas
        /*====================================*/
        foreach ([2 => self::RUTA_VENTAS, 1 => self::RUTA_COMPRAS] as $tipo => $ruta) {
            $etiqueta = $tipo === 1 ? 'compras' : 'ventas';
            $html = $this->renderizarVista($this->rutaModulo('../views/'.self::VISTA_BASE.'-formNew.php'),
                $this->datosSinteticos(2, $ruta, $tipo), $BASE, $f3);
            $this->addCase($results, 'form_new_render_'.$etiqueta, $html !== '', 'formNew de '.$etiqueta.' no produjo HTML');
            $this->addCase($results, 'form_new_id_'.$etiqueta, str_contains($html, 'id="FormNewPago"'),
                'formNew de '.$etiqueta.' no usa el id FormNewPago');
            $this->addCase($results, 'form_new_campos_'.$etiqueta,
                str_contains($html, 'name="idDocumentoPago"') && str_contains($html, 'name="N_Doc"') && str_contains($html, 'name="MontoPagado"')
                && str_contains($html, 'Efectivo'),
                'formNew de '.$etiqueta.' no declara el documento de pago, el numero y el monto');
            $this->addCase($results, 'form_new_ocultos_'.$etiqueta,
                (bool)preg_match('/name="idFacturacion"[^>]*value="41"|value="41"[^>]*name="idFacturacion"/', $html)
                && (bool)preg_match('/name="FechaPago"[^>]*value="\d{4}-\d{2}-\d{2}"|value="\d{4}-\d{2}-\d{2}"[^>]*name="FechaPago"/', $html)
                && !str_contains($html, 'name="idUsuario"') && !str_contains($html, 'name="idPago"'),
                'formNew de '.$etiqueta.' debe enviar idFacturacion y la fecha del servidor, y no idUsuario ni idPago');
            $this->addCase($results, 'form_new_endpoint_'.$etiqueta,
                str_contains($html, $BASE.'/'.$ruta.'/pagos\'') && str_contains($html, "let Metodo      = 'POST'"),
                'formNew de '.$etiqueta.' no publica en '.$ruta.'/pagos con POST');
            // Un pago puede dejar el documento pagado: al guardar se recarga el resumen completo (ID cifrado)
            $this->addCase($results, 'form_new_recarga_resumen_'.$etiqueta,
                str_contains($html, "Destino:'".$BASE.'/'.$ruta.'/resumen/') && !str_contains($html, '/resumen/41'),
                'formNew de '.$etiqueta.' no recarga el resumen del documento con el ID cifrado al guardar');

            $html = $this->renderizarVista($this->rutaModulo('../views/'.self::VISTA_BASE.'-formEdit.php'),
                $this->datosSinteticos(2, $ruta, $tipo), $BASE, $f3);
            $this->addCase($results, 'form_edit_render_'.$etiqueta, $html !== '', 'formEdit de '.$etiqueta.' no produjo HTML');
            $this->addCase($results, 'form_edit_id_'.$etiqueta, str_contains($html, 'id="FormEditPago"'),
                'formEdit de '.$etiqueta.' no usa el id FormEditPago');
            $this->addCase($results, 'form_edit_ocultos_'.$etiqueta,
                str_contains($html, 'name="idPago"') && str_contains($html, 'name="idFacturacion"') && str_contains($html, 'name="FechaPago"'),
                'formEdit de '.$etiqueta.' no envia idPago, idFacturacion y FechaPago');
            $this->addCase($results, 'form_edit_informativos_'.$etiqueta,
                str_contains($html, 'name="UsuarioPagoFake"') && str_contains($html, 'name="FechaPagoFake"') && str_contains($html, 'Tester'),
                'formEdit de '.$etiqueta.' no muestra el usuario y la fecha del pago como datos informativos');
            $this->addCase($results, 'form_edit_precarga_'.$etiqueta, (bool)preg_match('/name="MontoPagado"[^>]*value="50000"/', $html),
                'formEdit de '.$etiqueta.' no precarga el monto del pago');
            $this->addCase($results, 'form_edit_endpoint_'.$etiqueta,
                str_contains($html, $BASE.'/'.$ruta.'/pagos/update') && str_contains($html, "let Metodo      = 'POST'"),
                'formEdit de '.$etiqueta.' no publica en '.$ruta.'/pagos/update con POST');
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos `tsrxName()`, `dataCheck_1/2()`, el cifrado de IDs y la
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
        // dataCheck_1 (pago) y dataCheck_2 (estado de pago del documento)
        /*====================================*/
        try {
            $controlador = $this->instanciaSinBD();
            $esperados = [
                1 => ['ValidarEntero' => ['idPago', 'idFacturacion', 'idUsuario', 'idDocumentoPago'],
                      'ValidarNumero' => ['idPago', 'idFacturacion', 'idUsuario', 'idDocumentoPago', 'N_Doc', 'MontoPagado'],
                      'ValidarFecha'  => ['FechaPago']],
                2 => ['ValidarEntero' => ['idFacturacion', 'idEstadoPago'],
                      'ValidarNumero' => ['idFacturacion', 'idEstadoPago', 'MontoPagado']],
            ];
            $Post = ['idFacturacion' => 41, 'MontoPagado' => 50000];
            foreach ($esperados as $n => $campos) {
                $metodo    = 'dataCheck_'.$n;
                $reflector = new ReflectionMethod(self::CONTROLADOR, $metodo);
                $reflector->setAccessible(true);
                $DataCheck = $reflector->invoke($controlador, $Post);
                $this->addCase($results, $metodo.'_privado', $reflector->isPrivate(), $metodo.'() debe ser privado');
                $this->addCase($results, $metodo.'_post', ($DataCheck['Post'] ?? null) === $Post,
                    $metodo.'() no conserva el POST recibido');
                $this->addCase($results, $metodo.'_claves_contrato',
                    isset($DataCheck['emptyData'], $DataCheck['ValidarNumero'], $DataCheck['ValidarEntero'], $DataCheck['ValidarFecha'], $DataCheck['ValidarLargoMinimoN'], $DataCheck['ValidarLargoMaximoPorCampo']),
                    $metodo.'() no entrega las claves del contrato de CheckData');
                foreach ($campos as $clave => $lista) {
                    $presentes = array_map('trim', explode(',', (string)($DataCheck[$clave] ?? '')));
                    $faltan    = array_diff($lista, $presentes);
                    $this->addCase($results, $metodo.'_'.$clave, count($faltan) === 0,
                        $metodo.'() no incluye en '.$clave.': '.implode(', ', $faltan));
                }
            }
            $dc2 = new ReflectionMethod(self::CONTROLADOR, 'dataCheck_2');
            $dc2->setAccessible(true);
            $c2 = $dc2->invoke($controlador, []);
            $this->addCase($results, 'dataCheck_2_sin_fechas', trim((string)$c2['ValidarFecha']) === '',
                'dataCheck_2() valida fechas que el estado de pago no tiene');
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_ejecucion', false, 'no se pudo ejecutar dataCheck ('.$e->getMessage().')');
        }

        /*====================================*/
        // Cifrado de identificadores: ida y vuelta estable y rechazo de basura
        /*====================================*/
        try {
            $Codification = new FunctionsSecurityCodification();
            $cifrado      = $Codification->encryptDecrypt('encrypt', '66');
            $this->addCase($results, 'cifrado_genera_dato',
                ($cifrado['success'] ?? false) === true && !empty($cifrado['data']),
                'encryptDecrypt(encrypt) no produjo un valor cifrado');
            $descifrado = $Codification->encryptDecrypt('decrypt', (string)($cifrado['data'] ?? ''));
            $this->addCase($results, 'cifrado_ida_y_vuelta',
                ($descifrado['success'] ?? false) === true && (string)($descifrado['data'] ?? '') === '66',
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
     * Revisa estaticamente las vistas de pagos y el controlador: rutas relativas, rutas
     * absolutas, acceso a sesion, SQL embebido, cifrado de IDs, consultas preparadas e
     * interpolacion de datos del cliente en subselects SQL (deben forzarse a entero).
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
            'se esperaban 3 vistas de pagos y se encontraron '.count($arrVistas));
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
        $tabla = $this->codigoFuente($this->rutaModulo('../views/'.self::VISTA_BASE.'-UpdateList.php'));
        $this->addCase($results, 'tabla_escape_etiqueta', str_contains($tabla, "addslashes(\$crud['DocPago']"),
            'UpdateList no escapa la etiqueta que viaja al JavaScript');
        $this->addCase($results, 'tabla_id_cifrado', str_contains($tabla, "encryptDecrypt('encrypt', \$crud['idPago'])"),
            'UpdateList no cifra idPago en las acciones');
        $this->addCase($results, 'tabla_fecha_estandar', str_contains($tabla, 'fechaEstandar('),
            'UpdateList no formatea la fecha con fechaEstandar()');
        $this->addCase($results, 'tabla_acciones_solo_pendiente', substr_count($tabla, "\$data['rowData']['idEstadoPago']==1") === 2,
            'las acciones solo deben mostrarse con el documento pendiente (cabecera y fila)');

        /*====================================*/
        // Controlador: SQL preparado y sin concatenacion de entrada del cliente
        /*====================================*/
        $controlador = $this->fuenteControlador();
        $this->addCase($results, 'controlador_sin_concat_sql', !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $controlador),
            'el controlador concatena superglobales en una cadena SQL sin forzar entero');
        $this->addCase($results, 'controlador_sin_concat_datos', !preg_match('/\'\s*\.\s*\$(Data|dataDelete|PostData)\[/', $controlador),
            'el controlador concatena el arreglo de datos del cliente en una cadena SQL sin forzar entero');
        $this->addCase($results, 'controlador_sin_concat_id', !preg_match('/\'\s*\.\s*\$FacturacionID\s*\./', $controlador),
            'updatePago() concatena FacturacionID en el SQL sin forzar entero');
        $this->addCase($results, 'subselects_con_cast_entero',
            substr_count($controlador, "WHERE idFacturacion='.(int)") === 3 && substr_count($controlador, "AND idPago!='.(int)\$_POST['idPago']") === 1,
            'los 3 subselects de MontoPagado deben forzar a entero idFacturacion e idPago antes de interpolarlos');
        $this->addCase($results, 'controlador_parametros_preparados', preg_match_all("/'params'\s*=>/", $controlador) === 10,
            'las 10 consultas con filtro deben declarar la clave params (consultas preparadas)');
        $this->addCase($results, 'controlador_sin_get', !str_contains($controlador, '$_GET') && !str_contains($controlador, '$_REQUEST'),
            'el controlador lee $_GET/$_REQUEST; solo debe usar $_POST y parametros de ruta cifrados');

        /************************************/
        // Retorno los datos
        return $results;

    }

}
