<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class bodegasWidgets extends ControllerBase {

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
		$this->CommonData  = new FunctionsCommonData();
		$this->DataNumbers = new FunctionsDataNumbers();
		$this->DataDate    = new FunctionsDataDate();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                 EJECUCION                                  */
    /******************************************************************************/
    /******************************************************************************/
    //Instalacion del modulo completo
    public function loadWidgets(){

        //Variables
        $Data['Menu_Name']   = 'Gestión Bodegas y Productos';
        $Data['Menu_Value']  = [
            'Stock Productos'   => '../app/modules/bodegas/views/main-bodega-stock.php',
        ];

        //Devuelvo
        return $Data;
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //Actualizacion campañas
    public function stocksProductos($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $arrMenu  = $f3->get('SESSION.arrMenu');

        /*******************************************************************/
        //Variables
        $MainViewData = [
            'Count_Bodegas'   => 0,
            'Data_arrBodegas' => '',
            'Data_arrStocks'  => '',
        ];
        //Se asignan datos a buscar
        $menuCounters = [
            'Gestión Bodegas y Productos' => [
                'Stock Productos'   => 'Count_Bodegas',
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
        if($MainViewData['Count_Bodegas']!=0){
            /*******************************************************************/
            //Se genera la query
            $query = [
                'data'    => 'idBodegas,Nombre',
                'table'   => 'bodegas_listado',
                'join'    => '',
                'where'   => 'idEstado=1',
                'group'   => '',
                'having'  => '',
                'order'   => 'Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            //Ejecuto la query
            $xParams                         = ['query' => $query];
            $MainViewData['Data_arrBodegas'] = $this->Base_GetList($xParams);

            //Se genera la consulta
            $ActionSQL = '';
            foreach ($MainViewData['Data_arrBodegas'] as $bod) {
                $ActionSQL .= ',Cantidad_idBodegas_'.$bod['idBodegas'];
            }

            /*******************************************************************/
            //Se genera la query
            $query = [
                'data'    => '
                    bodegas_productos_stocks.idProducto,
                    productos_listado.Nombre AS Producto,
                    productos_listado.StockLimite AS ProductoStock,
                    core_unidades_medida.Nombre AS UniMed
                    '.$ActionSQL,
                'table'   => 'bodegas_productos_stocks',
                'join'    => '
                    LEFT JOIN productos_listado     ON productos_listado.idProducto    = bodegas_productos_stocks.idProducto
                    LEFT JOIN core_unidades_medida  ON core_unidades_medida.idUniMed   = productos_listado.idUniMed',
                'where'   => '',
                'group'   => '',
                'having'  => '',
                'order'   => 'productos_listado.Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            //Ejecuto la query
            $xParams                        = ['query' => $query];
            $MainViewData['Data_arrStocks'] = $this->Base_GetList($xParams);

        }

        /******************************************/
        //Datos enviados a la pagina
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

        //Se instancia la vista
        $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/main-bodega-stock-update.php');
    }

}
