<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class tercerosEntidadesInstaller extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'tercerosEntidadesInstaller';
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                               INSTALACION                                  */
    /******************************************************************************/
    /******************************************************************************/
    //Instalacion del modulo completo
    public function ListDataModule(){

        /*******************************************************/
        //Se instancian otros controladores
        $entidadesInstaller = new entidadesInstaller();
        $serviciosInstaller = new serviciosInstaller();
        $maquinasInstaller  = new maquinasInstaller();

        /*******************************************************/
        //Rutas
        $nData1    = $this->GetCountDataModule();
        $DepData1  = $entidadesInstaller->GetCountDataModule();
        $DepData2  = $serviciosInstaller->GetCountDataModule();
        $DepData3  = $maquinasInstaller->GetCountDataModule();

        /******************************************/
        //si es la respuesta esperada
        $countPermisos = is_numeric($nData1) ? 1 : 0;
        $DepInstall_1  = is_numeric($DepData1) ? 1 : 0;
        $DepInstall_2  = is_numeric($DepData2) ? 1 : 0;
        $DepInstall_3  = is_numeric($DepData3) ? 1 : 0;

        /******************************************/
        //Verificar que existan los permisos
        $arrData = [
            'Nombre'        => 'Módulo de Gestión de Clientes - Opciones Extras',
            'Descripcion'   => 'Módulo para gestionar a las Clientes - Opciones Extras',
            'Controller'    => $this->controllerName,
            'countPermisos' => $countPermisos,
            'Dependencias'  => [
                [
                    'Nombre' => ' - Módulo de Gestión de Entidades instalado',
                    'Numero' => $DepInstall_1,
                ],
                [
                    'Nombre' => ' - Módulo de Gestión de Servicios instalado',
                    'Numero' => $DepInstall_2,
                ],
                [
                    'Nombre' => ' - Módulo de Gestión de Maquinas instalado',
                    'Numero' => $DepInstall_3,
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
        $arrTables    = array();
        $arrPermisos  = array();

        /*******************************************************/
        /*                 SE GENERAN LAS TABLAS               */
        /*******************************************************/
        $arrTables[] = [
            'table'      => 'terceros_entidades_listado',
            'data'       => '`idTerceros` int UNSIGNED NOT NULL AUTO_INCREMENT,`idEntidad` int UNSIGNED NOT NULL',
            'primaryKey' => 'idTerceros',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'terceros_entidades_listado_maquinas',
            'data'       => '`idMaq` int UNSIGNED NOT NULL AUTO_INCREMENT,`idEntidad` int UNSIGNED NOT NULL,`idMaquina` int UNSIGNED NOT NULL,`idEstado` int UNSIGNED NOT NULL,`idUsuario` int UNSIGNED NOT NULL,`Fecha` date NULL DEFAULT NULL,`Observacion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL',
            'primaryKey' => 'idMaq',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'terceros_entidades_listado_planes',
            'data'       => '`idPlan` int UNSIGNED NOT NULL AUTO_INCREMENT,`idEntidad` int UNSIGNED NOT NULL,`idServicio` int UNSIGNED NOT NULL,`idEstado` int UNSIGNED NOT NULL,`idUsuario` int UNSIGNED NOT NULL,`Fecha` date NULL DEFAULT NULL,`Monto` decimal(10, 2) UNSIGNED NOT NULL,`Observacion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL',
            'primaryKey' => 'idPlan',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'terceros_entidades_listado_usuarios',
            'data'       => '`idUsuario` int UNSIGNED NOT NULL AUTO_INCREMENT,`idEntidad` int UNSIGNED NOT NULL,`password` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`idTipoUsuario` int UNSIGNED NOT NULL,`idEstado` int UNSIGNED NOT NULL,`email` varchar(60) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`Nombre` varchar(60) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`Rut` varchar(13) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Fono` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Direccion_img` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Ultimo_acceso` date NULL DEFAULT NULL,`IP_Client` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Agent_Transp` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL',
            'primaryKey' => 'idUsuario',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'terceros_entidades_listado_usuarios_maq',
            'data'       => '`idPermiso` int UNSIGNED NOT NULL AUTO_INCREMENT,`idUsuario` int UNSIGNED NOT NULL,`idMaquina` int UNSIGNED NOT NULL',
            'primaryKey' => 'idPermiso',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'terceros_entidades_listado_usuarios_noti',
            'data'       => '`idPermiso` int UNSIGNED NOT NULL AUTO_INCREMENT,`idUsuario` int UNSIGNED NOT NULL,`idTipoNoti` int UNSIGNED NOT NULL',
            'primaryKey' => 'idPermiso',
            'comentario' => 'Creado desde el Instalador',
        ];

        /************************************************/
        /************************************************/
        //Verifico si existe
        if($arrTables){
            //recorro
            foreach ($arrTables as $table) {
                /******************************/
                //Se genera la query
                $xParams = ['query' => $table];
                $this->Base_createTable($xParams);
            }
        }

        /*******************************************************/
        /*                 SE GENERAN LAS RUTAS                */
        /*******************************************************/
        $arrPermisos[] = [
            'idPermisosCat'  => '6',
            'idEstado'       => '1',
            'idTipo'         => '2',
            'Nombre'         => 'Clientes - Opciones Extras',
            'Descripcion'    => 'Permite administrar las opciones extras de los clientes',
            'idLevelLimit'   => '4',
            'RutaWeb'        => 'serviciosTerceros/entidades/listado',
            'RutaController' => 'tercerosEntidadesListado',
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
                $arrRutas = $this->listRouteModule($IntCounter, $permisosID);
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
    //Instalacion del modulo completo
    public function UninstallModule(){

        /*******************************************************/
        //Rutas para borrar
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
        $subQuery = !empty($arrPermisos)
                    ? ',' . implode(',', array_column($arrPermisos, 'idPermisos'))
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
        $arrTableDel[] = ['table' => 'terceros_entidades_listado_maquinas'];
        $arrTableDel[] = ['table' => 'terceros_entidades_listado_planes'];
        $arrTableDel[] = ['table' => 'terceros_entidades_listado_usuarios'];
        $arrTableDel[] = ['table' => 'terceros_entidades_listado_usuarios_maq'];
        $arrTableDel[] = ['table' => 'terceros_entidades_listado_usuarios_noti'];

        /************************************************/
        //Verifico si existe
        if($arrTableDel){
            //recorro
            foreach ($arrTableDel as $tblDel) {
                //Se ejecuta la query
                $xParams = ['query' => $tblDel];
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
        return $nData;

    }
    /******************************************************************************/
    //Se cuentan las rutas del controlador
    public function listRouteModule($Type, $permisosID){

        /******************************************/
        //Variables
        $arrRutas  = array();

        /******************************************/
        //Variables
        switch ($Type) {
            /******************************************/
            case 1:
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/listAll',                                         'RutaController' => 'tercerosEntidadesListado->listAll',                  'Descripcion' => 'Listar Toda la Información',                     'idLevelLimit' => 1, 'Controller' => 'tercerosEntidadesListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'serviciosTerceros/entidades/listado/search',                                          'RutaController' => 'tercerosEntidadesListado->UpdateList',               'Descripcion' => 'Filtrar datos',                                  'idLevelLimit' => 1, 'Controller' => 'tercerosEntidadesListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/updateList',                                      'RutaController' => 'tercerosEntidadesListado->UpdateList',               'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/view/@id',                                        'RutaController' => 'tercerosEntidadesListado->View',                     'Descripcion' => 'Mostrar Detallado',                              'idLevelLimit' => 1, 'Controller' => 'tercerosEntidadesListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/resumen/@id',                                     'RutaController' => 'tercerosEntidadesListado->Resumen',                  'Descripcion' => 'Mostrar Resúmen',                                'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/resumenUpdate/@id',                               'RutaController' => 'tercerosEntidadesListado->ResumenUpdate',            'Descripcion' => 'Mostrar información',                            'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'serviciosTerceros/entidades/listado/update',                                          'RutaController' => 'tercerosEntidadesListado->Update',                   'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/planes/new/@id',                                  'RutaController' => 'tercerosEntidadesListadoPlanes->New',                'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoPlanes'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/planes/updateList/@id',                           'RutaController' => 'tercerosEntidadesListadoPlanes->UpdateList',         'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoPlanes'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/planes/view/@id',                                 'RutaController' => 'tercerosEntidadesListadoPlanes->View',               'Descripcion' => 'Mostrar Detallado',                              'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoPlanes'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/planes/getID/@id',                                'RutaController' => 'tercerosEntidadesListadoPlanes->GetID',              'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoPlanes'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'serviciosTerceros/entidades/listado/planes',                                          'RutaController' => 'tercerosEntidadesListadoPlanes->Insert',             'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoPlanes'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'serviciosTerceros/entidades/listado/planes/update',                                   'RutaController' => 'tercerosEntidadesListadoPlanes->Update',             'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoPlanes'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'serviciosTerceros/entidades/listado/planes',                                          'RutaController' => 'tercerosEntidadesListadoPlanes->Delete',             'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoPlanes'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/usuarios/new/@id',                                'RutaController' => 'tercerosEntidadesListadoUsuarios->New',              'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoUsuarios'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/usuarios/updateList/@id',                         'RutaController' => 'tercerosEntidadesListadoUsuarios->UpdateList',       'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoUsuarios'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/usuarios/view/@id',                               'RutaController' => 'tercerosEntidadesListadoUsuarios->View',             'Descripcion' => 'Mostrar Detallado',                              'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoUsuarios'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/usuarios/getID/@id',                              'RutaController' => 'tercerosEntidadesListadoUsuarios->GetID',            'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoUsuarios'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'serviciosTerceros/entidades/listado/usuarios',                                        'RutaController' => 'tercerosEntidadesListadoUsuarios->Insert',           'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoUsuarios'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'serviciosTerceros/entidades/listado/usuarios/update',                                 'RutaController' => 'tercerosEntidadesListadoUsuarios->Update',           'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoUsuarios'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'serviciosTerceros/entidades/listado/usuarios',                                        'RutaController' => 'tercerosEntidadesListadoUsuarios->Delete',           'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoUsuarios'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/maquinas/new/@id',                                'RutaController' => 'tercerosEntidadesListadoMaquinas->New',              'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoMaquinas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/maquinas/updateList/@id',                         'RutaController' => 'tercerosEntidadesListadoMaquinas->UpdateList',       'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoMaquinas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/maquinas/view/@id',                               'RutaController' => 'tercerosEntidadesListadoMaquinas->View',             'Descripcion' => 'Mostrar Detallado',                              'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoMaquinas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/maquinas/getID/@id',                              'RutaController' => 'tercerosEntidadesListadoMaquinas->GetID',            'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoMaquinas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'serviciosTerceros/entidades/listado/maquinas',                                        'RutaController' => 'tercerosEntidadesListadoMaquinas->Insert',           'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoMaquinas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'serviciosTerceros/entidades/listado/maquinas/update',                                 'RutaController' => 'tercerosEntidadesListadoMaquinas->Update',           'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoMaquinas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'serviciosTerceros/entidades/listado/maquinas',                                        'RutaController' => 'tercerosEntidadesListadoMaquinas->Delete',           'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoMaquinas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/usuariosMaq/updateList/@idEntidad/@idUsuario',    'RutaController' => 'tercerosEntidadesListadoUsuariosMaq->UpdateList',    'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoUsuariosMaq'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'serviciosTerceros/entidades/listado/usuariosMaq/update',                              'RutaController' => 'tercerosEntidadesListadoUsuariosMaq->Update',        'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoUsuariosMaq'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/usuariosNoti/updateList/@idEntidad/@idUsuario',   'RutaController' => 'tercerosEntidadesListadoUsuariosNoti->UpdateList',   'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoUsuariosNoti'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'serviciosTerceros/entidades/listado/usuariosNoti/update',                             'RutaController' => 'tercerosEntidadesListadoUsuariosNoti->Update',       'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoUsuariosNoti'];

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
        $RutaController  = '"tercerosEntidadesListado"';
        $RutaController .= ',"tercerosEntidadesListadoPlanes"';
        $RutaController .= ',"tercerosEntidadesListadoUsuarios"';
        $RutaController .= ',"tercerosEntidadesListadoMaquinas"';
        $RutaController .= ',"tercerosEntidadesListadoUsuariosMaq"';
        $RutaController .= ',"tercerosEntidadesListadoUsuariosNoti"';

        /******************************************/
        //devuelvo
        return $RutaController;
    }

}
