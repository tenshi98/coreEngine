<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class vehiculosInstaller extends ControllerInstaller {

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
        $this->controllerName = 'vehiculosInstaller';
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
            'Nombre'        => 'Módulo de Gestión de Vehículos',
            'Descripcion'   => 'Módulo para gestionar a las Vehículos',
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
            'idPermisosCat'  => '1',                                 // Administración
            'idEstado'       => '1',                                 // Activo
            'idTipo'         => '2',                                 // Crud Resumen
            'Nombre'         => 'Vehículos - Listado',       // Nombre de la transaccion
            'Descripcion'    => 'Permite administrar las vehiculos', // Descripcion de la transaccion
            'idLevelLimit'   => '4',                                 // Ver / Editar / Crear / Borrar
            'RutaWeb'        => 'administracion/vehiculos/listado',  // Ruta web de la transaccion
            'RutaController' => 'vehiculosListado',                  // Controlador de la transaccion
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
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/vehiculos/listado/listAll',                       'RutaController' => 'vehiculosListado->listAll',                  'Descripcion' => 'Listar Toda la Información',                   'idLevelLimit' => 1, 'Controller' => 'vehiculosListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/vehiculos/listado/search',                        'RutaController' => 'vehiculosListado->UpdateList',               'Descripcion' => 'Filtrar datos',                                'idLevelLimit' => 1, 'Controller' => 'vehiculosListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/vehiculos/listado/updateList',                    'RutaController' => 'vehiculosListado->UpdateList',               'Descripcion' => 'Actualizar Lista',                             'idLevelLimit' => 2, 'Controller' => 'vehiculosListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/vehiculos/listado/exportar',                      'RutaController' => 'vehiculosListado->export',                   'Descripcion' => 'Listar Todas las vehiculos para exportarlas',  'idLevelLimit' => 3, 'Controller' => 'vehiculosListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/vehiculos/listado/view/@id',                      'RutaController' => 'vehiculosListado->View',                     'Descripcion' => 'Mostrar Detallado',                            'idLevelLimit' => 1, 'Controller' => 'vehiculosListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/vehiculos/listado/resumen/@id',                   'RutaController' => 'vehiculosListado->Resumen',                  'Descripcion' => 'Mostrar Resúmen',                              'idLevelLimit' => 2, 'Controller' => 'vehiculosListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/vehiculos/listado/resumenUpdate/@id',             'RutaController' => 'vehiculosListado->ResumenUpdate',            'Descripcion' => 'Mostrar información',                          'idLevelLimit' => 2, 'Controller' => 'vehiculosListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/vehiculos/listado',                               'RutaController' => 'vehiculosListado->Insert',                   'Descripcion' => 'Crear Información',                            'idLevelLimit' => 3, 'Controller' => 'vehiculosListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/vehiculos/listado/update',                        'RutaController' => 'vehiculosListado->Update',                   'Descripcion' => 'Editar por post (modificar y subir archivos)', 'idLevelLimit' => 2, 'Controller' => 'vehiculosListado'],
                ['idMetodo' => 4, 'RutaWeb' => 'administracion/vehiculos/listado/delFiles',                      'RutaController' => 'vehiculosListado->DelFiles',                 'Descripcion' => 'Permite eliminar archivos',                    'idLevelLimit' => 2, 'Controller' => 'vehiculosListado'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/vehiculos/listado',                               'RutaController' => 'vehiculosListado->Delete',                   'Descripcion' => 'Borrar dato y archivos',                       'idLevelLimit' => 4, 'Controller' => 'vehiculosListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/vehiculos/listado/observaciones/new/@id',         'RutaController' => 'vehiculosListadoObservaciones->New',         'Descripcion' => 'Mostrar modal nuevo',                          'idLevelLimit' => 2, 'Controller' => 'vehiculosListadoObservaciones'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/vehiculos/listado/observaciones/updateList/@id',  'RutaController' => 'vehiculosListadoObservaciones->UpdateList',  'Descripcion' => 'Actualizar Lista',                             'idLevelLimit' => 2, 'Controller' => 'vehiculosListadoObservaciones'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/vehiculos/listado/observaciones/view/@id',        'RutaController' => 'vehiculosListadoObservaciones->View',        'Descripcion' => 'Mostrar Detallado',                            'idLevelLimit' => 2, 'Controller' => 'vehiculosListadoObservaciones'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/vehiculos/listado/observaciones/getID/@id',       'RutaController' => 'vehiculosListadoObservaciones->GetID',       'Descripcion' => 'Información para el formulario edición',       'idLevelLimit' => 2, 'Controller' => 'vehiculosListadoObservaciones'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/vehiculos/listado/observaciones',                 'RutaController' => 'vehiculosListadoObservaciones->Insert',      'Descripcion' => 'Crear Información',                            'idLevelLimit' => 2, 'Controller' => 'vehiculosListadoObservaciones'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/vehiculos/listado/observaciones/update',          'RutaController' => 'vehiculosListadoObservaciones->Update',      'Descripcion' => 'Editar por post (modificar y subir archivos)', 'idLevelLimit' => 2, 'Controller' => 'vehiculosListadoObservaciones'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/vehiculos/listado/observaciones',                 'RutaController' => 'vehiculosListadoObservaciones->Delete',      'Descripcion' => 'Borrar dato y archivos',                       'idLevelLimit' => 2, 'Controller' => 'vehiculosListadoObservaciones'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/vehiculos/listado/documentos/new/@id',            'RutaController' => 'vehiculosListadoDocumentos->New',            'Descripcion' => 'Mostrar modal nuevo',                          'idLevelLimit' => 2, 'Controller' => 'vehiculosListadoDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/vehiculos/listado/documentos/updateList/@id',     'RutaController' => 'vehiculosListadoDocumentos->UpdateList',     'Descripcion' => 'Actualizar Lista',                             'idLevelLimit' => 2, 'Controller' => 'vehiculosListadoDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/vehiculos/listado/documentos/view/@id',           'RutaController' => 'vehiculosListadoDocumentos->View',           'Descripcion' => 'Mostrar Detallado',                            'idLevelLimit' => 2, 'Controller' => 'vehiculosListadoDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/vehiculos/listado/documentos/getID/@id',          'RutaController' => 'vehiculosListadoDocumentos->GetID',          'Descripcion' => 'Información para el formulario edición',       'idLevelLimit' => 2, 'Controller' => 'vehiculosListadoDocumentos'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/vehiculos/listado/documentos',                    'RutaController' => 'vehiculosListadoDocumentos->Insert',         'Descripcion' => 'Crear Información',                            'idLevelLimit' => 2, 'Controller' => 'vehiculosListadoDocumentos'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/vehiculos/listado/documentos/update',             'RutaController' => 'vehiculosListadoDocumentos->Update',         'Descripcion' => 'Editar por post (modificar y subir archivos)', 'idLevelLimit' => 2, 'Controller' => 'vehiculosListadoDocumentos'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/vehiculos/listado/documentos',                    'RutaController' => 'vehiculosListadoDocumentos->Delete',         'Descripcion' => 'Borrar dato y archivos',                       'idLevelLimit' => 2, 'Controller' => 'vehiculosListadoDocumentos'],
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
        $RutaController  = '"vehiculosListado"';
        $RutaController .= ',"vehiculosListadoObservaciones"';
        $RutaController .= ',"vehiculosListadoDocumentos"';

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
            'table'      => 'vehiculos_listado',
            'data'       => '`idVehiculo` int(10) unsigned NOT NULL AUTO_INCREMENT,`idEstado` int(10) unsigned NOT NULL,`idTipo` int(11) NOT NULL,`Nombre` varchar(255) NOT NULL,`Marca` varchar(255) DEFAULT NULL,`Modelo` varchar(255) DEFAULT NULL,`Num_serie` varchar(255) DEFAULT NULL,`AnoFab` int(10) unsigned DEFAULT NULL,`Patente` varchar(120) DEFAULT NULL,`CapacidadPersonas` int(10) unsigned DEFAULT NULL,`Capacidad` decimal(16,6) unsigned DEFAULT NULL,`MCubicos` decimal(16,6) unsigned DEFAULT NULL,`idTipoCarga` int(10) unsigned DEFAULT NULL,`Direccion_img` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Latitud` double DEFAULT NULL,`Longitud` double DEFAULT NULL,`Velocidad` decimal(20,6) DEFAULT NULL,`LastUpdateFecha` date DEFAULT NULL,`LastUpdateHora` time DEFAULT NULL',
            'primaryKey' => 'idVehiculo',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'vehiculos_listado_documentos',
            'data'       => '`idDocumentos` int(10) unsigned NOT NULL AUTO_INCREMENT,`idTipo` int(10) unsigned NOT NULL,`idVehiculo` int(10) unsigned NOT NULL,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`NombreArchivo` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Observacion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,`FechaCreacion` date NOT NULL,`FechaVencimiento` date NULL DEFAULT NULL',
            'primaryKey' => 'idDocumentos',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'vehiculos_listado_observaciones',
            'data'       => '`idObservaciones` int(10) unsigned NOT NULL AUTO_INCREMENT,`idUsuario` int(10) unsigned NOT NULL, `idVehiculo` int(10) unsigned NOT NULL, `Observacion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, `FechaCreacion` date NOT NULL',
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

        $Data = '';

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
