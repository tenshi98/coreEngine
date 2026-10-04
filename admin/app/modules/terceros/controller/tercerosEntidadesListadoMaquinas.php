<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class tercerosEntidadesListadoMaquinas extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataNumbers;
    private $DataDate;
    private $WidgetsCommon;

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'tercerosEntidadesListado';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->DataNumbers    = new FunctionsDataNumbers();
		$this->DataDate       = new FunctionsDataDate();
		$this->WidgetsCommon  = new UIWidgetsCommon();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /*******************************************************************/
    // Crear nuevo
    /*******************************************************************/
    public function New($f3, $params){

        /************************************/
        // Se obtiene el ID
        $EntidadID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($EntidadID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idEntidad',
            'table'   => 'entidades_listado',
            'join'    => '',
            'where'   => 'idEntidad = ?',
            'params'  => [$EntidadID['data']],
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
            'data'    => 'idMaquina',
            'table'   => 'terceros_entidades_listado_maquinas',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'idMaquina ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrMaq  = $this->Base_GetList($xParams);


        // Se filtran los ya existentes
        $ElementsIDs = [0];
        if ($arrMaq['status'] === true) {
            // Obtiene los identificadores de las campañas existentes y los agrega
            // a la lista de valores.
            $ElementsIDs = array_merge($ElementsIDs, array_column($arrMaq['data'], 'idMaquina'));
        }
        // Se generan los placeholders necesarios para vincular posteriormente
        // cada identificador mediante parámetros preparados.
        $placeholders = implode(',', array_fill(0, count($ElementsIDs), '?'));

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idMaquina AS ID,Nombre',
            'table'   => 'maquinas_listado',
            'join'    => '',
            'where'   => 'idEstado = ? AND idMaquina NOT IN ('.$placeholders.')',
            'params'  => array_merge([1], $ElementsIDs),
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams     = ['query' => $query];
        // Ejecuto la query
        $arrMaquinas = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrMaquinas['status']){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'       => $this->FormInputs,
                'Fnc_Codification'     => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'rowData'        => $rowData['data'],
                'arrMaquinas'    => $arrMaquinas['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Maquinas-formNew.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrMaquinas]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Actualizar Listar
    /*******************************************************************/
    public function UpdateList($f3, $params){

        /************************************/
        // Se obtiene el ID
        $EntidadID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($EntidadID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                terceros_entidades_listado_maquinas.idMaq,
                terceros_entidades_listado_maquinas.Fecha,
                maquinas_listado.Nombre AS Maquina,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor',
            'table'   => 'terceros_entidades_listado_maquinas',
            'join'    => '
                LEFT JOIN maquinas_listado  ON maquinas_listado.idMaquina  = terceros_entidades_listado_maquinas.idMaquina
                LEFT JOIN core_estados      ON core_estados.idEstado       = terceros_entidades_listado_maquinas.idEstado',
            'where'   => 'terceros_entidades_listado_maquinas.idEntidad = ?',
            'params'  => [$EntidadID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'terceros_entidades_listado_maquinas.Fecha DESC, maquinas_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrMaq  = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrMaq['status'] === true) {

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'     => $this->Codification,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                'Fnc_DataDate'         => $this->DataDate,
                /*=========== Datos Consultados ===========*/
                'arrMaq' => $arrMaq['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Maquinas-UpdateList.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrMaq]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // View
    /*******************************************************************/
    public function View($f3, $params){

        /************************************/
        // Se obtiene el ID
        $MaqID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($MaqID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                terceros_entidades_listado_maquinas.Fecha,
                terceros_entidades_listado_maquinas.Observacion,
                maquinas_listado.Nombre AS Maquina,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor',
            'table'   => 'terceros_entidades_listado_maquinas',
            'join'    => '
                LEFT JOIN maquinas_listado  ON maquinas_listado.idMaquina  = terceros_entidades_listado_maquinas.idMaquina
                LEFT JOIN core_estados      ON core_estados.idEstado       = terceros_entidades_listado_maquinas.idEstado',
            'where'   => 'terceros_entidades_listado_maquinas.idMaq = ?',
            'params'  => [$MaqID['data']],
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
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_WidgetsCommon' => $this->WidgetsCommon,
                'Fnc_DataDate'      => $this->DataDate,
                'Fnc_DataNumbers'   => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'rowData'       => $rowData['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Maquinas-View.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Editar
    /*******************************************************************/
    public function GetID($f3, $params){

        /************************************/
        // Se obtiene el ID
        $MaqID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($MaqID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idMaq,idEntidad,idMaquina,idEstado,Fecha,Observacion',
            'table'   => 'terceros_entidades_listado_maquinas',
            'join'    => '',
            'where'   => 'idMaq = ?',
            'params'  => [$MaqID['data']],
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
            'data'    => 'idMaquina',
            'table'   => 'terceros_entidades_listado_maquinas',
            'join'    => '',
            'where'   => 'idMaquina != ?',
            'params'  => [$rowData['data']['idMaquina']],
            'group'   => '',
            'having'  => '',
            'order'   => 'idMaquina ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrMaq  = $this->Base_GetList($xParams);

        // Se filtran los ya existentes
        $ElementsIDs = [0];
        if ($arrMaq['status'] === true) {
            // Obtiene los identificadores de las campañas existentes y los agrega
            // a la lista de valores.
            $ElementsIDs = array_merge($ElementsIDs, array_column($arrMaq['data'], 'idMaquina'));
        }
        // Se generan los placeholders necesarios para vincular posteriormente
        // cada identificador mediante parámetros preparados.
        $placeholders = implode(',', array_fill(0, count($ElementsIDs), '?'));

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idMaquina AS ID,Nombre',
            'table'   => 'maquinas_listado',
            'join'    => '',
            'where'   => 'idEstado = ? AND idMaquina NOT IN ('.$placeholders.')',
            'params'  => array_merge([1], $ElementsIDs),
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams     = ['query' => $query];
        // Ejecuto la query
        $arrMaquinas = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idEstado AS ID,Nombre',
            'table'   => 'core_estados',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams   = ['query' => $query];
        // Ejecuto la query
        $arrEstado = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrMaquinas['status'] && $arrEstado['status']){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'    => $this->FormInputs,
                'Fnc_Codification'  => $this->Codification,
                'Fnc_DataNumbers'   => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
                'arrMaquinas'      => $arrMaquinas['data'],
                'arrEstado'        => $arrEstado['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Maquinas-formEdit.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrMaquinas,$arrEstado]);
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
    public function Insert(){

        /************************************/
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idEntidad,idMaquina,idEstado,idUsuario,Fecha,Observacion',
            'required'  => 'idEntidad,idMaquina,idEstado,idUsuario',
            'unique'    => 'idMaquina',
            'encode'    => '',
            'table'     => 'terceros_entidades_listado_maquinas',
            'Post'      => $_POST
        ];
        // Preparo los datos
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        // Ejecuto la query
        $Response = $this->Base_insert($xParams);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if ($Response['status'] === false) {
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
        }

        /************************************/
        // Si es un ID numérico, se envía con código 200 (OK)
        Response::success($Response['data']);

    }

    /*******************************************************************/
    // Editar por put (solo modificar datos)
    // Editar por post (modificar y subir archivos)
    /*******************************************************************/
    public function Update(){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idMaq,idEntidad,idMaquina,idEstado,idUsuario,Fecha,Observacion',
            'required'  => 'idMaq,idEntidad,idMaquina,idEstado,idUsuario',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'terceros_entidades_listado_maquinas',
            'where'     => 'idMaq',
            'Post'      => $_POST
        ];
        // Preparo los datos
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        // Ejecuto la query
        $Response = $this->Base_update($xParams);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if ($Response['status'] === false) {
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
        }

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data']);

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
        // Se genera la query
        $query = [
            'files'       => '',
            'table'       => 'terceros_entidades_listado_maquinas',
            'where'       => 'idMaq',
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
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
        }

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data']);

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
            'ValidarNumero'             => 'idEntidad,idMaquina,idEstado,idUsuario',
            'ValidarEntero'             => 'idEntidad,idMaquina,idEstado,idUsuario',
            'ValidarRut'                => '',
            'ValidarPatente'            => '',
            'ValidarFecha'              => 'Fecha',
            'ValidarHora'               => '',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => 'Observacion',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => '',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Observacion',
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

}
