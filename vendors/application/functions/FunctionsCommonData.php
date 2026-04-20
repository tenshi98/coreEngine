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

		/**********************  Retorno datos  **********************/
		// Utiliza array_reduce para iterar el arreglo y construir la estructura agrupada
		return array_reduce($array, function ($carry, $item) use ($clave_orden) {
				// Extrae el valor que servirá como nueva clave de grupo
				$clave = $item[$clave_orden];
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
	 * Resuelve la ruta absoluta del archivo y verifica que el resultado comience
	 * estrictamente con el prefijo de la ruta raíz permitida. Si la ruta es inválida
	 * o se encuentra fuera del rango permitido, retorna la ruta raíz.
	 *
	 * @param string $path Ruta del archivo o directorio a validar.
	 * @param string $root Ruta base permitida que actúa como límite de seguridad.
	 *
	 * @return string La ruta absoluta validada o la ruta raíz en caso de acceso denegado.
	 *
	 * @example
	 * ```php
	 * $root = '/var/www/uploads';
	 * $path = '/var/www/uploads/imagen.jpg';
	 *
	 * echo $this->safePath($path, $root);
	 * // Resultado: /var/www/uploads/imagen.jpg
	 *
	 *
	 * $root = '/var/www/uploads';
	 * $path = '/var/www/uploads/../uploads/documento.pdf';
	 *
	 * echo $this->safePath($path, $root);
	 * // Resultado: /var/www/uploads/documento.pdf
	 *
	 *
	 * $root = '/var/www/uploads';
	 * $path = '/var/www/uploads/../../etc/passwd';
	 *
	 * echo $this->safePath($path, $root);
	 * // Resultado: /var/www/uploads (bloqueado)
	 *
	 *
	 * $root = '/var/www/uploads';
	 * $path = '/var/www/uploads/no_existe.txt';
	 *
	 * echo $this->safePath($path, $root);
	 * // Resultado: /var/www/uploads (fallback por seguridad)
	 *
	 *
	 * $root = '/var/www/uploads';
	 * $path = '/home/user/secret.txt';
	 *
	 * echo $this->safePath($path, $root);
	 * // Resultado: /var/www/uploads (acceso denegado)
	 * ```
	 *
	 */
	public function safePath($path, $root) {

		/********************** Validaciones   **********************/
        // Se verifica si esta vacio
        if(!isset($path) || $path == ''){  return 'Sin datos ingresados'; }
        if(!isset($root) || $root == ''){  return 'Sin datos ingresados'; }

        /********************** Si todo esta ok **********************/
		// Obtiene la ruta absoluta real eliminando enlaces simbólicos y relativos
		$real = realpath($path);

		// Valida si la ruta existe y si se mantiene dentro del directorio raíz
		if ($real === false || strpos($real, rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR) !== 0) {
			// Retorno de seguridad si se detecta una ruta fuera de los límites
			return $root;
		}

		/**********************  Retorno datos  **********************/
		// Retorno de la ruta real confirmada
		return $real;
	}


}

