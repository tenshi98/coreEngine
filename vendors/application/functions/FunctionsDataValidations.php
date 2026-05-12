<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsDataValidations {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	/**
     * Valida si una cadena de texto corresponde a un RUT chileno válido.
     * * El proceso incluye la limpieza de puntos, validación de formato mediante expresiones
     * regulares y el cálculo del dígito verificador utilizando el algoritmo del Módulo 11.
     *
     * @param string $Data El RUT a validar (ej: '12.345.678-9' o '12345678-9').
     *
     * @return bool True si el RUT es válido, false en caso contrario.
	 *
	 * @example
	 * ```php
	 * $DataValidations->validarRut('10.569.874-5');
	 * ```
	 *
     */
    public function validarRut($Data): bool {

        /********************** Validaciones Iniciales **********************/
        if($Data == '' || $Data == '0'){ return false; }

        /********************** Limpieza y Formateo **********************/
        // Elimina puntos para normalizar la cadena
        $rut = str_replace('.', '', $Data);

        // Verifica longitud mínima (un RUT válido tiene al menos 3 caracteres: 1-k)
        if (empty($rut) || strlen($rut) < 3) {
            return false;
        }

        // Separa la parte numérica del guion y dígito verificador
        $parteNumerica = str_replace(substr($rut, -2, 2), '', $rut);

        // Valida que la parte izquierda sean solo dígitos
        if (!preg_match("/^[0-9]*$/", $parteNumerica)) {
            return false;
        }

        $guionYVerificador = substr($rut, -2, 2);

        // El formato debe terminar estrictamente en "-X" donde X es 0-9 o K
        if (strlen($guionYVerificador) != 2 || !preg_match('/(^[-]{1}+[0-9kK]).{0}$/', $guionYVerificador)) {
            return false;
        }

        /********************** Algoritmo Módulo 11 **********************/
        // Prepara la cadena eliminando guiones y puntos para el cálculo
        $rutV   = preg_replace('/[\.\-]/i', '', $rut);
        $dv     = substr($rutV, -1);
        $numero = substr($rutV, 0, strlen($rutV) - 1);

        $i      = 2;
        $suma   = 0;

        // Multiplicación por serie 2,3,4,5,6,7 y suma
        foreach (array_reverse(str_split($numero)) as $v) {
            if ($i == 8) { $i = 2; }
            $suma += $v * $i;
            ++$i;
        }

        // Cálculo del dígito esperado
        $dvr = 11 - ($suma % 11);
        if ($dvr == 11) { $dvr = 0; }
        if ($dvr == 10) { $dvr = 'K'; }

        /********************** Retorno de Datos **********************/
        // Compara el dígito calculado con el ingresado
        return ($dvr == strtoupper($dv));
    }

	/************************************************************************************************************/
	/**
     * Valida si una cadena de texto tiene un formato de correo electrónico válido.
     * * Utiliza el filtro nativo de PHP FILTER_VALIDATE_EMAIL, que cumple con
     * gran parte de los estándares RFC.
     *
     * @param string $Data Correo electrónico a validar.
     *
     * @return bool True si el formato es correcto.
	 *
	 * @example
	 * ```php
	 * $DataValidations->validarEmail('asd@asd.cl'); //Devuelve true
	 * $DataValidations->validarEmail('asd@asd');    //Devuelve false
	 * ```
	 *
     */
    public function validarEmail($Data): bool {

        /**********************  Validaciones   **********************/
        if($Data == ''){ return false; }

        /********************** Retorno de Datos **********************/
        return (bool) filter_var($Data, FILTER_VALIDATE_EMAIL);
    }

	/************************************************************************************************************/
	/**
     * Valida si el dato ingresado es un valor numérico.
     * * Acepta números enteros, decimales (usando punto o coma) y valores negativos.
     *
     * @param mixed $Data Dato a validar.
     *
     * @return bool True si es un número válido.
	 *
	 * @example
	 * ```php
	 * $DataValidations->validarNumero(25);   //Devuelve true
	 * $DataValidations->validarNumero('25'); //Devuelve false
	 * ```
	 *
     */
    public function validarNumero($Data): bool {

        /**********************  Validaciones   **********************/
        if($Data === ''){ return false; }

        /********************** Normalización **********************/
        // Reemplaza comas por puntos para que is_numeric reconozca el formato decimal estándar
        $number = str_replace(',', '.', $Data);

        /********************** Retorno de Datos **********************/
        return is_numeric($number);
    }

	/************************************************************************************************************/
	/**
     * Valida si una cadena corresponde al formato de una patente vehicular chilena.
     * * Soporta tanto el formato antiguo (AA-1234) como el formato nuevo (BB-CC-12),
     * validando que no se utilicen vocales en el formato nuevo según la norma.
     *
     * @param string $Data Patente a validar.
     *
     * @return bool True si cumple con el patrón RegEx.
	 *
	 * @example
	 * ```php
	 * $DataValidations->ValidarPatente('AU1825');  //Devuelve true
	 * $DataValidations->ValidarPatente('512369');  //Devuelve false
	 * ```
	 *
     */
    public function ValidarPatente($Data): bool {

        /**********************  Validaciones   **********************/
        if($Data == ''){ return false; }

        /********************** Limpieza **********************/
        $patente = str_replace("-", "", $Data);

        // RegEx para:
        // 1. Formato Antiguo: 2 letras + 4 números
        // 2. Formato Nuevo: 4 consonantes (sin vocales) + 2 números
        $regex = '/^[a-z]{2}[\.\- ]?[0-9]{2}[\.\- ]?[0-9]{2}|[b-d,f-h,j-l,p,r-t,v-z]{2}[\-\. ]?[b-d,f-h,j-l,p,r-t,v-z]{2}[\.\- ]?[0-9]{2}$/i';

        /********************** Retorno de Datos **********************/
        return (bool) preg_match($regex, $patente);
    }

	/************************************************************************************************************/
	/**
     * Valida si una cadena de texto es una URL con formato válido.
     *
     * @param string $Data URL a validar.
     *
     * @return bool True si es una URL válida (incluyendo protocolo).
	 *
	 * @example
	 * ```php
	 * $DataValidations->validarURL(https://www.google.cl');  //Devuelve true
	 * $DataValidations->validarURL(https://www.  SSS  ');    //Devuelve false
	 * ```
	 *
     */
    public function validarURL($Data): bool {

        /**********************  Validaciones   **********************/
        if($Data == ''){ return false; }

        /********************** Retorno de Datos **********************/
        return (bool) filter_var($Data, FILTER_VALIDATE_URL);
    }

	/************************************************************************************************************/
	/**
     * Valida si una cadena representa una hora válida en formato H:M o H:M:S.
     * * Permite un rango de horas extendido (hasta 999) útil para cronómetros o
     * sumatoria de tiempos, validando que los minutos y segundos no excedan de 59.
     *
     * @param string $Data Hora a validar (ej: '16:24:00' o '120:30').
     *
     * @return bool True si el formato y los valores son correctos.
	 *
	 * @example
	 * ```php
	 * $DataValidations->validarHora('16:24:00'); //Devuelve true
	 * $DataValidations->validarHora(16);         //Devuelve false
	 * ```
	 *
     */
    public function validarHora($Data): bool {

        /**********************  Validaciones   **********************/
        // Limpia espacios en blanco al inicio y final
        // (muy común cuando los datos vienen de formularios o BD)
        $Data = trim($Data);

        // Validaciones básicas de entrada
        // - Evita string vacío
        // - Evita fechas "nulas" típicas de BD
        if ($Data === '' || $Data === '00:00:00') {
            return false;
        }

        /********************** Definición de Patrón **********************/
        /**
         * ^ (Inicio)
         * (?:[0-9]{1,3}) -> Horas de 1 a 3 dígitos (0-999)
         * : -> Separador obligatorio
         * (?:[0-5][0-9]) -> Minutos del 00 al 59
         * (?::[0-5][0-9])? -> Segundos del 00 al 59 (opcionales)
         * $ (Fin)
         */
        $patron = '/^(?:[0-9]{1,3}):(?:[0-5][0-9])(?::[0-5][0-9])?$/';

        if (preg_match($patron, $Data)) {
            $partes = explode(':', $Data);
            $horas = (int)$partes[0];

            // Validación de tope máximo definido en lógica de negocio
            return $horas <= 999;
        }

        /********************** Retorno de Datos **********************/
        return false;
    }

	/************************************************************************************************************/
	/**
     * Valida si una cadena corresponde a una fecha real según un formato específico.
     *
     * ✔ Soporta validación estricta usando DateTime
     * ✔ Detecta errores y warnings internos de parsing
     * ✔ Evita fechas inválidas como 2023-02-31
     * ✔ Elimina espacios en blanco que puedan invalidar la comparación
     *
     * @param string $Data   Cadena de fecha a validar
     * @param string $format Formato esperado (por defecto 'Y-m-d')
     *
     * @return bool True si la fecha es válida y coincide exactamente con el formato
	 *
	 * @example
	 * ```php
	 * $DataValidations->validarFecha('1900-01-01');          //Devuelve true
	 * $DataValidations->validarFecha('1900-01-01', 'Y-m-d'); //Devuelve true
     * $DataValidations->validarFecha('a');                   //Devuelve false
	 * ```
	 *
     */
    public function validarFecha($Data, $format = 'Y-m-d'): bool {

        /**********************  Validaciones   **********************/
        // Limpia espacios en blanco al inicio y final
        // (muy común cuando los datos vienen de formularios o BD)
        $Data = trim($Data);

        // Validaciones básicas de entrada
        // - Evita string vacío
        // - Evita fechas "nulas" típicas de BD
        if ($Data === '' || $Data === '0000-00-00') {
            return false;
        }

        /********************** Si todo esta ok **********************/
        // Se establece zona horaria
        date_default_timezone_set('UTC');
        date_default_timezone_set('America/Santiago');
        // Intenta crear un objeto DateTime a partir del formato dado
        $d = DateTime::createFromFormat('!' . $format, $Data);

        // Si no se pudo crear el objeto, la fecha es inválida
        if (!$d) {
            return false;
        }

        // Obtiene errores y advertencias del último parsing
        // DateTime puede crear objetos incluso con datos incorrectos,
        // por lo que es necesario validar estos errores manualmente
        $errors = DateTime::getLastErrors();

        // Si hay warnings o errores, la fecha no es válida
        if ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
            return false;
        }

        /********************** Retorno datos  **********************/
        // Validación final estricta:
        // Compara la fecha formateada con la original
        // Esto evita casos como:
        // '2023-02-31' → se convierte en '2023-03-03'
        return $d->format($format) === $Data;

    }

	/************************************************************************************************************/
	/**
     * Valida si el dato ingresado es un número entero.
     * * A diferencia de is_int(), esta función permite validar números que vienen
     * como strings (común en formularios) siempre que no contengan decimales.
     *
     * @param mixed $Data Dato a validar.
     *
     * @return bool True si es un número entero.
	 *
	 * @example
	 * ```php
	 * $DataValidations->validarEntero(16);   //Devuelve true
	 * $DataValidations->validarEntero('16'); //Devuelve false
	 * ```
	 *
     */
    public function validarEntero($Data): bool {

        /********************** Validaciones   **********************/
        if($Data === ''){ return false; }

        /********************** Si todo esta ok **********************/
        /********************** Retorno datos  **********************/
        // is_numeric asegura que sea un número, ctype_digit asegura que no tenga decimales ni signos
        return (is_numeric($Data)) ? ctype_digit(strval($Data)) : false;

    }

	/************************************************************************************************************/
	/**
     * Detecta si el usuario está accediendo desde un dispositivo móvil.
     * * Analiza la cadena HTTP_USER_AGENT del navegador en busca de palabras clave
     * comunes de sistemas operativos y navegadores móviles.
     *
     * @return bool True si se detecta un dispositivo móvil o tablet.
	 *
	 * @example
	 * ```php
	 * $DataValidations->validarDispositivoMovil();
	 * ```
	 *
     */
    public function validarDispositivoMovil(): bool {

        // Obtiene el User Agent del servidor
        $userAgent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');

        /********************** Si todo esta ok **********************/
        // Lista de palabras clave para identificar plataformas móviles
        $movilKeywords = [
            'android', 'iphone', 'ipod', 'ipad', 'blackberry', 'windows phone',
            'opera mini', 'opera mobi', 'mobile', 'silk', 'kindle', 'webos',
            'palm', 'symbian', 'fennec', 'maemo', 'nokia', 'htc', 'samsung',
            'lg', 'motorola', 'tablet', 'playbook'
        ];

        /********************** Retorno datos  **********************/
        foreach ($movilKeywords as $keyword) {
            if (strpos($userAgent, $keyword) !== false) {
                return true;
            }
        }
        return false;

    }

	/************************************************************************************************************/
	/**
     * Valida que una cadena de texto tenga al menos una cantidad mínima de caracteres.
     *
     * @param string $oracion Texto a validar.
     * @param int $largo Cantidad mínima de caracteres requerida.
     *
     * @return bool True si cumple con el largo mínimo.
	 *
	 * @example
	 * ```php
	 * 	$DataValidations->validarLargoMinimo('Lorem ipsum dolor sit amet, consectetur', 10); //Devuelve 'El dato ingresado debe tener no mas de 10 caracteres'
	 * 	$DataValidations->validarLargoMinimo('Lorem', 10); //Devuelve 1
	 * ```
	 *
     */
    public function validarLargoMinimo($oracion, $largo): bool {

        /********************** Validaciones   **********************/
        // Validaciones básicas de entrada
        // - Evita string vacío
        if ($oracion === '') {
            return false;
        }
        // Asegura que el parámetro de comparación sea un número válido
        if (!$this->validarNumero($largo) || !$this->validarEntero($largo)){  return false; }

        /********************** Si todo esta ok **********************/
        /********************** Retorno datos  **********************/
        return strlen((string)$oracion) >= $largo;

    }

	/************************************************************************************************************/
	/**
     * Valida que una cadena de texto no exceda una cantidad máxima de caracteres.
     *
     * @param string $oracion Texto a validar.
     * @param int $largo Cantidad máxima de caracteres permitida.
     *
     * @return bool True si el texto es igual o menor al largo indicado.
	 *
	 * @example
	 * ```php
	 * 	$DataValidations->validarLargoMaximo('Lorem', 10); //Devuelve 'El dato ingresado debe tener al menos 10 caracteres'
	 * 	$DataValidations->validarLargoMaximo('Lorem ipsum dolor sit amet, consectetur', 10); //Devuelve 1
	 * ```
	 *
     */
    public function validarLargoMaximo($oracion, $largo): bool {

        /********************** Validaciones   **********************/
        // Validaciones básicas de entrada
        // - Evita string vacío
        if ($oracion === '') {
            return false;
        }
        if (!$this->validarNumero($largo) || !$this->validarEntero($largo)){  return false; }

        /********************** Si todo esta ok **********************/
        /********************** Retorno datos  **********************/
        return strlen((string)$oracion) <= $largo;

    }

	/************************************************************************************************************/
	/**
     * Valida conjuntos de datos o variables individuales según diferentes reglas de negocio.
     * * Permite centralizar la validación de opciones, ejecución de métodos dinámicos
     * o validaciones básicas de tipos, generando alertas visuales mediante UIWidgetsCommon.
     *
     * @param array $validOptions Diccionario de opciones permitidas para validación por pertenencia.
     * @param mixed $dataToCheck Datos a validar (array de configuración o valor simple).
     * @param string $placeholder Texto de referencia para identificar el origen del dato en el mensaje.
     * @param int $type Identificador del motor de validación a utilizar (del 1 al 9).
     *
     * @return array Estructura con la cuenta de errores ['nErrors'] y las alertas HTML ['alerts'].
	 *
	 * @example
	 * ```php
	 * 	//Definir opciones válidas
     * 	$validOptions = [
     * 		'type'  => range(1, 7),
     * 	];
     *
     * 	//Opciones a validar
     * 	$optionsToCheck = [
     * 		['value' => $type,  'name' => 'type',  'label' => '$type'],
     * 	];
     *
     * 	//se ejecuta operacion
     * 	$DataValidations->checkData($validOptions, $optionsToCheck, '', 1); //Devuelve un array
	 * ```
	 *
     */
    public function checkData($validOptions, $dataToCheck, $placeholder, $type): array {

        /**********************  Definiciones   **********************/
        // Inicialización de contadores y componentes de interfaz
        $dataReturn['nErrors'] = 0;
        $dataReturn['alerts']  = '';
        $Alertas               = new UIWidgetsCommon();

        /**********************  Validaciones   **********************/
        // Selección del motor de validación basado en el tipo solicitado
        switch ($type) {
            case 1:
                // Validación de pertenencia simple para una colección de opciones
                foreach($dataToCheck as $option) {
                    if (!in_array($option['value'], $validOptions[$option['name']], true)) {
                        $dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, sprintf('La configuración %s (%s) entregada no está dentro de las opciones',$option['label'],$option['value']));
                        $dataReturn['nErrors']++;
                    }
                }
                break;

            case 2:
                // Validación de pertenencia incluyendo un marcador de posición (placeholder) general
                foreach($dataToCheck as $option) {
                    if (!in_array($option['value'], $validOptions[$option['name']], true)) {
                        $dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, sprintf('La configuración %s (%s) entregada en <strong>%s</strong> no esta dentro de las opciones',$option['label'],$option['value'],$placeholder));
                        $dataReturn['nErrors']++;
                    }
                }
                break;

            case 3:
                // Ejecución dinámica de métodos de validación internos definidos en el array de entrada
                foreach ($dataToCheck as $field) {
                    if (!$this->{$field['method']}($field['value']) && $field['value'] != '') {
                        $dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'El valor ingresado en '.$field['label'].' ('.$field['value'].') en <strong>'.$placeholder.'</strong> '.$field['msg']);
                        $dataReturn['nErrors']++;
                    }
                }
                break;

            case 4:
                // Validación de pertenencia con marcador de posición específico por cada opción
                foreach($dataToCheck as $option) {
                    if (!in_array($option['value'], $validOptions[$option['name']], true)) {
                        $dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, sprintf('La configuración %s (%s) entregada en <strong>%s</strong> no esta dentro de las opciones',$option['label'],$option['value'],$option['placeholder']));
                        $dataReturn['nErrors']++;
                    }
                }
                break;

            case 5:
                // Ejecución dinámica de métodos internos con marcador de posición individualizado
                foreach ($dataToCheck as $field) {
                    if (!$this->{$field['method']}($field['value']) && $field['value'] != '') {
                        $dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'El valor ingresado en '.$field['label'].' ('.$field['value'].') en <strong>'.$field['placeholder'].'</strong> '.$field['msg']);
                        $dataReturn['nErrors']++;
                    }
                }
                break;

            case 6:
                // Validación de pertenencia (idéntica al caso 1)
                foreach($dataToCheck as $option) {
                    if (!in_array($option['value'], $validOptions[$option['name']], true)) {
                        $dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, sprintf('La configuración %s (%s) entregada no esta dentro de las opciones',$option['label'],$option['value']));
                        $dataReturn['nErrors']++;
                    }
                }
                break;

            case 7:
                // Verificación de valor numérico y entero para un dato único
                if (!$this->validarNumero($dataToCheck)&&!$this->validarEntero($dataToCheck)&&$dataToCheck!=''){
                    $dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'El valor ingresado en $value ('.$dataToCheck.') en <strong>'.$placeholder.'</strong> no es un numero o no es un numero entero');
                    $dataReturn['nErrors']++;
                }
                break;

            case 8:
                // Verificación de formato de fecha para un dato único
                if (!$this->validarFecha($dataToCheck)&&$dataToCheck!=''){
                    $dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'El valor ingresado en $value ('.$dataToCheck.') en <strong>'.$placeholder.'</strong> no es una fecha');
                    $dataReturn['nErrors']++;
                }
                break;

            case 9:
                // Validación de presencia de datos (campo no vacío)
                if ($dataToCheck!=''){
                    $dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'El valor ingresado en $value ('.$dataToCheck.') en <strong>'.$placeholder.'</strong> esta vacio');
                    $dataReturn['nErrors']++;
                }
                break;
        }

        /**********************  Retorno datos  **********************/
        // Retorno de resultados del procesamiento
        return $dataReturn;

    }

}
