<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class serviciosInstaller extends ControllerInstaller {

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
        $this->controllerName = 'serviciosInstaller';
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
            'Nombre'        => 'Módulo de Gestión de Servicios',
            'Descripcion'   => 'Módulo para gestionar los Servicios',
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
            'Nombre'         => 'Servicios - Categorias',                                       // Nombre de la transaccion
            'Descripcion'    => 'Permite la administración de las categorías de los servicios', // Descripcion de la transaccion
            'idLevelLimit'   => '4',                                                            // Ver / Editar / Crear / Borrar
            'RutaWeb'        => 'administracion/servicios/categorias',                          // Ruta web de la transaccion
            'RutaController' => 'serviciosCategorias',                                          // Controlador de la transaccion
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '1',                                          // Administración
            'idEstado'       => '1',                                          // Activo
            'idTipo'         => '2',                                          // Crud Resumen
            'Nombre'         => 'Servicios - Listado',                        // Nombre de la transaccion
            'Descripcion'    => 'Permite la administración de los servicios', // Descripcion de la transaccion
            'idLevelLimit'   => '4',                                          // Ver / Editar / Crear / Borrar
            'RutaWeb'        => 'administracion/servicios/listado',           // Ruta web de la transaccion
            'RutaController' => 'serviciosListado',                           // Controlador de la transaccion
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
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/servicios/categorias/listAll',      'RutaController' => 'serviciosCategorias->listAll',      'Descripcion' => 'Listar Toda la Información',                     'idLevelLimit' => 1, 'Controller' => 'serviciosCategorias'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/servicios/categorias/search',       'RutaController' => 'serviciosCategorias->UpdateList',   'Descripcion' => 'Filtrar datos',                                  'idLevelLimit' => 1, 'Controller' => 'serviciosCategorias'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/servicios/categorias/updateList',   'RutaController' => 'serviciosCategorias->UpdateList',   'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'serviciosCategorias'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/servicios/categorias/view/@id',     'RutaController' => 'serviciosCategorias->View',         'Descripcion' => 'Mostrar Detallado',                              'idLevelLimit' => 1, 'Controller' => 'serviciosCategorias'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/servicios/categorias/getID/@id',    'RutaController' => 'serviciosCategorias->GetID',        'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'serviciosCategorias'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/servicios/categorias',              'RutaController' => 'serviciosCategorias->Insert',       'Descripcion' => 'Crear Información',                              'idLevelLimit' => 3, 'Controller' => 'serviciosCategorias'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/servicios/categorias/update',       'RutaController' => 'serviciosCategorias->Update',       'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'serviciosCategorias'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/servicios/categorias',              'RutaController' => 'serviciosCategorias->Delete',       'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 4, 'Controller' => 'serviciosCategorias'],
            ],
            2 => [
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/servicios/listado/listAll',                         'RutaController' => 'serviciosListado->listAll',                   'Descripcion' => 'Listar Toda la Información',                      'idLevelLimit' => 1, 'Controller' => 'serviciosListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/servicios/listado/search',                          'RutaController' => 'serviciosListado->UpdateList',                'Descripcion' => 'Filtrar datos',                                   'idLevelLimit' => 1, 'Controller' => 'serviciosListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/servicios/listado/updateList',                      'RutaController' => 'serviciosListado->UpdateList',                'Descripcion' => 'Actualizar Lista',                                'idLevelLimit' => 2, 'Controller' => 'serviciosListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/servicios/listado/view/@id',                        'RutaController' => 'serviciosListado->View',                      'Descripcion' => 'Mostrar Detallado',                               'idLevelLimit' => 1, 'Controller' => 'serviciosListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/servicios/listado/resumen/@id',                     'RutaController' => 'serviciosListado->Resumen',                   'Descripcion' => 'Mostrar Resúmen',                                 'idLevelLimit' => 2, 'Controller' => 'serviciosListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/servicios/listado/resumenUpdate/@id',               'RutaController' => 'serviciosListado->ResumenUpdate',             'Descripcion' => 'Mostrar información',                             'idLevelLimit' => 2, 'Controller' => 'serviciosListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/servicios/listado',                                 'RutaController' => 'serviciosListado->Insert',                    'Descripcion' => 'Crear Información',                               'idLevelLimit' => 3, 'Controller' => 'serviciosListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/servicios/listado/update',                          'RutaController' => 'serviciosListado->Update',                    'Descripcion' => 'Editar por post (modificar y subir archivos)',    'idLevelLimit' => 2, 'Controller' => 'serviciosListado'],
                ['idMetodo' => 4, 'RutaWeb' => 'administracion/servicios/listado/delFiles',                        'RutaController' => 'serviciosListado->DelFiles',                  'Descripcion' => 'Permite eliminar archivos',                       'idLevelLimit' => 2, 'Controller' => 'serviciosListado'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/servicios/listado',                                 'RutaController' => 'serviciosListado->Delete',                    'Descripcion' => 'Borrar dato y archivos',                          'idLevelLimit' => 4, 'Controller' => 'serviciosListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/servicios/listado/observaciones/new/@id',           'RutaController' => 'serviciosListadoObservaciones->New',          'Descripcion' => 'Mostrar modal nuevo',                             'idLevelLimit' => 2, 'Controller' => 'serviciosListadoObservaciones'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/servicios/listado/observaciones/updateList/@id',    'RutaController' => 'serviciosListadoObservaciones->UpdateList',   'Descripcion' => 'Actualizar Lista',                                'idLevelLimit' => 2, 'Controller' => 'serviciosListadoObservaciones'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/servicios/listado/observaciones/view/@id',          'RutaController' => 'serviciosListadoObservaciones->View',         'Descripcion' => 'Mostrar Detallado',                               'idLevelLimit' => 2, 'Controller' => 'serviciosListadoObservaciones'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/servicios/listado/observaciones/getID/@id',         'RutaController' => 'serviciosListadoObservaciones->GetID',        'Descripcion' => 'Información para el formulario edición',          'idLevelLimit' => 2, 'Controller' => 'serviciosListadoObservaciones'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/servicios/listado/observaciones',                   'RutaController' => 'serviciosListadoObservaciones->Insert',       'Descripcion' => 'Crear Información',                               'idLevelLimit' => 2, 'Controller' => 'serviciosListadoObservaciones'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/servicios/listado/observaciones/update',            'RutaController' => 'serviciosListadoObservaciones->Update',       'Descripcion' => 'Editar por post (modificar y subir archivos)',    'idLevelLimit' => 2, 'Controller' => 'serviciosListadoObservaciones'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/servicios/listado/observaciones',                   'RutaController' => 'serviciosListadoObservaciones->Delete',       'Descripcion' => 'Borrar dato y archivos',                          'idLevelLimit' => 2, 'Controller' => 'serviciosListadoObservaciones'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/servicios/listado/documentos/new/@id',              'RutaController' => 'serviciosListadoDocumentos->New',             'Descripcion' => 'Mostrar modal nuevo',                             'idLevelLimit' => 2, 'Controller' => 'serviciosListadoDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/servicios/listado/documentos/updateList/@id',       'RutaController' => 'serviciosListadoDocumentos->UpdateList',      'Descripcion' => 'Actualizar Lista',                                'idLevelLimit' => 2, 'Controller' => 'serviciosListadoDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/servicios/listado/documentos/view/@id',             'RutaController' => 'serviciosListadoDocumentos->View',            'Descripcion' => 'Mostrar Detallado',                               'idLevelLimit' => 2, 'Controller' => 'serviciosListadoDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/servicios/listado/documentos/getID/@id',            'RutaController' => 'serviciosListadoDocumentos->GetID',           'Descripcion' => 'Información para el formulario edición',          'idLevelLimit' => 2, 'Controller' => 'serviciosListadoDocumentos'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/servicios/listado/documentos',                      'RutaController' => 'serviciosListadoDocumentos->Insert',          'Descripcion' => 'Crear Información',                               'idLevelLimit' => 2, 'Controller' => 'serviciosListadoDocumentos'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/servicios/listado/documentos/update',               'RutaController' => 'serviciosListadoDocumentos->Update',          'Descripcion' => 'Editar por post (modificar y subir archivos)',    'idLevelLimit' => 2, 'Controller' => 'serviciosListadoDocumentos'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/servicios/listado/documentos',                      'RutaController' => 'serviciosListadoDocumentos->Delete',          'Descripcion' => 'Borrar dato y archivos',                          'idLevelLimit' => 2, 'Controller' => 'serviciosListadoDocumentos'],
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
        $RutaController  = '"serviciosCategorias"';
        $RutaController .= ',"serviciosListado"';
        $RutaController .= ',"serviciosListadoDocumentos"';
        $RutaController .= ',"serviciosListadoObservaciones"';

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
            'table'      => 'servicios_categorias',
            'data'       => '`idCategoria` int(10) unsigned NOT NULL AUTO_INCREMENT,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL',
            'primaryKey' => 'idCategoria',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'servicios_listado',
            'data'       => '`idServicio` int(10) unsigned NOT NULL AUTO_INCREMENT,`idEstado` int(10) unsigned NOT NULL,`idCategoria` int(10) unsigned NOT NULL,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`ValorIngreso` decimal(10, 2) UNSIGNED NULL DEFAULT NULL,`ValorEgreso` decimal(10, 2) UNSIGNED NULL DEFAULT NULL,`Descripcion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,`Codigo` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Direccion_img` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL',
            'primaryKey' => 'idServicio',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'servicios_listado_documentos',
            'data'       => '`idDocumentos` int(10) unsigned NOT NULL AUTO_INCREMENT,`idServicio` int(10) unsigned NOT NULL,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`NombreArchivo` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Observacion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,`FVencimiento` date NULL DEFAULT NULL',
            'primaryKey' => 'idDocumentos',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'servicios_listado_observaciones',
            'data'       => '`idObservaciones` int(10) unsigned NOT NULL AUTO_INCREMENT,`idUsuario` int(10) unsigned NOT NULL,`idServicio` int(10) unsigned NOT NULL,`Observacion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`FechaCreacion` date NOT NULL',
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
