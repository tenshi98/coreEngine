<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class usuariosInstaller extends ControllerBase {

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
        $this->controllerName = 'usuariosInstaller';
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
            'Nombre'        => 'Modulo de Administracion de Usuarios',
            'Descripcion'   => 'Módulo para gestionar a los Usuarios',
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
        $arrPermisos  = array();

        /*******************************************************/
        /*                 SE GENERAN LAS RUTAS                */
        /*******************************************************/
        $arrPermisos[] = [
            'idPermisosCat'  => '1',
            'idEstado'       => '1',
            'idTipo'         => '2',
            'Nombre'         => 'Usuarios - Listado',
            'Descripcion'    => 'Permite la administracion de los usuarios al interior de la plataforma',
            'idLevelLimit'   => '3',
            'RutaWeb'        => 'administracion/usuarios',
            'RutaController' => 'usuariosListado',
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
                        $xParams = ['DataCheck' => '', 'query' => $query, 'novalidate' => true];
                        $this->Base_insert($xParams);
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
                $this->Base_queryExecute($xParams);
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
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/usuarios/listAll',                       'RutaController' => 'usuariosListado->listAll',        'Descripcion' => 'Listar Toda la Información',                   'idLevelLimit' => 1, 'Controller' => 'usuariosListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/usuarios/search',                        'RutaController' => 'usuariosListado->UpdateList',     'Descripcion' => 'Filtrar datos',                                'idLevelLimit' => 1, 'Controller' => 'usuariosListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/usuarios/updateList',                    'RutaController' => 'usuariosListado->UpdateList',     'Descripcion' => 'Actualizar Lista',                             'idLevelLimit' => 2, 'Controller' => 'usuariosListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/usuarios/view/@id',                      'RutaController' => 'usuariosListado->View',           'Descripcion' => 'Mostrar Detallado',                            'idLevelLimit' => 1, 'Controller' => 'usuariosListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/usuarios/resumen/@id',                   'RutaController' => 'usuariosListado->Resumen',        'Descripcion' => 'Mostrar Resúmen',                              'idLevelLimit' => 2, 'Controller' => 'usuariosListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/usuarios/resumenUpdate/@id',             'RutaController' => 'usuariosListado->ResumenUpdate',  'Descripcion' => 'Mostrar información',                          'idLevelLimit' => 2, 'Controller' => 'usuariosListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/usuarios',                               'RutaController' => 'usuariosListado->Insert',         'Descripcion' => 'Crear Información',                            'idLevelLimit' => 3, 'Controller' => 'usuariosListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/usuarios/update',                        'RutaController' => 'usuariosListado->Update',         'Descripcion' => 'Editar por post (modificar y subir archivos)', 'idLevelLimit' => 2, 'Controller' => 'usuariosListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 4, 'RutaWeb' => 'administracion/usuarios/delFiles',                      'RutaController' => 'usuariosListado->DelFiles',       'Descripcion' => 'Permite eliminar archivos',                    'idLevelLimit' => 2, 'Controller' => 'usuariosListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'administracion/usuarios',                               'RutaController' => 'usuariosListado->Delete',         'Descripcion' => 'Borrar dato y archivos',                       'idLevelLimit' => 4, 'Controller' => 'usuariosListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/usuarios/observaciones/new/@id',         'RutaController' => 'usuariosListadoObs->New',         'Descripcion' => 'Mostrar modal nuevo',                          'idLevelLimit' => 2, 'Controller' => 'usuariosListadoObs'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/usuarios/observaciones/updateList/@id',  'RutaController' => 'usuariosListadoObs->UpdateList',  'Descripcion' => 'Actualizar Lista',                             'idLevelLimit' => 2, 'Controller' => 'usuariosListadoObs'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/usuarios/observaciones/view/@id',        'RutaController' => 'usuariosListadoObs->View',        'Descripcion' => 'Mostrar Detallado',                            'idLevelLimit' => 2, 'Controller' => 'usuariosListadoObs'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/usuarios/observaciones/getID/@id',       'RutaController' => 'usuariosListadoObs->GetID',       'Descripcion' => 'Información para el formulario edición',       'idLevelLimit' => 2, 'Controller' => 'usuariosListadoObs'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/usuarios/observaciones',                 'RutaController' => 'usuariosListadoObs->Insert',      'Descripcion' => 'Crear Información',                            'idLevelLimit' => 2, 'Controller' => 'usuariosListadoObs'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/usuarios/observaciones/update',          'RutaController' => 'usuariosListadoObs->Update',      'Descripcion' => 'Editar por post (modificar y subir archivos)', 'idLevelLimit' => 2, 'Controller' => 'usuariosListadoObs'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'administracion/usuarios/observaciones',                 'RutaController' => 'usuariosListadoObs->Delete',      'Descripcion' => 'Borrar dato y archivos',                       'idLevelLimit' => 2, 'Controller' => 'usuariosListadoObs'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/usuarios/permisos/update',               'RutaController' => 'usuariosListadoPermisos->Update', 'Descripcion' => 'Modificar los permisos de los usuarios',       'idLevelLimit' => 2, 'Controller' => 'usuariosListadoPermisos'];

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
        $RutaController  = '"usuariosListado"';
        $RutaController .= ',"usuariosListadoObs"';
        $RutaController .= ',"usuariosListadoPermisos"';

        /******************************************/
        //devuelvo
        return $RutaController;
    }

}
