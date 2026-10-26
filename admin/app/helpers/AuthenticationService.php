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

    /*******************************************************************/
    // Constantes
    /*******************************************************************/
    /**
     * Hash bcrypt (cost-12) precalculado, usado únicamente como objetivo de
     * password_verify() cuando el usuario no existe o no tiene hash almacenado.
     * Su propósito es equalizar el tiempo de respuesta con el de un login real
     * (evita el oráculo de timing para enumeración de usuarios). No corresponde
     * a ninguna contraseña válida.
     */
    private const DUMMY_BCRYPT_HASH = '$2y$12$1/WhBjegRjTdn4wqTEUlve7XIBs9kHYY7RcdUk1biKQ2NG1IPxo/e';

    /**
     * Mensaje único de respuesta de la solicitud de recuperación (POST /forgot).
     * Es idéntico exista o no el email / esté activo o no el usuario
     * (anti-enumeración de cuentas).
     */
    private const RESET_TOKEN_MESSAGE = 'Si el email está registrado, recibirá un enlace para recuperar su contraseña';

    /**
     * Mensaje único para cualquier token inválido, usado o expirado en el
     * reset (POST /reset-password): no distingue causas.
     */
    private const RESET_INVALID_MESSAGE = 'El enlace de recuperación no es válido o ha expirado';

    /** Minutos de vigencia del enlace de recuperación. */
    private const RESET_EXPIRY_MINUTES = 60;

    /** Ventana de throttle por destinatario: no se reemite token/correo si el anterior tiene menos de esta edad. */
    private const RESET_THROTTLE_MINUTES = 5;

    /*******************************************************************/
    // Variables
    /*******************************************************************/
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

        /************************************/
        // Se cargan las clases
        $Server            = new FunctionsServerServer();
		$Client            = new FunctionsServerClient();
        $bruteForceService = new BruteForceService();

        /************************************/
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
     * Procesa una solicitud de recuperación de contraseña (flujo por token).
     *
     * Valida los datos del formulario (sin exigir password), aplica los mismos
     * controles de fuerza bruta/honeypot/país que el login y verifica que haya
     * un motor de correo configurado (antes de buscar al usuario: la respuesta
     * es uniforme). Si el usuario existe y está activo, genera un token de un
     * solo uso (sha256 persistido + expiración), envía el enlace por correo y
     * responde con un mensaje genérico idéntico exista o no el email
     * (anti-enumeración). La contraseña del usuario nunca cambia aquí.
     *
     * @param Base  $f3   Instancia de Fat-Free Framework.
     * @param array $POST Datos del formulario ($_POST): 'email'.
     *
     * @return array{code:int, message:string} Resultado de la operación.
     */
    public function recoverPassword($f3, $POST){

        /************************************/
        // Se cargan las clases
        $Server            = new FunctionsServerServer();
		$Client            = new FunctionsServerClient();
        $bruteForceService = new BruteForceService();

        /************************************/
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

        /********************* Motor de correo *********************/
        // Se verifica ANTES de buscar al usuario: si falla, la respuesta es
        // igual para cualquier email y no revela la existencia de cuentas
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

        /********************* Usuario *********************/
        // Se busca y valida el usuario (anti-enumeración: data null si no existe/inactivo)
        $loadDataRecover = $this->loadDataRecover($dataVars, $POST, $bruteForceService);

        // Solo si existe y está activo se emite token + correo
        if(!empty($loadDataRecover['data'])){
            // Se genera el token y se envia el enlace
            $Response = $this->sendResetLink($f3, $loadDataRecover['data'], $SystemData, $dataVars);
            // Los errores de sistema (token/correo/BD) se propagan
            if(($Response['code'] ?? 500) !== 200){
                // Retorno de datos
                return $Response;
            }
        }

        /********************* Respuesta única *********************/
        // Retorno de datos: idéntica exista o no el email
        return ['code' => 200, 'message' => self::RESET_TOKEN_MESSAGE];

    }

    /******************************************************************************/
    /**
     * Ejecuta el restablecimiento de contraseña con un token de un solo uso.
     *
     * Valida geolocalización y honeypot, verifica que el token exista, no haya
     * sido usado y no haya expirado (hash sha256 + hash_equals), aplica la
     * misma política de contraseña que el cambio en perfil y, si todo es
     * correcto, en este orden: persiste la contraseña nueva (bcrypt cost-12),
     * invalida el token y cierra todas las sesiones web del usuario.
     *
     * No usa BruteForceService (ver specs/security.md §2 del proyecto): la
     * protección es el rate limit guest + la entropía del token.
     *
     * @param Base  $f3   Instancia de Fat-Free Framework.
     * @param array $POST Datos del formulario: 'token', 'password', 'rePassword', honeypot 'nombre'.
     *
     * @return array{code:int, message:string} Resultado de la operación.
     */
    public function resetPassword($f3, $POST){

        /************************************/
        // Se cargan las clases
        $Server = new FunctionsServerServer();
        $Client = new FunctionsServerClient();

        /************************************/
        // Variables
        $dataVars = $this->loadVars($Server, $Client);

        /********************* Controles *********************/
        // Valido si dato es ingresado por una maquina (honeypot)
        if (!empty($POST['nombre'])){
            // Retorno de datos
            return ["code" => 400, "message" => "Nombre es obligatorio"];
        }

        // Se valida la ubicacion
        $dataVars['Error'] = $this->checkCountry($dataVars['Error']);

        // Mostrar errores si los hay
        if(!empty($dataVars['Error'])){
            // Retorno de datos
            return ['code' => $dataVars['Error']['code'], 'message' => $dataVars['Error']['message']];
        }

        /********************* Token *********************/
        // Formato estricto: 64 caracteres hexadecimales (tokenBin2Hex(64))
        $Token = $POST['token'] ?? '';
        if(!is_string($Token) || strlen($Token)!==64 || ctype_xdigit($Token)!==true){
            // Retorno de datos (mensaje único, sin distinguir causas)
            return ['code' => 400, 'message' => self::RESET_INVALID_MESSAGE];
        }

        // Se busca el token activo (sin usar y no expirado) por su hash sha256
        $TokenHash = hash('sha256', $Token);
        $rowToken  = $this->userRepository->findActiveResetToken($TokenHash);

        // Si no existe o no coincide (defensa en profundidad)
        if($rowToken===null || hash_equals((string)($rowToken['token_hash'] ?? ''), $TokenHash)!==true){
            // Retorno de datos (mensaje único, sin distinguir causas)
            return ['code' => 400, 'message' => self::RESET_INVALID_MESSAGE];
        }

        /********************* Política de contraseña *********************/
        // Password obligatoria
        $Password   = $POST['password'] ?? '';
        $RePassword = $POST['rePassword'] ?? '';
        if($Password==='' || $RePassword===''){
            // Retorno de datos
            return ['code' => 400, 'message' => 'Password es obligatorio'];
        }

        // Se verifica que la nueva password y la repeticion sean iguales
        // (mismo mensaje que el cambio de contraseña en perfil)
        if($Password!==$RePassword){
            // Retorno de datos
            return ['code' => 400, 'message' => 'Las contraseñas no coinciden'];
        }

        // Mismas reglas de formato que el perfil: largo 3..255 y sin espacios
        $checkData = new CheckData();
        $checkResult = $checkData->checkingData([
            'ValidarLargoMinimo'    => 'password,rePassword',
            'ValidarLargoMinimoN'   => 3,
            'ValidarLargoMaximo'    => 'password,rePassword',
            'ValidarLargoMaximoN'   => 255,
            'ValidarEspaciosVacios' => 'password,rePassword',
            'Post'                  => $POST,
        ]);
        if(($checkResult['status'] ?? false)!==true){
            // Retorno de datos (primer mensaje de validación)
            return ['code' => 400, 'message' => ($checkResult['error'][0]['message'] ?? 'Datos inválidos')];
        }

        /********************* Éxito: password → token → sesiones *********************/
        // 1) Se persiste la contraseña nueva (bcrypt cost-12, sin cambio forzado)
        $this->userRepository->updateUserPassword((int)$rowToken['idUsuario'], $Password);

        // 2) El token queda invalidado de forma permanente (uso único)
        $this->userRepository->markResetTokenUsed((int)$rowToken['idPasswordReset']);

        // 3) Se cierran todas las sesiones web activas del usuario
        $userSessionDataService = new UserSessionDataService();
        $userSessionDataService->disabledAllAccess((int)$rowToken['idUsuario']);

        /************************************/
        // Retorno de datos
        return ['code' => 200, 'message' => 'Su contraseña fue actualizada, inicie sesión'];

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

        /************************************/
        // Obtengo el id del usuario
        $UsuarioID = $f3->get('SESSION.DataInfo.UserID');

        /************************************/
        // Se cargan las clases
        $userSessionDataService = new UserSessionDataService();
        // Se desactivan todas las sesiones anteriores
        $userSessionDataService->disabledAllAccess($UsuarioID);

        /************************************/
        // Se cargan las clases
        $sessionService = new SessionService();
        // Se elimina cualquier dato existente de las sesiones
        $sessionService->destroy($f3);

        /************************************/
        // Se limpia el token CSRF de la sesión
        CsrfToken::clear($f3);

        /************************************/
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

        /************************************/
        // Variable vacia
        $arrData = [];

        /************************************/
        // Se elimina cualquier dato existente de las sesiones
        $sessionService->destroy($f3);

        /************************************/
        // Regenera el ID de sesión para evitar fijación de sesión
        session_regenerate_id(true);

        /************************************/
        // Se cargan las clases
        $Operations = new FunctionsDataOperations();

        /************************************/
        // Se generan Variables
        $arrData['TokenUser']    = $passwordService->generate();
        $arrData['TokenExpires'] = $Operations->sumarDias($Server->fechaActual(),1).' '.$Server->horaActual();

        /************************************/
        // Se cargan las clases
        $userSessionDataService = new UserSessionDataService();
        // Se desactivan todas las sesiones anteriores
        $userSessionDataService->disabledAllAccess($rowData['idUsuario']);
        // Se crea una nueva sesion
        $userSessionDataService->registerAccess($rowData['idUsuario'], $arrData['TokenUser'], $arrData['TokenExpires'], $Server, $Client);
        // Se actualiza la sesion actual del usuario
        $this->userRepository->updateUserAccess($rowData['idUsuario'], $Server, $Client);

        /************************************/
        // Se arman los datos de menu, permisos y niveles
        $arrData = array_merge($arrData, $this->buildSessionPayload($rowData, $Client));

        /************************************/
        // Se crean la sesion y la coockie
        $sessionService->create($f3, $arrData, true);

        /************************************/
        // Se regenera el token CSRF tras el login (evita fijación de token)
        CsrfToken::rotate($f3);

        /************************************/
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

        /************************************/
        // Se elimina cualquier dato existente de las sesiones
        $sessionService->destroy($f3);

        /************************************/
        // Regenera el ID de sesión para evitar fijación de sesión
        session_regenerate_id(true);

        /************************************/
        // Se cargan las clases
        $Operations = new FunctionsDataOperations();

        /************************************/
        // Variable vacia
        $arrData = [];
        // Se generan Variables
        $arrData['TokenUser']    = $passwordService->generate();
        $arrData['TokenExpires'] = $Operations->sumarDias($Server->fechaActual(),1).' '.$Server->horaActual();

        /************************************/
        // Se rota el token en la fila de acceso activa para que la base de datos
        // acompanie el nuevo valor de la coockie
        $userSessionDataService = new UserSessionDataService();
        $userSessionDataService->rotateActiveAccess($rowData['idUsuario'], $arrData['TokenUser'], $arrData['TokenExpires']);

        /************************************/
        // Se actualiza la sesion actual del usuario
        $this->userRepository->updateUserAccess($rowData['idUsuario'], $Server, $Client);

        /************************************/
        // Se arman los datos de menu, permisos y niveles
        $arrData = array_merge($arrData, $this->buildSessionPayload($rowData, $Client));

        /************************************/
        // Se crea la sesion y se renueva la coockie con el nuevo token
        $sessionService->create($f3, $arrData, true);

        /************************************/
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

        /************************************/
        // Se cargan las clases
        $permissionService = new PermissionService();
        // Se obtienen los datos
        $arrMenu     = $permissionService->getMenu($rowData['idTipoUsuario'], $rowData['idUsuario']);
        $arrPermisos = $permissionService->getRoutes($rowData['idTipoUsuario'], $rowData['idUsuario']);

        /************************************/
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
     * @return bool true si la sesión fue actualizada; false si no se pudo obtener el usuario.
     */
    public function updateSession($f3, $UsuarioID, $sessionService, $Client){

        /************************************/
        // Obtengo los datos del usuario
        $rowData = $this->userRepository->findById($UsuarioID);

        /************************************/
        // Si no hay resultados, se conserva la sesion actual
        if ($rowData['status'] !== true || !is_array($rowData['data'])) {
            // Retorno de datos
            return false;
        }

        /************************************/
        // Se elimina los datos del usuario y la empresa
        $sessionService->refresh($f3);

        /************************************/
        // Variable vacia
        $arrData = [];
        // Se arman los datos a guardar
        $arrData['DataInfo'] = $this->userRepository->getDataInfo($rowData['data'], $Client);

        /************************************/
        // Se actualiza la sesion
        $sessionService->create($f3, $arrData);

        /************************************/
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

        /************************************/
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
     * Busca al usuario por email, verifica la contraseña (contra el hash
     * almacenado, o contra un hash dummy si el usuario no existe / no tiene
     * hash, para equalizar el timing) y, solo si la contraseña es correcta,
     * revisa el estado. Todos los fallos de credencial devuelven el mismo
     * 401 "Credenciales incorrectas" (anti-enumeración); el 403 "Usuario
     * Inactivo" solo se emite tras verificar la contraseña correcta.
     * Cualquier fallo de credencial se registra como intento de fuerza bruta.
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

        /************************************/
        // Se cargan las clases
        $sessionService = new SessionService();
        // Se elimina cualquier dato existente de las sesiones
        $sessionService->destroy($f3);

        /************************************/
        // Validaciones
        $validateUser = $this->userRepository->findByEmail($POST['email']);

        /************************************/
        // Se cargan las clases
        $passwordService = new PasswordService();

        /************************************/
        // Si no hay resultados: se ejecuta password_verify contra hash dummy
        // para equalizar el tiempo de respuesta (evita oráculo de timing)
        if(empty($validateUser['data'])){
            $passwordService->verify(($POST['password'] ?? ''), self::DUMMY_BCRYPT_HASH);
            // Se guarda registro
            $bruteForceService->register($dataVars['Fecha'], $dataVars['Hora'], $dataVars['DateTime'], ($POST['email'] ?? ''), ($POST['password'] ?? ''), $dataVars['IP_Client'], $dataVars['Agent_Transp']);
            // Retorno de datos
            return ["code" => 401, "message" => "Credenciales incorrectas"];
        }

        /************************************/
        // Si no hay hash almacenado: se trata como verificación fallida
        // (mismo 401, sin revelar que la cuenta existe sin contraseña)
        if(!isset($validateUser['data']['password']) || $validateUser['data']['password']==''){
            $passwordService->verify(($POST['password'] ?? ''), self::DUMMY_BCRYPT_HASH);
            // Se guarda registro
            $bruteForceService->register($dataVars['Fecha'], $dataVars['Hora'], $dataVars['DateTime'], ($POST['email'] ?? ''), ($POST['password'] ?? ''), $dataVars['IP_Client'], $dataVars['Agent_Transp']);
            // Retorno de datos
            return ["code" => 401, "message" => "Credenciales incorrectas"];
        }

        /************************************/
        // Se verifica la contraseña (antes de revisar estado: evita filtrar
        // la existencia de la cuenta vía el código 403)
        $checkPassword = $passwordService->verify($POST['password'], $validateUser['data']['password']);
        if($checkPassword===false){
            // Se guarda registro
            $bruteForceService->register($dataVars['Fecha'], $dataVars['Hora'], $dataVars['DateTime'], ($POST['email'] ?? ''), ($POST['password'] ?? ''), $dataVars['IP_Client'], $dataVars['Agent_Transp']);
            // Retorno de datos
            return ["code" => 401, "message" => "Credenciales incorrectas"];
        }

        /************************************/
        // Contraseña correcta: ahora sí se puede revelar el estado
        // (solo quien ya conoce la contraseña llega aquí)
        if(isset($validateUser['data']['idEstado'])&&$validateUser['data']['idEstado']!=1){
            // Retorno de datos
            return ["code" => 403, "message" => "Usuario Inactivo"];
        }

        /************************************/
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
     * Anti-enumeración: no distingue por código ni mensaje si el email existe,
     * no existe o el usuario está inactivo — en los tres casos se devuelve
     * code=200 con data=null (el token solo se emite para usuarios existentes
     * y activos, lo que el cliente no puede observar). El registro de fuerza
     * bruta para email inexistente se conserva.
     *
     * @param array             $dataVars          Datos base (ver loadVars()).
     * @param array             $POST              Datos del formulario: 'email'.
     * @param BruteForceService $bruteForceService Servicio de control de fuerza bruta.
     *
     * @return array{code:int, data:?array} code=200 siempre; data=datos del usuario si existe y está activo, null si no.
     */
    private function loadDataRecover($dataVars, $POST, $bruteForceService){

        // Validaciones
        $validateUser = $this->userRepository->findByEmail($POST['email']);

        /************************************/
        // Si no hay resultados: se guarda registro y se responde sin revelar
        if(($validateUser['status'] ?? false)===false){
            // Se guarda registro
            $bruteForceService->register($dataVars['Fecha'], $dataVars['Hora'], $dataVars['DateTime'], ($POST['email'] ?? ''), ($POST['password'] ?? ''), $dataVars['IP_Client'], $dataVars['Agent_Transp']);
            // Retorno de datos
            return ["code" => 200, "data" => null];
        }

        /************************************/
        // Verifico el estado: usuario inactivo no recibe token
        // (misma respuesta que email inexistente: no revela la cuenta)
        if(isset($validateUser['data']['idEstado'])&&$validateUser['data']['idEstado']!=1){
            // Retorno de datos
            return ["code" => 200, "data" => null];
        }

        /************************************/
		// Retorno de datos
        return ["code" => 200, "data" => $validateUser['data']];

    }

    /******************************************************************************/
    /**
     * Genera un token de un solo uso, lo persiste como hash y envía por correo
     * el enlace de restablecimiento (nunca una contraseña).
     *
     * Flujo: genera el token (CSPRNG 256-bit), aplica throttle por destinatario
     * (5 min), invalida los tokens previos del usuario, persiste solo el hash
     * sha256 con expiración de 60 minutos y envía el correo. Si el envío falla,
     * invalida el token recién creado (la contraseña del usuario no cambia en
     * ningún punto de este flujo).
     *
     * @param Base  $f3        Instancia de Fat-Free Framework (usa 'BASE').
     * @param array $dataUser  Datos del usuario destino ('idUsuario', 'email', ...).
     * @param array $SystemData Configuración del sistema (ya validado con motor de correo).
     * @param array $dataVars  Datos base: IP_Client y Agent_Transp del solicitante.
     *
     * @return array{code:int, message:string} 200 con mensaje genérico, o error de sistema.
     */
    private function sendResetLink($f3, $dataUser, $SystemData, $dataVars){

        /************************************/
        // Se cargan las clases y se genera el token (CSPRNG)
        $Passwords = new FunctionsSecurityPasswords();
        $Token     = $Passwords->tokenBin2Hex(64);
        // Si fallo la generacion
        if(($Token['success'] ?? false)!==true){
            // Retorno de datos
            return ['code' => 500, 'message' => 'No se ha podido generar el enlace de recuperación'];
        }
        // Token en claro solo viaja en el correo; en BD va su hash sha256
        $TokenClear     = $Token['data'];
        $TokenHash      = hash('sha256', $TokenClear);
        $FechaCreacion  = date('Y-m-d H:i:s');
        $FechaExpiracion = date('Y-m-d H:i:s', time() + (self::RESET_EXPIRY_MINUTES * 60));

        /************************************/
        // Throttle por destinatario: no se reemite si hay un token activo reciente
        if($this->userRepository->hasRecentResetToken($dataUser['idUsuario'], self::RESET_THROTTLE_MINUTES)===true){
            // Retorno de datos (misma respuesta genérica)
            return ['code' => 200, 'message' => self::RESET_TOKEN_MESSAGE];
        }

        /************************************/
        // Se invalidan los tokens anteriores y se persiste el nuevo (solo hash)
        $this->userRepository->invalidateResetTokens($dataUser['idUsuario']);
        $idToken = $this->userRepository->createResetToken(
            $dataUser['idUsuario'],
            $TokenHash,
            $FechaCreacion,
            $FechaExpiracion,
            $dataVars['IP_Client'],
            $dataVars['Agent_Transp']
        );
        // Si hubo error al persistir
        if($idToken===false){
            // Retorno de datos
            return ['code' => 500, 'message' => 'No se ha podido generar el enlace de recuperación'];
        }

        /************************************/
        // Se arma el enlace y se envia el correo primero: si falla, se invalida
        // el token recien creado (el usuario conserva su contraseña actual)
        $ResetURL = rtrim((string)$f3->get('BASE'), '/').'/reset-password?token='.$TokenClear;
        try {

            /************************************/
            // Se cargan las clases
            $mailService = new MailService();
            // Se entrega la info para el envio del correo
            $Respuesta = $mailService->sendPasswordReset($f3, $dataUser, $SystemData, $ResetURL);

            // Si no es la respuesta esperada
            if ($Respuesta!==true) {
                // Se invalida el token y se retorna el error
                $this->userRepository->markResetTokenUsed($idToken);
                return ['code' => 500, 'message' => $Respuesta];
            }
        }catch (\Throwable $e) {
            // Se invalida el token y se retorna el error
            $this->userRepository->markResetTokenUsed($idToken);
            return ['code' => 500, 'message' => 'No se ha podido enviar el correo, contacte con el administrador'];
        }

        /************************************/
        // Retorno de datos
        return ['code' => 200, 'message' => self::RESET_TOKEN_MESSAGE];

    }

}
