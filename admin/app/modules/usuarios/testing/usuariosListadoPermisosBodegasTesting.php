<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class usuariosListadoPermisosBodegasTesting
 *
 * Pruebas del modulo `usuarios` - controlador `usuariosListadoPermisosBodegas` (asignacion de
 * las bodegas a las que puede acceder un usuario).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. El normalizador
 *    `sistemaTesteos::normalizeResult()` marca EXITOSA o ERROR segun el contenido, por lo que
 *    los textos de los casos correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Alcance: pruebas NO destructivas. `Update()` abre una transaccion, borra/inserta permisos y
 * termina en `Response::success()`/`Response::error()` (que ejecutan `exit`), por eso NO se
 * invoca: se verifica por reflexion y por lectura del fuente. El controlador no tiene vistas
 * propias: su unica pantalla es `usuariosListado-Resumen-PermisosBodegas-List.php`, que se
 * renderiza con datos sinteticos.
 *
 * `seguridadControladorTest` agrupa las comprobaciones sobre defectos de seguridad conocidos del
 * controlador; si el controlador se corrige, sus casos pasan a OK sin tocar la prueba.
 *
 * @see skills/testing/SKILL.md
 */
class usuariosListadoPermisosBodegasTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR          = 'usuariosListadoPermisosBodegas';
    private const CONTROLADOR_PERMISOS = 'usuariosListado';
    private const INSTALADOR           = 'usuariosInstaller';
    private const RUTA_WEB             = 'administracion/usuarios';
    private const RUTA_WEB_UPDATE      = 'administracion/usuarios/bodegas/update';
    private const VISTA                = 'usuariosListado-Resumen-PermisosBodegas-List';
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
    // Ruta de la vista del controlador
    /*******************************************************************/
    private function rutaVista(): string{
        return $this->rutaModulo('../views/'.self::VISTA.'.php');
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
    // Fragmento (<tr>) de la tabla que contiene a un recurso
    /*******************************************************************/
    private function filaRecurso(string $html, int $id): string{
        foreach (explode('<tr', $html) as $trozo) {
            if (str_contains($trozo, 'name="switch_'.$id.'"')) {
                return $trozo;
            }
        }
        return '';
    }

    /*******************************************************************/
    // Datos sinteticos usados por la vista del controlador.
    // Bodega 1: con acceso. Bodega 2: sin acceso. Bodega 3: con acceso.
    /*******************************************************************/
    private function datosSinteticos(int $nivel = 2): array{
        return [
            'UserData'        => [
                'MainPathUrl'              => '/admin/public/upload/',
                'sistemaModalSubtitle'     => 1,
                'sistemaModalCloseBTN'     => 2,
                'usuariosPermisosBodegas'  => 2,
                'usuariosPermisosMaquinas' => 1,
                'UserType'                 => 2,
            ],
            'UserAccess'      => ['LevelAccess' => $nivel, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_FormInputs'  => new UIFormInputs(),
            'rowData'         => ['idUsuario' => 7, 'Nombre' => 'Usuario Prueba'],
            'arrPermisosBodegas' => [
                ['idBodegas' => 1, 'ID' => 1, 'Nombre' => 'Bodega Central', 'cuentaPerms' => 1],
                ['idBodegas' => 2, 'ID' => 2, 'Nombre' => 'Bodega Norte',   'cuentaPerms' => 0],
                ['idBodegas' => 3, 'ID' => 3, 'Nombre' => 'Bodega Sur',     'cuentaPerms' => 1],
            ],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica los archivos del controlador `usuariosListadoPermisosBodegas` (controlador,
     * su unica vista y testing), la proteccion de acceso directo y la convencion "clase =
     * nombre de archivo" exigida por el AUTOLOAD de Fat-Free.
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
            'vista_permisos' => $this->rutaVista(),
            'testing'     => $this->rutaModulo('usuariosListadoPermisosBodegasTesting.php'),
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
        $this->addCase($results, 'clase_testing_cargada', class_exists('usuariosListadoPermisosBodegasTesting'),
            'la clase usuariosListadoPermisosBodegasTesting no esta disponible');
        $padre = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padre === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padre.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('usuariosListadoPermisosBodegasTesting') === 'ControllerBase',
            'el testing no extiende ControllerBase');

        /*====================================*/
        // Constructor: conexion MySQL_1, cifrado y fecha del servidor
        /*====================================*/
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'constructor_conexion', str_contains($codigo, 'Database::getSQLConnection(ConfigDataBase::MySQL_1)'),
            'el constructor no abre la conexion MySQL_1');
        $this->addCase($results, 'constructor_padre', str_contains($codigo, 'parent::__construct($DB_conn_1, $queryBuilder, $checkData)'),
            'el constructor no delega en ControllerBase');
        $this->addCase($results, 'constructor_instancias',
            str_contains($codigo, 'new FunctionsSecurityCodification()') && str_contains($codigo, 'new FunctionsServerServer()'),
            'el constructor no instancia el cifrado ni la fecha del servidor');

        /*====================================*/
        // El controlador solo expone escritura: no tiene metodos de vista propios
        /*====================================*/
        $publicos = [];
        if (class_exists(self::CONTROLADOR)) {
            foreach ((new ReflectionClass(self::CONTROLADOR))->getMethods(ReflectionMethod::IS_PUBLIC) as $m) {
                if ($m->class === self::CONTROLADOR && $m->getName() !== '__construct') {
                    $publicos[] = $m->getName();
                }
            }
        }
        $this->addCase($results, 'solo_metodo_update', $publicos === ['Update'],
            'se esperaba solo Update() como metodo publico y hay: '.implode(', ', $publicos));

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                         CONTROLADOR: ESCRITURA (CRUD)                      */
    /******************************************************************************/
    /**
     * Verifica el contrato de `Update()` SIN ejecutarlo: abre una transaccion, quita o
     * agrega el acceso de un usuario a cada bodega y termina en `Response::*()` (que hace
     * `exit`). Se comprueba firma, verbo HTTP, catalogo de bodegas activas, rama de cada
     * estado (inactivo/activo x con/sin acceso previo) y transaccion completa.
     *
     * @return array Resultado por caso
     */
    public function controladorEscrituraTest(){

        $results = [];

        $this->addCase($results, 'metodo_Update', method_exists(self::CONTROLADOR, 'Update'),
            'no existe '.self::CONTROLADOR.'::Update()');
        if (method_exists(self::CONTROLADOR, 'Update')) {
            $r = new ReflectionMethod(self::CONTROLADOR, 'Update');
            $this->addCase($results, 'metodo_Update_publico', $r->isPublic(), 'Update() no es publico');
            $this->addCase($results, 'metodo_Update_parametros', $r->getNumberOfParameters() === 0,
                'Update() declara '.$r->getNumberOfParameters().' parametros (esperado 0)');
        }

        $update = $this->cuerpoMetodo('Update');

        /*====================================*/
        // Verbo HTTP, antes de cualquier consulta o transaccion
        /*====================================*/
        $this->addCase($results, 'update_verbo_post', str_contains($update, "REQUEST_METHOD'] !== 'POST'") && str_contains($update, '405'),
            'Update() no valida el verbo HTTP POST con 405');
        $this->addCase($results, 'update_verbo_antes_de_consultar',
            strpos($update, "REQUEST_METHOD'] !== 'POST'") < strpos($update, '$query = ['),
            'Update() debe validar el verbo antes de armar cualquier consulta');

        /*====================================*/
        // Catalogo de bodegas: solo las activas, limitado por N_MaxItems
        /*====================================*/
        $this->addCase($results, 'catalogo_tabla', str_contains($update, "'table'   => 'bodegas_listado'"),
            'Update() no parte del catalogo bodegas_listado');
        $this->addCase($results, 'catalogo_solo_activas',
            str_contains($update, "'where'   => 'idEstado = ?'") && str_contains($update, "'params'  => [1]"),
            'Update() debe considerar solo bodegas activas con parametros preparados');
        $this->addCase($results, 'catalogo_limite', str_contains($update, 'N_MaxItems'),
            'Update() no limita el catalogo con N_MaxItems');
        $this->addCase($results, 'catalogo_acceso_previo',
            str_contains($update, 'FROM bodegas_listado_permisos_usuarios WHERE idBodegas = ID AND idUsuario ='),
            'Update() no cuenta el acceso previo del usuario para decidir entre insertar y borrar');

        /*====================================*/
        // Transaccion completa: Begin -> operaciones -> Commit, con rollback por falla
        /*====================================*/
        $this->addCase($results, 'transaccion_completa',
            str_contains($update, 'Base_transactionBegin()') && str_contains($update, 'Base_transactionCommit()')
            && str_contains($update, 'failOperation('),
            'Update() debe usar Begin/Commit y failOperation (rollback gestionado)');
        $this->addCase($results, 'transaccion_begin_antes_de_escribir',
            strpos($update, 'Base_transactionBegin()') < strpos($update, 'Base_delete(')
            && strpos($update, 'Base_transactionBegin()') < strpos($update, 'Base_insertMultiple('),
            'Update() debe abrir la transaccion antes de borrar o insertar');
        $this->addCase($results, 'transaccion_commit_al_final',
            strpos($update, 'Base_transactionCommit()') > strpos($update, 'Base_insertMultiple(')
            && strpos($update, 'Base_transactionCommit()') > strpos($update, 'Base_delete('),
            'Update() debe confirmar la transaccion despues de todas las escrituras');
        $this->addCase($results, 'transaccion_falla_por_operacion',
            substr_count($update, 'failOperation(') === 2,
            'Update() debe revertir ante la falla de cada operacion (borrar, insertar): hay '.substr_count($update, 'failOperation(').' puntos de reversion');

        /*====================================*/
        // Ramas por estado del interruptor (1=inactivo, 2=activo) y acceso previo
        /*====================================*/
        $this->addCase($results, 'rama_inactivo', str_contains($update, 'case 1:'),
            'Update() no contempla el estado inactivo (switch=1)');
        $this->addCase($results, 'rama_activo', str_contains($update, 'case 2:'),
            'Update() no contempla el estado activo (switch=2)');
        $this->addCase($results, 'rama_sin_acceso_previo', substr_count($update, 'case 0:') === 2,
            'Update() debe tratar el caso sin acceso previo tanto al desactivar como al activar');
        $this->addCase($results, 'borrado_solo_si_existia',
            preg_match('/case 1:.*?case 0:.*?\/\/ Nada.*?default:.*?Base_delete\(/s', $update) === 1,
            'Update() solo debe borrar al desactivar cuando existia un acceso previo');
        $this->addCase($results, 'borrado_acotado',
            str_contains($update, "'where'       => 'idUsuario,idBodegas'") && str_contains($update, "'table'       => 'bodegas_listado_permisos_usuarios'"),
            'el borrado debe acotarse por idUsuario e idBodegas en bodegas_listado_permisos_usuarios');
        $this->addCase($results, 'borrado_sin_archivos', str_contains($update, "'files'       => ''"),
            'el borrado de accesos no debe tocar archivos');
        $this->addCase($results, 'sin_actualizacion', !str_contains($update, 'Base_update('),
            'el acceso a una bodega es binario: no debe haber una rama de actualizacion');
        $this->addCase($results, 'activo_existente_no_escribe',
            preg_match('/case 2:\s*\/\/ Verifico si existe\s*switch \(\$permisos\[\'cuentaPerms\'\]\) \{\s*\/\*+\/\s*\/\/ Si no hay permisos se crea\s*case 0:/s', $update) === 1
            && substr_count($update, 'default:') === 1,
            'activar una bodega ya asignada no debe escribir nada');
        $this->addCase($results, 'insercion_en_lote',
            str_contains($update, '$rowsPermisosNuevos[]') && substr_count($update, 'Base_insertMultiple(') === 1,
            'los accesos nuevos deben acumularse e insertarse en una sola sentencia');
        $this->addCase($results, 'insercion_solo_si_hay_filas', str_contains($update, 'if ($rowsPermisosNuevos)'),
            'no debe insertarse cuando no hay accesos nuevos');
        $this->addCase($results, 'insercion_campos',
            str_contains($update, "'data'      => 'idUsuario,idBodegas,fechaCreacion'")
            && str_contains($update, "'required'  => 'idUsuario,idBodegas'")
            && str_contains($update, "'table'     => 'bodegas_listado_permisos_usuarios'"),
            'la insercion no declara idUsuario, idBodegas y fechaCreacion sobre bodegas_listado_permisos_usuarios');
        $this->addCase($results, 'bodega_desde_catalogo',
            str_contains($update, "'idBodegas'     => \$permisos['idBodegas']"),
            'la bodega insertada debe salir del catalogo consultado y no del cliente');
        $this->addCase($results, 'fecha_del_servidor', substr_count($update, '$this->ServerServer->fechaActual()') === 1,
            'la fecha de asignacion debe venir del servidor');

        /*====================================*/
        // Rama de borrado: IDs pasados por cifrado/validacion y falla -> 400
        /*====================================*/
        $this->addCase($results, 'borrado_valida_ids',
            substr_count($update, "Response::error('Registro inválido', 400)") === 2
            && str_contains($update, "isValidDecrypted(\$UsuarioID, 'text')") && str_contains($update, "isValidDecrypted(\$BodegasID, 'text')"),
            'el borrado debe validar los IDs con isValidDecrypted y responder 400');

        /*====================================*/
        // Respuesta
        /*====================================*/
        $this->addCase($results, 'respuesta_ok', str_contains($update, 'Response::success(true)'),
            'Update() no responde con Response::success(true)');
        $this->addCase($results, 'respuesta_al_final',
            strpos($update, 'Response::success(true)') > strpos($update, 'Base_transactionCommit()'),
            'Update() debe responder solo despues de confirmar la transaccion');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                       CONTROLADOR: RUTAS E INSTALADOR                      */
    /******************************************************************************/
    /**
     * Verifica que la ruta de `usuariosListadoPermisosBodegas` publicada por el instalador
     * apunte a `Update`, por POST y con nivel 2, y que el controlador quede incluido en
     * la desinstalacion.
     *
     * @return array Resultado por caso
     */
    public function controladorRutasTest(){

        $results = [];
        $rutas   = $this->rutasInstalador();
        $this->addCase($results, 'instalador_rutas_disponibles', count($rutas) > 0,
            'getRouteDefinitions() no devolvio rutas');

        /*====================================*/
        // idMetodo: 2=POST. [clave, handler, nivel]
        /*====================================*/
        $clave   = '2 '.self::RUTA_WEB_UPDATE;
        $existe  = isset($rutas[$clave]);
        $this->addCase($results, 'ruta_update', $existe, 'el instalador no publica la ruta '.$clave);
        if ($existe) {
            $this->addCase($results, 'ruta_update_handler', strcasecmp($rutas[$clave][0], self::CONTROLADOR.'->Update') === 0,
                'la ruta '.$clave.' apunta a '.$rutas[$clave][0].' en vez de '.self::CONTROLADOR.'->Update');
            $this->addCase($results, 'ruta_update_nivel', $rutas[$clave][1] === 2,
                'la ruta '.$clave.' exige nivel '.$rutas[$clave][1].' en vez de 2');
            $this->addCase($results, 'ruta_update_controller', $rutas[$clave][2] === self::CONTROLADOR,
                'la ruta '.$clave.' queda asignada a '.$rutas[$clave][2]);
            $partes = explode('->', $rutas[$clave][0]);
            $this->addCase($results, 'ruta_update_metodo_existe',
                count($partes) === 2 && method_exists($partes[0], $partes[1]),
                'el handler '.$rutas[$clave][0].' no existe');
        }

        /*====================================*/
        // Rutas propias: una sola, de escritura por POST
        /*====================================*/
        $propias = array_filter($rutas, static function ($r) { return $r[2] === 'usuariosListadoPermisosBodegas'; });
        $this->addCase($results, 'rutas_propias_completas', count($propias) === 1,
            'se esperaba 1 ruta de '.self::CONTROLADOR.' y hay '.count($propias));
        foreach ($propias as $k => $datos) {
            $this->addCase($results, 'verbo_escritura_'.md5($k), (int)substr($k, 0, 1) === 2,
                $datos[0].' debe publicarse con POST');
        }

        /*====================================*/
        // Esta ruta cambia accesos de otros usuarios: no puede ser de solo lectura
        /*====================================*/
        $this->addCase($results, 'ruta_no_solo_lectura', !$existe || $rutas[$clave][1] >= 2,
            'la ruta que modifica accesos no puede exigir nivel 1');

        /*====================================*/
        // Desinstalacion: el controlador debe estar en RutaController()
        /*====================================*/
        $this->addCase($results, 'instalador_desinstala_controlador',
            str_contains($this->fuenteInstalador(), "\$RutaController .= ',\"usuariosListadoPermisosBodegas\"'"),
            'RutaController() no lista usuariosListadoPermisosBodegas y dejaria su ruta huerfana al desinstalar');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                PERMISOS                                    */
    /******************************************************************************/
    /**
     * Verifica la sesion, el nivel de acceso heredado de `usuariosListado`, la marca de
     * sesion que activa la pestana de bodegas y el registro de la ruta en
     * `SESSION.arrPermisos` y en el router de F3.
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
        if (is_array($UserData) && !empty($UserData)) {
            $valor = $UserData['usuariosPermisosBodegas'] ?? null;
            $this->addCase($results, 'sesion_usuariosPermisosBodegas', $valor === null || in_array((int)$valor, [1, 2], true),
                'usuariosPermisosBodegas de la sesion debe valer 1 o 2 (vale '.var_export($valor, true).')');
        }

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
        // Registro dinamico de la ruta desde SESSION.arrPermisos
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
            // Con nivel 1 (solo ver) la ruta de nivel 2 no se registra: es el comportamiento esperado
            $this->addCase($results, 'ruta_registrada', $nivel === null || (int)$nivel < 2 || $rutaOk,
                'la ruta '.self::RUTA_WEB_UPDATE.' no esta entre los permisos del usuario');
        }

        /*====================================*/
        // Router de F3: solo se evalua si el usuario tiene la ruta registrada
        /*====================================*/
        $arrRoutes = $f3->get('ROUTES');
        $this->addCase($results, 'router_disponible', is_array($arrRoutes),
            'F3 no expuso la variable ROUTES');
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
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /**
     * Renderiza `usuariosListado-Resumen-PermisosBodegas-List.php` con tres bodegas
     * sinteticas (con acceso, sin acceso, con acceso) y verifica interruptores, estado
     * marcado, valores enviados, endpoint, verbo y la tabla sin bodegas.
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function vistaPermisosBodegasTest($f3){

        $results = [];
        $BASE    = (string)$f3->get('BASE');
        if (trim($BASE) === '') {
            $BASE = '/admin';
        }
        $html = $this->renderizarVista($this->rutaVista(), $this->datosSinteticos(2), $BASE, $f3);

        /*====================================*/
        // Estructura general
        /*====================================*/
        $this->addCase($results, 'vista_render', $html !== '', 'la vista de bodegas no produjo HTML');
        $this->addCase($results, 'vista_form_id', str_contains($html, 'id="FormUpdatePermisosBodegas"'),
            'el formulario no usa el id FormUpdatePermisosBodegas');
        $this->addCase($results, 'vista_cabecera', str_contains($html, '>Nombre<') && str_contains($html, '>Permitido<'),
            'la tabla no dibuja Nombre y Permitido');
        $this->addCase($results, 'vista_sin_niveles', !str_contains($html, 'name="level_'),
            'el acceso a bodegas es binario: la vista no debe ofrecer niveles');
        $this->addCase($results, 'vista_nombres',
            str_contains($html, 'Bodega Central') && str_contains($html, 'Bodega Norte') && str_contains($html, 'Bodega Sur'),
            'la vista no lista todas las bodegas');
        $this->addCase($results, 'vista_filas', substr_count($html, 'name="switch_') === 6,
            'se esperaban 3 bodegas con 2 campos switch cada una');
        $this->addCase($results, 'vista_id_usuario_oculto', str_contains($html, 'name="idUsuario"') && str_contains($html, 'value="7"'),
            'el formulario no envia el idUsuario oculto');
        $this->addCase($results, 'vista_boton_guardar', str_contains($html, 'type="submit"') && str_contains($html, 'Guardar Cambios'),
            'falta el boton Guardar Cambios');

        /*====================================*/
        // Interruptor por bodega: valor 1 por defecto (oculto) y 2 si se marca
        /*====================================*/
        foreach ([1, 2, 3] as $id) {
            $fila = $this->filaRecurso($html, $id);
            $this->addCase($results, 'switch_doble_'.$id,
                $fila !== ''
                && str_contains($fila, 'type="hidden"   value="1" name="switch_'.$id.'"')
                && str_contains($fila, 'type="checkbox" value="2" name="switch_'.$id.'"'),
                'la bodega '.$id.' debe enviar switch_'.$id.'=1 (oculto) y 2 (marcado)');
        }

        /*====================================*/
        // Estado marcado segun el acceso previo
        /*====================================*/
        $this->addCase($results, 'bodega1_marcada', preg_match('/name="switch_1"[^>]*checked/', $this->filaRecurso($html, 1)) === 1,
            'la bodega con acceso debe verse marcada');
        $this->addCase($results, 'bodega2_sin_marcar', preg_match('/name="switch_2"[^>]*checked/', $this->filaRecurso($html, 2)) !== 1,
            'la bodega sin acceso no debe verse marcada');
        $this->addCase($results, 'bodega3_marcada', preg_match('/name="switch_3"[^>]*checked/', $this->filaRecurso($html, 3)) === 1,
            'la bodega con acceso debe verse marcada');
        $this->addCase($results, 'solo_dos_marcadas', preg_match_all('/type="checkbox"[^>]*checked/', $html) === 2,
            'solo las 2 bodegas con acceso deben venir marcadas');

        /*====================================*/
        // Endpoint y verbo
        /*====================================*/
        $this->addCase($results, 'endpoint_update',
            str_contains($html, $BASE.'/'.self::RUTA_WEB.'/bodegas/update') && str_contains($html, "Metodo      = 'POST'"),
            'el formulario no publica en /bodegas/update con POST');
        $this->addCase($results, 'envia_formulario_serializado', str_contains($html, '$("#FormUpdatePermisosBodegas").serialize()'),
            'el formulario debe enviar todos sus campos serializados');
        $this->addCase($results, 'envia_una_sola_vez', str_contains($html, 'if (ejecutandoForm.valor) return;'),
            'el formulario debe impedir el doble envio');

        /*====================================*/
        // Sin bodegas en el catalogo: tabla vacia sin filas ni errores
        /*====================================*/
        $vacio                       = $this->datosSinteticos(2);
        $vacio['arrPermisosBodegas'] = [];
        $htmlVacio                   = $this->renderizarVista($this->rutaVista(), $vacio, $BASE, $f3);
        $this->addCase($results, 'vacio_render', $htmlVacio !== '', 'sin bodegas la vista no produjo HTML');
        $this->addCase($results, 'vacio_sin_filas', !str_contains($htmlVacio, 'name="switch_'),
            'sin bodegas no debe haber filas');
        $this->addCase($results, 'vacio_conserva_formulario',
            str_contains($htmlVacio, 'id="FormUpdatePermisosBodegas"') && str_contains($htmlVacio, 'name="idUsuario"'),
            'sin bodegas el formulario debe seguir completo');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos el chequeo de datos del controlador (`dataCheck`), con el
     * valor vacio que usa la insercion en lote, y la validacion de IDs del borrado.
     *
     * @return array Resultado por caso
     */
    public function logicaValidacionTest(){

        $results = [];

        /*====================================*/
        // dataCheck devuelve el contrato esperado por Base_insertMultiple
        /*====================================*/
        try {
            $reflector  = new ReflectionMethod(self::CONTROLADOR, 'dataCheck');
            // Sin constructor: dataCheck() es logica pura y no necesita la conexion
            $Controller = (new ReflectionClass(self::CONTROLADOR))->newInstanceWithoutConstructor();

            $Post      = ['idUsuario' => 7, 'idBodegas' => 1];
            $DataCheck = $reflector->invoke($Controller, $Post);

            $this->addCase($results, 'datacheck_es_arreglo', is_array($DataCheck),
                'dataCheck() no devolvio un arreglo');
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post,
                'dataCheck() no conserva el POST recibido');
            $this->addCase($results, 'datacheck_numericos', ($DataCheck['ValidarNumero'] ?? '') === 'idUsuario,idBodegas',
                'dataCheck() no valida idUsuario e idBodegas como numeros');
            $this->addCase($results, 'datacheck_enteros', ($DataCheck['ValidarEntero'] ?? '') === 'idUsuario,idBodegas',
                'dataCheck() no valida idUsuario e idBodegas como enteros');

            // Solo se validan identificadores: el resto de validaciones queda vacio
            $inesperadas = [];
            foreach ($DataCheck as $clave => $valor) {
                if (in_array($clave, ['Post', 'ValidarNumero', 'ValidarEntero', 'ValidarLargoMinimoN', 'ValidarLargoMaximoPorCampo'], true)) {
                    continue;
                }
                if ($valor !== '') {
                    $inesperadas[] = $clave;
                }
            }
            $this->addCase($results, 'datacheck_solo_numericos', count($inesperadas) === 0,
                'dataCheck() declara validaciones no esperadas: '.implode(', ', $inesperadas));
            $this->addCase($results, 'datacheck_sin_largo_maximo', ($DataCheck['ValidarLargoMaximoPorCampo'] ?? null) === [],
                'dataCheck() declara largos maximos inesperados');

            // La insercion en lote lo invoca con cadena vacia: no debe fallar
            $vacio = $reflector->invoke($Controller, '');
            $this->addCase($results, 'datacheck_acepta_vacio', is_array($vacio) && ($vacio['Post'] ?? null) === '',
                'dataCheck(\'\') debe devolver el contrato con Post vacio');

            $otro = $reflector->invoke($Controller, ['idUsuario' => 9]);
            $this->addCase($results, 'datacheck_post_independiente', ($otro['Post'] ?? null) === ['idUsuario' => 9],
                'dataCheck() no debe arrastrar datos de una invocacion anterior');
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_es_arreglo', false,
                'no se pudo ejecutar dataCheck() ('.$e->getMessage().')');
        }

        /*====================================*/
        // Cifrado de los IDs del borrado: ida y vuelta y validacion como texto
        /*====================================*/
        try {
            $Codification = new FunctionsSecurityCodification();
            $cifrado      = $Codification->encryptDecrypt('encrypt', '7');
            $this->addCase($results, 'cifrado_valido_como_texto', $this->isValidDecrypted((array)$cifrado, 'text') === true,
                'el ID cifrado debe validarse como texto no vacio (asi lo exige el borrado)');
            $descifrado = $Codification->encryptDecrypt('decrypt', (string)($cifrado['data'] ?? ''));
            $this->addCase($results, 'cifrado_ida_y_vuelta',
                ($descifrado['success'] ?? false) === true && (string)($descifrado['data'] ?? '') === '7',
                'el cifrado no devuelve el valor original');
        } catch (Throwable $e) {
            $this->addCase($results, 'cifrado_ida_y_vuelta', false,
                'no se pudo ejecutar el cifrado ('.$e->getMessage().')');
        }

        $this->addCase($results, 'texto_valido_ok', $this->isValidDecrypted(['success' => true, 'data' => 'abc'], 'text') === true,
            'un texto no vacio debe ser valido para tipo text');
        $this->addCase($results, 'texto_valido_vacio', $this->isValidDecrypted(['success' => true, 'data' => '  '], 'text') === false,
            'un texto en blanco debe rechazarse para tipo text');
        $this->addCase($results, 'texto_valido_falla', $this->isValidDecrypted(['success' => false, 'data' => 'abc'], 'text') === false,
            'un cifrado fallido debe rechazarse');
        $this->addCase($results, 'texto_valido_sin_dato', $this->isValidDecrypted(['success' => true], 'text') === false,
            'un resultado sin dato debe rechazarse');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                 SEGURIDAD                                  */
    /******************************************************************************/
    /**
     * Revisa estaticamente la vista y el controlador: rutas relativas, rutas absolutas,
     * acceso a sesion, SQL embebido, parametros preparados, cifrado de los IDs del
     * borrado y escape de los nombres pintados.
     *
     * @return array Resultado por caso
     */
    public function seguridadVistasTest(){

        $results = [];

        /*====================================*/
        // Vista del controlador
        /*====================================*/
        $codigo = $this->codigoFuente($this->rutaVista());
        $nombre = basename($this->rutaVista());
        $this->addCase($results, 'hay_vista', $codigo !== '', 'no se pudo leer '.$nombre);
        $this->addCase($results, 'vista_sin_retroceso', !preg_match('#(?:\.\./|\.\.\\\\)#', $codigo),
            $nombre.' construye rutas con retroceso');
        $this->addCase($results, 'vista_sin_ruta_absoluta', !preg_match('#(?:/var/www|/home/|[A-Za-z]:\\\\)#', $codigo),
            $nombre.' fija una ruta absoluta del servidor');
        $this->addCase($results, 'vista_sin_sesion', !str_contains($codigo, '$_SESSION'),
            $nombre.' accede a la sesion de forma directa');
        $this->addCase($results, 'vista_sin_sql', !preg_match('/\b(SELECT|INSERT|UPDATE|DELETE)\s+\w+\s+FROM\b/i', $codigo),
            $nombre.' contiene una consulta SQL embebida');
        $this->addCase($results, 'vista_ruta_desde_acceso', str_contains($codigo, "\$data['UserAccess']['RouteAccess']"),
            $nombre.' debe construir el endpoint con la ruta de acceso del usuario');

        /*====================================*/
        // Controlador
        /*====================================*/
        $controlador = $this->fuenteControlador();
        $this->addCase($results, 'controlador_parametros_preparados', str_contains($controlador, "'params'"),
            'el controlador no usa parametros preparados');
        $this->addCase($results, 'controlador_sin_get_directo', !preg_match('/\$_(GET|REQUEST)\b/', $controlador),
            'el controlador lee $_GET/$_REQUEST directamente');
        $this->addCase($results, 'controlador_sin_volcado', !preg_match('/(error_log|var_dump|print_r|echo)\s*\(?[^;]*\$_POST/', $controlador),
            'el controlador no debe volcar el POST a logs ni a la salida');
        $this->addCase($results, 'borrado_ids_cifrados',
            str_contains($controlador, "encryptDecrypt('encrypt',\$_POST['idUsuario'])")
            && str_contains($controlador, "encryptDecrypt('encrypt',\$permisos['idBodegas'])"),
            'el borrado debe pasar los IDs por el cifrado antes de usarlos');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                    SEGURIDAD DEL CONTROLADOR (DEFECTOS CONOCIDOS)           */
    /******************************************************************************/
    /**
     * Comprueba, leyendo el fuente de `Update()`, que los datos del cliente se validen
     * antes de usarse: `idUsuario` no debe concatenarse en SQL ni usarse sin validar, el
     * usuario destino debe verificarse y las claves `switch_*` deben comprobarse antes de
     * leerse. Al corregir el controlador estos casos pasan a OK.
     *
     * @return array Resultado por caso
     */
    public function seguridadControladorTest(){

        $results = [];
        $update  = $this->cuerpoMetodo('Update');

        /*====================================*/
        // Inyeccion SQL: nada del cliente concatenado en el texto de una consulta
        /*====================================*/
        $this->addCase($results, 'controlador_sin_concat_sql',
            !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $this->fuenteControlador()),
            'Update() concatena $_POST[\'idUsuario\'] en el SQL de la subconsulta de acceso previo; debe validarse como entero positivo y pasarse como parametro');

        /*====================================*/
        // idUsuario validado como entero antes de cualquier consulta
        /*====================================*/
        $primeraQuery = strpos($update, '$query = [');
        $validaAntes  = preg_match('/\(int\)\s*\$_POST\[\'idUsuario\'\]|ctype_digit\(\s*\$_POST\[\'idUsuario\'\]|is_numeric\(\s*\$_POST\[\'idUsuario\'\]|filter_var\(\s*\$_POST\[\'idUsuario\'\]|intval\(\s*\$_POST\[\'idUsuario\'\]/', substr($update, 0, (int)$primeraQuery)) === 1;
        $this->addCase($results, 'idusuario_validado_antes_de_consultar', $validaAntes,
            'Update() usa $_POST[\'idUsuario\'] sin validarlo como entero antes de la primera consulta');

        /*====================================*/
        // El usuario destino existe y no es el superadministrador
        /*====================================*/
        $this->addCase($results, 'usuario_destino_verificado',
            str_contains($update, 'usuarios_listado') && str_contains($update, 'idTipoUsuario'),
            'Update() no verifica que el usuario destino exista ni que sea distinto del tipo de usuario 1');

        /*====================================*/
        // Claves del formulario verificadas antes de leerse
        /*====================================*/
        $this->addCase($results, 'switch_definido',
            str_contains($update, "isset(\$_POST['switch_") || str_contains($update, "\$_POST['switch_'.\$permisos['idBodegas']] ??"),
            'Update() lee $_POST[\'switch_<id>\'] sin verificar que exista: una clave ausente genera un aviso y no cambia nada');

        /************************************/
        // Retorno los datos
        return $results;

    }

}
