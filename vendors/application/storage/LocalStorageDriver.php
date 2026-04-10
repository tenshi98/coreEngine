<?php
/**
 * Driver de almacenamiento LOCAL (Apache / servidor propio)
 *
 * Encapsula todas las operaciones sobre el sistema de archivos local.
 * Es el comportamiento original de FileManager, extraído a un driver intercambiable.
 *
 * Configuración requerida en ConfigAPP::APP:
 *   'uploadFolder' => '/var/www/html/admin/public/upload/'
 *
 * @package App\Storage\Drivers
 */
class LocalStorageDriver implements StorageDriverInterface
{
    /*──────────────────────────────────────────────────────────────────────*/
    /*  Instancias / estado                                                  */
    /*──────────────────────────────────────────────────────────────────────*/

    private string  $baseDir;
    private ?\finfo $finfo = null;

    /*──────────────────────────────────────────────────────────────────────*/
    /*  Constructor                                                          */
    /*──────────────────────────────────────────────────────────────────────*/

    public function __construct()
    {
        // La carpeta base se obtiene desde la configuración global de la app
        $this->baseDir = rtrim(ConfigAPP::APP['uploadFolder'], '/') . '/';

        if (!is_dir($this->baseDir)) {
            throw new \RuntimeException("LocalStorageDriver: uploadFolder no existe: {$this->baseDir}");
        }
    }

    /*──────────────────────────────────────────────────────────────────────*/
    /*  Implementación de la interfaz                                        */
    /*──────────────────────────────────────────────────────────────────────*/

    /** {@inheritdoc} */
    public function upload(string $sourcePath, string $destPath, bool $isContent = false): bool
    {
        $fullDest = $this->baseDir . ltrim($destPath, '/');
        $this->ensureDir(dirname($fullDest));

        if ($isContent) {
            // $sourcePath contiene el binario en memoria (archivo Base64 decodificado)
            return file_put_contents($fullDest, $sourcePath) !== false;
        }

        // $sourcePath es la ruta temporal de PHP ($_FILES['tmp_name'])
        return move_uploaded_file($sourcePath, $fullDest);
    }

    /** {@inheritdoc} */
    public function delete(string $filePath): bool
    {
        $full = $this->baseDir . ltrim($filePath, '/');

        if (!file_exists($full)) {
            return true; // idempotente: si no existe, se considera eliminado
        }

        return unlink($full);
    }

    /** {@inheritdoc} */
    public function exists(string $filePath): bool
    {
        return file_exists($this->baseDir . ltrim($filePath, '/'));
    }

    /** {@inheritdoc} */
    public function listDirectory(string $dirPath): array
    {
        $full = $this->baseDir . ltrim($dirPath, '/');

        if (!is_dir($full)) {
            return [];
        }

        $entries = scandir($full);
        if ($entries === false) {
            return [];
        }

        $result = [];
        foreach ($entries as $name) {
            if ($name === '.' || $name === '..') {
                continue;
            }

            $filePath = $full . '/' . $name;
            $stat     = stat($filePath);
            $isDir    = ($stat['mode'] & 0040000) !== 0;

            $result[] = [
                'name' => $name,
                'type' => $isDir ? 'folder' : 'file',
                'size' => $isDir ? null : $stat['size'],
                'date' => date('Y-m-d H:i:s', $stat['mtime']),
            ];
        }

        return $result;
    }

    /** {@inheritdoc} */
    public function createDirectory(string $dirPath): array
    {
        $full = $this->baseDir . ltrim($dirPath, '/');

        if (is_dir($full)) {
            return ['success' => false, 'message' => 'La carpeta ya existe'];
        }

        try {
            if (mkdir($full, 0755, true)) {
                return ['success' => true, 'message' => 'Carpeta creada correctamente'];
            }
            return ['success' => false, 'message' => 'mkdir retornó false'];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Error al crear carpeta: ' . $e->getMessage()];
        }
    }

    /** {@inheritdoc} */
    public function deleteDirectory(string $dirPath): array
    {
        $full = $this->baseDir . ltrim($dirPath, '/');

        // Si la carpeta no existe se considera operación exitosa (idempotente)
        if (!is_dir($full)) {
            return ['success' => true, 'message' => 'La carpeta no existe'];
        }

        // Elimina recursivamente todo el contenido y luego la carpeta raíz
        $deleted = $this->deleteRecursive($full);

        if (!$deleted) {
            return ['success' => false, 'message' => 'No se pudo eliminar la carpeta o parte de su contenido'];
        }

        return ['success' => true, 'message' => 'Carpeta eliminada correctamente'];
    }

    /** {@inheritdoc} */
    public function getUrl(string $filePath, int $expiresIn = 0): string
    {
        // En local, la URL pública se construye a partir de la configuración APP
        $baseUrl = rtrim(ConfigAPP::APP['uploadUrl'] ?? '', '/');
        return $baseUrl . '/' . ltrim($filePath, '/');
    }

    /** {@inheritdoc} */
    public function getMimeType(string $filePath): string
    {
        $full = $this->baseDir . ltrim($filePath, '/');

        if (!file_exists($full)) {
            return '';
        }

        $mime = $this->getFinfo()->file($full);
        return $mime ?: '';
    }

    /** {@inheritdoc} */
    public function getMainPathUrl(): string
    {
        $baseUrl = rtrim(ConfigAPP::APP['uploadUrl'] ?? '', '/');
        return $baseUrl !== '' ? $baseUrl . '/' : '';
    }

    /*──────────────────────────────────────────────────────────────────────*/
    /*  Helpers privados                                                     */
    /*──────────────────────────────────────────────────────────────────────*/

    private function ensureDir(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    /**
     * Elimina un directorio y todo su contenido de forma recursiva.
     * Retorna false si algún elemento no pudo ser eliminado.
     */
    private function deleteRecursive(string $path): bool
    {
        $entries = scandir($path);
        if ($entries === false) {
            return false;
        }

        $success = true;

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $full = $path . '/' . $entry;

            if (is_dir($full)) {
                // Recursión en subdirectorios
                if (!$this->deleteRecursive($full)) {
                    $success = false;
                }
            } else {
                // Elimina el archivo; @ suprime el warning si falla por permisos
                if (!@unlink($full)) {
                    $success = false;
                }
            }
        }

        // Elimina el directorio vacío después de limpiar su contenido
        if (!@rmdir($path)) {
            $success = false;
        }

        return $success;
    }

    private function getFinfo(): \finfo
    {
        return $this->finfo ??= new \finfo(FILEINFO_MIME_TYPE);
    }
}
