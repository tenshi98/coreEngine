<?php
/**
 * Carga el sistema de almacenamiento multi-driver.
 * Se incluye aquí para garantizar disponibilidad sin importar
 * el orden de carga del framework.
 */
require_once __DIR__ . '/../storage/StorageDriverInterface.php';
require_once __DIR__ . '/../storage/LocalStorageDriver.php';
require_once __DIR__ . '/../storage/S3StorageDriver.php';
require_once __DIR__ . '/../storage/GCSStorageDriver.php';
require_once __DIR__ . '/../storage/SFTPStorageDriver.php';
require_once __DIR__ . '/../storage/StorageDriverFactory.php';

/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
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
 *
 * Drivers disponibles:
 *   'local' → Sistema de archivos local (Apache)    [por defecto]
 *   's3'    → Amazon S3 (o compatibles: MinIO, R2)
 *   'gcs'   → Google Cloud Storage
 *   'sftp'  → Servidor remoto vía SFTP
 */
class FileManager {

    /*******************************************************************************************************************/
    /*                                           Constantes de clase                                                   */
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
    /*                                                Instancias                                                       */
    /*******************************************************************************************************************/

    private FunctionsCommonData      $CommonData;
    private StorageDriverInterface   $storage;    // ← driver activo (local | s3 | gcs | sftp)
    private ?\finfo                  $finfo = null;
    private static array             $pathCache = [];

    public function __construct() {
        $this->CommonData = new FunctionsCommonData();
        // Instancia el driver correcto según ConfigAPP::APP['uploadServer']
        $this->storage = StorageDriverFactory::make();
    }

    /*******************************************************************************************************************/
    /*                                                  Métodos públicos                                               */
    /*******************************************************************************************************************/

    /******************************************************************************************/
    /**
     * Valida la integridad, seguridad y requisitos técnicos de los archivos antes de su procesamiento.
     * * Este método realiza una auditoría exhaustiva de cada archivo enviado al servidor:
     * verifica errores de subida nativos de PHP, bloquea extensiones peligrosas,
     * valida el tipo MIME real (inspeccionando el contenido y no solo la extensión),
     * controla el peso máximo permitido y comprueba la existencia de duplicados
     * mediante el driver de almacenamiento activo (Local, S3, etc.).
     *
     * @param array $SIS_FILES Arreglo equivalente a $_FILES con los datos binarios.
     * @param array $arrArchivos Configuración detallada de cada archivo esperado.
     * @param array $PostData Datos adicionales del contexto de la petición.
     * @return array ['success' => true] si pasa todas las pruebas, o un arreglo con el detalle de los errores.
     */
    public function validateFiles(array $SIS_FILES, array $arrArchivos, array $PostData = []): array {

        // Si no se definieron reglas de archivos, se asume validación exitosa por defecto
        if (empty($arrArchivos)) {
            return ['success' => true, 'data' => true];
        }

        // Acumulador de incidentes encontrados durante la validación
        $errors = [];

        // Procesamiento secuencial de cada archivo configurado
        foreach ($arrArchivos as $archivo) {
            /**
             * Filtro de entrada:
             * Solo se valida si el identificador está presente en PostData y NO es una carga vía Base64,
             * ya que el procesamiento de Base64 sigue un flujo de decodificación distinto.
             */
            if (!isset($PostData[$archivo['Identificador']]) || $archivo['Base64'] !== false) {
                continue;
            }

            $id = $archivo['Identificador'];

            /*************** Verificaciones de Existencia y Sistema ***************/

            // 1. Comprueba que el archivo físico exista en el buffer de subida
            if (empty($SIS_FILES[$id])) {
                $errors[] = ['success' => false, 'message' => $id . ' es obligatorio'];
                continue;
            }

            // 2. Valida códigos de error nativos de PHP (UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_PARTIAL, etc.)
            if ($SIS_FILES[$id]['error'] > 0) {
                $errors[] = ['success' => false, 'message' => $this->uploadPHPError($SIS_FILES[$id]['error'])];
                continue;
            }

            /*************** Verificaciones de Seguridad ***************/

            // 3. Bloquea archivos con extensiones ejecutables o peligrosas (.php, .exe, .sh, etc.)
            if ($this->hasForbiddenExtension($SIS_FILES[$id]['name'])) {
                $errors[] = ['success' => false, 'message' => 'Extensión de archivo no permitida por seguridad'];
                continue;
            }

            // 4. Validación de Tipo MIME Real:
            // No confía en la extensión; inspecciona los bytes del archivo temporal (tmp_name)
            $allowedMimes = $this->buildAllowedMimes($archivo['ValidarTipo']);
            $realMime     = $this->getRealMimeType($SIS_FILES[$id]['tmp_name']);

            if (!in_array($realMime, $allowedMimes, true)) {
                $errors[] = ['success' => false, 'message' => 'Tipo de archivo no permitido'];
                continue;
            }

            /*************** Verificaciones de Restricción ***************/

            // 5. Control de Peso: Convierte megabytes configurados a bytes para la comparación
            if ($SIS_FILES[$id]['size'] >= ($archivo['ValidarPeso'] * 1048576)) {
                $errors[] = ['success' => false, 'message' => 'Archivo excede el tamaño permitido'];
                continue;
            }

            // 6. Verificación de Duplicados en Almacenamiento:
            // Utiliza la abstracción de 'storage' para consultar si el nombre ya existe en el destino
            $nombreArchivo = $this->buildFileName($archivo, $SIS_FILES[$id]['name']);
            $rutaRelativa  = $this->buildRelativePath($archivo) . $nombreArchivo;

            if ($this->storage->exists($rutaRelativa)) {
                $errors[] = ['success' => false, 'message' => 'El archivo ' . $SIS_FILES[$id]['name'] . ' ya existe en el servidor'];
            }
        }

        // Retorno final: si el arreglo de errores está vacío, la validación es exitosa
        return empty($errors)
            ? ['success' => true,  'message' => true]
            : $errors;
    }

    /******************************************************************************************/
    /**
     * Gestiona la subida física de archivos al servidor o almacenamiento en la nube.
     *
     * @param array $SIS_FILES   Arreglo global $_FILES con los binarios.
     * @param array $arrArchivos Configuración de destino y reglas para cada archivo.
     * @param array $PostData    Datos adicionales (necesario para procesar Base64).
     * @return array {
     *      Nombres  : string  Fragmento SQL para columnas (ej: ", foto, cv").
     *      Archivos : string  Fragmento SQL para valores (ej: ", 'foto.jpg', 'doc.pdf'").
     *      Update   : string  Fragmento SQL para SET (ej: ", foto='foto.jpg', cv='doc.pdf'").
     *      success  : bool    Estado del proceso.
     * }
     */
    public function uploadFile(array $SIS_FILES, array $arrArchivos, array $PostData = []): array {

        // Variables
        $Data = [
            'Nombres'  => '',
            'Archivos' => '',
            'Update'   => '',
            'success'  => false,
            'message'  => '',
        ];

        // Itera sobre la configuración para decidir el método de subida
        // - handleBase64Upload: Maneja la subida de un archivo codificado en Base64
        // - handleNormalUpload: Maneja la subida de un archivo normal (multipart/form-data).
        foreach ($arrArchivos as $archivo) {
            if ($archivo['Base64'] === true) {
                // Procesa strings Base64 (común en firmas o canvas)
                $this->handleBase64Upload($archivo, $PostData, $Data);
            } else {
                // Procesa subidas tradicionales (multipart/form-data)
                $this->handleNormalUpload($archivo, $SIS_FILES, $Data);
            }
        }

        // Retorno de datos
        return $Data;
    }

    /******************************************************************************************/
    /**
     * Elimina un archivo específico del almacenamiento.
     *
     * @param string $SIS_File    Nombre del archivo físico.
     * @param string $SIS_Carpeta Subdirectorio donde reside.
     * @return bool True si se eliminó con éxito.
     */
    public function deleteFile(string $SIS_File, string $SIS_Carpeta): bool {

        // Si no hay archivos
        if (empty($SIS_File)) { return false;}

        // Normaliza la ruta para evitar slashes duplicados o al inicio
        $rutaRelativa = ltrim($SIS_Carpeta . '/' . $SIS_File, '/');

        // Delega al driver de almacenamiento (Local, S3, etc.)
        return $this->storage->delete($rutaRelativa);

    }

    /******************************************************************************************/
    /**
     * Elimina múltiples archivos de forma masiva basándose en un mapa de resultados.
     *
     * @param string $SIS_Files   Lista de IDs separados por coma (ej: "img_perfil,doc_identidad").
     * @param string $SIS_Carpeta Carpeta común de los archivos.
     * @param array  $Result      Mapa que asocia el ID con el nombre real del archivo.
     * @return bool
     */
    public function deleteFilesMassive(string $SIS_Files, string $SIS_Carpeta, array $Result): bool {

        // Si no hay archivos
        if (empty($SIS_Files) || empty($Result)) {
            return false;
        }

        // Se obtienen datos y rutas
        $arrFiles = $this->CommonData->parseDataCommas($SIS_Files);

        // Se recorren los archivos y se eliminan
        foreach ($arrFiles as $file) {
            // Sanitización estricta del ID para evitar inyecciones en la ruta
            $file = preg_replace('/[^a-zA-Z0-9_]/', '', $file);
            if (!empty($Result[$file])) {
                $rutaRelativa = ltrim($SIS_Carpeta . '/' . $Result[$file], '/');
                $this->storage->delete($rutaRelativa);
            }
        }

        // Retorno de datos
        return true;

    }

    /******************************************************************************************/
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
     * @return array Lista de archivos y carpetas con metadata (nombre, tamaño, fecha, icono)
     */
    public function fileExplorer(array $Data = []): array {

        // Resuelve la ruta real evitando "Path Traversal" (../)
        $relativePath = $this->resolveExplorerRelativePath($Data);

        // Construye la lista de MIME permitidos según los tipos configurados
        $allowedMimes = $this->buildAllowedMimes(
            $Data['tipos'] !== 'all'
                ? $Data['tipos']
                : 'word,excel,powerpoint,pdf,image,txt,zip,video,music'
        );

        // Obtiene el listado crudo desde el driver
        $rawEntries = $this->storage->listDirectory($relativePath);

        // Filtra archivos ocultos, prohibidos y construye la respuesta para el frontend
        return $this->filterAndBuildFileList($rawEntries, $relativePath, $allowedMimes);
    }

    /******************************************************************************************/
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
    public function createFolder(array $PostData = []): array {

        // Validacion de parametros de entrada
        if (empty($PostData['name'])) {
            return ['success' => false, 'message' => 'Nombre no definido'];
        }

        // Construcción segura de la ruta jerárquica
        $subRoute     = isset($PostData['SubRoute']) ? trim($this->sanitizePath($PostData['SubRoute']), '/') : '';
        $relativePath = $subRoute !== '' ? $subRoute . '/' : '';
        $relativePath .= isset($PostData['path'])
            ? trim($this->sanitizePath($PostData['path']), '/') . '/'
            : '';

        // Sanitiza el nombre de la carpeta para evitar caracteres ilegales en sistemas de archivos
        $relativePath .= $this->sanitizeFolderName($PostData['name']);

        // Normaliza posibles dobles slashes (extra seguridad)
        $relativePath = preg_replace('#/+#', '/', $relativePath);

        // Creacion de la carpeta (Manejo de errores detallado)
        return $this->storage->createDirectory($relativePath);

    }

    /******************************************************************************************/
    /**
     * Elimina una carpeta y todo su contenido usando el driver activo.
     *
     * Medidas de seguridad aplicadas:
     * - Validación de parámetros de entrada (path y name obligatorios)
     * - Sanitización de la ruta para prevenir Path Traversal
     * - Bloqueo de eliminación de la carpeta raíz de uploads
     * - Validación de que la ruta resultante no quede vacía
     *
     * @param array $PostData Parámetros de entrada:
     *  - SubRoute : subruta base opcional (controlada por backend)
     *  - path     : subruta dentro de la base
     *  - name     : nombre de la carpeta a eliminar
     *
     * @return array Resultado:
     *  - success : bool
     *  - message : string
     */
    public function deleteFolder(array $PostData = []): array {

        // Validacion de parametros de entrada
        if (empty($PostData['name'])) {
            return ['success' => false, 'message' => 'Nombre de carpeta no definido'];
        }

        // Ensamblado de la ruta absoluta relativa al driver
        $subRoute     = isset($PostData['SubRoute']) ? trim($this->sanitizePath($PostData['SubRoute']), '/') : '';
        $relativePath = $subRoute !== '' ? $subRoute . '/' : '';
        $relativePath .= isset($PostData['path'])
            ? trim($this->sanitizePath($PostData['path']), '/') . '/'
            : '';

        // Sanitiza el nombre de la carpeta para evitar caracteres ilegales en sistemas de archivos
        $relativePath .= $this->sanitizeFolderName($PostData['name']);

        // Elimina dobles slashes y espacios residuales
        $relativePath = trim(preg_replace('#/+#', '/', $relativePath), '/');

        /*******************************************************************/
        // SEGURIDAD CRÍTICA: impedir eliminación de la raíz
        // Una ruta vacía o de un solo nivel sin subruta controlada
        // apuntaría a la carpeta base completa del driver → bloqueado
        /*******************************************************************/
        if ($relativePath === '') {
            return ['success' => false, 'message' => 'No se permite eliminar la carpeta raíz'];
        }

        // Eliminacion de la carpeta (Manejo de errores detallado)
        return $this->storage->deleteDirectory($relativePath);
    }

    /*******************************************************************************************************************/
    /*                                              Métodos Públicos utilitarios                                       */
    /*******************************************************************************************************************/

    /******************************************************************************************/
    /**
     * Sanitiza una ruta eliminando secuencias peligrosas y caracteres no permitidos.
     * * Este método es la defensa principal contra ataques de "Path Traversal" (../).
     * Primero decodifica la URL para capturar intentos de evasión mediante encoding,
     * luego elimina cualquier intento de subir de nivel en el directorio (..) y
     * finalmente aplica una lista blanca de caracteres permitidos (letras, números,
     * slash, guiones y guiones bajos).
     *
     * @param string $path Ruta de entrada potencialmente insegura.
     * @return string Ruta limpia y segura para el sistema de archivos.
     */
    public function sanitizePath(string $path): string {
        // 1. Decodifica la URL para neutralizar bypasses como %2E%2E%2F (../)
        $decoded = rawurldecode($path);

        // 2. Elimina secuencias de dos o más puntos consecutivos para evitar navegar hacia atrás
        $clean   = preg_replace('/\.{2,}/', '', $decoded);

        // 3. Filtra y deja solo caracteres alfanuméricos, slashes, guiones y guiones bajos
        return preg_replace('/[^a-zA-Z0-9\/\-_]/', '', $clean);
    }

    /******************************************************************************************/
    /**
     * Sanitiza el nombre de una carpeta eliminando cualquier carácter especial o de ruta.
     * * A diferencia de sanitizePath, este método es mucho más restrictivo ya que
     * no permite el carácter "/" (slash). Su objetivo es garantizar que el nombre
     * de un directorio sea una cadena simple y segura, sin posibilidad de inyectar
     * subdirectorios o comandos.
     *
     * @param string $name Nombre de carpeta propuesto por el usuario.
     * @return string Nombre de carpeta sanitizado (alfanumérico, guion y guion bajo).
     */
    public function sanitizeFolderName(string $name): string {
        // Elimina absolutamente todo lo que no sea una letra, número, guion o guion bajo
        return preg_replace('/[^a-zA-Z0-9_\-]/', '', $name);
    }

    /******************************************************************************************/
    /**
     * Genera la URL de acceso a un recurso almacenado.
     * * Este método resuelve la ubicación pública de un archivo. Soporta tanto URLs
     * permanentes como URLs firmadas (temporales), las cuales son fundamentales
     * para la seguridad al compartir archivos privados almacenados en servicios
     * como Amazon S3 o Google Cloud Storage.
     *
     * @param string $filePath Ruta relativa del archivo dentro del storage.
     * @param int $expiresIn Tiempo de vida en segundos (0 para URL permanente).
     * @return string URL completa lista para ser usada en etiquetas <img>, <a>, etc.
     */
    public function getFileUrl(string $filePath, int $expiresIn = 0): string {
        // Delega la generación de la URL al driver activo (Local, S3, GCS, etc.)
        return $this->storage->getUrl($filePath, $expiresIn);
    }

    /******************************************************************************************/
    /**
     * Retorna la URL raíz pública configurada para el servidor de almacenamiento.
     * * Es una herramienta de abstracción esencial: permite que el frontend conozca
     * la base de las rutas sin necesidad de saber si los archivos están en el
     * servidor local o en un CDN externo. Garantiza la consistencia al incluir
     * siempre el slash final.
     *
     * @return string URL base del almacenamiento (ej: "https://cdn.example.com/uploads/").
     */
    public function getMainPathUrl(): string {
        // Recupera la ruta base directamente desde la configuración del driver activo.
        return $this->storage->getMainPathUrl();
    }

    /******************************************************************************************/

    /**
     * Provee acceso directo a la instancia del driver de almacenamiento actual.
     * * Sigue el patrón de diseño "Bridge" o "Adapter", permitiendo acceder a
     * funcionalidades específicas del driver que no estén mapeadas en los métodos
     * generales de la clase, facilitando la extensibilidad del sistema.
     *
     * @return StorageDriverInterface Instancia del driver (LocalDriver, S3Driver, etc.).
     */
    public function getStorageDriver(): StorageDriverInterface {
        return $this->storage;
    }

    /*******************************************************************************************************************/
    /*                                              Métodos privados                                                   */
    /*******************************************************************************************************************/

    /******************************************************************************************/
    /**
     * Maneja la subida de archivos en formato Base64.
     *
     * @param array $archivo   Configuración del archivo (identificador, nombre, ruta, etc.)
     * @param array $PostData  Datos recibidos (por ejemplo $_POST)
     * @param array &$Data     Referencia al array donde se almacenan los resultados
     */
    private function handleBase64Upload(array $archivo, array $PostData, array &$Data): void {

        // Obtiene el identificador único del archivo (clave en el POST)
        $id = $archivo['Identificador'];

        // Si no existe el dato en el POST, no se procesa
        if (empty($PostData[$id])) {
            $Data['success'] = false;
            $Data['message'] = 'No hay archivo';
            return;
        }

        // Base64 incrementa ~33% el tamaño del binario original (4 bytes encoded = 3 bytes raw)
        // Factor 1.37 agrega margen adicional sobre el 1.33 teórico
        $maxBase64Bytes = ($archivo['ValidarPeso'] ?? 10) * 1048576 * 1.37;
        if (strlen($PostData[$id]) > $maxBase64Bytes) {
            $Data['success'] = false;
            $Data['message'] = 'Archivo excede el tamaño permitido';
            return;
        }

        // Limpiar prefijo data URI dinámicamente (cualquier tipo MIME)
        $rawBase64 = $this->cleanBase64Payload($PostData[$id]);

        // Decodifica el contenido Base64 a binario
        // El segundo parámetro en true asegura validación estricta
        // Si la decodificación falla, se detiene el proceso
        $dIMG = base64_decode($rawBase64, true);
        if ($dIMG === false) {
            $Data['success'] = false;
            $Data['message'] = 'El contenido Base64 no es válido';
            return;
        }

        // Verificar MIME del binario decodificado antes de guardar
        $realMime = $this->getFinfo()->buffer($dIMG);

        // Verificar si esta dentro de los Mime permitidos
        $allowed  = $this->buildAllowedMimes($archivo['ValidarTipo'] ?? 'image');
        if (!in_array($realMime, $allowed, true)) {
            $Data['success'] = false;
            $Data['message'] = 'Tipo de archivo no permitido';
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
        $rutaRelativa  = $this->buildRelativePath($archivo);

        // Delegar guardado al método compartido
        $this->saveFileViaDriver($rutaRelativa, $nombreArchivo, $dIMG, true, $Data, $id);

    }

    /******************************************************************************************/
    /**
     * Maneja la subida de un archivo normal (multipart/form-data).
     *
     * @param array $archivo    Configuración del archivo (identificador, reglas, etc.)
     * @param array $SIS_FILES  Array de archivos subidos (equivalente a $_FILES)
     * @param array &$Data      Referencia al array donde se almacenan los resultados
     */
    private function handleNormalUpload(array $archivo, array $SIS_FILES, array &$Data): void {

        // Obtiene el identificador único del archivo (clave en $_FILES)
        $id = $archivo['Identificador'];

        // Verifica si el archivo fue enviado correctamente
        // Si no existe el nombre del archivo, se detiene el proceso
        if (empty($SIS_FILES[$id]['name'])) {
            $Data['success'] = false;
            $Data['message'] = 'No hay archivo';
            return;
        }

        // Construye el nombre final del archivo:
        // - Usa una función personalizada (puede incluir prefijos, timestamps, etc.)
        // - Luego sanitiza para evitar caracteres peligrosos o inválidos
        $nombreArchivo = $this->sanitizeFileName(
            $this->buildFileName($archivo, $SIS_FILES[$id]['name'])
        );

        // Construye la ruta donde se almacenará el archivo
        $rutaRelativa = $this->buildRelativePath($archivo);

        // Delegar guardado al método compartido (tmp_name como contenido)
        $this->saveFileViaDriver(
            $rutaRelativa,
            $nombreArchivo,
            $SIS_FILES[$id]['tmp_name'],
            false,
            $Data,
            $id
        );
    }

    /******************************************************************************************/
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
    private function saveFileViaDriver(string $rutaRelativa, string $nombreArchivo, string $contenido, bool   $isBase64, array  &$Data, string $id): void {

        // Evita sobrescribir archivos existentes en el servidor
        $fullRelative = ltrim($rutaRelativa . $nombreArchivo, '/');

        // Evita sobrescribir archivos existentes en el servidor
        if ($this->storage->exists($fullRelative)) {
            $Data['success'] = false;
            $Data['message'] = 'El archivo que intenta subir ya existe';
            return;
        }

        // Asegurar que el directorio exista (lo crea si es necesario)
        // Crea el "directorio" si el driver lo requiere (local y SFTP lo necesitan; S3/GCS no)
        $dirResult = $this->storage->createDirectory(ltrim($rutaRelativa, '/'));
        // No bloqueamos si el dir ya existía (success=false + "ya existe" es OK)
        if ($dirResult['success'] === false && !str_contains($dirResult['message'], 'ya existe')) {
            $Data['success'] = false;
            $Data['message'] = 'El directorio donde intenta subir el archivo no existe';
            return;
        }

        // Sube el archivo vía driver
        $saved = $this->storage->upload($contenido, $fullRelative, $isBase64);

        // Registrar resultado en $Data si se guardó correctamente
        if ($saved) {
            $this->appendToData($Data, $id, $nombreArchivo);
        }
    }

    /******************************************************************************************/
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
    private function buildFileName(array $archivo, string $originalName): string {

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
    /**
     * Construye la ruta RELATIVA del archivo (sin la carpeta base del driver).
     *
     * Reglas:
     * - Parte desde la carpeta base definida en la configuración global.
     * - Si se especifica una subcarpeta, se agrega a la ruta final.
     * - Se eliminan intentos básicos de path traversal (../) por seguridad.
     *
     * En v1 buildFilePath() retornaba la ruta absoluta incluyendo uploadFolder.
     * Ahora retorna solo la subcarpeta relativa, que el driver añade a su propio
     * "base" (carpeta local, bucket S3, directorio SFTP, etc.).
     *
     * Se mantiene la caché para evitar reprocesos dentro de la misma petición.
     *
     * @param array $archivo Configuración del archivo (incluye posible subcarpeta)
     *
     * @return string Ruta final donde se guardará el archivo
     */
    private function buildRelativePath(array $archivo): string {

        // Obtiene el valor de la subcarpeta desde el arreglo `$archivo`, si no existe, se asigna una cadena vacía por defecto.
        $sub = $archivo['SubCarpeta'] ?? '';

        // Verifica si la ruta ya fue previamente resuelta y almacenada en caché.
        if (!isset(self::$pathCache[$sub])) {
            // Obtiene la carpeta base de uploads desde la configuración de la aplicación
            $path = '';
            // Si se define una subcarpeta en la configuración del archivo
            if (!empty($archivo['SubCarpeta'])) {
                // Sanitiza la subcarpeta para evitar path traversal
                $path = $this->sanitizePath($archivo['SubCarpeta']) . '/';
            }
            self::$pathCache[$sub] = $path;
        }

        return self::$pathCache[$sub];
    }

    /******************************************************************************************/
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
    private function sanitizeFileName(string $filename): string {

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
    private function hasForbiddenExtension(string $filename): bool {

        // Obtiene la extensión del archivo y la normaliza a minúsculas
        // Ejemplo: "imagen.PNG" → "png"
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Verifica si la extensión está en la lista de extensiones prohibidas
        // strict = true evita comparaciones débiles (ej: "0" == 0)
        return in_array($ext, self::BLOCKED_EXTENSIONS, true);

    }

    /******************************************************************************************/
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
    private function getRealMimeType(string $tmpPath): string {

        // Obtiene el MIME real del archivo desde su contenido
        // Si falla, retorna false, por lo que usamos operador ternario para asegurar string
        $mime = $this->getFinfo()->file($tmpPath);

        // Retorna el MIME detectado o string vacío si no se pudo determinar
        return $mime ?: '';

    }

    /******************************************************************************************/
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
    private function buildAllowedMimes(string $tipos): array {

        // Convierte la cadena en un array
        // Ejemplo: "image,document" → ["image", "document"]
        $arrTipos     = $this->CommonData->parseDataCommas($tipos);

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
    private function appendToData(array &$Data, string $id, string $NombreArchivo): void {

        // Agrega el identificador del campo
        // Ejemplo: ",imagen,documento"
        $Data['Nombres']  .= ',' . $id;

        // Agrega el nombre del archivo como valor (entre comillas)
        // Ejemplo: ",'file1.png','file2.pdf'"
        $Data['Archivos'] .= ",'" . $NombreArchivo . "'";

        // Construye una expresión para UPDATE SQL
        // Ejemplo: ",imagen = 'file1.png',documento = 'file2.pdf'"
        $Data['Update']   .= ',' . $id . " = '" . $NombreArchivo . "'";

        // Construye una respuesta en caso de ser necesario
        $Data['success']   = true;
        $Data['message']   = 'Archivo subido correctamente';

    }

    /******************************************************************************************/
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
    private function uploadPHPError(int $error): string {

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
    /**
     * Filtra y construye la lista de archivos aplicando políticas de seguridad multicapa.
     * * Este método actúa como el "vigilante" del explorador de archivos. Procesa las entradas
     * crudas de cualquier driver (Local, S3, etc.) y decide qué mostrar al usuario basándose
     * en una lista negra de nombres, carpetas y extensiones prohibidas. Además, si el
     * servidor es local, realiza una inspección profunda del tipo MIME real para
     * evitar que archivos maliciosos camuflados aparezcan en la interfaz.
     *
     * @param array  $rawEntries   Listado bruto de archivos y carpetas del driver.
     * @param string $dirPath      Ruta del directorio que se está explorando.
     * @param array  $allowedMimes Lista blanca de tipos MIME permitidos para filtrar la vista.
     * @return array Listado filtrado y seguro de objetos (archivos/carpetas).
     */
    private function filterAndBuildFileList(array $rawEntries, string $dirPath, array $allowedMimes): array {

        // Verificacion del entorno
        $isLocal  = (ConfigAPP::APP['uploadServer'] ?? 'local') === 'local';
        $filtered = [];

        // Se recorre el Listado bruto de archivos
        foreach ($rawEntries as $entry) {
            $name  = $entry['name'];
            $lower = strtolower($name);

            /*************** 1. Filtros de Nombre y Sistema ***************/

            // Excluir archivos ocultos (que empiezan con punto como .htaccess o .git)
            if (str_starts_with($lower, '.')) { continue; }

            // Excluir nombres sensibles definidos en la constante de clase EXCLUDED_NAMES
            if (in_array($lower, self::EXCLUDED_NAMES, true)) { continue; }

            // Excluir carpetas del sistema o privadas (EXCLUDED_FOLDERS)
            if ($entry['type'] === 'folder' && in_array($lower, self::EXCLUDED_FOLDERS, true)) { continue; }

            /*************** 2. Filtros de Extensión ***************/
            // Obtener extensiones
            $ext = pathinfo($lower, PATHINFO_EXTENSION);

            // Bloquear extensiones peligrosas (ej: .php, .exe, .sh)
            if ($ext !== '' && in_array($ext, self::BLOCKED_EXTENSIONS, true)) { continue; }

            // Seguridad: Bloquear archivos que no tienen extensión (suelen ser binarios o scripts de sistema)
            if ($entry['type'] === 'file' && $ext === '') { continue; }

            /*************** 3. Validación de Contenido (Deep Scan) ***************/

            /**
             * Validación MIME real:
             * Solo es posible en almacenamiento LOCAL. En la nube (S3/GCS), no podemos leer
             * el contenido de todos los archivos eficientemente para obtener el MIME real,
             * por lo que confiamos en los filtros anteriores.
             */
            if ($isLocal && $entry['type'] === 'file') {
                $uploadFolder = rtrim(ConfigAPP::APP['uploadFolder'], '/');
                $fullPath     = $uploadFolder . '/' . ltrim($dirPath . '/' . $name, '/');

                if (file_exists($fullPath)) {
                    // Inspección de "Magic Bytes" mediante finfo
                    $mime = $this->getFinfo()->file($fullPath);

                    // Si el tipo de archivo real no está en la lista blanca de la petición, se oculta
                    if (!in_array($mime, $allowedMimes, true)) { continue; }
                }
            }

            // Si superó todas las pruebas, se añade al listado final
            $filtered[] = $entry;
        }

        return $filtered;
    }

    /******************************************************************************************/
    /**
     * Resuelve y valida la ruta relativa para el explorador de archivos, previniendo ataques de navegación.
     * * Este método actúa como un traductor de seguridad: desencripta la ruta base del servidor,
     * procesa la subruta ofuscada enviada desde el frontend (reemplazando tokens de seguridad)
     * y combina ambas piezas en una ruta limpia. Aplica filtros estrictos para asegurar
     * que la ruta resultante sea siempre relativa al directorio permitido y no contenga
     * secuencias de escape (..).
     *
     * @param array $Data Contiene 'route' (base encriptada) y 'path' (subruta ofuscada).
     * @return string Ruta relativa final, normalizada y segura para el driver de almacenamiento.
     */
    private function resolveExplorerRelativePath(array $Data): string {

        $fnc_Codification = new FunctionsSecurityCodification();

        /*************** 1. Desencriptación de Base ***************/
        // Recupera la ruta base que el backend definió como punto de partida.
        $decryptedBase = $fnc_Codification->encryptDecrypt('decrypt', $Data['route']);
        $safeBase      = $this->sanitizePath($decryptedBase);

        /*************** 2. Procesamiento de Subruta ***************/
        /**
         * Desofuscación del Frontend:
         * El frontend envía rutas usando 'asdqwe' (vacío) y 'ntn' (/) para evitar
         * que firewalls de aplicación (WAF) bloqueen la petición al detectar
         * caracteres de ruta en la URL.
         */
        $relativePath = isset($Data['path'])
            ? str_replace(['asdqwe', 'ntn'], ['', '/'], $Data['path'])
            : '';
        $relativePath = $this->sanitizePath($relativePath);

        /*************** 3. Combinación y Normalización ***************/
        // Une la base con la subruta y elimina slashes duplicados o accidentales en los extremos.
        $combined = trim($safeBase . '/' . $relativePath, '/');
        $combined = preg_replace('#/+#', '/', $combined);

        /*************** 4. Seguro de Vida (Hardening) ***************/
        /**
         * Si por alguna razón la ruta combinada contiene intentos de retroceso (..),
         * el sistema aborta la navegación profunda y devuelve al usuario a la base segura.
         */
        if (str_contains($combined, '..')) {
            return $safeBase;
        }

        return $combined;
    }

    /******************************************************************************************/
    /**
     * Resuelve la extensión de archivo correspondiente a partir de un tipo MIME detectado.
     * * Este método actúa como un diccionario de traducción inversa: toma el tipo MIME real
     * (identificado mediante la inspección de bytes del archivo) y lo convierte en
     * una extensión de archivo estándar (ej: 'image/jpeg' -> 'jpg').
     * * Es una pieza fundamental para la normalización de archivos, asegurando que
     * el nombre final en el disco sea coherente con el contenido real del archivo,
     * independientemente de la extensión original enviada por el usuario.
     *
     * @param string $mime Tipo MIME real detectado por el sistema (ej: 'application/pdf').
     * @return string|null Extensión sin punto (ej: 'pdf') o null si el tipo no está mapeado en la configuración.
     */
    private function resolveExtensionFromMime(string $mime): ?string {

        // Busca en la constante estática de la clase (MIME_TO_EXTENSION) la llave del MIME
        // Si no existe, utiliza el operador de fusión de nulos (??) para retornar null.
        return self::MIME_TO_EXTENSION[$mime] ?? null;

    }

    /******************************************************************************************/
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
    private function cleanBase64Payload(string $raw): string {

        // Elimina cualquier prefijo "data:...;base64," si existe
        if (str_contains($raw, ';base64,')) {
            $raw = substr($raw, strpos($raw, ';base64,') + 8);
        }
        // Normaliza espacios que los navegadores a veces insertan
        return str_replace(' ', '+', $raw);
    }

    /******************************************************************************************/
    /**
     * Obtiene una instancia persistente de la clase finfo para la detección de tipos MIME.
     * * Este método implementa el patrón de diseño "Lazy Loading" (carga perezosa) mediante
     * el operador de asignación de coalescencia nula (??=). La instancia de finfo se
     * crea únicamente la primera vez que se solicita y se almacena en una propiedad
     * de la clase para ser reutilizada en futuras validaciones durante el mismo ciclo
     * de vida de la petición.
     *
     * @return \finfo Instancia de finfo configurada con la constante FILEINFO_MIME_TYPE.
     */
    private function getFinfo(): \finfo {

        // Si $this->finfo ya tiene un objeto, lo retorna.
        // De lo contrario, crea el 'new \finfo', lo asigna a la propiedad y luego lo retorna.
        return $this->finfo ??= new \finfo(FILEINFO_MIME_TYPE);
    }
}
