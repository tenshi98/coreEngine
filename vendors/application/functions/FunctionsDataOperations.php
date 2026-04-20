<?php

/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsDataOperations {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                 Instancias                                                      */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	//Definiciones
	private $DataValidations;
	private $Convertions;

	/************************************************************************************************************/
	//Instancias
	public function __construct() {
		$this->DataValidations = new FunctionsDataValidations();
		$this->Convertions     = new FunctionsConvertions();
	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	/**
	 * Realiza la división de una hora específica por un divisor entero.
	 * Convierte la entrada a minutos antes de operar.
	 *
	 * @param string $hora Hora en formato 'HH:MM:SS'.
	 * @param int $divisor Valor entero por el cual se dividirá el tiempo.
	 *
	 * @return int|string Resultado de la división en minutos o mensaje de error en validación.
	 *
	 * @example
	 * ```php
	 * $DataOperations->dividirHoras('04:00:00', 4); //Devuelve 60
	 * ```
	 *
	 */
	public function dividirHoras($hora, $divisor): string | int {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateTime($hora, 'hora');
		$dataVal_2 = $this->_validateInteger($divisor, 'divisor');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }
		if ($dataVal_2 !== true) { return $dataVal_2; }

		/********************** Si todo esta ok **********************/
		// Realiza la conversión de la cadena de hora a su equivalente total en minutos
		$minutos = $this->Convertions->horas2minutos($hora);

		/********************** Retorno datos  **********************/
		// Retorna el cociente de los minutos totales entre el divisor proporcionado
		return $minutos / $divisor;
	}

	/************************************************************************************************************/
	/**
	 * Multiplica una hora por un factor entero y retorna el resultado en formato de tiempo.
	 *
	 * @param string $hora Hora en formato 'HH:MM:SS'.
	 * @param int $multiplicador Factor entero para multiplicar el tiempo.
	 *
	 * @return string Resultado formateado como 'HH:MM:SS' o mensaje de error.
	 *
	 * @example
	 * ```php
	 * $DataOperations->multiplicarHoras('04:00:00', 4); //Devuelve 16:00:00
	 * ```
	 *
	 */
	public function multiplicarHoras($hora, $multiplicador): string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateTime($hora, 'hora');
		$dataVal_2 = $this->_validateInteger($multiplicador, 'multiplicador');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }
		if ($dataVal_2 !== true) { return $dataVal_2; }

		/********************** Si todo esta ok **********************/
		// Convierte la hora al total de segundos.
		// strtotime("1970-01-01 $hora UTC") sigue siendo la forma más sencilla y robusta
		// para convertir 'HH:MM:SS' a segundos desde cero.
		$total_segundos = strtotime("1970-01-01 $hora UTC");

		// Multiplicar y asegurar que el resultado sea un entero (la parte que realmente importa).
		$segundos_multiplicados = (int) round($total_segundos * $multiplicador);

		// Calcular la parte de segundos, minutos y horas usando matemática simple.
		// Obtiene el residuo para los segundos restantes
		$segundos        = $segundos_multiplicados % 60;
		// Calcula el total de minutos acumulados
		$minutos_totales = floor($segundos_multiplicados / 60);
		// Obtiene el residuo para los minutos dentro de la hora
		$minutos         = $minutos_totales % 60;
		// Calcula el total de horas resultantes
		$horas           = floor($minutos_totales / 60);

		/********************** Retorno datos  **********************/
		// Formatea los componentes calculados en una cadena con ceros a la izquierda
		return sprintf("%02d:%02d:%02d", $horas, $minutos, $segundos);
	}

	/************************************************************************************************************/
	/**
	 * Calcula la diferencia entre dos horas dadas.
	 * Si la hora base es mayor que la hora a restar, suma un ciclo de 24 horas para el cálculo.
	 *
	 * @param string $hora Hora inicial.
	 * @param string $horaResta Hora a sustraer.
	 *
	 * @return string Diferencia en formato 'HH:MM:SS' o mensaje de error.
	 *
	 * @example
	 * ```php
	 * $DataOperations->restarhoras('14:00:00', '07:00:00'); //Devuelve 07:00:00
	 * ```
	 *
	 */
	public function restarhoras($hora, $horaResta): string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateTime($hora, 'hora');
		$dataVal_2 = $this->_validateTime($horaResta, 'horaResta');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }
		if ($dataVal_2 !== true) { return $dataVal_2; }

		/********************** Si todo esta ok **********************/
		// Compara los timestamps; si el minuendo es mayor que el sustraendo, se ajusta sumando 24 horas
		if (strtotime($hora) > strtotime($horaResta)) {
			$horaResta  = $this->sumarhoras($horaResta, '24:00:00');
		}

		// Descompone las cadenas de tiempo en arreglos de horas, minutos y segundos
		$hora      = explode(":", $hora);
		$horaResta = explode(":", $horaResta);

		// Asignación de componentes para la primera hora
		$horai = $hora[0];
		$mini  = $hora[1];
		$segi  = $hora[2];

		// Asignación de componentes para la segunda hora
		$horaf = $horaResta[0];
		$minf  = $horaResta[1];
		$segf  = $horaResta[2];

		// Conversión manual de cada componente de tiempo a segundos totales para la operación aritmética
		$ini   = ((($horai * 60) * 60) + ($mini * 60) + $segi);
		$fin   = ((($horaf * 60) * 60) + ($minf * 60) + $segf);

		// Cálculo de la diferencia absoluta en segundos
		$dif   = $fin - $ini;

		/********************** Retorno datos  **********************/
		// Utiliza el servicio de conversiones para retornar el formato de hora desde los segundos calculados
		return $this->Convertions->segundos2horas($dif);
	}

	/************************************************************************************************************/
	/**
	 * Suma dos valores de tiempo y retorna el acumulado.
	 *
	 * @param string $hora Primera hora en formato 'HH:MM:SS'.
	 * @param string $horaSuma Segunda hora a añadir.
	 *
	 * @return string Suma total en formato 'HH:MM:SS' o mensaje de error.
	 *
	 * @example
	 * ```php
	 * $DataOperations->sumarhoras('14:00:00', '07:00:00'); //Devuelve 21:00:00
	 * ```
	 *
	 */
	public function sumarhoras($hora, $horaSuma): string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateTime($hora, 'hora');
		$dataVal_2 = $this->_validateTime($horaSuma, 'horaSuma');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }
		if ($dataVal_2 !== true) { return $dataVal_2; }

		/********************** Si todo esta ok **********************/
		// Divide las cadenas de texto basándose en el delimitador de dos puntos
		$hora     = explode(":", $hora);
		$horaSuma = explode(":", $horaSuma);

		// Extracción de componentes H:M:S del primer operando
		$horai = $hora[0];
		$mini  = $hora[1];
		$segi  = $hora[2];

		// Extracción de componentes H:M:S del segundo operando
		$horaf = $horaSuma[0];
		$minf  = $horaSuma[1];
		$segf  = $horaSuma[2];

		// Cálculo del total de segundos para ambos operandos
		$ini   = ((($horai * 60) * 60) + ($mini * 60) + $segi);
		$fin   = ((($horaf * 60) * 60) + ($minf * 60) + $segf);

		// Suma aritmética de los segundos totales
		$dif   = $fin + $ini;

		/********************** Retorno datos  **********************/
		// Retorna la representación en cadena de los segundos sumados
		return $this->Convertions->segundos2horas($dif);
	}

	/************************************************************************************************************/
	/**
	 * Adiciona un número determinado de días a una fecha específica.
	 *
	 * @param string $Fecha Fecha base en formato 'YYYY-MM-DD'.
	 * @param int $nDias Cantidad de días a sumar.
	 *
	 * @return string Nueva fecha resultante o mensaje de error.
	 *
	 * @example
	 * ```php
	 * $DataOperations->sumarDias('2019-01-02', 5); //Devuelve 2019-01-07
	 * ```
	 *
	 */
	public function sumarDias($Fecha, $nDias): string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateDate($Fecha, 'Fecha');
		$dataVal_2 = $this->_validateInteger($nDias, 'nDias');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }
		if ($dataVal_2 !== true) { return $dataVal_2; }

		/********************** Si todo esta ok **********************/
		/********************** Retorno datos  **********************/
		// Utiliza funciones nativas de PHP para manipular la fecha añadiendo el intervalo de días
		return date('Y-m-d', strtotime('+' . $nDias . ' day', strtotime($Fecha)));
	}

	/************************************************************************************************************/
	/**
	 * Sustrae un número determinado de días de una fecha específica.
	 *
	 * @param string $Fecha Fecha base en formato 'YYYY-MM-DD'.
	 * @param int $nDias Cantidad de días a restar.
	 *
	 * @return string Nueva fecha resultante o mensaje de error.
	 *
	 * @example
	 * ```php
	 * $DataOperations->restarDias('2019-01-07', 5); //Devuelve 2019-01-02
	 * ```
	 *
	 */
	public function restarDias($Fecha, $nDias): string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateDate($Fecha, 'Fecha');
		$dataVal_2 = $this->_validateInteger($nDias, 'nDias');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }
		if ($dataVal_2 !== true) { return $dataVal_2; }

		/********************** Si todo esta ok **********************/
		/********************** Retorno datos  **********************/
		// Calcula la fecha previa restando el intervalo de días especificado
		return date('Y-m-d', strtotime('-' . $nDias . ' day', strtotime($Fecha)));
	}

	/************************************************************************************************************/
	/**
	 * Calcula la edad en años y meses basándose en una fecha de nacimiento y la fecha actual.
	 *
	 * @param string $fNacimiento Fecha de origen en formato 'YYYY-MM-DD'.
	 *
	 * @return string Representación textual de la edad (ej: "2 años, 5 meses") o mensaje de error.
	 *
	 * @example
	 * ```php
	 * $DataOperations->obtenerEdad('2022-01-01'); //Devuelve 'dos años, 5 meses' (a la fecha '2024-06-01')
	 * ```
	 *
	 */
	public function obtenerEdad($fNacimiento): string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateDate($fNacimiento, 'fNacimiento');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

		/********************** Si todo esta ok **********************/
		// Instancia de objetos DateTime para realizar la comparación cronológica
		$nacimiento = new DateTime($fNacimiento);
		$ahora      = new DateTime(date("Y-m-d"));
		// Genera un objeto DateInterval con la diferencia entre fechas
		$diferencia = $ahora->diff($nacimiento);

		/********************** Retorno datos  **********************/
		// Formatea la salida extrayendo los años (%y) y los meses (%m) del intervalo
		return $diferencia->format("%y") . ' años, ' . $diferencia->format("%m") . ' meses';
	}

	/************************************************************************************************************/
	/**
	 * Obtiene únicamente el número de años transcurridos desde una fecha hasta la actualidad.
	 *
	 * @param string $fNacimiento Fecha de origen en formato 'YYYY-MM-DD'.
	 *
	 * @return string Número de años transcurridos como cadena o mensaje de error.
	 *
	 * @example
	 * ```php
	 * $DataOperations->obtenerNumeroAnos('2022-01-01'); //Devuelve '2' (a la fecha '2024-06-01')
	 * ```
	 *
	 */
	public function obtenerNumeroAnos($fNacimiento): string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateDate($fNacimiento, 'fNacimiento');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

		/********************** Si todo esta ok **********************/
		// Creación de instancias cronológicas
		$nacimiento = new DateTime($fNacimiento);
		$ahora      = new DateTime(date("Y-m-d"));
		$diferencia = $ahora->diff($nacimiento);

		/********************** Retorno datos  **********************/
		// Retorna exclusivamente el valor numérico de los años
		return $diferencia->format("%y");
	}

	/************************************************************************************************************/
	/**
	 * Calcula la cantidad de días transcurridos entre dos fechas.
	 *
	 * ✔ Utiliza DateTime y DateInterval (más preciso que strtotime)
	 * ✔ Soporta fechas con o sin hora (Y-m-d / Y-m-d H:i:s)
	 * ✔ Retorna siempre un número entero positivo de días
	 * ✔ Maneja validaciones previas mediante método interno _validateDate
	 * ✔ Evita problemas con zonas horarias, DST y cálculos manuales
	 *
	 * @param string $fechaInicio  Fecha inicial (ej: '2023-12-01' o '2023-12-01 10:00:00')
	 * @param string $fechaTermino Fecha final   (ej: '2023-12-12' o '2023-12-12 15:30:00')
	 *
	 * @return int|string
	 *         - int: cantidad de días transcurridos (valor absoluto)
	 *         - string: mensaje de error si la validación falla
	 *
	 * @example
	 * ```php
	 * $DataOperations->diasTranscurridos('2019-01-02', '2019-02-02'); //Devuelve 31
	 * ```
	 *
	 */
	public function diasTranscurridos($fechaInicio, $fechaTermino): string | int {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateDate($fechaInicio, 'fechaInicio');
		$dataVal_2 = $this->_validateDate($fechaTermino, 'fechaTermino');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }
		if ($dataVal_2 !== true) { return $dataVal_2; }

		/********************** Si todo esta ok **********************/
		// Se crean objetos DateTime a partir de las fechas
		// DateTime maneja internamente formatos, zonas horarias y conversiones
		$d1 = new DateTime($fechaInicio);
		$d2 = new DateTime($fechaTermino);

		// Se calcula la diferencia entre fechas usando diff()
		// Esto retorna un objeto DateInterval
		$diff = $d1->diff($d2);

		/********************** Retorno datos  **********************/
		// ->days entrega la diferencia total en días (siempre positivo)
		// Se castea explícitamente a int para asegurar tipo estricto
		return (int) $diff->days;
	}

	/************************************************************************************************************/
	/**
	 * Calcula el total de horas acumuladas entre dos puntos temporales considerando fechas y horas.
	 *
	 * @param string $fechaInicio Fecha de inicio 'YYYY-MM-DD'.
	 * @param string $fechaTermino Fecha de término 'YYYY-MM-DD'.
	 * @param string $horaInicio Hora de inicio 'HH:MM:SS'.
	 * @param string $horaTermino Hora de término 'HH:MM:SS'.
	 *
	 * @return string Total de horas en formato 'HH:MM:SS' o mensaje de error.
	 *
	 * @example
	 * ```php
	 * $DataOperations->horasTranscurridas('2019-01-02', '2019-02-02', '14:00:00', '07:00:00'); //Devuelve 737:00:00
	 * ```
	 *
	 */
	public function horasTranscurridas($fechaInicio, $fechaTermino, $horaInicio, $horaTermino): string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateDate($fechaInicio, 'fechaInicio');
		$dataVal_2 = $this->_validateDate($fechaTermino, 'fechaTermino');
		$dataVal_3 = $this->_validateTime($horaInicio, 'horaInicio');
		$dataVal_4 = $this->_validateTime($horaTermino, 'horaTermino');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }
		if ($dataVal_2 !== true) { return $dataVal_2; }
		if ($dataVal_3 !== true) { return $dataVal_3; }
		if ($dataVal_4 !== true) { return $dataVal_4; }

		/********************** Si todo esta ok **********************/
		// Determina la diferencia base en días entre ambas fechas
		$n_dias     = $this->diasTranscurridos($fechaInicio, $fechaTermino);
		// Calcula la diferencia horaria entre las horas proporcionadas
		$HorasTrans = $this->restarhoras($horaInicio, $horaTermino);

		// Lógica de acumulación de horas basada en el salto de días
		if ($n_dias != 0) {
			// Caso para periodos de 2 o más días
			if ($n_dias >= 2) {
				$n_dias_temp  = $n_dias - 1;
				// Multiplica los días transcurridos por 24 horas
				$horas_trans  = $this->multiplicarHoras('24:00:00', $n_dias_temp);
				// Suma el acumulado de días al tiempo parcial
				$HorasTrans   = $this->sumarhoras($HorasTrans, $horas_trans);
			}
			// Ajuste específico para transiciones de un día dependiendo de la cronología horaria
			if ($n_dias == 1 && $horaInicio < $horaTermino) {
				$horas_trans  = $this->multiplicarHoras('24:00:00', $n_dias);
				$HorasTrans   = $this->sumarhoras($HorasTrans, $horas_trans);
			}
		}

		/********************** Retorno datos  **********************/
		// Retorno del valor final acumulado en formato de cadena horaria
		return $HorasTrans;
	}

	/************************************************************************************************************/
	/**
	 * Calcula la diferencia total de meses entre dos fechas, incluyendo el excedente de años.
	 *
	 * @param string $fechaInicio Fecha inicial.
	 * @param string $fechaTermino Fecha final.
	 *
	 * @return string|int Total de meses transcurridos o mensaje de error.
	 *
	 * @example
	 * ```php
	 * $DataOperations->diferenciaMeses('2019-01-02', '2019-02-02'); //Devuelve 1
	 * ```
	 *
	 */
	public function diferenciaMeses($fechaInicio, $fechaTermino): string | int {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateDate($fechaInicio, 'fechaInicio');
		$dataVal_2 = $this->_validateDate($fechaTermino, 'fechaTermino');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }
		if ($dataVal_2 !== true) { return $dataVal_2; }

		/********************** Si todo esta ok **********************/
		// Inicialización de objetos de comparación
		$datetime1 = new DateTime($fechaInicio);
		$datetime2 = new DateTime($fechaTermino);

		// Obtención del intervalo diferencial
		$interval      = $datetime2->diff($datetime1);
		// Extracción de la porción de meses restante
		$intervalMeses = $interval->format("%m");
		// Extracción de años convertidos a base mensual (12 meses por año)
		$intervalAnos  = $interval->format("%y") * 12;

		/********************** Retorno datos  **********************/
		// Suma aritmética de meses y años convertidos para obtener el total absoluto
		return $intervalMeses + $intervalAnos;
	}


	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Internos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
    /************************************************************************************************************/
	private function _validateDate($Data, $Name){

		/**********************  Validaciones   **********************/
        // Retorno inmediato si el valor es nulo, cadena vacía o numéricamente cero
        if ($Data=='' || $Data==0) {return 'Sin datos ingresados en '.$Name;}
        // Validación de tipos de datos mediante el componente externo DataValidations
        if (!$this->DataValidations->validarFecha($Data)) {
			return 'El dato ingresado en '.$Name.' no es una fecha (' . $Data . ')';
		}

		/**********************  Retorno datos  **********************/
		return true;

	}
    /************************************************************************************************************/
	private function _validateTime($Data, $Name){

		/**********************  Validaciones   **********************/
        // Retorno inmediato si el valor es nulo, cadena vacía o numéricamente cero
        if ($Data=='' || $Data==0) {return 'Sin datos ingresados en '.$Name;}
        // Validación de tipos de datos mediante el componente externo DataValidations
        if (!$this->DataValidations->validarHora($Data)) {
			return 'El dato ingresado en '.$Name.' no es una hora (' . $Data . ')';
		}

		/**********************  Retorno datos  **********************/
		return true;

	}
    /************************************************************************************************************/
	private function _validateInteger($Data, $Name){

		/**********************  Validaciones   **********************/
        // Retorno inmediato si el valor es nulo, cadena vacía o numéricamente cero
        if ($Data=='' || $Data==0) {return 'Sin datos ingresados en '.$Name;}
        // Validación de tipos de datos mediante el componente externo DataValidations
        if (!$this->DataValidations->validarNumero($Data) || !$this->DataValidations->validarEntero($Data)) {
            return 'El dato ingresado en '.$Name.' no es un numero ('.$Data.')';
        }

		/**********************  Retorno datos  **********************/
		return true;

	}


}
