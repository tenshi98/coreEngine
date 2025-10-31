<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class permisosListado extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;
    private $DBConn;
    private $QBuilder;
    private $FormInputs;
    private $Codification;
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
            'where'   => 'core_permisos_listado.idPermisos!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'core_permisos_categorias.Nombre ASC, core_permisos_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams     = ['query' => $query];
        $arrPermisos = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idPermisosCat AS ID,Nombre',
            'table'   => 'core_permisos_categorias',
            'join'    => '',
            'where'   => 'idPermisosCat!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams        = ['query' => $query];
        $arrPermisosCat = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idEstado AS ID,Nombre',
            'table'   => 'core_estados',
            'join'    => '',
            'where'   => 'idEstado!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams    = ['query' => $query];
        $arrEstados = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idTipo AS ID,Nombre',
            'table'   => 'core_permisos_listado_tipo',
            'join'    => '',
            'where'   => 'idTipo!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams  = ['query' => $query];
        $arrTipos = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idLevelLimit AS ID,Nombre',
            'table'   => 'core_permisos_listado_level_limit',
            'join'    => '',
            'where'   => 'idLevelLimit!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'idLevelLimit ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams       = ['query' => $query];
        $arrLevelLimit = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idMetodo AS ID,Nombre',
            'table'   => 'core_permisos_listado_rutas_metodo',
            'join'    => '',
            'where'   => 'idMetodo!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrMetodo = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idLevelLimit AS ID,Objetivo AS Nombre',
            'table'   => 'core_permisos_listado_level_limit',
            'join'    => '',
            'where'   => 'idLevelLimit!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'idLevelLimit ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams     = ['query' => $query];
        $arrObjetivo = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($arrPermisos)){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Listado de Permisos',
                'PageDescription' => 'Listado de las Categorias de los permisos.',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => 'Listado de Permisos',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_Codification'    => $this->Codification,
                'Fnc_CommonData'      => $this->CommonData,
                /*=========== Datos Consultados ===========*/
                'arrPermisos'     => $arrPermisos,
                'arrPermisosCat'  => $arrPermisosCat,
                'arrEstados'      => $arrEstados,
                'arrTipos'        => $arrTipos,
                'arrLevelLimit'   => $arrLevelLimit,
                'arrMetodo'       => $arrMetodo,
                'arrObjetivo'     => $arrObjetivo,
            ];

            /******************************************/
            //Se instancia la vista
            $view = new View;
            echo $view->render('../app/templates/user-header.php');                                        // Header
            echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/permisosListado-List.php');  // Vista
            echo $view->render('../app/templates/user-footer.php');                                        // Footer
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

        /*******************************************************************/
        //Variables
        $WhereData_int     = 'idPermisosCat,idEstado,idTipo';             //Datos búsqueda exacta
        $WhereData_string  = 'Nombre,Descripcion,RutaWeb,RutaController'; //Datos búsqueda relativa
        $WhereData_between = '';                                          //Datos búsqueda Between
        $whereInt          = '';                                          //se crea cadena
        /******************************************/
        //agrego variable busqueda
        $whereInt = $this->searchWhere($whereInt, $WhereData_int, 'core_permisos_listado', 1);
        $whereInt = $this->searchWhere($whereInt, $WhereData_string, 'core_permisos_listado', 2);
        $whereInt = $this->searchWhere($whereInt, $WhereData_between, 'core_permisos_listado', 3);

        /******************************/
        //Se genera la query
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
            'group'   => '',
            'having'  => '',
            'order'   => 'core_permisos_categorias.Nombre ASC, core_permisos_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams     = ['query' => $query];
        $arrPermisos = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($arrPermisos)){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'TableTitle'      => 'Listado de Permisos',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'    => $this->Codification,
                'Fnc_CommonData'      => $this->CommonData,
                /*=========== Datos Consultados ===========*/
                'arrPermisos'     => $arrPermisos,
            ];

            /******************************************/
            //Se instancia la vista
            $view = new View;
            echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/permisosListado-UpdateList.php'); // Vista
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
            'where'   => 'core_permisos_listado.idPermisos = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
            'where'   => 'core_permisos_listado_rutas.idPermisos = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'core_permisos_listado_rutas.Controller ASC, core_permisos_listado_rutas.idLevelLimit ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams  = ['query' => $query];
        $arrRutas = $this->Base_GetList($xParams);

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
                'Fnc_WidgetsCommon'   => $this->WidgetsCommon,
                'Fnc_CommonData'      => $this->CommonData,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData,
                'arrRutas'        => $arrRutas,
            ];

            /******************************************/
            //Se instancia la vista
            $view = new View;
            echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/permisosListado-View.php'); // Vista
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //View
    public function ViewAll($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /*******************************************************************/
        //Se genera la query
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
        //Ejecuto la query
        $xParams  = ['query' => $query];
        $arrRutas = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($arrRutas)){
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_CommonData'      => $this->CommonData,
                /*=========== Datos Consultados ===========*/
                'arrRutas' => $arrRutas,
            ];

            /******************************************/
            //Se instancia la vista
            $view = new View;
            echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/permisosListado-ViewAll.php'); // Vista
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //Resumen
    public function Resumen($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
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
            'where'   => 'core_permisos_listado.idPermisos = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
            'where'   => 'core_permisos_listado_rutas.idPermisos = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'core_permisos_listado_rutas.Controller ASC, core_permisos_listado_rutas.idLevelLimit ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams  = ['query' => $query];
        $arrRutas = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idPermisosCat AS ID,Nombre',
            'table'   => 'core_permisos_categorias',
            'join'    => '',
            'where'   => 'idPermisosCat!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams        = ['query' => $query];
        $arrPermisosCat = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idEstado AS ID,Nombre',
            'table'   => 'core_estados',
            'join'    => '',
            'where'   => 'idEstado!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams    = ['query' => $query];
        $arrEstados = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idTipo AS ID,Nombre',
            'table'   => 'core_permisos_listado_tipo',
            'join'    => '',
            'where'   => 'idTipo!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams  = ['query' => $query];
        $arrTipos = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idLevelLimit AS ID,Nombre',
            'table'   => 'core_permisos_listado_level_limit',
            'join'    => '',
            'where'   => 'idLevelLimit!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'idLevelLimit ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams       = ['query' => $query];
        $arrLevelLimit = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idMetodo AS ID,Nombre',
            'table'   => 'core_permisos_listado_rutas_metodo',
            'join'    => '',
            'where'   => 'idMetodo!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrMetodo = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idLevelLimit AS ID,Objetivo AS Nombre',
            'table'   => 'core_permisos_listado_level_limit',
            'join'    => '',
            'where'   => 'idLevelLimit!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'idLevelLimit ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams     = ['query' => $query];
        $arrObjetivo = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if ($rowData!==false) {
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Resumen Permiso',
                'PageDescription' => 'Resumen Permiso.',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_WidgetsCommon'   => $this->WidgetsCommon,
                'Fnc_Codification'    => $this->Codification,
                'Fnc_CommonData'      => $this->CommonData,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData,
                'arrRutas'        => $arrRutas,
                'arrPermisosCat'  => $arrPermisosCat,
                'arrEstados'      => $arrEstados,
                'arrTipos'        => $arrTipos,
                'arrLevelLimit'   => $arrLevelLimit,
                'arrMetodo'       => $arrMetodo,
                'arrObjetivo'     => $arrObjetivo,
            ];

            /******************************************/
            //Se instancia la vista
            $view = new View;
            echo $view->render('../app/templates/user-header.php');                                           // Header
            echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/permisosListado-Resumen.php');  // Vista
            echo $view->render('../app/templates/user-footer.php');                                           // Footer
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 1, $f3);
        }
    }

    /******************************************************************************/
    //Resumen-Update
    public function ResumenUpdate($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
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
            'where'   => 'core_permisos_listado.idPermisos = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
            'where'   => 'core_permisos_listado_rutas.idPermisos = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'core_permisos_listado_rutas.Controller ASC, core_permisos_listado_rutas.idLevelLimit ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams  = ['query' => $query];
        $arrRutas = $this->Base_GetList($xParams);

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
                'Fnc_WidgetsCommon'   => $this->WidgetsCommon,
                'Fnc_CommonData'      => $this->CommonData,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData,
                'arrRutas'        => $arrRutas,
            ];

            /******************************************/
            //Se instancia la vista
            $view = new View;
            echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/permisosListado-Resumen-Update.php'); // Vista
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
    public function Insert($f3){

        /******************************/
        //Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /****************************************/
        //Consulta de la ruta de la categoría
        $query = [
            'data'    => 'Carpeta',
            'table'   => 'core_permisos_categorias',
            'join'    => '',
            'where'   => 'idPermisosCat = "'.$_POST['idPermisosCat'].'"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /******************************/
        //Se genera la query
        $newPermiso = [
            'idPermisosCat'   => $_POST['idPermisosCat'],                    //Categoria Permiso
            'idEstado'        => $_POST['idEstado'],                         //Estado - Activo por defecto
            'idTipo'          => $_POST['idTipo'],                           //Tipo
            'Nombre'          => $_POST['Nombre'],                           //Nombre
            'Descripcion'     => $_POST['Descripcion'],                      //Descripcion
            'idLevelLimit'    => $_POST['idLevelLimit'],                     //Nivel Acceso
            'RutaWeb'         => $rowData['Carpeta'].'/'.$_POST['RutaWeb'],  //Ruta Web
            'RutaController'  => $_POST['RutaController'],                   //Controlador
        ];

        /******************************/
        //Se genera la query
        $query = [
            'data'      => 'idPermisosCat,idEstado,idTipo,Nombre,Descripcion,idLevelLimit,RutaWeb,RutaController',
            'required'  => 'idPermisosCat,idEstado,idTipo,Nombre,idLevelLimit,RutaWeb,RutaController',
            'unique'    => 'Nombre,RutaWeb,RutaController',
            'encode'    => '',
            'table'     => 'core_permisos_listado',
            'Post'      => $newPermiso
        ];
        //Ejecuto la query
        //Ejecuto la query
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        $Response = $this->Base_insert($xParams);

        /******************************/
        // Se asume que $Response contendrá un array de errores/datos, un ID numérico o algún otro valor.
        if (is_numeric($Response)) {
            /****************************************/
            //Variable vacia
            $arrRutas = [];
            /******************************/
            //variables
            $RutaWeb         = $rowData['Carpeta'].'/'.$_POST['RutaWeb'];
            $RutaController  = $_POST['RutaController'];
            //Se generan las rutas de forma automatica
            switch ($_POST['idTipo']) {
                /****************************************/
                //Crud Normal
                case 1:
                    /******************************/
                    //Se agrega respuesta
                    $arrRutas = [
                        /************************************************************/
                        /*                        Vistas                            */
                        /************************************************************/
                        [
                            'idPermisos'     => $Response,                    //idPermisos
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
                            'idPermisos'     => $Response,                      //idPermisos
                            'idMetodo'       => 2,                              //POST
                            'RutaWeb'        => $RutaWeb.'/search',             //Ruta
                            'RutaController' => $RutaController.'->UpdateList', //Controlador
                            'Descripcion'    => 'Filtrar datos',                //Descripcion
                            'idLevelLimit'   => 1,                              //Ver - Nivel requerido para ingresar
                            'Controller'     => $RutaController,                //Controlador
                        ],
                        [
                            'idPermisos'     => $Response,                      //idPermisos
                            'idMetodo'       => 1,                              //GET
                            'RutaWeb'        => $RutaWeb.'/updateList',         //Ruta
                            'RutaController' => $RutaController.'->UpdateList', //Controlador
                            'Descripcion'    => 'Actualizar Lista',             //Descripcion
                            'idLevelLimit'   => 2,                              //Editar - Nivel requerido para ingresar
                            'Controller'     => $RutaController,                //Controlador
                        ],
                        [
                            'idPermisos'     => $Response,                //idPermisos
                            'idMetodo'       => 1,                        //GET
                            'RutaWeb'        => $RutaWeb.'/view/@id',     //Ruta
                            'RutaController' => $RutaController.'->View', //Controlador
                            'Descripcion'    => 'Mostrar Detallado',      //Descripcion
                            'idLevelLimit'   => 1,                        //Ver - Nivel requerido para ingresar
                            'Controller'     => $RutaController,          //Controlador
                        ],
                        [
                            'idPermisos'     => $Response,                                //idPermisos
                            'idMetodo'       => 1,                                        //GET
                            'RutaWeb'        => $RutaWeb.'/getID/@id',                    //Ruta
                            'RutaController' => $RutaController.'->GetID',                //Controlador
                            'Descripcion'    => 'Información para el formulario edición', //Descripcion
                            'idLevelLimit'   => 2,                                        //Editar - Nivel requerido para ingresar
                            'Controller'     => $RutaController,                          //Controlador
                        ],
                        /************************************************************/
                        /*                         Acciones                         */
                        /************************************************************/
                        [
                            'idPermisos'     => $Response,                  //idPermisos
                            'idMetodo'       => 2,                          //POST
                            'RutaWeb'        => $RutaWeb,                   //Ruta
                            'RutaController' => $RutaController.'->Insert', //Controlador
                            'Descripcion'    => 'Crear Información',        //Descripcion
                            'idLevelLimit'   => 3,                          //Crear - Nivel requerido para ingresar
                            'Controller'     => $RutaController,            //Controlador
                        ],
                        [
                            'idPermisos'     => $Response,                                      //idPermisos
                            'idMetodo'       => 2,                                              //POST
                            'RutaWeb'        => $RutaWeb.'/update',                             //Ruta
                            'RutaController' => $RutaController.'->Update',                     //Controlador
                            'Descripcion'    => 'Editar por post (modificar y subir archivos)', //Descripcion
                            'idLevelLimit'   => 2,                                              //Editar - Nivel requerido para ingresar
                            'Controller'     => $RutaController,                                //Controlador
                        ],
                        [
                            'idPermisos'     => $Response,                  //idPermisos
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
                    /******************************/
                    //variables
                    $arrRutas = array();
                    $ndata_1  = isset($_POST['Controller']) ? count($_POST['Controller']) : 0;
                    /************************************************************/
                    /*                        Vistas                            */
                    /************************************************************/
                    $arrRutas[] = [
                        'idPermisos'     => $Response,                    //idPermisos
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
                        'idPermisos'     => $Response,                      //idPermisos
                        'idMetodo'       => 2,                              //POST
                        'RutaWeb'        => $RutaWeb.'/search',             //Ruta
                        'RutaController' => $RutaController.'->UpdateList', //Controlador
                        'Descripcion'    => 'Filtrar datos',                //Descripcion
                        'idLevelLimit'   => 1,                              //Ver - Nivel requerido para ingresar
                        'Controller'     => $RutaController,                //Controlador
                    ];
                    $arrRutas[] = [
                        'idPermisos'     => $Response,                      //idPermisos
                        'idMetodo'       => 1,                              //GET
                        'RutaWeb'        => $RutaWeb.'/updateList',         //Ruta
                        'RutaController' => $RutaController.'->UpdateList', //Controlador
                        'Descripcion'    => 'Actualizar Lista',             //Descripcion
                        'idLevelLimit'   => 2,                              //Editar - Nivel requerido para ingresar
                        'Controller'     => $RutaController,                //Controlador
                    ];
                    $arrRutas[] = [
                        'idPermisos'     => $Response,                //idPermisos
                        'idMetodo'       => 1,                        //GET
                        'RutaWeb'        => $RutaWeb.'/view/@id',     //Ruta
                        'RutaController' => $RutaController.'->View', //Controlador
                        'Descripcion'    => 'Mostrar Detallado',      //Descripcion
                        'idLevelLimit'   => 1,                        //Ver - Nivel requerido para ingresar
                        'Controller'     => $RutaController,          //Controlador
                    ];
                    $arrRutas[] = [
                        'idPermisos'     => $Response,                    //idPermisos
                        'idMetodo'       => 1,                            //GET
                        'RutaWeb'        => $RutaWeb.'/resumen/@id',      //Ruta
                        'RutaController' => $RutaController.'->Resumen',  //Controlador
                        'Descripcion'    => 'Mostrar Resúmen',            //Descripcion
                        'idLevelLimit'   => 2,                            //Editar - Nivel requerido para ingresar
                        'Controller'     => $RutaController,              //Controlador
                    ];
                    $arrRutas[] = [
                        'idPermisos'     => $Response,                          //idPermisos
                        'idMetodo'       => 1,                                  //GET
                        'RutaWeb'        => $RutaWeb.'/resumenUpdate/@id',      //Ruta
                        'RutaController' => $RutaController.'->ResumenUpdate',  //Controlador
                        'Descripcion'    => 'Mostrar información',              //Descripcion
                        'idLevelLimit'   => 2,                                  //Editar - Nivel requerido para ingresar
                        'Controller'     => $RutaController,                    //Controlador
                    ];
                    /************************************************************/
                    /*                         Acciones                         */
                    /************************************************************/
                    $arrRutas[] = [
                        'idPermisos'     => $Response,                  //idPermisos
                        'idMetodo'       => 2,                          //POST
                        'RutaWeb'        => $RutaWeb,                   //Ruta
                        'RutaController' => $RutaController.'->Insert', //Controlador
                        'Descripcion'    => 'Crear Información',        //Descripcion
                        'idLevelLimit'   => 3,                          //Crear - Nivel requerido para ingresar
                        'Controller'     => $RutaController,            //Controlador
                    ];
                    $arrRutas[] = [
                        'idPermisos'     => $Response,                                      //idPermisos
                        'idMetodo'       => 2,                                              //POST
                        'RutaWeb'        => $RutaWeb.'/update',                             //Ruta
                        'RutaController' => $RutaController.'->Update',                     //Controlador
                        'Descripcion'    => 'Editar por post (modificar y subir archivos)', //Descripcion
                        'idLevelLimit'   => 2,                                              //Editar - Nivel requerido para ingresar
                        'Controller'     => $RutaController,                                //Controlador
                    ];
                    $arrRutas[] = [
                        'idPermisos'     => $Response,                    //idPermisos
                        'idMetodo'       => 4,                            //PUT
                        'RutaWeb'        => $RutaWeb.'/delFiles',         //Ruta
                        'RutaController' => $RutaController.'->DelFiles', //Controlador
                        'Descripcion'    => 'Permite eliminar archivos',  //Descripcion
                        'idLevelLimit'   => 2,                            //Editar - Nivel requerido para ingresar
                        'Controller'     => $RutaController,              //Controlador
                    ];
                    $arrRutas[] = [
                        'idPermisos'     => $Response,                  //idPermisos
                        'idMetodo'       => 3,                          //DELETE
                        'RutaWeb'        => $RutaWeb,                   //Ruta
                        'RutaController' => $RutaController.'->Delete', //Controlador
                        'Descripcion'    => 'Borrar dato y archivos',   //Descripcion
                        'idLevelLimit'   => 4,                          //Borrar - Nivel requerido para ingresar
                        'Controller'     => $RutaController,            //Controlador
                    ];
                    /******************************************************************/
                    /******************************************************************/
                    //variables
                    $SubRutaWeb         = $rowData['Carpeta'].'/'.$_POST['RutaWeb'].'/observaciones';
                    $RutaSubController  = $_POST['RutaController'].'Observaciones';
                    /************************************************************/
                    /*                 Observaciones - Fragments                */
                    /************************************************************/
                    $arrRutas[] = [
                        'idPermisos'     => $Response,                  //idPermisos
                        'idMetodo'       => 1,                          //GET
                        'RutaWeb'        => $SubRutaWeb.'/new/@id',     //Ruta
                        'RutaController' => $RutaSubController.'->New', //Controlador
                        'Descripcion'    => 'Mostrar modal nuevo',      //Descripcion
                        'idLevelLimit'   => 2,                          //Editar - Nivel requerido para ingresar
                        'Controller'     => $RutaSubController,         //Controlador
                    ];
                    $arrRutas[] = [
                        'idPermisos'     => $Response,                         //idPermisos
                        'idMetodo'       => 1,                                 //GET
                        'RutaWeb'        => $SubRutaWeb.'/updateList/@id',     //Ruta
                        'RutaController' => $RutaSubController.'->UpdateList', //Controlador
                        'Descripcion'    => 'Actualizar Lista',                //Descripcion
                        'idLevelLimit'   => 2,                                 //Editar - Nivel requerido para ingresar
                        'Controller'     => $RutaSubController,                //Controlador
                    ];
                    $arrRutas[] = [
                        'idPermisos'     => $Response,                    //idPermisos
                        'idMetodo'       => 1,                            //GET
                        'RutaWeb'        => $SubRutaWeb.'/view/@id',      //Ruta
                        'RutaController' => $RutaSubController.'->View',  //Controlador
                        'Descripcion'    => 'Mostrar Detallado',          //Descripcion
                        'idLevelLimit'   => 2,                            //Editar - Nivel requerido para ingresar
                        'Controller'     => $RutaSubController,           //Controlador
                    ];
                    $arrRutas[] = [
                        'idPermisos'     => $Response,                                //idPermisos
                        'idMetodo'       => 1,                                        //GET
                        'RutaWeb'        => $SubRutaWeb.'/getID/@id',                 //Ruta
                        'RutaController' => $RutaSubController.'->GetID',             //Controlador
                        'Descripcion'    => 'Información para el formulario edición', //Descripcion
                        'idLevelLimit'   => 2,                                        //Editar - Nivel requerido para ingresar
                        'Controller'     => $RutaSubController,                       //Controlador
                    ];
                    /************************************************************/
                    /*                  Observaciones - Aciones                 */
                    /************************************************************/
                    $arrRutas[] = [
                        'idPermisos'     => $Response,                     //idPermisos
                        'idMetodo'       => 2,                             //POST
                        'RutaWeb'        => $SubRutaWeb,                   //Ruta
                        'RutaController' => $RutaSubController.'->Insert', //Controlador
                        'Descripcion'    => 'Crear Información',           //Descripcion
                        'idLevelLimit'   => 2,                             //Crear - Nivel requerido para ingresar
                        'Controller'     => $RutaSubController,            //Controlador
                    ];
                    $arrRutas[] = [
                        'idPermisos'     => $Response,                                      //idPermisos
                        'idMetodo'       => 2,                                              //POST
                        'RutaWeb'        => $SubRutaWeb.'/update',                          //Ruta
                        'RutaController' => $RutaSubController.'->Update',                  //Controlador
                        'Descripcion'    => 'Editar por post (modificar y subir archivos)', //Descripcion
                        'idLevelLimit'   => 2,                                              //Editar - Nivel requerido para ingresar
                        'Controller'     => $RutaSubController,                             //Controlador
                    ];
                    $arrRutas[] = [
                        'idPermisos'     => $Response,                     //idPermisos
                        'idMetodo'       => 3,                             //DELETE
                        'RutaWeb'        => $SubRutaWeb,                   //Ruta
                        'RutaController' => $RutaSubController.'->Delete', //Controlador
                        'Descripcion'    => 'Borrar dato y archivos',      //Descripcion
                        'idLevelLimit'   => 2,                             //Borrar - Nivel requerido para ingresar
                        'Controller'     => $RutaSubController,            //Controlador
                    ];
                    /******************************************************************/
                    /******************************************************************/
                    //Recorro los controladores internos extras
                    if(isset($ndata_1)&&$ndata_1!=0){
                        for($j1 = 0; $j1 < $ndata_1; $j1++){
                            /******************************/
                            //variables
                            $SubRutaWeb         = $rowData['Carpeta'].'/'.$_POST['RutaWeb'].'/'.$_POST['SubRuta'][$j1];
                            $RutaSubController  = $_POST['RutaController'].$_POST['Controller'][$j1];
                            /************************************************************/
                            /*                 Observaciones - Fragments                */
                            /************************************************************/
                            $arrRutas[] = [
                                'idPermisos'     => $Response,                  //idPermisos
                                'idMetodo'       => 1,                          //GET
                                'RutaWeb'        => $SubRutaWeb.'/new/@id',     //Ruta
                                'RutaController' => $RutaSubController.'->New', //Controlador
                                'Descripcion'    => 'Mostrar modal nuevo',      //Descripcion
                                'idLevelLimit'   => 2,                          //Editar - Nivel requerido para ingresar
                                'Controller'     => $RutaSubController,         //Controlador
                            ];
                            $arrRutas[] = [
                                'idPermisos'     => $Response,                         //idPermisos
                                'idMetodo'       => 1,                                 //GET
                                'RutaWeb'        => $SubRutaWeb.'/updateList/@id',     //Ruta
                                'RutaController' => $RutaSubController.'->UpdateList', //Controlador
                                'Descripcion'    => 'Actualizar Lista',                //Descripcion
                                'idLevelLimit'   => 2,                                 //Editar - Nivel requerido para ingresar
                                'Controller'     => $RutaSubController,                //Controlador
                            ];
                            $arrRutas[] = [
                                'idPermisos'     => $Response,                    //idPermisos
                                'idMetodo'       => 1,                            //GET
                                'RutaWeb'        => $SubRutaWeb.'/view/@id',      //Ruta
                                'RutaController' => $RutaSubController.'->View',  //Controlador
                                'Descripcion'    => 'Mostrar Detallado',          //Descripcion
                                'idLevelLimit'   => 2,                            //Editar - Nivel requerido para ingresar
                                'Controller'     => $RutaSubController,           //Controlador
                            ];
                            $arrRutas[] = [
                                'idPermisos'     => $Response,                                //idPermisos
                                'idMetodo'       => 1,                                        //GET
                                'RutaWeb'        => $SubRutaWeb.'/getID/@id',                 //Ruta
                                'RutaController' => $RutaSubController.'->GetID',             //Controlador
                                'Descripcion'    => 'Información para el formulario edición', //Descripcion
                                'idLevelLimit'   => 2,                                        //Editar - Nivel requerido para ingresar
                                'Controller'     => $RutaSubController,                       //Controlador
                            ];
                            /************************************************************/
                            /*                  Observaciones - Aciones                 */
                            /************************************************************/
                            $arrRutas[] = [
                                'idPermisos'     => $Response,                     //idPermisos
                                'idMetodo'       => 2,                             //POST
                                'RutaWeb'        => $SubRutaWeb,                   //Ruta
                                'RutaController' => $RutaSubController.'->Insert', //Controlador
                                'Descripcion'    => 'Crear Información',           //Descripcion
                                'idLevelLimit'   => 2,                             //Crear - Nivel requerido para ingresar
                                'Controller'     => $RutaSubController,            //Controlador
                            ];
                            $arrRutas[] = [
                                'idPermisos'     => $Response,                                      //idPermisos
                                'idMetodo'       => 2,                                              //POST
                                'RutaWeb'        => $SubRutaWeb.'/update',                          //Ruta
                                'RutaController' => $RutaSubController.'->Update',                  //Controlador
                                'Descripcion'    => 'Editar por post (modificar y subir archivos)', //Descripcion
                                'idLevelLimit'   => 2,                                              //Editar - Nivel requerido para ingresar
                                'Controller'     => $RutaSubController,                             //Controlador
                            ];
                            $arrRutas[] = [
                                'idPermisos'     => $Response,                     //idPermisos
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
                    /******************************/
                    //Se agrega respuesta
                    $arrRutas = [
                        /************************************************************/
                        /*                        Vistas                            */
                        /************************************************************/
                        [
                            'idPermisos'     => $Response,                    //idPermisos
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
                            'idPermisos'     => $Response,                      //idPermisos
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
                            'idPermisos'     => $Response,                //idPermisos
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

            /******************************/
            //Verifico si existe
            if($arrRutas){
                //recorro
                foreach ($arrRutas as $rutas) {
                    //Se genera la query
                    $query = [
                        'data'      => 'idPermisos,idMetodo,RutaWeb,RutaController,Descripcion,idLevelLimit,Controller',
                        'required'  => '',
                        'unique'    => '',
                        'table'     => 'core_permisos_listado_rutas',
                        'Post'      => $rutas,
                    ];
                    //Ejecuto la query
                    $this->QBuilder->queryInsert($query, $this->DBConn, true);
                }
            }

            /****************************************/
            //Se actualizan los permisos al crear uno nuevo
            $this->updatePermisos($f3);
            /****************************************/
            // Si es un ID numérico, encripta y envía con código 200 (OK)
            $Data = $this->Codification->encryptDecrypt('encrypt', $Response);
            echo Response::sendData(200, $Data);
        } else {
            // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
            // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
            echo Response::sendData(500, $Response);
        }

    }

    /******************************************************************************/
    //Editar por put (solo modificar datos)
    //Editar por post (modificar y subir archivos)
    public function Update($f3){
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            /******************************/
            //Se genera el chequeo
            $DataCheck = $this->dataCheck($_POST);

            /******************************/
            //Se genera la query
            $query = [
                'data'      => 'idPermisosCat,idEstado,idTipo,Nombre,Descripcion,idLevelLimit,RutaWeb,RutaController',
                'required'  => 'idPermisosCat,idEstado,idTipo,Nombre,idLevelLimit,RutaWeb,RutaController',
                'unique'    => 'Nombre,RutaWeb,RutaController',
                'encode'    => '',
                'table'     => 'core_permisos_listado',
                'where'     => 'idPermisos',
                'Post'      => $_POST,
            ];
            //Ejecuto la query
            $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
            $Response = $this->Base_update($xParams);

            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response===true) {
                /****************************************/
                //Se actualizan los permisos al crear uno nuevo
                $this->updatePermisos($f3);
                /****************************************/
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
    //Borrar dato y archivos
    public function Delete($f3){
        //Verificacion metodo PUT
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            //Se parsean los datos
            parse_str(file_get_contents("php://input"),$dataDelete);
            /******************************/
            //Se genera la query
            $query = [
                'files'       => '',
                'table'       => 'core_permisos_listado',
                'where'       => 'idPermisos',
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
                //Se genera la query
                $query = [
                    'files'       => '',
                    'table'       => 'core_permisos_listado_rutas',
                    'where'       => 'idPermisos',
                    'SubCarpeta'  => '',
                    'Post'        => $dataDelete
                ];
                //Ejecuto la query
                $xParams          = ['query' => $query];
                $ResponseDelRutas = $this->Base_delete($xParams);

                /******************************/
                // Se asume que $ResponseDelRutas contendrá un array de errores/datos, un true o algún otro valor.
                if ($ResponseDelRutas===true) {
                    /****************************************/
                    //Se actualizan los permisos al crear uno nuevo
                    $this->updatePermisos($f3);
                    // Devuelvo $Response con código 200 (OK)
                    echo Response::sendData(200, $ResponseDelRutas);
                } else {
                    // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
                    // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
                    echo Response::sendData(500, $ResponseDelRutas);
                }
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
            'ValidarLargoMinimo'        => 'Nombre,Descripcion',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'Nombre',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Nombre,Descripcion',
            'ValidarEspaciosVacios'     => '',
            'ValidarMayusculas'         => '',
            'ValidarCoincidencias'      => '',
            'Post'                      => $POST,
        ];
        //Devuelvo
        return $DataChecking;
    }

    /******************************************************************************/
    //Se validan los datos
    private function updatePermisos($f3){

        /******************************/
        //Se limpian las variables
        $f3->clear('SESSION.arrMenu');
        $f3->clear('SESSION.arrPermisos');
        $f3->clear('SESSION.arrLevel');

        /******************************/
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
            'where'   => 'core_permisos_listado.idEstado =1',
            'group'   => '',
            'having'  => '',
            'order'   => 'core_permisos_categorias.Nombre ASC, core_permisos_listado.Nombre ASC, core_permisos_listado.RutaWeb ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $arrMenu = $this->Base_GetList($xParams);

        /******************************/
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
            'where'   => 'core_permisos_listado.idEstado =1',
            'group'   => '',
            'having'  => '',
            'order'   => 'core_permisos_listado_rutas_metodo.Nombre ASC, core_permisos_listado_rutas.RutaWeb ASC, core_permisos_listado_rutas.RutaController ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams     = ['query' => $query];
        $arrPermisos = $this->Base_GetList($xParams);

        /******************************/
        //se crea variable para los niveles de permisos
        $arrLevel = [];
        //se recorren las variables
        foreach ($arrMenu as $value) {
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
        //Se agrupan los menus
        $arrMenuNew = $this->CommonData->agruparPorClave ($arrMenu, 'PermisosCat' );
        //Seteo las variables
        $f3->set('SESSION.arrMenu', $arrMenuNew);        //Menu
        $f3->set('SESSION.arrPermisos', $arrPermisos);   //Rutas
        $f3->set('SESSION.arrLevel', $arrLevel);         //Niveles de permisos

    }

}