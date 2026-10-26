<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class gestionDocumentosWidgets extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $CommonData;
    private $DataNumbers;
    private $DataDate;

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
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
    /*                                 EJECUCION                                  */
    /******************************************************************************/
    /*******************************************************************/
    // Carga del Widget
    /*******************************************************************/
    public function loadWidgets(){

        // Variables
        // Cada sub-widget (Compras / Ventas) tiene su propia vista y su propio endpoint,
        // de modo que pueden activarse/desactivarse y ordenarse de forma independiente.
        $Data['Menu_Name']   = 'Gestión Documentos Mercantiles';
        // Titulo que se muestra una sola vez sobre el bloque en la pantalla principal
        $Data['Menu_Title']  = 'Compras / Ventas';
        $Data['Menu_Value']  = [
            'Compras'  => ['Ruta' => '../app/modules/facturacion/widgets/widgets-doc-compras.php', 'Permiso' => 'Compras'],
            'Ventas'   => ['Ruta' => '../app/modules/facturacion/widgets/widgets-doc-ventas.php',  'Permiso' => 'Ventas'],
        ];

        // Retorno los datos
        return $Data;
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /*******************************************************************/
    // Pagos pendientes de los documentos mercantiles (Compras / Ventas)
    // Separamos en dos endpoints para que cada sub-widget (Compras y Ventas)
    // se renderice de forma independiente en la pantalla principal.
    /*******************************************************************/
    public function pagosPendientesCompras($f3, $params){
        $this->pagosPendientesData($f3, 1);
    }
    public function pagosPendientesVentas($f3, $params){
        $this->pagosPendientesData($f3, 2);
    }

    /*******************************************************************/
    // Carga de datos de pagos pendientes segun el tipo (1=Compras, 2=Ventas)
    /*******************************************************************/
    private function pagosPendientesData($f3, $idTipo){

        /************************************/
        // Se llaman los datos
        $arrMenu  = $f3->get('SESSION.arrMenu');

        /************************************/
        // Nombre del widget/permiso y vista segun el tipo
        $NombreWidget = ($idTipo==1) ? 'Compras' : 'Ventas';
        $VistaWidget  = ($idTipo==1) ? 'widgets-doc-compras-update.php' : 'widgets-doc-ventas-update.php';

        /************************************/
        // Variables
        $MainViewData = [
            'Count_DocMercantiles' => 0,
            'Data_Total'           => '',
            'Data_Listado'         => '',
        ];
        // Se asignan datos a buscar (solo el widget solicitado)
        $menuCounters = [
            'Gestión Documentos Mercantiles' => [
                $NombreWidget => 'Count_DocMercantiles',
            ],
        ];
        // Se recorren los permisos y se validan
        foreach ($menuCounters as $section => $names) {
            if (!empty($arrMenu[$section])) {
                foreach ($arrMenu[$section] as $asd) {
                    if (isset($names[$asd['Nombre']])) {
                        $MainViewData[$names[$asd['Nombre']]]++;
                    }
                }
            }
        }

        /************************************/
        // Se hacen las consultas
        if($MainViewData['Count_DocMercantiles']!=0){
            /************************************/
            //Total (Compras si idTipo=1, Ventas si idTipo=2)
            $query = [
                'data'    => '
                    SUM( ValorTotal ) AS ValorTotal,
                    SUM( MontoPagado ) AS MontoPagado',
                'table'   => 'facturacion_listado',
                'join'    => '',
                'where'   => 'idTipo = ? AND idEstadoPago = ?',
                'params'  => [$idTipo, 1],
                'group'   => '',
                'having'  => '',
                'order'   => ''
            ];
            // Preparo los datos
            $xParams                    = ['query' => $query];
            // Ejecuto la query
            $TempData                   = $this->Base_GetByID($xParams);
            $MainViewData['Data_Total'] = $TempData['data'];

            /************************************/
            // Listado (Compras si idTipo=1, Ventas si idTipo=2)
            $query = [
                'data'    => '
                    facturacion_listado.N_Doc,
                    facturacion_listado.Creacion_fecha,
                    facturacion_listado.ValorTotal,
                    facturacion_listado.MontoPagado,

                    entidades_listado.idTipoEntidad,
                    entidades_listado.Nombre AS EntidadesNombre,
                    entidades_listado.ApellidoPat AS EntidadesApellido,
                    entidades_listado.RazonSocial AS EntidadesRazonSocial,
                    entidades_listado.Nick AS EntidadesNick,
                    core_documentos_mercantiles.Nombre AS Documento',
                'table'   => 'facturacion_listado',
                'join'    => '
                    LEFT JOIN entidades_listado             ON entidades_listado.idEntidad                = facturacion_listado.idEntidad
                    LEFT JOIN core_documentos_mercantiles   ON core_documentos_mercantiles.idDocumentos   = facturacion_listado.idDocumentos',
                'where'   => 'facturacion_listado.idTipo = ? AND facturacion_listado.idEstadoPago = ?',
                'params'  => [$idTipo, 1],
                'group'   => '',
                'having'  => '',
                'order'   => 'facturacion_listado.Creacion_fecha DESC, facturacion_listado.N_Doc DESC, facturacion_listado.idFacturacion DESC',
                'limit'   => 9
            ];
            // Preparo los datos
            $xParams                       = ['query' => $query];
            // Ejecuto la query
            $TempData                      = $this->Base_GetList($xParams);
            $MainViewData['Data_Listado']  = $TempData['data'];
        }

        /************************************/
        // Datos enviados a la pagina
        $f3->data = [
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            /*===========   Funcionalidad   ===========*/
            'Fnc_CommonData'      => $this->CommonData,
            'Fnc_DataDate'        => $this->DataDate,
            'Fnc_DataNumbers'     => $this->DataNumbers,
            /*=========== Datos Consultados ===========*/
            'MainViewData'    => $MainViewData,
        ];

        /************************************/
        // Se instancia la vista
        $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$VistaWidget);
    }

}
