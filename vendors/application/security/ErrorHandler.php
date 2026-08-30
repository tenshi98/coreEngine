<?php
/**
 * Clase ErrorHandler
 *
 * Manejador global de excepciones no capturadas (Throwable que
 * escapa de todo try/catch de la aplicación).
 *
 * Evita exponer al cliente cualquier detalle interno (mensaje real
 * de la excepción, ruta del archivo, línea, stack trace). El detalle
 * real se guarda de forma privada en un archivo de log (dentro de
 * security/, bloqueado por .htaccess) y al cliente solo se responde
 * un JSON genérico con status 500.
 *
 * Además de capturar excepciones no atrapadas, register() redirige el
 * log_errors/error_log NATIVO de PHP (configurado normalmente en
 * php.ini) hacia la misma ruta que usa esta clase
 * (ConfigAPP::APP['errorLogFile']). Esto es necesario porque, sin esta
 * redirección, PHP puede escribir su archivo de log por defecto
 * (típicamente "error_log") en la misma carpeta del script que se
 * ejecuta (por ejemplo admin/public/), quedando expuesto junto al
 * index.php. Con esta redirección:
 * - Las excepciones no capturadas (Throwable) siguen manejándose vía
 *   handle(), que además responde al cliente con un JSON genérico.
 * - Los warnings, notices y errores fatales de PHP que NO son
 *   excepciones, junto con los error_log() explícitos que existan en
 *   el código (ej. Functions.php), ahora también se escriben en
 *   ConfigAPP::APP['errorLogFile'], en vez de en la ubicación por
 *   defecto del servidor.
 *
 * Esta clase lee su ruta de log directamente desde
 * ConfigAPP::APP['errorLogFile']: debe poder registrarse y funcionar
 * aunque el resto de la configuración de la aplicación todavía no se
 * haya cargado (por ejemplo, si el error ocurre muy temprano en el
 * bootstrap, cargando la configuración de base de datos).
 *
 */
class ErrorHandler {

    /**
     * Registra este manejador como el exception handler global de PHP
     * y redirige el log_errors/error_log nativo de PHP hacia
     * ConfigAPP::APP['errorLogFile'].
     *
     * Debe llamarse una única vez, lo antes posible en el bootstrap
     * de la aplicación (public/index.php), para capturar también
     * excepciones lanzadas durante la carga temprana del sistema
     * (por ejemplo, al conectar la base de datos) y para que ningún
     * error/warning/notice ni error_log() explícito alcance a
     * escribirse en la ubicación por defecto del servidor (que suele
     * ser la misma carpeta del script, junto a index.php) antes de
     * quedar redirigido.
     *
     * @return void
     *
     * @example
     * require_once __DIR__ . '/../app/ErrorHandler.php';
     * ErrorHandler::register();
     */
    public static function register() {
        self::configureNativeLogging();
        set_exception_handler([self::class, 'handle']);
    }

    /**
     * Maneja una excepción no capturada.
     *
     * Flujo:
     * 1. Registra el detalle real (timestamp, mensaje, archivo, línea)
     *    en el log de errores privado (ConfigAPP::APP['errorLogFile']).
     * 2. Responde al cliente con un JSON genérico (sin detalles
     *    internos) y código HTTP 500.
     * 3. Detiene la ejecución (exit).
     *
     * @param Throwable $exception Excepción no capturada.
     *
     * @return void No retorna (termina la ejecución con exit).
     */
    public static function handle($exception) {

        // Loguear el error real de forma segura (invisible al cliente)
        self::writeLog($exception->getMessage(), $exception->getFile(), $exception->getLine());

        // Responder al cliente sin exponer detalles internos
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode(['status' => 500, 'message' => 'Internal Server Error']);
        exit;

    }

    /**
     * Método de prueba: genera una entrada de prueba en el error.log
     * SIN enviar respuesta HTTP ni detener la ejecución (a diferencia
     * de handle()), para poder verificar desde un script/ruta de test
     * que ErrorHandler está correctamente configurado y puede escribir
     * en el archivo de log.
     *
     * @param string|null $message Mensaje de prueba (opcional).
     *
     * @return array{
     *     logFile: string,
     *     dirExists: bool,
     *     dirWritable: bool,
     *     bytesWritten: int|false,
     *     success: bool
     * }
     *
     * @example
     * $r = ErrorHandler::testLogGeneration();
     * var_dump($r);
     */
    public static function testLogGeneration($message = null) {

        $message = $message ?: 'Prueba de generación de error.log (' . date('Y-m-d H:i:s') . ')';
        $logFile = ConfigAPP::APP['errorLogFile'];
        $logDir  = dirname($logFile);

        $bytesWritten = self::writeLog($message, __FILE__, __LINE__);

        return [
            'logFile'      => $logFile,
            'dirExists'    => is_dir($logDir),
            'dirWritable'  => is_dir($logDir) && is_writable($logDir),
            'bytesWritten' => $bytesWritten,
            'success'      => $bytesWritten !== false,
        ];
    }

    /**
     * Fuerza al motor de PHP (log_errors/error_log nativo) a escribir
     * en ConfigAPP::APP['errorLogFile'] en vez de en la ubicación por
     * defecto definida en el php.ini del servidor.
     *
     * Afecta tanto a los errores nativos de PHP (warnings, notices,
     * fatales) como a los error_log() explícitos del código que no
     * indiquen un destino propio.
     *
     * @return void
     */
    private static function configureNativeLogging() {
        $logFile = ConfigAPP::APP['errorLogFile'];
        ini_set('log_errors', '1');
        ini_set('error_log', $logFile);
    }

    /**
     * Escribe una entrada en el archivo de log de errores.
     *
     * @param string $message Mensaje a registrar.
     * @param string $file    Archivo de origen (referencial).
     * @param int    $line    Línea de origen (referencial).
     *
     * @return int|false Cantidad de bytes escritos, o false si falló.
     */
    private static function writeLog($message, $file, $line) {
        $logFile = ConfigAPP::APP['errorLogFile'];
        $errorMessage = "[" . date('Y-m-d H:i:s') . "] " . $message . " en " . $file . ":" . $line . PHP_EOL;
        return @file_put_contents($logFile, $errorMessage, FILE_APPEND);
    }

}
