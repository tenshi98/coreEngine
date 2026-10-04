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
        if ($Data === null || trim((string)$Data) === '' || $Data == '0') {
            return false;
        }

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
        if ($Data === null || trim((string)$Data) === '') {
            return false;
        }

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
	 * $DataValidations->validarNumero(25);     //Devuelve true
	 * $DataValidations->validarNumero('25');   //Devuelve true (acepta numeros como string)
	 * $DataValidations->validarNumero('12,5'); //Devuelve true (normaliza la coma decimal)
	 * $DataValidations->validarNumero('abc');  //Devuelve false
	 * ```
	 *
     */
    public function validarNumero($Data): bool {

        /**********************  Validaciones   **********************/
        if ($Data === null || trim((string)$Data) === '') {
            return false;
        }

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
        if ($Data === null || trim((string)$Data) === '') {
            return false;
        }

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
	 * $DataValidations->validarURL('https://www.google.cl'); //Devuelve true
	 * $DataValidations->validarURL('https://www.  SSS  ');   //Devuelve false
	 * ```
	 *
     */
    public function validarURL($Data): bool {

        /**********************  Validaciones   **********************/
        if ($Data === null || trim((string)$Data) === '') {
            return false;
        }

        /********************** Limpieza **********************/
        $Data = trim($Data);

        /********************** Retorno de Datos **********************/
        return $Data !== '' && filter_var($Data, FILTER_VALIDATE_URL) !== false;

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
        // - Evita string vacío
        // - Evita fechas "nulas" típicas de BD
        if ($Data === null || trim((string)$Data) === '' || $Data === '00:00:00') {
            return false;
        }

        $Data = trim($Data);

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
        // - Evita string vacío
        // - Evita fechas "nulas" típicas de BD
        if ($Data === null || trim((string)$Data) === '' || $Data === '0000-00-00') {
            return false;
        }

        $Data = trim($Data);

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
	 * $DataValidations->validarEntero(16);     //Devuelve true
	 * $DataValidations->validarEntero('16');   //Devuelve true (acepta enteros como string)
	 * $DataValidations->validarEntero('16.5'); //Devuelve false (tiene decimales)
	 * $DataValidations->validarEntero('-16');  //Devuelve false (no admite signo, usar validarEnteroConSigno)
	 * ```
	 *
     */
    public function validarEntero($Data): bool {

        /********************** Validaciones   **********************/
        if ($Data === null || trim((string)$Data) === '') {
            return false;
        }

        /********************** Si todo esta ok **********************/
        /********************** Retorno datos  **********************/
        // is_numeric asegura que sea un número, ctype_digit asegura que no tenga decimales ni signos
        return (is_numeric($Data)) ? ctype_digit(strval($Data)) : false;

    }

/************************************************************************************************************/
/**
     * Valida si el dato ingresado es un número entero que admite signo.
     * * A diferencia de validarEntero(), acepta un signo '-' o '+' al inicio, ya que
     * muchos formularios requieren enteros bajo cero (temperaturas, saldos, ajustes).
     * * Sigue rechazando decimales y texto.
     *
     * @param mixed $Data Dato a validar.
     *
     * @return bool True si es un número entero con o sin signo.
 *
 * @example
 * ```php
 * $DataValidations->validarEnteroConSigno('-16'); //Devuelve true
 * $DataValidations->validarEnteroConSigno('16');  //Devuelve true
 * $DataValidations->validarEnteroConSigno('1.5'); //Devuelve false
 * ```
 *
     */
    public function validarEnteroConSigno($Data): bool {

        /********************** Validaciones   **********************/
        if ($Data === null || trim((string)$Data) === '') {
            return false;
        }

        /********************** Retorno datos  **********************/
        // Solo dígitos, con un signo opcional al inicio
        return (bool) preg_match('/^[+-]?[0-9]+$/', trim((string)$Data));

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
	 * 	$DataValidations->validarLargoMinimo('Lorem ipsum dolor sit amet, consectetur', 10); //Devuelve true
	 * 	$DataValidations->validarLargoMinimo('Lorem', 10); //Devuelve false
	 * ```
	 *
     */
    public function validarLargoMinimo($oracion, $largo): bool {

        /********************** Validaciones   **********************/
        // Validaciones básicas de entrada
        // - Evita string vacío
        if ($oracion === null || trim((string)$oracion) === '') {
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
	 * 	$DataValidations->validarLargoMaximo('Lorem', 10); //Devuelve true
	 * 	$DataValidations->validarLargoMaximo('Lorem ipsum dolor sit amet, consectetur', 10); //Devuelve false
	 * ```
	 *
     */
    public function validarLargoMaximo($oracion, $largo): bool {

        /********************** Validaciones   **********************/
        // Validaciones básicas de entrada
        // - Evita string vacío
        if ($oracion === null || trim((string)$oracion) === '') {
            return false;
        }
        if (!$this->validarNumero($largo) || !$this->validarEntero($largo)){  return false; }

        /********************** Si todo esta ok **********************/
        /********************** Retorno datos  **********************/
        return strlen((string)$oracion) <= $largo;

    }

	/************************************************************************************************************/
	/**
     * Valida que el dato sea un texto (dato simple) apto para imprimirse o concatenarse en HTML.
     * * Acepta cualquier valor escalar (texto, número o booleano) y los valores vacíos; rechaza
     * arreglos y objetos, que son los que provocan el aviso "Array to string conversion" al
     * interpolarse dentro de una cadena.
     * * Se usa con el motor 2 de checkData() para los datos descriptivos de los widgets
     * (Title, Text, Text_copy, Icon), que no tienen un formato específico que validar.
     *
     * @param mixed $Data Valor a validar.
     *
     * @return bool True si el dato es un texto/dato simple, false si es un arreglo u objeto.
	 *
	 * @example
	 * ```php
	 * 	$DataValidations->validarTextoPlano('usuario@empresa.com'); //Devuelve true
	 * 	$DataValidations->validarTextoPlano(25);                   //Devuelve true (los números son datos simples)
	 * 	$DataValidations->validarTextoPlano(['a', 'b']);           //Devuelve false
	 * ```
	 *
     */
    public function validarTextoPlano($Data): bool {

        /********************** Validaciones   **********************/
        // Sin dato (null) se considera valido: el campo es opcional
        if ($Data === null) { return true; }

        /********************** Retorno datos  **********************/
        // Solo los datos simples se pueden imprimir como texto
        return is_scalar($Data);

    }

	/************************************************************************************************************/
	/**
     * Valida conjuntos de datos o variables individuales según diferentes reglas de negocio.
     * * Permite centralizar la validación de opciones, ejecución de métodos dinámicos
     * o validaciones básicas de tipos, generando alertas visuales mediante UIWidgetsCommon.
     *
     * El comportamiento está determinado por $type, que selecciona el "motor" de validación
     * dentro del switch interno. Tras el refactor el catálogo quedó en 7 motores sin
     * duplicados, agrupados en dos familias según la forma que debe tener $dataToCheck:
     *
     *  type | Familia  | $dataToCheck      | Regla que aplica
     *  -----+----------+-------------------+-----------------------------------------------
     *    1  | arreglos | array de opciones | pertenencia estricta a $validOptions
     *    2  | arreglos | array de campos   | método de validación dinámico
     *    3  | escalar  | valor simple      | número (enteros, decimales y negativos)
     *    4  | escalar  | valor simple      | entero (sin decimales ni signo)
     *    5  | escalar  | valor simple      | entero con signo (admite negativos)
     *    6  | escalar  | valor simple      | fecha válida en formato 'Y-m-d'
     *    7  | escalar  | valor simple      | requerido (no puede venir vacío)
     *
     * - El motor 1 recibe opciones con 'value', 'name' y 'label' (y 'placeholder' opcional).
     * - El motor 2 recibe campos con 'value', 'method', 'label' y 'msg' (y 'placeholder' opcional).
     * - Pertenencia del motor 1: admite listas blancas de números (ej: `range(1, 7)`),
     *   de cadenas de texto (ej: `['GET', 'POST', 'PUT', 'DELETE']`, `['sm', 'md', 'lg']`),
     *   o mixtas. La comparación es estricta (el tipo y mayúsculas/minúsculas importan) pero
     *   acepta la equivalencia texto/número, de modo que '3' (string de $_POST) pertenece a
     *   range(1, 7) y 3 pertenece a ['1', '3']. Booleanos, nulos, arreglos y objetos nunca se normalizan.
     * - El parámetro $placeholder es el RESPALDO: se usa cuando el elemento no trae el suyo.
     *   Si no hay ninguno de los dos, el mensaje se emite sin el bloque 'en <strong>...</strong>'.
     * - En los motores 1, 2 y 3 a 6 un valor vacío significa "sin dato" y NO se valida (el
     *   campo es opcional). El motor 7 existe justamente para el caso contrario.
     *
     * Equivalencias con el catálogo anterior (usadas para migrar las llamadas):
     *   1 -> 1 | 2 -> 1 | 4 -> 1 | 6 -> 1  (los cuatro motores hacían lo mismo: pertenencia)
     *   3 -> 2 | 5 -> 2                     (los dos ejecutaban un método de validación)
     *   7 -> 4 y 5                         (antes mezclaba "número" con "entero")
     *   8 -> 6 | 9 -> 7                     (el 9 tenía la condición invertida)
     *
     * El retorno es SIEMPRE el mismo arreglo de dos claves:
     *   ['nErrors' => (int), 'alerts' => (string HTML)]
     * con nErrors = 0 y alerts = '' cuando no hay ninguna observación.
     *
     * @param array $validOptions Diccionario de listas de valores permitidos. Cada clave debe
     *               coincidir con el 'name' informado en las opciones de $dataToCheck (solo lo
     *               usa el motor 1; en el resto se ignora y los invocadores pasan '').
     *               Las listas pueden ser numéricas, de cadenas de texto o mixtas.
     *               Ej: ['type' => range(1, 7), 'method' => ['GET', 'POST', 'PUT'], 'size' => ['sm', 'md', 'lg']].
     * @param mixed $dataToCheck Datos a validar: arreglo de elementos (motores 1 y 2) o valor
     *               simple, string o número (motores 3 a 7).
     * @param string $placeholder Texto de referencia (normalmente el rótulo del input) que se
     *               incrusta en la alerta. Es el respaldo del 'placeholder' de cada elemento y
     *               es el identificador que usan los motores escalares en el mensaje.
     * @param int $type Identificador del motor de validación a utilizar (del 1 al 7).
     *
     * @return array Estructura con la cuenta de errores ['nErrors'] y las alertas HTML ['alerts'].
     *
     * @example
     * ```php
     * // ---------------------------------------------------------------------------
     * // ANATOMIA COMUN A TODOS LOS EJEMPLOS
     * // ---------------------------------------------------------------------------
     * // Los invocadores (UIFormInputs, UIWidgetsCommon, UIWidgetsViews) siguen siempre
     * // el mismo patrón: acumulan errores y solo generan el widget si $errorn == 0.
     *
     * $errorn = 0;
     * $alerts = '';
     *
     * $dataReturn = $DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 1);
     * $errorn += $dataReturn['nErrors']; // 0 = valido, > 0 = hay que corregir la llamada
     * $alerts .= $dataReturn['alerts'];  // HTML de alertas ya listo para imprimir
     *
     * if($errorn==0){
     *     // se genera el input
     * }else{ echo $alerts; }
     * ```
     *
     * @example
     * ```php
     * // ---------------------------------------------------------------------------
     * // $type = 1  |  Pertenencia a lista blanca (familia arreglos)
     * // ---------------------------------------------------------------------------
     * // QUE EVALUA: que cada 'value' informado exista dentro de la lista blanca
     * //             $validOptions[$option['name']]. La lista puede ser numérico-entera
     * //             (ej: range(1, 7)), de cadenas de texto (ej: ['GET', 'POST', 'PUT'])
     * //             o mixta.
     * // POR QUE: acota parámetros de configuración a rangos cerrados (Tipo 1..7,
     * //          Required 1..3, FormCol 0..12, etc.) o a palabras clave específicas
     * //          ('sm'|'md'|'lg', 'GET'|'POST', etc.) y evita que un valor fuera de
     * //          catálogo genere HTML inválido o índices inexistentes como
     * //          $options[$type-1]. Este motor reemplaza a los antiguos 1, 2, 4 y 6.
     * //
     * // FLUJO DE EVALUACION:
     * //   1. Verifica que $dataToCheck sea un arreglo (si no, alerta y termina).
     * //   2. Recorre cada opción y exige las claves 'value', 'name' y 'label'.
     * //      Si faltan, genera alerta y sigue con la siguiente opción.
     * //   3. Exige que $validOptions[$option['name']] exista y sea un arreglo.
     * //   4. Compara con comparación ESTRICTA más equivalencia texto/número:
     * //      - Para números: 3 y '3' se consideran iguales, así que un valor recibido
     * //        de $_POST ('3') SÍ pertenece a range(1, 7).
     * //      - Para cadenas de texto: la comparación distingue mayúsculas/minúsculas
     * //        ('POST' es válido en ['GET', 'POST'], pero 'post' es rechazado).
     * //      - Booleanos, nulos, arreglos y objetos no se normalizan nunca
     * //        (true jamás coincide con el 1 de una lista).
     * //   5. Si NO pertenece: alerta + nErrors++ (una alerta por cada opción inválida).
     * //   6. El marcador de posición resuelto es $option['placeholder'] y, si no viene,
     * //      el $placeholder general; si tampoco hay, el mensaje no muestra contexto.
     *
     * // Caso 1: Lista blanca numérica (rangos de tipos, configuraciones)
     * $validOptions = [
     *     'type'  => range(1, 7),
     * ];
     * $optionsToCheck = [
     *     ['value' => $type,  'name' => 'type',  'label' => '$type'],
     * ];
     * $DataValidations->checkData($validOptions, $optionsToCheck, '', 1);
     * // Resultado si $type = 3     => ['nErrors' => 0, 'alerts' => '']
     * // Resultado si $type = 9     => ['nErrors' => 1, 'alerts' => '...La configuración $type (9) no está dentro de las opciones...']
     * // Resultado si $type = '3'   => nErrors = 0 (equivalencia texto/número para $_POST)
     * // Resultado si $type = '9'   => nErrors = 1 (fuera del rango 1..7)
     *
     * // Caso 2: Lista blanca con cadenas de texto (métodos HTTP, tamaños, estados)
     * $validOptionsTxt = [
     *     'method' => ['GET', 'POST', 'PUT', 'DELETE'],
     *     'size'   => ['sm', 'md', 'lg', 'xl'],
     * ];
     * $optionsToCheckTxt = [
     *     ['value' => 'POST', 'name' => 'method', 'label' => '$method'],
     *     ['value' => 'md',   'name' => 'size',   'label' => '$size'],
     * ];
     * $DataValidations->checkData($validOptionsTxt, $optionsToCheckTxt, 'Formulario', 1);
     * // Resultado => ['nErrors' => 0, 'alerts' => '']
     * // Si $method = 'PATCH' => nErrors = 1 ('PATCH' no está dentro de las opciones en <strong>Formulario</strong>)
     * // Si $method = 'post'  => nErrors = 1 (la comparación de texto es estricta / case-sensitive)
     *
     * // USO REAL: formTittle(), formInputHidden(), formInput(), formSelect*(),
     * //           formCheckbox/Radio/Switch(), alertPostData() y widgets de UIWidgetsCommon.
     * ```
     *
     * @example
     * ```php
     * // ---------------------------------------------------------------------------
     * // $type = 2  |  Método de validación dinámico (familia arreglos)
     * // ---------------------------------------------------------------------------
     * // QUE EVALUA: invoca $this->{$field['method']}($field['value']) sobre cada campo,
     * //             es decir valida el FORMATO/CONTENIDO del valor y no su pertenencia a
     * //             una lista. Puede usar cualquier validador público de esta clase
     * //             (validarNumero, validarEntero, validarFecha, validarEmail, validarRut,
     * //             validarURL, validarHora, validarLargoMinimo, validarLargoMaximo, ...).
     * // POR QUE: permite declarar la validación como datos, sin escribir un if por campo,
     * //          y centralizar el mensaje de error. Reemplaza a los antiguos 3 y 5.
     * //
     * // FLUJO DE EVALUACION:
     * //   1. Verifica que $dataToCheck sea un arreglo (si no, alerta y termina).
     * //   2. Recorre cada campo y exige las claves 'value', 'method', 'label' y 'msg'.
     * //   3. Comprueba con method_exists() que 'method' exista en esta clase; si no
     * //      existe, genera una alerta en lugar del Error fatal de versiones anteriores.
     * //   4. Si el valor está vacío, el campo es opcional: no se valida ni suma error.
     * //   5. Ejecuta el método y, si devuelve false, alerta + nErrors++.
     * //   6. Si el valor es un arreglo u objeto, NO se llama al validador: alerta + error.
     * //   7. Marcador: 'placeholder' del campo y, si no viene, el $placeholder general.
     *
     * // Validaciones dinámicas: numéricas, cadenas de texto, formatos específicos
     * $fieldsToCheck = [
     *     ['value' => $email,    'method' => 'validarEmail',   'label' => '$email',    'msg' => 'no es un email válido'],
     *     ['value' => $rut,      'method' => 'validarRut',     'label' => '$rut',      'msg' => 'no es un RUT chileno válido'],
     *     ['value' => $patente,  'method' => 'ValidarPatente', 'label' => '$patente',  'msg' => 'no es una patente válida'],
     *     ['value' => $web,      'method' => 'validarURL',     'label' => '$web',      'msg' => 'no es una URL válida'],
     *     ['value' => $hora,     'method' => 'validarHora',    'label' => '$hora',     'msg' => 'no es una hora válida (H:M[:S])'],
     *     ['value' => $ndecimal, 'method' => 'validarEntero',  'label' => '$ndecimal', 'msg' => 'no es un número entero'],
     * ];
     *
     * $DataValidations->checkData('', $fieldsToCheck, $placeholder, 2);
     *
     * // Si $email = 'usuario@empresa.com' => nErrors = 0
     * // Si $email = 'usuario-invalido'    => nErrors = 1: '...no es un email válido'
     * // Si $rut   = '12.345.678-5'        => nErrors = 0 (admite puntos y guión)
     * // Si $patente = 'BB-CC-12'          => nErrors = 0
     * // Si $value = ''                    => nErrors = 0 (vacío permitido, el campo es opcional)
     * // Si 'method' = 'validarInexistente' => nErrors = 1 con alerta, ya NO Error fatal
     *
     * // USO REAL: formNumberSpinner(), formCheckbox(), formRadio(), formSwitch(),
     * //           formSelect*(), formSelectCountry(), formSelectnAuto(), formUploadMultiple().
     * ```
     *
     * @example
     * ```php
     * // ---------------------------------------------------------------------------
     * // $type = 3  |  Escalar: número (enteros, decimales y negativos)
     * // ---------------------------------------------------------------------------
     * // QUE EVALUA: !validarNumero($dataToCheck) sobre un único valor. Acepta enteros,
     * //             decimales con punto o coma (validarNumero normaliza la coma) y negativos.
     * // POR QUE: es el motor del valor inicial de los inputs numéricos de formulario, en
     * //          especial FormType 5 (reales/decimales) de formInput(), que antes NO tenía
     * //          ninguna validación de valor.
     * //
     * // FLUJO DE EVALUACION:
     * //   1. Verifica que $dataToCheck NO sea arreglo ni objeto (si lo es, alerta y termina).
     * //   2. Si el valor está vacío o es solo espacios, se omite (campo opcional).
     * //   3. Ejecuta validarNumero(); si devuelve false, alerta + nErrors++.
     *
     * $DataValidations->checkData('', $value, $placeholder, 3);
     *
     * // Si $value = 'abc' => nErrors = 1:
     * // 'El valor ingresado (abc) en <strong>Precio</strong> no es un número'
     * // Si $value = '12'    => nErrors = 0
     * // Si $value = '12,5'  => nErrors = 0 (la coma es un decimal válido)
     * // Si $value = '-12.5' => nErrors = 0
     * // Si $value = ''      => nErrors = 0
     *
     * // USO REAL: formInput() con FormType 5.
     * ```
     *
     * @example
     * ```php
     * // ---------------------------------------------------------------------------
     * // $type = 4  |  Escalar: entero (sin decimales ni signo)
     * // ---------------------------------------------------------------------------
     * // QUE EVALUA: !validarEntero($dataToCheck). Solo dígitos: rechaza decimales,
     * //             signos, notación científica y texto.
     * // POR QUE: corrige el bug del antiguo motor 7, que decía "entero" en el mensaje
     * //          pero aceptaba decimales y negativos. Es el motor correcto para
     * //          FormType 4 de formInput() ("números enteros positivos").
     * //
     * // FLUJO DE EVALUACION:
     * //   1. Verifica que $dataToCheck NO sea arreglo ni objeto.
     * //   2. Si el valor está vacío se omite.
     * //   3. Ejecuta validarEntero(); si devuelve false, alerta + nErrors++.
     *
     * $DataValidations->checkData('', $value, $placeholder, 4);
     *
     * // Si $value = '12'   => nErrors = 0
     * // Si $value = '12,5' => nErrors = 1: '... no es un número entero'
     * // Si $value = '-7'   => nErrors = 1 (para negativos usar el motor 5)
     * // Si $value = ''     => nErrors = 0
     *
     * // USO REAL: formInput() con FormType 4.
     * ```
     *
     * @example
     * ```php
     * // ---------------------------------------------------------------------------
     * // $type = 5  |  Escalar: entero con signo (admite negativos)
     * // ---------------------------------------------------------------------------
     * // QUE EVALUA: !validarEnteroConSigno($dataToCheck), validador que acepta un signo
     * //             '+' o '-' opcional seguido de dígitos, pero sigue rechazando decimales.
     * // POR QUE: FormType 6 de formInput() necesita "enteros que permiten valores
     * //          negativos" (temperaturas, ajustes, saldos) y ninguna validación previa
     * //          cubría ese caso: validarEntero() rechaza el signo y validarNumero()
     * //          acepta decimales de más.
     * //
     * // FLUJO DE EVALUACION:
     * //   1. Verifica que $dataToCheck NO sea arreglo ni objeto.
     * //   2. Si el valor está vacío se omite.
     * //   3. Ejecuta validarEnteroConSigno(); si devuelve false, alerta + nErrors++.
     *
     * $DataValidations->checkData('', $value, $placeholder, 5);
     *
     * // Si $value = '-7'   => nErrors = 0
     * // Si $value = '+7'   => nErrors = 0
     * // Si $value = '7'    => nErrors = 0
     * // Si $value = '7.5'  => nErrors = 1: '... no es un número entero con signo'
     *
     * // USO REAL: formInput() con FormType 6.
     * ```
     *
     * @example
     * ```php
     * // ---------------------------------------------------------------------------
     * // $type = 6  |  Escalar: fecha válida 'Y-m-d'
     * // ---------------------------------------------------------------------------
     * // QUE EVALUA: !validarFecha($dataToCheck) con el formato por defecto 'Y-m-d'.
     * // POR QUE: valida forma y existencia real de la fecha (DateTime + conteo de
     * //          errores + comparación estricta), por lo que rechaza '2023-02-31' o
     * //          texto suelto. Se usa en formInput() para FormType 7 (input type=date)
     * //          y FormType 8 (datepicker Material), donde el $value puede venir de la BD.
     * //
     * // FLUJO DE EVALUACION:
     * //   1. Verifica que $dataToCheck NO sea arreglo ni objeto.
     * //   2. Si el valor está vacío se omite.
     * //   3. Ejecuta validarFecha(); si devuelve false, alerta + nErrors++.
     *
     * $DataValidations->checkData('', $value, $placeholder, 6);
     *
     * // Si $value = '2023-02-28' => nErrors = 0
     * // Si $value = '2023-02-31' => nErrors = 1: '... no es una fecha válida (formato Y-m-d)'
     * // Si $value = '28-02-2023' => nErrors = 1 (el formato esperado es Y-m-d)
     *
     * // USO REAL: formInput() con FormType 7 y FormType 8.
     * ```
     *
     * @example
     * ```php
     * // ---------------------------------------------------------------------------
     * // $type = 7  |  Escalar: requerido (no puede venir vacío)
     * // ---------------------------------------------------------------------------
     * // QUE EVALUA: _checkDataIsEmpty($dataToCheck), es decir la AUSENCIA de dato es el
     * //             error. Se corrige el bug del antiguo motor 9, que tenía la condición
     * //             invertida (alertaba cuando el valor SÍ tenía contenido).
     * // POR QUE: sirve para validar datos ya recibidos (por ejemplo $_POST o registros
     * //          leídos de la BD) donde el dato es obligatorio. NO se debe usar sobre el
     * //          'Value' inicial de un widget: un input de Required 2 con Value '' debe
     * //          poder renderizarse (la obligatoriedad la resuelve el required del HTML).
     * //
     * // FLUJO DE EVALUACION:
     * //   1. Verifica que $dataToCheck NO sea arreglo ni objeto.
     * //   2. Si el valor es null, '' o solo espacios: alerta + nErrors++.
     * //   3. Cualquier otro valor (incluidos 0 y '0') es válido.
     *
     * $DataValidations->checkData('', $value, $placeholder, 7);
     *
     * // Si $value = ''     => nErrors = 1:
     * // 'El campo en <strong>Nombre</strong> es obligatorio y no puede estar vacío'
     * // Si $value = '   '  => nErrors = 1 (solo espacios se considera vacío)
     * // Si $value = null   => nErrors = 1
     * // Si $value = '0'    => nErrors = 0 (cero es un dato válido, no se considera vacío)
     * // Si $value = 0      => nErrors = 0 (entero cero también es válido)
     * // Si $value = false  => nErrors = 0 (booleano false no es nulo ni cadena vacía)
     * // Si $value = 'algo' => nErrors = 0
     *
     * // USO REAL: ninguno todavía (utilidad disponible para validar datos recibidos).
     * ```
     *
     * @example
     * ```php
     * // ---------------------------------------------------------------------------
     * // VALIDACIONES ADICIONALES QUE APLICA TODA LLAMADA
     * // ---------------------------------------------------------------------------
     * // Antes de entrar al switch el método comprueba, para no fallar en silencio:
     * //   1. Que $type exista en el catálogo 1..7. Si no existe, alerta + nErrors = 1
     * //      (antes devolvía 0 errores y alertas vacías: fallaba abierto).
     * //   2. Que $dataToCheck sea arreglo cuando el motor es 1 o 2.
     * //   3. Que $dataToCheck NO sea arreglo/objeto cuando el motor es 3 a 7.
     * // Si alguna de estas comprobaciones falla, retorna de inmediato con la alerta.
     * //
     * // Dentro de los motores de arreglo, además se valida cada elemento:
     * //   4. Motor 1: exige 'value', 'name' y 'label', y que 'name' exista en $validOptions.
     * //   5. Motor 2: exige 'value', 'method', 'label' y 'msg', que 'method' exista como
     * //      método público de la clase y que el valor sea simple (un arreglo nunca se
     * //      pasa al validador, evitando el warning 'Array to string conversion').
     * //
     * // Ejemplo del motor inexistente:
     * $DataValidations->checkData($validOptions, $optionsToCheck, '', 99);
     * // => ['nErrors' => 1, 'alerts' => '...El motor de validación solicitado ($type = 99) no existe...']
     * ```
     *
     */
    public function checkData($validOptions, $dataToCheck, $placeholder, $type): array {

        /**********************  Definiciones   **********************/
        // Inicialización de contadores y componentes de interfaz
        $dataReturn['nErrors'] = 0;
        $dataReturn['alerts']  = '';
        $Alertas               = new UIWidgetsCommon();

        // Catálogo de motores: los que recorren arreglos y los que validan un valor simple
        $arrayEngines  = [1, 2];
        $scalarEngines = [3, 4, 5, 6, 7];

        /**********************  Validaciones   **********************/
        // Normalización defensiva del motor: se acepta aunque llegue como string numérico ('2')
        if (!is_int($type) && is_numeric($type) && (int)$type == $type) { $type = (int)$type; }

        // El motor solicitado debe existir dentro del catálogo (antes fallaba abierto, en silencio)
        if (!in_array($type, array_merge($arrayEngines, $scalarEngines), true)) {
            $dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'El motor de validación solicitado ($type = '.$type.') no existe en el catálogo de checkData()');
            $dataReturn['nErrors']++;
            return $dataReturn;
        }

        // La estructura de los datos debe ser coherente con la familia del motor elegido
        if (in_array($type, $arrayEngines, true) && !is_array($dataToCheck)) {
            $dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'El motor de validación '.$type.' de checkData() requiere un arreglo de elementos en el parámetro $dataToCheck');
            $dataReturn['nErrors']++;
            return $dataReturn;
        }
        if (in_array($type, $scalarEngines, true) && (is_array($dataToCheck) || is_object($dataToCheck))) {
            $dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'El motor de validación '.$type.' de checkData() requiere un valor simple (string o número) en el parámetro $dataToCheck');
            $dataReturn['nErrors']++;
            return $dataReturn;
        }

        /**********************  Motores de validación   **********************/
        switch ($type) {

            case 1:
                // Pertenencia: cada valor debe existir dentro de su lista blanca de opciones
                foreach ($dataToCheck as $option) {

                    // Estructura mínima exigida por cada opción
                    if (!is_array($option) || !array_key_exists('value', $option) || !array_key_exists('name', $option) || !array_key_exists('label', $option)) {
                        $dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'Cada opción entregada al motor 1 de checkData() requiere las claves value, name y label');
                        $dataReturn['nErrors']++;
                        continue;
                    }

                    // La clave 'name' debe tener su lista de valores permitidos definida en $validOptions
                    if (!is_array($validOptions) || !isset($validOptions[$option['name']]) || !is_array($validOptions[$option['name']])) {
                        $dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'No se definió una lista de opciones permitidas para '.$option['label'].' en el parámetro $validOptions de checkData()');
                        $dataReturn['nErrors']++;
                        continue;
                    }

                    // Pertenencia: comparación estricta con equivalencia texto/número ('3' contra range(1, 7))
                    if (!$this->_checkDataInOptions($option['value'], $validOptions[$option['name']])) {
                        $dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, $this->_checkDataAlert('La configuración '.$option['label'], $option['value'], $option['placeholder'] ?? '', $placeholder, 'no está dentro de las opciones'));
                        $dataReturn['nErrors']++;
                    }
                }
                break;

            case 2:
                // Formato/contenido: ejecuta dinámicamente el validador declarado en cada campo
                foreach ($dataToCheck as $field) {

                    // Estructura mínima exigida por cada campo
                    if (!is_array($field) || !array_key_exists('value', $field) || !array_key_exists('method', $field) || !array_key_exists('label', $field) || !array_key_exists('msg', $field)) {
                        $dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'Cada campo entregado al motor 2 de checkData() requiere las claves value, method, label y msg');
                        $dataReturn['nErrors']++;
                        continue;
                    }

                    // El método declarado debe existir en esta clase (evita el Error fatal)
                    if (!method_exists($this, $field['method'])) {
                        $dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'El método de validación "'.$field['method'].'" declarado en '.$field['label'].' no existe en FunctionsDataValidations');
                        $dataReturn['nErrors']++;
                        continue;
                    }

                    // Los campos sin valor son opcionales: no se validan ni generan error
                    if ($this->_checkDataIsEmpty($field['value'])) { continue; }

                    // El valor debe ser un dato simple: evita warnings de conversión al validar arreglos
                    if (is_array($field['value']) || is_object($field['value'])) {
                        $dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'El valor de '.$field['label'].' debe ser un dato simple (string o número) para poder validarlo con '.$field['method'].'()');
                        $dataReturn['nErrors']++;
                        continue;
                    }

                    if (!$this->{$field['method']}($field['value'])) {
                        $dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, $this->_checkDataAlert('El valor ingresado en '.$field['label'], $field['value'], $field['placeholder'] ?? '', $placeholder, $field['msg']));
                        $dataReturn['nErrors']++;
                    }
                }
                break;

            case 3:
                // Número: enteros, decimales (punto o coma) y negativos
                if (!$this->_checkDataIsEmpty($dataToCheck) && !$this->validarNumero($dataToCheck)) {
                    $dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, $this->_checkDataAlert('El valor ingresado', $dataToCheck, '', $placeholder, 'no es un número'));
                    $dataReturn['nErrors']++;
                }
                break;

            case 4:
                // Entero: sin decimales ni signo
                if (!$this->_checkDataIsEmpty($dataToCheck) && !$this->validarEntero($dataToCheck)) {
                    $dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, $this->_checkDataAlert('El valor ingresado', $dataToCheck, '', $placeholder, 'no es un número entero'));
                    $dataReturn['nErrors']++;
                }
                break;

            case 5:
                // Entero con signo: admite negativos, pero no decimales
                if (!$this->_checkDataIsEmpty($dataToCheck) && !$this->validarEnteroConSigno($dataToCheck)) {
                    $dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, $this->_checkDataAlert('El valor ingresado', $dataToCheck, '', $placeholder, 'no es un número entero con signo'));
                    $dataReturn['nErrors']++;
                }
                break;

            case 6:
                // Fecha: formato por defecto de validarFecha(), 'Y-m-d', y fecha real existente
                if (!$this->_checkDataIsEmpty($dataToCheck) && !$this->validarFecha($dataToCheck)) {
                    $dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, $this->_checkDataAlert('El valor ingresado', $dataToCheck, '', $placeholder, 'no es una fecha válida (formato Y-m-d)'));
                    $dataReturn['nErrors']++;
                }
                break;

            case 7:
                // Requerido: la ausencia de valor es el error
                if ($this->_checkDataIsEmpty($dataToCheck)) {
                    $dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, $this->_checkDataAlert('El campo', '', '', $placeholder, 'es obligatorio y no puede estar vacío', false));
                    $dataReturn['nErrors']++;
                }
                break;
        }

        /**********************  Retorno datos  **********************/
        // Retorno de resultados del procesamiento
        return $dataReturn;

    }

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Internos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
    /************************************************************************************************************/
	/**
     * Determina si un valor pertenece a una lista de opciones permitidas.
     * * La comparación es ESTRICTA (el tipo de dato importa), pero además se acepta la
     * equivalencia texto/número: '3' pertenece a range(1, 7) y 3 pertenece a ['1', '3'].
     * * Esto cubre los datos que llegan como string desde $_POST o desde la base de datos,
     * que de otro modo fallarían contra listas numéricas declaradas con range().
     * * Booleanos, nulos, arreglos y objetos NO se normalizan, para no generar coincidencias
     * inesperadas (por ejemplo que el valor true coincida con el 1 de una lista).
     *
     * @param mixed $value Valor a buscar.
     * @param array $options Lista de valores permitidos.
     *
     * @return bool True si el valor pertenece a la lista de opciones.
	 *
	 * @example
	 * ```php
	 * $DataValidations->_checkDataInOptions('3', range(1, 7)); //Devuelve true (equivalencia texto/número)
	 * $DataValidations->_checkDataInOptions(3, range(1, 7));   //Devuelve true
	 * $DataValidations->_checkDataInOptions(9, range(1, 7));   //Devuelve false
	 * $DataValidations->_checkDataInOptions(true, [1]);        //Devuelve false (los booleanos no se normalizan)
	 * ```
	 *
     */
    private function _checkDataInOptions($value, $options): bool {

        /**********************  Definiciones   **********************/
        // 1) Comparación estricta: el tipo de dato importa (comportamiento base)
        if (in_array($value, $options, true)) { return true; }

        /**********************  Normalización opcional   **********************/
        // 2) Solo se normalizan datos simples de tipo texto o número
        if (!is_int($value) && !is_float($value) && !is_string($value)) { return false; }

        // 3) Equivalencia texto/número contra cada opción permitida
        foreach ($options as $option) {
            if ((is_int($option) || is_float($option) || is_string($option)) && (string)$option === (string)$value) {
                return true;
            }
        }

        /**********************  Retorno datos  **********************/
        return false;

    }

	/************************************************************************************************************/
	/**
     * Determina si un valor debe considerarse "sin dato" dentro de checkData().
     * * Se usa para que los campos opcionales no disparen errores por venir vacíos.
     * * OJO: los valores 0 y '0' NO se consideran vacíos, porque son datos válidos.
     *
     * @param mixed $Data Valor a revisar.
     *
     * @return bool True si el valor es nulo o una cadena vacía o de solo espacios.
     */
    private function _checkDataIsEmpty($Data): bool {
        return ($Data === null || (is_string($Data) && trim($Data) === ''));
    }

	/************************************************************************************************************/
	/**
     * Compone el texto estándar de las alertas generadas por checkData().
     * * El marcador de posición propio del elemento tiene prioridad sobre el general.
     * * Si no hay ningún marcador disponible, el mensaje se emite sin contexto.
     *
     * @param string $subject Sujeto del mensaje (ej: 'La configuración $type').
     * @param mixed $value Valor que falló la validación.
     * @param string $itemPlaceholder Marcador de posición propio del elemento.
     * @param string $globalPlaceholder Marcador de posición general de la llamada.
     * @param string $detail Explicación del error producido.
     * @param bool $showValue Si el valor fallido se muestra entre paréntesis.
     *
     * @return string Mensaje listo para ser usado como alerta.
     */
    private function _checkDataAlert($subject, $value, $itemPlaceholder, $globalPlaceholder, $detail, $showValue = true): string {

        /**********************  Definiciones   **********************/
        // Se prioriza el marcador del elemento y, si no viene, se usa el general
        $placeholder = (is_string($itemPlaceholder) && trim($itemPlaceholder) !== '') ? trim($itemPlaceholder) : '';
        if ($placeholder === '' && is_string($globalPlaceholder) && trim($globalPlaceholder) !== '') { $placeholder = trim($globalPlaceholder); }

        /**********************  Retorno datos  **********************/
        // El contexto del marcador solo se agrega cuando existe
        $context = ($placeholder !== '') ? ' en <strong>'.$placeholder.'</strong>' : '';
        $valueTx = ($showValue) ? ' ('.$value.')' : '';
        return $subject.$valueTx.$context.' '.$detail;

    }

}
