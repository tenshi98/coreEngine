<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class gestionDocumentosProductos extends ControllerBase {

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
        $this->controllerName = 'gestionDocumentos';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
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
    public function New_1($f3, $params){$this->New($f3, $params, 1);}
    public function New_2($f3, $params){$this->New($f3, $params, 2);}
    // Listar Todo
    public function UpdateList_1($f3, $params){$this->UpdateList($f3, $params, 1);}
    public function UpdateList_2($f3, $params){$this->UpdateList($f3, $params, 2);}
    // Listar Todo
    public function GetID_1($f3, $params){$this->GetID($f3, $params, 1);}
    public function GetID_2($f3, $params){$this->GetID($f3, $params, 2);}

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /*******************************************************************/
    // Crear nuevo
    /*******************************************************************/
    public function New($f3, $params, $idTipo){

        /************************************/
        // Se verifica movimiento
        $tsrxName = $this->tsrxName($idTipo);

        /************************************/
        // Se obtiene el ID
        $FacturacionID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($FacturacionID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idFacturacion',
            'table'   => 'facturacion_listado',
            'join'    => '',
            'where'   => 'idFacturacion = ?',
            'params'  => [$FacturacionID['data']],
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
            $X_join  = '';
            $X_where = 'bodegas_listado.idEstado = ?';
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

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrBodegas['status'] && $arrProductos['status']){
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
                'idTipo'          => $idTipo,
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Productos-formNew.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrBodegas,$arrProductos]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Actualizar Listar
    /*******************************************************************/
    public function UpdateList($f3, $params, $idTipo){

        /************************************/
        // Se verifica movimiento
        $tsrxName = $this->tsrxName($idTipo);

        /************************************/
        // Se obtiene el ID
        $FacturacionID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($FacturacionID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idEstadoPago',
            'table'   => 'facturacion_listado',
            'join'    => '',
            'where'   => 'idFacturacion = ?',
            'params'  => [$FacturacionID['data']],
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
                facturacion_listado_productos.idExistencia,
                core_estados_ingreso.Nombre AS TipoMovimiento,
                bodegas_listado.Nombre AS Bodega,
                productos_listado.Nombre AS ProductoNombre,
                facturacion_listado_productos.Number AS ProductoCantidad,
                facturacion_listado_productos.ValorTotal AS ProductoValor,
                core_unidades_medida.Nombre AS UnidadMedida',
            'table'   => 'facturacion_listado_productos',
            'join'    => '
                LEFT JOIN core_estados_ingreso  ON core_estados_ingreso.idEstadoIngreso  = facturacion_listado_productos.idEstadoIngreso
                LEFT JOIN bodegas_listado       ON bodegas_listado.idBodegas             = facturacion_listado_productos.idBodegas
                LEFT JOIN productos_listado     ON productos_listado.idProducto          = facturacion_listado_productos.idProducto
                LEFT JOIN core_unidades_medida  ON core_unidades_medida.idUniMed         = productos_listado.idUniMed',
            'where'   => 'facturacion_listado_productos.idFacturacion = ?',
            'params'  => [$FacturacionID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'facturacion_listado_productos.idExistencia ASC',
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
        if($rowData['status'] && $arrProductos['status']){

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
                'rowData'         => $rowData['data'],
                'arrProductos'    => $arrProductos['data'],
                'idTipo'          => $idTipo,
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Productos-UpdateList.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrProductos]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Editar
    /*******************************************************************/
    public function GetID($f3, $params, $idTipo){

        /************************************/
        // Se verifica movimiento
        $tsrxName = $this->tsrxName($idTipo);

        /************************************/
        // Se obtiene el ID
        $ExistenciaID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($ExistenciaID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                facturacion_listado_productos.idExistencia,
                facturacion_listado_productos.idFacturacion,
                facturacion_listado_productos.Number,
                facturacion_listado_productos.ValorTotal,
                facturacion_listado_productos.idEstadoIngreso,
                facturacion_listado_productos.idBodegas,
                facturacion_listado_productos.idProducto,
                core_estados_ingreso.Nombre AS TipoMovimiento,
                bodegas_listado.Nombre AS Bodega,
                productos_listado.Nombre AS ProductoNombre',
            'table'   => 'facturacion_listado_productos',
            'join'    => '
                LEFT JOIN core_estados_ingreso  ON core_estados_ingreso.idEstadoIngreso  = facturacion_listado_productos.idEstadoIngreso
                LEFT JOIN bodegas_listado       ON bodegas_listado.idBodegas             = facturacion_listado_productos.idBodegas
                LEFT JOIN productos_listado     ON productos_listado.idProducto          = facturacion_listado_productos.idProducto',
            'where'   => 'facturacion_listado_productos.idExistencia = ?',
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
    // Insertar
    /*******************************************************************/
    public function Insert($f3){

        /************************************/
        // Se le entregan los datos
        $Response = $this->InsertProds($f3, $_POST);

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
        // Se le entregan los datos
        $Response = $this->UpdateProds($f3, $_POST);

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
        // Se le entregan los datos
        $Response = $this->DeleteProds($f3, $dataDelete);

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
    // Insertar
    /*******************************************************************/
    public function InsertProds($f3, $PostData): array {

        /************************************/
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($PostData);

        /************************************/
        // Se instancia
        $arrUserData = $this->getUserData($f3);

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idFacturacion,idEstadoIngreso,idBodegas,idProducto,Number,ValorTotal',
            'required'  => 'idFacturacion,idEstadoIngreso,idBodegas,idProducto,ValorTotal',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'facturacion_listado_productos',
            'Post'      => $PostData
        ];
        // Preparo los datos
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        // Ejecuto la query
        $Response = $this->Base_insert($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            $this->Base_transactionRollback();
            return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $Response['error']];
        }

        /************************************/
        // Se actualizan los datos de la factura
        $gestionDocumentos  = new gestionDocumentos();
        $ResponseGestionDoc = $gestionDocumentos->updateFact(2, $PostData['idFacturacion'], $this->getDBConn());
        if($ResponseGestionDoc['code'] != 200){
            $this->Base_transactionRollback();
            return $ResponseGestionDoc;
        }

        /************************************/
        //Movimiento de bodegas
        //permite la interaccion con la bodega, para generar documentos de ingreso o egreso
        if($arrUserData["gestionDocumentosUsoBodega"]==2){

            /************************************/
            // Datos del movimiento
            $query = [
                'data'    => 'idMovimiento, idEstadoIngreso, idBodegasIngreso, idBodegasEgreso',
                'table'   => 'bodegas_movimientos',
                'join'    => '',
                'where'   => 'idFacturacion = ?',
                'params'  => [$PostData['idFacturacion']],
                'group'   => '',
                'having'  => '',
                'order'   => ''
            ];
            // Preparo los datos
            $xParams       = ['query' => $query];
            // Ejecuto la query
            $rowMovimiento = $this->Base_GetByID($xParams);

            /************************************/
            // Variable
            $PostMovProd = array();
            // Productos
            $PostMovProd['idMovimiento']       = $rowMovimiento['data']['idMovimiento'];
            $PostMovProd['idEstadoIngreso']    = $rowMovimiento['data']['idEstadoIngreso'];
            switch ($rowMovimiento['data']['idEstadoIngreso']) {
                case 1: $PostMovProd['idBodega'] = $rowMovimiento['data']['idBodegasIngreso']; break;
                case 2: $PostMovProd['idBodega'] = $rowMovimiento['data']['idBodegasEgreso']; break;
            }
            $PostMovProd['idProducto']    = $PostData['idProducto'];
            $PostMovProd['Number']        = $PostData['Number'];

            /************************************/
            // Se instancia
            $bodegasMovimientoProductos = new bodegasMovimientoProductos();
            $ResponseBodMovProd         = $bodegasMovimientoProductos->insertMov($PostMovProd, $this->getDBConn());
            if($ResponseBodMovProd['code'] != 200){
                $this->Base_transactionRollback();
                return $ResponseBodMovProd;
            }

        }

        /************************************/
        // Confirmar transacción
        $this->Base_transactionCommit();

        /************************************/
        // Retorno los datos
        return ['code' => 200, 'data' => $Response];

    }

    /*******************************************************************/
    // Editar por put (solo modificar datos)
    // Editar por post (modificar y subir archivos)
    /*******************************************************************/
    public function UpdateProds($f3, $PostData): array {

        /************************************/
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($PostData);

        /************************************/
        // Se instancia
        $arrUserData = $this->getUserData($f3);

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idExistencia,idFacturacion,idEstadoIngreso,idBodegas,idProducto,Number,ValorTotal',
            'required'  => 'idExistencia,idFacturacion,idEstadoIngreso,idBodegas,idProducto,ValorTotal',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'facturacion_listado_productos',
            'where'     => 'idExistencia',
            'Post'      => $PostData
        ];
        // Preparo los datos
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        // Ejecuto la query
        $Response = $this->Base_update($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            $this->Base_transactionRollback();
            return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $Response['error']];
        }

        /************************************/
        // Se actualizan los datos de la factura
        $gestionDocumentos  = new gestionDocumentos();
        $ResponseGestionDoc = $gestionDocumentos->updateFact(2, $PostData['idFacturacion'], $this->getDBConn());
        if($ResponseGestionDoc['code'] != 200){
            $this->Base_transactionRollback();
            return $ResponseGestionDoc;
        }

        /************************************/
        //Movimiento de bodegas
        //permite la interaccion con la bodega, para generar documentos de ingreso o egreso
        if($arrUserData["gestionDocumentosUsoBodega"]==2){

            /************************************/
            // Datos del movimiento
            $query = [
                'data'    => '
                    bodegas_movimientos_productos.idExistencia,
                    bodegas_movimientos_productos.idMovimiento,
                    bodegas_movimientos_productos.idEstadoIngreso,
                    bodegas_movimientos_productos.idBodegas,
                    bodegas_movimientos_productos.idProducto,
                    bodegas_movimientos_productos.Number',
                'table'   => 'bodegas_movimientos_productos',
                'join'    => 'LEFT JOIN bodegas_movimientos ON bodegas_movimientos.idMovimiento = bodegas_movimientos_productos.idMovimiento',
                'where'   => 'bodegas_movimientos.idFacturacion = ? AND bodegas_movimientos_productos.idProducto = ?',
                'params'  => [$PostData['idFacturacion'], $PostData['idProducto']],
                'group'   => '',
                'having'  => '',
                'order'   => ''
            ];
            // Preparo los datos
            $xParams       = ['query' => $query];
            // Ejecuto la query
            $rowMovimiento = $this->Base_GetByID($xParams);

            /************************************/
            // Variable
            $PostMovProd = array();
            // Productos
            $PostMovProd['idExistencia']    = $rowMovimiento['data']['idExistencia'];
            $PostMovProd['idMovimiento']    = $rowMovimiento['data']['idMovimiento'];
            $PostMovProd['idEstadoIngreso'] = $rowMovimiento['data']['idEstadoIngreso'];
            $PostMovProd['idBodegas']       = $rowMovimiento['data']['idBodegas'];
            $PostMovProd['idProducto']      = $rowMovimiento['data']['idProducto'];
            $PostMovProd['Number']          = $PostData['Number'];
            $PostMovProd['NumberOld']       = $rowMovimiento['data']['Number'];

            /************************************/
            // Se instancia
            $bodegasMovimientoProductos = new bodegasMovimientoProductos();
            $ResponseBodMovProd         = $bodegasMovimientoProductos->updateMov($PostMovProd, $this->getDBConn());
            if($ResponseBodMovProd['code'] != 200){
                $this->Base_transactionRollback();
                return $ResponseBodMovProd;
            }

        }

        /************************************/
        // Confirmar transacción
        $this->Base_transactionCommit();

        /************************************/
        // Retorno los datos
        return ['code' => 200, 'data' => $Response];

    }

    /*******************************************************************/
    // Borrar dato y archivos
    /*******************************************************************/
    public function DeleteProds($f3, $PostData): array {

        /************************************/
        // Se instancia
        $arrUserData = $this->getUserData($f3);

        /************************************/
        // Se obtiene el ID
        $ExistenciaID = $this->Codification->encryptDecrypt('decrypt', $PostData['idExistencia']);
        if (!$this->isValidDecrypted($ExistenciaID, 'id')) {
            return ['code' => 400, 'message' => 'Registro inválido'];
        }

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idFacturacion, idProducto',
            'table'   => 'facturacion_listado_productos',
            'join'    => '',
            'where'   => 'idExistencia = ?',
            'params'  => [$ExistenciaID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams        = ['query' => $query];
        // Ejecuto la query
        $rowFacturacion = $this->Base_GetByID($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($rowFacturacion['status'] === false) {
            $this->Base_transactionRollback();
            return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $rowFacturacion['error']];
        }

        /************************************/
        // Se genera la query
        $query = [
            'files'       => '',
            'table'       => 'facturacion_listado_productos',
            'where'       => 'idExistencia',
            'SubCarpeta'  => '',
            'Post'        => $PostData
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $Response = $this->Base_delete($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            $this->Base_transactionRollback();
            return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $Response['error']];
        }

        /************************************/
        // Se actualizan los datos de la factura
        $gestionDocumentos  = new gestionDocumentos();
        $ResponseGestionDoc = $gestionDocumentos->updateFact(2, $rowFacturacion['data']['idFacturacion'], $this->getDBConn());
        if($ResponseGestionDoc['code'] != 200){
            $this->Base_transactionRollback();
            return $ResponseGestionDoc;
        }

        /************************************/
        //Movimiento de bodegas
        //permite la interaccion con la bodega, para generar documentos de ingreso o egreso
        if($arrUserData["gestionDocumentosUsoBodega"]==2){

            /************************************/
            // Datos del movimiento
            $query = [
                'data'    => 'bodegas_movimientos_productos.idExistencia',
                'table'   => 'bodegas_movimientos_productos',
                'join'    => 'LEFT JOIN bodegas_movimientos ON bodegas_movimientos.idMovimiento = bodegas_movimientos_productos.idMovimiento',
                'where'   => 'bodegas_movimientos.idFacturacion = ? AND bodegas_movimientos_productos.idProducto = ?',
                'params'  => [$rowFacturacion['data']['idFacturacion'], $rowFacturacion['data']['idProducto']],
                'group'   => '',
                'having'  => '',
                'order'   => ''
            ];
            // Preparo los datos
            $xParams       = ['query' => $query];
            // Ejecuto la query
            $rowMovimiento = $this->Base_GetByID($xParams);

            /************************************/
            // Productos
            $ExistenciaID = $this->Codification->encryptDecrypt('encrypt', $rowMovimiento['data']['idExistencia']);
            if (!$this->isValidDecrypted($ExistenciaID, 'text')) {
                $this->Base_transactionRollback();
                return ['code' => 400, 'message' => 'Registro inválido'];
            }
            // Variable
            $PostMovProd = array();
            // Obtengo el dato codificado
            $PostMovProd['idExistencia'] = $ExistenciaID['data'];

            /************************************/
            // Se instancia
            $bodegasMovimientoProductos = new bodegasMovimientoProductos();
            $ResponseBodMovProd         = $bodegasMovimientoProductos->deleteMov($PostMovProd, $this->getDBConn());
            if($ResponseBodMovProd['code'] != 200){
                $this->Base_transactionRollback();
                return $ResponseBodMovProd;
            }

        }

        /************************************/
        // Confirmar transacción
        $this->Base_transactionCommit();

        /************************************/
        // Retorno los datos
        return ['code' => 200, 'data' => $Response];

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
            'ValidarNumero'             => 'idFacturacion,idEstadoIngreso,idBodegas,idProducto,Number,ValorTotal',
            'ValidarEntero'             => 'idFacturacion,idEstadoIngreso,idBodegas,idProducto',
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

    /*******************************************************************/
    // Se validan los datos
    /*******************************************************************/
    private function tsrxName(int $idTipo): string{
        // Normalizar y mapear tipo a nombre de permiso (más eficiente que switch)
        $tsrxMap = [
            1 => 'gestionDocumentosCompras',
            2 => 'gestionDocumentosVentas'
        ];
        // Por defecto usar ventas si no viene un tipo válido
        return $tsrxMap[$idTipo] ?? $tsrxMap[2];
    }


}
