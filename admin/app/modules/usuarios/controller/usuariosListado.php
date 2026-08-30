<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class usuariosListado extends ControllerBase {

    /******************************************************************************/
    // Variables
    private $controllerName;
    private $FormInputs;
    private $DataDate;
    private $Codification;
    private $DataNumbers;
    private $WidgetsCommon;
    private $CommonData;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'usuariosListado';
		$this->FormInputs     = new UIFormInputs();
		$this->DataDate       = new FunctionsDataDate();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->DataNumbers    = new FunctionsDataNumbers();
		$this->WidgetsCommon  = new UIWidgetsCommon();
		$this->CommonData     = new FunctionsCommonData();
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
        $arrList = $this->getDataList('usuarios_listado.idTipoUsuario!=1');

        /******************************/
        // Se genera la query
        $query = [
            'data'    => 'idCiudad AS ID,Nombre',
            'table'   => 'core_ubicacion_ciudad',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams   = ['query' => $query];
        $arrCiudad = $this->Base_GetList($xParams);

        /******************************/
        // Se genera la query
        $query = [
            'data'    => 'idComuna AS ID1, idCiudad AS ID2, Nombre',
            'table'   => 'core_ubicacion_comunas',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams   = ['query' => $query];
        $arrComuna = $this->Base_GetList($xParams);

        /******************************/
        // Se genera la query
        $query = [
            'data'    => 'idTipoUsuario AS ID,Nombre',
            'table'   => 'core_tipos_usuario',
            'join'    => '',
            'where'   => 'idTipoUsuario != ?',
            'params'  => [1],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams        = ['query' => $query];
        $arrTipoUsuario = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($arrList['status'] && $arrCiudad['status'] && $arrComuna['status'] && $arrTipoUsuario['status']){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Listado de Usuarios',
                'PageDescription' => 'Listado de Usuarios.',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => 'Listado de Usuarios',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_Codification'    => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList['data'],
                'arrCiudad'       => $arrCiudad['data'],
                'arrComuna'       => $arrComuna['data'],
                'arrTipoUsuario'  => $arrTipoUsuario['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$arrList,$arrCiudad,$arrComuna,$arrTipoUsuario]);
            //Muestra los errores
            $this->showError(1, $f3, $result);
        }
    }

    /******************************************************************************/
    //List
    public function UpdateList($f3){
        /*******************************************************************/
        // Variables
        $WhereData_int     = 'idTipoUsuario';  // Datos búsqueda exacta
        $WhereData_string  = 'email,Nombre';   // Datos búsqueda relativa
        $WhereData_between = '';               // Datos búsqueda Between
        $whereInt          = '';               // Se crea cadena
        $whereParams       = [];               // Valores bindeados asociados a $whereInt
        /******************************************/
        // Se validan las fechas
        $RespDataBetween = $this->searchValidateDates($WhereData_between);
        if($RespDataBetween!=''){
            Response::error($RespDataBetween, 500);
        }
        // Agrego variable busqueda
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_int, 'usuarios_listado', 1);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_string, 'usuarios_listado', 2);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_between, 'usuarios_listado', 3);
        $whereInt = $r['where']; $whereParams = $r['params'];
        // Verifico si esta vacio
        $whereInt   .= ($whereInt ? ' AND ' : '') . 'usuarios_listado.idTipoUsuario != ?';
        $whereParams = array_merge($whereParams, [1]);

        /******************************/
        // Se genera la query
        $arrList = $this->getDataList($whereInt, $whereParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($arrList['status']){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'TableTitle'      => 'Listado de Usuarios',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_Codification'    => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-UpdateList.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$arrList]);
            //Muestra los errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    //View
    public function View($f3, $params){

        /******************************************/
        //Se instancia
        $arrUserData = $this->getUserData($f3);

        /******************************************/
        // Se genera la query
        $rowData = $this->getDataDetail($this->Codification->encryptDecrypt('decrypt', $params['id']));

        /*******************************************************************/
        // Se genera la query
        $query = [
            'data'    => '
                core_permisos_listado.Nombre AS Permiso,
                core_permisos_listado_level_limit.Nombre AS Nivel
            ',
            'table'   => 'usuarios_listado_permisos',
            'join'    => '
                LEFT JOIN core_permisos_listado              ON core_permisos_listado.idPermisos               = usuarios_listado_permisos.idPermisos
                LEFT JOIN core_permisos_listado_level_limit  ON core_permisos_listado_level_limit.idLevelLimit = usuarios_listado_permisos.idLevelLimit',
            'where'   => 'usuarios_listado_permisos.idUsuario = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => 'core_permisos_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams     = ['query' => $query];
        $arrPermisos = $this->Base_GetList($xParams);

        /*******************************************************************/
        // Se genera la query
        $query = [
            'data'    => 'FechaCreacion,Observacion',
            'table'   => 'usuarios_listado_observaciones',
            'join'    => '',
            'where'   => 'idUsuario = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => 'idObservaciones ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams          = ['query' => $query];
        $arrObservaciones = $this->Base_GetList($xParams);

        /*******************************************************************/
        // Se verifica si se tiene el permiso para visualizar el dato
        if($arrUserData["usuariosPermisosBodegas"]==2){
            // Se genera la query
            $query = [
                'data'    => 'bodegas_listado.Nombre',
                'table'   => 'bodegas_listado_permisos_usuarios',
                'join'    => 'LEFT JOIN bodegas_listado ON bodegas_listado.idBodegas = bodegas_listado_permisos_usuarios.idBodegas',
                'where'   => 'bodegas_listado_permisos_usuarios.idUsuario = ?',
                'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
                'group'   => '',
                'having'  => '',
                'order'   => 'bodegas_listado.Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            // Ejecuto la query
            $xParams            = ['query' => $query];
            $arrPermisosBodegas = $this->Base_GetList($xParams);
        //Si se permite junto con la creacion de tareas
        }else{
            $arrPermisosBodegas['status'] = true;
            $arrPermisosBodegas['data']   = [];
        }

        /*******************************************************************/
        // Se verifica si se tiene el permiso para visualizar el dato
        if($arrUserData["usuariosPermisosMaquinas"]==2){
            // Se genera la query
            $query = [
                'data'    => 'maquinas_listado.Nombre',
                'table'   => 'maquinas_listado_permisos_usuarios',
                'join'    => 'LEFT JOIN maquinas_listado ON maquinas_listado.idMaquina = maquinas_listado_permisos_usuarios.idMaquina',
                'where'   => 'maquinas_listado_permisos_usuarios.idUsuario = ?',
                'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
                'group'   => '',
                'having'  => '',
                'order'   => 'maquinas_listado.Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            // Ejecuto la query
            $xParams             = ['query' => $query];
            $arrPermisosMaquinas = $this->Base_GetList($xParams);
        //Si se permite junto con la creacion de tareas
        }else{
            $arrPermisosMaquinas['status'] = true;
            $arrPermisosMaquinas['data']   = [];
        }

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrPermisos['status'] && $arrObservaciones['status'] && $arrPermisosBodegas['status'] && $arrPermisosMaquinas['status']){
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                /*=========== Datos Consultados ===========*/
                'rowData'             => $rowData['data'],
                'arrPermisos'         => $arrPermisos['data'],
                'arrObservaciones'    => $arrObservaciones['data'],
                'arrPermisosBodegas'  => $arrPermisosBodegas['data'],
                'arrPermisosMaquinas' => $arrPermisosMaquinas['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-View.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrPermisos,$arrObservaciones,$arrPermisosBodegas,$arrPermisosMaquinas]);
            //Muestra los errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    //Resumen
    public function Resumen($f3, $params){

        /******************************************/
        //Se instancia
        $arrUserData = $this->getUserData($f3);

        /******************************************/
        // Se genera la query
        $rowData = $this->getDataDetail($this->Codification->encryptDecrypt('decrypt', $params['id']));

        /******************************/
        // Se genera la query
        $query = [
            'data'    => 'idCiudad AS ID,Nombre',
            'table'   => 'core_ubicacion_ciudad',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams   = ['query' => $query];
        $arrCiudad = $this->Base_GetList($xParams);

        /******************************/
        // Se genera la query
        $query = [
            'data'    => 'idComuna AS ID1, idCiudad AS ID2, Nombre',
            'table'   => 'core_ubicacion_comunas',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams   = ['query' => $query];
        $arrComuna = $this->Base_GetList($xParams);

        /******************************/
        // Se genera la query
        $query = [
            'data'    => 'idTipoUsuario AS ID,Nombre',
            'table'   => 'core_tipos_usuario',
            'join'    => '',
            'where'   => 'idTipoUsuario != ?',
            'params'  => [1],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams        = ['query' => $query];
        $arrTipoUsuario = $this->Base_GetList($xParams);

        /******************************/
        // Se genera la query
        $query = [
            'data'    => 'idEstado AS ID,Nombre',
            'table'   => 'core_estados',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams   = ['query' => $query];
        $arrEstado = $this->Base_GetList($xParams);

        /*******************************************************************/
        // Se genera la query
        $query = [
            'data'    => '
                core_permisos_listado.idPermisos,
                core_permisos_listado.idPermisos AS ID,
                core_permisos_listado.Nombre,
                core_permisos_listado.Descripcion,
                core_permisos_listado.idLevelLimit,
                core_permisos_categorias.Nombre AS PermisosCat,
                (SELECT idLevelLimit FROM usuarios_listado_permisos WHERE idPermisos = ID AND idUsuario = '.$this->Codification->encryptDecrypt('decrypt', $params['id']).' LIMIT 1) AS level',
            'table'   => 'core_permisos_listado',
            'join'    => 'LEFT JOIN core_permisos_categorias ON core_permisos_categorias.idPermisosCat = core_permisos_listado.idPermisosCat',
            'where'   => 'core_permisos_listado.idEstado = ?',
            'params'  => [1],
            'group'   => '',
            'having'  => '',
            'order'   => 'core_permisos_categorias.Nombre ASC, core_permisos_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams     = ['query' => $query];
        $arrPermisos = $this->Base_GetList($xParams);

        /*******************************************************************/
        // Se verifica si se tiene el permiso para visualizar el dato
        if($arrUserData["usuariosPermisosBodegas"]==2){
            // Se genera la query
            $query = [
                'data'    => '
                    idBodegas,
                    idBodegas AS ID,
                    Nombre,
                    (SELECT COUNT(idPermisoUsuario) FROM bodegas_listado_permisos_usuarios WHERE idBodegas = ID AND idUsuario = '.$this->Codification->encryptDecrypt('decrypt', $params['id']).' LIMIT 1) AS cuentaPerms',
                'table'   => 'bodegas_listado',
                'join'    => '',
                'where'   => 'idEstado = ?',
                'params'  => [1],
                'group'   => '',
                'having'  => '',
                'order'   => 'Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            // Ejecuto la query
            $xParams            = ['query' => $query];
            $arrPermisosBodegas = $this->Base_GetList($xParams);
        //Si se permite junto con la creacion de tareas
        }else{
            $arrPermisosBodegas['status'] = true;
            $arrPermisosBodegas['data']   = [];
        }

        /*******************************************************************/
        // Se verifica si se tiene el permiso para visualizar el dato
        if($arrUserData["usuariosPermisosMaquinas"]==2){
            // Se genera la query
            $query = [
                'data'    => '
                    idMaquina,
                    idMaquina AS ID,
                    Nombre,
                    (SELECT COUNT(idPermisoUsuario) FROM maquinas_listado_permisos_usuarios WHERE idMaquina = ID AND idUsuario = '.$this->Codification->encryptDecrypt('decrypt', $params['id']).' LIMIT 1) AS cuentaPerms',
                'table'   => 'maquinas_listado',
                'join'    => '',
                'where'   => 'idEstado = ?',
                'params'  => [1],
                'group'   => '',
                'having'  => '',
                'order'   => 'Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            // Ejecuto la query
            $xParams             = ['query' => $query];
            $arrPermisosMaquinas = $this->Base_GetList($xParams);
        //Si se permite junto con la creacion de tareas
        }else{
            $arrPermisosMaquinas['status'] = true;
            $arrPermisosMaquinas['data']   = [];
        }

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrCiudad['status'] && $arrComuna['status'] && $arrTipoUsuario['status'] && $arrEstado['status'] && $arrPermisos['status'] && $arrPermisosBodegas['status'] && $arrPermisosMaquinas['status']){
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'        => 'Resumen Usuario',
                'PageDescription'  => 'Resumen Usuario.',
                'PageAuthor'       => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'     => ConfigAPP::SOFTWARE['SoftwareName'],
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'       => $this->FormInputs,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                'Fnc_Codification'     => $this->Codification,
                'Fnc_CommonData'       => $this->CommonData,
                /*=========== Datos Consultados ===========*/
                'rowData'               => $rowData['data'],
                'arrCiudad'             => $arrCiudad['data'],
                'arrComuna'             => $arrComuna['data'],
                'arrTipoUsuario'        => $arrTipoUsuario['data'],
                'arrEstado'             => $arrEstado['data'],
                'arrPermisos'           => $arrPermisos['data'],
                'arrPermisosBodegas'    => $arrPermisosBodegas['data'],
                'arrPermisosMaquinas'   => $arrPermisosMaquinas['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrCiudad,$arrComuna,$arrTipoUsuario,$arrEstado,$arrPermisos,$arrPermisosBodegas,$arrPermisosMaquinas]);
            //Muestra los errores
            $this->showError(1, $f3, $result);
        }
    }

    /******************************************************************************/
    //Resumen-Update
    public function ResumenUpdate($f3, $params){
        /******************************************/
        // Se genera la query
        $rowData = $this->getDataDetail($this->Codification->encryptDecrypt('decrypt', $params['id']));

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status']){
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Update.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$rowData]);
            //Muestra los errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    /*                            CONSULTAS INTERNAS                              */
    /******************************************************************************/
    /******************************************************************************/
    //Se obtiene la lista
    private function getDataList($filter, $params = []){
        // Se genera la query
        $query = [
            'data'    => '
                usuarios_listado.idUsuario,
                usuarios_listado.email,
                usuarios_listado.Nombre,
                usuarios_listado.Ultimo_acceso,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor',
            'table'   => 'usuarios_listado',
            'join'    => 'LEFT JOIN core_estados ON core_estados.idEstado = usuarios_listado.idEstado',
            'where'   => $filter,
            'params'  => $params,
            'group'   => '',
            'having'  => '',
            'order'   => 'usuarios_listado.email ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams = ['query' => $query];
        //Se retornan los datos
        return $this->Base_GetList($xParams);
    }

    /******************************************************************************/
    //Se obtienen los detalles
    private function getDataDetail($ID){
        // Se genera la query
        $query = [
            'data'    => '
                usuarios_listado.idUsuario,
                usuarios_listado.idTipoUsuario,
                usuarios_listado.idEstado,
                usuarios_listado.email,
                usuarios_listado.Nombre,
                usuarios_listado.Rut,
                usuarios_listado.fNacimiento,
                usuarios_listado.Fono,
                usuarios_listado.idCiudad,
                usuarios_listado.idComuna,
                usuarios_listado.Direccion,
                usuarios_listado.Direccion_img,
                usuarios_listado.Social_X,
                usuarios_listado.Social_Facebook,
                usuarios_listado.Social_Instagram,
                usuarios_listado.Social_Linkedin,
                usuarios_listado.idMenuPosicion,

                core_tipos_usuario.Nombre AS TipoUsuario,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor,
                core_ubicacion_ciudad.Nombre AS Ciudad,
                core_ubicacion_comunas.Nombre AS Comuna,
                core_posicion_menu.Nombre AS MenuPosicion',
            'table'   => 'usuarios_listado',
            'join'    => '
                LEFT JOIN core_tipos_usuario      ON core_tipos_usuario.idTipoUsuario   = usuarios_listado.idTipoUsuario
                LEFT JOIN core_estados            ON core_estados.idEstado              = usuarios_listado.idEstado
                LEFT JOIN core_ubicacion_ciudad   ON core_ubicacion_ciudad.idCiudad     = usuarios_listado.idCiudad
                LEFT JOIN core_ubicacion_comunas  ON core_ubicacion_comunas.idComuna    = usuarios_listado.idComuna
                LEFT JOIN core_posicion_menu      ON core_posicion_menu.idMenuPosicion  = usuarios_listado.idMenuPosicion',
            'where'   => 'usuarios_listado.idUsuario = ?',
            'params'  => [$ID],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Ejecuto la query
        $xParams = ['query' => $query];
        //Se retornan los datos
        return $this->Base_GetByID($xParams);
    }

    /******************************************************************************/
    /*                                  DATOS                                     */
    /******************************************************************************/
    /******************************************************************************/
    //Crear
    public function Insert(){

        /******************************/
        //Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /******************************/
        // Se genera la query
        $query = [
            'data'      => 'password,idTipoUsuario,idEstado,email,Nombre,Rut,fNacimiento,Fono,idCiudad,idComuna,Direccion,Ultimo_acceso,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin,IP_Client,Agent_Transp,idMenuPosicion',
            'required'  => 'password,idTipoUsuario,idEstado,email,Nombre,idMenuPosicion',
            'unique'    => 'email',
            'encode'    => 'password',
            'table'     => 'usuarios_listado',
            'Post'      => $_POST
        ];
        // Ejecuto la query
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        $Response = $this->Base_insert($xParams);

        /******************************/
        // Se asume que $Response contendrá un array de errores/datos, un ID numérico o algún otro valor.
        if ($Response['status']){
            // Si es un ID numérico, encripta y envía con código 200 (OK)
            $Data = $this->Codification->encryptDecrypt('encrypt', $Response['data']);
            Response::success($Data);
        } else {
            // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
            // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
            Response::error('Error al operar con la Base de Datos', 500, $Response['error']);
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
            // Se genera la query
            $query = [
                'data'      => 'idUsuario,password,idTipoUsuario,idEstado,email,Nombre,Rut,fNacimiento,Fono,idCiudad,idComuna,Direccion,Ultimo_acceso,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin,IP_Client,Agent_Transp,idMenuPosicion',
                'required'  => 'idTipoUsuario,idEstado,email,Nombre,idMenuPosicion',
                'unique'    => 'email',
                'encode'    => 'password',
                'table'     => 'usuarios_listado',
                'where'     => 'idUsuario',
                'Post'      => $_POST,
                'files'     => [
                    [
                        'Identificador' => 'Direccion_img',
                        'SubCarpeta'    => '',
                        'NombreArchivo' => '',
                        'SufijoArchivo' => 'Perfil_',
                        'ValidarTipo'   => 'image',
                        'ValidarPeso'   => 10,
                        'Base64'        => true
                    ],
                ]
            ];
            // Ejecuto la query
            $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
            $Response = $this->Base_update($xParams);

            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response['status']){
                // Devuelvo $Response con código 200 (OK)
                Response::success($Response['data']);
            } else {
                // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
                // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
                Response::error('Error al operar con la Base de Datos', 500, $Response['error']);
            }
        }else {
            // Request Method no esperado
            Response::error('Error en el Request Method', 500);
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
            // Se genera la query
            $query = [
                'files'       => 'Direccion_img',
                'table'       => 'usuarios_listado',
                'where'       => 'idUsuario',
                'SubCarpeta'  => '',
                'Post'        => $dataDelete
            ];
            // Ejecuto la query
            $xParams  = ['query' => $query];
            $Response = $this->Base_delete($xParams);
            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response['status']){
                /************************************************/
                //Listado de las tablas a eliminar los datos relacionados
                $arrTableDel  = array();
                $arrTableDel[] = ['files' => '', 'table' => 'usuarios_listado_observaciones'];

                /************************************************/
                // Verifico si existe
                if($arrTableDel){
                    //recorro
                    foreach ($arrTableDel as $tblDel) {
                        // Se genera la query
                        $query = ['files' => $tblDel['files'], 'table' => $tblDel['table'], 'where' => 'idUsuario', 'SubCarpeta' => '', 'Post' => $dataDelete];
                        // Ejecuto la query
                        $xParams = ['query' => $query];
                        $this->Base_delete($xParams);
                    }
                }

                /******************************/
                // Devuelvo $Response con código 200 (OK)
                Response::success($Response['data']);
            } else {
                // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
                // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
                Response::error('Error al operar con la Base de Datos', 500, $Response['error']);
            }
        }else {
            // Request Method no esperado
            Response::error('Error en el Request Method', 500);
        }
    }

    /******************************************************************************/
    //Permite eliminar archivos
    public function delFiles(){
        //Verificacion metodo PUT
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            //Se parsean los datos
            parse_str(file_get_contents("php://input"),$dataPut);
            /******************************/
            // Se genera la query
            $query = [
                'files'       => 'Direccion_img',
                'table'       => 'usuarios_listado',
                'where'       => 'idUsuario',
                'SubCarpeta'  => '',
                'Post'        => $dataPut
            ];
            // Ejecuto la query
            $xParams  = ['query' => $query];
            $Response = $this->Base_delFiles($xParams);
            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response['status']){
                // Devuelvo $Response con código 200 (OK)
                Response::success($Response['data']);
            } else {
                // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
                // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
                Response::error('Error al operar con la Base de Datos', 500, $Response['error']);
            }
        }else {
            // Request Method no esperado
            Response::error('Error en el Request Method', 500);
        }
    }

    /******************************************************************************/
    /*                             Métodos privados                               */
    /******************************************************************************/
    /******************************************************************************/
    //Se validan los datos
    private function dataCheck($POST){
        // Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => 'email',
            'ValidarNumero'             => 'idTipoUsuario,idEstado,Fono,idCiudad,idComuna,idMenuPosicion',
            'ValidarEntero'             => 'idTipoUsuario,idEstado,idCiudad,idComuna,idMenuPosicion',
            'ValidarRut'                => 'Rut',
            'ValidarPatente'            => '',
            'ValidarFecha'              => 'fNacimiento,Ultimo_acceso',
            'ValidarHora'               => '',
            'ValidarURL'                => 'Social_X,Social_Facebook,Social_Instagram,Social_Linkedin',
            'ValidarLargoMinimo'        => 'email,Nombre,Direccion',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'email,Nombre,Direccion',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Nombre,Direccion',
            'ValidarEspaciosVacios'     => 'email,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin',
            'ValidarMayusculas'         => 'email',
            'ValidarCoincidencias'      => '',
            'ValidarDominioEmail'       => 'email',
            'ValidarPasswordSegura'     => '',
            'ValidarFechaRango'         => 'fNacimiento',
            'ValidarEdadMinima'         => '',
            'ValidarJSON'               => '',
            'ValidarUUID'               => '',
            'ValidarIP'                 => 'IP_Client',
            'ValidarSoloAlfanumerico'   => '',
            'ValidarSoloLetras'         => '',
            'Post'                      => $POST,
        ];
        //Devuelvo
        return $DataChecking;
    }

}
