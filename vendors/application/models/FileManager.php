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
 * Class FileManager
 *
 * Fachada del subsistema de archivos. Conserva la API pública histórica y delega
 * la lógica en colaboradores con responsabilidad única (RF-009a):
 *
 *   FilePolicy     → reglas puras (listas, tokens, mime, nombres)
 *   FilePathGuard  → sanitización de rutas/nombres
 *   FileInspector  → detección de MIME real (finfo)
 *   FileUploader   → validación y subida (multipart + Base64)
 *   FileExplorer   → explorador y gestión de carpetas
 *
 * Drivers de almacenamiento soportados (sin cambios):
 *   'local' → Sistema de archivos local (Apache)    [por defecto]
 *   's3'    → Amazon S3 (o compatibles: MinIO, R2)
 *   'gcs'   → Google Cloud Storage
 *   'sftp'  → Servidor remoto vía SFTP
 *
 * La firma y semántica de todos los métodos públicos es INVARIABLE respecto a las
 * versiones previas de esta clase (los consumidores no requieren cambios).
 */
class FileManager {

    /*******************************************************************************************************************/
    /*                                                Instancias                                                       */
    /*******************************************************************************************************************/

    private StorageDriverInterface $storage;   // ← driver activo (local | s3 | gcs | sftp)
    private FileUploader           $uploader;
    private FileExplorer           $explorer;

    /*******************************************************************************************************************/
    /*                                                Constructor                                                      */
    /*******************************************************************************************************************/

    public function __construct() {
        // Instancia el driver correcto según ConfigAPP::APP['uploadServer']
        $this->storage  = StorageDriverFactory::make();

        // Inspector MIME compartido entre subida y explorador (misma instancia de finfo)
        $inspector      = new FileInspector();

        $this->uploader = new FileUploader($this->storage, $inspector);
        $this->explorer = new FileExplorer($this->storage, $inspector);
    }

    /*******************************************************************************************************************/
    /*                                         Métodos públicos (delegación)                                           */
    /*******************************************************************************************************************/

    /******************************************************************************************/
    /**
     * Valida la integridad, seguridad y requisitos técnicos de los archivos antes de su procesamiento.
     *
     * @param array $SIS_FILES   Arreglo equivalente a $_FILES con los datos binarios.
     * @param array $arrArchivos Configuración detallada de cada archivo esperado.
     * @param array $PostData    Datos adicionales del contexto de la petición.
     * @return array ['success' => true] si pasa todas las pruebas, o detalle de errores.
     */
    public function validateFiles(array $SIS_FILES, array $arrArchivos, array $PostData = []): array {
        return $this->uploader->validateFiles($SIS_FILES, $arrArchivos, $PostData);
    }

    /******************************************************************************************/
    /**
     * Gestiona la subida física de archivos al servidor o almacenamiento en la nube.
     *
     * @param array $SIS_FILES   Arreglo global $_FILES con los binarios.
     * @param array $arrArchivos Configuración de destino y reglas para cada archivo.
     * @param array $PostData    Datos adicionales (necesario para procesar Base64).
     * @return array Fragmentos SQL (Nombres/Archivos/Update) + success/message.
     */
    public function uploadFile(array $SIS_FILES, array $arrArchivos, array $PostData = []): array {
        return $this->uploader->uploadFile($SIS_FILES, $arrArchivos, $PostData);
    }

    /******************************************************************************************/
    /**
     * Explorador de archivos seguro.
     *
     * @param array $Data route (base encriptada), path (subruta ofuscada), tipos (tipos o "all").
     * @return array Lista de archivos y carpetas con metadata.
     */
    public function fileExplorer(array $Data = []): array {
        return $this->explorer->fileExplorer($Data);
    }

    /******************************************************************************************/
    /**
     * CREACIÓN SEGURA DE CARPETAS (HARDENING).
     *
     * @param array $PostData base (ruta base), path (subruta), name (nombre de la carpeta).
     * @return array ['success' => bool, 'message' => string (solo en error)]
     */
    public function createFolder(array $PostData = []): array {
        return $this->explorer->createFolder($PostData);
    }

    /******************************************************************************************/
    /**
     * Elimina una carpeta y todo su contenido usando el driver activo.
     *
     * @param array $PostData SubRoute, path, name.
     * @return array ['success' => bool, 'message' => string]
     */
    public function deleteFolder(array $PostData = []): array {
        return $this->explorer->deleteFolder($PostData);
    }

    /******************************************************************************************/
    /**
     * Sanitiza una ruta eliminando secuencias peligrosas y caracteres no permitidos.
     * Defensa principal contra "Path Traversal" (../).
     *
     * @param string $path Ruta de entrada potencialmente insegura.
     * @return string Ruta limpia y segura.
     */
    public function sanitizePath(string $path): string {
        return FilePathGuard::sanitizePath($path);
    }

    /******************************************************************************************/
    /**
     * Sanitiza el nombre de una carpeta eliminando cualquier carácter especial o de ruta.
     *
     * @param string $name Nombre de carpeta propuesto por el usuario.
     * @return string Nombre de carpeta sanitizado.
     */
    public function sanitizeFolderName(string $name): string {
        return FilePathGuard::sanitizeFolderName($name);
    }

    /*******************************************************************************************************************/
    /*                                       Métodos públicos (sobre el driver)                                        */
    /*******************************************************************************************************************/

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
        if (empty($SIS_File)) { return false; }

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
        $arrFiles = FilePolicy::splitList($SIS_Files);

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
     * Genera la URL de acceso a un recurso almacenado.
     *
     * @param string $filePath  Ruta relativa del archivo dentro del storage.
     * @param int    $expiresIn Tiempo de vida en segundos (0 para URL permanente).
     * @return string URL completa lista para ser usada.
     */
    public function getFileUrl(string $filePath, int $expiresIn = 0): string {
        // Delega la generación de la URL al driver activo (Local, S3, GCS, etc.)
        return $this->storage->getUrl($filePath, $expiresIn);
    }

    /******************************************************************************************/
    /**
     * Retorna la URL raíz pública configurada para el servidor de almacenamiento.
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
     *
     * @return StorageDriverInterface Instancia del driver (LocalDriver, S3Driver, etc.).
     */
    public function getStorageDriver(): StorageDriverInterface {
        return $this->storage;
    }
}
