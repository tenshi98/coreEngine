<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class coreSistemaInstaller extends ControllerInstaller {

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
        $this->controllerName = 'coreSistemaInstaller';
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
            'Nombre'        => 'Módulo Datos de la Empresa',
            'Descripcion'   => 'Módulo para gestionar los Datos de la Empresa',
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
            'idPermisosCat'  => '1',                                           // Administración
            'idEstado'       => '1',                                           // Activo
            'idTipo'         => '2',                                           // Crud Resumen
            'Nombre'         => 'Datos de la Empresa',                         // Nombre de la transaccion
            'Descripcion'    => 'Permite administrar los datos de la empresa', // Descripcion de la transaccion
            'idLevelLimit'   => '2',                                           // Ver / Editar
            'RutaWeb'        => 'administracion/sistema',                      // Ruta web de la transaccion
            'RutaController' => 'coreSistema',                                 // Controlador de la transaccion
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
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/sistema/listAll',        'RutaController' => 'coreSistema->Resumen',        'Descripcion' => 'Mostrar Resúmen',                               'idLevelLimit' => 2, 'Controller' => 'coreSistema'],
                ['idMetodo' => 1, 'RutaWeb' => 'administracion/sistema/resumenUpdate',  'RutaController' => 'coreSistema->ResumenUpdate',  'Descripcion' => 'Mostrar información',                           'idLevelLimit' => 2, 'Controller' => 'coreSistema'],
                ['idMetodo' => 2, 'RutaWeb' => 'administracion/sistema/update',         'RutaController' => 'coreSistema->Update',         'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'coreSistema'],
                ['idMetodo' => 4, 'RutaWeb' => 'administracion/sistema/delFiles',       'RutaController' => 'coreSistema->DelFiles',       'Descripcion' => 'Permite eliminar archivos',                     'idLevelLimit' => 2, 'Controller' => 'coreSistema'],
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
        $RutaController  = '"coreSistema"';

        /************************************/
        // Retorno los datos
        return $RutaController;
    }



}
