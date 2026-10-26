<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Distribucion fisica de bodegas - Zonas.
 *
 * Nivel 1 de la jerarquia bodega -> zona -> pasillo -> rack -> nivel -> posicion.
 * Los CRUD de zonas/pasillos/racks comparten el esquema de permisos del listado de
 * bodegas (`$this->controllerName = 'bodegasListado'`) y publican sus propias vistas
 * (`$this->viewName`), igual que hace `bodegasListadoObservaciones`.
 *
 * El borrado es en cascada (posiciones, niveles, racks y pasillos de la zona) y
 * anula antes las referencias de `bodegas_movimientos_productos.idPosicion`.
 */
class bodegasListadoZonas extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $viewName;
    private $FormInputs;
    private $Codification;
    private $DataDate;
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
        $this->controllerName = 'bodegasListado';       // raiz de permisos compartida
        $this->viewName       = 'bodegasListadoZonas';  // prefijo de las vistas del controlador
        $this->FormInputs     = new UIFormInputs();
        $this->Codification   = new FunctionsSecurityCodification();
        $this->DataDate       = new FunctionsDataDate();
        $this->WidgetsCommon  = new UIWidgetsCommon();
        $this->WidgetsViews  = new UIWidgetsViews();
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
        // Se obtiene el ID (bodega propietaria)
        $BodegasID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($BodegasID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idBodegas,Nombre,Codigo',
            'table'   => 'bodegas_listado',
            'join'    => '',
            'where'   => 'idBodegas = ?',
            'params'  => [$BodegasID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /************************************/
        // Se genera la query (tipos de zona)
        $query = [
            'data'    => 'idZonaTipo AS ID,Nombre',
            'table'   => 'bodegas_zonas_tipos',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Orden ASC, Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams    = ['query' => $query];
        // Ejecuto la query
        $arrZonaTipo = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query (estados)
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
        $xParams   = ['query' => $query];
        // Ejecuto la query
        $arrEstado = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($rowData['status'] === true && $arrZonaTipo['status'] === true && $arrEstado['status'] === true) {
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'   => $this->FormInputs,
                'Fnc_Codification' => $this->Codification,
                'Fnc_DataDate'     => $this->DataDate,
                'Fnc_ServerServer' => new FunctionsServerServer(),
                /*=========== Datos Consultados ===========*/
                'rowData'     => $rowData['data'],
                'arrZonaTipo' => $arrZonaTipo['data'],
                'arrEstado'   => $arrEstado['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->viewName.'-formNew.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrZonaTipo,$arrEstado]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // View
    /*******************************************************************/
    public function View($f3, $params){

        /************************************/
        // Se obtiene el ID
        $ZonaID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($ZonaID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                bodegas_zonas.idZona,
                bodegas_zonas.idBodegas,
                bodegas_zonas.Nombre,
                bodegas_zonas.Descripcion,
                bodegas_zonas.Orden,
                bodegas_zonas_tipos.Nombre AS Tipo,
                core_estados.Nombre        AS Estado,
                core_estados.Color         AS EstadoColor,
                bodegas_listado.Nombre     AS Bodega,
                bodegas_listado.Codigo     AS BodegaCodigo,
                (SELECT COUNT(idPasillo)  FROM bodegas_pasillos WHERE bodegas_pasillos.idZona = bodegas_zonas.idZona) AS Pasillos,
                (SELECT COUNT(idRack)     FROM bodegas_racks WHERE bodegas_racks.idPasillo IN (SELECT idPasillo FROM bodegas_pasillos WHERE bodegas_pasillos.idZona = bodegas_zonas.idZona)) AS Racks,
                (SELECT COUNT(idPosicion) FROM bodegas_posiciones WHERE bodegas_posiciones.idBodegas = bodegas_zonas.idBodegas AND bodegas_posiciones.idNivel IN (SELECT idNivel FROM bodegas_niveles WHERE bodegas_niveles.idRack IN (SELECT idRack FROM bodegas_racks WHERE bodegas_racks.idPasillo IN (SELECT idPasillo FROM bodegas_pasillos WHERE bodegas_pasillos.idZona = bodegas_zonas.idZona)))) AS Posiciones',
            'table'   => 'bodegas_zonas',
            'join'    => '
                LEFT JOIN bodegas_zonas_tipos ON bodegas_zonas_tipos.idZonaTipo  = bodegas_zonas.idZonaTipo
                LEFT JOIN core_estados        ON core_estados.idEstado           = bodegas_zonas.idEstado
                LEFT JOIN bodegas_listado     ON bodegas_listado.idBodegas       = bodegas_zonas.idBodegas',
            'where'   => 'bodegas_zonas.idZona = ?',
            'params'  => [$ZonaID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

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
                /*===========   Funcionalidad   ===========*/
                'Fnc_WidgetsCommon' => $this->WidgetsCommon,
                'Fnc_WidgetsViews'  => $this->WidgetsViews,
                'Fnc_DataDate'      => $this->DataDate,
                /*=========== Datos Consultados ===========*/
                'rowData' => $rowData['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->viewName.'-View.php');
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
    // Editar
    /*******************************************************************/
    public function GetID($f3, $params){

        /************************************/
        // Se obtiene el ID
        $ZonaID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($ZonaID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                bodegas_zonas.idZona,
                bodegas_zonas.idBodegas,
                bodegas_zonas.idZonaTipo,
                bodegas_zonas.Nombre,
                bodegas_zonas.Descripcion,
                bodegas_zonas.Orden,
                bodegas_zonas.idEstado,
                bodegas_listado.Nombre AS Bodega',
            'table'   => 'bodegas_zonas',
            'join'    => 'LEFT JOIN bodegas_listado ON bodegas_listado.idBodegas = bodegas_zonas.idBodegas',
            'where'   => 'bodegas_zonas.idZona = ?',
            'params'  => [$ZonaID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /************************************/
        // Se genera la query (tipos de zona)
        $query = [
            'data'    => 'idZonaTipo AS ID,Nombre',
            'table'   => 'bodegas_zonas_tipos',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Orden ASC, Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams     = ['query' => $query];
        // Ejecuto la query
        $arrZonaTipo = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query (estados)
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
        $xParams   = ['query' => $query];
        // Ejecuto la query
        $arrEstado = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($rowData['status'] === true && $arrZonaTipo['status'] === true && $arrEstado['status'] === true) {
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'   => $this->FormInputs,
                'Fnc_Codification' => $this->Codification,
                'Fnc_DataDate'     => $this->DataDate,
                /*=========== Datos Consultados ===========*/
                'rowData'     => $rowData['data'],
                'arrZonaTipo' => $arrZonaTipo['data'],
                'arrEstado'   => $arrEstado['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->viewName.'-formEdit.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrZonaTipo,$arrEstado]);
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
        // Se inicia la transacción
        $this->Base_transactionBegin();
        /************************************/
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idBodegas,idZonaTipo,Nombre,Descripcion,Orden,idEstado',
            'required'  => 'idBodegas,idZonaTipo,Nombre',
            'unique'    => 'idBodegas-Nombre',
            'encode'    => '',
            'table'     => 'bodegas_zonas',
            'Post'      => $_POST
        ];
        // Preparo los datos
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        // Ejecuto la query
        $Response = $this->Base_insert($xParams);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if ($Response['status'] === false) {
            $this->failOperation($Response);
        }

        /************************************/
        // Si es un ID numérico, se envía con código 200 (OK)
        /************************************/
        // Confirmar transacción
        $this->Base_transactionCommit();
        Response::success($Response['data']);

    }

    /*******************************************************************/
    // Editar por post (modificar datos)
    /*******************************************************************/
    public function Update($f3){

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();
        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idZona,idBodegas,idZonaTipo,Nombre,Descripcion,Orden,idEstado',
            'required'  => 'idZona,idBodegas,idZonaTipo,Nombre',
            'unique'    => 'idBodegas-Nombre',
            'encode'    => '',
            'table'     => 'bodegas_zonas',
            'where'     => 'idZona',
            'Post'      => $_POST
        ];
        // Preparo los datos
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        // Ejecuto la query
        $Response = $this->Base_update($xParams);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if ($Response['status'] === false) {
            $this->failOperation($Response);
        }
        /************************************/
        // Confirmar transacción
        $this->Base_transactionCommit();

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data']);

    }

    /*******************************************************************/
    // Borrar dato y sus descendientes
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
        // Se descifra y valida el identificador
        $ZonaID = $this->Codification->encryptDecrypt('decrypt', $dataDelete['idZona'] ?? '');
        if (!$this->isValidDecrypted($ZonaID, 'id')) {
            Response::error('Registro inválido', 400);
        }
        $idZona = (int)$ZonaID['data'];

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Se anulan las referencias de las posiciones en los movimientos
        $Response = $this->queryExecuteRaw(
            'UPDATE bodegas_movimientos_productos SET idPosicion = NULL
              WHERE idPosicion IN (
                    SELECT bodegas_posiciones.idPosicion FROM bodegas_posiciones
                     WHERE bodegas_posiciones.idNivel IN (
                           SELECT bodegas_niveles.idNivel FROM bodegas_niveles
                        WHERE bodegas_niveles.idRack IN (
                                  SELECT bodegas_racks.idRack FROM bodegas_racks
                                   WHERE bodegas_racks.idPasillo IN (
                                         SELECT bodegas_pasillos.idPasillo FROM bodegas_pasillos
                                          WHERE bodegas_pasillos.idZona = ?))))',
            [$idZona]
        );
        if ($Response['status'] === false) {
            $this->failOperation($Response);
        }

        /************************************/
        // Se borra la estructura de la zona (de lo mas specifico a lo general).
        // Cada sentencia filtra sobre la tabla padre, por lo que ninguna se
        // referencia a si misma dentro de su propia subconsulta.
        $arrDel = [
            'DELETE FROM bodegas_posiciones WHERE idNivel IN (
                 SELECT bodegas_niveles.idNivel FROM bodegas_niveles
                  WHERE bodegas_niveles.idRack IN (
                        SELECT bodegas_racks.idRack FROM bodegas_racks
                         WHERE bodegas_racks.idPasillo IN (
                               SELECT bodegas_pasillos.idPasillo FROM bodegas_pasillos
                                WHERE bodegas_pasillos.idZona = ?)))',
            'DELETE FROM bodegas_niveles WHERE idRack IN (
                 SELECT bodegas_racks.idRack FROM bodegas_racks
                  WHERE bodegas_racks.idPasillo IN (
                        SELECT bodegas_pasillos.idPasillo FROM bodegas_pasillos
                         WHERE bodegas_pasillos.idZona = ?))',
            'DELETE FROM bodegas_racks WHERE idPasillo IN (
                 SELECT bodegas_pasillos.idPasillo FROM bodegas_pasillos
                  WHERE bodegas_pasillos.idZona = ?)',
            'DELETE FROM bodegas_pasillos WHERE idZona = ?',
        ];
        // Recorro los datos
        foreach ($arrDel as $sentencia) {
            /************************************/
            // Se genera la query
            $Response = $this->queryExecuteRaw($sentencia, [$idZona]);
            /************************************/
            // Si falla la la ejecucion, se revierte de inmediato
            if ($Response['status'] === false) {
                $this->failOperation($Response);
            }
        }

        /************************************/
        // Se borra la zona en si
        $query = [
            'files'       => '',
            'table'       => 'bodegas_zonas',
            'where'       => 'idZona',
            'SubCarpeta'  => '',
            'Post'        => ['idZona' => $dataDelete['idZona'] ?? '']
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $Response = $this->Base_delete($xParams);

        /************************************/
        // Si falla la la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            $this->failOperation($Response);
        }

        /************************************/
        // Se confirma la transacción
        $this->Base_transactionCommit();

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data']);

    }

    /******************************************************************************/
    /*                             Métodos privados                               */
    /******************************************************************************/
    /*******************************************************************/
    // Ejecuta SQL crudo con parametros bindeados (cascada de borrado)
    /*******************************************************************/
    private function queryExecuteRaw(string $sql, array $params = []){

        /************************************
         * ControllerBase::$queryBuilder es privado y Base_queryExecute no admite
         * parametros bindeados, por lo que se instancia un QueryBuilder propio
         * sobre la misma conexion: asi participa de la transaccion activa.
         */
        $QueryBuilder = new QueryBuilder();
        /************************************/
        // Ejecuto la query
        return $QueryBuilder->queryExecute($sql, $this->getDBConn(), false, false, $params);
    }

    /*******************************************************************/
    // Se validan los datos
    /*******************************************************************/
    private function dataCheck($POST){
        // Variables
        $DataChecking = [
            'emptyData'                  => '',
            'encode'                     => '',
            'ValidarEmail'               => '',
            'ValidarNumero'              => 'idBodegas,idZonaTipo,idEstado,Orden',
            'ValidarEntero'              => 'idBodegas,idZonaTipo,idEstado,Orden',
            'ValidarRut'                 => '',
            'ValidarPatente'             => '',
            'ValidarFecha'               => '',
            'ValidarHora'                => '',
            'ValidarURL'                 => '',
            'ValidarLargoMinimo'         => 'Nombre',
            'ValidarLargoMinimoN'        => 3,
            'ValidarLargoMaximoPorCampo' => [
                'Descripcion' => 255,
                'Nombre'      => 100,
            ],
            'ValidarPalabrasCensuradas' => 'Nombre,Descripcion',
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

}
