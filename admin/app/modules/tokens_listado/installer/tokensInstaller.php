<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class tokensInstaller extends ControllerInstaller {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_ADMIN);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'tokensInstaller';
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                               INSTALACION                                  */
    /******************************************************************************/
    /*******************************************************************/
    // Se lista la informacion
    /*******************************************************************/
    public function ListDataModule(){

        /************************************/
        // Se verifica si esta instalado
        $nData1    = $this->GetCountDataModule();

        /************************************/
        // Defino si etsa instalado en base a la respuesta
        $countPermisos = is_numeric($nData1)&&$nData1!=0 ? 1 : 0;

        /************************************/
        // Se crean los datos a mostrar
        $arrData = [
            'Nombre'        => 'Módulo de Administración de Tokens',
            'Descripcion'   => 'Módulo para administrar los tokens de acceso',
            'Controller'    => $this->controllerName,
            'countPermisos' => $countPermisos,
        ];

        /************************************/
        // Retorno los datos
        return $arrData;
    }

    /*******************************************************************/
    // Instalacion del modulo
    /*******************************************************************/
    public function InstallModule(){

        /************************************/
        // Verifico si se ejecuta otro hilo
        $DBConn = $this->getDBConn();

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Variables
        $arrTables        = $this->listTables();
        $arrOptimizations = $this->optimizeTables();
        $arrPermisos      = array();

        /************************************/
        // Verifico si existe
        if($arrTables){
            // Recorro los datos
            foreach ($arrTables as $table) {
                /************************************/
                // Preparo los datos
                $xParams = ['query' => $table];
                // Se ejecuta la query
                $xTable  = $this->Base_createTable($xParams);
                /************************************/
                // Si falla la ejecucion, se revierte de inmediato
                if ($xTable['status'] === false) {
                    // Se revierte la transaccion activa (no-op si no hay transaccion)
                    $this->Base_transactionRollback();
                    // Se diferencian los errores de validación de los de base de datos
                    if (is_array($xTable['error'] ?? null)) {
                        Response::error('Datos inválidos', 400, $xTable['error']);
                    }
                    // Se reporta el error
                    Response::error('Error al operar con la Base de Datos', 500, $xTable['error']);
                }
            }
        }

        /************************************/
        // Migracion del alcance de los tokens (permiso+nivel -> modulo, ADR-0019); no hace nada si ya esta migrado
        if (!$this->migrarPermisosTokenAModulos()) {
            // Rollback solo de lo transaccional: el DDL ya hizo commit implicito y M2-M4 corren en autocommit
            // (cada sentencia es atomica, el conjunto no); la migracion es reanudable al repetir la instalacion
            $this->Base_transactionRollback();
            // Se reporta el error
            Response::error('Error al operar con la Base de Datos', 500);
        }

        /************************************/
        // Verifico si existe
        if($arrOptimizations){
            // Recorro los datos
            foreach ($arrOptimizations as $query) {
                /************************************/
                // Preparo los datos
                $xParams      = ['query' => $query];
                // Ejecuto la query
                $ResponseExec = $this->Base_queryExecute($xParams);
                /************************************/
                // Si falla la la ejecucion, se muestra alerta
                if ($ResponseExec['status'] === false) {
                    // Se revierte la transaccion activa (no-op si no hay transaccion)
                    $this->Base_transactionRollback();
                    // Se diferencian los errores de validación de los de base de datos
                    if (is_array($ResponseExec['error'] ?? null)) {
                        Response::error('Datos inválidos', 400, $ResponseExec['error']);
                    }
                    // Se reporta el error
                    Response::error('Error al operar con la Base de Datos', 500, $ResponseExec['error']);
                }
            }
        }

        /************************************/
        // Transacción solo para los DML (semillas + permisos/rutas)
        $this->Base_transactionBegin();

        /*******************************************************/
        /*                 SE GENERAN LAS RUTAS                */
        /*******************************************************/
        $arrPermisos[] = [
            'idPermisosCat'  => '1',
            'idEstado'       => '1',
            'idTipo'         => '2',
            'Nombre'         => 'Tokens - Listado',
            'Descripcion'    => 'Permite la administración de los tokens de acceso',
            'idLevelLimit'   => '4',
            'RutaWeb'        => 'administracion/tokens/listado',
            'RutaController' => 'tokensListado',
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '1',
            'idEstado'       => '1',
            'idTipo'         => '1',
            'Nombre'         => 'Tokens - Clientes OAuth',
            'Descripcion'    => 'Permite ver, activar, desactivar y borrar los clientes OAuth del servidor MCP',
            'idLevelLimit'   => '4',
            'RutaWeb'        => 'administracion/tokens/clientesOauth',
            'RutaController' => 'tokensOauthClientes',
        ];

        /************************************/
        // Verifico si existe
        if($arrPermisos){

            /************************************/
            // Se instala el módulo con sus permisos y rutas
            $this->Base_installModule($arrPermisos, $DBConn);

            /************************************/
            // Verifico si la transacción sigue activa (Base_installModule()
            // hace rollback interno en caso de error, por lo que si la
            // transacción ya no está activa, falló y no debemos hacer commit)
            if (!$this->Base_inTransaction()) {
                // La transacción fue revertida internamente, no hacer commit
                return false;
            }
        }

        /************************************/
        // Se confirma la transaccion
        $ResponseCommit = $this->Base_transactionCommit();

        /************************************/
        // Si falla la la ejecucion, se revierte de inmediato
        if ($ResponseCommit['status'] === false) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se reporta el error
            return false;
        }

        /************************************/
        // Retorno True por defecto
        return true;

    }

    /*******************************************************************/
    // Desinstalacion del modulo
    /*******************************************************************/
    public function UninstallModule(){

        /************************************/
        // Retorno datos
        return $this->Base_uninstallModule($this->RutaController(), $this->listTables());

    }

    /*******************************************************************/
    // Se cuentan las rutas del controlador
    /*******************************************************************/
    public function GetCountDataModule(){

        /************************************/
        // Retorno datos
        return $this->Base_getCountDataModule($this->RutaController());

    }

    /*******************************************************************/
    // Se listan las rutas
    /*******************************************************************/
    public function listRouteModule(int $type, mixed $permisosID): array {
        $routes = $this->getRouteDefinitions($type);

        return array_map(static function (array $route) use ($permisosID): array {
            $route['idPermisos'] = $permisosID;
            return $route;
        }, $routes);
    }

    /*******************************************************************/
    // Definicion de rutas por permiso
    /*******************************************************************/
    public function getRouteDefinitions(int $type): array {
        return match ($type) {
            1 => [
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/tokens/listado/listAll', 'RutaController' => 'tokensListado->listAll', 'Descripcion' => 'Listar Toda la Información', 'idLevelLimit' => 1, 'Controller' => 'tokensListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/tokens/listado/search', 'RutaController' => 'tokensListado->UpdateList', 'Descripcion' => 'Filtrar datos', 'idLevelLimit' => 1, 'Controller' => 'tokensListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/tokens/listado/updateList', 'RutaController' => 'tokensListado->UpdateList', 'Descripcion' => 'Actualizar Lista', 'idLevelLimit' => 1, 'Controller' => 'tokensListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/tokens/listado/view/@id', 'RutaController' => 'tokensListado->View', 'Descripcion' => 'Mostrar Detallado', 'idLevelLimit' => 1, 'Controller' => 'tokensListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/tokens/listado/resumen/@id', 'RutaController' => 'tokensListado->Resumen', 'Descripcion' => 'Mostrar Resúmen', 'idLevelLimit' => 2, 'Controller' => 'tokensListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/tokens/listado/resumenUpdate/@id', 'RutaController' => 'tokensListado->ResumenUpdate', 'Descripcion' => 'Mostrar información', 'idLevelLimit' => 2, 'Controller' => 'tokensListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/tokens/listado', 'RutaController' => 'tokensListado->Insert', 'Descripcion' => 'Crear Información', 'idLevelLimit' => 3, 'Controller' => 'tokensListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/tokens/listado/update', 'RutaController' => 'tokensListado->Update', 'Descripcion' => 'Editar por post (modificar y subir archivos)', 'idLevelLimit' => 2, 'Controller' => 'tokensListado'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/tokens/listado', 'RutaController' => 'tokensListado->Delete', 'Descripcion' => 'Borrar dato y archivos', 'idLevelLimit' => 4, 'Controller' => 'tokensListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/tokens/listado/permisos/updateList/@id', 'RutaController' => 'tokensListadoPermisos->UpdateList', 'Descripcion' => 'Actualizar Lista', 'idLevelLimit' => 2, 'Controller' => 'tokensListadoPermisos'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/tokens/listado/permisos/update', 'RutaController' => 'tokensListadoPermisos->Update', 'Descripcion' => 'Modificar los permisos de los tokens', 'idLevelLimit' => 2, 'Controller' => 'tokensListadoPermisos'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/tokens/listado/oauthClientes/updateList/@id', 'RutaController' => 'tokensListadoOauthClientes->UpdateList', 'Descripcion' => 'Actualizar Lista', 'idLevelLimit' => 2, 'Controller' => 'tokensListadoOauthClientes'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/tokens/listado/oauthClientes/revocar', 'RutaController' => 'tokensListadoOauthClientes->Revocar', 'Descripcion' => 'Revocar los accesos de un cliente OAuth con el token', 'idLevelLimit' => 2, 'Controller' => 'tokensListadoOauthClientes'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/tokens/listado/oauthAccesos/updateList/@id', 'RutaController' => 'tokensListadoOauthAccesos->UpdateList', 'Descripcion' => 'Actualizar Lista', 'idLevelLimit' => 2, 'Controller' => 'tokensListadoOauthAccesos'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/tokens/listado/oauthAccesos', 'RutaController' => 'tokensListadoOauthAccesos->Delete', 'Descripcion' => 'Revocar un acceso OAuth emitido', 'idLevelLimit' => 2, 'Controller' => 'tokensListadoOauthAccesos'],
            ],
            2 => [
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/tokens/clientesOauth/listAll', 'RutaController' => 'tokensOauthClientes->listAll', 'Descripcion' => 'Listar Toda la Información', 'idLevelLimit' => 1, 'Controller' => 'tokensOauthClientes'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/tokens/clientesOauth/search', 'RutaController' => 'tokensOauthClientes->UpdateList', 'Descripcion' => 'Filtrar datos', 'idLevelLimit' => 1, 'Controller' => 'tokensOauthClientes'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/tokens/clientesOauth/updateList', 'RutaController' => 'tokensOauthClientes->UpdateList', 'Descripcion' => 'Actualizar Lista', 'idLevelLimit' => 1, 'Controller' => 'tokensOauthClientes'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/tokens/clientesOauth/view/@id', 'RutaController' => 'tokensOauthClientes->View', 'Descripcion' => 'Mostrar Detallado', 'idLevelLimit' => 1, 'Controller' => 'tokensOauthClientes'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/tokens/clientesOauth/getID/@id', 'RutaController' => 'tokensOauthClientes->GetID', 'Descripcion' => 'Información para el formulario edición', 'idLevelLimit' => 2, 'Controller' => 'tokensOauthClientes'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/tokens/clientesOauth/update', 'RutaController' => 'tokensOauthClientes->Update', 'Descripcion' => 'Editar por post (modificar y subir archivos)', 'idLevelLimit' => 2, 'Controller' => 'tokensOauthClientes'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/tokens/clientesOauth', 'RutaController' => 'tokensOauthClientes->Delete', 'Descripcion' => 'Borrar dato y archivos', 'idLevelLimit' => 4, 'Controller' => 'tokensOauthClientes'],
            ],
            default => [],
        };
    }

    /*******************************************************************/
    // Se listan los controladores
    /*******************************************************************/
    private function RutaController(){

        /************************************/
        // Se obtiene el nombre de los Controladores utilizados
        $RutaController  = 'tokensListado';
        $RutaController .= ',tokensListadoPermisos';
        $RutaController .= ',tokensListadoOauthClientes';
        $RutaController .= ',tokensListadoOauthAccesos';
        $RutaController .= ',tokensOauthClientes';

        /************************************/
        // Retorno los datos
        return $RutaController;
    }

    /*******************************************************************/
    // Migracion: alcance del token por modulo (ADR-0019)
    /*******************************************************************/
    /**
     * Indica si tokens_listado_permisos conserva la forma antigua (columna idPermisos).
     *
     * @return bool true si la columna idPermisos existe
     * @throws RuntimeException si la consulta falla
     */
    private function permisosTokenTieneFormaAntigua(): bool {
        $xParams = [
            'query'     => "SELECT COUNT(*) AS Total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tokens_listado_permisos' AND COLUMN_NAME = 'idPermisos'",
            'singleRow' => true,
        ];
        $r = $this->Base_queryExecute($xParams);
        if (($r['status'] ?? false) !== true) {
            throw new RuntimeException('No se pudo comprobar la forma de tokens_listado_permisos');
        }
        return (int) ($r['data']['Total'] ?? 0) > 0;
    }

    /**
     * Ejecuta una sentencia de la migracion.
     *
     * @param string $query  SQL con marcadores posicionales
     * @param array  $params Valores enlazados
     * @return bool true si se ejecuto sin error
     */
    private function ejecutarMigracion(string $query, array $params = []): bool {
        $xParams = ['query' => $query, 'params' => $params];
        $r = $this->Base_queryExecute($xParams);
        return ($r['status'] ?? false) === true;
    }

    /** M1: agrega la columna Modulo (nullable) a la tabla antigua. */
    private function migrarAgregarModulo(): bool {
        return $this->ejecutarMigracion('ALTER TABLE tokens_listado_permisos ADD COLUMN IF NOT EXISTS Modulo varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;');
    }

    /**
     * M2: convierte permiso+nivel en modulo con la misma condicion con la que el MCP daba acceso
     * (nivel >= 1, permiso activo, controlador del modulo exponible).
     */
    private function migrarConvertirPermisos(): bool {
        foreach (InternalFunctionsTokensModulos::listExposable() as $m) {
            if ($m['controladores'] === []) {
                continue;
            }
            $marcadores = implode(',', array_fill(0, count($m['controladores']), '?'));
            $ok = $this->ejecutarMigracion(
                'UPDATE tokens_listado_permisos INNER JOIN core_permisos_listado ON core_permisos_listado.idPermisos = tokens_listado_permisos.idPermisos SET tokens_listado_permisos.Modulo = ? WHERE tokens_listado_permisos.Modulo IS NULL AND tokens_listado_permisos.idLevelLimit >= 1 AND core_permisos_listado.idEstado = 1 AND core_permisos_listado.RutaController IN (' . $marcadores . ')',
                array_merge([$m['modulo']], array_values($m['controladores']))
            );
            if (!$ok) {
                return false;
            }
        }
        return true;
    }

    /** M3: deja una sola fila por (idToken, Modulo), la de menor id. */
    private function migrarDeduplicar(): bool {
        return $this->ejecutarMigracion('DELETE a FROM tokens_listado_permisos a INNER JOIN tokens_listado_permisos b ON a.idToken = b.idToken AND a.Modulo = b.Modulo AND a.idTokenPermiso > b.idTokenPermiso WHERE a.Modulo IS NOT NULL;');
    }

    /**
     * Cuenta las filas de tokens_listado_permisos que cumplen una condicion fija (sin datos de usuario).
     *
     * @param string $where Condicion SQL literal (sin entrada externa) o '' para todas
     * @return int|null Total, o null si la consulta falla
     */
    private function contarPermisosToken(string $where = ''): ?int {
        $xParams = [
            'query'     => 'SELECT COUNT(*) AS Total FROM tokens_listado_permisos' . ($where !== '' ? ' WHERE ' . $where : ''),
            'singleRow' => true,
        ];
        $r = $this->Base_queryExecute($xParams);
        if (($r['status'] ?? false) !== true) {
            return null;
        }
        return (int) ($r['data']['Total'] ?? 0);
    }

    /** M4: descarta las filas que no dieron acceso MCP (sin modulo asignado); registra cuantas descarta. */
    private function migrarDescartarHuerfanos(): bool {
        $n = $this->contarPermisosToken('Modulo IS NULL');
        if ($n === null) {
            return false;
        }
        error_log('tokensInstaller: migracion de permisos a modulos: M4 descarta ' . $n . ' fila(s) sin modulo');
        return $this->ejecutarMigracion('DELETE FROM tokens_listado_permisos WHERE Modulo IS NULL;');
    }

    /** M5: elimina el indice y las columnas antiguas y fija Modulo NOT NULL. */
    private function migrarEliminarAntiguo(): bool {
        return $this->ejecutarMigracion('ALTER TABLE tokens_listado_permisos DROP INDEX IF EXISTS uk_tkperm_token_permiso, DROP COLUMN IF EXISTS idPermisos, DROP COLUMN IF EXISTS idLevelLimit, MODIFY Modulo varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;');
    }

    /**
     * Migra tokens_listado_permisos de (idPermisos, idLevelLimit) a Modulo. Reanudable: solo actua si existe idPermisos.
     *
     * Los pasos M1-M5 se ejecutan en orden y se detienen en el primer fallo. Antes de M1 hay una guarda: si hay filas
     * y listExposable() esta vacio (o lanza), se aborta con false sin tocar nada, porque M4 borraria todos los alcances.
     * Los datos antiguos (idPermisos/idLevelLimit) siguen intactos hasta M5; M2-M4 corren en autocommit.
     *
     * @return bool false ante cualquier fallo (el llamador aborta la instalacion)
     */
    private function migrarPermisosTokenAModulos(): bool {
        try {
            if (!$this->permisosTokenTieneFormaAntigua()) {
                return true;
            }
            // Guarda anti-perdida de datos (A-1): con filas y sin catalogo exponible, M2 no convertiria nada y M4 borraria todo
            $filas = $this->contarPermisosToken();
            if ($filas === null) {
                return false;
            }
            if ($filas > 0 && InternalFunctionsTokensModulos::listExposable() === []) {
                error_log('tokensInstaller: migracion de permisos a modulos abortada: ' . $filas . ' fila(s) y listExposable() vacio; no se modifica nada');
                return false;
            }
            return $this->migrarAgregarModulo()
                && $this->migrarConvertirPermisos()
                && $this->migrarDeduplicar()
                && $this->migrarDescartarHuerfanos()
                && $this->migrarEliminarAntiguo();
        } catch (Throwable $e) {
            error_log('tokensInstaller: migracion de permisos a modulos: ' . $e->getMessage());
            return false;
        }
    }

    /*******************************************************************/
    // Se listan las tablas
    /*******************************************************************/
    public function listTables(){

        /************************************/
        // Variables
        $arrTables    = array();

        /*******************************************************/
        /*                 SE GENERAN LAS TABLAS               */
        /*******************************************************/
        $arrTables[] = [
            'table'      => 'tokens_listado',
            'data'       => '`idToken` int(10) unsigned NOT NULL AUTO_INCREMENT,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`Token` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`FechaCreacion` datetime NOT NULL,`idUsuario` int(10) unsigned NOT NULL,`idEstado` int(10) unsigned NOT NULL,`FechaCaducidad` date NULL DEFAULT NULL,`Origen` int(10) unsigned NOT NULL DEFAULT 1,`idTokenPadre` int(10) unsigned NULL DEFAULT NULL,`idClienteOAuth` int(10) unsigned NULL DEFAULT NULL,`FechaExpiracion` datetime NULL DEFAULT NULL',
            'primaryKey' => 'idToken',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'tokens_listado_permisos',
            'data'       => '`idTokenPermiso` int(10) unsigned NOT NULL AUTO_INCREMENT,`idToken` int(10) unsigned NOT NULL,`Modulo` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`fechaCreacion` date NULL DEFAULT NULL',
            'primaryKey' => 'idTokenPermiso',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'tokens_mcp_log',
            'data'       => '`idLog` bigint(20) unsigned NOT NULL AUTO_INCREMENT,`idToken` int(10) unsigned NOT NULL DEFAULT 0,`Metodo` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`Herramienta` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Argumentos` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Resultado` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`IP` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`Fecha` datetime NOT NULL,`idClienteOAuth` int(10) unsigned NULL DEFAULT NULL',
            'primaryKey' => 'idLog',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'tokens_oauth_clientes',
            'data'       => '`idCliente` int(10) unsigned NOT NULL AUTO_INCREMENT,`ClientId` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`SecretHash` char(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`AuthMethod` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`RedirectUris` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`Metadatos` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`idEstado` int(10) unsigned NOT NULL,`FechaRegistro` datetime NOT NULL,`IPRegistro` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`IPClave` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`FechaUltimoUso` datetime NULL DEFAULT NULL',
            'primaryKey' => 'idCliente',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'tokens_oauth_codigos',
            'data'       => '`idCodigo` int(10) unsigned NOT NULL AUTO_INCREMENT,`CodigoHash` char(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`idCliente` int(10) unsigned NOT NULL,`idToken` int(10) unsigned NOT NULL,`RedirectUri` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`CodeChallenge` varchar(128) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`Resource` varchar(512) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`Scope` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`FechaCreacion` datetime NOT NULL,`FechaExpiracion` datetime NOT NULL,`Usado` int(10) unsigned NOT NULL DEFAULT 0,`idTokenAcceso` int(10) unsigned NULL DEFAULT NULL',
            'primaryKey' => 'idCodigo',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'tokens_oauth_refresh',
            'data'       => '`idRefresh` int(10) unsigned NOT NULL AUTO_INCREMENT,`RefreshHash` char(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`Familia` char(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`idCliente` int(10) unsigned NOT NULL,`idTokenAcceso` int(10) unsigned NOT NULL,`Resource` varchar(512) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`Scope` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`Estado` int(10) unsigned NOT NULL DEFAULT 1,`FechaCreacion` datetime NOT NULL,`FechaExpiracion` datetime NOT NULL,`FechaExpiracionFamilia` datetime NOT NULL,`FechaUso` datetime NULL DEFAULT NULL',
            'primaryKey' => 'idRefresh',
            'comentario' => 'Creado desde el Instalador',
        ];

        /************************************/
        // Retorno la respuesta de la consulta
        return $arrTables;

    }

    /*******************************************************************/
    // Optimizaciones de las tablas
    /*******************************************************************/
    public function optimizeTables(){

        /*******************************************************/
        /*            SE GENERAN LAS OPTIMIZACIONES            */
        /*******************************************************/
        $arrOptimizations = [
            'ALTER TABLE tokens_listado ADD UNIQUE INDEX IF NOT EXISTS uk_tokens_Token (Token),ADD INDEX IF NOT EXISTS idx_tokens_estado (idEstado),ADD INDEX IF NOT EXISTS idx_tokens_usuario (idUsuario),ADD INDEX IF NOT EXISTS idx_tokens_caducidad (FechaCaducidad),ADD COLUMN IF NOT EXISTS Nombre varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT \'\' AFTER idToken;',
            'ALTER TABLE tokens_listado ADD COLUMN IF NOT EXISTS Origen int(10) unsigned NOT NULL DEFAULT 1,ADD COLUMN IF NOT EXISTS idTokenPadre int(10) unsigned NULL DEFAULT NULL,ADD COLUMN IF NOT EXISTS idClienteOAuth int(10) unsigned NULL DEFAULT NULL,ADD COLUMN IF NOT EXISTS FechaExpiracion datetime NULL DEFAULT NULL;',
            'ALTER TABLE tokens_listado ADD INDEX IF NOT EXISTS idx_tokens_origen_fecha (Origen, FechaCreacion),ADD INDEX IF NOT EXISTS idx_tokens_padre (idTokenPadre),ADD INDEX IF NOT EXISTS idx_tokens_cliente (idClienteOAuth),ADD INDEX IF NOT EXISTS idx_tokens_expiracion (FechaExpiracion);',
            'ALTER TABLE tokens_listado_permisos ADD UNIQUE INDEX IF NOT EXISTS uk_tkperm_token_modulo (idToken, Modulo);',
            'ALTER TABLE tokens_mcp_log ADD INDEX IF NOT EXISTS idx_tkmcp_token_fecha (idToken, Fecha),ADD INDEX IF NOT EXISTS idx_tkmcp_fecha (Fecha);',
            'ALTER TABLE tokens_mcp_log ADD COLUMN IF NOT EXISTS idClienteOAuth int(10) unsigned NULL DEFAULT NULL;',
            'ALTER TABLE tokens_oauth_clientes ADD UNIQUE INDEX IF NOT EXISTS uk_tkoc_clientid (ClientId),ADD INDEX IF NOT EXISTS idx_tkoc_estado (idEstado),ADD INDEX IF NOT EXISTS idx_tkoc_ip_registro (IPClave, FechaRegistro),ADD INDEX IF NOT EXISTS idx_tkoc_uso (FechaUltimoUso, FechaRegistro);',
            'ALTER TABLE tokens_oauth_codigos ADD UNIQUE INDEX IF NOT EXISTS uk_tkocod_hash (CodigoHash),ADD INDEX IF NOT EXISTS idx_tkocod_cliente (idCliente),ADD INDEX IF NOT EXISTS idx_tkocod_token (idToken),ADD INDEX IF NOT EXISTS idx_tkocod_expiracion (FechaExpiracion);',
            'ALTER TABLE tokens_oauth_refresh ADD UNIQUE INDEX IF NOT EXISTS uk_tkoref_hash (RefreshHash),ADD INDEX IF NOT EXISTS idx_tkoref_familia (Familia),ADD INDEX IF NOT EXISTS idx_tkoref_acceso (idTokenAcceso),ADD INDEX IF NOT EXISTS idx_tkoref_cliente (idCliente),ADD INDEX IF NOT EXISTS idx_tkoref_expiracion (FechaExpiracion);',
        ];

        /************************************/
        // Retorna las optimizaciones
        return $arrOptimizations;

    }

    /*******************************************************************/
    // Mapeo de las tablas
    /*******************************************************************/
    public function mapTables(){

        $Data = '
        core_estados(idEstado,Nombre)
        core_permisos_listado_rutas(Controller)
        ';

        /************************************/
        // Variables
        $arrTables = $this->listTables();
        $dataSQL   = new FunctionsDataSQL();
        $Data     .= $dataSQL->minifyArrayTables($arrTables);

        /************************************/
        // Retorno True por defecto
        return $Data;

    }

}
