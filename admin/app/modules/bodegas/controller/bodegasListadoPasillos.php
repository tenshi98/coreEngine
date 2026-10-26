<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Distribucion fisica de bodegas - Pasillos.
 *
 * Nivel 2 de la jerarquia bodega -> zona -> pasillo -> rack -> nivel -> posicion.
 * Comparte el esquema de permisos con el listado de bodegas (`$this->controllerName = 'bodegasListado'`)
 * y publica sus propias vistas (`$this->viewName`), igual que hace `bodegasListadoObservaciones`.
 *
 * El borrado es en cascada (posiciones, niveles y racks del pasillo) y anula antes
 * las referencias de `bodegas_movimientos_productos.idPosicion`.
 */
class bodegasListadoPasillos extends ControllerBase {

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
        $this->controllerName = 'bodegasListado';          // raiz de permisos compartida
        $this->viewName       = 'bodegasListadoPasillos';  // prefijo de las vistas del controlador
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
        // Se obtiene el ID (zona propietaria)
        $ZonaID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($ZonaID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query (zona propietaria)
        $query = [
            'data'    => 'bodegas_zonas.idZona,bodegas_zonas.idBodegas,bodegas_zonas.Nombre AS Zona,bodegas_listado.Nombre AS Bodega',
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
        if ($rowData['status'] === true && $arrEstado['status'] === true) {
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
                'rowData'   => $rowData['data'],
                'arrEstado' => $arrEstado['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->viewName.'-formNew.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrEstado]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Actualizar Listar
    /*******************************************************************/
    public function UpdateList($f3, $params){

        /************************************/
        // Se obtiene el ID (zona propietaria)
        $ZonaID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($ZonaID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                bodegas_pasillos.idPasillo,
                bodegas_pasillos.idZona,
                bodegas_pasillos.Nombre,
                bodegas_pasillos.Orientacion,
                bodegas_pasillos.Orden,
                core_estados.Nombre  AS Estado,
                core_estados.Color   AS EstadoColor,
                (SELECT COUNT(idRack) FROM bodegas_racks WHERE bodegas_racks.idPasillo = bodegas_pasillos.idPasillo) AS Racks',
            'table'   => 'bodegas_pasillos',
            'join'    => 'LEFT JOIN core_estados ON core_estados.idEstado = bodegas_pasillos.idEstado',
            'where'   => 'bodegas_pasillos.idZona = ?',
            'params'  => [$ZonaID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'bodegas_pasillos.Orden ASC, bodegas_pasillos.idPasillo ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams    = ['query' => $query];
        // Ejecuto la query
        $arrPasillos = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrPasillos['status'] === true) {

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification' => $this->Codification,
                'Fnc_DataDate'     => $this->DataDate,
                /*=========== Datos Consultados ===========*/
                'arrPasillos' => $arrPasillos['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->viewName.'-UpdateList.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrPasillos]);
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
        $PasilloID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($PasilloID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                bodegas_pasillos.idPasillo,
                bodegas_pasillos.idBodegas,
                bodegas_pasillos.idZona,
                bodegas_pasillos.Nombre,
                bodegas_pasillos.Orientacion,
                bodegas_pasillos.Orden,
                core_estados.Nombre       AS Estado,
                core_estados.Color        AS EstadoColor,
                bodegas_zonas.Nombre      AS Zona,
                bodegas_listado.Nombre    AS Bodega,
                (SELECT COUNT(idRack)     FROM bodegas_racks WHERE bodegas_racks.idPasillo = bodegas_pasillos.idPasillo) AS Racks,
                (SELECT COUNT(idNivel)    FROM bodegas_niveles WHERE bodegas_niveles.idRack IN (SELECT bodegas_racks.idRack FROM bodegas_racks WHERE bodegas_racks.idPasillo = bodegas_pasillos.idPasillo)) AS Niveles,
                (SELECT COUNT(idPosicion) FROM bodegas_posiciones WHERE bodegas_posiciones.idNivel IN (SELECT bodegas_niveles.idNivel FROM bodegas_niveles WHERE bodegas_niveles.idRack IN (SELECT bodegas_racks.idRack FROM bodegas_racks WHERE bodegas_racks.idPasillo = bodegas_pasillos.idPasillo))) AS Posiciones',
            'table'   => 'bodegas_pasillos',
            'join'    => '
                LEFT JOIN core_estados   ON core_estados.idEstado      = bodegas_pasillos.idEstado
                LEFT JOIN bodegas_zonas  ON bodegas_zonas.idZona       = bodegas_pasillos.idZona
                LEFT JOIN bodegas_listado ON bodegas_listado.idBodegas = bodegas_pasillos.idBodegas',
            'where'   => 'bodegas_pasillos.idPasillo = ?',
            'params'  => [$PasilloID['data']],
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
        $PasilloID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($PasilloID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                bodegas_pasillos.idPasillo,
                bodegas_pasillos.idBodegas,
                bodegas_pasillos.idZona,
                bodegas_pasillos.Nombre,
                bodegas_pasillos.Orientacion,
                bodegas_pasillos.Orden,
                bodegas_pasillos.idEstado,
                bodegas_zonas.Nombre   AS Zona,
                bodegas_listado.Nombre AS Bodega',
            'table'   => 'bodegas_pasillos',
            'join'    => '
                LEFT JOIN bodegas_zonas   ON bodegas_zonas.idZona       = bodegas_pasillos.idZona
                LEFT JOIN bodegas_listado ON bodegas_listado.idBodegas   = bodegas_pasillos.idBodegas',
            'where'   => 'bodegas_pasillos.idPasillo = ?',
            'params'  => [$PasilloID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

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
        if ($rowData['status'] === true && $arrEstado['status'] === true) {
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
                'rowData'   => $rowData['data'],
                'arrEstado' => $arrEstado['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->viewName.'-formEdit.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrEstado]);
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
        // Se validan los valores permitidos
        if (!$this->isOrientacion($_POST['Orientacion'] ?? '')) {
            Response::error('Orientación inválida', 400);
        }

        /************************************/
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idBodegas,idZona,Nombre,Orientacion,Orden,idEstado',
            'required'  => 'idBodegas,idZona,Nombre,Orientacion',
            'unique'    => 'idZona-Nombre',
            'encode'    => '',
            'table'     => 'bodegas_pasillos',
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
        // Se validan los valores permitidos
        if (!$this->isOrientacion($_POST['Orientacion'] ?? '')) {
            Response::error('Orientación inválida', 400);
        }

        /************************************/
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idPasillo,idBodegas,idZona,Nombre,Orientacion,Orden,idEstado',
            'required'  => 'idPasillo,idBodegas,idZona,Nombre,Orientacion',
            'unique'    => 'idZona-Nombre',
            'encode'    => '',
            'table'     => 'bodegas_pasillos',
            'where'     => 'idPasillo',
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
        // Devuelvo $Response con código 200 (OK)
        /************************************/
        // Confirmar transacción
        $this->Base_transactionCommit();
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
        $PasilloID = $this->Codification->encryptDecrypt('decrypt', $dataDelete['idPasillo'] ?? '');
        if (!$this->isValidDecrypted($PasilloID, 'id')) {
            Response::error('Registro inválido', 400);
        }
        $idPasillo = (int)$PasilloID['data'];

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
                                   WHERE bodegas_racks.idPasillo = ?)))',
            [$idPasillo]
        );
        if ($Response['status'] === false) {
            $this->failOperation($Response);
        }

        /************************************/
        // Se borra la estructura del pasillo (de lo mas specifico a lo general)
        $arrDel = [
            'DELETE FROM bodegas_posiciones WHERE idNivel IN (
                 SELECT bodegas_niveles.idNivel FROM bodegas_niveles
                  WHERE bodegas_niveles.idRack IN (
                        SELECT bodegas_racks.idRack FROM bodegas_racks
                         WHERE bodegas_racks.idPasillo = ?))',
            'DELETE FROM bodegas_niveles WHERE idRack IN (
                 SELECT bodegas_racks.idRack FROM bodegas_racks
                  WHERE bodegas_racks.idPasillo = ?)',
            'DELETE FROM bodegas_racks WHERE idPasillo = ?',
        ];
        // Recorro los datos
        foreach ($arrDel as $sentencia) {
            /************************************/
            // Se genera la query
            $Response = $this->queryExecuteRaw($sentencia, [$idPasillo]);
            /************************************/
            // Si falla la la ejecucion, se revierte de inmediato
            if ($Response['status'] === false) {
                $this->failOperation($Response);
            }
        }

        /************************************/
        // Se borra el pasillo en si
        $query = [
            'files'       => '',
            'table'       => 'bodegas_pasillos',
            'where'       => 'idPasillo',
            'SubCarpeta'  => '',
            'Post'        => $dataDelete
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
    // Orientaciones admitidas
    /*******************************************************************/
    private function isOrientacion($value): bool {
        return in_array($value, ['H','V'], true);
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
            'ValidarNumero'              => 'idBodegas,idZona,idEstado,Orden',
            'ValidarEntero'              => 'idBodegas,idZona,idEstado,Orden',
            'ValidarRut'                 => '',
            'ValidarPatente'             => '',
            'ValidarFecha'               => '',
            'ValidarHora'                => '',
            'ValidarURL'                 => '',
            'ValidarLargoMinimo'         => 'Nombre',
            'ValidarLargoMinimoN'        => 3,
            'ValidarLargoMaximoPorCampo' => [
                'Orientacion' => 1,
                'Nombre'      => 100,
            ],
            'ValidarPalabrasCensuradas' => 'Nombre',
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
