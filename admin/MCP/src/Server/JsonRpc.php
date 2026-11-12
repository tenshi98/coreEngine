<?php
/**
 * Utilidades JSON-RPC 2.0 del servidor MCP.
 *
 * @see projects/mcp-servidor/specs/technical.md §5
 */
class JsonRpc
{
    /**
     * Decodifica el cuerpo y lo normaliza a lista de mensajes.
     *
     * @param string $body Cuerpo crudo de la peticion
     * @return array{batch: bool, messages: array}
     * @throws McpException -32700 si no es JSON valido; -32600 si el lote esta vacio o excede MAX_BATCH
     */
    public static function parse(string $body): array
    {
        $data = json_decode($body, true, McpConfig::MAX_JSON_DEPTH, JSON_BIGINT_AS_STRING);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            throw new McpException(-32700, 'Parse error');
        }
        // Lote: lista no vacia (array_is_list no existe en PHP 8.0)
        if ($data === [] || array_keys($data) === range(0, count($data) - 1)) {
            if ($data === [] || count($data) > McpConfig::MAX_BATCH) {
                throw new McpException(-32600, 'Invalid Request');
            }
            return ['batch' => true, 'messages' => $data];
        }
        return ['batch' => false, 'messages' => [$data]];
    }

    /**
     * Valida la forma de un mensaje JSON-RPC.
     *
     * @param mixed $msg Mensaje decodificado
     * @throws McpException -32600 si la forma es invalida
     */
    public static function validate(mixed $msg): void
    {
        if (!is_array($msg) || ($msg !== [] && array_keys($msg) === range(0, count($msg) - 1))) {
            throw new McpException(-32600, 'Invalid Request');
        }
        if (($msg['jsonrpc'] ?? null) !== '2.0') {
            throw new McpException(-32600, 'Invalid Request');
        }
        // Respuesta de cliente: sin method pero con result/error e id
        $esRespuesta = !array_key_exists('method', $msg)
            && (array_key_exists('result', $msg) || array_key_exists('error', $msg));
        if (!$esRespuesta && (!isset($msg['method']) || !is_string($msg['method']) || $msg['method'] === '')) {
            throw new McpException(-32600, 'Invalid Request');
        }
        if (array_key_exists('id', $msg) && !is_string($msg['id']) && !is_int($msg['id'])) {
            throw new McpException(-32600, 'Invalid Request');
        }
        if (array_key_exists('params', $msg) && !is_array($msg['params'])) {
            throw new McpException(-32600, 'Invalid Request');
        }
    }

    /**
     * Notificacion: con method y sin id; las respuestas de cliente (sin method) se tratan igual.
     *
     * @param array $msg Mensaje validado
     * @return bool
     */
    public static function isNotification(array $msg): bool
    {
        if (!array_key_exists('method', $msg)) {
            return true;
        }
        return !array_key_exists('id', $msg);
    }

    /**
     * @param string|int   $id     Id de la peticion
     * @param array|object $result Resultado
     * @return array
     */
    public static function result(string|int $id, array|object $result): array
    {
        return ['jsonrpc' => '2.0', 'id' => $id, 'result' => $result];
    }

    /**
     * @param string|int|null $id      Id de la peticion (null si no se pudo determinar)
     * @param int             $code    Codigo JSON-RPC
     * @param string          $message Mensaje fijo
     * @param mixed           $data    Datos opcionales (solo se incluye si no es null)
     * @return array
     */
    public static function error(string|int|null $id, int $code, string $message, mixed $data = null): array
    {
        $error = ['code' => $code, 'message' => $message];
        if ($data !== null) {
            $error['data'] = $data;
        }
        return ['jsonrpc' => '2.0', 'id' => $id, 'error' => $error];
    }

    /**
     * Codifica la respuesta (UTF-8 invalido se sustituye, H-13).
     *
     * @param array $payload Respuesta o lote de respuestas
     * @return string
     */
    public static function encode(array $payload): string
    {
        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }
}
