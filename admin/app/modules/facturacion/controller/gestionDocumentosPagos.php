<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class gestionDocumentosPagos extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataNumbers;
    private $DataDate;
    private $ServerServer;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
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
    /******************************************************************************/
    //Listar Todo
    public function New_1($f3, $params){$this->New($f3, $params, 1);}
    public function New_2($f3, $params){$this->New($f3, $params, 2);}
    //Listar Todo
    public function UpdateList_1($f3, $params){$this->UpdateList($f3, $params, 1);}
    public function UpdateList_2($f3, $params){$this->UpdateList($f3, $params, 2);}
    //Listar Todo
    public function GetID_1($f3, $params){$this->GetID($f3, $params, 1);}
    public function GetID_2($f3, $params){$this->GetID($f3, $params, 2);}

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //Crear nuevo
    public function New($f3, $params, $idTipo){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se verifica movimiento
        switch ($idTipo) {
            case 1: $tsrxName = 'gestionDocumentosCompras';break;  //Compras
            case 2: $tsrxName = 'gestionDocumentosVentas';break;   //Ventas
        }

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => 'idFacturacion',
            'table'   => 'facturacion_listado',
            'join'    => '',
            'where'   => 'idFacturacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
            'data'    => 'idDocumentoPago AS ID,Nombre',
            'table'   => 'core_documentos_pago',
            'join'    => '',
            'where'   => 'idDocumentoPago!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams          = ['query' => $query];
        $arrDocumentoPago = $this->Base_GetList($xParams);

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
                'UserAccess'    => $arrLevel[$tsrxName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_Codification'    => $this->Codification,
                'Fnc_ServerServer'    => $this->ServerServer,
                /*=========== Datos Consultados ===========*/
                'rowData'           => $rowData,
                'arrDocumentoPago'  => $arrDocumentoPago,
                'idTipo'            => $idTipo,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Pagos-formNew.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }
    /******************************************************************************/
    //List
    public function UpdateList($f3, $params, $idTipo){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se verifica movimiento
        switch ($idTipo) {
            case 1: $tsrxName = 'gestionDocumentosCompras';break;  //Compras
            case 2: $tsrxName = 'gestionDocumentosVentas';break;   //Ventas
        }

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => 'idEstadoPago',
            'table'   => 'facturacion_listado',
            'join'    => '',
            'where'   => 'idFacturacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
            'where'   => 'facturacion_listado_pagos.idFacturacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'facturacion_listado_pagos.idPago ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams  = ['query' => $query];
        $arrPagos = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($arrPagos)){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$tsrxName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'    => $this->Codification,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                'Fnc_DataDate'        => $this->DataDate,
                /*=========== Datos Consultados ===========*/
                'rowData'     => $rowData,
                'arrPagos'    => $arrPagos,
                'idTipo'      => $idTipo,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Pagos-UpdateList.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //Edit
    public function GetID($f3, $params, $idTipo){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se verifica movimiento
        switch ($idTipo) {
            case 1: $tsrxName = 'gestionDocumentosCompras';break;  //Compras
            case 2: $tsrxName = 'gestionDocumentosVentas';break;   //Ventas
        }

        /******************************************/
        //Se genera la query
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
            'where'   => 'facturacion_listado_pagos.idPago = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
            'data'    => 'idDocumentoPago AS ID,Nombre',
            'table'   => 'core_documentos_pago',
            'join'    => '',
            'where'   => 'idDocumentoPago!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams          = ['query' => $query];
        $arrDocumentoPago = $this->Base_GetList($xParams);

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
                'UserAccess'    => $arrLevel[$tsrxName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'    => $this->FormInputs,
                'Fnc_Codification'  => $this->Codification,
                'Fnc_DataNumbers'   => $this->DataNumbers,
                'Fnc_ServerServer'  => $this->ServerServer,
                /*=========== Datos Consultados ===========*/
                'rowData'           => $rowData,
                'arrDocumentoPago'  => $arrDocumentoPago,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Pagos-formEdit.php');
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

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => '
            facturacion_listado.idFacturacion,
            facturacion_listado.ValorTotal,
            (SELECT SUM(MontoPagado) FROM facturacion_listado_pagos WHERE idFacturacion='.$_POST['idFacturacion'].') AS MontoPagado',
            'table'   => 'facturacion_listado',
            'join'    => '',
            'where'   => 'idFacturacion = "'.$_POST['idFacturacion'].'"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /******************************/
        //Se verifica si el monto es superior al valor del documento
        if(isset($rowData['ValorTotal'], $_POST['MontoPagado'])&&$rowData['ValorTotal']<($rowData['MontoPagado']+$_POST['MontoPagado'])){
            echo Response::sendData(500, 'Ha ingresado un monto superior al valor total del documento');
        }else{
            /******************************/
            //Se genera el chequeo
            $DataCheck = $this->dataCheck($_POST);

            /******************************/
            //Se genera la query
            $query = [
                'data'      => 'idFacturacion,idUsuario,idDocumentoPago,N_Doc,MontoPagado,FechaPago',
                'required'  => 'idFacturacion,idUsuario,idDocumentoPago,MontoPagado,FechaPago',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'facturacion_listado_pagos',
                'Post'      => $_POST
            ];
            //Ejecuto la query
            //Ejecuto la query
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        $Response = $this->Base_insert($xParams);

            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un ID numérico o algún otro valor.
            if (is_numeric($Response)) {
                /******************************/
                // Se actualiza el estado de la factura
                $this->updateFact($_POST['idFacturacion']);
                /******************************/
                // Si es un ID numérico, se envía con código 200 (OK)
                echo Response::sendData(200, $Response);
            } else {
                // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
                // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
                echo Response::sendData(500, $Response);
            }

        }

    }

    /******************************************************************************/
    //Editar por put (solo modificar datos)
    //Editar por post (modificar y subir archivos)
    public function Update(){
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            /*******************************************************************/
            //Se genera la query
            $query = [
                'data'    => '
                facturacion_listado.idFacturacion,
                facturacion_listado.ValorTotal,
                (SELECT SUM(MontoPagado) FROM facturacion_listado_pagos WHERE idFacturacion='.$_POST['idFacturacion'].' AND idPago!='.$_POST['idPago'].') AS MontoPagado',
                'table'   => 'facturacion_listado',
                'join'    => '',
                'where'   => 'idFacturacion = "'.$_POST['idFacturacion'].'"',
                'group'   => '',
                'having'  => '',
                'order'   => ''
            ];
            //Ejecuto la query
            $xParams = ['query' => $query];
            $rowData = $this->Base_GetByID($xParams);

            /******************************/
            //Se verifica si el monto es superior al valor del documento
            if(isset($rowData['ValorTotal'], $rowData['MontoPagado'], $_POST['MontoPagado'])&&$rowData['ValorTotal']<($rowData['MontoPagado']+$_POST['MontoPagado'])){
                echo Response::sendData(500, 'Ha ingresado un monto superior al valor total del documento');
            }else{
                /******************************/
                //Se genera el chequeo
                $DataCheck = $this->dataCheck($_POST);

                /******************************/
                //Se genera la query
                $query = [
                    'data'      => 'idPago,idFacturacion,idUsuario,idDocumentoPago,N_Doc,MontoPagado,FechaPago',
                    'required'  => 'idFacturacion,idUsuario,idDocumentoPago,MontoPagado,FechaPago',
                    'unique'    => '',
                    'encode'    => '',
                    'table'     => 'facturacion_listado_pagos',
                    'where'     => 'idPago',
                    'Post'      => $_POST
                ];
                //Ejecuto la query
                $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
                $Response = $this->Base_update($xParams);

                /******************************/
                // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
                if ($Response===true) {
                    /******************************/
                    // Se actualiza el estado de la factura
                    $this->updateFact($_POST['idFacturacion']);
                    /******************************/
                    // Devuelvo $Response con código 200 (OK)
                    echo Response::sendData(200, $Response);
                } else {
                    // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
                    // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
                    echo Response::sendData(500, $Response);
                }

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

            /******************************************/
            //Se parsean los datos
            parse_str(file_get_contents("php://input"),$dataDelete);

            /******************************************/
            //Se genera la query
            $query = [
                'data'    => 'idFacturacion',
                'table'   => 'facturacion_listado_pagos',
                'join'    => '',
                'where'   => 'idPago = "'.$this->Codification->encryptDecrypt('decrypt', $dataDelete['idPago']).'"',
                'group'   => '',
                'having'  => '',
                'order'   => ''
            ];
            //Ejecuto la query
            $xParams        = ['query' => $query];
            $rowFacturacion = $this->Base_GetByID($xParams);

            /******************************/
            //Se genera la query
            $query = [
                'files'       => '',
                'table'       => 'facturacion_listado_pagos',
                'where'       => 'idPago',
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
                // Se actualiza el estado de la factura
                $this->updateFact($rowFacturacion['idFacturacion']);
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
            'ValidarLargoMinimo'        => '',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => '',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => '',
            'ValidarEspaciosVacios'     => '',
            'ValidarMayusculas'         => '',
            'ValidarCoincidencias'      => '',
            'Post'                      => $POST,
        ];
        //Devuelvo
        return $DataChecking;
    }

    /******************************************************************************/
    /*                             EJECUCION OTROS                                */
    /******************************************************************************/
    //Se actualizan los montos
    public function updateFact($FacturacionID){

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => '
            facturacion_listado.idFacturacion,
            facturacion_listado.ValorTotal,
            (SELECT SUM(MontoPagado) FROM facturacion_listado_pagos WHERE idFacturacion='.$FacturacionID.') AS MontoPagado',
            'table'   => 'facturacion_listado',
            'join'    => '',
            'where'   => 'idFacturacion = "'.$FacturacionID.'"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /******************************/
        //Se determina si esta pagado
        if(isset($rowData['ValorTotal'], $rowData['MontoPagado'])&&$rowData['ValorTotal']<=$rowData['MontoPagado']){
            $idEstadoPago = 2; //Pagado
        }else{
            $idEstadoPago = 1; //No Pagado
        }
        //Se agrega respuesta
        $arrTareas = [
            'idFacturacion'   => $rowData['idFacturacion'],
            'idEstadoPago'    => $idEstadoPago,
            'MontoPagado'     => $rowData['MontoPagado'],
        ];
        /******************************/
        //Se genera la query
        $query = [
            'data'      => 'idFacturacion,idEstadoPago,MontoPagado',
            'required'  => 'idFacturacion,idEstadoPago,MontoPagado',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'facturacion_listado',
            'where'     => 'idFacturacion',
            'Post'      => $arrTareas
        ];
        //Ejecuto la query
        $xParams       = ['DataCheck' => '', 'query' => $query];
        $ResponseTarea = $this->Base_update($xParams);

    }

}