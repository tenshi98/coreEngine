<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class maquinasInstaller extends ControllerInstaller {

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
        $this->controllerName = 'maquinasInstaller';
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
            'Nombre'        => 'Módulo de Gestión de Maquinas',
            'Descripcion'   => 'Módulo para gestionar los Maquinas',
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
            'idPermisosCat'  => '1',                                         // Administración
            'idEstado'       => '1',                                         // Activo
            'idTipo'         => '2',                                         // Crud Resumen
            'Nombre'         => 'Maquinas - Listado',                        // Nombre de la transaccion
            'Descripcion'    => 'Permite la administración de las Maquinas', // Descripcion de la transaccion
            'idLevelLimit'   => '4',                                         // Ver / Editar / Crear / Borrar
            'RutaWeb'        => 'administracion/maquinas/listado',           // Ruta web de la transaccion
            'RutaController' => 'maquinasListado',                           // Controlador de la transaccion
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
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/listAll',                         'RutaController' => 'maquinasListado->listAll',                   'Descripcion' => 'Listar Toda la Información',                      'idLevelLimit' => 1, 'Controller' => 'maquinasListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/search',                          'RutaController' => 'maquinasListado->UpdateList',                'Descripcion' => 'Filtrar datos',                                   'idLevelLimit' => 1, 'Controller' => 'maquinasListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/updateList',                      'RutaController' => 'maquinasListado->UpdateList',                'Descripcion' => 'Actualizar Lista',                                'idLevelLimit' => 2, 'Controller' => 'maquinasListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/view/@id',                        'RutaController' => 'maquinasListado->View',                      'Descripcion' => 'Mostrar Detallado',                               'idLevelLimit' => 1, 'Controller' => 'maquinasListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/resumen/@id',                     'RutaController' => 'maquinasListado->Resumen',                   'Descripcion' => 'Mostrar Resúmen',                                 'idLevelLimit' => 2, 'Controller' => 'maquinasListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/resumenUpdate/@id',               'RutaController' => 'maquinasListado->ResumenUpdate',             'Descripcion' => 'Mostrar información',                             'idLevelLimit' => 2, 'Controller' => 'maquinasListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado',                                 'RutaController' => 'maquinasListado->Insert',                    'Descripcion' => 'Crear Información',                               'idLevelLimit' => 3, 'Controller' => 'maquinasListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/update',                          'RutaController' => 'maquinasListado->Update',                    'Descripcion' => 'Editar por post (modificar y subir archivos)',    'idLevelLimit' => 2, 'Controller' => 'maquinasListado'],
                ['idMetodo' => 4, 'RutaWeb' => 'administracion/maquinas/listado/delFiles',                        'RutaController' => 'maquinasListado->DelFiles',                  'Descripcion' => 'Permite eliminar archivos',                       'idLevelLimit' => 2, 'Controller' => 'maquinasListado'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/maquinas/listado',                                 'RutaController' => 'maquinasListado->Delete',                    'Descripcion' => 'Borrar dato y archivos',                          'idLevelLimit' => 4, 'Controller' => 'maquinasListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/observaciones/new/@id',           'RutaController' => 'maquinasListadoObservaciones->New',          'Descripcion' => 'Mostrar modal nuevo',                             'idLevelLimit' => 2, 'Controller' => 'maquinasListadoObservaciones'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/observaciones/updateList/@id',    'RutaController' => 'maquinasListadoObservaciones->UpdateList',   'Descripcion' => 'Actualizar Lista',                                'idLevelLimit' => 2, 'Controller' => 'maquinasListadoObservaciones'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/observaciones/view/@id',          'RutaController' => 'maquinasListadoObservaciones->View',         'Descripcion' => 'Mostrar Detallado',                               'idLevelLimit' => 2, 'Controller' => 'maquinasListadoObservaciones'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/observaciones/getID/@id',         'RutaController' => 'maquinasListadoObservaciones->GetID',        'Descripcion' => 'Información para el formulario edición',          'idLevelLimit' => 2, 'Controller' => 'maquinasListadoObservaciones'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/observaciones',                   'RutaController' => 'maquinasListadoObservaciones->Insert',       'Descripcion' => 'Crear Información',                               'idLevelLimit' => 2, 'Controller' => 'maquinasListadoObservaciones'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/observaciones/update',            'RutaController' => 'maquinasListadoObservaciones->Update',       'Descripcion' => 'Editar por post (modificar y subir archivos)',    'idLevelLimit' => 2, 'Controller' => 'maquinasListadoObservaciones'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/maquinas/listado/observaciones',                   'RutaController' => 'maquinasListadoObservaciones->Delete',       'Descripcion' => 'Borrar dato y archivos',                          'idLevelLimit' => 2, 'Controller' => 'maquinasListadoObservaciones'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/documentos/new/@id',              'RutaController' => 'maquinasListadoDocumentos->New',             'Descripcion' => 'Mostrar modal nuevo',                             'idLevelLimit' => 2, 'Controller' => 'maquinasListadoDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/documentos/updateList/@id',       'RutaController' => 'maquinasListadoDocumentos->UpdateList',      'Descripcion' => 'Actualizar Lista',                                'idLevelLimit' => 2, 'Controller' => 'maquinasListadoDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/documentos/view/@id',             'RutaController' => 'maquinasListadoDocumentos->View',            'Descripcion' => 'Mostrar Detallado',                               'idLevelLimit' => 2, 'Controller' => 'maquinasListadoDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/documentos/getID/@id',            'RutaController' => 'maquinasListadoDocumentos->GetID',           'Descripcion' => 'Información para el formulario edición',          'idLevelLimit' => 2, 'Controller' => 'maquinasListadoDocumentos'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/documentos',                      'RutaController' => 'maquinasListadoDocumentos->Insert',          'Descripcion' => 'Crear Información',                               'idLevelLimit' => 2, 'Controller' => 'maquinasListadoDocumentos'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/documentos/update',               'RutaController' => 'maquinasListadoDocumentos->Update',          'Descripcion' => 'Editar por post (modificar y subir archivos)',    'idLevelLimit' => 2, 'Controller' => 'maquinasListadoDocumentos'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/maquinas/listado/documentos',                      'RutaController' => 'maquinasListadoDocumentos->Delete',          'Descripcion' => 'Borrar dato y archivos',                          'idLevelLimit' => 2, 'Controller' => 'maquinasListadoDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/componentes/new/@id',             'RutaController' => 'maquinasListadoComponentes->New',            'Descripcion' => 'Mostrar modal nuevo',                             'idLevelLimit' => 2, 'Controller' => 'maquinasListadoComponentes'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/componentes/updateList/@id',      'RutaController' => 'maquinasListadoComponentes->UpdateList',     'Descripcion' => 'Actualizar Lista',                                'idLevelLimit' => 2, 'Controller' => 'maquinasListadoComponentes'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/componentes/view/@id',            'RutaController' => 'maquinasListadoComponentes->View',           'Descripcion' => 'Mostrar Detallado',                               'idLevelLimit' => 2, 'Controller' => 'maquinasListadoComponentes'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/componentes/getID/@id',           'RutaController' => 'maquinasListadoComponentes->GetID',          'Descripcion' => 'Información para el formulario edición',          'idLevelLimit' => 2, 'Controller' => 'maquinasListadoComponentes'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/componentes',                     'RutaController' => 'maquinasListadoComponentes->Insert',         'Descripcion' => 'Crear Información',                               'idLevelLimit' => 2, 'Controller' => 'maquinasListadoComponentes'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/componentes/update',              'RutaController' => 'maquinasListadoComponentes->Update',         'Descripcion' => 'Editar por post (modificar y subir archivos)',    'idLevelLimit' => 2, 'Controller' => 'maquinasListadoComponentes'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/maquinas/listado/componentes',                     'RutaController' => 'maquinasListadoComponentes->Delete',         'Descripcion' => 'Borrar dato y archivos',                          'idLevelLimit' => 2, 'Controller' => 'maquinasListadoComponentes'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/sensores/new/@id',                'RutaController' => 'maquinasListadoSensores->New',               'Descripcion' => 'Mostrar modal nuevo',                             'idLevelLimit' => 2, 'Controller' => 'maquinasListadoSensores'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/sensores/updateList/@id',         'RutaController' => 'maquinasListadoSensores->UpdateList',        'Descripcion' => 'Actualizar Lista',                                'idLevelLimit' => 2, 'Controller' => 'maquinasListadoSensores'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/sensores/view/@id',               'RutaController' => 'maquinasListadoSensores->View',              'Descripcion' => 'Mostrar Detallado',                               'idLevelLimit' => 2, 'Controller' => 'maquinasListadoSensores'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/sensores/getID/@id',              'RutaController' => 'maquinasListadoSensores->GetID',             'Descripcion' => 'Información para el formulario edición',          'idLevelLimit' => 2, 'Controller' => 'maquinasListadoSensores'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/sensores',                        'RutaController' => 'maquinasListadoSensores->Insert',            'Descripcion' => 'Crear Información',                               'idLevelLimit' => 2, 'Controller' => 'maquinasListadoSensores'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/sensores/update',                 'RutaController' => 'maquinasListadoSensores->Update',            'Descripcion' => 'Editar por post (modificar y subir archivos)',    'idLevelLimit' => 2, 'Controller' => 'maquinasListadoSensores'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/maquinas/listado/sensores',                        'RutaController' => 'maquinasListadoSensores->Delete',            'Descripcion' => 'Borrar dato y archivos',                          'idLevelLimit' => 2, 'Controller' => 'maquinasListadoSensores'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/alarmas/new/@id',                 'RutaController' => 'maquinasListadoAlarmas->New',                'Descripcion' => 'Mostrar modal nuevo',                             'idLevelLimit' => 2, 'Controller' => 'maquinasListadoAlarmas'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/alarmas/updateList/@id',          'RutaController' => 'maquinasListadoAlarmas->UpdateList',         'Descripcion' => 'Actualizar Lista',                                'idLevelLimit' => 2, 'Controller' => 'maquinasListadoAlarmas'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/alarmas/view/@id',                'RutaController' => 'maquinasListadoAlarmas->View',               'Descripcion' => 'Mostrar Detallado',                               'idLevelLimit' => 2, 'Controller' => 'maquinasListadoAlarmas'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/alarmas/getID/@id',               'RutaController' => 'maquinasListadoAlarmas->GetID',              'Descripcion' => 'Información para el formulario edición',          'idLevelLimit' => 2, 'Controller' => 'maquinasListadoAlarmas'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/alarmas',                         'RutaController' => 'maquinasListadoAlarmas->Insert',             'Descripcion' => 'Crear Información',                               'idLevelLimit' => 2, 'Controller' => 'maquinasListadoAlarmas'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/alarmas/update',                  'RutaController' => 'maquinasListadoAlarmas->Update',             'Descripcion' => 'Editar por post (modificar y subir archivos)',    'idLevelLimit' => 2, 'Controller' => 'maquinasListadoAlarmas'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/maquinas/listado/alarmas',                         'RutaController' => 'maquinasListadoAlarmas->Delete',             'Descripcion' => 'Borrar dato y archivos',                          'idLevelLimit' => 2, 'Controller' => 'maquinasListadoAlarmas'],
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
        $RutaController  = '"maquinasListado"';
        $RutaController .= ',"maquinasListadoDocumentos"';
        $RutaController .= ',"maquinasListadoObservaciones"';

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
            'table'      => 'maquinas_listado',
            'data'       => '`idMaquina` int(10) unsigned NOT NULL AUTO_INCREMENT,`idEstado` int(10) unsigned NOT NULL,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`CodIdentificador` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Descripcion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,`Direccion_img` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL',
            'primaryKey' => 'idMaquina',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'maquinas_listado_documentos',
            'data'       => '`idDocumentos` int(10) unsigned NOT NULL AUTO_INCREMENT,`idMaquina` int(10) unsigned NOT NULL,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`NombreArchivo` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Observacion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,`FVencimiento` date NULL DEFAULT NULL',
            'primaryKey' => 'idDocumentos',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'maquinas_listado_observaciones',
            'data'       => '`idObservaciones` int(10) unsigned NOT NULL AUTO_INCREMENT,`idUsuario` int(10) unsigned NOT NULL,`idMaquina` int(10) unsigned NOT NULL,`Observacion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`FechaCreacion` date NOT NULL',
            'primaryKey' => 'idObservaciones',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'maquinas_listado_permisos_usuarios',
            'data'       => '`idPermisoUsuario` int(10) unsigned NOT NULL AUTO_INCREMENT,`idUsuario` int(10) unsigned NOT NULL,`idMaquina` int(10) unsigned NOT NULL,`fechaCreacion` date DEFAULT NULL',
            'primaryKey' => 'idPermisoUsuario',
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
            'optimization' => 'ALTER TABLE maquinas_listado ADD INDEX idx_maquina_estado (idEstado),ADD INDEX idx_maquina_tab (idTab),ADD INDEX idx_maquina_geo (id_Geo),ADD INDEX idx_maquina_sensores (id_Sensores);',
            'optimization' => 'ALTER TABLE maquinas_listado_observaciones ADD INDEX idx_mlo_maquina (idMaquina),ADD INDEX idx_mlo_usuario (idUsuario),ADD INDEX idx_mlo_fecha (FechaCreacion);',
            'optimization' => 'ALTER TABLE maquinas_listado_documentos ADD INDEX idx_mld_maquina (idMaquina),ADD INDEX idx_mld_vencimiento (FVencimiento);',
            'optimization' => 'ALTER TABLE maquinas_listado_permisos_usuarios ADD INDEX idx_mld_usuario (idUsuario),ADD INDEX idx_mld_maquina (idMaquina);',
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
