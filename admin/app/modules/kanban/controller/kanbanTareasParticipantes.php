<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class kanbanTareasParticipantes extends ControllerBase {

    /******************************************************************************/
    // Variables
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $ServerServer;

    /******************************************************************************/
    //Constructor
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
    /******************************************************************************/
    //NewData
    public function NewData($f3, $params){
        /******************************************/
        // Se genera la query
        $query = [
            'data'    => 'idKanban,Titulo',
            'table'   => 'kanban_tareas',
            'join'    => '',
            'where'   => 'idKanban = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        // Se genera la query
        $query = [
            'data'    => 'idUsuario',
            'table'   => 'kanban_tareas_participantes',
            'join'    => '',
            'where'   => 'idKanban = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => 'idUsuario ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams       = ['query' => $query];
        $arrExistentes = $this->Base_GetList($xParams);

        /******************************/
        // Se filtran los ya existentes
        $ElementsIDs = [0];
        if ($arrExistentes['status']) {
            // Obtiene los identificadores de las campañas existentes y los agrega
            // a la lista de valores.
            $ElementsIDs = array_merge($ElementsIDs, array_column($arrExistentes['data'], 'idUsuario'));
        }
        // Se generan los placeholders necesarios para vincular posteriormente
        // cada identificador mediante parámetros preparados.
        $placeholders = implode(',', array_fill(0, count($ElementsIDs), '?'));

        /*******************************************************************/
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
        // Ejecuto la query
        $xParams     = ['query' => $query];
        $arrUsuarios = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrUsuarios['status']){
            /******************************************/
            //Datos enviados a la pagina
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

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'Participantes-formNew.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrUsuarios]);
            //Muestra los errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    /*                                  DATOS                                     */
    /******************************************************************************/
    /******************************************************************************/
    //Crear
    public function Insert($f3){

        /******************************/
        // Usuario creador
        $_POST['idUsuario'] = $f3->get('SESSION.DataInfo.UserID');

        /*******************************************************************/
        // Variables
        $ndata_2 = isset($_POST['idParticipante']) ? count($_POST['idParticipante']) : 0;
        //generacion de errores
        if($ndata_2==0) {
            Response::error('No hay Participantes en la tarea', 500);
        }else{
            /******************************/
            //Recorro las tareas ingresadas
            if(isset($ndata_2)&&$ndata_2!=0){
                for($j2 = 0; $j2 < $ndata_2; $j2++){
                    /******************************/
                    // Se agrega respuesta
                    $arrParticipantes = [
                        'idKanban'  => $_POST['idKanban'],            //idKanban
                        'idUsuario' => $_POST['idParticipante'][$j2], //Participantes
                    ];
                    /******************************/
                    // Se genera la query
                    $query = [
                        'data'      => 'idKanban,idUsuario',
                        'required'  => 'idKanban,idUsuario',
                        'unique'    => '',
                        'encode'    => '',
                        'table'     => 'kanban_tareas_participantes',
                        'Post'      => $arrParticipantes
                    ];
                    //Se genera el chequeo
                    $dataCheck_1 = $this->dataCheck_1($arrParticipantes);
                    // Ejecuto la query
                    $xParams = ['DataCheck' => $dataCheck_1, 'query' => $query];
                    $this->Base_insert($xParams);
                }
            }
            /******************************/
            //Se agrega historial
            $arrTareas = [
                'idKanban'    => $_POST['idKanban'],             //idKanban
                'idUsuario'   => $_POST['idUsuario'],            //Usuario creador
                'Descripcion' => 'Se agregan nuevos encargados', //Descripcion
                'Fecha'       => $_POST['Fecha_Actual'],         //Fecha actual
                'Hora'        => $_POST['Hora_Actual'],          //Hora actual
            ];
            /******************************/
            // Se genera la query
            $query = [
                'data'      => 'idKanban,idUsuario,Descripcion,Fecha,Hora',
                'required'  => 'idKanban,idUsuario,Descripcion,Fecha,Hora',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'kanban_tareas_historial',
                'Post'      => $arrTareas
            ];
            //Se genera el chequeo
            $dataCheck_2 = $this->dataCheck_2($arrTareas);
            // Ejecuto la query
            $xParams = ['DataCheck' => $dataCheck_2, 'query' => $query, 'novalidate' => true];
            $this->Base_insert($xParams);

            /******************************/
            //devuelvo el ultimo id
            Response::success($_POST['idKanban']);
        }

    }

    /******************************************************************************/
    //Borrar dato y archivos
    public function Delete(){
        //Verificacion metodo PUT
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            //Se parsean los datos
            parse_str(file_get_contents("php://input"),$dataDelete);

            /******************************************/
            // Se genera la query
            $query = [
                'data'    => 'usuarios_listado.Nombre',
                'table'   => 'kanban_tareas_participantes',
                'join'    => 'LEFT JOIN usuarios_listado ON usuarios_listado.idUsuario  = kanban_tareas_participantes.idUsuario',
                'where'   => 'kanban_tareas_participantes.idParticipantes = ?',
                'params'  => [$this->Codification->encryptDecrypt('decrypt', $dataDelete['idParticipantes'])],
                'group'   => '',
                'having'  => '',
                'order'   => ''
            ];
            // Ejecuto la query
            $xParams = ['query' => $query];
            $rowData = $this->Base_GetByID($xParams);

            /******************************/
            // Se genera la query
            $query = [
                'files'       => '',
                'table'       => 'kanban_tareas_participantes',
                'where'       => 'idParticipantes',
                'SubCarpeta'  => '',
                'Post'        => $dataDelete
            ];
            // Ejecuto la query
            $xParams  = ['query' => $query];
            $Response = $this->Base_delete($xParams);

            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($rowData['status'] && $Response['status']){
                /******************************/
                //Se agrega historial
                $arrTareas = [
                    'idKanban'    => $this->Codification->encryptDecrypt('decrypt', $dataDelete['idKanbanDel']),  //idKanban
                    'idUsuario'   => $dataDelete['idUsuarioDel'],                                                 //Usuario creador
                    'Descripcion' => 'Encargado '.$rowData['data']['Nombre'].' Borrado',                          //Descripcion
                    'Fecha'       => $dataDelete['Fecha_Actual'],                                                 //Fecha actual
                    'Hora'        => $dataDelete['Hora_Actual'],                                                  //Hora actual
                ];
                /******************************/
                // Se genera la query
                $query = [
                    'data'      => 'idKanban,idUsuario,Descripcion,Fecha,Hora',
                    'required'  => 'idKanban,idUsuario,Descripcion,Fecha,Hora',
                    'unique'    => '',
                    'encode'    => '',
                    'table'     => 'kanban_tareas_historial',
                    'Post'      => $arrTareas
                ];
                //Se genera el chequeo
                $dataCheck_2 = $this->dataCheck_2($arrTareas);
                // Ejecuto la query
                $xParams = ['DataCheck' => $dataCheck_2, 'query' => $query, 'novalidate' => true];
                $this->Base_insert($xParams);
                /******************************/
                // Devuelvo $Response con código 200 (OK)
                Response::success($Response['data']);
            } else {
                // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
                // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
                Response::error('Error al operar con la Base de Datos', 500, $Response['error']);
            }
        }else {
            // Request Method no esperado
            Response::error('Error en el Request Method', 500);
        }
    }

    /******************************************************************************/
    /*                             Métodos privados                               */
    /******************************************************************************/
    /******************************************************************************/
    //Se validan los datos
    private function dataCheck_1($POST){
        // Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => '',
            'ValidarNumero'             => 'idKanban,idUsuario',
            'ValidarEntero'             => 'idKanban,idUsuario',
            'ValidarRut'                => '',
            'ValidarPatente'            => '',
            'ValidarFecha'              => '',
            'ValidarHora'               => '',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => '',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => '',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => '',
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
        //Devuelvo
        return $DataChecking;
    }

    //Se validan los datos
    private function dataCheck_2($POST){
        // Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => '',
            'ValidarNumero'             => 'idKanban,idUsuario',
            'ValidarEntero'             => 'idKanban,idUsuario',
            'ValidarRut'                => '',
            'ValidarPatente'            => '',
            'ValidarFecha'              => 'Fecha',
            'ValidarHora'               => 'Hora',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => 'Descripcion',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => '',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Descripcion',
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
        //Devuelvo
        return $DataChecking;
    }

}
