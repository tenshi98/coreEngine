<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class maquinasListadoSensores extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $WidgetsCommon;

    private $WidgetsViews;
    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        // Se conserva 'maquinasListado' para reutilizar el namespace de vistas Resumen
        $this->controllerName = 'maquinasListado';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->WidgetsCommon  = new UIWidgetsCommon();
		$this->WidgetsViews  = new UIWidgetsViews();
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
        // Se obtiene el ID de la máquina
        $MaquinaID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($MaquinaID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se valida que la máquina exista
        $rowData = $this->getMaquina($MaquinaID['data']);
        // Se obtienen los catálogos
        $arrCatalog = $this->getCatalogos();

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($rowData['status'] === true && $arrCatalog['status'] === true) {
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
                'rowData'          => $rowData['data'],
                'arrGrupos'        => $arrCatalog['arrGrupos'],
                'arrSubgrupos'     => $arrCatalog['arrSubgrupos'],
                'arrUnimed'        => $arrCatalog['arrUnimed'],
                'arrEstado'        => $arrCatalog['arrEstado'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Sensores-formNew.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData, $arrCatalog]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Actualizar Lista
    /*******************************************************************/
    public function UpdateList($f3, $params){

        /************************************/
        // Se obtiene el ID de la máquina
        $MaquinaID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($MaquinaID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se obtiene la máquina y su JSON de sensores
        $rowData = $this->getMaquina($MaquinaID['data']);
        // Se decodifica el arreglo de sensores
        $arrSensores = $rowData['status'] === true ? $this->sensorDecode($rowData['data']['Sensores'] ?? '') : [];

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
                'Fnc_Codification'     => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrSensores'   => $arrSensores,
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Sensores-UpdateList.php');
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
    // View
    /*******************************************************************/
    public function View($f3, $params){

        /************************************/
        // Se obtiene el ID de la máquina
        $MaquinaID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($MaquinaID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se obtiene el sensor por uid
        $sensor = $this->getSensor($MaquinaID['data'], $_GET['uid'] ?? '');
        if ($sensor === false) {
            Response::error('Registro inválido', 400);
        }

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        /************************************/
        // Datos enviados a la pagina
        $f3->data = [
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
            /*===========   Funcionalidad   ===========*/
            'Fnc_WidgetsCommon' => $this->WidgetsCommon,
            'Fnc_WidgetsViews'  => $this->WidgetsViews,
            /*=========== Datos Consultados ===========*/
            'rowData'       => $sensor,
        ];

        /************************************/
        // Se instancia la vista
        $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Sensores-View.php');
    }

    /*******************************************************************/
    // Editar
    /*******************************************************************/
    public function GetID($f3, $params){

        /************************************/
        // Se obtiene el ID de la máquina
        $MaquinaID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($MaquinaID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se obtiene el sensor por uid
        $sensor = $this->getSensor($MaquinaID['data'], $_GET['uid'] ?? '');
        if ($sensor === false) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se obtienen los catálogos
        $arrCatalog = $this->getCatalogos();

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrCatalog['status'] === true) {
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
                'rowData'          => $sensor,
                'idMaquina'        => $MaquinaID['data'],
                'arrGrupos'        => $arrCatalog['arrGrupos'],
                'arrSubgrupos'     => $arrCatalog['arrSubgrupos'],
                'arrUnimed'        => $arrCatalog['arrUnimed'],
                'arrEstado'        => $arrCatalog['arrEstado'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Sensores-formEdit.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrCatalog]);
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
        // Se resuelven los datos y nombres del sensor
        $sensor = $this->buildSensor($_POST, null);

        /************************************/
        // Se obtiene la máquina y sus sensores actuales
        $MaquinaID   = $_POST['idMaquina'];
        $rowData     = $this->getMaquina($MaquinaID);
        if ($rowData['status'] === false) {
            $this->failOperation($rowData);
        }
        $arrSensores = $this->sensorDecode($rowData['data']['Sensores'] ?? '');

        // Se agrega al final del arreglo (RN-003)
        $arrSensores[] = $sensor;

        /************************************/
        // Se persiste el arreglo
        $Response = $this->sensorPersist($MaquinaID, $arrSensores, $DataCheck);

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data']);

    }

    /*******************************************************************/
    // Editar por post
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
        // Se resuelven los datos y nombres del sensor
        $sensor = $this->buildSensor($_POST, $_POST['uid'] ?? '');

        /************************************/
        // Se obtiene la máquina y sus sensores actuales
        $MaquinaID   = $_POST['idMaquina'];
        $rowData     = $this->getMaquina($MaquinaID);
        if ($rowData['status'] === false) {
            $this->failOperation($rowData);
        }
        $arrSensores = $this->sensorDecode($rowData['data']['Sensores'] ?? '');

        // Se reemplaza el sensor por su uid (RN-004)
        $indice = $this->sensorFind($arrSensores, $_POST['uid'] ?? '');
        if ($indice === false) {
            Response::error('Registro inválido', 400);
        }
        $arrSensores[$indice] = $sensor;

        /************************************/
        // Se persiste el arreglo
        $Response = $this->sensorPersist($MaquinaID, $arrSensores, $DataCheck);

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data']);

    }

    /*******************************************************************/
    // Borrar sensor
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
        // Se obtiene el ID de la máquina
        $MaquinaID = $this->Codification->encryptDecrypt('decrypt', $dataDelete['idMaquina'] ?? '');
        if (!$this->isValidDecrypted($MaquinaID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se obtiene la máquina y sus sensores actuales
        $rowData     = $this->getMaquina($MaquinaID['data']);
        if ($rowData['status'] === false) {
            $this->failOperation($rowData);
        }
        $arrSensores = $this->sensorDecode($rowData['data']['Sensores'] ?? '');

        /************************************/
        // Se elimina el sensor por su uid (RN-004)
        $indice = $this->sensorFind($arrSensores, $dataDelete['uid'] ?? '');
        if ($indice === false) {
            Response::error('Registro inválido', 400);
        }
        unset($arrSensores[$indice]);
        // Se compacta el arreglo (RN-003)
        $arrSensores = array_values($arrSensores);

        /************************************/
        // Se persiste el arreglo
        $Response = $this->sensorPersist($MaquinaID['data'], $arrSensores, $this->dataCheckPersist());

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data']);

    }

    /*******************************************************************/
    // Guardar el orden de los sensores
    /*******************************************************************/
    public function SaveOrder(){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Se obtiene el ID de la máquina
        $MaquinaID = $this->Codification->encryptDecrypt('decrypt', $_POST['idMaquina'] ?? '');
        if (!$this->isValidDecrypted($MaquinaID, 'id')) {
            Response::error('Registro inválido', 400);
        }
        // Se reciben los uid en el nuevo orden
        $uids = $_POST['uids'] ?? [];
        if (!is_array($uids) || empty($uids)) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se obtiene la máquina y sus sensores actuales
        $rowData     = $this->getMaquina($MaquinaID['data']);
        if ($rowData['status'] === false) {
            $this->failOperation($rowData);
        }
        $arrSensores = $this->sensorDecode($rowData['data']['Sensores'] ?? '');

        /************************************/
        // Se reordena conservando exactamente los mismos elementos (RN-005)
        $arrOrder = [];
        foreach ($uids as $uid) {
            $indice = $this->sensorFind($arrSensores, $uid);
            if ($indice === false) {
                Response::error('Registro inválido', 400);
            }
            $arrOrder[] = $arrSensores[$indice];
        }
        // Se verifica que no falte ningún sensor
        if (count($arrOrder) !== count($arrSensores)) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se persiste el arreglo reordenado
        $Response = $this->sensorPersist($MaquinaID['data'], $arrOrder, $this->dataCheckPersist());

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data']);

    }

    /******************************************************************************/
    /*                             Métodos privados                               */
    /******************************************************************************/
    /*******************************************************************/
    // Se obtiene una máquina por su ID
    /*******************************************************************/
    private function getMaquina($idMaquina){

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idMaquina,Nombre,Sensores',
            'table'   => 'maquinas_listado',
            'join'    => '',
            'where'   => 'idMaquina = ?',
            'params'  => [$idMaquina],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        return $this->Base_GetByID($xParams);
    }

    /*******************************************************************/
    // Se obtiene un sensor por uid dentro de una máquina
    /*******************************************************************/
    private function getSensor($idMaquina, $uid){

        /************************************/
        // Se obtiene la máquina
        $rowData = $this->getMaquina($idMaquina);
        if ($rowData['status'] === false) {
            return false;
        }

        /************************************/
        // Se busca el sensor por uid
        $arrSensores = $this->sensorDecode($rowData['data']['Sensores'] ?? '');
        $indice      = $this->sensorFind($arrSensores, $uid);

        /************************************/
        // Retorno el sensor (o false)
        return $indice === false ? false : $arrSensores[$indice];
    }

    /*******************************************************************/
    // Se construye el sensor resolviendo los nombres desde los catálogos
    /*******************************************************************/
    private function buildSensor($POST, $uid){

        /************************************/
        // Se validan los campos obligatorios (RN-001)
        $requeridos = [
            'Nombre'    => 'Nombre',
            'idGrupo'   => 'Grupo',
            'idSubGrupo'=> 'Subgrupo',
            'idUniMed'  => 'Unidad de Medida',
            'idEstado'  => 'Estado Sensor',
        ];
        foreach ($requeridos as $campo => $etiqueta) {
            if (!isset($POST[$campo]) || $POST[$campo] === '') {
                Response::error('El campo '.$etiqueta.' es obligatorio', 400);
            }
        }

        /************************************/
        // Se obtienen los catálogos
        $arrCatalog = $this->getCatalogos();
        if ($arrCatalog['status'] === false) {
            $this->failOperation($arrCatalog);
        }

        /************************************/
        // Se validan los catálogos (RN-006)
        if (!isset($arrCatalog['mapGrupos'][$POST['idGrupo']])) {
            Response::error('El grupo seleccionado no es válido', 400);
        }
        if (!isset($arrCatalog['mapUnimed'][$POST['idUniMed']])) {
            Response::error('La unidad de medida seleccionada no es válida', 400);
        }
        if (!isset($arrCatalog['mapEstado'][$POST['idEstado']])) {
            Response::error('El estado seleccionado no es válido', 400);
        }
        // El subgrupo debe existir y pertenecer al grupo (RN-002)
        if (!isset($arrCatalog['mapSubgrupos'][$POST['idSubGrupo']])) {
            Response::error('El subgrupo seleccionado no es válido', 400);
        }
        if ((int)$arrCatalog['mapSubgrupos'][$POST['idSubGrupo']]['idGrupo'] !== (int)$POST['idGrupo']) {
            Response::error('El subgrupo seleccionado no corresponde al grupo', 400);
        }

        /************************************/
        // Se construye el sensor con los nombres desnormalizados
        $sensor = [
            'uid'        => ($uid !== null && $uid !== '') ? $uid : uniqid(),
            'Nombre'     => $POST['Nombre'],
            'idGrupo'    => (int)$POST['idGrupo'],
            'Grupo'      => $arrCatalog['mapGrupos'][$POST['idGrupo']],
            'idSubGrupo' => (int)$POST['idSubGrupo'],
            'SubGrupo'   => $arrCatalog['mapSubgrupos'][$POST['idSubGrupo']]['Nombre'],
            'idUniMed'   => (int)$POST['idUniMed'],
            'UniMed'     => $arrCatalog['mapUnimed'][$POST['idUniMed']],
            'idEstado'   => (int)$POST['idEstado'],
            'Estado'     => $arrCatalog['mapEstado'][$POST['idEstado']],
        ];

        /************************************/
        // Retorno el sensor
        return $sensor;
    }

    /*******************************************************************/
    // Se obtienen los catálogos y sus mapas de resolución
    /*******************************************************************/
    private function getCatalogos(){

        /************************************/
        // Grupos
        $query = [
            'data'    => 'idGrupo AS ID,Nombre',
            'table'   => 'maquinas_sensores_grupos',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrGrupos = $this->Base_GetList($xParams);

        /************************************/
        // Subgrupos (ID1=subgrupo, ID2=grupo, Nombre)
        $query = [
            'data'    => 'idSubGrupo AS ID1,idGrupo AS ID2,Nombre',
            'table'   => 'maquinas_sensores_subgrupos',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'idGrupo ASC, Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrSubgrupos = $this->Base_GetList($xParams);

        /************************************/
        // Unidades de medida
        $query = [
            'data'    => 'idUniMed AS ID,Nombre',
            'table'   => 'core_unidades_medida',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrUnimed = $this->Base_GetList($xParams);

        /************************************/
        // Estados
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
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrEstado = $this->Base_GetList($xParams);

        /************************************/
        // Si falla cualquiera de los catálogos, se retorna el error
        foreach ([$arrGrupos, $arrSubgrupos, $arrUnimed, $arrEstado] as $arr) {
            if ($arr['status'] === false) {
                return ['status' => false, 'error' => $arr['error'] ?? '', 'data' => []];
            }
        }

        /************************************/
        // Se construyen los mapas de resolución por id
        $mapGrupos    = [];
        foreach (($arrGrupos['data'] ?? []) as $row) {
            $mapGrupos[$row['ID']] = $row['Nombre'];
        }
        $mapSubgrupos = [];
        foreach (($arrSubgrupos['data'] ?? []) as $row) {
            $mapSubgrupos[$row['ID1']] = ['idGrupo' => $row['ID2'], 'Nombre' => $row['Nombre']];
        }
        $mapUnimed    = [];
        foreach (($arrUnimed['data'] ?? []) as $row) {
            $mapUnimed[$row['ID']] = $row['Nombre'];
        }
        $mapEstado    = [];
        foreach (($arrEstado['data'] ?? []) as $row) {
            $mapEstado[$row['ID']] = $row['Nombre'];
        }

        /************************************/
        // Retorno los datos
        return [
            'status'       => true,
            'arrGrupos'    => $arrGrupos['data'] ?? [],
            'arrSubgrupos' => $arrSubgrupos['data'] ?? [],
            'arrUnimed'    => $arrUnimed['data'] ?? [],
            'arrEstado'    => $arrEstado['data'] ?? [],
            'mapGrupos'    => $mapGrupos,
            'mapSubgrupos' => $mapSubgrupos,
            'mapUnimed'    => $mapUnimed,
            'mapEstado'    => $mapEstado,
        ];
    }

    /*******************************************************************/
    // Se persiste el arreglo JSON de sensores de una máquina
    /*******************************************************************/
    private function sensorPersist($idMaquina, array $arrSensores, array $DataCheck){

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idMaquina,Sensores',
            'required'  => 'idMaquina',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'maquinas_listado',
            'where'     => 'idMaquina',
            'Post'      => ['idMaquina' => $idMaquina, 'Sensores' => $this->sensorEncode($arrSensores)]
        ];

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Preparo los datos
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        // Ejecuto la query
        $Response = $this->Base_update($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            $this->failOperation($Response);
        }

        /************************************/
        // Confirmar transacción
        $this->Base_transactionCommit();

        /************************************/
        // Retorno la respuesta
        return $Response;
    }

    /*******************************************************************/
    // Se decodifica el JSON de sensores
    /*******************************************************************/
    private function sensorDecode($json){

        /************************************/
        // Si no hay datos, se retorna arreglo vacío
        if (!is_string($json) || trim($json) === '') {
            return [];
        }

        /************************************/
        // Se decodifica
        $arr = json_decode($json, true);

        /************************************/
        // Retorno el arreglo (o vacío si es inválido)
        return is_array($arr) ? $arr : [];
    }

    /*******************************************************************/
    // Se codifica el arreglo de sensores
    /*******************************************************************/
    private function sensorEncode(array $arrSensores){

        /************************************/
        // Retorno el JSON
        return json_encode(array_values($arrSensores), JSON_UNESCAPED_UNICODE);
    }

    /*******************************************************************/
    // Se busca un sensor por uid (retorna índice o false)
    /*******************************************************************/
    private function sensorFind(array $arrSensores, $uid){

        /************************************/
        // Sin uid no se puede buscar
        if ($uid === null || $uid === '') {
            return false;
        }

        /************************************/
        // Se recorre el arreglo
        foreach ($arrSensores as $indice => $sensor) {
            if (isset($sensor['uid']) && (string)$sensor['uid'] === (string)$uid) {
                return $indice;
            }
        }

        /************************************/
        // No encontrado
        return false;
    }

    /*******************************************************************/
    // Se validan los datos del sensor
    /*******************************************************************/
    private function dataCheck($POST){
        // Variables
        $DataChecking = [
            'emptyData'                  => '',
            'encode'                     => '',
            'ValidarEmail'               => '',
            'ValidarNumero'              => 'idGrupo,idSubGrupo,idUniMed,idEstado',
            'ValidarEntero'              => 'idGrupo,idSubGrupo,idUniMed,idEstado',
            'ValidarRut'                 => '',
            'ValidarPatente'             => '',
            'ValidarFecha'               => '',
            'ValidarHora'                => '',
            'ValidarURL'                 => '',
            'ValidarLargoMinimo'         => 'Nombre',
            'ValidarLargoMinimoN'        => 3,
            'ValidarLargoMaximoPorCampo' => [
                'Nombre' => 120,
            ],
            'ValidarPalabrasCensuradas' => 'Nombre',
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

    /*******************************************************************/
    // Se valida la persistencia del JSON (sin validaciones de texto)
    /*******************************************************************/
    private function dataCheckPersist(){
        // Variables
        $DataChecking = [
            'emptyData'                  => '',
            'encode'                     => '',
            'ValidarEmail'               => '',
            'ValidarNumero'              => '',
            'ValidarEntero'              => '',
            'ValidarRut'                 => '',
            'ValidarPatente'             => '',
            'ValidarFecha'               => '',
            'ValidarHora'                => '',
            'ValidarURL'                 => '',
            'ValidarLargoMinimo'         => '',
            'ValidarLargoMinimoN'        => 0,
            'ValidarLargoMaximoPorCampo' => [],
            'ValidarPalabrasCensuradas'  => '',
            'ValidarEspaciosVacios'      => '',
            'ValidarMayusculas'          => '',
            'ValidarCoincidencias'       => '',
            'ValidarDominioEmail'        => '',
            'ValidarPasswordSegura'      => '',
            'ValidarFechaRango'          => '',
            'ValidarEdadMinima'          => '',
            'ValidarJSON'                => '',
            'ValidarUUID'                => '',
            'ValidarIP'                  => '',
            'ValidarSoloAlfanumerico'    => '',
            'ValidarSoloLetras'          => '',
            'Post'                       => [],
        ];
        // Retorno los datos
        return $DataChecking;
    }

}
