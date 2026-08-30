<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Controla y registra intentos de acceso sospechosos de fuerza bruta,
 * bloqueando temporalmente por email/IP y escalando a lista negra según los
 * umbrales configurados en ConfigAPP.
 */
class BruteForceService {

    /******************************************************************************/
    // Variables
    private $DBConn;
    private $QBuilder;

    /******************************************************************************/
    /**
     * Inicializa la conexión a base de datos y el constructor de queries.
     *
     * @return void
     */
    public function __construct(){
        /*================== Instancias =================*/
        $this->DBConn    = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $this->QBuilder  = new QueryBuilder();
    }

    /******************************************************************************/
    /**
     * Cuenta los intentos fallidos recientes (últimas 2 horas) por email o
     * por IP y determina si corresponde bloquear el acceso.
     *
     * Si se supera el umbral máximo (checkBruteMaxConections), además envía
     * la IP a lista negra.
     *
     * @param string $Email     Email involucrado en el intento de acceso.
     * @param string $IP_Client IP de origen del intento de acceso.
     *
     * @return bool true si el acceso debe bloquearse; false si puede continuar.
     */
    public function check($Email, $IP_Client){
        /**********************************************************************/
        // Variable
        $TimeValid = time() - (2 * 60 * 60);  //Tiempo actual menos 2 horas

        /******************************/
        // Se genera la query
        $query = [
            'data'   => 'idAcceso',
            'table'  => 'usuarios_checkbrute',
            'join'   => '',
            'where'  => '(Email = ? OR IP_Client = ?) AND DateTime > ?',
            'params'  => [$Email, $IP_Client, $TimeValid],
            'group'  => '',
            'having' => '',
            'order'  => 'idAcceso DESC',
            'limit'  => 60
        ];

        /******************************/
        // Ejecuto la query
        $num_rows = $this->QBuilder->queryNRows($query, $this->DBConn);

        /**********************************************************************/
        // REGLA: Evaluar primero el límite más alto (Máximo / Lista Negra)
        if ($num_rows['data'] > ConfigAPP::APP["checkBruteMaxConections"]) {
            // Se cargan las clases
            $Server = new FunctionsServerSecurity();
            // Le envio al servidor la tarea de enviarlo al black list
            $Server->sendIPtoBlackList($IP_Client);
            // Dar respuesta de bloqueo
            return true;
        // Si no pasó el máximo, evaluamos si al menos pasó el límite normal de bloqueo
        } elseif ($num_rows['data'] > ConfigAPP::APP["checkBruteConections"]) {
            // Dar respuesta de bloqueo standard
            return true;
        } else {
            // No está bloqueado, puede intentar iniciar sesión
            return false;
        }

    }

    /******************************************************************************/
    /**
     * Inserta un registro de intento de acceso fallido/sospechoso en
     * usuarios_checkbrute, utilizado luego por check() para contar intentos.
     *
     * @param string $Fecha        Fecha del intento.
     * @param string $Hora         Hora del intento.
     * @param int    $DateTime     Timestamp del intento.
     * @param string $Email       Email recibido en el intento.
     * @param string $Password    Password recibida en el intento.
     * @param string $IP_Client   IP de origen del intento.
     * @param string $Agent_Transp User agent del cliente.
     *
     * @return void
     */
    public function register($Fecha, $Hora, $DateTime, $Email, $Password, $IP_Client, $Agent_Transp){

        /******************************/
        // Se agrega respuesta
        $Post = [
            'Fecha'        => $Fecha,
            'Hora'         => $Hora,
            'DateTime'     => $DateTime,
            'Email'        => $Email,
            'Password'     => $Password,
            'IP_Client'    => $IP_Client,
            'Agent_Transp' => $Agent_Transp,
        ];
        // Se genera la query
        $query = [
            'data'      => 'Fecha, Hora, DateTime, Email, Password, IP_Client, Agent_Transp',
            'required'  => '',
            'unique'    => '',
            'table'     => 'usuarios_checkbrute',
            'Post'      => $Post,
        ];
        // Ejecuto la query
        $this->QBuilder->queryInsert($query, $this->DBConn);


    }



}
