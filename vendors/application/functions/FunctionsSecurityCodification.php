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
    public function simpleEncode($simple_string, $passkey): string {
        /*
        *=================================================     Detalles    =================================================
        *
        * Permite codificar un texto para que quede ilegible a la lectura normal, con la opción de la utilizacion de una
        * palabra clave para su codificacion
        *
        *=================================================    Modo de uso  =================================================
        *
        * 	//se codifica texto
        * 	$Codification->simpleEncode("php recipe");
        * 	$Codification->simpleEncode("php recipe", "passkey"); //Devuelve 'lEKK57naUY4/VQ=='
        *
        *=================================================    Parametros   =================================================
        * @input   string   $simple_string   Texto a transformar
        * @input   string   $passkey         (Opcional)Palabra clave de codificacion
        * @return  string
		*===================================================================================================================
		*/

        /**********************  Validaciones   **********************/
        if (!$simple_string) { return 'No ha ingresado el texto a codificar'; }

        /********************** Si todo esta ok **********************/
        if (!isset($passkey) || empty($passkey) || $passkey=='') {
            $encryption_key = sha1('EnCRypT10nK#Y!RiSRNn');
        }else{
            $encryption_key = $passkey;
        }
        /**************************************/
        //variables
        $ciphering     = "AES-128-CTR";// Store the cipher method
        //$iv_length     = openssl_cipher_iv_length($ciphering);// Use OpenSSl Encryption method
        $options       = 0;
        $encryption_iv = '1234567891011121';// Non-NULL Initialization Vector for encryption
        // Use openssl_encrypt() function to encrypt the data
        $encryption    = openssl_encrypt($simple_string, $ciphering, $encryption_key, $options, $encryption_iv);
        /**************************************/
        /**************************************/
        // Reemplazo múltiple en una sola operación
        $encryption = str_replace(['+', '/'], ['_', '---'], $encryption);

        /**********************  Retorno datos  **********************/
        //devuelvo
        return $encryption;

    }

    /************************************************************************************************************/
    public function simpleDecode($string, $passkey): string {
        /*
        *=================================================     Detalles    =================================================
        *
        * Permite decodificar un texto para que quede legible a la lectura normal, con la opción de la utilizacion de una
        * palabra clave para su decodificacion
        *
        *=================================================    Modo de uso  =================================================
        *
        * 	//se decodifica texto
        * 	$Codification->simpleDecode("qcnVhqjKxpuilw==");
        * 	$Codification->simpleDecode("lEKK57naUY4/VQ==", "passkey"); //Devuelve 'php recipe'
        *
        *=================================================    Parametros   =================================================
        * @input   string   $simple_string   Texto a transformar
        * @input   string   $passkey         (Opcional)Palabra clave de codificacion
        * @return  string
		*===================================================================================================================
		*/

        /**********************  Validaciones   **********************/
        //verifico si hay que reemplazar algo
        if (!$string) {return 'No ha ingresado el texto a decodificar';}

        /********************** Si todo esta ok **********************/
        //verifico si hay que reemplazar algo
        $simple_string = str_replace(['_', '---', ' '], [' ', '/', '+'], $string);
        /**************************************/
        //verifico la contraseña
        if (!isset($passkey) || empty($passkey) || $passkey=='') {
            $decryption_key = sha1('EnCRypT10nK#Y!RiSRNn');
        }else{
            $decryption_key = $passkey;
        }
        /**************************************/
        //variables
        $ciphering     = "AES-128-CTR";// Store the cipher method
        //$iv_length     = openssl_cipher_iv_length($ciphering);// Use OpenSSl Encryption method
        $options       = 0;
        $decryption_iv = '1234567891011121';// Non-NULL Initialization Vector for encryption
        // Use openssl_encrypt() function to encrypt the data
        $decryption    = openssl_decrypt ($simple_string, $ciphering, $decryption_key, $options, $decryption_iv);
        /**************************************/

        /**********************  Retorno datos  **********************/
        //devuelvo
        return $decryption;

    }

    /************************************************************************************************************/
    public function generateServerSpecificHash(): string{
        /*
        *=================================================     Detalles    =================================================
        *
        * Codificacion propia por cada servidor, esto impide el copiado de información entre servidores
        *
        *=================================================    Modo de uso  =================================================
        *
        * 	//se genera codigo
        * 	$Codification->generateServerSpecificHash(); //Devuelve '421aa90e079fa326b6494f812ad13e79'
        *
        *=================================================    Parametros   =================================================
        * @return  string
		*===================================================================================================================
		*/

        /********************** Si todo esta ok **********************/
        /**********************  Retorno datos  **********************/
        return (isset($_SERVER['SERVER_NAME']) && !empty($_SERVER['SERVER_NAME']))
                ? hash('sha256', $_SERVER['SERVER_NAME'])
                : hash('sha256', pathinfo(__FILE__, PATHINFO_FILENAME));

    }

    /************************************************************************************************************/
    public function encryptDecrypt($action, $string, $passkey = '') :string | int {
        /*
        *=================================================     Detalles    =================================================
        *
        * Permite decodificar un texto para que quede legible a la lectura normal, con la opción de la utilizacion de una
        * palabra clave para su decodificacion
        *
        *=================================================    Modo de uso  =================================================
        *
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
        *
        *=================================================    Parametros   =================================================
        * @input   string   $string   Texto a transformar
        * @input   string   $passkey  (Opcional)Palabra clave de codificacion
        * @return  string
		*===================================================================================================================
		*/

        /********************** Si todo esta ok **********************/
        $output         = false;
        $encrypt_method = "AES-256-CBC";
        $secret_key     = !empty($passkey) ? $passkey : 'YzJRMk5XWm5NVFpsT0hKbmN6WmtablkxTVRaelpEVm1kakZ6Tm1SbU5YWXhObUZsWmpWbk5ERTJOR2MyWlRobllYYzJaR1kxTVdFeU1R';
        $secret_iv      = 'salt_secreto';
        // hash
        $key = hash('sha256', $secret_key);
        // iv - encrypt method AES-256-CBC expects 16 bytes
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        if ( $action == 'encrypt' ) {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        }elseif( $action == 'decrypt' ) {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }

        /**********************  Retorno datos  **********************/
        return $output;

    }

}
