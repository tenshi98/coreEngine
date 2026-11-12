<?php
defined('MCP_ROOT') || exit;
/**
 * Registro de las tools del servidor MCP.
 *
 * @see projects/mcp-servidor/specs/technical.md §9
 */
class ToolRegistry
{
    /** @var array<string,AbstractTool> Tools indexadas por nombre */
    private array $tools = [];

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
        foreach ([ListModulesTool::class, DescribeModuleTool::class, ListRecordsTool::class, SearchRecordsTool::class, GetRecordTool::class] as $clase) {
            $tool = new $clase($catalogo, $scope);
            $this->tools[$tool->definition([])['name']] = $tool;
        }
    }

    /**
     * Definiciones de las tools con module.enum = modulos del alcance.
     *
     * @return array
     */
    public function listTools(): array
    {
        $modulos = [];
        foreach ($this->catalogo as $nombre => $modulo) {
            if ($this->scope->allowsModule($modulo)) {
                $modulos[] = (string) $nombre;
            }
        }
        return array_values(array_map(static fn(AbstractTool $t): array => $t->definition($modulos), $this->tools));
    }

    /**
     * Ejecuta una tool por nombre.
     *
     * @param string $name Nombre de la tool
     * @param array  $args Argumentos
     * @return array Resultado MCP
     * @throws McpException -32602 'Unknown tool' si el nombre no existe
     */
    public function call(string $name, array $args): array
    {
        if (!isset($this->tools[$name])) {
            throw new McpException(-32602, 'Unknown tool');
        }
        return $this->tools[$name]->execute($args);
    }
}
