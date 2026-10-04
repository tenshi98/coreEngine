<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Clase CsrfToken
 *
 * Implementa la protección contra Cross-Site Request Forgery (CSRF) mediante un
 * token de sincronización ("synchronizer token") almacenado en la sesión del usuario.
 *
 * Funcionalidad:
 * - Genera y persiste un token único por sesión (carga perezosa / lazy loading).
 * - Recupera el token enviado por el cliente (campo POST o encabezado HTTP).
 * - Valida el token recibido contra el de la sesión usando comparación en tiempo constante.
 * - Permite regenerar (login) y limpiar (logout) el token.
 *
 * Estrategia:
 * - Solo se exige token en métodos de escritura (POST, PUT, DELETE, PATCH).
 * - La validación se aplica de forma central en el borde de la petición (hook
 *   `beforeroute` de `ControllerBase`). Las llamadas internas entre controladores
 *   no pasan por el router y por tanto NO vuelven a validar, de forma análoga a como
 *   la capa de datos reutiliza el contexto con `$NewDBConn ?? $this->getDBConn()`.
 * - "Fail-closed": ante token ausente o inválido se corta la ejecución con 419.
 *
 * Configuración (ConfigAPP::APP):
 * - csrfEnabled        (bool)   Habilita/deshabilita la validación.
 * - csrfTokenName      (string) Nombre del campo en el cuerpo POST.
 * - csrfHeaderName     (string) Nombre del encabezado HTTP.
 * - csrfExcludeVerbs   (array)  Verbos que no requieren token.
 * - csrfExcludeRoutes  (array)  Rutas exentas (formato "VERBO /ruta" o "/ruta").
 *
 * @package App\Security
 *
 * @example
 * // Validación central (ControllerBase::beforeroute)
 * CsrfToken::guard($f3);
 *
 * // Token para las vistas / JS
 * CsrfToken::getToken($f3);
 */
class CsrfToken {

    /** Clave con la que se almacena el token dentro de SESSION. */
    const SESSION_KEY = 'csrf_token';

    /**
     * Obtiene el token CSRF de la sesión generándolo de forma perezosa.
     *
     * Si el token no existe (o está vacío) se genera uno nuevo con
     * `random_bytes()` y se almacena en la sesión. En posteriores llamadas
     * dentro de la misma sesión se devuelve el token ya existente.
     *
     * @param \Base|null $f3 Instancia de Fat-Free Framework (opcional).
     *
     * @return string Token CSRF de la sesión actual.
     */
    public static function getToken($f3 = null) {

        // Se resuelve la instancia del framework
        $f3 = $f3 ?: \Base::instance();

        // Se recupera el token actual
        $token = $f3->get('SESSION.'.self::SESSION_KEY);

        // Si no existe, se genera y persiste
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            $f3->set('SESSION.'.self::SESSION_KEY, $token);
        }

        // Se devuelve el token
        return $token;
    }

    /**
     * Recupera el token CSRF enviado por el cliente.
     *
     * Orden de búsqueda:
     * 1. Campo en el cuerpo POST (formularios / FormData).
     * 2. Encabezado HTTP (peticiones AJAX con jQuery/fetch).
     * 3. Superglobal $_SERVER como respaldo.
     *
     * @param \Base|null $f3 Instancia de Fat-Free Framework (opcional).
     *
     * @return string|null Token recibido o null si no viene ninguno.
     */
    public static function fromRequest($f3 = null) {

        // Nombre de campo y encabezado configurables
        $name   = ConfigAPP::APP['csrfTokenName']  ?? '_token';
        $header = ConfigAPP::APP['csrfHeaderName'] ?? 'X-CSRF-Token';

        // 1) Campo en el cuerpo POST
        $token = $_POST[$name] ?? null;

        // 2) Encabezado HTTP vía F3 (sin distinguir mayúsculas/minúsculas)
        if ($token === null && $f3 !== null) {
            $headers = $f3->get('HEADERS');
            if (is_array($headers)) {
                foreach ($headers as $key => $value) {
                    if (strcasecmp($key, $header) === 0) {
                        $token = $value;
                        break;
                    }
                }
            }
        }

        // 3) Respaldo directo en $_SERVER
        if ($token === null) {
            $serverKey = 'HTTP_'.strtoupper(str_replace('-', '_', $header));
            if (isset($_SERVER[$serverKey])) {
                $token = $_SERVER[$serverKey];
            }
        }

        // Solo se aceptan cadenas
        return is_string($token) ? $token : null;
    }

    /**
     * Valida el token recibido contra el token de la sesión.
     *
     * @param \Base|null  $f3    Instancia de Fat-Free Framework (opcional).
     * @param string|null $token Token a validar (si es null se toma de la petición).
     *
     * @return bool true si el token es válido; false en cualquier otro caso.
     */
    public static function validate($f3 = null, $token = null) {

        // Se resuelve el token a comparar
        $token = $token ?? self::fromRequest($f3);

        // Sin token no hay validación posible
        if (!is_string($token) || $token === '') {
            return false;
        }

        // Comparación en tiempo constante (evita ataques de temporización)
        return hash_equals(self::getToken($f3), $token);
    }

    /**
     * Indica si la petición actual debe pasar por validación CSRF.
     *
     * Se evalúan tres condiciones:
     * - La validación está habilitada (csrfEnabled).
     * - El verbo HTTP es de escritura (no está en csrfExcludeVerbs).
     * - La ruta no está en la lista de exenciones (csrfExcludeRoutes).
     *
     * @param \Base|null $f3 Instancia de Fat-Free Framework (opcional).
     *
     * @return bool true si la petición debe validarse; false si está exenta.
     */
    public static function isRequired($f3 = null) {

        // Se resuelve la instancia del framework
        $f3 = $f3 ?: \Base::instance();

        // 1) Validación deshabilitada globalmente
        if (empty(ConfigAPP::APP['csrfEnabled'])) {
            return false;
        }

        // 2) Verbo HTTP seguro (no requiere token)
        $verb         = strtoupper((string) ($f3->get('VERB') ?: 'GET'));
        $excludeVerbs = ConfigAPP::APP['csrfExcludeVerbs'] ?? ['GET', 'HEAD', 'OPTIONS'];
        if (in_array($verb, $excludeVerbs, true)) {
            return false;
        }

        // 3) Ruta exenta (se comparan con y sin verbo para ser robustos)
        $pattern       = (string) ($f3->get('PATTERN') ?: '');
        $routeKey      = $verb.' '.$pattern;
        $excludeRoutes = ConfigAPP::APP['csrfExcludeRoutes'] ?? [];
        foreach ($excludeRoutes as $excluded) {
            if ($excluded === '') {
                continue;
            }
            if (strcasecmp($routeKey, $excluded) === 0 || strcasecmp($pattern, $excluded) === 0) {
                return false;
            }
        }

        // Requiere validación
        return true;
    }

    /**
     * Guardián central de CSRF.
     *
     * Expone el token a las vistas (hive CSRF_TOKEN) y, cuando la petición lo
     * requiere, valida el token recibido. Si la validación falla, registra el
     * evento de auditoría y corta la ejecución con una respuesta 419.
     *
     * @param \Base|null $f3 Instancia de Fat-Free Framework (opcional).
     *
     * @return bool true si todo es correcto (o la ruta está exenta).
     */
    public static function guard($f3 = null) {

        // Se resuelve la instancia del framework
        $f3 = $f3 ?: \Base::instance();

        // Se publica el token para las vistas ($CSRF_TOKEN) de forma perezosa
        $f3->set('CSRF_TOKEN', self::getToken($f3));

        // Si la petición no requiere validación, se continúa
        if (!self::isRequired($f3)) {
            return true;
        }

        // Validación del token
        if (self::validate($f3)) {
            return true;
        }

        // Se registra el intento fallido (trazabilidad)
        AuditLogger::log(
            'CSRF',
            'Token inválido. Verbo '.(string) $f3->get('VERB').' ruta '.(string) $f3->get('PATTERN')
        );

        // Se corta la ejecución (fail-closed)
        Response::error('Token de seguridad inválido o expirado. Recargue la página e intente nuevamente.', 419, ['csrf' => true]);
        return false;
    }

    /**
     * Regenera el token CSRF de la sesión.
     *
     * Debe invocarse tras un login exitoso para evitar la fijación del token
     * (el token previo a la autenticación deja de ser válido).
     *
     * @param \Base|null $f3 Instancia de Fat-Free Framework (opcional).
     *
     * @return string Nuevo token generado.
     */
    public static function rotate($f3 = null) {

        // Se resuelve la instancia del framework
        $f3 = $f3 ?: \Base::instance();

        // Se elimina el token actual y se genera uno nuevo
        $f3->clear('SESSION.'.self::SESSION_KEY);

        // Se devuelve el nuevo token
        return self::getToken($f3);
    }

    /**
     * Elimina el token CSRF de la sesión.
     *
     * Debe invocarse al cerrar sesión.
     *
     * @param \Base|null $f3 Instancia de Fat-Free Framework (opcional).
     *
     * @return void
     */
    public static function clear($f3 = null) {

        // Se resuelve la instancia del framework
        $f3 = $f3 ?: \Base::instance();

        // Se limpia el token de la sesión
        $f3->clear('SESSION.'.self::SESSION_KEY);
    }

}


