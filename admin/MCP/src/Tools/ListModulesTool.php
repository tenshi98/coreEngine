<?php
defined('MCP_ROOT') || exit;
/**
 * Tool list_modules: modulos accesibles con el token y sus tablas.
 *
 * @see projects/mcp-servidor/specs/api.md §1.3
 */
class ListModulesTool extends AbstractTool
{
    /**
     * @param string[] $modulosEnAlcance Modulos que pasan allowsModule
     * @return array
     */
    public function definition(array $modulosEnAlcance): array
    {
        return [
            'name'        => 'list_modules',
            'title'       => 'Listar módulos',
            'description' => 'Lista los módulos de coreEngine a los que tiene acceso este token, con sus tablas.',
            'inputSchema' => ['type' => 'object', 'properties' => (object) [], 'additionalProperties' => false],
            'annotations' => $this->annotations(),
        ];
    }

    /**
     * @param array $args Argumentos (ninguno)
     * @return array
     */
    public function execute(array $args): array
    {
        $this->validateArgs(['type' => 'object', 'additionalProperties' => false], $args);
        $modulos = [];
        foreach ($this->catalogo as $m) {
            if (!$this->scope->allowsModule($m)) {
                continue;
            }
            $tablas = [];
            foreach ($m['tablas'] as $nombre => $t) {
                if ($this->scope->allowsTable($t)) {
                    $tablas[] = (string) $nombre;
                }
            }
            $modulos[] = ['module' => $m['modulo'], 'name' => $m['nombre'], 'description' => $m['descripcion'], 'tables' => $tablas];
        }
        return $this->toResult(['modules' => $modulos]);
    }
}
