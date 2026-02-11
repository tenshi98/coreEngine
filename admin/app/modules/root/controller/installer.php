<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class installer extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;
    private $FormInputs;

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
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //Paso 1: Vista - Bienvenida
    public function welcome($f3){

        /******************************************/
        //Validacion de instalacion
        $ValidInstall = true;

        /******************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Bienvenida',
            'PageDescription' => 'Bienvenida',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========   Funcionalidad   ===========*/
            'Fnc_FormInputs'      => $this->FormInputs,
            /*=========== Datos Consultados ===========*/
            'ValidInstall'   => $ValidInstall,
        ];

        //Se instancia la vista
        $view = new View;
        echo $view->render('../app/templates/guest-header.php');                                   // Header
        echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/installer-welcome.php'); // Vista
        echo $view->render('../app/templates/guest-footer.php');                                   // Footer

    }
    /******************************************************************************/
    //Paso 2: Vista - Credenciales MySQL
    public function credentials($f3){

        /******************************************/
        //Validacion de instalacion
        $ValidInstall = true;

        /******************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*===========   Funcionalidad   ===========*/
            'Fnc_FormInputs' => $this->FormInputs,
            /*=========== Datos Consultados ===========*/
            'ValidInstall'   => $ValidInstall,
        ];

        //Se instancia la vista
        $view = new View;
        echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/installer-credentials.php'); // Vista

    }
    /******************************************************************************/
    //Paso 3: Configuración de Base de Datos
    public function database($f3){

        $PostData = [
            'Host'     => $_POST['Host'],
            'Usuario'  => $_POST['Usuario'],
            'Password' => $_POST['Password'],
        ];


        /******************************************/
        //Validacion de instalacion
        $ValidInstall = true;

        /******************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*===========   Funcionalidad   ===========*/
            'Fnc_FormInputs'      => $this->FormInputs,
            /*=========== Datos Consultados ===========*/
            'PostData'       => $PostData,
            'ValidInstall'   => $ValidInstall,
        ];

        //Se instancia la vista
        $view = new View;
        echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/installer-database.php');    // Vista

    }
    /******************************************************************************/
    //Paso 4: Vista - Resumen
    public function summary($f3){

        $PostData = [
            'Host'     => $_POST['Host'],
            'Usuario'  => $_POST['Usuario'],
            'Password' => $_POST['Password'],
            'DBName'   => $_POST['DBName'],
        ];


        /******************************************/
        //Validacion de instalacion
        $ValidInstall = true;

        /******************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*===========   Funcionalidad   ===========*/
            'Fnc_FormInputs'      => $this->FormInputs,
            /*=========== Datos Consultados ===========*/
            'PostData'       => $PostData,
            'ValidInstall'   => $ValidInstall,
        ];

        //Se instancia la vista
        $view = new View;
        echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/installer-summary.php'); // Vista

    }
    /******************************************************************************/
    //Paso 5: Vista - Finalización
    public function finish($f3){

        $PostData = [
            'Host'     => $_POST['Host'],
            'Usuario'  => $_POST['Usuario'],
            'Password' => $_POST['Password'],
            'DBName'   => $_POST['DBName'],
        ];


        /******************************************/
        //Validacion de instalacion
        $ValidInstall = true;

        /******************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*===========   Funcionalidad   ===========*/
            'Fnc_FormInputs'      => $this->FormInputs,
            /*=========== Datos Consultados ===========*/
            'PostData'       => $PostData,
            'ValidInstall'   => $ValidInstall,
        ];

        //Se instancia la vista
        $view = new View;
        echo $view->render('../'.$this->returnRutaVista(__DIR__, 'app').'/installer-finish.php'); // Vista

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
        //Se genera la query
        $query = [
            'data'      => 'password,idTipoUsuario,idEstado,email,Nombre,Rut,fNacimiento,Fono,idCiudad,idComuna,Direccion,Ultimo_acceso,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin,IP_Client,Agent_Transp,idMenuPosicion',
            'required'  => 'password,idTipoUsuario,idEstado,email,Nombre,idMenuPosicion',
            'unique'    => 'email',
            'encode'    => 'password',
            'table'     => 'usuarios_listado',
            'Post'      => $_POST
        ];
        //Ejecuto la query
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        $Response = $this->Base_insert($xParams);

        /******************************/
        // Se asume que $Response contendrá un array de errores/datos, un ID numérico o algún otro valor.
        if (is_numeric($Response)) {
            // Si es un ID numérico, encripta y envía con código 200 (OK)
            $Data = $this->Codification->encryptDecrypt('encrypt', $Response);
            echo Response::sendData(200, $Data);
        } else {
            // Si es un array (errores o datos no esperados) o cualquier otra cosa no numérica,
            // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
            echo Response::sendData(500, $Response);
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
            //Se genera la query
            $query = [
                'data'      => 'idUsuario,password,idTipoUsuario,idEstado,email,Nombre,Rut,fNacimiento,Fono,idCiudad,idComuna,Direccion,Ultimo_acceso,Social_X,Social_Facebook,Social_Instagram,Social_Linkedin,IP_Client,Agent_Transp,idMenuPosicion',
                'required'  => 'password,idTipoUsuario,idEstado,email,Nombre,idMenuPosicion',
                'unique'    => 'email',
                'encode'    => 'password',
                'table'     => 'usuarios_listado',
                'where'     => 'idUsuario',
                'Post'      => $_POST,
                'files'     => [
                    [
                        'Identificador' => 'Direccion_img',
                        'SubCarpeta'    => '',
                        'NombreArchivo' => '',
                        'SufijoArchivo' => 'Perfil_',
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
    //Borrar dato y archivos
    public function Delete(){
        //Verificacion metodo PUT
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            //Se parsean los datos
            parse_str(file_get_contents("php://input"),$dataDelete);
            /******************************/
            //Se genera la query
            $query = [
                'files'       => 'Direccion_img',
                'table'       => 'usuarios_listado',
                'where'       => 'idUsuario',
                'SubCarpeta'  => '',
                'Post'        => $dataDelete
            ];
            //Ejecuto la query
            $xParams  = ['query' => $query];
            $Response = $this->Base_delete($xParams);
            /******************************/
            // Se asume que $Response contendrá un array de errores/datos, un true o algún otro valor.
            if ($Response===true) {
                /************************************************/
                //Listado de las tablas a eliminar los datos relacionados
                $arrTableDel  = array();
                $arrTableDel[] = ['files' => '', 'table' => 'usuarios_listado_observaciones'];

                /************************************************/
                //Verifico si existe
                if($arrTableDel){
                    //recorro
                    foreach ($arrTableDel as $tblDel) {
                        //Se genera la query
                        $query = ['files' => $tblDel['files'], 'table' => $tblDel['table'], 'where' => 'idUsuario', 'SubCarpeta' => '', 'Post' => $dataDelete];
                        //Ejecuto la query
                        $xParams = ['query' => $query];
                        $this->Base_delete($xParams);
                    }
                }

                /******************************/
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
                'files'       => 'Direccion_img',
                'table'       => 'usuarios_listado',
                'where'       => 'idUsuario',
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
            'ValidarEmail'              => 'email',
            'ValidarNumero'             => 'Fono',
            'ValidarEntero'             => '',
            'ValidarRut'                => 'Rut',
            'ValidarPatente'            => '',
            'ValidarFecha'              => 'fNacimiento',
            'ValidarHora'               => '',
            'ValidarURL'                => 'Social_X,Social_Facebook,Social_Instagram,Social_Linkedin',
            'ValidarLargoMinimo'        => 'email,Nombre,Direccion',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'email,Nombre,Direccion',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Nombre,Direccion',
            'ValidarEspaciosVacios'     => 'email',
            'ValidarMayusculas'         => 'email',
            'ValidarCoincidencias'      => '',
            'Post'                      => $POST,
        ];
        //Devuelvo
        return $DataChecking;
    }

}
