<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class installer extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $FormInputs;
    private $DataValidations;
    private $FunctionsServer;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn       = '';                 //Vacia por ser instalada aqui
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
		$this->FormInputs         = new UIFormInputs();
		$this->DataValidations    = new FunctionsDataValidations();
		$this->FunctionsServer    = new FunctionsServerServer();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //Paso 1: Vista - Bienvenida
    public function welcome($f3){

        /******************************************/
        //Validacion de instalacion
        try {
            // Obtener la configuración
            $config = ConfigData::MySQL_1;

            // Verificar que la clave exista
            if (array_key_exists('HOSTNAME', $config)) {
                $ValidInstall = false; //No permite instalar
            } else {
                $ValidInstall = true; //Permite instalar
            }

        } catch (\Throwable $th) {
            $ValidInstall = false; //No permite instalar
        }

                $ValidInstall = true; //Permite instalar
        /******************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Bienvenida',
            'PageDescription' => 'Bienvenida',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========   Funcionalidad   ===========*/
            'Fnc_FormInputs' => $this->FormInputs,
            /*=========== Datos Consultados ===========*/
            'ValidInstall'   => $ValidInstall,
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(0, $this->returnRutaVista(__DIR__, 'app').'/installer-welcome.php');

    }

    /******************************************************************************/
    //Paso 2: Vista - Credenciales MySQL
    public function credentials($f3){

        /******************************************/
        //Validacion de instalacion
        try {
            // Obtener la configuración
            $config = ConfigData::MySQL_1;

            // Verificar que la clave exista
            if (array_key_exists('HOSTNAME', $config)) {
                $ValidInstall = false; //No permite instalar
            } else {
                $ValidInstall = true; //Permite instalar
            }

        } catch (\Throwable $th) {
            $ValidInstall = false; //No permite instalar
        }

        /******************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*===========   Funcionalidad   ===========*/
            'Fnc_FormInputs' => $this->FormInputs,
            /*=========== Datos Consultados ===========*/
            'ValidInstall'   => $ValidInstall,
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/installer-credentials.php');

    }

    /******************************************************************************/
    //Paso 3: Configuración de Base de Datos
    public function database($f3){

        /******************************************/
        //Validacion de instalacion
        try {
            // Obtener la configuración
            $config = ConfigData::MySQL_1;

            // Verificar que la clave exista
            if (array_key_exists('HOSTNAME', $config)) {
                $ValidInstall = false; //No permite instalar
            } else {
                $ValidInstall = true; //Permite instalar
            }

        } catch (\Throwable $th) {
            $ValidInstall = false; //No permite instalar
        }

        /******************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*===========   Funcionalidad   ===========*/
            'Fnc_FormInputs' => $this->FormInputs,
            /*=========== Datos Consultados ===========*/
            'ValidInstall'   => $ValidInstall,
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/installer-database.php');

    }

    /******************************************************************************/
    //Paso 4: Vista - Resumen
    public function summary($f3){

        /******************************************/
        //Validacion de instalacion
        try {
            // Obtener la configuración
            $config = ConfigData::MySQL_1;

            // Verificar que la clave exista
            if (array_key_exists('HOSTNAME', $config)) {
                $ValidInstall = false; //No permite instalar
            } else {
                $ValidInstall = true; //Permite instalar
            }

        } catch (\Throwable $th) {
            $ValidInstall = false; //No permite instalar
        }

        /******************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*===========   Funcionalidad   ===========*/
            'Fnc_FormInputs' => $this->FormInputs,
            /*=========== Datos Consultados ===========*/
            'ValidInstall'   => $ValidInstall,
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/installer-summary.php');

    }

    /******************************************************************************/
    //Paso 5: Vista - Finalización
    public function finish($f3){

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

        /******************************************/
        //Se instancia la vista
        $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/installer-finish.php');

    }



    /******************************************************************************/
    /*                                  DATOS                                     */
    /******************************************************************************/
    /******************************************************************************/
    //Editar por put (solo modificar datos)
    public function credentialsPost(){
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            /******************************/
            //Variables
            $Response = '';
            //Valido datos ingresados por el usuario
            if (empty($_POST['Host'])){           $Response.= '<br> - Host es obligatorio';                   }else{$Host            = trim($_POST['Host']);}
            if (empty($_POST['Admin_Usuario'])){  $Response.= '<br> - Usuario Administrador es obligatorio';  }else{$Admin_Usuario   = trim($_POST['Admin_Usuario']);}
            if (empty($_POST['Admin_Password'])){ $Response.= '<br> - Password Administrador es obligatorio'; }else{$Admin_Password  = $_POST['Admin_Password'];}
            if (empty($_POST['Prod_Usuario'])){   $Response.= '<br> - Usuario Producción es obligatorio';     }else{$Prod_Usuario    = trim($_POST['Prod_Usuario']);}
            if (empty($_POST['Prod_Password'])){  $Response.= '<br> - Password Producción es obligatorio';    }else{$Prod_Password   = $_POST['Prod_Password'];}
            //Datos opcionales
            if (empty($_POST['Port'])){     $Port     = 3306;       }else{$Port     = trim($_POST['Port']);}
            if (empty($_POST['Charset'])){  $Charset  = 'utf8mb4';  }else{$Charset  = trim($_POST['Charset']);}

            /******************************/
            //Si no hay errores
            if(empty($Response)){
                // Validar credenciales
                $result_Admin = $this->DataValidations->validateCredentials($Host, $Admin_Usuario, $Admin_Password, $Port, $Charset, 'admin');
                $result_Prod  = $this->DataValidations->validateCredentials($Host, $Prod_Usuario,  $Prod_Password,  $Port, $Charset, 'basic');
                // Se valida resultado
                if ($result_Admin['success'] && $result_Prod['success']) {
                    // Guardar credenciales en sesión (no guardamos la conexión porque PDO no es serializable)
                    $_SESSION['db_Host']           = $Host;
                    $_SESSION['db_Admin_Usuario']  = $Admin_Usuario;
                    $_SESSION['db_Admin_Password'] = $Admin_Password;
                    $_SESSION['db_Prod_Usuario']   = $Prod_Usuario;
                    $_SESSION['db_Prod_Password']  = $Prod_Password;
                    $_SESSION['db_Port']           = $Port;
                    $_SESSION['db_Charset']        = $Charset;
                    //devuelve true
                    $Response = true;
                } else {
                    $Response.= $result_Admin['message'] ? $result_Admin['message'] : null;
                    $Response.= $result_Prod['message']  ? $result_Prod['message']  : null;
                }
            }

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
    //Editar por put (solo modificar datos)
    public function databasePost(){
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            /******************************/
            //Variables
            $Response = '';
            //Valido datos ingresados por el usuario
            if (empty($_POST['Host'])){           $Response.= '<br> - Host es obligatorio';                   }else{$Host            = trim($_POST['Host']);}
            if (empty($_POST['Admin_Usuario'])){  $Response.= '<br> - Usuario Administrador es obligatorio';  }else{$Admin_Usuario   = trim($_POST['Admin_Usuario']);}
            if (empty($_POST['Admin_Password'])){ $Response.= '<br> - Password Administrador es obligatorio'; }else{$Admin_Password  = $_POST['Admin_Password'];}
            if (empty($_POST['Prod_Usuario'])){   $Response.= '<br> - Usuario Producción es obligatorio';     }else{$Prod_Usuario    = trim($_POST['Prod_Usuario']);}
            if (empty($_POST['Prod_Password'])){  $Response.= '<br> - Password Producción es obligatorio';    }else{$Prod_Password   = $_POST['Prod_Password'];}
            if (empty($_POST['Port'])){           $Response.= '<br> - Port es obligatorio';                   }else{$Port            = trim($_POST['Port']);}
            if (empty($_POST['Charset'])){        $Response.= '<br> - Charset es obligatorio';                }else{$Charset         = trim($_POST['Charset']);}
            if (empty($_POST['DBName'])){         $Response.= '<br> - Nombre Base de Datos es obligatorio';   }else{$DBName          = trim($_POST['DBName']);}

            /******************************/
            //Si no hay errores
            if(empty($Response)){
                // Validar credenciales
                $result = $this->DataValidations->validateDatabase($Host, $Admin_Usuario, $Admin_Password, $Port, $Charset, $DBName);
                // Se valida resultado
                if ($result['success']) {
                    // Guardar credenciales en sesión (no guardamos la conexión porque PDO no es serializable)
                    $_SESSION['db_Host']           = $Host;
                    $_SESSION['db_Admin_Usuario']  = $Admin_Usuario;
                    $_SESSION['db_Admin_Password'] = $Admin_Password;
                    $_SESSION['db_Prod_Usuario']   = $Prod_Usuario;
                    $_SESSION['db_Prod_Password']  = $Prod_Password;
                    $_SESSION['db_Port']           = $Port;
                    $_SESSION['db_Charset']        = $Charset;
                    $_SESSION['db_DBName']         = $DBName;
                    //devuelve true
                    $Response = true;
                } else {
                    $Response.= $result['message'];
                }
            }

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
    //Editar por put (solo modificar datos)
    public function summaryPost(){
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            /******************************/
            //Variables
            $Response = '';
            //Valido datos ingresados por el usuario
            if (empty($_POST['Host'])){           $Response.= '<br> - Host es obligatorio';                   }else{$Host            = trim($_POST['Host']);}
            if (empty($_POST['Admin_Usuario'])){  $Response.= '<br> - Usuario Administrador es obligatorio';  }else{$Admin_Usuario   = trim($_POST['Admin_Usuario']);}
            if (empty($_POST['Admin_Password'])){ $Response.= '<br> - Password Administrador es obligatorio'; }else{$Admin_Password  = $_POST['Admin_Password'];}
            if (empty($_POST['Prod_Usuario'])){   $Response.= '<br> - Usuario Producción es obligatorio';     }else{$Prod_Usuario    = trim($_POST['Prod_Usuario']);}
            if (empty($_POST['Prod_Password'])){  $Response.= '<br> - Password Producción es obligatorio';    }else{$Prod_Password   = $_POST['Prod_Password'];}
            if (empty($_POST['Port'])){           $Response.= '<br> - Port es obligatorio';                   }else{$Port            = trim($_POST['Port']);}
            if (empty($_POST['Charset'])){        $Response.= '<br> - Charset es obligatorio';                }else{$Charset         = trim($_POST['Charset']);}
            if (empty($_POST['DBName'])){         $Response.= '<br> - Nombre Base de Datos es obligatorio';   }else{$DBName          = trim($_POST['DBName']);}

            /******************************/
            //Si no hay errores
            if(empty($Response)){

                /***************   Creacion de la Base de datos Vacia   ***************/
                //Se generan los datos de conexión
                $query = [
                    'dbName' => $DBName,
                ];
                $newBDConn = [
                    'HOSTNAME' => $Host,
                    'USERNAME' => $Admin_Usuario,
                    'PASSWORD' => $Admin_Password,
                    'PORT'     => $Port,
                    'CHARSET'  => $Charset,
                ];
                //Se genera el array con datos
                $xParams  = ['query' => $query, 'newBDConn' => $newBDConn];
                //Ejecuto la query
                $CreateDB = $this->Base_createDatabase($xParams);

                /******************************/
                //Si se ejecuta correctamente
                if ($CreateDB===true) {

                    /***************   Se ejecuta un archivo SQL para llenar de datos la BD recien creada   ***************/
                    //Se generan los datos
                    $rutaController = substr(__DIR__, strpos(__DIR__, 'app')); //se obtiene la ruta del controlador
                    $rutaVista      = str_replace("controller", "files", $rutaController);   //se obtiene la ruta a la vista
                    $filepath       = '../'.$rutaVista.'/install.sql';

                    //Se generan los datos de conexión
                    $BD_Data = [
                        'HOSTNAME' => $Host,
                        'USERNAME' => $Admin_Usuario,
                        'PASSWORD' => $Admin_Password,
                        'PORT'     => $Port,
                        'CHARSET'  => $Charset,
                        'DATABASE' => $DBName,
                    ];
                    //Se genera conexion a la base de datos utilizando la conexion normal
                    $newBDConn = Database::getSQLConnection($BD_Data);
                    //Se genera el array con datos
                    $xParams  = ['filepath' => $filepath, 'newBDConn' => $newBDConn];
                    //Ejecuto la query
                    $ExecuteFileSQL = $this->Base_executeFile($xParams);
                    //Si se ejecuta correctamente
                    if ($ExecuteFileSQL===true) {

                        //Obtener la ruta del directorio
                        $rootPath   = __DIR__;
                        $rootPath   = $this->FunctionsServer->getParentPath($rootPath, 4);
                        $folderPath = $rootPath . '/app/config/';

                        //Se verifica el permiso de la carpeta
                        $isWritableDirectory = $this->FunctionsServer->isWritableDirectory($folderPath, 0775);

                        //Si se permite la escritura
                        if ($isWritableDirectory['success']) {
                            //Se agrega el nombre del archivo
                            $envPath = $folderPath . 'ConfigDataTest.php';
                            //Se generan los datos a ingresar en el archivo
                            $variables = [
                                'MySQL_ADMIN' => [
                                    'HOSTNAME' => $Host,
                                    'USERNAME' => $Admin_Usuario,
                                    'PASSWORD' => $Admin_Password,
                                    'DATABASE' => $DBName,
                                    'CHARSET'  => $Charset,
                                    'PORT'     => $Port,
                                ],
                                'MySQL_1' => [
                                    'HOSTNAME' => $Host,
                                    'USERNAME' => $Prod_Usuario,
                                    'PASSWORD' => $Prod_Password,
                                    'DATABASE' => $DBName,
                                    'CHARSET'  => $Charset,
                                    'PORT'     => $Port,
                                ],
                            ];

                            //Se crea el archivo
                            $iswritePHPFile = $this->FunctionsServer->writeConfigClassFile($envPath, $variables);
                            //Si se crea correctamente
                            if ($iswritePHPFile['success']) {
                                // Se cambian los permisos de la carpeta y el archivo
                                try {
                                    chmod($folderPath, 0700);
                                    chmod($envPath, 0600);
                                } catch (\Throwable $th) {
                                    //throw $th;
                                }
                                //Eliminar los datos de sesion utilizados para la instalacion
                                //Vaciar todas las variables de sesión
                                $_SESSION = [];

                                //Responder true para redirigir a la pantalla de finalizacion
                                $Response = true;

                            } else {
                                $Response = $iswritePHPFile['message'];
                            }
                        } else {
                            $Response = $isWritableDirectory['message'];
                        }
                    } else {
                        $Response = $ExecuteFileSQL;
                    }
                } else {
                    $Response = $CreateDB;
                }
            }

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



}
