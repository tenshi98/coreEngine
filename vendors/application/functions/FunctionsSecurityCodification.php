<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsSecurityCodification {

    /*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Publicos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
    /**
     * Codifica un texto utilizando el algoritmo AES-128-CTR para hacerlo ilegible.
     * * Permite el uso de una llave personalizada (passkey). Si no se proporciona, utiliza
     * una llave interna predefinida. Genera un IV aleatorio en cada llamada y lo antepone
     * al ciphertext (el IV no es secreto, pero nunca debe reutilizarse con la misma llave).
     * El resultado se sanitiza para ser seguro en URLs aplicando Base64 URL-safe (RFC 4648):
     * '+' -> '-' y '/' -> '_', sin relleno '='.
     *
     * @param string $simple_string Texto original que se desea codificar.
     * @param string $passkey (Opcional) Llave de cifrado personalizada.
     *
     * @return array Texto codificado y sanitizado. Distinto en cada llamada aunque el texto sea el mismo.
	 *
	 * @example
	 * ```php
	 * $Codification->simpleEncode("php recipe");
	 * $Codification->simpleEncode("php recipe", "passkey");
	 * ```
	 *
     */
    public function simpleEncode($simple_string, $passkey): array {

        /********************** Validaciones   **********************/
        if ($simple_string==''){ return ['success' => false, 'error' => 'Sin datos ingresados'];}

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

        // Sanitización para transporte (URL friendly) con Base64 URL-safe estándar (RFC 4648):
        // '+' -> '-' y '/' -> '_', eliminando el relleno '=' final.
        $encryption = rtrim(strtr($encryption, '+/', '-_'), '=');

        /********************** Retorno datos  **********************/
        return ['success' => true, 'data' => $encryption];

    }

    /******************************************************************************/
    /**
     * Codifica un dato utilizando Base64 URL-safe (RFC 4648).
     * * Reemplaza '+' por '-' y '/' por '_', y elimina el relleno '=' final.
     * * Utilizado para transmitir datos codificados en URLs de forma segura.
     *
     * @param string $Data Dato a codificar.
     *
     * @return array Datos codificados en Base64 URL-safe.
     *
     * @example
     * ```php
     * $Codification->base64UrlEncode('datos sensibles');
     * // Devuelve ['success' => true, 'data' => 'c2FtcGxlIgBpbnNlcnQ']
     * ```
     *
     */
    public function base64UrlEncode($Data): array {

        /********************** Validaciones   **********************/
        if ($Data === null || trim((string)$Data) === '') {
            return ['success' => false, 'error' => 'Sin datos ingresados'];
        }

        /********************** Codificacion **********************/
        $base64 = base64_encode($Data);
        // Sanitización URL-safe: '+' -> '-' y '/' -> '_', eliminando '=' final
        $urlSafe = rtrim(strtr($base64, '+/', '-_'), '=');

        /********************** Retorno datos **********************/
        return ['success' => true, 'data' => $urlSafe];

    }

    /******************************************************************************/
    /**
     * Decodifica un dato que fue codificado con base64UrlEncode.
     * * Invierte la sanitización Base64 URL-safe: '-' -> '+' y '_' -> '/'
     * * Agrega el relleno '=' necesario para una decodificación válida.
     *
     * @param string $Data Dato codificado en Base64 URL-safe.
     *
     * @return array Datos decodificados originales.
     *
     * @example
     * ```php
     * $Codification->base64UrlDecode('c2FtcGxlIgBpbnNlcnQ');
     * // Devuelve ['success' => true, 'data' => 'datos sensibles']
     * ```
     *
     */
    public function base64UrlDecode($Data): array {

        /********************** Validaciones   **********************/
        if ($Data === null || trim((string)$Data) === '') {
            return ['success' => false, 'error' => 'Sin datos ingresados'];
        }

        /********************** Decodificación **********************/
        // Reversión de la sanitización URL-safe
        $base64 = strtr($Data, '-_', '+/');
        // Agrega el relleno '=' necesario
        $base64 .= str_repeat('=', (4 - strlen($base64) % 4) % 4);

        /********************** Retorno datos **********************/
        $decoded = base64_decode($base64);
        if ($decoded === false) {
            return ['success' => false, 'error' => 'Datos inválidos para decodificar'];
        }
        return ['success' => true, 'data' => $decoded];

    }

    /******************************************************************************/
    /**
     * Genera una firma digital (hash) para un dato utilizando hash_equals
     * para comparacion segura contra ataques de tiempo (timing attacks).
     * * Firma el dato concatenando un identificador unico y aplicando SHA-256.
     * * El resultado incluye la firma y los datos necesarios para verificar.
     *
     * @param string $Dato El dato a firmar.
     * @param string $Clave Secreta o llave para la firma.
     *
     * @return array Estructura con la firma generada y los datos firmados.
     *
     * @example
     * ```php
     * $Codification->firmarDato('datos importantes', 'mi_clave_secreta');
     * // Devuelve ['success' => true, 'data' => ['firma' => '...', 'dato' => '...']]
     * ```
     *
     */
    public function firmarDato($Dato, $Clave): array {

        /********************** Validaciones   **********************/
        if ($Dato === null || trim((string)$Dato) === '') {
            return ['success' => false, 'error' => 'Sin datos para firmar'];
        }
        if ($Clave === null || trim((string)$Clave) === '') {
            return ['success' => false, 'error' => 'Sin clave para firmar'];
        }

        /********************** Generacion de firma **********************/
        // Genera un identificador unico para esta firma (timestamp + random)
        $identificador = uniqid('', true);
        $datosParaHash = $Dato . '|' . $identificador . '|' . $Clave;
        $firma = hash_hmac('sha256', $datosParaHash, $Clave);

        /********************** Retorno datos **********************/
        return ['success' => true, 'data' => [
            'firma' => $firma,
            'identificador' => $identificador,
            'dato' => $Dato
        ]];

    }

    /******************************************************************************/
    /**
     * Verifica si una firma digital es válida para un dato dado.
     * * Utiliza hash_equals para comparacion constante en el tiempo, protegiendo
     * contra ataques de tiempo (timing attacks).
     * * Compara la firma almacenada con la firma calculada actualmentre.
     *
     * @param string $Dato El dato original (debe coincidir con el usado para firmar).
     * @param array $FirmaEstructura Estructura que contiene 'firma' e 'identificador' generada por firmarDato().
     * @param string $Clave La clave secreta utilizada originalmente para firmar.
     *
     * @return bool True si la firma es válida, False en caso contrario.
     *
     * @example
     * ```php
     * $resultado = $Codification->verificarFirma('datos importantes', ['firma' => $firma, 'identificador' => $id], 'mi_clave_secreta');
     * // Devuelve true si la firma es valida, false en caso contrario
     * ```
     *
     */
    public function verificarFirma($Dato, $FirmaEstructura, $Clave): bool {

        /********************** Validaciones   **********************/
        if ($Dato === null || trim((string)$Dato) === '') {
            return false;
        }
        if ($FirmaEstructura === null || !is_array($FirmaEstructura)) {
            return false;
        }
        if ($Clave === null || trim((string)$Clave) === '') {
            return false;
        }

        /********************** Calculacion de firma **********************/
        $identificador = $FirmaEstructura['identificador'] ?? '';
        $datosParaHash = $Dato . '|' . $identificador . '|' . $Clave;
        $firmaCalculada = hash_hmac('sha256', $datosParaHash, $Clave);

        /********************** Comparacion segura **********************/
        // Utiliza hash_equals para comparacion constante en el tiempo
        return hash_equals($firmaCalculada, $FirmaEstructura['firma']);

    }

    /******************************************************************************/
    /**
     * @param string $string Texto codificado que se desea recuperar.
     * @param string $passkey (Opcional) Llave de cifrado utilizada originalmente.
     *
     * @return array Texto original decodificado.
	 *
	 * @example
	 * ```php
	 * $Codification->simpleDecode($Codification->simpleEncode("php recipe"));
	 * ```
	 *
     */
    public function simpleDecode($string, $passkey): array {

        /********************** Validaciones   **********************/
        if ($string==''){ return ['success' => false, 'error' => 'Sin datos ingresados'];}

        /********************** Si todo esta ok **********************/
        // Reversión de la sanitización Base64 URL-safe (RFC 4648): '-' -> '+' y '_' -> '/'
        $base64  = strtr($string, '-_', '+/');
        // Restaura el relleno '=' eliminado durante la codificación
        $base64 .= str_repeat('=', (4 - strlen($base64) % 4) % 4);

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
        $raw            = base64_decode($base64);
        $decryption_iv  = substr($raw, 0, $iv_length);
        $ciphertext_raw = substr($raw, $iv_length);

        // Ejecución del descifrado
        $decryption = openssl_decrypt($ciphertext_raw, $ciphering, $decryption_key, $options, $decryption_iv);

        /********************** Retorno datos  **********************/
        return ['success' => true, 'data' => $decryption];

    }

	/******************************************************************************/
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
     * @return array El resultado procesado o False en caso de error.
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
    public function encryptDecrypt($action, $string, $passkey = '') : array {

        /********************** Validaciones   **********************/
        // Valida que la acción haya sido ingresada y que corresponda a una operación permitida.
        if (!in_array($action, ['encrypt', 'decrypt'], true)) {
            return ['success' => false, 'error' => 'Acción no válida'];
        }

        // Valida que el dato a procesar exista y no esté vacío.
        if ($string === null || trim((string)$string) === '') {
            return ['success' => false, 'error' => 'Sin datos ingresados'];
        }

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
            // Retorno de datos
            return ['success' => true, 'data' => $output];
        } elseif ($action == 'decrypt') {
            // Revierte Base64 URL-safe y restaura el relleno '=' antes de decodificar
            $base64  = strtr($string, '-_', '+/');
            $base64 .= str_repeat('=', (4 - strlen($base64) % 4) % 4);

            // El IV viaja concatenado al inicio de los datos, se separa del ciphertext
            $raw            = base64_decode($base64);
            $iv             = substr((string)$raw, 0, $iv_length);
            $ciphertext_raw = substr((string)$raw, $iv_length);

            // Valido el material criptografico ANTES de llamar a openssl.
            // Un IV incompleto hace que openssl_decrypt() emita un warning
            // ("IV passed is only N bytes long, cipher expects an IV of precisely
            // 16 bytes"). Como Fat-Free Framework instala un set_error_handler
            // global que convierte cualquier warning en HTTP 500 + die(), ese
            // warning abortaba la peticion completa (incluido el sistema de
            // testeos). Un dato corrupto u obfuscado debe devolverse como error
            // de negocio, nunca como warning de PHP.
            if($raw === false || strlen($iv) !== $iv_length || $ciphertext_raw === ''){
                return ['success' => false, 'error' => 'Registro encriptado invalido'];
            }

            $output         = openssl_decrypt($ciphertext_raw, $encrypt_method, $key, OPENSSL_RAW_DATA, $iv);
            // Verifico
            if ($output === false) { return ['success' => false, 'error' => 'No se pudo desencriptar']; }
            // Retorno de datos
            return ['success' => true, 'data' => $output];
        }

        /********************** Retorno datos  **********************/
        return ['success' => false, 'error' => 'Registro inválido'];

    }

	/******************************************************************************/
    /**
     * Genera un hash SHA-256 basado en un identificador pseudoaleatorio seguro.
     *
     * Esta función genera una cadena de 32 bytes criptográficamente seguros mediante random_bytes,
     * la convierte a representación hexadecimal y posteriormente aplica un algoritmo de hash SHA-256
     * sobre dicha cadena para devolver una huella digital única de 64 caracteres.
     *
     * @return array Cadena de 64 caracteres hexadecimales correspondiente al hash SHA-256 del identificador.
     * @throws Exception Si no se encuentra una fuente de entropía suficiente para la generación de bytes aleatorios.
     *
     * @example
     * ```php
     * $codification = new Codification();
     * $hash =$codification->generateServerSpecificHash();
     * // Devuelve una cadena similar a: '421aa90e079fa326b6494f812ad13e792e34f... (64 caracteres)'
     * ```
     */
    public function generateServerSpecificHash(): array {

        /********************** Si todo esta ok **********************/
        // Genera 32 bytes aleatorios criptográficamente seguros y los convierte a formato hexadecimal (64 caracteres)
        $identifier = bin2hex(random_bytes(32));

        /********************** Retorno datos  **********************/
        // Genera y retorna el resumen criptográfico SHA-256 del identificador binario convertido
        return ['success' => true, 'data' => hash('sha256', $identifier)];

    }


}
