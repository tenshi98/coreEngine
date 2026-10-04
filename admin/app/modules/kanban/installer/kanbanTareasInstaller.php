<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class kanbanTareasInstaller extends ControllerInstaller {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_ADMIN);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName    = 'kanbanTareasInstaller';
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                               INSTALACION                                  */
    /******************************************************************************/
    /*******************************************************************/
    // Se lista la informacion
    /*******************************************************************/
    public function ListDataModule(){

        /************************************/
        // Se instancian los datos
        $usuariosInstaller = new usuariosInstaller();

        /************************************/
        // Se verifica si esta instalado
        $nData1    = $this->GetCountDataModule();
        $DepData1  = $usuariosInstaller->GetCountDataModule();

        /************************************/
        // Defino si etsa instalado en base a la respuesta
        $countPermisos = is_numeric($nData1)&&$nData1!=0 ? 1 : 0;
        $DepInstall_1  = is_numeric($DepData1)&&$DepData1!=0 ? 1 : 0;

        /************************************/
        // Se crean los datos a mostrar
        $arrData = [
            'Nombre'        => 'Módulo de Tareas Kanban',
            'Descripcion'   => 'Módulo para gestionar las tareas',
            'Controller'    => $this->controllerName,
            'countPermisos' => $countPermisos,
            'Dependencias'  => [
                [
                    'Nombre' => ' - Módulo de Administracion de Usuarios instalado',
                    'Numero' => $DepInstall_1,
                ],
            ]
        ];

        /************************************/
        // Retorno los datos
        return $arrData;
    }

    /*******************************************************************/
    // Instalacion del modulo
    /*******************************************************************/
    public function InstallModule(){

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /************************************/
        // Variables
        $arrTables    = $this->listTables();
        $arrPermisos  = array();

        /************************************/
        // Verifico si existe
        if($arrTables){
            // Recorro los datos
            foreach ($arrTables as $table) {
                /************************************/
                // Preparo los datos
                $xParams = ['query' => $table];
                // Se ejecuta la query
                $xTable  = $this->Base_createTable($xParams);
                /************************************/
                // Si falla la ejecucion, se revierte de inmediato
                if ($xTable['status'] === false) {
                    // Se revierte la transaccion
                    $this->Base_transactionRollback();
                    // Se reporta el error
                    Response::error('Error al operar con la Base de Datos', 500, $xTable['error']);
                }
            }
        }

        /*******************************************************/
        /*                 SE GENERAN LAS RUTAS                */
        /*******************************************************/
        $arrPermisos[] = [
            'idPermisosCat'  => '2',                              // Gestión Proyectos
            'idEstado'       => '1',                              // Activo
            'idTipo'         => '1',                              // Crud Normal
            'Nombre'         => 'Tareas en Curso',                // Nombre de la transaccion
            'Descripcion'    => 'Listado de tareas gestionadas',  // Descripcion de la transaccion
            'idLevelLimit'   => '4',                              // Ver / Editar / Crear / Borrar
            'RutaWeb'        => 'gestionProyectos/kanban/tareas', // Ruta web de la transaccion
            'RutaController' => 'kanbanTareas',                   // Controlador de la transaccion
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '2',                                     // Gestión Proyectos
            'idEstado'       => '1',                                     // Activo
            'idTipo'         => '3',                                     // Informe
            'Nombre'         => 'Informe Tareas',                        // Nombre de la transaccion
            'Descripcion'    => 'Informe de las tareas del panel',       // Descripcion de la transaccion
            'idLevelLimit'   => '1',                                     // Solo Ver
            'RutaWeb'        => 'gestionProyectos/kanban/informeTareas', // Ruta web de la transaccion
            'RutaController' => 'informeTareas',                         // Controlador de la transaccion
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '1',                              // Administración
            'idEstado'       => '1',                              // Activo
            'idTipo'         => '1',                              // Crud Normal
            'Nombre'         => 'Proyectos - Tableros',   // Nombre de la transaccion
            'Descripcion'    => 'Listado de tableros kanban',     // Descripcion de la transaccion
            'idLevelLimit'   => '4',                              // Ver / Editar / Crear / Borrar
            'RutaWeb'        => 'administracion/kanban/tableros', // Ruta web de la transaccion
            'RutaController' => 'kanbanTableros',                 // Controlador de la transaccion
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '1',                                                       // Administración
            'idEstado'       => '1',                                                       // Activo
            'idTipo'         => '1',                                                       // Crud Normal
            'Nombre'         => 'Proyectos - Tareas',                              // Nombre de la transaccion
            'Descripcion'    => 'Permite crear los trabajos a hacer dentro de las tareas', // Descripcion de la transaccion
            'idLevelLimit'   => '4',                                                       // Ver / Editar / Crear / Borrar
            'RutaWeb'        => 'administracion/kanban/trabajos',                          // Ruta web de la transaccion
            'RutaController' => 'kanbanTrabajos',                                          // Controlador de la transaccion
        ];
        /************************************/
        // Verifico si existe
        if($arrPermisos){

            /************************************/
            // Se inicia la transacción
            $this->Base_installModule($arrPermisos);

        }

        /************************************/
        // Se confirma la transaccion
        $this->Base_transactionCommit();

        /************************************/
        // Retorno True por defecto
        return true;

    }

    /*******************************************************************/
    // Desinstalacion del modulo
    /*******************************************************************/
    public function UninstallModule(){

        /************************************/
        // Retorno datos
        return $this->Base_uninstallModule($this->RutaController(), $this->listTables());

    }

    /*******************************************************************/
    // Se cuentan las rutas del controlador
    /*******************************************************************/
    public function GetCountDataModule(){

        /************************************/
        // Retorno datos
        return $this->Base_getCountDataModule($this->RutaController());

    }

    /*******************************************************************/
    // Se listan las rutas
    /*******************************************************************/
    public function listRouteModule(int $type, mixed $permisosID): array {
        $routes = $this->getRouteDefinitions($type);

        return array_map(static function (array $route) use ($permisosID): array {
            $route['idPermisos'] = $permisosID;
            return $route;
        }, $routes);
    }

    public function getRouteDefinitions(int $type): array {
        return match ($type) {
            1 => [
                ['idMetodo' => 1, 'RutaWeb' => 'gestionProyectos/kanban/tareas/listAll',                   'RutaController' => 'kanbanTareas->listAll',              'Descripcion' => 'Listar Toda la Información',                   'idLevelLimit' => 1, 'Controller' => 'kanbanTareas'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionProyectos/kanban/tareas/search',                    'RutaController' => 'kanbanTareas->UpdateTableList',      'Descripcion' => 'Filtrar datos',                                'idLevelLimit' => 1, 'Controller' => 'kanbanTareas'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionProyectos/kanban/tareas/updateList',                'RutaController' => 'kanbanTareas->UpdateList',           'Descripcion' => 'Actualizar Lista',                             'idLevelLimit' => 2, 'Controller' => 'kanbanTareas'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionProyectos/kanban/tareas/view/@id',                  'RutaController' => 'kanbanTareas->View',                 'Descripcion' => 'Mostrar Detallado',                            'idLevelLimit' => 1, 'Controller' => 'kanbanTareas'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionProyectos/kanban/tareas/getID/@id',                 'RutaController' => 'kanbanTareas->GetID',                'Descripcion' => 'Información para el formulario edición',       'idLevelLimit' => 2, 'Controller' => 'kanbanTareas'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionProyectos/kanban/tareas',                           'RutaController' => 'kanbanTareas->Insert',               'Descripcion' => 'Crear Información',                            'idLevelLimit' => 3, 'Controller' => 'kanbanTareas'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionProyectos/kanban/tareas/update',                    'RutaController' => 'kanbanTareas->Update',               'Descripcion' => 'Editar por post (modificar y subir archivos)', 'idLevelLimit' => 2, 'Controller' => 'kanbanTareas'],
                ['idMetodo' => 3, 'RutaWeb' => 'gestionProyectos/kanban/tareas',                           'RutaController' => 'kanbanTareas->Delete',               'Descripcion' => 'Borrar dato y archivos',                       'idLevelLimit' => 4, 'Controller' => 'kanbanTareas'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionProyectos/kanban/tareas/changeStatus',              'RutaController' => 'kanbanTareas->ChangeStatus',         'Descripcion' => 'Actualiza el estado',                          'idLevelLimit' => 2, 'Controller' => 'kanbanTareas'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionProyectos/kanban/tareas/print/@id',                 'RutaController' => 'kanbanTareas->Print',                'Descripcion' => 'Pantalla imprimir',                            'idLevelLimit' => 1, 'Controller' => 'kanbanTareas'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionProyectos/kanban/estados/getID/@id',                'RutaController' => 'kanbanEstados->GetID',               'Descripcion' => 'Información para el formulario edición',       'idLevelLimit' => 2, 'Controller' => 'kanbanEstados'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionProyectos/kanban/estados',                          'RutaController' => 'kanbanEstados->Insert',              'Descripcion' => 'Crear Información',                            'idLevelLimit' => 3, 'Controller' => 'kanbanEstados'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionProyectos/kanban/estados/update',                   'RutaController' => 'kanbanEstados->Update',              'Descripcion' => 'Editar por post (modificar y subir archivos)', 'idLevelLimit' => 2, 'Controller' => 'kanbanEstados'],
                ['idMetodo' => 3, 'RutaWeb' => 'gestionProyectos/kanban/estados',                          'RutaController' => 'kanbanEstados->Delete',              'Descripcion' => 'Borrar dato y archivos',                       'idLevelLimit' => 4, 'Controller' => 'kanbanEstados'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionProyectos/kanban/tareasTareas/newData/@id',         'RutaController' => 'kanbanTareasTareas->NewData',        'Descripcion' => 'Formulario Creación',                          'idLevelLimit' => 2, 'Controller' => 'kanbanTareasTareas'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionProyectos/kanban/tareasTareas/getID/@id',           'RutaController' => 'kanbanTareasTareas->GetID',          'Descripcion' => 'Informacion para el formulario',               'idLevelLimit' => 2, 'Controller' => 'kanbanTareasTareas'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionProyectos/kanban/tareasTareas',                     'RutaController' => 'kanbanTareasTareas->Insert',         'Descripcion' => 'Crear Información',                            'idLevelLimit' => 2, 'Controller' => 'kanbanTareasTareas'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionProyectos/kanban/tareasTareas/update',              'RutaController' => 'kanbanTareasTareas->Update',         'Descripcion' => 'Editar por post (modificar y subir archivos)', 'idLevelLimit' => 2, 'Controller' => 'kanbanTareasTareas'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionProyectos/kanban/tareasParticipantes/newData/@id',  'RutaController' => 'kanbanTareasParticipantes->NewData', 'Descripcion' => 'Formulario Creación',                          'idLevelLimit' => 2, 'Controller' => 'kanbanTareasParticipantes'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionProyectos/kanban/tareasParticipantes',              'RutaController' => 'kanbanTareasParticipantes->Insert',  'Descripcion' => 'Crear Información',                            'idLevelLimit' => 2, 'Controller' => 'kanbanTareasParticipantes'],
                ['idMetodo' => 3, 'RutaWeb' => 'gestionProyectos/kanban/tareasParticipantes',              'RutaController' => 'kanbanTareasParticipantes->Delete',  'Descripcion' => 'Borrar dato y archivos',                       'idLevelLimit' => 2, 'Controller' => 'kanbanTareasParticipantes'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionProyectos/kanban/tareas/updateTableList',           'RutaController' => 'kanbanTareas->UpdateTableList',      'Descripcion' => 'Filtrar datos',                                'idLevelLimit' => 1, 'Controller' => 'kanbanTareas'],
            ],
            2 => [
                ['idMetodo' => 1, 'RutaWeb' => 'gestionProyectos/kanban/informeTareas/listAll',   'RutaController' => 'informeTareas->listAll',     'Descripcion' => 'Filtro de búsqueda', 'idLevelLimit' => 1, 'Controller' => 'informeTareas'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionProyectos/kanban/informeTareas/search',    'RutaController' => 'informeTareas->UpdateList',  'Descripcion' => 'Filtrar datos',      'idLevelLimit' => 1, 'Controller' => 'informeTareas'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionProyectos/kanban/informeTareas/view/@id',  'RutaController' => 'informeTareas->View',        'Descripcion' => 'Mostrar Detallado',  'idLevelLimit' => 1, 'Controller' => 'informeTareas'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionProyectos/kanban/informeTareas/print/@id', 'RutaController' => 'informeTareas->Print',       'Descripcion' => 'Pantalla imprimir',  'idLevelLimit' => 1, 'Controller' => 'informeTareas'],
            ],
            3 => [
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/kanban/tableros/listAll',     'RutaController' => 'kanbanTableros->listAll',     'Descripcion' => 'Listar Toda la Información',                    'idLevelLimit' => 1, 'Controller' => 'kanbanTableros'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/kanban/tableros/search',      'RutaController' => 'kanbanTableros->UpdateList',  'Descripcion' => 'Filtrar datos',                                 'idLevelLimit' => 1, 'Controller' => 'kanbanTableros'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/kanban/tableros/updateList',  'RutaController' => 'kanbanTableros->UpdateList',  'Descripcion' => 'Actualizar Lista',                              'idLevelLimit' => 2, 'Controller' => 'kanbanTableros'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/kanban/tableros/view/@id',    'RutaController' => 'kanbanTableros->View',        'Descripcion' => 'Mostrar Detallado',                             'idLevelLimit' => 1, 'Controller' => 'kanbanTableros'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/kanban/tableros/getID/@id',   'RutaController' => 'kanbanTableros->GetID',       'Descripcion' => 'Información para el formulario edición',        'idLevelLimit' => 2, 'Controller' => 'kanbanTableros'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/kanban/tableros',             'RutaController' => 'kanbanTableros->Insert',      'Descripcion' => 'Crear Información',                             'idLevelLimit' => 3, 'Controller' => 'kanbanTableros'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/kanban/tableros/update',      'RutaController' => 'kanbanTableros->Update',      'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'kanbanTableros'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/kanban/tableros',             'RutaController' => 'kanbanTableros->Delete',      'Descripcion' => 'Borrar dato y archivos',                        'idLevelLimit' => 4, 'Controller' => 'kanbanTableros'],
            ],
            4 => [
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/kanban/trabajos/listAll',     'RutaController' => 'kanbanTrabajos->listAll',     'Descripcion' => 'Listar Toda la Información',                   'idLevelLimit' => 1, 'Controller' => 'kanbanTrabajos'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/kanban/trabajos/search',      'RutaController' => 'kanbanTrabajos->UpdateList',  'Descripcion' => 'Filtrar datos',                                'idLevelLimit' => 1, 'Controller' => 'kanbanTrabajos'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/kanban/trabajos/updateList',  'RutaController' => 'kanbanTrabajos->UpdateList',  'Descripcion' => 'Actualizar Lista',                             'idLevelLimit' => 2, 'Controller' => 'kanbanTrabajos'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/kanban/trabajos/view/@id',    'RutaController' => 'kanbanTrabajos->View',        'Descripcion' => 'Mostrar Detallado',                            'idLevelLimit' => 1, 'Controller' => 'kanbanTrabajos'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/kanban/trabajos/getID/@id',   'RutaController' => 'kanbanTrabajos->GetID',       'Descripcion' => 'Información para el formulario edición',       'idLevelLimit' => 2, 'Controller' => 'kanbanTrabajos'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/kanban/trabajos',             'RutaController' => 'kanbanTrabajos->Insert',      'Descripcion' => 'Crear Información',                            'idLevelLimit' => 3, 'Controller' => 'kanbanTrabajos'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/kanban/trabajos/update',      'RutaController' => 'kanbanTrabajos->Update',      'Descripcion' => 'Editar por post (modificar y subir archivos)', 'idLevelLimit' => 2, 'Controller' => 'kanbanTrabajos'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/kanban/trabajos',             'RutaController' => 'kanbanTrabajos->Delete',      'Descripcion' => 'Borrar dato y archivos',                       'idLevelLimit' => 4, 'Controller' => 'kanbanTrabajos'],
            ],
            default => [],
        };
    }

    /*******************************************************************/
    // Se listan los controladores
    /*******************************************************************/
    private function RutaController(){

        /************************************/
        // Se obtiene el nombre de los Controladores utilizados
        $RutaController  = '"kanbanTareas"';
        $RutaController .= ',"kanbanEstados"';
        $RutaController .= ',"kanbanTareasTareas"';
        $RutaController .= ',"kanbanTareasParticipantes"';
        $RutaController .= ',"informeTareas"';
        $RutaController .= ',"kanbanTableros"';
        $RutaController .= ',"kanbanTrabajos"';

        /************************************/
        // Retorno los datos
        return $RutaController;
    }

    /*******************************************************************/
    // Se listan las tablas
    /*******************************************************************/
    public function listTables(){

        /************************************/
        // Variables
        $arrTables    = array();

        /*******************************************************/
        /*                 SE GENERAN LAS TABLAS               */
        /*******************************************************/
        $arrTables[] = [
            'table'      => 'kanban_estados',
            'data'       => '`idKanbanEstado` int(10) unsigned NOT NULL AUTO_INCREMENT,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`idColor` int(10) unsigned NOT NULL,`idPrioridad` int(10) unsigned NOT NULL,`idCierre` int(10) unsigned NOT NULL',
            'primaryKey' => 'idKanbanEstado',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'kanban_tareas',
            'data'       => '`idKanban` bigint(20) unsigned NOT NULL AUTO_INCREMENT,`idKanbanEstado` int(10) unsigned NOT NULL,`idEstadoCierre` int(10) unsigned NOT NULL,`idPrioridad` int(10) unsigned NOT NULL,`idUsuario` int(10) unsigned NOT NULL,`Fecha` date NOT NULL,`Titulo` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`Descripcion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`FechaCreacion` date NOT NULL',
            'primaryKey' => 'idKanban',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'kanban_tareas_historial',
            'data'       => '`idHistorial` bigint(20) unsigned NOT NULL AUTO_INCREMENT,`idKanban` bigint(20) unsigned NOT NULL,`idUsuario` int(10) unsigned NOT NULL,`Descripcion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`Fecha` date NOT NULL,`Hora` time NOT NULL',
            'primaryKey' => 'idHistorial',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'kanban_tareas_participantes',
            'data'       => '`idParticipantes` bigint(20) unsigned NOT NULL AUTO_INCREMENT,`idKanban` bigint(20) unsigned NOT NULL,`idUsuario` int(10) unsigned NOT NULL',
            'primaryKey' => 'idParticipantes',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'kanban_tareas_tareas',
            'data'       => '`idTareas` bigint(20) unsigned NOT NULL AUTO_INCREMENT,`idKanban` bigint(20) unsigned NOT NULL,`Tarea` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`idEstadoTrabajo` int(10) unsigned NOT NULL,`idTrabajo` int(10) unsigned NULL DEFAULT NULL',
            'primaryKey' => 'idTareas',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'kanban_trabajos',
            'data'       => '`idTrabajo` int(10) unsigned NOT NULL AUTO_INCREMENT,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`idEstado` int(10) unsigned NOT NULL',
            'primaryKey' => 'idTrabajo',
            'comentario' => 'Creado desde el Instalador',
        ];

        /************************************/
        // Retorno True por defecto
        return $arrTables;

    }

    /*******************************************************************/
    // Mapeo de las tablas
    /*******************************************************************/
    public function mapTables(){

        $Data = '
        usuarios_listado(idUsuario,idCiudad,idComuna,idTipoUsuario,Nombre)
        core_tipos_usuario(idTipoUsuario,Nombre)
        core_prioridades(idPrioridad,Nombre)
        core_estados_cierre(idEstadoCierre,Nombre)
        core_estados_trabajos(idEstadoTrabajo,Nombre)
        core_estados(idEstado,Nombre)
        ';

        /************************************/
        // Variables
        $arrTables = $this->listTables();
        $dataSQL   = new FunctionsDataSQL();
        $Data     .= $dataSQL->minifyArrayTables($arrTables);

        /************************************/
        // Retorno True por defecto
        return $Data;

    }



}
