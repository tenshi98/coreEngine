<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class mainCampanas extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $DataValidations;
    private $CommonData;
    private $DataNumbers;
    private $Convertions;
    private $ServerServer;
    private $DataDate;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
		$this->DataValidations   = new FunctionsDataValidations();
		$this->CommonData        = new FunctionsCommonData();
		$this->DataNumbers       = new FunctionsDataNumbers();
		$this->Convertions       = new FunctionsConvertions($this->DataValidations);
		$this->ServerServer      = new FunctionsServerServer();
		$this->DataDate          = new FunctionsDataDate();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //Actualizacion campañas
    public function resumenCampana($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $arrMenu  = $f3->get('SESSION.arrMenu');

        /*******************************************************************/
        //Variables
        $MainViewData = [
            'Count_Campanas' => 0,
            'Data_Campanas'  => '',
        ];
        //Se asignan datos a buscar
        $menuCounters = [
            'Gestión Campañas' => [
                'Campañas - Listado' => 'Count_Campanas',
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
        if($MainViewData['Count_Campanas']!=0){
            //Se genera la query
            $query = [
                'data'    => '
                    Fecha_Mes,
                    SUM(Costos) AS Costos,
                    SUM(Beneficios) AS Beneficios,
                    SUM(Perdidas) AS Perdidas,
                    SUM(Margen) AS Margenes',
                'table'   => 'campanas_listado',
                'join'    => '',
                'where'   => 'Fecha_Ano='.$params['id'],
                'group'   => 'Fecha_Mes',
                'having'  => '',
                'order'   => 'Fecha_Mes ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            //Ejecuto la query
            $xParams                       = ['query' => $query];
            $MainViewData['Data_Campanas'] = $this->Base_GetList($xParams);
        }

        /******************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*===========   Funcionalidad   ===========*/
            'Fnc_CommonData'      => $this->CommonData,
            'Fnc_Convertions'     => $this->Convertions,
            'Fnc_DataNumbers'     => $this->DataNumbers,
            'Fnc_ServerServer'    => $this->ServerServer,
            /*=========== Datos Consultados ===========*/
            'MainViewData'    => $MainViewData,
        ];

        //Se instancia la vista
        $view = new View;
        echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/main-campana-update.php'); // Vista
    }

    /******************************************************************************/
    //Actualizacion campañas
    public function campanaAnalisisContable($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $arrMenu  = $f3->get('SESSION.arrMenu');

        /*******************************************************************/
        //Variables
        $MainViewData = [
            'Count_Campanas' => 0,
            'Data_Analisis'  => '',
        ];
        //Se asignan datos a buscar
        $menuCounters = [
            'Gestión Campañas' => [
                'Campañas - Listado'   => 'Count_Campanas',
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
        if($MainViewData['Count_Campanas']!=0){
            //Se genera la query
            $query = [
                'data'    => 'idCampana,Nombre,Costos,Beneficios,Margen',
                'table'   => 'campanas_listado',
                'join'    => '',
                'where'   => 'Fecha_Ano='.$params['id'],
                'group'   => '',
                'having'  => '',
                'order'   => 'idCampana DESC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            //Ejecuto la query
            $xParams                       = ['query' => $query];
            $MainViewData['Data_Analisis'] = $this->Base_GetList($xParams);

            /*******************************************************************/
            //Se filtran los ya existentes
            $X_Where = 'idCampana IN (0';
            //Se genera la consulta
            if (!empty($MainViewData['Data_Analisis'])) {$X_Where .= ',' . implode(',', array_column($MainViewData['Data_Analisis'], 'idCampana'));}
            $X_Where .= ')';

            /*******************************************************************/
            //Se genera la query
            $query = [
                'data'    => '
                    campanas_listado_costos.idCampana,
                    facturacion_listado_productos.idProducto,
                    SUM(facturacion_listado_productos.Number) AS Cantidad,
                    SUM(facturacion_listado_productos.ValorTotal) AS Valor,
                    productos_listado.Nombre AS Producto,
                    core_unidades_medida.Nombre AS Unimed',
                'table'   => 'campanas_listado_costos',
                'join'    => '
                    LEFT JOIN facturacion_listado_productos   ON facturacion_listado_productos.idFacturacion   = campanas_listado_costos.idFacturacion
                    LEFT JOIN productos_listado               ON productos_listado.idProducto                  = facturacion_listado_productos.idProducto
                    LEFT JOIN core_unidades_medida            ON core_unidades_medida.idUniMed                 = productos_listado.idUniMed',
                'where'   => 'campanas_listado_costos.'.$X_Where,
                'group'   => 'campanas_listado_costos.idCampana,facturacion_listado_productos.idProducto',
                'having'  => '',
                'order'   => 'campanas_listado_costos.idCampana ASC, facturacion_listado_productos.idProducto ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            //Ejecuto la query
            $xParams                             = ['query' => $query];
            $MainViewData['Data_AnalisisCostos'] = $this->Base_GetList($xParams);

            /*******************************************************************/
            //Se genera la query
            $query = [
                'data'    => '
                    campanas_listado_partidas.idCampana,
                    campanas_listado_partidas_productos.idProducto,
                    SUM(campanas_listado_partidas_productos.Cantidad) AS Cantidad,
                    SUM(campanas_listado_partidas_productos.Beneficios) AS Valor,
                    productos_listado.Nombre AS Producto,
                    core_unidades_medida.Nombre AS Unimed',
                'table'   => 'campanas_listado_partidas',
                'join'    => '
                    LEFT JOIN campanas_listado_partidas_productos   ON campanas_listado_partidas_productos.idExistencia  = campanas_listado_partidas.idExistencia
                    LEFT JOIN productos_listado                     ON productos_listado.idProducto                      = campanas_listado_partidas_productos.idProducto
                    LEFT JOIN core_unidades_medida                  ON core_unidades_medida.idUniMed                     = productos_listado.idUniMed',
                'where'   => 'campanas_listado_partidas.'.$X_Where.' AND campanas_listado_partidas.idEstadoPartida = 6',
                'group'   => 'campanas_listado_partidas.idCampana,campanas_listado_partidas_productos.idProducto',
                'having'  => '',
                'order'   => 'campanas_listado_partidas.idCampana ASC, campanas_listado_partidas_productos.idProducto ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            //Ejecuto la query
            $xParams                             = ['query' => $query];
            $MainViewData['Data_AnalisisVentas'] = $this->Base_GetList($xParams);

        }

        /******************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*===========   Funcionalidad   ===========*/
            'Fnc_CommonData'      => $this->CommonData,
            'Fnc_Convertions'     => $this->Convertions,
            'Fnc_DataNumbers'     => $this->DataNumbers,
            'Fnc_ServerServer'    => $this->ServerServer,
            /*=========== Datos Consultados ===========*/
            'MainViewData'    => $MainViewData,
        ];

        //Se instancia la vista
        $view = new View;
        echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/main-campana-listado-update.php'); // Vista
    }

    /******************************************************************************/
    //Actualizacion campañas
    public function partidaConfirmada($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $arrMenu  = $f3->get('SESSION.arrMenu');

        /*******************************************************************/
        //Variables
        $MainViewData = [
            'Count_Campanas'          => 0,
            'Data_PartidaConfirmada'  => '',
        ];
        //Se asignan datos a buscar
        $menuCounters = [
            'Gestión Campañas' => [
                'Campañas - Listado'   => 'Count_Campanas',
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
        if($MainViewData['Count_Campanas']!=0){
            //Se genera la query
            $query = [
                'data'    => '
                    campanas_listado_partidas.Fecha,
                    campanas_listado_partidas.ConfirmacionFecha,
                    campanas_listado_partidas.ConfirmacionHora,
                    campanas_listado.Nombre AS CampanaNombre,
                    entidades_listado.Nombre AS EntidadNombre,
                    entidades_listado.ApellidoPat AS EntidadApellido,
                    entidades_listado.RazonSocial AS EntidadRazonSocial,
                    entidades_listado.Nick AS EntidadNick,
                    entidades_sectores.Nombre AS EntidadSector,
                    entidades_listado.Direccion AS EntidadDireccion,
                    campanas_listado_partidas.idExistencia,
                    campanas_listado_partidas_productos.Cantidad,
                    productos_listado.Nombre AS ProductoNombre,
                    core_unidades_medida.Nombre AS ProductoUniMed',
                'table'   => 'campanas_listado_partidas',
                'join'    => '
                    LEFT JOIN campanas_listado                      ON campanas_listado.idCampana                        = campanas_listado_partidas.idCampana
                    LEFT JOIN entidades_listado                     ON entidades_listado.idEntidad                       = campanas_listado_partidas.idEntidad
                    LEFT JOIN entidades_sectores                    ON entidades_sectores.idSector                       = entidades_listado.idSector
                    LEFT JOIN campanas_listado_partidas_productos   ON campanas_listado_partidas_productos.idExistencia  = campanas_listado_partidas.idExistencia
                    LEFT JOIN productos_listado                     ON productos_listado.idProducto                      = campanas_listado_partidas_productos.idProducto
                    LEFT JOIN core_unidades_medida                  ON core_unidades_medida.idUniMed                     = productos_listado.idUniMed',
                'where'   => 'campanas_listado_partidas.idEstadoPartida = "4"',
                'group'   => '',
                'having'  => '',
                'order'   => 'campanas_listado_partidas.ConfirmacionFecha DESC, campanas_listado_partidas.ConfirmacionHora DESC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            //Ejecuto la query
            $xParams                                = ['query' => $query];
            $MainViewData['Data_PartidaConfirmada'] = $this->Base_GetList($xParams);
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
        echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/main-campana-partidaConfirmada-update.php'); // Vista
    }


}
