<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class kanbanTareasParticipantes extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $ServerServer;

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'kanbanTareas';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->ServerServer   = new FunctionsServerServer();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  DATOS                                     */
    /******************************************************************************/
    /*******************************************************************/
    // NewData
    /*******************************************************************/
    public function NewData($f3, $params){

        /************************************/
        // Se obtiene el ID
        $KanbanID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($KanbanID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idKanban,Titulo',
            'table'   => 'kanban_tareas',
            'join'    => '',
            'where'   => 'idKanban = ?',
            'params'  => [$KanbanID['data']],
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
            'data'    => 'idUsuario',
            'table'   => 'kanban_tareas_participantes',
            'join'    => '',
            'where'   => 'idKanban = ?',
            'params'  => [$KanbanID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'idUsuario ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams       = ['query' => $query];
        // Ejecuto la query
        $arrExistentes = $this->Base_GetList($xParams);

        /************************************/
        // Se filtran los ya existentes
        $ElementsIDs = [0];
        if ($arrExistentes['status'] === true) {
            // Obtiene los identificadores de las campañas existentes y los agrega
            // a la lista de valores.
            $ElementsIDs = array_merge($ElementsIDs, array_column($arrExistentes['data'], 'idUsuario'));
        }
        // Se generan los placeholders necesarios para vincular posteriormente
        // cada identificador mediante parámetros preparados.
        $placeholders = implode(',', array_fill(0, count($ElementsIDs), '?'));

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idUsuario AS ID,Nombre',
            'table'   => 'usuarios_listado',
            'join'    => '',
            'where'   => 'idTipoUsuario != ? AND idEstado = ? AND idUsuario NOT IN ('.$placeholders.')',
            'params'  => array_merge([1, 1], $ElementsIDs),
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams     = ['query' => $query];
        // Ejecuto la query
        $arrUsuarios = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrUsuarios['status']){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'    => $this->FormInputs,
                'Fnc_Codification'  => $this->Codification,
                'Fnc_ServerServer'  => $this->ServerServer,
                /*=========== Datos Consultados ===========*/
                'rowData'       => $rowData['data'],
                'arrUsuarios'   => $arrUsuarios['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'Participantes-formNew.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrUsuarios]);
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
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Usuario creador (sesion): no se acepta del formulario
        $_POST['idUsuario'] = $f3->get('SESSION.DataInfo.UserID');

        /************************************/
        // Variables
        $ndata_2 = (isset($_POST['idParticipante']) && is_array($_POST['idParticipante'])) ? count($_POST['idParticipante']) : 0;
        // Generacion de errores
        if($ndata_2==0) {
            Response::error('No hay Participantes en la tarea', 400);
        }

        /************************************/
        // La tarea debe existir y seguir abierta
        $this->validarTareaAbierta($_POST['idKanban'] ?? '');

        /************************************/
        // Se inicia la transacción (participantes + historial)
        $this->Base_transactionBegin();

        /************************************/
        // Se acumulan las filas a insertar (una sola sentencia)
        $rowsParticipantes = [];
        for($j2 = 0; $j2 < $ndata_2; $j2++){
            $rowsParticipantes[] = [
                'idKanban'  => $_POST['idKanban'],            //idKanban
                'idUsuario' => $_POST['idParticipante'][$j2], //Participantes
            ];
        }
        /************************************/
        // Se genera el chequeo (se valida cada fila)
        $dataCheck_1 = $this->dataCheck_1('');
        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idKanban,idUsuario',
            'required'  => 'idKanban,idUsuario',
            'table'     => 'kanban_tareas_participantes',
            'rows'      => $rowsParticipantes
        ];
        // Preparo los datos
        $xParams = ['DataCheck' => $dataCheck_1, 'query' => $query];
        // Ejecuto la query
        $respParticipantes = $this->Base_insertMultiple($xParams);
        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($respParticipantes['status'] === false) {
            $this->failOperation($respParticipantes);
        }

        /************************************/
        //Se agrega historial (fecha y hora del servidor)
        $arrTareas = [
            'idKanban'    => $_POST['idKanban'],                //idKanban
            'idUsuario'   => $_POST['idUsuario'],               //Usuario creador
            'Descripcion' => 'Se agregan nuevos encargados',    //Descripcion
            'Fecha'       => $this->ServerServer->fechaActual(),//Fecha actual (servidor)
            'Hora'        => $this->ServerServer->horaActual(), //Hora actual (servidor)
        ];
        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idKanban,idUsuario,Descripcion,Fecha,Hora',
            'required'  => 'idKanban,idUsuario,Descripcion,Fecha,Hora',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'kanban_tareas_historial',
            'Post'      => $arrTareas
        ];
        /************************************/
        // Se genera el chequeo
        $dataCheck_2 = $this->dataCheck_2($arrTareas);
        // Preparo los datos
        $xParams = ['DataCheck' => $dataCheck_2, 'query' => $query, 'novalidate' => true];
        $xInsert = $this->Base_insert($xParams);
        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($xInsert['status'] === false) {
            $this->failOperation($xInsert);
        }

        /************************************/
        // Se confirma la transacción
        $this->Base_transactionCommit();

        /************************************/
        //devuelvo el ultimo id
        Response::success($_POST['idKanban']);

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
        // Se obtienen y validan los ID (antes de tocar la Base de Datos)
        $ParticipantesID = $this->Codification->encryptDecrypt('decrypt', $dataDelete['idParticipantes'] ?? '');
        if (!$this->isValidDecrypted($ParticipantesID, 'id')) {
            Response::error('Registro inválido', 400);
        }
        $KanbanDelID = $this->Codification->encryptDecrypt('decrypt', $dataDelete['idKanbanDel'] ?? '');
        if (!$this->isValidDecrypted($KanbanDelID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'usuarios_listado.Nombre',
            'table'   => 'kanban_tareas_participantes',
            'join'    => 'LEFT JOIN usuarios_listado ON usuarios_listado.idUsuario  = kanban_tareas_participantes.idUsuario',
            'where'   => 'kanban_tareas_participantes.idParticipantes = ?',
            'params'  => [$ParticipantesID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /************************************/
        // Si falla la ejecucion o el participante no existe
        if ($rowData['status'] === false) {
            $this->failOperation($rowData);
        }
        if (empty($rowData['data'])) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'files'       => '',
            'table'       => 'kanban_tareas_participantes',
            'where'       => 'idParticipantes',
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
            $this->failOperation($Response);
        }

        //Se agrega historial (usuario de la sesion, fecha y hora del servidor)
        $arrTareas = [
            'idKanban'    => $KanbanDelID['data'],                                          // idKanban
            'idUsuario'   => $f3->get('SESSION.DataInfo.UserID'),                           // Usuario (sesion)
            'Descripcion' => 'Encargado '.($rowData['data']['Nombre'] ?? '').' Borrado',    // Descripcion
            'Fecha'       => $this->ServerServer->fechaActual(),                            // Fecha actual (servidor)
            'Hora'        => $this->ServerServer->horaActual(),                             // Hora actual (servidor)
        ];
        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idKanban,idUsuario,Descripcion,Fecha,Hora',
            'required'  => 'idKanban,idUsuario,Descripcion,Fecha,Hora',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'kanban_tareas_historial',
            'Post'      => $arrTareas
        ];
        /************************************/
        // Se genera el chequeo
        $dataCheck_2 = $this->dataCheck_2($arrTareas);
        // Preparo los datos
        $xParams = ['DataCheck' => $dataCheck_2, 'query' => $query, 'novalidate' => true];
        $this->Base_insert($xParams);
        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data']);

    }

    /******************************************************************************/
    /*                             Métodos privados                               */
    /******************************************************************************/
    /*******************************************************************/
    // Se valida que la tarea exista y siga abierta (responde 400 si no)
    /*******************************************************************/
    private function validarTareaAbierta($idKanban){

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idKanban,idEstadoCierre',
            'table'   => 'kanban_tareas',
            'join'    => '',
            'where'   => 'idKanban = ?',
            'params'  => [is_scalar($idKanban) ? $idKanban : ''],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /************************************/
        // Si falla la ejecucion, la tarea no existe o esta cerrada
        if ($rowData['status'] === false) {
            $this->failOperation($rowData);
        }
        if (empty($rowData['data'])) {
            Response::error('Registro inválido', 400);
        }
        if ((int)$rowData['data']['idEstadoCierre'] !== 1) {
            Response::error('La tarea está cerrada', 400);
        }

    }

    /*******************************************************************/
    // Se validan los datos
    /*******************************************************************/
    private function dataCheck_1($POST){
        // Variables
        $DataChecking = [
            'emptyData'                  => '',
            'encode'                     => '',
            'ValidarEmail'               => '',
            'ValidarNumero'              => 'idKanban,idUsuario',
            'ValidarEntero'              => 'idKanban,idUsuario',
            'ValidarRut'                 => '',
            'ValidarPatente'             => '',
            'ValidarFecha'               => '',
            'ValidarHora'                => '',
            'ValidarURL'                 => '',
            'ValidarLargoMinimo'         => '',
            'ValidarLargoMinimoN'        => 3,
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
            'Post'                       => $POST,
        ];
        // Retorno los datos
        return $DataChecking;
    }

    /*******************************************************************/
    // Se validan los datos
    /*******************************************************************/
    private function dataCheck_2($POST){
        // Variables
        $DataChecking = [
            'emptyData'                  => '',
            'encode'                     => '',
            'ValidarEmail'               => '',
            'ValidarNumero'              => 'idKanban,idUsuario',
            'ValidarEntero'              => 'idKanban,idUsuario',
            'ValidarRut'                 => '',
            'ValidarPatente'             => '',
            'ValidarFecha'               => 'Fecha',
            'ValidarHora'                => 'Hora',
            'ValidarURL'                 => '',
            'ValidarLargoMinimo'         => 'Descripcion',
            'ValidarLargoMinimoN'        => 3,
            'ValidarLargoMaximoPorCampo' => [],
            'ValidarPalabrasCensuradas'  => 'Descripcion',
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
            'Post'                       => $POST,
        ];
        // Retorno los datos
        return $DataChecking;
    }

}
