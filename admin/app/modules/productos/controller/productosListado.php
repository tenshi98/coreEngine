<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class productosListado extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataDate;
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
        $this->controllerName = 'productosListado';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->DataDate       = new FunctionsDataDate();
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
                productos_listado.idProducto,
                productos_listado.Nombre,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor,
                productos_tipos.Nombre AS Tipo,
                productos_categorias.Nombre AS Categoria',
            'table'   => 'productos_listado',
            'join'    => '
                LEFT JOIN core_estados           ON core_estados.idEstado               = productos_listado.idEstado
                LEFT JOIN productos_tipos        ON productos_tipos.idTipoProducto      = productos_listado.idTipoProducto
                LEFT JOIN productos_categorias   ON productos_categorias.idCategoria    = productos_listado.idCategoria',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'productos_listado.idEstado ASC, productos_tipos.Nombre ASC, productos_categorias.Nombre ASC, productos_listado.Nombre ASC',
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
            'data'    => 'idTipoProducto AS ID,Nombre',
            'table'   => 'productos_tipos',
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
            'data'    => 'idCategoria AS ID,Nombre',
            'table'   => 'productos_categorias',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams      = ['query' => $query];
        // Ejecuto la query
        $arrCategoria = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idUniMed AS ID,Nombre',
            'table'   => 'core_unidades_medida',
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
        $arrUnimed = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($arrList['status'] && $arrEstado['status'] && $arrTipo['status'] && $arrCategoria['status'] && $arrUnimed['status']){

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Listado Productos',
                'PageDescription' => 'Listado Productos.',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => 'Listado de Productos',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_Codification'    => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList['data'],
                'arrEstado'       => $arrEstado['data'],
                'arrTipo'         => $arrTipo['data'],
                'arrCategoria'    => $arrCategoria['data'],
                'arrUnimed'       => $arrUnimed['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrList,$arrEstado,$arrTipo,$arrCategoria,$arrUnimed]);
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
        $WhereData_int     = 'idEstado,idTipoProducto,idCategoria,idUniMed';  // Datos búsqueda exacta
        $WhereData_string  = 'Nombre,Marca,Codigo';                           // Datos búsqueda relativa
        $WhereData_between = '';                                              // Datos búsqueda Between
        $whereInt          = '';                                              // Se crea cadena
        $whereParams       = [];                                              // Valores bindeados asociados a $whereInt
        /************************************/
        // Se validan las fechas
        $RespDataBetween = $this->searchValidateDates($WhereData_between);
        if($RespDataBetween!=''){
            Response::error($RespDataBetween, 500);
        }
        /************************************/
        // Agrego variable busqueda
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_int, 'productos_listado', 1);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_string, 'productos_listado', 2);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_between, 'productos_listado', 3);
        $whereInt = $r['where']; $whereParams = $r['params'];

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                productos_listado.idProducto,
                productos_listado.Nombre,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor,
                productos_tipos.Nombre AS Tipo,
                productos_categorias.Nombre AS Categoria',
            'table'   => 'productos_listado',
            'join'    => '
                LEFT JOIN core_estados           ON core_estados.idEstado               = productos_listado.idEstado
                LEFT JOIN productos_tipos        ON productos_tipos.idTipoProducto      = productos_listado.idTipoProducto
                LEFT JOIN productos_categorias   ON productos_categorias.idCategoria    = productos_listado.idCategoria',
            'where'   => $whereInt,
            'params'  => $whereParams,
            'group'   => '',
            'having'  => '',
            'order'   => 'productos_listado.idEstado ASC, productos_tipos.Nombre ASC, productos_categorias.Nombre ASC, productos_listado.Nombre ASC',
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
                'TableTitle'      => 'Listado de Productos',
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
        $ProductoID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($ProductoID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                productos_listado.idProducto,
                productos_listado.Nombre,
                productos_listado.Marca,
                productos_listado.StockLimite,
                productos_listado.ValorIngreso,
                productos_listado.ValorEgreso,
                productos_listado.Descripcion,
                productos_listado.Codigo,
                productos_listado.Direccion_img,

                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor,
                productos_tipos.Nombre AS Tipo,
                productos_categorias.Nombre AS Categoria,
                core_unidades_medida.Nombre AS UniMed',
            'table'   => 'productos_listado',
            'join'    => '
                LEFT JOIN core_estados           ON core_estados.idEstado               = productos_listado.idEstado
                LEFT JOIN productos_tipos        ON productos_tipos.idTipoProducto      = productos_listado.idTipoProducto
                LEFT JOIN productos_categorias   ON productos_categorias.idCategoria    = productos_listado.idCategoria
                LEFT JOIN core_unidades_medida   ON core_unidades_medida.idUniMed       = productos_listado.idUniMed',
            'where'   => 'productos_listado.idProducto = ?',
            'params'  => [$ProductoID['data']],
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
        if($arrUserData["productosListadoVerDocumentos"]==2){
            /************************************/
            // Se genera la query
            $query = [
                'data'    => 'idDocumentos,Nombre,NombreArchivo,FVencimiento',
                'table'   => 'productos_listado_documentos',
                'join'    => '',
                'where'   => 'idProducto = ?',
                'params'  => [$ProductoID['data']],
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
                productos_listado_observaciones.Observacion,
                productos_listado_observaciones.FechaCreacion,
                usuarios_listado.Nombre AS Usuario',
            'table'   => 'productos_listado_observaciones',
            'join'    => 'LEFT JOIN usuarios_listado ON usuarios_listado.idUsuario = productos_listado_observaciones.idUsuario',
            'where'   => 'productos_listado_observaciones.idProducto = ?',
            'params'  => [$ProductoID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'productos_listado_observaciones.idObservaciones ASC',
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
        if($rowData['status'] && $arrDocumentos['status'] && $arrObservaciones['status']){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_Codification'     => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
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
            $result = $this->mergeResponses([$rowData,$arrDocumentos,$arrObservaciones]);
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
        $ProductoID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($ProductoID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                productos_listado.idProducto,
                productos_listado.idEstado,
                productos_listado.idTipoProducto,
                productos_listado.idCategoria,
                productos_listado.idUniMed,
                productos_listado.Nombre,
                productos_listado.Marca,
                productos_listado.StockLimite,
                productos_listado.ValorIngreso,
                productos_listado.ValorEgreso,
                productos_listado.Descripcion,
                productos_listado.Codigo,
                productos_listado.Direccion_img,

                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor,
                productos_tipos.Nombre AS Tipo,
                productos_categorias.Nombre AS Categoria,
                core_unidades_medida.Nombre AS UniMed',
            'table'   => 'productos_listado',
            'join'    => '
                LEFT JOIN core_estados           ON core_estados.idEstado               = productos_listado.idEstado
                LEFT JOIN productos_tipos        ON productos_tipos.idTipoProducto      = productos_listado.idTipoProducto
                LEFT JOIN productos_categorias   ON productos_categorias.idCategoria    = productos_listado.idCategoria
                LEFT JOIN core_unidades_medida   ON core_unidades_medida.idUniMed       = productos_listado.idUniMed',
            'where'   => 'productos_listado.idProducto = ?',
            'params'  => [$ProductoID['data']],
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
            'data'    => 'idTipoProducto AS ID,Nombre',
            'table'   => 'productos_tipos',
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
            'data'    => 'idCategoria AS ID,Nombre',
            'table'   => 'productos_categorias',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams      = ['query' => $query];
        // Ejecuto la query
        $arrCategoria = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idUniMed AS ID,Nombre',
            'table'   => 'core_unidades_medida',
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
        $arrUnimed = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrEstado['status'] && $arrTipo['status'] && $arrCategoria['status'] && $arrUnimed['status']){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'        => 'Resumen Productos',
                'PageDescription'  => 'Resumen Productos.',
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
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData['data'],
                'arrEstado'       => $arrEstado['data'],
                'arrTipo'         => $arrTipo['data'],
                'arrCategoria'    => $arrCategoria['data'],
                'arrUnimed'       => $arrUnimed['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrEstado,$arrTipo,$arrCategoria,$arrUnimed]);
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
        $ProductoID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($ProductoID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                productos_listado.idProducto,
                productos_listado.Nombre,
                productos_listado.Marca,
                productos_listado.StockLimite,
                productos_listado.ValorIngreso,
                productos_listado.ValorEgreso,
                productos_listado.Descripcion,
                productos_listado.Codigo,
                productos_listado.Direccion_img,

                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor,
                productos_tipos.Nombre AS Tipo,
                productos_categorias.Nombre AS Categoria,
                core_unidades_medida.Nombre AS UniMed',
            'table'   => 'productos_listado',
            'join'    => '
                LEFT JOIN core_estados           ON core_estados.idEstado               = productos_listado.idEstado
                LEFT JOIN productos_tipos        ON productos_tipos.idTipoProducto      = productos_listado.idTipoProducto
                LEFT JOIN productos_categorias   ON productos_categorias.idCategoria    = productos_listado.idCategoria
                LEFT JOIN core_unidades_medida   ON core_unidades_medida.idUniMed       = productos_listado.idUniMed',
            'where'   => 'productos_listado.idProducto = ?',
            'params'  => [$ProductoID['data']],
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
        // Se genera la query
        $query = [
            'data'      => 'idEstado,idTipoProducto,idCategoria,idUniMed,Nombre,Marca,StockLimite,ValorIngreso,ValorEgreso,Descripcion,Codigo',
            'required'  => 'idEstado,idTipoProducto,idCategoria,idUniMed,Nombre',
            'unique'    => 'Nombre,Codigo',
            'encode'    => '',
            'table'     => 'productos_listado',
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
        // Se genera la query
        $query = [
            'data'      => 'idProducto,idEstado,idTipoProducto,idCategoria,idUniMed,Nombre,Marca,StockLimite,ValorIngreso,ValorEgreso,Descripcion,Codigo',
            'required'  => 'idProducto,idEstado,idTipoProducto,idCategoria,idUniMed,Nombre',
            'unique'    => 'Nombre,Codigo',
            'encode'    => '',
            'table'     => 'productos_listado',
            'where'     => 'idProducto',
            'Post'      => $_POST,
            'files'     => [
                [
                    'Identificador' => 'Direccion_img',
                    'SubCarpeta'    => '',
                    'NombreArchivo' => '',
                    'SufijoArchivo' => 'ProductoIMG_',
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
            'table'       => 'productos_listado',
            'where'       => 'idProducto',
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
        $arrTableDel[] = ['files' => 'NombreArchivo', 'table' => 'productos_listado_documentos'];
        $arrTableDel[] = ['files' => '',              'table' => 'productos_listado_observaciones'];

        /************************************/
        // Verifico si existe
        if (!empty($arrTableDel)) {
            // Recorro
            foreach ($arrTableDel as $tblDel) {
                /************************************/
                // Se genera la query
                $query = ['files' => $tblDel['files'], 'table' => $tblDel['table'], 'where' => 'idProducto', 'SubCarpeta' => '', 'Post' => $dataDelete];
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
            'table'       => 'productos_listado',
            'where'       => 'idProducto',
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
            'ValidarEmail'              => '',
            'ValidarNumero'             => 'idEstado,idTipoProducto,idCategoria,idUniMed,StockLimite,ValorIngreso,ValorEgreso',
            'ValidarEntero'             => 'idEstado,idTipoProducto,idCategoria,idUniMed',
            'ValidarRut'                => '',
            'ValidarPatente'            => '',
            'ValidarFecha'              => '',
            'ValidarHora'               => '',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => 'Nombre,Marca,Descripcion,Codigo',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'Nombre,Marca,Codigo',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Nombre,Marca,Descripcion,Codigo',
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
