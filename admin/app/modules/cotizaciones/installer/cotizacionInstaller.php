<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class cotizacionInstaller extends ControllerInstaller {

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
        $this->controllerName     = 'cotizacionInstaller';
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
            'idPermisosCat'  => '4',                                              // Gestión Documentos Mercantiles
            'idEstado'       => '1',                                              // Activo
            'idTipo'         => '2',                                              // Crud Resumen
            'Nombre'         => 'Cotizaciones',                                   // Nombre de la transaccion
            'Descripcion'    => 'Permite el ingreso de los documentos de ventas', // Descripcion de la transaccion
            'idLevelLimit'   => '4',                                              // Ver / Editar / Crear / Borrar
            'RutaWeb'        => 'cotizacionListado/ventas/listado',               // Ruta web de la transaccion
            'RutaController' => 'cotizacionListado',                              // Controlador de la transaccion
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '4',                                          // Gestión Documentos Mercantiles
            'idEstado'       => '1',                                          // Activo
            'idTipo'         => '3',                                          // Informe
            'Nombre'         => 'Buscar Cotizaciones',                        // Nombre de la transaccion
            'Descripcion'    => 'Permite la búsqueda de cotizaciones',        // Descripcion de la transaccion
            'idLevelLimit'   => '1',                                          // Solo Ver
            'RutaWeb'        => 'cotizacionListado/informe/busqueda/listado', // Ruta web de la transaccion
            'RutaController' => 'informeCotizacion',                          // Controlador de la transaccion
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
                ['idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/listAll',                    'RutaController' => 'cotizacionListado->listAll',                'Descripcion' => 'Listar Toda la Información',                     'idLevelLimit' => 1, 'Controller' => 'cotizacionListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'cotizacionListado/ventas/listado/search',                     'RutaController' => 'cotizacionListado->UpdateList',             'Descripcion' => 'Filtrar datos',                                  'idLevelLimit' => 1, 'Controller' => 'cotizacionListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/updateList',                 'RutaController' => 'cotizacionListado->UpdateList',             'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'cotizacionListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/view/@id',                   'RutaController' => 'cotizacionListado->View',                   'Descripcion' => 'Mostrar Detallado',                              'idLevelLimit' => 1, 'Controller' => 'cotizacionListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/print/@id',                  'RutaController' => 'cotizacionListado->Print',                  'Descripcion' => 'Pantalla imprimir',                              'idLevelLimit' => 1, 'Controller' => 'cotizacionListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/noPrint/@id',                'RutaController' => 'cotizacionListado->noPrint',                'Descripcion' => 'Pantalla para visualizar documento',             'idLevelLimit' => 1, 'Controller' => 'cotizacionListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/resumen/@id',                'RutaController' => 'cotizacionListado->Resumen',                'Descripcion' => 'Mostrar Resúmen',                                'idLevelLimit' => 2, 'Controller' => 'cotizacionListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/resumenUpdate/@id',          'RutaController' => 'cotizacionListado->ResumenUpdate',          'Descripcion' => 'Mostrar información',                            'idLevelLimit' => 2, 'Controller' => 'cotizacionListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'cotizacionListado/ventas/listado',                            'RutaController' => 'cotizacionListado->Insert',                 'Descripcion' => 'Crear Información',                              'idLevelLimit' => 3, 'Controller' => 'cotizacionListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'cotizacionListado/ventas/listado/update',                     'RutaController' => 'cotizacionListado->Update',                 'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'cotizacionListado'],
                ['idMetodo' => 4, 'RutaWeb' => 'cotizacionListado/ventas/listado/delFiles',                   'RutaController' => 'cotizacionListado->DelFiles',               'Descripcion' => 'Permite eliminar archivos',                      'idLevelLimit' => 2, 'Controller' => 'cotizacionListado'],
                ['idMetodo' => 3, 'RutaWeb' => 'cotizacionListado/ventas/listado',                            'RutaController' => 'cotizacionListado->Delete',                 'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 4, 'Controller' => 'cotizacionListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/items/new/@id',              'RutaController' => 'cotizacionListadoItems->New',               'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoItems'],
                ['idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/items/updateList/@id',       'RutaController' => 'cotizacionListadoItems->UpdateList',        'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoItems'],
                ['idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/items/getID/@id',            'RutaController' => 'cotizacionListadoItems->GetID',             'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoItems'],
                ['idMetodo' => 2, 'RutaWeb' => 'cotizacionListado/ventas/listado/items',                      'RutaController' => 'cotizacionListadoItems->Insert',            'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoItems'],
                ['idMetodo' => 2, 'RutaWeb' => 'cotizacionListado/ventas/listado/items/update',               'RutaController' => 'cotizacionListadoItems->Update',            'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoItems'],
                ['idMetodo' => 3, 'RutaWeb' => 'cotizacionListado/ventas/listado/items',                      'RutaController' => 'cotizacionListadoItems->Delete',            'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoItems'],
                ['idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/productos/new/@id',          'RutaController' => 'cotizacionListadoProductos->New',           'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoProductos'],
                ['idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/productos/updateList/@id',   'RutaController' => 'cotizacionListadoProductos->UpdateList',    'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoProductos'],
                ['idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/productos/getID/@id',        'RutaController' => 'cotizacionListadoProductos->GetID',         'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoProductos'],
                ['idMetodo' => 2, 'RutaWeb' => 'cotizacionListado/ventas/listado/productos',                  'RutaController' => 'cotizacionListadoProductos->Insert',        'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoProductos'],
                ['idMetodo' => 2, 'RutaWeb' => 'cotizacionListado/ventas/listado/productos/update',           'RutaController' => 'cotizacionListadoProductos->Update',        'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoProductos'],
                ['idMetodo' => 3, 'RutaWeb' => 'cotizacionListado/ventas/listado/productos',                  'RutaController' => 'cotizacionListadoProductos->Delete',        'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoProductos'],
                ['idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/servicios/new/@id',          'RutaController' => 'cotizacionListadoServicios->New',           'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoServicios'],
                ['idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/servicios/updateList/@id',   'RutaController' => 'cotizacionListadoServicios->UpdateList',    'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoServicios'],
                ['idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/ventas/listado/servicios/getID/@id',        'RutaController' => 'cotizacionListadoServicios->GetID',         'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoServicios'],
                ['idMetodo' => 2, 'RutaWeb' => 'cotizacionListado/ventas/listado/servicios',                  'RutaController' => 'cotizacionListadoServicios->Insert',        'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoServicios'],
                ['idMetodo' => 2, 'RutaWeb' => 'cotizacionListado/ventas/listado/servicios/update',           'RutaController' => 'cotizacionListadoServicios->Update',        'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoServicios'],
                ['idMetodo' => 3, 'RutaWeb' => 'cotizacionListado/ventas/listado/servicios',                  'RutaController' => 'cotizacionListadoServicios->Delete',        'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'cotizacionListadoServicios'],
            ],
            2 => [
                ['idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/informe/busqueda/listado/listAll',   'RutaController' => 'informeCotizacion->listAll',    'Descripcion' => 'Filtro de búsqueda', 'idLevelLimit' => 1, 'Controller' => 'informeCotizacion'],
                ['idMetodo' => 2, 'RutaWeb' => 'cotizacionListado/informe/busqueda/listado/search',    'RutaController' => 'informeCotizacion->UpdateList', 'Descripcion' => 'Filtrar datos',      'idLevelLimit' => 1, 'Controller' => 'informeCotizacion'],
                ['idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/informe/busqueda/listado/view/@id',  'RutaController' => 'cotizacionListado->View',       'Descripcion' => 'Mostrar Detallado',  'idLevelLimit' => 1, 'Controller' => 'informeCotizacion'],
                ['idMetodo' => 1, 'RutaWeb' => 'cotizacionListado/informe/busqueda/listado/print/@id', 'RutaController' => 'cotizacionListado->Print',      'Descripcion' => 'Pantalla imprimir',  'idLevelLimit' => 1, 'Controller' => 'informeCotizacion'],
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
        $RutaController  = '"cotizacionListado"';
        $RutaController .= ',"informeCotizacion"';

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
            'optimization' => 'ALTER TABLE cotizacion_listado ADD INDEX idx_cotizacion_usuario (idUsuario),ADD INDEX idx_cotizacion_entidad (idEntidad),ADD INDEX idx_cotizacion_fecha (Creacion_fecha),ADD INDEX idx_cotizacion_fecha_auto (fecha_auto),ADD INDEX idx_cotizacion_entidad_fecha (idEntidad, Creacion_fecha),ADD INDEX idx_cotizacion_usuario_fecha (idUsuario, Creacion_fecha);',
            'optimization' => 'ALTER TABLE cotizacion_listado_items ADD INDEX idx_cotizacion_items_cotizacion (idCotizacion);',
            'optimization' => 'ALTER TABLE cotizacion_listado_productos ADD INDEX idx_cotizacion_productos_cotizacion (idCotizacion),ADD INDEX idx_cotizacion_productos_producto (idProducto),ADD INDEX idx_cotizacion_productos_cotizacion_producto (idCotizacion, idProducto);',
            'optimization' => 'ALTER TABLE cotizacion_listado_servicios ADD INDEX idx_cotizacion_servicios_cotizacion (idCotizacion),ADD INDEX idx_cotizacion_servicios_servicio (idServicio),ADD INDEX idx_cotizacion_servicios_cotizacion_servicio (idCotizacion, idServicio);',
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
