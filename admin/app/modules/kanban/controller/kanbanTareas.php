<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class kanbanTareas extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;
    private $FormInputs;
    private $DataDate;
    private $Codification;
    private $WidgetsCommon;
    private $CommonData;
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
		$this->DataDate       = new FunctionsDataDate();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->WidgetsCommon  = new UIWidgetsCommon();
		$this->CommonData     = new FunctionsCommonData();
		$this->ServerServer   = new FunctionsServerServer();
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
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => '
                kanban_estados.idKanbanEstado,
                kanban_estados.Nombre,
                core_estados_colores.Nombre AS Color',
            'table'   => 'kanban_estados',
            'join'    => 'LEFT JOIN core_estados_colores   ON core_estados_colores.idColor = kanban_estados.idColor',
            'where'   => 'kanban_estados.idKanbanEstado!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'kanban_estados.idPrioridad ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $arrList = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
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
            'where'   => 'kanban_tareas.idEstadoCierre=1',
            'group'   => '',
            'having'  => '',
            'order'   => 'kanban_tareas.idKanbanEstado ASC, kanban_tareas.Fecha ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrTareas = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idPrioridad AS ID,Nombre',
            'table'   => 'core_prioridades',
            'join'    => '',
            'where'   => 'idPrioridad!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'idPrioridad ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams      = ['query' => $query];
        $arrPrioridad = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se verifica si se permite Administrar Tableros Independiente de las Tareas
        if($UserData["KanbanTareasAdminTabIndepend"]==2){
            $arrColores   = [];
            $arrCierre    = [];
        //Si se permite junto con la creacion de tareas
        }else{
            /*******************************************************************/
            //Se genera la query
            $query = [
                'data'    => 'idColor AS ID,Nombre',
                'table'   => 'core_estados_colores',
                'join'    => '',
                'where'   => 'idColor!=0',
                'group'   => '',
                'having'  => '',
                'order'   => 'Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            //Ejecuto la query
            $xParams    = ['query' => $query];
            $arrColores = $this->Base_GetList($xParams);

            /*******************************************************************/
            //Se genera la query
            $query = [
                'data'    => 'idOpciones AS ID,Nombre',
                'table'   => 'core_opciones',
                'join'    => '',
                'where'   => 'idOpciones!=0',
                'group'   => '',
                'having'  => '',
                'order'   => 'Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            //Ejecuto la query
            $xParams   = ['query' => $query];
            $arrCierre = $this->Base_GetList($xParams);
        }

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idEstadoCierre AS ID,Nombre',
            'table'   => 'core_estados_cierre',
            'join'    => '',
            'where'   => 'idEstadoCierre!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'idEstadoCierre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams         = ['query' => $query];
        $arrEstadoCierre = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idUsuario AS ID,Nombre',
            'table'   => 'usuarios_listado',
            'join'    => '',
            'where'   => 'idTipoUsuario!=1 AND idEstado=1',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams     = ['query' => $query];
        $arrUsuarios = $this->Base_GetList($xParams);

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
        if(is_array($arrList)){

            /******************************************/
            //Se agrupan los menus
            $arrTareasNew = $this->CommonData->agruparPorClave ($arrTareas, 'ID' );

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Listado de Tareas',
                'PageDescription' => 'Listado de Tareas pendientes de finalizacion.',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => 'Listado de Tareas',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'   => $this->FormInputs,
                'Fnc_DataDate'     => $this->DataDate,
                'Fnc_Codification' => $this->Codification,
                'Fnc_ServerServer' => $this->ServerServer,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList,
                'arrTareas'       => $arrTareasNew,
                'arrColores'      => $arrColores,
                'arrPrioridad'    => $arrPrioridad,
                'arrCierre'       => $arrCierre,
                'arrEstadoCierre' => $arrEstadoCierre,
                'arrUsuarios'     => $arrUsuarios,
                'arrTrabajos'     => $arrTrabajos,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 1, $f3);
        }
    }

    /******************************************************************************/
    //List
    public function UpdateList($f3){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************/
        //Se genera la query
        $query = [
            'data'    => '
                kanban_estados.idKanbanEstado,
                kanban_estados.Nombre,
                core_estados_colores.Nombre AS Color',
            'table'   => 'kanban_estados',
            'join'    => 'LEFT JOIN core_estados_colores   ON core_estados_colores.idColor = kanban_estados.idColor',
            'where'   => 'kanban_estados.idKanbanEstado!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'kanban_estados.idPrioridad ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $arrList = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
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
            'where'   => 'kanban_tareas.idEstadoCierre=1',
            'group'   => '',
            'having'  => '',
            'order'   => 'kanban_tareas.idKanbanEstado ASC, kanban_tareas.Fecha ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrTareas = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se pide para el formulario de busqueda de la segunda pestaña
        //Se genera la query
        $query = [
            'data'    => 'idPrioridad AS ID,Nombre',
            'table'   => 'core_prioridades',
            'join'    => '',
            'where'   => 'idPrioridad!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'idPrioridad ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams      = ['query' => $query];
        $arrPrioridad = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se pide para el formulario de busqueda de la segunda pestaña
        //Se genera la query
        $query = [
            'data'    => 'idEstadoCierre AS ID,Nombre',
            'table'   => 'core_estados_cierre',
            'join'    => '',
            'where'   => 'idEstadoCierre!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'idEstadoCierre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams         = ['query' => $query];
        $arrEstadoCierre = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($arrList)){

            //Se agrupan los menus
            $arrTareasNew = $this->CommonData->agruparPorClave ($arrTareas, 'ID' );

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'TableTitle'      => 'Listado de Tareas',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_Codification'    => $this->Codification,
                'Fnc_ServerServer'    => $this->ServerServer,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList,
                'arrTareas'       => $arrTareasNew,
                'arrPrioridad'    => $arrPrioridad,
                'arrEstadoCierre' => $arrEstadoCierre,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-UpdateList.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //List
    public function UpdateTableList($f3){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /*******************************************************************/
        //Variables
        $WhereData_int     = 'idPrioridad,Fecha,idEstadoCierre';  //Datos búsqueda exacta
        $WhereData_string  = 'Titulo';                            //Datos búsqueda relativa
        $WhereData_between = '';                                  //Datos búsqueda Between
        $whereInt          = '';                                  //se crea cadena
        /******************************************/
        //agrego variable busqueda
        $whereInt = $this->searchWhere($whereInt, $WhereData_int, 'kanban_tareas', 1);
        $whereInt = $this->searchWhere($whereInt, $WhereData_string, 'kanban_tareas', 2);
        $whereInt = $this->searchWhere($whereInt, $WhereData_between, 'kanban_tareas', 3);

        /*******************************************************************/
        //Se genera la query
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
            'group'   => '',
            'having'  => '',
            'order'   => 'kanban_tareas.idKanbanEstado ASC, kanban_tareas.Fecha ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrTareas = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($arrTareas)){

            //Se agrupan los menus
            $arrTareasNew = $this->CommonData->agruparPorClave ($arrTareas, 'ID' );

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'TableTitle'      => 'Listado de Tareas',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_Codification'    => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrTareas'       => $arrTareasNew,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-UpdateTableList.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //View
    public function View($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
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
                LEFT JOIN core_prioridades         ON core_prioridades.idPrioridad           = kanban_tareas.idPrioridad
                LEFT JOIN kanban_estados           ON kanban_estados.idKanbanEstado          = kanban_tareas.idKanbanEstado
                LEFT JOIN core_estados_cierre      ON core_estados_cierre.idEstadoCierre     = kanban_tareas.idEstadoCierre
                LEFT JOIN core_estados_colores     ON core_estados_colores.idColor           = kanban_estados.idColor',
            'where'   => 'kanban_tareas.idKanban = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /******************************************/
        //Se genera la query
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
                LEFT JOIN core_estados_trabajos ON core_estados_trabajos.idEstadoTrabajo = kanban_tareas_tareas.idEstadoTrabajo
                LEFT JOIN kanban_trabajos       ON kanban_trabajos.idTrabajo             = kanban_tareas_tareas.idTrabajo',
            'where'   => 'kanban_tareas_tareas.idKanban = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'kanban_tareas_tareas.Tarea ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrTareas = $this->Base_GetList($xParams);

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => '
                kanban_tareas_participantes.idParticipantes,
                usuarios_listado.Nombre AS UsuarioNombre,
                usuarios_listado.Direccion_img AS UsuarioImg',
            'table'   => 'kanban_tareas_participantes',
            'join'    => 'LEFT JOIN usuarios_listado ON usuarios_listado.idUsuario = kanban_tareas_participantes.idUsuario',
            'where'   => 'kanban_tareas_participantes.idKanban = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'usuarios_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams          = ['query' => $query];
        $arrParticipantes = $this->Base_GetList($xParams);

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => '
                kanban_tareas_historial.Descripcion,
                kanban_tareas_historial.Fecha,
                kanban_tareas_historial.Hora,
                usuarios_listado.Nombre AS UsuarioNombre,
                usuarios_listado.Direccion_img AS UsuarioImg',
            'table'   => 'kanban_tareas_historial',
            'join'    => 'LEFT JOIN usuarios_listado ON usuarios_listado.idUsuario = kanban_tareas_historial.idUsuario',
            'where'   => 'kanban_tareas_historial.idKanban = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'usuarios_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams      = ['query' => $query];
        $arrHistorial = $this->Base_GetList($xParams);

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
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_Codification'     => $this->Codification,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData,
                'arrTareas'        => $arrTareas,
                'arrParticipantes' => $arrParticipantes,
                'arrHistorial'     => $arrHistorial,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-View.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //View
    public function Print($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
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
            'where'   => 'kanban_tareas.idKanban = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /******************************************/
        //Se genera la query
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
                LEFT JOIN core_estados_trabajos ON core_estados_trabajos.idEstadoTrabajo = kanban_tareas_tareas.idEstadoTrabajo
                LEFT JOIN kanban_trabajos       ON kanban_trabajos.idTrabajo             = kanban_tareas_tareas.idTrabajo',
            'where'   => 'kanban_tareas_tareas.idKanban = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'kanban_tareas_tareas.Tarea ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrTareas = $this->Base_GetList($xParams);

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => '
                kanban_tareas_participantes.idParticipantes,
                usuarios_listado.Nombre AS UsuarioNombre,
                usuarios_listado.Direccion_img AS UsuarioImg',
            'table'   => 'kanban_tareas_participantes',
            'join'    => 'LEFT JOIN usuarios_listado ON usuarios_listado.idUsuario = kanban_tareas_participantes.idUsuario',
            'where'   => 'kanban_tareas_participantes.idKanban = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'usuarios_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams          = ['query' => $query];
        $arrParticipantes = $this->Base_GetList($xParams);

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
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_Codification'     => $this->Codification,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData,
                'arrTareas'        => $arrTareas,
                'arrParticipantes' => $arrParticipantes,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 3, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Print.php');
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
            'data'    => '
                kanban_tareas.idKanban,
                kanban_tareas.idKanbanEstado,
                kanban_tareas.idEstadoCierre,
                kanban_tareas.idPrioridad,
                kanban_tareas.idUsuario,
                kanban_tareas.Fecha,
                kanban_tareas.Titulo,
                kanban_tareas.Descripcion,
                kanban_estados.idCierre',
            'table'   => 'kanban_tareas',
            'join'    => 'LEFT JOIN kanban_estados  ON kanban_estados.idKanbanEstado  = kanban_tareas.idKanbanEstado',
            'where'   => 'kanban_tareas.idKanban = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        //Verifico si permite el cierre
        if($rowData['idCierre']==1){
            //Se genera la query
            $query = [
                'data'    => 'idEstadoCierre AS ID,Nombre',
                'table'   => 'core_estados_cierre',
                'join'    => '',
                'where'   => 'idEstadoCierre!=0',
                'group'   => '',
                'having'  => '',
                'order'   => 'idEstadoCierre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            //Ejecuto la query
            $xParams         = ['query' => $query];
            $arrEstadoCierre = $this->Base_GetList($xParams);
        //Si no lo permite se envia array vacio
        }else{
            $arrEstadoCierre =[];
        }

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idPrioridad AS ID,Nombre',
            'table'   => 'core_prioridades',
            'join'    => '',
            'where'   => 'idPrioridad!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'idPrioridad ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams      = ['query' => $query];
        $arrPrioridad = $this->Base_GetList($xParams);

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
                'rowData'          => $rowData,
                'arrPrioridad'     => $arrPrioridad,
                'arrEstadoCierre'  => $arrEstadoCierre,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-formEdit.php');
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
        $ndata_2 = isset($_POST['idParticipante']) ? count($_POST['idParticipante']) : 0;

        //generacion de errores
        if($ndata_2==0) {
            echo Response::sendData(500, 'No hay Participantes en la tarea');
        }else{
            /******************************/
            //Se genera el chequeo
            $DataCheck = $this->dataCheck($_POST);

            /******************************/
            //Se genera la query
            $query = [
                'data'      => 'idKanbanEstado,idEstadoCierre,idPrioridad,idUsuario,Fecha,Titulo,Descripcion,FechaCreacion',
                'required'  => 'idKanbanEstado,idEstadoCierre,idPrioridad,idUsuario,Fecha,Titulo,Descripcion,FechaCreacion',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'kanban_tareas',
                'Post'      => $_POST
            ];
            //Ejecuto la query
            //Ejecuto la query
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        $Response = $this->Base_insert($xParams);

            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un ID numérico o algún otro valor.
            if (is_numeric($Response)) {
                /******************************/
                //Recorro las tareas ingresadas
                if(isset($ndata_1)&&$ndata_1!=0){
                    for($j1 = 0; $j1 < $ndata_1; $j1++){
                        /******************************/
                        //Se agrega respuesta
                        $arrTareas = [
                            'idKanban'         => $Response,                        //idKanban
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
                        $xParams       = ['DataCheck' => '', 'query' => $query];
                        $ResponseTarea = $this->Base_insert($xParams);
                    }
                }
                /******************************/
                //Recorro las tareas ingresadas
                if(isset($ndata_2)&&$ndata_2!=0){
                    for($j2 = 0; $j2 < $ndata_2; $j2++){
                        /******************************/
                        //Se agrega respuesta
                        $arrParticipantes = [
                            'idKanban'  => $Response,                     //idKanban
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
                        $xParams      = ['DataCheck' => '', 'query' => $query, 'novalidate' => true];
                        $ResponsePart = $this->Base_insert($xParams);
                    }
                }

                /******************************/
                //Se agrega historial
                $arrTareas = [
                    'idKanban'    => $Response,               //idKanban
                    'idUsuario'   => $_POST['idUsuario'],     //Usuario creador
                    'Descripcion' => 'Tarea Creada',          //Descripcion
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
                $xParams      = ['DataCheck' => '', 'query' => $query];
                $ResponseHist = $this->Base_insert($xParams);

                /******************************/
                // Si es un ID numérico, se envía con código 200 (OK)
                echo Response::sendData(200, $Response);
            } else {
                // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
                // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
                echo Response::sendData(500, $Response);
            }

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
                'data'      => 'idKanbanEstado,idEstadoCierre,idPrioridad,idUsuario,Fecha,Titulo,Descripcion,FechaCreacion',
                'required'  => 'idPrioridad,Fecha,Titulo,Descripcion',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'kanban_tareas',
                'where'     => 'idKanban',
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
                    'data'    => 'idPrioridad AS ID,Nombre',
                    'table'   => 'core_prioridades',
                    'join'    => '',
                    'where'   => 'idPrioridad!=0',
                    'group'   => '',
                    'having'  => '',
                    'order'   => 'idPrioridad ASC',
                    'limit'   => ConfigAPP::APP["N_MaxItems"]
                ];
                //Ejecuto la query
                $xParams      = ['query' => $query];
                $arrPrioridad = $this->Base_GetList($xParams);

                /*******************************************************************/
                //Se genera la query
                $query = [
                    'data'    => 'idEstadoCierre AS ID,Nombre',
                    'table'   => 'core_estados_cierre',
                    'join'    => '',
                    'where'   => 'idEstadoCierre!=0',
                    'group'   => '',
                    'having'  => '',
                    'order'   => 'idEstadoCierre ASC',
                    'limit'   => ConfigAPP::APP["N_MaxItems"]
                ];
                //Ejecuto la query
                $xParams         = ['query' => $query];
                $arrEstadoCierre = $this->Base_GetList($xParams);

                /*******************************************************************/
                //Variables
                $arrPrioridadNew = [];
                $arrEstadoNew    = [];
                //Se guardan los datos
                foreach ($arrPrioridad AS $task){       $arrPrioridadNew[$task['ID']] = $task['Nombre'];}
                foreach ($arrEstadoCierre AS $task){    $arrEstadoNew[$task['ID']]    = $task['Nombre'];}

                /******************************/
                //Se hacen comparaciones
                $comparacion = '';
                $campos = [
                    'idPrioridad'    => ['label' => 'prioridad', 'array' => $arrPrioridadNew  ],
                    'Fecha'          => ['label' => 'fecha de termino'],
                    'Titulo'         => ['label' => 'titulo'],
                    'Descripcion'    => ['label' => 'descripcion'],
                    'idEstadoCierre' => ['label' => 'estado de cierre','array' => $arrEstadoNew]
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
                        'idKanban'    => $Response,                                            //idKanban
                        'idUsuario'   => $_POST['idUsuario'],                                  //Usuario creador
                        'Descripcion' => 'Se cambian datos basicos de la tarea:'.$comparacion, //Descripcion
                        'Fecha'       => $_POST['Fecha_Actual'],                               //Fecha actual
                        'Hora'        => $_POST['Hora_Actual'],                                //Hora actual
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
                    $xParams      = ['DataCheck' => '', 'query' => $query, 'novalidate' => true];
                    $ResponseHist = $this->Base_insert($xParams);

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
    //Editar por put (solo modificar datos)
    //Editar por post (modificar y subir archivos)
    public function ChangeStatus(){
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            /******************************/
            //Se decodifica
            $idKanban       = $this->Codification->encryptDecrypt('decrypt', $_POST['idKanban']);
            $idKanbanEstado = $this->Codification->encryptDecrypt('decrypt', $_POST['idKanbanEstado']);

            /******************************************/
            //Se genera la query
            $query = [
                'data'    => 'idKanbanEstado',
                'table'   => 'kanban_tareas',
                'join'    => '',
                'where'   => 'idKanban = "'.$idKanban.'"',
                'group'   => '',
                'having'  => '',
                'order'   => ''
            ];
            //Ejecuto la query
            $xParams = ['query' => $query];
            $rowData = $this->Base_GetByID($xParams);

            /******************************/
            //Verifico si son diferente
            if($rowData['idKanbanEstado']!=$idKanbanEstado){
                /******************************/
                //Se agrega respuesta
                $arrTareas = [
                    'idKanban'       => $idKanban,
                    'idKanbanEstado' => $idKanbanEstado,
                ];
                /******************************/
                //Se genera la query
                $query = [
                    'data'      => 'idKanban,idKanbanEstado',
                    'required'  => 'idKanbanEstado',
                    'unique'    => '',
                    'encode'    => '',
                    'table'     => 'kanban_tareas',
                    'where'     => 'idKanban',
                    'Post'      => $arrTareas
                ];
                //Ejecuto la query
                $xParams  = ['DataCheck' => '', 'query' => $query];
                $Response = $this->Base_update($xParams);

                /******************************/
                // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
                if ($Response===true) {
                    /******************************/
                    //Se agrega historial
                    $arrTareas = [
                        'idKanban'    => $Response,                       //idKanban
                        'idUsuario'   => $_POST['idUsuario'],             //Usuario creador
                        'Descripcion' => 'Tarea Actualizada de tablero',  //Descripcion
                        'Fecha'       => $_POST['Fecha_Actual'],          //Fecha actual
                        'Hora'        => $_POST['Hora_Actual'],           //Hora actual
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
                    $xParams      = ['DataCheck' => '', 'query' => $query, 'novalidate' => true];
                    $ResponseHist = $this->Base_insert($xParams);
                    /******************************/
                    // Devuelvo $Response con código 200 (OK)
                    echo Response::sendData(200, $Response);
                } else {
                    // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
                    // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
                    echo Response::sendData(500, $Response);
                }

            }

        }else {
            echo Response::sendData(500, "Error en el Request Method");
        }
    }

    /******************************************************************************/
    //Borrar dato y archivos
    public function Delete(){
        //Verificacion metodo PUT
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            //Se parsean los datos
            parse_str(file_get_contents("php://input"),$dataDelete);
            /******************************/
            //Se genera la query
            $query = [
                'files'       => '',
                'table'       => 'kanban_tareas',
                'where'       => 'idKanban',
                'SubCarpeta'  => '',
                'Post'        => $dataDelete
            ];
            //Ejecuto la query
            $xParams  = ['query' => $query];
            $Response = $this->Base_delete($xParams);

            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response===true) {
                /************************************************/
                //Listado de las tablas a eliminar los datos relacionados
                $arrTableDel  = array();
                $arrTableDel[] = ['files' => '', 'table' => 'kanban_tareas_historial'];
                $arrTableDel[] = ['files' => '', 'table' => 'kanban_tareas_participantes'];
                $arrTableDel[] = ['files' => '', 'table' => 'kanban_tareas_tareas'];

                /************************************************/
                //Verifico si existe
                if($arrTableDel){
                    //recorro
                    foreach ($arrTableDel as $tblDel) {
                        //Se genera la query
                        $query = ['files' => $tblDel['files'], 'table' => $tblDel['table'], 'where' => 'idKanban', 'SubCarpeta' => '', 'Post' => $dataDelete];
                        //Ejecuto la query
                        $xParams     = ['query' => $query];
                        $ResponseDel = $this->Base_delete($xParams);
                    }
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
            'ValidarFecha'              => 'Fecha',
            'ValidarHora'               => '',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => 'Titulo,Descripcion',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'Titulo',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Titulo,Descripcion',
            'ValidarEspaciosVacios'     => '',
            'ValidarMayusculas'         => '',
            'ValidarCoincidencias'      => '',
            'Post'                      => $POST,
        ];
        //Devuelvo
        return $DataChecking;
    }

}