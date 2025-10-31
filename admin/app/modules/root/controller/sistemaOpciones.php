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
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
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
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');
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
                core_sistemas.idOpcionesGen_20,
                core_sistemas.idOpcionesGen_21,
                core_sistemas.idOpcionesGen_22,
                core_sistemas.idOpcionesGen_23,
                core_sistemas.idOpcionesGen_24,
                core_sistemas.idOpcionesGen_25,
                core_sistemas.idOpcionesGen_26,
                core_sistemas.idOpcionesGen_27,
                core_sistemas.idOpcionesGen_28,
                core_sistemas.idOpcionesGen_29,
                core_sistemas.idOpcionesGen_30,
                core_sistemas.idOpcionesGen_31,
                core_sistemas.idOpcionesGen_32,
                core_sistemas.idOpcionesGen_33,
                core_sistemas.idOpcionesGen_34,
                core_sistemas.idOpcionesGen_35,
                core_sistemas.idOpcionesGen_36,
                core_sistemas.idOpcionesGen_37,
                core_sistemas.idOpcionesGen_38,
                core_sistemas.idOpcionesGen_39,
                core_sistemas.idOpcionesGen_40,
                core_sistemas.Social_X,
                core_sistemas.Social_Facebook,
                core_sistemas.Social_Instagram,
                core_sistemas.Social_Linkedin,

                core_ubicacion_ciudad.Nombre AS Ciudad,
                core_ubicacion_comunas.Nombre AS Comuna,
                core_temas.Nombre AS Tema',
            'table'   => 'core_sistemas',
            'join'    => '
                LEFT JOIN core_ubicacion_ciudad  ON core_ubicacion_ciudad.idCiudad  = core_sistemas.Sistema_idCiudad
                LEFT JOIN core_ubicacion_comunas ON core_ubicacion_comunas.idComuna = core_sistemas.Sistema_idComuna
                LEFT JOIN core_temas             ON core_temas.idTema               = core_sistemas.Sistema_idTema',
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
            'Gestion Proyectos' => [
                'Informe Tareas'   => 'Count_GestionProyectos',
                'Tareas en Curso'  => 'Count_GestionProyectos',
            ],
            'Administracion' => [
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
            'Externalizacion Servicios' => [
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
        if ($rowData!==false) {
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'        => 'Configuracion Plataforma',
                'PageDescription'  => 'Configuracion Plataforma.',
                'PageAuthor'       => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'     => ConfigAPP::SOFTWARE['SoftwareName'],
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'       => $this->FormInputs,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                'Fnc_Codification'     => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData,
                'arrCiudad'        => $arrCiudad,
                'arrComuna'        => $arrComuna,
                'arrTemas'         => $arrTemas,
                'MainViewData'     => $MainViewData,
            ];

            /******************************************/
            //Se instancia la vista
            $view = new View;
            echo $view->render('../app/templates/user-header.php');                                           // Header
            echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/sistemaOpciones-Resumen.php');  // Vista
            echo $view->render('../app/templates/user-footer.php');                                           // Footer
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 1, $f3);
        }
    }

    /******************************************************************************/
    //Resumen-Update
    public function ResumenUpdate($f3){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

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
                core_sistemas.idOpcionesGen_20,
                core_sistemas.idOpcionesGen_21,
                core_sistemas.idOpcionesGen_22,
                core_sistemas.idOpcionesGen_23,
                core_sistemas.idOpcionesGen_24,
                core_sistemas.idOpcionesGen_25,
                core_sistemas.idOpcionesGen_26,
                core_sistemas.idOpcionesGen_27,
                core_sistemas.idOpcionesGen_28,
                core_sistemas.idOpcionesGen_29,
                core_sistemas.idOpcionesGen_30,
                core_sistemas.idOpcionesGen_31,
                core_sistemas.idOpcionesGen_32,
                core_sistemas.idOpcionesGen_33,
                core_sistemas.idOpcionesGen_34,
                core_sistemas.idOpcionesGen_35,
                core_sistemas.idOpcionesGen_36,
                core_sistemas.idOpcionesGen_37,
                core_sistemas.idOpcionesGen_38,
                core_sistemas.idOpcionesGen_39,
                core_sistemas.idOpcionesGen_40,
                core_sistemas.Social_X,
                core_sistemas.Social_Facebook,
                core_sistemas.Social_Instagram,
                core_sistemas.Social_Linkedin,

                core_ubicacion_ciudad.Nombre AS Ciudad,
                core_ubicacion_comunas.Nombre AS Comuna',
            'table'   => 'core_sistemas',
            'join'    => '
                LEFT JOIN core_ubicacion_ciudad  ON core_ubicacion_ciudad.idCiudad  = core_sistemas.Sistema_idCiudad
                LEFT JOIN core_ubicacion_comunas ON core_ubicacion_comunas.idComuna = core_sistemas.Sistema_idComuna',
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
        if ($rowData!==false) {
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData,
            ];

            /******************************************/
            //Se instancia la vista
            $view = new View;
            echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/sistemaOpciones-Resumen-Update.php'); // Vista
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
    //Editar por put (solo modificar datos)
    //Editar por post (modificar y subir archivos)
    public function Update($f3){
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            /******************************/
            //Se genera el chequeo
            $DataCheck = $this->dataCheck($_POST);

            /******************************/
            //Se genera la query
            $query = [
                'data'      => 'idSistema,Sistema_Nombre,Sistema_Email,Sistema_Rut,Sistema_idCiudad,Sistema_idComuna,Sistema_Direccion,Sistema_idTema,Sistema_NotiWhatsapp,Contacto_Nombre,Contacto_Fono1,Contacto_Fono2,Contacto_Fax,Contacto_Email,Contacto_Web,RepresentanteNombre,RepresentanteRut,RepresentanteFono,RepresentanteEmail,Config_API_GoogleMaps,Config_WhatsappToken,Config_WhatsappInstanceId,KanbanTareasUsoTareas,KanbanTareasAdminTabIndepend,entidadesListadoVerCargas,entidadesListadoVerContactos,entidadesListadoVerDocumentos,productosListadoVerDocumentos,serviciosListadoVerDocumentos,entidadesListadoUsoPassword,gestionDocumentosUsoBodega,entidadesListadoUsoPlanes,entidadesListadoUsoUsuarios,maquinasListadoVerDocumentos,maquinasListadoComponentes,maquinasListadoTelemetria,maquinasListadoBackups,sistemaModalSubtitle,sistemaModalCloseBTN,entidadesListadoUsoMaquinas,maquinasListadoNotificaciones,idOpcionesGen_20,idOpcionesGen_21,idOpcionesGen_22,idOpcionesGen_23,idOpcionesGen_24,idOpcionesGen_25,idOpcionesGen_26,idOpcionesGen_27,idOpcionesGen_28,idOpcionesGen_29,idOpcionesGen_30,idOpcionesGen_31,idOpcionesGen_32,idOpcionesGen_33,idOpcionesGen_34,idOpcionesGen_35,idOpcionesGen_36,idOpcionesGen_37,idOpcionesGen_38,idOpcionesGen_39,idOpcionesGen_40,Social_X, Social_Facebook, Social_Instagram, Social_Linkedin',
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
            if ($Response===true) {
                /****************************************/
                //Actualizo los datos de la sesion
                $userSession = new userSession();
                $userSession->updateSession($_SESSION['DataInfo']['UserID'], $f3, 1);
                /****************************************/
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
            'ValidarEmail'              => 'Sistema_Email,Contacto_Email,RepresentanteEmail',
            'ValidarNumero'             => '',
            'ValidarEntero'             => '',
            'ValidarRut'                => 'Sistema_Rut,RepresentanteRut',
            'ValidarPatente'            => '',
            'ValidarFecha'              => '',
            'ValidarHora'               => '',
            'ValidarURL'                => 'Social_X,Social_Facebook,Social_Instagram,Social_Linkedin',
            'ValidarLargoMinimo'        => 'Sistema_Nombre,Sistema_Direccion,Contacto_Nombre,RepresentanteNombre',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'Sistema_Nombre,Sistema_Direccion,Contacto_Nombre,RepresentanteNombre',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Sistema_Nombre,Sistema_Direccion,Contacto_Nombre,RepresentanteNombre',
            'ValidarEspaciosVacios'     => '',
            'ValidarMayusculas'         => '',
            'ValidarCoincidencias'      => '',
            'Post'                      => $POST,
        ];
        //Devuelvo
        return $DataChecking;
    }


}