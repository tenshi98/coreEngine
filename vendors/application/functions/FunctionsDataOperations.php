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
	/******************************************************************************/
	//Definiciones
	private $DataValidations;
	private $Convertions;

	/******************************************************************************/
	/**
	 * Inicializa la instancia con los componentes de validación y conversión requeridos.
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * $DataOperations = new FunctionsDataOperations();
	 * ```
	 */
	public function __construct() {
		$this->DataValidations = new FunctionsDataValidations();
		$this->Convertions     = new FunctionsConvertions();
	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Publicos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
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
	 * $DataOperations->sumarhoras('14:00:00', '07:00:00'); // Devuelve 21:00:00
	 * ```
	 *
	 */
	public function sumarhoras($hora, $horaSuma): string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateTime($hora, 'hora');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }

		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_2 = $this->_validateTime($horaSuma, 'horaSuma');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
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

	/******************************************************************************/
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
	 * $DataOperations->restarhoras('14:00:00', '07:00:00'); // Devuelve 07:00:00
	 * ```
	 *
	 */
	public function restarhoras($hora, $horaResta): string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateTime($hora, 'hora');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }

		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_2 = $this->_validateTime($horaResta, 'horaResta');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
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

	/******************************************************************************/
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
	 * $DataOperations->multiplicarHoras('04:00:00', 4); // Devuelve 16:00:00
	 * ```
	 *
	 */
	public function multiplicarHoras($hora, $multiplicador): string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateTime($hora, 'hora');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }

		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_2 = $this->_validateInteger($multiplicador, 'multiplicador');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
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

	/******************************************************************************/
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
	 * $DataOperations->dividirHoras('04:00:00', 4); // Devuelve 60
	 * ```
	 *
	 */
	public function dividirHoras($hora, $divisor): string | int {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateTime($hora, 'hora');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }

		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_2 = $this->_validateInteger($divisor, 'divisor');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_2 !== true) { return $dataVal_2; }

		/********************** Si todo esta ok **********************/
		// Realiza la conversión de la cadena de hora a su equivalente total en minutos
		$minutos = $this->Convertions->horas2minutos($hora);

		/********************** Retorno datos  **********************/
		// Retorna el cociente de los minutos totales entre el divisor proporcionado
		return $minutos / $divisor;

	}

	/******************************************************************************/
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
	 * $DataOperations->sumarDias('2019-01-02', 5); // Devuelve 2019-01-07
	 * ```
	 *
	 */
	public function sumarDias($Fecha, $nDias): string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateDate($Fecha, 'Fecha');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }

		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_2 = $this->_validateInteger($nDias, 'nDias');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_2 !== true) { return $dataVal_2; }

		/********************** Si todo esta ok **********************/
		/********************** Retorno datos  **********************/
		// Utiliza funciones nativas de PHP para manipular la fecha añadiendo el intervalo de días
		return date('Y-m-d', strtotime('+' . $nDias . ' day', strtotime($Fecha)));

	}

	/******************************************************************************/
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
	 * $DataOperations->restarDias('2019-01-07', 5); // Devuelve 2019-01-02
	 * ```
	 *
	 */
	public function restarDias($Fecha, $nDias): string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateDate($Fecha, 'Fecha');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }

		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_2 = $this->_validateInteger($nDias, 'nDias');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_2 !== true) { return $dataVal_2; }

		/********************** Si todo esta ok **********************/
		/********************** Retorno datos  **********************/
		// Calcula la fecha previa restando el intervalo de días especificado
		return date('Y-m-d', strtotime('-' . $nDias . ' day', strtotime($Fecha)));

	}

	/******************************************************************************/
	/**
	 * Adiciona un número determinado de meses a una fecha específica.
	 *
	 * Respeta el formato de entrada: acepta fecha simple 'Y-m-d' o fecha/hora
	 * 'Y-m-d H:i:s' y devuelve el resultado en ese mismo formato. Ante el
	 * desborde de días (31/ene + 1 mes) aplica el ajuste de fin de mes de DateTime.
	 *
	 * @param string $Fecha Fecha base ('Y-m-d' o 'Y-m-d H:i:s').
	 * @param int $Meses Cantidad de meses a sumar (no negativa).
	 *
	 * @return string Nueva fecha resultante o mensaje de error.
	 *
	 * @example
	 * ```php
	 * $DataOperations->sumarMeses('2024-01-31', 1); // Devuelve 2024-02-29 (ajuste de fin de mes)
	 * $DataOperations->sumarMeses('2024-01-15', 2); // Devuelve 2024-03-15
	 * $DataOperations->sumarMeses('2024-01-15', -1); // Devuelve El dato ingresado no es un numero de meses valido (-1)
	 * ```
	 *
	 */
	public function sumarMeses($Fecha, int $Meses): string {

		/********************** Validaciones   **********************/
		// Guarda: sin fecha no se puede operar
		if ($Fecha === null || trim((string)$Fecha) === '' || $Fecha == 0) {
			return 'Sin datos ingresados en Fecha';
		}
		// Se detecta y valida el formato de entrada (fecha/hora o fecha simple)
		$Formato = 'Y-m-d H:i:s';
		if (!$this->DataValidations->validarFecha($Fecha, 'Y-m-d H:i:s')) {
			$Formato = 'Y-m-d';
			if (!$this->DataValidations->validarFecha($Fecha, 'Y-m-d')) {
				return 'El dato ingresado en Fecha no es una fecha ('.$Fecha.')';
			}
		}

		// Ejecuta la validación interna del entero a sumar
		$dataVal = $this->_validateInteger($Meses, 'Meses');
		if ($dataVal !== true) { return $dataVal; }
		// Solo se admite suma de meses no negativa (para restar usar restarMeses)
		if ($Meses < 0) {
			return 'El dato ingresado no es un numero de meses valido ('.$Meses.')';
		}

		/********************** Si todo esta ok **********************/
		// DateTime aplica la suma de meses con ajuste automático de fin de mes
		$d = new DateTime($Fecha);
		$d->modify('+' . $Meses . ' months');

		/********************** Retorno datos  **********************/
		// Se devuelve en el mismo formato que se recibió
		return $d->format($Formato);

	}

	/******************************************************************************/
	/**
	 * Sustrae un número determinado de meses de una fecha específica.
	 *
	 * Respeta el formato de entrada: acepta fecha simple 'Y-m-d' o fecha/hora
	 * 'Y-m-d H:i:s' y devuelve el resultado en ese mismo formato. Ante el
	 * desborde de días aplica el ajuste de fin de mes de DateTime.
	 *
	 * @param string $Fecha Fecha base ('Y-m-d' o 'Y-m-d H:i:s').
	 * @param int $Meses Cantidad de meses a restar (no negativa).
	 *
	 * @return string Nueva fecha resultante o mensaje de error.
	 *
	 * @example
	 * ```php
	 * $DataOperations->restarMeses('2024-03-31', 1); // Devuelve 2024-02-29 (ajuste de fin de mes)
	 * $DataOperations->restarMeses('2024-03-15', 2); // Devuelve 2024-01-15
	 * ```
	 *
	 */
	public function restarMeses($Fecha, int $Meses): string {

		/********************** Validaciones   **********************/
		// Guarda: sin fecha no se puede operar
		if ($Fecha === null || trim((string)$Fecha) === '' || $Fecha == 0) {
			return 'Sin datos ingresados en Fecha';
		}
		// Se detecta y valida el formato de entrada (fecha/hora o fecha simple)
		$Formato = 'Y-m-d H:i:s';
		if (!$this->DataValidations->validarFecha($Fecha, 'Y-m-d H:i:s')) {
			$Formato = 'Y-m-d';
			if (!$this->DataValidations->validarFecha($Fecha, 'Y-m-d')) {
				return 'El dato ingresado en Fecha no es una fecha ('.$Fecha.')';
			}
		}

		// Ejecuta la validación interna del entero a restar
		$dataVal = $this->_validateInteger($Meses, 'Meses');
		if ($dataVal !== true) { return $dataVal; }
		// Solo se admite resta de meses no negativa (para sumar usar sumarMeses)
		if ($Meses < 0) {
			return 'El dato ingresado no es un numero de meses valido ('.$Meses.')';
		}

		/********************** Si todo esta ok **********************/
		// DateTime aplica la resta de meses con ajuste automático de fin de mes
		$d = new DateTime($Fecha);
		$d->modify('-' . $Meses . ' months');

		/********************** Retorno datos  **********************/
		// Se devuelve en el mismo formato que se recibió
		return $d->format($Formato);

	}

	/******************************************************************************/
	/**
	 * Determina si un año es bisiesto según el calendario Gregoriano.
	 *
	 * Acepta el año como entero o como cadena numérica de cuatro dígitos.
	 * El calendario Gregoriano es válido desde el año 1583.
	 *
	 * @param mixed $Ano Año a evaluar (ej: 2024 o '2024').
	 *
	 * @return bool|string true si es bisiesto; mensaje de error si el dato no es válido.
	 *
	 * @example
	 * ```php
	 * $DataOperations->esBisiesto(2024); // Devuelve true
	 * $DataOperations->esBisiesto(2023); // Devuelve false
	 * $DataOperations->esBisiesto(1500); // Devuelve El ano ingresado fuera de rango (1500)
	 * ```
	 *
	 */
	public function esBisiesto($Ano): bool | string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna: el dato debe ser numérico no vacío
		$dataVal = $this->_validateInteger($Ano, 'Ano');
		if ($dataVal !== true) { return $dataVal; }
		// Control de vigencia del calendario Gregoriano
		if ((int)$Ano < 1583) {
			return 'El ano ingresado fuera de rango ('.$Ano.')';
		}

		/********************** Si todo esta ok **********************/
		/********************** Retorno datos  **********************/
		// checkdate(29 de febrero) solo es válido en años bisiestos
		return checkdate(2, 29, (int)$Ano);

	}

	/******************************************************************************/
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
	 * $DataOperations->diasTranscurridos('2019-01-02', '2019-02-02'); // Devuelve 31
	 * ```
	 *
	 */
	public function diasTranscurridos($fechaInicio, $fechaTermino): string | int {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateDate($fechaInicio, 'fechaInicio');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }

		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_2 = $this->_validateDate($fechaTermino, 'fechaTermino');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
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

	/******************************************************************************/
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
	 * $DataOperations->diferenciaMeses('2019-01-02', '2019-02-02'); // Devuelve 1
	 * ```
	 *
	 */
	public function diferenciaMeses($fechaInicio, $fechaTermino): string | int {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateDate($fechaInicio, 'fechaInicio');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }

		// Ejecuta la validación interna del formato y consistencia de la fecha recibida

		$dataVal_2 = $this->_validateDate($fechaTermino, 'fechaTermino');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
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

	/******************************************************************************/
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
	 * $DataOperations->horasTranscurridas('2019-01-02', '2019-02-02', '14:00:00', '07:00:00'); // Devuelve 737:00:00
	 * ```
	 *
	 */
	public function horasTranscurridas($fechaInicio, $fechaTermino, $horaInicio, $horaTermino): string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateDate($fechaInicio, 'fechaInicio');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }

		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_2 = $this->_validateDate($fechaTermino, 'fechaTermino');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_2 !== true) { return $dataVal_2; }

		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_3 = $this->_validateTime($horaInicio, 'horaInicio');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_3 !== true) { return $dataVal_3; }

		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_4 = $this->_validateTime($horaTermino, 'horaTermino');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_4 !== true) { return $dataVal_4; }

		/********************** Si todo esta ok **********************/
		// Calculo unificado bajo DateTime/DateInterval: se combinan fecha+hora en dos
		// puntos temporales y se mide la diferencia absoluta en segundos. Conserva el
		// contrato previo (retorna magnitud en formato HH:MM:SS, admite mas de 24h).
		$inicio = new DateTime(trim($fechaInicio) . ' ' . trim($horaInicio));
		$termino = new DateTime(trim($fechaTermino) . ' ' . trim($horaTermino));
		$difSegundos = abs($termino->getTimestamp() - $inicio->getTimestamp());
		$horas = floor($difSegundos / 3600);
		$minutos = floor(($difSegundos % 3600) / 60);
		$segundos = $difSegundos % 60;
		$HorasTrans = sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos);

		/********************** Retorno datos  **********************/
		// Retorno del valor final acumulado en formato de cadena horaria
		return $HorasTrans;

	}

	/******************************************************************************/
	/**
	 * Calcula la cantidad de días hábiles (lunes a viernes) entre dos fechas.
	 *
	 * Ambas fechas se consideran incluidas en el recuento cuando caen en día
	 * hábil. No considera feriados ni festivos (no existe calendario de festivos
	 * en la plataforma).
	 *
	 * @param string $FechaInicio Fecha inicial en formato 'Y-m-d'.
	 * @param string $FechaFin Fecha final en formato 'Y-m-d'.
	 *
	 * @return int|string Cantidad de días hábiles; mensaje de error si el rango no es válido.
	 *
	 * @example
	 * ```php
	 * $DataOperations->diasHabiles('2026-10-12', '2026-10-16'); // Devuelve 5 (lunes a viernes)
	 * $DataOperations->diasHabiles('2026-10-17', '2026-10-18'); // Devuelve 0 (sábado y domingo)
	 * $DataOperations->diasHabiles('2026-10-16', '2026-10-12'); // Devuelve La fecha de inicio no puede ser posterior a la fecha de termino
	 * ```
	 *
	 */
	public function diasHabiles($FechaInicio, $FechaFin): int | string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateDate($FechaInicio, 'FechaInicio');
		if ($dataVal_1 !== true) { return $dataVal_1; }

		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_2 = $this->_validateDate($FechaFin, 'FechaFin');
		if ($dataVal_2 !== true) { return $dataVal_2; }

		// El inicio no puede ser posterior al fin
		$Inicio = strtotime($FechaInicio);
		$Fin    = strtotime($FechaFin);
		if ($Inicio > $Fin) {
			return 'La fecha de inicio no puede ser posterior a la fecha de termino';
		}

		/********************** Si todo esta ok **********************/
		// Recorrido día a día contabilizando solo lunes (1) a viernes (5)
		$Contador = 0;
		for ($ts = $Inicio; $ts <= $Fin; $ts = strtotime('+1 day', $ts)) {
			$DiaSemana = (int)date('w', $ts);
			if ($DiaSemana >= 1 && $DiaSemana <= 5) {
				$Contador++;
			}
		}

		/********************** Retorno datos  **********************/
		return $Contador;

	}

	/******************************************************************************/
	/**
	 * Calcula la edad en años y meses basándose en una fecha de nacimiento y la fecha actual.
	 *
	 * @param string $fNacimiento Fecha de origen en formato 'YYYY-MM-DD'.
	 *
	 * @return string Representación textual de la edad (ej: "2 años, 5 meses") o mensaje de error.
	 *
	 * @example
	 * ```php
	 * $DataOperations->obtenerEdad('2022-01-01'); // Devuelve '2 años, 5 meses' (a la fecha '2024-06-01')
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

	/******************************************************************************/
	/**
	 * Obtiene únicamente el número de años transcurridos desde una fecha hasta la actualidad.
	 *
	 * @param string $fNacimiento Fecha de origen en formato 'YYYY-MM-DD'.
	 *
	 * @return string Número de años transcurridos como cadena o mensaje de error.
	 *
	 * @example
	 * ```php
	 * $DataOperations->obtenerNumeroAnos('2022-01-01'); // Devuelve '2' (a la fecha '2024-06-01')
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

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Privados                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	/**
	 * Valida que un dato corresponda a una fecha real en formato 'Y-m-d'.
	 * * Rechaza valores nulos, cadenas vacías y ceros numéricos; el resto se
	 * delega a FunctionsDataValidations::validarFecha().
	 *
	 * @param mixed  $Data Valor a validar (ej: '2024-06-01').
	 * @param string $Name Nombre del campo para componer el mensaje de error.
	 *
	 * @return true|string True si la fecha es válida; mensaje de error en caso contrario.
	 *
	 * @example
	 * ```php
	 * $this->_validateDate('2024-06-01', 'Fecha'); // Devuelve true
	 * $this->_validateDate('no-fecha', 'Fecha');   // Devuelve 'El dato ingresado en Fecha no es una fecha (no-fecha)'
	 * ```
	 */
	private function _validateDate($Data, $Name){

		/**********************  Validaciones   **********************/
        // Retorno inmediato si el valor es nulo, cadena vacía o numéricamente cero
        if ($Data === null || trim((string)$Data) === '' || $Data==0) {
            return 'Sin datos ingresados en '.$Name;
        }
        // Validación de tipos de datos mediante el componente externo DataValidations
        if (!$this->DataValidations->validarFecha($Data)) {
			return 'El dato ingresado en '.$Name.' no es una fecha (' . $Data . ')';
		}

		/**********************  Retorno datos  **********************/
		return true;

	}

	/******************************************************************************/
	/**
	 * Valida que un dato corresponda a una hora real en formato 'H:M[:S]'.
	 * * Rechaza valores nulos, cadenas vacías y ceros numéricos; el resto se
	 * delega a FunctionsDataValidations::validarHora().
	 *
	 * @param mixed  $Data Valor a validar (ej: '16:24:00').
	 * @param string $Name Nombre del campo para componer el mensaje de error.
	 *
	 * @return true|string True si la hora es válida; mensaje de error en caso contrario.
	 *
	 * @example
	 * ```php
	 * $this->_validateTime('16:24:00', 'hora'); // Devuelve true
	 * $this->_validateTime('no-hora', 'hora');  // Devuelve 'El dato ingresado en hora no es una hora (no-hora)'
	 * ```
	 */
	private function _validateTime($Data, $Name){

		/**********************  Validaciones   **********************/
        // Retorno inmediato si el valor es nulo, cadena vacía o numéricamente cero
        if ($Data === null || trim((string)$Data) === '' || $Data==0) {
            return 'Sin datos ingresados en '.$Name;
        }
        // Validación de tipos de datos mediante el componente externo DataValidations
        if (!$this->DataValidations->validarHora($Data)) {
			return 'El dato ingresado en '.$Name.' no es una hora (' . $Data . ')';
		}

		/**********************  Retorno datos  **********************/
		return true;

	}

	/******************************************************************************/
	/**
	 * Valida que un dato corresponda a un número entero sin decimales.
	 * * Rechaza valores nulos, cadenas vacías y ceros numéricos; el resto se
	 * delega en FunctionsDataValidations::validarNumero() y validarEntero().
	 *
	 * @param mixed  $Data Valor a validar (ej: 5).
	 * @param string $Name Nombre del campo para componer el mensaje de error.
	 *
	 * @return true|string True si el valor es un entero; mensaje de error en caso contrario.
	 *
	 * @example
	 * ```php
	 * $this->_validateInteger(5, 'divisor');     // Devuelve true
	 * $this->_validateInteger('abc', 'divisor'); // Devuelve 'El dato ingresado en divisor no es un numero (abc)'
	 * ```
	 */
	private function _validateInteger($Data, $Name){

		/**********************  Validaciones   **********************/
        // Retorno inmediato si el valor es nulo, cadena vacía o numéricamente cero
        if ($Data === null || trim((string)$Data) === '' || $Data==0) {
            return 'Sin datos ingresados en '.$Name;
        }
        // Validación de tipos de datos mediante el componente externo DataValidations
        if (!$this->DataValidations->validarNumero($Data) || !$this->DataValidations->validarEntero($Data)) {
            return 'El dato ingresado en '.$Name.' no es un numero ('.$Data.')';
        }

		/**********************  Retorno datos  **********************/
		return true;

	}



}
