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
    public function sendSMTPMail($TemplateData, $query){

        /**********************  Configuración   **********************/
        $ConfigMail = [
            'ServerURL'    => ConfigMail::SMTPSender["SERVERURL"],
            'ServerPort'   => ConfigMail::SMTPSender["SERVERPORT"],
            'ServerSecure' => ConfigMail::SMTPSender["SERVERSECURE"],
            'UserName'     => ConfigMail::SMTPSender["USERNAME"],
            'UserEmail'    => ConfigMail::SMTPSender["USEREMAIL"],
            'UserPass'     => ConfigMail::SMTPSender["PASSWORD"],
        ];

        /**********************  Validaciones   **********************/
        $DataVal = $this->validateMail($query);

        /**********************  Retorno datos  **********************/
        return ($DataVal===true) ? $this->sendMail($TemplateData, $ConfigMail, $query) : $DataVal;

    }

    /******************************************************************************/
    public function sendGMail($TemplateData, $query){

        /**********************  Configuración   **********************/
        $ConfigMail = [
            'ServerURL'    => ConfigMail::GmailSender["SERVERURL"],
            'ServerPort'   => ConfigMail::GmailSender["SERVERPORT"],
            'ServerSecure' => ConfigMail::GmailSender["SERVERSECURE"],
            'UserName'     => ConfigMail::GmailSender["USERNAME"],
            'UserEmail'    => ConfigMail::GmailSender["USEREMAIL"],
            'UserPass'     => ConfigMail::GmailSender["PASSWORD"],
        ];

        /**********************  Validaciones   **********************/
        $DataVal = $this->validateMail($query);

        /**********************  Retorno datos  **********************/
        return ($DataVal===true) ? $this->sendMail($TemplateData, $ConfigMail, $query) : $DataVal;

    }

    /******************************************************************************/
    public function sendSendingBlueMail($TemplateData, $query){

        /**********************  Configuración   **********************/
        //Se crea arreglo para adjuntar datos
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
            "htmlContent" => $this->templateEmail($TemplateData, $query['template'], $query['Post'])
        );

        /**********************  Validaciones   **********************/
        $DataVal = $this->validateMail($query);

        /**********************  Retorno datos  **********************/
        if($DataVal===true){
            try {
                //envio de los datos
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, ConfigMail::SendingBlueSender["SERVERURL"]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                $headers = array();
                $headers[] = 'Accept: application/json';
                $headers[] = 'Api-Key: '.ConfigMail::SendingBlueSender["SERVERAPI"];
                $headers[] = 'Content-Type: application/json';
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_exec($ch);
                curl_close($ch);

                return true;
            } catch (Exception $e) {
                return false;
            }
        }else{
            return $DataVal;
        }
    }

    /******************************************************************************/
    public function testMailTemplate($TemplateData, $query){

        /**********************  Validaciones   **********************/
        $DataVal = $this->validateMail($query);

        /**********************  Retorno datos  **********************/
        return ($DataVal===true) ? $this->templateEmail($TemplateData, $query['template'], $query['Post']) : $DataVal;

    }

    /*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Internos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
    /******************************************************************************/
    private function validateMail($query){

        /******************************************/
        //Se verifica si hay datos
        if(!isset($query['data']) || $query['data']==''){         return false; }
        if(!isset($query['template']) || $query['template']==''){ return false; }

        /******************************************/
        //Variables
        $arrData = $this->CommonData->parseDataCommas($query['data']); //Separacion por comas
        $errors  = [];
        //Recorro
        foreach ($arrData as $field) {
            //verifico si existe el dato
            if (empty($query['Post'][$field])) {
                $errors[] = ["message" => "$field es obligatorio"];
            }
        }

        /******************************************/
        //si no hay errores
        return (empty($errors)) ? true : $errors;

    }
    /******************************************************************************/
    private function sendMail($TemplateData, $ConfigMail, $query){

        /******************************************/
        //Variable
        $ServerURL    = $ConfigMail['ServerURL'];
        $ServerPort   = $ConfigMail['ServerPort'];
        $ServerSecure = $ConfigMail['ServerSecure'];
        $UserName     = $this->DataText->desanitizarTexto($ConfigMail['UserName']);
        $UserEmail    = $this->DataText->desanitizarTexto($ConfigMail['UserEmail']);
        $UserPass     = $ConfigMail['UserPass'];
        $Hacia        = $this->DataText->desanitizarTexto($query['Post']['Hacia']);
        $Asunto       = $this->DataText->desanitizarTexto($query['Post']['Asunto']);

        /******************************************/
        //Configuracion de envio
        $smtp = new SMTP ( $ServerURL, $ServerPort, $ServerSecure, $UserEmail, $UserPass );
        //Configuracion de
        $smtp->set('From', '"'.$UserName.'" <'.$UserEmail.'>');  //Quien envia el correo
        $smtp->set('To', '<'.$Hacia.'>');       //Quien lo recibe
        $smtp->set('Subject', $Asunto);         //Asunto
        $smtp->set('Errors-to', '<'.$UserEmail.'>');             //En caso de error en la entrega informar a
        //Creacion del mensaje n base a una plantilla
        $message = $this->templateEmail($TemplateData, $query['template'], $query['Post']);
        //Se hace el envio
        $sent  = $smtp->send($message, true);
        //Se guardan registros
        $mylog = $smtp->log();
        //envio correcto
        return ($sent) ? true : $mylog;

    }
    /******************************************************************************/
    private function templateEmail($TemplateData, $Template, $Data){

       try {
            /******************************************/
            //armar plantillas
            switch ($Template) {
                /******************************************/
                //Normal
                case 1:
                    $this->TemplateRender->templatePath('../app/templates/Mail/mailTemplate_1.php');
                    $this->TemplateRender->assign('title', 'maqueta');
                    $this->TemplateRender->assign('Mensaje', $Data['Mensaje']);
                    break;
                /******************************************/
                //Formato 1
                case 2:
                    /********************/
                    $icons = [
                        'Social_X'        => ['twitter-black.png', 'X'],
                        'Social_Facebook' => ['facebook-black.png', 'Facebook'],
                        'Social_Instagram'=> ['instagram-black.png', 'Instagram'],
                        'Social_Linkedin' => ['linkedin-black.png', 'Linkedin'],
                    ];
                    //Se imprimen los iconos de las redes sociales
                    $Social_icon = '';
                    foreach ($icons as $key => [$img, $alt]) {
                        if (!empty($TemplateData[$key])) {
                            $Social_icon .= '<td style="padding-top: 3px; padding-right: 20px;"><a target="_blank" rel="noopener noreferrer" href="'.$TemplateData[$key].'" ><img src="'.$TemplateData['baseUrl'].'/img/social_icons/'.$img.'" width="16" alt="'.$alt.'" draggable="false"></a></td>';
                        }
                    }
                    /********************/
                    $this->TemplateRender->templatePath('../app/templates/Mail/mailTemplate_2.php');
                    $this->TemplateRender->assign('title', 'maqueta');
                    $this->TemplateRender->assign('CompanyLogo', !empty($TemplateData['Sistema_IMGLogo']) ? $TemplateData['baseUrl'].'/upload/'.$TemplateData['Sistema_IMGLogo'] : $TemplateData['baseUrl'].'/img/logo.png');
                    $this->TemplateRender->assign('baseUrl', $TemplateData['baseUrl']);
                    $this->TemplateRender->assign('Sistema_Direccion', $TemplateData['Sistema_Direccion']);
                    $this->TemplateRender->assign('Sistema_Email', $TemplateData['Sistema_Email']);
                    $this->TemplateRender->assign('Social_icon', $Social_icon);
                    $this->TemplateRender->assign('Asunto', $Data['Asunto']);
                    $this->TemplateRender->assign('Mensaje', $Data['Mensaje']);
                    break;
                /******************************************/
                //Otra opcion
                case 3:
                    //
                    break;

            }
            //Generar el correo HTML
            return $this->TemplateRender->render();
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }

    }

}

