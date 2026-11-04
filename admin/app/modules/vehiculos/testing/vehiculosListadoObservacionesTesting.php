<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class vehiculosListadoObservacionesTesting
 *
 * Pruebas del modulo `vehiculos` - controlador `vehiculosListadoObservaciones` (observaciones de un
 * vehiculo, embebidas en la pestana "Observaciones" del resumen de `vehiculosListado`).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. El normalizador
 *    `sistemaTesteos::normalizeResult()` marca EXITOSA o ERROR segun el contenido, por lo que
 *    los textos de los casos correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Alcance: pruebas NO destructivas. Los metodos que escriben en base de datos (`Insert()`,
 * `Update()`, `Delete()`) terminan en `Response::success()`/`Response::error()`, que ejecutan `exit`,
 * y abren transaccion; por eso NO se invocan: se verifican por reflexion y por lectura del fuente.
 * El controlador comparte la raiz de permisos `vehiculosListado` (su `controllerName`).
 *
 * @see skills/testing/SKILL.md
 */
class vehiculosListadoObservacionesTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR          = 'vehiculosListadoObservaciones';
    private const CONTROLADOR_PERMISOS = 'vehiculosListado';
    private const TABLA                = 'vehiculos_listado_observaciones';
    private const VISTA_BASE           = 'vehiculosListado-Resumen-Observaciones';
    private const RUTA_WEB             = 'administracion/vehiculos/listado';
    private const RUTA_OBSERVACIONES   = 'administracion/vehiculos/listado/observaciones';
    // Niveles de permiso del catalogo (1=ver, 2=editar, 3=crear, 4=borrar)
    private const NIVELES              = [1, 2, 3, 4];
    // Verbos HTTP del instalador (idMetodo): 1=GET, 2=POST, 3=DELETE, 4=PUT
    private const VERBOS               = [1 => 'GET', 2 => 'POST', 3 => 'DELETE', 4 => 'PUT'];
    // Palabras que delatan codigo copiado de otro modulo (entidades)
    private const RESIDUOS_OTRO_MODULO = '/(TipoEntidad|ApellidoPat|ApellidoMat|RazonSocial|FNacimiento|RepLegal)/';

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

    private function fuenteControlador(): string{
        return $this->codigoFuente($this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'));
    }

    private function fuenteInstalador(): string{
        return $this->codigoFuente($this->rutaModulo('../installer/vehiculosInstaller.php'));
    }

    /*******************************************************************/
    // Instancia sin ejecutar el constructor (logica pura, sin conexion a BD)
    /*******************************************************************/
    private function instanciaSinBD(string $clase){
        return (new ReflectionClass($clase))->newInstanceWithoutConstructor();
    }

    /*******************************************************************/
    // Indica si las marcas aparecen en el texto y en ese orden
    /*******************************************************************/
    private function enOrden(string $texto, array $marcas): bool{
        $posicion = -1;
        foreach ($marcas as $marca) {
            $encontrada = strpos($texto, $marca, $posicion + 1);
            if ($encontrada === false) {
                return false;
            }
            $posicion = $encontrada;
        }

        return true;
    }

    /*******************************************************************/
    // Rutas del instalador de un controlador
    // Formato: [ 'idMetodo RutaWeb' => ['handler','nivel','ruta','metodo'] ]
    /*******************************************************************/
    private function rutasInstalador(string $controlador): array{
        $rutas = [];
        foreach (preg_split('/\r\n|\r|\n/', $this->fuenteInstalador()) as $linea) {
            if (!str_contains($linea, "'".$controlador.'->')) {
                continue;
            }
            $ok = preg_match(
                "/'idMetodo'\s*=>\s*(\d+)\s*,\s*'RutaWeb'\s*=>\s*'([^']+)'\s*,\s*'RutaController'\s*=>\s*'([^']+)'.*?'idLevelLimit'\s*=>\s*'?(\d+)'?/",
                $linea, $m
            );
            if ($ok !== 1) {
                continue;
            }
            $rutas[$m[1].' '.$m[2]] = [
                'handler' => $m[3],
                'nivel'   => (int)$m[4],
                'ruta'    => $m[2],
                'metodo'  => (int)$m[1],
            ];
        }

        return $rutas;
    }

    /*******************************************************************/
    // Columnas de una tabla declaradas en el instalador
    /*******************************************************************/
    private function columnasTabla(string $tabla): array{
        $patron = "/'table'\s*=>\s*'".preg_quote($tabla, '/')."'\s*,\s*'data'\s*=>\s*'([^']*)'/";
        if (preg_match($patron, $this->fuenteInstalador(), $m) !== 1) {
            return [];
        }
        preg_match_all('/`(\w+)`/', $m[1], $columnas);

        return $columnas[1] ?? [];
    }

    /*******************************************************************/
    // Campos separados por coma de una clave ('data','required'...) en un texto
    /*******************************************************************/
    private function camposDeClave(string $texto, string $clave): array{
        if (preg_match("/'".$clave."'\s*=>\s*'([^']*)'/", $texto, $m) !== 1) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $m[1])), static fn(string $c): bool => $c !== ''));
    }

    /*******************************************************************/
    // Nombres de los campos (input/select/textarea) de un HTML
    /*******************************************************************/
    private function camposDeHtml(string $html): array{
        preg_match_all('/<(?:input|select|textarea)\b[^>]*\sname="(\w+)"/', $html, $m);

        return array_values(array_unique($m[1]));
    }

    /*******************************************************************/
    // Renderiza una vista del modulo con datos sinteticos y devuelve el HTML.
    // - El CWD se mueve a admin/public (las plantillas de inputs usan rutas relativas).
    // - Los require_once internos se reescriben como include para que cada renderizado
    //   sea independiente del orden de ejecucion de las pruebas.
    // - Un warning de la plantilla se convierte en excepcion: solo falla ese caso.
    /*******************************************************************/
    private function renderizarVista(string $rutaVista, array $data, string $BASE, $f3 = null): string{
        if (!is_file($rutaVista)) {
            return '';
        }
        $dirVistas = dirname($rutaVista).'/';
        $codigo    = (string)file_get_contents($rutaVista);
        $codigo    = preg_replace("/require_once\(\s*'([^']+)'\s*\)/", "include(\$dirVistas.'$1')", $codigo);

        $cwd = getcwd();
        @chdir($this->rutaModulo('../../../../public'));
        set_error_handler(static function (int $nivel, string $mensaje, string $archivo, int $linea): bool {
            throw new ErrorException($mensaje, 0, $nivel, $archivo, $linea);
        });
        ob_start();
        try {
            eval('?>'.$codigo);
        } catch (Throwable $e) {
            ob_end_clean();
            return '';
        } finally {
            restore_error_handler();
            @chdir($cwd);
        }

        return (string)ob_get_clean();
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
    // Cuerpo (fuente) de un metodo del controlador, por reflexion
    /*******************************************************************/
    private function cuerpoMetodo(string $metodo): string{
        if (!method_exists(self::CONTROLADOR, $metodo)) {
            return '';
        }
        $reflector = new ReflectionMethod(self::CONTROLADOR, $metodo);
        $lineas    = preg_split('/\r\n|\r|\n/', $this->fuenteControlador());

        return implode("\n", array_slice($lineas, $reflector->getStartLine() - 1,
            $reflector->getEndLine() - $reflector->getStartLine() + 1));
    }

    /*******************************************************************/
    // Contrato de presentacion de un metodo (vista, errores y usuario)
    /*******************************************************************/
    private function contratoPresentacion(array &$results, string $metodo, string $vista, string $tipoVista){
        $cuerpo = $this->cuerpoMetodo($metodo);
        $this->addCase($results, 'vista_'.$metodo, str_contains($cuerpo, $vista),
            $metodo.'() no instancia la vista '.$vista);
        $this->addCase($results, 'tipoview_'.$metodo, str_contains($cuerpo, 'showVista('.$tipoVista.','),
            $metodo.'() no usa showVista('.$tipoVista.')');
        $this->addCase($results, 'rutavista_'.$metodo, str_contains($cuerpo, 'returnRutaVista('),
            $metodo.'() no resuelve la ruta de la vista');
        $this->addCase($results, 'rutaruta_'.$metodo, str_contains($cuerpo, '$this->controllerName'),
            $metodo.'() no referencia $this->controllerName');
        $this->addCase($results, 'errores_'.$metodo, str_contains($cuerpo, 'showError('),
            $metodo.'() no maneja la ruta de error con showError()');
        $this->addCase($results, 'usuario_'.$metodo, str_contains($cuerpo, 'getUserData($f3)'),
            $metodo.'() no obtiene los datos del usuario');
    }

    /*******************************************************************/
    // Datos sinteticos usados por las vistas del controlador
    /*******************************************************************/
    private function datosSinteticos(int $nivel): array{
        return [
            'UserData'        => [
                'MainPathUrl'          => '/admin/public/upload/',
                'sistemaModalSubtitle' => 1,
                'sistemaModalCloseBTN' => 2,
                'UserType'             => 2,
            ],
            'UserAccess'      => ['LevelAccess' => $nivel, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_FormInputs'    => new UIFormInputs(),
            'Fnc_Codification'  => new FunctionsSecurityCodification(),
            'Fnc_DataDate'      => new FunctionsDataDate(),
            'Fnc_WidgetsCommon' => new UIWidgetsCommon(),
            'Fnc_WidgetsViews'  => new UIWidgetsViews(),
            'Fnc_ServerServer'  => new FunctionsServerServer(),
            'arrObservaciones'  => [[
                'idObservaciones' => 1,
                'Observacion'     => 'Observacion de prueba',
                'FechaCreacion'   => '2026-01-02',
                'FechaEdicion'    => null,
                'Usuario'         => 'Tester',
                'UsuarioEdicion'  => null,
            ]],
            'rowData'         => [
                'idObservaciones' => 1,
                'idVehiculo'      => 1,
                'Observacion'     => 'Observacion de prueba',
                'FechaCreacion'   => '2026-01-02',
                'FechaEdicion'    => null,
                'Usuario'         => 'Tester',
                'UsuarioEdicion'  => null,
            ],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica los archivos del controlador `vehiculosListadoObservaciones` (controlador, vistas,
     * testing), la proteccion de acceso directo y la convencion "clase = nombre de archivo".
     *
     * @return array Resultado por caso
     */
    public function estructuraModuloTest(){

        $results = [];

        /*====================================*/
        // Archivos obligatorios del controlador
        /*====================================*/
        $arrArchivos = [
            'controlador'     => $this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'),
            'vista_form_new'  => $this->rutaModulo('../views/'.self::VISTA_BASE.'-formNew.php'),
            'vista_form_edit' => $this->rutaModulo('../views/'.self::VISTA_BASE.'-formEdit.php'),
            'vista_update'    => $this->rutaModulo('../views/'.self::VISTA_BASE.'-UpdateList.php'),
            'vista_view'      => $this->rutaModulo('../views/'.self::VISTA_BASE.'-View.php'),
            'testing'         => $this->rutaModulo('vehiculosListadoObservacionesTesting.php'),
        ];
        foreach ($arrArchivos as $nombre => $ruta) {
            $this->addCase($results, 'archivo_'.$nombre, is_file($ruta), 'no se encuentra '.$ruta);
        }

        /*====================================*/
        // Proteccion de acceso directo
        /*====================================*/
        $arrHtaccess = [
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
        $this->addCase($results, 'clase_testing_cargada', class_exists('vehiculosListadoObservacionesTesting'),
            'la clase vehiculosListadoObservacionesTesting no esta disponible');
        $padreController = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padreController === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padreController.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('vehiculosListadoObservacionesTesting') === 'ControllerBase',
            'el testing no extiende ControllerBase');

        /*====================================*/
        // Constructor: conexion MySQL_1 y raiz de permisos compartida
        /*====================================*/
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'constructor_conexion', str_contains($codigo, 'ConfigDataBase::MySQL_1'),
            'el constructor no usa la conexion ConfigDataBase::MySQL_1');
        $this->addCase($results, 'constructor_padre', str_contains($codigo, 'parent::__construct($DB_conn_1, $queryBuilder, $checkData)'),
            'el constructor no llama a parent::__construct con la conexion, el QueryBuilder y el CheckData');
        $this->addCase($results, 'permisos_raiz_compartida',
            str_contains($codigo, "\$this->controllerName = '".self::CONTROLADOR_PERMISOS."'"),
            'controllerName debe ser '.self::CONTROLADOR_PERMISOS.' para heredar su nivel de acceso');

        /*====================================*/
        // Las vistas del controlador no se confunden con las de otros sub-controladores
        /*====================================*/
        $arrVistas = glob($this->rutaModulo('../views/'.self::VISTA_BASE.'-*.php')) ?: [];
        $this->addCase($results, 'vistas_esperadas', count($arrVistas) === 4,
            'se encontraron '.count($arrVistas).' vistas '.self::VISTA_BASE.'-* (esperadas 4)');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                            CONTROLADOR: VISTAS                             */
    /******************************************************************************/
    /**
     * Verifica el contrato de los metodos que devuelven HTML (`New`, `UpdateList`, `View`,
     * `GetID`): firma, `showVista(2, ...)`, `returnRutaVista()`, `getUserData()` y que descifran,
     * validan y enlazan como parametro el ID recibido.
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];

        /*====================================*/
        // Firma de cada metodo de vista
        /*====================================*/
        foreach (['New', 'UpdateList', 'View', 'GetID'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 2, 2);
        }

        /*====================================*/
        // Contrato de presentacion (todas son modales: showVista(2))
        /*====================================*/
        $this->contratoPresentacion($results, 'New', "\$this->controllerName.'-Resumen-Observaciones-formNew.php'", '2');
        $this->contratoPresentacion($results, 'UpdateList', "\$this->controllerName.'-Resumen-Observaciones-UpdateList.php'", '2');
        $this->contratoPresentacion($results, 'View', "\$this->controllerName.'-Resumen-Observaciones-View.php'", '2');
        $this->contratoPresentacion($results, 'GetID', "\$this->controllerName.'-Resumen-Observaciones-formEdit.php'", '2');

        /*====================================*/
        // ID cifrado: se descifra, se valida (400) y se enlaza como parametro
        /*====================================*/
        $arrIds = ['New' => 'VehiculoID', 'UpdateList' => 'VehiculoID', 'View' => 'ObservacionesID', 'GetID' => 'ObservacionesID'];
        foreach ($arrIds as $metodo => $variable) {
            $cuerpo = $this->cuerpoMetodo($metodo);
            $this->addCase($results, 'id_descifrado_'.$metodo,
                str_contains($cuerpo, "\$".$variable." = \$this->Codification->encryptDecrypt('decrypt', \$params['id'])"),
                $metodo.'() no descifra $params[\'id\']');
            $this->addCase($results, 'id_validado_'.$metodo,
                str_contains($cuerpo, "isValidDecrypted(\$".$variable.", 'id')") && str_contains($cuerpo, "Response::error('Registro inv"),
                $metodo.'() no rechaza con 400 un ID invalido');
            $this->addCase($results, 'id_enlazado_'.$metodo,
                str_contains($cuerpo, "'params'  => [\$".$variable."['data']]"),
                $metodo.'() no enlaza el ID como parametro de la consulta');
        }

        /*====================================*/
        // Consultas de cada vista
        /*====================================*/
        $new = $this->cuerpoMetodo('New');
        $this->addCase($results, 'new_verifica_vehiculo',
            str_contains($new, "'table'   => 'vehiculos_listado'") && str_contains($new, "'where'   => 'idVehiculo = ?'"),
            'New() no comprueba que el vehiculo exista antes de mostrar el formulario');

        $lista = $this->cuerpoMetodo('UpdateList');
        $this->addCase($results, 'lista_filtra_por_vehiculo', str_contains($lista, 'vehiculos_listado_observaciones.idVehiculo = ?'),
            'UpdateList() no filtra las observaciones por vehiculo');
        $this->addCase($results, 'lista_autor_y_editor',
            str_contains($lista, 'AS Usuario') && str_contains($lista, 'AS UsuarioEdicion'),
            'UpdateList() no trae el autor y el editor de cada observacion');
        $this->addCase($results, 'lista_orden', str_contains($lista, "'order'   => 'vehiculos_listado_observaciones.idObservaciones ASC'"),
            'UpdateList() no ordena las observaciones cronologicamente');
        $this->addCase($results, 'lista_limite', str_contains($lista, 'N_MaxItems'),
            'UpdateList() no limita las filas con ConfigAPP::APP["N_MaxItems"]');

        $view = $this->cuerpoMetodo('View');
        $this->addCase($results, 'view_filtra_por_observacion', str_contains($view, 'vehiculos_listado_observaciones.idObservaciones = ?'),
            'View() no filtra por idObservaciones');
        $this->addCase($results, 'view_autor_y_editor',
            str_contains($view, 'AS Usuario') && str_contains($view, 'AS UsuarioEdicion'),
            'View() no trae el autor y el editor de la observacion');

        $getId = $this->cuerpoMetodo('GetID');
        $this->addCase($results, 'getid_campos', str_contains($getId, "'idObservaciones,idVehiculo,Observacion,FechaCreacion'"),
            'GetID() no entrega idObservaciones,idVehiculo,Observacion,FechaCreacion que usa el formulario');
        $this->addCase($results, 'getid_filtra_por_observacion', str_contains($getId, "'where'   => 'idObservaciones = ?'"),
            'GetID() no filtra por idObservaciones');

        /*====================================*/
        // Fallback de errores en las cuatro vistas
        /*====================================*/
        foreach (['New', 'UpdateList', 'View', 'GetID'] as $metodo) {
            $this->addCase($results, 'fallback_'.$metodo, str_contains($this->cuerpoMetodo($metodo), 'mergeResponses('),
                $metodo.'() no agrupa las respuestas fallidas con mergeResponses()');
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                         CONTROLADOR: ESCRITURA (CRUD)                      */
    /******************************************************************************/
    /**
     * Verifica el contrato de `Insert`, `Update` y `Delete` SIN ejecutarlos: terminan en
     * `Response::*()` (que hace `exit`) y escriben en BD dentro de una transaccion. Se comprueba
     * firma, verbo HTTP, que autor y fecha de edicion los fije el servidor ANTES del chequeo, el
     * orden Begin -> Base_* -> Commit y que la edicion conserve autor y fecha de creacion.
     *
     * @return array Resultado por caso
     */
    public function controladorEscrituraTest(){

        $results = [];

        /*====================================*/
        // Firma de cada metodo de escritura (Insert/Update reciben F3 para la sesion)
        /*====================================*/
        $this->firmaMetodo($results, 'Insert', 1, 1);
        $this->firmaMetodo($results, 'Update', 1, 1);
        $this->firmaMetodo($results, 'Delete', 0, 0);

        /*====================================*/
        // Insert: el autor sale de la sesion, no del cliente
        /*====================================*/
        $insert = $this->cuerpoMetodo('Insert');
        $this->addCase($results, 'insert_autor_de_sesion',
            str_contains($insert, "\$_POST['idUsuario'] = \$f3->get('SESSION.DataInfo.UserID')"),
            'Insert() no toma el autor de SESSION.DataInfo.UserID');
        $this->addCase($results, 'insert_autor_antes_del_chequeo',
            $this->enOrden($insert, ["\$_POST['idUsuario']", '$this->dataCheck($_POST)']),
            'Insert() debe fijar idUsuario antes de construir el DataCheck (un valor del cliente seria sobrescrito)');
        $this->addCase($results, 'insert_tabla', str_contains($insert, "'table'     => '".self::TABLA."'"),
            'Insert() no apunta a la tabla '.self::TABLA);
        $this->addCase($results, 'insert_requeridos', str_contains($insert, "'required'  => 'idUsuario,idVehiculo,Observacion,FechaCreacion'"),
            'Insert() no exige idUsuario,idVehiculo,Observacion,FechaCreacion');
        $this->addCase($results, 'insert_orden_transaccion',
            $this->enOrden($insert, ['Base_transactionBegin()', 'Base_insert(', 'failOperation(', 'Base_transactionCommit()', 'Response::success(']),
            'Insert() debe seguir el orden Begin -> Base_insert -> failOperation -> Commit -> Response::success');

        /*====================================*/
        // Update: verbo, editor y fecha de edicion fijados por el servidor, conserva autor
        /*====================================*/
        $update = $this->cuerpoMetodo('Update');
        $this->addCase($results, 'update_verbo_post', str_contains($update, "REQUEST_METHOD'] !== 'POST'") && str_contains($update, '405'),
            'Update() no rechaza con 405 un verbo distinto de POST');
        $this->addCase($results, 'update_verbo_antes_de_escribir',
            $this->enOrden($update, ["REQUEST_METHOD'] !== 'POST'", 'Base_transactionBegin()', 'Base_update(']),
            'Update() debe validar el verbo antes de abrir la transaccion y escribir');
        $this->addCase($results, 'update_editor_de_sesion',
            str_contains($update, "\$_POST['idUsuarioEdicion'] = \$f3->get('SESSION.DataInfo.UserID')")
            && str_contains($update, "\$_POST['FechaEdicion']     = date("),
            'Update() no fija idUsuarioEdicion (sesion) y FechaEdicion (servidor)');
        $this->addCase($results, 'update_editor_antes_del_chequeo',
            $this->enOrden($update, ["\$_POST['idUsuarioEdicion']", "\$_POST['FechaEdicion']", '$this->dataCheck($_POST)']),
            'Update() debe fijar editor y fecha antes de construir el DataCheck');
        $this->addCase($results, 'update_donde', str_contains($update, "'where'     => 'idObservaciones'"),
            'Update() no delimita la escritura por idObservaciones');
        $this->addCase($results, 'update_requeridos', str_contains($update, "'required'  => 'idObservaciones,Observacion,idUsuarioEdicion,FechaEdicion'"),
            'Update() no exige idObservaciones,Observacion,idUsuarioEdicion,FechaEdicion');
        $camposUpdate = $this->camposDeClave($update, 'data');
        $this->addCase($results, 'update_conserva_autor_y_creacion',
            !array_intersect($camposUpdate, ['idUsuario', 'FechaCreacion', 'idVehiculo']),
            'Update() permite cambiar el autor, la fecha de creacion o el vehiculo de la observacion');
        $this->addCase($results, 'update_orden_transaccion',
            $this->enOrden($update, ['Base_transactionBegin()', 'Base_update(', 'failOperation(', 'Base_transactionCommit()', 'Response::success(']),
            'Update() debe seguir el orden Begin -> Base_update -> failOperation -> Commit -> Response::success');

        /*====================================*/
        // Delete: verbo DELETE, transaccion y sin archivos
        /*====================================*/
        $delete = $this->cuerpoMetodo('Delete');
        $this->addCase($results, 'delete_verbo', str_contains($delete, "REQUEST_METHOD'] !== 'DELETE'") && str_contains($delete, '405'),
            'Delete() no rechaza con 405 un verbo distinto de DELETE');
        $this->addCase($results, 'delete_lee_cuerpo', str_contains($delete, 'parse_str(file_get_contents("php://input")'),
            'Delete() no lee el cuerpo DELETE con parse_str(php://input)');
        $this->addCase($results, 'delete_donde', str_contains($delete, "'where'       => 'idObservaciones'"),
            'Delete() no delimita el borrado por idObservaciones');
        $this->addCase($results, 'delete_sin_archivos', str_contains($delete, "'files'       => ''"),
            'Delete() declara archivos a borrar y las observaciones no tienen archivos');
        $this->addCase($results, 'delete_orden_transaccion',
            $this->enOrden($delete, ["REQUEST_METHOD'] !== 'DELETE'", 'Base_transactionBegin()', 'Base_delete(', 'failOperation(', 'Base_transactionCommit()', 'Response::success(']),
            'Delete() debe seguir el orden verbo -> Begin -> Base_delete -> failOperation -> Commit -> Response::success');

        /*====================================*/
        // Ninguna escritura arma SQL a mano
        /*====================================*/
        foreach (['Insert' => $insert, 'Update' => $update, 'Delete' => $delete] as $metodo => $cuerpo) {
            $this->addCase($results, 'sin_sql_manual_'.$metodo,
                !preg_match('/\b(INSERT\s+INTO|UPDATE\s+\w+\s+SET|DELETE\s+FROM)\b/i', $cuerpo),
                $metodo.'() contiene SQL escrito a mano en vez de usar los Base_*');
        }

        /*====================================*/
        // Coherencia con el borrado en cascada de vehiculosListado::Delete()
        /*====================================*/
        $listado = $this->codigoFuente($this->rutaModulo('../controller/vehiculosListado.php'));
        $this->addCase($results, 'cascada_desde_listado',
            str_contains($listado, "['files' => '',              'table' => '".self::TABLA."']"),
            'vehiculosListado::Delete() no borra las observaciones al eliminar un vehiculo');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                      CONTROLADOR: RUTAS DEL INSTALADOR                     */
    /******************************************************************************/
    /**
     * Verifica, leyendo el fuente del instalador (sin ejecutarlo), que las 7 rutas del controlador
     * esten registradas con verbo, handler y nivel correctos, y que cada endpoint al que llaman
     * las vistas de observaciones exista en el instalador.
     *
     * @return array Resultado por caso
     */
    public function controladorRutasTest(){

        $results = [];
        $rutas   = $this->rutasInstalador(self::CONTROLADOR);

        /*====================================*/
        // El instalador declara las 7 rutas del controlador
        /*====================================*/
        $this->addCase($results, 'instalador_rutas', count($rutas) === 7,
            'el instalador declara '.count($rutas).' rutas de '.self::CONTROLADOR.' (esperadas 7)');

        /*====================================*/
        // Nivel, verbo, prefijo y handler validos en cada ruta
        /*====================================*/
        $nivelOk   = true;
        $handlerOk = true;
        $metodoOk  = true;
        $prefijoOk = true;
        foreach ($rutas as $ruta) {
            if (!in_array($ruta['nivel'], self::NIVELES, true)) {
                $nivelOk = false;
            }
            $partes = explode('->', $ruta['handler']);
            if ($partes[0] !== self::CONTROLADOR || !method_exists(self::CONTROLADOR, $partes[1] ?? '')) {
                $handlerOk = false;
            }
            if (!isset(self::VERBOS[$ruta['metodo']])) {
                $metodoOk = false;
            }
            if (!str_starts_with($ruta['ruta'], self::RUTA_OBSERVACIONES)) {
                $prefijoOk = false;
            }
        }
        $this->addCase($results, 'instalador_nivel', $nivelOk,
            'hay rutas con idLevelLimit fuera del catalogo 1..4');
        $this->addCase($results, 'instalador_handler', $handlerOk,
            'hay rutas cuyo RutaController no apunta a un metodo existente de '.self::CONTROLADOR);
        $this->addCase($results, 'instalador_verbo', $metodoOk,
            'hay rutas con idMetodo fuera de 1..4 (GET/POST/DELETE/PUT)');
        $this->addCase($results, 'instalador_prefijo', $prefijoOk,
            'hay rutas que no cuelgan de '.self::RUTA_OBSERVACIONES);

        /*====================================*/
        // Matriz esperada: verbo + ruta + handler + nivel
        /*====================================*/
        $arrAcciones = [
            ['metodo' => 1, 'ruta' => self::RUTA_OBSERVACIONES.'/new/@id',        'handler' => 'New',        'nivel' => 2],
            ['metodo' => 1, 'ruta' => self::RUTA_OBSERVACIONES.'/updateList/@id', 'handler' => 'UpdateList', 'nivel' => 2],
            ['metodo' => 1, 'ruta' => self::RUTA_OBSERVACIONES.'/view/@id',       'handler' => 'View',       'nivel' => 2],
            ['metodo' => 1, 'ruta' => self::RUTA_OBSERVACIONES.'/getID/@id',      'handler' => 'GetID',      'nivel' => 2],
            ['metodo' => 2, 'ruta' => self::RUTA_OBSERVACIONES,                   'handler' => 'Insert',     'nivel' => 2],
            ['metodo' => 2, 'ruta' => self::RUTA_OBSERVACIONES.'/update',         'handler' => 'Update',     'nivel' => 2],
            ['metodo' => 3, 'ruta' => self::RUTA_OBSERVACIONES,                   'handler' => 'Delete',     'nivel' => 2],
        ];
        foreach ($arrAcciones as $accion) {
            $clave = $accion['metodo'].' '.$accion['ruta'];
            $caso  = 'ruta_'.strtolower($accion['handler']).'_'.self::VERBOS[$accion['metodo']];
            if (!isset($rutas[$clave])) {
                $this->addCase($results, $caso, false, 'el instalador no declara '.self::VERBOS[$accion['metodo']].' '.$accion['ruta']);
                continue;
            }
            $this->addCase($results, $caso, strcasecmp($rutas[$clave]['handler'], self::CONTROLADOR.'->'.$accion['handler']) === 0,
                'la ruta '.$accion['ruta'].' apunta a '.$rutas[$clave]['handler'].' en vez de '.self::CONTROLADOR.'->'.$accion['handler']);
            $this->addCase($results, $caso.'_nivel', $rutas[$clave]['nivel'] === $accion['nivel'],
                'la ruta '.$accion['ruta'].' exige nivel '.$rutas[$clave]['nivel'].' en vez de '.$accion['nivel']);
        }

        /*====================================*/
        // El verbo registrado coincide con el que valida el controlador
        /*====================================*/
        foreach (['Update' => 'POST', 'Delete' => 'DELETE'] as $metodo => $verbo) {
            $registrado = '';
            foreach ($rutas as $ruta) {
                if (strcasecmp($ruta['handler'], self::CONTROLADOR.'->'.$metodo) === 0) {
                    $registrado = self::VERBOS[$ruta['metodo']] ?? '';
                }
            }
            $this->addCase($results, 'verbo_coincide_'.$metodo, $registrado === $verbo
                && str_contains($this->cuerpoMetodo($metodo), "REQUEST_METHOD'] !== '".$verbo."'"),
                $metodo.'() valida '.$verbo.' pero el instalador lo registra como '.($registrado ?: 'inexistente'));
        }

        /*====================================*/
        // Cada endpoint de observaciones que usan las vistas existe en el instalador
        /*====================================*/
        $endpoints = [];
        foreach (glob($this->rutaModulo('../views/vehiculosListado-*.php')) ?: [] as $vista) {
            if (preg_match_all("/RouteAccess'\]\.'(\/observaciones[^']*)'/", $this->codigoFuente($vista), $m)) {
                foreach ($m[1] as $endpoint) {
                    $endpoints[rtrim($endpoint, '/')] = basename($vista);
                }
            }
        }
        $this->addCase($results, 'vistas_usan_endpoints', count($endpoints) >= 6,
            'las vistas del modulo solo referencian '.count($endpoints).' endpoints de observaciones (esperados al menos 6)');
        foreach ($endpoints as $endpoint => $vista) {
            $existe = false;
            foreach ($rutas as $ruta) {
                $registrada = substr($ruta['ruta'], strlen(self::RUTA_WEB));
                if ($registrada === $endpoint || str_starts_with($registrada, $endpoint.'/@')) {
                    $existe = true;
                }
            }
            $this->addCase($results, 'endpoint_registrado_'.str_replace('/', '_', ltrim($endpoint, '/')), $existe,
                $vista.' llama a '.self::RUTA_WEB.$endpoint.' que el instalador no registra');
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                PERMISOS                                    */
    /******************************************************************************/
    /**
     * Verifica la sesion del usuario, el nivel de acceso heredado de `vehiculosListado` y el
     * registro de las rutas de observaciones en `SESSION.arrPermisos` y en el router de F3.
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function permisosSesionTest($f3){

        $results = [];

        /*====================================*/
        // Datos del usuario en sesion (Insert/Update los usan como autor/editor)
        /*====================================*/
        $UserData = $f3->get('SESSION.DataInfo');
        $this->addCase($results, 'sesion_usuario_presente', is_array($UserData) && !empty($UserData),
            'no hay datos de usuario en la sesion');
        $this->addCase($results, 'sesion_user_id', is_array($UserData) && isset($UserData['UserID']) && is_numeric($UserData['UserID']),
            'SESSION.DataInfo.UserID no esta disponible para registrar autor y editor');

        /*====================================*/
        // Lectura de la sesion desde el punto de vista del controlador
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

        /*====================================*/
        // El nivel otorgado debe estar en el catalogo 1..4 y apuntar a la raiz compartida
        /*====================================*/
        $nivel = is_array($arrLevel) ? ($arrLevel['LevelAccess'] ?? null) : null;
        $this->addCase($results, 'nivel_en_rango', $nivel === null || in_array((int)$nivel, self::NIVELES, true),
            'LevelAccess quedo fuera del catalogo 1..4');
        $this->addCase($results, 'ruta_de_acceso_coincide', $nivel === null || ($arrLevel['RouteAccess'] ?? '') === self::RUTA_WEB,
            'RouteAccess no coincide con '.self::RUTA_WEB);

        /*====================================*/
        // Registro dinamico de las rutas desde SESSION.arrPermisos
        /*====================================*/
        $arrPermisos = $f3->get('SESSION.arrPermisos');
        $this->addCase($results, 'permisos_de_ruta_presentes', is_array($arrPermisos),
            'SESSION.arrPermisos no esta disponible');

        if (is_array($arrPermisos)) {
            $rutaOk = false;
            foreach ($arrPermisos as $permiso) {
                if (isset($permiso['RutaWeb']) && $permiso['RutaWeb'] === self::RUTA_OBSERVACIONES.'/updateList/@id') {
                    $rutaOk = true;
                    break;
                }
            }
            // Un usuario sin permiso sobre el modulo no registra la ruta: no es un fallo del modulo
            $this->addCase($results, 'ruta_registrada', $rutaOk || $nivel === null || (int)$nivel < 2,
                'la ruta '.self::RUTA_OBSERVACIONES.'/updateList/@id no esta entre los permisos de un usuario de nivel 2 o superior');
        }

        /*====================================*/
        // Las rutas deben existir en el router de F3 con el verbo y handler esperados
        // (solo se evalua si el usuario de pruebas las tiene registradas)
        /*====================================*/
        $arrRoutes = $f3->get('ROUTES');
        $this->addCase($results, 'router_disponible', is_array($arrRoutes),
            'F3 no expuso la variable ROUTES');

        $esquema = [
            'ruta_lista_router'    => ['/'.self::RUTA_OBSERVACIONES.'/updateList/@id', 'GET',    self::CONTROLADOR.'->UpdateList'],
            'ruta_insert_router'   => ['/'.self::RUTA_OBSERVACIONES,                   'POST',   self::CONTROLADOR.'->Insert'],
            'ruta_delete_router'   => ['/'.self::RUTA_OBSERVACIONES,                   'DELETE', self::CONTROLADOR.'->Delete'],
        ];
        foreach ($esquema as $caso => $rutaEsperada) {
            [$ruta, $verbo, $handlerEsperado] = $rutaEsperada;
            if (!is_array($arrRoutes) || !isset($arrRoutes[$ruta])) {
                continue;
            }
            $handler = $arrRoutes[$ruta][0][$verbo][0] ?? '';
            $this->addCase($results, $caso, strcasecmp($handler, $handlerEsperado) === 0,
                'la ruta '.$ruta.' ('.$verbo.') apunta a '.var_export($handler, true).' en vez de '.$handlerEsperado);
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /**
     * Renderiza `...-Observaciones-UpdateList.php` con datos sinteticos: columnas, autor, acciones
     * con ID cifrado, la leyenda "Editado por" (solo si hay edicion) y lista vacia.
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
        $ruta = $this->rutaModulo('../views/'.self::VISTA_BASE.'-UpdateList.php');

        /*====================================*/
        // Tabla con una observacion sin editar
        /*====================================*/
        $tabla = $this->renderizarVista($ruta, $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'tabla_render', $tabla !== '', 'la vista UpdateList no produjo HTML');
        $this->addCase($results, 'tabla_columnas',
            str_contains($tabla, '>Fecha Creacion<') && str_contains($tabla, '>Observacion<') && str_contains($tabla, '>Acciones<'),
            'la tabla no declara las columnas Fecha Creacion, Observacion y Acciones');
        $this->addCase($results, 'tabla_datos',
            str_contains($tabla, 'Observacion de prueba') && str_contains($tabla, '<strong>Tester:</strong>'),
            'la tabla no pinta el autor y el texto de la observacion');
        $this->addCase($results, 'tabla_acciones',
            str_contains($tabla, 'tabObsView(') && str_contains($tabla, 'tabObsEdit(') && str_contains($tabla, 'tabObsDel('),
            'la tabla no ofrece Ver, Editar y Borrar');
        $this->addCase($results, 'tabla_id_cifrado', !str_contains($tabla, "tabObsView('1')"),
            'la tabla envia el ID de la observacion sin cifrar');
        $this->addCase($results, 'tabla_sin_editado', !str_contains($tabla, 'Editado por'),
            'la tabla indica "Editado por" en una observacion sin edicion');

        /*====================================*/
        // Observacion editada: se muestra quien y cuando la edito
        /*====================================*/
        $datos = $this->datosSinteticos(2);
        $datos['arrObservaciones'][0]['FechaEdicion']   = '2026-02-03 10:00:00';
        $datos['arrObservaciones'][0]['UsuarioEdicion'] = 'Editor';
        $tabla = $this->renderizarVista($ruta, $datos, $BASE, $f3);
        $this->addCase($results, 'tabla_editado_por', str_contains($tabla, 'Editado por Editor'),
            'la tabla no muestra quien edito la observacion');

        /*====================================*/
        // Lista vacia
        /*====================================*/
        $datos = $this->datosSinteticos(2);
        $datos['arrObservaciones'] = [];
        $tabla = $this->renderizarVista($ruta, $datos, $BASE, $f3);
        $this->addCase($results, 'tabla_vacia_render', $tabla !== '' && str_contains($tabla, '<tbody>'),
            'la vista UpdateList no se dibuja con una lista vacia');
        $this->addCase($results, 'tabla_vacia_sin_filas', !str_contains($tabla, 'btn-group'),
            'la vista UpdateList dibuja acciones sin registros');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                        VISTAS DE DETALLE Y FORMULARIOS                     */
    /******************************************************************************/
    /**
     * Renderiza `formNew`, `formEdit` y `View` con datos sinteticos y comprueba ids de formulario,
     * campos, endpoints con su verbo, ID cifrado, que los campos del formulario mas los que fija
     * el servidor cubran los requeridos de `Insert()`/`Update()` y que el detalle muestre la
     * leyenda de edicion.
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
        // Formulario de alta
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::VISTA_BASE.'-formNew.php'),
            $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'form_new_render', $html !== '', 'la vista formNew no produjo HTML');
        $this->addCase($results, 'form_new_id', str_contains($html, 'id="FormNewObservacion"'),
            'el formulario de alta no usa el id FormNewObservacion');
        $this->addCase($results, 'form_new_campo', str_contains($html, 'id="NewObs_Observacion"'),
            'el formulario de alta no declara el campo NewObs_Observacion');
        $this->addCase($results, 'form_new_endpoint',
            str_contains($html, self::RUTA_OBSERVACIONES."'") && str_contains($html, "Metodo      = 'POST'"),
            'el formulario de alta no publica en '.self::RUTA_OBSERVACIONES.' con POST');
        $this->addCase($results, 'form_new_refresca_lista', str_contains($html, self::RUTA_OBSERVACIONES.'/updateList/'),
            'el formulario de alta no refresca la lista de observaciones');
        $this->addCase($results, 'form_new_id_cifrado', !str_contains($html, '/updateList/1\''),
            'el formulario de alta refresca la lista con el ID del vehiculo sin cifrar');

        // Campos del formulario + los que fija el servidor deben cubrir los requeridos de Insert()
        $insert    = $this->cuerpoMetodo('Insert');
        $enviados  = $this->camposDeHtml($html);
        $faltan    = array_values(array_diff($this->camposDeClave($insert, 'required'), array_merge($enviados, ['idUsuario'])));
        $this->addCase($results, 'form_new_cubre_requeridos', !empty($enviados) && empty($faltan),
            'el formulario de alta no envia campos requeridos por Insert(): '.implode(', ', $faltan));
        $sobrantes = array_values(array_diff($enviados, $this->camposDeClave($insert, 'data')));
        $this->addCase($results, 'form_new_campos_en_insert', empty($sobrantes),
            'el formulario de alta envia campos que Insert() no escribe: '.implode(', ', $sobrantes));
        $this->addCase($results, 'form_new_sin_autor_cliente', !in_array('idUsuario', $enviados, true),
            'el formulario de alta envia idUsuario (el autor debe salir de la sesion)');

        /*====================================*/
        // Formulario de edicion
        /*====================================*/
        $html = $this->renderizarVista($this->rutaModulo('../views/'.self::VISTA_BASE.'-formEdit.php'),
            $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'form_edit_render', $html !== '', 'la vista formEdit no produjo HTML');
        $this->addCase($results, 'form_edit_id', str_contains($html, 'id="FormEditObservacion"'),
            'el formulario de edicion no usa el id FormEditObservacion');
        $this->addCase($results, 'form_edit_campo', str_contains($html, 'id="EditObs_Observacion"'),
            'el formulario de edicion no declara el campo EditObs_Observacion');
        $this->addCase($results, 'form_edit_valor', str_contains($html, 'Observacion de prueba'),
            'el formulario de edicion no precarga la observacion');
        $this->addCase($results, 'form_edit_id_oculto', str_contains($html, 'name="idObservaciones"'),
            'el formulario de edicion no envia idObservaciones');
        $this->addCase($results, 'form_edit_endpoint',
            str_contains($html, self::RUTA_OBSERVACIONES.'/update') && str_contains($html, "Metodo      = 'POST'"),
            'el formulario de edicion no publica en '.self::RUTA_OBSERVACIONES.'/update con POST');
        $this->addCase($results, 'form_edit_refresca_lista', str_contains($html, self::RUTA_OBSERVACIONES.'/updateList/'),
            'el formulario de edicion no refresca la lista de observaciones');

        $update   = $this->cuerpoMetodo('Update');
        $enviados = $this->camposDeHtml($html);
        $servidor = ['idUsuarioEdicion', 'FechaEdicion'];
        $faltan   = array_values(array_diff($this->camposDeClave($update, 'required'), array_merge($enviados, $servidor)));
        $this->addCase($results, 'form_edit_cubre_requeridos', empty($faltan),
            'el formulario de edicion no envia campos requeridos por Update(): '.implode(', ', $faltan));
        $this->addCase($results, 'form_edit_sin_editor_cliente', !array_intersect($enviados, $servidor),
            'el formulario de edicion envia idUsuarioEdicion/FechaEdicion (deben salir del servidor)');

        /*====================================*/
        // Detalle de la observacion (View)
        /*====================================*/
        $ruta = $this->rutaModulo('../views/'.self::VISTA_BASE.'-View.php');
        $html = $this->renderizarVista($ruta, $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'view_render', $html !== '', 'la vista View no produjo HTML');
        $this->addCase($results, 'view_datos', str_contains($html, 'Observacion de prueba') && str_contains($html, 'Tester'),
            'la vista View no pinta el autor y el texto de la observacion');
        $this->addCase($results, 'view_pie_cierre', str_contains($html, 'data-bs-dismiss="modal"'),
            'la vista View no ofrece el pie de cierre del modal');

        $datos = $this->datosSinteticos(2);
        $datos['rowData']['FechaEdicion']   = '2026-02-03 10:00:00';
        $datos['rowData']['UsuarioEdicion'] = 'Editor';
        $html = $this->renderizarVista($ruta, $datos, $BASE, $f3);
        $this->addCase($results, 'view_editado_por', str_contains($html, 'Editor'),
            'la vista View no muestra quien edito la observacion (usa $crud en vez de $data[\'rowData\'])');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos el chequeo de datos (`dataCheck`) y cruza las columnas que
     * consulta o escribe el controlador con las de la tabla que crea el instalador.
     *
     * @return array Resultado por caso
     */
    public function logicaValidacionTest(){

        $results = [];

        /*====================================*/
        // dataCheck devuelve el contrato esperado por Base_insert/Base_update
        /*====================================*/
        try {
            $reflector = new ReflectionMethod(self::CONTROLADOR, 'dataCheck');
            $reflector->setAccessible(true);
            $Controller = $this->instanciaSinBD(self::CONTROLADOR);

            $Post      = ['idVehiculo' => 1, 'Observacion' => 'Observacion de prueba', 'FechaCreacion' => '2026-01-02'];
            $DataCheck = $reflector->invoke($Controller, $Post);

            $this->addCase($results, 'datacheck_es_arreglo', is_array($DataCheck),
                'dataCheck() no devolvio un arreglo');
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post,
                'dataCheck() no conserva el POST recibido');
            $this->addCase($results, 'datacheck_identificador',
                ($DataCheck['ValidarNumero'] ?? '') === 'idVehiculo' && ($DataCheck['ValidarEntero'] ?? '') === 'idVehiculo',
                'dataCheck() no valida idVehiculo como entero');
            $this->addCase($results, 'datacheck_fecha', ($DataCheck['ValidarFecha'] ?? '') === 'FechaCreacion',
                'dataCheck() no valida FechaCreacion como fecha');
            $this->addCase($results, 'datacheck_censura', ($DataCheck['ValidarPalabrasCensuradas'] ?? '') === 'Observacion',
                'dataCheck() no censura palabras en Observacion');
            $this->addCase($results, 'datacheck_largo',
                (string)($DataCheck['ValidarLargoMinimo'] ?? '') === 'Observacion' && (int)($DataCheck['ValidarLargoMinimoN'] ?? 0) === 3,
                'dataCheck() no fija el largo minimo 3 de Observacion');
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_es_arreglo', false,
                'no se pudo ejecutar dataCheck() ('.$e->getMessage().')');
        }

        /*====================================*/
        // Las columnas del controlador existen en la tabla del instalador
        /*====================================*/
        $columnas = $this->columnasTabla(self::TABLA);
        $this->addCase($results, 'tabla_columnas_leidas', count($columnas) > 0,
            'no se pudieron leer las columnas de '.self::TABLA.' desde el instalador');

        if (!empty($columnas)) {
            // Columnas calificadas (vehiculos_listado_observaciones.X) en consultas y ordenamientos
            preg_match_all('/'.self::TABLA.'\.(\w+)/', $this->fuenteControlador(), $m);
            $desconocidas = array_values(array_diff(array_unique($m[1]), $columnas));
            $this->addCase($results, 'columnas_consultas_existen', empty($desconocidas),
                'el controlador consulta columnas inexistentes en '.self::TABLA.': '.implode(', ', $desconocidas));

            // Columnas sin calificar (GetID) y de escritura (Insert/Update)
            $usadas = $this->camposDeClave($this->cuerpoMetodo('GetID'), 'data');
            foreach (['Insert', 'Update'] as $metodo) {
                foreach (['data', 'required', 'unique', 'where'] as $clave) {
                    $usadas = array_merge($usadas, $this->camposDeClave($this->cuerpoMetodo($metodo), $clave));
                }
            }
            $desconocidas = array_values(array_diff(array_unique($usadas), $columnas));
            $this->addCase($results, 'columnas_escritura_existen', empty($desconocidas),
                'GetID()/Insert()/Update() usan columnas inexistentes en '.self::TABLA.': '.implode(', ', $desconocidas));

            // Todo campo validado por dataCheck existe como columna
            $validados = [];
            foreach (['ValidarNumero', 'ValidarEntero', 'ValidarFecha', 'ValidarLargoMinimo'] as $clave) {
                $validados = array_merge($validados, $this->camposDeClave($this->cuerpoMetodo('dataCheck'), $clave));
            }
            $desconocidas = array_values(array_diff(array_unique($validados), $columnas));
            $this->addCase($results, 'columnas_validadas_existen', empty($desconocidas),
                'dataCheck() valida campos que no son columnas de '.self::TABLA.': '.implode(', ', $desconocidas));

            // Los requeridos de cada escritura estan entre los datos que escribe
            foreach (['Insert', 'Update'] as $metodo) {
                $cuerpo = $this->cuerpoMetodo($metodo);
                $faltan = array_values(array_diff($this->camposDeClave($cuerpo, 'required'), $this->camposDeClave($cuerpo, 'data')));
                $this->addCase($results, 'requeridos_en_data_'.$metodo, empty($faltan),
                    $metodo.'() exige campos que no escribe: '.implode(', ', $faltan));
            }

            // Autor, editor, fecha de edicion y observacion son columnas de la tabla
            foreach (['idUsuario', 'idUsuarioEdicion', 'FechaEdicion', 'Observacion'] as $columna) {
                $this->addCase($results, 'columna_'.$columna, in_array($columna, $columnas, true),
                    'la tabla '.self::TABLA.' no declara la columna '.$columna);
            }
        }

        /*====================================*/
        // El join con usuarios usa las columnas que existen en la tabla de observaciones
        /*====================================*/
        $lista = $this->cuerpoMetodo('UpdateList');
        $this->addCase($results, 'join_autor_y_editor',
            str_contains($lista, 'usuarios_listado.idUsuario  = vehiculos_listado_observaciones.idUsuario')
            && str_contains($lista, 'usuarios_edicion.idUsuario  = vehiculos_listado_observaciones.idUsuarioEdicion'),
            'UpdateList() no une autor y editor con usuarios_listado por idUsuario/idUsuarioEdicion');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                 SEGURIDAD                                  */
    /******************************************************************************/
    /**
     * Revisa estaticamente las vistas y el controlador: rutas relativas, rutas absolutas, acceso
     * directo a la sesion, SQL embebido, codigo residual de otro modulo, ID cifrado, parametros
     * preparados y que autor/editor no dependan de datos del cliente.
     *
     * @return array Resultado por caso
     */
    public function seguridadVistasTest(){

        $results = [];

        /*====================================*/
        // Vistas del controlador
        /*====================================*/
        $arrVistas = glob($this->rutaModulo('../views/'.self::VISTA_BASE.'-*.php')) ?: [];
        $this->addCase($results, 'hay_vistas', count($arrVistas) > 0,
            'no se encontraron vistas '.self::VISTA_BASE.'-*');

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
            $this->addCase($results, 'vista_sin_sql_'.$nombre, !preg_match('/\b(SELECT|INSERT|UPDATE|DELETE)\s+\w+\s+FROM\b/i', $codigo),
                $nombre.' contiene una consulta SQL embebida');
            $this->addCase($results, 'vista_sin_residuos_'.$nombre, !preg_match(self::RESIDUOS_OTRO_MODULO, $codigo, $residuo),
                $nombre.' contiene codigo de otro modulo ('.($residuo[1] ?? '').')');
            $this->addCase($results, 'vista_sin_variables_ajenas_'.$nombre,
                !preg_match('/\$crud\b/', preg_replace('/foreach\s*\(\s*\$data\[[^\]]+\]\s+as\s+\$crud\s*\).*/s', '', $codigo, 1) ?? ''),
                $nombre.' usa $crud fuera de un foreach que la defina');
        }

        /*====================================*/
        // ID cifrado en la tabla
        /*====================================*/
        $tabla = $this->codigoFuente($this->rutaModulo('../views/'.self::VISTA_BASE.'-UpdateList.php'));
        $this->addCase($results, 'tabla_id_cifrado', str_contains($tabla, "encryptDecrypt('encrypt'"),
            'UpdateList.php no cifra el ID antes de pintarlo en las acciones');

        /*====================================*/
        // El controlador no concatena SQL desde el cliente
        /*====================================*/
        $controlador = $this->fuenteControlador();
        $this->addCase($results, 'controlador_sin_concat_sql',
            !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $controlador),
            'el controlador concatena superglobales en una cadena SQL');
        $this->addCase($results, 'controlador_parametros_preparados', str_contains($controlador, "'params'"),
            'el controlador no usa parametros preparados');
        $this->addCase($results, 'controlador_sin_residuos', !preg_match(self::RESIDUOS_OTRO_MODULO, $controlador, $residuo),
            'el controlador contiene codigo de otro modulo ('.($residuo[1] ?? '').')');

        /*====================================*/
        // Autor y editor no los decide el cliente
        /*====================================*/
        $this->addCase($results, 'autor_editor_no_vienen_del_cliente',
            substr_count($controlador, "SESSION.DataInfo.UserID") === 2
            && !preg_match('/\$_(POST|GET|REQUEST)\[[\'"]idUsuario(Edicion)?[\'"]\]\s*(\?\?|\))/', $controlador),
            'el autor y el editor deben tomarse solo de SESSION.DataInfo.UserID');

        /************************************/
        // Retorno los datos
        return $results;

    }

}
