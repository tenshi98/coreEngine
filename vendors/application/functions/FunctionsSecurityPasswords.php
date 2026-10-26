<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsSecurityPasswords {

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
	 * Instancia las dependencias internas de la clase de contraseñas.
	 *
	 * Carga FunctionsDataValidations para validar enteros y booleanos
	 * recibidos como parámetros de generación y hash.
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * $SecurityPasswords = new FunctionsSecurityPasswords();
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
     * Genera una contraseña aleatoria de longitud específica y tipo (numérico o alfanumérico).
     * * Utiliza un pool de caracteres basado en el tipo seleccionado, mezcla el contenido
     * y extrae una subcadena del largo solicitado.
     *
     * @param int $longitud Largo de la contraseña generada.
     * @param string $tipo Tipo de caracteres: 'numerico' o 'alfanumerico'.
     *
     * @return array La contraseña generada o un mensaje de error en caso de validación fallida.
	 *
	 * @example
	 * ```php
	 * $SecurityPasswords->generarPassword(10,'numerico');     // Devuelve valores numeros aleatoreos
	 * $SecurityPasswords->generarPassword(10,'alfanumerico'); // Devuelve valores alfanumerico aleatoreos
	 * ```
	 *
     */
    public function generarPassword($longitud = 15, $tipo = 'alfanumerico'): array {

        /********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateInteger($longitud, 'longitud');
		// Si la validación devuelve un valor distinto a true, se da un valor por defecto
		if ($dataVal !== true) { $longitud = 10; }
        // Si la longitud esta fuera de los parametros
        if ($longitud < 1 || $longitud > 15) {
            $longitud = 10;
        }

        // Validación pertenencia de tipo a los permitidos
        if (!in_array($tipo, ['alfanumerico', 'numerico'], true)) {
            return ['success' => false, 'error' => 'El dato ingresado en tipo esta fuera de parámetros esperados'];
        }

        /********************** Si todo esta ok **********************/
        // Definir los alfabetos disponibles para la generación
        $alfabetos = [
            'alfanumerico' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
            'numerico'     => '0123456789',
        ];

        // Se ontienen los datos
        $alfabeto         = $alfabetos[$tipo];
        $longitudAlfabeto = strlen($alfabeto);

        // Variable
        $password = '';

        // Mezclar aleatoriamente los caracteres del pool
        for ($i = 0; $i < $longitud; $i++) {
            $password .= $alfabeto[random_int(0, $longitudAlfabeto - 1)];
        }

        /********************** Retorno datos  **********************/
        // Retorna la subcadena truncada a la longitud deseada
        return ['success' => true, 'data' => $password];

    }

    /******************************************************************************/
    /**
     * Genera una cadena de caracteres aleatorios con opciones avanzadas de personalización.
     * * Permite omitir caracteres visualmente ambiguos (como 'O' y '0'), incluir símbolos
     * y garantizar que no existan caracteres duplicados en la cadena resultante.
     *
     * @param int $longitud Largo de la palabra generada.
     * @param bool $lecturaAmigable Si es true, remueve caracteres similares (O/0, l/1, etc.).
     * @param bool $incluirSimbolos Si es true, añade caracteres especiales (solo si lecturaAmigable es false).
     * @param bool $sinDuplicados Si es true, asegura que cada carácter aparezca solo una vez.
     *
     * @return array Resultado con la cadena aleatoria en 'data' (['success' => true, 'data' => string]) o error en 'error'.
     * @throws LengthException Si se solicita una longitud mayor a los caracteres únicos disponibles sin duplicados.
	 *
	 * @example
	 * ```php
	 * $SecurityPasswords->caracteresRandom(16, true,  false, false);  // Devuelve valores aleatoreos
	 * $SecurityPasswords->caracteresRandom(16, true,  true,  false);  // Devuelve valores aleatoreos
	 * $SecurityPasswords->caracteresRandom(16, true,  true,  true);   // Devuelve valores aleatoreos
	 * $SecurityPasswords->caracteresRandom(16, false, true,  false);  // Devuelve valores aleatoreos
	 * $SecurityPasswords->caracteresRandom(16, false, true,  true);   // Devuelve valores aleatoreos
	 * $SecurityPasswords->caracteresRandom(16, false, false, true);   // Devuelve valores aleatoreos
	 * $SecurityPasswords->caracteresRandom(16, true,  false, true);   // Devuelve valores aleatoreos
	 * ```
	 *
     */
    public function caracteresRandom($longitud = 16, $lecturaAmigable = true, $incluirSimbolos = false, $sinDuplicados = false): array {

        /********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateInteger($longitud, 'longitud');
		$dataVal_2 = $this->_validateBool($lecturaAmigable, 'lecturaAmigable');
		$dataVal_3 = $this->_validateBool($incluirSimbolos, 'incluirSimbolos');
		$dataVal_4 = $this->_validateBool($sinDuplicados, 'sinDuplicados');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
        if($dataVal_1 !== true){  return ['success' => false, 'error' => $dataVal_1]; }
        if($dataVal_2 !== true){  return ['success' => false, 'error' => $dataVal_2]; }
        if($dataVal_3 !== true){  return ['success' => false, 'error' => $dataVal_3]; }
        if($dataVal_4 !== true){  return ['success' => false, 'error' => $dataVal_4]; }

        /********************** Si todo esta ok **********************/
        // Definición de sets de caracteres
        $caracteres_legibles = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefhjkmnprstuvwxyz23456789';
        $caracteres_todos    = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
        $simbolos            = '!@#$%^&*()~_-=+{}[]|:;<>,.?/"\'\\`';

        // Selección del conjunto base según la preferencia de legibilidad
        $pool = $lecturaAmigable ? $caracteres_legibles : $caracteres_todos;

        // Adición de símbolos si se solicita y no se requiere lectura amigable
        if (!$lecturaAmigable && $incluirSimbolos) {
            $pool .= $simbolos;
        }

        // Lógica de generación con duplicados permitidos (estándar)
        if (!$sinDuplicados) {
            $repeticiones = (int) ceil($longitud / strlen($pool));
            return ['success' => true, 'data' => substr(str_shuffle(str_repeat($pool, $repeticiones)), 0, $longitud)];
        }

        // Lógica de generación sin duplicados
        $caracteres_unicos_pool = str_split($pool);
        $total_unicos           = count(array_unique($caracteres_unicos_pool));
        // Verificar que la longitud no supere la cantidad de caracteres únicos disponibles
        if ($longitud > $total_unicos) {
            throw new LengthException("La longitud solicitada ($longitud) excede los caracteres únicos disponibles ($total_unicos).");
        }

        $caracteres = str_split($pool);

        // Algoritmo de mezcla Fisher-Yates utilizando un generador criptográficamente seguro
        for ($i = count($caracteres) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$caracteres[$i], $caracteres[$j]] = [$caracteres[$j], $caracteres[$i]];
        }

        /********************** Retorno datos  **********************/
        // Extrae la porción solicitada del array mezclado y lo convierte a string
        return ['success' => true, 'data' => implode('', array_slice($caracteres, 0, $longitud))];

    }

    /******************************************************************************/
    /**
     * Genera un token aleatorio codificado en formato hexadecimal.
     * * Utiliza la librería OpenSSL para generar bytes pseudo-aleatorios seguros,
     * lo cual es ideal para tokens de sesión o llaves de seguridad.
     *
     * @param int $longitud Longitud total de la cadena hexadecimal resultante.
     *
     * @return array Token en formato hexadecimal dentro de la clave 'data', o el error en 'error'.
	 *
	 * @example
	 * ```php
	 * $SecurityPasswords->tokenBin2Hex(25); // Devuelve valores aleatoreos
	 * ```
	 *
     */
    public function tokenBin2Hex($longitud): array {

        /********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateInteger($longitud, 'longitud');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return ['success' => false, 'error' => $dataVal]; }

        /********************** Si todo esta ok **********************/
        // Calcula la cantidad de bytes necesarios (cada byte produce 2 caracteres hexadecimales)
        $bytesNeeded = (int)(($longitud - ($longitud % 2)) / 2);

        /********************** Retorno datos  **********************/
        // Genera bytes aleatorios y los convierte a representación hexadecimal
        return ['success' => true, 'data' => bin2hex(openssl_random_pseudo_bytes($bytesNeeded))];

    }

    /******************************************************************************/
    /**
     * Crea un hash de alta seguridad para contraseñas utilizando el algoritmo BCRYPT.
     * * Implementa un factor de costo (work factor) de 12, optimizado para el hardware actual,
     * lo que dificulta ataques de fuerza bruta.
     *
     * @param string $Texto La contraseña o cadena en texto plano a procesar.
     * @param int $costo Factor de costo BCRYPT (por defecto: 12; 2^n iteraciones).
     *
     * @return array El hash generado en la clave 'data' (o el error en 'error'), listo para almacenar.
	 *
	 * @example
	 * ```php
	 * $SecurityPasswords->hashCreate('MiClave123');
	 * // devuelve ['success' => true, 'data' => '$2y$12$...'] o ['success' => false, 'error' => 'Sin datos ingresados en Texto']
	 * ```
	 *
     */
    public static function hashCreate($Texto, $costo = 12): array {

        /********************** Validaciones   **********************/
        // Se verifica si esta vacio
        if ($Texto === null || trim((string)$Texto) === '') {
            return ['success' => false, 'error' => 'Sin datos ingresados en Texto'];
        }

        /********************** Si todo esta ok **********************/
        // 'cost' define el número de iteraciones del algoritmo (2^n).
        $options = ['cost' => $costo];

        // password_hash maneja automáticamente la generación de la sal (salt)
        $hash = password_hash($Texto, PASSWORD_BCRYPT, $options);
        // password_hash retorna False si el algoritmo o el costo no son válidos
        if ($hash === false) {
            return ['success' => false, 'error' => 'No se pudo generar el hash'];
        }

        /********************** Retorno datos  **********************/
        return ['success' => true, 'data' => $hash];

    }

    /******************************************************************************/
    /**
     * Verifica si una cadena en texto plano coincide con un hash previamente generado.
     * * Es resistente a ataques de tiempo (timing attacks) y detecta automáticamente
     * el algoritmo y el costo utilizados en el hash proporcionado.
     *
     * @param string $Texto La cadena ingresada (ej: desde un formulario de login).
     * @param string $hash El hash almacenado contra el cual se desea comparar.
     *
     * @return bool True si la contraseña es válida, False en caso contrario.
	 *
	 * @example
	 * ```php
	 * $hash = FunctionsSecurityPasswords::hashCreate('MiClave123')['data'];
	 * FunctionsSecurityPasswords::hashVerify('MiClave123', $hash); // devuelve true
	 * FunctionsSecurityPasswords::hashVerify('OtraClave', $hash);  // devuelve false
	 * ```
	 *
     */
    public static function hashVerify($Texto, $Hash): bool {

        /********************** Validaciones   **********************/
        // Se verifica si esta vacio
        if ($Texto === null || trim((string)$Texto) === '') {
            return false;
        }
        if ($Hash === null || trim((string)$Hash) === '') {
            return false;
        }

        /********************** Si todo esta ok **********************/
        /********************** Retorno datos  **********************/
        // Compara el texto plano con el hash de forma segura
        return password_verify($Texto, $Hash);

    }

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Privados                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
    /******************************************************************************/
	/**
	 * Valida que el dato recibido sea un entero positivo no vacío.
	 *
	 * Comprueba que el valor no sea nulo, vacío ni cero y que sea reconocido
	 * como número entero por FunctionsDataValidations.
	 *
	 * @param mixed $Data Valor a validar (ej: 10, '15').
	 * @param string $Name Nombre del campo recibido, usado en el mensaje de error (ej: 'longitud').
	 *
	 * @return bool|string true si es un entero válido; mensaje de error (string) si no lo es.
	 *
	 * @example
	 * ```php
	 * $this->_validateInteger(10, 'longitud'); // devuelve true
	 * $this->_validateInteger(0, 'longitud');  // devuelve 'Sin datos ingresados en longitud'
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

    /******************************************************************************/
	/**
	 * Valida que el dato recibido sea un booleano no vacío.
	 *
	 * Comprueba que el valor no sea nulo, cadena vacía y que sea de tipo bool.
	 *
	 * @param mixed $Data Valor a validar (true/false).
	 * @param string $Name Nombre del campo recibido, usado en el mensaje de error (ej: 'lecturaAmigable').
	 *
	 * @return bool|string true si es un booleano válido; mensaje de error (string) si no lo es.
	 *
	 * @example
	 * ```php
	 * $this->_validateBool(true, 'lecturaAmigable');  // devuelve true
	 * $this->_validateBool('si', 'lecturaAmigable');  // devuelve 'El dato ingresado en lecturaAmigable esta fuera de parámetros esperados'
	 * ```
	 */
	private function _validateBool($Data, $Name){

		/**********************  Validaciones   **********************/
        // Se verifica si esta vacio
        if ($Data === null || trim((string)$Data) === '') {
            return 'Sin datos ingresados en '.$Name;
        }

        // Validación pertenencia de tipo a los permitidos
        if (!is_bool($Data)) {
            return 'El dato ingresado en '.$Name.' esta fuera de parámetros esperados';
        }

		/**********************  Retorno datos  **********************/
		return true;

	}

}
