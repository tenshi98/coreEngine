<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class sistemaOpciones extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;
    private $FormInputs;
    private $WidgetsCommon;
    private $DataDate;
    private $DataNumbers;
    private $Codification;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_ADMIN);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'Empty';
		$this->FormInputs     = new UIFormInputs();
		$this->WidgetsCommon  = new UIWidgetsCommon();
		$this->DataDate       = new FunctionsDataDate();
		$this->DataNumbers    = new FunctionsDataNumbers();
		$this->Codification   = new FunctionsSecurityCodification();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //Resumen
    public function Resumen($f3){
        /*******************************************************************/
        //Se llaman los datos
        $arrMenu  = $f3->get('SESSION.arrMenu');

        /******************************************/
        //Se genera la query
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
                core_sistemas.KanbanTareasUsoTareas,
                core_sistemas.KanbanTareasAdminTabIndepend,
                core_sistemas.entidadesListadoVerCargas,
                core_sistemas.entidadesListadoVerContactos,
                core_sistemas.entidadesListadoVerDocumentos,
                core_sistemas.productosListadoVerDocumentos,
                core_sistemas.serviciosListadoVerDocumentos,
                core_sistemas.entidadesListadoUsoPassword,
                core_sistemas.gestionDocumentosUsoBodega,
                core_sistemas.entidadesListadoUsoPlanes,
                core_sistemas.entidadesListadoUsoUsuarios,
                core_sistemas.maquinasListadoVerDocumentos,
                core_sistemas.maquinasListadoComponentes,
                core_sistemas.maquinasListadoTelemetria,
                core_sistemas.maquinasListadoBackups,
                core_sistemas.sistemaModalSubtitle,
                core_sistemas.sistemaModalCloseBTN,
                core_sistemas.entidadesListadoUsoMaquinas,
                core_sistemas.maquinasListadoNotificaciones,
                core_sistemas.sistemaUsoWhatsapp,
                core_sistemas.Config_motorEmail,
                core_sistemas.Config_motorMap,
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
                core_sistemas.Social_X,
                core_sistemas.Social_Facebook,
                core_sistemas.Social_Instagram,
                core_sistemas.Social_Linkedin,

                core_ubicacion_ciudad.Nombre AS Ciudad,
                core_ubicacion_comunas.Nombre AS Comuna,
                core_temas.Nombre AS Tema,
                core_config_email.Nombre AS ConfigEmail,
                core_config_map.Nombre AS ConfigMap,
                core_ia_provider.Nombre AS IAProvider',
            'table'   => 'core_sistemas',
            'join'    => '
                LEFT JOIN core_ubicacion_ciudad  ON core_ubicacion_ciudad.idCiudad        = core_sistemas.Sistema_idCiudad
                LEFT JOIN core_ubicacion_comunas ON core_ubicacion_comunas.idComuna       = core_sistemas.Sistema_idComuna
                LEFT JOIN core_temas             ON core_temas.idTema                     = core_sistemas.Sistema_idTema
                LEFT JOIN core_config_email      ON core_config_email.idConfigEmail       = core_sistemas.Config_motorEmail
                LEFT JOIN core_config_map        ON core_config_map.idConfigMap           = core_sistemas.Config_motorMap
                LEFT JOIN core_ia_provider       ON core_ia_provider.idIAProvider         = core_sistemas.Config_IA_Provider',
            'where'   => 'core_sistemas.idSistema = "1"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /******************************/
        //Se genera la query
        $query = [
            'data'    => 'idCiudad AS ID,Nombre',
            'table'   => 'core_ubicacion_ciudad',
            'join'    => '',
            'where'   => '',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrCiudad = $this->Base_GetList($xParams);

        /******************************/
        //Se genera la query
        $query = [
            'data'    => 'idComuna AS ID1, idCiudad AS ID2, Nombre',
            'table'   => 'core_ubicacion_comunas',
            'join'    => '',
            'where'   => '',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrComuna = $this->Base_GetList($xParams);

        /******************************/
        //Se genera la query
        $query = [
            'data'    => 'idConfigEmail AS ID, Nombre',
            'table'   => 'core_config_email',
            'join'    => '',
            'where'   => '',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrConfigEmail = $this->Base_GetList($xParams);

        /******************************/
        //Se genera la query
        $query = [
            'data'    => 'idConfigMap AS ID, Nombre',
            'table'   => 'core_config_map',
            'join'    => '',
            'where'   => '',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrConfigMap = $this->Base_GetList($xParams);

        /******************************/
        //Se genera la query
        $query = [
            'data'    => 'idTema AS ID,Nombre',
            'table'   => 'core_temas',
            'join'    => '',
            'where'   => '',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams  = ['query' => $query];
        $arrTemas = $this->Base_GetList($xParams);

        /******************************/
        //Se genera la query
        $query = [
            'data'    => 'idIAProvider AS ID,Nombre',
            'table'   => 'core_ia_provider',
            'join'    => '',
            'where'   => '',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams       = ['query' => $query];
        $arrIAProvider = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Variables
        $MainViewData = [
            'Count_GestionProyectos'      => 0,
            'Count_GestionEntidades'      => 0,
            'Count_Productos'             => 0,
            'Count_Servicios'             => 0,
            'Count_DocumentosMercantiles' => 0,
            'Count_Externalizacion'       => 0,
            'Count_Maquinas'              => 0,
        ];
        //Se asignan datos a buscar
        $menuCounters = [
            'Gestión Proyectos' => [
                'Informe Tareas'   => 'Count_GestionProyectos',
                'Tareas en Curso'  => 'Count_GestionProyectos',
            ],
            'Administración' => [
                'Gestion Entidades - Listado' => 'Count_GestionEntidades',
                'Productos - Categorias'      => 'Count_Productos',
                'Productos - Listado'         => 'Count_Productos',
                'Productos - Tipos'           => 'Count_Productos',
                'Servicios - Categorias'      => 'Count_Servicios',
                'Servicios - Listado'         => 'Count_Servicios',
                'Maquinas - Listado'          => 'Count_Maquinas',
            ],
            'Gestión Documentos Mercantiles' => [
                'Buscar Documentos' => 'Count_DocumentosMercantiles',
                'Compras'           => 'Count_DocumentosMercantiles',
                'Ventas'            => 'Count_DocumentosMercantiles',
            ],
            'Externalización Servicios' => [
                'Clientes - Opciones Extras' => 'Count_Externalizacion',
            ],
        ];
        //Se recorren los permisos y se validan
        foreach ($menuCounters as $section => $names) {
            if (!empty($arrMenu[$section])) {
                foreach ($arrMenu[$section] as $asd) {
                    if (isset($names[$asd['Nombre']])) {
                        $MainViewData[$names[$asd['Nombre']]]++;
                    }
                }
            }
        }

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if($rowData['status'] && $arrCiudad['status'] && $arrComuna['status'] && $arrTemas['status'] && $arrConfigEmail['status'] && $arrConfigMap['status'] && $arrIAProvider['status'] && is_array($MainViewData)){
            /******************************************/
            //Datos enviados a la pagina
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
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                'Fnc_Codification'     => $this->Codification,
                'Fnc_WidgetsMaps'      => new UIWidgetsMaps(),
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
                'arrCiudad'        => $arrCiudad['data'],
                'arrComuna'        => $arrComuna['data'],
                'arrTemas'         => $arrTemas['data'],
                'arrConfigEmail'   => $arrConfigEmail['data'],
                'arrConfigMap'     => $arrConfigMap['data'],
                'arrIAProvider'    => $arrIAProvider['data'],
                'MainViewData'     => $MainViewData,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/sistemaOpciones-Resumen.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrCiudad,$arrComuna,$arrTemas,$arrConfigEmail,$arrConfigMap,$arrIAProvider]);
            //Muestra los errores
            $this->showError(1, $f3, $result);
        }
    }

    /******************************************************************************/
    //Resumen-Update
    public function ResumenUpdate($f3){
        /******************************************/
        //Se genera la query
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
                core_sistemas.KanbanTareasUsoTareas,
                core_sistemas.KanbanTareasAdminTabIndepend,
                core_sistemas.entidadesListadoVerCargas,
                core_sistemas.entidadesListadoVerContactos,
                core_sistemas.entidadesListadoVerDocumentos,
                core_sistemas.productosListadoVerDocumentos,
                core_sistemas.serviciosListadoVerDocumentos,
                core_sistemas.entidadesListadoUsoPassword,
                core_sistemas.gestionDocumentosUsoBodega,
                core_sistemas.entidadesListadoUsoPlanes,
                core_sistemas.entidadesListadoUsoUsuarios,
                core_sistemas.maquinasListadoVerDocumentos,
                core_sistemas.maquinasListadoComponentes,
                core_sistemas.maquinasListadoTelemetria,
                core_sistemas.maquinasListadoBackups,
                core_sistemas.sistemaModalSubtitle,
                core_sistemas.sistemaModalCloseBTN,
                core_sistemas.entidadesListadoUsoMaquinas,
                core_sistemas.maquinasListadoNotificaciones,
                core_sistemas.sistemaUsoWhatsapp,
                core_sistemas.Config_motorEmail,
                core_sistemas.Config_motorMap,
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
                core_sistemas.Social_X,
                core_sistemas.Social_Facebook,
                core_sistemas.Social_Instagram,
                core_sistemas.Social_Linkedin,

                core_ubicacion_ciudad.Nombre AS Ciudad,
                core_ubicacion_comunas.Nombre AS Comuna,
                core_temas.Nombre AS Tema,
                core_config_email.Nombre AS ConfigEmail,
                core_config_map.Nombre AS ConfigMap,
                core_ia_provider.Nombre AS IAProvider',
            'table'   => 'core_sistemas',
            'join'    => '
                LEFT JOIN core_ubicacion_ciudad  ON core_ubicacion_ciudad.idCiudad        = core_sistemas.Sistema_idCiudad
                LEFT JOIN core_ubicacion_comunas ON core_ubicacion_comunas.idComuna       = core_sistemas.Sistema_idComuna
                LEFT JOIN core_temas             ON core_temas.idTema                     = core_sistemas.Sistema_idTema
                LEFT JOIN core_config_email      ON core_config_email.idConfigEmail       = core_sistemas.Config_motorEmail
                LEFT JOIN core_config_map        ON core_config_map.idConfigMap           = core_sistemas.Config_motorMap
                LEFT JOIN core_ia_provider       ON core_ia_provider.idIAProvider         = core_sistemas.Config_IA_Provider',
            'where'   => 'core_sistemas.idSistema = "1"',
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
        if($rowData['status']){
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_WidgetsMaps'      => new UIWidgetsMaps(),
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/sistemaOpciones-Resumen-Update.php');
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
    //Editar por put (solo modificar datos)
    //Editar por post (modificar y subir archivos)
    public function Update($f3){
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            /******************************/
            //Se genera el chequeo
            $DataCheck = $this->dataCheck($_POST);

            /******************************/
            //Si hay datos
            if(isset($_POST['Sistema_Direccion'])&&$_POST['Sistema_Direccion']!=''){
                //Se instancia
                $fncLocation = new FunctionsLocation;
                //Se obtiene la direccion
                $Ubicacion =  $_POST['Sistema_Direccion'];
                //Si existe comuna
                if(isset($_POST['Sistema_idComuna'])&&$_POST['Sistema_idComuna']!=''){
                    //Se genera la query
                    $query = [
                        'data'    => 'Nombre',
                        'table'   => 'core_ubicacion_comunas',
                        'join'    => '',
                        'where'   => 'idComuna = "'.$_POST['Sistema_idComuna'].'"',
                        'group'   => '',
                        'having'  => '',
                        'order'   => ''
                    ];
                    //Ejecuto la query
                    $xParams = ['query' => $query];
                    $rowData = $this->Base_GetByID($xParams);
                    //Si hay resultados
                    if($rowData['status']){
                        $Ubicacion .= ', '.$rowData['data']['Nombre'];
                    }
                }
                //Si existe ciudad
                if(isset($_POST['Sistema_idCiudad'])&&$_POST['Sistema_idCiudad']!=''){
                    //Se genera la query
                    $query = [
                        'data'    => 'Nombre',
                        'table'   => 'core_ubicacion_ciudad',
                        'join'    => '',
                        'where'   => 'idCiudad = "'.$_POST['Sistema_idCiudad'].'"',
                        'group'   => '',
                        'having'  => '',
                        'order'   => ''
                    ];
                    //Ejecuto la query
                    $xParams = ['query' => $query];
                    $rowData = $this->Base_GetByID($xParams);
                    //Si hay resultados
                    if($rowData['status']){
                        $Ubicacion .= ', '.$rowData['data']['Nombre'];
                    }
                }
                //Pais
                $Ubicacion .= ', Chile';
                //Se hace la busqueda de lat y long por su direccion
                $result = $fncLocation->geocodeAddress($Ubicacion);
                //Si hay resultados se guarda
                if ($result) {
                    //Se guarda el ultimo dato
                    $_POST['Latitud']  = $result['lat'];
                    $_POST['Longitud'] = $result['lon'];
                }
            }

            /******************************/
            //Se genera la query
            $query = [
                'data'      => 'idSistema,Sistema_Nombre,Sistema_Email,Sistema_Rut,Sistema_idCiudad,Sistema_idComuna,Sistema_Direccion,Sistema_idTema,Sistema_NotiWhatsapp,Contacto_Nombre,Contacto_Fono1,Contacto_Fono2,Contacto_Fax,Contacto_Email,Contacto_Web,RepresentanteNombre,RepresentanteRut,RepresentanteFono,RepresentanteEmail,Config_API_GoogleMaps,Config_WhatsappToken,Config_WhatsappInstanceId,KanbanTareasUsoTareas,KanbanTareasAdminTabIndepend,entidadesListadoVerCargas,entidadesListadoVerContactos,entidadesListadoVerDocumentos,productosListadoVerDocumentos,serviciosListadoVerDocumentos,entidadesListadoUsoPassword,gestionDocumentosUsoBodega,entidadesListadoUsoPlanes,entidadesListadoUsoUsuarios,maquinasListadoVerDocumentos,maquinasListadoComponentes,maquinasListadoTelemetria,maquinasListadoBackups,sistemaModalSubtitle,sistemaModalCloseBTN,entidadesListadoUsoMaquinas,maquinasListadoNotificaciones,sistemaUsoWhatsapp,Config_motorEmail,Config_motorMap,Latitud,Longitud,Config_Principal_Meteo,Config_Principal_Radio,Config_Principal_Feed,Config_Principal_FeedURL,Config_IA_Provider,Config_IA_ApiKey,Config_IA_Model,Config_IA_Base_URL,Config_IA_Name,Config_IA_Tone,Config_IA_Uso,Config_IA_UsoCache,usuariosPermisosBodegas,usuariosPermisosMaquinas,idOpcionesGen_39,idOpcionesGen_40,Social_X, Social_Facebook, Social_Instagram, Social_Linkedin',
                'required'  => 'Sistema_Nombre',
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
            //Ejecuto la query
            $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
            $Response = $this->Base_update($xParams);

            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response['status']){
                /****************************************/
                //Actualizo los datos de la sesion
                $userSession = new userSession();
                $userSession->updateSession($_SESSION['DataInfo']['UserID'], $f3, 1);
                /****************************************/
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
    //Permite eliminar archivos
    public function delFiles(){
        //Verificacion metodo PUT
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            //Se parsean los datos
            parse_str(file_get_contents("php://input"),$dataPut);
            /******************************/
            //Se genera la query
            $query = [
                'files'       => 'Sistema_IMGLogo',
                'table'       => 'core_sistemas',
                'where'       => 'idSistema',
                'SubCarpeta'  => '',
                'Post'        => $dataPut
            ];
            //Ejecuto la query
            $xParams  = ['query' => $query];
            $Response = $this->Base_delFiles($xParams);
            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response['status']){
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
        //Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => 'Sistema_Email,Contacto_Email,RepresentanteEmail',
            'ValidarNumero'             => '',
            'ValidarEntero'             => '',
            'ValidarRut'                => 'Sistema_Rut,RepresentanteRut',
            'ValidarPatente'            => '',
            'ValidarFecha'              => '',
            'ValidarHora'               => '',
            'ValidarURL'                => 'Social_X,Social_Facebook,Social_Instagram,Social_Linkedin',
            'ValidarLargoMinimo'        => 'Sistema_Email,Contacto_Email,RepresentanteEmail,Sistema_Nombre,Sistema_Direccion,Contacto_Nombre,RepresentanteNombre',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'Sistema_Email,Contacto_Email,RepresentanteEmail,Sistema_Nombre,Sistema_Direccion,Contacto_Nombre,RepresentanteNombre',
            'ValidarLargoMaximoN'       => 255,
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
        //Devuelvo
        return $DataChecking;
    }


}
