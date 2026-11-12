# Módulo `tokens`

## Propósito

Administración de tokens de acceso que otros proyectos consumirán, de los accesos OAuth emitidos a partir de ellos y de los clientes OAuth del servidor MCP. `tokensListado` es un **CRUD Resumen** (`idTipo` 2, ficha con 5 pestañas): el servidor genera el token, registra la fecha de creación y el creador (usuario de la sesión) y el usuario solo elige el estado y, opcionalmente, una fecha de caducidad. `tokensOauthClientes` es un CRUD Normal de gestión (sin alta: los clientes se crean por registro dinámico). Proyectos SDD: `projects/administracion-tokens/` (módulo base) y `projects/mcp-oauth/` (OAuth, CRUD Resumen y clientes).

**Antes de usar el MCP, aplicar "Actualizar Modulo"** (ADR-0013): `TokenAuthenticator` (`admin/MCP/src/Auth/`) consulta las columnas nuevas de `tokens_listado` y el servidor de autorización usa las tablas `tokens_oauth_*`, que crea este installer. Estado de verificación: el installer y los controladores se verificaron con `php -l`, análisis estático y dobles; **la instalación y "Actualizar Modulo" en dev con BD los ejecuta el usuario** (hoja TASK-003.21 de `mcp-oauth`, cerrada por su instrucción; los resultados de `SHOW COLUMNS`/`SHOW INDEX` no constan en el repositorio).

## Estructura

```text
admin/app/modules/tokens/
├── .htaccess                          # deny from all (y en cada subcarpeta)
├── README.md
├── controller/tokensListado.php       # CRUD Resumen: listAll, UpdateList, View, Resumen, ResumenUpdate, Insert, Update, Delete (+ privados getData*, dataCheck)
├── controller/tokensListadoPermisos.php        # subcontrolador pestaña 3: UpdateList y Update de la matriz de módulos del token (getModulosInstalados, getModulos)
├── controller/tokensListadoOauthClientes.php   # subcontrolador pestaña 4: UpdateList y Revocar (accesos de un cliente con el token)
├── controller/tokensListadoOauthAccesos.php    # subcontrolador pestaña 5: UpdateList y Delete (revocar un acceso)
├── controller/tokensOauthClientes.php          # CRUD Normal sin Insert: listAll, UpdateList, View, GetID, Update, Delete
├── functions/InternalFunctionsTokensOAuth.php  # validadores estáticos de redirect_uri (los usa admin/MCP/src/OAuth); functions/.htaccess
├── functions/InternalFunctionsTokensModulos.php  # listExposable(): módulos que el MCP puede exponer (matriz de permisos y migración; reutiliza ModuleScanner, sin BD)
├── views/tokensListado-{List,UpdateList,View,formNew,formSearch}.php
├── views/tokensListado-Resumen{,-Update}.php   # ficha con pestañas y su pestaña de solo lectura
├── views/tokensListado-Resumen-{Permisos-List,OauthClientes-UpdateList,OauthAccesos-UpdateList}.php  # fragmentos de las pestañas 3 a 5
├── views/tokensOauthClientes-{List,UpdateList,View,formEdit,formSearch}.php
├── installer/tokensInstaller.php      # tablas, índices, permisos, rutas y migración
└── testing/                           # 7 suites sistemaTesteos (una por controlador + InternalFunctionsTokensOAuthTesting + InternalFunctionsTokensModulosTesting)
```

Vistas: `tokensListado-View.php` es **solo lectura** (ADR 0018, regla de `docs/architecture/ui/view-suffixes.md`): sin formularios, inputs ni botones de edición; la edición vive en la ficha `-Resumen`. `tokensListado-formEdit.php` y `getID` ya no existen.

## Tablas

`tokens_listado` (creada por `tokensInstaller::listTables()`, índices en `optimizeTables()`):

| Columna | Tipo | Notas |
|---|---|---|
| `idToken` | int(10) unsigned AI | PK |
| `Nombre` | varchar(255) latin1 NOT NULL | obligatorio, 3 a 255 caracteres (`dataCheck`); instalaciones previas: `ADD COLUMN IF NOT EXISTS` (`DEFAULT ''`) |
| `Token` | varchar(64) latin1 | 64 hex (`tokenBin2Hex(64)`), único (`uk_tokens_Token`), texto plano |
| `FechaCreacion` | datetime | la fija el servidor |
| `idUsuario` | int(10) unsigned | creador = usuario de la sesión (`idx_tokens_usuario`) |
| `idEstado` | int(10) unsigned | `core_estados` (1 Activo, 2 Inactivo) (`idx_tokens_estado`) |
| `FechaCaducidad` | date NULL | informativa en el backoffice; el MCP la aplica (`idx_tokens_caducidad`) |
| `Origen` | int(10) unsigned NOT NULL DEFAULT 1 | 1 = token manual; 2 = acceso OAuth, hijo de un manual (`idx_tokens_origen_fecha`) |
| `idTokenPadre` | int(10) unsigned NULL | solo `Origen` 2: el token manual pegado en `/authorize` (`idx_tokens_padre`) |
| `idClienteOAuth` | int(10) unsigned NULL | solo `Origen` 2: cliente al que se emitió (`idx_tokens_cliente`) |
| `FechaExpiracion` | datetime NULL | solo `Origen` 2: expiración del access token (`idx_tokens_expiracion`) |

Las filas `Origen` 2 las crea y borra el servidor de autorización (`admin/MCP/src/OAuth/`): `Nombre` = `OAuth: <nombre del cliente>`, `idUsuario` = 0 (valor neutro fijo de la columna obligatoria; no es un creador), `idEstado` 1, sin `FechaCaducidad` y **sin filas en `tokens_listado_permisos`**: su alcance es el de su padre. El listado y la ficha de `tokensListado` solo muestran `Origen` 1; el CRUD nunca edita ni borra un hijo por esta vía.

`tokens_listado_permisos` (alcance del token por módulo, ADR-0019): `idTokenPermiso` PK, `idToken`, `Modulo` (varchar 64, carpeta de `admin/app/modules/<Modulo>/`) y `fechaCreacion` (date). Único `uk_tkperm_token_modulo (idToken, Modulo)`. Sin `idPermisos` ni `idLevelLimit`: el alcance es un interruptor por módulo, sin nivel.

`tokens_mcp_log` (auditoría de llamadas al servidor MCP): `idLog` bigint PK, `idToken` (0 si no se identificó; con un acceso OAuth, el del token padre), `Metodo`, `Herramienta`, `Argumentos`, `Resultado`, `IP`, `Fecha` e `idClienteOAuth` (NULL si la llamada no fue con un acceso OAuth). Índices `idx_tkmcp_token_fecha (idToken, Fecha)` e `idx_tkmcp_fecha (Fecha)`. Nunca guarda el valor del token, solo su `idToken`. La tabla la crea el installer; el escritor es `admin/MCP/src/Audit/McpAuditLog.php` (`projects/mcp-servidor/`), que redacta toda secuencia de 32 o más hex en los campos de texto.

Tablas OAuth nuevas (DDL, índices y relaciones exactos en `projects/mcp-oauth/specs/database.md` §2-§4 y §8; sin claves foráneas, las cascadas las hacen los controladores, D-22):

| Tabla | Contenido | Índices |
|---|---|---|
| `tokens_oauth_clientes` | clientes registrados por `/register`: `ClientId` (32 hex), `SecretHash` (SHA-256 o NULL en públicos), `AuthMethod`, `Nombre` (declarado, no verificado), `RedirectUris` (JSON), `Metadatos`, `idEstado`, `FechaRegistro`, `IPRegistro`, `IPClave`, `FechaUltimoUso` (NULL = nunca obtuvo acceso) | `uk_tkoc_clientid`, `idx_tkoc_estado`, `idx_tkoc_ip_registro`, `idx_tkoc_uso` |
| `tokens_oauth_codigos` | códigos de autorización de un solo uso (hash, `idCliente`, `idToken` pegado, `RedirectUri` efectiva, `CodeChallenge`, `Resource`, `Scope`, vigencia 60 s, `Usado`, `idTokenAcceso`) | `uk_tkocod_hash`, `idx_tkocod_cliente`, `idx_tkocod_token`, `idx_tkocod_expiracion` |
| `tokens_oauth_refresh` | refresh tokens (hash, `Familia`, `idCliente`, `idTokenAcceso`, `Estado` 1 vigente / 2 usado / 3 revocado, `FechaExpiracion`, `FechaExpiracionFamilia`, `FechaUso`) | `uk_tkoref_hash`, `idx_tkoref_familia`, `idx_tkoref_acceso`, `idx_tkoref_cliente`, `idx_tkoref_expiracion` |

El módulo gestiona así **6 tablas**: `tokens_listado`, `tokens_listado_permisos`, `tokens_mcp_log` y las 3 OAuth. Instalaciones existentes: `optimizeTables()` añade columnas e índices con `IF NOT EXISTS` y, al final, migra el permiso `Tokens - Listado` a `idTipo` 2 y borra la ruta `tokensListado->GetID`. La cuenta de BD del MCP y del servidor de autorización necesita GRANT sobre estas tablas (`database.md` §7; **pendientes de operador**, ver `admin/MCP/README.md`).

## Permisos y rutas

El módulo define **2 permisos**:

| Permiso | `idTipo` | `idLevelLimit` | `RutaWeb` | Controlador |
|---|---|---|---|---|
| `Tokens - Listado` | 2 (CRUD Resumen) | 4 | `administracion/tokens/listado` | `tokensListado` |
| `Tokens - Clientes OAuth` | 1 (CRUD Normal) | 4 | `administracion/tokens/clientesOauth` | `tokensOauthClientes` |

Niveles de ambos: Ver (1), Editar (2), Crear (3, solo en `Tokens - Listado`), Borrar (4).

**`Tokens - Listado`** (15 rutas): `listAll`, `search`, `updateList`, `view/@id` (nivel 1); `resumen/@id` y `resumenUpdate/@id` (nivel 2); `Insert` (base, POST, nivel 3), `update` (POST, nivel 2) y `Delete` (base, DELETE, nivel 4); más 6 rutas de subcontroladores, todas de nivel 2 y bajo el mismo permiso (el `RutaController()` del installer lista los 5 controladores del módulo; ver `docs/architecture/patterns/shared-permissions-subcontroller.md`): `permisos/updateList/@id` y `permisos/update` (`tokensListadoPermisos`), `oauthClientes/updateList/@id` y `oauthClientes/revocar` (`tokensListadoOauthClientes`), `oauthAccesos/updateList/@id` y `oauthAccesos` DELETE (`tokensListadoOauthAccesos`). Sin ruta `getID`. Se recomienda otorgarlo solo a administradores (cualquier usuario con nivel Ver ve todos los tokens en claro).

**`Tokens - Clientes OAuth`** (7 rutas, CRUD Normal **sin `Insert`**): `listAll`, `search`, `updateList`, `view/@id` (nivel 1); `getID/@id` y `update` (nivel 2, activar o desactivar); `Delete` (nivel 4). Los clientes solo se crean por registro dinámico (`POST /register` del servidor de autorización).

Ficha de `tokensListado` (`resumen/@id`, nivel 2): **5 pestañas** — Resumen (solo lectura, con `NModulos` —número de módulos asignados—, `NClientes` y `NAccesos` vigentes), Editar Datos (`Nombre`, `idEstado`, `FechaCaducidad`) y, con nivel >= 2, Permisos (módulos), Clientes OAuth y Accesos OAuth. La pestaña Clientes OAuth lista los clientes que usan el token (vía sus hijos) y permite "Revocar accesos"; la de Accesos OAuth lista cada acceso (cliente, emitido, expiración, si es renovable; **nunca el valor del token**) y permite "Revocar".

## Comportamiento

- `Nombre`: obligatorio en Insert y Update; `dataCheck` con `ValidarLargoMinimo`=`Nombre` (N=3), `ValidarLargoMaximoPorCampo` `['Nombre' => 255]` y `ValidarPalabrasCensuradas`=`Nombre` (patrón de `serviciosCategorias`). Filtro de búsqueda parcial por Nombre.
- `Insert`: fija en el servidor `idUsuario` (sesión), `FechaCreacion` y `Token`; no acepta esos valores del formulario, descarta `Origen`, `idTokenPadre`, `idClienteOAuth` y `FechaExpiracion` del POST y devuelve el id cifrado (el formulario redirige a la ficha `resumen/`), no el token.
- `Update`: solo `Nombre`, `idEstado` y `FechaCaducidad`; solo sobre tokens manuales (`Origen` 1, si no 400). **Si `idEstado` deja de ser 1** se revoca todo lo emitido con ese token: en la misma transacción borra los refresh tokens y los accesos hijos del token y sus códigos de autorización pendientes (D-22). Los campos vacíos se omiten: una vez fijada, la caducidad se puede cambiar pero no borrar desde el formulario (decisión D-2).
- `Delete`: solo tokens manuales (`Origen` 1); en una transacción borra el token, sus permisos y, en cascada, sus refresh, accesos hijos y códigos (RN-006). Un cliente OAuth no se borra con este borrado.
- Caducidad: en el backoffice es solo informativa (el listado y el detalle marcan "Caducado" si `FechaCaducidad < CURDATE()`; sin job ni cambio de estado); el MCP sí la aplica.
- **`tokensListadoOauthClientes::Revocar`** (POST, transaccional): borra, para un cliente y un token, los refresh y los accesos hijos; el cliente sigue activo y puede volver a autorizar. **`tokensListadoOauthAccesos::Delete`** (DELETE, transaccional): borra un acceso (`Origen` 2) y sus refresh. Ambos reciben los ids cifrados.
- **`tokensOauthClientes`**: `listAll` muestra un aviso si hay clientes que nunca obtuvieron acceso (los dinámicos sin concesión viva se purgan solos a las 24 h). `View` es de solo lectura, no incluye `SecretHash` y pinta el nombre como "declarado, no verificado" y las URI y metadatos como texto. `Update` solo acepta `idCliente` e `idEstado`; **desactivar un cliente borra sus refresh, códigos y accesos hijos** (D-22). `Delete` borra el cliente y esa misma cascada. No existe `Insert`.
- Funciones internas (`functions/InternalFunctionsTokensOAuth.php`, métodos estáticos): `redirectUriAllowed`, `redirectUriMatches` y `redirectUriResolve` (D-9: `https`; `http` solo en `localhost`, `127.0.0.1` y `[::1]`; esquemas privados `cursor`, `vscode` y `vscode-insiders`). Las usa el servidor de autorización de `admin/MCP/src/OAuth/`; el módulo no las llama desde el backoffice.

## Permisos del token (MCP)

- `tokensListadoPermisos` (subcontrolador, constructor con `controllerName = 'tokensListado'`):
  - `UpdateList` (GET): pinta la matriz de módulos (vista `tokensListado-Resumen-Permisos-List.php`): una fila por módulo con un interruptor (`activo`), sin categorías ni niveles. Se carga en la pestaña Permisos de la ficha, solo con nivel >= 2; solo para tokens manuales (un hijo no tiene matriz).
  - `getModulos(idToken)` (privado): módulos de `InternalFunctionsTokensModulos::listExposable()` que además están instalados (`getModulosInstalados()` lee `Controller` de `core_permisos_listado_rutas` y se cruza con los controladores del módulo), con `activo` 1/0 según las filas del token. Los módulos ocultos del MCP (`installer`, `tokens`, `McpConfig::EXCLUDED_MODULES`), sin manifiesto válido o sin tablas consultables no aparecen. Es la misma regla que aplica el catálogo del MCP, de modo que la matriz ofrece solo lo que el MCP puede exponer.
  - `Update` (POST, transaccional): por cada módulo de `getModulos()`, `switch_<Modulo>` 2 = asignado; 1 (o ausente) = no asignado. Borra los desmarcados (`idToken, Modulo`) e inserta los nuevos en bloque (`Base_insertMultiple`; duplicado → 400 "Módulo duplicado"). Un `switch_*` de un módulo que no está en `getModulos()` se ignora y no toca filas existentes. El `idToken` llega cifrado.
- Con nivel Editar sobre `Tokens - Listado` se asigna cualquier módulo exponible (sin filtrar por los permisos del usuario de la sesión): por eso el permiso se otorga solo a administradores. Riesgo aceptado (ADR-0012, ADR-0019).
- Los módulos del token **no dependen de ningún usuario**: `tokens_listado.idUsuario` es solo dato de auditoría (creador).
- **Un acceso OAuth hereda los módulos del token manual que se pegó en `/authorize`** (su padre): `TokenScope::load` resuelve `COALESCE(idTokenPadre, idToken)`. Cambiar los módulos del padre afecta de inmediato a los accesos ya emitidos.
- Borrado en cascada: `tokensListado::Delete` elimina las filas de `tokens_listado_permisos` del token (junto con el borrado del token y la cascada OAuth).
- Regla de bloqueo del MCP (ADR-0012, `docs/architecture/decisions/0012-alcance-y-bloqueo-de-tokens-en-consumidores.md`): un consumidor acepta el token solo si existe, `idEstado = 1` y `FechaCaducidad IS NULL OR >= CURDATE()`; si no, 401 idéntico para todos los motivos. Sin módulos asignados, 403. Desactivar, caducar o quitar módulos lo retira de inmediato de los consumidores. Esto **no cambia** el backoffice: aquí estado y caducidad siguen siendo informativos. El servidor MCP (`admin/MCP/`, ver `admin/MCP/README.md`) implementa esta regla en `src/Auth/TokenAuthenticator.php` (401 idéntico para todos los motivos; 403 sin módulos) y el alcance por módulo (ADR-0019, que sustituye el criterio de acceso de ADR-0015) en `src/Auth/TokenScope.php`.
- Con OAuth, el MCP acepta además accesos `Origen` 2 solo si el padre es manual, está activo y no caducado y el cliente está activo (si no, 401 idéntico); el hijo caduca a la hora (`FechaExpiracion`). Flujo y duraciones: `admin/MCP/docs/CONEXION.md` §7.
- Ampliar el módulo ya instalado: botón "Actualizar Modulo" (ADR-0013), sin perder datos.

## Migración del alcance (ADR-0019)

Pasar de permisos con nivel a módulos exige ejecutar **"Actualizar Modulo" de `tokens` antes de usar el MCP nuevo**: `TokenScope::load` lee la columna `Modulo`, que antes no existía. El installer, tras crear las tablas y antes de los índices, ejecuta `migrarPermisosTokenAModulos()` (solo si la tabla conserva `idPermisos`; en una instalación nueva o ya migrada no hace nada):

1. M1 añade `Modulo`; M2 convierte cada permiso con `idLevelLimit` >= 1 y `idEstado` = 1 cuyo `RutaController` sea de un controlador de un módulo exponible en ese módulo (la misma condición con la que el MCP daba acceso: el acceso efectivo no cambia); M3 deduplica `(idToken, Modulo)` conservando la fila de menor id.
2. M4 descarta las filas sin módulo (permisos de módulos no exponibles, de `tokens` o `installer`, inactivos o con nivel 0, que no daban acceso al MCP); M5 elimina `uk_tkperm_token_permiso`, `idPermisos` e `idLevelLimit` y deja `Modulo` NOT NULL.

Es reanudable: el DDL no tiene rollback, pero cada paso es idempotente y los datos antiguos siguen intactos hasta M5; si falla, se vuelve a pulsar "Actualizar Modulo". Un token cuyos permisos no se convirtieron en ningún módulo queda **sin módulos y el MCP responde 403** hasta que se le asignen en la pestaña Permisos. Detalle: `projects/tokens-permisos-modulo/specs/database.md` §4-5. El nuevo código del MCP con la tabla antigua falla (consulta sobre `Modulo` inexistente): respetar el orden de despliegue.

## Riesgo aceptado

El token se guarda y se muestra en **texto plano** (decisión del usuario); los access tokens OAuth (`Origen` 2) también se guardan en claro (riesgo heredado, RA-1 de `mcp-oauth`), mientras que los códigos y refresh tokens se guardan solo como hash SHA-256. Quien tenga acceso a la tabla o al nivel Ver del permiso ve todos los tokens. Fuera de alcance: endpoint de validación de tokens para otros proyectos (D-5), rotación, hash, auditoría de uso.

## Dependencias

Tablas externas: `core_estados`, `usuarios_listado`, `core_permisos_listado_rutas` (módulo instalado). Consumidores: `admin/MCP/` (`src/Auth`, `src/Audit`, servidor de autorización `oauth/` y `src/OAuth/`), que lee y escribe estas tablas con la cuenta `MySQL_MCP`. Sin dependencias de installer de otros módulos.
