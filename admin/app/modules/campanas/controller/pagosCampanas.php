<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class pagosCampanas extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataDate;
    private $DataNumbers;
    private $CommonData;
    private $ServerServer;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'pagosCampanas';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->DataDate       = new FunctionsDataDate();
		$this->DataNumbers    = new FunctionsDataNumbers();
		$this->CommonData     = new FunctionsCommonData();
		$this->ServerServer   = new FunctionsServerServer();
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
            'data'    => 'idDocumentos AS ID,Nombre',
            'table'   => 'core_documentos_mercantiles',
            'join'    => '',
            'where'   => 'idDocumentos!=3',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams       = ['query' => $query];
        $arrDocumentos = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idEntidad AS ID,CONCAT((CASE WHEN ( Nombre = "" OR Nombre IS NULL ) THEN RazonSocial ELSE CONCAT(Nombre,IFNULL( CONCAT( " ", ApellidoPat ), "" )) END ),CASE WHEN ( Nick = "" OR Nick IS NULL ) THEN "" ELSE CONCAT( " (", Nick, ")" ) END ) AS Nombre ',
            'table'   => 'entidades_listado',
            'join'    => '',
            'where'   => 'idEstado=1 AND idTipo=2',
            'group'   => '',
            'having'  => '',
            'order'   => 'ApellidoPat ASC,Nombre ASC,RazonSocial ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams      = ['query' => $query];
        $arrEntidades = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idCampana AS ID,CONCAT( Nombre, " Fecha ", Fecha ) AS Nombre',
            'table'   => 'campanas_listado',
            'join'    => '',
            'where'   => 'idCampana!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Fecha DESC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams     = ['query' => $query];
        $arrCampanas = $this->Base_GetList($xParams);

        /******************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Informe Pagos',
            'PageDescription' => 'Informe Pagos.',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'      => $UserData,
            'UserAccess'    => $arrLevel[$this->controllerName],
            /*===========   Funcionalidad   ===========*/
            'Fnc_FormInputs'   => $this->FormInputs,
            'Fnc_Codification' => $this->Codification,
            /*=========== Datos Consultados ===========*/
            'arrDocumentos'  => $arrDocumentos,
            'arrEntidades'   => $arrEntidades,
            'arrCampanas'    => $arrCampanas,
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista($UserData['TypeSession'], 1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
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
        $WhereData_int     = 'idDocumentos,idEntidad,idFacturacion';  //Datos búsqueda exacta
        $WhereData_string  = 'N_Doc';                                 //Datos búsqueda relativa
        $WhereData_between = 'Creacion_fecha-F_Inicio-F_Termino';     //Datos búsqueda Between
        $WhereData_int2     = 'idCampana';                            //Datos búsqueda exacta
        $whereInt          = '';                                      //se crea cadena
        /******************************************/
        //agrego variable busqueda
        $whereInt = $this->searchWhere($whereInt, $WhereData_int, 'facturacion_listado', 1);
        $whereInt = $this->searchWhere($whereInt, $WhereData_string, 'facturacion_listado', 2);
        $whereInt = $this->searchWhere($whereInt, $WhereData_between, 'facturacion_listado', 3);
        $whereInt = $this->searchWhere($whereInt, $WhereData_int2, 'campanas_listado', 1);
        //Verifico si esta vacio
        $whereInt2 = $whereInt ? $whereInt . ' AND facturacion_listado.idTipo = "2" AND facturacion_listado.idEstadoPago = "1" AND facturacion_listado.idDocumentos != "3"' : 'facturacion_listado.idTipo = "2" AND facturacion_listado.idEstadoPago = "1" AND facturacion_listado.idDocumentos != "3"';

        /******************************/
        //Se genera la query
        $query = [
            'data'    => '
                facturacion_listado.idFacturacion,
                facturacion_listado.N_Doc,
                facturacion_listado.Creacion_fecha,
                facturacion_listado.ValorTotal,
                facturacion_listado.MontoPagado,

                entidades_listado.Nombre AS EntidadNombre,
                entidades_listado.ApellidoPat AS EntidadApellido,
                entidades_listado.RazonSocial AS EntidadRazonSocial,
                entidades_listado.Nick AS EntidadNick,
                core_documentos_mercantiles.Nombre AS Documento,
                campanas_listado.Nombre AS Campana',
            'table'   => 'facturacion_listado',
            'join'    => '
                LEFT JOIN entidades_listado             ON entidades_listado.idEntidad                = facturacion_listado.idEntidad
                LEFT JOIN core_documentos_mercantiles   ON core_documentos_mercantiles.idDocumentos   = facturacion_listado.idDocumentos
                LEFT JOIN campanas_listado_partidas     ON campanas_listado_partidas.idFacturacion    = facturacion_listado.idFacturacion
                LEFT JOIN campanas_listado              ON campanas_listado.idCampana                 = campanas_listado_partidas.idCampana',
            'where'   => $whereInt2,
            'group'   => '',
            'having'  => '',
            'order'   => 'facturacion_listado.Creacion_fecha DESC, facturacion_listado.N_Doc DESC, facturacion_listado.idFacturacion DESC',
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
                'TableTitle'      => 'Informe Pagos',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'    => $this->Codification,
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                'Fnc_CommonData'      => $this->CommonData,
                /*=========== Datos Consultados ===========*/
                'arrList'       => $arrList,
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
                facturacion_listado.idFacturacion,
                facturacion_listado.N_Doc,
                facturacion_listado.ValorTotal,
                core_documentos_mercantiles.Nombre AS Documento',
            'table'   => 'facturacion_listado',
            'join'    => 'LEFT JOIN core_documentos_mercantiles   ON core_documentos_mercantiles.idDocumentos   = facturacion_listado.idDocumentos',
            'where'   => 'facturacion_listado.idFacturacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'        => 'Resumen Pagos',
                'PageDescription'  => 'Resumen Pagos.',
                'PageAuthor'       => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'     => ConfigAPP::SOFTWARE['SoftwareName'],
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_Codification'    => $this->Codification,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData,
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
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //Listar Todo
    public function New_2($f3, $params){$this->New($f3, $params, 2);}
    //Listar Todo
    public function UpdateList_2($f3, $params){$this->UpdateListPagos($f3, $params, 2);}
    //Listar Todo
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
                'UserAccess'    => $arrLevel[$this->controllerName],
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
    public function UpdateListPagos($f3, $params, $idTipo){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

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
                'UserAccess'    => $arrLevel[$this->controllerName],
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
                'UserAccess'    => $arrLevel[$this->controllerName],
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


}
