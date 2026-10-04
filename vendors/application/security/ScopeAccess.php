<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Clase ScopeAccess
 *
 * Concede y valida capabilities de acceso por "ámbito" (scope) para endpoints
 * genéricos compartidos por varios módulos (por ejemplo, los del explorador de
 * archivos `/core/fileExplorer/*`).
 *
 * Por qué existe:
 * - `sistemaFuncionalidad` es un controlador GENÉRICO reutilizado por múltiples
 *   transacciones, por lo que no puede conocer el módulo que lo invoca.
 * - `FileManager` es una librería de almacenamiento y no debe conocer permisos.
 * - El nivel de acceso debe OTORGARSE en el módulo que implementa la pantalla
 *   (donde ya se conoce `getArrLevel()`) y VALIDARSE en el endpoint.
 *
 * Seguridad:
 * - El nivel NO viaja por el cliente: vive en la sesión del servidor, por lo que
 *   no es falsificable (a diferencia de enviarlo como parámetro POST/GET).
 * - El ámbito recibido por el endpoint únicamente *selecciona* una concesión ya
 *   existente; no puede crear ni elevar una. Un scope inexistente se deniega.
 * - El grant se ata al `UserID` de la sesión: no se puede reutilizar la concesión
 *   de otro usuario.
 * - Caducidad por tiempo: tras revocar permisos en BD, la concesión deja de
 *   servir pasado el TTL y se renueva al recargar la vista.
 * - "Fail-closed": cualquier dato ausente, incoherente o expirado se deniega.
 *
 * @package App\Security
 *
 * @example
 * // En el módulo que implementa la pantalla (origen)
 * ScopeAccess::grant($f3, 'archivosListado', $nivelDelUsuario);
 *
 * // En el endpoint genérico (consumo)
 * if (!ScopeAccess::check($f3, $_POST['AccessScope'] ?? '', 3)) { exit; }
 */
class ScopeAccess {

    /** Clave de SESSION donde se almacenan las concesiones. */
    const SESSION_KEY = 'arrScopeAccess';

    /** Caducidad por defecto de una concesión, en minutos. */
    const DEFAULT_TTL_MINUTES = 15;

    /*******************************************************************************************************************/
    /**
     * Normaliza y valida un nombre de ámbito.
     *
     * Se restringe a caracteres alfanuméricos, guion y guion bajo para que el
     * ámbito sea siempre una clave segura de arreglo y no pueda colisionar con
     * el formato de los parámetros de ruta del router.
     *
     * @param mixed $scope Ámbito recibido (POST/GET).
     *
     * @return string Ámbito normalizado, o cadena vacía si no es utilizable.
     */
    public static function normalizeScope($scope): string {

        if (!is_string($scope) && !is_numeric($scope)) {
            return '';
        }

        $clean = preg_replace('/[^a-zA-Z0-9_\-]/', '', trim((string) $scope));

        return is_string($clean) ? $clean : '';
    }

    /*******************************************************************************************************************/
    /**
     * Registra en la sesión el nivel de acceso del usuario para un ámbito.
     *
     * Se invoca desde el módulo que implementa la pantalla, que es quien conoce
     * el nivel real del usuario (`getArrLevel()`). Si el usuario no tiene
     * permiso, se concede nivel 0 y por tanto el endpoint deniega la operación.
     *
     * @param \Base|null $f3      Instancia de Fat-Free Framework.
     * @param string     $scope   Ámbito (normalmente el RutaController del módulo).
     * @param int|null   $level   Nivel de acceso del usuario (1..4). Null o vacío = 0.
     * @param int        $ttlMin  Vigencia de la concesión en minutos.
     *
     * @return string Ámbito concedido, o cadena vacía si el ámbito no es válido.
     */
    public static function grant($f3 = null, $scope = '', $level = 0, int $ttlMin = self::DEFAULT_TTL_MINUTES): string {

        $f3 = $f3 ?: \Base::instance();

        // Un ámbito inválido no se concede (fail-closed)
        $scope = self::normalizeScope($scope);
        if ($scope === '') {
            return '';
        }

        // Se normaliza el nivel a un entero acotado a 0..4
        $level = (int) $level;
        $level = max(0, min(4, $level));

        // Vigencia mínima de 1 minuto
        $ttl = max(1, $ttlMin) * 60;

        // Se acumula la concesión (conserva los demás ámbitos de la sesión)
        $grants = $f3->get('SESSION.'.self::SESSION_KEY);
        if (!is_array($grants)) {
            $grants = [];
        }

        $grants[$scope] = [
            'Level'  => $level,
            'UserID' => (int) ($f3->get('SESSION.DataInfo.UserID') ?: 0),
            'Exp'    => time() + $ttl,
        ];

        $f3->set('SESSION.'.self::SESSION_KEY, $grants);

        return $scope;
    }
    /*******************************************************************************************************************/
    /**
     * Valida si el usuario actual posee un ámbito con nivel suficiente.
     *
     * Exige, en este orden: ámbito normalizable, concesión existente, concesión
     * perteneciente al usuario en sesión, concesión vigente y nivel suficiente.
     * Cualquier fallo devuelve false (fail-closed).
     *
     * @param \Base|null $f3         Instancia de Fat-Free Framework.
     * @param mixed      $scope      Ámbito solicitado por el cliente.
     * @param int        $minLevel   Nivel mínimo requerido por la operación.
     *
     * @return bool True solo si la operación está autorizada.
     */
    public static function check($f3 = null, $scope = '', int $minLevel = 1): bool {

        $f3 = $f3 ?: \Base::instance();

        $scope = self::normalizeScope($scope);
        if ($scope === '') {
            return false;
        }

        $grants = $f3->get('SESSION.'.self::SESSION_KEY);
        if (!is_array($grants) || !isset($grants[$scope]) || !is_array($grants[$scope])) {
            return false;
        }

        $grant = $grants[$scope];

        // La concesión debe pertenecer al usuario en sesión
        $userId = (int) ($f3->get('SESSION.DataInfo.UserID') ?: 0);
        if ($userId === 0 || (int) ($grant['UserID'] ?? 0) !== $userId) {
            return false;
        }

        // La concesión debe estar vigente
        if ((int) ($grant['Exp'] ?? 0) <= time()) {
            return false;
        }

        // Nivel suficiente (comparación estricta: 0 nunca autoriza)
        $level = (int) ($grant['Level'] ?? 0);
        if ($level < 1 || $level < $minLevel) {
            return false;
        }

        return true;
    }

    /*******************************************************************************************************************/
    /**
     * Elimina la concesión de un ámbito concreto.
     *
     * @param \Base|null $f3    Instancia de Fat-Free Framework.
     * @param string     $scope Ámbito a revocar.
     *
     * @return void
     */
    public static function revoke($f3 = null, $scope = ''): void {

        $f3 = $f3 ?: \Base::instance();

        $scope = self::normalizeScope($scope);
        if ($scope === '') {
            return;
        }

        $grants = $f3->get('SESSION.'.self::SESSION_KEY);
        if (!is_array($grants)) {
            return;
        }

        unset($grants[$scope]);
        $f3->set('SESSION.'.self::SESSION_KEY, $grants);

    }

}
