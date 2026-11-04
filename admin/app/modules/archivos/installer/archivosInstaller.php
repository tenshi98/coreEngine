<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class archivosInstaller extends ControllerInstaller {

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
        $this->controllerName     = 'archivosInstaller';
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
            'Nombre'        => 'Módulo de Gestión de Documentación',
            'Descripcion'   => 'Módulo para gestionar los archivos y documentos',
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
        // Verifico si se ejecuta otro hilo
        $DBConn = $this->getDBConn();

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
            'idPermisosCat'  => '7',                                        // Gestión Documentación
            'idEstado'       => '1',                                        // Activo
            'idTipo'         => '4',                                        // Otros
            'Nombre'         => 'Gestor de Archivos',                       // Nombre de la transaccion
            'Descripcion'    => 'Permite administrar los archivos',         // Descripcion de la transaccion
            'idLevelLimit'   => '4',                                        // Ver / Editar / Crear / Borrar
            'RutaWeb'        => 'gestionDocumentacion/fileManager/listado', // Ruta web de la transaccion
            'RutaController' => 'archivosListado',                          // Controlador de la transaccion
        ];

        /************************************/
        // Verifico si existe
        if($arrPermisos){

            /************************************/
            // Se instala el módulo con sus permisos y rutas
            $this->Base_installModule($arrPermisos, $DBConn);

            /************************************/
            // Verifico si la transacción sigue activa (Base_installModule()
            // hace rollback interno en caso de error, por lo que si la
            // transacción ya no está activa, falló y no debemos hacer commit)
            if (!$this->Base_inTransaction()) {
                // La transacción fue revertida internamente, no hacer commit
                return false;
            }

        }

        /************************************/
        // Se confirma la transaccion
        $ResponseCommit = $this->Base_transactionCommit();

        /************************************/
        // Si falla la la ejecucion, se revierte de inmediato
        if ($ResponseCommit['status'] === false) {
            // Se revierte la transaccion activa (no-op si no hay transaccion)
            $this->Base_transactionRollback();
            // Se reporta el error
            return false;
        }

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

    /*******************************************************************/
    // Definicion de rutas por permiso
    /*******************************************************************/
    public function getRouteDefinitions(int $type): array {
        return match ($type) {
            1 => [
                ['idMetodo' => 1, 'RutaWeb' => 'gestionDocumentacion/fileManager/listado/listAll',   'RutaController' => 'archivosListado->listAll',  'Descripcion' => 'Permite visualizar los archivos',  'idLevelLimit' => 4, 'Controller' => 'archivosListado'],
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
        $RutaController  = 'archivosListado';

        /************************************/
        // Retorno los datos
        return $RutaController;
    }


}
