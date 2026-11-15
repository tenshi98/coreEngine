<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class usuariosListadoObsTesting
 *
 * Pruebas del modulo `usuarios` - controlador `usuariosListadoObs` (observaciones de un usuario).
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
 * `Update()`, `Delete()`) abren transacciones y terminan en `Response::success()`/`Response::error()`,
 * que ejecutan `exit`; por eso NO se invocan: se verifican por reflexion y por lectura del
 * fuente. Solo se ejecutan rutas puras (`dataCheck`, `isValidDecrypted`, `getRouteDefinitions`)
 * y el render de vistas con datos sinteticos.
 *
 * Nota: el controlador usa `usuariosListado` como raiz de permisos (`$this->controllerName`),
 * por lo que el nivel de acceso se resuelve contra esa clave y no contra `usuariosListadoObs`.
 *
 * @see skills/testing/SKILL.md
 */
class usuariosListadoObsTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR          = 'usuariosListadoObs';
    private const CONTROLADOR_PERMISOS = 'usuariosListado';
    private const INSTALADOR           = 'usuariosInstaller';
    private const RUTA_WEB             = 'administracion/usuarios';
    private const RUTA_WEB_OBS         = 'administracion/usuarios/observaciones';
    private const VISTA_BASE           = 'usuariosListado-Resumen-Observaciones';
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
    // Fuente del instalador del modulo
    /*******************************************************************/
    private function fuenteInstalador(): string{
        return $this->codigoFuente($this->rutaModulo('../installer/'.self::INSTALADOR.'.php'));
    }

    /*******************************************************************/
    // Ruta de una vista de observaciones
    /*******************************************************************/
    private function rutaVista(string $sufijo): string{
        return $this->rutaModulo('../views/'.self::VISTA_BASE.'-'.$sufijo.'.php');
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
    // IDs descifrados que aparecen en un HTML segun un patron con un grupo de captura.
    // El cifrado usa un IV aleatorio por operacion: no se puede comparar el texto
    // cifrado, hay que descifrar lo que la vista pinto.
    /*******************************************************************/
    private function idsDescifrados(string $html, string $patron): array{
        $ids = [];
        if (preg_match_all($patron, $html, $coincidencias)) {
            $Codification = new FunctionsSecurityCodification();
            foreach ($coincidencias[1] as $cifrado) {
                $r     = $Codification->encryptDecrypt('decrypt', $cifrado);
                $ids[] = ($r['success'] ?? false) === true ? (string)$r['data'] : '';
            }
        }
        return $ids;
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
    private function contratoPresentacion(array &$results, string $metodo, string $vista){
        $cuerpo = $this->cuerpoMetodo($metodo);
        $this->addCase($results, 'vista_'.$metodo, str_contains($cuerpo, $vista),
            $metodo.'() no instancia la vista '.$vista);
        $this->addCase($results, 'tipoview_'.$metodo, str_contains($cuerpo, 'showVista(2,'),
            $metodo.'() no usa showVista(2) (modal/fragmento)');
        $this->addCase($results, 'rutavista_'.$metodo, str_contains($cuerpo, 'returnRutaVista('),
            $metodo.'() no resuelve la ruta de la vista');
        $this->addCase($results, 'errores_'.$metodo, str_contains($cuerpo, 'showError(2, $f3, $result)'),
            $metodo.'() no maneja la ruta de error con showError()');
        $this->addCase($results, 'fallback_'.$metodo, str_contains($cuerpo, 'mergeResponses('),
            $metodo.'() no agrupa las respuestas fallidas con mergeResponses()');
        $this->addCase($results, 'usuario_'.$metodo, str_contains($cuerpo, 'getUserData($f3)'),
            $metodo.'() no obtiene los datos del usuario');
        $this->addCase($results, 'nivel_'.$metodo, str_contains($cuerpo, 'getArrLevel($f3, $this->controllerName)'),
            $metodo.'() no resuelve el nivel de acceso con la raiz de permisos');
    }

    /*******************************************************************/
    // Datos sinteticos usados por las vistas del controlador
    /*******************************************************************/
    private function datosSinteticos(int $nivel = 2): array{
        return [
            'UserData'        => [
                'MainPathUrl'              => '/admin/public/upload/',
                'sistemaModalSubtitle'     => 1,
                'sistemaModalCloseBTN'     => 2,
                'usuariosPermisosBodegas'  => 1,
                'usuariosPermisosMaquinas' => 1,
                'UserType'                 => 2,
            ],
            'UserAccess'      => ['LevelAccess' => $nivel, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_FormInputs'  => new UIFormInputs(),
            'Fnc_Codification' => new FunctionsSecurityCodification(),
            'Fnc_DataDate'    => new FunctionsDataDate(),
            'Fnc_ServerServer' => new FunctionsServerServer(),
            'Fnc_WidgetsCommon' => new UIWidgetsCommon(),
            'Fnc_WidgetsViews'  => new UIWidgetsViews(),
            'arrObservaciones' => [
                [
                    'idObservaciones' => 11,
                    'FechaCreacion'   => '2026-01-02',
                    'FechaEdicion'    => null,
                    'Usuario'         => 'Tester',
                    'UsuarioEdicion'  => null,
                    'Observacion'     => 'Observacion de prueba uno',
                ],
                [
                    'idObservaciones' => 12,
                    'FechaCreacion'   => '2026-01-03',
                    'FechaEdicion'    => '2026-01-04 10:20:00',
                    'Usuario'         => 'Otro',
                    'UsuarioEdicion'  => 'Editor',
                    'Observacion'     => 'Observacion de prueba dos',
                ],
            ],
            'rowData'         => [
                'idObservaciones' => 11,
                'idUsuario'       => 7,
                'Nombre'          => 'Usuario Prueba',
                'FechaCreacion'   => '2026-01-02',
                'Usuario'         => 'Tester',
                'Observacion'     => 'Observacion de prueba uno',
            ],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica los archivos del controlador `usuariosListadoObs` (controlador, 4 vistas
     * y testing), la proteccion de acceso directo y la convencion "clase = nombre de
     * archivo" exigida por el AUTOLOAD de Fat-Free.
     *
     * @return array Resultado por caso
     */
    public function estructuraModuloTest(){

        $results = [];

        /*====================================*/
        // Archivos obligatorios del controlador
        /*====================================*/
        $arrArchivos = [
            'controlador' => $this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'),
            'vista_form_new'  => $this->rutaVista('formNew'),
            'vista_form_edit' => $this->rutaVista('formEdit'),
            'vista_update_list' => $this->rutaVista('UpdateList'),
            'vista_view'      => $this->rutaVista('View'),
            'testing'         => $this->rutaModulo('usuariosListadoObsTesting.php'),
        ];
        foreach ($arrArchivos as $nombre => $ruta) {
            $this->addCase($results, 'archivo_'.$nombre, is_file($ruta), 'no se encuentra '.$ruta);
        }

        /*====================================*/
        // Proteccion de acceso directo
        /*====================================*/
        foreach (['controller' => '../controller/.htaccess', 'views' => '../views/.htaccess', 'testing' => '.htaccess'] as $nombre => $sub) {
            $ruta = $this->rutaModulo($sub);
            if (!is_file($ruta)) {
                $this->addCase($results, 'htaccess_'.$nombre, false, 'no se encuentra '.$ruta);
                continue;
            }
            $this->addCase($results, 'htaccess_'.$nombre,
                stripos((string)file_get_contents($ruta), 'deny from all') !== false,
                'el archivo no contiene "deny from all"');
        }

        /*====================================*/
        // Convencion clase = nombre de archivo y herencias
        /*====================================*/
        $this->addCase($results, 'clase_controlador_cargada', class_exists(self::CONTROLADOR),
            'la clase '.self::CONTROLADOR.' no esta disponible');
        $this->addCase($results, 'clase_testing_cargada', class_exists('usuariosListadoObsTesting'),
            'la clase usuariosListadoObsTesting no esta disponible');
        $padre = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padre === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padre.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('usuariosListadoObsTesting') === 'ControllerBase',
            'el testing no extiende ControllerBase');

        /*====================================*/
        // Constructor: conexion MySQL_1 y raiz de permisos compartida
        /*====================================*/
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'constructor_conexion', str_contains($codigo, 'Database::getSQLConnection(ConfigDataBase::MySQL_1)'),
            'el constructor no abre la conexion MySQL_1');
        $this->addCase($results, 'constructor_padre', str_contains($codigo, 'parent::__construct($DB_conn_1, $queryBuilder, $checkData)'),
            'el constructor no delega en ControllerBase');
        $this->addCase($results, 'constructor_raiz_permisos',
            str_contains($codigo, "\$this->controllerName = '".self::CONTROLADOR_PERMISOS."'"),
            'controllerName debe apuntar a la raiz de permisos '.self::CONTROLADOR_PERMISOS);

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                            CONTROLADOR: VISTAS                             */
    /******************************************************************************/
    /**
     * Verifica el contrato de los metodos que devuelven HTML (`New`, `UpdateList`,
     * `View`, `GetID`): existencia, visibilidad, firma, vista asociada y consultas
     * parametrizadas por ID descifrado.
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];

        /*====================================*/
        // Firma de cada metodo de vista: ($f3, $params)
        /*====================================*/
        foreach (['New', 'UpdateList', 'View', 'GetID'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 2, 2);
        }

        /*====================================*/
        // Contrato de presentacion de cada metodo
        /*====================================*/
        $this->contratoPresentacion($results, 'New', "-Resumen-Observaciones-formNew.php");
        $this->contratoPresentacion($results, 'UpdateList', "-Resumen-Observaciones-UpdateList.php");
        $this->contratoPresentacion($results, 'View', "-Resumen-Observaciones-View.php");
        $this->contratoPresentacion($results, 'GetID', "-Resumen-Observaciones-formEdit.php");

        /*====================================*/
        // ID descifrado, validado y consultado con parametros preparados
        /*====================================*/
        $consultas = [
            'New'        => ['usuarios_listado',                'idUsuario = ?',                                                   'UsuarioID'],
            'UpdateList' => ['usuarios_listado_observaciones',  'usuarios_listado_observaciones.idUsuario = ?',                     'UsuarioID'],
            'View'       => ['usuarios_listado_observaciones',  'usuarios_listado_observaciones.idObservaciones = ?',               'ObservacionesID'],
            'GetID'      => ['usuarios_listado_observaciones',  'idObservaciones = ?',                                              'ObservacionesID'],
        ];
        foreach ($consultas as $metodo => $datos) {
            [$tabla, $where, $variable] = $datos;
            $cuerpo = $this->cuerpoMetodo($metodo);
            $this->addCase($results, 'id_descifrado_'.$metodo,
                str_contains($cuerpo, "encryptDecrypt('decrypt', \$params['id'])"),
                $metodo.'() no descifra $params[\'id\']');
            $this->addCase($results, 'id_validado_'.$metodo,
                str_contains($cuerpo, "isValidDecrypted(\${$variable}, 'id')") && str_contains($cuerpo, "Response::error('Registro inválido', 400)"),
                $metodo.'() no rechaza con 400 un ID invalido');
            $this->addCase($results, 'id_antes_de_consulta_'.$metodo,
                strpos($cuerpo, 'isValidDecrypted(') < strpos($cuerpo, '$query = ['),
                $metodo.'() arma la consulta antes de validar el ID');
            $this->addCase($results, 'consulta_tabla_'.$metodo, str_contains($cuerpo, "'table'   => '".$tabla."'"),
                $metodo.'() no consulta la tabla '.$tabla);
            $this->addCase($results, 'consulta_parametrizada_'.$metodo,
                str_contains($cuerpo, "'where'   => '".$where."'") && str_contains($cuerpo, "'params'  => [\${$variable}['data']]"),
                $metodo.'() no filtra con parametros preparados ('.$where.')');
        }

        /*====================================*/
        // Datos entregados a las vistas
        /*====================================*/
        $this->addCase($results, 'new_datos_formulario',
            str_contains($this->cuerpoMetodo('New'), "'Fnc_FormInputs'") && str_contains($this->cuerpoMetodo('New'), "'Fnc_ServerServer'"),
            'New() no entrega los inputs y la fecha del servidor al formulario');
        $this->addCase($results, 'updatelist_orden_observaciones',
            str_contains($this->cuerpoMetodo('UpdateList'), "'limit'   => \$page['limit']")
                && str_contains($this->cuerpoMetodo('UpdateList'), '$this->getPageParams()')
                && str_contains($this->cuerpoMetodo('UpdateList'), '$this->sliceMore('),
            'UpdateList() debe limitar la cantidad de filas por bloques con getPageParams()/sliceMore() (carga incremental, ADR-0022)');
        $this->addCase($results, 'updatelist_datos_tabla',
            str_contains($this->cuerpoMetodo('UpdateList'), "'arrObservaciones'") && str_contains($this->cuerpoMetodo('UpdateList'), "'Fnc_DataDate'"),
            'UpdateList() no entrega observaciones y formateador de fechas');
        $this->addCase($results, 'updatelist_campos_edicion',
            str_contains($this->cuerpoMetodo('UpdateList'), 'usuarios_listado_observaciones.FechaEdicion')
            && str_contains($this->cuerpoMetodo('UpdateList'), 'usuarios_edicion.Nombre AS UsuarioEdicion'),
            'UpdateList() no consulta la fecha y el usuario de edicion');
        $this->addCase($results, 'updatelist_join_editor',
            str_contains($this->cuerpoMetodo('UpdateList'), 'LEFT JOIN usuarios_listado AS usuarios_edicion')
            && str_contains($this->cuerpoMetodo('UpdateList'), 'usuarios_edicion.idUsuario  = usuarios_listado_observaciones.idUsuarioEdicion'),
            'UpdateList() no une la tabla de usuarios para el editor');
        $this->addCase($results, 'getid_datos_formulario',
            str_contains($this->cuerpoMetodo('GetID'), "'Fnc_DataDate'") && str_contains($this->cuerpoMetodo('GetID'), "'Fnc_ServerServer'"),
            'GetID() no entrega el formateador de fechas y la fecha del servidor al formulario');
        $this->addCase($results, 'getid_campos',
            str_contains($this->cuerpoMetodo('GetID'), 'idObservaciones,idUsuario,Observacion,FechaCreacion'),
            'GetID() no consulta los campos que necesita el formulario de edicion');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                         CONTROLADOR: ESCRITURA (CRUD)                      */
    /******************************************************************************/
    /**
     * Verifica el contrato de los metodos de escritura (`Insert`, `Update`, `Delete`)
     * SIN ejecutarlos: abren transaccion, escriben en base de datos y terminan en
     * `Response::*()` (que hace `exit`). Se comprueba firma, verbo HTTP, transaccion
     * completa, usuario creador tomado de la sesion y respuestas al cliente.
     *
     * @return array Resultado por caso
     */
    public function controladorEscrituraTest(){

        $results = [];

        /*====================================*/
        // Firma de cada metodo de escritura
        /*====================================*/
        $this->firmaMetodo($results, 'Insert', 1, 1);
        $this->firmaMetodo($results, 'Update', 1, 1);
        $this->firmaMetodo($results, 'Delete', 0, 0);

        /*====================================*/
        // Rasgos comunes: transaccion completa y orden de las operaciones
        /*====================================*/
        $operaciones = ['Insert' => 'Base_insert(', 'Update' => 'Base_update(', 'Delete' => 'Base_delete('];
        foreach ($operaciones as $metodo => $operacion) {
            $cuerpo = $this->cuerpoMetodo($metodo);
            $this->addCase($results, 'transaccion_'.$metodo,
                str_contains($cuerpo, 'Base_transactionBegin()')
                && str_contains($cuerpo, 'Base_transactionCommit()')
                && str_contains($cuerpo, 'failOperation('),
                $metodo.'() debe usar Begin/Commit y failOperation (rollback gestionado)');
            $this->addCase($results, 'transaccion_orden_'.$metodo,
                strpos($cuerpo, 'Base_transactionBegin()') < strpos($cuerpo, $operacion)
                && strpos($cuerpo, $operacion) < strpos($cuerpo, 'Base_transactionCommit()'),
                $metodo.'() debe abrir la transaccion antes de escribir y confirmarla despues');
            $this->addCase($results, 'falla_antes_de_commit_'.$metodo,
                strpos($cuerpo, 'failOperation(') < strpos($cuerpo, 'Base_transactionCommit()'),
                $metodo.'() debe revertir por falla antes de confirmar');
            $this->addCase($results, 'respuesta_'.$metodo, str_contains($cuerpo, 'Response::success($Response[\'data\'])'),
                $metodo.'() no responde con Response::success()');
        }

        /*====================================*/
        // Insert
        /*====================================*/
        $insert = $this->cuerpoMetodo('Insert');
        $this->addCase($results, 'insert_creador_sesion', str_contains($insert, "\$_POST['idUsuarioCreador'] = \$f3->get('SESSION.DataInfo.UserID')"),
            'Insert() debe tomar el usuario creador de la sesion y no del cliente');
        $this->addCase($results, 'insert_creador_antes_de_check',
            strpos($insert, "\$_POST['idUsuarioCreador']") < strpos($insert, '$this->dataCheck($_POST)'),
            'Insert() debe fijar el creador antes de construir el DataCheck');
        $this->addCase($results, 'insert_datacheck', str_contains($insert, '$this->dataCheck($_POST)'),
            'Insert() no construye el DataCheck con dataCheck($_POST)');
        $this->addCase($results, 'insert_tabla', str_contains($insert, "'table'     => 'usuarios_listado_observaciones'"),
            'Insert() no apunta a la tabla usuarios_listado_observaciones');
        $this->addCase($results, 'insert_requeridos',
            str_contains($insert, "'required'  => 'idUsuarioCreador,idUsuario,Observacion,FechaCreacion'"),
            'Insert() no declara los campos obligatorios');

        /*====================================*/
        // Update
        /*====================================*/
        $update = $this->cuerpoMetodo('Update');
        $this->addCase($results, 'update_verbo_post', str_contains($update, "REQUEST_METHOD'] !== 'POST'") && str_contains($update, '405'),
            'Update() no valida el verbo HTTP POST con 405');
        $this->addCase($results, 'update_verbo_antes_de_escribir',
            strpos($update, "REQUEST_METHOD'] !== 'POST'") < strpos($update, 'Base_transactionBegin()'),
            'Update() debe validar el verbo antes de abrir la transaccion');
        $this->addCase($results, 'update_editor_sesion',
            str_contains($update, "\$_POST['idUsuarioEdicion'] = \$f3->get('SESSION.DataInfo.UserID')")
            && str_contains($update, "\$_POST['FechaEdicion']     = date('Y-m-d H:i:s')"),
            'Update() debe fijar el usuario y la fecha de edicion desde el servidor');
        $this->addCase($results, 'update_conserva_creador',
            !str_contains($update, "\$_POST['idUsuarioCreador']")
            && !str_contains($update, "\$_POST['idUsuario']")
            && !str_contains($update, "\$_POST['FechaCreacion']"),
            'Update() no debe reenviar ni sobrescribir el creador o la fecha de creacion');
        $this->addCase($results, 'update_donde', str_contains($update, "'where'     => 'idObservaciones'"),
            'Update() no delimita la escritura por idObservaciones');
        $this->addCase($results, 'update_requeridos',
            str_contains($update, "'required'  => 'idObservaciones,Observacion,idUsuarioEdicion,FechaEdicion'"),
            'Update() no declara los campos obligatorios');
        $this->addCase($results, 'update_tabla', str_contains($update, "'table'     => 'usuarios_listado_observaciones'"),
            'Update() no apunta a la tabla usuarios_listado_observaciones');

        /*====================================*/
        // Delete
        /*====================================*/
        $delete = $this->cuerpoMetodo('Delete');
        $this->addCase($results, 'delete_verbo', str_contains($delete, "REQUEST_METHOD'] !== 'DELETE'") && str_contains($delete, '405'),
            'Delete() no valida el verbo HTTP DELETE con 405');
        $this->addCase($results, 'delete_verbo_antes_de_escribir',
            strpos($delete, "REQUEST_METHOD'] !== 'DELETE'") < strpos($delete, 'Base_transactionBegin()'),
            'Delete() debe validar el verbo antes de abrir la transaccion');
        $this->addCase($results, 'delete_donde', str_contains($delete, "'where'       => 'idObservaciones'"),
            'Delete() no delimita el borrado por idObservaciones');
        $this->addCase($results, 'delete_tabla', str_contains($delete, "'table'       => 'usuarios_listado_observaciones'"),
            'Delete() no apunta a la tabla usuarios_listado_observaciones');
        $this->addCase($results, 'delete_sin_archivos', str_contains($delete, "'files'       => ''"),
            'Delete() no debe borrar archivos: las observaciones no tienen');
        $this->addCase($results, 'delete_cuerpo_put', str_contains($delete, 'parse_str(file_get_contents("php://input")'),
            'Delete() no lee el cuerpo de la peticion DELETE');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                       CONTROLADOR: RUTAS E INSTALADOR                      */
    /******************************************************************************/
    /**
     * Verifica que las 7 rutas de `usuariosListadoObs` publicadas por el instalador
     * apunten a metodos reales con el verbo, nivel y controlador esperados, y que el
     * controlador quede incluido en la desinstalacion.
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
            'nuevo'      => ['1 '.self::RUTA_WEB_OBS.'/new/@id',        'usuariosListadoObs->New',        2],
            'refrescar'  => ['1 '.self::RUTA_WEB_OBS.'/updateList/@id', 'usuariosListadoObs->UpdateList', 2],
            'ver'        => ['1 '.self::RUTA_WEB_OBS.'/view/@id',       'usuariosListadoObs->View',       2],
            'getid'      => ['1 '.self::RUTA_WEB_OBS.'/getID/@id',      'usuariosListadoObs->GetID',      2],
            'crear'      => ['2 '.self::RUTA_WEB_OBS,                   'usuariosListadoObs->Insert',     2],
            'editar'     => ['2 '.self::RUTA_WEB_OBS.'/update',         'usuariosListadoObs->Update',     2],
            'borrar'     => ['3 '.self::RUTA_WEB_OBS,                   'usuariosListadoObs->Delete',     2],
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
        // Rutas propias: cantidad, verbos y prefijo
        /*====================================*/
        $propias = array_filter($rutas, static function ($r) { return $r[2] === 'usuariosListadoObs'; });
        $this->addCase($results, 'rutas_propias_completas', count($propias) === count($esperadas),
            'se esperaban '.count($esperadas).' rutas de '.self::CONTROLADOR.' y hay '.count($propias));
        $fueraPrefijo = [];
        foreach ($propias as $clave => $datos) {
            if (!str_contains($clave, ' '.self::RUTA_WEB_OBS)) {
                $fueraPrefijo[] = $clave;
            }
            $verbo = (int)substr($clave, 0, 1);
            if (str_ends_with($datos[0], '->Insert') || str_ends_with($datos[0], '->Update')) {
                $this->addCase($results, 'verbo_escritura_'.md5($clave), $verbo === 2, $datos[0].' debe publicarse con POST');
            }
            if (str_ends_with($datos[0], '->Delete')) {
                $this->addCase($results, 'verbo_borrado_'.md5($clave), $verbo === 3, $datos[0].' debe publicarse con DELETE');
            }
            if (!str_ends_with($datos[0], '->Insert') && !str_ends_with($datos[0], '->Update') && !str_ends_with($datos[0], '->Delete')) {
                $this->addCase($results, 'verbo_lectura_'.md5($clave), $verbo === 1, $datos[0].' debe publicarse con GET');
                $this->addCase($results, 'ruta_con_id_'.md5($clave), str_ends_with($clave, '/@id'), $clave.' debe recibir el ID cifrado');
            }
        }
        $this->addCase($results, 'rutas_bajo_observaciones', count($fueraPrefijo) === 0,
            'rutas fuera de '.self::RUTA_WEB_OBS.': '.implode(' | ', $fueraPrefijo));

        /*====================================*/
        // Desinstalacion: el controlador debe estar en RutaController()
        /*====================================*/
        $this->addCase($results, 'instalador_desinstala_controlador',
            str_contains($this->fuenteInstalador(), "\$RutaController .= ',\"usuariosListadoObs\"'"),
            'RutaController() no lista usuariosListadoObs y dejaria rutas huerfanas al desinstalar');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                PERMISOS                                    */
    /******************************************************************************/
    /**
     * Verifica la sesion, el nivel de acceso heredado de `usuariosListado` y el
     * registro de las rutas de observaciones en `SESSION.arrPermisos` y en el
     * router de F3.
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
        $this->addCase($results, 'sesion_usuario_id',
            !is_array($UserData) || empty($UserData) || (isset($UserData['UserID']) && (int)$UserData['UserID'] > 0),
            'la sesion no trae UserID, necesario para fijar el creador de la observacion');

        /*====================================*/
        // Nivel de acceso resuelto con la raiz de permisos del controlador
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

        $nivel = is_array($arrLevel) ? ($arrLevel['LevelAccess'] ?? null) : null;
        $this->addCase($results, 'nivel_en_rango', $nivel === null || in_array((int)$nivel, self::NIVELES, true),
            'LevelAccess quedo fuera del catalogo 1..4');
        $this->addCase($results, 'ruta_de_acceso_coincide', $nivel === null || ($arrLevel['RouteAccess'] ?? '') === self::RUTA_WEB,
            'RouteAccess no coincide con '.self::RUTA_WEB);
        // Un usuario con acceso al modulo debe poder usar observaciones (todas sus rutas exigen nivel 2)
        $this->addCase($results, 'nivel_observaciones_coherente', $nivel === null || (int)$nivel >= 1,
            'el nivel del usuario no es valido para usar observaciones');

        /*====================================*/
        // Registro dinamico de las rutas desde SESSION.arrPermisos
        /*====================================*/
        $arrPermisos = $f3->get('SESSION.arrPermisos');
        $this->addCase($results, 'permisos_de_ruta_presentes', is_array($arrPermisos),
            'SESSION.arrPermisos no esta disponible');
        if (is_array($arrPermisos)) {
            $rutaOk = false;
            foreach ($arrPermisos as $permiso) {
                if (isset($permiso['RutaWeb']) && $permiso['RutaWeb'] === self::RUTA_WEB_OBS.'/updateList/@id') {
                    $rutaOk = true;
                    break;
                }
            }
            // Con nivel 1 (solo ver) las rutas de nivel 2 no se registran: es el comportamiento esperado
            $this->addCase($results, 'ruta_registrada', $nivel === null || (int)$nivel < 2 || $rutaOk,
                'la ruta '.self::RUTA_WEB_OBS.'/updateList/@id no esta entre los permisos del usuario');
        }

        /*====================================*/
        // Router de F3: solo se evalua si el usuario tiene la ruta registrada
        /*====================================*/
        $arrRoutes = $f3->get('ROUTES');
        $this->addCase($results, 'router_disponible', is_array($arrRoutes),
            'F3 no expuso la variable ROUTES');
        $esquema = [
            'ruta_listado_router'  => ['/'.self::RUTA_WEB_OBS.'/updateList/@id', 'GET',    self::CONTROLADOR.'->UpdateList'],
            'ruta_alta_router'     => ['/'.self::RUTA_WEB_OBS,                   'POST',   self::CONTROLADOR.'->Insert'],
            'ruta_editar_router'   => ['/'.self::RUTA_WEB_OBS.'/update',         'POST',   self::CONTROLADOR.'->Update'],
            'ruta_borrar_router'   => ['/'.self::RUTA_WEB_OBS,                   'DELETE', self::CONTROLADOR.'->Delete'],
        ];
        foreach ($esquema as $caso => $rutaEsperada) {
            [$ruta, $verbo, $handlerEsperado] = $rutaEsperada;
            if (!is_array($arrRoutes) || !isset($arrRoutes[$ruta])) {
                continue;
            }
            $handler = $arrRoutes[$ruta][0][$verbo][0] ?? '';
            $this->addCase($results, $caso, strcasecmp($handler, $handlerEsperado) === 0,
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
     * Renderiza la tabla de observaciones (`UpdateList`) con datos sinteticos: filas,
     * acciones ver/editar/borrar con ID cifrado, escape del titulo de borrado y tabla vacia.
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
        $ruta = $this->rutaVista('UpdateList');

        /*====================================*/
        // Tabla con datos: las acciones no dependen del nivel (la ruta ya exige nivel 2)
        /*====================================*/
        foreach ([2, 3, 4] as $nivel) {
            $tabla = $this->renderizarVista($ruta, $this->datosSinteticos($nivel), $BASE, $f3);
            $this->addCase($results, 'tabla_render_nivel'.$nivel, $tabla !== '',
                'la vista UpdateList no produjo HTML con nivel '.$nivel);
            $this->addCase($results, 'tabla_cabecera_nivel'.$nivel,
                str_contains($tabla, 'Fecha Creacion') && str_contains($tabla, 'Observacion') && str_contains($tabla, 'Acciones'),
                'la tabla no dibuja su cabecera con nivel '.$nivel);
            $this->addCase($results, 'tabla_filas_nivel'.$nivel, substr_count($tabla, '<tr>') === 3,
                'la tabla debe tener 1 fila de cabecera y 2 de datos con nivel '.$nivel);
            $this->addCase($results, 'tabla_datos_nivel'.$nivel,
                str_contains($tabla, 'Observacion de prueba uno') && str_contains($tabla, 'Observacion de prueba dos')
                && str_contains($tabla, 'Tester') && str_contains($tabla, 'Otro'),
                'la tabla no pinta las observaciones y sus autores con nivel '.$nivel);
            $this->addCase($results, 'tabla_acciones_nivel'.$nivel,
                substr_count($tabla, 'tabObsView(') === 2 && substr_count($tabla, 'tabObsEdit(') === 2 && substr_count($tabla, 'tabObsDel(') === 2,
                'cada fila debe ofrecer ver, editar y borrar con nivel '.$nivel);
            $this->addCase($results, 'tabla_edicion_nivel'.$nivel,
                substr_count($tabla, 'Editado por') === 1 && str_contains($tabla, 'Editor'),
                'la tabla debe mostrar la leyenda de edicion solo en la fila editada con nivel '.$nivel);
        }

        /*====================================*/
        // El ID de la observacion nunca viaja en claro
        /*====================================*/
        $tabla     = $this->renderizarVista($ruta, $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'tabla_id_cifrado',
            $this->idsDescifrados($tabla, "/tabObsView\\('([^']+)'\\)/") === ['11', '12'],
            'las acciones deben recibir el ID cifrado de cada observacion (11 y 12)');
        $this->addCase($results, 'tabla_id_no_claro',
            !str_contains($tabla, "tabObsView('11')") && !str_contains($tabla, "tabObsEdit('11')") && !str_contains($tabla, "tabObsDel( '11'"),
            'la tabla expone el ID de la observacion sin cifrar');
        $this->addCase($results, 'tabla_borrar_etiqueta_fija', str_contains($tabla, ", 'Observacion')"),
            'la accion de borrar debe enviar una etiqueta fija, no contenido del usuario');

        /*====================================*/
        // Tabla sin datos: cabecera presente y ninguna accion
        /*====================================*/
        $vacio                     = $this->datosSinteticos(2);
        $vacio['arrObservaciones'] = [];
        $tablaVacia                = $this->renderizarVista($ruta, $vacio, $BASE, $f3);
        $this->addCase($results, 'tabla_vacia_cabecera', str_contains($tablaVacia, 'Fecha Creacion'),
            'la tabla sin datos no dibuja la cabecera');
        $this->addCase($results, 'tabla_vacia_sin_acciones', !str_contains($tablaVacia, 'tabObsView('),
            'la tabla sin datos no debe ofrecer acciones');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                        VISTAS DE DETALLE Y FORMULARIOS                     */
    /******************************************************************************/
    /**
     * Renderiza el detalle de una observacion y sus formularios de alta y edicion con
     * datos sinteticos y comprueba ids de formulario, campos, endpoints, verbos y que
     * tras guardar se refresque la tabla del resumen.
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
        // Detalle de la observacion (View)
        /*====================================*/
        $html = $this->renderizarVista($this->rutaVista('View'), $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'view_render', $html !== '', 'la vista View no produjo HTML');
        $this->addCase($results, 'view_datos',
            str_contains($html, 'Observacion de prueba uno') && str_contains($html, 'Tester'),
            'la vista View no pinta el autor y el texto de la observacion');
        $this->addCase($results, 'view_fecha', str_contains($html, 'Fecha Creacion'),
            'la vista View no pinta la fecha de creacion');
        $this->addCase($results, 'view_cabecera_modal', str_contains($html, 'Ver Datos') && str_contains($html, 'data-modal-close'),
            'la vista View no dibuja la cabecera del modal');
        $this->addCase($results, 'view_pie_cierre', str_contains($html, 'data-bs-dismiss="modal"'),
            'la vista View no ofrece el pie de cierre del modal');
        $conSubtitulo = $this->datosSinteticos(2);
        $conSubtitulo['UserData']['sistemaModalSubtitle'] = 2;
        $conSubtitulo['UserData']['sistemaModalCloseBTN'] = 1;
        $html2 = $this->renderizarVista($this->rutaVista('View'), $conSubtitulo, $BASE, $f3);
        $this->addCase($results, 'view_variante_subtitulo', str_contains($html2, 'modal-subtitle'),
            'con sistemaModalSubtitle=2 la vista View debe usar el subtitulo');
        $this->addCase($results, 'view_sin_pie_si_no_aplica', !str_contains($html2, 'data-bs-dismiss="modal"'),
            'con sistemaModalCloseBTN distinto de 2 la vista View no debe dibujar el pie');

        /*====================================*/
        // Formulario de alta
        /*====================================*/
        $html = $this->renderizarVista($this->rutaVista('formNew'), $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'form_new_render', $html !== '', 'la vista formNew no produjo HTML');
        $this->addCase($results, 'form_new_id', str_contains($html, 'id="FormNewObservacion"'),
            'el formulario de alta no usa el id FormNewObservacion');
        $this->addCase($results, 'form_new_titulo', str_contains($html, 'Crear Nuevo para'),
            'el formulario de alta no indica para que usuario se crea');
        $this->addCase($results, 'form_new_campos',
            str_contains($html, 'name="Observacion"') && str_contains($html, 'id="NewObs_Observacion"'),
            'el formulario de alta no declara el campo Observacion');
        $this->addCase($results, 'form_new_ocultos',
            str_contains($html, 'name="idUsuario"') && str_contains($html, 'name="FechaCreacion"'),
            'el formulario de alta no envia idUsuario y FechaCreacion ocultos');
        $this->addCase($results, 'form_new_sin_creador', !str_contains($html, 'name="idUsuarioCreador"'),
            'el creador lo fija el servidor: el formulario no debe enviarlo');
        $this->addCase($results, 'form_new_endpoint',
            str_contains($html, $BASE.'/'.self::RUTA_WEB_OBS."'") && str_contains($html, "Metodo      = 'POST'"),
            'el formulario de alta no publica en '.self::RUTA_WEB_OBS.' con POST');
        $this->addCase($results, 'form_new_refresco',
            $this->idsDescifrados($html, '#'.preg_quote($BASE.'/'.self::RUTA_WEB_OBS, '#')."/updateList/([^'\"]+)#") === ['7'],
            'tras crear, el formulario debe refrescar la tabla con updateList/<id cifrado del usuario>');
        $this->addCase($results, 'form_new_cierra_modal', str_contains($html, "closeModal:'#viewModal-lg'"),
            'tras crear, el formulario debe cerrar el modal');
        $this->addCase($results, 'form_new_boton_cerrar', str_contains($html, 'data-bs-dismiss="modal"'),
            'el formulario de alta no ofrece el boton Cerrar');

        /*====================================*/
        // Formulario de edicion
        /*====================================*/
        $html = $this->renderizarVista($this->rutaVista('formEdit'), $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'form_edit_render', $html !== '', 'la vista formEdit no produjo HTML');
        $this->addCase($results, 'form_edit_id', str_contains($html, 'id="FormEditObservacion"'),
            'el formulario de edicion no usa el id FormEditObservacion');
        $this->addCase($results, 'form_edit_valor', str_contains($html, 'Observacion de prueba uno'),
            'el formulario de edicion no precarga la observacion');
        $this->addCase($results, 'form_edit_fecha_solo_lectura', str_contains($html, 'FechaCreacionFake'),
            'el formulario de edicion debe mostrar la fecha original como dato de solo lectura');
        $this->addCase($results, 'form_edit_ocultos',
            str_contains($html, 'name="idObservaciones"') && str_contains($html, 'name="idUsuario"'),
            'el formulario de edicion no envia idObservaciones e idUsuario ocultos');
        $this->addCase($results, 'form_edit_sin_fecha_creacion', !str_contains($html, 'name="FechaCreacion"'),
            'el formulario de edicion no debe reenviar FechaCreacion');
        $this->addCase($results, 'form_edit_endpoint',
            str_contains($html, $BASE.'/'.self::RUTA_WEB_OBS.'/update') && str_contains($html, "Metodo      = 'POST'"),
            'el formulario de edicion no publica en /update con POST');
        $this->addCase($results, 'form_edit_refresco',
            $this->idsDescifrados($html, "#/updateList/([^'\"]+)#") === ['7'],
            'tras editar, el formulario debe refrescar la tabla con updateList/<id cifrado del usuario>');
        $this->addCase($results, 'form_edit_cierra_modal', str_contains($html, "closeModal:'#viewModal-lg'"),
            'tras editar, el formulario debe cerrar el modal');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos el chequeo de datos del controlador (`dataCheck`) y la
     * validacion de IDs descifrados que usan todos sus metodos.
     *
     * @return array Resultado por caso
     */
    public function logicaValidacionTest(){

        $results = [];

        /*====================================*/
        // dataCheck devuelve el contrato esperado por Base_insert/Base_update
        /*====================================*/
        try {
            $reflector  = new ReflectionMethod(self::CONTROLADOR, 'dataCheck');
            // Sin constructor: dataCheck() es logica pura y no necesita la conexion
            $Controller = (new ReflectionClass(self::CONTROLADOR))->newInstanceWithoutConstructor();

            $Post      = ['idUsuario' => 7, 'Observacion' => 'Observacion de prueba', 'FechaCreacion' => '2026-01-02'];
            $DataCheck = $reflector->invoke($Controller, $Post);

            $this->addCase($results, 'datacheck_es_arreglo', is_array($DataCheck),
                'dataCheck() no devolvio un arreglo');
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post,
                'dataCheck() no conserva el POST recibido');
            $this->addCase($results, 'datacheck_usuario',
                ($DataCheck['ValidarNumero'] ?? '') === 'idUsuario' && ($DataCheck['ValidarEntero'] ?? '') === 'idUsuario',
                'dataCheck() no valida idUsuario como entero');
            $this->addCase($results, 'datacheck_fecha', ($DataCheck['ValidarFecha'] ?? '') === 'FechaCreacion',
                'dataCheck() no valida la fecha de creacion');
            $this->addCase($results, 'datacheck_largo_minimo',
                ($DataCheck['ValidarLargoMinimo'] ?? '') === 'Observacion' && (int)($DataCheck['ValidarLargoMinimoN'] ?? 0) === 3,
                'dataCheck() no exige un largo minimo de 3 en la observacion');
            $this->addCase($results, 'datacheck_censura', ($DataCheck['ValidarPalabrasCensuradas'] ?? '') === 'Observacion',
                'dataCheck() no censura palabras en la observacion');
            $this->addCase($results, 'datacheck_sin_largo_maximo', ($DataCheck['ValidarLargoMaximoPorCampo'] ?? null) === [],
                'dataCheck() declara un largo maximo inesperado (la columna de observacion es de texto libre)');

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

            $otro = $reflector->invoke($Controller, ['Observacion' => 'X']);
            $this->addCase($results, 'datacheck_post_independiente', ($otro['Post'] ?? null) === ['Observacion' => 'X'],
                'dataCheck() no debe arrastrar datos de una invocacion anterior');
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_es_arreglo', false,
                'no se pudo ejecutar dataCheck() ('.$e->getMessage().')');
        }

        /*====================================*/
        // Cifrado de identificadores y validacion del ID descifrado
        /*====================================*/
        try {
            $Codification = new FunctionsSecurityCodification();
            $cifrado      = $Codification->encryptDecrypt('encrypt', '11');
            $descifrado   = $Codification->encryptDecrypt('decrypt', (string)($cifrado['data'] ?? ''));
            $this->addCase($results, 'cifrado_ida_y_vuelta',
                ($descifrado['success'] ?? false) === true && (string)($descifrado['data'] ?? '') === '11',
                'el cifrado no devuelve el valor original');
            $this->addCase($results, 'cifrado_id_valido', $this->isValidDecrypted((array)$descifrado, 'id') === true,
                'un ID cifrado y descifrado debe ser valido como identificador');

            $manipulado = $Codification->encryptDecrypt('decrypt', 'valor-manipulado');
            $this->addCase($results, 'cifrado_rechaza_manipulado',
                $this->isValidDecrypted((array)$manipulado, 'id') === false,
                'un ID manipulado no debe validarse como identificador');
        } catch (Throwable $e) {
            $this->addCase($results, 'cifrado_ida_y_vuelta', false,
                'no se pudo ejecutar el cifrado ('.$e->getMessage().')');
        }

        $this->addCase($results, 'id_valido_ok', $this->isValidDecrypted(['success' => true, 'data' => '11'], 'id') === true,
            'un ID numerico positivo debe ser valido');
        $this->addCase($results, 'id_valido_descifro_falla', $this->isValidDecrypted(['success' => false, 'data' => '11'], 'id') === false,
            'un descifrado fallido debe rechazarse');
        $this->addCase($results, 'id_valido_texto', $this->isValidDecrypted(['success' => true, 'data' => 'abc'], 'id') === false,
            'un ID no numerico debe rechazarse');
        $this->addCase($results, 'id_valido_cero', $this->isValidDecrypted(['success' => true, 'data' => '0'], 'id') === false,
            'el ID 0 debe rechazarse');
        $this->addCase($results, 'id_valido_negativo', $this->isValidDecrypted(['success' => true, 'data' => '-3'], 'id') === false,
            'un ID negativo debe rechazarse');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                 SEGURIDAD                                  */
    /******************************************************************************/
    /**
     * Revisa estaticamente las vistas y el controlador de observaciones: rutas
     * relativas, rutas absolutas, acceso a sesion, SQL embebido, IDs cifrados,
     * consultas parametrizadas y origen del usuario creador.
     *
     * @return array Resultado por caso
     */
    public function seguridadVistasTest(){

        $results = [];

        /*====================================*/
        // Vistas involucradas
        /*====================================*/
        $arrVistas = glob($this->rutaModulo('../views/'.self::VISTA_BASE.'-*.php')) ?: [];
        $this->addCase($results, 'hay_vistas', count($arrVistas) >= 4,
            'se esperaban al menos 4 vistas de observaciones y hay '.count($arrVistas));

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
            $this->addCase($results, 'vista_sin_sql_'.$nombre, !preg_match('/\b(SELECT|INSERT|UPDATE|DELETE)\s+\w+\s+FROM\b/i', $codigo),
                $nombre.' contiene una consulta SQL embebida');
        }

        /*====================================*/
        // Los formularios cifran el ID del usuario antes de usarlo en URLs
        /*====================================*/
        foreach (['formNew', 'formEdit'] as $sufijo) {
            $codigo = $this->codigoFuente($this->rutaVista($sufijo));
            $this->addCase($results, 'form_id_cifrado_'.$sufijo,
                str_contains($codigo, "encryptDecrypt('encrypt', \$data['rowData']['idUsuario'])"),
                $sufijo.' no cifra el ID del usuario para la URL de refresco');
        }
        // La tabla y sus filas (parcial -UpdateListRows, ADR-0022)
        $tabla = $this->codigoFuente($this->rutaVista('UpdateList')).$this->codigoFuente($this->rutaVista('UpdateListRows'));
        $this->addCase($results, 'tabla_id_cifrado', str_contains($tabla, "encryptDecrypt('encrypt', \$crud['idObservaciones'])"),
            'UpdateList.php no cifra el ID antes de pintarlo en las acciones');
        $this->addCase($results, 'tabla_fecha_formateada', str_contains($tabla, 'fechaEstandar('),
            'UpdateList.php no formatea la fecha de creacion');

        /*====================================*/
        // El controlador no concatena SQL desde el cliente ni lee GET/REQUEST
        /*====================================*/
        $controlador = $this->fuenteControlador();
        $this->addCase($results, 'controlador_sin_concat_sql',
            !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $controlador),
            'el controlador concatena superglobales en una cadena SQL');
        $this->addCase($results, 'controlador_parametros_preparados', str_contains($controlador, "'params'"),
            'el controlador no usa parametros preparados');
        $this->addCase($results, 'controlador_sin_get_directo', !preg_match('/\$_(GET|REQUEST)\b/', $controlador),
            'el controlador lee $_GET/$_REQUEST directamente');
        $this->addCase($results, 'controlador_sin_interpolar_id',
            !preg_match('/\'\s*\.\s*\$(UsuarioID|ObservacionesID)/', $controlador),
            'el controlador interpola IDs en el texto de las consultas en vez de usar params');

        /*====================================*/
        // El creador de la observacion nunca viene del cliente
        /*====================================*/
        $insert = $this->cuerpoMetodo('Insert');
        $this->addCase($results, 'creador_desde_sesion_Insert',
            preg_match('/\$_POST\[\'idUsuarioCreador\'\]\s*=\s*\$f3->get\(\'SESSION\.DataInfo\.UserID\'\)/', $insert) === 1,
            'Insert() debe sobrescribir idUsuarioCreador con el UserID de la sesion');

        /*====================================*/
        // El editor y la fecha de edicion se fijan en el servidor
        /*====================================*/
        $update = $this->cuerpoMetodo('Update');
        $this->addCase($results, 'editor_desde_sesion_Update',
            preg_match('/\$_POST\[\'idUsuarioEdicion\'\]\s*=\s*\$f3->get\(\'SESSION\.DataInfo\.UserID\'\)/', $update) === 1,
            'Update() debe sobrescribir idUsuarioEdicion con el UserID de la sesion');
        $this->addCase($results, 'fecha_edicion_servidor_Update',
            str_contains($update, "\$_POST['FechaEdicion']     = date('Y-m-d H:i:s')"),
            'Update() debe generar FechaEdicion en el servidor');
        $this->addCase($results, 'update_no_sobrescribe_creador',
            !str_contains($update, "idUsuarioCreador")
            && !str_contains($update, "FechaCreacion"),
            'Update() no debe incluir el creador ni la fecha de creacion en la escritura');
        $formNew  = $this->codigoFuente($this->rutaVista('formNew'));
        $formEdit = $this->codigoFuente($this->rutaVista('formEdit'));
        $this->addCase($results, 'creador_no_enviado_por_formularios',
            !str_contains($formNew, 'idUsuarioCreador') && !str_contains($formEdit, 'idUsuarioCreador'),
            'los formularios no deben enviar idUsuarioCreador');

        /************************************/
        // Retorno los datos
        return $results;

    }

}
