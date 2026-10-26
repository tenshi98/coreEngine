<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class FilePolicy
 *
 * Reglas puras de archivos del núcleo: listas de tipos MIME, extensiones
 * prohibidas, nombres/carpetas excluidos, mapa MIME→extensión y tokens de
 * transporte del explorador.
 *
 * Extraída de FileManager (RF-009a) para concentrar la política en un único
 * lugar y poderla auditar sin recorrer el pipeline de subida. Todos sus
 * métodos son estáticos y sin estado (no dependen de la instancia ni de la
 * configuración de la aplicación).
 *
 * Nota: `splitList()` replica exactamente `FunctionsCommonData::parseDataCommas()`
 * para que esta clase no dependa de ninguna Functions* y permanezca pura.
 */
class FilePolicy {

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
            'application/x-real',
            'application/vnd.adobe.xfdf',
            'application/vnd.fdf',
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
        'php', 'php3', 'php4', 'php5', 'php7', 'php8',         // Scripts backend
        'phtml', 'pht', 'phtm', 'phar', 'phps', 'phpt',        // Variantes/alias PHP
        'ini', 'env', 'conf', 'config', 'yaml', 'yml', 'toml', // Configuración / entorno
        'log',                                                 // Logs / debug
        'sh', 'bash', 'zsh', 'bat', 'cmd', 'ps1',              // Shell / ejecución
        'exe', 'bin', 'run',                                   // Binarios / ejecutables
        'sql', 'bak', 'old', 'backup', 'dump',                 // Backups / dumps
        'cgi', 'pl', 'py', 'rb', 'jsp', 'asp', 'aspx',         // Otros potencialmente peligrosos
        'shtml', 'shtm', 'xhtml', 'inc',                       // Server-side includes / inclusión
        'htaccess', 'htpasswd'                                 // Configuración Apache (defensa en profundidad)
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
        'application/x-real'                                                        => 'pdf',   // Alias legacy agrupado en la categoría pdf (evita fallo al derivar extensión desde MIME)
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
        'application/mp4'                                                           => 'mp4',   // Alias no estándar de video/mp4 (evita fallo al derivar extensión desde MIME)
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

    /******************************************************************************************/
    // Tokens de transporte de ruta del explorador.
    //
    // El endpoint de listado es `GET /core/fileExplorer/updateList/@route/@tipos/@path/@scope`,
    // donde @path es un token de Fat-Free que SOLO admite un segmento de URL (no puede contener
    // "/"). Por eso el frontend codifica la ruta en un único segmento: "/" → SLASH_TOKEN y la
    // raíz (segmento vacío, no permitido) → ROOT_TOKEN. NO es una medida anti-WAF: es la
    // codificación necesaria del transporte. Los POST del explorador envían `path` crudo en el
    // cuerpo (FormData), por lo que estos tokens solo aplican al GET de listado.
    public const EXPLORER_ROOT_TOKEN  = 'asdqwe';
    public const EXPLORER_SLASH_TOKEN = 'ntn';

    /*******************************************************************************************************************/
    /*                                              Métodos públicos                                                   */
    /*******************************************************************************************************************/

    /******************************************************************************************/
    /**
     * Divide una cadena separada por comas en un arreglo, descartando espacios y vacíos.
     *
     * Replica exactamente `FunctionsCommonData::parseDataCommas()` para que esta clase
     * permanezca pura (sin dependencias).
     *
     * @param string $list Cadena con elementos separados por comas (ej: "image,pdf").
     * @return array Arreglo de elementos no vacíos.
     */
    public static function splitList(string $list): array {
        return preg_split('/\s*,\s*/', $list, -1, PREG_SPLIT_NO_EMPTY);
    }

    /******************************************************************************************/
    /**
     * Construye la lista de tipos MIME permitidos a partir de categorías separadas por comas.
     *
     * @param string $tipos Categorías separadas por comas (ej: "image,pdf").
     * @return array Lista de tipos MIME permitidos.
     */
    public static function allowedMimes(string $tipos): array {

        $allowedMimes = [];

        foreach (self::splitList($tipos) as $tipo) {
            if (isset(self::MIME_TYPES[$tipo])) {
                $allowedMimes = array_unique(array_merge($allowedMimes, self::MIME_TYPES[$tipo]));
            }
        }

        return $allowedMimes;
    }

    /******************************************************************************************/
    /**
     * Resuelve la extensión de archivo a partir de un tipo MIME real.
     *
     * @param string $mime Tipo MIME real (ej: 'image/jpeg').
     * @return string|null Extensión sin punto (ej: 'jpg') o null si no está mapeado.
     */
    public static function extensionFromMime(string $mime): ?string {
        return self::MIME_TO_EXTENSION[$mime] ?? null;
    }

    /******************************************************************************************/
    /**
     * Verifica si la extensión del archivo está en la lista de extensiones prohibidas.
     *
     * @param string $filename Nombre del archivo (puede incluir ruta; solo se evalúa la extensión).
     * @return bool TRUE si la extensión está prohibida.
     */
    public static function forbiddenExtension(string $filename): bool {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, self::BLOCKED_EXTENSIONS, true);
    }

    /******************************************************************************************/
    /**
     * Verifica si el nombre corresponde a un nombre sensible/excluido (política de SUBIDA).
     *
     * Bloquea archivos ocultos (nombre que comienza con punto) y los nombres de la lista
     * EXCLUDED_NAMES comparados en minúsculas.
     *
     * @param string $filename Nombre del archivo (puede incluir ruta; solo se evalúa el basename).
     * @return bool TRUE si el nombre está excluido o es oculto.
     */
    public static function excludedName(string $filename): bool {

        $name = strtolower(basename(trim($filename)));

        if (str_starts_with($name, '.')) { return true; }

        static $blocklist = null;
        $blocklist ??= array_map('strtolower', self::EXCLUDED_NAMES);

        return in_array($name, $blocklist, true);
    }

    /******************************************************************************************/
    /**
     * Verifica si el nombre de una ENTRADA del explorador está excluido.
     *
     * Replica el comportamiento heredado de `filterAndBuildFileList()`: compara el nombre
     * en minúsculas contra la lista EXCLUDED_NAMES SIN normalizar (comparación sensible a
     * mayúsculas/minúsculas), por lo que las entradas en PascalCase (Dockerfile, README.md,
     * LICENSE) no coinciden. Se conserva idéntico para no alterar la vista del explorador.
     *
     * @param string $name Nombre de la entrada (archivo o carpeta).
     * @return bool TRUE si es oculto o está en la lista tal cual.
     */
    public static function excludedEntryName(string $name): bool {
        $lower = strtolower($name);
        if (str_starts_with($lower, '.')) { return true; }
        return in_array($lower, self::EXCLUDED_NAMES, true);
    }

    /******************************************************************************************/
    /**
     * Verifica si el nombre corresponde a una carpeta sensible excluida del explorador.
     *
     * @param string $name Nombre de la carpeta.
     * @return bool TRUE si está en EXCLUDED_FOLDERS.
     */
    public static function excludedFolder(string $name): bool {
        return in_array(strtolower($name), self::EXCLUDED_FOLDERS, true);
    }
}
