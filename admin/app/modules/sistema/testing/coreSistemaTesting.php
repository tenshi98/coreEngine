<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class coreSistemaTesting
 *
 * Pruebas del modulo `sistema` - controlador `coreSistema` (datos de la empresa y orden de widgets).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. El normalizador
 *    `sistemaTesteos::normalizeResult()` marca EXITOSA o ERROR segun el contenido, por lo que
 *    los textos de los casos correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Alcance: pruebas NO destructivas. Los metodos que escriben en base de datos (`Update()`,
 * `delFiles()`, `WidgetsOrdenSave()`) terminan en `Response::success()`/`Response::error()`,
 * que ejecutan `exit`; por eso NO se invocan: se verifican por reflexion y por lectura del
 * fuente. Tampoco se ejecuta `InstallModule()`/`UninstallModule()` ni `ListDataModule()`
 * (consulta la BD de administracion). El controlador no tiene alta ni borrado: la empresa es
 * un registro unico (`idSistema = 1`).
 *
 * @see skills/testing/SKILL.md
 */
class coreSistemaTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR      = 'coreSistema';
    private const INSTALADOR       = 'coreSistemaInstaller';
    private const RUTA_WEB         = 'administracion/sistema';
    private const RUTA_WEB_WIDGETS = 'mantencionSistema/sistema/widgetsOrden';
    // Niveles de permiso del catalogo (1=ver, 2=editar, 3=crear, 4=borrar)
    private const NIVELES          = [1, 2, 3, 4];
    // Rutas publicadas por el instalador: clave "VERBO ruta" => [handler, nivel]
    private const RUTAS            = [
        'GET administracion/sistema/listAll'                  => ['coreSistema->Resumen',        2],
        'GET administracion/sistema/resumenUpdate'            => ['coreSistema->ResumenUpdate',  2],
        'POST administracion/sistema/update'                  => ['coreSistema->Update',         2],
        'PUT administracion/sistema/delFiles'                 => ['coreSistema->DelFiles',       2],
        'GET mantencionSistema/sistema/widgetsOrden/listAll'  => ['coreSistema->WidgetsOrden',     2],
        'POST mantencionSistema/sistema/widgetsOrden/save'    => ['coreSistema->WidgetsOrdenSave', 2],
    ];

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
    // Atajos de lectura del controlador y del instalador
    /*******************************************************************/
    private function fuenteControlador(): string{
        return $this->codigoFuente($this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'));
    }

    private function fuenteInstalador(): string{
        return $this->codigoFuente($this->rutaModulo('../installer/'.self::INSTALADOR.'.php'));
    }

    /*******************************************************************/
    // Fuente de un unico metodo (acota las lecturas estaticas al metodo
    // probado y evita falsos positivos por coincidencias en otros metodos)
    /*******************************************************************/
    private function cuerpoMetodo(string $clase, string $metodo): string{
        try {
            $reflector = new ReflectionMethod($clase, $metodo);
            $lineas    = file((string)$reflector->getFileName());
            if (!is_array($lineas)) {
                return '';
            }
            $inicio = $reflector->getStartLine() - 1;
            $largo  = $reflector->getEndLine() - $reflector->getStartLine() + 1;
            return implode('', array_slice($lineas, $inicio, $largo));
        } catch (Throwable $e) {
            return '';
        }
    }

    /*******************************************************************/
    // Indica si los fragmentos aparecen en el texto en ese mismo orden
    /*******************************************************************/
    private function enOrden(string $texto, array $fragmentos): bool{
        $desde = 0;
        foreach ($fragmentos as $fragmento) {
            $pos = strpos($texto, $fragmento, $desde);
            if ($pos === false) {
                return false;
            }
            $desde = $pos + strlen($fragmento);
        }
        return true;
    }

    /*******************************************************************/
    // Instancia el controlador sin constructor (sin conexion a la BD)
    /*******************************************************************/
    private function instanciaSinBD(){
        return (new ReflectionClass(self::CONTROLADOR))->newInstanceWithoutConstructor();
    }

    /*******************************************************************/
    // Contrato basico de un metodo del controlador (existencia,
    // visibilidad y rango de parametros)
    /*******************************************************************/
    private function firmaMetodo(array &$results, string $metodo, int $minParam, int $maxParam, bool $publico = true){
        $existe = method_exists(self::CONTROLADOR, $metodo);
        $this->addCase($results, 'metodo_'.$metodo, $existe, 'no existe '.self::CONTROLADOR.'::'.$metodo.'()');
        if (!$existe) {
            return;
        }
        $reflector = new ReflectionMethod(self::CONTROLADOR, $metodo);
        $this->addCase($results, 'metodo_'.$metodo.($publico ? '_publico' : '_privado'),
            $publico ? $reflector->isPublic() : $reflector->isPrivate(),
            $metodo.'() no tiene la visibilidad esperada');
        $n = $reflector->getNumberOfParameters();
        $this->addCase($results, 'metodo_'.$metodo.'_parametros', $n >= $minParam && $n <= $maxParam,
            $metodo.'() declara '.$n.' parametros (esperado entre '.$minParam.' y '.$maxParam.')');
    }

    /*******************************************************************/
    // Contrato de presentacion de un metodo de vista (acotado a su cuerpo)
    /*******************************************************************/
    private function contratoPresentacion(array &$results, string $metodo, string $vista, string $tipoVista, string $tipoError){
        $cuerpo = $this->cuerpoMetodo(self::CONTROLADOR, $metodo);
        $this->addCase($results, 'vista_'.$metodo, str_contains($cuerpo, $vista),
            $metodo.'() no instancia la vista '.$vista);
        $this->addCase($results, 'tipoview_'.$metodo, str_contains($cuerpo, 'showVista('.$tipoVista.','),
            $metodo.'() no usa showVista('.$tipoVista.')');
        $this->addCase($results, 'rutavista_'.$metodo, str_contains($cuerpo, "returnRutaVista(__DIR__, 'app')"),
            $metodo.'() no resuelve la ruta de la vista');
        $this->addCase($results, 'nombrecontrolador_'.$metodo, str_contains($cuerpo, '$this->controllerName'),
            $metodo.'() no referencia $this->controllerName');
        $this->addCase($results, 'errores_'.$metodo, str_contains($cuerpo, 'showError('.$tipoError.', $f3, $result)'),
            $metodo.'() no despliega la ruta de error con showError('.$tipoError.')');
        $this->addCase($results, 'fusion_errores_'.$metodo, str_contains($cuerpo, 'mergeResponses('),
            $metodo.'() no agrupa las respuestas fallidas con mergeResponses()');
        $this->addCase($results, 'usuario_'.$metodo, str_contains($cuerpo, 'getUserData($f3)'),
            $metodo.'() no obtiene los datos del usuario');
        $this->addCase($results, 'nivel_'.$metodo, str_contains($cuerpo, 'getArrLevel($f3, $this->controllerName)'),
            $metodo.'() no resuelve el nivel de acceso del usuario');
    }

    /*******************************************************************/
    // Parsea getRouteDefinitions() del instalador a un mapa indexado por
    // "VERBO RutaWeb". El reparto por tipo de permiso (1 y 2) obliga a
    // agregar los tipos 1..N. idMetodo: 1=GET, 2=POST, 3=DELETE, 4=PUT.
    /*******************************************************************/
    private function rutasInstalador(): array{
        $rutas = [];
        try {
            $instalador = (new ReflectionClass(self::INSTALADOR))->newInstanceWithoutConstructor();
            $reflector  = new ReflectionMethod(self::INSTALADOR, 'getRouteDefinitions');
            $defs       = [];
            for ($type = 1; $type <= 10; $type++) {
                $lote = (array)$reflector->invoke($instalador, $type);
                if (empty($lote)) {
                    break;
                }
                $defs = array_merge($defs, $lote);
            }
        } catch (Throwable $e) {
            return [];
        }
        $mapa = [1 => 'GET', 2 => 'POST', 3 => 'DELETE', 4 => 'PUT'];
        foreach ($defs as $def) {
            $verbo         = $mapa[(int)($def['idMetodo'] ?? 0)] ?? '';
            $clave         = $verbo.' '.($def['RutaWeb'] ?? '');
            $rutas[$clave] = [
                'handler'    => (string)($def['RutaController'] ?? ''),
                'nivel'      => (int)($def['idLevelLimit'] ?? 0),
                'controller' => (string)($def['Controller'] ?? ''),
            ];
        }
        return $rutas;
    }

    /*******************************************************************/
    // Renderiza una vista del modulo con datos sinteticos y devuelve el
    // HTML. El CWD se mueve a admin/public porque las plantillas de los
    // inputs se resuelven con rutas relativas ('../app/templates/...').
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
    // Base de las URL de las vistas (F3 puede entregarla vacia por CLI)
    /*******************************************************************/
    private function baseUrl($f3): string{
        $BASE = (string)$f3->get('BASE');
        return trim($BASE) === '' ? '/admin' : $BASE;
    }

    /*******************************************************************/
    // Fila sintetica de core_sistemas (con las claves que consumen las vistas)
    /*******************************************************************/
    private function filaSistema(array $cambios = []): array{
        return array_merge([
            'idSistema'                 => 1,
            'Sistema_Nombre'            => 'Empresa Prueba',
            'Sistema_Email'             => 'contacto@empresa-prueba.cl',
            'Sistema_Rut'               => '76123456-0',
            'Sistema_idCiudad'          => 1,
            'Sistema_idComuna'          => 1,
            'Sistema_Direccion'         => 'Av. Siempreviva 742',
            'Sistema_IMGLogo'           => 'logo_prueba.png',
            'Sistema_idTema'            => 1,
            'Sistema_NotiWhatsapp'      => '56911112222',
            'Contacto_Nombre'           => 'Contacto Prueba',
            'Contacto_Fono1'            => '221112222',
            'Contacto_Fono2'            => '223334444',
            'Contacto_Fax'              => '225556666',
            'Contacto_Email'            => 'info@empresa-prueba.cl',
            'Contacto_Web'              => 'www.empresa-prueba.cl',
            'RepresentanteNombre'       => 'Representante Prueba',
            'RepresentanteRut'          => '11111111-1',
            'RepresentanteFono'         => '56922223333',
            'RepresentanteEmail'        => 'rep@empresa-prueba.cl',
            'Config_API_GoogleMaps'     => 'clave-maps-prueba',
            'Config_WhatsappToken'      => 'token-ws-prueba',
            'Config_WhatsappInstanceId' => 'instancia-prueba',
            'Latitud'                   => '-33.4372',
            'Longitud'                  => '-70.6506',
            'Social_X'                  => 'https://x.com/prueba',
            'Social_Facebook'           => 'https://facebook.com/prueba',
            'Social_Instagram'          => 'https://instagram.com/prueba',
            'Social_Linkedin'           => 'https://linkedin.com/company/prueba',
            'Config_Principal_Meteo'    => 2,
            'Config_Principal_Radio'    => 2,
            'Config_Principal_Feed'     => 2,
            'Config_Principal_FeedURL'  => 'https://feeds.example.org/rss',
            'Config_IA_Provider'        => 1,
            'Config_IA_ApiKey'          => 'ia-key-prueba',
            'Config_IA_Model'           => 'modelo-prueba',
            'Config_IA_Base_URL'        => 'https://ia.example.org/v1',
            'Config_IA_Name'            => 'Asistente',
            'Config_IA_Tone'            => 'Cordial',
            'Config_IA_Uso'             => 2,
            'Config_IA_UsoCache'        => 2,
            'usuariosPermisosBodegas'   => 2,
            'usuariosPermisosMaquinas'  => 1,
            'Ciudad'                    => 'Santiago',
            'Comuna'                    => 'Providencia',
            'Tema'                      => 'Tema Claro',
            'IAProvider'                => 'Proveedor IA Prueba',
        ], $cambios);
    }

    /*******************************************************************/
    // Datos sinteticos usados por las vistas Resumen / Resumen-Update
    /*******************************************************************/
    private function datosSinteticos(int $nivel = 2, array $usuario = [], array $fila = []): array{
        return [
            'PageTitle'         => 'Configuracion Plataforma',
            'PageDescription'   => 'Configuracion Plataforma.',
            'PageAuthor'        => 'Pruebas',
            'PageKeywords'      => 'Pruebas',
            'UserData'          => array_merge([
                'MainPathUrl'          => '/admin/public/upload/',
                'sistemaModalSubtitle' => 1,
                'sistemaModalCloseBTN' => 2,
                'sistemaUsoWhatsapp'   => 2,
                'Config_motorMap'      => 2,
                'Config_IA_Uso'        => 2,
                'UserType'             => 2,
            ], $usuario),
            'UserAccess'        => ['LevelAccess' => $nivel, 'RouteAccess' => self::RUTA_WEB],
            'Fnc_FormInputs'    => new UIFormInputs(),
            'Fnc_WidgetsCommon' => new UIWidgetsCommon(),
            'Fnc_WidgetsViews'  => new UIWidgetsViews(),
            'Fnc_WidgetsMaps'   => new UIWidgetsMaps(),
            'Fnc_DataDate'      => new FunctionsDataDate(),
            'Fnc_DataNumbers'   => new FunctionsDataNumbers(),
            'Fnc_Codification'  => new FunctionsSecurityCodification(),
            'rowData'           => $this->filaSistema($fila),
            'arrCiudad'         => [['ID' => 1, 'Nombre' => 'Santiago']],
            'arrComuna'         => [['ID1' => 1, 'ID2' => 1, 'Nombre' => 'Providencia']],
            'arrIAProvider'     => [['ID' => 1, 'Nombre' => 'Proveedor IA Prueba']],
        ];
    }

    /*******************************************************************/
    // Datos sinteticos de la vista WidgetsOrden (jerarquia bloque -> items)
    /*******************************************************************/
    private function datosWidgets(array $widgets, bool $configurado): array{
        return [
            'PageTitle'         => 'Orden de Widgets',
            'UserData'          => ['MainPathUrl' => '/admin/public/upload/'],
            'UserAccess'        => ['LevelAccess' => 2, 'RouteAccess' => self::RUTA_WEB_WIDGETS],
            'Fnc_WidgetsCommon' => new UIWidgetsCommon(),
            'Fnc_WidgetsViews'  => new UIWidgetsViews(),
            'arrWidgets'        => $widgets,
            'isConfigured'      => $configurado,
        ];
    }

    /*******************************************************************/
    // Ejecuta dataCheck() (privado) y pasa su contrato por CheckData,
    // devolviendo el resultado real de las validaciones. ValidarDominioEmail
    // consulta el DNS (lento y dependiente de la red): se desactiva aqui y su
    // declaracion en el contrato se comprueba por separado.
    /*******************************************************************/
    private function validarPost(array $post): array{
        $reflector = new ReflectionMethod(self::CONTROLADOR, 'dataCheck');
        $reflector->setAccessible(true);
        $DataCheck = $reflector->invoke($this->instanciaSinBD(), $post);
        $DataCheck['ValidarDominioEmail'] = '';
        return (new CheckData())->checkingData($DataCheck);
    }

    /*******************************************************************/
    // Post valido de referencia (datos sinteticos)
    /*******************************************************************/
    private function postValido(): array{
        return [
            'idSistema'          => '1',
            'Sistema_Nombre'     => 'Empresa Prueba',
            'Sistema_Email'      => 'contacto@empresa-prueba.cl',
            'Sistema_Rut'        => '76123456-0',
            'Sistema_Direccion'  => 'Av. Siempreviva 742',
            'Contacto_Nombre'    => 'Contacto Prueba',
            'Contacto_Email'     => 'info@empresa-prueba.cl',
            'RepresentanteNombre' => 'Representante Prueba',
            'RepresentanteRut'   => '11111111-1',
            'RepresentanteEmail' => 'rep@empresa-prueba.cl',
            'Social_X'           => 'https://x.com/prueba',
            'Social_Facebook'    => 'https://facebook.com/prueba',
            'Social_Instagram'   => 'https://instagram.com/prueba',
            'Social_Linkedin'    => 'https://linkedin.com/company/prueba',
        ];
    }

    /*******************************************************************/
    // Widgets descubiertos sinteticos (misma forma que discover())
    /*******************************************************************/
    private function widgetsDescubiertos(): array{
        $item = static function (string $nombre): array {
            return ['key' => $nombre, 'Nombre' => $nombre, 'Ruta' => 'ruta/'.$nombre, 'Permiso' => $nombre];
        };
        return [
            'Alfa' => ['key' => 'Alfa', 'Menu_Name' => 'Alfa', 'Titulo' => 'Alfa', 'items' => [
                'A1' => $item('A1'), 'A2' => $item('A2'),
            ]],
            'Beta' => ['key' => 'Beta', 'Menu_Name' => 'Beta', 'Titulo' => 'Beta', 'items' => [
                'B1' => $item('B1'),
            ]],
        ];
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica la estructura de carpetas y archivos del modulo `sistema`, la
     * convencion "clase = nombre de archivo" exigida por el AUTOLOAD de Fat-Free y
     * la disponibilidad de las clases auxiliares que usa el controlador.
     *
     * @return array Resultado por caso
     */
    public function estructuraModuloTest(){

        $results = [];

        /*====================================*/
        // Archivos obligatorios del modulo
        /*====================================*/
        $arrArchivos = [
            'controlador'        => $this->rutaModulo('../controller/'.self::CONTROLADOR.'.php'),
            'instalador'         => $this->rutaModulo('../installer/'.self::INSTALADOR.'.php'),
            'vista_resumen'      => $this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen.php'),
            'vista_resumen_upd'  => $this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen-Update.php'),
            'vista_widgets'      => $this->rutaModulo('../views/'.self::CONTROLADOR.'-WidgetsOrden.php'),
            'testing'            => $this->rutaModulo('coreSistemaTesting.php'),
            'readme'             => $this->rutaModulo('../README.md'),
            'servicio_widgets'   => $this->rutaModulo('../../../helpers/WidgetsOrderService.php'),
        ];
        foreach ($arrArchivos as $nombre => $ruta) {
            $this->addCase($results, 'archivo_'.$nombre, is_file($ruta), 'no se encuentra '.$ruta);
        }

        /*====================================*/
        // El modulo es de registro unico: no hay vistas de listado ni formularios de alta
        /*====================================*/
        $arrAusentes = ['List', 'UpdateList', 'View', 'formNew', 'formEdit', 'formSearch'];
        foreach ($arrAusentes as $sufijo) {
            $this->addCase($results, 'sin_vista_'.$sufijo,
                !is_file($this->rutaModulo('../views/'.self::CONTROLADOR.'-'.$sufijo.'.php')),
                'aparecio la vista '.$sufijo.' que no corresponde a un registro unico');
        }
        $vistas = glob($this->rutaModulo('../views/'.self::CONTROLADOR.'-*.php')) ?: [];
        $this->addCase($results, 'cantidad_vistas', count($vistas) === 3,
            'se esperaban 3 vistas y hay '.count($vistas));

        /*====================================*/
        // Proteccion de acceso directo
        /*====================================*/
        $arrHtaccess = [
            'raiz'       => $this->rutaModulo('../.htaccess'),
            'controller' => $this->rutaModulo('../controller/.htaccess'),
            'installer'  => $this->rutaModulo('../installer/.htaccess'),
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
        // Convencion clase = nombre de archivo
        /*====================================*/
        $this->addCase($results, 'clase_controlador_cargada', class_exists(self::CONTROLADOR),
            'la clase '.self::CONTROLADOR.' no esta disponible');
        $this->addCase($results, 'clase_instalador_cargada', class_exists(self::INSTALADOR),
            'la clase '.self::INSTALADOR.' no esta disponible');
        $this->addCase($results, 'clase_testing_cargada', class_exists('coreSistemaTesting'),
            'la clase coreSistemaTesting no esta disponible');

        /*====================================*/
        // Herencias esperadas por el proyecto
        /*====================================*/
        $padreController = class_exists(self::CONTROLADOR) ? get_parent_class(self::CONTROLADOR) : '';
        $this->addCase($results, 'herencia_controlador', $padreController === 'ControllerBase',
            'el controlador no extiende ControllerBase (extiende '.$padreController.')');
        $padreInstalador = class_exists(self::INSTALADOR) ? get_parent_class(self::INSTALADOR) : '';
        $this->addCase($results, 'herencia_instalador', $padreInstalador === 'ControllerInstaller',
            'el instalador no extiende ControllerInstaller (extiende '.$padreInstalador.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('coreSistemaTesting') === 'ControllerBase',
            'el testing no extiende ControllerBase');

        /*====================================*/
        // Clases auxiliares que consume el controlador
        /*====================================*/
        $arrAuxiliares = ['UIFormInputs', 'UIWidgetsCommon', 'UIWidgetsViews', 'UIWidgetsMaps', 'FunctionsDataDate',
                          'FunctionsDataNumbers', 'FunctionsSecurityCodification', 'FunctionsLocation',
                          'WidgetsOrderService'];
        foreach ($arrAuxiliares as $clase) {
            $this->addCase($results, 'auxiliar_'.$clase, class_exists($clase),
                'la clase auxiliar '.$clase.' no esta disponible');
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                            CONTROLADOR: VISTAS                             */
    /******************************************************************************/
    /**
     * Verifica el contrato de los metodos que devuelven HTML (`Resumen`,
     * `ResumenUpdate`, `WidgetsOrden`): firma, uso de `showVista()`/`returnRutaVista()`/
     * `getUserData()`, consultas de apoyo y manejo de errores.
     *
     * @return array Resultado por caso
     */
    public function controladorVistasTest(){

        $results = [];

        /*====================================*/
        // Firma de cada metodo de vista y de la consulta interna
        /*====================================*/
        foreach (['Resumen', 'ResumenUpdate', 'WidgetsOrden'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 1, 1);
        }
        $this->firmaMetodo($results, 'getDataDetail', 0, 0, false);

        /*====================================*/
        // Contrato de presentacion de cada metodo
        /*====================================*/
        $this->contratoPresentacion($results, 'Resumen', "'-Resumen.php'", '1', '1');
        $this->contratoPresentacion($results, 'ResumenUpdate', "'-Resumen-Update.php'", '2', '2');
        $this->contratoPresentacion($results, 'WidgetsOrden', "'-WidgetsOrden.php'", '1', '1');

        /*====================================*/
        // Resumen: catalogos y metadatos de la pagina
        /*====================================*/
        $resumen = $this->cuerpoMetodo(self::CONTROLADOR, 'Resumen');
        $this->addCase($results, 'resumen_detalle', str_contains($resumen, '$this->getDataDetail()'),
            'Resumen() no consulta el detalle de la empresa con getDataDetail()');
        $this->addCase($results, 'resumen_titulo', str_contains($resumen, "'PageTitle'"),
            'Resumen() no asigna PageTitle');
        $this->addCase($results, 'resumen_catalogo_ciudades', str_contains($resumen, "'table'   => 'core_ubicacion_ciudad'"),
            'Resumen() no consulta el catalogo de ciudades');
        $this->addCase($results, 'resumen_catalogo_comunas', str_contains($resumen, "'table'   => 'core_ubicacion_comunas'"),
            'Resumen() no consulta el catalogo de comunas');
        $this->addCase($results, 'resumen_catalogo_ia', str_contains($resumen, "'table'   => 'core_ia_provider'"),
            'Resumen() no consulta el catalogo de proveedores de IA');
        $this->addCase($results, 'resumen_tres_listados', substr_count($resumen, '$this->Base_GetList(') === 3,
            'Resumen() deberia ejecutar exactamente 3 listados de catalogo');
        $this->addCase($results, 'resumen_limite_listados', substr_count($resumen, 'ConfigAPP::APP["N_MaxItems"]') === 3,
            'los 3 listados de catalogo deben acotarse con N_MaxItems');
        $this->addCase($results, 'resumen_condicion_estados',
            str_contains($resumen, "\$rowData['status'] && \$arrCiudad['status'] && \$arrComuna['status'] && \$arrIAProvider['status']"),
            'Resumen() no exige que las 4 consultas respondan bien antes de pintar');
        $this->addCase($results, 'resumen_errores_cuatro',
            str_contains($resumen, 'mergeResponses([$rowData,$arrCiudad,$arrComuna,$arrIAProvider])'),
            'Resumen() no agrupa las 4 respuestas en la ruta de error');
        $this->addCase($results, 'resumen_funciones',
            str_contains($resumen, "'Fnc_FormInputs'") && str_contains($resumen, "'Fnc_WidgetsViews'")
            && str_contains($resumen, "'Fnc_WidgetsMaps'"),
            'Resumen() no entrega las funciones de formularios, vistas y mapas');

        /*====================================*/
        // ResumenUpdate: solo necesita el detalle
        /*====================================*/
        $update = $this->cuerpoMetodo(self::CONTROLADOR, 'ResumenUpdate');
        $this->addCase($results, 'update_detalle', str_contains($update, '$this->getDataDetail()'),
            'ResumenUpdate() no consulta el detalle de la empresa');
        $this->addCase($results, 'update_sin_catalogos', !str_contains($update, 'Base_GetList('),
            'ResumenUpdate() no necesita catalogos y los esta consultando');
        $this->addCase($results, 'update_estado_estricto', str_contains($update, "\$rowData['status'] === true"),
            'ResumenUpdate() no valida estrictamente el estado del detalle');

        /*====================================*/
        // WidgetsOrden: lectura por ID fijo y combinacion con los widgets descubiertos
        /*====================================*/
        $widgets = $this->cuerpoMetodo(self::CONTROLADOR, 'WidgetsOrden');
        $this->addCase($results, 'widgets_tabla', str_contains($widgets, "'table'   => 'core_sistemas'"),
            'WidgetsOrden() no lee de core_sistemas');
        $this->addCase($results, 'widgets_columna', str_contains($widgets, 'Config_Principal_Widgets'),
            'WidgetsOrden() no lee la configuracion Config_Principal_Widgets');
        $this->addCase($results, 'widgets_por_id', str_contains($widgets, "'where'   => 'idSistema = ?'")
            && str_contains($widgets, "'params'  => [1]"),
            'WidgetsOrden() debe filtrar con parametro preparado idSistema = ?');
        $this->addCase($results, 'widgets_unico_registro', str_contains($widgets, '$this->Base_GetByID('),
            'WidgetsOrden() debe leer un unico registro con Base_GetByID()');
        $this->addCase($results, 'widgets_combina',
            str_contains($widgets, 'WidgetsOrderService::merge(WidgetsOrderService::discover()'),
            'WidgetsOrden() no combina los widgets descubiertos con el orden guardado');
        $this->addCase($results, 'widgets_nulo_seguro', str_contains($widgets, "['Config_Principal_Widgets'] ?? null"),
            'WidgetsOrden() debe tolerar una configuracion nula');
        $this->addCase($results, 'widgets_entrega_items',
            str_contains($widgets, "'arrWidgets'       => \$merged['items']")
            && str_contains($widgets, "'isConfigured'     => \$merged['configured']"),
            'WidgetsOrden() no entrega arrWidgets e isConfigured a la vista');

        /*====================================*/
        // getDataDetail: consulta con joins y parametro preparado
        /*====================================*/
        $detalle = $this->cuerpoMetodo(self::CONTROLADOR, 'getDataDetail');
        $this->addCase($results, 'detalle_por_id',
            str_contains($detalle, "'where'   => 'core_sistemas.idSistema = ?'") && str_contains($detalle, "'params'  => [1]"),
            'getDataDetail() debe filtrar con parametro preparado idSistema = ?');
        $this->addCase($results, 'detalle_joins', substr_count($detalle, 'LEFT JOIN') === 4,
            'getDataDetail() deberia unir ciudad, comuna, tema y proveedor de IA (4 LEFT JOIN)');
        $this->addCase($results, 'detalle_etiquetas',
            str_contains($detalle, 'AS Ciudad') && str_contains($detalle, 'AS Comuna')
            && str_contains($detalle, 'AS Tema') && str_contains($detalle, 'AS IAProvider'),
            'getDataDetail() no etiqueta Ciudad/Comuna/Tema/IAProvider para las vistas');
        $this->addCase($results, 'detalle_id_unico', str_contains($detalle, '$this->Base_GetByID('),
            'getDataDetail() debe devolver un unico registro con Base_GetByID()');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                         CONTROLADOR: ESCRITURA                             */
    /******************************************************************************/
    /**
     * Verifica el contrato de los metodos de escritura (`Update`, `delFiles`,
     * `WidgetsOrdenSave`) SIN ejecutarlos: terminan en `Response::*()` (que hace
     * `exit`) y escriben en base de datos. Se comprueba existencia, verbo HTTP,
     * lista blanca de columnas, transaccion y respuesta leyendo el fuente de cada
     * metodo. El modulo no expone `Insert` ni `Delete` (registro unico).
     *
     * @return array Resultado por caso
     */
    public function controladorEscrituraTest(){

        $results = [];

        /*====================================*/
        // Firma de cada metodo de escritura y ausencia de alta/borrado
        /*====================================*/
        foreach (['Update', 'delFiles', 'WidgetsOrdenSave'] as $metodo) {
            $this->firmaMetodo($results, $metodo, 0, 0);
        }
        foreach (['Insert', 'Delete'] as $metodo) {
            $this->addCase($results, 'sin_'.$metodo, !method_exists(self::CONTROLADOR, $metodo),
                'el controlador expone '.$metodo.'() aunque la empresa es un registro unico');
        }

        /*====================================*/
        // Update: verbo, chequeo, lista blanca, archivo y transaccion
        /*====================================*/
        $update = $this->cuerpoMetodo(self::CONTROLADOR, 'Update');
        $this->addCase($results, 'update_verbo_post', str_contains($update, "REQUEST_METHOD'] !== 'POST'"),
            'Update() no valida el verbo HTTP POST');
        $this->addCase($results, 'update_405', str_contains($update, "Response::error('Error en el Request Method', 405)"),
            'Update() no responde 405 ante un verbo incorrecto');
        $this->addCase($results, 'update_verbo_antes_de_chequeo',
            $this->enOrden($update, ["REQUEST_METHOD'] !== 'POST'", '$this->dataCheck($_POST)']),
            'Update() debe validar el verbo antes de procesar el POST');
        $this->addCase($results, 'update_datacheck', str_contains($update, '$this->dataCheck($_POST)'),
            'Update() no construye el DataCheck con dataCheck($_POST)');
        $this->addCase($results, 'update_tabla', str_contains($update, "'table'     => 'core_sistemas'"),
            'Update() no apunta a la tabla core_sistemas');
        $this->addCase($results, 'update_donde', str_contains($update, "'where'     => 'idSistema'"),
            'Update() no delimita la escritura por idSistema');
        $this->addCase($results, 'update_obligatorios', str_contains($update, "'required'  => 'idSistema,Sistema_Nombre'"),
            'Update() no exige idSistema y Sistema_Nombre');
        $this->addCase($results, 'update_lista_blanca', str_contains($update, "'data'      => 'idSistema,Sistema_Nombre,")
            && !str_contains($update, 'array_keys($_POST)'),
            'Update() debe escribir solo las columnas declaradas (lista blanca)');
        $this->addCase($results, 'update_no_toca_widgets', !str_contains($update, 'Config_Principal_Widgets'),
            'Update() no debe poder sobrescribir Config_Principal_Widgets');
        $this->addCase($results, 'update_no_escribe_logo_en_data',
            !str_contains(explode("'required'", $update)[0], 'Sistema_IMGLogo,'),
            'el logo se gestiona por la clave files, no por la lista de columnas');
        $this->addCase($results, 'update_logo_files',
            str_contains($update, "'Identificador' => 'Sistema_IMGLogo'")
            && str_contains($update, "'SufijoArchivo' => 'LogoSis_'")
            && str_contains($update, "'ValidarTipo'   => 'image'")
            && str_contains($update, "'ValidarPeso'   => 10")
            && str_contains($update, "'Base64'        => true"),
            'Update() no declara la subida del logo (prefijo LogoSis_, tipo image, 10 MB, Base64)');
        $this->addCase($results, 'update_parametros_preparados',
            substr_count($update, "'params'") === 2 && !preg_match('/\.\s*\$_(POST|GET|REQUEST)/', $update),
            'las consultas de comuna y ciudad deben usar parametros preparados');
        $this->addCase($results, 'update_geolocalizacion',
            str_contains($update, 'new FunctionsLocation') && str_contains($update, 'geocodeAddress($Ubicacion)')
            && str_contains($update, "', Chile'"),
            'Update() no geolocaliza la direccion (comuna, ciudad y pais)');
        $this->addCase($results, 'update_geolocalizacion_condicional',
            str_contains($update, "isset(\$_POST['Sistema_Direccion'])") && str_contains($update, 'if ($result)'),
            'la geolocalizacion debe depender de que haya direccion y de que el resultado exista');
        $this->addCase($results, 'update_transaccion_completa',
            $this->enOrden($update, ['Base_transactionBegin()', 'Base_update($xParams)', 'failOperation($Response)',
                                     'Base_transactionCommit()', 'Response::success($Response[\'data\'])']),
            'Update() debe encadenar Begin -> Base_update -> failOperation -> Commit -> success');
        $this->addCase($results, 'update_lecturas_antes_de_transaccion',
            strpos($update, 'Base_GetByID(') !== false
            && strpos($update, 'Base_GetByID(') < strpos($update, 'Base_transactionBegin()'),
            'las lecturas de comuna y ciudad deben ocurrir antes de abrir la transaccion');
        $this->addCase($results, 'update_rollback_en_falla',
            str_contains($update, "\$Response['status'] === false"),
            'Update() debe revisar el estado de Base_update para revertir');

        /*====================================*/
        // delFiles: verbo PUT y limpieza del archivo
        /*====================================*/
        $del = $this->cuerpoMetodo(self::CONTROLADOR, 'delFiles');
        $this->addCase($results, 'delfiles_verbo', str_contains($del, "REQUEST_METHOD'] !== 'PUT'"),
            'delFiles() no valida el verbo HTTP PUT');
        $this->addCase($results, 'delfiles_405', str_contains($del, "Response::error('Error en el Request Method', 405)"),
            'delFiles() no responde 405 ante un verbo incorrecto');
        $this->addCase($results, 'delfiles_cuerpo_put', str_contains($del, 'parse_str(file_get_contents("php://input")'),
            'delFiles() no lee el cuerpo PUT');
        $this->addCase($results, 'delfiles_columna', str_contains($del, "'files'       => 'Sistema_IMGLogo'"),
            'delFiles() no apunta a la columna Sistema_IMGLogo');
        $this->addCase($results, 'delfiles_tabla_donde',
            str_contains($del, "'table'       => 'core_sistemas'") && str_contains($del, "'where'       => 'idSistema'"),
            'delFiles() no delimita por tabla core_sistemas e idSistema');
        $this->addCase($results, 'delfiles_base', str_contains($del, 'Base_delFiles('),
            'delFiles() no invoca Base_delFiles()');
        $this->addCase($results, 'delfiles_transaccion_completa',
            $this->enOrden($del, ['Base_transactionBegin()', 'Base_delFiles($xParams)', 'failOperation($Response)',
                                  'Base_transactionCommit()', 'Response::success($Response[\'data\'])']),
            'delFiles() debe encadenar Begin -> Base_delFiles -> failOperation -> Commit -> success');

        /*====================================*/
        // WidgetsOrdenSave: validacion previa estricta y columna unica
        /*====================================*/
        $save = $this->cuerpoMetodo(self::CONTROLADOR, 'WidgetsOrdenSave');
        $this->addCase($results, 'save_verbo_post', str_contains($save, "REQUEST_METHOD'] !== 'POST'"),
            'WidgetsOrdenSave() no valida el verbo HTTP POST');
        $this->addCase($results, 'save_405', str_contains($save, "Response::error('Error en el Request Method', 405)"),
            'WidgetsOrdenSave() no responde 405 ante un verbo incorrecto');
        $this->addCase($results, 'save_reset', str_contains($save, "\$_POST['reset']=='1'") && str_contains($save, "\$json = '[]'"),
            'WidgetsOrdenSave() no restablece el orden por defecto con reset=1');
        $this->addCase($results, 'save_decodifica', str_contains($save, "json_decode(\$_POST['Config_Principal_Widgets'] ?? ''"),
            'WidgetsOrdenSave() no decodifica Config_Principal_Widgets de forma segura');
        $this->addCase($results, 'save_valida_contra_descubiertos',
            str_contains($save, 'WidgetsOrderService::discover()')
            && str_contains($save, '!isset($discovered[$block[\'key\']])')
            && str_contains($save, '!isset($discovered[$blockKey][\'items\'][$item[\'key\']])'),
            'WidgetsOrdenSave() no valida bloques e items contra los widgets realmente descubiertos');
        $this->addCase($results, 'save_rechazos_400', substr_count($save, "Response::error('Datos de widgets inv") === 4,
            'WidgetsOrdenSave() deberia rechazar con 400 en 4 ramas (lista vacia, bloque, item y resultado vacio)');
        $this->addCase($results, 'save_todos_los_rechazos_son_400',
            substr_count($save, "Response::error('Datos de widgets inv") === preg_match_all("/Datos de widgets inv[^']*', 400\)/", $save),
            'todo rechazo de widgets debe responder con codigo 400');
        $this->addCase($results, 'save_valida_tipos',
            str_contains($save, 'is_string($block[\'key\'])') && str_contains($save, 'is_array($block)')
            && str_contains($save, 'is_string($item[\'key\'])') && str_contains($save, 'is_array($item)'),
            'WidgetsOrdenSave() no valida los tipos de cada bloque e item');
        $this->addCase($results, 'save_ignora_duplicados',
            str_contains($save, 'isset($clean[$blockKey])') && str_contains($save, 'isset($subClean[$item[\'key\']])'),
            'WidgetsOrdenSave() no descarta bloques ni items repetidos');
        $this->addCase($results, 'save_normaliza_activo', substr_count($save, '(int)') >= 2 && str_contains($save, '===0) ? 0 : 1'),
            'WidgetsOrdenSave() no normaliza el interruptor activo a 0/1');
        $this->addCase($results, 'save_rechaza_vacio', str_contains($save, 'count($clean)===0')
            && str_contains($save, 'count($items)===0'),
            'WidgetsOrdenSave() no rechaza una configuracion vacia');
        $this->addCase($results, 'save_codifica_servicio', str_contains($save, 'WidgetsOrderService::encode(array_values($clean))'),
            'WidgetsOrdenSave() no serializa con WidgetsOrderService::encode()');
        $this->addCase($results, 'save_id_fijo', str_contains($save, "'Post'      => ['idSistema' => 1, 'Config_Principal_Widgets' => \$json]")
            && !str_contains($save, "\$_POST['idSistema']"),
            'WidgetsOrdenSave() debe fijar idSistema = 1 sin tomarlo del cliente');
        $this->addCase($results, 'save_columna_unica', str_contains($save, "'data'      => 'idSistema,Config_Principal_Widgets'"),
            'WidgetsOrdenSave() debe escribir solo Config_Principal_Widgets');
        $this->addCase($results, 'save_sin_validacion_post', str_contains($save, "'novalidate' => true") && str_contains($save, "'DataCheck' => []"),
            'WidgetsOrdenSave() ya valido el JSON a mano y debe declararlo con novalidate');
        $this->addCase($results, 'save_validacion_antes_de_transaccion',
            strrpos(substr($save, 0, (int)strpos($save, 'Base_transactionBegin()')), "Response::error('Datos de widgets inv") !== false
            && strpos($save, 'Base_transactionBegin()') > strrpos($save, "Response::error('Datos de widgets inv"),
            'las validaciones del JSON deben terminar antes de abrir la transaccion');
        $this->addCase($results, 'save_transaccion_completa',
            $this->enOrden($save, ['Base_transactionBegin()', 'Base_update($xParams)', 'failOperation($Response)',
                                   'Base_transactionCommit()', 'Response::success($Response[\'data\'])']),
            'WidgetsOrdenSave() debe encadenar Begin -> Base_update -> failOperation -> Commit -> success');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                       CONTROLADOR: RUTAS (INSTALADOR)                       */
    /******************************************************************************/
    /**
     * Compara las rutas que publica `getRouteDefinitions()` del instalador con el
     * contrato esperado: verbo, handler, nivel de permiso y existencia real del
     * metodo del controlador al que apunta cada ruta.
     *
     * @return array Resultado por caso
     */
    public function controladorRutasTest(){

        $results = [];
        $rutas   = $this->rutasInstalador();

        $this->addCase($results, 'rutas_instalador_legibles', !empty($rutas),
            'no se pudo leer getRouteDefinitions() del instalador');
        $this->addCase($results, 'rutas_total', count($rutas) === count(self::RUTAS),
            'el instalador publica '.count($rutas).' rutas y se esperaban '.count(self::RUTAS));

        /*====================================*/
        // Cada ruta esperada: handler, nivel, controlador y metodo existente
        /*====================================*/
        foreach (self::RUTAS as $clave => [$handler, $nivel]) {
            $caso = strtolower(str_replace([' ', '/'], '_', $clave));
            $this->addCase($results, 'ruta_'.$caso, isset($rutas[$clave]),
                'el instalador no publica la ruta '.$clave);
            if (!isset($rutas[$clave])) {
                continue;
            }
            $this->addCase($results, 'handler_'.$caso, $rutas[$clave]['handler'] === $handler,
                $clave.' apunta a '.var_export($rutas[$clave]['handler'], true).' en vez de '.$handler);
            $this->addCase($results, 'nivel_'.$caso, $rutas[$clave]['nivel'] === $nivel,
                $clave.' exige nivel '.$rutas[$clave]['nivel'].' y se esperaba '.$nivel);
            $this->addCase($results, 'controlador_'.$caso, $rutas[$clave]['controller'] === self::CONTROLADOR,
                $clave.' declara el controlador '.var_export($rutas[$clave]['controller'], true));

            // El metodo existe (PHP no distingue mayusculas en nombres de metodo)
            [$clase, $metodo] = explode('->', $rutas[$clave]['handler']) + ['', ''];
            $this->addCase($results, 'metodo_existe_'.$caso, $clase === self::CONTROLADOR && method_exists($clase, $metodo),
                'el handler '.$rutas[$clave]['handler'].' no existe en el controlador');
        }

        /*====================================*/
        // Verbos del controlador coherentes con el verbo registrado
        /*====================================*/
        $codigo = $this->fuenteControlador();
        $this->addCase($results, 'verbo_update_coincide',
            isset($rutas['POST '.self::RUTA_WEB.'/update']) && str_contains($this->cuerpoMetodo(self::CONTROLADOR, 'Update'), "!== 'POST'"),
            'la ruta de update y el verbo que valida Update() no coinciden');
        $this->addCase($results, 'verbo_delfiles_coincide',
            isset($rutas['PUT '.self::RUTA_WEB.'/delFiles']) && str_contains($this->cuerpoMetodo(self::CONTROLADOR, 'delFiles'), "!== 'PUT'"),
            'la ruta de delFiles y el verbo que valida delFiles() no coinciden');
        $this->addCase($results, 'verbo_save_coincide',
            isset($rutas['POST '.self::RUTA_WEB_WIDGETS.'/save']) && str_contains($this->cuerpoMetodo(self::CONTROLADOR, 'WidgetsOrdenSave'), "!== 'POST'"),
            'la ruta de guardado de widgets y el verbo que valida WidgetsOrdenSave() no coinciden');

        /*====================================*/
        // Todo metodo publico de negocio del controlador tiene su ruta
        /*====================================*/
        $publicos = [];
        foreach ((new ReflectionClass(self::CONTROLADOR))->getMethods(ReflectionMethod::IS_PUBLIC) as $m) {
            if ($m->class === self::CONTROLADOR && !$m->isConstructor()) {
                $publicos[] = strtolower($m->getName());
            }
        }
        $handlers = [];
        foreach ($rutas as $r) {
            $handlers[] = strtolower(substr(strrchr($r['handler'], '>') ?: '', 1));
        }
        sort($publicos);
        $unicos = array_values(array_unique($handlers));
        sort($unicos);
        $this->addCase($results, 'metodos_publicos_con_ruta', $publicos === $unicos,
            'los metodos publicos ('.implode(',', $publicos).') no coinciden con los handlers ('.implode(',', $unicos).')');
        $this->addCase($results, 'controlador_sin_rutas_sueltas', !str_contains($codigo, '$f3->route('),
            'el controlador no debe registrar rutas por su cuenta');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                INSTALADOR                                  */
    /******************************************************************************/
    /**
     * Verifica el instalador SIN ejecutarlo (`InstallModule`/`UninstallModule` alteran
     * permisos de la plataforma y `ListDataModule` consulta la BD de administracion):
     * permisos publicados, coherencia de prefijos de ruta, transaccion de instalacion y
     * lista de controladores.
     *
     * @return array Resultado por caso
     */
    public function instaladorTest(){

        $results = [];
        $codigo  = $this->fuenteInstalador();

        /*====================================*/
        // Firmas de los metodos del instalador
        /*====================================*/
        foreach (['ListDataModule', 'InstallModule', 'UninstallModule', 'GetCountDataModule', 'getRouteDefinitions', 'listRouteModule'] as $metodo) {
            $this->addCase($results, 'instalador_metodo_'.$metodo,
                class_exists(self::INSTALADOR) && method_exists(self::INSTALADOR, $metodo)
                && (new ReflectionMethod(self::INSTALADOR, $metodo))->isPublic(),
                'el instalador no expone '.$metodo.'()');
        }
        $this->addCase($results, 'instalador_ruta_controller_privada',
            class_exists(self::INSTALADOR) && method_exists(self::INSTALADOR, 'RutaController')
            && (new ReflectionMethod(self::INSTALADOR, 'RutaController'))->isPrivate(),
            'RutaController() debe ser privado');

        /*====================================*/
        // Controladores que declara el instalador
        /*====================================*/
        try {
            $instalador = (new ReflectionClass(self::INSTALADOR))->newInstanceWithoutConstructor();
            $reflector  = new ReflectionMethod(self::INSTALADOR, 'RutaController');
            $reflector->setAccessible(true);
            $cadena     = (string)$reflector->invoke($instalador);
            $this->addCase($results, 'controladores_declarados', $cadena === '"coreSistema"',
                'RutaController() devolvio '.$cadena.' y se esperaba "coreSistema"');
        } catch (Throwable $e) {
            $this->addCase($results, 'controladores_declarados', false,
                'no se pudo leer RutaController() ('.$e->getMessage().')');
        }

        /*====================================*/
        // Rutas por tipo de permiso
        /*====================================*/
        try {
            $instalador = (new ReflectionClass(self::INSTALADOR))->newInstanceWithoutConstructor();
            $tipo1 = $instalador->getRouteDefinitions(1);
            $tipo2 = $instalador->getRouteDefinitions(2);
            $tipo3 = $instalador->getRouteDefinitions(3);
            $this->addCase($results, 'rutas_tipo1_cantidad', count($tipo1) === 4,
                'el permiso Datos de la Empresa debe publicar 4 rutas y publica '.count($tipo1));
            $this->addCase($results, 'rutas_tipo2_cantidad', count($tipo2) === 2,
                'el permiso Orden de Widgets debe publicar 2 rutas y publica '.count($tipo2));
            $this->addCase($results, 'rutas_tipo_desconocido_vacio', $tipo3 === [],
                'un tipo de permiso desconocido debe devolver un arreglo vacio');

            foreach ($tipo1 as $def) {
                $this->addCase($results, 'prefijo_tipo1_'.strtolower(basename($def['RutaWeb'])),
                    str_starts_with($def['RutaWeb'], self::RUTA_WEB.'/'),
                    $def['RutaWeb'].' no cuelga de '.self::RUTA_WEB);
            }
            foreach ($tipo2 as $def) {
                $this->addCase($results, 'prefijo_tipo2_'.strtolower(basename($def['RutaWeb'])),
                    str_starts_with($def['RutaWeb'], self::RUTA_WEB_WIDGETS.'/'),
                    $def['RutaWeb'].' no cuelga de '.self::RUTA_WEB_WIDGETS);
            }

            // Claves obligatorias de cada definicion (las exige ControllerInstaller)
            $claves = ['idMetodo', 'RutaWeb', 'RutaController', 'Descripcion', 'idLevelLimit', 'Controller'];
            $completas = true;
            foreach (array_merge($tipo1, $tipo2) as $def) {
                foreach ($claves as $clave) {
                    if (!array_key_exists($clave, $def) || $def[$clave] === '' || $def[$clave] === null) {
                        $completas = false;
                    }
                }
            }
            $this->addCase($results, 'rutas_definiciones_completas', $completas,
                'alguna ruta no declara todas las claves obligatorias del instalador');

            // listRouteModule agrega el id del permiso a cada ruta
            $conId = $instalador->listRouteModule(1, 99);
            $todas = !empty($conId);
            foreach ($conId as $def) {
                $todas = $todas && (int)($def['idPermisos'] ?? 0) === 99;
            }
            $this->addCase($results, 'listroutemodule_agrega_permiso', $todas && count($conId) === count($tipo1),
                'listRouteModule() no agrega idPermisos a cada ruta');
        } catch (Throwable $e) {
            $this->addCase($results, 'rutas_tipo1_cantidad', false,
                'no se pudo leer getRouteDefinitions() ('.$e->getMessage().')');
        }

        /*====================================*/
        // Permisos que crea InstallModule (por fuente: no se ejecuta)
        /*====================================*/
        $install = $this->cuerpoMetodo(self::INSTALADOR, 'InstallModule');
        $this->addCase($results, 'install_dos_permisos', substr_count($install, '$arrPermisos[] = [') === 2,
            'InstallModule() deberia crear 2 permisos (empresa y orden de widgets)');
        $this->addCase($results, 'install_permiso_empresa',
            str_contains($install, "'RutaWeb'        => 'administracion/sistema',")
            && str_contains($install, "'RutaController' => 'coreSistema',"),
            'InstallModule() no crea el permiso de la ruta administracion/sistema');
        $this->addCase($results, 'install_permiso_widgets',
            str_contains($install, "'RutaWeb'        => 'mantencionSistema/sistema/widgetsOrden',"),
            'InstallModule() no crea el permiso de la ruta mantencionSistema/sistema/widgetsOrden');
        $this->addCase($results, 'install_nivel_ver_editar', substr_count($install, "'idLevelLimit'   => '2',") === 2,
            'ambos permisos deben limitarse al nivel 2 (ver/editar)');
        $this->addCase($results, 'install_tipo_resumen', substr_count($install, "'idTipo'         => '2',") === 2,
            'ambos permisos deben ser de tipo CRUD Resumen (2)');
        $this->addCase($results, 'install_activos', substr_count($install, "'idEstado'       => '1',") === 2,
            'ambos permisos deben instalarse activos');
        $this->addCase($results, 'install_transaccion',
            $this->enOrden($install, ['Base_transactionBegin()', 'Base_installModule($arrPermisos)', 'Base_transactionCommit()', 'return true']),
            'InstallModule() debe encadenar Begin -> Base_installModule -> Commit -> return true');
        $this->addCase($results, 'uninstall_por_controlador',
            str_contains($this->cuerpoMetodo(self::INSTALADOR, 'UninstallModule'), 'Base_uninstallModule($this->RutaController())'),
            'UninstallModule() no desinstala por la lista de controladores');
        $this->addCase($results, 'conteo_por_controlador',
            str_contains($this->cuerpoMetodo(self::INSTALADOR, 'GetCountDataModule'), 'Base_getCountDataModule($this->RutaController())'),
            'GetCountDataModule() no cuenta por la lista de controladores');
        $this->addCase($results, 'listdata_claves',
            str_contains($codigo, "'Controller'    => \$this->controllerName") && str_contains($codigo, "'countPermisos' => \$countPermisos"),
            'ListDataModule() no devuelve Controller y countPermisos');
        $this->addCase($results, 'listdata_instalado_numerico', str_contains($codigo, 'is_numeric($nData1)&&$nData1!=0 ? 1 : 0'),
            'ListDataModule() debe tratar un conteo no numerico como no instalado');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                PERMISOS                                    */
    /******************************************************************************/
    /**
     * Verifica la sesion del usuario, el nivel de acceso del controlador y el
     * registro de las rutas en `SESSION.arrPermisos` y en el router de F3.
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
        // (getUserData/getArrLevel son protected en ControllerBase y se
        // prueban sobre la propia instancia de testing, sin tocar la BD)
        /*====================================*/
        try {
            $arrUserData = $this->getUserData($f3);
            $this->addCase($results, 'get_user_data_arreglo', is_array($arrUserData),
                'getUserData() no devolvio un arreglo');

            $arrLevel = $this->getArrLevel($f3, self::CONTROLADOR);
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
        $this->addCase($results, 'ruta_de_acceso_coincide',
            $nivel === null || in_array(($arrLevel['RouteAccess'] ?? ''), [self::RUTA_WEB, self::RUTA_WEB_WIDGETS], true),
            'RouteAccess no coincide con '.self::RUTA_WEB.' ni con '.self::RUTA_WEB_WIDGETS);

        /*====================================*/
        // Registro dinamico de las rutas desde SESSION.arrPermisos
        /*====================================*/
        $arrPermisos = $f3->get('SESSION.arrPermisos');
        $this->addCase($results, 'permisos_de_ruta_presentes', is_array($arrPermisos),
            'SESSION.arrPermisos no esta disponible');

        if (is_array($arrPermisos)) {
            $registradas = [];
            foreach ($arrPermisos as $permiso) {
                if (isset($permiso['RutaWeb'])) {
                    $registradas[$permiso['RutaWeb']] = true;
                }
            }
            $this->addCase($results, 'ruta_registrada', isset($registradas[self::RUTA_WEB.'/listAll']),
                'la ruta '.self::RUTA_WEB.'/listAll no esta entre los permisos del usuario');
            $this->addCase($results, 'ruta_widgets_registrada', isset($registradas[self::RUTA_WEB_WIDGETS.'/listAll']),
                'la ruta '.self::RUTA_WEB_WIDGETS.'/listAll no esta entre los permisos del usuario');
        }

        /*====================================*/
        // Las rutas deben existir en el router de F3 con el verbo y handler
        // esperados (solo se evaluan las que el usuario de pruebas tiene
        // registradas: el router de F3 se arma con SESSION.arrPermisos)
        /*====================================*/
        $arrRoutes = $f3->get('ROUTES');
        $this->addCase($results, 'router_disponible', is_array($arrRoutes),
            'F3 no expuso la variable ROUTES');

        foreach (self::RUTAS as $clave => [$handlerEsperado]) {
            [$verbo, $ruta] = explode(' ', $clave, 2);
            $ruta = '/'.$ruta;
            if (!is_array($arrRoutes) || !isset($arrRoutes[$ruta])) {
                continue;
            }
            $handler = (string)($arrRoutes[$ruta][0][$verbo][0] ?? '');
            $this->addCase($results, 'router_'.strtolower($verbo).'_'.strtolower(str_replace('/', '_', $ruta)),
                strcasecmp($handler, $handlerEsperado) === 0,
                'la ruta '.$ruta.' apunta a '.var_export($handler, true).' en vez de '.$handlerEsperado);
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /**
     * Renderiza `coreSistema-Resumen.php` (que incluye `coreSistema-Resumen-Update.php`)
     * con datos sinteticos y verifica las seis pestanas, los cuatro formularios de edicion,
     * los endpoints con su verbo, la carga de imagen y las secciones condicionadas por las
     * opciones del sistema (Whatsapp, mapas, IA, feed).
     *
     * `coreSistema-Resumen-Update.php` declara la funcion global `activo()`, por lo que solo
     * puede incluirse una vez por proceso: se evalua a traves de la pestana Resumen del
     * primer render y nunca se incluye directamente.
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function vistasResumenTest($f3){

        $results = [];
        $BASE    = $this->baseUrl($f3);
        $rutaRes = $this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen.php');
        $rutaUpd = (string)realpath($this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen-Update.php'));

        /*====================================*/
        // La vista Resumen-Update declara activo(): no se puede incluir si ya existe
        /*====================================*/
        $yaIncluida = in_array($rutaUpd, get_included_files(), true);
        if (!$yaIncluida && function_exists('activo')) {
            $this->addCase($results, 'funcion_activo_libre', false,
                'la funcion global activo() ya existe y colisiona con coreSistema-Resumen-Update.php');
            return $results;
        }
        $this->addCase($results, 'funcion_activo_libre', true);

        /*====================================*/
        // Render completo (Whatsapp, IA, mapa Leaflet, feed y logo activos)
        /*====================================*/
        $html = $this->renderizarVista($rutaRes, $this->datosSinteticos(2), $BASE, $f3);
        $this->addCase($results, 'resumen_render', $html !== '', 'la vista Resumen no produjo HTML');

        $arrPestanas = ['#resumen', '#resumen-edit_1', '#resumen-edit_2', '#resumen-edit_3', '#resumen-edit_4', '#resumen-edit_5'];
        foreach ($arrPestanas as $pestana) {
            $this->addCase($results, 'pestana_'.ltrim($pestana, '#'),
                str_contains($html, 'data-bs-target="'.$pestana.'"') && str_contains($html, 'id="'.ltrim($pestana, '#').'"'),
                'falta la pestana '.$pestana.' o su contenedor');
        }
        $this->addCase($results, 'pestana_resumen_activa', str_contains($html, 'nav-link w-100 active" data-bs-toggle="tab" data-bs-target="#resumen"'),
            'la pestana Resumen no es la activa por defecto');

        /*====================================*/
        // Formularios de edicion: ids, campos ocultos y botones
        /*====================================*/
        for ($i = 1; $i <= 4; $i++) {
            $this->addCase($results, 'form_'.$i.'_id', str_contains($html, 'id="FormEditData_'.$i.'"'),
                'falta el formulario FormEditData_'.$i);
            $this->addCase($results, 'form_'.$i.'_post', preg_match('/id="FormEditData_'.$i.'"[^>]*method="POST"/', $html) === 1,
                'FormEditData_'.$i.' no se declara con method POST');
            $this->addCase($results, 'form_'.$i.'_id_oculto', substr_count($html, 'name="idSistema"') >= 4,
                'los formularios no envian idSistema oculto');
        }
        $this->addCase($results, 'form_botones_guardar', substr_count($html, 'Guardar Cambios') === 4,
            'se esperaban 4 botones Guardar Cambios');
        $this->addCase($results, 'form_nombre_oculto_en_2_3_4', substr_count($html, 'name="Sistema_Nombre"') === 4,
            'Sistema_Nombre debe viajar en los 4 formularios (visible en el 1, oculto en 2, 3 y 4)');

        /*====================================*/
        // Campos por formulario
        /*====================================*/
        $arrCampos = [
            'Sistema_Nombre', 'Sistema_Email', 'Sistema_Rut', 'Sistema_idCiudad', 'Sistema_idComuna', 'Sistema_Direccion',
            'Social_X', 'Social_Facebook', 'Social_Instagram', 'Social_Linkedin',
            'Contacto_Nombre', 'Contacto_Fono1', 'Contacto_Fono2', 'Contacto_Fax', 'Contacto_Email', 'Contacto_Web',
            'RepresentanteNombre', 'RepresentanteRut', 'RepresentanteFono', 'RepresentanteEmail',
            'Config_Principal_Meteo', 'Config_Principal_Radio', 'Config_Principal_Feed', 'Config_Principal_FeedURL',
            'Config_IA_Uso', 'usuariosPermisosBodegas', 'usuariosPermisosMaquinas',
        ];
        foreach ($arrCampos as $campo) {
            $this->addCase($results, 'campo_'.$campo, str_contains($html, 'name="'.$campo.'"'),
                'el formulario no declara el campo '.$campo);
        }
        $this->addCase($results, 'campo_valor_precargado',
            str_contains($html, 'Empresa Prueba') && str_contains($html, 'contacto@empresa-prueba.cl'),
            'los formularios no precargan los datos de la empresa');

        /*====================================*/
        // Secciones condicionadas (render completo)
        /*====================================*/
        $this->addCase($results, 'whatsapp_visible', str_contains($html, 'name="Sistema_NotiWhatsapp"')
            && str_contains($html, 'name="Config_WhatsappToken"') && str_contains($html, 'name="Config_WhatsappInstanceId"'),
            'con Whatsapp activo faltan los campos de notificacion, token o instancia');
        $this->addCase($results, 'ia_visible', str_contains($html, 'Config_IA_Provider') && str_contains($html, 'name="Config_IA_ApiKey"')
            && str_contains($html, 'name="Config_IA_Model"') && str_contains($html, 'name="Config_IA_Base_URL"')
            && str_contains($html, 'name="Config_IA_Name"') && str_contains($html, 'name="Config_IA_Tone"')
            && str_contains($html, 'name="Config_IA_UsoCache"'),
            'con IA activa faltan campos de configuracion');
        $this->addCase($results, 'mapa_google_oculto_con_leaflet', !str_contains($html, 'name="Config_API_GoogleMaps"'),
            'con el motor Leaflet no debe pedirse la clave de Google Maps');

        /*====================================*/
        // Pestana Resumen (Resumen-Update incluida en el primer render)
        /*====================================*/
        if (!$yaIncluida) {
            $this->addCase($results, 'update_incluida_en_resumen', in_array($rutaUpd, get_included_files(), true),
                'Resumen no incluyo coreSistema-Resumen-Update.php');
            $arrTitulos = ['Datos B', 'Datos de Contacto', 'Representante Legal', 'APIS Y Configuraciones', 'Inteligencia Artificial', 'Redes Sociales'];
            foreach ($arrTitulos as $i => $titulo) {
                $this->addCase($results, 'resumen_seccion_'.($i + 1), str_contains($html, $titulo),
                    'la pestana Resumen no muestra la seccion '.$titulo);
            }
            $this->addCase($results, 'resumen_datos_empresa',
                str_contains($html, 'Empresa Prueba') && str_contains($html, 'Santiago') && str_contains($html, 'Providencia')
                && str_contains($html, 'Tema Claro') && str_contains($html, '76123456-0'),
                'la pestana Resumen no pinta nombre, ciudad, comuna, tema y rut');
            $this->addCase($results, 'resumen_datos_contacto_representante',
                str_contains($html, 'Contacto Prueba') && str_contains($html, 'Representante Prueba')
                && str_contains($html, 'rep@empresa-prueba.cl'),
                'la pestana Resumen no pinta contacto y representante');
            $this->addCase($results, 'resumen_imagen_logo', str_contains($html, '/admin/public/upload/logo_prueba.png'),
                'la pestana Resumen no muestra el logo con MainPathUrl');
            $this->addCase($results, 'resumen_mapa_leaflet', str_contains($html, 'map_1'),
                'con motor Leaflet y coordenadas debe dibujarse el mapa map_1');
            $this->addCase($results, 'resumen_ia_pintada', str_contains($html, 'Proveedor IA Prueba') && str_contains($html, 'Cordial'),
                'la pestana Resumen no pinta la configuracion de IA');
            $this->addCase($results, 'resumen_url_feed', str_contains($html, 'https://feeds.example.org/rss'),
                'con el feed activo debe mostrarse su URL');
            $this->addCase($results, 'resumen_interruptores_si_no', str_contains($html, '>Si<') || str_contains($html, 'Si</'),
                'los interruptores no se traducen a Si/No');
            $this->addCase($results, 'resumen_redes_enlaces',
                str_contains($html, 'href="https://x.com/prueba"') && str_contains($html, 'href="https://facebook.com/prueba"')
                && str_contains($html, 'href="https://instagram.com/prueba"') && str_contains($html, 'href="https://linkedin.com/company/prueba"'),
                'las redes sociales no se pintan como enlaces');
        }

        /*====================================*/
        // Endpoints y verbos que usa el JavaScript de la vista
        /*====================================*/
        $this->addCase($results, 'endpoint_update', str_contains($html, $BASE.'/'.self::RUTA_WEB.'/update'),
            'la vista no apunta a '.self::RUTA_WEB.'/update');
        $this->addCase($results, 'endpoint_update_post', substr_count($html, "let Metodo      = 'POST'") === 4,
            'los 4 formularios deben enviar con POST');
        $this->addCase($results, 'endpoint_refresco_resumen', str_contains($html, $BASE.'/'.self::RUTA_WEB.'/resumenUpdate'),
            'la vista no refresca la pestana con '.self::RUTA_WEB.'/resumenUpdate');
        $this->addCase($results, 'endpoint_delfiles', str_contains($html, $BASE.'/'.self::RUTA_WEB.'/delFiles')
            && str_contains($html, "let Metodo      = 'PUT'"),
            'el borrado de imagen no usa PUT sobre '.self::RUTA_WEB.'/delFiles');
        $this->addCase($results, 'endpoint_destino_borrado', str_contains($html, $BASE.'/'.self::RUTA_WEB.'/listAll'),
            'tras borrar la imagen la vista no regresa a '.self::RUTA_WEB.'/listAll');
        $rutas = $this->rutasInstalador();
        foreach (['POST '.self::RUTA_WEB.'/update', 'GET '.self::RUTA_WEB.'/resumenUpdate', 'PUT '.self::RUTA_WEB.'/delFiles', 'GET '.self::RUTA_WEB.'/listAll'] as $clave) {
            $this->addCase($results, 'endpoint_registrado_'.strtolower(str_replace([' ', '/'], '_', $clave)), isset($rutas[$clave]),
                'la vista usa '.$clave.' pero el instalador no la publica');
        }

        /*====================================*/
        // Imagen: con logo se ofrece borrarlo; sin logo se ofrece subirlo
        /*====================================*/
        $this->addCase($results, 'logo_boton_borrar', str_contains($html, "delIMG('logo_prueba.png')"),
            'con logo no se ofrece el boton Borrar Imagen');
        $this->addCase($results, 'logo_sin_subida', !str_contains($html, 'name="Sistema_IMGLogo"'),
            'con logo no debe mostrarse el cargador de imagen');
        $htmlSinLogo = $this->renderizarVista($rutaRes, $this->datosSinteticos(2, [], ['Sistema_IMGLogo' => '']), $BASE, $f3);
        $this->addCase($results, 'sin_logo_render', $htmlSinLogo !== '', 'la vista Resumen sin logo no produjo HTML');
        $this->addCase($results, 'sin_logo_sin_borrar', !str_contains($htmlSinLogo, "delIMG('logo_prueba.png')"),
            'sin logo no debe ofrecerse borrar la imagen');
        $this->addCase($results, 'sin_logo_subida', str_contains($htmlSinLogo, 'Sistema_IMGLogo'),
            'sin logo debe ofrecerse el cargador de imagen');
        $this->addCase($results, 'sin_logo_extra_data', str_contains($htmlSinLogo, '"idSistema": 1'),
            'el cargador de imagen no envia idSistema como dato extra');

        /*====================================*/
        // Variantes: Whatsapp apagado, IA apagada, Google Maps y feed apagado
        /*====================================*/
        $htmlBasico = $this->renderizarVista($rutaRes, $this->datosSinteticos(2,
            ['sistemaUsoWhatsapp' => 1, 'Config_IA_Uso' => 1, 'Config_motorMap' => 1],
            ['Config_Principal_Feed' => 1]), $BASE, $f3);
        $this->addCase($results, 'basico_render', $htmlBasico !== '', 'la variante sin opciones no produjo HTML');
        $this->addCase($results, 'basico_sin_whatsapp', !str_contains($htmlBasico, 'name="Sistema_NotiWhatsapp"')
            && !str_contains($htmlBasico, 'name="Config_WhatsappToken"') && !str_contains($htmlBasico, 'name="Config_WhatsappInstanceId"'),
            'con Whatsapp apagado no deben pedirse sus datos');
        $this->addCase($results, 'basico_sin_ia', !str_contains($htmlBasico, 'name="Config_IA_ApiKey"')
            && !str_contains($htmlBasico, 'name="Config_IA_Model"') && !str_contains($htmlBasico, 'name="Config_IA_UsoCache"'),
            'con IA apagada no deben pedirse sus datos');
        $this->addCase($results, 'basico_google_maps', str_contains($htmlBasico, 'name="Config_API_GoogleMaps"'),
            'con el motor Google Maps debe pedirse la clave de la API');
        $this->addCase($results, 'basico_switch_feed', str_contains($htmlBasico, 'Config_Principal_Feed'),
            'el interruptor del feed debe seguir disponible');

        /*====================================*/
        // El nivel de acceso lo resuelve la ruta (nivel 2): la vista no lo consulta
        /*====================================*/
        foreach ([1, 2] as $nivel) {
            $htmlNivel = $this->renderizarVista($rutaRes, $this->datosSinteticos($nivel), $BASE, $f3);
            $this->addCase($results, 'nivel_'.$nivel.'_render', $htmlNivel !== '' && str_contains($htmlNivel, 'id="FormEditData_1"'),
                'la vista no se renderiza con el nivel '.$nivel);
        }
        $this->addCase($results, 'vista_no_consulta_nivel',
            !str_contains($this->codigoFuente($rutaRes), 'LevelAccess'),
            'la vista ahora consulta LevelAccess: agregar la matriz de niveles a esta prueba');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                        VISTA: ORDEN DE WIDGETS                             */
    /******************************************************************************/
    /**
     * Renderiza `coreSistema-WidgetsOrden.php` con una jerarquia sintetica de bloques y
     * widgets: interruptores, escape de los nombres, mensaje de orden por defecto, lista
     * vacia y, sobre todo, que las URL de guardado y recarga coincidan con las rutas que
     * publica el instalador.
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function vistaWidgetsOrdenTest($f3){

        $results = [];
        $BASE    = $this->baseUrl($f3);
        $ruta    = $this->rutaModulo('../views/'.self::CONTROLADOR.'-WidgetsOrden.php');

        $widgets = [
            ['key' => 'Alfa', 'Menu_Name' => 'Alfa', 'Titulo' => 'Alfa', 'activo' => 1, 'items' => [
                ['key' => 'A1', 'Nombre' => 'Widget A1', 'Ruta' => 'ruta/a1', 'Permiso' => 'A1', 'activo' => 1],
                ['key' => 'A2', 'Nombre' => 'Widget A2', 'Ruta' => 'ruta/a2', 'Permiso' => 'A2', 'activo' => 0],
            ]],
            ['key' => 'Beta', 'Menu_Name' => 'Beta', 'Titulo' => 'Beta', 'activo' => 0, 'items' => [
                ['key' => 'B1', 'Nombre' => 'Widget B1', 'Ruta' => 'ruta/b1', 'Permiso' => 'B1', 'activo' => 1],
            ]],
        ];

        /*====================================*/
        // Estructura con orden ya configurado
        /*====================================*/
        $html = $this->renderizarVista($ruta, $this->datosWidgets($widgets, true), $BASE, $f3);
        $this->addCase($results, 'widgets_render', $html !== '', 'la vista WidgetsOrden no produjo HTML');
        $this->addCase($results, 'widgets_lista', str_contains($html, 'id="listaWidgets"'),
            'falta la lista #listaWidgets');
        $this->addCase($results, 'widgets_bloques', substr_count($html, 'class="list-group-item p-0 mb-2 widget-bloque"') === 2
            && str_contains($html, 'data-bloque="Alfa"') && str_contains($html, 'data-bloque="Beta"'),
            'no se dibujan los 2 bloques con su data-bloque');
        $this->addCase($results, 'widgets_items', substr_count($html, 'widget-item" data-key=') === 3
            && str_contains($html, 'data-key="A1"') && str_contains($html, 'data-key="A2"') && str_contains($html, 'data-key="B1"'),
            'no se dibujan los 3 sub-widgets con su data-key');
        $this->addCase($results, 'widgets_nombres_visibles',
            str_contains($html, 'Widget A1') && str_contains($html, 'Widget A2') && str_contains($html, 'Widget B1'),
            'no se muestran los nombres de los widgets');
        $this->addCase($results, 'widgets_interruptores',
            substr_count($html, 'class="form-check-input bloque-activo"') === 2
            && substr_count($html, 'class="form-check-input item-activo"') === 3,
            'se esperaban 2 interruptores de bloque y 3 de widget');
        $this->addCase($results, 'widgets_estado_marcado', substr_count($html, 'role="switch" checked') === 3,
            'solo Alfa, A1 y B1 estan activos: se esperaban 3 interruptores marcados');
        $this->addCase($results, 'widgets_asas_arrastre',
            substr_count($html, 'handle-bloque') >= 2 && substr_count($html, 'handle-item') >= 3,
            'faltan las asas de arrastre de bloques o widgets');
        $this->addCase($results, 'widgets_botones', str_contains($html, 'id="btnGuardarWidgets"') && str_contains($html, 'id="btnResetWidgets"'),
            'faltan los botones Guardar y Restablecer');
        $this->addCase($results, 'widgets_sin_mensaje_defecto', !str_contains($html, 'orden por defecto (alfab'),
            'con orden configurado no debe avisarse que se usa el orden por defecto');
        $this->addCase($results, 'widgets_serializa_jerarquia',
            str_contains($html, "Config_Principal_Widgets: JSON.stringify(bloques)")
            && str_contains($html, "enviarWidgets({ reset: 1 })"),
            'el guardado no serializa la jerarquia ni envia reset');
        $this->addCase($results, 'widgets_guardado_post', str_contains($html, "SendDataForms('POST', urlSaveWidgets"),
            'el guardado debe enviarse con POST');

        /*====================================*/
        // Sin configuracion previa: aviso de orden por defecto
        /*====================================*/
        $htmlDefecto = $this->renderizarVista($ruta, $this->datosWidgets($widgets, false), $BASE, $f3);
        $this->addCase($results, 'widgets_mensaje_defecto', str_contains($htmlDefecto, 'orden por defecto (alfab'),
            'sin configuracion debe avisarse que se usa el orden por defecto');

        /*====================================*/
        // Escape de nombres y claves (XSS)
        /*====================================*/
        $hostil = [['key' => '<b>Bloque</b>', 'Menu_Name' => 'x', 'Titulo' => 'x', 'activo' => 1, 'items' => [
            ['key' => '"><script>alert(1)</script>', 'Nombre' => '<img src=x onerror=alert(1)>', 'Ruta' => 'r', 'Permiso' => 'p', 'activo' => 1],
        ]]];
        $htmlHostil = $this->renderizarVista($ruta, $this->datosWidgets($hostil, true), $BASE, $f3);
        $this->addCase($results, 'widgets_escape_render', $htmlHostil !== '', 'la vista con nombres hostiles no produjo HTML');
        $this->addCase($results, 'widgets_escape_bloque', !str_contains($htmlHostil, '<b>Bloque</b>') && str_contains($htmlHostil, '&lt;b&gt;Bloque&lt;/b&gt;'),
            'la clave del bloque no se escapa al pintarla');
        $this->addCase($results, 'widgets_escape_item_key', !str_contains($htmlHostil, '"><script>alert(1)</script>'),
            'la clave del widget rompe el atributo data-key (sin escapar)');
        $this->addCase($results, 'widgets_escape_item_nombre', !str_contains($htmlHostil, '<img src=x onerror=alert(1)>'),
            'el nombre del widget se imprime sin escapar');

        /*====================================*/
        // Lista vacia: no se dibuja la lista ni los botones
        /*====================================*/
        $htmlVacio = $this->renderizarVista($ruta, $this->datosWidgets([], false), $BASE, $f3);
        $this->addCase($results, 'widgets_vacio_sin_lista', !str_contains($htmlVacio, 'id="listaWidgets"'),
            'sin widgets no debe dibujarse la lista');
        $this->addCase($results, 'widgets_vacio_sin_botones', !str_contains($htmlVacio, 'btnGuardarWidgets'),
            'sin widgets no deben ofrecerse los botones de guardado');

        /*====================================*/
        // Las URL de la vista deben ser rutas publicadas por el instalador
        /*====================================*/
        $rutas = $this->rutasInstalador();
        $this->addCase($results, 'widgets_url_guardado_registrada',
            str_contains($html, $BASE.'/'.self::RUTA_WEB_WIDGETS.'/save') && isset($rutas['POST '.self::RUTA_WEB_WIDGETS.'/save']),
            'la vista guarda en una URL distinta de '.self::RUTA_WEB_WIDGETS.'/save (ruta publicada por el instalador)');
        $this->addCase($results, 'widgets_url_recarga_registrada',
            str_contains($html, $BASE.'/'.self::RUTA_WEB_WIDGETS.'/listAll') && isset($rutas['GET '.self::RUTA_WEB_WIDGETS.'/listAll']),
            'la vista recarga desde una URL distinta de '.self::RUTA_WEB_WIDGETS.'/listAll (ruta publicada por el instalador)');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                       SERVICIO: WidgetsOrderService                         */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos el servicio estatico que ordena los widgets
     * (`decode`, `merge`, `encode` y `discover`): configuracion vacia o invalida,
     * migracion del formato plano antiguo, orden respetado, widgets nuevos al final,
     * widgets eliminados descartados y serializacion normalizada.
     *
     * @return array Resultado por caso
     */
    public function servicioWidgetsOrdenTest(){

        $results = [];

        if (!class_exists('WidgetsOrderService')) {
            $this->addCase($results, 'servicio_disponible', false, 'la clase WidgetsOrderService no esta disponible');
            return $results;
        }
        $this->addCase($results, 'servicio_disponible', true);
        $descubiertos = $this->widgetsDescubiertos();

        /*====================================*/
        // decode: sin configuracion o configuracion invalida => orden por defecto
        /*====================================*/
        $entradas = [['nulo', null], ['vacio', ''], ['espacios', '   '], ['texto', 'no es json'], ['objeto', '{"a":1}']];
        foreach ($entradas as [$nombre, $entrada]) {
            $this->addCase($results, 'decode_'.$nombre, WidgetsOrderService::decode($entrada) === [],
                'decode() debe devolver [] ante una entrada '.$nombre.' o invalida');
        }
        $this->addCase($results, 'decode_arreglo_vacio', WidgetsOrderService::decode('[]') === [],
            'decode("[]") debe devolver [] (orden por defecto)');
        $this->addCase($results, 'decode_no_cadena', WidgetsOrderService::decode(123) === [] && WidgetsOrderService::decode(['x']) === [],
            'decode() debe rechazar entradas que no son texto');

        /*====================================*/
        // decode: formato jerarquico
        /*====================================*/
        $json = '[{"key":"Beta","activo":0,"items":[{"key":"B1","activo":1}]},{"key":"Alfa","activo":1,"items":[{"key":"A2","activo":0},{"key":"A1"}]}]';
        $dec  = WidgetsOrderService::decode($json);
        $this->addCase($results, 'decode_jerarquico_orden', array_keys($dec) === ['Beta', 'Alfa'],
            'decode() no conserva el orden de los bloques');
        $this->addCase($results, 'decode_jerarquico_items_orden', array_keys($dec['Alfa']['items'] ?? []) === ['A2', 'A1'],
            'decode() no conserva el orden de los items');
        $this->addCase($results, 'decode_jerarquico_activo', ($dec['Beta']['activo'] ?? null) === 0 && ($dec['Alfa']['activo'] ?? null) === 1,
            'decode() no conserva el estado del bloque');
        $this->addCase($results, 'decode_item_activo_por_defecto', ($dec['Alfa']['items']['A1']['activo'] ?? null) === 1,
            'un item sin campo activo debe quedar activo');
        $this->addCase($results, 'decode_item_inactivo', ($dec['Alfa']['items']['A2']['activo'] ?? null) === 0,
            'un item con activo=0 debe quedar inactivo');

        /*====================================*/
        // decode: depuracion de basura y duplicados
        /*====================================*/
        $sucio = '[{"key":"Alfa","items":[{"key":"A1"},{"key":"A1","activo":0},{"key":""},{"nokey":1},"texto"]},{"key":"Alfa","items":[]},{"key":""},"x",{"items":[]}]';
        $dec2  = WidgetsOrderService::decode($sucio);
        $this->addCase($results, 'decode_bloques_duplicados', array_keys($dec2) === ['Alfa'],
            'decode() debe descartar bloques repetidos o sin clave');
        $this->addCase($results, 'decode_items_duplicados', array_keys($dec2['Alfa']['items'] ?? []) === ['A1']
            && ($dec2['Alfa']['items']['A1']['activo'] ?? null) === 1,
            'decode() debe conservar la primera aparicion de un item repetido y descartar los invalidos');

        /*====================================*/
        // decode: migracion del formato plano antiguo
        /*====================================*/
        $plano = '[{"key":"Alfa|A1","activo":1},{"key":"Alfa|A2","activo":0},{"key":"Beta|B1"},{"key":"sinseparador"},{"key":"|X"},{"key":"Y|"}]';
        $dec3  = WidgetsOrderService::decode($plano);
        $this->addCase($results, 'decode_plano_agrupa', array_keys($dec3) === ['Alfa', 'Beta'],
            'decode() no agrupa el formato plano por bloque');
        $this->addCase($results, 'decode_plano_items', array_keys($dec3['Alfa']['items'] ?? []) === ['A1', 'A2']
            && ($dec3['Alfa']['items']['A2']['activo'] ?? null) === 0,
            'decode() no migra los items del formato plano con su estado');
        $this->addCase($results, 'decode_plano_bloque_activo', ($dec3['Alfa']['activo'] ?? null) === 1,
            'los bloques migrados del formato plano deben quedar activos');
        $this->addCase($results, 'decode_plano_descarta_invalidos', !isset($dec3['sinseparador']) && !isset($dec3['']) && !isset($dec3['Y']),
            'decode() debe descartar claves sin separador o con partes vacias');

        /*====================================*/
        // merge: sin configuracion => todo activo, orden por defecto
        /*====================================*/
        $m0 = WidgetsOrderService::merge($descubiertos, null);
        $this->addCase($results, 'merge_defecto_no_configurado', $m0['configured'] === false && $m0['hasNew'] === false,
            'sin configuracion merge() no debe marcar configurado ni nuevos');
        $this->addCase($results, 'merge_defecto_orden', array_column($m0['items'], 'key') === ['Alfa', 'Beta'],
            'sin configuracion merge() debe respetar el orden descubierto');
        $todosActivos = true;
        foreach ($m0['items'] as $bloque) {
            $todosActivos = $todosActivos && $bloque['activo'] === 1;
            foreach ($bloque['items'] as $it) {
                $todosActivos = $todosActivos && $it['activo'] === 1;
            }
        }
        $this->addCase($results, 'merge_defecto_todo_activo', $todosActivos,
            'sin configuracion todos los bloques y widgets deben quedar activos');
        $this->addCase($results, 'merge_items_conserva_datos',
            ($m0['items'][0]['items'][0]['Ruta'] ?? '') === 'ruta/A1' && ($m0['items'][0]['items'][0]['Permiso'] ?? '') === 'A1',
            'merge() debe conservar Ruta y Permiso de cada widget descubierto');

        /*====================================*/
        // merge: orden guardado, estado, eliminados y nuevos
        /*====================================*/
        $guardado = '[{"key":"Beta","activo":0,"items":[{"key":"B1","activo":0}]},'
                  . '{"key":"Alfa","activo":1,"items":[{"key":"A2","activo":0},{"key":"FANTASMA","activo":1}]},'
                  . '{"key":"Eliminado","activo":1,"items":[{"key":"Z","activo":1}]}]';
        $m1 = WidgetsOrderService::merge($descubiertos, $guardado);
        $this->addCase($results, 'merge_configurado', $m1['configured'] === true, 'con configuracion merge() debe marcar configurado');
        $this->addCase($results, 'merge_orden_guardado', array_column($m1['items'], 'key') === ['Beta', 'Alfa'],
            'merge() no respeta el orden guardado de los bloques');
        $this->addCase($results, 'merge_descarta_bloque_eliminado', !in_array('Eliminado', array_column($m1['items'], 'key'), true),
            'merge() debe descartar bloques que ya no existen');
        $this->addCase($results, 'merge_descarta_item_eliminado',
            !in_array('FANTASMA', array_column($m1['items'][1]['items'] ?? [], 'key'), true),
            'merge() debe descartar widgets que ya no existen');
        $this->addCase($results, 'merge_estado_bloque', $m1['items'][0]['activo'] === 0 && $m1['items'][1]['activo'] === 1,
            'merge() no conserva el estado del bloque');
        $this->addCase($results, 'merge_orden_items', array_column($m1['items'][1]['items'] ?? [], 'key') === ['A2', 'A1'],
            'merge() debe poner primero A2 (guardado) y despues A1 (nuevo)');
        $this->addCase($results, 'merge_item_nuevo_activo', ($m1['items'][1]['items'][1]['activo'] ?? null) === 1,
            'un widget nuevo debe agregarse activo');
        $this->addCase($results, 'merge_item_guardado_inactivo', ($m1['items'][1]['items'][0]['activo'] ?? null) === 0,
            'un widget guardado como inactivo debe seguir inactivo');
        $this->addCase($results, 'merge_marca_nuevos', $m1['hasNew'] === true,
            'con un widget nuevo hasNew debe ser verdadero');

        // Bloque nuevo al final cuando ya habia configuracion
        $parcial = '[{"key":"Alfa","activo":1,"items":[{"key":"A1","activo":1},{"key":"A2","activo":1}]}]';
        $m2 = WidgetsOrderService::merge($descubiertos, $parcial);
        $this->addCase($results, 'merge_bloque_nuevo_al_final', array_column($m2['items'], 'key') === ['Alfa', 'Beta'] && $m2['hasNew'] === true,
            'un bloque nuevo debe agregarse al final y marcar hasNew');

        // Configuracion completa y sin novedades
        $completo = '[{"key":"Alfa","activo":1,"items":[{"key":"A1","activo":1},{"key":"A2","activo":1}]},{"key":"Beta","activo":1,"items":[{"key":"B1","activo":1}]}]';
        $m3 = WidgetsOrderService::merge($descubiertos, $completo);
        $this->addCase($results, 'merge_sin_novedades', $m3['configured'] === true && $m3['hasNew'] === false,
            'una configuracion completa no debe marcar widgets nuevos');
        $this->addCase($results, 'merge_vacio_descubierto', WidgetsOrderService::merge([], $completo)['items'] === [],
            'sin widgets descubiertos merge() debe devolver una lista vacia');

        /*====================================*/
        // encode: serializacion normalizada e ida y vuelta
        /*====================================*/
        $codificado = WidgetsOrderService::encode([
            ['key' => 'Alfa', 'activo' => 5, 'items' => [['key' => 'A1', 'activo' => 0], ['key' => 'A2'], ['key' => ''], ['nokey' => 1]]],
            ['key' => '', 'activo' => 1, 'items' => []],
            ['nokey' => 'x'],
            ['key' => 'Beta', 'activo' => 0],
        ]);
        $decodificado = json_decode($codificado, true);
        $this->addCase($results, 'encode_json_valido', is_array($decodificado), 'encode() no produjo un JSON valido');
        $this->addCase($results, 'encode_descarta_sin_clave', is_array($decodificado) && array_column($decodificado, 'key') === ['Alfa', 'Beta'],
            'encode() debe descartar bloques sin clave');
        $this->addCase($results, 'encode_normaliza_activo',
            is_array($decodificado) && $decodificado[0]['activo'] === 1 && $decodificado[0]['items'][0]['activo'] === 0
            && $decodificado[0]['items'][1]['activo'] === 1 && $decodificado[1]['activo'] === 0,
            'encode() debe normalizar activo a 0/1 (0 solo si es 0)');
        $this->addCase($results, 'encode_items_validos', is_array($decodificado) && count($decodificado[0]['items']) === 2 && $decodificado[1]['items'] === [],
            'encode() debe descartar items sin clave y tolerar bloques sin items');
        $this->addCase($results, 'encode_vacio', WidgetsOrderService::encode([]) === '[]',
            'encode([]) debe devolver "[]"');
        $this->addCase($results, 'encode_unicode', str_contains(WidgetsOrderService::encode([['key' => 'Mantención', 'items' => []]]), 'Mantención'),
            'encode() debe conservar los caracteres UTF-8 sin escapar');
        $this->addCase($results, 'ida_y_vuelta', array_keys(WidgetsOrderService::decode($codificado)) === ['Alfa', 'Beta'],
            'decode(encode(x)) no conserva los bloques');

        /*====================================*/
        // discover: forma de los widgets reales instalados en el sistema
        /*====================================*/
        $clases = WidgetsOrderService::discoverClasses();
        $this->addCase($results, 'discover_clases_arreglo', is_array($clases), 'discoverClasses() no devolvio un arreglo');
        $this->addCase($results, 'discover_clases_con_load_widgets',
            count(array_filter($clases, static fn($c) => method_exists($c, 'loadWidgets'))) === count($clases),
            'discoverClasses() debe devolver solo clases con loadWidgets()');
        $reales = WidgetsOrderService::discover();
        $this->addCase($results, 'discover_arreglo', is_array($reales), 'discover() no devolvio un arreglo');
        $this->addCase($results, 'discover_hay_widgets', count($reales) > 0, 'discover() no encontro ningun bloque de widgets');
        $formaOk = true;
        foreach ($reales as $clave => $bloque) {
            $formaOk = $formaOk && ($bloque['key'] ?? null) === $clave && !empty($bloque['items'])
                && isset($bloque['Menu_Name'], $bloque['Titulo']);
            foreach ($bloque['items'] ?? [] as $nombre => $item) {
                $formaOk = $formaOk && ($item['key'] ?? null) === $nombre && isset($item['Nombre'], $item['Ruta'], $item['Permiso'])
                    && $item['Permiso'] !== '';
            }
        }
        $this->addCase($results, 'discover_forma_bloques', $formaOk,
            'algun bloque descubierto no cumple la forma key/Menu_Name/Titulo/items(key,Nombre,Ruta,Permiso)');
        $this->addCase($results, 'discover_sin_bloques_vacios',
            count(array_filter($reales, static fn($b) => count($b['items']) === 0)) === 0,
            'discover() debe descartar bloques sin widgets');
        $this->addCase($results, 'discover_merge_estable', count(WidgetsOrderService::merge($reales, null)['items']) === count($reales),
            'merge() sin configuracion debe devolver un bloque por cada bloque descubierto');
        $this->addCase($results, 'loadwidgets_clase_invalida',
            WidgetsOrderService::loadWidgetsSinConexion('ClaseQueNoExiste') === []
            && WidgetsOrderService::loadWidgetsSinConexion('') === []
            && WidgetsOrderService::loadWidgetsSinConexion(null) === [],
            'loadWidgetsSinConexion() debe devolver [] ante una clase invalida');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                              LOGICA PURA                                   */
    /******************************************************************************/
    /**
     * Ejecuta sin base de datos el chequeo de datos del controlador (`dataCheck`) junto con
     * `CheckData`, el cifrado de identificadores y la validacion de IDs descifrados.
     *
     * @return array Resultado por caso
     */
    public function logicaValidacionTest(){

        $results = [];

        /*====================================*/
        // dataCheck devuelve el contrato esperado por Base_update
        /*====================================*/
        try {
            $reflector = new ReflectionMethod(self::CONTROLADOR, 'dataCheck');
            $reflector->setAccessible(true);
            $Post      = $this->postValido();
            $DataCheck = $reflector->invoke($this->instanciaSinBD(), $Post);

            $this->addCase($results, 'datacheck_es_arreglo', is_array($DataCheck),
                'dataCheck() no devolvio un arreglo');
            $this->addCase($results, 'datacheck_post', ($DataCheck['Post'] ?? null) === $Post,
                'dataCheck() no conserva el POST recibido');
            $this->addCase($results, 'datacheck_sin_obligatorios', (string)($DataCheck['emptyData'] ?? 'x') === '',
                'dataCheck() delega los obligatorios en Base_update (emptyData vacio)');
            $this->addCase($results, 'datacheck_emails',
                (string)($DataCheck['ValidarEmail'] ?? '') === 'Sistema_Email,Contacto_Email,RepresentanteEmail',
                'dataCheck() no valida los 3 correos');
            $this->addCase($results, 'datacheck_ruts',
                (string)($DataCheck['ValidarRut'] ?? '') === 'Sistema_Rut,RepresentanteRut',
                'dataCheck() no valida los 2 ruts');
            $this->addCase($results, 'datacheck_urls',
                (string)($DataCheck['ValidarURL'] ?? '') === 'Social_X,Social_Facebook,Social_Instagram,Social_Linkedin',
                'dataCheck() no valida las 4 redes sociales como URL');
            $this->addCase($results, 'datacheck_largo',
                (string)($DataCheck['ValidarLargoMinimo'] ?? '') === 'Sistema_Email,Contacto_Email,RepresentanteEmail,Sistema_Nombre,Sistema_Direccion,Contacto_Nombre,RepresentanteNombre'
                && (int)($DataCheck['ValidarLargoMinimoN'] ?? 0) === 3
                && ($DataCheck['ValidarLargoMaximoPorCampo'] ?? []) === [
                    'Sistema_Nombre' => 120,
                    'Sistema_Direccion' => 180,
                    'Sistema_NotiWhatsapp' => 15,
                    'Sistema_Rut' => 13,
                    'Contacto_Nombre' => 120,
                    'Contacto_Fono1' => 15,
                    'Contacto_Fono2' => 15,
                    'Contacto_Fax' => 15,
                    'Contacto_Email' => 120,
                    'Contacto_Web' => 120,
                    'RepresentanteNombre' => 120,
                    'RepresentanteRut' => 13,
                    'RepresentanteFono' => 120,
                    'RepresentanteEmail' => 120,
                    'Config_API_GoogleMaps' => 255,
                    'Config_WhatsappToken' => 255,
                    'Config_WhatsappInstanceId' => 255,
                    'Config_Principal_FeedURL' => 255,
                    'Config_IA_Base_URL' => 255,
                    'Config_IA_Model' => 255,
                    'Config_IA_Name' => 255,
                    'Config_IA_Tone' => 255,
                    'Social_X' => 255,
                    'Social_Facebook' => 255,
                    'Social_Instagram' => 255,
                    'Social_Linkedin' => 255,
                    'Sistema_Email' => 60,
                ],
                'dataCheck() no fija correctamente todos los campos de largo (minimo/maximo)');
            $this->addCase($results, 'datacheck_censura',
                (string)($DataCheck['ValidarPalabrasCensuradas'] ?? '') === 'Sistema_Nombre,Sistema_Direccion,Contacto_Nombre,RepresentanteNombre',
                'dataCheck() no censura palabras en nombres y direccion');
            $this->addCase($results, 'datacheck_espacios_mayusculas_dominio',
                str_contains((string)($DataCheck['ValidarEspaciosVacios'] ?? ''), 'Sistema_Email')
                && (string)($DataCheck['ValidarMayusculas'] ?? '') === 'Sistema_Email,Contacto_Email,RepresentanteEmail'
                && (string)($DataCheck['ValidarDominioEmail'] ?? '') === 'Sistema_Email,Contacto_Email,RepresentanteEmail',
                'dataCheck() no controla espacios, mayusculas y dominio de los correos');
            foreach (['ValidarNumero', 'ValidarEntero', 'ValidarPatente', 'ValidarFecha', 'ValidarHora', 'ValidarCoincidencias',
                      'ValidarPasswordSegura', 'ValidarFechaRango', 'ValidarEdadMinima', 'ValidarJSON', 'ValidarUUID',
                      'ValidarIP', 'ValidarSoloAlfanumerico', 'ValidarSoloLetras', 'encode'] as $inactiva) {
                $this->addCase($results, 'datacheck_inactiva_'.$inactiva, (string)($DataCheck[$inactiva] ?? 'x') === '',
                    'dataCheck() no debe activar '.$inactiva.' para la empresa');
            }
        } catch (Throwable $e) {
            $this->addCase($results, 'datacheck_es_arreglo', false,
                'no se pudo ejecutar dataCheck() ('.$e->getMessage().')');
        }

        /*====================================*/
        // Validaciones reales: dataCheck + CheckData (sin BD)
        /*====================================*/
        try {
            $ok = $this->validarPost($this->postValido());
            $this->addCase($results, 'valida_post_correcto', ($ok['status'] ?? false) === true,
                'una empresa sintetica valida fue rechazada: '.json_encode($ok['error'] ?? []));

            // Casos invalidos: campo => valor que debe ser rechazado
            $invalidos = [
                'email_sin_arroba'        => ['Sistema_Email', 'correo.sin.arroba'],
                'email_sin_dominio'       => ['Contacto_Email', 'usuario@'],
                'email_con_espacios'      => ['RepresentanteEmail', 'rep @empresa.cl'],
                'email_mayusculas'        => ['Sistema_Email', 'CONTACTO@EMPRESA-PRUEBA.CL'],
                'email_muy_corto'         => ['Sistema_Email', 'a@b'],
                'rut_invalido'            => ['Sistema_Rut', '12345'],
                'rut_digito_incorrecto'   => ['RepresentanteRut', '11111111-9'],
                'url_sin_esquema'         => ['Social_X', 'no es una url'],
                'url_con_espacios'        => ['Social_Facebook', 'https://face book.com/x'],
                'nombre_muy_corto'        => ['Sistema_Nombre', 'Ab'],
                'nombre_muy_largo'        => ['Sistema_Nombre', str_repeat('a', 61)],
                'direccion_muy_corta'     => ['Sistema_Direccion', 'ab'],
                'direccion_muy_larga'     => ['Sistema_Direccion', str_repeat('d', 61)],
                'contacto_muy_largo'      => ['Contacto_Nombre', str_repeat('c', 61)],
                'representante_muy_corto' => ['RepresentanteNombre', 'Re'],
            ];
            foreach ($invalidos as $caso => [$campo, $valor]) {
                $post         = $this->postValido();
                $post[$campo] = $valor;
                $r            = $this->validarPost($post);
                $this->addCase($results, 'rechaza_'.$caso, ($r['status'] ?? true) === false,
                    'un '.$campo.' invalido ('.substr((string)$valor, 0, 14).') fue aceptado');
            }

            // Limites validos: el borde se acepta
            $limites = [
                'nombre_largo_minimo'     => ['Sistema_Nombre', 'Abc'],
                'nombre_largo_maximo'     => ['Sistema_Nombre', str_repeat('a', 60)],
                'direccion_largo_maximo'  => ['Sistema_Direccion', str_repeat('d', 60)],
                'rut_con_k'               => ['Sistema_Rut', '10000013-K'],
                'url_http'                => ['Social_X', 'http://x.com/prueba'],
            ];
            foreach ($limites as $caso => [$campo, $valor]) {
                $post         = $this->postValido();
                $post[$campo] = $valor;
                $r            = $this->validarPost($post);
                $this->addCase($results, 'acepta_'.$caso, ($r['status'] ?? false) === true,
                    'un '.$campo.' valido en el limite fue rechazado: '.json_encode($r['error'] ?? []));
            }

            // Campos opcionales vacios: no se validan
            $post = $this->postValido();
            foreach (['Social_X', 'Social_Facebook', 'Social_Instagram', 'Social_Linkedin', 'Contacto_Email', 'RepresentanteRut', 'RepresentanteEmail'] as $opcional) {
                unset($post[$opcional]);
            }
            $r = $this->validarPost($post);
            $this->addCase($results, 'acepta_opcionales_ausentes', ($r['status'] ?? false) === true,
                'los campos opcionales ausentes no deben bloquear el guardado: '.json_encode($r['error'] ?? []));
        } catch (Throwable $e) {
            $this->addCase($results, 'valida_post_correcto', false,
                'no se pudo ejecutar CheckData ('.$e->getMessage().')');
        }

        /*====================================*/
        // Cifrado de identificadores: ida y vuelta estable
        /*====================================*/
        try {
            $Codification = new FunctionsSecurityCodification();
            $cifrado      = $Codification->encryptDecrypt('encrypt', '42');
            $this->addCase($results, 'cifrado_genera_dato',
                ($cifrado['success'] ?? false) === true && !empty($cifrado['data']),
                'encryptDecrypt(encrypt) no produjo un valor cifrado');
            $this->addCase($results, 'cifrado_oculta_id', (string)($cifrado['data'] ?? '') !== '42',
                'el valor cifrado coincide con el ID plano');

            $descifrado = $Codification->encryptDecrypt('decrypt', (string)($cifrado['data'] ?? ''));
            $this->addCase($results, 'cifrado_ida_y_vuelta',
                ($descifrado['success'] ?? false) === true && (string)($descifrado['data'] ?? '') === '42',
                'el cifrado no devuelve el valor original');

            $basura = $Codification->encryptDecrypt('decrypt', 'valor-que-no-es-un-token');
            $this->addCase($results, 'cifrado_rechaza_token_ajeno',
                !(($basura['success'] ?? false) === true && $this->isValidDecrypted($basura, 'id')),
                'un token arbitrario no deberia pasar como ID valido');
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
        $this->addCase($results, 'id_valido_tipo_texto', $this->isValidDecrypted(['success' => true, 'data' => 'abc'], 'text') === true,
            'un texto no vacio debe ser valido para tipo text');
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
     * Revisa estaticamente las vistas y el controlador del modulo: ausencia de rutas
     * relativas maliciosas, de rutas absolutas del sistema de ficheros, de acceso directo
     * a la sesion y de SQL embebido en las plantillas; escape de los datos de la pantalla
     * de widgets y uso de parametros preparados en el controlador.
     *
     * @return array Resultado por caso
     */
    public function seguridadVistasTest(){

        $results = [];

        /*====================================*/
        // Vistas involucradas
        /*====================================*/
        $arrVistas = glob($this->rutaModulo('../views/'.self::CONTROLADOR.'-*.php')) ?: [];
        $this->addCase($results, 'hay_vistas', count($arrVistas) > 0,
            'no se encontraron vistas del controlador '.self::CONTROLADOR);

        foreach ($arrVistas as $ruta) {
            $nombre  = basename($ruta);
            $codigo  = $this->codigoFuente($ruta);

            $this->addCase($results, 'vista_sin_retroceso_'.$nombre, !preg_match('#(?:\.\./|\.\.\\\\)#', $codigo),
                $nombre.' construye rutas con retroceso');
            $this->addCase($results, 'vista_sin_ruta_absoluta_'.$nombre,
                !preg_match('#(?:/var/www|/home/|[A-Za-z]:\\\\)#', $codigo),
                $nombre.' fija una ruta absoluta del servidor');
            $this->addCase($results, 'vista_sin_sesion_'.$nombre, !str_contains($codigo, '$_SESSION'),
                $nombre.' accede a la sesion de forma directa');
            $this->addCase($results, 'vista_sin_sql_'.$nombre, !preg_match('/\b(SELECT|INSERT|UPDATE|DELETE)\s+\w+\s+FROM\b/i', $codigo),
                $nombre.' contiene una consulta SQL embebida');
            $this->addCase($results, 'vista_sin_eval_'.$nombre, !preg_match('/\b(eval|system|shell_exec|passthru|exec)\s*\(/', $codigo),
                $nombre.' ejecuta codigo o comandos dinamicos');
        }

        /*====================================*/
        // Escape de los datos pintados en la pantalla de widgets
        /*====================================*/
        $widgets = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-WidgetsOrden.php'));
        $this->addCase($results, 'widgets_escape_htmlspecialchars', substr_count($widgets, 'htmlspecialchars(') >= 4,
            'WidgetsOrden pinta claves y nombres sin htmlspecialchars()');
        $this->addCase($results, 'widgets_escape_utf8', substr_count($widgets, 'ENT_QUOTES, \'UTF-8\'') === substr_count($widgets, 'htmlspecialchars('),
            'todo htmlspecialchars() debe usar ENT_QUOTES y UTF-8');

        /*====================================*/
        // Identificadores: el id de la empresa nunca se cifra ni viaja por URL
        /*====================================*/
        $resumenUpdate = $this->codigoFuente($this->rutaModulo('../views/'.self::CONTROLADOR.'-Resumen-Update.php'));
        $this->addCase($results, 'sin_id_en_url', !preg_match('#/(update|delFiles|listAll|resumenUpdate)/@?\$#', $resumenUpdate),
            'la vista de resumen no debe armar URL con el id de la empresa');

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
        $this->addCase($results, 'controlador_where_con_marcador',
            substr_count($controlador, "'where'   => 'idSistema = ?'") + substr_count($controlador, "'where'   => 'core_sistemas.idSistema = ?'")
            + substr_count($controlador, "'where'   => 'idComuna = ?'") + substr_count($controlador, "'where'   => 'idCiudad = ?'") === 4,
            'las 4 consultas con filtro deben usar marcadores ? en el WHERE');
        $this->addCase($results, 'controlador_sin_eval', !preg_match('/\b(eval|system|shell_exec|passthru|exec|unserialize)\s*\(/', $controlador),
            'el controlador ejecuta codigo dinamico o deserializa datos');
        $this->addCase($results, 'controlador_sin_superglobal_files', !str_contains($controlador, '$_FILES'),
            'la subida del logo debe delegarse en Base_update (validacion de tipo y peso), no manejar $_FILES a mano');

        /************************************/
        // Retorno los datos
        return $results;

    }

}
