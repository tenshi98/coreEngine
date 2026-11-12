<?php
PHP_SAPI === 'cli' || exit;
/**
 * Puente stdio del servidor MCP de coreEngine.
 *
 * Script CLI sin bootstrap.php (D-10): reenvia por HTTPS las lineas de STDIN al endpoint MCP.
 * Configuracion por entorno: MCP_URL y MCP_TOKEN. Nunca imprime el token ni cabeceras HTTP.
 *
 * @see projects/mcp-servidor/specs/technical.md §12
 */

/**
 * Lee y valida MCP_URL y MCP_TOKEN del entorno (H-11).
 *
 * Ante cualquier incumplimiento escribe el motivo (sin el token) en STDERR y sale con codigo 1.
 *
 * @return array{url: string, token: string}
 */
function stdio_config(): array
{
    $url   = getenv('MCP_URL');
    $token = getenv('MCP_TOKEN');
    $fail  = static function (string $motivo): void {
        fwrite(STDERR, 'mcp-stdio: ' . $motivo . PHP_EOL);
        exit(1);
    };

    if ($url === false || $url === '') {
        $fail('falta la variable MCP_URL');
    }
    if ($token === false || $token === '') {
        $fail('falta la variable MCP_TOKEN');
    }
    if (strpos($url, '?') !== false || strpos($url, '#') !== false) {
        $fail('MCP_URL no puede llevar query string ni fragmento');
    }
    $partes = parse_url($url);
    if ($partes === false || !isset($partes['scheme'], $partes['host'])) {
        $fail('MCP_URL no es una URL valida');
    }
    $esquema = strtolower($partes['scheme']);
    $host    = strtolower(trim($partes['host'], '[]'));
    if ($esquema === 'http') {
        if (!in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            $fail('MCP_URL debe usar https (http solo para localhost, 127.0.0.1 o ::1)');
        }
    } elseif ($esquema !== 'https') {
        $fail('MCP_URL debe usar https');
    }
    if (preg_match('/^[A-Fa-f0-9]{64}$/', $token) !== 1) {
        $fail('MCP_TOKEN no tiene el formato esperado (64 caracteres hexadecimales)');
    }

    return ['url' => $url, 'token' => strtolower($token)];
}

/**
 * Envia una linea JSON-RPC al endpoint MCP por HTTP POST.
 *
 * No sigue redirecciones y verifica el certificado (H-11). El token solo viaja en la cabecera
 * Authorization; nunca se imprime ni se escribe en STDERR.
 *
 * @param array       $cfg     Configuracion de stdio_config()
 * @param string      $line    Mensaje JSON-RPC (una linea)
 * @param string|null $version Valor de MCP-Protocol-Version tras initialize, o null antes
 * @return array{status: int, body: string} status 0 si no hubo respuesta HTTP
 */
function stdio_post(array $cfg, string $line, ?string $version): array
{
    $cabeceras = [
        'Content-Type: application/json',
        'Accept: application/json, text/event-stream',
        'Authorization: Bearer ' . $cfg['token'],
    ];
    if ($version !== null) {
        $cabeceras[] = 'MCP-Protocol-Version: ' . $version;
    }
    $ctx = stream_context_create([
        'http' => [
            'method'          => 'POST',
            'header'          => implode("\r\n", $cabeceras),
            'content'         => $line,
            'ignore_errors'   => true,
            'timeout'         => 60,
            'follow_location' => 0,
            'max_redirects'   => 0,
        ],
        'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
    ]);
    $body   = @file_get_contents($cfg['url'], false, $ctx);
    $status = 0;
    foreach ($http_response_header ?? [] as $cab) {
        if (preg_match('#^HTTP/\S+\s+(\d{3})#', $cab, $m) === 1) {
            $status = (int) $m[1];
        }
    }
    return ['status' => $status, 'body' => $body === false ? '' : $body];
}

/**
 * Linea JSON-RPC de error para un fallo HTTP o de red (nunca incluye cabeceras ni el token).
 *
 * @param array|null $msg    Mensaje enviado (null si no era un objeto JSON valido)
 * @param int        $status Codigo HTTP recibido (0 si hubo fallo de red)
 * @return string|null Linea de respuesta, o null si el mensaje era una notificacion (sin id)
 */
function stdio_error_line(?array $msg, int $status): ?string
{
    if ($msg === null || !array_key_exists('id', $msg)) {
        return null;
    }
    $mapa   = [401 => [-32001, 'Unauthorized'], 403 => [-32003, 'Forbidden'], 429 => [-32004, 'Too many requests']];
    [$code, $texto] = $mapa[$status] ?? [-32603, 'Internal error'];
    return json_encode(['jsonrpc' => '2.0', 'id' => $msg['id'], 'error' => ['code' => $code, 'message' => $texto]]);
}

/**********************************************************************************************************************************/
/*                                                        Bucle principal                                                         */
/**********************************************************************************************************************************/
// Solo al ejecutarlo como script (permite incluir el archivo para probar las funciones)
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $cfg     = stdio_config();
    $version = null;
    while (($line = fgets(STDIN)) !== false) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        $decoded = json_decode($line, true);
        $msg     = is_array($decoded) ? $decoded : null;
        $resp    = stdio_post($cfg, $line, $version);
        $salida  = null;
        if ($resp['status'] >= 200 && $resp['status'] < 300) {
            $body = trim($resp['body']);
            if ($resp['status'] !== 202 && $body !== '') {
                // Una linea por respuesta: si el cuerpo trae saltos de linea se recodifica en compacto
                $json   = json_decode($body, true);
                $salida = (strpos($body, "\n") === false || $json === null) ? $body : json_encode($json);
                if ($msg !== null && ($msg['method'] ?? null) === 'initialize' && is_string($json['result']['protocolVersion'] ?? null)) {
                    $version = $json['result']['protocolVersion'];
                }
            }
        } else {
            fwrite(STDERR, 'mcp-stdio: el servidor respondio HTTP ' . $resp['status'] . PHP_EOL);
            $salida = stdio_error_line($msg, $resp['status']);
        }
        if ($salida !== null) {
            fwrite(STDOUT, $salida . "\n");
            fflush(STDOUT);
        }
    }
}
