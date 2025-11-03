<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class bodegasInstaller extends ControllerBase {

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
        $this->controllerName     = 'bodegasInstaller';
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
        //Se instancian los datos
        $usuariosInstaller  = new usuariosInstaller();
		$productosInstaller = new productosInstaller();

        /*******************************************************/
        //Rutas
        $nData1    = $this->GetCountDataModule();
        $DepData1  = $usuariosInstaller->GetCountDataModule();
        $DepData2  = $productosInstaller->GetCountDataModule();

        /******************************************/
        //si es la respuesta esperada
        $countPermisos = is_numeric($nData1) ? 1 : 0;
        $DepInstall_1  = is_numeric($DepData1) ? 1 : 0;
        $DepInstall_2  = is_numeric($DepData2) ? 1 : 0;

        /******************************************/
        //Verificar que existan los permisos
        $arrData = [
            'Nombre'        => 'Módulo de Gestión de Bodegas',
            'Descripcion'   => 'Módulo para gestionar a las Bodegas',
            'Controller'    => $this->controllerName,
            'countPermisos' => $countPermisos,
            'Dependencias'  => [
                [
                    'Nombre' => ' - Módulo de Administracion de Usuarios instalado',
                    'Numero' => $DepInstall_1,
                ],
                [
                    'Nombre' => ' - Módulo de Gestión de Productos instalado',
                    'Numero' => $DepInstall_2,
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
            'table'      => 'bodegas_listado',
            'data'       => '`idBodegas` int UNSIGNED NOT NULL AUTO_INCREMENT,`idEstado` int UNSIGNED NOT NULL,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`idCiudad` int UNSIGNED NULL DEFAULT NULL,`idComuna` int UNSIGNED NULL DEFAULT NULL,`Direccion` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Direccion_img` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL',
            'primaryKey' => 'idBodegas',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'bodegas_listado_observaciones',
            'data'       => '`idObservaciones` int UNSIGNED NOT NULL AUTO_INCREMENT,`idBodegas` int UNSIGNED NOT NULL,`Observacion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`FechaCreacion` date NOT NULL',
            'primaryKey' => 'idObservaciones',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'bodegas_movimientos',
            'data'       => '`idMovimiento` bigint UNSIGNED NOT NULL AUTO_INCREMENT,`idEstadoIngreso` int UNSIGNED NOT NULL,`idBodegasIngreso` int UNSIGNED NULL DEFAULT NULL,`idBodegasEgreso` int UNSIGNED NULL DEFAULT NULL,`Creacion_fecha` date NOT NULL,`Creacion_hora` time NOT NULL,`Observaciones` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,`fecha_auto` date NOT NULL,`idUsuario` int UNSIGNED NOT NULL,`idFacturacion` bigint UNSIGNED NULL DEFAULT NULL',
            'primaryKey' => 'idMovimiento',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'bodegas_movimientos_productos',
            'data'       => '`idExistencia` bigint UNSIGNED NOT NULL AUTO_INCREMENT,`idMovimiento` bigint UNSIGNED NOT NULL,`idEstadoIngreso` int UNSIGNED NOT NULL,`idBodegas` int UNSIGNED NOT NULL,`idProducto` int UNSIGNED NOT NULL,`Number` decimal(10, 2) UNSIGNED NOT NULL',
            'primaryKey' => 'idExistencia',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'bodegas_productos_stocks',
            'data'       => '`idStocks` bigint UNSIGNED NOT NULL AUTO_INCREMENT,`idProducto` int UNSIGNED NOT NULL,`Cantidad_idBodegas_1` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_2` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_3` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_4` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_5` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_6` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_7` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_8` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_9` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_10` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_11` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_12` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_13` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_14` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_15` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_16` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_17` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_18` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_19` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_20` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_21` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_22` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_23` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_24` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_25` decimal(10, 2) NULL DEFAULT NULL',
            'primaryKey' => 'idStocks',
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
                $xParams  = ['query' => $table];
                $this->Base_createTable($xParams);
            }
        }

        /*******************************************************/
        /*                 SE GENERAN LAS RUTAS                */
        /*******************************************************/
        $arrPermisos[] = [
            'idPermisosCat'  => '1',
            'idEstado'       => '1',
            'idTipo'         => '2',
            'Nombre'         => 'Bodegas - Listado',
            'Descripcion'    => 'Permite administrar las bodegas',
            'idLevelLimit'   => '4',
            'RutaWeb'        => 'administracion/bodegas/listado',
            'RutaController' => 'bodegasListado',
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '3',
            'idEstado'       => '1',
            'idTipo'         => '2',
            'Nombre'         => 'Movimientos Bodegas - Ingresos',
            'Descripcion'    => 'Permite el ingreso de productos a bodega',
            'idLevelLimit'   => '4',
            'RutaWeb'        => 'gestionBodegas/ingresos/listado',
            'RutaController' => 'bodegasMovimientoIngreso',
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '3',
            'idEstado'       => '1',
            'idTipo'         => '2',
            'Nombre'         => 'Movimientos Bodegas - Egresos',
            'Descripcion'    => 'Permite el egreso de productos a bodega',
            'idLevelLimit'   => '4',
            'RutaWeb'        => 'gestionBodegas/egresos/listado',
            'RutaController' => 'bodegasMovimientoEgreso',
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '3',
            'idEstado'       => '1',
            'idTipo'         => '2',
            'Nombre'         => 'Movimientos Bodegas - Traspasos',
            'Descripcion'    => 'Permite el traspaso de productos entre bodegas',
            'idLevelLimit'   => '4',
            'RutaWeb'        => 'gestionBodegas/traspaso/listado',
            'RutaController' => 'bodegasMovimientoTraspaso',
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '3',
            'idEstado'       => '1',
            'idTipo'         => '3',
            'Nombre'         => 'Stock Productos',
            'Descripcion'    => 'Permite ver el stock actual de los productos en las bodegas',
            'idLevelLimit'   => '1',
            'RutaWeb'        => 'gestionBodegas/productos/listado',
            'RutaController' => 'informeProductos',
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
                        $xParams  = ['DataCheck' => '', 'query' => $query, 'novalidate' => true];
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
        $arrTableDel[] = ['table' => 'bodegas_listado'];
        $arrTableDel[] = ['table' => 'bodegas_listado_observaciones'];
        $arrTableDel[] = ['table' => 'bodegas_movimientos'];
        $arrTableDel[] = ['table' => 'bodegas_movimientos_productos'];
        $arrTableDel[] = ['table' => 'bodegas_productos_stocks'];

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
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/bodegas/listado/listAll',                        'RutaController' => 'bodegasListado->listAll',                   'Descripcion' => 'Listar Toda la Información',                    'idLevelLimit' => 1, 'Controller' => 'bodegasListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/bodegas/listado/search',                         'RutaController' => 'bodegasListado->UpdateList',                'Descripcion' => 'Filtrar datos',                                 'idLevelLimit' => 1, 'Controller' => 'bodegasListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/bodegas/listado/updateList',                     'RutaController' => 'bodegasListado->UpdateList',                'Descripcion' => 'Actualizar Lista',                              'idLevelLimit' => 2, 'Controller' => 'bodegasListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/bodegas/listado/view/@id',                       'RutaController' => 'bodegasListado->View',                      'Descripcion' => 'Mostrar Detallado',                             'idLevelLimit' => 1, 'Controller' => 'bodegasListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/bodegas/listado/resumen/@id',                    'RutaController' => 'bodegasListado->Resumen',                   'Descripcion' => 'Mostrar Resúmen',                               'idLevelLimit' => 2, 'Controller' => 'bodegasListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/bodegas/listado/resumenUpdate/@id',              'RutaController' => 'bodegasListado->ResumenUpdate',             'Descripcion' => 'Mostrar información',                           'idLevelLimit' => 2, 'Controller' => 'bodegasListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/bodegas/listado',                                'RutaController' => 'bodegasListado->Insert',                    'Descripcion' => 'Crear Información',                             'idLevelLimit' => 3, 'Controller' => 'bodegasListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/bodegas/listado/update',                         'RutaController' => 'bodegasListado->Update',                    'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'bodegasListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 4, 'RutaWeb' => 'administracion/bodegas/listado/delFiles',                       'RutaController' => 'bodegasListado->DelFiles',                  'Descripcion' => 'Permite eliminar archivos',                     'idLevelLimit' => 2, 'Controller' => 'bodegasListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'administracion/bodegas/listado',                                'RutaController' => 'bodegasListado->Delete',                    'Descripcion' => 'Borrar dato y archivos',                        'idLevelLimit' => 4, 'Controller' => 'bodegasListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/bodegas/listado/observaciones/new/@id',          'RutaController' => 'bodegasListadoObservaciones->New',          'Descripcion' => 'Mostrar modal nuevo',                           'idLevelLimit' => 2, 'Controller' => 'bodegasListadoObservaciones'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/bodegas/listado/observaciones/updateList/@id',   'RutaController' => 'bodegasListadoObservaciones->UpdateList',   'Descripcion' => 'Actualizar Lista',                              'idLevelLimit' => 2, 'Controller' => 'bodegasListadoObservaciones'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/bodegas/listado/observaciones/view/@id',         'RutaController' => 'bodegasListadoObservaciones->View',         'Descripcion' => 'Mostrar Detallado',                             'idLevelLimit' => 2, 'Controller' => 'bodegasListadoObservaciones'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/bodegas/listado/observaciones/getID/@id',        'RutaController' => 'bodegasListadoObservaciones->GetID',        'Descripcion' => 'Información para el formulario edición',        'idLevelLimit' => 2, 'Controller' => 'bodegasListadoObservaciones'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/bodegas/listado/observaciones',                  'RutaController' => 'bodegasListadoObservaciones->Insert',       'Descripcion' => 'Crear Información',                             'idLevelLimit' => 2, 'Controller' => 'bodegasListadoObservaciones'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/bodegas/listado/observaciones/update',           'RutaController' => 'bodegasListadoObservaciones->Update',       'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'bodegasListadoObservaciones'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'administracion/bodegas/listado/observaciones',                  'RutaController' => 'bodegasListadoObservaciones->Delete',       'Descripcion' => 'Borrar dato y archivos',                        'idLevelLimit' => 2, 'Controller' => 'bodegasListadoObservaciones'];

                break;
            /******************************************/
            case 2:
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/listAll',                    'RutaController' => 'bodegasMovimiento->listAll_1',               'Descripcion' => 'Listar Toda la Información',                    'idLevelLimit' => 1, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/search',                     'RutaController' => 'bodegasMovimiento->UpdateList_1',            'Descripcion' => 'Filtrar datos',                                 'idLevelLimit' => 1, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/updateList',                 'RutaController' => 'bodegasMovimiento->UpdateList_1',            'Descripcion' => 'Actualizar Lista',                              'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/view/@id',                   'RutaController' => 'bodegasMovimiento->View_1',                  'Descripcion' => 'Mostrar Detallado',                             'idLevelLimit' => 1, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/resumen/@id',                'RutaController' => 'bodegasMovimiento->Resumen_1',               'Descripcion' => 'Mostrar Resúmen',                               'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/resumenUpdate/@id',          'RutaController' => 'bodegasMovimiento->ResumenUpdate_1',         'Descripcion' => 'Mostrar información',                           'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/ingresos/listado',                            'RutaController' => 'bodegasMovimiento->Insert',                  'Descripcion' => 'Crear Información',                             'idLevelLimit' => 3, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/update',                     'RutaController' => 'bodegasMovimiento->Update',                  'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 4, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/delFiles',                   'RutaController' => 'bodegasMovimiento->DelFiles',                'Descripcion' => 'Permite eliminar archivos',                     'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' =>  'gestionBodegas/ingresos/listado',                            'RutaController' => 'bodegasMovimiento->Delete',                  'Descripcion' => 'Borrar dato y archivos',                        'idLevelLimit' => 4, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/productos/new/@id',          'RutaController' => 'bodegasMovimientoProductos->New_1',          'Descripcion' => 'Mostrar modal nuevo',                           'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/productos/updateList/@id',   'RutaController' => 'bodegasMovimientoProductos->UpdateList_1',   'Descripcion' => 'Actualizar Lista',                              'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/productos/getID/@id',        'RutaController' => 'bodegasMovimientoProductos->GetID_1',        'Descripcion' => 'Información para el formulario edición',        'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/productos',                  'RutaController' => 'bodegasMovimientoProductos->Insert',         'Descripcion' => 'Crear Información',                             'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/productos/update',           'RutaController' => 'bodegasMovimientoProductos->Update',         'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/productos',                  'RutaController' => 'bodegasMovimientoProductos->Delete',         'Descripcion' => 'Borrar dato y archivos',                        'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'];

                break;
            /******************************************/
            case 3:
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/egresos/listado/listAll',                     'RutaController' => 'bodegasMovimiento->listAll_2',              'Descripcion' => 'Listar Toda la Información',                     'idLevelLimit' => 1, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/egresos/listado/search',                      'RutaController' => 'bodegasMovimiento->UpdateList_2',           'Descripcion' => 'Filtrar datos',                                  'idLevelLimit' => 1, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/egresos/listado/updateList',                  'RutaController' => 'bodegasMovimiento->UpdateList_2',           'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/egresos/listado/view/@id',                    'RutaController' => 'bodegasMovimiento->View_2',                 'Descripcion' => 'Mostrar Detallado',                              'idLevelLimit' => 1, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/egresos/listado/resumen/@id',                 'RutaController' => 'bodegasMovimiento->Resumen_2',              'Descripcion' => 'Mostrar Resúmen',                                'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/egresos/listado/resumenUpdate/@id',           'RutaController' => 'bodegasMovimiento->ResumenUpdate_2',        'Descripcion' => 'Mostrar información',                            'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/egresos/listado',                             'RutaController' => 'bodegasMovimiento->Insert',                 'Descripcion' => 'Crear Información',                              'idLevelLimit' => 3, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/egresos/listado/update',                      'RutaController' => 'bodegasMovimiento->Update',                 'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 4, 'RutaWeb' =>  'gestionBodegas/egresos/listado/delFiles',                    'RutaController' => 'bodegasMovimiento->DelFiles',               'Descripcion' => 'Permite eliminar archivos',                      'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' =>  'gestionBodegas/egresos/listado',                             'RutaController' => 'bodegasMovimiento->Delete',                 'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 4, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/egresos/listado/productos/new/@id',           'RutaController' => 'bodegasMovimientoProductos->New_2',         'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/egresos/listado/productos/updateList/@id',    'RutaController' => 'bodegasMovimientoProductos->UpdateList_2',  'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/egresos/listado/productos/getID/@id',         'RutaController' => 'bodegasMovimientoProductos->GetID_2',       'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/egresos/listado/productos',                   'RutaController' => 'bodegasMovimientoProductos->Insert',        'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/egresos/listado/productos/update',            'RutaController' => 'bodegasMovimientoProductos->Update',        'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' =>  'gestionBodegas/egresos/listado/productos',                   'RutaController' => 'bodegasMovimientoProductos->Delete',        'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'];

                break;
            /******************************************/
            case 4:
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/listAll',                    'RutaController' => 'bodegasMovimiento->listAll_3',               'Descripcion' => 'Listar Toda la Información',                     'idLevelLimit' => 1, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/search',                     'RutaController' => 'bodegasMovimiento->UpdateList_3',            'Descripcion' => 'Filtrar datos',                                  'idLevelLimit' => 1, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/updateList',                 'RutaController' => 'bodegasMovimiento->UpdateList_3',            'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/view/@id',                   'RutaController' => 'bodegasMovimiento->View_3',                  'Descripcion' => 'Mostrar Detallado',                              'idLevelLimit' => 1, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/resumen/@id',                'RutaController' => 'bodegasMovimiento->Resumen_3',               'Descripcion' => 'Mostrar Resúmen',                                'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/resumenUpdate/@id',          'RutaController' => 'bodegasMovimiento->ResumenUpdate_3',         'Descripcion' => 'Mostrar información',                            'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/traspaso/listado',                            'RutaController' => 'bodegasMovimiento->Insert',                  'Descripcion' => 'Crear Información',                              'idLevelLimit' => 3, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/update',                     'RutaController' => 'bodegasMovimiento->Update',                  'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 4, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/delFiles',                   'RutaController' => 'bodegasMovimiento->DelFiles',                'Descripcion' => 'Permite eliminar archivos',                      'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' =>  'gestionBodegas/traspaso/listado',                            'RutaController' => 'bodegasMovimiento->Delete',                  'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 4, 'Controller' => 'bodegasMovimiento'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/productos/new/@id',          'RutaController' => 'bodegasMovimientoProductos->New_3',          'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/productos/updateList/@id',   'RutaController' => 'bodegasMovimientoProductos->UpdateList_3',   'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/productos/getID/@id',        'RutaController' => 'bodegasMovimientoProductos->GetID_3',        'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/productos',                  'RutaController' => 'bodegasMovimientoProductos->Insert',         'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/productos/update',           'RutaController' => 'bodegasMovimientoProductos->Update',         'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/productos',                  'RutaController' => 'bodegasMovimientoProductos->Delete',         'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'];

                break;
            /******************************************/
            case 5:
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/productos/listado/listAll',                      'RutaController' => 'informeProductos->listAll',      'Descripcion' => 'Filtro de búsqueda',  'idLevelLimit' => 1, 'Controller' => 'informeProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/productos/listado/search',                       'RutaController' => 'informeProductos->UpdateList',   'Descripcion' => 'Filtrar datos',       'idLevelLimit' => 1, 'Controller' => 'informeProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/productos/listado/view/@idProducto/@idBodegas',  'RutaController' => 'informeProductos->View',         'Descripcion' => 'Mostrar Detallado',   'idLevelLimit' => 1, 'Controller' => 'informeProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/productos/listado/print/@id',                    'RutaController' => 'informeProductos->Print',        'Descripcion' => 'Pantalla imprimir',   'idLevelLimit' => 1, 'Controller' => 'informeProductos'];

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
        $RutaController  = '"bodegasListado"';
        $RutaController .= ',"bodegasListadoObservaciones"';
        $RutaController .= ',"bodegasMovimientoIngreso"';
        $RutaController .= ',"bodegasMovimientoEgreso"';
        $RutaController .= ',"bodegasMovimientoTraspaso"';
        $RutaController .= ',"informeProductos"';

        //devuelvo
        return $RutaController;
    }

}
