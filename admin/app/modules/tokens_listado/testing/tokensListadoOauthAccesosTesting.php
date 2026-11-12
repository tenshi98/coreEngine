<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class tokensListadoOauthAccesosTesting
 *
 * Pruebas del modulo `tokens` - controlador `tokensListadoOauthAccesos` (subcontrolador de la pestana
 * "Accesos OAuth" del Resumen de un token: `UpdateList` lista los accesos emitidos con el token y `Delete`
 * revoca un acceso con su familia de refresh). El acceso y los permisos son los de `tokensListado`
 * (`controllerName = 'tokensListado'`).
 *
 * Convenciones del sistema de testeos: toda prueba es un metodo publico que termina en "Test"; devuelve
 * un arreglo [caso => 'OK'|'FALLO: razon']; si declara parametros el runner le inyecta F3.
 *
 * Alcance: pruebas NO destructivas. `Delete()` y `UpdateList()` terminan en `Response::*()` (`exit`) y
 * `Delete()` borra en BD, por eso NO se invocan: se verifican por reflexion, lectura del fuente y render
 * de la vista con datos sinteticos.
 *
 * @see projects/mcp-oauth/specs/technical.md §21.1
 * @see skills/testing/SKILL.md
 */
class tokensListadoOauthAccesosTesting extends ControllerBase {

    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR          = 'tokensListadoOauthAccesos';
    private const CONTROLADOR_PERMISOS = 'tokensListado';
    private const INSTALADOR           = 'tokensInstaller';
    private const RUTA_WEB             = 'administracion/tokens/listado';
    private const RUTA_UPDATELIST      = 'administracion/tokens/listado/oauthAccesos/updateList/@id';
    private const RUTA_DELETE          = 'administracion/tokens/listado/oauthAccesos';
    private const VISTA                = 'tokensListado-Resumen-OauthAccesos-UpdateList';
    private const NIVELES              = [1, 2, 3, 4];

    public function __construct(){
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
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
    // Posicion de una subcadena dentro del cuerpo (false si no esta)
    /*******************************************************************/
    private function pos(string $cuerpo, string $aguja){
        return strpos($cuerpo, $aguja);
    }

    /*******************************************************************/
    // Rutas del permiso 1 del installer: [idMetodo.' '.RutaWeb => [handler, nivel, controller]]
    /*******************************************************************/
    private function rutasInstalador(): array{
        $rutas = [];
        if (!class_exists(self::INSTALADOR)) {
            return $rutas;
        }
        try {
            $instalador = (new ReflectionClass(self::INSTALADOR))->newInstanceWithoutConstructor();
            foreach ($instalador->getRouteDefinitions(1) as $ruta) {
                $rutas[$ruta['idMetodo'].' '.$ruta['RutaWeb']] = [$ruta['RutaController'], (int)$ruta['idLevelLimit'], $ruta['Controller']];
            }
        } catch (Throwable $e) {
            return [];
        }
        return $rutas;
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
    // Datos sinteticos de la vista de la pestana: un acceso por estado
    // (vigente, expirado con refresh vivo, expirado sin refresh)
    /*******************************************************************/
    private function datosSinteticos(int $nivel, array $accesos = null): array{
        return [
            'UserAccess'       => ['LevelAccess' => $nivel, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_Codification' => new FunctionsSecurityCodification(),
            'TokenID'          => 'TOKENCIFRADO',
            'arrAccesos'       => $accesos ?? [
                ['idToken' => 21, 'Cliente' => 'Cliente <b>Alfa</b>', 'FechaCreacion' => '2026-01-02 10:00:00', 'FechaExpiracion' => '2099-01-02 11:00:00', 'Expirado' => 0, 'RefreshExpira' => null],
                ['idToken' => 22, 'Cliente' => 'Cliente Beta', 'FechaCreacion' => '2026-01-01 10:00:00', 'FechaExpiracion' => '2026-01-01 11:00:00', 'Expirado' => 1, 'RefreshExpira' => '2099-02-02 10:00:00'],
                ['idToken' => 23, 'Cliente' => 'Cliente Gamma', 'FechaCreacion' => '2025-12-01 10:00:00', 'FechaExpiracion' => '2025-12-01 11:00:00', 'Expirado' => 1, 'RefreshExpira' => null],
            ],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica los archivos (controlador, vista del fragmento, `.htaccess`), la clase, la herencia sin
     * sobrescribir `beforeroute` y las firmas de `UpdateList` (2 parametros) y `Delete` (0).
     *
     * @return array Resultado por caso
     */
    public function estructuraModuloTest(){

        $results = [];

        foreach (['controlador' => '../controller/'.self::CONTROLADOR.'.php', 'vista' => '../views/'.self::VISTA.'.php',
                  'instalador' => '../installer/'.self::INSTALADOR.'.php'] as $nombre => $sub) {
            $this->addCase($results, 'archivo_'.$nombre, is_file($this->rutaModulo($sub)), 'no se encuentra '.$sub);
        }
        $this->addCase($results, 'testing_htaccess_deny', str_contains($this->codigoFuente($this->rutaModulo('.htaccess')), 'deny from all'),
            'testing/.htaccess debe contener "deny from all"');

        $this->addCase($results, 'clase_existe', class_exists(self::CONTROLADOR), 'no existe la clase '.self::CONTROLADOR);
        if (class_exists(self::CONTROLADOR)) {
            $this->addCase($results, 'clase_extiende_ControllerBase', is_subclass_of(self::CONTROLADOR, 'ControllerBase'),
                self::CONTROLADOR.' debe extender ControllerBase');
            $this->addCase($results, 'no_sobrescribe_beforeroute',
                (new ReflectionMethod(self::CONTROLADOR, 'beforeroute'))->getDeclaringClass()->getName() === 'ControllerBase',
                self::CONTROLADOR.' sobrescribe beforeroute y puede saltarse la guardia CSRF');
        }

        foreach (['UpdateList' => 2, 'Delete' => 0] as $metodo => $parametros) {
            $existe = method_exists(self::CONTROLADOR, $metodo);
            $this->addCase($results, 'metodo_'.$metodo, $existe, 'no existe '.self::CONTROLADOR.'::'.$metodo.'()');
            if ($existe) {
                $r = new ReflectionMethod(self::CONTROLADOR, $metodo);
                $this->addCase($results, 'metodo_'.$metodo.'_publico', $r->isPublic(), $metodo.'() no es publico');
                $this->addCase($results, 'metodo_'.$metodo.'_parametros', $r->getNumberOfParameters() === $parametros,
                    $metodo.'() declara '.$r->getNumberOfParameters().' parametros (esperado '.$parametros.')');
            }
        }
        $this->addCase($results, 'sin_metodos_de_escritura_extra',
            !method_exists(self::CONTROLADOR, 'Insert') && !method_exists(self::CONTROLADOR, 'Update'),
            self::CONTROLADOR.' no debe crear ni editar accesos: solo UpdateList y Delete');

        return $results;

    }

    /******************************************************************************/
    /*                       CONTROLADOR: RUTAS E INSTALADOR                      */
    /******************************************************************************/
    /**
     * `controllerName = 'tokensListado'` (permisos compartidos) y rutas del installer: GET
     * `oauthAccesos/updateList/@id` y DELETE `oauthAccesos`, ambas con nivel 2 y handler del
     * subcontrolador; el controlador figura en `RutaController()`.
     *
     * @return array Resultado por caso
     */
    public function controladorRutasTest(){

        $results = [];
        $fuente  = $this->fuenteControlador();
        $this->addCase($results, 'controller_name_permisos', str_contains($fuente, "\$this->controllerName = '".self::CONTROLADOR_PERMISOS."';"),
            'controllerName debe ser '.self::CONTROLADOR_PERMISOS.' (el acceso es el de tokens_listado)');
        $this->addCase($results, 'sin_beforeroute_propio', !str_contains($fuente, 'beforeroute') && !str_contains($fuente, 'CsrfToken'),
            self::CONTROLADOR.' no debe tocar ni desactivar la guardia CSRF');

        $rutas = $this->rutasInstalador();
        $this->addCase($results, 'instalador_rutas_leidas', count($rutas) > 0, 'no se pudieron leer las rutas de '.self::INSTALADOR.'::getRouteDefinitions(1)');
        // idMetodo: 1 GET, 2 POST, 3 DELETE
        $matriz = [
            'updatelist' => ['1 '.self::RUTA_UPDATELIST, self::CONTROLADOR.'->UpdateList'],
            'delete'     => ['3 '.self::RUTA_DELETE,     self::CONTROLADOR.'->Delete'],
        ];
        foreach ($matriz as $nombre => [$clave, $handler]) {
            $existe = isset($rutas[$clave]);
            $this->addCase($results, 'ruta_'.$nombre, $existe, 'el instalador no publica la ruta '.$clave);
            if (!$existe) {
                continue;
            }
            $this->addCase($results, 'ruta_'.$nombre.'_handler', $rutas[$clave][0] === $handler, 'la ruta '.$clave.' apunta a '.$rutas[$clave][0].' en vez de '.$handler);
            $this->addCase($results, 'ruta_'.$nombre.'_nivel_2', $rutas[$clave][1] === 2, 'la ruta '.$clave.' exige nivel '.$rutas[$clave][1].' en vez de 2');
            $this->addCase($results, 'ruta_'.$nombre.'_controller', $rutas[$clave][2] === self::CONTROLADOR, 'la ruta '.$clave.' queda asignada a '.$rutas[$clave][2]);
            $partes = explode('->', $rutas[$clave][0]);
            $this->addCase($results, 'ruta_'.$nombre.'_metodo_existe', count($partes) === 2 && method_exists($partes[0], $partes[1]), 'el handler '.$rutas[$clave][0].' no existe');
        }
        $propias = array_filter($rutas, static function ($r) { return $r[2] === 'tokensListadoOauthAccesos'; });
        $this->addCase($results, 'rutas_propias_completas', count($propias) === 2, 'se esperaban 2 rutas de '.self::CONTROLADOR.' y hay '.count($propias));
        $this->addCase($results, 'rutas_propias_nivel_minimo_2', $propias !== [] && min(array_column($propias, 1)) >= 2,
            'una ruta de esta pestana exige menos de nivel 2');
        $this->addCase($results, 'instalador_desinstala_controlador',
            str_contains($this->codigoFuente($this->rutaModulo('../installer/'.self::INSTALADOR.'.php')), "\$RutaController .= ',".self::CONTROLADOR."';"),
            'RutaController() no lista '.self::CONTROLADOR.' y dejaria rutas huerfanas al desinstalar');

        return $results;

    }

    /******************************************************************************/
    /*                                  PERMISOS                                  */
    /******************************************************************************/
    /**
     * Verifica la sesion, el nivel de acceso heredado de `tokensListado` y las rutas del subcontrolador
     * en el router de F3 (solo se evaluan si el usuario las tiene registradas).
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function permisosSesionTest($f3){

        $results = [];

        $UserData = $f3->get('SESSION.DataInfo');
        $this->addCase($results, 'sesion_usuario_presente', is_array($UserData) && !empty($UserData), 'no hay datos de usuario en la sesion');

        try {
            $arrLevel = $this->getArrLevel($f3, self::CONTROLADOR_PERMISOS);
            $this->addCase($results, 'get_arr_level_arreglo', is_array($arrLevel), 'getArrLevel() no devolvio un arreglo');
        } catch (Throwable $e) {
            $this->addCase($results, 'get_arr_level_arreglo', false, 'no se pudo leer la sesion ('.$e->getMessage().')');
            $arrLevel = [];
        }
        $nivel = is_array($arrLevel) ? ($arrLevel['LevelAccess'] ?? null) : null;
        $this->addCase($results, 'nivel_en_rango', $nivel === null || in_array((int)$nivel, self::NIVELES, true), 'LevelAccess quedo fuera del catalogo 1..4');
        $this->addCase($results, 'ruta_de_acceso_coincide', $nivel === null || ($arrLevel['RouteAccess'] ?? '') === self::RUTA_WEB, 'RouteAccess no coincide con '.self::RUTA_WEB);

        $arrRoutes = $f3->get('ROUTES');
        $this->addCase($results, 'router_disponible', is_array($arrRoutes), 'F3 no expuso la variable ROUTES');
        $esquema = [
            'ruta_updatelist_router' => ['/'.self::RUTA_UPDATELIST, 'GET',    self::CONTROLADOR.'->UpdateList'],
            'ruta_delete_router'     => ['/'.self::RUTA_DELETE,     'DELETE', self::CONTROLADOR.'->Delete'],
        ];
        foreach ($esquema as $caso => [$ruta, $verbo, $handlerEsperado]) {
            if (!is_array($arrRoutes) || !isset($arrRoutes[$ruta])) {
                continue;
            }
            $handler = $arrRoutes[$ruta][0][$verbo][0] ?? '';
            $this->addCase($results, $caso, $handler === $handlerEsperado, 'la ruta '.$ruta.' apunta a '.var_export($handler, true).' en vez de '.$handlerEsperado);
        }

        return $results;

    }

    /******************************************************************************/
    /*                              LISTADO DE ACCESOS                             */
    /******************************************************************************/
    /**
     * `UpdateList` valida el id cifrado y que el token sea manual (`Origen = 1`) antes de leer; la consulta
     * `getAccesos` filtra por `idTokenPadre` y `Origen = 2` con parametros preparados y NO selecciona el
     * valor del token (`Token`) de los accesos. Sin ejecutar el endpoint.
     *
     * @return array Resultado por caso
     */
    public function listadoAccesosTest(){

        $results = [];
        $list = $this->cuerpoMetodo('UpdateList');
        $cons = $this->cuerpoMetodo('getAccesos');
        $this->addCase($results, 'fuente_updatelist_leido', $list !== '', 'no se pudo leer UpdateList()');
        $this->addCase($results, 'fuente_getaccesos_leido', $cons !== '', 'no se pudo leer getAccesos()');

        $this->addCase($results, 'updatelist_id_obligatorio', str_contains($list, "!isset(\$params['id'])") && str_contains($list, "Response::error('Registro ID inexistente', 400)"),
            'UpdateList() debe responder 400 cuando no llega el id');
        $this->addCase($results, 'updatelist_valida_descifrado', str_contains($list, "encryptDecrypt('decrypt', \$params['id'])") && str_contains($list, "isValidDecrypted(\$TokenID, 'id')"),
            'UpdateList() debe descifrar y validar el id');
        $this->addCase($results, 'updatelist_guarda_origen_1',
            str_contains($list, "'where'   => 'idToken = ? AND Origen = ?'") && str_contains($list, '[(int) $TokenID[\'data\'], 1]'),
            'UpdateList() debe comprobar con parametros que el token existe y es manual (Origen = 1)');
        $this->addCase($results, 'updatelist_fila_vacia_400', str_contains($list, "empty(\$rowData['data'])") && str_contains($list, "Response::error('Registro inválido', 400)"),
            'UpdateList() debe responder 400 si el token no existe o no es manual');
        $this->addCase($results, 'updatelist_guarda_antes_de_leer',
            $this->pos($list, "'where'   => 'idToken = ? AND Origen = ?'") !== false && $this->pos($list, "'where'   => 'idToken = ? AND Origen = ?'") < $this->pos($list, '$this->getAccesos('),
            'la guarda del token debe ejecutarse antes de leer los accesos');
        $this->addCase($results, 'updatelist_vista', str_contains($list, "showVista(2, \$this->returnRutaVista(__DIR__, 'app').'/'.\$this->controllerName.'-Resumen-OauthAccesos-UpdateList.php')"),
            'UpdateList() debe cargar '.self::VISTA.'.php con showVista(2)');
        $this->addCase($results, 'updatelist_errores', str_contains($list, 'showError(2'), 'UpdateList() debe manejar errores con showError(2)');
        $this->addCase($results, 'updatelist_nivel_de_tokens', str_contains($list, 'getArrLevel($f3, $this->controllerName)'),
            'UpdateList() debe resolver el nivel con controllerName (tokensListado)');

        $this->addCase($results, 'getaccesos_filtra_padre_origen',
            str_contains($cons, "'where'   => 'tokens_listado.idTokenPadre = ? AND tokens_listado.Origen = ?'") && str_contains($cons, '[$idToken, 2]'),
            'getAccesos() debe filtrar idTokenPadre = ? y Origen = 2 con parametros preparados');
        // La consulta no selecciona el valor del token (columna Token), ni el secreto del cliente
        $this->addCase($results, 'getaccesos_no_selecciona_token',
            !preg_match('/tokens_listado\.Token\b/', $cons) && !preg_match('/(?<![A-Za-z_.])Token\b\s*[,\n]/', explode("'table'", $cons)[0]) && !str_contains($cons, 'SecretHash'),
            'getAccesos() no debe seleccionar el valor del token (Token) ni el secreto del cliente');
        $this->addCase($results, 'getaccesos_columnas',
            str_contains($cons, 'tokens_listado.idToken') && str_contains($cons, 'tokens_listado.FechaExpiracion') && str_contains($cons, 'AS Expirado') && str_contains($cons, 'AS RefreshExpira'),
            'getAccesos() debe devolver idToken, FechaExpiracion, Expirado y RefreshExpira');
        $this->addCase($results, 'getaccesos_refresh_vivo',
            str_contains($cons, 'r.Estado = 1 AND r.FechaExpiracion >= NOW()'),
            'RefreshExpira debe considerar solo refresh con Estado = 1 y no expirado');
        $this->addCase($results, 'getaccesos_sin_concat_sql', !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $this->fuenteControlador()),
            'el controlador concatena entrada del usuario');

        return $results;

    }

    /******************************************************************************/
    /*                          REVOCAR UN ACCESO (Delete)                         */
    /******************************************************************************/
    /**
     * `Delete` exige DELETE con 405 antes de abrir la transaccion, descifra y valida `idToken`, comprueba con
     * la guarda `Origen = ?` (parametro 2) que sea un acceso OAuth y no un token manual, borra los refresh del
     * acceso y el acceso (`Origen = 2`) en ese orden con parametros preparados y confirma. Sin ejecutarlo.
     *
     * @return array Resultado por caso
     */
    public function revocarAccesoTest(){

        $results = [];
        $del = $this->cuerpoMetodo('Delete');
        $this->addCase($results, 'fuente_delete_leido', $del !== '', 'no se pudo leer Delete()');

        $this->addCase($results, 'delete_exige_delete', str_contains($del, "REQUEST_METHOD'] !== 'DELETE'") && str_contains($del, "'Error en el Request Method', 405"),
            'Delete() debe exigir el verbo DELETE y responder 405');
        $this->addCase($results, 'delete_verbo_antes_de_transaccion',
            $this->pos($del, "REQUEST_METHOD'] !== 'DELETE'") !== false && $this->pos($del, "REQUEST_METHOD'] !== 'DELETE'") < $this->pos($del, 'Base_transactionBegin()'),
            'Delete() debe validar el verbo antes de abrir la transaccion');
        $this->addCase($results, 'delete_lee_cuerpo', str_contains($del, 'parse_str(file_get_contents("php://input"),$dataDelete)'),
            'Delete() debe leer el cuerpo de la peticion DELETE con parse_str(php://input)');
        $this->addCase($results, 'delete_descifra_id', str_contains($del, "encryptDecrypt('decrypt', \$dataDelete['idToken'] ?? '')") && str_contains($del, "isValidDecrypted(\$AccesoID, 'id')"),
            'Delete() debe descifrar y validar idToken');
        $this->addCase($results, 'delete_id_invalido_antes_de_transaccion',
            $this->pos($del, "isValidDecrypted(\$AccesoID, 'id')") < $this->pos($del, 'Base_transactionBegin()'),
            'el id debe validarse antes de abrir la transaccion');

        // Guarda: solo accesos OAuth (Origen = 2); un token manual (Origen = 1) nunca se revoca
        $this->addCase($results, 'delete_guarda_origen_2',
            str_contains($del, "'where'   => 'idToken = ? AND Origen = ?'") && str_contains($del, '[(int) $AccesoID[\'data\'], 2]'),
            'Delete() debe comprobar con parametros que el id es un acceso OAuth (Origen = 2)');
        $this->addCase($results, 'delete_guarda_400', str_contains($del, 'empty($ResponseGuarda[\'data\'])') && str_contains($del, "Response::error('Registro inválido', 400)"),
            'Delete() debe responder 400 si el id no existe o es un token manual');
        $this->addCase($results, 'delete_guarda_revierte',
            preg_match('/empty\(\$ResponseGuarda\[\'data\'\]\).*?Base_transactionRollback\(\).*?Response::error\(\'Registro inv/s', $del) === 1,
            'ante una guarda fallida debe revertirse la transaccion antes de responder 400');
        $g = $this->pos($del, "'where'   => 'idToken = ? AND Origen = ?'");
        $c1 = $this->pos($del, 'DELETE FROM tokens_oauth_refresh WHERE idTokenAcceso = ?');
        $c2 = $this->pos($del, 'DELETE FROM tokens_listado WHERE idToken = ? AND Origen = 2');
        $this->addCase($results, 'delete_guarda_antes_de_borrar', $g !== false && $c1 !== false && $g < $c1,
            'la guarda Origen debe ejecutarse antes de cualquier borrado');
        $this->addCase($results, 'delete_cascada_en_orden', $c1 !== false && $c2 !== false && $c1 < $c2,
            'Delete() debe borrar primero los refresh del acceso y luego el acceso (con Origen = 2)');
        $this->addCase($results, 'delete_no_borra_manuales', !str_contains($del, 'Origen = 1') && substr_count($del, "Origen = 2'") === 1,
            'el DELETE final debe acotarse a Origen = 2 y no puede referirse a Origen = 1');
        $this->addCase($results, 'delete_parametros_preparados', str_contains($del, "'params'    => [(int) \$AccesoID['data']]"),
            'la cascada debe usar parametros preparados con el id descifrado');
        $this->addCase($results, 'delete_transaccion',
            $this->pos($del, 'Base_transactionBegin()') < $g && $c2 < $this->pos($del, 'Base_transactionCommit()'),
            'la guarda y los borrados deben ocurrir entre Begin y Commit');
        $this->addCase($results, 'delete_falla_revierte',
            preg_match('/\$Response\[\'status\'\] === false\).*?Base_transactionRollback\(\).*?Response::error\(/s', $del) === 1,
            'ante la falla de un borrado debe revertirse la transaccion antes de responder');
        $this->addCase($results, 'delete_commit_antes_de_exito',
            $this->pos($del, 'Base_transactionCommit()') !== false && $this->pos($del, 'Base_transactionCommit()') < $this->pos($del, 'Response::success(true)'),
            'Delete() debe confirmar antes de responder true');

        return $results;

    }

    /******************************************************************************/
    /*                                  VISTA                                     */
    /******************************************************************************/
    /**
     * Renderiza `-Resumen-OauthAccesos-UpdateList` con un acceso por estado: la vista distingue Vigente,
     * Expirado con refresh renovable ("Expirado, renovable hasta ...") y Expirado; escapa el cliente; el
     * boton Revocar solo con nivel >= 2 y con el id cifrado; mensaje de lista vacia; sin valor de token.
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function vistaAccesosTest($f3){

        $results = [];
        $BASE    = (string)$f3->get('BASE');
        $ruta    = $this->rutaModulo('../views/'.self::VISTA.'.php');

        foreach (self::NIVELES as $nivel) {
            $html = $this->renderizarVista($ruta, $this->datosSinteticos($nivel), $BASE, $f3);
            $this->addCase($results, 'vista_render_n'.$nivel, $html !== '', 'no se pudo renderizar la vista con nivel '.$nivel);
            $this->addCase($results, 'vista_revocar_n'.$nivel, substr_count($html, 'tabAccesoRevocar(') === ($nivel >= 2 ? 3 : 0),
                'el boton Revocar debe '.($nivel >= 2 ? 'mostrarse en las 3 filas' : 'ocultarse').' con nivel '.$nivel);
        }

        $html = $this->renderizarVista($ruta, $this->datosSinteticos(2), $BASE, $f3);
        foreach (['Cliente', 'Emitido', 'Access expira', 'Renovable hasta', 'Estado'] as $col) {
            $this->addCase($results, 'vista_columna_'.md5($col), str_contains($html, '<th scope="col">'.$col.'</th>'), 'falta la columna '.$col);
        }
        // Los tres estados: vigente, expirado renovable y expirado
        $this->addCase($results, 'vista_estado_vigente', substr_count($html, '>Vigente<') === 1, 'solo el acceso no expirado debe marcarse Vigente');
        $this->addCase($results, 'vista_estado_renovable',
            substr_count($html, 'Expirado, renovable hasta 2099-02-02 10:00:00') === 1,
            'el acceso expirado con refresh vivo debe mostrar "Expirado, renovable hasta <fecha>"');
        $this->addCase($results, 'vista_estado_expirado', substr_count($html, '>Expirado<') === 1,
            'el acceso expirado sin refresh debe marcarse solo como Expirado');
        $this->addCase($results, 'vista_tres_estados_distintos', substr_count($html, 'badge bg-success') === 1 && substr_count($html, 'badge bg-warning') === 1 && substr_count($html, 'badge bg-secondary') === 1,
            'cada estado debe usar su propia etiqueta (success, warning y secondary)');
        $this->addCase($results, 'vista_fechas', str_contains($html, '2026-01-02 10:00:00') && str_contains($html, '2099-01-02 11:00:00'),
            'debe mostrar las fechas de emision y de expiracion del acceso');
        $this->addCase($results, 'vista_cliente_escapado', !str_contains($html, '<b>Alfa</b>') && str_contains($html, '&lt;b&gt;Alfa&lt;/b&gt;'),
            'el nombre del cliente debe escaparse');
        $this->addCase($results, 'vista_id_cifrado', !str_contains($html, 'tabAccesoRevocar("21"') && preg_match('/tabAccesoRevocar\("[^"]{8,}"/', $html) === 1,
            'el id del acceso en el boton Revocar debe ir cifrado');

        $vacia = $this->renderizarVista($ruta, $this->datosSinteticos(2, []), $BASE, $f3);
        $this->addCase($results, 'vista_lista_vacia', str_contains($vacia, 'Sin accesos OAuth emitidos con este token'), 'la lista vacia debe mostrar el mensaje de sin accesos');

        $src = $this->codigoFuente($ruta);
        $this->addCase($results, 'vista_sin_token', !str_contains($src, "['Token']"), 'la vista no debe leer el valor del token');
        $this->addCase($results, 'vista_sin_sesion', !str_contains($src, '$_SESSION'), 'la vista usa $_SESSION directamente');
        $this->addCase($results, 'vista_sin_sql', !preg_match('/\b(SELECT\s.+\sFROM|INSERT\s+INTO|UPDATE\s+\w+\s+SET|DELETE\s+FROM)\b/i', $src), 'la vista contiene SQL embebido');
        $this->addCase($results, 'vista_sin_retroceso', !preg_match('/\.\.[\/\\\\]/', $src), 'la vista contiene retroceso de ruta');

        return $results;

    }

}
