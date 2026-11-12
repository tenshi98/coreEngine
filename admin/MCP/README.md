# Servidor MCP de coreEngine (`admin/MCP/`)

Servidor [Model Context Protocol](https://modelcontextprotocol.io) de **solo lectura**: expone a clientes de IA los datos de los módulos instalados de `admin/app/modules/` mediante 5 tools y un resource de esquema por módulo. Proyecto SDD: `projects/mcp-servidor/` (specs en `specs/`). Decisiones: ADR-0011 (manifiesto `mcp.php`), ADR-0012 (alcance del token), ADR-0013 (installer idempotente), ADR-0015 (acceso por módulo, criterio sustituido por ADR-0019), ADR-0016 (PII por columna y caché endurecida), en `docs/architecture/decisions/`.

## Estructura

```text
admin/MCP/
├── .htaccess            # deniega bootstrap.php, *.md y *.json; Options -Indexes
├── bootstrap.php        # configuración, ErrorHandler, RateLimiter, F3 (ONERROR genérico, QUIET, DEBUG 0), AUTOLOAD
├── public/              # único directorio servido: index.php (transporte Streamable HTTP, solo POST) + .htaccess
├── oauth/               # servidor de autorización OAuth: index.php (front controller), views/ (consent, error) y .htaccess
├── bin/                 # CLI: mcp-stdio.php (puente stdio -> HTTP), mcp-selftest.php (script de humo)
├── config/McpConfig.php # constantes: límites, listas negras, PII_EXEMPT, HIDDEN_COLUMNS
├── cache/               # catalogo.json (caché del catálogo, 0640) y su .lock
├── docs/                # CONEXION.md (guía por cliente), mcp.example.php (manifiesto) y well-known.htaccess.example (fragmento OAuth)
└── src/                 # Server, Auth, Discovery, Tools, Resources, Audit, Security y OAuth (clases sin namespace)
```

`bin/`, `config/`, `src/` y `docs/` llevan `.htaccess` con `Require all denied`; los archivos de `src/` y `config/` empiezan con `defined('MCP_ROOT') || exit;` y los de `bin/` con `PHP_SAPI === 'cli' || exit;`. `cache/` no tiene `.htaccess` propio: el `*.json` queda denegado por el `.htaccess` de la raíz.

## Endpoint y tools

URL (dev): `http://localhost/coreEngine/admin/MCP/public/`. Todo el protocolo viaja en el cuerpo JSON-RPC por `POST` con `Authorization: Bearer <token>`. Guía por cliente: [`docs/CONEXION.md`](docs/CONEXION.md). Contrato completo: `projects/mcp-servidor/specs/api.md`.

| Tool | Uso |
|---|---|
| `list_modules` | módulos y tablas accesibles con el token |
| `describe_module` | columnas visibles (tipo, descripción, `indexed`) de un módulo |
| `list_records` | filas de una tabla con `filters` de igualdad, `order_by` (PK o columna indexada), `limit` ≤ 100 y `cursor` |
| `search_records` | búsqueda `LIKE` sobre columnas de texto visibles |
| `get_record` | una fila por clave primaria (`id` entero) |

Resource: `mcp://modules/<modulo>/schema`.

## Configuración

Variables leídas con `getenv` (el `.env` de `admin/app/config/` se carga con `EnvLoader` y las exporta con `putenv`):

| Variable | Uso |
|---|---|
| `DB_MCP_HOSTNAME`, `DB_MCP_USERNAME`, `DB_MCP_PASSWORD`, `DB_MCP_DATABASE`, `DB_MCP_CHARSET`, `DB_MCP_PORT` | cuenta de BD propia del MCP (`ConfigDataBase::MySQL_MCP`); plantilla en `admin/app/config/.env.example` |
| `MCP_ALLOWED_ORIGINS` | orígenes CORS permitidos, separados por comas; vacío por defecto; `null` y `*` se descartan. Hace falta para clientes de navegador, p. ej. MCP Inspector (`http://localhost:6274`) |
| `MCP_PUBLIC_URL` | URL pública del MCP sin `/public` ni `/oauth` (dev `http://localhost/coreEngine/admin/MCP`); `https`, o `http` solo en `localhost`, `127.0.0.1` o `[::1]`; sin query, fragmento ni credenciales (`McpConfig::publicUrl`). **Sin ella el servidor de autorización responde 503 y el 401 del MCP no lleva `resource_metadata`** (sección "Servidor de autorización OAuth") |
| `MCP_RATE_TOKEN_MAX`, `MCP_MAX_RESPONSE_BYTES` | solo bajan el límite (`McpConfig::limit`); los usa el script de humo en dev |

### Cuenta de BD (estado actual: pendiente de operador)

El código usa `MySQL_MCP`, pero **la cuenta MySQL de solo lectura aún no existe**: `DB_MCP_*` apunta temporalmente a la cuenta `root` del entorno de desarrollo, que no sirve para verificar los `GRANT` ni es aceptable fuera de desarrollo. **Pendiente de operador**: crear la cuenta, aplicar los `GRANT` siguientes y sustituir `DB_MCP_USERNAME`/`DB_MCP_PASSWORD`. `<db>` = `DB_MCP_DATABASE` (misma base que `DB_MYSQL1_DATABASE`), `<host>` = host desde el que conecta PHP. Fuente: `projects/mcp-servidor/specs/database.md` §Cuenta de BD del MCP.

```sql
CREATE USER 'coreengine_mcp'@'<host>' IDENTIFIED BY '<secreto>';
-- Tablas propias del MCP
GRANT SELECT ON `<db>`.`tokens_listado`              TO 'coreengine_mcp'@'<host>';
GRANT SELECT ON `<db>`.`tokens_listado_permisos`     TO 'coreengine_mcp'@'<host>';
GRANT SELECT ON `<db>`.`core_permisos_listado`       TO 'coreengine_mcp'@'<host>';   -- ya no necesario para el alcance (ADR-0019); retirarlo es opcional, de operador
GRANT SELECT ON `<db>`.`core_permisos_listado_rutas` TO 'coreengine_mcp'@'<host>';
GRANT INSERT ON `<db>`.`tokens_mcp_log`              TO 'coreengine_mcp'@'<host>';
-- Enriquecimiento de usuarios (D-31): solo estas dos columnas, nunca la tabla entera
GRANT SELECT (`idUsuario`, `Nombre`) ON `<db>`.`usuarios_listado` TO 'coreengine_mcp'@'<host>';
-- Tablas de módulos: generar una línea por tabla y ejecutar el resultado
SELECT CONCAT('GRANT SELECT ON `', TABLE_SCHEMA, '`.`', TABLE_NAME, '` TO ''coreengine_mcp''@''<host>'';')
  FROM information_schema.TABLES
 WHERE TABLE_SCHEMA = '<db>'
   AND TABLE_NAME NOT LIKE 'usuarios\_%'
   AND TABLE_NAME NOT LIKE 'core\_%'
   AND TABLE_NAME NOT LIKE 'tokens\_%';
```

**GRANT del servidor de autorización OAuth** (spec: `projects/mcp-oauth/specs/database.md` §7; **solo sobre `tokens_*`**; se suman a los anteriores, que ya dan `SELECT` en `tokens_listado` e `INSERT` en `tokens_mcp_log`, columna `idClienteOAuth` incluida). **Estado: pendiente de operador, no aplicados** (hoja TASK-007.9 de `mcp-oauth`, bloqueada hasta que exista la cuenta):

```sql
-- Hijos OAuth: alta, rotación del valor y de la expiración, revocación y limpieza
GRANT INSERT, DELETE, UPDATE (`Token`, `FechaExpiracion`) ON `<db>`.`tokens_listado` TO 'coreengine_mcp'@'<host>';
-- Clientes: lectura completa (el AS valida cliente y secreto), alta por registro, borrado por purga, uso por canje
GRANT SELECT, INSERT, DELETE, UPDATE (`FechaUltimoUso`) ON `<db>`.`tokens_oauth_clientes` TO 'coreengine_mcp'@'<host>';
-- Códigos y refresh
GRANT SELECT, INSERT, UPDATE, DELETE ON `<db>`.`tokens_oauth_codigos` TO 'coreengine_mcp'@'<host>';
GRANT SELECT, INSERT, UPDATE, DELETE ON `<db>`.`tokens_oauth_refresh` TO 'coreengine_mcp'@'<host>';
```

- El `UPDATE` se limita por columnas (H-17) a lo que modifica el servidor de autorización: `Token` y `FechaExpiracion` de los hijos y `FechaUltimoUso` del cliente. El estado (`idEstado`) solo lo cambia el backoffice.
- Consecuencia (RA-5): la cuenta del MCP deja de ser de solo lectura en `tokens_*`. Las tools siguen sin exponer tablas `tokens_*` (`BLACKLIST_TABLES`, `EXCLUDED_MODULES`).
- Zona horaria: la sesión MySQL de `coreengine_mcp` debe tener la zona horaria de la aplicación (`default_time_zone` del servidor o `SET time_zone` al conectar); las fechas del flujo se calculan en SQL (`NOW()`, `CURDATE()`).
- Sin estos GRANT el registro, la autorización y el canje OAuth no funcionan; la autenticación con tokens manuales no depende de ellos.

**Alcance de la cuenta tras ambos bloques:**

- Sin `CREATE`, `DROP`, `ALTER`, `FILE`, `PROCESS` ni `GRANT OPTION`; ninguna tabla `usuarios_*` completa. **`UPDATE` y `DELETE` solo existen sobre `tokens_*`** (servidor de autorización); el resto de tablas, incluidas las de módulos, siguen siendo de solo lectura.
- **Al instalar un módulo nuevo**, volver a ejecutar el `SELECT CONCAT(...)` y aplicar las líneas que falten. Hasta entonces las tools sobre sus tablas responden `-32603`; el catálogo sí las lista.
- Verificación del operador: `SHOW GRANTS FOR 'coreengine_mcp'@'<host>'` no debe contener tablas `usuarios_*` completas ni escritura fuera de `tokens_*` (`INSERT` en `tokens_mcp_log` y los GRANT OAuth de arriba) (TASK-012.4).
- La conexión fija `SET SESSION max_statement_time = 5` y desactiva el log de `QueryBuilder` (`McpDb::conn`).

## Alta de un token y su alcance

1. En el backoffice, `Administración > Tokens` (permiso `Tokens - Listado`, solo para administradores): crear el token (el servidor genera 64 hexadecimales), estado Activo y, si se quiere, fecha de caducidad.
2. En la ficha del token (`Resumen`, nivel >= 2 sobre `tokensListado`), pestaña "Permisos": activar los **módulos** a los que el token puede acceder (interruptor por módulo, sin nivel; solo aparecen los módulos instalados que el MCP puede exponer; ADR-0019). El `-View` es de solo lectura, ADR 0018.
3. **Alcance propio del token**: la matriz vive en `tokens_listado_permisos` y no depende de ningún usuario. El token acepta si existe, `idEstado = 1` y no está caducado; su alcance es la lista de módulos asignados. Sin módulos -> 403; token ausente, inexistente, inactivo o caducado -> 401 idéntico.
4. Desactivar, caducar o quitar módulos surte efecto en la siguiente petición.

Detalle del módulo: `admin/app/modules/tokens/README.md`.

## Servidor de autorización OAuth (`oauth/`, proyecto `mcp-oauth`)

Además de los tokens manuales, el MCP acepta **access tokens OAuth 2.1** emitidos por un servidor de autorización propio (issuer = `MCP_PUBLIC_URL` + `/oauth`). Guía de conexión y duraciones: [`docs/CONEXION.md`](docs/CONEXION.md) §7. Spec: `projects/mcp-oauth/specs/`. Código: `oauth/index.php` (front controller) y `src/OAuth/`. Los tokens manuales siguen funcionando igual.

| Endpoint | Uso |
|---|---|
| `GET /.well-known/oauth-protected-resource<ruta MCP>/public` y `…/oauth-authorization-server<ruta MCP>/oauth` | metadatos RFC 9728 y RFC 8414, **en la raíz del host** (fragmento obligatorio, abajo) |
| `POST <issuer>/register` | registro dinámico de clientes (RFC 7591); el cliente queda activo |
| `GET/POST <issuer>/authorize` | pantalla propia donde se **pega un token manual** (o se deniega); PKCE `S256` obligatorio |
| `POST <issuer>/token` | canje del código y renovación con rotación del refresh token |
| `POST <issuer>/revoke` | revocación (RFC 7009) |

- **Permisos**: el access token es una fila hija (`Origen` 2) del token pegado en `tokens_listado`; su alcance, su cuota y su estado son los del token padre. No hay inicio de sesión de personas.
- **Fragmento `.well-known` (obligatorio, D-1)**: `docs/well-known.htaccess.example` se instala en la raíz del host; pasos en `docs/CONEXION.md` §7.6. Sin él los clientes no descubren los metadatos (instalación en dev: hoja de operador TASK-006.14, pendiente).
- **Requisitos**: `MCP_PUBLIC_URL` (sin ella, 503), PHP 8.1 o superior (`array_is_list` y el tipo `never`), zona horaria de la BD igual a la de la app y los GRANT de la sección anterior. **"Actualizar Modulo" del módulo `tokens` debe aplicarse antes de usar el MCP**: `TokenAuthenticator` consulta las columnas nuevas de `tokens_listado` (`Origen`, `idTokenPadre`, `idClienteOAuth`, `FechaExpiracion`), `TokenScope` lee `tokens_listado_permisos.Modulo` (el installer migra los permisos antiguos a módulos, ver `app/modules/tokens/README.md` §Migración del alcance) y las tablas `tokens_oauth_*` las crea ese instalador. El alcance del token es la lista de módulos asignados; con alcance vacío, 403.
- **Conexión de BD**: el servidor de autorización usa la de este MCP (`MySQL_MCP`, `McpDb::conn()`), nunca la sesión del backoffice.
- **Límites** (`McpConfig::OAUTH_*`): código 60 s, access token 3600 s, refresh token 30 días con tope de 90 por concesión, gracia de rotación 10 s; límites de registro y de intentos en `docs/CONEXION.md` §7.4.
- Verificación: implementado y probado con dobles de BD y servidores PHP temporales; **sin verificar aún contra dev con BD** (TASK-006.15, TASK-007.10 y TASK-008.20 del proyecto, pendientes). Script de humo: casos 28 a 41 de `bin/mcp-selftest.php` (variables `MCP_PUBLIC_URL` y `MCP_OAUTH_*` en su cabecera).

## Acceso por módulo, tablas y columnas ocultas (ADR-0015, ADR-0016)

**Acceso por módulo (ADR-0019).** Una tabla es accesible si su módulo está en el alcance del token (`tokens_listado_permisos.Modulo`, asignado en la pestaña Permisos de la ficha). No hay nivel, ni permiso de `core_permisos_listado`, ni permiso propietario por tabla. Un módulo o tabla fuera de alcance responde `-32602`, como si no existiera. Quedan excluidos los módulos `installer` y `tokens` (`EXCLUDED_MODULES`).

**Restricción opcional por manifiesto.** El módulo puede incluir `admin/app/modules/<modulo>/mcp.php` (ADR-0011) para ocultar tablas o columnas y describirlas. La clave `permiso` ya no existe (ADR-0019): el acceso es por módulo y un manifiesto que la incluya es inválido y excluye el módulo. El manifiesto solo restringe: nunca amplía ni libera lo que ocultan las listas de `McpConfig`. Formato cerrado; uno inválido, con salida o con excepción excluye el módulo entero. Ejemplo comentado: [`docs/mcp.example.php`](docs/mcp.example.php). Hoy ningún módulo del repo tiene `mcp.php`.

**Qué no sale nunca** (`config/McpConfig.php`):

| Regla | Efecto |
|---|---|
| `BLACKLIST_TABLES` | tablas `tokens_*`, `core_permisos_*`, `usuarios_*`, las que contengan `permisos` y los logs |
| `HIDDEN_TABLES` | por nombre exacto: `terceros_entidades_listado`, `terceros_entidades_listado_usuarios`, `terceros_entidades_listado_usuarios_maq`, `terceros_entidades_listado_usuarios_noti` (enlaces y cuentas de terceros) |
| `ACCOUNT_COLUMN` (`/pass/i`) | oculta la **columna** (`password*`, `PasswordForzarCambio`, `PasswordUltimoCambio`) en todas las tablas; la tabla sigue siendo consultable |
| `BLACKLIST_COLUMNS` | columnas secretas (clave, token, hash, otp, sesión, IP...) |
| `PII_COLUMNS` | `Rut`, `Email`, teléfonos, direcciones, coordenadas, nacimiento, nick, agente, último acceso y `RepLegal*`, ocultos salvo excepción |

**`PII_EXEMPT`** (excepción por tabla o columna, ADR-0016). Claves opcionales por entrada: `todas` (no se aplica `PII_COLUMNS` a la tabla), `columnas` (nombres exactos eximidos), `liberar` (patrones de `BLACKLIST_COLUMNS` que no se aplican) y `ocultar` (patrones que siguen ocultos). Entradas actuales:

| Tabla | Excepción |
|---|---|
| `entidades_listado`, `solicitantes_listado` | `todas`, `liberar` IP (`IP_Client`), `ocultar` `RepLegal*` |
| `entidades_listado_contactos`, `solicitantes_listado_contactos` | `todas` |
| `vehiculos_listado` | `columnas`: `Latitud`, `Longitud` |
| `entidades_listado_cargas` | `columnas`: `FNacimiento` |

Cómo añadir una excepción de columna: agregar una entrada a `McpConfig::PII_EXEMPT` con `columnas => ['NombreExacto']`, regenerar el catálogo (`rm admin/MCP/cache/catalogo.json`) y verificar con `describe_module`. Toda excepción nueva es una decisión del usuario que debe constar en un ADR o en su entrada de history.

**`HIDDEN_COLUMNS = ['Direccion_img']`**: se oculta siempre en todas las tablas (ruta de fichero del servidor), primera regla de `SchemaFilter::filterColumns`; ni `PII_EXEMPT` ni el manifiesto la liberan.

**Enriquecimiento de usuarios.** Las columnas visibles `idUsuario`, `idUsuarioEdicion`, `idUsuarioCreador` e `idUsuarioResponsable` (salvo la PK de la propia tabla) se acompañan de `<columna>_nombre`, resuelto con una consulta por página a `usuarios_listado` limitada por whitelist en código (`USER_PK` + `USER_NAME_COLUMNS` = `idUsuario`, `Nombre`) y en BD (`GRANT SELECT (idUsuario, Nombre) ON usuarios_listado`, ver arriba). `usuarios_listado` no es consultable por las tools. **Sin ese `GRANT`, el MCP degrada**: devuelve los registros sin `<columna>_nombre` y hace `error_log` interno, sin `-32603` ni detalle al cliente. Con la cuenta temporal `root` el enriquecimiento funciona, pero eso no prueba el `GRANT` por columnas.

**Caché del catálogo.** `cache/catalogo.json` vive 300 s (reconstrucción con `flock` no bloqueante; con la caché caducada como respaldo). `ModuleScanner::loadCache` **no confía en ella** (ADR-0016): contrasta con el código el módulo y sus controladores, la pertenencia de cada tabla al installer del módulo (y su `pk`), que el `modulo` de cada tabla sea el del módulo y que no traiga `permisos` ni `permiso` (una caché antigua se descarta y se reconstruye) y reaplica el manifiesto real; cualquier discrepancia devuelve `null` y se reconstruye. Borrar `admin/MCP/cache/catalogo.json` fuerza la reconstrucción. No se compara el contenido de `columnas` con el DDL. Se escribe con `0640` mediante temporal + `rename`.

## Seguridad y límites

| Control | Valor |
|---|---|
| Autenticación | `Authorization: Bearer <64 hex>` solo por cabecera; token en la query string o en `token`/`access_token` -> 400 |
| Orden del transporte | método -> límite IP -> token en URL -> Origin -> autenticación -> límite token -> alcance -> cuerpo |
| Límite por IP / por token | 120 por ventana de 60 s / 480 por ventana de 480 s; cada mensaje de un lote cuenta; `429` con `Retry-After` |
| Tamaños | cuerpo 1 MiB, respuesta 2 MiB, lote 20, JSON profundidad 64, celdas de texto 2000 caracteres |
| Consultas | `limit` <= 100, offset <= 10 000, filtros <= 5, `max_statement_time` 5 s, `set_time_limit(30)`, `order_by` solo PK o columna indexada |
| CORS | solo `Origin` idéntico a uno de `MCP_ALLOWED_ORIGINS`; sin `Access-Control-Allow-Credentials` |
| Errores | `-32603` genérico, detalle solo en `error_log`; `display_errors` a 0 |

**Auditoría.** Cada mensaje se registra en `tokens_mcp_log` (`idToken`, `Metodo`, `Herramienta`, `Argumentos`, `Resultado`, `IP`, `Fecha`). Los rechazos anónimos solo se registran tras pasar el límite por IP y los `429` solo en la transición a bloqueado. `Metodo` y `Herramienta` se reducen a 64 ASCII y `Argumentos` se guarda en ASCII truncado a 2000 caracteres. El token no se escribe en el log, en `error_log` ni en `AuditLogger`: las secuencias hexadecimales largas (el token tiene 64; el código actual de `McpAuditLog::redact` redacta desde 32) se sustituyen por `[redactado]` en **todos los campos de texto** (`Metodo`, `Herramienta`, `Argumentos`, `Resultado`, `IP`), antes de truncar (`projects/mcp-servidor/specs/security.md` §2 punto 8). No hay purga automática (retención pendiente).

**Archivo de transiciones `429`.** `cache/ratelimit-transiciones.json` debe pertenecer al usuario con el que corre Apache (o crearse vacío con esos permisos). Si lo crea otro usuario (p. ej. un `php -S` como root), `McpRateLimit::isTransition` devuelve `false` y los `429` dejan de auditarse sin más rastro que `error_log`.

**Nota para el operador: `LogFormat` de Apache.** El MCP nunca recibe el token en la URL, pero si el `LogFormat` del servidor registra cabeceras (`%{Authorization}i`), el token quedaría en el log de acceso. No registrar esa cabecera. Detrás de un proxy inverso, `REMOTE_ADDR` es la IP del proxy y todas las peticiones comparten contador (no se confía en `X-Forwarded-For`).

**Prompt injection.** El servidor es de solo lectura (las tools no escriben; la cuenta solo escribe en `tokens_*`, por OAuth), pero los datos devueltos son entrada no confiable para la IA. No combinarlo con herramientas de escritura sin confirmación humana.

## Operación

- Script de humo: `php admin/MCP/bin/mcp-selftest.php` (variables `MCP_URL`, los tokens de prueba y, para los casos OAuth 28-41, `MCP_PUBLIC_URL` y `MCP_OAUTH_*`, descritos en su cabecera; en dev el servidor arranca con `MCP_RATE_TOKEN_MAX=50` y `MCP_MAX_RESPONSE_BYTES=200000`). El PHP CLI de desarrollo no tiene `pdo_mysql`: lo que necesita BD se prueba por HTTP o con el PHP del contenedor web.
- Puente stdio para clientes sin transporte HTTP con cabeceras: `admin/MCP/bin/mcp-stdio.php` (`MCP_URL`, `MCP_TOKEN`).
- Tras instalar o actualizar un módulo ("Actualizar Modulo", ADR-0013): regenerar los `GRANT` de tablas y, si hace falta, borrar `cache/catalogo.json`.
- Ampliar el MCP o añadir PII/columnas ocultas: `McpConfig` + `SchemaFilter`, nunca desde el manifiesto.
