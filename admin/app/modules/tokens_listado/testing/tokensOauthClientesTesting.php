<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class tokensOauthClientesTesting
 *
 * Pruebas del modulo `tokens` - controlador `tokensOauthClientes` (CRUD Normal de gestion de los clientes
 * OAuth registrados en el servidor de autorizacion del MCP, `idTipo` 1): `listAll`, `UpdateList`, `View`,
 * `GetID`, `Update` y `Delete`. NO tiene alta manual: sin `Insert` ni `-formNew` (los clientes se registran
 * por `/register`).
 *
 * Convenciones del sistema de testeos: toda prueba es un metodo publico que termina en "Test"; devuelve
 * un arreglo [caso => 'OK'|'FALLO: razon']; si declara parametros el runner le inyecta F3.
 *
 * Alcance: pruebas NO destructivas. `Update()` y `Delete()` terminan en `Response::*()` (`exit`) y escriben
 * en BD, por eso NO se invocan: se verifican por reflexion, lectura del fuente, `dataCheck` sin BD y render
 * de vistas con datos sinteticos.
 *
 * @see projects/mcp-oauth/specs/technical.md §21.1
 * @see skills/testing/SKILL.md
 */
class tokensOauthClientesTesting extends ControllerBase {

    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR          = 'tokensOauthClientes';
    private const INSTALADOR           = 'tokensInstaller';
    private const RUTA_WEB             = 'administracion/tokens/clientesOauth';
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
    // Rutas del permiso 2 (clientes OAuth) del installer: [idMetodo.' '.RutaWeb => [handler, nivel, controller]]
    /*******************************************************************/
    private function rutasInstalador(): array{
        $rutas = [];
        if (!class_exists(self::INSTALADOR)) {
            return $rutas;
        }
        try {
            $instalador = (new ReflectionClass(self::INSTALADOR))->newInstanceWithoutConstructor();
            foreach ($instalador->getRouteDefinitions(2) as $ruta) {
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
    // Datos sinteticos usados por las vistas del modulo
    /*******************************************************************/
    private function datosSinteticos(int $nivel): array{
        $cliente = [
            'idCliente'       => 5,
            'Nombre'          => 'Cliente <b>Alfa</b>',
            'ClientId'        => 'abcdef0123456789abcdef0123456789',
            'AuthMethod'      => 'none',
            'idEstado'        => 1,
            'Estado'          => 'Activo',
            'EstadoColor'     => 'success',
            'NAccesos'        => 3,
            'FechaRegistro'   => '2026-01-02 10:00:00',
            'IPRegistro'      => '203.0.113.9',
            'IPClave'         => '203.0.113.9',
            'FechaUltimoUso'  => null,
            'RedirectUris'    => '["http://127.0.0.1:5555/cb"]',
            'Metadatos'       => '{"client_uri":"https://c.example/","logo_uri":"https://c.example/l.png"}',
        ];
        return [
            'PageTitle'       => 'Clientes OAuth',
            'PageDescription' => 'Clientes OAuth.',
            'PageAuthor'      => 'Pruebas',
            'PageKeywords'    => 'Pruebas',
            'TableTitle'      => 'Clientes OAuth',
            'UserData'        => [
                'MainPathUrl'          => '/admin/public/upload/',
                'sistemaModalSubtitle' => 1,
                'sistemaModalCloseBTN' => 2,
                'UserType'             => 2,
            ],
            'UserAccess'        => ['LevelAccess' => $nivel, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_FormInputs'    => new UIFormInputs(),
            'Fnc_Codification'  => new FunctionsSecurityCodification(),
            'Fnc_WidgetsCommon' => new UIWidgetsCommon(),
            'Fnc_WidgetsViews'  => new UIWidgetsViews(),
            'NSinUso'           => 2,
            'arrList'           => [
                $cliente,
                array_merge($cliente, ['idCliente' => 6, 'Nombre' => 'Cliente Beta', 'AuthMethod' => 'client_secret_post', 'idEstado' => 2, 'Estado' => 'Inactivo', 'EstadoColor' => 'danger', 'FechaUltimoUso' => '2026-01-03 10:00:00']),
            ],
            'arrEstado'         => [['ID' => 1, 'Nombre' => 'Activo'], ['ID' => 2, 'Nombre' => 'Inactivo']],
            'rowData'           => $cliente,
            'arrRedirectUris'   => ['http://127.0.0.1:5555/cb', 'https://claude.ai/api/mcp/auth_callback'],
            'arrMetadatos'      => ['client_uri' => 'https://c.example/', 'logo_uri' => 'https://c.example/l.png', 'scope' => ['a', 'b']],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica `.htaccess`, las 5 vistas del CRUD (sin `-formNew`), la clase, la herencia sin sobrescribir
     * `beforeroute` y la ausencia de `Insert`.
     *
     * @return array Resultado por caso
     */
    public function estructuraModuloTest(){

        $results = [];

        foreach (['', 'controller', 'views', 'installer', 'testing'] as $carpeta) {
            $ruta = $this->rutaModulo('../'.($carpeta !== '' ? $carpeta.'/' : '').'.htaccess');
            $this->addCase($results, 'htaccess_'.($carpeta !== '' ? $carpeta : 'raiz'),
                str_contains($this->codigoFuente($ruta), 'deny from all'),
                'falta .htaccess con "deny from all" en '.($carpeta !== '' ? $carpeta : 'la raiz del modulo'));
        }
        foreach (['List', 'UpdateList', 'View', 'formEdit', 'formSearch'] as $sufijo) {
            $this->addCase($results, 'vista_'.$sufijo, is_file($this->rutaModulo('../views/'.self::CONTROLADOR.'-'.$sufijo.'.php')),
                'falta la vista '.self::CONTROLADOR.'-'.$sufijo.'.php');
        }
        $this->addCase($results, 'sin_vista_formNew', !is_file($this->rutaModulo('../views/'.self::CONTROLADOR.'-formNew.php')),
            'no debe existir '.self::CONTROLADOR.'-formNew.php: los clientes no se crean a mano');
        $this->addCase($results, 'clase_controlador', class_exists(self::CONTROLADOR), 'la clase '.self::CONTROLADOR.' no se carga');
        if (class_exists(self::CONTROLADOR)) {
            $this->addCase($results, 'hereda_controllerbase', is_subclass_of(self::CONTROLADOR, 'ControllerBase'), self::CONTROLADOR.' no extiende ControllerBase');
            $this->addCase($results, 'no_sobrescribe_beforeroute',
                (new ReflectionMethod(self::CONTROLADOR, 'beforeroute'))->getDeclaringClass()->getName() === 'ControllerBase',
                self::CONTROLADOR.' sobrescribe beforeroute y puede saltarse la guardia CSRF');
        }
        $this->addCase($results, 'sin_metodo_Insert', !method_exists(self::CONTROLADOR, 'Insert'),
            self::CONTROLADOR.'::Insert() no debe existir (el alta es el registro dinamico del AS)');
        $this->addCase($results, 'clase_installer', class_exists(self::INSTALADOR), 'la clase '.self::INSTALADOR.' no se carga');
        $this->addCase($results, 'hereda_controllerinstaller', is_subclass_of(self::INSTALADOR, 'ControllerInstaller'), self::INSTALADOR.' no extiende ControllerInstaller');

        return $results;

    }

    /******************************************************************************/
    /*                       CONTROLADOR, RUTAS Y PERMISOS                         */
    /******************************************************************************/
    /**
     * Verifica las firmas (`listAll`, `UpdateList`, `View`, `GetID`, `Update`, `Delete`) y el contrato de
     * presentacion de las vistas (`showVista(1)` en `-List`; `showVista(2)` en el resto).
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];
        foreach (['listAll' => [1, 1], 'UpdateList' => [1, 1], 'View' => [2, 2], 'GetID' => [2, 2], 'Update' => [0, 0], 'Delete' => [0, 0]] as $metodo => [$min, $max]) {
            $existe = method_exists(self::CONTROLADOR, $metodo);
            $this->addCase($results, 'metodo_'.$metodo, $existe, 'no existe '.self::CONTROLADOR.'::'.$metodo.'()');
            if (!$existe) {
                continue;
            }
            $r = new ReflectionMethod(self::CONTROLADOR, $metodo);
            $this->addCase($results, 'metodo_'.$metodo.'_publico', $r->isPublic(), $metodo.'() no es publico');
            $n = $r->getNumberOfParameters();
            $this->addCase($results, 'metodo_'.$metodo.'_parametros', $n >= $min && $n <= $max, $metodo.'() declara '.$n.' parametros (esperado entre '.$min.' y '.$max.')');
        }

        $vistas = ['listAll' => ['-List.php', 1], 'UpdateList' => ['-UpdateList.php', 2], 'View' => ['-View.php', 2], 'GetID' => ['-formEdit.php', 2]];
        foreach ($vistas as $metodo => [$archivo, $tipoVista]) {
            $cuerpo = $this->cuerpoMetodo($metodo);
            $this->addCase($results, 'vista_'.$metodo,
                str_contains($cuerpo, "showVista(".$tipoVista.", \$this->returnRutaVista(__DIR__, 'app').'/'.\$this->controllerName.'".$archivo."')"),
                $metodo.'() debe cargar '.self::CONTROLADOR.$archivo.' con showVista('.$tipoVista.')');
            $this->addCase($results, 'errores_'.$metodo, str_contains($cuerpo, 'showError('), $metodo.'() no maneja errores con showError()');
            $this->addCase($results, 'usuario_'.$metodo, str_contains($cuerpo, 'getUserData($f3)') && str_contains($cuerpo, 'getArrLevel($f3, $this->controllerName)'),
                $metodo.'() no obtiene los datos del usuario y el nivel con controllerName');
        }
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'controller_name', str_contains($codigo, "\$this->controllerName = 'tokensOauthClientes';"), 'controllerName debe ser tokensOauthClientes');
        $this->addCase($results, 'sin_formnew', !str_contains($codigo, '-formNew.php'), 'el controlador no debe cargar -formNew.php');
        foreach (['View', 'GetID'] as $metodo) {
            $cuerpo = $this->cuerpoMetodo($metodo);
            $this->addCase($results, 'id_obligatorio_'.$metodo, str_contains($cuerpo, "!isset(\$params['id'])") && str_contains($cuerpo, "Response::error('Registro ID inexistente', 400)"),
                $metodo.'() debe responder 400 si falta el id');
            $this->addCase($results, 'id_cifrado_'.$metodo, str_contains($cuerpo, "encryptDecrypt('decrypt', \$params['id'])") && str_contains($cuerpo, "isValidDecrypted(\$ClienteID, 'id')"),
                $metodo.'() debe descifrar y validar el id');
            $this->addCase($results, 'fila_vacia_400_'.$metodo, str_contains($cuerpo, "empty(\$rowData['data'])") && str_contains($cuerpo, "Response::error('Registro inválido', 400)"),
                $metodo.'() debe responder 400 con una fila vacia');
        }

        /*====================================*/
        // Lectura: nunca el hash del secreto ni de las IP en el listado
        /*====================================*/
        foreach (['View', 'GetID', 'getDataList', 'listAll'] as $metodo) {
            $this->addCase($results, 'sin_secrethash_'.$metodo, !str_contains($this->cuerpoMetodo($metodo), 'SecretHash'),
                $metodo.'() no debe seleccionar SecretHash');
        }
        $this->addCase($results, 'view_campos', str_contains($this->cuerpoMetodo('View'), 'tokens_oauth_clientes.Metadatos') && str_contains($this->cuerpoMetodo('View'), 'tokens_oauth_clientes.RedirectUris'),
            'View() debe leer RedirectUris y Metadatos');
        $this->addCase($results, 'view_decodifica_json',
            str_contains($this->cuerpoMetodo('View'), "json_decode((string) (\$rowData['data']['RedirectUris'] ?? ''), true)")
            && str_contains($this->cuerpoMetodo('View'), "json_decode((string) (\$rowData['data']['Metadatos'] ?? ''), true)"),
            'View() debe decodificar RedirectUris y Metadatos y entregar arrays a la vista');
        $this->addCase($results, 'listado_naccesos', str_contains($this->cuerpoMetodo('getDataList'), 'FROM tokens_listado t WHERE t.idClienteOAuth = tokens_oauth_clientes.idCliente) AS NAccesos'),
            'getDataList() debe contar los accesos del cliente (NAccesos)');
        $this->addCase($results, 'listado_nsinuso', str_contains($this->cuerpoMetodo('listAll'), 'COUNT(*) AS NSinUso') && str_contains($this->cuerpoMetodo('listAll'), "'where'   => 'FechaUltimoUso IS NULL'"),
            'listAll() debe contar los clientes sin uso (FechaUltimoUso IS NULL)');

        $view = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'));
        $this->addCase($results, 'view_adr0018_comando',
            preg_match("/<form|LevelAccess'\\]\\s*>=\\s*[234]|listTableDataEdit|listTableDataDel|_Add\\(|_Edit\\(|_Del\\(|bi-pencil|bi-trash/", $view) === 0,
            '-View incumple el comando de verificacion de la ADR 0018');
        $this->addCase($results, 'view_adr0018_sin_escritura', preg_match('/<input|<select|<textarea|Fnc_FormInputs|SendDataForms|UpdateContentId/', $view) === 0,
            '-View no debe contener campos, Fnc_FormInputs, SendDataForms ni cargas AJAX (ADR 0018)');

        return $results;

    }

    /**
     * Revisa el contrato de escritura sin ejecutar: `Update` reduce el POST a `idCliente,idEstado` y aplica la
     * cascada al desactivar; `Delete` borra el cliente y aplica la cascada; verbo, transaccion y commit.
     *
     * @return array Resultado por caso
     */
    public function controladorEscrituraTest(){

        $results = [];
        foreach (['Update' => 'POST', 'Delete' => 'DELETE'] as $metodo => $verbo) {
            $cuerpo = $this->cuerpoMetodo($metodo);
            $this->addCase($results, $metodo.'_existe', $cuerpo !== '', $metodo.'() no encontrado');
            if ($cuerpo === '') {
                continue;
            }
            $this->addCase($results, $metodo.'_verbo', str_contains($cuerpo, "!== '".$verbo."'") && str_contains($cuerpo, "'Error en el Request Method', 405"),
                $metodo.'() no valida el verbo '.$verbo.' con 405');
            $this->addCase($results, $metodo.'_verbo_antes_de_transaccion',
                $this->pos($cuerpo, "!== '".$verbo."'") < $this->pos($cuerpo, 'Base_transactionBegin()'),
                $metodo.'() debe validar el verbo antes de abrir la transaccion');
            $this->addCase($results, $metodo.'_tabla', str_contains($cuerpo, "'table'     => 'tokens_oauth_clientes'") || str_contains($cuerpo, "'table'       => 'tokens_oauth_clientes'"),
                $metodo.'() no apunta a tokens_oauth_clientes');
            $this->addCase($results, $metodo.'_where_pk', str_contains($cuerpo, "'where'     => 'idCliente'") || str_contains($cuerpo, "'where'       => 'idCliente'"),
                $metodo.'() debe operar por idCliente');
            $this->addCase($results, $metodo.'_begin', str_contains($cuerpo, 'Base_transactionBegin()'), $metodo.'() no abre transaccion');
            $this->addCase($results, $metodo.'_commit', str_contains($cuerpo, 'Base_transactionCommit()'), $metodo.'() no confirma la transaccion');
            $this->addCase($results, $metodo.'_rollback', str_contains($cuerpo, 'Base_transactionRollback()'), $metodo.'() no revierte ante error');
            $this->addCase($results, $metodo.'_commit_antes_exito',
                $this->pos($cuerpo, 'Base_transactionCommit()') !== false && $this->pos($cuerpo, 'Base_transactionCommit()') < $this->pos($cuerpo, 'Response::success('),
                $metodo.'(): el commit debe ocurrir antes de Response::success()');
            $this->addCase($results, $metodo.'_cascada_parametros', str_contains($cuerpo, "'params'    => [(int)"),
                $metodo.'(): la cascada debe usar parametros preparados');
            $this->addCase($results, $metodo.'_cascada_revierte',
                preg_match('/\$respCascada\[\'status\'\] === false\).*?Base_transactionRollback\(\).*?Response::error\(/s', $cuerpo) === 1,
                $metodo.'(): ante la falla de la cascada debe revertirse antes de responder');
            $r1 = $this->pos($cuerpo, 'DELETE FROM tokens_oauth_refresh WHERE idCliente = ?');
            $r2 = $this->pos($cuerpo, 'DELETE FROM tokens_oauth_codigos WHERE idCliente = ?');
            $r3 = $this->pos($cuerpo, "DELETE FROM tokens_listado WHERE idClienteOAuth = ? AND Origen = 2'");
            $this->addCase($results, $metodo.'_cascada_en_orden', $r1 !== false && $r2 !== false && $r3 !== false && $r1 < $r2 && $r2 < $r3,
                $metodo.'() debe borrar refresh, codigos y accesos hijos (Origen = 2) del cliente, en ese orden');
            $this->addCase($results, $metodo.'_cascada_en_transaccion',
                $r3 !== false && $this->pos($cuerpo, 'Base_transactionBegin()') < $r1 && $r3 < $this->pos($cuerpo, 'Base_transactionCommit()'),
                $metodo.'(): la cascada debe ocurrir entre Begin y Commit');
        }

        /*====================================*/
        // Update: solo el estado; el resto del POST (incluida la auditoria) se descarta
        /*====================================*/
        $upd = $this->cuerpoMetodo('Update');
        $this->addCase($results, 'update_reduce_post',
            str_contains($upd, "\$_POST = array_intersect_key(\$_POST, array_flip(['idCliente', 'idEstado']));"),
            'Update debe reducir $_POST a idCliente e idEstado');
        $this->addCase($results, 'update_reduce_antes_de_query',
            $this->pos($upd, 'array_intersect_key($_POST') !== false && $this->pos($upd, 'array_intersect_key($_POST') < $this->pos($upd, '$query = ['),
            'la reduccion del POST debe ir antes de armar la query');
        $this->addCase($results, 'update_campos_editables', str_contains($upd, "'data'      => 'idCliente,idEstado'") && str_contains($upd, "'required'  => 'idCliente,idEstado'"),
            'Update debe editar solo idCliente,idEstado y exigirlos');
        $this->addCase($results, 'update_sin_otros_campos',
            !str_contains($upd, 'SecretHash') && !str_contains($upd, 'RedirectUris') && !str_contains($upd, "'Nombre'") && !str_contains($upd, 'ClientId'),
            'Update no debe tocar secreto, URI, nombre ni client_id');
        $this->addCase($results, 'update_datacheck', str_contains($upd, '$this->dataCheck($_POST)'), 'Update debe pasar por dataCheck($_POST)');
        $this->addCase($results, 'update_cascada_si_desactiva', str_contains($upd, "if ((int) (\$_POST['idEstado'] ?? 0) !== 1)"),
            'Update debe lanzar la cascada solo cuando idEstado != 1');
        $this->addCase($results, 'update_cascada_tras_update', $this->pos($upd, 'Base_update(') < $this->pos($upd, 'DELETE FROM tokens_oauth_refresh'),
            'la cascada debe ejecutarse despues del UPDATE del estado');

        /*====================================*/
        // Delete: id descifrado del cuerpo; borrado del cliente antes de la cascada
        /*====================================*/
        $del = $this->cuerpoMetodo('Delete');
        $this->addCase($results, 'delete_descifra_id', str_contains($del, "encryptDecrypt('decrypt', \$dataDelete['idCliente'] ?? '')") && str_contains($del, "isValidDecrypted(\$ClienteID, 'id')"),
            'Delete debe descifrar y validar idCliente');
        $this->addCase($results, 'delete_id_antes_de_transaccion', $this->pos($del, "isValidDecrypted(\$ClienteID, 'id')") < $this->pos($del, 'Base_transactionBegin()'),
            'el id debe validarse antes de abrir la transaccion');
        $this->addCase($results, 'delete_borra_cliente', str_contains($del, '$this->Base_delete($xParams)') && str_contains($del, "'Post'        => \$dataDelete"),
            'Delete debe borrar el cliente con Base_delete');
        $this->addCase($results, 'delete_cascada_parametros_descifrados', str_contains($del, "'params'    => [(int) \$ClienteID['data']]"),
            'la cascada de Delete debe usar el id descifrado');

        return $results;

    }

    /**
     * Verifica contra las rutas del installer (`getRouteDefinitions(2)`): 7 rutas del CRUD Normal sin
     * `Insert`, con verbo, nivel y handler correctos, y el permiso de tipo Normal (`idTipo` 1).
     *
     * @return array Resultado por caso
     */
    public function controladorRutasTest(){

        $results = [];
        $rutas   = $this->rutasInstalador();
        $this->addCase($results, 'instalador_rutas_leidas', count($rutas) > 0, 'no se pudieron leer las rutas de '.self::INSTALADOR.'::getRouteDefinitions(2)');
        $this->addCase($results, 'rutas_total', count($rutas) === 7, 'el permiso 2 define '.count($rutas).' rutas (esperado 7 del CRUD Normal sin Insert)');

        // idMetodo: 1 GET, 2 POST, 3 DELETE
        $esperadas = [
            'listAll'    => ['1 '.self::RUTA_WEB.'/listAll',      'listAll',    1],
            'search'     => ['2 '.self::RUTA_WEB.'/search',       'UpdateList', 1],
            'updateList' => ['1 '.self::RUTA_WEB.'/updateList',   'UpdateList', 1],
            'view'       => ['1 '.self::RUTA_WEB.'/view/@id',     'View',       1],
            'getID'      => ['1 '.self::RUTA_WEB.'/getID/@id',    'GetID',      2],
            'update'     => ['2 '.self::RUTA_WEB.'/update',       'Update',     2],
            'delete'     => ['3 '.self::RUTA_WEB,                 'Delete',     4],
        ];
        foreach ($esperadas as $caso => [$clave, $accion, $nivel]) {
            $existe = isset($rutas[$clave]);
            $this->addCase($results, 'ruta_'.$caso, $existe, 'falta la ruta '.$clave);
            if (!$existe) {
                continue;
            }
            $this->addCase($results, 'ruta_'.$caso.'_handler', $rutas[$clave][0] === self::CONTROLADOR.'->'.$accion, 'la ruta '.$clave.' apunta a '.$rutas[$clave][0]);
            $this->addCase($results, 'ruta_'.$caso.'_nivel', $rutas[$clave][1] === $nivel, 'la ruta '.$clave.' exige nivel '.$rutas[$clave][1].' en vez de '.$nivel);
            $this->addCase($results, 'ruta_'.$caso.'_controller', $rutas[$clave][2] === self::CONTROLADOR, 'la ruta '.$clave.' queda asignada a '.$rutas[$clave][2]);
            $this->addCase($results, 'ruta_'.$caso.'_metodo_existe', method_exists(self::CONTROLADOR, $accion), 'el handler '.self::CONTROLADOR.'->'.$accion.' no existe');
        }
        // Sin Insert: no hay POST a la ruta base
        $this->addCase($results, 'sin_ruta_insert', !isset($rutas['2 '.self::RUTA_WEB]), 'no debe haber un POST a '.self::RUTA_WEB.' (no hay alta manual)');
        $conInsert = array_filter($rutas, static function ($r) { return stripos($r[0], '->Insert') !== false; });
        $this->addCase($results, 'sin_handler_insert', count($conInsert) === 0, 'ninguna ruta puede apuntar a un handler Insert');
        $escrituraBaja = array_filter($rutas, static function ($r, $k) { return $k[0] !== '1' && substr($k, -7) !== '/search' && $r[1] < 2; }, ARRAY_FILTER_USE_BOTH);
        $this->addCase($results, 'escritura_nivel_minimo_2', count($escrituraBaja) === 0, 'una ruta de escritura exige nivel 1');

        $inst = $this->codigoFuente($this->rutaModulo('../installer/'.self::INSTALADOR.'.php'));
        $this->addCase($results, 'permiso_tipo_normal',
            str_contains($inst, "'idTipo'         => '1',\n            'Nombre'         => 'Tokens - Clientes OAuth'") && str_contains($inst, "'RutaWeb'        => '".self::RUTA_WEB."'")
            && str_contains($inst, "'RutaController' => 'tokensOauthClientes'"),
            'el permiso Tokens - Clientes OAuth debe ser idTipo 1 con RutaWeb '.self::RUTA_WEB);
        $this->addCase($results, 'ruta_controller_declarado', str_contains($inst, "\$RutaController .= ',tokensOauthClientes';"),
            'RutaController() debe listar tokensOauthClientes (desinstalacion)');

        return $results;

    }

    /**
     * Verifica la sesion y el permiso del usuario de pruebas sobre la ruta del modulo y que el router de F3
     * no registre una ruta de alta.
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function permisosSesionTest($f3){

        $results = [];

        $UserData = $f3->get('SESSION.DataInfo');
        $this->addCase($results, 'sesion_usuario_presente', is_array($UserData) && !empty($UserData), 'no hay datos de usuario en la sesion');
        try {
            $arrLevel = $this->getArrLevel($f3, self::CONTROLADOR);
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
            'ruta_en_router'       => ['/'.self::RUTA_WEB.'/listAll', 'GET',  self::CONTROLADOR.'->listAll'],
            'ruta_busqueda_router' => ['/'.self::RUTA_WEB.'/search',  'POST', self::CONTROLADOR.'->UpdateList'],
            'ruta_getid_router'    => ['/'.self::RUTA_WEB.'/getID/@id', 'GET', self::CONTROLADOR.'->GetID'],
        ];
        foreach ($esquema as $caso => [$ruta, $verbo, $handlerEsperado]) {
            if (!is_array($arrRoutes) || !isset($arrRoutes[$ruta])) {
                continue;
            }
            $handler = $arrRoutes[$ruta][0][$verbo][0] ?? '';
            $this->addCase($results, $caso, $handler === $handlerEsperado, 'la ruta '.$ruta.' apunta a '.var_export($handler, true).' en vez de '.$handlerEsperado);
        }
        $this->addCase($results, 'router_sin_alta', !is_array($arrRoutes) || !isset($arrRoutes['/'.self::RUTA_WEB][0]['POST']),
            'el router no debe registrar un POST de alta en /'.self::RUTA_WEB);

        return $results;

    }

    /******************************************************************************/
    /*                              VALIDACION DE DATOS                            */
    /******************************************************************************/
    /**
     * Valida `dataCheck` por reflexion sin BD: `idCliente` e `idEstado` obligatorios y enteros.
     *
     * @return array Resultado por caso
     */
    public function chequeoDatosTest(){

        $results = [];
        try {
            $reflector = new ReflectionMethod(self::CONTROLADOR, 'dataCheck');
            $reflector->setAccessible(true);
            $Controller = (new ReflectionClass(self::CONTROLADOR))->newInstanceWithoutConstructor();
            $Post       = ['idCliente' => 5, 'idEstado' => 2];
            $DataCheck  = $reflector->invoke($Controller, $Post);

            $this->addCase($results, 'datacheck_es_arreglo', is_array($DataCheck), 'dataCheck() no devolvio un arreglo');
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post, 'dataCheck() no conserva el POST');
            $this->addCase($results, 'datacheck_obligatorios', (string)($DataCheck['emptyData'] ?? '') === 'idCliente,idEstado', 'idCliente e idEstado deben ser obligatorios');
            $this->addCase($results, 'datacheck_numeros', (string)($DataCheck['ValidarNumero'] ?? '') === 'idCliente,idEstado', 'idCliente e idEstado deben validarse como numeros');
            $this->addCase($results, 'datacheck_enteros', (string)($DataCheck['ValidarEntero'] ?? '') === 'idCliente,idEstado', 'idCliente e idEstado deben validarse como enteros');

            $checker = new CheckData();
            $bueno   = $checker->checkingData($reflector->invoke($Controller, ['idCliente' => 5, 'idEstado' => 1]));
            $sinEst  = $checker->checkingData($reflector->invoke($Controller, ['idCliente' => 5]));
            $sinId   = $checker->checkingData($reflector->invoke($Controller, ['idEstado' => 1]));
            $estTxt  = $checker->checkingData($reflector->invoke($Controller, ['idCliente' => 5, 'idEstado' => 'abc']));
            $idDec   = $checker->checkingData($reflector->invoke($Controller, ['idCliente' => '5.5', 'idEstado' => 1]));
            $this->addCase($results, 'chequeo_valido_aceptado', ($bueno['status'] ?? false) === true, 'idCliente e idEstado enteros deben aceptarse');
            $this->addCase($results, 'chequeo_sin_estado_rechazado', ($sinEst['status'] ?? true) === false, 'sin idEstado debe rechazarse');
            $this->addCase($results, 'chequeo_sin_id_rechazado', ($sinId['status'] ?? true) === false, 'sin idCliente debe rechazarse');
            $this->addCase($results, 'chequeo_estado_texto_rechazado', ($estTxt['status'] ?? true) === false, 'un idEstado no numerico debe rechazarse');
            $this->addCase($results, 'chequeo_id_decimal_rechazado', ($idDec['status'] ?? true) === false, 'un idCliente decimal debe rechazarse');
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_es_arreglo', false, 'no se pudo ejecutar dataCheck() ('.$e->getMessage().')');
        }

        return $results;

    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /**
     * Renderiza `-List` y `-UpdateList` con datos sinteticos: matriz de niveles (ver >=1, editar >=2,
     * borrar >=4, sin crear), aviso de clientes sin uso, tipo, "Sin uso" y escape del nombre.
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function vistaListadoTest($f3){

        $results = [];
        $BASE    = (string)$f3->get('BASE');
        $vista   = $this->rutaModulo('../views/'.self::CONTROLADOR);

        foreach (self::NIVELES as $nivel) {
            $html = $this->renderizarVista($vista.'-UpdateList.php', $this->datosSinteticos($nivel), $BASE, $f3);
            $this->addCase($results, 'updatelist_render_n'.$nivel, $html !== '', 'no se pudo renderizar -UpdateList con nivel '.$nivel);
            $this->addCase($results, 'updatelist_ver_n'.$nivel, substr_count($html, 'listTableDataView(') === ($nivel >= 1 ? 2 : 0), 'boton Ver incorrecto con nivel '.$nivel);
            $this->addCase($results, 'updatelist_editar_n'.$nivel, substr_count($html, 'listTableDataEdit(') === ($nivel >= 2 ? 2 : 0), 'boton Editar incorrecto con nivel '.$nivel);
            $this->addCase($results, 'updatelist_borrar_n'.$nivel, substr_count($html, 'listTableDataDel(') === ($nivel >= 4 ? 2 : 0), 'boton Borrar incorrecto con nivel '.$nivel);
        }
        $html = $this->renderizarVista($vista.'-UpdateList.php', $this->datosSinteticos(1), $BASE, $f3);
        foreach (['Nombre (declarado)', 'Client ID', 'Tipo', 'Estado', 'Accesos', 'Registro', 'Último uso'] as $col) {
            $this->addCase($results, 'updatelist_columna_'.md5($col), str_contains($html, '<th scope="col">'.$col.'</th>'), 'falta la columna '.$col);
        }
        $this->addCase($results, 'updatelist_tipo', str_contains($html, '>Público<') && str_contains($html, '>Confidencial<'), 'debe distinguir clientes Publico (none) y Confidencial');
        $this->addCase($results, 'updatelist_sin_uso', substr_count($html, 'Sin uso') === 1 && str_contains($html, '2026-01-03 10:00:00'), 'debe mostrar "Sin uso" solo para el cliente sin FechaUltimoUso');
        $this->addCase($results, 'updatelist_nombre_escapado', !str_contains($html, '<b>Alfa</b>') && str_contains($html, '&lt;b&gt;Alfa&lt;/b&gt;'), 'el nombre declarado por el cliente debe escaparse');
        $this->addCase($results, 'updatelist_id_cifrado', !str_contains($html, "listTableDataView('5')") && !str_contains($html, "listTableDataView('6')"), 'el id de la fila debe ir cifrado');
        $this->addCase($results, 'updatelist_sin_secreto', !str_contains($html, 'SecretHash') && !str_contains($html, 'IPClave'), 'la lista no debe mostrar el secreto ni IPClave');

        foreach ([1, 3] as $nivel) {
            $list = $this->renderizarVista($vista.'-List.php', $this->datosSinteticos($nivel), $BASE, $f3);
            $this->addCase($results, 'list_render_n'.$nivel, $list !== '', 'no se pudo renderizar -List con nivel '.$nivel);
            $this->addCase($results, 'list_sin_crear_n'.$nivel, !str_contains($list, 'newFormModal') && !str_contains($list, 'Crear Nuevo'), '-List no debe ofrecer "Crear Nuevo" (nivel '.$nivel.')');
            $this->addCase($results, 'list_aviso_sin_uso_n'.$nivel, str_contains($list, 'alert-warning') && str_contains($list, '2 clientes registrados que nunca obtuvieron acceso'),
                '-List debe avisar de los clientes sin uso');
            $this->addCase($results, 'list_borrar_pk_n'.$nivel, str_contains($list, '"idCliente": ID'), 'el borrado debe enviar idCliente');
        }
        $datos = $this->datosSinteticos(2);
        $datos['NSinUso'] = 0;
        $sin = $this->renderizarVista($vista.'-List.php', $datos, $BASE, $f3);
        $this->addCase($results, 'list_sin_aviso_si_cero', $sin !== '' && !str_contains($sin, 'nunca obtuvieron acceso'), 'sin clientes sin uso no debe mostrarse el aviso');

        return $results;

    }

    /**
     * Renderiza `-View`, `-formEdit` y `-formSearch`: solo lectura con todo escapado en `-View`; `-formEdit` con
     * el aviso, el selector de estado y el id oculto; `-formSearch` con Nombre, ClientId y estado.
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function vistasDetalleTest($f3){

        $results = [];
        $BASE    = (string)$f3->get('BASE');
        $vista   = $this->rutaModulo('../views/'.self::CONTROLADOR);
        $data    = $this->datosSinteticos(2);

        $view = $this->renderizarVista($vista.'-View.php', $data, $BASE, $f3);
        $this->addCase($results, 'view_render', $view !== '', 'no se pudo renderizar -View');
        $this->addCase($results, 'view_etiqueta_no_verificado', str_contains($view, 'Nombre (declarado, no verificado)'), '-View debe marcar el nombre como declarado, no verificado');
        $this->addCase($results, 'view_nombre_escapado', !str_contains($view, '<b>Alfa</b>') && str_contains($view, '&lt;b&gt;Alfa&lt;/b&gt;'), '-View debe escapar el nombre declarado');
        $this->addCase($results, 'view_uris_completas', str_contains($view, 'http://127.0.0.1:5555/cb') && str_contains($view, 'https://claude.ai/api/mcp/auth_callback'), '-View debe mostrar las URI registradas completas');
        $this->addCase($results, 'view_metadatos_como_texto', str_contains($view, 'client_uri') && str_contains($view, 'https://c.example/') && !str_contains($view, 'href="https://c.example/"') && !str_contains($view, '<img'),
            '-View debe mostrar client_uri y logo_uri como texto, sin enlaces ni imagenes');
        $this->addCase($results, 'view_metadato_array', str_contains($view, 'a, b'), '-View debe unir con coma los metadatos de tipo lista');
        $this->addCase($results, 'view_sin_uso', str_contains($view, 'Sin uso'), '-View debe mostrar "Sin uso" sin FechaUltimoUso');
        $this->addCase($results, 'view_sin_secreto', !str_contains($view, 'SecretHash') && !str_contains($view, 'client_secret'), '-View no debe mostrar el secreto');
        $this->addCase($results, 'view_solo_lectura', !str_contains($view, '<form') && !str_contains($view, '<input') && !str_contains($view, 'listTableData'), '-View no debe contener formularios ni acciones (ADR 0018)');

        $edit = $this->renderizarVista($vista.'-formEdit.php', $data, $BASE, $f3);
        $this->addCase($results, 'formedit_render', $edit !== '', 'no se pudo renderizar -formEdit');
        $this->addCase($results, 'formedit_campos', str_contains($edit, 'name="idEstado"') && str_contains($edit, 'name="idCliente"'), '-formEdit debe tener idEstado e idCliente (oculto)');
        $this->addCase($results, 'formedit_solo_estado', !str_contains($edit, 'name="Nombre"') && !str_contains($edit, 'name="RedirectUris"') && !str_contains($edit, 'name="ClientId"'),
            '-formEdit solo debe permitir editar el estado');
        $this->addCase($results, 'formedit_aviso_revoca', str_contains($edit, 'Desactivar revoca todos sus accesos'), '-formEdit debe avisar "Desactivar revoca todos sus accesos"');
        $this->addCase($results, 'formedit_endpoint', str_contains($edit, '/update'), '-formEdit debe enviar a /update');
        $this->addCase($results, 'formedit_nombre_escapado', !str_contains($edit, '<b>Alfa</b>') && str_contains($edit, '&lt;b&gt;Alfa&lt;/b&gt;'), '-formEdit debe escapar el nombre declarado');

        $search = $this->renderizarVista($vista.'-formSearch.php', $data, $BASE, $f3);
        $this->addCase($results, 'formsearch_render', $search !== '', 'no se pudo renderizar -formSearch');
        $this->addCase($results, 'formsearch_campos', str_contains($search, 'name="Nombre"') && str_contains($search, 'name="ClientId"') && str_contains($search, 'name="idEstado"'),
            '-formSearch debe tener Nombre, ClientId e idEstado');
        $this->addCase($results, 'formsearch_endpoint', str_contains($search, '/search'), '-formSearch debe enviar a /search');

        return $results;

    }

    /******************************************************************************/
    /*                                 SEGURIDAD                                  */
    /******************************************************************************/
    /**
     * Revisa estaticamente vistas y controlador: sin retroceso de rutas, sin rutas absolutas, sin sesion
     * directa, sin SQL embebido, consultas preparadas y ninguna lectura del hash del secreto.
     *
     * @return array Resultado por caso
     */
    public function seguridadVistasTest(){

        $results = [];
        $vistas  = glob($this->rutaModulo('../views/'.self::CONTROLADOR.'-*.php')) ?: [];
        $this->addCase($results, 'vistas_encontradas', count($vistas) === 5, 'se esperaban 5 vistas y hay '.count($vistas));

        foreach ($vistas as $ruta) {
            $nombre = basename($ruta, '.php');
            $src    = $this->codigoFuente($ruta);
            $this->addCase($results, 'vista_sin_retroceso_'.$nombre, !preg_match('/\.\.[\/\\\\]/', $src), $nombre.' contiene retroceso de ruta');
            $this->addCase($results, 'vista_sin_ruta_absoluta_'.$nombre, !preg_match('#(/var/www|/home/|[A-Z]:\\\\)#', $src), $nombre.' contiene ruta absoluta');
            $this->addCase($results, 'vista_sin_sesion_'.$nombre, !str_contains($src, '$_SESSION'), $nombre.' usa $_SESSION directamente');
            $this->addCase($results, 'vista_sin_sql_'.$nombre, !preg_match('/\b(SELECT\s.+\sFROM|INSERT\s+INTO|UPDATE\s+\w+\s+SET|DELETE\s+FROM)\b/i', $src), $nombre.' contiene SQL embebido');
            $this->addCase($results, 'vista_sin_secreto_'.$nombre, !str_contains($src, 'SecretHash') && !str_contains($src, 'client_secret'), $nombre.' hace referencia al secreto del cliente');
        }

        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'controlador_sin_concat_sql', !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $codigo), 'el controlador concatena entrada del usuario');
        $this->addCase($results, 'controlador_parametros_preparados', str_contains($codigo, "'params'"), 'el controlador debe usar parametros preparados');
        $this->addCase($results, 'controlador_sin_secreto', !str_contains($codigo, 'SecretHash'), 'el controlador no debe leer SecretHash');
        $this->addCase($results, 'controlador_sin_sesion_directa', !str_contains($codigo, '$_SESSION'), 'el controlador no debe leer $_SESSION directamente');

        return $results;

    }

}
