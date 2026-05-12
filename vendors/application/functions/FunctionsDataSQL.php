<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsDataSQL {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
    /************************************************************************************************************/
	/**
     * Valida credenciales de conexión a un servidor MySQL y comprueba permisos de usuario.
     * * Intenta establecer conexión y, según el tipo solicitado ('admin' o 'basic'),
     * realiza pruebas de creación/eliminación de bases de datos o manipulación de tablas temporales.
     *
     * @param string $host Dirección del servidor de base de datos.
     * @param string $username Nombre de usuario para la conexión.
     * @param string $password Contraseña de acceso.
     * @param string|int $port Puerto de red del servicio MySQL.
     * @param string $charset Juego de caracteres de la conexión.
     * @param string $type Nivel de permisos a validar: 'admin' o 'basic'.
     *
     * @return array Resultado de la validación con claves 'status', 'success' y 'message'.
	 *
	 * @example
	 * ```php
	 * $DataSQL->validateCredentials($host, $username, $password);
	 * ```
	 *
     */
    public function validateCredentials($host, $username, $password, $port, $charset, $type): array {

        /**********************  Definiciones   **********************/
        // Empaquetado de parámetros para verificación de existencia
        $params = compact('host', 'username', 'password', 'port', 'charset', 'type');

        /**********************  Validaciones   **********************/
        foreach ($params as $name => $value) {
            if ($value === '' || $value === null) {
                return [
                    'status'  => 'missing_param',
                    'success' => false,
                    'message' => "No hay datos en \$$name"
                ];
            }
        }

        // Validación de rango de puerto TCP estándar
        if (!is_numeric($port) || (int)$port <= 0 || (int)$port > 65535) {
            return [
                'status'  => 'invalid_port',
                'success' => false,
                'message' => 'El puerto debe ser un número entre 1 y 65535'
            ];
        }

        // Restricción de tipos de validación permitidos
        if (!in_array($type, ['admin', 'basic'], true)) {
            return [
                'status'  => 'invalid_type',
                'success' => false,
                'message' => 'Tipo de usuario no válido. Use "admin" o "basic"'
            ];
        }

        /********************** Si todo esta ok **********************/
        try {

            // Intento de instanciación de conexión SQL
            $DBConn = new DB\SQL(
                'mysql:host=' . $host . ';port=' . (int)$port . ';charset=' . $charset,
                $username,
                $password,
                [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8;']
            );

            // Verificación básica de comunicación con el motor
            $DBConn->exec("SELECT 1;");

            // Validación específica para usuarios con privilegios administrativos
            if ($type === 'admin') {

                // Generación de un nombre aleatorio para una base de datos de prueba
                $testDbName = '__test_install_' . preg_replace('/[^a-f0-9]/', '', uniqid('', true));

                try {
                    // Prueba de creación de base de datos física
                    $DBConn->exec("CREATE DATABASE `$testDbName`");
                    // Prueba de eliminación para limpiar el entorno
                    $DBConn->exec("DROP DATABASE `$testDbName`");
                    $realCreateWorks = true;
                } catch (\Exception $e) {
                    // Intento de limpieza en caso de fallo intermedio
                    try { $DBConn->exec("DROP DATABASE IF EXISTS `$testDbName`"); } catch (\Exception $ignored) {}
                    $realCreateWorks = false;
                }

                // Error si la conexión fue exitosa pero no tiene privilegios suficientes
                if (!$realCreateWorks) {
                    return [
                        'status'  => 'no_create_permission',
                        'success' => false,
                        'message' => 'Usuario válido pero sin permisos CREATE DATABASE'
                    ];
                }

                return [
                    'status'  => 'success',
                    'success' => true,
                    'message' => 'Usuario ADMIN validado correctamente'
                ];
            }

            // Validación específica para usuarios con privilegios operativos básicos
            if ($type === 'basic') {

                // Cambio manual al contexto de la base de datos 'mysql'
                $DBConn->exec("USE `mysql` || SELECT 1;");

                // Generación de nombre único para tabla temporal
                $testTable = '__test_perm_' . preg_replace('/[^a-f0-9]/', '', uniqid('', true));

                try {
                    // Prueba del ciclo de vida de una tabla temporal (CRUD básico)
                    $DBConn->exec("CREATE TEMPORARY TABLE `$testTable` (id INT)");
                    $DBConn->exec("INSERT INTO `$testTable` (id) VALUES (1)");
                    $DBConn->exec("SELECT * FROM `$testTable` LIMIT 1");
                    $DBConn->exec("DELETE FROM `$testTable` WHERE id = 1");
                    $DBConn->exec("DROP TEMPORARY TABLE IF EXISTS `$testTable`");
                    $basicPermissionsOK = true;
                } catch (\Exception $e) {
                    // Limpieza preventiva
                    try { $DBConn->exec("DROP TEMPORARY TABLE IF EXISTS `$testTable`"); } catch (\Exception $ignored) {}
                    $basicPermissionsOK = false;
                }

                // Error si faltan permisos de manipulación de datos
                if (!$basicPermissionsOK) {
                    return [
                        'status'  => 'no_basic_permissions',
                        'success' => false,
                        'message' => 'Usuario válido pero sin permisos SELECT/INSERT/DELETE'
                    ];
                }

                return [
                    'status'  => 'success',
                    'success' => true,
                    'message' => 'Usuario BASIC validado correctamente'
                ];
            }

            return [
                'status'  => 'unknown_error',
                'success' => false,
                'message' => 'Error desconocido'
            ];

        } catch (\PDOException $e) {

            $message = $e->getMessage();

            // Mapeo de errores comunes de PDO a estados de la aplicación
            if (str_contains($message, '2002') || str_contains($message, '2003')) {
                return [
                    'status'  => 'server_unreachable',
                    'success' => false,
                    'message' => 'No se puede conectar al servidor MySQL'
                ];
            }

            if (str_contains($message, '1045')) {
                return [
                    'status'  => 'access_denied',
                    'success' => false,
                    'message' => 'Usuario o contraseña incorrectos'
                ];
            }

            if (str_contains($message, '1044')) {
                return [
                    'status'  => 'db_access_denied',
                    'success' => false,
                    'message' => 'Acceso denegado a la base de datos especificada'
                ];
            }

            return [
                'status'  => 'unknown_error',
                'success' => false,
                'message' => $message
            ];

        } catch (\Exception $e) {

            // Captura de errores generales de lógica o instanciación
            return [
                'status'  => 'unknown_error',
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

    }

    /************************************************************************************************************/
	/**
     * Valida la disponibilidad y el formato de un nombre para una base de datos.
     * * Comprueba restricciones de longitud, caracteres permitidos, nombres reservados
     * y consulta al servidor si el nombre ya se encuentra en uso.
     *
     * @param string $host Host de conexión.
     * @param string $username Usuario de conexión.
     * @param string $password Contraseña de conexión.
     * @param string|int $port Puerto de conexión.
     * @param string $charset Juego de caracteres.
     * @param string $dbName Nombre de la base de datos a verificar.
     *
     * @return array Resultado con claves 'status', 'success' y 'message'.
	 *
	 * @example
	 * ```php
	 * ```
	 *
     */
    public function validateDatabase($host, $username, $password, $port, $charset, $dbName): array {

        /**********************  Validaciones   **********************/
        // Validación de campos obligatorios
        if(!isset($host) || $host==''){          return ['success' => false, 'message' => 'No hay datos en $host'];}
        if(!isset($username) || $username==''){  return ['success' => false, 'message' => 'No hay datos en $username'];}
        if(!isset($password) || $password==''){  return ['success' => false, 'message' => 'No hay datos en $password'];}
        if(!isset($port) || $port==''){          return ['success' => false, 'message' => 'No hay datos en $port'];}
        if(!isset($charset) || $charset==''){    return ['success' => false, 'message' => 'No hay datos en $charset'];}
        if(!isset($dbName) || $dbName==''){      return ['success' => false, 'message' => 'No hay datos en $dbName'];}

        // Control de longitud según estándares de identificadores de motores SQL
        if (strlen($dbName) < 3 || strlen($dbName) > 64) {
            return [
                'status'  => 'invalid_length',
                'success' => false,
                'message' => 'El nombre debe tener entre 3 y 64 caracteres'
            ];
        }

        // Restricción de caracteres para prevenir inyección o nombres inválidos
        if (!preg_match('/^[A-Za-z0-9_]+$/', $dbName)) {
            return [
                'status'  => 'invalid_format',
                'success' => false,
                'message' => 'El nombre solo puede contener letras, números y guiones bajos'
            ];
        }

        // Lista negra de nombres de bases de datos internas del motor
        $reservedNames = [
            'mysql',
            'information_schema',
            'performance_schema',
            'sys'
        ];
        if (in_array(strtolower($dbName), $reservedNames, true)) {
            return [
                'status'  => 'reserved_name',
                'success' => false,
                'message' => 'El nombre corresponde a una base de datos reservada del sistema'
            ];
        }

        /********************** Si todo esta ok **********************/
        try {

            // Establecimiento de conexión para consulta remota
            $DBConn = new DB\SQL(
                'mysql:host='.$host.';port='.$port.';charset='.$charset,
                $username,
                $password,
                array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8;')
            );

            // Limpieza básica del nombre de la base de datos
            $safeDbName = str_replace('`', '', $dbName);

            // Consulta al motor para verificar existencia previa
            $query  = "SHOW DATABASES LIKE '".$safeDbName."'";
            $result = $DBConn->exec($query);

            if (!empty($result)) {
                return [
                    'status'  => 'database_exists',
                    'success' => false,
                    'message' => 'Ya existe una base de datos con ese nombre'
                ];
            }

            return [
                'status'  => 'valid',
                'success' => true,
                'message' => 'Nombre válido y disponible'
            ];

        } catch (\Exception $e) {

            return [
                'status'  => 'connection_error',
                'success' => false,
                'message' => 'Error al conectar al servidor: '.$e->getMessage()
            ];
        }
    }

    /************************************************************************************************************/
	/**
     * Verifica la existencia y accesibilidad de un archivo en el sistema de archivos local.
     *
     * @param string $PathFile Ruta absoluta o relativa al archivo.
     *
     * @return array Resultado con claves 'success', 'message' y 'path'.
	 *
	 * @example
	 * ```php
	 * $DataSQL->validatePathFile('Path');
	 * ```
	 *
     */
    public function validatePathFile($PathFile): array {

        /**********************  Validaciones   **********************/
        // Comprobación de parámetro obligatorio
        if(!isset($PathFile) || $PathFile==''){  return ['success' => false,'message' => 'No hay datos en $PathFile'];}

        // Verificación de existencia física en el disco
        if (!file_exists($PathFile)) {
            return [
                'success' => false,
                'message' => 'Archivo no encontrado',
                'path'    => $PathFile
            ];
        }

        // Verificación de permisos de lectura para el proceso actual
        if (!is_readable($PathFile)) {
            return [
                'success' => false,
                'message' => 'Archivo no es legible',
                'path'    => $PathFile
            ];
        }

        /**********************  Retorno datos  **********************/
        return [
            'success' => true,
            'message' => 'Archivo válido',
            'path'    => $PathFile
        ];
    }

    /************************************************************************************************************/
	/**
     * Valida una query SQL MySQL con distintos niveles de seguridad.
     *
     * CAPAS DE VALIDACIÓN (en orden de ejecución):
     *  1. Null bytes
     *  2. Longitud máxima
     *  3. Normalización (strip comentarios, espacios)
     *  4. Extracción de literales (simple, doble, backtick)
     *  5. Múltiples sentencias
     *  6. Detección de tipo
     *  7. Lista deny global
     *  8. Funciones peligrosas globales (SLEEP, BENCHMARK, encoding)
     *  9. Modo strict  / safe / paranoid
     * 10. SELECT sin FROM
     * 11. Tautologías (OR 1=1, OR 'x'='x', OR true, OR 2>1, IS NOT NULL)
     * 12. Whitelist de tablas (opcional)
     *
     * @param string $query   Query SQL a validar.
     * @param array  $options {
     *     @type string   $mode       Nivel de seguridad: default|strict|safe|paranoid.
     *                                - default  : validaciones básicas.
     *                                - strict   : solo tipos en $allowed; keywords en $deny bloqueadas globalmente.
     *                                - safe     : anti-mezcla de operaciones destructivas + subqueries peligrosas.
     *                                - paranoid : solo SELECT plano sin JOIN/UNION/subqueries.
     *     @type array    $allowed    Tipos de query permitidos (usado en strict).
     *                                Default: ['SELECT','INSERT','UPDATE','DELETE'].
     *     @type array    $deny       Keywords/tipos siempre bloqueados sin importar el modo.
     *                                Default: [].
     *     @type bool     $single     Si true, bloquea múltiples sentencias separadas por ';'.
     *                                Default: true.
     *     @type int      $max_length Longitud máxima permitida de la query en caracteres.
     *                                Default: 10000.
     *     @type array    $tables     Whitelist de tablas permitidas en FROM (vacío = sin restricción).
     *                                Default: [].
     * }
     *
     * @return array {
     *     @type bool        $valid  true si la query pasó todas las validaciones.
     *     @type string|null $error  Mensaje de error legible, null si válida.
     *     @type string|null $type   Tipo detectado (SELECT, INSERT, etc.), null si no detectado.
     * }
     */
    public function validateSQL(string $query, array $options = []): array {
        // =========================================================================
        // Opciones con sus defaults
        // =========================================================================
        $mode             = $options['mode']       ?? 'default';
        $allowed          = $options['allowed']    ?? ['SELECT', 'INSERT', 'UPDATE', 'DELETE'];
        $deny             = $options['deny']       ?? ['REPLACE', 'DROP', 'ALTER', 'CREATE', 'TRUNCATE'];
        $single           = $options['single']     ?? true;
        $maxLength        = $options['max_length'] ?? 10000;
        $tables           = $options['tables']     ?? [];
        $blacklistTables  = $options['blacklist_tables']  ?? [];
        $sensitiveColumns = $options['sensitive_columns'] ?? ['password', 'passwd', 'token'];

        // Closure de respuesta negativa — evita repetir la estructura del array.
        $fail = fn(string $msg, ?string $type = null): array => [
            'valid' => false,
            'error' => $msg,
            'type'  => $type,
        ];

        // =========================================================================
        // [1] NULL BYTES
        // Un null byte (\0) puede truncar el análisis en ciertas implementaciones
        // de C subyacentes a PHP/MySQL y se usa para evadir filtros de texto.
        // Ejemplo de ataque: "SELECT *\0 FROM users-- "
        // =========================================================================
        if (str_contains($query, "\0")) {
            return $fail('Query contiene caracteres nulos');
        }

        // =========================================================================
        // [2] LONGITUD MÁXIMA
        // Previene DoS por queries gigantes que disparen backtracking catastrófico
        // en los regex posteriores (ReDoS) o saturen memoria.
        // =========================================================================
        if (strlen($query) > $maxLength) {
            return $fail("Query excede el límite de {$maxLength} caracteres");
        }

        // =========================================================================
        // [3] NORMALIZACIÓN
        // Orden importante:
        //   a) trim()            — elimina espacios externos.
        //   b) strip comentarios — elimina -- , # y /* */ ANTES de analizar.
        //      Sin este paso, "SELECT 1 -- comentario falso" podría romper
        //      regex posteriores o esconder keywords.
        //   c) colapsar espacios — un único espacio entre tokens facilita
        //      los patrones \b en los regex siguientes.
        //   d) rtrim ';'         — el punto y coma final es ruido para la
        //      detección de múltiples sentencias.
        // =========================================================================
        $query = trim($query);
        $query = preg_replace('/(--[^\n]*$)|(#[^\n]*$)/m', '', $query); // comentarios de línea
        $query = preg_replace('/\/\*.*?\*\//s', '', $query);             // comentarios de bloque
        $query = preg_replace('/\s+/', ' ', $query);                     // colapsar whitespace
        $query = rtrim($query, '; ');

        if ($query === '') {
            return $fail('Query vacía');
        }

        // =========================================================================
        // [4] EXTRACCIÓN DE LITERALES STRING → $sanitized
        //
        // PROBLEMA que resuelve:
        //   WHERE name = 'DROP TABLE users'  → antes disparaba falso positivo.
        //   WHERE name = "SLEEP(5)"          → idem con comillas dobles.
        //
        // SOLUCIÓN:
        //   Reemplazar cada literal string por un placeholder neutral __STR_N__
        //   antes de cualquier análisis estructural. Así los regex de seguridad
        //   nunca "ven" el contenido de los valores, solo la estructura SQL.
        //
        // COBERTURA de comillas (punto 1 del plan de mejoras):
        //   '...'  — estándar SQL / MySQL
        //   "..."  — MySQL con ANSI_QUOTES desactivado
        //   `...`  — identifiers MySQL (nombres de tabla/columna)
        //
        // El regex maneja escapes internos:
        //   \'  dentro de '...'
        //   \"  dentro de "..."
        //   \`  dentro de `...`
        // =========================================================================
        $literals  = [];
        $sanitized = preg_replace_callback(
            "/'(?:[^'\\\\]|\\\\.)*'|\"(?:[^\"\\\\]|\\\\.)*\"|`(?:[^`\\\\]|\\\\.)*`/",
            function (array $m) use (&$literals): string {
                $placeholder           = '__STR_' . count($literals) . '__';
                $literals[$placeholder] = $m[0];
                return $placeholder;
            },
            $query
        );

        // =========================================================================
        // [5] MÚLTIPLES SENTENCIAS
        // Detecta patrones como: "SELECT 1; DROP TABLE x"
        // Se analiza sobre $sanitized para que un ';' dentro de un literal
        // ('val;ue') no dispare un falso positivo.
        // =========================================================================
        if ($single && preg_match('/;.+\S/', $sanitized)) {
            return $fail('Múltiples sentencias no permitidas');
        }

        // =========================================================================
        // [6] DETECCIÓN DE TIPO
        // Solo acepta los verbos SQL reconocidos al inicio de la query.
        // Cualquier otra cosa (o query vacía post-normalización) es inválida.
        // =========================================================================
        $knownTypes = 'SELECT|INSERT|UPDATE|DELETE|REPLACE|DROP|ALTER|CREATE|TRUNCATE';
        if (!preg_match('/^(' . $knownTypes . ')\b/i', $sanitized, $match)) {
            return $fail('Tipo de query no reconocido');
        }
        $type = strtoupper($match[1]);

        // =========================================================================
        // [7] LISTA DENY GLOBAL
        // Se evalúa ANTES de cualquier modo para que $deny actúe como lista negra
        // absoluta independiente del modo activo.
        // Ejemplo: ['DROP', 'TRUNCATE'] bloqueará esos tipos siempre.
        // =========================================================================
        if (!empty($deny) && in_array($type, $deny, true)) {
            return $fail("Tipo '$type' está en la lista de denegación", $type);
        }

        // =========================================================================
        // [8] FUNCIONES PELIGROSAS GLOBALES (aplican a todos los modos)
        //
        // 8a. TIMING / BLIND SQLi
        //     SLEEP y BENCHMARK se usan para inferir datos mediante retardos.
        //     Ejemplo: WHERE IF(1=1, SLEEP(5), 0)
        //
        // 8b. FUNCIONES DE ENCODING / OFUSCACIÓN (punto 2 del plan)
        //     Permiten ofuscar keywords para evadir los regex.
        //     Ejemplo: WHERE id = CHAR(49,32,79,82)  →  "1 OR"
        //              WHERE id = 0x44524f50         →  "DROP"
        //     Se detectan por el patrón FUNCION( para no bloquear
        //     nombres de columna que casualmente se llamen 'hex', etc.
        // =========================================================================

        // 8a — Timing/blind
        if (preg_match('/\b(SLEEP|BENCHMARK|WAIT\s+FOR\s+DELAY|PG_SLEEP)\s*\(/i', $sanitized)) {
            return $fail('Query contiene funciones de timing prohibidas', $type);
        }

        // 8b — Encoding/ofuscación
        if (preg_match('/\b(CHAR|HEX|UNHEX|ASCII|ORD|CONV|BIN)\s*\(/i', $sanitized)) {
            return $fail('Query contiene funciones de encoding/ofuscación prohibidas', $type);
        }

        // =========================================================================
        // [9a] MODO STRICT
        //
        // Dos controles:
        //   A) El tipo principal debe estar en $allowed.
        //   B) Ninguna keyword de $deny puede aparecer en cualquier parte
        //      de la query sanitizada (no solo como tipo principal).
        //      Esto bloquea, p.ej., un SELECT que contenga DROP en una subquery.
        // =========================================================================
        if ($mode === 'strict') {
            if (!in_array($type, $allowed, true)) {
                return $fail("Tipo '$type' no permitido en modo strict", $type);
            }

            foreach ($deny as $kw) {
                if (preg_match('/\b' . preg_quote($kw, '/') . '\b/i', $sanitized)) {
                    return $fail("Keyword '$kw' no permitida en modo strict", $type);
                }
            }
        }

        // =========================================================================
        // [9b] MODO SAFE
        //
        // Control A — Mezcla de operaciones destructivas (punto 7 del plan):
        //   Lista ampliada con REPLACE, CREATE, RENAME, LOCK además de los
        //   originales. Si aparecen más de una de estas keywords en la query,
        //   es señal de una query compuesta peligrosa.
        //
        // Control B — Subqueries destructivas en IN/EXISTS/ANY/ALL:
        //   Cubre el caso: WHERE id IN (SELECT id FROM x WHERE ... DELETE ...)
        //
        // Control C — SELECT con operaciones destructivas fuera de subqueries:
        //   Cubre inyecciones directas en el cuerpo del SELECT.
        // =========================================================================
        if ($mode === 'safe') {
            // A — keywords destructivas (lista ampliada)
            $dangerKeywords = [
                'INSERT', 'UPDATE', 'DELETE', 'DROP', 'ALTER',
                'TRUNCATE', 'REPLACE', 'CREATE', 'RENAME', 'LOCK',
            ];

            $found = array_values(array_filter(
                $dangerKeywords,
                fn(string $kw): bool => (bool) preg_match('/\b' . $kw . '\b/i', $sanitized)
            ));

            if (count($found) > 1) {
                return $fail(
                    'Query mezcla múltiples operaciones peligrosas: ' . implode(', ', $found),
                    $type
                );
            }

            // B — Operación destructiva dentro de subquery IN/EXISTS/ANY/ALL
            if (preg_match('/\b(?:IN|EXISTS|ANY|ALL)\s*\(.*\b(?:DELETE|UPDATE|DROP|INSERT)\b/is', $sanitized)) {
                return $fail('Subquery contiene operación destructiva', $type);
            }

            // C — SELECT cuyo cuerpo contiene verbos destructivos
            if ($type === 'SELECT' && preg_match('/\b(UPDATE|DELETE|INSERT|DROP)\b/i', $sanitized)) {
                return $fail('SELECT contiene operaciones peligrosas', $type);
            }
        }

        // =========================================================================
        // [9c] MODO PARANOID
        //
        // El modo más restrictivo: solo acepta SELECT plano.
        //
        // Control A — Solo SELECT.
        // Control B — Sin subqueries anidadas: bloquea ( ... SELECT ...
        // Control C — Sin UNION ni ninguna variante de JOIN ni INTO.
        //   UNION permite extraer datos de otras tablas.
        //   JOIN expone relaciones entre tablas.
        //   INTO permite escribir archivos (INTO OUTFILE).
        // Control D — Sin funciones de exfiltración/timing (ya cubiertas
        //   globalmente en [8], se repite aquí como documentación explícita
        //   del contrato del modo paranoid).
        // =========================================================================
        if ($mode === 'paranoid') {
            // A — Solo SELECT
            if ($type !== 'SELECT') {
                return $fail("Modo paranoid solo permite SELECT, se recibió '$type'", $type);
            }

            // B — Sin subqueries
            if (preg_match('/\(.*\bSELECT\b/i', $sanitized)) {
                return $fail('Modo paranoid no permite subqueries', $type);
            }

            // C — Sin UNION, JOIN (cualquier variante), INTO
            $forbiddenClauses = implode('|', [
                'UNION',
                'INNER\s+JOIN', 'LEFT\s+JOIN', 'RIGHT\s+JOIN',
                'FULL\s+JOIN',  'CROSS\s+JOIN', 'JOIN',
                'INTO',
            ]);
            if (preg_match('/\b(?:' . $forbiddenClauses . ')\b/i', $sanitized)) {
                return $fail('Modo paranoid no permite UNION, JOIN ni INTO', $type);
            }
        }

        // =========================================================================
        // [10] SELECT SIN FROM
        // Un SELECT sin FROM es casi siempre un error o una inyección de prueba
        // (p.ej. "SELECT 1", "SELECT version()").
        // Se evalúa sobre $sanitized para ignorar 'FROM' dentro de literales.
        // =========================================================================
        if ($type === 'SELECT' && !preg_match('/\bFROM\b/i', $sanitized)) {
            return $fail('SELECT sin FROM (posible query inválida)', $type);
        }

        // =========================================================================
        // [11] TAUTOLOGÍAS (anti OR 1=1 y variantes)
        //
        // Se opera sobre $sanitized: los literales ya fueron extraídos, por lo
        // que un WHERE name = '1 OR 1=1' NO dispara esta validación (correcto),
        // pero un WHERE id = 1 OR 1=1 SÍ la dispara (correcto).
        //
        // Variantes cubiertas (punto 3 del plan):
        //   A) Igualdad numérica trivial   : OR 1=1, OR 2=2
        //   B) Igualdad de identificadores : OR x=x (mismo token ambos lados)
        //   C) Booleano directo            : OR true, OR false (siempre sospechoso en WHERE)
        //   D) Comparación numérica obvia  : OR 2>1, OR 1<2
        //   E) IS NOT NULL incondicional   : OR id IS NOT NULL (siempre true si hay filas)
        // =========================================================================

        // A — OR/AND con número igual a sí mismo: OR 1=1, AND 2=2
        if (preg_match('/\b(?:OR|AND)\b\s*[\'"]?\d+[\'"]?\s*=\s*[\'"]?\d+[\'"]?/i', $sanitized)) {
            return $fail('Posible inyección SQL: condición numérica tautológica', $type);
        }

        // B — OR/AND con mismo identificador ambos lados: OR x=x
        if (preg_match('/\b(?:OR|AND)\b\s*(\w+)\s*=\s*\1\b/i', $sanitized)) {
            return $fail('Posible inyección SQL: condición tautológica (OR x=x)', $type);
        }

        // C — OR/AND con booleano literal: OR true, OR false
        if (preg_match('/\b(?:OR|AND)\b\s*\b(?:true|false)\b/i', $sanitized)) {
            return $fail('Posible inyección SQL: condición booleana literal', $type);
        }

        // D — OR/AND con comparación numérica obvia: OR 2>1, OR 1<2
        if (preg_match('/\b(?:OR|AND)\b\s*\d+\s*[><]\s*\d+/i', $sanitized)) {
            return $fail('Posible inyección SQL: comparación numérica siempre verdadera', $type);
        }

        // E — OR/AND <columna> IS NOT NULL (sospechoso en contexto de inyección)
        if (preg_match('/\b(?:OR|AND)\b\s*\w+\s+IS\s+NOT\s+NULL/i', $sanitized)) {
            return $fail('Posible inyección SQL: condición IS NOT NULL sospechosa', $type);
        }

        // =========================================================================
        // [12] WHITELIST DE TABLAS (punto 9 del plan)
        //
        // Si $tables no está vacío, extrae el nombre de tabla inmediatamente
        // después de FROM y verifica que esté en la whitelist.
        //
        // Limitación conocida: solo valida la primera tabla del FROM.
        // Para queries con múltiples tablas (JOIN, subqueries) se recomienda
        // usar esto solo en modo paranoid donde JOIN/subqueries ya están bloqueados.
        // =========================================================================
        if (!empty($tables)) {
            if (!preg_match('/\bFROM\s+(\w+)/i', $sanitized, $tableMatch)) {
                return $fail('No se pudo determinar la tabla de destino', $type);
            }

            $targetTable = strtolower($tableMatch[1]);
            $allowedTables = array_map('strtolower', $tables);

            if (!in_array($targetTable, $allowedTables, true)) {
                return $fail("Tabla '$targetTable' no está en la whitelist de tablas permitidas", $type);
            }
        }
        // =========================================================================
        // [13] BLACKLIST DE TABLAS SENSIBLES (control contextual)
        //
        // Objetivo:
        //  - Evitar acceso directo o modificación de tablas sensibles (ej: users)
        //  - Permitir JOIN siempre que NO se acceda a columnas sensibles
        //
        // Opciones nuevas:
        //   @type array $blacklist_tables   Tablas sensibles restringidas.
        //   @type array $sensitive_columns  Columnas prohibidas (ej: password).
        //
        // Ejemplo:
        //   'blacklist_tables'  => ['users'],
        //   'sensitive_columns' => ['password', 'passwd', 'token']
        // =========================================================================
        if (!empty($blacklistTables)) {

            $lowerQuery = strtolower($sanitized);
            $blacklistTables = array_map('strtolower', $blacklistTables);

            // ---------------------------------------------------------------------
            // A — Detectar tablas involucradas (FROM + JOIN)
            // ---------------------------------------------------------------------
            preg_match_all('/\b(?:from|join)\s+(\w+)/i', $lowerQuery, $matches);
            $usedTables = array_map('strtolower', $matches[1] ?? []);

            $intersectTables = array_intersect($usedTables, $blacklistTables);

            if (!empty($intersectTables)) {

                // -----------------------------------------------------------------
                // B — Bloquear modificaciones directas
                // -----------------------------------------------------------------
                if (in_array($type, ['UPDATE', 'DELETE', 'INSERT', 'REPLACE'], true)) {

                    // Detectar tabla objetivo principal
                    if (preg_match('/\b(?:update|into)\s+(\w+)/i', $lowerQuery, $mainTableMatch)) {
                        $mainTable = strtolower($mainTableMatch[1]);

                        if (in_array($mainTable, $blacklistTables, true)) {
                            return $fail("Modificación directa a tabla sensible '$mainTable' no permitida", $type);
                        }
                    }
                }

                // -----------------------------------------------------------------
                // C — SELECT directo sin JOIN (acceso completo)
                // -----------------------------------------------------------------
                if ($type === 'SELECT') {

                    $hasJoin = preg_match('/\bjoin\b/i', $lowerQuery);

                    if (!$hasJoin) {
                        return $fail(
                            'Acceso directo a tabla sensible no permitido (use JOIN controlado)',
                            $type
                        );
                    }

                    // -----------------------------------------------------------------
                    // D — Detección avanzada de columnas sensibles (soporte alias real)
                    // -----------------------------------------------------------------

                    // -------------------------------------------------------------
                    // 1. Construir mapa alias → tabla
                    //    Soporta:
                    //      FROM users u
                    //      FROM users AS u
                    //      JOIN users u2
                    // -------------------------------------------------------------
                    $aliasMap = [];

                    // FROM + JOIN con alias
                    preg_match_all(
                        '/\b(from|join)\s+(\w+)(?:\s+as)?\s+(\w+)/i',
                        $lowerQuery,
                        $aliasMatches,
                        PREG_SET_ORDER
                    );

                    foreach ($aliasMatches as $m) {
                        $table = strtolower($m[2]);
                        $alias = strtolower($m[3]);
                        $aliasMap[$alias] = $table;
                    }

                    // También incluir tablas sin alias (alias implícito = nombre tabla)
                    foreach ($usedTables as $tbl) {
                        $aliasMap[$tbl] = $tbl;
                    }

                    // -------------------------------------------------------------
                    // 2. Detectar acceso a columnas con alias (u.password)
                    // -------------------------------------------------------------
                    foreach ($aliasMap as $alias => $table) {

                        // Solo validar tablas sensibles
                        if (!in_array($table, $blacklistTables, true)) {
                            continue;
                        }

                        foreach ($sensitiveColumns as $col) {

                            // Detecta:
                            //   u.password
                            //   users.password
                            if (preg_match('/\b' . preg_quote($alias, '/') . '\.' . preg_quote($col, '/') . '\b/i', $lowerQuery)) {
                                return $fail(
                                    "Acceso a columna sensible '{$table}.{$col}' mediante alias '{$alias}' no permitido",
                                    $type
                                );
                            }
                        }

                        // ---------------------------------------------------------
                        // 3. Detectar SELECT alias.* (ej: u.*)
                        // ---------------------------------------------------------
                        if (preg_match('/\b' . preg_quote($alias, '/') . '\.\*/i', $lowerQuery)) {
                            return $fail(
                                "Acceso wildcard '{$alias}.*' a tabla sensible '{$table}' no permitido",
                                $type
                            );
                        }
                    }

                    // -------------------------------------------------------------
                    // 4. Fallback (por si no usan alias)
                    // -------------------------------------------------------------
                    foreach ($sensitiveColumns as $col) {
                        if (preg_match('/\b' . preg_quote(strtolower($col), '/') . '\b/i', $lowerQuery)) {
                            return $fail(
                                "Acceso a columna sensible '$col' no permitido",
                                $type
                            );
                        }
                    }
                }
            }
        }

        // =========================================================================
        // Query válida
        // =========================================================================
        return ['valid' => true, 'error' => null, 'type' => $type];
    }


    /**
     * Procesa una cadena SQL que contiene sentencias CREATE TABLE y genera una representación
     * simplificada de las tablas con sus columnas.
     *
     * El método realiza las siguientes operaciones:
     * 1. Elimina comentarios SQL de tipo línea (--) y bloque (/* * /).
     * 2. Identifica sentencias CREATE TABLE (incluyendo IF NOT EXISTS).
     * 3. Extrae correctamente el bloque de definición de columnas balanceando paréntesis.
     * 4. Separa las columnas evitando dividir por comas internas (por ejemplo, dentro de funciones o strings).
     * 5. Filtra únicamente las columnas reales, excluyendo restricciones e índices.
     *
     * El resultado es una cadena donde cada línea contiene:
     * nombre_tabla (columna1, columna2, columna3)
     *
     * @param string $sql Cadena SQL que contiene una o más sentencias CREATE TABLE
     *
     * @return string Representación simplificada de las tablas y sus columnas, separadas por saltos de línea
     *
     * @throws \Exception No lanza excepciones explícitas; depende de la validez estructural del SQL de entrada
     */
    public function minifyCreateTable($sql) {
        // Inicializa el arreglo que almacenará el resultado final
        $result = [];

        // Elimina comentarios de línea (--) del SQL
        $sql = preg_replace('/--.*(\n|$)/', '', $sql);

        // Elimina comentarios de bloque (/* */) del SQL
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

        // Busca todas las sentencias CREATE TABLE, capturando el nombre de la tabla y su posición
        preg_match_all('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?\s*\(/i', $sql, $matches, PREG_OFFSET_CAPTURE);

        // Itera sobre cada coincidencia encontrada
        foreach ($matches[1] as $index => $match) {
            // Nombre de la tabla detectada
            $tableName = $match[0];

            // Posición inicial de la sentencia CREATE TABLE
            $startPos  = $matches[0][$index][1];

            // Encuentra la posición del primer paréntesis de apertura
            $openPos = strpos($sql, '(', $startPos);

            // Longitud total del SQL
            $length  = strlen($sql);

            // Nivel de anidamiento de paréntesis
            $level   = 0;

            // Posición de cierre del bloque de columnas
            $endPos  = $openPos;

            // Recorre el SQL para encontrar el cierre correcto balanceando paréntesis
            for ($i = $openPos; $i < $length; $i++) {
                if ($sql[$i] === '(') $level++;
                if ($sql[$i] === ')') $level--;

                // Cuando el nivel vuelve a 0, se ha encontrado el cierre correspondiente
                if ($level === 0) {
                    $endPos = $i;
                    break;
                }
            }

            // Extrae el contenido interno del bloque de columnas
            $columnsRaw = substr($sql, $openPos + 1, $endPos - $openPos - 1);

            // Inicializa arreglo para almacenar las columnas separadas correctamente
            $columnsArr = [];

            // Buffer temporal para construir cada definición de columna
            $buffer = '';

            // Nivel de anidamiento de paréntesis dentro de columnas
            $level = 0;

            // Indicador de si se está dentro de una cadena de texto
            $inString = false;

            // Recorre carácter por carácter el bloque de columnas
            for ($i = 0; $i < strlen($columnsRaw); $i++) {
                $char = $columnsRaw[$i];

                // Detecta inicio o fin de cadenas de texto delimitadas por comillas simples
                if ($char === "'" && ($i === 0 || $columnsRaw[$i - 1] !== '\\')) {
                    $inString = !$inString;
                }

                // Solo analiza estructura si no está dentro de un string
                if (!$inString) {
                    if ($char === '(') $level++;
                    if ($char === ')') $level--;

                    // Si encuentra una coma en nivel 0, separa una definición de columna
                    if ($char === ',' && $level === 0) {
                        $columnsArr[] = trim($buffer);
                        $buffer = '';
                        continue;
                    }
                }

                // Acumula el carácter actual en el buffer
                $buffer .= $char;
            }

            // Agrega el último elemento si el buffer contiene datos
            if (trim($buffer) !== '') {
                $columnsArr[] = trim($buffer);
            }

            // Inicializa el arreglo final de nombres de columnas
            $columns = [];

            // Itera sobre cada definición separada
            foreach ($columnsArr as $col) {
                // Filtra definiciones que corresponden a restricciones o índices
                if (preg_match('/^(PRIMARY|FOREIGN|UNIQUE|KEY|INDEX|CONSTRAINT|CHECK)/i', $col)) {
                    continue;
                }

                // Extrae el nombre de la columna, permitiendo backticks opcionales
                if (preg_match('/^`?(\w+)`?\s+/i', $col, $m)) {
                    $columns[] = $m[1];
                }
            }

            // Construye la representación final de la tabla con sus columnas
            $result[] = $tableName . ' (' . implode(', ', $columns) . ')';
        }

        // Retorna todas las tablas procesadas separadas por saltos de línea
        return implode("\n", $result);
    }

    /**
     * Genera una representación simplificada de tablas y sus columnas a partir de un arreglo estructurado.
     *
     * Este método recorre un arreglo de tablas, extrae los nombres de las columnas definidas en cada una
     * y construye una cadena de texto donde cada línea contiene el nombre de la tabla seguido de sus columnas.
     *
     * El formato de salida es:
     * nombre_tabla (columna1, columna2, columna3)
     *
     * Solo se incluyen tablas que contengan las claves 'table' y 'data' con valores no vacíos.
     * Las columnas son extraídas a partir de una cadena separada por comas, ignorando cualquier definición adicional
     * como tipos de datos o restricciones.
     *
     * @param array $arrTables Arreglo de tablas donde cada elemento debe contener:
     *                         - 'table' (string): nombre de la tabla
     *                         - 'data' (string): definición de columnas separadas por comas
     *
     * @return string Cadena de texto con una tabla por línea y sus columnas listadas entre paréntesis
     *
     * @throws \Exception No lanza excepciones explícitamente, pero depende de la estructura correcta del arreglo de entrada
     */
    public function minifyArrayTables(array $arrTables): string {
        // Inicializa el arreglo que almacenará el resultado final
        $result = [];

        // Itera sobre cada definición de tabla en el arreglo de entrada
        foreach ($arrTables as $table) {
            // Valida que existan y no estén vacías las claves 'table' y 'data'
            if (empty($table['table']) || empty($table['data'])) {
                continue;
            }

            // Obtiene el nombre de la tabla
            $tableName  = $table['table'];

            // Obtiene la cadena cruda de columnas
            $columnsRaw = $table['data'];

            // Divide la cadena de columnas usando coma como separador
            $parts = explode(',', $columnsRaw);

            // Inicializa el arreglo que contendrá los nombres de columnas procesados
            $columns = [];

            // Itera sobre cada fragmento de columna
            foreach ($parts as $col) {
                // Elimina espacios en blanco al inicio y final de cada fragmento
                $col = trim($col);

                // Extrae el nombre de la columna utilizando una expresión regular
                // Permite nombres con o sin backticks (`) y captura solo el identificador
                if (preg_match('/^`?(\w+)`?\s+/i', $col, $match)) {
                    $columns[] = $match[1];
                }
            }

            // Construye la representación de la tabla con sus columnas y la agrega al resultado
            $result[] = $tableName . ' (' . implode(', ', $columns) . ')';
        }

        // Une todas las representaciones de tablas en una sola cadena separada por saltos de línea
        return implode("\n", $result);
    }

    /**
     * Genera una tabla HTML estilizada con Bootstrap a partir de un arreglo de datos.
     *
     * Este método construye dinámicamente un componente visual compuesto por:
     * - Un contenedor tipo "card"
     * - Un encabezado con título y botón de exportación a Excel
     * - Una tabla responsive con encabezados y filas generadas desde los datos
     *
     * Características principales:
     * - Si el arreglo está vacío, retorna un mensaje de advertencia
     * - Genera un ID único para la tabla (utilizado para exportación)
     * - Usa las claves del primer elemento como encabezados de la tabla
     * - Escapa los valores para prevenir inyección HTML
     *
     * @param array $data Arreglo de datos donde cada elemento representa una fila asociativa
     *                    (clave => valor). Todas las filas deben compartir las mismas claves.
     *
     * @return string HTML completo del componente Bootstrap con la tabla generada
     *
     * @throws \Exception No lanza excepciones explícitas; depende de la estructura del arreglo de entrada
     */
    public function arrayToBootstrapTable(array $data) {
        // Valida si el arreglo de datos está vacío
        if (empty($data)) {
            return '<div class="alert alert-warning">No hay datos disponibles</div>';
        }

        // Genera un identificador único para la tabla
        $tableId  = 'table_' . uniqid();

        // Genera un nombre de archivo basado en fecha y hora para exportación
        $fileName = 'detalle_' . date('Ymd_His');

        // Obtiene los encabezados a partir de las claves del primer elemento del arreglo
        $headers = array_keys(reset($data));

        // Inicializa el contenedor principal tipo card
        $html  = '<div class="card card-custom mb-3 shadow-sm">';

        // Construye el encabezado del card con título y botón de exportación
        $html .= '<div class="card-header d-flex justify-content-between align-items-center">';
        $html .= '<span>Tabla de datos</span>';
        $html .= '<button type="button" class="btn btn-sm btn-success" onclick="exportTableToExcel(\'' . $tableId . '\', \'' . $fileName . '\')"><i class="ri-file-excel-2-line"></i> Exportar a Excel</button>';
        $html .= '</div>';

        // Inicia el contenedor responsive para la tabla
        $html .= '<div class="table-responsive">';

        // Abre la tabla con el ID generado
        $html .= '<table id="' . $tableId . '" class="table table-borderless mb-0">';

        // Construye la sección THEAD con los encabezados
        $html .= '<thead class="table-light"><tr>';
        foreach ($headers as $header) {
            // Aplica formato al encabezado y escapa el contenido
            $html .= '<th scope="col">' . htmlspecialchars(ucfirst($header)) . '</th>';
        }
        $html .= '</tr></thead>';

        // Construye la sección TBODY con los datos
        $html .= '<tbody>';
        foreach ($data as $row) {
            $html .= '<tr>';

            // Recorre cada encabezado para mantener consistencia en columnas
            foreach ($headers as $header) {
                // Obtiene el valor correspondiente o asigna vacío si no existe
                $value = isset($row[$header]) ? $row[$header] : '';

                // Escapa el valor antes de insertarlo en la celda
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }

            $html .= '</tr>';
        }
        $html .= '</tbody>';

        // Cierra la tabla y los contenedores
        $html .= '</table></div></div>';

        // Retorna el HTML generado
        return $html;
    }

    /**
     * Genera un componente HTML que renderiza un gráfico utilizando la librería ApexCharts.
     *
     * Este método construye dinámicamente:
     * - Un contenedor tipo "card" con título
     * - Un elemento DIV donde se renderizará el gráfico
     * - Un script autoejecutable que inicializa el gráfico de forma asíncrona
     *
     * Características principales:
     * - Soporta distintos tipos de gráfico (bar, line, pie, etc.)
     * - Maneja carga asíncrona de la librería ApexCharts mediante reintentos
     * - Convierte los datos de entrada a formato JSON seguro para JavaScript
     * - Extrae etiquetas y valores desde un arreglo estructurado
     *
     * Estructura esperada de $data:
     * [
     *   ['label' => 'Categoría 1', 'value' => 10],
     *   ['label' => 'Categoría 2', 'value' => 20]
     * ]
     *
     * @param array  $data   Arreglo de datos con claves 'label' y 'value'
     * @param string $type   Tipo de gráfico compatible con ApexCharts (por defecto 'bar')
     * @param string $title  Título del gráfico
     * @param int    $height Altura del gráfico en píxeles
     *
     * @return string HTML + JavaScript necesario para renderizar el gráfico
     *
     * @throws \Exception No lanza excepciones explícitas; depende de la estructura de entrada y del entorno cliente
     */
    public function generateApexChart($data, $type = 'bar', $title = 'Gráfico', $height = 350) {

        // Valida si existen datos para graficar
        if (empty($data)) {
            return '<div class="alert alert-warning">No hay datos para graficar</div>';
        }

        // Genera un ID único para el contenedor del gráfico
        $chartId = 'chart_' . uniqid();

        // Inicializa arreglos para categorías (eje X) y valores (serie de datos)
        $categories = [];
        $values = [];

        // Recorre los datos para separar etiquetas y valores
        foreach ($data as $row) {
            $categories[] = $row['label'] ?? '';
            $values[]     = $row['value'] ?? 0;
        }

        // Convierte los arreglos a formato JSON para uso en JavaScript
        $categoriesJson = json_encode($categories);
        $valuesJson     = json_encode($values);

        // Construye el HTML y script necesario para renderizar el gráfico
        $html = '
        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <h6 class="card-title">'.htmlspecialchars($title).'</h6>
                <div id="'.$chartId.'"></div>
            </div>
        </div>

        <script>
        (function() {
            // Función encargada de renderizar el gráfico
            function renderChart() {
                // Verifica si la librería ApexCharts está disponible
                if (typeof ApexCharts === "undefined") {
                    // Reintenta luego de un breve intervalo si aún no está cargada
                    return setTimeout(renderChart, 100);
                }

                // Configuración del gráfico
                var options = {
                    chart: {
                        type: "'.$type.'",
                        height: '.$height.'
                    },
                    series: [{
                        name: "Valores",
                        data: '.$valuesJson.'
                    }],
                    xaxis: {
                        categories: '.$categoriesJson.'
                    },
                    title: {
                        text: "'.addslashes($title).'"
                    }
                };

                // Inicializa el gráfico en el contenedor correspondiente
                var chart = new ApexCharts(document.querySelector("#'.$chartId.'"), options);
                chart.render();
            }

            // Ejecuta la función inmediatamente para soportar inserciones dinámicas
            renderChart();
        })();
        </script>
        ';

        // Retorna el HTML completo con el gráfico
        return $html;
    }




}
