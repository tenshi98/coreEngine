# Operación, escalabilidad y continuidad

Estrategia de operación de coreEngine: crecimiento, integración, control de cambios, respaldos y recuperación.

---

## Tabla de contenidos

- [Escalabilidad](#escalabilidad)
- [Integración con sistemas institucionales](#integración-con-sistemas-institucionales)
- [Control de cambios](#control-de-cambios)
- [Respaldo de información y recuperación](#respaldo-de-información-y-recuperación)
- [Monitoreo y logs](#monitoreo-y-logs)
- [Mantenimiento y actualización de módulos](#mantenimiento-y-actualización-de-módulos)

---

## Escalabilidad

### Crecimiento significativo del volumen de usuarios

La plataforma se concibió con una arquitectura ligera basada en **Fat-Free Framework (F3)**, lo que permite un consumo mínimo de memoria RAM (~1-2 MB por petición) y una latencia baja frente a frameworks monolíticos tradicionales.

Para escalar ante un incremento masivo de tráfico y usuarios activos, la evolución prevista es:

1. **Desacoplamiento del almacenamiento de archivos (nodos web stateless):**
   - **Estado actual:** almacenamiento local en `public/upload`.
   - **Evolución:** activar los drivers ya implementados (`FileManager.php` / `ConfigAPP.php`) para usar **Amazon S3** o **Google Cloud Storage (GCS)**, junto a una CDN (Cloudflare o AWS CloudFront). Esto elimina el estado local en los servidores web y acelera la entrega de archivos.

2. **Sesiones distribuidas y caché con Redis:**
   - **Estado actual:** sesiones PHP basadas en servidor o cookies/tokens individuales.
   - **Evolución:** configurar **Redis** como almacén centralizado de sesiones y caché mediante la clave `Redis_1` de `ConfigDataBase.php` y la librería `predis/` de `vendors/libs/predis`. Permite distribuir peticiones con un balanceador (Nginx, AWS ALB o HAProxy) hacia múltiples instancias sin perder el estado del usuario (*stateless horizontal scaling*).

3. **Escalamiento de la capa de datos:**
   - **Estado actual:** instancia relacional MySQL principal.
   - **Evolución:** implementar **réplicas de lectura** en MySQL/MariaDB. Aprovechando las claves de conexión de `ConfigDataBase.php` (`MySQL_ADMIN`, `MySQL_1`), las consultas de lectura masiva (reportes, catálogo, calendarios) se derivan a las réplicas, reservando el nodo primario para escrituras y transacciones críticas.

4. **Contenedores y auto-scaling:**
   - Empaquetar la aplicación en contenedores **Docker** con orquestación **Kubernetes (K8s)** o **AWS ECS**, permitiendo escalado y desescalado automático según CPU y memoria.

---

## Integración con sistemas institucionales

Para integrarse en ecosistemas universitarios o corporativos existentes (ERPs, CRMs, portales de alumnos/empleados), la solución evoluciona en:

1. **Autenticación centralizada (SSO / IAM):**
   - Integración con **OAuth2 / OpenID Connect (OIDC)**, **SAML 2.0** o **LDAP / Microsoft Active Directory**, para que los usuarios inicien sesión con credenciales institucionales.

2. **Exposición de API RESTful con JWT:**
   - Extensión de la capa de controladores (`ControllerBase.php`) para endpoints Web API RESTful en JSON, resguardados con los tokens de `ConfigToken.php`.
   - Documentación estandarizada con **OpenAPI 3.0 (Swagger)**.

3. **Sincronización asíncrona con webhooks y colas:**
   - Colas de mensajes (RabbitMQ o Redis Queue) para procesar eventos institucionales en segundo plano (sincronización de matrículas, actualización de roles, notificaciones masivas) sin afectar los tiempos de respuesta.

---

## Control de cambios

La gestión de cambios sigue un flujo normado para garantizar la estabilidad en producción.

### Estrategia de ramificación (GitFlow)

- `main` / `master`: código en producción, etiquetado con versiones semánticas (por ejemplo `v1.2.0`).
- `staging` / `qa`: entorno de pruebas integradas antes de producción.
- `feature/*`: desarrollo de nuevas funcionalidades o módulos.
- `hotfix/*`: correcciones urgentes desplegables tras aprobación.

### Pipeline de integración y despliegue continuo (CI/CD)

- **Validación automática** en cada Pull Request o Merge (GitHub Actions / GitLab CI):
  - Análisis estático y comprobación sintáctica de PHP (`php -l`).
  - Verificación de estándares de código (PSR-12).
  - Pruebas unitarias e integrales del motor de consultas (`QueryBuilder`).
- **Despliegue sin interrupción (zero-downtime):** enlaces simbólicos o despliegue azul/verde (*Blue-Green Deployment*) para no cortar sesiones activas.

### Documentación e histórico

- Mantener un registro de versiones con nuevas características, correcciones, cambios mayores e incompatibilidades (el repositorio aún no incluye un `CHANGELOG.md` en la raíz).
- Todo cambio relevante deja la doble traza del proyecto: `docs/history/` (memoria técnica) + `docs/change_logs/` (post divulgativo).

---

## Respaldo de información y recuperación

La estrategia de respaldo y continuidad de negocio mitiga la pérdida de datos por fallos de hardware, corrupción o desastres operacionales.

### Respaldos de base de datos

- **Dumps diarios automatizados:** tareas cron en el servidor o servicios gestionados (AWS RDS / GCP Cloud SQL), con respaldos completos diarios cifrados.
- **Recuperación en el punto en el tiempo (PITR):** registro binario de MySQL (*binary logging* / WAL) para restaurar a cualquier segundo del día.
- **Almacenamiento offsite cifrado:** los respaldos se cifran con AES-256 y se replican en un depósito externo inmutable (Amazon S3 Glacier o GCS con retención WORM).

### Respaldos de archivos multimedia (`upload`)

- **Entornos locales:** rsync/snapshot periódicos hacia un servidor de respaldo secundario.
- **Entornos cloud (S3 / GCS):** versionamiento de objetos y replicación entre regiones (*cross-region replication*) para durabilidad de 99.999999999%.

### Objetivos de recuperación ante desastres (DRP)

- **RPO (Recovery Point Objective):** máximo 1 hora de pérdida de datos en el peor escenario.
- **RTO (Recovery Time Objective):** restablecimiento completo en menos de 2 horas tras un fallo catastrófico, mediante infraestructura como código (Docker / Terraform) y procedimientos de restauración automatizados.

---

## Monitoreo y logs

- **Logs de aplicación:** `admin/security/error.log` y logs del servidor web.
- **Auditoría:** `AuditLogger` registra rechazos CSRF y `AUTHZ` de `ScopeAccess`.
- **Rate limit:** `admin/security/rateLimit_IP.json` y `rateLimit_User.json`.
- **Rotación de logs:** configurar rotación para evitar crecimiento indefinido.
- **Alertas recomendadas:** errores 5xx, picos de 429, fallos de conexión a BD y espacio en disco de `upload/`.

---

## Mantenimiento y actualización de módulos

- **Instalador idempotente:** `InstallModule` es re-ejecutable ("Actualizar Módulo") sin perder datos; crea tablas, índices, permisos y rutas solo si faltan (ADR-0013).
- **Actualización de dependencias:** las librerías de `vendors/libs/` se actualizan de forma controlada, cada una con su `vendor/` y `composer.lock`.
- **Revisión de módulos:** cada `admin/app/modules/<modulo>/README.md` documenta estructura, tablas, rutas y dependencias.
- **Pruebas:** el runner `sistemaTesteos` descubre suites de módulos y sitios (`docs/architecture/testing/runner.md`).
- **Limpieza:** no dejar archivos sueltos (`.bak`) en carpetas autocargadas; revisar `docs/architecture/security/files.md`.
- **Backups antes de migrar:** todo cambio de esquema o actualización de módulo se respalda antes de ejecutarse.

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
