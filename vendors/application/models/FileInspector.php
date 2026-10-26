<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Class FileInspector
 *
 * Detección del tipo MIME real mediante la extensión Fileinfo (finfo). Inspecciona
 * el contenido (no confía en la extensión ni en el header del cliente), evitando
 * falsificaciones como subir un .php disfrazado de .jpg.
 *
 * Extraída de FileManager (RF-009a). Mantiene una instancia perezosa de finfo
 * reutilizable dentro de la misma petición.
 */
class FileInspector {

    /*******************************************************************************************************************/
    /*                                                Instancias                                                       */
    /*******************************************************************************************************************/

    private ?\finfo $finfo = null;

    /*******************************************************************************************************************/
    /*                                              Métodos públicos                                                   */
    /*******************************************************************************************************************/

    /******************************************************************************************/
    /**
     * Obtiene el tipo MIME real de un archivo en disco.
     *
     * @param string $path Ruta del archivo (ej: $_FILES['tmp_name']).
     * @return string Tipo MIME detectado (ej: "image/png") o cadena vacía si falla.
     */
    public function realMimeType(string $path): string {
        $mime = $this->finfo()->file($path);
        return $mime ?: '';
    }

    /******************************************************************************************/
    /**
     * Obtiene el tipo MIME real de un contenido binario en memoria.
     *
     * @param string $buffer Contenido binario (ej: Base64 ya decodificado).
     * @return string Tipo MIME detectado o cadena vacía si falla.
     */
    public function bufferMimeType(string $buffer): string {
        $mime = $this->finfo()->buffer($buffer);
        return $mime ?: '';
    }

    /*******************************************************************************************************************/
    /*                                              Métodos privados                                                   */
    /*******************************************************************************************************************/

    /******************************************************************************************/
    /**
     * Obtiene una instancia persistente de finfo configurada con FILEINFO_MIME_TYPE.
     * Implementa "Lazy Loading" mediante el operador ??=.
     *
     * @return \finfo Instancia de finfo reutilizable.
     */
    private function finfo(): \finfo {
        return $this->finfo ??= new \finfo(FILEINFO_MIME_TYPE);
    }
}
