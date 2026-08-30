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
     * una llave interna predefinida. Genera un IV aleatorio en cada llamada y lo antepone
     * al ciphertext (el IV no es secreto, pero nunca debe reutilizarse con la misma llave).
     * El resultado se sanitiza para ser seguro en URLs reemplazando caracteres conflictivos
     * ('+' por '_' y '/' por '---').
     *
     * @param string $simple_string Texto original que se desea codificar.
     * @param string $passkey (Opcional) Llave de cifrado personalizada.
     *
     * @return string Texto codificado y sanitizado. Distinto en cada llamada aunque el texto sea el mismo.
	 *
	 * @example
	 * ```php
	 * $Codification->simpleEncode("php recipe");
	 * $Codification->simpleEncode("php recipe", "passkey");
	 * ```
	 *
     */
    public function simpleEncode($simple_string, $passkey): string {

        /********************** Validaciones   **********************/
        if ($simple_string=='') { return 'Sin datos ingresados'; }

        /********************** Si todo esta ok **********************/
        // Configuración de la llave de cifrado
        if (!isset($passkey) || empty($passkey)) {
            $encryption_key = sha1(ConfigToken::ENCODE_KEYS["KEY_2"]);
        } else {
            $encryption_key = $passkey;
        }

        // Configuración de OpenSSL
        $ciphering     = "AES-128-CTR";
        $options       = OPENSSL_RAW_DATA;
        $encryption_iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($ciphering)); // IV aleatorio por operación

        // Ejecución del cifrado (datos crudos, se codifica en Base64 más abajo junto al IV)
        $ciphertext_raw = openssl_encrypt($simple_string, $ciphering, $encryption_key, $options, $encryption_iv);

        // El IV no es secreto: se antepone al ciphertext para poder recuperarlo al decodificar
        $encryption = base64_encode($encryption_iv . $ciphertext_raw);

        // Sanitización para transporte (URL friendly)
        $encryption = str_replace(['+', '/'], ['_', '---'], $encryption);

        /********************** Retorno datos  **********************/
        return $encryption;
    }

    /************************************************************************************************************/
    /**
     * Decodifica un texto previamente cifrado con el método simpleEncode.
     * * Revierte la sanitización de caracteres, extrae el IV que viaja al inicio de los
     * datos y aplica el proceso inverso de AES-128-CTR utilizando la misma llave con la
     * que fue cifrado.
     *
     * @param string $string Texto codificado que se desea recuperar.
     * @param string $passkey (Opcional) Llave de cifrado utilizada originalmente.
     *
     * @return string Texto original decodificado.
	 *
	 * @example
	 * ```php
	 * $Codification->simpleDecode($Codification->simpleEncode("php recipe"));
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
            $decryption_key = sha1(ConfigToken::ENCODE_KEYS["KEY_2"]);
        } else {
            $decryption_key = $passkey;
        }

        // Configuración de OpenSSL idéntica al proceso de codificación
        $ciphering = "AES-128-CTR";
        $options   = OPENSSL_RAW_DATA;
        $iv_length = openssl_cipher_iv_length($ciphering);

        // El IV viaja concatenado al inicio de los datos, se separa del ciphertext
        $raw            = base64_decode($simple_string);
        $decryption_iv  = substr($raw, 0, $iv_length);
        $ciphertext_raw = substr($raw, $iv_length);

        // Ejecución del descifrado
        $decryption = openssl_decrypt($ciphertext_raw, $ciphering, $decryption_key, $options, $decryption_iv);

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
     * * A diferencia del método "simple", este utiliza una llave de 256 bits. Genera un
     * IV aleatorio en cada operación de cifrado y lo antepone al ciphertext (el IV no es
     * secreto, pero nunca debe reutilizarse con la misma llave). Es ideal para proteger
     * IDs o datos sensibles en bases de datos o sesiones.
     *
     * @param string $action Acción a realizar: 'encrypt' para cifrar o 'decrypt' para descifrar.
     * @param mixed  $string El contenido a procesar (texto o número).
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
        $secret_key     = !empty($passkey) ? $passkey : ConfigToken::ENCODE_KEYS["KEY_4"];

        // Generación de llave mediante hashing para cumplir con los requisitos de 256 bits
        $key       = hash('sha256', $secret_key);
        $iv_length = openssl_cipher_iv_length($encrypt_method);

        if ($action == 'encrypt') {
            // IV aleatorio por operación, antepuesto al ciphertext (crudo) antes de Base64
            $iv             = openssl_random_pseudo_bytes($iv_length);
            $ciphertext_raw = openssl_encrypt($string, $encrypt_method, $key, OPENSSL_RAW_DATA, $iv);
            // Base64 URL-safe (sin '+', '/' ni '=' de relleno) para evitar problemas en URLs
            $output         = rtrim(strtr(base64_encode($iv . $ciphertext_raw), '+/', '-_'), '=');
        } elseif ($action == 'decrypt') {
            // Revierte Base64 URL-safe y restaura el relleno '=' antes de decodificar
            $base64  = strtr($string, '-_', '+/');
            $base64 .= str_repeat('=', (4 - strlen($base64) % 4) % 4);

            // El IV viaja concatenado al inicio de los datos, se separa del ciphertext
            $raw            = base64_decode($base64);
            $iv             = substr($raw, 0, $iv_length);
            $ciphertext_raw = substr($raw, $iv_length);
            $output         = openssl_decrypt($ciphertext_raw, $encrypt_method, $key, OPENSSL_RAW_DATA, $iv);
        }

        /********************** Retorno datos  **********************/
        return $output;
    }

}
