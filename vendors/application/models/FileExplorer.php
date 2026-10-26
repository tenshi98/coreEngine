<?php
/*******************************************************************************************************************/
/*                                              Requisitos                                                         */
/*******************************************************************************************************************/
/**
 * Carga el sistema de almacenamiento multi-driver.
 * Se incluye aquí para garantizar disponibilidad sin importar
 * el orden de carga del framework (storage/ no está en AUTOLOAD).
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
 * Class FileExplorer
 *
 * Explorador de archivos seguro y gestión de carpetas (crear/eliminar).
 * Extraída de FileManager (RF-009a).
 *
 * Responsabilidades:
 *  - Listar y filtrar el contenido de un directorio aplicando política multicapa.
 *  - Resolver y validar la ruta relativa (anti path traversal) e interpretar el
 *    transporte de ruta del frontend (tokens EXPLORER_*).
 *  - Crear/eliminar carpetas dentro de una ruta base controlada.
 *
 * Depende del driver de almacenamiento (inyectado) y de `FileInspector`.
 */
class FileExplorer {

    /*******************************************************************************************************************/
    /*                                                Instancias                                                       */
    /*******************************************************************************************************************/

    private StorageDriverInterface $storage;    // ← driver activo (local | s3 | gcs | sftp)
    private FileInspector          $inspector;

    /*******************************************************************************************************************/
    /*                                                Constructor                                                      */
    /*******************************************************************************************************************/

    /**
     * @param StorageDriverInterface $storage   Driver de almacenamiento activo.
     * @param FileInspector          $inspector Detector de MIME real (finfo) compartido.
     */
    public function __construct(StorageDriverInterface $storage, FileInspector $inspector) {
        $this->storage   = $storage;
        $this->inspector = $inspector;
    }

    /*******************************************************************************************************************/
    /*                                                  Métodos públicos                                               */
    /*******************************************************************************************************************/

    /******************************************************************************************/
    /**
     * Explorador de archivos seguro.
     *
     * Orquesta las responsabilidades delegadas a métodos privados:
     * 1. Resuelve y valida la ruta segura.
     * 2. Construye los MIME permitidos.
     * 3. Lista y filtra el contenido del directorio.
     *
     * @param array $Data Parámetros de entrada:
     *  - route : ruta encriptada base
     *  - path  : subruta desde el frontend (ofuscada)
     *  - tipos : tipos de archivos permitidos (ej: "image,pdf") o "all"
     * @return array Lista de archivos y carpetas con metadata (nombre, tamaño, fecha, icono)
     */
    public function fileExplorer(array $Data = []): array {

        // Resuelve la ruta real evitando "Path Traversal" (../)
        $relativePath = $this->resolveExplorerRelativePath($Data);

        // Construye la lista de MIME permitidos según los tipos configurados
        $allowedMimes = FilePolicy::allowedMimes(
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
     * CREACIÓN SEGURA DE CARPETAS (HARDENING).
     *
     * Crea una carpeta dentro de una ruta base controlada aplicando validaciones
     * de seguridad y consistencia (sanitización, rechazo de nombres vacíos,
     * normalización de rutas, prevención de Path Traversal).
     *
     * @param array $PostData Parámetros de entrada:
     *  - base : ruta base (obligatoria, controlada por backend)
     *  - path : subruta dentro de la base
     *  - name : nombre de la nueva carpeta
     * @return array Resultado: ['success' => bool, 'message' => string (solo en error)]
     */
    public function createFolder(array $PostData = []): array {

        // Validacion de parametros de entrada
        if (empty($PostData['name'])) {
            return ['success' => false, 'message' => 'Nombre no definido'];
        }

        // Sanitiza el nombre de la carpeta para evitar caracteres ilegales en sistemas de archivos
        $folderName = FilePathGuard::sanitizeFolderName($PostData['name']);

        /*******************************************************************/
        // SEGURIDAD CRÍTICA: si el nombre se reduce a cadena vacía ("..", ".",
        // "!!!") no se creará ninguna carpeta.
        /*******************************************************************/
        if ($folderName === '') {
            return ['success' => false, 'message' => 'Nombre de carpeta inválido'];
        }

        // Construcción segura de la ruta jerárquica
        $subRoute     = isset($PostData['SubRoute']) ? trim(FilePathGuard::sanitizePath($PostData['SubRoute']), '/') : '';
        $relativePath = $subRoute !== '' ? $subRoute . '/' : '';
        $relativePath .= isset($PostData['path'])
            ? trim(FilePathGuard::sanitizePath($PostData['path']), '/') . '/'
            : '';

        // Nombre de la carpeta (ya validado arriba)
        $relativePath .= $folderName;

        // Normaliza posibles dobles slashes (extra seguridad)
        $relativePath = preg_replace('#/+#', '/', $relativePath);

        // Creacion de la carpeta (Manejo de errores detallado)
        return $this->storage->createDirectory($relativePath);
    }

    /******************************************************************************************/
    /**
     * Elimina una carpeta y todo su contenido usando el driver activo.
     *
     * @param array $PostData Parámetros de entrada:
     *  - SubRoute : subruta base opcional (controlada por backend)
     *  - path     : subruta dentro de la base
     *  - name     : nombre de la carpeta a eliminar
     * @return array Resultado: ['success' => bool, 'message' => string]
     */
    public function deleteFolder(array $PostData = []): array {

        // Validacion de parametros de entrada
        if (empty($PostData['name'])) {
            return ['success' => false, 'message' => 'Nombre de carpeta no definido'];
        }

        // Ensamblado de la ruta padre (SubRoute + path), ya saneada y sin separadores colgantes
        $subRoute   = isset($PostData['SubRoute']) ? trim(FilePathGuard::sanitizePath($PostData['SubRoute']), '/') : '';
        $parentPath = $subRoute !== '' ? $subRoute : '';
        $parentPath .= isset($PostData['path'])
            ? ($parentPath !== '' ? '/' : '') . trim(FilePathGuard::sanitizePath($PostData['path']), '/')
            : '';
        $parentPath = trim(preg_replace('#/+#', '/', $parentPath), '/');

        // Sanitiza el nombre de la carpeta para evitar caracteres ilegales en sistemas de archivos
        $folderName = FilePathGuard::sanitizeFolderName($PostData['name']);

        /*******************************************************************/
        // SEGURIDAD CRÍTICA: un nombre que la sanitización reduce a cadena
        // vacía ("..", ".", "!!!") NO identifica ninguna carpeta.
        /*******************************************************************/
        if ($folderName === '') {
            return [
                'success' => false,
                'message' => $parentPath === ''
                    ? 'No se permite eliminar la carpeta raíz'
                    : 'Nombre de carpeta inválido',
            ];
        }

        // Ruta final: la carpeta a eliminar es SIEMPRE hija directa de la ruta padre
        $relativePath = $parentPath !== '' ? $parentPath . '/' . $folderName : $folderName;

        // Elimina dobles slashes y espacios residuales
        $relativePath = trim(preg_replace('#/+#', '/', $relativePath), '/');

        /*******************************************************************/
        // SEGURIDAD CRÍTICA: impedir eliminación de la raíz.
        /*******************************************************************/
        if ($relativePath === '') {
            return ['success' => false, 'message' => 'No se permite eliminar la carpeta raíz'];
        }

        // Eliminacion de la carpeta (Manejo de errores detallado)
        return $this->storage->deleteDirectory($relativePath);
    }

    /*******************************************************************************************************************/
    /*                                              Métodos privados                                                   */
    /*******************************************************************************************************************/

    /******************************************************************************************/
    /**
     * Filtra y construye la lista de archivos aplicando políticas de seguridad multicapa.
     *
     * Procesa las entradas crudas de cualquier driver y decide qué mostrar al usuario
     * basándose en listas de nombres, carpetas y extensiones prohibidas. Si el servidor
     * es local, realiza una inspección profunda del tipo MIME real.
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

            // Excluir archivos ocultos y nombres sensibles/excluidos
            if (FilePolicy::excludedEntryName($name)) { continue; }

            // Excluir carpetas del sistema o privadas (EXCLUDED_FOLDERS)
            if ($entry['type'] === 'folder' && FilePolicy::excludedFolder($lower)) { continue; }

            /*************** 2. Filtros de Extensión ***************/
            // Obtener extensión
            $ext = pathinfo($lower, PATHINFO_EXTENSION);

            // Bloquear extensiones peligrosas (ej: .php, .exe, .sh)
            if ($ext !== '' && FilePolicy::forbiddenExtension($lower)) { continue; }

            // Seguridad: Bloquear archivos que no tienen extensión (binarios o scripts de sistema)
            if ($entry['type'] === 'file' && $ext === '') { continue; }

            /*************** 3. Validación de Contenido (Deep Scan) ***************/

            /**
             * Validación MIME real:
             * Solo es posible en almacenamiento LOCAL. En la nube (S3/GCS) se confía
             * en los filtros anteriores.
             */
            if ($isLocal && $entry['type'] === 'file') {
                $uploadFolder = rtrim(ConfigAPP::APP['uploadFolder'], '/');
                $fullPath     = $uploadFolder . '/' . ltrim($dirPath . '/' . $name, '/');

                if (file_exists($fullPath)) {
                    // Inspección de "Magic Bytes" mediante finfo
                    $mime = $this->inspector->realMimeType($fullPath);

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
     *
     * Desencripta la ruta base del servidor, procesa la subruta enviada desde el frontend
     * (revirtiendo el transporte de tokens) y combina ambas piezas en una ruta limpia.
     *
     * @param array $Data Contiene 'route' (base encriptada) y 'path' (subruta ofuscada).
     * @return string Ruta relativa final, normalizada y segura para el driver de almacenamiento.
     */
    private function resolveExplorerRelativePath(array $Data): string {

        $fnc_Codification = new FunctionsSecurityCodification();

        /*************** 1. Desencriptación de Base ***************/
        $decryptedBase = $fnc_Codification->encryptDecrypt('decrypt', $Data['route']);
        if (($decryptedBase['success'] ?? false) !== true || !isset($decryptedBase['data']) || $decryptedBase['data'] === false) {
            return '';
        }
        $safeBase = FilePathGuard::sanitizePath($decryptedBase['data']);

        /*************** 2. Procesamiento de Subruta ***************/
        /**
         * Decodificación del transporte del Frontend:
         * El GET de listado transporta la ruta en un único token de Fat-Free (@path), que
         * no admite "/". El frontend codifica la ruta con EXPLORER_SLASH_TOKEN ('/') y usa
         * EXPLORER_ROOT_TOKEN para la raíz (segmento vacío no permitido). Aquí se revierte.
         *
         * No es evasión de WAF: es la codificación obligatoria del transporte en un solo
         * segmento. La seguridad real de la ruta la aporta FilePathGuard::sanitizePath() + el
         * seguro de "..". Colisión teórica: un nombre de carpeta que contenga literalmente el
         * token se decodificaría mal (impacto: navegación a carpeta errónea, sin escalada).
         */
        $relativePath = isset($Data['path'])
            ? str_replace(
                [FilePolicy::EXPLORER_ROOT_TOKEN, FilePolicy::EXPLORER_SLASH_TOKEN],
                ['', '/'],
                $Data['path']
            )
            : '';
        $relativePath = FilePathGuard::sanitizePath($relativePath);

        /*************** 3. Combinación y Normalización ***************/
        $combined = trim($safeBase . '/' . $relativePath, '/');
        $combined = preg_replace('#/+#', '/', $combined);

        /*************** 4. Seguro de Vida (Hardening) ***************/
        /**
         * Si la ruta combinada contiene intentos de retroceso (..), se aborta la navegación
         * profunda y se devuelve a la base segura.
         */
        if (str_contains($combined, '..')) {
            return $safeBase;
        }

        return $combined;
    }
}
