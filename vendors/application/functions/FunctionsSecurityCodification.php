<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsSecurityCodification {

    /*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
    /**
     * Codifica un texto utilizando el algoritmo AES-128-CTR para hacerlo ilegible.
     * * Permite el uso de una llave personalizada (passkey). Si no se proporciona, utiliza
     * una llave interna predefinida. El resultado se sanitiza para ser seguro en URLs
     * reemplazando caracteres conflictivos ('+' por '_' y '/' por '---').
     *
     * @param string $simple_string Texto original que se desea codificar.
     * @param string $passkey (Opcional) Llave de cifrado personalizada.
     *
     * @return string Texto codificado y sanitizado.
	 *
	 * @example
	 * ```php
	 * $Codification->simpleEncode("php recipe");
	 * $Codification->simpleEncode("php recipe", "passkey"); //Devuelve 'lEKK57naUY4/VQ=='
	 * ```
	 *
     */
    public function simpleEncode($simple_string, $passkey): string {

        /********************** Validaciones   **********************/
        if ($simple_string=='') { return 'Sin datos ingresados'; }

        /********************** Si todo esta ok **********************/
        // Configuración de la llave de cifrado
        if (!isset($passkey) || empty($passkey)) {
            $encryption_key = sha1('EnCRypT10nK#Y!RiSRNn');
        } else {
            $encryption_key = $passkey;
        }

        // Configuración de OpenSSL
        $ciphering     = "AES-128-CTR";
        $options       = 0;
        $encryption_iv = '1234567891011121'; // Vector de inicialización (IV) fijo

        // Ejecución del cifrado
        $encryption = openssl_encrypt($simple_string, $ciphering, $encryption_key, $options, $encryption_iv);

        // Sanitización para transporte (URL friendly)
        $encryption = str_replace(['+', '/'], ['_', '---'], $encryption);

        /********************** Retorno datos  **********************/
        return $encryption;
    }

    /************************************************************************************************************/
    /**
     * Decodifica un texto previamente cifrado con el método simpleEncode.
     * * Revierte la sanitización de caracteres y aplica el proceso inverso de AES-128-CTR
     * utilizando la misma llave y vector de inicialización con los que fue cifrado.
     *
     * @param string $string Texto codificado que se desea recuperar.
     * @param string $passkey (Opcional) Llave de cifrado utilizada originalmente.
     *
     * @return string Texto original decodificado.
	 *
	 * @example
	 * ```php
	 * $Codification->simpleDecode("qcnVhqjKxpuilw==");
	 * $Codification->simpleDecode("lEKK57naUY4/VQ==", "passkey"); //Devuelve 'php recipe'
	 * ```
	 *
     */
    public function simpleDecode($string, $passkey): string {

        /********************** Validaciones   **********************/
        if ($string=='') { return 'Sin datos ingresados'; }

        /********************** Si todo esta ok **********************/
        // Reversión de la sanitización (restaura caracteres originales de Base64)
        $simple_string = str_replace(['_', '---', ' '], ['+', '/', '+'], $string);

        // Configuración de la llave de descifrado
        if (!isset($passkey) || empty($passkey)) {
            $decryption_key = sha1('EnCRypT10nK#Y!RiSRNn');
        } else {
            $decryption_key = $passkey;
        }

        // Configuración de OpenSSL idéntica al proceso de codificación
        $ciphering     = "AES-128-CTR";
        $options       = 0;
        $decryption_iv = '1234567891011121';

        // Ejecución del descifrado
        $decryption = openssl_decrypt($simple_string, $ciphering, $decryption_key, $options, $decryption_iv);

        /********************** Retorno datos  **********************/
        return (string)$decryption;
    }

    /************************************************************************************************************/
    /**
     * Genera un hash SHA-256 único basado en la identidad del servidor actual.
     * * Utiliza el nombre del servidor (SERVER_NAME) o, en su defecto, el nombre del
     * archivo actual para crear una huella digital. Esto ayuda a restringir o
     * validar que ciertos procesos o datos pertenezcan al entorno correcto.
     *
     * @return string Hash representativo del servidor.
	 *
	 * @example
	 * ```php
	 * $Codification->generateServerSpecificHash(); //Devuelve '421aa90e079fa326b6494f812ad13e79'
	 * ```
	 *
     */
    public function generateServerSpecificHash(): string {

        /********************** Si todo esta ok **********************/
        // Intenta obtener el nombre del servidor, de lo contrario usa el nombre del script
        $identifier = (isset($_SERVER['SERVER_NAME']) && !empty($_SERVER['SERVER_NAME']))
                    ? $_SERVER['SERVER_NAME']
                    : pathinfo(__FILE__, PATHINFO_FILENAME);

        /********************** Retorno datos  **********************/
        return hash('sha256', $identifier);
    }

    /************************************************************************************************************/
    /**
     * Realiza operaciones de cifrado y descifrado utilizando el algoritmo AES-256-CBC.
     * * A diferencia del método "simple", este utiliza una llave de 256 bits y un vector
     * de inicialización derivado de un hash, proporcionando una capa de seguridad
     * superior. Es ideal para proteger IDs o datos sensibles en bases de datos o sesiones.
     *
     * @param string $action Acción a realizar: 'encrypt' para cifrar o 'decrypt' para descifrar.
     * @param mixed $string El contenido a procesar (texto o número).
     * @param string $passkey (Opcional) Llave personalizada de alta seguridad.
     *
     * @return string|int El resultado procesado o False en caso de error.
	 *
	 * @example
	 * ```php
	 * 	// Encriptas id 5008
     * 	$encriptar = $Codification->encryptDecrypt('encrypt',5008);
     * 	echo $encriptar . '<br>';
     *
     * 	// Desencriptas el id para verlo de manera original
     * 	$desencriptar = $Codification->encryptDecrypt('decrypt',$encriptar);
     * 	echo $desencriptar;
     *
     * 	//salidas:
     * 	bnR6UTRVTHAzYWd1dWEvWVdpMGo4QT09 (corresponde a 5008)
     * 	5008
	 * ```
	 *
     */
    public function encryptDecrypt($action, $string, $passkey = '') : string | int | bool {

        /********************** Validaciones   **********************/
        if ($action=='') { return 'Sin datos ingresados'; }
        if ($string=='') { return 'Sin datos ingresados'; }

        /********************** Si todo esta ok **********************/
        $output         = false;
        $encrypt_method = "AES-256-CBC";
        // Llave secreta por defecto si no se entrega una personalizada
        $secret_key     = !empty($passkey) ? $passkey : 'YzJRMk5XWm5NVFpsT0hKbmN6WmtablkxTVRaelpEVm1kakZ6Tm1SbU5YWXhObUZsWmpWbk5ERTJOR2MyWlRobllYYzJaR1kxTVdFeU1R';
        $secret_iv      = 'salt_secreto';

        // Generación de llave y IV mediante hashing para cumplir con los requisitos de 256 bits
        $key = hash('sha256', $secret_key);
        $iv  = substr(hash('sha256', $secret_iv), 0, 16);

        if ($action == 'encrypt') {
            // Cifrado y posterior codificación en Base64 para manejo de caracteres
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } elseif ($action == 'decrypt') {
            // Decodificación Base64 y descifrado posterior
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }

        /********************** Retorno datos  **********************/
        return $output;
    }

}
