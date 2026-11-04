# Módulo `maquinas`

Administración de máquinas y de sus tabs de la pantalla `Resumen`.

## Estructura

- `controller/maquinasListado.php`: listado principal de máquinas.
- `controller/maquinasListadoSensores.php`: CRUD de sensores en JSON (`maquinas_listado.Sensores`).
- `controller/maquinasListadoAlarmas.php`: CRUD de alarmas personalizadas en JSON (`maquinas_listado.Alarmas`).
- `views/maquinasListado-Resumen.php`: tabs de la máquina (sensores, componentes, documentos, observaciones, alarmas).
- `views/maquinasListado-Resumen-Alarmas-{formNew,formEdit,UpdateList,View}.php`: UI del tab de alarmas.
- `installer/maquinasInstaller.php`: instalación, rutas, permisos, esquema y catálogos del módulo.
- `testing/`: pruebas del runner `sistemaTesteos` (métodos públicos terminados en `Test`).

## Alarmas personalizadas

- Persistencia: arreglo JSON en `maquinas_listado.Alarmas` (columna `LONGTEXT`).
- Cada alarma se identifica con un `uid`; cada fila referencia un sensor por `uid` y conserva su nombre desnormalizado.
- Catálogos: `core_tipos_alarmas` (8 tipos) y `core_tipos_alarmas_prioridad`; se siembran en `install.sql` y en `seedCatalogs()` con `CREATE TABLE IF NOT EXISTS` + `INSERT IGNORE`.
- Rutas (permiso existente `Maquinas - Listado`, nivel ≥1 ver / ≥2 editar):
  - `GET alarmas/new/@id`
  - `GET alarmas/updateList/@id`
  - `GET alarmas/view/@id?uid=`
  - `GET alarmas/getID/@id?uid=`
  - `POST alarmas`
  - `POST alarmas/update`
  - `DELETE alarmas` (cuerpo `idMaquina` cifrado + `uid`)
- No existe reordenamiento (`SaveOrder`/`order`) para alarmas.
- Vistas y controlador están documentados en `projects/maquinas_alarmas/`.

## Pruebas

`admin/app/modules/maquinas/testing/maquinasListadoAlarmasTesting.php` valida estructura, contratos del controlador, reglas de negocio, lógica JSON y seguridad de vistas. No ejecuta escrituras ni el instalador.
