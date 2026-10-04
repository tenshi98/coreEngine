<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class permisosListadoRutas extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $CommonData;
    private $WidgetsCommon;

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_ADMIN);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'Empty';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->CommonData     = new FunctionsCommonData();
		$this->WidgetsCommon  = new UIWidgetsCommon();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /*******************************************************************/
    // Actualizar Listar
    /*******************************************************************/
    public function UpdateList($f3, $params){

        /************************************/
        // Se obtiene el ID
        $PermisosID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($PermisosID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                core_permisos_listado_rutas.idRutas,
                core_permisos_listado_rutas.idMetodo,
                core_permisos_listado_rutas.RutaWeb,
                core_permisos_listado_rutas.RutaController,
                core_permisos_listado_rutas.Descripcion,
                core_permisos_listado_rutas.Controller,
                core_permisos_listado_rutas_metodo.Nombre AS Metodo,
                core_permisos_listado_level_limit.Objetivo AS LevelLimit',
            'table'   => 'core_permisos_listado_rutas',
            'join'    => '
                LEFT JOIN core_permisos_listado_rutas_metodo ON core_permisos_listado_rutas_metodo.idMetodo     = core_permisos_listado_rutas.idMetodo
                LEFT JOIN core_permisos_listado_level_limit  ON core_permisos_listado_level_limit.idLevelLimit  = core_permisos_listado_rutas.idLevelLimit',
            'where'   => 'core_permisos_listado_rutas.idPermisos = ?',
            'params'  => [$PermisosID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'core_permisos_listado_rutas.Controller ASC, core_permisos_listado_rutas.idLevelLimit ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $arrRutas = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrRutas['status'] === true) {

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification' => $this->Codification,
                'Fnc_CommonData'   => $this->CommonData,
                /*=========== Datos Consultados ===========*/
                'arrRutas'     => $arrRutas['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/permisosListado-Resumen-Rutas-UpdateList.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrRutas]);
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
        $RutasID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($RutasID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                core_permisos_listado_rutas.RutaWeb,
                core_permisos_listado_rutas.RutaController,
                core_permisos_listado_rutas.Descripcion,
                core_permisos_listado_rutas.Controller,
                core_permisos_listado_rutas_metodo.Nombre AS Metodo,
                core_permisos_listado_level_limit.Objetivo AS LevelLimit',
            'table'   => 'core_permisos_listado_rutas',
            'join'    => '
                LEFT JOIN core_permisos_listado_rutas_metodo ON core_permisos_listado_rutas_metodo.idMetodo     = core_permisos_listado_rutas.idMetodo
                LEFT JOIN core_permisos_listado_level_limit  ON core_permisos_listado_level_limit.idLevelLimit  = core_permisos_listado_rutas.idLevelLimit',
            'where'   => 'core_permisos_listado_rutas.idRutas = ?',
            'params'  => [$RutasID['data']],
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
                /*=========== Datos Consultados ===========*/
                'rowData'       => $rowData['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/permisosListado-Resumen-Rutas-View.php');
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
        $RutasID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($RutasID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idRutas,idPermisos,idMetodo,RutaWeb,RutaController,Descripcion,idLevelLimit,Controller',
            'table'   => 'core_permisos_listado_rutas',
            'join'    => '',
            'where'   => 'idRutas = ?',
            'params'  => [$RutasID['data']],
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
            'data'    => 'idLevelLimit AS ID,Objetivo AS Nombre',
            'table'   => 'core_permisos_listado_level_limit',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'idLevelLimit ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams       = ['query' => $query];
        // Ejecuto la query
        $arrLevelLimit = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idMetodo AS ID,Nombre',
            'table'   => 'core_permisos_listado_rutas_metodo',
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
        $arrMetodo = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrLevelLimit['status'] && $arrMetodo['status']){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'    => $this->FormInputs,
                'Fnc_Codification'  => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'rowData'       => $rowData['data'],
                'arrLevelLimit' => $arrLevelLimit['data'],
                'arrMetodo'     => $arrMetodo['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/permisosListado-Resumen-Rutas-formEdit.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrLevelLimit,$arrMetodo]);
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
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idPermisos,idMetodo,RutaWeb,RutaController,Descripcion,idLevelLimit,Controller',
            'required'  => 'idPermisos,idMetodo,RutaWeb,RutaController,Descripcion,idLevelLimit,Controller',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'core_permisos_listado_rutas',
            'Post'      => $_POST
        ];
        // Preparo los datos
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query, 'novalidate' => true];
        $Response = $this->Base_insert($xParams);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if ($Response['status'] === false) {
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
        }

        /************************************/
        //Se actualizan los permisos al crear uno nuevo
        $permisosListado  = new permisosListado();
        $ResponsePermisos = $permisosListado->updatePermisos($f3);
        if($ResponsePermisos['code'] != 200){
            Response::error($ResponsePermisos['message'], $ResponsePermisos['code'], $ResponsePermisos['error'] ?? '');
        }

        /************************************/
        // Si es un ID numérico, se envía con código 200 (OK)
        Response::success($Response['data']);

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
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idRutas,idPermisos,idMetodo,RutaWeb,RutaController,Descripcion,idLevelLimit,Controller',
            'required'  => 'idRutas,idPermisos,idMetodo,RutaWeb,RutaController,Descripcion,idLevelLimit,Controller',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'core_permisos_listado_rutas',
            'where'     => 'idRutas',
            'Post'      => $_POST
        ];
        // Preparo los datos
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query, 'novalidate' => true];
        $Response = $this->Base_update($xParams);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if ($Response['status'] === false) {
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
        }

        /************************************/
        //Se actualizan los permisos al crear uno nuevo
        $permisosListado  = new permisosListado();
        $ResponsePermisos = $permisosListado->updatePermisos($f3);
        if($ResponsePermisos['code'] != 200){
            Response::error($ResponsePermisos['message'], $ResponsePermisos['code'], $ResponsePermisos['error'] ?? '');
        }

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data']);

    }

    /*******************************************************************/
    // Borrar dato y archivos
    /*******************************************************************/
    public function Delete($f3){

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
            'table'       => 'core_permisos_listado_rutas',
            'where'       => 'idRutas',
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
        //Se actualizan los permisos al crear uno nuevo
        $permisosListado  = new permisosListado();
        $ResponsePermisos = $permisosListado->updatePermisos($f3);
        if($ResponsePermisos['code'] != 200){
            Response::error($ResponsePermisos['message'], $ResponsePermisos['code'], $ResponsePermisos['error'] ?? '');
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
            'ValidarNumero'             => 'idPermisos,idMetodo,idLevelLimit',
            'ValidarEntero'             => 'idPermisos,idMetodo,idLevelLimit',
            'ValidarRut'                => '',
            'ValidarPatente'            => '',
            'ValidarFecha'              => '',
            'ValidarHora'               => '',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => 'RutaWeb,RutaController,Descripcion,Controller',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'RutaWeb,RutaController,Descripcion,Controller',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'RutaWeb,RutaController,Descripcion,Controller',
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
