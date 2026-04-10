<?php
/**
 * StorageDriverFactory
 *
 * Fábrica que instancia el driver de almacenamiento correcto
 * según el valor de ConfigAPP::APP['uploadServer'].
 *
 * Valores soportados:
 *   'local'  → LocalStorageDriver  (servidor Apache / sistema de archivos local)
 *   's3'     → S3StorageDriver     (Amazon S3 o compatible: MinIO, DO Spaces, R2)
 *   'gcs'    → GCSStorageDriver    (Google Cloud Storage)
 *   'sftp'   → SFTPStorageDriver   (Servidor remoto vía SFTP)
 *
 * Uso:
 *   $driver = StorageDriverFactory::make();
 *   $driver->upload($tmpPath, 'docs/file.pdf');
 *
 * @package App\Storage
 */
class StorageDriverFactory
{
    /*──────────────────────────────────────────────────────────────────────*/
    /*  Mapa de drivers disponibles                                          */
    /*──────────────────────────────────────────────────────────────────────*/

    private const DRIVER_MAP = [
        'local' => LocalStorageDriver::class,
        's3'    => S3StorageDriver::class,
        'gcs'   => GCSStorageDriver::class,
        'sftp'  => SFTPStorageDriver::class,
    ];

    /*──────────────────────────────────────────────────────────────────────*/
    /*  Instancia singleton del driver activo                                */
    /*──────────────────────────────────────────────────────────────────────*/

    private static ?StorageDriverInterface $instance = null;

    /*──────────────────────────────────────────────────────────────────────*/
    /*  Métodos públicos                                                     */
    /*──────────────────────────────────────────────────────────────────────*/

    /**
     * Retorna la instancia activa del driver de almacenamiento.
     * Se instancia una sola vez (singleton ligero por petición PHP).
     *
     * @return StorageDriverInterface
     * @throws \RuntimeException Si el driver configurado no existe
     */
    public static function make(): StorageDriverInterface
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        // Lee el servidor configurado; por defecto 'local' para compatibilidad
        $server = strtolower(trim(ConfigAPP::APP['uploadServer'] ?? 'local'));

        if (!array_key_exists($server, self::DRIVER_MAP)) {
            throw new \RuntimeException(
                "StorageDriverFactory: driver '{$server}' no reconocido. " .
                "Opciones válidas: " . implode(', ', array_keys(self::DRIVER_MAP))
            );
        }

        // Carga el archivo del driver si aún no está incluido
        self::requireDriver($server);

        $class            = self::DRIVER_MAP[$server];
        self::$instance   = new $class();

        return self::$instance;
    }

    /**
     * Fuerza la reinstanciación del driver (útil en tests o cambio dinámico).
     */
    public static function reset(): void
    {
        self::$instance = null;
    }

    /*──────────────────────────────────────────────────────────────────────*/
    /*  Helpers privados                                                     */
    /*──────────────────────────────────────────────────────────────────────*/

    private static function requireDriver(string $server): void
    {
        $driverFiles = [
            'local' => __DIR__ . '/LocalStorageDriver.php',
            's3'    => __DIR__ . '/S3StorageDriver.php',
            'gcs'   => __DIR__ . '/GCSStorageDriver.php',
            'sftp'  => __DIR__ . '/SFTPStorageDriver.php',
        ];

        // Carga la interfaz si no está disponible
        if (!interface_exists('StorageDriverInterface', false)) {
            require_once __DIR__ . '/StorageDriverInterface.php';
        }

        if (!class_exists(self::DRIVER_MAP[$server], false)) {
            require_once $driverFiles[$server];
        }
    }
}
