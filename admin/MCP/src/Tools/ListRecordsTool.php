<?php
defined('MCP_ROOT') || exit;
/**
 * Tool list_records: filas de una tabla con filtros de igualdad, orden y paginacion por cursor.
 *
 * @see projects/mcp-servidor/specs/api.md §1.3
 */
class ListRecordsTool extends AbstractTool
{
    /**
     * @param string[] $modulosEnAlcance Modulos que pasan allowsModule
     * @return array
     */
    public function definition(array $modulosEnAlcance): array
    {
        $ident = '^[A-Za-z0-9_]{1,64}$';
        return [
            'name'        => 'list_records',
            'title'       => 'Listar registros',
            'description' => 'Lista filas de una tabla de un módulo, con filtros de igualdad, orden y paginación por cursor. Solo lectura.',
            'inputSchema' => [
                'type'                 => 'object',
                'properties'           => [
                    'module'    => $this->moduleProperty($modulosEnAlcance),
                    'table'     => ['type' => 'string', 'pattern' => $ident],
                    'filters'   => ['type' => 'object', 'maxProperties' => McpConfig::MAX_FILTERS, 'propertyNames' => ['pattern' => $ident], 'additionalProperties' => ['type' => ['string', 'number', 'boolean']], 'description' => 'Columna visible => valor exacto (se compara como texto)'],
                    'order_by'  => ['type' => 'string', 'pattern' => $ident, 'description' => 'Clave primaria o columna indexada (describe_module la marca con indexed); por defecto la clave primaria. Siempre desempata por la clave primaria'],
                    'order_dir' => ['type' => 'string', 'enum' => ['asc', 'desc'], 'default' => 'desc'],
                    'limit'     => ['type' => 'integer', 'minimum' => 1, 'maximum' => McpConfig::MAX_LIMIT, 'default' => McpConfig::DEFAULT_LIMIT],
                    'cursor'    => ['type' => 'string', 'maxLength' => 64, 'description' => 'nextCursor de la llamada anterior'],
                ],
                'required'             => ['module', 'table'],
                'additionalProperties' => false,
            ],
            'annotations' => $this->annotations(),
        ];
    }

    /**
     * Condiciones de igualdad sobre columnas visibles, con valores como string (H-20, BD 6).
     *
     * @param array $tabla   Entrada de la tabla del catalogo
     * @param array $filters Columna => valor
     * @return array{0: string, 1: array} [where con placeholders, valores]
     * @throws McpException -32602 si una columna no es visible o el texto no es latin1
     */
    private function buildWhere(array $tabla, array $filters): array
    {
        $partes = [];
        $params = [];
        foreach ($filters as $col => $valor) {
            if ($this->findColumn($tabla, (string) $col) === null) {
                throw new McpException(-32602, 'Invalid params', ['param' => 'filters.' . $col]);
            }
            $valor = is_bool($valor) ? ($valor ? '1' : '0') : (string) $valor;
            $this->assertLatin1($valor);
            $partes[] = '`' . $col . '` = ?';
            $params[] = $valor;
        }
        return [implode(' AND ', $partes), $params];
    }

    /**
     * @param array $args module, table, filters, order_by, order_dir, limit, cursor
     * @return array
     * @throws McpException -32602 por argumentos, tabla, columna, orden o cursor invalidos; -32603 si falla la consulta
     */
    public function execute(array $args): array
    {
        $this->validateArgs($this->definition($this->modulesInScope())['inputSchema'], $args);
        [$modulo, $tabla] = $this->resolveTable($args);
        [$where, $params] = $this->buildWhere($tabla, $args['filters'] ?? []);

        $orderBy = $args['order_by'] ?? $tabla['pk'];
        $col     = $this->findColumn($tabla, $orderBy);
        if ($col === null || ($orderBy !== $tabla['pk'] && $col['indexada'] !== true)) {
            throw new McpException(-32602, 'Invalid params', ['param' => 'order_by']);
        }
        $dir   = strtolower($args['order_dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
        $order = '`' . $orderBy . '` ' . $dir . ($orderBy === $tabla['pk'] ? '' : ', `' . $tabla['pk'] . '` ' . $dir);

        $offset = $this->decodeCursor($args['cursor'] ?? null);
        $limit  = (int) ($args['limit'] ?? McpConfig::DEFAULT_LIMIT);
        return $this->toResult($this->paginate($modulo, $tabla, $where, $params, $order, $offset, $limit));
    }
}
