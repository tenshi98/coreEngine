<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class kanbanTareasInstaller extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_ADMIN);
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
    /******************************************************************************/
    //Se lista la informacion
    public function ListDataModule(){

        /*******************************************************/
        //Se instancian los datos
        $usuariosInstaller = new usuariosInstaller();

        /*******************************************************/
        //Rutas
        $nData1    = $this->GetCountDataModule();
        $DepData1  = $usuariosInstaller->GetCountDataModule();

        /******************************************/
        //si es la respuesta esperada
        $countPermisos = is_numeric($nData1)&&$nData1!=0 ? 1 : 0;
        $DepInstall_1  = is_numeric($DepData1)&&$DepData1!=0 ? 1 : 0;

        /******************************************/
        //Verificar que existan los permisos
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
        //devuelvo
        return $arrData;
    }
    /******************************************************************************/
    //Instalacion del modulo completo
    public function InstallModule(){

        /******************************************/
        //Variables
        $arrTables    = $this->listTables();
        $arrPermisos  = array();

        /************************************************/
        /************************************************/
        //Verifico si existe
        if($arrTables){
            //recorro
            foreach ($arrTables as $table) {
                /******************************/
                //Se genera la query
                $xParams  = ['query' => $table];
                $this->Base_createTable($xParams);
            }
        }

        /*******************************************************/
        /*                 SE GENERAN LAS RUTAS                */
        /*******************************************************/
        $arrPermisos[] = [
            'idPermisosCat'  => '2',
            'idEstado'       => '1',
            'idTipo'         => '1',
            'Nombre'         => 'Tareas en Curso',
            'Descripcion'    => 'Listado de tareas gestionadas',
            'idLevelLimit'   => '4',
            'RutaWeb'        => 'gestionProyectos/kanban/tareas',
            'RutaController' => 'kanbanTareas',
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '2',
            'idEstado'       => '1',
            'idTipo'         => '3',
            'Nombre'         => 'Informe Tareas',
            'Descripcion'    => 'Informe de las tareas del panel',
            'idLevelLimit'   => '1',
            'RutaWeb'        => 'gestionProyectos/kanban/informeTareas',
            'RutaController' => 'informeTareas',
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '1',
            'idEstado'       => '1',
            'idTipo'         => '1',
            'Nombre'         => 'Gestion Proyectos - Tableros',
            'Descripcion'    => 'Listado de tableros kanban',
            'idLevelLimit'   => '4',
            'RutaWeb'        => 'administracion/kanban/tableros',
            'RutaController' => 'kanbanTableros',
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '1',
            'idEstado'       => '1',
            'idTipo'         => '1',
            'Nombre'         => 'Gestion Proyectos - Tareas',
            'Descripcion'    => 'Permite crear los trabajos a hacer dentro de las tareas',
            'idLevelLimit'   => '4',
            'RutaWeb'        => 'administracion/kanban/trabajos',
            'RutaController' => 'kanbanTrabajos',
        ];
        /************************************************/
        /************************************************/
        //Verifico si existe
        if($arrPermisos){
            //Variable
            $IntCounter = 1;
            //recorro
            foreach ($arrPermisos as $permiso) {
                /************************************************/
                //Se genera la query
                $query = [
                    'data'      => 'idPermisosCat,idEstado,idTipo,Nombre,Descripcion,idLevelLimit,RutaWeb,RutaController',
                    'required'  => 'idPermisosCat,idEstado,idTipo,Nombre,Descripcion,idLevelLimit,RutaWeb,RutaController',
                    'unique'    => '',
                    'encode'    => '',
                    'table'     => 'core_permisos_listado',
                    'Post'      => $permiso
                ];
                //Ejecuto la query
                $xParams    = ['DataCheck' => '', 'query' => $query];
                $permisosID = $this->Base_insert($xParams);
                /************************************************/
                //Listar las rutas
                $arrRutas = $this->listRouteModule($IntCounter, $permisosID['data']);
                /************************************************/
                //Verifico si existe
                if($arrRutas){
                    //recorro
                    foreach ($arrRutas as $rutas) {
                        /******************************/
                        //Se genera la query
                        $query = [
                            'data'      => 'idPermisos,idMetodo,RutaWeb,RutaController,Descripcion,idLevelLimit,Controller',
                            'required'  => 'idPermisos,idMetodo,RutaWeb,RutaController,Descripcion,idLevelLimit,Controller',
                            'unique'    => '',
                            'encode'    => '',
                            'table'     => 'core_permisos_listado_rutas',
                            'Post'      => $rutas
                        ];
                        //Ejecuto la query
                        //Ejecuto la query
                        $xParams = ['DataCheck' => '', 'query' => $query, 'novalidate' => true];
                        $this->Base_insert($xParams);
                    }
                }
                /************************************************/
                //Se aumenta
                $IntCounter++;
            }
        }

        /************************************************/
        //devuelvo true
        return true;

    }
    /******************************************************************************/
    //Desinstalacion del modulo
    public function UninstallModule(){

        /*******************************************************/
        //Rutas
        $RutaController  = $this->RutaController();

        /*******************************************************/
        /*             SE CONSULTAN LOS PERMISOS               */
        /*******************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idPermisos',
            'table'   => 'core_permisos_listado',
            'join'    => '',
            'where'   => 'RutaController IN ('.$RutaController.')',
            'group'   => '',
            'having'  => '',
            'order'   => 'idPermisos ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams     = ['query' => $query];
        $arrPermisos = $this->Base_GetList($xParams);

        /*******************************************************/
        /*        SE ELIMINAN PERMISOS DE LOS USUARIOS         */
        /*******************************************************/
        $subQuery = $arrPermisos['status']
                    ? ',' . implode(',', array_column($arrPermisos['data'], 'idPermisos'))
                    : '';

        /************************************************/
        //Se listan las query
        $arrPermDel   = array();
        $arrPermDel[] = 'DELETE FROM `usuarios_listado_permisos` WHERE idPermisos IN (0 '.$subQuery.')';
        $arrPermDel[] = 'DELETE FROM `core_permisos_listado` WHERE RutaController IN ('.$RutaController.')';
        $arrPermDel[] = 'DELETE FROM `core_permisos_listado_rutas` WHERE Controller IN ('.$RutaController.')';

        /************************************************/
        //Verifico si existe
        if($arrPermDel){
            //recorro
            foreach ($arrPermDel as $sql) {
                //Se ejecuta la query
                $xParams = ['query' => $sql];
                $this->Base_queryExecute($xParams);
            }
        }

        /*******************************************************/
        /*              SE ELIMINAN LAS TABLAS                 */
        /*******************************************************/
        $arrTableDel  = array();
        $arrTableDel[] = ['table' => 'kanban_estados'];
        $arrTableDel[] = ['table' => 'kanban_tareas'];
        $arrTableDel[] = ['table' => 'kanban_tareas_historial'];
        $arrTableDel[] = ['table' => 'kanban_tareas_participantes'];
        $arrTableDel[] = ['table' => 'kanban_tareas_tareas'];
        $arrTableDel[] = ['table' => 'kanban_trabajos'];

        /************************************************/
        //Verifico si existe
        if($arrTableDel){
            //recorro
            foreach ($arrTableDel as $tblDel) {
                //Se ejecuta la query
                $xParams  = ['query' => $tblDel];
                $this->Base_dropTable($xParams);
            }
        }

        /************************************************/
        /************************************************/
        //devuelvo true
        return true;

    }
    /******************************************************************************/
    //Se cuentan las rutas del controlador
    public function GetCountDataModule(){

        /*******************************************************/
        //Rutas
        $RutaController  = $this->RutaController();

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => 'idRutas',
            'table'   => 'core_permisos_listado_rutas',
            'join'    => '',
            'where'   => 'Controller IN ('.$RutaController.')',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $nData   = $this->Base_GetCountData($xParams);

        /******************************************/
        //devuelvo
        return $nData['data'];

    }
    /******************************************************************************/
    //Se listan las rutas
    public function listRouteModule($Type, $permisosID){

        /******************************************/
        //Variables
        $arrRutas  = array();

        /******************************************/
        //Variables
        switch ($Type) {
            /******************************************/
            case 1:
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionProyectos/kanban/tareas/listAll',                   'RutaController' => 'kanbanTareas->listAll',              'Descripcion' => 'Listar Toda la Información',                   'idLevelLimit' => 1, 'Controller' => 'kanbanTareas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionProyectos/kanban/tareas/search',                    'RutaController' => 'kanbanTareas->UpdateTableList',      'Descripcion' => 'Filtrar datos',                                'idLevelLimit' => 1, 'Controller' => 'kanbanTareas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionProyectos/kanban/tareas/updateList',                'RutaController' => 'kanbanTareas->UpdateList',           'Descripcion' => 'Actualizar Lista',                             'idLevelLimit' => 2, 'Controller' => 'kanbanTareas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionProyectos/kanban/tareas/view/@id',                  'RutaController' => 'kanbanTareas->View',                 'Descripcion' => 'Mostrar Detallado',                            'idLevelLimit' => 1, 'Controller' => 'kanbanTareas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionProyectos/kanban/tareas/getID/@id',                 'RutaController' => 'kanbanTareas->GetID',                'Descripcion' => 'Información para el formulario edición',       'idLevelLimit' => 2, 'Controller' => 'kanbanTareas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionProyectos/kanban/tareas',                           'RutaController' => 'kanbanTareas->Insert',               'Descripcion' => 'Crear Información',                            'idLevelLimit' => 3, 'Controller' => 'kanbanTareas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionProyectos/kanban/tareas/update',                    'RutaController' => 'kanbanTareas->Update',               'Descripcion' => 'Editar por post (modificar y subir archivos)', 'idLevelLimit' => 2, 'Controller' => 'kanbanTareas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'gestionProyectos/kanban/tareas',                           'RutaController' => 'kanbanTareas->Delete',               'Descripcion' => 'Borrar dato y archivos',                       'idLevelLimit' => 4, 'Controller' => 'kanbanTareas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionProyectos/kanban/tareas/changeStatus',              'RutaController' => 'kanbanTareas->ChangeStatus',         'Descripcion' => 'Actualiza el estado',                          'idLevelLimit' => 2, 'Controller' => 'kanbanTareas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionProyectos/kanban/tareas/print/@id',                 'RutaController' => 'kanbanTareas->Print',                'Descripcion' => 'Pantalla imprimir',                            'idLevelLimit' => 1, 'Controller' => 'kanbanTareas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionProyectos/kanban/estados/getID/@id',                'RutaController' => 'kanbanEstados->GetID',               'Descripcion' => 'Información para el formulario edición',       'idLevelLimit' => 2, 'Controller' => 'kanbanEstados'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionProyectos/kanban/estados',                          'RutaController' => 'kanbanEstados->Insert',              'Descripcion' => 'Crear Información',                            'idLevelLimit' => 3, 'Controller' => 'kanbanEstados'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionProyectos/kanban/estados/update',                   'RutaController' => 'kanbanEstados->Update',              'Descripcion' => 'Editar por post (modificar y subir archivos)', 'idLevelLimit' => 2, 'Controller' => 'kanbanEstados'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'gestionProyectos/kanban/estados',                          'RutaController' => 'kanbanEstados->Delete',              'Descripcion' => 'Borrar dato y archivos',                       'idLevelLimit' => 4, 'Controller' => 'kanbanEstados'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionProyectos/kanban/tareasTareas/newData/@id',         'RutaController' => 'kanbanTareasTareas->NewData',        'Descripcion' => 'Formulario Creación',                          'idLevelLimit' => 2, 'Controller' => 'kanbanTareasTareas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionProyectos/kanban/tareasTareas/getID/@id',           'RutaController' => 'kanbanTareasTareas->GetID',          'Descripcion' => 'Informacion para el formulario',               'idLevelLimit' => 2, 'Controller' => 'kanbanTareasTareas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionProyectos/kanban/tareasTareas',                     'RutaController' => 'kanbanTareasTareas->Insert',         'Descripcion' => 'Crear Información',                            'idLevelLimit' => 2, 'Controller' => 'kanbanTareasTareas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionProyectos/kanban/tareasTareas/update',              'RutaController' => 'kanbanTareasTareas->Update',         'Descripcion' => 'Editar por post (modificar y subir archivos)', 'idLevelLimit' => 2, 'Controller' => 'kanbanTareasTareas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionProyectos/kanban/tareasParticipantes/newData/@id',  'RutaController' => 'kanbanTareasParticipantes->NewData', 'Descripcion' => 'Formulario Creación',                          'idLevelLimit' => 2, 'Controller' => 'kanbanTareasParticipantes'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionProyectos/kanban/tareasParticipantes',              'RutaController' => 'kanbanTareasParticipantes->Insert',  'Descripcion' => 'Crear Información',                            'idLevelLimit' => 2, 'Controller' => 'kanbanTareasParticipantes'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'gestionProyectos/kanban/tareasParticipantes',              'RutaController' => 'kanbanTareasParticipantes->Delete',  'Descripcion' => 'Borrar dato y archivos',                       'idLevelLimit' => 2, 'Controller' => 'kanbanTareasParticipantes'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionProyectos/kanban/tareas/updateTableList',           'RutaController' => 'kanbanTareas->UpdateTableList',      'Descripcion' => 'Filtrar datos',                                'idLevelLimit' => 1, 'Controller' => 'kanbanTareas'];

                break;
            /******************************************/
            case 2:
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionProyectos/kanban/informeTareas/listAll',   'RutaController' => 'informeTareas->listAll',     'Descripcion' => 'Filtro de búsqueda', 'idLevelLimit' => 1, 'Controller' => 'informeTareas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionProyectos/kanban/informeTareas/search',    'RutaController' => 'informeTareas->UpdateList',  'Descripcion' => 'Filtrar datos',      'idLevelLimit' => 1, 'Controller' => 'informeTareas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionProyectos/kanban/informeTareas/view/@id',  'RutaController' => 'informeTareas->View',        'Descripcion' => 'Mostrar Detallado',  'idLevelLimit' => 1, 'Controller' => 'informeTareas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionProyectos/kanban/informeTareas/print/@id', 'RutaController' => 'informeTareas->Print',       'Descripcion' => 'Pantalla imprimir',  'idLevelLimit' => 1, 'Controller' => 'informeTareas'];

                break;
            /******************************************/
            case 3:
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/kanban/tableros/listAll',     'RutaController' => 'kanbanTableros->listAll',     'Descripcion' => 'Listar Toda la Información',                    'idLevelLimit' => 1, 'Controller' => 'kanbanTableros'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/kanban/tableros/search',      'RutaController' => 'kanbanTableros->UpdateList',  'Descripcion' => 'Filtrar datos',                                 'idLevelLimit' => 1, 'Controller' => 'kanbanTableros'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/kanban/tableros/updateList',  'RutaController' => 'kanbanTableros->UpdateList',  'Descripcion' => 'Actualizar Lista',                              'idLevelLimit' => 2, 'Controller' => 'kanbanTableros'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/kanban/tableros/view/@id',    'RutaController' => 'kanbanTableros->View',        'Descripcion' => 'Mostrar Detallado',                             'idLevelLimit' => 1, 'Controller' => 'kanbanTableros'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/kanban/tableros/getID/@id',   'RutaController' => 'kanbanTableros->GetID',       'Descripcion' => 'Información para el formulario edición',        'idLevelLimit' => 2, 'Controller' => 'kanbanTableros'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/kanban/tableros',             'RutaController' => 'kanbanTableros->Insert',      'Descripcion' => 'Crear Información',                             'idLevelLimit' => 3, 'Controller' => 'kanbanTableros'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/kanban/tableros/update',      'RutaController' => 'kanbanTableros->Update',      'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'kanbanTableros'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'administracion/kanban/tableros',             'RutaController' => 'kanbanTableros->Delete',      'Descripcion' => 'Borrar dato y archivos',                        'idLevelLimit' => 4, 'Controller' => 'kanbanTableros'];

                break;
            /******************************************/
            case 4:
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/kanban/trabajos/listAll',     'RutaController' => 'kanbanTrabajos->listAll',     'Descripcion' => 'Listar Toda la Información',                   'idLevelLimit' => 1, 'Controller' => 'kanbanTrabajos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/kanban/trabajos/search',      'RutaController' => 'kanbanTrabajos->UpdateList',  'Descripcion' => 'Filtrar datos',                                'idLevelLimit' => 1, 'Controller' => 'kanbanTrabajos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/kanban/trabajos/updateList',  'RutaController' => 'kanbanTrabajos->UpdateList',  'Descripcion' => 'Actualizar Lista',                             'idLevelLimit' => 2, 'Controller' => 'kanbanTrabajos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/kanban/trabajos/view/@id',    'RutaController' => 'kanbanTrabajos->View',        'Descripcion' => 'Mostrar Detallado',                            'idLevelLimit' => 1, 'Controller' => 'kanbanTrabajos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/kanban/trabajos/getID/@id',   'RutaController' => 'kanbanTrabajos->GetID',       'Descripcion' => 'Información para el formulario edición',       'idLevelLimit' => 2, 'Controller' => 'kanbanTrabajos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/kanban/trabajos',             'RutaController' => 'kanbanTrabajos->Insert',      'Descripcion' => 'Crear Información',                            'idLevelLimit' => 3, 'Controller' => 'kanbanTrabajos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/kanban/trabajos/update',      'RutaController' => 'kanbanTrabajos->Update',      'Descripcion' => 'Editar por post (modificar y subir archivos)', 'idLevelLimit' => 2, 'Controller' => 'kanbanTrabajos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'administracion/kanban/trabajos',             'RutaController' => 'kanbanTrabajos->Delete',      'Descripcion' => 'Borrar dato y archivos',                       'idLevelLimit' => 4, 'Controller' => 'kanbanTrabajos'];

                break;
        }

        /******************************************/
        //devuelvo
        return $arrRutas;

    }
    /******************************************************************************/
    //Se listan los controladores
    private function RutaController(){

        /*******************************************************/
        //Rutas
        $RutaController  = '"kanbanTareas"';
        $RutaController .= ',"kanbanEstados"';
        $RutaController .= ',"kanbanTareasTareas"';
        $RutaController .= ',"kanbanTareasParticipantes"';
        $RutaController .= ',"informeTareas"';
        $RutaController .= ',"kanbanTableros"';
        $RutaController .= ',"kanbanTrabajos"';

        //devuelvo
        return $RutaController;
    }
    /******************************************************************************/
    //Se listan las tablas
    public function listTables(){

        /******************************************/
        //Variables
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

        /************************************************/
        //devuelvo true
        return $arrTables;

    }
    /******************************************************************************/
    //Mapeo de las tablas
    public function mapTables(){

        $Data = '
        usuarios_listado(idUsuario,idCiudad,idComuna,idTipoUsuario,Nombre)
        core_tipos_usuario(idTipoUsuario,Nombre)
        core_prioridades(idPrioridad,Nombre)
        core_estados_cierre(idEstadoCierre,Nombre)
        core_estados_trabajos(idEstadoTrabajo,Nombre)
        core_estados(idEstado,Nombre)
        ';

        /******************************************/
        //Variables
        $arrTables = $this->listTables();
        $dataSQL   = new FunctionsDataSQL();
        $Data     .= $dataSQL->minifyArrayTables($arrTables);

        /************************************************/
        //devuelvo true
        return $Data;

    }

}
