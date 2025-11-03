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
        $storageFileRate   = "security/rate_limit_" . md5($ip) . ".json"; //Ubicacion y archivo
        $storageFileVisits = "security/nVisits_" . md5($ip) . ".json";    //Ubicacion y archivo
        /*******************************************************/
        //Se ejecuta
        try {
            // Verifica si el archivo existe
            if (!file_exists($storageFileRate)) {
                // Si no existe, crea el archivo vacio
                file_put_contents($storageFileRate, json_encode([]));
            }

            // Lee el contenido del archivo
            $requests = json_decode(file_get_contents($storageFileRate), true);

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
            file_put_contents($storageFileRate, json_encode($requests));

        } catch (\Throwable $th) {
            error_log("No se ha podido cargar el rateLimit", 0);
        }
        /*******************************************************/
        //Se ejecuta
        try {
            // Verifica si el archivo existe
            if (!file_exists($storageFileVisits)) {
                // Si no existe, crea el archivo con el valor inicial 1
                file_put_contents($storageFileVisits, json_encode(['contador' => 1]));
            }

            // Lee el contenido del archivo
            $datos = json_decode(file_get_contents($storageFileVisits), true);

            // Verifica si el contenido es válido y contiene el campo 'contador'
            if (!is_array($datos) || !isset($datos['contador'])) {
                $datos = ['contador' => 1];
            } else {
                $datos['contador'] += 1;
            }

            // Guarda el nuevo valor en el archivo
            file_put_contents($storageFileVisits, json_encode($datos));

        } catch (\Throwable $th) {
            error_log("No se ha podido cargar el rateLimit", 0);
        }

        break;
    /**********************      Redis      **********************/
    case 2:
        require_once "../../vendors/libs/predis/vendor/autoload.php";  //Se llama a la libreria Key

        Predis\Autoloader::register();
        // Conexión a Redis
        $redis = new Predis\Client();
        $redis->connect('127.0.0.1', 6379); // Ajusta si tu Redis está en otro host/puerto
        // Configuración
        $key_1   = "rateLimit:" . $ip;
        $key_2   = "nVisitas:" . $ip;
        /*******************************************************/
        //Se ejecuta
        try {
            // Obtener el número actual de solicitudes
            $current = $redis->get($key_1);

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
            $redis->incr($key_1);

            // Establecer tiempo de expiración si es la primera solicitud
            if ($current === false) {
                $redis->expire($key_1, $window);
            }
        } catch (\Throwable $th) {
            error_log("No se ha podido cargar el rateLimit", 0);
        }
        /*******************************************************/
        //Se ejecuta
        try {
            // Obtener el número actual de solicitudes
            $current = $redis->get($key_2);

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
            $redis->incr($key_2);

            // Establecer tiempo de expiración si es la primera solicitud
            if ($current === false) {
                $redis->expire($key_2, $window);
            }
        } catch (\Throwable $th) {
            error_log("No se ha podido cargar el rateLimit", 0);
        }

        break;

}
