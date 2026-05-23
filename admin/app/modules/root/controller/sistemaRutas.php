<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class sistemaRutas extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_ADMIN);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName     = 'Empty';
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //Resumen
    public function Resumen($f3){
        /******************************************/
        //Variable vacia
        $arrModules = [];

        //Arreglo con los controladores a instalar
		$sistemaInstalacion = new sistemaInstalacion();
        $array              = $sistemaInstalacion->arrayModInstall();
        /******************************************/
        //Verifico si existe
        if($array){
            //recorro
            foreach ($array as $data) {
                //Se genera la query
                $ListDataModule = method_exists($data, 'ListDataModule');
                //si el metodo existe
                if($ListDataModule===true){
                    $ControllerData = new $data;
                    //Se traen las rutas
                    for ($i=0; $i < 10; $i++) {
                        $arrModules[] = $ControllerData->listRouteModule($i, 0);
                    }
                }
            }
        }
        //Se eliminan valores vacios
        $arrModules = array_filter($arrModules);

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => 'idPermisos,idMetodo,RutaWeb,RutaController,Descripcion,idLevelLimit,Controller',
            'table'   => 'core_permisos_listado_rutas',
            'join'    => '',
            'where'   => 'idRutas!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'idRutas ASC',
            'limit'   => 9999
        ];
        //Ejecuto la query
        $xParams  = ['query' => $query];
        $arrRutas = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($arrModules) && $arrRutas['status']){
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'        => 'Comparación Rutas',
                'PageDescription'  => 'Comparación Rutas.',
                'PageAuthor'       => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'     => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => 'Comparación Rutas',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*=========== Datos Consultados ===========*/
                'arrModules' => $arrModules,
                'arrRutas'   => $arrRutas['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/sistemaRutas-Resumen.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$arrRutas]);
            //Muestra los errores
            $this->showError(1, $f3, $result);
        }
    }



}
