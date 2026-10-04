<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class sistemaTesteos extends ControllerBase {

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
        $this->controllerName  = 'Empty';
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /*******************************************************************/
    // Resumen
    /*******************************************************************/
    public function Resumen($f3){
        /************************************/
        // Variable vacia
        $arrModules = [];

        //Se detectan los modulos instalados y sus pruebas disponibles
        $arrModules = $this->getInstalledModules();

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if(is_array($arrModules)){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'        => 'Sistema Testeos',
                'PageDescription'  => 'Sistema de Testeos de los Modulos instalados.',
                'PageAuthor'       => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'     => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'       => 'Sistema Testeos',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*=========== Datos Consultados ===========*/
                'arrModules' => $arrModules,
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/sistemaTesteos-Resumen.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrModules]);
            // Despliegue de errores
            $this->showError(1, $f3, $result);
        }
    }

    /******************************************************************************/
    /*                                  DATOS                                     */
    /******************************************************************************/
    /*******************************************************************/
    // Ejecucion individual de una prueba
    /*******************************************************************/
    public function executeTest($f3){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Obtener datos (el servidor determina la prueba a ejecutar)
        $Module = isset($_POST['Module']) ? trim((string)$_POST['Module']) : '';
        $Test   = isset($_POST['Test'])   ? trim((string)$_POST['Test'])   : '';

        /************************************/
        // Se validan los parametros recibidos
        if($Module==='' || $Test===''){
            Response::error('Parametros invalidos', 400, ['Module'=>$Module, 'Test'=>$Test]);
        }

        /************************************/
        // Se detectan unicamente los modulos INSTALADOS
        $arrModules = $this->getInstalledModules();

        /************************************/
        // Se localiza el modulo indicado
        $ModuleData = null;
        foreach ($arrModules as $module) {
            if($module['Module']===$Module){
                $ModuleData = $module;
                break;
            }
        }

        /************************************/
        // Se valida que el modulo exista y este instalado
        if($ModuleData===null){
            Response::error('El modulo no existe o no esta instalado', 404, ['Module'=>$Module]);
        }

        /************************************/
        // Se localiza la prueba dentro del modulo
        $TestData = null;
        foreach ($ModuleData['Tests'] as $test) {
            if($test['Test']===$Test){
                $TestData = $test;
                break;
            }
        }

        /************************************/
        // Se valida que la prueba exista y pertenezca al modulo
        if($TestData===null){
            Response::error('La prueba no existe en el modulo indicado', 404, ['Module'=>$Module, 'Test'=>$Test]);
        }

        /************************************/
        // Se ejecuta UNICAMENTE la prueba solicitada
        $result = $this->runTest($f3, $TestData['Testing'], $TestData['Test']);

        /************************************/
        // Se compone la respuesta
        $data = [
            'Module'      => $ModuleData['Module'],
            'ModuleName'  => $ModuleData['Nombre'],
            'Test'        => $TestData['Test'],
            'TestName'    => $TestData['Nombre'],
            'TestId'      => $TestData['Id'],
            'Metodo'      => $TestData['Metodo'],
            'Testing'     => $TestData['Testing'],
            'Estado'      => $result['Estado'],
            'Mensaje'     => $result['Mensaje'],
            'Resultado'   => $result['Resultado'],
            'Info'        => $result['Info'],
            // Token de sesion ACTUAL (csrfTest rota el token; se sincroniza en cliente)
            'Token'       => CsrfToken::getToken($f3),
        ];

        /************************************/
        // Devuelvo la respuesta con codigo 200 (OK)
        Response::success($result['Mensaje'], $data);
    }

    /*******************************************************************/
    // Ejecucion de TODAS las pruebas de un modulo
    /*******************************************************************/
    public function executeAllTests($f3){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Se solicita unicamente el modulo (la lista de pruebas la descubre el servidor)
        $Module = isset($_POST['Module']) ? trim((string)$_POST['Module']) : '';

        /************************************/
        // Se validan los parametros recibidos
        if($Module===''){
            Response::error('Parametros invalidos', 400, ['Module'=>$Module]);
        }

        /************************************/
        // Se detectan unicamente los modulos INSTALADOS
        $arrModules = $this->getInstalledModules();

        /************************************/
        // Se localiza el modulo indicado
        $ModuleData = null;
        foreach ($arrModules as $module) {
            if($module['Module']===$Module){
                $ModuleData = $module;
                break;
            }
        }

        /************************************/
        // Se valida que el modulo exista y este instalado
        if($ModuleData===null){
            Response::error('El modulo no existe o no esta instalado', 404, ['Module'=>$Module]);
        }

        /************************************/
        // Se valida que el modulo tenga pruebas disponibles
        if(empty($ModuleData['Tests'])){
            Response::error('El modulo no tiene pruebas para ejecutar', 400, ['Module'=>$Module]);
        }

        /************************************/
        // Control de tiempo general de la suite
        $timeSuite = microtime(true);

        /************************************/
        // Contadores del resumen
        $total      = 0;
        $totalOk    = 0;
        $totalError = 0;
        $totalWarn  = 0;

        /************************************/
        // Resultado de cada prueba
        $arrTests = [];

        /************************************/
        // Se ejecutan TODAS las pruebas del modulo (un fallo no detiene la suite)
        foreach ($ModuleData['Tests'] as $test) {
            /************************************/
            // Ejecucion individual (ya normaliza EXITOSA/ERROR/ADVERTENCIA y captura excepciones)
            $result = $this->runTest($f3, $test['Testing'], $test['Test']);

            /************************************/
            // Se acumula el resumen
            $total++;
            if($result['Estado']==='ERROR'){
                $totalError++;
            } elseif($result['Estado']==='ADVERTENCIA'){
                $totalWarn++;
            } else {
                $totalOk++;
            }

            /************************************/
            // Se agrega el resultado de la prueba
            $arrTests[] = [
                'Test'        => $test['Test'],
                'TestName'    => $test['Nombre'],
                'TestId'      => $test['Id'],
                'Metodo'      => $test['Metodo'],
                'Testing'     => $test['Testing'],
                'Estado'      => $result['Estado'],
                'Mensaje'     => $result['Mensaje'],
                'Resultado'   => $result['Resultado'],
                'Info'        => $result['Info'],
            ];
        }

        /************************************/
        // Tiempo total de la suite (ms)
        $tiempoTotal = round((microtime(true) - $timeSuite) * 1000, 2);

        /************************************/
        // Mensaje general segun el resultado de la suite
        if($totalError>0){
            $mensaje = 'La suite finalizo con '.$totalError.' prueba(s) fallida(s) de '.$total;
        } elseif($totalWarn>0){
            $mensaje = 'La suite finalizo con '.$totalWarn.' advertencia(s) de '.$total;
        } else {
            $mensaje = 'La suite finalizo correctamente ('.$totalOk.' de '.$total.' pruebas)';
        }

        /************************************/
        // Se compone la respuesta
        $data = [
            'Module'          => $ModuleData['Module'],
            'ModuleName'      => $ModuleData['Nombre'],
            'Total'           => $total,
            'TotalOk'         => $totalOk,
            'TotalError'      => $totalError,
            'TotalAdvertencia' => $totalWarn,
            'TiempoTotal'     => $tiempoTotal,
            'Tests'           => $arrTests,
            // Token de sesion ACTUAL (csrfTest rota el token; se sincroniza en cliente)
            'Token'           => CsrfToken::getToken($f3),
        ];

        /************************************/
        // Devuelvo la respuesta con codigo 200 (OK)
        Response::success($mensaje, $data);
    }

    /******************************************************************************/
    /*                        DETECCION DE MODULOS                                */
    /******************************************************************************/
    /*******************************************************************/
    // Retorna unicamente los modulos INSTALADOS junto a sus pruebas
    /*******************************************************************/
    private function getInstalledModules(){

        /************************************/
        // Variable vacia
        $arrModules = [];

        //Arreglo con los controladores a instalar (mecanismo existente)
        $sistemaInstalacion = new sistemaInstalacion();
        $array              = $sistemaInstalacion->arrayModInstall();

        /************************************/
        // Verifico si existe
        if($array){
            // Recorro los datos
            foreach ($array as $installerClass) {
                /************************************/
                // Se valida que exista el metodo de datos
                if(method_exists($installerClass, 'ListDataModule')===false){
                    continue;
                }

                /************************************/
                // Se instancian los datos
                $ControllerData = new $installerClass;
                $ModuleData     = $ControllerData->ListDataModule();

                /************************************/
                // Omito por completo los modulos NO instalados
                if(!isset($ModuleData['countPermisos']) || (int)$ModuleData['countPermisos'] !== 1){
                    continue;
                }

                /************************************/
                // Se obtiene la carpeta del modulo
                $folder = $this->getModuleFolder($installerClass);
                if($folder==='' || $folder==='installer'){
                    continue;
                }

                /************************************/
                // Se descubren las pruebas del modulo
                $arrTests = $this->discoverTests($folder);

                /************************************/
                // Se compone el dato
                $arrModules[] = [
                    'Module'      => $folder,
                    'Nombre'      => $ModuleData['Nombre']      ?? $folder,
                    'Descripcion' => $ModuleData['Descripcion'] ?? '',
                    'Controller'  => $ModuleData['Controller']  ?? '',
                    'Tests'       => $arrTests,
                ];
            }
        }

        /************************************/
        // Ordeno por nombre de modulo
        usort($arrModules, function($a, $b){
            return strcasecmp($a['Nombre'], $b['Nombre']);
        });

        /************************************/
        // Retorno los datos
        return $arrModules;
    }

    /*******************************************************************/
    // Carpeta del modulo que contiene un instalador determinado
    /*******************************************************************/
    private function getModuleFolder($installerClass){

        /************************************/
        // Carpeta raiz de los modulos
        $modulesPath = __DIR__ . '/../../';

        /************************************/
        // Se busca el archivo del instalador
        $files = glob($modulesPath.'*/installer/'.$installerClass.'.php');

        /************************************/
        // Verifico si existe
        if($files && count($files)>0){
            // Carpeta del modulo (modules/<folder>/installer/archivo.php)
            return basename(dirname(dirname($files[0])));
        }

        /************************************/
        // Sin resultado
        return '';
    }

    /*******************************************************************/
    // Verifica que un archivo declare exactamente la clase esperada
    /*******************************************************************/
    private function fileDeclaresClass($file, $className){

        /************************************/
        // Se lee el contenido del archivo
        $content = @file_get_contents($file);

        /************************************/
        // Si no se pudo leer se asume invalido
        if($content===false){
            return false;
        }

        /************************************/
        // Se busca la declaracion "class <Clase>"
        return preg_match('/\bclass\s+'.preg_quote($className, '/').'\b/', $content) === 1;
    }

    /******************************************************************************/
    /*                        DESCUBRIMIENTO DE PRUEBAS                           */
    /******************************************************************************/
    /*******************************************************************/
    // Descubre automaticamente las pruebas de un modulo (convencion testing/)
    /*******************************************************************/
    private function discoverTests($folder){

        /************************************/
        // Variable vacia
        $arrTests = [];

        /************************************/
        // Carpeta raiz de los modulos
        $modulesPath = __DIR__ . '/../../';

        /************************************/
        // Se escanean los archivos *Testing.php del modulo
        $files = glob($modulesPath.$folder.'/testing/*Testing.php');

        /************************************/
        // Recorro los archivos encontrados
        foreach ($files as $file) {
            /************************************/
            // Nombre de la clase (igual al nombre del archivo)
            $testingClass = basename($file, '.php');

            /************************************/
            // Se valida que el archivo declare exactamente la clase esperada.
            // Evita un error fatal de re-declaracion si un archivo no cumple la
            // convencion (clase = nombre de archivo), de modo que el sistema no se detiene.
            if($this->fileDeclaresClass($file, $testingClass)===false){
                continue;
            }

            /************************************/
            // Se valida que la clase exista (autoload)
            if(!class_exists($testingClass)){
                continue;
            }

            /************************************/
            // Se refleccionan los metodos publicos
            try {
                $reflector = new ReflectionClass($testingClass);
                $methods   = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);
            } catch (Throwable $e) {
                continue;
            }

            /************************************/
            // Recorro los metodos
            foreach ($methods as $method) {
                /************************************/
                // Se excluyen constructores y metodos magicos
                if($method->isConstructor() || $method->isDestructor() || strpos($method->getName(), '__') === 0){
                    continue;
                }

                /************************************/
                // Convencion: toda prueba es un metodo publico que termina en "Test"
                // (executeTest, csrfTest, ...). Nuevas pruebas se descubren solas.
                if(preg_match('/Test$/i', $method->getName())!==1){
                    continue;
                }

                /************************************/
                // Nombre legible de la prueba
                $nombre = $this->describeMethod($method);
                $doc    = $this->describeDoc($method);

                /************************************/
                // Se agrega el descriptor
                $arrTests[] = [
                    'Id'          => $folder.'::'.$method->getName(),
                    'Test'        => $method->getName(),
                    'Nombre'      => $nombre,
                    'Descripcion' => ($doc!=='') ? $doc : $nombre,
                    'Modulo'      => $folder,
                    'Testing'     => $testingClass,
                    'Metodo'      => $testingClass.'::'.$method->getName(),
                    'Estado'      => 'Disponible',
                ];
            }
        }

        /************************************/
        // Ordeno las pruebas por nombre
        usort($arrTests, function($a, $b){
            return strcasecmp($a['Nombre'], $b['Nombre']);
        });

        /************************************/
        // Retorno los datos
        return $arrTests;
    }

    /*******************************************************************/
    // Nombre legible de un metodo (camelCase a texto)
    /*******************************************************************/
    private function describeMethod(ReflectionMethod $method){

        /************************************/
        // Nombre del metodo
        $name = $method->getName();

        /************************************/
        // Separo camelCase en palabras
        $words = preg_split('/(?<=[a-z0-9])(?=[A-Z])/', $name);
        $name  = ($words && count($words)>0) ? implode(' ', $words) : $name;

        /************************************/
        // Retorno con la primera letra en mayuscula
        return ucfirst($name);
    }

    /*******************************************************************/
    // Descripcion de un metodo desde su docblock (opcional)
    /*******************************************************************/
    private function describeDoc(ReflectionMethod $method){

        /************************************/
        // Se obtiene el comentario de documentacion
        $doc = $method->getDocComment();

        /************************************/
        // Si no existe documento se retorna vacio
        if($doc===false || $doc===''){
            return '';
        }

        /************************************/
        // Limpio los delimitadores del docblock
        $doc   = str_replace(['/**', '*/'], '', $doc);
        $lines = preg_split('/\r\n|\r|\n/', $doc);

        /************************************/
        // Busco la primera linea con contenido
        foreach ($lines as $line) {
            $line = trim($line, " \t*");
            if($line!==''){
                return $line;
            }
        }

        /************************************/
        // Sin contenido
        return '';
    }

    /******************************************************************************/
    /*                         EJECUCION DE PRUEBAS                               */
    /******************************************************************************/
    /*******************************************************************/
    // Ejecuta una unica prueba y normaliza su respuesta
    /*******************************************************************/
    private function runTest($f3, $testingClass, $method){

        /************************************/
        // Variable inicial
        $result = [
            'Estado'    => 'ERROR',
            'Mensaje'   => 'No se pudo ejecutar la prueba',
            'Resultado' => '',
            'Info'      => '',
        ];

        /************************************/
        // Se valida que la clase y el metodo existan
        if(!class_exists($testingClass) || !method_exists($testingClass, $method)){
            $result['Mensaje'] = 'La prueba no esta disponible';
            $result['Info']    = $testingClass.'::'.$method;
            return $result;
        }

        /************************************/
        // Control de tiempo
        $start = microtime(true);

        /************************************/
        // Se ejecuta la prueba capturando cualquier error
        // AISLAMIENTO: durante la ejecucion se sustituye temporalmente el manejador
        // de errores de Fat-Free Framework (que convierte cualquier warning en un
        // HTTP 500 + die()) por uno que solo registra el aviso. Asi una prueba que
        // genere un warning no aborta la suite completa, y el aviso queda asociado
        // a esa prueba en el modal.
        set_error_handler(function($errno, $errstr, $errfile = '', $errline = 0){
            $GLOBALS['TESTEOS_AVISOS'][] = $errstr.' ('.basename($errfile).':'.$errline.')';
            return true;
        });
        $GLOBALS['TESTEOS_AVISOS'] = [];

        try {
            /************************************/
            // Se instancia la clase de pruebas
            $instance = new $testingClass;

            /************************************/
            // Se determina si el metodo requiere la instancia de F3 (ej. csrfTest($f3))
            $reflector = new ReflectionMethod($testingClass, $method);
            $args      = [];
            if($reflector->getNumberOfParameters() > 0){
                $args[] = $f3;
            }

            /************************************/
            // Ejecucion unica de la prueba
            $data = $reflector->invokeArgs($instance, $args);

            /************************************/
            // Se normaliza la respuesta
            $result = $this->normalizeResult($data);

        /************************************/
        // Excepciones y errores controlados
        } catch (Throwable $e) {
            $result['Estado']    = 'ERROR';
            $result['Mensaje']   = 'Excepcion durante la ejecucion: '.$e->getMessage();
            $result['Resultado'] = get_class($e);
            $result['Info']      = $e->getFile().':'.$e->getLine();

        /************************************/
        // Se restaura SIEMPRE el manejador de errores de Fat-Free Framework
        } finally {
            restore_error_handler();
        }

        /************************************/
        // Se adjuntan los avisos (warnings) generados durante la prueba
        if(!empty($GLOBALS['TESTEOS_AVISOS'])){
            $result['Info'] = trim($result['Info'].' | Avisos: '.implode(' | ', array_slice($GLOBALS['TESTEOS_AVISOS'], 0, 5)));
        }
        $GLOBALS['TESTEOS_AVISOS'] = [];

        /************************************/
        // Se agrega el tiempo de ejecucion
        $time = round((microtime(true) - $start) * 1000, 2);
        $result['Info'] = trim($result['Info'].' | Tiempo: '.$time.' ms');

        /************************************/
        // Retorno los datos
        return $result;
    }

    /*******************************************************************/
    // Normaliza el resultado de una prueba a Estado/Mensaje/Resultado
    /*******************************************************************/
    private function normalizeResult($data){

        /************************************/
        // Variable inicial
        $result = [
            'Estado'    => 'EXITOSA',
            'Mensaje'   => 'Prueba ejecutada correctamente',
            'Resultado' => '',
            'Info'      => '',
        ];

        /************************************/
        // Segun el tipo de respuesta
        if(is_bool($data)){
            /************************************/
            // executeTest() devuelve true / false
            $result['Resultado'] = $data ? 'true' : 'false';
            if($data===false){
                $result['Estado']  = 'ERROR';
                $result['Mensaje'] = 'La prueba finalizo con false';
            }

        /************************************/
        } elseif($data === null){
            /************************************/
            // Sin respuesta
            $result['Estado']    = 'ADVERTENCIA';
            $result['Mensaje']   = 'La prueba no devolvio resultado';
            $result['Resultado'] = 'null';

        /************************************/
        } elseif(is_array($data)){
            /************************************/
            // Arreglo de casos (ej. csrfTest): [caso => 'OK'|'FALLO: ...']
            $result['Resultado'] = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $hasFail  = false;
            $hasOk    = false;
            $arrFails = [];
            foreach ($data as $key => $value) {
                $text = is_scalar($value) ? (string)$value : '';
                if(preg_match('/FALLO|ERROR|FAIL/i', $text)===1){
                    $hasFail   = true;
                    $arrFails[] = $key.': '.$text;
                } elseif(preg_match('/OK|EXITO|PASS/i', $text)===1){
                    $hasOk = true;
                }
            }
            if($hasFail){
                $result['Estado']    = 'ERROR';
                $result['Mensaje']   = 'La prueba tiene casos fallidos';
                $result['Info']      = implode(' | ', $arrFails);
            } elseif($hasOk){
                $result['Estado']    = 'EXITOSA';
                $result['Mensaje']   = 'Todos los casos pasaron correctamente';
            } elseif(empty($data)){
                $result['Estado']    = 'ADVERTENCIA';
                $result['Mensaje']   = 'La prueba no devolvio casos';
            }

        /************************************/
        } elseif(is_string($data)){
            /************************************/
            // Cadena
            $result['Resultado'] = $data;
            if($data===''){
                $result['Estado']    = 'ADVERTENCIA';
                $result['Mensaje']   = 'La prueba devolvio una cadena vacia';
            } elseif(preg_match('/^(FALLO|ERROR|FAIL)/i', $data)===1){
                $result['Estado']    = 'ERROR';
                $result['Mensaje']   = 'La prueba reporto un fallo';
            }

        /************************************/
        } else {
            /************************************/
            // Escalar u objeto
            $encoded = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $result['Resultado'] = ($encoded===false) ? gettype($data) : $encoded;
        }

        /************************************/
        // Retorno los datos
        return $result;
    }

}