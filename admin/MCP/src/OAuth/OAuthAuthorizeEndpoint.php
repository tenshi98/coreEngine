<?php
defined('MCP_ROOT') || exit;
/**
 * Endpoint de autorizacion del AS: GET/POST <issuer>/authorize con pantalla propia (pegar un token manual o denegar).
 *
 * El GET valida cliente y redirect_uri (fase A: errores en pagina propia, SIN redirigir, B-4) y el resto de
 * parametros (fase B: error a la URI efectiva, construida desde la registrada). El POST lo decide la persona:
 * CSRF por rid, Origin, limites y verificacion del token manual con OAuthCredentials::verifyToken (respuesta
 * generica sin distinguir la causa). La sesion es la propia del AS (OAuthStateStore), nunca la del backoffice.
 * Nunca se registran tokens ni el valor pegado; los fallos de BD responden con una pagina 500 sin detalle.
 *
 * @see projects/mcp-oauth/specs/technical.md §13.0, §13.2
 */
class OAuthAuthorizeEndpoint
{
    /** @var OAuthClientRepository Repositorio de clientes */
    private OAuthClientRepository $repo;

    /** @var OAuthCredentials Verificacion del token manual y limites de consulta */
    private OAuthCredentials $creds;

    /** @var OAuthCodeRepository Repositorio de codigos de autorizacion */
    private OAuthCodeRepository $codes;

    /**
     * Crea el endpoint con los repositorios que usa (conexion unica McpDb::conn()).
     */
    public function __construct()
    {
        $this->repo  = new OAuthClientRepository();
        $this->creds = new OAuthCredentials();
        $this->codes = new OAuthCodeRepository();
    }

    /**
     * Pinta la pagina de error propia del AS (sin redirigir y sin detalles tecnicos) y termina.
     *
     * @param string $titulo  Titulo de la pagina
     * @param string $mensaje Mensaje para la persona
     * @param int    $status  Codigo HTTP
     * @return never
     */
    private function page(string $titulo, string $mensaje, int $status = 400): never
    {
        OAuthHttp::html('error', ['titulo' => $titulo, 'mensaje' => $mensaje, 'volverUrl' => null], $status);
    }

    /**
     * Comprueba el limite por IP de /authorize (oauth-ip) y responde 429 con Retry-After en una pagina propia.
     *
     * @return void
     */
    private function limit(): void
    {
        $ventana = (int) ConfigAPP::APP['rateLimiterGuestWindowSeconds'];
        if (RateLimiter::isBlockedByIp('oauth-ip:' . OAuthHttp::ipKey((string) ($_SERVER['REMOTE_ADDR'] ?? '')), McpConfig::OAUTH_IP_MAX, $ventana)) {
            header('Retry-After: ' . $ventana);
            $this->page('Demasiadas solicitudes', 'Espere un minuto e inténtelo de nuevo.', 429);
        }
    }

    /**
     * Pagina 500 generica tras un fallo no previsto (el detalle nunca sale al cliente).
     *
     * @param Throwable $e Fallo capturado (solo se registra su clase)
     * @return never
     */
    private function fail(Throwable $e): never
    {
        error_log('OAuth: ' . get_class($e));
        $this->page('Error del servidor', 'No se pudo completar la solicitud. Inténtelo más tarde.', 500);
    }

    /**
     * Origen (esquema://host[:puerto]) del issuer, en minusculas y sin el puerto por defecto, para comparar con Origin.
     *
     * @return string Origen del issuer
     */
    private function issuerOrigin(): string
    {
        $p      = (array) parse_url((string) McpConfig::issuerUrl());
        $scheme = strtolower((string) ($p['scheme'] ?? ''));
        $host   = strtolower((string) ($p['host'] ?? ''));
        $port   = isset($p['port']) ? (int) $p['port'] : null;
        if ($port !== null && !(($scheme === 'https' && $port === 443) || ($scheme === 'http' && $port === 80))) {
            return $scheme . '://' . $host . ':' . $port;
        }
        return $scheme . '://' . $host;
    }

    /**
     * Redirige al cliente con un error de autorizacion (RFC 6749 §4.1.2.1) a la URI efectiva, con state e iss.
     *
     * Solo se usa con client_id existente y activo y redirect_uri ya resuelta (B-4).
     *
     * @param string      $uriEfectiva       URI efectiva construida desde la registrada
     * @param string      $error             Codigo de error OAuth
     * @param string      $descripcion       Descripcion ASCII
     * @param string|null $state             state recibido (null si no hay o si es invalido)
     * @return never
     */
    private function redirectError(string $uriEfectiva, string $error, string $descripcion, ?string $state): never
    {
        OAuthHttp::redirect($uriEfectiva, [
            'error'             => $error,
            'error_description' => $descripcion,
            'state'             => $state,
            'iss'               => McpConfig::issuerUrl(),
        ]);
    }

    /**
     * Valida la peticion de autorizacion (fases A y B).
     *
     * Fase A (sin redirigir, pagina propia): client_id existente; redirect_uri presente y resuelta contra las
     * registradas (redirectUriResolve); cliente activo. Fase B (redireccion a la URI efectiva): state demasiado
     * largo, response_type, code_challenge S256 y resource (D-11). Los scopes desconocidos se ignoran (D-10).
     *
     * @param array<string,string> $q Parametros de la query, ya sin repetidos
     * @return array<string,mixed> Peticion saneada: client_id, redirect_uri (efectiva), code_challenge, state,
     *         scope ("mcp") y resource (URI canonica del MCP). Ante cualquier error la respuesta termina aqui
     * @throws RuntimeException Si falla la base de datos
     */
    public function validateRequest(array $q): array
    {
        // Fase A: sin redirigir mientras client_id o redirect_uri no esten validados
        $c = $this->repo->findByClientId((string) ($q['client_id'] ?? ''));
        if ($c === null) {
            $this->page('Aplicación no reconocida', 'La aplicación que inició esta solicitud no está registrada.');
        }
        $pedida = (string) ($q['redirect_uri'] ?? '');
        $efectiva = $pedida === '' ? null : InternalFunctionsTokensOAuth::redirectUriResolve($c['RedirectUris'], $pedida);
        if ($efectiva === null) {
            $this->page('Dirección de retorno no válida', 'La dirección de retorno de la solicitud no está registrada para esta aplicación.');
        }
        if (!$this->repo->isUsable($c)) {
            $this->page('Aplicación no disponible', 'La aplicación que inició esta solicitud está desactivada.');
        }

        // Fase B: errores a la URI efectiva (con state solo si es de longitud admisible)
        $state = $q['state'] ?? null;
        if ($state !== null && strlen($state) > McpConfig::OAUTH_MAX_STATE_CHARS) {
            $this->redirectError($efectiva, 'invalid_request', 'state too long', null);
        }
        if (($q['response_type'] ?? '') !== 'code') {
            $this->redirectError($efectiva, 'unsupported_response_type', 'response_type must be code', $state);
        }
        $challenge = (string) ($q['code_challenge'] ?? '');
        if (!OAuthPkce::isValidChallenge($challenge) || ($q['code_challenge_method'] ?? '') !== 'S256') {
            $this->redirectError($efectiva, 'invalid_request', 'PKCE with S256 is required', $state);
        }
        if (isset($q['resource']) && !OAuthMetadata::isMcpResource($q['resource'])) {
            $this->redirectError($efectiva, 'invalid_target', 'resource is not this server', $state);
        }

        return [
            'client_id'      => $c['ClientId'],
            'redirect_uri'   => $efectiva,
            'code_challenge' => $challenge,
            'state'          => $state,
            'scope'          => McpConfig::OAUTH_SCOPE,
            'resource'       => McpConfig::resourceUrl(),
        ];
    }

    /**
     * Pinta la pantalla del AS para una peticion pendiente (rid).
     *
     * @param string      $rid    Identificador de la peticion pendiente
     * @param string|null $error  Aviso a mostrar (null = ninguno)
     * @param int         $status Codigo HTTP de la respuesta
     * @return never
     * @throws RuntimeException Si falla la base de datos
     */
    public function render(string $rid, ?string $error, int $status = 200): never
    {
        $p = OAuthStateStore::pending($rid);
        $c = $p === null ? null : $this->repo->findByClientId((string) $p['client_id']);
        if ($p === null || $c === null) {
            $this->page('Solicitud no válida', 'La solicitud caducó o no existe. Vuelva a iniciar la conexión desde la aplicación.');
        }
        OAuthHttp::html('consent', [
            'cliente'        => (string) $c['Nombre'],
            'clientePrefijo' => substr((string) $c['ClientId'], 0, 8),
            'redirectUri'    => (string) $p['redirect_uri'],
            'registro'       => (string) $c['FechaRegistro'],
            'ip'             => (string) $c['IPRegistro'],
            'rid'            => $rid,
            'clientId'       => (string) $c['ClientId'],
            'csrf'           => OAuthStateStore::csrfToken($rid),
            'accion'         => McpConfig::issuerUrl() . '/authorize',
            'error'          => $error,
        ], $status);
    }

    /**
     * Atiende GET /authorize: valida, abre la sesion propia (regenerando su id, B-3), guarda la peticion y pinta la pantalla.
     *
     * @return never
     */
    public function start(): never
    {
        try {
            $this->limit();
            $q = OAuthHttp::parseParams((string) ($_SERVER['QUERY_STRING'] ?? ''));
            if ($q === null) {
                $this->page('Solicitud no válida', 'La solicitud contiene parámetros repetidos.');
            }
            $req = $this->validateRequest($q);
            OAuthStateStore::start(true);
            $rid = OAuthStateStore::savePending($req);
            $this->render($rid, null);
        } catch (Throwable $e) {
            $this->fail($e);
        }
    }

    /**
     * Atiende POST /authorize: la persona pega un token manual o deniega.
     *
     * Controles en orden: limite por IP; Origin; rid vigente; CSRF del rid y client_id del rid; cliente recargado
     * y activo; decision. deny redirige con access_denied; token aplica los limites de consulta, verifica el
     * token (cualquier causa de fallo = "Credenciales no validas") y emite el codigo (una sola sentencia INSERT).
     *
     * @return never
     */
    public function decide(): never
    {
        try {
            $this->limit();
            OAuthStateStore::start(false);
            $f = OAuthHttp::formBody();

            $origin = $_SERVER['HTTP_ORIGIN'] ?? null;
            if ($origin !== null && strtolower((string) $origin) !== $this->issuerOrigin()) {
                $this->page('Solicitud rechazada', 'El origen de la solicitud no es válido.', 403);
            }

            $rid = (string) ($f['rid'] ?? '');
            $p   = OAuthStateStore::pending($rid);
            if ($p === null) {
                $this->page('La solicitud caducó','Vuelva a iniciar la conexión desde la aplicación.');
            }
            if (!OAuthStateStore::checkCsrf($rid, (string) ($f['csrf'] ?? '')) || ($f['client_id'] ?? '') !== $p['client_id']) {
                $this->page('Solicitud no válida', 'No se pudo comprobar la solicitud. Vuelva a iniciar la conexión desde la aplicación.');
            }

            // El cliente se recarga antes de cualquier redireccion: nunca se redirige a un cliente inexistente o desactivado (B-4)
            $c = $this->repo->findByClientId((string) $p['client_id']);
            if ($c === null || !$this->repo->isUsable($c)) {
                OAuthStateStore::clear($rid);
                $this->page('Aplicación no disponible', 'La aplicación que inició esta solicitud ya no está disponible.');
            }

            $decision = (string) ($f['decision'] ?? '');
            if ($decision === 'deny') {
                OAuthStateStore::clear($rid);
                $this->redirectError((string) $p['redirect_uri'], 'access_denied', 'The user denied the request', $p['state'] ?? null);
            }
            if ($decision !== 'token') {
                $this->page('Solicitud no válida', 'La decisión recibida no es válida.');
            }

            $token = (string) ($f['token'] ?? '');
            if ($this->creds->throttled(OAuthHttp::ipKey((string) ($_SERVER['REMOTE_ADDR'] ?? '')), $token)) {
                header('Retry-After: ' . (int) ConfigAPP::APP['rateLimiterGuestWindowSeconds']);
                $this->render($rid, 'Demasiados intentos; espere un minuto', 429);
            }
            $v = $this->creds->verifyToken($token);
            if ($v === null) {
                OAuthStateStore::fail($rid);
                if (OAuthStateStore::pending($rid) !== null) {
                    $this->render($rid, 'Credenciales no válidas', 401);
                }
                $this->page('Demasiados intentos', 'Se superaron los intentos permitidos. Vuelva a iniciar la conexión desde la aplicación.');
            }

            $code = $this->codes->issue($p, (int) $c['idCliente'], (int) $v['idToken']);
            OAuthStateStore::clear($rid);
            OAuthHttp::redirect((string) $p['redirect_uri'], ['code' => $code, 'state' => $p['state'] ?? null, 'iss' => McpConfig::issuerUrl()]);
        } catch (Throwable $e) {
            $this->fail($e);
        }
    }
}
