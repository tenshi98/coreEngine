<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class kanbanTareas extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $DataDate;
    private $Codification;
    private $WidgetsCommon;
    private $CommonData;
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
    /*******************************************************************/
    // Listar
    /*******************************************************************/
    public function listAll($f3){

        /************************************/
        // Se instancia
        $arrUserData = $this->getUserData($f3);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                kanban_estados.idKanbanEstado,
                kanban_estados.Nombre,
                core_estados_colores.Nombre AS Color',
            'table'   => 'kanban_estados',
            'join'    => 'LEFT JOIN core_estados_colores   ON core_estados_colores.idColor = kanban_estados.idColor',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'kanban_estados.idPrioridad ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrList = $this->Base_GetList($xParams);

        /************************************/
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
            'where'   => 'kanban_tareas.idEstadoCierre = ?',
            'params'  => [1],
            'group'   => '',
            'having'  => '',
            'order'   => 'kanban_tareas.idKanbanEstado ASC, kanban_tareas.Fecha ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams   = ['query' => $query];
        // Ejecuto la query
        $arrTareas = $this->Base_GetList($xParams);

        /************************************/
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
        // Preparo los datos
        $xParams      = ['query' => $query];
        // Ejecuto la query
        $arrPrioridad = $this->Base_GetList($xParams);

        /************************************/
        // Se verifica si se tiene el permiso para visualizar el dato
        if($arrUserData["KanbanTareasAdminTabIndepend"]==2){
            $arrColores['status']   = true;
            $arrColores['data']     = [];
            $arrCierre['status']    = true;
            $arrCierre['data']      = [];
        // Si se permite junto con la creacion de tareas
        }else{
            /************************************/
            // Se genera la query
            $query = [
                'data'    => 'idColor AS ID,Nombre',
                'table'   => 'core_estados_colores',
                'join'    => '',
                'where'   => '',
            'params'  => [],
                'group'   => '',
                'having'  => '',
                'order'   => 'Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            // Preparo los datos
            $xParams    = ['query' => $query];
            // Ejecuto la query
            $arrColores = $this->Base_GetList($xParams);

            /************************************/
            // Se genera la query
            $query = [
                'data'    => 'idOpciones AS ID,Nombre',
                'table'   => 'core_opciones',
                'join'    => '',
                'where'   => '',
                'params'  => [],
                'group'   => '',
                'having'  => '',
                'order'   => 'Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            // Preparo los datos
            $xParams   = ['query' => $query];
            // Ejecuto la query
            $arrCierre = $this->Base_GetList($xParams);
        }

        /************************************/
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
        // Preparo los datos
        $xParams         = ['query' => $query];
        // Ejecuto la query
        $arrEstadoCierre = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idUsuario AS ID,Nombre',
            'table'   => 'usuarios_listado',
            'join'    => '',
            'where'   => 'idTipoUsuario != ? AND idEstado = ?',
            'params'  => [1, 1],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams     = ['query' => $query];
        // Ejecuto la query
        $arrUsuarios = $this->Base_GetList($xParams);

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
            $arrTrabajos['status']   = true;
            $arrTrabajos['data']     = [];
        }

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($arrList['status'] && $arrTareas['status'] && $arrColores['status'] && $arrPrioridad['status'] && $arrCierre['status'] && $arrEstadoCierre['status'] && $arrUsuarios['status'] && $arrTrabajos['status']){

            /************************************/
            //Se agrupan los menus
            $arrTareasNew = $this->CommonData->agruparPorClave ($arrTareas['data'], 'ID' );

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Listado de Tareas',
                'PageDescription' => 'Listado de Tareas pendientes de finalizacion.',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => 'Listado de Tareas',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'   => $this->FormInputs,
                'Fnc_DataDate'     => $this->DataDate,
                'Fnc_Codification' => $this->Codification,
                'Fnc_ServerServer' => $this->ServerServer,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList['data'],
                'arrTareas'       => $arrTareasNew,
                'arrColores'      => $arrColores['data'],
                'arrPrioridad'    => $arrPrioridad['data'],
                'arrCierre'       => $arrCierre['data'],
                'arrEstadoCierre' => $arrEstadoCierre['data'],
                'arrUsuarios'     => $arrUsuarios['data'],
                'arrTrabajos'     => $arrTrabajos['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrList,$arrTareas,$arrColores,$arrPrioridad,$arrCierre,$arrEstadoCierre,$arrUsuarios,$arrTrabajos]);
            // Despliegue de errores
            $this->showError(1, $f3, $result);
        }
    }

    /*******************************************************************/
    // Actualizar Listar
    /*******************************************************************/
    public function UpdateList($f3){

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                kanban_estados.idKanbanEstado,
                kanban_estados.Nombre,
                core_estados_colores.Nombre AS Color',
            'table'   => 'kanban_estados',
            'join'    => 'LEFT JOIN core_estados_colores   ON core_estados_colores.idColor = kanban_estados.idColor',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'kanban_estados.idPrioridad ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrList = $this->Base_GetList($xParams);

        /************************************/
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
            'where'   => 'kanban_tareas.idEstadoCierre = ?',
            'params'  => [1],
            'group'   => '',
            'having'  => '',
            'order'   => 'kanban_tareas.idKanbanEstado ASC, kanban_tareas.Fecha ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams   = ['query' => $query];
        // Ejecuto la query
        $arrTareas = $this->Base_GetList($xParams);

        /************************************/
        //Se pide para el formulario de busqueda de la segunda pestaña
        /************************************/
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
        // Preparo los datos
        $xParams      = ['query' => $query];
        // Ejecuto la query
        $arrPrioridad = $this->Base_GetList($xParams);

        /************************************/
        //Se pide para el formulario de busqueda de la segunda pestaña
        /************************************/
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
        // Preparo los datos
        $xParams         = ['query' => $query];
        // Ejecuto la query
        $arrEstadoCierre = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($arrList['status'] && $arrTareas['status'] && $arrPrioridad['status'] && $arrEstadoCierre['status']){

            //Se agrupan los menus
            $arrTareasNew = $this->CommonData->agruparPorClave ($arrTareas['data'], 'ID' );

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'TableTitle'      => 'Listado de Tareas',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_Codification'    => $this->Codification,
                'Fnc_ServerServer'    => $this->ServerServer,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList['data'],
                'arrTareas'       => $arrTareasNew,
                'arrPrioridad'    => $arrPrioridad['data'],
                'arrEstadoCierre' => $arrEstadoCierre['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-UpdateList.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrList,$arrTareas,$arrPrioridad,$arrEstadoCierre]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Listar
    /*******************************************************************/
    public function UpdateTableList($f3){

        /************************************/
        // Variables
        $WhereData_int     = 'idPrioridad,Fecha,idEstadoCierre';  // Datos búsqueda exacta
        $WhereData_string  = 'Titulo';                            // Datos búsqueda relativa
        $WhereData_between = '';                                  // Datos búsqueda Between
        $whereInt          = '';                                  // Se crea cadena
        $whereParams       = [];                                  // Valores bindeados asociados a $whereInt
        /************************************/
        // Se validan las fechas
        $RespDataBetween = $this->searchValidateDates($WhereData_between);
        if($RespDataBetween!=''){
            Response::error($RespDataBetween, 500);
        }
        /************************************/
        // Agrego variable busqueda
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_int, 'kanban_tareas', 1);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_string, 'kanban_tareas', 2);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_between, 'kanban_tareas', 3);
        $whereInt = $r['where']; $whereParams = $r['params'];

        /************************************/
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
        // Preparo los datos
        $xParams   = ['query' => $query];
        // Ejecuto la query
        $arrTareas = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrTareas['status'] === true) {

            //Se agrupan los menus
            $arrTareasNew = $this->CommonData->agruparPorClave ($arrTareas['data'], 'ID' );

            /************************************/
            // Datos enviados a la pagina
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

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-UpdateTableList.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrTareas]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // View
    /*******************************************************************/
    public function View($f3, $params){

        /************************************/
        // Se obtiene el ID
        $KanbanID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($KanbanID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
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
                LEFT JOIN core_prioridades         ON core_prioridades.idPrioridad           = kanban_tareas.idPrioridad
                LEFT JOIN kanban_estados           ON kanban_estados.idKanbanEstado          = kanban_tareas.idKanbanEstado
                LEFT JOIN core_estados_cierre      ON core_estados_cierre.idEstadoCierre     = kanban_tareas.idEstadoCierre
                LEFT JOIN core_estados_colores     ON core_estados_colores.idColor           = kanban_estados.idColor',
            'where'   => 'kanban_tareas.idKanban = ?',
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
                LEFT JOIN core_estados_trabajos ON core_estados_trabajos.idEstadoTrabajo = kanban_tareas_tareas.idEstadoTrabajo
                LEFT JOIN kanban_trabajos       ON kanban_trabajos.idTrabajo             = kanban_tareas_tareas.idTrabajo',
            'where'   => 'kanban_tareas_tareas.idKanban = ?',
            'params'  => [$KanbanID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'kanban_tareas_tareas.Tarea ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams   = ['query' => $query];
        // Ejecuto la query
        $arrTareas = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                kanban_tareas_participantes.idParticipantes,
                usuarios_listado.Nombre AS UsuarioNombre,
                usuarios_listado.Direccion_img AS UsuarioImg',
            'table'   => 'kanban_tareas_participantes',
            'join'    => 'LEFT JOIN usuarios_listado ON usuarios_listado.idUsuario = kanban_tareas_participantes.idUsuario',
            'where'   => 'kanban_tareas_participantes.idKanban = ?',
            'params'  => [$KanbanID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'usuarios_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams          = ['query' => $query];
        // Ejecuto la query
        $arrParticipantes = $this->Base_GetList($xParams);

        /************************************/
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
            'params'  => [$KanbanID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'usuarios_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams      = ['query' => $query];
        // Ejecuto la query
        $arrHistorial = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrTareas['status'] && $arrParticipantes['status'] && $arrHistorial['status']){

            /************************************/
            // Datos enviados a la pagina
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

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-View.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrTareas,$arrParticipantes,$arrHistorial]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Imprimir
    /*******************************************************************/
    public function Print($f3, $params){

        /************************************/
        // Se obtiene el ID
        $KanbanID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($KanbanID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
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
                LEFT JOIN core_estados_trabajos ON core_estados_trabajos.idEstadoTrabajo = kanban_tareas_tareas.idEstadoTrabajo
                LEFT JOIN kanban_trabajos       ON kanban_trabajos.idTrabajo             = kanban_tareas_tareas.idTrabajo',
            'where'   => 'kanban_tareas_tareas.idKanban = ?',
            'params'  => [$KanbanID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'kanban_tareas_tareas.Tarea ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams   = ['query' => $query];
        // Ejecuto la query
        $arrTareas = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                kanban_tareas_participantes.idParticipantes,
                usuarios_listado.Nombre AS UsuarioNombre,
                usuarios_listado.Direccion_img AS UsuarioImg',
            'table'   => 'kanban_tareas_participantes',
            'join'    => 'LEFT JOIN usuarios_listado ON usuarios_listado.idUsuario = kanban_tareas_participantes.idUsuario',
            'where'   => 'kanban_tareas_participantes.idKanban = ?',
            'params'  => [$KanbanID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'usuarios_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams          = ['query' => $query];
        // Ejecuto la query
        $arrParticipantes = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrTareas['status'] && $arrParticipantes['status']){

            /************************************/
            // Datos enviados a la pagina
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

            /************************************/
            // Se instancia la vista
            $this->showVista(3, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Print.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrTareas,$arrParticipantes]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Editar
    /*******************************************************************/
    public function GetID($f3, $params){

        /************************************/
        // Se obtiene el ID
        $KanbanID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($KanbanID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
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
            'where'   => 'kanban_tareas.idKanban = ?',
            'params'  => [$KanbanID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        //Verifico si permite el cierre
        if($rowData['data']['idCierre']==1){
            /************************************/
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
            // Preparo los datos
            $xParams         = ['query' => $query];
            // Ejecuto la query
            $arrEstadoCierre = $this->Base_GetList($xParams);
        //Si no lo permite se envia array vacio
        }else{
            $arrEstadoCierre['status'] = true;
            $arrEstadoCierre['data']   = [];
        }

        /************************************/
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
        // Preparo los datos
        $xParams      = ['query' => $query];
        // Ejecuto la query
        $arrPrioridad = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrPrioridad['status'] && $arrEstadoCierre['status']){
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
                'rowData'          => $rowData['data'],
                'arrPrioridad'     => $arrPrioridad['data'],
                'arrEstadoCierre'  => $arrEstadoCierre['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-formEdit.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrPrioridad,$arrEstadoCierre]);
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
        $ndata_2 = isset($_POST['idParticipante']) ? count($_POST['idParticipante']) : 0;

        /************************************/
        // Generacion de errores
        if($ndata_2==0) {
            Response::error('No hay Participantes en la tarea', 500);
        }else{

            /************************************/
            // Se inicia la transacción
            $this->Base_transactionBegin();

            /************************************/
            // Se genera la query
            $query = [
                'data'      => 'idKanbanEstado,idEstadoCierre,idPrioridad,idUsuario,Fecha,Titulo,Descripcion,FechaCreacion',
                'required'  => 'idKanbanEstado,idEstadoCierre,idPrioridad,idUsuario,Fecha,Titulo,Descripcion,FechaCreacion',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'kanban_tareas',
                'Post'      => $_POST
            ];
            /************************************/
            // Se genera el chequeo
            $dataCheck_1 = $this->dataCheck_1($_POST);
            // Preparo los datos
            $xParams  = ['DataCheck' => $dataCheck_1, 'query' => $query];
            // Ejecuto la query
            $Response = $this->Base_insert($xParams);

            /************************************/
            // Si falla la ejecucion, se revierte de inmediato
            if ($Response['status'] === false) {
                $this->Base_transactionRollback();
                Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
            }

            /************************************/
            // Recorro las tareas ingresadas
            if(isset($ndata_1)&&$ndata_1!=0){
                // Se acumulan las filas a insertar para evitar un INSERT por cada tarea (N+1)
                $rowsTareas = [];
                // Recorro los datos
                for($j1 = 0; $j1 < $ndata_1; $j1++){
                    /************************************/
                    // Se agrega respuesta
                    $rowsTareas[] = [
                        'idKanban'         => $Response['data'],                //idKanban
                        'Tarea'            => $_POST['Tarea'][$j1],             //Tarea
                        'idEstadoTrabajo'  => 1,                                //Estado abierto
                        'idTrabajo'        => $_POST['idTrabajo'][$j1] ?? '',   //idTrabajo si existe
                    ];
                }

                /************************************/
                // Si hay datos marcados, se insertan todos en una sola sentencia
                if ($rowsTareas){
                    /************************************/
                    // Se genera el chequeo
                    $DataCheck = $this->dataCheck_2('');
                    /************************************/
                    // Se genera la query
                    $query = [
                        'data'      => 'idKanban,Tarea,idEstadoTrabajo,idTrabajo',
                        'required'  => 'idKanban,Tarea,idEstadoTrabajo',
                        'table'     => 'kanban_tareas_tareas',
                        'rows'      => $rowsTareas
                    ];
                    // Preparo los datos
                    $xParams    = ['DataCheck' => $DataCheck, 'query' => $query];
                    // Ejecuto la query
                    $respTareas = $this->Base_insertMultiple($xParams);
                    /************************************/
                    // Si falla la ejecucion, se revierte de inmediato
                    if ($respTareas['status'] === false) {
                        $this->Base_transactionRollback();
                        Response::error('Error al operar con la Base de Datos', 500, $respTareas['error']);
                    }
                }
            }
            /************************************/
            // Recorro las tareas ingresadas
            if(isset($ndata_2)&&$ndata_2!=0){
                // Se acumulan las filas a insertar para evitar un INSERT por cada participante (N+1)
                $rowsParticipantes = [];
                // Recorro los datos
                for($j2 = 0; $j2 < $ndata_2; $j2++){
                    /************************************/
                    // Se agrega respuesta
                    $rowsParticipantes[] = [
                        'idKanban'  => $Response['data'],             //idKanban
                        'idUsuario' => $_POST['idParticipante'][$j2], //Participantes
                    ];
                }

                /************************************/
                // Si hay datos marcados, se insertan todos en una sola sentencia
                if ($rowsParticipantes){
                    /************************************/
                    // Se genera el chequeo
                    $DataCheck = $this->dataCheck_2('');
                    /************************************/
                    // Se genera la query
                    $query = [
                        'data'      => 'idKanban,idUsuario',
                        'required'  => 'idKanban,idUsuario',
                        'table'     => 'kanban_tareas_participantes',
                        'rows'      => $rowsParticipantes
                    ];
                    // Preparo los datos
                    $xParams           = ['DataCheck' => $DataCheck, 'query' => $query];
                    // Ejecuto la query
                    $respParticipantes = $this->Base_insertMultiple($xParams);
                    /************************************/
                    // Si falla la ejecucion, se revierte de inmediato
                    if ($respParticipantes['status'] === false) {
                        $this->Base_transactionRollback();
                        Response::error('Error al operar con la Base de Datos', 500, $respParticipantes['error']);
                    }
                }
            }

            /************************************/
            //Se agrega historial
            $arrTareas = [
                'idKanban'    => $Response['data'],       //idKanban
                'idUsuario'   => $_POST['idUsuario'],     //Usuario creador
                'Descripcion' => 'Tarea Creada',          //Descripcion
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
            $dataCheck_3 = $this->dataCheck_3($arrTareas);
            // Preparo los datos
            $xParams = ['DataCheck' => $dataCheck_3, 'query' => $query];
            // Ejecuto la query
            $xInsert = $this->Base_insert($xParams);

            /************************************/
            // Si falla la ejecucion, se revierte de inmediato
            if ($xInsert['status'] === false) {
                $this->Base_transactionRollback();
                Response::error('Error al operar con la Base de Datos', 500, $xInsert['error']);
            }

            /************************************/
            // Se confirma la transacción
            $this->Base_transactionCommit();

            /************************************/
            // Si es un ID numérico, se envía con código 200 (OK)
            Response::success($Response['data']);

        }
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
            'data'      => 'idKanban,idKanbanEstado,idEstadoCierre,idPrioridad,idUsuario,Fecha,Titulo,Descripcion,FechaCreacion',
            'required'  => 'idKanban,idPrioridad,Fecha,Titulo,Descripcion',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'kanban_tareas',
            'where'     => 'idKanban',
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
        // Preparo los datos
        $xParams      = ['query' => $query];
        // Ejecuto la query
        $arrPrioridad = $this->Base_GetList($xParams);

        /************************************/
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
        // Preparo los datos
        $xParams         = ['query' => $query];
        // Ejecuto la query
        $arrEstadoCierre = $this->Base_GetList($xParams);

        /************************************/
        // Variables
        $arrPrioridadNew = [];
        $arrEstadoNew    = [];
        //Se guardan los datos
        foreach ($arrPrioridad['data'] as $task){       $arrPrioridadNew[$task['ID']] = $task['Nombre'];}
        foreach ($arrEstadoCierre['data'] as $task){    $arrEstadoNew[$task['ID']]    = $task['Nombre'];}

        /************************************/
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
                'idKanban'    => $Response['data'],                                    //idKanban
                'idUsuario'   => $_POST['idUsuario'],                                  //Usuario creador
                'Descripcion' => 'Se cambian datos basicos de la tarea:'.$comparacion, //Descripcion
                'Fecha'       => $_POST['Fecha_Actual'],                               //Fecha actual
                'Hora'        => $_POST['Hora_Actual'],                                //Hora actual
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
            $dataCheck_3 = $this->dataCheck_3($arrTareas);
            // Preparo los datos
            $xParams = ['DataCheck' => $dataCheck_3, 'query' => $query, 'novalidate' => true];
            $this->Base_insert($xParams);

        }

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data']);

    }

    /*******************************************************************/
    // Editar por put (solo modificar datos)
    // Editar por post (modificar y subir archivos)
    /*******************************************************************/
    public function ChangeStatus(){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        //Se decodifica
        $KanbanID       = $this->Codification->encryptDecrypt('decrypt', $_POST['idKanban']);
        $KanbanEstadoID = $this->Codification->encryptDecrypt('decrypt', $_POST['idKanbanEstado']);
        // Se verifica
        if (!$this->isValidDecrypted($KanbanID, 'id')) {
            Response::error('Registro inválido', 400);
        }
        if (!$this->isValidDecrypted($KanbanEstadoID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        $idKanban       = $KanbanID['data'];
        $idKanbanEstado = $KanbanEstadoID['data'];

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idKanbanEstado',
            'table'   => 'kanban_tareas',
            'join'    => '',
            'where'   => 'idKanban = ?',
            'params'  => [$idKanban],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /************************************/
        //Verifico si son diferente
        if($rowData['status'] && $rowData['data']['idKanbanEstado']!=$idKanbanEstado){
            /************************************/
            // Se agrega respuesta
            $arrTareas = [
                'idKanban'       => $idKanban,
                'idKanbanEstado' => $idKanbanEstado,
            ];
            /************************************/
            // Se genera la query
            $query = [
                'data'      => 'idKanban,idKanbanEstado',
                'required'  => 'idKanbanEstado',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'kanban_tareas',
                'where'     => 'idKanban',
                'Post'      => $arrTareas
            ];
            /************************************/
            // Se genera el chequeo
            $dataCheck_1 = $this->dataCheck_1($arrTareas);
            // Preparo los datos
            $xParams  = ['DataCheck' => $dataCheck_1, 'query' => $query];
            // Ejecuto la query
            $Response = $this->Base_update($xParams);

            /************************************/
            // Si falla la ejecucion, se revierte de inmediato
            if ($Response['status'] === false) {
                Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
            }

            /************************************/
            //Se agrega historial
            $arrTareas = [
                'idKanban'    => $Response['data'],               //idKanban
                'idUsuario'   => $_POST['idUsuario'],             //Usuario creador
                'Descripcion' => 'Tarea Actualizada de tablero',  //Descripcion
                'Fecha'       => $_POST['Fecha_Actual'],          //Fecha actual
                'Hora'        => $_POST['Hora_Actual'],           //Hora actual
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
            $dataCheck_3 = $this->dataCheck_3($arrTareas);
            // Preparo los datos
            $xParams = ['DataCheck' => $dataCheck_3, 'query' => $query, 'novalidate' => true];
            $this->Base_insert($xParams);
            /************************************/
            // Devuelvo $Response con código 200 (OK)
            Response::success($Response['data']);

        }

    }

    /*******************************************************************/
    // Borrar dato y archivos
    /*******************************************************************/
    public function Delete(){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Obtener datos
        parse_str(file_get_contents("php://input"),$dataDelete);

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Se genera la query
        $query = [
            'files'       => '',
            'table'       => 'kanban_tareas',
            'where'       => 'idKanban',
            'SubCarpeta'  => '',
            'Post'        => $dataDelete
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $Response = $this->Base_delete($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            $this->Base_transactionRollback();
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
        }

        /************************************/
        // Listado de las tablas a eliminar los datos relacionados
        $arrTableDel  = array();
        $arrTableDel[] = ['files' => '', 'table' => 'kanban_tareas_historial'];
        $arrTableDel[] = ['files' => '', 'table' => 'kanban_tareas_participantes'];
        $arrTableDel[] = ['files' => '', 'table' => 'kanban_tareas_tareas'];

        /************************************/
        // Verifico si existe
        if (!empty($arrTableDel)) {
            // Recorro
            foreach ($arrTableDel as $tblDel) {
                /************************************/
                // Se genera la query
                $query = ['files' => $tblDel['files'], 'table' => $tblDel['table'], 'where' => 'idKanban', 'SubCarpeta' => '', 'Post' => $dataDelete];
                // Preparo los datos
                $xParams    = ['query' => $query];
                // Ejecuto la query
                $respDelRel = $this->Base_delete($xParams);
                /************************************/
                // Si falla la ejecucion, se revierte de inmediato
                if ($respDelRel['status'] === false) {
                    $this->Base_transactionRollback();
                    Response::error('Error al operar con la Base de Datos', 500, $respDelRel['error']);
                }
            }
        }

        /************************************/
        // Se confirma la transacción
        $this->Base_transactionCommit();

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
            'ValidarNumero'             => 'idKanban,idKanbanEstado,idEstadoCierre,idPrioridad,idUsuario',
            'ValidarEntero'             => 'idKanban,idKanbanEstado,idEstadoCierre,idPrioridad,idUsuario',
            'ValidarRut'                => '',
            'ValidarPatente'            => '',
            'ValidarFecha'              => 'Fecha,FechaCreacion',
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
            'ValidarNumero'             => 'idKanban,idEstadoTrabajo,idTrabajo,idUsuario',
            'ValidarEntero'             => 'idKanban,idEstadoTrabajo,idTrabajo,idUsuario',
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
    private function dataCheck_3($POST){
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
