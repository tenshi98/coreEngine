<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class mainDocMercantiles extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $CommonData;
    private $DataNumbers;
    private $DataDate;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
		$this->CommonData   = new FunctionsCommonData();
		$this->DataNumbers  = new FunctionsDataNumbers();
		$this->DataDate     = new FunctionsDataDate();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //Actualizacion campañas
    public function pagosPendientes($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $arrMenu  = $f3->get('SESSION.arrMenu');

        /*******************************************************************/
        //Variables
        $MainViewData = [
            'Count_DocMercantiles' => 0,
            'Data_ComprasTotal'    => '',
            'Data_ComprasListado'  => '',
            'Data_VentasTotal'     => '',
            'Data_VentasListado'   => '',
        ];
        //Se asignan datos a buscar
        $menuCounters = [
            'Gestión Documentos Mercantiles' => [
                'Compras'  => 'Count_DocMercantiles',
                'Ventas'   => 'Count_DocMercantiles',
            ],
        ];
        //Se recorren los permisos y se validan
        foreach ($menuCounters as $section => $names) {
            if (!empty($arrMenu[$section])) {
                foreach ($arrMenu[$section] as $asd) {
                    if (isset($names[$asd['Nombre']])) {
                        $MainViewData[$names[$asd['Nombre']]]++;
                    }
                }
            }
        }

        /*******************************************************************/
        //Se hacen las consultas
        /******************************************/
        if($MainViewData['Count_DocMercantiles']!=0){
            /******************************************/
            //Total Compras
            $query = [
                'data'    => '
                    SUM( ValorTotal ) AS ValorTotal,
	                SUM( MontoPagado ) AS MontoPagado',
                'table'   => 'facturacion_listado',
                'join'    => '',
                'where'   => 'idTipo = "1" AND idEstadoPago = "1"',
                'group'   => '',
                'having'  => '',
                'order'   => ''
            ];
            //Ejecuto la query
            $xParams = ['query' => $query];
            $MainViewData['Data_ComprasTotal'] = $this->Base_GetByID($xParams);

            /******************************************/
            //Listado Compras
            $query = [
                'data'    => '
                    facturacion_listado.ValorTotal,
                    facturacion_listado.MontoPagado,

                    entidades_listado.Nombre AS EntidadesNombre,
                    entidades_listado.ApellidoPat AS EntidadesApellido,
                    entidades_listado.RazonSocial AS EntidadesRazonSocial,
                    entidades_listado.Nick AS EntidadesNick',
                'table'   => 'facturacion_listado',
                'join'    => 'LEFT JOIN entidades_listado ON entidades_listado.idEntidad = facturacion_listado.idEntidad',
                'where'   => 'facturacion_listado.idTipo = "1" AND facturacion_listado.idEstadoPago = "1"',
                'group'   => '',
                'having'  => '',
                'order'   => 'facturacion_listado.Creacion_fecha DESC, facturacion_listado.N_Doc DESC, facturacion_listado.idFacturacion DESC',
                'limit'   => 10
            ];
            //Ejecuto la query
            $xParams                             = ['query' => $query];
            $MainViewData['Data_ComprasListado'] = $this->Base_GetList($xParams);

            /******************************************/
            //Total Compras
            $query = [
                'data'    => '
                    SUM( ValorTotal ) AS ValorTotal,
	                SUM( MontoPagado ) AS MontoPagado',
                'table'   => 'facturacion_listado',
                'join'    => '',
                'where'   => 'idTipo = "2" AND idEstadoPago = "1"',
                'group'   => '',
                'having'  => '',
                'order'   => ''
            ];
            //Ejecuto la query
            $xParams                          = ['query' => $query];
            $MainViewData['Data_VentasTotal'] = $this->Base_GetByID($xParams);

            /******************************************/
            //Listado Ventas
            $query = [
                'data'    => '
                    facturacion_listado.N_Doc,
                    facturacion_listado.Creacion_fecha,
                    facturacion_listado.ValorTotal,
                    facturacion_listado.MontoPagado,

                    entidades_listado.Nombre AS EntidadesNombre,
                    entidades_listado.ApellidoPat AS EntidadesApellido,
                    entidades_listado.RazonSocial AS EntidadesRazonSocial,
                    entidades_listado.Nick AS EntidadesNick,
                    core_documentos_mercantiles.Nombre AS Documento',
                'table'   => 'facturacion_listado',
                'join'    => '
                    LEFT JOIN entidades_listado             ON entidades_listado.idEntidad                = facturacion_listado.idEntidad
                    LEFT JOIN core_documentos_mercantiles   ON core_documentos_mercantiles.idDocumentos   = facturacion_listado.idDocumentos',
                'where'   => 'facturacion_listado.idTipo = "2" AND facturacion_listado.idEstadoPago = "1"',
                'group'   => '',
                'having'  => '',
                'order'   => 'facturacion_listado.Creacion_fecha DESC, facturacion_listado.N_Doc DESC, facturacion_listado.idFacturacion DESC',
                'limit'   => 10
            ];
            //Ejecuto la query
            $xParams                            = ['query' => $query];
            $MainViewData['Data_VentasListado'] = $this->Base_GetList($xParams);
        }

        /******************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*===========   Funcionalidad   ===========*/
            'Fnc_CommonData'      => $this->CommonData,
            'Fnc_DataDate'        => $this->DataDate,
            'Fnc_DataNumbers'     => $this->DataNumbers,
            /*=========== Datos Consultados ===========*/
            'MainViewData'    => $MainViewData,
        ];

        //Se instancia la vista
        $view = new View;
        echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/main-doc-mercantiles-update.php'); // Vista
    }


}
