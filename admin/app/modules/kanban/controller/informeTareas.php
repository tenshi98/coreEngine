<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class informeTareas extends ControllerBase {

    /******************************************************************************/
    // Variables
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataDate;
    private $CommonData;
    private $WidgetsCommon;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'informeTareas';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->DataDate       = new FunctionsDataDate();
		$this->CommonData     = new FunctionsCommonData();
		$this->WidgetsCommon  = new UIWidgetsCommon();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //Listar Todo
    public function listAll($f3){
        /*******************************************************************/
        // Se genera la query
        $query = [
            'data'    => 'idPrioridad AS ID,Nombre',
            'table'   => 'core_prioridades',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'idPrioridad ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams      = ['query' => $query];
        $arrPrioridad = $this->Base_GetList($xParams);

        /*******************************************************************/
        // Se genera la query
        $query = [
            'data'    => 'idEstadoCierre AS ID,Nombre',
            'table'   => 'core_estados_cierre',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'idEstadoCierre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams         = ['query' => $query];
        $arrEstadoCierre = $this->Base_GetList($xParams);

        /******************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Informes',
            'PageDescription' => 'Testeos de Informes.',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            'TableTitle'      => 'Informes',
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
            /*===========   Funcionalidad   ===========*/
            'Fnc_FormInputs'   => $this->FormInputs,
            'Fnc_Codification' => $this->Codification,
            /*=========== Datos Consultados ===========*/
            'arrPrioridad'     => $arrPrioridad['data'],
            'arrEstadoCierre'  => $arrEstadoCierre['data'],
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
    }

    /******************************************************************************/
    //List
    public function UpdateList($f3){
        /*******************************************************************/
        // Variables
        $WhereData_int     = 'idPrioridad,Fecha,idEstadoCierre';  // Datos búsqueda exacta
        $WhereData_string  = 'Titulo';                            // Datos búsqueda relativa
        $WhereData_between = '';                                  // Datos búsqueda Between
        $whereInt          = '';                                  // Se crea cadena
        $whereParams       = [];                                  // Valores bindeados asociados a $whereInt
        /******************************************/
        // Se validan las fechas
        $RespDataBetween = $this->searchValidateDates($WhereData_between);
        if($RespDataBetween!=''){
            Response::error($RespDataBetween, 500);
        }
        // Agrego variable busqueda
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_int, 'kanban_tareas', 1);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_string, 'kanban_tareas', 2);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_between, 'kanban_tareas', 3);
        $whereInt = $r['where']; $whereParams = $r['params'];

        /*******************************************************************/
        // Se genera la query
        $query = [
            'data'    => '
                kanban_tareas.idKanban AS ID,
                kanban_tareas.idKanban,
                kanban_tareas.idKanbanEstado,
                core_prioridades.Nombre AS PrioridadNombre,
                core_prioridades.Color AS PrioridadColor,
                kanban_tareas.Fecha,
                kanban_tareas.Titulo,
                usuarios_listado.Nombre AS UsuarioNombre,
                usuarios_listado.Direccion_img AS UsuarioImg,
                kanban_estados.Nombre AS KanbanEstado,
                core_estados_colores.Nombre AS KanbanColor',
            'table'   => 'kanban_tareas',
            'join'    => '
                LEFT JOIN core_prioridades             ON core_prioridades.idPrioridad         = kanban_tareas.idPrioridad
                LEFT JOIN kanban_tareas_participantes  ON kanban_tareas_participantes.idKanban = kanban_tareas.idKanban
                LEFT JOIN usuarios_listado             ON usuarios_listado.idUsuario           = kanban_tareas_participantes.idUsuario
                LEFT JOIN kanban_estados               ON kanban_estados.idKanbanEstado        = kanban_tareas.idKanbanEstado
                LEFT JOIN core_estados_colores         ON core_estados_colores.idColor         = kanban_estados.idColor',
            'where'   => $whereInt,
            'params'  => $whereParams,
            'group'   => '',
            'having'  => '',
            'order'   => 'kanban_tareas.idKanbanEstado ASC, kanban_tareas.Fecha ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams   = ['query' => $query];
        $arrTareas = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($arrTareas['status']){

            //Se agrupan los menus
            $arrTareasNew = $this->CommonData->agruparPorClave ($arrTareas['data'], 'ID' );

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'TableTitle'      => 'Listado de Tareas',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_Codification'    => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrTareas'       => $arrTareasNew,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-UpdateList.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$arrTareas]);
            //Muestra los errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    //View
    public function View($f3, $params){
        /******************************************/
        // Se genera la query
        $query = [
            'data'    => '
                kanban_tareas.idKanban,
                kanban_tareas.idEstadoCierre,
                core_prioridades.Nombre AS PrioridadNombre,
                core_prioridades.Color AS PrioridadColor,
                kanban_tareas.Fecha,
                kanban_tareas.Titulo,
                kanban_tareas.Descripcion,
                core_estados_cierre.Nombre AS EstadoCierreNombre,
                core_estados_cierre.Color AS EstadoCierreColor,
                kanban_estados.Nombre AS KanbanEstado,
                core_estados_colores.Nombre AS KanbanColor',
            'table'   => 'kanban_tareas',
            'join'    => '
                LEFT JOIN core_prioridades        ON core_prioridades.idPrioridad         = kanban_tareas.idPrioridad
                LEFT JOIN kanban_estados          ON kanban_estados.idKanbanEstado        = kanban_tareas.idKanbanEstado
                LEFT JOIN core_estados_cierre     ON core_estados_cierre.idEstadoCierre   = kanban_tareas.idEstadoCierre
                LEFT JOIN core_estados_colores    ON core_estados_colores.idColor         = kanban_estados.idColor',
            'where'   => 'kanban_tareas.idKanban = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /******************************************/
        // Se genera la query
        $query = [
            'data'    => '
                kanban_tareas_tareas.idTareas,
                kanban_tareas_tareas.Tarea,
                core_estados_trabajos.Nombre AS EstadoNombre,
                core_estados_trabajos.Color AS EstadoColor,
                core_estados_trabajos.Icon AS EstadoIcon,
                kanban_trabajos.Nombre AS Trabajo',
            'table'   => 'kanban_tareas_tareas',
            'join'    => '
                LEFT JOIN core_estados_trabajos  ON core_estados_trabajos.idEstadoTrabajo = kanban_tareas_tareas.idEstadoTrabajo
                LEFT JOIN kanban_trabajos        ON kanban_trabajos.idTrabajo             = kanban_tareas_tareas.idTrabajo',
            'where'   => 'kanban_tareas_tareas.idKanban = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => 'kanban_tareas_tareas.Tarea ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams   = ['query' => $query];
        $arrTareas = $this->Base_GetList($xParams);

        /******************************************/
        // Se genera la query
        $query = [
            'data'    => '
                kanban_tareas_participantes.idParticipantes,
                usuarios_listado.Nombre AS UsuarioNombre,
                usuarios_listado.Direccion_img AS UsuarioImg',
            'table'   => 'kanban_tareas_participantes',
            'join'    => 'LEFT JOIN usuarios_listado ON usuarios_listado.idUsuario = kanban_tareas_participantes.idUsuario',
            'where'   => 'kanban_tareas_participantes.idKanban = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => 'usuarios_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams          = ['query' => $query];
        $arrParticipantes = $this->Base_GetList($xParams);

        /******************************************/
        // Se genera la query
        $query = [
            'data'    => '
                kanban_tareas_historial.Descripcion,
                kanban_tareas_historial.Fecha,
                kanban_tareas_historial.Hora,
                usuarios_listado.Nombre AS UsuarioNombre,
                usuarios_listado.Direccion_img AS UsuarioImg',
            'table'   => 'kanban_tareas_historial',
            'join'    => 'LEFT JOIN usuarios_listado ON usuarios_listado.idUsuario = kanban_tareas_historial.idUsuario',
            'where'   => 'kanban_tareas_historial.idKanban = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => 'usuarios_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams      = ['query' => $query];
        $arrHistorial = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrTareas['status'] && $arrParticipantes['status'] && $arrHistorial['status']){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_Codification'     => $this->Codification,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
                'arrTareas'        => $arrTareas['data'],
                'arrParticipantes' => $arrParticipantes['data'],
                'arrHistorial'     => $arrHistorial['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-View.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrTareas,$arrParticipantes,$arrHistorial]);
            //Muestra los errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    //View
    public function Print($f3, $params){
        /******************************************/
        // Se genera la query
        $query = [
            'data'    => '
                kanban_tareas.idKanban,
                kanban_tareas.idEstadoCierre,
                core_prioridades.Nombre AS PrioridadNombre,
                core_prioridades.Color AS PrioridadColor,
                kanban_tareas.Fecha,
                kanban_tareas.Titulo,
                kanban_tareas.Descripcion,
                core_estados_cierre.Nombre AS EstadoCierreNombre,
                core_estados_cierre.Color AS EstadoCierreColor,
                kanban_estados.Nombre AS KanbanEstado,
                core_estados_colores.Nombre AS KanbanColor',
            'table'   => 'kanban_tareas',
            'join'    => '
                LEFT JOIN core_prioridades        ON core_prioridades.idPrioridad          = kanban_tareas.idPrioridad
                LEFT JOIN kanban_estados          ON kanban_estados.idKanbanEstado         = kanban_tareas.idKanbanEstado
                LEFT JOIN core_estados_cierre     ON core_estados_cierre.idEstadoCierre    = kanban_tareas.idEstadoCierre
                LEFT JOIN core_estados_colores    ON core_estados_colores.idColor          = kanban_estados.idColor',
            'where'   => 'kanban_tareas.idKanban = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /******************************************/
        // Se genera la query
        $query = [
            'data'    => '
                kanban_tareas_tareas.idTareas,
                kanban_tareas_tareas.Tarea,
                core_estados_trabajos.Nombre AS EstadoNombre,
                core_estados_trabajos.Color AS EstadoColor,
                core_estados_trabajos.Icon AS EstadoIcon,
                kanban_trabajos.Nombre AS Trabajo',
            'table'   => 'kanban_tareas_tareas',
            'join'    => '
                LEFT JOIN core_estados_trabajos  ON core_estados_trabajos.idEstadoTrabajo = kanban_tareas_tareas.idEstadoTrabajo
                LEFT JOIN kanban_trabajos        ON kanban_trabajos.idTrabajo             = kanban_tareas_tareas.idTrabajo',
            'where'   => 'kanban_tareas_tareas.idKanban = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => 'kanban_tareas_tareas.Tarea ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams   = ['query' => $query];
        $arrTareas = $this->Base_GetList($xParams);

        /******************************************/
        // Se genera la query
        $query = [
            'data'    => '
                kanban_tareas_participantes.idParticipantes,
                usuarios_listado.Nombre AS UsuarioNombre,
                usuarios_listado.Direccion_img AS UsuarioImg',
            'table'   => 'kanban_tareas_participantes',
            'join'    => 'LEFT JOIN usuarios_listado ON usuarios_listado.idUsuario = kanban_tareas_participantes.idUsuario',
            'where'   => 'kanban_tareas_participantes.idKanban = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => 'usuarios_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams          = ['query' => $query];
        $arrParticipantes = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrTareas['status'] && $arrParticipantes['status']){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_Codification'     => $this->Codification,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
                'arrTareas'        => $arrTareas['data'],
                'arrParticipantes' => $arrParticipantes['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(3, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Print.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrTareas,$arrParticipantes]);
            //Muestra los errores
            $this->showError(2, $f3, $result);
        }
    }

}
