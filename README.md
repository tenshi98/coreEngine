# Documentación del Proyecto

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

## Estructura de la Documentación Generada

A continuación se detalla cada documento creado para este proyecto, con una breve descripción y acceso directo:

| # | Archivo | Tema Principal | Para Qué Sirve |
|---|---|---|---|
| 1 | **arquitectura.md** | Arquitectura del sistema | Estructura completa: stack, componentes, flujo de petición, tipos de transacción, vistas, seguridad y arreglo de 34 imágenes del proyecto. |
| 2 | **instalacion.md** | Instalación y configuración | Guía paso a paso: importación de base de datos, ConfigAPP, ConfigData, ConfigMail, permisos seguros de upload y verificación final. |
| 3 | **configuracion.md** | Configuración de parámetros | Parámetros detallados de ConfigAPP.php, ConfigData.php, ConfigMail.php y ConfigToken.php con sus valores por defecto y críticos. |
| 4 | **modulos.md** | Módulos del backoffice | Inventario de 33 módulos con responsabilidades, dependencias (I/T/W/F), estructura canónica y módulos sin installer. |
| 5 | **bbdd.md** | Diagrama de base de datos | Diagrama ER completo con 58 tablas, descripciones de campos clave, índices y relaciones importantes. |
| 6 | **flujo-peticion.md** | Flujo de petición | Bootstrap completo (`admin/public/index.php`), diagnóstico rápido de problemas, puntos de entrada y flujo de seguridad anti-intrusión. |
| 7 | **agentes.md** | Agentes y roles | Conceptos fundamentales: Agent (QUIEN), Skill (CÓMO), Spec (QUÉ), Task (QUÉ), Validation (CÓMO), Docs (VERDAD), History (MEMORIA), ChangeLog (Blog). Contrato común y flujo SDD. |
| 8 | **README.md** | Este archivo | **Vista actual** — presentando esta explicación general y acceso a los demás documentos. |

## Próximos Pasos

1. Revisar la documentación arquitecural en `arquitectura.md` para comprender la estructura del sistema.
2. Consultar `instalacion.md` si se necesita desplegar o configurar el entorno.
3. Revisar `modulos.md` para entender los módulos disponibles y sus dependencias.
4. Usar `flujo-peticion.md` para depurar rutas o problemas de seguridad.
5. Consultar `bbdd.md` para diseño de nuevas tablas o relaciones.
6. Revisar `configuracion.md` para ajustes de parámetros del sistema.
