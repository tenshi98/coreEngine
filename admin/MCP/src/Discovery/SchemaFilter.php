<?php
defined('MCP_ROOT') || exit;
/**
 * Filtro de tablas y columnas expuestas por el MCP.
 *
 * @see projects/mcp-servidor/specs/technical.md §8.5
 */
class SchemaFilter
{
    /**
     * Indica si una tabla puede exponerse.
     *
     * @param string     $tabla           Nombre de la tabla
     * @param array      $columnas        Columnas: nombres (string) o entradas con clave 'nombre'
     * @param array|null $manifiestoTabla Definicion de la tabla en el manifiesto, si existe
     * @return bool
     */
    public static function tableAllowed(string $tabla, array $columnas, ?array $manifiestoTabla): bool
    {
        foreach (McpConfig::BLACKLIST_TABLES as $patron) {
            if (preg_match($patron, $tabla) === 1) {
                return false;
            }
        }
        if (in_array($tabla, McpConfig::HIDDEN_TABLES, true)) {
            return false;
        }
        if ($manifiestoTabla !== null && ($manifiestoTabla['expuesta'] ?? true) === false) {
            return false;
        }
        return true;
    }

    /**
     * Quita las columnas de cuenta, secretas, PII y ocultas por manifiesto; anade sus descripciones.
     *
     * HIDDEN_COLUMNS y ACCOUNT_COLUMN ocultan siempre. PII_EXEMPT[$tabla] anula PII_COLUMNS (con 'todas' o
     * por nombre en 'columnas') y los patrones 'liberar' de BLACKLIST_COLUMNS, y mantiene ocultos los patrones 'ocultar'.
     *
     * @param string     $tabla           Nombre de la tabla
     * @param array      $columnas        Entradas ['nombre' => ..., 'tipo' => ..., 'descripcion' => ..., 'indexada' => ...]
     * @param array|null $manifiestoTabla Definicion de la tabla en el manifiesto, si existe
     * @return array Lista reindexada de columnas visibles
     */
    public static function filterColumns(string $tabla, array $columnas, ?array $manifiestoTabla): array
    {
        $ocultas = $manifiestoTabla['columnas_ocultas'] ?? [];
        $desc    = $manifiestoTabla['columnas_descripcion'] ?? [];
        $exento  = McpConfig::PII_EXEMPT[$tabla] ?? null;
        $liberar = $exento['liberar'] ?? [];
        $forzar  = $exento['ocultar'] ?? [];
        $todas   = ($exento['todas'] ?? false) === true;
        $nombres = $exento['columnas'] ?? [];
        $out     = [];
        foreach ($columnas as $col) {
            $nombre = self::name($col);
            if (in_array($nombre, McpConfig::HIDDEN_COLUMNS, true)) {
                continue;
            }
            if (in_array($nombre, $ocultas, true) || preg_match(McpConfig::ACCOUNT_COLUMN, $nombre) === 1) {
                continue;
            }
            foreach ($forzar as $patron) {
                if (preg_match($patron, $nombre) === 1) {
                    continue 2;
                }
            }
            foreach (McpConfig::BLACKLIST_COLUMNS as $patron) {
                if (!in_array($patron, $liberar, true) && preg_match($patron, $nombre) === 1) {
                    continue 2;
                }
            }
            if (!$todas && !in_array($nombre, $nombres, true)) {
                foreach (McpConfig::PII_COLUMNS as $patron) {
                    if (preg_match($patron, $nombre) === 1) {
                        continue 2;
                    }
                }
            }
            if (is_array($col) && isset($desc[$nombre]) && is_string($desc[$nombre])) {
                $col['descripcion'] = $desc[$nombre];
            }
            $out[] = $col;
        }
        return $out;
    }

    /**
     * @param mixed $col Columna (string o array con 'nombre')
     */
    private static function name(mixed $col): string
    {
        return is_array($col) ? (string) ($col['nombre'] ?? '') : (string) $col;
    }
}
