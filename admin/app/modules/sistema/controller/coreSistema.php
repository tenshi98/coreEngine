<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class coreSistema extends ControllerBase {

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
        $this->controllerName = 'coreSistema';
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
                core_sistemas.Config_WhatsappToken,
                core_sistemas.Config_WhatsappInstanceId,

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
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen.php');
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
                core_sistemas.Config_WhatsappToken,
                core_sistemas.Config_WhatsappInstanceId,

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
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Update.php');
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
    public function Update(){
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            /******************************/
            //Se genera el chequeo
            $DataCheck = $this->dataCheck($_POST);

            /******************************/
            //Se genera la query
            $query = [
                'data'      => 'idSistema,Sistema_Nombre,Sistema_Email,Sistema_Rut,Sistema_idCiudad,Sistema_idComuna,Sistema_Direccion,Sistema_NotiWhatsapp,Contacto_Nombre,Contacto_Fono1,Contacto_Fono2,Contacto_Fax,Contacto_Email,Contacto_Web,RepresentanteNombre,RepresentanteRut,RepresentanteFono,RepresentanteEmail,Config_API_GoogleMaps,Config_WhatsappToken,Config_WhatsappInstanceId,KanbanTareasUsoTareas,KanbanTareasAdminTabIndepend,entidadesListadoVerCargas,entidadesListadoVerContactos,entidadesListadoVerDocumentos,productosListadoVerDocumentos,serviciosListadoVerDocumentos,entidadesListadoUsoPassword,gestionDocumentosUsoBodega,idOpcionesGen_10,idOpcionesGen_11,idOpcionesGen_12,idOpcionesGen_13,idOpcionesGen_14,idOpcionesGen_15,idOpcionesGen_16,idOpcionesGen_17,idOpcionesGen_18,idOpcionesGen_19,idOpcionesGen_20',
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
            'ValidarURL'                => '',
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