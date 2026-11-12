# Módulo Sistema

Módulo de configuración de la empresa y orden de widgets para el panel de administración.

## Rutas Principales
- `administracion/sistema` - Vistas del sistema (empresa)
- `mantencionSistema/sistema/widgetsOrden` - Mantenimiento de orden de widgets

## Controladores
- `coreSistema` - Controlador principal con métodos: Resumen, ResumenUpdate, Update, delFiles, WidgetsOrden, WidgetsOrdenSave

## Instalador
- Publica rutas y permisos para el módulo
- Crea 2 permisos: uno para empresa (CRUD Resumen) y uno para widgets (orden)

## Testing
- Suite de pruebas en `testing/coreSistemaTesting.php`
- Cobertura baseline: estructuraModuloTest, controladorVistasTest, controladorEscrituraTest, controladorRutasTest, permisosSesionTest, logicaValidacionTest, seguridadVistasTest
- Pruebas verifican source code sin ejecutar operaciones destructivas
