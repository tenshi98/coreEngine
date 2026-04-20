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
	/************************************************************************************************************/
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
	 * $DataTime->formatoHoraEstandar('1:1'); //devuelve 01:01
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

	/************************************************************************************************************/
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
	 * $DataTime->formatoHoraProgramada('1:1'); //devuelve 01:01:00
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

	/************************************************************************************************************/
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
	 * $DataTime->formatoHoraArchivos('1:1'); //devuelve 010100
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


	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Internos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
    /************************************************************************************************************/
	private function _validateTime($Data, $Name){

		/**********************  Validaciones   **********************/
        // Retorno inmediato si el valor es nulo, cadena vacía o numéricamente cero
        if ($Data=='' || $Data==0 || $Data=='00:00:00') {return 'Sin datos ingresados en '.$Name;}
        // Validación de tipos de datos mediante el componente externo DataValidations
        if (!$this->DataValidations->validarHora($Data)) {
			return 'El dato ingresado en '.$Name.' no es una hora (' . $Data . ')';
		}

		/**********************  Retorno datos  **********************/
		return true;

	}

}
