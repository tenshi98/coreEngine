<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class ControllerWeb {
    /*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                 Instancias                                                      */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	//Definiciones
    private $mailSender;

    /************************************************************************************************************/
	//Instancias
    public function __construct($mailSender){
        $this->mailSender = $mailSender;
    }

    /*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
    /**
     * Punto de entrada central de seguridad (hook de Fat-Free Framework).
     *
     * F3 invoca automáticamente este método antes de ejecutar cualquier acción de
     * un controlador enrutado (formato 'controlador->metodo'). Se utiliza para
     * aplicar la protección CSRF en un único lugar y para publicar el token en las
     * vistas ($CSRF_TOKEN).
     *
     * Importante: al ejecutarse sólo en el despacho del router, las llamadas
     * internas entre controladores (por ejemplo `$otro->metodo(...)`) NO pasan por
     * aquí y por tanto no vuelven a validar el token. Es el mismo criterio que usa
     * el backoffice con `ControllerBase::beforeroute()`: el contexto validado
     * pertenece al punto de entrada (la petición HTTP) y lo heredan las llamadas
     * internas, sin necesidad de cambiar las firmas de los métodos.
     *
     * @param \Base|null $f3     Instancia de Fat-Free Framework.
     * @param array|null $params Parámetros capturados de la ruta (tokens @name).
     *
     * @return void
     */
    public function beforeroute($f3 = null, $params = null){

        // Verificación central de CSRF (solo métodos de escritura)
        CsrfToken::guard($f3);

    }

    /************************************************************************************************************/
    /**
     * Procesa y envía un correo electrónico utilizando el protocolo SMTP.
     *
     * Esta función extrae la información de configuración del sistema y redes sociales
     * desde la sesión del usuario, prepara una estructura de datos para la plantilla
     * y utiliza la clase MailSender para realizar el envío físico del mensaje.
     *
     * @param object $UserData  Instancia de datos de sesión.
     * @param object $BASE      Instancia de datos de configuración.
     * @param mixed  $query     Objeto o arreglo con la información específica del correo a enviar (destinatario, asunto, contenido).
     *
     * @return mixed Retorna el resultado del envío si es exitoso, o false en caso de fallo.
     */
    protected function Base_SMTPMail($UserData, $BASE, $query){

        /********************** Si todo esta ok **********************/
        // Construye el arreglo de datos que se inyectarán en la plantilla de correo
        $TemplateData = [
            'Sistema_IMGLogo'   => $UserData['Sistema_IMGLogo'],
            'Sistema_Direccion' => $UserData['Sistema_Direccion'],
            'Sistema_Email'     => $UserData['Sistema_Email'],
            'Social_X'          => $UserData['Social_X'],
            'Social_Facebook'   => $UserData['Social_Facebook'],
            'Social_Instagram'  => $UserData['Social_Instagram'],
            'Social_Linkedin'   => $UserData['Social_Linkedin'],
            'baseUrl'           => $BASE,
        ];

        // Inicializa el controlador de envíos de correo
        $mailSender = new MailSender();
        // Ejecuta el envío SMTP pasando la configuración de la plantilla y los datos del mensaje
        $result     = $mailSender->sendSMTPMail($TemplateData, $query); //Envio por correo normal

        /**********************  Retorno datos  **********************/
        // Evalúa la respuesta del emisor y retorna el resultado o un booleano falso si no hubo respuesta positiva
        return ($result) ? $result : false;
    }

    /************************************************************************************************************/
    /**
     * Procesa y envía un correo electrónico utilizando la integración específica de Gmail.
     *
     * Esta función recopila la configuración visual, corporativa y de redes sociales desde
     * la sesión activa del usuario. A diferencia del método SMTP estándar, esta función
     * incluye el parámetro 'MainPathUrl' en el conjunto de datos de la plantilla antes de
     * delegar el envío a la clase MailSender mediante el método especializado para Gmail.
     *
     * @param object $UserData  Instancia de datos de sesión.
     * @param object $BASE      Instancia de datos de configuración.
     * @param mixed  $query     Objeto o arreglo con la información específica del correo a enviar (destinatario, asunto, contenido).
     *
     * @return mixed Retorna el objeto de respuesta del envío si es exitoso, o false en caso de error o ausencia de resultado.
     */
    protected function Base_GMail($UserData, $BASE, $query){

        /********************** Si todo esta ok **********************/
        // Construye el arreglo de datos que se inyectarán en la plantilla de correo
        $TemplateData = [
            'Sistema_IMGLogo'   => $UserData['Sistema_IMGLogo'],
            'Sistema_Direccion' => $UserData['Sistema_Direccion'],
            'Sistema_Email'     => $UserData['Sistema_Email'],
            'Social_X'          => $UserData['Social_X'],
            'Social_Facebook'   => $UserData['Social_Facebook'],
            'Social_Instagram'  => $UserData['Social_Instagram'],
            'Social_Linkedin'   => $UserData['Social_Linkedin'],
            'baseUrl'           => $BASE,
            'MainPathUrl'       => $UserData['MainPathUrl'],
        ];

        // Inicializa el controlador de envíos de correo
        $mailSender = new MailSender();
        // Ejecuta el envío a través del canal específico configurado para Gmail
        $result     = $mailSender->sendGMail($TemplateData, $query);    //Envio por gmail

        /**********************  Retorno datos  **********************/
        // Evalúa la respuesta del emisor y retorna el resultado o un booleano falso si no hubo respuesta positiva
        return ($result) ? $result : false;
    }

    /************************************************************************************************************/
    /**
     * Gestiona el envío de correos electrónicos a través del servicio externo SendingBlue (Brevo).
     *
     * Esta función extrae los metadatos corporativos y de redes sociales desde la sesión
     * del usuario para configurar la apariencia de la plantilla de correo. Posteriormente,
     * delega la ejecución técnica a la clase MailSender utilizando su método específico
     * para la API de SendingBlue.
     *
     * @param object $UserData  Instancia de datos de sesión.
     * @param object $BASE      Instancia de datos de configuración.
     * @param mixed  $query     Objeto o arreglo con la información específica del correo a enviar (destinatario, asunto, contenido).
     *
     * @return mixed Retorna el objeto de respuesta del servicio SendingBlue si la operación es exitosa, o false en caso contrario.
     */
    protected function Base_SendingBlue($UserData, $BASE, $query){

        /********************** Si todo esta ok **********************/
        // Construye el arreglo de datos que se inyectarán en la plantilla de correo
        $TemplateData = [
            'Sistema_IMGLogo'   => $UserData['Sistema_IMGLogo'],
            'Sistema_Direccion' => $UserData['Sistema_Direccion'],
            'Sistema_Email'     => $UserData['Sistema_Email'],
            'Social_X'          => $UserData['Social_X'],
            'Social_Facebook'   => $UserData['Social_Facebook'],
            'Social_Instagram'  => $UserData['Social_Instagram'],
            'Social_Linkedin'   => $UserData['Social_Linkedin'],
            'baseUrl'           => $BASE,
        ];

        // Inicializa el controlador de envíos de correo
        $mailSender = new MailSender();
        // Invoca el método de envío especializado para la API de SendingBlue/Brevo
        $result     = $mailSender->sendSendingBlueMail($TemplateData, $query);    //Envio por Sending Blue

        /**********************  Retorno datos  **********************/
        // Evalúa la respuesta del emisor y retorna el resultado o un booleano falso si no hubo respuesta positiva
        return ($result) ? $result : false;
    }

    /************************************************************************************************************/
    /**
     * Traduce la ruta del sistema de archivos de un controlador a su ruta de vista correspondiente.
     *
     * Esta función asume una arquitectura de carpetas basada en convenciones donde las rutas
     * contienen los directorios "controller" y "views". Realiza una manipulación de strings
     * para extraer la ruta relativa desde el nombre de la aplicación y sustituir el
     * segmento de lógica por el de presentación.
     *
     * @param string $directorio El path absoluto o relativo completo del archivo del controlador.
     * @param string $aplicacion El nombre o segmento de la aplicación que sirve como punto de anclaje.
     *
     * @return string La ruta transformada hacia el directorio de vistas.
     */
    protected function returnRutaVista($directorio, $aplicacion){

        /********************** Si todo esta ok **********************/
        // Localiza la posición de la aplicación en el path y extrae la ruta a partir de ese punto
        $rutaController = substr($directorio, strpos($directorio, $aplicacion)); //se obtiene la ruta del controlador

        // Reemplaza la subcadena "controller" por "views" para apuntar al directorio de plantillas
        $rutaVista      = str_replace("controller", "views", $rutaController);   //se obtiene la ruta a la vista

        /**********************  Retorno datos  **********************/
        // Devuelve la cadena de texto con la ruta final hacia la vista
        return $rutaVista;

    }

}
