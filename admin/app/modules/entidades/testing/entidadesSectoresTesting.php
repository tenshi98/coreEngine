<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class entidadesSectoresTesting
 *
 * Pruebas del modulo `entidades` - controlador `entidadesSectores` (CRUD de sectores de entidades).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. Los textos de los casos
 *    correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Alcance: pruebas NO destructivas. `Insert()`, `Update()` y `Delete()` escriben en base de
 * datos y terminan en `Response::*()` (`exit`); NO se invocan: se verifican por reflexion y
 * lectura del fuente. Solo se ejecuta logica pura (`dataCheck`, cifrado de IDs).
 *
 * Es un CRUD normal (permiso propio `mantencionSistema/entidades/sectores`, controlador y vistas
 * `entidadesSectores-*`) de una sola tabla, sin transacciones ni archivos.
 *
 * @see skills/testing/SKILL.md
 */
class entidadesSectoresTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR      = 'entidadesSectores';
    private const RUTA_WEB         = 'mantencionSistema/entidades/sectores';
    private const RUTA_WEB_LISTADO = 'mantencionSistema/entidades/sectores/listAll';
    private const RUTA_WEB_SEARCH  = 'mantencionSistema/entidades/sectores/search';
    // Niveles de permiso del catalogo (1=ver, 2=editar, 3=crear, 4=borrar)
    private const NIVELES          = [1, 2, 3, 4];

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
    // Contrato de presentacion: el controlador compone la vista como
    // $this->controllerName.'-<sufijo>.php' y la sirve con el tipo indicado
    /*******************************************************************/
    private function contratoPresentacion(array &$results, string $metodo, string $sufijoVista, string $tipoVista){
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'vista_'.$metodo, str_contains($codigo, "\$this->controllerName.'-".$sufijoVista.".php'"),
            $metodo.'() no instancia la vista '.self::CONTROLADOR.'-'.$sufijoVista);
        $this->addCase($results, 'vista_existe_'.$metodo, is_file($this->rutaModulo('../views/'.self::CONTROLADOR.'-'.$sufijoVista.'.php')),
            'no existe el archivo de la vista '.self::CONTROLADOR.'-'.$sufijoVista);
        $this->addCase($results, 'tipoview_'.$metodo,
            (bool)preg_match("/showVista\(".$tipoVista.",[^;]*-".preg_quote($sufijoVista, '/')."\.php'\)/", $codigo),
            $metodo.'() no sirve la vista '.$sufijoVista.' con showVista('.$tipoVista.')');
    }

    /*******************************************************************/
    // Rutas del instalador: [idMetodo.' '.RutaWeb => [handler, nivel, controller]]
    /*******************************************************************/
    private function rutasInstalador(): array{
        $rutas = [];
        if (!class_exists('entidadesInstaller')) {
            return $rutas;
        }
        try {
            $instalador = (new ReflectionClass('entidadesInstaller'))->newInstanceWithoutConstructor();
            foreach ([1, 2] as $tipo) {
                foreach ($instalador->getRouteDefinitions($tipo) as $ruta) {
                    $rutas[$ruta['idMetodo'].' '.$ruta['RutaWeb']] = [
                        $ruta['RutaController'],
                        (int)$ruta['idLevelLimit'],
                        $ruta['Controller'],
                    ];
                }
            }
        } catch (Throwable $e) {
            return [];
        }
        return $rutas;
    }

    /*******************************************************************/
    // Datos sinteticos usados por las vistas del controlador
    /*******************************************************************/
    private function datosSinteticos(int $nivel): array{
        return [
            'PageTitle'        => 'Listado Sectores',
            'PageDescription'  => 'Listado Sectores.',
            'PageAuthor'       => 'Pruebas',
            'PageKeywords'     => 'Pruebas',
            'TableTitle'       => 'Listado Sectores',
            'UserData'         => [
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
            'arrList'           => [['idSector' => 21, 'Nombre' => 'Sector Prueba']],
            'rowData'           => ['idSector' => 21, 'Nombre' => 'Sector Prueba'],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica los archivos del controlador `entidadesSectores` y la convencion
     * "clase = nombre de archivo" exigida por el AUTOLOAD de Fat-Free.
     *
     * @return array Resultado por caso
     */
    public function estructuraModuloTest(){

        $results = [];

        /*====================================*/
        // Archivos obligatorios
        /*====================================*/
        $arrArchivos = [
            'controlador'       => $this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'),
            'vista_list'        => $this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php'),
            'vista_update_list' => $this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'),
            'vista_view'        => $this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'),
            'vista_form_new'    => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formNew.php'),
            'vista_form_edit'   => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formEdit.php'),
            'vista_form_search' => $this->rutaModulo('../views/'.self::CONTROLADOR.'-formSearch.php'),
            'testing'           => $this->rutaModulo('entidadesSectoresTesting.php'),
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
            $this->addCase($results, 'htaccess_'.$nombre,
                (stripos((string)file_get_contents($ruta), 'deny from all') !== false),
                'el archivo no contiene "deny from all"');
        }

        /*====================================*/
        // Convencion clase = nombre de archivo y herencias
        /*====================================*/
        $this->addCase($results, 'clase_controlador_cargada', class_exists(self::CONTROLADOR),
            'la clase '.self::CONTROLADOR.' no esta disponible');
        $this->addCase($results, 'clase_testing_cargada', class_exists('entidadesSectoresTesting'),
            'la clase entidadesSectoresTesting no esta disponible');
        $padre = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padre === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padre.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('entidadesSectoresTesting') === 'ControllerBase',
            'el testing no extiende ControllerBase');

        /*====================================*/
        // Constructor del controlador
        /*====================================*/
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'constructor_conexion', str_contains($codigo, 'Database::getSQLConnection(ConfigDataBase::MySQL_1)'),
            'el constructor no obtiene la conexion MySQL_1');
        $this->addCase($results, 'constructor_padre', str_contains($codigo, 'parent::__construct($DB_conn_1, $queryBuilder, $checkData)'),
            'el constructor no delega en ControllerBase');
        $this->addCase($results, 'constructor_nombre', str_contains($codigo, "\$this->controllerName = '".self::CONTROLADOR."'"),
            'controllerName no coincide con '.self::CONTROLADOR);

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                            CONTROLADOR: VISTAS                             */
    /******************************************************************************/
    /**
     * Verifica el contrato de los metodos que devuelven HTML (`listAll`, `UpdateList`,
     * `View`, `GetID`) y de la busqueda por nombre.
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Firma de cada metodo de vista
        /*====================================*/
        $this->firmaMetodo($results, 'listAll', 1, 1);
        $this->firmaMetodo($results, 'UpdateList', 1, 1);
        $this->firmaMetodo($results, 'View', 2, 2);
        $this->firmaMetodo($results, 'GetID', 2, 2);

        /*====================================*/
        // Contrato de presentacion
        /*====================================*/
        $this->contratoPresentacion($results, 'listAll', 'List', '1');
        $this->contratoPresentacion($results, 'UpdateList', 'UpdateList', '2');
        $this->contratoPresentacion($results, 'View', 'View', '2');
        $this->contratoPresentacion($results, 'GetID', 'formEdit', '2');

        $this->addCase($results, 'rutavista_resuelta', substr_count($codigo, 'returnRutaVista(__DIR__') === 4,
            'las 4 vistas deben resolver su ruta con returnRutaVista()');
        $this->addCase($results, 'errores_showerror', substr_count($codigo, 'showError(') === 4,
            'las 4 vistas deben manejar el fallo de consulta con showError()');
        $this->addCase($results, 'fallback_mergeresponses', substr_count($codigo, 'mergeResponses(') === 4,
            'las 4 vistas deben agrupar respuestas con mergeResponses()');
        $this->addCase($results, 'usuario_en_vistas', substr_count($codigo, 'getUserData($f3)') === 4,
            'las 4 vistas deben obtener los datos del usuario');
        $this->addCase($results, 'nivel_en_vistas', substr_count($codigo, 'getArrLevel($f3, $this->controllerName)') === 4,
            'las 4 vistas deben resolver el nivel de acceso');
        $this->addCase($results, 'listado_titulo', str_contains($codigo, "'PageTitle'") && str_contains($codigo, "'TableTitle'"),
            'listAll() no asigna PageTitle y TableTitle');

        /*====================================*/
        // Consultas de lectura
        /*====================================*/
        $this->addCase($results, 'listado_tabla', substr_count($codigo, "'table'   => 'entidades_sectores'") === 4,
            'listAll, UpdateList, View y GetID deben consultar entidades_sectores');
        $this->addCase($results, 'listado_ordenado', substr_count($codigo, "'order'   => 'Nombre ASC'") === 2,
            'listAll() y UpdateList() deben ordenar por Nombre');
        $this->addCase($results, 'listado_limite', substr_count($codigo, 'ConfigAPP::APP["N_MaxItems"]') === 2,
            'listAll() y UpdateList() deben acotar el numero de filas con N_MaxItems');
        $this->addCase($results, 'detalle_por_sector', substr_count($codigo, "'where'   => 'idSector = ?'") === 2,
            'View() y GetID() deben consultar por idSector con parametros preparados');
        $this->addCase($results, 'getid_campos', str_contains($codigo, "'data'    => 'idSector,Nombre'"),
            'GetID() no consulta idSector y Nombre');

        /*====================================*/
        // Busqueda: columna, validacion de fechas y WHERE preparado
        /*====================================*/
        $this->addCase($results, 'busqueda_campo_texto', str_contains($codigo, "\$WhereData_string  = 'Nombre';"),
            'UpdateList() no filtra por Nombre');
        $this->addCase($results, 'busqueda_sin_exactos_ni_fechas',
            str_contains($codigo, "\$WhereData_int     = '';") && str_contains($codigo, "\$WhereData_between = '';"),
            'UpdateList() declara filtros exactos o de rango que el formulario no tiene');
        $this->addCase($results, 'busqueda_valida_fechas', str_contains($codigo, 'searchValidateDates('),
            'UpdateList() no valida las fechas con searchValidateDates()');
        $this->addCase($results, 'busqueda_where_preparado', substr_count($codigo, '$this->searchWhere(') === 3,
            'UpdateList() no construye el WHERE con los tres tipos de searchWhere()');
        $this->addCase($results, 'busqueda_params', str_contains($codigo, "'params'  => \$whereParams"),
            'UpdateList() no pasa los parametros preparados del WHERE');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                         CONTROLADOR: ESCRITURA (CRUD)                      */
    /******************************************************************************/
    /**
     * Verifica `Insert`, `Update` y `Delete` SIN ejecutarlos: verbo HTTP, chequeo de datos,
     * unicidad del nombre, delimitacion por idSector y respuestas al cliente. El CRUD
     * escribe en una sola tabla, por lo que no abre transaccion.
     *
     * @return array Resultado por caso
     */
    public function controladorEscrituraTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Firma de cada metodo de escritura (sin parametros)
        /*====================================*/
        foreach (['Insert', 'Update', 'Delete'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 0, 0);
        }

        /*====================================*/
        // Chequeo de datos, tabla y unicidad
        /*====================================*/
        $this->addCase($results, 'datacheck_post', substr_count($codigo, '$this->dataCheck($_POST)') === 2,
            'Insert() y Update() deben construir el DataCheck con dataCheck($_POST)');
        $this->addCase($results, 'tabla_sectores', preg_match_all("/'table'\s*=>\s*'entidades_sectores'/", $codigo) === 7,
            'se esperaban 7 usos de entidades_sectores (4 lecturas y Insert/Update/Delete)');
        $this->addCase($results, 'unicidad_nombre', substr_count($codigo, "'unique'    => 'Nombre'") === 2,
            'Insert() y Update() deben exigir un Nombre unico');
        $this->addCase($results, 'sin_codificacion', substr_count($codigo, "'encode'    => ''") === 2,
            'los sectores no tienen campos codificados');

        /*====================================*/
        // Insert
        /*====================================*/
        $this->addCase($results, 'insert_campos', str_contains($codigo, "'data'      => 'Nombre',"),
            'Insert() no declara solo el campo Nombre');
        $this->addCase($results, 'insert_requeridos', str_contains($codigo, "'required'  => 'Nombre'"),
            'Insert() no declara Nombre como obligatorio');
        $this->addCase($results, 'insert_base', str_contains($codigo, '$this->Base_insert('),
            'Insert() no escribe con Base_insert()');

        /*====================================*/
        // Update
        /*====================================*/
        $this->addCase($results, 'update_verbo_post', str_contains($codigo, "REQUEST_METHOD'] !== 'POST'"),
            'Update() no valida el verbo HTTP POST');
        $this->addCase($results, 'update_donde', str_contains($codigo, "'where'     => 'idSector'"),
            'Update() no delimita la escritura por idSector');
        $this->addCase($results, 'update_requeridos', str_contains($codigo, "'required'  => 'idSector,Nombre'"),
            'Update() no declara idSector y Nombre como obligatorios');
        $this->addCase($results, 'update_base', str_contains($codigo, '$this->Base_update('),
            'Update() no escribe con Base_update()');

        /*====================================*/
        // Delete
        /*====================================*/
        $this->addCase($results, 'delete_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'DELETE'"),
            'Delete() no valida el verbo HTTP DELETE');
        $this->addCase($results, 'delete_donde', str_contains($codigo, "'where'       => 'idSector'"),
            'Delete() no delimita el borrado por idSector');
        $this->addCase($results, 'delete_sin_archivos', str_contains($codigo, "'files'       => ''"),
            'Delete() no declara que el sector no maneja archivos');
        $this->addCase($results, 'delete_lee_cuerpo', str_contains($codigo, 'parse_str(file_get_contents("php://input")'),
            'Delete() no lee el cuerpo DELETE');
        $this->addCase($results, 'delete_base', str_contains($codigo, '$this->Base_delete('),
            'Delete() no borra con Base_delete()');

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

        /*====================================*/
        // Respuestas al cliente
        /*====================================*/
        $this->addCase($results, 'respuesta_exito', substr_count($codigo, 'Response::success(') === 3,
            'se esperaban 3 Response::success() (Insert, Update y Delete)');
        $this->addCase($results, 'respuesta_405', substr_count($codigo, "Response::error('Error en el Request Method', 405)") === 2,
            'Update() y Delete() deben responder 405 ante un verbo incorrecto');
        $this->addCase($results, 'respuesta_400', substr_count($codigo, "Response::error('Registro inválido', 400)") === 2,
            'View() y GetID() deben responder 400 ante un ID invalido');
        $this->addCase($results, 'respuesta_500', substr_count($codigo, 'failOperation(') === 3,
            'se esperaban 3 puntos failOperation (Insert, Update y Delete)');
        $this->addCase($results, 'id_descifrado_vistas', substr_count($codigo, "encryptDecrypt('decrypt', \$params['id'])") === 2,
            'View() y GetID() deben descifrar $params[\'id\']');
        $this->addCase($results, 'id_valido_vistas', substr_count($codigo, "isValidDecrypted(\$SectorID, 'id')") === 2,
            'View() y GetID() deben validar el ID con isValidDecrypted()');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                       CONTROLADOR: RUTAS E INSTALADOR                      */
    /******************************************************************************/
    /**
     * Verifica que las 8 rutas de `entidadesSectores` publicadas por el instalador
     * apunten a metodos reales con el verbo y el nivel esperados.
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
            'listado'   => ['1 '.self::RUTA_WEB.'/listAll',      'entidadesSectores->listAll',    1],
            'busqueda'  => ['2 '.self::RUTA_WEB.'/search',       'entidadesSectores->UpdateList', 1],
            'refrescar' => ['1 '.self::RUTA_WEB.'/updateList',   'entidadesSectores->UpdateList', 2],
            'ver'       => ['1 '.self::RUTA_WEB.'/view/@id',     'entidadesSectores->View',       1],
            'getid'     => ['1 '.self::RUTA_WEB.'/getID/@id',    'entidadesSectores->GetID',      2],
            'crear'     => ['2 '.self::RUTA_WEB,                 'entidadesSectores->Insert',     3],
            'editar'    => ['2 '.self::RUTA_WEB.'/update',       'entidadesSectores->Update',     2],
            'borrar'    => ['3 '.self::RUTA_WEB,                 'entidadesSectores->Delete',     4],
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

        $propias = array_filter($rutas, static function ($r) { return $r[2] === 'entidadesSectores'; });
        $this->addCase($results, 'rutas_propias_completas', count($propias) === 8,
            'se esperaban 8 rutas de '.self::CONTROLADOR.' y hay '.count($propias));

        /*====================================*/
        // Instalador: permiso propio y desinstalacion
        /*====================================*/
        $instalador = $this->codigoFuente($this->rutaModulo('../installer/entidadesInstaller.php'));
        $this->addCase($results, 'instalador_permiso',
            str_contains($instalador, "'RutaWeb'        => '".self::RUTA_WEB."'") && str_contains($instalador, "'RutaController' => 'entidadesSectores'"),
            'el instalador no declara el permiso con su ruta y controlador');
        $this->addCase($results, 'instalador_desinstala_controlador', str_contains($instalador, "\$RutaController  = '\"entidadesSectores\"'"),
            'RutaController() no lista entidadesSectores y dejaria rutas huerfanas al desinstalar');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                PERMISOS                                    */
    /******************************************************************************/
    /**
     * Verifica la sesion, el nivel de acceso del controlador y el registro de sus rutas
     * en el router de F3 (solo si el usuario de pruebas las tiene).
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function permisosSesionTest($f3){

        $results = [];

        $UserData = $f3->get('SESSION.DataInfo');
        $this->addCase($results, 'sesion_usuario_presente', is_array($UserData) && !empty($UserData),
            'no hay datos de usuario en la sesion');

        /*====================================*/
        // Lectura de la sesion desde el controlador
        /*====================================*/
        try {
            $this->addCase($results, 'get_user_data_arreglo', is_array($this->getUserData($f3)),
                'getUserData() no devolvio un arreglo');
            $arrLevel = $this->getArrLevel($f3, self::CONTROLADOR);
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
        // Rutas en SESSION.arrPermisos y en el router (solo si las tiene)
        /*====================================*/
        $arrPermisos = $f3->get('SESSION.arrPermisos');
        $this->addCase($results, 'permisos_de_ruta_presentes', $arrPermisos === null || is_array($arrPermisos),
            'SESSION.arrPermisos no es un arreglo');
        if (is_array($arrPermisos)) {
            $rutaOk = false;
            foreach ($arrPermisos as $permiso) {
                if (isset($permiso['RutaWeb']) && $permiso['RutaWeb'] === self::RUTA_WEB_LISTADO) {
                    $rutaOk = true;
                    break;
                }
            }
            $this->addCase($results, 'ruta_registrada', $rutaOk || $nivel === null,
                'el usuario tiene nivel '.var_export($nivel, true).' pero la ruta '.self::RUTA_WEB_LISTADO.' no esta en sus permisos');
        }

        $arrRoutes = $f3->get('ROUTES');
        $this->addCase($results, 'router_disponible', is_array($arrRoutes), 'F3 no expuso la variable ROUTES');
        $esquema = [
            'ruta_listado_router'  => ['/'.self::RUTA_WEB_LISTADO, 'GET',    self::CONTROLADOR.'->listAll'],
            'ruta_busqueda_router' => ['/'.self::RUTA_WEB_SEARCH,  'POST',   self::CONTROLADOR.'->UpdateList'],
            'ruta_insert_router'   => ['/'.self::RUTA_WEB,         'POST',   self::CONTROLADOR.'->Insert'],
            'ruta_delete_router'   => ['/'.self::RUTA_WEB,         'DELETE', self::CONTROLADOR.'->Delete'],
        ];
        if (is_array($arrRoutes)) {
            foreach ($esquema as $caso => $rutaEsperada) {
                [$ruta, $verbo, $handlerEsperado] = $rutaEsperada;
                if (!isset($arrRoutes[$ruta])) {
                    continue;
                }
                $handler = $arrRoutes[$ruta][0][$verbo][0] ?? '';
                $this->addCase($results, $caso, strcasecmp((string)$handler, $handlerEsperado) === 0,
                    'la ruta '.$ruta.' apunta a '.var_export($handler, true).' en vez de '.$handlerEsperado);
            }
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /**
     * Renderiza `List` y `UpdateList` con datos sinteticos y verifica la matriz de
     * permisos: Crear Nuevo con nivel >= 3, ver >= 1, editar >= 2 y borrar >= 4, el ID
     * cifrado, la tabla vacia y el escape de comillas hacia JavaScript.
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

        /*====================================*/
        // Estructura de la pagina de listado (nivel 1)
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php'),
            $this->datosSinteticos(1), $BASE, $f3);
        $this->addCase($results, 'listado_render', $html !== '', 'la vista List no produjo HTML');
        $this->addCase($results, 'listado_contenedor', str_contains($html, 'id="listTableData"'),
            'falta el contenedor de la tabla');
        $this->addCase($results, 'listado_form_busqueda', str_contains($html, 'id="FormSearchData"'),
            'no se dibuja el formulario de busqueda');
        $this->addCase($results, 'listado_form_nuevo', str_contains($html, 'id="newFormModal"'),
            'no se dibuja el modal de alta');
        $this->addCase($results, 'listado_boton_filtrar', str_contains($html, 'data-bs-target="#formSearch"'),
            'falta el boton que despliega el filtro');
        $this->addCase($results, 'listado_sin_crear_nivel1', !str_contains($html, 'Crear Nuevo</button>'),
            'con nivel 1 el listado ofrece crear registros');
        $this->addCase($results, 'listado_endpoint_ver', str_contains($html, '/view/'),
            'falta el endpoint de detalle');
        $this->addCase($results, 'listado_endpoint_edicion', str_contains($html, '/getID/'),
            'falta el endpoint del formulario de edicion');
        $this->addCase($results, 'listado_endpoint_actualizar', str_contains($html, '/updateList'),
            'falta el endpoint de refresco de tabla');
        $this->addCase($results, 'listado_borrado_verbo', str_contains($html, "let Metodo      = 'DELETE'"),
            'la accion de borrar no viaja con el verbo DELETE');
        $this->addCase($results, 'listado_borrado_id', str_contains($html, '{"idSector": ID}'),
            'la accion de borrar no envia idSector');

        $htmlNivel3 = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-List.php'),
            $this->datosSinteticos(3), $BASE, $f3);
        $this->addCase($results, 'listado_crear_nivel3', str_contains($htmlNivel3, 'Crear Nuevo</button>'),
            'con nivel 3 no se muestra el boton Crear Nuevo');
        $this->addCase($results, 'listado_modal_nivel3', str_contains($htmlNivel3, 'data-bs-target="#newFormModal"'),
            'el boton de alta no apunta al modal #newFormModal');

        /*====================================*/
        // Matriz de acciones sobre la tabla (UpdateList)
        /*====================================*/
        $rutaTabla = $this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php');
        foreach (self::NIVELES as $nivel) {
            $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos($nivel), $BASE, $f3);

            $this->addCase($results, 'tabla_render_nivel'.$nivel, $tabla !== '',
                'la vista UpdateList no produjo HTML con nivel '.$nivel);
            $this->addCase($results, 'tabla_ver_nivel'.$nivel,
                str_contains($tabla, 'listTableDataView(') === ($nivel >= 1),
                'la accion Ver no coincide con el nivel '.$nivel);
            $this->addCase($results, 'tabla_editar_nivel'.$nivel,
                str_contains($tabla, 'listTableDataEdit(') === ($nivel >= 2),
                'la accion Editar no coincide con el nivel '.$nivel);
            $this->addCase($results, 'tabla_borrar_nivel'.$nivel,
                str_contains($tabla, 'listTableDataDel(') === ($nivel >= 4),
                'la accion Borrar no coincide con el nivel '.$nivel);
            $this->addCase($results, 'tabla_datos_nivel'.$nivel, str_contains($tabla, 'Sector Prueba'),
                'la tabla no pinta el sector sintetico con nivel '.$nivel);
            $this->addCase($results, 'tabla_id_cifrado_nivel'.$nivel,
                !str_contains($tabla, "listTableDataView('21')") && !str_contains($tabla, "listTableDataEdit('21')"),
                'la tabla expone idSector sin cifrar con nivel '.$nivel);
        }
        $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos(4), $BASE, $f3);
        $this->addCase($results, 'tabla_columnas', str_contains($tabla, '>Nombre<') && str_contains($tabla, '>Acciones<'),
            'la tabla no declara las columnas Nombre y Acciones');

        $vacio = $this->datosSinteticos(4);
        $vacio['arrList'] = [];
        $tablaVacia = $this->renderizarVista($rutaTabla, $vacio, $BASE, $f3);
        $this->addCase($results, 'tabla_vacia', $tablaVacia !== '' && str_contains($tablaVacia, '<tbody>') && !str_contains($tablaVacia, 'listTableDataView('),
            'la tabla sin datos pinta filas o no se dibuja');

        $comillas = $this->datosSinteticos(4);
        $comillas['arrList'][0]['Nombre'] = "Sector d'Prueba";
        $tablaComillas = $this->renderizarVista($rutaTabla, $comillas, $BASE, $f3);
        $this->addCase($results, 'tabla_escapa_comillas', str_contains($tablaComillas, "Sector d\\'Prueba"),
            'la etiqueta del borrado no escapa las comillas simples');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                        VISTAS DE DETALLE Y FORMULARIOS                     */
    /******************************************************************************/
    /**
     * Renderiza `View`, `formSearch`, `formNew` y `formEdit` con datos sinteticos y
     * comprueba el contrato de ids, campos, endpoints y verbos.
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
        // Modal de detalle (View)
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-View.php'),
            $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'view_render', $html !== '', 'la vista View no produjo HTML');
        $this->addCase($results, 'view_datos_basicos', str_contains($html, 'Sector Prueba') && str_contains($html, 'Nombre'),
            'la vista View no pinta el nombre del sector');
        $this->addCase($results, 'view_pie_cierre', str_contains($html, 'data-bs-dismiss="modal"'),
            'la vista View no ofrece el pie de cierre del modal');

        /*====================================*/
        // Formulario de busqueda
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-formSearch.php'),
            $this->datosSinteticos(1), $BASE, $f3);
        $this->addCase($results, 'form_search_render', $html !== '', 'la vista formSearch no produjo HTML');
        $this->addCase($results, 'form_search_id', str_contains($html, 'id="FormSearchData"'),
            'el formulario de busqueda no usa el id FormSearchData');
        $this->addCase($results, 'form_search_inputs', str_contains($html, 'id="Search_Nombre"'),
            'el formulario de busqueda no declara Search_Nombre');
        $this->addCase($results, 'form_search_endpoint',
            str_contains($html, self::RUTA_WEB.'/search') && str_contains($html, "let Metodo      = 'POST'"),
            'el formulario de busqueda no apunta a /search con POST');

        /*====================================*/
        // Formulario de alta
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-formNew.php'),
            $this->datosSinteticos(3), $BASE, $f3);
        $this->addCase($results, 'form_new_render', $html !== '', 'la vista formNew no produjo HTML');
        $this->addCase($results, 'form_new_id', str_contains($html, 'id="FormNewData"'),
            'el formulario de alta no usa el id FormNewData');
        $this->addCase($results, 'form_new_modal', str_contains($html, 'id="newFormModal"'),
            'el formulario de alta no se monta en #newFormModal');
        $this->addCase($results, 'form_new_campos', str_contains($html, 'name="Nombre"'),
            'el formulario de alta no declara Nombre');
        $this->addCase($results, 'form_new_sin_idsector', !str_contains($html, 'name="idSector"'),
            'el formulario de alta no debe enviar idSector');
        $this->addCase($results, 'form_new_endpoint',
            str_contains($html, $BASE.'/'.self::RUTA_WEB."'") && str_contains($html, "let Metodo      = 'POST'"),
            'el formulario de alta no publica en '.self::RUTA_WEB.' con POST');
        $this->addCase($results, 'form_new_refresca_tabla', str_contains($html, '#listTableData') && str_contains($html, '/updateList'),
            'el formulario de alta no refresca la tabla al guardar');

        /*====================================*/
        // Formulario de edicion
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::CONTROLADOR.'-formEdit.php'),
            $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'form_edit_render', $html !== '', 'la vista formEdit no produjo HTML');
        $this->addCase($results, 'form_edit_id', str_contains($html, 'id="FormEditData"'),
            'el formulario de edicion no usa el id FormEditData');
        $this->addCase($results, 'form_edit_campos', str_contains($html, 'name="Nombre"') && str_contains($html, 'name="idSector"'),
            'el formulario de edicion no declara Nombre e idSector');
        $this->addCase($results, 'form_edit_precarga', str_contains($html, 'Sector Prueba'),
            'el formulario de edicion no precarga el sector');
        $this->addCase($results, 'form_edit_endpoint',
            str_contains($html, $BASE.'/'.self::RUTA_WEB.'/update') && str_contains($html, "let Metodo      = 'POST'"),
            'el formulario de edicion no publica en /update con POST');
        $this->addCase($results, 'form_edit_refresca_tabla', str_contains($html, '#listTableData') && str_contains($html, '/updateList'),
            'el formulario de edicion no refresca la tabla al guardar');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos `dataCheck()` del controlador, el cifrado de IDs y la
     * validacion de IDs descifrados.
     *
     * @return array Resultado por caso
     */
    public function logicaValidacionTest(){

        $results = [];

        /*====================================*/
        // dataCheck: contrato esperado por Base_insert/Base_update
        /*====================================*/
        try {
            $reflector = new ReflectionMethod(self::CONTROLADOR, 'dataCheck');
            $reflector->setAccessible(true);
            $Controller = (new ReflectionClass(self::CONTROLADOR))->newInstanceWithoutConstructor();

            $Post      = ['Nombre' => 'Sector Prueba'];
            $DataCheck = $reflector->invoke($Controller, $Post);

            $this->addCase($results, 'datacheck_es_arreglo', is_array($DataCheck), 'dataCheck() no devolvio un arreglo');
            $this->addCase($results, 'datacheck_privado', $reflector->isPrivate(), 'dataCheck() debe ser privado');
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post,
                'dataCheck() no conserva el POST recibido');
            $this->addCase($results, 'datacheck_claves_contrato',
                isset($DataCheck['emptyData'], $DataCheck['ValidarLargoMinimo'], $DataCheck['ValidarLargoMinimoN'], $DataCheck['ValidarLargoMaximoPorCampo']),
                'dataCheck() no entrega las claves del contrato de CheckData');
            $this->addCase($results, 'datacheck_largo_nombre',
                trim((string)$DataCheck['ValidarLargoMinimo']) === 'Nombre'
                && (int)($DataCheck['ValidarLargoMaximoPorCampo']['Nombre'] ?? 0) === 255
                && (int)($DataCheck['ValidarLargoMinimoN'] ?? 0) === 3,
                'dataCheck() no limita Nombre a 3..255 caracteres');
            $this->addCase($results, 'datacheck_censura', trim((string)$DataCheck['ValidarPalabrasCensuradas']) === 'Nombre',
                'dataCheck() no filtra palabras censuradas en Nombre');
            $this->addCase($results, 'datacheck_sin_otros_validadores',
                trim((string)$DataCheck['ValidarEmail']) === '' && trim((string)$DataCheck['ValidarNumero']) === '' && trim((string)$DataCheck['ValidarFecha']) === '',
                'dataCheck() valida email, numeros o fechas que el sector no tiene');
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_es_arreglo', false,
                'no se pudo ejecutar dataCheck() ('.$e->getMessage().')');
        }

        /*====================================*/
        // Cifrado de identificadores: ida y vuelta estable y rechazo de basura
        /*====================================*/
        try {
            $Codification = new FunctionsSecurityCodification();
            $cifrado      = $Codification->encryptDecrypt('encrypt', '21');
            $this->addCase($results, 'cifrado_genera_dato',
                ($cifrado['success'] ?? false) === true && !empty($cifrado['data']),
                'encryptDecrypt(encrypt) no produjo un valor cifrado');

            $descifrado = $Codification->encryptDecrypt('decrypt', (string)($cifrado['data'] ?? ''));
            $this->addCase($results, 'cifrado_ida_y_vuelta',
                ($descifrado['success'] ?? false) === true && (string)($descifrado['data'] ?? '') === '21',
                'el cifrado no devuelve el valor original');

            $basura = $Codification->encryptDecrypt('decrypt', 'valor-no-cifrado');
            $this->addCase($results, 'cifrado_rechaza_basura',
                ($basura['success'] ?? false) === false || !$this->isValidDecrypted($basura, 'id'),
                'un ID no cifrado se acepto como valido');
        } catch (Throwable $e) {
            $this->addCase($results, 'cifrado_ida_y_vuelta', false,
                'no se pudo ejecutar el cifrado ('.$e->getMessage().')');
        }

        /*====================================*/
        // isValidDecrypted: caminos de exito y rechazo
        /*====================================*/
        $this->addCase($results, 'id_valido_ok', $this->isValidDecrypted(['success' => true, 'data' => '7'], 'id') === true,
            'un ID numerico positivo debe ser valido');
        $this->addCase($results, 'id_valido_descifro_falla', $this->isValidDecrypted(['success' => false, 'data' => '7'], 'id') === false,
            'un descifrado fallido debe rechazarse');
        $this->addCase($results, 'id_valido_texto', $this->isValidDecrypted(['success' => true, 'data' => 'abc'], 'id') === false,
            'un ID no numerico debe rechazarse para tipo id');
        $this->addCase($results, 'id_valido_cero', $this->isValidDecrypted(['success' => true, 'data' => '0'], 'id') === false,
            'el ID 0 debe rechazarse para tipo id');
        $this->addCase($results, 'id_valido_negativo', $this->isValidDecrypted(['success' => true, 'data' => '-5'], 'id') === false,
            'un ID negativo debe rechazarse para tipo id');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                 SEGURIDAD                                  */
    /******************************************************************************/
    /**
     * Revisa estaticamente las vistas y el controlador: rutas relativas, rutas absolutas,
     * acceso a sesion, SQL embebido, cifrado de IDs, escape y consultas preparadas.
     *
     * @return array Resultado por caso
     */
    public function seguridadVistasTest(){

        $results = [];

        /*====================================*/
        // Vistas involucradas
        /*====================================*/
        $arrVistas = glob($this->rutaModulo('../views/'.self::CONTROLADOR.'-*.php')) ?: [];
        $this->addCase($results, 'hay_vistas', count($arrVistas) === 6,
            'se esperaban 6 vistas de '.self::CONTROLADOR.' y se encontraron '.count($arrVistas));

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
            $this->addCase($results, 'vista_sin_sql_'.$nombre,
                !preg_match('/\b(SELECT|INSERT|UPDATE|DELETE)\s+\w+\s+FROM\b/i', $codigo),
                $nombre.' contiene una consulta SQL embebida');
        }

        /*====================================*/
        // Escape y cifrado en la tabla
        /*====================================*/
        $tabla = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-UpdateList.php'));
        $this->addCase($results, 'tabla_escape_etiqueta', str_contains($tabla, "addslashes(\$crud['Nombre'])"),
            'UpdateList no escapa la etiqueta que viaja al JavaScript');
        $this->addCase($results, 'tabla_id_cifrado', str_contains($tabla, "encryptDecrypt('encrypt', \$crud['idSector'])"),
            'UpdateList no cifra idSector en las acciones');

        /*====================================*/
        // Controlador: SQL preparado y sin concatenacion de entrada
        /*====================================*/
        $controlador = $this->fuenteControlador();
        $this->addCase($results, 'controlador_sin_concat_sql', !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $controlador),
            'el controlador concatena superglobales en una cadena SQL');
        $this->addCase($results, 'controlador_parametros_preparados', preg_match_all("/'params'\s*=>/", $controlador) === 4,
            'las 4 consultas deben declarar la clave params (consultas preparadas)');
        $this->addCase($results, 'controlador_sin_get', !str_contains($controlador, '$_GET') && !str_contains($controlador, '$_REQUEST'),
            'el controlador lee $_GET/$_REQUEST; solo debe usar $_POST y parametros de ruta cifrados');

        /************************************/
        // Retorno los datos
        return $results;

    }

}
