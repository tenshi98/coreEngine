<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Modulos que el MCP puede exponer, para la matriz de permisos del token y la migracion del installer (ADR-0019).
 *
 * Helper de modulo sin herencia ni BD. Reutiliza ModuleScanner (fuente unica de la regla de exposicion) tras
 * definir MCP_ROOT y MCP_MODULES_PATH con los mismos valores que admin/MCP/bootstrap.php; no carga ese bootstrap
 * (registra ErrorHandler y reinstancia F3). Nunca llama a buildCatalog(), isInstalled() ni McpDb: el backoffice
 * no tiene la cuenta MySQL_MCP. Por eso no lleva el guard defined('MCP_ROOT').
 *
 * @see projects/tokens-permisos-modulo/specs/technical.md §3
 */
class InternalFunctionsTokensModulos {

    /*******************************************************************/
    // Metodos
    /*******************************************************************/
    /**
     * Define las constantes del MCP y carga las clases del descubrimiento (idempotente).
     *
     * @return void
     */
    private static function cargarMcp(): void {
        $admin = dirname(__DIR__, 4);
        defined('MCP_ROOT') || define('MCP_ROOT', $admin . '/MCP');
        defined('MCP_MODULES_PATH') || define('MCP_MODULES_PATH', $admin . '/app/modules/');
        require_once MCP_ROOT . '/config/McpConfig.php';
        require_once MCP_ROOT . '/src/Discovery/SchemaFilter.php';
        require_once MCP_ROOT . '/src/Discovery/ModuleManifest.php';
        require_once MCP_ROOT . '/src/Discovery/ModuleScanner.php';
    }

    /**
     * Modulos exponibles por el MCP (sin consultar instalacion ni BD), ordenados por nombre.
     *
     * @return array<string,array{modulo:string,nombre:string,descripcion:string,controladores:string[]}> [modulo => datos]
     */
    public static function listExposable(): array {
        self::cargarMcp();
        $out = [];
        foreach ((new ModuleScanner())->buildExposable() as $modulo => $m) {
            $out[$modulo] = [
                'modulo'        => $m['modulo'],
                'nombre'        => $m['nombre'],
                'descripcion'   => $m['descripcion'],
                'controladores' => $m['controladores'],
            ];
        }
        uasort($out, static fn(array $a, array $b): int => strcmp(mb_strtolower($a['nombre']), mb_strtolower($b['nombre'])) ?: strcmp($a['modulo'], $b['modulo']));
        return $out;
    }
}
