<?php
/**
 * Driver de almacenamiento SFTP
 *
 * Utiliza la librería phpseclib (sin extensión SSH2 requerida).
 * Instalación (ver README):
 *   cd coreEngine/vendors/libs/sftp-sdk && composer require phpseclib/phpseclib:~3.0
 *
 * Configuración requerida en ConfigAPP::APP:
 *   'sftpHost'       => 'sftp.ejemplo.com'
 *   'sftpPort'       => 22
 *   'sftpUser'       => 'mi_usuario'
 *   'sftpPassword'   => 'mi_password'          // o vacío si se usa clave privada
 *   'sftpPrivateKey' => '/ruta/a/id_rsa'        // opcional, ruta a la clave privada
 *   'sftpKeyPass'    => ''                      // passphrase de la clave privada (si aplica)
 *   'sftpTimeout'    => 30                      // timeout de conexión en segundos
 *   'sftpBasePath'   => '/var/www/uploads'      // ruta base en el servidor remoto
 *   'sftpBaseUrl'    => 'https://cdn.ejemplo.com/uploads'  // URL pública base (opcional)
 *
 * @package App\Storage\Drivers
 */
class SFTPStorageDriver implements StorageDriverInterface
{
    /*──────────────────────────────────────────────────────────────────────*/
    /*  Constantes internas                                                  */
    /*──────────────────────────────────────────────────────────────────────*/

    private const SDK_AUTOLOAD = __DIR__ . '/../../libs/sftp-sdk/vendor/autoload.php';

    /*──────────────────────────────────────────────────────────────────────*/
    /*  Instancias / estado                                                  */
    /*──────────────────────────────────────────────────────────────────────*/

    private \phpseclib3\Net\SFTP $sftp;
    private string $basePath;
    private string $baseUrl;

    /*──────────────────────────────────────────────────────────────────────*/
    /*  Constructor                                                          */
    /*──────────────────────────────────────────────────────────────────────*/

    public function __construct()
    {
        if (!file_exists(self::SDK_AUTOLOAD)) {
            throw new \RuntimeException(
                'phpseclib no encontrado. Ejecuta: ' .
                'cd coreEngine/vendors/libs/sftp-sdk && composer require phpseclib/phpseclib:~3.0'
            );
        }
        require_once self::SDK_AUTOLOAD;

        $cfg = ConfigAPP::APP;

        foreach (['sftpHost', 'sftpUser', 'sftpBasePath'] as $key) {
            if (empty($cfg[$key])) {
                throw new \RuntimeException("SFTPStorageDriver: falta la configuración '{$key}'");
            }
        }

        $this->basePath = rtrim($cfg['sftpBasePath'], '/');
        $this->baseUrl  = rtrim($cfg['sftpBaseUrl'] ?? '', '/');

        // Conectar al servidor SFTP
        $this->sftp = new \phpseclib3\Net\SFTP(
            $cfg['sftpHost'],
            (int)($cfg['sftpPort'] ?? 22),
            (int)($cfg['sftpTimeout'] ?? 30)
        );

        // Autenticación: clave privada o password
        if (!empty($cfg['sftpPrivateKey']) && file_exists($cfg['sftpPrivateKey'])) {
            $key = \phpseclib3\Crypt\PublicKeyLoader::load(
                file_get_contents($cfg['sftpPrivateKey']),
                $cfg['sftpKeyPass'] ?? false
            );
            $loginOk = $this->sftp->login($cfg['sftpUser'], $key);
        } else {
            $loginOk = $this->sftp->login($cfg['sftpUser'], $cfg['sftpPassword'] ?? '');
        }

        if (!$loginOk) {
            throw new \RuntimeException('SFTPStorageDriver: autenticación fallida en ' . $cfg['sftpHost']);
        }
    }

    /*──────────────────────────────────────────────────────────────────────*/
    /*  Implementación de la interfaz                                        */
    /*──────────────────────────────────────────────────────────────────────*/

    /** {@inheritdoc} */
    public function upload(string $sourcePath, string $destPath, bool $isContent = false): bool
    {
        $fullDest = $this->buildPath($destPath);
        $this->ensureRemoteDir(dirname($fullDest));

        if ($isContent) {
            // Sube el binario directamente desde una variable en memoria
            return $this->sftp->put($fullDest, $sourcePath, \phpseclib3\Net\SFTP::SOURCE_STRING);
        }

        return $this->sftp->put($fullDest, $sourcePath, \phpseclib3\Net\SFTP::SOURCE_LOCAL_FILE);
    }

    /** {@inheritdoc} */
    public function delete(string $filePath): bool
    {
        $full = $this->buildPath($filePath);

        if (!$this->sftp->file_exists($full)) {
            return true; // idempotente
        }

        return $this->sftp->delete($full);
    }

    /** {@inheritdoc} */
    public function exists(string $filePath): bool
    {
        return $this->sftp->file_exists($this->buildPath($filePath));
    }

    /** {@inheritdoc} */
    public function listDirectory(string $dirPath): array
    {
        $full    = $this->buildPath($dirPath);
        $rawList = $this->sftp->rawlist($full);

        if ($rawList === false) {
            return [];
        }

        $entries = [];

        foreach ($rawList as $name => $info) {
            if ($name === '.' || $name === '..') {
                continue;
            }

            $isDir = ($info['type'] ?? 0) === NET_SFTP_TYPE_DIRECTORY;

            $entries[] = [
                'name' => $name,
                'type' => $isDir ? 'folder' : 'file',
                'size' => $isDir ? null : (int)($info['size'] ?? 0),
                'date' => isset($info['mtime'])
                    ? date('Y-m-d H:i:s', $info['mtime'])
                    : date('Y-m-d H:i:s'),
            ];
        }

        return $entries;
    }

    /** {@inheritdoc} */
    public function createDirectory(string $dirPath): array
    {
        $full = $this->buildPath($dirPath);

        if ($this->sftp->is_dir($full)) {
            return ['success' => false, 'message' => 'La carpeta ya existe'];
        }

        if ($this->sftp->mkdir($full, 0755, true)) {
            return ['success' => true, 'message' => 'Carpeta creada correctamente'];
        }

        return ['success' => false, 'message' => 'No se pudo crear la carpeta en SFTP'];
    }

    /** {@inheritdoc} */
    public function deleteDirectory(string $dirPath): array
    {
        $full = $this->buildPath($dirPath);

        // Si no existe, se considera exitoso (idempotente)
        if (!$this->sftp->is_dir($full)) {
            return ['success' => true, 'message' => 'La carpeta no existe'];
        }

        // phpseclib delete() con el flag $recursive=true elimina el árbol completo
        if ($this->sftp->delete($full, true)) {
            return ['success' => true, 'message' => 'Carpeta eliminada correctamente'];
        }

        return ['success' => false, 'message' => 'No se pudo eliminar la carpeta en SFTP'];
    }

    /** {@inheritdoc} */
    public function getUrl(string $filePath, int $expiresIn = 0): string
    {
        // SFTP no genera URLs públicas nativas; se sirven desde el servidor web
        if ($this->baseUrl === '') {
            return '';
        }
        return $this->baseUrl . '/' . ltrim($filePath, '/');
    }

    /** {@inheritdoc} */
    public function getMimeType(string $filePath): string
    {
        // SFTP no retorna MIME directamente; se infiere por extensión
        $ext   = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimes = [
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
            'gif' => 'image/gif',  'webp' => 'image/webp', 'pdf' => 'application/pdf',
            'zip' => 'application/zip', 'mp4' => 'video/mp4',
            'doc' => 'application/msword',
            'docx'=> 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx'=> 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
        return $mimes[$ext] ?? 'application/octet-stream';
    }

    /** {@inheritdoc} */
    public function getMainPathUrl(): string
    {
        return $this->baseUrl !== '' ? $this->baseUrl . '/' : '';
    }

    /*──────────────────────────────────────────────────────────────────────*/
    /*  Helpers privados                                                     */
    /*──────────────────────────────────────────────────────────────────────*/

    private function buildPath(string $filePath): string
    {
        return $this->basePath . '/' . ltrim($filePath, '/');
    }

    private function ensureRemoteDir(string $dir): void
    {
        if (!$this->sftp->is_dir($dir)) {
            $this->sftp->mkdir($dir, 0755, true);
        }
    }
}
