<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class bodegasMovimientoProductos extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $Codification;
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
        $this->controllerName = 'bodegasMovimiento';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->DataNumbers    = new FunctionsDataNumbers();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  RUTAS                                     */
    /******************************************************************************/
    /*******************************************************************/
    // Listar
    /*******************************************************************/
    public function New_1($f3, $params){$this->New($f3, $params, 1);}
    public function New_2($f3, $params){$this->New($f3, $params, 2);}
    public function New_3($f3, $params){$this->New($f3, $params, 3);}
    // Listar Todo
    public function UpdateList_1($f3, $params){$this->UpdateList($f3, $params, 1);}
    public function UpdateList_2($f3, $params){$this->UpdateList($f3, $params, 2);}
    public function UpdateList_3($f3, $params){$this->UpdateList($f3, $params, 3);}
    // Listar Todo
    public function GetID_1($f3, $params){$this->GetID($f3, $params, 1);}
    public function GetID_2($f3, $params){$this->GetID($f3, $params, 2);}
    public function GetID_3($f3, $params){$this->GetID($f3, $params, 3);}

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /*******************************************************************/
    // Crear nuevo
    /*******************************************************************/
    public function New($f3, $params, $idTipoIngreso){

        /************************************/
        // Se obtiene el ID
        $MovimientoID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($MovimientoID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se verifica movimiento
        switch ($idTipoIngreso) {
            case 1: $tsrxName = 'bodegasMovimientoIngreso';  break;//Ingreso
            case 2: $tsrxName = 'bodegasMovimientoEgreso';   break;//Egreso
            case 3: $tsrxName = 'bodegasMovimientoTraspaso'; break;//Traspaso
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idMovimiento,idBodegasIngreso,idBodegasEgreso',
            'table'   => 'bodegas_movimientos',
            'join'    => '',
            'where'   => 'idMovimiento = ?',
            'params'  => [$MovimientoID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /************************************/
        // Se instancia
        $arrUserData = $this->getUserData($f3);

        // Se verifica si se tiene el permiso para visualizar el dato
        if($arrUserData["usuariosPermisosBodegas"]==2 && $arrUserData['UserType'] != 1){
            $X_join  = 'INNER JOIN bodegas_listado_permisos_usuarios ON bodegas_listado_permisos_usuarios.idBodegas = bodegas_listado.idBodegas';
            $X_where  = 'bodegas_listado.idEstado = ? AND bodegas_listado_permisos_usuarios.idUsuario = ?';
            $X_params = [1, $arrUserData['UserID']];
        // Si se permite junto con la creacion de tareas
        }else{
            $X_join   = '';
            $X_where  = 'bodegas_listado.idEstado = ?';
            $X_params = [1];
        }
        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'bodegas_listado.idBodegas AS ID, bodegas_listado.Nombre',
            'table'   => 'bodegas_listado',
            'join'    => $X_join,
            'where'   => $X_where,
            'params'  => $X_params,
            'group'   => '',
            'having'  => '',
            'order'   => 'bodegas_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams    = ['query' => $query];
        // Ejecuto la query
        $arrBodegas = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idProducto AS ID,Nombre',
            'table'   => 'productos_listado',
            'join'    => '',
            'where'   => 'idEstado = ?',
            'params'  => [1],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams      = ['query' => $query];
        // Ejecuto la query
        $arrProductos = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idEstadoIngreso AS ID,Nombre',
            'table'   => 'core_estados_ingreso',
            'join'    => '',
            'where'   => 'idEstadoIngreso != ?',
            'params'  => [3],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams    = ['query' => $query];
        // Ejecuto la query
        $arrTipoMov = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrBodegas['status'] && $arrProductos['status'] && $arrTipoMov['status']){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $tsrxName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'       => $this->FormInputs,
                'Fnc_Codification'     => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData['data'],
                'arrBodegas'      => $arrBodegas['data'],
                'arrProductos'    => $arrProductos['data'],
                'arrTipoMov'      => $arrTipoMov['data'],
                'idTipoIngreso'   => $idTipoIngreso,
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Productos-formNew.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrBodegas,$arrProductos,$arrTipoMov]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Actualizar Listar
    /*******************************************************************/
    public function UpdateList($f3, $params, $idTipoIngreso){

        /************************************/
        // Se obtiene el ID
        $MovimientoID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($MovimientoID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se verifica movimiento
        switch ($idTipoIngreso) {
            case 1: $tsrxName = 'bodegasMovimientoIngreso';  break;//Ingreso
            case 2: $tsrxName = 'bodegasMovimientoEgreso';   break;//Egreso
            case 3: $tsrxName = 'bodegasMovimientoTraspaso'; break;//Traspaso
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                bodegas_movimientos_productos.idExistencia,
                core_estados_ingreso.Nombre AS TipoMovimiento,
                bodegas_listado.Nombre AS Bodega,
                productos_listado.Nombre AS ProductoNombre,
                bodegas_movimientos_productos.Number AS ProductoCantidad,
                core_unidades_medida.Nombre AS UnidadMedida',
            'table'   => 'bodegas_movimientos_productos',
            'join'    => '
                LEFT JOIN core_estados_ingreso  ON core_estados_ingreso.idEstadoIngreso  = bodegas_movimientos_productos.idEstadoIngreso
                LEFT JOIN bodegas_listado       ON bodegas_listado.idBodegas             = bodegas_movimientos_productos.idBodegas
                LEFT JOIN productos_listado     ON productos_listado.idProducto          = bodegas_movimientos_productos.idProducto
                LEFT JOIN core_unidades_medida  ON core_unidades_medida.idUniMed         = productos_listado.idUniMed',
            'where'   => 'bodegas_movimientos_productos.idMovimiento = ?',
            'params'  => [$MovimientoID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'bodegas_movimientos_productos.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams      = ['query' => $query];
        // Ejecuto la query
        $arrProductos = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrProductos['status'] === true) {

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $tsrxName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'    => $this->Codification,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'arrProductos'    => $arrProductos['data'],
                'idTipoIngreso'   => $idTipoIngreso,
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Productos-UpdateList.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrProductos]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Editar
    /*******************************************************************/
    public function GetID($f3, $params, $idTipoIngreso){

        /************************************/
        // Se obtiene el ID
        $ExistenciaID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($ExistenciaID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se verifica movimiento
        switch ($idTipoIngreso) {
            case 1: $tsrxName = 'bodegasMovimientoIngreso';  break;//Ingreso
            case 2: $tsrxName = 'bodegasMovimientoEgreso';   break;//Egreso
            case 3: $tsrxName = 'bodegasMovimientoTraspaso'; break;//Traspaso
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                bodegas_movimientos_productos.idExistencia,
                bodegas_movimientos_productos.idMovimiento,
                bodegas_movimientos_productos.idEstadoIngreso,
                bodegas_movimientos_productos.idBodegas,
                bodegas_movimientos_productos.idProducto,
                bodegas_movimientos_productos.Number,
                core_estados_ingreso.Nombre AS TipoMovimiento,
                bodegas_listado.Nombre AS Bodega,
                productos_listado.Nombre AS ProductoNombre',
            'table'   => 'bodegas_movimientos_productos',
            'join'    => '
                LEFT JOIN core_estados_ingreso  ON core_estados_ingreso.idEstadoIngreso  = bodegas_movimientos_productos.idEstadoIngreso
                LEFT JOIN bodegas_listado       ON bodegas_listado.idBodegas             = bodegas_movimientos_productos.idBodegas
                LEFT JOIN productos_listado     ON productos_listado.idProducto          = bodegas_movimientos_productos.idProducto',
            'where'   => 'bodegas_movimientos_productos.idExistencia = ?',
            'params'  => [$ExistenciaID['data']],
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
                'UserAccess'    => $this->getArrLevel($f3, $tsrxName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'    => $this->FormInputs,
                'Fnc_Codification'  => $this->Codification,
                'Fnc_DataNumbers'   => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'rowData'       => $rowData['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Productos-formEdit.php');
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
    // Crear producto asociado a un movimiento.
    /*******************************************************************/
    public function Insert(): void {

        /************************************/
        // Se llama al movimiento de materiales
        $Response = $this->insertMov($_POST);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if($Response['code'] != 200){
            Response::error($Response['message'], $Response['code'], $Response['error'] ?? '');
        }

        /************************************/
        // Se envía respuesta con código 200 (OK)
        Response::success($Response['data']['data']);

    }

    /*******************************************************************/
    // Editar producto asociado a un movimiento.
    /*******************************************************************/
    public function Update(): void {

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Se llama al movimiento de materiales
        $Response = $this->updateMov($_POST);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if($Response['code'] != 200){
            Response::error($Response['message'], $Response['code'], $Response['error'] ?? '');
        }

        /************************************/
        // Se envía respuesta con código 200 (OK)
        Response::success($Response['data']['data']);

    }

    /*******************************************************************/
    // Elimina un producto del movimiento y revierte su stock.
    /*******************************************************************/
    public function Delete(): void {

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Obtener datos
        parse_str(file_get_contents('php://input'), $dataDelete);

        /************************************/
        // Se llama al movimiento de materiales
        $Response = $this->deleteMov($dataDelete);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if($Response['code'] != 200){
            Response::error($Response['message'], $Response['code'], $Response['error'] ?? '');
        }

        /************************************/
        // Se envía respuesta con código 200 (OK)
        Response::success($Response['data']['data']);

    }

    /******************************************************************************/
    /*                             EJECUCION OTROS                                */
    /******************************************************************************/
    /*******************************************************************/
    // Crear producto asociado a un movimiento.
    /*******************************************************************/
    public function insertMov($PostData, $NewDBConn = null): array {

        /************************************/
        // Verifico si se ejecuta otro hilo
        $DBConn = $NewDBConn ?? $this->getDBConn();

        /*******************************************************************/
        // Valores
        /*******************************************************************/
        $idMovimiento    = (int)($PostData['idMovimiento'] ?? 0);
        $idEstadoIngreso = (int)($PostData['idEstadoIngreso'] ?? 0);
        $idBodega        = (int)($PostData['idBodegas'] ?? 0);
        $idProducto      = (int)($PostData['idProducto'] ?? 0);
        $cantidad        = (float)($PostData['Number'] ?? 0);

        /*******************************************************************/
        // Validaciones
        /*******************************************************************/
        if ($idMovimiento <= 0) {                         return ['code' => 400, 'message' => 'Movimiento inválido'];}
        if (!in_array($idEstadoIngreso, [1, 2], true)) {  return ['code' => 400, 'message' => 'Tipo de movimiento inválido'];}
        if ($idBodega <= 0) {                             return ['code' => 400, 'message' => 'Debe indicar una bodega válida'];}
        if ($idProducto <= 0) {                           return ['code' => 400, 'message' => 'Producto inválido'];}
        if ($cantidad <= 0) {                             return ['code' => 400, 'message' => 'La cantidad debe ser mayor que cero'];}

        /************************************/
        // Se inicia la transacción solo si corresponde
        if($NewDBConn === null){
            $this->Base_transactionBegin(['newBDConn' => $DBConn]);
        }

        /************************************/
        // Ejecucion
        try {

            /*******************************************************************/
            // Insertar producto del movimiento
            /*******************************************************************/
            // Se crean los datos
            $arrTareas = [
                'idMovimiento'    => $idMovimiento,
                'idEstadoIngreso' => $idEstadoIngreso,
                'idBodegas'       => $idBodega,
                'idProducto'      => $idProducto,
                'Number'          => $cantidad
            ];
            /************************************/
            // Se genera la query
            $query = [
                'data'      => 'idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
                'required'  => 'idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'bodegas_movimientos_productos',
                'Post'      => $arrTareas
            ];
            /************************************/
            // Se genera el chequeo
            $DataCheck = $this->dataCheck_1($arrTareas);
            // Preparo los datos
            $xParams  = ['DataCheck' => $DataCheck, 'query' => $query, 'newBDConn' => $DBConn];
            // Ejecuto la query
            $Response = $this->Base_insert($xParams);

            /************************************/
            // Si falla la ejecucion, se revierte de inmediato
            if ($Response['status'] === false) {
                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $Response['error']];
            }

            /*******************************************************************/
            // Obtener stock actual
            /*******************************************************************/
            // Variable
            $campoStock = 'Cantidad_idBodegas_' . $idBodega;
            //Se consultan los stocks
            $query = [
                'data'    => 'idStocks,idProducto,' . $campoStock . ' AS Cantidad',
                'table'   => 'bodegas_productos_stocks',
                'join'    => '',
                'where'   => 'idProducto = ?',
                'params'  => [$idProducto],
                'group'   => '',
                'having'  => '',
                'order'   => 'idProducto ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            // Preparo los datos
            $xParams   = ['query' => $query, 'newBDConn' => $DBConn];
            // Ejecuto la query
            $arrStocks = $this->Base_GetList($xParams);

            /************************************/
            // Si falla la ejecucion, se revierte de inmediato
            if ($arrStocks['status'] === false) {
                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                return ['code' => 500, 'message' => 'Error al consultar el stock', 'error' => $arrStocks['error']];
            }

            /*******************************************************************/
            // Preparar stock
            /*******************************************************************/
            $idStocks     = null;
            $stockActual  = 0;

            if (!empty($arrStocks['data'])) {
                $stock       = $arrStocks['data'][0];
                $idStocks    = $stock['idStocks'] ?? null;
                $stockActual = (float)($stock['Cantidad'] ?? 0);
            }

            /*******************************************************************/
            // Calcular nuevo stock
            /*******************************************************************/
            $nuevoStock = match ($idEstadoIngreso) {
                1 => $stockActual + $cantidad,
                2 => $stockActual - $cantidad,
                default => 0
            };

            /*******************************************************************/
            // Validar stock para egreso
            /*******************************************************************/
            if ($idEstadoIngreso === 2 && $nuevoStock < 0) {
                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                return ['code' => 400, 'message' => 'Stock insuficiente para el producto ' . $idProducto];
            }

            /*******************************************************************/
            // Actualizar / crear stock
            /*******************************************************************/
            $bodegasMovimiento = new bodegasMovimiento();
            $guardarStock      = $bodegasMovimiento->guardarStock($idStocks, $idProducto, $idBodega, $nuevoStock, $DBConn);
            if($guardarStock['code'] != 200){
                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                return $guardarStock;
            }

            /************************************/
            // Confirmar transacción solo si corresponde
            if($NewDBConn === null){
                $this->Base_transactionCommit(['newBDConn' => $DBConn]);
            }

            /************************************/
            // Retorno los datos
            return ['code' => 200, 'data' => $Response];

        } catch (\Throwable $e) {

            /************************************/
            // Se revierte toda la operación ante cualquier error
            $this->Base_transactionRollback(['newBDConn' => $DBConn]);
            return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $e->getMessage()];

        }
    }

    /*******************************************************************/
    // Editar producto asociado a un movimiento.
    /*******************************************************************/
    public function updateMov($PostData, $NewDBConn = null): array {

        /************************************/
        // Verifico si se ejecuta otro hilo
        $DBConn = $NewDBConn ?? $this->getDBConn();

        /*******************************************************************/
        // Valores
        /*******************************************************************/
        $idExistencia     = (int)($PostData['idExistencia'] ?? 0);
        $idMovimiento     = (int)($PostData['idMovimiento'] ?? 0);
        $idEstadoIngreso  = (int)($PostData['idEstadoIngreso'] ?? 0);
        $idBodega         = (int)($PostData['idBodegas'] ?? 0);
        $idProducto       = (int)($PostData['idProducto'] ?? 0);
        $cantidadNueva    = (float)($PostData['Number'] ?? 0);
        $cantidadAnterior = (float)($PostData['NumberOld'] ?? 0);

        /*******************************************************************/
        // Validaciones
        /*******************************************************************/
        if ($idExistencia <= 0) {                         return ['code' => 400, 'message' => 'Registro inválido'];}
        if ($idMovimiento <= 0) {                         return ['code' => 400, 'message' => 'Movimiento inválido'];}
        if (!in_array($idEstadoIngreso, [1, 2], true)) {  return ['code' => 400, 'message' => 'Tipo de movimiento inválido'];}
        if ($idBodega <= 0) {                             return ['code' => 400, 'message' => 'Debe indicar una bodega válida'];}
        if ($idProducto <= 0) {                           return ['code' => 400, 'message' => 'Producto inválido'];}
        if ($cantidadNueva <= 0) {                        return ['code' => 400, 'message' => 'La cantidad debe ser mayor que cero'];}
        if ($cantidadAnterior < 0) {                      return ['code' => 400, 'message' => 'La cantidad anterior no es válida'];}

        /*******************************************************************/
        // Calcular diferencia
        /*******************************************************************/
        $diferencia = $cantidadNueva - $cantidadAnterior;

        /************************************/
        // Se inicia la transacción solo si corresponde
        if($NewDBConn === null){
            $this->Base_transactionBegin(['newBDConn' => $DBConn]);
        }

        /************************************/
        // Ejecucion
        try {

            /*******************************************************************/
            // Actualizar detalle del movimiento
            /*******************************************************************/
            // Se crean los datos
            $arrTareas = [
                'idExistencia'    => $idExistencia,
                'idMovimiento'    => $idMovimiento,
                'idEstadoIngreso' => $idEstadoIngreso,
                'idBodegas'       => $idBodega,
                'idProducto'      => $idProducto,
                'Number'          => $cantidadNueva
            ];
            /************************************/
            // Se genera la query
            $query = [
                'data'      => 'idExistencia,idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
                'required'  => 'idExistencia,idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'bodegas_movimientos_productos',
                'where'     => 'idExistencia',
                'Post'      => $arrTareas
            ];
            /************************************/
            // Se genera el chequeo
            $DataCheck = $this->DataCheck_1($arrTareas);
            // Preparo los datos
            $xParams  = ['DataCheck' => $DataCheck, 'query' => $query, 'newBDConn' => $DBConn];
            // Ejecuto la query
            $Response = $this->Base_update($xParams);

            /************************************/
            // Si falla la ejecucion, se revierte de inmediato
            if ($Response['status'] === false) {
                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $Response['error']];
            }

            /*******************************************************************/
            // Si no cambió la cantidad, no es necesario modificar stock
            /*******************************************************************/
            if ($diferencia == 0) {
                // Retorno los datos
                return ['code' => 200, 'data' => $Response];
            }

            /*******************************************************************/
            // Obtener stock actual
            /*******************************************************************/
            // Variable
            $campoStock = 'Cantidad_idBodegas_' . $idBodega;
            // Se consultan los stocks
            $query = [
                'data'    => 'idStocks,idProducto,' . $campoStock . ' AS Cantidad',
                'table'   => 'bodegas_productos_stocks',
                'join'    => '',
                'where'   => 'idProducto = ?',
                'params'  => [$idProducto],
                'group'   => '',
                'having'  => '',
                'order'   => 'idProducto ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            // Preparo los datos
            $xParams   = ['query' => $query, 'newBDConn' => $DBConn];
            // Ejecuto la query
            $arrStocks = $this->Base_GetList($xParams);

            /************************************/
            // Si falla la ejecucion, se revierte de inmediato
            if ($arrStocks['status'] === false) {
                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                return ['code' => 500, 'message' => 'Error al consultar el stock', 'error' => $arrStocks['error']];
            }

            /*******************************************************************/
            // Preparar stock
            /*******************************************************************/
            $idStocks    = null;
            $stockActual = 0;

            if (!empty($arrStocks['data'])) {
                $stock       = $arrStocks['data'][0];
                $idStocks    = $stock['idStocks'] ?? null;
                $stockActual = (float)($stock['Cantidad'] ?? 0);
            }

            /*******************************************************************/
            // Calcular nuevo stock
            /*******************************************************************/
            $nuevoStock = match ($idEstadoIngreso) {
                1 => $stockActual + $diferencia,
                2 => $stockActual - $diferencia,
                default => 0
            };

            /*******************************************************************/
            // Validar stock
            /*******************************************************************/
            if ($idEstadoIngreso === 2 && $nuevoStock < 0) {
                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                return ['code' => 400, 'message' => 'Stock insuficiente para modificar el producto ' . $idProducto];
            }

            /*******************************************************************/
            // Actualizar stock
            /*******************************************************************/
            $bodegasMovimiento = new bodegasMovimiento();
            $guardarStock      = $bodegasMovimiento->guardarStock($idStocks, $idProducto, $idBodega, $nuevoStock, $DBConn);
            if($guardarStock['code'] != 200){
                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                return $guardarStock;
            }

            /************************************/
            // Confirmar transacción solo si corresponde
            if($NewDBConn === null){
                $this->Base_transactionCommit(['newBDConn' => $DBConn]);
            }

            /************************************/
            // Retorno los datos
            return ['code' => 200, 'data' => $Response];

        } catch (\Throwable $e) {

            /************************************/
            // Se revierte toda la operación ante cualquier error
            $this->Base_transactionRollback(['newBDConn' => $DBConn]);
            return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $e->getMessage()];

        }

    }

    /*******************************************************************/
    // Elimina un producto del movimiento y revierte su stock.
    /*******************************************************************/
    public function deleteMov($PostData, $NewDBConn = null): array {

        /************************************/
        // Verifico si se ejecuta otro hilo
        $DBConn = $NewDBConn ?? $this->getDBConn();

        /************************************/
        // Se obtiene el ID real del movimiento
        $ExistenciaID = $this->Codification->encryptDecrypt('decrypt', $PostData['idExistencia']);
        if (!$this->isValidDecrypted($ExistenciaID, 'id')) {
            return ['code' => 400, 'message' => 'Registro inválido'];
        }

        /************************************/
        // Se inicia la transacción solo si corresponde
        if($NewDBConn === null){
            $this->Base_transactionBegin(['newBDConn' => $DBConn]);
        }

        /************************************/
        // Ejecucion
        try {

            /*******************************************************************/
            // Obtener información del producto antes de eliminarlo
            /*******************************************************************/
            // Consulta
            $query = [
                'data' => 'idExistencia, idMovimiento, idEstadoIngreso, idBodegas, idProducto, Number',
                'table'  => 'bodegas_movimientos_productos',
                'join'   => '',
                'where'  => 'idExistencia = ?',
                'params' => [$ExistenciaID['data']],
                'group'  => '',
                'having' => '',
                'order'  => '',
                'limit'  => 1
            ];
            // Preparo los datos
            $xParams      = ['query' => $query, 'newBDConn' => $DBConn];
            // Ejecuto la query
            $arrProducto = $this->Base_GetList($xParams);

            /************************************/
            // Si falla la ejecucion, se revierte de inmediato
            if ($arrProducto['status'] === false) {
                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                return ['code' => 500, 'message' => 'Error al consultar el producto del movimiento', 'error' => $arrProducto['error']];
            }
            if (empty($arrProducto['data'])) {
                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                return ['code' => 404, 'message' => 'No existe el producto indicado'];
            }

            $producto = $arrProducto['data'][0];

            /*******************************************************************/
            // Normalizar datos
            /*******************************************************************/
            $idMovimiento    = (int)$producto['idMovimiento'];
            $idEstadoIngreso = (int)$producto['idEstadoIngreso'];
            $idBodega        = (int)$producto['idBodegas'];
            $idProducto      = (int)$producto['idProducto'];
            $cantidad        = (float)$producto['Number'];

            /*******************************************************************/
            // Validar producto
            /*******************************************************************/
            if ($idProducto <= 0 || $cantidad <= 0) {
                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                return ['code' => 400, 'message' => 'Los datos del producto no son válidos'];
            }

            if (!in_array($idEstadoIngreso, [1, 2], true)) {
                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                return ['code' => 400, 'message' => 'Estado de movimiento no válido: ' . $idEstadoIngreso];
            }

            if ($idBodega <= 0) {
                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                return ['code' => 400, 'message' => 'La bodega del movimiento no es válida'];
            }

            /*******************************************************************/
            // Obtener stock actual
            /*******************************************************************/
            // Variables
            $campoStock = 'Cantidad_idBodegas_' . $idBodega;
            // Consulta
            $query = [
                'data'    => 'idStocks,idProducto,' . $campoStock . ' AS Cantidad',
                'table'   => 'bodegas_productos_stocks',
                'join'    => '',
                'where'   => 'idProducto = ?',
                'params'  => [$idProducto],
                'group'   => '',
                'having'  => '',
                'order'   => 'idProducto ASC',
                'limit'   => 1
            ];
            // Preparo los datos
            $xParams   = ['query' => $query, 'newBDConn' => $DBConn];
            // Ejecuto la query
            $arrStocks = $this->Base_GetList($xParams);

            /************************************/
            // Si falla la ejecucion, se revierte de inmediato
            if ($arrStocks['status'] === false) {
                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                return ['code' => 500, 'message' => 'Error al consultar el stock', 'error' => $arrStocks['error']];
            }

            /*******************************************************************/
            // El stock debe existir para poder revertir el movimiento
            /*******************************************************************/
            if (empty($arrStocks['data'])) {
                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                return ['code' => 400, 'message' => 'No existe stock para el producto: ' . $idProducto];
            }

            $stock       = $arrStocks['data'][0];
            $idStocks    = $stock['idStocks'] ?? null;
            $stockActual = (float)($stock['Cantidad'] ?? 0);

            if (empty($idStocks)) {
                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                return ['code' => 400, 'message' => 'No existe un registro de stock para el producto: ' . $idProducto];
            }

            /*******************************************************************/
            // Revertir stock
            /*******************************************************************/

            switch ($idEstadoIngreso) {

                /***************************************************************/
                // INGRESO
                // El movimiento había sumado stock.
                // Al eliminarlo debemos restarlo.
                /***************************************************************/

                case 1:

                    $nuevoStock = $stockActual - $cantidad;

                    if ($nuevoStock < 0) {
                        $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                        return ['code' => 400, 'message' => 'No es posible eliminar el movimiento porque el stock resultante sería negativo para el producto ' . $idProducto];
                    }

                    $arrTareas = [
                        'idStocks'   => $idStocks,
                        $campoStock  => $nuevoStock
                    ];

                    $bodegasMovimiento = new bodegasMovimiento();
                    $actualizarStock   = $bodegasMovimiento->actualizarStock($arrTareas, $campoStock, $DBConn);
                    if($actualizarStock['code'] != 200){
                        $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                        return $actualizarStock;
                    }

                    break;

                /***************************************************************/
                // EGRESO
                // El movimiento había restado stock.
                // Al eliminarlo debemos devolverlo.
                /***************************************************************/

                case 2:

                    $nuevoStock = $stockActual + $cantidad;

                    $arrTareas = [
                        'idStocks'  => $idStocks,
                        $campoStock => $nuevoStock
                    ];

                    $bodegasMovimiento = new bodegasMovimiento();
                    $actualizarStock   = $bodegasMovimiento->actualizarStock($arrTareas, $campoStock, $DBConn);
                    if($actualizarStock['code'] != 200){
                        $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                        return $actualizarStock;
                    }

                    break;
            }

            /*******************************************************************/
            // Eliminar producto del movimiento
            /*******************************************************************/

            /************************************/
            // Se genera la query
            $query = [
                'files'      => '',
                'table'      => 'bodegas_movimientos_productos',
                'where'      => 'idExistencia',
                'SubCarpeta' => '',
                'Post'       => $PostData
            ];
            // Preparo los datos
            $xParams  = ['query' => $query, 'newBDConn' => $DBConn];
            // Ejecuto la query
            $Response = $this->Base_delete($xParams);

            /************************************/
            // Si falla la ejecucion, se revierte de inmediato
            if ($Response['status'] === false) {
                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                return ['code' => 500, 'message' => 'Error al eliminar el producto del movimiento', 'error' => $Response['error']];
            }

            /************************************/
            // Confirmar transacción solo si corresponde
            if($NewDBConn === null){
                $this->Base_transactionCommit(['newBDConn' => $DBConn]);
            }

            /************************************/
            // Retorno los datos
            return ['code' => 200, 'data' => $Response];

        } catch (\Throwable $e) {

            /************************************/
            // Se revierte toda la operación ante cualquier error
            $this->Base_transactionRollback(['newBDConn' => $DBConn]);
            return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $e->getMessage()];

        }

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
            'ValidarNumero'             => 'idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
            'ValidarEntero'             => 'idMovimiento,idEstadoIngreso,idBodegas,idProducto',
            'ValidarRut'                => '',
            'ValidarPatente'            => '',
            'ValidarFecha'              => '',
            'ValidarHora'               => '',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => '',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => '',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => '',
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
