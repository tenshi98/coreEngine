<?php
defined('MCP_ROOT') || exit;
/**
 * Repositorio de codigos de autorizacion (tokens_oauth_codigos).
 *
 * El codigo (256 bits) solo se devuelve al llamador; en BD se guarda su SHA-256. Un solo uso, 60 s.
 *
 * @see projects/mcp-oauth/specs/technical.md §10
 */
class OAuthCodeRepository
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
     * Comprueba el resultado de QueryBuilder y devuelve sus datos.
     *
     * @param array<string,mixed> $r Respuesta de QueryBuilder
     * @return mixed Campo data de la respuesta
     * @throws RuntimeException Si status no es true (sin reenviar error, internal_error ni query)
     */
    private function check(array $r): mixed
    {
        if (($r['status'] ?? false) !== true) {
            throw new RuntimeException('OAuth: fallo de base de datos');
        }
        return $r['data'] ?? null;
    }

    /**
     * Emite un codigo de autorizacion.
     *
     * @param array<string,mixed> $req       Peticion validada: redirect_uri (efectiva), code_challenge y resource
     * @param int                 $idCliente Cliente
     * @param int                 $idToken   Token manual verificado (futuro idTokenPadre)
     * @return string Codigo en claro (64 hexadecimales); en BD solo su hash
     * @throws RuntimeException Si falla la base de datos
     */
    public function issue(array $req, int $idCliente, int $idToken): string
    {
        $codigo = OAuthCrypto::randomHex(32);
        $this->check($this->qb->queryExecute(
            'INSERT INTO tokens_oauth_codigos (CodigoHash, idCliente, idToken, RedirectUri, CodeChallenge, Resource, Scope, FechaCreacion, FechaExpiracion, Usado) '
            . 'VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? SECOND), 0)',
            $this->db,
            false,
            false,
            [
                OAuthCrypto::hash($codigo),
                $idCliente,
                $idToken,
                (string) $req['redirect_uri'],
                (string) $req['code_challenge'],
                (string) $req['resource'],
                McpConfig::OAUTH_SCOPE,
                McpConfig::OAUTH_CODE_TTL,
            ]
        ));
        return $codigo;
    }

    /**
     * Consume un codigo (bloquea la fila con FOR UPDATE; la transaccion es del llamador).
     *
     * @param string $code Codigo recibido en /token
     * @return array{estado: string, idCodigo: int, idCliente: int, idToken: int, RedirectUri: string,
     *         CodeChallenge: string, Resource: string, Scope: string, idTokenAcceso: ?int}|null null si el formato es
     *         invalido o no existe. estado: reusado (Usado = 1, tiene prioridad), expirado u ok (el codigo
     *         queda marcado Usado = 1)
     * @throws RuntimeException Si falla la base de datos
     */
    public function consume(string $code): ?array
    {
        if (preg_match('/^[a-f0-9]{64}$/D', $code) !== 1) {
            return null;
        }
        $fila = $this->check($this->qb->queryExecute(
            'SELECT idCodigo, idCliente, idToken, RedirectUri, CodeChallenge, Resource, Scope, Usado, idTokenAcceso, '
            . '(FechaExpiracion < NOW()) AS Expirado FROM tokens_oauth_codigos WHERE CodigoHash = ? FOR UPDATE',
            $this->db,
            false,
            true,
            [OAuthCrypto::hash($code)]
        ));
        if (!is_array($fila) || $fila === []) {
            return null;
        }
        if ((int) $fila['Usado'] === 1) {
            $estado = 'reusado';
        } elseif ((int) $fila['Expirado'] === 1) {
            $estado = 'expirado';
        } else {
            $this->check($this->qb->queryExecute(
                'UPDATE tokens_oauth_codigos SET Usado = 1 WHERE idCodigo = ?',
                $this->db,
                false,
                false,
                [(int) $fila['idCodigo']]
            ));
            $estado = 'ok';
        }
        return [
            'estado'        => $estado,
            'idCodigo'      => (int) $fila['idCodigo'],
            'idCliente'     => (int) $fila['idCliente'],
            'idToken'       => (int) $fila['idToken'],
            'RedirectUri'   => (string) $fila['RedirectUri'],
            'CodeChallenge' => (string) $fila['CodeChallenge'],
            'Resource'      => (string) $fila['Resource'],
            'Scope'         => (string) $fila['Scope'],
            'idTokenAcceso' => $fila['idTokenAcceso'] !== null ? (int) $fila['idTokenAcceso'] : null,
        ];
    }

    /**
     * Enlaza el codigo con el acceso emitido (permite revocarlo si el codigo se reutiliza, D-18).
     *
     * @param int $idCodigo      Id del codigo
     * @param int $idTokenAcceso Fila hija emitida
     * @return void
     * @throws RuntimeException Si falla la base de datos
     */
    public function attachAccess(int $idCodigo, int $idTokenAcceso): void
    {
        $this->check($this->qb->queryExecute(
            'UPDATE tokens_oauth_codigos SET idTokenAcceso = ? WHERE idCodigo = ?',
            $this->db,
            false,
            false,
            [$idTokenAcceso, $idCodigo]
        ));
    }
}
