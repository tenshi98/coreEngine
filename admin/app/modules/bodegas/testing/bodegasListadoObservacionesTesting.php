<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class bodegasListadoObservacionesTesting
 *
 * Pruebas del modulo `bodegas` - controlador `bodegasListadoObservaciones`
 * (observaciones anidadas bajo la bodega).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. El normalizador
 *    `sistemaTesteos::normalizeResult()` marca EXITOSA o ERROR segun el contenido, por lo que
 *    los textos de los casos correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Nota de diseno: este controlador reutiliza `$this->controllerName = 'bodegasListado'`
 * para resolver los permisos, y las vistas le concatenan el sufijo `/observaciones`.
 *
 * Alcance: pruebas NO destructivas. `Insert()`, `Update()` y `Delete()` terminan en
 * `Response::success()`/`Response::error()` (que ejecutan `exit`) y escriben en base de
 * datos, por lo que NO se invocan: se verifican por reflexion y lectura del fuente.
 *
 * @see skills/testing/SKILL.md
 */
class bodegasListadoObservacionesTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR          = 'bodegasListadoObservaciones';
    // El controlador comparte la raiz de permisos con el listado de bodegas
    private const CONTROLADOR_PERMISOS = 'bodegasListado';
    private const RUTA_WEB             = 'administracion/bodegas/listado';
    private const RUTA_OBSERVACIONES   = 'administracion/bodegas/listado/observaciones';
    private const VISTA_BASE           = 'bodegasListado-Resumen-Observaciones';
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
    /*                                 EJECUCION                                  */
    /******************************************************************************/


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
    // Datos sinteticos usados por las vistas del subrecurso
    /*******************************************************************/
    private function datosSinteticos(int $nivel): array{
        return [
            'TableTitle'   => 'Observaciones',
            'UserData'     => [
                'MainPathUrl'          => '/admin/public/upload/',
                'sistemaModalSubtitle' => 1,
                'sistemaModalCloseBTN' => 2,
            ],
            'UserAccess'   => ['LevelAccess' => $nivel, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_FormInputs'   => new UIFormInputs(),
            'Fnc_Codification' => new FunctionsSecurityCodification(),
            'Fnc_DataDate'     => new FunctionsDataDate(),
            'Fnc_DataNumbers'  => new FunctionsDataNumbers(),
            'Fnc_WidgetsCommon' => new UIWidgetsCommon(),
            'Fnc_WidgetsViews'  => new UIWidgetsViews(),
            'Fnc_ServerServer'  => new FunctionsServerServer(),
            'arrObservaciones'  => [[
                'idObservaciones' => 5,
                'idBodegas'       => 1,
                'FechaCreacion'   => '2026-01-02',
                'Usuario'         => 'Tester',
                'FechaEdicion'    => '2026-02-03 10:00:00',
                'UsuarioEdicion'  => 'Editor',
                'Observacion'     => 'Observacion de prueba',
            ]],
            'rowData'       => [
                'idObservaciones' => 5,
                'idBodegas'       => 1,
                'FechaCreacion'   => '2026-01-02',
                'Usuario'         => 'Tester',
                'Observacion'     => 'Observacion de prueba',
            ],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica la estructura de carpetas y archivos del subrecurso de observaciones
     * y la convencion "clase = nombre de archivo" exigida por el AUTOLOAD de Fat-Free.
     *
     * @return array Resultado por caso
     */
    public function estructuraModuloTest(){

        $results = [];

        /*====================================*/
        // Archivos obligatorios
        /*====================================*/
        $arrArchivos = [
            'controlador'    => $this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'),
            'vista_nueva'    => $this->rutaModulo('../views/'.self::VISTA_BASE.'-formNew.php'),
            'vista_editar'   => $this->rutaModulo('../views/'.self::VISTA_BASE.'-formEdit.php'),
            'vista_list'     => $this->rutaModulo('../views/'.self::VISTA_BASE.'-UpdateList.php'),
            'vista_view'     => $this->rutaModulo('../views/'.self::VISTA_BASE.'-View.php'),
            'testing'        => $this->rutaModulo('bodegasListadoObservacionesTesting.php'),
            'readme'         => $this->rutaModulo('../README.md'),
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
            $contenido = (string)file_get_contents($ruta);
            $this->addCase($results, 'htaccess_'.$nombre,
                (stripos($contenido, 'deny from all') !== false),
                'el archivo no contiene "deny from all"'
            );
        }

        /*====================================*/
        // Convencion clase = nombre de archivo
        /*====================================*/
        $this->addCase($results, 'clase_controlador_cargada', class_exists(self::CONTROLADOR),
            'la clase '.self::CONTROLADOR.' no esta disponible');
        $this->addCase($results, 'clase_testing_cargada', class_exists('bodegasListadoObservacionesTesting'),
            'la clase bodegasListadoObservacionesTesting no esta disponible');

        /*====================================*/
        // Herencias esperadas por el proyecto
        /*====================================*/
        $padreController = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padreController === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padreController.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('bodegasListadoObservacionesTesting') === 'ControllerBase',
            'el testing no extiende ControllerBase');

        /*====================================*/
        // Soporte de deteccion de tipos de archivo
        /*====================================*/
        $this->addCase($results, 'entorno_finfo', $this->finfoDisponible(),
            'la extension finfo no esta disponible para validar tipos de archivo');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                            CONTROLADOR: VISTAS                             */
    /******************************************************************************/
    /**
     * Verifica el contrato de los metodos que devuelven HTML (`New`, `UpdateList`,
     * `View`, `GetID`): existencia, visibilidad, firma, descifrado del ID y uso de
     * `showVista(2, ...)`.
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];
        $codigo  = $this->codigoFuente($this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'));

        /*====================================*/
        // Firma de cada metodo de vista
        /*====================================*/
        foreach (['New', 'UpdateList', 'View', 'GetID'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 2, 2);
        }

        /*====================================*/
        // Cada metodo de vista descifra y valida el ID recibido
        /*====================================*/
        foreach (['New', 'UpdateList', 'View', 'GetID'] as $metodo) {
            $this->addCase($results, 'descifra_'.$metodo,
                str_contains($codigo, "encryptDecrypt('decrypt', \$params['id'])"),
                $metodo.'() no descifra $params[\'id\']');
            $this->addCase($results, 'valida_'.$metodo, str_contains($codigo, 'isValidDecrypted('),
                $metodo.'() no valida el ID con isValidDecrypted()');
            $this->addCase($results, 'respeta_'.$metodo, str_contains($codigo, "Response::error('Registro inválido', 400)"),
                $metodo.'() no responde 400 ante un ID invalido');
        }

        /*====================================*/
        // Contrato de presentacion
        /*====================================*/
        $this->addCase($results, 'vista_form_new', str_contains($codigo, "\$this->controllerName.'-Resumen-Observaciones-formNew.php'"),
            'New() no instancia el formulario de alta');
        $this->addCase($results, 'vista_form_edit', str_contains($codigo, "\$this->controllerName.'-Resumen-Observaciones-formEdit.php'"),
            'GetID() no instancia el formulario de edicion');
        $this->addCase($results, 'vista_list', str_contains($codigo, "\$this->controllerName.'-Resumen-Observaciones-UpdateList.php'"),
            'UpdateList() no instancia la tabla de observaciones');
        $this->addCase($results, 'vista_view', str_contains($codigo, "\$this->controllerName.'-Resumen-Observaciones-View.php'"),
            'View() no instancia el modal de detalle');
        $this->addCase($results, 'tipoview_modal', substr_count($codigo, 'showVista(2,') >= 4,
            'los metodos de vista no usan showVista(2) (modal)');
        $this->addCase($results, 'rutavista', str_contains($codigo, 'returnRutaVista('),
            'los metodos de vista no resuelven la ruta con returnRutaVista()');
        $this->addCase($results, 'errores_ruta', str_contains($codigo, 'showError(2,'),
            'los metodos de vista no manejan la ruta de error con showError(2)');
        $this->addCase($results, 'usuario', substr_count($codigo, 'getUserData($f3)') >= 4,
            'los metodos de vista no obtienen los datos del usuario');
        $this->addCase($results, 'consulta_listado', str_contains($codigo, 'bodegas_listado_observaciones'),
            'el controlador no consulta la tabla de observaciones');
        $this->addCase($results, 'join_usuarios', str_contains($codigo, 'LEFT JOIN usuarios_listado'),
            'el controlador no resuelve el autor de la observacion');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                         CONTROLADOR: ESCRITURA (CRUD)                      */
    /******************************************************************************/
    /**
     * Verifica el contrato de `Insert`, `Update` y `Delete` SIN ejecutarlos: terminan
     * en `Response::*()` (que hace `exit`) y escriben en base de datos.
     *
     * @return array Resultado por caso
     */
    public function controladorEscrituraTest(){

        $results = [];
        $codigo  = $this->codigoFuente($this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'));

        /*====================================*/
        // Firma de cada metodo de escritura
        /*====================================*/
        $this->firmaMetodo($results, 'Insert', 1, 1);
        $this->firmaMetodo($results, 'Update', 1, 1);
        $this->firmaMetodo($results, 'Delete', 0, 0);

        /*====================================*/
        // Insert: toma el usuario de la sesion y responde 200
        /*====================================*/
        $this->addCase($results, 'insert_sesion', str_contains($codigo, "SESSION.DataInfo.UserID"),
            'Insert() no toma el usuario desde la sesion');
        $this->addCase($results, 'insert_datacheck', str_contains($codigo, '$this->dataCheck($_POST)'),
            'Insert() no construye el DataCheck con dataCheck($_POST)');
        $this->addCase($results, 'insert_tabla', str_contains($codigo, "table'     => 'bodegas_listado_observaciones'"),
            'Insert() no apunta a bodegas_listado_observaciones');
        $this->addCase($results, 'insert_respuesta', str_contains($codigo, 'Response::success('),
            'Insert() no responde con Response::success()');

        /*====================================*/
        // Update: verbo POST y delimitacion por idObservaciones
        /*====================================*/
        $this->addCase($results, 'update_verbo_post', str_contains($codigo, "REQUEST_METHOD'] !== 'POST'"),
            'Update() no valida el verbo HTTP POST');
        $this->addCase($results, 'update_donde', str_contains($codigo, "'where'     => 'idObservaciones'"),
            'Update() no delimita la escritura por idObservaciones');
        // Update conserva el autor y la fecha de creacion y registra quien y cuando edito
        $iniUpd    = strpos($codigo, 'public function Update(');
        $finUpd    = strpos($codigo, 'public function Delete(');
        $cuerpoUpd = ($iniUpd !== false && $finUpd !== false && $finUpd > $iniUpd) ? substr($codigo, $iniUpd, $finUpd - $iniUpd) : '';
        $this->addCase($results, 'update_requeridos', str_contains($cuerpoUpd, "'required'  => 'idObservaciones,Observacion,idUsuarioEdicion,FechaEdicion'"),
            'Update() no declara los obligatorios idObservaciones,Observacion,idUsuarioEdicion,FechaEdicion');
        $this->addCase($results, 'update_conserva_autor_y_fecha',
            $cuerpoUpd !== ''
            && str_contains($cuerpoUpd, "'data'      => 'idObservaciones,Observacion,idUsuarioEdicion,FechaEdicion'")
            && !str_contains($cuerpoUpd, "'idUsuario'") && !str_contains($cuerpoUpd, 'FechaCreacion'),
            'Update() no debe escribir idUsuario ni FechaCreacion (se conservan autor y fecha original)');
        $this->addCase($results, 'update_registra_edicion',
            str_contains($cuerpoUpd, "\$_POST['idUsuarioEdicion'] = \$f3->get('SESSION.DataInfo.UserID');")
            && str_contains($cuerpoUpd, "\$_POST['FechaEdicion']     = date('Y-m-d H:i:s');")
            && strpos($cuerpoUpd, "\$_POST['idUsuarioEdicion']") < strpos($cuerpoUpd, '$this->dataCheck($_POST)'),
            'Update() debe registrar usuario y fecha de la edicion antes de validar el POST');
        $this->addCase($results, 'listado_con_editor',
            preg_match('/LEFT JOIN usuarios_listado AS usuarios_edicion\s+ON usuarios_edicion\.idUsuario\s+= bodegas_listado_observaciones\.idUsuarioEdicion/', $codigo) === 1
            && str_contains($codigo, 'bodegas_listado_observaciones.FechaEdicion') && str_contains($codigo, 'usuarios_edicion.Nombre AS UsuarioEdicion'),
            'UpdateList() debe unir el usuario que edito y entregar FechaEdicion');

        /*====================================*/
        // Delete: verbo DELETE y borrado puntual
        /*====================================*/
        $this->addCase($results, 'delete_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'DELETE'"),
            'Delete() no valida el verbo HTTP DELETE');
        $this->addCase($results, 'delete_donde', str_contains($codigo, "'where'       => 'idObservaciones'"),
            'Delete() no delimita el borrado por idObservaciones');
        $this->addCase($results, 'delete_sin_archivos', str_contains($codigo, "'files'       => ''"),
            'Delete() declara archivos que este subrecurso no gestiona');

        /*====================================*/
        // Transaccion: cada escritura (Insert/Update/Delete) abre la transaccion,
        // la confirma y la revierte ante el fallo de la Base de Datos
        /*====================================*/
        $this->addCase($results, 'transaccion_begin',
            substr_count($codigo, 'Base_transactionBegin()') === 3,
            'Insert/Update/Delete deben abrir transaccion');
        $this->addCase($results, 'transaccion_commit',
            substr_count($codigo, 'Base_transactionCommit()') === 3,
            'Insert/Update/Delete deben confirmar la transaccion');
        $this->addCase($results, 'transaccion_rollback',
            substr_count($codigo, 'Base_transactionRollback()') === 0,
            'Insert/Update/Delete deben revertir la transaccion ante el fallo de la Base');
        $this->addCase($results, 'transaccion_rollback_antes_de_error',
            substr_count($codigo, 'failOperation(') === 3,
            'cada punto de fallo de Base debe invocar failOperation');
        $this->addCase($results, 'transaccion_commit_antes_de_exito',
            strpos($codigo, 'Base_transactionCommit()') < strpos($codigo, 'Response::success('),
            'la confirmacion debe ocurrir antes de responder con exito');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                PERMISOS                                    */
    /******************************************************************************/
    /**
     * Verifica que el subrecurso comparta la raiz de permisos con `bodegasListado`
     * (asi esta disennado el controlador) y que la ruta de observaciones este
     * registrada en la sesion y en el router de F3.
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
        // Nivel de acceso resuelto con la raiz compartida
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

        /*====================================*/
        // El controlador declara explicitamente la raiz de permisos
        /*====================================*/
        $codigo = $this->codigoFuente($this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'));
        $this->addCase($results, 'controllername_compartido',
            str_contains($codigo, "\$this->controllerName = '".self::CONTROLADOR_PERMISOS."'"),
            'el controlador no fija controllerName en '.self::CONTROLADOR_PERMISOS);

        /*====================================*/
        // Registro de la ruta en SESSION.arrPermisos
        /*====================================*/
        $arrPermisos = $f3->get('SESSION.arrPermisos');
        $this->addCase($results, 'permisos_de_ruta_presentes', is_array($arrPermisos),
            'SESSION.arrPermisos no esta disponible');

        if (is_array($arrPermisos)) {
            $rutaOk = false;
            foreach ($arrPermisos as $permiso) {
                if (isset($permiso['RutaWeb']) && $permiso['RutaWeb'] === self::RUTA_OBSERVACIONES) {
                    $rutaOk = true;
                    break;
                }
            }
            $this->addCase($results, 'ruta_registrada', $rutaOk,
                'la ruta '.self::RUTA_OBSERVACIONES.' no esta entre los permisos del usuario');
        }

        /*====================================*/
        // Rutas en el router de F3
        // (el router de F3 se arma con SESSION.arrPermisos, por lo que solo se
        // evalua el manejador cuando la ruta esta registrada para este usuario)
        /*====================================*/
        $arrRoutes = $f3->get('ROUTES');
        $this->addCase($results, 'router_disponible', is_array($arrRoutes),
            'F3 no expuso la variable ROUTES');

        $esquema = [
            'ruta_listado_router'  => ['/'.self::RUTA_OBSERVACIONES.'/updateList/@id', 'GET',    self::CONTROLADOR.'->UpdateList'],
            'ruta_alta_router'     => ['/'.self::RUTA_OBSERVACIONES,                  'POST',   self::CONTROLADOR.'->Insert'],
            'ruta_edicion_router'  => ['/'.self::RUTA_OBSERVACIONES.'/update',        'POST',   self::CONTROLADOR.'->Update'],
            'ruta_borrado_router'  => ['/'.self::RUTA_OBSERVACIONES,                  'DELETE', self::CONTROLADOR.'->Delete'],
        ];
        foreach ($esquema as $caso => $rutaEsperada) {
            [$ruta, $verbo, $handlerEsperado] = $rutaEsperada;
            if (!isset($arrRoutes[$ruta])) {
                continue;
            }
            $handler = $arrRoutes[$ruta][0][$verbo][0] ?? '';
            $this->addCase($results, $caso, $handler === $handlerEsperado,
                'la ruta '.$ruta.' ('.$verbo.') apunta a '.var_export($handler, true).' en vez de '.$handlerEsperado);
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /**
     * Renderiza las cuatro vistas del subrecurso con datos sinteticos y comprueba
     * el contrato de ids, endpoints y contenido.
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function vistasObservacionesTest($f3){

        $results = [];
        $BASE    = (string)$f3->get('BASE');
        if (trim($BASE) === '') {
            $BASE = '/admin';
        }

        /*====================================*/
        // Tabla de observaciones
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::VISTA_BASE.'-UpdateList.php'),
            $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'tabla_render', $html !== '', 'la vista UpdateList no produjo HTML');
        $this->addCase($results, 'tabla_columnas',
            str_contains($html, 'Fecha Creacion') && str_contains($html, 'Observacion')
            && str_contains($html, 'Acciones'),
            'la tabla no declara sus tres columnas');
        $this->addCase($results, 'tabla_autor', str_contains($html, '<strong>Tester:</strong>'),
            'la tabla no resalta el autor de la observacion');
        $this->addCase($results, 'tabla_edicion_visible', str_contains($html, 'Editado por Editor el 03-02-2026'),
            'la tabla debe mostrar quien y cuando edito la observacion');
        $sinEdicion = $this->datosSinteticos(2);
        $sinEdicion['arrObservaciones'][0]['FechaEdicion']   = null;
        $sinEdicion['arrObservaciones'][0]['UsuarioEdicion'] = null;
        $htmlSinEd = $this->renderizarVista($this->rutaModulo('../views/'.self::VISTA_BASE.'-UpdateList.php'), $sinEdicion, $BASE, $f3);
        $this->addCase($results, 'tabla_sin_edicion_oculta', $htmlSinEd !== '' && !str_contains($htmlSinEd, 'Editado por'),
            'una observacion nunca editada no debe mostrar la leyenda de edicion');
        $this->addCase($results, 'tabla_acciones',
            str_contains($html, 'tabObsView(') && str_contains($html, 'tabObsEdit(') && str_contains($html, 'tabObsDel('),
            'la tabla no publica las tres acciones del subrecurso');
        $this->addCase($results, 'tabla_id_cifrado', preg_match('/tabObsView\(\'[A-Za-z0-9_-]{10,}\'\)/', $html) === 1,
            'la tabla no envia el ID cifrado a la accion Ver');

        /*====================================*/
        // Tabla vacia
        /*====================================*/
        $sinDatos = $this->datosSinteticos(2);
        $sinDatos['arrObservaciones'] = [];
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::VISTA_BASE.'-UpdateList.php'),
            $sinDatos, $BASE, $f3);
        $this->addCase($results, 'tabla_vacia', str_contains($html, '<tbody>') && !str_contains($html, 'tabObsView('),
            'con la lista vacia la tabla sigue pintando filas');

        /*====================================*/
        // Modal de detalle
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::VISTA_BASE.'-View.php'),
            $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'view_render', $html !== '', 'la vista View no produjo HTML');
        $this->addCase($results, 'view_titulo', str_contains($html, 'Ver Datos'),
            'la vista View no muestra el titulo del modal');
        $this->addCase($results, 'view_contenido',
            str_contains($html, 'Fecha Creacion') && str_contains($html, 'Observacion de prueba'),
            'la vista View no pinta la observacion');
        $this->addCase($results, 'view_pie', str_contains($html, 'data-bs-dismiss="modal"'),
            'la vista View no ofrece el pie de cierre');

        /*====================================*/
        // Formulario de alta
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::VISTA_BASE.'-formNew.php'),
            $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'form_new_render', $html !== '', 'la vista formNew no produjo HTML');
        $this->addCase($results, 'form_new_id', str_contains($html, 'id="FormNewObservacion"'),
            'el formulario de alta no usa el id FormNewObservacion');
        $this->addCase($results, 'form_new_oculto', str_contains($html, 'name="idBodegas"'),
            'el formulario de alta no arrastra la bodega propietaria');
        $this->addCase($results, 'form_new_area', str_contains($html, 'name="Observacion"'),
            'el formulario de alta no declara el campo Observacion');
        $this->addCase($results, 'form_new_endpoint',
            str_contains($html, self::RUTA_WEB.'/observaciones') && str_contains($html, "Metodo      = 'POST'"),
            'el formulario de alta no publica en '.self::RUTA_OBSERVACIONES.' con POST');
        $this->addCase($results, 'form_new_refresco', str_contains($html, '/observaciones/updateList/'),
            'el formulario de alta no refresca la tabla tras guardar');

        /*====================================*/
        // Formulario de edicion
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::VISTA_BASE.'-formEdit.php'),
            $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'form_edit_render', $html !== '', 'la vista formEdit no produjo HTML');
        $this->addCase($results, 'form_edit_id', str_contains($html, 'id="FormEditObservacion"'),
            'el formulario de edicion no usa el id FormEditObservacion');
        $this->addCase($results, 'form_edit_sin_fecha_creacion', !str_contains($html, 'name="FechaCreacion"'),
            'el formulario de edicion no debe reenviar FechaCreacion (se conserva la original)');
        $this->addCase($results, 'form_edit_id_oculto', str_contains($html, 'name="idObservaciones"'),
            'el formulario de edicion no arrastra el ID de la observacion');
        $this->addCase($results, 'form_edit_valor', str_contains($html, 'Observacion de prueba'),
            'el formulario de edicion no precarga el texto actual');
        $this->addCase($results, 'form_edit_endpoint',
            str_contains($html, self::RUTA_WEB.'/observaciones/update') && str_contains($html, "Metodo      = 'POST'"),
            'el formulario de edicion no publica en '.self::RUTA_OBSERVACIONES.'/update con POST');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos el chequeo de datos del controlador (`dataCheck`).
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
            // Logica pura: se crea el objeto sin ejecutar el constructor (sin BD)
            $Controller = (new ReflectionClass(self::CONTROLADOR))->newInstanceWithoutConstructor();

            $Post      = ['idBodegas' => 1, 'Observacion' => 'Texto de prueba', 'FechaCreacion' => '2026-01-02'];
            $DataCheck = $reflector->invoke($Controller, $Post);

            $this->addCase($results, 'datacheck_es_arreglo', is_array($DataCheck),
                'dataCheck() no devolvio un arreglo');
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post,
                'dataCheck() no conserva el POST recibido');
            $this->addCase($results, 'datacheck_idbodegas',
                str_contains((string)($DataCheck['ValidarNumero'] ?? ''), 'idBodegas')
                && str_contains((string)($DataCheck['ValidarEntero'] ?? ''), 'idBodegas'),
                'dataCheck() no valida idBodegas como numero entero');
            $this->addCase($results, 'datacheck_fecha', str_contains((string)($DataCheck['ValidarFecha'] ?? ''), 'FechaCreacion'),
                'dataCheck() no valida FechaCreacion');
            $this->addCase($results, 'datacheck_texto', str_contains((string)($DataCheck['ValidarLargoMinimo'] ?? ''), 'Observacion'),
                'dataCheck() no valida la longitud minima de Observacion');
            $this->addCase($results, 'datacheck_largo_minimo', (int)($DataCheck['ValidarLargoMinimoN'] ?? 0) === 3,
                'dataCheck() no fija el largo minimo en 3');
            $this->addCase($results, 'datacheck_censura', str_contains((string)($DataCheck['ValidarPalabrasCensuradas'] ?? ''), 'Observacion'),
                'dataCheck() no aplica el filtro de palabras a Observacion');
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_es_arreglo', false,
                'no se pudo ejecutar dataCheck() ('.$e->getMessage().')');
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                 SEGURIDAD                                  */
    /******************************************************************************/
    /**
     * Revisa estaticamente las vistas y el controlador del subrecurso: ausencia de
     * rutas relativas maliciosas, de rutas absolutas del sistema de ficheros, de
     * acceso directo a la sesion desde las plantillas y de SQL concatenado.
     *
     * @return array Resultado por caso
     */
    public function seguridadVistasTest(){

        $results = [];

        /*====================================*/
        // Vistas involucradas
        /*====================================*/
        $arrVistas = glob($this->rutaModulo('../views/'.self::VISTA_BASE.'-*.php')) ?: [];
        $this->addCase($results, 'hay_vistas', count($arrVistas) === 4,
            'se encontraron '.count($arrVistas).' vistas (esperadas 4)');

        foreach ($arrVistas as $ruta) {
            $nombre = basename($ruta);
            $codigo = $this->codigoFuente($ruta);

            $this->addCase($results, 'vista_sin_retroceso_'.$nombre, !str_contains($codigo, '..'),
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
        // Escape de los datos pintados en la tabla
        /*====================================*/
        $tabla = $this->codigoFuente($this->rutaModulo('../views/'.self::VISTA_BASE.'-UpdateList.php'));
        $this->addCase($results, 'tabla_escape_fecha', str_contains($tabla, 'fechaEstandar('),
            'UpdateList.php no normaliza la fecha antes de pintarla');
        $this->addCase($results, 'tabla_autor_negrita', str_contains($tabla, '<strong>'),
            'UpdateList.php no marca el autor de la observacion');

        /*====================================*/
        // El controlador no concatena SQL desde el cliente
        /*====================================*/
        $controlador = $this->codigoFuente($this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'));
        $this->addCase($results, 'controlador_sin_concat_sql',
            !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $controlador),
            'el controlador concatena superglobales en una cadena SQL');
        $this->addCase($results, 'controlador_parametros_preparados',
            str_contains($controlador, "'params'"),
            'el controlador no usa parametros preparados');

        /************************************/
        // Retorno los datos
        return $results;

    }

}
