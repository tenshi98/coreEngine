<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class usuariosInstaller extends ControllerInstaller {

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
        $this->controllerName = 'usuariosInstaller';
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
            'Nombre'        => 'Módulo de Administracion de Usuarios',
            'Descripcion'   => 'Módulo para gestionar a los Usuarios',
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
        $arrPermisos  = array();

        /*******************************************************/
        /*                 SE GENERAN LAS RUTAS                */
        /*******************************************************/
        $arrPermisos[] = [
            'idPermisosCat'  => '1',                                                                      // Administración
            'idEstado'       => '1',                                                                      // Activo
            'idTipo'         => '2',                                                                      // Crud Resumen
            'Nombre'         => 'Usuarios - Listado',                                                     // Nombre de la transaccion
            'Descripcion'    => 'Permite la administración de los usuarios al interior de la plataforma', // Descripcion de la transaccion
            'idLevelLimit'   => '3',                                                                      // Ver / Editar / Crear
            'RutaWeb'        => 'administracion/usuarios',                                                // Ruta web de la transaccion
            'RutaController' => 'usuariosListado',                                                        // Controlador de la transaccion
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
        return $this->Base_uninstallModule($this->RutaController());

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
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/usuarios/listAll',                       'RutaController' => 'usuariosListado->listAll',                'Descripcion' => 'Listar Toda la Información',                                       'idLevelLimit' => 1, 'Controller' => 'usuariosListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/usuarios/search',                        'RutaController' => 'usuariosListado->UpdateList',             'Descripcion' => 'Filtrar datos',                                                    'idLevelLimit' => 1, 'Controller' => 'usuariosListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/usuarios/updateList',                    'RutaController' => 'usuariosListado->UpdateList',             'Descripcion' => 'Actualizar Lista',                                                 'idLevelLimit' => 2, 'Controller' => 'usuariosListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/usuarios/view/@id',                      'RutaController' => 'usuariosListado->View',                   'Descripcion' => 'Mostrar Detallado',                                                'idLevelLimit' => 1, 'Controller' => 'usuariosListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/usuarios/resumen/@id',                   'RutaController' => 'usuariosListado->Resumen',                'Descripcion' => 'Mostrar Resúmen',                                                  'idLevelLimit' => 2, 'Controller' => 'usuariosListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/usuarios/resumenUpdate/@id',             'RutaController' => 'usuariosListado->ResumenUpdate',          'Descripcion' => 'Mostrar información',                                              'idLevelLimit' => 2, 'Controller' => 'usuariosListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/usuarios',                               'RutaController' => 'usuariosListado->Insert',                 'Descripcion' => 'Crear Información',                                                'idLevelLimit' => 3, 'Controller' => 'usuariosListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/usuarios/update',                        'RutaController' => 'usuariosListado->Update',                 'Descripcion' => 'Editar por post (modificar y subir archivos)',                     'idLevelLimit' => 2, 'Controller' => 'usuariosListado'],
                ['idMetodo' => 4, 'RutaWeb' => 'administracion/usuarios/delFiles',                      'RutaController' => 'usuariosListado->DelFiles',               'Descripcion' => 'Permite eliminar archivos',                                        'idLevelLimit' => 2, 'Controller' => 'usuariosListado'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/usuarios',                               'RutaController' => 'usuariosListado->Delete',                 'Descripcion' => 'Borrar dato y archivos',                                           'idLevelLimit' => 4, 'Controller' => 'usuariosListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/usuarios/observaciones/new/@id',         'RutaController' => 'usuariosListadoObs->New',                 'Descripcion' => 'Mostrar modal nuevo',                                              'idLevelLimit' => 2, 'Controller' => 'usuariosListadoObs'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/usuarios/observaciones/updateList/@id',  'RutaController' => 'usuariosListadoObs->UpdateList',          'Descripcion' => 'Actualizar Lista',                                                 'idLevelLimit' => 2, 'Controller' => 'usuariosListadoObs'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/usuarios/observaciones/view/@id',        'RutaController' => 'usuariosListadoObs->View',                'Descripcion' => 'Mostrar Detallado',                                                'idLevelLimit' => 2, 'Controller' => 'usuariosListadoObs'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/usuarios/observaciones/getID/@id',       'RutaController' => 'usuariosListadoObs->GetID',               'Descripcion' => 'Información para el formulario edición',                           'idLevelLimit' => 2, 'Controller' => 'usuariosListadoObs'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/usuarios/observaciones',                 'RutaController' => 'usuariosListadoObs->Insert',              'Descripcion' => 'Crear Información',                                                'idLevelLimit' => 2, 'Controller' => 'usuariosListadoObs'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/usuarios/observaciones/update',          'RutaController' => 'usuariosListadoObs->Update',              'Descripcion' => 'Editar por post (modificar y subir archivos)',                     'idLevelLimit' => 2, 'Controller' => 'usuariosListadoObs'],
                ['idMetodo' => 3, 'RutaWeb' => 'administracion/usuarios/observaciones',                 'RutaController' => 'usuariosListadoObs->Delete',              'Descripcion' => 'Borrar dato y archivos',                                           'idLevelLimit' => 2, 'Controller' => 'usuariosListadoObs'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/usuarios/permisos/update',               'RutaController' => 'usuariosListadoPermisos->Update',         'Descripcion' => 'Modificar los permisos de los usuarios',                           'idLevelLimit' => 2, 'Controller' => 'usuariosListadoPermisos'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/usuarios/bodegas/update',                'RutaController' => 'usuariosListadoPermisosBodegas->Update',  'Descripcion' => 'Modificar los permisos de acceso a bodegas de los usuarios',       'idLevelLimit' => 2, 'Controller' => 'usuariosListadoPermisosBodegas'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/usuarios/maquinas/update',               'RutaController' => 'usuariosListadoPermisosMaquinas->Update', 'Descripcion' => 'Modificar los permisos de acceso a maquinas de los usuarios',      'idLevelLimit' => 2, 'Controller' => 'usuariosListadoPermisosMaquinas'],
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
        $RutaController  = '"usuariosListado"';
        $RutaController .= ',"usuariosListadoObs"';
        $RutaController .= ',"usuariosListadoPermisos"';

        /************************************/
        // Retorno los datos
        return $RutaController;
    }



}
