<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class kanbanTareasTareas extends ControllerBase {

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
    /*                                  VISTAS                                    */
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
        //Se verifica si se permite Administrar Tableros Independiente de las Tareas
        if($UserData["KanbanTareasUsoTareas"]==2){
            /*******************************************************************/
            //Se genera la query
            $query = [
                'data'    => 'idTrabajo AS ID,Nombre',
                'table'   => 'kanban_trabajos',
                'join'    => '',
                'where'   => 'idEstado=1',
                'group'   => '',
                'having'  => '',
                'order'   => 'Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            //Ejecuto la query
            $xParams     = ['query' => $query];
            $arrTrabajos = $this->Base_GetList($xParams);
        //Si se permite junto con la creacion de tareas
        }else{
            $arrTrabajos   = [];
        }

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
                'arrTrabajos'   => $arrTrabajos,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'Tareas-formNew.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //Edit
    public function GetID($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => 'idTareas,idKanban,Tarea,idEstadoTrabajo,idTrabajo',
            'table'   => 'kanban_tareas_tareas',
            'join'    => '',
            'where'   => 'idTareas = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        //Se verifica si se permite Administrar Tableros Independiente de las Tareas
        if($UserData["KanbanTareasUsoTareas"]==2){
            /*******************************************************************/
            //Se genera la query
            $query = [
                'data'    => 'idTrabajo AS ID,Nombre',
                'table'   => 'kanban_trabajos',
                'join'    => '',
                'where'   => 'idEstado=1',
                'group'   => '',
                'having'  => '',
                'order'   => 'Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            //Ejecuto la query
            $xParams     = ['query' => $query];
            $arrTrabajos = $this->Base_GetList($xParams);
        //Si se permite junto con la creacion de tareas
        }else{
            $arrTrabajos   = [];
        }

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idEstadoTrabajo AS ID,Nombre',
            'table'   => 'core_estados_trabajos',
            'join'    => '',
            'where'   => 'idEstadoTrabajo!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'idEstadoTrabajo ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams          = ['query' => $query];
        $arrEstadoTrabajo = $this->Base_GetList($xParams);

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
                'Fnc_FormInputs'   => $this->FormInputs,
                'Fnc_Codification' => $this->Codification,
                'Fnc_ServerServer' => $this->ServerServer,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData,
                'arrEstadoTrabajo' => $arrEstadoTrabajo,
                'arrTrabajos'      => $arrTrabajos,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'Tareas-formEdit.php');
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
        $ndata_1 = isset($_POST['Tarea']) ? count($_POST['Tarea']) : 0;
        //generacion de errores
        if($ndata_1==0) {
            echo Response::sendData(500, 'No hay Tareas nuevas creadas');
        }else{
            /******************************/
            //Se genera el chequeo
            $DataTarea = 'Nueva Tarea Asignada:';
            /******************************/
            //Recorro las tareas ingresadas
            if(isset($ndata_1)&&$ndata_1!=0){
                for($j1 = 0; $j1 < $ndata_1; $j1++){
                    /******************************/
                    //Guardo la tarea
                    $DataTarea .= '<br/> - '.$_POST['Tarea'][$j1];
                    /******************************/
                    //Se agrega respuesta
                    $arrTareas = [
                        'idKanban'         => $_POST['idKanban'],               //idKanban
                        'Tarea'            => $_POST['Tarea'][$j1],             //Tarea
                        'idEstadoTrabajo'  => 1,                                //Estado abierto
                        'idTrabajo'        => ($_POST['idTrabajo'][$j1] ?? ''), //idTrabajo si existe
                    ];
                    /******************************/
                    //Se genera la query
                    $query = [
                        'data'      => 'idKanban,Tarea,idEstadoTrabajo,idTrabajo',
                        'required'  => 'idKanban,Tarea,idEstadoTrabajo',
                        'unique'    => '',
                        'encode'    => '',
                        'table'     => 'kanban_tareas_tareas',
                        'Post'      => $arrTareas
                    ];
                    //Ejecuto la query
                    $xParams = ['DataCheck' => '', 'query' => $query];
                    $this->Base_insert($xParams);
                }
            }

            /******************************/
            //Se agrega historial
            $arrTareas = [
                'idKanban'    => $_POST['idKanban'],      //idKanban
                'idUsuario'   => $_POST['idUsuario'],     //Usuario creador
                'Descripcion' => $DataTarea,              //Descripcion
                'Fecha'       => $_POST['Fecha_Actual'],  //Fecha actual
                'Hora'        => $_POST['Hora_Actual'],   //Hora actual
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
    //Editar por put (solo modificar datos)
    //Editar por post (modificar y subir archivos)
    public function Update(){
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            /******************************/
            //Se genera el chequeo
            $DataCheck = $this->dataCheck($_POST);

            /******************************/
            //Se genera la query
            $query = [
                'data'      => 'Tarea,idEstadoTrabajo,idTrabajo',
                'required'  => 'Tarea,idEstadoTrabajo',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'kanban_tareas_tareas',
                'where'     => 'idTareas',
                'Post'      => $_POST
            ];
            //Ejecuto la query
            $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
            $Response = $this->Base_update($xParams);

            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response===true) {
                /*******************************************************************/
                //Se genera la query
                $query = [
                    'data'    => 'idEstadoTrabajo AS ID,Nombre',
                    'table'   => 'core_estados_trabajos',
                    'join'    => '',
                    'where'   => 'idEstadoTrabajo!=0',
                    'group'   => '',
                    'having'  => '',
                    'order'   => 'idEstadoTrabajo ASC',
                    'limit'   => ConfigAPP::APP["N_MaxItems"]
                ];
                //Ejecuto la query
                $xParams          = ['query' => $query];
                $arrEstadoTrabajo = $this->Base_GetList($xParams);

                /*******************************************************************/
                //Se genera la query
                $query = [
                    'data'    => 'idTrabajo AS ID,Nombre',
                    'table'   => 'kanban_trabajos',
                    'join'    => '',
                    'where'   => 'idEstado=1',
                    'group'   => '',
                    'having'  => '',
                    'order'   => 'Nombre ASC',
                    'limit'   => ConfigAPP::APP["N_MaxItems"]
                ];
                //Ejecuto la query
                $xParams     = ['query' => $query];
                $arrTrabajos = $this->Base_GetList($xParams);

                /*******************************************************************/
                //Variables
                $arrEstadoNew    = [];
                $arrTrabajosNew    = [];
                //Se guardan los datos
                foreach ($arrEstadoTrabajo as $task){    $arrEstadoNew[$task['ID']]   = $task['Nombre'];}
                foreach ($arrTrabajos as $task){         $arrTrabajosNew[$task['ID']] = $task['Nombre'];}

                /******************************/
                //Se hacen comparaciones
                $comparacion = '';

                $campos = [
                    'Tarea'           => ['label' => 'Tarea'],
                    'idEstadoTrabajo' => ['label' => 'Estado',  'array' => $arrEstadoNew],
                    'idTrabajo'       => ['label' => 'Trabajo', 'array' => $arrTrabajosNew]
                ];

                foreach ($campos as $campo => $config) {
                    $oldCampo = 'Old_' . $campo;
                    if (isset($_POST[$campo], $_POST[$oldCampo]) && $_POST[$campo] != $_POST[$oldCampo]) {
                        $valorAntiguo = $config['array'][$_POST[$oldCampo]] ?? $_POST[$oldCampo];
                        $valorNuevo = $config['array'][$_POST[$campo]] ?? $_POST[$campo];
                        $comparacion .= "<br/> - Se cambia la {$config['label']} (de {$valorAntiguo} a {$valorNuevo})";
                    }
                }

                /******************************/
                //Se hacen comparaciones
                if($comparacion!=''){
                    /******************************/
                    //Se agrega historial
                    $arrTareas = [
                        'idKanban'    => $_POST['idKanban'],                           //idKanban
                        'idUsuario'   => $_POST['idUsuario'],                          //Usuario creador
                        'Descripcion' => 'Se cambian datos de la tarea:'.$comparacion, //Descripcion
                        'Fecha'       => $_POST['Fecha_Actual'],                       //Fecha actual
                        'Hora'        => $_POST['Hora_Actual'],                        //Hora actual
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
                }

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

    /******************************************************************************/
    //Se validan los datos
    private function dataCheck($POST){
        //Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => '',
            'ValidarNumero'             => '',
            'ValidarEntero'             => '',
            'ValidarRut'                => '',
            'ValidarPatente'            => '',
            'ValidarFecha'              => '',
            'ValidarHora'               => '',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => 'Tarea',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'Tarea',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Tarea',
            'ValidarEspaciosVacios'     => '',
            'ValidarMayusculas'         => '',
            'ValidarCoincidencias'      => '',
            'Post'                      => $POST,
        ];
        //Devuelvo
        return $DataChecking;
    }

}
