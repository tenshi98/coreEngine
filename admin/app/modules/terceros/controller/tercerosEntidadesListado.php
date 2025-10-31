<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class tercerosEntidadesListado extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $DataDate;
    private $WidgetsCommon;
    private $DataNumbers;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'tercerosEntidadesListado';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->DataDate       = new FunctionsDataDate();
		$this->WidgetsCommon  = new UIWidgetsCommon();
		$this->DataNumbers    = new FunctionsDataNumbers();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //Listar Todo
    public function listAll($f3){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /*******************************************************************/
        //Se genera la query
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
                core_tipos_entidades.Nombre AS TipoEntidad',
            'table'   => 'entidades_listado',
            'join'    => '
                LEFT JOIN core_estados         ON core_estados.idEstado               = entidades_listado.idEstado
                LEFT JOIN entidades_sectores   ON entidades_sectores.idSector         = entidades_listado.idSector
                LEFT JOIN core_tipos_entidades ON core_tipos_entidades.idTipoEntidad  = entidades_listado.idTipoEntidad',
            'where'   => 'entidades_listado.idTipo=2',
            'group'   => '',
            'having'  => '',
            'order'   => 'entidades_listado.idEstado ASC, entidades_listado.ApellidoPat ASC, entidades_listado.Nombre ASC, entidades_listado.RazonSocial ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $arrList = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idEstado AS ID,Nombre',
            'table'   => 'core_estados',
            'join'    => '',
            'where'   => 'idEstado!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrEstado = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idSector AS ID,Nombre',
            'table'   => 'entidades_sectores',
            'join'    => '',
            'where'   => 'idSector!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams   = ['query' => $query];
        $arrSector = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idSexo AS ID,Nombre',
            'table'   => 'core_sexo',
            'join'    => '',
            'where'   => 'idSexo!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $arrSexo = $this->Base_GetList($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idTipoEntidad AS ID,Nombre',
            'table'   => 'core_tipos_entidades',
            'join'    => '',
            'where'   => 'idTipoEntidad!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams        = ['query' => $query];
        $arrTipoEntidad = $this->Base_GetList($xParams);

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
        if(is_array($arrList)){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'       => 'Listado Clientes',
                'PageDescription' => 'Listado Clientes.',
                'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
                'TableTitle'      => 'Listado Clientes',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_Codification'    => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList,
                'arrTipoEntidad'  => $arrTipoEntidad,
                'arrSexo'         => $arrSexo,
                'arrCiudad'       => $arrCiudad,
                'arrComuna'       => $arrComuna,
                'arrSector'       => $arrSector,
                'arrEstado'       => $arrEstado,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 1, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-List.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 1, $f3);
        }
    }

    /******************************************************************************/
    //List
    public function UpdateList($f3){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /*******************************************************************/
        //Variables
        $WhereData_int     = 'idEstado,idSector,idSexo,idTipo,idTipoEntidad,idCiudad,idComuna,FNacimiento';  //Datos búsqueda exacta
        $WhereData_string  = 'Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Direccion,Email';              //Datos búsqueda relativa
        $WhereData_between = '';                                                                             //Datos búsqueda Between
        $whereInt          = '';                                                                             //se crea cadena
        /******************************************/
        //agrego variable busqueda
        $whereInt = $this->searchWhere($whereInt, $WhereData_int, 'entidades_listado', 1);
        $whereInt = $this->searchWhere($whereInt, $WhereData_string, 'entidades_listado', 2);
        $whereInt = $this->searchWhere($whereInt, $WhereData_between, 'entidades_listado', 3);
        //Verifico si esta vacio
        $whereInt2 = $whereInt ? $whereInt . ' AND entidades_listado.idTipo=2' : 'entidades_listado.idTipo=2';

        /******************************/
        //Se genera la query
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
                core_tipos_entidades.Nombre AS TipoEntidad',
            'table'   => 'entidades_listado',
            'join'    => '
                LEFT JOIN core_estados         ON core_estados.idEstado               = entidades_listado.idEstado
                LEFT JOIN entidades_sectores   ON entidades_sectores.idSector         = entidades_listado.idSector
                LEFT JOIN core_tipos_entidades ON core_tipos_entidades.idTipoEntidad  = entidades_listado.idTipoEntidad',
            'where'   => $whereInt2,
            'group'   => '',
            'having'  => '',
            'order'   => 'entidades_listado.idEstado ASC, entidades_listado.ApellidoPat ASC, entidades_listado.Nombre ASC, entidades_listado.RazonSocial ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $arrList = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($arrList)){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*=========== Datos de la Pagina ===========*/
                'TableTitle'      => 'Listado Clientes',
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'    => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'arrList'         => $arrList,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-UpdateList.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //View
    public function View($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => '
                entidades_listado.Nombre,
                entidades_listado.ApellidoPat,
                entidades_listado.ApellidoMat,
                entidades_listado.RazonSocial,
                entidades_listado.Nick,
                entidades_listado.Direccion_img,
                entidades_listado.idTipoEntidad,

                core_sexo.Nombre AS Sexo,
                core_tipos_entidades.Nombre AS TipoEntidad',
            'table'   => 'entidades_listado',
            'join'    => '
                LEFT JOIN core_sexo                ON core_sexo.idSexo                    = entidades_listado.idSexo
                LEFT JOIN core_tipos_entidades     ON core_tipos_entidades.idTipoEntidad  = entidades_listado.idTipoEntidad',
            'where'   => 'entidades_listado.idEntidad = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        //Se verifica si se permite Administrar Tableros Independiente de las Tareas
        if($UserData["entidadesListadoUsoPlanes"]==2){
            //Se genera la query
            $query = [
                'data'    => '
                    terceros_entidades_listado_planes.idPlan,
                    terceros_entidades_listado_planes.Fecha,
                    terceros_entidades_listado_planes.Monto,
                    servicios_listado.Nombre AS Servicio,
                    core_estados.Nombre AS Estado,
                    core_estados.Color AS EstadoColor',
                'table'   => 'terceros_entidades_listado_planes',
                'join'    => '
                    LEFT JOIN servicios_listado  ON servicios_listado.idServicio  = terceros_entidades_listado_planes.idServicio
                    LEFT JOIN core_estados       ON core_estados.idEstado         = terceros_entidades_listado_planes.idEstado',
                'where'   => 'terceros_entidades_listado_planes.idEntidad = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
                'group'   => '',
                'having'  => '',
                'order'   => 'terceros_entidades_listado_planes.Fecha DESC, servicios_listado.Nombre ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            //Ejecuto la query
            $xParams   = ['query' => $query];
            $arrPlanes = $this->Base_GetList($xParams);
        //Si se permite junto con la creacion de tareas
        }else{
            $arrPlanes   = [];
        }

        /*******************************************************************/
        //Se verifica si se permite Administrar Tableros Independiente de las Tareas
        if($UserData["entidadesListadoUsoUsuarios"]==2){
            //Se genera la query
            $query = [
                'data'    => '
                    terceros_entidades_listado_usuarios.email,
                    terceros_entidades_listado_usuarios.Nombre,
                    terceros_entidades_listado_usuarios.Ultimo_acceso,
                    core_estados.Nombre AS Estado,
                    core_estados.Color AS EstadoColor',
                'table'   => 'terceros_entidades_listado_usuarios',
                'join'    => 'LEFT JOIN core_estados ON core_estados.idEstado = terceros_entidades_listado_usuarios.idEstado',
                'where'   => 'terceros_entidades_listado_usuarios.idEntidad = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
                'group'   => '',
                'having'  => '',
                'order'   => 'terceros_entidades_listado_usuarios.email ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            //Ejecuto la query
            $xParams     = ['query' => $query];
            $arrUsuarios = $this->Base_GetList($xParams);
        //Si se permite junto con la creacion de tareas
        }else{
            $arrUsuarios   = [];
        }

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
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData,
                'arrPlanes'        => $arrPlanes,
                'arrUsuarios'      => $arrUsuarios,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-View.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
    }

    /******************************************************************************/
    //Resumen
    public function Resumen($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => '
                entidades_listado.idEntidad,
                entidades_listado.Nombre,
                entidades_listado.ApellidoPat,
                entidades_listado.ApellidoMat,
                entidades_listado.RazonSocial,
                entidades_listado.Nick,
                entidades_listado.Direccion_img,
                entidades_listado.idTipoEntidad,

                core_sexo.Nombre AS Sexo,
                core_tipos_entidades.Nombre AS TipoEntidad',
            'table'   => 'entidades_listado',
            'join'    => '
                LEFT JOIN core_sexo                ON core_sexo.idSexo                    = entidades_listado.idSexo
                LEFT JOIN core_tipos_entidades     ON core_tipos_entidades.idTipoEntidad  = entidades_listado.idTipoEntidad',
            'where'   => 'entidades_listado.idEntidad = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
                /*=========== Datos de la Pagina ===========*/
                'PageTitle'        => 'Resumen Clientes',
                'PageDescription'  => 'Resumen Clientes.',
                'PageAuthor'       => ConfigAPP::SOFTWARE['SoftwareName'],
                'PageKeywords'     => ConfigAPP::SOFTWARE['SoftwareName'],
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'       => $this->FormInputs,
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_DataDate'         => $this->DataDate,
                'Fnc_Codification'     => $this->Codification,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData,
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
    public function ResumenUpdate($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => '
                entidades_listado.idEntidad,
                entidades_listado.Nombre,
                entidades_listado.ApellidoPat,
                entidades_listado.ApellidoMat,
                entidades_listado.RazonSocial,
                entidades_listado.Nick,
                entidades_listado.Direccion_img,

                core_sexo.Nombre AS Sexo,
                core_tipos_entidades.Nombre AS TipoEntidad',
            'table'   => 'entidades_listado',
            'join'    => '
                LEFT JOIN core_sexo                ON core_sexo.idSexo                    = entidades_listado.idSexo
                LEFT JOIN core_tipos_entidades     ON core_tipos_entidades.idTipoEntidad  = entidades_listado.idTipoEntidad',
            'where'   => 'entidades_listado.idEntidad = "'.$this->Codification->encryptDecrypt('decrypt', $params['id']).'"',
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
                'Fnc_WidgetsCommon'    => $this->WidgetsCommon,
                'Fnc_DataNumbers'      => $this->DataNumbers,
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
                'data'      => 'idEntidad,idEstado,idSector,idSexo,idTipo,idTipoEntidad,password,Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Rut,idCiudad,idComuna,Direccion,FNacimiento,Email,Fono1,Fono2,Web,Giro,RepLegalNombre,RepLegalRut,RepLegalEmail,RepLegalFono1,RepLegalFono2,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin,IP_Client,Agent_Transp,Ultimo_acceso',
                'required'  => 'idEstado,idTipo,idTipoEntidad',
                'unique'    => 'Rut,Email,Fono1,Fono2',
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
    //Se validan los datos
    private function dataCheck($POST){
        //Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => 'Email,RepLegalEmail',
            'ValidarNumero'             => 'Fono1,Fono2,RepLegalFono1,RepLegalFono2',
            'ValidarEntero'             => '',
            'ValidarRut'                => 'Rut,RepLegalRut',
            'ValidarPatente'            => '',
            'ValidarFecha'              => 'FNacimiento',
            'ValidarHora'               => '',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => 'Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Direccion,Giro,RepLegalNombre',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Direccion,Giro,RepLegalNombre',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Direccion,Giro,RepLegalNombre',
            'ValidarEspaciosVacios'     => 'Web,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin',
            'ValidarMayusculas'         => '',
            'ValidarCoincidencias'      => '',
            'Post'                      => $POST,
        ];
        //Devuelvo
        return $DataChecking;
    }

}