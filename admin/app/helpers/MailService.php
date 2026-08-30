<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Envía correos transaccionales del sistema (por ahora, recuperación de
 * contraseña), delegando el armado y envío del mensaje en ControllerBase.
 */
class MailService {

    /******************************************************************************/
    // Variables
    private $DB_conn;
    private $queryBuilder;

    /******************************************************************************/
    /**
     * Inicializa la conexión a base de datos y el constructor de queries.
     *
     * @return void
     */
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $this->DB_conn       = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $this->queryBuilder  = new QueryBuilder();
    }

    /******************************************************************************/
    /**
     * Arma y envía el correo de aviso de nueva contraseña generada.
     *
     * @param Base   $f3           Instancia de Fat-Free Framework (usa 'BASE').
     * @param array  $dataUser     Datos del usuario destinatario ('email', ...).
     * @param array  $SystemData   Configuración del sistema (logo, contacto, redes, motor de correo).
     * @param string $NewPasswords Nueva contraseña generada, incluida en el cuerpo del mensaje.
     *
     * @return mixed Resultado de ControllerBase::Base_SelectMail() (true si el envío fue exitoso).
     */
    public function sendPasswordReset($f3, $dataUser, $SystemData, $NewPasswords){

        /******************************/
        // Se agrega respuesta
        $arrData = [
            'Asunto'  => 'Cambio de contraseña',
            'Hacia'   => $dataUser['email'],
            'Mensaje' => 'Se ha generado una nueva contraseña para el email '.$dataUser['email'].', su nueva contraseña es: '.$NewPasswords,
        ];
        // Se genera la query
        $query = [
            'data'      => 'Asunto,Hacia,Mensaje',
            'template'  => 1,
            'Post'      => $arrData,
        ];

        /******************************/
        // Se arma la informacion del sistema
        $UserData['Sistema_IMGLogo']   = $SystemData['data']['Sistema_IMGLogo'];
        $UserData['Sistema_Direccion'] = $SystemData['data']['Sistema_Direccion'];
        $UserData['Sistema_Email']     = $SystemData['data']['Sistema_Email'];
        $UserData['Social_X']          = $SystemData['data']['Social_X'];
        $UserData['Social_Facebook']   = $SystemData['data']['Social_Facebook'];
        $UserData['Social_Instagram']  = $SystemData['data']['Social_Instagram'];
        $UserData['Social_Linkedin']   = $SystemData['data']['Social_Linkedin'];
        $BASE                          = $f3->get('BASE');

        /******************************/
        // Se cargan las clases
        $ControllerBase = new ControllerBase($this->DB_conn, $this->queryBuilder, '');
        // Se hace el envio del correo
        $Response = $ControllerBase->Base_SelectMail($UserData, $BASE, $query, $SystemData['data']['Config_motorEmail']);
        // Retorno de datos
        return $Response;

    }





}
