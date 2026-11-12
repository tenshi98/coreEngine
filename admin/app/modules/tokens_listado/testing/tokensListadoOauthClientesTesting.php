<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class tokensListadoOauthClientesTesting
 *
 * Pruebas del modulo `tokens` - controlador `tokensListadoOauthClientes` (subcontrolador de la pestana
 * "Clientes OAuth" del Resumen de un token: `UpdateList` lista los clientes que usan el token y `Revocar`
 * borra sus accesos con ese token). El acceso y los permisos son los de `tokensListado`
 * (`controllerName = 'tokensListado'`).
 *
 * Convenciones del sistema de testeos: toda prueba es un metodo publico que termina en "Test"; devuelve
 * un arreglo [caso => 'OK'|'FALLO: razon']; si declara parametros el runner le inyecta F3.
 *
 * Alcance: pruebas NO destructivas. `Revocar()` y `UpdateList()` terminan en `Response::*()` (`exit`) y
 * `Revocar()` borra en BD, por eso NO se invocan: se verifican por reflexion, lectura del fuente y render
 * de la vista con datos sinteticos.
 *
 * @see projects/mcp-oauth/specs/technical.md §21.1
 * @see skills/testing/SKILL.md
 */
class tokensListadoOauthClientesTesting extends ControllerBase {

    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR          = 'tokensListadoOauthClientes';
    private const CONTROLADOR_PERMISOS = 'tokensListado';
    private const INSTALADOR           = 'tokensInstaller';
    private const RUTA_WEB             = 'administracion/tokens/listado';
    private const RUTA_UPDATELIST      = 'administracion/tokens/listado/oauthClientes/updateList/@id';
    private const RUTA_REVOCAR         = 'administracion/tokens/listado/oauthClientes/revocar';
    private const VISTA                = 'tokensListado-Resumen-OauthClientes-UpdateList';
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
    // Datos sinteticos de la vista de la pestana
    /*******************************************************************/
    private function datosSinteticos(int $nivel, array $clientes = null): array{
        return [
            'UserAccess'       => ['LevelAccess' => $nivel, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_Codification' => new FunctionsSecurityCodification(),
            'TokenID'          => 'TOKENCIFRADO',
            'arrClientes'      => $clientes ?? [
                [
                    'idCliente'      => 5,
                    'Nombre'         => 'Cliente <b>Alfa</b>',
                    'ClientId'       => 'abcdef0123456789abcdef0123456789',
                    'AuthMethod'     => 'none',
                    'RedirectUris'   => '["http://127.0.0.1:5555/cb","https://claude.ai/api/mcp/auth_callback"]',
                    'Estado'         => 'Activo',
                    'EstadoColor'    => 'success',
                    'NAccesos'       => 3,
                    'UltimaConcesion' => '2026-01-02 10:00:00',
                ],
            ],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica los archivos (controlador, vista del fragmento, `.htaccess`), la clase, la herencia sin
     * sobrescribir `beforeroute` y las firmas de `UpdateList` (2 parametros) y `Revocar` (0).
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

        foreach (['UpdateList' => 2, 'Revocar' => 0] as $metodo => $parametros) {
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
            !method_exists(self::CONTROLADOR, 'Insert') && !method_exists(self::CONTROLADOR, 'Update') && !method_exists(self::CONTROLADOR, 'Delete'),
            self::CONTROLADOR.' solo expone UpdateList y Revocar');

        return $results;

    }

    /******************************************************************************/
    /*                       CONTROLADOR: RUTAS E INSTALADOR                      */
    /******************************************************************************/
    /**
     * `controllerName = 'tokensListado'` (permisos compartidos) y rutas del installer: GET
     * `oauthClientes/updateList/@id` y POST `oauthClientes/revocar`, ambas con nivel 2 y handler del
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
        $matriz = [
            'updatelist' => ['1 '.self::RUTA_UPDATELIST, self::CONTROLADOR.'->UpdateList'],
            'revocar'    => ['2 '.self::RUTA_REVOCAR,    self::CONTROLADOR.'->Revocar'],
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
        $propias = array_filter($rutas, static function ($r) { return $r[2] === 'tokensListadoOauthClientes'; });
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
            'ruta_updatelist_router' => ['/'.self::RUTA_UPDATELIST, 'GET',  self::CONTROLADOR.'->UpdateList'],
            'ruta_revocar_router'    => ['/'.self::RUTA_REVOCAR,    'POST', self::CONTROLADOR.'->Revocar'],
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
    /*                              LISTADO DE CLIENTES                            */
    /******************************************************************************/
    /**
     * `UpdateList` valida el id cifrado y que el token sea manual (`Origen = 1`) antes de leer; la consulta
     * `getClientes` filtra por `idTokenPadre` y `Origen = 2` con parametros preparados y no selecciona el
     * valor de ningun token ni el secreto del cliente. Sin ejecutar el endpoint.
     *
     * @return array Resultado por caso
     */
    public function listadoClientesTest(){

        $results = [];
        $list = $this->cuerpoMetodo('UpdateList');
        $cons = $this->cuerpoMetodo('getClientes');
        $this->addCase($results, 'fuente_updatelist_leido', $list !== '', 'no se pudo leer UpdateList()');
        $this->addCase($results, 'fuente_getclientes_leido', $cons !== '', 'no se pudo leer getClientes()');

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
            $this->pos($list, "'where'   => 'idToken = ? AND Origen = ?'") !== false && $this->pos($list, "'where'   => 'idToken = ? AND Origen = ?'") < $this->pos($list, '$this->getClientes('),
            'la guarda del token debe ejecutarse antes de leer los clientes');
        $this->addCase($results, 'updatelist_vista', str_contains($list, "showVista(2, \$this->returnRutaVista(__DIR__, 'app').'/'.\$this->controllerName.'-Resumen-OauthClientes-UpdateList.php')"),
            'UpdateList() debe cargar '.self::VISTA.'.php con showVista(2)');
        $this->addCase($results, 'updatelist_errores', str_contains($list, 'showError(2'), 'UpdateList() debe manejar errores con showError(2)');
        $this->addCase($results, 'updatelist_nivel_de_tokens', str_contains($list, 'getArrLevel($f3, $this->controllerName)'),
            'UpdateList() debe resolver el nivel con controllerName (tokensListado)');

        $this->addCase($results, 'getclientes_filtra_padre_origen',
            str_contains($cons, "'where'   => 'tokens_listado.idTokenPadre = ? AND tokens_listado.Origen = ?'") && str_contains($cons, '[$idToken, 2]'),
            'getClientes() debe filtrar idTokenPadre = ? y Origen = 2 con parametros preparados');
        $this->addCase($results, 'getclientes_agrupa_por_cliente', str_contains($cons, "'group'   => 'tokens_oauth_clientes.idCliente'") && str_contains($cons, 'COUNT(tokens_listado.idToken) AS NAccesos'),
            'getClientes() debe agrupar por cliente y contar sus accesos');
        $this->addCase($results, 'getclientes_sin_token_ni_secreto',
            !preg_match('/tokens_listado\.Token\b/', $cons) && !str_contains($cons, 'SecretHash') && !str_contains($cons, 'IPClave') && !str_contains($cons, 'IPRegistro'),
            'getClientes() no debe seleccionar el valor de un token, el secreto ni las IP del cliente');
        $this->addCase($results, 'getclientes_sin_concat_sql', !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $this->fuenteControlador()),
            'el controlador concatena entrada del usuario');

        return $results;

    }

    /******************************************************************************/
    /*                       REVOCAR LOS ACCESOS DE UN CLIENTE                     */
    /******************************************************************************/
    /**
     * `Revocar` exige POST con 405 antes de abrir la transaccion, descifra y valida `idCliente` e
     * `idToken`, borra refresh y accesos hijos del cliente con ese token en ese orden (parametros
     * preparados, solo `Origen = 2`), confirma y responde `true`. Sin ejecutar `Revocar()`.
     *
     * @return array Resultado por caso
     */
    public function revocarTest(){

        $results = [];
        $rev = $this->cuerpoMetodo('Revocar');
        $this->addCase($results, 'fuente_revocar_leido', $rev !== '', 'no se pudo leer Revocar()');

        $this->addCase($results, 'revocar_exige_post', str_contains($rev, "REQUEST_METHOD'] !== 'POST'") && str_contains($rev, "'Error en el Request Method', 405"),
            'Revocar() debe exigir el verbo POST y responder 405');
        $this->addCase($results, 'revocar_verbo_antes_de_transaccion',
            $this->pos($rev, "REQUEST_METHOD'] !== 'POST'") !== false && $this->pos($rev, "REQUEST_METHOD'] !== 'POST'") < $this->pos($rev, 'Base_transactionBegin()'),
            'Revocar() debe validar el verbo antes de abrir la transaccion');
        $this->addCase($results, 'revocar_descifra_cliente', str_contains($rev, "encryptDecrypt('decrypt', \$_POST['idCliente'] ?? '')") && str_contains($rev, "isValidDecrypted(\$ClienteID, 'id')"),
            'Revocar() debe descifrar y validar idCliente');
        $this->addCase($results, 'revocar_descifra_token', str_contains($rev, "encryptDecrypt('decrypt', \$_POST['idToken'] ?? '')") && str_contains($rev, "isValidDecrypted(\$TokenID, 'id')"),
            'Revocar() debe descifrar y validar idToken');
        $this->addCase($results, 'revocar_ids_400', substr_count($rev, "Response::error('Registro inválido', 400)") === 2,
            'un id invalido debe responder 400 ('.substr_count($rev, "Response::error('Registro inválido', 400)").' respuestas de registro)');
        $this->addCase($results, 'revocar_ids_antes_de_transaccion',
            $this->pos($rev, "isValidDecrypted(\$TokenID, 'id')") < $this->pos($rev, 'Base_transactionBegin()'),
            'los ids deben validarse antes de abrir la transaccion');

        $c1 = $this->pos($rev, 'DELETE FROM tokens_oauth_refresh WHERE idTokenAcceso IN (SELECT idToken FROM tokens_listado WHERE idClienteOAuth = ? AND idTokenPadre = ? AND Origen = 2)');
        $c2 = $this->pos($rev, 'DELETE FROM tokens_listado WHERE idClienteOAuth = ? AND idTokenPadre = ? AND Origen = 2');
        $this->addCase($results, 'revocar_cascada_en_orden', $c1 !== false && $c2 !== false && $c1 < $c2,
            'Revocar() debe borrar primero los refresh y luego los accesos hijos (solo Origen = 2)');
        $this->addCase($results, 'revocar_solo_origen_2', preg_match_all("/Origen = 2\\)?'/", $rev) === 2 && !str_contains($rev, 'Origen = 1'),
            'las dos consultas deben acotarse a Origen = 2 (nunca borrar un token manual)');
        $this->addCase($results, 'revocar_parametros_preparados',
            str_contains($rev, "'params'    => [(int) \$ClienteID['data'], (int) \$TokenID['data']]"),
            'la cascada debe usar parametros preparados con idCliente e idToken descifrados (en ese orden)');
        $this->addCase($results, 'revocar_sin_concat', !preg_match('/DELETE FROM[^;]*\.\s*\$/', $rev) && !str_contains($rev, '$_POST[\'idCliente\'] .'),
            'las consultas de Revocar() no deben concatenar entrada del usuario');
        $this->addCase($results, 'revocar_transaccion',
            $this->pos($rev, 'Base_transactionBegin()') < $c1 && $c2 < $this->pos($rev, 'Base_transactionCommit()'),
            'la cascada debe ocurrir entre Begin y Commit');
        $this->addCase($results, 'revocar_falla_revierte',
            preg_match('/\$Response\[\'status\'\] === false\).*?Base_transactionRollback\(\).*?Response::error\(/s', $rev) === 1,
            'ante la falla de una consulta debe revertirse la transaccion antes de responder');
        $this->addCase($results, 'revocar_commit_antes_de_exito',
            $this->pos($rev, 'Base_transactionCommit()') !== false && $this->pos($rev, 'Base_transactionCommit()') < $this->pos($rev, 'Response::success(true)'),
            'Revocar() debe confirmar antes de responder true');

        return $results;

    }

    /******************************************************************************/
    /*                                  VISTA                                     */
    /******************************************************************************/
    /**
     * Renderiza `-Resumen-OauthClientes-UpdateList` con datos sinteticos: columnas, `client_id` truncado,
     * redirecciones completas y escapadas, nombre escapado, boton Revocar solo con nivel >= 2 y mensaje
     * de lista vacia.
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function vistaClientesTest($f3){

        $results = [];
        $BASE    = (string)$f3->get('BASE');
        $ruta    = $this->rutaModulo('../views/'.self::VISTA.'.php');

        foreach (self::NIVELES as $nivel) {
            $html = $this->renderizarVista($ruta, $this->datosSinteticos($nivel), $BASE, $f3);
            $this->addCase($results, 'vista_render_n'.$nivel, $html !== '', 'no se pudo renderizar la vista con nivel '.$nivel);
            $this->addCase($results, 'vista_revocar_n'.$nivel, str_contains($html, 'tabClienteRevocar(') === ($nivel >= 2),
                'el boton Revocar accesos debe '.($nivel >= 2 ? 'mostrarse' : 'ocultarse').' con nivel '.$nivel);
        }

        $html = $this->renderizarVista($ruta, $this->datosSinteticos(2), $BASE, $f3);
        foreach (['Nombre (declarado)', 'client_id', 'Tipo', 'Estado', 'Accesos', 'Última concesión', 'Redirección'] as $col) {
            $this->addCase($results, 'vista_columna_'.md5($col), str_contains($html, '<th scope="col">'.$col.'</th>'), 'falta la columna '.$col);
        }
        $this->addCase($results, 'vista_clientid_truncado', str_contains($html, 'abcdef01&hellip;') && !str_contains($html, 'abcdef0123456789abcdef0123456789'),
            'el client_id debe mostrarse truncado a 8 caracteres');
        $this->addCase($results, 'vista_redirecciones_completas',
            str_contains($html, 'http://127.0.0.1:5555/cb') && str_contains($html, 'https://claude.ai/api/mcp/auth_callback'),
            'las redirect_uri registradas deben mostrarse completas');
        $this->addCase($results, 'vista_nombre_escapado', !str_contains($html, '<b>Alfa</b>') && str_contains($html, '&lt;b&gt;Alfa&lt;/b&gt;'),
            'el nombre declarado por el cliente debe escaparse');
        $this->addCase($results, 'vista_id_cifrado', !str_contains($html, 'tabClienteRevocar("5"') && preg_match('/tabClienteRevocar\("[^"]{8,}"/', $html) === 1,
            'el id del cliente en el boton Revocar debe ir cifrado');
        $this->addCase($results, 'vista_nombre_js_escapado', str_contains($html, '\u003Cb\u003EAlfa\u003C\\/b\u003E'),
            'el nombre pasado a JavaScript debe ir con JSON_HEX_TAG');
        $this->addCase($results, 'vista_accesos', str_contains($html, '<td>3</td>'), 'debe mostrar el numero de accesos (3)');

        // Redirect_uri mal formada o JSON no valido: se muestra como texto escapado, sin romper la vista
        $malo = $this->renderizarVista($ruta, $this->datosSinteticos(1, [[
            'idCliente' => 6, 'Nombre' => 'X', 'ClientId' => 'zz', 'AuthMethod' => 'none', 'RedirectUris' => '<script>x</script>',
            'Estado' => 'Activo', 'EstadoColor' => 'success', 'NAccesos' => 0, 'UltimaConcesion' => null,
        ]]), $BASE, $f3);
        $this->addCase($results, 'vista_uris_no_json_escapadas', $malo !== '' && !str_contains($malo, '<script>') && str_contains($malo, '&lt;script&gt;'),
            'una RedirectUris que no es JSON debe mostrarse escapada');

        $vacia = $this->renderizarVista($ruta, $this->datosSinteticos(2, []), $BASE, $f3);
        $this->addCase($results, 'vista_lista_vacia', str_contains($vacia, 'Ningún cliente OAuth usa este token'), 'la lista vacia debe mostrar el mensaje de sin clientes');

        $src = $this->codigoFuente($ruta);
        $this->addCase($results, 'vista_sin_sesion', !str_contains($src, '$_SESSION'), 'la vista usa $_SESSION directamente');
        $this->addCase($results, 'vista_sin_sql', !preg_match('/\b(SELECT\s.+\sFROM|INSERT\s+INTO|UPDATE\s+\w+\s+SET|DELETE\s+FROM)\b/i', $src), 'la vista contiene SQL embebido');
        $this->addCase($results, 'vista_sin_retroceso', !preg_match('/\.\.[\/\\\\]/', $src), 'la vista contiene retroceso de ruta');
        $this->addCase($results, 'vista_sin_secretos', !str_contains($src, 'SecretHash') && !str_contains($src, "['Token']"), 'la vista no debe leer secretos ni tokens');

        return $results;

    }

}
