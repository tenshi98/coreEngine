<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsDataNumbers {

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
     * Convierte un valor numérico a un formato de cadena con separadores de miles (punto)
     * y decimales (coma), rellenando con ceros según la precisión indicada.
     *
     * @param mixed $valor El número original que se desea formatear.
     * @param int $n_decimales Cantidad de dígitos decimales que se deben mostrar.
     * @return string El número formateado o un mensaje de error si la validación falla.
     * * @example $DataNumbers->Cantidades(1250.85, 6); // Devuelve "1.250,850000"
     */
    public function Cantidades($valor, $n_decimales): string{
        /*
        *=================================================    Modo de uso  =================================================
        *
        * //se formatea numero
        * $DataNumbers->Cantidades(1250.85, 6); //Devuelve 1.250,850000
        *
        *===================================================================================================================
        */

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateValue($valor);
		$dataVal_2 = $this->_validateInteger($n_decimales);
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }
		if ($dataVal_2 !== true) { return $dataVal_2; }

        /**********************  Retorno datos  **********************/
        // Formatea el número utilizando punto para miles y coma para decimales
        return number_format($valor,$n_decimales,',','.');

    }

	/************************************************************************************************************/
	/**
     * Formatea un número como un correlativo de documento agregando ceros a la izquierda
     * hasta alcanzar la longitud deseada.
     *
     * @param mixed $valor El número de documento original.
     * @param int $n_ceros Longitud total deseada de la cadena resultante.
     * @return string El número paddeado con ceros o un mensaje de error.
     * * @example $DataNumbers->nDoc(25, 7); // Devuelve "0000025"
     */
    public function nDoc($valor, $n_ceros): string{
        /*
        *=================================================    Modo de uso  =================================================
        *
        * //se formatea numero
        * $DataNumbers->nDoc(25, 7); //Devuelve 0000025
        *
        *===================================================================================================================
        */

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateInteger($valor);
		$dataVal_2 = $this->_validateInteger($n_ceros);
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }
		if ($dataVal_2 !== true) { return $dataVal_2; }

        /**********************  Retorno datos  **********************/
        // Rellena la cadena por la izquierda con el carácter "0" hasta completar n_ceros
        return str_pad($valor, $n_ceros, "0", STR_PAD_LEFT);

    }

	/************************************************************************************************************/
	/**
     * Formatea un valor numérico como moneda, anteponiendo el símbolo de peso ($)
     * y aplicando formato de miles y decimales.
     *
     * @param mixed $valor El monto numérico a formatear.
     * @param int $n_decimales Cantidad de decimales requeridos.
     * @return string Representación monetaria del valor.
     * * @example $DataNumbers->Valores(1500.85565, 2); // Devuelve "$ 1.500,86"
     */
    public function Valores($valor, $n_decimales): string{
        /*
        *=================================================    Modo de uso  =================================================
        *
        * //se formatea numero
        * $DataNumbers->Valores(1500.85565,2); //Devuelve $ 1.500,86
        *
        *===================================================================================================================
        */

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateValue($valor);
		$dataVal_2 = $this->_validateInteger($n_decimales);
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }
		if ($dataVal_2 !== true) { return $dataVal_2; }

        /**********************  Retorno datos  **********************/
        // Concatena el símbolo de peso con el número formateado (punto para miles, coma para decimales)
        return '$ '.number_format($valor,$n_decimales,',','.');

    }

	/************************************************************************************************************/
	/**
     * Redondea un valor flotante al entero más cercano.
     *
     * @param mixed $valor El número que se desea redondear.
     * @return string|float|int El valor redondeado o un mensaje de error.
     * * @example $DataNumbers->valoresEnteros(1500.85); // Devuelve 1501
     */
    public function valoresEnteros($valor): string | float | int {
        /*
        *=================================================    Modo de uso  =================================================
        *
        * //se formatea numero
        * $DataNumbers->valoresEnteros(1500.85); //Devuelve 1501
        *
        *===================================================================================================================
        */

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateValue($valor);
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

        /**********************  Retorno datos  **********************/
        // Utiliza la función round para redondear al entero superior o inferior más próximo
        return round($valor);

    }

	/************************************************************************************************************/
	/**
     * Redondea un valor decimal hacia arriba (techo) al siguiente entero.
     *
     * @param mixed $valor El número a procesar.
     * @return string|float|int El valor redondeado hacia arriba o un mensaje de error.
     * * @example $DataNumbers->valoresComparables(1500.85); // Devuelve 1501
     */
    public function valoresComparables($valor): string | float | int {
        /*
        *=================================================    Modo de uso  =================================================
        *
        * //se formatea numero
        * $DataNumbers->valoresComparables(1500.85); //Devuelve 1501
        *
        *===================================================================================================================
        */

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateValue($valor);
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

        /**********************  Retorno datos  **********************/
        // Aplica la función ceil para obtener el entero inmediato superior
        return ceil($valor);

    }

	/************************************************************************************************************/
	/**
     * Trunca un valor numérico eliminando su parte decimal (redondeo hacia abajo).
     *
     * @param mixed $valor El número a truncar.
     * @return string|float|int El entero resultante o un mensaje de error.
     * * @example $DataNumbers->valoresTruncados(1500.85); // Devuelve 1500
     */
    public function valoresTruncados($valor): string | float | int {
        /*
        *=================================================    Modo de uso  =================================================
        *
        * //se formatea numero
        * $DataNumbers->valoresTruncados(1500.85); //Devuelve 1500
        *
        *===================================================================================================================
        */

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateValue($valor);
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

        /**********************  Retorno datos  **********************/
        // Aplica la función floor para descartar los decimales sin redondear hacia arriba
        return floor($valor);

    }

	/************************************************************************************************************/
	/**
     * Formatea un número mostrando únicamente los decimales significativos existentes,
     * con un límite máximo de 6 dígitos decimales.
     *
     * @param mixed $valor El número a formatear.
     * @return string|float|int El número redondeado a sus decimales reales (máx 6) o mensaje de error.
     * @example $DataNumbers->cantidadesDecimalesJustos(1500.85000); // Devuelve 1500.85
     */
    public function cantidadesDecimalesJustos($valor): string | float | int {
        /*
        *=================================================    Modo de uso  =================================================
        *
        * //se formatea numero
        * $DataNumbers->cantidadesDecimalesJustos(1500.85000); //Devuelve 1500.85
        *
        *===================================================================================================================
        */

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateValue($valor);
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

        /********************** Si todo esta ok **********************/
        // Conversión a cadena para análisis de posición de caracteres
        $valor_str = (string) $valor;

        // Calcula la cantidad de dígitos después del punto decimal
        // substr(strrchr()) extrae la parte decimal; strlen cuenta su longitud
        $dec = strlen(substr(strrchr($valor_str, '.'), 1));

        // Establece un techo técnico de 6 decimales para el redondeo
        if ($dec >= 6) {$dec = 6;}

        /**********************  Retorno datos  **********************/
        // Retorna el valor redondeado a la precisión calculada dinámicamente
        return round($valor, $dec);

    }

	/************************************************************************************************************/
	/**
     * Prepara un valor numérico para ser compatible con el formato de celdas de Excel
     * (Localización ES), sustituyendo el punto decimal por coma.
     *
     * @param mixed $valor El número original con punto decimal.
     * @return string|float|int El valor con formato de coma decimal o "0".
     * @example $DataNumbers->cantidadesExcel(1500.85); // Devuelve "1500,85"
     */
    public function cantidadesExcel($valor): string | float | int {
        /*
        *=================================================    Modo de uso  =================================================
        *
        * //se formatea numero
        * $DataNumbers->cantidadesExcel(1500.85); //Devuelve 1500.85
        *
        *===================================================================================================================
        */

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateValue($valor);
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

        /**********************  Retorno datos  **********************/
        // Reemplaza el separador decimal estándar (.) por el formato usado frecuentemente en Excel ES (,)
        return str_replace('.', ',', $valor);

    }

	/************************************************************************************************************/
	/**
     * Normaliza un valor numérico para entornos de Google (Sheets/Cloud) asegurando
     * el uso del punto como separador decimal.
     *
     * @param mixed $valor El valor numérico que puede contener comas.
     * @return string|float|int El valor con punto decimal o mensaje de error.
     * @example $DataNumbers->cantidadesGoogle(1500.85); // Devuelve 1500.85
     */
    public function cantidadesGoogle($valor): string | float | int {
        /*
        *=================================================    Modo de uso  =================================================
        *
        * //se formatea numero
        * $DataNumbers->cantidadesGoogle(1500.85); //Devuelve 1500.85
        *
        *===================================================================================================================
        */

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateValue($valor);
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

        /**********************  Retorno datos  **********************/
        // Asegura que el separador decimal sea un punto, reemplazando comas si existieran
        return str_replace(',', '.', $valor);

    }

	/************************************************************************************************************/
	/**
     * Aplica un formato visual de máscara telefónica a una cadena de números.
     *
     * @param string $Phone Cadena con el número de teléfono.
     * @return string Teléfono formateado como "(+XX) X XXXX XXXX" o mensaje de error.
     * @example $DataNumbers->formatPhone('+56911265984'); // Devuelve "(+56) 9 1126 5984"
     */
    public function formatPhone($Phone): string{
        /*
        *=================================================    Modo de uso  =================================================
        *
        * //se formatea numero
        * $DataNumbers->formatPhone('+56911265984'); //Devuelve (+56) 9 1126 5984
        *
        *===================================================================================================================
        */

		/**********************  Validaciones   **********************/
        // Validaciones de existencia y longitud mínima/máxima
        if(!isset($Phone) || $Phone==''){ return 'Sin datos ingresados';}
        if(strlen($Phone)<=7){            return 'Numero demasiado corto, tiene '.strlen($Phone).' numeros y debe tener al menos 9';}
        if(strlen($Phone)>=13){           return 'Numero demasiado largo, tiene '.strlen($Phone).' numeros y debe tener no mas de 11';}

		/********************** Si todo esta ok **********************/
        // Llama a la función interna para estandarizar el prefijo y limpiar caracteres
        $myPhone = $this->normalizarPhone($Phone);

		/**********************  Retorno datos  **********************/
        // Divide la cadena normalizada en bloques usando substr para aplicar la máscara final
        return sprintf("(%s) %s %s %s",
            substr($myPhone, 0, 3),
            substr($myPhone, 3, 1),
            substr($myPhone, 4, 4),
            substr($myPhone, 8, 4));

    }

	/************************************************************************************************************/
	/**
     * Estandariza un número de teléfono al formato internacional chileno (+56).
     *
     * @param string $Phone El número de teléfono en diversos posibles formatos.
     * @return string El número normalizado comenzando con +56.
     */
    public function normalizarPhone($Phone): string{
        /*
        *=================================================    Modo de uso  =================================================
        *
        * //se formatea numero
        * $DataNumbers->normalizarPhone('+56911265984'); //Devuelve +56 9 1126 5984
        *
        *===================================================================================================================
        */

        /**********************  Validaciones   **********************/
        // Validaciones de longitud y presencia de datos
        if(!isset($Phone) || $Phone==''){ return 'Sin datos ingresados';}
        if(strlen($Phone)<=7){            return 'Numero demasiado corto, tiene '.strlen($Phone).' numeros y debe tener al menos 9';}
        if(strlen($Phone)>=13){           return 'Numero demasiado largo, tiene '.strlen($Phone).' numeros y debe tener no mas de 11';}

        /********************** Si todo esta ok **********************/
        // Corrección para números de 8 dígitos (formato antiguo) anteponiendo el dígito 9
        if(strlen($Phone)==8){$Phone = '9'.$Phone;}

        $myNumber = $Phone;
        // Remueve cualquier carácter que no sea un dígito numérico
        $Phone = preg_replace('/\D/', '', $myNumber);

        // Lógica condicional para determinar cómo aplicar el prefijo +56 según el inicio de la cadena original
        if (strpos($myNumber, '+56') === 0) {
            $myPhone = '+56' . substr($Phone, 2);
        } elseif (strpos($myNumber, '56') === 0) {
            $myPhone = '+56' . substr($Phone, 2);
        } elseif (strpos($myNumber, '+') === 0) {
            $myPhone = $myNumber;
        } else {
            // Si no tiene prefijo internacional detectado, se asume local y se agrega +56
            $myPhone = '+56' . $Phone;
        }

        /**********************  Retorno datos  **********************/
        // Retornar numero fono normalizado
        return $myPhone;

    }

	/************************************************************************************************************/
	/**
     * Agrega un cero a la izquierda para números naturales menores a 10.
     *
     * @param mixed $valor El número a evaluar.
     * @return string El número formateado con dos dígitos (ej: "01") o el original.
     * @example $DataNumbers->numberInit0(1); // Devuelve "01"
     */
    public function numberInit0($valor): string{
        /*
        *=================================================    Modo de uso  =================================================
        *
        * //se formatea numero
        * $DataNumbers->numberInit0(1); //Devuelve 01
        *
        *===================================================================================================================
        */

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateValue($valor);
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

        /**********************  Retorno datos  **********************/
        // Evaluación por operador ternario: si es menor a 10, concatena el carácter "0"
        return ($valor<10) ? '0'.$valor : $valor;

    }


	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Internos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
    /************************************************************************************************************/
	private function _validateValue($Data){

		/**********************  Validaciones   **********************/
        // Retorno inmediato si el valor es nulo, cadena vacía o numéricamente cero
        if ($Data=='' || $Data==0) {   return '0'; }
        // Validación de tipos de datos mediante el componente externo DataValidations
        if (!$this->DataValidations->validarNumero($Data)) {
            return 'El dato ingresado no es un numero ('.$Data.')';
        }

		/**********************  Retorno datos  **********************/
		return true;

	}
    /************************************************************************************************************/
	private function _validateInteger($Data){

		/**********************  Validaciones   **********************/
        // Retorno inmediato si el valor es nulo, cadena vacía o numéricamente cero
        if ($Data=='' || $Data==0) { return 'Sin datos ingresados'; }
        // Validación de tipos de datos mediante el componente externo DataValidations
        if (!$this->DataValidations->validarNumero($Data) || !$this->DataValidations->validarEntero($Data)) {
            return 'El dato ingresado no es un numero ('.$Data.')';
        }

		/**********************  Retorno datos  **********************/
		return true;

	}


}
