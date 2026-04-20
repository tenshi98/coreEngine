<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsServerServer {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	/**
     * Obtiene la fecha actual configurada para la zona horaria de Chile.
     * Establece el huso horario 'America/Santiago' y retorna la fecha en formato ISO 8601 extendido.
     *
     * @return string Fecha actual en formato YYYY-MM-DD (ej: 2026-04-04).
	 *
	 * @example
	 * ```php
	 * $ServerServer->fechaActual(); //devuelve la fecha actual con formato 2024-07-01
	 * ```
	 *
     */
    public function fechaActual($format = "Y-m-d"): string {

        /********************** Si todo esta ok **********************/
        // Establecer la zona horaria predeterminada a Chile para asegurar consistencia en los datos
        date_default_timezone_set('America/Santiago');

        /********************** Retorno datos  **********************/
        // Devolvemos la fecha actual utilizando el separador de guion
        return date($format);

    }

    /************************************************************************************************************/
	/**
     * Obtiene la fecha actual de Chile sin caracteres separadores.
     * Útil para la generación de nombres de archivos, folios o procesos que requieren un formato compacto.
     *
     * @return string Fecha actual en formato YYYYMMDD (ej: 20260404).
	 *
	 * @example
	 * ```php
	 * $ServerServer->fechaActualAlternative(); //devuelve la fecha actual con formato 20240701
	 * ```
	 *
     */
    public function fechaActualAlternative(): string {

        /********************** Si todo esta ok **********************/
        // Configura la zona horaria local de Chile
        date_default_timezone_set('America/Santiago');

        /********************** Retorno datos  **********************/
        // Retorna la cadena numérica representativa de la fecha
        return date("Ymd");

    }

    /************************************************************************************************************/
	/**
     * Obtiene la hora actual configurada para la zona horaria de Chile.
     * Retorna la representación del tiempo en formato de 24 horas con separadores de dos puntos.
     *
     * @return string Hora actual en formato HH:ii:ss (ej: 18:28:58).
	 *
	 * @example
	 * ```php
	 * $ServerServer->horaActual(); //devuelve la hora actual con formato 18:28:58
	 * ```
	 *
     */
    public function horaActual($format = "H:i:s"): string {

        /********************** Si todo esta ok **********************/
        // Asegura que la hora corresponda al huso horario de Chile
        date_default_timezone_set('America/Santiago');

        /********************** Retorno datos  **********************/
        // Genera la cadena de tiempo con formato estándar de base de datos
        return date($format);

    }

    /************************************************************************************************************/
	/**
     * Obtiene la hora actual de Chile utilizando guiones como separadores.
     * Formato alternativo diseñado para compatibilidad con sistemas de archivos que restringen el uso de ':'.
     *
     * @return string Hora actual en formato HH-ii-ss (ej: 18-28-58).
	 *
	 * @example
	 * ```php
	 * $ServerServer->horaActualAlternative(); //devuelve la hora actual con formato 18-28-58
	 * ```
	 *
     */
    public function horaActualAlternative(): string {

        /********************** Si todo esta ok **********************/
        // Sincronización con la hora local de Chile
        date_default_timezone_set('America/Santiago');

        /********************** Retorno datos  **********************/
        // Retorna la hora con separadores de guion
        return date("H-i-s");

    }

    /************************************************************************************************************/
	/**
     * Obtiene el número del día actual del mes en curso.
     * Retorna el día sin ceros iniciales, basándose en la zona horaria de Chile.
     *
     * @return string Día del mes (1 a 31).
	 *
	 * @example
	 * ```php
	 * $ServerServer->diaActual(); //devuelve 1 (para la fecha 2024-07-01)
	 * ```
	 *
     */
    public function diaActual(): string {

        /********************** Si todo esta ok **********************/
        // Configuración regional de horario
        date_default_timezone_set('America/Santiago');

        /********************** Retorno datos  **********************/
        // Extrae el componente del día del mes sin relleno de ceros
        return date("j");

    }

    /************************************************************************************************************/
	/**
     * Obtiene el número de la semana actual del año.
     * Utiliza el estándar ISO-8601 donde las semanas comienzan en lunes.
     *
     * @return string Número de la semana del año (01 a 52/53).
	 *
	 * @example
	 * ```php
	 * $ServerServer->semanaActual(); //devuelve 27 (para la fecha 2024-07-01)
	 * ```
	 *
     */
    public function semanaActual(): string {

        /********************** Si todo esta ok **********************/
        // Sincronización horaria
        date_default_timezone_set('America/Santiago');

        /********************** Retorno datos  **********************/
        // Retorna el número de semana anual
        return date("W");

    }

    /************************************************************************************************************/
	/**
     * Obtiene el número del mes actual.
     * Retorna el valor numérico del mes sin ceros iniciales.
     *
     * @return string Número del mes (1 a 12).
	 *
	 * @example
	 * ```php
	 * $ServerServer->mesActual(); //devuelve 7 (para la fecha 2024-07-01)
	 * ```
	 *
     */
    public function mesActual(): string {

        /********************** Si todo esta ok **********************/
        // Configuración de zona horaria local
        date_default_timezone_set('America/Santiago');

        /********************** Retorno datos  **********************/
        // Extrae el mes actual en formato numérico simple
        return date("n");

    }

    /************************************************************************************************************/
	/**
     * Obtiene el año actual en formato de cuatro dígitos.
     *
     * @return string Año actual (ej: 2026).
	 *
	 * @example
	 * ```php
	 * $ServerServer->anoActual(); //devuelve 2024 (para la fecha 2024-07-01)
	 * ```
	 *
     */
    public function anoActual(): string {

        /********************** Si todo esta ok **********************/
        // Asegura que la fecha base sea la de Chile
        date_default_timezone_set('America/Santiago');

        /********************** Retorno datos  **********************/
        // Retorna el año completo (4 dígitos)
        return date("Y");

    }

    /************************************************************************************************************/
	/**
     * Ejecuta comandos administrativos o peticiones web en segundo plano en el servidor.
     *
     * Permite delegar tareas pesadas o de configuración (bloqueo de IP, wget) al sistema operativo.
     * Implementa validaciones de seguridad para IPs y URLs antes de la ejecución.
     *
     * @param string $tarea Dato de entrada (IP o URL) según el tipo de tarea.
     * @param int $Type Identificador del tipo de tarea (1: Iptables, 2: Wget, 3: Personalizado).
     *
     * @return array Resultado de la ejecución con estado 'success' y mensaje descriptivo.
	 *
	 * @example
	 * ```php
	 * $ServerServer->tareasServer(https://www.ejemplo.com?param1=1&param2=2&param3=3);
	 * ```
	 *
     */
    public function tareasServer(string $tarea, int $Type): array {

        /********************** Si todo esta ok **********************/
        try {
            // Validación de integridad: la cadena de tarea no debe ser nula o vacía
            if (empty($tarea)) {
                return ['success' => false, 'data' => 'La tarea no puede estar vacía.'];
            }

            // Selección del comando de sistema a construir basado en el tipo de operación
            switch ($Type) {
                /*************************/
                // Caso 1: Gestión de seguridad mediante bloqueo de IP en el firewall (iptables)
                case 1:
                    if(filter_var(trim($tarea), FILTER_VALIDATE_IP)){
                        // Genera un script multilínea para añadir la IP al DROP y persistir cambios
                        $command = "
                        # Agrega la IP a la lista negra (DROP todo el tráfico entrante)
                        iptables -A INPUT -s ".$tarea." -j DROP

                        # Guarda los cambios (puede variar según la distribución)
                        if command -v netfilter-persistent &> /dev/null; then
                            netfilter-persistent save
                        elif command -v iptables-save &> /dev/null; then
                            iptables-save > /etc/iptables/rules.v4
                        fi";
                    }else{
                        return ['success' => false, 'data' => 'Verifique el dato solicitado, no es una IP.'];
                    }
                    break;
                /*************************/
                // Caso 2: Ejecución de petición HTTP asíncrona mediante wget
                case 2:
                    if(filter_var(trim($tarea), FILTER_VALIDATE_URL)){
                        // Escapa el argumento de la URL para prevenir inyecciones de comandos shell
                        $urlSeguro = escapeshellarg($tarea);
                        // Ejecuta wget en modo silencioso y en segundo plano (&)
                        $command = "/usr/bin/wget -N -q $urlSeguro &";
                    }else{
                        return ['success' => false, 'data' => 'Verifique el dato solicitado, no es una URL.'];
                    }
                    break;
                /*************************/
                // Caso 3: Espacio reservado para lógica de comandos adicionales
                case 3:
                    //otro comando
                    break;
            }

            // Intento de ejecución del comando construido en la terminal del sistema operativo
            try {
                // Ejecución del comando y captura de la salida estándar
                $resultado = shell_exec($command);

                // Si shell_exec retorna null, indica que hubo un error o el comando no produjo salida
                if ($resultado === null) {
                    return ['success' => false, 'data' => 'Error al ejecutar el comando. No se recibió salida.'];
                }

                // Confirmación de envío del comando al sistema
                return ['success' => true, 'data' => 'Ejecucion correcta'];
            } catch (\Throwable $th) {
                // Captura de errores fatales durante el proceso de ejecución shell
                return ['success' => false, 'data' => $th->getMessage(), 'code' => $th->getCode()];
            }

        } catch (Exception $e) {
            // Manejo de excepciones generales y sanitización de mensajes de error
            return ['success' => false, 'data' => 'Ocurrió un error:'.htmlspecialchars($e->getMessage())];
        }

    }

    /************************************************************************************************************/
	/**
     * Recupera y normaliza los índices informativos del arreglo global $_SERVER.
     *
     * Extrae una lista predefinida de variables de entorno, cabeceras y rutas del servidor,
     * transformándolas en un objeto para facilitar el acceso orientado a objetos.
	 *
     * @return object Objeto que contiene las claves de $_SERVER solicitadas o un mensaje de error.
	 *
	 * @example
	 * ```php
	 * $ServerServer->indicesServer()->PHP_SELF;
     * $ServerServer->indicesServer()->GATEWAY_INTERFACE;
     * $ServerServer->indicesServer()->SERVER_NAME;
     * $ServerServer->indicesServer()->SERVER_PROTOCOL;
     * $ServerServer->indicesServer()->REQUEST_TIME;
	 * ```
	 *
     */
    public function indicesServer(): object {

        /********************** Si todo esta ok **********************/
        try {
            // Definición de las claves de interés dentro del entorno global de ejecución
            $claves = [
                'PHP_SELF', 'argv', 'argc', 'GATEWAY_INTERFACE', 'SERVER_ADDR', 'SERVER_NAME',
                'SERVER_SOFTWARE', 'SERVER_PROTOCOL', 'REQUEST_METHOD', 'REQUEST_TIME',
                'REQUEST_TIME_FLOAT', 'QUERY_STRING', 'DOCUMENT_ROOT', 'HTTP_ACCEPT',
                'HTTP_ACCEPT_CHARSET', 'HTTP_ACCEPT_ENCODING', 'HTTP_ACCEPT_LANGUAGE',
                'HTTP_CONNECTION', 'HTTP_HOST', 'HTTP_REFERER', 'HTTP_USER_AGENT', 'HTTPS',
                'REMOTE_ADDR', 'REMOTE_HOST', 'REMOTE_PORT', 'REMOTE_USER', 'REDIRECT_REMOTE_USER',
                'SCRIPT_FILENAME', 'SERVER_ADMIN', 'SERVER_PORT', 'SERVER_SIGNATURE',
                'PATH_TRANSLATED', 'SCRIPT_NAME', 'REQUEST_URI', 'PHP_AUTH_DIGEST',
                'PHP_AUTH_USER', 'PHP_AUTH_PW', 'AUTH_TYPE', 'PATH_INFO', 'ORIG_PATH_INFO'
            ];

            $datos = [];

            // Itera sobre las claves definidas para extraer los valores existentes
            foreach ($claves as $clave) {
                // Asigna el valor de $_SERVER si existe, de lo contrario establece null
                $datos[$clave] = array_key_exists($clave, $_SERVER) ? $_SERVER[$clave] : null;
            }

            /********************** Retorno datos  **********************/
            // Casteo del array resultante a objeto estándar
            return (object) $datos;

        } catch (Throwable $e) {
            // Registro del error en el log del sistema en caso de fallo crítico
            error_log("Error al obtener datos del servidor: " . $e->getMessage());
            return (object) ['error' => 'No se pudieron obtener los datos del servidor.'];
        }

    }

    /************************************************************************************************************/
	/**
     * Elimina un directorio y todo su contenido de forma recursiva.
     *
     * Recorre cada archivo y subdirectorio dentro de la ruta especificada, eliminando
     * primero los elementos internos (unlink/rmdir) para poder borrar el directorio raíz.
	 *
     * @param string $src Ruta absoluta o relativa del directorio a eliminar.
     *
     * @return array Estado de la operación ('success' true/false).
	 *
	 * @example
	 * ```php
	 * $structure = '/client_folder/client/tutor'; //carpeta
	 * $ServerServer->removeDirectoryRecursive($structure);
	 * ```
	 *
     */
    public function removeDirectoryRecursive($src): array {

        /********************** Validaciones   **********************/
        // Validación de entrada obligatoria para evitar ejecuciones sobre rutas nulas
        if(!isset($src) || $src==''){ return ['success' => false, 'error' => 'No ha ingresado la ruta de la carpeta'];}

        /********************** Si todo esta ok **********************/
        try {
            // Apertura del manejador del directorio
            $dir = opendir($src);
            // Iteración sobre el contenido del directorio
            while(false !== ( $file = readdir($dir)) ) {
                // Omite los punteros de navegación de directorio '.' y '..'
                if (( $file != '.' ) && ( $file != '..' )) {
                    $full = $src . '/' . $file;
                    // Si el elemento es un directorio, aplica recursividad para vaciarlo
                    if ( is_dir($full) ) {
                        $this->removeDirectoryRecursive($full);
                    }
                    else {
                        // Si es un archivo, procede a su eliminación física
                        unlink($full);
                    }
                }
            }
            // Cierre del manejador de directorio antes de intentar borrar la carpeta vacía
            closedir($dir);
            // Eliminación del directorio una vez que se encuentra completamente vacío
            rmdir($src);

            // Confirmación de éxito en la limpieza
            return ['success' => true, 'data' => 'Archivos borrados'];
        } catch (Exception $e) {
            // Captura de errores como falta de permisos o rutas inexistentes
            return ['success' => false, 'error' => 'Ha ocurrido un error al borrar archivos'];
        }
    }

    /************************************************************************************************************/
	/**
     * Genera un archivo de configuración .env a partir de un arreglo asociativo.
     *
     * Sanitiza las claves de las variables y maneja valores con espacios aplicando
     * comillas y escapado de caracteres. Incluye validaciones de directorio y sobreescritura.
	 *
     * @param string $path Ruta completa del archivo destino.
     * @param array $variables Arreglo ['KEY' => 'VALUE'] con los datos de configuración.
     * @param bool $overwrite Si es true, reemplaza el archivo si ya existe.
     *
     * @return array Estado del proceso y mensaje descriptivo.
	 *
	 * @example
	 * ```php
	 * 	$variables = [
     * 	    'APP_NAME'    => 'Mi Aplicacion',
     * 	    'APP_ENV'     => 'production',
     * 	    'DB_HOST'     => 'localhost',
     * 	    'DB_DATABASE' => 'mi_base',
     * 	    'DB_USERNAME' => 'root',
     * 	    'DB_PASSWORD' => '123456'
     * 	];
     *
     * 	$result = writeEnvFile($envPath, $variables, true);
     *
     * 	if ($result['success']) {
     * 	    echo $result['message'];
     * 	} else {
     * 	    echo "Error: " . $result['message'];
     * 	}
	 * ```
	 *
     */
    public function writeEnvFile(string $path, array $variables, bool $overwrite = false): array {

        /********************** Si todo esta ok **********************/
        try {
            // Verifica que el directorio donde se escribirá el archivo sea válido
            $directory = dirname($path);
            if (!is_dir($directory)) {
                return [
                    'success' => false,
                    'message' => "El directorio no existe: {$directory}"
                ];
            }

            // Control de sobreescritura basado en el parámetro $overwrite
            if (file_exists($path) && !$overwrite) {
                return [
                    'success' => false,
                    'message' => "El archivo ya existe y overwrite está deshabilitado."
                ];
            }

            // Construcción secuencial del contenido del archivo .env
            $content = '';
            foreach ($variables as $key => $value) {
                // Sanitización de la clave: permite solo caracteres alfanuméricos y guiones bajos
                $cleanKey = preg_replace('/[^A-Z0-9_]/i', '', $key);

                // Si el valor contiene espacios, lo envuelve en comillas y escapa caracteres internos
                if (preg_match('/\s/', $value)) {
                    $value = '"' . addslashes($value) . '"';
                }

                // Concatenación en formato KEY=VALUE con salto de línea según PHP_EOL
                $content .= "{$cleanKey}={$value}" . PHP_EOL;
            }

            // Intento de escritura física del archivo en el sistema
            if (file_put_contents($path, $content) === false) {
                return [
                    'success' => false,
                    'message' => "No se pudo escribir el archivo."
                ];
            }

			// Retorno por defecto si no hay problemas
            return [
                'success' => true,
                'message' => "Archivo .env creado correctamente."
            ];

        } catch (Throwable $e) {
            // Manejo de errores fatales de sistema o permisos
            return [
                'success' => false,
                'message' => "Error: " . $e->getMessage()
            ];
        }
    }



    /******************************************************************************/
	/**
     * Crea o actualiza un archivo de clase PHP con constantes de configuración.
     *
     * Esta función genera dinámicamente una clase denominada 'ConfigData'. Si el archivo no existe,
     * crea la estructura completa. Si existe, verifica la presencia de la clase y añade únicamente
     * las constantes que no se encuentren ya definidas para evitar duplicidad.
     *
     * @param string $path Ruta absoluta del archivo .php a crear o modificar.
     * @param array $variables Arreglo asociativo donde cada clave será una constante de clase y su valor un array de configuración.
     *
     * @return array Resultado de la operación con estado 'success' y mensaje detallado de constantes agregadas/existentes.
	 *
	 * @example
	 * ```php
	 * 	$phpPath = __DIR__ . '/config.php';
     *
     * 	$variables = [
     * 		'MySQL_ADMIN' => [
     * 			'HOSTNAME' => 'localhost',
     * 			'USERNAME' => 'root',
     * 			'PASSWORD' => '123456'
     * 			'DATABASE' => 'mi_base',
     * 			'CHARSET'  => 'utf8mb4',
     * 			'PORT'     => 3306,
     * 		],
     * 		'MySQL_1' => [
     * 			'HOSTNAME' => 'localhost',
     * 			'USERNAME' => 'usuario',
     * 			'PASSWORD' => '123456'
     * 			'DATABASE' => 'mi_base',
     * 			'CHARSET'  => 'utf8mb4',
     * 			'PORT'     => 3306,
     * 		],
     * 	];
     *
     * 	$result = writeConfigClassFile($phpPath, $variables);
     *
     * 	if ($result['success']) {
     * 	    echo $result['message'];
     * 	} else {
     * 	    echo "Error: " . $result['message'];
     * 	}
	 * ```
	 *
     */
    public function writeConfigClassFile(string $path, array $variables): array {

        try {

            /********************** Validaciones   **********************/
            // Verificar existencia del directorio padre
            $directory = dirname($path);
            if (!is_dir($directory)) {
                return [
                    'success' => false,
                    'message' => "El directorio no existe: {$directory}"
                ];
            }

            // Validar que el archivo tenga extensión PHP
            if (pathinfo($path, PATHINFO_EXTENSION) !== 'php') {
                return [
                    'success' => false,
                    'message' => "El archivo debe tener extensión .php"
                ];
            }

            // Validar que cada elemento de las variables sea un arreglo (estructura de constante)
            foreach ($variables as $constName => $fields) {
                if (!is_array($fields)) {
                    return [
                        'success' => false,
                        'message' => "El valor de '{$constName}' debe ser un array."
                    ];
                }
            }

            /********************** Si todo esta ok **********************/
            // Función anónima para construir el bloque de código de una constante de clase
            $buildConstBlock = function(string $constName, array $fields): string {

                // Sanitización del nombre de la constante (Mayúsculas y caracteres permitidos)
                $constName  = preg_replace('/[^A-Z0-9_]/', '', strtoupper($constName));
                $constBlock  = "    /*****************************************************/\n";
                $constBlock .= "    //Variables para MySQL\n";
                $constBlock .= "    const {$constName} = [\n";

                foreach ($fields as $key => $value) {

                    // Sanitización de la clave del arreglo interno
                    $cleanKey = preg_replace('/[^A-Z0-9_]/i', '', strtoupper($key));

                    // Determinación del formato de exportación según el tipo de dato
                    if (is_int($value) || is_float($value)) {
                        $exportedValue = $value;
                    } elseif (is_bool($value)) {
                        $exportedValue = $value ? 'true' : 'false';
                    } elseif (is_null($value)) {
                        $exportedValue = 'null';
                    } elseif (is_numeric($value) && !preg_match('/^0\d+$/', $value)) {
                        $exportedValue = $value + 0;
                    } else {
                        // Escapado de cadenas de texto
                        $exportedValue = "'" . addslashes($value) . "'";
                    }

                    $constBlock .= "        '{$cleanKey}' => {$exportedValue},\n";
                }

                $constBlock .= "    ];\n\n";
                return $constBlock;
            };

            // Lógica para creación de archivo nuevo
            if (!file_exists($path)) {

                $content  = "<?php\n";
                $content .= "/*******************************************************************************************************************/\n";
                $content .= "/* Se define la clase                                                 */\n";
                $content .= "/*******************************************************************************************************************/\n";
                $content .= "class ConfigData{\n";

                // Iterar y construir bloques para todas las variables recibidas
                foreach ($variables as $constName => $fields) {
                    $content .= $buildConstBlock($constName, $fields);
                }

                $content .= "}\n";

                file_put_contents($path, $content);

                $names = implode(', ', array_keys($variables));
                return [
                    'success' => true,
                    'message' => "Archivo creado con las constantes: {$names}."
                ];
            }

            // Lógica para actualización de archivo existente
            $existingContent = file_get_contents($path);

            // Si el archivo existe pero no contiene la declaración de la clase
            if (!preg_match('/class\s+ConfigData/i', $existingContent)) {

                $existingContent .= "\n\nclass ConfigData{\n";

                foreach ($variables as $constName => $fields) {
                    $existingContent .= $buildConstBlock($constName, $fields);
                }

                $existingContent .= "}\n";

                file_put_contents($path, $existingContent);

                $names = implode(', ', array_keys($variables));
                return [
                    'success' => true,
                    'message' => "Clase creada con las constantes: {$names}."
                ];
            }

            // Filtrado de constantes para evitar duplicados en la clase existente
            $existentes = [];
            $nuevas     = [];

            foreach ($variables as $constName => $fields) {
                $sanitized = preg_replace('/[^A-Z0-9_]/', '', strtoupper($constName));
                // Búsqueda mediante expresión regular de la definición de la constante
                if (preg_match('/const\s+' . $sanitized . '\s*=/i', $existingContent)) {
                    $existentes[] = $sanitized;
                } else {
                    $nuevas[$sanitized] = $fields;
                }
            }

            // Si no hay constantes nuevas que agregar, retornar error informativo
            if (empty($nuevas)) {
                $names = implode(', ', $existentes);
                return [
                    'success' => false,
                    'message' => "Las constantes ya existen: {$names}."
                ];
            }

            // Inserción de nuevos bloques antes del cierre de la llave de la clase
            $newBlocks = '';
            foreach ($nuevas as $constName => $fields) {
                $newBlocks .= $buildConstBlock($constName, $fields);
            }

            $updatedContent = preg_replace(
                '/}\s*$/',
                $newBlocks . "}",
                $existingContent
            );

            if ($updatedContent === null) {
                return [
                    'success' => false,
                    'message' => "Error al actualizar el archivo."
                ];
            }

            file_put_contents($path, $updatedContent);

            /********************** Retorno datos  **********************/
            $msg = [];
            if (!empty($nuevas))     { $msg[] = "Agregadas: "  . implode(', ', array_keys($nuevas)); }
            if (!empty($existentes)) { $msg[] = "Ya existían: " . implode(', ', $existentes); }

            return [
                'success' => true,
                'message' => implode(' | ', $msg)
            ];

        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => "Error: " . $e->getMessage()
            ];
        }
    }

	/************************************************************************************************************/
	/**
     * Navega hacia arriba en la jerarquía de directorios una cantidad específica de niveles.
     *
     * Toma una ruta base y aplica la función dirname de forma iterativa para obtener el
     * directorio padre según los niveles solicitados.
     *
     * @param string $path Ruta inicial desde la cual se desea subir.
     * @param int $levels Número de niveles jerárquicos a ascender (por defecto 1).
     *
     * @return string Ruta resultante normalizada sin separadores finales.
	 *
	 * @example
	 * ```php
	 * $envPath  = __DIR__;
	 * $rootPath = getParentPath($envPath, 4);
     * echo $rootPath;
	 * ```
	 *
     */
    public function getParentPath(string $path, int $levels = 1): string {

        /********************** Validaciones   **********************/
        // Validar que la ruta de entrada no esté vacía
        if(!isset($path) || $path==''){ return 'No ha ingresado la ruta del directorio';}

        /********************** Si todo esta ok **********************/
        // Limpieza de separadores finales para asegurar consistencia en el bucle
        $result = rtrim($path, DIRECTORY_SEPARATOR);

        // Aplicación iterativa de dirname para subir niveles
        for ($i = 0; $i < $levels; $i++) {
            $result = dirname($result);
        }

        /********************** Retorno datos  **********************/
        return $result;
    }

	/************************************************************************************************************/
	/**
     * Verifica la capacidad de escritura de un directorio e intenta corregirla si es necesario.
     *
     * Comprueba si el directorio existe y si tiene permisos de escritura. En caso negativo,
     * intenta aplicar un cambio de modo (chmod) con los permisos especificados.
     *
     * @param string $directory Ruta absoluta del directorio a validar.
     * @param int $permission Máscara de permisos octal a aplicar en caso de fallo (por defecto 0755).
     *
     * @return array Resultado de la validación y acciones tomadas con mensaje descriptivo.
	 *
	 * @example
	 * ```php
	 * 	$path = '/var/www/html/coreEngine/admin/storage';
     *
     * 	$result = ensureWritableDirectory($path, 0775);
     *
     * 	if ($result['success']) {
     * 		echo $result['message'];
     * 	} else {
     * 		echo "Error: " . $result['message'];
     * 	}
	 * ```
	 *
     */
    public function isWritableDirectory(string $directory, int $permission = 0755): array {

        /********************** Validaciones   **********************/
        if(!isset($directory) || $directory==''){ return ['success' => false,'message' => "No ha ingresado la ruta del directorio."];}

        /********************** Si todo esta ok **********************/
        try {

            // Comprobación de existencia física del directorio
            if (!is_dir($directory)) {
                return [
                    'success' => false,
                    'message' => "El directorio no existe."
                ];
            }

            // Comprobación de permisos de escritura actuales
            if (is_writable($directory)) {
                return [
                    'success' => true,
                    'message' => "El directorio ya tiene permisos de escritura."
                ];
            }

            // Intento de modificación de permisos mediante chmod
            if (!chmod($directory, $permission)) {
                return [
                    'success' => false,
                    'message' => "No se pudieron cambiar los permisos del directorio."
                ];
            }

            // Segunda verificación tras el intento de corrección
            if (!is_writable($directory)) {
                return [
                    'success' => false,
                    'message' => "Los permisos fueron cambiados, pero aún no es escribible."
                ];
            }

            /********************** Retorno datos  **********************/
            return [
                'success' => true,
                'message' => "Permisos actualizados correctamente."
            ];

        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => "Error: " . $e->getMessage()
            ];
        }
    }

    /******************************************************************************************/
	/**
     * Garantiza la existencia de un directorio, creándolo recursivamente si no existe.
     *
     * Utiliza permisos 0755 para la creación. Incluye manejo de excepciones para
     * capturar errores de sistema durante la ejecución de mkdir.
     *
     * @param string $path Ruta del directorio a verificar o crear.
     *
     * @return array Resultado indicando si el directorio existe o fue creado exitosamente.
	 *
	 * @example
	 * ```php
	 *
	 * ```
	 *
     */
    public function ensureDirectoryExists(string $path): array {

        /********************** Si todo esta ok **********************/
        // Verifica si la ruta ya existe y es un directorio válido
        if (!is_dir($path)) {

            // Intento de creación recursiva con permisos estándar
            try {
                $created = mkdir($path, 0755, true);

                if ($created) {
                    return [
                        "success" => true,
                        "message" => "Carpeta creada correctamente"
                    ];
                }

                // Error lógico si mkdir falla sin lanzar excepción
                return [
                    "success" => false,
                    "message" => "No se pudo crear el directorio (mkdir retornó false)"
                ];

            } catch (\Throwable $e) {
                // Captura de errores de permisos o restricciones del sistema de archivos
                return [
                    "success" => false,
                    "message" => "Error al crear carpeta: " . $e->getMessage()
                ];

            }
        }else{
            // El directorio ya se encuentra disponible para su uso
            return ['success' => true, 'message' => 'El directorio ya existe'];
        }
    }

    /******************************************************************************************/
	/**
     * Valida y corrige los permisos de una ruta al estándar 0755.
     *
     * Obtiene los permisos actuales en formato octal y, si difieren de 0755,
     * intenta realizar la corrección mediante chmod, verificando el resultado final.
     *
     * @param string $path Ruta del archivo o directorio a validar.
	 *
	 * @return array Comparativa de permisos actuales vs esperados y resultado del cambio:
	 *  - success : bool
     *  - message : string
     *  - current : string (permisos actuales)
     *  - expected: string (permisos esperados)
	 *
	 * @example
	 * ```php
	 *
	 * ```
	 *
     */
    public function ensurePermissions755(string $path): array {

        /********************** Validaciones   **********************/
        // Verifica la existencia física del elemento antes de operar
        if (!file_exists($path)) {
            return [
                "success" => false,
                "message" => "La ruta no existe",
                "current" => $path,
                "expected" => "0755"
            ];
        }

        /********************** Si todo esta ok **********************/
        // Extracción de permisos actuales y conversión a formato octal de 4 dígitos
        $perms = fileperms($path);
        $currentPerms = substr(sprintf('%o', $perms), -4);

        // Si los permisos ya coinciden con el requerimiento, no se realiza acción
        if ($currentPerms === '0755') {
            return [
                "success" => true,
                "message" => "Permisos correctos",
                "current" => $currentPerms,
                "expected" => "0755"
            ];
        }

        // Intento de actualización de permisos silenciando advertencias directas
        $changed = @chmod($path, 0755);

        // Re-verificación de permisos tras la ejecución de chmod
        $permsAfter = fileperms($path);
        $newPerms   = substr(sprintf('%o', $permsAfter), -4);

        /********************** Retorno datos  **********************/
        if ($changed && $newPerms === '0755') {
            return [
                "success" => true,
                "message" => "Permisos corregidos correctamente",
                "current" => $newPerms,
                "expected" => "0755"
            ];
        }

        // Error en la corrección, común en entornos con volúmenes restringidos o Docker
        return [
            "success" => false,
            "message" => "No se pudieron cambiar los permisos (posible restricción de Docker o permisos del sistema)",
            "current" => $currentPerms,
            "expected" => "0755"
        ];
    }

    /******************************************************************************************/
	/**
     * Realiza una prueba empírica de escritura en una ruta específica.
     *
     * A diferencia de is_writable(), esta función intenta crear un archivo temporal
     * físico para confirmar que el proceso PHP tiene permisos reales sobre el
     * sistema de archivos o volumen.
	 *
	 * Ventajas:
	 * - Evita falsos negativos comunes en Docker
	 * - Detecta problemas reales de permisos (UID/GID, volúmenes, FS)
	 * - Funciona incluso cuando is_writable() falla incorrectamente
	 *
	 * Consideraciones:
	 * - Crea un archivo temporal oculto (prefijo ".perm_test_")
	 * - Requiere permisos de escritura en la ruta
	 * - Puede fallar si el sistema bloquea creación de archivos
     *
     * @param string $path Ruta donde se desea verificar la capacidad de escritura.
     *
     * @return bool True si la escritura y eliminación del archivo de prueba fueron exitosas.
	 *
	 * @example
	 * ```php
	 *
	 * ```
	 *
     */
    public function canWrite(string $path): bool {

        /********************** Si todo esta ok **********************/
        // Generación de un nombre de archivo temporal oculto y único
        $testFile = rtrim($path, '/') . '/.perm_test_' . uniqid();

        // Intento de escritura física de contenido mínimo
        if (@file_put_contents($testFile, 'test') !== false) {

            // Limpieza del archivo de prueba tras confirmar éxito
            @unlink($testFile);

            /********************** Retorno datos  **********************/
            return true;
        }

        // Si la escritura falló, se confirma la falta de permisos efectivos
        return false;
    }

}
