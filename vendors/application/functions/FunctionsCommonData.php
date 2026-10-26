<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsCommonData {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Publicos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
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
	 *
	 * @example
	 * ```php
	 * $listado = [
	 *     ['estado' => 'Activo',    'area' => 'Ventas'],
	 *     ['estado' => 'Pendiente', 'area' => 'Ventas'],
	 *     ['estado' => '',          'area' => 'Soporte'],
	 * ];
	 * $CommonData->agruparYContar($listado, ['estado']);
	 * // ['estado' => [['nombre' => 'Activo', 'cantidad' => 1],
	 * //               ['nombre' => 'Pendiente', 'cantidad' => 1],
	 * //               ['nombre' => 'Sin información', 'cantidad' => 1]],
	 * //  'totalElementos' => 3]
	 * ```
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

		/**********************  Retorno datos  **********************/
		// Retorno del arreglo procesado
		return $estadisticas;

	}

	/******************************************************************************/
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
	 * // Devuelve ['nombre' => 'Ana', 'direccion' => ['calle' => 'Av. Central', 'ciudad' => 'Madrid']]
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
	 * Invierte el orden de los elementos de un arreglo conservando sus claves.
	 *
	 * Devuelve una copia del arreglo con los elementos en orden inverso, sin
	 * reindexar las claves numéricas originales.
	 *
	 * @param array $Arr Arreglo de entrada que se desea invertir.
	 *
	 * @return array Arreglo con los elementos en orden inverso y las mismas claves.
	 *
	 * @example
	 * ```php
	 * $CommonData->invertirArreglo(['a' => 1, 'b' => 2, 'c' => 3]);
	 * // Devuelve ['c' => 3, 'b' => 2, 'a' => 1]
	 * ```
	 *
	 */
	public function invertirArreglo(array $Arr): array {

		/********************** Si todo esta ok **********************/
		// Invierte el orden de los elementos conservando las claves originales (no reindexa)
		return array_reverse($Arr, true);

	}

	/******************************************************************************/
	/**
	 * Aplana de forma recursiva los arreglos anidados a un solo nivel de profundidad.
	 *
	 * Las claves enteras de los arreglos anidados se fusionan en orden (se anulan)
	 * y las claves string se conservan; en caso de colisión, sobrescribe la interna.
	 *
	 * @param array $Arr Arreglo con posibles anidamientos que se desea aplanar.
	 *
	 * @return array Arreglo plano resultante.
	 *
	 * @example
	 * ```php
	 * $CommonData->aplanarArreglo([1, [2, 3], ['a' => 4]]);
	 * // Devuelve [1, 2, 3, 'a' => 4]
	 * ```
	 *
	 */
	public function aplanarArreglo(array $Arr): array {

		/********************** Si todo esta ok **********************/
		// Arreglo destino que irá recibiendo los valores aplanados
		$Resultado = [];

		// Recorre cada elemento del arreglo de entrada
		foreach ($Arr as $Key => $Value) {
			// Si el valor es un arreglo, se aplanan sus elementos de forma recursiva
			if (is_array($Value)) {
				$Plano = $this->aplanarArreglo($Value);
				// Se fusionan los elementos aplanados según el tipo de clave
				foreach ($Plano as $KeyInt => $ValueInt) {
					// Las claves enteras se anulan para mantener la secuencia lineal
					if (is_int($KeyInt)) {
						$Resultado[] = $ValueInt;
					} else {
						// Las claves string se conservan (las internas sobrescriben si colisionan)
						$Resultado[$KeyInt] = $ValueInt;
					}
				}
			} else {
				// Los elementos escalares conservan su clave original
				$Resultado[$Key] = $Value;
			}
		}

		/**********************  Retorno datos  **********************/
		return $Resultado;

	}

	/******************************************************************************/
	/**
	 * Mezcla dos arreglos de forma recursiva (merge profundo).
	 *
	 * Los arreglos anidados se combinan por clave de forma recursiva y los
	 * escalares de $Extra sobrescriben los de $Base; las claves nuevas se agregan.
	 *
	 * @param array $Base Arreglo base que será extendido.
	 * @param array $Extra Arreglo cuyos valores tienen prioridad sobre $Base.
	 *
	 * @return array Arreglo combinado resultante.
	 *
	 * @example
	 * ```php
	 * $CommonData->mezclarArreglosProfundo(['a' => 1, 'b' => [2]], ['b' => [3], 'c' => 4]);
	 * // Devuelve ['a' => 1, 'b' => [2, 3], 'c' => 4]
	 * ```
	 *
	 */
	public function mezclarArreglosProfundo(array $Base, array $Extra): array {

		/********************** Si todo esta ok **********************/
		// Recorre las claves del arreglo extra para fusionarlas sobre la base
		foreach ($Extra as $Key => $Value) {
			// Si ambos valores son arreglos, se combinan de forma recursiva
			if (is_array($Value) && isset($Base[$Key]) && is_array($Base[$Key])) {
				$Base[$Key] = $this->mezclarArreglosProfundo($Base[$Key], $Value);
			} else {
				// Los escalares y claves nuevas de $Extra sobrescriben o suman sobre $Base
				$Base[$Key] = $Value;
			}
		}

		/**********************  Retorno datos  **********************/
		return $Base;

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
	 * $CommonData->parseDataCommas('uno,dos,tres'); // Devuelve ['uno', 'dos', 'tres']
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
	 * $CommonData->parseDataSeparator('uno-dos-tres'); // Devuelve ['uno', 'dos', 'tres']
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
	 * $CommonData->parseDataSymbol('uno=dos!=tres'); // Devuelve ['uno', 'dos', 'tres']
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
	 * Convierte una cantidad de bytes a su representación legible con unidad binaria.
	 *
	 * Expresa el valor en B, KB, MB, GB, TB o PB utilizando la escala binaria
	 * (1 KB = 1024 B), redondeando a la cantidad de decimales indicada.
	 *
	 * @param float $Bytes Cantidad de bytes a convertir.
	 * @param int $Decimales Cantidad de decimales a mostrar (entre 0 y 4).
	 *
	 * @return string Cadena legible con la unidad; mensaje de error si los decimales están fuera de rango.
	 *
	 * @example
	 * ```php
	 * $CommonData->bytes2Legible(1536);       // Devuelve "1.50 KB"
	 * $CommonData->bytes2Legible(0);          // Devuelve "0 B"
	 * $CommonData->bytes2Legible(1048576, 0); // Devuelve "1 MB"
	 * ```
	 *
	 */
	public function bytes2Legible(float $Bytes, int $Decimales = 2): string {

		/********************** Validaciones   **********************/
		// Se verifica que la cantidad de decimales esté dentro del rango permitido
		if ($Decimales < 0 || $Decimales > 4) {
			return 'El dato ingresado no es un decimal valido ('.$Decimales.')';
		}

		/********************** Si todo esta ok **********************/
		// Cero bytes se reporta sin decimales por convención legible
		if ($Bytes == 0) {
			return '0 B';
		}

		// Unidades binarias ordenadas de menor a mayor
		$Unidades = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
		$Indice   = 0;
		$Valor    = abs($Bytes);

		// Avanza a la unidad que corresponda mientras el valor sea mayor o igual a 1024
		while ($Valor >= 1024 && $Indice < count($Unidades) - 1) {
			$Valor  = $Valor / 1024;
			$Indice++;
		}

		/**********************  Retorno datos  **********************/
		// Formatea el valor con los decimales solicitados, conserva el signo y concatena la unidad
		$Signo = $Bytes < 0 ? '-' : '';
		return $Signo.number_format($Valor, $Decimales, '.', '').' '.$Unidades[$Indice];

	}


}
