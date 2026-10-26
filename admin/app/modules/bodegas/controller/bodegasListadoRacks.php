<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Distribucion fisica de bodegas - Racks.
 *
 * Nivel 3 de la jerarquia bodega -> zona -> pasillo -> rack -> nivel -> posicion.
 * Comparte el esquema de permisos con el listado de bodegas (`$this->controllerName = 'bodegasListado'`)
 * y publica sus propias vistas (`$this->viewName`), igual que hace `bodegasListadoObservaciones`.
 *
 * Al crear un rack se generan en la misma transaccion sus niveles y las posiciones de
 * cada nivel (`Niveles` x `PosicionesPorNivel`). Esas dos dimensiones se informan una
 * sola vez: al editar el rack son de solo lectura y no regeneran posiciones.
 *
 * El borrado es en cascada (posiciones y niveles del rack) y anula antes las
 * referencias de `bodegas_movimientos_productos.idPosicion`.
 */
class bodegasListadoRacks extends ControllerBase {

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
    /*=========== Limites de dimension del rack ===========*/
    const NIVELES_MAX         = 50;
    const POSICIONES_MAX      = 50;

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
        $this->viewName       = 'bodegasListadoRacks';  // prefijo de las vistas del controlador
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
        // Se obtiene el ID (pasillo propietario)
        $PasilloID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($PasilloID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query (pasillo propietario)
        $query = [
            'data'    => '
                bodegas_pasillos.idPasillo,
                bodegas_pasillos.idBodegas,
                bodegas_pasillos.idZona,
                bodegas_pasillos.Nombre AS Pasillo,
                bodegas_zonas.Nombre AS Zona,
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

        /************************************/
        // Candidatos a rack gemelo (pasillo doble); en alta no hay racks propios
        $arrGemelo = $this->getRacksCandidatos((int)$PasilloID['data']);

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
                'arrGemelo' => $arrGemelo,
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
        // Se obtiene el ID (pasillo propietario)
        $PasilloID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($PasilloID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                bodegas_racks.idRack,
                bodegas_racks.idPasillo,
                bodegas_racks.Nombre,
                bodegas_racks.Niveles,
                bodegas_racks.PosicionesPorNivel,
                bodegas_racks.Orden,
                bodegas_racks.idRackEnfrentado,
                brg.Nombre           AS RackGemelo,
                core_estados.Nombre  AS Estado,
                core_estados.Color   AS EstadoColor,
                (SELECT COUNT(idPosicion) FROM bodegas_posiciones WHERE bodegas_posiciones.idNivel IN (SELECT bodegas_niveles.idNivel FROM bodegas_niveles WHERE bodegas_niveles.idRack = bodegas_racks.idRack)) AS Posiciones',
            'table'   => 'bodegas_racks',
            'join'    => '
                LEFT JOIN core_estados         ON core_estados.idEstado = bodegas_racks.idEstado
                LEFT JOIN bodegas_racks AS brg ON brg.idRack            = bodegas_racks.idRackEnfrentado',
            'where'   => 'bodegas_racks.idPasillo = ?',
            'params'  => [$PasilloID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'bodegas_racks.Orden ASC, bodegas_racks.idRack ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $arrRacks = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrRacks['status'] === true) {

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
                'arrRacks' => $arrRacks['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->viewName.'-UpdateList.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrRacks]);
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
        $RackID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($RackID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
                'data'    => '
                bodegas_racks.idRack,
                bodegas_racks.idBodegas,
                bodegas_racks.idPasillo,
                bodegas_racks.Nombre,
                bodegas_racks.Niveles,
                bodegas_racks.PosicionesPorNivel,
                bodegas_racks.Orden,
                bodegas_racks.idRackEnfrentado,
                brg.Nombre              AS RackGemelo,
                core_estados.Nombre     AS Estado,
                core_estados.Color      AS EstadoColor,
                bodegas_pasillos.Nombre AS Pasillo,
                bodegas_zonas.Nombre    AS Zona,
                bodegas_listado.Nombre  AS Bodega,
                (SELECT COUNT(idNivel)    FROM bodegas_niveles WHERE bodegas_niveles.idRack = bodegas_racks.idRack) AS NivelesReales,
                (SELECT COUNT(idPosicion) FROM bodegas_posiciones WHERE bodegas_posiciones.idNivel IN (SELECT bodegas_niveles.idNivel FROM bodegas_niveles WHERE bodegas_niveles.idRack = bodegas_racks.idRack)) AS Posiciones',
            'table'   => 'bodegas_racks',
            'join'    => '
                LEFT JOIN core_estados         ON core_estados.idEstado       = bodegas_racks.idEstado
                LEFT JOIN bodegas_pasillos     ON bodegas_pasillos.idPasillo  = bodegas_racks.idPasillo
                LEFT JOIN bodegas_zonas        ON bodegas_zonas.idZona        = bodegas_pasillos.idZona
                LEFT JOIN bodegas_listado      ON bodegas_listado.idBodegas   = bodegas_racks.idBodegas
                LEFT JOIN bodegas_racks AS brg ON brg.idRack                  = bodegas_racks.idRackEnfrentado',
            'where'   => 'bodegas_racks.idRack = ?',
            'params'  => [$RackID['data']],
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
        $RackID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($RackID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
                'data'    => '
                bodegas_racks.idRack,
                bodegas_racks.idBodegas,
                bodegas_racks.idPasillo,
                bodegas_racks.Nombre,
                bodegas_racks.Niveles,
                bodegas_racks.PosicionesPorNivel,
                bodegas_racks.Orden,
                bodegas_racks.idEstado,
                bodegas_racks.idRackEnfrentado,
                bodegas_pasillos.Nombre AS Pasillo,
                bodegas_zonas.Nombre    AS Zona,
                bodegas_listado.Nombre  AS Bodega',
            'table'   => 'bodegas_racks',
            'join'    => '
                LEFT JOIN bodegas_pasillos ON bodegas_pasillos.idPasillo  = bodegas_racks.idPasillo
                LEFT JOIN bodegas_zonas    ON bodegas_zonas.idZona        = bodegas_pasillos.idZona
                LEFT JOIN bodegas_listado  ON bodegas_listado.idBodegas   = bodegas_racks.idBodegas',
            'where'   => 'bodegas_racks.idRack = ?',
            'params'  => [$RackID['data']],
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

        /************************************/
        // Candidatos a rack gemelo (pasillo doble); se excluye al propio rack
        $idPasilloRack = (int)($rowData['data']['idPasillo'] ?? 0);
        $arrGemelo = ($idPasilloRack > 0) ? $this->getRacksCandidatos($idPasilloRack, (int)$RackID['data']) : [];

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
                'arrGemelo' => $arrGemelo,
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
    // Insertar (rack + niveles + posiciones)
    /*******************************************************************/
    public function Insert($f3){

        /************************************/
        // Se validan las dimensiones del rack
        $Niveles         = (int)($_POST['Niveles'] ?? 0);
        $PosicionesNivel = (int)($_POST['PosicionesPorNivel'] ?? 0);
        if ($Niveles < 1 || $Niveles > self::NIVELES_MAX || $PosicionesNivel < 1 || $PosicionesNivel > self::POSICIONES_MAX) {
            Response::error('Dimensiones de rack inválidas (1 a '.self::NIVELES_MAX.' niveles y posiciones)', 400);
        }

        /************************************/
        // La bodega y la zona se resuelven desde el pasillo, nunca desde el cliente
        $Pasillo = $this->getPasillo($_POST['idPasillo'] ?? '');
        if ($Pasillo === null) {
            Response::error('Pasillo inválido', 404);
        }
        $_POST['idPasillo']  = $Pasillo['idPasillo'];
        $_POST['idBodegas']  = $Pasillo['idBodegas'];

        /************************************/
        // Rack gemelo propuesto (pasillo doble); se valida antes de escribir nada
        $idGemelo = $this->validarGemelo($_POST['idRackEnfrentado'] ?? null, $Pasillo['idPasillo']);

        /************************************/
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Se crea el rack
        $query = [
            'data'      => 'idBodegas,idPasillo,Nombre,Niveles,PosicionesPorNivel,Orden,idEstado',
            'required'  => 'idBodegas,idPasillo,Nombre,Niveles,PosicionesPorNivel',
            'unique'    => 'idPasillo-Nombre',
            'encode'    => '',
            'table'     => 'bodegas_racks',
            'Post'      => $_POST
        ];
        // Preparo los datos
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        // Ejecuto la query
        $Response = $this->Base_insert($xParams);
        // Si falla la la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            $this->failOperation($Response);
        }
        $idRack = (int)$Response['data'];

        /************************************/
        // Se generan los niveles del rack
        $rowsNiveles = [];
        for ($Nivel = 1; $Nivel <= $Niveles; $Nivel++) {
            $rowsNiveles[] = [
                'idBodegas' => $Pasillo['idBodegas'],
                'idRack'    => $idRack,
                'Nivel'     => $Nivel,
            ];
        }
        $query = [
            'data'     => 'idBodegas,idRack,Nivel',
            'required' => 'idBodegas,idRack,Nivel',
            'table'    => 'bodegas_niveles',
            'rows'     => $rowsNiveles
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $Response = $this->Base_insertMultiple($xParams);
        // Si falla la la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            $this->failOperation($Response);
        }

        /************************************/
        // Se recuperan los niveles generados (para obtener sus ID)
        $query = [
            'data'    => 'idNivel,Nivel',
            'table'   => 'bodegas_niveles',
            'join'    => '',
            'where'   => 'idRack = ?',
            'params'  => [$idRack],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nivel ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams    = ['query' => $query];
        // Ejecuto la query
        $arrNiveles = $this->Base_GetList($xParams);
        // Si falla la la ejecucion o no hay niveles, se revierte de inmediato
        if ($arrNiveles['status'] === false || count($arrNiveles['data']) !== $Niveles) {
            $this->failOperation($arrNiveles);
        }

        /************************************/
        // Se generan las posiciones de cada nivel
        $rowsPosiciones = [];
        foreach ($arrNiveles['data'] as $NivelRow) {
            for ($Numero = 1; $Numero <= $PosicionesNivel; $Numero++) {
                $rowsPosiciones[] = [
                    'idBodegas'        => $Pasillo['idBodegas'],
                    'idNivel'          => $NivelRow['idNivel'],
                    'Codigo'           => $this->codigoPosicion($Pasillo['idZona'], $Pasillo['idPasillo'], $idRack, $NivelRow['Nivel'], $Numero),
                    'Numero'           => $Numero,
                    'idEstadoPosicion' => 1,
                ];
            }
        }
        $query = [
            'data'     => 'idBodegas,idNivel,Codigo,Numero,idEstadoPosicion',
            'required' => 'idBodegas,idNivel,Codigo,Numero,idEstadoPosicion',
            'table'    => 'bodegas_posiciones',
            'rows'     => $rowsPosiciones
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $Response = $this->Base_insertMultiple($xParams);
        // Si falla la la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            $this->failOperation($Response);
        }

        /************************************/
        // Se enlaza el rack gemelo (simetrico) dentro de la misma transaccion
        if ($idGemelo !== null) {
            $this->aplicarEnfrentado($idRack, $idGemelo);
        }

        /************************************/
        // Se confirma la transacción
        $this->Base_transactionCommit();

        /************************************/
        // Si es un ID numérico, se envía con código 200 (OK)
        Response::success($idRack);

    }

    /*******************************************************************/
    // Editar por post (modificar datos)
    /*******************************************************************/
    public function Update($f3){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // La bodega y la zona se resuelven desde el pasillo, nunca desde el cliente
        $Pasillo = $this->getPasillo($_POST['idPasillo'] ?? '');
        if ($Pasillo === null) {
            Response::error('Pasillo inválido', 404);
        }
        $_POST['idPasillo']  = $Pasillo['idPasillo'];
        $_POST['idBodegas']  = $Pasillo['idBodegas'];

        /************************************/
        // Rack gemelo propuesto (pasillo doble); no puede enfrentarse a si mismo
        $idRackEditado = (int)($_POST['idRack'] ?? 0);
        $idGemelo      = $this->validarGemelo($_POST['idRackEnfrentado'] ?? null, $Pasillo['idPasillo'], $idRackEditado);

        /************************************/
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /************************************
         * Niveles y PosicionesPorNivel quedan fuera del SET: describen la
         * estructura generada al crear el rack y editarlas no regenera nada.
         */
        $query = [
            'data'      => 'idRack,idBodegas,idPasillo,Nombre,Orden,idEstado',
            'required'  => 'idRack,idBodegas,idPasillo,Nombre',
            'unique'    => 'idPasillo-Nombre',
            'encode'    => '',
            'table'     => 'bodegas_racks',
            'where'     => 'idRack',
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
        // Se enlaza el rack gemelo (simetrico); en Update no hay transaccion activa
        $this->aplicarEnfrentado($idRackEditado, $idGemelo);

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
        $RackID = $this->Codification->encryptDecrypt('decrypt', $dataDelete['idRack'] ?? '');
        if (!$this->isValidDecrypted($RackID, 'id')) {
            Response::error('Registro inválido', 400);
        }
        $idRack = (int)$RackID['data'];

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Se desvincula el rack gemelo (ambos sentidos) antes de borrar
        $Response = $this->queryExecuteRaw(
            'UPDATE bodegas_racks SET idRackEnfrentado = NULL
              WHERE idRack = ? OR idRackEnfrentado = ?',
            [$idRack, $idRack]
        );
        if ($Response['status'] === false) {
            $this->failOperation($Response);
        }

        /************************************/
        // Se anulan las referencias de las posiciones en los movimientos
        $Response = $this->queryExecuteRaw(
            'UPDATE bodegas_movimientos_productos SET idPosicion = NULL
              WHERE idPosicion IN (
                    SELECT bodegas_posiciones.idPosicion FROM bodegas_posiciones
                     WHERE bodegas_posiciones.idNivel IN (
                           SELECT bodegas_niveles.idNivel FROM bodegas_niveles
                            WHERE bodegas_niveles.idRack = ?))',
            [$idRack]
        );
        if ($Response['status'] === false) {
            $this->failOperation($Response);
        }

        /************************************/
        // Se borra la estructura del rack (de lo mas specifico a lo general)
        $arrDel = [
            'DELETE FROM bodegas_posiciones WHERE idNivel IN (
                 SELECT bodegas_niveles.idNivel FROM bodegas_niveles
                  WHERE bodegas_niveles.idRack = ?)',
            'DELETE FROM bodegas_niveles WHERE idRack = ?',
        ];
        // Recorro los datos
        foreach ($arrDel as $sentencia) {
            /************************************/
            // Se genera la query
            $Response = $this->queryExecuteRaw($sentencia, [$idRack]);
            /************************************/
            // Si falla la la ejecucion, se revierte de inmediato
            if ($Response['status'] === false) {
                $this->failOperation($Response);
            }
        }

        /************************************/
        // Se borra el rack en si
        $query = [
            'files'       => '',
            'table'       => 'bodegas_racks',
            'where'       => 'idRack',
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
    // Devuelve los datos del pasillo padre o null si no existe
    /*******************************************************************/
    private function getPasillo($idPasillo): ?array {

        /************************************/
        // Solo se aceptan identificadores numericos
        if (!is_numeric($idPasillo) || (int)$idPasillo < 1) {
            return null;
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idPasillo,idBodegas,idZona',
            'table'   => 'bodegas_pasillos',
            'join'    => '',
            'where'   => 'idPasillo = ?',
            'params'  => [(int)$idPasillo],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $Row = $this->Base_GetByID($xParams);

        /************************************/
        // Retorno el padre cuando existe
        if ($Row['status'] === true && !empty($Row['data'])) {
            return [
                'idPasillo' => (int)$Row['data']['idPasillo'],
                'idBodegas' => (int)$Row['data']['idBodegas'],
                'idZona'    => (int)$Row['data']['idZona'],
            ];
        }
        return null;
    }

    /*******************************************************************/
    // Devuelve el id del rack gemelo (enfrentado) o null
    /*******************************************************************/
    private function getRackEnfrentado(int $idRack): ?int {
        $query = [
            'data'    => 'idRackEnfrentado',
            'table'   => 'bodegas_racks',
            'join'    => '',
            'where'   => 'idRack = ?',
            'params'  => [$idRack],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        $xParams = ['query' => $query];
        $Row = $this->Base_GetByID($xParams);
        if ($Row['status'] === true && !empty($Row['data'])) {
            $v = $Row['data']['idRackEnfrentado'] ?? null;
            return ($v === null || $v === '') ? null : (int)$v;
        }
        return null;
    }

    /*******************************************************************/
    // Escribe idRackEnfrentado en crudo (dentro de la transaccion activa)
    /*******************************************************************/
    private function setRackEnfrentadoRaw(int $idRack, ?int $valor) {
        $sql = 'UPDATE bodegas_racks SET idRackEnfrentado = ' . ($valor === null ? 'NULL' : (int)$valor) . ' WHERE idRack = ' . (int)$idRack;
        return $this->queryExecuteRaw($sql, []);
    }

    /*******************************************************************/
    // Enlaza simetricamente dos racks (gemelo de un pasillo doble)
    /*******************************************************************/
    private function aplicarEnfrentado(int $idRack, ?int $nuevo): bool {
        $actual = $this->getRackEnfrentado($idRack);
        if ($actual === $nuevo) {
            return true;
        }
        // Se desvincula el gemelo anterior
        if ($actual !== null) {
            $this->setRackEnfrentadoRaw($actual, null);
        }
        // Se prepara el nuevo gemelo (libera su vinculo previo si lo tenia)
        if ($nuevo !== null) {
            $previo = $this->getRackEnfrentado($nuevo);
            if ($previo !== null && $previo !== $idRack) {
                $this->setRackEnfrentadoRaw($previo, null);
            }
            $this->setRackEnfrentadoRaw($nuevo, $idRack);
        }
        $this->setRackEnfrentadoRaw($idRack, $nuevo);
        return true;
    }

    /*******************************************************************/
    // Valida el rack gemelo propuesto (mismo pasillo, distinto de si mismo)
    /*******************************************************************/
    private function validarGemelo($idGemelo, int $idPasillo, int $idRackExcluir = 0): ?int {
        // Sin gemelo (vacio o centinela 0)
        if ($idGemelo === null || $idGemelo === '' || $idGemelo === '0') {
            return null;
        }
        if (!is_numeric($idGemelo) || (int)$idGemelo === 0) {
            Response::error('Rack gemelo inválido', 400);
        }
        $id = (int)$idGemelo;
        if ($id === $idRackExcluir) {
            Response::error('Un rack no puede enfrentarse a sí mismo', 400);
        }
        $query = [
            'data'    => 'idRack,idPasillo',
            'table'   => 'bodegas_racks',
            'join'    => '',
            'where'   => 'idRack = ?',
            'params'  => [$id],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        $xParams = ['query' => $query];
        $Row = $this->Base_GetByID($xParams);
        if ($Row['status'] !== true || empty($Row['data'])) {
            Response::error('Rack gemelo inválido', 400);
        }
        if ((int)$Row['data']['idPasillo'] !== $idPasillo) {
            Response::error('El rack gemelo debe pertenecer al mismo pasillo', 400);
        }
        return $id;
    }

    /*******************************************************************/
    // Candidatos a gemelo: otros racks del mismo pasillo
    /*******************************************************************/
    private function getRacksCandidatos(int $idPasillo, int $idRackExcluir = 0): array {
        $where  = 'bodegas_racks.idPasillo = ?';
        $params = [$idPasillo];
        if ($idRackExcluir > 0) {
            $where .= ' AND bodegas_racks.idRack != ?';
            $params[] = $idRackExcluir;
        }
        $query = [
            'data'    => 'bodegas_racks.idRack AS ID,bodegas_racks.Nombre',
            'table'   => 'bodegas_racks',
            'join'    => '',
            'where'   => $where,
            'params'  => $params,
            'group'   => '',
            'having'  => '',
            'order'   => 'bodegas_racks.Orden ASC, bodegas_racks.idRack ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        $xParams = ['query' => $query];
        $R = $this->Base_GetList($xParams);
        return ($R['status'] === true && is_array($R['data'])) ? $R['data'] : [];
    }

    /*******************************************************************/
    // Codigo de una posicion: Z<zona>-P<pasillo>-R<rack>-N<nivel>-C<col>
    /*******************************************************************/
    private function codigoPosicion(int $idZona, int $idPasillo, int $idRack, $Nivel, int $Numero): string {
        return 'Z'.$idZona.'-P'.$idPasillo.'-R'.$idRack.'-N'.$Nivel.'-C'.$Numero;
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
            'ValidarNumero'              => 'idBodegas,idPasillo,idEstado,Orden,Niveles,PosicionesPorNivel',
            'ValidarEntero'              => 'idBodegas,idPasillo,idEstado,Orden,Niveles,PosicionesPorNivel,idRack',
            'ValidarRut'                 => '',
            'ValidarPatente'             => '',
            'ValidarFecha'               => '',
            'ValidarHora'                => '',
            'ValidarURL'                 => '',
            'ValidarLargoMinimo'         => 'Nombre',
            'ValidarLargoMinimoN'        => 3,
            'ValidarLargoMaximoPorCampo' => [
                'Nombre' => 100,
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
