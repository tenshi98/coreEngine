<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class cotizacionListadoItems extends ControllerBase {

    /******************************************************************************/
    // Variables
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataNumbers;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'cotizacionListado';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->DataNumbers    = new FunctionsDataNumbers();
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
            'data'    => 'idCotizacion',
            'table'   => 'cotizacion_listado',
            'join'    => '',
            'where'   => 'idCotizacion = ?',
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
                'rowData'         => $rowData['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Items-formNew.php');
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
            'data'    => 'idExistencia,Item,Number,ValorTotal',
            'table'   => 'cotizacion_listado_items',
            'join'    => '',
            'where'   => 'idCotizacion = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => 'idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams  = ['query' => $query];
        $arrItems = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($arrItems['status']){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'    => $this->Codification,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'arrItems'    => $arrItems['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Items-UpdateList.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$arrItems]);
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
            'data'    => 'idExistencia,idCotizacion,Item,Number,ValorTotal',
            'table'   => 'cotizacion_listado_items',
            'join'    => '',
            'where'   => 'idExistencia = ?',
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
                'Fnc_DataNumbers'   => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'rowData'       => $rowData['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Items-formEdit.php');
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
            'data'      => 'idCotizacion,Item,Number,ValorTotal',
            'required'  => 'idCotizacion,Item,ValorTotal',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'cotizacion_listado_items',
            'Post'      => $_POST
        ];
        // Ejecuto la query
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        $Response = $this->Base_insert($xParams);

        /******************************/
        // Se asume que $Response contendrá un array de errores/datos, un ID numérico o algún otro valor.
        if ($Response['status']){
            /******************************************/
            //Se actualizan los datos de la factura
            $cotizacionListado = new cotizacionListado();
            $cotizacionListado->updateFact(1, $_POST['idCotizacion']);
            /******************************************/
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
                'data'      => 'idExistencia,idCotizacion,Item,Number,ValorTotal',
                'required'  => 'idCotizacion,Item,ValorTotal',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'cotizacion_listado_items',
                'where'     => 'idExistencia',
                'Post'      => $_POST
            ];
            // Ejecuto la query
            $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
            $Response = $this->Base_update($xParams);

            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response['status']){
                /******************************************/
                //Se actualizan los datos de la factura
                $cotizacionListado = new cotizacionListado();
                $cotizacionListado->updateFact(1, $_POST['idCotizacion']);
                /******************************************/
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

            /******************************************/
            //Se parsean los datos
            parse_str(file_get_contents("php://input"),$dataDelete);

            /******************************************/
            // Se genera la query
            $query = [
                'data'    => 'idCotizacion',
                'table'   => 'cotizacion_listado_items',
                'join'    => '',
                'where'   => 'idExistencia = ?',
                'params'  => [$this->Codification->encryptDecrypt('decrypt', $dataDelete['idExistencia'])],
                'group'   => '',
                'having'  => '',
                'order'   => ''
            ];
            // Ejecuto la query
            $xParams        = ['query' => $query];
            $rowFacturacion = $this->Base_GetByID($xParams);

            /******************************/
            // Se genera la query
            $query = [
                'files'       => '',
                'table'       => 'cotizacion_listado_items',
                'where'       => 'idExistencia',
                'SubCarpeta'  => '',
                'Post'        => $dataDelete
            ];
            // Ejecuto la query
            $xParams  = ['query' => $query];
            $Response = $this->Base_delete($xParams);

            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($rowFacturacion['status'] && $Response['status']){
                /******************************************/
                //Se actualizan los datos de la factura
                $cotizacionListado = new cotizacionListado();
                $cotizacionListado->updateFact(1, $rowFacturacion['data']['idCotizacion']);
                /******************************************/
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
            'ValidarNumero'             => 'idCotizacion,Number,ValorTotal',
            'ValidarEntero'             => 'idCotizacion',
            'ValidarRut'                => '',
            'ValidarPatente'            => '',
            'ValidarFecha'              => '',
            'ValidarHora'               => '',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => 'Item',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'Item',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Item',
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
