<?php
/**********************  Configuracion  **********************/
$tipo     = 1;                        // Tipo de validacion
$limit    = 10;                       // número máximo de solicitudes
$window   = 5;                        // ventana de tiempo en segundos
$ip       = $_SERVER['REMOTE_ADDR'];  // Identificador único por IP
/**********************    Verificar    **********************/
switch ($tipo) {
    /**********************     Archivo     **********************/
    case 1:
        //Configuracion
        $storageFile = "security/rate_limit_" . md5($ip) . ".json"; //Ubicacion y archivo
        //Se ejecuta
        try {
            // Asegurar que el archivo existe
            if (!file_exists($storageFile)) {
                file_put_contents($storageFile, json_encode([]));
            }

            // Cargar historial de solicitudes
            $requests = json_decode(file_get_contents($storageFile), true);

            // Eliminar solicitudes fuera de la ventana
            $requests = array_filter($requests, function ($timestamp) use ($window) {
                return $timestamp >= time() - $window;
            });

            // Verificar si se excedió el límite
            if (count($requests) >= $limit) {
                // Establece el código de respuesta HTTP correspondiente al error.
                // Por ejemplo, 429 para "Too Many Requests", 404 para "Not Found", etc.
                http_response_code(429);

                // Crea un arreglo asociativo con el mensaje de error.
                // Este mensaje será enviado al cliente en formato JSON.
                $MSG['message'] = "⛔ Has excedido el límite de solicitudes. Intenta más tarde.";

                // Codifica el arreglo en formato JSON y lo devuelve como respuesta.
                // Esto permite que el cliente reciba el mensaje de error de forma estructurada.
                echo json_encode($MSG);

                //Detengo la ejecucion
                exit;
            }

            // Registrar nueva solicitud
            $requests[] = time();
            file_put_contents($storageFile, json_encode($requests));

        } catch (\Throwable $th) {
            error_log("No se ha podido cargar el rateLimit", 0);
        }
        break;
    /**********************      Redis      **********************/
    case 2:
        require_once "../../vendors/libs/predis/vendor/autoload.php";  //Se llama a la libreria Key

        Predis\Autoloader::register();
        // Configuración
        $key   = "rate_limit:" . $ip;
        // Conexión a Redis
        $redis = new Predis\Client();
        $redis->connect('127.0.0.1', 6379); // Ajusta si tu Redis está en otro host/puerto
        //Se ejecuta
        try {
            // Obtener el número actual de solicitudes
            $current = $redis->get($key);

            // Verificar si se excedió el límite
            if ($current !== false && $current >= $limit) {
                // Establece el código de respuesta HTTP correspondiente al error.
                // Por ejemplo, 429 para "Too Many Requests", 404 para "Not Found", etc.
                http_response_code(429);

                // Crea un arreglo asociativo con el mensaje de error.
                // Este mensaje será enviado al cliente en formato JSON.
                $MSG['message'] = "⛔ Has excedido el límite de solicitudes. Intenta más tarde.";

                // Codifica el arreglo en formato JSON y lo devuelve como respuesta.
                // Esto permite que el cliente reciba el mensaje de error de forma estructurada.
                echo json_encode($MSG);

                //Detengo la ejecucion
                exit;
            }

            // Incrementar el contador
            $redis->incr($key);

            // Establecer tiempo de expiración si es la primera solicitud
            if ($current === false) {
                $redis->expire($key, $window);
            }
        } catch (\Throwable $th) {
            error_log("No se ha podido cargar el rateLimit", 0);
        }
        break;

}