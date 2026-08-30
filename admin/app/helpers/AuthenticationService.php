<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Orquesta el flujo de autenticación de usuarios: login, recuperación de
 * contraseña, cierre de sesión y creación/renovación de la sesión (SESSION
 * de F3 + cookie "Sesion_tk"). Coordina UserRepository, SessionService,
 * PasswordService, BruteForceService, PermissionService y MailService.
 */
class AuthenticationService {

    /******************************************************************************/
    // Variables
    private $userRepository;
    private $CommonData;

    /******************************************************************************/
    /**
     * Instancia las dependencias internas del servicio.
     *
     * @return void
     */
    public function __construct(){
        /*================== Instancias =================*/
		$this->userRepository  = new UserRepository();
		$this->CommonData      = new FunctionsCommonData();
    }

    /******************************************************************************/
    /*                                  ACCIONES                                  */
    /******************************************************************************/
    /******************************************************************************/
    /**
     * Procesa un intento de inicio de sesión.
     *
     * Valida los datos del formulario (email/password), controla intentos de
     * fuerza bruta y honeypot, restringe el acceso por país (Web\Geo) y, si
     * todo es correcto, valida credenciales contra la base de datos y crea
     * la sesión.
     *
     * @param Base  $f3   Instancia de Fat-Free Framework (SESSION/cookies).
     * @param array $POST Datos del formulario de login ($_POST): 'email', 'password'.
     *
     * @return array{code:int, message:string} code=200 si el acceso es correcto;
     *                                          código de error (400/401/403/429/500) en caso contrario.
     */
    public function authenticate($f3, $POST){

        /******************************************/
        // Se cargan las clases
        $Server            = new FunctionsServerServer();
		$Client            = new FunctionsServerClient();
        $bruteForceService = new BruteForceService();

        /******************************************/
        // Variables
        $dataVars = $this->loadVars($Server, $Client);

        /********************* Accesos forzosos *********************/
        // Valido datos ingresados por el usuario
        $dataVars['Error'] = $this->checkAccessAttempt($dataVars, $POST, $bruteForceService, true);

        // Se valida la ubicacion
        $dataVars['Error'] = $this->checkCountry($dataVars['Error']);

        // Mostrar errores si los hay
        if(!empty($dataVars['Error'])){
            // Retorno de datos
            return ['code' => $dataVars['Error']['code'], 'message' => $dataVars['Error']['message']];
        }

        /********************* Usuario *********************/
        // Se busca y valida usuario que accede
        $dataVars['Error'] = $this->createDataLogin($f3, $dataVars, $POST, $Server, $Client, $bruteForceService);

        // Mostrar errores si los hay
        if(!empty($dataVars['Error'])){
            // Retorno de datos
            return ['code' => $dataVars['Error']['code'], 'message' => $dataVars['Error']['message']];
        }

        /********************* Si todo esta ok *********************/
        // Retorno de datos
        return ['code' => 200, 'message' => 'Acceso Correcto'];

    }

    /******************************************************************************/
    /**
     * Procesa una solicitud de recuperación de contraseña.
     *
     * Valida el email recibido (sin exigir password), aplica los mismos
     * controles de fuerza bruta/honeypot/país que el login, busca al usuario
     * y, si existe y está activo, genera y envía una nueva contraseña por
     * correo.
     *
     * @param Base  $f3   Instancia de Fat-Free Framework.
     * @param array $POST Datos del formulario ($_POST): 'email'.
     *
     * @return array{code:int, message:string} Resultado de la operación.
     */
    public function recoverPassword($f3, $POST){

        /******************************************/
        // Se cargan las clases
        $Server            = new FunctionsServerServer();
		$Client            = new FunctionsServerClient();
        $bruteForceService = new BruteForceService();

        /******************************************/
        // Variables
        $dataVars = $this->loadVars($Server, $Client);

        /********************* Accesos forzosos *********************/
        // Valido datos ingresados por el usuario
        $dataVars['Error'] = $this->checkAccessAttempt($dataVars, $POST, $bruteForceService, false);

        // Se valida la ubicacion
        $dataVars['Error'] = $this->checkCountry($dataVars['Error']);

        // Mostrar errores si los hay
        if(!empty($dataVars['Error'])){
            // Retorno de datos
            return ['code' => $dataVars['Error']['code'], 'message' => $dataVars['Error']['message']];
        }

        /********************* Usuario *********************/
        // Se busca y valida usuario que accede
        $loadDataLogin   = $this->loadDataRecover($dataVars, $POST, $bruteForceService);

        // Mostrar errores si los hay
        if($loadDataLogin['code']!=200){
            // Retorno de datos
            return ['code' => $loadDataLogin['code'], 'message' => $loadDataLogin['message']];
        }

        // Se envia correo
        return $this->sendNewPassword($f3, $loadDataLogin['data']);

    }

    /******************************************************************************/
    /**
     * Cierra la sesión del usuario actualmente autenticado.
     *
     * Desactiva en base de datos todos los accesos web del usuario y elimina
     * los datos de sesión de F3 junto con la cookie "Sesion_tk".
     *
     * @param Base $f3 Instancia de Fat-Free Framework.
     *
     * @return array{code:int, message:string} code=200 y mensaje de confirmación.
     */
    public function closeSession($f3){

        /******************************/
        // Obtengo el id del usuario
        $UsuarioID = $f3->get('SESSION.DataInfo.UserID');

        /******************************/
        // Se cargan las clases
        $userSessionDataService = new UserSessionDataService();
        // Se desactivan todas las sesiones anteriores
        $userSessionDataService->disabledAllAccess($UsuarioID);

        /******************************************/
        // Se cargan las clases
        $sessionService = new SessionService();
        // Se elimina cualquier dato existente de las sesiones
        $sessionService->destroy($f3);

        /******************************/
        // Retorno de datos
        return ['code' => 200, 'message' => 'Sesion cerrada correctamente'];

    }

    /******************************************************************************/
    /*                                 SESIONES                                   */
    /******************************************************************************/
    /******************************************************************************/
    /**
     * Crea una sesión nueva tras un login exitoso.
     *
     * Elimina cualquier sesión previa, regenera el ID de sesión (anti
     * session fixation), genera un nuevo token de sesión y su expiración,
     * desactiva los accesos web anteriores del usuario, registra el nuevo
     * acceso, actualiza el último ingreso y arma el payload de sesión (menú,
     * permisos, niveles y datos de usuario) antes de persistirlo en
     * SESSION + cookie.
     *
     * @param Base            $f3              Instancia de Fat-Free Framework.
     * @param array           $rowData         Fila del usuario autenticado (usuarios_listado).
     * @param SessionService  $sessionService  Servicio de manejo de SESSION/cookie.
     * @param PasswordService $passwordService Servicio de generación de tokens/contraseñas.
     * @param object          $Server          Helper de datos de servidor (fecha/hora actual).
     * @param object          $Client          Helper de datos de cliente (IP, user agent).
     *
     * @return array{code:int, message:string} code=200 y mensaje de confirmación.
     */
    public function createSession($f3, $rowData, $sessionService, $passwordService, $Server, $Client){

        /******************************************/
        // Variable vacia
        $arrData = [];

        /******************************************/
        // Se elimina cualquier dato existente de las sesiones
        $sessionService->destroy($f3);

        /******************************************/
        // Regenera el ID de sesión para evitar fijación de sesión
        session_regenerate_id(true);

        /******************************/
        // Se cargan las clases
        $Operations = new FunctionsDataOperations();

        /******************************/
        // Se generan Variables
        $arrData['TokenUser']    = $passwordService->generate();
        $arrData['TokenExpires'] = $Operations->sumarDias($Server->fechaActual(),1).' '.$Server->horaActual();

        /******************************/
        // Se cargan las clases
        $userSessionDataService = new UserSessionDataService();
        // Se desactivan todas las sesiones anteriores
        $userSessionDataService->disabledAllAccess($rowData['idUsuario']);
        // Se crea una nueva sesion
        $userSessionDataService->registerAccess($rowData['idUsuario'], $arrData['TokenUser'], $arrData['TokenExpires'], $Server, $Client);
        // Se actualiza la sesion actual del usuario
        $this->userRepository->updateUserAccess($rowData['idUsuario'], $Server, $Client);

        /******************************/
        // Se arman los datos de menu, permisos y niveles
        $arrData = array_merge($arrData, $this->buildSessionPayload($rowData, $Client));

        /******************************/
        // Se crean la sesion y la coockie
        $sessionService->create($f3, $arrData, true);

        /******************************/
        // Retorno de datos
        return ['code' => 200, 'message' => 'Acceso Correcto'];

    }

    /******************************************************************************/
    /**
     * Renueva la sesión de un usuario ya autenticado (ruta de validación por
     * cookie, sin credenciales), rotando el token y reconstruyendo el
     * payload de sesión sin volver a registrar un nuevo acceso en base de
     * datos.
     *
     * @param Base            $f3              Instancia de Fat-Free Framework.
     * @param array           $rowData         Fila del usuario (usuarios_listado).
     * @param SessionService  $sessionService  Servicio de manejo de SESSION/cookie.
     * @param PasswordService $passwordService Servicio de generación de tokens.
     * @param object          $Server          Helper de datos de servidor.
     * @param object          $Client          Helper de datos de cliente.
     *
     * @return bool true si la sesión fue renovada.
     */
    public function regenerateSession($f3, $rowData, $sessionService, $passwordService, $Server, $Client){

        /******************************************/
        // Se elimina cualquier dato existente de las sesiones
        $sessionService->destroy($f3);

        /******************************************/
        // Regenera el ID de sesión para evitar fijación de sesión
        session_regenerate_id(true);

        /******************************/
        // Se cargan las clases
        $Operations = new FunctionsDataOperations();

        /******************************/
        // Variable vacia
        $arrData = [];
        // Se generan Variables
        $arrData['TokenUser']    = $passwordService->generate();
        $arrData['TokenExpires'] = $Operations->sumarDias($Server->fechaActual(),1).' '.$Server->horaActual();

        /******************************/
        // Se actualiza la sesion actual del usuario
        $this->userRepository->updateUserAccess($rowData['idUsuario'], $Server, $Client);

        /******************************/
        // Se arman los datos de menu, permisos y niveles
        $arrData = array_merge($arrData, $this->buildSessionPayload($rowData, $Client));

        /******************************/
        // Se crea la sesion y se renueva la coockie con el nuevo token
        $sessionService->create($f3, $arrData, true);

        /******************************/
        // Retorno de datos
        return true;

    }

    /******************************************************************************/
    /**
     * Construye la porción del payload de sesión relativa a permisos/menú.
     *
     * Obtiene el menú y las rutas permitidas según el tipo de usuario,
     * agrupa el menú por categoría y calcula los niveles de acceso, junto
     * con los datos generales del usuario (DataInfo).
     *
     * @param array  $rowData Fila del usuario (usuarios_listado).
     * @param object $Client  Helper de datos de cliente (IP).
     *
     * @return array{DataInfo:array, arrMenuNew:array, arrPermisos:array, arrLevel:array}
     */
    private function buildSessionPayload($rowData, $Client){

        /******************************/
        // Se cargan las clases
        $permissionService = new PermissionService();
        // Se obtienen los datos
        $arrMenu     = $permissionService->getMenu($rowData['idTipoUsuario'], $rowData['idUsuario']);
        $arrPermisos = $permissionService->getRoutes($rowData['idTipoUsuario'], $rowData['idUsuario']);

        /******************************/
        // Retorno de datos
        return [
            'DataInfo'    => $this->userRepository->getDataInfo($rowData, $Client),
            'arrMenuNew'  => $this->CommonData->agruparPorClave($arrMenu['data'], 'PermisosCat'),
            'arrPermisos' => $arrPermisos['data'],
            'arrLevel'    => $permissionService->getLevels($rowData['idTipoUsuario'], $arrMenu),
        ];

    }

    /******************************************************************************/
    /**
     * Refresca únicamente los datos de usuario (DataInfo) dentro de la
     * sesión actual, sin tocar el token ni regenerar el ID de sesión.
     *
     * @param Base           $f3             Instancia de Fat-Free Framework.
     * @param int            $UsuarioID      Id del usuario a recargar.
     * @param SessionService $sessionService Servicio de manejo de SESSION.
     * @param object         $Client         Helper de datos de cliente.
     *
     * @return bool true una vez actualizada la sesión.
     */
    public function updateSession($f3, $UsuarioID, $sessionService, $Client){

        /******************************************/
        // Se elimina los datos del usuario y la empresa
        $sessionService->refresh($f3);

        /******************************/
        // Obtengo los datos del usuario
        $rowData = $this->userRepository->findById($UsuarioID);

        /******************************/
        // Variable vacia
        $arrData = [];
        // Se arman los datos a guardar
        $arrData['DataInfo'] = $this->userRepository->getDataInfo($rowData, $Client);

        /******************************/
        // Se actualiza la sesion
        $sessionService->create($f3, $arrData);

        /******************************/
        // Retorno de datos
        return true;

    }

    /******************************************************************************/
    /*                                  COMUNES                                   */
    /******************************************************************************/
    /******************************************************************************/
    /**
     * Recolecta los datos comunes de fecha/hora/servidor/cliente utilizados
     * en los distintos flujos de autenticación.
     *
     * @param object $Server Helper de datos de servidor (fecha/hora actual).
     * @param object $Client Helper de datos de cliente (IP, user agent).
     *
     * @return array Datos base: Error, Fecha, Hora, DateTime, Email, Password, IP_Client, Agent_Transp.
     */
    private function loadVars($Server, $Client){

        // Variables
        $dataVars                  = [];
        $dataVars['Error']         = [];
        $dataVars['Fecha']         = $Server->fechaActual();
        $dataVars['Hora']          = $Server->horaActual();
        $dataVars['DateTime']      = time();
        $dataVars['Email']         = '';
        $dataVars['Password']      = '';
        $dataVars['IP_Client']     = $Client->getClientIp();
        $dataVars['Agent_Transp']  = $Client->getBrowser();

        // Retorno de datos
        return $dataVars;

    }

    /******************************************************************************/
    /**
     * Valida los datos de un intento de acceso (login o recuperación) contra
     * fuerza bruta y honeypot.
     *
     * Exige email siempre; exige password solo si $requierePassword es true.
     * Si el campo honeypot 'nombre' viene con datos, se asume un bot y se
     * registra el intento como fuerza bruta. Luego consulta a
     * BruteForceService si el email/IP ya superó el límite de intentos.
     *
     * @param array              $dataVars          Datos base (ver loadVars()).
     * @param array              $POST              Datos del formulario ($_POST).
     * @param BruteForceService  $bruteForceService Servicio de control de fuerza bruta.
     * @param bool               $requierePassword  Si true, exige 'password' en $POST (por defecto true).
     *
     * @return array{code:int, message:string}|array Array de error, o [] si no hay errores.
     */
    private function checkAccessAttempt($dataVars, $POST, $bruteForceService, $requierePassword = true){

        /******************************/
        // Validaciones
        if (empty($POST['email'])){
            // Retorno de datos
            return ["code" => 400, "message" => "Email es obligatorio"];
        }else{
            $Email = $POST['email'];
        }
        $Password = '';
        if ($requierePassword){
            if (empty($POST['password'])){
                // Retorno de datos
                return ["code" => 400, "message" => "Password es obligatorio"];
            }else{
                $Password = $POST['password'];
            }
        }
        // Valido si dato es ingresado por una maquina
        if (!empty($POST['nombre'])){
            // Se guarda registro
            $bruteForceService->register($dataVars['Fecha'], $dataVars['Hora'], $dataVars['DateTime'], $Email, $Password, $dataVars['IP_Client'], $dataVars['Agent_Transp']);
            // Retorno de datos
            return ["code" => 400, "message" => "Nombre es obligatorio"];
        }
        // Se verifica si se trata de hacer fuerza bruta en el ingreso
        if ($bruteForceService->check($Email, $dataVars['IP_Client']) === true) {
            // Retorno de datos
            return ["code" => 429, "message" => "Demasiados intentos fallidos, usuario bloqueado por 2 horas"];
        }

        // Retorno de datos vacio
        return [];

    }

    /******************************************************************************/
    /**
     * Restringe el acceso a ubicaciones fuera de Chile según geolocalización IP.
     *
     * @param array $errors Errores previos a propagar si no aplica la restricción.
     *
     * @return array Los mismos $errors si el país es CL o no se pudo determinar;
     *               error 400 en caso contrario.
     */
    private function checkCountry($errors){
        // Validaciones
        $loc = Web\Geo::instance()->location();
        // Si no esta en chile
        if (isset($loc['country_code']) && $loc['country_code']!='CL') {
            // Retorno de datos
            return ["code" => 400, "message" => "No se puede acceder desde su ubicación"];
        }
        // Retorno de datos originales
        return $errors;

    }

    /******************************************************************************/
    /*                             FLUJO PARA EL LOGIN                            */
    /******************************************************************************/
    /******************************************************************************/
    /**
     * Ejecuta la validación de credenciales y creación de sesión del flujo
     * de login.
     *
     * Busca al usuario por email, valida su estado, verifica que tenga
     * contraseña almacenada, compara la contraseña recibida contra el hash
     * guardado y, si todo es correcto, crea la sesión. Cualquier fallo de
     * credenciales se registra como intento de fuerza bruta.
     *
     * @param Base              $f3                Instancia de Fat-Free Framework.
     * @param array             $dataVars          Datos base (ver loadVars()).
     * @param array             $POST              Datos del formulario: 'email', 'password'.
     * @param object            $Server            Helper de datos de servidor.
     * @param object            $Client            Helper de datos de cliente.
     * @param BruteForceService $bruteForceService Servicio de control de fuerza bruta.
     *
     * @return array{code:int, message:string}|array Array de error, o [] si el login fue exitoso.
     */
    private function createDataLogin($f3, $dataVars, $POST, $Server, $Client, $bruteForceService){

        /******************************/
        // Se cargan las clases
        $sessionService = new SessionService();
        // Se elimina cualquier dato existente de las sesiones
        $sessionService->destroy($f3);

        /******************************/
        // Validaciones
        $validateUser = $this->userRepository->findByEmail($POST['email']);

        /******************************/
        // Si no hay resultados
        if(empty($validateUser['data'])){
            // Se guarda registro
            $bruteForceService->register($dataVars['Fecha'], $dataVars['Hora'], $dataVars['DateTime'], ($POST['email'] ?? ''), ($POST['password'] ?? ''), $dataVars['IP_Client'], $dataVars['Agent_Transp']);
            // Retorno de datos
            return ["code" => 400, "message" => "Credenciales incorrectas"];
        }

        /******************************/
        // Verifico el estado
        if(isset($validateUser['data']['idEstado'])&&$validateUser['data']['idEstado']!=1){
            // Retorno de datos
            return ["code" => 403, "message" => "Usuario Inactivo"];
        }
        if(!isset($validateUser['data']['password']) || $validateUser['data']['password']==''){
            // Retorno de datos
            return ["code" => 500, "message" => "No hay password Almacenada"];
        }

        /******************************/
        // Se cargan las clases
        $passwordService = new PasswordService();
        // Se verifica la contraseña
        $checkPassword = $passwordService->verify($POST['password'], $validateUser['data']['password']);
        if($checkPassword===false){
            // Se guarda registro
            $bruteForceService->register($dataVars['Fecha'], $dataVars['Hora'], $dataVars['DateTime'], ($POST['email'] ?? ''), ($POST['password'] ?? ''), $dataVars['IP_Client'], $dataVars['Agent_Transp']);
            // Retorno de datos
            return ["code" => 401, "message" => "Credenciales incorrectas"];
        }

        /******************************/
        // Se cargan los datos de la sesion
        $newSesion = $this->createSession($f3, $validateUser['data'], $sessionService, $passwordService, $Server, $Client);
        if ($newSesion === false) {
            // Retorno de datos
            return ["code" => 500, "message" => "No se puede iniciar Sesion"];
        }

        // Retorno de datos vacio
        return [];

    }

    /******************************************************************************/
    /**
     * Busca y valida al usuario para el flujo de recuperación de contraseña.
     *
     * @param array             $dataVars          Datos base (ver loadVars()).
     * @param array             $POST              Datos del formulario: 'email'.
     * @param BruteForceService $bruteForceService Servicio de control de fuerza bruta.
     *
     * @return array{code:int, message:string, data?:array} code=200 y datos del usuario si existe y está activo.
     */
    private function loadDataRecover($dataVars, $POST, $bruteForceService){

        // Validaciones
        $validateUser = $this->userRepository->findByEmail($POST['email']);

        /******************************/
        // Si no hay resultados
        if($validateUser['status']===false){
            // Se guarda registro
            $bruteForceService->register($dataVars['Fecha'], $dataVars['Hora'], $dataVars['DateTime'], ($POST['email'] ?? ''), ($POST['password'] ?? ''), $dataVars['IP_Client'], $dataVars['Agent_Transp']);
            // Retorno de datos
            return ["code" => 400, "message" => "Credenciales incorrectas"];
        }

        /******************************/
        // Verifico el estado
        if(isset($validateUser['data']['idEstado'])&&$validateUser['data']['idEstado']!=1){
            // Retorno de datos
            return ["code" => 403, "message" => "Usuario Inactivo"];
        }

        /******************************/
		// Retorno de datos
        return ["code" => 200, "data" => $validateUser['data']];

    }

    /******************************************************************************/
    /**
     * Genera una nueva contraseña y la envía por correo al usuario.
     *
     * Verifica primero que exista un motor de correo configurado. Genera la
     * nueva contraseña e intenta enviarla; solo si el envío es exitoso se
     * persiste la nueva contraseña en base de datos (evita dejar al usuario
     * con una contraseña que nunca recibió).
     *
     * @param Base  $f3       Instancia de Fat-Free Framework.
     * @param array $dataUser Datos del usuario destino ('idUsuario', 'email', ...).
     *
     * @return array{code:int, message:string} Resultado de la operación.
     */
    private function sendNewPassword($f3, $dataUser){

        /******************************/
        // Se cargan los datos de la plataforma
        $SystemData = $this->userRepository->getSystemData();
        // Si no hay resultados
        if($SystemData['status']===false){
            // Retorno de datos
            return ["code" => 500, "message" => "Error en Obtencion de Datos"];
        }
        // Si no hay un motor de correos configurado
        if($SystemData['data']['Config_motorEmail']==0){
            // Retorno de datos
            return ["code" => 500, "message" => "No hay un motor de correos configurado"];
        }

        /******************************/
        // Se cargan las clases
        $Passwords = new FunctionsSecurityPasswords();
        // Se genera la nueva contraseña
        $NewPasswords  = $Passwords->generarPassword(10,'alfanumerico');

        /******************************/
        // Se envia el correo primero: si falla, no se persiste la nueva contraseña
        try {

            /******************************/
            // Se cargan las clases
            $mailService = new MailService();
            // Se entrega la info para el envio del correo
            $Respuesta = $mailService->sendPasswordReset($f3, $dataUser, $SystemData, $NewPasswords);

            // Si no es la respuesta esperada
            if ($Respuesta!==true) {
                // Retorno de datos
                return ['code' => 500, 'message' => $Respuesta];
            }
        }catch (\Throwable $e) {
            // Retorno de datos
            return ['code' => 500, 'message' => 'No se ha podido enviar el correo, contacte con el administrador'];
        }

        /******************************/
        // El correo se envio correctamente, se actualiza la nueva contraseña
        $Response = $this->userRepository->updateUserPassword($dataUser['idUsuario'], $NewPasswords);

        /******************************/
        // Si hay respuesta positiva.
        if (!$Response){
            // Retorno de datos
            return ['code' => 500, 'message' => 'No se ha podido cambiar la contraseña'];
        }

        // Retorno de datos
        return ['code' => 200, 'message' => 'La nueva contraseña fue enviada a tu correo'];

    }

}
