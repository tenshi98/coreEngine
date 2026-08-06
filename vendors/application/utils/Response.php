<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Clase Response
 *
 * Encapsula la generación de respuestas HTTP en formato JSON
 * para la API, asegurando una estructura estandarizada.
 *
 * Responsabilidades:
 * - Definir códigos HTTP
 * - Enviar headers adecuados
 * - Formatear respuestas JSON consistentes
 * - Finalizar la ejecución del script
 *
 * Estructura estándar de respuesta:
 * {
 *   "status": 200,
 *   "message": "Mensaje descriptivo",
 *   "data": {...}
 * }
 *
 * @package App\Core
 */
class Response {
    /**
     * Envía una respuesta JSON al cliente y finaliza la ejecución.
     *
     * Este método:
     * - Define el código HTTP
     * - Establece headers necesarios (JSON + CORS básico)
     * - Codifica la respuesta en formato JSON
     * - Termina la ejecución del script con exit
     *
     * Headers incluidos:
     * - Content-Type: application/json
     * - Access-Control-Allow-Origin: * (CORS abierto)
     *
     * @param int         $status  Código HTTP (200, 201, 400, 404, etc.)
     * @param string      $message Mensaje descriptivo de la respuesta
     * @param mixed|null  $data    Datos adicionales (payload)
     *
     * @return void
     *
     * @example
     * Response::json(200, 'OK', ['id' => 1]);
     */
    public static function json($status, $message, $data = null) {

        // Definir código de respuesta HTTP
        http_response_code($status);

        // Headers de respuesta
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-*');

        // Estructura estándar de respuesta
        $response = [
            'status'  => $status,
            'message' => $message,
            'data'    => $data
        ];

        // Codificar y enviar respuesta
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        // Detengo la ejecucion
        exit;

    }

    /**
     * Envía una respuesta exitosa.
     *
     * Método helper para simplificar respuestas HTTP exitosas.
     *
     * Códigos típicos:
     * - 200 OK
     * - 201 Created
     *
     * @param string     $message Mensaje de éxito
     * @param mixed|null $data    Datos de respuesta
     * @param int        $status  Código HTTP (default: 200)
     *
     * @example
     * Response::success('Usuario creado', ['id' => 10], 201);
     */
    public static function success($message = 'Success', $data = null, $status = 200) {
        self::json($status, $message, $data);
    }

    /**
     * Envía una respuesta de error.
     *
     * Método helper para respuestas de fallo.
     *
     * Códigos típicos:
     * - 400 Bad Request
     * - 401 Unauthorized
     * - 404 Not Found
     * - 422 Unprocessable Entity
     * - 500 Internal Server Error
     *
     * @param string     $message Mensaje de error
     * @param int        $status  Código HTTP (default: 400)
     * @param mixed|null $data    Información adicional (ej: errores de validación)
     *
     * @example
     * Response::error('No autorizado', 401);
     * Response::error('Error de validación', 422, ['email' => ['Requerido']]);
     */
    public static function error($message = 'Error', $status = 500, $data = null) {
        self::json($status, $message, $data);
    }

    /**
     * Se codifica directamente un dato.
     *
     * Método helper para codificacion directa.
     *
     * Códigos típicos:
     * - 400 Bad Request
     * - 401 Unauthorized
     * - 404 Not Found
     * - 422 Unprocessable Entity
     * - 500 Internal Server Error
     *
     * @param int        $status  Código HTTP (default: 400)
     * @param mixed|null $data    Información a pasar
     *
     * @example
     * Response::direct('No autorizado', 401);
     * Response::direct('Error de validación', 422);
     */
    public static function direct($data = null, $status = 200) {

        // Definir código de respuesta HTTP
        http_response_code($status);

        // Headers de respuesta
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Origin: *');

        // Codificar y enviar respuesta
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        // Detengo la ejecucion
        exit;

    }

    /**
     * Envía una respuesta de manejo de datos.
     *
     * Método helper para el manejo de datos del filemanager.
     *
     * @param bool    $success true o false
     * @param string  $message Mensaje de notificacion
     *
     * @example
     * Response::fileData(true, 'No autorizado');
     * Response::fileData(false, 'Error de validación');
     */
    public static function fileData($success = null, $message = null) {

        // Definir código de respuesta HTTP
        http_response_code(200);

        // Headers de respuesta
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Origin: *');

        // Codificar y enviar respuesta
        echo json_encode(["success" => $success, "message" => $message], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        // Detengo la ejecucion
        exit;

    }

}
