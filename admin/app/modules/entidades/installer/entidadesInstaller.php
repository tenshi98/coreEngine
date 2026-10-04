<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class entidadesInstaller extends ControllerInstaller {

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
        $this->controllerName = 'entidadesInstaller';
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
            'Nombre'        => 'Módulo de Gestión de Entidades',
            'Descripcion'   => 'Módulo para gestionar a las Entidades',
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
            'idPermisosCat'  => '1',                                 // Administración
            'idEstado'       => '1',                                 // Activo
            'idTipo'         => '1',                                 // Crud Normal
            'Nombre'         => 'Entidades - Sectores',      // Nombre de la transaccion
            'Descripcion'    => 'Permite administrar los sectores',  // Descripcion de la transaccion
            'idLevelLimit'   => '4',                                 // Ver / Editar / Crear / Borrar
            'RutaWeb'        => 'administracion/entidades/sectores', // Ruta web de la transaccion
            'RutaController' => 'entidadesSectores',                 // Controlador de la transaccion
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '1',                                 // Administración
            'idEstado'       => '1',                                 // Activo
            'idTipo'         => '2',                                 // Crud Resumen
            'Nombre'         => 'Entidades - Listado',       // Nombre de la transaccion
            'Descripcion'    => 'Permite administrar las entidades', // Descripcion de la transaccion
            'idLevelLimit'   => '4',                                 // Ver / Editar / Crear / Borrar
            'RutaWeb'        => 'administracion/entidades/listado',  // Ruta web de la transaccion
            'RutaController' => 'entidadesListado',                  // Controlador de la transaccion
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
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/sectores/listAll',      'RutaController' => 'entidadesSectores->listAll',     'Descripcion' => 'Listar Toda la Información',                    'idLevelLimit' => 1, 'Controller' => 'entidadesSectores'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/sectores/search',       'RutaController' => 'entidadesSectores->UpdateList',  'Descripcion' => 'Filtrar datos',                                 'idLevelLimit' => 1, 'Controller' => 'entidadesSectores'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/sectores/updateList',   'RutaController' => 'entidadesSectores->UpdateList',  'Descripcion' => 'Actualizar Lista',                              'idLevelLimit' => 2, 'Controller' => 'entidadesSectores'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/sectores/view/@id',     'RutaController' => 'entidadesSectores->View',        'Descripcion' => 'Mostrar Detallado',                             'idLevelLimit' => 1, 'Controller' => 'entidadesSectores'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/sectores/getID/@id',    'RutaController' => 'entidadesSectores->GetID',       'Descripcion' => 'Información para el formulario edición',        'idLevelLimit' => 2, 'Controller' => 'entidadesSectores'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/sectores',              'RutaController' => 'entidadesSectores->Insert',      'Descripcion' => 'Crear Información',                             'idLevelLimit' => 3, 'Controller' => 'entidadesSectores'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/sectores/update',       'RutaController' => 'entidadesSectores->Update',      'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'entidadesSectores'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/entidades/sectores',              'RutaController' => 'entidadesSectores->Delete',      'Descripcion' => 'Borrar dato y archivos',                        'idLevelLimit' => 4, 'Controller' => 'entidadesSectores'],
            ],
            2 => [
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/listAll',                       'RutaController' => 'entidadesListado->listAll',                  'Descripcion' => 'Listar Toda la Información',                    'idLevelLimit' => 1, 'Controller' => 'entidadesListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/listado/search',                        'RutaController' => 'entidadesListado->UpdateList',               'Descripcion' => 'Filtrar datos',                                 'idLevelLimit' => 1, 'Controller' => 'entidadesListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/updateList',                    'RutaController' => 'entidadesListado->UpdateList',               'Descripcion' => 'Actualizar Lista',                              'idLevelLimit' => 2, 'Controller' => 'entidadesListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/exportar',                      'RutaController' => 'entidadesListado->export',                   'Descripcion' => 'Listar Todas las entidades para exportarlas',   'idLevelLimit' => 3, 'Controller' => 'entidadesListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/view/@id',                      'RutaController' => 'entidadesListado->View',                     'Descripcion' => 'Mostrar Detallado',                             'idLevelLimit' => 1, 'Controller' => 'entidadesListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/resumen/@id',                   'RutaController' => 'entidadesListado->Resumen',                  'Descripcion' => 'Mostrar Resúmen',                               'idLevelLimit' => 2, 'Controller' => 'entidadesListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/resumenUpdate/@id',             'RutaController' => 'entidadesListado->ResumenUpdate',            'Descripcion' => 'Mostrar información',                           'idLevelLimit' => 2, 'Controller' => 'entidadesListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/listado',                               'RutaController' => 'entidadesListado->Insert',                   'Descripcion' => 'Crear Información',                             'idLevelLimit' => 3, 'Controller' => 'entidadesListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/listado/update',                        'RutaController' => 'entidadesListado->Update',                   'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'entidadesListado'],
                ['idMetodo' => 4, 'RutaWeb' => 'administracion/entidades/listado/delFiles',                      'RutaController' => 'entidadesListado->DelFiles',                 'Descripcion' => 'Permite eliminar archivos',                     'idLevelLimit' => 2, 'Controller' => 'entidadesListado'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/entidades/listado',                               'RutaController' => 'entidadesListado->Delete',                   'Descripcion' => 'Borrar dato y archivos',                        'idLevelLimit' => 4, 'Controller' => 'entidadesListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/observaciones/new/@id',         'RutaController' => 'entidadesListadoObservaciones->New',         'Descripcion' => 'Mostrar modal nuevo',                           'idLevelLimit' => 2, 'Controller' => 'entidadesListadoObservaciones'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/observaciones/updateList/@id',  'RutaController' => 'entidadesListadoObservaciones->UpdateList',  'Descripcion' => 'Actualizar Lista',                              'idLevelLimit' => 2, 'Controller' => 'entidadesListadoObservaciones'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/observaciones/view/@id',        'RutaController' => 'entidadesListadoObservaciones->View',        'Descripcion' => 'Mostrar Detallado',                             'idLevelLimit' => 2, 'Controller' => 'entidadesListadoObservaciones'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/observaciones/getID/@id',       'RutaController' => 'entidadesListadoObservaciones->GetID',       'Descripcion' => 'Información para el formulario edición',        'idLevelLimit' => 2, 'Controller' => 'entidadesListadoObservaciones'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/listado/observaciones',                 'RutaController' => 'entidadesListadoObservaciones->Insert',      'Descripcion' => 'Crear Información',                             'idLevelLimit' => 2, 'Controller' => 'entidadesListadoObservaciones'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/listado/observaciones/update',          'RutaController' => 'entidadesListadoObservaciones->Update',      'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'entidadesListadoObservaciones'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/entidades/listado/observaciones',                 'RutaController' => 'entidadesListadoObservaciones->Delete',      'Descripcion' => 'Borrar dato y archivos',                        'idLevelLimit' => 2, 'Controller' => 'entidadesListadoObservaciones'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/cargas/new/@id',                'RutaController' => 'entidadesListadoCargas->New',                'Descripcion' => 'Mostrar modal nuevo',                           'idLevelLimit' => 2, 'Controller' => 'entidadesListadoCargas'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/cargas/updateList/@id',         'RutaController' => 'entidadesListadoCargas->UpdateList',         'Descripcion' => 'Actualizar Lista',                              'idLevelLimit' => 2, 'Controller' => 'entidadesListadoCargas'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/cargas/view/@id',               'RutaController' => 'entidadesListadoCargas->View',               'Descripcion' => 'Mostrar Detallado',                             'idLevelLimit' => 2, 'Controller' => 'entidadesListadoCargas'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/cargas/getID/@id',              'RutaController' => 'entidadesListadoCargas->GetID',              'Descripcion' => 'Información para el formulario edición',        'idLevelLimit' => 2, 'Controller' => 'entidadesListadoCargas'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/listado/cargas',                        'RutaController' => 'entidadesListadoCargas->Insert',             'Descripcion' => 'Crear Información',                             'idLevelLimit' => 2, 'Controller' => 'entidadesListadoCargas'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/listado/cargas/update',                 'RutaController' => 'entidadesListadoCargas->Update',             'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'entidadesListadoCargas'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/entidades/listado/cargas',                        'RutaController' => 'entidadesListadoCargas->Delete',             'Descripcion' => 'Borrar dato y archivos',                        'idLevelLimit' => 2, 'Controller' => 'entidadesListadoCargas'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/contactos/new/@id',             'RutaController' => 'entidadesListadoContactos->New',             'Descripcion' => 'Mostrar modal nuevo',                           'idLevelLimit' => 2, 'Controller' => 'entidadesListadoContactos'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/contactos/updateList/@id',      'RutaController' => 'entidadesListadoContactos->UpdateList',      'Descripcion' => 'Actualizar Lista',                              'idLevelLimit' => 2, 'Controller' => 'entidadesListadoContactos'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/contactos/view/@id',            'RutaController' => 'entidadesListadoContactos->View',            'Descripcion' => 'Mostrar Detallado',                             'idLevelLimit' => 2, 'Controller' => 'entidadesListadoContactos'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/contactos/getID/@id',           'RutaController' => 'entidadesListadoContactos->GetID',           'Descripcion' => 'Información para el formulario edición',        'idLevelLimit' => 2, 'Controller' => 'entidadesListadoContactos'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/listado/contactos',                     'RutaController' => 'entidadesListadoContactos->Insert',          'Descripcion' => 'Crear Información',                             'idLevelLimit' => 2, 'Controller' => 'entidadesListadoContactos'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/listado/contactos/update',              'RutaController' => 'entidadesListadoContactos->Update',          'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'entidadesListadoContactos'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/entidades/listado/contactos',                     'RutaController' => 'entidadesListadoContactos->Delete',          'Descripcion' => 'Borrar dato y archivos',                        'idLevelLimit' => 2, 'Controller' => 'entidadesListadoContactos'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/documentos/new/@id',            'RutaController' => 'entidadesListadoDocumentos->New',            'Descripcion' => 'Mostrar modal nuevo',                           'idLevelLimit' => 2, 'Controller' => 'entidadesListadoDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/documentos/updateList/@id',     'RutaController' => 'entidadesListadoDocumentos->UpdateList',     'Descripcion' => 'Actualizar Lista',                              'idLevelLimit' => 2, 'Controller' => 'entidadesListadoDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/documentos/view/@id',           'RutaController' => 'entidadesListadoDocumentos->View',           'Descripcion' => 'Mostrar Detallado',                             'idLevelLimit' => 2, 'Controller' => 'entidadesListadoDocumentos'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/documentos/getID/@id',          'RutaController' => 'entidadesListadoDocumentos->GetID',          'Descripcion' => 'Información para el formulario edición',        'idLevelLimit' => 2, 'Controller' => 'entidadesListadoDocumentos'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/listado/documentos',                    'RutaController' => 'entidadesListadoDocumentos->Insert',         'Descripcion' => 'Crear Información',                             'idLevelLimit' => 2, 'Controller' => 'entidadesListadoDocumentos'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/listado/documentos/update',             'RutaController' => 'entidadesListadoDocumentos->Update',         'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'entidadesListadoDocumentos'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/entidades/listado/documentos',                    'RutaController' => 'entidadesListadoDocumentos->Delete',         'Descripcion' => 'Borrar dato y archivos',                        'idLevelLimit' => 2, 'Controller' => 'entidadesListadoDocumentos'],
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
        $RutaController  = '"entidadesSectores"';
        $RutaController .= ',"entidadesListado"';
        $RutaController .= ',"entidadesListadoObservaciones"';
        $RutaController .= ',"entidadesListadoCargas"';
        $RutaController .= ',"entidadesListadoContactos"';
        $RutaController .= ',"entidadesListadoDocumentos"';

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
            'table'      => 'entidades_listado',
            'data'       => '`idEntidad` int(10) unsigned NOT NULL AUTO_INCREMENT,`idEstado` int(10) unsigned NOT NULL,`idSector` int(10) unsigned NULL DEFAULT NULL,`idSexo` int(10) unsigned NULL DEFAULT NULL,`idTipo` int(10) unsigned NOT NULL,`idTipoEntidad` int(10) unsigned NOT NULL,`password` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`ApellidoPat` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`ApellidoMat` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`RazonSocial` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Nick` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Rut` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`idCiudad` int(10) unsigned NULL DEFAULT NULL,`idComuna` int(10) unsigned NULL DEFAULT NULL,`Direccion` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Direccion_img` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`FNacimiento` date NULL DEFAULT NULL,`Email` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Fono1` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Fono2` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Web` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Giro` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`RepLegalNombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`RepLegalRut` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`RepLegalEmail` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`RepLegalFono1` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`RepLegalFono2` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Social_X` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Social_Facebook` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Social_Instagram` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Social_Linkedin` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`IP_Client` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Agent_Transp` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Ultimo_acceso` date NULL DEFAULT NULL,`Latitud` double DEFAULT NULL,`Longitud` double DEFAULT NULL',
            'primaryKey' => 'idEntidad',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'entidades_listado_cargas',
            'data'       => '`idCargas` int(10) unsigned NOT NULL AUTO_INCREMENT,`idEntidad` int(10) unsigned NOT NULL,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`ApellidoPat` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`ApellidoMat` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`idSexo` int(10) unsigned NULL DEFAULT NULL,`FNacimiento` date NULL DEFAULT NULL,`idEstado` int(10) unsigned NULL DEFAULT NULL,`idParentesco` int(10) unsigned NULL DEFAULT NULL,`idEstudios` int(10) unsigned NULL DEFAULT NULL,`idEstadoEstudio` int(10) unsigned NULL DEFAULT NULL,`ObsEstudios` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`FechaVigencia` date NULL DEFAULT NULL,`FechaVencimiento` date NULL DEFAULT NULL',
            'primaryKey' => 'idCargas',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'entidades_listado_contactos',
            'data'       => '`idContacto` int(10) unsigned NOT NULL AUTO_INCREMENT,`idEntidad` int(10) unsigned NOT NULL,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`ApellidoPat` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`ApellidoMat` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Email` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Rut` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Fono1` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Fono2` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`idCiudad` int(10) unsigned NULL DEFAULT NULL,`idComuna` int(10) unsigned NULL DEFAULT NULL,`Direccion` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`idTipoContacto` int(10) unsigned NULL DEFAULT NULL,`Cargo` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`idEstado` int(10) unsigned NOT NULL',
            'primaryKey' => 'idContacto',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'entidades_listado_documentos',
            'data'       => '`idDocumentos` int(10) unsigned NOT NULL AUTO_INCREMENT,`idEntidad` int(10) unsigned NOT NULL,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`NombreArchivo` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Observacion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,`FVencimiento` date NULL DEFAULT NULL',
            'primaryKey' => 'idDocumentos',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'entidades_listado_observaciones',
            'data'       => '`idObservaciones` int(10) unsigned NOT NULL AUTO_INCREMENT,`idUsuario` int(10) unsigned NOT NULL,`idEntidad` int(10) unsigned NOT NULL,`Observacion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`FechaCreacion` date NOT NULL',
            'primaryKey' => 'idObservaciones',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'entidades_sectores',
            'data'       => '`idSector` int(10) unsigned NOT NULL AUTO_INCREMENT,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL',
            'primaryKey' => 'idSector',
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
            'optimization' => 'ALTER TABLE entidades_listado ADD INDEX idx_entidades_estado (idEstado),ADD INDEX idx_entidades_sector (idSector),ADD INDEX idx_entidades_sexo (idSexo),ADD INDEX idx_entidades_tipo (idTipo),ADD INDEX idx_entidades_tipo_entidad (idTipoEntidad),ADD INDEX idx_entidades_ciudad (idCiudad),ADD INDEX idx_entidades_comuna (idComuna),ADD INDEX idx_entidades_rut (Rut),ADD INDEX idx_entidades_email (Email),ADD INDEX idx_entidades_nick (Nick),ADD INDEX idx_entidades_nombre (Nombre),ADD INDEX idx_entidades_ultimo_acceso (Ultimo_acceso),ADD INDEX idx_entidades_estado_tipo (idEstado, idTipo),ADD INDEX idx_entidades_estado_tipo_entidad (idEstado, idTipo, idTipoEntidad);',
            'optimization' => 'ALTER TABLE entidades_listado_cargas ADD INDEX idx_cargas_entidad (idEntidad),ADD INDEX idx_cargas_estado (idEstado),ADD INDEX idx_cargas_sexo (idSexo),ADD INDEX idx_cargas_parentesco (idParentesco),ADD INDEX idx_cargas_estudios (idEstudios),ADD INDEX idx_cargas_estado_estudio (idEstadoEstudio),ADD INDEX idx_cargas_vigencia (FechaVigencia),ADD INDEX idx_cargas_vencimiento (FechaVencimiento),ADD INDEX idx_cargas_entidad_estado (idEntidad, idEstado);',
            'optimization' => 'ALTER TABLE entidades_listado_contactos ADD INDEX idx_contactos_entidad (idEntidad),ADD INDEX idx_contactos_email (Email),ADD INDEX idx_contactos_rut (Rut),ADD INDEX idx_contactos_ciudad (idCiudad),ADD INDEX idx_contactos_comuna (idComuna),ADD INDEX idx_contactos_tipo (idTipoContacto),ADD INDEX idx_contactos_estado (idEstado),ADD INDEX idx_contactos_entidad_estado (idEntidad, idEstado),ADD INDEX idx_contactos_entidad_tipo (idEntidad, idTipoContacto);',
            'optimization' => 'ALTER TABLE entidades_listado_documentos ADD INDEX idx_documentos_entidad (idEntidad),ADD INDEX idx_documentos_vencimiento (FVencimiento),ADD INDEX idx_documentos_entidad_vencimiento (idEntidad, FVencimiento);',
            'optimization' => 'ALTER TABLE entidades_listado_observaciones ADD INDEX idx_observaciones_entidad (idEntidad),ADD INDEX idx_observaciones_usuario (idUsuario),ADD INDEX idx_observaciones_fecha (FechaCreacion),ADD INDEX idx_observaciones_entidad_fecha (idEntidad, FechaCreacion);',
            'optimization' => 'ALTER TABLE entidades_sectores ADD INDEX idx_sectores_nombre (Nombre);',
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
        core_tipos_entidad(idTipo,Nombre)
        core_tipos_entidades(idTipoEntidad,Nombre)
        core_tipos_rrhh_trabajadores_cargas_parentesco(idParentesco,Nombre)
        core_estudios(idEstudios,Nombre)
        core_estados_estudio(idEstadoEstudio,Nombre)
        core_tipos_contactos(idTipoContacto,Nombre)
        core_estados(idEstado,Nombre)
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
