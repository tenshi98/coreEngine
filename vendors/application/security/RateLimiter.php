<?php
/**
 * Clase RateLimiter
 *
 * Implementa un mecanismo básico de protección contra ataques
 * de fuerza bruta / abuso de solicitudes, utilizando almacenamiento
 * en archivo. Lleva dos conteos independientes:
 * - Por IP: para visitantes no logueados (ConfigAPP::APP['rateLimiterFileIP'])
 * - Por usuario: para usuarios logueados, identificados por su
 *   UserID de sesión (ConfigAPP::APP['rateLimiterFileUser'])
 *
 * Cada pista usa su propio archivo, por lo que ambos conteos son
 * completamente independientes entre sí (un usuario logueado no
 * consume ni comparte el límite de su propia IP).
 *
 * Funcionalidad:
 * - Limita la cantidad de intentos por identificador (IP o UserID) en una ventana de tiempo
 * - Bloquea temporalmente al identificador que excede el límite
 * - Permite limpiar el contador de un identificador puntual (ej. tras login exitoso)
 *
 * Estrategia:
 * - Almacena intentos en un archivo JSON
 * - Usa bloqueo de archivo (flock) para evitar condiciones de carrera
 * - Limpia registros expirados en cada ejecución
 * - "Fail-open": si el archivo no puede abrirse, permite el acceso
 *   (prioriza disponibilidad sobre bloqueo)
 *
 * Estructura del archivo:
 * {
 *   "<identificador>": {
 *     "count": 3,
 *     "timestamp": 1710000000
 *   }
 * }
 *
 * @package App\Security
 *
 * @example
 * // Visitante no logueado
 * if (RateLimiter::isBlockedByIp($_SERVER['REMOTE_ADDR'])) {
 *     Response::error('Demasiados intentos. Intente más tarde.', 429);
 * }
 *
 * // Usuario logueado
 * $userId = $f3->get('SESSION.DataInfo.UserID');
 * if ($userId && RateLimiter::isBlockedByUser($userId)) {
 *     Response::error('Demasiadas solicitudes. Intente más tarde.', 429);
 * }
 */
class RateLimiter {

    /**
     * Verifica si una IP ha excedido el límite de intentos permitidos.
     * Pensado para usuarios no logueados (no hay UserID de sesión).
     *
     * @param string   $ip            Dirección IP del cliente
     * @param int|null $maxAttempts   Número máximo de intentos permitidos (por defecto ConfigAPP::APP['rateLimiterGuestMaxAttempts'])
     * @param int|null $windowSeconds Ventana de tiempo en segundos (por defecto ConfigAPP::APP['rateLimiterGuestWindowSeconds'])
     *
     * @return bool True si la IP está bloqueada, false si puede continuar
     */
    public static function isBlockedByIp($ip, $maxAttempts = null, $windowSeconds = null) {
        return self::isBlocked(ConfigAPP::APP['rateLimiterFileIP'], $ip, $maxAttempts, $windowSeconds);
    }

    /**
     * Verifica si un usuario logueado ha excedido el límite de intentos permitidos.
     *
     * @param int|string $userId        UserID de sesión (ej. $f3->get('SESSION.DataInfo.UserID'))
     * @param int|null   $maxAttempts   Número máximo de intentos permitidos (por defecto ConfigAPP::APP['rateLimiterUserMaxAttempts'])
     * @param int|null   $windowSeconds Ventana de tiempo en segundos (por defecto ConfigAPP::APP['rateLimiterUserWindowSeconds'])
     *
     * @return bool True si el usuario está bloqueado, false si puede continuar
     */
    public static function isBlockedByUser($userId, $maxAttempts = null, $windowSeconds = null) {
        // Limite establecido para los usuarios logueados
        $maxAttempts   = $maxAttempts   ?? ConfigAPP::APP['rateLimiterUserMaxAttempts'];
        $windowSeconds = $windowSeconds ?? ConfigAPP::APP['rateLimiterUserWindowSeconds'];
        // Ejecuto
        return self::isBlocked(ConfigAPP::APP['rateLimiterFileUser'], (string) $userId, $maxAttempts, $windowSeconds);
    }

    /**
     * Limpia el contador de intentos de una IP específica.
     * Uso típico: después de un login exitoso desde esa IP.
     *
     * @param string $ip Dirección IP del cliente
     *
     * @return void
     */
    public static function clearIp($ip) {
        self::clear(ConfigAPP::APP['rateLimiterFileIP'], $ip);
    }

    /**
     * Limpia el contador de intentos de un usuario logueado específico.
     *
     * @param int|string $userId UserID de sesión
     *
     * @return void
     */
    public static function clearUser($userId) {
        self::clear(ConfigAPP::APP['rateLimiterFileUser'], (string) $userId);
    }

    /**
     * Verifica si un identificador (IP o UserID) ha excedido el límite
     * de intentos permitidos dentro del archivo de conteo indicado.
     *
     * Flujo:
     * 1. Abre archivo de almacenamiento
     * 2. Aplica bloqueo exclusivo (LOCK_EX)
     * 3. Lee y decodifica el contenido JSON
     * 4. Elimina registros expirados (ventana de tiempo)
     * 5. Incrementa contador del identificador actual
     * 6. Evalúa si supera el máximo permitido
     * 7. Guarda el estado actualizado
     *
     * @param string   $file          Ruta del archivo de conteo (IP o Usuario)
     * @param string   $key           Identificador (IP o UserID) a evaluar
     * @param int|null $maxAttempts   Número máximo de intentos permitidos
     * @param int|null $windowSeconds Ventana de tiempo en segundos
     *
     * @return bool True si el identificador está bloqueado, false si puede continuar
     */
    private static function isBlocked($file, $key, $maxAttempts, $windowSeconds) {

        $maxAttempts   = $maxAttempts   ?? ConfigAPP::APP['rateLimiterGuestMaxAttempts'];
        $windowSeconds = $windowSeconds ?? ConfigAPP::APP['rateLimiterGuestWindowSeconds'];

        // Se abre el archivo en modo lectura/escritura; se crea si no existe
        $fp = @fopen($file, 'c+');

        // Fail-open: si no se puede acceder al archivo, no bloquear
        if (!$fp) {return false;}

        // Variable que indica si el identificador debe ser bloqueado
        $blocked = false;

        // Se adquiere un bloqueo exclusivo para evitar condiciones de carrera
        if (flock($fp, LOCK_EX)) {

            // Leer contenido actual del archivo
            $content = stream_get_contents($fp);

            // Decodificar el contenido JSON a un arreglo asociativo
            $logs    = $content ? json_decode($content, true) : [];

            // Validar que el resultado sea un arreglo válido
            if (!is_array($logs)) {$logs = [];}

            // Obtener timestamp actual
            $now = time();

            // Limpieza de registros expirados fuera de la ventana de tiempo
            foreach ($logs as $logKey => $data) {
                if ($now - $data['timestamp'] > $windowSeconds) {
                    unset($logs[$logKey]);
                }
            }

            // Verificar si el identificador ya tiene registros previos
            if (isset($logs[$key])) {

                // Incrementar contador de intentos
                $logs[$key]['count']++;

                // Evaluar si supera el máximo permitido
                if ($logs[$key]['count'] > $maxAttempts) {
                    $blocked = true;
                }

            } else {

                // Registrar nuevo identificador con contador inicial y timestamp actual
                $logs[$key] = [
                    'count' => 1,
                    'timestamp' => $now
                ];
            }

            // Persistir cambios en el archivo
            ftruncate($fp, 0);                                  // Truncar el contenido existente
            rewind($fp);                                        // Reposicionar el puntero al inicio
            fwrite($fp, json_encode($logs, JSON_PRETTY_PRINT)); // Escribir el nuevo estado en formato JSON
            fflush($fp);                                        // Forzar escritura en disco
            flock($fp, LOCK_UN);                                // Liberar el bloqueo del archivo
        }

        // Cerrar el archivo
        fclose($fp);

        // Retornar si el identificador debe ser bloqueado
        return $blocked;
    }

    /**
     * Limpia el contador de intentos para un identificador específico
     * (IP o UserID) dentro del archivo de conteo indicado.
     *
     * @param string $file Ruta del archivo de conteo (IP o Usuario)
     * @param string $key  Identificador (IP o UserID) a limpiar
     *
     * @return void
     */
    private static function clear($file, $key) {

        // Se abre el archivo en modo lectura/escritura; se crea si no existe
        $fp = @fopen($file, 'c+');

        // Si no se puede abrir el archivo, se termina la ejecución
        if (!$fp) {return;}

        // Se adquiere un bloqueo exclusivo para evitar accesos concurrentes
        if (flock($fp, LOCK_EX)) {

            // Leer contenido actual del archivo
            $content = stream_get_contents($fp);

            // Decodificar el contenido JSON a un arreglo asociativo
            $logs = $content ? json_decode($content, true) : [];

            // Validar que el contenido sea un arreglo y que el identificador exista
            if (is_array($logs) && isset($logs[$key])) {
                unset($logs[$key]);                                 // Eliminar el registro del identificador
                ftruncate($fp, 0);                                  // Truncar el archivo para sobrescribirlo
                rewind($fp);                                        // Reposicionar el puntero al inicio
                fwrite($fp, json_encode($logs, JSON_PRETTY_PRINT)); // Escribir el nuevo estado en formato JSON
                fflush($fp);                                        // Forzar escritura en disco
            }

            // Liberar el bloqueo del archivo
            flock($fp, LOCK_UN);
        }

        // Cerrar el archivo
        fclose($fp);
    }
}
