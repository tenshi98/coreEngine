<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class entidadesListadoDocumentosTesting
 *
 * Pruebas del modulo `entidades` - controlador `entidadesListadoDocumentos` (documentos de una entidad).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. Los textos de los casos
 *    correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Alcance: pruebas NO destructivas. `Insert()` (sube un archivo al servidor), `Update()` y
 * `Delete()` (borra el archivo fisico) escriben en base de datos y/o disco y terminan en
 * `Response::*()` (`exit`); NO se invocan: se verifican por reflexion y lectura del fuente.
 * Solo se ejecuta logica pura (`dataCheck`, cifrado de IDs, nombre de archivo generado).
 *
 * El controlador reutiliza los permisos y la ruta `entidadesListado` ($this->controllerName)
 * y las vistas `entidadesListado-Resumen-Documentos-*`; el CRUD no usa transacciones. Es el unico sub-recurso de entidades que gestiona archivos.
 *
 * @see skills/testing/SKILL.md
 */
class entidadesListadoDocumentosTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR          = 'entidadesListadoDocumentos';
    private const CONTROLADOR_PERMISOS = 'entidadesListado';
    private const VISTA_BASE           = 'entidadesListado-Resumen-Documentos';
    private const RUTA_WEB             = 'administracion/entidades/listado';
    private const RUTA_WEB_DOCUMENTOS      = 'administracion/entidades/listado/documentos';
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
    // $this->controllerName.'-Resumen-Documentos-<sufijo>.php' y la sirve como modal
    /*******************************************************************/
    private function contratoPresentacion(array &$results, string $metodo, string $sufijoVista){
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'vista_'.$metodo, str_contains($codigo, "\$this->controllerName.'-Resumen-Documentos-".$sufijoVista.".php'"),
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
            'Fnc_WidgetsCommon' => new UIWidgetsCommon(),
            'Fnc_WidgetsViews'  => new UIWidgetsViews(),
            'Fnc_WidgetsPreview' => new UIWidgetsPreview(),
            'arrDocumentos'     => [[
                'idDocumentos' => 33,
                'Nombre'       => 'Contrato de prueba',
                'FVencimiento' => '2026-12-31',
            ]],
            'rowData'           => [
                'idDocumentos'  => 33,
                'idEntidad'     => 91,
                'Nombre'        => 'Contrato de prueba',
                'NombreArchivo' => 'EntidadDoc_prueba.pdf',
                'Observacion'   => 'Observacion de prueba del documento',
                'FVencimiento'  => '2026-12-31',
            ],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica los archivos del controlador `entidadesListadoDocumentos` y la convencion
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
            'testing'           => $this->rutaModulo('entidadesListadoDocumentosTesting.php'),
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
        $this->addCase($results, 'clase_testing_cargada', class_exists('entidadesListadoDocumentosTesting'),
            'la clase entidadesListadoDocumentosTesting no esta disponible');
        $padre = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padre === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padre.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('entidadesListadoDocumentosTesting') === 'ControllerBase',
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

        /*====================================*/
        // Entorno: la subida de archivos valida el tipo real
        /*====================================*/
        $this->addCase($results, 'entorno_finfo', class_exists('finfo'),
            'la extension finfo no esta disponible para validar el tipo de los documentos subidos');

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
            str_contains($codigo, "'data'    => 'idEntidad',") && str_contains($codigo, "'where'   => 'idEntidad = ?'"),
            'New() no verifica que la entidad exista');
        $this->addCase($results, 'listado_por_entidad',
            str_contains($codigo, "'data'    => 'idDocumentos,Nombre,FVencimiento'") && str_contains($codigo, "'where'   => 'idEntidad = ?'"),
            'UpdateList() no lista los documentos de la entidad');
        $this->addCase($results, 'detalle_por_documento',
            str_contains($codigo, "'data'    => 'Nombre,NombreArchivo,Observacion,FVencimiento'") && str_contains($codigo, "'where'   => 'idDocumentos = ?'"),
            'View() no consulta el documento con su archivo y observacion');
        $this->addCase($results, 'getid_por_documento',
            str_contains($codigo, "'data'    => 'idDocumentos,idEntidad,Nombre,Observacion,FVencimiento'"),
            'GetID() no consulta los campos editables del documento');
        $this->addCase($results, 'getid_sin_archivo', !preg_match("/'data'\s*=>\s*'idDocumentos,idEntidad,Nombre,NombreArchivo/", $codigo),
            'GetID() expone el nombre del archivo en el formulario de edicion');
        $this->addCase($results, 'consultas_limite', substr_count($codigo, 'ConfigAPP::APP["N_MaxItems"]') === 1,
            'solo UpdateList() debe acotar el numero de filas con N_MaxItems');
        $this->addCase($results, 'sin_catalogos', substr_count($codigo, 'Base_GetList(') === 1,
            'los documentos no usan catalogos: solo UpdateList() lista con Base_GetList()');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                         CONTROLADOR: ESCRITURA (CRUD)                      */
    /******************************************************************************/
    /**
     * Verifica `Insert`, `Update` y `Delete` SIN ejecutarlos: verbo HTTP, chequeo de datos,
     * campos obligatorios, delimitacion por idDocumentos y respuestas al cliente. El CRUD de
     * documentos envuelve cada escritura en una transaccion Base_transactionBegin/Commit/Rollback.
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
        $this->addCase($results, 'tabla_documentos', preg_match_all("/'table'\s*=>\s*'entidades_listado_documentos'/", $codigo) === 6,
            'se esperaban 6 usos de entidades_listado_documentos (3 lecturas y Insert/Update/Delete)');
        $this->addCase($results, 'sin_unicidad_ni_codificacion',
            substr_count($codigo, "'unique'    => ''") === 2 && substr_count($codigo, "'encode'    => ''") === 2,
            'los documentos no tienen campos unicos ni codificados');

        /*====================================*/
        // Insert
        /*====================================*/
        $this->addCase($results, 'insert_campos',
            str_contains($codigo, "'data'      => 'idEntidad,Nombre,Observacion,FVencimiento'"),
            'Insert() no declara los campos esperados de el documento');
        $this->addCase($results, 'insert_requeridos', str_contains($codigo, "'required'  => 'idEntidad,Nombre'"),
            'Insert() no declara los obligatorios idEntidad,Nombre,ApellidoPat');
        $this->addCase($results, 'insert_base', str_contains($codigo, '$this->Base_insert('),
            'Insert() no escribe con Base_insert()');

        /*====================================*/
        // Update
        /*====================================*/
        $this->addCase($results, 'update_verbo_post', str_contains($codigo, "REQUEST_METHOD'] !== 'POST'"),
            'Update() no valida el verbo HTTP POST');
        $this->addCase($results, 'update_donde', str_contains($codigo, "'where'     => 'idDocumentos'"),
            'Update() no delimita la escritura por idDocumentos');
        $this->addCase($results, 'update_requeridos', str_contains($codigo, "'required'  => 'idDocumentos,idEntidad,Nombre'"),
            'Update() no declara los obligatorios');
        $this->addCase($results, 'update_base', str_contains($codigo, '$this->Base_update('),
            'Update() no escribe con Base_update()');

        /*====================================*/
        // Delete: verbo, delimitacion y sin archivos
        /*====================================*/
        $this->addCase($results, 'delete_verbo', str_contains($codigo, "REQUEST_METHOD'] !== 'DELETE'"),
            'Delete() no valida el verbo HTTP DELETE');
        $this->addCase($results, 'delete_donde', str_contains($codigo, "'where'       => 'idDocumentos'"),
            'Delete() no delimita el borrado por idDocumentos');
        $this->addCase($results, 'delete_borra_archivo', str_contains($codigo, "'files'       => 'NombreArchivo'"),
            'Delete() no borra el archivo fisico del documento (files => NombreArchivo)');
        $this->addCase($results, 'delete_base', str_contains($codigo, '$this->Base_delete('),
            'Delete() no borra con Base_delete()');
        $this->addCase($results, 'delete_lee_cuerpo', str_contains($codigo, 'parse_str(file_get_contents("php://input")'),
            'Delete() no lee el cuerpo DELETE');

        /*====================================*/
        // Subida de archivo (solo en Insert): identificador, nombre, tipos y peso
        /*====================================*/
        $this->addCase($results, 'subida_identificador', substr_count($codigo, "'Identificador' => 'NombreArchivo'") === 1,
            'solo Insert() debe declarar la subida del archivo NombreArchivo');
        $this->addCase($results, 'subida_nombre_aleatorio', str_contains($codigo, "\$this->generarNombreArchivo('EntidadDoc')"),
            'la subida no genera un nombre de archivo propio (EntidadDoc_...) y conservaria el del cliente');
        $this->addCase($results, 'subida_tipos', str_contains($codigo, "'ValidarTipo'   => 'word,excel,powerpoint,pdf,image,txt,zip,video,music'"),
            'la subida no restringe los tipos de archivo permitidos');
        $this->addCase($results, 'subida_sin_ejecutables', !preg_match("/'ValidarTipo'\s*=>\s*'[^']*(php|exe|js|html|sh)/i", $codigo),
            'la subida permite tipos ejecutables');
        $this->addCase($results, 'subida_peso', str_contains($codigo, "'ValidarPeso'   => 10"),
            'la subida no limita el peso (10)');
        $this->addCase($results, 'subida_sin_base64', str_contains($codigo, "'Base64'        => false"),
            'la subida debe usar multipart y no base64');
        $this->addCase($results, 'update_sin_archivo', substr_count($codigo, "'files'     =>") === 1,
            'Update() no debe reemplazar el archivo: solo Insert() lo sube');

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
     * Verifica que las 7 rutas de `entidadesListadoDocumentos` publicadas por el instalador
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
            'nuevo'     => ['1 '.self::RUTA_WEB_DOCUMENTOS.'/new/@id',        'entidadesListadoDocumentos->New',        2],
            'refrescar' => ['1 '.self::RUTA_WEB_DOCUMENTOS.'/updateList/@id', 'entidadesListadoDocumentos->UpdateList', 2],
            'ver'       => ['1 '.self::RUTA_WEB_DOCUMENTOS.'/view/@id',       'entidadesListadoDocumentos->View',       2],
            'getid'     => ['1 '.self::RUTA_WEB_DOCUMENTOS.'/getID/@id',      'entidadesListadoDocumentos->GetID',      2],
            'crear'     => ['2 '.self::RUTA_WEB_DOCUMENTOS,                   'entidadesListadoDocumentos->Insert',     2],
            'editar'    => ['2 '.self::RUTA_WEB_DOCUMENTOS.'/update',         'entidadesListadoDocumentos->Update',     2],
            'borrar'    => ['3 '.self::RUTA_WEB_DOCUMENTOS,                   'entidadesListadoDocumentos->Delete',     2],
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

        $propias = array_filter($rutas, static function ($r) { return $r[2] === 'entidadesListadoDocumentos'; });
        $this->addCase($results, 'rutas_propias_completas', count($propias) === 7,
            'se esperaban 7 rutas de '.self::CONTROLADOR.' y hay '.count($propias));

        /*====================================*/
        // Desinstalacion: el sub-recurso debe estar en RutaController()
        /*====================================*/
        $instalador = $this->codigoFuente($this->rutaModulo('../installer/entidadesInstaller.php'));
        $this->addCase($results, 'instalador_desinstala_subrecurso', str_contains($instalador, '"entidadesListadoDocumentos"'),
            'RutaController() no lista entidadesListadoDocumentos y dejaria rutas huerfanas al desinstalar');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                PERMISOS                                    */
    /******************************************************************************/
    /**
     * Verifica la sesion, el nivel de acceso heredado de `entidadesListado` y el registro
     * de las rutas de documentos en el router de F3 (solo si el usuario las tiene).
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
            'ruta_insert_router' => ['/'.self::RUTA_WEB_DOCUMENTOS,             'POST',   self::CONTROLADOR.'->Insert'],
            'ruta_update_router' => ['/'.self::RUTA_WEB_DOCUMENTOS.'/update',   'POST',   self::CONTROLADOR.'->Update'],
            'ruta_delete_router' => ['/'.self::RUTA_WEB_DOCUMENTOS,             'DELETE', self::CONTROLADOR.'->Delete'],
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
     * Renderiza `UpdateList`, `View`, `formNew` y `formEdit` de documentos con datos sinteticos
     * y verifica ids de formulario, campos, endpoints con su verbo, ID cifrado y estados.
     * En la tabla las acciones no dependen del nivel (la ruta ya exige nivel 2).
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function vistasDocumentosTest($f3){

        $results = [];
        $BASE    = (string)$f3->get('BASE');
        if (trim($BASE) === '') {
            $BASE = '/admin';
        }

        /*====================================*/
        // Tabla de documentos
        /*====================================*/
        $rutaTabla = $this->rutaModulo('../views/'.self::VISTA_BASE.'-UpdateList.php');
        foreach (self::NIVELES as $nivel) {
            $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos($nivel), $BASE, $f3);
            $this->addCase($results, 'tabla_render_nivel'.$nivel, $tabla !== '',
                'UpdateList no produjo HTML con nivel '.$nivel);
            $this->addCase($results, 'tabla_acciones_nivel'.$nivel,
                str_contains($tabla, 'tabDocumentosView(') && str_contains($tabla, 'tabDocumentosEdit(') && str_contains($tabla, 'tabDocumentosDel('),
                'la tabla no ofrece ver, editar y borrar con nivel '.$nivel);
        }
        $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'tabla_datos', str_contains($tabla, 'Contrato de prueba') && str_contains($tabla, '31-12-2026'),
            'la tabla no pinta el nombre y la fecha de vencimiento formateada');
        $this->addCase($results, 'tabla_columnas',
            str_contains($tabla, '>Nombre<') && str_contains($tabla, '>Fecha Vencimiento<') && str_contains($tabla, '>Acciones<'),
            'la tabla no declara las columnas Nombre, Fecha Vencimiento y Acciones');
        $this->addCase($results, 'tabla_id_cifrado',
            !str_contains($tabla, "tabDocumentosView('33')") && !str_contains($tabla, "tabDocumentosEdit('33')") && !str_contains($tabla, "tabDocumentosDel( '33'"),
            'la tabla expone idDocumentos sin cifrar');

        $vacio = $this->datosSinteticos(2);
        $vacio['arrDocumentos'] = [];
        $tablaVacia = $this->renderizarVista($rutaTabla, $vacio, $BASE, $f3);
        $this->addCase($results, 'tabla_vacia', $tablaVacia !== '' && str_contains($tablaVacia, '<tbody>') && !str_contains($tablaVacia, 'tabDocumentosView('),
            'la tabla sin datos pinta filas o no se dibuja');

        $comillas = $this->datosSinteticos(2);
        $comillas['arrDocumentos'][0]['Nombre'] = "Contrato d'prueba";
        $tablaComillas = $this->renderizarVista($rutaTabla, $comillas, $BASE, $f3);
        $this->addCase($results, 'tabla_escapa_comillas', str_contains($tablaComillas, "Contrato d\\'prueba"),
            'la etiqueta del borrado no escapa las comillas simples');

        /*====================================*/
        // Modal de detalle (View)
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::VISTA_BASE.'-View.php'),
            $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'view_render', $html !== '', 'la vista View no produjo HTML');
        $this->addCase($results, 'view_datos_basicos',
            str_contains($html, 'Contrato de prueba') && str_contains($html, '31-12-2026') && str_contains($html, 'Observacion de prueba del documento'),
            'la vista View no pinta nombre, vencimiento y observacion');
        $this->addCase($results, 'view_vista_previa', str_contains($html, 'EntidadDoc_prueba.pdf'),
            'la vista View no apunta la vista previa al archivo del documento');
        $this->addCase($results, 'view_pie_cierre', str_contains($html, 'data-bs-dismiss="modal"'),
            'la vista View no ofrece el pie de cierre del modal');

        /*====================================*/
        // Formulario de alta
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::VISTA_BASE.'-formNew.php'),
            $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'form_new_render', $html !== '', 'formNew no produjo HTML');
        $this->addCase($results, 'form_new_id', str_contains($html, 'id="FormNewDocumentos"'),
            'el formulario de alta no usa el id FormNewDocumentos');
        $faltan = [];
        foreach (['Nombre', 'FVencimiento', 'Observacion', 'NombreArchivo'] as $campo) {
            if (!str_contains($html, 'name="'.$campo.'"')) {
                $faltan[] = $campo;
            }
        }
        $this->addCase($results, 'form_new_campos', count($faltan) === 0,
            'el formulario de alta no declara los campos: '.implode(', ', $faltan));
        $this->addCase($results, 'form_new_ocultos',
            (bool)preg_match('/name="idEntidad"[^>]*value="91"|value="91"[^>]*name="idEntidad"/', $html),
            'el formulario de alta no envia idEntidad (91) como campo oculto');
        $this->addCase($results, 'form_new_sin_iddocumentos', !str_contains($html, 'name="idDocumentos"'),
            'el formulario de alta no debe enviar idDocumentos');
        $this->addCase($results, 'form_new_multipart', str_contains($html, 'enctype="multipart/form-data"'),
            'el formulario de alta no usa enctype multipart/form-data necesario para subir el archivo');
        $this->addCase($results, 'form_new_endpoint',
            str_contains($html, $BASE.'/'.self::RUTA_WEB_DOCUMENTOS."'") && str_contains($html, "let Metodo      = 'POST'"),
            'el formulario de alta no publica en '.self::RUTA_WEB_DOCUMENTOS.' con POST');
        $this->addCase($results, 'form_new_refresca_tabla', str_contains($html, '#tabDocumentosDataTable') && str_contains($html, '/documentos/updateList/'),
            'el formulario de alta no refresca la tabla de documentos al guardar');
        $this->addCase($results, 'form_new_id_cifrado_refresco', !str_contains($html, '/documentos/updateList/91'),
            'el refresco de la tabla expone idEntidad sin cifrar');

        /*====================================*/
        // Formulario de edicion
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::VISTA_BASE.'-formEdit.php'),
            $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'form_edit_render', $html !== '', 'formEdit no produjo HTML');
        $this->addCase($results, 'form_edit_id', str_contains($html, 'id="FormEditDocumentos"'),
            'el formulario de edicion no usa el id FormEditDocumentos');
        $this->addCase($results, 'form_edit_campos',
            str_contains($html, 'name="Nombre"') && str_contains($html, 'name="FVencimiento"') && str_contains($html, 'name="Observacion"'),
            'el formulario de edicion no declara Nombre, FVencimiento y Observacion');
        $this->addCase($results, 'form_edit_sin_archivo', !str_contains($html, 'name="NombreArchivo"'),
            'el formulario de edicion no debe permitir subir otro archivo');
        $this->addCase($results, 'form_edit_ocultos', str_contains($html, 'name="idDocumentos"') && str_contains($html, 'name="idEntidad"'),
            'el formulario de edicion no envia idDocumentos e idEntidad');
        $this->addCase($results, 'form_edit_precarga', str_contains($html, 'Contrato de prueba') && str_contains($html, '2026-12-31') && str_contains($html, 'Observacion de prueba del documento'),
            'el formulario de edicion no precarga el documento');
        $this->addCase($results, 'form_edit_endpoint',
            str_contains($html, $BASE.'/'.self::RUTA_WEB_DOCUMENTOS.'/update') && str_contains($html, "let Metodo      = 'POST'"),
            'el formulario de edicion no publica en /documentos/update con POST');

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

            $Post      = ['idEntidad' => 91, 'Nombre' => 'Contrato de prueba', 'FVencimiento' => '2026-12-31'];
            $DataCheck = $reflector->invoke($Controller, $Post);

            $this->addCase($results, 'datacheck_es_arreglo', is_array($DataCheck), 'dataCheck() no devolvio un arreglo');
            $this->addCase($results, 'datacheck_privado', $reflector->isPrivate(), 'dataCheck() debe ser privado');
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post,
                'dataCheck() no conserva el POST recibido');
            $this->addCase($results, 'datacheck_claves_contrato',
                isset($DataCheck['emptyData'], $DataCheck['ValidarNumero'], $DataCheck['ValidarEntero'], $DataCheck['ValidarFecha'], $DataCheck['ValidarLargoMinimoN'], $DataCheck['ValidarLargoMaximoPorCampo']),
                'dataCheck() no entrega las claves del contrato de CheckData');
            $this->addCase($results, 'datacheck_id_entero',
                trim((string)$DataCheck['ValidarEntero']) === 'idEntidad' && trim((string)$DataCheck['ValidarNumero']) === 'idEntidad',
                'dataCheck() no valida idEntidad como entero');
            $this->addCase($results, 'datacheck_fecha_vencimiento', trim((string)$DataCheck['ValidarFecha']) === 'FVencimiento',
                'dataCheck() no valida la fecha de vencimiento');
            $this->addCase($results, 'datacheck_largos',
                str_contains((string)$DataCheck['ValidarLargoMinimo'], 'Nombre') && str_contains((string)$DataCheck['ValidarLargoMinimo'], 'Observacion')
                && (int)($DataCheck['ValidarLargoMaximoPorCampo']['Nombre'] ?? 0) === 255
                && (int)($DataCheck['ValidarLargoMinimoN'] ?? 0) === 3,
                'dataCheck() no limita Nombre a 3..255 y Observacion a un minimo de 3');
            $this->addCase($results, 'datacheck_censura',
                str_contains((string)$DataCheck['ValidarPalabrasCensuradas'], 'Nombre') && str_contains((string)$DataCheck['ValidarPalabrasCensuradas'], 'Observacion'),
                'dataCheck() no filtra palabras censuradas en nombre y observacion');
            $this->addCase($results, 'datacheck_sin_otros_validadores',
                trim((string)$DataCheck['ValidarEmail']) === '' && trim((string)$DataCheck['ValidarRut']) === '' && trim((string)$DataCheck['ValidarURL']) === '',
                'dataCheck() valida email, rut o URL que el documento no tiene');
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_es_arreglo', false,
                'no se pudo ejecutar dataCheck() ('.$e->getMessage().')');
        }

        /*====================================*/
        // Nombre de archivo generado: prefijo, marca de tiempo, aleatorio y unico
        /*====================================*/
        try {
            $generar = new ReflectionMethod(self::CONTROLADOR, 'generarNombreArchivo');
            $generar->setAccessible(true);
            $Controller = (new ReflectionClass(self::CONTROLADOR))->newInstanceWithoutConstructor();
            $n1 = (string)$generar->invoke($Controller, 'EntidadDoc');
            $n2 = (string)$generar->invoke($Controller, 'EntidadDoc');
            $this->addCase($results, 'nombre_archivo_formato', (bool)preg_match('/^EntidadDoc_\d{14}_[0-9a-f]{16}$/', $n1),
                'el nombre generado ('.$n1.') no sigue el formato EntidadDoc_AAAAMMDDHHMMSS_<16 hex>');
            $this->addCase($results, 'nombre_archivo_unico', $n1 !== $n2,
                'dos nombres generados seguidos son iguales');
            $this->addCase($results, 'nombre_archivo_sin_ruta', !preg_match('#[/\\\\.]#', $n1),
                'el nombre generado contiene separadores de ruta o puntos');
        } catch (Throwable $e) {
            $this->addCase($results, 'nombre_archivo_formato', false,
                'no se pudo ejecutar generarNombreArchivo() ('.$e->getMessage().')');
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
     * Revisa estaticamente las vistas de documentos y el controlador: rutas relativas, rutas
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
            'se esperaban 4 vistas de documentos y se encontraron '.count($arrVistas));

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
        $this->addCase($results, 'tabla_id_cifrado', str_contains($tabla, "encryptDecrypt('encrypt', \$crud['idDocumentos'])"),
            'UpdateList no cifra idDocumentos en las acciones');
        $this->addCase($results, 'tabla_fecha_estandar', str_contains($tabla, 'fechaEstandar('),
            'UpdateList no formatea el vencimiento con fechaEstandar()');
        $view = $this->codigoFuente($this->rutaModulo('../views/'.self::VISTA_BASE.'-View.php'));
        $this->addCase($results, 'view_vista_previa_widget', str_contains($view, "previewDocs(\$BASE, 'upload', \$data['rowData']['NombreArchivo'])"),
            'View debe delegar la vista previa en previewDocs() con la carpeta upload');
        $this->addCase($results, 'view_sin_enlace_directo_php', !preg_match('/href=["\'][^"\']*\.php/i', $view),
            'View enlaza directamente a un archivo PHP');

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
