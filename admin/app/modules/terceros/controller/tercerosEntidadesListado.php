<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class tercerosEntidadesListado extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataDate;
    private $WidgetsCommon;
    private $DataNumbers;

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'tercerosEntidadesListado';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->DataDate       = new FunctionsDataDate();
		$this->WidgetsCommon  = new UIWidgetsCommon();
		$this->DataNumbers    = new FunctionsDataNumbers();
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
                core_tipos_entidades.Nombre AS TipoEntidad',
            'table'   => 'entidades_listado',
            'join'    => '
                LEFT JOIN core_estados         ON core_estados.idEstado               = entidades_listado.idEstado
                LEFT JOIN entidades_sectores   ON entidades_sectores.idSector         = entidades_listado.idSector
                LEFT JOIN core_tipos_entidades ON core_tipos_entidades.idTipoEntidad  = entidades_listado.idTipoEntidad',
            'where'   => 'entidades_listado.idTipo = ?',
            'params'  => [2],
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
        if($arrList['status'] && $arrTipoEntidad['status'] && $arrSexo['status'] && $arrCiudad['status'] && $arrComuna['status'] && $arrSector['status'] && $arrEstado['status']){

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Listado Clientes',
                'PageDescription' => 'Listado Clientes.',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => 'Listado Clientes',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_Codification'    => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList['data'],
                'arrTipoEntidad'  => $arrTipoEntidad['data'],
                'arrSexo'         => $arrSexo['data'],
                'arrCiudad'       => $arrCiudad['data'],
                'arrComuna'       => $arrComuna['data'],
                'arrSector'       => $arrSector['data'],
                'arrEstado'       => $arrEstado['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrList,$arrTipoEntidad,$arrSexo,$arrCiudad,$arrComuna,$arrSector,$arrEstado]);
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
        // Verifico si esta vacio
        $whereInt   .= ($whereInt ? ' AND ' : '') . 'entidades_listado.idTipo = ?';
        $whereParams = array_merge($whereParams, [2]);

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
                core_tipos_entidades.Nombre AS TipoEntidad',
            'table'   => 'entidades_listado',
            'join'    => '
                LEFT JOIN core_estados         ON core_estados.idEstado               = entidades_listado.idEstado
                LEFT JOIN entidades_sectores   ON entidades_sectores.idSector         = entidades_listado.idSector
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
                'TableTitle'      => 'Listado Clientes',
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
                entidades_listado.Direccion_img,
                entidades_listado.idTipoEntidad,

                core_sexo.Nombre AS Sexo,
                core_tipos_entidades.Nombre AS TipoEntidad',
            'table'   => 'entidades_listado',
            'join'    => '
                LEFT JOIN core_sexo                ON core_sexo.idSexo                    = entidades_listado.idSexo
                LEFT JOIN core_tipos_entidades     ON core_tipos_entidades.idTipoEntidad  = entidades_listado.idTipoEntidad',
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
        if($arrUserData["entidadesListadoUsoPlanes"]==2){
            /************************************/
            // Se genera la query
            $query = [
                'data'    => '
                    terceros_entidades_listado_planes.idPlan,
                    terceros_entidades_listado_planes.Fecha,
                    terceros_entidades_listado_planes.Monto,
                    servicios_listado.Nombre AS Servicio,
                    core_estados.Nombre AS Estado,
                    core_estados.Color AS EstadoColor',
                'table'   => 'terceros_entidades_listado_planes',
                'join'    => '
                    LEFT JOIN servicios_listado  ON servicios_listado.idServicio  = terceros_entidades_listado_planes.idServicio
                    LEFT JOIN core_estados       ON core_estados.idEstado         = terceros_entidades_listado_planes.idEstado',
                'where'   => 'terceros_entidades_listado_planes.idEntidad = ?',
                'params'  => [$EntidadID['data']],
                'group'   => '',
                'having'  => '',
                'order'   => 'terceros_entidades_listado_planes.Fecha DESC, servicios_listado.Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            // Preparo los datos
            $xParams   = ['query' => $query];
            // Ejecuto la query
            $arrPlanes = $this->Base_GetList($xParams);
        // Si se permite junto con la creacion de tareas
        }else{
            $arrPlanes['status'] = true;
            $arrPlanes['data']   = [];
        }

        /************************************/
        // Se verifica si se tiene el permiso para visualizar el dato
        if($arrUserData["entidadesListadoUsoUsuarios"]==2){
            /************************************/
            // Se genera la query
            $query = [
                'data'    => '
                    terceros_entidades_listado_usuarios.email,
                    terceros_entidades_listado_usuarios.Nombre,
                    terceros_entidades_listado_usuarios.Ultimo_acceso,
                    core_estados.Nombre AS Estado,
                    core_estados.Color AS EstadoColor',
                'table'   => 'terceros_entidades_listado_usuarios',
                'join'    => 'LEFT JOIN core_estados ON core_estados.idEstado = terceros_entidades_listado_usuarios.idEstado',
                'where'   => 'terceros_entidades_listado_usuarios.idEntidad = ?',
                'params'  => [$EntidadID['data']],
                'group'   => '',
                'having'  => '',
                'order'   => 'terceros_entidades_listado_usuarios.email ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            // Preparo los datos
            $xParams     = ['query' => $query];
            // Ejecuto la query
            $arrUsuarios = $this->Base_GetList($xParams);
        // Si se permite junto con la creacion de tareas
        }else{
            $arrUsuarios['status'] = true;
            $arrUsuarios['data']   = [];
        }

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrPlanes['status'] && $arrUsuarios['status']){
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
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
                'arrPlanes'        => $arrPlanes['data'],
                'arrUsuarios'      => $arrUsuarios['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-View.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrPlanes,$arrUsuarios]);
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
                entidades_listado.Direccion_img,
                entidades_listado.idTipoEntidad,

                core_sexo.Nombre AS Sexo,
                core_tipos_entidades.Nombre AS TipoEntidad',
            'table'   => 'entidades_listado',
            'join'    => '
                LEFT JOIN core_sexo                ON core_sexo.idSexo                    = entidades_listado.idSexo
                LEFT JOIN core_tipos_entidades     ON core_tipos_entidades.idTipoEntidad  = entidades_listado.idTipoEntidad',
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
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'        => 'Resumen Clientes',
                'PageDescription'  => 'Resumen Clientes.',
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
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData]);
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
                entidades_listado.idEntidad,
                entidades_listado.Nombre,
                entidades_listado.ApellidoPat,
                entidades_listado.ApellidoMat,
                entidades_listado.RazonSocial,
                entidades_listado.Nick,
                entidades_listado.Direccion_img,
                entidades_listado.idTipoEntidad,

                core_sexo.Nombre AS Sexo,
                core_tipos_entidades.Nombre AS TipoEntidad',
            'table'   => 'entidades_listado',
            'join'    => '
                LEFT JOIN core_sexo                ON core_sexo.idSexo                    = entidades_listado.idSexo
                LEFT JOIN core_tipos_entidades     ON core_tipos_entidades.idTipoEntidad  = entidades_listado.idTipoEntidad',
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
        // Se genera la query
        $query = [
            'data'      => 'idEntidad,idEstado,idSector,idSexo,idTipo,idTipoEntidad,password,Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Rut,idCiudad,idComuna,Direccion,FNacimiento,Email,Fono1,Fono2,Web,Giro,RepLegalNombre,RepLegalRut,RepLegalEmail,RepLegalFono1,RepLegalFono2,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin,IP_Client,Agent_Transp,Ultimo_acceso',
            'required'  => 'idEntidad,idEstado,idTipo,idTipoEntidad',
            'unique'    => 'Rut,Email,Fono1,Fono2',
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
            'ValidarNumero'             => 'Fono1,Fono2,RepLegalFono1,RepLegalFono2',
            'ValidarEntero'             => '',
            'ValidarRut'                => 'Rut,RepLegalRut',
            'ValidarPatente'            => '',
            'ValidarFecha'              => 'FNacimiento',
            'ValidarHora'               => '',
            'ValidarURL'                => 'Web,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin',
            'ValidarLargoMinimo'        => 'Email,RepLegalEmail,Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Direccion,Giro,RepLegalNombre',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'Email,RepLegalEmail,Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Direccion,Giro,RepLegalNombre',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Direccion,Giro,RepLegalNombre',
            'ValidarEspaciosVacios'     => 'Email,RepLegalEmail,Web,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin',
            'ValidarMayusculas'         => 'Email,RepLegalEmail',
            'ValidarCoincidencias'      => '',
            'ValidarDominioEmail'       => 'Email,RepLegalEmail',
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
