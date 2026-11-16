# Documentación del Proyecto

## Tabla de contenidos

- [Explicación General](#explicación-general)
- [Requisitos del Sistema](#requisitos-del-sistema)
- [Demo Plataforma](#demo-plataforma)
- [Documentación relacionada](#documentación-relacionada)

## Explicación General

Plataforma Básica enfocada al manejo e interacción con los distintos módulos instalados, algunos independientes de otros, y otros dependientes de la instalación de otros módulos.

Esta plataforma está diseñada específicamente para pequeñas y medianas empresas (PyMEs) con infraestructura de alojamiento estándar, compatible con entornos LAMP/LEMP (servidores Apache o Nginx, PHP y MySQL).

**coreEngine** es una plataforma multi-sitio en **PHP 8 + Fat-Free Framework (F3) + MySQL**, organizada para trabajar con IA bajo **Spec-Driven Development (SDD)** sin romper la arquitectura existente.

Es un monólito modular estructurado en:
- **Núcleo compartido** en `vendors/application/`
- **Backoffice** en `admin/` (módulos en `admin/app/modules/`)
- **Sitios públicos** en `sites_*/`
- **Capa de IA** (`agents/`, `skills/`, `projects/`, `docs/`)

---

## Requisitos del Sistema

Para desplegar y ejecutar correctamente la plataforma, el servidor debe cumplir con los siguientes requisitos:

- **Servidor Web:** Apache 2.4+ o Nginx (con el módulo de reescritura de URLs `mod_rewrite` habilitado).
- **Entorno PHP:** PHP 8.0 o superior.
- **Base de Datos:** MySQL 5.7+ / MariaDB 10.3+ (soporte nativo ampliable a PostgreSQL, SQLite, MongoDB o Redis).
- **Extensiones PHP Requeridas:**
  - `pdo` y `pdo_mysql`: Para la conexión y gestión persistente de base de datos.
  - `gd`: Para el procesamiento, redimensionamiento y recorte de imágenes.
  - `curl`: Para integraciones HTTP, servicios de mail y APIs externas.
  - `mbstring`: Manejo de cadenas de texto multibyte y codificaciones UTF-8.
  - `openssl`: Para cifrado seguro y generación de tokens de seguridad.
  - `json`: Procesamiento de respuestas JSON y tokens JWT.
  - `fileinfo`: Validación de tipos MIME en la subida segura de archivos.
- **Extensiones para Exportación a Excel Requeridas:**
  - `ctype` y `filter`: Validación y filtrado de datos, comprobación de tipos de caracteres y verificación de valores antes de su procesamiento.
  - `dom`, `libxml`, `simplexml`, `xml`, `xmlreader` y `xmlwriter`: Lectura, generación, análisis y manipulación de estructuras XML utilizadas en documentos y formatos de intercambio de datos.
  - `fileinfo`: Identificación y validación de tipos MIME de archivos para verificar su formato.
  - `iconv` y `mbstring`: Conversión de codificaciones y manejo de cadenas multibyte, garantizando la compatibilidad con caracteres especiales y UTF-8.
  - `zip` y `zlib`: Compresión, descompresión y gestión de archivos ZIP, utilizados en la estructura interna de documentos Excel .xlsx.


---

## Demo Plataforma

| Dato | Descripción |
|-----------|-------------|
| URL Demo | [democoreengine.digitalcreations.cl](https://democoreengine.digitalcreations.cl/) |
| Usuario | demo1@example.com |
| Contraseña | 1234 |

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
