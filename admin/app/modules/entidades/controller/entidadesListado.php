<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class entidadesListado extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataDate;
    private $DataNumbers;
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
        $this->controllerName = 'entidadesListado';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->DataDate       = new FunctionsDataDate();
		$this->DataNumbers    = new FunctionsDataNumbers();
		$this->WidgetsCommon  = new UIWidgetsCommon();
		$this->WidgetsViews   = new UIWidgetsViews();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /*******************************************************************/
    // Listar
    /*******************************************************************/
    public function listAll($f3){

        /************************************/
        // Carga incremental: primer bloque (N_MaxItems + 1 fila para saber si hay más)
        $page = $this->getPageParams();

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                entidades_listado.idEntidad,
                entidades_listado.idTipoEntidad,
                entidades_listado.Nombre,
                entidades_listado.ApellidoPat,
                entidades_listado.ApellidoMat,
                entidades_listado.RazonSocial,
                entidades_listado.Nick,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor,
                entidades_sectores.Nombre AS Sector,
                core_tipos_entidad.Nombre AS Tipo,
                core_tipos_entidades.Nombre AS TipoEntidad',
            'table'   => 'entidades_listado',
            'join'    => '
                LEFT JOIN core_estados         ON core_estados.idEstado               = entidades_listado.idEstado
                LEFT JOIN entidades_sectores   ON entidades_sectores.idSector         = entidades_listado.idSector
                LEFT JOIN core_tipos_entidad   ON core_tipos_entidad.idTipo           = entidades_listado.idTipo
                LEFT JOIN core_tipos_entidades ON core_tipos_entidades.idTipoEntidad  = entidades_listado.idTipoEntidad',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'entidades_listado.idEstado ASC, entidades_listado.ApellidoPat ASC, entidades_listado.Nombre ASC, entidades_listado.RazonSocial ASC, entidades_listado.idEntidad ASC',
            'limit'   => $page['limit']
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrList = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
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
        // Se genera la query
        $query = [
            'data'    => 'idSector AS ID,Nombre',
            'table'   => 'entidades_sectores',
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
        $arrSector = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idSexo AS ID,Nombre',
            'table'   => 'core_sexo',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrSexo = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idTipo AS ID,Nombre',
            'table'   => 'core_tipos_entidad',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrTipo = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idTipoEntidad AS ID,Nombre',
            'table'   => 'core_tipos_entidades',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams        = ['query' => $query];
        // Ejecuto la query
        $arrTipoEntidad = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idCiudad AS ID,Nombre',
            'table'   => 'core_ubicacion_ciudad',
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
        $arrCiudad = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idComuna AS ID1, idCiudad AS ID2, Nombre',
            'table'   => 'core_ubicacion_comunas',
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
        $arrComuna = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($arrList['status'] === true && $arrEstado['status'] === true && $arrSector['status'] === true && $arrSexo['status'] === true && $arrTipo['status'] === true && $arrTipoEntidad['status'] === true && $arrCiudad['status'] === true && $arrComuna['status'] === true){

            /************************************/
            // Se recorta la fila extra y se calcula si quedan más datos
            $more = $this->sliceMore($arrList['data'], $page);

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Listado Entidades',
                'PageDescription' => 'Listado Entidades.',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => 'Listado Entidades',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_Codification'    => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $more['rows'],
                'hasMore'         => $more['hasMore'],
                'nextOffset'      => $more['next'],
                'arrEstado'       => $arrEstado['data'],
                'arrSector'       => $arrSector['data'],
                'arrSexo'         => $arrSexo['data'],
                'arrTipo'         => $arrTipo['data'],
                'arrTipoEntidad'  => $arrTipoEntidad['data'],
                'arrCiudad'       => $arrCiudad['data'],
                'arrComuna'       => $arrComuna['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrList,$arrEstado,$arrSector,$arrSexo,$arrTipo,$arrTipoEntidad,$arrCiudad,$arrComuna]);
            // Despliegue de errores
            $this->showError(1, $f3, $result);
        }
    }

    /*******************************************************************/
    // Actualizar Listar
    /*******************************************************************/
    public function UpdateList($f3){

        /************************************/
        // Variables
        $WhereData_int     = 'idEstado,idSector,idSexo,idTipo,idTipoEntidad,idCiudad,idComuna,FNacimiento';  // Datos búsqueda exacta
        $WhereData_string  = 'Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Direccion,Email';              // Datos búsqueda relativa
        $WhereData_between = '';                                                                             // Datos búsqueda Between
        $whereInt          = '';                                                                             // Se crea cadena
        $whereParams       = [];                                                                             // Valores bindeados asociados a $whereInt
        /************************************/
        // Se validan las fechas
        $RespDataBetween = $this->searchValidateDates($WhereData_between);
        if($RespDataBetween!=''){
            Response::error($RespDataBetween, 400);
        }
        /************************************/
        // Agrego variable busqueda
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_int, 'entidades_listado', 1);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_string, 'entidades_listado', 2);
        $whereInt = $r['where']; $whereParams = $r['params'];
        $r = $this->searchWhere($whereInt, $whereParams, $WhereData_between, 'entidades_listado', 3);
        $whereInt = $r['where']; $whereParams = $r['params'];

        /************************************/
        // Carga incremental: offset y tamaño de bloque (N_MaxItems + 1 fila para saber si hay más)
        $page = $this->getPageParams();

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                entidades_listado.idEntidad,
                entidades_listado.idTipoEntidad,
                entidades_listado.Nombre,
                entidades_listado.ApellidoPat,
                entidades_listado.ApellidoMat,
                entidades_listado.RazonSocial,
                entidades_listado.Nick,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor,
                entidades_sectores.Nombre AS Sector,
                core_tipos_entidad.Nombre AS Tipo,
                core_tipos_entidades.Nombre AS TipoEntidad',
            'table'   => 'entidades_listado',
            'join'    => '
                LEFT JOIN core_estados         ON core_estados.idEstado               = entidades_listado.idEstado
                LEFT JOIN entidades_sectores   ON entidades_sectores.idSector         = entidades_listado.idSector
                LEFT JOIN core_tipos_entidad   ON core_tipos_entidad.idTipo           = entidades_listado.idTipo
                LEFT JOIN core_tipos_entidades ON core_tipos_entidades.idTipoEntidad  = entidades_listado.idTipoEntidad',
            'where'   => $whereInt,
            'params'  => $whereParams,
            'group'   => '',
            'having'  => '',
            'order'   => 'entidades_listado.idEstado ASC, entidades_listado.ApellidoPat ASC, entidades_listado.Nombre ASC, entidades_listado.RazonSocial ASC, entidades_listado.idEntidad ASC',
            'limit'   => $page['limit']
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrList = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrList['status'] === true) {

            /************************************/
            // Se recorta la fila extra y se calcula si quedan más datos
            $more = $this->sliceMore($arrList['data'], $page);

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'TableTitle'      => 'Listado Entidades',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'    => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $more['rows'],
                'hasMore'         => $more['hasMore'],
                'nextOffset'      => $more['next'],
            ];

            /************************************/
            // Fragmento (solo filas) para la carga incremental, o tabla completa
            if ($page['fragment']) {
                header('X-Has-More: '.($more['hasMore'] ? '1' : '0'));
                header('X-Next-Offset: '.$more['next']);
                $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-UpdateListRows.php');
            } else {
                $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-UpdateList.php');
            }
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrList]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Listar
    /*******************************************************************/
    public function export($f3){

        /************************************/
        // Misma consulta que exportExcel (getDataExport)
        $arrList = $this->getDataExport();

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if ($arrList['status'] === true) {

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Exportar Entidades',
                'PageDescription' => 'Exportar Entidades.',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => 'Exportar Entidades',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_DataDate'        => $this->DataDate,
                'Fnc_DataNumbers'     => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Exportar.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$arrList]);
            // Despliegue de errores
            $this->showError(1, $f3, $result);
        }
    }

    /*******************************************************************/
    // Exportar a Excel real (.xlsx generado en el servidor, ADR-0021)
    // POST, nivel 3. La página -Exportar no filtra: se exporta lo mismo que muestra
    /*******************************************************************/
    public function exportExcel($f3, $params = null){

        /************************************/
        // Solo POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            (new FunctionsExportExcel())->auditReject($this->controllerName, '', 405);
            Response::error('Método no permitido', 405);
        }

        /************************************/
        // Límite de exportaciones por usuario (1 simultánea + 10/min)
        $Export = new FunctionsExportExcel();
        $userId = (string) ($f3->get('SESSION.DataInfo.UserID') ?: ($_SERVER['REMOTE_ADDR'] ?? '0'));
        $limite = $Export->acquire($userId);
        if ($limite !== '') {
            $Export->auditReject($this->controllerName, '', 429);
            Response::error($limite, 429);
        }
        // Sin filtros que validar: la exportación cuenta para el límite de frecuencia
        $Export->commit();

        /************************************/
        // Misma consulta que la vista -Exportar (mismo orden) leída por bloques hasta N_MaxExportRows
        $maxRows = FunctionsExportExcel::maxRows();
        $arrList = $this->getDataExport($maxRows);
        if ($arrList['status'] !== true) {
            $Export->release();
            $Export->auditReject($this->controllerName, '', 500);
            Response::error('No se pudo generar la exportación', 500);
        }
        $data = (is_array($arrList['data']) ? $arrList['data'] : []);
        // La consulta trae una fila extra solo para detectar que hay más datos
        $truncated = count($data) > $maxRows;
        if ($truncated) { $data = array_slice($data, 0, $maxRows); }

        /************************************/
        // Columnas = columnas de la vista -Exportar
        $columns = [
            ['key' => 'Nombre',         'label' => 'Nombre',            'type' => 'text', 'width' => 35],
            ['key' => 'Nick',           'label' => 'Nick',              'type' => 'text'],
            ['key' => 'Rut',            'label' => 'Rut',               'type' => 'text'],
            ['key' => 'Ciudad',         'label' => 'Ciudad',            'type' => 'text'],
            ['key' => 'Comuna',         'label' => 'Comuna',            'type' => 'text'],
            ['key' => 'Sector',         'label' => 'Sector',            'type' => 'text'],
            ['key' => 'Direccion',      'label' => 'Dirección',         'type' => 'text', 'width' => 35],
            ['key' => 'FNacimiento',    'label' => 'Fecha Nacimiento',  'type' => 'date'],
            ['key' => 'Email',          'label' => 'Email',             'type' => 'text'],
            ['key' => 'Fono1',          'label' => 'Celular',           'type' => 'text'],
            ['key' => 'Fono2',          'label' => 'Teléfono',          'type' => 'text'],
            ['key' => 'Web',            'label' => 'Web',               'type' => 'text'],
            ['key' => 'Giro',           'label' => 'Giro',              'type' => 'text'],
            ['key' => 'Tipo',           'label' => 'Tipo',              'type' => 'text'],
            ['key' => 'TipoEntidad',    'label' => 'Tipo Entidad',      'type' => 'text'],
            ['key' => 'Estado',         'label' => 'Estado',            'type' => 'text'],
            ['key' => 'Sexo',           'label' => 'Sexo',              'type' => 'text'],
            ['key' => 'RepLegalNombre', 'label' => 'Rep Legal Nombre',  'type' => 'text'],
            ['key' => 'RepLegalRut',    'label' => 'Rep Legal Rut',     'type' => 'text'],
            ['key' => 'RepLegalEmail',  'label' => 'Rep Legal Email',   'type' => 'text'],
            ['key' => 'RepLegalFono1',  'label' => 'Rep Legal Celular', 'type' => 'text'],
            ['key' => 'RepLegalFono2',  'label' => 'Rep Legal Teléfono','type' => 'text'],
            ['key' => 'Social_X',       'label' => 'X (twiter)',        'type' => 'text'],
            ['key' => 'Social_Facebook','label' => 'Facebook',          'type' => 'text'],
            ['key' => 'Social_Instagram','label' => 'Instagram',        'type' => 'text'],
            ['key' => 'Social_Linkedin','label' => 'Linkedin',          'type' => 'text'],
        ];

        /************************************/
        // Filas: valores crudos de la consulta (el formato lo aplica el helper)
        $rows = [];
        foreach ($data as $r) {
            // Nombre de la entidad: persona natural (1) o razón social (2), igual que la vista
            if ((int) ($r['idTipoEntidad'] ?? 0) === 1) {
                $entidad = trim(($r['ApellidoPat'] ?? '').' '.($r['ApellidoMat'] ?? '').', '.($r['Nombre'] ?? ''));
            } else {
                $entidad = trim((string) ($r['RazonSocial'] ?? ''));
            }
            $rows[] = [
                'Nombre'          => $entidad,
                'Nick'            => $r['Nick'] ?? '',
                'Rut'             => $r['Rut'] ?? '',
                'Ciudad'          => $r['Ciudad'] ?? '',
                'Comuna'          => $r['Comuna'] ?? '',
                'Sector'          => $r['Sector'] ?? '',
                'Direccion'       => $r['Direccion'] ?? '',
                'FNacimiento'     => $r['FNacimiento'] ?? null,
                'Email'           => $r['Email'] ?? '',
                'Fono1'           => $r['Fono1'] ?? '',
                'Fono2'           => $r['Fono2'] ?? '',
                'Web'             => $r['Web'] ?? '',
                'Giro'            => $r['Giro'] ?? '',
                'Tipo'            => $r['Tipo'] ?? '',
                'TipoEntidad'     => $r['TipoEntidad'] ?? '',
                'Estado'          => $r['Estado'] ?? '',
                'Sexo'            => $r['Sexo'] ?? '',
                'RepLegalNombre'  => $r['RepLegalNombre'] ?? '',
                'RepLegalRut'     => $r['RepLegalRut'] ?? '',
                'RepLegalEmail'   => $r['RepLegalEmail'] ?? '',
                'RepLegalFono1'   => $r['RepLegalFono1'] ?? '',
                'RepLegalFono2'   => $r['RepLegalFono2'] ?? '',
                'Social_X'        => $r['Social_X'] ?? '',
                'Social_Facebook' => $r['Social_Facebook'] ?? '',
                'Social_Instagram'=> $r['Social_Instagram'] ?? '',
                'Social_Linkedin' => $r['Social_Linkedin'] ?? '',
            ];
        }

        /************************************/
        // Descarga: audita (Q11), emite el archivo y termina. Nombre fijo del servidor.
        $userData = $this->getUserData($f3);
        $Export->download('Entidades', [[ 'title' => 'Entidades', 'columns' => $columns, 'rows' => $rows ]], [
            'filters'   => [],
            'user'      => (string) ($userData['UserName'] ?? ''),
            'truncated' => $truncated,
            'audit'     => ['controller' => $this->controllerName, 'userId' => $userId],
        ]);
    }

    /*******************************************************************/
    // View
    /*******************************************************************/
    public function View($f3, $params){

        /************************************/
        // Se no existe el ID
        if (!isset($params['id'])) {
            Response::error('Registro ID inexistente', 400);
        }

        /************************************/
        // Se obtiene el ID
        $EntidadID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        // Se verifica

        if (!$this->isValidDecrypted($EntidadID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se instancia
        $arrUserData = $this->getUserData($f3);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                entidades_listado.Nombre,
                entidades_listado.ApellidoPat,
                entidades_listado.ApellidoMat,
                entidades_listado.RazonSocial,
                entidades_listado.Nick,
                entidades_listado.Rut,
                entidades_listado.Direccion,
                entidades_listado.Direccion_img,
                entidades_listado.FNacimiento,
                entidades_listado.Email,
                entidades_listado.Fono1,
                entidades_listado.Fono2,
                entidades_listado.Web,
                entidades_listado.Giro,
                entidades_listado.RepLegalNombre,
                entidades_listado.RepLegalRut,
                entidades_listado.RepLegalEmail,
                entidades_listado.RepLegalFono1,
                entidades_listado.RepLegalFono2,
                entidades_listado.Social_X,
                entidades_listado.Social_Facebook,
                entidades_listado.Social_Instagram,
                entidades_listado.Social_Linkedin,
                entidades_listado.Ultimo_acceso,
                entidades_listado.idTipo,
                entidades_listado.idTipoEntidad,
                entidades_listado.Latitud,
                entidades_listado.Longitud,

                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor,
                entidades_sectores.Nombre AS Sector,
                core_sexo.Nombre AS Sexo,
                core_tipos_entidad.Nombre AS Tipo,
                core_tipos_entidades.Nombre AS TipoEntidad,
                core_ubicacion_ciudad.Nombre AS Ciudad,
                core_ubicacion_comunas.Nombre AS Comuna',
            'table'   => 'entidades_listado',
            'join'    => '
                LEFT JOIN core_estados             ON core_estados.idEstado               = entidades_listado.idEstado
                LEFT JOIN entidades_sectores       ON entidades_sectores.idSector         = entidades_listado.idSector
                LEFT JOIN core_sexo                ON core_sexo.idSexo                    = entidades_listado.idSexo
                LEFT JOIN core_tipos_entidad       ON core_tipos_entidad.idTipo           = entidades_listado.idTipo
                LEFT JOIN core_tipos_entidades     ON core_tipos_entidades.idTipoEntidad  = entidades_listado.idTipoEntidad
                LEFT JOIN core_ubicacion_ciudad    ON core_ubicacion_ciudad.idCiudad      = entidades_listado.idCiudad
                LEFT JOIN core_ubicacion_comunas   ON core_ubicacion_comunas.idComuna     = entidades_listado.idComuna',
            'where'   => 'entidades_listado.idEntidad = ?',
            'params'  => [$EntidadID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /************************************/
        // Se verifica si se tiene el permiso para visualizar el dato
        if($arrUserData["entidadesListadoVerCargas"] === 2){
            /************************************/
            // Se genera la query
            $query = [
                'data'    => '
                    entidades_listado_cargas.Nombre,
                    entidades_listado_cargas.ApellidoPat,
                    entidades_listado_cargas.ApellidoMat,
                    core_tipos_rrhh_trabajadores_cargas_parentesco.Nombre AS Parentesco',
                'table'   => 'entidades_listado_cargas',
                'join'    => 'LEFT JOIN core_tipos_rrhh_trabajadores_cargas_parentesco   ON core_tipos_rrhh_trabajadores_cargas_parentesco.idParentesco  = entidades_listado_cargas.idParentesco',
                'where'   => 'entidades_listado_cargas.idEntidad = ?',
                'params'  => [$EntidadID['data']],
                'group'   => '',
                'having'  => '',
                'order'   => 'entidades_listado_cargas.ApellidoPat ASC, entidades_listado_cargas.ApellidoMat ASC, entidades_listado_cargas.Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            // Preparo los datos
            $xParams   = ['query' => $query];
            // Ejecuto la query
            $arrCargas = $this->Base_GetList($xParams);
        // Si se permite junto con la creacion de tareas
        }else{
            $arrCargas['status'] = true;
            $arrCargas['data']   = [];
        }

        /************************************/
        // Se verifica si se tiene el permiso para visualizar el dato
        if($arrUserData["entidadesListadoVerContactos"] === 2){
            /************************************/
            // Se genera la query
            $query = [
                'data'    => 'Nombre,ApellidoPat,ApellidoMat,Email,Fono1,Fono2',
                'table'   => 'entidades_listado_contactos',
                'join'    => '',
                'where'   => 'idEntidad = ?',
                'params'  => [$EntidadID['data']],
                'group'   => '',
                'having'  => '',
                'order'   => 'ApellidoPat ASC, ApellidoMat ASC, Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            // Preparo los datos
            $xParams      = ['query' => $query];
            // Ejecuto la query
            $arrContactos = $this->Base_GetList($xParams);
        // Si se permite junto con la creacion de tareas
        }else{
            $arrContactos['status'] = true;
            $arrContactos['data']   = [];
        }

        /************************************/
        // Se verifica si se tiene el permiso para visualizar el dato
        if($arrUserData["entidadesListadoVerDocumentos"] === 2){
            /************************************/
            // Se genera la query
            $query = [
                'data'    => 'idDocumentos,Nombre,NombreArchivo,FVencimiento',
                'table'   => 'entidades_listado_documentos',
                'join'    => '',
                'where'   => 'idEntidad = ?',
                'params'  => [$EntidadID['data']],
                'group'   => '',
                'having'  => '',
                'order'   => 'Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            // Preparo los datos
            $xParams       = ['query' => $query];
            // Ejecuto la query
            $arrDocumentos = $this->Base_GetList($xParams);
        // Si se permite junto con la creacion de tareas
        }else{
            $arrDocumentos['status'] = true;
            $arrDocumentos['data']   = [];
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                entidades_listado_observaciones.Observacion,
                entidades_listado_observaciones.FechaCreacion,
                entidades_listado_observaciones.FechaEdicion,
                usuarios_listado.Nombre AS Usuario,
                usuarios_edicion.Nombre AS UsuarioEdicion',
            'table'   => 'entidades_listado_observaciones',
            'join'    => '
                LEFT JOIN usuarios_listado                     ON usuarios_listado.idUsuario  = entidades_listado_observaciones.idUsuario
                LEFT JOIN usuarios_listado AS usuarios_edicion ON usuarios_edicion.idUsuario  = entidades_listado_observaciones.idUsuarioEdicion',
            'where'   => 'entidades_listado_observaciones.idEntidad = ?',
            'params'  => [$EntidadID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'entidades_listado_observaciones.idObservaciones ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams          = ['query' => $query];
        // Ejecuto la query
        $arrObservaciones = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] === true && $arrCargas['status'] === true && $arrContactos['status'] === true && $arrDocumentos['status'] === true && $arrObservaciones['status'] === true){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_WidgetsViews'  => $this->WidgetsViews,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                'Fnc_Codification'     => $this->Codification,
                'Fnc_WidgetsMaps'      => new UIWidgetsMaps(),
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
                'arrCargas'        => $arrCargas['data'],
                'arrContactos'     => $arrContactos['data'],
                'arrDocumentos'    => $arrDocumentos['data'],
                'arrObservaciones' => $arrObservaciones['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-View.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrCargas,$arrContactos,$arrDocumentos,$arrObservaciones]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /*******************************************************************/
    // Resumen
    /*******************************************************************/
    public function Resumen($f3, $params){

        /************************************/
        // Se no existe el ID
        if (!isset($params['id'])) {
            Response::error('Registro ID inexistente', 400);
        }

        /************************************/
        // Se obtiene el ID
        $EntidadID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        // Se verifica

        if (!$this->isValidDecrypted($EntidadID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                entidades_listado.idEntidad,
                entidades_listado.Nombre,
                entidades_listado.ApellidoPat,
                entidades_listado.ApellidoMat,
                entidades_listado.RazonSocial,
                entidades_listado.Nick,
                entidades_listado.Rut,
                entidades_listado.Direccion,
                entidades_listado.Direccion_img,
                entidades_listado.FNacimiento,
                entidades_listado.Email,
                entidades_listado.Fono1,
                entidades_listado.Fono2,
                entidades_listado.Web,
                entidades_listado.Giro,
                entidades_listado.RepLegalNombre,
                entidades_listado.RepLegalRut,
                entidades_listado.RepLegalEmail,
                entidades_listado.RepLegalFono1,
                entidades_listado.RepLegalFono2,
                entidades_listado.Social_X,
                entidades_listado.Social_Facebook,
                entidades_listado.Social_Instagram,
                entidades_listado.Social_Linkedin,
                entidades_listado.Ultimo_acceso,
                entidades_listado.idEstado,
                entidades_listado.idSector,
                entidades_listado.idSexo,
                entidades_listado.idTipo,
                entidades_listado.idTipoEntidad,
                entidades_listado.idCiudad,
                entidades_listado.idComuna,
                entidades_listado.Latitud,
                entidades_listado.Longitud,

                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor,
                entidades_sectores.Nombre AS Sector,
                core_sexo.Nombre AS Sexo,
                core_tipos_entidad.Nombre AS Tipo,
                core_tipos_entidades.Nombre AS TipoEntidad,
                core_ubicacion_ciudad.Nombre AS Ciudad,
                core_ubicacion_comunas.Nombre AS Comuna',
            'table'   => 'entidades_listado',
            'join'    => '
                LEFT JOIN core_estados             ON core_estados.idEstado               = entidades_listado.idEstado
                LEFT JOIN entidades_sectores       ON entidades_sectores.idSector         = entidades_listado.idSector
                LEFT JOIN core_sexo                ON core_sexo.idSexo                    = entidades_listado.idSexo
                LEFT JOIN core_tipos_entidad       ON core_tipos_entidad.idTipo           = entidades_listado.idTipo
                LEFT JOIN core_tipos_entidades     ON core_tipos_entidades.idTipoEntidad  = entidades_listado.idTipoEntidad
                LEFT JOIN core_ubicacion_ciudad    ON core_ubicacion_ciudad.idCiudad      = entidades_listado.idCiudad
                LEFT JOIN core_ubicacion_comunas   ON core_ubicacion_comunas.idComuna     = entidades_listado.idComuna',
            'where'   => 'entidades_listado.idEntidad = ?',
            'params'  => [$EntidadID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /************************************/
        // Se genera la query
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
        // Se genera la query
        $query = [
            'data'    => 'idSector AS ID,Nombre',
            'table'   => 'entidades_sectores',
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
        $arrSector = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idSexo AS ID,Nombre',
            'table'   => 'core_sexo',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrSexo = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idTipo AS ID,Nombre',
            'table'   => 'core_tipos_entidad',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $arrTipo = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idTipoEntidad AS ID,Nombre',
            'table'   => 'core_tipos_entidades',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams        = ['query' => $query];
        // Ejecuto la query
        $arrTipoEntidad = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idCiudad AS ID,Nombre',
            'table'   => 'core_ubicacion_ciudad',
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
        $arrCiudad = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idComuna AS ID1, idCiudad AS ID2, Nombre',
            'table'   => 'core_ubicacion_comunas',
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
        $arrComuna = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] === true && $arrEstado['status'] === true && $arrSector['status'] === true && $arrSexo['status'] === true && $arrTipo['status'] === true && $arrTipoEntidad['status'] === true && $arrCiudad['status'] === true && $arrComuna['status'] === true){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'        => 'Resumen Entidades',
                'PageDescription'  => 'Resumen Entidades.',
                'PageAuthor'       => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'     => ConfigAPP::SOFTWARE['SoftwareName'],
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'       => $this->FormInputs,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_WidgetsViews'  => $this->WidgetsViews,
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_Codification'     => $this->Codification,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                'Fnc_WidgetsMaps'      => new UIWidgetsMaps(),
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData['data'],
                'arrEstado'       => $arrEstado['data'],
                'arrSector'       => $arrSector['data'],
                'arrSexo'         => $arrSexo['data'],
                'arrTipo'         => $arrTipo['data'],
                'arrTipoEntidad'  => $arrTipoEntidad['data'],
                'arrCiudad'       => $arrCiudad['data'],
                'arrComuna'       => $arrComuna['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrEstado,$arrSector,$arrSexo,$arrTipo,$arrTipoEntidad,$arrCiudad,$arrComuna]);
            // Despliegue de errores
            $this->showError(1, $f3, $result);
        }
    }

    /*******************************************************************/
    // Resumen Actualizar
    /*******************************************************************/
    public function ResumenUpdate($f3, $params){

        /************************************/
        // Se no existe el ID
        if (!isset($params['id'])) {
            Response::error('Registro ID inexistente', 400);
        }

        /************************************/
        // Se obtiene el ID
        $EntidadID = $this->Codification->encryptDecrypt('decrypt', $params['id']);
        // Se verifica

        if (!$this->isValidDecrypted($EntidadID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                entidades_listado.Nombre,
                entidades_listado.ApellidoPat,
                entidades_listado.ApellidoMat,
                entidades_listado.RazonSocial,
                entidades_listado.Nick,
                entidades_listado.Rut,
                entidades_listado.Direccion,
                entidades_listado.Direccion_img,
                entidades_listado.FNacimiento,
                entidades_listado.Email,
                entidades_listado.Fono1,
                entidades_listado.Fono2,
                entidades_listado.Web,
                entidades_listado.Giro,
                entidades_listado.RepLegalNombre,
                entidades_listado.RepLegalRut,
                entidades_listado.RepLegalEmail,
                entidades_listado.RepLegalFono1,
                entidades_listado.RepLegalFono2,
                entidades_listado.Social_X,
                entidades_listado.Social_Facebook,
                entidades_listado.Social_Instagram,
                entidades_listado.Social_Linkedin,
                entidades_listado.Ultimo_acceso,
                entidades_listado.idTipoEntidad,
                entidades_listado.Latitud,
                entidades_listado.Longitud,

                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor,
                entidades_sectores.Nombre AS Sector,
                core_sexo.Nombre AS Sexo,
                core_tipos_entidad.Nombre AS Tipo,
                core_tipos_entidades.Nombre AS TipoEntidad,
                core_ubicacion_ciudad.Nombre AS Ciudad,
                core_ubicacion_comunas.Nombre AS Comuna',
            'table'   => 'entidades_listado',
            'join'    => '
                LEFT JOIN core_estados             ON core_estados.idEstado               = entidades_listado.idEstado
                LEFT JOIN entidades_sectores       ON entidades_sectores.idSector         = entidades_listado.idSector
                LEFT JOIN core_sexo                ON core_sexo.idSexo                    = entidades_listado.idSexo
                LEFT JOIN core_tipos_entidad       ON core_tipos_entidad.idTipo           = entidades_listado.idTipo
                LEFT JOIN core_tipos_entidades     ON core_tipos_entidades.idTipoEntidad  = entidades_listado.idTipoEntidad
                LEFT JOIN core_ubicacion_ciudad    ON core_ubicacion_ciudad.idCiudad      = entidades_listado.idCiudad
                LEFT JOIN core_ubicacion_comunas   ON core_ubicacion_comunas.idComuna     = entidades_listado.idComuna',
            'where'   => 'entidades_listado.idEntidad = ?',
            'params'  => [$EntidadID['data']],
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
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_WidgetsViews'     => $this->WidgetsViews,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                'Fnc_WidgetsMaps'      => new UIWidgetsMaps(),
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Update.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData]);
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
    public function Insert(){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Password inicial por defecto: se fuerza el cambio en el primer ingreso
        $_POST['password']             = '1234';
        $_POST['PasswordForzarCambio'] = 1;
        $_POST['PasswordUltimoCambio'] = date('Y-m-d H:i:s');

        /************************************/
        //Si hay datos
        if(isset($_POST['Direccion'])&&$_POST['Direccion']!=''){
            //Se obtiene la direccion
            $Ubicacion = $_POST['Direccion'];
            //Si existe comuna
            if(isset($_POST['idComuna'])&&$_POST['idComuna']!=''){
                /************************************/
                // Se genera la query
                $query = [
                    'data'    => 'Nombre',
                    'table'   => 'core_ubicacion_comunas',
                    'join'    => '',
                    'where'   => 'idComuna = ?',
                    'params'  => [$_POST['idComuna']],
                    'group'   => '',
                    'having'  => '',
                    'order'   => ''
                ];
                // Preparo los datos
                $xParams = ['query' => $query];
                // Ejecuto la query
                $rowData = $this->Base_GetByID($xParams);
                // Si hay resultados
                if ($rowData['status'] === true) {
                    $Ubicacion .= ', '.$rowData['data']['Nombre'];
                }
            }
            //Si existe ciudad
            if(isset($_POST['idCiudad'])&&$_POST['idCiudad']!=''){
                /************************************/
                // Se genera la query
                $query = [
                    'data'    => 'Nombre',
                    'table'   => 'core_ubicacion_ciudad',
                    'join'    => '',
                    'where'   => 'idCiudad = ?',
                    'params'  => [$_POST['idCiudad']],
                    'group'   => '',
                    'having'  => '',
                    'order'   => ''
                ];
                // Preparo los datos
                $xParams = ['query' => $query];
                // Ejecuto la query
                $rowData = $this->Base_GetByID($xParams);
                // Si hay resultados
                if ($rowData['status'] === true) {
                    $Ubicacion .= ', '.$rowData['data']['Nombre'];
                }
            }
            //Pais
            $Ubicacion .= ', Chile';
            // Se instancia
            $fncLocation = new FunctionsLocation;
            //Se hace la busqueda de lat y long por su direccion
            $result = $fncLocation->geocodeAddress($Ubicacion);
            // Si hay resultados se guarda
            if (is_array($result) && isset($result['lat'], $result['lon'])) {
                //Se guarda el ultimo dato
                $_POST['Latitud']  = $result['lat'];
                $_POST['Longitud'] = $result['lon'];
            }else{
                //Se agregan datos por defecto
                $_POST['Latitud']  = $_SESSION['DataInfo']['Latitud'];
                $_POST['Longitud'] = $_SESSION['DataInfo']['Longitud'];
            }
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idEstado,idSector,idSexo,idTipo,idTipoEntidad,password,Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Rut,idCiudad,idComuna,Direccion,FNacimiento,Email,Fono1,Fono2,Web,Giro,RepLegalNombre,RepLegalRut,RepLegalEmail,RepLegalFono1,RepLegalFono2,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin,IP_Client,Agent_Transp,Ultimo_acceso,Latitud,Longitud,PasswordForzarCambio,PasswordUltimoCambio',
            'required'  => 'idEstado,idTipo,idTipoEntidad,password',
            'unique'    => 'Rut,Email',
            'encode'    => 'password',
            'table'     => 'entidades_listado',
            'Post'      => $_POST
        ];
        /************************************/
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);
        // Preparo los datos
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        // Ejecuto la query
        $Response = $this->Base_insert($xParams);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if ($Response['status'] === false) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se diferencian los errores de validación de los de base de datos
            if (is_array($Response['error'] ?? null)) {
                Response::error('Datos inválidos', 400, $Response['error']);
            }
            // Se reporta el error
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
        }

        /************************************/
        // Confirmar transacción
        $ResponseCommit = $this->Base_transactionCommit();

        /************************************/
        // Si falla la la ejecucion, se revierte de inmediato
        if ($ResponseCommit['status'] === false) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se reporta el error
            Response::error('Error al operar con la Base de Datos', 500, $ResponseCommit['error'] ?? '');
        }

        /************************************/
        // Si es un ID numérico, encripta y envía con código 200 (OK)
        $DataID = $this->Codification->encryptDecrypt('encrypt', $Response['data']);
        // Se verifica
        if (!$this->isValidDecrypted($DataID, 'text')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($DataID['data']);

    }

    /*******************************************************************/
    // Editar por put (solo modificar datos)
    // Editar por post (modificar y subir archivos)
    /*******************************************************************/
    public function Update(){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        //Solo si hay datos y se ha cambiado la direccion
        if(isset($_POST['Direccion'], $_POST['OldDireccion'], $_POST['OldLatitud'])&&(($_POST['Direccion']!=''&&$_POST['Direccion']!=$_POST['OldDireccion']) OR ($_POST['OldLatitud']=='xxxxxxx'))){
            //Se obtiene la direccion
            $Ubicacion = $_POST['Direccion'];
            //Si existe comuna
            if(isset($_POST['idComuna'])&&$_POST['idComuna']!=''){
                /************************************/
                // Se genera la query
                $query = [
                    'data'    => 'Nombre',
                    'table'   => 'core_ubicacion_comunas',
                    'join'    => '',
                    'where'   => 'idComuna = ?',
                    'params'  => [$_POST['idComuna']],
                    'group'   => '',
                    'having'  => '',
                    'order'   => ''
                ];
                // Preparo los datos
                $xParams = ['query' => $query];
                // Ejecuto la query
                $rowData = $this->Base_GetByID($xParams);
                // Si hay resultados
                if ($rowData['status'] === true) {
                    $Ubicacion .= ', '.$rowData['data']['Nombre'];
                }
            }
            //Si existe ciudad
            if(isset($_POST['idCiudad'])&&$_POST['idCiudad']!=''){
                /************************************/
                // Se genera la query
                $query = [
                    'data'    => 'Nombre',
                    'table'   => 'core_ubicacion_ciudad',
                    'join'    => '',
                    'where'   => 'idCiudad = ?',
                    'params'  => [$_POST['idCiudad']],
                    'group'   => '',
                    'having'  => '',
                    'order'   => ''
                ];
                // Preparo los datos
                $xParams = ['query' => $query];
                // Ejecuto la query
                $rowData = $this->Base_GetByID($xParams);
                // Si hay resultados
                if ($rowData['status'] === true) {
                    $Ubicacion .= ', '.$rowData['data']['Nombre'];
                }
            }
            //Pais
            $Ubicacion .= ', Chile';
            // Se instancia
            $fncLocation = new FunctionsLocation;
            //Se hace la busqueda de lat y long por su direccion
            $result = $fncLocation->geocodeAddress($Ubicacion);
            // Si hay resultados se guarda
            if (is_array($result) && isset($result['lat'], $result['lon'])) {
                //Se guarda el ultimo dato
                $_POST['Latitud']  = $result['lat'];
                $_POST['Longitud'] = $result['lon'];
            }else{
                //Se agregan datos por defecto
                $_POST['Latitud']  = $_SESSION['DataInfo']['Latitud'];
                $_POST['Longitud'] = $_SESSION['DataInfo']['Longitud'];
            }

        }

        /************************************/
        // Si se cambia la password, se fuerza el cambio en el primer ingreso
        if(isset($_POST['password'])&&$_POST['password']!=''){
            $_POST['PasswordForzarCambio'] = 1;
            $_POST['PasswordUltimoCambio'] = date('Y-m-d H:i:s');
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idEntidad,idEstado,idSector,idSexo,idTipo,idTipoEntidad,password,Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Rut,idCiudad,idComuna,Direccion,FNacimiento,Email,Fono1,Fono2,Web,Giro,RepLegalNombre,RepLegalRut,RepLegalEmail,RepLegalFono1,RepLegalFono2,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin,IP_Client,Agent_Transp,Ultimo_acceso,Latitud,Longitud,PasswordForzarCambio,PasswordUltimoCambio',
            'required'  => 'idEntidad,idEstado,idTipo,idTipoEntidad',
            'unique'    => 'Rut,Email',
            'encode'    => 'password',
            'table'     => 'entidades_listado',
            'where'     => 'idEntidad',
            'Post'      => $_POST,
            'files'     => [
                [
                    'Identificador' => 'Direccion_img',
                    'SubCarpeta'    => '',
                    'NombreArchivo' => '',
                    'SufijoArchivo' => 'EntidadIMG_',
                    'ValidarTipo'   => 'image',
                    'ValidarPeso'   => 10,
                    'Base64'        => true
                ],
            ]
        ];
        /************************************/
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);
        // Preparo los datos
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        // Ejecuto la query
        $Response = $this->Base_update($xParams);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if ($Response['status'] === false) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se diferencian los errores de validación de los de base de datos
            if (is_array($Response['error'] ?? null)) {
                Response::error('Datos inválidos', 400, $Response['error']);
            }
            // Se reporta el error
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
        }

        /************************************/
        // Confirmar transacción
        $ResponseCommit = $this->Base_transactionCommit();

        /************************************/
        // Si falla la la ejecucion, se revierte de inmediato
        if ($ResponseCommit['status'] === false) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se reporta el error
            Response::error('Error al operar con la Base de Datos', 500, $ResponseCommit['error'] ?? '');
        }

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data']);

    }

    /*******************************************************************/
    // Borrar dato y archivos
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
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Se genera la query
        $query = [
            'files'       => 'Direccion_img',
            'table'       => 'entidades_listado',
            'where'       => 'idEntidad',
            'SubCarpeta'  => '',
            'Post'        => $dataDelete
        ];
        /************************************/
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $Response = $this->Base_delete($xParams);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($Response['status'] === false) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se diferencian los errores de validación de los de base de datos
            if (is_array($Response['error'] ?? null)) {
                Response::error('Datos inválidos', 400, $Response['error']);
            }
            // Se reporta el error
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
        }

        /************************************/
        // Listado de las tablas a eliminar los datos relacionados
        $arrTableDel  = array();
        $arrTableDel[] = ['files' => '',              'table' => 'entidades_listado_cargas'];
        $arrTableDel[] = ['files' => '',              'table' => 'entidades_listado_contactos'];
        $arrTableDel[] = ['files' => 'NombreArchivo', 'table' => 'entidades_listado_documentos'];
        $arrTableDel[] = ['files' => '',              'table' => 'entidades_listado_observaciones'];

        /************************************/
        // Verifico si existe
        if (!empty($arrTableDel)) {
            // Recorro
            foreach ($arrTableDel as $tblDel) {
                /************************************/
                // Se genera la query
                $query = ['files' => $tblDel['files'], 'table' => $tblDel['table'], 'where' => 'idEntidad', 'SubCarpeta' => '', 'Post' => $dataDelete];
                // Preparo los datos
                $xParams    = ['query' => $query];
                // Ejecuto la query
                $respDelRel = $this->Base_delete($xParams);
                /************************************/
                // Si falla la ejecucion, se revierte de inmediato
                if ($respDelRel['status'] === false) {
                    // Se revierte la transaccion activa (no-op si no hay transaccion)
                    $this->Base_transactionRollback();
                    // Se diferencian los errores de validación de los de base de datos
                    if (is_array($respDelRel['error'] ?? null)) {
                        Response::error('Datos inválidos', 400, $respDelRel['error']);
                    }
                    // Se reporta el error
                    Response::error('Error al operar con la Base de Datos', 500, $respDelRel['error'] ?? '');
                }
            }
        }

        /************************************/
        // Se confirma la transacción
        $ResponseCommit = $this->Base_transactionCommit();

        /************************************/
        // Si falla la la ejecucion, se revierte de inmediato
        if ($ResponseCommit['status'] === false) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se reporta el error
            Response::error('Error al operar con la Base de Datos', 500, $ResponseCommit['error'] ?? '');
        }

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data']);

    }

    /*******************************************************************/
    // Borrar archivos
    /*******************************************************************/
    public function delFiles(){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Obtener datos
        parse_str(file_get_contents("php://input"),$dataPut);

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Se genera la query
        $query = [
            'files'       => 'Direccion_img',
            'table'       => 'entidades_listado',
            'where'       => 'idEntidad',
            'SubCarpeta'  => '',
            'Post'        => $dataPut
        ];
        /************************************/
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $Response = $this->Base_delFiles($xParams);

        /************************************/
        // Si falla la la ejecucion, se muestra alerta
        if ($Response['status'] === false) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se diferencian los errores de validación de los de base de datos
            if (is_array($Response['error'] ?? null)) {
                Response::error('Datos inválidos', 400, $Response['error']);
            }
            // Se reporta el error
            Response::error('Error al operar con la Base de Datos', 500, $Response['error'] ?? '');
        }

        /************************************/
        // Confirmar transacción
        $ResponseCommit = $this->Base_transactionCommit();

        /************************************/
        // Si falla la la ejecucion, se revierte de inmediato
        if ($ResponseCommit['status'] === false) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se reporta el error
            Response::error('Error al operar con la Base de Datos', 500, $ResponseCommit['error'] ?? '');
        }

        /************************************/
        // Devuelvo $Response con código 200 (OK)
        Response::success($Response['data']);

    }

    /******************************************************************************/
    /*                             Métodos privados                               */
    /******************************************************************************/
    /*******************************************************************/
    // Consulta compartida por export (vista) y exportExcel (Excel): una sola fuente
    /*******************************************************************/
    private function getDataExport(?int $maxRows = null): array{
        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                entidades_listado.Nombre,
                entidades_listado.ApellidoPat,
                entidades_listado.ApellidoMat,
                entidades_listado.RazonSocial,
                entidades_listado.Nick,
                entidades_listado.Rut,
                entidades_listado.Direccion,
                entidades_listado.FNacimiento,
                entidades_listado.Email,
                entidades_listado.Fono1,
                entidades_listado.Fono2,
                entidades_listado.Web,
                entidades_listado.Giro,
                entidades_listado.RepLegalNombre,
                entidades_listado.RepLegalRut,
                entidades_listado.RepLegalEmail,
                entidades_listado.RepLegalFono1,
                entidades_listado.RepLegalFono2,
                entidades_listado.Social_X,
                entidades_listado.Social_Facebook,
                entidades_listado.Social_Instagram,
                entidades_listado.Social_Linkedin,
                entidades_listado.idTipoEntidad,

                core_estados.Nombre AS Estado,
                entidades_sectores.Nombre AS Sector,
                core_sexo.Nombre AS Sexo,
                core_tipos_entidad.Nombre AS Tipo,
                core_tipos_entidades.Nombre AS TipoEntidad,
                core_ubicacion_ciudad.Nombre AS Ciudad,
                core_ubicacion_comunas.Nombre AS Comuna',
            'table'   => 'entidades_listado',
            'join'    => '
                LEFT JOIN core_estados           ON core_estados.idEstado               = entidades_listado.idEstado
                LEFT JOIN entidades_sectores     ON entidades_sectores.idSector         = entidades_listado.idSector
                LEFT JOIN core_sexo              ON core_sexo.idSexo                    = entidades_listado.idSexo
                LEFT JOIN core_tipos_entidad     ON core_tipos_entidad.idTipo           = entidades_listado.idTipo
                LEFT JOIN core_tipos_entidades   ON core_tipos_entidades.idTipoEntidad  = entidades_listado.idTipoEntidad
                LEFT JOIN core_ubicacion_ciudad  ON core_ubicacion_ciudad.idCiudad      = entidades_listado.idCiudad
                LEFT JOIN core_ubicacion_comunas ON core_ubicacion_comunas.idComuna     = entidades_listado.idComuna
                ',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'entidades_listado.idEstado ASC, entidades_listado.ApellidoPat ASC, entidades_listado.Nombre ASC, entidades_listado.RazonSocial ASC, entidades_listado.idEntidad ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Excel: lectura por bloques hasta el tope de exportación (la vista -Exportar usa un solo bloque)
        if ($maxRows !== null) {
            return $this->Base_GetListChunked($query, $maxRows);
        }
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        return $this->Base_GetList($xParams);
    }

    /*******************************************************************/
    // Se validan los datos
    /*******************************************************************/
    private function dataCheck($POST){
        // Variables
        $DataChecking = [
            'emptyData'                  => '',
            'encode'                     => '',
            'ValidarEmail'               => 'Email,RepLegalEmail',
            'ValidarNumero'              => 'idEstado,idSector,idSexo,idTipo,idTipoEntidad,idCiudad,idComuna,Fono1,Fono2,RepLegalFono1,RepLegalFono2,Latitud,Longitud',
            'ValidarEntero'              => 'idEstado,idSector,idSexo,idTipo,idTipoEntidad,idCiudad,idComuna',
            'ValidarRut'                 => 'Rut,RepLegalRut',
            'ValidarPatente'             => '',
            'ValidarFecha'               => 'FNacimiento',
            'ValidarHora'                => '',
            'ValidarURL'                 => 'Web,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin',
            'ValidarLargoMinimo'         => 'Email,RepLegalEmail,Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Direccion,Giro,RepLegalNombre,password',
            'ValidarLargoMinimoN'        => 3,
            'ValidarLargoMaximoPorCampo' => [
                'Rut'              => 15,
                'RepLegalRut'      => 15,
                'Fono1'            => 15,
                'Fono2'            => 15,
                'RepLegalFono1'    => 15,
                'RepLegalFono2'    => 15,
                'Web'              => 255,
                'Social_X'         => 255,
                'Social_Facebook'  => 255,
                'Social_Instagram' => 255,
                'Social_Linkedin'  => 255,
                'Agent_Transp'     => 240,
                'IP_Client'        => 120,
                'Email'            => 255,
                'RepLegalEmail'    => 255,
                'Nombre'           => 255,
                'ApellidoPat'      => 255,
                'ApellidoMat'      => 255,
                'RazonSocial'      => 255,
                'Nick'             => 255,
                'Direccion'        => 255,
                'Giro'             => 255,
                'RepLegalNombre'   => 255,
                'password'         => 255,
            ],
            'ValidarPalabrasCensuradas' => 'Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Direccion,Giro,RepLegalNombre',
            'ValidarEspaciosVacios'     => 'Email,RepLegalEmail,Web,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin,password',
            'ValidarMayusculas'         => 'Email,RepLegalEmail',
            'ValidarCoincidencias'      => '',
            'ValidarDominioEmail'       => 'Email,RepLegalEmail',
            'ValidarPasswordSegura'     => '',
            'ValidarFechaRango'         => 'FNacimiento',
            'ValidarEdadMinima'         => '',
            'ValidarJSON'               => '',
            'ValidarUUID'               => '',
            'ValidarIP'                 => 'IP_Client',
            'ValidarSoloAlfanumerico'   => '',
            'ValidarSoloLetras'         => '',
            'Post'                      => $POST,
        ];
        // Retorno los datos
        return $DataChecking;
    }

}
