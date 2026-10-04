<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class gestionDocumentosPagos extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataNumbers;
    private $DataDate;
    private $ServerServer;

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
		$this->DataDate       = new FunctionsDataDate();
		$this->ServerServer   = new FunctionsServerServer();
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
        // Se genera la query
        $query = [
            'data'    => 'idDocumentoPago AS ID,Nombre',
            'table'   => 'core_documentos_pago',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams          = ['query' => $query];
        // Ejecuto la query
        $arrDocumentoPago = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrDocumentoPago['status']){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $tsrxName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_Codification'    => $this->Codification,
                'Fnc_ServerServer'    => $this->ServerServer,
                /*=========== Datos Consultados ===========*/
                'rowData'           => $rowData['data'],
                'arrDocumentoPago'  => $arrDocumentoPago['data'],
                'idTipo'            => $idTipo,
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Pagos-formNew.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrDocumentoPago]);
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
                facturacion_listado_pagos.idPago,
                facturacion_listado_pagos.N_Doc,
                facturacion_listado_pagos.MontoPagado,
                facturacion_listado_pagos.FechaPago,
                usuarios_listado.Nombre AS UsuarioPago,
                core_documentos_pago.Nombre AS DocPago',
            'table'   => 'facturacion_listado_pagos',
            'join'    => '
                LEFT JOIN usuarios_listado     ON usuarios_listado.idUsuario            = facturacion_listado_pagos.idUsuario
                LEFT JOIN core_documentos_pago ON core_documentos_pago.idDocumentoPago  = facturacion_listado_pagos.idDocumentoPago',
            'where'   => 'facturacion_listado_pagos.idFacturacion = ?',
            'params'  => [$FacturacionID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'facturacion_listado_pagos.idPago ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $arrPagos = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrPagos['status']){

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $tsrxName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'    => $this->Codification,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                'Fnc_DataDate'        => $this->DataDate,
                /*=========== Datos Consultados ===========*/
                'rowData'     => $rowData['data'],
                'arrPagos'    => $arrPagos['data'],
                'idTipo'      => $idTipo,
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Pagos-UpdateList.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrPagos]);
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
        $PagoID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($PagoID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                facturacion_listado_pagos.idPago,
                facturacion_listado_pagos.idFacturacion,
                facturacion_listado_pagos.idDocumentoPago,
                facturacion_listado_pagos.N_Doc,
                facturacion_listado_pagos.MontoPagado,
                facturacion_listado_pagos.FechaPago,
                usuarios_listado.Nombre AS UsuarioPago',
            'table'   => 'facturacion_listado_pagos',
            'join'    => 'LEFT JOIN usuarios_listado ON usuarios_listado.idUsuario = facturacion_listado_pagos.idUsuario',
            'where'   => 'facturacion_listado_pagos.idPago = ?',
            'params'  => [$PagoID['data']],
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
            'data'    => 'idDocumentoPago AS ID,Nombre',
            'table'   => 'core_documentos_pago',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams          = ['query' => $query];
        // Ejecuto la query
        $arrDocumentoPago = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrDocumentoPago['status']){
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
                'Fnc_ServerServer'  => $this->ServerServer,
                /*=========== Datos Consultados ===========*/
                'rowData'           => $rowData['data'],
                'arrDocumentoPago'  => $arrDocumentoPago['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Pagos-formEdit.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrDocumentoPago]);
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
        // Usuario creador
        $_POST['idUsuario'] = $f3->get('SESSION.DataInfo.UserID');

        /************************************/
        // Envio los datos recibidos
        $ResponseUp = $this->insertPago($_POST);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if($ResponseUp['code'] != 200){
            Response::error($ResponseUp['message'], $ResponseUp['code'], $ResponseUp['error'] ?? '');
        }

        /************************************/
        // Si es un ID numérico, encripta y envía con código 200 (OK)
        Response::success($ResponseUp['data']['data']);

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
        // Usuario creador
        $_POST['idUsuario'] = $f3->get('SESSION.DataInfo.UserID');

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
            facturacion_listado.idFacturacion,
            facturacion_listado.ValorTotal,
            (SELECT SUM(MontoPagado) FROM facturacion_listado_pagos WHERE idFacturacion='.$_POST['idFacturacion'].' AND idPago!='.$_POST['idPago'].') AS MontoPagado',
            'table'   => 'facturacion_listado',
            'join'    => '',
            'where'   => 'idFacturacion = ?',
            'params'  => [$_POST['idFacturacion']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($rowData['status'] === false) {
            $this->Base_transactionRollback();
            return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $rowData['error']];
        }

        /************************************/
        //Se verifica si el monto es superior al valor del documento
        if(isset($rowData['data']['ValorTotal'], $rowData['data']['MontoPagado'], $_POST['MontoPagado'])&&$rowData['data']['ValorTotal']<($rowData['data']['MontoPagado']+$_POST['MontoPagado'])){
            $this->Base_transactionRollback();
            Response::error('Ha ingresado un monto superior al valor total del documento', 500);
        }else{
            /************************************/
            // Se genera la query
            $query = [
                'data'      => 'idPago,idFacturacion,idUsuario,idDocumentoPago,N_Doc,MontoPagado,FechaPago',
                'required'  => 'idPago,idFacturacion,idUsuario,idDocumentoPago,MontoPagado,FechaPago',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'facturacion_listado_pagos',
                'where'     => 'idPago',
                'Post'      => $_POST
            ];
            /************************************/
            // Se genera el chequeo
            $dataCheck_1 = $this->dataCheck_1($_POST);
            // Preparo los datos
            $xParams  = ['DataCheck' => $dataCheck_1, 'query' => $query];
            // Ejecuto la query
            $Response = $this->Base_update($xParams);

            /************************************/
            // Si falla la ejecucion, se revierte de inmediato
            if ($Response['status'] === false) {
                $this->Base_transactionRollback();
                Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
            }

            /************************************/
            // Se actualiza el estado de la factura
            $ResponseUp = $this->updatePago($_POST['idFacturacion'], $this->getDBConn());

            /************************************/
            // Si falla la la ejecucion, se muestra alerta
            if($ResponseUp['code'] != 200){
                $this->Base_transactionRollback();
                Response::error($ResponseUp['message'], $ResponseUp['code'], $ResponseUp['error'] ?? '');
            }

            /************************************/
            // Confirmar transacción
            $this->Base_transactionCommit();

            /************************************/
            // Devuelvo $Response con código 200 (OK)
            Response::success($Response['data']);

        }

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
        // Se obtiene el ID
        $PagoID = $this->Codification->encryptDecrypt('decrypt', $dataDelete['idPago']);
        if (!$this->isValidDecrypted($PagoID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idFacturacion',
            'table'   => 'facturacion_listado_pagos',
            'join'    => '',
            'where'   => 'idPago = ?',
            'params'  => [$PagoID['data']],
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
            Response::error('Error al operar con la Base de Datos', 500, $rowFacturacion['error']);
        }

        /************************************/
        // Se genera la query
        $query = [
            'files'       => '',
            'table'       => 'facturacion_listado_pagos',
            'where'       => 'idPago',
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
        // Se actualiza el estado de la factura
        $ResponseUp = $this->updatePago($rowFacturacion['data']['idFacturacion'], $this->getDBConn());

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if($ResponseUp['code'] != 200){
            $this->Base_transactionRollback();
            Response::error($ResponseUp['message'], $ResponseUp['code'], $ResponseUp['error'] ?? '');
        }

        /************************************/
        // Confirmar transacción
        $this->Base_transactionCommit();

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data']);

    }

    /******************************************************************************/
    /*                             EJECUCION OTROS                                */
    /******************************************************************************/
    /*******************************************************************/
    // Se actualizan los montos
    /*******************************************************************/
    public function updatePago($FacturacionID, $NewDBConn = null): array{

        /************************************/
        // Verifico si se ejecuta otro hilo
        $DBConn = $NewDBConn ?? $this->getDBConn();

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
            facturacion_listado.idFacturacion,
            facturacion_listado.ValorTotal,
            (SELECT SUM(MontoPagado) FROM facturacion_listado_pagos WHERE idFacturacion='.$FacturacionID.') AS MontoPagado',
            'table'   => 'facturacion_listado',
            'join'    => '',
            'where'   => 'idFacturacion = ?',
            'params'  => [$FacturacionID],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query, 'newBDConn' => $DBConn];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($rowData['status'] === false) {
            return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $rowData['error']];
        }

        /************************************/
        //Se determina si esta pagado
        if(isset($rowData['data']['ValorTotal'], $rowData['data']['MontoPagado'])&&$rowData['data']['ValorTotal']<=$rowData['data']['MontoPagado']){
            $idEstadoPago = 2; // Pagado
        }else{
            $idEstadoPago = 1; //No Pagado
        }
        // Se agrega respuesta
        $arrTareas = [
            'idFacturacion'   => $rowData['data']['idFacturacion'],
            'idEstadoPago'    => $idEstadoPago,
            'MontoPagado'     => $rowData['data']['MontoPagado'] ?? 0,
        ];
        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idFacturacion,idEstadoPago,MontoPagado',
            'required'  => 'idFacturacion,idEstadoPago,MontoPagado',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'facturacion_listado',
            'where'     => 'idFacturacion',
            'Post'      => $arrTareas
        ];
        /************************************/
        // Se genera el chequeo
        $dataCheck_2 = $this->dataCheck_2($arrTareas);
        // Preparo los datos
        $xParams = ['DataCheck' => $dataCheck_2, 'query' => $query, 'newBDConn' => $DBConn];
        // Ejecuto la query
        $Response = $this->Base_update($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $Response['error']];
        }

        /************************************/
        // Retorno los datos
        return ['code' => 200, 'data' => $Response];

    }

    /*******************************************************************/
    // Se genera el pago
    /*******************************************************************/
    public function insertPago($Data, $NewDBConn = null){

        /************************************/
        // Verifico si se ejecuta otro hilo
        $DBConn = $NewDBConn ?? $this->getDBConn();

        /************************************/
        // Se inicia la transacción solo si corresponde
        if($NewDBConn === null){
            $this->Base_transactionBegin(['newBDConn' => $DBConn]);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
            facturacion_listado.idFacturacion,
            facturacion_listado.ValorTotal,
            (SELECT SUM(MontoPagado) FROM facturacion_listado_pagos WHERE idFacturacion='.$Data['idFacturacion'].') AS MontoPagado',
            'table'   => 'facturacion_listado',
            'join'    => '',
            'where'   => 'idFacturacion = ?',
            'params'  => [$Data['idFacturacion']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query, 'newBDConn' => $DBConn];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($rowData['status'] === false) {
            $this->Base_transactionRollback(['newBDConn' => $DBConn]);
            return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $rowData['error']];
        }

        /************************************/
        //Se verifica si el monto es superior al valor del documento
        if(isset($rowData['data']['ValorTotal'], $Data['MontoPagado'])&&$rowData['data']['ValorTotal']<($rowData['data']['MontoPagado']+$Data['MontoPagado'])){
            $this->Base_transactionRollback(['newBDConn' => $DBConn]);
            return ['code' => 500, 'message' => 'Ha ingresado un monto superior al valor total del documento'];
        }else{
            /************************************/
            // Se genera la query
            $query = [
                'data'      => 'idFacturacion,idUsuario,idDocumentoPago,N_Doc,MontoPagado,FechaPago',
                'required'  => 'idFacturacion,idUsuario,idDocumentoPago,MontoPagado,FechaPago',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'facturacion_listado_pagos',
                'Post'      => $Data
            ];
            /************************************/
            // Se genera el chequeo
            $dataCheck_1 = $this->dataCheck_1($Data);
            // Preparo los datos
            $xParams  = ['DataCheck' => $dataCheck_1, 'query' => $query, 'newBDConn' => $DBConn];
            // Ejecuto la query
            $Response = $this->Base_insert($xParams);

            /************************************/
            // Si falla la ejecucion, se revierte de inmediato
            if ($Response['status'] === false) {
                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                return ['code' => 500, 'message' => 'Error al operar con la Base de Datos', 'error' => $Response['error']];
            }

            /************************************/
            // Se actualiza el estado de la factura
            $ResponsePago = $this->updatePago($Data['idFacturacion'], $DBConn);
            if($ResponsePago['code'] != 200){
                $this->Base_transactionRollback(['newBDConn' => $DBConn]);
                return $ResponsePago;
            }

            /************************************/
            // Confirmar transacción solo si corresponde
            if($NewDBConn === null){
                $this->Base_transactionCommit(['newBDConn' => $DBConn]);
            }

            /************************************/
            // Si es un ID numérico, se envía con código 200 (OK)
            return ['code' => 200, 'data' => $Response];

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
            'ValidarNumero'             => 'idPago,idFacturacion,idUsuario,idDocumentoPago,N_Doc,MontoPagado',
            'ValidarEntero'             => 'idPago,idFacturacion,idUsuario,idDocumentoPago',
            'ValidarRut'                => '',
            'ValidarPatente'            => '',
            'ValidarFecha'              => 'FechaPago',
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
    private function dataCheck_2($POST){
        // Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => '',
            'ValidarNumero'             => 'idFacturacion,idEstadoPago,MontoPagado',
            'ValidarEntero'             => 'idFacturacion,idEstadoPago',
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
