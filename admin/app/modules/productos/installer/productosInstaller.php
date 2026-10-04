<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class productosInstaller extends ControllerInstaller {

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
        $this->controllerName = 'productosInstaller';
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
        // Se verifica si esta instalado
        $nData1    = $this->GetCountDataModule();

        /************************************/
        // Defino si etsa instalado en base a la respuesta
        $countPermisos = is_numeric($nData1)&&$nData1!=0 ? 1 : 0;

        /************************************/
        // Se crean los datos a mostrar
        $arrData = [
            'Nombre'        => 'Módulo de Gestión de Productos',
            'Descripcion'   => 'Módulo para gestionar los Productos',
            'Controller'    => $this->controllerName,
            'countPermisos' => $countPermisos,
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
            'idPermisosCat'  => '1',                                                            // Administración
            'idEstado'       => '1',                                                            // Activo
            'idTipo'         => '1',                                                            // Crud Normal
            'Nombre'         => 'Productos - Categorias',                                       // Nombre de la transaccion
            'Descripcion'    => 'Permite la administración de las categorías de los productos', // Descripcion de la transaccion
            'idLevelLimit'   => '4',                                                            // Ver / Editar / Crear / Borrar
            'RutaWeb'        => 'administracion/productos/categorias',                          // Ruta web de la transaccion
            'RutaController' => 'productosCategorias',                                          // Controlador de la transaccion
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '1',                                                   // Administración
            'idEstado'       => '1',                                                   // Activo
            'idTipo'         => '1',                                                   // Crud Normal
            'Nombre'         => 'Productos - Tipos',                                   // Nombre de la transaccion
            'Descripcion'    => 'Permite la administración de los tipos de productos', // Descripcion de la transaccion
            'idLevelLimit'   => '4',                                                   // Ver / Editar / Crear / Borrar
            'RutaWeb'        => 'administracion/productos/tipos',                      // Ruta web de la transaccion
            'RutaController' => 'productosTipos',                                      // Controlador de la transaccion
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '1',                                          // Administración
            'idEstado'       => '1',                                          // Activo
            'idTipo'         => '2',                                          // Crud Resumen
            'Nombre'         => 'Productos - Listado',                        // Nombre de la transaccion
            'Descripcion'    => 'Permite la administración de los productos', // Descripcion de la transaccion
            'idLevelLimit'   => '4',                                          // Ver / Editar / Crear / Borrar
            'RutaWeb'        => 'administracion/productos/listado',           // Ruta web de la transaccion
            'RutaController' => 'productosListado',                           // Controlador de la transaccion
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
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/productos/categorias/listAll',      'RutaController' => 'productosCategorias->listAll',      'Descripcion' => 'Listar Toda la Información',                     'idLevelLimit' => 1, 'Controller' => 'productosCategorias'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/productos/categorias/search',       'RutaController' => 'productosCategorias->UpdateList',   'Descripcion' => 'Filtrar datos',                                  'idLevelLimit' => 1, 'Controller' => 'productosCategorias'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/productos/categorias/updateList',   'RutaController' => 'productosCategorias->UpdateList',   'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'productosCategorias'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/productos/categorias/view/@id',     'RutaController' => 'productosCategorias->View',         'Descripcion' => 'Mostrar Detallado',                              'idLevelLimit' => 1, 'Controller' => 'productosCategorias'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/productos/categorias/getID/@id',    'RutaController' => 'productosCategorias->GetID',        'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'productosCategorias'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/productos/categorias',              'RutaController' => 'productosCategorias->Insert',       'Descripcion' => 'Crear Información',                              'idLevelLimit' => 3, 'Controller' => 'productosCategorias'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/productos/categorias/update',       'RutaController' => 'productosCategorias->Update',       'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'productosCategorias'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/productos/categorias',              'RutaController' => 'productosCategorias->Delete',       'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 4, 'Controller' => 'productosCategorias'],
            ],
            2 => [
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/productos/tipos/listAll',      'RutaController' => 'productosTipos->listAll',      'Descripcion' => 'Listar Toda la Información',                     'idLevelLimit' => 1, 'Controller' => 'productosTipos'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/productos/tipos/search',       'RutaController' => 'productosTipos->UpdateList',   'Descripcion' => 'Filtrar datos',                                  'idLevelLimit' => 1, 'Controller' => 'productosTipos'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/productos/tipos/updateList',   'RutaController' => 'productosTipos->UpdateList',   'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'productosTipos'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/productos/tipos/view/@id',     'RutaController' => 'productosTipos->View',         'Descripcion' => 'Mostrar Detallado',                              'idLevelLimit' => 1, 'Controller' => 'productosTipos'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/productos/tipos/getID/@id',    'RutaController' => 'productosTipos->GetID',        'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'productosTipos'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/productos/tipos',              'RutaController' => 'productosTipos->Insert',       'Descripcion' => 'Crear Información',                              'idLevelLimit' => 3, 'Controller' => 'productosTipos'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/productos/tipos/update',       'RutaController' => 'productosTipos->Update',       'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'productosTipos'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/productos/tipos',              'RutaController' => 'productosTipos->Delete',       'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 4, 'Controller' => 'productosTipos'],
            ],
            3 => [
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/productos/listado/listAll',                         'RutaController' => 'productosListado->listAll',                   'Descripcion' => 'Listar Toda la Información',                      'idLevelLimit' => 1, 'Controller' => 'productosListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/productos/listado/search',                          'RutaController' => 'productosListado->UpdateList',                'Descripcion' => 'Filtrar datos',                                   'idLevelLimit' => 1, 'Controller' => 'productosListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/productos/listado/updateList',                      'RutaController' => 'productosListado->UpdateList',                'Descripcion' => 'Actualizar Lista',                                'idLevelLimit' => 2, 'Controller' => 'productosListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/productos/listado/view/@id',                        'RutaController' => 'productosListado->View',                      'Descripcion' => 'Mostrar Detallado',                               'idLevelLimit' => 1, 'Controller' => 'productosListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/productos/listado/resumen/@id',                     'RutaController' => 'productosListado->Resumen',                   'Descripcion' => 'Mostrar Resúmen',                                 'idLevelLimit' => 2, 'Controller' => 'productosListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/productos/listado/resumenUpdate/@id',               'RutaController' => 'productosListado->ResumenUpdate',             'Descripcion' => 'Mostrar información',                             'idLevelLimit' => 2, 'Controller' => 'productosListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/productos/listado',                                 'RutaController' => 'productosListado->Insert',                    'Descripcion' => 'Crear Información',                               'idLevelLimit' => 3, 'Controller' => 'productosListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/productos/listado/update',                          'RutaController' => 'productosListado->Update',                    'Descripcion' => 'Editar por post (modificar y subir archivos)',    'idLevelLimit' => 2, 'Controller' => 'productosListado'],
                ['idMetodo' => 4, 'RutaWeb' => 'administracion/productos/listado/delFiles',                        'RutaController' => 'productosListado->DelFiles',                  'Descripcion' => 'Permite eliminar archivos',                       'idLevelLimit' => 2, 'Controller' => 'productosListado'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/productos/listado',                                 'RutaController' => 'productosListado->Delete',                    'Descripcion' => 'Borrar dato y archivos',                          'idLevelLimit' => 4, 'Controller' => 'productosListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/productos/listado/observaciones/new/@id',           'RutaController' => 'productosListadoObservaciones->New',          'Descripcion' => 'Mostrar modal nuevo',                             'idLevelLimit' => 2, 'Controller' => 'productosListadoObservaciones'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/productos/listado/observaciones/updateList/@id',    'RutaController' => 'productosListadoObservaciones->UpdateList',   'Descripcion' => 'Actualizar Lista',                                'idLevelLimit' => 2, 'Controller' => 'productosListadoObservaciones'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/productos/listado/observaciones/view/@id',          'RutaController' => 'productosListadoObservaciones->View',         'Descripcion' => 'Mostrar Detallado',                               'idLevelLimit' => 2, 'Controller' => 'productosListadoObservaciones'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/productos/listado/observaciones/getID/@id',         'RutaController' => 'productosListadoObservaciones->GetID',        'Descripcion' => 'Información para el formulario edición',          'idLevelLimit' => 2, 'Controller' => 'productosListadoObservaciones'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/productos/listado/observaciones',                   'RutaController' => 'productosListadoObservaciones->Insert',       'Descripcion' => 'Crear Información',                               'idLevelLimit' => 2, 'Controller' => 'productosListadoObservaciones'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/productos/listado/observaciones/update',            'RutaController' => 'productosListadoObservaciones->Update',       'Descripcion' => 'Editar por post (modificar y subir archivos)',    'idLevelLimit' => 2, 'Controller' => 'productosListadoObservaciones'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/productos/listado/observaciones',                   'RutaController' => 'productosListadoObservaciones->Delete',       'Descripcion' => 'Borrar dato y archivos',                          'idLevelLimit' => 2, 'Controller' => 'productosListadoObservaciones'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/productos/listado/documentos/new/@id',              'RutaController' => 'productosListadoDocumentos->New',             'Descripcion' => 'Mostrar modal nuevo',                             'idLevelLimit' => 2, 'Controller' => 'productosListadoDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/productos/listado/documentos/updateList/@id',       'RutaController' => 'productosListadoDocumentos->UpdateList',      'Descripcion' => 'Actualizar Lista',                                'idLevelLimit' => 2, 'Controller' => 'productosListadoDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/productos/listado/documentos/view/@id',             'RutaController' => 'productosListadoDocumentos->View',            'Descripcion' => 'Mostrar Detallado',                               'idLevelLimit' => 2, 'Controller' => 'productosListadoDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/productos/listado/documentos/getID/@id',            'RutaController' => 'productosListadoDocumentos->GetID',           'Descripcion' => 'Información para el formulario edición',          'idLevelLimit' => 2, 'Controller' => 'productosListadoDocumentos'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/productos/listado/documentos',                      'RutaController' => 'productosListadoDocumentos->Insert',          'Descripcion' => 'Crear Información',                               'idLevelLimit' => 2, 'Controller' => 'productosListadoDocumentos'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/productos/listado/documentos/update',               'RutaController' => 'productosListadoDocumentos->Update',          'Descripcion' => 'Editar por post (modificar y subir archivos)',    'idLevelLimit' => 2, 'Controller' => 'productosListadoDocumentos'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/productos/listado/documentos',                      'RutaController' => 'productosListadoDocumentos->Delete',          'Descripcion' => 'Borrar dato y archivos',                          'idLevelLimit' => 2, 'Controller' => 'productosListadoDocumentos'],
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
        $RutaController  = '"productosCategorias"';
        $RutaController .= ',"productosTipos"';
        $RutaController .= ',"productosListado"';
        $RutaController .= ',"productosListadoDocumentos"';
        $RutaController .= ',"productosListadoObservaciones"';

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
            'table'      => 'productos_categorias',
            'data'       => '`idCategoria` int(10) unsigned NOT NULL AUTO_INCREMENT,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL',
            'primaryKey' => 'idCategoria',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'productos_tipos',
            'data'       => '`idTipoProducto` int(10) unsigned NOT NULL AUTO_INCREMENT,  `Nombre` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL',
            'primaryKey' => 'idTipoProducto',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'productos_listado',
            'data'       => '`idProducto` int(10) unsigned NOT NULL AUTO_INCREMENT,`idEstado` int(10) unsigned NOT NULL,`idTipoProducto` int(10) unsigned NOT NULL,`idCategoria` int(10) unsigned NOT NULL,`idUniMed` int(10) unsigned NOT NULL,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`Marca` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`StockLimite` decimal(10, 2) UNSIGNED NULL DEFAULT NULL,`ValorIngreso` decimal(10, 2) UNSIGNED NULL DEFAULT NULL,`ValorEgreso` decimal(10, 2) UNSIGNED NULL DEFAULT NULL,`Descripcion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,`Codigo` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Direccion_img` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL',
            'primaryKey' => 'idProducto',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'productos_listado_documentos',
            'data'       => '`idDocumentos` int(10) unsigned NOT NULL AUTO_INCREMENT,`idProducto` int(10) unsigned NOT NULL,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`NombreArchivo` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Observacion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,`FVencimiento` date NULL DEFAULT NULL',
            'primaryKey' => 'idDocumentos',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'productos_listado_observaciones',
            'data'       => '`idObservaciones` int(10) unsigned NOT NULL AUTO_INCREMENT,`idUsuario` int(10) unsigned NOT NULL,`idProducto` int(10) unsigned NOT NULL,`Observacion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`FechaCreacion` date NOT NULL',
            'primaryKey' => 'idObservaciones',
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
        core_estados(idEstado,Nombre)
        core_unidades_medida(idUniMed,Nombre)
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
