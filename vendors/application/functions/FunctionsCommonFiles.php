<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsCommonFiles {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Publicos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	/**
     * Verifica la existencia y accesibilidad de un archivo en el sistema de archivos local.
     *
     * @param string $PathFile Ruta absoluta o relativa al archivo.
     *
     * @return array Resultado con claves 'success' y 'message' (más 'path' cuando se evalúa una ruta): ['success' => bool, 'message' => string, 'path' => string].
	 *
	 * @example
	 * ```php
	 * $Files   = new FunctionsCommonFiles();
	 * $Files->validatePathFile('/var/www/html/config.php');
	 * // devuelve ['success' => true, 'message' => 'Archivo válido', 'path' => '/var/www/html/config.php']
	 * // o ['success' => false, 'message' => 'Archivo no encontrado', 'path' => '/var/www/html/config.php']
	 * ```
	 *
     */
    public function validatePathFile($PathFile): array {

        /**********************  Validaciones   **********************/
        // Comprobación de parámetro obligatorio
        if ($PathFile === null || trim((string)$PathFile) === '') {
            return ['success' => false,'message' => 'No hay datos en $PathFile'];
        }

        // Verificación de existencia física en el disco
        if (!file_exists($PathFile)) {
            return [
                'success' => false,
                'message' => 'Archivo no encontrado',
                'path'    => $PathFile
            ];
        }

        // Verificación de permisos de lectura para el proceso actual
        if (!is_readable($PathFile)) {
            return [
                'success' => false,
                'message' => 'Archivo no es legible',
                'path'    => $PathFile
            ];
        }

        /**********************  Retorno datos  **********************/
        return [
            'success' => true,
            'message' => 'Archivo válido',
            'path'    => $PathFile
        ];

    }

	/******************************************************************************/
	/**
	 * Recupera la extensión de un archivo a partir de su nombre o ruta completa.
	 *
	 * Extrae la cadena de caracteres posterior al último punto en la ruta proporcionada
	 * utilizando las funciones nativas del sistema de archivos.
	 *
	 * @param string $nombreArchivo Nombre o ruta completa del archivo en el servidor.
	 *
	 * @return string Extensión del archivo resultante.
	 *
	 * @example
	 * ```php
	 * $Files->obtenerExtensionArchivo('informe.pdf'); // Devuelve 'pdf'
	 * ```
	 *
	 */
	public function obtenerExtensionArchivo(string $nombreArchivo): string {

		/**********************  Retorno datos  **********************/
		// Retorna la extensión analizando la cadena de ruta mediante pathinfo
		return pathinfo($nombreArchivo, PATHINFO_EXTENSION);

	}

	/******************************************************************************/
	/**
	 * Valida y normaliza una ruta de archivo para prevenir ataques de salto de directorio.
	 *
	 * Resuelve la ruta absoluta del archivo y de la raíz con realpath() (eliminando
	 * enlaces simbólicos y segmentos '..') y verifica que el resultado sea igual a la
	 * raíz o que comience estrictamente con ella, de modo que los directorios hermanos
	 * (p. ej. /var/www/uploads_evil) queden bloqueados. Solo se aceptan rutas
	 * existentes: si la ruta no existe, queda fuera de la raíz o los datos de entrada
	 * son inválidos, la respuesta indica el fallo en la clave "error" y, cuando es
	 * posible, la raíz canónica queda en "data" como valor seguro de respaldo.
	 *
	 * @param string $path Ruta del archivo o directorio a validar (debe existir).
	 * @param string $root Ruta base permitida que actúa como límite de seguridad (debe existir).
	 *
	 * @return array Respuesta estructurada con las claves:
	 *               - success (bool): true solo si la ruta existe y está permitida.
	 *               - data (string): la ruta absoluta canónica validada; la raíz
	 *                 canónica como respaldo cuando success=false, o '' si los datos
	 *                 de entrada no permiten calcularla.
	 *               - error (string|null): null cuando success=true; en caso contrario,
	 *                 el motivo del fallo.
	 *
	 * @example
	 * ```php
	 * $root = '/var/www/uploads';
	 * $path = '/var/www/uploads/imagen.jpg';
	 *
	 * $this->safePath($path, $root);
	 * // ['success' => true, 'data' => '/var/www/uploads/imagen.jpg', 'error' => null]
	 *
	 *
	 * $root = '/var/www/uploads';
	 * $path = '/var/www/uploads/../uploads/documento.pdf';
	 *
	 * $this->safePath($path, $root);
	 * // ['success' => true, 'data' => '/var/www/uploads/documento.pdf', 'error' => null]
	 *
	 *
	 * $root = '/var/www/uploads';
	 * $path = '/var/www/uploads/../../etc/passwd';
	 *
	 * $this->safePath($path, $root);
	 * // ['success' => false, 'data' => '/var/www/uploads',
	 * //  'error' => 'Acceso denegado: la ruta está fuera de la raíz permitida']
	 *
	 *
	 * $root = '/var/www/uploads';
	 * $path = '/var/www/uploads/no_existe.txt';
	 *
	 * $this->safePath($path, $root);
	 * // ['success' => false, 'data' => '/var/www/uploads',
	 * //  'error' => 'La ruta no existe o no es accesible']
	 *
	 *
	 * $root = '/var/www/uploads';
	 * $path = '/home/user/secret.txt';
	 *
	 * $this->safePath($path, $root);
	 * // ['success' => false, 'data' => '/var/www/uploads',
	 * //  'error' => 'Acceso denegado: la ruta está fuera de la raíz permitida']
	 * ```
	 *
	 */
	public function safePath($path, $root): array {

		/********************** Validaciones   **********************/
		// Guarda: se descartan entradas nulas, vacías o no escalares (evita warnings de conversión)
		if ($path === null || !is_scalar($path) || trim((string)$path) === '') {
			return ['success' => false, 'data' => '', 'error' => 'Sin datos ingresados'];
		}
		if ($root === null || !is_scalar($root) || trim((string)$root) === '') {
			return ['success' => false, 'data' => '', 'error' => 'Sin datos ingresados'];
		}
		$path = (string)$path;
		$root = (string)$root;

		// Guarda: los bytes nulos provocan ValueError en realpath() desde PHP 8
		if (str_contains($path, "\0") || str_contains($root, "\0")) {
			return ['success' => false, 'data' => '', 'error' => 'La ruta contiene caracteres no permitidos'];
		}

		/********************** Si todo esta ok **********************/
		// Canoniza también la raíz: elimina enlaces simbólicos, segmentos '..' y barra final
		$realRoot = realpath($root);
		if ($realRoot === false) {
			return ['success' => false, 'data' => '', 'error' => 'La ruta raíz no existe o no es accesible'];
		}
		$realRoot = rtrim($realRoot, DIRECTORY_SEPARATOR);

		// Obtiene la ruta absoluta real; solo se aceptan rutas existentes
		$real = realpath($path);
		if ($real === false) {
			// Retorno de seguridad: fallback a la raíz canónica
			return ['success' => false, 'data' => $realRoot, 'error' => 'La ruta no existe o no es accesible'];
		}

		/**********************  Retorno datos  **********************/
		// La raíz exacta o cualquier ruta estrictamente dentro de ella está permitida
		// (el separador posterior evita el bypass de hermanos tipo /var/www/uploads_evil)
		if ($real === $realRoot || str_starts_with($real, $realRoot . DIRECTORY_SEPARATOR)) {
			return ['success' => true, 'data' => $real, 'error' => null];
		}

		// Retorno de seguridad si se detecta una ruta fuera de los límites
		return ['success' => false, 'data' => $realRoot, 'error' => 'Acceso denegado: la ruta está fuera de la raíz permitida'];

	}


}
