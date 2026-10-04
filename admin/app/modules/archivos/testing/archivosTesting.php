<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class archivosTesting
 *
 * Pruebas del modulo `archivos` (Gestor de Archivos).
 *
 * Convenciones del sistema de testeos:
 *  - Toda prueba es un metodo publico que termina en "Test" (las descubre automaticamente
 *    `sistemaTesteos::discoverTests()`).
 *  - Cada metodo devuelve un arreglo [caso => 'OK'|'FALLO: razon']. El normalizador
 *    `sistemaTesteos::normalizeResult()` marca EXITOSA o ERROR segun el contenido, por lo que
 *    los textos de los casos correctos NO deben contener las palabras 'error', 'fail' ni 'fallo'.
 *  - Si el metodo declara parametros, el runner le inyecta la instancia de F3.
 *
 * Alcance: pruebas NO destructivas sobre la base de datos. No se ejercitan el instalador
 * del modulo (`InstallModule()` / `UninstallModule()`, que alteran la tabla de permisos de la
 * plataforma) ni `uploadFile()` real (el driver local usa `move_uploaded_file()`, que solo
 * funciona dentro de una peticion POST autentica).
 *
 * @see skills/testing/SKILL.md
 */
class archivosTesting extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    /*=========== Constantes de las pruebas ===========*/
    private const CONTROLADOR        = 'archivosListado';
    private const RUTA_WEB           = 'gestionDocumentacion/fileManager/listado';
    private const RUTA_WEB_LISTADO   = 'gestionDocumentacion/fileManager/listado/listAll';
    private const SUBRUTA_EXPLORADOR = 'fileExplorer';
    // Sufijo de las carpetas temporales creadas por las pruebas (se limpian siempre)
    private const PREFIJO_PRUEBAS    = 'tmp_pruebas_archivos_';

    // Rutas de los archivos temporales usados como fixtures de la subida
    private array $arrTemporales = [];

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
    /*                                 EJECUCION                                  */
    /******************************************************************************/


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
    // Crea una carpeta temporal de pruebas y devuelve su nombre
    /*******************************************************************/
    private function nuevaCarpetaPrueba(FileManager $FileManager): string{
        $nombre = self::PREFIJO_PRUEBAS.uniqid();
        $FileManager->createFolder(['SubRoute' => self::SUBRUTA_EXPLORADOR, 'name' => $nombre]);

        return $nombre;
    }

    /*******************************************************************/
    // Elimina una carpeta temporal de pruebas (idempotente)
    /*******************************************************************/
    private function limpiarCarpetaPrueba(FileManager $FileManager, string $nombre){
        $FileManager->deleteFolder(['SubRoute' => self::SUBRUTA_EXPLORADOR, 'name' => $nombre]);
    }

    /*******************************************************************/
    // Construye una entrada de $_FILES sintetica sobre un archivo temporal real.
    // La ruta temporal se registra para su limpieza posterior.
    /*******************************************************************/
    private function archivoFalso(string $nombre, string $contenido, int $size = 0, int $codigo = 0): array{
        $tmpName = tempnam(sys_get_temp_dir(), 'archivosTest');
        file_put_contents($tmpName, $contenido);

        /*=========== Se registra para limpiar ===========*/
        $this->arrTemporales[] = $tmpName;

        return ['file' => [
            'name'     => $nombre,
            'type'     => 'application/octet-stream',
            'tmp_name' => $tmpName,
            'error'    => $codigo,
            'size'     => $size > 0 ? $size : (int)filesize($tmpName),
        ]];
    }

    /*******************************************************************/
    // Elimina los archivos temporales registrados y los deja en memoria
    /*******************************************************************/
    private function limpiarTemporales(): bool{
        $ok = true;
        foreach ($this->arrTemporales as $tmpName) {
            if (is_file($tmpName) && !@unlink($tmpName)) {
                $ok = false;
            }
        }
        $this->arrTemporales = [];

        return $ok;
    }

    /******************************************************************************/
    /*                           ESTRUCTURA DEL MODULO                             */
    /******************************************************************************/
    /**
     * Verifica la estructura de carpetas y archivos del modulo `archivos`:
     * existencia de los archivos obligatorios, proteccion `.htaccess` en cada carpeta
     * y la convencion del proyecto "nombre de clase = nombre de archivo" (requisito
     * del AUTOLOAD de Fat-Free Framework).
     *
     * @return array Resultado por caso
     */
    public function estructuraModuloTest(){

        $results = [];

        /*====================================*/
        // Archivos obligatorios del modulo
        /*====================================*/
        $arrArchivos = [
            'controlador' => $this->rutaModulo('../controller/archivosListado.php'),
            'vista'       => $this->rutaModulo('../views/archivosListado-List.php'),
            'testing'     => $this->rutaModulo('archivosTesting.php'),
            'readme'      => $this->rutaModulo('../README.md'),
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
            $contenido = (string)file_get_contents($ruta);
            $this->addCase($results, 'htaccess_'.$nombre,
                (stripos($contenido, 'deny from all') !== false),
                'el archivo no contiene "deny from all"'
            );
        }

        /*====================================*/
        // Convencion clase = nombre de archivo
        /*====================================*/
        $this->addCase($results, 'clase_controlador_cargada', class_exists('archivosListado'), 'la clase archivosListado no esta disponible');
        $this->addCase($results, 'clase_testing_cargada',     class_exists('archivosTesting'), 'la clase archivosTesting no esta disponible');

        /*====================================*/
        // Herencias esperadas por el proyecto
        /*====================================*/
        $padreController = class_exists('archivosListado') ? get_parent_class('archivosListado') : '';
        $this->addCase($results, 'herencia_controlador', $padreController === 'ControllerFiles',
            'el controlador no extiende ControllerFiles (extiende '.$padreController.')');
        $this->addCase($results, 'herencia_testing', get_parent_class('archivosTesting') === 'ControllerBase',
            'el testing no extiende ControllerBase');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /******************************************************************************/
    /*                       CONTROLADOR, SESION Y VISTA                          */
    /******************************************************************************/
    /**
     * Verifica el contrato del controlador de la vista `archivosListado`:
     * existencia y firma de `listAll()`, y uso de las piezas de presentacion
     * (`showVista()` de pagina completa y `returnRutaVista()`).
     *
     * @return array Resultado por caso
     */
    public function controladorListadoTest(){

        $results = [];

        /*====================================*/
        // Firma del metodo de vista
        /*====================================*/
        $this->addCase($results, 'metodo_listall', method_exists('archivosListado', 'listAll'),
            'el controlador no expone listAll()');

        if (method_exists('archivosListado', 'listAll')) {
            $reflector = new ReflectionMethod('archivosListado', 'listAll');
            $this->addCase($results, 'listall_publico', $reflector->isPublic(), 'listAll() no es publico');
            $this->addCase($results, 'listall_recibe_f3', $reflector->getNumberOfParameters() === 1,
                'listAll() deberia recibir unicamente la instancia de F3');
        }

        /*====================================*/
        // El controlador solo expone vistas (no hay acciones de datos)
        /*====================================*/
        $accionesDatos = [];
        foreach (['Insert', 'Update', 'Delete', 'DelFiles'] as $accion) {
            if (method_exists('archivosListado', $accion)) {
                $accionesDatos[] = $accion;
            }
        }
        $this->addCase($results, 'sin_acciones_de_datos', empty($accionesDatos),
            'el controlador expone acciones de datos: '.implode(', ', $accionesDatos));

        /*====================================*/
        // Piezas de presentacion utilizadas por listAll()
        /*====================================*/
        $codigo = (string)file_get_contents($this->rutaModulo('../controller/archivosListado.php'));
        $this->addCase($results, 'prepara_datos_pagina', str_contains($codigo, "'PageTitle'"), 'no asigna PageTitle');
        $this->addCase($results, 'titulo_explorador', str_contains($codigo, 'Explorador Archivos'),
            'el titulo de la pagina no es "Explorador Archivos"');
        $this->addCase($results, 'obtiene_datos_usuario', str_contains($codigo, 'getUserData($f3)'), 'no obtiene los datos del usuario');
        $this->addCase($results, 'obtiene_nivel_acceso', str_contains($codigo, 'getArrLevel($f3'), 'no obtiene el nivel de acceso');
        $this->addCase($results, 'renderiza_pagina_completa', str_contains($codigo, 'showVista(1,'),
            'no renderiza la vista como pagina completa (showVista(1, ...))');
        $this->addCase($results, 'resuelve_ruta_vista', str_contains($codigo, 'returnRutaVista('), 'no resuelve la ruta de la vista');
        $this->addCase($results, 'instancia_widget', str_contains($codigo, 'new UIWidgetsCommon()'), 'no instancia el widget comun');

        /************************************/
        // Retorno los datos
        return $results;

    }
    /**
     * Verifica la cadena de permisos del modulo con la sesion real del usuario:
     * datos de usuario, nivel de acceso del controlador y registro dinamico de la ruta.
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
        // El controlador debe leer la sesion de forma segura
        /*====================================*/
        try {
            $Controller = new archivosListado();

            $reflector = new ReflectionMethod('archivosListado', 'getUserData');
            $reflector->setAccessible(true);
            $arrUserData = $reflector->invoke($Controller, $f3);
            $this->addCase($results, 'get_user_data_arreglo', is_array($arrUserData), 'getUserData() no devolvio un arreglo');

            $reflector = new ReflectionMethod('archivosListado', 'getArrLevel');
            $reflector->setAccessible(true);
            $arrLevel = $reflector->invoke($Controller, $f3, self::CONTROLADOR);
            $this->addCase($results, 'get_arr_level_arreglo', is_array($arrLevel), 'getArrLevel() no devolvio un arreglo');
        } catch (Throwable $e) {
            $this->addCase($results, 'get_arr_level_arreglo', false,
                'no se pudo leer la sesion del controlador ('.$e->getMessage().')');
            $arrLevel = [];
        }

        /*====================================*/
        // El nivel otorgado debe estar en el catalogo 1..4
        /*====================================*/
        $nivel = is_array($arrLevel) ? ($arrLevel['LevelAccess'] ?? null) : null;
        $this->addCase($results, 'nivel_en_rango', $nivel === null || (is_numeric($nivel) && (int)$nivel >= 1 && (int)$nivel <= 4),
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
                if (isset($permiso['RutaWeb']) && $permiso['RutaWeb'] === self::RUTA_WEB_LISTADO) {
                    $rutaOk = true;
                    $this->addCase($results, 'ruta_metodo_get', ($permiso['Metodo'] ?? '') === 'GET',
                        'la ruta se registro con un verbo distinto de GET');
                    $this->addCase($results, 'ruta_controlador_asignado', ($permiso['RutaController'] ?? '') === self::CONTROLADOR.'->listAll',
                        'la ruta no apunta a archivosListado->listAll');
                    break;
                }
            }
            $this->addCase($results, 'ruta_registrada', $rutaOk,
                'la ruta '.self::RUTA_WEB_LISTADO.' no esta entre los permisos del usuario');
        }

        /*====================================*/
        // La ruta debe existir en el router de F3 con el verbo GET
        /*====================================*/
        $arrRoutes = $f3->get('ROUTES');
        $handler   = $arrRoutes['/'.self::RUTA_WEB_LISTADO][0]['GET'][0] ?? '';
        $this->addCase($results, 'ruta_en_router', $handler === self::CONTROLADOR.'->listAll',
            'el router no apunta a '.self::CONTROLADOR.'->listAll');

        /************************************/
        // Retorno los datos
        return $results;

    }
    /**
     * Renderiza la vista `archivosListado-List.php` con datos sinteticos y verifica
     * el contrato que la vista entrega a `widget_fileExplorer()`:
     * BASE, rootPath, Route, ValidarTipo y levelPermission.
     *
     * Comprueba ademas la semantica de permisos del widget: con nivel 1 no deben
     * aparecer los botones de escritura, con nivel 2 si, y la columna de acciones de
     * la tabla solo con nivel 3 o superior. Con nivel vacio el widget aplica 4.
     *
     * @param \F3 $f3 Instancia global de Fat-Free Framework inyectada por el runner.
     * @return array Resultado por caso
     */
    public function vistaWidgetTest($f3){

        $results = [];

        /*====================================*/
        // Contrato de opciones declarado en la vista
        /*====================================*/
        $codigo = (string)file_get_contents($this->rutaModulo('../views/archivosListado-List.php'));
        $this->addCase($results, 'vista_invoca_widget', str_contains($codigo, 'widget_fileExplorer'), 'la vista no invoca el widget del explorador');
        $this->addCase($results, 'vista_opcion_base', str_contains($codigo, "'BASE'"), 'no envia la opcion BASE');
        $this->addCase($results, 'vista_opcion_rootpath', str_contains($codigo, "'rootPath'"), 'no envia la opcion rootPath');
        $this->addCase($results, 'vista_opcion_route', str_contains($codigo, "'Route'"), 'no envia la opcion Route');
        $this->addCase($results, 'vista_opcion_tipo', str_contains($codigo, "'ValidarTipo'"), 'no envia la opcion ValidarTipo');
        $this->addCase($results, 'vista_opcion_nivel', str_contains($codigo, "'levelPermission'"), 'no envia la opcion levelPermission');
        $this->addCase($results, 'vista_rootpath_desde_usuario', str_contains($codigo, "UserData']['MainPathUrl"),
            'rootPath no se obtiene de UserData.MainPathUrl');
        $this->addCase($results, 'vista_nivel_desde_acceso', str_contains($codigo, "UserAccess']['LevelAccess"),
            'levelPermission no se obtiene de UserAccess.LevelAccess');
        // La ruta base del widget debe llegar cifrada: nunca vacia (si no, el widget corta la peticion)
        $this->addCase($results, 'vista_route_no_vacia', !preg_match("/'Route'\s*=>\s*''/", $codigo),
            'la vista envia Route vacio, lo que hace que el widget detenga la peticion');

        /*====================================*/
        // Rutas base para el renderizado
        /*====================================*/
        $BASE     = (string)$f3->get('BASE');
        $rootPath = rtrim((string)$f3->get('BASE'), '/').'/public/upload/';
        if (trim($BASE) === '') {
            $BASE = '/admin';
        }

        /*====================================*/
        // Render real por nivel de permiso
        /*====================================*/
        $arrNiveles = [
            'nivel_1'      => 1,
            'nivel_2'      => 2,
            'nivel_3'      => 3,
            'nivel_vacio'  => '',
        ];
        foreach ($arrNiveles as $caso => $nivel) {
            $html = $this->renderizarVista($BASE, $rootPath, $nivel);

            // El widget siempre debe montar la estructura del explorador
            $this->addCase($results, $caso.'_estructura', str_contains($html, 'file-explorer')
                && str_contains($html, 'id="listView"') && str_contains($html, 'id="gridView"'),
                'el widget no genero la estructura del explorador');

            // Marcadores de escritura (botones de la barra de herramientas)
            $botones = str_contains($html, 'id="fileInput"') || str_contains($html, 'function createNewFolder');
            // Marcador de la columna de acciones de la tabla
            $acciones = str_contains($html, '<th scope="col">Acciones</th>');

            $this->addCase($results, $caso.'_botones', $botones === ($nivel === '' || $nivel >= 2),
                'los botones de escritura no coinciden con el nivel '.var_export($nivel, true));
            $this->addCase($results, $caso.'_acciones', $acciones === ($nivel === '' || $nivel >= 3),
                'la columna de acciones no coincide con el nivel '.var_export($nivel, true));
        }

        /*====================================*/
        // La ruta cifrada debe viajar en el HTML generado
        /*====================================*/
        /*====================================*/
        // La ruta base debe viajar cifrada en la peticion de listado.
        // No se puede comparar con un cifrado nuevo: el IV es aleatorio en cada
        // llamada, por lo que se comprueba que la URL NO exponga la ruta en claro.
        /*====================================*/
        $html = $this->renderizarVista($BASE, $rootPath, 4);
        $this->addCase($results, 'endpoint_listado_correcto', str_contains($html, '/core/fileExplorer/updateList/'),
            'el widget no apunta al endpoint de listado del explorador');
        $this->addCase($results, 'ruta_no_expuesta_en_claro',
            !preg_match('#updateList/'.preg_quote(self::SUBRUTA_EXPLORADOR, '#').'/#', $html),
            'la ruta base viaja sin cifrar hacia el cliente');
        $this->addCase($results, 'ruta_cifrada_concreta',
            preg_match('#updateList/[A-Za-z0-9_-]{20,}/#', $html) === 1,
            'el widget no incluye un valor cifrado en la URL de listado');

        /*====================================*/
        // Los endpoints de escritura deben viajar con el verbo POST
        /*====================================*/
        $arrEndpoints = ['createFolder', 'uploadFile', 'deleteFolder', 'deleteFile'];
        foreach ($arrEndpoints as $endpoint) {
            $this->addCase($results, 'endpoint_'.$endpoint,
                str_contains($html, '/core/fileExplorer/'.$endpoint)
                && preg_match('#/core/fileExplorer/'.$endpoint.'`?[,\s]*\n?[^{]*\{[^}]*method:\s*"POST"#s', $html) === 1,
                'el endpoint '.$endpoint.' no se invoca con el verbo POST');
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

    /*******************************************************************/
    // Renderiza la vista del modulo con datos sinteticos y devuelve el HTML
    /*******************************************************************/
    private function renderizarVista(string $BASE, string $rootPath, $nivel): string{
        $data = [
            'UserData'      => ['MainPathUrl' => $rootPath],
            'UserAccess'    => ['LevelAccess' => $nivel],
            'Fnc_WidgetsCommon' => new UIWidgetsCommon(),
        ];
        $rutaVista = $this->rutaModulo('../views/archivosListado-List.php');

        ob_start();
        try {
            include $rutaVista;
        } catch (Throwable $e) {
            ob_end_clean();
            return '';
        }
        $html = (string)ob_get_clean();

        return $html;
    }
    /******************************************************************************/
    /*                                  SEGURIDAD                                 */
    /******************************************************************************/
    /**
     * Verifica la defensa anti path-traversal del gestor de archivos sobre las
     * rutas y los nombres de carpeta que llegan desde el cliente.
     *
     * @return array Resultado por caso
     */
    public function seguridadRutasTest(){

        $results = [];

        /*====================================*/
        // Se instancia la libreria
        /*====================================*/
        try {
            $FileManager = new FileManager();
        } catch (Throwable $e) {
            $results['instancia_file_manager'] = 'FALLO: no se pudo crear FileManager ('.$e->getMessage().')';
            return $results;
        }
        $results['instancia_file_manager'] = 'OK';

        /*====================================*/
        // La ruta nunca debe conservar intentos de retroceso
        /*====================================*/
        $arrRutas = [
            'puntos_simples'    => '../../etc/passwd',
            'puntos_codificados'=> '%2e%2e%2f%2e%2e%2fetc',
            'puntos_mixtos'     => '..././secret',
            'con_espacios'      => 'carpeta con espacios/archivo',
        ];
        foreach ($arrRutas as $caso => $entrada) {
            $limpio = $FileManager->sanitizePath($entrada);
            $this->addCase($results, 'sin_retroceso_'.$caso, !str_contains($limpio, '..'),
                'la ruta limpia todavia contiene ".." ('.$limpio.')');
            $this->addCase($results, 'solo_permitidos_'.$caso,
                preg_match('/[^a-zA-Z0-9\/\-_]/', $limpio) === 0,
                'la ruta limpia contiene caracteres no permitidos ('.$limpio.')');
        }

        /*====================================*/
        // Una ruta legitima debe conservarse
        /*====================================*/
        $this->addCase($results, 'ruta_legitima_conservada',
            $FileManager->sanitizePath('documentos/2026/reporte') === 'documentos/2026/reporte',
            'una ruta sin amenazas fue alterada');

        /*====================================*/
        // El nombre de carpeta no admite rutas ni separadores
        /*====================================*/
        $this->addCase($results, 'nombre_carpeta_sin_slash',
            !str_contains($FileManager->sanitizeFolderName('carpeta/hija'), '/'),
            'el nombre de carpeta conserva una barra');
        $this->addCase($results, 'nombre_carpeta_sin_espacios',
            !str_contains($FileManager->sanitizeFolderName('mi carpeta'), ' '),
            'el nombre de carpeta conserva espacios');
        $this->addCase($results, 'nombre_carpeta_sin_puntos',
            !str_contains($FileManager->sanitizeFolderName('../../'), '.'),
            'el nombre de carpeta conserva puntos de retroceso');

        /************************************/
        // Retorno los datos
        return $results;

    }
    /**
     * Verifica las reglas de `FileManager::validateFiles()` que aplica el modulo al
     * recibir un archivo: bloqueos por extension, por nombre sensible o por nombre
     * oculto, control del MIME real, limite de peso y cortes nativos de PHP.
     *
     * Trabaja con archivos temporales fuera del repositorio: no escribe en el
     * almacenamiento del servidor.
     *
     * @return array Resultado por caso
     */
    public function validacionSubidaTest(){

        $results = [];

        /*====================================*/
        // Se instancia la libreria
        /*====================================*/
        try {
            $FileManager = new FileManager();
        } catch (Throwable $e) {
            $results['instancia_file_manager'] = 'FALLO: no se pudo crear FileManager ('.$e->getMessage().')';
            return $results;
        }
        $results['instancia_file_manager'] = 'OK';

        /*====================================*/
        // Configuracion equivalente a la del endpoint de subida del modulo
        /*====================================*/
        $arrArchivos = [[
            'Identificador'  => 'file',
            'SubCarpeta'     => self::PREFIJO_PRUEBAS,
            'NombreArchivo'  => '',
            'SufijoArchivo'  => '',
            'ValidarTipo'    => 'word,excel,powerpoint,pdf,image,txt,zip,video,music',
            'ValidarPeso'    => 10,
            'Base64'         => false,
        ]];
        $PostData = ['file' => 1];

        /*====================================*/
        // Sin reglas configuradas no hay nada que validar
        /*====================================*/
        $this->addCase($results, 'sin_reglas_aprueba',
            $FileManager->validateFiles([], [], [])['success'] === true,
            'una validacion sin reglas deberia aprobar');

        /*====================================*/
        // Bloqueos que no dependen del MIME real
        /*====================================*/
        $arrBloqueos = [
            'extension_ejecutable' => ['malicioso.php', '<?php echo 1;'],
            'archivo_htaccess'     => ['.htaccess', 'deny from all'],
            'archivo_env'          => ['.env', 'DB_PASSWORD=x'],
            'archivo_oculto'       => ['.secreto.txt', 'contenido'],
            'configuracion_app'    => ['config.php', '<?php $x=1;'],
        ];
        foreach ($arrBloqueos as $caso => $datos) {
            $SIS_FILES = $this->archivoFalso($datos[0], $datos[1]);
            $respuesta = $FileManager->validateFiles($SIS_FILES, $arrArchivos, $PostData);
            $this->addCase($results, 'bloquea_'.$caso, $respuesta['success'] === false,
                'el archivo '.$datos[0].' deberia estar bloqueado');
            $this->addCase($results, 'mensaje_'.$caso, stripos((string)$respuesta['message'], 'seguridad') !== false,
                'el mensaje no informa del bloqueo por seguridad');
        }

        /*====================================*/
        // MIME real no permitido (contenido binario en un .pdf)
        /*====================================*/
        if ($this->finfoDisponible()) {
            $SIS_FILES = $this->archivoFalso('documento.pdf', random_bytes(128));
            $respuesta = $FileManager->validateFiles($SIS_FILES, $arrArchivos, $PostData);
            $this->addCase($results, 'bloquea_mime_no_permitido', $respuesta['success'] === false,
                'un PDF con contenido binario deberia estar bloqueado');
        } else {
            $results['bloquea_mime_no_permitido'] = 'OK (omitido: la clase finfo no esta disponible)';
        }

        /*====================================*/
        // Limite de peso (10 MB segun la configuracion del endpoint)
        /*====================================*/
        $SIS_FILES = $this->archivoFalso('pesado.txt', 'contenido', 11 * 1048576);
        $respuesta = $FileManager->validateFiles($SIS_FILES, $arrArchivos, $PostData);
        $this->addCase($results, 'bloquea_peso_excedido', $respuesta['success'] === false,
            'un archivo de 11 MB deberia estar bloqueado');

        /*====================================*/
        // Corte de subida nativo de PHP
        /*====================================*/
        $SIS_FILES = $this->archivoFalso('corte.txt', 'contenido', 0, UPLOAD_ERR_INI_SIZE);
        $respuesta = $FileManager->validateFiles($SIS_FILES, $arrArchivos, $PostData);
        $this->addCase($results, 'bloquea_corte_subida', $respuesta['success'] === false,
            'un corte de subida nativo deberia estar bloqueado');

        /*====================================*/
        // Camino feliz: un archivo de texto valido
        /*====================================*/
        $SIS_FILES = $this->archivoFalso('reporte_ok.txt', 'contenido de texto plano');
        $respuesta = $FileManager->validateFiles($SIS_FILES, $arrArchivos, $PostData);
        $this->addCase($results, 'acepta_txt_valido', $respuesta['success'] === true,
            'un archivo de texto valido deberia aprobar');

        /*====================================*/
        // Limpieza de los archivos temporales usados como fixtures
        /*====================================*/
        $this->addCase($results, 'limpia_temporales', $this->limpiarTemporales(),
            'quedaron archivos temporales sin eliminar');

        /************************************/
        // Retorno los datos
        return $results;

    }
    /******************************************************************************/
    /*                     GESTION DE CARPETAS Y EXPLORADOR                       */
    /******************************************************************************/
    /**
     * Verifica las operaciones de carpeta que usa el widget del modulo:
     * validacion de parametros, proteccion de la carpeta raiz, ciclo completo
     * crear/listar/eliminar y listado del explorador con la ruta cifrada.
     *
     * Solo crea y borra carpetas propias de la prueba (prefijo aleatorio) dentro de
     * la subruta del explorador, y siempre las elimina al terminar.
     *
     * @return array Resultado por caso
     */
    public function exploradorCarpetasTest(){

        $results = [];

        /*====================================*/
        // Se instancia la libreria
        /*====================================*/
        try {
            $FileManager = new FileManager();
        } catch (Throwable $e) {
            $results['instancia_file_manager'] = 'FALLO: no se pudo crear FileManager ('.$e->getMessage().')';
            return $results;
        }
        $results['instancia_file_manager'] = 'OK';

        /*====================================*/
        // Validacion de parametros de entrada
        /*====================================*/
        $respuesta = $FileManager->createFolder([]);
        $this->addCase($results, 'crear_sin_nombre_rechazado',
            $respuesta['success'] === false && $respuesta['message'] === 'Nombre no definido',
            'crear carpeta sin nombre deberia rechazarse');

        $respuesta = $FileManager->deleteFolder([]);
        $this->addCase($results, 'borrar_sin_nombre_rechazado',
            $respuesta['success'] === false && $respuesta['message'] === 'Nombre de carpeta no definido',
            'borrar carpeta sin nombre deberia rechazarse');

        /*====================================*/
        // Proteccion de la carpeta raiz: un nombre que se sanitiza a vacio y sin
        // subruta controlada no debe llegar al almacenamiento
        /*====================================*/
        $arrNombresVacios = ['puntos' => '..', 'puntos_oscuros' => '...', 'simbolos' => '!!!'];
        foreach ($arrNombresVacios as $caso => $nombre) {
            $respuesta = $FileManager->deleteFolder(['name' => $nombre]);
            $this->addCase($results, 'protege_raiz_'.$caso,
                $respuesta['success'] === false && $respuesta['message'] === 'No se permite eliminar la carpeta raíz',
                'una entrada que colapsa a vacio no fue bloqueada');
        }

        /*====================================*/
        // Ciclo completo sobre una carpeta propia de la prueba
        /*====================================*/
        $nombre = '';
        try {
            $nombre = $this->nuevaCarpetaPrueba($FileManager);
            $base   = rtrim((string)ConfigAPP::APP['uploadFolder'], '/');
            $ruta   = $base.'/'.self::SUBRUTA_EXPLORADOR.'/'.$nombre;

            $this->addCase($results, 'crea_carpeta', is_dir($ruta), 'la carpeta de prueba no se creo en disco');

            /*====================================*/
            // No se puede repetir el nombre
            /*====================================*/
            $respuesta = $FileManager->createFolder(['SubRoute' => self::SUBRUTA_EXPLORADOR, 'name' => $nombre]);
            $this->addCase($results, 'rechaza_carpeta_duplicada',
                $respuesta['success'] === false && $respuesta['message'] === 'La carpeta ya existe',
                'una carpeta con el mismo nombre deberia rechazarse');

            /*====================================*/
            // El explorador debe listar la carpeta recien creada
            /*====================================*/
            $Cifrado = (new FunctionsSecurityCodification())->encryptDecrypt('encrypt', self::SUBRUTA_EXPLORADOR);
            $listado = $FileManager->fileExplorer(['route' => $Cifrado['data'], 'tipos' => 'all', 'path' => 'asdqwe']);
            $this->addCase($results, 'explorador_devuelve_arreglo', is_array($listado), 'el explorador no devolvio un arreglo');
            $this->addCase($results, 'explorador_muestra_carpeta',
                is_array($listado) && in_array($nombre, array_column($listado, 'name'), true),
                'la carpeta creada no aparece en el listado del explorador');

            /*====================================*/
            // Una ruta cifrada invalida no debe romper el listado
            /*====================================*/
            $listado = $FileManager->fileExplorer(['route' => 'ruta-invalida', 'tipos' => 'all', 'path' => 'asdqwe']);
            $this->addCase($results, 'explorador_ruta_invalida', is_array($listado), 'una ruta invalida rompio el listado');

        } catch (Throwable $e) {
            $this->addCase($results, 'ciclo_de_carpetas', false, 'el ciclo de pruebas lanzo '.$e->getMessage());
        } finally {
            /*====================================*/
            // Limpieza garantizada de la carpeta propia
            /*====================================*/
            if ($nombre !== '') {
                $this->limpiarCarpetaPrueba($FileManager, $nombre);
                $base = rtrim((string)ConfigAPP::APP['uploadFolder'], '/');
                $this->addCase($results, 'limpia_carpeta_prueba',
                    !is_dir($base.'/'.self::SUBRUTA_EXPLORADOR.'/'.$nombre),
                    'la carpeta de prueba quedo en el almacenamiento');
            }
        }

        /************************************/
        // Retorno los datos
        return $results;

    }
/**
     * Verifica la autorizacion por ambito (scope) de los endpoints del explorador.
     *
     * El nivel de acceso se OTORGA en el modulo que implementa la pantalla
     * (`ScopeAccess::grant()`) y se VALIDA en el endpoint generico
     * (`ScopeAccess::check()`). Estas pruebas cubren el criterio de decision:
     * normalizacion del ambito, fail-closed sin concesion, umbrales por nivel,
     * aislamiento entre ambitos, atado al usuario, caducidad y revocacion.
     *
     * Usa un doble de F3 con get()/set() porque ScopeAccess solo interactua
     * con la clave de SESSION `arrScopeAccess`.
     *
     * @return array Resultado por caso
     */
    public function autorizacionScopeTest(){

        $results = [];

        /*====================================*/
        // Objeto F3 simulado (ScopeAccess solo usa get/set sobre SESSION)
        /*====================================*/
        $f3 = new class {
            public array $memoria = [];
            public function get($clave){ return $this->memoria[$clave] ?? null; }
            public function set($clave, $valor){ $this->memoria[$clave] = $valor; }
            public function clear($clave){ unset($this->memoria[$clave]); }
        };

        $f3->set('SESSION.DataInfo.UserID', 7);

        /*====================================*/
        // Normalizacion del ambito
        /*====================================*/
        $this->addCase($results, 'scope_valido_se_normaliza',
            ScopeAccess::normalizeScope('archivosListado') === 'archivosListado',
            'un ambito alfanumerico deberia conservarse');
        $this->addCase($results, 'scope_con_basura_se_normaliza',
            ScopeAccess::normalizeScope('archivos Listado!') === 'archivosListado',
            'un ambito con caracteres no permitidos deberia filtrarse');
        $this->addCase($results, 'scope_vacio_rechazado',
            ScopeAccess::normalizeScope('') === '' && ScopeAccess::normalizeScope(null) === '',
            'un ambito vacio o nulo deberia quedar en cadena vacia');
        $this->addCase($results, 'scope_array_rechazado',
            ScopeAccess::normalizeScope(['a','b']) === '',
            'un ambito que no es escalar deberia rechazarse');

        /*====================================*/
        // Sin concesion previa todo se deniega (fail-closed)
        /*====================================*/
        $this->addCase($results, 'sin_concesion_se_deniega',
            ScopeAccess::check($f3, 'archivosListado', 1) === false,
            'sin concesion previa no deberia autorizar ninguna operacion');

        /*====================================*/
        // Umbrales por nivel: 2 crea pero no borra, 3 si borra
        /*====================================*/
        ScopeAccess::grant($f3, 'archivosListado', 2);
        $this->addCase($results, 'nivel2_puede_ver',
            ScopeAccess::check($f3, 'archivosListado', 1) === true,
            'nivel 2 deberia poder listar');
        $this->addCase($results, 'nivel2_puede_crear',
            ScopeAccess::check($f3, 'archivosListado', 2) === true,
            'nivel 2 deberia poder crear carpetas');
        $this->addCase($results, 'nivel2_no_puede_borrar',
            ScopeAccess::check($f3, 'archivosListado', 3) === false,
            'nivel 2 no deberia poder borrar');

        ScopeAccess::grant($f3, 'archivosListado', 3);
        $this->addCase($results, 'nivel3_puede_borrar',
            ScopeAccess::check($f3, 'archivosListado', 3) === true,
            'nivel 3 deberia poder borrar');

        /*====================================*/
        // Aislamiento: la concesion de un ambito no habilita otro
        /*====================================*/
        $this->addCase($results, 'ambito_ajeno_se_deniega',
            ScopeAccess::check($f3, 'coreWidgets', 1) === false,
            'un ambito sin concesion propia no deberia autorizar');
        $this->addCase($results, 'ambito_con_sufijo_se_deniega',
            ScopeAccess::check($f3, 'archivosListado OTRO', 1) === false,
            'un ambito con sufijo no deberia resolver al ambito concedido');

        /*====================================*/
        // Nivel 0 (usuario sin permiso) nunca autoriza
        /*====================================*/
        ScopeAccess::grant($f3, 'archivosListado', 0);
        $this->addCase($results, 'nivel0_se_deniega',
            ScopeAccess::check($f3, 'archivosListado', 1) === false,
            'nivel 0 no deberia autorizar ni siquiera la lectura');

        /*====================================*/
        // La concesion se ata al usuario de sesion (no se puede reutilizar)
        /*====================================*/
        ScopeAccess::grant($f3, 'archivosListado', 4);
        $f3->set('SESSION.DataInfo.UserID', 9);
        $this->addCase($results, 'concesion_otro_usuario_se_deniega',
            ScopeAccess::check($f3, 'archivosListado', 1) === false,
            'la concesion deberia estar atada al usuario que la recibio');
        $f3->set('SESSION.DataInfo.UserID', 7);

        /*====================================*/
        // Caducidad por tiempo
        /*====================================*/
        ScopeAccess::grant($f3, 'archivosListado', 4, 1);
        $grants = $f3->get('SESSION.'.ScopeAccess::SESSION_KEY);
        $grants['archivosListado']['Exp'] = time() - 1;
        $f3->set('SESSION.'.ScopeAccess::SESSION_KEY, $grants);
        $this->addCase($results, 'concesion_vencida_se_deniega',
            ScopeAccess::check($f3, 'archivosListado', 1) === false,
            'una concesion vencida no deberia autorizar');

        /*====================================*/
        // Revocacion explicita
        /*====================================*/
        ScopeAccess::grant($f3, 'archivosListado', 4);
        ScopeAccess::revoke($f3, 'archivosListado');
        $this->addCase($results, 'concesion_revocada_se_deniega',
            ScopeAccess::check($f3, 'archivosListado', 1) === false,
            'una concesion revocada no deberia autorizar');

        /************************************/
        // Retorno los datos
        return $results;

    }

    /**
     * Prueba de regresion del borrado de carpetas con nombre que se sanitiza a vacio.
     *
     * `FileManager::deleteFolder()` bloquea la carpeta raiz cuando la ruta completa
     * queda vacia, pero si el cliente envia una subruta controlada junto a un nombre
     * que `sanitizeFolderName()` reduce a cadena vacia (por ejemplo ".."), la ruta
     * relativa no queda vacia y la proteccion no se dispara: se borra la subruta
     * completa con todo su contenido.
     *
     * La prueba se ejecuta SIEMPRE contra una carpeta propia con nombre aleatorio,
     * nunca contra una subruta real del explorador.
     *
     * @return array Resultado por caso
     */
    public function borradoSubrutaInesperadoTest(){

        $results = [];

        /*====================================*/
        // Se instancia la libreria
        /*====================================*/
        try {
            $FileManager = new FileManager();
        } catch (Throwable $e) {
            $results['instancia_file_manager'] = 'FALLO: no se pudo crear FileManager ('.$e->getMessage().')';
            return $results;
        }
        $results['instancia_file_manager'] = 'OK';

        /*====================================*/
        // Se prepara una subruta propia, aislada y con nombre aleatorio
        /*====================================*/
        $base   = rtrim((string)ConfigAPP::APP['uploadFolder'], '/');
        $hoja   = self::PREFIJO_PRUEBAS.uniqid();
        $sub    = $hoja.'/hoja';                  // Subruta en profundidad
        $ruta   = $base.'/'.self::SUBRUTA_EXPLORADOR.'/'.$sub;

        try {
            $creada = $FileManager->createFolder(['SubRoute' => self::SUBRUTA_EXPLORADOR, 'path' => $hoja, 'name' => 'hoja']);
            $this->addCase($results, 'prepara_subruta', ($creada['success'] === true) && is_dir($ruta),
                'no se pudo preparar la subruta de la prueba: '.$creada['message']);

            /*====================================*/
            // Se escribe un archivo marcador: sirve para comprobar que el
            // contenido de la subruta sobrevive a todos los intentos de ataque
            /*====================================*/
            $marcador = $ruta.'/marcador.txt';
            file_put_contents($marcador, 'contenido que no debe perderse');

            /*====================================*/
            // Nombres que se sanitizan a vacio junto a una subruta controlada.
            // Se usa el mismo esquema que envia el widget: SubRoute + path + name.
            // Cada variante debe rechazarse Y dejar intacta la subruta.
            /*====================================*/
            $arrNombresVacios = [
                'puntos'         => '..',
                'punto'          => '.',
                'puntos_oscuros' => '...',
                'simbolos'       => '!!!',
                'mixto'          => '../.',
                'barra'          => '/',
                'codificado'     => '%2e%2e',
                'no_latin'       => 'ñ€',
            ];
            foreach ($arrNombresVacios as $caso => $nombre) {
                $respuesta = $FileManager->deleteFolder([
                    'SubRoute' => self::SUBRUTA_EXPLORADOR,
                    'path'     => $sub,
                    'name'     => $nombre,
                ]);
                $this->addCase($results, 'rechaza_borrado_'.$caso,
                    $respuesta['success'] === false && is_dir($ruta),
                    'el nombre "'.$nombre.'" no fue bloqueado: elimino la subruta '.$sub);
            }

            /*====================================*/
            // El mensaje debe explicar la causa real (nombre invalido) y no
            // reportar falsamente que se intento borrar la raiz
            /*====================================*/
            $respuesta = $FileManager->deleteFolder([
                'SubRoute' => self::SUBRUTA_EXPLORADOR,
                'path'     => $sub,
                'name'     => '..',
            ]);
            $this->addCase($results, 'mensaje_nombre_invalido',
                $respuesta['success'] === false && $respuesta['message'] === 'Nombre de carpeta inválido',
                'el mensaje reportado fue: '.$respuesta['message']);

            /*====================================*/
            // La carpeta y su contenido deben seguir existiendo
            /*====================================*/
            $this->addCase($results, 'conserva_contenido_subruta', is_dir($ruta),
                'la carpeta de la prueba desaparecio tras un borrado con nombre vacio');
            $this->addCase($results, 'conserva_archivo_marcador', is_file($marcador),
                'el archivo marcador de la subruta desaparecio');

            /*====================================*/
            // createFolder() sufre el mismo patron: un nombre que colapsa a
            // vacio no debe crear ni reportar la SubRoute/path como carpeta
            /*====================================*/
            foreach ($arrNombresVacios as $caso => $nombre) {
                $respuesta = $FileManager->createFolder([
                    'SubRoute' => self::SUBRUTA_EXPLORADOR,
                    'path'     => $hoja,
                    'name'     => $nombre,
                ]);
                $this->addCase($results, 'crear_rechaza_'.$caso,
                    $respuesta['success'] === false && $respuesta['message'] === 'Nombre de carpeta inválido',
                    'createFolder acepto el nombre "'.$nombre.'": '.$respuesta['message']);
            }

            /*====================================*/
            // Defensa en profundidad: el driver no borra la base con ruta vacia
            /*====================================*/
            $respuesta = $FileManager->getStorageDriver()->deleteDirectory('');
            $this->addCase($results, 'driver_bloquea_ruta_vacia',
                $respuesta['success'] === false && $respuesta['message'] === 'No se permite eliminar la carpeta raíz',
                'el driver acepto una ruta vacia: '.$respuesta['message']);

            $baseUpload = rtrim((string)ConfigAPP::APP['uploadFolder'], '/');
            $this->addCase($results, 'conserva_carpeta_base_upload', is_dir($baseUpload),
                'la carpeta base del almacenamiento desaparecio');
        } catch (Throwable $e) {
            $this->addCase($results, 'no_borra_subruta_con_nombre_vacio', false,
                'la prueba lanzo '.$e->getMessage());
        } finally {
            /*====================================*/
            // Limpieza garantizada: hoja y luego la carpeta raiz de la prueba
            /*====================================*/
            if (is_dir($ruta)) {
                $FileManager->deleteFolder(['SubRoute' => self::SUBRUTA_EXPLORADOR, 'path' => $hoja, 'name' => 'hoja']);
            }
            if (is_dir($base.'/'.self::SUBRUTA_EXPLORADOR.'/'.$hoja)) {
                $FileManager->deleteFolder(['SubRoute' => self::SUBRUTA_EXPLORADOR, 'name' => $hoja]);
            }
            $this->addCase($results, 'limpia_subruta_prueba',
                !is_dir($ruta) && !is_dir($base.'/'.self::SUBRUTA_EXPLORADOR.'/'.$hoja),
                'la subruta de la prueba quedo en el almacenamiento');
        }

        /************************************/
        // Retorno los datos
        return $results;

    }

}
