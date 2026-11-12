<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class tokensListadoPermisosTesting
 *
 * Pruebas del modulo `tokens` - controlador `tokensListadoPermisos` (matriz de modulos de un
 * token: alta y baja por modulo exponible por el MCP, sin nivel; ADR-0019).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. El normalizador
 *    `sistemaTesteos::normalizeResult()` marca EXITOSA o ERROR segun el contenido, por lo que
 *    los textos de los casos correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Alcance: pruebas NO destructivas. `Update()` y `UpdateList()` terminan en `Response::*()`
 * (que ejecutan `exit`) y `Update()` escribe en `tokens_listado_permisos`, por eso NO se invocan:
 * se verifican por reflexion y lectura del fuente. Lo que se puede comprobar sin escribir se
 * ejecuta de verdad con lecturas (`getModulos` de un token inexistente, `tokenExists`,
 * descifrado de ids, integridad del indice unico y de las filas huerfanas).
 *
 * Decisiones que fijan estas pruebas: D-27 (el alcance del token es independiente del usuario que
 * lo edita; el unico requisito es el nivel Editar en Tokens) y ADR-0019 (alcance por modulo).
 *
 * @see projects/tokens-permisos-modulo/specs/technical.md §6
 *
 * @see skills/testing/SKILL.md
 */
class tokensListadoPermisosTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR          = 'tokensListadoPermisos';
    private const CONTROLADOR_PADRE    = 'tokensListado';
    private const CONTROLADOR_PERMISOS = 'tokensListado';
    private const INSTALADOR           = 'tokensInstaller';
    private const RUTA_WEB             = 'administracion/tokens/listado';
    private const RUTA_WEB_UPDATELIST  = 'administracion/tokens/listado/permisos/updateList/@id';
    private const RUTA_WEB_UPDATE      = 'administracion/tokens/listado/permisos/update';
    private const VISTA                = 'tokensListado-Resumen-Permisos-List';
    // Vista antigua (CRUD Normal): retirada al pasar `tokensListado` a CRUD Resumen
    private const VISTA_ANTIGUA        = 'tokensListado-Permisos-UpdateList';
    private const TABLA                = 'tokens_listado_permisos';
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
    // Cuerpo (fuente) de un metodo de una clase, para pruebas estaticas
    /*******************************************************************/
    private function cuerpoMetodo(string $metodo, string $clase = self::CONTROLADOR): string{
        if (!method_exists($clase, $metodo)) {
            return '';
        }
        $r      = new ReflectionMethod($clase, $metodo);
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
    // Posicion de una subcadena dentro del cuerpo (false si no esta)
    /*******************************************************************/
    private function pos(string $cuerpo, string $aguja){
        return strpos($cuerpo, $aguja);
    }

    /*******************************************************************/
    // Instancia del controlador con la conexion real (lecturas) para invocar
    // privados de solo lectura por reflexion
    /*******************************************************************/
    private function instanciaControlador(){
        return new (self::CONTROLADOR)();
    }

    /*******************************************************************/
    // Invoca un metodo privado del controlador y devuelve su resultado
    /*******************************************************************/
    private function invocarPrivado($instancia, string $metodo, array $args = []){
        $r = new ReflectionMethod(self::CONTROLADOR, $metodo);
        $r->setAccessible(true);
        return $r->invokeArgs($instancia, $args);
    }

    /*******************************************************************/
    // Id de un token existente de un origen (1 manual, 2 acceso OAuth; lectura), 0 si no hay ninguno
    /*******************************************************************/
    private function idTokenExistente(int $origen = 1): int{
        $query = [
            'data'   => 'idToken', 'table' => 'tokens_listado', 'join' => '', 'where' => 'Origen = ?',
            'params' => [$origen], 'group' => '', 'having' => '', 'order' => 'idToken ASC', 'limit' => 1,
        ];
        $xParams = ['query' => $query];
        $resp    = $this->Base_GetList($xParams);
        if (($resp['status'] ?? false) !== true || empty($resp['data'])) {
            return 0;
        }
        return (int)$resp['data'][0]['idToken'];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica los archivos del controlador `tokensListadoPermisos` (controlador, vista
     * `-Resumen-Permisos-List`, testing y proteccion `.htaccess`), la convencion "clase =
     * nombre de archivo" del AUTOLOAD de Fat-Free y la firma de `UpdateList` y `Update`.
     *
     * @return array Resultado por caso
     */
    public function estructuraModuloTest(){

        $results = [];

        /*====================================*/
        // Archivos obligatorios del controlador
        /*====================================*/
        $arrArchivos = [
            'controlador'    => $this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'),
            'vista_permisos' => $this->rutaModulo('../views/'.self::VISTA.'.php'),
            'instalador'     => $this->rutaModulo('../installer/'.self::INSTALADOR.'.php'),
            'testing'        => $this->rutaModulo('tokensListadoPermisosTesting.php'),
            'helper_modulos' => $this->rutaModulo('../functions/InternalFunctionsTokensModulos.php'),
            'testing_helper' => $this->rutaModulo('InternalFunctionsTokensModulosTesting.php'),
        ];
        foreach ($arrArchivos as $nombre => $ruta) {
            $this->addCase($results, 'archivo_'.$nombre, is_file($ruta), 'no se encuentra '.$ruta);
        }

        /*====================================*/
        // La vista antigua ya no existe y el controlador carga la nueva
        /*====================================*/
        $this->addCase($results, 'vista_antigua_retirada', !is_file($this->rutaModulo('../views/'.self::VISTA_ANTIGUA.'.php')),
            'no debe existir la vista antigua '.self::VISTA_ANTIGUA.'.php');
        $this->addCase($results, 'controlador_carga_vista_nueva',
            str_contains($this->fuenteControlador(), "'/'.\$this->controllerName.'-Resumen-Permisos-List.php'") && !str_contains($this->fuenteControlador(), 'Permisos-UpdateList'),
            'UpdateList() debe cargar '.self::VISTA.'.php y no la vista antigua');

        /*====================================*/
        // testing/ protegido contra acceso web directo
        /*====================================*/
        $htaccess = $this->codigoFuente($this->rutaModulo('.htaccess'));
        $this->addCase($results, 'testing_htaccess_deny', str_contains($htaccess, 'deny from all'),
            'testing/.htaccess debe contener "deny from all"');

        /*====================================*/
        // Clase = archivo, herencia y firmas
        /*====================================*/
        $this->addCase($results, 'clase_existe', class_exists(self::CONTROLADOR), 'no existe la clase '.self::CONTROLADOR);
        if (class_exists(self::CONTROLADOR)) {
            $this->addCase($results, 'clase_extiende_ControllerBase', is_subclass_of(self::CONTROLADOR, 'ControllerBase'),
                self::CONTROLADOR.' debe extender ControllerBase');
            // Hereda beforeroute (guardia CSRF) sin sobrescribirlo
            $this->addCase($results, 'no_sobrescribe_beforeroute',
                (new ReflectionMethod(self::CONTROLADOR, 'beforeroute'))->getDeclaringClass()->getName() === 'ControllerBase',
                self::CONTROLADOR.' sobrescribe beforeroute y puede saltarse la guardia CSRF');
        }

        $firmas = ['UpdateList' => 2, 'Update' => 0];
        foreach ($firmas as $metodo => $parametros) {
            $existe = method_exists(self::CONTROLADOR, $metodo);
            $this->addCase($results, 'metodo_'.$metodo, $existe, 'no existe '.self::CONTROLADOR.'::'.$metodo.'()');
            if ($existe) {
                $r = new ReflectionMethod(self::CONTROLADOR, $metodo);
                $this->addCase($results, 'metodo_'.$metodo.'_publico', $r->isPublic(), $metodo.'() no es publico');
                $this->addCase($results, 'metodo_'.$metodo.'_parametros', $r->getNumberOfParameters() === $parametros,
                    $metodo.'() declara '.$r->getNumberOfParameters().' parametros (esperado '.$parametros.')');
            }
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                       CONTROLADOR: RUTAS E INSTALADOR                      */
    /******************************************************************************/
    /**
     * Matriz de endpoints de `tokensListadoPermisos` publicada por el instalador: GET
     * `permisos/updateList/@id` y POST `permisos/update`, ambos con nivel 2, handler del
     * subcontrolador y CSRF heredado de `ControllerBase::beforeroute` (solo escritura).
     *
     * @return array Resultado por caso
     */
    public function controladorRutasTest(){

        $results = [];
        $rutas   = $this->rutasInstalador();

        $this->addCase($results, 'instalador_rutas_leidas', count($rutas) > 0,
            'no se pudieron leer las rutas de '.self::INSTALADOR.'::getRouteDefinitions()');

        /*====================================*/
        // Matriz: [clave idMetodo+ruta => handler esperado]. 1=GET, 2=POST
        /*====================================*/
        $matriz = [
            'updatelist' => ['1 '.self::RUTA_WEB_UPDATELIST, self::CONTROLADOR.'->UpdateList'],
            'update'     => ['2 '.self::RUTA_WEB_UPDATE,     self::CONTROLADOR.'->Update'],
        ];
        foreach ($matriz as $nombre => [$clave, $handler]) {
            $existe = isset($rutas[$clave]);
            $this->addCase($results, 'ruta_'.$nombre, $existe, 'el instalador no publica la ruta '.$clave);
            if (!$existe) {
                continue;
            }
            $this->addCase($results, 'ruta_'.$nombre.'_handler', strcasecmp($rutas[$clave][0], $handler) === 0,
                'la ruta '.$clave.' apunta a '.$rutas[$clave][0].' en vez de '.$handler);
            $this->addCase($results, 'ruta_'.$nombre.'_nivel_2', $rutas[$clave][1] === 2,
                'la ruta '.$clave.' exige nivel '.$rutas[$clave][1].' en vez de 2 (Editar en Tokens, D-27)');
            $this->addCase($results, 'ruta_'.$nombre.'_controller', $rutas[$clave][2] === self::CONTROLADOR,
                'la ruta '.$clave.' queda asignada a '.$rutas[$clave][2]);
            $partes = explode('->', $rutas[$clave][0]);
            $this->addCase($results, 'ruta_'.$nombre.'_metodo_existe',
                count($partes) === 2 && method_exists($partes[0], $partes[1]),
                'el handler '.$rutas[$clave][0].' no existe');
        }

        /*====================================*/
        // Solo dos rutas propias, ninguna de solo lectura por debajo de nivel 2
        /*====================================*/
        $propias = array_filter($rutas, static function ($r) { return $r[2] === 'tokensListadoPermisos'; });
        $this->addCase($results, 'rutas_propias_completas', count($propias) === 2,
            'se esperaban 2 rutas de '.self::CONTROLADOR.' y hay '.count($propias));
        $nivelMinimo = $propias ? min(array_column($propias, 1)) : 0;
        $this->addCase($results, 'rutas_propias_nivel_minimo_2', $nivelMinimo >= 2,
            'una ruta de la matriz de permisos exige nivel '.$nivelMinimo.' (modifica permisos: minimo 2)');

        /*====================================*/
        // Verbo: la escritura solo por POST (405 en el cuerpo) y no hay otro verbo de escritura
        /*====================================*/
        $update = $this->cuerpoMetodo('Update');
        $this->addCase($results, 'update_verbo_post', str_contains($update, "REQUEST_METHOD'] !== 'POST'") && str_contains($update, '405'),
            'Update() no valida el verbo HTTP POST con 405');
        $this->addCase($results, 'update_verbo_antes_de_consultar',
            $this->pos($update, "REQUEST_METHOD'] !== 'POST'") !== false && $this->pos($update, "REQUEST_METHOD'] !== 'POST'") < $this->pos($update, '$query = ['),
            'Update() debe validar el verbo antes de armar cualquier consulta');
        $this->addCase($results, 'update_sin_verbo_get',
            !isset($rutas['1 '.self::RUTA_WEB_UPDATE]),
            'la ruta '.self::RUTA_WEB_UPDATE.' no debe publicarse por GET');

        /*====================================*/
        // UpdateList: 400 cuando falta el id (parametro de ruta)
        /*====================================*/
        $list = $this->cuerpoMetodo('UpdateList');
        $this->addCase($results, 'updatelist_id_obligatorio',
            str_contains($list, "!isset(\$params['id'])") && str_contains($list, "Response::error('Registro ID inexistente', 400)"),
            'UpdateList() debe responder 400 cuando no llega el id');

        /*====================================*/
        // CSRF: ControllerBase::beforeroute aplica la guardia y esta no es opcional
        /*====================================*/
        $base = $this->cuerpoMetodo('beforeroute', 'ControllerBase');
        $this->addCase($results, 'csrf_guard_en_beforeroute', str_contains($base, 'CsrfToken::guard('),
            'ControllerBase::beforeroute() no invoca CsrfToken::guard() (las escrituras quedarian sin CSRF)');
        $this->addCase($results, 'csrf_controlador_sin_exclusion',
            !str_contains($this->fuenteControlador(), 'CsrfToken') && !str_contains($this->fuenteControlador(), 'beforeroute'),
            self::CONTROLADOR.' no debe tocar ni desactivar la guardia CSRF');

        /*====================================*/
        // Desinstalacion: el controlador debe estar en RutaController()
        /*====================================*/
        $this->addCase($results, 'instalador_desinstala_controlador',
            str_contains($this->fuenteInstalador(), "\$RutaController .= ',tokensListadoPermisos'"),
            'RutaController() no lista tokensListadoPermisos y dejaria rutas huerfanas al desinstalar');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                PERMISOS                                    */
    /******************************************************************************/
    /**
     * Verifica la sesion, el nivel de acceso heredado de `tokensListado` y el registro de
     * las rutas de permisos en `SESSION.arrPermisos` y en el router de F3.
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
        // Nivel de acceso resuelto con la raiz de permisos del modulo
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
        // Registro dinamico de las rutas desde SESSION.arrPermisos
        /*====================================*/
        $arrPermisos = $f3->get('SESSION.arrPermisos');
        $this->addCase($results, 'permisos_de_ruta_presentes', is_array($arrPermisos),
            'SESSION.arrPermisos no esta disponible');
        if (is_array($arrPermisos)) {
            $rutaOk = false;
            foreach ($arrPermisos as $permiso) {
                if (isset($permiso['RutaWeb']) && $permiso['RutaWeb'] === self::RUTA_WEB_UPDATE) {
                    $rutaOk = true;
                    break;
                }
            }
            // Con nivel 1 (solo ver) las rutas de nivel 2 no se registran: es el comportamiento esperado
            $this->addCase($results, 'ruta_registrada', $nivel === null || (int)$nivel < 2 || $rutaOk,
                'la ruta '.self::RUTA_WEB_UPDATE.' no esta entre los permisos del usuario');
        }

        /*====================================*/
        // Router de F3: solo se evalua si el usuario tiene la ruta registrada
        /*====================================*/
        $arrRoutes = $f3->get('ROUTES');
        $this->addCase($results, 'router_disponible', is_array($arrRoutes), 'F3 no expuso la variable ROUTES');
        $ruta = '/'.self::RUTA_WEB_UPDATE;
        if (is_array($arrRoutes) && isset($arrRoutes[$ruta])) {
            $handler = $arrRoutes[$ruta][0]['POST'][0] ?? '';
            $this->addCase($results, 'ruta_update_router', strcasecmp($handler, self::CONTROLADOR.'->Update') === 0,
                'la ruta '.$ruta.' apunta a '.var_export($handler, true).' en vez de '.self::CONTROLADOR.'->Update');
            $this->addCase($results, 'ruta_update_sin_get', !isset($arrRoutes[$ruta][0]['GET']),
                'la ruta '.$ruta.' no debe responder por GET');
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                  ASIGNACION INDEPENDIENTE DEL USUARIO (D-27)                */
    /******************************************************************************/
    /**
     * Un usuario con nivel Editar en Tokens puede asignar a un token cualquier modulo exponible, tenga o
     * no acceso a el (D-27). Sin ejecutar `Update()`: la lista de modulos se ejecuta con una lectura real
     * (`getModulos` de un token inexistente) y se comprueba que no depende de la sesion; el alta se
     * verifica en el fuente (insercion en lote de `(idToken, Modulo, fechaCreacion)` sin filtro por los
     * permisos del usuario).
     *
     * @return array Resultado por caso
     */
    public function asignacionIndependienteUsuarioTest(){

        $results = [];
        $update  = $this->cuerpoMetodo('Update');
        $getMod  = $this->cuerpoMetodo('getModulos');
        $this->addCase($results, 'fuente_update_leido', $update !== '', 'no se pudo leer Update()');
        $this->addCase($results, 'fuente_getmodulos_leido', $getMod !== '', 'no se pudo leer getModulos()');

        /*====================================*/
        // Lista: modulos exponibles, sin cruzar con los permisos del usuario
        /*====================================*/
        $this->addCase($results, 'lista_origen_exponibles', str_contains($getMod, 'InternalFunctionsTokensModulos::listExposable()'),
            'getModulos() debe partir de InternalFunctionsTokensModulos::listExposable()');
        $this->addCase($results, 'lista_sin_permisos_usuario',
            !str_contains($getMod, 'usuarios_listado_permisos') && !str_contains($getMod, 'SESSION') && !str_contains($getMod, 'arrPermisos'),
            'getModulos() no debe filtrar por los permisos del usuario de la sesion (D-27)');
        $this->addCase($results, 'lista_estado_del_token',
            str_contains($getMod, "'table'   => 'tokens_listado_permisos'") && str_contains($getMod, "'where'   => 'idToken = ?'") && str_contains($getMod, '[$idToken]'),
            'getModulos() debe leer los modulos asignados al token (no los del usuario) con parametros preparados');

        /*====================================*/
        // Update(): no consulta sesion ni nivel del usuario para decidir que asignar
        /*====================================*/
        $this->addCase($results, 'update_sin_sesion_usuario',
            !str_contains($update, 'SESSION') && !str_contains($update, 'getArrLevel') && !str_contains($update, 'getUserData')
            && !str_contains($update, 'usuarios_listado_permisos'),
            'Update() no debe condicionar los modulos del token a los del usuario (D-27)');
        $this->addCase($results, 'update_inserta_nuevos',
            str_contains($update, '$aInsertar[] =') && str_contains($update, 'Base_insertMultiple(')
            && str_contains($update, "'table'     => 'tokens_listado_permisos'"),
            'Update() debe insertar en tokens_listado_permisos los modulos nuevos marcados');
        $this->addCase($results, 'update_inserta_campos',
            str_contains($update, "'data'      => 'idToken,Modulo,fechaCreacion'") && str_contains($update, "'required'  => 'idToken,Modulo'"),
            'la insercion debe declarar idToken, Modulo y fechaCreacion (requeridos idToken y Modulo)');
        $this->addCase($results, 'update_sin_nivel', !str_contains($update, 'idLevelLimit') && !str_contains($update, 'level_') && !str_contains($update, 'idPermisos'),
            'Update() no debe referenciar nivel ni idPermisos (ADR-0019)');
        $this->addCase($results, 'update_inserta_en_lote_una_vez',
            substr_count($update, 'Base_insertMultiple(') === 1 && str_contains($update, 'if ($aInsertar)'),
            'los modulos nuevos deben insertarse en una sola sentencia y solo si hay filas');
        $this->addCase($results, 'update_alta_solo_si_no_existia',
            preg_match('/\$marcado && \$modulo\[\'activo\'\] === 0\).*?\$aInsertar\[\]/s', $update) === 1,
            'el alta debe producirse solo para un modulo marcado que no estaba asignado');
        $this->addCase($results, 'update_fecha_del_servidor', substr_count($update, '$this->ServerServer->fechaActual()') === 1,
            'la fecha de asignacion debe venir del servidor ('.substr_count($update, '$this->ServerServer->fechaActual()').' usos)');
        $this->addCase($results, 'duplicado_responde_400', str_contains($update, "Response::error('Módulo duplicado', 400)"),
            'un choque con el indice unico debe responder 400 "Módulo duplicado"');

        /*====================================*/
        // dataCheck: id numerico y entero, Modulo con largo maximo 64, sin campos de permiso/nivel
        /*====================================*/
        try {
            $reflector  = new ReflectionMethod(self::CONTROLADOR, 'dataCheck');
            $reflector->setAccessible(true);
            $Controller = (new ReflectionClass(self::CONTROLADOR))->newInstanceWithoutConstructor();
            $Post       = ['idToken' => 7, 'Modulo' => 'bodegas', 'fechaCreacion' => '2026-01-02'];
            $DataCheck  = $reflector->invoke($Controller, $Post);
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post, 'dataCheck() no conserva el POST recibido');
            $this->addCase($results, 'datacheck_numericos', ($DataCheck['ValidarNumero'] ?? '') === 'idToken', 'dataCheck() debe validar solo idToken como numero');
            $this->addCase($results, 'datacheck_enteros', ($DataCheck['ValidarEntero'] ?? '') === 'idToken', 'dataCheck() debe validar solo idToken como entero');
            $this->addCase($results, 'datacheck_largo_modulo', ($DataCheck['ValidarLargoMaximoPorCampo'] ?? null) === ['Modulo' => 64],
                'dataCheck() debe limitar Modulo a 64 caracteres');
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_post', false, 'dataCheck() no es accesible por reflexion: '.$e->getMessage());
        }

        /*====================================*/
        // Ejecucion real (lectura): token inexistente -> ningun modulo asignado
        /*====================================*/
        try {
            $ctrl = $this->instanciaControlador();
            $resp = $this->invocarPrivado($ctrl, 'getModulos', [0]);
            $ok   = ($resp['status'] ?? false) === true && is_array($resp['data'] ?? null);
            $this->addCase($results, 'lista_ejecuta', $ok, 'getModulos(0) no devolvio la lista ('.json_encode($resp['error'] ?? null).')');
            if ($ok) {
                $activos = array_filter($resp['data'], static function ($f) { return (int)$f['activo'] !== 0; });
                $this->addCase($results, 'lista_token_sin_asignar', count($activos) === 0,
                    'un token inexistente aparece con '.count($activos).' modulos asignados');
                $formaOk = true;
                foreach ($resp['data'] as $f) {
                    if (!isset($f['modulo'], $f['nombre'], $f['descripcion']) || !in_array($f['activo'], [0, 1], true)) {
                        $formaOk = false;
                    }
                }
                $this->addCase($results, 'lista_forma_filas', $formaOk, 'cada fila debe traer modulo, nombre, descripcion y activo (0|1)');
            }
        } catch (Throwable $e) {
            $this->addCase($results, 'lista_ejecuta', false, 'getModulos(0) lanzo '.$e->getMessage());
        }

        /*====================================*/
        // BD (lectura): indice unico idToken+Modulo => un alta repetida nunca duplica filas
        /*====================================*/
        $unico = false;
        try {
            $filas     = $this->getDBConn()->exec('SHOW INDEX FROM '.self::TABLA);
            $porIndice = [];
            foreach ((array)$filas as $f) {
                if ((int)$f['Non_unique'] === 0) {
                    $porIndice[$f['Key_name']][(int)$f['Seq_in_index']] = $f['Column_name'];
                }
            }
            foreach ($porIndice as $columnas) {
                ksort($columnas);
                if (array_values($columnas) === ['idToken', 'Modulo']) {
                    $unico = true;
                }
            }
        } catch (Throwable $e) {
            $unico = false;
        }
        $this->addCase($results, 'bd_indice_unico_token_modulo', $unico,
            'tokens_listado_permisos no tiene un indice unico (idToken, Modulo)');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                         BAJA: SWITCH = 1 BORRA                              */
    /******************************************************************************/
    /**
     * Un modulo asignado cuyo `switch_<Modulo>` llega con valor 1 (o ausente) se borra de
     * `tokens_listado_permisos`, acotado por `idToken` y `Modulo` (el resto de filas del token no
     * cambia). Sin ejecutar `Update()`: se comprueba la rama en el fuente.
     *
     * @return array Resultado por caso
     */
    public function switchBorraModuloTest(){

        $results = [];
        $update  = $this->cuerpoMetodo('Update');
        $this->addCase($results, 'fuente_update_leido', $update !== '', 'no se pudo leer Update()');

        $this->addCase($results, 'switch_ausente_es_inactivo',
            str_contains($update, "\$_POST['switch_'.\$modulo['modulo']] ?? 1"),
            'un switch ausente debe tratarse como inactivo (1)');
        $this->addCase($results, 'marcado_es_dos', str_contains($update, '=== 2'), 'el modulo se considera marcado solo con switch = 2');
        $this->addCase($results, 'borrar_solo_si_estaba_activo',
            preg_match('/!\$marcado && \$modulo\[\'activo\'\] === 1\).*?\$aBorrar\[\]/s', $update) === 1,
            'Update() solo debe borrar un modulo desmarcado que estaba asignado');
        $this->addCase($results, 'borrado_una_sola_vez', substr_count($update, 'Base_delete(') === 1,
            'Update() debe borrar en un unico punto ('.substr_count($update, 'Base_delete(').' llamadas)');
        $this->addCase($results, 'borrado_tabla', str_contains($update, "'table'       => 'tokens_listado_permisos'"),
            'el borrado debe dirigirse a tokens_listado_permisos');
        $this->addCase($results, 'borrado_acotado_token_y_modulo', str_contains($update, "'where'       => 'idToken,Modulo'"),
            'el borrado debe acotarse por idToken y Modulo (no debe afectar a otras filas)');
        $this->addCase($results, 'borrado_sin_archivos', str_contains($update, "'files'       => ''"),
            'el borrado de modulos no debe tocar archivos');
        $this->addCase($results, 'borrado_valida_ids',
            substr_count($update, "Response::error('Registro inválido', 400)") === 3
            && str_contains($update, "isValidDecrypted(\$TokenIDCod, 'text')") && str_contains($update, "isValidDecrypted(\$ModuloIDCod, 'text')"),
            'el borrado debe validar los IDs cifrados con isValidDecrypted y responder 400 (hay '.substr_count($update, "Response::error('Registro inválido', 400)").' respuestas 400 de registro)');
        $this->addCase($results, 'borrado_falla_revierte',
            preg_match('/\$respDel\[\'status\'\] === false\).*?Base_transactionRollback\(\).*?Response::error\(/s', $update) === 1,
            'ante la falla del borrado debe revertirse la transaccion antes de responder');
        $this->addCase($results, 'rollback_en_cada_error_de_escritura',
            substr_count($update, 'Base_transactionRollback()') === 4,
            'Update() debe revertir en cada salida de error tras abrir la transaccion ('.substr_count($update, 'Base_transactionRollback()').' puntos de reversion)');

        /*====================================*/
        // Transaccion: Begin antes del borrado, Commit despues
        /*====================================*/
        $begin  = $this->pos($update, 'Base_transactionBegin()');
        $delete = $this->pos($update, 'Base_delete(');
        $commit = $this->pos($update, 'Base_transactionCommit()');
        $this->addCase($results, 'transaccion_begin_antes_de_borrar', $begin !== false && $delete !== false && $begin < $delete,
            'Update() debe abrir la transaccion antes de borrar');
        $this->addCase($results, 'transaccion_commit_despues_de_borrar', $commit !== false && $delete !== false && $commit > $delete,
            'Update() debe confirmar la transaccion despues de borrar');
        $this->addCase($results, 'alta_tras_borrado_dentro_de_transaccion',
            $this->pos($update, 'Base_insertMultiple(') > $delete && $this->pos($update, 'Base_insertMultiple(') < $commit,
            'el alta en lote debe ejecutarse tras los borrados y antes del Commit');
        $this->addCase($results, 'respuesta_ok_al_final',
            str_contains($update, 'Response::success(true)') && $this->pos($update, 'Response::success(true)') > $commit,
            'Update() debe responder true solo despues del Commit');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                    MODULOS NO LISTADOS SE IGNORAN (S-4)                     */
    /******************************************************************************/
    /**
     * Un `switch_<x>` de un modulo que no esta en `getModulos()` (p. ej. `installer`, un nombre
     * inexistente o un modulo ya no exponible) ni crea filas ni borra las existentes: `Update()`
     * solo recorre los modulos de `getModulos()`. Sin ejecutar `Update()`: se verifica en el fuente.
     *
     * @return array Resultado por caso
     */
    public function moduloNoListadoSeIgnoraTest(){

        $results = [];
        $update  = $this->cuerpoMetodo('Update');
        $this->addCase($results, 'fuente_update_leido', $update !== '', 'no se pudo leer Update()');

        $this->addCase($results, 'update_usa_getmodulos', str_contains($update, '$this->getModulos($idToken)'),
            'Update() debe obtener los modulos de getModulos()');
        $this->addCase($results, 'update_recorre_solo_getmodulos', str_contains($update, "foreach (\$arrModulos['data'] as \$modulo)"),
            'Update() debe recorrer solo los modulos devueltos por getModulos()');
        $this->addCase($results, 'update_no_recorre_post',
            !str_contains($update, 'foreach ($_POST') && !str_contains($update, 'array_keys($_POST') && !str_contains($update, 'preg_grep'),
            'Update() no debe iterar $_POST: un switch_<modulo> no listado no puede crear ni borrar filas');
        $this->addCase($results, 'update_post_solo_por_clave_listada',
            substr_count($update, '$_POST[') === 2 && str_contains($update, "\$_POST['idToken']") && str_contains($update, "\$_POST['switch_'.\$modulo['modulo']]"),
            'los unicos accesos a $_POST deben ser idToken y switch_<modulo de la lista> ('.substr_count($update, '$_POST[').' accesos)');
        $this->addCase($results, 'update_sin_borrado_por_post', !str_contains($update, "'Modulo'  => \$_POST") && !str_contains($update, "\$_POST['Modulo']"),
            'ningun nombre de modulo debe tomarse directamente del POST');

        /*====================================*/
        // Ejecucion real (lectura): la lista no trae installer ni nombres inexistentes
        /*====================================*/
        try {
            $resp = $this->invocarPrivado($this->instanciaControlador(), 'getModulos', [0]);
            if (($resp['status'] ?? false) === true) {
                $nombres = array_column($resp['data'], 'modulo');
                $this->addCase($results, 'lista_sin_installer', !in_array('installer', $nombres, true), 'installer no debe estar en getModulos()');
                $this->addCase($results, 'lista_sin_inexistente', !in_array('noExiste', $nombres, true), 'un modulo inexistente no debe estar en getModulos()');
            } else {
                $this->addCase($results, 'lista_ejecuta', false, 'getModulos(0) fallo ('.json_encode($resp['error'] ?? null).')');
            }
        } catch (Throwable $e) {
            $this->addCase($results, 'lista_ejecuta', false, 'getModulos(0) lanzo '.$e->getMessage());
        }

        return $results;

    }

    /******************************************************************************/
    /*                  LA MATRIZ OCULTA LOS MODULOS EXCLUIDOS                     */
    /******************************************************************************/
    /**
     * `getModulos()` (por reflexion, lectura) no contiene `installer` ni `tokens` y solo contiene
     * modulos de `InternalFunctionsTokensModulos::listExposable()`, con las claves esperadas.
     *
     * @return array Resultado por caso
     */
    public function matrizOcultaModulosExcluidosTest(){

        $results = [];

        $existe = class_exists('InternalFunctionsTokensModulos');
        $this->addCase($results, 'helper_existe', $existe, 'no se carga InternalFunctionsTokensModulos (autoload de functions/)');
        if (!$existe) {
            return $results;
        }

        try {
            $exponibles = array_keys(InternalFunctionsTokensModulos::listExposable());
            $resp       = $this->invocarPrivado($this->instanciaControlador(), 'getModulos', [0]);
            $ok         = ($resp['status'] ?? false) === true && is_array($resp['data'] ?? null);
            $this->addCase($results, 'getmodulos_ejecuta', $ok, 'getModulos(0) no devolvio datos ('.json_encode($resp['error'] ?? null).')');
            if ($ok) {
                $nombres = array_column($resp['data'], 'modulo');
                $this->addCase($results, 'sin_installer', !in_array('installer', $nombres, true), 'installer no debe aparecer en la matriz');
                $this->addCase($results, 'sin_tokens', !in_array('tokens', $nombres, true), 'tokens no debe aparecer en la matriz');
                $this->addCase($results, 'solo_exponibles', array_diff($nombres, $exponibles) === [],
                    'la matriz contiene modulos no exponibles: '.implode(', ', array_diff($nombres, $exponibles)));
                $this->addCase($results, 'sin_duplicados', count($nombres) === count(array_unique($nombres)), 'la matriz repite modulos');
            }
        } catch (Throwable $e) {
            $this->addCase($results, 'getmodulos_ejecuta', false, 'getModulos(0) lanzo '.$e->getMessage());
        }

        return $results;

    }

    /******************************************************************************/
    /*                            TOKEN INEXISTENTE                                */
    /******************************************************************************/
    /**
     * `idToken` cifrado de un token inexistente, vacio o que no descifra se rechaza con 400 en
     * `Update()` y en `UpdateList()`, antes de abrir transaccion o leer la matriz. Se ejecutan
     * de verdad el descifrado y `tokenExists()` (lecturas); los endpoints no se invocan.
     *
     * @return array Resultado por caso
     */
    public function tokenInexistenteTest(){

        $results = [];
        $update  = $this->cuerpoMetodo('Update');
        $list    = $this->cuerpoMetodo('UpdateList');
        $this->addCase($results, 'fuente_update_leido', $update !== '', 'no se pudo leer Update()');
        $this->addCase($results, 'fuente_updatelist_leido', $list !== '', 'no se pudo leer UpdateList()');

        /*====================================*/
        // Fuente: descifrar -> validar -> existe -> 400, y todo antes de leer/escribir
        /*====================================*/
        $endpoints = ['update' => $update, 'updatelist' => $list];
        foreach ($endpoints as $nombre => $cuerpo) {
            $this->addCase($results, $nombre.'_descifra_id', str_contains($cuerpo, "encryptDecrypt('decrypt'"),
                $nombre.': el idToken debe descifrarse');
            $this->addCase($results, $nombre.'_valida_descifrado', str_contains($cuerpo, "isValidDecrypted(\$TokenID, 'id')"),
                $nombre.': el id descifrado debe validarse con isValidDecrypted(..., \'id\')');
            $this->addCase($results, $nombre.'_verifica_existencia', str_contains($cuerpo, '$this->tokenExists((int)$TokenID[\'data\'])')
                || str_contains($cuerpo, '$this->tokenExists($idToken)'),
                $nombre.': debe verificar que el token exista');
            $this->addCase($results, $nombre.'_responde_400', substr_count($cuerpo, "Response::error('Registro inválido', 400)") >= 2,
                $nombre.': id invalido y token inexistente deben responder 400 ('.substr_count($cuerpo, "Response::error('Registro inválido', 400)").' respuestas)');
        }
        $this->addCase($results, 'update_valida_antes_de_transaccion',
            $this->pos($update, 'tokenExists(') !== false && $this->pos($update, 'tokenExists(') < $this->pos($update, 'Base_transactionBegin()'),
            'Update() debe comprobar el token antes de abrir la transaccion');
        $this->addCase($results, 'update_valida_antes_de_leer_matriz',
            $this->pos($update, 'tokenExists(') < $this->pos($update, '$this->getModulos('),
            'Update() debe comprobar el token antes de leer la matriz de modulos');
        $this->addCase($results, 'updatelist_valida_antes_de_leer_matriz',
            $this->pos($list, 'tokenExists(') !== false && $this->pos($list, 'tokenExists(') < $this->pos($list, '$this->getModulos('),
            'UpdateList() debe comprobar el token antes de leer la matriz de modulos');
        $this->addCase($results, 'update_idtoken_ausente_se_rechaza', str_contains($update, "\$_POST['idToken'] ?? ''"),
            'un POST sin idToken debe llegar como cadena vacia y rechazarse');

        /*====================================*/
        // tokenExists: solo tokens manuales (Origen = 1); un acceso OAuth hijo no tiene matriz
        /*====================================*/
        $existe = $this->cuerpoMetodo('tokenExists');
        $this->addCase($results, 'tokenexists_fuente_leido', $existe !== '', 'no se pudo leer tokenExists()');
        $this->addCase($results, 'tokenexists_filtra_origen',
            str_contains($existe, "'where'   => 'idToken = ? AND Origen = ?'") && str_contains($existe, '[$idToken, 1]'),
            'tokenExists() debe filtrar por idToken y Origen = 1 con parametros preparados');
        $this->addCase($results, 'tokenexists_devuelve_bool',
            str_contains($existe, "=== true && !empty(\$rowData['data'])"),
            'tokenExists() debe existir solo si la consulta fue correcta y devolvio una fila');

        /*====================================*/
        // Ejecucion real: ids que no descifran / vacios / no numericos
        /*====================================*/
        try {
            $cod = new FunctionsSecurityCodification();
            $ctrl = $this->instanciaControlador();
            $esValido = function ($valor) use ($cod, $ctrl) {
                return $this->invocarPrivado_isValid($ctrl, $cod->encryptDecrypt('decrypt', $valor));
            };

            $invalidos = ['vacio' => '', 'basura' => 'esto-no-descifra', 'numero_plano' => '123', 'base64_corto' => 'QUJD'];
            foreach ($invalidos as $nombre => $valor) {
                $this->addCase($results, 'id_invalido_rechazado_'.$nombre, $esValido($valor) === false,
                    'el id "'.$valor.'" debia rechazarse y se acepto');
            }

            // Id cifrado de un token que no existe: descifra bien pero tokenExists() es false
            $inexistente = 2000000000;
            $cifrado     = $cod->encryptDecrypt('encrypt', $inexistente);
            $this->addCase($results, 'id_inexistente_descifra', $esValido($cifrado['data'] ?? '') === true,
                'el id cifrado de un numero valido debe descifrar (la falla debe ser por inexistencia)');
            $this->addCase($results, 'id_inexistente_no_existe', $this->invocarPrivado($ctrl, 'tokenExists', [$inexistente]) === false,
                'tokenExists('.$inexistente.') devolvio true');
            $this->addCase($results, 'id_cero_no_existe', $this->invocarPrivado($ctrl, 'tokenExists', [0]) === false,
                'tokenExists(0) devolvio true');

            // Positivo: si hay un token real, tokenExists lo reconoce (la prueba puede fallar por ambos lados)
            $idReal = $this->idTokenExistente();
            if ($idReal > 0) {
                $this->addCase($results, 'id_real_existe', $this->invocarPrivado($ctrl, 'tokenExists', [$idReal]) === true,
                    'tokenExists('.$idReal.') devolvio false para un token manual existente');
            }
            // Un acceso OAuth (Origen = 2) no tiene matriz de permisos: tokenExists debe rechazarlo
            $idHijo = $this->idTokenExistente(2);
            if ($idHijo > 0) {
                $this->addCase($results, 'id_hijo_oauth_no_existe', $this->invocarPrivado($ctrl, 'tokenExists', [$idHijo]) === false,
                    'tokenExists('.$idHijo.') devolvio true para un acceso OAuth (Origen = 2)');
            }
        } catch (Throwable $e) {
            $this->addCase($results, 'ejecucion_real_ids', false, 'la ejecucion real de la validacion de ids lanzo '.$e->getMessage());
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                       VISTA -Resumen-Permisos-List                          */
    /******************************************************************************/
    /**
     * Renderiza `-Resumen-Permisos-List` con datos sinteticos: un interruptor por modulo (`switch_<modulo>`
     * oculto=1 y casilla=2, marcada si `activo`), sin niveles ni categorias, el id del token como campo
     * oculto, el envio a `/permisos/update` y el boton Guardar solo con nivel >= 2.
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function vistaMatrizPermisosTest($f3){

        $results = [];
        $BASE    = (string)$f3->get('BASE');
        $ruta    = $this->rutaModulo('../views/'.self::VISTA.'.php');
        $this->addCase($results, 'vista_existe', is_file($ruta), 'falta la vista '.self::VISTA.'.php');

        foreach (self::NIVELES as $nivel) {
            $data = [
                'UserAccess'     => ['LevelAccess' => $nivel, 'RouteAccess' => self::RUTA_WEB],
                'Fnc_CommonData' => new FunctionsCommonData(),
                'Fnc_FormInputs' => new UIFormInputs(),
                'rowData'        => ['idToken' => 'TOKENCIFRADO'],
                'arrModulos'     => [
                    ['modulo' => 'mod_alfa', 'nombre' => 'Modulo Alfa', 'descripcion' => 'Descripcion Alfa', 'activo' => 1],
                    ['modulo' => 'mod_beta', 'nombre' => 'Modulo Beta', 'descripcion' => 'Descripcion Beta', 'activo' => 0],
                ],
            ];
            $html = $this->renderizarVista($ruta, $data, $BASE, $f3);
            $this->addCase($results, 'matriz_render_n'.$nivel, $html !== '', 'no se pudo renderizar la vista con nivel '.$nivel);
            $this->addCase($results, 'matriz_guardar_n'.$nivel, str_contains($html, 'Guardar Cambios') === ($nivel >= 2),
                'el boton Guardar Cambios debe '.($nivel >= 2 ? 'mostrarse' : 'ocultarse').' con nivel '.$nivel);
            if ($nivel !== 2) {
                continue;
            }
            $this->addCase($results, 'matriz_switch_por_modulo', substr_count($html, 'name="switch_mod_alfa"') === 2 && substr_count($html, 'name="switch_mod_beta"') === 2,
                'cada modulo debe tener su campo oculto switch_<modulo>=1 y su casilla switch_<modulo>=2');
            $this->addCase($results, 'matriz_activo_marcado',
                preg_match('/type="checkbox" value="2" name="switch_mod_alfa"[^>]*\schecked/', $html) === 1
                && preg_match('/type="checkbox" value="2" name="switch_mod_beta"[^>]*\schecked/', $html) === 0,
                'solo el modulo activo (Alfa) debe venir marcado');
            $this->addCase($results, 'matriz_sin_niveles', !str_contains($html, 'level_') && !str_contains($html, 'Nivel Acceso') && !str_contains($html, 'dlk-radio'),
                'la vista no debe ofrecer niveles');
            $this->addCase($results, 'matriz_sin_categorias', !str_contains($html, 'table-secondary'), 'la vista no debe agrupar por categoria');
            $this->addCase($results, 'matriz_id_token_oculto', str_contains($html, 'name="idToken"') && str_contains($html, 'TOKENCIFRADO'),
                'la vista debe enviar el idToken (cifrado) como campo oculto');
            $this->addCase($results, 'matriz_endpoint_update', str_contains($html, '/'.self::RUTA_WEB.'/permisos/update'),
                'la vista debe enviar el formulario a /'.self::RUTA_WEB.'/permisos/update');
            $this->addCase($results, 'matriz_nombre_y_descripcion', str_contains($html, 'Modulo Alfa') && str_contains($html, 'Descripcion Alfa'),
                'la vista debe mostrar el nombre del modulo y su descripcion');
        }

        /*====================================*/
        // Sin modulos: mensaje vacio
        /*====================================*/
        $data = [
            'UserAccess'     => ['LevelAccess' => 2, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_CommonData' => new FunctionsCommonData(),
            'Fnc_FormInputs' => new UIFormInputs(),
            'rowData'        => ['idToken' => 'X'],
            'arrModulos'     => [],
        ];
        $html = $this->renderizarVista($ruta, $data, $BASE, $f3);
        $this->addCase($results, 'matriz_vacia', str_contains($html, 'No hay módulos disponibles'), 'una lista vacia debe mostrar "No hay módulos disponibles"');

        /*====================================*/
        // Escape: un nombre con HTML se muestra escapado
        /*====================================*/
        $data['arrModulos'] = [['modulo' => 'mod_x', 'nombre' => '<b>x</b>', 'descripcion' => '"d"', 'activo' => 0]];
        $html = $this->renderizarVista($ruta, $data, $BASE, $f3);
        $this->addCase($results, 'matriz_escapa_html', $html !== '' && !str_contains($html, '<b>x</b>') && str_contains($html, '&lt;b&gt;x&lt;/b&gt;'),
            'los nombres y descripciones deben escaparse con htmlspecialchars');

        return $results;

    }

    /*******************************************************************/
    // Renderiza una vista y devuelve el HTML (CWD en admin/public; restaura el CWD)
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
    // Aplica isValidDecrypted (protegido en ControllerBase) a un resultado de descifrado
    /*******************************************************************/
    private function invocarPrivado_isValid($instancia, array $dato): bool{
        $r = new ReflectionMethod('ControllerBase', 'isValidDecrypted');
        $r->setAccessible(true);
        return (bool)$r->invoke($instancia, $dato, 'id');
    }

    /******************************************************************************/
    /*                    BORRADO EN CASCADA AL BORRAR EL TOKEN                    */
    /******************************************************************************/
    /**
     * Borrar un token (`tokensListado::Delete`) borra sus filas de `tokens_listado_permisos`
     * en la misma transaccion, acotadas por `idToken`. Sin ejecutar `Delete()`: se comprueba
     * el fuente y, con lecturas reales, que no existen filas huerfanas de tokens ya borrados.
     *
     * @return array Resultado por caso
     */
    public function borradoCascadaTest(){

        $results = [];
        $delete  = $this->cuerpoMetodo('Delete', self::CONTROLADOR_PADRE);
        $this->addCase($results, 'fuente_delete_leido', $delete !== '', 'no se pudo leer '.self::CONTROLADOR_PADRE.'::Delete()');

        /*====================================*/
        // Fuente: la tabla de permisos se limpia por idToken en la misma transaccion
        /*====================================*/
        $this->addCase($results, 'cascada_verbo_delete', str_contains($delete, "REQUEST_METHOD'] !== 'DELETE'") && str_contains($delete, '405'),
            'Delete() debe exigir el verbo DELETE con 405');
        $this->addCase($results, 'cascada_tabla_listada', str_contains($delete, "'table' => 'tokens_listado_permisos'"),
            'Delete() debe listar tokens_listado_permisos entre las tablas relacionadas a borrar');
        $this->addCase($results, 'cascada_where_idtoken',
            str_contains($delete, "'where' => 'idToken'") && str_contains($delete, "'Post' => \$dataDelete"),
            'el borrado relacionado debe acotarse por idToken con el mismo id recibido');
        $this->addCase($results, 'cascada_sin_archivos', str_contains($delete, "['files' => '', 'table' => 'tokens_listado_permisos']"),
            'el borrado de permisos no debe tocar archivos');
        $this->addCase($results, 'cascada_auditoria_no_se_borra', !str_contains($delete, 'mcp_audit') && !str_contains($delete, 'tokens_listado_audit'),
            'Delete() no debe borrar el registro de auditoria MCP');

        $begin   = $this->pos($delete, 'Base_transactionBegin()');
        $primero = $this->pos($delete, "'table'       => 'tokens_listado'");
        $cascada = $this->pos($delete, "tokens_listado_permisos");
        $commit  = $this->pos($delete, 'Base_transactionCommit()');
        $this->addCase($results, 'cascada_misma_transaccion',
            $begin !== false && $cascada !== false && $commit !== false && $begin < $cascada && $cascada < $commit,
            'el borrado en cascada debe ocurrir entre Begin y Commit de la misma transaccion');
        $this->addCase($results, 'cascada_tras_borrar_token', $primero !== false && $cascada !== false && $primero < $cascada,
            'los permisos se borran despues de la fila del token dentro de la transaccion');
        $this->addCase($results, 'cascada_falla_revierte',
            preg_match('/\$respDelRel\[\'status\'\] === false\).*?Base_transactionRollback\(\).*?Response::error\(/s', $delete) === 1,
            'ante la falla del borrado en cascada debe revertirse la transaccion antes de responder');

        /*====================================*/
        // BD (lectura): la tabla existe, y no quedan permisos de tokens inexistentes
        /*====================================*/
        $queryHuerfanos = ['query' => [
            'data' => 'COUNT(*) AS n', 'table' => self::TABLA, 'join' => 'LEFT JOIN tokens_listado ON tokens_listado.idToken = tokens_listado_permisos.idToken',
            'where' => 'tokens_listado.idToken IS NULL', 'params' => [], 'group' => '', 'having' => '', 'order' => '', 'limit' => 1,
        ]];
        $resp = $this->Base_GetList($queryHuerfanos);
        $consultaOk = ($resp['status'] ?? false) === true && isset($resp['data'][0]['n']);
        $this->addCase($results, 'bd_consulta_huerfanos', $consultaOk,
            'no se pudo consultar tokens_listado_permisos ('.json_encode($resp['error'] ?? null).')');
        if ($consultaOk) {
            $this->addCase($results, 'bd_sin_permisos_huerfanos', (int)$resp['data'][0]['n'] === 0,
                'hay '.(int)$resp['data'][0]['n'].' filas en tokens_listado_permisos de tokens que ya no existen');
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

}
