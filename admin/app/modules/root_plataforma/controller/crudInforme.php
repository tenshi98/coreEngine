<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class crudInforme extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $DataDate;
    private $DataNumbers;
    private $Codification;
    private $WidgetsCommon;
    private $WidgetsViews;

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_ADMIN);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'crudInforme';
		$this->FormInputs     = new UIFormInputs();
		$this->DataDate       = new FunctionsDataDate();
		$this->DataNumbers    = new FunctionsDataNumbers();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->WidgetsCommon  = new UIWidgetsCommon();
		$this->WidgetsViews   = new UIWidgetsViews();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /*******************************************************************/
    // Listar
    /*******************************************************************/
    public function listAll($f3){
        /************************************/
        // Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Informes',
            'PageDescription' => 'Testeos de Informes.',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            'TableTitle'      => 'Informes',
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
            /*===========   Funcionalidad   ===========*/
            'Fnc_FormInputs'   => $this->FormInputs,
            'Fnc_DataDate'     => $this->DataDate,
            'Fnc_DataNumbers'  => $this->DataNumbers,
            'Fnc_Codification' => $this->Codification,
        ];

        /************************************/
        // Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
    }

    /*******************************************************************/
    // Actualizar Listar
    /*******************************************************************/
    public function UpdateList($f3){

        /************************************/
        // Variables
        $WhereData_int     = '';                                            // Datos búsqueda exacta
        $WhereData_string  = 'Email,Numero,Rut,Patente,Fecha,Hora,Palabra'; // Datos búsqueda relativa
        $WhereData_between = '';                                            // Datos búsqueda Between
        $whereInt          = '';                                            // Se crea cadena
        $whereParams       = [];                                            // Valores bindeados asociados a $whereInt
        /************************************/
        // Se validan las fechas
        $RespDataBetween = $this->searchValidateDates($WhereData_between);
        if($RespDataBetween!=''){
            Response::error($RespDataBetween, 400);
        }
        /************************************/
        // Agrego variable busqueda
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_int, 'core_test_crud', 1);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_string, 'core_test_crud', 2);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_between, 'core_test_crud', 3);
        $whereInt = $r['where']; $whereParams = $r['params'];

        /************************************/
        // Carga incremental: offset y tamaño de bloque (N_MaxItems + 1 fila para saber si hay más)
        $page = $this->getPageParams();

        /************************************/
        // Se obtienen los datos
        $arrList = $this->getDataList($whereInt, $whereParams, $page['limit']);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrList['status'] === true) {

            /************************************/
            // Se recorta la fila extra y se calcula si quedan más datos
            $more = $this->sliceMore($arrList['data'], $page);

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'TableTitle'      => 'Informes',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                'Fnc_Codification'    => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrList'        => $more['rows'],
                'hasMore' => $more['hasMore'],
                'nextOffset' => $more['next'],
            ];

            /************************************/
            // Fragmento (solo filas) para la carga incremental, o tabla completa
            if ($page['fragment']) {
                header('X-Has-More: '.($more['hasMore'] ? '1' : '0'));
                header('X-Next-Offset: '.$more['next']);
                $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-UpdateListRows.php');
            } else {
                $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-UpdateList.php');
            }
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrList]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // View
    /*******************************************************************/
    public function View($f3, $params){

        /************************************/
        // Se no existe el ID
        if (!isset($params['id'])) {
            Response::error('Registro ID inexistente', 400);
        }

        /************************************/
        // Se obtiene el ID
        $CrudID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        // Se verifica
        if (!$this->isValidDecrypted($CrudID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se obtienen los datos
        $rowData = $this->getDataDetail($CrudID['data']);

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
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                'Fnc_WidgetsCommon'   => $this->WidgetsCommon,
                'Fnc_WidgetsViews'    => $this->WidgetsViews,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-View.php');
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
    /*                            CONSULTAS INTERNAS                              */
    /******************************************************************************/
    /*******************************************************************/
    // Se obtiene la lista
    /*******************************************************************/
    private function getDataList($filter, $params = [], $limit = null){

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idCrud,Email,Numero,Rut,Patente,Fecha,Hora,Palabra',
            'table'   => 'core_test_crud',
            'join'    => '',
            'where'   => $filter,
            'params'  => $params,
            'group'   => '',
            'having'  => '',
            'order'   => 'Email ASC, idCrud ASC',
            'limit'   => ($limit ?? ConfigAPP::APP["N_MaxItems"])
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        //Se retornan los datos
        return $this->Base_GetList($xParams);
    }

    /*******************************************************************/
    // Se obtienen los detalles
    /*******************************************************************/
    private function getDataDetail($ID){

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idCrud,idUsuario,Email,Numero,Rut,Patente,Fecha,Hora,Palabra',
            'table'   => 'core_test_crud',
            'join'    => '',
            'where'   => 'idCrud = ?',
            'params'  => [$ID],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        //Se retornan los datos
        return $this->Base_GetByID($xParams);
    }

}
