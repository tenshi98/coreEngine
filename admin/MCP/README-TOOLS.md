# Cómo crear una tool específica en el MCP (ejemplo: `stock_por_bodega`)

Guía para añadir una tool propia a `admin/MCP/`, además de las 5 genéricas (`list_modules`, `describe_module`, `list_records`, `search_records`, `get_record`). El ejemplo es `stock_por_bodega`: devuelve el stock con **nombre de bodega, nombre de producto y cantidades** en una sola llamada, en lugar de que la IA haga tres consultas y cruce ids.

> **Estado del ejemplo:** `stock_por_bodega` **no existe en el código**; es un diseño ilustrativo. Las clases que usa (`AbstractTool`, `ToolRegistry`, `McpConfig`, `TokenScope`) sí existen y son las descritas aquí. Antes de implementarlo de verdad, es un cambio de alcance del MCP: seguir el flujo SDD (`AGENTS.md` §1) y dejar el doble registro en `docs/history/` y `docs/change_logs/`.

Reglas del servidor que **no se pueden saltar** (ver `README.md`, secciones "Acceso por módulo" y "Seguridad y límites"): solo lectura, acceso por módulo según el alcance del token, columnas ocultas y PII filtradas por `SchemaFilter`, errores genéricos `-32603`, `max_statement_time` 5 s.

## 1. Cuándo conviene una tool específica

Una tool nueva se justifica cuando la respuesta útil **cruza varias tablas** o aplica una regla de negocio que los filtros de igualdad de `list_records` no permiten. No se justifica si basta con `list_records` y un filtro.

| Caso | Tool genérica | Tool específica |
|---|---|---|
| Filas de una tabla por `idProducto` | `list_records` | no hace falta |
| Stock con nombre de bodega y de producto | 3 llamadas y cruce en la IA | `stock_por_bodega` |

## 2. Piezas que hay que tocar

| Pieza | Archivo | Qué se hace |
|---|---|---|
| La tool | `src/Tools/StockPorBodegaTool.php` (nuevo) | Clase que extiende `AbstractTool` |
| Registro | `src/Tools/ToolRegistry.php` | Añadir la clase a la lista del constructor |
| Autocarga | `bootstrap.php` | **Nada**: ya recorre todo `src/Tools/` |
| Prueba de humo | `bin/mcp-selftest.php` | Casos nuevos (ver §6) |
| Docs | `README.md` (tabla de tools), `docs/CONEXION.md` si aplica | Documentar la tool |

No se toca la BD: ni tablas, ni DDL, ni GRANT nuevos. La tool lee tablas de módulos que la cuenta `MySQL_MCP` ya puede consultar.

## 3. Contrato de `AbstractTool`

Toda tool extiende `AbstractTool` (`src/Tools/AbstractTool.php`), que ya trae la conexión (`$this->db`, `McpDb::conn()`), el `QueryBuilder` (`$this->qb`), el catálogo (`$this->catalogo`) y el alcance (`$this->scope`).

Hay que implementar dos métodos:

- `definition(array $modulosEnAlcance): array`: lo que se publica en `tools/list` (`name`, `title`, `description`, `inputSchema`, `annotations`).
- `execute(array $args): array`: ejecuta y devuelve un resultado MCP con `toResult($data)` o `toError($mensaje)`.

Helpers reutilizables, no reimplementar:

| Helper | Uso |
|---|---|
| `validateArgs($schema, $args)` | Valida los argumentos contra el `inputSchema` (`-32602` si no cumplen). Soporta `type`, `enum`, `pattern`, `minimum`, `maximum`, `minLength`, `maxLength`, `maxItems`, `required`, `additionalProperties`. |
| `resolveTable(['module'=>..., 'table'=>...])` | Devuelve `[modulo, tabla]` del catálogo **solo si el alcance del token permite la tabla**; si no, `-32602` "Módulo o tabla no disponible" (igual que si no existiera). |
| `selectColumns($tabla)` | Lista de columnas visibles entre backticks. Nunca `SELECT *`. |
| `checkResult($r)` | Convierte un fallo de `QueryBuilder` en `-32603` genérico sin filtrar detalle. |
| `shapeRow($fila)` | Trunca celdas de más de 2000 caracteres. |
| `assertLatin1($texto)` | Exige texto representable en latin1 (la BD es latin1). |
| `findColumn($tabla, $nombre)` | Busca una columna **visible** del catálogo. |
| `toResult` / `toError` | Formato de salida (`content`, `structuredContent`, `isError`). |
| `annotations()` | `readOnlyHint: true`, `openWorldHint: false`. |

## 4. Reglas de seguridad de una tool nueva

1. **El alcance manda.** Cada tabla que lea debe pasar por `resolveTable` (o por `$this->scope->allowsTable($tabla)`). No consultar nunca una tabla "directa" saltándose el catálogo. Así una tool nueva no amplía el acceso del token.
2. **Solo columnas del catálogo.** El catálogo ya excluye PII, claves, IP, `Direccion_img` y tablas de la lista negra. No leer columnas que no estén en `$tabla['columnas']`.
3. **Todo valor del cliente va con `?`** en `params`; los identificadores (tabla, columna) salen del catálogo, no del argumento. Los nombres de columna que se interpolan deben validarse con `self::IDENT`.
4. **Solo lectura:** `SELECT` únicamente. La tool no escribe en ninguna tabla.
5. **Acotar siempre:** `limit` con máximo, sin consultas sin tope. `McpConfig::MAX_LIMIT` es 100 y la respuesta no puede pasar de 2 MiB.
6. **Errores:** de negocio con `toError('...')`; de infraestructura dejar que `checkResult` lance `-32603`. Nunca devolver el mensaje de una excepción al cliente.
7. **Degradar con elegancia** si el token no cubre una tabla secundaria (ver el ejemplo: sin el módulo `productos` se devuelven ids de producto).

## 5. Ejemplo completo: `src/Tools/StockPorBodegaTool.php`

Diseño: el stock vive en `bodegas_productos_stocks` (`idStocks`, `idProducto` y una columna `Cantidad_idBodegas_<N>` por bodega); el nombre de la bodega, en `bodegas_listado` (`idBodegas`, `Nombre`); el del producto, en `productos_listado` (`idProducto`, `Nombre`, `Codigo`), que es de **otro módulo** (`productos`). Las tablas y columnas son las del catálogo actual (`cache/catalogo.json`); si cambia el esquema, la tool debe ajustarse.

```php
<?php
defined('MCP_ROOT') || exit;
/**
 * Tool stock_por_bodega: stock de productos con nombre de bodega y de producto.
 *
 * Requiere el modulo bodegas en el alcance. Si el token ademas tiene productos, resuelve el nombre del producto
 * y permite filtrar por el; si no, devuelve idProducto sin nombre.
 */
class StockPorBodegaTool extends AbstractTool
{
    private const MAX_LIMIT = 50;

    /**
     * @param string[] $modulosEnAlcance Modulos que pasan allowsModule
     * @return array
     */
    public function definition(array $modulosEnAlcance): array
    {
        return [
            'name'        => 'stock_por_bodega',
            'title'       => 'Stock por bodega',
            'description' => 'Stock de productos por bodega, con nombre de bodega y de producto y cantidades. '
                           . 'Filtra por nombre de producto y/o de bodega. Solo lectura.',
            'inputSchema' => [
                'type'                 => 'object',
                'properties'           => [
                    'product' => ['type' => 'string', 'minLength' => 2, 'maxLength' => 100, 'description' => 'Texto contenido en el nombre del producto (requiere el modulo productos)'],
                    'bodega'  => ['type' => 'string', 'minLength' => 2, 'maxLength' => 100, 'description' => 'Texto contenido en el nombre de la bodega'],
                    'limit'   => ['type' => 'integer', 'minimum' => 1, 'maximum' => self::MAX_LIMIT, 'default' => 25],
                ],
                'additionalProperties' => false,
            ],
            'annotations' => $this->annotations(),
        ];
    }

    /**
     * @param array $args product, bodega, limit
     * @return array
     * @throws McpException -32602 por argumentos o alcance invalidos; -32603 si falla una consulta
     */
    public function execute(array $args): array
    {
        $this->validateArgs($this->definition($this->modulesInScope())['inputSchema'], $args);
        $limit = (int) ($args['limit'] ?? 25);

        // 1) Tablas de bodegas: obligatorias. resolveTable exige alcance y lanza -32602 si no hay.
        [, $stock]   = $this->resolveTable(['module' => 'bodegas', 'table' => 'bodegas_productos_stocks']);
        [, $bodegas] = $this->resolveTable(['module' => 'bodegas', 'table' => 'bodegas_listado']);

        // 2) Tabla de productos: opcional. Sin alcance se degrada a ids.
        $productos = null;
        try {
            [, $productos] = $this->resolveTable(['module' => 'productos', 'table' => 'productos_listado']);
        } catch (McpException $e) {
            if (isset($args['product'])) {
                return $this->toError('El filtro por producto requiere el módulo productos en el token');
            }
        }

        // 3) Nombres de bodega: mapa idBodegas => Nombre, y columnas Cantidad_idBodegas_<N> visibles del stock.
        $nombresBodega = [];
        foreach ($this->checkResult($this->qb->queryArray([
            'data'  => '`idBodegas`, `Nombre`',
            'table' => $bodegas['tabla'],
            'limit' => 500,
        ], $this->db)) as $b) {
            $nombresBodega[(int) $b['idBodegas']] = (string) $b['Nombre'];
        }
        $colsBodega = [];                                   // [idBodega => nombre de columna de cantidad]
        foreach ($stock['columnas'] as $c) {
            if (preg_match('/^Cantidad_idBodegas_(\d+)$/', (string) $c['nombre'], $m) === 1) {
                $colsBodega[(int) $m[1]] = $c['nombre'];
            }
        }
        if (isset($args['bodega'])) {                       // el filtro de bodega se aplica en PHP, sin tocar SQL
            $texto      = mb_strtolower($args['bodega'], 'UTF-8');
            $colsBodega = array_filter(
                $colsBodega,
                static fn(int $id): bool => mb_stripos($nombresBodega[$id] ?? '', $texto, 0, 'UTF-8') !== false,
                ARRAY_FILTER_USE_KEY
            );
            if ($colsBodega === []) {
                return $this->toError('Ninguna bodega coincide con el filtro');
            }
        }

        // 4) Filtro por producto: ids que coinciden por nombre (valor siempre con ?, comodines escapados).
        $where  = '';
        $params = [];
        if (isset($args['product']) && $productos !== null) {
            $this->assertLatin1($args['product']);
            $ids = $this->checkResult($this->qb->queryArray([
                'data'   => '`idProducto`',
                'table'  => $productos['tabla'],
                'where'  => '`Nombre` LIKE ?',
                'params' => ['%' . addcslashes($args['product'], '%_\\') . '%'],
                'limit'  => 100,
            ], $this->db));
            $ids = array_map(static fn(array $f): int => (int) $f['idProducto'], $ids);
            if ($ids === []) {
                return $this->toResult(['rows' => [], 'count' => 0]);
            }
            $where  = '`idProducto` IN (' . implode(',', array_fill(0, count($ids), '?')) . ')';
            $params = $ids;
        }

        // 5) Stock: solo las columnas necesarias (nombres salen del catalogo y validan con IDENT).
        $cols = ['`idProducto`'];
        foreach ($colsBodega as $col) {
            if (preg_match(self::IDENT, $col) !== 1) {
                throw new McpException(-32603, 'Internal error');
            }
            $cols[] = '`' . $col . '`';
        }
        $query = ['data' => implode(', ', $cols), 'table' => $stock['tabla'], 'order' => '`idProducto` ASC', 'limit' => $limit];
        if ($where !== '') {
            $query['where']  = $where;
            $query['params'] = $params;
        }
        $filas = $this->checkResult($this->qb->queryArray($query, $this->db));

        // 6) Nombres de producto de la pagina (una consulta), si el alcance lo permite.
        $nombresProducto = [];
        if ($productos !== null && $filas !== []) {
            $idsPagina = array_map(static fn(array $f): int => (int) $f['idProducto'], $filas);
            foreach ($this->checkResult($this->qb->queryArray([
                'data'   => '`idProducto`, `Nombre`, `Codigo`',
                'table'  => $productos['tabla'],
                'where'  => '`idProducto` IN (' . implode(',', array_fill(0, count($idsPagina), '?')) . ')',
                'params' => $idsPagina,
                'limit'  => count($idsPagina),
            ], $this->db)) as $p) {
                $nombresProducto[(int) $p['idProducto']] = ['nombre' => $p['Nombre'], 'codigo' => $p['Codigo']];
            }
        }

        // 7) Salida legible.
        $out = [];
        foreach ($filas as $f) {
            $idP    = (int) $f['idProducto'];
            $detalle = [];
            $total   = 0.0;
            foreach ($colsBodega as $idB => $col) {
                $cant      = (float) $f[$col];
                $total    += $cant;
                $detalle[] = ['idBodega' => $idB, 'bodega' => $nombresBodega[$idB] ?? null, 'cantidad' => $cant];
            }
            $out[] = $this->shapeRow([
                'idProducto' => $idP,
                'producto'   => $nombresProducto[$idP]['nombre'] ?? null,
                'codigo'     => $nombresProducto[$idP]['codigo'] ?? null,
                'bodegas'    => $detalle,
                'total'      => $total,
            ]);
        }
        return $this->toResult(['rows' => $out, 'count' => count($out)]);
    }
}
```

Notas del diseño:

- **Alcance**: `bodegas` es obligatorio (si falta, `-32602` como cualquier tabla fuera de alcance); `productos` es opcional y la tool degrada a `idProducto` sin nombre.
- **Sin ampliar acceso**: solo lee tablas y columnas que el token ya podría leer con `list_records`; la tool no añade nada que no estuviera en el catálogo.
- **Filtro de bodega en PHP**: evita construir SQL con el nombre de la bodega; solo cambia qué columnas `Cantidad_idBodegas_<N>` se seleccionan.
- **Paginación**: este ejemplo usa solo `limit` (≤ 50). Si se necesita recorrer todo el stock, reutilizar `encodeCursor`/`decodeCursor` como hace `paginate()`.
- **`bodega` ↔ `Cantidad_idBodegas_N`**: se asume que `N` es el `idBodegas` de `bodegas_listado`. Verificarlo con datos reales antes de dar la tool por buena.

## 6. Registro en `ToolRegistry`

En `src/Tools/ToolRegistry.php`, añadir la clase a la lista del constructor:

```php
foreach ([ListModulesTool::class, DescribeModuleTool::class, ListRecordsTool::class, SearchRecordsTool::class, GetRecordTool::class, StockPorBodegaTool::class] as $clase) {
```

`ToolRegistry` publica la definición en `tools/list` y enruta `tools/call` por el `name` de la definición; no hace falta tocar `McpServer`.

**Alcance de `tools/list`**: hoy el registro lista todas las tools aunque el token no tenga el módulo. Si se quiere que `stock_por_bodega` solo aparezca con `bodegas` en el alcance, filtrar en `ToolRegistry::listTools()` (por ejemplo, que `definition()` devuelva `null` cuando falte el módulo). Es una decisión de diseño a fijar en la spec; el ejemplo asume que aparece siempre y falla con `-32602` si falta el módulo.

## 7. Pruebas

1. `php -l` sobre la clase y `ToolRegistry.php`.
2. **Selftest** (`php admin/MCP/bin/mcp-selftest.php`, variables en su cabecera): añadir casos para
   - `tools/list` incluye `stock_por_bodega` con su `inputSchema`;
   - token con `bodegas` y `productos`: filas con `producto` y `bodega` con nombre;
   - token solo con `bodegas`: `producto` es `null`; con `product` en los argumentos, `isError: true`;
   - token sin `bodegas`: `-32602`;
   - argumento desconocido o `limit` 0 o 51: `-32602`;
   - `product` con `%` o `_`: no devuelve todo el listado (comodines escapados).
3. Con BD real en dev (el PHP CLI no tiene `pdo_mysql`, ver `README.md` §Operación): comprobar a mano que `Cantidad_idBodegas_N` corresponde al `idBodegas` N y que los totales cuadran con el informe de stock del backoffice.
4. Revisar `tokens_mcp_log`: la llamada queda auditada con `Herramienta = stock_por_bodega`.

## 8. Checklist de cierre

- [ ] La clase empieza con `defined('MCP_ROOT') || exit;` y extiende `AbstractTool`.
- [ ] Todas las tablas pasan por `resolveTable` / alcance.
- [ ] Valores del cliente con `?`; identificadores del catálogo validados con `IDENT`.
- [ ] `limit` acotado; sin `SELECT *`.
- [ ] Errores genéricos hacia el cliente (`toError` / `-32603`).
- [ ] Registrada en `ToolRegistry`.
- [ ] Casos nuevos en `mcp-selftest.php`.
- [ ] Tabla de tools de `README.md` actualizada.
- [ ] Doble registro en `docs/history/` y `docs/change_logs/`.
