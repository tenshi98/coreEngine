<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class cotizacionInstaller extends ControllerBase {

    /******************************************************************************/
    // Variables
    private $controllerName;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_ADMIN);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName     = 'cotizacionInstaller';
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
        $countPermisos = is_numeric($nData1)&&$nData1!=0 ? 1 : 0;
        $DepInstall_1  = is_numeric($DepData1)&&$DepData1!=0 ? 1 : 0;
        $DepInstall_2  = is_numeric($DepData2)&&$DepData2!=0 ? 1 : 0;
        $DepInstall_3  = is_numeric($DepData3)&&$DepData3!=0 ? 1 : 0;
        $DepInstall_4  = is_numeric($DepData4)&&$DepData4!=0 ? 1 : 0;

        /******************************************/
        //Verificar que existan los permisos
        $arrData = [
            'Nombre'        => 'Módulo de Cotizaciones',
            'Descripcion'   => 'Módulo para gestionar las cotizaciones de los clientes',
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
        // Variables
        $arrTables    = $this->listTables();
        $arrPermisos  = array();

        /************************************************/
        /************************************************/
        // Verifico si existe
        if($arrTables){
            //recorro
            foreach ($arrTables as $table) {
                /******************************/
                // Se genera la query
                $xParams = ['query' => $table];
                $this->Base_createTable($xParams);
            }
        }

        /*******************************************************/
        /*                 SE GENERAN LAS RUTAS                */
        /*******************************************************/
        $arrPermisos[] = [
            'idPermisosCat'  => '4',
            'idEstado'       => '1',
            'idTipo'         => '2',
            'Nombre'         => 'Cotizaciones',
            'Descripcion'    => 'Permite el ingreso de los documentos de ventas',
            'idLevelLimit'   => '4',
            'RutaWeb'        => 'cotizacionListado/ventas/listado',
            'RutaController' => 'cotizacionListado',
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '4',
            'idEstado'       => '1',
            'idTipo'         => '3',
            'Nombre'         => 'Buscar Cotizaciones',
            'Descripcion'    => 'Permite la busqueda de cotizaciones',
            'idLevelLimit'   => '1',
            'RutaWeb'        => 'cotizacionListado/informe/busqueda/listado',
            'RutaController' => 'informeCotizacion',
        ];
        /************************************************/
        /************************************************/
        // Verifico si existe
        if($arrPermisos){
            //Variable
            $IntCounter = 1;
            //recorro
            foreach ($arrPermisos as $permiso) {
                /************************************************/
                // Se genera la query
                $query = [
                    'data'      => 'idPermisosCat,idEstado,idTipo,Nombre,Descripcion,idLevelLimit,RutaWeb,RutaController',
                    'required'  => 'idPermisosCat,idEstado,idTipo,Nombre,Descripcion,idLevelLimit,RutaWeb,RutaController',
                    'unique'    => '',
                    'encode'    => '',
                    'table'     => 'core_permisos_listado',
                    'Post'      => $permiso
                ];
                // Ejecuto la query
                $xParams    = ['DataCheck' => '', 'query' => $query];
                $permisosID = $this->Base_insert($xParams);
                /************************************************/
                //Listar las rutas
                $arrRutas = $this->listRouteModule($IntCounter, $permisosID['data']);
                /************************************************/
                // Verifico si existe
                if($arrRutas){
                    //recorro
                    foreach ($arrRutas as $rutas) {
                        /******************************/
                        // Se genera la query
                        $query = [
                            'data'      => 'idPermisos,idMetodo,RutaWeb,RutaController,Descripcion,idLevelLimit,Controller',
                            'required'  => 'idPermisos,idMetodo,RutaWeb,RutaController,Descripcion,idLevelLimit,Controller',
                            'unique'    => '',
                            'encode'    => '',
                            'table'     => 'core_permisos_listado_rutas',
                            'Post'      => $rutas
                        ];
                        // Ejecuto la query
                        // Ejecuto la query
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
        // Se genera la query
        $query = [
            'data'    => 'idPermisos',
            'table'   => 'core_permisos_listado',
            'join'    => '',
            'where'   => 'RutaController IN ('.$RutaController.')',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'idPermisos ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Ejecuto la query
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
        // Verifico si existe
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
        $arrTableDel[] = ['table' => 'cotizacion_listado'];
        $arrTableDel[] = ['table' => 'cotizacion_listado_items'];
        $arrTableDel[] = ['table' => 'cotizacion_listado_productos'];
        $arrTableDel[] = ['table' => 'cotizacion_listado_servicios'];

        /************************************************/
        // Verifico si existe
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
        // Se genera la query
        $query = [
            'data'    => 'idRutas',
            'table'   => 'core_permisos_listado_rutas',
            'join'    => '',
            'where'   => 'Controller IN ('.$RutaController.')',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Ejecuto la query
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
        // Variables
        $arrRutas  = array();

        /******************************************/
        // Variables
        switch ($Type) {
            /******************************************/
            case 1:
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/listAll',                    'RutaController' => 'cotizacionListado->listAll',                'Descripcion' => 'Listar Toda la Información',                     'idLevelLimit' => 1, 'Controller' => 'cotizacionListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'cotizacionListado/ventas/listado/search',                     'RutaController' => 'cotizacionListado->UpdateList',             'Descripcion' => 'Filtrar datos',                                  'idLevelLimit' => 1, 'Controller' => 'cotizacionListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/updateList',                 'RutaController' => 'cotizacionListado->UpdateList',             'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'cotizacionListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/view/@id',                   'RutaController' => 'cotizacionListado->View',                   'Descripcion' => 'Mostrar Detallado',                              'idLevelLimit' => 1, 'Controller' => 'cotizacionListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/print/@id',                  'RutaController' => 'cotizacionListado->Print',                  'Descripcion' => 'Pantalla imprimir',                              'idLevelLimit' => 1, 'Controller' => 'cotizacionListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/noPrint/@id',                'RutaController' => 'cotizacionListado->noPrint',                'Descripcion' => 'Pantalla para visualizar documento',             'idLevelLimit' => 1, 'Controller' => 'cotizacionListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/resumen/@id',                'RutaController' => 'cotizacionListado->Resumen',                'Descripcion' => 'Mostrar Resúmen',                                'idLevelLimit' => 2, 'Controller' => 'cotizacionListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/resumenUpdate/@id',          'RutaController' => 'cotizacionListado->ResumenUpdate',          'Descripcion' => 'Mostrar información',                            'idLevelLimit' => 2, 'Controller' => 'cotizacionListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'cotizacionListado/ventas/listado',                            'RutaController' => 'cotizacionListado->Insert',                 'Descripcion' => 'Crear Información',                              'idLevelLimit' => 3, 'Controller' => 'cotizacionListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'cotizacionListado/ventas/listado/update',                     'RutaController' => 'cotizacionListado->Update',                 'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'cotizacionListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 4, 'RutaWeb' => 'cotizacionListado/ventas/listado/delFiles',                   'RutaController' => 'cotizacionListado->DelFiles',               'Descripcion' => 'Permite eliminar archivos',                      'idLevelLimit' => 2, 'Controller' => 'cotizacionListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'cotizacionListado/ventas/listado',                            'RutaController' => 'cotizacionListado->Delete',                 'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 4, 'Controller' => 'cotizacionListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/items/new/@id',              'RutaController' => 'cotizacionListadoItems->New',               'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoItems'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/items/updateList/@id',       'RutaController' => 'cotizacionListadoItems->UpdateList',        'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoItems'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/items/getID/@id',            'RutaController' => 'cotizacionListadoItems->GetID',             'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoItems'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'cotizacionListado/ventas/listado/items',                      'RutaController' => 'cotizacionListadoItems->Insert',            'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoItems'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'cotizacionListado/ventas/listado/items/update',               'RutaController' => 'cotizacionListadoItems->Update',            'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoItems'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'cotizacionListado/ventas/listado/items',                      'RutaController' => 'cotizacionListadoItems->Delete',            'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoItems'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/productos/new/@id',          'RutaController' => 'cotizacionListadoProductos->New',           'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/productos/updateList/@id',   'RutaController' => 'cotizacionListadoProductos->UpdateList',    'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/productos/getID/@id',        'RutaController' => 'cotizacionListadoProductos->GetID',         'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'cotizacionListado/ventas/listado/productos',                  'RutaController' => 'cotizacionListadoProductos->Insert',        'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'cotizacionListado/ventas/listado/productos/update',           'RutaController' => 'cotizacionListadoProductos->Update',        'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'cotizacionListado/ventas/listado/productos',                  'RutaController' => 'cotizacionListadoProductos->Delete',        'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoProductos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/servicios/new/@id',          'RutaController' => 'cotizacionListadoServicios->New',           'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoServicios'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/servicios/updateList/@id',   'RutaController' => 'cotizacionListadoServicios->UpdateList',    'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoServicios'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/servicios/getID/@id',        'RutaController' => 'cotizacionListadoServicios->GetID',         'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoServicios'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'cotizacionListado/ventas/listado/servicios',                  'RutaController' => 'cotizacionListadoServicios->Insert',        'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoServicios'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'cotizacionListado/ventas/listado/servicios/update',           'RutaController' => 'cotizacionListadoServicios->Update',        'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoServicios'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'cotizacionListado/ventas/listado/servicios',                  'RutaController' => 'cotizacionListadoServicios->Delete',        'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoServicios'];

                break;
            /******************************************/
            case 2:
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/informe/busqueda/listado/listAll',   'RutaController' => 'informeCotizacion->listAll',    'Descripcion' => 'Filtro de búsqueda', 'idLevelLimit' => 1, 'Controller' => 'informeCotizacion'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'cotizacionListado/informe/busqueda/listado/search',    'RutaController' => 'informeCotizacion->UpdateList', 'Descripcion' => 'Filtrar datos',      'idLevelLimit' => 1, 'Controller' => 'informeCotizacion'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/informe/busqueda/listado/view/@id',  'RutaController' => 'cotizacionListado->View',       'Descripcion' => 'Mostrar Detallado',  'idLevelLimit' => 1, 'Controller' => 'informeCotizacion'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/informe/busqueda/listado/print/@id', 'RutaController' => 'cotizacionListado->Print',      'Descripcion' => 'Pantalla imprimir',  'idLevelLimit' => 1, 'Controller' => 'informeCotizacion'];

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
        $RutaController  = '"cotizacionListado"';
        $RutaController .= ',"informeCotizacion"';

        //devuelvo
        return $RutaController;
    }
    /******************************************************************************/
    //Se listan las tablas
    public function listTables(){

        /******************************************/
        // Variables
        $arrTables    = array();

        /*******************************************************/
        /*                 SE GENERAN LAS TABLAS               */
        /*******************************************************/
        $arrTables[] = [
            'table'      => 'cotizacion_listado',
            'data'       => '`idCotizacion` bigint(20) unsigned NOT NULL AUTO_INCREMENT,`idUsuario` int(10) unsigned NOT NULL,`idEntidad` int(10) unsigned NOT NULL,`fecha_auto` date NOT NULL,`Creacion_fecha` date NOT NULL,`Creacion_Semana` int(10) unsigned NULL DEFAULT NULL,`Creacion_mes` int(10) unsigned NULL DEFAULT NULL,`Creacion_ano` int(10) unsigned NULL DEFAULT NULL,`Creacion_hora` time NULL DEFAULT NULL,`Observaciones` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,`ValorNeto` decimal(15, 2) UNSIGNED NULL DEFAULT NULL,`IVA` decimal(15, 2) UNSIGNED NULL DEFAULT NULL,`ValorTotal` decimal(15, 2) UNSIGNED NULL DEFAULT NULL,`TotalItems` decimal(15, 2) UNSIGNED NULL DEFAULT NULL,`TotalProductos` decimal(15, 2) UNSIGNED NULL DEFAULT NULL,`TotalServicios` decimal(15, 2) UNSIGNED NULL DEFAULT NULL,`TotalGuias` decimal(15, 2) UNSIGNED NULL DEFAULT NULL',
            'primaryKey' => 'idCotizacion',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'cotizacion_listado_items',
            'data'       => '`idExistencia` bigint(20) unsigned NOT NULL AUTO_INCREMENT,`idCotizacion` bigint(20) unsigned NOT NULL,`Item` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`Number` decimal(10, 2) UNSIGNED NULL DEFAULT NULL,`ValorTotal` decimal(15, 2) UNSIGNED NOT NULL',
            'primaryKey' => 'idExistencia',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'cotizacion_listado_productos',
            'data'       => '`idExistencia` bigint(20) unsigned NOT NULL AUTO_INCREMENT,`idCotizacion` bigint(20) unsigned NOT NULL,`idProducto` int(10) unsigned NOT NULL,`Number` decimal(10, 2) UNSIGNED NULL DEFAULT NULL,`ValorTotal` decimal(15, 2) UNSIGNED NOT NULL',
            'primaryKey' => 'idExistencia',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'cotizacion_listado_servicios',
            'data'       => '`idExistencia` bigint(20) unsigned NOT NULL AUTO_INCREMENT,`idCotizacion` bigint(20) unsigned NOT NULL,`idServicio` int(10) unsigned NOT NULL,`Number` decimal(10, 2) UNSIGNED NULL DEFAULT NULL,`ValorTotal` decimal(15, 2) UNSIGNED NOT NULL',
            'primaryKey' => 'idExistencia',
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
        entidades_listado(idEntidad,idSector,idSexo,idTipoEntidad,Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Rut,idCiudad,idComuna,Direccion,FNacimiento,Email,Fono1,Fono2,Web,Giro,RepLegalNombre,RepLegalRut,RepLegalEmail,RepLegalFono1,RepLegalFono2)
        core_tipos_entidades(idTipoEntidad,Nombre)
        entidades_sectores(idSector,Nombre)
        productos_listado(idProducto,idUniMed,Nombre)
        core_unidades_medida(idUniMed,Nombre)
        servicios_listado(idServicio,Nombre)
        core_sexo(idSexo,Nombre)
        core_ubicacion_ciudad(idCiudad,Nombre)
        core_ubicacion_comunas(idComuna,idCiudad,Nombre)
        ';

        /******************************************/
        // Variables
        $arrTables = $this->listTables();
        $dataSQL   = new FunctionsDataSQL();
        $Data     .= $dataSQL->minifyArrayTables($arrTables);

        /************************************************/
        //devuelvo true
        return $Data;

    }

}
