<?php
defined('MCP_ROOT') || exit;
/**
 * Base de las tools del servidor MCP (solo lectura).
 *
 * @see projects/mcp-servidor/specs/technical.md §9
 */
abstract class AbstractTool
{
    protected const IDENT = '/^[A-Za-z0-9_]{1,64}$/';

    /** @var array Catalogo de modulos (ModuleScanner::getCatalog) */
    protected array $catalogo;

    /** @var TokenScope Alcance del token */
    protected TokenScope $scope;

    /** @var DB\SQL Conexion MySQL_MCP */
    protected $db;

    /** @var QueryBuilder Constructor de consultas */
    protected QueryBuilder $qb;

    /**
     * @param array      $catalogo Catalogo de modulos
     * @param TokenScope $scope    Alcance del token
     */
    public function __construct(array $catalogo, TokenScope $scope)
    {
        $this->catalogo = $catalogo;
        $this->scope    = $scope;
        $this->db       = McpDb::conn();
        $this->qb       = new QueryBuilder();
    }

    /**
     * Definicion de la tool para tools/list.
     *
     * @param string[] $modulosEnAlcance Modulos que pasan allowsModule
     * @return array name, title, description, inputSchema y annotations
     */
    abstract public function definition(array $modulosEnAlcance): array;

    /**
     * Ejecuta la tool.
     *
     * @param array $args Argumentos de tools/call
     * @return array Resultado MCP (toResult / toError)
     */
    abstract public function execute(array $args): array;

    /**
     * Nombres de los modulos que pasan allowsModule.
     *
     * @return string[]
     */
    protected function modulesInScope(): array
    {
        $out = [];
        foreach ($this->catalogo as $nombre => $modulo) {
            if ($this->scope->allowsModule($modulo)) {
                $out[] = (string) $nombre;
            }
        }
        return $out;
    }

    /**
     * Resuelve modulo y tabla del catalogo exigiendo que el alcance permita la tabla (RN-006).
     *
     * Inexistente y fuera de alcance dan el mismo error (sin enumeracion).
     *
     * @param array $args Argumentos con 'module' y 'table'
     * @return array{0: array, 1: array} [entrada del modulo, entrada de la tabla]
     * @throws McpException -32602 'Módulo o tabla no disponible'
     */
    protected function resolveTable(array $args): array
    {
        $modulo = $args['module'] ?? null;
        $tabla  = $args['table'] ?? null;
        if (is_string($modulo) && is_string($tabla)) {
            $m = $this->catalogo[$modulo] ?? null;
            $t = is_array($m) ? ($m['tablas'][$tabla] ?? null) : null;
            if (is_array($m) && is_array($t) && $this->scope->allowsTable($t)) {
                return [$m, $t];
            }
        }
        throw new McpException(-32602, 'Módulo o tabla no disponible');
    }

    /**
     * Valida los argumentos contra un subconjunto de JSON Schema (H-20).
     *
     * Subconjunto: type, enum, pattern, propertyNames, minimum, maximum, minLength, maxLength, maxItems,
     * items, maxProperties, required y additionalProperties.
     *
     * @param array $schema inputSchema de la tool
     * @param array $args   Argumentos recibidos
     * @throws McpException -32602 con la clave incumplida en data
     */
    protected function validateArgs(array $schema, array $args): void
    {
        $this->validateValue($schema, $args, '');
    }

    /**
     * @param array  $schema Fragmento de schema
     * @param mixed  $valor  Valor a validar
     * @param string $ruta   Ruta de la clave (para el error)
     * @throws McpException -32602
     */
    private function validateValue(array $schema, mixed $valor, string $ruta): void
    {
        $fallo = static fn(): McpException => new McpException(-32602, 'Invalid params', ['param' => $ruta === '' ? '(arguments)' : $ruta]);

        if (isset($schema['type'])) {
            $ok = false;
            foreach ((array) $schema['type'] as $tipo) {
                $ok = $ok || match ($tipo) {
                    'object'  => is_array($valor) && ($valor === [] || array_keys($valor) !== range(0, count($valor) - 1)),
                    'array'   => is_array($valor) && ($valor === [] || array_keys($valor) === range(0, count($valor) - 1)),
                    'string'  => is_string($valor),
                    'integer' => is_int($valor),
                    'number'  => is_int($valor) || is_float($valor),
                    'boolean' => is_bool($valor),
                    default   => false,
                };
            }
            if (!$ok) {
                throw $fallo();
            }
        }
        if (isset($schema['enum']) && !in_array($valor, $schema['enum'], true)) {
            throw $fallo();
        }
        if (is_string($valor)) {
            if (isset($schema['pattern']) && preg_match('/' . str_replace('/', '\/', $schema['pattern']) . '/', $valor) !== 1) {
                throw $fallo();
            }
            $len = mb_strlen($valor, 'UTF-8');
            if ((isset($schema['minLength']) && $len < $schema['minLength']) || (isset($schema['maxLength']) && $len > $schema['maxLength'])) {
                throw $fallo();
            }
        }
        if (is_int($valor) || is_float($valor)) {
            if ((isset($schema['minimum']) && $valor < $schema['minimum']) || (isset($schema['maximum']) && $valor > $schema['maximum'])) {
                throw $fallo();
            }
        }
        if (is_array($valor) && isset($schema['items']) && ($valor === [] || array_keys($valor) === range(0, count($valor) - 1))) {
            if (isset($schema['maxItems']) && count($valor) > $schema['maxItems']) {
                throw $fallo();
            }
            foreach ($valor as $i => $item) {
                $this->validateValue($schema['items'], $item, $ruta . '[' . $i . ']');
            }
        } elseif (is_array($valor) && isset($schema['maxItems']) && count($valor) > $schema['maxItems']) {
            throw $fallo();
        }
        if (is_array($valor) && (isset($schema['properties']) || isset($schema['required']) || isset($schema['additionalProperties']) || isset($schema['propertyNames']) || isset($schema['maxProperties']))) {
            if (isset($schema['maxProperties']) && count($valor) > $schema['maxProperties']) {
                throw $fallo();
            }
            foreach ($schema['required'] ?? [] as $req) {
                if (!array_key_exists($req, $valor)) {
                    throw new McpException(-32602, 'Invalid params', ['param' => ($ruta === '' ? '' : $ruta . '.') . $req]);
                }
            }
            foreach ($valor as $clave => $v) {
                $clave = (string) $clave;
                $sub   = ($ruta === '' ? '' : $ruta . '.') . $clave;
                if (isset($schema['propertyNames']['pattern']) && preg_match('/' . str_replace('/', '\/', $schema['propertyNames']['pattern']) . '/', $clave) !== 1) {
                    throw new McpException(-32602, 'Invalid params', ['param' => $sub]);
                }
                if (isset($schema['properties'][$clave])) {
                    $this->validateValue($schema['properties'][$clave], $v, $sub);
                } elseif (($schema['additionalProperties'] ?? true) === false) {
                    throw new McpException(-32602, 'Invalid params', ['param' => $sub]);
                } elseif (is_array($schema['additionalProperties'] ?? null)) {
                    $this->validateValue($schema['additionalProperties'], $v, $sub);
                }
            }
        }
    }

    /**
     * Resultado correcto de una tool.
     *
     * @param array $data structuredContent
     * @return array content (texto JSON), structuredContent e isError = false
     */
    protected function toResult(array $data): array
    {
        return [
            'content'           => [['type' => 'text', 'text' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE)]],
            'structuredContent' => $data,
            'isError'           => false,
        ];
    }

    /**
     * Resultado de error de negocio de una tool (no es un error JSON-RPC).
     *
     * @param string $mensaje Texto para el cliente
     * @return array content con el mensaje e isError = true
     */
    protected function toError(string $mensaje): array
    {
        return [
            'content' => [['type' => 'text', 'text' => $mensaje]],
            'isError' => true,
        ];
    }

    /**
     * Codifica un desplazamiento como cursor opaco.
     *
     * @param int $offset Desplazamiento
     * @return string base64url sin relleno de {"o":n}
     */
    protected function encodeCursor(int $offset): string
    {
        return rtrim(strtr(base64_encode(json_encode(['o' => $offset])), '+/', '-_'), '=');
    }

    /**
     * Decodifica y valida un cursor (H-5, H-20).
     *
     * @param string|null $cursor Cursor recibido
     * @return int Desplazamiento (0 si no hay cursor)
     * @throws McpException -32602 si no es un cursor valido o supera MAX_OFFSET
     */
    protected function decodeCursor(?string $cursor): int
    {
        if ($cursor === null) {
            return 0;
        }
        $raw  = base64_decode(strtr($cursor, '-_', '+/'), true);
        $json = $raw === false ? null : json_decode($raw, true);
        if (!is_array($json) || !array_key_exists('o', $json) || !is_int($json['o']) || $json['o'] < 0 || $json['o'] > McpConfig::MAX_OFFSET) {
            throw new McpException(-32602, 'Invalid params', ['param' => 'cursor']);
        }
        return $json['o'];
    }

    /**
     * Trunca los textos largos de una fila (RN-007).
     *
     * @param array $row Fila
     * @return array Fila con los strings de mas de MAX_CELL_CHARS caracteres cortados y terminados en "…[truncado]"
     */
    protected function shapeRow(array $row): array
    {
        foreach ($row as $k => $v) {
            if (is_string($v) && mb_strlen($v, 'UTF-8') > McpConfig::MAX_CELL_CHARS) {
                $row[$k] = mb_substr($v, 0, McpConfig::MAX_CELL_CHARS, 'UTF-8') . '…[truncado]';
            }
        }
        return $row;
    }

    /**
     * Exige que un texto sea representable en latin1 (la BD guarda latin1; BD 6).
     *
     * @param string $valor Texto
     * @throws McpException -32602 'Texto no representable'
     */
    protected function assertLatin1(string $valor): void
    {
        if (mb_convert_encoding(mb_convert_encoding($valor, 'ISO-8859-1', 'UTF-8'), 'UTF-8', 'ISO-8859-1') !== $valor) {
            throw new McpException(-32602, 'Texto no representable');
        }
    }

    /**
     * Lista de columnas visibles para el SELECT: entre backticks, nunca "*" (H-20).
     *
     * @param array $tabla Entrada de la tabla del catalogo
     * @return string
     * @throws McpException -32603 si algun nombre no valida
     */
    protected function selectColumns(array $tabla): string
    {
        $cols = [];
        foreach ($tabla['columnas'] ?? [] as $c) {
            if (!is_string($c['nombre'] ?? null) || preg_match(self::IDENT, $c['nombre']) !== 1) {
                throw new McpException(-32603, 'Internal error');
            }
            $cols[] = '`' . $c['nombre'] . '`';
        }
        if ($cols === []) {
            throw new McpException(-32603, 'Internal error');
        }
        return implode(', ', $cols);
    }

    /**
     * Verifica una respuesta de QueryBuilder sin filtrar detalles al cliente (H-8).
     *
     * @param array $r Respuesta de QueryBuilder
     * @return array El contenido de 'data'
     * @throws McpException -32603 'Internal error' (sin data) si status !== true
     */
    protected function checkResult(array $r): array
    {
        if (($r['status'] ?? false) !== true) {
            throw new McpException(-32603, 'Internal error');
        }
        return is_array($r['data'] ?? null) ? $r['data'] : [];
    }

    /**
     * Columna visible de una tabla por nombre.
     *
     * @param array  $tabla  Entrada de la tabla del catalogo
     * @param string $nombre Nombre de columna
     * @return array|null
     */
    protected function findColumn(array $tabla, string $nombre): ?array
    {
        foreach ($tabla['columnas'] ?? [] as $c) {
            if (($c['nombre'] ?? null) === $nombre) {
                return $c;
            }
        }
        return null;
    }

    /**
     * Anade `<columna>_nombre` a las columnas idUsuario* de las filas (D-31, RF-022).
     *
     * Una sola consulta por llamada a USER_TABLE, limitada a USER_PK y USER_NAME_COLUMNS (whitelist; ninguna otra
     * columna de usuarios se lee ni se devuelve). Si la consulta falla (p. ej. falta el GRANT de columnas) degrada:
     * error_log y filas sin `_nombre`, sin -32603.
     *
     * @param array $tabla Entrada de la tabla del catalogo
     * @param array $filas Filas ya recortadas con shapeRow
     * @return array Filas con `<columna>_nombre` junto a cada columna idUsuario* visible (salvo la PK)
     */
    protected function enrichUserNames(array $tabla, array $filas): array
    {
        $cols = [];
        foreach ($tabla['columnas'] ?? [] as $c) {
            $n = $c['nombre'] ?? null;
            if (is_string($n) && $n !== ($tabla['pk'] ?? null) && in_array($n, McpConfig::USER_ID_COLUMNS, true)) {
                $cols[] = $n;
            }
        }
        if ($cols === [] || $filas === []) {
            return $filas;
        }
        $ids = [];
        foreach ($filas as $fila) {
            foreach ($cols as $col) {
                $v = $fila[$col] ?? null;
                if ((is_int($v) || (is_string($v) && ctype_digit($v))) && (int) $v > 0) {
                    $ids[(int) $v] = true;
                }
            }
        }
        $nombres = [];
        if ($ids !== []) {
            $ids = array_keys($ids);
            try {
                $r = $this->qb->queryArray([
                    'data'   => '`' . McpConfig::USER_PK . '`, `' . implode('`, `', McpConfig::USER_NAME_COLUMNS) . '`',
                    'table'  => McpConfig::USER_TABLE,
                    'where'  => '`' . McpConfig::USER_PK . '` IN (' . implode(',', array_fill(0, count($ids), '?')) . ')',
                    'params' => $ids,
                    'limit'  => count($ids),
                ], $this->db);
            } catch (Throwable $e) {
                $r = null;
            }
            if (!is_array($r) || ($r['status'] ?? false) !== true || !is_array($r['data'] ?? null)) {
                error_log('MCP: no se pudo resolver el nombre de usuarios (falta el GRANT de columnas sobre ' . McpConfig::USER_TABLE . '?)');
                return $filas;
            }
            foreach ($r['data'] as $u) {
                $partes = [];
                foreach (McpConfig::USER_NAME_COLUMNS as $nc) {
                    $partes[] = trim((string) ($u[$nc] ?? ''));
                }
                $nombre = trim(implode(' ', array_filter($partes, static fn(string $p): bool => $p !== '')));
                $nombres[(int) ($u[McpConfig::USER_PK] ?? 0)] = $nombre === '' ? null : $nombre;
            }
        }
        foreach ($filas as $i => $fila) {
            $nueva = [];
            foreach ($fila as $k => $v) {
                $nueva[$k] = $v;
                if (in_array($k, $cols, true)) {
                    $id = (is_int($v) || (is_string($v) && ctype_digit($v))) ? (int) $v : 0;
                    $nueva[$k . '_nombre'] = $id > 0 ? ($nombres[$id] ?? null) : null;
                }
            }
            $filas[$i] = $nueva;
        }
        return $filas;
    }

    /**
     * Pagina comun de list_records y search_records: ejecuta, recorta a limit y calcula nextCursor.
     *
     * @param array  $modulo Entrada del modulo
     * @param array  $tabla  Entrada de la tabla
     * @param string $where  WHERE con placeholders ('' = sin filtros)
     * @param array  $params Valores bindeados
     * @param string $order  ORDER BY con identificadores del catalogo
     * @param int    $offset Desplazamiento
     * @param int    $limit  Filas por pagina
     * @return array structuredContent: module, table, rows, count, nextCursor
     */
    protected function paginate(array $modulo, array $tabla, string $where, array $params, string $order, int $offset, int $limit): array
    {
        $query = [
            'data'  => $this->selectColumns($tabla),
            'table' => $tabla['tabla'],
            'order' => $order,
            'limit' => (int) $offset . ', ' . ((int) $limit + 1),
        ];
        if ($where !== '') {
            $query['where']  = $where;
            $query['params'] = $params;
        }
        $filas = $this->checkResult($this->qb->queryArray($query, $this->db));
        $hay   = count($filas) > $limit;
        $filas = array_slice($filas, 0, $limit);
        $filas = array_map(fn(array $f): array => $this->shapeRow($f), $filas);
        $filas = $this->enrichUserNames($tabla, $filas);
        $sig   = $offset + $limit;
        return [
            'module'     => $modulo['modulo'],
            'table'      => $tabla['tabla'],
            'rows'       => $filas,
            'count'      => count($filas),
            'nextCursor' => ($hay && $sig <= McpConfig::MAX_OFFSET) ? $this->encodeCursor($sig) : null,
        ];
    }

    /**
     * Esquema de las propiedades comunes module/table para las definiciones.
     *
     * @param string[] $modulosEnAlcance Modulos en alcance
     * @return array
     */
    protected function moduleProperty(array $modulosEnAlcance, bool $conDescripcion = false): array
    {
        $p = ['type' => 'string', 'enum' => array_values($modulosEnAlcance)];
        if ($conDescripcion) {
            $p['description'] = 'Módulo devuelto por list_modules';
        }
        return $p;
    }

    /**
     * @return array Anotaciones comunes de las tools de solo lectura
     */
    protected function annotations(): array
    {
        return ['readOnlyHint' => true, 'openWorldHint' => false];
    }
}
