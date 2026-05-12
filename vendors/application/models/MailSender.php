<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class MailSender{

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                 Instancias                                                      */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	//Definiciones
	private $DataText;
	private $CommonData;
	private $TemplateRender;

	/******************************************************************************/
	//Instancias
	public function __construct() {
		$this->DataText       = new FunctionsDataText();
		$this->CommonData     = new FunctionsCommonData();
        $this->TemplateRender = new TemplateRenderer();
	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
    /******************************************************************************/
    /**
     * Orquestador principal para el envío de correos electrónicos mediante el protocolo SMTP.
     * * Este método actúa como un puente de configuración; extrae las credenciales seguras
     * desde la clase de configuración global (ConfigMail), valida la integridad de los
     * destinatarios y el contenido de la consulta, y finalmente delega el envío físico
     * al método especializado 'sendMail'.
     *
     * @param string|array $TemplateData Contenido del cuerpo del mensaje o identificador de plantilla.
     * @param array $query Configuración del envío (destinatarios, asunto, adjuntos, etc.).
     * @return bool|array Retorna true si el proceso se inicia correctamente o un arreglo con los errores de validación.
     */
    public function sendSMTPMail($TemplateData, $query){

        /********************** Configuración   **********************/
        // Mapea las constantes globales de configuración SMTP a un arreglo local.
        // Esto centraliza las credenciales (Host, Puerto, Seguridad, Usuario, Pass)
        // protegiendo los datos sensibles fuera de la lógica del controlador.
        $ConfigMail = [
            'ServerURL'    => ConfigMail::SMTPSender["SERVERURL"],
            'ServerPort'   => ConfigMail::SMTPSender["SERVERPORT"],
            'ServerSecure' => ConfigMail::SMTPSender["SERVERSECURE"],
            'UserName'     => ConfigMail::SMTPSender["USERNAME"],
            'UserEmail'    => ConfigMail::SMTPSender["USEREMAIL"],
            'UserPass'     => ConfigMail::SMTPSender["PASSWORD"],
        ];

        /********************** Validaciones   **********************/
        // Ejecuta la validación de los datos del correo (ej: formato de email, asunto no vacío).
        // Antes de intentar conectar al servidor SMTP, se asegura de que el envío sea lógico.
        $DataVal = $this->validateMail($query);

        /********************** Retorno datos  **********************/
        // Si la validación es exitosa (true), procede con el envío físico.
        // De lo contrario, corta el flujo y devuelve el detalle de los errores encontrados.
        return ($DataVal===true) ? $this->sendMail($TemplateData, $ConfigMail, $query) : $DataVal;

    }

    /******************************************************************************/
    /**
     * Orquestador especializado para el envío de correos electrónicos a través de los servidores de Gmail.
     * * Este método es una variante de sendSMTPMail configurada específicamente para utilizar
     * los parámetros de Gmail definidos en la clase ConfigMail. Gestiona la autenticación,
     * valida los datos del destinatario y delega la ejecución final al motor de envío.
     *
     * @param string|array $TemplateData Contenido del mensaje o datos para la plantilla.
     * @param array $query Configuración del envío (destinatarios, asunto, etc.).
     * @return bool|array Retorna true si el proceso es exitoso o un arreglo de errores.
     */
    public function sendGMail($TemplateData, $query){

        /********************** Configuración   **********************/
        // Extrae las credenciales específicas del nodo GmailSender en ConfigMail.
        // Gmail requiere configuraciones particulares de puerto (465 o 587) y seguridad (SSL/TLS).
        $ConfigMail = [
            'ServerURL'    => ConfigMail::GmailSender["SERVERURL"],
            'ServerPort'   => ConfigMail::GmailSender["SERVERPORT"],
            'ServerSecure' => ConfigMail::GmailSender["SERVERSECURE"],
            'UserName'     => ConfigMail::GmailSender["USERNAME"],
            'UserEmail'    => ConfigMail::GmailSender["USEREMAIL"],
            'UserPass'     => ConfigMail::GmailSender["PASSWORD"],
        ];

        /********************** Validaciones   **********************/
        // Verifica que el arreglo $query contenga correos válidos y campos obligatorios.
        $DataVal = $this->validateMail($query);

        /********************** Retorno datos  **********************/
        // Si la validación es correcta, invoca sendMail pasando la configuración de Gmail.
        return ($DataVal===true) ? $this->sendMail($TemplateData, $ConfigMail, $query) : $DataVal;

    }

    /******************************************************************************/
    /**
     * Realiza el envío de correos electrónicos utilizando la API REST de Sendinblue (Brevo).
     * * A diferencia de los métodos SMTP, este utiliza el protocolo HTTP mediante cURL
     * para comunicarse con la API de Sendinblue. Procesa el cuerpo del mensaje a
     * través de un motor de plantillas, desanitiza los textos de entrada para asegurar
     * que caracteres especiales (como tildes) se visualicen correctamente y gestiona
     * la autenticación mediante una API Key.
     *
     * @param string|array $TemplateData Datos dinámicos para inyectar en la plantilla.
     * @param array $query Configuración del envío (Post con De, Hacia, Asunto, y configuración de template).
     * @return bool|array Retorna true si se procesó el envío o un arreglo con errores de validación.
     */
    public function sendSendingBlueMail($TemplateData, $query){

        /********************** Configuración   **********************/
        // Construcción del objeto JSON requerido por la API de Sendinblue.
        // Se utiliza desanitizarTexto para que los nombres y asuntos recuperen caracteres
        // especiales que pudieron ser escapados previamente.
        $data = array(
            "sender" => array(
                "email" => $this->DataText->desanitizarTexto($query['Post']['De_correo']),
                "name"  => $this->DataText->desanitizarTexto($query['Post']['De_nombre'])
            ),
            "to" => array(
                array(
                    "email" => $this->DataText->desanitizarTexto($query['Post']['Hacia_correo']),
                    "name"  => $this->DataText->desanitizarTexto($query['Post']['Hacia_nombre'])
                )
            ),
            "subject"     => $this->DataText->desanitizarTexto($query['Post']['Asunto']),
            // Procesa el HTML final utilizando el motor de plantillas interno del sistema.
            "htmlContent" => $this->templateEmail($TemplateData, $query['template'], $query['Post'])
        );

        /********************** Validaciones   **********************/
        // Valida que los campos del correo cumplan con los requisitos mínimos de formato.
        $DataVal = $this->validateMail($query);

        /********************** Retorno datos  **********************/
        if($DataVal===true){
            try {
                /*************** Ejecución vía cURL ***************/
                // Se inicia la sesión cURL para realizar la petición POST a la API.
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, ConfigMail::SendingBlueSender["SERVERURL"]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                // Los datos se envían serializados en formato JSON.
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                // Configuración de encabezados HTTP, incluyendo la API-Key de seguridad.
                $headers = array();
                $headers[] = 'Accept: application/json';
                $headers[] = 'Api-Key: '.ConfigMail::SendingBlueSender["SERVERAPI"];
                $headers[] = 'Content-Type: application/json';

                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                // Ejecuta la petición y cierra la conexión.
                curl_exec($ch);
                curl_close($ch);

                return true;
            } catch (Exception $e) {
                // En caso de error en la comunicación de red, retorna falso.
                return false;
            }
        } else {
            // Retorna los errores de validación si no se superó el primer filtro.
            return $DataVal;
        }
    }

    /******************************************************************************/
    /**
     * Genera una previsualización del contenido final de un correo electrónico.
     * * Este método permite a los desarrolladores o administradores probar cómo se verá
     * el diseño del correo (HTML) una vez que el motor de plantillas haya inyectado
     * los datos dinámicos. Realiza las mismas validaciones que un envío real, pero
     * en lugar de despachar el mensaje, retorna el código HTML procesado.
     *
     * @param string|array $TemplateData Datos o variables que se inyectarán en la plantilla.
     * @param array $query Configuración que debe incluir el nombre de la 'template' y los datos en 'Post'.
     * @return string|array Retorna el HTML renderizado o un arreglo con errores de validación.
     */
    public function testMailTemplate($TemplateData, $query){

        /********************** Validaciones   **********************/
        // Ejecuta el filtro de seguridad y consistencia para asegurar que
        // el correo tiene todos los componentes necesarios (asunto, destinatario, etc.)
        $DataVal = $this->validateMail($query);

        /********************** Retorno datos  **********************/
        // Si la validación es exitosa, invoca al motor de renderizado interno.
        // templateEmail toma el archivo base y reemplaza los "placeholders" por los datos reales.
        return ($DataVal===true) ? $this->templateEmail($TemplateData, $query['template'], $query['Post']) : $DataVal;

    }

    /*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Internos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
    /**
     * Valida que los datos necesarios para el envío de un correo estén presentes.
     * * @param array $query Configuración que contiene 'data' (campos requeridos), 'template' y 'Post'.
     * @return bool|array True si es válido, o un arreglo con los errores encontrados.
     */
    private function validateMail($query){

        /******************************************/
        // Verificaciones de integridad básica
        if(!isset($query['data']) || $query['data']==''){         return false; }
        if(!isset($query['template']) || $query['template']==''){ return false; }

        /******************************************/
        // Preparación de campos requeridos
        $arrData = $this->CommonData->parseDataCommas($query['data']);
        $errors  = [];

        // Validación de presencia de datos en el arreglo Post
        foreach ($arrData as $field) {
            if (empty($query['Post'][$field])) {
                $errors[] = ["message" => "$field es obligatorio"];
            }
        }

        // Retorno de datos
        return (empty($errors)) ? true : $errors;

    }

    /******************************************************************************/

    /**
     * Ejecuta el envío físico del correo electrónico utilizando una conexión SMTP.
     * * @param mixed $TemplateData Información del sistema o variables para la plantilla.
     * @param array $ConfigMail Credenciales del servidor (URL, Puerto, Usuario, etc.).
     * @param array $query Datos del envío (Destinatario, Asunto, etc.).
     * @return bool|string True si se envió, o el log del error en caso de fallo.
     */
    private function sendMail($TemplateData, $ConfigMail, $query){

        /******************************************/
        // Extracción y desanitización de variables (asegura visualización de caracteres especiales)
        $ServerURL    = $ConfigMail['ServerURL'];
        $ServerPort   = $ConfigMail['ServerPort'];
        $ServerSecure = $ConfigMail['ServerSecure'];
        $UserName     = $this->DataText->desanitizarTexto($ConfigMail['UserName']);
        $UserEmail    = $this->DataText->desanitizarTexto($ConfigMail['UserEmail']);
        $UserPass     = $ConfigMail['UserPass'];
        $Hacia        = $this->DataText->desanitizarTexto($query['Post']['Hacia']);
        $Asunto       = $this->DataText->desanitizarTexto($query['Post']['Asunto']);

        /******************************************/
        // Inicialización del objeto SMTP con los parámetros del servidor
        $smtp = new SMTP ( $ServerURL, $ServerPort, $ServerSecure, $UserEmail, $UserPass );

        // Configuración de cabeceras de correo
        $smtp->set('From', '"'.$UserName.'" <'.$UserEmail.'>');
        $smtp->set('To', '<'.$Hacia.'>');
        $smtp->set('Subject', $Asunto);
        $smtp->set('Errors-to', '<'.$UserEmail.'>');

        // Generación del cuerpo HTML procesando la plantilla correspondiente
        $message = $this->templateEmail($TemplateData, $query['template'], $query['Post']);

        // Ejecución del envío
        $sent  = $smtp->send($message, true);

        // Captura de trazas para auditoría técnica en caso de error
        $mylog = $smtp->log();

        // Retorno de datos
        return ($sent) ? true : $mylog;

    }

    /******************************************************************************/

    /**
     * Motor de renderizado de plantillas de correo electrónico.
     * * Selecciona el archivo físico de la plantilla y asigna las variables dinámicas
     * para generar el contenido HTML final del mensaje.
     * * @param array $TemplateData Configuración global (Redes sociales, logos, URLs).
     * @param int $Template Identificador de la estructura de plantilla a utilizar.
     * @param array $Data Datos específicos del mensaje (Asunto, Cuerpo).
     * @return string Contenido HTML renderizado.
     */
    private function templateEmail($TemplateData, $Template, $Data){

    try {
            /******************************************/
            // Selección de lógica según el tipo de plantilla (switch)
            switch ($Template) {

                // Caso 1: Plantilla básica o de sistema
                case 1:
                    $this->TemplateRender->templatePath('../app/templates/Mail/mailTemplate_1.php');
                    $this->TemplateRender->assign('title', 'maqueta');
                    $this->TemplateRender->assign('Mensaje', $Data['Mensaje']);
                    break;

                // Caso 2: Plantilla corporativa con Redes Sociales y Logos
                case 2:
                    // Mapeo dinámico de iconos de redes sociales
                    $icons = [
                        'Social_X'        => ['twitter-black.png', 'X'],
                        'Social_Facebook' => ['facebook-black.png', 'Facebook'],
                        'Social_Instagram'=> ['instagram-black.png', 'Instagram'],
                        'Social_Linkedin' => ['linkedin-black.png', 'Linkedin'],
                    ];

                    $Social_icon = '';
                    foreach ($icons as $key => [$img, $alt]) {
                        // Solo genera el HTML del icono si la URL está definida en el sistema
                        if (!empty($TemplateData[$key])) {
                            $Social_icon .= '<td style="padding-top: 3px; padding-right: 20px;"><a target="_blank" rel="noopener noreferrer" href="'.$TemplateData[$key].'"><img src="'.$TemplateData['baseUrl'].'/img/social_icons/'.$img.'" width="16" alt="'.$alt.'" draggable="false"></a></td>';
                        }
                    }

                    // Asignación de ruta física y variables visuales
                    $this->TemplateRender->templatePath('../app/templates/Mail/mailTemplate_2.php');
                    $this->TemplateRender->assign('title', 'maqueta');
                    $this->TemplateRender->assign('CompanyLogo', !empty($TemplateData['Sistema_IMGLogo']) ? $TemplateData['MainPathUrl'].$TemplateData['Sistema_IMGLogo'] : $TemplateData['baseUrl'].'/img/logo.png');
                    $this->TemplateRender->assign('baseUrl', $TemplateData['baseUrl']);
                    $this->TemplateRender->assign('Sistema_Direccion', $TemplateData['Sistema_Direccion']);
                    $this->TemplateRender->assign('Sistema_Email', $TemplateData['Sistema_Email']);
                    $this->TemplateRender->assign('Social_icon', $Social_icon);
                    $this->TemplateRender->assign('Asunto', $Data['Asunto']);
                    $this->TemplateRender->assign('Mensaje', $Data['Mensaje']);
                    break;

                case 3:
                    // Espacio para futuras plantillas personalizadas
                    break;

            }
            // Retorna el buffer de salida procesado por la clase TemplateRender
            return $this->TemplateRender->render();
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }

    }

}

