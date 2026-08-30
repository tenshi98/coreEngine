<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class entidadesListadoContactos extends ControllerBase {

    /******************************************************************************/
    // Variables
    private $controllerName;
    private $FormInputs;
    private $Codification;
    private $WidgetsCommon;
    private $DataNumbers;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'entidadesListado';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
		$this->WidgetsCommon  = new UIWidgetsCommon();
		$this->DataNumbers    = new FunctionsDataNumbers();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //Crear nuevo
    public function New($f3, $params){
        /******************************************/
        // Se genera la query
        $query = [
            'data'    => 'idEntidad',
            'table'   => 'entidades_listado',
            'join'    => '',
            'where'   => 'idEntidad = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /******************************/
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
        // Ejecuto la query
        $xParams   = ['query' => $query];
        $arrCiudad = $this->Base_GetList($xParams);

        /******************************/
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
        // Ejecuto la query
        $xParams   = ['query' => $query];
        $arrComuna = $this->Base_GetList($xParams);

        /******************************/
        // Se genera la query
        $query = [
            'data'    => 'idTipoContacto AS ID,Nombre',
            'table'   => 'core_tipos_contactos',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams         = ['query' => $query];
        $arrTipoContacto = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrCiudad['status'] && $arrComuna['status'] && $arrTipoContacto['status']){
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'       => $this->FormInputs,
                'Fnc_Codification'     => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'rowData'          => $rowData['data'],
                'arrCiudad'        => $arrCiudad['data'],
                'arrComuna'        => $arrComuna['data'],
                'arrTipoContacto'  => $arrTipoContacto['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Contactos-formNew.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrCiudad,$arrComuna,$arrTipoContacto]);
            //Muestra los errores
            $this->showError(2, $f3, $result);
        }
    }
    /******************************************************************************/
    //List
    public function UpdateList($f3, $params){
        /*******************************************************************/
        // Se genera la query
        $query = [
            'data'    => 'idContacto,Nombre,ApellidoPat,ApellidoMat,Email,Fono1,Fono2',
            'table'   => 'entidades_listado_contactos',
            'join'    => '',
            'where'   => 'idEntidad = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => 'ApellidoPat ASC, ApellidoMat ASC, Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams      = ['query' => $query];
        $arrContactos = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($arrContactos['status']){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_Codification'     => $this->Codification,
                'Fnc_DataNumbers'      => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'arrContactos' => $arrContactos['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Contactos-UpdateList.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$arrContactos]);
            //Muestra los errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    //View
    public function View($f3, $params){
        /******************************************/
        // Se genera la query
        $query = [
            'data'    => '
                entidades_listado_contactos.Nombre,
                entidades_listado_contactos.ApellidoPat,
                entidades_listado_contactos.ApellidoMat,
                entidades_listado_contactos.Email,
                entidades_listado_contactos.Rut,
                entidades_listado_contactos.Fono1,
                entidades_listado_contactos.Fono2,
                entidades_listado_contactos.Direccion,
                entidades_listado_contactos.Cargo,

                core_ubicacion_ciudad.Nombre AS Ciudad,
                core_ubicacion_comunas.Nombre AS Comuna,
                core_tipos_contactos.Nombre AS TipoContacto,
                core_estados.Nombre AS Estado,
                core_estados.Color AS EstadoColor',
            'table'   => 'entidades_listado_contactos',
            'join'    => '
                LEFT JOIN core_ubicacion_ciudad   ON core_ubicacion_ciudad.idCiudad       = entidades_listado_contactos.idCiudad
                LEFT JOIN core_ubicacion_comunas  ON core_ubicacion_comunas.idComuna      = entidades_listado_contactos.idComuna
                LEFT JOIN core_tipos_contactos    ON core_tipos_contactos.idTipoContacto  = entidades_listado_contactos.idTipoContacto
                LEFT JOIN core_estados            ON core_estados.idEstado                = entidades_listado_contactos.idEstado',
            'where'   => 'entidades_listado_contactos.idContacto = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status']){
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_WidgetsCommon' => $this->WidgetsCommon,
                'Fnc_DataNumbers'   => $this->DataNumbers,
                /*=========== Datos Consultados ===========*/
                'rowData'       => $rowData['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Contactos-View.php');
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
    //Edit
    public function GetID($f3, $params){
        /******************************************/
        // Se genera la query
        $query = [
            'data'    => 'idContacto,idEntidad,Nombre,ApellidoPat,ApellidoMat,Email,Rut,Fono1,Fono2,idCiudad,idComuna,Direccion,idTipoContacto,Cargo,idEstado',
            'table'   => 'entidades_listado_contactos',
            'join'    => '',
            'where'   => 'idContacto = ?',
            'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /******************************/
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
        // Ejecuto la query
        $xParams   = ['query' => $query];
        $arrCiudad = $this->Base_GetList($xParams);

        /******************************/
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
        // Ejecuto la query
        $xParams   = ['query' => $query];
        $arrComuna = $this->Base_GetList($xParams);

        /******************************/
        // Se genera la query
        $query = [
            'data'    => 'idTipoContacto AS ID,Nombre',
            'table'   => 'core_tipos_contactos',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
        $xParams         = ['query' => $query];
        $arrTipoContacto = $this->Base_GetList($xParams);

        /*******************************************************************/
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
        // Ejecuto la query
        $xParams   = ['query' => $query];
        $arrEstado = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrCiudad['status'] && $arrComuna['status'] && $arrTipoContacto['status'] && $arrEstado['status']){
            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'    => $this->FormInputs,
                'Fnc_Codification'  => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'rowData'         => $rowData['data'],
                'arrCiudad'       => $arrCiudad['data'],
                'arrComuna'       => $arrComuna['data'],
                'arrTipoContacto' => $arrTipoContacto['data'],
                'arrEstado'       => $arrEstado['data'],
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Contactos-formEdit.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrCiudad,$arrComuna,$arrTipoContacto,$arrEstado]);
            //Muestra los errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    /*                                  DATOS                                     */
    /******************************************************************************/
    /******************************************************************************/
    //Crear
    public function Insert(){

        /******************************/
        //Se genera el chequeo
        $DataCheck = $this->dataCheck($_POST);

        /******************************/
        // Se genera la query
        $query = [
            'data'      => 'idEntidad,Nombre,ApellidoPat,ApellidoMat,Email,Rut,Fono1,Fono2,idCiudad,idComuna,Direccion,idTipoContacto,Cargo,idEstado',
            'required'  => 'idEntidad,Nombre,ApellidoPat',
            'unique'    => '',
            'encode'    => '',
            'table'     => 'entidades_listado_contactos',
            'Post'      => $_POST
        ];
        // Ejecuto la query
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        $Response = $this->Base_insert($xParams);

        /******************************/
        // Se asume que $Response contendrá un array de errores/datos, un ID numérico o algún otro valor.
        if ($Response['status']){
            // Si es un ID numérico, se envía con código 200 (OK)
            Response::success($Response['data']);
        } else {
            // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
            // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
            Response::error('Error al operar con la Base de Datos', 500, $Response['error']);
        }
    }

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
            // Se genera la query
            $query = [
                'data'      => 'idContacto,idEntidad,Nombre,ApellidoPat,ApellidoMat,Email,Rut,Fono1,Fono2,idCiudad,idComuna,Direccion,idTipoContacto,Cargo,idEstado',
                'required'  => 'idEntidad,Nombre,ApellidoPat',
                'unique'    => '',
                'encode'    => '',
                'table'     => 'entidades_listado_contactos',
                'where'     => 'idContacto',
                'Post'      => $_POST
            ];
            // Ejecuto la query
            $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
            $Response = $this->Base_update($xParams);

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
    //Borrar dato y archivos
    public function Delete(){
        //Verificacion metodo PUT
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            //Se parsean los datos
            parse_str(file_get_contents("php://input"),$dataDelete);
            /******************************/
            // Se genera la query
            $query = [
                'files'       => '',
                'table'       => 'entidades_listado_contactos',
                'where'       => 'idContacto',
                'SubCarpeta'  => '',
                'Post'        => $dataDelete
            ];
            // Ejecuto la query
            $xParams  = ['query' => $query];
            $Response = $this->Base_delete($xParams);

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
        // Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => 'Email',
            'ValidarNumero'             => 'idEntidad,idCiudad,idComuna,idTipoContacto,idEstado,Fono1,Fono2',
            'ValidarEntero'             => 'idEntidad,idCiudad,idComuna,idTipoContacto,idEstado',
            'ValidarRut'                => 'Rut',
            'ValidarPatente'            => '',
            'ValidarFecha'              => '',
            'ValidarHora'               => '',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => 'Email,Nombre,ApellidoPat,ApellidoMat,Direccion,Cargo',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'Email,Nombre,ApellidoPat,ApellidoMat,Direccion,Cargo',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Nombre,ApellidoPat,ApellidoMat,Direccion,Cargo',
            'ValidarEspaciosVacios'     => 'Email',
            'ValidarMayusculas'         => 'Email',
            'ValidarCoincidencias'      => '',
            'ValidarDominioEmail'       => 'Email',
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
