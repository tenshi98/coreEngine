<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class gestionDocumentosInstaller extends ControllerBase {

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
        $this->controllerName     = 'gestionDocumentosInstaller';
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
		$entidadesInstaller = new entidadesInstaller();
		$productosInstaller = new productosInstaller();
		$serviciosInstaller = new serviciosInstaller();

        /*******************************************************/
        //Rutas
        $nData1    = $this->GetCountDataModule();
        $DepData1  = $usuariosInstaller->GetCountDataModule();
        $DepData2  = $entidadesInstaller->GetCountDataModule();
        $DepData3  = $productosInstaller->GetCountDataModule();
        $DepData4  = $serviciosInstaller->GetCountDataModule();

        /******************************************/
        //si es la respuesta esperada
        $countPermisos = is_numeric($nData1) ? 1 : 0;
        $DepInstall_1  = is_numeric($DepData1) ? 1 : 0;
        $DepInstall_2  = is_numeric($DepData2) ? 1 : 0;
        $DepInstall_3  = is_numeric($DepData3) ? 1 : 0;
        $DepInstall_4  = is_numeric($DepData4) ? 1 : 0;

        /******************************************/
        //Verificar que existan los permisos
        $arrData = [
            'Nombre'        => 'Módulo de Gestión de Documentos',
            'Descripcion'   => 'Módulo para gestionar las compras y ventas',
            'Controller'    => $this->controllerName,
            'countPermisos' => $countPermisos,
            'Dependencias'  => [
                [
                    'Nombre' => ' - Módulo de Administracion de Usuarios instalado',
                    'Numero' => $DepInstall_1,
                ],
                [
                    'Nombre' => ' - Módulo de Gestión de Entidades instalado',
                    'Numero' => $DepInstall_2,
                ],
                [
                    'Nombre' => ' - Módulo de Gestión de Productos instalado',
                    'Numero' => $DepInstall_3,
                ],
                [
                    'Nombre' => ' - Módulo de Gestión de Servicios instalado',
                    'Numero' => $DepInstall_4,
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
            'table'      => 'facturacion_listado',
            'data'       => '`idFacturacion` bigint UNSIGNED NOT NULL AUTO_INCREMENT,`idUsuario` int UNSIGNED NOT NULL,`idTipo` int UNSIGNED NOT NULL,`idEntidad` int UNSIGNED NOT NULL,`idBodegasIngreso` int UNSIGNED NULL DEFAULT NULL,`idBodegasEgreso` int UNSIGNED NULL DEFAULT NULL,`fecha_auto` date NOT NULL,`idDocumentos` int UNSIGNED NOT NULL,`N_Doc` varchar(60) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Creacion_fecha` date NOT NULL,`Creacion_Semana` int UNSIGNED NULL DEFAULT NULL,`Creacion_mes` int UNSIGNED NULL DEFAULT NULL,`Creacion_ano` int UNSIGNED NULL DEFAULT NULL,`Creacion_hora` time NULL DEFAULT NULL,`Observaciones` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,`ValorNeto` decimal(15, 2) UNSIGNED NULL DEFAULT NULL,`IVA` decimal(15, 2) UNSIGNED NULL DEFAULT NULL,`ValorTotal` decimal(15, 2) UNSIGNED NULL DEFAULT NULL,`TotalItems` decimal(15, 2) UNSIGNED NULL DEFAULT NULL,`TotalProductos` decimal(15, 2) UNSIGNED NULL DEFAULT NULL,`TotalServicios` decimal(15, 2) UNSIGNED NULL DEFAULT NULL,`TotalGuias` decimal(15, 2) UNSIGNED NULL DEFAULT NULL,`idEstadoPago` int UNSIGNED NOT NULL,`MontoPagado` decimal(15, 2) UNSIGNED NULL DEFAULT NULL',
            'primaryKey' => 'idFacturacion',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'facturacion_listado_guias',
            'data'       => '`idExistencia` bigint UNSIGNED NOT NULL AUTO_INCREMENT,`idFacturacion` bigint UNSIGNED NOT NULL,`idFacturacionRel` bigint UNSIGNED NOT NULL',
            'primaryKey' => 'idExistencia',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'facturacion_listado_items',
            'data'       => '`idExistencia` bigint UNSIGNED NOT NULL AUTO_INCREMENT,`idFacturacion` bigint UNSIGNED NOT NULL,`Item` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`Number` decimal(10, 2) UNSIGNED NULL DEFAULT NULL,`ValorTotal` decimal(15, 2) UNSIGNED NOT NULL',
            'primaryKey' => 'idExistencia',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'facturacion_listado_pagos',
            'data'       => '`idPago` bigint UNSIGNED NOT NULL AUTO_INCREMENT,`idFacturacion` bigint UNSIGNED NOT NULL,`idUsuario` int UNSIGNED NOT NULL,`idDocumentoPago` int UNSIGNED NOT NULL,`N_Doc` varchar(60) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`MontoPagado` decimal(15, 2) UNSIGNED NOT NULL,`FechaPago` date NOT NULL',
            'primaryKey' => 'idPago',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'facturacion_listado_productos',
            'data'       => '`idExistencia` bigint UNSIGNED NOT NULL AUTO_INCREMENT,`idFacturacion` bigint UNSIGNED NOT NULL,`idEstadoIngreso` int UNSIGNED NOT NULL,`idBodegas` int UNSIGNED NOT NULL,`idProducto` int UNSIGNED NOT NULL,`Number` decimal(10, 2) UNSIGNED NULL DEFAULT NULL,`ValorTotal` decimal(15, 2) UNSIGNED NOT NULL',
            'primaryKey' => 'idExistencia',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'facturacion_listado_servicios',
            'data'       => '`idExistencia` bigint UNSIGNED NOT NULL AUTO_INCREMENT,`idFacturacion` bigint UNSIGNED NOT NULL,`idServicio` int UNSIGNED NOT NULL,`Number` decimal(10, 2) UNSIGNED NULL DEFAULT NULL,`ValorTotal` decimal(15, 2) UNSIGNED NOT NULL',
            'primaryKey' => 'idExistencia',
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
                $Response = $this->Base_createTable($xParams);
            }
        }

        /*******************************************************/
        /*                 SE GENERAN LAS RUTAS                */
        /*******************************************************/
        $arrPermisos[] = [
            'idPermisosCat'  => '4',
            'idEstado'       => '1',
            'idTipo'         => '2',
            'Nombre'         => 'Compras',
            'Descripcion'    => 'Permite el ingreso de los documentos de compras',
            'idLevelLimit'   => '4',
            'RutaWeb'        => 'gestionDocumentos/compras/listado',
            'RutaController' => 'gestionDocumentosCompras',
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '4',
            'idEstado'       => '1',
            'idTipo'         => '2',
            'Nombre'         => 'Ventas',
            'Descripcion'    => 'Permite el ingreso de los documentos de ventas',
            'idLevelLimit'   => '4',
            'RutaWeb'        => 'gestionDocumentos/ventas/listado',
            'RutaController' => 'gestionDocumentosVentas',
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '4',
            'idEstado'       => '1',
            'idTipo'         => '3',
            'Nombre'         => 'Buscar Documentos',
            'Descripcion'    => 'Permite la busqueda de documentos',
            'idLevelLimit'   => '1',
            'RutaWeb'        => 'gestionDocumentos/informe/busqueda/listado',
            'RutaController' => 'informeDocumentos',
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
                        //Ejecuto la query
                        $xParams  = ['DataCheck' => '', 'query' => $query, 'novalidate' => true];
                        $Response = $this->Base_insert($xParams);
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
                $result  = $this->Base_queryExecute($xParams);
            }
        }

        /*******************************************************/
        /*              SE ELIMINAN LAS TABLAS                 */
        /*******************************************************/
        $arrTableDel  = array();
        $arrTableDel[] = ['table' => 'facturacion_listado'];
        $arrTableDel[] = ['table' => 'facturacion_listado_guias'];
        $arrTableDel[] = ['table' => 'facturacion_listado_items'];
        $arrTableDel[] = ['table' => 'facturacion_listado_pagos'];
        $arrTableDel[] = ['table' => 'facturacion_listado_productos'];
        $arrTableDel[] = ['table' => 'facturacion_listado_servicios'];

        /************************************************/
        //Verifico si existe
        if($arrTableDel){
            //recorro
            foreach ($arrTableDel as $tblDel) {
                //Se ejecuta la query
                $xParams  = ['query' => $tblDel];
                $Response = $this->Base_dropTable($xParams);
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
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/listAll',                    'RutaController' => 'gestionDocumentos->listAll_1',              'Descripcion' => 'Listar Toda la Información',                     'idLevelLimit' => 1, 'Controller' => 'gestionDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/compras/listado/search',                     'RutaController' => 'gestionDocumentos->UpdateList_1',           'Descripcion' => 'Filtrar datos',                                  'idLevelLimit' => 1, 'Controller' => 'gestionDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/updateList',                 'RutaController' => 'gestionDocumentos->UpdateList_1',           'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'gestionDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/view/@id',                   'RutaController' => 'gestionDocumentos->View_1',                 'Descripcion' => 'Mostrar Detallado',                              'idLevelLimit' => 1, 'Controller' => 'gestionDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/print/@id',                  'RutaController' => 'gestionDocumentos->Print_1',                'Descripcion' => 'Pantalla imprimir',                              'idLevelLimit' => 1, 'Controller' => 'gestionDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/noPrint/@id',                'RutaController' => 'gestionDocumentos->noPrint_1',              'Descripcion' => 'Pantalla para visualizar documento',             'idLevelLimit' => 1, 'Controller' => 'gestionDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/resumen/@id',                'RutaController' => 'gestionDocumentos->Resumen_1',              'Descripcion' => 'Mostrar Resúmen',                                'idLevelLimit' => 2, 'Controller' => 'gestionDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/resumenUpdate/@id',          'RutaController' => 'gestionDocumentos->ResumenUpdate_1',        'Descripcion' => 'Mostrar información',                            'idLevelLimit' => 2, 'Controller' => 'gestionDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/compras/listado',                            'RutaController' => 'gestionDocumentos->Insert',                 'Descripcion' => 'Crear Información',                              'idLevelLimit' => 3, 'Controller' => 'gestionDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/compras/listado/update',                     'RutaController' => 'gestionDocumentos->Update',                 'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'gestionDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 4, 'RutaWeb' => 'gestionDocumentos/compras/listado/delFiles',                   'RutaController' => 'gestionDocumentos->DelFiles',               'Descripcion' => 'Permite eliminar archivos',                      'idLevelLimit' => 2, 'Controller' => 'gestionDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'gestionDocumentos/compras/listado',                            'RutaController' => 'gestionDocumentos->Delete',                 'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 4, 'Controller' => 'gestionDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/items/new/@id',              'RutaController' => 'gestionDocumentosItems->New_1',             'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosItems'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/items/updateList/@id',       'RutaController' => 'gestionDocumentosItems->UpdateList_1',      'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosItems'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/items/getID/@id',            'RutaController' => 'gestionDocumentosItems->GetID_1',           'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosItems'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/compras/listado/items',                      'RutaController' => 'gestionDocumentosItems->Insert',            'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosItems'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/compras/listado/items/update',               'RutaController' => 'gestionDocumentosItems->Update',            'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosItems'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'gestionDocumentos/compras/listado/items',                      'RutaController' => 'gestionDocumentosItems->Delete',            'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosItems'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/productos/new/@id',          'RutaController' => 'gestionDocumentosProductos->New_1',         'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/productos/updateList/@id',   'RutaController' => 'gestionDocumentosProductos->UpdateList_1',  'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/productos/getID/@id',        'RutaController' => 'gestionDocumentosProductos->GetID_1',       'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/compras/listado/productos',                  'RutaController' => 'gestionDocumentosProductos->Insert',        'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/compras/listado/productos/update',           'RutaController' => 'gestionDocumentosProductos->Update',        'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'gestionDocumentos/compras/listado/productos',                  'RutaController' => 'gestionDocumentosProductos->Delete',        'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/servicios/new/@id',          'RutaController' => 'gestionDocumentosServicios->New_1',         'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosServicios'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/servicios/updateList/@id',   'RutaController' => 'gestionDocumentosServicios->UpdateList_1',  'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosServicios'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/servicios/getID/@id',        'RutaController' => 'gestionDocumentosServicios->GetID_1',       'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosServicios'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/compras/listado/servicios',                  'RutaController' => 'gestionDocumentosServicios->Insert',        'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosServicios'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/compras/listado/servicios/update',           'RutaController' => 'gestionDocumentosServicios->Update',        'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosServicios'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'gestionDocumentos/compras/listado/servicios',                  'RutaController' => 'gestionDocumentosServicios->Delete',        'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosServicios'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/guias/new/@id',              'RutaController' => 'gestionDocumentosGuias->New_1',             'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosGuias'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/guias/updateList/@id',       'RutaController' => 'gestionDocumentosGuias->UpdateList_1',      'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosGuias'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/compras/listado/guias',                      'RutaController' => 'gestionDocumentosGuias->Insert',            'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosGuias'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'gestionDocumentos/compras/listado/guias',                      'RutaController' => 'gestionDocumentosGuias->Delete',            'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosGuias'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/pagos/new/@id',              'RutaController' => 'gestionDocumentosPagos->New_1',             'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosPagos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/pagos/updateList/@id',       'RutaController' => 'gestionDocumentosPagos->UpdateList_1',      'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosPagos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/pagos/getID/@id',            'RutaController' => 'gestionDocumentosPagos->GetID_1',           'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosPagos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/compras/listado/pagos',                      'RutaController' => 'gestionDocumentosPagos->Insert',            'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosPagos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/compras/listado/pagos/update',               'RutaController' => 'gestionDocumentosPagos->Update',            'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosPagos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'gestionDocumentos/compras/listado/pagos',                      'RutaController' => 'gestionDocumentosPagos->Delete',            'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosPagos'];

                break;
            /******************************************/
            case 2:
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/listAll',                    'RutaController' => 'gestionDocumentos->listAll_2',              'Descripcion' => 'Listar Toda la Información',                     'idLevelLimit' => 1, 'Controller' => 'gestionDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/ventas/listado/search',                     'RutaController' => 'gestionDocumentos->UpdateList_2',           'Descripcion' => 'Filtrar datos',                                  'idLevelLimit' => 1, 'Controller' => 'gestionDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/updateList',                 'RutaController' => 'gestionDocumentos->UpdateList_2',           'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'gestionDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/view/@id',                   'RutaController' => 'gestionDocumentos->View_2',                 'Descripcion' => 'Mostrar Detallado',                              'idLevelLimit' => 1, 'Controller' => 'gestionDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/print/@id',                  'RutaController' => 'gestionDocumentos->Print_2',                'Descripcion' => 'Pantalla imprimir',                              'idLevelLimit' => 1, 'Controller' => 'gestionDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/noPrint/@id',                'RutaController' => 'gestionDocumentos->noPrint_2',              'Descripcion' => 'Pantalla para visualizar documento',             'idLevelLimit' => 1, 'Controller' => 'gestionDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/resumen/@id',                'RutaController' => 'gestionDocumentos->Resumen_2',              'Descripcion' => 'Mostrar Resúmen',                                'idLevelLimit' => 2, 'Controller' => 'gestionDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/resumenUpdate/@id',          'RutaController' => 'gestionDocumentos->ResumenUpdate_2',        'Descripcion' => 'Mostrar información',                            'idLevelLimit' => 2, 'Controller' => 'gestionDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/ventas/listado',                            'RutaController' => 'gestionDocumentos->Insert',                 'Descripcion' => 'Crear Información',                              'idLevelLimit' => 3, 'Controller' => 'gestionDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/ventas/listado/update',                     'RutaController' => 'gestionDocumentos->Update',                 'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'gestionDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 4, 'RutaWeb' => 'gestionDocumentos/ventas/listado/delFiles',                   'RutaController' => 'gestionDocumentos->DelFiles',               'Descripcion' => 'Permite eliminar archivos',                      'idLevelLimit' => 2, 'Controller' => 'gestionDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'gestionDocumentos/ventas/listado',                            'RutaController' => 'gestionDocumentos->Delete',                 'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 4, 'Controller' => 'gestionDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/items/new/@id',              'RutaController' => 'gestionDocumentosItems->New_2',             'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosItems'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/items/updateList/@id',       'RutaController' => 'gestionDocumentosItems->UpdateList_2',      'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosItems'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/items/getID/@id',            'RutaController' => 'gestionDocumentosItems->GetID_2',           'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosItems'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/ventas/listado/items',                      'RutaController' => 'gestionDocumentosItems->Insert',            'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosItems'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/ventas/listado/items/update',               'RutaController' => 'gestionDocumentosItems->Update',            'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosItems'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'gestionDocumentos/ventas/listado/items',                      'RutaController' => 'gestionDocumentosItems->Delete',            'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosItems'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/productos/new/@id',          'RutaController' => 'gestionDocumentosProductos->New_2',         'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/productos/updateList/@id',   'RutaController' => 'gestionDocumentosProductos->UpdateList_2',  'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/productos/getID/@id',        'RutaController' => 'gestionDocumentosProductos->GetID_2',       'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/ventas/listado/productos',                  'RutaController' => 'gestionDocumentosProductos->Insert',        'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/ventas/listado/productos/update',           'RutaController' => 'gestionDocumentosProductos->Update',        'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'gestionDocumentos/ventas/listado/productos',                  'RutaController' => 'gestionDocumentosProductos->Delete',        'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/servicios/new/@id',          'RutaController' => 'gestionDocumentosServicios->New_2',         'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosServicios'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/servicios/updateList/@id',   'RutaController' => 'gestionDocumentosServicios->UpdateList_2',  'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosServicios'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/servicios/getID/@id',        'RutaController' => 'gestionDocumentosServicios->GetID_2',       'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosServicios'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/ventas/listado/servicios',                  'RutaController' => 'gestionDocumentosServicios->Insert',        'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosServicios'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/ventas/listado/servicios/update',           'RutaController' => 'gestionDocumentosServicios->Update',        'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosServicios'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'gestionDocumentos/ventas/listado/servicios',                  'RutaController' => 'gestionDocumentosServicios->Delete',        'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosServicios'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/guias/new/@id',              'RutaController' => 'gestionDocumentosGuias->New_1',             'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosGuias'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/guias/updateList/@id',       'RutaController' => 'gestionDocumentosGuias->UpdateList_1',      'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosGuias'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/ventas/listado/guias',                      'RutaController' => 'gestionDocumentosGuias->Insert',            'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosGuias'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'gestionDocumentos/ventas/listado/guias',                      'RutaController' => 'gestionDocumentosGuias->Delete',            'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosGuias'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/pagos/new/@id',              'RutaController' => 'gestionDocumentosPagos->New_2',             'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosPagos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/pagos/updateList/@id',       'RutaController' => 'gestionDocumentosPagos->UpdateList_2',      'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosPagos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/pagos/getID/@id',            'RutaController' => 'gestionDocumentosPagos->GetID_2',           'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosPagos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/ventas/listado/pagos',                      'RutaController' => 'gestionDocumentosPagos->Insert',            'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosPagos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/ventas/listado/pagos/update',               'RutaController' => 'gestionDocumentosPagos->Update',            'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosPagos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'gestionDocumentos/ventas/listado/pagos',                      'RutaController' => 'gestionDocumentosPagos->Delete',            'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosPagos'];

                break;
            /******************************************/
            case 3:
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/informe/busqueda/listado/listAll',   'RutaController' => 'informeDocumentos->listAll',    'Descripcion' => 'Filtro de búsqueda', 'idLevelLimit' => 1, 'Controller' => 'informeDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/informe/busqueda/listado/search',    'RutaController' => 'informeDocumentos->UpdateList', 'Descripcion' => 'Filtrar datos',      'idLevelLimit' => 1, 'Controller' => 'informeDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/informe/busqueda/listado/view/@id',  'RutaController' => 'gestionDocumentos->View_0',       'Descripcion' => 'Mostrar Detallado',  'idLevelLimit' => 1, 'Controller' => 'informeDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/informe/busqueda/listado/print/@id', 'RutaController' => 'gestionDocumentos->Print_0',      'Descripcion' => 'Pantalla imprimir',  'idLevelLimit' => 1, 'Controller' => 'informeDocumentos'];

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
        $RutaController  = '"gestionDocumentosCompras"';
        $RutaController .= ',"gestionDocumentosVentas"';
        $RutaController .= ',"informeDocumentos"';

        //devuelvo
        return $RutaController;
    }

}