<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class gestionCampanasCostos extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $ServerServer;
    private $DataNumbers;
    private $DataDate;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName    = 'gestionCampanas';
		$this->FormInputs        = new UIFormInputs();
		$this->Codification      = new FunctionsSecurityCodification();
		$this->ServerServer      = new FunctionsServerServer();
		$this->DataNumbers       = new FunctionsDataNumbers();
		$this->DataDate          = new FunctionsDataDate();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //Crear nuevo
    public function New($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => 'idCampana',
            'table'   => 'campanas_listado',
            'join'    => '',
            'where'   => 'idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if ($rowData!==false) {
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_Codification'    => $this->Codification,
                'Fnc_ServerServer'    => $this->ServerServer,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Costos-formNew.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //Crear nuevo
    public function NewDoc($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => 'idCampana,idBodegas',
            'table'   => 'campanas_listado',
            'join'    => '',
            'where'   => 'idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idEntidad AS ID,CONCAT((CASE WHEN ( Nombre = "" OR Nombre IS NULL ) THEN RazonSocial ELSE CONCAT(Nombre,IFNULL( CONCAT( " ", ApellidoPat ), "" )) END ),CASE WHEN ( Nick = "" OR Nick IS NULL ) THEN "" ELSE CONCAT( " (", Nick, ")" ) END ) AS Nombre ',
            'table'   => 'entidades_listado',
            'join'    => '',
            'where'   => 'idEstado=1 AND idTipo=1',
            'group'   => '',
            'having'  => '',
            'order'   => 'ApellidoPat ASC,Nombre ASC,RazonSocial ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams      = ['query' => $query];
        $arrEntidades = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idDocumentos AS ID,Nombre',
            'table'   => 'core_documentos_mercantiles',
            'join'    => '',
            'where'   => 'idDocumentos!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams       = ['query' => $query];
        $arrDocumentos = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idProducto AS ID,Nombre',
            'table'   => 'productos_listado',
            'join'    => '',
            'where'   => 'idEstado=1',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams      = ['query' => $query];
        $arrProductos = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if ($rowData!==false) {
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_Codification'    => $this->Codification,
                'Fnc_ServerServer'    => $this->ServerServer,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData,
                'arrEntidades'    => $arrEntidades,
                'arrDocumentos'   => $arrDocumentos,
                'arrProductos'    => $arrProductos,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Costos-formNewDoc.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //List
    public function UpdateList($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => 'idEstado',
            'table'   => 'campanas_listado',
            'join'    => '',
            'where'   => 'idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => '
                campanas_listado_costos.idExistencia,
                campanas_listado_costos.Fecha,
                campanas_listado_costos.Item,
                campanas_listado_costos.Costos,
                campanas_listado_costos.idFacturacion,
                core_documentos_mercantiles.Nombre AS DocumentoNombre,
                facturacion_listado.N_Doc AS DocumentoN_Doc',
            'table'   => 'campanas_listado_costos',
            'join'    => '
                LEFT JOIN facturacion_listado           ON facturacion_listado.idFacturacion          = campanas_listado_costos.idFacturacion
                LEFT JOIN core_documentos_mercantiles   ON core_documentos_mercantiles.idDocumentos   = facturacion_listado.idDocumentos',
            'where'   => 'campanas_listado_costos.idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'campanas_listado_costos.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrCostos = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($arrCostos)){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'    => $this->Codification,
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'rowData'      => $rowData,
                'arrCostos'    => $arrCostos,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Costos-UpdateList.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //Edit
    public function GetID($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => 'idExistencia,idCampana,Item,Costos',
            'table'   => 'campanas_listado_costos',
            'join'    => '',
            'where'   => 'idExistencia = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if ($rowData!==false) {
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'    => $this->FormInputs,
                'Fnc_Codification'  => $this->Codification,
                'Fnc_DataNumbers'   => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'rowData'       => $rowData,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Costos-formEdit.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
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
        //Se genera la query
        $query = [
            'data'      => 'idCampana,Fecha,Item,Costos,idUsuario',
            'required'  => 'idCampana,Fecha,Item,Costos,idUsuario',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'campanas_listado_costos',
            'Post'      => $_POST
        ];
        //Ejecuto la query
        //Ejecuto la query
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        $Response = $this->Base_insert($xParams);

        /******************************/
        // Se asume que $Response contendrá un array de errores/datos, un ID numérico o algún otro valor.
        if (is_numeric($Response)) {
            //Se actualizan los datos del costo
            $gestionCampanas = new gestionCampanas();
            $gestionCampanas->updateCostos(1, $_POST['idCampana']);
            // Si es un ID numérico, se envía con código 200 (OK)
            echo Response::sendData(200, $Response);
        } else {
            // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
            // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
            echo Response::sendData(500, $Response);
        }

    }

    /******************************************************************************/
    //Crear con Doc
    public function InsertDoc($f3){

        /*******************************************************************/
        //variables
        $ndata_1 = isset($_POST['Producto_idProducto']) ? count($_POST['Producto_idProducto']) : 0;

        //generacion de errores
        if($ndata_1==0) {
            echo Response::sendData(500, 'No hay productos ingresados');
        }else{
            /******************************/
            //Se genera el chequeo
            $DataCheck = $this->dataCheck($_POST);

            /*******************************************************************/
            //Se llaman los datos
            $UserData = $f3->get('SESSION.DataInfo');

            /******************************************/
            //Variable
            $PostMovProd = array();
            //Se generan los datos
            $PostMovProd['idDocumentos']         = $_POST['idDocumentos'];
            $PostMovProd['N_Doc']                = $_POST['N_Doc'];
            $PostMovProd['idEntidad']            = $_POST['idEntidad'];
            $PostMovProd['Creacion_fecha']       = $_POST['Creacion_fecha'];
            $PostMovProd['Observaciones']        = $_POST['Observaciones'];
            $PostMovProd['idUsuario']            = $_POST['idUsuario'];
            $PostMovProd['idTipo']               = $_POST['idTipo'];
            $PostMovProd['fecha_auto']           = $_POST['fecha_auto'];
            $PostMovProd['Creacion_hora']        = $_POST['Creacion_hora'];
            $PostMovProd['idEstadoPago']         = $_POST['idEstadoPago'];
            $PostMovProd['idBodegasIngreso']     = $_POST['idBodegasIngreso'];
            //productos
            $PostMovProd['Producto_idProducto']  = $_POST['Producto_idProducto'];
            $PostMovProd['Producto_Number']      = $_POST['Producto_Number'];
            $PostMovProd['Producto_ValorTotal']  = $_POST['Producto_ValorTotal'];

            /*******************************************************/
            //Se instancian otros controladores
		    $gestionDocumentos = new gestionDocumentos();
            $FacturacionID  = $gestionDocumentos->createDoc($PostMovProd, $UserData);

            /******************************/
            // Se asume que $FacturacionID contendrá un array de errores/datos, un ID numérico o algún otro valor.
            if (is_numeric($FacturacionID)) {
                /******************************************/
                //Variables
                $x_ValorTotal     = 0;
                //recorro los items
                for($j1 = 0; $j1 < $ndata_1; $j1++){
                    //Variables
                    $x_ValorTotal = (isset($_POST['Producto_ValorTotal'][$j1])) ? $x_ValorTotal + $_POST['Producto_ValorTotal'][$j1] : $x_ValorTotal;
                }

                /*******************************************************/
                //Se agrega respuesta
                $arrTareas = [
                    'idCampana'     => $_POST['idCampana'],
                    'Fecha'         => $_POST['fecha_auto'],
                    'Item'          => 'Ingreso Documento Compra',
                    'Costos'        => $x_ValorTotal,
                    'idFacturacion' => $FacturacionID,
                    'idUsuario'     => $_POST['idUsuario'],
                ];
                /******************************/
                //Se genera la query
                $query = [
                    'data'      => 'idCampana,Fecha,Item,Costos,idFacturacion,idUsuario',
                    'required'  => 'idCampana,Fecha,Item,Costos,idUsuario',
                    'unique'    => '',
                    'encode'    => '',
                    'table'     => 'campanas_listado_costos',
                    'Post'      => $arrTareas
                ];
                //Ejecuto la query
                $xParams = ['DataCheck' => $DataCheck, 'query' => $query];
                $this->Base_insert($xParams);

                /******************************************/
                //Se actualizan los datos del costo
                $gestionCampanas   = new gestionCampanas();
                $gestionCampanas->updateCostos(1, $_POST['idCampana']);

                /******************************************/
                // Si es un ID numérico, se envía con código 200 (OK)
                echo Response::sendData(200, $FacturacionID);
            } else {
                // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
                // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
                echo Response::sendData(500, $FacturacionID);
            }
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
            //Se genera la query
            $query = [
                'data'      => 'idExistencia,idCampana,Fecha,Item,Costos,idUsuario',
                'required'  => 'idCampana,Fecha,Item,Costos,idUsuario',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'campanas_listado_costos',
                'where'     => 'idExistencia',
                'Post'      => $_POST
            ];
            //Ejecuto la query
            $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
            $Response = $this->Base_update($xParams);

            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response===true) {
                /******************************************/
                //Se actualizan los datos del costo
                $gestionCampanas = new gestionCampanas();
                $gestionCampanas->updateCostos(1, $_POST['idCampana']);
                /******************************************/
                // Devuelvo $Response con código 200 (OK)
                echo Response::sendData(200, $Response);
            } else {
                // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
                // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
                echo Response::sendData(500, $Response);
            }

        }else {
            echo Response::sendData(500, "Error en el Request Method");
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
            //Se genera la query
            $query = [
                'data'    => 'idCampana',
                'table'   => 'campanas_listado_costos',
                'join'    => '',
                'where'   => 'idExistencia = "'.$this->Codification->encryptDecrypt('decrypt', $dataDelete['idExistencia']).'"',
                'group'   => '',
                'having'  => '',
                'order'   => ''
            ];
            //Ejecuto la query
            $xParams   = ['query' => $query];
            $rowCostos = $this->Base_GetByID($xParams);

            /******************************/
            //Se genera la query
            $query = [
                'files'       => '',
                'table'       => 'campanas_listado_costos',
                'where'       => 'idExistencia',
                'SubCarpeta'  => '',
                'Post'        => $dataDelete
            ];
            //Ejecuto la query
            $xParams  = ['query' => $query];
            $Response = $this->Base_delete($xParams);

            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response===true) {
                /******************************************/
                //Se actualizan los datos del costo
                $gestionCampanas = new gestionCampanas();
                $gestionCampanas->updateCostos(1, $rowCostos['idCampana']);
                /******************************************/
                // Devuelvo $Response con código 200 (OK)
                echo Response::sendData(200, $Response);
            } else {
                // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
                // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
                echo Response::sendData(500, $Response);
            }

        }else {
            echo Response::sendData(500, "Error en el Request Method");
        }
    }

    /******************************************************************************/
    //Se validan los datos
    private function dataCheck($POST){
        //Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => '',
            'ValidarNumero'             => '',
            'ValidarEntero'             => '',
            'ValidarRut'                => '',
            'ValidarPatente'            => '',
            'ValidarFecha'              => 'Fecha',
            'ValidarHora'               => '',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => 'Item',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => '',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Item',
            'ValidarEspaciosVacios'     => '',
            'ValidarMayusculas'         => '',
            'ValidarCoincidencias'      => '',
            'Post'                      => $POST,
        ];
        //Devuelvo
        return $DataChecking;
    }

}
