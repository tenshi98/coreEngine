# FileManager — Sistema Multi-Driver de Almacenamiento

## Arquitectura

```
coreEngine/
├── vendors/
│   ├── application/
│   │   ├── models/
│   │   │   └── FileManager.php              ← Clase principal (modificada)
│   │   └── storage/
│   │       ├── StorageDriverInterface.php   ← Contrato de todos los drivers
│   │       ├── StorageDriverFactory.php     ← Fábrica: instancia el driver correcto
│   │       ├── LocalStorageDriver.php       ← Driver: servidor Apache/Nginx
│   │       ├── S3StorageDriver.php          ← Driver: Amazon S3 (y compatibles)
│   │       ├── GCSStorageDriver.php         ← Driver: Google Cloud Storage
│   │       └── SFTPStorageDriver.php        ← Driver: servidor remoto vía SFTP
│   └── libs/
│       ├── aws-sdk/                         ← SDK de AWS (instalado con Composer)
│       ├── gcs-sdk/                         ← SDK de GCS (instalado con Composer)
│       └── sftp-sdk/                        ← phpseclib (instalado con Composer)
```

---

## Selección del driver

En `ConfigAPP::APP` añade o cambia la clave:

```php
'uploadServer' => 'local',   // 'local' | 's3' | 'gcs' | 'sftp'
```

Todos los métodos públicos de `FileManager` **mantienen su firma original**. No es necesario
modificar ningún controlador ni vista de la aplicación.

---

## Instalación de drivers

### Driver LOCAL
No requiere instalación adicional. Es el comportamiento original.

```php
// ConfigAPP::APP
'uploadServer' => 'local',
'uploadFolder' => __DIR__ . '/../../../admin/public/upload/',
'uploadUrl'    => 'https://miapp.com/admin/public/upload',
```

---

### Driver AWS S3

```bash
mkdir -p coreEngine/vendors/libs/aws-sdk
cd coreEngine/vendors/libs/aws-sdk
composer init --no-interaction
composer require aws/aws-sdk-php
```

```php
// ConfigAPP::APP
'uploadServer' => 's3',
'uploadFolder' => __DIR__ . '/../../../admin/public/upload/', // mantener para compatibilidad
'uploadUrl'    => 'https://miapp.com/admin/public/upload',
's3Key'        => 'AKIAIOSFODNN7EXAMPLE',
's3Secret'     => 'tu-secret-key',
's3Region'     => 'us-east-1',
's3Bucket'     => 'mi-bucket',
's3Prefix'     => 'uploads',
's3Acl'        => 'private',
's3UrlSigned'  => true,
's3UrlExpiry'  => 3600,
// Solo para S3-compatible (MinIO, Spaces, R2):
's3Endpoint'   => 'https://mi-endpoint.com',
```

**Compatibles con S3:**
| Proveedor            | `s3Endpoint`                                    |
|----------------------|-------------------------------------------------|
| Amazon S3 (original) | _(vacío)_                                       |
| MinIO (self-hosted)  | `https://minio.midominio.com`                   |
| DigitalOcean Spaces  | `https://<region>.digitaloceanspaces.com`       |
| Cloudflare R2        | `https://<account_id>.r2.cloudflarestorage.com` |
| Wasabi               | `https://s3.wasabisys.com`                      |
| Backblaze B2         | `https://s3.<region>.backblazeb2.com`           |

---

### Driver Google Cloud Storage (GCS)

```bash
mkdir -p coreEngine/vendors/libs/gcs-sdk
cd coreEngine/vendors/libs/gcs-sdk
composer init --no-interaction
composer require google/cloud-storage
```

```php
// ConfigAPP::APP
'uploadServer' => 'gcs',
'gcsProjectId' => 'mi-proyecto-gcp',
'gcsKeyFile'   => __DIR__ . '/keys/service-account.json',
'gcsBucket'    => 'mi-bucket',
'gcsPrefix'    => 'uploads',
'gcsUrlSigned' => true,
'gcsUrlExpiry' => 3600,
```

**Pasos en GCP:**
1. Ir a IAM & Admin → Service Accounts
2. Crear cuenta de servicio con rol `Storage Object Admin`
3. Generar clave JSON y guardar en `coreEngine/config/keys/service-account.json`
4. Asegurarse de que el archivo JSON **NO** quede en un directorio público web

---

### Driver SFTP

```bash
mkdir -p coreEngine/vendors/libs/sftp-sdk
cd coreEngine/vendors/libs/sftp-sdk
composer init --no-interaction
composer require phpseclib/phpseclib:~3.0
```

```php
// ConfigAPP::APP — autenticación por password
'uploadServer' => 'sftp',
'sftpHost'     => 'mi-servidor.com',
'sftpPort'     => 22,
'sftpUser'     => 'deploy',
'sftpPassword' => 'mi-password',
'sftpBasePath' => '/var/www/uploads',
'sftpBaseUrl'  => 'https://cdn.mi-servidor.com/uploads',

// — O —  autenticación por clave privada (recomendado)
'sftpPrivateKey' => __DIR__ . '/keys/id_rsa',
'sftpKeyPass'    => '',   // passphrase si la clave la tiene
```

---

## Estructura de carpetas en los servidores remotos

El driver preserva automáticamente la estructura de subcarpetas definida en
`$arrArchivos[]['SubCarpeta']`, independientemente del servidor:

| Configuración              | Local                        | S3 / GCS                     | SFTP                              |
|---------------------------|------------------------------|------------------------------|-----------------------------------|
| `'SubCarpeta' => 'docs'`  | `upload/docs/archivo.pdf`    | `uploads/docs/archivo.pdf`   | `/var/www/uploads/docs/archivo.pdf` |
| `'SubCarpeta' => 'img/2025'` | `upload/img/2025/foto.jpg` | `uploads/img/2025/foto.jpg`  | `/var/www/uploads/img/2025/foto.jpg`|

---

## Métodos públicos (sin cambios de firma)

| Método                          | Descripción                                      |
|---------------------------------|--------------------------------------------------|
| `validateFiles()`               | Valida archivos antes de subir                   |
| `uploadFile()`                  | Sube archivos al driver activo                   |
| `deleteFile()`                  | Elimina un archivo                               |
| `deleteFilesMassive()`          | Elimina múltiples archivos                       |
| `fileExplorer()`                | Explorador de archivos con filtros de seguridad  |
| `createFolder()`                | Crea una carpeta                                 |
| `sanitizePath()`                | Sanitiza una ruta (anti path traversal)          |
| `sanitizeFolderName()`          | Sanitiza un nombre de carpeta                    |

### Nuevos métodos públicos (no rompen compatibilidad)

| Método                          | Descripción                                                       |
|---------------------------------|-------------------------------------------------------------------|
| `getFileUrl($path, $expiry)`    | URL pública o firmada del archivo (útil para S3/GCS)              |
| `getStorageDriver()`            | Retorna el driver activo para usos avanzados                      |

---

## Consideraciones de seguridad por driver

### S3 / GCS
- Usar ACL `private` con URLs pre-firmadas en lugar de `public-read` para datos sensibles.
- Las credenciales (keys, JSON) **nunca** deben estar en directorios accesibles desde web.
- Activar **versioning** en el bucket para recuperación ante eliminaciones accidentales.
- Activar **MFA Delete** para buckets de producción.

### SFTP
- Preferir autenticación por clave privada RSA/ED25519 sobre password.
- Restringir el usuario SFTP a un directorio (chroot jail) en `/etc/ssh/sshd_config`.
- La clave privada debe tener permisos `chmod 600`.

### Local
- La carpeta `upload/` no debe contener `.php` ejecutable (añadir `.htaccess` con `php_flag engine off`).
- Verificar que el servidor web no sirva archivos `.env`, `.htpasswd`, etc.

---

## Ejemplo: cambiar de local a S3 sin tocar los controladores

```php
// ANTES (ConfigAPP.php)
'uploadServer' => 'local',

// DESPUÉS (ConfigAPP.php) — sin tocar ningún controlador
'uploadServer' => 's3',
's3Key'        => 'AKIA...',
's3Secret'     => '...',
's3Region'     => 'us-east-1',
's3Bucket'     => 'mi-bucket',
's3Prefix'     => 'uploads',
's3Acl'        => 'private',
's3UrlSigned'  => true,
```

Los métodos `uploadFile()`, `deleteFile()`, `fileExplorer()`, etc. funcionan
exactamente igual. Solo cambia dónde se almacenan físicamente los archivos.

---

## Agregar un nuevo driver personalizado

1. Crear `coreEngine/vendors/application/storage/MiDriverStorageDriver.php`
2. Implementar `StorageDriverInterface` (todos sus métodos)
3. Registrar en `StorageDriverFactory::DRIVER_MAP`:
   ```php
   private const DRIVER_MAP = [
       ...
       'mi-driver' => MiDriverStorageDriver::class,
   ];
   ```
4. Añadir en `requireDriver()` dentro de `StorageDriverFactory`:
   ```php
   'mi-driver' => __DIR__ . '/MiDriverStorageDriver.php',
   ```
5. Configurar en `ConfigAPP::APP`:
   ```php
   'uploadServer' => 'mi-driver',
   ```
