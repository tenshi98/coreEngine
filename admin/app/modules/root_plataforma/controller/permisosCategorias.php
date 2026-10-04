<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class permisosCategorias extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $Codification;
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
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
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
        $arrCategoria = $this->getDataList('core_permisos_categorias.idPermisosCat!=0');

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idColor AS ID,Nombre',
            'table'   => 'core_iconos_colores',
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

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($arrCategoria['status'] && $arrColores['status']){

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Listado de Categorias',
                'PageDescription' => 'Listado de las Categorias de los permisos.',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => 'Listado de Categorias',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_Codification'    => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrCategoria'    => $arrCategoria['data'],
                'arrColores'      => $arrColores['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/permisosCategorias-List.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrCategoria,$arrColores]);
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
        $WhereData_int     = 'IdIconColor';          // Datos búsqueda exacta
        $WhereData_string  = 'Nombre,Icon,Carpeta';  // Datos búsqueda relativa
        $WhereData_between = '';                     // Datos búsqueda Between
        $whereInt          = '';                     // Se crea cadena
        $whereParams       = [];                     // Valores bindeados asociados a $whereInt
        /************************************/
        // Se validan las fechas
        $RespDataBetween = $this->searchValidateDates($WhereData_between);
        if($RespDataBetween!=''){
            Response::error($RespDataBetween, 500);
        }
        /************************************/
        // Agrego variable busqueda
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_int, 'core_permisos_categorias', 1);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_string, 'core_permisos_categorias', 2);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_between, 'core_permisos_categorias', 3);
        $whereInt = $r['where']; $whereParams = $r['params'];

        /************************************/
        // Se genera la query
        $arrCategoria = $this->getDataList($whereInt, $whereParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idColor AS ID,Nombre',
            'table'   => 'core_iconos_colores',
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

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($arrCategoria['status'] && $arrColores['status']){

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'TableTitle'      => 'Listado de Categorias',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'    => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrCategoria'    => $arrCategoria['data'],
                'arrColores'      => $arrColores['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/permisosCategorias-UpdateList.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrCategoria,$arrColores]);
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
        $PermisosCatID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($PermisosCatID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                core_permisos_categorias.idPermisosCat,
                core_permisos_categorias.Nombre,
                core_permisos_categorias.Icon,
                core_permisos_categorias.Descripcion,
                core_permisos_categorias.Carpeta,
                core_iconos_colores.Nombre AS IconColor',
            'table'   => 'core_permisos_categorias',
            'join'    => 'LEFT JOIN core_iconos_colores ON core_iconos_colores.idColor = core_permisos_categorias.IdIconColor',
            'where'   => 'core_permisos_categorias.idPermisosCat = ?',
            'params'  => [$PermisosCatID['data']],
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
                'Fnc_WidgetsCommon'   => $this->WidgetsCommon,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/permisosCategorias-View.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData]);
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
        $PermisosCatID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($PermisosCatID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idPermisosCat,Nombre,Icon,IdIconColor,Descripcion,Carpeta',
            'table'   => 'core_permisos_categorias',
            'join'    => '',
            'where'   => 'idPermisosCat = ?',
            'params'  => [$PermisosCatID['data']],
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
            'data'    => 'idColor AS ID,Nombre',
            'table'   => 'core_iconos_colores',
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

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrColores['status']){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*=========== Datos de la Pagina ===========*/
                'Fnc_FormInputs' => $this->FormInputs,
                /*=========== Datos Consultados ===========*/
                'rowData'    => $rowData['data'],
                'arrColores' => $arrColores['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/permisosCategorias-formEdit.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrColores]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    /*                            CONSULTAS INTERNAS                              */
    /******************************************************************************/
    /*******************************************************************/
    // Se obtiene la lista
    /*******************************************************************/
    private function getDataList($filter, $params = []){

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                core_permisos_categorias.idPermisosCat,
                core_permisos_categorias.Nombre,
                core_permisos_categorias.Icon,
                core_permisos_categorias.Descripcion,
                core_permisos_categorias.Carpeta,
                core_iconos_colores.Nombre AS IconColor',
            'table'   => 'core_permisos_categorias',
            'join'    => 'LEFT JOIN core_iconos_colores ON core_iconos_colores.idColor = core_permisos_categorias.IdIconColor',
            'where'   => $filter,
            'params'  => $params,
            'group'   => '',
            'having'  => '',
            'order'   => 'core_permisos_categorias.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams      = ['query' => $query];
        //Se retornan los datos
        return $this->Base_GetList($xParams);
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
        // Se genera la query
        $query = [
            'data'      => 'Nombre,Icon,IdIconColor,Descripcion,Carpeta',
            'required'  => 'Nombre,Icon,IdIconColor,Carpeta',
            'unique'    => 'Nombre',
            'encode'    => '',
            'table'     => 'core_permisos_categorias',
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
        // Si es un ID numérico, se envía con código 200 (OK)
        Response::success($Response['data']);

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
        // Se genera la query
        $query = [
            'data'      => 'idPermisosCat,Nombre,Icon,IdIconColor,Descripcion,Carpeta',
            'required'  => 'idPermisosCat,Nombre,Icon,IdIconColor,Carpeta',
            'unique'    => 'Nombre',
            'encode'    => '',
            'table'     => 'core_permisos_categorias',
            'where'     => 'idPermisosCat',
            'Post'      => $_POST
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
        // Se genera la query
        $query = [
            'files'       => '',
            'table'       => 'core_permisos_categorias',
            'where'       => 'idPermisosCat',
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
            'ValidarEmail'              => '',
            'ValidarNumero'             => 'IdIconColor',
            'ValidarEntero'             => 'IdIconColor',
            'ValidarRut'                => '',
            'ValidarPatente'            => '',
            'ValidarFecha'              => '',
            'ValidarHora'               => '',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => 'Nombre,Icon,Descripcion,Carpeta',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'Nombre,Icon,Carpeta',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Nombre,Icon,Descripcion,Carpeta',
            'ValidarEspaciosVacios'     => 'Carpeta',
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
