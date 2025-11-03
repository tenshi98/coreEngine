<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class example extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $DBConn;
    private $QBuilder;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->DBConn    = $DB_conn_1;
        $this->QBuilder  = $queryBuilder;
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    //Login
    public function updateDataDemo($f3){

        /******************************************/
        //Variable para las querys
        $arrQuery  = array();

        /*******************************************************/
        /*        SE ACTUALIZAN LOS DATOS DEL PERFIL           */
        /*******************************************************/
        //Se arma la query
        $arrQuery[] = "UPDATE `usuarios_listado` SET `password` = 'SFRjQTFXSnBsNWUrVmNwUHRsVHhSdz09', `idTipoUsuario` = 2, `idEstado` = 1, `email` = 'demo1@testmail.com', `Nombre` = 'Usuario Demo', `Rut` = NULL, `fNacimiento` = NULL, `Fono` = NULL, `idCiudad` = NULL, `idComuna` = NULL, `Direccion` = NULL, `Direccion_img` = NULL, `Ultimo_acceso` = NULL, `Social_X` = NULL, `Social_Facebook` = NULL, `Social_Instagram` = NULL, `Social_Linkedin` = NULL, `IP_Client` = NULL, `Agent_Transp` = NULL, `idMenuPosicion` = 2 WHERE `idUsuario` = 2";

        /*******************************************************/
        /*  SE BORRAN LOS DATOS INGRESADOS POR LOS EXTERNOS    */
        /*******************************************************/
        //Se arma la query
        $arrQuery[] = "TRUNCATE TABLE kanban_tareas";
        $arrQuery[] = "TRUNCATE TABLE kanban_tareas_historial";
        $arrQuery[] = "TRUNCATE TABLE kanban_tareas_participantes";
        $arrQuery[] = "TRUNCATE TABLE kanban_tareas_tareas";

        /*******************************************************/
        /*  SE BORRAN LOS DATOS INGRESADOS POR USUARIOS        */
        /*******************************************************/
        //Se arma la query
        $arrQuery[] = "TRUNCATE TABLE usuarios_accesos";
        $arrQuery[] = "TRUNCATE TABLE usuarios_checkbrute";

        /************************************************/
        /************************************************/
        //Verifico si existe
        if($arrQuery){
            //recorro
            foreach ($arrQuery as $query) {
                //Se ejecuta la query
                //$xParams = ['query' => $query];
                //$result  = $this->Base_queryExecute($xParams);
                echo '<br/>'.$query;
            }
        }

        //  */5 * * * * wget -q -O /dev/null "https://democoreengine.digitalcreations.cl/cron/tab/run" > /dev/null 2>&1
    }

}
