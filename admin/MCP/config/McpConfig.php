<?php
/**
 * Configuracion del servidor MCP de coreEngine.
 *
 * Constantes de protocolo, limites, cuotas, cache y listas negras. Sin estado.
 *
 * @see projects/mcp-servidor/specs/technical.md §3
 */
class McpConfig
{
    /**********************************************************************************************************************************/
    /*                                                  Protocolo y servidor                                                          */
    /**********************************************************************************************************************************/
    public const PROTOCOL_VERSION  = '2025-06-18';
    public const PROTOCOL_VERSIONS = ['2025-06-18', '2025-03-26', '2024-11-05'];
    public const SERVER_INFO       = ['name' => 'coreengine-mcp', 'title' => 'coreEngine MCP', 'version' => '1.0.0'];
    public const INSTRUCTIONS      = 'Servidor de solo lectura de coreEngine. Use list_modules para ver los módulos disponibles y describe_module antes de consultar una tabla.';

    /**********************************************************************************************************************************/
    /*                                              Limites, cuotas y cache                                                           */
    /**********************************************************************************************************************************/
    public const MAX_BODY_BYTES        = 1048576;
    public const MAX_RESPONSE_BYTES    = 2097152;
    public const MAX_BATCH             = 20;
    public const MAX_JSON_DEPTH        = 64;
    public const DEFAULT_LIMIT         = 25;
    public const MAX_LIMIT             = 100;
    public const MAX_OFFSET            = 10000;
    public const MAX_FILTERS           = 5;
    public const SEARCH_MIN            = 2;
    public const SEARCH_MAX            = 100;
    public const MAX_SEARCH_COLUMNS    = 10;
    public const MAX_CELL_CHARS        = 2000;
    public const MAX_LOG_ARGS_CHARS    = 2000;
    public const MAX_STATEMENT_SECONDS = 5;
    public const MAX_EXECUTION_SECONDS = 30;
    public const CACHE_FILE            = MCP_ROOT . '/cache/catalogo.json';
    public const CACHE_TTL             = 300;
    public const RATE_IP_MAX           = 120; // ventana: ConfigAPP::APP['rateLimiterGuestWindowSeconds'] (D-8)
    public const RATE_TOKEN_MAX        = 480; // ventana: ConfigAPP::APP['rateLimiterUserWindowSeconds'] (D-8)

    /**********************************************************************************************************************************/
    /*                                            Servidor de autorizacion OAuth                                                      */
    /**********************************************************************************************************************************/
    // Ventanas: ConfigAPP::APP['rateLimiterGuestWindowSeconds'] (claves por IP) y ['rateLimiterUserWindowSeconds'] (claves por token consultado)
    public const OAUTH_SCOPE           = 'mcp';              // unico scope emitido (D-10)
    public const OAUTH_CODE_TTL        = 60;                 // segundos (D-2)
    public const OAUTH_ACCESS_TTL      = 3600;               // segundos (D-2)
    public const OAUTH_REFRESH_TTL     = 2592000;            // 30 dias (D-2)
    public const OAUTH_REFRESH_MAX_TTL = 7776000;            // 90 dias, maximo de familia (D-2)
    public const OAUTH_REFRESH_GRACE   = 10;                 // segundos: reuso de un refresh recien rotado por concurrencia (D-2)
    public const OAUTH_IP_MAX          = 60;                 // peticiones por clave de IP y ventana de invitados (oauth-ip:<ipKey>)
    public const OAUTH_REG_MAX         = 3;                  // registros por clave de IP y ventana de invitados (oauth-reg:<ipKey>, D-3)
    public const OAUTH_REG_IP_DIA      = 20;                 // registros por clave de IP (IPv4 o IPv6 /64) en 24 h, contados en BD (D-3, B-5)
    public const OAUTH_UNUSED_MAX      = 100;                // tope global de clientes dinamicos que nunca obtuvieron acceso (D-3)
    public const OAUTH_UNUSED_HOURS    = 24;                 // horas antes de purgar un cliente sin uso (D-4, B-5)
    public const OAUTH_CRED_IP_MAX     = 10;                 // consultas de token por clave de IP y ventana corta (oauth-cred-ip:<ipKey>)
    public const OAUTH_CRED_ID_MAX     = 5;                  // consultas por token consultado y ventana larga (oauth-cred-id:<sha256>)
    public const OAUTH_CRED_RID_MAX    = 5;                  // intentos fallidos por peticion rid; superado, la peticion se descarta
    public const OAUTH_CLEANUP_ODDS    = 50;                 // 1 de cada N /token ejecuta la limpieza (D-4)
    public const OAUTH_CLEANUP_BATCH   = 500;                // filas por tabla y pasada (D-4)
    public const OAUTH_MAX_BODY_BYTES  = 65536;              // cuerpo maximo de los POST del AS
    public const OAUTH_MAX_STATE_CHARS = 1024;               // longitud maxima de state
    public const OAUTH_PENDING_SECONDS = 600;                // vida de cada peticion de autorizacion guardada
    public const OAUTH_PENDING_MAX     = 5;                  // peticiones pendientes por sesion del AS (se descartan las mas antiguas)
    public const OAUTH_SESSION_NAME    = 'coreengine_oauth'; // nombre de la sesion PHP del AS

    /**********************************************************************************************************************************/
    /*                                    Exclusiones, lista negra y columna de cuenta                                                */
    /**********************************************************************************************************************************/
    public const EXCLUDED_MODULES  = ['installer', 'tokens'];
    public const BLACKLIST_TABLES  = ['/^tokens_/i', '/^core_permisos_/i', '/^usuarios_/i', '/permisos/i', '/(^|_)logs?$/i'];
    public const HIDDEN_TABLES     = ['terceros_entidades_listado', 'terceros_entidades_listado_usuarios', 'terceros_entidades_listado_usuarios_maq', 'terceros_entidades_listado_usuarios_noti']; // nombre exacto (D-30)
    public const ACCOUNT_COLUMN    = '/pass/i'; // columna que case = oculta siempre, ya no oculta la tabla (D-22 revisada)
    public const HIDDEN_COLUMNS    = ['Direccion_img']; // nombre exacto, oculta siempre en todas las tablas; no anulable por PII_EXEMPT ni manifiesto (D-32)
    public const BLACKLIST_COLUMNS = ['/clave/i', '/token/i', '/secret/i', '/hash/i', '/salt/i', '/api_?key/i', '/otp/i', '/^pin$/i', '/cookie/i', '/sesion/i', '/session/i', '/(^|_)ip(_|$)/i'];

    /**********************************************************************************************************************************/
    /*                                                Datos personales (D-14)                                                         */
    /**********************************************************************************************************************************/
    public const PII_COLUMNS = ['/rut$/i', '/e_?mail/i', '/fono/i', '/celular|movil/i', '/direcci[oó]n/i', '/^(lat|lng|lon|long|latitud|longitud)$/i', '/latitud|longitud/i', '/nacimiento/i', '/nick/i', '/agent/i', '/ultimo_?acceso/i', '/^replegal/i'];
    // Excepcion por tabla o columna (D-14 rev. 9, D-32). Claves opcionales: todas = PII_COLUMNS no se aplica a la tabla;
    // columnas = nombres exactos eximidos de PII_COLUMNS; liberar = patrones de BLACKLIST_COLUMNS que no se aplican;
    // ocultar = patrones que siguen ocultos. No libera HIDDEN_COLUMNS, ACCOUNT_COLUMN ni BLACKLIST_COLUMNS no listados en liberar.
    public const PII_EXEMPT = [
        'entidades_listado'              => ['todas' => true, 'liberar' => ['/(^|_)ip(_|$)/i'], 'ocultar' => ['/^replegal/i']],
        'solicitantes_listado'           => ['todas' => true, 'liberar' => ['/(^|_)ip(_|$)/i'], 'ocultar' => ['/^replegal/i']],
        'entidades_listado_contactos'    => ['todas' => true],
        'solicitantes_listado_contactos' => ['todas' => true],
        'vehiculos_listado'              => ['columnas' => ['Latitud', 'Longitud']],
        'entidades_listado_cargas'       => ['columnas' => ['FNacimiento']],
    ];

    /**********************************************************************************************************************************/
    /*                                       Enriquecimiento de usuarios (D-31)                                                       */
    /**********************************************************************************************************************************/
    public const USER_TABLE        = 'usuarios_listado';
    public const USER_PK           = 'idUsuario';
    public const USER_NAME_COLUMNS = ['Nombre']; // whitelist: solo estas columnas se leen de USER_TABLE
    public const USER_ID_COLUMNS   = ['idUsuario', 'idUsuarioEdicion', 'idUsuarioCreador', 'idUsuarioResponsable'];

    /**********************************************************************************************************************************/
    /*                                                      Metodos                                                                   */
    /**********************************************************************************************************************************/
    /**
     * Origenes permitidos para CORS, de la variable de entorno MCP_ALLOWED_ORIGINS.
     *
     * Lista separada por comas, con trim, sin vacios, sin "null" ni "*" (H-18).
     *
     * @return string[]
     */
    public static function allowedOrigins(): array
    {
        $raw = getenv('MCP_ALLOWED_ORIGINS');
        if ($raw === false || $raw === '') {
            return [];
        }
        $origins = [];
        foreach (explode(',', $raw) as $origin) {
            $origin = trim($origin);
            if ($origin === '' || $origin === '*' || strtolower($origin) === 'null') {
                continue;
            }
            $origins[] = $origin;
        }
        return $origins;
    }

    /**
     * Limite efectivo: la constante o, si es menor, el valor de MCP_<nombre> (solo permite bajar).
     *
     * @param string $nombre RATE_TOKEN_MAX o MAX_RESPONSE_BYTES
     * @return int
     * @throws InvalidArgumentException si el nombre no es uno de los permitidos
     */
    public static function limit(string $nombre): int
    {
        if ($nombre === 'RATE_TOKEN_MAX') {
            $max = self::RATE_TOKEN_MAX;
        } elseif ($nombre === 'MAX_RESPONSE_BYTES') {
            $max = self::MAX_RESPONSE_BYTES;
        } else {
            throw new InvalidArgumentException('Limite no configurable: ' . $nombre);
        }
        $env = (int) getenv('MCP_' . $nombre);
        return $env > 0 ? min($max, $env) : $max;
    }

    /**
     * URL publica del MCP, de la variable de entorno MCP_PUBLIC_URL (D-16).
     *
     * trim y quita una "/" final. Valida si filter_var la acepta, el scheme es https (o http con host
     * localhost, 127.0.0.1 o [::1]), hay host y no hay query, fragment, user ni pass.
     *
     * @return string|null URL sin barra final o null si falta o no es valida
     */
    public static function publicUrl(): ?string
    {
        $raw = getenv('MCP_PUBLIC_URL');
        if ($raw === false) {
            return null;
        }
        $url = trim($raw);
        if (str_ends_with($url, '/')) {
            $url = substr($url, 0, -1);
        }
        if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }
        $p = parse_url($url);
        if ($p === false || !isset($p['scheme'], $p['host']) || $p['host'] === '') {
            return null;
        }
        if (isset($p['query']) || isset($p['fragment']) || isset($p['user']) || isset($p['pass'])) {
            return null;
        }
        if ($p['scheme'] === 'https') {
            return $url;
        }
        if ($p['scheme'] === 'http' && in_array($p['host'], ['localhost', '127.0.0.1', '[::1]'], true)) {
            return $url;
        }
        return null;
    }

    /**
     * Issuer del servidor de autorizacion: publicUrl() . '/oauth'.
     *
     * @return string|null Issuer o null si MCP_PUBLIC_URL no es valida
     */
    public static function issuerUrl(): ?string
    {
        $base = self::publicUrl();
        return $base === null ? null : $base . '/oauth';
    }

    /**
     * URI canonica del MCP (recurso protegido) con barra final: publicUrl() . '/public/' (M-7, RFC 9728 §3.3).
     *
     * @return string|null URI del recurso o null si MCP_PUBLIC_URL no es valida
     */
    public static function resourceUrl(): ?string
    {
        $base = self::publicUrl();
        return $base === null ? null : $base . '/public/';
    }

    /**
     * URL de los metadatos del recurso protegido (RFC 9728 §3.1, insercion de ruta en la raiz del host).
     *
     * Origen de publicUrl() + /.well-known/oauth-protected-resource + ruta de resourceUrl() sin la barra final.
     * Requiere el fragmento de la raiz del host (D-1, admin/MCP/docs/well-known.htaccess.example).
     *
     * @return string|null URL de metadatos o null si MCP_PUBLIC_URL no es valida
     */
    public static function resourceMetadataUrl(): ?string
    {
        $base = self::publicUrl();
        if ($base === null) {
            return null;
        }
        $p = parse_url($base);
        $origen = $p['scheme'] . '://' . $p['host'] . (isset($p['port']) ? ':' . $p['port'] : '');
        return $origen . '/.well-known/oauth-protected-resource' . ($p['path'] ?? '') . '/public';
    }
}
