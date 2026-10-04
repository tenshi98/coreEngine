<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class gestionDocumentosInstaller extends ControllerInstaller {

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
        $this->controllerName     = 'gestionDocumentosInstaller';
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
		$entidadesInstaller = new entidadesInstaller();
		$productosInstaller = new productosInstaller();
		$serviciosInstaller = new serviciosInstaller();

        /************************************/
        // Se verifica si esta instalado
        $nData1    = $this->GetCountDataModule();
        $DepData1  = $usuariosInstaller->GetCountDataModule();
        $DepData2  = $entidadesInstaller->GetCountDataModule();
        $DepData3  = $productosInstaller->GetCountDataModule();
        $DepData4  = $serviciosInstaller->GetCountDataModule();

        /************************************/
        // Defino si etsa instalado en base a la respuesta
        $countPermisos = is_numeric($nData1)&&$nData1!=0 ? 1 : 0;
        $DepInstall_1  = is_numeric($DepData1)&&$DepData1!=0 ? 1 : 0;
        $DepInstall_2  = is_numeric($DepData2)&&$DepData2!=0 ? 1 : 0;
        $DepInstall_3  = is_numeric($DepData3)&&$DepData3!=0 ? 1 : 0;
        $DepInstall_4  = is_numeric($DepData4)&&$DepData4!=0 ? 1 : 0;

        /************************************/
        // Se crean los datos a mostrar
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
            'idPermisosCat'  => '4',                                               // Gestión Documentos Mercantiles
            'idEstado'       => '1',                                               // Activo
            'idTipo'         => '2',                                               // Crud Resumen
            'Nombre'         => 'Compras',                                         // Nombre de la transaccion
            'Descripcion'    => 'Permite el ingreso de los documentos de compras', // Descripcion de la transaccion
            'idLevelLimit'   => '4',                                               // Ver / Editar / Crear / Borrar
            'RutaWeb'        => 'gestionDocumentos/compras/listado',               // Ruta web de la transaccion
            'RutaController' => 'gestionDocumentosCompras',                        // Controlador de la transaccion
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '4',                                              // Gestión Documentos Mercantiles
            'idEstado'       => '1',                                              // Activo
            'idTipo'         => '2',                                              // Crud Resumen
            'Nombre'         => 'Ventas',                                         // Nombre de la transaccion
            'Descripcion'    => 'Permite el ingreso de los documentos de ventas', // Descripcion de la transaccion
            'idLevelLimit'   => '4',                                              // Ver / Editar / Crear / Borrar
            'RutaWeb'        => 'gestionDocumentos/ventas/listado',               // Ruta web de la transaccion
            'RutaController' => 'gestionDocumentosVentas',                        // Controlador de la transaccion
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '4',                                          // Gestión Documentos Mercantiles
            'idEstado'       => '1',                                          // Activo
            'idTipo'         => '3',                                          // Informe
            'Nombre'         => 'Buscar Documentos',                          // Nombre de la transaccion
            'Descripcion'    => 'Permite la búsqueda de documentos',          // Descripcion de la transaccion
            'idLevelLimit'   => '1',                                          // Solo Ver
            'RutaWeb'        => 'gestionDocumentos/informe/busqueda/listado', // Ruta web de la transaccion
            'RutaController' => 'informeDocumentos',                          // Controlador de la transaccion
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
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/listAll',                    'RutaController' => 'gestionDocumentos->listAll_1',              'Descripcion' => 'Listar Toda la Información',                     'idLevelLimit' => 1, 'Controller' => 'gestionDocumentos'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/compras/listado/search',                     'RutaController' => 'gestionDocumentos->UpdateList_1',           'Descripcion' => 'Filtrar datos',                                  'idLevelLimit' => 1, 'Controller' => 'gestionDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/updateList',                 'RutaController' => 'gestionDocumentos->UpdateList_1',           'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'gestionDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/view/@id',                   'RutaController' => 'gestionDocumentos->View_1',                 'Descripcion' => 'Mostrar Detallado',                              'idLevelLimit' => 1, 'Controller' => 'gestionDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/print/@id',                  'RutaController' => 'gestionDocumentos->Print_1',                'Descripcion' => 'Pantalla imprimir',                              'idLevelLimit' => 1, 'Controller' => 'gestionDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/noPrint/@id',                'RutaController' => 'gestionDocumentos->noPrint_1',              'Descripcion' => 'Pantalla para visualizar documento',             'idLevelLimit' => 1, 'Controller' => 'gestionDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/resumen/@id',                'RutaController' => 'gestionDocumentos->Resumen_1',              'Descripcion' => 'Mostrar Resúmen',                                'idLevelLimit' => 2, 'Controller' => 'gestionDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/resumenUpdate/@id',          'RutaController' => 'gestionDocumentos->ResumenUpdate_1',        'Descripcion' => 'Mostrar información',                            'idLevelLimit' => 2, 'Controller' => 'gestionDocumentos'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/compras/listado',                            'RutaController' => 'gestionDocumentos->Insert',                 'Descripcion' => 'Crear Información',                              'idLevelLimit' => 3, 'Controller' => 'gestionDocumentos'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/compras/listado/update',                     'RutaController' => 'gestionDocumentos->Update',                 'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'gestionDocumentos'],
                ['idMetodo' => 4, 'RutaWeb' => 'gestionDocumentos/compras/listado/delFiles',                   'RutaController' => 'gestionDocumentos->DelFiles',               'Descripcion' => 'Permite eliminar archivos',                      'idLevelLimit' => 2, 'Controller' => 'gestionDocumentos'],
                ['idMetodo' => 3, 'RutaWeb' => 'gestionDocumentos/compras/listado',                            'RutaController' => 'gestionDocumentos->Delete',                 'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 4, 'Controller' => 'gestionDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/items/new/@id',              'RutaController' => 'gestionDocumentosItems->New_1',             'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosItems'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/items/updateList/@id',       'RutaController' => 'gestionDocumentosItems->UpdateList_1',      'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosItems'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/items/getID/@id',            'RutaController' => 'gestionDocumentosItems->GetID_1',           'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosItems'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/compras/listado/items',                      'RutaController' => 'gestionDocumentosItems->Insert',            'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosItems'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/compras/listado/items/update',               'RutaController' => 'gestionDocumentosItems->Update',            'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosItems'],
                ['idMetodo' => 3, 'RutaWeb' => 'gestionDocumentos/compras/listado/items',                      'RutaController' => 'gestionDocumentosItems->Delete',            'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosItems'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/productos/new/@id',          'RutaController' => 'gestionDocumentosProductos->New_1',         'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosProductos'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/productos/updateList/@id',   'RutaController' => 'gestionDocumentosProductos->UpdateList_1',  'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosProductos'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/productos/getID/@id',        'RutaController' => 'gestionDocumentosProductos->GetID_1',       'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosProductos'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/compras/listado/productos',                  'RutaController' => 'gestionDocumentosProductos->Insert',        'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosProductos'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/compras/listado/productos/update',           'RutaController' => 'gestionDocumentosProductos->Update',        'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosProductos'],
                ['idMetodo' => 3, 'RutaWeb' => 'gestionDocumentos/compras/listado/productos',                  'RutaController' => 'gestionDocumentosProductos->Delete',        'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosProductos'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/servicios/new/@id',          'RutaController' => 'gestionDocumentosServicios->New_1',         'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosServicios'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/servicios/updateList/@id',   'RutaController' => 'gestionDocumentosServicios->UpdateList_1',  'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosServicios'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/servicios/getID/@id',        'RutaController' => 'gestionDocumentosServicios->GetID_1',       'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosServicios'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/compras/listado/servicios',                  'RutaController' => 'gestionDocumentosServicios->Insert',        'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosServicios'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/compras/listado/servicios/update',           'RutaController' => 'gestionDocumentosServicios->Update',        'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosServicios'],
                ['idMetodo' => 3, 'RutaWeb' => 'gestionDocumentos/compras/listado/servicios',                  'RutaController' => 'gestionDocumentosServicios->Delete',        'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosServicios'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/guias/new/@id',              'RutaController' => 'gestionDocumentosGuias->New_1',             'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosGuias'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/guias/updateList/@id',       'RutaController' => 'gestionDocumentosGuias->UpdateList_1',      'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosGuias'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/compras/listado/guias',                      'RutaController' => 'gestionDocumentosGuias->Insert',            'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosGuias'],
                ['idMetodo' => 3, 'RutaWeb' => 'gestionDocumentos/compras/listado/guias',                      'RutaController' => 'gestionDocumentosGuias->Delete',            'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosGuias'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/pagos/new/@id',              'RutaController' => 'gestionDocumentosPagos->New_1',             'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosPagos'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/pagos/updateList/@id',       'RutaController' => 'gestionDocumentosPagos->UpdateList_1',      'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosPagos'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/compras/listado/pagos/getID/@id',            'RutaController' => 'gestionDocumentosPagos->GetID_1',           'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosPagos'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/compras/listado/pagos',                      'RutaController' => 'gestionDocumentosPagos->Insert',            'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosPagos'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/compras/listado/pagos/update',               'RutaController' => 'gestionDocumentosPagos->Update',            'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosPagos'],
                ['idMetodo' => 3, 'RutaWeb' => 'gestionDocumentos/compras/listado/pagos',                      'RutaController' => 'gestionDocumentosPagos->Delete',            'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosPagos'],
            ],
            2 => [
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/listAll',                    'RutaController' => 'gestionDocumentos->listAll_2',              'Descripcion' => 'Listar Toda la Información',                     'idLevelLimit' => 1, 'Controller' => 'gestionDocumentos'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/ventas/listado/search',                     'RutaController' => 'gestionDocumentos->UpdateList_2',           'Descripcion' => 'Filtrar datos',                                  'idLevelLimit' => 1, 'Controller' => 'gestionDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/updateList',                 'RutaController' => 'gestionDocumentos->UpdateList_2',           'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'gestionDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/view/@id',                   'RutaController' => 'gestionDocumentos->View_2',                 'Descripcion' => 'Mostrar Detallado',                              'idLevelLimit' => 1, 'Controller' => 'gestionDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/print/@id',                  'RutaController' => 'gestionDocumentos->Print_2',                'Descripcion' => 'Pantalla imprimir',                              'idLevelLimit' => 1, 'Controller' => 'gestionDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/noPrint/@id',                'RutaController' => 'gestionDocumentos->noPrint_2',              'Descripcion' => 'Pantalla para visualizar documento',             'idLevelLimit' => 1, 'Controller' => 'gestionDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/noPrintDoc/@id',             'RutaController' => 'gestionDocumentos->noPrintDoc',             'Descripcion' => 'Pantalla para visualizar documento',             'idLevelLimit' => 1, 'Controller' => 'gestionDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/resumen/@id',                'RutaController' => 'gestionDocumentos->Resumen_2',              'Descripcion' => 'Mostrar Resúmen',                                'idLevelLimit' => 2, 'Controller' => 'gestionDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/resumenUpdate/@id',          'RutaController' => 'gestionDocumentos->ResumenUpdate_2',        'Descripcion' => 'Mostrar información',                            'idLevelLimit' => 2, 'Controller' => 'gestionDocumentos'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/ventas/listado',                            'RutaController' => 'gestionDocumentos->Insert',                 'Descripcion' => 'Crear Información',                              'idLevelLimit' => 3, 'Controller' => 'gestionDocumentos'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/ventas/listado/update',                     'RutaController' => 'gestionDocumentos->Update',                 'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'gestionDocumentos'],
                ['idMetodo' => 4, 'RutaWeb' => 'gestionDocumentos/ventas/listado/delFiles',                   'RutaController' => 'gestionDocumentos->DelFiles',               'Descripcion' => 'Permite eliminar archivos',                      'idLevelLimit' => 2, 'Controller' => 'gestionDocumentos'],
                ['idMetodo' => 3, 'RutaWeb' => 'gestionDocumentos/ventas/listado',                            'RutaController' => 'gestionDocumentos->Delete',                 'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 4, 'Controller' => 'gestionDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/items/new/@id',              'RutaController' => 'gestionDocumentosItems->New_2',             'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosItems'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/items/updateList/@id',       'RutaController' => 'gestionDocumentosItems->UpdateList_2',      'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosItems'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/items/getID/@id',            'RutaController' => 'gestionDocumentosItems->GetID_2',           'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosItems'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/ventas/listado/items',                      'RutaController' => 'gestionDocumentosItems->Insert',            'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosItems'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/ventas/listado/items/update',               'RutaController' => 'gestionDocumentosItems->Update',            'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosItems'],
                ['idMetodo' => 3, 'RutaWeb' => 'gestionDocumentos/ventas/listado/items',                      'RutaController' => 'gestionDocumentosItems->Delete',            'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosItems'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/productos/new/@id',          'RutaController' => 'gestionDocumentosProductos->New_2',         'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosProductos'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/productos/updateList/@id',   'RutaController' => 'gestionDocumentosProductos->UpdateList_2',  'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosProductos'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/productos/getID/@id',        'RutaController' => 'gestionDocumentosProductos->GetID_2',       'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosProductos'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/ventas/listado/productos',                  'RutaController' => 'gestionDocumentosProductos->Insert',        'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosProductos'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/ventas/listado/productos/update',           'RutaController' => 'gestionDocumentosProductos->Update',        'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosProductos'],
                ['idMetodo' => 3, 'RutaWeb' => 'gestionDocumentos/ventas/listado/productos',                  'RutaController' => 'gestionDocumentosProductos->Delete',        'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosProductos'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/servicios/new/@id',          'RutaController' => 'gestionDocumentosServicios->New_2',         'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosServicios'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/servicios/updateList/@id',   'RutaController' => 'gestionDocumentosServicios->UpdateList_2',  'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosServicios'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/servicios/getID/@id',        'RutaController' => 'gestionDocumentosServicios->GetID_2',       'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosServicios'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/ventas/listado/servicios',                  'RutaController' => 'gestionDocumentosServicios->Insert',        'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosServicios'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/ventas/listado/servicios/update',           'RutaController' => 'gestionDocumentosServicios->Update',        'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosServicios'],
                ['idMetodo' => 3, 'RutaWeb' => 'gestionDocumentos/ventas/listado/servicios',                  'RutaController' => 'gestionDocumentosServicios->Delete',        'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosServicios'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/guias/new/@id',              'RutaController' => 'gestionDocumentosGuias->New_1',             'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosGuias'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/guias/updateList/@id',       'RutaController' => 'gestionDocumentosGuias->UpdateList_1',      'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosGuias'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/ventas/listado/guias',                      'RutaController' => 'gestionDocumentosGuias->Insert',            'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosGuias'],
                ['idMetodo' => 3, 'RutaWeb' => 'gestionDocumentos/ventas/listado/guias',                      'RutaController' => 'gestionDocumentosGuias->Delete',            'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosGuias'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/pagos/new/@id',              'RutaController' => 'gestionDocumentosPagos->New_2',             'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosPagos'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/pagos/updateList/@id',       'RutaController' => 'gestionDocumentosPagos->UpdateList_2',      'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosPagos'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/ventas/listado/pagos/getID/@id',            'RutaController' => 'gestionDocumentosPagos->GetID_2',           'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosPagos'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/ventas/listado/pagos',                      'RutaController' => 'gestionDocumentosPagos->Insert',            'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosPagos'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/ventas/listado/pagos/update',               'RutaController' => 'gestionDocumentosPagos->Update',            'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosPagos'],
                ['idMetodo' => 3, 'RutaWeb' => 'gestionDocumentos/ventas/listado/pagos',                      'RutaController' => 'gestionDocumentosPagos->Delete',            'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosPagos'],
            ],
            3 => [
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/informe/busqueda/listado/listAll',   'RutaController' => 'informeDocumentos->listAll',      'Descripcion' => 'Filtro de búsqueda', 'idLevelLimit' => 1, 'Controller' => 'informeDocumentos'],
                ['idMetodo' => 2, 'RutaWeb' => 'gestionDocumentos/informe/busqueda/listado/search',    'RutaController' => 'informeDocumentos->UpdateList',   'Descripcion' => 'Filtrar datos',      'idLevelLimit' => 1, 'Controller' => 'informeDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/informe/busqueda/listado/view/@id',  'RutaController' => 'gestionDocumentos->View_0',       'Descripcion' => 'Mostrar Detallado',  'idLevelLimit' => 1, 'Controller' => 'informeDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentos/informe/busqueda/listado/print/@id', 'RutaController' => 'gestionDocumentos->Print_0',      'Descripcion' => 'Pantalla imprimir',  'idLevelLimit' => 1, 'Controller' => 'informeDocumentos'],
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
        $RutaController  = '"gestionDocumentosCompras"';
        $RutaController .= ',"gestionDocumentosVentas"';
        $RutaController .= ',"informeDocumentos"';

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
            'table'      => 'facturacion_listado',
            'data'       => '`idFacturacion` bigint(20) unsigned NOT NULL AUTO_INCREMENT,`idUsuario` int(10) unsigned NOT NULL,`idTipo` int(10) unsigned NOT NULL,`idEntidad` int(10) unsigned NOT NULL,`idBodegasIngreso` int(10) unsigned NULL DEFAULT NULL,`idBodegasEgreso` int(10) unsigned NULL DEFAULT NULL,`fecha_auto` date NOT NULL,`idDocumentos` int(10) unsigned NOT NULL,`N_Doc` varchar(60) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Creacion_fecha` date NOT NULL,`Creacion_Semana` int(10) unsigned NULL DEFAULT NULL,`Creacion_mes` int(10) unsigned NULL DEFAULT NULL,`Creacion_ano` int(10) unsigned NULL DEFAULT NULL,`Creacion_hora` time NULL DEFAULT NULL,`Observaciones` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,`ValorNeto` decimal(15, 2) UNSIGNED NULL DEFAULT NULL,`IVA` decimal(15, 2) UNSIGNED NULL DEFAULT NULL,`ValorTotal` decimal(15, 2) UNSIGNED NULL DEFAULT NULL,`TotalItems` decimal(15, 2) UNSIGNED NULL DEFAULT NULL,`TotalProductos` decimal(15, 2) UNSIGNED NULL DEFAULT NULL,`TotalServicios` decimal(15, 2) UNSIGNED NULL DEFAULT NULL,`TotalGuias` decimal(15, 2) UNSIGNED NULL DEFAULT NULL,`idEstadoPago` int(10) unsigned NOT NULL,`MontoPagado` decimal(15, 2) UNSIGNED NULL DEFAULT NULL',
            'primaryKey' => 'idFacturacion',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'facturacion_listado_guias',
            'data'       => '`idExistencia` bigint(20) unsigned NOT NULL AUTO_INCREMENT,`idFacturacion` bigint(20) unsigned NOT NULL,`idFacturacionRel` bigint(20) unsigned NOT NULL',
            'primaryKey' => 'idExistencia',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'facturacion_listado_items',
            'data'       => '`idExistencia` bigint(20) unsigned NOT NULL AUTO_INCREMENT,`idFacturacion` bigint(20) unsigned NOT NULL,`Item` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`Number` decimal(10, 2) UNSIGNED NULL DEFAULT NULL,`ValorTotal` decimal(15, 2) UNSIGNED NOT NULL',
            'primaryKey' => 'idExistencia',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'facturacion_listado_pagos',
            'data'       => '`idPago` bigint(20) unsigned NOT NULL AUTO_INCREMENT,`idFacturacion` bigint(20) unsigned NOT NULL,`idUsuario` int(10) unsigned NOT NULL,`idDocumentoPago` int(10) unsigned NOT NULL,`N_Doc` varchar(60) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`MontoPagado` decimal(15, 2) UNSIGNED NOT NULL,`FechaPago` date NOT NULL',
            'primaryKey' => 'idPago',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'facturacion_listado_productos',
            'data'       => '`idExistencia` bigint(20) unsigned NOT NULL AUTO_INCREMENT,`idFacturacion` bigint(20) unsigned NOT NULL,`idEstadoIngreso` int(10) unsigned NOT NULL,`idBodegas` int(10) unsigned NOT NULL,`idProducto` int(10) unsigned NOT NULL,`Number` decimal(10, 2) UNSIGNED NULL DEFAULT NULL,`ValorTotal` decimal(15, 2) UNSIGNED NOT NULL',
            'primaryKey' => 'idExistencia',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'facturacion_listado_servicios',
            'data'       => '`idExistencia` bigint(20) unsigned NOT NULL AUTO_INCREMENT,`idFacturacion` bigint(20) unsigned NOT NULL,`idServicio` int(10) unsigned NOT NULL,`Number` decimal(10, 2) UNSIGNED NULL DEFAULT NULL,`ValorTotal` decimal(15, 2) UNSIGNED NOT NULL',
            'primaryKey' => 'idExistencia',
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
        entidades_listado(idEntidad,idSector,idSexo,idTipoEntidad,Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Rut,idCiudad,idComuna,Direccion,FNacimiento,Email,Fono1,Fono2,Web,Giro,RepLegalNombre,RepLegalRut,RepLegalEmail,RepLegalFono1,RepLegalFono2)
        core_tipos_entidades(idTipoEntidad,Nombre)
        entidades_sectores(idSector,Nombre)
        core_facturacion_tipo(idTipo,Nombre)
        bodegas_listado(idBodegas,Nombre)
        core_documentos_mercantiles(idDocumentos,Nombre)
        core_estados_pago(idEstadoPago,Nombre)
        productos_listado(idProducto,idUniMed,Nombre)
        core_unidades_medida(idUniMed,Nombre)
        core_documentos_pago(idDocumentoPago,Nombre)
        core_estados_ingreso(idEstadoIngreso,Nombre)
        servicios_listado(idServicio,Nombre)
        core_sexo(idSexo,Nombre)
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
