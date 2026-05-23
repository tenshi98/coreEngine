<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class ControllerWeb {
    //Variables
    private $mailSender;

    public function __construct($mailSender){
        $this->mailSender = $mailSender;
    }

    /******************************************************************************/
    /******************************************************************************/
    /**
     * Procesa y envía un correo electrónico utilizando el protocolo SMTP.
     *
     * Esta función extrae la información de configuración del sistema y redes sociales
     * desde la sesión del usuario, prepara una estructura de datos para la plantilla
     * y utiliza la clase MailSender para realizar el envío físico del mensaje.
     *
     * @param object $f3    Instancia del framework (Fat-Free Framework) que provee acceso a variables globales y de sesión.
     * @param mixed  $query Objeto o arreglo con la información específica del correo a enviar (destinatario, asunto, contenido).
     *
     * @return mixed Retorna el resultado del envío si es exitoso, o false en caso de fallo.
     */
    protected function Base_SMTPMail($f3, $query, $UserData){

        /******************************************/
        // Construye el arreglo de datos que se inyectarán en la plantilla de correo
        $TemplateData = [
            'Sistema_IMGLogo'   => $UserData['Sistema_IMGLogo'],
            'Sistema_Direccion' => $UserData['Sistema_Direccion'],
            'Sistema_Email'     => $UserData['Sistema_Email'],
            'Social_X'          => $UserData['Social_X'],
            'Social_Facebook'   => $UserData['Social_Facebook'],
            'Social_Instagram'  => $UserData['Social_Instagram'],
            'Social_Linkedin'   => $UserData['Social_Linkedin'],
            'baseUrl'           => $f3->get('BASE')
        ];

        /******************************/
        // Ejecuta el envío SMTP pasando la configuración de la plantilla y los datos del mensaje
        $result = $this->mailSender->sendSMTPMail($TemplateData, $query); //Envio por correo normal

        /******************************/
        // Evalúa la respuesta del emisor y retorna el resultado o un booleano falso si no hubo respuesta positiva
        return ($result) ? $result : false;
    }

    /******************************************************************************/
    /**
     * Procesa y envía un correo electrónico utilizando la integración específica de Gmail.
     *
     * Esta función recopila la configuración visual, corporativa y de redes sociales desde
     * la sesión activa del usuario. A diferencia del método SMTP estándar, esta función
     * incluye el parámetro 'MainPathUrl' en el conjunto de datos de la plantilla antes de
     * delegar el envío a la clase MailSender mediante el método especializado para Gmail.
     *
     * @param object $f3    Instancia del framework (Fat-Free Framework) para el acceso a datos globales y de sesión.
     * @param mixed  $query Información estructurada del mensaje (destinatario, contenido, asunto, etc.).
     *
     * @return mixed Retorna el objeto de respuesta del envío si es exitoso, o false en caso de error o ausencia de resultado.
     */
    protected function Base_GMail($f3, $query, $UserData){

        /******************************************/
        // Construye el arreglo de datos que se inyectarán en la plantilla de correo
        $TemplateData = [
            'Sistema_IMGLogo'   => $UserData['Sistema_IMGLogo'],
            'Sistema_Direccion' => $UserData['Sistema_Direccion'],
            'Sistema_Email'     => $UserData['Sistema_Email'],
            'Social_X'          => $UserData['Social_X'],
            'Social_Facebook'   => $UserData['Social_Facebook'],
            'Social_Instagram'  => $UserData['Social_Instagram'],
            'Social_Linkedin'   => $UserData['Social_Linkedin'],
            'baseUrl'           => $f3->get('BASE')
        ];

        /******************************/
        // Ejecuta el envío a través del canal específico configurado para Gmail
        $result = $this->mailSender->sendGMail($TemplateData, $query);    //Envio por gmail

        /******************************/
        // Evalúa la respuesta del emisor y retorna el resultado o un booleano falso si no hubo respuesta positiva
        return ($result) ? $result : false;
    }

    /******************************************************************************/
    /**
     * Gestiona el envío de correos electrónicos a través del servicio externo SendingBlue (Brevo).
     *
     * Esta función extrae los metadatos corporativos y de redes sociales desde la sesión
     * del usuario para configurar la apariencia de la plantilla de correo. Posteriormente,
     * delega la ejecución técnica a la clase MailSender utilizando su método específico
     * para la API de SendingBlue.
     *
     * @param object $f3    Instancia del framework (Fat-Free Framework) utilizada para acceder a datos de sesión y variables de ruta.
     * @param mixed  $query Contiene la información lógica del correo (destinatario, asunto, datos dinámicos del cuerpo).
     *
     * @return mixed Retorna el objeto de respuesta del servicio SendingBlue si la operación es exitosa, o false en caso contrario.
     */
    protected function Base_SendingBlue($f3, $query, $UserData){

        /******************************************/
        // Construye el arreglo de datos que se inyectarán en la plantilla de correo
        $TemplateData = [
            'Sistema_IMGLogo'   => $UserData['Sistema_IMGLogo'],
            'Sistema_Direccion' => $UserData['Sistema_Direccion'],
            'Sistema_Email'     => $UserData['Sistema_Email'],
            'Social_X'          => $UserData['Social_X'],
            'Social_Facebook'   => $UserData['Social_Facebook'],
            'Social_Instagram'  => $UserData['Social_Instagram'],
            'Social_Linkedin'   => $UserData['Social_Linkedin'],
            'baseUrl'           => $f3->get('BASE')
        ];

        /******************************/
        // Invoca el método de envío especializado para la API de SendingBlue/Brevo
        $result = $this->mailSender->sendSendingBlueMail($TemplateData, $query);    //Envio por Sending Blue

        /******************************/
        // Evalúa la respuesta del emisor y retorna el resultado o un booleano falso si no hubo respuesta positiva
        return ($result) ? $result : false;
    }

    /******************************************************************************/
    /******************************************************************************/
    protected function returnRutaVista($directorio, $aplicacion){
        //Generar ubicacion de las vistas
        $rutaController = substr($directorio, strpos($directorio, $aplicacion)); //se obtiene la ruta del controlador
        $rutaVista      = str_replace("controller", "views", $rutaController);   //se obtiene la ruta a la vista

        return $rutaVista;
    }

}
