<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Encapsula la verificación de contraseñas de usuario y la generación de
 * tokens/contraseñas aleatorias, delegando en FunctionsSecurityPasswords.
 */
class PasswordService {

    /******************************************************************************/
    // Variables
    private $Passwords;

    /******************************************************************************/
    /**
     * Instancia el helper de seguridad de contraseñas.
     *
     * @return void
     */
    public function __construct(){
        /*================== Instancias =================*/
		$this->Passwords = new FunctionsSecurityPasswords();
    }

    /******************************************************************************/
    /**
     * Verifica que una contraseña en texto plano coincida con el hash
     * almacenado en base de datos.
     *
     * @param string $PostPassword Contraseña recibida del formulario.
     * @param string $DBPassword   Hash de contraseña almacenado en base de datos.
     *
     * @return bool true si la contraseña es válida; false en caso contrario.
     */
    public function verify($PostPassword, $DBPassword){

        /******************************************/
        // Llamo a las otras clases
		$response = false;

        /******************************/
        // Se verifica la contraseña
        $checkPassword = $this->Passwords->hashVerify($PostPassword, $DBPassword);
        if($checkPassword===true){
            $response = $checkPassword;
        }

        /******************************/
		// Retorno de datos
        return $response;

    }

    /******************************************************************************/
    /**
     * Genera un token/contraseña aleatoria alfanumérica de 20 caracteres,
     * utilizado como token de sesión.
     *
     * @return string Cadena alfanumérica generada.
     */
    public function generate(){

        /******************************/
        // Se cargan las clases
        $SecurityPasswords = new FunctionsSecurityPasswords();

        /******************************/
        // Retorno de datos
        return $SecurityPasswords->generarPassword(20,'alfanumerico');

    }




}
