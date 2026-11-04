<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class tercerosEntidadesListadoUsuariosMaqTesting
 *
 * Pruebas del modulo `terceros` - controlador `tercerosEntidadesListadoUsuariosMaq` (modal de permisos
 * de visualizacion de maquinas de un usuario de un cliente: lista las maquinas de la entidad con un
 * interruptor por maquina y sincroniza los permisos en `terceros_entidades_listado_usuarios_maq`).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. El normalizador
 *    `sistemaTesteos::normalizeResult()` marca EXITOSA o ERROR segun el contenido, por lo que
 *    los textos de los casos correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Alcance: pruebas NO destructivas. `Update()` borra e inserta permisos y termina en
 * `Response::success()`/`Response::error()` (que ejecutan `exit`); por eso NO se invoca: se verifica
 * por reflexion y por lectura del fuente acotada al metodo. `InstallModule()`/`UninstallModule()`
 * tampoco se ejecutan: solo se lee `getRouteDefinitions()` sin conexion a BD.
 *
 * Notas del controlador:
 *  - Comparte el permiso del controlador `tercerosEntidadesListado` (`$this->controllerName`).
 *  - Su unica vista es `tercerosEntidadesListado-Resumen-Usuarios-Maquinas-formEdit.php` y el JS que la
 *    abre (`tabUsuariosEditMaq`) vive en `tercerosEntidadesListado-Resumen.php`.
 *  - Las rutas llevan DOS identificadores cifrados (`@idEntidad/@idUsuario`).
 *
 * @see skills/testing/SKILL.md
 */
class tercerosEntidadesListadoUsuariosMaqTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR      = 'tercerosEntidadesListadoUsuariosMaq';
    private const INSTALADOR       = 'tercerosEntidadesInstaller';
    // Controlador cuyo permiso comparte (valor de $this->controllerName)
    private const CONTROLADOR_PERMISOS = 'tercerosEntidadesListado';
    private const VISTA_BASE       = 'tercerosEntidadesListado';
    private const RUTA_WEB         = 'serviciosTerceros/entidades/listado';
    private const RUTA_WEB_LISTADO = 'serviciosTerceros/entidades/listado/listAll';
    private const RUTA_UMAQ        = 'serviciosTerceros/entidades/listado/usuariosMaq';
    // Niveles de permiso del catalogo (1=ver, 2=editar, 3=crear, 4=borrar)
    private const NIVELES          = [1, 2, 3, 4];
    // Vistas propias de este controlador (sufijo tras VISTA_BASE.'-')
    private const VISTAS_PROPIAS   = ['Resumen-Usuarios-Maquinas-formEdit'];

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
    // Fuente del controlador completo
    /*******************************************************************/
    private function fuenteControlador(): string{
        return $this->codigoFuente($this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'));
    }

    /*******************************************************************/
    // Fuente de UN metodo del controlador (para no confundir metodos entre si)
    /*******************************************************************/
    private function cuerpoMetodo(string $metodo): string{
        if (!method_exists(self::CONTROLADOR, $metodo)) {
            return '';
        }
        $reflector = new ReflectionMethod(self::CONTROLADOR, $metodo);
        $lineas    = file($reflector->getFileName(), FILE_IGNORE_NEW_LINES) ?: [];
        $inicio    = $reflector->getStartLine() - 1;
        $largo     = $reflector->getEndLine() - $inicio;

        return implode("\n", array_slice($lineas, $inicio, $largo));
    }

    /*******************************************************************/
    // Rutas del instalador para este controlador (sin conexion a BD):
    // [idMetodo.' '.RutaWeb => [handler, nivel]]
    /*******************************************************************/
    private function rutasInstalador(): array{
        $instalador = (new ReflectionClass(self::INSTALADOR))->newInstanceWithoutConstructor();
        $rutas      = [];
        foreach ($instalador->getRouteDefinitions(1) as $ruta) {
            if (($ruta['Controller'] ?? '') !== self::CONTROLADOR) {
                continue;
            }
            $rutas[$ruta['idMetodo'].' '.$ruta['RutaWeb']] = [$ruta['RutaController'], (int)$ruta['idLevelLimit']];
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
    // Ruta de una vista propia del controlador
    /*******************************************************************/
    private function rutaVista(string $sufijo): string{
        return $this->rutaModulo('../views/'.self::VISTA_BASE.'-'.$sufijo.'.php');
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
    // evaluado sobre el cuerpo del propio metodo
    /*******************************************************************/
    private function contratoPresentacion(array &$results, string $metodo, string $vista, string $tipoVista){
        $codigo = $this->cuerpoMetodo($metodo);
        $this->addCase($results, 'vista_'.$metodo, str_contains($codigo, $vista),
            $metodo.'() no instancia la vista '.$vista);
        $this->addCase($results, 'tipoview_'.$metodo, str_contains($codigo, 'showVista('.$tipoVista.','),
            $metodo.'() no usa showVista('.$tipoVista.')');
        $this->addCase($results, 'rutavista_'.$metodo, str_contains($codigo, 'returnRutaVista('),
            $metodo.'() no resuelve la ruta de la vista');
        $this->addCase($results, 'rutaruta_'.$metodo, str_contains($codigo, '$this->controllerName'),
            $metodo.'() no referencia $this->controllerName');
        $this->addCase($results, 'errores_'.$metodo, str_contains($codigo, 'showError('.$tipoVista.','),
            $metodo.'() no maneja la ruta de error con showError('.$tipoVista.')');
        $this->addCase($results, 'usuario_'.$metodo, str_contains($codigo, 'getUserData($f3)'),
            $metodo.'() no obtiene los datos del usuario');
        $this->addCase($results, 'nivel_'.$metodo, str_contains($codigo, 'getArrLevel($f3, $this->controllerName)'),
            $metodo.'() no resuelve el nivel de acceso del usuario');
    }

    /*******************************************************************/
    // Datos sinteticos usados por la vista del controlador
    /*******************************************************************/
    private function datosSinteticos(int $nivel, int $subtitulo = 1): array{
        return [
            'UserData'        => [
                'MainPathUrl'          => '/admin/public/upload/',
                'sistemaModalSubtitle' => $subtitulo,
                'sistemaModalCloseBTN' => 2,
                'UserType'             => 2,
            ],
            'UserAccess'      => ['LevelAccess' => $nivel, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_FormInputs'    => new UIFormInputs(),
            'Fnc_Codification'  => new FunctionsSecurityCodification(),
            'rowData'         => [
                'idUsuario' => 5,
                'idEntidad' => 91,
                'Nombre'    => 'Ana Demo',
            ],
            'arrPermisos'     => [
                ['idMaq' => 11, 'ID' => 11, 'Maquina' => 'Maquina Demo A', 'IsActivo' => 1],
                ['idMaq' => 12, 'ID' => 12, 'Maquina' => 'Maquina Demo B', 'IsActivo' => 0],
                ['idMaq' => 13, 'ID' => 13, 'Maquina' => 'Maquina Demo C', 'IsActivo' => 1],
            ],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica los archivos y la proteccion de acceso del controlador `tercerosEntidadesListadoUsuariosMaq`
     * y la convencion "clase = nombre de archivo" exigida por el AUTOLOAD de Fat-Free.
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
            'instalador'  => $this->rutaModulo('../installer/'.self::INSTALADOR.'.php'),
            'testing'     => $this->rutaModulo('tercerosEntidadesListadoUsuariosMaqTesting.php'),
            'vista_padre' => $this->rutaVista('Resumen'),
        ];
        foreach (self::VISTAS_PROPIAS as $vista) {
            $arrArchivos['vista_'.$vista] = $this->rutaVista($vista);
        }
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
        // Convencion clase = nombre de archivo y herencias
        /*====================================*/
        $this->addCase($results, 'clase_controlador_cargada', class_exists(self::CONTROLADOR),
            'la clase '.self::CONTROLADOR.' no esta disponible');
        $this->addCase($results, 'clase_testing_cargada', class_exists('tercerosEntidadesListadoUsuariosMaqTesting'),
            'la clase tercerosEntidadesListadoUsuariosMaqTesting no esta disponible');
        $padreController = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padreController === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padreController.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('tercerosEntidadesListadoUsuariosMaqTesting') === 'ControllerBase',
            'el testing no extiende ControllerBase');

        /*====================================*/
        // Constructor: comparte el permiso del controlador raiz del listado
        /*====================================*/
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'constructor_conexion', str_contains($codigo, 'ConfigDataBase::MySQL_1'),
            'el constructor no usa la conexion ConfigDataBase::MySQL_1');
        $this->addCase($results, 'constructor_permiso_compartido',
            str_contains($codigo, "\$this->controllerName = '".self::CONTROLADOR_PERMISOS."'"),
            'controllerName debe ser '.self::CONTROLADOR_PERMISOS.' (el permiso se comparte con el listado)');
        $this->addCase($results, 'constructor_padre', str_contains($codigo, 'parent::__construct($DB_conn_1, $queryBuilder, $checkData)'),
            'el constructor no delega en ControllerBase');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                            CONTROLADOR: VISTAS                             */
    /******************************************************************************/
    /**
     * Verifica el contrato de `UpdateList` (unico metodo que devuelve HTML): firma, uso de
     * `showVista()`/`returnRutaVista()`/`getUserData()`, descifrado y validacion de los DOS
     * identificadores (`idUsuario`, `idEntidad`) y las consultas que alimentan la vista.
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];

        $this->firmaMetodo($results, 'UpdateList', 2, 2);
        $this->contratoPresentacion($results, 'UpdateList', "\$this->controllerName.'-Resumen-Usuarios-Maquinas-formEdit.php'", '2');

        /*====================================*/
        // Identificadores cifrados: se descifran y validan ambos antes de consultar
        /*====================================*/
        $lista = $this->cuerpoMetodo('UpdateList');
        foreach (['UsuarioID' => 'idUsuario', 'EntidadID' => 'idEntidad'] as $variable => $parametro) {
            $this->addCase($results, 'id_descifrado_'.$parametro,
                str_contains($lista, "\$".$variable."  = \$this->Codification->encryptDecrypt('decrypt', \$params['".$parametro."'])"),
                'UpdateList() no descifra $params[\''.$parametro.'\']');
            $this->addCase($results, 'id_valido_'.$parametro, str_contains($lista, "isValidDecrypted(\$".$variable.", 'id')"),
                'UpdateList() no valida '.$parametro.' con isValidDecrypted()');
        }
        $this->addCase($results, 'id_invalido_400', substr_count($lista, "Response::error('Registro inválido', 400)") === 2,
            'UpdateList() debe responder 400 ante cada ID invalido');

        /*====================================*/
        // Consulta del usuario y de las maquinas de la entidad
        /*====================================*/
        $this->addCase($results, 'usuario_consulta',
            str_contains($lista, "'table'   => 'terceros_entidades_listado_usuarios'") && str_contains($lista, "'where'   => 'idUsuario = ?'")
            && str_contains($lista, "'params'  => [\$UsuarioID['data']]"),
            'UpdateList() no consulta al usuario con parametro preparado');
        $this->addCase($results, 'usuario_solo_columnas_necesarias', str_contains($lista, "'data'    => 'idUsuario,idEntidad,Nombre'"),
            'UpdateList() consulta columnas de mas del usuario (no debe incluir la clave)');
        $this->addCase($results, 'maquinas_de_la_entidad',
            str_contains($lista, "'table'   => 'terceros_entidades_listado_maquinas'")
            && str_contains($lista, "'where'   => 'terceros_entidades_listado_maquinas.idEntidad = ?'")
            && str_contains($lista, "'params'  => [\$EntidadID['data']]"),
            'UpdateList() no limita las maquinas a la entidad con parametro preparado');
        $this->addCase($results, 'maquinas_nombre', str_contains($lista, 'LEFT JOIN maquinas_listado') && str_contains($lista, 'maquinas_listado.Nombre AS Maquina'),
            'UpdateList() no une el nombre de la maquina');
        $this->addCase($results, 'maquinas_es_activo',
            str_contains($lista, 'AS IsActivo') && str_contains($lista, 'terceros_entidades_listado_usuarios_maq'),
            'UpdateList() no calcula si el usuario ya tiene el permiso (IsActivo)');
        $this->addCase($results, 'maquinas_orden', str_contains($lista, 'maquinas_listado.Nombre ASC'),
            'UpdateList() no ordena por nombre de maquina');
        $this->addCase($results, 'maquinas_limite', str_contains($lista, 'N_MaxItems'),
            'UpdateList() no acota la cantidad de filas con N_MaxItems');
        $this->addCase($results, 'entrega_datos',
            str_contains($lista, "'rowData'") && str_contains($lista, "'arrPermisos'") && str_contains($lista, "'Fnc_FormInputs'"),
            'UpdateList() no entrega rowData, arrPermisos y Fnc_FormInputs a la vista');
        $this->addCase($results, 'fallback_errores', str_contains($lista, 'mergeResponses([$rowData,$arrPermisos])'),
            'UpdateList() no agrupa las respuestas fallidas con mergeResponses()');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                         CONTROLADOR: ESCRITURA (UPDATE)                    */
    /******************************************************************************/
    /**
     * Verifica el contrato de `Update($f3)` SIN ejecutarlo: borra e inserta permisos y termina en
     * `Response::*()` (que hace `exit`). Se comprueba firma, verbo HTTP, semantica de los
     * interruptores (1 = inactivo, 2 = activo), borrado de permisos retirados, insercion multiple de
     * permisos nuevos y orden de la transaccion.
     *
     * @return array Resultado por caso
     */
    public function controladorEscrituraTest(){

        $results = [];

        $this->firmaMetodo($results, 'Update', 1, 1);
        foreach (['Insert', 'Delete', 'New', 'View', 'GetID'] as $metodo) {
            $this->addCase($results, 'sin_'.$metodo, !method_exists(self::CONTROLADOR, $metodo),
                'el controlador expone '.$metodo.'(), que no forma parte de este modulo');
        }
        $update = $this->cuerpoMetodo('Update');

        /*====================================*/
        // Verbo HTTP
        /*====================================*/
        $this->addCase($results, 'update_verbo_post', str_contains($update, "REQUEST_METHOD'] !== 'POST'"),
            'Update() no valida el verbo HTTP POST');
        $this->addCase($results, 'update_verbo_405', str_contains($update, "Response::error('Error en el Request Method', 405)"),
            'Update() no responde 405 ante un verbo invalido');
        $this->addCase($results, 'update_verbo_antes_de_leer_post',
            (int)strpos($update, 'REQUEST_METHOD') < (int)strpos($update, '$_POST['),
            'Update() debe validar el verbo antes de leer el POST');

        /*====================================*/
        // Lectura de las maquinas de la entidad y del estado actual de cada permiso
        /*====================================*/
        $this->addCase($results, 'update_maquinas_de_la_entidad',
            str_contains($update, "'table'   => 'terceros_entidades_listado_maquinas'") && str_contains($update, "'where'   => 'idEntidad = ?'")
            && str_contains($update, "'params'  => [\$_POST['idEntidad']]"),
            'Update() no limita las maquinas a la entidad con parametro preparado');
        $this->addCase($results, 'update_es_activo', str_contains($update, 'AS IsActivo'),
            'Update() no calcula el estado actual de cada permiso (IsActivo)');

        /*====================================*/
        // Interruptores: 1 = inactivo (borra si existia), 2 = activo (crea si no existia)
        /*====================================*/
        $this->addCase($results, 'update_lee_interruptor', str_contains($update, "\$_POST['switch_'.\$permisos['idMaq']]"),
            'Update() no lee el interruptor switch_<idMaq> de cada maquina');
        $this->addCase($results, 'update_semantica_interruptores',
            (bool)preg_match('/case 1:.*?case 2:/s', $update),
            'Update() no distingue el interruptor inactivo (1) del activo (2)');
        $this->addCase($results, 'update_borra_permiso_retirado',
            str_contains($update, "'table'       => 'terceros_entidades_listado_usuarios_maq'") && str_contains($update, "'where'       => 'idUsuario,idMaquina'")
            && str_contains($update, '$this->Base_delete('),
            'Update() no borra el permiso por idUsuario e idMaquina cuando se desactiva');
        $this->addCase($results, 'update_borrado_valida_ids',
            substr_count($update, "isValidDecrypted(") >= 2 && str_contains($update, "'files'       => ''"),
            'Update() no valida los IDs cifrados antes de borrar');
        $this->addCase($results, 'update_inserta_permisos_nuevos',
            str_contains($update, "'data'      => 'idUsuario,idMaquina'") && str_contains($update, "'required'  => 'idUsuario,idMaquina'")
            && str_contains($update, "'rows'      => \$rowsPermisosNuevos") && str_contains($update, '$this->Base_insertMultiple('),
            'Update() no inserta los permisos nuevos con una sola sentencia (Base_insertMultiple)');
        $this->addCase($results, 'update_inserta_solo_si_hay_filas', str_contains($update, 'if ($rowsPermisosNuevos){'),
            'Update() intenta insertar sin filas nuevas');
        $this->addCase($results, 'update_no_duplica_permisos',
            (bool)preg_match('/case 2:.*?switch \(\$permisos\[\'IsActivo\'\]\).*?case 0:.*?\$rowsPermisosNuevos\[\]/s', $update),
            'Update() debe crear el permiso solo cuando aun no existe (IsActivo = 0)');

        /*====================================*/
        // Transaccion: se abre antes de escribir y se confirma al final
        /*====================================*/
        $posBegin   = (int)strpos($update, 'Base_transactionBegin()');
        $posDelete  = (int)strpos($update, '$this->Base_delete(');
        $posInsert  = (int)strpos($update, '$this->Base_insertMultiple(');
        $posCommit  = (int)strpos($update, 'Base_transactionCommit()');
        $posSuccess = (int)strpos($update, 'Response::success(true)');
        $this->addCase($results, 'update_transaccion_completa',
            $posBegin > 0 && $posDelete > 0 && $posInsert > 0 && $posCommit > 0 && $posSuccess > 0,
            'Update() debe usar Begin, Base_delete, Base_insertMultiple, Commit y Response::success(true)');
        $this->addCase($results, 'update_transaccion_orden',
            $posBegin < $posDelete && $posDelete < $posInsert && $posInsert < $posCommit && $posCommit < $posSuccess,
            'Update() ejecuta la transaccion en un orden incorrecto');
        $this->addCase($results, 'update_revierte_en_ambas_escrituras', substr_count($update, 'failOperation(') === 2,
            'Update() debe revertir con failOperation tanto si falla el borrado como la insercion');

        /*====================================*/
        // dataCheck_1 devuelve el contrato esperado por Base_insertMultiple
        /*====================================*/
        try {
            $reflector = new ReflectionMethod(self::CONTROLADOR, 'dataCheck_1');
            $reflector->setAccessible(true);
            $Controller = (new ReflectionClass(self::CONTROLADOR))->newInstanceWithoutConstructor();
            $DataCheck  = $reflector->invoke($Controller, '');
            $this->addCase($results, 'datacheck_es_arreglo', is_array($DataCheck), 'dataCheck_1() no devolvio un arreglo');
            $this->addCase($results, 'datacheck_numero_entero',
                (string)($DataCheck['ValidarNumero'] ?? '') === 'idUsuario,idMaquina' && (string)($DataCheck['ValidarEntero'] ?? '') === 'idUsuario,idMaquina',
                'dataCheck_1() no valida idUsuario e idMaquina como enteros');
            $this->addCase($results, 'datacheck_privado', $reflector->isPrivate(), 'dataCheck_1() debe ser privado');
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_es_arreglo', false,
                'no se pudo ejecutar dataCheck_1() ('.$e->getMessage().')');
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                       CONTROLADOR: RUTAS DEL INSTALADOR                    */
    /******************************************************************************/
    /**
     * Compara las rutas que registra `tercerosEntidadesInstaller::getRouteDefinitions()` para este
     * controlador con las esperadas (verbo, handler, nivel) y comprueba el contrato con el
     * JavaScript que abre la ventana (`tabUsuariosEditMaq` en `tercerosEntidadesListado-Resumen.php`)
     * y con el formulario. NO se ejecuta `InstallModule()`.
     *
     * @return array Resultado por caso
     */
    public function controladorRutasTest(){

        $results = [];

        try {
            $rutas = $this->rutasInstalador();
        } catch (Throwable $e) {
            $this->addCase($results, 'instalador_rutas_legibles', false,
                'no se pudieron leer las rutas del instalador ('.$e->getMessage().')');
            return $results;
        }
        $this->addCase($results, 'instalador_rutas_legibles', count($rutas) > 0,
            'el instalador no registra rutas para '.self::CONTROLADOR);

        /*====================================*/
        // Rutas esperadas: 'verbo ruta' => [handler, nivel]  (1=GET, 2=POST)
        /*====================================*/
        $esperadas = [
            '1 '.self::RUTA_UMAQ.'/updateList/@idEntidad/@idUsuario' => [self::CONTROLADOR.'->UpdateList', 2],
            '2 '.self::RUTA_UMAQ.'/update'                           => [self::CONTROLADOR.'->Update', 2],
        ];
        foreach ($esperadas as $clave => $esperado) {
            $this->addCase($results, 'ruta_registrada_'.$clave, isset($rutas[$clave]),
                'el instalador no registra la ruta '.$clave);
            if (!isset($rutas[$clave])) {
                continue;
            }
            $this->addCase($results, 'ruta_handler_'.$clave, $rutas[$clave][0] === $esperado[0],
                'la ruta '.$clave.' apunta a '.$rutas[$clave][0].' en vez de '.$esperado[0]);
            $this->addCase($results, 'ruta_nivel_'.$clave, $rutas[$clave][1] === $esperado[1],
                'la ruta '.$clave.' exige nivel '.$rutas[$clave][1].' en vez de '.$esperado[1]);
        }
        $this->addCase($results, 'rutas_sin_extras', count($rutas) === count($esperadas),
            'el instalador registra '.count($rutas).' rutas y se esperaban '.count($esperadas));
        $this->addCase($results, 'rutas_sin_borrado', count(array_filter(array_keys($rutas), static fn($k) => str_starts_with($k, '3 '))) === 0,
            'se registra una ruta DELETE para un controlador sin borrado');

        $existenMetodos = true;
        foreach ($rutas as [$handler, $nivel]) {
            [$clase, $metodo] = explode('->', $handler) + [1 => ''];
            if ($clase !== self::CONTROLADOR || !method_exists($clase, $metodo) || !in_array($nivel, self::NIVELES, true)) {
                $existenMetodos = false;
            }
        }
        $this->addCase($results, 'rutas_handlers_existen_y_niveles', $existenMetodos,
            'una ruta apunta a un metodo inexistente o usa un nivel fuera del catalogo 1..4');

        /*====================================*/
        // Instalador: el controlador forma parte del permiso raiz y la tabla existe
        /*====================================*/
        $instalador = $this->codigoFuente($this->rutaModulo('../installer/'.self::INSTALADOR.'.php'));
        $this->addCase($results, 'instalador_lista_controlador', str_contains($instalador, '"'.self::CONTROLADOR.'"'),
            'RutaController() del instalador no incluye '.self::CONTROLADOR);
        $this->addCase($results, 'instalador_tabla', str_contains($instalador, "'table'      => 'terceros_entidades_listado_usuarios_maq'"),
            'el instalador no crea la tabla terceros_entidades_listado_usuarios_maq');
        foreach (['idPermiso', 'idUsuario', 'idMaquina'] as $columna) {
            $this->addCase($results, 'instalador_columna_'.$columna, str_contains($instalador, '`'.$columna.'`'),
                'la tabla de permisos de maquinas no declara la columna '.$columna);
        }

        /*====================================*/
        // Contrato con el JavaScript de Resumen que abre la ventana
        /*====================================*/
        $resumen = $this->codigoFuente($this->rutaVista('Resumen'));
        $this->addCase($results, 'js_funcion_abre_ventana', str_contains($resumen, 'function tabUsuariosEditMaq(idEntidad, idUsuario)'),
            'Resumen no define tabUsuariosEditMaq(idEntidad, idUsuario)');
        $this->addCase($results, 'js_endpoint_dos_ids',
            str_contains($resumen, "/usuariosMaq/updateList/'; ?>'+idEntidad+'/'+idUsuario"),
            'Resumen no arma la URL updateList con idEntidad e idUsuario en ese orden');

        /*====================================*/
        // Contrato con el formulario: nombre del interruptor y endpoint
        /*====================================*/
        $vista = $this->codigoFuente($this->rutaVista('Resumen-Usuarios-Maquinas-formEdit'));
        $this->addCase($results, 'form_interruptor_coincide',
            str_contains($vista, "'switch_'.\$crud['idMaq']") && str_contains($this->cuerpoMetodo('Update'), "'switch_'.\$permisos['idMaq']"),
            'el nombre del interruptor del formulario no coincide con el que lee Update()');
        $this->addCase($results, 'form_endpoint_update', str_contains($vista, "/usuariosMaq/update'"),
            'el formulario no publica en /usuariosMaq/update');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                PERMISOS                                    */
    /******************************************************************************/
    /**
     * Verifica la sesion del usuario, el nivel de acceso (permiso compartido con
     * `tercerosEntidadesListado`) y el registro de las rutas en `SESSION.arrPermisos` y en el
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
        // La sesion trae el identificador del usuario que escribe (lo usa Update)
        /*====================================*/
        $userId = $f3->get('SESSION.DataInfo.UserID');
        $this->addCase($results, 'sesion_user_id_numerico', $userId === null || (is_numeric($userId) && (int)$userId > 0),
            'SESSION.DataInfo.UserID no es un identificador numerico positivo');

        /*====================================*/
        // Registro dinamico de la ruta raiz desde SESSION.arrPermisos
        /*====================================*/
        $arrPermisos = $f3->get('SESSION.arrPermisos');
        $this->addCase($results, 'permisos_de_ruta_presentes', is_array($arrPermisos),
            'SESSION.arrPermisos no esta disponible');

        if (is_array($arrPermisos)) {
            $rutaOk = false;
            foreach ($arrPermisos as $permiso) {
                if (isset($permiso['RutaWeb']) && $permiso['RutaWeb'] === self::RUTA_WEB_LISTADO) {
                    $rutaOk = true;
                    break;
                }
            }
            $this->addCase($results, 'ruta_registrada', $rutaOk,
                'la ruta '.self::RUTA_WEB_LISTADO.' no esta entre los permisos del usuario');
        }

        /*====================================*/
        // Rutas del controlador en el router de F3 con el verbo y handler esperados
        // (solo se evaluan las registradas: el router se arma con SESSION.arrPermisos)
        /*====================================*/
        $arrRoutes = $f3->get('ROUTES');
        $this->addCase($results, 'router_disponible', is_array($arrRoutes),
            'F3 no expuso la variable ROUTES');

        $esquema = [
            'ruta_lista_router'  => ['/'.self::RUTA_UMAQ.'/updateList/@idEntidad/@idUsuario', 'GET',  'UpdateList'],
            'ruta_update_router' => ['/'.self::RUTA_UMAQ.'/update',                           'POST', 'Update'],
        ];
        foreach ($esquema as $caso => [$ruta, $verbo, $metodo]) {
            if (!is_array($arrRoutes) || !isset($arrRoutes[$ruta])) {
                continue;
            }
            $handler = $arrRoutes[$ruta][0][$verbo][0] ?? '';
            $this->addCase($results, $caso, $handler === self::CONTROLADOR.'->'.$metodo,
                'la ruta '.$ruta.' ('.$verbo.') apunta a '.var_export($handler, true).' en vez de '.self::CONTROLADOR.'->'.$metodo);
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                        VISTA DEL FORMULARIO DE PERMISOS                    */
    /******************************************************************************/
    /**
     * Renderiza `...-Resumen-Usuarios-Maquinas-formEdit.php` con datos sinteticos y comprueba el
     * titulo con el nombre del usuario, un interruptor por maquina (valor oculto 1 + casilla 2, marcada
     * solo si ya existe el permiso), los campos ocultos y el endpoint de guardado.
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function vistasFormularioTest($f3){

        $results = [];
        $BASE    = (string)$f3->get('BASE');
        if (trim($BASE) === '') {
            $BASE = '/admin';
        }
        $ruta = $this->rutaVista('Resumen-Usuarios-Maquinas-formEdit');

        /*====================================*/
        // Estructura del formulario
        /*====================================*/
        $html = $this->renderizarVista($ruta, $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'form_render', $html !== '', 'la vista formEdit no produjo HTML');
        $this->addCase($results, 'form_id', str_contains($html, 'id="FormEditUsuariosPermisos"'),
            'el formulario no usa el id FormEditUsuariosPermisos');
        $this->addCase($results, 'form_titulo_usuario', str_contains($html, 'Ana Demo'),
            'el titulo no menciona el nombre del usuario');
        $this->addCase($results, 'form_cabeceras', str_contains($html, 'Nombre') && str_contains($html, 'Permiso'),
            'la tabla no declara las cabeceras Nombre y Permiso');
        $this->addCase($results, 'form_maquinas_pintadas',
            str_contains($html, 'Maquina Demo A') && str_contains($html, 'Maquina Demo B') && str_contains($html, 'Maquina Demo C'),
            'el formulario no lista las maquinas de la entidad');

        /*====================================*/
        // Un interruptor por maquina: oculto 1 (inactivo) + casilla 2 (activo)
        /*====================================*/
        foreach ([11, 12, 13] as $idMaq) {
            $this->addCase($results, 'form_interruptor_oculto_'.$idMaq,
                (bool)preg_match('/type="hidden"\s+value="1"\s+name="switch_'.$idMaq.'"/', $html),
                'falta el valor oculto 1 del interruptor switch_'.$idMaq);
            $this->addCase($results, 'form_interruptor_casilla_'.$idMaq,
                (bool)preg_match('/type="checkbox"\s+value="2"\s+name="switch_'.$idMaq.'"/', $html),
                'falta la casilla con valor 2 del interruptor switch_'.$idMaq);
        }
        $this->addCase($results, 'form_marcadas_segun_permiso',
            (bool)preg_match('/name="switch_11" checked/', $html) && !preg_match('/name="switch_12" checked/', $html)
            && (bool)preg_match('/name="switch_13" checked/', $html),
            'las casillas no reflejan el permiso actual (11 y 13 activas, 12 inactiva)');
        $this->addCase($results, 'form_total_interruptores', substr_count($html, 'type="checkbox"') === 3,
            'debe haber exactamente una casilla por maquina');

        /*====================================*/
        // Campos ocultos y endpoint
        /*====================================*/
        $this->addCase($results, 'form_oculto_entidad', (bool)preg_match('/name="idEntidad"[^>]*value="91"|value="91"[^>]*name="idEntidad"/', $html),
            'el formulario no envia idEntidad como campo oculto');
        $this->addCase($results, 'form_oculto_usuario', (bool)preg_match('/name="idUsuario"[^>]*value="5"|value="5"[^>]*name="idUsuario"/', $html),
            'el formulario no envia idUsuario (el usuario cuyos permisos se editan) como campo oculto');
        $this->addCase($results, 'form_endpoint',
            str_contains($html, self::RUTA_UMAQ.'/update') && str_contains($html, "Metodo      = 'POST'"),
            'el formulario no publica en '.self::RUTA_UMAQ.'/update con POST');
        $this->addCase($results, 'form_cierra_modal', str_contains($html, "closeModal:'#viewModal-xl'"),
            'el formulario no cierra el modal al guardar');
        $this->addCase($results, 'form_botones', str_contains($html, 'data-bs-dismiss="modal"') && str_contains($html, 'Guardar Cambios'),
            'el formulario no ofrece cerrar y guardar');

        /*====================================*/
        // Subtitulo segun la opcion del sistema
        /*====================================*/
        $htmlSub = $this->renderizarVista($ruta, $this->datosSinteticos(2, 2), $BASE, $f3);
        $this->addCase($results, 'form_subtitulo', str_contains($htmlSub, 'modal-subtitle') && str_contains($htmlSub, 'Ana Demo'),
            'el formulario no dibuja el subtitulo con el nombre del usuario cuando la opcion lo activa');
        $this->addCase($results, 'form_sin_subtitulo', !str_contains($html, 'modal-subtitle'),
            'el formulario dibuja el subtitulo cuando la opcion lo desactiva');

        /*====================================*/
        // Sin maquinas: la tabla se dibuja vacia y el formulario sigue siendo valido
        /*====================================*/
        $vacio = $this->datosSinteticos(2);
        $vacio['arrPermisos'] = [];
        $htmlVacio = $this->renderizarVista($ruta, $vacio, $BASE, $f3);
        $this->addCase($results, 'form_vacio_render', str_contains($htmlVacio, '<tbody>') && !str_contains($htmlVacio, 'type="checkbox"'),
            'la tabla sin maquinas debe dibujarse sin interruptores');
        $this->addCase($results, 'form_vacio_conserva_ocultos', str_contains($htmlVacio, 'name="idEntidad"') && str_contains($htmlVacio, 'name="idUsuario"'),
            'el formulario sin maquinas pierde los campos ocultos');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                 SEGURIDAD                                  */
    /******************************************************************************/
    /**
     * Revisa estaticamente la vista y el controlador: ausencia de rutas relativas maliciosas, rutas
     * absolutas, acceso a la sesion y SQL en la vista, y el origen de cada valor que el controlador
     * concatena en una consulta (solo se admiten enteros ya validados o el ID de la sesion).
     *
     * @return array Resultado por caso
     */
    public function seguridadVistasTest(){

        $results = [];

        foreach (self::VISTAS_PROPIAS as $vista) {
            $ruta   = $this->rutaVista($vista);
            $nombre = basename($ruta);
            $codigo = $this->codigoFuente($ruta);

            $this->addCase($results, 'vista_existe_'.$nombre, is_file($ruta), 'falta la vista '.$nombre);
            $this->addCase($results, 'vista_sin_retroceso_'.$nombre, !preg_match('#(?:\.\./|\.\.\\\\)#', $codigo),
                $nombre.' construye rutas con retroceso');
            $this->addCase($results, 'vista_sin_ruta_absoluta_'.$nombre,
                !preg_match('#(?:/var/www|/home/|[A-Za-z]:\\\\)#', $codigo),
                $nombre.' fija una ruta absoluta del servidor');
            $this->addCase($results, 'vista_sin_sesion_'.$nombre, !str_contains($codigo, '$_SESSION'),
                $nombre.' accede a la sesion de forma directa');
            $this->addCase($results, 'vista_sin_sql_'.$nombre, !preg_match('/\b(SELECT|INSERT|UPDATE|DELETE)\s+\w+\s+FROM\b/i', $codigo),
                $nombre.' contiene una consulta SQL embebida');
            $this->addCase($results, 'vista_sin_superglobales_'.$nombre, !preg_match('/\$_(POST|GET|REQUEST|COOKIE|SERVER)\b/', $codigo),
                $nombre.' lee superglobales de la peticion');
        }

        /*====================================*/
        // Controlador: valores concatenados en consultas (deberian ir como parametros preparados)
        // Se admiten solo los que el propio controlador garantiza: un entero validado o el ID de sesion.
        /*====================================*/
        $controlador = $this->fuenteControlador();
        preg_match_all("/'\s*\.\s*(\\\$[A-Za-z_]+(?:\[[^\]]+\])*)\s*\.\s*'/", $controlador, $m);
        $concatenados = array_values(array_unique($m[1]));
        $admitidos    = ["\$UsuarioID['data']", "\$_POST['idUsuario']"];
        $this->addCase($results, 'controlador_concatenados_conocidos', array_diff($concatenados, $admitidos) === [],
            'el controlador concatena valores no previstos en una consulta: '.implode(',', array_diff($concatenados, $admitidos)));

        $lista = $this->cuerpoMetodo('UpdateList');
        $this->addCase($results, 'controlador_lista_concat_validado',
            !str_contains($lista, "'.\$UsuarioID['data'].'")
            || ((int)strpos($lista, "isValidDecrypted(\$UsuarioID, 'id')") > 0
                && (int)strpos($lista, "isValidDecrypted(\$UsuarioID, 'id')") < (int)strpos($lista, "'.\$UsuarioID['data'].'")),
            'UpdateList() concatena UsuarioID antes de validarlo');
        $update = $this->cuerpoMetodo('Update');
        $posAsigna = (int)strpos($update, "\$_POST['idUsuario'] = ");
        $posUso    = (int)strpos($update, "'.\$_POST['idUsuario'].'");
        $this->addCase($results, 'controlador_update_concat_de_sesion',
            $posUso === 0 || ($posAsigna > 0 && $posAsigna < $posUso && str_contains($update, "\$_POST['idUsuario'] = \$f3->get('SESSION.DataInfo.UserID')")),
            'Update() concatena idUsuario del POST sin haberlo reemplazado antes por un valor de sesion');

        $this->addCase($results, 'controlador_parametros_preparados', str_contains($controlador, "'params'"),
            'el controlador no usa parametros preparados');
        $this->addCase($results, 'controlador_sin_acceso_session', !str_contains($controlador, '$_SESSION'),
            'el controlador lee $_SESSION directamente en vez de $f3->get() / getUserData()');
        $this->addCase($results, 'controlador_sin_eval', !preg_match('/\b(eval|exec|shell_exec|system|passthru)\s*\(/', $controlador),
            'el controlador usa funciones de ejecucion de codigo');

        /************************************/
        // Retorno los datos
        return $results;

    }

}
