# Conectar un cliente de IA al servidor MCP de coreEngine

Guía por cliente (RF-019). Servidor, seguridad y operación: [`../README.md`](../README.md). Contrato del endpoint: `projects/mcp-servidor/specs/api.md` §1.

Los formatos de configuración de cada cliente son los vigentes al escribir esta guía y cambian con frecuencia: ante una diferencia, manda la documentación del cliente. Solo el puente stdio y el endpoint son de este proyecto.

## 1. General

| Dato | Valor |
|---|---|
| URL del endpoint | `<host>/admin/MCP/public/` (dev: `http://localhost/coreEngine/admin/MCP/public/`) |
| Transporte | Streamable HTTP, solo `POST` con cuerpo JSON-RPC |
| Autenticación | cabecera `Authorization: Bearer <token>` (64 hexadecimales): un token manual o un access token OAuth ([§7](#7-conexión-por-oauth)) |

**El token nunca va en la URL**: una query string con 64 hexadecimales, o con `token`/`access_token`, se rechaza con 400.

**Alcance efectivo del token.** Cada token tiene sus propios **módulos asignados** (pestaña "Permisos" de la ficha del token en el backoffice, un interruptor por módulo, sin nivel) y no depende de ningún usuario. Con un módulo asignado el token ve todas las tablas expuestas de ese módulo, y solo esas; un módulo o tabla fuera de alcance responde `-32602` como si no existiera. Un token sin módulos recibe 403 y uno inexistente, inactivo o caducado, 401 idéntico. Dé a cada cliente un token propio con el alcance mínimo necesario y desactívelo desde el backoffice si se pierde.

### Puente stdio (`bin/mcp-stdio.php`)

Para clientes que solo lanzan procesos locales (Claude Desktop). Reenvía por HTTP cada línea de STDIN al endpoint. Configuración por entorno:

| Variable | Regla |
|---|---|
| `MCP_URL` | URL del endpoint, sin query string ni fragmento; solo `https`, salvo `http` para `localhost`, `127.0.0.1` o `::1` |
| `MCP_TOKEN` | token de 64 hexadecimales |

El puente no imprime el token ni cabeceras HTTP, no sigue redirecciones y verifica el certificado TLS; requiere PHP CLI con `allow_url_fopen` y `openssl`. Si falta o es inválida alguna variable, escribe el motivo (sin el token) en STDERR y sale con código 1.

### Permisos de los archivos con el token

Varios clientes guardan el token en un archivo de configuración en claro. Restrinja su lectura al propietario:

```bash
chmod 600 <archivo-de-configuracion>
```

No suba esos archivos a un repositorio ni los comparta. `.cursor/mcp.json` y `.vscode/mcp.json` dentro de un proyecto versionado deben ir en `.gitignore` o usar variables/`inputs` en lugar del token literal.

### Probar con MCP Inspector

```bash
npx @modelcontextprotocol/inspector
```

En la interfaz elegir transporte "Streamable HTTP", la URL del endpoint y la cabecera `Authorization: Bearer <token>`; `tools/list` debe mostrar `list_modules`, `describe_module`, `list_records`, `search_records` y `get_record`.

### Aviso: prompt injection

El servidor es de solo lectura y su cuenta de BD no puede escribir, pero los **datos que devuelve son entrada no confiable**: un campo de texto puede contener instrucciones dirigidas a la IA. No lo combine con herramientas de escritura o de ejecución sin confirmación humana de cada acción.

## 2. Claude Code y Claude Desktop

**Claude Code** (transporte HTTP directo):

```bash
claude mcp add --transport http coreengine <url> --header "Authorization: Bearer <token>"
```

Sustituya `<url>` por el endpoint y `<token>` por el token. El token queda en la configuración de Claude Code: proteja ese archivo con `600`.

**Claude Desktop** (puente stdio). Edite `claude_desktop_config.json` (menú Settings > Developer > Edit Config; en macOS `~/Library/Application Support/Claude/`, en Windows `%APPDATA%\Claude\`):

```json
{
  "mcpServers": {
    "coreengine": {
      "command": "php",
      "args": ["/ruta/absoluta/a/coreEngine/admin/MCP/bin/mcp-stdio.php"],
      "env": {
        "MCP_URL": "https://<host>/admin/MCP/public/",
        "MCP_TOKEN": "<token>"
      }
    }
  }
}
```

Reinicie Claude Desktop. Aplique `chmod 600` al archivo (en macOS/Linux).

Estado de verificación: la prueba real con ambos clientes está pendiente (TASK-012.3); el puente stdio sí está verificado contra el endpoint (TASK-004.8).

## 3. ChatGPT

**No se conecta directamente** (decisión D-13). Los conectores remotos de ChatGPT exigen OAuth o "sin autenticación"; este servidor autentica con token Bearer en cabecera, por lo que no hay una configuración soportada con un token manual. **Actualización (proyecto `mcp-oauth`)**: el servidor ya implementa un servidor de autorización OAuth 2.1 (§7); ChatGPT no se ha probado con él y no se afirma que funcione. Exponer el endpoint sin autenticación no es una alternativa: quedaría abierto a cualquiera.

Alternativas, sin garantías: usar uno de los demás clientes de esta guía, o ejecutar una pasarela propia que añada OAuth delante del servidor (no incluida en este proyecto).

## 4. Gemini CLI

En `~/.gemini/settings.json`:

```json
{
  "mcpServers": {
    "coreengine": {
      "httpUrl": "https://<host>/admin/MCP/public/",
      "headers": {
        "Authorization": "Bearer <token>"
      }
    }
  }
}
```

`chmod 600 ~/.gemini/settings.json`.

## 5. Cursor

En `.cursor/mcp.json` del proyecto (o `~/.cursor/mcp.json` global):

```json
{
  "mcpServers": {
    "coreengine": {
      "url": "https://<host>/admin/MCP/public/",
      "headers": {
        "Authorization": "Bearer <token>"
      }
    }
  }
}
```

`chmod 600` sobre el archivo y, si es de un proyecto versionado, añádalo a `.gitignore`.

## 6. VS Code Copilot

En `.vscode/mcp.json`. El token se pide al iniciar el servidor mediante `inputs`, con `password: true`, y no queda escrito en el archivo:

```json
{
  "inputs": [
    {
      "type": "promptString",
      "id": "coreengine-token",
      "description": "Token del MCP de coreEngine",
      "password": true
    }
  ],
  "servers": {
    "coreengine": {
      "type": "http",
      "url": "https://<host>/admin/MCP/public/",
      "headers": {
        "Authorization": "Bearer ${input:coreengine-token}"
      }
    }
  }
}
```

## 7. Conexión por OAuth

Alternativa a pegar un token en la configuración del cliente: el cliente descubre el servidor de autorización, se registra solo y la persona aprueba la conexión pegando **un token manual existente** en una pantalla del servidor. El token pegado **no se copia en la configuración del cliente**: el cliente recibe un access token propio, de vida corta y renovable. Los tokens manuales de las secciones 1 a 6 siguen funcionando igual. Contrato y decisiones: `projects/mcp-oauth/specs/` (`functional.md` §2-§3, `technical.md`). Requisitos de despliegue: §7.5 y §8.

Estado de verificación: el flujo está implementado y se probó con dobles de BD, servidores PHP temporales y análisis estático; **no se ha verificado todavía contra el entorno de desarrollo con BD ni con clientes reales** (pendiente: TASK-006.15, TASK-007.10 y TASK-008.20 del proyecto).

### 7.1 Flujo

| Paso | Qué ocurre | Endpoint |
|---|---|---|
| 1. Descubrimiento | Una petición al MCP sin credenciales válidas recibe 401 con `WWW-Authenticate: Bearer realm="coreEngine-MCP", error="invalid_token", resource_metadata="<origen>/.well-known/oauth-protected-resource<ruta MCP>/public"`. Esa URL devuelve los metadatos del recurso (RFC 9728): `resource` (URI del MCP con barra final), `authorization_servers` (el issuer), `scopes_supported` `["mcp"]` y `bearer_methods_supported` `["header"]`. Los metadatos del servidor de autorización (RFC 8414) están en `<origen>/.well-known/oauth-authorization-server<ruta MCP>/oauth` | GET, públicos |
| 2. Registro dinámico | El cliente envía sus metadatos (RFC 7591) y recibe `client_id` (y `client_secret` solo si pidió `client_secret_post` o `client_secret_basic`; por defecto el cliente es público, `none`). El cliente queda **activo** de inmediato | `POST <issuer>/register` |
| 3. Autorización | El cliente abre en el navegador `GET <issuer>/authorize` con `response_type=code`, `client_id`, `redirect_uri`, `code_challenge` (PKCE **S256**, obligatorio), `state` y, opcional, `resource` (debe ser el MCP). El servidor muestra **su propia pantalla**: nombre declarado por la aplicación (no verificado), prefijo del `client_id`, `redirect_uri`, fecha e IP de registro, un campo para **pegar un token manual** y el botón Denegar | `GET/POST <issuer>/authorize` |
| 4. Aprobación | La persona pega un token manual (de `Administración > Tokens`, activo y no caducado). No hay inicio de sesión de personas: lo que autoriza es poseer el token. Con un token no válido, por cualquier causa, la pantalla responde "Credenciales no válidas" sin distinguir el motivo. Si es válido, el servidor redirige a la `redirect_uri` registrada con `code`, `state` e `iss` | POST de `/authorize` |
| 5. Canje | El cliente cambia el `code` (un solo uso, vigencia de 60 s) más `redirect_uri` y `code_verifier` por `access_token`, `token_type` `Bearer`, `expires_in` `3600`, `scope` `mcp` y, si el cliente tiene el grant, `refresh_token` | `POST <issuer>/token`, `grant_type=authorization_code` |
| 6. Uso | El cliente llama al MCP con `Authorization: Bearer <access_token>` | `<host>/admin/MCP/public/` |
| 7. Renovación | Con `grant_type=refresh_token` el cliente recibe un access token y un refresh token nuevos (**rotación**: el refresh anterior deja de valer). Reusar un refresh ya rotado, pasada una gracia de 10 s por concurrencia, revoca toda la concesión | `POST <issuer>/token` |
| 8. Revocación | El cliente revoca un access o un refresh token propios; responde 200 aunque el token no exista | `POST <issuer>/revoke` |

`<issuer>` = `MCP_PUBLIC_URL` + `/oauth`. `/token` y `/revoke` autentican al cliente según su método de registro (`none` con solo `client_id`, `client_secret_post` o `client_secret_basic`); `client_secret`, `code`, `code_verifier`, `refresh_token` y `token` en la query string se rechazan con 400. Metadatos del servidor: `code_challenge_methods_supported` solo `S256`, `grant_types_supported` `authorization_code` y `refresh_token`, `scopes_supported` solo `mcp` (un scope desconocido en la petición se ignora y se emite `mcp`).

### 7.2 Permisos del acceso OAuth

El access token es una fila hija del token pegado: **sus módulos son exactamente los del token pegado** (los de la pestaña "Permisos" de ese token: el acceso hijo hereda los módulos del padre, de modo que cambiarlos afecta de inmediato a los accesos ya emitidos). Pierde el acceso, en la siguiente petición, cuando el token pegado se desactiva, caduca o se borra, cuando se desactiva o borra el cliente, o cuando se revoca desde la ficha del token (pestañas "Clientes OAuth" y "Accesos OAuth" del módulo `tokens`, [`../../app/modules/tokens/README.md`](../../app/modules/tokens/README.md)). La cuota de peticiones es la del token pegado, compartida entre todos sus accesos. Para un cliente concreto, pegue un token con el alcance mínimo necesario.

Lo que el servidor guarda del secreto: el código y el refresh token solo como hash SHA-256; el access token en claro (igual que los tokens manuales).

### 7.3 `redirect_uri` admitidas

| Forma | Condición |
|---|---|
| `https://<host>/…` | host en minúsculas con `a-z`, `0-9`, `.` y `-` |
| `http://…` | **solo** hosts `localhost`, `127.0.0.1` o `[::1]` (exactos, en minúsculas; sin variantes como `localhost.`, `x.localhost` ni `LOCALHOST`) |
| esquema privado | solo `cursor://`, `vscode://` y `vscode-insiders://` |

Se rechazan `#`, `\`, `%`, `@`, credenciales en la URI, puertos fuera de 1-65535, más de 2000 caracteres y más de 5 URI por cliente. Para `http` en loopback el cliente puede pedir en `/authorize` un **puerto distinto** del registrado (mismos host, ruta y query), y `localhost` no equivale a `127.0.0.1` ni a `[::1]` al compararlos con la registrada. Admitir `localhost` es una **desviación consciente** de RFC 8252 §8.3 (que prefiere la IP literal), por interoperabilidad: los clientes que registran `http://localhost:<puerto>/…`, como Claude Code y MCP Inspector, quedan admitidos. El servidor redirige siempre a la URI construida desde la registrada, nunca a la cadena recibida.

### 7.4 Duraciones y límites

| Dato | Valor (`McpConfig::OAUTH_*`) |
|---|---|
| Código de autorización | 60 s, un solo uso (`OAUTH_CODE_TTL`) |
| Access token | 3600 s (`OAUTH_ACCESS_TTL`) |
| Refresh token | 30 días por emisión, con tope de 90 días desde la primera emisión de la concesión (`OAUTH_REFRESH_TTL`, `OAUTH_REFRESH_MAX_TTL`); gracia de 10 s tras rotar (`OAUTH_REFRESH_GRACE`) |
| Petición de autorización pendiente | 600 s, hasta 5 por sesión del servidor (`OAUTH_PENDING_SECONDS`, `OAUTH_PENDING_MAX`); 5 intentos fallidos de pegar el token y la petición se descarta (`OAUTH_CRED_RID_MAX`) |
| Consultas de token en la pantalla | 10 por IP en 60 s y 5 por token consultado en 480 s (`OAUTH_CRED_IP_MAX`, `OAUTH_CRED_ID_MAX`; ventanas de `ConfigAPP`) |
| Registro dinámico | 3 por minuto y 20 por IP en 24 h (IPv6 a /64), y como máximo 100 clientes sin uso en total (`OAUTH_REG_MAX`, `OAUTH_REG_IP_DIA`, `OAUTH_UNUSED_MAX`); superado, 429 con `Retry-After` |
| Resto de endpoints | 60 peticiones por IP y minuto (`OAUTH_IP_MAX`) |
| Limpieza | un cliente registrado hace más de 24 h sin accesos ni refresh vigentes se purga; también caducados (código, refresh y access, con 1 día de margen). Se ejecuta en `/register` y en 1 de cada 50 `/token` |
| Cuerpos | 64 KiB máximo; `state` hasta 1024 caracteres |

### 7.5 Variables y requisitos de despliegue

| Requisito | Detalle |
|---|---|
| `MCP_PUBLIC_URL` | URL pública del MCP **sin** `/public` ni `/oauth` (p. ej. `https://<host>/admin/MCP`; dev `http://localhost/coreEngine/admin/MCP`). `https`, o `http` solo en `localhost`, `127.0.0.1` o `[::1]`; sin query, fragmento ni credenciales. **Sin ella, o inválida, el servidor de autorización responde 503 y el 401 del MCP no lleva `resource_metadata`** (el cliente no puede descubrir nada). Todas las URL de los metadatos salen de esta variable, nunca de la cabecera `Host` |
| `MCP_ALLOWED_ORIGINS` | Necesaria para **clientes de navegador**, como MCP Inspector (origen `http://localhost:6274`). Lista de orígenes exactos separados por comas (`*` y `null` se descartan). Controla el CORS de `/register`, `/token`, `/revoke` y del propio MCP; los metadatos `.well-known` son públicos (`Access-Control-Allow-Origin: *`) y `/authorize` no usa CORS. Los clientes que no son de navegador (no envían `Origin`) no la necesitan |
| Fragmento `.well-known` | **Obligatorio** en la raíz del host (§7.6) |
| PHP 8.1 o superior | el código usa `array_is_list` y el tipo de retorno `never`; con PHP 8.0 el registro falla con un error fatal |
| Zona horaria de la BD | la sesión MySQL de la cuenta del MCP debe usar la zona horaria de la aplicación: las fechas del flujo (`NOW()`, `CURDATE()`) se calculan en SQL |
| "Actualizar Modulo" de `tokens` | debe aplicarse **antes** de usar el MCP: `TokenAuthenticator` consulta las columnas nuevas de `tokens_listado` (`Origen`, `idTokenPadre`, `idClienteOAuth`, `FechaExpiracion`) y, sin ellas, falla la consulta de autenticación de todos los tokens, también los manuales |
| Cuenta de BD | el servidor de autorización usa la conexión del MCP (`MySQL_MCP`); GRANT en [`../README.md`](../README.md) (estado: cuenta pendiente de crear por el operador) |

### 7.6 Configuración de `/.well-known` (fragmento de la raíz del host)

El fragmento **siempre es necesario** (decisión D-1): RFC 8414 §3.1 y RFC 9728 §3.1 colocan los metadatos bajo `/.well-known/` en la **raíz del host**, con la ruta del recurso o del issuer insertada tras el nombre, y la especificación MCP 2025-06-18 obliga a los clientes a descubrir así. Las rutas dentro de `admin/MCP/oauth/` solo sirven de respaldo: sin el fragmento, los clientes OAuth no encuentran los metadatos.

1. Abra [`well-known.htaccess.example`](well-known.htaccess.example) y copie las cuatro reglas `RewriteRule` (con `RewriteEngine On`) en el `.htaccess` de la raíz del host (`DocumentRoot`) o en el `<VirtualHost>`. Requiere `mod_rewrite`.
2. Sustituya `<RUTA_MCP>` en las cuatro reglas por la ruta de `MCP_PUBLIC_URL` sin barra final: `/admin/MCP` en producción, `/coreEngine/admin/MCP` en desarrollo.
3. Si lo coloca en el `<VirtualHost>` (no en un `.htaccess`), anteponga `/` a cada patrón (`^/\.well-known/…`): en ese contexto la ruta conserva la barra inicial.
4. Compruebe (dev, `<origen>` = `http://localhost`); ambas deben responder 200 con su JSON:

```bash
curl -i <origen>/.well-known/oauth-protected-resource/coreEngine/admin/MCP/public
curl -i <origen>/.well-known/oauth-authorization-server/coreEngine/admin/MCP/oauth
```

Las dos primeras reglas (con ruta) son las que usa el 401 del MCP; las dos últimas (sin ruta) cubren a clientes de la especificación MCP 2025-03-26. Un 404 en estas URL indica que el fragmento falta o `<RUTA_MCP>` es incorrecta. La instalación en dev es una tarea de operador (TASK-006.14 de `mcp-oauth`, pendiente).

### 7.7 Por cliente

Solo se indica lo que se deduce del servidor; los pasos de la interfaz de cada cliente son los de su documentación. En todos los casos la URL del MCP es el endpoint de la sección 1 y **no se escribe ningún token** en la configuración.

| Cliente | Qué consta |
|---|---|
| Claude Code | registra `http://localhost:<puerto>/…`: admitida (§7.3). Dar de alta el servidor HTTP **sin** la cabecera `Authorization` de la sección 2 hace que el cliente reciba el 401 con `resource_metadata` y arranque el flujo; no verificado con el cliente real |
| MCP Inspector (`npx @modelcontextprotocol/inspector`) | transporte "Streamable HTTP" y la URL del endpoint, sin cabecera. Es un cliente de navegador: arranque el servidor con `MCP_ALLOWED_ORIGINS=http://localhost:6274` (su origen); sin ella el CORS de `/register`, `/token` y del MCP lo rechaza. Registra `http://localhost:<puerto>/…` (admitida) |
| Cursor | su esquema privado `cursor://` está en la lista blanca (§7.3) |
| VS Code (Copilot) | sus esquemas `vscode://` y `vscode-insiders://` están en la lista blanca; si registra `http://localhost:<puerto>/…`, también se admite |
| Claude (aplicaciones) y ChatGPT | **no se conoce ni se ha probado la `redirect_uri` que registran**. Si es `https` se admite; cualquier forma fuera de §7.3 (p. ej. un esquema privado propio) se rechaza en `/register` con `invalid_redirect_uri` |

Si el cliente no soporta OAuth contra este servidor, siga usando un token manual (secciones 2 a 6).

## 8. Requisitos y avisos

Ver §7.5. Resumen para el operador, en orden: aplicar "Actualizar Modulo" de `tokens`; fijar `MCP_PUBLIC_URL` (y `MCP_ALLOWED_ORIGINS` si hay clientes de navegador); instalar el fragmento `.well-known` (§7.6); comprobar las dos URL con `curl`; crear la cuenta `coreengine_mcp` con los GRANT de [`../README.md`](../README.md).
