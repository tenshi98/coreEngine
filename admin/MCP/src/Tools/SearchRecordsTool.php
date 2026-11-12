<?php
defined('MCP_ROOT') || exit;
/**
 * Tool search_records: filas cuyo texto contiene un termino en las columnas de texto visibles.
 *
 * @see projects/mcp-servidor/specs/api.md §1.3
 */
class SearchRecordsTool extends AbstractTool
{
    private const TEXT_TYPES = ['char', 'varchar', 'text', 'tinytext', 'mediumtext', 'longtext'];

    /**
     * @param string[] $modulosEnAlcance Modulos que pasan allowsModule
     * @return array
     */
    public function definition(array $modulosEnAlcance): array
    {
        $ident = '^[A-Za-z0-9_]{1,64}$';
        return [
            'name'        => 'search_records',
            'title'       => 'Buscar registros',
            'description' => 'Busca filas cuyo texto contiene un término en las columnas de texto visibles de una tabla. Solo lectura.',
            'inputSchema' => [
                'type'                 => 'object',
                'properties'           => [
                    'module'  => $this->moduleProperty($modulosEnAlcance),
                    'table'   => ['type' => 'string', 'pattern' => $ident],
                    'query'   => ['type' => 'string', 'minLength' => McpConfig::SEARCH_MIN, 'maxLength' => McpConfig::SEARCH_MAX],
                    'columns' => ['type' => 'array', 'items' => ['type' => 'string', 'pattern' => $ident], 'maxItems' => McpConfig::MAX_SEARCH_COLUMNS, 'description' => 'Subconjunto de columnas de texto visibles; por defecto todas'],
                    'limit'   => ['type' => 'integer', 'minimum' => 1, 'maximum' => McpConfig::MAX_LIMIT, 'default' => McpConfig::DEFAULT_LIMIT],
                    'cursor'  => ['type' => 'string', 'maxLength' => 64],
                ],
                'required'             => ['module', 'table', 'query'],
                'additionalProperties' => false,
            ],
            'annotations' => $this->annotations(),
        ];
    }

    /**
     * Condicion LIKE sobre las columnas de texto visibles (o el subconjunto pedido).
     *
     * @param array      $tabla   Entrada de la tabla del catalogo
     * @param string     $query   Termino de busqueda
     * @param array|null $columns Subconjunto de columnas (null = todas las de texto)
     * @return array{0: string, 1: array} [where con placeholders, valores]
     * @throws McpException -32602 si no hay columnas de texto, una columna pedida no es visible o el texto no es latin1
     */
    private function buildSearchWhere(array $tabla, string $query, ?array $columns): array
    {
        $texto = [];
        foreach ($tabla['columnas'] as $c) {
            if (in_array($c['tipo'], self::TEXT_TYPES, true)) {
                $texto[] = $c['nombre'];
            }
        }
        if ($columns !== null) {
            foreach ($columns as $col) {
                if (!in_array($col, $texto, true)) {
                    throw new McpException(-32602, 'Invalid params', ['param' => 'columns']);
                }
            }
            $texto = array_values(array_unique($columns));
        }
        if ($texto === []) {
            throw new McpException(-32602, 'Invalid params', ['param' => 'table']);
        }
        $this->assertLatin1($query);
        $term   = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query) . '%';
        $partes = [];
        foreach ($texto as $col) {
            $partes[] = '`' . $col . "` LIKE ? ESCAPE '\\\\'";
        }
        return ['(' . implode(' OR ', $partes) . ')', array_fill(0, count($texto), $term)];
    }

    /**
     * @param array $args module, table, query, columns, limit, cursor
     * @return array
     * @throws McpException -32602 por argumentos, tabla, columnas o cursor invalidos; -32603 si falla la consulta
     */
    public function execute(array $args): array
    {
        $this->validateArgs($this->definition($this->modulesInScope())['inputSchema'], $args);
        [$modulo, $tabla] = $this->resolveTable($args);
        [$where, $params] = $this->buildSearchWhere($tabla, $args['query'], $args['columns'] ?? null);
        $offset = $this->decodeCursor($args['cursor'] ?? null);
        $limit  = (int) ($args['limit'] ?? McpConfig::DEFAULT_LIMIT);
        return $this->toResult($this->paginate($modulo, $tabla, $where, $params, '`' . $tabla['pk'] . '` DESC', $offset, $limit));
    }
}
