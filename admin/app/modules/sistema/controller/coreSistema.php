<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class coreSistema extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $WidgetsCommon;
    private $WidgetsViews;
    private $DataDate;
    private $DataNumbers;
    private $Codification;

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'coreSistema';
		$this->FormInputs     = new UIFormInputs();
		$this->WidgetsCommon  = new UIWidgetsCommon();
		$this->WidgetsViews  = new UIWidgetsViews();
		$this->DataDate       = new FunctionsDataDate();
		$this->DataNumbers    = new FunctionsDataNumbers();
		$this->Codification   = new FunctionsSecurityCodification();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /*******************************************************************/
    // Resumen
    /*******************************************************************/
    public function Resumen($f3){

        /************************************/
        // Se genera la query
        $rowData = $this->getDataDetail();

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

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idIAProvider AS ID,Nombre',
            'table'   => 'core_ia_provider',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];
        // Ejecuto la query
        $arrIAProvider = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrCiudad['status'] && $arrComuna['status'] && $arrIAProvider['status']){
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'        => 'Configuracion Plataforma',
                'PageDescription'  => 'Configuracion Plataforma.',
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
                'Fnc_DataNumbers'      => $this->DataNumbers,
                'Fnc_Codification'     => $this->Codification,
                'Fnc_WidgetsMaps'      => new UIWidgetsMaps(),
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
                'arrCiudad'        => $arrCiudad['data'],
                'arrComuna'        => $arrComuna['data'],
                'arrIAProvider'    => $arrIAProvider['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrCiudad,$arrComuna,$arrIAProvider]);
            // Despliegue de errores
            $this->showError(1, $f3, $result);
        }
    }

    /*******************************************************************/
    // Resumen Actualizar
    /*******************************************************************/
    public function ResumenUpdate($f3){

        /************************************/
        // Se genera la query
        $rowData = $this->getDataDetail();

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
                'Fnc_DataNumbers'      => $this->DataNumbers,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_WidgetsViews'  => $this->WidgetsViews,
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

    /*******************************************************************/
    // Orden y activacion de los widgets de la pantalla principal
    /*******************************************************************/
    public function WidgetsOrden($f3){

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idSistema,Config_Principal_Widgets',
            'table'   => 'core_sistemas',
            'join'    => '',
            'where'   => 'idSistema = ?',
            'params'  => [1],
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
            // Widgets descubiertos combinados con el orden guardado (los nuevos quedan al final)
            $merged = WidgetsOrderService::merge(WidgetsOrderService::discover(), $rowData['data']['Config_Principal_Widgets'] ?? null);
            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'        => 'Orden de Widgets',
                'PageDescription'  => 'Orden y activacion de los widgets de la pantalla principal.',
                'PageAuthor'       => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'     => ConfigAPP::SOFTWARE['SoftwareName'],
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_WidgetsViews'  => $this->WidgetsViews,
                /*=========== Datos Consultados ===========*/
                'arrWidgets'       => $merged['items'],
                'isConfigured'     => $merged['configured'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-WidgetsOrden.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData]);
            // Despliegue de errores
            $this->showError(1, $f3, $result);
        }
    }

    /******************************************************************************/
    /*                            CONSULTAS INTERNAS                              */
    /******************************************************************************/
    /*******************************************************************/
    // Se obtienen los detalles
    /*******************************************************************/
    private function getDataDetail(){

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                core_sistemas.idSistema,
                core_sistemas.Sistema_Nombre,
                core_sistemas.Sistema_Email,
                core_sistemas.Sistema_Rut,
                core_sistemas.Sistema_idCiudad,
                core_sistemas.Sistema_idComuna,
                core_sistemas.Sistema_Direccion,
                core_sistemas.Sistema_IMGLogo,
                core_sistemas.Sistema_idTema,
                core_sistemas.Sistema_NotiWhatsapp,
                core_sistemas.Contacto_Nombre,
                core_sistemas.Contacto_Fono1,
                core_sistemas.Contacto_Fono2,
                core_sistemas.Contacto_Fax,
                core_sistemas.Contacto_Email,
                core_sistemas.Contacto_Web,
                core_sistemas.RepresentanteNombre,
                core_sistemas.RepresentanteRut,
                core_sistemas.RepresentanteFono,
                core_sistemas.RepresentanteEmail,
                core_sistemas.Config_API_GoogleMaps,
                core_sistemas.Config_WhatsappToken,
                core_sistemas.Config_WhatsappInstanceId,
                core_sistemas.Latitud,
                core_sistemas.Longitud,
                core_sistemas.Social_X,
                core_sistemas.Social_Facebook,
                core_sistemas.Social_Instagram,
                core_sistemas.Social_Linkedin,
                core_sistemas.Latitud,
                core_sistemas.Longitud,
                core_sistemas.Config_Principal_Meteo,
                core_sistemas.Config_Principal_Radio,
                core_sistemas.Config_Principal_Feed,
                core_sistemas.Config_Principal_FeedURL,
                core_sistemas.Config_IA_Provider,
                core_sistemas.Config_IA_ApiKey,
                core_sistemas.Config_IA_Model,
                core_sistemas.Config_IA_Base_URL,
                core_sistemas.Config_IA_Name,
                core_sistemas.Config_IA_Tone,
                core_sistemas.Config_IA_Uso,
                core_sistemas.Config_IA_UsoCache,
                core_sistemas.usuariosPermisosBodegas,
                core_sistemas.usuariosPermisosMaquinas,
                core_sistemas.idOpcionesGen_39,
                core_sistemas.idOpcionesGen_40,

                core_ubicacion_ciudad.Nombre AS Ciudad,
                core_ubicacion_comunas.Nombre AS Comuna,
                core_temas.Nombre AS Tema,
                core_ia_provider.Nombre AS IAProvider',
            'table'   => 'core_sistemas',
            'join'    => '
                LEFT JOIN core_ubicacion_ciudad  ON core_ubicacion_ciudad.idCiudad        = core_sistemas.Sistema_idCiudad
                LEFT JOIN core_ubicacion_comunas ON core_ubicacion_comunas.idComuna       = core_sistemas.Sistema_idComuna
                LEFT JOIN core_temas             ON core_temas.idTema                     = core_sistemas.Sistema_idTema
                LEFT JOIN core_ia_provider       ON core_ia_provider.idIAProvider         = core_sistemas.Config_IA_Provider',
            'where'   => 'core_sistemas.idSistema = ?',
            'params'  => [1],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        //Se retornan los datos
        return $this->Base_GetByID($xParams);
    }

    /******************************************************************************/
    /*                                  DATOS                                     */
    /******************************************************************************/
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
        // Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /************************************/
        //Si hay datos
        if(isset($_POST['Sistema_Direccion'])&&$_POST['Sistema_Direccion']!=''){
            //Se obtiene la direccion
            $Ubicacion = $_POST['Sistema_Direccion'];
            //Si existe comuna
            if(isset($_POST['Sistema_idComuna'])&&$_POST['Sistema_idComuna']!=''){
                /************************************/
                // Se genera la query
                $query = [
                    'data'    => 'Nombre',
                    'table'   => 'core_ubicacion_comunas',
                    'join'    => '',
                    'where'   => 'idComuna = ?',
                    'params'  => [$_POST['Sistema_idComuna']],
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
            if(isset($_POST['Sistema_idCiudad'])&&$_POST['Sistema_idCiudad']!=''){
                /************************************/
                // Se genera la query
                $query = [
                    'data'    => 'Nombre',
                    'table'   => 'core_ubicacion_ciudad',
                    'join'    => '',
                    'where'   => 'idCiudad = ?',
                    'params'  => [$_POST['Sistema_idCiudad']],
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
            if ($result) {
                //Se guarda el ultimo dato
                $_POST['Latitud']  = $result['lat'];
                $_POST['Longitud'] = $result['lon'];
            }
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idSistema,Sistema_Nombre,Sistema_Email,Sistema_Rut,Sistema_idCiudad,Sistema_idComuna,Sistema_Direccion,Sistema_idTema,Sistema_NotiWhatsapp,Contacto_Nombre,Contacto_Fono1,Contacto_Fono2,Contacto_Fax,Contacto_Email,Contacto_Web,RepresentanteNombre,RepresentanteRut,RepresentanteFono,RepresentanteEmail,Config_API_GoogleMaps,Config_WhatsappToken,Config_WhatsappInstanceId,KanbanTareasUsoTareas,KanbanTareasAdminTabIndepend,entidadesListadoVerCargas,entidadesListadoVerContactos,entidadesListadoVerDocumentos,productosListadoVerDocumentos,serviciosListadoVerDocumentos,entidadesListadoUsoPassword,gestionDocumentosUsoBodega,entidadesListadoUsoPlanes,entidadesListadoUsoUsuarios,maquinasListadoVerDocumentos,maquinasListadoComponentes,maquinasListadoTelemetria,maquinasListadoBackups,sistemaModalSubtitle,sistemaModalCloseBTN,entidadesListadoUsoMaquinas,maquinasListadoNotificaciones,sistemaUsoWhatsapp,Config_motorEmail,Config_motorMap,Latitud,Longitud,Config_Principal_Meteo,Config_Principal_Radio,Config_Principal_Feed,Config_Principal_FeedURL,Config_IA_Provider,Config_IA_ApiKey,Config_IA_Model,Config_IA_Base_URL,Config_IA_Name,Config_IA_Tone,Config_IA_Uso,Config_IA_UsoCache,usuariosPermisosBodegas,usuariosPermisosMaquinas,idOpcionesGen_39,idOpcionesGen_40,Social_X, Social_Facebook, Social_Instagram, Social_Linkedin',
            'required'  => 'idSistema,Sistema_Nombre',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'core_sistemas',
            'where'     => 'idSistema',
            'Post'      => $_POST,
            'files'     => [
                [
                    'Identificador' => 'Sistema_IMGLogo',
                    'SubCarpeta'    => '',
                    'NombreArchivo' => '',
                    'SufijoArchivo' => 'LogoSis_',
                    'ValidarTipo'   => 'image',
                    'ValidarPeso'   => 10,
                    'Base64'        => true
                ],
            ]
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
        // Se genera la query
        $query = [
            'files'       => 'Sistema_IMGLogo',
            'table'       => 'core_sistemas',
            'where'       => 'idSistema',
            'SubCarpeta'  => '',
            'Post'        => $dataPut
        ];
        // Preparo los datos
        $xParams  = ['query' => $query];

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        // Ejecuto la query
        $Response = $this->Base_delFiles($xParams);

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

    /*******************************************************************/
    // Guardar el orden y estado de los widgets (POST)
    // Config_Principal_Widgets = JSON jerarquico [{"key":"<Bloque>","activo":1,"items":[{"key":"<Sub>","activo":1}]}]
    // reset = 1 => vuelve al orden por defecto
    /*******************************************************************/
    public function WidgetsOrdenSave(){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Restablecer orden por defecto: '[]' (el query builder ignora valores vacios)
        if(isset($_POST['reset']) && $_POST['reset']=='1'){
            $json = '[]';
        } else {
            /************************************/
            // Se decodifica y valida contra los widgets realmente existentes.
            // Se espera la jerarquia: [{key:Bloque, activo, items:[{key:Item, activo}]}]
            $items = json_decode($_POST['Config_Principal_Widgets'] ?? '', true);
            if(!is_array($items) || count($items)===0){
                Response::error('Datos de widgets inválidos', 400);
            }
            $discovered = WidgetsOrderService::discover();
            $clean      = [];
            foreach ($items as $block) {
                // Cada bloque debe existir y no repetirse
                if(!is_array($block) || !isset($block['key']) || !is_string($block['key']) || !isset($discovered[$block['key']])){
                    Response::error('Datos de widgets inválidos', 400);
                }
                $blockKey = $block['key'];
                if(isset($clean[$blockKey])){
                    continue;
                }
                // Se validan los sub-widgets del bloque contra los descubiertos
                $subClean = [];
                $subItems = (isset($block['items']) && is_array($block['items'])) ? $block['items'] : [];
                foreach ($subItems as $item) {
                    if(!is_array($item) || !isset($item['key']) || !is_string($item['key']) || !isset($discovered[$blockKey]['items'][$item['key']])){
                        Response::error('Datos de widgets inválidos', 400);
                    }
                    if(isset($subClean[$item['key']])){
                        continue;
                    }
                    $subClean[$item['key']] = ['key' => $item['key'], 'activo' => (isset($item['activo']) && (int)$item['activo']===0) ? 0 : 1];
                }
                // Se anexa el bloque con su interruptor maestro y sus sub-widgets
                $clean[$blockKey] = [
                    'key'    => $blockKey,
                    'activo' => (isset($block['activo']) && (int)$block['activo']===0) ? 0 : 1,
                    'items'  => array_values($subClean),
                ];
            }
            // Si tras la validacion no queda ningun bloque, se rechaza
            if(count($clean)===0){
                Response::error('Datos de widgets inválidos', 400);
            }
            $json = WidgetsOrderService::encode(array_values($clean));
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'idSistema,Config_Principal_Widgets',
            'required'  => 'idSistema',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'core_sistemas',
            'where'     => 'idSistema',
            'Post'      => ['idSistema' => 1, 'Config_Principal_Widgets' => $json],
        ];
        // Preparo los datos
        $xParams  = ['DataCheck' => [], 'query' => $query, 'novalidate' => true];

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
            'ValidarEmail'               => 'Sistema_Email,Contacto_Email,RepresentanteEmail',
            'ValidarNumero'              => '',
            'ValidarEntero'              => 'Sistema_idCiudad,Sistema_idComuna,Config_IA_Provider,Config_IA_Uso,Config_IA_UsoCache,Config_Principal_Meteo,Config_Principal_Radio,Config_Principal_Feed,usuariosPermisosBodegas,usuariosPermisosMaquinas,Sistema_idTema,KanbanTareasUsoTareas,KanbanTareasAdminTabIndepend,entidadesListadoVerCargas,entidadesListadoVerContactos,entidadesListadoVerDocumentos,productosListadoVerDocumentos,serviciosListadoVerDocumentos,entidadesListadoUsoPassword,gestionDocumentosUsoBodega,entidadesListadoUsoPlanes,entidadesListadoUsoUsuarios,maquinasListadoVerDocumentos,maquinasListadoComponentes,maquinasListadoTelemetria,maquinasListadoBackups,sistemaModalSubtitle,sistemaModalCloseBTN,entidadesListadoUsoMaquinas,maquinasListadoNotificaciones,sistemaUsoWhatsapp,Config_motorEmail,Config_motorMap,idOpcionesGen_39,idOpcionesGen_40',
            'ValidarRut'                 => 'Sistema_Rut,RepresentanteRut',
            'ValidarPatente'             => '',
            'ValidarFecha'               => '',
            'ValidarHora'                => '',
            'ValidarURL'                 => 'Social_X,Social_Facebook,Social_Instagram,Social_Linkedin,Contacto_Web,Config_IA_Base_URL,Config_Principal_FeedURL',
            'ValidarLargoMinimo'         => 'Sistema_Email,Contacto_Email,RepresentanteEmail,Sistema_Nombre,Sistema_Direccion,Contacto_Nombre,RepresentanteNombre',
            'ValidarLargoMinimoN'        => 3,
            'ValidarLargoMaximoPorCampo' => [
                'Sistema_Nombre'            => 120,
                'Sistema_Direccion'         => 180,
                'Sistema_NotiWhatsapp'      => 15,
                'Sistema_Rut'               => 13,
                'Contacto_Nombre'           => 120,
                'Contacto_Fono1'            => 15,
                'Contacto_Fono2'            => 15,
                'Contacto_Fax'              => 15,
                'Contacto_Email'            => 120,
                'Contacto_Web'              => 120,
                'RepresentanteNombre'       => 120,
                'RepresentanteRut'          => 13,
                'RepresentanteFono'         => 120,
                'RepresentanteEmail'        => 120,
                'Config_API_GoogleMaps'     => 255,
                'Config_WhatsappToken'      => 255,
                'Config_WhatsappInstanceId' => 255,
                'Config_Principal_FeedURL'  => 255,
                'Config_IA_Base_URL'        => 255,
                'Config_IA_Model'           => 255,
                'Config_IA_Name'            => 255,
                'Config_IA_Tone'            => 255,
                'Social_X'                  => 255,
                'Social_Facebook'           => 255,
                'Social_Instagram'          => 255,
                'Social_Linkedin'           => 255,
                'Sistema_Email'             => 60,
            ],
            'ValidarPalabrasCensuradas' => 'Sistema_Nombre,Sistema_Direccion,Contacto_Nombre,RepresentanteNombre',
            'ValidarEspaciosVacios'     => 'Sistema_Email,Contacto_Email,RepresentanteEmail,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin',
            'ValidarMayusculas'         => 'Sistema_Email,Contacto_Email,RepresentanteEmail',
            'ValidarCoincidencias'      => '',
            'ValidarDominioEmail'       => 'Sistema_Email,Contacto_Email,RepresentanteEmail',
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
