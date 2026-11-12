<?php
defined('MCP_ROOT') || exit;
/**
 * Alcance efectivo de un token: los modulos asignados en tokens_listado_permisos (ADR-0019, sustituye a D-27 y D-25).
 * Un acceso OAuth (hijo) hereda los del token padre; un token manual, los suyos.
 *
 * @see projects/tokens-permisos-modulo/specs/technical.md §2
 */
class TokenScope
{
    /** @var array<string,true> Nombres de los modulos del alcance */
    private array $modulos = [];

    /**
     * @param string[] $modulos Nombres de los modulos del alcance
     */
    public function __construct(array $modulos = [])
    {
        foreach ($modulos as $modulo) {
            $this->modulos[(string) $modulo] = true;
        }
    }

    /**
     * Carga el alcance de un token: los modulos de sus filas en tokens_listado_permisos (sin nivel ni estado).
     *
     * El alcance se resuelve sobre COALESCE(idTokenPadre, idToken): un hijo usa los modulos de su padre.
     *
     * @param int $idToken Id del token autenticado (manual u hijo OAuth)
     * @return TokenScope
     * @throws McpException -32603 si la consulta falla (sin datos del error, H-8)
     */
    public static function load(int $idToken): TokenScope
    {
        $qb = new QueryBuilder();
        $a  = $qb->queryRow([
            'data'   => 'COALESCE(idTokenPadre, idToken) AS idAlcance',
            'table'  => 'tokens_listado',
            'where'  => 'idToken = ?',
            'params' => [$idToken],
        ], McpDb::conn());
        if (($a['status'] ?? false) !== true) {
            throw new McpException(-32603, 'Internal error');
        }
        if (!is_array($a['data']) || $a['data'] === [] || !isset($a['data']['idAlcance'])) {
            return new TokenScope();
        }
        $idAlcance = (int) $a['data']['idAlcance'];
        $r = $qb->queryArray([
            'data'   => 'Modulo',
            'table'  => 'tokens_listado_permisos',
            'where'  => 'idToken = ?',
            'params' => [$idAlcance],
            'limit'  => ConfigAPP::APP['N_MaxItems'],
        ], McpDb::conn());
        if (($r['status'] ?? false) !== true) {
            throw new McpException(-32603, 'Internal error');
        }
        return new TokenScope(array_column($r['data'], 'Modulo'));
    }

    /**
     * @return bool true si el token no tiene ningun modulo asignado
     */
    public function isEmpty(): bool
    {
        return $this->modulos === [];
    }

    /**
     * La tabla es accesible si su modulo esta en el alcance (RF-010, ADR-0019).
     *
     * @param array $tabla Entrada de tabla del catalogo con la clave 'modulo'
     * @return bool
     */
    public function allowsTable(array $tabla): bool
    {
        $m = $tabla['modulo'] ?? null;
        return is_string($m) && isset($this->modulos[$m]);
    }

    /**
     * El modulo es accesible si esta en el alcance (ya no depende de sus tablas).
     *
     * @param array $modulo Entrada del catalogo con la clave 'modulo'
     * @return bool
     */
    public function allowsModule(array $modulo): bool
    {
        $m = $modulo['modulo'] ?? null;
        return is_string($m) && isset($this->modulos[$m]);
    }
}
