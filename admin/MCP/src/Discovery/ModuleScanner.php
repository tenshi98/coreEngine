<?php
defined('MCP_ROOT') || exit;
/**
 * Descubrimiento de modulos, esquema y catalogo del servidor MCP.
 *
 * @see projects/mcp-servidor/specs/technical.md §8
 */
class ModuleScanner
{
    private const IDENT = '/^[A-Za-z0-9_]{1,64}$/';

    /**
     * Modulos con installer valido, sin los excluidos.
     *
     * @return array<string,string> [modulo => claseInstaller]
     */
    public function scanModules(): array
    {
        $modulos = [];
        $files   = glob(MCP_MODULES_PATH . '*/installer/*Installer.php') ?: [];
        foreach ($files as $file) {
            $modulo = basename(dirname(dirname($file)));
            if (in_array($modulo, McpConfig::EXCLUDED_MODULES, true)) {
                continue;
            }
            $clase = basename($file, '.php');
            if (class_exists($clase) && method_exists($clase, 'listTables')) {
                $modulos[$modulo] = $clase;
            }
        }
        return $modulos;
    }

    /**
     * Controladores de un modulo (basenames de controller/*.php).
     *
     * @param string $modulo Carpeta del modulo
     * @return string[]
     */
    public function listControllers(string $modulo): array
    {
        $files = glob(MCP_MODULES_PATH . $modulo . '/controller/*.php') ?: [];
        return array_map(static fn(string $f): string => basename($f, '.php'), $files);
    }

    /**
     * Indica si el modulo esta instalado: alguna ruta de core_permisos_listado_rutas usa un controlador suyo (D-21).
     *
     * No instancia el installer ni llama a ListDataModule.
     *
     * @param string $modulo Carpeta del modulo
     * @return bool
     * @throws McpException -32603 si la consulta falla
     */
    public function isInstalled(string $modulo): bool
    {
        $controladores = $this->listControllers($modulo);
        if ($controladores === []) {
            return false;
        }
        $r = (new QueryBuilder())->queryRow([
            'data'   => 'COUNT(*) AS Total',
            'table'  => 'core_permisos_listado_rutas',
            'where'  => 'Controller IN (' . implode(',', array_fill(0, count($controladores), '?')) . ')',
            'params' => $controladores,
        ], McpDb::conn());
        if (($r['status'] ?? false) !== true) {
            throw new McpException(-32603, 'Internal error');
        }
        return (int) ($r['data']['Total'] ?? 0) > 0;
    }

    /**
     * Instancia el installer sin ejecutar su constructor (no abre conexion MySQL_ADMIN; D-21, RT-005).
     *
     * @param string $clase Clase del installer
     * @return object
     */
    public function readInstaller(string $clase): object
    {
        return (new ReflectionClass($clase))->newInstanceWithoutConstructor();
    }

    /**
     * Columnas y tipos de un DDL de listTables().
     *
     * @param string $ddl Valor de 'data' de una entrada de listTables()
     * @return array<int,array{nombre:string,tipo:string}>
     */
    public function parseColumns(string $ddl): array
    {
        $columnas = [];
        if (preg_match_all('/`([A-Za-z0-9_]+)`\s+([a-z]+)/', $ddl, $m, PREG_SET_ORDER) > 0) {
            foreach ($m as $c) {
                $columnas[] = ['nombre' => $c[1], 'tipo' => $c[2]];
            }
        }
        return $columnas;
    }

    /**
     * Columnas citadas en los INDEX / UNIQUE INDEX de optimizeTables().
     *
     * @param object $installer Installer (sin constructor)
     * @return array<string,string[]> [tabla => columnas]; [] si no hay optimizeTables()
     */
    public function indexedColumns(object $installer): array
    {
        if (!method_exists($installer, 'optimizeTables')) {
            return [];
        }
        $out = [];
        foreach ((array) $installer->optimizeTables() as $sql) {
            if (!is_string($sql) || preg_match('/ALTER TABLE (\w+)/i', $sql, $t) !== 1) {
                continue;
            }
            if (preg_match_all('/(?:UNIQUE )?INDEX IF NOT EXISTS \w+ \(([^)]+)\)/i', $sql, $idx) > 0) {
                foreach ($idx[1] as $lista) {
                    foreach (explode(',', $lista) as $col) {
                        $col = trim($col);
                        if ($col !== '' && !in_array($col, $out[$t[1]] ?? [], true)) {
                            $out[$t[1]][] = $col;
                        }
                    }
                }
            }
        }
        return $out;
    }

    /**
     * Tablas del installer con sus columnas (sin permisos ni filtros).
     *
     * @param object $installer Installer (sin constructor)
     * @return array<int,array> Entradas con tabla, pk, descripcion y columnas (con indexada)
     */
    public function readTables(object $installer): array
    {
        $indexadas = $this->indexedColumns($installer);
        $tablas    = [];
        foreach ((array) $installer->listTables() as $def) {
            if (!is_array($def) || !isset($def['table'], $def['data'], $def['primaryKey'])) {
                continue;
            }
            $columnas = [];
            foreach ($this->parseColumns((string) $def['data']) as $c) {
                $columnas[] = [
                    'nombre'      => $c['nombre'],
                    'tipo'        => $c['tipo'],
                    'descripcion' => '',
                    'indexada'    => $c['nombre'] === $def['primaryKey'] || in_array($c['nombre'], $indexadas[$def['table']] ?? [], true),
                ];
            }
            $tablas[] = [
                'tabla'       => (string) $def['table'],
                'pk'          => (string) $def['primaryKey'],
                'descripcion' => '',
                'columnas'    => $columnas,
            ];
        }
        return $tablas;
    }

    /**
     * Tablas consultables de un modulo: las del installer tras el manifiesto y SchemaFilter, con pk visible.
     *
     * Cada tabla lleva la clave 'modulo' y ninguna de permisos (el alcance del token es por modulo, ADR-0019).
     *
     * @param string     $modulo     Carpeta del modulo
     * @param string     $clase      Clase del installer
     * @param array|null $manifiesto Manifiesto valido del modulo (ModuleManifest::load) o null si no hay
     * @return array<string,array> [tabla => {tabla, pk, descripcion, columnas, modulo}]
     */
    public function exposableTables(string $modulo, string $clase, ?array $manifiesto): array
    {
        $tablas = [];
        foreach ($this->readTables($this->readInstaller($clase)) as $t) {
            $mt = $manifiesto['tablas'][$t['tabla']] ?? null;
            if (!SchemaFilter::tableAllowed($t['tabla'], $t['columnas'], $mt)) {
                continue;
            }
            $columnas = SchemaFilter::filterColumns($t['tabla'], $t['columnas'], $mt);
            if (!in_array($t['pk'], array_column($columnas, 'nombre'), true)) {
                continue;
            }
            $t['descripcion'] = $mt['descripcion'] ?? '';
            $t['columnas']    = $columnas;
            $t['modulo']      = $modulo;
            $tablas[$t['tabla']] = $t;
        }
        return $tablas;
    }

    /**
     * Modulos que el MCP puede exponer, sin consultar instalacion ni BD: manifiesto valido y no oculto, con al menos una tabla.
     *
     * Fuente unica de la regla; la usan buildCatalog() y la matriz de permisos del token (ADR-0019).
     *
     * @return array<string,array> [modulo => {modulo, nombre, descripcion, controladores, tablas}]
     */
    public function buildExposable(): array
    {
        $out = [];
        foreach ($this->scanModules() as $modulo => $clase) {
            $controladores = $this->listControllers($modulo);
            $manifiesto    = ModuleManifest::load($modulo);
            if ($manifiesto === false || ($manifiesto['expuesto'] ?? true) === false) {
                continue;
            }
            $tablas = $this->exposableTables($modulo, $clase, $manifiesto);
            if ($tablas === []) {
                continue;
            }
            $out[$modulo] = [
                'modulo'        => $modulo,
                'nombre'        => $manifiesto['nombre'] ?? $modulo,
                'descripcion'   => $manifiesto['descripcion'] ?? '',
                'controladores' => $controladores,
                'tablas'        => $tablas,
            ];
        }
        return $out;
    }

    /**
     * Catalogo de modulos exponibles que ademas estan instalados (D-21).
     *
     * @return array Catalogo (estructura de specs/technical.md §8.1)
     * @throws McpException -32603 si falla la consulta de instalacion
     */
    public function buildCatalog(): array
    {
        $catalogo = [];
        foreach ($this->buildExposable() as $modulo => $m) {
            if (!$this->isInstalled($modulo)) {
                continue;
            }
            $catalogo[$modulo] = $m;
        }
        return $catalogo;
    }

    /**
     * Lee la cache del catalogo sin confiar en ella (H-10, D-33): revalida identificadores y la contrasta con el codigo.
     *
     * La cache solo aporta que modulos estan instalados y que tablas pasaron el filtro. El modulo debe existir y sus
     * controladores coincidir con los reales; cada tabla debe declararla el installer de su modulo con la misma pk;
     * el 'modulo' de cada tabla debe ser el del modulo (una cache antigua con 'permisos' se descarta); y se reaplica el
     * manifiesto real y SchemaFilter como en buildCatalog. No se compara 'columnas' con el DDL (limitacion aceptada).
     *
     * @param bool $aceptarCaducada true para aceptar una cache fuera de CACHE_TTL
     * @return array|null Catalogo o null si no existe, esta caducada (y no se acepta), es JSON invalido o algo no valida
     */
    public function loadCache(bool $aceptarCaducada = false): ?array
    {
        $file = McpConfig::CACHE_FILE;
        clearstatcache(true, $file);
        if (!is_file($file)) {
            return null;
        }
        $mtime = filemtime($file);
        if (!$aceptarCaducada && ($mtime === false || time() - $mtime > McpConfig::CACHE_TTL)) {
            return null;
        }
        $raw = @file_get_contents($file);
        $cat = $raw === false ? null : json_decode($raw, true, McpConfig::MAX_JSON_DEPTH);
        if (!is_array($cat)) {
            return null;
        }
        $clases = $this->scanModules();
        $out    = [];
        foreach ($cat as $modulo => $m) {
            if (!is_string($modulo) || !is_array($m) || !$this->ident($m['modulo'] ?? null) || $m['modulo'] !== $modulo
                || !is_string($m['nombre'] ?? null) || !is_string($m['descripcion'] ?? null)
                || !is_array($m['controladores'] ?? null) || !is_array($m['tablas'] ?? null) || !$this->ident($modulo)) {
                return null;
            }
            foreach ($m['controladores'] as $c) {
                if (!$this->ident($c)) {
                    return null;
                }
            }
            // Fuente de verdad del codigo: modulo existente, controladores reales y tablas del installer del modulo
            if (!isset($clases[$modulo])) {
                return null;
            }
            $reales = $this->listControllers($modulo);
            $dadas  = array_values($m['controladores']);
            sort($dadas);
            sort($reales);
            if ($dadas !== $reales) {
                return null;
            }
            $declaradas = [];
            foreach ($this->readTables($this->readInstaller($clases[$modulo])) as $d) {
                $declaradas[$d['tabla']] = $d['pk'];
            }
            $mf = ModuleManifest::load($modulo);
            if ($mf === false || ($mf['expuesto'] ?? true) === false) {
                continue;
            }
            $tablas = [];
            foreach ($m['tablas'] as $nombre => $t) {
                if (!is_string($nombre) || !is_array($t) || !$this->ident($nombre) || ($t['tabla'] ?? null) !== $nombre
                    || !$this->ident($t['pk'] ?? null) || ($t['modulo'] ?? null) !== $modulo
                    || array_key_exists('permiso', $t) || array_key_exists('permisos', $t)
                    || !is_string($t['descripcion'] ?? null) || !is_array($t['columnas'] ?? null)) {
                    return null;
                }
                if (!isset($declaradas[$nombre]) || $declaradas[$nombre] !== $t['pk']) {
                    return null;
                }
                $mt = $mf['tablas'][$nombre] ?? null;
                foreach ($t['columnas'] as $c) {
                    if (!is_array($c) || !$this->ident($c['nombre'] ?? null) || !is_string($c['tipo'] ?? null)
                        || !is_string($c['descripcion'] ?? null) || !is_bool($c['indexada'] ?? null)) {
                        return null;
                    }
                }
                // Reaplica el filtro con el manifiesto real: una cache manipulada no puede reintroducir tablas o columnas ocultas
                if (!SchemaFilter::tableAllowed($nombre, $t['columnas'], $mt)) {
                    continue;
                }
                $t['columnas'] = SchemaFilter::filterColumns($nombre, $t['columnas'], $mt);
                if (!in_array($t['pk'], array_column($t['columnas'], 'nombre'), true)) {
                    continue;
                }
                $t['descripcion'] = $mt['descripcion'] ?? '';
                $tablas[$nombre]  = $t;
            }
            if ($tablas !== []) {
                $m['tablas']      = $tablas;
                $m['nombre']      = $mf['nombre'] ?? $modulo;
                $m['descripcion'] = $mf['descripcion'] ?? '';
                $out[$modulo]     = $m;
            }
        }
        return $out;
    }

    /**
     * Guarda el catalogo con escritura atomica (temporal + chmod 0640 + rename). Un fallo se registra y no interrumpe.
     *
     * @param array $catalogo Catalogo a cachear
     */
    public function saveCache(array $catalogo): void
    {
        $tmp = McpConfig::CACHE_FILE . '.tmp.' . getmypid();
        $ok  = @file_put_contents($tmp, json_encode($catalogo)) !== false
            && @chmod($tmp, 0640)
            && @rename($tmp, McpConfig::CACHE_FILE);
        if (!$ok) {
            @unlink($tmp);
            error_log('MCP: no se pudo escribir la cache del catalogo');
        }
    }

    /**
     * Catalogo con cache, reconstruccion bajo bloqueo no bloqueante y respaldo con cache caducada (H-10).
     *
     * @return array
     */
    public function getCatalog(): array
    {
        $cat = $this->loadCache();
        if ($cat !== null) {
            return $cat;
        }
        $lock = @fopen(McpConfig::CACHE_FILE . '.lock', 'c');
        if ($lock !== false && flock($lock, LOCK_EX | LOCK_NB)) {
            try {
                $cat = $this->loadCache();
                if ($cat === null) {
                    $cat = $this->buildCatalog();
                    $this->saveCache($cat);
                }
            } finally {
                flock($lock, LOCK_UN);
                fclose($lock);
            }
            return $cat;
        }
        if ($lock !== false) {
            fclose($lock);
        }
        return $this->loadCache(true) ?? $this->buildCatalog();
    }

    /**
     * @param mixed $v Valor a comprobar
     * @return bool true si es un identificador ^[A-Za-z0-9_]{1,64}$
     */
    private function ident(mixed $v): bool
    {
        return is_string($v) && preg_match(self::IDENT, $v) === 1;
    }
}
