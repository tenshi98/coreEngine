<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class bodegasMovimientoProductos extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataNumbers;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'bodegasMovimiento';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->DataNumbers    = new FunctionsDataNumbers();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  RUTAS                                     */
    /******************************************************************************/
    /******************************************************************************/
    //Listar Todo
    public function New_1($f3, $params){$this->New($f3, $params, 1);}
    public function New_2($f3, $params){$this->New($f3, $params, 2);}
    public function New_3($f3, $params){$this->New($f3, $params, 3);}
    //Listar Todo
    public function UpdateList_1($f3, $params){$this->UpdateList($f3, $params, 1);}
    public function UpdateList_2($f3, $params){$this->UpdateList($f3, $params, 2);}
    public function UpdateList_3($f3, $params){$this->UpdateList($f3, $params, 3);}
    //Listar Todo
    public function GetID_1($f3, $params){$this->GetID($f3, $params, 1);}
    public function GetID_2($f3, $params){$this->GetID($f3, $params, 2);}
    public function GetID_3($f3, $params){$this->GetID($f3, $params, 3);}

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //Crear nuevo
    public function New($f3, $params, $idTipoIngreso){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se verifica movimiento
        switch ($idTipoIngreso) {
            case 1: $tsrxName = 'bodegasMovimientoIngreso';  break;//Ingreso
            case 2: $tsrxName = 'bodegasMovimientoEgreso';   break;//Egreso
            case 3: $tsrxName = 'bodegasMovimientoTraspaso'; break;//Traspaso
        }

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => 'idMovimiento,idBodegasIngreso,idBodegasEgreso',
            'table'   => 'bodegas_movimientos',
            'join'    => '',
            'where'   => 'idMovimiento = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
            'data'    => 'idBodegas AS ID,Nombre',
            'table'   => 'bodegas_listado',
            'join'    => '',
            'where'   => 'idEstado=1',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams    = ['query' => $query];
        $arrBodegas = $this->Base_GetList($xParams);

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
        //Se genera la query
        $query = [
            'data'    => 'idEstadoIngreso AS ID,Nombre',
            'table'   => 'core_estados_ingreso',
            'join'    => '',
            'where'   => 'idEstadoIngreso!=3',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams    = ['query' => $query];
        $arrTipoMov = $this->Base_GetList($xParams);

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
                'UserAccess'    => $arrLevel[$tsrxName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'       => $this->FormInputs,
                'Fnc_Codification'     => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData,
                'arrBodegas'      => $arrBodegas,
                'arrProductos'    => $arrProductos,
                'arrTipoMov'      => $arrTipoMov,
                'idTipoIngreso'   => $idTipoIngreso,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Productos-formNew.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }
    /******************************************************************************/
    //List
    public function UpdateList($f3, $params, $idTipoIngreso){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se verifica movimiento
        switch ($idTipoIngreso) {
            case 1: $tsrxName = 'bodegasMovimientoIngreso';  break;//Ingreso
            case 2: $tsrxName = 'bodegasMovimientoEgreso';   break;//Egreso
            case 3: $tsrxName = 'bodegasMovimientoTraspaso'; break;//Traspaso
        }

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => '
                bodegas_movimientos_productos.idExistencia,
                core_estados_ingreso.Nombre AS TipoMovimiento,
                bodegas_listado.Nombre AS Bodega,
                productos_listado.Nombre AS ProductoNombre,
                bodegas_movimientos_productos.Number AS ProductoCantidad,
                core_unidades_medida.Nombre AS UnidadMedida',
            'table'   => 'bodegas_movimientos_productos',
            'join'    => '
                LEFT JOIN core_estados_ingreso  ON core_estados_ingreso.idEstadoIngreso  = bodegas_movimientos_productos.idEstadoIngreso
                LEFT JOIN bodegas_listado       ON bodegas_listado.idBodegas             = bodegas_movimientos_productos.idBodegas
                LEFT JOIN productos_listado     ON productos_listado.idProducto          = bodegas_movimientos_productos.idProducto
                LEFT JOIN core_unidades_medida  ON core_unidades_medida.idUniMed         = productos_listado.idUniMed',
            'where'   => 'bodegas_movimientos_productos.idMovimiento = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'bodegas_movimientos_productos.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams      = ['query' => $query];
        $arrProductos = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($arrProductos)){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$tsrxName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'    => $this->Codification,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'arrProductos'    => $arrProductos,
                'idTipoIngreso'   => $idTipoIngreso,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Productos-UpdateList.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //Edit
    public function GetID($f3, $params, $idTipoIngreso){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se verifica movimiento
        switch ($idTipoIngreso) {
            case 1: $tsrxName = 'bodegasMovimientoIngreso';  break;//Ingreso
            case 2: $tsrxName = 'bodegasMovimientoEgreso';   break;//Egreso
            case 3: $tsrxName = 'bodegasMovimientoTraspaso'; break;//Traspaso
        }

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => '
                bodegas_movimientos_productos.idExistencia,
                bodegas_movimientos_productos.idMovimiento,
                bodegas_movimientos_productos.idEstadoIngreso,
                bodegas_movimientos_productos.idBodegas,
                bodegas_movimientos_productos.idProducto,
                bodegas_movimientos_productos.Number,
                core_estados_ingreso.Nombre AS TipoMovimiento,
                bodegas_listado.Nombre AS Bodega,
                productos_listado.Nombre AS ProductoNombre',
            'table'   => 'bodegas_movimientos_productos',
            'join'    => '
                LEFT JOIN core_estados_ingreso  ON core_estados_ingreso.idEstadoIngreso  = bodegas_movimientos_productos.idEstadoIngreso
                LEFT JOIN bodegas_listado       ON bodegas_listado.idBodegas             = bodegas_movimientos_productos.idBodegas
                LEFT JOIN productos_listado     ON productos_listado.idProducto          = bodegas_movimientos_productos.idProducto',
            'where'   => 'bodegas_movimientos_productos.idExistencia = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
                'UserAccess'    => $arrLevel[$tsrxName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'    => $this->FormInputs,
                'Fnc_Codification'  => $this->Codification,
                'Fnc_DataNumbers'   => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'rowData'       => $rowData,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Productos-formEdit.php');
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
            'data'      => 'idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
            'required'  => 'idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'bodegas_movimientos_productos',
            'Post'      => $_POST
        ];
        //Ejecuto la query
        //Ejecuto la query
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        $Response = $this->Base_insert($xParams);

        /******************************/
        // Se asume que $Response contendrá un array de errores/datos, un ID numérico o algún otro valor.
        if (is_numeric($Response)) {

            /******************************/
            //Se consultan los stocks
            $query = [
                'data'    => 'idStocks,idProducto,Cantidad_idBodegas_'.$_POST['idBodegas'].' AS Cantidad',
                'table'   => 'bodegas_productos_stocks',
                'join'    => '',
                'where'   => 'idProducto='.$_POST['idProducto'],
                'group'   => '',
                'having'  => '',
                'order'   => 'idProducto ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            //Ejecuto la query
            $xParams   = ['query' => $query];
            $arrStocks = $this->Base_GetList($xParams);

            /******************************/
            //Recorro
            foreach($arrStocks AS $crud){
                $arrProdStock[$crud['idProducto']]['idStocks'] = $crud['idStocks'];
                $arrProdStock[$crud['idProducto']]['Cantidad'] = $crud['Cantidad'];
            }

            /******************************/
            //Se verifica movimiento
            switch ($_POST['idEstadoIngreso']) {
                /**************************************************************************************/
                /**************************************************************************************/
                //Ingreso
                case 1:
                    /******************************/
                    //Se Actualizan los stocks
                    //verifico si existe el dato en el stock
                    if(isset($arrProdStock[$_POST['idProducto']]['idStocks'])&&$arrProdStock[$_POST['idProducto']]['idStocks']!=''){
                        /******************************/
                        //Se agrega respuesta
                        $arrTareas = [
                            'idStocks'                                => $arrProdStock[$_POST['idProducto']]['idStocks'],
                            'Cantidad_idBodegas_'.$_POST['idBodegas'] => ($arrProdStock[$_POST['idProducto']]['Cantidad'] + $_POST['Number']),
                        ];
                        /******************************/
                        //Se genera la query
                        $query = [
                            'data'      => 'idStocks,Cantidad_idBodegas_'.$_POST['idBodegas'],
                            'required'  => 'idStocks,Cantidad_idBodegas_'.$_POST['idBodegas'],
                            'unique'    => '',
                            'encode'    => '',
                            'table'     => 'bodegas_productos_stocks',
                            'where'     => 'idStocks',
                            'Post'      => $arrTareas
                        ];
                        //Ejecuto la query
                        $xParams       = ['DataCheck' => $DataCheck, 'query' => $query];
                        $ResponseTarea = $this->Base_update($xParams);

                    }else{
                        /******************************/
                        //Se agrega respuesta
                        $arrTareas = [
                            'idProducto'                              => $_POST['idProducto'],
                            'Cantidad_idBodegas_'.$_POST['idBodegas'] => $_POST['Number'],
                        ];
                        /******************************/
                        //Se genera la query
                        $query = [
                            'data'      => 'idProducto,Cantidad_idBodegas_'.$_POST['idBodegas'],
                            'required'  => 'idProducto,Cantidad_idBodegas_'.$_POST['idBodegas'],
                            'unique'    => '',
                            'encode'    => '',
                            'table'     => 'bodegas_productos_stocks',
                            'Post'      => $arrTareas
                        ];
                        //Ejecuto la query
                        $xParams       = ['DataCheck' => $DataCheck, 'query' => $query];
                        $ResponseTarea = $this->Base_insert($xParams);

                    }
                    break;
                /**************************************************************************************/
                /**************************************************************************************/
                //Egreso
                case 2:
                    /******************************/
                    //Se Actualizan los stocks
                    //verifico si existe el dato en el stock
                    if(isset($arrProdStock[$_POST['idProducto']]['idStocks'])&&$arrProdStock[$_POST['idProducto']]['idStocks']!=''){
                        /******************************/
                        //Se agrega respuesta
                        $arrTareas = [
                            'idStocks'                                => $arrProdStock[$_POST['idProducto']]['idStocks'],
                            'Cantidad_idBodegas_'.$_POST['idBodegas'] => ($arrProdStock[$_POST['idProducto']]['Cantidad'] - $_POST['Number']),
                        ];
                        /******************************/
                        //Se genera la query
                        $query = [
                            'data'      => 'idStocks,Cantidad_idBodegas_'.$_POST['idBodegas'],
                            'required'  => 'idStocks,Cantidad_idBodegas_'.$_POST['idBodegas'],
                            'unique'    => '',
                            'encode'    => '',
                            'table'     => 'bodegas_productos_stocks',
                            'where'     => 'idStocks',
                            'Post'      => $arrTareas
                        ];
                        //Ejecuto la query
                        $xParams       = ['DataCheck' => $DataCheck, 'query' => $query];
                        $ResponseTarea = $this->Base_update($xParams);
                    }else{
                        /******************************/
                        //Se agrega respuesta
                        $arrTareas = [
                            'idProducto'                              => $_POST['idProducto'],
                            'Cantidad_idBodegas_'.$_POST['idBodegas'] => (0 - $_POST['Number']),
                        ];
                        /******************************/
                        //Se genera la query
                        $query = [
                            'data'      => 'idProducto,Cantidad_idBodegas_'.$_POST['idBodegas'],
                            'required'  => 'idProducto,Cantidad_idBodegas_'.$_POST['idBodegas'],
                            'unique'    => '',
                            'encode'    => '',
                            'table'     => 'bodegas_productos_stocks',
                            'Post'      => $arrTareas
                        ];
                        //Ejecuto la query
                        $xParams       = ['DataCheck' => $DataCheck, 'query' => $query];
                        $ResponseTarea = $this->Base_insert($xParams);
                    }
                    break;
            }

            /******************************/
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
                'data'      => 'idExistencia,idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
                'required'  => 'idMovimiento,idEstadoIngreso,idBodegas,idProducto,Number',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'bodegas_movimientos_productos',
                'where'     => 'idExistencia',
                'Post'      => $_POST
            ];
            //Ejecuto la query
            $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
            $Response = $this->Base_update($xParams);

            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response===true) {

                /******************************/
                //Se consultan los stocks
                $query = [
                    'data'    => 'idStocks,idProducto,Cantidad_idBodegas_'.$_POST['idBodegas'].' AS Cantidad',
                    'table'   => 'bodegas_productos_stocks',
                    'join'    => '',
                    'where'   => 'idProducto='.$_POST['idProducto'],
                    'group'   => '',
                    'having'  => '',
                    'order'   => 'idProducto ASC',
                    'limit'   => ConfigAPP::APP["N_MaxItems"]
                ];
                //Ejecuto la query
                $xParams   = ['query' => $query];
                $arrStocks = $this->Base_GetList($xParams);

                /******************************/
                //Recorro
                foreach($arrStocks AS $crud){
                    $arrProdStock[$crud['idProducto']]['idStocks'] = $crud['idStocks'];
                    $arrProdStock[$crud['idProducto']]['Cantidad'] = $crud['Cantidad'];
                }

                /******************************/
                //Se verifica movimiento
                switch ($_POST['idEstadoIngreso']) {
                    /**************************************************************************************/
                    /**************************************************************************************/
                    //Ingreso
                    case 1:
                        /******************************/
                        //Se Actualizan los stocks
                        //verifico si existe el dato en el stock
                        if(isset($arrProdStock[$_POST['idProducto']]['idStocks'])&&$arrProdStock[$_POST['idProducto']]['idStocks']!=''){
                            /******************************/
                            //Se agrega respuesta
                            $arrTareas = [
                                'idStocks'                                => $arrProdStock[$_POST['idProducto']]['idStocks'],
                                'Cantidad_idBodegas_'.$_POST['idBodegas'] => ($arrProdStock[$_POST['idProducto']]['Cantidad'] + ($_POST['Number'] - $_POST['NumberOld'])),
                            ];
                            /******************************/
                            //Se genera la query
                            $query = [
                                'data'      => 'idStocks,Cantidad_idBodegas_'.$_POST['idBodegas'],
                                'required'  => 'idStocks,Cantidad_idBodegas_'.$_POST['idBodegas'],
                                'unique'    => '',
                                'encode'    => '',
                                'table'     => 'bodegas_productos_stocks',
                                'where'     => 'idStocks',
                                'Post'      => $arrTareas
                            ];
                            //Ejecuto la query
                            $xParams       = ['DataCheck' => $DataCheck, 'query' => $query];
                            $ResponseTarea = $this->Base_update($xParams);

                        }else{
                            /******************************/
                            //Se agrega respuesta
                            $arrTareas = [
                                'idProducto'                              => $_POST['idProducto'],
                                'Cantidad_idBodegas_'.$_POST['idBodegas'] => ($_POST['Number'] - $_POST['NumberOld']),
                            ];
                            /******************************/
                            //Se genera la query
                            $query = [
                                'data'      => 'idProducto,Cantidad_idBodegas_'.$_POST['idBodegas'],
                                'required'  => 'idProducto,Cantidad_idBodegas_'.$_POST['idBodegas'],
                                'unique'    => '',
                                'encode'    => '',
                                'table'     => 'bodegas_productos_stocks',
                                'Post'      => $arrTareas
                            ];
                            //Ejecuto la query
                            $xParams       = ['DataCheck' => $DataCheck, 'query' => $query];
                            $ResponseTarea = $this->Base_insert($xParams);

                        }
                        break;
                    /**************************************************************************************/
                    /**************************************************************************************/
                    //Egreso
                    case 2:
                        /******************************/
                        //Se Actualizan los stocks
                        //verifico si existe el dato en el stock
                        if(isset($arrProdStock[$_POST['idProducto']]['idStocks'])&&$arrProdStock[$_POST['idProducto']]['idStocks']!=''){
                            /******************************/
                            //Se agrega respuesta
                            $arrTareas = [
                                'idStocks'                                => $arrProdStock[$_POST['idProducto']]['idStocks'],
                                'Cantidad_idBodegas_'.$_POST['idBodegas'] => ($arrProdStock[$_POST['idProducto']]['Cantidad'] - ($_POST['Number'] - $_POST['NumberOld'])),
                            ];
                            /******************************/
                            //Se genera la query
                            $query = [
                                'data'      => 'idStocks,Cantidad_idBodegas_'.$_POST['idBodegas'],
                                'required'  => 'idStocks,Cantidad_idBodegas_'.$_POST['idBodegas'],
                                'unique'    => '',
                                'encode'    => '',
                                'table'     => 'bodegas_productos_stocks',
                                'where'     => 'idStocks',
                                'Post'      => $arrTareas
                            ];
                            //Ejecuto la query
                            $xParams       = ['DataCheck' => $DataCheck, 'query' => $query];
                            $ResponseTarea = $this->Base_update($xParams);
                        }else{
                            /******************************/
                            //Se agrega respuesta
                            $arrTareas = [
                                'idProducto'                              => $_POST['idProducto'],
                                'Cantidad_idBodegas_'.$_POST['idBodegas'] => (0 - ($_POST['Number'] - $_POST['NumberOld'])),
                            ];
                            /******************************/
                            //Se genera la query
                            $query = [
                                'data'      => 'idProducto,Cantidad_idBodegas_'.$_POST['idBodegas'],
                                'required'  => 'idProducto,Cantidad_idBodegas_'.$_POST['idBodegas'],
                                'unique'    => '',
                                'encode'    => '',
                                'table'     => 'bodegas_productos_stocks',
                                'Post'      => $arrTareas
                            ];
                            //Ejecuto la query
                            $xParams       = ['DataCheck' => $DataCheck, 'query' => $query];
                            $ResponseTarea = $this->Base_insert($xParams);
                        }
                        break;
                }

                /******************************/
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
            //Se parsean los datos
            parse_str(file_get_contents("php://input"),$dataDelete);
            /******************************/
            //Se genera la query
            $query = [
                'files'       => '',
                'table'       => 'bodegas_movimientos_productos',
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
            'Post'                      => $POST,
        ];
        //Devuelvo
        return $DataChecking;
    }

}
