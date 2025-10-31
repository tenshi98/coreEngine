<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class gestionCampanasPerdidas extends ControllerBase {

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
                'arrProductos'    => $arrProductos,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Perdidas-formNew.php');
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
                campanas_listado_perdidas.idExistencia,
                campanas_listado_perdidas.Fecha,
                campanas_listado_perdidas.Item,
                campanas_listado_perdidas.Cantidad,
                campanas_listado_perdidas.Perdidas,
                campanas_listado_perdidas.idMovimiento,
                productos_listado.Nombre AS Producto,
                core_unidades_medida.Nombre AS Unimed',
            'table'   => 'campanas_listado_perdidas',
            'join'    => '
                LEFT JOIN productos_listado      ON productos_listado.idProducto    = campanas_listado_perdidas.idProducto
                LEFT JOIN core_unidades_medida   ON core_unidades_medida.idUniMed   = productos_listado.idUniMed',
            'where'   => 'campanas_listado_perdidas.idCampana = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'campanas_listado_perdidas.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams     = ['query' => $query];
        $arrPerdidas = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($arrPerdidas)){

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
                'arrPerdidas'  => $arrPerdidas,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Perdidas-UpdateList.php');
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
            'data'    => '
                campanas_listado_perdidas.idExistencia,
                campanas_listado_perdidas.idCampana,
                campanas_listado_perdidas.Item,
                campanas_listado_perdidas.idProducto,
                campanas_listado_perdidas.Cantidad,
                campanas_listado_perdidas.Perdidas,
                campanas_listado_perdidas.idMovimiento,
                productos_listado.Nombre AS Producto',
            'table'   => 'campanas_listado_perdidas',
            'join'    => 'LEFT JOIN productos_listado      ON productos_listado.idProducto    = campanas_listado_perdidas.idProducto',
            'where'   => 'campanas_listado_perdidas.idExistencia = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Perdidas-formEdit.php');
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
    //Crear con Doc
    public function Insert($f3){

        /******************************/
        //Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /******************************************/
        //si hay cantidades Se genera el movimiento
        if(isset($_POST['Cantidad'])&&$_POST['Cantidad']!=''){
            //Variable
            $PostMovProd = array();
            //Se generan los datos
            $PostMovProd['idEstadoIngreso']  = $_POST['idTipo'];
            $PostMovProd['idBodegasIngreso'] = (isset($_POST['idBodegasIngreso']) && $_POST['idBodegasIngreso'] !== '' ? $_POST['idBodegasIngreso'] : '');
            $PostMovProd['idBodegasEgreso']  = (isset($_POST['idBodegasEgreso']) && $_POST['idBodegasEgreso'] !== '' ? $_POST['idBodegasEgreso'] : '');
            $PostMovProd['Creacion_fecha']   = $_POST['Creacion_fecha'];
            $PostMovProd['Creacion_hora']    = $_POST['Creacion_hora'];
            $PostMovProd['Observaciones']    = 'Movimiento declarado como perdida';
            $PostMovProd['fecha_auto']       = $_POST['fecha_auto'];
            $PostMovProd['idUsuario']        = $_POST['idUsuario'];
            //productos
            $PostMovProd['idProducto'][0]    = $_POST['idProducto'];
            $PostMovProd['Number'][0]        = $_POST['Cantidad'];

            /*******************************************************/
            //Se instancian otros controladores
		    $bodegasMovimiento = new bodegasMovimiento();
            $Response = $bodegasMovimiento->createMov($PostMovProd);

        }else{
            $Response = 0;
        }

        /******************************/
        // Se asume que $Response contendrá un array de errores/datos, un ID numérico o algún otro valor.
        if (is_numeric($Response)) {
            /******************************************/
            //Verifico si existe
            if(isset($_POST['Creacion_fecha'])&&$_POST['Creacion_fecha']!=''){
                $_POST['Fecha']      = $_POST['Creacion_fecha'];
                $_POST['Fecha_Dia']  = $this->DataDate->fecha2NdiaMes($_POST['Fecha']);
                $_POST['Fecha_Mes']  = $this->DataDate->fecha2NMes($_POST['Fecha']);
                $_POST['Fecha_Ano']  = $this->DataDate->fecha2Ano($_POST['Fecha']);
            }

            /******************************************/
            //Se agrega respuesta
            $arrTareas = [
                'idCampana'    => $_POST['idCampana'],
                'Fecha'        => $_POST['Fecha'],
                'Fecha_Dia'    => $_POST['Fecha_Dia'],
                'Fecha_Mes'    => $_POST['Fecha_Mes'],
                'Fecha_Ano'    => $_POST['Fecha_Ano'],
                'Item'         => $_POST['Item'],
                'idProducto'   => $_POST['idProducto'],
                'Cantidad'     => $_POST['Cantidad'],
                'Perdidas'     => $_POST['Perdidas'],
                'idUsuario'    => $_POST['idUsuario'],
                'idMovimiento' => $Response,
            ];
            /******************************/
            //Se genera la query
            $query = [
                'data'      => 'idCampana,Fecha,Fecha_Dia,Fecha_Mes,Fecha_Ano,Item,idProducto,Cantidad,Perdidas,idUsuario,idMovimiento',
                'required'  => 'idCampana,Fecha,Fecha_Dia,Fecha_Mes,Fecha_Ano,Item,Perdidas,idUsuario',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'campanas_listado_perdidas',
                'Post'      => $arrTareas
            ];
            //Ejecuto la query
            $xParams         = ['DataCheck' => $DataCheck, 'query' => $query];
            $ResponsePerdida = $this->Base_insert($xParams);
            /******************************************/
            //Se actualizan los datos del costo
            $gestionCampanas = new gestionCampanas();
            $gestionCampanas->updateCostos(3, $_POST['idCampana']);
            /******************************************/
            // Si es un ID numérico, se envía con código 200 (OK)
            echo Response::sendData(200, $Response);
        } else {
            // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
            // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
            echo Response::sendData(500, $Response);
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
                'data'      => 'idExistencia,idCampana,Fecha,Fecha_Dia,Fecha_Mes,Fecha_Ano,Item,idProducto,Cantidad,Perdidas,idUsuario,idMovimiento',
                'required'  => 'idCampana,Fecha,Fecha_Dia,Fecha_Mes,Fecha_Ano,Item,Perdidas,idUsuario',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'campanas_listado_perdidas',
                'where'     => 'idExistencia',
                'Post'      => $_POST
            ];
            //Ejecuto la query
            $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
            $Response = $this->Base_update($xParams);

            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response===true) {
                /*******************************************************/
                /*                    ACTUALIZACIONES                  */
                /*******************************************************/
                if(isset($_POST['Cantidad'])&&$_POST['Cantidad']!=0){
                    /*******************************************************/
                    /*                       STOCKS                        */
                    /*******************************************************/
                    /******************************************/
                    //Se consultan los datos
                    $query = [
                        'data'    => 'idBodegasEgreso',
                        'table'   => 'bodegas_movimientos',
                        'join'    => '',
                        'where'   => 'idMovimiento = "'.$_POST['idMovimiento'].'"',
                        'group'   => '',
                        'having'  => '',
                        'order'   => ''
                    ];
                    //Ejecuto la query
                    $xParams       = ['query' => $query];
                    $rowMovimiento = $this->Base_GetByID($xParams);
                    /******************************/
                    //Se consultan los datos
                    $query = [
                        'data'    => 'idStocks,Cantidad_idBodegas_'.$rowMovimiento['idBodegasEgreso'].' AS Cantidad',
                        'table'   => 'bodegas_productos_stocks',
                        'join'    => '',
                        'where'   => 'idProducto = "'.$_POST['Old_idProducto'].'"',
                        'group'   => '',
                        'having'  => '',
                        'order'   => ''
                    ];
                    //Ejecuto la query
                    $xParams      = ['query' => $query];
                    $rowProdStock = $this->Base_GetByID($xParams);
                    /******************************/
                    //Variables
                    $NewCantidad = $rowProdStock['Cantidad'] + ($_POST['Old_Cantidad'] - $_POST['Cantidad']);
                    /******************************/
                    //Se Actualizan los stocks
                    //verifico si existe el dato en el stock
                    if(isset($rowProdStock['idStocks'])&&$rowProdStock['idStocks']!=''){
                        /******************************/
                        //Se agrega respuesta
                        $arrTareas = [
                            'idStocks'                                              => $rowProdStock['idStocks'],
                            'Cantidad_idBodegas_'.$rowMovimiento['idBodegasEgreso'] => $NewCantidad,
                        ];
                        /******************************/
                        //Se genera la query
                        $query = [
                            'data'      => 'idStocks,Cantidad_idBodegas_'.$rowMovimiento['idBodegasEgreso'],
                            'required'  => 'idStocks,Cantidad_idBodegas_'.$rowMovimiento['idBodegasEgreso'],
                            'unique'    => '',
                            'encode'    => '',
                            'table'     => 'bodegas_productos_stocks',
                            'where'     => 'idStocks',
                            'Post'      => $arrTareas
                        ];
                        //Ejecuto la query
                        $xParams       = ['DataCheck' => '', 'query' => $query];
                        $ResponseTarea = $this->Base_update($xParams);

                    }
                    /*******************************************************/
                    /*                     MOVIMIENTO                      */
                    /*******************************************************/
                    /***************************************/
                    //Se actualizan las cantidades en las facturas
                    $ActionSQL  = 'UPDATE `bodegas_movimientos_productos` ';
                    $ActionSQL .= 'SET Number = "'.$_POST['Cantidad'].'"';
                    $ActionSQL .= 'WHERE idMovimiento = "'.$_POST['idMovimiento'].'" AND idProducto = "'.$_POST['Old_idProducto'].'"';
                    //Se ejecuta la query
                    $xParams = ['query' => $ActionSQL];
                    $result  = $this->Base_queryExecute($xParams);

                }

                /******************************************/
                //Se actualizan los datos del costo
                $gestionCampanas = new gestionCampanas();
                $gestionCampanas->updateCostos(3, $_POST['idCampana']);

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
                'data'    => 'idCampana,idProducto,Cantidad,idMovimiento',
                'table'   => 'campanas_listado_perdidas',
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
                'table'       => 'campanas_listado_perdidas',
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
                /*******************************************************/
                /*                    ACTUALIZACIONES                  */
                /*******************************************************/
                if(isset($rowCostos['Cantidad'])&&$rowCostos['Cantidad']!=0){
                    /*******************************************************/
                    /*                       STOCKS                        */
                    /*******************************************************/
                    /******************************************/
                    //Se consultan los datos
                    $query = [
                        'data'    => 'idBodegasEgreso',
                        'table'   => 'bodegas_movimientos',
                        'join'    => '',
                        'where'   => 'idMovimiento = "'.$rowCostos['idMovimiento'].'"',
                        'group'   => '',
                        'having'  => '',
                        'order'   => ''
                    ];
                    //Ejecuto la query
                    $xParams       = ['query' => $query];
                    $rowMovimiento = $this->Base_GetByID($xParams);
                    /******************************/
                    //Se consultan los datos
                    $query = [
                        'data'    => 'idStocks,Cantidad_idBodegas_'.$rowMovimiento['idBodegasEgreso'].' AS Cantidad',
                        'table'   => 'bodegas_productos_stocks',
                        'join'    => '',
                        'where'   => 'idProducto = "'.$rowCostos['idProducto'].'"',
                        'group'   => '',
                        'having'  => '',
                        'order'   => ''
                    ];
                    //Ejecuto la query
                    $xParams      = ['query' => $query];
                    $rowProdStock = $this->Base_GetByID($xParams);
                    /******************************/
                    //Variables
                    $NewCantidad = $rowProdStock['Cantidad'] + $rowCostos['Cantidad'];
                    /******************************/
                    //Se Actualizan los stocks
                    //verifico si existe el dato en el stock
                    if(isset($rowProdStock['idStocks'])&&$rowProdStock['idStocks']!=''){
                        /******************************/
                        //Se agrega respuesta
                        $arrTareas = [
                            'idStocks'                                              => $rowProdStock['idStocks'],
                            'Cantidad_idBodegas_'.$rowMovimiento['idBodegasEgreso'] => $NewCantidad,
                        ];
                        /******************************/
                        //Se genera la query
                        $query = [
                            'data'      => 'idStocks,Cantidad_idBodegas_'.$rowMovimiento['idBodegasEgreso'],
                            'required'  => 'idStocks,Cantidad_idBodegas_'.$rowMovimiento['idBodegasEgreso'],
                            'unique'    => '',
                            'encode'    => '',
                            'table'     => 'bodegas_productos_stocks',
                            'where'     => 'idStocks',
                            'Post'      => $arrTareas
                        ];
                        //Ejecuto la query
                        $xParams       = ['DataCheck' => '', 'query' => $query];
                        $ResponseTarea = $this->Base_update($xParams);

                    }
                    /*******************************************************/
                    /*                     MOVIMIENTO                      */
                    /*******************************************************/
                    /******************************************/
                    //Se borran los datos
                    $ActionSQL = 'DELETE FROM `bodegas_movimientos` WHERE idMovimiento = "'.$rowCostos['idMovimiento'].'"';
                    //Se ejecuta la query
                    $xParams = ['query' => $ActionSQL];
                    $result  = $this->Base_queryExecute($xParams);
                    //Se borran los datos
                    $ActionSQL = 'DELETE FROM `bodegas_movimientos_productos` WHERE idMovimiento = "'.$rowCostos['idMovimiento'].'"';
                    //Se ejecuta la query
                    $xParams = ['query' => $ActionSQL];
                    $result  = $this->Base_queryExecute($xParams);
                }

                /******************************************/
                //Se actualizan los datos del costo
                $gestionCampanas = new gestionCampanas();
                $gestionCampanas->updateCostos(3, $rowCostos['idCampana']);

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
            'ValidarLargoMinimo'        => '',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => '',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => '',
            'ValidarEspaciosVacios'     => '',
            'ValidarMayusculas'         => '',
            'ValidarCoincidencias'      => '',
            'Post'                      => $POST,
        ];
        //Devuelvo
        return $DataChecking;
    }

}