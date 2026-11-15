# Seguridad de coreEngine

coreEngine implementa un sistema de gestión de sesiones y acceso centrado en la **autorización estricta**, la **detección de intrusos** y la **defensa en profundidad**. Este documento resume el modelo y enlaza a la verdad interna de `docs/architecture/security/`.

---

## Tabla de contenidos

- [Modelo de seguridad](#modelo-de-seguridad)
- [Sesiones y autenticación](#sesiones-y-autenticación)
- [Autorización dinámica de rutas](#autorización-dinámica-de-rutas)
- [Permisos y niveles de acceso](#permisos-y-niveles-de-acceso)
- [Anti-intrusión](#anti-intrusión)
- [CSRF](#csrf)
- [Rate limiting](#rate-limiting)
- [Validación de entrada y SQL](#validación-de-entrada-y-sql)
- [IDs cifrados](#ids-cifrados)
- [Archivos y subidas](#archivos-y-subidas)
- [Auditoría y errores](#auditoría-y-errores)
- [Recomendaciones de despliegue](#recomendaciones-de-despliegue)

---

## Modelo de seguridad

La seguridad se apoya en mecanismos existentes y transversales; **no se reimplementan** ni se introducen mecanismos nuevos sin una decisión documentada (ADR):

- `SessionService` — validación de sesión.
- `AuthenticationService` — login, creación y cierre de sesión.
- `PermissionService` — rutas permitidas por usuario.
- `CsrfToken` — token de sincronización por sesión.
- `ScopeAccess` — autorización de endpoints compartidos.
- `RateLimiter` — límite de peticiones por IP y por usuario.
- `BruteForceService` — bloqueo por intentos fallidos.
- `AuditLogger` — auditoría de rechazos y operaciones.
- `ErrorHandler` — registro y respuesta de errores.
- `CheckData` — validación de entrada.
- `QueryBuilder` — SQL con prepared statements.
- IDs cifrados AES-256-CBC.
- `FileManager` + `FilePolicy` / `FilePathGuard` / `FileInspector` — subida segura.

---

## Sesiones y autenticación

- Sesión F3 (`SESSION`) + cookie `Sesion_tk`.
- `SessionService::check($f3, $cookieToken)` valida la sesión del servidor contra la BD.
- `SessionService::validate(...)` comprueba expiración, token, usuario e IP.
- Vida de la sesión PHP: `JAR.lifetime = 86400` (`admin/public/index.php`).
- Sin sesión válida solo quedan las rutas de `userGuest.php`.
- Logout: `GET /auth/logout` → `miUsuario->logout`.
- Soporte de sesiones mediante **cookies tradicionales** o **JSON Web Tokens (JWT)**.

### Servicios de autenticación (`admin/app/helpers/`)

| Servicio | Rol |
|---|---|
| `AuthenticationService` | Login (`createDataLogin`), `createSession`, `closeSession`. |
| `PasswordService` | Hash y verificación de contraseñas. |
| `BruteForceService` | Bloqueo por intentos de login (por IP). |
| `UserRepository` | Datos del usuario (`getDataInfo`). |
| `UserSessionDataService` | Registro y comparación de accesos. |
| `SessionService` | Validación de sesión. |

### Anti-enumeración en login

`AuthenticationService::createDataLogin`:

- Email inexistente, contraseña incorrecta o usuario sin hash → mismo `401 'Credenciales incorrectas'`.
- `403 'Usuario Inactivo'` solo tras verificar la contraseña correcta.
- Si el usuario no existe o no tiene hash, se ejecuta `password_verify` contra `DUMMY_BCRYPT_HASH` (bcrypt cost 12) para igualar el tiempo de respuesta.

### IP del cliente y dispositivo

- `FunctionsServerClient::getClientIp`: `FILTER_VALIDATE_IP` sin excluir rangos privados (necesario para LAN/dev); último recurso `$_SERVER['REMOTE_ADDR']`.
- `SESSION.DataInfo.DeviceMobile` (bool): acceso desde móvil o tablet según el User-Agent (`FunctionsServerClient::validarDispositivoMovil`).

---

## Autorización dinámica de rutas

Las rutas a las que un usuario puede acceder se **generan dinámicamente** al iniciar sesión, en base a sus **permisos asignados**. Una ruta no permitida **no existe** para ese usuario (404).

1. Al iniciar sesión, `PermissionService::getRoutes($TipoUsuarioID, $UsuarioID)` trae solo las rutas con `idLevelLimit <=` el nivel asignado.
2. El bootstrap registra esas rutas en `admin/app/utils/`.
3. En el render, `getArrLevel($f3, $controllerName)` expone `LevelAccess` y `RouteAccess`.
4. La vista oculta acciones con `LevelAccess`; el controlador **no** re-verifica el nivel.

Consecuencia: toda acción debe declararse como ruta en el installer con su `idLevelLimit` correcto.

### Superadmin

`SESSION.DataInfo.UserType == 1` → todas las rutas de `admin/app/utils/userAdmin.php`.

### Endpoints compartidos

Los endpoints que no pueden conocer al invocador (`/core/fileExplorer/*`) se autorizan con `ScopeAccess`:

1. El módulo que renderiza la pantalla **concede** el nivel en sesión: `ScopeAccess::grant($f3, $scope, $level, $ttlMin = 15)`.
2. El endpoint **valida**: `ScopeAccess::check($f3, $scope, $minLevel)`.
3. Umbrales de `sistemaFuncionalidad`: ver 1, escribir 2, borrar 3.
4. El nivel **nunca** viaja por el cliente; `AccessScope` solo selecciona una concesión existente. Scope desconocido → denegado (fail-closed). La concesión se ata al `UserID`, caduca por TTL y se audita como `AUTHZ`.

---

## Permisos y niveles de acceso

| Tabla | Contenido |
|---|---|
| `core_permisos_categorias` | Categorías de permisos (menú). |
| `core_permisos_listado` | Permiso (transacción). |
| `core_permisos_listado_rutas` | Rutas del permiso con `idLevelLimit`. |
| `core_permisos_listado_rutas_metodo` | Verbos: 1 GET, 2 POST, 3 DELETE, 4 PUT. |
| `usuarios_listado_permisos` | Asignación permiso → usuario con su `idLevelLimit`. |

Niveles: **1 Ver · 2 Editar · 3 Crear · 4 Borrar**.

Gestión:

- Permisos y rutas: `admin/app/modules/root_plataforma/` (`permisosCategorias`, `permisosListado`, `permisosListadoRutas`).
- Asignación a usuarios: `admin/app/modules/usuarios/` (`usuariosListadoPermisos`, `usuariosListadoPermisosBodegas`, `usuariosListadoPermisosMaquinas`).

Detalle: `docs/architecture/security/permissions.md` y `docs/architecture/patterns/routes-permissions.md`.

---

## Anti-intrusión

Al iniciar sesión se genera un **token de seguridad** que incluye:

- ID de usuario.
- Dirección IP.
- Token CSRF.

El token se valida en **cada interacción** del usuario con la plataforma, comprobando que el token CSRF y la IP del equipo sean los mismos del inicio de sesión.

Respuesta a intrusos:

1. Intento de sesión no autorizado a una transacción no autorizada o *spoofing* repetitivo → el intruso es **inicialmente baneado por 5 horas**.
2. Si persiste el ataque, la IP maliciosa puede enviarse a la **lista negra a nivel del servidor** de forma automática, si el entorno de hosting lo permite.

---

## CSRF

Token de sincronización por sesión. Clase: `vendors/application/security/CsrfToken.php`.

- Se genera de forma perezosa (`CsrfToken::getToken()`) y se guarda en `SESSION.csrf_token`.
- Se publica en las vistas como `$CSRF_TOKEN`: meta `csrf-token` + `window.CSRF_TOKEN`.
- Cada `Response::json` devuelve el token vigente en la cabecera `X-CSRF-Token`.
- Validación central en el borde: `ControllerBase::beforeroute()`, `ControllerFiles::beforeroute()` y `ControllerWeb::beforeroute()` → `CsrfToken::guard($f3)`.
- Solo métodos de escritura (POST, PUT, DELETE, PATCH); comparación con `hash_equals()`. Fallo → HTTP 419 + registro en `AuditLogger`.
- Llamadas internas entre controladores no pasan por el router y no se re-validan.

### Configuración (`ConfigAPP::APP`)

| Clave | Valor |
|---|---|
| `csrfEnabled` | `true` |
| `csrfTokenName` | `_token` (campo POST) |
| `csrfHeaderName` | `X-CSRF-Token` (cabecera AJAX) |
| `csrfExcludeVerbs` | `['GET', 'HEAD', 'OPTIONS']` |
| `csrfExcludeRoutes` | Instalador `/install/*` |

### Transporte y ciclo de vida

- Cabecera `X-CSRF-Token`: `$.ajaxSetup` en `admin/public/js/functions_v2.js` y `fetch()` del fileExplorer en `UIWidgetsCommon`.
- Campo oculto `_token` inyectado automáticamente en todos los `<form>`.
- Ante 419 el JS reintenta con el token nuevo (`tokenDesdeRespuesta419`, `adoptarTokenCsrf`).
- Rota tras el login (`AuthenticationService::createSession`) y se limpia en el logout.
- Los sitios públicos cargan `CsrfToken` en su bootstrap y publican `$CSRF_TOKEN` (ADR-0007).

Detalle: `docs/architecture/security/csrf.md`.

---

## Rate limiting

- `RateLimiter::isBlockedByUser($userId)` / `isBlockedByIp($IP)` en el bootstrap → HTTP 429.
- Persistencia: `admin/security/rateLimit_IP.json`, `admin/security/rateLimit_User.json`.
- No se salta ni se desactiva.
- Límites por defecto en `ConfigAPP::APP`:
  - Invitados: 20 intentos / 60 s.
  - Usuarios logueados: 240 intentos / 480 s.

---

## Validación de entrada y SQL

- **Prepared statements** en todas las consultas: `QueryBuilder` construye SQL desde arrays y nunca concatena datos de entrada.
- **Validación de entrada:** `CheckData` (`checkingData`) evalúa reglas de negocio, campos requeridos, unicidad y formatos antes de operar sobre la BD.
- **Sin SQL concatenado** con datos del usuario; los filtros se construyen con las claves de `dataCheck`.
- **Errores genéricos** al cliente; el detalle queda en logs.

Detalle: `docs/architecture/data/datacheck-keys.md`, `docs/architecture/data/array-query.md` y `docs/architecture/data/querybuilder-behaviour.md`.

---

## IDs cifrados

Los identificadores que viajan por la URL se cifran con **AES-256-CBC**. No se exponen IDs numéricos. `ControllerBase` ofrece `isValidDecrypted` para validar el ID descifrado.

Detalle: `docs/architecture/data/ids.md`.

---

## Archivos y subidas

| Pieza | Rol |
|---|---|
| `FileManager` | Fachada: valida, sube y borra (`validateFiles`, `uploadFile`, `deleteFile`, `deleteFilesMassive`). |
| `FilePolicy`, `FilePathGuard`, `FileInspector`, `FileUploader`, `FileExplorer` | Política de tipos, anti path-traversal, MIME real, subida y explorador. |
| `ImageManager` | Imágenes: redimensionado, recorte y conversión. |
| `StorageDriverFactory` | Driver de almacenamiento: Local, S3, GCS, SFTP. |
| `ControllerBase::generarNombreArchivo()` | Nombre de archivo generado. |

Reglas:

- En controladores, la subida va por la clave `files` del `$query` de `Base_insert` / `Base_update`. No mover archivos a mano.
- Validaciones: extensión (`ValidarTipo`), peso en MB (`ValidarPeso`), MIME real, nombre oculto y códigos `UPLOAD_ERR_*`.
- Rutas del explorador siempre saneadas (`sanitizePath`, `sanitizeFolderName`); nunca rutas absolutas ni `..` desde la vista.
- `.htaccess` con `deny from all` en cada carpeta (`app/`, `modules/`, `config/`, `helpers/`, `upload/` y las carpetas de cada módulo).
- En producción, `upload/` sin permisos `777`; scripts deshabilitados por Apache/Nginx (ver [INSTALLATION.md](INSTALLATION.md#7-permisos-seguros-de-upload)).

Detalle: `docs/architecture/security/files.md`.

---

## Auditoría y errores

- `AuditLogger` (`vendors/application/security/`) se instancia en el bootstrap y registra operaciones del sistema (rechazos CSRF, `AUTHZ` de `ScopeAccess`).
- `ErrorHandler` registra errores y responde de forma controlada.
- Archivos de log en `admin/security/`: `error.log`, auditoría y rate limit.
- Los logs deben tener rotación y permisos restringidos.

---

## Recomendaciones de despliegue

1. **HTTPS obligatorio** en producción; cookies de sesión con `Secure` y `HttpOnly` (según configuración del sitio).
2. **Permisos mínimos:** `755` para directorios, `644` para archivos; nunca `777`.
3. **`.env` fuera de control de versiones** y con permisos restringidos.
4. **Ocultar errores** al usuario; revisar `admin/security/error.log`.
5. **Rate limit activo** y revisión periódica de `rateLimit_IP.json` / `rateLimit_User.json`.
6. **Lista negra del servidor** configurada si el hosting lo permite.
7. **Copias de seguridad** cifradas y probadas (ver [OPERATIONS.md](OPERATIONS.md#respaldo-de-información-y-recuperación)).
8. **Auditoría de dependencias** de `vendors/libs/` y actualización controlada.
9. **No exponer** `admin/`, `vendors/`, `config/` ni `upload/` fuera del `public/` correspondiente.

---

## Documentación relacionada

- [README.md](README.md) — visión general, demo, SDD y reglas.
- [AGENTES.md](AGENTES.md) — agents, skills, specs y tasks.
- [ARCHITECTURE.md](ARCHITECTURE.md) — arquitectura, componentes de seguridad y capa de datos.
- [DATABASE.md](DATABASE.md) — modelo de datos y diagrama ER.
- [FEATURES.md](FEATURES.md) — funcionalidades y módulos con capturas.
- [INSTALLATION.md](INSTALLATION.md) — requisitos, instalación y configuración.
- [OPERATIONS.md](OPERATIONS.md) — escalabilidad, cambios, backups y DRP.
- [SECURITY.md](SECURITY.md) — modelo de seguridad, permisos y niveles de acceso.
