<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class coreSistemaInstaller extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
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
    /******************************************************************************/
    //Instalacion del modulo completo
    public function ListDataModule(){

        /*******************************************************/
        //Rutas
        $nData1    = $this->GetCountDataModule();

        /******************************************/
        //si es la respuesta esperada
        $countPermisos = is_numeric($nData1) ? 1 : 0;

        /******************************************/
        //Verificar que existan los permisos
        $arrData = [
            'Nombre'        => 'Modulo Datos de la Empresa',
            'Descripcion'   => 'Módulo para gestionar los Datos de la Empresa',
            'Controller'    => $this->controllerName,
            'countPermisos' => $countPermisos,
        ];
        //devuelvo
        return $arrData;
    }
    /******************************************************************************/
    //Instalacion del modulo completo
    public function InstallModule(){

        /******************************************/
        //Variables
        $arrTables    = array();
        $arrPermisos  = array();

        /*******************************************************/
        /*                 SE GENERAN LAS RUTAS                */
        /*******************************************************/
        $arrPermisos[] = [
            'idPermisosCat'  => '1',
            'idEstado'       => '1',
            'idTipo'         => '2',
            'Nombre'         => 'Datos de la Empresa',
            'Descripcion'    => 'Permite administrar los datos de la empresa',
            'idLevelLimit'   => '2',
            'RutaWeb'        => 'administracion/sistema',
            'RutaController' => 'coreSistema',
        ];
        /************************************************/
        /************************************************/
        //Verifico si existe
        if($arrPermisos){
            //Variable
            $IntCounter = 1;
            //recorro
            foreach ($arrPermisos as $permiso) {
                /************************************************/
                //Se genera la query
                $query = [
                    'data'      => 'idPermisosCat,idEstado,idTipo,Nombre,Descripcion,idLevelLimit,RutaWeb,RutaController',
                    'required'  => 'idPermisosCat,idEstado,idTipo,Nombre,Descripcion,idLevelLimit,RutaWeb,RutaController',
                    'unique'    => '',
                    'encode'    => '',
                    'table'     => 'core_permisos_listado',
                    'Post'      => $permiso
                ];
                //Ejecuto la query
                $xParams    = ['DataCheck' => '', 'query' => $query];
                $permisosID = $this->Base_insert($xParams);
                /************************************************/
                //Listar las rutas
                $arrRutas = $this->listRouteModule($IntCounter, $permisosID);
                /************************************************/
                //Verifico si existe
                if($arrRutas){
                    //recorro
                    foreach ($arrRutas as $rutas) {
                        /******************************/
                        //Se genera la query
                        $query = [
                            'data'      => 'idPermisos,idMetodo,RutaWeb,RutaController,Descripcion,idLevelLimit,Controller',
                            'required'  => 'idPermisos,idMetodo,RutaWeb,RutaController,Descripcion,idLevelLimit,Controller',
                            'unique'    => '',
                            'encode'    => '',
                            'table'     => 'core_permisos_listado_rutas',
                            'Post'      => $rutas
                        ];
                        //Ejecuto la query
                        //Ejecuto la query
                        $xParams  = ['DataCheck' => '', 'query' => $query, 'novalidate' => true];
                        $Response = $this->Base_insert($xParams);
                    }
                }
                /************************************************/
                //Se aumenta
                $IntCounter++;
            }
        }

        /************************************************/
        //devuelvo true
        return true;

    }
    /******************************************************************************/
    //Instalacion del modulo completo
    public function UninstallModule(){

        /*******************************************************/
        //Rutas
        $RutaController  = $this->RutaController();

        /*******************************************************/
        /*             SE CONSULTAN LOS PERMISOS               */
        /*******************************************************/
        //Se genera la query
        $query = [
            'data'    => 'idPermisos',
            'table'   => 'core_permisos_listado',
            'join'    => '',
            'where'   => 'RutaController IN ('.$RutaController.')',
            'group'   => '',
            'having'  => '',
            'order'   => 'idPermisos ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams     = ['query' => $query];
        $arrPermisos = $this->Base_GetList($xParams);

        /*******************************************************/
        /*        SE ELIMINAN PERMISOS DE LOS USUARIOS         */
        /*******************************************************/
        $subQuery = !empty($arrPermisos)
                    ? ',' . implode(',', array_column($arrPermisos, 'idPermisos'))
                    : '';

        /************************************************/
        //Se listan las query
        $arrPermDel   = array();
        $arrPermDel[] = 'DELETE FROM `usuarios_listado_permisos` WHERE idPermisos IN (0 '.$subQuery.')';
        $arrPermDel[] = 'DELETE FROM `core_permisos_listado` WHERE RutaController IN ('.$RutaController.')';
        $arrPermDel[] = 'DELETE FROM `core_permisos_listado_rutas` WHERE Controller IN ('.$RutaController.')';

        /************************************************/
        //Verifico si existe
        if($arrPermDel){
            //recorro
            foreach ($arrPermDel as $sql) {
                //Se ejecuta la query
                $xParams = ['query' => $sql];
                $result  = $this->Base_queryExecute($xParams);
            }
        }

        /************************************************/
        /************************************************/
        //devuelvo true
        return true;

    }
    /******************************************************************************/
    //Se cuentan las rutas del controlador
    public function GetCountDataModule(){

        /*******************************************************/
        //Rutas
        $RutaController  = $this->RutaController();

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => 'idRutas',
            'table'   => 'core_permisos_listado_rutas',
            'join'    => '',
            'where'   => 'Controller IN ('.$RutaController.')',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $nData   = $this->Base_GetCountData($xParams);

        /******************************************/
        //devuelvo
        return $nData;

    }
    /******************************************************************************/
    //Se cuentan las rutas del controlador
    public function listRouteModule($Type, $permisosID){

        /******************************************/
        //Variables
        $arrRutas  = array();

        /******************************************/
        //Variables
        switch ($Type) {
            /******************************************/
            case 1:
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/sistema/listAll',        'RutaController' => 'coreSistema->Resumen',        'Descripcion' => 'Mostrar Resúmen',                               'idLevelLimit' => 2, 'Controller' => 'coreSistema'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/sistema/resumenUpdate',  'RutaController' => 'coreSistema->ResumenUpdate',  'Descripcion' => 'Mostrar información',                           'idLevelLimit' => 2, 'Controller' => 'coreSistema'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/sistema/update',         'RutaController' => 'coreSistema->Update',         'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'coreSistema'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 4, 'RutaWeb' => 'administracion/sistema/delFiles',       'RutaController' => 'coreSistema->DelFiles',       'Descripcion' => 'Permite eliminar archivos',                     'idLevelLimit' => 2, 'Controller' => 'coreSistema'];

                break;
        }

        /******************************************/
        //devuelvo
        return $arrRutas;

    }
    /******************************************************************************/
    //Se listan los controladores
    private function RutaController(){

        /*******************************************************/
        //Rutas
        $RutaController  = '"coreSistema"';

        /******************************************/
        //devuelvo
        return $RutaController;
    }

}