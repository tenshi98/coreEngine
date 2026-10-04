<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class permisosListado extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $DBConn;
    private $QBuilder;
    private $FormInputs;
    private $Codification;
    private $CommonData;
    private $WidgetsCommon;

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_ADMIN);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'Empty';
        $this->DBConn         = $DB_conn_1;
        $this->QBuilder       = $queryBuilder;
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->CommonData     = new FunctionsCommonData();
		$this->WidgetsCommon  = new UIWidgetsCommon();
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
        // Se genera la query
        $query = [
            'data'    => '
                core_permisos_listado.idPermisos,
                core_permisos_listado.idEstado,
                core_permisos_listado.Nombre,
                core_permisos_listado.Descripcion,
                core_permisos_listado.RutaWeb,
                core_permisos_listado.RutaController,
                core_permisos_categorias.Nombre AS PermisosCat,
                core_estados.Nombre AS Estado,
                core_permisos_listado_tipo.Nombre AS Tipo,
                core_permisos_listado_level_limit.NombreCorto AS LevelLimit',
            'table'   => 'core_permisos_listado',
            'join'    => '
                LEFT JOIN core_permisos_categorias          ON core_permisos_categorias.idPermisosCat          = core_permisos_listado.idPermisosCat
                LEFT JOIN core_estados                      ON core_estados.idEstado                           = core_permisos_listado.idEstado
                LEFT JOIN core_permisos_listado_tipo        ON core_permisos_listado_tipo.idTipo               = core_permisos_listado.idTipo
                LEFT JOIN core_permisos_listado_level_limit ON core_permisos_listado_level_limit.idLevelLimit  = core_permisos_listado.idLevelLimit',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'core_permisos_categorias.Nombre ASC, core_permisos_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams     = ['query' => $query];
        // Ejecuto la query
        $arrPermisos = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idPermisosCat AS ID,Nombre',
            'table'   => 'core_permisos_categorias',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams        = ['query' => $query];
        // Ejecuto la query
        $arrPermisosCat = $this->Base_GetList($xParams);

        /************************************/
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
        // Preparo los datos
        $xParams    = ['query' => $query];
        // Ejecuto la query
        $arrEstados = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idTipo AS ID,Nombre',
            'table'   => 'core_permisos_listado_tipo',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $arrTipos = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idLevelLimit AS ID,Nombre',
            'table'   => 'core_permisos_listado_level_limit',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'idLevelLimit ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams       = ['query' => $query];
        // Ejecuto la query
        $arrLevelLimit = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idMetodo AS ID,Nombre',
            'table'   => 'core_permisos_listado_rutas_metodo',
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
        $arrMetodo = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idLevelLimit AS ID,Objetivo AS Nombre',
            'table'   => 'core_permisos_listado_level_limit',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'idLevelLimit ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams     = ['query' => $query];
        // Ejecuto la query
        $arrObjetivo = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($arrPermisos['status'] && $arrPermisosCat['status'] && $arrEstados['status'] && $arrTipos['status'] && $arrLevelLimit['status'] && $arrMetodo['status'] && $arrObjetivo['status']){

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Listado de Permisos',
                'PageDescription' => 'Listado de las Categorias de los permisos.',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => 'Listado de Permisos',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_Codification'    => $this->Codification,
                'Fnc_CommonData'      => $this->CommonData,
                /*=========== Datos Consultados ===========*/
                'arrPermisos'     => $arrPermisos['data'],
                'arrPermisosCat'  => $arrPermisosCat['data'],
                'arrEstados'      => $arrEstados['data'],
                'arrTipos'        => $arrTipos['data'],
                'arrLevelLimit'   => $arrLevelLimit['data'],
                'arrMetodo'       => $arrMetodo['data'],
                'arrObjetivo'     => $arrObjetivo['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/permisosListado-List.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrPermisos,$arrPermisosCat,$arrEstados,$arrTipos,$arrLevelLimit,$arrMetodo,$arrObjetivo]);
            // Despliegue de errores
            $this->showError(1, $f3, $result);
        }
    }

    /*******************************************************************/
    // Actualizar Listar
    /*******************************************************************/
    public function UpdateList($f3){

        /************************************/
        // Variables
        $WhereData_int     = 'idPermisosCat,idEstado,idTipo';             // Datos búsqueda exacta
        $WhereData_string  = 'Nombre,Descripcion,RutaWeb,RutaController'; // Datos búsqueda relativa
        $WhereData_between = '';                                          // Datos búsqueda Between
        $whereInt          = '';                                          // Se crea cadena
        $whereParams       = [];                                          // Valores bindeados asociados a $whereInt
        /************************************/
        // Se validan las fechas
        $RespDataBetween = $this->searchValidateDates($WhereData_between);
        if($RespDataBetween!=''){
            Response::error($RespDataBetween, 500);
        }
        /************************************/
        // Agrego variable busqueda
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_int, 'core_permisos_listado', 1);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_string, 'core_permisos_listado', 2);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_between, 'core_permisos_listado', 3);
        $whereInt = $r['where']; $whereParams = $r['params'];

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                core_permisos_listado.idPermisos,
                core_permisos_listado.idEstado,
                core_permisos_listado.Nombre,
                core_permisos_listado.Descripcion,
                core_permisos_listado.RutaWeb,
                core_permisos_listado.RutaController,
                core_permisos_categorias.Nombre AS PermisosCat,
                core_estados.Nombre AS Estado,
                core_permisos_listado_tipo.Nombre AS Tipo,
                core_permisos_listado_level_limit.NombreCorto AS LevelLimit',
            'table'   => 'core_permisos_listado',
            'join'    => '
                LEFT JOIN core_permisos_categorias          ON core_permisos_categorias.idPermisosCat          = core_permisos_listado.idPermisosCat
                LEFT JOIN core_estados                      ON core_estados.idEstado                           = core_permisos_listado.idEstado
                LEFT JOIN core_permisos_listado_tipo        ON core_permisos_listado_tipo.idTipo               = core_permisos_listado.idTipo
                LEFT JOIN core_permisos_listado_level_limit ON core_permisos_listado_level_limit.idLevelLimit  = core_permisos_listado.idLevelLimit',
            'where'   => $whereInt,
            'params'  => $whereParams,
            'group'   => '',
            'having'  => '',
            'order'   => 'core_permisos_categorias.Nombre ASC, core_permisos_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams     = ['query' => $query];
        // Ejecuto la query
        $arrPermisos = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrPermisos['status'] === true) {

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'TableTitle'      => 'Listado de Permisos',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'    => $this->Codification,
                'Fnc_CommonData'      => $this->CommonData,
                /*=========== Datos Consultados ===========*/
                'arrPermisos'     => $arrPermisos['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/permisosListado-UpdateList.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrPermisos]);
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
        $PermisosID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($PermisosID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                core_permisos_listado.idEstado,
                core_permisos_listado.Nombre,
                core_permisos_listado.Descripcion,
                core_permisos_listado.RutaWeb,
                core_permisos_listado.RutaController,
                core_permisos_categorias.Nombre AS PermisosCat,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor,
                core_permisos_listado_tipo.Nombre AS Tipo,
                core_permisos_listado_level_limit.NombreCorto AS LevelLimit',
            'table'   => 'core_permisos_listado',
            'join'    => '
                LEFT JOIN core_permisos_categorias          ON core_permisos_categorias.idPermisosCat          = core_permisos_listado.idPermisosCat
                LEFT JOIN core_estados                      ON core_estados.idEstado                           = core_permisos_listado.idEstado
                LEFT JOIN core_permisos_listado_tipo        ON core_permisos_listado_tipo.idTipo               = core_permisos_listado.idTipo
                LEFT JOIN core_permisos_listado_level_limit ON core_permisos_listado_level_limit.idLevelLimit  = core_permisos_listado.idLevelLimit',
            'where'   => 'core_permisos_listado.idPermisos = ?',
            'params'  => [$PermisosID['data']],
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
                core_permisos_listado_rutas.RutaWeb,
                core_permisos_listado_rutas.RutaController,
                core_permisos_listado_rutas.Descripcion,
                core_permisos_listado_rutas.Controller,
                core_permisos_listado_rutas_metodo.Nombre AS Metodo,
                core_permisos_listado_level_limit.Objetivo AS LevelLimit',
            'table'   => 'core_permisos_listado_rutas',
            'join'    => '
                LEFT JOIN core_permisos_listado_rutas_metodo ON core_permisos_listado_rutas_metodo.idMetodo     = core_permisos_listado_rutas.idMetodo
                LEFT JOIN core_permisos_listado_level_limit  ON core_permisos_listado_level_limit.idLevelLimit  = core_permisos_listado_rutas.idLevelLimit',
            'where'   => 'core_permisos_listado_rutas.idPermisos = ?',
            'params'  => [$PermisosID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'core_permisos_listado_rutas.Controller ASC, core_permisos_listado_rutas.idLevelLimit ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $arrRutas = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrRutas['status']){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_WidgetsCommon'   => $this->WidgetsCommon,
                'Fnc_CommonData'      => $this->CommonData,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData['data'],
                'arrRutas'        => $arrRutas['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/permisosListado-View.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrRutas]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // View
    /*******************************************************************/
    public function ViewAll($f3, $params){

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                core_permisos_listado_rutas.RutaWeb,
                core_permisos_listado_rutas.RutaController,
                core_permisos_listado_rutas.Descripcion,
                core_permisos_listado_rutas.Controller,
                core_permisos_listado_rutas_metodo.Nombre AS Metodo,
                core_permisos_listado_level_limit.Objetivo AS LevelLimit',
            'table'   => 'core_permisos_listado_rutas',
            'join'    => '
                LEFT JOIN core_permisos_listado_rutas_metodo ON core_permisos_listado_rutas_metodo.idMetodo     = core_permisos_listado_rutas.idMetodo
                LEFT JOIN core_permisos_listado_level_limit  ON core_permisos_listado_level_limit.idLevelLimit  = core_permisos_listado_rutas.idLevelLimit',
            'where'   => 'core_permisos_listado_rutas.idPermisos!=""',
            'group'   => '',
            'having'  => '',
            'order'   => 'core_permisos_listado_rutas.Controller ASC, core_permisos_listado_rutas.idLevelLimit ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $arrRutas = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrRutas['status'] === true) {
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_CommonData'      => $this->CommonData,
                /*=========== Datos Consultados ===========*/
                'arrRutas' => $arrRutas['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/permisosListado-ViewAll.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrRutas]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Resumen
    /*******************************************************************/
    public function Resumen($f3, $params){

        /************************************/
        // Se obtiene el ID
        $PermisosID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($PermisosID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                core_permisos_listado.idPermisos,
                core_permisos_listado.idPermisosCat,
                core_permisos_listado.idEstado,
                core_permisos_listado.idTipo,
                core_permisos_listado.idLevelLimit,
                core_permisos_listado.Nombre,
                core_permisos_listado.Descripcion,
                core_permisos_listado.RutaWeb,
                core_permisos_listado.RutaController,
                core_permisos_categorias.Nombre AS PermisosCat,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor,
                core_permisos_listado_tipo.Nombre AS Tipo,
                core_permisos_listado_level_limit.Nombre AS LevelLimit',
            'table'   => 'core_permisos_listado',
            'join'    => '
                LEFT JOIN core_permisos_categorias          ON core_permisos_categorias.idPermisosCat          = core_permisos_listado.idPermisosCat
                LEFT JOIN core_estados                      ON core_estados.idEstado                           = core_permisos_listado.idEstado
                LEFT JOIN core_permisos_listado_tipo        ON core_permisos_listado_tipo.idTipo               = core_permisos_listado.idTipo
                LEFT JOIN core_permisos_listado_level_limit ON core_permisos_listado_level_limit.idLevelLimit  = core_permisos_listado.idLevelLimit',
            'where'   => 'core_permisos_listado.idPermisos = ?',
            'params'  => [$PermisosID['data']],
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
                core_permisos_listado_rutas.idRutas,
                core_permisos_listado_rutas.idMetodo,
                core_permisos_listado_rutas.RutaWeb,
                core_permisos_listado_rutas.RutaController,
                core_permisos_listado_rutas.Descripcion,
                core_permisos_listado_rutas.Controller,
                core_permisos_listado_rutas_metodo.Nombre AS Metodo,
                core_permisos_listado_level_limit.Objetivo AS LevelLimit',
            'table'   => 'core_permisos_listado_rutas',
            'join'    => '
                LEFT JOIN core_permisos_listado_rutas_metodo    ON core_permisos_listado_rutas_metodo.idMetodo     = core_permisos_listado_rutas.idMetodo
                LEFT JOIN core_permisos_listado_level_limit     ON core_permisos_listado_level_limit.idLevelLimit  = core_permisos_listado_rutas.idLevelLimit',
            'where'   => 'core_permisos_listado_rutas.idPermisos = ?',
            'params'  => [$PermisosID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'core_permisos_listado_rutas.Controller ASC, core_permisos_listado_rutas.idLevelLimit ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $arrRutas = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idPermisosCat AS ID,Nombre',
            'table'   => 'core_permisos_categorias',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams        = ['query' => $query];
        // Ejecuto la query
        $arrPermisosCat = $this->Base_GetList($xParams);

        /************************************/
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
        // Preparo los datos
        $xParams    = ['query' => $query];
        // Ejecuto la query
        $arrEstados = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idTipo AS ID,Nombre',
            'table'   => 'core_permisos_listado_tipo',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $arrTipos = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idLevelLimit AS ID,Nombre',
            'table'   => 'core_permisos_listado_level_limit',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'idLevelLimit ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams       = ['query' => $query];
        // Ejecuto la query
        $arrLevelLimit = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idMetodo AS ID,Nombre',
            'table'   => 'core_permisos_listado_rutas_metodo',
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
        $arrMetodo = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idLevelLimit AS ID,Objetivo AS Nombre',
            'table'   => 'core_permisos_listado_level_limit',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'idLevelLimit ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams     = ['query' => $query];
        // Ejecuto la query
        $arrObjetivo = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrRutas['status'] && $arrPermisosCat['status'] && $arrEstados['status'] && $arrTipos['status'] && $arrLevelLimit['status'] && $arrMetodo['status'] && $arrObjetivo['status']){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Resumen Permiso',
                'PageDescription' => 'Resumen Permiso.',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_WidgetsCommon'   => $this->WidgetsCommon,
                'Fnc_Codification'    => $this->Codification,
                'Fnc_CommonData'      => $this->CommonData,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData['data'],
                'arrRutas'        => $arrRutas['data'],
                'arrPermisosCat'  => $arrPermisosCat['data'],
                'arrEstados'      => $arrEstados['data'],
                'arrTipos'        => $arrTipos['data'],
                'arrLevelLimit'   => $arrLevelLimit['data'],
                'arrMetodo'       => $arrMetodo['data'],
                'arrObjetivo'     => $arrObjetivo['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/permisosListado-Resumen.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrRutas,$arrPermisosCat,$arrEstados,$arrTipos,$arrLevelLimit,$arrMetodo,$arrObjetivo]);
            // Despliegue de errores
            $this->showError(1, $f3, $result);
        }
    }

    /*******************************************************************/
    // Resumen Actualizar
    /*******************************************************************/
    public function ResumenUpdate($f3, $params){

        /************************************/
        // Se obtiene el ID
        $PermisosID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($PermisosID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                core_permisos_listado.Nombre,
                core_permisos_listado.Descripcion,
                core_permisos_listado.RutaWeb,
                core_permisos_listado.RutaController,
                core_permisos_categorias.Nombre AS PermisosCat,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor,
                core_permisos_listado_tipo.Nombre AS Tipo,
                core_permisos_listado_level_limit.Nombre AS LevelLimit',
            'table'   => 'core_permisos_listado',
            'join'    => '
                LEFT JOIN core_permisos_categorias          ON core_permisos_categorias.idPermisosCat          = core_permisos_listado.idPermisosCat
                LEFT JOIN core_estados                      ON core_estados.idEstado                           = core_permisos_listado.idEstado
                LEFT JOIN core_permisos_listado_tipo        ON core_permisos_listado_tipo.idTipo               = core_permisos_listado.idTipo
                LEFT JOIN core_permisos_listado_level_limit ON core_permisos_listado_level_limit.idLevelLimit  = core_permisos_listado.idLevelLimit',
            'where'   => 'core_permisos_listado.idPermisos = ?',
            'params'  => [$PermisosID['data']],
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
                core_permisos_listado_rutas.RutaWeb,
                core_permisos_listado_rutas.RutaController,
                core_permisos_listado_rutas.Descripcion,
                core_permisos_listado_rutas.Controller,
                core_permisos_listado_rutas_metodo.Nombre AS Metodo,
                core_permisos_listado_level_limit.Objetivo AS LevelLimit',
            'table'   => 'core_permisos_listado_rutas',
            'join'    => '
                LEFT JOIN core_permisos_listado_rutas_metodo ON core_permisos_listado_rutas_metodo.idMetodo     = core_permisos_listado_rutas.idMetodo
                LEFT JOIN core_permisos_listado_level_limit  ON core_permisos_listado_level_limit.idLevelLimit  = core_permisos_listado_rutas.idLevelLimit',
            'where'   => 'core_permisos_listado_rutas.idPermisos = ?',
            'params'  => [$PermisosID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'core_permisos_listado_rutas.Controller ASC, core_permisos_listado_rutas.idLevelLimit ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $arrRutas = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrRutas['status']){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_WidgetsCommon'   => $this->WidgetsCommon,
                'Fnc_CommonData'      => $this->CommonData,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData['data'],
                'arrRutas'        => $arrRutas['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/permisosListado-Resumen-Update.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrRutas]);
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
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /************************************/
        //Consulta de la ruta de la categoría
        $query = [
            'data'    => 'Carpeta',
            'table'   => 'core_permisos_categorias',
            'join'    => '',
            'where'   => 'idPermisosCat = ?',
            'params'  => [$_POST['idPermisosCat']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if ($rowData['status'] === false) {
            Response::error('Error al operar con la Base de Datos', 500, $rowData['error'] ?? '');
        }

        /************************************/
        // Se verifica que la categoría exista y tenga carpeta definida
        if (!isset($rowData['data']['Carpeta']) || trim((string) $rowData['data']['Carpeta']) === '') {
            Response::error('La categoría de permiso indicada no existe', 404, [
                ['message' => 'No existe la categoría de permiso: '.$_POST['idPermisosCat'].'.']
            ]);
        }

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Se genera la query
        $newPermiso = [
            'idPermisosCat'   => $_POST['idPermisosCat'],                            //Categoria Permiso
            'idEstado'        => $_POST['idEstado'],                                 //Estado - Activo por defecto
            'idTipo'          => $_POST['idTipo'],                                   //Tipo
            'Nombre'          => $_POST['Nombre'],                                   //Nombre
            'Descripcion'     => $_POST['Descripcion'] ?? '',                        //Descripcion
            'idLevelLimit'    => $_POST['idLevelLimit'],                             //Nivel Acceso
            'RutaWeb'         => $rowData['data']['Carpeta'].'/'.$_POST['RutaWeb'],  //Ruta Web
            'RutaController'  => $_POST['RutaController'],                           //Controlador
        ];
        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idPermisosCat,idEstado,idTipo,Nombre,Descripcion,idLevelLimit,RutaWeb,RutaController',
            'required'  => 'idPermisosCat,idEstado,idTipo,Nombre,idLevelLimit,RutaWeb,RutaController',
            'unique'    => 'Nombre,RutaWeb,RutaController',
            'encode'    => '',
            'table'     => 'core_permisos_listado',
            'Post'      => $newPermiso
        ];
        // Preparo los datos
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        // Ejecuto la query
        $Response = $this->Base_insert($xParams);

        /************************************/
        //Si falla la reserva principal, se revierte de inmediato
        if ($Response['status'] === false) {
            $this->Base_transactionRollback();
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
        }

        /************************************/
        // Variable vacia
        $arrRutas = [];
        /************************************/
        // Variables
        $RutaWeb         = $rowData['data']['Carpeta'].'/'.$_POST['RutaWeb'];
        $RutaController  = $_POST['RutaController'];
        //Se generan las rutas de forma automatica
        switch ($_POST['idTipo']) {
            /************************************/
            //Crud Normal
            case 1:
                /************************************/
                // Se agrega respuesta
                $arrRutas = [
                    /************************************************************/
                    /*                        Vistas                            */
                    /************************************************************/
                    [
                        'idPermisos'     => $Response['data'],            //idPermisos
                        'idMetodo'       => 1,                            //GET
                        'RutaWeb'        => $RutaWeb.'/listAll',          //Ruta
                        'RutaController' => $RutaController.'->listAll',  //Controlador
                        'Descripcion'    => 'Listar Toda la Información', //Descripcion
                        'idLevelLimit'   => 1,                            //Ver - Nivel requerido para ingresar
                        'Controller'     => $RutaController,              //Controlador
                    ],
                    /************************************************************/
                    /*                       Fragments                          */
                    /************************************************************/
                    [
                        'idPermisos'     => $Response['data'],              //idPermisos
                        'idMetodo'       => 2,                              //POST
                        'RutaWeb'        => $RutaWeb.'/search',             //Ruta
                        'RutaController' => $RutaController.'->UpdateList', //Controlador
                        'Descripcion'    => 'Filtrar datos',                //Descripcion
                        'idLevelLimit'   => 1,                              //Ver - Nivel requerido para ingresar
                        'Controller'     => $RutaController,                //Controlador
                    ],
                    [
                        'idPermisos'     => $Response['data'],              //idPermisos
                        'idMetodo'       => 1,                              //GET
                        'RutaWeb'        => $RutaWeb.'/updateList',         //Ruta
                        'RutaController' => $RutaController.'->UpdateList', //Controlador
                        'Descripcion'    => 'Actualizar Lista',             //Descripcion
                        'idLevelLimit'   => 2,                              // Editar - Nivel requerido para ingresar
                        'Controller'     => $RutaController,                //Controlador
                    ],
                    [
                        'idPermisos'     => $Response['data'],        //idPermisos
                        'idMetodo'       => 1,                        //GET
                        'RutaWeb'        => $RutaWeb.'/view/@id',     //Ruta
                        'RutaController' => $RutaController.'->View', //Controlador
                        'Descripcion'    => 'Mostrar Detallado',      //Descripcion
                        'idLevelLimit'   => 1,                        //Ver - Nivel requerido para ingresar
                        'Controller'     => $RutaController,          //Controlador
                    ],
                    [
                        'idPermisos'     => $Response['data'],                        //idPermisos
                        'idMetodo'       => 1,                                        //GET
                        'RutaWeb'        => $RutaWeb.'/getID/@id',                    //Ruta
                        'RutaController' => $RutaController.'->GetID',                //Controlador
                        'Descripcion'    => 'Información para el formulario edición', //Descripcion
                        'idLevelLimit'   => 2,                                        // Editar - Nivel requerido para ingresar
                        'Controller'     => $RutaController,                          //Controlador
                    ],
                    /************************************************************/
                    /*                         Acciones                         */
                    /************************************************************/
                    [
                        'idPermisos'     => $Response['data'],          //idPermisos
                        'idMetodo'       => 2,                          //POST
                        'RutaWeb'        => $RutaWeb,                   //Ruta
                        'RutaController' => $RutaController.'->Insert', //Controlador
                        'Descripcion'    => 'Crear Información',        //Descripcion
                        'idLevelLimit'   => 3,                          //Crear - Nivel requerido para ingresar
                        'Controller'     => $RutaController,            //Controlador
                    ],
                    [
                        'idPermisos'     => $Response['data'],                              //idPermisos
                        'idMetodo'       => 2,                                              //POST
                        'RutaWeb'        => $RutaWeb.'/update',                             //Ruta
                        'RutaController' => $RutaController.'->Update',                     //Controlador
                        'Descripcion'    => 'Editar por post (modificar y subir archivos)', //Descripcion
                        'idLevelLimit'   => 2,                                              // Editar - Nivel requerido para ingresar
                        'Controller'     => $RutaController,                                //Controlador
                    ],
                    [
                        'idPermisos'     => $Response['data'],          //idPermisos
                        'idMetodo'       => 3,                          //DELETE
                        'RutaWeb'        => $RutaWeb,                   //Ruta
                        'RutaController' => $RutaController.'->Delete', //Controlador
                        'Descripcion'    => 'Borrar dato y archivos',   //Descripcion
                        'idLevelLimit'   => 4,                          //Borrar - Nivel requerido para ingresar
                        'Controller'     => $RutaController,            //Controlador
                    ],
                ];
                break;
            /****************************************/
            //Crud Resumen
            case 2:
                /************************************/
                // Variables
                $arrRutas = array();
                $ndata_1  = isset($_POST['Controller']) ? count($_POST['Controller']) : 0;
                /************************************************************/
                /*                        Vistas                            */
                /************************************************************/
                $arrRutas[] = [
                    'idPermisos'     => $Response['data'],            //idPermisos
                    'idMetodo'       => 1,                            //GET
                    'RutaWeb'        => $RutaWeb.'/listAll',          //Ruta
                    'RutaController' => $RutaController.'->listAll',  //Controlador
                    'Descripcion'    => 'Listar Toda la Información', //Descripcion
                    'idLevelLimit'   => 1,                            //Ver - Nivel requerido para ingresar
                    'Controller'     => $RutaController,              //Controlador
                ];
                /************************************************************/
                /*                       Fragments                          */
                /************************************************************/
                $arrRutas[] = [
                    'idPermisos'     => $Response['data'],              //idPermisos
                    'idMetodo'       => 2,                              //POST
                    'RutaWeb'        => $RutaWeb.'/search',             //Ruta
                    'RutaController' => $RutaController.'->UpdateList', //Controlador
                    'Descripcion'    => 'Filtrar datos',                //Descripcion
                    'idLevelLimit'   => 1,                              //Ver - Nivel requerido para ingresar
                    'Controller'     => $RutaController,                //Controlador
                ];
                $arrRutas[] = [
                    'idPermisos'     => $Response['data'],              //idPermisos
                    'idMetodo'       => 1,                              //GET
                    'RutaWeb'        => $RutaWeb.'/updateList',         //Ruta
                    'RutaController' => $RutaController.'->UpdateList', //Controlador
                    'Descripcion'    => 'Actualizar Lista',             //Descripcion
                    'idLevelLimit'   => 2,                              // Editar - Nivel requerido para ingresar
                    'Controller'     => $RutaController,                //Controlador
                ];
                $arrRutas[] = [
                    'idPermisos'     => $Response['data'],        //idPermisos
                    'idMetodo'       => 1,                        //GET
                    'RutaWeb'        => $RutaWeb.'/view/@id',     //Ruta
                    'RutaController' => $RutaController.'->View', //Controlador
                    'Descripcion'    => 'Mostrar Detallado',      //Descripcion
                    'idLevelLimit'   => 1,                        //Ver - Nivel requerido para ingresar
                    'Controller'     => $RutaController,          //Controlador
                ];
                $arrRutas[] = [
                    'idPermisos'     => $Response['data'],            //idPermisos
                    'idMetodo'       => 1,                            //GET
                    'RutaWeb'        => $RutaWeb.'/resumen/@id',      //Ruta
                    'RutaController' => $RutaController.'->Resumen',  //Controlador
                    'Descripcion'    => 'Mostrar Resúmen',            //Descripcion
                    'idLevelLimit'   => 2,                            // Editar - Nivel requerido para ingresar
                    'Controller'     => $RutaController,              //Controlador
                ];
                $arrRutas[] = [
                    'idPermisos'     => $Response['data'],                  //idPermisos
                    'idMetodo'       => 1,                                  //GET
                    'RutaWeb'        => $RutaWeb.'/resumenUpdate/@id',      //Ruta
                    'RutaController' => $RutaController.'->ResumenUpdate',  //Controlador
                    'Descripcion'    => 'Mostrar información',              //Descripcion
                    'idLevelLimit'   => 2,                                  // Editar - Nivel requerido para ingresar
                    'Controller'     => $RutaController,                    //Controlador
                ];
                /************************************************************/
                /*                         Acciones                         */
                /************************************************************/
                $arrRutas[] = [
                    'idPermisos'     => $Response['data'],          //idPermisos
                    'idMetodo'       => 2,                          //POST
                    'RutaWeb'        => $RutaWeb,                   //Ruta
                    'RutaController' => $RutaController.'->Insert', //Controlador
                    'Descripcion'    => 'Crear Información',        //Descripcion
                    'idLevelLimit'   => 3,                          //Crear - Nivel requerido para ingresar
                    'Controller'     => $RutaController,            //Controlador
                ];
                $arrRutas[] = [
                    'idPermisos'     => $Response['data'],                              //idPermisos
                    'idMetodo'       => 2,                                              //POST
                    'RutaWeb'        => $RutaWeb.'/update',                             //Ruta
                    'RutaController' => $RutaController.'->Update',                     //Controlador
                    'Descripcion'    => 'Editar por post (modificar y subir archivos)', //Descripcion
                    'idLevelLimit'   => 2,                                              // Editar - Nivel requerido para ingresar
                    'Controller'     => $RutaController,                                //Controlador
                ];
                $arrRutas[] = [
                    'idPermisos'     => $Response['data'],            //idPermisos
                    'idMetodo'       => 4,                            //PUT
                    'RutaWeb'        => $RutaWeb.'/delFiles',         //Ruta
                    'RutaController' => $RutaController.'->DelFiles', //Controlador
                    'Descripcion'    => 'Permite eliminar archivos',  //Descripcion
                    'idLevelLimit'   => 2,                            // Editar - Nivel requerido para ingresar
                    'Controller'     => $RutaController,              //Controlador
                ];
                $arrRutas[] = [
                    'idPermisos'     => $Response['data'],          //idPermisos
                    'idMetodo'       => 3,                          //DELETE
                    'RutaWeb'        => $RutaWeb,                   //Ruta
                    'RutaController' => $RutaController.'->Delete', //Controlador
                    'Descripcion'    => 'Borrar dato y archivos',   //Descripcion
                    'idLevelLimit'   => 4,                          //Borrar - Nivel requerido para ingresar
                    'Controller'     => $RutaController,            //Controlador
                ];
                /******************************************************************/
                /******************************************************************/
                // Variables
                $SubRutaWeb         = $rowData['data']['Carpeta'].'/'.$_POST['RutaWeb'].'/observaciones';
                $RutaSubController  = $_POST['RutaController'].'Observaciones';
                /************************************************************/
                /*                 Observaciones - Fragments                */
                /************************************************************/
                $arrRutas[] = [
                    'idPermisos'     => $Response['data'],          //idPermisos
                    'idMetodo'       => 1,                          //GET
                    'RutaWeb'        => $SubRutaWeb.'/new/@id',     //Ruta
                    'RutaController' => $RutaSubController.'->New', //Controlador
                    'Descripcion'    => 'Mostrar modal nuevo',      //Descripcion
                    'idLevelLimit'   => 2,                          // Editar - Nivel requerido para ingresar
                    'Controller'     => $RutaSubController,         //Controlador
                ];
                $arrRutas[] = [
                    'idPermisos'     => $Response['data'],                 //idPermisos
                    'idMetodo'       => 1,                                 //GET
                    'RutaWeb'        => $SubRutaWeb.'/updateList/@id',     //Ruta
                    'RutaController' => $RutaSubController.'->UpdateList', //Controlador
                    'Descripcion'    => 'Actualizar Lista',                //Descripcion
                    'idLevelLimit'   => 2,                                 // Editar - Nivel requerido para ingresar
                    'Controller'     => $RutaSubController,                //Controlador
                ];
                $arrRutas[] = [
                    'idPermisos'     => $Response['data'],            //idPermisos
                    'idMetodo'       => 1,                            //GET
                    'RutaWeb'        => $SubRutaWeb.'/view/@id',      //Ruta
                    'RutaController' => $RutaSubController.'->View',  //Controlador
                    'Descripcion'    => 'Mostrar Detallado',          //Descripcion
                    'idLevelLimit'   => 2,                            // Editar - Nivel requerido para ingresar
                    'Controller'     => $RutaSubController,           //Controlador
                ];
                $arrRutas[] = [
                    'idPermisos'     => $Response['data'],                        //idPermisos
                    'idMetodo'       => 1,                                        //GET
                    'RutaWeb'        => $SubRutaWeb.'/getID/@id',                 //Ruta
                    'RutaController' => $RutaSubController.'->GetID',             //Controlador
                    'Descripcion'    => 'Información para el formulario edición', //Descripcion
                    'idLevelLimit'   => 2,                                        // Editar - Nivel requerido para ingresar
                    'Controller'     => $RutaSubController,                       //Controlador
                ];
                /************************************************************/
                /*                  Observaciones - Aciones                 */
                /************************************************************/
                $arrRutas[] = [
                    'idPermisos'     => $Response['data'],             //idPermisos
                    'idMetodo'       => 2,                             //POST
                    'RutaWeb'        => $SubRutaWeb,                   //Ruta
                    'RutaController' => $RutaSubController.'->Insert', //Controlador
                    'Descripcion'    => 'Crear Información',           //Descripcion
                    'idLevelLimit'   => 2,                             //Crear - Nivel requerido para ingresar
                    'Controller'     => $RutaSubController,            //Controlador
                ];
                $arrRutas[] = [
                    'idPermisos'     => $Response['data'],                              //idPermisos
                    'idMetodo'       => 2,                                              //POST
                    'RutaWeb'        => $SubRutaWeb.'/update',                          //Ruta
                    'RutaController' => $RutaSubController.'->Update',                  //Controlador
                    'Descripcion'    => 'Editar por post (modificar y subir archivos)', //Descripcion
                    'idLevelLimit'   => 2,                                              // Editar - Nivel requerido para ingresar
                    'Controller'     => $RutaSubController,                             //Controlador
                ];
                $arrRutas[] = [
                    'idPermisos'     => $Response['data'],             //idPermisos
                    'idMetodo'       => 3,                             //DELETE
                    'RutaWeb'        => $SubRutaWeb,                   //Ruta
                    'RutaController' => $RutaSubController.'->Delete', //Controlador
                    'Descripcion'    => 'Borrar dato y archivos',      //Descripcion
                    'idLevelLimit'   => 2,                             //Borrar - Nivel requerido para ingresar
                    'Controller'     => $RutaSubController,            //Controlador
                ];
                /******************************************************************/
                /******************************************************************/
                // Recorro los controladores internos extras
                if(isset($ndata_1)&&$ndata_1!=0){
                    for($j1 = 0; $j1 < $ndata_1; $j1++){
                        /************************************/
                        // Variables
                        $SubRutaWeb         = $rowData['data']['Carpeta'].'/'.$_POST['RutaWeb'].'/'.$_POST['SubRuta'][$j1];
                        $RutaSubController  = $_POST['RutaController'].$_POST['Controller'][$j1];
                        /************************************************************/
                        /*                 Observaciones - Fragments                */
                        /************************************************************/
                        $arrRutas[] = [
                            'idPermisos'     => $Response['data'],          //idPermisos
                            'idMetodo'       => 1,                          //GET
                            'RutaWeb'        => $SubRutaWeb.'/new/@id',     //Ruta
                            'RutaController' => $RutaSubController.'->New', //Controlador
                            'Descripcion'    => 'Mostrar modal nuevo',      //Descripcion
                            'idLevelLimit'   => 2,                          // Editar - Nivel requerido para ingresar
                            'Controller'     => $RutaSubController,         //Controlador
                        ];
                        $arrRutas[] = [
                            'idPermisos'     => $Response['data'],                 //idPermisos
                            'idMetodo'       => 1,                                 //GET
                            'RutaWeb'        => $SubRutaWeb.'/updateList/@id',     //Ruta
                            'RutaController' => $RutaSubController.'->UpdateList', //Controlador
                            'Descripcion'    => 'Actualizar Lista',                //Descripcion
                            'idLevelLimit'   => 2,                                 // Editar - Nivel requerido para ingresar
                            'Controller'     => $RutaSubController,                //Controlador
                        ];
                        $arrRutas[] = [
                            'idPermisos'     => $Response['data'],            //idPermisos
                            'idMetodo'       => 1,                            //GET
                            'RutaWeb'        => $SubRutaWeb.'/view/@id',      //Ruta
                            'RutaController' => $RutaSubController.'->View',  //Controlador
                            'Descripcion'    => 'Mostrar Detallado',          //Descripcion
                            'idLevelLimit'   => 2,                            // Editar - Nivel requerido para ingresar
                            'Controller'     => $RutaSubController,           //Controlador
                        ];
                        $arrRutas[] = [
                            'idPermisos'     => $Response['data'],                        //idPermisos
                            'idMetodo'       => 1,                                        //GET
                            'RutaWeb'        => $SubRutaWeb.'/getID/@id',                 //Ruta
                            'RutaController' => $RutaSubController.'->GetID',             //Controlador
                            'Descripcion'    => 'Información para el formulario edición', //Descripcion
                            'idLevelLimit'   => 2,                                        // Editar - Nivel requerido para ingresar
                            'Controller'     => $RutaSubController,                       //Controlador
                        ];
                        /************************************************************/
                        /*                  Observaciones - Aciones                 */
                        /************************************************************/
                        $arrRutas[] = [
                            'idPermisos'     => $Response['data'],             //idPermisos
                            'idMetodo'       => 2,                             //POST
                            'RutaWeb'        => $SubRutaWeb,                   //Ruta
                            'RutaController' => $RutaSubController.'->Insert', //Controlador
                            'Descripcion'    => 'Crear Información',           //Descripcion
                            'idLevelLimit'   => 2,                             //Crear - Nivel requerido para ingresar
                            'Controller'     => $RutaSubController,            //Controlador
                        ];
                        $arrRutas[] = [
                            'idPermisos'     => $Response['data'],                              //idPermisos
                            'idMetodo'       => 2,                                              //POST
                            'RutaWeb'        => $SubRutaWeb.'/update',                          //Ruta
                            'RutaController' => $RutaSubController.'->Update',                  //Controlador
                            'Descripcion'    => 'Editar por post (modificar y subir archivos)', //Descripcion
                            'idLevelLimit'   => 2,                                              // Editar - Nivel requerido para ingresar
                            'Controller'     => $RutaSubController,                             //Controlador
                        ];
                        $arrRutas[] = [
                            'idPermisos'     => $Response['data'],             //idPermisos
                            'idMetodo'       => 3,                             //DELETE
                            'RutaWeb'        => $SubRutaWeb,                   //Ruta
                            'RutaController' => $RutaSubController.'->Delete', //Controlador
                            'Descripcion'    => 'Borrar dato y archivos',      //Descripcion
                            'idLevelLimit'   => 2,                             //Borrar - Nivel requerido para ingresar
                            'Controller'     => $RutaSubController,            //Controlador
                        ];
                    }
                }
                break;
            /****************************************/
            //Informe
            case 3:
                /************************************/
                // Se agrega respuesta
                $arrRutas = [
                    /************************************************************/
                    /*                        Vistas                            */
                    /************************************************************/
                    [
                        'idPermisos'     => $Response['data'],            //idPermisos
                        'idMetodo'       => 1,                            //GET
                        'RutaWeb'        => $RutaWeb.'/listAll',          //Ruta
                        'RutaController' => $RutaController.'->listAll',  //Controlador
                        'Descripcion'    => 'Filtro de búsqueda',         //Descripcion
                        'idLevelLimit'   => 1,                            //Ver - Nivel requerido para ingresar
                        'Controller'     => $RutaController,              //Controlador
                    ],
                    /************************************************************/
                    /*                       Fragments                          */
                    /************************************************************/
                    [
                        'idPermisos'     => $Response['data'],              //idPermisos
                        'idMetodo'       => 2,                              //POST
                        'RutaWeb'        => $RutaWeb.'/search',             //Ruta
                        'RutaController' => $RutaController.'->UpdateList', //Controlador
                        'Descripcion'    => 'Filtrar datos',                //Descripcion
                        'idLevelLimit'   => 1,                              //Ver - Nivel requerido para ingresar
                        'Controller'     => $RutaController,                //Controlador
                    ],
                    /************************************************************/
                    /*                         Acciones                         */
                    /************************************************************/
                    [
                        'idPermisos'     => $Response['data'],        //idPermisos
                        'idMetodo'       => 1,                        //GET
                        'RutaWeb'        => $RutaWeb.'/view/@id',     //Ruta
                        'RutaController' => $RutaController.'->View', //Controlador
                        'Descripcion'    => 'Mostrar Detallado',      //Descripcion
                        'idLevelLimit'   => 1,                        //Ver - Nivel requerido para ingresar
                        'Controller'     => $RutaController,          //Controlador
                    ],
                ];

                break;
            /****************************************/
            //Otros
            case 4:
                //Nada
                break;
        }

        /************************************/
        // Verifico si existe
        if($arrRutas){
            // Recorro
            foreach ($arrRutas as $rutas) {
                /************************************/
                // Se genera la query
                $query = [
                    'data'      => 'idPermisos,idMetodo,RutaWeb,RutaController,Descripcion,idLevelLimit,Controller',
                    'required'  => '',
                    'unique'    => '',
                    'table'     => 'core_permisos_listado_rutas',
                    'Post'      => $rutas,
                ];
                // Ejecuto la query
                $ResponseInsert = $this->QBuilder->queryInsert($query, $this->DBConn, false, true);

                /************************************/
                //Si falla la reserva principal, se revierte de inmediato
                if ($ResponseInsert['status'] === false) {
                    $this->Base_transactionRollback();
                    Response::error('Error al operar con la Base de Datos', 500, $ResponseInsert['error'] ?? '');
                }
            }
        }

        /************************************/
        //Se actualizan los permisos al crear uno nuevo
        $ResponsePermisos = $this->updatePermisos($f3);
        if($ResponsePermisos['code'] != 200){
            $this->Base_transactionRollback();
            Response::error($ResponsePermisos['message'], $ResponsePermisos['code'], $ResponsePermisos['error'] ?? '');
        }

        /************************************/
        // Se confirma la transacción
        $this->Base_transactionCommit();

        /************************************/
        // Si es un ID numérico, encripta y envía con código 200 (OK)
        $DataID = $this->Codification->encryptDecrypt('encrypt', $Response['data']);
        if (!$this->isValidDecrypted($DataID, 'text')) {
            Response::error('Registro inválido', 400);
        }
        Response::success($DataID['data']);

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
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idPermisos,idPermisosCat,idEstado,idTipo,Nombre,Descripcion,idLevelLimit,RutaWeb,RutaController',
            'required'  => 'idPermisos,idPermisosCat,idEstado,idTipo,Nombre,idLevelLimit,RutaWeb,RutaController',
            'unique'    => 'Nombre,RutaWeb,RutaController',
            'encode'    => '',
            'table'     => 'core_permisos_listado',
            'where'     => 'idPermisos',
            'Post'      => $_POST,
        ];
        // Preparo los datos
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        // Ejecuto la query
        $Response = $this->Base_update($xParams);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if ($Response['status'] === false) {
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
        }

        /************************************/
        //Se actualizan los permisos al crear uno nuevo
        $ResponsePermisos = $this->updatePermisos($f3);
        if($ResponsePermisos['code'] != 200){
            Response::error($ResponsePermisos['message'], $ResponsePermisos['code'], $ResponsePermisos['error'] ?? '');
        }

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data']);

    }

    /*******************************************************************/
    // Borrar dato y archivos
    /*******************************************************************/
    public function Delete($f3){

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
            'table'       => 'core_permisos_listado',
            'where'       => 'idPermisos',
            'SubCarpeta'  => '',
            'Post'        => $dataDelete
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $Response = $this->Base_delete($xParams);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if ($Response['status'] === false) {
            $this->Base_transactionRollback();
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
        }

        /************************************/
        // Se genera la query
        $query = [
            'files'       => '',
            'table'       => 'core_permisos_listado_rutas',
            'where'       => 'idPermisos',
            'SubCarpeta'  => '',
            'Post'        => $dataDelete
        ];
        // Preparo los datos
        $xParams          = ['query' => $query];
        // Ejecuto la query
        $ResponseDelRutas = $this->Base_delete($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($ResponseDelRutas['status'] === false) {
            $this->Base_transactionRollback();
            Response::error('Error al operar con la Base de Datos', 500, $ResponseDelRutas['error'] ?? '');
        }

        /************************************/
        //Se actualizan los permisos al crear uno nuevo
        $ResponsePermisos = $this->updatePermisos($f3);
        if($ResponsePermisos['code'] != 200){
            $this->Base_transactionRollback();
            Response::error($ResponsePermisos['message'], $ResponsePermisos['code'], $ResponsePermisos['error'] ?? '');
        }

        /************************************/
        // Se confirma la transacción
        $this->Base_transactionCommit();

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($ResponseDelRutas['data']);

    }

    /******************************************************************************/
    /*                             Métodos privados                               */
    /******************************************************************************/
    /*******************************************************************/
    // Se validan los datos
    /*******************************************************************/
    private function dataCheck($POST){
        // Variables
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
            'ValidarLargoMinimo'        => 'Nombre,Descripcion',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'Nombre',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Nombre,Descripcion',
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
    // Se actualizan los permisos de la sesion
    /*******************************************************************/
    public function updatePermisos($f3){

        /************************************/
        //Consulta para el menu
        $query = [
            'data'    => '
                core_permisos_categorias.Nombre AS PermisosCat,
                core_permisos_categorias.Icon AS PermisosIcon,
                core_iconos_colores.Nombre AS PermisosIconColor,
                core_permisos_listado.Nombre,
                core_permisos_listado.RutaWeb,
                core_permisos_listado.idLevelLimit AS PermisosLevel,
                core_permisos_listado.RutaController AS PermisosController',
            'table'   => 'core_permisos_listado',
            'join'    => '
                LEFT JOIN core_permisos_categorias ON core_permisos_categorias.idPermisosCat = core_permisos_listado.idPermisosCat
                LEFT JOIN core_iconos_colores      ON core_iconos_colores.idColor            = core_permisos_categorias.IdIconColor',
            'where'   => 'core_permisos_listado.idEstado = ?',
            'params'  => [1],
            'group'   => '',
            'having'  => '',
            'order'   => 'core_permisos_categorias.Nombre ASC, core_permisos_listado.Nombre ASC, core_permisos_listado.RutaWeb ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrMenu = $this->Base_GetList($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($arrMenu['status'] === false) {
            return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $arrMenu['error']];
        }

        /************************************/
        //Consulta para las rutas
        $query = [
            'data'    => '
                core_permisos_listado_rutas_metodo.Nombre AS Metodo,
                core_permisos_listado_rutas.RutaWeb,
                core_permisos_listado_rutas.RutaController',
            'table'   => 'core_permisos_listado',
            'join'    => '
                LEFT JOIN core_permisos_listado_rutas        ON core_permisos_listado_rutas.idPermisos      = core_permisos_listado.idPermisos
                LEFT JOIN core_permisos_listado_rutas_metodo ON core_permisos_listado_rutas_metodo.idMetodo = core_permisos_listado_rutas.idMetodo',
            'where'   => 'core_permisos_listado.idEstado = ?',
            'params'  => [1],
            'group'   => '',
            'having'  => '',
            'order'   => 'core_permisos_listado_rutas_metodo.Nombre ASC, core_permisos_listado_rutas.RutaWeb ASC, core_permisos_listado_rutas.RutaController ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams     = ['query' => $query];
        // Ejecuto la query
        $arrPermisos = $this->Base_GetList($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($arrPermisos['status'] === false) {
            return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $arrPermisos['error']];
        }

        /************************************/
        //se crea variable para los niveles de permisos
        // aqui solo entra el administrador, no es necesario validar tipo de usuario
        $arrLevel = [];
        //se recorren las variables
        foreach ($arrMenu['data'] as $value) {
            //se crea la variable
            $arrLevel[$value['PermisosController']]['LevelAccess']  = $value['PermisosLevel'];
            $arrLevel[$value['PermisosController']]['RouteAccess']  = $value['RutaWeb'];
        }
        //Permisos rutas de prueba
        $arrLevel['crudNormal']['LevelAccess']   = 4;
        $arrLevel['crudResumen']['LevelAccess']  = 4;
        $arrLevel['crudInforme']['LevelAccess']  = 4;
        $arrLevel['Empty']['LevelAccess']        = 4;
        $arrLevel['crudNormal']['RouteAccess']   = 'Core/pruebas/crudNormal';
        $arrLevel['crudResumen']['RouteAccess']  = 'Core/pruebas/crudResumen';
        $arrLevel['crudInforme']['RouteAccess']  = 'Core/pruebas/crudInforme';
        $arrLevel['Empty']['RouteAccess']        = '';

        /***************************************************/
        /*          Se guardan lo datos del usuario        */
        /***************************************************/

        /************************************/
        //Se agrupan los menus
        $arrMenuNew = $this->CommonData->agruparPorClave ($arrMenu['data'], 'PermisosCat' );

        /************************************/
        //Se limpian las variables
        $f3->clear('SESSION.arrMenu');
        $f3->clear('SESSION.arrPermisos');
        $f3->clear('SESSION.arrLevel');

        /************************************/
        //Seteo las variables
        $f3->set('SESSION.arrMenu', $arrMenuNew);                // Menu
        $f3->set('SESSION.arrPermisos', $arrPermisos['data']);   // Rutas
        $f3->set('SESSION.arrLevel', $arrLevel);                 // Niveles de permisos

        /************************************/
        // Retorno los datos
        return ['code' => 200, 'data' => 'OK'];

    }

}
