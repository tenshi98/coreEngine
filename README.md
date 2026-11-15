# coreEngine — Documentación del Proyecto

## Explicación General

**coreEngine** es una plataforma multi-sitio en **PHP 8 + Fat-Free Framework (F3) + MySQL**, organizada para trabajar con IA bajo **Spec-Driven Development (SDD)** sin romper la arquitectura existente.

Es un monólito modular estructurado en:
- **Núcleo compartido** en `vendors/application/`
- **Backoffice** en `admin/` (módulos en `admin/app/modules/`)
- **Sitios públicos** en `sites_*/`
- **Capa de IA** (`agents/`, `skills/`, `projects/`, `docs/`)

### Prioridad Absoluta

```text
ARQUITECTURA EXISTENTE > CONVENCIONES EXISTENTES > REQUISITOS DEL NUEVO PROYECTO > NUEVAS ABSTRACCIONES
```

### Regla Final
> Primero comprender. Después especificar. Después diseñar. Después programar. Finalmente validar y documentar.

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

## Flujo SDD Obligatorio

```text
REQUISITOS → ANÁLISIS → ESPECIFICACIÓN → ARQUITECTURA → DISEÑO DE BASE DE DATOS
→ DISEÑO API → TASKS → [APROBACIÓN] → IMPLEMENTACIÓN → TESTING → REVISIÓN → VALIDACIÓN → DOCUMENTACIÓN
```

No se implementa con requisitos/specs insuficientemente definidos ni sin spec aprobada.

## Conceptos Clave

| Concepto | Significado |
|---|---|
| **Agent** | QUIÉN realiza el trabajo (roles en `agents/`; sin conocimiento procedural técnico) |
| **Skill** | CÓMO se realiza determinado trabajo (conocimiento en `skills/`; no define roles) |
| **Spec** | QUÉ se debe construir (`projects/<p>/specs/`) |
| **Project** | DÓNDE se define el trabajo (`projects/<p>/`) |
| **Task** | QUÉ unidad concreta ejecutar (`projects/<p>/tasks/<ID>.md`) |
| **Validation** | CÓMO verificamos que está correcto (`projects/<p>/validation/validation.md`) |

## Punto de Entrada Rápido

- **¿Qué se carga al principio?** → `AGENTS.md` §0 paso 1: `skills/caveman/SKILL.md` (modo de respuesta) + `skills/boost-prompt/SKILL.md` (refinar petición si es ambigua o SDD)
- **¿Qué abarca esta petición?** → Puntual = solo lo pedido, sin tocar `projects/`; SDD = flujo completo
- **¿Qué tareas hay pendientes?** → `AGENTS.md` §2 (grep canónico) + `docs/history/PENDIENTES.md`
- **¿Qué se decidió antes sobre X?** → `docs/history/DECISIONES.md` + `docs/history/INDEX.md`
- **¿Auditar arquitectura?** → `docs/architecture/README.md` (mapa) + `docs/architecture/core/`
- **¿Crear o modificar una transacción de tipo X?** → `skills/transactions/SKILL.md` + `docs/architecture/transactions/README.md`
- **¿Exportar a Excel?** → `skills/excel/SKILL.md` + `docs/architecture/ui/export-excel.md`
- **¿Crear especificación?** → `agents/architect/instructions.md` + `skills/sdd/SKILL.md`
- **¿Implementar?** → `agents/developer/instructions.md` + `skills/transactions|php|mysql|api/SKILL.md`
- **¿Tocar BD?** → `agents/database/instructions.md` + `skills/mysql/SKILL.md`
- **¿Revisar / depurar?** → `agents/reviewer|debugger/instructions.md` + `skills/review|testing|debugging/SKILL.md`
- **¿No sé qué agent usar?** → `agents/triage/instructions.md` (clasifica y enruta)
- **¿Crear pruebas?** → `agents/tester/instructions.md` + `skills/testing/SKILL.md`
- **¿Auditar seguridad?** → `agents/security/instructions.md` + `skills/security-audit/SKILL.md`
- **¿Cambiar una pantalla?** → `agents/frontend/instructions.md` + `skills/frontend/SKILL.md`
- **¿Por qué se decidió X a nivel de arquitectura?** → `docs/architecture/decisions/` (ADR)
- **¿Documentar un cambio?** → `agents/documentation/instructions.md` + `docs/history/README.md` + `docs/change_logs/README.md`
- **¿Ver ejemplo completo?** → `projects/example/` (README + requirements + specs + architecture + tasks + validation)

## Próximos Pasos

1. Revisar la documentación arquitecural en `arquitectura.md` para comprender la estructura del sistema.
2. Consultar `instalacion.md` si se necesita desplegar o configurar el entorno.
3. Revisar `modulos.md` para entender los módulos disponibles y sus dependencias.
4. Usar `flujo-peticion.md` para depurar rutas o problemas de seguridad.
5. Consultar `bbdd.md` para diseño de nuevas tablas o relaciones.
6. Revisar `configuracion.md` para ajustes de parámetros del sistema.

---

*Documentación generada el: Sat Nov 14 2026*
*Total de archivos: 8 documentos de referencia*
*Imágenes catalogadas: 34 del directorio src/*

---