<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class entidadesListadoContactosTesting
 *
 * Pruebas del modulo `entidades` - controlador `entidadesListadoContactos` (contactos de una entidad).
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
 * El controlador reutiliza los permisos y la ruta `entidadesListado` ($this->controllerName)
 * y las vistas `entidadesListado-Resumen-Contactos-*`; el CRUD no usa transacciones.
 *
 * @see skills/testing/SKILL.md
 */
class entidadesListadoContactosTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR          = 'entidadesListadoContactos';
    private const CONTROLADOR_PERMISOS = 'entidadesListado';
    private const VISTA_BASE           = 'entidadesListado-Resumen-Contactos';
    private const RUTA_WEB             = 'administracion/entidades/listado';
    private const RUTA_WEB_CONTACTOS      = 'administracion/entidades/listado/contactos';
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
    // $this->controllerName.'-Resumen-Contactos-<sufijo>.php' y la sirve como modal
    /*******************************************************************/
    private function contratoPresentacion(array &$results, string $metodo, string $sufijoVista){
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'vista_'.$metodo, str_contains($codigo, "\$this->controllerName.'-Resumen-Contactos-".$sufijoVista.".php'"),
            $metodo.'() no instancia la vista '.self::VISTA_BASE.'-'.$sufijoVista);
        $this->addCase($results, 'vista_existe_'.$metodo, is_file($this->rutaModulo('../views/'.self::VISTA_BASE.'-'.$sufijoVista.'.php')),
            'no existe el archivo de la vista '.self::VISTA_BASE.'-'.$sufijoVista);
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
            'UserData'         => [
                'MainPathUrl'          => '/admin/public/upload/',
                'sistemaModalSubtitle' => 1,
                'sistemaModalCloseBTN' => 2,
                'UserType'             => 2,
            ],
            'UserAccess'        => ['LevelAccess' => $nivel, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_FormInputs'    => new UIFormInputs(),
            'Fnc_Codification'  => new FunctionsSecurityCodification(),
            'Fnc_DataDate'      => new FunctionsDataDate(),
            'Fnc_DataNumbers'   => new FunctionsDataNumbers(),
            'Fnc_WidgetsCommon' => new UIWidgetsCommon(),
            'Fnc_WidgetsViews'  => new UIWidgetsViews(),
            'arrContactos'      => [[
                'idContacto'   => 33,
                'Nombre'       => 'Ana',
                'ApellidoPat'  => 'Prueba',
                'ApellidoMat'  => 'Test',
                'Email'        => 'ana@example.test',
                'Fono1'        => '+56911111111',
                'Fono2'        => '',
                'TipoContacto' => 'Comercial',
            ]],
            'arrCiudad'         => [['ID' => 1, 'Nombre' => 'Santiago']],
            'arrComuna'         => [['ID1' => 1, 'ID2' => 1, 'Nombre' => 'Providencia']],
            'arrTipoContacto'   => [['ID' => 1, 'Nombre' => 'Comercial']],
            'arrEstado'         => [['ID' => 1, 'Nombre' => 'Activo'], ['ID' => 2, 'Nombre' => 'Inactivo']],
            'rowData'           => [
                'idContacto'     => 33,
                'idEntidad'      => 91,
                'idTipo'         => 1,
                'Nombre'         => 'Ana',
                'ApellidoPat'    => 'Prueba',
                'ApellidoMat'    => 'Test',
                'Email'          => 'ana@example.test',
                'Rut'            => '12.345.678-5',
                'Fono1'          => '+56911111111',
                'Fono2'          => '',
                'idCiudad'       => 1,
                'idComuna'       => 1,
                'Direccion'      => 'Calle Falsa 123',
                'idTipoContacto' => 1,
                'Cargo'          => 'Gerente de pruebas',
                'idEstado'       => 1,
                'Estado'         => 'Activo',
                'EstadoColor'    => 'success',
                'Ciudad'         => 'Santiago',
                'Comuna'         => 'Providencia',
                'TipoContacto'   => 'Comercial',
            ],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica los archivos del controlador `entidadesListadoContactos` y la convencion
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
            'vista_form_new'    => $this->rutaModulo('../views/'.self::VISTA_BASE.'-formNew.php'),
            'vista_form_edit'   => $this->rutaModulo('../views/'.self::VISTA_BASE.'-formEdit.php'),
            'vista_update_list' => $this->rutaModulo('../views/'.self::VISTA_BASE.'-UpdateList.php'),
            'vista_view'        => $this->rutaModulo('../views/'.self::VISTA_BASE.'-View.php'),
            'testing'           => $this->rutaModulo('entidadesListadoContactosTesting.php'),
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
        $this->addCase($results, 'clase_testing_cargada', class_exists('entidadesListadoContactosTesting'),
            'la clase entidadesListadoContactosTesting no esta disponible');
        $padre = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padre === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padre.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('entidadesListadoContactosTesting') === 'ControllerBase',
            'el testing no extiende ControllerBase');

        /*====================================*/
        // Constructor del controlador
        /*====================================*/
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'constructor_conexion', str_contains($codigo, 'Database::getSQLConnection(ConfigDataBase::MySQL_1)'),
            'el constructor no obtiene la conexion MySQL_1');
        $this->addCase($results, 'constructor_padre', str_contains($codigo, 'parent::__construct($DB_conn_1, $queryBuilder, $checkData)'),
            'el constructor no delega en ControllerBase');
        $this->addCase($results, 'constructor_permisos', str_contains($codigo, "\$this->controllerName = '".self::CONTROLADOR_PERMISOS."'"),
            'controllerName debe reutilizar los permisos de '.self::CONTROLADOR_PERMISOS);

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                            CONTROLADOR: VISTAS                             */
    /******************************************************************************/
    /**
     * Verifica el contrato de los metodos que devuelven HTML (`New`, `UpdateList`, `View`,
     * `GetID`): firma, vista instanciada, tipo de vista, errores, catalogos y consultas.
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];
        $codigo  = $this->fuenteControlador();

        /*====================================*/
        // Firma de cada metodo de vista (F3 pasa $f3 y $params)
        /*====================================*/
        foreach (['New', 'UpdateList', 'View', 'GetID'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 2, 2);
        }

        /*====================================*/
        // Contrato de presentacion
        /*====================================*/
        $this->contratoPresentacion($results, 'New', 'formNew');
        $this->contratoPresentacion($results, 'UpdateList', 'UpdateList');
        $this->contratoPresentacion($results, 'View', 'View');
        $this->contratoPresentacion($results, 'GetID', 'formEdit');

        $this->addCase($results, 'tipoview_modal', substr_count($codigo, 'showVista(2,') === 4,
            'las cuatro vistas deben servirse como modal/parcial con showVista(2,)');
        $this->addCase($results, 'rutavista_resuelta', substr_count($codigo, 'returnRutaVista(__DIR__') === 4,
            'las vistas no resuelven su ruta con returnRutaVista()');
        $this->addCase($results, 'errores_showerror', substr_count($codigo, 'showError(2, $f3, $result)') === 4,
            'las vistas no manejan el fallo de consulta con showError()');
        $this->addCase($results, 'fallback_mergeresponses', substr_count($codigo, 'mergeResponses(') === 4,
            'las vistas no agrupan respuestas con mergeResponses()');
        $this->addCase($results, 'usuario_en_vistas', substr_count($codigo, 'getUserData($f3)') === 4,
            'las vistas no obtienen los datos del usuario');
        $this->addCase($results, 'nivel_en_vistas', substr_count($codigo, 'getArrLevel($f3, $this->controllerName)') === 4,
            'las vistas no resuelven el nivel de acceso');

        /*====================================*/
        // Consultas de lectura: catalogos, listado ordenado y detalle con union
        /*====================================*/
        $this->addCase($results, 'consulta_entidad_existe',
            str_contains($codigo, "'data'    => 'idEntidad, idTipo'") && str_contains($codigo, "'where'   => 'idEntidad = ?'"),
            'New() no verifica que la entidad exista y obtiene su idTipo');
        foreach (['core_ubicacion_ciudad', 'core_ubicacion_comunas', 'core_tipos_contactos'] as $tabla) {
            $this->addCase($results, 'catalogo_'.$tabla, substr_count($codigo, "'table'   => '".$tabla."'") === 2,
                'New() y GetID() deben cargar el catalogo '.$tabla);
        }
        $this->addCase($results, 'catalogo_estados_edicion', substr_count($codigo, "'table'   => 'core_estados'") === 1,
            'GetID() debe cargar el catalogo de estados para la edicion');
        $this->addCase($results, 'tipo_contacto_por_entidad',
            substr_count($codigo, '$tipo = ($rowData[\'data\'][\'idTipo\'] == 3) ? 1 : 2;') === 2
            && substr_count($codigo, "'where'   => 'idTipo = ?'") === 2,
            'New() y GetID() deben filtrar los tipos de contacto segun el tipo de entidad (trabajadores 1, resto 2)');
        $this->addCase($results, 'listado_por_entidad', str_contains($codigo, "'where'   => 'entidades_listado_contactos.idEntidad = ?'"),
            'UpdateList() no filtra los contactos por entidad');
        $this->addCase($results, 'listado_ordenado',
            str_contains($codigo, "'order'   => 'entidades_listado_contactos.ApellidoPat ASC, entidades_listado_contactos.ApellidoMat ASC, entidades_listado_contactos.Nombre ASC'"),
            'UpdateList() no ordena los contactos por apellidos y nombre');
        $this->addCase($results, 'detalle_por_contacto', str_contains($codigo, "'where'   => 'entidades_listado_contactos.idContacto = ?'"),
            'View() no consulta el contacto por idContacto');
        $this->addCase($results, 'getid_por_contacto', str_contains($codigo, "'where'   => 'idContacto = ?'") && str_contains($codigo, 'entidades_listado.idTipo'),
            'GetID() no consulta el contacto por idContacto junto al tipo de su entidad');
        $this->addCase($results, 'consultas_limite', substr_count($codigo, 'ConfigAPP::APP["N_MaxItems"]') === 8,
            'se esperaban 8 consultas de lista acotadas por N_MaxItems');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                         CONTROLADOR: ESCRITURA (CRUD)                      */
    /******************************************************************************/
    /**
     * Verifica `Insert`, `Update` y `Delete` SIN ejecutarlos: verbo HTTP, chequeo de datos,
     * campos obligatorios, delimitacion por idContacto y respuestas al cliente. El CRUD de
     * contactos envuelve cada escritura en una transaccion Base_transactionBegin/Commit/Rollback.
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
        // Chequeo de datos y tabla
        /*====================================*/
        $this->addCase($results, 'datacheck_post', substr_count($codigo, '$this->dataCheck($_POST)') === 2,
            'Insert() y Update() deben construir el DataCheck con dataCheck($_POST)');
        $this->addCase($results, 'tabla_contactos', preg_match_all("/'table'\s*=>\s*'entidades_listado_contactos'/", $codigo) === 6,
            'se esperaban 6 usos de entidades_listado_contactos (3 lecturas y Insert/Update/Delete)');
        $this->addCase($results, 'sin_unicidad_ni_codificacion',
            substr_count($codigo, "'unique'    => ''") === 2 && substr_count($codigo, "'encode'    => ''") === 2,
            'los contactos no tienen campos unicos ni codificados');

        /*====================================*/
        // Insert
        /*====================================*/
        $this->addCase($results, 'insert_campos',
            str_contains($codigo, "'data'      => 'idEntidad,Nombre,ApellidoPat,ApellidoMat,Email,Rut,Fono1,Fono2,idCiudad,idComuna,Direccion,idTipoContacto,Cargo,idEstado'"),
            'Insert() no declara los campos esperados de el contacto');
        $this->addCase($results, 'insert_requeridos', str_contains($codigo, "'required'  => 'idEntidad,Nombre,ApellidoPat'"),
            'Insert() no declara los obligatorios idEntidad,Nombre,ApellidoPat');
        $this->addCase($results, 'insert_base', str_contains($codigo, '$this->Base_insert('),
            'Insert() no escribe con Base_insert()');

        /*====================================*/
        // Update
        /*====================================*/
        $this->addCase($results, 'update_verbo_post', str_contains($codigo, "REQUEST_METHOD'] !== 'POST'"),
            'Update() no valida el verbo HTTP POST');
        $this->addCase($results, 'update_donde', str_contains($codigo, "'where'     => 'idContacto'"),
            'Update() no delimita la escritura por idContacto');
        $this->addCase($results, 'update_requeridos', str_contains($codigo, "'required'  => 'idContacto,idEntidad,Nombre,ApellidoPat'"),
            'Update() no declara los obligatorios');
        $this->addCase($results, 'update_base', str_contains($codigo, '$this->Base_update('),
            'Update() no escribe con Base_update()');

        /*====================================*/
        // Delete: verbo, delimitacion y sin archivos
        /*====================================*/
        $this->addCase($results, 'delete_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'DELETE'"),
            'Delete() no valida el verbo HTTP DELETE');
        $this->addCase($results, 'delete_donde', str_contains($codigo, "'where'       => 'idContacto'"),
            'Delete() no delimita el borrado por idContacto');
        $this->addCase($results, 'delete_sin_archivos', str_contains($codigo, "'files'       => ''"),
            'Delete() no declara que el contacto no maneja archivos');
        $this->addCase($results, 'delete_base', str_contains($codigo, '$this->Base_delete('),
            'Delete() no borra con Base_delete()');
        $this->addCase($results, 'delete_lee_cuerpo', str_contains($codigo, 'parse_str(file_get_contents("php://input")'),
            'Delete() no lee el cuerpo DELETE');

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
        $this->addCase($results, 'respuesta_400', substr_count($codigo, "Response::error('Registro inválido', 400)") === 4,
            'los 4 metodos con ID cifrado deben responder 400 ante un ID invalido');
        $this->addCase($results, 'respuesta_500', substr_count($codigo, 'failOperation(') === 3,
            'se esperaban 3 puntos failOperation (Insert, Update y Delete)');
        $this->addCase($results, 'id_descifrado_vistas', substr_count($codigo, "encryptDecrypt('decrypt', \$params['id'])") === 4,
            'New/UpdateList/View/GetID deben descifrar $params[\'id\']');
        $this->addCase($results, 'id_valido_vistas', substr_count($codigo, "isValidDecrypted(") === 4,
            'New/UpdateList/View/GetID deben validar el ID con isValidDecrypted()');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                       CONTROLADOR: RUTAS E INSTALADOR                      */
    /******************************************************************************/
    /**
     * Verifica que las 7 rutas de `entidadesListadoContactos` publicadas por el instalador
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
            'nuevo'     => ['1 '.self::RUTA_WEB_CONTACTOS.'/new/@id',        'entidadesListadoContactos->New',        2],
            'refrescar' => ['1 '.self::RUTA_WEB_CONTACTOS.'/updateList/@id', 'entidadesListadoContactos->UpdateList', 2],
            'ver'       => ['1 '.self::RUTA_WEB_CONTACTOS.'/view/@id',       'entidadesListadoContactos->View',       2],
            'getid'     => ['1 '.self::RUTA_WEB_CONTACTOS.'/getID/@id',      'entidadesListadoContactos->GetID',      2],
            'crear'     => ['2 '.self::RUTA_WEB_CONTACTOS,                   'entidadesListadoContactos->Insert',     2],
            'editar'    => ['2 '.self::RUTA_WEB_CONTACTOS.'/update',         'entidadesListadoContactos->Update',     2],
            'borrar'    => ['3 '.self::RUTA_WEB_CONTACTOS,                   'entidadesListadoContactos->Delete',     2],
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

        $propias = array_filter($rutas, static function ($r) { return $r[2] === 'entidadesListadoContactos'; });
        $this->addCase($results, 'rutas_propias_completas', count($propias) === 7,
            'se esperaban 7 rutas de '.self::CONTROLADOR.' y hay '.count($propias));

        /*====================================*/
        // Desinstalacion: el sub-recurso debe estar en RutaController()
        /*====================================*/
        $instalador = $this->codigoFuente($this->rutaModulo('../installer/entidadesInstaller.php'));
        $this->addCase($results, 'instalador_desinstala_subrecurso', str_contains($instalador, '"entidadesListadoContactos"'),
            'RutaController() no lista entidadesListadoContactos y dejaria rutas huerfanas al desinstalar');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                PERMISOS                                    */
    /******************************************************************************/
    /**
     * Verifica la sesion, el nivel de acceso heredado de `entidadesListado` y el registro
     * de las rutas de contactos en el router de F3 (solo si el usuario las tiene).
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
        // Lectura de la sesion desde el controlador (permisos de entidadesListado)
        /*====================================*/
        try {
            $this->addCase($results, 'get_user_data_arreglo', is_array($this->getUserData($f3)),
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
        // Rutas en SESSION.arrPermisos y en el router (solo si las tiene)
        /*====================================*/
        $arrPermisos = $f3->get('SESSION.arrPermisos');
        $this->addCase($results, 'permisos_de_ruta_presentes', $arrPermisos === null || is_array($arrPermisos),
            'SESSION.arrPermisos no es un arreglo');

        $arrRoutes = $f3->get('ROUTES');
        $this->addCase($results, 'router_disponible', is_array($arrRoutes), 'F3 no expuso la variable ROUTES');
        $esquema = [
            'ruta_insert_router' => ['/'.self::RUTA_WEB_CONTACTOS,             'POST',   self::CONTROLADOR.'->Insert'],
            'ruta_update_router' => ['/'.self::RUTA_WEB_CONTACTOS.'/update',   'POST',   self::CONTROLADOR.'->Update'],
            'ruta_delete_router' => ['/'.self::RUTA_WEB_CONTACTOS,             'DELETE', self::CONTROLADOR.'->Delete'],
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
     * Renderiza `UpdateList`, `View`, `formNew` y `formEdit` de contactos con datos sinteticos
     * y verifica ids de formulario, campos, endpoints con su verbo, ID cifrado y estados.
     * En la tabla las acciones no dependen del nivel (la ruta ya exige nivel 2).
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function vistasContactosTest($f3){

        $results = [];
        $BASE    = (string)$f3->get('BASE');
        if (trim($BASE) === '') {
            $BASE = '/admin';
        }

        /*====================================*/
        // Tabla de contactos
        /*====================================*/
        $rutaTabla = $this->rutaModulo('../views/'.self::VISTA_BASE.'-UpdateList.php');
        foreach (self::NIVELES as $nivel) {
            $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos($nivel), $BASE, $f3);
            $this->addCase($results, 'tabla_render_nivel'.$nivel, $tabla !== '',
                'UpdateList no produjo HTML con nivel '.$nivel);
            $this->addCase($results, 'tabla_acciones_nivel'.$nivel,
                str_contains($tabla, 'tabContactosView(') && str_contains($tabla, 'tabContactosEdit(') && str_contains($tabla, 'tabContactosDel('),
                'la tabla no ofrece ver, editar y borrar con nivel '.$nivel);
        }
        $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'tabla_datos',
            str_contains($tabla, 'Prueba Test Ana') && str_contains($tabla, 'Comercial') && str_contains($tabla, 'ana@example.test'),
            'la tabla no pinta el nombre completo, el tipo y el email');
        $this->addCase($results, 'tabla_telefono_formateado', str_contains($tabla, '(+56) 9 1111 1111'),
            'la tabla no formatea el celular con formatPhone()');
        $this->addCase($results, 'tabla_columnas',
            str_contains($tabla, '>Nombre<') && str_contains($tabla, '>Tipo Contacto<') && str_contains($tabla, '>Email<') && str_contains($tabla, '>Celular<') && str_contains($tabla, '>Acciones<'),
            'la tabla no declara las columnas Nombre, Tipo Contacto, Email, Celular y Acciones');
        $this->addCase($results, 'tabla_id_cifrado',
            !str_contains($tabla, "tabContactosView('33')") && !str_contains($tabla, "tabContactosEdit('33')") && !str_contains($tabla, "tabContactosDel( '33'"),
            'la tabla expone idContacto sin cifrar');

        $vacio = $this->datosSinteticos(2);
        $vacio['arrContactos'] = [];
        $tablaVacia = $this->renderizarVista($rutaTabla, $vacio, $BASE, $f3);
        $this->addCase($results, 'tabla_vacia', $tablaVacia !== '' && str_contains($tablaVacia, '<tbody>') && !str_contains($tablaVacia, 'tabContactosView('),
            'la tabla sin datos pinta filas o no se dibuja');

        $comillas = $this->datosSinteticos(2);
        $comillas['arrContactos'][0]['Nombre'] = "D'Ana";
        $tablaComillas = $this->renderizarVista($rutaTabla, $comillas, $BASE, $f3);
        $this->addCase($results, 'tabla_escapa_comillas', str_contains($tablaComillas, "D\\'Ana"),
            'la etiqueta del borrado no escapa las comillas simples');

        /*====================================*/
        // Modal de detalle (View)
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::VISTA_BASE.'-View.php'),
            $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'view_render', $html !== '', 'la vista View no produjo HTML');
        $this->addCase($results, 'view_datos_basicos',
            str_contains($html, 'Prueba Test Ana') && str_contains($html, 'ana@example.test') && str_contains($html, '12.345.678-5'),
            'la vista View no pinta nombre, email y rut');
        $this->addCase($results, 'view_telefono_formateado', str_contains($html, '(+56) 9 1111 1111'),
            'la vista View no formatea el celular con formatPhone()');
        $this->addCase($results, 'view_estado_badge', str_contains($html, 'badge-sp1-success') && str_contains($html, 'Activo'),
            'la vista View no pinta el estado con su color');
        $this->addCase($results, 'view_datos_ubicacion_y_cargo',
            str_contains($html, 'Santiago') && str_contains($html, 'Providencia') && str_contains($html, 'Calle Falsa 123')
            && str_contains($html, 'Comercial') && str_contains($html, 'Gerente de pruebas'),
            'la vista View no pinta ciudad, comuna, direccion, tipo de contacto y cargo');
        $this->addCase($results, 'view_pie_cierre', str_contains($html, 'data-bs-dismiss="modal"'),
            'la vista View no ofrece el pie de cierre del modal');

        /*====================================*/
        // Formulario de alta
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::VISTA_BASE.'-formNew.php'),
            $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'form_new_render', $html !== '', 'formNew no produjo HTML');
        $this->addCase($results, 'form_new_id', str_contains($html, 'id="FormNewContacto"'),
            'el formulario de alta no usa el id FormNewContacto');
        $faltan = [];
        foreach (['Nombre', 'ApellidoPat', 'ApellidoMat', 'Email', 'Rut', 'Fono1', 'Fono2', 'idCiudad', 'idComuna', 'Direccion', 'idTipoContacto', 'Cargo'] as $campo) {
            if (!str_contains($html, 'name="'.$campo.'"')) {
                $faltan[] = $campo;
            }
        }
        $this->addCase($results, 'form_new_campos', count($faltan) === 0,
            'el formulario de alta no declara los campos: '.implode(', ', $faltan));
        $this->addCase($results, 'form_new_ocultos',
            (bool)preg_match('/name="idEntidad"[^>]*value="91"|value="91"[^>]*name="idEntidad"/', $html)
            && (bool)preg_match('/name="idEstado"[^>]*value="1"|value="1"[^>]*name="idEstado"/', $html),
            'el formulario de alta no envia idEntidad (91) e idEstado activo (1) como campos ocultos');
        $this->addCase($results, 'form_new_sin_idcontactos', !str_contains($html, 'name="idContacto"'),
            'el formulario de alta no debe enviar idContacto');
        $this->addCase($results, 'form_new_catalogos', str_contains($html, 'Santiago') && str_contains($html, 'Providencia') && str_contains($html, 'Comercial'),
            'el formulario de alta no ofrece los catalogos de ciudad, comuna y tipo de contacto');
        $this->addCase($results, 'form_new_endpoint',
            str_contains($html, $BASE.'/'.self::RUTA_WEB_CONTACTOS."'") && str_contains($html, "let Metodo      = 'POST'"),
            'el formulario de alta no publica en '.self::RUTA_WEB_CONTACTOS.' con POST');
        $this->addCase($results, 'form_new_refresca_tabla', str_contains($html, '#tabContactosDataTable') && str_contains($html, '/contactos/updateList/'),
            'el formulario de alta no refresca la tabla de contactos al guardar');
        $this->addCase($results, 'form_new_id_cifrado_refresco', !str_contains($html, '/contactos/updateList/91'),
            'el refresco de la tabla expone idEntidad sin cifrar');

        /*====================================*/
        // Formulario de edicion
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::VISTA_BASE.'-formEdit.php'),
            $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'form_edit_render', $html !== '', 'formEdit no produjo HTML');
        $this->addCase($results, 'form_edit_id', str_contains($html, 'id="FormEditContacto"'),
            'el formulario de edicion no usa el id FormEditContacto');
        $this->addCase($results, 'form_edit_campos',
            str_contains($html, 'name="Nombre"') && str_contains($html, 'name="ApellidoPat"') && str_contains($html, 'name="Cargo"')
            && str_contains($html, 'name="idTipoContacto"') && str_contains($html, 'name="idEstado"'),
            'el formulario de edicion no declara los campos del contacto y el estado');
        $this->addCase($results, 'form_edit_ocultos', str_contains($html, 'name="idContacto"') && str_contains($html, 'name="idEntidad"'),
            'el formulario de edicion no envia idContacto e idEntidad');
        $this->addCase($results, 'form_edit_precarga', str_contains($html, 'Prueba') && str_contains($html, 'Gerente de pruebas') && str_contains($html, 'ana@example.test'),
            'el formulario de edicion no precarga el contacto');
        $this->addCase($results, 'form_edit_estados', str_contains($html, 'Inactivo'),
            'el formulario de edicion no ofrece el catalogo de estados');
        $this->addCase($results, 'form_edit_endpoint',
            str_contains($html, $BASE.'/'.self::RUTA_WEB_CONTACTOS.'/update') && str_contains($html, "let Metodo      = 'POST'"),
            'el formulario de edicion no publica en /contactos/update con POST');

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

            $Post      = ['idEntidad' => 91, 'Nombre' => 'Ana', 'ApellidoPat' => 'Prueba', 'Email' => 'ana@example.test'];
            $DataCheck = $reflector->invoke($Controller, $Post);

            $this->addCase($results, 'datacheck_es_arreglo', is_array($DataCheck), 'dataCheck() no devolvio un arreglo');
            $this->addCase($results, 'datacheck_privado', $reflector->isPrivate(), 'dataCheck() debe ser privado');
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post,
                'dataCheck() no conserva el POST recibido');
            $this->addCase($results, 'datacheck_claves_contrato',
                isset($DataCheck['emptyData'], $DataCheck['ValidarEmail'], $DataCheck['ValidarNumero'], $DataCheck['ValidarEntero'], $DataCheck['ValidarRut'], $DataCheck['ValidarLargoMinimoN'], $DataCheck['ValidarLargoMaximoPorCampo']),
                'dataCheck() no entrega las claves del contrato de CheckData');
            $this->addCase($results, 'datacheck_email',
                trim((string)$DataCheck['ValidarEmail']) === 'Email' && str_contains((string)$DataCheck['ValidarDominioEmail'], 'Email')
                && str_contains((string)$DataCheck['ValidarMayusculas'], 'Email') && str_contains((string)$DataCheck['ValidarEspaciosVacios'], 'Email'),
                'dataCheck() no valida formato, dominio, mayusculas y espacios del Email');
            $this->addCase($results, 'datacheck_rut', trim((string)$DataCheck['ValidarRut']) === 'Rut',
                'dataCheck() no valida el Rut');
            $this->addCase($results, 'datacheck_ids_enteros',
                str_contains((string)$DataCheck['ValidarEntero'], 'idEntidad') && str_contains((string)$DataCheck['ValidarEntero'], 'idCiudad')
                && str_contains((string)$DataCheck['ValidarEntero'], 'idComuna') && str_contains((string)$DataCheck['ValidarEntero'], 'idTipoContacto')
                && str_contains((string)$DataCheck['ValidarEntero'], 'idEstado'),
                'dataCheck() no valida los identificadores como enteros');
            $this->addCase($results, 'datacheck_telefonos',
                str_contains((string)$DataCheck['ValidarNumero'], 'Fono1') && str_contains((string)$DataCheck['ValidarNumero'], 'Fono2'),
                'dataCheck() no valida los telefonos como numeros');
            $this->addCase($results, 'datacheck_largos',
                str_contains((string)$DataCheck['ValidarLargoMinimo'], 'Cargo')
                && (int)($DataCheck['ValidarLargoMaximoPorCampo']['Cargo'] ?? 0) === 255
                && (int)($DataCheck['ValidarLargoMaximoPorCampo']['Direccion'] ?? 0) === 255
                && (int)($DataCheck['ValidarLargoMinimoN'] ?? 0) === 3,
                'dataCheck() no limita los textos a 3..255 caracteres');
            $this->addCase($results, 'datacheck_censura',
                str_contains((string)$DataCheck['ValidarPalabrasCensuradas'], 'Nombre') && str_contains((string)$DataCheck['ValidarPalabrasCensuradas'], 'Cargo'),
                'dataCheck() no filtra palabras censuradas en nombres y cargo');
            $this->addCase($results, 'datacheck_sin_fechas', trim((string)$DataCheck['ValidarFecha']) === '',
                'dataCheck() valida fechas pero el contacto no tiene campos de fecha');
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_es_arreglo', false,
                'no se pudo ejecutar dataCheck() ('.$e->getMessage().')');
        }

        /*====================================*/
        // Cifrado de identificadores: ida y vuelta estable y rechazo de basura
        /*====================================*/
        try {
            $Codification = new FunctionsSecurityCodification();
            $cifrado      = $Codification->encryptDecrypt('encrypt', '33');
            $this->addCase($results, 'cifrado_genera_dato',
                ($cifrado['success'] ?? false) === true && !empty($cifrado['data']),
                'encryptDecrypt(encrypt) no produjo un valor cifrado');

            $descifrado = $Codification->encryptDecrypt('decrypt', (string)($cifrado['data'] ?? ''));
            $this->addCase($results, 'cifrado_ida_y_vuelta',
                ($descifrado['success'] ?? false) === true && (string)($descifrado['data'] ?? '') === '33',
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
     * Revisa estaticamente las vistas de contactos y el controlador: rutas relativas, rutas
     * absolutas, acceso a sesion, SQL embebido, cifrado de IDs y consultas preparadas.
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
            'se esperaban 4 vistas de contactos y se encontraron '.count($arrVistas));

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
        $tabla = $this->codigoFuente($this->rutaModulo('../views/'.self::VISTA_BASE.'-UpdateList.php'));
        $this->addCase($results, 'tabla_escape_etiqueta', str_contains($tabla, 'addslashes('),
            'UpdateList no escapa la etiqueta que viaja al JavaScript');
        $this->addCase($results, 'tabla_id_cifrado', str_contains($tabla, "encryptDecrypt('encrypt', \$crud['idContacto'])"),
            'UpdateList no cifra idContacto en las acciones');
        $this->addCase($results, 'tabla_telefono_formateado', substr_count($tabla, 'formatPhone(') === 2,
            'UpdateList debe formatear sus 2 telefonos con formatPhone()');
        $view = $this->codigoFuente($this->rutaModulo('../views/'.self::VISTA_BASE.'-View.php'));
        $this->addCase($results, 'view_telefonos_formateados', substr_count($view, 'formatPhone(') === 2,
            'View debe formatear sus 2 telefonos con formatPhone()');

        /*====================================*/
        // Controlador: SQL preparado y sin concatenacion de entrada
        /*====================================*/
        $controlador = $this->fuenteControlador();
        $this->addCase($results, 'controlador_sin_concat_sql', !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $controlador),
            'el controlador concatena superglobales en una cadena SQL');
        $this->addCase($results, 'controlador_parametros_preparados', preg_match_all("/'params'\s*=>/", $controlador) === 11,
            'las 11 consultas deben declarar la clave params (consultas preparadas)');
        $this->addCase($results, 'controlador_sin_get', !str_contains($controlador, '$_GET') && !str_contains($controlador, '$_REQUEST'),
            'el controlador lee $_GET/$_REQUEST; solo debe usar $_POST y parametros de ruta cifrados');

        /************************************/
        // Retorno los datos
        return $results;

    }

}
