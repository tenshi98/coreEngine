<?php
defined('MCP_ROOT') || exit;
/**
 * Tool get_record: una fila de una tabla por su clave primaria.
 *
 * @see projects/mcp-servidor/specs/api.md §1.3
 */
class GetRecordTool extends AbstractTool
{
    /**
     * @param string[] $modulosEnAlcance Modulos que pasan allowsModule
     * @return array
     */
    public function definition(array $modulosEnAlcance): array
    {
        return [
            'name'        => 'get_record',
            'title'       => 'Obtener registro',
            'description' => 'Devuelve una fila de una tabla por su clave primaria. Solo lectura.',
            'inputSchema' => [
                'type'                 => 'object',
                'properties'           => [
                    'module' => $this->moduleProperty($modulosEnAlcance),
                    'table'  => ['type' => 'string', 'pattern' => '^[A-Za-z0-9_]{1,64}$'],
                    'id'     => ['type' => 'integer', 'minimum' => 1],
                ],
                'required'             => ['module', 'table', 'id'],
                'additionalProperties' => false,
            ],
            'annotations' => $this->annotations(),
        ];
    }

    /**
     * @param array $args module, table, id
     * @return array
     * @throws McpException -32602 por argumentos o tabla invalidos; -32603 si falla la consulta
     */
    public function execute(array $args): array
    {
        $this->validateArgs($this->definition($this->modulesInScope())['inputSchema'], $args);
        [$modulo, $tabla] = $this->resolveTable($args);
        $fila = $this->checkResult($this->qb->queryRow([
            'data'   => $this->selectColumns($tabla),
            'table'  => $tabla['tabla'],
            'where'  => '`' . $tabla['pk'] . '` = ?',
            'params' => [$args['id']],
        ], $this->db));
        if ($fila === []) {
            return $this->toError('Registro no encontrado');
        }
        return $this->toResult(['module' => $modulo['modulo'], 'table' => $tabla['tabla'], 'row' => $this->enrichUserNames($tabla, [$this->shapeRow($fila)])[0]]);
    }
}
