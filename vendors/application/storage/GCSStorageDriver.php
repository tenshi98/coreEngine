<?php
/**
 * Driver de almacenamiento Google Cloud Storage (GCS)
 *
 * Utiliza el SDK oficial de Google Cloud (google/cloud-storage).
 * Instalación (ver README):
 *   cd coreEngine/vendors/libs/gcs-sdk && composer require google/cloud-storage
 *
 * Configuración requerida en ConfigAPP::APP:
 *   'gcsKeyFile'    => '/ruta/a/service-account.json'  // JSON de cuenta de servicio
 *   'gcsBucket'     => 'mi-bucket-gcs'
 *   'gcsPrefix'     => 'uploads'                       // prefijo global dentro del bucket
 *   'gcsProjectId'  => 'mi-proyecto-gcp'
 *   'gcsUrlSigned'  => false                           // true = URLs firmadas
 *   'gcsUrlExpiry'  => 3600                            // segundos de validez (firmadas)
 *
 * @package App\Storage\Drivers
 */
class GCSStorageDriver implements StorageDriverInterface
{
    /*──────────────────────────────────────────────────────────────────────*/
    /*  Constantes internas                                                  */
    /*──────────────────────────────────────────────────────────────────────*/

    private const SDK_AUTOLOAD = __DIR__ . '/../../libs/gcs-sdk/vendor/autoload.php';

    /*──────────────────────────────────────────────────────────────────────*/
    /*  Instancias / estado                                                  */
    /*──────────────────────────────────────────────────────────────────────*/

    private \Google\Cloud\Storage\StorageClient $gcs;
    private \Google\Cloud\Storage\Bucket        $bucket;
    private string $bucketName;
    private string $prefix;
    private bool   $signedUrls;
    private int    $urlExpiry;

    /*──────────────────────────────────────────────────────────────────────*/
    /*  Constructor                                                          */
    /*──────────────────────────────────────────────────────────────────────*/

    public function __construct()
    {
        if (!file_exists(self::SDK_AUTOLOAD)) {
            throw new \RuntimeException(
                'SDK de GCS no encontrado. Ejecuta: ' .
                'cd coreEngine/vendors/libs/gcs-sdk && composer require google/cloud-storage'
            );
        }
        require_once self::SDK_AUTOLOAD;

        $cfg = ConfigAPP::APP;

        foreach (['gcsKeyFile', 'gcsBucket', 'gcsProjectId'] as $key) {
            if (empty($cfg[$key])) {
                throw new \RuntimeException("GCSStorageDriver: falta la configuración '{$key}'");
            }
        }

        if (!file_exists($cfg['gcsKeyFile'])) {
            throw new \RuntimeException("GCSStorageDriver: archivo de credenciales no encontrado: {$cfg['gcsKeyFile']}");
        }

        $this->gcs = new \Google\Cloud\Storage\StorageClient([
            'projectId'   => $cfg['gcsProjectId'],
            'keyFilePath' => $cfg['gcsKeyFile'],
        ]);

        $this->bucketName = $cfg['gcsBucket'];
        $this->bucket     = $this->gcs->bucket($this->bucketName);
        $this->prefix     = trim($cfg['gcsPrefix'] ?? 'uploads', '/');
        $this->signedUrls = (bool)($cfg['gcsUrlSigned'] ?? false);
        $this->urlExpiry  = (int)($cfg['gcsUrlExpiry']  ?? 3600);
    }

    /*──────────────────────────────────────────────────────────────────────*/
    /*  Implementación de la interfaz                                        */
    /*──────────────────────────────────────────────────────────────────────*/

    /** {@inheritdoc} */
    public function upload(string $sourcePath, string $destPath, bool $isContent = false): bool
    {
        try {
            $key  = $this->buildKey($destPath);
            $mime = $this->detectMime($sourcePath, $isContent);

            $options = [
                'name'          => $key,
                'predefinedAcl' => 'private',
            ];

            if ($mime !== '') {
                $options['metadata'] = ['contentType' => $mime];
            }

            if ($isContent) {
                // Convierte el binario a un stream en memoria
                $stream = fopen('php://temp', 'r+');
                fwrite($stream, $sourcePath);
                rewind($stream);
                $this->bucket->upload($stream, $options);
                fclose($stream);
            } else {
                $this->bucket->upload(
                    fopen($sourcePath, 'r'),
                    $options
                );
            }

            return true;

        } catch (\Exception $e) {
            error_log('GCSStorageDriver::upload error: ' . $e->getMessage());
            return false;
        }
    }

    /** {@inheritdoc} */
    public function delete(string $filePath): bool
    {
        try {
            $object = $this->bucket->object($this->buildKey($filePath));
            $object->delete();
            return true;
        } catch (\Google\Cloud\Core\Exception\NotFoundException $e) {
            return true; // idempotente
        } catch (\Exception $e) {
            error_log('GCSStorageDriver::delete error: ' . $e->getMessage());
            return false;
        }
    }

    /** {@inheritdoc} */
    public function exists(string $filePath): bool
    {
        return $this->bucket->object($this->buildKey($filePath))->exists();
    }

    /** {@inheritdoc} */
    public function listDirectory(string $dirPath): array
    {
        $prefix = $this->buildKey($dirPath);
        if (!str_ends_with($prefix, '/')) {
            $prefix .= '/';
        }

        try {
            $objects = $this->bucket->objects([
                'prefix'    => $prefix,
                'delimiter' => '/',
            ]);

            $entries = [];

            foreach ($objects as $obj) {
                /** @var \Google\Cloud\Storage\StorageObject $obj */
                $info = $obj->info();
                $key  = $info['name'];
                $name = basename(rtrim($key, '/'));

                if ($name === '' || $key === $prefix) {
                    continue;
                }

                $isDir = str_ends_with($key, '/');
                $entries[] = [
                    'name' => $name,
                    'type' => $isDir ? 'folder' : 'file',
                    'size' => $isDir ? null : (int)($info['size'] ?? 0),
                    'date' => isset($info['updated'])
                        ? date('Y-m-d H:i:s', strtotime($info['updated']))
                        : date('Y-m-d H:i:s'),
                ];
            }

            return $entries;

        } catch (\Exception $e) {
            error_log('GCSStorageDriver::listDirectory error: ' . $e->getMessage());
            return [];
        }
    }

    /** {@inheritdoc} */
    public function createDirectory(string $dirPath): array
    {
        $key = $this->buildKey($dirPath);
        if (!str_ends_with($key, '/')) {
            $key .= '/';
        }

        try {
            if ($this->bucket->object($key)->exists()) {
                return ['success' => false, 'message' => 'La carpeta ya existe'];
            }

            $this->bucket->upload('', [
                'name'     => $key,
                'metadata' => ['contentType' => 'application/x-directory'],
            ]);

            return ['success' => true, 'message' => 'Carpeta creada correctamente'];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error GCS: ' . $e->getMessage()];
        }
    }

    /** {@inheritdoc} */
    public function deleteDirectory(string $dirPath): array
    {
        $prefix = $this->buildKey($dirPath);
        if (!str_ends_with($prefix, '/')) {
            $prefix .= '/';
        }

        try {
            // Lista TODOS los objetos que comparten el prefijo (sin delimiter para recorrer subcarpetas)
            $objects = $this->bucket->objects(['prefix' => $prefix]);

            $count = 0;
            foreach ($objects as $obj) {
                /** @var \Google\Cloud\Storage\StorageObject $obj */
                try {
                    $obj->delete();
                    $count++;
                } catch (\Google\Cloud\Core\Exception\NotFoundException $e) {
                    // Ya no existía; se continúa sin interrumpir el proceso
                }
            }

            // Si no se encontró ningún objeto, la carpeta no existía
            if ($count === 0) {
                // Puede que solo existiera el objeto "directorio vacío" → comprobamos
                $dirObject = $this->bucket->object($prefix);
                if ($dirObject->exists()) {
                    $dirObject->delete();
                    return ['success' => true, 'message' => 'Carpeta eliminada correctamente'];
                }
                return ['success' => true, 'message' => 'La carpeta no existe'];
            }

            return ['success' => true, 'message' => 'Carpeta eliminada correctamente'];

        } catch (\Exception $e) {
            error_log('GCSStorageDriver::deleteDirectory error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error GCS: ' . $e->getMessage()];
        }
    }

    /** {@inheritdoc} */
    public function getUrl(string $filePath, int $expiresIn = 0): string
    {
        $key    = $this->buildKey($filePath);
        $expiry = $expiresIn > 0 ? $expiresIn : $this->urlExpiry;

        if ($this->signedUrls || $expiresIn > 0) {
            $object = $this->bucket->object($key);
            return $object->signedUrl(
                new \DateTime("now + {$expiry} seconds"),
                ['version' => 'v4', 'method' => 'GET']
            );
        }

        // URL pública (requiere que el objeto sea public-read)
        return "https://storage.googleapis.com/{$this->bucketName}/{$key}";
    }

    /** {@inheritdoc} */
    public function getMimeType(string $filePath): string
    {
        try {
            $info = $this->bucket->object($this->buildKey($filePath))->info();
            return $info['contentType'] ?? '';
        } catch (\Exception $e) {
            return '';
        }
    }

    /** {@inheritdoc} */
    public function getMainPathUrl(): string
    {
        $prefix = $this->prefix !== '' ? $this->prefix . '/' : '';
        return 'https://storage.googleapis.com/' . $this->bucketName . '/' . $prefix;
    }

    /*──────────────────────────────────────────────────────────────────────*/
    /*  Helpers privados                                                     */
    /*──────────────────────────────────────────────────────────────────────*/

    private function buildKey(string $filePath): string
    {
        $clean = ltrim($filePath, '/');
        return $this->prefix !== ''
            ? $this->prefix . '/' . $clean
            : $clean;
    }

    private function detectMime(string $sourcePath, bool $isContent): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        if ($isContent) {
            return $finfo->buffer($sourcePath) ?: '';
        }
        if (file_exists($sourcePath)) {
            return $finfo->file($sourcePath) ?: '';
        }
        return '';
    }
}
