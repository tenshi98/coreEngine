<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class informeTareas extends ControllerBase {

    /******************************************************************************/
    //Variables
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
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
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
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

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
            'UserData'      => $UserData,
            'UserAccess'    => $arrLevel[$this->controllerName],
            /*===========   Funcionalidad   ===========*/
            'Fnc_FormInputs'   => $this->FormInputs,
            'Fnc_Codification' => $this->Codification,
            /*=========== Datos Consultados ===========*/
            'arrPrioridad'     => $arrPrioridad,
            'arrEstadoCierre'  => $arrEstadoCierre,
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista($UserData['TypeSession'], 1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
    }

    /******************************************************************************/
    //List
    public function UpdateList($f3){
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
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-UpdateList.php');
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
                LEFT JOIN core_prioridades        ON core_prioridades.idPrioridad         = kanban_tareas.idPrioridad
                LEFT JOIN kanban_estados          ON kanban_estados.idKanbanEstado        = kanban_tareas.idKanbanEstado
                LEFT JOIN core_estados_cierre     ON core_estados_cierre.idEstadoCierre   = kanban_tareas.idEstadoCierre
                LEFT JOIN core_estados_colores    ON core_estados_colores.idColor         = kanban_estados.idColor',
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
                LEFT JOIN core_estados_trabajos  ON core_estados_trabajos.idEstadoTrabajo = kanban_tareas_tareas.idEstadoTrabajo
                LEFT JOIN kanban_trabajos        ON kanban_trabajos.idTrabajo             = kanban_tareas_tareas.idTrabajo',
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
                LEFT JOIN core_estados_trabajos  ON core_estados_trabajos.idEstadoTrabajo = kanban_tareas_tareas.idEstadoTrabajo
                LEFT JOIN kanban_trabajos        ON kanban_trabajos.idTrabajo             = kanban_tareas_tareas.idTrabajo',
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

}