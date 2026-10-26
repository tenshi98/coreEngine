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
        // Configuración del canal SMTP + validación + envío (ver dispatch()).
        return $this->dispatch($this->channelConfig('smtp'), $TemplateData, $query);
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
        // Configuración del canal Gmail (misma lógica que SMTP; ver dispatch()).
        return $this->dispatch($this->channelConfig('gmail'), $TemplateData, $query);
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
        // Cabeceras/campos de envío: se elimina CR/LF (anti header-injection, defensa en profundidad).
        $data = array(
            "sender" => array(
                "email" => $this->sanitizeHeaderValue($this->DataText->desanitizarTexto($query['Post']['De_correo'])),
                "name"  => $this->sanitizeHeaderValue($this->DataText->desanitizarTexto($query['Post']['De_nombre']))
            ),
            "to" => array(
                array(
                    "email" => $this->sanitizeHeaderValue($this->DataText->desanitizarTexto($query['Post']['Hacia_correo'])),
                    "name"  => $this->sanitizeHeaderValue($this->DataText->desanitizarTexto($query['Post']['Hacia_nombre']))
                )
            ),
            "subject"     => $this->sanitizeHeaderValue($this->DataText->desanitizarTexto($query['Post']['Asunto'])),
            // Procesa el HTML final utilizando el motor de plantillas interno del sistema.
            // allowHtml: el cuerpo admite HTML (histórico); con false se escapa.
            "htmlContent" => $this->templateEmail($TemplateData, $query['template'], $query['Post'], $query['allowHtml'] ?? true)
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
    /******************************************************************************/
    /**
     * Devuelve la configuración de credenciales del canal de correo solicitado.
     * Centraliza el mapeo de ConfigMail, reutilizado por sendSMTPMail() y sendGMail().
     *
     * @param string $channel 'smtp' (por defecto) o 'gmail'.
     * @return array Credenciales normalizadas (ServerURL, ServerPort, ServerSecure, UserName, UserEmail, UserPass).
     */
    private function channelConfig(string $channel): array {
        // Selección del nodo de configuración (constantes de ConfigMail).
        $node = ($channel === 'gmail') ? ConfigMail::GmailSender : ConfigMail::SMTPSender;

        return [
            'ServerURL'    => $node["SERVERURL"],
            'ServerPort'   => $node["SERVERPORT"],
            'ServerSecure' => $node["SERVERSECURE"],
            'UserName'     => $node["USERNAME"],
            'UserEmail'    => $node["USEREMAIL"],
            'UserPass'     => $node["PASSWORD"],
        ];
    }

    /******************************************************************************/
    /**
     * Valida el envío y, si es correcto, delega en sendMail(). Compartido por los canales SMTP/Gmail.
     *
     * @param array $ConfigMail Credenciales del canal (ver channelConfig()).
     * @param mixed $TemplateData Datos de la plantilla / identificador.
     * @param array $query Configuración del envío.
     * @return bool|array true si se envió, false si falló, o arreglo de errores de validación.
     */
    private function dispatch(array $ConfigMail, $TemplateData, $query) {
        $DataVal = $this->validateMail($query);
        return ($DataVal === true) ? $this->sendMail($TemplateData, $ConfigMail, $query) : $DataVal;
    }

    /******************************************************************************/
    /**
     * Valida que los datos necesarios para el envío de un correo estén presentes.
     * * @param array $query Configuración que contiene 'data' (campos requeridos), 'template' y 'Post'.
     * @return bool|array True si es válido, o un arreglo con los errores encontrados.
     */
    private function validateMail($query){

        /************************************/
        // Verificaciones de integridad básica
        if(!isset($query['data']) || $query['data']==''){         return false; }
        if(!isset($query['template']) || $query['template']==''){ return false; }

        /************************************/
        // Preparación de campos requeridos
        $arrData = $this->CommonData->parseDataCommas($query['data']);
        $errors  = [];

        // Validación de presencia de datos en el arreglo Post
        foreach ($arrData as $field) {
            if (empty($query['Post'][$field])) {
                $errors[] = ["message" => "$field es obligatorio"];
            }
        }

        /************************************/
        // Anti header-injection: los campos que terminan en cabeceras del correo
        // (SMTP o payload JSON de la API) no pueden contener CR/LF; un salto de línea
        // permitiría inyectar cabeceras (p. ej. Bcc:) o alterar el asunto.
        $headerFields = ['Hacia', 'Asunto', 'De_correo', 'Hacia_correo', 'De_nombre', 'Hacia_nombre'];
        foreach ($headerFields as $field) {
            if (isset($query['Post'][$field]) && is_string($query['Post'][$field])
                && preg_match('/[\r\n]/', $query['Post'][$field])) {
                $errors[] = ["message" => "$field contiene caracteres no permitidos"];
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

        /************************************/
        // Extracción y desanitización de variables (asegura visualización de caracteres especiales)
        $ServerURL    = $ConfigMail['ServerURL'];
        $ServerPort   = $ConfigMail['ServerPort'];
        $ServerSecure = $ConfigMail['ServerSecure'];
        $UserName     = $this->DataText->desanitizarTexto($ConfigMail['UserName']);
        $UserEmail    = $this->DataText->desanitizarTexto($ConfigMail['UserEmail']);
        $UserPass     = $ConfigMail['UserPass'];

        // Cabeceras: se elimina CR/LF para impedir header injection (defensa en profundidad,
        // además de la validación previa en validateMail()).
        $Hacia        = $this->sanitizeHeaderValue($this->DataText->desanitizarTexto($query['Post']['Hacia']));
        $Asunto       = $this->sanitizeHeaderValue($this->DataText->desanitizarTexto($query['Post']['Asunto']));
        $UserName     = $this->sanitizeHeaderValue($UserName);
        $UserEmail    = $this->sanitizeHeaderValue($UserEmail);

        /************************************/
        // Inicialización del objeto SMTP con los parámetros del servidor
        $smtp = new SMTP ( $ServerURL, $ServerPort, $ServerSecure, $UserEmail, $UserPass );

        // Configuración de cabeceras de correo
        $smtp->set('From', '"'.$UserName.'" <'.$UserEmail.'>');
        $smtp->set('To', '<'.$Hacia.'>');
        $smtp->set('Subject', $Asunto);
        $smtp->set('Errors-to', '<'.$UserEmail.'>');

        // Generación del cuerpo HTML procesando la plantilla correspondiente
        // allowHtml: por defecto el Mensaje admite HTML (comportamiento histórico); con false se escapa.
        $message = $this->templateEmail($TemplateData, $query['template'], $query['Post'], $query['allowHtml'] ?? true);

        // Ejecución del envío
        $sent  = $smtp->send($message, true);

        // En fallo NO se devuelve el log SMTP (puede contener credenciales AUTH LOGIN en base64);
        // se registra en el log del servidor con la credencial enmascarada y se retorna false.
        if (!$sent) {
            $mylog = preg_replace('/(AUTH LOGIN\s+)([A-Za-z0-9+\/=]+)/i', '$1***', (string)$smtp->log());
            error_log('[MailSender] Fallo al enviar correo: ' . $mylog);
            return false;
        }

        // Retorno de datos
        return true;

    }

    /******************************************************************************/
    /**
     * Sanea un valor destinado a una cabecera de correo eliminando CR/LF.
     * Evita header injection (p. ej. inyectar "Bcc:" mediante un salto de línea).
     *
     * @param mixed $valor Valor a sanear.
     * @return string Valor sin retornos de carro ni saltos de línea, sin espacios extremos.
     */
    private function sanitizeHeaderValue($valor): string {
        return trim(str_replace(["\r", "\n"], '', (string)$valor));
    }

    /******************************************************************************/

    /**
     * Motor de renderizado de plantillas de correo electrónico.
     * * Selecciona el archivo físico de la plantilla y asigna las variables dinámicas
     * para generar el contenido HTML final del mensaje.
     *      * @param array $TemplateData Configuración global (Redes sociales, logos, URLs).
     * @param int $Template Identificador de la estructura de plantilla a utilizar.
     * @param array $Data Datos específicos del mensaje (Asunto, Cuerpo).
     * @param bool $allowHtml true (por defecto) permite HTML en el Mensaje; false lo escapa como texto.
     * @return string Contenido HTML renderizado.
     */
    private function templateEmail($TemplateData, $Template, $Data, $allowHtml = true){

    try {
            // Asunto: siempre texto plano → siempre escapado (evita HTML injection en el correo).
            $Asunto  = htmlspecialchars((string)($Data['Asunto'] ?? ''), ENT_QUOTES, 'UTF-8');
            // Mensaje: admite HTML por defecto (comportamiento histórico). Con $allowHtml=false se escapa.
            $Mensaje = $allowHtml
                ? (string)($Data['Mensaje'] ?? '')
                : htmlspecialchars((string)($Data['Mensaje'] ?? ''), ENT_QUOTES, 'UTF-8');

            /************************************/
            // Selección de lógica según el tipo de plantilla (switch)
            switch ($Template) {

                // Caso 1: Plantilla básica o de sistema
                case 1:
                    $this->TemplateRender->templatePath('../app/templates/Mail/mailTemplate_1.php');
                    $this->TemplateRender->assign('title', 'maqueta');
                    $this->TemplateRender->assign('Mensaje', $Mensaje);
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
                    $this->TemplateRender->assign('Asunto', $Asunto);
                    $this->TemplateRender->assign('Mensaje', $Mensaje);
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
