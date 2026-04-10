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
     * Genera una contraseña aleatoria de longitud específica y tipo (numérico o alfanumérico).
     * * Utiliza un pool de caracteres basado en el tipo seleccionado, mezcla el contenido
     * y extrae una subcadena del largo solicitado.
     *
     * @param int $longitud Largo de la contraseña generada.
     * @param string $tipo Tipo de caracteres: 'numerico' o 'alfanumerico'.
     * @return string La contraseña generada o un mensaje de error en caso de validación fallida.
     */
    public function generarPassword($longitud, $tipo): string {
        /*
        *=================================================    Modo de uso  =================================================
        * 	//Numerico:
        * 	$SecurityPasswords->generarPassword(10,'numerico'); //Devuelve valores numeros aleatoreos
        *
        * 	//Alfanumerico:
        * 	$SecurityPasswords->generarPassword(10,'alfanumerico'); //Devuelve valores alfanumerico aleatoreos
        *
        *===================================================================================================================
		*/

        /********************** Validaciones   **********************/
        if(!isset($longitud) || $longitud == ''){ return 'No ha ingresado longitud'; }
        if(!isset($tipo) || $tipo == ''){ return 'No ha ingresado tipo'; }

        // Validación de tipo numérico para la longitud y pertenencia de tipo a los permitidos
        if (!$this->DataValidations->validarNumero($longitud) || ($tipo != "alfanumerico" && $tipo != "numerico")){
            return 'Datos requeridos mal ingresados';
        }

        /********************** Si todo esta ok **********************/
        // Definir los alfabetos disponibles para la generación
        $alfabetos = [
            'alfanumerico' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
            'numerico'     => '0123456789',
        ];

        // Seleccionar el alfabeto según el tipo solicitado
        $alphabet = $alfabetos[$tipo] ?? $alfabetos['alfanumerico'];

        // Asegurar que el pool de caracteres sea suficiente para la longitud pedida
        $repeticiones = (int) ceil($longitud / strlen($alphabet));
        $pool         = str_repeat($alphabet, $repeticiones);

        // Mezclar aleatoriamente los caracteres del pool
        $shuffled = str_shuffle($pool);

        /********************** Retorno datos  **********************/
        // Retorna la subcadena truncada a la longitud deseada
        return substr($shuffled, 0, (int)$longitud);

    }

    /************************************************************************************************************/
    /**
     * Genera una contraseña única basada en la estampa de tiempo (Timestamp) del servidor.
     * * Concatena la fecha (YYYYMMDD) y la hora (HHMMSS) actual de Chile.
     * Útil para identificadores rápidos que requieren orden cronológico.
     *
     * @return string Cadena numérica representativa del momento exacto (ej: 20260404132055).
     */
    public function generarPasswordUnica(): string {
        /*
        *=================================================    Modo de uso  =================================================
        *
        * 	//generar una password
        * 	$SecurityPasswords->generarPasswordUnica(); //Devuelve 20241007152055 (para la fecha 2024/10/07 15:20:55)
        *
        *===================================================================================================================
		*/

        /********************** Si todo esta ok **********************/
        // Establecer la zona horaria predeterminada a Chile para asegurar consistencia
        date_default_timezone_set('America/Santiago');

        /********************** Retorno datos  **********************/
        // Devuelve la concatenación de fecha y hora actual
        return date("Ymd") . date("His");

    }

    /************************************************************************************************************/
    /**
     * Genera una cadena de caracteres aleatorios con opciones avanzadas de personalización.
     * * Permite omitir caracteres visualmente ambiguos (como 'O' y '0'), incluir símbolos
     * y garantizar que no existan caracteres duplicados en la cadena resultante.
     *
     * @param int $longitud Largo de la palabra generada.
     * @param bool $lecturaAmigable Si es true, remueve caracteres similares (O/0, l/1, etc.).
     * @param bool $incluirSimbolos Si es true, añade caracteres especiales (solo si lecturaAmigable es false).
     * @param bool $sinDuplicados Si es true, asegura que cada carácter aparezca solo una vez.
     * @return string Cadena aleatoria generada.
     * @throws LengthException Si se solicita una longitud mayor a los caracteres únicos disponibles sin duplicados.
     */
    public function caracteresRandom($longitud = 16, $lecturaAmigable = true, $incluirSimbolos = false, $sinDuplicados = false): string {
        /*
        *=================================================    Modo de uso  =================================================
        *
        * 	//Caracteres Random
        * 	$SecurityPasswords->caracteresRandom(16, true, false, false); //Devuelve valores aleatoreos
        *
        *===================================================================================================================
		*/

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
            return substr(str_shuffle(str_repeat($pool, $repeticiones)), 0, $longitud);
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
        return implode('', array_slice($caracteres, 0, $longitud));

    }

    /************************************************************************************************************/
    /**
     * Genera un token aleatorio codificado en formato hexadecimal.
     * * Utiliza la librería OpenSSL para generar bytes pseudo-aleatorios seguros,
     * lo cual es ideal para tokens de sesión o llaves de seguridad.
     *
     * @param int $longitud Longitud total de la cadena hexadecimal resultante.
     * @return string Token en formato hexadecimal.
     */
    public function tokenBin2Hex($longitud): string {
        /*
        *=================================================    Modo de uso  =================================================
        *
        * 	//se genera codigo
        * 	$SecurityPasswords->tokenBin2Hex(25); //Devuelve valores aleatoreos
        *
        *===================================================================================================================
		*/

        /********************** Validaciones   **********************/
        if(!isset($longitud) || $longitud == ''){ return 'No ha ingresado longitud'; }

        /********************** Si todo esta ok **********************/
        // Calcula la cantidad de bytes necesarios (cada byte produce 2 caracteres hexadecimales)
        $bytesNeeded = (int)(($longitud - ($longitud % 2)) / 2);

        /********************** Retorno datos  **********************/
        // Genera bytes aleatorios y los convierte a representación hexadecimal
        return bin2hex(openssl_random_pseudo_bytes($bytesNeeded));

    }

    /************************************************************************************************************/
    /**
     * Crea un hash de alta seguridad para contraseñas utilizando el algoritmo BCRYPT.
     * * Implementa un factor de costo (work factor) de 12, optimizado para el hardware actual,
     * lo que dificulta ataques de fuerza bruta.
     *
     * @param string $plain La contraseña o cadena en texto plano a procesar.
     * @return string El hash generado listo para ser almacenado en la base de datos.
     */
    public static function hashCreate($plain): string {
        /*
        *=================================================    Modo de uso  =================================================
        *
        * 	//se genera codigo
        * 	$SecurityPasswords->password_hash(25);
        *
        *===================================================================================================================
		*/

        /********************** Si todo esta ok **********************/
        // 'cost' 12 define el número de iteraciones del algoritmo (2^12).
        $options = ['cost' => 12];

        /********************** Retorno datos  **********************/
        // password_hash maneja automáticamente la generación de la sal (salt)
        return password_hash($plain, PASSWORD_BCRYPT, $options);

    }

    /************************************************************************************************************/
    /**
     * Verifica si una cadena en texto plano coincide con un hash previamente generado.
     * * Es resistente a ataques de tiempo (timing attacks) y detecta automáticamente
     * el algoritmo y el costo utilizados en el hash proporcionado.
     *
     * @param string $plain La cadena ingresada (ej: desde un formulario de login).
     * @param string $hash El hash almacenado contra el cual se desea comparar.
     * @return bool True si la contraseña es válida, False en caso contrario.
     */
    public static function hashVerify($plain, $hash): bool {
        /*
        *=================================================    Modo de uso  =================================================
        *
        * 	//se genera codigo
        * 	$SecurityPasswords->hashVerify(25, 'asdqwe');
        *
        *===================================================================================================================
		*/

        /********************** Si todo esta ok **********************/
        /********************** Retorno datos  **********************/
        // Compara el texto plano con el hash de forma segura
        return password_verify($plain, $hash);

    }


}
