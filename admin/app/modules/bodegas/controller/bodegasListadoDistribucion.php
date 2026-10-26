<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Distribucion fisica de bodegas - Lectura del arbol y del mapa.
 *
 * Expone lo que el Resumen de la bodega muestra en las pestanas "Distribucion" y "Mapa":
 *  - Estructura: esqueleto del arbol (zonas) con los contenedores de pasillos y racks.
 *  - Mapa:       rejilla de posiciones coloreadas por `bodegas_posiciones_estados`.
 *  - Posicion:   formulario para cambiar el estado de una posicion.
 *  - Posiciones: opciones <option> para los formularios que eligen una posicion.
 *
 * Comparte el esquema de permisos con el listado de bodegas (`$this->controllerName = 'bodegasListado'`)
 * y publica sus propias vistas (`$this->viewName`).
 */
class bodegasListadoDistribucion extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $viewName;
    private $FormInputs;
    private $Codification;
    private $DataDate;
    private $WidgetsCommon;

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'bodegasListado';             // raiz de permisos compartida
        $this->viewName       = 'bodegasListadoDistribucion'; // prefijo de las vistas del controlador
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
    /*******************************************************************/
    // Esqueleto del arbol: zonas con contenedores de pasillos y racks
    /*******************************************************************/
    public function Estructura($f3, $params){

        /************************************/
        // Se obtiene el ID (bodega propietaria)
        $BodegasID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($BodegasID, 'id')) {
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
                bodegas_zonas_tipos.Nombre   AS Tipo,
                bodegas_zonas_tipos.Color    AS TipoColor,
                core_estados.Nombre          AS Estado,
                core_estados.Color           AS EstadoColor,
                (SELECT COUNT(idPasillo)  FROM bodegas_pasillos   WHERE bodegas_pasillos.idZona = bodegas_zonas.idZona) AS Pasillos,
                (SELECT COUNT(idRack)     FROM bodegas_racks      WHERE bodegas_racks.idPasillo IN (SELECT bodegas_pasillos.idPasillo FROM bodegas_pasillos WHERE bodegas_pasillos.idZona = bodegas_zonas.idZona)) AS Racks,
                (SELECT COUNT(idPosicion) FROM bodegas_posiciones WHERE bodegas_posiciones.idNivel IN (SELECT bodegas_niveles.idNivel FROM bodegas_niveles WHERE bodegas_niveles.idRack IN (SELECT bodegas_racks.idRack FROM bodegas_racks WHERE bodegas_racks.idPasillo IN (SELECT bodegas_pasillos.idPasillo FROM bodegas_pasillos WHERE bodegas_pasillos.idZona = bodegas_zonas.idZona)))) AS Posiciones',
            'table'   => 'bodegas_zonas',
            'join'    => '
                LEFT JOIN bodegas_zonas_tipos ON bodegas_zonas_tipos.idZonaTipo  = bodegas_zonas.idZonaTipo
                LEFT JOIN core_estados        ON core_estados.idEstado           = bodegas_zonas.idEstado',
            'where'   => 'bodegas_zonas.idBodegas = ?',
            'params'  => [$BodegasID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'bodegas_zonas.Orden ASC, bodegas_zonas.idZona ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $arrZonas = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrZonas['status'] === true) {

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
                'arrZonas' => $arrZonas['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->viewName.'-Estructura.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrZonas]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Mapa de posiciones
    /*******************************************************************/
    public function Mapa($f3, $params){

        /************************************/
        // Se obtiene el ID (bodega propietaria)
        $BodegasID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($BodegasID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                bodegas_racks.idRack,
                bodegas_racks.Nombre    AS Rack,
                bodegas_racks.Orden     AS OrdenRack,
                bodegas_pasillos.Nombre AS Pasillo,
                bodegas_zonas.Nombre    AS Zona,
                bodegas_niveles.Nivel,
                bodegas_posiciones.idPosicion,
                bodegas_posiciones.Codigo,
                bodegas_posiciones.Numero,
                bodegas_posiciones_estados.Nombre AS Estado,
                bodegas_posiciones_estados.Color  AS EstadoColor',
            'table'   => 'bodegas_racks',
            'join'    => '
                LEFT JOIN bodegas_pasillos           ON bodegas_pasillos.idPasillo                  = bodegas_racks.idPasillo
                LEFT JOIN bodegas_zonas              ON bodegas_zonas.idZona                        = bodegas_pasillos.idZona
                INNER JOIN bodegas_niveles           ON bodegas_niveles.idRack                      = bodegas_racks.idRack
                INNER JOIN bodegas_posiciones        ON bodegas_posiciones.idNivel                  = bodegas_niveles.idNivel
                LEFT JOIN bodegas_posiciones_estados ON bodegas_posiciones_estados.idEstadoPosicion = bodegas_posiciones.idEstadoPosicion',
            'where'   => 'bodegas_racks.idBodegas = ?',
            'params'  => [$BodegasID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'bodegas_zonas.Orden ASC, bodegas_pasillos.Orden ASC, bodegas_racks.Orden ASC, bodegas_niveles.Nivel ASC, bodegas_posiciones.Numero ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $arrMapa  = $this->Base_GetList($xParams);

        /************************************/
        // Se agrupa por rack -> nivel
        $arrRacks = [];
        if ($arrMapa['status'] === true && is_array($arrMapa['data'])) {
            foreach ($arrMapa['data'] as $row) {
                /************************************/
                // Se obtiene (o se crea) el rack
                $idRack = $row['idRack'];
                if (!isset($arrRacks[$idRack])) {
                    $arrRacks[$idRack] = [
                        'idRack'  => $row['idRack'],
                        'Rack'    => $row['Rack'],
                        'Pasillo' => $row['Pasillo'],
                        'Zona'    => $row['Zona'],
                        'niveles' => [],
                    ];
                }
                /************************************/
                // Se obtiene (o se crea) el nivel
                $idNivel = $row['Nivel'];
                if (!isset($arrRacks[$idRack]['niveles'][$idNivel])) {
                    $arrRacks[$idRack]['niveles'][$idNivel] = [
                        'Nivel'      => $row['Nivel'],
                        'posiciones' => [],
                    ];
                }
                /************************************/
                // Se agrega la posicion
                $arrRacks[$idRack]['niveles'][$idNivel]['posiciones'][] = [
                    'idPosicion' => $row['idPosicion'],
                    'Codigo'     => $row['Codigo'],
                    'Numero'     => $row['Numero'],
                    'Estado'     => $row['Estado'],
                    'Color'      => $row['EstadoColor'],
                ];
            }
        }

        /************************************/
        // Se genera la query (estados de posicion)
        $query = [
            'data'    => 'idEstadoPosicion AS ID,Nombre,Color',
            'table'   => 'bodegas_posiciones_estados',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Orden ASC, Nombre ASC',
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
        if ($arrMapa['status'] === true && $arrEstado['status'] === true) {
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
                'arrRacks'  => array_values($arrRacks),
                'arrEstado' => $arrEstado['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->viewName.'-Mapa.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrMapa,$arrEstado]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Vista 3D isometrica de la distribucion
    /*******************************************************************/
    public function Visual($f3, $params){

        /************************************/
        // Se obtiene el ID (bodega propietaria)
        $BodegasID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($BodegasID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query (estructura completa con estados)
        $query = [
            'data'    => '
                bodegas_zonas.idZona,
                bodegas_zonas.Nombre        AS Zona,
                bodegas_zonas.Orden         AS OrdenZona,
                bodegas_zonas_tipos.Color   AS ZonaTipoColor,
                bodegas_pasillos.idPasillo,
                bodegas_pasillos.Nombre     AS Pasillo,
                bodegas_pasillos.Orientacion,
                bodegas_pasillos.Orden      AS OrdenPasillo,
                bodegas_racks.idRack,
                bodegas_racks.Nombre        AS Rack,
                bodegas_racks.Orden         AS OrdenRack,
                bodegas_racks.Niveles,
                bodegas_racks.PosicionesPorNivel,
                bodegas_racks.idRackEnfrentado,
                bodegas_niveles.Nivel,
                bodegas_posiciones.idPosicion,
                bodegas_posiciones.Codigo,
                bodegas_posiciones.Numero,
                bodegas_posiciones_estados.Nombre AS Estado,
                bodegas_posiciones_estados.Color  AS EstadoColor',
            'table'   => 'bodegas_racks',
            'join'    => '
                LEFT JOIN bodegas_pasillos           ON bodegas_pasillos.idPasillo                  = bodegas_racks.idPasillo
                LEFT JOIN bodegas_zonas              ON bodegas_zonas.idZona                        = bodegas_pasillos.idZona
                LEFT JOIN bodegas_zonas_tipos        ON bodegas_zonas_tipos.idZonaTipo              = bodegas_zonas.idZonaTipo
                INNER JOIN bodegas_niveles           ON bodegas_niveles.idRack                      = bodegas_racks.idRack
                INNER JOIN bodegas_posiciones        ON bodegas_posiciones.idNivel                  = bodegas_niveles.idNivel
                LEFT JOIN bodegas_posiciones_estados ON bodegas_posiciones_estados.idEstadoPosicion = bodegas_posiciones.idEstadoPosicion',
            'where'   => 'bodegas_racks.idBodegas = ?',
            'params'  => [$BodegasID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'bodegas_zonas.Orden ASC, bodegas_pasillos.Orden ASC, bodegas_racks.Orden ASC, bodegas_niveles.Nivel ASC, bodegas_posiciones.Numero ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $arrMapa  = $this->Base_GetList($xParams);

        /************************************/
        // Se agrupa por zona -> pasillo -> rack -> nivel
        $arrZonas = [];
        if ($arrMapa['status'] === true && is_array($arrMapa['data'])) {
            foreach ($arrMapa['data'] as $row) {
                /************************************/
                // Se obtiene (o se crea) la zona
                $idZona = $row['idZona'];
                if (!isset($arrZonas[$idZona])) {
                    $arrZonas[$idZona] = [
                        'idZona'        => $row['idZona'],
                        'Zona'          => $row['Zona'],
                        'ZonaTipoColor' => $row['ZonaTipoColor'],
                        'pasillos'      => [],
                    ];
                }
                /************************************/
                // Se obtiene (o se crea) el pasillo
                $idPasillo = $row['idPasillo'];
                if (!isset($arrZonas[$idZona]['pasillos'][$idPasillo])) {
                    $arrZonas[$idZona]['pasillos'][$idPasillo] = [
                        'idPasillo'   => $row['idPasillo'],
                        'Pasillo'     => $row['Pasillo'],
                        'Orientacion' => $row['Orientacion'],
                        'racks'       => [],
                    ];
                }
                /************************************/
                // Se obtiene (o se crea) el rack
                $idRack = $row['idRack'];
                if (!isset($arrZonas[$idZona]['pasillos'][$idPasillo]['racks'][$idRack])) {
                    $arrZonas[$idZona]['pasillos'][$idPasillo]['racks'][$idRack] = [
                        'idRack'             => $row['idRack'],
                        'Rack'               => $row['Rack'],
                        'Niveles'            => (int) $row['Niveles'],
                        'PosicionesPorNivel' => (int) $row['PosicionesPorNivel'],
                        'idRackEnfrentado'   => ($row['idRackEnfrentado'] !== null && $row['idRackEnfrentado'] !== '') ? (int) $row['idRackEnfrentado'] : null,
                        'niveles'            => [],
                    ];
                }
                /************************************/
                // Se obtiene (o se crea) el nivel
                $idNivel = $row['Nivel'];
                if (!isset($arrZonas[$idZona]['pasillos'][$idPasillo]['racks'][$idRack]['niveles'][$idNivel])) {
                    $arrZonas[$idZona]['pasillos'][$idPasillo]['racks'][$idRack]['niveles'][$idNivel] = [
                        'Nivel'      => $row['Nivel'],
                        'posiciones' => [],
                    ];
                }
                /************************************/
                // Se agrega la posicion (con el id cifrado para el clic)
                $EncPosicion = $this->Codification->encryptDecrypt('encrypt', $row['idPosicion']);
                $arrZonas[$idZona]['pasillos'][$idPasillo]['racks'][$idRack]['niveles'][$idNivel]['posiciones'][] = [
                    'idPosicion' => $row['idPosicion'],
                    'idEnc'      => $EncPosicion['data'],
                    'Codigo'     => $row['Codigo'],
                    'Numero'     => $row['Numero'],
                    'Estado'     => $row['Estado'],
                    'Color'      => $row['EstadoColor'],
                ];
            }
        }

        /************************************/
        // Se normalizan los arrays anidados (claves por ID -> listas, para que json_encode genere arreglos)
        foreach ($arrZonas as $kZ => &$zona) {
            foreach ($zona['pasillos'] as $kP => &$pasillo) {
                foreach ($pasillo['racks'] as $kR => &$rack) {
                    $rack['niveles'] = array_values($rack['niveles']);
                }
                $pasillo['racks'] = array_values($pasillo['racks']);
            }
            $zona['pasillos'] = array_values($zona['pasillos']);
        }
        unset($zona, $pasillo, $rack);

        /************************************/
        // Se genera la query (estados de posicion)
        $query = [
            'data'    => 'idEstadoPosicion AS ID,Nombre,Color',
            'table'   => 'bodegas_posiciones_estados',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Orden ASC, Nombre ASC',
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
        if ($arrMapa['status'] === true && $arrEstado['status'] === true) {
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
                'arrZonas'   => array_values($arrZonas),
                'arrEstado'  => $arrEstado['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->viewName.'-Visual.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrMapa,$arrEstado]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Formulario de estado de una posicion
    /*******************************************************************/
    public function Posicion($f3, $params){

        /************************************/
        // Se obtiene el ID
        $PosicionID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($PosicionID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                bodegas_posiciones.idPosicion,
                bodegas_posiciones.idBodegas,
                bodegas_posiciones.idEstadoPosicion,
                bodegas_posiciones.Codigo,
                bodegas_posiciones.Numero,
                bodegas_niveles.Nivel,
                bodegas_racks.Nombre    AS Rack,
                bodegas_pasillos.Nombre AS Pasillo,
                bodegas_zonas.Nombre    AS Zona,
                bodegas_posiciones_estados.Nombre AS Estado,
                bodegas_posiciones_estados.Color  AS EstadoColor',
            'table'   => 'bodegas_posiciones',
            'join'    => '
                LEFT JOIN bodegas_niveles            ON bodegas_niveles.idNivel                     = bodegas_posiciones.idNivel
                LEFT JOIN bodegas_racks              ON bodegas_racks.idRack                        = bodegas_niveles.idRack
                LEFT JOIN bodegas_pasillos           ON bodegas_pasillos.idPasillo                  = bodegas_racks.idPasillo
                LEFT JOIN bodegas_zonas              ON bodegas_zonas.idZona                        = bodegas_pasillos.idZona
                LEFT JOIN bodegas_posiciones_estados ON bodegas_posiciones_estados.idEstadoPosicion = bodegas_posiciones.idEstadoPosicion',
            'where'   => 'bodegas_posiciones.idPosicion = ?',
            'params'  => [$PosicionID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /************************************/
        // Se genera la query (estados de posicion)
        $query = [
            'data'    => 'idEstadoPosicion AS ID,Nombre,Color',
            'table'   => 'bodegas_posiciones_estados',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Orden ASC, Nombre ASC',
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
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->viewName.'-Posicion.php');
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
    // Opciones de posicion para formularios (select)
    /*******************************************************************/
    public function Posiciones($f3, $params){

        /************************************/
        // Se obtiene el ID (bodega propietaria)
        $BodegasID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        if (!$this->isValidDecrypted($BodegasID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                bodegas_posiciones.idPosicion,
                bodegas_posiciones.Codigo,
                bodegas_posiciones_estados.Nombre AS Estado,
                bodegas_zonas.Nombre   AS Zona,
                bodegas_racks.Nombre   AS Rack,
                bodegas_niveles.Nivel',
            'table'   => 'bodegas_posiciones',
            'join'    => '
                LEFT JOIN bodegas_niveles            ON bodegas_niveles.idNivel                     = bodegas_posiciones.idNivel
                LEFT JOIN bodegas_racks              ON bodegas_racks.idRack                        = bodegas_niveles.idRack
                LEFT JOIN bodegas_pasillos           ON bodegas_pasillos.idPasillo                  = bodegas_racks.idPasillo
                LEFT JOIN bodegas_zonas              ON bodegas_zonas.idZona                        = bodegas_pasillos.idZona
                LEFT JOIN bodegas_posiciones_estados ON bodegas_posiciones_estados.idEstadoPosicion = bodegas_posiciones.idEstadoPosicion',
            'where'   => 'bodegas_posiciones.idBodegas = ?',
            'params'  => [$BodegasID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'bodegas_zonas.Orden ASC, bodegas_racks.Orden ASC, bodegas_niveles.Nivel ASC, bodegas_posiciones.Numero ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams     = ['query' => $query];
        // Ejecuto la query
        $arrPosicion = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrPosicion['status'] === true) {
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*=========== Datos Consultados ===========*/
                'arrPosicion' => $arrPosicion['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->viewName.'-Posiciones.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrPosicion]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    /*                                  DATOS                                     */
    /******************************************************************************/
    /*******************************************************************/
    // Cambiar el estado de una posicion
    /*******************************************************************/
    public function PosicionUpdate($f3){

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
            'data'      => 'idPosicion,idEstadoPosicion',
            'required'  => 'idPosicion,idEstadoPosicion',
            'encode'    => '',
            'table'     => 'bodegas_posiciones',
            'where'     => 'idPosicion',
            'Post'      => $_POST
        ];
        // Preparo los datos
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        // Ejecuto la query
        $Response = $this->Base_update($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
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

    /******************************************************************************/
    /*                             Métodos privados                               */
    /******************************************************************************/
    /*******************************************************************/
    // Se validan los datos
    /*******************************************************************/
    private function dataCheck($POST){
        // Variables
        $DataChecking = [
            'emptyData'                  => '',
            'encode'                     => '',
            'ValidarEmail'               => '',
            'ValidarNumero'              => 'idPosicion,idEstadoPosicion',
            'ValidarEntero'              => 'idPosicion,idEstadoPosicion',
            'ValidarRut'                 => '',
            'ValidarPatente'             => '',
            'ValidarFecha'               => '',
            'ValidarHora'                => '',
            'ValidarURL'                 => '',
            'ValidarLargoMinimo'         => '',
            'ValidarLargoMinimoN'        => 3,
            'ValidarLargoMaximoPorCampo' => [],
            'ValidarPalabrasCensuradas'  => '',
            'ValidarEspaciosVacios'      => '',
            'ValidarMayusculas'          => '',
            'ValidarCoincidencias'       => '',
            'ValidarDominioEmail'        => '',
            'ValidarPasswordSegura'      => '',
            'ValidarFechaRango'          => '',
            'ValidarEdadMinima'          => '',
            'ValidarJSON'                => '',
            'ValidarUUID'                => '',
            'ValidarIP'                  => '',
            'ValidarSoloAlfanumerico'    => '',
            'ValidarSoloLetras'          => '',
            'Post'                       => $POST,
        ];
        // Retorno los datos
        return $DataChecking;
    }

}
