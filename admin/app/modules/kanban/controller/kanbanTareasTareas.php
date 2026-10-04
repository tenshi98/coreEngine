<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class kanbanTareasTareas extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $ServerServer;

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
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
    /*******************************************************************/
    // NewData
    /*******************************************************************/
    public function NewData($f3, $params){

        /************************************/
        // Se instancia
        $arrUserData = $this->getUserData($f3);

        /************************************/
        // Se obtiene el ID
        $KanbanID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($KanbanID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idKanban,Titulo',
            'table'   => 'kanban_tareas',
            'join'    => '',
            'where'   => 'idKanban = ?',
            'params'  => [$KanbanID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /************************************/
        // Se verifica si se tiene el permiso para visualizar el dato
        if($arrUserData["KanbanTareasUsoTareas"]==2){
            /************************************/
            // Se genera la query
            $query = [
                'data'    => 'idTrabajo AS ID,Nombre',
                'table'   => 'kanban_trabajos',
                'join'    => '',
                'where'   => 'idEstado = ?',
                'params'  => [1],
                'group'   => '',
                'having'  => '',
                'order'   => 'Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            // Preparo los datos
            $xParams     = ['query' => $query];
            // Ejecuto la query
            $arrTrabajos = $this->Base_GetList($xParams);
        // Si se permite junto con la creacion de tareas
        }else{
            $arrTrabajos['status'] = true;
            $arrTrabajos['data']   = [];
        }

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrTrabajos['status']){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'    => $this->FormInputs,
                'Fnc_Codification'  => $this->Codification,
                'Fnc_ServerServer'  => $this->ServerServer,
                /*=========== Datos Consultados ===========*/
                'rowData'       => $rowData['data'],
                'arrTrabajos'   => $arrTrabajos['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'Tareas-formNew.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrTrabajos]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Editar
    /*******************************************************************/
    public function GetID($f3, $params){

        /************************************/
        // Se instancia
        $arrUserData = $this->getUserData($f3);

        /************************************/
        // Se obtiene el ID
        $TareasID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($TareasID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idTareas,idKanban,Tarea,idEstadoTrabajo,idTrabajo',
            'table'   => 'kanban_tareas_tareas',
            'join'    => '',
            'where'   => 'idTareas = ?',
            'params'  => [$TareasID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /************************************/
        // Se verifica si se tiene el permiso para visualizar el dato
        if($arrUserData["KanbanTareasUsoTareas"]==2){
            /************************************/
            // Se genera la query
            $query = [
                'data'    => 'idTrabajo AS ID,Nombre',
                'table'   => 'kanban_trabajos',
                'join'    => '',
                'where'   => 'idEstado = ?',
                'params'  => [1],
                'group'   => '',
                'having'  => '',
                'order'   => 'Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            // Preparo los datos
            $xParams     = ['query' => $query];
            // Ejecuto la query
            $arrTrabajos = $this->Base_GetList($xParams);
        // Si se permite junto con la creacion de tareas
        }else{
            $arrTrabajos['status'] = true;
            $arrTrabajos['data']   = [];
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idEstadoTrabajo AS ID,Nombre',
            'table'   => 'core_estados_trabajos',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'idEstadoTrabajo ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams          = ['query' => $query];
        // Ejecuto la query
        $arrEstadoTrabajo = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrTrabajos['status'] && $arrEstadoTrabajo['status']){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'   => $this->FormInputs,
                'Fnc_Codification' => $this->Codification,
                'Fnc_ServerServer' => $this->ServerServer,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
                'arrTrabajos'      => $arrTrabajos['data'],
                'arrEstadoTrabajo' => $arrEstadoTrabajo['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'Tareas-formEdit.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrTrabajos,$arrEstadoTrabajo]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    /*                                  DATOS                                     */
    /******************************************************************************/
    /*******************************************************************/
    // Insertar
    /*******************************************************************/
    public function Insert($f3){

        /************************************/
        // Usuario creador
        $_POST['idUsuario'] = $f3->get('SESSION.DataInfo.UserID');

        /************************************/
        // Variables
        $ndata_1 = isset($_POST['Tarea']) ? count($_POST['Tarea']) : 0;
        // Generacion de errores
        if ($ndata_1==0) {
            Response::error('No hay Tareas nuevas creadas', 500);
        }

        /************************************/
        // Se genera el chequeo
        $DataTarea = 'Nueva Tarea Asignada:';
        /************************************/
        // Recorro las tareas ingresadas
        for($j1 = 0; $j1 < $ndata_1; $j1++){
            /************************************/
            //Guardo la tarea
            $DataTarea .= '<br/> - '.$_POST['Tarea'][$j1];
            /************************************/
            // Se agrega respuesta
            $arrTareas = [
                'idKanban'         => $_POST['idKanban'],               //idKanban
                'Tarea'            => $_POST['Tarea'][$j1],             //Tarea
                'idEstadoTrabajo'  => 1,                                //Estado abierto
                'idTrabajo'        => $_POST['idTrabajo'][$j1] ?? '',   //idTrabajo si existe
            ];
            /************************************/
            // Se genera la query
            $query = [
                'data'      => 'idKanban,Tarea,idEstadoTrabajo,idTrabajo',
                'required'  => 'idKanban,Tarea,idEstadoTrabajo',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'kanban_tareas_tareas',
                'Post'      => $arrTareas
            ];
            /************************************/
            // Se genera el chequeo
            $dataCheck_1 = $this->dataCheck_1($arrTareas);
            // Preparo los datos
            $xParams = ['DataCheck' => $dataCheck_1, 'query' => $query];
            // Ejecuto la query
            $this->Base_insert($xParams);
        }

        /************************************/
        //Se agrega historial
        $arrTareas = [
            'idKanban'    => $_POST['idKanban'],      //idKanban
            'idUsuario'   => $_POST['idUsuario'],     //Usuario creador
            'Descripcion' => $DataTarea,              //Descripcion
            'Fecha'       => $_POST['Fecha_Actual'],  //Fecha actual
            'Hora'        => $_POST['Hora_Actual'],   //Hora actual
        ];
        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idKanban,idUsuario,Descripcion,Fecha,Hora',
            'required'  => 'idKanban,idUsuario,Descripcion,Fecha,Hora',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'kanban_tareas_historial',
            'Post'      => $arrTareas
        ];
        /************************************/
        // Se genera el chequeo
        $dataCheck_2 = $this->dataCheck_2($arrTareas);
        // Preparo los datos
        $xParams = ['DataCheck' => $dataCheck_2, 'query' => $query, 'novalidate' => true];
        $this->Base_insert($xParams);

        /************************************/
        //devuelvo el ultimo id
        Response::success($_POST['idKanban']);

    }

    /*******************************************************************/
    // Editar por put (solo modificar datos)
    // Editar por post (modificar y subir archivos)
    /*******************************************************************/
    public function Update($f3){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Usuario creador
        $_POST['idUsuario'] = $f3->get('SESSION.DataInfo.UserID');

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idTareas,Tarea,idEstadoTrabajo,idTrabajo',
            'required'  => 'idTareas,Tarea,idEstadoTrabajo',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'kanban_tareas_tareas',
            'where'     => 'idTareas',
            'Post'      => $_POST
        ];
        /************************************/
        // Se genera el chequeo
        $dataCheck_1 = $this->dataCheck_1($_POST);
        // Preparo los datos
        $xParams  = ['DataCheck' => $dataCheck_1, 'query' => $query];
        // Ejecuto la query
        $Response = $this->Base_update($xParams);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if ($Response['status'] === false) {
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idEstadoTrabajo AS ID,Nombre',
            'table'   => 'core_estados_trabajos',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'idEstadoTrabajo ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams          = ['query' => $query];
        // Ejecuto la query
        $arrEstadoTrabajo = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idTrabajo AS ID,Nombre',
            'table'   => 'kanban_trabajos',
            'join'    => '',
            'where'   => 'idEstado = ?',
            'params'  => [1],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams     = ['query' => $query];
        // Ejecuto la query
        $arrTrabajos = $this->Base_GetList($xParams);

        /************************************/
        // Variables
        $arrEstadoNew    = [];
        $arrTrabajosNew    = [];
        //Se guardan los datos
        foreach ($arrEstadoTrabajo['data'] as $task){    $arrEstadoNew[$task['ID']]   = $task['Nombre'];}
        foreach ($arrTrabajos['data'] as $task){         $arrTrabajosNew[$task['ID']] = $task['Nombre'];}

        /************************************/
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
                $valorAntiguo  = $config['array'][$_POST[$oldCampo]] ?? $_POST[$oldCampo];
                $valorNuevo    = $config['array'][$_POST[$campo]] ?? $_POST[$campo];
                $comparacion  .= "<br/> - Se cambia la {$config['label']} (de {$valorAntiguo} a {$valorNuevo})";
            }
        }

        /************************************/
        //Se hacen comparaciones
        if($comparacion!=''){
            /************************************/
            //Se agrega historial
            $arrTareas = [
                'idKanban'    => $_POST['idKanban'],                           //idKanban
                'idUsuario'   => $_POST['idUsuario'],                          //Usuario creador
                'Descripcion' => 'Se cambian datos de la tarea:'.$comparacion, //Descripcion
                'Fecha'       => $_POST['Fecha_Actual'],                       //Fecha actual
                'Hora'        => $_POST['Hora_Actual'],                        //Hora actual
            ];
            /************************************/
            // Se genera la query
            $query = [
                'data'      => 'idKanban,idUsuario,Descripcion,Fecha,Hora',
                'required'  => 'idKanban,idUsuario,Descripcion,Fecha,Hora',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'kanban_tareas_historial',
                'Post'      => $arrTareas
            ];
            /************************************/
            // Se genera el chequeo
            $dataCheck_2 = $this->dataCheck_2($arrTareas);
            // Preparo los datos
            $xParams = ['DataCheck' => $dataCheck_2, 'query' => $query, 'novalidate' => true];
            $this->Base_insert($xParams);
        }

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data']);

    }

    /******************************************************************************/
    /*                             Métodos privados                               */
    /******************************************************************************/
    /*******************************************************************/
    // Se validan los datos
    /*******************************************************************/
    private function dataCheck_1($POST){
        // Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => '',
            'ValidarNumero'             => 'idKanban,idEstadoTrabajo,idTrabajo',
            'ValidarEntero'             => 'idKanban,idEstadoTrabajo,idTrabajo',
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
            'ValidarDominioEmail'       => '',
            'ValidarPasswordSegura'     => '',
            'ValidarFechaRango'         => '',
            'ValidarEdadMinima'         => '',
            'ValidarJSON'               => '',
            'ValidarUUID'               => '',
            'ValidarIP'                 => '',
            'ValidarSoloAlfanumerico'   => '',
            'ValidarSoloLetras'         => '',
            'Post'                      => $POST,
        ];
        // Retorno los datos
        return $DataChecking;
    }

    /*******************************************************************/
    // Se validan los datos
    /*******************************************************************/
    private function dataCheck_2($POST){
        // Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => '',
            'ValidarNumero'             => 'idKanban,idUsuario',
            'ValidarEntero'             => 'idKanban,idUsuario',
            'ValidarRut'                => '',
            'ValidarPatente'            => '',
            'ValidarFecha'              => 'Fecha',
            'ValidarHora'               => 'Hora',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => 'Descripcion',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => '',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Descripcion',
            'ValidarEspaciosVacios'     => '',
            'ValidarMayusculas'         => '',
            'ValidarCoincidencias'      => '',
            'ValidarDominioEmail'       => '',
            'ValidarPasswordSegura'     => '',
            'ValidarFechaRango'         => '',
            'ValidarEdadMinima'         => '',
            'ValidarJSON'               => '',
            'ValidarUUID'               => '',
            'ValidarIP'                 => '',
            'ValidarSoloAlfanumerico'   => '',
            'ValidarSoloLetras'         => '',
            'Post'                      => $POST,
        ];
        // Retorno los datos
        return $DataChecking;
    }

}
