<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class gestionDocumentosProductos extends ControllerBase {

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
        $this->controllerName = 'gestionDocumentos';
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
    //Listar Todo
    public function New_1($f3, $params){$this->New($f3, $params, 1);}
    public function New_2($f3, $params){$this->New($f3, $params, 2);}
    //Listar Todo
    public function UpdateList_1($f3, $params){$this->UpdateList($f3, $params, 1);}
    public function UpdateList_2($f3, $params){$this->UpdateList($f3, $params, 2);}
    //Listar Todo
    public function GetID_1($f3, $params){$this->GetID($f3, $params, 1);}
    public function GetID_2($f3, $params){$this->GetID($f3, $params, 2);}

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //Crear nuevo
    public function New($f3, $params, $idTipo){
        /******************************************/
        //Se verifica movimiento
        $tsrxName = $this->tsrxName($idTipo);

        /******************************************/
        // Se genera la query
        $query = [
            'data'    => 'idFacturacion',
            'table'   => 'facturacion_listado',
            'join'    => '',
            'where'   => 'idFacturacion = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        //Se instancia
        $arrUserData = $this->getUserData($f3);
        // Se verifica si se tiene el permiso para visualizar el dato
        if($arrUserData["usuariosPermisosBodegas"]==2 && $arrUserData['UserType'] != 1){
            $X_join  = 'INNER JOIN bodegas_listado_permisos_usuarios ON bodegas_listado_permisos_usuarios.idBodegas = bodegas_listado.idBodegas';
            $X_where  = 'bodegas_listado.idEstado = ? AND bodegas_listado_permisos_usuarios.idUsuario = ?';
            $X_params = [1, $arrUserData['UserID']];
        //Si se permite junto con la creacion de tareas
        }else{
            $X_join  = '';
            $X_where = 'bodegas_listado.idEstado = ?';
            $X_params = [1];
        }
        // Se genera la query
        $query = [
            'data'    => 'bodegas_listado.idBodegas AS ID, bodegas_listado.Nombre',
            'table'   => 'bodegas_listado',
            'join'    => $X_join,
            'where'   => $X_where,
            'params'  => $X_params,
            'group'   => '',
            'having'  => '',
            'order'   => 'bodegas_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams    = ['query' => $query];
        $arrBodegas = $this->Base_GetList($xParams);

        /*******************************************************************/
        // Se genera la query
        $query = [
            'data'    => 'idProducto AS ID,Nombre',
            'table'   => 'productos_listado',
            'join'    => '',
            'where'   => 'idEstado = ?',
            'params'  => [1],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams      = ['query' => $query];
        $arrProductos = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrBodegas['status'] && $arrProductos['status']){
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $tsrxName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'       => $this->FormInputs,
                'Fnc_Codification'     => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData['data'],
                'arrBodegas'      => $arrBodegas['data'],
                'arrProductos'    => $arrProductos['data'],
                'idTipo'          => $idTipo,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Productos-formNew.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrBodegas,$arrProductos]);
            //Muestra los errores
            $this->showError(2, $f3, $result);
        }
    }
    /******************************************************************************/
    //List
    public function UpdateList($f3, $params, $idTipo){
        /******************************************/
        //Se verifica movimiento
        $tsrxName = $this->tsrxName($idTipo);

        /******************************************/
        // Se genera la query
        $query = [
            'data'    => 'idEstadoPago',
            'table'   => 'facturacion_listado',
            'join'    => '',
            'where'   => 'idFacturacion = ?',
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
            'data'    => '
                facturacion_listado_productos.idExistencia,
                core_estados_ingreso.Nombre AS TipoMovimiento,
                bodegas_listado.Nombre AS Bodega,
                productos_listado.Nombre AS ProductoNombre,
                facturacion_listado_productos.Number AS ProductoCantidad,
                facturacion_listado_productos.ValorTotal AS ProductoValor,
                core_unidades_medida.Nombre AS UnidadMedida',
            'table'   => 'facturacion_listado_productos',
            'join'    => '
                LEFT JOIN core_estados_ingreso  ON core_estados_ingreso.idEstadoIngreso  = facturacion_listado_productos.idEstadoIngreso
                LEFT JOIN bodegas_listado       ON bodegas_listado.idBodegas             = facturacion_listado_productos.idBodegas
                LEFT JOIN productos_listado     ON productos_listado.idProducto          = facturacion_listado_productos.idProducto
                LEFT JOIN core_unidades_medida  ON core_unidades_medida.idUniMed         = productos_listado.idUniMed',
            'where'   => 'facturacion_listado_productos.idFacturacion = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => 'facturacion_listado_productos.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams      = ['query' => $query];
        $arrProductos = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrProductos['status']){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $tsrxName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'    => $this->Codification,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData['data'],
                'arrProductos'    => $arrProductos['data'],
                'idTipo'          => $idTipo,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Productos-UpdateList.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrProductos]);
            //Muestra los errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    //Edit
    public function GetID($f3, $params, $idTipo){
        /******************************************/
        //Se verifica movimiento
        $tsrxName = $this->tsrxName($idTipo);

        /******************************************/
        // Se genera la query
        $query = [
            'data'    => '
                facturacion_listado_productos.idExistencia,
                facturacion_listado_productos.idFacturacion,
                facturacion_listado_productos.Number,
                facturacion_listado_productos.ValorTotal,
                core_estados_ingreso.Nombre AS TipoMovimiento,
                bodegas_listado.Nombre AS Bodega,
                productos_listado.Nombre AS ProductoNombre',
            'table'   => 'facturacion_listado_productos',
            'join'    => '
                LEFT JOIN core_estados_ingreso  ON core_estados_ingreso.idEstadoIngreso  = facturacion_listado_productos.idEstadoIngreso
                LEFT JOIN bodegas_listado       ON bodegas_listado.idBodegas             = facturacion_listado_productos.idBodegas
                LEFT JOIN productos_listado     ON productos_listado.idProducto          = facturacion_listado_productos.idProducto',
            'where'   => 'facturacion_listado_productos.idExistencia = ?',
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
                'UserAccess'    => $this->getArrLevel($f3, $tsrxName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'    => $this->FormInputs,
                'Fnc_Codification'  => $this->Codification,
                'Fnc_DataNumbers'   => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'rowData'       => $rowData['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Productos-formEdit.php');
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
            'data'      => 'idFacturacion,idEstadoIngreso,idBodegas,idProducto,Number,ValorTotal',
            'required'  => 'idFacturacion,idEstadoIngreso,idBodegas,idProducto,ValorTotal',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'facturacion_listado_productos',
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
            $gestionDocumentos = new gestionDocumentos();
            $gestionDocumentos->updateFact(2, $_POST['idFacturacion']);
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
                'data'      => 'idExistencia,idFacturacion,idEstadoIngreso,idBodegas,idProducto,Number,ValorTotal',
                'required'  => 'idFacturacion,idEstadoIngreso,idBodegas,idProducto,ValorTotal',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'facturacion_listado_productos',
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
                $gestionDocumentos = new gestionDocumentos();
                $gestionDocumentos->updateFact(2, $_POST['idFacturacion']);
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
                'data'    => 'idFacturacion',
                'table'   => 'facturacion_listado_productos',
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
                'table'       => 'facturacion_listado_productos',
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
                $gestionDocumentos = new gestionDocumentos();
                $gestionDocumentos->updateFact(2, $rowFacturacion['data']['idFacturacion']);
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
            'ValidarNumero'             => 'idFacturacion,idEstadoIngreso,idBodegas,idProducto,Number,ValorTotal',
            'ValidarEntero'             => 'idFacturacion,idEstadoIngreso,idBodegas,idProducto',
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

    /******************************************************************************/
    //Se validan los datos
    private function tsrxName(int $idTipo): string{
        // Normalizar y mapear tipo a nombre de permiso (más eficiente que switch)
        $tsrxMap = [
            1 => 'gestionDocumentosCompras',
            2 => 'gestionDocumentosVentas'
        ];
        // Por defecto usar ventas si no viene un tipo válido
        return $tsrxMap[$idTipo] ?? $tsrxMap[2];
    }


}
