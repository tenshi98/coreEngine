<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class tokensListadoTesting
 *
 * Pruebas del modulo `tokens` - controlador `tokensListado` (CRUD Resumen, `idTipo` 2: `listAll`,
 * `UpdateList`, `View`, `Resumen`, `ResumenUpdate`, `Insert`, `Update`, `Delete`; sin `GetID` ni `-formEdit`).
 *
 * Alcance: pruebas NO destructivas. No se invocan `Insert()`, `Update()`, `Delete()`,
 * `InstallModule()` ni `UninstallModule()` (terminan en `Response::*` o escriben en BD).
 * Se verifican por reflexion, lectura del fuente, `dataCheck` sin BD y render de vistas
 * con datos sinteticos. La generacion del token se prueba con la funcion pura
 * `tokenBin2Hex(64)` (sin BD).
 *
 * @see skills/testing/SKILL.md
 */
class tokensListadoTesting extends ControllerBase {

    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR          = 'tokensListado';
    private const CONTROLADOR_PERMISOS = 'tokensListadoPermisos';
    private const INSTALADOR           = 'tokensInstaller';
    private const RUTA_WEB             = 'administracion/tokens/listado';
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
    // Fuente del controlador
    /*******************************************************************/
    private function fuenteControlador(): string{
        return $this->codigoFuente($this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'));
    }

    /*******************************************************************/
    // Cuerpo de un metodo del controlador (busqueda por llaves)
    /*******************************************************************/
    private function cuerpoMetodo(string $metodo): string{
        $fuente = $this->fuenteControlador();
        $ini    = strpos($fuente, 'function '.$metodo.'(');
        if ($ini === false) {
            return '';
        }
        $abre = strpos($fuente, '{', $ini);
        if ($abre === false) {
            return '';
        }
        $braces = 0;
        $len    = strlen($fuente);
        for ($i = $abre; $i < $len; $i++) {
            if ($fuente[$i] === '{') {
                $braces++;
            } elseif ($fuente[$i] === '}') {
                $braces--;
                if ($braces === 0) {
                    return substr($fuente, $ini, $i - $ini + 1);
                }
            }
        }
        return '';
    }

    /*******************************************************************/
    // Renderiza una vista con datos sinteticos y devuelve el HTML
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
    // Datos sinteticos usados por las vistas del modulo
    /*******************************************************************/
    private function datosSinteticos(int $nivel): array{
        return [
            'PageTitle'       => 'Tokens',
            'PageDescription' => 'Tokens.',
            'PageAuthor'      => 'Pruebas',
            'PageKeywords'    => 'Pruebas',
            'TableTitle'      => 'Tokens',
            'UserData'        => [
                'MainPathUrl'          => '/admin/public/upload/',
                'sistemaModalSubtitle' => 1,
                'sistemaModalCloseBTN' => 2,
                'UserType'             => 2,
            ],
            'UserAccess'      => ['LevelAccess' => $nivel, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_FormInputs'  => new UIFormInputs(),
            'Fnc_Codification' => new FunctionsSecurityCodification(),
            'Fnc_WidgetsCommon' => new UIWidgetsCommon(),
            'Fnc_WidgetsViews'  => new UIWidgetsViews(),
            'Fnc_CommonData'    => new FunctionsCommonData(),
            'arrList'         => [
                [
                    'idToken'        => 1,
                    'Nombre'         => 'Token Alpha',
                    'Token'          => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                    'FechaCreacion'  => '2026-01-02 10:00:00',
                    'FechaCaducidad' => '2026-01-10',
                    'idEstado'       => 1,
                    'Usuario'        => 'Tester',
                    'Estado'         => 'Activo',
                    'EstadoColor'    => 'success',
                    'Caducado'       => 1,
                ],
                [
                    'idToken'        => 2,
                    'Nombre'         => 'Token Beta',
                    'Token'          => 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb',
                    'FechaCreacion'  => '2026-01-03 10:00:00',
                    'FechaCaducidad' => null,
                    'idEstado'       => 1,
                    'Usuario'        => 'Tester',
                    'Estado'         => 'Activo',
                    'EstadoColor'    => 'success',
                    'Caducado'       => 0,
                ],
            ],
            'arrEstado'       => [['ID' => 1, 'Nombre' => 'Activo'], ['ID' => 2, 'Nombre' => 'Inactivo']],
            'arrUsuario'      => [['ID' => 1, 'Nombre' => 'Tester']],
            'rowData'         => [
                'idToken'        => 1,
                'Nombre'         => 'Token Alpha',
                'Token'          => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'FechaCreacion'  => '2026-01-02 10:00:00',
                'FechaCaducidad' => '2026-01-10',
                'idEstado'       => 1,
                'Usuario'        => 'Tester',
                'Estado'         => 'Activo',
                'EstadoColor'    => 'success',
                'Caducado'       => 1,
                'Origen'         => 1,
                'NModulos'       => 3,
                'NClientes'      => 2,
                'NAccesos'       => 1,
            ],
            'arrModulos'      => [
                ['Modulo' => 'modulo_uno', 'nombre' => 'Modulo Uno'],
                ['Modulo' => 'modulo_dos', 'nombre' => 'Modulo Dos'],
            ],
        ];
    }

    /*******************************************************************/
    // Rutas del permiso 1 del installer (getRouteDefinitions(1)):
    // [['metodo' => idMetodo, 'ruta' => RutaWeb, 'handler' => RutaController, 'nivel' => idLevelLimit, 'controller' => Controller]]
    /*******************************************************************/
    private function rutasInstalador(): array{
        $rutas = [];
        if (!class_exists(self::INSTALADOR)) {
            return $rutas;
        }
        try {
            $instalador = (new ReflectionClass(self::INSTALADOR))->newInstanceWithoutConstructor();
            foreach ($instalador->getRouteDefinitions(1) as $r) {
                $rutas[] = ['metodo' => (int)$r['idMetodo'], 'ruta' => $r['RutaWeb'], 'handler' => $r['RutaController'],
                            'nivel' => (int)$r['idLevelLimit'], 'controller' => $r['Controller']];
            }
        } catch (Throwable $e) {
            return [];
        }
        return $rutas;
    }

    /*******************************************************************/
    // Busca una ruta por verbo (1 GET, 2 POST, 3 DELETE), RutaWeb y controlador
    /*******************************************************************/
    private function buscarRuta(array $rutas, int $metodo, string $ruta, string $controller = self::CONTROLADOR): ?array{
        foreach ($rutas as $r) {
            if ($r['metodo'] === $metodo && $r['ruta'] === $ruta && $r['controller'] === $controller) {
                return $r;
            }
        }
        return null;
    }

    /*******************************************************************/
    // Posicion de una subcadena dentro del cuerpo (false si no esta)
    /*******************************************************************/
    private function pos(string $cuerpo, string $aguja){
        return strpos($cuerpo, $aguja);
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica `.htaccess` con `deny from all`, las 7 vistas del CRUD Resumen, la ausencia de `-formEdit`
     * y de la vista antigua de permisos, las clases y la herencia.
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

        // Las 7 vistas del tipo CRUD Resumen
        foreach (['List', 'UpdateList', 'View', 'formNew', 'formSearch', 'Resumen', 'Resumen-Update'] as $sufijo) {
            $this->addCase($results, 'vista_'.$sufijo, is_file($this->rutaModulo('../views/'.self::CONTROLADOR.'-'.$sufijo.'.php')),
                'falta la vista '.self::CONTROLADOR.'-'.$sufijo.'.php');
        }
        // Retiradas al pasar a CRUD Resumen: no existe -formEdit ni la vista antigua de permisos
        foreach (['formEdit', 'Permisos-UpdateList'] as $sufijo) {
            $this->addCase($results, 'vista_retirada_'.$sufijo, !is_file($this->rutaModulo('../views/'.self::CONTROLADOR.'-'.$sufijo.'.php')),
                'no debe existir la vista '.self::CONTROLADOR.'-'.$sufijo.'.php en el CRUD Resumen');
        }

        $this->addCase($results, 'clase_controlador', class_exists(self::CONTROLADOR),
            'la clase '.self::CONTROLADOR.' no se carga');
        $this->addCase($results, 'hereda_controllerbase', is_subclass_of(self::CONTROLADOR, 'ControllerBase'),
            self::CONTROLADOR.' no extiende ControllerBase');
        $this->addCase($results, 'clase_installer', class_exists(self::INSTALADOR),
            'la clase '.self::INSTALADOR.' no se carga');
        $this->addCase($results, 'hereda_controllerinstaller', is_subclass_of(self::INSTALADOR, 'ControllerInstaller'),
            self::INSTALADOR.' no extiende ControllerInstaller');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                       CONTROLADOR, RUTAS Y PERMISOS                         */
    /******************************************************************************/
    /**
     * Verifica las firmas de los 8 metodos publicos del CRUD Resumen (sin `GetID`), el contrato de
     * presentacion de las vistas (`showVista(1)` en `-List` y `-Resumen`, `showVista(2)` en el resto) y que
     * `-View` cumpla la norma de solo visualizacion (ADR 0018).
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];
        $this->firmaMetodo($results, 'listAll', 1, 1);
        $this->firmaMetodo($results, 'UpdateList', 1, 1);
        $this->firmaMetodo($results, 'View', 2, 2);
        $this->firmaMetodo($results, 'Resumen', 2, 2);
        $this->firmaMetodo($results, 'ResumenUpdate', 2, 2);
        $this->firmaMetodo($results, 'Insert', 1, 1);
        $this->firmaMetodo($results, 'Update', 0, 0);
        $this->firmaMetodo($results, 'Delete', 0, 0);
        $this->addCase($results, 'sin_metodo_GetID', !method_exists(self::CONTROLADOR, 'GetID'),
            self::CONTROLADOR.'::GetID() no debe existir en un CRUD Resumen (D-15)');

        $codigo = $this->fuenteControlador();
        $vistas = [
            'listAll'       => ['-List.php', 1],
            'UpdateList'    => ['-UpdateList.php', 2],
            'View'          => ['-View.php', 2],
            'Resumen'       => ['-Resumen.php', 1],
            'ResumenUpdate' => ['-Resumen-Update.php', 2],
        ];
        foreach ($vistas as $metodo => [$archivo, $tipoVista]) {
            $cuerpo = $this->cuerpoMetodo($metodo);
            $this->addCase($results, 'vista_'.$metodo,
                str_contains($cuerpo, "showVista(".$tipoVista.", \$this->returnRutaVista(__DIR__, 'app').'/'.\$this->controllerName.'".$archivo."')"),
                $metodo.'() debe cargar '.self::CONTROLADOR.$archivo.' con showVista('.$tipoVista.')');
            $this->addCase($results, 'errores_'.$metodo, str_contains($cuerpo, 'showError('), $metodo.'() no maneja errores con showError()');
            $this->addCase($results, 'usuario_'.$metodo, str_contains($cuerpo, 'getUserData($f3)') && str_contains($cuerpo, 'getArrLevel($f3, $this->controllerName)'),
                $metodo.'() no obtiene los datos del usuario y el nivel con controllerName');
        }
        $this->addCase($results, 'controller_name', str_contains($codigo, "\$this->controllerName = 'tokensListado';"),
            'controllerName debe ser tokensListado');
        $this->addCase($results, 'ruta_vista', str_contains($codigo, 'returnRutaVista('), 'no resuelve la ruta de vista');
        $this->addCase($results, 'sin_formedit', !str_contains($codigo, '-formEdit.php'), 'el controlador no debe cargar -formEdit.php');
        $this->addCase($results, 'tres_ids_cifrados', substr_count($codigo, "encryptDecrypt('decrypt', \$params['id'])") === 3,
            'View, Resumen y ResumenUpdate deben descifrar el id ('.substr_count($codigo, "encryptDecrypt('decrypt', \$params['id'])").' descifrados)');
        foreach (['View', 'Resumen', 'ResumenUpdate'] as $metodo) {
            $cuerpo = $this->cuerpoMetodo($metodo);
            $this->addCase($results, 'id_obligatorio_'.$metodo, str_contains($cuerpo, "!isset(\$params['id'])") && str_contains($cuerpo, "Response::error('Registro ID inexistente', 400)"),
                $metodo.'() debe responder 400 si falta el id');
            $this->addCase($results, 'id_valido_'.$metodo, str_contains($cuerpo, "isValidDecrypted(\$TokenID, 'id')"),
                $metodo.'() debe validar el id descifrado');
            $this->addCase($results, 'fila_vacia_400_'.$metodo, str_contains($cuerpo, "empty(\$rowData['data'])") && str_contains($cuerpo, "Response::error('Registro inválido', 400)"),
                $metodo.'() debe responder 400 con una fila vacia (id inexistente o de un token hijo)');
        }

        /*====================================*/
        // Lectura: solo tokens manuales (Origen = 1)
        /*====================================*/
        $lista = $this->cuerpoMetodo('getDataList');
        $this->addCase($results, 'getdatalist_filtra_origen',
            str_contains($lista, "'where'   => 'tokens_listado.Origen = ?'") && str_contains($lista, 'array_merge([1], $params)'),
            'getDataList() debe filtrar tokens_listado.Origen = ? con parametro 1');
        $detalle = $this->cuerpoMetodo('getDataDetail');
        $this->addCase($results, 'getdatadetail_filtra_origen',
            str_contains($detalle, "'where'   => 'tokens_listado.idToken = ? AND tokens_listado.Origen = ?'") && str_contains($detalle, '[$ID, 1]'),
            'getDataDetail() debe filtrar idToken y Origen = 1 con parametros preparados');
        $this->addCase($results, 'getdatadetail_conteos',
            str_contains($detalle, 'AS NModulos') && str_contains($detalle, 'AS NClientes') && str_contains($detalle, 'AS NAccesos'),
            'getDataDetail() debe calcular NModulos, NClientes y NAccesos');
        $campos = $this->cuerpoMetodo('getDataFields');
        $this->addCase($results, 'getdatafields_origen', str_contains($campos, 'tokens_listado.Origen'), 'getDataFields() debe incluir Origen');

        /*====================================*/
        // ADR 0018: -View solo muestra datos
        /*====================================*/
        $view = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'));
        $this->addCase($results, 'view_fuente_leido', $view !== '', 'no se pudo leer '.self::CONTROLADOR.'-View.php');
        $this->addCase($results, 'view_adr0018_comando',
            preg_match("/<form|LevelAccess'\\]\\s*>=\\s*[234]|listTableDataEdit|listTableDataDel|_Add\\(|_Edit\\(|_Del\\(|bi-pencil|bi-trash/", $view) === 0,
            '-View incumple el comando de verificacion de la ADR 0018 (formulario, botones de edicion o borrado, LevelAccess >= 2)');
        $this->addCase($results, 'view_adr0018_sin_escritura',
            preg_match('/<input|<select|<textarea|Fnc_FormInputs|SendDataForms|UpdateContentId/', $view) === 0,
            '-View no debe contener campos, Fnc_FormInputs, SendDataForms ni cargas AJAX de fragmentos editables (ADR 0018)');

        return $results;

    }

    /**
     * Revisa el contrato de escritura sin ejecutar: verbo HTTP, transaccion, `dataCheck`, tabla y PK,
     * descarte de los campos de auditoria OAuth, guarda `Origen` y cascada OAuth al desactivar o borrar.
     *
     * @return array Resultado por caso
     */
    public function controladorEscrituraTest(){

        $results = [];
        $esperado = ['Insert' => 'POST', 'Update' => 'POST', 'Delete' => 'DELETE'];
        foreach ($esperado as $metodo => $verbo) {
            $cuerpo = $this->cuerpoMetodo($metodo);
            $this->addCase($results, $metodo.'_existe', $cuerpo !== '', $metodo.'() no encontrado');
            if ($cuerpo === '') {
                continue;
            }
            $this->addCase($results, $metodo.'_verbo', str_contains($cuerpo, "!== '".$verbo."'"), $metodo.'() no valida el verbo '.$verbo);
            $this->addCase($results, $metodo.'_tabla', str_contains($cuerpo, "'table'     => 'tokens_listado'") || str_contains($cuerpo, "'table'       => 'tokens_listado'"), $metodo.'() no apunta a tokens_listado');
            $this->addCase($results, $metodo.'_begin', str_contains($cuerpo, 'Base_transactionBegin()'), $metodo.'() no abre transaccion');
            $this->addCase($results, $metodo.'_commit', str_contains($cuerpo, 'Base_transactionCommit()'), $metodo.'() no confirma la transaccion');
            $this->addCase($results, $metodo.'_rollback', str_contains($cuerpo, 'Base_transactionRollback()'), $metodo.'() no revierte ante error');
            $this->addCase($results, $metodo.'_commit_antes_exito',
                strpos($cuerpo, 'Base_transactionCommit()') !== false && strpos($cuerpo, 'Response::success(') !== false
                && strpos($cuerpo, 'Base_transactionCommit()') < strpos($cuerpo, 'Response::success('),
                $metodo.'(): el commit debe ocurrir antes de Response::success()');
            $this->addCase($results, $metodo.'_verbo_antes_de_transaccion',
                $this->pos($cuerpo, "!== '".$verbo."'") !== false && $this->pos($cuerpo, "!== '".$verbo."'") < $this->pos($cuerpo, 'Base_transactionBegin()'),
                $metodo.'() debe validar el verbo antes de abrir la transaccion');
        }

        /*====================================*/
        // Campos de auditoria OAuth: los fija el servidor (Insert y Update los descartan con unset)
        /*====================================*/
        foreach (['Insert', 'Update'] as $metodo) {
            $cuerpo = $this->cuerpoMetodo($metodo);
            $this->addCase($results, $metodo.'_unset_auditoria',
                str_contains($cuerpo, "unset(\$_POST['Origen'], \$_POST['idTokenPadre'], \$_POST['idClienteOAuth'], \$_POST['FechaExpiracion'])"),
                $metodo.'() debe descartar Origen, idTokenPadre, idClienteOAuth y FechaExpiracion del POST');
            $this->addCase($results, $metodo.'_unset_antes_de_query',
                $this->pos($cuerpo, 'unset($_POST[') !== false && $this->pos($cuerpo, 'unset($_POST[') < $this->pos($cuerpo, '$query = ['),
                $metodo.'(): el unset de los campos de auditoria debe ir antes de armar la query');
        }

        /*====================================*/
        // Update: nombre, estado y caducidad; Token, creador y fecha NO se editan; guarda Origen = 1
        /*====================================*/
        $upd = $this->cuerpoMetodo('Update');
        $this->addCase($results, 'update_campos_editables', str_contains($upd, "'data'      => 'idToken,Nombre,idEstado,FechaCaducidad'"),
            'Update debe editar solo idToken,Nombre,idEstado,FechaCaducidad');
        $this->addCase($results, 'update_sin_token_ni_creador', !str_contains($upd, "'Token'") && !str_contains($upd, 'idUsuario') && !str_contains($upd, 'FechaCreacion'),
            'Update no debe tocar Token, idUsuario ni FechaCreacion');
        $this->addCase($results, 'update_pk_requerida', str_contains($upd, "'required'  => 'idToken,Nombre,idEstado'"),
            'Update debe exigir idToken, Nombre e idEstado');
        $this->addCase($results, 'update_guarda_origen',
            str_contains($upd, "'where'   => 'idToken = ? AND Origen = ?'") && str_contains($upd, "[(int) (\$_POST['idToken'] ?? 0), 1]"),
            'Update debe comprobar con parametros que el token existe y tiene Origen = 1');
        $this->addCase($results, 'update_guarda_400',
            str_contains($upd, 'empty($ResponseGuarda[\'data\'])') && str_contains($upd, "Response::error('Registro inválido', 400)"),
            'Update debe responder 400 si la guarda no devuelve fila');
        $this->addCase($results, 'update_guarda_antes_de_guardar',
            $this->pos($upd, "'where'   => 'idToken = ? AND Origen = ?'") !== false && $this->pos($upd, "'where'   => 'idToken = ? AND Origen = ?'") < $this->pos($upd, "'data'      => 'idToken,Nombre,idEstado,FechaCaducidad'"),
            'la guarda Origen debe ejecutarse antes del UPDATE');

        /*====================================*/
        // Update: cascada al desactivar (idEstado != 1): refresh, accesos hijos y codigos, en ese orden
        /*====================================*/
        $this->addCase($results, 'update_cascada_si_desactiva', str_contains($upd, "if ((int) (\$_POST['idEstado'] ?? 0) !== 1)"),
            'Update debe lanzar la cascada solo cuando idEstado != 1');
        $c1 = $this->pos($upd, 'DELETE FROM tokens_oauth_refresh WHERE idTokenAcceso IN (SELECT idToken FROM tokens_listado WHERE idTokenPadre = ? AND Origen = 2)');
        $c2 = $this->pos($upd, 'DELETE FROM tokens_listado WHERE idTokenPadre = ? AND Origen = 2');
        $c3 = $this->pos($upd, 'DELETE FROM tokens_oauth_codigos WHERE idToken = ?');
        $this->addCase($results, 'update_cascada_tres_consultas_en_orden', $c1 !== false && $c2 !== false && $c3 !== false && $c1 < $c2 && $c2 < $c3,
            'la cascada de Update debe borrar refresh, accesos hijos y codigos, en ese orden');
        $this->addCase($results, 'update_cascada_parametros', str_contains($upd, "'params'    => [(int) \$_POST['idToken']]"),
            'la cascada de Update debe usar parametros preparados con idToken');
        $this->addCase($results, 'update_cascada_antes_commit',
            $c3 !== false && $c3 < $this->pos($upd, 'Base_transactionCommit()'),
            'la cascada debe ejecutarse dentro de la transaccion (antes del commit)');
        $this->addCase($results, 'update_cascada_revierte',
            preg_match('/\$respCascada\[\'status\'\] === false\).*?Base_transactionRollback\(\).*?Response::error\(/s', $upd) === 1,
            'ante la falla de la cascada debe revertirse la transaccion antes de responder');

        /*====================================*/
        // Delete: guarda Origen = 1, borrado en cascada de permisos y de lo OAuth
        /*====================================*/
        $del = $this->cuerpoMetodo('Delete');
        $this->addCase($results, 'delete_where_pk', str_contains($del, "'where'       => 'idToken'"), 'Delete debe borrar por idToken');
        $this->addCase($results, 'delete_guarda_origen',
            str_contains($del, "'where'   => 'idToken = ? AND Origen = ?'") && str_contains($del, '[(int) $TokenID[\'data\'], 1]'),
            'Delete debe comprobar con parametros que el token tiene Origen = 1');
        $this->addCase($results, 'delete_guarda_400', str_contains($del, 'empty($ResponseGuarda[\'data\'])') && str_contains($del, "Response::error('Registro inválido', 400)"),
            'Delete debe responder 400 si la guarda no devuelve fila');
        $this->addCase($results, 'delete_permisos', str_contains($del, "['files' => '', 'table' => 'tokens_listado_permisos']"),
            'Delete debe borrar las filas de tokens_listado_permisos');
        $d1 = $this->pos($del, 'DELETE FROM tokens_oauth_refresh WHERE idTokenAcceso IN (SELECT idToken FROM tokens_listado WHERE idTokenPadre = ? AND Origen = 2)');
        $d2 = $this->pos($del, 'DELETE FROM tokens_listado WHERE idTokenPadre = ? AND Origen = 2');
        $d3 = $this->pos($del, 'DELETE FROM tokens_oauth_codigos WHERE idToken = ?');
        $this->addCase($results, 'delete_cascada_oauth_en_orden', $d1 !== false && $d2 !== false && $d3 !== false && $d1 < $d2 && $d2 < $d3,
            'Delete debe borrar refresh, accesos hijos y codigos OAuth, en ese orden');
        $this->addCase($results, 'delete_cascada_en_transaccion',
            $d3 !== false && $this->pos($del, 'Base_transactionBegin()') < $d1 && $d3 < $this->pos($del, 'Base_transactionCommit()'),
            'la cascada OAuth de Delete debe ocurrir entre Begin y Commit');
        $this->addCase($results, 'delete_cascada_parametros', str_contains($del, "'params'    => [(int) \$TokenID['data']]"),
            'la cascada de Delete debe usar parametros preparados con el id descifrado');

        return $results;

    }

    /**
     * Verifica contra las rutas del installer (`getRouteDefinitions(1)`): 15 rutas del permiso (9 de
     * `tokensListado` y 6 de sus subcontroladores), sin `getID`, con verbo, nivel y handler correctos.
     *
     * @return array Resultado por caso
     */
    public function controladorRutasTest(){

        $results = [];
        $rutas   = $this->rutasInstalador();
        $this->addCase($results, 'rutas_leidas', count($rutas) > 0, 'no se pudieron leer las rutas de '.self::INSTALADOR.'::getRouteDefinitions(1)');
        $this->addCase($results, 'rutas_total', count($rutas) === 15, 'el permiso 1 define '.count($rutas).' rutas (esperado 15)');

        $porControlador = [];
        foreach ($rutas as $r) {
            $porControlador[$r['controller']] = ($porControlador[$r['controller']] ?? 0) + 1;
        }
        $this->addCase($results, 'rutas_controladores',
            array_keys($porControlador) === [self::CONTROLADOR, self::CONTROLADOR_PERMISOS, 'tokensListadoOauthClientes', 'tokensListadoOauthAccesos'],
            'controladores esperados tokensListado y sus 3 subcontroladores; hay '.implode(',', array_keys($porControlador)));
        $this->addCase($results, 'rutas_total_listado', ($porControlador[self::CONTROLADOR] ?? 0) === 9,
            self::CONTROLADOR.' tiene '.($porControlador[self::CONTROLADOR] ?? 0).' rutas (esperado 9 del CRUD Resumen)');

        $coherentes = true;
        foreach ($rutas as $r) {
            if (explode('->', $r['handler'])[0] !== $r['controller']) {
                $coherentes = false;
            }
        }
        $this->addCase($results, 'rutas_handler_coincide_controller', $rutas !== [] && $coherentes,
            'algun RutaController no pertenece a la clase declarada en Controller');

        /*====================================*/
        // Las 9 rutas del CRUD Resumen: [verbo, ruta, handler, nivel]
        /*====================================*/
        $esperadas = [
            'listAll'       => [1, self::RUTA_WEB.'/listAll',                 'listAll',       1],
            'search'        => [2, self::RUTA_WEB.'/search',                  'UpdateList',    1],
            'updateList'    => [1, self::RUTA_WEB.'/updateList',              'UpdateList',    1],
            'view'          => [1, self::RUTA_WEB.'/view/@id',                'View',          1],
            'resumen'       => [1, self::RUTA_WEB.'/resumen/@id',             'Resumen',       2],
            'resumenUpdate' => [1, self::RUTA_WEB.'/resumenUpdate/@id',       'ResumenUpdate', 2],
            'insert'        => [2, self::RUTA_WEB,                            'Insert',        3],
            'update'        => [2, self::RUTA_WEB.'/update',                  'Update',        2],
            'delete'        => [3, self::RUTA_WEB,                            'Delete',        4],
        ];
        foreach ($esperadas as $caso => [$metodo, $ruta, $accion, $nivel]) {
            $r = $this->buscarRuta($rutas, $metodo, $ruta);
            $this->addCase($results, 'ruta_'.$caso, $r !== null, 'falta la ruta (idMetodo '.$metodo.') '.$ruta);
            if ($r === null) {
                continue;
            }
            $this->addCase($results, 'ruta_'.$caso.'_handler', $r['handler'] === self::CONTROLADOR.'->'.$accion,
                'la ruta '.$ruta.' apunta a '.$r['handler'].' en vez de '.self::CONTROLADOR.'->'.$accion);
            $this->addCase($results, 'ruta_'.$caso.'_nivel', $r['nivel'] === $nivel,
                'la ruta '.$ruta.' exige nivel '.$r['nivel'].' en vez de '.$nivel);
            $this->addCase($results, 'ruta_'.$caso.'_metodo_existe', method_exists(self::CONTROLADOR, $accion),
                'el handler '.self::CONTROLADOR.'->'.$accion.' no existe');
        }

        /*====================================*/
        // Sin getID (D-15) y sin rutas de nivel 1 que escriban
        /*====================================*/
        $conGetId = array_filter($rutas, static function ($r) { return stripos($r['ruta'], 'getid') !== false && $r['controller'] === 'tokensListado'; });
        $this->addCase($results, 'sin_ruta_getID', count($conGetId) === 0, 'tokensListado no debe publicar una ruta getID (D-15)');
        // search (POST) es un filtro de solo lectura: el unico POST de nivel 1 permitido
        $escrituraBaja = array_filter($rutas, static function ($r) { return $r['controller'] === 'tokensListado' && $r['metodo'] !== 1 && $r['handler'] !== 'tokensListado->UpdateList' && $r['nivel'] < 2; });
        $this->addCase($results, 'escritura_nivel_minimo_2', count($escrituraBaja) === 0, 'una ruta de escritura de tokensListado exige nivel 1');

        /*====================================*/
        // Installer: permiso tipo Resumen (idTipo 2), controladores y optimizaciones
        /*====================================*/
        $inst = $this->codigoFuente($this->rutaModulo('../installer/tokensInstaller.php'));
        $this->addCase($results, 'permiso_tipo_resumen',
            str_contains($inst, "'idTipo'         => '2',\n            'Nombre'         => 'Tokens - Listado'") && str_contains($inst, "'RutaWeb'        => '".self::RUTA_WEB."'"),
            'el permiso Tokens - Listado debe ser idTipo 2 (CRUD Resumen) con RutaWeb '.self::RUTA_WEB);
        $this->addCase($results, 'ruta_controller_declarado', str_contains($inst, "\$RutaController  = 'tokensListado';"),
            'RutaController() debe listar tokensListado');
        foreach ([self::CONTROLADOR_PERMISOS, 'tokensListadoOauthClientes', 'tokensListadoOauthAccesos', 'tokensOauthClientes'] as $sub) {
            $this->addCase($results, 'ruta_controller_declarado_'.$sub, str_contains($inst, "\$RutaController .= ',".$sub."';"),
                'RutaController() debe listar tambien '.$sub);
        }
        $this->addCase($results, 'optimiza_idtipo_2',
            str_contains($inst, "UPDATE core_permisos_listado SET idTipo = 2 WHERE RutaWeb = \\'administracion/tokens/listado\\' AND RutaController = \\'tokensListado\\';"),
            'optimizeTables debe pasar a idTipo 2 los permisos ya instalados');
        $this->addCase($results, 'optimiza_borra_getid',
            str_contains($inst, "DELETE FROM core_permisos_listado_rutas WHERE RutaController = \\'tokensListado->GetID\\';"),
            'optimizeTables debe borrar la ruta antigua tokensListado->GetID');
        $this->addCase($results, 'columna_nombre_ddl', str_contains($inst, '`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`Token`'),
            'listTables debe declarar Nombre varchar(255) NOT NULL');
        $this->addCase($results, 'columna_nombre_idempotente', str_contains($inst, 'ADD COLUMN IF NOT EXISTS Nombre varchar(255)') && str_contains($inst, 'AFTER idToken'),
            'optimizeTables debe agregar Nombre con ADD COLUMN IF NOT EXISTS (instalaciones previas)');
        $this->addCase($results, 'columnas_oauth_ddl',
            str_contains($inst, '`Origen` int(10) unsigned NOT NULL DEFAULT 1,`idTokenPadre`') && str_contains($inst, '`idClienteOAuth` int(10) unsigned NULL DEFAULT NULL,`FechaExpiracion` datetime NULL DEFAULT NULL'),
            'listTables debe declarar Origen (DEFAULT 1), idTokenPadre, idClienteOAuth y FechaExpiracion');
        $this->addCase($results, 'indices_unicos', str_contains($inst, 'uk_tokens_Token (Token)') && str_contains($inst, 'IF NOT EXISTS'),
            'falta el indice unico de Token con IF NOT EXISTS');
        $this->addCase($results, 'indice_origen_fecha', str_contains($inst, 'idx_tokens_origen_fecha (Origen, FechaCreacion)'),
            'falta el indice idx_tokens_origen_fecha (Origen, FechaCreacion)');

        return $results;

    }

    /**
     * Verifica la sesion y el permiso del usuario de pruebas sobre la ruta del modulo.
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function permisosSesionTest($f3){

        $results = [];

        $UserData = $f3->get('SESSION.DataInfo');
        $this->addCase($results, 'sesion_usuario_presente', is_array($UserData) && !empty($UserData),
            'no hay datos de usuario en la sesion');

        try {
            $arrLevel = $this->getArrLevel($f3, self::CONTROLADOR);
            $this->addCase($results, 'get_arr_level_arreglo', is_array($arrLevel), 'getArrLevel() no devolvio un arreglo');
        } catch (Throwable $e) {
            $this->addCase($results, 'get_arr_level_arreglo', false, 'no se pudo leer la sesion ('.$e->getMessage().')');
            $arrLevel = [];
        }
        $nivel = is_array($arrLevel) ? ($arrLevel['LevelAccess'] ?? null) : null;
        $this->addCase($results, 'nivel_en_rango', $nivel === null || in_array((int)$nivel, self::NIVELES, true),
            'LevelAccess quedo fuera del catalogo 1..4');
        $this->addCase($results, 'ruta_de_acceso_coincide', $nivel === null || ($arrLevel['RouteAccess'] ?? '') === self::RUTA_WEB,
            'RouteAccess no coincide con '.self::RUTA_WEB);

        $arrRoutes = $f3->get('ROUTES');
        $this->addCase($results, 'router_disponible', is_array($arrRoutes), 'F3 no expuso la variable ROUTES');
        $esquema = [
            'ruta_en_router'        => ['/'.self::RUTA_WEB.'/listAll',          'GET',  self::CONTROLADOR.'->listAll'],
            'ruta_busqueda_router'  => ['/'.self::RUTA_WEB.'/search',           'POST', self::CONTROLADOR.'->UpdateList'],
            'ruta_resumen_router'   => ['/'.self::RUTA_WEB.'/resumen/@id',      'GET',  self::CONTROLADOR.'->Resumen'],
            'ruta_resumenupdate_router' => ['/'.self::RUTA_WEB.'/resumenUpdate/@id', 'GET', self::CONTROLADOR.'->ResumenUpdate'],
        ];
        foreach ($esquema as $caso => [$ruta, $verbo, $handlerEsperado]) {
            if (!is_array($arrRoutes) || !isset($arrRoutes[$ruta])) {
                continue;
            }
            $handler = $arrRoutes[$ruta][0][$verbo][0] ?? '';
            $this->addCase($results, $caso, $handler === $handlerEsperado,
                'la ruta '.$ruta.' apunta a '.var_export($handler, true).' en vez de '.$handlerEsperado);
        }
        // La ruta getID ya no se registra nunca
        $this->addCase($results, 'ruta_getid_no_registrada', !is_array($arrRoutes) || !isset($arrRoutes['/'.self::RUTA_WEB.'/getID/@id']),
            'el router no debe registrar /'.self::RUTA_WEB.'/getID/@id');

        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA DEL TOKEN                               */
    /******************************************************************************/
    /**
     * Verifica que `Insert` genere el token en el servidor (64 hex, unico), fije el creador
     * desde la sesion y la fecha, devuelva solo el id cifrado y no registre el token. No ejecuta `Insert`.
     *
     * @return array Resultado por caso
     */
    public function insertTokenServidorTest(){

        $results = [];

        /*====================================*/
        // Generador: 64 caracteres hexadecimales y distintos entre llamadas
        /*====================================*/
        $gen = new FunctionsSecurityPasswords();
        $a   = $gen->tokenBin2Hex(64);
        $b   = $gen->tokenBin2Hex(64);
        $this->addCase($results, 'generador_ok', ($a['success'] ?? false) === true && ($b['success'] ?? false) === true,
            'tokenBin2Hex(64) no devolvio success');
        $ta = (string)($a['data'] ?? '');
        $tb = (string)($b['data'] ?? '');
        $this->addCase($results, 'token_64_hex', preg_match('/^[0-9a-f]{64}$/', $ta) === 1,
            'el token generado no tiene 64 caracteres hexadecimales');
        $this->addCase($results, 'token_distinto', $ta !== '' && $ta !== $tb, 'dos tokens consecutivos coinciden');

        /*====================================*/
        // Insert (fuente): campos fijados por el servidor
        /*====================================*/
        $ins = $this->cuerpoMetodo('Insert');
        $this->addCase($results, 'insert_usuario_sesion', str_contains($ins, "\$_POST['idUsuario']") && str_contains($ins, "SESSION.DataInfo.UserID"),
            'Insert debe fijar idUsuario desde SESSION.DataInfo.UserID');
        $this->addCase($results, 'insert_usuario_antes_query',
            strpos($ins, "\$_POST['idUsuario']") !== false && strpos($ins, "\$_POST['idUsuario']") < strpos($ins, '$query = ['),
            'idUsuario debe fijarse antes de armar la query (el valor del POST se sobrescribe)');
        $this->addCase($results, 'insert_fecha_servidor', str_contains($ins, "\$_POST['FechaCreacion'] = date('Y-m-d H:i:s')"),
            'Insert debe fijar FechaCreacion con date()');
        $this->addCase($results, 'insert_token_generador', str_contains($ins, 'tokenBin2Hex(64)') && str_contains($ins, "\$_POST['Token']"),
            'Insert debe generar el Token con tokenBin2Hex(64)');
        $this->addCase($results, 'insert_error_generador', str_contains($ins, "'No se pudo generar el token', 500"),
            'Insert debe responder 500 si falla el generador');
        $this->addCase($results, 'insert_nombre_obligatorio',
            str_contains($ins, "'data'      => 'idUsuario,Nombre,Token,FechaCreacion,idEstado,FechaCaducidad'") && str_contains($ins, "'required'  => 'idUsuario,Nombre,Token,FechaCreacion,idEstado'"),
            'Insert debe incluir Nombre en data y required');
        $this->addCase($results, 'insert_unique_token', str_contains($ins, "'unique'    => 'Token'"),
            'Insert debe declarar Token como unico');
        // CRUD Resumen: la respuesta es el id del registro cifrado (no el token)
        $this->addCase($results, 'insert_devuelve_id_cifrado',
            str_contains($ins, "encryptDecrypt('encrypt', \$Response['data'])") && str_contains($ins, 'Response::success($DataID[\'data\'])'),
            'Insert debe devolver el id insertado cifrado ($DataID[data])');
        $this->addCase($results, 'insert_valida_id_cifrado', str_contains($ins, "isValidDecrypted(\$DataID, 'text')"),
            'Insert debe validar el id cifrado antes de responder');
        $this->addCase($results, 'insert_sin_token_en_respuesta',
            !preg_match("/Response::(success|error)\\([^;]*\\\$_POST\\['Token'\\]/", $ins)
            && !preg_match('/(error_log|Log::|logger|->log\\()[^;]*Token/i', $ins),
            'Insert no debe devolver ni registrar el token');

        /*====================================*/
        // Lectura: etiqueta Caducado calculada en la consulta
        /*====================================*/
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'consulta_caducado',
            str_contains($codigo, 'tokens_listado.FechaCaducidad < CURDATE()') && str_contains($codigo, 'AS Caducado'),
            'la consulta debe calcular Caducado con FechaCaducidad < CURDATE()');

        return $results;

    }

    /**
     * Valida `dataCheck` por reflexion sin BD: estado obligatorio y entero, fecha Y-m-d, nombre 3..255.
     *
     * @return array Resultado por caso
     */
    public function chequeoDatosTest(){

        $results = [];
        try {
            $reflector = new ReflectionMethod(self::CONTROLADOR, 'dataCheck');
            $reflector->setAccessible(true);
            $Controller = (new ReflectionClass(self::CONTROLADOR))->newInstanceWithoutConstructor();
            $Post       = ['idEstado' => 1, 'FechaCaducidad' => '2026-12-31'];
            $DataCheck  = $reflector->invoke($Controller, $Post);

            $this->addCase($results, 'datacheck_es_arreglo', is_array($DataCheck), 'dataCheck() no devolvio un arreglo');
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post, 'dataCheck() no conserva el POST');
            $this->addCase($results, 'datacheck_estado_obligatorio', (string)($DataCheck['emptyData'] ?? '') === 'idEstado',
                'idEstado debe ser el unico campo obligatorio');
            $this->addCase($results, 'datacheck_estado_entero', str_contains((string)($DataCheck['ValidarEntero'] ?? ''), 'idEstado'),
                'idEstado debe validarse como entero');
            $this->addCase($results, 'datacheck_fecha_formato', ($DataCheck['ValidarFechaFormato'] ?? []) === ['FechaCaducidad' => 'Y-m-d'],
                'FechaCaducidad debe validarse con formato Y-m-d');

            $this->addCase($results, 'datacheck_nombre_largo_minimo',
                (string)($DataCheck['ValidarLargoMinimo'] ?? '') === 'Nombre' && (int)($DataCheck['ValidarLargoMinimoN'] ?? 0) === 3,
                'Nombre debe validarse con largo minimo 3');
            $this->addCase($results, 'datacheck_nombre_largo_maximo', ($DataCheck['ValidarLargoMaximoPorCampo'] ?? []) === ['Nombre' => 255],
                'Nombre debe validarse con largo maximo 255');
            $this->addCase($results, 'datacheck_nombre_censuradas', (string)($DataCheck['ValidarPalabrasCensuradas'] ?? '') === 'Nombre',
                'Nombre debe validarse contra palabras censuradas');

            // Comportamiento real de CheckData (sin BD): fecha mala rechazada, fecha buena y vacia aceptadas
            $checker = new CheckData();
            $malo  = $checker->checkingData($reflector->invoke($Controller, ['idEstado' => 1, 'FechaCaducidad' => '31/12/2026']));
            $bueno = $checker->checkingData($reflector->invoke($Controller, ['idEstado' => 1, 'FechaCaducidad' => '2026-12-31']));
            $vacio = $checker->checkingData($reflector->invoke($Controller, ['idEstado' => 1, 'FechaCaducidad' => '']));
            $sinEs = $checker->checkingData($reflector->invoke($Controller, ['FechaCaducidad' => '2026-12-31']));
            $nCorto = $checker->checkingData($reflector->invoke($Controller, ['Nombre' => 'ab', 'idEstado' => 1]));
            $nMin   = $checker->checkingData($reflector->invoke($Controller, ['Nombre' => 'abc', 'idEstado' => 1]));
            $nMax   = $checker->checkingData($reflector->invoke($Controller, ['Nombre' => str_repeat('a', 255), 'idEstado' => 1]));
            $nLargo = $checker->checkingData($reflector->invoke($Controller, ['Nombre' => str_repeat('a', 256), 'idEstado' => 1]));
            $this->addCase($results, 'nombre_corto_rechazado', ($nCorto['status'] ?? true) === false, 'un Nombre de 2 caracteres debe rechazarse');
            $this->addCase($results, 'nombre_3_aceptado', ($nMin['status'] ?? false) === true, 'un Nombre de 3 caracteres debe aceptarse');
            $this->addCase($results, 'nombre_255_aceptado', ($nMax['status'] ?? false) === true, 'un Nombre de 255 caracteres debe aceptarse');
            $this->addCase($results, 'nombre_256_rechazado', ($nLargo['status'] ?? true) === false, 'un Nombre de 256 caracteres debe rechazarse');
            $this->addCase($results, 'fecha_invalida_rechazada', ($malo['status'] ?? true) === false, 'una fecha con formato distinto de Y-m-d debe rechazarse');
            $this->addCase($results, 'fecha_valida_aceptada', ($bueno['status'] ?? false) === true, 'una fecha Y-m-d valida debe aceptarse');
            $this->addCase($results, 'fecha_vacia_aceptada', ($vacio['status'] ?? false) === true, 'la caducidad vacia debe aceptarse (opcional)');
            $this->addCase($results, 'estado_vacio_rechazado', ($sinEs['status'] ?? true) === false, 'sin idEstado debe rechazarse');
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_es_arreglo', false, 'no se pudo ejecutar dataCheck() ('.$e->getMessage().')');
        }

        return $results;

    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /**
     * Renderiza `-List` y `-UpdateList` con datos sinteticos: matriz de niveles (ver >=1, editar >=2 como
     * enlace a `/resumen/`, crear >=3, borrar >=4), etiqueta "Caducado" y token no listado.
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function vistaListadoTest($f3){

        $results = [];
        $BASE    = (string)$f3->get('BASE');
        $vista   = $this->rutaModulo('../views/'.self::CONTROLADOR);

        foreach (self::NIVELES as $nivel) {
            $data = $this->datosSinteticos($nivel);
            $html = $this->renderizarVista($vista.'-UpdateList.php', $data, $BASE, $f3);
            $this->addCase($results, 'updatelist_render_n'.$nivel, $html !== '', 'no se pudo renderizar -UpdateList con nivel '.$nivel);
            $this->addCase($results, 'updatelist_ver_n'.$nivel, str_contains($html, 'listTableDataView(') === ($nivel >= 1), 'boton Ver incorrecto con nivel '.$nivel);
            $this->addCase($results, 'updatelist_editar_enlace_resumen_n'.$nivel, str_contains($html, '/'.self::RUTA_WEB.'/resumen/') === ($nivel >= 2),
                'el enlace de edicion a /resumen/ es incorrecto con nivel '.$nivel);
            $this->addCase($results, 'updatelist_sin_listTableDataEdit_n'.$nivel, !str_contains($html, 'listTableDataEdit('),
                'un CRUD Resumen no usa listTableDataEdit (la edicion es el enlace a /resumen/)');
            $this->addCase($results, 'updatelist_borrar_n'.$nivel, str_contains($html, 'listTableDataDel(') === ($nivel >= 4), 'boton Borrar incorrecto con nivel '.$nivel);
            if ($nivel === 1) {
                $this->addCase($results, 'updatelist_nombre', str_contains($html, '<th scope="col">Nombre</th>') && str_contains($html, 'Token Alpha'), 'la lista debe mostrar la columna y el valor de Nombre');
                $this->addCase($results, 'updatelist_sin_token', !str_contains($html, str_repeat('a', 64)) && !str_contains($html, str_repeat('b', 64)),
                    'la lista no debe mostrar el valor del token');
                $this->addCase($results, 'updatelist_caducado_una_vez', substr_count($html, '>Caducado<') === 1, 'solo la fila con Caducado=1 debe mostrar la etiqueta');
                $this->addCase($results, 'updatelist_id_cifrado', !str_contains($html, "listTableDataView('1')") && !str_contains($html, '/resumen/1"'), 'el id de la fila debe ir cifrado');
            }
        }

        foreach ([2, 3] as $nivel) {
            $html = $this->renderizarVista($vista.'-List.php', $this->datosSinteticos($nivel), $BASE, $f3);
            $this->addCase($results, 'list_render_n'.$nivel, $html !== '', 'no se pudo renderizar -List con nivel '.$nivel);
            $this->addCase($results, 'list_crear_n'.$nivel, str_contains($html, 'data-bs-target="#newFormModal"') === ($nivel >= 3), 'boton Crear incorrecto con nivel '.$nivel);
            $this->addCase($results, 'list_borrar_pk_n'.$nivel, str_contains($html, '"idToken": ID'), 'el borrado debe enviar idToken');
        }

        return $results;

    }

    /**
     * Renderiza `-View`, `-formNew`, `-formSearch`, `-Resumen` y `-Resumen-Update`: campos, endpoints,
     * pestanas por nivel, token solo como texto y ausencia de `-formEdit`.
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function vistasFormulariosTest($f3){

        $results = [];
        $BASE    = (string)$f3->get('BASE');
        $vista   = $this->rutaModulo('../views/'.self::CONTROLADOR);
        $data    = $this->datosSinteticos(3);

        $view = $this->renderizarVista($vista.'-View.php', $data, $BASE, $f3);
        $this->addCase($results, 'view_render', $view !== '', 'no se pudo renderizar -View');
        $this->addCase($results, 'view_nombre', str_contains($view, 'Token Alpha'), '-View debe mostrar el Nombre');
        $this->addCase($results, 'view_token_texto', str_contains($view, str_repeat('a', 64)), '-View debe mostrar el token como texto');
        $this->addCase($results, 'view_caducado', str_contains($view, '>Caducado<'), '-View debe marcar Caducado');
        $this->addCase($results, 'view_permisos', str_contains($view, 'Modulo Uno') && str_contains($view, 'Modulo Dos') && !str_contains($view, 'Categoria') && !str_contains($view, 'Nivel'),
            '-View debe listar los modulos asignados (sin categoria ni nivel)');
        $this->addCase($results, 'view_solo_lectura', !str_contains($view, '<form') && !str_contains($view, '<input') && !str_contains($view, 'listTableData'),
            '-View no debe contener formularios, campos ni acciones (ADR 0018)');

        $new = $this->renderizarVista($vista.'-formNew.php', $data, $BASE, $f3);
        $this->addCase($results, 'formnew_render', $new !== '', 'no se pudo renderizar -formNew');
        $this->addCase($results, 'formnew_campos', str_contains($new, 'name="Nombre"') && str_contains($new, 'name="idEstado"') && str_contains($new, 'name="FechaCaducidad"'), '-formNew debe tener Nombre, idEstado y FechaCaducidad');
        $this->addCase($results, 'formnew_sin_token_ni_creador',
            !str_contains($new, 'name="Token"') && !str_contains($new, 'name="idUsuario"') && !str_contains($new, 'name="FechaCreacion"')
            && !str_contains($new, 'name="Origen"') && !str_contains($new, 'name="idTokenPadre"'),
            '-formNew no debe pedir Token, creador, fecha de creacion ni campos de auditoria OAuth');
        $this->addCase($results, 'formnew_post', str_contains($new, "'POST'") && str_contains($new, self::RUTA_WEB), '-formNew debe enviar POST a la ruta base');

        $search = $this->renderizarVista($vista.'-formSearch.php', $data, $BASE, $f3);
        $this->addCase($results, 'formsearch_render', $search !== '', 'no se pudo renderizar -formSearch');
        $this->addCase($results, 'formsearch_campos', str_contains($search, 'name="Nombre"') && str_contains($search, 'name="idEstado"') && str_contains($search, 'name="idUsuario"'),
            '-formSearch debe tener Nombre, idEstado e idUsuario');
        $this->addCase($results, 'formsearch_sin_token', !str_contains($search, 'name="Token"'), '-formSearch no debe filtrar por el valor del token');
        $this->addCase($results, 'formsearch_endpoint', str_contains($search, '/search'), '-formSearch debe enviar a /search');

        /*====================================*/
        // -Resumen: pestanas Resumen y Editar siempre; Permisos, Clientes y Accesos solo con nivel >= 2
        /*====================================*/
        foreach ([1, 2, 4] as $nivel) {
            $res = $this->renderizarVista($vista.'-Resumen.php', $this->datosSinteticos($nivel), $BASE, $f3);
            $this->addCase($results, 'resumen_render_n'.$nivel, $res !== '', 'no se pudo renderizar -Resumen con nivel '.$nivel);
            $this->addCase($results, 'resumen_pestanas_base_n'.$nivel, str_contains($res, 'data-bs-target="#resumen"') && str_contains($res, 'data-bs-target="#resumen-edit"'),
                '-Resumen debe tener las pestanas Resumen y Editar Datos con nivel '.$nivel);
            foreach (['permisos' => 'tabPermisosLoad()', 'oauth-clientes' => 'tabClientesLoad()', 'oauth-accesos' => 'tabAccesosLoad()'] as $pestana => $fn) {
                $this->addCase($results, 'resumen_pestana_'.$pestana.'_n'.$nivel,
                    str_contains($res, 'data-bs-target="#resumen-'.$pestana.'"') === ($nivel >= 2) && str_contains($res, 'onclick="'.$fn.'"') === ($nivel >= 2),
                    'la pestana '.$pestana.' debe '.($nivel >= 2 ? 'mostrarse' : 'ocultarse').' con nivel '.$nivel);
            }
            if ($nivel === 2) {
                $this->addCase($results, 'resumen_formedit_campos',
                    str_contains($res, 'name="Nombre"') && str_contains($res, 'Token Alpha') && str_contains($res, 'name="idEstado"') && str_contains($res, 'name="FechaCaducidad"') && str_contains($res, 'name="idToken"'),
                    '-Resumen debe tener el formulario con Nombre (con valor), idToken (oculto), idEstado y FechaCaducidad');
                // -Resumen incluye -Resumen-Update con require_once (el texto del token se prueba al renderizar esa vista)
                $this->addCase($results, 'resumen_token_no_editable',
                    !str_contains($res, 'name="Token"') && str_contains($this->codigoFuente($vista.'-Resumen.php'), "require_once('tokensListado-Resumen-Update.php')"),
                    '-Resumen no debe pedir el token como input y debe incluir -Resumen-Update (que lo muestra como texto)');
                $this->addCase($results, 'resumen_endpoint_update', str_contains($res, '/update'), '-Resumen debe enviar el formulario a /update');
                $this->addCase($results, 'resumen_refresca_resumenupdate', str_contains($res, '/resumenUpdate/'), '-Resumen debe refrescar #resumen desde /resumenUpdate/');
                $this->addCase($results, 'resumen_urls_pestanas',
                    str_contains($res, '/permisos/updateList/') && str_contains($res, '/oauthClientes/updateList/') && str_contains($res, '/oauthAccesos/updateList/')
                    && str_contains($res, '/oauthClientes/revocar') && str_contains($res, '/oauthAccesos'),
                    '-Resumen debe cargar las pestanas desde permisos, oauthClientes y oauthAccesos (updateList, revocar y borrado)');
            }
        }

        $upd = $this->renderizarVista($vista.'-Resumen-Update.php', $data, $BASE, $f3);
        $this->addCase($results, 'resumenupdate_render', $upd !== '', 'no se pudo renderizar -Resumen-Update');
        $this->addCase($results, 'resumenupdate_datos', str_contains($upd, 'Token Alpha') && str_contains($upd, str_repeat('a', 64)) && str_contains($upd, 'Manual'),
            '-Resumen-Update debe mostrar el nombre, el token como texto y el origen Manual');
        $this->addCase($results, 'resumenupdate_caducado', str_contains($upd, '>Caducado<'), '-Resumen-Update debe marcar Caducado');
        $this->addCase($results, 'resumenupdate_conteos', str_contains($upd, 'Módulos') && str_contains($upd, 'Clientes OAuth') && str_contains($upd, 'Accesos vigentes'),
            '-Resumen-Update debe mostrar los conteos de módulos, clientes OAuth y accesos vigentes');
        $this->addCase($results, 'resumenupdate_sin_formulario', !str_contains($upd, '<form') && !str_contains($upd, '<input'),
            '-Resumen-Update es solo visualizacion: no debe tener formularios');

        return $results;

    }

    /******************************************************************************/
    /*                                 SEGURIDAD                                  */
    /******************************************************************************/
    /**
     * Revisa estaticamente vistas y controlador: sin retroceso de rutas, sin rutas
     * absolutas, sin sesion directa, sin SQL embebido, token escapado y consultas preparadas.
     *
     * @return array Resultado por caso
     */
    public function seguridadVistasTest(){

        $results = [];
        $vistas  = glob($this->rutaModulo('../views/'.self::CONTROLADOR.'-*.php')) ?: [];
        // 7 vistas del CRUD Resumen + 3 fragmentos de sub-entidad (permisos, clientes OAuth, accesos OAuth)
        $this->addCase($results, 'vistas_encontradas', count($vistas) === 10, 'se esperaban 10 vistas tokensListado-* y hay '.count($vistas));

        foreach ($vistas as $ruta) {
            $nombre = basename($ruta, '.php');
            $src    = $this->codigoFuente($ruta);
            $this->addCase($results, 'vista_sin_retroceso_'.$nombre, !preg_match('/\.\.[\/\\\\]/', $src), $nombre.' contiene retroceso de ruta');
            $this->addCase($results, 'vista_sin_ruta_absoluta_'.$nombre, !preg_match('#(/var/www|/home/|[A-Z]:\\\\)#', $src), $nombre.' contiene ruta absoluta');
            $this->addCase($results, 'vista_sin_sesion_'.$nombre, !str_contains($src, '$_SESSION'), $nombre.' usa $_SESSION directamente');
            $this->addCase($results, 'vista_sin_sql_'.$nombre, !preg_match('/\b(SELECT\s.+\sFROM|INSERT\s+INTO|UPDATE\s+\w+\s+SET|DELETE\s+FROM)\b/i', $src), $nombre.' contiene SQL embebido');
        }

        foreach (['-View', '-Resumen-Update'] as $sufijo) {
            $src = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.$sufijo.'.php'));
            $this->addCase($results, 'token_escapado'.$sufijo, str_contains($src, "htmlspecialchars((string)(\$data['rowData']['Token']"),
                $sufijo.' debe escapar el token con htmlspecialchars');
        }
        $lista = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'));
        $this->addCase($results, 'updatelist_no_lee_token', !str_contains($lista, "['Token']"), '-UpdateList no debe leer el valor del token');

        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'controlador_sin_concat_sql', !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $codigo), 'el controlador concatena entrada del usuario');
        $this->addCase($results, 'controlador_parametros_preparados', str_contains($codigo, "'params'"), 'el controlador debe usar parametros preparados');
        $this->addCase($results, 'controlador_sin_sesion_directa', !str_contains($codigo, '$_SESSION'), 'el controlador no debe leer $_SESSION directamente');

        return $results;

    }

}
