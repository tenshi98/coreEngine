<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class kanbanTareasParticipantes extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $ServerServer;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'kanbanTareas';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->ServerServer   = new FunctionsServerServer();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  DATOS                                     */
    /******************************************************************************/
    /******************************************************************************/
    //NewData
    public function NewData($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => 'idKanban,Titulo',
            'table'   => 'kanban_tareas',
            'join'    => '',
            'where'   => 'idKanban = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idUsuario',
            'table'   => 'kanban_tareas_participantes',
            'join'    => '',
            'where'   => 'idKanban = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'idUsuario ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams       = ['query' => $query];
        $arrExistentes = $this->Base_GetList($xParams);

        //Recorro
        $xWhere = 'idTipoUsuario!=1 AND idEstado=1';
        foreach ($arrExistentes as $usr){
            $xWhere .= ' AND idUsuario!='.$usr['idUsuario'];
        }

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idUsuario AS ID,Nombre',
            'table'   => 'usuarios_listado',
            'join'    => '',
            'where'   => $xWhere,
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams     = ['query' => $query];
        $arrUsuarios = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if ($rowData!==false) {
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'    => $this->FormInputs,
                'Fnc_Codification'  => $this->Codification,
                'Fnc_ServerServer'  => $this->ServerServer,
                /*=========== Datos Consultados ===========*/
                'rowData'       => $rowData,
                'arrUsuarios'   => $arrUsuarios,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'Participantes-formNew.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    /*                                  DATOS                                     */
    /******************************************************************************/
    /******************************************************************************/
    //Crear
    public function Insert(){

        /*******************************************************************/
        //variables
        $ndata_2 = isset($_POST['idParticipante']) ? count($_POST['idParticipante']) : 0;
        //generacion de errores
        if($ndata_2==0) {
            echo Response::sendData(500, 'No hay Participantes en la tarea');
        }else{
            /******************************/
            //Recorro las tareas ingresadas
            if(isset($ndata_2)&&$ndata_2!=0){
                for($j2 = 0; $j2 < $ndata_2; $j2++){
                    /******************************/
                    //Se agrega respuesta
                    $arrParticipantes = [
                        'idKanban'  => $_POST['idKanban'],            //idKanban
                        'idUsuario' => $_POST['idParticipante'][$j2], //Participantes
                    ];
                    /******************************/
                    //Se genera la query
                    $query = [
                        'data'      => 'idKanban,idUsuario',
                        'required'  => 'idKanban,idUsuario',
                        'unique'    => '',
                        'encode'    => '',
                        'table'     => 'kanban_tareas_participantes',
                        'Post'      => $arrParticipantes
                    ];
                    //Ejecuto la query
                    $xParams = ['DataCheck' => '', 'query' => $query];
                    $this->Base_insert($xParams);
                }
            }
            /******************************/
            //Se agrega historial
            $arrTareas = [
                'idKanban'    => $_POST['idKanban'],             //idKanban
                'idUsuario'   => $_POST['idUsuario'],            //Usuario creador
                'Descripcion' => 'Se agregan nuevos encargados', //Descripcion
                'Fecha'       => $_POST['Fecha_Actual'],         //Fecha actual
                'Hora'        => $_POST['Hora_Actual'],          //Hora actual
            ];
            /******************************/
            //Se genera la query
            $query = [
                'data'      => 'idKanban,idUsuario,Descripcion,Fecha,Hora',
                'required'  => 'idKanban,idUsuario,Descripcion,Fecha,Hora',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'kanban_tareas_historial',
                'Post'      => $arrTareas
            ];
            //Ejecuto la query
            $xParams = ['DataCheck' => '', 'query' => $query, 'novalidate' => true];
            $this->Base_insert($xParams);

            /******************************/
            //devuelvo el ultimo id
            echo Response::sendData(200, $_POST['idKanban']);
        }

    }

    /******************************************************************************/
    //Borrar dato y archivos
    public function Delete(){
        //Verificacion metodo PUT
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            //Se parsean los datos
            parse_str(file_get_contents("php://input"),$dataDelete);

            /******************************************/
            //Se genera la query
            $query = [
                'data'    => 'usuarios_listado.Nombre',
                'table'   => 'kanban_tareas_participantes',
                'join'    => 'LEFT JOIN usuarios_listado ON usuarios_listado.idUsuario  = kanban_tareas_participantes.idUsuario',
                'where'   => 'kanban_tareas_participantes.idParticipantes = "'.$this->Codification->encryptDecrypt('decrypt',$dataDelete['idParticipantes']).'"',
                'group'   => '',
                'having'  => '',
                'order'   => ''
            ];
            //Ejecuto la query
            $xParams = ['query' => $query];
            $rowData = $this->Base_GetByID($xParams);

            /******************************/
            //Se genera la query
            $query = [
                'files'       => '',
                'table'       => 'kanban_tareas_participantes',
                'where'       => 'idParticipantes',
                'SubCarpeta'  => '',
                'Post'        => $dataDelete
            ];
            //Ejecuto la query
            $xParams  = ['query' => $query];
            $Response = $this->Base_delete($xParams);

            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response===true) {
                /******************************/
                //Se agrega historial
                $arrTareas = [
                    'idKanban'    => $this->Codification->encryptDecrypt('decrypt', $dataDelete['idKanbanDel']),  //idKanban
                    'idUsuario'   => $dataDelete['idUsuarioDel'],                                            //Usuario creador
                    'Descripcion' => 'Encargado '.$rowData['Nombre'].' Borrado',                             //Descripcion
                    'Fecha'       => $dataDelete['Fecha_Actual'],                                            //Fecha actual
                    'Hora'        => $dataDelete['Hora_Actual'],                                             //Hora actual
                ];
                /******************************/
                //Se genera la query
                $query = [
                    'data'      => 'idKanban,idUsuario,Descripcion,Fecha,Hora',
                    'required'  => 'idKanban,idUsuario,Descripcion,Fecha,Hora',
                    'unique'    => '',
                    'encode'    => '',
                    'table'     => 'kanban_tareas_historial',
                    'Post'      => $arrTareas
                ];
                //Ejecuto la query
                $xParams = ['DataCheck' => '', 'query' => $query, 'novalidate' => true];
                $this->Base_insert($xParams);
                /******************************/
                // Devuelvo $Response con código 200 (OK)
                echo Response::sendData(200, $Response);
            } else {
                // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
                // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
                echo Response::sendData(500, $Response);
            }

        }else {
            echo Response::sendData(500, "Error en el Request Method");
        }
    }

}
