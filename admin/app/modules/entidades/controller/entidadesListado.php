<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class entidadesListado extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataDate;
    private $DataNumbers;
    private $WidgetsCommon;

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
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
    /*******************************************************************/
    // Listar
    /*******************************************************************/
    public function listAll($f3){

        /************************************/
        // Se genera la query
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
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'entidades_listado.idEstado ASC, entidades_listado.ApellidoPat ASC, entidades_listado.Nombre ASC, entidades_listado.RazonSocial ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrList = $this->Base_GetList($xParams);

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
        $xParams   = ['query' => $query];
        // Ejecuto la query
        $arrEstado = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idSector AS ID,Nombre',
            'table'   => 'entidades_sectores',
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
        $arrSector = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idSexo AS ID,Nombre',
            'table'   => 'core_sexo',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrSexo = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idTipo AS ID,Nombre',
            'table'   => 'core_tipos_entidad',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrTipo = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idTipoEntidad AS ID,Nombre',
            'table'   => 'core_tipos_entidades',
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
        $arrTipoEntidad = $this->Base_GetList($xParams);

        /************************************/
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
        // Preparo los datos
        $xParams   = ['query' => $query];
        // Ejecuto la query
        $arrCiudad = $this->Base_GetList($xParams);

        /************************************/
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
        // Preparo los datos
        $xParams   = ['query' => $query];
        // Ejecuto la query
        $arrComuna = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($arrList['status'] && $arrEstado['status'] && $arrSector['status'] && $arrSexo['status'] && $arrTipo['status'] && $arrTipoEntidad['status'] && $arrCiudad['status'] && $arrComuna['status']){

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Listado Entidades',
                'PageDescription' => 'Listado Entidades.',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => 'Listado Entidades',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_Codification'    => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList['data'],
                'arrEstado'       => $arrEstado['data'],
                'arrSector'       => $arrSector['data'],
                'arrSexo'         => $arrSexo['data'],
                'arrTipo'         => $arrTipo['data'],
                'arrTipoEntidad'  => $arrTipoEntidad['data'],
                'arrCiudad'       => $arrCiudad['data'],
                'arrComuna'       => $arrComuna['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrList,$arrEstado,$arrSector,$arrSexo,$arrTipo,$arrTipoEntidad,$arrCiudad,$arrComuna]);
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
        $WhereData_int     = 'idEstado,idSector,idSexo,idTipo,idTipoEntidad,idCiudad,idComuna,FNacimiento';  // Datos búsqueda exacta
        $WhereData_string  = 'Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Direccion,Email';              // Datos búsqueda relativa
        $WhereData_between = '';                                                                             // Datos búsqueda Between
        $whereInt          = '';                                                                             // Se crea cadena
        $whereParams       = [];                                                                             // Valores bindeados asociados a $whereInt
        /************************************/
        // Se validan las fechas
        $RespDataBetween = $this->searchValidateDates($WhereData_between);
        if($RespDataBetween!=''){
            Response::error($RespDataBetween, 500);
        }
        /************************************/
        // Agrego variable busqueda
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_int, 'entidades_listado', 1);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_string, 'entidades_listado', 2);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_between, 'entidades_listado', 3);
        $whereInt = $r['where']; $whereParams = $r['params'];

        /************************************/
        // Se genera la query
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
            'where'   => $whereInt,
            'params'  => $whereParams,
            'group'   => '',
            'having'  => '',
            'order'   => 'entidades_listado.idEstado ASC, entidades_listado.ApellidoPat ASC, entidades_listado.Nombre ASC, entidades_listado.RazonSocial ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrList = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrList['status'] === true) {

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'TableTitle'      => 'Listado Entidades',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'    => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-UpdateList.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrList]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Listar
    /*******************************************************************/
    public function export($f3){

        /************************************/
        // Se genera la query
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
                entidades_listado.idTipoEntidad,

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
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'entidades_listado.idEstado ASC, entidades_listado.ApellidoPat ASC, entidades_listado.Nombre ASC, entidades_listado.RazonSocial ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrList = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrList['status'] === true) {

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Exportar Entidades',
                'PageDescription' => 'Exportar Entidades.',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => 'Exportar Entidades',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Exportar.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrList]);
            // Despliegue de errores
            $this->showError(1, $f3, $result);
        }
    }

    /*******************************************************************/
    // View
    /*******************************************************************/
    public function View($f3, $params){

        /************************************/
        // Se instancia
        $arrUserData = $this->getUserData($f3);

        /************************************/
        // Se obtiene el ID
        $EntidadID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($EntidadID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
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
                entidades_listado.idTipo,
                entidades_listado.idTipoEntidad,
                entidades_listado.Latitud,
                entidades_listado.Longitud,

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
            'where'   => 'entidades_listado.idEntidad = ?',
            'params'  => [$EntidadID['data']],
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
        if($arrUserData["entidadesListadoVerCargas"]==2){
            /************************************/
            // Se genera la query
            $query = [
                'data'    => '
                    entidades_listado_cargas.Nombre,
                    entidades_listado_cargas.ApellidoPat,
                    entidades_listado_cargas.ApellidoMat,
                    core_tipos_rrhh_trabajadores_cargas_parentesco.Nombre AS Parentesco',
                'table'   => 'entidades_listado_cargas',
                'join'    => 'LEFT JOIN core_tipos_rrhh_trabajadores_cargas_parentesco   ON core_tipos_rrhh_trabajadores_cargas_parentesco.idParentesco  = entidades_listado_cargas.idParentesco',
                'where'   => 'entidades_listado_cargas.idEntidad = ?',
                'params'  => [$EntidadID['data']],
                'group'   => '',
                'having'  => '',
                'order'   => 'entidades_listado_cargas.ApellidoPat ASC, entidades_listado_cargas.ApellidoMat ASC, entidades_listado_cargas.Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            // Preparo los datos
            $xParams   = ['query' => $query];
            // Ejecuto la query
            $arrCargas = $this->Base_GetList($xParams);
        // Si se permite junto con la creacion de tareas
        }else{
            $arrCargas['status'] = true;
            $arrCargas['data']   = [];
        }

        /************************************/
        // Se verifica si se tiene el permiso para visualizar el dato
        if($arrUserData["entidadesListadoVerContactos"]==2){
            /************************************/
            // Se genera la query
            $query = [
                'data'    => 'Nombre,ApellidoPat,ApellidoMat,Email,Fono1,Fono2',
                'table'   => 'entidades_listado_contactos',
                'join'    => '',
                'where'   => 'idEntidad = ?',
                'params'  => [$EntidadID['data']],
                'group'   => '',
                'having'  => '',
                'order'   => 'ApellidoPat ASC, ApellidoMat ASC, Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            // Preparo los datos
            $xParams      = ['query' => $query];
            // Ejecuto la query
            $arrContactos = $this->Base_GetList($xParams);
        // Si se permite junto con la creacion de tareas
        }else{
            $arrContactos['status'] = true;
            $arrContactos['data']   = [];
        }

        /************************************/
        // Se verifica si se tiene el permiso para visualizar el dato
        if($arrUserData["entidadesListadoVerDocumentos"]==2){
            /************************************/
            // Se genera la query
            $query = [
                'data'    => 'idDocumentos,Nombre,NombreArchivo,FVencimiento',
                'table'   => 'entidades_listado_documentos',
                'join'    => '',
                'where'   => 'idEntidad = ?',
                'params'  => [$EntidadID['data']],
                'group'   => '',
                'having'  => '',
                'order'   => 'Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            // Preparo los datos
            $xParams       = ['query' => $query];
            // Ejecuto la query
            $arrDocumentos = $this->Base_GetList($xParams);
        // Si se permite junto con la creacion de tareas
        }else{
            $arrDocumentos['status'] = true;
            $arrDocumentos['data']   = [];
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                entidades_listado_observaciones.Observacion,
                entidades_listado_observaciones.FechaCreacion,
                usuarios_listado.Nombre AS Usuario',
            'table'   => 'entidades_listado_observaciones',
            'join'    => 'LEFT JOIN usuarios_listado ON usuarios_listado.idUsuario = entidades_listado_observaciones.idUsuario',
            'where'   => 'entidades_listado_observaciones.idEntidad = ?',
            'params'  => [$EntidadID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'entidades_listado_observaciones.idObservaciones ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams          = ['query' => $query];
        // Ejecuto la query
        $arrObservaciones = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrCargas['status'] && $arrContactos['status'] && $arrDocumentos['status'] && $arrObservaciones['status']){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                'Fnc_Codification'     => $this->Codification,
                'Fnc_WidgetsMaps'      => new UIWidgetsMaps(),
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
                'arrCargas'        => $arrCargas['data'],
                'arrContactos'     => $arrContactos['data'],
                'arrDocumentos'    => $arrDocumentos['data'],
                'arrObservaciones' => $arrObservaciones['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-View.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrCargas,$arrContactos,$arrDocumentos,$arrObservaciones]);
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
        $EntidadID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($EntidadID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
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
                entidades_listado.Latitud,
                entidades_listado.Longitud,

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
            'where'   => 'entidades_listado.idEntidad = ?',
            'params'  => [$EntidadID['data']],
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
        $xParams   = ['query' => $query];
        // Ejecuto la query
        $arrEstado = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idSector AS ID,Nombre',
            'table'   => 'entidades_sectores',
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
        $arrSector = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idSexo AS ID,Nombre',
            'table'   => 'core_sexo',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrSexo = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idTipo AS ID,Nombre',
            'table'   => 'core_tipos_entidad',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrTipo = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idTipoEntidad AS ID,Nombre',
            'table'   => 'core_tipos_entidades',
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
        $arrTipoEntidad = $this->Base_GetList($xParams);

        /************************************/
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
        // Preparo los datos
        $xParams   = ['query' => $query];
        // Ejecuto la query
        $arrCiudad = $this->Base_GetList($xParams);

        /************************************/
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
        // Preparo los datos
        $xParams   = ['query' => $query];
        // Ejecuto la query
        $arrComuna = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrEstado['status'] && $arrSector['status'] && $arrSexo['status'] && $arrTipo['status'] && $arrTipoEntidad['status'] && $arrCiudad['status'] && $arrComuna['status']){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'        => 'Resumen Entidades',
                'PageDescription'  => 'Resumen Entidades.',
                'PageAuthor'       => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'     => ConfigAPP::SOFTWARE['SoftwareName'],
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'       => $this->FormInputs,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_Codification'     => $this->Codification,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                'Fnc_WidgetsMaps'      => new UIWidgetsMaps(),
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData['data'],
                'arrEstado'       => $arrEstado['data'],
                'arrSector'       => $arrSector['data'],
                'arrSexo'         => $arrSexo['data'],
                'arrTipo'         => $arrTipo['data'],
                'arrTipoEntidad'  => $arrTipoEntidad['data'],
                'arrCiudad'       => $arrCiudad['data'],
                'arrComuna'       => $arrComuna['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrEstado,$arrSector,$arrSexo,$arrTipo,$arrTipoEntidad,$arrCiudad,$arrComuna]);
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
        $EntidadID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($EntidadID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
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
                entidades_listado.Latitud,
                entidades_listado.Longitud,

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
            'where'   => 'entidades_listado.idEntidad = ?',
            'params'  => [$EntidadID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($rowData['status'] === true) {
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                'Fnc_WidgetsMaps'      => new UIWidgetsMaps(),
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Update.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData]);
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
    public function Insert(){

        /************************************/
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /************************************/
        //Si hay datos
        if(isset($_POST['Direccion'])&&$_POST['Direccion']!=''){
            //Se obtiene la direccion
            $Ubicacion = $_POST['Direccion'];
            //Si existe comuna
            if(isset($_POST['idComuna'])&&$_POST['idComuna']!=''){
                /************************************/
                // Se genera la query
                $query = [
                    'data'    => 'Nombre',
                    'table'   => 'core_ubicacion_comunas',
                    'join'    => '',
                    'where'   => 'idComuna = ?',
                    'params'  => [$_POST['idComuna']],
                    'group'   => '',
                    'having'  => '',
                    'order'   => ''
                ];
                // Preparo los datos
                $xParams = ['query' => $query];
                // Ejecuto la query
                $rowData = $this->Base_GetByID($xParams);
                // Si hay resultados
                if ($rowData['status'] === true) {
                    $Ubicacion .= ', '.$rowData['data']['Nombre'];
                }
            }
            //Si existe ciudad
            if(isset($_POST['idCiudad'])&&$_POST['idCiudad']!=''){
                /************************************/
                // Se genera la query
                $query = [
                    'data'    => 'Nombre',
                    'table'   => 'core_ubicacion_ciudad',
                    'join'    => '',
                    'where'   => 'idCiudad = ?',
                    'params'  => [$_POST['idCiudad']],
                    'group'   => '',
                    'having'  => '',
                    'order'   => ''
                ];
                // Preparo los datos
                $xParams = ['query' => $query];
                // Ejecuto la query
                $rowData = $this->Base_GetByID($xParams);
                // Si hay resultados
                if ($rowData['status'] === true) {
                    $Ubicacion .= ', '.$rowData['data']['Nombre'];
                }
            }
            //Pais
            $Ubicacion .= ', Chile';
            // Se instancia
            $fncLocation = new FunctionsLocation;
            //Se hace la busqueda de lat y long por su direccion
            $result = $fncLocation->geocodeAddress($Ubicacion);
            // Si hay resultados se guarda
            if ($result) {
                //Se guarda el ultimo dato
                $_POST['Latitud']  = $result['lat'];
                $_POST['Longitud'] = $result['lon'];
            }else{
                //Se agregan datos por defecto
                $_POST['Latitud']  = $_SESSION['DataInfo']['Latitud'];
                $_POST['Longitud'] = $_SESSION['DataInfo']['Longitud'];
            }
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idEstado,idSector,idSexo,idTipo,idTipoEntidad,password,Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Rut,idCiudad,idComuna,Direccion,FNacimiento,Email,Fono1,Fono2,Web,Giro,RepLegalNombre,RepLegalRut,RepLegalEmail,RepLegalFono1,RepLegalFono2,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin,IP_Client,Agent_Transp,Ultimo_acceso,Latitud,Longitud',
            'required'  => 'idEstado,idTipo,idTipoEntidad,password',
            'unique'    => 'Rut,Email',
            'encode'    => 'password',
            'table'     => 'entidades_listado',
            'Post'      => $_POST
        ];
        // Preparo los datos
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        // Ejecuto la query
        $Response = $this->Base_insert($xParams);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if ($Response['status'] === false) {
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
        }

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
    public function Update(){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /************************************/
        //Solo si hay datos y se ha cambiado la direccion
        if(isset($_POST['Direccion'], $_POST['OldDireccion'], $_POST['OldLatitud'])&&(($_POST['Direccion']!=''&&$_POST['Direccion']!=$_POST['OldDireccion']) OR ($_POST['OldLatitud']=='xxxxxxx'))){
            //Se obtiene la direccion
            $Ubicacion = $_POST['Direccion'];
            //Si existe comuna
            if(isset($_POST['idComuna'])&&$_POST['idComuna']!=''){
                /************************************/
                // Se genera la query
                $query = [
                    'data'    => 'Nombre',
                    'table'   => 'core_ubicacion_comunas',
                    'join'    => '',
                    'where'   => 'idComuna = ?',
                    'params'  => [$_POST['idComuna']],
                    'group'   => '',
                    'having'  => '',
                    'order'   => ''
                ];
                // Preparo los datos
                $xParams = ['query' => $query];
                // Ejecuto la query
                $rowData = $this->Base_GetByID($xParams);
                // Si hay resultados
                if ($rowData['status'] === true) {
                    $Ubicacion .= ', '.$rowData['data']['Nombre'];
                }
            }
            //Si existe ciudad
            if(isset($_POST['idCiudad'])&&$_POST['idCiudad']!=''){
                /************************************/
                // Se genera la query
                $query = [
                    'data'    => 'Nombre',
                    'table'   => 'core_ubicacion_ciudad',
                    'join'    => '',
                    'where'   => 'idCiudad = ?',
                    'params'  => [$_POST['idCiudad']],
                    'group'   => '',
                    'having'  => '',
                    'order'   => ''
                ];
                // Preparo los datos
                $xParams = ['query' => $query];
                // Ejecuto la query
                $rowData = $this->Base_GetByID($xParams);
                // Si hay resultados
                if ($rowData['status'] === true) {
                    $Ubicacion .= ', '.$rowData['data']['Nombre'];
                }
            }
            //Pais
            $Ubicacion .= ', Chile';
            // Se instancia
            $fncLocation = new FunctionsLocation;
            //Se hace la busqueda de lat y long por su direccion
            $result = $fncLocation->geocodeAddress($Ubicacion);
            // Si hay resultados se guarda
            if ($result) {
                //Se guarda el ultimo dato
                $_POST['Latitud']  = $result['lat'];
                $_POST['Longitud'] = $result['lon'];
            }else{
                //Se agregan datos por defecto
                $_POST['Latitud']  = $_SESSION['DataInfo']['Latitud'];
                $_POST['Longitud'] = $_SESSION['DataInfo']['Longitud'];
            }

        }

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idEntidad,idEstado,idSector,idSexo,idTipo,idTipoEntidad,password,Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Rut,idCiudad,idComuna,Direccion,FNacimiento,Email,Fono1,Fono2,Web,Giro,RepLegalNombre,RepLegalRut,RepLegalEmail,RepLegalFono1,RepLegalFono2,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin,IP_Client,Agent_Transp,Ultimo_acceso,Latitud,Longitud',
            'required'  => 'idEntidad,idEstado,idTipo,idTipoEntidad',
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
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data']);

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
            'files'       => 'Direccion_img',
            'table'       => 'entidades_listado',
            'where'       => 'idEntidad',
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
        $arrTableDel[] = ['files' => '',              'table' => 'entidades_listado_cargas'];
        $arrTableDel[] = ['files' => '',              'table' => 'entidades_listado_contactos'];
        $arrTableDel[] = ['files' => 'NombreArchivo', 'table' => 'entidades_listado_documentos'];
        $arrTableDel[] = ['files' => '',              'table' => 'entidades_listado_observaciones'];

        /************************************/
        // Verifico si existe
        if (!empty($arrTableDel)) {
            // Recorro
            foreach ($arrTableDel as $tblDel) {
                /************************************/
                // Se genera la query
                $query = ['files' => $tblDel['files'], 'table' => $tblDel['table'], 'where' => 'idEntidad', 'SubCarpeta' => '', 'Post' => $dataDelete];
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

    /*******************************************************************/
    // Borrar archivos
    /*******************************************************************/
    public function delFiles(){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Obtener datos
        parse_str(file_get_contents("php://input"),$dataPut);

        /************************************/
        // Se genera la query
        $query = [
            'files'       => 'Direccion_img',
            'table'       => 'entidades_listado',
            'where'       => 'idEntidad',
            'SubCarpeta'  => '',
            'Post'        => $dataPut
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $Response = $this->Base_delFiles($xParams);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if ($Response['status'] === false) {
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
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
    private function dataCheck($POST){
        // Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => 'Email,RepLegalEmail',
            'ValidarNumero'             => 'idEstado,idSector,idSexo,idTipo,idTipoEntidad,idCiudad,idComuna,Fono1,Fono2,RepLegalFono1,RepLegalFono2',
            'ValidarEntero'             => 'idEstado,idSector,idSexo,idTipo,idTipoEntidad,idCiudad,idComuna',
            'ValidarRut'                => 'Rut,RepLegalRut',
            'ValidarPatente'            => '',
            'ValidarFecha'              => 'FNacimiento',
            'ValidarHora'               => '',
            'ValidarURL'                => 'Web,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin',
            'ValidarLargoMinimo'        => 'Email,RepLegalEmail,Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Direccion,Giro,RepLegalNombre,password',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'Email,RepLegalEmail,Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Direccion,Giro,RepLegalNombre,password',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Direccion,Giro,RepLegalNombre',
            'ValidarEspaciosVacios'     => 'Email,RepLegalEmail,Web,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin,password',
            'ValidarMayusculas'         => 'Email,RepLegalEmail',
            'ValidarCoincidencias'      => '',
            'ValidarDominioEmail'       => 'Email,RepLegalEmail',
            'ValidarPasswordSegura'     => '',
            'ValidarFechaRango'         => 'FNacimiento',
            'ValidarEdadMinima'         => '',
            'ValidarJSON'               => '',
            'ValidarUUID'               => '',
            'ValidarIP'                 => 'IP_Client',
            'ValidarSoloAlfanumerico'   => '',
            'ValidarSoloLetras'         => '',
            'Post'                      => $POST,
        ];
        // Retorno los datos
        return $DataChecking;
    }

}
