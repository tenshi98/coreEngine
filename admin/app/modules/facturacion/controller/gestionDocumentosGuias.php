<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class gestionDocumentosGuias extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;
    private $FormInputs;
    private $Codification;
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
        $this->controllerName = 'gestionDocumentos';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->DataNumbers    = new FunctionsDataNumbers();
		$this->DataDate       = new FunctionsDataDate();
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
        //Se genera la query
        $query = [
            'data'    => 'idFacturacion,idEntidad',
            'table'   => 'facturacion_listado',
            'join'    => '',
            'where'   => 'idFacturacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
            'data'    => 'idFacturacion AS ID, CONCAT("Guia Despacho ", IFNULL(N_Doc, CONCAT("nRef ", idFacturacion)), IFNULL(CONCAT(" Fecha ", Creacion_fecha), "")) AS Nombre',
            'table'   => 'facturacion_listado',
            'join'    => '',
            'where'   => 'idDocumentos=3 AND idEstadoPago=1 AND idEntidad='.$rowData['idEntidad'],
            'group'   => '',
            'having'  => '',
            'order'   => 'idFacturacion ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams  = ['query' => $query];
        $arrGuias = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if($rowData['status'] && $arrGuias['status']){
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
                'arrGuias'        => $arrGuias['data'],
                'idTipo'          => $idTipo,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Guias-formNew.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrGuias]);
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
        //Se genera la query
        $query = [
            'data'    => 'idEstadoPago',
            'table'   => 'facturacion_listado',
            'join'    => '',
            'where'   => 'idFacturacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
                facturacion_listado_guias.idExistencia,
                facturacion_listado_guias.idFacturacionRel,
                core_documentos_mercantiles.Nombre AS Documento,
                facturacion_listado.N_Doc,
                facturacion_listado.Creacion_fecha,
                facturacion_listado.ValorTotal',
            'table'   => 'facturacion_listado_guias',
            'join'    => '
                LEFT JOIN facturacion_listado          ON facturacion_listado.idFacturacion         = facturacion_listado_guias.idFacturacionRel
                LEFT JOIN core_documentos_mercantiles  ON core_documentos_mercantiles.idDocumentos  = facturacion_listado.idDocumentos',
            'where'   => 'facturacion_listado_guias.idFacturacion = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => 'facturacion_listado_guias.idExistencia ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams  = ['query' => $query];
        $arrGuias = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if($rowData['status'] && $arrGuias['status']){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $tsrxName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'    => $this->Codification,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                'Fnc_DataDate'        => $this->DataDate,
                /*=========== Datos Consultados ===========*/
                'rowData'     => $rowData['data'],
                'arrGuias'    => $arrGuias['data'],
                'idTipo'      => $idTipo,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Guias-UpdateList.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrGuias]);
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
        //Se genera la query
        $query = [
            'data'      => 'idFacturacion,idFacturacionRel',
            'required'  => 'idFacturacion,idFacturacionRel',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'facturacion_listado_guias',
            'Post'      => $_POST
        ];
        //Se genera el chequeo
        $dataCheck_1 = $this->dataCheck_1($_POST);
        //Ejecuto la query
        $xParams  = ['DataCheck' => $dataCheck_1, 'query' => $query];
        $Response = $this->Base_insert($xParams);

        /******************************/
        // Se asume que $Response contendrá un array de errores/datos, un ID numérico o algún otro valor.
        if ($Response['status']){
            /******************************************/
            //Se actualizan los datos de la factura
            $this->updateFact($_POST, 2);
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
                'data'    => 'idFacturacion,idFacturacionRel',
                'table'   => 'facturacion_listado_guias',
                'join'    => '',
                'where'   => 'idExistencia = "'.$this->Codification->encryptDecrypt('decrypt', $dataDelete['idExistencia']).'"',
                'group'   => '',
                'having'  => '',
                'order'   => ''
            ];
            //Ejecuto la query
            $xParams        = ['query' => $query];
            $rowFacturacion = $this->Base_GetByID($xParams);

            /******************************/
            //Se genera la query
            $query = [
                'files'       => '',
                'table'       => 'facturacion_listado_guias',
                'where'       => 'idExistencia',
                'SubCarpeta'  => '',
                'Post'        => $dataDelete
            ];
            //Ejecuto la query
            $xParams  = ['query' => $query];
            $Response = $this->Base_delete($xParams);

            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($rowFacturacion['status'] && $Response['status']){
                /******************************************/
                //Se actualizan los datos de la factura
                $this->updateFact($rowFacturacion['data'], 1);
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
    private function dataCheck_1($POST){
        //Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => '',
            'ValidarNumero'             => 'idFacturacion,idFacturacionRel',
            'ValidarEntero'             => 'idFacturacion,idFacturacionRel',
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
        //Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => '',
            'ValidarNumero'             => 'idFacturacion,idEstadoPago,ValorNeto,IVA,ValorTotal,TotalItems,TotalProductos,TotalServicios,TotalGuias',
            'ValidarEntero'             => 'idFacturacion,idEstadoPago',
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
    //Se actualizan los montos
    private function updateFact($PostData, $EstadoPagoID){
        /******************************************/
        //Se genera la query
        $query = [
            'data'    => '
                facturacion_listado.idFacturacion,
                (SELECT SUM(ValorTotal) FROM facturacion_listado_items     WHERE idFacturacion='.$PostData['idFacturacion'].') AS TotalItem,
                (SELECT SUM(ValorTotal) FROM facturacion_listado_productos WHERE idFacturacion='.$PostData['idFacturacion'].') AS TotalProducto,
                (SELECT SUM(ValorTotal) FROM facturacion_listado_servicios WHERE idFacturacion='.$PostData['idFacturacion'].') AS TotalServicio,
                (SELECT SUM(facturacion_listado.ValorTotal) FROM facturacion_listado_guias LEFT JOIN facturacion_listado ON facturacion_listado.idFacturacion = facturacion_listado_guias.idFacturacionRel WHERE facturacion_listado_guias.idFacturacion='.$_POST['idFacturacion'].') AS TotalGuia',
            'table'   => 'facturacion_listado',
            'join'    => '',
            'where'   => 'idFacturacion = "'.$PostData['idFacturacion'].'"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /******************************/
        //Calculo
        $x_ValorTotal = $rowData['data']['TotalItem'] + $rowData['data']['TotalProducto'] + $rowData['data']['TotalServicio'] + $rowData['data']['TotalGuia'];
        //Se agrega respuesta
        $arrTareas = [
            'idFacturacion'   => $PostData['idFacturacion'],
            'ValorNeto'       => ($x_ValorTotal/1.19),
            'IVA'             => $x_ValorTotal - ($x_ValorTotal/1.19),
            'ValorTotal'      => $x_ValorTotal,
            'TotalItems'      => $rowData['data']['TotalItem'],
            'TotalProductos'  => $rowData['data']['TotalProducto'],
            'TotalServicios'  => $rowData['data']['TotalServicio'],
            'TotalGuias'      => $rowData['data']['TotalGuia'],
        ];
        /******************************/
        //Se genera la query
        $query = [
            'data'      => 'idFacturacion,ValorNeto,IVA,ValorTotal,TotalItems,TotalProductos,TotalServicios,TotalGuias',
            'required'  => 'idFacturacion,ValorNeto,IVA,ValorTotal,TotalItems,TotalProductos,TotalServicios,TotalGuias',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'facturacion_listado',
            'where'     => 'idFacturacion',
            'Post'      => $arrTareas
        ];
        //Se genera el chequeo
        $dataCheck_2 = $this->dataCheck_2($arrTareas);
        //Ejecuto la query
        $xParams = ['DataCheck' => $dataCheck_2, 'query' => $query];
        $this->Base_update($xParams);

        /******************************/
        //Se acambia el estado
        $arrTareas = [
            'idFacturacion' => $PostData['idFacturacionRel'],
            'idEstadoPago'  => $EstadoPagoID,
        ];
        /******************************/
        //Se genera la query
        $query = [
            'data'      => 'idFacturacion,idEstadoPago',
            'required'  => 'idFacturacion,idEstadoPago',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'facturacion_listado',
            'where'     => 'idFacturacion',
            'Post'      => $arrTareas
        ];
        //Se genera el chequeo
        $dataCheck_2 = $this->dataCheck_2($arrTareas);
        //Ejecuto la query
        $xParams = ['DataCheck' => $dataCheck_2, 'query' => $query];
        $this->Base_update($xParams);

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
