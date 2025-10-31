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
    //Envio de correo (solo un correo, con uno o varios receptores)
    protected function Base_SMTPMail($f3, $query, $UserData){

        /******************************************/
        //Se arman los datos
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
        //Ejecuto la query
        $result = $this->mailSender->sendSMTPMail($TemplateData, $query); //Envio por correo normal

        /******************************/
        //Si hay resultados
        return ($result) ? $result : false;
    }

    /******************************************************************************/
    //Envio de correo (solo un correo, con uno o varios receptores)
    protected function Base_GMail($f3, $query, $UserData){

        /******************************************/
        //Se arman los datos
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
        //Ejecuto la query
        $result = $this->mailSender->sendGMail($TemplateData, $query);    //Envio por gmail

        /******************************/
        //Si hay resultados
        return ($result) ? $result : false;
    }

    /******************************************************************************/
    //Envio de correo (solo un correo, con uno o varios receptores)
    protected function Base_SendingBlue($f3, $query, $UserData){

        /******************************************/
        //Se arman los datos
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
        //Ejecuto la query
        $result = $this->mailSender->sendSendingBlueMail($TemplateData, $query);    //Envio por Sending Blue

        /******************************/
        //Si hay resultados
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
