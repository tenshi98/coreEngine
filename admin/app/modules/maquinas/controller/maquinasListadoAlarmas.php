<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class maquinasListadoAlarmas extends ControllerBase {

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
		$this->WidgetsViews   = new UIWidgetsViews();
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
        // Se no existe el ID
        if (!isset($params['id'])) {
            Response::error('Registro ID inexistente', 400);
        }

        /************************************/
        // Se obtiene el ID de la máquina
        $MaquinaID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        // Se verifica
        if (!$this->isValidDecrypted($MaquinaID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se valida que la máquina exista
        $rowData    = $this->getMaquina($MaquinaID['data']);
        // Se obtienen los sensores activos y los catálogos
        $arrSensores = $this->getSensoresActivos($MaquinaID['data']);
        $arrCatalog  = $this->getCatalogos();

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($rowData['status'] === true && $arrSensores['status'] === true && $arrCatalog['status'] === true) {

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
                'arrSensores'      => $this->sensoresSelect($arrSensores['arrSensores']),
                'arrTipos'         => $arrCatalog['arrTipos'],
                'arrPrioridades'   => $arrCatalog['arrPrioridades'],
                'arrUnimed'        => $arrCatalog['arrUnimed'],
                'arrEstado'        => $arrCatalog['arrEstado'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Alarmas-formNew.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData, $arrSensores, $arrCatalog]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Actualizar Lista
    /*******************************************************************/
    public function UpdateList($f3, $params){

        /************************************/
        // Se no existe el ID
        if (!isset($params['id'])) {
            Response::error('Registro ID inexistente', 400);
        }

        /************************************/
        // Se obtiene el ID de la máquina
        $MaquinaID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        // Se verifica
        if (!$this->isValidDecrypted($MaquinaID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se obtiene la máquina y su JSON de alarmas
        $rowData     = $this->getMaquina($MaquinaID['data']);
        $arrAlarmas  = $rowData['status'] === true ? $this->alarmaDecode($rowData['data']['Alarmas'] ?? '') : [];

        /************************************/
        // Se resuelven los nombres actuales de los sensores
        $arrSensores = $this->getSensoresActivos($MaquinaID['data']);
        if ($arrSensores['status'] === true) {
            $arrAlarmas = $this->alarmaHydrate($arrAlarmas, $arrSensores['arrSensores']);
        }

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
                /*=========== Datos Consultados ===========*/
                'arrAlarmas'    => $arrAlarmas,
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Alarmas-UpdateList.php');
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
        // Se no existe el ID
        if (!isset($params['id'])) {
            Response::error('Registro ID inexistente', 400);
        }

        /************************************/
        // Se obtiene el ID de la máquina
        $MaquinaID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        // Se verifica
        if (!$this->isValidDecrypted($MaquinaID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se obtiene la alarma por uid
        $alarma = $this->getAlarma($MaquinaID['data'], $_GET['uid'] ?? '');
        if ($alarma === false) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se resuelven los nombres actuales de los sensores
        $arrSensores = $this->getSensoresActivos($MaquinaID['data']);
        if ($arrSensores['status'] === true) {
            $alarma = $this->alarmaHydrate([$alarma], $arrSensores['arrSensores'])[0];
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
            'rowData'       => $alarma,
        ];

        /************************************/
        // Se instancia la vista
        $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Alarmas-View.php');
    }

    /*******************************************************************/
    // Editar
    /*******************************************************************/
    public function GetID($f3, $params){

        /************************************/
        // Se no existe el ID
        if (!isset($params['id'])) {
            Response::error('Registro ID inexistente', 400);
        }

        /************************************/
        // Se obtiene el ID de la máquina
        $MaquinaID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        // Se verifica
        if (!$this->isValidDecrypted($MaquinaID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se obtiene la alarma por uid
        $alarma = $this->getAlarma($MaquinaID['data'], $_GET['uid'] ?? '');
        if ($alarma === false) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se obtienen los sensores activos y los catálogos
        $arrSensores = $this->getSensoresActivos($MaquinaID['data']);
        $arrCatalog  = $this->getCatalogos();

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrSensores['status'] === true && $arrCatalog['status'] === true) {
            /************************************/
            // Se resuelven los nombres actuales de los sensores
            $alarma = $this->alarmaHydrate([$alarma], $arrSensores['arrSensores'])[0];

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
                'rowData'          => $alarma,
                'idMaquina'        => $MaquinaID['data'],
                'arrSensores'      => $this->sensoresSelect($arrSensores['arrSensores']),
                'arrTipos'         => $arrCatalog['arrTipos'],
                'arrPrioridades'   => $arrCatalog['arrPrioridades'],
                'arrUnimed'        => $arrCatalog['arrUnimed'],
                'arrEstado'        => $arrCatalog['arrEstado'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Alarmas-formEdit.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrSensores, $arrCatalog]);
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
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /************************************/
        // Se construye la alarma resolviendo los nombres
        $alarma = $this->buildAlarma($_POST, null);

        /************************************/
        // Se obtiene la máquina y sus alarmas actuales
        $MaquinaID   = $_POST['idMaquina'] ?? '';
        $rowData     = $this->getMaquina($MaquinaID);

        /************************************/
        // Si falla la ejecucion
        if ($rowData['status'] === false) {
            // Se reporta el error
            Response::error('Error al operar con la Base de Datos', 500, $rowData['error'] ?? '');
        }
        $arrAlarmas = $this->alarmaDecode($rowData['data']['Alarmas'] ?? '');

        // Se agrega al final del arreglo (RN-015)
        $arrAlarmas[] = $alarma;

        /************************************/
        // Se persiste el arreglo
        $Response = $this->alarmaPersist($MaquinaID, $arrAlarmas, $DataCheck);

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
        // Se valida el uid recibido
        $uid = (string)($_POST['uid'] ?? '');
        if ($uid === '') {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /************************************/
        // Se construye la alarma resolviendo los nombres
        $alarma = $this->buildAlarma($_POST, $uid);

        /************************************/
        // Se obtiene la máquina y sus alarmas actuales
        $MaquinaID   = $_POST['idMaquina'] ?? '';
        $rowData     = $this->getMaquina($MaquinaID);
        if ($rowData['status'] === false) {
            // Se reporta el error
            Response::error('Error al operar con la Base de Datos', 500, $rowData['error'] ?? '');
        }
        $arrAlarmas = $this->alarmaDecode($rowData['data']['Alarmas'] ?? '');

        // Se reemplaza la alarma por su uid (RN-015)
        $indice = $this->alarmaFind($arrAlarmas, $uid);
        if ($indice === false) {
            Response::error('Registro inválido', 400);
        }
        $arrAlarmas[$indice] = $alarma;

        /************************************/
        // Se persiste el arreglo
        $Response = $this->alarmaPersist($MaquinaID, $arrAlarmas, $DataCheck);

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data']);

    }

    /*******************************************************************/
    // Borrar alarma
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
        // Se verifica
        if (!$this->isValidDecrypted($MaquinaID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se obtiene la máquina y sus alarmas actuales
        $rowData     = $this->getMaquina($MaquinaID['data']);
        if ($rowData['status'] === false) {
            // Se reporta el error
            Response::error('Error al operar con la Base de Datos', 500, $rowData['error'] ?? '');
        }
        $arrAlarmas = $this->alarmaDecode($rowData['data']['Alarmas'] ?? '');

        /************************************/
        // Se elimina la alarma por su uid (RN-015)
        $indice = $this->alarmaFind($arrAlarmas, $dataDelete['uid'] ?? '');
        if ($indice === false) {
            Response::error('Registro inválido', 400);
        }
        unset($arrAlarmas[$indice]);
        // Se compacta el arreglo (RN-015)
        $arrAlarmas = array_values($arrAlarmas);

        /************************************/
        // Se persiste el arreglo
        $Response = $this->alarmaPersist($MaquinaID['data'], $arrAlarmas, $this->dataCheckPersist());

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
            'data'    => 'idMaquina,Nombre,Sensores,Alarmas',
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
    // Se obtiene una alarma por uid dentro de una máquina
    /*******************************************************************/
    private function getAlarma($idMaquina, $uid){

        /************************************/
        // Se obtiene la máquina
        $rowData = $this->getMaquina($idMaquina);
        if ($rowData['status'] === false) {
            return false;
        }

        /************************************/
        // Se busca la alarma por uid
        $arrAlarmas = $this->alarmaDecode($rowData['data']['Alarmas'] ?? '');
        $indice     = $this->alarmaFind($arrAlarmas, $uid);

        /************************************/
        // Retorno la alarma (o false)
        return $indice === false ? false : $arrAlarmas[$indice];

    }

    /*******************************************************************/
    // Se obtienen los sensores activos de una máquina
    /*******************************************************************/
    private function getSensoresActivos($idMaquina){

        /************************************/
        // Se obtiene la máquina
        $rowData = $this->getMaquina($idMaquina);
        if ($rowData['status'] === false) {
            return ['status' => false, 'error' => $rowData['error'] ?? '', 'arrSensores' => [], 'mapSensores' => []];
        }

        /************************************/
        // Se filtran los sensores activos
        $arrSensores = [];
        $mapSensores = [];
        foreach ($this->alarmaDecode($rowData['data']['Sensores'] ?? '') as $sensor) {
            if ((int)($sensor['idEstado'] ?? 0) !== 1) {
                continue;
            }
            $arrSensores[] = $sensor;
            if (isset($sensor['uid'])) {
                $mapSensores[(string)$sensor['uid']] = $sensor;
            }
        }

        /************************************/
        // Retorno los datos
        return ['status' => true, 'arrSensores' => $arrSensores, 'mapSensores' => $mapSensores];

    }

    /*******************************************************************/
    // Se preparan los sensores para los selectores del formulario
    /*******************************************************************/
    private function sensoresSelect(array $arrSensores){

        /************************************/
        // Se transforman al formato ID/Nombre de los widgets
        $arrSelect = [];
        foreach ($arrSensores as $sensor) {
            $arrSelect[] = [
                'ID'     => $sensor['uid'] ?? '',
                'Nombre' => $sensor['Nombre'] ?? '',
            ];
        }

        /************************************/
        // Retorno los datos
        return $arrSelect;

    }

    /*******************************************************************/
    // Se obtienen los catálogos y sus mapas de resolución
    /*******************************************************************/
    private function getCatalogos(){

        /************************************/
        // Tipos de alarma
        $query = [
            'data'    => 'idTipoAlarma AS ID,Nombre',
            'table'   => 'core_tipos_alarmas',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'idTipoAlarma ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrTipos = $this->Base_GetList($xParams);

        /************************************/
        // Prioridades de alarma
        $query = [
            'data'    => 'idPrioridad AS ID,Nombre',
            'table'   => 'core_tipos_alarmas_prioridad',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'idPrioridad ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrPrioridades = $this->Base_GetList($xParams);

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
        foreach ([$arrTipos, $arrPrioridades, $arrUnimed, $arrEstado] as $arr) {
            if ($arr['status'] === false) {
                return ['status' => false, 'error' => $arr['error'] ?? ''];
            }
        }

        /************************************/
        // Se construyen los mapas de resolución por id
        $mapTipos = [];
        foreach (($arrTipos['data'] ?? []) as $row) {
            $mapTipos[$row['ID']] = $row['Nombre'];
        }
        $mapPrioridades = [];
        foreach (($arrPrioridades['data'] ?? []) as $row) {
            $mapPrioridades[$row['ID']] = $row['Nombre'];
        }
        $mapUnimed = [];
        foreach (($arrUnimed['data'] ?? []) as $row) {
            $mapUnimed[$row['ID']] = $row['Nombre'];
        }
        $mapEstado = [];
        foreach (($arrEstado['data'] ?? []) as $row) {
            $mapEstado[$row['ID']] = $row['Nombre'];
        }

        /************************************/
        // Retorno los datos
        return [
            'status'          => true,
            'arrTipos'        => $arrTipos['data'] ?? [],
            'arrPrioridades'  => $arrPrioridades['data'] ?? [],
            'arrUnimed'       => $arrUnimed['data'] ?? [],
            'arrEstado'       => $arrEstado['data'] ?? [],
            'mapTipos'        => $mapTipos,
            'mapPrioridades'  => $mapPrioridades,
            'mapUnimed'       => $mapUnimed,
            'mapEstado'       => $mapEstado,
        ];
    }

    /*******************************************************************/
    // Se construye la alarma resolviendo los nombres desde los catálogos
    /*******************************************************************/
    private function buildAlarma($POST, $uid){

        /************************************/
        // Se validan los campos obligatorios (RN-001)
        $requeridos = [
            'Nombre'       => 'Nombre',
            'idTipoAlarma' => 'Tipo de Alarma',
            'MaxErrores'   => 'N° Máximo Errores',
            'idPrioridad'  => 'Prioridad',
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
            // Se reporta el error
            Response::error('Error al operar con la Base de Datos', 500, $arrCatalog['error'] ?? '');
        }

        /************************************/
        // Se validan los catálogos (RN-012)
        if (!isset($arrCatalog['mapTipos'][$POST['idTipoAlarma']])) {
            Response::error('El tipo de alarma seleccionado no es válido', 400);
        }
        if (!isset($arrCatalog['mapPrioridades'][$POST['idPrioridad']])) {
            Response::error('La prioridad seleccionada no es válida', 400);
        }
        // Unidad de medida opcional con default 1 (RN-007)
        $idUniMed = (isset($POST['idUniMed']) && $POST['idUniMed'] !== '') ? $POST['idUniMed'] : 1;
        if (!isset($arrCatalog['mapUnimed'][$idUniMed])) {
            Response::error('La unidad de medida seleccionada no es válida', 400);
        }
        // Estado por defecto Activo en alta (RN-008)
        $idEstado = (isset($POST['idEstado']) && $POST['idEstado'] !== '') ? $POST['idEstado'] : 1;
        if (!isset($arrCatalog['mapEstado'][$idEstado])) {
            Response::error('El estado seleccionado no es válido', 400);
        }

        /************************************/
        // Se validan los números de errores (RN-006)
        if (!is_numeric($POST['MaxErrores']) || (int)$POST['MaxErrores'] < 1) {
            Response::error('El N° Máximo de Errores debe ser un entero mayor o igual a 1', 400);
        }
        $actualErrores = (isset($POST['ActualErrores']) && $POST['ActualErrores'] !== '') ? $POST['ActualErrores'] : 0;
        if (!is_numeric($actualErrores) || (int)$actualErrores < 0) {
            Response::error('El N° Actual de Errores debe ser un entero mayor o igual a 0', 400);
        }

        /************************************/
        // Se obtienen los sensores activos de la máquina (RN-002/RN-003)
        $arrSensoresActivos = $this->getSensoresActivos($POST['idMaquina'] ?? '');
        if ($arrSensoresActivos['status'] === false) {
            // Se reporta el error
            Response::error('Error al operar con la Base de Datos', 500, $arrSensoresActivos['error'] ?? '');
        }
        $mapSensores = $arrSensoresActivos['mapSensores'];

        /************************************/
        // Se obtienen las filas de sensor enviadas
        $arrFilas = $POST['Sensor_idSensor'] ?? [];
        if (!is_array($arrFilas)) {
            $arrFilas = [];
        }

        /************************************/
        // Se construyen y validan las filas
        $idTipoAlarma = (int)$POST['idTipoAlarma'];
        $arrSensores  = [];
        $arrUids      = [];
        foreach ($arrFilas as $i => $idSensor) {
            $idSensor = trim((string)$idSensor);

            // Se ignoran las filas completamente vacías
            if ($idSensor === '' && $this->filaVacia($POST, $i)) {
                continue;
            }
            // Si hay datos pero no hay sensor, la fila no es válida
            if ($idSensor === '' || !isset($mapSensores[$idSensor])) {
                Response::error('El sensor seleccionado no es válido o no está activo', 400);
            }
            // No se permite repetir el sensor (RN-004)
            if (isset($arrUids[$idSensor])) {
                Response::error('El sensor seleccionado está repetido', 400);
            }
            $arrUids[$idSensor] = true;

            // Se construye la fila según el tipo
            $fila = [
                'idSensor'        => $idSensor,
                'Sensor'          => $mapSensores[$idSensor]['Nombre'] ?? '',
                'RangoInicio'     => null,
                'RangoTermino'    => null,
                'ValorEspecifico' => null,
                'ValorEncendido'  => null,
            ];

            if ($idTipoAlarma === 1 || $idTipoAlarma === 2) {
                $inicio  = $this->valorDecimal($POST['RangoInicio'][$i] ?? '');
                $termino = $this->valorDecimal($POST['RangoTermino'][$i] ?? '');
                if ($inicio === false || $termino === false) {
                    Response::error('El rango ingresado no es válido', 400);
                }
                // RN-009: el inicio no puede superar el término
                if ($inicio > $termino) {
                    Response::error('El rango ingresado no es válido', 400);
                }
                $fila['RangoInicio']  = $inicio;
                $fila['RangoTermino'] = $termino;
            } elseif ($idTipoAlarma === 3) {
                $especifico = $this->valorDecimal($POST['ValorEspecifico'][$i] ?? '');
                if ($especifico === false) {
                    Response::error('El valor específico ingresado no es válido', 400);
                }
                $fila['ValorEspecifico'] = $especifico;
            } elseif ($idTipoAlarma === 8) {
                $encendido = $this->valorDecimal($POST['ValorEncendido'][$i] ?? '');
                if ($encendido === false) {
                    Response::error('El valor de encendido ingresado no es válido', 400);
                }
                $fila['ValorEncendido'] = $encendido;
            }

            $arrSensores[] = $fila;
        }

        // Debe existir al menos un sensor activo (RN-002)
        if (empty($arrSensores)) {
            Response::error('Debe agregar al menos un sensor activo', 400);
        }

        /************************************/
        // Se construye la alarma con los nombres desnormalizados
        $alarma = [
            'uid'                  => ($uid !== null && $uid !== '') ? $uid : uniqid(),
            'Nombre'               => $POST['Nombre'],
            'idTipoAlarma'         => $idTipoAlarma,
            'TipoAlarma'           => $arrCatalog['mapTipos'][$POST['idTipoAlarma']],
            'MaxErrores'           => (int)$POST['MaxErrores'],
            'ActualErrores'        => (int)$actualErrores,
            'ValorError'           => null,
            'ValorMinimo'          => null,
            'ValorMaximo'          => null,
            'PorcentajeDiferencia' => null,
            'HoraInicio'           => null,
            'HoraTermino'          => null,
            'idPrioridad'          => (int)$POST['idPrioridad'],
            'Prioridad'            => $arrCatalog['mapPrioridades'][$POST['idPrioridad']],
            'idUniMed'             => (int)$idUniMed,
            'UniMed'               => $arrCatalog['mapUnimed'][$idUniMed],
            'idEstado'             => (int)$idEstado,
            'Estado'               => $arrCatalog['mapEstado'][$idEstado],
            'Sensores'             => $arrSensores,
        ];

        /************************************/
        // Se validan los campos propios de cada tipo (RN-005)
        if ($idTipoAlarma === 4) {
            $valorError = $this->valorDecimal($POST['ValorError'] ?? '');
            if ($valorError === false) {
                Response::error('El valor de error ingresado no es válido', 400);
            }
            $alarma['ValorError'] = $valorError;
        } elseif ($idTipoAlarma === 5 || $idTipoAlarma === 6) {
            $minimo = $this->valorDecimal($POST['ValorMinimo'] ?? '');
            $maximo = $this->valorDecimal($POST['ValorMaximo'] ?? '');
            if ($minimo === false || $maximo === false || $minimo > $maximo) {
                Response::error('El rango ingresado no es válido', 400);
            }
            $alarma['ValorMinimo'] = $minimo;
            $alarma['ValorMaximo'] = $maximo;
        } elseif ($idTipoAlarma === 7) {
            $porcentaje = $this->valorDecimal($POST['PorcentajeDiferencia'] ?? '');
            if ($porcentaje === false || $porcentaje < 0 || $porcentaje > 100) {
                Response::error('El porcentaje debe estar entre 0 y 100', 400);
            }
            $alarma['PorcentajeDiferencia'] = $porcentaje;
        } elseif ($idTipoAlarma === 8) {
            $horaInicio  = trim((string)($POST['HoraInicio'] ?? ''));
            $horaTermino = trim((string)($POST['HoraTermino'] ?? ''));
            // RN-011: formato HH:mm (la ventana puede cruzar la medianoche)
            if (!$this->horaValida($horaInicio) || !$this->horaValida($horaTermino)) {
                Response::error('La hora ingresada no es válida', 400);
            }
            $alarma['HoraInicio']  = $horaInicio;
            $alarma['HoraTermino'] = $horaTermino;
        }

        /************************************/
        // Retorno la alarma
        return $alarma;
    }

    /*******************************************************************/
    // Se determina si una fila de sensor está completamente vacía
    /*******************************************************************/
    private function filaVacia($POST, $i){
        foreach (['RangoInicio', 'RangoTermino', 'ValorEspecifico', 'ValorEncendido'] as $campo) {
            if (isset($POST[$campo][$i]) && trim((string)$POST[$campo][$i]) !== '') {
                return false;
            }
        }
        return true;
    }

    /*******************************************************************/
    // Se convierte un valor decimal (o devuelve false si no es válido)
    /*******************************************************************/
    private function valorDecimal($valor){
        $valor = str_replace(',', '.', trim((string)$valor));
        if ($valor === '' || !is_numeric($valor)) {
            return false;
        }
        return (strpos($valor, '.') !== false) ? (float)$valor : (int)$valor;
    }

    /*******************************************************************/
    // Se valida una hora en formato HH:mm
    /*******************************************************************/
    private function horaValida($hora){
        return (bool)preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', (string)$hora);
    }

    /*******************************************************************/
    // Se resuelven los nombres actuales de catálogo y sensor
    /*******************************************************************/
    private function alarmaHydrate(array $arrAlarmas, array $arrSensores){

        /************************************/
        // Se indexan los sensores activos por uid
        $mapSensores = [];
        foreach ($arrSensores as $sensor) {
            if (isset($sensor['uid'])) {
                $mapSensores[(string)$sensor['uid']] = $sensor;
            }
        }

        /************************************/
        // Se obtienen los catálogos para re-resolver nombres
        $arrCatalog = $this->getCatalogos();
        $mapTipos       = $arrCatalog['status'] === true ? $arrCatalog['mapTipos'] : [];
        $mapPrioridades = $arrCatalog['status'] === true ? $arrCatalog['mapPrioridades'] : [];
        $mapUnimed      = $arrCatalog['status'] === true ? $arrCatalog['mapUnimed'] : [];
        $mapEstado      = $arrCatalog['status'] === true ? $arrCatalog['mapEstado'] : [];

        /************************************/
        // Se recorre cada alarma
        foreach ($arrAlarmas as &$alarma) {
            if (isset($mapTipos[$alarma['idTipoAlarma']])) {
                $alarma['TipoAlarma'] = $mapTipos[$alarma['idTipoAlarma']];
            }
            if (isset($mapPrioridades[$alarma['idPrioridad']])) {
                $alarma['Prioridad'] = $mapPrioridades[$alarma['idPrioridad']];
            }
            if (isset($mapUnimed[$alarma['idUniMed']])) {
                $alarma['UniMed'] = $mapUnimed[$alarma['idUniMed']];
            }
            if (isset($mapEstado[$alarma['idEstado']])) {
                $alarma['Estado'] = $mapEstado[$alarma['idEstado']];
            }

            // Se re-resuelven los nombres de los sensores (RN-014)
            foreach (($alarma['Sensores'] ?? []) as &$fila) {
                $idSensor = (string)($fila['idSensor'] ?? '');
                if (isset($mapSensores[$idSensor])) {
                    $fila['Sensor'] = $mapSensores[$idSensor]['Nombre'] ?? ($fila['Sensor'] ?? '');
                } else {
                    $nombre = (string)($fila['Sensor'] ?? '');
                    if ($nombre === '') {
                        $nombre = 'Sensor eliminado';
                    }
                    if (strpos($nombre, ' (no disponible)') === false) {
                        $nombre .= ' (no disponible)';
                    }
                    $fila['Sensor'] = $nombre;
                }
            }
            unset($fila);
        }
        unset($alarma);

        /************************************/
        // Retorno las alarmas
        return $arrAlarmas;

    }

    /*******************************************************************/
    // Se persiste el arreglo JSON de alarmas de una máquina
    /*******************************************************************/
    private function alarmaPersist($idMaquina, array $arrAlarmas, array $DataCheck){

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idMaquina,Alarmas',
            'required'  => 'idMaquina',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'maquinas_listado',
            'where'     => 'idMaquina',
            'Post'      => ['idMaquina' => $idMaquina, 'Alarmas' => $this->alarmaEncode($arrAlarmas)]
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
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se diferencian los errores de validación de los de base de datos
            if (is_array($Response['error'] ?? null)) {
                Response::error('Datos inválidos', 400, $Response['error']);
            }
            // Se reporta el error
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
        }

        /************************************/
        // Confirmar transacción
        $ResponseCommit = $this->Base_transactionCommit();

        /************************************/
        // Si falla la la ejecucion, se revierte de inmediato
        if ($ResponseCommit['status'] === false) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se reporta el error
            Response::error('Error al operar con la Base de Datos', 500, $ResponseCommit['error'] ?? '');
        }

        /************************************/
        // Retorno la respuesta
        return $Response;

    }

    /*******************************************************************/
    // Se decodifica el JSON de alarmas
    /*******************************************************************/
    private function alarmaDecode($json){

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
    // Se codifica el arreglo de alarmas
    /*******************************************************************/
    private function alarmaEncode(array $arrAlarmas){

        /************************************/
        // Retorno el JSON
        return json_encode(array_values($arrAlarmas), JSON_UNESCAPED_UNICODE);
    }

    /*******************************************************************/
    // Se busca una alarma por uid (retorna índice o false)
    /*******************************************************************/
    private function alarmaFind(array $arrAlarmas, $uid){

        /************************************/
        // Sin uid no se puede buscar
        if ($uid === null || $uid === '') {
            return false;
        }

        /************************************/
        // Se recorre el arreglo
        foreach ($arrAlarmas as $indice => $alarma) {
            if (isset($alarma['uid']) && (string)$alarma['uid'] === (string)$uid) {
                return $indice;
            }
        }

        /************************************/
        // No encontrado
        return false;
    }

    /*******************************************************************/
    // Se validan los datos de la alarma
    /*******************************************************************/
    private function dataCheck($POST){
        // Variables
        $DataChecking = [
            'emptyData'                  => '',
            'encode'                     => '',
            'ValidarEmail'               => '',
            'ValidarNumero'              => 'idMaquina,idTipoAlarma,idPrioridad,idUniMed,idEstado',
            'ValidarEntero'              => 'idMaquina,idTipoAlarma,idPrioridad,idUniMed,idEstado,MaxErrores,ActualErrores',
            'ValidarRut'                 => '',
            'ValidarPatente'             => '',
            'ValidarFecha'               => '',
            'ValidarHora'                => 'HoraInicio,HoraTermino',
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
            'ValidarNumero'              => 'idMaquina',
            'ValidarEntero'              => 'idMaquina',
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
