<?php
/**
 * Interface StorageDriverInterface
 *
 * Contrato que deben implementar todos los drivers de almacenamiento.
 * Permite intercambiar el backend (local, S3, GCS, FTP) sin modificar FileManager.
 *
 * @package App\Storage
 */
interface StorageDriverInterface
{
    /**
     * Sube un archivo al servidor de almacenamiento.
     *
     * @param string $sourcePath     Ruta temporal del archivo (tmp_name) o contenido binario
     * @param string $destPath       Ruta destino relativa dentro del bucket/carpeta (sin slash inicial)
     * @param bool   $isContent      true = $sourcePath contiene el binario directo, false = es una ruta de archivo
     * @return bool
     */
    public function upload(string $sourcePath, string $destPath, bool $isContent = false): bool;

    /**
     * Elimina un archivo del servidor de almacenamiento.
     *
     * @param string $filePath Ruta relativa del archivo dentro del bucket/carpeta
     * @return bool
     */
    public function delete(string $filePath): bool;

    /**
     * Verifica si un archivo existe en el servidor.
     *
     * @param string $filePath Ruta relativa del archivo
     * @return bool
     */
    public function exists(string $filePath): bool;

    /**
     * Lista el contenido de un directorio.
     *
     * @param string $dirPath Ruta relativa del directorio
     * @return array<array{name: string, type: string, size: int|null, date: string}>
     */
    public function listDirectory(string $dirPath): array;

    /**
     * Crea un directorio (carpeta) en el servidor.
     * En servidores como S3 puede ser una operación vacía o crear un objeto prefijo.
     *
     * @param string $dirPath Ruta relativa del directorio a crear
     * @return array{success: bool, message: string}
     */
    public function createDirectory(string $dirPath): array;

    /**
     * Elimina un directorio y todo su contenido de forma recursiva.
     * En S3/GCS elimina todos los objetos cuya clave comience con el prefijo dado.
     *
     * @param string $dirPath Ruta relativa del directorio a eliminar
     * @return array{success: bool, message: string}
     */
    public function deleteDirectory(string $dirPath): array;

    /**
     * Retorna la URL pública (o firmada) de acceso a un archivo.
     *
     * @param string $filePath  Ruta relativa del archivo
     * @param int    $expiresIn Segundos de validez (para URLs firmadas). 0 = URL pública permanente.
     * @return string
     */
    public function getUrl(string $filePath, int $expiresIn = 0): string;

    /**
     * Obtiene el tipo MIME real de un archivo almacenado.
     * Útil para validaciones post-subida o para el explorador de archivos.
     *
     * @param string $filePath Ruta relativa del archivo
     * @return string Tipo MIME o cadena vacía si no se puede determinar
     */
    public function getMimeType(string $filePath): string;

    /**
     * Retorna la URL raíz pública del servidor de almacenamiento activo.
     * Incluye el prefijo/subcarpeta base configurado, con slash final garantizado.
     *
     * Ejemplos por driver:
     *   Local → "https://miapp.com/admin/public/upload/"
     *   S3    → "https://mi-bucket.s3.us-east-1.amazonaws.com/uploads/"
     *   GCS   → "https://storage.googleapis.com/mi-bucket/uploads/"
     *   SFTP  → "https://cdn.ejemplo.com/uploads/"
     *
     * @return string URL raíz con slash final, o cadena vacía si no está configurada
     */
    public function getMainPathUrl(): string;
}
