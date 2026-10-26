<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsServerServer {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Publicos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
    /**
     * Obtiene la fecha actual configurada para la zona horaria de Chile.
     * Establece el huso horario 'America/Santiago' y retorna la fecha en el formato indicado (por defecto ISO 8601: Y-m-d).
     *
     * @param string $format Formato de fecha PHP (por defecto: 'Y-m-d').
     *
     * @return string Fecha actual en el formato indicado (por defecto: YYYY-MM-DD, ej: 2026-04-04).
	 *
	 * @example
	 * ```php
	 * $ServerServer->fechaActual(); // Devuelve la fecha actual con formato 2024-07-01
	 * $ServerServer->fechaActual('Ymd'); // Devuelve la fecha actual con formato 20240701
	 * ```
	 *
     */
    public function fechaActual($format = "Y-m-d"): string {

        /********************** Retorno datos  **********************/
        // Zona horaria 'America/Santiago' y fecha en el formato indicado (centralizado en now())
        return $this->now($format);

    }

    /******************************************************************************/
    /**
     * Obtiene la fecha actual de Chile sin caracteres separadores.
     * Útil para la generación de nombres de archivos, folios o procesos que requieren un formato compacto.
     *
     * @return string Fecha actual en formato YYYYMMDD (ej: 20260404).
	 *
	 * @example
	 * ```php
	 * $ServerServer->fechaActualAlternative(); // Devuelve la fecha actual con formato 20240701
	 * ```
	 *
     */
    public function fechaActualAlternative(): string {

        /********************** Retorno datos  **********************/
        // Zona horaria 'America/Santiago' y fecha compacta YYYYMMDD (centralizado en now())
        return $this->now('Ymd');

    }

    /******************************************************************************/
    /**
     * Obtiene la hora actual configurada para la zona horaria de Chile.
     * Retorna la representación del tiempo en el formato indicado (por defecto, 24 horas con separadores de dos puntos).
     *
     * @param string $format Formato de hora PHP (por defecto: 'H:i:s').
     *
     * @return string Hora actual en el formato indicado (por defecto: HH:MM:SS, ej: 18:28:58).
	 *
	 * @example
	 * ```php
	 * $ServerServer->horaActual(); // Devuelve la hora actual con formato 18:28:58
	 * $ServerServer->horaActual('H-i'); // Devuelve la hora actual con formato 18-28
	 * ```
	 *
     */
    public function horaActual($format = "H:i:s"): string {

        /********************** Retorno datos  **********************/
        // Zona horaria 'America/Santiago' y hora en el formato indicado (centralizado en now())
        return $this->now($format);

    }

    /******************************************************************************/
    /**
     * Obtiene la hora actual de Chile utilizando guiones como separadores.
     * Formato alternativo diseñado para compatibilidad con sistemas de archivos que restringen el uso de ':'.
     *
     * @return string Hora actual en formato HH-MM-SS (ej: 18-28-58).
	 *
	 * @example
	 * ```php
	 * $ServerServer->horaActualAlternative(); // Devuelve la hora actual con formato 18-28-58
	 * ```
	 *
     */
    public function horaActualAlternative(): string {

        /********************** Retorno datos  **********************/
        // Zona horaria 'America/Santiago' y hora con guiones (centralizado en now())
        return $this->now('H-i-s');

    }

    /******************************************************************************/
    /**
     * Obtiene el número del día actual del mes en curso.
     * Retorna el día sin ceros iniciales, basándose en la zona horaria de Chile.
     *
     * @return string Día del mes (1 a 31).
	 *
	 * @example
	 * ```php
	 * $ServerServer->diaActual(); // Devuelve 1 (para la fecha 2024-07-01)
	 * ```
	 *
     */
    public function diaActual(): string {

        /********************** Retorno datos  **********************/
        // Zona horaria 'America/Santiago' y día del mes sin relleno (centralizado en now())
        return $this->now('j');

    }

    /******************************************************************************/
    /**
     * Obtiene el número de la semana actual del año.
     * Utiliza el estándar ISO-8601 donde las semanas comienzan en lunes.
     *
     * @return string Número de la semana del año (01 a 52/53).
	 *
	 * @example
	 * ```php
	 * $ServerServer->semanaActual(); // Devuelve 27 (para la fecha 2024-07-01)
	 * ```
	 *
     */
    public function semanaActual(): string {

        /********************** Retorno datos  **********************/
        // Zona horaria 'America/Santiago' y semana ISO-8601 (centralizado en now())
        return $this->now('W');

    }

    /******************************************************************************/
    /**
     * Obtiene el número del mes actual.
     * Retorna el valor numérico del mes sin ceros iniciales.
     *
     * @return string Número del mes (1 a 12).
	 *
	 * @example
	 * ```php
	 * $ServerServer->mesActual(); // Devuelve 7 (para la fecha 2024-07-01)
	 * ```
	 *
     */
    public function mesActual(): string {

        /********************** Retorno datos  **********************/
        // Zona horaria 'America/Santiago' y mes numérico sin relleno (centralizado en now())
        return $this->now('n');

    }

    /******************************************************************************/
    /**
     * Obtiene el año actual en formato de cuatro dígitos.
     *
     * @return string Año actual (ej: 2026).
	 *
	 * @example
	 * ```php
	 * $ServerServer->anoActual(); // Devuelve 2024 (para la fecha 2024-07-01)
	 * ```
	 *
     */
    public function anoActual(): string {

        /********************** Retorno datos  **********************/
        // Zona horaria 'America/Santiago' y año de 4 dígitos (centralizado en now())
        return $this->now('Y');

    }

	/******************************************************************************/
    /**
     * Recupera y normaliza los índices informativos del arreglo global $_SERVER.
     *
     * Extrae una lista predefinida de variables de entorno, cabeceras y rutas del servidor,
     * transformándolas en un objeto para facilitar el acceso orientado a objetos.
	 *
     * @return object Objeto con las claves de $_SERVER solicitadas (las inexistentes valen null) o un objeto con la clave 'error'.
	 *
	 * @example
	 * ```php
	 * $serverInfo = $ServerServer->indicesServer();
	 * $serverInfo->PHP_SELF; // Devuelve el valor de $_SERVER['PHP_SELF'] o null
	 * $serverInfo->SERVER_NAME; // Devuelve el valor de $_SERVER['SERVER_NAME'] o null
	 * $serverInfo->REQUEST_TIME; // Devuelve el valor de $_SERVER['REQUEST_TIME'] o null
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

            // Enmascara las credenciales HTTP Basic/Digest para evitar fugas si el
            // objeto resultante se registra en logs o se expone en una respuesta
            foreach (['PHP_AUTH_USER', 'PHP_AUTH_PW', 'PHP_AUTH_DIGEST'] as $claveSensible) {
                if (!empty($datos[$claveSensible])) {
                    $datos[$claveSensible] = '***';
                }
            }

            /********************** Retorno datos  **********************/
            // Casteo del array resultante a objeto estándar
            return (object) $datos;

        } catch (Throwable $e) {
            // Registro del error en el log del sistema en caso de fallo crítico
            return (object) ['error' => 'No se pudieron obtener los datos del servidor.'];
        }

    }

	/******************************************************************************/
    /**
	 * Obtiene información de uso de disco de una ruta.
	 *
	 * Usa disk_total_space y disk_free_space para calcular total, libre, usado y porcentaje.
	 * La ruta por defecto es la base del proyecto (BASE).
	 *
	 * @param string|null $Ruta Ruta a analizar (por defecto BASE).
	 *
	 * @return array|string ['total','libre','usado','porcentaje'] en bytes + %; mensaje de error si falla.
	 *
	 * @example
	 * ```php
	 * $ServerServer->diskUsage('/var/www'); // Devuelve ['total'=>...,'libre'=>...,'usado'=>...,'porcentaje'=>45.2]
	 * ```
	 *
	 */
	public function diskUsage(?string $Ruta = null): array|string {

		/********************** Validaciones   **********************/
		if ($Ruta === null) {
            $RutaFinal = dirname(__DIR__);
        } else {
            $RutaFinal = trim($Ruta);
        }

        if ($RutaFinal === '') {
            return 'La ruta no puede estar vacía';
        }

		if (!is_dir($RutaFinal)) {
			return 'La ruta no existe o no es un directorio';
		}

		/********************** Si todo esta ok **********************/
		$Total  = disk_total_space($RutaFinal);
		$Libre  = disk_free_space($RutaFinal);
		if ($Total === false || $Libre === false) {
			return 'No se pudo obtener la información de disco';
		}
		$Usado      = $Total - $Libre;
		$Porcentaje = $Total > 0 ? round(($Usado / $Total) * 100, 1) : 0;

		/********************** Retorno datos **********************/
		return [
			'total'      => $Total,
			'libre'      => $Libre,
			'usado'      => $Usado,
			'porcentaje' => $Porcentaje,
		];

	}

	/******************************************************************************/
    /**
     * Navega hacia arriba en la jerarquía de directorios una cantidad específica de niveles.
     *
     * Toma una ruta base y aplica la función dirname de forma iterativa para obtener el
     * directorio padre según los niveles solicitados.
     *
     * @param string $path Ruta inicial desde la cual se desea subir.
     * @param int $levels Número de niveles jerárquicos a ascender (por defecto 1).
     *
     * @return string Ruta resultante normalizada sin separadores finales, o mensaje de error si la ruta está vacía.
	 *
	 * @example
	 * ```php
	 * $envPath  = __DIR__;
	 * $rootPath = $ServerServer->getParentPath($envPath, 4);
	 * echo $rootPath; // Devuelve la ruta 4 niveles arriba de __DIR__
	 * ```
	 *
     */
    public function getParentPath(string $path, int $levels = 1): string {

        /********************** Validaciones   **********************/
        // Validar que la ruta de entrada no esté vacía
        if ($path === null || trim((string)$path) === '') {
            return 'No ha ingresado la ruta del directorio';
        }

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

    /******************************************************************************/
    /**
     * Garantiza la existencia de un directorio, creándolo recursivamente si no existe.
     *
     * Utiliza permisos 0755 para la creación. Incluye manejo de excepciones para
     * capturar errores de sistema durante la ejecución de mkdir.
     *
     * @param string $path Ruta del directorio a verificar o crear.
     * @param string|null $baseDir Directorio base autorizado (por defecto la raíz del proyecto); la ruta debe quedar contenida dentro de él.
     *
     * @return array Resultado de la operación: ['success' => bool, 'message' => string] indicando si el directorio existía o fue creado.
	 *
	 * @example
	 * ```php
	 * $result = $ServerServer->ensureDirectoryExists('/var/www/html/coreEngine/admin/storage');
	 * // Devuelve ['success' => true, 'message' => 'Carpeta creada correctamente'] o ['success' => true, 'message' => 'El directorio ya existe']
	 * ```
	 *
     */
    public function ensureDirectoryExists(string $path, ?string $baseDir = null): array {

        /********************** Validaciones   **********************/
        if ($path === null || trim((string)$path) === '') {
            return ['success' => false,'message' => "No ha ingresado la ruta del directorio."];
        }

        // Directorio base autorizado: por defecto la raíz del proyecto (3 niveles arriba de functions/)
        $baseReal = realpath($baseDir ?? dirname(__DIR__, 3));
        if ($baseReal === false) {
            return ['success' => false, 'message' => 'El directorio base autorizado no existe'];
        }

        // Normaliza la ruta destino: si no existe aún, se resuelve el ancestro existente más cercano
        $targetReal = realpath($path);
        if ($targetReal === false) {
            $parentReal = realpath(dirname($path));
            if ($parentReal === false) {
                return ['success' => false, 'message' => 'La ruta destino no es válida'];
            }
            $targetReal = $parentReal . DIRECTORY_SEPARATOR . basename($path);
        }

        // Verifica que la ruta normalizada quede contenida dentro del directorio base autorizado
        if ($targetReal !== $baseReal && !str_starts_with($targetReal, $baseReal . DIRECTORY_SEPARATOR)) {
            return ['success' => false, 'message' => 'La ruta indicada está fuera del directorio autorizado'];
        }

        /********************** Si todo esta ok **********************/
        // Verifica si la ruta ya existe y es un directorio válido (sobre la ruta normalizada)
        if (!is_dir($targetReal)) {

            // Intento de creación recursiva con permisos estándar
            try {
                $created = mkdir($targetReal, 0755, true);

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

    /******************************************************************************/
    /**
     * Elimina un directorio y todo su contenido de forma recursiva.
     *
     * Recorre cada archivo y subdirectorio dentro de la ruta especificada, eliminando
     * primero los elementos internos (unlink/rmdir) para poder borrar el directorio raíz.
	 *
     * @param string $src Ruta absoluta o relativa del directorio a eliminar.
     * @param string|null $baseDir Directorio base autorizado (por defecto la raíz del proyecto); la ruta a eliminar debe quedar contenida dentro de él.
     *
     * @return array Resultado con éxito y datos, o error: ['success' => bool, 'data'|'error' => string].
	 *
	 * @example
	 * ```php
	 * $structure = '/client_folder/client/tutor'; //carpeta
	 * $ServerServer->removeDirectoryRecursive($structure); // Devuelve ['success' => true, 'data' => 'Archivos borrados'] o ['success' => false, 'error' => '...']
	 * ```
	 *
     */
    public function removeDirectoryRecursive($src, ?string $baseDir = null): array {

        /********************** Validaciones   **********************/
        // Validación de entrada obligatoria para evitar ejecuciones sobre rutas nulas
        if ($src === null || trim((string)$src) === '') {
            return ['success' => false, 'error' => 'No ha ingresado la ruta de la carpeta'];
        }

        // Directorio base autorizado: por defecto la raíz del proyecto (3 niveles arriba de functions/)
        $baseReal = realpath($baseDir ?? dirname(__DIR__, 3));
        if ($baseReal === false) {
            return ['success' => false, 'error' => 'El directorio base autorizado no existe'];
        }

        // Normaliza la ruta objetivo con realpath() para impedir path traversal ('../', symlinks)
        $srcReal = realpath($src);
        if ($srcReal === false) {
            return ['success' => false, 'error' => 'La ruta de la carpeta no existe'];
        }

        // Verifica que la ruta normalizada quede contenida dentro del directorio base autorizado
        if ($srcReal !== $baseReal && !str_starts_with($srcReal, $baseReal . DIRECTORY_SEPARATOR)) {
            return ['success' => false, 'error' => 'La ruta indicada está fuera del directorio autorizado'];
        }

        /********************** Si todo esta ok **********************/
        try {
            // Apertura del manejador del directorio (sobre la ruta ya normalizada)
            $dir = opendir($srcReal);
            // Iteración sobre el contenido del directorio
            while(false !== ( $file = readdir($dir)) ) {
                // Omite los punteros de navegación de directorio '.' y '..'
                if (( $file != '.' ) && ( $file != '..' )) {
                    $full = $srcReal . '/' . $file;
                    // Los enlaces simbólicos se eliminan sin seguirlos (evitan salirse del directorio base)
                    if ( is_link($full) ) {
                        unlink($full);
                    }
                    // Si el elemento es un directorio, aplica recursividad para vaciarlo
                    elseif ( is_dir($full) ) {
                        $this->removeDirectoryRecursive($full, $baseDir);
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
            rmdir($srcReal);

            // Confirmación de éxito en la limpieza
            return ['success' => true, 'data' => 'Archivos borrados'];
        } catch (Exception $e) {
            // Captura de errores como falta de permisos o rutas inexistentes
            return ['success' => false, 'error' => 'Ha ocurrido un error al borrar archivos'];
        }

    }

    /******************************************************************************/
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
     * @return array Resultado del proceso: ['success' => bool, 'message' => string].
	 *
	 * @example
	 * ```php
	 * $envPath = '/var/www/html/coreEngine/.env';
	 * $variables = [
	 *     'APP_NAME'    => 'Mi Aplicacion',
	 *     'APP_ENV'     => 'production',
	 *     'DB_HOST'     => 'localhost',
	 *     'DB_DATABASE' => 'mi_base',
	 *     'DB_USERNAME' => 'root',
	 *     'DB_PASSWORD' => '123456'
	 * ];
	 *
	 * $result = $ServerServer->writeEnvFile($envPath, $variables, true);
	 *
	 * if ($result['success']) {
	 *     echo $result['message']; //Archivo .env creado correctamente.
	 * } else {
	 *     echo "Error: " . $result['message'];
	 * }
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

                // Los saltos de línea romperían el formato .env: se reemplazan por un espacio
                $value = str_replace(["\r", "\n"], ' ', (string)$value);

                // Si el valor contiene espacios, '#', '"' o '\', se envuelve en comillas dobles
                // escapando '\' y '"' internamente (escapado .env, más seguro que addslashes)
                if (preg_match('/[\s#"\'\\\\]/', $value)) {
                    $value = '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
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
     * Esta función genera dinámicamente una clase denominada 'ConfigDataBase'. Si el archivo no existe,
     * crea la estructura completa. Si existe, verifica la presencia de la clase y añade únicamente
     * las constantes que no se encuentren ya definidas para evitar duplicidad.
     *
     * @param string $path Ruta absoluta del archivo .php a crear o modificar.
     * @param array $variables Arreglo asociativo donde cada clave será una constante de clase y su valor un array de configuración.
     *
     * @return array Resultado de la operación: ['success' => bool, 'message' => string] con el detalle de constantes agregadas o ya existentes.
	 *
	 * @example
	 * ```php
	 * $phpPath = __DIR__ . '/config.php';
	 *
	 * $variables = [
	 *     'MySQL_ADMIN' => [
	 *         'HOSTNAME' => 'localhost',
	 *         'USERNAME' => 'root',
	 *         'PASSWORD' => '123456',
	 *         'DATABASE' => 'mi_base',
	 *         'CHARSET'  => 'utf8mb4',
	 *         'PORT'     => 3306,
	 *     ],
	 *     'MySQL_1' => [
	 *         'HOSTNAME' => 'localhost',
	 *         'USERNAME' => 'usuario',
	 *         'PASSWORD' => '123456',
	 *         'DATABASE' => 'mi_base',
	 *         'CHARSET'  => 'utf8mb4',
	 *         'PORT'     => 3306,
	 *     ],
	 * ];
	 *
	 * $result = $ServerServer->writeConfigClassFile($phpPath, $variables);
	 *
	 * if ($result['success']) {
	 *     echo $result['message'];
	 * } else {
	 *     echo "Error: " . $result['message'];
	 * }
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
                $constBlock .= "    // Variables para MySQL\n";
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
                        // var_export genera un literal PHP válido y bien escapado (multibyte, comillas, contrabarras)
                        $exportedValue = var_export($value, true);
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
                $content .= "class ConfigDataBase{\n";

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
            if (!preg_match('/class\s+ConfigDataBase/i', $existingContent)) {

                $existingContent .= "\n\nclass ConfigDataBase{\n";

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

	/******************************************************************************/
    /**
	 * Lista el contenido de un directorio devolviendo información de cada entrada.
	 *
	 * La ruta se valida con FunctionsCommonFiles::safePath para prevenir path traversal.
	 * Se excluyen '.' y '..'.
	 *
	 * @param string $Ruta Ruta del directorio a listar.
	 * @param bool $SoloArchivos Si true, omite subdirectorios del resultado.
	 *
	 * @return array|string Arreglo de entradas [['nombre','tipo','peso','modificado'], ...] o mensaje de error.
	 *
	 * @example
	 * ```php
	 * $ServerServer->listarDirectorio('/var/www'); // Devuelve array de entradas
	 * ```
	 *
	 */
	public function listarDirectorio(string $Ruta, string $Root, bool $SoloArchivos = false): array|string {

		/********************** Validaciones   **********************/
		if ($Ruta === null || trim((string)$Ruta) === '') {
			return 'No ha ingresado una ruta';
		}
		// Validación de seguridad: path traversal
		$CommonFiles = new FunctionsCommonFiles();
		$RutaSegura  = $CommonFiles->safePath($Ruta, $Root);
		if ($RutaSegura['success'] === false || !is_dir($RutaSegura['data'])) {
			return 'La ruta no existe o no es un directorio';
		}

		/********************** Si todo esta ok **********************/
		$Entradas = [];
		$Items    = scandir($RutaSegura['data']);
		foreach ($Items as $Item) {
			if ($Item === '.' || $Item === '..') { continue; }
			$PathCompleto = $RutaSegura['data'].DIRECTORY_SEPARATOR.$Item;
			$Tipo         = is_dir($PathCompleto) ? 'dir' : 'file';
			if ($SoloArchivos && $Tipo === 'dir') { continue; }
			$Entradas[] = [
				'nombre'      => $Item,
				'tipo'        => $Tipo,
				'peso'        => $Tipo === 'file' ? filesize($PathCompleto) : 0,
				'modificado'  => date('Y-m-d H:i:s', filemtime($PathCompleto)),
			];
		}

		/********************** Retorno datos **********************/
		// Ordena por nombre
		usort($Entradas, fn($a,$b) => strcmp($a['nombre'], $b['nombre']));
		return $Entradas;

	}

    /******************************************************************************/
    /**
	 * Crea una copia de seguridad de un archivo con timestamp en el nombre.
	 *
	 * Copia archivo.ext → archivo.ext.YYYYMMDDHHIISS.bak. Si ya existe, agrega .1, .2...
	 * No sobrescribe archivos existentes.
	 *
	 * @param string $Ruta Ruta del archivo a respaldar.
	 * @param string|null $Sufijo Sufijo personalizado (por defecto timestamp YmdHis).
	 *
	 * @return array ['success'=>bool,'data'=>rutaDestino|error].
	 *
	 * @example
	 * ```php
	 * $ServerServer->backupArchivo('/etc/config.php');
	 * // Devuelve ['success'=>true,'data'=>'/etc/config.php.20261015143022.bak']
	 * ```
	 *
	 */
	public function backupArchivo(string $Ruta, ?string $Sufijo = null): array {

		/********************** Validaciones   **********************/
		if ($Ruta === null || trim((string)$Ruta) === '') {
			return ['success' => false, 'error' => 'No ha ingresado una ruta'];
		}
		if (!is_file($Ruta)) {
			return ['success' => false, 'error' => 'El archivo no existe'];
		}
		if (!is_readable($Ruta)) {
			return ['success' => false, 'error' => 'El archivo no es legible'];
		}
		$Dir = dirname($Ruta);
		if (!is_writable($Dir)) {
			return ['success' => false, 'error' => 'El directorio destino no es escribible'];
		}

		/********************** Si todo esta ok **********************/
		$SufijoFinal = $Sufijo ?? date('YmdHis');
		$Destino     = $Ruta.'.'.$SufijoFinal.'.bak';
		$Intento     = 0;
		while (file_exists($Destino)) {
			$Intento++;
			$Destino = $Ruta.'.'.$SufijoFinal.'.'.$Intento.'.bak';
		}
		$Ok = copy($Ruta, $Destino);
		if (!$Ok) {
			return ['success' => false, 'error' => 'No se pudo crear la copia de seguridad'];
		}

		/********************** Retorno datos **********************/
		return ['success' => true, 'data' => $Destino];

	}

	/******************************************************************************/
    /**
     * Verifica la capacidad de escritura de un directorio e intenta corregirla si es necesario.
     *
     * Comprueba si el directorio existe y si tiene permisos de escritura. En caso negativo,
     * intenta aplicar un cambio de modo (chmod) con los permisos especificados.
     *
     * @param string $directory Ruta absoluta del directorio a validar.
     * @param int $permission Máscara de permisos octal a aplicar en caso de fallo (por defecto 0755).
     *
     * @return array Resultado de la validación: ['success' => bool, 'message' => string].
	 *
	 * @example
	 * ```php
	 * $path = '/var/www/html/coreEngine/admin/storage';
	 *
	 * $result = $ServerServer->isWritableDirectory($path, 0775);
	 *
	 * if ($result['success']) {
	 *     echo $result['message'];
	 * } else {
	 *     echo "Error: " . $result['message'];
	 * }
	 * ```
	 *
     */
    public function isWritableDirectory(string $directory, int $permission = 0755): array {

        /********************** Validaciones   **********************/
        if ($directory === null || trim((string)$directory) === '') {
            return ['success' => false,'message' => "No ha ingresado la ruta del directorio."];
        }

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

    /******************************************************************************/
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
     * @return bool True si la escritura y eliminación del archivo de prueba fueron exitosas; False si la ruta está vacía o la escritura falla.
	 *
	 * @example
	 * ```php
	 * $escribible = $ServerServer->canWrite('/var/www/html/coreEngine/admin/storage');
	 * // Devuelve true si la ruta admite crear y borrar un archivo temporal; false en caso contrario
	 * ```
	 *
     */
    public function canWrite(string $path): bool {

        /********************** Validaciones   **********************/
        if ($path === null || trim((string)$path) === '') {
            return false;
        }

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

        /**********************  Retorno datos  **********************/
        // Si la escritura falló, se confirma la falta de permisos efectivos
        return false;

    }

    /******************************************************************************/
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
	 * $result = $ServerServer->ensurePermissions755('/var/www/html/coreEngine/admin/storage');
	 * // Devuelve ['success' => true, 'message' => 'Permisos correctos', 'current' => '0755', 'expected' => '0755'] cuando ya cumple el estándar
	 * ```
	 *
     */
    public function ensurePermissions755(string $path): array {

        /********************** Validaciones   **********************/
        if ($path === null || trim((string)$path) === '') {
            return ['success' => false,'message' => "No ha ingresado la ruta del directorio."];
        }

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

        /**********************  Retorno datos  **********************/
        // Error en la corrección, común en entornos con volúmenes restringidos o Docker
        return [
            "success" => false,
            "message" => "No se pudieron cambiar los permisos (posible restricción de Docker o permisos del sistema)",
            "current" => $currentPerms,
            "expected" => "0755"
        ];

    }


    /*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Privados                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
    /**
     * Aplica la zona horaria de Chile y retorna la fecha/hora actual en el formato indicado.
     *
     * Método privado que centraliza la configuración de zona horaria usada por los
     * métodos públicos de fecha/hora (fechaActual, horaActual, diaActual, etc.),
     * evitando duplicar date_default_timezone_set('America/Santiago') en cada uno.
     *
     * @param string $format Formato de fecha/hora PHP (ej: 'Y-m-d', 'H:i:s', 'Ymd').
     *
     * @return string Fecha/hora actual en el formato indicado.
     *
     * @example
     * ```php
     * // Uso interno: $this->now('Y-m-d'); // Devuelve 2026-10-16
     * ```
     */
    private function now(string $format): string {

        /********************** Si todo esta ok **********************/
        // Asegura la zona horaria de Chile antes de generar el valor
        date_default_timezone_set('America/Santiago');

        /********************** Retorno datos  **********************/
        // Retorna la fecha/hora con el formato solicitado
        return date($format);

    }


}
