<?php
defined('MCP_ROOT') || exit;
/**
 * Registro de resources del servidor MCP: el esquema de cada modulo del alcance.
 *
 * @see projects/mcp-servidor/specs/technical.md §10
 */
class ResourceRegistry
{
    /** @var array Catalogo de modulos */
    private array $catalogo;

    /** @var TokenScope Alcance del token */
    private TokenScope $scope;

    /**
     * @param array      $catalogo Catalogo de modulos
     * @param TokenScope $scope    Alcance del token
     */
    public function __construct(array $catalogo, TokenScope $scope)
    {
        $this->catalogo = $catalogo;
        $this->scope    = $scope;
    }

    /**
     * Un resource por modulo del alcance.
     *
     * @return array
     */
    public function listResources(): array
    {
        $out = [];
        foreach ($this->catalogo as $nombre => $m) {
            if (!$this->scope->allowsModule($m)) {
                continue;
            }
            $out[] = [
                'uri'         => 'mcp://modules/' . $nombre . '/schema',
                'name'        => $nombre . '-schema',
                'title'       => 'Esquema de ' . $m['nombre'],
                'description' => 'Tablas, claves primarias y columnas visibles del módulo ' . $nombre . '.',
                'mimeType'    => 'application/json',
            ];
        }
        return $out;
    }

    /**
     * @return array Plantilla unica de resources
     */
    public function listTemplates(): array
    {
        return [[
            'uriTemplate' => 'mcp://modules/{modulo}/schema',
            'name'        => 'module-schema',
            'title'       => 'Esquema de un módulo',
            'description' => 'Tablas, claves primarias y columnas visibles de un módulo del alcance del token.',
            'mimeType'    => 'application/json',
        ]];
    }

    /**
     * Lee el esquema de un modulo (el mismo objeto que describe_module).
     *
     * @param string $uri mcp://modules/<modulo>/schema
     * @return array ['contents' => [...]]
     * @throws McpException -32002 'Resource not found' si la URI no es valida o el modulo esta fuera del alcance
     */
    public function read(string $uri): array
    {
        if (preg_match('/^mcp:\/\/modules\/([A-Za-z0-9_]+)\/schema$/', $uri, $m) !== 1
            || !isset($this->catalogo[$m[1]]) || !$this->scope->allowsModule($this->catalogo[$m[1]])) {
            throw new McpException(-32002, 'Resource not found', ['uri' => $uri]);
        }
        $esquema = (new DescribeModuleTool($this->catalogo, $this->scope))->execute(['module' => $m[1]])['structuredContent'];
        return ['contents' => [[
            'uri'      => $uri,
            'mimeType' => 'application/json',
            'text'     => json_encode($esquema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE),
        ]]];
    }
}
