<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsConvertions {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                 Instancias                                                      */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	//Definiciones
	private $DataValidations;

	/************************************************************************************************************/
	//Instancias
	public function __construct() {
		$this->DataValidations = new FunctionsDataValidations();
	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Funciones  Horas                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	/**
	 * Convierte un valor numérico decimal (representando horas) al formato de tiempo HH:MM:00.
	 *
	 * Esta función toma una cifra decimal donde la parte entera son las horas y la
	 * parte fraccionaria representa la proporción de una hora (minutos).
	 *
	 * @param float $horasDecimales Valor numérico decimal a transformar.
	 *
	 * @return string Tiempo formateado o mensaje de error en caso de validación fallida.
	 *
	 * @example
	 * ```php
	 * $Convertions->numero2horas('');  //Devuelve Sin datos ingresados
	 * $Convertions->numero2horas('a'); //Devuelve El dato ingresado no es un numero
	 * $Convertions->numero2horas(1.5); //Devuelve 01:30:00
	 * ```
	 *
	 */
	public function numero2horas($horasDecimales): string {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateValue($horasDecimales, 'horasDecimales');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

		/********************** Si todo esta ok **********************/
		// Extracción de la parte entera para representar las horas
		$h = intval($horasDecimales);

		// Cálculo de minutos multiplicando la fracción decimal por 60
		$m = ($horasDecimales - $h) * 60;

		// Ajuste de desbordamiento en caso de que los minutos resultantes completen una hora extra
		if ($m == 60){$h++;$m = 0;}

		/**********************  Retorno datos  **********************/
		// Retorno del string con relleno de ceros a la izquierda para horas y minutos
		return sprintf("%02d:%02d:00", $h, $m);

	}

	/************************************************************************************************************/
	/**
	 * Convierte una cantidad total de minutos en un string con formato de tiempo HH:MM:00.
	 *
	 * @param int $nMinutos Cantidad total de minutos a transformar.
	 *
	 * @return string Tiempo formateado o mensaje de error si la validación falla.
	 *
	 * @example
	 * ```php
	 * $Convertions->minutos2horas('');  //Devuelve Sin datos ingresados
	 * $Convertions->minutos2horas('a'); //Devuelve El dato ingresado no es un numero
	 * $Convertions->minutos2horas(65);  //Devuelve 01:05:00
	 * ```
	 *
	 */
	public function minutos2horas($nMinutos): string {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateInteger($nMinutos, 'nMinutos');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

		/********************** Si todo esta ok **********************/
		// Cálculo de horas mediante división entera
		$horas   = floor($nMinutos / 60);

		// Obtención de los minutos restantes mediante el operador de módulo
		$minutos = $nMinutos % 60;

		/**********************  Retorno datos  **********************/
		// Generación de cadena formateada (los segundos se mantienen en cero)
		return sprintf('%02d:%02d:00', $horas, $minutos);

	}

	/************************************************************************************************************/
	/**
	 * Convierte una cantidad total de segundos en un string con formato de tiempo completo HH:MM:SS.
	 *
	 * @param int $nSegundos Cantidad total de segundos a transformar.
	 *
	 * @return string Tiempo formateado (HH:MM:SS) o mensaje de error.
	 *
	 * @example
	 * ```php
	 * $Convertions->segundos2horas('');   //Devuelve Sin datos ingresados
	 * $Convertions->segundos2horas('a');  //Devuelve El dato ingresado no es un numero
	 * $Convertions->segundos2horas(3600); //Devuelve 01:00:00
	 * ```
	 *
	 */
	public function segundos2horas($nSegundos): string {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateInteger($nSegundos, 'nSegundos');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

		/********************** Si todo esta ok **********************/
		// Obtención de horas totales dividiendo por la cantidad de segundos en una hora
		$horas    = floor($nSegundos / 3600);

		// Cálculo de minutos restantes tras extraer las horas
		$minutos  = floor(($nSegundos % 3600) / 60);

		// Obtención del remanente final de segundos
		$segundos = $nSegundos % 60;

		/**********************  Retorno datos  **********************/
		// Retorno de la cadena con formato de dos dígitos para cada segmento temporal
		return sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos);

	}

	/************************************************************************************************************/
	/**
	 * Transforma una cadena de tiempo en formato "H:i:s" a la cantidad total de minutos representados.
	 *
	 * @param string $horas La hora en formato de texto (HH:MM:SS).
	 *
	 * @return string|int Cantidad total de minutos (int) o mensaje de error (string).
	 *
	 * @example
	 * ```php
	 * $Convertions->horas2minutos('');         //Devuelve Sin datos ingresados
	 * $Convertions->horas2minutos('a');        //Devuelve El dato ingresado no es una hora
	 * $Convertions->horas2minutos('01:05:00'); //Devuelve 65
	 * ```
	 *
	 */
	public function horas2minutos($horas): string | int {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateHour($horas, 'horas');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

		/********************** Si todo esta ok **********************/
		// Creación de objeto DateTime a partir del formato específico
		$dateTime = DateTime::createFromFormat('H:i:s', $horas);

		// Control de seguridad en caso de que el parseo de la fecha falle
		if ($dateTime === false) {
			return 'El dato ingresado no es una hora ('.$horas.')';
		}

		/**********************  Retorno datos  **********************/
		// Cálculo: (Horas * 60) + Minutos
		return ($dateTime->format('H') * 60) + $dateTime->format('i');

	}

	/************************************************************************************************************/
	/**
	 * Transforma una cadena de tiempo en formato "H:i:s" a la cantidad total de segundos representados.
	 *
	 * @param string $horas La hora en formato de texto (HH:MM:SS).
	 *
	 * @return string|int Cantidad total de segundos (int) o mensaje de error (string).
	 *
	 * @example
	 * ```php
	 * $Convertions->horas2segundos('');         //Devuelve Sin datos ingresados
	 * $Convertions->horas2segundos('a');        //Devuelve El dato ingresado no es una hora
	 * $Convertions->horas2segundos('00:30:00'); //Devuelve 1800
	 * ```
	 *
	 */
	public function horas2segundos($horas): string | int{

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateHour($horas, 'horas');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

		/********************** Si todo esta ok **********************/
		// Instanciación de DateTime para segmentar la cadena horaria
		$dateTime = DateTime::createFromFormat('H:i:s', $horas);

		// Validación de éxito en la creación del objeto de fecha
		if ($dateTime === false) {
			return 'El dato ingresado no es una hora ('.$horas.')';
		}

		/**********************  Retorno datos  **********************/
		// Sumatoria de componentes: (Horas * 3600) + (Minutos * 60) + Segundos
		return ($dateTime->format('H') * 3600) + ($dateTime->format('i') * 60) + $dateTime->format('s');

	}

	/************************************************************************************************************/
	/**
	 * Transforma una cadena de tiempo en formato "H:i:s" a su representación numérica decimal.
	 *
	 * @param string $horas La hora en formato de texto (HH:MM:SS).
	 *
	 * @return string|float Representación decimal de las horas (float) o mensaje de error (string).
	 *
	 * @example
	 * ```php
	 * $Convertions->horas2decimales('');         //Devuelve Sin datos ingresados
	 * $Convertions->horas2decimales('a');        //Devuelve El dato ingresado no es una hora
	 * $Convertions->horas2decimales('01:30:00'); //Devuelve 1.5
	 * ```
	 *
	 */
	public function horas2decimales($horas): string | float{

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateHour($horas, 'horas');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

		/********************** Si todo esta ok **********************/
		// Generación del objeto DateTime para extracción de partes
		$dateTime = DateTime::createFromFormat('H:i:s', $horas);

		// Manejo de errores de formato no detectados por la validación previa
		if ($dateTime === false) {
			return 'El dato ingresado no es una hora ('.$horas.')';
		}

		/**********************  Retorno datos  **********************/
		// Cálculo decimal: Horas + (Minutos / 60) + (Segundos / 3600)
		return $dateTime->format('H') + ($dateTime->format('i') / 60) + ($dateTime->format('s')/3600);

	}



	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Funciones  Fechas                                                  */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	/**
	 * Convierte la abreviatura de un mes (3 letras) a su nombre completo en español.
	 *
	 * @param string $mes Abreviatura del mes a convertir.
	 *
	 * @return string Nombre completo del mes o mensaje de error si no se encuentra.
	 *
	 * @example
	 * ```php
	 * $Convertions->devolverMes('');    //Devuelve Sin datos ingresados
	 * $Convertions->devolverMes('a');   //Devuelve Dato fuera de parámetros esperados
	 * $Convertions->devolverMes('ene'); //Devuelve Enero
	 * ```
	 *
	 */
	public function devolverMes($mes): string{

		/**********************  Validaciones   **********************/
		// Validación de entrada no vacía
		if ($mes==''){ return 'Sin datos ingresados';}

		/**********************  Definiciones   **********************/
		// Mapeo de abreviaturas en minúsculas a nombres completos
		$meses = [
			'ene' => 'Enero',
			'feb' => 'Febrero',
			'mar' => 'Marzo',
			'abr' => 'Abril',
			'may' => 'Mayo',
			'jun' => 'Junio',
			'jul' => 'Julio',
			'ago' => 'Agosto',
			'sep' => 'Septiembre',
			'oct' => 'Octubre',
			'nov' => 'Noviembre',
			'dic' => 'Diciembre'
		];

		/**********************  Retorno datos  **********************/
		// Normalización de la entrada a minúsculas y verificación de existencia en el arreglo
		return array_key_exists(strtolower($mes), $meses) ? $meses[strtolower($mes)] : 'Dato fuera de parámetros esperados';

	}

	/************************************************************************************************************/
	/**
	 * Traduce un índice numérico (1-12) al nombre completo del mes correspondiente.
	 *
	 * @param int $numero Índice del mes (rango esperado de 1 a 12).
	 *
	 * @return string Nombre del mes o mensaje de error por validación o rango.
	 *
	 * @example
	 * ```php
	 * $Convertions->numero2mes('');  //Devuelve Sin datos ingresados
	 * $Convertions->numero2mes('a'); //Devuelve El dato ingresado no es un numero
	 * $Convertions->numero2mes(25);  //Devuelve Numero fuera de parámetros esperados
	 * $Convertions->numero2mes(1);   //Devuelve Enero
	 * ```
	 *
	 */
	public function numero2mes($numero): string{

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateInteger($numero, 'numero');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }
		// Restricción del rango permitido para meses del año
		if ($numero < 1 || $numero > 12){return 'Numero fuera de parámetros esperados';}

		/**********************  Definiciones   **********************/
		// Listado indexado de meses
		$options = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

		/**********************  Retorno datos  **********************/
		// Retorno del valor ajustando el índice (n-1) para coincidir con el arreglo
		return $options[$numero-1];

	}

	/************************************************************************************************************/
	/**
	 * Traduce un índice numérico (1-12) a la abreviatura de 3 letras del mes.
	 *
	 * @param int $numero Índice del mes (rango esperado de 1 a 12).
	 *
	 * @return string Abreviatura del mes o mensaje de error.
	 *
	 * @example
	 * ```php
	 * $Convertions->numero2mesCorto('');  //Devuelve Sin datos ingresados
	 * $Convertions->numero2mesCorto('a'); //Devuelve El dato ingresado no es un numero
	 * $Convertions->numero2mesCorto(25);  //Devuelve Numero fuera de parámetros esperados
	 * $Convertions->numero2mesCorto(1);   //Devuelve Ene
	 * ```
	 *
	 */
	public function numero2mesCorto($numero): string{

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateInteger($numero, 'numero');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }
		// Validación de límites para meses
		if ($numero < 1 || $numero > 12){return 'Numero fuera de parámetros esperados';}

		/**********************  Definiciones   **********************/
		// Definición de etiquetas cortas para meses
		$options = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

		/**********************  Retorno datos  **********************/
		// Acceso al arreglo mediante desplazamiento de índice
		return $options[$numero-1];

	}

	/************************************************************************************************************/
	/**
	 * Convierte un número de día (1-7) en su nombre correspondiente en español.
	 *
	 * @param int $numero Índice del día (donde 1 representa Lunes y 7 representa Domingo).
	 *
	 * @return string Nombre del día de la semana o mensaje de error.
	 *
	 * @example
	 * ```php
	 * $Convertions->numeroNombreDia('');  //Devuelve Sin datos ingresados
	 * $Convertions->numeroNombreDia('a'); //Devuelve El dato ingresado no es un numero
	 * $Convertions->numeroNombreDia(25);  //Devuelve Numero fuera de parámetros esperados
	 * $Convertions->numeroNombreDia(3);   //Devuelve Miercoles
	 * ```
	 *
	 */
	public function numeroNombreDia($numero): string{

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateInteger($numero, 'numero');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }
		// Control de rango permitido para la semana (nota: el código permite técnicamente hasta el índice 8 antes de fallar)
		if($numero<0 || $numero>8){return 'Numero fuera de parámetros esperados';}

		/**********************  Definiciones   **********************/
		// Definición secuencial de los días de la semana iniciando en Lunes
		$options = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'];

		/**********************  Retorno datos  **********************/
		// Retorno del nombre basado en el índice restado en una unidad
		return $options[$numero-1];

	}


	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Funciones  Valores                                                 */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	/**
	 * Transforma un valor numérico decimal en una representación de cadena con formato de porcentaje.
	 *
	 * La función multiplica el valor de entrada por 100 y aplica un formato de número
	 * sin decimales, utilizando la coma como separador de miles y el punto como separador decimal,
	 * añadiendo finalmente el símbolo de porcentaje.
	 *
	 * @param float $valor El número decimal o entero que se desea convertir.
	 *
	 * @return string El valor formateado como porcentaje o un mensaje de error si la validación falla.
	 *
	 * @example
	 * ```php
	 * $Convertions->porcentaje('');   //Devuelve Sin datos ingresados
	 * $Convertions->porcentaje('a');  //Devuelve El dato ingresado no es un numero
	 * $Convertions->porcentaje(0.65); //Devuelve 65%
	 * ```
	 *
	 */
	public function porcentaje($valor): string{

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateValue($valor, 'valor');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

		/**********************  Retorno datos  **********************/
		// Cálculo del porcentaje y formateo numérico
		// Se multiplica por 100, se definen 0 decimales y se establecen separadores específicos
		return number_format(($valor *100),0,',','.').' %';

	}



	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Funciones  Textos                                                  */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	/**
	 * Convierte un valor numérico a su representación literal en palabras (español).
	 * * La función descompone el número en bloques (miles, millones, mil millones) y utiliza
	 * una lógica recursiva para procesar centenas, decenas y unidades, gestionando
	 * casos especiales como "ciento" vs "cien", "un millón" vs "millones" y números negativos.
	 *
	 * @param int|float $numero El valor numérico a transformar.
	 *
	 * @return string Representación textual del número o mensaje de error.
	 *
	 * @example
	 * ```php
	 * $Convertions->numeroApalabras('');    //Devuelve Sin datos ingresados
	 * $Convertions->numeroApalabras('a');   //Devuelve El dato ingresado no es un numero
	 * $Convertions->numeroApalabras(1200);  //Devuelve mil doscientos
	 * $Convertions->numeroApalabras(-15);   //Devuelve menos quince
	 * ```
	 *
	 */
	public function numeroApalabras($numero): string {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateValue($numero, 'numero');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

		/********************** Si todo esta ok **********************/
		// Gestión del signo: almacenamiento del estado negativo y conversión a valor absoluto para procesamiento
		$es_negativo = $numero < 0;
		$numero_abs = abs($numero);

		// Mapeo de unidades básicas
		$unidades = [
			0 => 'cero', 1 => 'uno', 2 => 'dos', 3 => 'tres', 4 => 'cuatro',
			5 => 'cinco', 6 => 'seis', 7 => 'siete', 8 => 'ocho', 9 => 'nueve'
		];

		// Mapeo de decenas y números especiales (10-15)
		$dieces = [
			10 => 'diez', 11 => 'once', 12 => 'doce', 13 => 'trece', 14 => 'catorce',
			15 => 'quince', 20 => 'veinte', 30 => 'treinta', 40 => 'cuarenta',
			50 => 'cincuenta', 60 => 'sesenta', 70 => 'setenta', 80 => 'ochenta', 90 => 'noventa'
		];

		// Definición de umbrales para grandes magnitudes
		$potencias = [
			1000000000 => 'mil millones', 1000000 => 'millón', 1000 => 'mil'
		];

		// Función interna anónima para procesar bloques de hasta tres dígitos (0-999)
		$convertirCientos = function ($n) use (&$convertirCientos, $unidades, $dieces) {
			// Procesamiento de unidades simples
			if ($n < 10) {
				return $unidades[$n];
			}
			// Procesamiento de números del 10 al 15
			elseif ($n < 16) {
				return $dieces[$n];
			}
			// Procesamiento de la decena del diez (16-19)
			elseif ($n < 20) {
				return 'dieci' . $unidades[$n - 10];
			}
			// Procesamiento de la decena del veinte (20-29)
			elseif ($n < 30) {
				return ($n === 20) ? $dieces[20] : 'veinti' . $unidades[$n - 20];
			}
			// Procesamiento de decenas hasta el 99 con conjunción "y"
			elseif ($n < 100) {
				$decena = (int)(floor($n / 10) * 10);
				$unidad = $n % 10;
				if ($unidad === 0) {
					return $dieces[$decena];
				} else {
					return $dieces[$decena] . ' y ' . $unidades[$unidad];
				}
			}
			// Procesamiento de centenas (100-999)
			elseif ($n < 1000) {
				$cifra = (int)(floor($n / 100));
				$resto = $n % 100;

				$centenas = [
					1 => 'ciento', 2 => 'doscientos', 3 => 'trescientos', 4 => 'cuatrocientos',
					5 => 'quinientos', 6 => 'seiscientos', 7 => 'setecientos', 8 => 'ochocientos',
					9 => 'novecientos'
				];

				// Excepción gramatical para el número 100 exacto
				if ($n === 100) {
					$centena = 'cien';
				} else {
					$centena = $centenas[$cifra] ?? ($unidades[$cifra] . 'cientos');
				}

				// Llamada recursiva para procesar el residuo de la centena
				if ($resto === 0) {
					return $centena;
				} else {
					return $centena . ' ' . $convertirCientos($resto);
				}
			}
			return '';
		};

		// Caso base para el número cero
		if ($numero_abs === 0) {
			return $unidades[0];
		}

		$palabras = [];

		// Descomposición del número iterando por potencias de magnitud descendente
		foreach ($potencias as $valor => $nombre) {
			if ($numero_abs >= $valor) {
				$cociente = floor($numero_abs / $valor);
				$resto = $numero_abs % $valor;

				// Procesamiento del bloque actual (cuántos miles, cuántos millones, etc.)
				$bloque_palabras = $convertirCientos($cociente);

				// Ajuste gramatical para el singular/plural de millones
				if ($nombre === 'millón' && $cociente === 1) {
					$palabras[] = 'un ' . $nombre;
				} else {
					$palabras[] = $bloque_palabras . ' ' . ($cociente > 1 && $nombre === 'millón' ? 'millones' : $nombre);
				}

				// Actualización del valor absoluto con el residuo para la siguiente iteración
				$numero_abs = $resto;
			}
		}

		// Procesamiento de los últimos tres dígitos restantes (unidades, decenas, centenas)
		if ($numero_abs > 0) {
			$palabras[] = $convertirCientos($numero_abs);
		}

		// Consolidación de todos los bloques procesados en una sola cadena
		$resultado = trim(implode(' ', $palabras));

		// Prefijado de la palabra "menos" si el número original era negativo
		if ($es_negativo) {
			$resultado = 'menos ' . $resultado;
		}

		/********************** Retorno datos  **********************/
		return $resultado;
	}


	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Internos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
    /************************************************************************************************************/
	/************************************************************************************************************/
	private function _validateValue($Data, $Name){

		/**********************  Validaciones   **********************/
        // Retorno inmediato si el valor es nulo, cadena vacía o numéricamente cero
        if ($Data=='') {return 'Sin datos ingresados en '.$Name;}
        // Validación de tipos de datos mediante el componente externo DataValidations
        if (!$this->DataValidations->validarNumero($Data)) {
            return 'El dato ingresado en '.$Name.' no es un numero ('.$Data.')';
        }

		/**********************  Retorno datos  **********************/
		return true;

	}
    /************************************************************************************************************/
	private function _validateInteger($Data, $Name){

		/**********************  Validaciones   **********************/
        // Retorno inmediato si el valor es nulo, cadena vacía o numéricamente cero
        if ($Data=='') {return 'Sin datos ingresados en '.$Name;}
        // Validación de tipos de datos mediante el componente externo DataValidations
        if (!$this->DataValidations->validarNumero($Data) || !$this->DataValidations->validarEntero($Data)) {
            return 'El dato ingresado en '.$Name.' no es un numero ('.$Data.')';
        }

		/**********************  Retorno datos  **********************/
		return true;

	}
    /************************************************************************************************************/
	private function _validateHour($Data, $Name){

		/**********************  Validaciones   **********************/
        // Retorno inmediato si el valor es nulo, cadena vacía o numéricamente cero
        if ($Data=='') {return 'Sin datos ingresados en '.$Name;}
        // Validación de tipos de datos mediante el componente externo DataValidations
        if (!$this->DataValidations->validarHora($Data)) {
            return 'El dato ingresado en '.$Name.' no es una hora ('.$Data.')';
        }

		/**********************  Retorno datos  **********************/
		return true;

	}


}


