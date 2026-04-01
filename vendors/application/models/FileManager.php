<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FileManager {
    /**
     * Class FileManager
     *
     * Gestiona la subida, validación y eliminación de archivos en el servidor.
     *
     * Mejoras aplicadas:
     *  - Validación de MIME type real con finfo (evita spoofing del header HTTP)
     *  - Sanitización de nombres de archivo (previene path traversal)
     *  - Bloqueo de extensiones peligrosas (.php, .sh, .exe, etc.)
     *  - Uso controlado del operador @ solo donde el error es manejado explícitamente
     *  - Uso de match() en lugar de switch/case con break redundantes
     *  - Tipado estricto en parámetros y retornos
     *  - Extracción de lógica duplicada a métodos privados (DRY)
     *  - Validaciones de entradas vacías
     *  - Permisos de directorio corregidos (0755 en lugar de 0777)
     *  - Manejo de errores con excepciones
     */
    /*******************************************************************************************************************/
	/*                                                                                                                 */
    /*                                           Constantes de clase                                                   */
	/*                                                                                                                 */
    /*******************************************************************************************************************/

    /******************************************************************************************/
    // Mapa de tipos MIME agrupados por categoría.
    private const MIME_TYPES = [
        'word' => [
            'application/msword',
            'application/vnd.ms-word',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/x-abiword',
            'application/vnd.oasis.opendocument.text',
        ],
        'excel' => [
            'application/msexcel',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/csv',
            'application/vnd.oasis.opendocument.spreadsheet',
        ],
        'powerpoint' => [
            'application/mspowerpoint',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.oasis.opendocument.presentation',
        ],
        'pdf' => [
            'application/pdf',
            'application/octet-stream',
            'application/x-real',
            'application/vnd.adobe.xfdf',
            'application/vnd.fdf',
            'binary/octet-stream',
            'application/epub+zip',
        ],
        'image' => [
            'image/jpg',
            'image/jpeg',
            'image/gif',
            'image/png',
            'image/bmp',
            'image/webp',
            'image/x-ms-bmp',
        ],
        'txt' => [
            'text/plain',
            'text/richtext',
            'application/rtf',
            'text/rtf',
        ],
        'zip' => [
            'application/x-zip-compressed',
            'application/zip',
            'multipart/x-zip',
            'application/x-7z-compressed',
            'application/x-rar-compressed',
            'application/x-rar',
            'application/vnd.rar',
            'application/gzip',
            'application/x-gzip',
            'application/x-gtar',
            'application/x-tgz',
            'application/octet-stream',
            'application/x-bzip',
            'application/x-bzip2',
        ],
        'video' => [
            'video/x-msvideo',
            'video/mpeg',
            'video/ogg',
            'video/webm',
            'application/mp4',
            'video/mp4',
        ],
        'music' => [
            'audio/aac',
            'audio/midi',
            'audio/ogg',
            'audio/x-wav',
            'audio/webm',
            'audio/wav',
            'audio/mpeg',
        ],
    ];

    /******************************************************************************************/
    // ARCHIVOS SENSIBLES A EXCLUIR: Incluye configuraciones, credenciales, backups, logs, etc.
    private const EXCLUDED_NAMES = [
        '.htaccess', '.htpasswd', '.env', '.env.local', '.env.production', '.env.dev',
        '.gitignore', '.gitattributes', 'config.php', 'configuration.php', 'settings.php',
        'web.config', 'composer.json', 'composer.lock', 'package.json', 'package-lock.json',
        'yarn.lock', 'Dockerfile', 'docker-compose.yml', 'phpunit.xml', 'README.md',
        'LICENSE', 'error_log', 'access.log'
    ];

    /******************************************************************************************/
    // EXTENSIONES PROHIBIDAS: Scripts ejecutables, configs, backups y archivos peligrosos
    private const BLOCKED_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'phtml', 'phar',        // Scripts backend
        'ini', 'env', 'conf', 'config', 'yaml', 'yml', 'toml', // Configuración / entorno
        'log',                                                 // Logs / debug
        'sh', 'bash', 'zsh', 'bat', 'cmd', 'ps1',              // Shell / ejecución
        'exe', 'bin', 'run',                                   // Binarios / ejecutables
        'sql', 'bak', 'old', 'backup', 'dump',                 // Backups / dumps
        'cgi', 'pl', 'py', 'rb', 'jsp', 'asp', 'aspx'          // Otros potencialmente peligrosos
    ];

    /******************************************************************************************/
    // CARPETAS SENSIBLES: Carpetas internas del sistema, dependencias y control de versiones
    private const EXCLUDED_FOLDERS = [
        '.git', '.svn', '.hg',                            // Control de versiones
        'node_modules', 'vendor',                         // Dependencias
        '.idea', '.vscode',                               // Configuración / entorno
        'bin', 'etc', 'var', 'proc', 'sys', 'dev', 'tmp', // Sistema / servidor
        'logs', 'log', 'cache', 'storage',                // Logs / cache
        'backup', 'backups',                              // Backups
        '.docker', '.github',                             // Docker / DevOps
        'tests', 'test'                                   // Testing
    ];

    /******************************************************************************************/
    // Mapa MIME → extensión
    private const MIME_TO_EXTENSION = [

        // ── Word ──────────────────────────────────────────────────────────────────
        'application/msword'                                                        => 'doc',
        'application/vnd.ms-word'                                                   => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => 'docx',
        'application/x-abiword'                                                     => 'abw',   // AbiWord — procesador de texto libre
        'application/vnd.oasis.opendocument.text'                                   => 'odt',   // OpenDocument Text (LibreOffice Writer)

        // ── Excel ─────────────────────────────────────────────────────────────────
        'application/msexcel'                                                       => 'xls',
        'application/vnd.ms-excel'                                                  => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'         => 'xlsx',
        'text/csv'                                                                  => 'csv',
        'application/vnd.oasis.opendocument.spreadsheet'                            => 'ods',   // OpenDocument Spreadsheet (LibreOffice Calc)

        // ── PowerPoint ────────────────────────────────────────────────────────────
        'application/mspowerpoint'                                                  => 'ppt',
        'application/vnd.ms-powerpoint'                                             => 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        'application/vnd.oasis.opendocument.presentation'                           => 'odp',   // OpenDocument Presentation (LibreOffice Impress)

        // ── PDF y documentos ──────────────────────────────────────────────────────
        'application/pdf'                                                           => 'pdf',
        'application/vnd.adobe.xfdf'                                                => 'xfdf',  // XML Forms Data Format (Adobe Acrobat)
        'application/vnd.fdf'                                                       => 'fdf',   // Forms Data Format (Adobe Acrobat)
        'application/epub+zip'                                                      => 'epub',  // Libro electrónico

        // ── Imágenes ──────────────────────────────────────────────────────────────
        'image/png'                                                                 => 'png',
        'image/jpg'                                                                 => 'jpg',   // Alias no estándar de image/jpeg
        'image/jpeg'                                                                => 'jpg',
        'image/gif'                                                                 => 'gif',
        'image/webp'                                                                => 'webp',
        'image/bmp'                                                                 => 'bmp',
        'image/x-ms-bmp'                                                            => 'bmp',   // Alias Windows de image/bmp

        // ── Texto ─────────────────────────────────────────────────────────────────
        'text/plain'                                                                => 'txt',
        'text/richtext'                                                             => 'rtf',
        'application/rtf'                                                           => 'rtf',
        'text/rtf'                                                                  => 'rtf',

        // ── Comprimidos ───────────────────────────────────────────────────────────
        'application/x-zip-compressed'                                              => 'zip',   // Alias no estándar de application/zip
        'application/zip'                                                           => 'zip',
        'multipart/x-zip'                                                           => 'zip',   // Alias antiguo
        'application/x-7z-compressed'                                               => '7z',
        'application/x-rar-compressed'                                              => 'rar',   // Alias obsoleto
        'application/x-rar'                                                         => 'rar',
        'application/vnd.rar'                                                       => 'rar',   // MIME oficial registrado en IANA
        'application/gzip'                                                          => 'gz',
        'application/x-gzip'                                                        => 'gz',    // Alias legacy de application/gzip
        'application/x-gtar'                                                        => 'tar',   // GNU tar sin comprimir
        'application/x-tgz'                                                         => 'tar.gz',// GNU tar comprimido con gzip
        'application/x-bzip'                                                        => 'bz',
        'application/x-bzip2'                                                       => 'bz2',

        // ── Vídeo ─────────────────────────────────────────────────────────────────
        'video/x-msvideo'                                                           => 'avi',
        'video/mpeg'                                                                => 'mpeg',  // Puede ser .mpeg o .mpg — se usa la forma larga por convención
        'video/ogg'                                                                 => 'ogv',   // Ogg Video (distinto de audio/ogg → .oga)
        'video/webm'                                                                => 'webm',
        'video/mp4'                                                                 => 'mp4',

        // ── Audio ─────────────────────────────────────────────────────────────────
        'audio/aac'                                                                 => 'aac',
        'audio/midi'                                                                => 'midi',
        'audio/ogg'                                                                 => 'oga',   // Ogg Audio (distinto de video/ogg → .ogv)
        'audio/x-wav'                                                               => 'wav',   // Alias legacy de audio/wav
        'audio/webm'                                                                => 'weba',  // WebM Audio — extensión oficial según IANA
        'audio/wav'                                                                 => 'wav',
        'audio/mpeg'                                                                => 'mp3',

    ];


    /*******************************************************************************************************************/
	/*                                                                                                                 */
    /*                                                Instancias                                                       */
	/*                                                                                                                 */
    /*******************************************************************************************************************/

    private FunctionsCommonData $CommonData;
    private ?\finfo $finfo = null;
    private static array $pathCache = [];

    public function __construct() {
        $this->CommonData = new FunctionsCommonData();
    }

    /*******************************************************************************************************************/
	/*                                                                                                                 */
    /*                                                  Métodos públicos                                               */
	/*                                                                                                                 */
    /*******************************************************************************************************************/

    /******************************************************************************************/
    public function validateFiles(array $SIS_FILES, array $arrArchivos, array $PostData = []): array {
        /**
         * Valida los archivos antes de subirlos al servidor.
         *
         * @param array      $SIS_FILES   Equivalente a $_FILES
         * @param array      $arrArchivos Configuración de archivos a validar
         * @param array      $PostData    Datos POST adicionales
         * @return array                  Array con 'success' y 'message', o array de errores indexados
         */

        // Si no hay archivos
        if (empty($arrArchivos)) { return ['success' => true,  'data' => true];}

        // Variables
        $errors = [];

        // Se recorren archivos
        foreach ($arrArchivos as $archivo) {
            // Solo se valida si el identificador existe en PostData y NO es Base64
            if (!isset($PostData[$archivo['Identificador']]) || $archivo['Base64'] !== false) {
                continue;
            }

            // Variable
            $id = $archivo['Identificador'];

            // Verificar existencia del archivo en el request
            if (empty($SIS_FILES[$id])) {
                $errors[] = ['success' => false, 'message' => $id . ' es obligatorio'];
                continue;
            }

            // Verificar errores PHP de subida
            if ($SIS_FILES[$id]['error'] > 0) {
                $errors[] = ['success' => false, 'message' => $this->uploadPHPError($SIS_FILES[$id]['error'])];
                continue;
            }

            // Verificar extensión peligrosa
            if ($this->hasForbiddenExtension($SIS_FILES[$id]['name'])) {
                $errors[] = ['success' => false, 'message' => 'Extensión de archivo no permitida por seguridad'];
                continue;
            }

            // Verificar tipo MIME real (desde el archivo temporal, no del header)
            $allowedMimes = $this->buildAllowedMimes($archivo['ValidarTipo']);
            $realMime     = $this->getRealMimeType($SIS_FILES[$id]['tmp_name']);
            // Verificacion
            if (!in_array($realMime, $allowedMimes, true)) {
                $errors[] = ['success' => false, 'message' => 'Tipo de archivo no permitido'];
                continue;
            }

            // Verificar peso máximo (en megas)
            if ($SIS_FILES[$id]['size'] >= ($archivo['ValidarPeso'] * 1048576)) {
                $errors[] = ['success' => false, 'message' => 'Archivo excede el tamaño permitido'];
                continue;
            }

            // Verificar si el archivo ya existe en el servidor
            $nombreArchivo = $this->buildFileName($archivo, $SIS_FILES[$id]['name']);
            $rutaArchivo   = $this->buildFilePath($archivo) . $nombreArchivo;
            // Verificacion
            if (file_exists($rutaArchivo)) {
                $errors[] = ['success' => false, 'message' => 'El archivo ' . $SIS_FILES[$id]['name'] . ' ya existe en el servidor'];
            }
        }

        // Retorno de datos
        return empty($errors) ? ['success' => true,  'message' => true] : $errors;
    }

    /******************************************************************************************/
    public function uploadFile(array $SIS_FILES, array $arrArchivos, array $PostData = []): array {
        /**
         * Sube los archivos al servidor.
         *
         * @param array $SIS_FILES   Equivalente a $_FILES
         * @param array $arrArchivos Configuración de archivos a subir
         * @param array $PostData    Datos POST adicionales (para Base64)
         * @return array {
         *   Nombres  : string  Columnas concatenadas (ej: ",imagen,doc")
         *   Archivos : string  Valores concatenados  (ej: ",'file.png'")
         *   Update   : string  Expresiones SQL UPDATE (ej: ",col = 'file.png'")
         *   success  : bool
         *   message  : string
         * }
         */

        // Variables
        $Data = [
            'Nombres'  => '',
            'Archivos' => '',
            'Update'   => '',
            'success'  => false,
            'message'  => '',
        ];

        // Se recorren archivos
        foreach ($arrArchivos as $archivo) {
            if ($archivo['Base64'] === true) {
                $this->handleBase64Upload($archivo, $PostData, $Data);
            } else {
                $this->handleNormalUpload($archivo, $SIS_FILES, $Data);
            }
        }

        // Retorno de datos
        return $Data;
    }

    /******************************************************************************************/
    public function deleteFile(string $SIS_File, string $SIS_Carpeta): bool {
        /**
         * Elimina un archivo del servidor.
         *
         * @param string $SIS_File    Nombre del archivo
         * @param string $SIS_Carpeta Subcarpeta donde se encuentra
         * @return bool
         */

        // Si no hay archivos
        if (empty($SIS_File)) { return false;}

        // Se obtiene la ruta
        $rutaArchivo = $this->buildFilePath(['SubCarpeta' => $SIS_Carpeta]) . $SIS_File;

        // Se verifica si existe y se borra los datos
        if (file_exists($rutaArchivo)) {
            return unlink($rutaArchivo);
        }

        // El archivo no existía en el servidor, se considera operación exitosa
        // (evita errores en el queryBuilder cuando el archivo ya fue eliminado previamente)
        return true;
    }

    /******************************************************************************************/
    public function deleteFilesMassive(string $SIS_Files, string $SIS_Carpeta, array $Result): bool {
        /**
         * Elimina múltiples archivos del servidor.
         *
         * @param string $SIS_Files   Lista de identificadores separados por coma
         * @param string $SIS_Carpeta Subcarpeta donde se encuentran
         * @param array  $Result      Mapa identificador => nombre de archivo
         * @return bool
         */

        // Si no hay archivos
        if (empty($SIS_Files) || empty($Result)) {return false;}

        // Se obtienen datos y rutas
        $arrFiles    = $this->CommonData->parseDataCommas($SIS_Files);
        $rutaArchivo = $this->buildFilePath(['SubCarpeta' => $SIS_Carpeta]);

        // Se recorren los archivos y se eliminan
        foreach ($arrFiles as $file) {
            $file = preg_replace('/[^a-zA-Z0-9_]/', '', $file);
            if (!empty($Result[$file]) && file_exists($rutaArchivo . $Result[$file])) {
                unlink($rutaArchivo . $Result[$file]);
            }
        }

        // Retorno de datos
        return true;
    }

    /******************************************************************************************/
    public function fileExplorer(array $Data = []): array {
        /**
         * Explorador de archivos seguro.
         *
         * Orquesta las responsabilidades delegadas a métodos privados:
         * 1. Resuelve y valida la ruta segura
         * 2. Construye los MIME permitidos
         * 3. Lista y filtra el contenido del directorio
         *
         * @param array $Data Parámetros de entrada:
         *  - route : ruta encriptada base
         *  - path  : subruta desde el frontend (ofuscada)
         *  - tipos : tipos de archivos permitidos (ej: "image,pdf") o "all"
         *
         * @return array Lista de archivos y carpetas con metadata
         */

        // Resuelve y valida la ruta del explorador (anti path traversal)
        $fullPath = $this->resolveExplorerPath($Data);

        // Construye la lista de MIME permitidos según los tipos configurados
        $allowedMimes = $this->buildAllowedMimes(
            $Data['tipos'] !== 'all'
                ? $Data['tipos']
                : 'word,excel,powerpoint,pdf,image,txt,zip,video,music'
        );

        // Lista, filtra y retorna el contenido del directorio
        return $this->buildFileList($fullPath, $allowedMimes);
    }

    /******************************************************************************************/
    public function createFolder(array $PostData = []): array {
        /**
         * CREACIÓN SEGURA DE CARPETAS (HARDENING)
         *
         * Este método crea una carpeta dentro de una ruta base controlada,
         * aplicando múltiples validaciones de seguridad y consistencia:
         *
         * Medidas implementadas:
         * - Validación de existencia de parámetros (path y name)
         * - Sanitización de path y nombre
         * - Normalización de rutas (evita doble / o separadores inválidos)
         * - Prevención de Path Traversal
         * - Validación de permisos de escritura
         * - Manejo de errores detallado en mkdir
         * - Fallback en caso de fallo
         *
         * @param array $PostData Parámetros de entrada:
         *  - base : ruta base (obligatoria, controlada por backend)
         *  - path : subruta dentro de la base
         *  - name : nombre de la nueva carpeta
         *
         * @return array Resultado:
         *  - success : bool
         *  - message : string (solo en error)
         */

        /*******************************************************************/
        // VALIDACIÓN DE PARÁMETROS DE ENTRADA
        /*******************************************************************/
        if (empty($PostData['name'])) {
            return ["success" => false, "message" => "Nombre no definido"];
        }

        /*******************************************************************/
        // CONSTRUCCIÓN SEGURA DE RUTA (SIN DOBLE SLASH)
        /*******************************************************************/
        $ROOT_PATH     = ConfigAPP::APP['uploadFolder'];
        $ROOT_PATH    .= isset($PostData['SubRoute']) ? '/'.trim($this->sanitizePath($PostData['SubRoute']), '/') : '';
        $relativePath  = isset($PostData['path']) ? '/'.trim($this->sanitizePath($PostData['path']), '/') : '';
        $relativePath .= isset($PostData['name']) ? '/'.trim($this->sanitizeFolderName($PostData['name']), '/') : '';

        /*******************************************************************/
        // VALIDACIÓN DE SEGURIDAD (ANTI PATH TRAVERSAL)
        /*******************************************************************/
        // Normaliza posibles dobles slashes (extra seguridad)
        $fullPath = preg_replace('#/+#', '/', $ROOT_PATH.$relativePath);

        /*******************************************************************/
        // VALIDACIÓN DE EXISTENCIA
        /*******************************************************************/
        if (is_dir($fullPath)) {
            return [
                "success" => false,
                "message" => "La carpeta ya existe"
            ];
        }

        /*******************************************************************/
        // VALIDACIÓN DE PERMISOS CARPETA UPLOAD
        /*******************************************************************/
        $result = $this->ensurePermissions755($ROOT_PATH);
        if (!$result['success']) {
            return [
                "success" => false,
                "message" => $result['message'] . " (actual: {$result['current']})"
            ];
        }
        if (!$this->canWrite($ROOT_PATH)) {
            return [
                "success" => false,
                "message" => "No hay permisos de escritura en ".$ROOT_PATH
            ];
        }

        /*******************************************************************/
        // CREACIÓN DE CARPETA (MANEJO DE ERRORES DETALLADO)
        /*******************************************************************/
        return $this->ensureDirectoryExists($fullPath);

    }


    /*******************************************************************************************************************/
	/*                                                                                                                 */
    /*                                              Métodos Publicos                                                   */
	/*                                                                                                                 */
    /*******************************************************************************************************************/

    /******************************************************************************************/
    // Evitar Directory Traversal (../)
    public function sanitizePath(string $path): string {
        $decoded = rawurldecode($path);
        $clean   = preg_replace('/\.{2,}/', '', $decoded); // elimina ".."
        return preg_replace('/[^a-zA-Z0-9\/\-_]/', '', $clean);
    }

    /******************************************************************************************/
    // Eliminar caracteres especiales para nombres de carpetas
    public function sanitizeFolderName(string $name): string {
        return preg_replace('/[^a-zA-Z0-9_\-]/', '', $name);
    }


    /*******************************************************************************************************************/
	/*                                                                                                                 */
    /*                                              Métodos privados                                                   */
	/*                                                                                                                 */
    /*******************************************************************************************************************/

    /******************************************************************************************/
    // Maneja la subida de un archivo codificado en Base64.
    private function handleBase64Upload(array $archivo, array $PostData, array &$Data): void {
        /**
         * Maneja la subida de archivos en formato Base64.
         *
         * @param array $archivo   Configuración del archivo (identificador, nombre, ruta, etc.)
         * @param array $PostData  Datos recibidos (por ejemplo $_POST)
         * @param array &$Data     Referencia al array donde se almacenan los resultados
         */

        // Obtiene el identificador único del archivo (clave en el POST)
        $id = $archivo['Identificador'];

        // Si no existe el dato en el POST, no se procesa
        if (empty($PostData[$id])) {
            // Mensaje
            $Data['success'] = false;
            $Data['message'] = 'No hay archivo';
            // Se detiene proceso
            return;
        }

        // Base64 incrementa ~33% el tamaño del binario original (4 bytes encoded = 3 bytes raw)
        // Factor 1.37 agrega margen adicional sobre el 1.33 teórico
        $maxBase64Bytes = ($archivo['ValidarPeso'] ?? 10) * 1048576 * 1.37;
        if (strlen($PostData[$id]) > $maxBase64Bytes) {
            // Mensaje
            $Data['success'] = false;
            $Data['message'] = 'Archivo excede el tamaño permitido';
            // Se detiene proceso
            return;
        }

        // Limpiar prefijo data URI dinámicamente (cualquier tipo MIME)
        $rawBase64 = $this->cleanBase64Payload($PostData[$id]);

        // Decodifica el contenido Base64 a binario
        // El segundo parámetro en true asegura validación estricta
        // Si la decodificación falla, se detiene el proceso
        $dIMG = base64_decode($rawBase64, true);
        if ($dIMG === false) {
            // Mensaje
            $Data['success'] = false;
            $Data['message'] = 'El contenido Base64 no es válido';
            // Se detiene proceso
            return;
        }

        // Verificar MIME del binario decodificado antes de guardar
        $realMime = $this->getFinfo()->buffer($dIMG); // ← buffer() inspecciona el contenido en memoria

        // Verificar si esta dentro de los Mime permitidos
        $allowed  = $this->buildAllowedMimes($archivo['ValidarTipo'] ?? 'image');
        if (!in_array($realMime, $allowed, true)) {
            // Mensaje
            $Data['success'] = false;
            $Data['message'] = 'Tipo de archivo no permitido';
            // Se detiene proceso
            return;
        }

        // Resolver extensión desde el MIME real detectado (no desde el cliente)
        $ext = $this->resolveExtensionFromMime($realMime);
        if ($ext === null) {
            $Data['success'] = false;
            $Data['message'] = 'No se pudo determinar la extensión del archivo';
            return;
        }

        // Construir nombre final usando la extensión real del binario:
        // - Si viene definido, se usa ese nombre
        // - Si no, se genera uno con sufijo + timestamp
        $nombreArchivo = !empty($archivo['NombreArchivo'])
            ? $archivo['NombreArchivo'] . '.' . $ext
            : ($archivo['SufijoArchivo'] ?? '') . time() . '.' . $ext;

        // Sanitiza el nombre del archivo para evitar caracteres peligrosos
        $nombreArchivo = $this->sanitizeFileName($nombreArchivo);

        // Construye la ruta completa donde se guardará el archivo
        $rutaArchivo = $this->buildFilePath($archivo);

        // Delegar guardado al método compartido
        $this->saveFileToDisk($rutaArchivo, $nombreArchivo, $dIMG, true, $Data, $id);

    }

    /******************************************************************************************/
    // Maneja la subida de un archivo normal (multipart/form-data).
    private function handleNormalUpload(array $archivo, array $SIS_FILES, array &$Data): void {
        /**
         * Maneja la subida de un archivo normal (multipart/form-data).
         *
         * @param array $archivo    Configuración del archivo (identificador, reglas, etc.)
         * @param array $SIS_FILES  Array de archivos subidos (equivalente a $_FILES)
         * @param array &$Data      Referencia al array donde se almacenan los resultados
         */

        // Obtiene el identificador único del archivo (clave en $_FILES)
        $id = $archivo['Identificador'];

        // Verifica si el archivo fue enviado correctamente
        // Si no existe el nombre del archivo, se detiene el proceso
        if (empty($SIS_FILES[$id]['name'])) {
            // Mensaje
            $Data['success'] = false;
            $Data['message'] = 'No hay archivo';
            // Se detiene proceso
            return;
        }

        // Construye el nombre final del archivo:
        // - Usa una función personalizada (puede incluir prefijos, timestamps, etc.)
        // - Luego sanitiza para evitar caracteres peligrosos o inválidos
        $nombreArchivo = $this->sanitizeFileName(
            $this->buildFileName($archivo, $SIS_FILES[$id]['name'])
        );

        // Construye la ruta donde se almacenará el archivo
        $rutaArchivo = $this->buildFilePath($archivo);

        // Delegar guardado al método compartido (tmp_name como contenido)
        $this->saveFileToDisk($rutaArchivo, $nombreArchivo, $SIS_FILES[$id]['tmp_name'], false, $Data, $id);

    }

    /******************************************************************************************/
    // Guarda un archivo en disco, verificando existencia y directorio previamente.
    private function saveFileToDisk(string $rutaArchivo,string $nombreArchivo,string $contenido,bool   $isBase64,array  &$Data,string $id): void {
        /**
         * Guarda un archivo en disco, verificando existencia y directorio previamente.
         * Centraliza la lógica común entre handleBase64Upload y handleNormalUpload.
         *
         * @param string   $rutaArchivo   Ruta del directorio destino (con slash final)
         * @param string   $nombreArchivo Nombre final del archivo
         * @param string   $contenido     Contenido binario (Base64 decodificado) o ruta tmp_name
         * @param bool     $isBase64      true = file_put_contents, false = move_uploaded_file
         * @param array    &$Data         Referencia al array de resultados
         * @param string   $id            Identificador del archivo (clave en $Data)
         */

        // Evita sobrescribir archivos existentes en el servidor
        if (file_exists($rutaArchivo . $nombreArchivo)) {
            // Mensaje
            $Data['success'] = false;
            $Data['message'] = 'El archivo que intenta subir ya existe';
            // Se detiene proceso
            return;
        }

        // Asegurar que el directorio exista (lo crea si es necesario)
        $response = $this->ensureDirectoryExists($rutaArchivo);
        if ($response['success'] === false) {
            // Mensaje
            $Data['success'] = false;
            $Data['message'] = 'El directorio donde intenta subir el archivo no existe';
            // Se detiene proceso
            return;
        }

        // Guardar el archivo según su origen:
        // - Base64: escribe el binario ya decodificado en memoria (file_put_contents)
        // - Normal: mueve el archivo desde la ubicación temporal del servidor (move_uploaded_file)
        $saved = $isBase64
            ? file_put_contents($rutaArchivo . $nombreArchivo, $contenido) !== false
            : move_uploaded_file($contenido, $rutaArchivo . $nombreArchivo);

        // Registrar resultado en $Data si se guardó correctamente
        if ($saved) {
            $this->appendToData($Data, $id, $nombreArchivo);
        }
    }

    /******************************************************************************************/
    // Construye el nombre final del archivo según la configuración.
    private function buildFileName(array $archivo, string $originalName): string {
        /**
         * Construye el nombre final del archivo según la configuración definida.
         *
         * Reglas:
         * - Si existe 'NombreArchivo', se usa como nombre base y se respeta la extensión original.
         * - Si existe 'SufijoArchivo', se antepone al nombre original.
         * - Si no hay configuración, se mantiene el nombre original.
         *
         * @param array  $archivo      Configuración del archivo (NombreArchivo, SufijoArchivo, etc.)
         * @param string $originalName Nombre original del archivo subido (incluye extensión)
         *
         * @return string Nombre final del archivo
         */

        // Caso 1: Se define un nombre fijo para el archivo
        // - Se mantiene la extensión original del archivo subido
        if (!empty($archivo['NombreArchivo'])) {
            $ext = pathinfo($originalName, PATHINFO_EXTENSION);
            return $ext !== ''
                ? $archivo['NombreArchivo'] . '.' . $ext
                : $archivo['NombreArchivo'];
        }

        // Caso 2: Se define un sufijo/prefijo para el archivo
        // - Se antepone al nombre original completo
        if (!empty($archivo['SufijoArchivo'])) {
            return $archivo['SufijoArchivo'] . $originalName;
        }

        // Caso 3: No hay configuración adicional
        // - Se devuelve el nombre original tal cual
        return $originalName;
    }

    /******************************************************************************************/
    // Construye la ruta de destino según la configuración.
    private function buildFilePath(array $archivo): string {
        /**
         * Construye la ruta de destino donde se almacenará el archivo.
         *
         * Reglas:
         * - Parte desde la carpeta base definida en la configuración global.
         * - Si se especifica una subcarpeta, se agrega a la ruta final.
         * - Se eliminan intentos básicos de path traversal (../) por seguridad.
         *
         * @param array $archivo Configuración del archivo (incluye posible subcarpeta)
         *
         * @return string Ruta final donde se guardará el archivo
         */

        // Obtiene el valor de la subcarpeta desde el arreglo `$archivo`, si no existe, se asigna una cadena vacía por defecto.
        $sub = $archivo['SubCarpeta'] ?? '';

        // Verifica si la ruta ya fue previamente resuelta y almacenada en caché.
        if (!isset(self::$pathCache[$sub])) {

            // Obtiene la carpeta base de uploads desde la configuración de la aplicación
            $path = ConfigAPP::APP['uploadFolder'];

            // Si se define una subcarpeta en la configuración del archivo
            if (!empty($archivo['SubCarpeta'])) {

                // Resuelve la ruta absoluta real, eliminando ".." y enlaces simbólicos.
                // Si la subcarpeta intenta escapar del uploadFolder, realpath() retornará
                // la ruta real del sistema — validar contra ROOT si se requiere confinamiento estricto.
                $ROOT        = realpath(ConfigAPP::APP['uploadFolder']);
                $safeSubPath = realpath($ROOT . '/' . $archivo['SubCarpeta']);

                // Agrega la subcarpeta a la ruta base
                $path = $safeSubPath . '/';
            }

            // Se almacena en el caché.
            self::$pathCache[$sub] = $path;
        }

        // Retorna la ruta desde caché (evitando reprocesos).
        return self::$pathCache[$sub];

    }

    /******************************************************************************************/
    // Sanitiza el nombre de archivo para prevenir path traversal e inyecciones.
    private function sanitizeFileName(string $filename): string {
        /**
         * Sanitiza el nombre de archivo para prevenir vulnerabilidades como:
         * - Path Traversal (ej: ../../archivo.php)
         * - Inyección de caracteres peligrosos
         * - Uso de nombres no válidos en el sistema de archivos
         *
         * Reglas:
         * - Se eliminan rutas y se conserva solo el nombre del archivo.
         * - Se reemplazan caracteres no permitidos por "_".
         *
         * @param string $filename Nombre original del archivo
         *
         * @return string Nombre de archivo seguro
         */

        // Elimina cualquier componente de ruta (previene path traversal)
        // Ejemplo: "../../etc/passwd" → "passwd"
        $filename = basename($filename);

        // Reemplaza cualquier carácter que no sea:
        // letras (a-z, A-Z), números (0-9), punto (.), guion (-) o guion bajo (_)
        // por un guion bajo "_"
        // Esto evita inyección de caracteres especiales o no válidos
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

        // Retorna el nombre sanitizado
        return $filename;
    }

    /******************************************************************************************/
    // Verifica si la extensión del archivo está en la lista de extensiones prohibidas.
    private function hasForbiddenExtension(string $filename): bool {
        /**
         * Verifica si la extensión del archivo está dentro de una lista de extensiones prohibidas.
         *
         * Esto ayuda a prevenir la subida de archivos potencialmente peligrosos
         * como scripts ejecutables (ej: .php, .exe, .sh, etc.).
         *
         * @param string $filename Nombre del archivo (puede incluir ruta, pero solo se evalúa la extensión)
         *
         * @return bool TRUE si la extensión está prohibida, FALSE en caso contrario
         */

        // Obtiene la extensión del archivo y la normaliza a minúsculas
        // Ejemplo: "imagen.PNG" → "png"
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Verifica si la extensión está en la lista de extensiones prohibidas
        // strict = true evita comparaciones débiles (ej: "0" == 0)
        return in_array($ext, self::BLOCKED_EXTENSIONS, true);
    }

    /******************************************************************************************/
    // Obtiene el tipo MIME real del archivo usando finfo (no el reportado por el cliente).
    private function getRealMimeType(string $tmpPath): string {
        /**
         * Obtiene el tipo MIME real de un archivo utilizando la extensión Fileinfo de PHP.
         *
         * A diferencia del MIME enviado por el cliente (ej: $_FILES['type']),
         * este método inspecciona el contenido real del archivo, evitando
         * falsificaciones (ej: subir un .php disfrazado como .jpg).
         *
         * @param string $tmpPath Ruta temporal del archivo (ej: $_FILES['tmp_name'])
         *
         * @return string Tipo MIME detectado (ej: "image/png") o cadena vacía si falla
         */

        // Obtiene el MIME real del archivo desde su contenido
        // Si falla, retorna false, por lo que usamos operador ternario para asegurar string
        $mime = $this->getFinfo()->file($tmpPath);

        // Retorna el MIME detectado o string vacío si no se pudo determinar
        return $mime ?: '';
    }

    /******************************************************************************************/
    // Construye el array de tipos MIME permitidos a partir de una cadena de categorías separadas por comas.
    private function buildAllowedMimes(string $tipos): array {
        /**
         * Construye un array de tipos MIME permitidos a partir de una cadena
         * de categorías separadas por comas.
         *
         * Ejemplo:
         * Entrada: "image,document"
         * Salida: ["image/png", "image/jpeg", "application/pdf", ...]
         *
         * Reglas:
         * - Convierte la cadena en un array de categorías.
         * - Busca cada categoría en una constante de tipos MIME definidos.
         * - Combina todos los MIME encontrados en un solo array.
         *
         * @param string $tipos Cadena de categorías separadas por comas
         *
         * @return array Lista de tipos MIME permitidos
         */

        // Convierte la cadena en un array
        // Ejemplo: "image,document" → ["image", "document"]
        $arrTipos = $this->CommonData->parseDataCommas($tipos);

        // Inicializa el array de MIME permitidos
        $allowedMimes = [];

        // Recorre cada categoría solicitada
        foreach ($arrTipos as $tipo) {

            // Verifica si la categoría existe en la constante MIME_TYPES
            // Esto evita errores por categorías no definidas
            if (isset(self::MIME_TYPES[$tipo])) {

                // Mezcla (merge) los MIME de esa categoría al array final
                // Ejemplo: "image" → ["image/png", "image/jpeg", ...]
                $allowedMimes = array_unique(array_merge($allowedMimes, self::MIME_TYPES[$tipo]));
            }
        }

        // Retorna el listado final de tipos MIME permitidos
        return $allowedMimes;
    }

    /******************************************************************************************/
    // Crea el directorio si no existe, con permisos seguros (0755).
    private function ensureDirectoryExists(string $path): array {
        /**
         * Crea el directorio si no existe, utilizando permisos seguros.
         *
         * Reglas:
         * - Verifica si el directorio ya existe antes de intentar crearlo.
         * - Si no existe, lo crea de forma recursiva.
         * - Si la creación falla, lanza una excepción para evitar errores silenciosos.
         *
         * @param string $path Ruta del directorio a verificar/crear
         */

        // Verifica si la ruta ya existe y es un directorio válido
        if (!is_dir($path)) {

            // Intenta crear el directorio:
            // - 0755: permisos seguros (lectura/ejecución para todos, escritura solo propietario)
            // - true: permite crear directorios anidados (recursivo)
            try {
                $created = mkdir($path, 0755, true);

                if ($created) {
                    return [
                        "success" => true,
                        "message" => "Carpeta creada correctamente"
                    ];
                }

                // Fallback: mkdir devolvió false sin excepción
                return [
                    "success" => false,
                    "message" => "No se pudo crear el directorio (mkdir retornó false)"
                ];

            } catch (\Throwable $e) {

                // Manejo de errores reales del sistema (permisos, rutas, etc.)
                return [
                    "success" => false,
                    "message" => "Error al crear carpeta: " . $e->getMessage()
                ];

            }
        // Si el directorio ya existe
        }else{
            return ['success' => true, 'message' => 'El directorio ya existe'];
        }
    }

    /******************************************************************************************/
    // Agrega los datos del archivo subido al array de resultados.
    private function appendToData(array &$Data, string $id, string $NombreArchivo): void {
        /**
         * Agrega la información del archivo procesado al array de resultados.
         *
         * Este método construye strings concatenados que luego pueden ser usados
         * para operaciones como inserciones o actualizaciones en base de datos.
         *
         * Estructura esperada en $Data:
         * - Nombres: lista de identificadores (columnas)
         * - Archivos: lista de valores (nombres de archivos)
         * - Update: expresiones tipo "col = 'valor'" para UPDATE SQL.
         *           IMPORTANTE: usar solo con query builders que apliquen prepared statements.
         *
         * @param array  &$Data          Array de resultados (por referencia)
         * @param string $id             Identificador del archivo (ej: nombre de columna)
         * @param string $NombreArchivo  Nombre final del archivo almacenado
         *
         * @return void
         */

        // Agrega el identificador del campo
        // Ejemplo: ",imagen,documento"
        $Data['Nombres'] .= ',' . $id;

        // Agrega el nombre del archivo como valor (entre comillas)
        // Ejemplo: ",'file1.png','file2.pdf'"
        $Data['Archivos'] .= ",'" . $NombreArchivo . "'";

        // Construye una expresión para UPDATE SQL
        // Ejemplo: ",imagen = 'file1.png',documento = 'file2.pdf'"
        $Data['Update'] .= ',' . $id . " = '" . $NombreArchivo . "'";

        // Construye una respuesta en caso de ser necesario
        $Data['success'] = true;
        $Data['message'] = 'Archivo subido correctamente';

    }

    /******************************************************************************************/
    // Retorna el mensaje de error correspondiente al código de error de subida de PHP.
    private function uploadPHPError(int $error): string {
        /**
         * Retorna el mensaje de error correspondiente a un código de subida de archivos en PHP.
         *
         * Estos códigos provienen de la constante interna de PHP `$_FILES['error']`
         * y permiten identificar qué falló durante el proceso de carga.
         *
         * @param int $error Código de error de subida (UPLOAD_ERR_*)
         *
         * @return string Mensaje descriptivo del error
         */

        // Utiliza la expresión match (PHP 8+) para mapear códigos a mensajes
        return match ($error) {

            // 0: No ocurrió ningún error, la subida fue exitosa
            0 => 'No hay error, el archivo se cargó con éxito',

            // 1: El archivo excede el límite definido en php.ini (upload_max_filesize)
            1 => 'El archivo cargado supera la directiva upload_max_filesize en php.ini',

            // 2: El archivo excede el límite definido en el formulario HTML (MAX_FILE_SIZE)
            2 => 'El archivo cargado excede la directiva MAX_FILE_SIZE del formulario HTML',

            // 3: El archivo se subió parcialmente (interrupción)
            3 => 'El archivo cargado solo se cargó parcialmente',

            // 4: No se seleccionó ningún archivo
            4 => 'No se cargó ningún archivo',

            // 6: No existe o no está disponible la carpeta temporal del servidor
            6 => 'Falta una carpeta temporal',

            // 7: Error al escribir el archivo en el disco (permisos o almacenamiento)
            7 => 'Error al escribir el archivo en el disco',

            // 8: Una extensión de PHP detuvo la subida (ej: extensión de seguridad)
            8 => 'Una extensión PHP detuvo la carga del archivo',

            // Caso por defecto: código no reconocido
            default => "Error desconocido al subir el archivo (código: $error)",
        };
    }

    /******************************************************************************************/
    // Determina si un archivo o carpeta debe ser incluido en el listado del explorador, aplicando múltiples reglas de seguridad
    private function isAllowed(string $file, string $fullPath, array $allowedMimes): bool {
        /**
         * FUNCIÓN DE FILTRO DE ARCHIVOS Y CARPETAS
         *
         * Determina si un archivo o carpeta debe ser incluido en el listado del explorador,
         * aplicando múltiples reglas de seguridad:
         *
         * - Exclusión de archivos ocultos
         * - Exclusión por nombre
         * - Exclusión por extensión
         * - Exclusión de carpetas completas
         * - Validación de tipo MIME real (solo para archivos)
         *
         * @param string   $file            Nombre del archivo o carpeta
         * @param string   $fullPath        Ruta completa del directorio actual
         * @param array    $allowedMimes    Lista blanca de tipos MIME permitidos
         *
         * @return bool TRUE si el archivo es válido, FALSE si debe ser excluido
         */

        // Normaliza el nombre del archivo a minúsculas para comparaciones seguras
        $name = strtolower($file);

        // Construye la ruta completa del archivo/carpeta
        $filePath = $fullPath . "/" . $file;

        // EXCLUSIÓN DE ARCHIVOS OCULTOS: Archivos que comienzan con "." (ej: .env, .gitignore)
        if (str_starts_with($name, '.')) {
            return false;
        }

        // EXCLUSIÓN POR NOMBRE EXACTO: Evita archivos sensibles definidos explícitamente
        if (in_array($name, self::EXCLUDED_NAMES, true)) {
            return false;
        }

        // EXCLUSIÓN DE CARPETAS COMPLETAS: Bloquea acceso a directorios completos (ej: .git, node_modules)
        if (is_dir($filePath) && in_array($name, self::EXCLUDED_FOLDERS, true)) {
            return false;
        }

        // EXCLUSIÓN POR EXTENSIÓN: Extrae la extensión y valida contra lista negra
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        if ($ext !== '' && in_array($ext, self::BLOCKED_EXTENSIONS, true)) {
            return false;
        }

        // Bloquear archivos sin extensión
        if (!is_dir($filePath) && $ext === '') {
            return false;
        }

        // VALIDACIÓN MIME REAL (SOLO ARCHIVOS): Verifica el contenido real del archivo, no solo su extensión.
        if (is_file($filePath)) {
            // Obtiene el tipo MIME real usando finfo
            $mime = $this->getFinfo()->file($filePath);
            // Si el MIME no está en la lista blanca → bloquear
            if (!in_array($mime, $allowedMimes, true)) {
                return false;
            }
        }

        // Si pasa todas las validaciones, el archivo es permitido
        return true;
    }

    /******************************************************************************************/
    // Verifica que una ruta tenga permisos 0755
    private function ensurePermissions755(string $path): array {
        /**
         * Verifica que una ruta tenga permisos 0755 y, si no los tiene,
         * intenta corregirlos automáticamente.
         *
         * Consideraciones:
         * - Funciona principalmente en Linux/Unix
         * - En Docker puede fallar si el contenedor no tiene permisos suficientes
         * - Usa chmod, por lo que requiere permisos sobre el sistema de archivos
         *
         * @param string $path Ruta a validar
         *
         * @return array Resultado:
         *  - success : bool
         *  - message : string
         *  - current : string (permisos actuales)
         *  - expected: string (permisos esperados)
         */
        // Verifica que exista la ruta
        if (!file_exists($path)) {
            return [
                "success" => false,
                "message" => "La ruta no existe",
                "current" => $path,
                "expected" => "0755"
            ];
        }

        // Obtiene permisos actuales en formato octal (ej: 0755)
        $perms = fileperms($path);
        $currentPerms = substr(sprintf('%o', $perms), -4);

        // Si ya tiene 0755, no hace nada
        if ($currentPerms === '0755') {
            return [
                "success" => true,
                "message" => "Permisos correctos",
                "current" => $currentPerms,
                "expected" => "0755"
            ];
        }

        // Intenta cambiar permisos
        $changed = @chmod($path, 0755);

        // Verifica nuevamente
        $permsAfter = fileperms($path);
        $newPerms   = substr(sprintf('%o', $permsAfter), -4);

        if ($changed && $newPerms === '0755') {
            return [
                "success" => true,
                "message" => "Permisos corregidos correctamente",
                "current" => $newPerms,
                "expected" => "0755"
            ];
        }

        // Fallo al cambiar permisos (muy común en Docker)
        return [
            "success" => false,
            "message" => "No se pudieron cambiar los permisos (posible restricción de Docker o permisos del sistema)",
            "current" => $currentPerms,
            "expected" => "0755"
        ];
    }

    /******************************************************************************************/
    // Verifica si una ruta tiene permisos reales de escritura.
    private function canWrite(string $path): bool {
        /**
         * Verifica si una ruta tiene permisos reales de escritura.
         *
         * A diferencia de funciones como is_writable(), este método realiza
         * una prueba real creando y eliminando un archivo temporal.
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
         * @param string $path Ruta donde se desea verificar escritura
         *
         * @return bool TRUE si se puede escribir, FALSE en caso contrario
         */

        // Construye un archivo temporal único dentro de la ruta
        // - rtrim evita doble slash al final
        // - uniqid garantiza nombre único
        $testFile = rtrim($path, '/') . '/.perm_test_' . uniqid();

        // Intenta escribir un archivo de prueba
        // - @ evita warnings visibles si falla (manejo controlado)
        if (@file_put_contents($testFile, 'test') !== false) {

            // Si se pudo crear, elimina el archivo temporal
            @unlink($testFile);

            // Retorna TRUE: escritura confirmada
            return true;
        }

        // Si no se pudo escribir, no hay permisos reales
        return false;
    }

    /******************************************************************************************/
    // Retorna la instancia única de finfo (patrón lazy singleton dentro de la clase).
    private function getFinfo(): \finfo {
        return $this->finfo ??= new \finfo(FILEINFO_MIME_TYPE);
    }

    /******************************************************************************************/
    // Resuelve, desencripta y valida la ruta completa del explorador (anti path traversal).
    private function resolveExplorerPath(array $Data): string {
        /**
         * Construye la ruta absoluta y segura para el explorador de archivos.
         *
         * Pasos:
         * 1. Desencripta la ruta base recibida del frontend
         * 2. Reconstruye la subruta eliminando tokens de ofuscación
         * 3. Valida que la ruta final no salga del ROOT_PATH (anti path traversal)
         *
         * @param array $Data Parámetros de entrada (route, path)
         *
         * @return string Ruta absoluta validada
         *
         * @throws \RuntimeException Si la ruta no es válida o intenta escapar del ROOT
         */

        // Instancia de clase para desencriptar la ruta
        $fnc_Codification = new FunctionsSecurityCodification();

        // Construye y normaliza la ruta raíz segura:
        // - Se desencripta la ruta recibida del frontend
        // - realpath() resuelve ".." y enlaces simbólicos
        $ROOT_PATH = realpath(
            ConfigAPP::APP['uploadFolder'] . '/' .
            $fnc_Codification->encryptDecrypt('decrypt', $Data['route'])
        );

        // Si realpath() falla, la ruta no existe o es inválida
        if ($ROOT_PATH === false) {
            throw new \RuntimeException('Ruta base no válida o inaccesible');
        }

        // Reconstruye la subruta enviada desde el frontend:
        // - Elimina tokens de ofuscación ("asdqwe" y "ntn")
        // - "ntn" era el separador de ruta → se convierte de vuelta a "/"
        $relativePath = isset($Data['path'])
            ? str_replace(['asdqwe', 'ntn'], ['', '/'], $Data['path'])
            : '';
        $relativePath = $this->sanitizePath($relativePath); // limpia "..", "%2e", etc.

        // Construye y valida la ruta final contra el ROOT_PATH (via safePath)
        // safePath garantiza que no se pueda escapar del directorio raíz
        $fullPath = $this->CommonData->safePath(
            $ROOT_PATH . '/' . $relativePath,
            $ROOT_PATH
        );

        // Validación adicional: la ruta resuelta debe existir y ser un directorio
        if (!is_dir($fullPath)) {
            throw new \RuntimeException('El directorio solicitado no existe o no es accesible');
        }

        return $fullPath;
    }

    /******************************************************************************************/
    // Lista y filtra el contenido de un directorio, retornando solo archivos permitidos con su metadata.
    private function buildFileList(string $fullPath, array $allowedMimes): array {
        /**
         * Recorre un directorio y retorna los archivos y carpetas permitidos.
         *
         * - Delega el filtro de seguridad a isAllowed()
         * - Delega la construcción de cada entrada a buildFileEntry()
         *
         * @param string $fullPath    Ruta absoluta validada del directorio a listar
         * @param array  $allowedMimes Lista blanca de tipos MIME permitidos
         *
         * @return array Lista de archivos/carpetas con metadata
         */

        // Array de resultados
        $files = [];

        // Validar retorno
        $entries = scandir($fullPath);
        if ($entries === false) {
            return []; // o lanzar excepción
        }

        // Recorre todos los elementos del directorio
        foreach ($entries as $file) {

            // Ignora referencias de directorio actual y padre
            if ($file === '.' || $file === '..') {
                continue;
            }

            // Aplica todas las validaciones de seguridad y filtrado
            // Las constantes se usan directamente desde la clase (no se pasan como parámetro)
            if (!$this->isAllowed($file, $fullPath, $allowedMimes)) {
                continue;
            }

            // Construye la entrada con metadata del archivo/carpeta
            $files[] = $this->buildFileEntry($file, $fullPath);
        }

        //retorno los archivos
        return $files;
    }

    /******************************************************************************************/
    // Construye el array de metadata de un archivo o carpeta individual.
    private function buildFileEntry(string $file, string $fullPath): array {
        /**
         * Construye la estructura de datos de un archivo o carpeta.
         *
         * Centraliza la lógica de metadata en un único lugar,
         * facilitando agregar nuevos campos en el futuro sin tocar buildFileList().
         *
         * @param string $file      Nombre del archivo o carpeta
         * @param string $fullPath  Ruta absoluta del directorio que lo contiene
         *
         * @return array Metadata del archivo:
         *  - name : Nombre del archivo o carpeta
         *  - type : "folder" | "file"
         *  - size : Tamaño en bytes (null para carpetas)
         *  - date : Fecha de última modificación (Y-m-d H:i:s)
         */

        $filePath = $fullPath . '/' . $file;
        $stat     = stat($filePath);          // una sola llamada al sistema
        $isDir    = ($stat['mode'] & 0040000) !== 0;

        return [
            'name' => $file,
            'type' => $isDir ? 'folder' : 'file',
            'size' => $isDir ? null : $stat['size'],
            'date' => date('Y-m-d H:i:s', $stat['mtime']),
        ];
    }

    /******************************************************************************************/
    // Resuelve la extensión de archivo a partir de un tipo MIME real.
    private function resolveExtensionFromMime(string $mime): ?string {
        /**
         * Resuelve la extensión de archivo a partir de un tipo MIME real.
         * Si el MIME no está mapeado, retorna null para que el llamador decida.
         *
         * @param string $mime Tipo MIME real detectado por finfo
         * @return string|null Extensión sin punto (ej: 'jpg') o null si no está mapeado
         */
        return self::MIME_TO_EXTENSION[$mime] ?? null;
    }

    /******************************************************************************************/
    // Limpia el prefijo data URI de un string Base64 sin importar el tipo.
    private function cleanBase64Payload(string $raw): string {
        /**
         * Limpia el prefijo data URI de un string Base64 sin importar el tipo.
         * Ejemplos de prefijos eliminados:
         *  - data:image/png;base64,
         *  - data:image/jpeg;base64,
         *  - data:application/pdf;base64,
         *
         * @param string $raw String Base64 crudo recibido del frontend
         * @return string     String Base64 limpio listo para decodificar
         */
        // Elimina cualquier prefijo "data:...;base64," si existe
        if (str_contains($raw, ';base64,')) {
            $raw = substr($raw, strpos($raw, ';base64,') + 8);
        }
        // Normaliza espacios que los navegadores a veces insertan
        return str_replace(' ', '+', $raw);
    }


}

