<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class bodegasInstaller extends ControllerInstaller {

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
        $this->controllerName     = 'bodegasInstaller';
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
        $usuariosInstaller  = new usuariosInstaller();
		$productosInstaller = new productosInstaller();

        /************************************/
        // Se verifica si esta instalado
        $nData1    = $this->GetCountDataModule();
        $DepData1  = $usuariosInstaller->GetCountDataModule();
        $DepData2  = $productosInstaller->GetCountDataModule();

        /************************************/
        // Defino si etsa instalado en base a la respuesta
        $countPermisos = is_numeric($nData1)&&$nData1!=0 ? 1 : 0;
        $DepInstall_1  = is_numeric($DepData1)&&$DepData1!=0 ? 1 : 0;
        $DepInstall_2  = is_numeric($DepData2)&&$DepData2!=0 ? 1 : 0;

        /************************************/
        // Se crean los datos a mostrar
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
        $arrTables        = $this->listTables();
        $arrOptimizations = $this->optimizeTables();
        $arrPermisos      = array();

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

        /************************************/
        // Verifico si existe
        if($arrOptimizations){
            // Recorro los datos
            foreach ($arrOptimizations as $opt) {
                /************************************/
                // Preparo los datos
                $xParams      = ['query' => $opt['optimization']];
                // Ejecuto la query
                $ResponseExec = $this->Base_queryExecute($xParams);
                /************************************/
                // Si falla la la ejecucion, se muestra alerta
                if ($ResponseExec['status'] === false) {
                    $this->Base_transactionRollback();
                    Response::error('Error al operar con la Base de Datos', 500, $ResponseExec['error']);
                }
            }
        }

        /*******************************************************/
        /*                 SE GENERAN LAS RUTAS                */
        /*******************************************************/
        $arrPermisos[] = [
            'idPermisosCat'  => '1',                               // Administración
            'idEstado'       => '1',                               // Activo
            'idTipo'         => '2',                               // Crud Resumen
            'Nombre'         => 'Bodegas - Listado',               // Nombre de la transaccion
            'Descripcion'    => 'Permite administrar las bodegas', // Descripcion de la transaccion
            'idLevelLimit'   => '4',                               // Ver / Editar / Crear / Borrar
            'RutaWeb'        => 'administracion/bodegas/listado',  // Ruta web de la transaccion
            'RutaController' => 'bodegasListado',                  // Controlador de la transaccion
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '3',                                        // Gestión Bodegas y Productos
            'idEstado'       => '1',                                        // Activo
            'idTipo'         => '2',                                        // Crud Resumen
            'Nombre'         => 'Movimientos Bodegas - Ingresos',           // Nombre de la transaccion
            'Descripcion'    => 'Permite el ingreso de productos a bodega', // Descripcion de la transaccion
            'idLevelLimit'   => '4',                                        // Ver / Editar / Crear / Borrar
            'RutaWeb'        => 'gestionBodegas/ingresos/listado',          // Ruta web de la transaccion
            'RutaController' => 'bodegasMovimientoIngreso',                 // Controlador de la transaccion
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '3',                                       // Gestión Bodegas y Productos
            'idEstado'       => '1',                                       // Activo
            'idTipo'         => '2',                                       // Crud Resumen
            'Nombre'         => 'Movimientos Bodegas - Egresos',           // Nombre de la transaccion
            'Descripcion'    => 'Permite el egreso de productos a bodega', // Descripcion de la transaccion
            'idLevelLimit'   => '4',                                       // Ver / Editar / Crear / Borrar
            'RutaWeb'        => 'gestionBodegas/egresos/listado',          // Ruta web de la transaccion
            'RutaController' => 'bodegasMovimientoEgreso',                 // Controlador de la transaccion
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '3',                                              // Gestión Bodegas y Productos
            'idEstado'       => '1',                                              // Activo
            'idTipo'         => '2',                                              // Crud Resumen
            'Nombre'         => 'Movimientos Bodegas - Traspasos',                // Nombre de la transaccion
            'Descripcion'    => 'Permite el traspaso de productos entre bodegas', // Descripcion de la transaccion
            'idLevelLimit'   => '4',                                              // Ver / Editar / Crear / Borrar
            'RutaWeb'        => 'gestionBodegas/traspaso/listado',                // Ruta web de la transaccion
            'RutaController' => 'bodegasMovimientoTraspaso',                      // Controlador de la transaccion
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '3',                                                           // Gestión Bodegas y Productos
            'idEstado'       => '1',                                                           // Activo
            'idTipo'         => '3',                                                           // Informe
            'Nombre'         => 'Stock Productos',                                             // Nombre de la transaccion
            'Descripcion'    => 'Permite ver el stock actual de los productos en las bodegas', // Descripcion de la transaccion
            'idLevelLimit'   => '1',                                                           // Solo Ver
            'RutaWeb'        => 'gestionBodegas/productos/listado',                            // Ruta web de la transaccion
            'RutaController' => 'informeProductos',                                            // Controlador de la transaccion
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
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/bodegas/listado/listAll',                        'RutaController' => 'bodegasListado->listAll',                   'Descripcion' => 'Listar Toda la Información',                    'idLevelLimit' => 1, 'Controller' => 'bodegasListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/bodegas/listado/search',                         'RutaController' => 'bodegasListado->UpdateList',                'Descripcion' => 'Filtrar datos',                                 'idLevelLimit' => 1, 'Controller' => 'bodegasListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/bodegas/listado/updateList',                     'RutaController' => 'bodegasListado->UpdateList',                'Descripcion' => 'Actualizar Lista',                              'idLevelLimit' => 2, 'Controller' => 'bodegasListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/bodegas/listado/view/@id',                       'RutaController' => 'bodegasListado->View',                      'Descripcion' => 'Mostrar Detallado',                             'idLevelLimit' => 1, 'Controller' => 'bodegasListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/bodegas/listado/resumen/@id',                    'RutaController' => 'bodegasListado->Resumen',                   'Descripcion' => 'Mostrar Resúmen',                               'idLevelLimit' => 2, 'Controller' => 'bodegasListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/bodegas/listado/resumenUpdate/@id',              'RutaController' => 'bodegasListado->ResumenUpdate',             'Descripcion' => 'Mostrar información',                           'idLevelLimit' => 2, 'Controller' => 'bodegasListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/bodegas/listado',                                'RutaController' => 'bodegasListado->Insert',                    'Descripcion' => 'Crear Información',                             'idLevelLimit' => 3, 'Controller' => 'bodegasListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/bodegas/listado/update',                         'RutaController' => 'bodegasListado->Update',                    'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'bodegasListado'],
                ['idMetodo' => 4, 'RutaWeb' => 'administracion/bodegas/listado/delFiles',                       'RutaController' => 'bodegasListado->DelFiles',                  'Descripcion' => 'Permite eliminar archivos',                     'idLevelLimit' => 2, 'Controller' => 'bodegasListado'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/bodegas/listado',                                'RutaController' => 'bodegasListado->Delete',                    'Descripcion' => 'Borrar dato y archivos',                        'idLevelLimit' => 4, 'Controller' => 'bodegasListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/bodegas/listado/observaciones/new/@id',          'RutaController' => 'bodegasListadoObservaciones->New',          'Descripcion' => 'Mostrar modal nuevo',                           'idLevelLimit' => 2, 'Controller' => 'bodegasListadoObservaciones'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/bodegas/listado/observaciones/updateList/@id',   'RutaController' => 'bodegasListadoObservaciones->UpdateList',   'Descripcion' => 'Actualizar Lista',                              'idLevelLimit' => 2, 'Controller' => 'bodegasListadoObservaciones'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/bodegas/listado/observaciones/view/@id',         'RutaController' => 'bodegasListadoObservaciones->View',         'Descripcion' => 'Mostrar Detallado',                             'idLevelLimit' => 2, 'Controller' => 'bodegasListadoObservaciones'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/bodegas/listado/observaciones/getID/@id',        'RutaController' => 'bodegasListadoObservaciones->GetID',        'Descripcion' => 'Información para el formulario edición',        'idLevelLimit' => 2, 'Controller' => 'bodegasListadoObservaciones'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/bodegas/listado/observaciones',                  'RutaController' => 'bodegasListadoObservaciones->Insert',       'Descripcion' => 'Crear Información',                             'idLevelLimit' => 2, 'Controller' => 'bodegasListadoObservaciones'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/bodegas/listado/observaciones/update',           'RutaController' => 'bodegasListadoObservaciones->Update',       'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'bodegasListadoObservaciones'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/bodegas/listado/observaciones',                  'RutaController' => 'bodegasListadoObservaciones->Delete',       'Descripcion' => 'Borrar dato y archivos',                        'idLevelLimit' => 2, 'Controller' => 'bodegasListadoObservaciones'],

            ],
            2 => [
                ['idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/listAll',                    'RutaController' => 'bodegasMovimiento->listAll_1',               'Descripcion' => 'Listar Toda la Información',                    'idLevelLimit' => 1, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/search',                     'RutaController' => 'bodegasMovimiento->UpdateList_1',            'Descripcion' => 'Filtrar datos',                                 'idLevelLimit' => 1, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/updateList',                 'RutaController' => 'bodegasMovimiento->UpdateList_1',            'Descripcion' => 'Actualizar Lista',                              'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/view/@id',                   'RutaController' => 'bodegasMovimiento->View_1',                  'Descripcion' => 'Mostrar Detallado',                             'idLevelLimit' => 1, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/resumen/@id',                'RutaController' => 'bodegasMovimiento->Resumen_1',               'Descripcion' => 'Mostrar Resúmen',                               'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/resumenUpdate/@id',          'RutaController' => 'bodegasMovimiento->ResumenUpdate_1',         'Descripcion' => 'Mostrar información',                           'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/ingresos/listado',                            'RutaController' => 'bodegasMovimiento->Insert',                  'Descripcion' => 'Crear Información',                             'idLevelLimit' => 3, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/update',                     'RutaController' => 'bodegasMovimiento->Update',                  'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 4, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/delFiles',                   'RutaController' => 'bodegasMovimiento->DelFiles',                'Descripcion' => 'Permite eliminar archivos',                     'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 3, 'RutaWeb' =>  'gestionBodegas/ingresos/listado',                            'RutaController' => 'bodegasMovimiento->Delete',                  'Descripcion' => 'Borrar dato y archivos',                        'idLevelLimit' => 4, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/productos/new/@id',          'RutaController' => 'bodegasMovimientoProductos->New_1',          'Descripcion' => 'Mostrar modal nuevo',                           'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'],
                ['idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/productos/updateList/@id',   'RutaController' => 'bodegasMovimientoProductos->UpdateList_1',   'Descripcion' => 'Actualizar Lista',                              'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'],
                ['idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/productos/getID/@id',        'RutaController' => 'bodegasMovimientoProductos->GetID_1',        'Descripcion' => 'Información para el formulario edición',        'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'],
                ['idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/productos',                  'RutaController' => 'bodegasMovimientoProductos->Insert',         'Descripcion' => 'Crear Información',                             'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'],
                ['idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/productos/update',           'RutaController' => 'bodegasMovimientoProductos->Update',         'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'],
                ['idMetodo' => 3, 'RutaWeb' =>  'gestionBodegas/ingresos/listado/productos',                  'RutaController' => 'bodegasMovimientoProductos->Delete',         'Descripcion' => 'Borrar dato y archivos',                        'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'],
            ],
            3 => [
                ['idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/egresos/listado/listAll',                     'RutaController' => 'bodegasMovimiento->listAll_2',              'Descripcion' => 'Listar Toda la Información',                     'idLevelLimit' => 1, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/egresos/listado/search',                      'RutaController' => 'bodegasMovimiento->UpdateList_2',           'Descripcion' => 'Filtrar datos',                                  'idLevelLimit' => 1, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/egresos/listado/updateList',                  'RutaController' => 'bodegasMovimiento->UpdateList_2',           'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/egresos/listado/view/@id',                    'RutaController' => 'bodegasMovimiento->View_2',                 'Descripcion' => 'Mostrar Detallado',                              'idLevelLimit' => 1, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/egresos/listado/resumen/@id',                 'RutaController' => 'bodegasMovimiento->Resumen_2',              'Descripcion' => 'Mostrar Resúmen',                                'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/egresos/listado/resumenUpdate/@id',           'RutaController' => 'bodegasMovimiento->ResumenUpdate_2',        'Descripcion' => 'Mostrar información',                            'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/egresos/listado',                             'RutaController' => 'bodegasMovimiento->Insert',                 'Descripcion' => 'Crear Información',                              'idLevelLimit' => 3, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/egresos/listado/update',                      'RutaController' => 'bodegasMovimiento->Update',                 'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 4, 'RutaWeb' =>  'gestionBodegas/egresos/listado/delFiles',                    'RutaController' => 'bodegasMovimiento->DelFiles',               'Descripcion' => 'Permite eliminar archivos',                      'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 3, 'RutaWeb' =>  'gestionBodegas/egresos/listado',                             'RutaController' => 'bodegasMovimiento->Delete',                 'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 4, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/egresos/listado/productos/new/@id',           'RutaController' => 'bodegasMovimientoProductos->New_2',         'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'],
                ['idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/egresos/listado/productos/updateList/@id',    'RutaController' => 'bodegasMovimientoProductos->UpdateList_2',  'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'],
                ['idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/egresos/listado/productos/getID/@id',         'RutaController' => 'bodegasMovimientoProductos->GetID_2',       'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'],
                ['idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/egresos/listado/productos',                   'RutaController' => 'bodegasMovimientoProductos->Insert',        'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'],
                ['idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/egresos/listado/productos/update',            'RutaController' => 'bodegasMovimientoProductos->Update',        'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'],
                ['idMetodo' => 3, 'RutaWeb' =>  'gestionBodegas/egresos/listado/productos',                   'RutaController' => 'bodegasMovimientoProductos->Delete',        'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'],
            ],
            4 => [
                ['idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/listAll',                    'RutaController' => 'bodegasMovimiento->listAll_3',               'Descripcion' => 'Listar Toda la Información',                     'idLevelLimit' => 1, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/search',                     'RutaController' => 'bodegasMovimiento->UpdateList_3',            'Descripcion' => 'Filtrar datos',                                  'idLevelLimit' => 1, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/updateList',                 'RutaController' => 'bodegasMovimiento->UpdateList_3',            'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/view/@id',                   'RutaController' => 'bodegasMovimiento->View_3',                  'Descripcion' => 'Mostrar Detallado',                              'idLevelLimit' => 1, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/resumen/@id',                'RutaController' => 'bodegasMovimiento->Resumen_3',               'Descripcion' => 'Mostrar Resúmen',                                'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/resumenUpdate/@id',          'RutaController' => 'bodegasMovimiento->ResumenUpdate_3',         'Descripcion' => 'Mostrar información',                            'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/traspaso/listado',                            'RutaController' => 'bodegasMovimiento->Insert',                  'Descripcion' => 'Crear Información',                              'idLevelLimit' => 3, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/update',                     'RutaController' => 'bodegasMovimiento->Update',                  'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 4, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/delFiles',                   'RutaController' => 'bodegasMovimiento->DelFiles',                'Descripcion' => 'Permite eliminar archivos',                      'idLevelLimit' => 2, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 3, 'RutaWeb' =>  'gestionBodegas/traspaso/listado',                            'RutaController' => 'bodegasMovimiento->Delete',                  'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 4, 'Controller' => 'bodegasMovimiento'],
                ['idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/productos/new/@id',          'RutaController' => 'bodegasMovimientoProductos->New_3',          'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'],
                ['idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/productos/updateList/@id',   'RutaController' => 'bodegasMovimientoProductos->UpdateList_3',   'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'],
                ['idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/productos/getID/@id',        'RutaController' => 'bodegasMovimientoProductos->GetID_3',        'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'],
                ['idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/productos',                  'RutaController' => 'bodegasMovimientoProductos->Insert',         'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'],
                ['idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/productos/update',           'RutaController' => 'bodegasMovimientoProductos->Update',         'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'],
                ['idMetodo' => 3, 'RutaWeb' =>  'gestionBodegas/traspaso/listado/productos',                  'RutaController' => 'bodegasMovimientoProductos->Delete',         'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'bodegasMovimientoProductos'],
            ],
            5 => [
                ['idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/productos/listado/listAll',                      'RutaController' => 'informeProductos->listAll',      'Descripcion' => 'Filtro de búsqueda',  'idLevelLimit' => 1, 'Controller' => 'informeProductos'],
                ['idMetodo' => 2, 'RutaWeb' =>  'gestionBodegas/productos/listado/search',                       'RutaController' => 'informeProductos->UpdateList',   'Descripcion' => 'Filtrar datos',       'idLevelLimit' => 1, 'Controller' => 'informeProductos'],
                ['idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/productos/listado/view/@idProducto/@idBodegas',  'RutaController' => 'informeProductos->View',         'Descripcion' => 'Mostrar Detallado',   'idLevelLimit' => 1, 'Controller' => 'informeProductos'],
                ['idMetodo' => 1, 'RutaWeb' =>  'gestionBodegas/productos/listado/print/@id',                    'RutaController' => 'informeProductos->Print',        'Descripcion' => 'Pantalla imprimir',   'idLevelLimit' => 1, 'Controller' => 'informeProductos'],
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
        $RutaController  = '"bodegasListado"';
        $RutaController .= ',"bodegasListadoObservaciones"';
        $RutaController .= ',"bodegasMovimientoIngreso"';
        $RutaController .= ',"bodegasMovimientoEgreso"';
        $RutaController .= ',"bodegasMovimientoTraspaso"';
        $RutaController .= ',"informeProductos"';

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
            'table'      => 'bodegas_listado',
            'data'       => '`idBodegas` int(10) unsigned NOT NULL AUTO_INCREMENT,`idEstado` int(10) unsigned NOT NULL,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`idCiudad` int(10) unsigned NULL DEFAULT NULL,`idComuna` int(10) unsigned NULL DEFAULT NULL,`Direccion` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Direccion_img` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`DistribucionFisica` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`DistribucionVisual` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL',
            'primaryKey' => 'idBodegas',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'bodegas_listado_observaciones',
            'data'       => '`idObservaciones` int(10) unsigned NOT NULL AUTO_INCREMENT,`idUsuario` int(10) unsigned NOT NULL,`idBodegas` int(10) unsigned NOT NULL,`Observacion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`FechaCreacion` date NOT NULL',
            'primaryKey' => 'idObservaciones',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'bodegas_listado_permisos_usuarios',
            'data'       => '`idPermisoUsuario` int(10) unsigned NOT NULL AUTO_INCREMENT,`idUsuario` int(10) unsigned NOT NULL,`idBodegas` int(10) unsigned NOT NULL,`fechaCreacion` date DEFAULT NULL',
            'primaryKey' => 'idPermisoUsuario',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'bodegas_movimientos',
            'data'       => '`idMovimiento` bigint(20) unsigned NOT NULL AUTO_INCREMENT,`idEstadoIngreso` int(10) unsigned NOT NULL,`idBodegasIngreso` int(10) unsigned NULL DEFAULT NULL,`idBodegasEgreso` int(10) unsigned NULL DEFAULT NULL,`Creacion_fecha` date NOT NULL,`Creacion_hora` time NOT NULL,`Observaciones` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,`fecha_auto` date NOT NULL,`idUsuario` int(10) unsigned NOT NULL,`idFacturacion` bigint(20) unsigned NULL DEFAULT NULL',
            'primaryKey' => 'idMovimiento',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'bodegas_movimientos_productos',
            'data'       => '`idExistencia` bigint(20) unsigned NOT NULL AUTO_INCREMENT,`idMovimiento` bigint(20) unsigned NOT NULL,`idEstadoIngreso` int(10) unsigned NOT NULL,`idBodegas` int(10) unsigned NOT NULL,`idProducto` int(10) unsigned NOT NULL,`Number` decimal(10, 2) UNSIGNED NOT NULL',
            'primaryKey' => 'idExistencia',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'bodegas_productos_stocks',
            'data'       => '`idStocks` bigint(20) unsigned NOT NULL AUTO_INCREMENT,`idProducto` int(10) unsigned NOT NULL,`Cantidad_idBodegas_1` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_2` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_3` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_4` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_5` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_6` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_7` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_8` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_9` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_10` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_11` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_12` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_13` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_14` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_15` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_16` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_17` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_18` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_19` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_20` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_21` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_22` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_23` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_24` decimal(10, 2) NULL DEFAULT NULL,`Cantidad_idBodegas_25` decimal(10, 2) NULL DEFAULT NULL',
            'primaryKey' => 'idStocks',
            'comentario' => 'Creado desde el Instalador',
        ];

        /************************************/
        // Retorno True por defecto
        return $arrTables;

    }

    /*******************************************************************/
    // Optimizaciones de las tablas
    /*******************************************************************/
    public function optimizeTables(){

        /************************************/
        // Variables
        $arrOptimizations = array();

        /*******************************************************/
        /*            SE GENERAN LAS OPTIMIZACIONES            */
        /*******************************************************/
        $arrOptimizations[] = [
            'optimization' => 'ALTER TABLE bodegas_listado ADD INDEX idx_bodegas_estado (idEstado),ADD INDEX idx_bodegas_ciudad (idCiudad),ADD INDEX idx_bodegas_comuna (idComuna);',
            'optimization' => 'ALTER TABLE bodegas_listado_observaciones ADD INDEX idx_bodegas_obs_bodega (idBodegas),ADD INDEX idx_bodegas_obs_usuario (idUsuario),ADD INDEX idx_bodegas_obs_bodega_fecha (idBodegas, FechaCreacion);',
            'optimization' => 'ALTER TABLE bodegas_listado_permisos_usuarios ADD UNIQUE INDEX uk_bodegas_permiso_usuario (idUsuario, idBodegas),ADD INDEX idx_bodegas_permiso_bodega (idBodegas);',
            'optimization' => 'ALTER TABLE bodegas_movimientos ADD INDEX idx_mov_estado_ingreso (idEstadoIngreso),ADD INDEX idx_mov_bodega_ingreso (idBodegasIngreso),ADD INDEX idx_mov_bodega_egreso (idBodegasEgreso),ADD INDEX idx_mov_usuario (idUsuario),ADD INDEX idx_mov_facturacion (idFacturacion),ADD INDEX idx_mov_fecha_auto (fecha_auto),ADD INDEX idx_mov_creacion_fecha (Creacion_fecha),ADD INDEX idx_mov_bodega_fecha (idBodegasIngreso, Creacion_fecha),ADD INDEX idx_mov_estado_fecha (idEstadoIngreso, Creacion_fecha);',
            'optimization' => 'ALTER TABLE bodegas_movimientos_productos ADD INDEX idx_movprod_movimiento (idMovimiento),ADD INDEX idx_movprod_producto (idProducto),ADD INDEX idx_movprod_bodega (idBodegas),ADD INDEX idx_movprod_estado (idEstadoIngreso),ADD INDEX idx_movprod_bodega_producto (idBodegas, idProducto),ADD INDEX idx_movprod_producto_bodega (idProducto, idBodegas);',
        ];

        /************************************/
        // Retorno True por defecto
        return $arrOptimizations;

    }

    /*******************************************************************/
    // Mapeo de las tablas
    /*******************************************************************/
    public function mapTables(){

        $Data = '
        usuarios_listado(idUsuario,idCiudad,idComuna,idTipoUsuario,Nombre)
        core_tipos_usuario(idTipoUsuario,Nombre)
        core_estados_ingreso(idEstadoIngreso,Nombre)
        bodegas_listado(idBodegas,Nombre)
        productos_listado(idProducto,idUniMed,Nombre)
        core_unidades_medida(idUniMed,Nombre)
        core_estados(idEstado,Nombre)
        core_ubicacion_ciudad(idCiudad,Nombre)
        core_ubicacion_comunas(idComuna,idCiudad,Nombre)
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
