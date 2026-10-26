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
                    // Se revierte la transaccion activa (no-op si no hay transaccion)
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
            foreach ($arrOptimizations as $query) {
                /************************************/
                // Preparo los datos
                $xParams      = ['query' => $query];
                // Ejecuto la query
                $ResponseExec = $this->Base_queryExecute($xParams);
                /************************************/
                // Si falla la la ejecucion, se muestra alerta
                if ($ResponseExec['status'] === false) {
                    // Se revierte la transaccion activa (no-op si no hay transaccion)
                    $this->Base_transactionRollback();
                    // Se reporta el error
                    Response::error('Error al operar con la Base de Datos', 500, $ResponseExec['error']);
                }
            }
        }

        /*******************************************************/
        /*            SEMBRADO DE LOS CATALOGOS                */
        /*  Los catalogos no tienen pantalla de administracion, */
        /*  por lo que el instalador los siembra (INSERT IGNORE */
        /*  sobre la clave unica de Nombre: repetible).         */
        /*******************************************************/
        $arrSeed = $this->seedCatalogs();
        if($arrSeed){
            // Recorro los datos
            foreach ($arrSeed as $seed) {
                /************************************/
                // Preparo los datos
                $xParams     = ['query' => $seed];
                // Ejecuto la query
                $ResponseExec = $this->Base_queryExecute($xParams);
                /************************************/
                // Si falla la la ejecucion, se muestra alerta
                if ($ResponseExec['status'] === false) {
                    // Se revierte la transaccion activa (no-op si no hay transaccion)
                    $this->Base_transactionRollback();
                    // Se reporta el error
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
        $arrPermisos[] = [
            'idPermisosCat'  => '10',                                          // Mantenimiento Sistema
            'idEstado'       => '1',                                           // Activo
            'idTipo'         => '1',                                           // Crud Normal
            'Nombre'         => 'Maquinas - Sensores Grupos',                  // Nombre de la transaccion
            'Descripcion'    => 'Permite administrar los grupos de sensores',  // Descripcion de la transaccion
            'idLevelLimit'   => '4',                                           // Ver / Editar / Crear / Borrar
            'RutaWeb'        => 'mantencionSistema/maquinas/sensores/grupos',     // Ruta web de la transaccion
            'RutaController' => 'maquinasSensoresGrupos',                      // Controlador de la transaccion
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '10',                                              // Mantenimiento Sistema
            'idEstado'       => '1',                                               // Activo
            'idTipo'         => '1',                                               // Crud Normal
            'Nombre'         => 'Maquinas - Sensores Subgrupos',                   // Nombre de la transaccion
            'Descripcion'    => 'Permite administrar los subgrupos de sensores',   // Descripcion de la transaccion
            'idLevelLimit'   => '4',                                               // Ver / Editar / Crear / Borrar
            'RutaWeb'        => 'mantencionSistema/maquinas/sensores/subgrupos',      // Ruta web de la transaccion
            'RutaController' => 'maquinasSensoresSubgrupos',                       // Controlador de la transaccion
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
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/componentes/newSub/@id',          'RutaController' => 'maquinasListadoComponentes->NewSub',         'Descripcion' => 'Mostrar modal nuevo subcomponente',               'idLevelLimit' => 2, 'Controller' => 'maquinasListadoComponentes'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/componentes/updateList/@id',      'RutaController' => 'maquinasListadoComponentes->UpdateList',     'Descripcion' => 'Actualizar Lista',                                'idLevelLimit' => 2, 'Controller' => 'maquinasListadoComponentes'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/componentes/hijos/@id',           'RutaController' => 'maquinasListadoComponentes->Hijos',          'Descripcion' => 'Actualizar Lista de Hijos',                       'idLevelLimit' => 2, 'Controller' => 'maquinasListadoComponentes'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/componentes/view/@id',            'RutaController' => 'maquinasListadoComponentes->View',           'Descripcion' => 'Mostrar Detallado',                               'idLevelLimit' => 2, 'Controller' => 'maquinasListadoComponentes'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/componentes/getID/@id',           'RutaController' => 'maquinasListadoComponentes->GetID',          'Descripcion' => 'Información para el formulario edición',          'idLevelLimit' => 2, 'Controller' => 'maquinasListadoComponentes'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/componentes',                     'RutaController' => 'maquinasListadoComponentes->Insert',         'Descripcion' => 'Crear Información',                               'idLevelLimit' => 2, 'Controller' => 'maquinasListadoComponentes'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/componentes/update',              'RutaController' => 'maquinasListadoComponentes->Update',         'Descripcion' => 'Editar por post (modificar datos y tareas)',    'idLevelLimit' => 2, 'Controller' => 'maquinasListadoComponentes'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/maquinas/listado/componentes',                     'RutaController' => 'maquinasListadoComponentes->Delete',         'Descripcion' => 'Borrar dato y archivos',                          'idLevelLimit' => 2, 'Controller' => 'maquinasListadoComponentes'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/sensores/new/@id',                'RutaController' => 'maquinasListadoSensores->New',               'Descripcion' => 'Mostrar modal nuevo',                             'idLevelLimit' => 2, 'Controller' => 'maquinasListadoSensores'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/sensores/updateList/@id',         'RutaController' => 'maquinasListadoSensores->UpdateList',        'Descripcion' => 'Actualizar Lista',                                'idLevelLimit' => 2, 'Controller' => 'maquinasListadoSensores'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/sensores/view/@id',               'RutaController' => 'maquinasListadoSensores->View',              'Descripcion' => 'Mostrar Detallado',                               'idLevelLimit' => 2, 'Controller' => 'maquinasListadoSensores'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/sensores/getID/@id',              'RutaController' => 'maquinasListadoSensores->GetID',             'Descripcion' => 'Información para el formulario edición',          'idLevelLimit' => 2, 'Controller' => 'maquinasListadoSensores'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/sensores',                        'RutaController' => 'maquinasListadoSensores->Insert',            'Descripcion' => 'Crear Información',                               'idLevelLimit' => 2, 'Controller' => 'maquinasListadoSensores'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/sensores/update',                 'RutaController' => 'maquinasListadoSensores->Update',            'Descripcion' => 'Editar por post (modificar y subir archivos)',    'idLevelLimit' => 2, 'Controller' => 'maquinasListadoSensores'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/maquinas/listado/sensores',                        'RutaController' => 'maquinasListadoSensores->Delete',            'Descripcion' => 'Borrar dato y archivos',                          'idLevelLimit' => 2, 'Controller' => 'maquinasListadoSensores'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/sensores/order',                  'RutaController' => 'maquinasListadoSensores->SaveOrder',         'Descripcion' => 'Guardar el orden de los sensores',                'idLevelLimit' => 2, 'Controller' => 'maquinasListadoSensores'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/alarmas/new/@id',                 'RutaController' => 'maquinasListadoAlarmas->New',                'Descripcion' => 'Mostrar modal nuevo',                             'idLevelLimit' => 2, 'Controller' => 'maquinasListadoAlarmas'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/alarmas/updateList/@id',          'RutaController' => 'maquinasListadoAlarmas->UpdateList',         'Descripcion' => 'Actualizar Lista',                                'idLevelLimit' => 2, 'Controller' => 'maquinasListadoAlarmas'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/alarmas/view/@id',                'RutaController' => 'maquinasListadoAlarmas->View',               'Descripcion' => 'Mostrar Detallado',                               'idLevelLimit' => 2, 'Controller' => 'maquinasListadoAlarmas'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/alarmas/getID/@id',               'RutaController' => 'maquinasListadoAlarmas->GetID',              'Descripcion' => 'Información para el formulario edición',          'idLevelLimit' => 2, 'Controller' => 'maquinasListadoAlarmas'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/alarmas',                         'RutaController' => 'maquinasListadoAlarmas->Insert',             'Descripcion' => 'Crear Información',                               'idLevelLimit' => 2, 'Controller' => 'maquinasListadoAlarmas'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/alarmas/update',                  'RutaController' => 'maquinasListadoAlarmas->Update',             'Descripcion' => 'Editar por post (modificar y subir archivos)',    'idLevelLimit' => 2, 'Controller' => 'maquinasListadoAlarmas'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/maquinas/listado/alarmas',                         'RutaController' => 'maquinasListadoAlarmas->Delete',             'Descripcion' => 'Borrar dato y archivos',                          'idLevelLimit' => 2, 'Controller' => 'maquinasListadoAlarmas'],
            ],
            2 => [
                ['idMetodo' => 1, 'RutaWeb' => 'mantencionSistema/maquinas/sensores/grupos/listAll',      'RutaController' => 'maquinasSensoresGrupos->listAll',     'Descripcion' => 'Listar Toda la Información',                   'idLevelLimit' => 1, 'Controller' => 'maquinasSensoresGrupos'],
                ['idMetodo' => 2, 'RutaWeb' => 'mantencionSistema/maquinas/sensores/grupos/search',       'RutaController' => 'maquinasSensoresGrupos->UpdateList',  'Descripcion' => 'Filtrar datos',                                'idLevelLimit' => 1, 'Controller' => 'maquinasSensoresGrupos'],
                ['idMetodo' => 1, 'RutaWeb' => 'mantencionSistema/maquinas/sensores/grupos/updateList',   'RutaController' => 'maquinasSensoresGrupos->UpdateList',  'Descripcion' => 'Actualizar Lista',                             'idLevelLimit' => 1, 'Controller' => 'maquinasSensoresGrupos'],
                ['idMetodo' => 1, 'RutaWeb' => 'mantencionSistema/maquinas/sensores/grupos/view/@id',     'RutaController' => 'maquinasSensoresGrupos->View',        'Descripcion' => 'Mostrar Detallado',                            'idLevelLimit' => 1, 'Controller' => 'maquinasSensoresGrupos'],
                ['idMetodo' => 1, 'RutaWeb' => 'mantencionSistema/maquinas/sensores/grupos/getID/@id',    'RutaController' => 'maquinasSensoresGrupos->GetID',       'Descripcion' => 'Información para el formulario edición',       'idLevelLimit' => 2, 'Controller' => 'maquinasSensoresGrupos'],
                ['idMetodo' => 2, 'RutaWeb' => 'mantencionSistema/maquinas/sensores/grupos',              'RutaController' => 'maquinasSensoresGrupos->Insert',      'Descripcion' => 'Crear Información',                            'idLevelLimit' => 3, 'Controller' => 'maquinasSensoresGrupos'],
                ['idMetodo' => 2, 'RutaWeb' => 'mantencionSistema/maquinas/sensores/grupos/update',       'RutaController' => 'maquinasSensoresGrupos->Update',      'Descripcion' => 'Editar por post (modificar y subir archivos)', 'idLevelLimit' => 2, 'Controller' => 'maquinasSensoresGrupos'],
                ['idMetodo' => 3, 'RutaWeb' => 'mantencionSistema/maquinas/sensores/grupos',              'RutaController' => 'maquinasSensoresGrupos->Delete',      'Descripcion' => 'Borrar dato y archivos',                       'idLevelLimit' => 4, 'Controller' => 'maquinasSensoresGrupos'],
            ],
            3 => [
                ['idMetodo' => 1, 'RutaWeb' => 'mantencionSistema/maquinas/sensores/subgrupos/listAll',    'RutaController' => 'maquinasSensoresSubgrupos->listAll',     'Descripcion' => 'Listar Toda la Información',                   'idLevelLimit' => 1, 'Controller' => 'maquinasSensoresSubgrupos'],
                ['idMetodo' => 2, 'RutaWeb' => 'mantencionSistema/maquinas/sensores/subgrupos/search',     'RutaController' => 'maquinasSensoresSubgrupos->UpdateList',  'Descripcion' => 'Filtrar datos',                                'idLevelLimit' => 1, 'Controller' => 'maquinasSensoresSubgrupos'],
                ['idMetodo' => 1, 'RutaWeb' => 'mantencionSistema/maquinas/sensores/subgrupos/updateList', 'RutaController' => 'maquinasSensoresSubgrupos->UpdateList',  'Descripcion' => 'Actualizar Lista',                             'idLevelLimit' => 1, 'Controller' => 'maquinasSensoresSubgrupos'],
                ['idMetodo' => 1, 'RutaWeb' => 'mantencionSistema/maquinas/sensores/subgrupos/view/@id',   'RutaController' => 'maquinasSensoresSubgrupos->View',        'Descripcion' => 'Mostrar Detallado',                            'idLevelLimit' => 1, 'Controller' => 'maquinasSensoresSubgrupos'],
                ['idMetodo' => 1, 'RutaWeb' => 'mantencionSistema/maquinas/sensores/subgrupos/getID/@id',  'RutaController' => 'maquinasSensoresSubgrupos->GetID',       'Descripcion' => 'Información para el formulario edición',       'idLevelLimit' => 2, 'Controller' => 'maquinasSensoresSubgrupos'],
                ['idMetodo' => 2, 'RutaWeb' => 'mantencionSistema/maquinas/sensores/subgrupos',            'RutaController' => 'maquinasSensoresSubgrupos->Insert',      'Descripcion' => 'Crear Información',                            'idLevelLimit' => 3, 'Controller' => 'maquinasSensoresSubgrupos'],
                ['idMetodo' => 2, 'RutaWeb' => 'mantencionSistema/maquinas/sensores/subgrupos/update',     'RutaController' => 'maquinasSensoresSubgrupos->Update',      'Descripcion' => 'Editar por post (modificar y subir archivos)', 'idLevelLimit' => 2, 'Controller' => 'maquinasSensoresSubgrupos'],
                ['idMetodo' => 3, 'RutaWeb' => 'mantencionSistema/maquinas/sensores/subgrupos',            'RutaController' => 'maquinasSensoresSubgrupos->Delete',      'Descripcion' => 'Borrar dato y archivos',                       'idLevelLimit' => 4, 'Controller' => 'maquinasSensoresSubgrupos'],
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
        // Incluye los controladores de las rutas (columna Controller): Base_uninstallModule()
        // borra core_permisos_listado_rutas WHERE Controller IN (esta lista), asi que
        // omitirlos dejaria sus rutas huerfanas al desinstalar el modulo
        $RutaController  = '"maquinasListado"';
        $RutaController .= ',"maquinasListadoDocumentos"';
        $RutaController .= ',"maquinasListadoObservaciones"';
        $RutaController .= ',"maquinasListadoComponentes"';
        $RutaController .= ',"maquinasListadoSensores"';
        $RutaController .= ',"maquinasListadoAlarmas"';
        $RutaController .= ',"maquinasSensoresGrupos"';
        $RutaController .= ',"maquinasSensoresSubgrupos"';

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
            'data'       => '`idMaquina` int(10) unsigned NOT NULL AUTO_INCREMENT,`idEstado` int(10) unsigned NOT NULL,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`CodIdentificador` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Descripcion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,`Direccion_img` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Sim_Num_Tel` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Sim_Compania` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`TiempoFueraLinea` time NULL DEFAULT NULL,`idTab` int(10) unsigned NULL DEFAULT NULL,`id_Geo` int(10) unsigned NULL DEFAULT NULL,`id_Sensores` int(10) unsigned NULL DEFAULT NULL,`idBackup` int(10) unsigned NULL DEFAULT NULL,`NregBackup` int(10) unsigned NULL DEFAULT NULL,`idAlertaTemprana` int(10) unsigned NULL DEFAULT NULL,`AlertaTemprCritica` time NULL DEFAULT NULL,`AlertaTemprNormal` time NULL DEFAULT NULL,`Sensores` longtext CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL',
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
            'data'       => '`idObservaciones` int(10) unsigned NOT NULL AUTO_INCREMENT,`idUsuario` int(10) unsigned NOT NULL,`idMaquina` int(10) unsigned NOT NULL,`Observacion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`FechaCreacion` date NOT NULL,`idUsuarioEdicion` int(10) unsigned NULL DEFAULT NULL,`FechaEdicion` datetime NULL DEFAULT NULL',
            'primaryKey' => 'idObservaciones',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'maquinas_listado_permisos_usuarios',
            'data'       => '`idPermisoUsuario` int(10) unsigned NOT NULL AUTO_INCREMENT,`idUsuario` int(10) unsigned NOT NULL,`idMaquina` int(10) unsigned NOT NULL,`fechaCreacion` date DEFAULT NULL',
            'primaryKey' => 'idPermisoUsuario',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'maquinas_listado_componentes',
            'data'       => '`idComponentes` int(10) unsigned NOT NULL AUTO_INCREMENT,`idMaquina` int(10) unsigned NOT NULL,`idPadre` int(10) unsigned NULL DEFAULT NULL,`Nivel` tinyint(3) unsigned NOT NULL,`NoUtilizable` tinyint(1) NOT NULL DEFAULT 0,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`Codigo` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Marca` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Modelo` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`CodigoSaf` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Numero` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`AnioFabricacion` smallint(5) unsigned NULL DEFAULT NULL,`Serie` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`NombreArchivo` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL',
            'primaryKey' => 'idComponentes',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'maquinas_componentes_tareas',
            'data'       => '`idTarea` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,`Nombre` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`Orden` tinyint(3) unsigned NOT NULL DEFAULT 0',
            'primaryKey' => 'idTarea',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'maquinas_componentes_tareas_rel',
            'data'       => '`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`idComponente` int(10) unsigned NOT NULL,`idTarea` tinyint(3) unsigned NOT NULL',
            'primaryKey' => 'id',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'maquinas_sensores_grupos',
            'data'       => '`idGrupo` int(10) unsigned NOT NULL AUTO_INCREMENT,`Nombre` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL',
            'primaryKey' => 'idGrupo',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'maquinas_sensores_subgrupos',
            'data'       => '`idSubGrupo` int(10) unsigned NOT NULL AUTO_INCREMENT,`idGrupo` int(10) unsigned NOT NULL,`Nombre` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL',
            'primaryKey' => 'idSubGrupo',
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

        /*******************************************************/
        /*            SE GENERAN LAS OPTIMIZACIONES            */
        /*******************************************************/
        $arrOptimizations = [
            'ALTER TABLE maquinas_listado ADD INDEX IF NOT EXISTS idx_maquina_estado (idEstado),ADD INDEX IF NOT EXISTS idx_maquina_tab (idTab),ADD INDEX IF NOT EXISTS idx_maquina_geo (id_Geo),ADD INDEX IF NOT EXISTS idx_maquina_sensores (id_Sensores),ADD COLUMN IF NOT EXISTS Sensores LONGTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;',
            'ALTER TABLE maquinas_listado_observaciones ADD INDEX IF NOT EXISTS idx_mlo_maquina (idMaquina),ADD INDEX IF NOT EXISTS idx_mlo_usuario (idUsuario),ADD INDEX IF NOT EXISTS idx_mlo_fecha (FechaCreacion);',
            'ALTER TABLE maquinas_listado_documentos ADD INDEX IF NOT EXISTS idx_mld_maquina (idMaquina),ADD INDEX IF NOT EXISTS idx_mld_vencimiento (FVencimiento);',
            'ALTER TABLE maquinas_listado_permisos_usuarios ADD INDEX IF NOT EXISTS idx_mld_usuario (idUsuario),ADD INDEX IF NOT EXISTS idx_mld_maquina (idMaquina);',
            'ALTER TABLE maquinas_listado_componentes ADD INDEX IF NOT EXISTS idx_mcomp_maquina (idMaquina),ADD INDEX IF NOT EXISTS idx_mcomp_padre (idPadre);',
            'ALTER TABLE maquinas_componentes_tareas_rel ADD UNIQUE INDEX IF NOT EXISTS uk_mctrel_componente_tarea (idComponente, idTarea);',
            'ALTER TABLE maquinas_sensores_subgrupos ADD INDEX IF NOT EXISTS idx_mss_idGrupo (idGrupo);',
        ];

        /************************************/
        // Retorno True por defecto
        return $arrOptimizations;

    }

    /*******************************************************************/
    // Sembrado de los catalogos de componentes
    /*******************************************************************/
    public function seedCatalogs(){

        /************************************/
        // Variables
        $arrSeed = array();

        /*******************************************************/
        /*                  SEMILLAS                           */
        /*******************************************************/
        $arrSeed[] = 'INSERT IGNORE INTO maquinas_componentes_tareas (idTarea, Nombre, Orden) VALUES '
                   . '(1, \'Utilizacion Grasa\', 1), '
                   . '(2, \'Utilizacion Aceite\', 2), '
                   . '(3, \'Utilizacion Otros Productos\', 3), '
                   . '(4, \'Inspecciones u Otros\', 4);';

        /************************************/
        // Retorno los datos
        return $arrSeed;

    }

    /*******************************************************************/
    // Mapeo de las tablas
    /*******************************************************************/
    public function mapTables(){

        $Data = '
        core_estados(idEstado,Nombre)
        maquinas_listado(idMaquina,Nombre)
        maquinas_listado_componentes(idComponentes,idMaquina,idPadre,Nombre)
        maquinas_componentes_tareas(idTarea,Nombre)
        maquinas_sensores_grupos(idGrupo,Nombre)
        maquinas_sensores_subgrupos(idSubGrupo,idGrupo,Nombre)
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
