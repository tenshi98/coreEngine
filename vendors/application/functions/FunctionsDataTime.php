<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsDataTime {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                 Instancias                                                      */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	//Definiciones
	private $DataValidations;

	/******************************************************************************/
	/**
	 * Instancia las dependencias internas de la clase de tiempo.
	 *
	 * Carga FunctionsDataValidations para validar el formato y la consistencia
	 * de las horas recibidas antes de formatearlas.
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * $DataTime = new FunctionsDataTime();
	 * ```
	 */
	public function __construct() {
		$this->DataValidations = new FunctionsDataValidations();
	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Publicos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	/**
     * Convierte una hora ingresada a un formato estándar de 24 horas (HH:mm).
     * * Ideal para visualización en interfaces de usuario donde no se requieren los segundos.
     *
     * @param string $Hora Cadena de tiempo a formatear (ej: '1:1', '13:5').
     *
     * @return string Hora en formato 'H:i' (ej: '01:01', '13:05') o 'Sin Hora' si es medianoche exacta.
	 *
	 * @example
	 * ```php
	 * $DataTime->formatoHoraEstandar('1:1'); // Devuelve 01:01
	 * ```
	 *
     */
    public function formatoHoraEstandar($Hora): string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateTime($Hora, 'Hora');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

        /********************** Proceso de Formateo **********************/
        // Crea un objeto de fecha a partir de la cadena y aplica el formato de horas y minutos
        return date_format(date_create($Hora), 'H:i');

    }

	/******************************************************************************/
	/**
     * Convierte una hora ingresada al formato extendido de programación (HH:mm:ss).
     * * Asegura que la cadena resultante incluya siempre los segundos, útil para
     * inserciones precisas en bases de datos o comparaciones lógicas.
     *
     * @param string $Hora Cadena de tiempo a formatear.
     *
     * @return string Hora en formato 'H:i:s' (ej: '01:01:00') o 'Sin Hora'.
	 *
	 * @example
	 * ```php
	 * $DataTime->formatoHoraProgramada('1:1'); // Devuelve 01:01:00
	 * ```
	 *
     */
    public function formatoHoraProgramada($Hora): string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateTime($Hora, 'Hora');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

        /********************** Proceso de Formateo **********************/
        // Instanciación de objeto DateTime para manipulación de formato extendido
        $HoraForm = new DateTime($Hora);

        /********************** Retorno datos **********************/
        return $HoraForm->format('H:i:s');

    }

	/******************************************************************************/
	/**
     * Formatea la hora omitiendo separadores para su uso seguro en nombres de archivos.
     * * Genera una cadena compacta de 6 dígitos que representa horas, minutos y segundos.
     *
     * @param string $Hora Cadena de tiempo a formatear.
     *
     * @return string Hora en formato 'His' (ej: '010100') o 'Sin Hora'.
	 *
	 * @example
	 * ```php
	 * $DataTime->formatoHoraArchivos('1:1'); // Devuelve 010100
	 * ```
	 *
     */
    public function formatoHoraArchivos($Hora): string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateTime($Hora, 'Hora');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

        /********************** Proceso de Formateo **********************/
        // Conversión a objeto DateTime para eliminar los caracteres de separación ':'
        $HoraForm = new DateTime($Hora);

        /********************** Retorno datos **********************/
        return $HoraForm->format('His');

    }

	/******************************************************************************/
	/**
	 * Convierte una hora al formato legible de 12 horas con AM/PM.
	 *
	 * Acepta los formatos 'H:i' y 'H:i:s' y devuelve la hora en formato
	 * "hh:mm AM/PM" (ej: '14:05:00' → "02:05 PM", '00:30' → "12:30 AM").
	 *
	 * @param string $Hora Hora a convertir ('H:i' o 'H:i:s').
	 *
	 * @return string Hora en formato 12 horas; mensaje de error si la hora no es válida.
	 *
	 * @example
	 * ```php
	 * $DataTime->formatoHora12('14:05:00'); // Devuelve 02:05 PM
	 * $DataTime->formatoHora12('00:30');    // Devuelve 12:30 AM
	 * $DataTime->formatoHora12('a');        // Devuelve El dato ingresado en Hora no es una hora (a)
	 * ```
	 *
	 */
    public function formatoHora12($Hora): string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la hora recibida
		$dataVal = $this->_validateTime($Hora, 'Hora');
		if ($dataVal !== true) { return $dataVal; }

        /********************** Proceso de Formateo **********************/
        // Conversión a objeto DateTime para obtener el formato de 12 horas
        $Hora12 = new DateTime($Hora);

        /********************** Retorno datos **********************/
        // 'h:i A' produce 2 horas + minutos + AM/PM
        return $Hora12->format('h:i A');

    }

	/******************************************************************************/
	/**
	 * Determina si una hora cae dentro de un horario laboral.
	 *
	 * El inicio es inclusivo y el término exclusivo (inicio <= hora < término).
	 * Soporta horarios que cruzan la medianoche (ej: 22:00 a 06:00).
	 *
	 * @param string $Hora Hora a evaluar ('H:i' o 'H:i:s').
	 * @param string $HoraInicio Inicio del horario (por defecto '09:00').
	 * @param string $HoraTermino Término del horario (por defecto '18:00').
	 *
	 * @return bool|string true si está dentro del horario; mensaje de error si algún dato no es válido.
	 *
	 * @example
	 * ```php
	 * $DataTime->esHorarioLaboral('10:00:00');            // Devuelve true
	 * $DataTime->esHorarioLaboral('19:00:00');            // Devuelve false
	 * $DataTime->esHorarioLaboral('23:30:00', '22:00', '06:00'); // Devuelve true (horario nocturno)
	 * ```
	 *
	 */
    public function esHorarioLaboral($Hora, $HoraInicio = '09:00', $HoraTermino = '18:00'): bool|string {

		/********************** Validaciones   **********************/
		// Ejecuta la validación interna de las tres horas involucradas
		$dataVal_1 = $this->_validateTime($Hora, 'Hora');
		if ($dataVal_1 !== true) { return $dataVal_1; }
		$dataVal_2 = $this->_validateTime($HoraInicio, 'HoraInicio');
		if ($dataVal_2 !== true) { return $dataVal_2; }
		$dataVal_3 = $this->_validateTime($HoraTermino, 'HoraTermino');
		if ($dataVal_3 !== true) { return $dataVal_3; }

		// Un horario con inicio igual a término no tiene ventana válida
		if ($HoraInicio === $HoraTermino) {
			return 'El horario de inicio y termino no pueden ser iguales';
		}

        /********************** Si todo esta ok **********************/
        // Conversión a minutos desde la medianoche para comparar de forma lineal
        $minutoHora     = (new DateTime($Hora))->format('G') * 60 + (new DateTime($Hora))->format('i');
        $minutoInicio   = (new DateTime($HoraInicio))->format('G') * 60 + (new DateTime($HoraInicio))->format('i');
        $minutoTermino  = (new DateTime($HoraTermino))->format('G') * 60 + (new DateTime($HoraTermino))->format('i');

        /********************** Retorno datos **********************/
        // Horario normal: ventana contigua dentro del mismo día
        if ($minutoInicio < $minutoTermino) {
            return $minutoHora >= $minutoInicio && $minutoHora < $minutoTermino;
        }
        // Horario que cruza medianoche: se acepta desde el inicio o antes del término
        return $minutoHora >= $minutoInicio || $minutoHora < $minutoTermino;

    }

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Privados                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
    /******************************************************************************/
	/**
	 * Valida que el dato recibido corresponda a una hora no vacía.
	 *
	 * Comprueba que el valor no sea nulo, vacío ni '00:00:00' y que además
	 * sea reconocido como hora por FunctionsDataValidations::validarHora().
	 *
	 * @param string $Data Cadena de tiempo a validar (ej: '13:5', '01:01:00').
	 * @param string $Name Nombre del campo recibido, usado en el mensaje de error (ej: 'Hora').
	 *
	 * @return bool|string true si la hora es válida; mensaje de error (string) si no lo es.
	 *
	 * @example
	 * ```php
	 * $this->_validateTime('13:5', 'Hora');  // devuelve true
	 * $this->_validateTime('', 'Hora');      // devuelve 'Sin datos ingresados en Hora'
	 * ```
	 */
	private function _validateTime($Data, $Name){

		/**********************  Validaciones   **********************/
        // Retorno inmediato si el valor es nulo, cadena vacía o numéricamente cero
        if ($Data === null || trim((string)$Data) === '' || $Data==0 || $Data=='00:00:00') {
            return 'Sin datos ingresados en '.$Name;
        }
        // Validación de tipos de datos mediante el componente externo DataValidations
        if (!$this->DataValidations->validarHora($Data)) {
			return 'El dato ingresado en '.$Name.' no es una hora (' . $Data . ')';
		}

		/**********************  Retorno datos  **********************/
		return true;

	}

}
