<?php
/**
 * Driver de almacenamiento AWS S3
 *
 * Utiliza el SDK oficial de AWS (aws/aws-sdk-php).
 * Instalación (ver README):
 *   cd coreEngine/vendors/libs/aws-sdk && composer require aws/aws-sdk-php
 *
 * Configuración requerida en ConfigAPP::APP:
 *   's3Key'       => 'AKIAIOSFODNN7EXAMPLE'
 *   's3Secret'    => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY'
 *   's3Region'    => 'us-east-1'
 *   's3Bucket'    => 'mi-bucket'
 *   's3Prefix'    => 'uploads'            // prefijo global dentro del bucket (sin slashes)
 *   's3Endpoint'  => ''                   // solo para S3-compatible (MinIO, etc.)
 *   's3UrlSigned' => false                // true = URLs pre-firmadas, false = URL pública
 *   's3UrlExpiry' => 3600                 // segundos de validez para URLs firmadas
 *   's3Acl'       => 'private'            // 'private' | 'public-read'
 *
 * @package App\Storage\Drivers
 */
class S3StorageDriver implements StorageDriverInterface
{
    /*──────────────────────────────────────────────────────────────────────*/
    /*  Constantes internas                                                  */
    /*──────────────────────────────────────────────────────────────────────*/

    private const SDK_AUTOLOAD = __DIR__ . '/../../libs/aws-sdk/vendor/autoload.php';

    /*──────────────────────────────────────────────────────────────────────*/
    /*  Instancias / estado                                                  */
    /*──────────────────────────────────────────────────────────────────────*/

    private \Aws\S3\S3Client $s3;
    private string           $bucket;
    private string           $prefix;        // carpeta raíz dentro del bucket
    private string           $acl;
    private bool             $signedUrls;
    private int              $urlExpiry;

    /*──────────────────────────────────────────────────────────────────────*/
    /*  Constructor                                                          */
    /*──────────────────────────────────────────────────────────────────────*/

    public function __construct()
    {
        // Carga el autoloader del SDK de AWS
        if (!file_exists(self::SDK_AUTOLOAD)) {
            throw new \RuntimeException(
                'SDK de AWS no encontrado. Ejecuta: ' .
                'cd coreEngine/vendors/libs/aws-sdk && composer require aws/aws-sdk-php'
            );
        }
        require_once self::SDK_AUTOLOAD;

        $cfg = ConfigAPP::APP;

        // Valida configuración obligatoria
        foreach (['s3Key', 's3Secret', 's3Region', 's3Bucket'] as $key) {
            if (empty($cfg[$key])) {
                throw new \RuntimeException("S3StorageDriver: falta la configuración '{$key}'");
            }
        }

        // Opciones del cliente S3
        $clientArgs = [
            'version'     => 'latest',
            'region'      => $cfg['s3Region'],
            'credentials' => [
                'key'    => $cfg['s3Key'],
                'secret' => $cfg['s3Secret'],
            ],
        ];

        // Endpoint personalizado (MinIO, DigitalOcean Spaces, Cloudflare R2, etc.)
        if (!empty($cfg['s3Endpoint'])) {
            $clientArgs['endpoint']                = $cfg['s3Endpoint'];
            $clientArgs['use_path_style_endpoint'] = true;
        }

        $this->s3         = new \Aws\S3\S3Client($clientArgs);
        $this->bucket     = $cfg['s3Bucket'];
        $this->prefix     = trim($cfg['s3Prefix'] ?? 'uploads', '/');
        $this->acl        = $cfg['s3Acl']       ?? 'private';
        $this->signedUrls = (bool)($cfg['s3UrlSigned'] ?? false);
        $this->urlExpiry  = (int)($cfg['s3UrlExpiry']  ?? 3600);
    }

    /*──────────────────────────────────────────────────────────────────────*/
    /*  Implementación de la interfaz                                        */
    /*──────────────────────────────────────────────────────────────────────*/

    /** {@inheritdoc} */
    public function upload(string $sourcePath, string $destPath, bool $isContent = false): bool
    {
        try {
            $args = [
                'Bucket' => $this->bucket,
                'Key'    => $this->buildKey($destPath),
                'ACL'    => $this->acl,
            ];

            if ($isContent) {
                // Contenido binario en memoria (Base64 decodificado)
                $args['Body'] = $sourcePath;
            } else {
                // Archivo temporal de PHP
                $args['SourceFile'] = $sourcePath;
            }

            // Detecta y agrega ContentType para mejor manejo por S3
            $mime = $this->detectMime($sourcePath, $isContent);
            if ($mime !== '') {
                $args['ContentType'] = $mime;
            }

            $this->s3->putObject($args);
            return true;

        } catch (\Aws\Exception\AwsException $e) {
            error_log('S3StorageDriver::upload error: ' . $e->getMessage());
            return false;
        }
    }

    /** {@inheritdoc} */
    public function delete(string $filePath): bool
    {
        try {
            $this->s3->deleteObject([
                'Bucket' => $this->bucket,
                'Key'    => $this->buildKey($filePath),
            ]);
            return true;
        } catch (\Aws\Exception\AwsException $e) {
            error_log('S3StorageDriver::delete error: ' . $e->getMessage());
            return false;
        }
    }

    /** {@inheritdoc} */
    public function exists(string $filePath): bool
    {
        return $this->s3->doesObjectExist($this->bucket, $this->buildKey($filePath));
    }

    /** {@inheritdoc} */
    public function listDirectory(string $dirPath): array
    {
        $prefix = $this->buildKey($dirPath);
        if (!str_ends_with($prefix, '/')) {
            $prefix .= '/';
        }

        try {
            $result  = $this->s3->listObjectsV2([
                'Bucket'    => $this->bucket,
                'Prefix'    => $prefix,
                'Delimiter' => '/',
            ]);

            $entries = [];

            // Carpetas (prefijos comunes)
            foreach ($result->get('CommonPrefixes') ?? [] as $cp) {
                $name = basename(rtrim($cp['Prefix'], '/'));
                $entries[] = [
                    'name' => $name,
                    'type' => 'folder',
                    'size' => null,
                    'date' => date('Y-m-d H:i:s'),
                ];
            }

            // Archivos
            foreach ($result->get('Contents') ?? [] as $obj) {
                $key  = $obj['Key'];
                $name = basename($key);

                // Ignorar el propio objeto "directorio vacío" de S3
                if ($name === '' || $key === $prefix) {
                    continue;
                }

                $entries[] = [
                    'name' => $name,
                    'type' => 'file',
                    'size' => (int)$obj['Size'],
                    'date' => $obj['LastModified']->format('Y-m-d H:i:s'),
                ];
            }

            return $entries;

        } catch (\Aws\Exception\AwsException $e) {
            error_log('S3StorageDriver::listDirectory error: ' . $e->getMessage());
            return [];
        }
    }

    /** {@inheritdoc} */
    public function createDirectory(string $dirPath): array
    {
        // S3 no tiene "carpetas" reales; se simula con un objeto vacío de clave "prefix/"
        $key = $this->buildKey($dirPath);
        if (!str_ends_with($key, '/')) {
            $key .= '/';
        }

        // Verifica si ya existe algún objeto con ese prefijo
        try {
            $result = $this->s3->listObjectsV2([
                'Bucket'  => $this->bucket,
                'Prefix'  => $key,
                'MaxKeys' => 1,
            ]);

            if (!empty($result->get('Contents'))) {
                return ['success' => false, 'message' => 'La carpeta ya existe'];
            }

            // Crea el objeto "carpeta"
            $this->s3->putObject([
                'Bucket'      => $this->bucket,
                'Key'         => $key,
                'Body'        => '',
                'ContentType' => 'application/x-directory',
                'ACL'         => $this->acl,
            ]);

            return ['success' => true, 'message' => 'Carpeta creada correctamente'];

        } catch (\Aws\Exception\AwsException $e) {
            return ['success' => false, 'message' => 'Error S3: ' . $e->getMessage()];
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
            // Verifica si existe algo con ese prefijo antes de intentar eliminar
            $check = $this->s3->listObjectsV2([
                'Bucket'  => $this->bucket,
                'Prefix'  => $prefix,
                'MaxKeys' => 1,
            ]);

            if (empty($check->get('Contents'))) {
                return ['success' => true, 'message' => 'La carpeta no existe'];
            }

            // Elimina en lotes de hasta 1000 objetos (límite de la API de S3)
            // El paginador recorre automáticamente todas las páginas
            $paginator = $this->s3->getPaginator('ListObjectsV2', [
                'Bucket' => $this->bucket,
                'Prefix' => $prefix,
            ]);

            foreach ($paginator as $page) {
                $objects = $page->get('Contents') ?? [];
                if (empty($objects)) {
                    continue;
                }

                // Construye el array de claves para eliminación en batch
                $toDelete = array_map(
                    fn(array $obj) => ['Key' => $obj['Key']],
                    $objects
                );

                $this->s3->deleteObjects([
                    'Bucket' => $this->bucket,
                    'Delete' => ['Objects' => $toDelete, 'Quiet' => true],
                ]);
            }

            return ['success' => true, 'message' => 'Carpeta eliminada correctamente'];

        } catch (\Aws\Exception\AwsException $e) {
            error_log('S3StorageDriver::deleteDirectory error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error S3: ' . $e->getMessage()];
        }
    }

    /** {@inheritdoc} */
    public function getUrl(string $filePath, int $expiresIn = 0): string
    {
        $key     = $this->buildKey($filePath);
        $expiry  = $expiresIn > 0 ? $expiresIn : $this->urlExpiry;

        if ($this->signedUrls || $expiresIn > 0) {
            // URL pre-firmada (temporal y segura)
            $cmd = $this->s3->getCommand('GetObject', [
                'Bucket' => $this->bucket,
                'Key'    => $key,
            ]);
            $request = $this->s3->createPresignedRequest($cmd, "+{$expiry} seconds");
            return (string) $request->getUri();
        }

        // URL pública permanente (requiere ACL public-read en el objeto/bucket)
        return "https://{$this->bucket}.s3.{$this->getRegion()}.amazonaws.com/{$key}";
    }

    /** {@inheritdoc} */
    public function getMimeType(string $filePath): string
    {
        try {
            $meta = $this->s3->headObject([
                'Bucket' => $this->bucket,
                'Key'    => $this->buildKey($filePath),
            ]);
            return $meta->get('ContentType') ?? '';
        } catch (\Aws\Exception\AwsException $e) {
            return '';
        }
    }

    /** {@inheritdoc} */
    public function getMainPathUrl(): string
    {
        // Construye la URL raíz usando el mismo patrón que getUrl() pero sin clave de archivo
        $prefix = $this->prefix !== '' ? $this->prefix . '/' : '';

        if (!empty(ConfigAPP::APP['s3Endpoint'])) {
            // S3-compatible (MinIO, DigitalOcean Spaces, Cloudflare R2, etc.)
            $endpoint = rtrim(ConfigAPP::APP['s3Endpoint'], '/');
            return $endpoint . '/' . $this->bucket . '/' . $prefix;
        }

        // Amazon S3 estándar
        return 'https://' . $this->bucket . '.s3.' . $this->getRegion() . '.amazonaws.com/' . $prefix;
    }

    /*──────────────────────────────────────────────────────────────────────*/
    /*  Helpers privados                                                     */
    /*──────────────────────────────────────────────────────────────────────*/

    /**
     * Construye la clave S3 completa: prefijo global + ruta relativa.
     * Ejemplo: prefix="uploads", filePath="docs/file.pdf" → "uploads/docs/file.pdf"
     */
    private function buildKey(string $filePath): string
    {
        $clean = ltrim($filePath, '/');
        return $this->prefix !== ''
            ? $this->prefix . '/' . $clean
            : $clean;
    }

    /** Obtiene la región desde el cliente S3. */
    private function getRegion(): string
    {
        return ConfigAPP::APP['s3Region'] ?? 'us-east-1';
    }

    /** Detecta el MIME real del archivo o contenido. */
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
