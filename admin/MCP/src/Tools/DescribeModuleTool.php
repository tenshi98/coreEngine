<?php
defined('MCP_ROOT') || exit;
/**
 * Tool describe_module: tablas visibles de un modulo con clave primaria y columnas.
 *
 * @see projects/mcp-servidor/specs/api.md §1.3
 */
class DescribeModuleTool extends AbstractTool
{
    /**
     * @param string[] $modulosEnAlcance Modulos que pasan allowsModule
     * @return array
     */
    public function definition(array $modulosEnAlcance): array
    {
        return [
            'name'        => 'describe_module',
            'title'       => 'Describir módulo',
            'description' => 'Describe las tablas visibles de un módulo: clave primaria, columnas, tipos y descripciones.',
            'inputSchema' => [
                'type'                 => 'object',
                'properties'           => ['module' => $this->moduleProperty($modulosEnAlcance, true)],
                'required'             => ['module'],
                'additionalProperties' => false,
            ],
            'annotations' => $this->annotations(),
        ];
    }

    /**
     * @param array $args module
     * @return array
     * @throws McpException -32602 si el modulo no esta en el alcance
     */
    public function execute(array $args): array
    {
        $this->validateArgs($this->definition($this->modulesInScope())['inputSchema'], $args);
        $m      = $this->catalogo[$args['module']];
        $tablas = [];
        foreach ($m['tablas'] as $t) {
            if (!$this->scope->allowsTable($t)) {
                continue;
            }
            $cols = [];
            foreach ($t['columnas'] as $c) {
                $cols[] = ['name' => $c['nombre'], 'type' => $c['tipo'], 'description' => $c['descripcion'], 'indexed' => $c['indexada']];
            }
            $tablas[] = ['table' => $t['tabla'], 'description' => $t['descripcion'], 'primaryKey' => $t['pk'], 'columns' => $cols];
        }
        return $this->toResult(['module' => $m['modulo'], 'name' => $m['nombre'], 'description' => $m['descripcion'], 'tables' => $tablas]);
    }
}
