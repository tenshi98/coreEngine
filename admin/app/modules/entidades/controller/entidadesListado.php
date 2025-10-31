<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class entidadesListado extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataDate;
    private $DataNumbers;
    private $WidgetsCommon;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'entidadesListado';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->DataDate       = new FunctionsDataDate();
		$this->DataNumbers    = new FunctionsDataNumbers();
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
                entidades_listado.idEntidad,
                entidades_listado.idTipoEntidad,
                entidades_listado.Nombre,
                entidades_listado.ApellidoPat,
                entidades_listado.ApellidoMat,
                entidades_listado.RazonSocial,
                entidades_listado.Nick,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor,
                entidades_sectores.Nombre AS Sector,
                core_tipos_entidad.Nombre AS Tipo,
                core_tipos_entidades.Nombre AS TipoEntidad',
            'table'   => 'entidades_listado',
            'join'    => '
                LEFT JOIN core_estados         ON core_estados.idEstado               = entidades_listado.idEstado
                LEFT JOIN entidades_sectores   ON entidades_sectores.idSector         = entidades_listado.idSector
                LEFT JOIN core_tipos_entidad   ON core_tipos_entidad.idTipo           = entidades_listado.idTipo
                LEFT JOIN core_tipos_entidades ON core_tipos_entidades.idTipoEntidad  = entidades_listado.idTipoEntidad',
            'where'   => 'entidades_listado.idEntidad!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'entidades_listado.idEstado ASC, entidades_listado.ApellidoPat ASC, entidades_listado.Nombre ASC, entidades_listado.RazonSocial ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $arrList = $this->Base_GetList($xParams);

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
        $xParams   = ['query' => $query];
        $arrEstado = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idSector AS ID,Nombre',
            'table'   => 'entidades_sectores',
            'join'    => '',
            'where'   => 'idSector!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrSector = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idSexo AS ID,Nombre',
            'table'   => 'core_sexo',
            'join'    => '',
            'where'   => 'idSexo!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $arrSexo = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idTipo AS ID,Nombre',
            'table'   => 'core_tipos_entidad',
            'join'    => '',
            'where'   => 'idTipo!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $arrTipo = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idTipoEntidad AS ID,Nombre',
            'table'   => 'core_tipos_entidades',
            'join'    => '',
            'where'   => 'idTipoEntidad!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams        = ['query' => $query];
        $arrTipoEntidad = $this->Base_GetList($xParams);

        /******************************/
        //Se genera la query
        $query = [
            'data'    => 'idCiudad AS ID,Nombre',
            'table'   => 'core_ubicacion_ciudad',
            'join'    => '',
            'where'   => '',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrCiudad = $this->Base_GetList($xParams);

        /******************************/
        //Se genera la query
        $query = [
            'data'    => 'idComuna AS ID1, idCiudad AS ID2, Nombre',
            'table'   => 'core_ubicacion_comunas',
            'join'    => '',
            'where'   => '',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrComuna = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($arrList)){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Listado Entidades',
                'PageDescription' => 'Listado Entidades.',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => 'Listado Entidades',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_Codification'    => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList,
                'arrEstado'       => $arrEstado,
                'arrSector'       => $arrSector,
                'arrSexo'         => $arrSexo,
                'arrTipo'         => $arrTipo,
                'arrTipoEntidad'  => $arrTipoEntidad,
                'arrCiudad'       => $arrCiudad,
                'arrComuna'       => $arrComuna,
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

        /*******************************************************************/
        //Variables
        $WhereData_int     = 'idEstado,idSector,idSexo,idTipo,idTipoEntidad,idCiudad,idComuna,FNacimiento';  //Datos búsqueda exacta
        $WhereData_string  = 'Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Direccion,Email';              //Datos búsqueda relativa
        $WhereData_between = '';                                                                             //Datos búsqueda Between
        $whereInt          = '';                                                                             //se crea cadena
        /******************************************/
        //agrego variable busqueda
        $whereInt = $this->searchWhere($whereInt, $WhereData_int, 'entidades_listado', 1);
        $whereInt = $this->searchWhere($whereInt, $WhereData_string, 'entidades_listado', 2);
        $whereInt = $this->searchWhere($whereInt, $WhereData_between, 'entidades_listado', 3);
        //Verifico si esta vacio
        $whereInt2 = $whereInt ? $whereInt . ' AND entidades_listado.idEntidad!=0' : 'entidades_listado.idEntidad!=0';

        /******************************/
        //Se genera la query
        $query = [
            'data'    => '
                entidades_listado.idEntidad,
                entidades_listado.idTipoEntidad,
                entidades_listado.Nombre,
                entidades_listado.ApellidoPat,
                entidades_listado.ApellidoMat,
                entidades_listado.RazonSocial,
                entidades_listado.Nick,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor,
                entidades_sectores.Nombre AS Sector,
                core_tipos_entidad.Nombre AS Tipo,
                core_tipos_entidades.Nombre AS TipoEntidad',
            'table'   => 'entidades_listado',
            'join'    => '
                LEFT JOIN core_estados         ON core_estados.idEstado               = entidades_listado.idEstado
                LEFT JOIN entidades_sectores   ON entidades_sectores.idSector         = entidades_listado.idSector
                LEFT JOIN core_tipos_entidad   ON core_tipos_entidad.idTipo           = entidades_listado.idTipo
                LEFT JOIN core_tipos_entidades ON core_tipos_entidades.idTipoEntidad  = entidades_listado.idTipoEntidad',
            'where'   => $whereInt2,
            'group'   => '',
            'having'  => '',
            'order'   => 'entidades_listado.idEstado ASC, entidades_listado.ApellidoPat ASC, entidades_listado.Nombre ASC, entidades_listado.RazonSocial ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $arrList = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($arrList)){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'TableTitle'      => 'Listado Entidades',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'    => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList,
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
    //Listar Todo
    public function export($f3){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => '
                entidades_listado.Nombre,
                entidades_listado.ApellidoPat,
                entidades_listado.ApellidoMat,
                entidades_listado.RazonSocial,
                entidades_listado.Nick,
                entidades_listado.Rut,
                entidades_listado.Direccion,
                entidades_listado.FNacimiento,
                entidades_listado.Email,
                entidades_listado.Fono1,
                entidades_listado.Fono2,
                entidades_listado.Web,
                entidades_listado.Giro,
                entidades_listado.RepLegalNombre,
                entidades_listado.RepLegalRut,
                entidades_listado.RepLegalEmail,
                entidades_listado.RepLegalFono1,
                entidades_listado.RepLegalFono2,
                entidades_listado.Social_X,
                entidades_listado.Social_Facebook,
                entidades_listado.Social_Instagram,
                entidades_listado.Social_Linkedin,

                core_estados.Nombre AS Estado,
                entidades_sectores.Nombre AS Sector,
                core_sexo.Nombre AS Sexo,
                core_tipos_entidad.Nombre AS Tipo,
                core_tipos_entidades.Nombre AS TipoEntidad,
                core_ubicacion_ciudad.Nombre AS Ciudad,
                core_ubicacion_comunas.Nombre AS Comuna',
            'table'   => 'entidades_listado',
            'join'    => '
                LEFT JOIN core_estados           ON core_estados.idEstado               = entidades_listado.idEstado
                LEFT JOIN entidades_sectores     ON entidades_sectores.idSector         = entidades_listado.idSector
                LEFT JOIN core_sexo              ON core_sexo.idSexo                    = entidades_listado.idSexo
                LEFT JOIN core_tipos_entidad     ON core_tipos_entidad.idTipo           = entidades_listado.idTipo
                LEFT JOIN core_tipos_entidades   ON core_tipos_entidades.idTipoEntidad  = entidades_listado.idTipoEntidad
                LEFT JOIN core_ubicacion_ciudad  ON core_ubicacion_ciudad.idCiudad      = entidades_listado.idCiudad
                LEFT JOIN core_ubicacion_comunas ON core_ubicacion_comunas.idComuna     = entidades_listado.idComuna
                ',
            'where'   => 'entidades_listado.idEntidad!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'entidades_listado.idEstado ASC, entidades_listado.ApellidoPat ASC, entidades_listado.Nombre ASC, entidades_listado.RazonSocial ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $arrList = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($arrList)){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Exportar Entidades',
                'PageDescription' => 'Exportar Entidades.',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => 'Exportar Entidades',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Exportar.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 1, $f3);
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
                entidades_listado.Nombre,
                entidades_listado.ApellidoPat,
                entidades_listado.ApellidoMat,
                entidades_listado.RazonSocial,
                entidades_listado.Nick,
                entidades_listado.Rut,
                entidades_listado.Direccion,
                entidades_listado.Direccion_img,
                entidades_listado.FNacimiento,
                entidades_listado.Email,
                entidades_listado.Fono1,
                entidades_listado.Fono2,
                entidades_listado.Web,
                entidades_listado.Giro,
                entidades_listado.RepLegalNombre,
                entidades_listado.RepLegalRut,
                entidades_listado.RepLegalEmail,
                entidades_listado.RepLegalFono1,
                entidades_listado.RepLegalFono2,
                entidades_listado.Social_X,
                entidades_listado.Social_Facebook,
                entidades_listado.Social_Instagram,
                entidades_listado.Social_Linkedin,
                entidades_listado.Ultimo_acceso,
                entidades_listado.idTipoEntidad,

                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor,
                entidades_sectores.Nombre AS Sector,
                core_sexo.Nombre AS Sexo,
                core_tipos_entidad.Nombre AS Tipo,
                core_tipos_entidades.Nombre AS TipoEntidad,
                core_ubicacion_ciudad.Nombre AS Ciudad,
                core_ubicacion_comunas.Nombre AS Comuna',
            'table'   => 'entidades_listado',
            'join'    => '
                LEFT JOIN core_estados             ON core_estados.idEstado               = entidades_listado.idEstado
                LEFT JOIN entidades_sectores       ON entidades_sectores.idSector         = entidades_listado.idSector
                LEFT JOIN core_sexo                ON core_sexo.idSexo                    = entidades_listado.idSexo
                LEFT JOIN core_tipos_entidad       ON core_tipos_entidad.idTipo           = entidades_listado.idTipo
                LEFT JOIN core_tipos_entidades     ON core_tipos_entidades.idTipoEntidad  = entidades_listado.idTipoEntidad
                LEFT JOIN core_ubicacion_ciudad    ON core_ubicacion_ciudad.idCiudad      = entidades_listado.idCiudad
                LEFT JOIN core_ubicacion_comunas   ON core_ubicacion_comunas.idComuna     = entidades_listado.idComuna',
            'where'   => 'entidades_listado.idEntidad = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        //Se verifica si se permite Administrar Tableros Independiente de las Tareas
        if($UserData["entidadesListadoVerCargas"]==2){
            //Se genera la query
            $query = [
                'data'    => '
                    entidades_listado_cargas.Nombre,
                    entidades_listado_cargas.ApellidoPat,
                    entidades_listado_cargas.ApellidoMat,
                    core_tipos_rrhh_trabajadores_cargas_parentesco.Nombre AS Parentesco',
                'table'   => 'entidades_listado_cargas',
                'join'    => 'LEFT JOIN core_tipos_rrhh_trabajadores_cargas_parentesco   ON core_tipos_rrhh_trabajadores_cargas_parentesco.idParentesco     = entidades_listado.idParentesco',
                'where'   => 'entidades_listado_cargas.idEntidad = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
                'group'   => '',
                'having'  => '',
                'order'   => 'entidades_listado_cargas.ApellidoPat ASC, entidades_listado_cargas.ApellidoMat ASC, entidades_listado_cargas.Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            //Ejecuto la query
            $xParams   = ['query' => $query];
            $arrCargas = $this->Base_GetList($xParams);
        //Si se permite junto con la creacion de tareas
        }else{
            $arrCargas   = [];
        }

        /*******************************************************************/
        //Se verifica si se permite Administrar Tableros Independiente de las Tareas
        if($UserData["entidadesListadoVerContactos"]==2){
            //Se genera la query
            $query = [
                'data'    => 'Nombre,ApellidoPat,ApellidoMat,Email,Fono1,Fono2',
                'table'   => 'entidades_listado_contactos',
                'join'    => '',
                'where'   => 'idEntidad = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
                'group'   => '',
                'having'  => '',
                'order'   => 'ApellidoPat ASC, ApellidoMat ASC, Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            //Ejecuto la query
            $xParams      = ['query' => $query];
            $arrContactos = $this->Base_GetList($xParams);
        //Si se permite junto con la creacion de tareas
        }else{
            $arrContactos   = [];
        }

        /*******************************************************************/
        //Se verifica si se permite Administrar Tableros Independiente de las Tareas
        if($UserData["entidadesListadoVerDocumentos"]==2){
            //Se genera la query
            $query = [
                'data'    => 'Nombre,NombreArchivo,FVencimiento',
                'table'   => 'entidades_listado_documentos',
                'join'    => '',
                'where'   => 'idEntidad = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
                'group'   => '',
                'having'  => '',
                'order'   => 'Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            //Ejecuto la query
            $xParams       = ['query' => $query];
            $arrDocumentos = $this->Base_GetList($xParams);
        //Si se permite junto con la creacion de tareas
        }else{
            $arrDocumentos   = [];
        }

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'FechaCreacion,Observacion',
            'table'   => 'entidades_listado_observaciones',
            'join'    => '',
            'where'   => 'idEntidad = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'idObservaciones ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams          = ['query' => $query];
        $arrObservaciones = $this->Base_GetList($xParams);

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
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData,
                'arrCargas'        => $arrCargas,
                'arrContactos'     => $arrContactos,
                'arrDocumentos'    => $arrDocumentos,
                'arrObservaciones' => $arrObservaciones,
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
                entidades_listado.idEntidad,
                entidades_listado.Nombre,
                entidades_listado.ApellidoPat,
                entidades_listado.ApellidoMat,
                entidades_listado.RazonSocial,
                entidades_listado.Nick,
                entidades_listado.Rut,
                entidades_listado.Direccion,
                entidades_listado.Direccion_img,
                entidades_listado.FNacimiento,
                entidades_listado.Email,
                entidades_listado.Fono1,
                entidades_listado.Fono2,
                entidades_listado.Web,
                entidades_listado.Giro,
                entidades_listado.RepLegalNombre,
                entidades_listado.RepLegalRut,
                entidades_listado.RepLegalEmail,
                entidades_listado.RepLegalFono1,
                entidades_listado.RepLegalFono2,
                entidades_listado.Social_X,
                entidades_listado.Social_Facebook,
                entidades_listado.Social_Instagram,
                entidades_listado.Social_Linkedin,
                entidades_listado.Ultimo_acceso,
                entidades_listado.idEstado,
                entidades_listado.idSector,
                entidades_listado.idSexo,
                entidades_listado.idTipo,
                entidades_listado.idTipoEntidad,
                entidades_listado.idCiudad,
                entidades_listado.idComuna,

                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor,
                entidades_sectores.Nombre AS Sector,
                core_sexo.Nombre AS Sexo,
                core_tipos_entidad.Nombre AS Tipo,
                core_tipos_entidades.Nombre AS TipoEntidad,
                core_ubicacion_ciudad.Nombre AS Ciudad,
                core_ubicacion_comunas.Nombre AS Comuna',
            'table'   => 'entidades_listado',
            'join'    => '
                LEFT JOIN core_estados             ON core_estados.idEstado               = entidades_listado.idEstado
                LEFT JOIN entidades_sectores       ON entidades_sectores.idSector         = entidades_listado.idSector
                LEFT JOIN core_sexo                ON core_sexo.idSexo                    = entidades_listado.idSexo
                LEFT JOIN core_tipos_entidad       ON core_tipos_entidad.idTipo           = entidades_listado.idTipo
                LEFT JOIN core_tipos_entidades     ON core_tipos_entidades.idTipoEntidad  = entidades_listado.idTipoEntidad
                LEFT JOIN core_ubicacion_ciudad    ON core_ubicacion_ciudad.idCiudad      = entidades_listado.idCiudad
                LEFT JOIN core_ubicacion_comunas   ON core_ubicacion_comunas.idComuna     = entidades_listado.idComuna',
            'where'   => 'entidades_listado.idEntidad = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
        $xParams   = ['query' => $query];
        $arrEstado = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idSector AS ID,Nombre',
            'table'   => 'entidades_sectores',
            'join'    => '',
            'where'   => 'idSector!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrSector = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idSexo AS ID,Nombre',
            'table'   => 'core_sexo',
            'join'    => '',
            'where'   => 'idSexo!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $arrSexo = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idTipo AS ID,Nombre',
            'table'   => 'core_tipos_entidad',
            'join'    => '',
            'where'   => 'idTipo!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $arrTipo = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idTipoEntidad AS ID,Nombre',
            'table'   => 'core_tipos_entidades',
            'join'    => '',
            'where'   => 'idTipoEntidad!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams        = ['query' => $query];
        $arrTipoEntidad = $this->Base_GetList($xParams);

        /******************************/
        //Se genera la query
        $query = [
            'data'    => 'idCiudad AS ID,Nombre',
            'table'   => 'core_ubicacion_ciudad',
            'join'    => '',
            'where'   => '',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrCiudad = $this->Base_GetList($xParams);

        /******************************/
        //Se genera la query
        $query = [
            'data'    => 'idComuna AS ID1, idCiudad AS ID2, Nombre',
            'table'   => 'core_ubicacion_comunas',
            'join'    => '',
            'where'   => '',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrComuna = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if ($rowData!==false) {
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'        => 'Resumen Entidades',
                'PageDescription'  => 'Resumen Entidades.',
                'PageAuthor'       => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'     => ConfigAPP::SOFTWARE['SoftwareName'],
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'       => $this->FormInputs,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_Codification'     => $this->Codification,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData,
                'arrEstado'       => $arrEstado,
                'arrSector'       => $arrSector,
                'arrSexo'         => $arrSexo,
                'arrTipo'         => $arrTipo,
                'arrTipoEntidad'  => $arrTipoEntidad,
                'arrCiudad'       => $arrCiudad,
                'arrComuna'       => $arrComuna,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen.php');
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
                entidades_listado.Nombre,
                entidades_listado.ApellidoPat,
                entidades_listado.ApellidoMat,
                entidades_listado.RazonSocial,
                entidades_listado.Nick,
                entidades_listado.Rut,
                entidades_listado.Direccion,
                entidades_listado.Direccion_img,
                entidades_listado.FNacimiento,
                entidades_listado.Email,
                entidades_listado.Fono1,
                entidades_listado.Fono2,
                entidades_listado.Web,
                entidades_listado.Giro,
                entidades_listado.RepLegalNombre,
                entidades_listado.RepLegalRut,
                entidades_listado.RepLegalEmail,
                entidades_listado.RepLegalFono1,
                entidades_listado.RepLegalFono2,
                entidades_listado.Social_X,
                entidades_listado.Social_Facebook,
                entidades_listado.Social_Instagram,
                entidades_listado.Social_Linkedin,
                entidades_listado.Ultimo_acceso,
                entidades_listado.idTipoEntidad,

                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor,
                entidades_sectores.Nombre AS Sector,
                core_sexo.Nombre AS Sexo,
                core_tipos_entidad.Nombre AS Tipo,
                core_tipos_entidades.Nombre AS TipoEntidad,
                core_ubicacion_ciudad.Nombre AS Ciudad,
                core_ubicacion_comunas.Nombre AS Comuna',
            'table'   => 'entidades_listado',
            'join'    => '
                LEFT JOIN core_estados             ON core_estados.idEstado               = entidades_listado.idEstado
                LEFT JOIN entidades_sectores       ON entidades_sectores.idSector         = entidades_listado.idSector
                LEFT JOIN core_sexo                ON core_sexo.idSexo                    = entidades_listado.idSexo
                LEFT JOIN core_tipos_entidad       ON core_tipos_entidad.idTipo           = entidades_listado.idTipo
                LEFT JOIN core_tipos_entidades     ON core_tipos_entidades.idTipoEntidad  = entidades_listado.idTipoEntidad
                LEFT JOIN core_ubicacion_ciudad    ON core_ubicacion_ciudad.idCiudad      = entidades_listado.idCiudad
                LEFT JOIN core_ubicacion_comunas   ON core_ubicacion_comunas.idComuna     = entidades_listado.idComuna',
            'where'   => 'entidades_listado.idEntidad = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

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
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Update.php');
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

        /******************************/
        //Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /******************************/
        //Se genera la query
        $query = [
            'data'      => 'idEstado,idSector,idSexo,idTipo,idTipoEntidad,password,Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Rut,idCiudad,idComuna,Direccion,FNacimiento,Email,Fono1,Fono2,Web,Giro,RepLegalNombre,RepLegalRut,RepLegalEmail,RepLegalFono1,RepLegalFono2,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin,IP_Client,Agent_Transp,Ultimo_acceso',
            'required'  => 'idEstado,idTipo,idTipoEntidad,password',
            'unique'    => 'Rut,Email',
            'encode'    => 'password',
            'table'     => 'entidades_listado',
            'Post'      => $_POST
        ];
        //Ejecuto la query
        //Ejecuto la query
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        $Response = $this->Base_insert($xParams);

        /******************************/
        // Se asume que $Response contendrá un array de errores/datos, un ID numérico o algún otro valor.
        if (is_numeric($Response)) {
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
    public function Update(){
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            /******************************/
            //Se genera el chequeo
            $DataCheck = $this->dataCheck($_POST);

            /******************************/
            //Se genera la query
            $query = [
                'data'      => 'idEntidad,idEstado,idSector,idSexo,idTipo,idTipoEntidad,password,Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Rut,idCiudad,idComuna,Direccion,FNacimiento,Email,Fono1,Fono2,Web,Giro,RepLegalNombre,RepLegalRut,RepLegalEmail,RepLegalFono1,RepLegalFono2,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin,IP_Client,Agent_Transp,Ultimo_acceso',
                'required'  => 'idEstado,idTipo,idTipoEntidad',
                'unique'    => 'Rut,Email',
                'encode'    => 'password',
                'table'     => 'entidades_listado',
                'where'     => 'idEntidad',
                'Post'      => $_POST,
                'files'     => [
                    [
                        'Identificador' => 'Direccion_img',
                        'SubCarpeta'    => '',
                        'NombreArchivo' => '',
                        'SufijoArchivo' => 'EntidadIMG_',
                        'ValidarTipo'   => 'image',
                        'ValidarPeso'   => 10,
                        'Base64'        => true
                    ],
                ]
            ];
            //Ejecuto la query
            $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
            $Response = $this->Base_update($xParams);

            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response===true) {
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
    public function Delete(){
        //Verificacion metodo PUT
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            //Se parsean los datos
            parse_str(file_get_contents("php://input"),$dataDelete);
            /******************************/
            //Se genera la query
            $query = [
                'files'       => 'Direccion_img',
                'table'       => 'entidades_listado',
                'where'       => 'idEntidad',
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
                $arrTableDel[] = ['files' => '',              'table' => 'entidades_listado_cargas'];
                $arrTableDel[] = ['files' => '',              'table' => 'entidades_listado_contactos'];
                $arrTableDel[] = ['files' => 'NombreArchivo', 'table' => 'entidades_listado_documentos'];
                $arrTableDel[] = ['files' => '',              'table' => 'entidades_listado_observaciones'];

                /************************************************/
                //Verifico si existe
                if($arrTableDel){
                    //recorro
                    foreach ($arrTableDel as $tblDel) {
                        //Se genera la query
                        $query = ['files' => $tblDel['files'], 'table' => $tblDel['table'], 'where' => 'idEntidad', 'SubCarpeta' => '', 'Post' => $dataDelete];
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
    //Permite eliminar archivos
    public function delFiles(){
        //Verificacion metodo PUT
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            //Se parsean los datos
            parse_str(file_get_contents("php://input"),$dataPut);
            /******************************/
            //Se genera la query
            $query = [
                'files'       => 'Direccion_img',
                'table'       => 'entidades_listado',
                'where'       => 'idEntidad',
                'SubCarpeta'  => '',
                'Post'        => $dataPut
            ];
            //Ejecuto la query
            $xParams  = ['query' => $query];
            $Response = $this->Base_delFiles($xParams);
            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response===true) {
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
            'ValidarEmail'              => 'Email,RepLegalEmail',
            'ValidarNumero'             => 'Fono1,Fono2,RepLegalFono1,RepLegalFono2',
            'ValidarEntero'             => '',
            'ValidarRut'                => 'Rut,RepLegalRut',
            'ValidarPatente'            => '',
            'ValidarFecha'              => 'FNacimiento',
            'ValidarHora'               => '',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => 'Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Direccion,Giro,RepLegalNombre',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Direccion,Giro,RepLegalNombre',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Direccion,Giro,RepLegalNombre',
            'ValidarEspaciosVacios'     => 'Web,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin,Email,RepLegalEmail',
            'ValidarMayusculas'         => 'Email,RepLegalEmail',
            'ValidarCoincidencias'      => '',
            'Post'                      => $POST,
        ];
        //Devuelvo
        return $DataChecking;
    }

}