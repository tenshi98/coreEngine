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
	public function agruparPorClave (array $array, string $clave_orden): array {
		/*
		*=================================================     Detalles    =================================================
		*
		* Al ingresar un Array, se reordena un array normal transformandolo en un array multinivel
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se filtran los datos
		* 	$CommonData->agruparPorClave ($arreglo, 'categoria' );
		* 	//se recorre el nuevo arreglo
		* 	foreach ($arreglo AS $categoria=>$arr1){
		* 		//imprimimos la categoría
		* 		echo $categoria;
		* 		//se recorren los datos dentro de la categoría
		* 		foreach ($arr1 AS $arr2){
		* 			//imprimimos los datos dentro de la categoría
		* 		}
		* 	}
		*
		*=================================================    Parametros   =================================================
		* @input   array    $array        Arreglo a reordenar
		* @input   string   $clave_orden  Columna desde donde se reordenara el arreglo
		* @return  array
		*===================================================================================================================
		*/

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		//Devolvemos el nuevo array
		return array_reduce($array, function ($carry, $item) use ($clave_orden) {
                $clave = $item[$clave_orden];
                unset($item[$clave_orden]);
                $carry[$clave][] = $item;
                return $carry;
            }, []);


	}

	/************************************************************************************************************/
	public function obtenerExtensionArchivo(string $nombreArchivo): string {
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite obtener la extension del archivo solicitado, hay que tener en cuenta el otorgar la ruta completa de
		* acceso al archivo en el servidor
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//obtener extension
		* 	$CommonData->obtenerExtensionArchivo('nombre del archivo'); //devuelve la extension
		*
		*=================================================    Parametros   =================================================
		* @input   string   $nombreArchivo     Nombre del archivo a revisar, incluyendo la ruta de acceso a este
		* @return  string
		*===================================================================================================================
		*/

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return pathinfo($nombreArchivo, PATHINFO_EXTENSION);

	}

	/************************************************************************************************************/
    public function objectToArrayRecursive (object $obj): array{
		/*
		*=================================================     Detalles    =================================================
		*
		* El objetivo es convertir un objeto (o un árbol de objetos) en un array asociativo, de forma recursiva.
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se recorre el nuevo arreglo
		* 	$persona = (object)[
		*		'nombre' => 'Ana',
		*		'direccion' => (object)[
		*			'calle' => 'Av. Central',
		*			'ciudad' => 'Madrid'
		*		]
		*	];
		*   $CommonData->objectToArrayRecursive ($persona);
		*
		*=================================================    Parametros   =================================================
		* @input   object     $obj   Objeto a convertir
		* @return  array
		*===================================================================================================================
		*/

		/********************** Si todo esta ok **********************/
		// Convertir el objeto recibido en un array asociativo
		$reaged = (array)$obj;

		/**********************  Retorno datos  **********************/
		// Devolver el array completamente convertido
		return array_map(function ($field) {
			return is_object($field) ? $this->objectToArrayRecursive($field) : $field;
		}, $reaged);

    }

	/******************************************************************************/
    public function parseDataCommas($Data): array{
		/*
		*=================================================     Detalles    =================================================
		*
		* Se le entrega una cadena separada por comas y devuelve un array con los elementos ya separados
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se recorre el nuevo arreglo
		* 	$CommonData->parseDataCommas('uno,dos,tres');
		*
		*=================================================    Parametros   =================================================
		* @input   string     $Data   Cadena con los datos
		* @return  array
		*===================================================================================================================
		*/

		/**********************  Retorno datos  **********************/
		return preg_split('/\s*,\s*/', $Data, -1, PREG_SPLIT_NO_EMPTY);

    }

    /******************************************************************************/
    public function parseDataSeparator($Data): array{
		/*
		*=================================================     Detalles    =================================================
		*
		* Se le entrega una cadena separada por guiones medio y devuelve un array con los elementos ya separados
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se recorre el nuevo arreglo
		* 	$CommonData->parseDataSeparator('uno-dos-tres');
		*
		*=================================================    Parametros   =================================================
		* @input   string     $Data   Cadena con los datos
		* @return  array
		*===================================================================================================================
		*/

		/**********************  Retorno datos  **********************/
		return preg_split('/\s*-\s*/', $Data, -1, PREG_SPLIT_NO_EMPTY);

    }

	/******************************************************************************/
    public function parseDataSymbol($Data): array{
		/*
		*=================================================     Detalles    =================================================
		*
		* Se le entrega una cadena separada por !=|<=|>=|=|<|> y devuelve un array con los elementos ya separados
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se recorre el nuevo arreglo
		* 	$CommonData->parseDataSymbol('uno=dos!=tres');
		*
		*=================================================    Parametros   =================================================
		* @input   string     $Data   Cadena con los datos
		* @return  array
		*===================================================================================================================
		*/

		/********************** Si todo esta ok **********************/
		//Valida !=|<=|>=|=|<|> en orden
		$Data = preg_split('/\s*(?:!=|<=|>=|=|<|>)\s*/', $Data, -1, PREG_SPLIT_NO_EMPTY);

		/**********************  Retorno datos  **********************/
		return $Data;

    }


}

