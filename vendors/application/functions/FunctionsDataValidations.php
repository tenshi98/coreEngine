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

    /************************************************************************************************************/
	/**
     * Valida credenciales de conexión a un servidor MySQL y comprueba permisos de usuario.
     * * Intenta establecer conexión y, según el tipo solicitado ('admin' o 'basic'),
     * realiza pruebas de creación/eliminación de bases de datos o manipulación de tablas temporales.
     *
     * @param string $host Dirección del servidor de base de datos.
     * @param string $username Nombre de usuario para la conexión.
     * @param string $password Contraseña de acceso.
     * @param string|int $port Puerto de red del servicio MySQL.
     * @param string $charset Juego de caracteres de la conexión.
     * @param string $type Nivel de permisos a validar: 'admin' o 'basic'.
     *
     * @return array Resultado de la validación con claves 'status', 'success' y 'message'.
	 *
	 * @example
	 * ```php
	 * $DataValidations->validateCredentials($host, $username, $password);
	 * ```
	 *
     */
    public function validateCredentials($host, $username, $password, $port, $charset, $type): array {

        /**********************  Definiciones   **********************/
        // Empaquetado de parámetros para verificación de existencia
        $params = compact('host', 'username', 'password', 'port', 'charset', 'type');

        /**********************  Validaciones   **********************/
        foreach ($params as $name => $value) {
            if ($value === '' || $value === null) {
                return [
                    'status'  => 'missing_param',
                    'success' => false,
                    'message' => "No hay datos en \$$name"
                ];
            }
        }

        // Validación de rango de puerto TCP estándar
        if (!is_numeric($port) || (int)$port <= 0 || (int)$port > 65535) {
            return [
                'status'  => 'invalid_port',
                'success' => false,
                'message' => 'El puerto debe ser un número entre 1 y 65535'
            ];
        }

        // Restricción de tipos de validación permitidos
        if (!in_array($type, ['admin', 'basic'], true)) {
            return [
                'status'  => 'invalid_type',
                'success' => false,
                'message' => 'Tipo de usuario no válido. Use "admin" o "basic"'
            ];
        }

        /********************** Si todo esta ok **********************/
        try {

            // Intento de instanciación de conexión SQL
            $DBConn = new DB\SQL(
                'mysql:host=' . $host . ';port=' . (int)$port . ';charset=' . $charset,
                $username,
                $password,
                [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8;']
            );

            // Verificación básica de comunicación con el motor
            $DBConn->exec("SELECT 1;");

            // Validación específica para usuarios con privilegios administrativos
            if ($type === 'admin') {

                // Generación de un nombre aleatorio para una base de datos de prueba
                $testDbName = '__test_install_' . preg_replace('/[^a-f0-9]/', '', uniqid('', true));

                try {
                    // Prueba de creación de base de datos física
                    $DBConn->exec("CREATE DATABASE `$testDbName`");
                    // Prueba de eliminación para limpiar el entorno
                    $DBConn->exec("DROP DATABASE `$testDbName`");
                    $realCreateWorks = true;
                } catch (\Exception $e) {
                    // Intento de limpieza en caso de fallo intermedio
                    try { $DBConn->exec("DROP DATABASE IF EXISTS `$testDbName`"); } catch (\Exception $ignored) {}
                    $realCreateWorks = false;
                }

                // Error si la conexión fue exitosa pero no tiene privilegios suficientes
                if (!$realCreateWorks) {
                    return [
                        'status'  => 'no_create_permission',
                        'success' => false,
                        'message' => 'Usuario válido pero sin permisos CREATE DATABASE'
                    ];
                }

                return [
                    'status'  => 'success',
                    'success' => true,
                    'message' => 'Usuario ADMIN validado correctamente'
                ];
            }

            // Validación específica para usuarios con privilegios operativos básicos
            if ($type === 'basic') {

                // Cambio manual al contexto de la base de datos 'mysql'
                $DBConn->exec("USE `mysql` || SELECT 1;");

                // Generación de nombre único para tabla temporal
                $testTable = '__test_perm_' . preg_replace('/[^a-f0-9]/', '', uniqid('', true));

                try {
                    // Prueba del ciclo de vida de una tabla temporal (CRUD básico)
                    $DBConn->exec("CREATE TEMPORARY TABLE `$testTable` (id INT)");
                    $DBConn->exec("INSERT INTO `$testTable` (id) VALUES (1)");
                    $DBConn->exec("SELECT * FROM `$testTable` LIMIT 1");
                    $DBConn->exec("DELETE FROM `$testTable` WHERE id = 1");
                    $DBConn->exec("DROP TEMPORARY TABLE IF EXISTS `$testTable`");
                    $basicPermissionsOK = true;
                } catch (\Exception $e) {
                    // Limpieza preventiva
                    try { $DBConn->exec("DROP TEMPORARY TABLE IF EXISTS `$testTable`"); } catch (\Exception $ignored) {}
                    $basicPermissionsOK = false;
                }

                // Error si faltan permisos de manipulación de datos
                if (!$basicPermissionsOK) {
                    return [
                        'status'  => 'no_basic_permissions',
                        'success' => false,
                        'message' => 'Usuario válido pero sin permisos SELECT/INSERT/DELETE'
                    ];
                }

                return [
                    'status'  => 'success',
                    'success' => true,
                    'message' => 'Usuario BASIC validado correctamente'
                ];
            }

            return [
                'status'  => 'unknown_error',
                'success' => false,
                'message' => 'Error desconocido'
            ];

        } catch (\PDOException $e) {

            $message = $e->getMessage();

            // Mapeo de errores comunes de PDO a estados de la aplicación
            if (str_contains($message, '2002') || str_contains($message, '2003')) {
                return [
                    'status'  => 'server_unreachable',
                    'success' => false,
                    'message' => 'No se puede conectar al servidor MySQL'
                ];
            }

            if (str_contains($message, '1045')) {
                return [
                    'status'  => 'access_denied',
                    'success' => false,
                    'message' => 'Usuario o contraseña incorrectos'
                ];
            }

            if (str_contains($message, '1044')) {
                return [
                    'status'  => 'db_access_denied',
                    'success' => false,
                    'message' => 'Acceso denegado a la base de datos especificada'
                ];
            }

            return [
                'status'  => 'unknown_error',
                'success' => false,
                'message' => $message
            ];

        } catch (\Exception $e) {

            // Captura de errores generales de lógica o instanciación
            return [
                'status'  => 'unknown_error',
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

    }

    /************************************************************************************************************/
	/**
     * Valida la disponibilidad y el formato de un nombre para una base de datos.
     * * Comprueba restricciones de longitud, caracteres permitidos, nombres reservados
     * y consulta al servidor si el nombre ya se encuentra en uso.
     *
     * @param string $host Host de conexión.
     * @param string $username Usuario de conexión.
     * @param string $password Contraseña de conexión.
     * @param string|int $port Puerto de conexión.
     * @param string $charset Juego de caracteres.
     * @param string $dbName Nombre de la base de datos a verificar.
     *
     * @return array Resultado con claves 'status', 'success' y 'message'.
	 *
	 * @example
	 * ```php
	 * ```
	 *
     */
    public function validateDatabase($host, $username, $password, $port, $charset, $dbName): array {

        /**********************  Validaciones   **********************/
        // Validación de campos obligatorios
        if(!isset($host) || $host==''){          return ['success' => false, 'message' => 'No hay datos en $host'];}
        if(!isset($username) || $username==''){  return ['success' => false, 'message' => 'No hay datos en $username'];}
        if(!isset($password) || $password==''){  return ['success' => false, 'message' => 'No hay datos en $password'];}
        if(!isset($port) || $port==''){          return ['success' => false, 'message' => 'No hay datos en $port'];}
        if(!isset($charset) || $charset==''){    return ['success' => false, 'message' => 'No hay datos en $charset'];}
        if(!isset($dbName) || $dbName==''){      return ['success' => false, 'message' => 'No hay datos en $dbName'];}

        // Control de longitud según estándares de identificadores de motores SQL
        if (strlen($dbName) < 3 || strlen($dbName) > 64) {
            return [
                'status'  => 'invalid_length',
                'success' => false,
                'message' => 'El nombre debe tener entre 3 y 64 caracteres'
            ];
        }

        // Restricción de caracteres para prevenir inyección o nombres inválidos
        if (!preg_match('/^[A-Za-z0-9_]+$/', $dbName)) {
            return [
                'status'  => 'invalid_format',
                'success' => false,
                'message' => 'El nombre solo puede contener letras, números y guiones bajos'
            ];
        }

        // Lista negra de nombres de bases de datos internas del motor
        $reservedNames = [
            'mysql',
            'information_schema',
            'performance_schema',
            'sys'
        ];
        if (in_array(strtolower($dbName), $reservedNames, true)) {
            return [
                'status'  => 'reserved_name',
                'success' => false,
                'message' => 'El nombre corresponde a una base de datos reservada del sistema'
            ];
        }

        /********************** Si todo esta ok **********************/
        try {

            // Establecimiento de conexión para consulta remota
            $DBConn = new DB\SQL(
                'mysql:host='.$host.';port='.$port.';charset='.$charset,
                $username,
                $password,
                array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8;')
            );

            // Limpieza básica del nombre de la base de datos
            $safeDbName = str_replace('`', '', $dbName);

            // Consulta al motor para verificar existencia previa
            $query  = "SHOW DATABASES LIKE '".$safeDbName."'";
            $result = $DBConn->exec($query);

            if (!empty($result)) {
                return [
                    'status'  => 'database_exists',
                    'success' => false,
                    'message' => 'Ya existe una base de datos con ese nombre'
                ];
            }

            return [
                'status'  => 'valid',
                'success' => true,
                'message' => 'Nombre válido y disponible'
            ];

        } catch (\Exception $e) {

            return [
                'status'  => 'connection_error',
                'success' => false,
                'message' => 'Error al conectar al servidor: '.$e->getMessage()
            ];
        }
    }

    /************************************************************************************************************/
	/**
     * Verifica la existencia y accesibilidad de un archivo en el sistema de archivos local.
     *
     * @param string $PathFile Ruta absoluta o relativa al archivo.
     *
     * @return array Resultado con claves 'success', 'message' y 'path'.
	 *
	 * @example
	 * ```php
	 * $DataValidations->validatePathFile('Path');
	 * ```
	 *
     */
    public function validatePathFile($PathFile): array {

        /**********************  Validaciones   **********************/
        // Comprobación de parámetro obligatorio
        if(!isset($PathFile) || $PathFile==''){  return ['success' => false,'message' => 'No hay datos en $PathFile'];}

        // Verificación de existencia física en el disco
        if (!file_exists($PathFile)) {
            return [
                'success' => false,
                'message' => 'Archivo no encontrado',
                'path'    => $PathFile
            ];
        }

        // Verificación de permisos de lectura para el proceso actual
        if (!is_readable($PathFile)) {
            return [
                'success' => false,
                'message' => 'Archivo no es legible',
                'path'    => $PathFile
            ];
        }

        /**********************  Retorno datos  **********************/
        return [
            'success' => true,
            'message' => 'Archivo válido',
            'path'    => $PathFile
        ];
    }

    /************************************************************************************************************/
	/**
     * Valida una query SQL MySQL con distintos niveles de seguridad.
     *
     * CAPAS DE VALIDACIÓN (en orden de ejecución):
     *  1. Null bytes
     *  2. Longitud máxima
     *  3. Normalización (strip comentarios, espacios)
     *  4. Extracción de literales (simple, doble, backtick)
     *  5. Múltiples sentencias
     *  6. Detección de tipo
     *  7. Lista deny global
     *  8. Funciones peligrosas globales (SLEEP, BENCHMARK, encoding)
     *  9. Modo strict  / safe / paranoid
     * 10. SELECT sin FROM
     * 11. Tautologías (OR 1=1, OR 'x'='x', OR true, OR 2>1, IS NOT NULL)
     * 12. Whitelist de tablas (opcional)
     *
     * @param string $query   Query SQL a validar.
     * @param array  $options {
     *     @type string   $mode       Nivel de seguridad: default|strict|safe|paranoid.
     *                                - default  : validaciones básicas.
     *                                - strict   : solo tipos en $allowed; keywords en $deny bloqueadas globalmente.
     *                                - safe     : anti-mezcla de operaciones destructivas + subqueries peligrosas.
     *                                - paranoid : solo SELECT plano sin JOIN/UNION/subqueries.
     *     @type array    $allowed    Tipos de query permitidos (usado en strict).
     *                                Default: ['SELECT','INSERT','UPDATE','DELETE'].
     *     @type array    $deny       Keywords/tipos siempre bloqueados sin importar el modo.
     *                                Default: [].
     *     @type bool     $single     Si true, bloquea múltiples sentencias separadas por ';'.
     *                                Default: true.
     *     @type int      $max_length Longitud máxima permitida de la query en caracteres.
     *                                Default: 10000.
     *     @type array    $tables     Whitelist de tablas permitidas en FROM (vacío = sin restricción).
     *                                Default: [].
     * }
     *
     * @return array {
     *     @type bool        $valid  true si la query pasó todas las validaciones.
     *     @type string|null $error  Mensaje de error legible, null si válida.
     *     @type string|null $type   Tipo detectado (SELECT, INSERT, etc.), null si no detectado.
     * }
     */
    public function validateSQL(string $query, array $options = []): array {
        // =========================================================================
        // Opciones con sus defaults
        // =========================================================================
        $mode             = $options['mode']       ?? 'default';
        $allowed          = $options['allowed']    ?? ['SELECT', 'INSERT', 'UPDATE', 'DELETE'];
        $deny             = $options['deny']       ?? ['REPLACE', 'DROP', 'ALTER', 'CREATE', 'TRUNCATE'];
        $single           = $options['single']     ?? true;
        $maxLength        = $options['max_length'] ?? 10000;
        $tables           = $options['tables']     ?? [];
        $blacklistTables  = $options['blacklist_tables']  ?? [];
        $sensitiveColumns = $options['sensitive_columns'] ?? ['password', 'passwd', 'token'];

        // Closure de respuesta negativa — evita repetir la estructura del array.
        $fail = fn(string $msg, ?string $type = null): array => [
            'valid' => false,
            'error' => $msg,
            'type'  => $type,
        ];

        // =========================================================================
        // [1] NULL BYTES
        // Un null byte (\0) puede truncar el análisis en ciertas implementaciones
        // de C subyacentes a PHP/MySQL y se usa para evadir filtros de texto.
        // Ejemplo de ataque: "SELECT *\0 FROM users-- "
        // =========================================================================
        if (str_contains($query, "\0")) {
            return $fail('Query contiene caracteres nulos');
        }

        // =========================================================================
        // [2] LONGITUD MÁXIMA
        // Previene DoS por queries gigantes que disparen backtracking catastrófico
        // en los regex posteriores (ReDoS) o saturen memoria.
        // =========================================================================
        if (strlen($query) > $maxLength) {
            return $fail("Query excede el límite de {$maxLength} caracteres");
        }

        // =========================================================================
        // [3] NORMALIZACIÓN
        // Orden importante:
        //   a) trim()            — elimina espacios externos.
        //   b) strip comentarios — elimina -- , # y /* */ ANTES de analizar.
        //      Sin este paso, "SELECT 1 -- comentario falso" podría romper
        //      regex posteriores o esconder keywords.
        //   c) colapsar espacios — un único espacio entre tokens facilita
        //      los patrones \b en los regex siguientes.
        //   d) rtrim ';'         — el punto y coma final es ruido para la
        //      detección de múltiples sentencias.
        // =========================================================================
        $query = trim($query);
        $query = preg_replace('/(--[^\n]*$)|(#[^\n]*$)/m', '', $query); // comentarios de línea
        $query = preg_replace('/\/\*.*?\*\//s', '', $query);             // comentarios de bloque
        $query = preg_replace('/\s+/', ' ', $query);                     // colapsar whitespace
        $query = rtrim($query, '; ');

        if ($query === '') {
            return $fail('Query vacía');
        }

        // =========================================================================
        // [4] EXTRACCIÓN DE LITERALES STRING → $sanitized
        //
        // PROBLEMA que resuelve:
        //   WHERE name = 'DROP TABLE users'  → antes disparaba falso positivo.
        //   WHERE name = "SLEEP(5)"          → idem con comillas dobles.
        //
        // SOLUCIÓN:
        //   Reemplazar cada literal string por un placeholder neutral __STR_N__
        //   antes de cualquier análisis estructural. Así los regex de seguridad
        //   nunca "ven" el contenido de los valores, solo la estructura SQL.
        //
        // COBERTURA de comillas (punto 1 del plan de mejoras):
        //   '...'  — estándar SQL / MySQL
        //   "..."  — MySQL con ANSI_QUOTES desactivado
        //   `...`  — identifiers MySQL (nombres de tabla/columna)
        //
        // El regex maneja escapes internos:
        //   \'  dentro de '...'
        //   \"  dentro de "..."
        //   \`  dentro de `...`
        // =========================================================================
        $literals  = [];
        $sanitized = preg_replace_callback(
            "/'(?:[^'\\\\]|\\\\.)*'|\"(?:[^\"\\\\]|\\\\.)*\"|`(?:[^`\\\\]|\\\\.)*`/",
            function (array $m) use (&$literals): string {
                $placeholder           = '__STR_' . count($literals) . '__';
                $literals[$placeholder] = $m[0];
                return $placeholder;
            },
            $query
        );

        // =========================================================================
        // [5] MÚLTIPLES SENTENCIAS
        // Detecta patrones como: "SELECT 1; DROP TABLE x"
        // Se analiza sobre $sanitized para que un ';' dentro de un literal
        // ('val;ue') no dispare un falso positivo.
        // =========================================================================
        if ($single && preg_match('/;.+\S/', $sanitized)) {
            return $fail('Múltiples sentencias no permitidas');
        }

        // =========================================================================
        // [6] DETECCIÓN DE TIPO
        // Solo acepta los verbos SQL reconocidos al inicio de la query.
        // Cualquier otra cosa (o query vacía post-normalización) es inválida.
        // =========================================================================
        $knownTypes = 'SELECT|INSERT|UPDATE|DELETE|REPLACE|DROP|ALTER|CREATE|TRUNCATE';
        if (!preg_match('/^(' . $knownTypes . ')\b/i', $sanitized, $match)) {
            return $fail('Tipo de query no reconocido');
        }
        $type = strtoupper($match[1]);

        // =========================================================================
        // [7] LISTA DENY GLOBAL
        // Se evalúa ANTES de cualquier modo para que $deny actúe como lista negra
        // absoluta independiente del modo activo.
        // Ejemplo: ['DROP', 'TRUNCATE'] bloqueará esos tipos siempre.
        // =========================================================================
        if (!empty($deny) && in_array($type, $deny, true)) {
            return $fail("Tipo '$type' está en la lista de denegación", $type);
        }

        // =========================================================================
        // [8] FUNCIONES PELIGROSAS GLOBALES (aplican a todos los modos)
        //
        // 8a. TIMING / BLIND SQLi
        //     SLEEP y BENCHMARK se usan para inferir datos mediante retardos.
        //     Ejemplo: WHERE IF(1=1, SLEEP(5), 0)
        //
        // 8b. FUNCIONES DE ENCODING / OFUSCACIÓN (punto 2 del plan)
        //     Permiten ofuscar keywords para evadir los regex.
        //     Ejemplo: WHERE id = CHAR(49,32,79,82)  →  "1 OR"
        //              WHERE id = 0x44524f50         →  "DROP"
        //     Se detectan por el patrón FUNCION( para no bloquear
        //     nombres de columna que casualmente se llamen 'hex', etc.
        // =========================================================================

        // 8a — Timing/blind
        if (preg_match('/\b(SLEEP|BENCHMARK|WAIT\s+FOR\s+DELAY|PG_SLEEP)\s*\(/i', $sanitized)) {
            return $fail('Query contiene funciones de timing prohibidas', $type);
        }

        // 8b — Encoding/ofuscación
        if (preg_match('/\b(CHAR|HEX|UNHEX|ASCII|ORD|CONV|BIN)\s*\(/i', $sanitized)) {
            return $fail('Query contiene funciones de encoding/ofuscación prohibidas', $type);
        }

        // =========================================================================
        // [9a] MODO STRICT
        //
        // Dos controles:
        //   A) El tipo principal debe estar en $allowed.
        //   B) Ninguna keyword de $deny puede aparecer en cualquier parte
        //      de la query sanitizada (no solo como tipo principal).
        //      Esto bloquea, p.ej., un SELECT que contenga DROP en una subquery.
        // =========================================================================
        if ($mode === 'strict') {
            if (!in_array($type, $allowed, true)) {
                return $fail("Tipo '$type' no permitido en modo strict", $type);
            }

            foreach ($deny as $kw) {
                if (preg_match('/\b' . preg_quote($kw, '/') . '\b/i', $sanitized)) {
                    return $fail("Keyword '$kw' no permitida en modo strict", $type);
                }
            }
        }

        // =========================================================================
        // [9b] MODO SAFE
        //
        // Control A — Mezcla de operaciones destructivas (punto 7 del plan):
        //   Lista ampliada con REPLACE, CREATE, RENAME, LOCK además de los
        //   originales. Si aparecen más de una de estas keywords en la query,
        //   es señal de una query compuesta peligrosa.
        //
        // Control B — Subqueries destructivas en IN/EXISTS/ANY/ALL:
        //   Cubre el caso: WHERE id IN (SELECT id FROM x WHERE ... DELETE ...)
        //
        // Control C — SELECT con operaciones destructivas fuera de subqueries:
        //   Cubre inyecciones directas en el cuerpo del SELECT.
        // =========================================================================
        if ($mode === 'safe') {
            // A — keywords destructivas (lista ampliada)
            $dangerKeywords = [
                'INSERT', 'UPDATE', 'DELETE', 'DROP', 'ALTER',
                'TRUNCATE', 'REPLACE', 'CREATE', 'RENAME', 'LOCK',
            ];

            $found = array_values(array_filter(
                $dangerKeywords,
                fn(string $kw): bool => (bool) preg_match('/\b' . $kw . '\b/i', $sanitized)
            ));

            if (count($found) > 1) {
                return $fail(
                    'Query mezcla múltiples operaciones peligrosas: ' . implode(', ', $found),
                    $type
                );
            }

            // B — Operación destructiva dentro de subquery IN/EXISTS/ANY/ALL
            if (preg_match('/\b(?:IN|EXISTS|ANY|ALL)\s*\(.*\b(?:DELETE|UPDATE|DROP|INSERT)\b/is', $sanitized)) {
                return $fail('Subquery contiene operación destructiva', $type);
            }

            // C — SELECT cuyo cuerpo contiene verbos destructivos
            if ($type === 'SELECT' && preg_match('/\b(UPDATE|DELETE|INSERT|DROP)\b/i', $sanitized)) {
                return $fail('SELECT contiene operaciones peligrosas', $type);
            }
        }

        // =========================================================================
        // [9c] MODO PARANOID
        //
        // El modo más restrictivo: solo acepta SELECT plano.
        //
        // Control A — Solo SELECT.
        // Control B — Sin subqueries anidadas: bloquea ( ... SELECT ...
        // Control C — Sin UNION ni ninguna variante de JOIN ni INTO.
        //   UNION permite extraer datos de otras tablas.
        //   JOIN expone relaciones entre tablas.
        //   INTO permite escribir archivos (INTO OUTFILE).
        // Control D — Sin funciones de exfiltración/timing (ya cubiertas
        //   globalmente en [8], se repite aquí como documentación explícita
        //   del contrato del modo paranoid).
        // =========================================================================
        if ($mode === 'paranoid') {
            // A — Solo SELECT
            if ($type !== 'SELECT') {
                return $fail("Modo paranoid solo permite SELECT, se recibió '$type'", $type);
            }

            // B — Sin subqueries
            if (preg_match('/\(.*\bSELECT\b/i', $sanitized)) {
                return $fail('Modo paranoid no permite subqueries', $type);
            }

            // C — Sin UNION, JOIN (cualquier variante), INTO
            $forbiddenClauses = implode('|', [
                'UNION',
                'INNER\s+JOIN', 'LEFT\s+JOIN', 'RIGHT\s+JOIN',
                'FULL\s+JOIN',  'CROSS\s+JOIN', 'JOIN',
                'INTO',
            ]);
            if (preg_match('/\b(?:' . $forbiddenClauses . ')\b/i', $sanitized)) {
                return $fail('Modo paranoid no permite UNION, JOIN ni INTO', $type);
            }
        }

        // =========================================================================
        // [10] SELECT SIN FROM
        // Un SELECT sin FROM es casi siempre un error o una inyección de prueba
        // (p.ej. "SELECT 1", "SELECT version()").
        // Se evalúa sobre $sanitized para ignorar 'FROM' dentro de literales.
        // =========================================================================
        if ($type === 'SELECT' && !preg_match('/\bFROM\b/i', $sanitized)) {
            return $fail('SELECT sin FROM (posible query inválida)', $type);
        }

        // =========================================================================
        // [11] TAUTOLOGÍAS (anti OR 1=1 y variantes)
        //
        // Se opera sobre $sanitized: los literales ya fueron extraídos, por lo
        // que un WHERE name = '1 OR 1=1' NO dispara esta validación (correcto),
        // pero un WHERE id = 1 OR 1=1 SÍ la dispara (correcto).
        //
        // Variantes cubiertas (punto 3 del plan):
        //   A) Igualdad numérica trivial   : OR 1=1, OR 2=2
        //   B) Igualdad de identificadores : OR x=x (mismo token ambos lados)
        //   C) Booleano directo            : OR true, OR false (siempre sospechoso en WHERE)
        //   D) Comparación numérica obvia  : OR 2>1, OR 1<2
        //   E) IS NOT NULL incondicional   : OR id IS NOT NULL (siempre true si hay filas)
        // =========================================================================

        // A — OR/AND con número igual a sí mismo: OR 1=1, AND 2=2
        if (preg_match('/\b(?:OR|AND)\b\s*[\'"]?\d+[\'"]?\s*=\s*[\'"]?\d+[\'"]?/i', $sanitized)) {
            return $fail('Posible inyección SQL: condición numérica tautológica', $type);
        }

        // B — OR/AND con mismo identificador ambos lados: OR x=x
        if (preg_match('/\b(?:OR|AND)\b\s*(\w+)\s*=\s*\1\b/i', $sanitized)) {
            return $fail('Posible inyección SQL: condición tautológica (OR x=x)', $type);
        }

        // C — OR/AND con booleano literal: OR true, OR false
        if (preg_match('/\b(?:OR|AND)\b\s*\b(?:true|false)\b/i', $sanitized)) {
            return $fail('Posible inyección SQL: condición booleana literal', $type);
        }

        // D — OR/AND con comparación numérica obvia: OR 2>1, OR 1<2
        if (preg_match('/\b(?:OR|AND)\b\s*\d+\s*[><]\s*\d+/i', $sanitized)) {
            return $fail('Posible inyección SQL: comparación numérica siempre verdadera', $type);
        }

        // E — OR/AND <columna> IS NOT NULL (sospechoso en contexto de inyección)
        if (preg_match('/\b(?:OR|AND)\b\s*\w+\s+IS\s+NOT\s+NULL/i', $sanitized)) {
            return $fail('Posible inyección SQL: condición IS NOT NULL sospechosa', $type);
        }

        // =========================================================================
        // [12] WHITELIST DE TABLAS (punto 9 del plan)
        //
        // Si $tables no está vacío, extrae el nombre de tabla inmediatamente
        // después de FROM y verifica que esté en la whitelist.
        //
        // Limitación conocida: solo valida la primera tabla del FROM.
        // Para queries con múltiples tablas (JOIN, subqueries) se recomienda
        // usar esto solo en modo paranoid donde JOIN/subqueries ya están bloqueados.
        // =========================================================================
        if (!empty($tables)) {
            if (!preg_match('/\bFROM\s+(\w+)/i', $sanitized, $tableMatch)) {
                return $fail('No se pudo determinar la tabla de destino', $type);
            }

            $targetTable = strtolower($tableMatch[1]);
            $allowedTables = array_map('strtolower', $tables);

            if (!in_array($targetTable, $allowedTables, true)) {
                return $fail("Tabla '$targetTable' no está en la whitelist de tablas permitidas", $type);
            }
        }
        // =========================================================================
        // [13] BLACKLIST DE TABLAS SENSIBLES (control contextual)
        //
        // Objetivo:
        //  - Evitar acceso directo o modificación de tablas sensibles (ej: users)
        //  - Permitir JOIN siempre que NO se acceda a columnas sensibles
        //
        // Opciones nuevas:
        //   @type array $blacklist_tables   Tablas sensibles restringidas.
        //   @type array $sensitive_columns  Columnas prohibidas (ej: password).
        //
        // Ejemplo:
        //   'blacklist_tables'  => ['users'],
        //   'sensitive_columns' => ['password', 'passwd', 'token']
        // =========================================================================
        if (!empty($blacklistTables)) {

            $lowerQuery = strtolower($sanitized);
            $blacklistTables = array_map('strtolower', $blacklistTables);

            // ---------------------------------------------------------------------
            // A — Detectar tablas involucradas (FROM + JOIN)
            // ---------------------------------------------------------------------
            preg_match_all('/\b(?:from|join)\s+(\w+)/i', $lowerQuery, $matches);
            $usedTables = array_map('strtolower', $matches[1] ?? []);

            $intersectTables = array_intersect($usedTables, $blacklistTables);

            if (!empty($intersectTables)) {

                // -----------------------------------------------------------------
                // B — Bloquear modificaciones directas
                // -----------------------------------------------------------------
                if (in_array($type, ['UPDATE', 'DELETE', 'INSERT', 'REPLACE'], true)) {

                    // Detectar tabla objetivo principal
                    if (preg_match('/\b(?:update|into)\s+(\w+)/i', $lowerQuery, $mainTableMatch)) {
                        $mainTable = strtolower($mainTableMatch[1]);

                        if (in_array($mainTable, $blacklistTables, true)) {
                            return $fail("Modificación directa a tabla sensible '$mainTable' no permitida", $type);
                        }
                    }
                }

                // -----------------------------------------------------------------
                // C — SELECT directo sin JOIN (acceso completo)
                // -----------------------------------------------------------------
                if ($type === 'SELECT') {

                    $hasJoin = preg_match('/\bjoin\b/i', $lowerQuery);

                    if (!$hasJoin) {
                        return $fail(
                            'Acceso directo a tabla sensible no permitido (use JOIN controlado)',
                            $type
                        );
                    }

                    // -----------------------------------------------------------------
                    // D — Detección avanzada de columnas sensibles (soporte alias real)
                    // -----------------------------------------------------------------

                    // -------------------------------------------------------------
                    // 1. Construir mapa alias → tabla
                    //    Soporta:
                    //      FROM users u
                    //      FROM users AS u
                    //      JOIN users u2
                    // -------------------------------------------------------------
                    $aliasMap = [];

                    // FROM + JOIN con alias
                    preg_match_all(
                        '/\b(from|join)\s+(\w+)(?:\s+as)?\s+(\w+)/i',
                        $lowerQuery,
                        $aliasMatches,
                        PREG_SET_ORDER
                    );

                    foreach ($aliasMatches as $m) {
                        $table = strtolower($m[2]);
                        $alias = strtolower($m[3]);
                        $aliasMap[$alias] = $table;
                    }

                    // También incluir tablas sin alias (alias implícito = nombre tabla)
                    foreach ($usedTables as $tbl) {
                        $aliasMap[$tbl] = $tbl;
                    }

                    // -------------------------------------------------------------
                    // 2. Detectar acceso a columnas con alias (u.password)
                    // -------------------------------------------------------------
                    foreach ($aliasMap as $alias => $table) {

                        // Solo validar tablas sensibles
                        if (!in_array($table, $blacklistTables, true)) {
                            continue;
                        }

                        foreach ($sensitiveColumns as $col) {

                            // Detecta:
                            //   u.password
                            //   users.password
                            if (preg_match('/\b' . preg_quote($alias, '/') . '\.' . preg_quote($col, '/') . '\b/i', $lowerQuery)) {
                                return $fail(
                                    "Acceso a columna sensible '{$table}.{$col}' mediante alias '{$alias}' no permitido",
                                    $type
                                );
                            }
                        }

                        // ---------------------------------------------------------
                        // 3. Detectar SELECT alias.* (ej: u.*)
                        // ---------------------------------------------------------
                        if (preg_match('/\b' . preg_quote($alias, '/') . '\.\*/i', $lowerQuery)) {
                            return $fail(
                                "Acceso wildcard '{$alias}.*' a tabla sensible '{$table}' no permitido",
                                $type
                            );
                        }
                    }

                    // -------------------------------------------------------------
                    // 4. Fallback (por si no usan alias)
                    // -------------------------------------------------------------
                    foreach ($sensitiveColumns as $col) {
                        if (preg_match('/\b' . preg_quote(strtolower($col), '/') . '\b/i', $lowerQuery)) {
                            return $fail(
                                "Acceso a columna sensible '$col' no permitido",
                                $type
                            );
                        }
                    }
                }
            }
        }

        // =========================================================================
        // Query válida
        // =========================================================================
        return ['valid' => true, 'error' => null, 'type' => $type];
    }



}
