<?php
defined('MCP_ROOT') || exit;
/**
 * Autenticacion por token Bearer contra tokens_listado.
 *
 * El token es el unico factor: no se consulta ninguna tabla de usuarios (D-27).
 *
 * @see projects/mcp-servidor/specs/technical.md §7
 */
class TokenAuthenticator
{
    /** @var DB\SQL Conexion MySQL_MCP */
    private $db;

    /** @var QueryBuilder Constructor de consultas */
    private QueryBuilder $qb;

    public function __construct()
    {
        $this->db = McpDb::conn();
        $this->qb = new QueryBuilder();
    }

    /**
     * Extrae el token de la cabecera Authorization sin distinguir mayusculas (H-16).
     *
     * Fuentes: HTTP_AUTHORIZATION, REDIRECT_HTTP_AUTHORIZATION y apache_request_headers() (strcasecmp).
     *
     * @return string|null Token en minusculas, o null si falta, no es Bearer o no son 64 hexadecimales
     */
    public function extractBearer(): ?string
    {
        $valor = null;
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $valor = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $valor = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        } elseif (function_exists('apache_request_headers')) {
            foreach (apache_request_headers() as $nombre => $v) {
                if (strcasecmp((string) $nombre, 'Authorization') === 0) {
                    $valor = $v;
                    break;
                }
            }
        }
        if (!is_string($valor) || preg_match('/^Bearer\s+([A-Fa-f0-9]{64})$/i', trim($valor), $m) !== 1) {
            return null;
        }
        return strtolower($m[1]);
    }

    /**
     * Valida un token ya con formato correcto.
     *
     * Un token manual (Origen 1) sigue la validacion de siempre. Un acceso OAuth (Origen 2) ademas
     * no debe haber expirado y su cliente y su padre deben ser validos (validateChild).
     *
     * @param string $token Token de 64 hexadecimales en minusculas
     * @return array{estado: string, idToken: int, origen: int, idTokenPadre: int|null, idClienteOAuth: int|null}
     *         estado: inexistente, inactivo, caducado, expirado, padre, cliente u ok (en ese orden);
     *         origen, idTokenPadre e idClienteOAuth sirven a la cuota y la auditoria (0/null si no hay token)
     * @throws McpException -32603 si la consulta falla (sin datos del error, H-8)
     */
    public function validate(string $token): array
    {
        $r = $this->qb->queryRow([
            'data'   => 'idToken, Token, idEstado, Origen, idTokenPadre, idClienteOAuth, (FechaCaducidad IS NOT NULL AND FechaCaducidad < CURDATE()) AS Caducado, (FechaExpiracion IS NOT NULL AND FechaExpiracion < NOW()) AS Expirado',
            'table'  => 'tokens_listado',
            'where'  => 'Token = ?',
            'params' => [$token],
        ], $this->db);
        if (($r['status'] ?? false) !== true) {
            throw new McpException(-32603, 'Internal error');
        }
        $fila = $r['data'];
        if (!is_array($fila) || $fila === [] || !hash_equals((string) ($fila['Token'] ?? ''), $token)) {
            return ['estado' => 'inexistente', 'idToken' => 0, 'origen' => 0, 'idTokenPadre' => null, 'idClienteOAuth' => null];
        }
        $id      = (int) $fila['idToken'];
        $origen  = (int) ($fila['Origen'] ?? 1);
        $esHijo  = $origen === 2;
        $padre   = ($esHijo && isset($fila['idTokenPadre'])) ? (int) $fila['idTokenPadre'] : null;
        $cliente = ($esHijo && isset($fila['idClienteOAuth'])) ? (int) $fila['idClienteOAuth'] : null;
        $res     = static fn (string $estado): array => [
            'estado' => $estado, 'idToken' => $id, 'origen' => $origen, 'idTokenPadre' => $padre, 'idClienteOAuth' => $cliente,
        ];
        if ((int) $fila['idEstado'] !== 1) {
            return $res('inactivo');
        }
        if ((int) $fila['Caducado'] === 1) {
            return $res('caducado');
        }
        if ((int) ($fila['Expirado'] ?? 0) === 1) {
            return $res('expirado');
        }
        if ($esHijo) {
            return $res($this->validateChild($fila));
        }
        return $res('ok');
    }

    /**
     * Valida el cliente y el padre de un acceso OAuth (Origen 2).
     *
     * El cliente debe existir y estar activo; el padre debe existir, ser Origen 1, estar activo y no caducado.
     * Cualquier fallo se reduce al mismo 401 generico en index.php (H-15); aqui solo se distingue para auditar.
     *
     * @param array $fila Fila del hijo con idTokenPadre e idClienteOAuth
     * @return string 'cliente', 'padre' u 'ok' (el cliente se comprueba primero)
     * @throws McpException -32603 si una consulta falla (sin datos del error, H-8)
     */
    private function validateChild(array $fila): string
    {
        $idCliente = (int) ($fila['idClienteOAuth'] ?? 0);
        $idPadre   = (int) ($fila['idTokenPadre'] ?? 0);
        if ($idCliente <= 0) {
            return 'cliente';
        }
        $c = $this->qb->queryRow([
            'data'   => 'idCliente, idEstado',
            'table'  => 'tokens_oauth_clientes',
            'where'  => 'idCliente = ?',
            'params' => [$idCliente],
        ], $this->db);
        if (($c['status'] ?? false) !== true) {
            throw new McpException(-32603, 'Internal error');
        }
        $cliente = $c['data'];
        if (!is_array($cliente) || $cliente === [] || (int) ($cliente['idEstado'] ?? 0) !== 1) {
            return 'cliente';
        }
        if ($idPadre <= 0) {
            return 'padre';
        }
        $p = $this->qb->queryRow([
            'data'   => 'idToken, Origen, idEstado, (FechaCaducidad IS NOT NULL AND FechaCaducidad < CURDATE()) AS Caducado',
            'table'  => 'tokens_listado',
            'where'  => 'idToken = ?',
            'params' => [$idPadre],
        ], $this->db);
        if (($p['status'] ?? false) !== true) {
            throw new McpException(-32603, 'Internal error');
        }
        $padre = $p['data'];
        if (
            !is_array($padre) || $padre === []
            || (int) ($padre['Origen'] ?? 0) !== 1
            || (int) ($padre['idEstado'] ?? 0) !== 1
            || (int) ($padre['Caducado'] ?? 1) === 1
        ) {
            return 'padre';
        }
        return 'ok';
    }
}
