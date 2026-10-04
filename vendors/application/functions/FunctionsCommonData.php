<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsCommonData {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	/**
	 * Agrupa un arreglo bidimensional en un formato multinivel basado en una clave específica.
	 *
	 * Transforma un array plano de elementos en un array asociativo donde las llaves
	 * son los valores de la columna de ordenamiento. La clave utilizada para agrupar
	 * es removida de los elementos internos.
	 *
	 * Guardas aplicadas:
	 * - Si $clave_orden está vacío, retorna un arreglo vacío.
	 * - Las filas que no sean arreglos se omiten.
	 * - Las filas sin la clave, o con valor nulo/vacío, se agrupan bajo "Sin información".
	 * - Los valores de clave no escalares (array/objeto) se omiten.
	 *
	 * @param array $array Arreglo de entrada que se desea reordenar.
	 * @param string $clave_orden Nombre de la columna que actuará como índice de agrupación.
	 *
	 * @return array Arreglo procesado y agrupado por niveles.
	 *
	 * @example
	 * ```php
	 * 	//se filtran los datos
	 * 	$CommonData->agruparPorClave ($arreglo, 'categoria' );
	 * 	//se recorre el nuevo arreglo
	 * 	foreach ($arreglo as $categoria=>$arr1){
	 * 		//imprimimos la categoría
	 * 		echo $categoria;
	 * 		//se recorren los datos dentro de la categoría
	 * 		foreach ($arr1 as $arr2){
	 * 			//imprimimos los datos dentro de la categoría
	 * 		}
	 * 	}
	 * ```
	 *
	 */
	public function agruparPorClave(array $array, string $clave_orden): array {

		/********************** Validaciones   **********************/
		// Guarda: sin clave de agrupación no es posible construir la estructura
		if (trim($clave_orden) === '') {
			return [];
		}

		/**********************  Retorno datos  **********************/
		// Utiliza array_reduce para iterar el arreglo y construir la estructura agrupada
		return array_reduce($array, function ($carry, $item) use ($clave_orden) {
				// Guarda: se omite la fila si no es un arreglo (evita error en el acceso a la clave)
				if (!is_array($item)) {
					return $carry;
				}
				// Guarda: se lee la clave sin emitir warning cuando no existe en la fila
				$clave = array_key_exists($clave_orden, $item) ? $item[$clave_orden] : '';
				// Guarda: los valores nulos o vacíos se agrupan bajo una etiqueta controlada
				if ($clave === null || $clave === '') {
					$clave = 'Sin información';
				}
				// Guarda: solo se permiten claves escalares (evita "Illegal offset type")
				if (!is_scalar($clave)) {
					return $carry;
				}
				// Elimina la clave de orden del elemento original para evitar redundancia
				unset($item[$clave_orden]);
				// Agrega el elemento al grupo correspondiente dentro del acumulador
				$carry[$clave][] = $item;
				return $carry;
			}, []);

	}

	/************************************************************************************************************/
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
	 * $CommonData->obtenerExtensionArchivo('nombre del archivo'); //devuelve la extension
	 * ```
	 *
	 */
	public function obtenerExtensionArchivo(string $nombreArchivo): string {

		/**********************  Retorno datos  **********************/
		// Retorna la extensión analizando la cadena de ruta mediante pathinfo
		return pathinfo($nombreArchivo, PATHINFO_EXTENSION);

	}

	/************************************************************************************************************/
	/**
	 * Convierte un objeto o una estructura jerárquica de objetos en un arreglo asociativo.
	 *
	 * Procesa de forma recursiva cada propiedad del objeto. Si una propiedad es a su vez
	 * un objeto, la función se llama a sí misma para garantizar que toda la estructura
	 * final sea un array.
	 *
	 * @param object $obj El objeto inicial que se desea convertir.
	 *
	 * @return array Arreglo asociativo con los datos del objeto original.
	 *
	 * @example
	 * ```php
	 * 	//se recorre el nuevo arreglo
	 * 	$persona = (object)[
	 *		'nombre' => 'Ana',
	 *		'direccion' => (object)[
	 *			'calle' => 'Av. Central',
	 *			'ciudad' => 'Madrid'
	 *		]
	 *	];
	 *   $CommonData->objectToArrayRecursive ($persona);
	 * ```
	 *
	 */
	public function objectToArrayRecursive(object $obj): array {

		/********************** Si todo esta ok **********************/
		// Realiza el casting inicial del objeto a un arreglo asociativo
		$reaged = (array)$obj;

		/**********************  Retorno datos  **********************/
		// Aplica una función sobre cada campo para detectar objetos anidados
		return array_map(function ($field) {
			// Si el campo es un objeto, inicia la recursión; de lo contrario, retorna el valor
			return is_object($field) ? $this->objectToArrayRecursive($field) : $field;
		}, $reaged);

	}

	/******************************************************************************/
	/**
	 * Segmenta una cadena de caracteres delimitada por comas en un arreglo de elementos.
	 *
	 * Utiliza una expresión regular para dividir la cadena, eliminando espacios en blanco
	 * alrededor de las comas y omitiendo fragmentos que resulten vacíos.
	 *
	 * @param string $Data Cadena de texto con elementos separados por comas.
	 *
	 * @return array Arreglo que contiene los elementos individuales extraídos.
	 *
	 * @example
	 * ```php
	 * $CommonData->parseDataCommas('uno,dos,tres');
	 * ```
	 *
	 */
	public function parseDataCommas($Data): array {

		/**********************  Retorno datos  **********************/
		// Divide la cadena basándose en comas, permitiendo espacios opcionales (\s*)
		return preg_split('/\s*,\s*/', $Data, -1, PREG_SPLIT_NO_EMPTY);
	}

    /******************************************************************************/
	/**
	 * Segmenta una cadena de caracteres delimitada por guiones medios en un arreglo.
	 *
	 * Utiliza una expresión regular para dividir la cadena utilizando el carácter '-'
	 * como delimitador, eliminando espacios en blanco adyacentes y descartando
	 * resultados vacíos.
	 *
	 * @param string $Data Cadena de texto que contiene los elementos separados por guiones.
	 *
	 * @return array Arreglo con los elementos individuales extraídos.
	 *
	 * @example
	 * ```php
	 * $CommonData->parseDataSeparator('uno-dos-tres');
	 * ```
	 *
	 */
	public function parseDataSeparator($Data): array {

		/**********************  Retorno datos  **********************/
		// Divide la cadena basándose en guiones, permitiendo espacios opcionales (\s*)
		return preg_split('/\s*-\s*/', $Data, -1, PREG_SPLIT_NO_EMPTY);
	}

	/******************************************************************************/
	/**
	 * Divide una cadena de texto utilizando operadores de comparación como delimitadores.
	 *
	 * Emplea una expresión regular para identificar símbolos lógicos (!=, <=, >=, =, <, >)
	 * y separar la cadena en sus componentes, ignorando espacios en blanco alrededor
	 * de dichos símbolos.
	 *
	 * @param string $Data Cadena con datos y operadores de comparación.
	 *
	 * @return array Arreglo con los fragmentos de texto resultantes de la división.
	 *
	 * @example
	 * ```php
	 * $CommonData->parseDataSymbol('uno=dos!=tres');
	 * ```
	 *
	 */
	public function parseDataSymbol($Data): array {

		/********************** Si todo esta ok **********************/
		// Ejecuta la división mediante un grupo de no captura para los operadores lógicos
		$Data = preg_split('/\s*(?:!=|<=|>=|=|<|>)\s*/', $Data, -1, PREG_SPLIT_NO_EMPTY);

		/**********************  Retorno datos  **********************/
		// Retorno del arreglo procesado
		return $Data;
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

	/******************************************************************************/
	/**
	 * Genera una versión más clara de un color hexadecimal aumentando
	 * su componente de luminosidad en el espacio de color HSL.
	 *
	 * La función acepta colores en formato hexadecimal completo (#RRGGBB)
	 * o abreviado (#RGB). El color se convierte de HEX a RGB, posteriormente
	 * a HSL, se incrementa la luminosidad según el factor indicado y finalmente
	 * se convierte nuevamente a RGB y formato hexadecimal.
	 *
	 * @param string $hex    Código de color hexadecimal de entrada.
	 * @param float  $factor Factor de incremento de luminosidad. Por defecto 0.35.
	 *
	 * @return string Código de color hexadecimal correspondiente al color aclarado.
	 */
	public function colorMasClaro(string $hex, float $factor = 0.35): string {
		// Eliminar #
		$hex = ltrim($hex, '#');

		// Soportar formato corto #RGB
		if (strlen($hex) === 3) {
			$hex = $hex[0] . $hex[0]
				. $hex[1] . $hex[1]
				. $hex[2] . $hex[2];
		}

		// Convertir HEX a RGB
		$r = hexdec(substr($hex, 0, 2)) / 255;
		$g = hexdec(substr($hex, 2, 2)) / 255;
		$b = hexdec(substr($hex, 4, 2)) / 255;

		// RGB -> HSL
		$max = max($r, $g, $b);
		$min = min($r, $g, $b);

		$h = 0;
		$s = 0;
		$l = ($max + $min) / 2;

		// Calcular saturación y tono cuando el color no es acromático
		if ($max !== $min) {
			$d = $max - $min;

			$s = ($l > 0.5)
				? $d / (2 - $max - $min)
				: $d / ($max + $min);

			// Determinar el componente dominante para calcular el tono
			switch ($max) {
				case $r: $h = (($g - $b) / $d) + ($g < $b ? 6 : 0); break;
				case $g: $h = (($b - $r) / $d) + 2; break;
				case $b: $h = (($r - $g) / $d) + 4; break;
			}

			$h /= 6;
		}

		// Aumentar luminosidad
		$l = min(1, $l + $factor);

		// HSL -> RGB
		if ($s == 0) {
			$r = $g = $b = $l;
		} else {
			$q = $l < 0.5
				? $l * (1 + $s)
				: $l + $s - ($l * $s);

			$p = 2 * $l - $q;

			// Convierte un componente de tono HSL a su correspondiente valor RGB
			$hue2rgb = function ($p, $q, $t) {
				if ($t < 0) $t += 1;
				if ($t > 1) $t -= 1;
				if ($t < 1 / 6) return $p + ($q - $p) * 6 * $t;
				if ($t < 1 / 2) return $q;
				if ($t < 2 / 3) return $p + ($q - $p) * (2 / 3 - $t) * 6;
				return $p;
			};

			$r = $hue2rgb($p, $q, $h + 1 / 3);
			$g = $hue2rgb($p, $q, $h);
			$b = $hue2rgb($p, $q, $h - 1 / 3);
		}

		// Convertir los componentes RGB normalizados nuevamente a hexadecimal
		return sprintf(
			'#%02X%02X%02X',
			round($r * 255),
			round($g * 255),
			round($b * 255)
		);
	}

	/******************************************************************************/
	/**
	 * Agrupa y contabiliza los elementos de un listado según los campos indicados.
	 *
	 * Para cada campo recibido, recorre el listado y contabiliza la cantidad
	 * de registros que poseen cada valor. Los valores inexistentes o vacíos
	 * son agrupados bajo la etiqueta "Sin información".
	 *
	 * El resultado de cada campo se transforma al formato nombre/cantidad.
	 * Finalmente, se incorpora la cantidad total de elementos procesados.
	 *
	 * @param array $arrList       Listado de elementos que serán agrupados y contabilizados.
	 * @param array $dataRequired  Lista de campos utilizados para realizar las agrupaciones.
	 *
	 * @return array Arreglo con las estadísticas agrupadas para cada campo y la
	 *               cantidad total de elementos procesados en "totalElementos".
	 */
	public function agruparYContar(array $arrList, array $dataRequired): array {
		$estadisticas = [];

		// Procesa cada campo solicitado para generar sus estadísticas correspondientes
		foreach ($dataRequired as $campo) {
			$estadisticas[$campo] = [];

			// Recorre los elementos y acumula la cantidad correspondiente a cada valor
			foreach ($arrList as $solicitud) {

				// Obtiene el valor del campo o utiliza un valor predeterminado si no existe
				$valor = $solicitud[$campo] ?? 'Sin información';

				// Considera los valores vacíos como información no disponible
				if ($valor === '') {
					$valor = 'Sin información';
				}

				// Inicializa el contador cuando el valor aún no ha sido registrado
				if (!isset($estadisticas[$campo][$valor])) {
					$estadisticas[$campo][$valor] = 0;
				}

				$estadisticas[$campo][$valor]++;
			}

			// Convertir a formato nombre/cantidad
			$estadisticas[$campo] = array_map(
				fn($nombre, $cantidad) => [
					'nombre'   => $nombre,
					'cantidad' => $cantidad
				],
				array_keys($estadisticas[$campo]),
				array_values($estadisticas[$campo])
			);
		}

		// Registra la cantidad total de elementos procesados
		$estadisticas['totalElementos'] = count($arrList);

		return $estadisticas;
	}

}
