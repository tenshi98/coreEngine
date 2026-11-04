<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class tercerosEntidadesListadoUsuariosTesting
 *
 * Pruebas del modulo `terceros` - controlador `tercerosEntidadesListadoUsuarios` (pestana "Usuarios"
 * del resumen de clientes: alta, edicion, detalle y borrado de los usuarios de un cliente, con acceso a
 * sus permisos de maquinas y notificaciones).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. El normalizador
 *    `sistemaTesteos::normalizeResult()` marca EXITOSA o ERROR segun el contenido, por lo que
 *    los textos de los casos correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Alcance: pruebas NO destructivas. `Insert()`, `Update()` y `Delete()` escriben en base de datos y
 * terminan en `Response::success()`/`Response::error()` (que ejecutan `exit`); por eso NO se invocan:
 * se verifican por reflexion y por lectura del fuente. `InstallModule()`/`UninstallModule()` tampoco
 * se ejecutan: solo se lee `getRouteDefinitions()` sin conexion a BD.
 *
 * Notas del controlador:
 *  - Comparte el permiso del controlador `tercerosEntidadesListado` (`$this->controllerName`), por lo
 *    que el nivel de acceso y la ruta raiz son los de ese controlador.
 *  - Sus vistas usan el prefijo `tercerosEntidadesListado-Resumen-Usuarios-` y el JavaScript que las
 *    consume (`tabUsuarios*`) vive en `tercerosEntidadesListado-Resumen.php`.
 *
 * @see skills/testing/SKILL.md
 */
class tercerosEntidadesListadoUsuariosTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR      = 'tercerosEntidadesListadoUsuarios';
    private const INSTALADOR       = 'tercerosEntidadesInstaller';
    // Controlador cuyo permiso comparte (valor de $this->controllerName)
    private const CONTROLADOR_PERMISOS = 'tercerosEntidadesListado';
    private const VISTA_BASE       = 'tercerosEntidadesListado';
    private const RUTA_WEB         = 'serviciosTerceros/entidades/listado';
    private const RUTA_WEB_LISTADO = 'serviciosTerceros/entidades/listado/listAll';
    private const RUTA_USU         = 'serviciosTerceros/entidades/listado/usuarios';
    // Niveles de permiso del catalogo (1=ver, 2=editar, 3=crear, 4=borrar)
    private const NIVELES          = [1, 2, 3, 4];
    // Vistas propias de este controlador (sufijo tras VISTA_BASE.'-')
    private const VISTAS_PROPIAS   = ['Resumen-Usuarios-formNew', 'Resumen-Usuarios-formEdit', 'Resumen-Usuarios-UpdateList', 'Resumen-Usuarios-View'];

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
    // Datos sinteticos usados por las vistas del controlador
    /*******************************************************************/
    private function datosSinteticos(int $nivel, int $maquinas = 1, int $notificaciones = 2): array{
        return [
            'UserData'        => [
                'MainPathUrl'          => '/admin/public/upload/',
                'sistemaModalSubtitle' => 1,
                'sistemaModalCloseBTN' => 2,
                'UserType'             => 2,
                'entidadesListadoUsoUsuarios'   => 2,
                'maquinasListadoNotificaciones' => $notificaciones,
            ],
            'UserAccess'      => ['LevelAccess' => $nivel, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_FormInputs'    => new UIFormInputs(),
            'Fnc_Codification'  => new FunctionsSecurityCodification(),
            'Fnc_DataDate'      => new FunctionsDataDate(),
            'Fnc_DataNumbers'   => new FunctionsDataNumbers(),
            'Fnc_WidgetsCommon' => new UIWidgetsCommon(),
            'Fnc_WidgetsViews'  => new UIWidgetsViews(),
            'arrUsuarios'     => [
                [
                    'idEntidad'     => 91,
                    'idUsuario'     => 1,
                    'email'         => 'ana.demo@example.com',
                    'Nombre'        => 'Ana Demo',
                    'Ultimo_acceso' => '2026-01-02',
                    'Estado'        => 'Activo',
                    'EstadoColor'   => 'success',
                ],
                [
                    'idEntidad'     => 91,
                    'idUsuario'     => 2,
                    'email'         => 'beto.demo@example.com',
                    'Nombre'        => 'Beto Demo',
                    'Ultimo_acceso' => '2026-02-03',
                    'Estado'        => 'Inactivo',
                    'EstadoColor'   => 'danger',
                ],
            ],
            // Contadores y datos que arma el controlador a partir del menu de la sesion
            'MainViewData'    => [
                'Count_Maquinas' => $maquinas,
                'Data_Maquinas'  => [['Maquina' => 'Maquina Demo A'], ['Maquina' => 'Maquina Demo B']],
                'Data_Noti'      => [['Notificacion' => 'Aviso Demo']],
            ],
            'arrTipoUsuario'  => [['ID' => 2, 'Nombre' => 'Administrador']],
            'arrEstado'       => [['ID' => 1, 'Nombre' => 'Activo', 'Color' => 'success']],
            'rowData'         => [
                'idUsuario'     => 1,
                'idEntidad'     => 91,
                'idTipoUsuario' => 2,
                'idEstado'      => 1,
                'email'         => 'ana.demo@example.com',
                'Nombre'        => 'Ana Demo',
                'Rut'           => '11111111-1',
                'Fono'          => '56911111111',
                'Direccion_img' => '',
                'Ultimo_acceso' => '2026-01-02',
                'IP_Client'     => '10.0.0.5',
                'Agent_Transp'  => 'Navegador Demo',
                'TipoUsuario'   => 'Administrador',
                'Estado'        => 'Activo',
                'EstadoColor'   => 'success',
            ],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica los archivos y la proteccion de acceso del controlador `tercerosEntidadesListadoUsuarios`
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
            'testing'     => $this->rutaModulo('tercerosEntidadesListadoUsuariosTesting.php'),
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
        $this->addCase($results, 'clase_testing_cargada', class_exists('tercerosEntidadesListadoUsuariosTesting'),
            'la clase tercerosEntidadesListadoUsuariosTesting no esta disponible');
        $padreController = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padreController === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padreController.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('tercerosEntidadesListadoUsuariosTesting') === 'ControllerBase',
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
     * Verifica el contrato de los metodos que devuelven HTML (`New`, `UpdateList`, `View`, `GetID`):
     * existencia, visibilidad, firma, uso de `showVista()`/`returnRutaVista()`/`getUserData()` y las
     * consultas que alimentan cada vista, evaluadas sobre el cuerpo de cada metodo. Incluye el
     * armado de los contadores de permisos de maquinas a partir del menu de la sesion.
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];

        /*====================================*/
        // Firma de cada metodo de vista (todos reciben $f3 y $params)
        /*====================================*/
        foreach (['New', 'UpdateList', 'View', 'GetID'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 2, 2);
        }

        /*====================================*/
        // Contrato de presentacion de cada metodo
        /*====================================*/
        $this->contratoPresentacion($results, 'New', "\$this->controllerName.'-Resumen-Usuarios-formNew.php'", '2');
        $this->contratoPresentacion($results, 'UpdateList', "\$this->controllerName.'-Resumen-Usuarios-UpdateList.php'", '2');
        $this->contratoPresentacion($results, 'View', "\$this->controllerName.'-Resumen-Usuarios-View.php'", '2');
        $this->contratoPresentacion($results, 'GetID', "\$this->controllerName.'-Resumen-Usuarios-formEdit.php'", '2');

        /*====================================*/
        // Identificadores cifrados: se descifran y validan antes de consultar
        /*====================================*/
        $variables = ['New' => 'EntidadID', 'UpdateList' => 'EntidadID', 'View' => 'UsuarioID', 'GetID' => 'UsuarioID'];
        foreach ($variables as $metodo => $variable) {
            $cuerpo = $this->cuerpoMetodo($metodo);
            $this->addCase($results, 'id_descifrado_'.$metodo,
                str_contains($cuerpo, "\$".$variable." = \$this->Codification->encryptDecrypt('decrypt', \$params['id'])"),
                $metodo.'() no descifra $params[\'id\']');
            $this->addCase($results, 'id_valido_'.$metodo, str_contains($cuerpo, "isValidDecrypted(\$".$variable.", 'id')"),
                $metodo.'() no valida el ID con isValidDecrypted()');
            $this->addCase($results, 'id_invalido_400_'.$metodo, str_contains($cuerpo, "Response::error('Registro inválido', 400)"),
                $metodo.'() no responde 400 ante un ID invalido');
            $this->addCase($results, 'id_parametro_preparado_'.$metodo,
                str_contains($cuerpo, "'params'  => [\$".$variable."['data']]"),
                $metodo.'() no consulta con el ID como parametro preparado');
        }

        /*====================================*/
        // New: verifica la entidad y ofrece tipos de usuario sin el superadministrador
        /*====================================*/
        $new = $this->cuerpoMetodo('New');
        $this->addCase($results, 'new_consulta_entidad',
            str_contains($new, "'table'   => 'entidades_listado'") && str_contains($new, "'where'   => 'idEntidad = ?'"),
            'New() no verifica que la entidad exista');
        $this->addCase($results, 'new_catalogo_tipos',
            str_contains($new, "'table'   => 'core_tipos_usuario'") && str_contains($new, "'data'    => 'idTipoUsuario AS ID,Nombre'"),
            'New() no consulta el catalogo de tipos de usuario');
        $this->addCase($results, 'new_excluye_superadmin',
            str_contains($new, "'where'   => 'idTipoUsuario != ?'") && str_contains($new, "'params'  => [1]"),
            'New() no excluye el tipo de usuario 1 (superadministrador) con parametros preparados');
        $this->addCase($results, 'new_entrega_datos', str_contains($new, "'rowData'") && str_contains($new, "'arrTipoUsuario'"),
            'New() no entrega rowData y arrTipoUsuario a la vista');
        $this->addCase($results, 'new_fallback_errores', str_contains($new, 'mergeResponses([$rowData,$arrTipoUsuario])'),
            'New() no agrupa las respuestas fallidas con mergeResponses()');

        /*====================================*/
        // UpdateList: usuarios de la entidad y contador de permisos de maquinas
        /*====================================*/
        $lista = $this->cuerpoMetodo('UpdateList');
        $this->addCase($results, 'lista_filtra_entidad', str_contains($lista, "'where'   => 'terceros_entidades_listado_usuarios.idEntidad = ?'"),
            'UpdateList() no filtra por la entidad');
        $this->addCase($results, 'lista_columnas',
            str_contains($lista, 'terceros_entidades_listado_usuarios.idUsuario') && str_contains($lista, 'terceros_entidades_listado_usuarios.idEntidad')
            && str_contains($lista, 'terceros_entidades_listado_usuarios.Ultimo_acceso'),
            'UpdateList() no consulta idUsuario, idEntidad y Ultimo_acceso');
        $this->addCase($results, 'lista_no_expone_password', !str_contains($lista, 'password'),
            'UpdateList() consulta la clave de los usuarios');
        $this->addCase($results, 'lista_orden', str_contains($lista, 'terceros_entidades_listado_usuarios.email ASC'),
            'UpdateList() no ordena por email');
        $this->addCase($results, 'lista_limite', str_contains($lista, 'N_MaxItems'),
            'UpdateList() no acota la cantidad de filas con N_MaxItems');
        $this->addCase($results, 'lista_menu_sesion', str_contains($lista, "\$f3->get('SESSION.arrMenu')"),
            'UpdateList() no lee el menu de la sesion para los permisos de maquinas');
        $this->addCase($results, 'lista_contador_maquinas',
            str_contains($lista, "'Clientes - Opciones Extras' => 'Count_Maquinas'") && str_contains($lista, "'Externalizacion Servicios'"),
            'UpdateList() no cuenta el permiso Clientes - Opciones Extras de Externalizacion Servicios');
        $this->addCase($results, 'lista_entrega_datos',
            str_contains($lista, "'arrUsuarios'") && str_contains($lista, "'MainViewData'") && str_contains($lista, "'Fnc_DataDate'"),
            'UpdateList() no entrega arrUsuarios, MainViewData y Fnc_DataDate a la vista');

        /*====================================*/
        // View: datos del usuario y, con permiso, maquinas y notificaciones
        /*====================================*/
        $view = $this->cuerpoMetodo('View');
        $this->addCase($results, 'view_filtra_idusuario', str_contains($view, "'where'   => 'terceros_entidades_listado_usuarios.idUsuario = ?'"),
            'View() no consulta por idUsuario');
        $this->addCase($results, 'view_columnas',
            str_contains($view, 'terceros_entidades_listado_usuarios.Rut') && str_contains($view, 'terceros_entidades_listado_usuarios.IP_Client')
            && str_contains($view, 'core_tipos_usuario.Nombre AS TipoUsuario') && str_contains($view, 'core_estados.Color AS EstadoColor'),
            'View() no consulta rut, IP, tipo de usuario y estado');
        $this->addCase($results, 'view_no_expone_password', !str_contains($view, 'terceros_entidades_listado_usuarios.password'),
            'View() consulta la clave del usuario');
        $this->addCase($results, 'view_maquinas_con_permiso', str_contains($view, "if(\$MainViewData['Count_Maquinas']!=0){"),
            'View() consulta las maquinas sin comprobar el permiso Clientes - Opciones Extras');
        $this->addCase($results, 'view_maquinas_join',
            str_contains($view, "'table'   => 'terceros_entidades_listado_usuarios_maq'")
            && str_contains($view, 'terceros_entidades_listado_maquinas.idMaq       = terceros_entidades_listado_usuarios_maq.idMaquina'),
            'View() no une los permisos de maquinas con las maquinas de la entidad');
        $this->addCase($results, 'view_notificaciones_con_permiso',
            str_contains($view, "if(\$arrUserData[\"maquinasListadoNotificaciones\"]==2){")
            && str_contains($view, "'table'   => 'terceros_entidades_listado_usuarios_noti'"),
            'View() consulta las notificaciones sin comprobar maquinasListadoNotificaciones');
        $this->addCase($results, 'view_entrega_datos', str_contains($view, "'rowData'") && str_contains($view, "'MainViewData'"),
            'View() no entrega rowData y MainViewData a la vista');

        /*====================================*/
        // GetID
        /*====================================*/
        $getId = $this->cuerpoMetodo('GetID');
        $this->addCase($results, 'getid_columnas', str_contains($getId, "'data'    => 'idUsuario,idEntidad,idTipoUsuario,idEstado,email,Nombre,Rut,Fono'"),
            'GetID() no consulta las columnas editables');
        $this->addCase($results, 'getid_no_expone_password', !str_contains($getId, 'password'),
            'GetID() consulta la clave del usuario');
        $this->addCase($results, 'getid_excluye_superadmin',
            str_contains($getId, "'where'   => 'idTipoUsuario != ?'") && str_contains($getId, "'params'  => [1]"),
            'GetID() no excluye el tipo de usuario 1 con parametros preparados');
        $this->addCase($results, 'getid_catalogo_estados', str_contains($getId, "'table'   => 'core_estados'"),
            'GetID() no consulta el catalogo de estados');
        $this->addCase($results, 'getid_entrega_datos',
            str_contains($getId, "'arrTipoUsuario'") && str_contains($getId, "'arrEstado'") && str_contains($getId, "'rowData'"),
            'GetID() no entrega rowData, arrTipoUsuario y arrEstado a la vista');
        $this->addCase($results, 'getid_fallback_errores', str_contains($getId, 'mergeResponses([$rowData,$arrTipoUsuario,$arrEstado])'),
            'GetID() no agrupa las respuestas fallidas con mergeResponses()');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                         CONTROLADOR: ESCRITURA (CRUD)                      */
    /******************************************************************************/
    /**
     * Verifica el contrato de `Insert`, `Update` y `Delete` SIN ejecutarlos: terminan en
     * `Response::*()` (que hace `exit`) y escriben en base de datos. Se comprueba firma, verbo HTTP,
     * clave inicial, definicion de la escritura y el orden de la transaccion.
     *
     * @return array Resultado por caso
     */
    public function controladorEscrituraTest(){

        $results = [];

        foreach (['Insert', 'Update', 'Delete'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 0, 0);
        }
        $this->addCase($results, 'sin_delFiles', !method_exists(self::CONTROLADOR, 'delFiles'),
            'el controlador expone delFiles() y no maneja archivos');

        /*====================================*/
        // Orden de la transaccion en cada metodo de escritura
        /*====================================*/
        $operaciones = ['Insert' => '$this->Base_insert(', 'Update' => '$this->Base_update(', 'Delete' => '$this->Base_delete('];
        foreach ($operaciones as $metodo => $llamada) {
            $cuerpo    = $this->cuerpoMetodo($metodo);
            $posBegin  = (int)strpos($cuerpo, 'Base_transactionBegin()');
            $posOp     = (int)strpos($cuerpo, $llamada);
            $posFail   = (int)strpos($cuerpo, 'failOperation(');
            $posCommit = (int)strpos($cuerpo, 'Base_transactionCommit()');
            $posResp   = (int)strpos($cuerpo, 'Response::success(');
            $this->addCase($results, strtolower($metodo).'_transaccion_completa',
                $posBegin > 0 && $posOp > 0 && $posFail > 0 && $posCommit > 0 && $posResp > 0,
                $metodo.'() debe usar Begin, '.$llamada.', failOperation, Commit y Response::success');
            $this->addCase($results, strtolower($metodo).'_transaccion_orden',
                $posBegin < $posOp && $posOp < $posFail && $posFail < $posCommit && $posCommit < $posResp,
                $metodo.'() ejecuta la transaccion en un orden incorrecto');
            $this->addCase($results, strtolower($metodo).'_revierte_si_falla', str_contains($cuerpo, "['status'] === false"),
                $metodo.'() no revisa el estado de la operacion antes de confirmar');
            $this->addCase($results, strtolower($metodo).'_tabla',
                str_contains($cuerpo, "'table'") && str_contains($cuerpo, "'terceros_entidades_listado_usuarios'"),
                $metodo.'() no apunta a la tabla terceros_entidades_listado_usuarios');
        }

        /*====================================*/
        // Insert: clave inicial forzada a cambiar, chequeo, campos, obligatorios y unicidad
        /*====================================*/
        $insert = $this->cuerpoMetodo('Insert');
        $this->addCase($results, 'insert_password_inicial', str_contains($insert, "\$_POST['password']             = '1234'"),
            'Insert() no fija la clave inicial documentada en el formulario (1234)');
        $this->addCase($results, 'insert_fuerza_cambio_clave',
            str_contains($insert, "\$_POST['PasswordForzarCambio'] = 1") && str_contains($insert, "\$_POST['PasswordUltimoCambio'] = date("),
            'Insert() no obliga a cambiar la clave inicial en el primer ingreso');
        $this->addCase($results, 'insert_clave_antes_de_validar',
            (int)strpos($insert, "\$_POST['password']") > 0 && (int)strpos($insert, "\$_POST['password']") < (int)strpos($insert, 'dataCheck('),
            'Insert() debe fijar la clave antes de construir el DataCheck');
        $this->addCase($results, 'insert_datacheck', str_contains($insert, '$this->dataCheck($_POST)'),
            'Insert() no construye el DataCheck con dataCheck($_POST)');
        $this->addCase($results, 'insert_columnas',
            str_contains($insert, "'data'      => 'idEntidad,password,idTipoUsuario,idEstado,email,Nombre,Rut,Fono,Direccion_img,Ultimo_acceso,IP_Client,Agent_Transp,PasswordForzarCambio,PasswordUltimoCambio'"),
            'Insert() no declara las columnas esperadas');
        $this->addCase($results, 'insert_requeridos', str_contains($insert, "'required'  => 'idEntidad,password,idTipoUsuario,idEstado,email,Nombre'"),
            'Insert() no exige idEntidad, password, idTipoUsuario, idEstado, email y Nombre');
        $this->addCase($results, 'insert_unicos', str_contains($insert, "'unique'    => 'email,Rut'"),
            'Insert() no impide duplicar email ni Rut');
        $this->addCase($results, 'insert_password_codificada', str_contains($insert, "'encode'    => 'password'"),
            'Insert() no codifica la clave antes de guardarla');
        $this->addCase($results, 'insert_post_en_query', str_contains($insert, "'Post'      => \$_POST"),
            'Insert() no pasa el POST al constructor de la query');

        /*====================================*/
        // Update: verbo, requeridos, where y cambio de clave
        /*====================================*/
        $update = $this->cuerpoMetodo('Update');
        $this->addCase($results, 'update_verbo_post', str_contains($update, "REQUEST_METHOD'] !== 'POST'"),
            'Update() no valida el verbo HTTP POST');
        $this->addCase($results, 'update_verbo_405', str_contains($update, "Response::error('Error en el Request Method', 405)"),
            'Update() no responde 405 ante un verbo invalido');
        $this->addCase($results, 'update_datacheck', str_contains($update, '$this->dataCheck($_POST)'),
            'Update() no construye el DataCheck con dataCheck($_POST)');
        $this->addCase($results, 'update_donde', str_contains($update, "'where'     => 'idUsuario'"),
            'Update() no delimita la escritura por idUsuario');
        $this->addCase($results, 'update_requeridos', str_contains($update, "'required'  => 'idUsuario,idEntidad,idTipoUsuario,idEstado,email,Nombre'"),
            'Update() no exige idUsuario, idEntidad, idTipoUsuario, idEstado, email y Nombre');
        $this->addCase($results, 'update_unicos', str_contains($update, "'unique'    => 'email,Rut'"),
            'Update() no impide duplicar email ni Rut');
        $this->addCase($results, 'update_password_codificada', str_contains($update, "'encode'    => 'password'"),
            'Update() no codifica la clave antes de guardarla');
        $this->addCase($results, 'update_clave_solo_si_viene',
            str_contains($update, "isset(\$_POST['password'])&&\$_POST['password']!=''")
            && str_contains($update, "\$_POST['PasswordForzarCambio'] = 1"),
            'Update() no fuerza el cambio de clave solo cuando se envia una nueva');

        /*====================================*/
        // Delete: verbo DELETE, cuerpo de la peticion y where
        /*====================================*/
        $delete = $this->cuerpoMetodo('Delete');
        $this->addCase($results, 'delete_verbo', str_contains($delete, "REQUEST_METHOD'] !== 'DELETE'"),
            'Delete() no valida el verbo HTTP DELETE');
        $this->addCase($results, 'delete_verbo_405', str_contains($delete, "Response::error('Error en el Request Method', 405)"),
            'Delete() no responde 405 ante un verbo invalido');
        $this->addCase($results, 'delete_lee_cuerpo', str_contains($delete, 'parse_str(file_get_contents("php://input"),$dataDelete)'),
            'Delete() no lee el cuerpo de la peticion DELETE');
        $this->addCase($results, 'delete_donde', str_contains($delete, "'where'       => 'idUsuario'"),
            'Delete() no delimita el borrado por idUsuario');
        $this->addCase($results, 'delete_sin_archivos', str_contains($delete, "'files'       => ''"),
            'Delete() declara archivos y no borra la imagen del usuario');
        $this->addCase($results, 'delete_post_en_query', str_contains($delete, "'Post'        => \$dataDelete"),
            'Delete() no pasa el cuerpo leido al constructor de la query');

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
     * JavaScript de la pestana en `tercerosEntidadesListado-Resumen.php`. NO se ejecuta
     * `InstallModule()` y la lectura se hace sin conexion a base de datos.
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
        // Rutas esperadas: 'verbo ruta' => [handler, nivel]  (1=GET, 2=POST, 3=DELETE)
        /*====================================*/
        $esperadas = [
            '1 '.self::RUTA_USU.'/new/@id'        => [self::CONTROLADOR.'->New', 2],
            '1 '.self::RUTA_USU.'/updateList/@id' => [self::CONTROLADOR.'->UpdateList', 2],
            '1 '.self::RUTA_USU.'/view/@id'       => [self::CONTROLADOR.'->View', 2],
            '1 '.self::RUTA_USU.'/getID/@id'      => [self::CONTROLADOR.'->GetID', 2],
            '2 '.self::RUTA_USU                   => [self::CONTROLADOR.'->Insert', 2],
            '2 '.self::RUTA_USU.'/update'         => [self::CONTROLADOR.'->Update', 2],
            '3 '.self::RUTA_USU                   => [self::CONTROLADOR.'->Delete', 2],
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

        /*====================================*/
        // Reglas generales de las rutas
        /*====================================*/
        $nivelesOk      = true;
        $existenMetodos = true;
        $bajoRaiz       = true;
        foreach ($rutas as $clave => [$handler, $nivel]) {
            if (!in_array($nivel, self::NIVELES, true)) {
                $nivelesOk = false;
            }
            if (!str_contains($clave, ' '.self::RUTA_USU)) {
                $bajoRaiz = false;
            }
            [$clase, $metodo] = explode('->', $handler) + [1 => ''];
            if ($clase !== self::CONTROLADOR || !method_exists($clase, $metodo)) {
                $existenMetodos = false;
            }
        }
        $this->addCase($results, 'rutas_niveles_catalogo', $nivelesOk, 'una ruta usa un nivel fuera del catalogo 1..4');
        $this->addCase($results, 'rutas_handlers_existen', $existenMetodos, 'una ruta apunta a un metodo inexistente del controlador');
        $this->addCase($results, 'rutas_bajo_usuarios', $bajoRaiz, 'una ruta no cuelga de '.self::RUTA_USU);

        /*====================================*/
        // Instalador: el controlador forma parte del permiso raiz
        /*====================================*/
        $instalador = $this->codigoFuente($this->rutaModulo('../installer/'.self::INSTALADOR.'.php'));
        $this->addCase($results, 'instalador_lista_controlador', str_contains($instalador, '"'.self::CONTROLADOR.'"'),
            'RutaController() del instalador no incluye '.self::CONTROLADOR);

        /*====================================*/
        // Tabla de la entidad en el instalador
        /*====================================*/
        $this->addCase($results, 'instalador_tabla', str_contains($instalador, "'table'      => 'terceros_entidades_listado_usuarios'"),
            'el instalador no crea la tabla terceros_entidades_listado_usuarios');
        foreach (['idUsuario', 'idEntidad', 'password', 'idTipoUsuario', 'idEstado', 'email', 'Nombre', 'Rut', 'Fono', 'Direccion_img', 'Ultimo_acceso', 'IP_Client', 'Agent_Transp', 'PasswordForzarCambio', 'PasswordUltimoCambio'] as $columna) {
            $this->addCase($results, 'instalador_columna_'.$columna, str_contains($instalador, '`'.$columna.'`'),
                'la tabla de usuarios no declara la columna '.$columna);
        }

        /*====================================*/
        // Contrato con el JavaScript de la pestana (vive en Resumen)
        /*====================================*/
        $resumen = $this->codigoFuente($this->rutaVista('Resumen'));
        foreach (['tabUsuariosLoadList', 'tabUsuariosNew', 'tabUsuariosView', 'tabUsuariosEdit', 'tabUsuariosDel', 'tabUsuariosEditMaq', 'tabUsuariosEditNoti'] as $funcion) {
            $this->addCase($results, 'js_funcion_'.$funcion, str_contains($resumen, 'function '.$funcion.'('),
                'Resumen no define la funcion '.$funcion);
        }
        foreach (['/usuarios/updateList/', '/usuarios/new/', '/usuarios/view/', '/usuarios/getID/', '/usuariosMaq/updateList/', '/usuariosNoti/updateList/'] as $fragmento) {
            $this->addCase($results, 'js_endpoint_'.$fragmento, str_contains($resumen, $fragmento),
                'Resumen no consume el endpoint '.$fragmento);
        }
        $this->addCase($results, 'js_borrado_delete', str_contains($resumen, "let Metodo      = 'DELETE'") && str_contains($resumen, '{"idUsuario": ID}'),
            'el borrado de usuarios de Resumen no envia DELETE con idUsuario');

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
            $arrUserData = [];
            $arrLevel    = [];
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
        // Bandera de modulo que habilita la pestana (vale 1 o 2 cuando existe)
        /*====================================*/
        foreach (['entidadesListadoUsoUsuarios', 'maquinasListadoNotificaciones'] as $bandera) {
            $valor = is_array($arrUserData) ? ($arrUserData[$bandera] ?? null) : null;
            $this->addCase($results, 'bandera_'.$bandera, $valor === null || in_array((int)$valor, [1, 2], true),
                'la bandera '.$bandera.' tiene un valor fuera de 1/2');
        }

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
        // Rutas de usuarios en el router de F3 con el verbo y handler esperados
        // (solo se evaluan las registradas: el router se arma con SESSION.arrPermisos)
        /*====================================*/
        $arrRoutes = $f3->get('ROUTES');
        $this->addCase($results, 'router_disponible', is_array($arrRoutes),
            'F3 no expuso la variable ROUTES');

        $esquema = [
            'ruta_new_router'    => ['/'.self::RUTA_USU.'/new/@id',        'GET',    'New'],
            'ruta_lista_router'  => ['/'.self::RUTA_USU.'/updateList/@id', 'GET',    'UpdateList'],
            'ruta_view_router'   => ['/'.self::RUTA_USU.'/view/@id',       'GET',    'View'],
            'ruta_getid_router'  => ['/'.self::RUTA_USU.'/getID/@id',      'GET',    'GetID'],
            'ruta_insert_router' => ['/'.self::RUTA_USU,                   'POST',   'Insert'],
            'ruta_update_router' => ['/'.self::RUTA_USU.'/update',         'POST',   'Update'],
            'ruta_delete_router' => ['/'.self::RUTA_USU,                   'DELETE', 'Delete'],
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
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /**
     * Renderiza `...-Resumen-Usuarios-UpdateList.php` con datos sinteticos y verifica las acciones
     * de la tabla: ver, editar y borrar, y las acciones de permisos de maquinas y notificaciones,
     * que dependen del contador `Count_Maquinas` y de `maquinasListadoNotificaciones`. Los ID viajan
     * cifrados y la tabla vacia se dibuja sin filas.
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
        $rutaTabla = $this->rutaVista('Resumen-Usuarios-UpdateList');

        /*====================================*/
        // Render por nivel de acceso: la tabla se dibuja y cifra los ID en todos los niveles
        /*====================================*/
        foreach (self::NIVELES as $nivel) {
            $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos($nivel), $BASE, $f3);

            $this->addCase($results, 'tabla_render_nivel'.$nivel, $tabla !== '',
                'la vista UpdateList no produjo HTML con nivel '.$nivel);
            $this->addCase($results, 'tabla_ids_cifrados_nivel'.$nivel,
                !str_contains($tabla, "tabUsuariosView('1')") && !str_contains($tabla, "tabUsuariosView('2')")
                && !str_contains($tabla, "tabUsuariosEdit('1')") && !str_contains($tabla, "tabUsuariosDel( '1'"),
                'la tabla expone el ID real del usuario con nivel '.$nivel);
        }

        /*====================================*/
        // Acciones basicas: ver, editar y borrar
        /*====================================*/
        $tabla = $this->renderizarVista($rutaTabla, $this->datosSinteticos(2, 0, 1), $BASE, $f3);
        $this->addCase($results, 'tabla_accion_ver', substr_count($tabla, 'tabUsuariosView(') === 2,
            'la tabla debe ofrecer Ver para cada usuario');
        $this->addCase($results, 'tabla_accion_editar', substr_count($tabla, 'tabUsuariosEdit(') === 2,
            'la tabla debe ofrecer Editar para cada usuario');
        $this->addCase($results, 'tabla_accion_borrar', substr_count($tabla, 'tabUsuariosDel(') === 2,
            'la tabla debe ofrecer Borrar para cada usuario');

        /*====================================*/
        // Permisos de maquinas y notificaciones segun contador y opcion del sistema
        /*====================================*/
        $casos = [
            'sin_maquinas'           => [0, 2, false, false],
            'maquinas_sin_noti'      => [1, 1, true,  false],
            'maquinas_con_noti'      => [1, 2, true,  true],
            'sin_maquinas_con_noti'  => [0, 2, false, false],
        ];
        foreach ($casos as $nombre => [$maquinas, $noti, $esperaMaq, $esperaNoti]) {
            $html = $this->renderizarVista($rutaTabla, $this->datosSinteticos(2, $maquinas, $noti), $BASE, $f3);
            $this->addCase($results, 'tabla_permisos_maquinas_'.$nombre, str_contains($html, 'tabUsuariosEditMaq(') === $esperaMaq,
                'el boton de permisos de maquinas no coincide con el caso '.$nombre);
            $this->addCase($results, 'tabla_permisos_noti_'.$nombre, str_contains($html, 'tabUsuariosEditNoti(') === $esperaNoti,
                'el boton de notificaciones no coincide con el caso '.$nombre);
        }
        $html = $this->renderizarVista($rutaTabla, $this->datosSinteticos(2, 1, 2), $BASE, $f3);
        $this->addCase($results, 'tabla_permisos_ids_cifrados',
            !str_contains($html, "tabUsuariosEditMaq('91'") && !str_contains($html, "tabUsuariosEditNoti('91'"),
            'los botones de permisos exponen el ID real de la entidad');

        /*====================================*/
        // Contenido de la tabla
        /*====================================*/
        $this->addCase($results, 'tabla_cabeceras',
            str_contains($tabla, 'Email') && str_contains($tabla, 'Nombre') && str_contains($tabla, 'Ultimo Acceso') && str_contains($tabla, 'Estado'),
            'la tabla no declara las cabeceras Email, Nombre, Ultimo Acceso y Estado');
        $this->addCase($results, 'tabla_datos',
            str_contains($tabla, 'ana.demo@example.com') && str_contains($tabla, 'Beto Demo'),
            'la tabla no pinta los usuarios sinteticos');
        $this->addCase($results, 'tabla_estado_color',
            str_contains($tabla, 'badge-sp1-success') && str_contains($tabla, 'badge-sp1-danger'),
            'la tabla no pinta el estado con su color');
        $this->addCase($results, 'tabla_sin_password', !str_contains($tabla, 'password'),
            'la tabla menciona la clave de los usuarios');
        $this->addCase($results, 'tabla_filas', substr_count($tabla, '<tr>') === 3,
            'la tabla debe tener la cabecera y una fila por usuario');

        /*====================================*/
        // Rama sin datos: la tabla se dibuja vacia sin romperse
        /*====================================*/
        $vacio = $this->datosSinteticos(4);
        $vacio['arrUsuarios'] = [];
        $tablaVacia = $this->renderizarVista($rutaTabla, $vacio, $BASE, $f3);
        $this->addCase($results, 'tabla_vacia_render', str_contains($tablaVacia, '<tbody>'),
            'la tabla sin datos no se dibuja');
        $this->addCase($results, 'tabla_vacia_sin_filas', !str_contains($tablaVacia, '<td>'),
            'la tabla sin datos dibuja filas');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                        VISTAS DE DETALLE Y FORMULARIOS                     */
    /******************************************************************************/
    /**
     * Renderiza el detalle (`View`) y los formularios de alta (`formNew`) y edicion (`formEdit`) con
     * datos sinteticos y comprueba ids, campos, campos ocultos, endpoints, verbos y refresco de la
     * tabla de la pestana. En `View` se verifican las pestanas de maquinas y notificaciones segun
     * permisos.
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
        $rutaView = $this->rutaVista('Resumen-Usuarios-View');

        /*====================================*/
        // Modal de detalle (View) con permisos de maquinas y notificaciones
        /*====================================*/
        $html = $this->renderizarVista($rutaView, $this->datosSinteticos(2, 1, 2), $BASE, $f3);
        $this->addCase($results, 'view_render', $html !== '', 'la vista View no produjo HTML');
        $this->addCase($results, 'view_pestanas_completas',
            str_contains($html, 'id="view_tab_1"') && str_contains($html, 'id="view_tab_2"') && str_contains($html, 'id="view_tab_3"'),
            'la vista View no monta las pestanas de datos, maquinas y notificaciones con permiso');
        $this->addCase($results, 'view_datos_usuario',
            str_contains($html, 'ana.demo@example.com') && str_contains($html, 'Ana Demo') && str_contains($html, '11111111-1')
            && str_contains($html, 'Administrador'),
            'la vista View no pinta email, nombre, rut y tipo de usuario');
        $this->addCase($results, 'view_datos_acceso', str_contains($html, '10.0.0.5') && str_contains($html, 'Navegador Demo'),
            'la vista View no pinta la IP y el navegador del ultimo acceso');
        $this->addCase($results, 'view_estado_color', str_contains($html, 'badge-sp1-success'),
            'la vista View no pinta el estado con su color');
        $this->addCase($results, 'view_imagen_defecto', str_contains($html, $BASE.'/img/picture-img.jpg'),
            'la vista View no usa la imagen por defecto cuando el usuario no tiene imagen');
        $this->addCase($results, 'view_maquinas_pintadas', str_contains($html, 'Maquina Demo A') && str_contains($html, 'Maquina Demo B'),
            'la vista View no lista las maquinas con permiso');
        $this->addCase($results, 'view_notificaciones_pintadas', str_contains($html, 'Aviso Demo'),
            'la vista View no lista las notificaciones con permiso');
        $this->addCase($results, 'view_sin_password', !str_contains($html, 'password'),
            'la vista View menciona la clave del usuario');
        $this->addCase($results, 'view_pie_cierre', str_contains($html, 'data-bs-dismiss="modal"'),
            'la vista View no ofrece el pie de cierre del modal');

        $conImagen = $this->datosSinteticos(2);
        $conImagen['rowData']['Direccion_img'] = 'usuario_demo.png';
        $htmlImg = $this->renderizarVista($rutaView, $conImagen, $BASE, $f3);
        $this->addCase($results, 'view_imagen_propia', str_contains($htmlImg, '/admin/public/upload/usuario_demo.png'),
            'la vista View no pinta la imagen cargada del usuario');

        /*====================================*/
        // View sin permiso de maquinas: solo datos; con maquinas y sin notificaciones: sin pestana 3
        /*====================================*/
        $html = $this->renderizarVista($rutaView, $this->datosSinteticos(2, 0, 2), $BASE, $f3);
        $this->addCase($results, 'view_sin_maquinas',
            str_contains($html, 'id="view_tab_1"') && !str_contains($html, 'id="view_tab_2"') && !str_contains($html, 'id="view_tab_3"')
            && !str_contains($html, 'Maquina Demo A'),
            'la vista View muestra maquinas o notificaciones sin el permiso de maquinas');
        $html = $this->renderizarVista($rutaView, $this->datosSinteticos(2, 1, 1), $BASE, $f3);
        $this->addCase($results, 'view_sin_notificaciones',
            str_contains($html, 'id="view_tab_2"') && !str_contains($html, 'id="view_tab_3"') && !str_contains($html, 'Aviso Demo'),
            'la vista View muestra notificaciones cuando la opcion del sistema las desactiva');
        $sinCierre = $this->datosSinteticos(2);
        $sinCierre['UserData']['sistemaModalCloseBTN'] = 1;
        $htmlSinCierre = $this->renderizarVista($rutaView, $sinCierre, $BASE, $f3);
        $this->addCase($results, 'view_sin_pie_si_no_aplica', !str_contains($htmlSinCierre, 'modal-footer'),
            'la vista View dibuja el pie de cierre cuando la opcion del sistema lo desactiva');

        /*====================================*/
        // Formulario de alta
        /*====================================*/
        $html = $this->renderizarVista($this->rutaVista('Resumen-Usuarios-formNew'), $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'form_new_render', $html !== '', 'la vista formNew no produjo HTML');
        $this->addCase($results, 'form_new_id', str_contains($html, 'id="FormNewUsuario"'),
            'el formulario de alta no usa el id FormNewUsuario');
        foreach (['email', 'Nombre', 'Rut', 'idTipoUsuario'] as $campo) {
            $this->addCase($results, 'form_new_campo_'.$campo, str_contains($html, 'id="NewUsuario_'.$campo.'"'),
                'el formulario de alta no declara el campo NewUsuario_'.$campo);
        }
        $this->addCase($results, 'form_new_campo_Fono', str_contains($html, 'id="NewUsuario_Fono"') || str_contains($html, 'name="Fono"'),
            'el formulario de alta no declara el campo Fono');
        $this->addCase($results, 'form_new_aviso_clave', str_contains($html, '1234'),
            'el formulario de alta no avisa la clave inicial por defecto');
        $this->addCase($results, 'form_new_sin_campo_password', !str_contains($html, 'name="password"'),
            'el formulario de alta pide la clave y el controlador la fija');
        $this->addCase($results, 'form_new_opciones', str_contains($html, 'Administrador'),
            'el formulario de alta no ofrece los tipos de usuario');
        $this->addCase($results, 'form_new_oculto_entidad', (bool)preg_match('/name="idEntidad"[^>]*value="91"|value="91"[^>]*name="idEntidad"/', $html),
            'el formulario de alta no envia idEntidad (91) como campo oculto');
        $this->addCase($results, 'form_new_oculto_estado', (bool)preg_match('/name="idEstado"[^>]*value="1"|value="1"[^>]*name="idEstado"/', $html),
            'el formulario de alta no envia idEstado activo (1) como campo oculto');
        $this->addCase($results, 'form_new_sin_idusuario', !str_contains($html, 'name="idUsuario"'),
            'el formulario de alta envia idUsuario y el registro aun no existe');
        $this->addCase($results, 'form_new_endpoint',
            str_contains($html, self::RUTA_USU."'") && str_contains($html, "Metodo      = 'POST'"),
            'el formulario de alta no publica en '.self::RUTA_USU.' con POST');
        $this->addCase($results, 'form_new_refresca_tabla',
            str_contains($html, "Div:'#tabUsuariosDataTable'") && str_contains($html, self::RUTA_USU.'/updateList/'),
            'el formulario de alta no refresca la tabla de la pestana');
        $this->addCase($results, 'form_new_id_cifrado', !str_contains($html, '/updateList/91'),
            'el formulario de alta expone el ID real de la entidad');

        /*====================================*/
        // Formulario de edicion
        /*====================================*/
        $html = $this->renderizarVista($this->rutaVista('Resumen-Usuarios-formEdit'), $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'form_edit_render', $html !== '', 'la vista formEdit no produjo HTML');
        $this->addCase($results, 'form_edit_id', str_contains($html, 'id="FormEditUsuarios"'),
            'el formulario de edicion no usa el id FormEditUsuarios');
        foreach (['email', 'Nombre', 'Rut', 'idTipoUsuario', 'idEstado', 'password'] as $campo) {
            $this->addCase($results, 'form_edit_campo_'.$campo, str_contains($html, 'id="EditUsuarios_'.$campo.'"'),
                'el formulario de edicion no declara el campo EditUsuarios_'.$campo);
        }
        $this->addCase($results, 'form_edit_precarga',
            str_contains($html, 'value="ana.demo@example.com"') && str_contains($html, 'value="Ana Demo"') && str_contains($html, 'value="11111111-1"'),
            'el formulario de edicion no precarga los valores actuales del usuario');
        $this->addCase($results, 'form_edit_password_vacia', !preg_match('/id="EditUsuarios_password"[^>]*value="[^"]+"/', $html),
            'el formulario de edicion precarga la clave del usuario');
        $this->addCase($results, 'form_edit_oculto_idusuario', (bool)preg_match('/name="idUsuario"[^>]*value="1"|value="1"[^>]*name="idUsuario"/', $html),
            'el formulario de edicion no envia idUsuario como campo oculto');
        $this->addCase($results, 'form_edit_endpoint',
            str_contains($html, self::RUTA_USU.'/update') && str_contains($html, "Metodo      = 'POST'"),
            'el formulario de edicion no publica en '.self::RUTA_USU.'/update con POST');
        $this->addCase($results, 'form_edit_refresca_tabla',
            str_contains($html, "Div:'#tabUsuariosDataTable'") && str_contains($html, self::RUTA_USU.'/updateList/'),
            'el formulario de edicion no refresca la tabla de la pestana');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos el chequeo de datos del controlador (`dataCheck`), el cifrado
     * de identificadores y la validacion de IDs descifrados.
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
            // Se crea sin constructor: dataCheck() es logica pura y no necesita
            // la conexion a base de datos del controlador.
            $Controller = (new ReflectionClass(self::CONTROLADOR))->newInstanceWithoutConstructor();

            $Post      = ['idEntidad' => 91, 'idTipoUsuario' => 2, 'idEstado' => 1, 'email' => 'ana.demo@example.com', 'Nombre' => 'Ana Demo', 'Rut' => '11111111-1', 'Fono' => '56911111111'];
            $DataCheck = $reflector->invoke($Controller, $Post);

            $this->addCase($results, 'datacheck_es_arreglo', is_array($DataCheck),
                'dataCheck() no devolvio un arreglo');
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post,
                'dataCheck() no conserva el POST recibido');
            $this->addCase($results, 'datacheck_email', (string)($DataCheck['ValidarEmail'] ?? '') === 'email',
                'dataCheck() no valida email como correo');
            $this->addCase($results, 'datacheck_email_reglas',
                (string)($DataCheck['ValidarEspaciosVacios'] ?? '') === 'email' && (string)($DataCheck['ValidarMayusculas'] ?? '') === 'email'
                && (string)($DataCheck['ValidarDominioEmail'] ?? '') === 'email',
                'dataCheck() no rechaza espacios, mayusculas ni dominios invalidos en el correo');
            $this->addCase($results, 'datacheck_numeros',
                (string)($DataCheck['ValidarNumero'] ?? '') === 'idEntidad,idTipoUsuario,idEstado,Fono',
                'dataCheck() no valida identificadores y Fono como numericos');
            $this->addCase($results, 'datacheck_enteros',
                (string)($DataCheck['ValidarEntero'] ?? '') === 'idEntidad,idTipoUsuario,idEstado',
                'dataCheck() no valida los identificadores como enteros');
            $this->addCase($results, 'datacheck_rut', (string)($DataCheck['ValidarRut'] ?? '') === 'Rut',
                'dataCheck() no valida Rut');
            $this->addCase($results, 'datacheck_fecha', (string)($DataCheck['ValidarFecha'] ?? '') === 'Ultimo_acceso',
                'dataCheck() no valida Ultimo_acceso como fecha');
            $this->addCase($results, 'datacheck_ip', (string)($DataCheck['ValidarIP'] ?? '') === 'IP_Client',
                'dataCheck() no valida IP_Client como direccion IP');
            $this->addCase($results, 'datacheck_largo_minimo',
                (string)($DataCheck['ValidarLargoMinimo'] ?? '') === 'email,Nombre' && (int)($DataCheck['ValidarLargoMinimoN'] ?? 0) === 3,
                'dataCheck() no fija el largo minimo 3 para email y Nombre');
            $this->addCase($results, 'datacheck_censura', (string)($DataCheck['ValidarPalabrasCensuradas'] ?? '') === 'Nombre',
                'dataCheck() no filtra palabras censuradas en Nombre');

            /*====================================*/
            // Largos maximos: deben respetar el tamano de las columnas de la tabla
            /*====================================*/
            $maximos   = $DataCheck['ValidarLargoMaximoPorCampo'] ?? [];
            $esperados = ['email' => 60, 'Nombre' => 60, 'Rut' => 13, 'Fono' => 15, 'Direccion_img' => 120, 'IP_Client' => 120, 'Agent_Transp' => 240];
            foreach ($esperados as $campo => $largo) {
                $this->addCase($results, 'datacheck_maximo_'.$campo, (int)($maximos[$campo] ?? 0) === $largo,
                    'dataCheck() no limita '.$campo.' a '.$largo.' caracteres (tamano de la columna)');
            }

            /*====================================*/
            // Todo campo con largo minimo tiene tambien largo maximo
            /*====================================*/
            $conMinimo = array_filter(explode(',', (string)($DataCheck['ValidarLargoMinimo'] ?? '')));
            $sinMaximo = array_values(array_filter($conMinimo, static fn($c) => !isset($maximos[$c])));
            $this->addCase($results, 'datacheck_minimo_con_maximo', $sinMaximo === [],
                'campos con largo minimo y sin maximo: '.implode(',', $sinMaximo));

            /*====================================*/
            // Los campos validados son columnas de la tabla
            /*====================================*/
            $columnas  = explode(',', 'idUsuario,idEntidad,password,idTipoUsuario,idEstado,email,Nombre,Rut,Fono,Direccion_img,Ultimo_acceso,IP_Client,Agent_Transp,PasswordForzarCambio,PasswordUltimoCambio');
            $validados = array_merge(
                explode(',', (string)$DataCheck['ValidarNumero']),
                explode(',', (string)$DataCheck['ValidarEntero']),
                explode(',', (string)$DataCheck['ValidarRut']),
                explode(',', (string)$DataCheck['ValidarFecha']),
                explode(',', (string)$DataCheck['ValidarLargoMinimo']),
                array_keys($maximos)
            );
            $ajenos = array_values(array_diff($validados, $columnas));
            $this->addCase($results, 'datacheck_campos_son_columnas', $ajenos === [],
                'dataCheck() valida campos que no son columnas: '.implode(',', $ajenos));
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_es_arreglo', false,
                'no se pudo ejecutar dataCheck() ('.$e->getMessage().')');
        }

        $this->addCase($results, 'datacheck_privado',
            method_exists(self::CONTROLADOR, 'dataCheck') && (new ReflectionMethod(self::CONTROLADOR, 'dataCheck'))->isPrivate(),
            'dataCheck() debe ser privado');

        /*====================================*/
        // Cifrado de identificadores: ida y vuelta estable
        /*====================================*/
        try {
            $Codification = new FunctionsSecurityCodification();
            $cifrado      = $Codification->encryptDecrypt('encrypt', '42');
            $this->addCase($results, 'cifrado_genera_dato',
                ($cifrado['success'] ?? false) === true && !empty($cifrado['data']),
                'encryptDecrypt(encrypt) no produjo un valor cifrado');

            $descifrado = $Codification->encryptDecrypt('decrypt', (string)($cifrado['data'] ?? ''));
            $this->addCase($results, 'cifrado_ida_y_vuelta',
                ($descifrado['success'] ?? false) === true && (string)($descifrado['data'] ?? '') === '42',
                'el cifrado no devuelve el valor original');

            $basura = $Codification->encryptDecrypt('decrypt', 'valor-no-cifrado');
            $this->addCase($results, 'cifrado_rechaza_basura', ($basura['success'] ?? false) === false,
                'un valor no cifrado no debe descifrarse');
        } catch (Throwable $e) {
            $this->addCase($results, 'cifrado_ida_y_vuelta', false,
                'no se pudo ejecutar el cifrado ('.$e->getMessage().')');
        }

        /*====================================*/
        // isValidDecrypted: los caminos posibles
        /*====================================*/
        $this->addCase($results, 'id_valido_ok', $this->isValidDecrypted(['success' => true, 'data' => '7'], 'id') === true,
            'un ID numerico positivo debe ser valido');
        $this->addCase($results, 'id_valido_descifro_falla', $this->isValidDecrypted(['success' => false, 'data' => '7'], 'id') === false,
            'un descifrado fallido debe rechazarse');
        $this->addCase($results, 'id_valido_texto', $this->isValidDecrypted(['success' => true, 'data' => 'abc'], 'id') === false,
            'un ID no numerico debe rechazarse para tipo id');
        $this->addCase($results, 'id_valido_cero', $this->isValidDecrypted(['success' => true, 'data' => '0'], 'id') === false,
            'el ID 0 debe rechazarse para tipo id');
        $this->addCase($results, 'id_valido_tipo_desconocido', $this->isValidDecrypted(['success' => true, 'data' => 'abc'], 'otro') === false,
            'un tipo de dato desconocido debe rechazarse');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                 SEGURIDAD                                  */
    /******************************************************************************/
    /**
     * Revisa estaticamente las vistas propias del controlador y el propio controlador:
     * ausencia de rutas relativas maliciosas, de rutas absolutas del sistema de ficheros, de
     * acceso directo a la sesion desde las plantillas, de SQL embebido y de concatenacion de
     * superglobales en consultas.
     *
     * @return array Resultado por caso
     */
    public function seguridadVistasTest(){

        $results = [];

        /*====================================*/
        // Vistas propias (el prefijo es compartido con otros controladores)
        /*====================================*/
        $arrVistas = [];
        foreach (self::VISTAS_PROPIAS as $vista) {
            $arrVistas[] = $this->rutaVista($vista);
        }
        $this->addCase($results, 'hay_vistas', count(array_filter($arrVistas, 'is_file')) === count(self::VISTAS_PROPIAS),
            'faltan vistas del controlador '.self::CONTROLADOR);

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
            $this->addCase($results, 'vista_sin_superglobales_'.$nombre, !preg_match('/\$_(POST|GET|REQUEST|COOKIE|SERVER)\b/', $codigo),
                $nombre.' lee superglobales de la peticion');
        }

        /*====================================*/
        // Los identificadores de la tabla viajan cifrados y el rotulo del borrado se escapa
        /*====================================*/
        $tabla = $this->codigoFuente($this->rutaVista('Resumen-Usuarios-UpdateList'));
        $this->addCase($results, 'tabla_id_cifrado', str_contains($tabla, "encryptDecrypt('encrypt'"),
            'UpdateList no cifra el ID de la asignacion antes de pintarlo');
        $this->addCase($results, 'tabla_escape_rotulo_borrado', str_contains($tabla, 'addslashes('),
            'UpdateList no escapa el rotulo que viaja al JavaScript de borrado');

        /*====================================*/
        // El controlador no concatena SQL desde el cliente
        /*====================================*/
        $controlador = $this->fuenteControlador();
        $this->addCase($results, 'controlador_sin_concat_sql',
            !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $controlador),
            'el controlador concatena superglobales en una cadena SQL');
        $this->addCase($results, 'controlador_parametros_preparados',
            str_contains($controlador, "'params'"),
            'el controlador no usa parametros preparados');
        $this->addCase($results, 'controlador_sin_in_concatenado',
            !preg_match('/\bIN\s*\(\s*\'\s*\.|\bIN\s*\(\s*\$/', $controlador),
            'el controlador arma un IN con valores concatenados');
        $this->addCase($results, 'controlador_sin_acceso_session', !str_contains($controlador, '$_SESSION'),
            'el controlador lee $_SESSION directamente en vez de getUserData()');
        $this->addCase($results, 'controlador_sin_eval', !preg_match('/\b(eval|exec|shell_exec|system|passthru)\s*\(/', $controlador),
            'el controlador usa funciones de ejecucion de codigo');

        /************************************/
        // Retorno los datos
        return $results;

    }

}
