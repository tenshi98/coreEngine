<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class serviciosListadoObservacionesTesting
 *
 * Pruebas del modulo `servicios` - controlador `serviciosListadoObservaciones`
 * (observaciones anidadas bajo el servicio). Etapa servicios / Fase 4 de 4.
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. El normalizador
 *    `sistemaTesteos::normalizeResult()` marca EXITOSA o ERROR segun el contenido, por lo que
 *    los textos de los casos correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Nota de diseno: este controlador fija `$this->controllerName = 'serviciosListado'` para
 * resolver los permisos (raiz compartida con el servicio) y las vistas se llaman
 * `serviciosListado-Resumen-Observaciones-*.php`. Las rutas cuelgan de
 * `administracion/servicios/listado/observaciones`.
 *
 * Alcance: pruebas NO destructivas. `Insert()`, `Update()` y `Delete()` terminan en
 * `Response::success()`/`Response::error()` (que ejecutan `exit`) y escriben en base de
 * datos, por lo que NO se invocan: se verifican por reflexion y lectura del fuente. El
 * instalador se lee con `newInstanceWithoutConstructor`, sin ejecutar
 * `InstallModule`/`UninstallModule`.
 *
 * @see skills/testing/SKILL.md
 */
class serviciosListadoObservacionesTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR          = 'serviciosListadoObservaciones';
    // El controlador comparte la raiz de permisos con el listado de servicios
    private const CONTROLADOR_PERMISOS = 'serviciosListado';
    private const RUTA_WEB             = 'administracion/servicios/listado';
    private const RUTA_SUB             = 'administracion/servicios/listado/observaciones';
    // Prefijo real de las vistas del sub-recurso
    private const VISTA_BASE           = 'serviciosListado-Resumen-Observaciones';
    private const TABLA                = 'servicios_listado_observaciones';
    private const NIVELES              = [1, 2, 3, 4];
    /*=========== Controladores del modulo (instalador) ===========*/
    private const CONTROLADORES        = [
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
    // Ruta absoluta dentro del modulo (la carpeta testing/ es la base)
    /*******************************************************************/
    private function rutaModulo(string $sub = ''): string{
        return rtrim(__DIR__.'/'.$sub, '/');
    }

    /*******************************************************************/
    // Ruta de una vista del sub-recurso (sufijo: UpdateList, View, ...)
    /*******************************************************************/
    private function rutaVista(string $sufijo): string{
        return $this->rutaModulo('../views/'.self::VISTA_BASE.'-'.$sufijo.'.php');
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
    // Cuerpo (fuente) de un metodo del controlador, por reflexion
    /*******************************************************************/
    private function cuerpoMetodo(string $metodo): string{
        if (!method_exists(self::CONTROLADOR, $metodo)) {
            return '';
        }
        $reflector = new ReflectionMethod(self::CONTROLADOR, $metodo);
        $lineas    = file($reflector->getFileName()) ?: [];
        $inicio    = $reflector->getStartLine() - 1;
        $largo     = $reflector->getEndLine() - $inicio;
        return implode('', array_slice($lineas, $inicio, $largo));
    }

    /*******************************************************************/
    // Parsea getRouteDefinitions() del instalador a un mapa indexado
    // por verbo + RutaWeb (se agregan los tipos 1..N).
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
    // Renderiza una vista con datos sinteticos y devuelve el HTML. El CWD
    // se mueve a admin/public porque las plantillas de los inputs se
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
    // Contrato de presentacion de un metodo (vista, errores y usuario),
    // acotado al cuerpo del propio metodo.
    /*******************************************************************/
    private function contratoPresentacion(array &$results, string $metodo, string $vista, string $tipoVista){
        $codigo = $this->cuerpoMetodo($metodo);
        $this->addCase($results, 'vista_'.$metodo,
            str_contains($codigo, "'-Resumen-Observaciones".$vista."'") || str_contains($codigo, "'/'.\$this->controllerName.'-Resumen-Observaciones".$vista."'"),
            $metodo.'() no instancia la vista Resumen-Observaciones'.$vista);
        $this->addCase($results, 'tipoview_'.$metodo, str_contains($codigo, 'showVista('.$tipoVista.','),
            $metodo.'() no usa showVista('.$tipoVista.')');
        $this->addCase($results, 'rutavista_'.$metodo, str_contains($codigo, 'returnRutaVista('),
            $metodo.'() no resuelve la ruta de la vista');
        $this->addCase($results, 'rutaruta_'.$metodo, str_contains($codigo, '$this->controllerName'),
            $metodo.'() no referencia $this->controllerName');
        $this->addCase($results, 'errores_'.$metodo, str_contains($codigo, 'showError(2, $f3, $result)'),
            $metodo.'() no maneja la ruta de presentacion con showError()');
        $this->addCase($results, 'usuario_'.$metodo, str_contains($codigo, 'getUserData($f3)'),
            $metodo.'() no obtiene los datos del usuario');
        $this->addCase($results, 'permisos_'.$metodo, str_contains($codigo, 'getArrLevel($f3, $this->controllerName)'),
            $metodo.'() no resuelve el nivel con getArrLevel($f3, $this->controllerName)');
    }

    /*******************************************************************/
    // Datos sinteticos usados por las vistas del sub-recurso
    /*******************************************************************/
    // Datos sinteticos usados por las vistas del sub-recurso
    /*******************************************************************/
    private function datosSinteticos(int $nivel): array{
        $observacion = [
            'idObservaciones' => 9,
            'idServicio'   => 3,
            'Observacion'     => 'Observacion de prueba del servicio',
            'FechaCreacion'   => '2026-10-01',
            'FechaEdicion'    => null,
            'Usuario'         => 'Usuario Creador',
            'UsuarioEdicion'  => null,
        ];
        return [
            'UserData'         => [
                'MainPathUrl'          => '/admin/public/upload/',
                'sistemaModalSubtitle' => 2,
                'sistemaModalCloseBTN' => 2,
                'UserType'             => 2,
            ],
            'UserAccess'       => ['LevelAccess' => $nivel, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_FormInputs'   => new UIFormInputs(),
            'Fnc_Codification' => new FunctionsSecurityCodification(),
            'Fnc_DataDate'     => new FunctionsDataDate(),
            'Fnc_ServerServer' => new FunctionsServerServer(),
            'Fnc_WidgetsCommon'=> new UIWidgetsCommon(),
            'Fnc_WidgetsViews' => new UIWidgetsViews(),
            'arrObservaciones' => [$observacion],
            'rowData'          => $observacion,
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica los archivos obligatorios del sub-recurso: controlador, las
     * 4 vistas `Resumen-Observaciones`, instalador, fichero de pruebas,
     * `.htaccess` y herencias.
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
            str_contains($fuente, "\$this->controllerName = '".self::CONTROLADOR_PERMISOS."'"),
            self::CONTROLADOR.' no comparte la raiz de permisos '.self::CONTROLADOR_PERMISOS);

        /*====================================*/
        // Vistas del sub-recurso (nombres reales Resumen-Observaciones)
        /*====================================*/
        $arrVistas = ['formEdit', 'formNew', 'UpdateList', 'View'];
        $this->addCase($results, 'hay_vistas',
            count(glob($this->rutaModulo('../views/'.self::VISTA_BASE.'-*.php'))) === count($arrVistas),
            'el sub-recurso debe tener exactamente '.count($arrVistas).' vistas '.self::VISTA_BASE.'-*');
        foreach ($arrVistas as $vista) {
            $this->addCase($results, 'vista_'.$vista, is_file($this->rutaVista($vista)),
                'falta la vista '.self::VISTA_BASE.'-'.$vista.'.php');
        }
        $this->addCase($results, 'vistas_no_con_nombre_de_controlador',
            count(glob($this->rutaModulo('../views/'.self::CONTROLADOR.'-*.php'))) === 0,
            'las vistas deben llamarse con la raiz '.self::CONTROLADOR_PERMISOS.', no '.self::CONTROLADOR);

        /*====================================*/
        // Instalador
        /*====================================*/
        $this->addCase($results, 'instalador_existe', is_file($this->rutaModulo('../installer/serviciosInstaller.php')),
            'no existe installer/serviciosInstaller.php');
        $this->addCase($results, 'instalador_clase', class_exists('serviciosInstaller'),
            'PHP no declara la clase serviciosInstaller');
        $this->addCase($results, 'instalador_herencia',
            is_subclass_of('serviciosInstaller', 'ControllerInstaller'),
            'serviciosInstaller no extiende ControllerInstaller');
        $instalador = $this->fuenteInstalador();
        foreach (['ListDataModule', 'InstallModule', 'UninstallModule', 'getRouteDefinitions', 'listTables'] as $metodo) {
            $this->addCase($results, 'instalador_metodo_'.$metodo, str_contains($instalador, 'function '.$metodo.'('),
                'el instalador no declara '.$metodo.'()');
        }

        /*====================================*/
        // Fichero de pruebas y .htaccess
        /*====================================*/
        $this->addCase($results, 'testing_existe', is_file($this->rutaModulo(__CLASS__.'.php')),
            'no existe testing/'.__CLASS__.'.php');
        $this->addCase($results, 'testing_clase', class_exists(__CLASS__),
            'PHP no declara la clase '.__CLASS__);
        $this->addCase($results, 'testing_herencia', is_subclass_of(__CLASS__, 'ControllerBase'),
            __CLASS__.' no extiende ControllerBase');
        foreach (['..', '../controller', '../views', '../installer', '.'] as $carpeta) {
            $ruta = $this->rutaModulo($carpeta.'/.htaccess');
            $this->addCase($results, 'htaccess_'.md5($carpeta), is_file($ruta)
                && str_contains($this->codigoFuente($ruta), 'deny from all'),
                'falta .htaccess con deny from all en '.$carpeta);
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              CONTROLADOR                                    */
    /******************************************************************************/
    /**
     * Revisa por reflexion las firmas de los metodos de vista (`New`,
     * `UpdateList`, `View`, `GetID`), su contrato de presentacion y las
     * consultas que alimentan cada vista.
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];

        /*====================================*/
        // Firmas de los metodos
        /*====================================*/
        foreach (['New', 'UpdateList', 'View', 'GetID'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 2, 2);
        }

        /*====================================*/
        // Contrato de presentacion (vista Modal tipo 2)
        /*====================================*/
        $this->contratoPresentacion($results, 'New',        '-formNew.php',    '2');
        $this->contratoPresentacion($results, 'UpdateList', '-UpdateList.php', '2');
        $this->contratoPresentacion($results, 'View',       '-View.php',       '2');
        $this->contratoPresentacion($results, 'GetID',      '-formEdit.php',   '2');

        /*====================================*/
        // Consultas de cada vista
        /*====================================*/
        $new = $this->cuerpoMetodo('New');
        $this->addCase($results, 'new_valida_servicio',
            str_contains($new, "'table'   => 'servicios_listado'") && str_contains($new, "'where'   => 'idServicio = ?'"),
            'New() no comprueba que el servicio exista');
        $this->addCase($results, 'new_funciones',
            str_contains($new, "'Fnc_ServerServer'") && str_contains($new, "'Fnc_FormInputs'") && str_contains($new, "'Fnc_Codification'"),
            'New() no entrega Fnc_ServerServer, Fnc_FormInputs y Fnc_Codification a la vista');

        $lista = $this->cuerpoMetodo('UpdateList');
        $this->addCase($results, 'lista_filtra_servicio',
            str_contains($lista, "'where'   => 'servicios_listado_observaciones.idServicio = ?'")
            && str_contains($lista, "'params'  => [\$ServicioID['data']]"),
            'UpdateList() no filtra las observaciones por el servicio descifrado');
        $this->addCase($results, 'lista_tabla', str_contains($lista, "'table'   => '".self::TABLA."'"),
            'UpdateList() no consulta '.self::TABLA);
        $this->addCase($results, 'lista_autor_y_editor',
            str_contains($lista, 'usuarios_listado.idUsuario') && str_contains($lista, 'usuarios_edicion.idUsuario')
            && str_contains($lista, 'AS Usuario') && str_contains($lista, 'AS UsuarioEdicion'),
            'UpdateList() no resuelve el autor y el editor de cada observacion');
        $this->addCase($results, 'lista_orden', str_contains($lista, "'order'   => 'servicios_listado_observaciones.idObservaciones ASC'"),
            'UpdateList() no ordena por idObservaciones ascendente');
        $this->addCase($results, 'lista_limite', str_contains($lista, 'ConfigAPP::APP["N_MaxItems"]'),
            'UpdateList() no limita con N_MaxItems');

        $view = $this->cuerpoMetodo('View');
        $this->addCase($results, 'view_filtra_observacion',
            str_contains($view, "'where'   => 'servicios_listado_observaciones.idObservaciones = ?'") && str_contains($view, "'params'  => [\$ObservacionesID['data']]"),
            'View() no filtra por la observacion descifrada');
        $this->addCase($results, 'view_campos', str_contains($view, 'servicios_listado_observaciones.Observacion,')
            && str_contains($view, 'servicios_listado_observaciones.FechaCreacion,')
            && str_contains($view, 'usuarios_listado.Nombre AS Usuario')
            && str_contains($view, 'LEFT JOIN usuarios_listado ON usuarios_listado.idUsuario = servicios_listado_observaciones.idUsuario'),
            'View() no consulta Observacion, FechaCreacion y el autor (Usuario)');

        $getid = $this->cuerpoMetodo('GetID');
        $this->addCase($results, 'getid_filtra_observacion',
            str_contains($getid, "'where'   => 'idObservaciones = ?'") && str_contains($getid, "'params'  => [\$ObservacionesID['data']]"),
            'GetID() no filtra por la observacion descifrada');
        $this->addCase($results, 'getid_campos',
            str_contains($getid, "'data'    => 'idObservaciones,idServicio,Observacion,FechaCreacion'"),
            'GetID() no entrega los campos que usa el formulario de edicion');
        $this->addCase($results, 'getid_funciones',
            str_contains($getid, "'Fnc_DataDate'") && str_contains($getid, "'Fnc_FormInputs'") && str_contains($getid, "'Fnc_Codification'"),
            'GetID() no entrega Fnc_DataDate, Fnc_FormInputs y Fnc_Codification a la vista');

        $this->addCase($results, 'respuestas_fusionadas',
            substr_count($this->fuenteControlador(), 'mergeResponses(') === 4,
            'cada metodo de vista debe fusionar sus respuestas con mergeResponses()');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              ESCRITURA                                      */
    /******************************************************************************/
    /**
     * Verifica SIN ejecutarlos los metodos de escritura: verbo HTTP, usuario
     * y fecha de auditoria tomados del servidor, dataCheck, tabla,
     * obligatorios, transaccion y IDs descifrados (lectura del fuente y
     * reflexion).
     *
     * @return array Resultado por caso
     */
    public function controladorEscrituraTest(){

        $results = [];

        /*====================================*/
        // Firmas de los metodos de escritura
        /*====================================*/
        $this->firmaMetodo($results, 'Insert', 1, 1);
        $this->firmaMetodo($results, 'Update', 1, 1);
        $this->firmaMetodo($results, 'Delete', 0, 0);
        $this->addCase($results, 'datacheck_privado',
            method_exists(self::CONTROLADOR, 'dataCheck')
            && !(new ReflectionMethod(self::CONTROLADOR, 'dataCheck'))->isPublic(),
            'dataCheck() debe existir y ser privado');

        /*====================================*/
        // Insert: autor desde la sesion, chequeo propio y transaccion
        /*====================================*/
        $insert = $this->cuerpoMetodo('Insert');
        $this->addCase($results, 'insert_autor_sesion', str_contains($insert, "\$_POST['idUsuario'] = \$f3->get('SESSION.DataInfo.UserID')"),
            'Insert() no toma el autor de SESSION.DataInfo.UserID');
        $this->addCase($results, 'insert_autor_antes_de_datacheck',
            strpos($insert, "\$_POST['idUsuario']") < strpos($insert, '$this->dataCheck($_POST)'),
            'Insert() debe fijar el autor antes de construir el DataCheck');
        $this->addCase($results, 'insert_datacheck', str_contains($insert, '$this->dataCheck($_POST)'),
            'Insert() no construye el DataCheck con dataCheck($_POST)');
        $this->addCase($results, 'insert_tabla',
            (bool)preg_match("/'table'\s*=>\s*'".self::TABLA."'/", $insert),
            'Insert() no apunta a la tabla '.self::TABLA);
        $this->addCase($results, 'insert_campos',
            str_contains($insert, "'data'      => 'idUsuario,idServicio,Observacion,FechaCreacion'"),
            'Insert() no declara idUsuario,idServicio,Observacion,FechaCreacion');
        $this->addCase($results, 'insert_obligatorios',
            (bool)preg_match("/'required'\s*=>\s*'idUsuario,idServicio,Observacion,FechaCreacion'/", $insert),
            'Insert() no declara idUsuario,idServicio,Observacion,FechaCreacion como obligatorios');
        $this->addCase($results, 'insert_sin_unicidad', (bool)preg_match("/'unique'\s*=>\s*''/", $insert),
            'Insert() no deberia exigir unicidad de la observacion');
        $this->addCase($results, 'insert_transaccion',
            str_contains($insert, 'Base_transactionBegin()')
            && str_contains($insert, 'Base_transactionCommit()')
            && str_contains($insert, 'failOperation('),
            'Insert() debe abrir transaccion y revertir con failOperation');
        $this->addCase($results, 'insert_orden_transaccion',
            strpos($insert, '$this->dataCheck($_POST)') < strpos($insert, 'Base_transactionBegin()')
            && strpos($insert, 'Base_transactionBegin()') < strpos($insert, 'Base_insert(')
            && strpos($insert, 'Base_insert(') < strpos($insert, 'Base_transactionCommit()'),
            'Insert() no respeta el orden dataCheck, begin, insert, commit');
        $this->addCase($results, 'insert_respuesta', str_contains($insert, 'Response::success('),
            'Insert() no responde con Response::success()');

        /*====================================*/
        // Update: verbo POST, auditoria de edicion y delimitacion por id
        /*====================================*/
        $update = $this->cuerpoMetodo('Update');
        $this->addCase($results, 'update_verbo_post', str_contains($update, "REQUEST_METHOD'] !== 'POST'"),
            'Update() no valida el verbo HTTP POST');
        $this->addCase($results, 'update_editor_sesion',
            str_contains($update, "\$_POST['idUsuarioEdicion'] = \$f3->get('SESSION.DataInfo.UserID')"),
            'Update() no toma el editor de SESSION.DataInfo.UserID');
        $this->addCase($results, 'update_fecha_edicion', str_contains($update, "\$_POST['FechaEdicion']     = date('Y-m-d H:i:s')"),
            'Update() no fija FechaEdicion con la fecha del servidor');
        $this->addCase($results, 'update_auditoria_antes_de_datacheck',
            strpos($update, "\$_POST['idUsuarioEdicion']") < strpos($update, '$this->dataCheck($_POST)')
            && strpos($update, "\$_POST['FechaEdicion']") < strpos($update, '$this->dataCheck($_POST)'),
            'Update() debe fijar editor y fecha antes de construir el DataCheck');
        $this->addCase($results, 'update_tabla',
            (bool)preg_match("/'table'\s*=>\s*'".self::TABLA."'/", $update),
            'Update() no apunta a la tabla '.self::TABLA);
        $this->addCase($results, 'update_donde', (bool)preg_match("/'where'\s*=>\s*'idObservaciones'/", $update),
            'Update() no delimita la escritura por idObservaciones');
        $this->addCase($results, 'update_campos',
            str_contains($update, "'data'      => 'idObservaciones,Observacion,idUsuarioEdicion,FechaEdicion'"),
            'Update() no declara idObservaciones,Observacion,idUsuarioEdicion,FechaEdicion');
        $this->addCase($results, 'update_conserva_autor_y_fecha',
            !str_contains($update, "'data'      => 'idObservaciones,idUsuario,")
            && !preg_match("/'data'\s*=>\s*'[^']*FechaCreacion/", $update)
            && !preg_match("/'data'\s*=>\s*'[^']*idServicio/", $update),
            'Update() no deberia sobrescribir idUsuario, idServicio ni FechaCreacion');
        $this->addCase($results, 'update_obligatorios',
            (bool)preg_match("/'required'\s*=>\s*'idObservaciones,Observacion,idUsuarioEdicion,FechaEdicion'/", $update),
            'Update() no declara idObservaciones,Observacion,idUsuarioEdicion,FechaEdicion como obligatorios');
        $this->addCase($results, 'update_transaccion',
            str_contains($update, 'Base_transactionBegin()')
            && str_contains($update, 'Base_transactionCommit()')
            && str_contains($update, 'failOperation('),
            'Update() debe abrir transaccion y revertir con failOperation');
        $this->addCase($results, 'update_verbo_antes_de_transaccion',
            strpos($update, "REQUEST_METHOD'] !== 'POST'") < strpos($update, 'Base_transactionBegin()'),
            'Update() debe validar el verbo antes de abrir la transaccion');
        $this->addCase($results, 'update_respuesta', str_contains($update, 'Response::success('),
            'Update() no responde con Response::success()');

        /*====================================*/
        // Delete: verbo DELETE, transaccion y sin manejo de archivos
        /*====================================*/
        $delete = $this->cuerpoMetodo('Delete');
        $this->addCase($results, 'delete_verbo', str_contains($delete, "REQUEST_METHOD'] !== 'DELETE'"),
            'Delete() no valida el verbo HTTP DELETE');
        $this->addCase($results, 'delete_tabla',
            (bool)preg_match("/'table'\s*=>\s*'".self::TABLA."'/", $delete),
            'Delete() no apunta a la tabla '.self::TABLA);
        $this->addCase($results, 'delete_donde', (bool)preg_match("/'where'\s*=>\s*'idObservaciones'/", $delete),
            'Delete() no delimita el borrado por idObservaciones');
        $this->addCase($results, 'delete_sin_archivos', (bool)preg_match("/'files'\s*=>\s*''/", $delete),
            'Delete() no declara la observacion como carente de archivos');
        $this->addCase($results, 'delete_lee_cuerpo', str_contains($delete, 'parse_str(file_get_contents("php://input")'),
            'Delete() no lee el cuerpo DELETE con parse_str');
        $this->addCase($results, 'delete_transaccion',
            str_contains($delete, 'Base_transactionBegin()')
            && str_contains($delete, 'Base_transactionCommit()')
            && str_contains($delete, 'failOperation('),
            'Delete() debe abrir transaccion y revertir con failOperation');
        $this->addCase($results, 'delete_verbo_antes_de_transaccion',
            strpos($delete, "REQUEST_METHOD'] !== 'DELETE'") < strpos($delete, 'Base_transactionBegin()'),
            'Delete() debe validar el verbo antes de abrir la transaccion');

        /*====================================*/
        // El sub-recurso no maneja archivos
        /*====================================*/
        $this->addCase($results, 'sin_delfiles', !method_exists(self::CONTROLADOR, 'DelFiles') && !method_exists(self::CONTROLADOR, 'delFiles'),
            'la observacion no deberia declarar DelFiles() (no maneja archivos)');

        /*====================================*/
        // IDs descifrados antes de consultar
        /*====================================*/
        foreach (['New', 'UpdateList'] as $metodo) {
            $this->addCase($results, 'id_servicio_descifrado_'.$metodo,
                str_contains($this->cuerpoMetodo($metodo), "\$ServicioID = \$this->Codification->encryptDecrypt('decrypt', \$params['id'])")
                && str_contains($this->cuerpoMetodo($metodo), 'isValidDecrypted($ServicioID'),
                $metodo.'() no descifra ni valida $params[\'id\'] como servicio');
        }
        foreach (['View', 'GetID'] as $metodo) {
            $this->addCase($results, 'id_observacion_descifrado_'.$metodo,
                str_contains($this->cuerpoMetodo($metodo), "\$ObservacionesID = \$this->Codification->encryptDecrypt('decrypt', \$params['id'])")
                && str_contains($this->cuerpoMetodo($metodo), 'isValidDecrypted($ObservacionesID'),
                $metodo.'() no descifra ni valida $params[\'id\'] como observacion');
        }
        $this->addCase($results, 'id_invalido_responde_400',
            substr_count($this->fuenteControlador(), "Response::error('Registro inválido', 400)") === 4,
            'los 4 metodos de vista deben responder 400 ante un ID invalido');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                               RUTAS                                         */
    /******************************************************************************/
    /**
     * Coteja las 7 rutas del sub-recurso publicadas por el instalador:
     * handlers, verbos, niveles, prefijo `administracion/servicios/listado/observaciones`,
     * permiso raiz compartido y cobertura de la desinstalacion. No instala ni
     * desinstala nada.
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
                $web === self::RUTA_SUB || str_starts_with($web, self::RUTA_SUB.'/'),
                'la ruta '.$clave.' no cuelga de '.self::RUTA_SUB);
        }
        $this->addCase($results, 'instalador_rutas_completas', $nPropias === 7,
            'el instalador publica '.$nPropias.' rutas de '.self::CONTROLADOR.' (esperadas 7)');

        /*====================================*/
        // Endpoints esperados: ruta, handler y nivel real del instalador
        /*====================================*/
        $esperadas = [
            'GET '.self::RUTA_SUB.'/new/@id'        => ['New',        2],
            'GET '.self::RUTA_SUB.'/updateList/@id' => ['UpdateList', 2],
            'GET '.self::RUTA_SUB.'/view/@id'       => ['View',       2],
            'GET '.self::RUTA_SUB.'/getID/@id'      => ['GetID',      2],
            'POST '.self::RUTA_SUB                  => ['Insert',     2],
            'POST '.self::RUTA_SUB.'/update'        => ['Update',     2],
            'DELETE '.self::RUTA_SUB                => ['Delete',     2],
        ];
        foreach ($esperadas as $clave => [$metodo, $nivel]) {
            $this->addCase($results, 'ruta_publicada_'.md5($clave), isset($arrRutas[$clave]),
                'el instalador no publica la ruta '.$clave);
            $this->addCase($results, 'ruta_handler_esperado_'.md5($clave),
                ($arrRutas[$clave]['handler'] ?? '') === self::CONTROLADOR.'->'.$metodo,
                'la ruta '.$clave.' debe apuntar a '.self::CONTROLADOR.'->'.$metodo);
            $this->addCase($results, 'ruta_nivel_esperado_'.md5($clave),
                ($arrRutas[$clave]['nivel'] ?? 0) === $nivel,
                'la ruta '.$clave.' deberia exigir nivel '.$nivel);
        }

        /*====================================*/
        // Sin rutas de archivos ni de listado propio
        /*====================================*/
        $this->addCase($results, 'sin_ruta_delfiles',
            !isset($arrRutas['PUT '.self::RUTA_SUB.'/delFiles']),
            'el instalador publica delFiles para un sub-recurso sin archivos');

        /*====================================*/
        // Permiso raiz compartido con el servicio
        /*====================================*/
        $arrPadre = $arrRutas['GET '.self::RUTA_WEB.'/listAll'] ?? [];
        $this->addCase($results, 'permiso_raiz_servicio',
            ($arrPadre['controller'] ?? '') === self::CONTROLADOR_PERMISOS,
            'la ruta raiz '.self::RUTA_WEB.'/listAll debe pertenecer a '.self::CONTROLADOR_PERMISOS);
        $this->addCase($results, 'permiso_raiz_en_instalador',
            str_contains($this->fuenteInstalador(), "'RutaController' => '".self::CONTROLADOR_PERMISOS."'")
            && str_contains($this->fuenteInstalador(), "'RutaWeb'        => '".self::RUTA_WEB."'"),
            'el instalador no crea el permiso '.self::CONTROLADOR_PERMISOS.' con RutaWeb '.self::RUTA_WEB);
        $this->addCase($results, 'subrecurso_no_crea_permiso_propio',
            !str_contains($this->fuenteInstalador(), "'RutaController' => '".self::CONTROLADOR."',"),
            self::CONTROLADOR.' no debe declarar un permiso propio: cuelga del servicio');

        /*====================================*/
        // RutaController() cubre todos los controladores con rutas
        /*====================================*/
        $lista = $this->listaControladoresInstalador();
        $this->addCase($results, 'desinstalacion_cubre_'.self::CONTROLADOR, in_array(self::CONTROLADOR, $lista, true),
            'RutaController() no incluye '.self::CONTROLADOR.' (dejaria rutas huerfanas)');
        foreach (self::CONTROLADORES as $controlador) {
            $this->addCase($results, 'desinstalacion_cubre_'.$controlador, in_array($controlador, $lista, true),
                'RutaController() no incluye '.$controlador.' (dejaria rutas huerfanas)');
        }

        /*====================================*/
        // Tabla del instalador
        /*====================================*/
        try {
            $instalador = (new ReflectionClass('serviciosInstaller'))->newInstanceWithoutConstructor();
            $tablas     = (array)(new ReflectionMethod('serviciosInstaller', 'listTables'))->invoke($instalador);
        } catch (Throwable $e) {
            $tablas = [];
        }
        $this->addCase($results, 'tabla_observaciones',
            in_array(self::TABLA, array_column($tablas, 'table'), true),
            'listTables() no crea la tabla '.self::TABLA);

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                 PERMISOS                                    */
    /******************************************************************************/
    /**
     * Verifica la sesion del usuario, el nivel de acceso sobre la raiz de
     * permisos compartida (`serviciosListado`) y el registro de las rutas
     * del sub-recurso en `SESSION.arrPermisos` y en el router de F3. Tolera
     * el caso de usuario de pruebas sin la ruta (nivel null).
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

            $arrLevel = $this->getArrLevel($f3, self::CONTROLADOR_PERMISOS);
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
        // Registro dinamico de las rutas desde SESSION.arrPermisos
        /*====================================*/
        $arrPermisos = $f3->get('SESSION.arrPermisos');
        $this->addCase($results, 'permisos_de_ruta_presentes', is_array($arrPermisos),
            'SESSION.arrPermisos no esta disponible');

        if (is_array($arrPermisos)) {
            $rutaOk = false;
            foreach ($arrPermisos as $permiso) {
                if (isset($permiso['RutaWeb']) && $permiso['RutaWeb'] === self::RUTA_SUB.'/updateList/@id') {
                    $rutaOk = true;
                    break;
                }
            }
            $this->addCase($results, 'ruta_registrada', $rutaOk,
                'la ruta '.self::RUTA_SUB.'/updateList/@id no esta entre los permisos del usuario');
        }

        /*====================================*/
        // Las rutas deben existir en el router de F3 con el verbo y handler esperados
        /*====================================*/
        $arrRoutes = $f3->get('ROUTES');
        $this->addCase($results, 'router_disponible', is_array($arrRoutes),
            'F3 no expuso la variable ROUTES');

        $esquema = [
            'ruta_new_router'        => ['/'.self::RUTA_SUB.'/new/@id',        'GET',    self::CONTROLADOR.'->New'],
            'ruta_updatelist_router' => ['/'.self::RUTA_SUB.'/updateList/@id', 'GET',    self::CONTROLADOR.'->UpdateList'],
            'ruta_view_router'       => ['/'.self::RUTA_SUB.'/view/@id',       'GET',    self::CONTROLADOR.'->View'],
            'ruta_getid_router'      => ['/'.self::RUTA_SUB.'/getID/@id',      'GET',    self::CONTROLADOR.'->GetID'],
            'ruta_insert_router'     => ['/'.self::RUTA_SUB,                   'POST',   self::CONTROLADOR.'->Insert'],
            'ruta_update_router'     => ['/'.self::RUTA_SUB.'/update',         'POST',   self::CONTROLADOR.'->Update'],
            'ruta_delete_router'     => ['/'.self::RUTA_SUB,                   'DELETE', self::CONTROLADOR.'->Delete'],
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
     * Renderiza `-UpdateList` con datos sinteticos y verifica la matriz de
     * niveles 1..4: la tabla de observaciones NO condiciona las acciones al
     * nivel (el permiso se aplica en el router), autor y fecha, marca de
     * edicion condicional y IDs cifrados.
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
        $rutaUpdateList = $this->rutaVista('UpdateList');
        $fechaEstandar  = (new FunctionsDataDate())->fechaEstandar('2026-10-01');

        /*====================================*/
        // Estructura de la tabla (nivel 2, el minimo de las rutas)
        /*====================================*/
        $html = $this->renderizarVista($rutaUpdateList, $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'tabla_render', $html !== '', 'la vista UpdateList no produjo HTML');
        $this->addCase($results, 'tabla_elemento', str_contains($html, '<table') && str_contains($html, 'datatable'),
            'no se dibuja la tabla datatable');
        foreach (['Fecha Creacion', 'Observacion', 'Acciones'] as $columna) {
            $this->addCase($results, 'tabla_columna_'.str_replace(' ', '_', $columna), str_contains($html, '>'.$columna.'<'),
                'la tabla no declara la columna '.$columna);
        }
        $this->addCase($results, 'tabla_fila_fecha', is_string($fechaEstandar) && str_contains($html, '>'.$fechaEstandar.'<'),
            'la fila no muestra la fecha de creacion con fechaEstandar()');
        $this->addCase($results, 'tabla_fila_autor', str_contains($html, '<strong>Usuario Creador:</strong>'),
            'la fila no muestra el autor de la observacion');
        $this->addCase($results, 'tabla_fila_texto', str_contains($html, 'Observacion de prueba del servicio'),
            'la fila no muestra el texto de la observacion');
        $this->addCase($results, 'tabla_sin_marca_edicion_si_no_editada', !str_contains($html, 'Editado por'),
            'una observacion sin editar no deberia mostrar la marca de edicion');

        /*====================================*/
        // Observacion editada: muestra editor y fecha (solo los 10 primeros caracteres)
        /*====================================*/
        $dataEditada = $this->datosSinteticos(2);
        $dataEditada['arrObservaciones'][0]['FechaEdicion']   = '2026-10-05 14:30:00';
        $dataEditada['arrObservaciones'][0]['UsuarioEdicion'] = 'Usuario Editor';
        $htmlEditada   = $this->renderizarVista($rutaUpdateList, $dataEditada, $BASE, $f3);
        $fechaEdicion  = (new FunctionsDataDate())->fechaEstandar('2026-10-05');
        $this->addCase($results, 'tabla_marca_edicion', str_contains($htmlEditada, 'Editado por Usuario Editor'),
            'una observacion editada no muestra quien la edito');
        $this->addCase($results, 'tabla_marca_edicion_fecha', is_string($fechaEdicion) && str_contains($htmlEditada, 'el '.$fechaEdicion),
            'una observacion editada no muestra la fecha de edicion sin la hora');
        $this->addCase($results, 'tabla_marca_edicion_sin_hora', !str_contains($htmlEditada, '14:30'),
            'la marca de edicion no deberia mostrar la hora');

        /*====================================*/
        // Tabla vacia: solo cabecera
        /*====================================*/
        $dataVacia = $this->datosSinteticos(2);
        $dataVacia['arrObservaciones'] = [];
        $htmlVacio = $this->renderizarVista($rutaUpdateList, $dataVacia, $BASE, $f3);
        $this->addCase($results, 'tabla_vacia_render', $htmlVacio !== '' && str_contains($htmlVacio, '<thead>'),
            'la tabla sin observaciones no conserva su cabecera');
        $this->addCase($results, 'tabla_vacia_sin_filas', !str_contains($htmlVacio, 'tabObsView('),
            'la tabla sin observaciones no deberia ofrecer acciones');

        /*====================================*/
        // Matriz de niveles: las acciones son iguales para 1..4
        /*====================================*/
        foreach (self::NIVELES as $nivel) {
            $htmlFila = $this->renderizarVista($rutaUpdateList, $this->datosSinteticos($nivel), $BASE, $f3);
            $this->addCase($results, 'filas_render_nivel'.$nivel, $htmlFila !== '',
                'UpdateList no produjo HTML con nivel '.$nivel);
            $this->addCase($results, 'filas_ver_nivel'.$nivel, str_contains($htmlFila, 'tabObsView('),
                'nivel '.$nivel.' no ofrece ver la observacion');
            $this->addCase($results, 'filas_editar_nivel'.$nivel, str_contains($htmlFila, 'tabObsEdit('),
                'nivel '.$nivel.' no ofrece editar la observacion');
            $this->addCase($results, 'filas_borrar_nivel'.$nivel, str_contains($htmlFila, 'tabObsDel('),
                'nivel '.$nivel.' no ofrece borrar la observacion');
            // Los IDs de las acciones deben viajar cifrados (no el ID plano '9')
            $idsOk = true;
            $nIds  = 0;
            foreach (['tabObsView', 'tabObsEdit', 'tabObsDel'] as $funcion) {
                if (preg_match_all('/'.preg_quote($funcion, '/')."\\(\\s*'([^']+)'/", $htmlFila, $coincidencias)) {
                    foreach ($coincidencias[1] as $token) {
                        $nIds++;
                        if ($token === '9' || strlen($token) < 3) {
                            $idsOk = false;
                        }
                    }
                }
            }
            $this->addCase($results, 'filas_id_cifrado_nivel'.$nivel, $idsOk && $nIds === 3,
                'nivel '.$nivel.' pinta un ID sin cifrar en las acciones (ids detectados: '.$nIds.')');
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                             VISTAS DETALLE                                  */
    /******************************************************************************/
    /**
     * Renderiza `-View` (modal de lectura servido por `View`): titulo,
     * fecha y texto de la observacion y boton de cierre segun la
     * preferencia del usuario.
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
        $rutaView = $this->rutaVista('View');

        $html = $this->renderizarVista($rutaView, $this->datosSinteticos(2), $BASE, $f3);

        $this->addCase($results, 'view_render', $html !== '', 'la vista View no produjo HTML');
        $this->addCase($results, 'view_titulo', str_contains($html, 'Ver Datos'),
            'el modal no muestra el titulo Ver Datos');
        $this->addCase($results, 'view_subtitulo', str_contains($html, 'modal-subtitle'),
            'el modal no muestra el subtitulo con sistemaModalSubtitle=2');
        $this->addCase($results, 'view_campo_fecha', str_contains($html, 'Fecha Creacion'),
            'la ficha no declara el campo Fecha Creacion');
        $this->addCase($results, 'view_campo_observacion', str_contains($html, 'Observación'),
            'la ficha no declara el campo Observación');
        $fechaEstandar = (new FunctionsDataDate())->fechaEstandar('2026-10-01');
        $this->addCase($results, 'view_valor_fecha', is_string($fechaEstandar) && str_contains($html, $fechaEstandar),
            'la fecha de creacion no se pinta con fechaEstandar()');
        $this->addCase($results, 'view_valor_observacion', str_contains($html, 'Observacion de prueba del servicio'),
            'el texto sintetico no se pinta en la ficha');
        $this->addCase($results, 'view_boton_cerrar', str_contains($html, 'data-bs-dismiss="modal"'),
            'falta el pie con el boton Cerrar');
        $this->addCase($results, 'view_sin_formulario', !str_contains($html, '<form'),
            'la vista de lectura no debe contener un formulario');

        /*====================================*/
        // Variante sin boton de cierre (sistemaModalCloseBTN distinto de 2)
        /*====================================*/
        $dataSinCierre = $this->datosSinteticos(2);
        $dataSinCierre['UserData']['sistemaModalCloseBTN'] = 1;
        $dataSinCierre['UserData']['sistemaModalSubtitle'] = 1;
        $htmlSinCierre = $this->renderizarVista($rutaView, $dataSinCierre, $BASE, $f3);
        $this->addCase($results, 'view_sin_pie', $htmlSinCierre !== '' && !str_contains($htmlSinCierre, 'data-bs-dismiss="modal"'),
            'con sistemaModalCloseBTN=1 no deberia dibujarse el pie de cierre');
        $this->addCase($results, 'view_titulo_simple', !str_contains($htmlSinCierre, 'modal-subtitle'),
            'con sistemaModalSubtitle=1 no deberia dibujarse el subtitulo');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                          VISTAS FORMULARIOS                                 */
    /******************************************************************************/
    /**
     * Renderiza `-formNew` (servido por `New`) y `-formEdit` (servido por
     * `GetID`): identificadores, campo Observacion, ocultos, endpoint, verbo
     * e ID del servicio cifrado en el refresco de la tabla.
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
        $htmlNew = $this->renderizarVista($this->rutaVista('formNew'), $this->datosSinteticos(2), $BASE, $f3);

        $this->addCase($results, 'form_new_render', $htmlNew !== '', 'la vista formNew no produjo HTML');
        $this->addCase($results, 'form_new_formulario',
            str_contains($htmlNew, 'id="FormNewObservacion"') && str_contains($htmlNew, 'method="POST"'),
            'el formulario de alta no declara id ni verbo POST');
        $this->addCase($results, 'form_new_titulo', str_contains($htmlNew, 'Crear Nuevo'),
            'el modal no muestra el titulo Crear Nuevo');
        $this->addCase($results, 'form_new_campo_observacion',
            str_contains($htmlNew, 'name="Observacion"') && str_contains($htmlNew, 'id="NewObs_Observacion"') && str_contains($htmlNew, '<textarea'),
            'el formulario de alta no declara el textarea Observacion (NewObs_Observacion)');
        $this->addCase($results, 'form_new_oculto_servicio', str_contains($htmlNew, 'name="idServicio"') && str_contains($htmlNew, 'value="3"'),
            'el alta no transporta el idServicio oculto');
        $this->addCase($results, 'form_new_oculto_fecha', str_contains($htmlNew, 'name="FechaCreacion"'),
            'el alta no transporta la FechaCreacion oculta');
        $fechaActual = (new FunctionsServerServer())->fechaActual();
        $this->addCase($results, 'form_new_fecha_actual', is_string($fechaActual) && $fechaActual !== '' && str_contains($htmlNew, 'value="'.$fechaActual.'"'),
            'la FechaCreacion oculta no es la fecha actual del servidor');
        $this->addCase($results, 'form_new_sin_autor_en_form', !str_contains($htmlNew, 'name="idUsuario"'),
            'el alta no deberia enviar idUsuario: el autor lo fija el servidor desde la sesion');
        $this->addCase($results, 'form_new_endpoint',
            str_contains($htmlNew, "'POST'") && str_contains($htmlNew, '/'.self::RUTA_WEB.'/observaciones'."'"),
            'el alta no envia por POST a la raiz de observaciones');
        $this->addCase($results, 'form_new_refresco_tabla',
            str_contains($htmlNew, '#tabObsDataTable') && str_contains($htmlNew, '/observaciones/updateList/'),
            'tras crear no se refresca la tabla de observaciones');
        $this->addCase($results, 'form_new_id_cifrado_refresco', !str_contains($htmlNew, '/observaciones/updateList/3'."'"),
            'el refresco de la tabla usa el ID del servicio sin cifrar');
        $this->addCase($results, 'form_new_notificacion', str_contains($htmlNew, 'Dato Creado Correctamente'),
            'el alta no notifica el resultado al usuario');

        /*====================================*/
        // Formulario de edicion
        /*====================================*/
        $htmlEdit = $this->renderizarVista($this->rutaVista('formEdit'), $this->datosSinteticos(2), $BASE, $f3);

        $this->addCase($results, 'form_edit_render', $htmlEdit !== '', 'la vista formEdit no produjo HTML');
        $this->addCase($results, 'form_edit_formulario',
            str_contains($htmlEdit, 'id="FormEditObservacion"') && str_contains($htmlEdit, 'method="POST"'),
            'el formulario de edicion no declara id ni verbo POST');
        $this->addCase($results, 'form_edit_titulo', str_contains($htmlEdit, 'Editar Información'),
            'el modal no muestra el titulo Editar Información');
        $this->addCase($results, 'form_edit_valor_observacion',
            str_contains($htmlEdit, 'id="EditObs_Observacion"') && str_contains($htmlEdit, 'Observacion de prueba del servicio'),
            'el textarea EditObs_Observacion no se pinta con el valor actual');
        $fechaEstandar = (new FunctionsDataDate())->fechaEstandar('2026-10-01');
        $this->addCase($results, 'form_edit_fecha_visible',
            str_contains($htmlEdit, 'name="FechaCreacionFake"') && is_string($fechaEstandar) && str_contains($htmlEdit, $fechaEstandar),
            'la fecha de creacion de solo lectura no se pinta con fechaEstandar()');
        $this->addCase($results, 'form_edit_fecha_no_se_envia_como_dato', !str_contains($htmlEdit, 'name="FechaCreacion"'),
            'la edicion no deberia enviar FechaCreacion (se conserva la original)');
        $this->addCase($results, 'form_edit_oculto_observacion', str_contains($htmlEdit, 'name="idObservaciones"'),
            'el formulario de edicion no transporta el idObservaciones oculto');
        $this->addCase($results, 'form_edit_oculto_servicio', str_contains($htmlEdit, 'name="idServicio"'),
            'el formulario de edicion no transporta el idServicio oculto');
        $this->addCase($results, 'form_edit_endpoint',
            str_contains($htmlEdit, '/observaciones/update'."'") && str_contains($htmlEdit, "'POST'"),
            'el formulario de edicion no apunta a /observaciones/update con POST');
        $this->addCase($results, 'form_edit_refresco_tabla',
            str_contains($htmlEdit, '#tabObsDataTable') && str_contains($htmlEdit, '/observaciones/updateList/'),
            'tras editar no se refresca la tabla de observaciones');
        $this->addCase($results, 'form_edit_id_cifrado_refresco', !str_contains($htmlEdit, '/observaciones/updateList/3'."'"),
            'el refresco de la tabla usa el ID del servicio sin cifrar');
        $this->addCase($results, 'form_edit_notificacion', str_contains($htmlEdit, 'Datos Editados Correctamente'),
            'la edicion no notifica el resultado al usuario');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos el chequeo de datos de observaciones
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
            // Se crea sin constructor: dataCheck() es logica pura y no necesita BD.
            $Controller = (new ReflectionClass(self::CONTROLADOR))->newInstanceWithoutConstructor();

            $Post      = ['idServicio' => '3', 'Observacion' => 'Observacion de prueba', 'FechaCreacion' => '2026-10-01'];
            $DataCheck = $reflector->invoke($Controller, $Post);

            $this->addCase($results, 'datacheck_es_arreglo', is_array($DataCheck),
                'dataCheck() no devolvio un arreglo');
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post,
                'dataCheck() no conserva el POST recibido');
            $this->addCase($results, 'datacheck_servicio_numerico',
                (string)($DataCheck['ValidarNumero'] ?? '') === 'idServicio' && (string)($DataCheck['ValidarEntero'] ?? '') === 'idServicio',
                'dataCheck() no valida idServicio como numero entero');
            $this->addCase($results, 'datacheck_fecha', (string)($DataCheck['ValidarFecha'] ?? '') === 'FechaCreacion',
                'dataCheck() no valida FechaCreacion como fecha');
            $this->addCase($results, 'datacheck_largo_minimo',
                (string)($DataCheck['ValidarLargoMinimo'] ?? '') === 'Observacion' && (int)($DataCheck['ValidarLargoMinimoN'] ?? 0) === 3,
                'dataCheck() no fija el largo minimo de Observacion en 3');
            $this->addCase($results, 'datacheck_sin_largo_maximo', ($DataCheck['ValidarLargoMaximoPorCampo'] ?? null) === [],
                'dataCheck() no deberia limitar el largo maximo de Observacion (columna text)');
            $this->addCase($results, 'datacheck_censura',
                (string)($DataCheck['ValidarPalabrasCensuradas'] ?? '') === 'Observacion',
                'dataCheck() no censura palabras en Observacion');
            $this->addCase($results, 'datacheck_sin_email_ni_rut',
                (string)($DataCheck['ValidarEmail'] ?? '') === '' && (string)($DataCheck['ValidarRut'] ?? '') === '',
                'una observacion no deberia validar email ni rut');
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_es_arreglo', false,
                'no se pudo ejecutar dataCheck() ('.$e->getMessage().')');
        }

        /*====================================*/
        // Cifrado de identificadores: ida y vuelta estable
        /*====================================*/
        try {
            $Codification = new FunctionsSecurityCodification();
            $cifrado      = $Codification->encryptDecrypt('encrypt', '9');
            $this->addCase($results, 'cifrado_genera_dato',
                ($cifrado['success'] ?? false) === true && !empty($cifrado['data']),
                'encryptDecrypt(encrypt) no produjo un valor cifrado');
            $this->addCase($results, 'cifrado_no_expone_id', (string)($cifrado['data'] ?? '') !== '9',
                'el ID cifrado coincide con el ID plano');

            $descifrado = $Codification->encryptDecrypt('decrypt', (string)($cifrado['data'] ?? ''));
            $this->addCase($results, 'cifrado_ida_y_vuelta',
                ($descifrado['success'] ?? false) === true && (string)($descifrado['data'] ?? '') === '9',
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
     * Revisa estaticamente las vistas `Resumen-Observaciones-*` y el controlador:
     * ausencia de rutas relativas maliciosas, de rutas absolutas del sistema
     * de ficheros, de acceso directo a la sesion, de SQL embebido y de
     * concatenaciones de superglobals en consultas.
     *
     * @return array Resultado por caso
     */
    public function seguridadVistasTest(){

        $results = [];

        /*====================================*/
        // Vistas involucradas (nombres reales Resumen-Observaciones)
        /*====================================*/
        $arrVistas = glob($this->rutaModulo('../views/'.self::VISTA_BASE.'-*.php')) ?: [];
        $this->addCase($results, 'hay_vistas', count($arrVistas) === 4,
            'se esperaban 4 vistas '.self::VISTA_BASE.'-* y hay '.count($arrVistas));

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
            $this->addCase($results, 'vista_sin_eval_'.$nombre,
                !str_contains($codigo, 'eval(') && !str_contains($codigo, 'shell_exec('),
                $nombre.' usa eval o shell_exec');
        }

        /*====================================*/
        // Controlador
        /*====================================*/
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'controlador_sin_concat_sql',
            !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $codigo),
            'el controlador concatena superglobals en cadenas');
        $this->addCase($results, 'controlador_parametros_preparados',
            substr_count($codigo, "'params'") >= 4,
            'las 4 consultas del controlador deben usar la clave params (prepared statements)');
        $this->addCase($results, 'controlador_autor_no_viene_del_post',
            !preg_match("/'idUsuario'\\s*=>\\s*\\\$_POST/", $codigo) && substr_count($codigo, "SESSION.DataInfo.UserID") === 2,
            'el autor y el editor deben salir de la sesion (2 usos de SESSION.DataInfo.UserID)');
        $this->addCase($results, 'controlador_sin_eval',
            !str_contains($codigo, 'eval(') && !str_contains($codigo, 'shell_exec('),
            'el controlador usa eval o shell_exec');

        /*====================================*/
        // Escape antes de pintar
        /*====================================*/
        $vistaListado = $this->codigoFuente($this->rutaVista('UpdateList'));
        $this->addCase($results, 'tabla_borrado_texto_fijo', str_contains($vistaListado, "'Observacion')"),
            'UpdateList debe pasar un texto fijo (no dato del usuario) como etiqueta de borrado');
        $this->addCase($results, 'tabla_id_cifrado',
            str_contains($vistaListado, "encryptDecrypt('encrypt'"),
            'UpdateList no cifra el ID antes de componer las acciones');
        foreach (['formNew', 'formEdit'] as $form) {
            $this->addCase($results, 'form_id_servicio_cifrado_'.$form,
                str_contains($this->codigoFuente($this->rutaVista($form)), "encryptDecrypt('encrypt', \$data['rowData']['idServicio'])"),
                $form.' no cifra el idServicio usado en la URL de refresco');
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

}
