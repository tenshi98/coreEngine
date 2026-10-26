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
	/******************************************************************************/
	//Definiciones
	private $DataValidations;

	/******************************************************************************/
    /**
	 * Instancia las dependencias internas de la clase de números.
	 *
	 * Carga FunctionsDataValidations para validar números y enteros antes de
	 * formatearlos o redondearlos.
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * $DataNumbers = new FunctionsDataNumbers();
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
     * Formatea un valor numérico como moneda, anteponiendo el símbolo de peso ($)
     * y aplicando formato de miles y decimales.
     *
     * @param mixed $valor El monto numérico a formatear.
     * @param int $n_decimales Cantidad de decimales requeridos.
     *
     * @return string Representación monetaria del valor (ej: "$ 1.500,86") o mensaje de error de validación.
	 *
	 * @example
	 * ```php
	 * $DataNumbers->Valores(1500.85565, 2); // Devuelve "$ 1.500,86"
	 * ```
	 *
     */
    public function Valores($valor, $n_decimales): string{

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateValue($valor, 'valor');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }

		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_2 = $this->_validateInteger($n_decimales, 'n_decimales');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_2 !== true) { return $dataVal_2; }

        /**********************  Retorno datos  **********************/
        // Concatena el símbolo de peso con el número formateado (punto para miles, coma para decimales)
        return '$ '.number_format($valor,$n_decimales,',','.');

    }

    /******************************************************************************/
    /**
     * Convierte un valor numérico a un formato de cadena con separadores de miles (punto)
     * y decimales (coma), rellenando con ceros según la precisión indicada.
     *
     * @param mixed $valor El número original que se desea formatear.
     * @param int $n_decimales Cantidad de dígitos decimales que se deben mostrar.
     *
     * @return string El número formateado o un mensaje de error si la validación falla.
	 *
	 * @example
	 * ```php
	 * $DataNumbers->Cantidades(1250.85, 6); // Devuelve "1.250,850000"
	 * ```
	 *
     */
    public function Cantidades($valor, $n_decimales): string{

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateValue($valor, 'valor');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }

		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_2 = $this->_validateInteger($n_decimales, 'n_decimales');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_2 !== true) { return $dataVal_2; }

        /**********************  Retorno datos  **********************/
        // Formatea el número utilizando punto para miles y coma para decimales
        return number_format($valor,$n_decimales,',','.');

    }

    /******************************************************************************/
    /**
     * Formatea un número mostrando únicamente los decimales significativos existentes,
     * con un límite máximo de 6 dígitos decimales.
     *
     * @param mixed $valor El número a formatear.
     *
     * @return string|float|int El número redondeado a sus decimales reales (máx 6) o mensaje de error.
	 *
	 * @example
	 * ```php
	 * $DataNumbers->cantidadesDecimalesJustos(1500.85000); // Devuelve 1500.85
	 * ```
	 *
     */
    public function cantidadesDecimalesJustos($valor): string | float | int {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateValue($valor, 'valor');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

        /********************** Si todo esta ok **********************/
        // Conversión a cadena para análisis de posición de caracteres
        // Se usa notación decimal fija para evitar notación científica (p.ej. 1.0E+20)
        $valor_str = sprintf('%.10F', (float) $valor);
        // Elimina ceros finales y posible punto sobrante antes de contar decimales
        $valor_str = rtrim(rtrim($valor_str, '0'), '.');

        // Calcula la cantidad de dígitos después del punto decimal
        $posPunto = strpos($valor_str, '.');
        $dec = ($posPunto === false) ? 0 : strlen(substr($valor_str, $posPunto + 1));

        // Establece un techo técnico de 6 decimales para el redondeo
        if ($dec >= 6) {$dec = 6;}

        /**********************  Retorno datos  **********************/
        // Retorna el valor redondeado a la precisión calculada dinámicamente
        return round($valor, $dec);

    }

	/******************************************************************************/
    /**
     * Prepara un valor numérico para ser compatible con el formato de celdas de Excel
     * (Localización ES), sustituyendo el punto decimal por coma.
     *
     * @param mixed $valor El número original con punto decimal.
     *
     * @return string|float|int El valor con formato de coma decimal, '0' cuando no hay datos o mensaje de error si no es numérico.
	 *
	 * @example
	 * ```php
	 * $DataNumbers->cantidadesExcel(1500.85); // Devuelve "1500,85"
	 * ```
	 *
     */
    public function cantidadesExcel($valor): string | float | int {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateValue($valor, 'valor');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

        /**********************  Retorno datos  **********************/
        // Reemplaza el separador decimal estándar (.) por el formato usado frecuentemente en Excel ES (,)
        return str_replace('.', ',', $valor);

    }

    /******************************************************************************/
    /**
     * Normaliza un valor numérico para entornos de Google (Sheets/Cloud) asegurando
     * el uso del punto como separador decimal.
     *
     * @param mixed $valor El valor numérico que puede contener comas.
     *
     * @return string|float|int El valor con punto decimal o mensaje de error.
	 *
	 * @example
	 * ```php
	 * $DataNumbers->cantidadesGoogle(1500.85); // Devuelve 1500.85
	 * ```
	 *
     */
    public function cantidadesGoogle($valor): string | float | int {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateValue($valor, 'valor');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

        /**********************  Retorno datos  **********************/
        // Asegura que el separador decimal sea un punto, reemplazando comas si existieran
        return str_replace(',', '.', $valor);

    }

	/******************************************************************************/
    /**
     * Formatea un número como un correlativo de documento agregando ceros a la izquierda
     * hasta alcanzar la longitud deseada.
     *
     * @param mixed $valor El número de documento original.
     * @param int $n_ceros Longitud total deseada de la cadena resultante.
     *
     * @return string El número paddeado con ceros o un mensaje de error.
	 *
	 * @example
	 * ```php
	 * $DataNumbers->nDoc(25, 7); // Devuelve "0000025"
	 * ```
	 *
     */
    public function nDoc($valor, $n_ceros): string{

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateInteger($valor, 'valor');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }

		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_2 = $this->_validateInteger($n_ceros, 'n_ceros');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_2 !== true) { return $dataVal_2; }

        /**********************  Retorno datos  **********************/
        // Rellena la cadena por la izquierda con el carácter "0" hasta completar n_ceros
        return str_pad($valor, $n_ceros, "0", STR_PAD_LEFT);

    }

    /******************************************************************************/
    /**
     * Agrega un cero a la izquierda para números naturales menores a 10.
     *
     * @param mixed $valor El número a evaluar.
     *
     * @return string El número formateado con dos dígitos (ej: "01"), el valor original si es mayor o igual a 10 o mensaje de error de validación.
	 *
	 * @example
	 * ```php
	 * $DataNumbers->numberInit0(1); // Devuelve 01
	 * ```
	 *
     */
    public function numberInit0($valor): string{

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateValue($valor, 'valor');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

        /**********************  Retorno datos  **********************/
        // Evaluación por operador ternario: solo naturales de un dígito llevan cero inicial
        if ($valor >= 0 && $valor < 10) { return '0'.$valor; }
        return (string) $valor;

    }

	/******************************************************************************/
    /**
     * Redondea un valor flotante al entero más cercano.
     *
     * @param mixed $valor El número que se desea redondear.
     *
     * @return string|float|int El valor redondeado o un mensaje de error.
	 *
	 * @example
	 * ```php
	 * $DataNumbers->valoresEnteros(1500.85); // Devuelve 1501
	 * ```
	 *
     */
    public function valoresEnteros($valor): string | float | int {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateValue($valor, 'valor');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

        /**********************  Retorno datos  **********************/
        // Utiliza la función round para redondear al entero superior o inferior más próximo
        return round($valor);

    }

    /******************************************************************************/
    /**
     * Redondea un valor decimal hacia arriba (techo) al siguiente entero.
     *
     * @param mixed $valor El número a procesar.
     *
     * @return string|float|int El valor redondeado hacia arriba o un mensaje de error.
	 *
	 * @example
	 * ```php
	 * $DataNumbers->valoresComparables(1500.85); // Devuelve 1501
	 * ```
	 *
     */
    public function valoresComparables($valor): string | float | int {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateValue($valor, 'valor');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

        /**********************  Retorno datos  **********************/
        // Aplica la función ceil para obtener el entero inmediato superior
        return ceil($valor);

    }

    /******************************************************************************/
    /**
     * Trunca un valor numérico eliminando su parte decimal (redondeo hacia abajo).
     *
     * @param mixed $valor El número a truncar.
     *
     * @return string|float|int El entero resultante o un mensaje de error.
	 *
	 * @example
	 * ```php
	 * $DataNumbers->valoresTruncados(1500.85); // Devuelve 1500
	 * ```
	 *
     */
    public function valoresTruncados($valor): string | float | int {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateValue($valor, 'valor');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

        /**********************  Retorno datos  **********************/
        // Aplica la función floor para descartar los decimales sin redondear hacia arriba
        return floor($valor);

    }

    /******************************************************************************/
    /**
     * Redondea un número con el modo y la precisión indicados.
     *
     * Complementa a valoresTruncados (que solo descarta decimales) con los
     * modos de redondeo nativos de PHP: half_up (por defecto), half_down,
     * half_even, up (hacia +infinito) y down (hacia -infinito/truncar).
     *
     * @param float $Numero Número a redondear.
     * @param int $Decimales Cantidad de decimales (entre 0 y 10).
     * @param string $Modo Modo de redondeo: half_up|half_down|half_even|up|down.
     *
     * @return float|string Número redondeado; mensaje de error si los parámetros no son válidos.
     *
     * @example
     * ```php
     * $DataNumbers->redondear(2.5);                 // Devuelve 3
     * $DataNumbers->redondear(2.345, 2);            // Devuelve 2.35
     * $DataNumbers->redondear(2.345, 2, 'down');    // Devuelve 2.34
     * $DataNumbers->redondear(2.5, 0, 'half_even'); // Devuelve 2
     * ```
     *
     */
    public function redondear(float $Numero, int $Decimales = 0, string $Modo = 'half_up'): float | string {

        /********************** Validaciones   **********************/
        // Control de rango de decimales permitidos
        if ($Decimales < 0 || $Decimales > 10) {
            return 'El dato ingresado no es un decimal valido ('.$Decimales.')';
        }
        // Control de los modos de redondeo soportados
        $Modos = ['half_up', 'half_down', 'half_even', 'up', 'down'];
        if (!in_array($Modo, $Modos, true)) {
            return 'El dato ingresado no es un modo de redondeo valido ('.$Modo.')';
        }

        /********************** Si todo esta ok **********************/
        // Escala auxiliar para los modos manuales up/down con decimales
        $Escala = pow(10, $Decimales);

        /**********************  Retorno datos  **********************/
        // Selección del modo de redondeo solicitado
        switch ($Modo) {
            case 'up':       return ceil($Numero * $Escala) / $Escala;
            case 'down':     return floor($Numero * $Escala) / $Escala;
            case 'half_down':return round($Numero, $Decimales, PHP_ROUND_HALF_DOWN);
            case 'half_even':return round($Numero, $Decimales, PHP_ROUND_HALF_EVEN);
            default:         return round($Numero, $Decimales, PHP_ROUND_HALF_UP);
        }

    }

	/******************************************************************************/
    /**
     * Aplica un formato visual de máscara telefónica a una cadena de números.
     *
     * @param string $Fono Cadena con el número de teléfono.
     *
     * @return string Teléfono formateado como "(+XX) X XXXX XXXX" o mensaje de error.
	 *
	 * @example
	 * ```php
	 * $DataNumbers->formatPhone('+56911265984'); // Devuelve (+56) 9 1126 5984
	 * ```
	 *
     */
    public function formatPhone($Fono): string{

		/**********************  Validaciones   **********************/
        // Validaciones de existencia y longitud mínima/máxima
        if ($Fono === null || trim((string)$Fono) === '') {  return 'Sin datos ingresados en Fono';}
        if (strlen($Fono)<=7) {                              return 'Numero demasiado corto, tiene '.strlen($Fono).' numeros y debe tener al menos 8';}
        if (strlen($Fono)>=13) {                             return 'Numero demasiado largo, tiene '.strlen($Fono).' numeros y debe tener no mas de 12';}

		/********************** Si todo esta ok **********************/
        // Llama a la función interna para estandarizar el prefijo y limpiar caracteres
        $miFono = $this->normalizarPhone($Fono);

		// Si el normalizado no es chileno (+56) o es un mensaje de error, se devuelve sin mascara chilena
		if (strpos($miFono, '+56') !== 0) { return $miFono; }

		/**********************  Retorno datos  **********************/
        // Divide la cadena normalizada en bloques usando substr para aplicar la máscara final
        return sprintf("(%s) %s %s %s",
            substr($miFono, 0, 3),
            substr($miFono, 3, 1),
            substr($miFono, 4, 4),
            substr($miFono, 8, 4));

    }

    /******************************************************************************/
    /**
     * Estandariza un número de teléfono al formato internacional chileno (+56).
     *
     * @param string $Fono El número de teléfono en diversos posibles formatos.
     *
     * @return string El número normalizado comenzando con +56 o mensaje de error de validación.
	 *
	 * @example
	 * ```php
	 * $DataNumbers->normalizarPhone('+56911265984'); // Devuelve +56911265984
	 * ```
	 *
     */
    public function normalizarPhone($Fono): string{

        /**********************  Validaciones   **********************/
        // Validaciones de longitud y presencia de datos
        if ($Fono === null || trim((string)$Fono) === '') {  return 'Sin datos ingresados en Fono';}
        if (strlen($Fono)<=7) {                              return 'Numero demasiado corto, tiene '.strlen($Fono).' numeros y debe tener al menos 8';}
        if (strlen($Fono)>=13) {                             return 'Numero demasiado largo, tiene '.strlen($Fono).' numeros y debe tener no mas de 12';}

        /********************** Si todo esta ok **********************/
        // Corrección para números de 8 dígitos (formato antiguo) anteponiendo el dígito 9
        if(strlen($Fono)==8){$Fono = '9'.$Fono;}

        $myNumber = $Fono;
        // Remueve cualquier carácter que no sea un dígito numérico
        $Fono = preg_replace('/\D/', '', $myNumber);

        // Lógica condicional para determinar cómo aplicar el prefijo +56 según el inicio de la cadena original
        if (strpos($myNumber, '+56') === 0) {
            $miFono = '+56' . substr($Fono, 2);
        } elseif (strpos($myNumber, '56') === 0) {
            $miFono = '+56' . substr($Fono, 2);
        } elseif (strpos($myNumber, '+') === 0) {
            $miFono = $myNumber;
        } else {
            // Si no tiene prefijo internacional detectado, se asume local y se agrega +56
            $miFono = '+56' . $Fono;
        }

        /**********************  Retorno datos  **********************/
        // Retornar numero fono normalizado
        return $miFono;

    }

	/******************************************************************************/
    /**
     * Calcula el promedio (media aritmética) de un conjunto de valores numéricos.
     *
     * @param array $Valores Arreglo de números (enteros, decimales o cadenas numéricas).
     *
     * @return float|string Promedio; mensaje de error si el arreglo está vacío o contiene no numéricos.
     *
     * @example
     * ```php
     * $DataNumbers->promedio([2, 4, 6, 8]); // Devuelve 5
     * $DataNumbers->promedio([]);           // Devuelve Sin datos ingresados en Valores
     * ```
     *
     */
    public function promedio(array $Valores): float | string {

        /********************** Validaciones   **********************/
        // Guarda: sin valores no es posible calcular la media
        if (count($Valores) === 0) {
            return 'Sin datos ingresados en Valores';
        }

        /********************** Si todo esta ok **********************/
        // Acumulador de la suma y contador de elementos válidos
        $Suma   = 0;
        $Conteo = 0;
        foreach ($Valores as $Valor) {
            // Todo elemento debe ser numérico (se rechaza el arreglo completo si no lo es)
            if (!is_numeric($Valor)) {
                return 'El dato ingresado no es un numero ('.(string)$Valor.')';
            }
            $Suma   += (float)$Valor;
            $Conteo++;
        }

        /**********************  Retorno datos  **********************/
        return $Suma / $Conteo;

    }

    /******************************************************************************/
    /**
     * Calcula la mediana de un conjunto de valores numéricos.
     *
     * Ordena los valores y devuelve el elemento central; cuando la cantidad es
     * par, devuelve el promedio de los dos elementos centrales.
     *
     * @param array $Valores Arreglo de números (enteros, decimales o cadenas numéricas).
     *
     * @return float|string Mediana; mensaje de error si el arreglo está vacío o contiene no numéricos.
     *
     * @example
     * ```php
     * $DataNumbers->mediana([5, 1, 3]);   // Devuelve 3
     * $DataNumbers->mediana([4, 1, 3, 2]); // Devuelve 2.5
     * $DataNumbers->mediana('a');          // Devuelve El dato ingresado no es un arreglo (a)
     * ```
     *
     */
    public function mediana(array $Valores): float | string {

        /********************** Validaciones   **********************/
        // Guarda: sin valores no es posible calcular la mediana
        if (count($Valores) === 0) {
            return 'Sin datos ingresados en Valores';
        }

        /********************** Si todo esta ok **********************/
        // Copia numérica ordenada de los valores de entrada
        $Numeros = [];
        foreach ($Valores as $Valor) {
            // Todo elemento debe ser numérico (se rechaza el arreglo completo si no lo es)
            if (!is_numeric($Valor)) {
                return 'El dato ingresado no es un numero ('.(string)$Valor.')';
            }
            $Numeros[] = (float)$Valor;
        }
        // Orden ascendente para ubicar el elemento central
        sort($Numeros, SORT_NUMERIC);
        $n     = count($Numeros);
        $Mitad = intdiv($n, 2);

        /**********************  Retorno datos  **********************/
        // Cantidad impar: elemento central; par: promedio de los dos centrales
        if ($n % 2 === 1) {
            return $Numeros[$Mitad];
        }
        return ($Numeros[$Mitad - 1] + $Numeros[$Mitad]) / 2;

    }

    /******************************************************************************/
    /**
     * Genera una cadena de dígitos aleatoria criptográficamente segura.
     *
     * Utiliza random_int (CSPRNG) en lugar de rand/mt_rand, por lo que es
     * apta para códigos de verificación. Los ceros a la izquierda se
     * conservan para respetar la longitud solicitada.
     *
     * @param int $Digitos Cantidad de dígitos (entre 1 y 32).
     *
     * @return string Cadena numérica aleatoria; mensaje de error si la longitud está fuera de rango.
     *
     * @example
     * ```php
     * $DataNumbers->numeroAleatorio(6); // Devuelve p.ej. "048213" (6 dígitos exactos)
     * $DataNumbers->numeroAleatorio(0); // Devuelve El dato ingresado no es un numero de digitos valido (0)
     * ```
     *
     */
    public function numeroAleatorio(int $Digitos = 6): string {

        /********************** Validaciones   **********************/
        // Control de la longitud solicitada
        if ($Digitos < 1 || $Digitos > 32) {
            return 'El dato ingresado no es un numero de digitos valido ('.$Digitos.')';
        }

        /********************** Si todo esta ok **********************/
        // Generación dígito a dígito para soportar longitudes mayores al rango de PHP_INT
        $Cadena = '';
        for ($i = 0; $i < $Digitos; $i++) {
            $Cadena .= (string)random_int(0, 9);
        }

        /**********************  Retorno datos  **********************/
        return $Cadena;

    }

    /*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Privados                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
    /**
	 * Valida que el dato recibido sea un valor numérico no vacío.
	 *
	 * Devuelve true cuando el dato es numérico; si está vacío o es nulo retorna
	 * el string '0' (valor por defecto para ausencia de datos) y si no es
	 * numérico retorna el mensaje de error con el nombre del campo.
	 *
	 * @param mixed  $Data Dato a validar (número o cadena numérica).
	 * @param string $Name Nombre del campo usado en los mensajes de error.
	 *
	 * @return true|string true si el dato es numérico, '0' si está vacío o mensaje de error.
	 *
	 * @example
	 * ```php
	 * $this->_validateValue(1500.85, 'valor'); // Devuelve true
	 * $this->_validateValue('', 'valor');      // Devuelve '0'
	 * $this->_validateValue('a', 'valor');     // Devuelve 'El dato ingresado en valor no es un numero (a)'
	 * ```
	 */
	private function _validateValue($Data, $Name){

		/**********************  Validaciones   **********************/
        // Retorno inmediato si el valor es nulo, cadena vacía o numéricamente cero
        if ($Data === null || trim((string)$Data) === '') {
            return '0';
        }
        // Validación de tipos de datos mediante el componente externo DataValidations
        if (!$this->DataValidations->validarNumero($Data)) {
            return 'El dato ingresado en '.$Name.' no es un numero ('.$Data.')';
        }

		/**********************  Retorno datos  **********************/
		return true;

	}

    /******************************************************************************/
    /**
	 * Valida que el dato recibido sea un número entero no vacío.
	 *
	 * Comprueba que el dato no esté vacío, que sea numérico y que sea entero;
	 * en caso contrario retorna el mensaje de error con el nombre del campo.
	 *
	 * @param mixed  $Data Dato a validar (entero o cadena numérica entera).
	 * @param string $Name Nombre del campo usado en los mensajes de error.
	 *
	 * @return true|string true si el dato es entero o mensaje de error.
	 *
	 * @example
	 * ```php
	 * $this->_validateInteger(7, 'n_ceros');  // Devuelve true
	 * $this->_validateInteger('a', 'n_ceros'); // Devuelve 'El dato ingresado en n_ceros no es un numero (a)'
	 * $this->_validateInteger('', 'n_ceros');  // Devuelve 'Sin datos ingresados en n_ceros'
	 * ```
	 */
	private function _validateInteger($Data, $Name){

		/**********************  Validaciones   **********************/
        // Retorno inmediato si el valor es nulo, cadena vacía o numéricamente cero
        if ($Data === null || trim((string)$Data) === '') {
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
