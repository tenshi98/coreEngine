<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class maquinasListadoAlarmas extends ControllerBase {

    /******************************************************************************/
    // Variables
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataDate;
    private $WidgetsCommon;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'maquinasListado';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->DataDate       = new FunctionsDataDate();
		$this->WidgetsCommon  = new UIWidgetsCommon();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //Crear nuevo
    public function New($f3, $params){
        /******************************************/
        // Se genera la query
        $query = [
            'data'    => 'idMaquina',
            'table'   => 'maquinas_listado',
            'join'    => '',
            'where'   => 'idMaquina = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status']){
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'       => $this->FormInputs,
                'Fnc_Codification'     => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Documentos-formNew.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$rowData]);
            //Muestra los errores
            $this->showError(2, $f3, $result);
        }
    }
    /******************************************************************************/
    //List
    public function UpdateList($f3, $params){
        /*******************************************************************/
        // Se genera la query
        $query = [
            'data'    => 'idDocumentos,Nombre,FVencimiento',
            'table'   => 'maquinas_listado_documentos',
            'join'    => '',
            'where'   => 'idMaquina = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams       = ['query' => $query];
        $arrDocumentos = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($arrDocumentos['status']){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'     => $this->Codification,
                'Fnc_DataDate'         => $this->DataDate,
                /*=========== Datos Consultados ===========*/
                'arrDocumentos' => $arrDocumentos['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Documentos-UpdateList.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$arrDocumentos]);
            //Muestra los errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    //View
    public function View($f3, $params){
        /******************************************/
        // Se genera la query
        $query = [
            'data'    => 'Nombre,NombreArchivo,Observacion,FVencimiento',
            'table'   => 'maquinas_listado_documentos',
            'join'    => '',
            'where'   => 'idDocumentos = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status']){
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_WidgetsCommon' => $this->WidgetsCommon,
                'Fnc_DataDate'      => $this->DataDate,
                /*=========== Datos Consultados ===========*/
                'rowData'       => $rowData['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Documentos-View.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$rowData]);
            //Muestra los errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    //Edit
    public function GetID($f3, $params){
        /******************************************/
        // Se genera la query
        $query = [
            'data'    => 'idDocumentos,idMaquina,Nombre,Observacion,FVencimiento',
            'table'   => 'maquinas_listado_documentos',
            'join'    => '',
            'where'   => 'idDocumentos = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status']){
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'    => $this->FormInputs,
                'Fnc_Codification'  => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'rowData'       => $rowData['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Documentos-formEdit.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$rowData]);
            //Muestra los errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    /*                                  DATOS                                     */
    /******************************************************************************/
    /******************************************************************************/
    //Crear
    public function Insert(){

        /******************************/
        //Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /******************************/
        // Se genera la query
        $query = [
            'data'      => 'idMaquina,Nombre,Observacion,FVencimiento',
            'required'  => 'idMaquina,Nombre',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'maquinas_listado_documentos',
            'Post'      => $_POST,
            'files'     => [
                [
                    'Identificador' => 'NombreArchivo',
                    'SubCarpeta'    => '',
                    'NombreArchivo' => '',
                    'SufijoArchivo' => 'MaquinasAlarmaDoc_',
                    'ValidarTipo'   => 'word,excel,powerpoint,pdf,image,txt,zip,video,music',
                    'ValidarPeso'   => 10,
                    'Base64'        => false
                ],
            ]
        ];
        // Ejecuto la query
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        $Response = $this->Base_insert($xParams);

        /******************************/
        // Se asume que $Response contendrá un array de errores/datos, un ID numérico o algún otro valor.
        if ($Response['status']){
            // Si es un ID numérico, se envía con código 200 (OK)
            Response::success($Response['data']);
        } else {
            // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
            // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
            Response::error('Error al operar con la Base de Datos', 500, $Response['error']);
        }
    }

    /******************************************************************************/
    //Editar por put (solo modificar datos)
    //Editar por post (modificar y subir archivos)
    public function Update(){
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            /******************************/
            //Se genera el chequeo
            $DataCheck = $this->dataCheck($_POST);

            /******************************/
            // Se genera la query
            $query = [
                'data'      => 'idDocumentos,idMaquina,Nombre,Observacion,FVencimiento',
                'required'  => 'idMaquina,Nombre',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'maquinas_listado_documentos',
                'where'     => 'idDocumentos',
                'Post'      => $_POST
            ];
            // Ejecuto la query
            $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
            $Response = $this->Base_update($xParams);

            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response['status']){
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
    //Borrar dato y archivos
    public function Delete(){
        //Verificacion metodo PUT
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            //Se parsean los datos
            parse_str(file_get_contents("php://input"),$dataDelete);
            /******************************/
            // Se genera la query
            $query = [
                'files'       => 'NombreArchivo',
                'table'       => 'maquinas_listado_documentos',
                'where'       => 'idDocumentos',
                'SubCarpeta'  => '',
                'Post'        => $dataDelete
            ];
            // Ejecuto la query
            $xParams  = ['query' => $query];
            $Response = $this->Base_delete($xParams);

            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response['status']){
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
    private function dataCheck($POST){
        // Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => '',
            'ValidarNumero'             => 'idMaquina',
            'ValidarEntero'             => 'idMaquina',
            'ValidarRut'                => '',
            'ValidarPatente'            => '',
            'ValidarFecha'              => 'FVencimiento',
            'ValidarHora'               => '',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => 'Nombre,Observacion',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'Nombre',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Nombre,Observacion',
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
