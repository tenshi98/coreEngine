<?php
defined('MCP_ROOT') || exit;
/**
 * Manifiesto opcional mcp.php de un modulo (ADR-0011).
 *
 * @see projects/mcp-servidor/specs/technical.md §8.4
 */
class ModuleManifest
{
    private const ROOT_KEYS  = ['version', 'expuesto', 'nombre', 'descripcion', 'tablas'];
    private const TABLE_KEYS = ['expuesta', 'descripcion', 'columnas_ocultas', 'columnas_descripcion'];

    /**
     * Carga el manifiesto mcp.php de un modulo aislado de efectos secundarios (H-14).
     *
     * @param string $modulo Nombre de carpeta del modulo (viene del glob, nunca de la entrada)
     * @return array|null|false null si no existe mcp.php; el array si es valido; false si es invalido,
     *                          lanza una excepcion o imprime salida
     */
    public static function load(string $modulo): array|null|false
    {
        $ruta = MCP_MODULES_PATH . $modulo . '/mcp.php';
        if (!is_file($ruta)) {
            return null;
        }
        $nivel = ob_get_level();
        ob_start();
        try {
            $m = self::incluir($ruta);
        } catch (Throwable $e) {
            while (ob_get_level() > $nivel) {
                ob_end_clean();
            }
            return false;
        }
        $salida = '';
        while (ob_get_level() > $nivel) {
            $salida = ob_get_clean() . $salida;
        }
        if ($salida !== '') {
            return false;
        }
        return self::validate($m) ? $m : false;
    }

    /**
     * Incluye el archivo en un ambito aislado (sin acceso a variables del llamador).
     *
     * @param string $ruta Ruta absoluta de mcp.php
     * @return mixed Valor devuelto por el archivo
     */
    private static function incluir(string $ruta): mixed
    {
        return include $ruta;
    }

    /**
     * Valida el formato cerrado del manifiesto.
     *
     * @param mixed $m Valor devuelto por mcp.php
     * @return bool false ante clave desconocida (incluida 'permiso', ADR-0019), tipo erroneo, version != 1 o tabla invalida
     */
    public static function validate(mixed $m): bool
    {
        if (!is_array($m) || !array_key_exists('version', $m) || $m['version'] !== 1) {
            return false;
        }
        foreach ($m as $k => $v) {
            if (!is_string($k) || !in_array($k, self::ROOT_KEYS, true)) {
                return false;
            }
        }
        if (isset($m['expuesto']) && !is_bool($m['expuesto'])) {
            return false;
        }
        foreach (['nombre', 'descripcion'] as $k) {
            if (array_key_exists($k, $m) && !is_string($m[$k])) {
                return false;
            }
        }
        if (array_key_exists('tablas', $m)) {
            if (!is_array($m['tablas'])) {
                return false;
            }
            foreach ($m['tablas'] as $tabla => $def) {
                if (!is_string($tabla) || preg_match('/^[A-Za-z0-9_]{1,64}$/', $tabla) !== 1 || !self::validTable($def)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @param mixed $t Definicion de una tabla
     */
    private static function validTable(mixed $t): bool
    {
        if (!is_array($t)) {
            return false;
        }
        foreach ($t as $k => $v) {
            if (!is_string($k) || !in_array($k, self::TABLE_KEYS, true)) {
                return false;
            }
        }
        if (array_key_exists('expuesta', $t) && !is_bool($t['expuesta'])) {
            return false;
        }
        if (array_key_exists('descripcion', $t) && !is_string($t['descripcion'])) {
            return false;
        }
        if (array_key_exists('columnas_ocultas', $t)) {
            if (!is_array($t['columnas_ocultas'])) {
                return false;
            }
            foreach ($t['columnas_ocultas'] as $c) {
                if (!is_string($c)) {
                    return false;
                }
            }
        }
        if (array_key_exists('columnas_descripcion', $t)) {
            if (!is_array($t['columnas_descripcion'])) {
                return false;
            }
            foreach ($t['columnas_descripcion'] as $c => $d) {
                if (!is_string($c) || !is_string($d)) {
                    return false;
                }
            }
        }
        return true;
    }
}
