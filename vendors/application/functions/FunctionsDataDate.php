<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsDataDate {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                 Instancias                                                      */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	//Definiciones
	private $DataValidations;
	const optionsMesLargo  = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
	const optionsMesCorto  = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
	const optionsDiaSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

	/******************************************************************************/
	/**
	 * Instancia las dependencias internas de la clase de fechas.
	 *
	 * Carga FunctionsDataValidations para validar el formato y la consistencia
	 * de las fechas recibidas antes de formatearlas.
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * $DataDate = new FunctionsDataDate();
	 * ```
	 */
	public function __construct() {
		$this->DataValidations  = new FunctionsDataValidations();
	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Publicos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	/**
     * Formatea una fecha al estilo: "Mes Día del Año".
     *
     * @param string $Fecha Fecha a formatear (ej: '2024-01-01').
     *
     * @return string Fecha formateada (ej: "Enero 01 del 2024") o mensaje de error de validación.
	 *
	 * @example
	 * ```php
	 * $DataDate->fechaCompleta('');           // Devuelve Sin fecha ingresada
	 * $DataDate->fechaCompleta('a');          // Devuelve El dato ingresado no es una fecha
	 * $DataDate->fechaCompleta('2024-01-01'); // Devuelve Enero 01 del 2024
	 * ```
	 *
     */
    public function fechaCompleta($Fecha): string{

        /********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateDate($Fecha, 'Fecha');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) {
			return $dataVal;
		}

        /********************** Si todo esta ok **********************/
        // Instancia un objeto DateTime para la manipulación de los componentes de la fecha
		$mes_c = new DateTime($Fecha);
		// Extrae el día del mes con ceros iniciales (01 a 31)
		$dia = $mes_c->format('d');
		// Obtiene el año en formato de cuatro dígitos
        $ano = $mes_c->format('Y');
        // Obtiene el nombre del mes desde el array optionsMesLargo usando el índice numérico del mes (0-11)
        $mes = self::optionsMesLargo[$mes_c->format('m') - 1];

        /********************** Retorno datos  **********************/
        return $mes.' '.$dia.' del '.$ano;

    }

	/******************************************************************************/
	/**
     * Formatea una fecha al estilo alternativo: "Día de Mes de Año".
     *
     * @param string $Fecha Fecha a formatear (ej: '2024-01-01').
     *
     * @return string Fecha formateada (ej: "01 de Enero de 2024") o error.
	 *
	 * @example
	 * ```php
	 * $DataDate->fechaCompletaAlt('');           // Devuelve Sin fecha ingresada
	 * $DataDate->fechaCompletaAlt('a');          // Devuelve El dato ingresado no es una fecha
	 * $DataDate->fechaCompletaAlt('2024-01-01'); // Devuelve 01 de Enero de 2024
	 * ```
	 *
     */
    public function fechaCompletaAlt($Fecha): string{

        /********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateDate($Fecha, 'Fecha');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) {
			return $dataVal;
		}

        /********************** Si todo esta ok **********************/
		// Instancia un objeto DateTime para la manipulación de los componentes de la fecha
		$mes_c = new DateTime($Fecha);
		// Extrae el día del mes con ceros iniciales (01 a 31)
		$dia = $mes_c->format('d');
		// Obtiene el año en formato de cuatro dígitos
        $ano = $mes_c->format('Y');
		// Obtiene el nombre del mes desde el array optionsMesLargo usando el índice numérico del mes (0-11)
		$mes = self::optionsMesLargo[$mes_c->format('m') - 1];

        /********************** Retorno datos  **********************/
        return $dia.' de '.$mes.' de '.$ano;

    }

	/******************************************************************************/
	/**
	 * Formatea una fecha para obtener el día y el nombre del mes en formato largo.
	 *
	 * @param string $Fecha Fecha en formato válido para procesamiento.
	 *
	 * @return string Retorna el día y mes (ej: "01 Enero") o el mensaje de error de validación.
	 *
	 * @example
	 * ```php
	 * $DataDate->diaMes('');           // Devuelve Sin fecha ingresada
	 * $DataDate->diaMes('a');          // Devuelve El dato ingresado no es una fecha
	 * $DataDate->diaMes('2024-01-01'); // Devuelve 01 Enero
	 * ```
	 *
	 */
	public function diaMes($Fecha): string {

		/**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateDate($Fecha, 'Fecha');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) {
			return $dataVal;
		}

		/********************** Si todo esta ok **********************/
		// Instancia un objeto DateTime para la manipulación de los componentes de la fecha
		$mes_c = new DateTime($Fecha);
		// Extrae el día del mes con ceros iniciales (01 a 31)
		$dia = $mes_c->format('d');
		// Obtiene el nombre del mes desde el array optionsMesLargo usando el índice numérico del mes (0-11)
		$mes = self::optionsMesLargo[$mes_c->format('m') - 1];

		/**********************  Retorno datos  **********************/
		// Concatena el día y el nombre del mes para el resultado final
		return $dia . ' ' . $mes;

	}

	/******************************************************************************/
	/**
	 * Genera una representación textual de la fecha indicando el mes y el año.
	 *
	 * @param string $Fecha La fecha de entrada a procesar.
	 *
	 * @return string Nombre del mes seguido del año (ej: "Enero del 2024") o mensaje de error de validación.
	 *
	 * @example
	 * ```php
	 * $DataDate->fechaMesAno('');           // Devuelve Sin fecha ingresada
	 * $DataDate->fechaMesAno('a');          // Devuelve El dato ingresado no es una fecha
	 * $DataDate->fechaMesAno('2024-01-01'); // Devuelve Enero del 2024
	 * ```
	 *
	 */
	public function fechaMesAno($Fecha): string{

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateDate($Fecha, 'Fecha');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) {
			return $dataVal;
		}

		/********************** Si todo esta ok **********************/
		// Instancia un objeto DateTime para la manipulación de los componentes de la fecha
		$mes_c = new DateTime($Fecha);
		// Obtiene el año en formato de cuatro dígitos
		$ano = $mes_c->format('Y');
		// Obtiene el nombre del mes desde el array optionsMesLargo usando el índice numérico del mes (0-11)
		$mes = self::optionsMesLargo[$mes_c->format('m') - 1];

		/**********************  Retorno datos  **********************/
		// Retorna la cadena construida con el formato descriptivo solicitado
		return $mes.' del '.$ano;

	}

	/******************************************************************************/
	/**
	 * Genera una cadena de texto descriptiva que incluye mes, día, año y hora.
	 *
	 * @param string $Fecha Cadena de texto con fecha y hora (Y-m-d H:i:s).
	 *
	 * @return string Fecha formateada en español (ej: "Diciembre 12 del 2023 13:17:59") o mensaje de error.
	 *
	 * @example
	 * ```php
	 * $DataDate->fullDate('');                    // Devuelve Sin fecha ingresada
	 * $DataDate->fullDate('a');                   // Devuelve El dato ingresado no es una fecha
	 * $DataDate->fullDate('2023-12-12 13:17:59'); // Devuelve Diciembre 12 del 2023 13:17:59
	 * ```
	 *
	 */
	public function fullDate($Fecha): string{

		$Fecha = trim($Fecha);
		/**********************  Validaciones   **********************/
		// Comprobación de valor vacío o nulo
        if ($Fecha === null || trim((string)$Fecha) === '' || $Fecha=='0000-00-00' || $Fecha=='00-00-0000') {
            return 'Sin fecha ingresada en Fecha';
        }
		// Validación de formato estricto incluyendo horas, minutos y segundos
		if(!$this->DataValidations->validarFecha($Fecha, 'Y-m-d H:i:s')){ return 'El dato ingresado en Fecha no es una fecha ('.$Fecha.')';}

		/********************** Si todo esta ok **********************/
		// No se modifica la zona horaria global (la fija el bootstrap de la app);
		// se usa zona explícita solo para descomponer la fecha de entrada.

		// Instancia un objeto DateTime para la manipulación de los componentes de la fecha
		$NewFecha = new DateTime($Fecha, new DateTimeZone('America/Santiago'));
		// Extrae el día del mes con ceros iniciales (01 a 31)
		$dia = $NewFecha->format('d');
		// Obtiene el año en formato de cuatro dígitos
		$ano = $NewFecha->format('Y');
		// Obtiene la hora en formato hh:mm:ss (01:00:00)
		$hora = $NewFecha->format('H:i:s');
		// Obtiene el nombre del mes desde el array optionsMesLargo usando el índice numérico del mes (0-11)
		$mes = self::optionsMesLargo[$NewFecha->format('m') - 1];

		/**********************  Retorno datos  **********************/
		// Construye y retorna la cadena final con el formato descriptivo
		return $mes.' '.$dia.' del '.$ano.' '.$hora;

	}

	/******************************************************************************/
	/**
	 * Formatea una fecha al estilo estadounidense con el nombre del mes en inglés.
	 *
	 * @param string $Fecha Cadena de texto que representa la fecha.
	 *
	 * @return DateTime|string Fecha formateada (ej: "January 01 2024") o error de validación.
	 *
	 * @example
	 * ```php
	 * $DataDate->fechaGringa('');           // Devuelve Sin fecha ingresada
	 * $DataDate->fechaGringa('a');          // Devuelve El dato ingresado no es una fecha
	 * $DataDate->fechaGringa('2024-01-01'); // Devuelve January 01 2024
	 * ```
	 *
	 */
	public function fechaGringa($Fecha): DateTime | string{

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateDate($Fecha, 'Fecha');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) {
			return $dataVal;
		}

		/**********************  Retorno datos  **********************/
		// Genera un recurso de fecha y aplica el formato 'F d Y'
		// 'F' devuelve la representación textual completa del mes en inglés
		return date_format(date_create($Fecha), 'F d Y');

	}

	/******************************************************************************/
	/**
	 * Convierte una fecha al formato estándar extendido (día-mes-año de cuatro dígitos).
	 *
	 * @param string $Fecha Fecha a formatear.
	 *
	 * @return DateTime|string Retorna la fecha en formato 'd-m-Y' o el mensaje de error de validación.
	 *
	 * @example
	 * ```php
	 * $DataDate->fechaEstandar('');           // Devuelve Sin fecha ingresada
	 * $DataDate->fechaEstandar('a');          // Devuelve El dato ingresado no es una fecha
	 * $DataDate->fechaEstandar('2024-01-01'); // Devuelve 01-01-2024
	 * ```
	 *
	 */
	public function fechaEstandar($Fecha): DateTime|string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateDate($Fecha, 'Fecha');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) {
			return $dataVal;
		}

		/**********************  Retorno datos  **********************/
		// Crea un objeto de fecha y aplica el formato de salida con año completo
		return date_format(date_create($Fecha), 'd-m-Y');

	}

	/******************************************************************************/
	/**
	 * Convierte una fecha al formato estándar corto (día-mes-año de dos dígitos).
	 *
	 * @param string $Fecha Fecha a formatear.
	 *
	 * @return DateTime|string Retorna la fecha en formato 'd-m-y' o el mensaje de error de validación.
	 *
	 * @example
	 * ```php
	 * $DataDate->fechaEstandarCorta('');           // Devuelve Sin fecha ingresada
	 * $DataDate->fechaEstandarCorta('a');          // Devuelve El dato ingresado no es una fecha
	 * $DataDate->fechaEstandarCorta('2024-01-01'); // Devuelve 01-01-24
	 * ```
	 *
	 */
	public function fechaEstandarCorta($Fecha): DateTime|string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateDate($Fecha, 'Fecha');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) {
			return $dataVal;
		}

		/**********************  Retorno datos  **********************/
		// Genera la representación de la fecha con el año abreviado a dos dígitos
		return date_format(date_create($Fecha), 'd-m-y');

	}

	/******************************************************************************/
	/**
	 * Normaliza una fecha al formato estándar de base de datos (AAAA-MM-DD).
	 *
	 * @param string $Fecha La fecha de entrada a normalizar.
	 *
	 * @return DateTime|string La fecha en formato 'Y-m-d' o el resultado de la validación si falla.
	 *
	 * @example
	 * ```php
	 * $DataDate->fechaNormalizada('');           // Devuelve Sin fecha ingresada
	 * $DataDate->fechaNormalizada('a');          // Devuelve El dato ingresado no es una fecha
	 * $DataDate->fechaNormalizada('2024-01-01'); // Devuelve 2024-01-01
	 * ```
	 *
	 */
	public function fechaNormalizada($Fecha): DateTime | string{

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateDate($Fecha, 'Fecha');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) {
			return $dataVal;
		}

		/**********************  Retorno datos  **********************/
		// Sustituye barras inclinadas por guiones para asegurar la compatibilidad con date_create
		// Retorna la fecha formateada como Año-Mes-Día
		return date_format(date_create(str_replace('/', '-', $Fecha)), 'Y-m-d');

	}

	/******************************************************************************/
	/**
	 * Formatea una fecha para su uso en nomenclatura de archivos (AAAAMMDD).
	 *
	 * @param string $Fecha La fecha de entrada a formatear.
	 *
	 * @return DateTime|string La cadena de texto con la fecha compacta o el error de validación.
	 *
	 * @example
	 * ```php
	 * $DataDate->fechaArchivos('');           // Devuelve Sin fecha ingresada
	 * $DataDate->fechaArchivos('a');          // Devuelve El dato ingresado no es una fecha
	 * $DataDate->fechaArchivos('2024-01-01'); // Devuelve 20240101
	 * ```
	 *
	 */
	public function fechaArchivos($Fecha): DateTime | string{

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateDate($Fecha, 'Fecha');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) {
			return $dataVal;
		}

		/**********************  Retorno datos  **********************/
		// Reemplaza separadores y genera una cadena numérica sin guiones ni espacios
		return date_format(date_create(str_replace('/', '-', $Fecha)), 'Ymd');

	}

	/******************************************************************************/
	/**
	 * Convierte una fecha/hora estándar a su equivalente en timestamp Unix.
	 *
	 * Acepta tanto el formato fecha/hora 'Y-m-d H:i:s' como la fecha simple
	 * 'Y-m-d' (a la cual se le asigna la medianoche del día). El resultado usa
	 * la zona horaria por defecto del servidor (America/Santiago).
	 *
	 * @param string $Fecha Fecha a convertir (ej: '2026-10-15 14:30:00').
	 *
	 * @return int|string Timestamp Unix; mensaje de error si la fecha no es válida.
	 *
	 * @example
	 * ```php
	 * $DataDate->fecha2Timestamp('2026-10-15 14:30:00'); // Devuelve 1792103400 (aprox, según zona)
	 * $DataDate->fecha2Timestamp('2026-01-01');          // Devuelve timestamp de la medianoche
	 * $DataDate->fecha2Timestamp('a');                   // Devuelve El dato ingresado en Fecha no es una fecha (a)
	 * ```
	 *
	 */
	public function fecha2Timestamp(string $Fecha): int|string {

		/********************** Validaciones   **********************/
        if ($Fecha === null || trim((string)$Fecha) === '') {
            return 'Sin fecha ingresada en Fecha';
        }
		// Se aceptan ambos formatos estándar: fecha/hora y fecha simple
		$EsFechaHora = $this->DataValidations->validarFecha($Fecha, 'Y-m-d H:i:s');
		$EsFecha     = $this->DataValidations->validarFecha($Fecha, 'Y-m-d');
		if (!$EsFechaHora && !$EsFecha) {
			return 'El dato ingresado en Fecha no es una fecha ('.$Fecha.')';
		}

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		// strtotime convierte la cadena al número de segundos desde la época Unix
		return (int)strtotime($Fecha);

	}

	/******************************************************************************/
	/**
	 * Convierte un timestamp Unix a una fecha con el formato indicado.
	 *
	 * @param mixed $Timestamp Marca de tiempo Unix a convertir.
	 * @param string $Formato Formato de salida de PHP (por defecto 'Y-m-d H:i:s').
	 *
	 * @return string Fecha formateada; mensaje de error si el dato no es un entero.
	 *
	 * @example
	 * ```php
	 * $DataDate->timestamp2Fecha(0);                    // Devuelve 1969-12-31 21:00:00 (zona America/Santiago)
	 * $DataDate->timestamp2Fecha(1792103400, 'Y-m-d');  // Devuelve la fecha en formato corto
	 * $DataDate->timestamp2Fecha('a');                  // Devuelve El dato ingresado no es un timestamp (a)
	 * ```
	 *
	 */
	public function timestamp2Fecha($Timestamp, string $Formato = 'Y-m-d H:i:s'): string {

		/********************** Validaciones   **********************/
		// Se verifica que el dato sea un entero no vacío
		if (!$this->DataValidations->validarEntero($Timestamp)) {
			return 'El dato ingresado no es un timestamp ('.(string)$Timestamp.')';
		}

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		// date formatea el timestamp según el formato solicitado y la zona del servidor
		return date($Formato, (int)$Timestamp);

	}

	/******************************************************************************/
	/**
	 * Obtiene el número del día del mes sin ceros iniciales.
	 *
	 * @param string $Fecha Fecha a procesar.
	 *
	 * @return string Número del día (1 a 31) o error de validación.
	 *
	 * @example
	 * ```php
	 * $DataDate->fecha2NdiaMes('');           // Devuelve Sin fecha ingresada
	 * $DataDate->fecha2NdiaMes('a');          // Devuelve El dato ingresado no es una fecha
	 * $DataDate->fecha2NdiaMes('2024-01-02'); // Devuelve 2
	 * ```
	 *
	 */
	public function fecha2NdiaMes($Fecha): string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateDate($Fecha, 'Fecha');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) {
			return $dataVal;
		}

		/********************** Si todo esta ok **********************/
		// Instancia un objeto DateTime para la manipulación de los componentes de la fecha
		$subdato = new DateTime($Fecha);

		/**********************  Retorno datos  **********************/
		// Retorna el día del mes sin ceros iniciales mediante el formato 'j'
		return $subdato->format("j");

	}

	/******************************************************************************/
	/**
	 * Obtiene el número del día del mes con dos dígitos (ceros iniciales).
	 *
	 * @param string $Fecha Fecha a procesar.
	 *
	 * @return string Número del día (01 a 31) o error de validación.
	 *
	 * @example
	 * ```php
	 * $DataDate->fecha2NdiaMesCon0('');           // Devuelve Sin fecha ingresada
	 * $DataDate->fecha2NdiaMesCon0('a');          // Devuelve El dato ingresado no es una fecha
	 * $DataDate->fecha2NdiaMesCon0('2024-01-01'); // Devuelve 01
	 * ```
	 *
	 */
	public function fecha2NdiaMesCon0($Fecha): string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateDate($Fecha, 'Fecha');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) {
			return $dataVal;
		}

		/********************** Si todo esta ok **********************/
		// Instancia un objeto DateTime para la manipulación de los componentes de la fecha
		$subdato = new DateTime($Fecha);

		/**********************  Retorno datos  **********************/
		// Retorna el día del mes con ceros iniciales mediante el formato 'd'
		return $subdato->format('d');

	}

	/******************************************************************************/
	/**
	 * Obtiene la representación numérica del día de la semana (ISO-8601).
	 *
	 * @param string $Fecha Fecha a procesar.
	 *
	 * @return string Número del día de la semana (1 para Lunes, 7 para Domingo) o mensaje de error de validación.
	 *
	 * @example
	 * ```php
	 * $DataDate->fecha2NDiaSemana('');           // Devuelve Sin fecha ingresada
	 * $DataDate->fecha2NDiaSemana('a');          // Devuelve El dato ingresado no es una fecha
	 * $DataDate->fecha2NDiaSemana('2024-01-01'); // Devuelve 1
	 * ```
	 *
	 */
	public function fecha2NDiaSemana($Fecha): string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateDate($Fecha, 'Fecha');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) {
			return $dataVal;
		}

		/********************** Si todo esta ok **********************/
		// Instancia un objeto DateTime para la manipulación de los componentes de la fecha
		$subdato = new DateTime($Fecha);

		/**********************  Retorno datos  **********************/
		// Retorna el número del día de la semana según el estándar ISO-8601
		return $subdato->format('N');

	}

	/******************************************************************************/
	/**
	 * Obtiene el nombre completo del día de la semana en español.
	 *
	 * @param string $Fecha Fecha a procesar.
	 *
	 * @return string Nombre del día (ej: "Martes") o error de validación.
	 *
	 * @example
	 * ```php
	 * $DataDate->fecha2NombreDia('');           // Devuelve Sin fecha ingresada
	 * $DataDate->fecha2NombreDia('a');          // Devuelve El dato ingresado no es una fecha
	 * $DataDate->fecha2NombreDia('2024-01-02'); // Devuelve Martes
	 * ```
	 *
	 */
	public function fecha2NombreDia($Fecha): string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateDate($Fecha, 'Fecha');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) {
			return $dataVal;
		}

		/**********************  Retorno datos  **********************/
		// Utiliza el método fecha2NDiaSemana para obtener el índice (1-7)
		// Se resta 1 para ajustar al índice base cero del arreglo optionsDiaSemana
		// Obtiene el nombre del dia desde el array optionsDiaSemana usando el índice numérico del dia (0-6)
		return self::optionsDiaSemana[$this->fecha2NDiaSemana($Fecha) - 1];

	}

	/******************************************************************************/
	/**
	 * Obtiene el número de la semana del año basándose en la fecha proporcionada.
	 *
	 * @param string $Fecha Fecha de entrada para el cálculo.
	 *
	 * @return string Número de la semana (ISO-8601) con ceros iniciales o error de validación.
	 *
	 * @example
	 * ```php
	 * $DataDate->fecha2NSemana('');           // Devuelve Sin fecha ingresada
	 * $DataDate->fecha2NSemana('a');          // Devuelve El dato ingresado no es una fecha
	 * $DataDate->fecha2NSemana('2024-01-01'); // Devuelve 01
	 * ```
	 *
	 */
	public function fecha2NSemana($Fecha): string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateDate($Fecha, 'Fecha');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) {
			return $dataVal;
		}

		/********************** Si todo esta ok **********************/
		// Instancia un objeto DateTime para la manipulación de los componentes de la fecha
		$subdato = new DateTime($Fecha);

		/**********************  Retorno datos  **********************/
		// Retorna el número de la semana del año (01 a 52/53)
		return $subdato->format("W");

	}

	/******************************************************************************/
	/**
	 * Obtiene la representación numérica del mes a partir de una fecha.
	 *
	 * @param string $Fecha Fecha de entrada.
	 *
	 * @return string Número del mes sin ceros iniciales (1 a 12) o error de validación.
	 *
	 * @example
	 * ```php
	 * $DataDate->fecha2NMes('');           // Devuelve Sin fecha ingresada
	 * $DataDate->fecha2NMes('a');          // Devuelve El dato ingresado no es una fecha
	 * $DataDate->fecha2NMes('2024-01-01'); // Devuelve 1
	 * ```
	 *
	 */
	public function fecha2NMes($Fecha): string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateDate($Fecha, 'Fecha');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) {
			return $dataVal;
		}

		/********************** Si todo esta ok **********************/
		// Instancia un objeto DateTime para la manipulación de los componentes de la fecha
		$subdato = new DateTime($Fecha);

		/**********************  Retorno datos  **********************/
		// Retorna el mes en formato numérico sin ceros a la izquierda
		return $subdato->format("n");

	}

	/******************************************************************************/
	/**
	 * Recupera el nombre completo del mes en español basándose en la fecha.
	 *
	 * @param string $Fecha Fecha de entrada.
	 *
	 * @return string Nombre largo del mes (ej: "Enero") o error de validación.
	 *
	 * @example
	 * ```php
	 * $DataDate->fecha2NombreMes('');           // Devuelve Sin fecha ingresada
	 * $DataDate->fecha2NombreMes('a');          // Devuelve El dato ingresado no es una fecha
	 * $DataDate->fecha2NombreMes('2024-01-01'); // Devuelve Enero
	 * ```
	 *
	 */
	public function fecha2NombreMes($Fecha): string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateDate($Fecha, 'Fecha');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) {
			return $dataVal;
		}

		/**********************  Retorno datos  **********************/
		// Utiliza el método fecha2NMes para obtener el índice numérico (1-12)
		// Se resta 1 para mapear correctamente al índice del arreglo constante optionsMesLargo
		// Obtiene el nombre del mes desde el array optionsMesLargo usando el índice numérico del mes (0-11)
		return self::optionsMesLargo[$this->fecha2NMes($Fecha) - 1];

	}

	/******************************************************************************/
	/**
	 * Obtiene la abreviatura de tres letras del mes basándose en la fecha.
	 *
	 * @param string $Fecha Fecha de entrada.
	 *
	 * @return string Nombre corto del mes (ej: "Ene") o error de validación.
	 *
	 * @example
	 * ```php
	 * $DataDate->fecha2NombreMesCorto('');           // Devuelve Sin fecha ingresada
	 * $DataDate->fecha2NombreMesCorto('a');          // Devuelve El dato ingresado no es una fecha
	 * $DataDate->fecha2NombreMesCorto('2024-01-01'); // Devuelve Ene
	 * ```
	 *
	 */
	public function fecha2NombreMesCorto($Fecha): string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateDate($Fecha, 'Fecha');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) {
			return $dataVal;
		}

		/**********************  Retorno datos  **********************/
		// Obtiene el número del mes y accede al arreglo constante optionsMesCorto
		// El ajuste de índice (-1) es necesario para la correspondencia con arreglos base cero
		// Obtiene el nombre corto del mes desde el array optionsMesLargo usando el índice numérico del mes (0-11)
		return self::optionsMesCorto[$this->fecha2NMes($Fecha) - 1];

	}

	/******************************************************************************/
	/**
	 * Extrae el año de una fecha proporcionada.
	 *
	 * @param string $Fecha Cadena de texto que representa la fecha.
	 *
	 * @return string Año en formato de cuatro dígitos (YYYY) o mensaje de error de validación.
	 *
	 * @example
	 * ```php
	 * $DataDate->fecha2Ano('');           // Devuelve Sin fecha ingresada
	 * $DataDate->fecha2Ano('a');          // Devuelve El dato ingresado no es una fecha
	 * $DataDate->fecha2Ano('2024-01-01'); // Devuelve 2024
	 * ```
	 *
	 */
	public function fecha2Ano($Fecha): string{

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateDate($Fecha, 'Fecha');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) {
			return $dataVal;
		}

		/********************** Si todo esta ok **********************/
		// Instancia un objeto DateTime para la manipulación de los componentes de la fecha
		$subdato = new DateTime($Fecha);

		/**********************  Retorno datos  **********************/
		// Retorna el año utilizando el formato 'Y' (ej: 2024)
		return $subdato->format('Y');

	}

	/******************************************************************************/
	/**
	 * Calcula y retorna la fecha correspondiente al último día del mes de la fecha dada.
	 *
	 * @param string $Fecha Cadena de texto que representa la fecha.
	 *
	 * @return string Fecha completa del último día del mes (YYYY-MM-DD) o error de validación.
	 *
	 * @example
	 * ```php
	 * $DataDate->fechaUltimoDiaMes('');           // Devuelve Sin fecha ingresada
	 * $DataDate->fechaUltimoDiaMes('a');          // Devuelve El dato ingresado no es una fecha
	 * $DataDate->fechaUltimoDiaMes('2024-01-01'); // Devuelve 2024-01-31
	 * ```
	 *
	 */
	public function fechaUltimoDiaMes($Fecha): string{

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateDate($Fecha, 'Fecha');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) {
			return $dataVal;
		}

		/**********************  Retorno datos  **********************/
		// Utiliza el parámetro de formato 't' que devuelve el número de días del mes dado
		// Se combina con Y-m para reconstruir la fecha completa del último día
		return date("Y-m-t", strtotime($Fecha));

	}

	/******************************************************************************/
	/**
	 * Describe la diferencia entre una fecha y la actual en términos legibles.
	 *
	 * Calcula la distancia respecto a la fecha base (por defecto la fecha y hora
	 * actual) y la expresa en el componente de mayor peso: años, meses, días,
	 * horas o minutos. Diferencias menores a un minuto se reportan como "ahora mismo".
	 *
	 * @param string $Fecha Fecha de referencia (formato 'Y-m-d H:i:s' o 'Y-m-d').
	 * @param string|null $FechaBase Fecha desde la que se compara (por defecto la actual).
	 *
	 * @return string Texto relativo (ej: "hace 3 días", "en 2 horas"); mensaje de error si la fecha no es válida.
	 *
	 * @example
	 * ```php
	 * $DataDate->fechaRelativa('2026-10-12 10:00:00', '2026-10-15 10:00:00'); // Devuelve "hace 3 días"
	 * $DataDate->fechaRelativa(date('Y-m-d H:i:s'));                            // Devuelve "ahora mismo"
	 * $DataDate->fechaRelativa('a');                                            // Devuelve El dato ingresado en Fecha no es una fecha (a)
	 * ```
	 *
	 */
	public function fechaRelativa(string $Fecha, ?string $FechaBase = null): string {

		/********************** Validaciones   **********************/
        if ($Fecha === null || trim((string)$Fecha) === '') {
            return 'Sin fecha ingresada en Fecha';
        }
		// Se aceptan los dos formatos estándar: fecha/hora y fecha simple
		if (!$this->DataValidations->validarFecha($Fecha, 'Y-m-d H:i:s') && !$this->DataValidations->validarFecha($Fecha, 'Y-m-d')) {
			return 'El dato ingresado en Fecha no es una fecha ('.$Fecha.')';
		}
		// La fecha base, cuando se informa, debe cumplir el mismo criterio
		if ($FechaBase !== null) {
			if (trim((string)$FechaBase) === '') {
				return 'Sin fecha ingresada en FechaBase';
			}
			if (!$this->DataValidations->validarFecha($FechaBase, 'Y-m-d H:i:s') && !$this->DataValidations->validarFecha($FechaBase, 'Y-m-d')) {
				return 'El dato ingresado en FechaBase no es una fecha ('.$FechaBase.')';
			}
		}

		/********************** Si todo esta ok **********************/
		// Se construyen los objetos DateTime: base por defecto = fecha y hora actual
		$dRef  = new DateTime($Fecha);
		$dBase = new DateTime($FechaBase ?? date('Y-m-d H:i:s'));
		// Signo de la diferencia: negativo si la fecha es anterior a la base
		$Adelante = $dRef > $dBase;
		$dif = $dBase->diff($dRef);

		/**********************  Retorno datos  **********************/
		// Diferencia menor a un minuto: se reporta como instante actual
		if ($dif->y === 0 && $dif->m === 0 && $dif->d === 0 && $dif->h === 0 && $dif->i === 0) {
			return 'ahora mismo';
		}
		// Selección del componente de mayor peso y su etiqueta en plural
		if ($dif->y > 0)      { $Cantidad = $dif->y; $Unidad = $dif->y === 1 ? 'año'     : 'años'; }
		elseif ($dif->m > 0)  { $Cantidad = $dif->m; $Unidad = $dif->m === 1 ? 'mes'     : 'meses'; }
		elseif ($dif->d > 0)  { $Cantidad = $dif->d; $Unidad = $dif->d === 1 ? 'día'     : 'días'; }
		elseif ($dif->h > 0)  { $Cantidad = $dif->h; $Unidad = $dif->h === 1 ? 'hora'    : 'horas'; }
		else                  { $Cantidad = $dif->i; $Unidad = $dif->i === 1 ? 'minuto'  : 'minutos'; }

		// El prefijo indica si el hecho es pasado ("hace") o futuro ("en")
		return ($Adelante ? 'en ' : 'hace ').$Cantidad.' '.$Unidad;

	}

	/******************************************************************************/
	/**
	 * Determina si una fecha cae en sábado o domingo.
	 *
	 * @param string $Fecha Fecha a evaluar en formato 'Y-m-d'.
	 *
	 * @return bool|string true si es fin de semana, false si es día hábil;
	 *                     mensaje de error si la fecha no es válida.
	 *
	 * @example
	 * ```php
	 * $DataDate->esFinDeSemana('2026-10-17'); // Devuelve true (sábado)
	 * $DataDate->esFinDeSemana('2026-10-15'); // Devuelve false (jueves)
	 * $DataDate->esFinDeSemana('a');          // Devuelve El dato ingresado en Fecha no es una fecha (a)
	 * ```
	 *
	 */
	public function esFinDeSemana(string $Fecha): bool|string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateDate($Fecha, 'Fecha');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

		/********************** Si todo esta ok **********************/
		// date('w') devuelve 0 para domingo y 6 para sábado
		$DiaSemana = (int)date('w', strtotime($Fecha));

		/**********************  Retorno datos  **********************/
		return $DiaSemana === 0 || $DiaSemana === 6;

	}

	/******************************************************************************/
	/**
	 * Genera la lista de fechas comprendidas entre dos fechas (inclusiva).
	 *
	 * Útil para filtros de rango y exportaciones: devuelve cada día del período
	 * en el formato solicitado, incluyendo tanto la fecha de inicio como la de fin.
	 *
	 * @param string $FechaInicio Fecha inicial en formato 'Y-m-d'.
	 * @param string $FechaFin Fecha final en formato 'Y-m-d'.
	 * @param string $Formato Formato de salida de PHP (por defecto 'Y-m-d').
	 *
	 * @return array|string Arreglo de fechas; mensaje de error si el rango no es válido.
	 *
	 * @example
	 * ```php
	 * $DataDate->rangoFechas('2026-01-01', '2026-01-03');
	 * // Devuelve ['2026-01-01', '2026-01-02', '2026-01-03']
	 * $DataDate->rangoFechas('2026-01-10', '2026-01-01'); // Devuelve La fecha de inicio no puede ser posterior a la fecha de termino
	 * ```
	 *
	 */
	public function rangoFechas(string $FechaInicio, string $FechaFin, string $Formato = 'Y-m-d'): array|string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna de ambas fechas
		$dataVal_1 = $this->_validateDate($FechaInicio, 'FechaInicio');
		if ($dataVal_1 !== true) { return $dataVal_1; }
		$dataVal_2 = $this->_validateDate($FechaFin, 'FechaFin');
		if ($dataVal_2 !== true) { return $dataVal_2; }
		// El inicio no puede ser posterior al fin
		if (strtotime($FechaInicio) > strtotime($FechaFin)) {
			return 'La fecha de inicio no puede ser posterior a la fecha de termino';
		}
		// Protección contra rangos excesivos (máximo 10 años)
		$dias = (int)((strtotime($FechaFin) - strtotime($FechaInicio)) / 86400);
		if ($dias > 3660) {
			return 'El rango solicitado supera los 10 anos';
		}

		/********************** Si todo esta ok **********************/
		// Recorrido día a día desde el inicio hasta el fin inclusive
		$Rango  = [];
		$Actual = strtotime($FechaInicio);
		$Fin    = strtotime($FechaFin);
		while ($Actual <= $Fin) {
			$Rango[] = date($Formato, $Actual);
			$Actual  = strtotime('+1 day', $Actual);
		}

		/**********************  Retorno datos  **********************/
		return $Rango;

	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Privados                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	/**
	 * Valida que el dato recibido sea una fecha real no vacía (formato AAAA-MM-DD).
	 *
	 * Descarta valores nulos, vacíos y las cadenas '0000-00-00'/'00-00-0000';
	 * luego verifica la fecha con FunctionsDataValidations. Si la validación
	 * falla, retorna el mensaje de error con el nombre del campo.
	 *
	 * @param mixed  $Fecha Fecha a validar (ej: '2024-01-01').
	 * @param string $Name  Nombre del campo usado en los mensajes de error.
	 *
	 * @return true|string true si la fecha es válida o mensaje de error.
	 *
	 * @example
	 * ```php
	 * $this->_validateDate('2024-01-01', 'Fecha'); // Devuelve true
	 * $this->_validateDate('a', 'Fecha');          // Devuelve 'El dato ingresado en Fecha no es una fecha (a)'
	 * $this->_validateDate('', 'Fecha');           // Devuelve 'Sin fecha ingresada en Fecha'
	 * ```
	 */
	private function _validateDate($Fecha, $Name){

		/**********************  Validaciones   **********************/
        if ($Fecha === null || trim((string)$Fecha) === '' || $Fecha=='0000-00-00' || $Fecha=='00-00-0000') {
            return 'Sin fecha ingresada en '.$Name;
        }
		if(!$this->DataValidations->validarFecha($Fecha)){
			return 'El dato ingresado en '.$Name.' no es una fecha ('.$Fecha.')';
		}

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return true;

	}


}
