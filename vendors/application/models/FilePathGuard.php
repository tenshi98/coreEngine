<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class FilePathGuard
 *
 * Sanitización de rutas y nombres de archivo/carpeta. Es la defensa del núcleo
 * contra "Path Traversal" y caracteres peligrosos en el sistema de archivos.
 *
 * Extraída de FileManager (RF-009a). Métodos estáticos y puros: no dependen de
 * la instancia ni de la configuración de la aplicación.
 */
class FilePathGuard {

    /*******************************************************************************************************************/
    /*                                              Métodos públicos                                                   */
    /*******************************************************************************************************************/

    /******************************************************************************************/
    /**
     * Sanitiza una ruta eliminando secuencias peligrosas y caracteres no permitidos.
     *
     * Primero decodifica la URL para capturar intentos de evasión mediante encoding,
     * luego elimina cualquier intento de subir de nivel en el directorio (..) y
     * finalmente aplica una lista blanca de caracteres permitidos (letras, números,
     * slash, guiones y guiones bajos).
     *
     * @param string $path Ruta de entrada potencialmente insegura.
     * @return string Ruta limpia y segura para el sistema de archivos.
     */
    public static function sanitizePath(string $path): string {
        // 1. Decodifica la URL para neutralizar bypasses como %2E%2E%2F (../)
        $decoded = rawurldecode($path);

        // 2. Elimina secuencias de dos o más puntos consecutivos para evitar navegar hacia atrás
        $clean   = preg_replace('/\.{2,}/', '', $decoded);

        // 3. Filtra y deja solo caracteres alfanuméricos, slashes, guiones y guiones bajos
        return preg_replace('/[^a-zA-Z0-9\/\-_]/', '', $clean);
    }

    /******************************************************************************************/
    /**
     * Sanitiza el nombre de una carpeta eliminando cualquier carácter especial o de ruta.
     *
     * A diferencia de sanitizePath, es más restrictivo: no permite el carácter "/". Su
     * objetivo es garantizar que el nombre de un directorio sea una cadena simple y segura,
     * sin posibilidad de inyectar subdirectorios o comandos.
     *
     * Decodifica la URL antes de filtrar para que un nombre codificado no evade el filtro:
     * "%2e%2e" se normaliza a ".." y termina descartado.
     *
     * @param string $name Nombre de carpeta propuesto por el usuario.
     * @return string Nombre de carpeta sanitizado (alfanumérico, guion y guion bajo).
     */
    public static function sanitizeFolderName(string $name): string {
        // 1. Decodifica la URL para neutralizar nombres codificados (%2e%2e → "..")
        $decoded = rawurldecode($name);

        // 2. Elimina absolutamente todo lo que no sea una letra, número, guion o guion bajo
        return preg_replace('/[^a-zA-Z0-9_\-]/', '', $decoded);
    }

    /******************************************************************************************/
    /**
     * Sanitiza el nombre de archivo para prevenir:
     * - Path Traversal (ej: ../../archivo.php)
     * - Inyección de caracteres peligrosos
     * - Uso de nombres no válidos en el sistema de archivos
     *
     * Reglas:
     * - Se eliminan rutas y se conserva solo el nombre del archivo.
     * - Se reemplazan caracteres no permitidos por "_".
     *
     * @param string $filename Nombre original del archivo.
     * @return string Nombre de archivo seguro.
     */
    public static function sanitizeFileName(string $filename): string {

        // Elimina cualquier componente de ruta (previene path traversal)
        // Ejemplo: "../../etc/passwd" → "passwd"
        $filename = basename($filename);

        // Reemplaza cualquier carácter que no sea letras, números, punto, guion o guion bajo
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

        // Retorna el nombre sanitizado
        return $filename;
    }
}
