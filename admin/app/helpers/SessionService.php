<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Maneja la persistencia de la sesión del usuario: escritura en SESSION de
 * F3, emisión/limpieza de la cookie "Sesion_tk" y validación de la sesión
 * activa (tanto por cookie contra base de datos como por comparación directa
 * contra los datos ya presentes en SESSION).
 */
class SessionService {

    /******************************************************************************/
    /**
     * Escribe en SESSION los datos de sesión recibidos y, opcionalmente,
     * emite la cookie "Sesion_tk" con el token de usuario.
     *
     * @param Base  $f3            Instancia de Fat-Free Framework.
     * @param array $arrData       Datos a guardar en SESSION: TokenUser, TokenExpires,
     *                             DataInfo, arrMenuNew, arrPermisos, arrLevel (todas opcionales).
     * @param bool  $createCoockie Si true, genera la cookie "Sesion_tk" con expiración de 1 día
     *                             (por defecto false).
     *
     * @return void
     */
    public function create($f3, $arrData, $createCoockie = false){

        /******************************/
        // Seteo las variables
        if(isset($arrData['TokenUser'])){    $f3->set('SESSION.TokenUser',    $arrData['TokenUser']);}     // Token del usuario
        if(isset($arrData['TokenExpires'])){ $f3->set('SESSION.TokenExpires', $arrData['TokenExpires']);}  // Token valido por 1 dia
        if(isset($arrData['DataInfo'])){     $f3->set('SESSION.DataInfo',     $arrData['DataInfo']);}      // Datos del usuario
        if(isset($arrData['arrMenuNew'])){   $f3->set('SESSION.arrMenu',      $arrData['arrMenuNew']);}    // Menu
        if(isset($arrData['arrPermisos'])){  $f3->set('SESSION.arrPermisos',  $arrData['arrPermisos']);}   // Rutas
        if(isset($arrData['arrLevel'])){     $f3->set('SESSION.arrLevel',     $arrData['arrLevel']);}      // Niveles de permisos

        /******************************/
        // Se genera la cookie con expiración de 1 día
        if($createCoockie){
            setcookie(
                'Sesion_tk',
                $f3->get('SESSION.TokenUser'),
                [
                    'expires'  => time() + 86400,
                    'path'     => '/',
                    'secure'   => true,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]
            );
        }
    }

    /******************************************************************************/
    /**
     * Valida la sesión cuando no hay datos de SESSION disponibles (p. ej.
     * tras reiniciar el navegador), apoyándose únicamente en la cookie
     * "Sesion_tk" contra la base de datos.
     *
     * Verifica que la cookie exista y sea válida en base de datos
     * (UserSessionDataService::checkAccess), que el usuario exista y esté
     * activo, y de ser así renueva/rota la sesión.
     *
     * @param Base       $f3          Instancia de Fat-Free Framework.
     * @param string|false $cookieToken Valor de la cookie "Sesion_tk" ($_COOKIE) o false si no existe.
     *
     * @return bool true si la sesión fue validada y renovada; false en cualquier otro caso.
     */
    public function check($f3, $cookieToken){

        /******************************/
        // Se verifica la existencia de la coockie
        if (empty($cookieToken)) { return false; }

        /******************************/
        // Se cargan las clases
        $userSessionDataService  = new UserSessionDataService();
        // Se valida el acceso en la base de datos, si aun tiene una sesion activa
        $checkAccessUser = $userSessionDataService->checkAccess($cookieToken);
        if ($checkAccessUser['status'] === false) {
            // Retorno de datos
            return false;
        }

        /******************************/
        // Se cargan las clases
        $userRepository = new UserRepository();
        // Se obtienen los datos del usuario
        $validateUser    = $userRepository->findById($checkAccessUser['data']['idUsuario']);
        if ($validateUser['status'] === false) {
            // Retorno de datos
            return false;
        }
        // Verifico si esta activo
        if(isset($validateUser['data']['idEstado'])&&$validateUser['data']['idEstado']!=1){
            // Retorno de datos
            return false;
        }

        /******************************/
        // Se cargan las clases
        $authenticationService = new AuthenticationService();
        $sessionService        = new SessionService();
        $passwordService       = new PasswordService();
        $Server                = new FunctionsServerServer();
		$Client                = new FunctionsServerClient();
        // Se refresca la sesion activa
        $activeSesion = $authenticationService->regenerateSession($f3, $validateUser['data'], $sessionService, $passwordService, $Server, $Client);
        // Retorno de datos
        return $activeSesion;

    }

    /******************************************************************************/
    /**
     * Valida la sesión cuando SESSION ya contiene TokenUser/TokenExpires
     * (ruta rápida, sin consultar base de datos).
     *
     * Compara el token recibido por cookie contra el guardado en SESSION,
     * verifica que no haya expirado y que la IP registrada coincida con la
     * IP actual del cliente.
     *
     * @param string $Token        Valor de la cookie "Sesion_tk" recibida.
     * @param string $TokenExpires Fecha/hora de expiración guardada en SESSION.TokenExpires.
     * @param string $TokenUser    Token guardado en SESSION.TokenUser.
     * @param array  $UserData     Datos de usuario en SESSION.DataInfo (usa 'UserIP').
     *
     * @return bool true si la sesión es válida; false en cualquier otro caso.
     */
    public function validate($Token, $TokenExpires, $TokenUser, $UserData){

        /******************************/
        // Se verifica la existencia de la coockie
        if (empty($Token)) { return false; }

        /******************************/
        // Se cargan las clases
		$ServerClient = new FunctionsServerClient();
        // Se verifica
        try {
            /******************************/
            // Verifico la expiracion
            if ($TokenExpires!='' && date('Y-m-d H:i:s') > $TokenExpires) {  return false;}
            // Se compara si el valor de la coockie es distinto al de la sesion en el servidor
            if ($Token != $TokenUser) { return false;}
            // Se compara la IP para evitar accesos no autorizados
            if (isset($UserData['UserIP'])&&$UserData['UserIP'] != $ServerClient->getClientIp()) { return false;}
            // Si no hay problemas se da como valido
            return true;
        } catch (PDOException $e) {
            return false;
        }

    }

    /******************************************************************************/
    /**
     * Elimina únicamente los datos de usuario (DataInfo) de SESSION,
     * conservando el token de sesión.
     *
     * @param Base $f3 Instancia de Fat-Free Framework.
     *
     * @return void
     */
    public function refresh($f3){

        /******************************/
        // Limpio las variables
        $f3->clear('SESSION.DataInfo');  //Datos del usuario

    }

    /******************************************************************************/
    /**
     * Elimina todos los datos de sesión de F3 (token, expiración, datos de
     * usuario, menú, permisos y niveles) y borra la cookie "Sesion_tk"
     * tanto en el cliente como en la superglobal $_COOKIE.
     *
     * @param Base $f3 Instancia de Fat-Free Framework.
     *
     * @return void
     */
    public function destroy($f3){

        /******************************/
        // Limpio las variables
        $f3->clear('SESSION.TokenUser');    //Token del usuario
        $f3->clear('SESSION.TokenExpires'); //token valido por 1 dia
        $f3->clear('SESSION.DataInfo');     //Datos del usuario
        $f3->clear('SESSION.arrMenu');      //Menu
        $f3->clear('SESSION.arrPermisos');  //Rutas
        $f3->clear('SESSION.arrLevel');     //Niveles de permisos

        /******************************/
        // Se limpian las cookies
        setcookie('Sesion_tk','',time()-1);
        // También es recomendable unset($_COOKIE['']) para borrar la cookie de la superglobal $_COOKIE
        unset($_COOKIE['Sesion_tk']);

    }


}
