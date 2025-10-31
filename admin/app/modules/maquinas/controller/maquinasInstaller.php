<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class maquinasInstaller extends ControllerBase {

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
        $this->controllerName = 'maquinasInstaller';
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
            'Nombre'        => 'Módulo de Gestión de Maquinas',
            'Descripcion'   => 'Módulo para gestionar los Maquinas',
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
        /*                 SE GENERAN LAS TABLAS               */
        /*******************************************************/
        $arrTables[] = [
            'table'      => 'maquinas_listado',
            'data'       => '`idMaquina` int UNSIGNED NOT NULL AUTO_INCREMENT,`idEstado` int UNSIGNED NOT NULL,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`CodIdentificador` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Descripcion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,`Direccion_img` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL',
            'primaryKey' => 'idMaquina',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'maquinas_listado_documentos',
            'data'       => '`idDocumentos` int UNSIGNED NOT NULL AUTO_INCREMENT,`idMaquina` int UNSIGNED NOT NULL,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`NombreArchivo` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Observacion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,`FVencimiento` date NULL DEFAULT NULL',
            'primaryKey' => 'idDocumentos',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'maquinas_listado_observaciones',
            'data'       => '`idObservaciones` int UNSIGNED NOT NULL AUTO_INCREMENT,`idMaquina` int UNSIGNED NOT NULL,`Observacion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`FechaCreacion` date NOT NULL',
            'primaryKey' => 'idObservaciones',
            'comentario' => 'Creado desde el Instalador',
        ];

        /************************************************/
        /************************************************/
        //Verifico si existe
        if($arrTables){
            //recorro
            foreach ($arrTables as $table) {
                /******************************/
                //Se genera la query
                $xParams  = ['query' => $table];
                $Response = $this->Base_createTable($xParams);
            }
        }

        /*******************************************************/
        /*                 SE GENERAN LAS RUTAS                */
        /*******************************************************/
        $arrPermisos[] = [
            'idPermisosCat'  => '1',
            'idEstado'       => '1',
            'idTipo'         => '2',
            'Nombre'         => 'Maquinas - Listado',
            'Descripcion'    => 'Permite la administracion de las Maquinas',
            'idLevelLimit'   => '4',
            'RutaWeb'        => 'administracion/maquinas/listado',
            'RutaController' => 'maquinasListado',
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

        /*******************************************************/
        /*              SE ELIMINAN LAS TABLAS                 */
        /*******************************************************/
        $arrTableDel  = array();
        $arrTableDel[] = ['table' => 'maquinas_listado'];
        $arrTableDel[] = ['table' => 'maquinas_listado_documentos'];
        $arrTableDel[] = ['table' => 'maquinas_listado_observaciones'];

        /************************************************/
        //Verifico si existe
        if($arrTableDel){
            //recorro
            foreach ($arrTableDel as $tblDel) {
                //Se ejecuta la query
                $xParams  = ['query' => $tblDel];
                $Response = $this->Base_dropTable($xParams);
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
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/listAll',                         'RutaController' => 'maquinasListado->listAll',                   'Descripcion' => 'Listar Toda la Información',                      'idLevelLimit' => 1, 'Controller' => 'maquinasListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/search',                          'RutaController' => 'maquinasListado->UpdateList',                'Descripcion' => 'Filtrar datos',                                   'idLevelLimit' => 1, 'Controller' => 'maquinasListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/updateList',                      'RutaController' => 'maquinasListado->UpdateList',                'Descripcion' => 'Actualizar Lista',                                'idLevelLimit' => 2, 'Controller' => 'maquinasListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/view/@id',                        'RutaController' => 'maquinasListado->View',                      'Descripcion' => 'Mostrar Detallado',                               'idLevelLimit' => 1, 'Controller' => 'maquinasListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/resumen/@id',                     'RutaController' => 'maquinasListado->Resumen',                   'Descripcion' => 'Mostrar Resúmen',                                 'idLevelLimit' => 2, 'Controller' => 'maquinasListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/resumenUpdate/@id',               'RutaController' => 'maquinasListado->ResumenUpdate',             'Descripcion' => 'Mostrar información',                             'idLevelLimit' => 2, 'Controller' => 'maquinasListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado',                                 'RutaController' => 'maquinasListado->Insert',                    'Descripcion' => 'Crear Información',                               'idLevelLimit' => 3, 'Controller' => 'maquinasListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/update',                          'RutaController' => 'maquinasListado->Update',                    'Descripcion' => 'Editar por post (modificar y subir archivos)',    'idLevelLimit' => 2, 'Controller' => 'maquinasListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 4, 'RutaWeb' => 'administracion/maquinas/listado/delFiles',                        'RutaController' => 'maquinasListado->DelFiles',                  'Descripcion' => 'Permite eliminar archivos',                       'idLevelLimit' => 2, 'Controller' => 'maquinasListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'administracion/maquinas/listado',                                 'RutaController' => 'maquinasListado->Delete',                    'Descripcion' => 'Borrar dato y archivos',                          'idLevelLimit' => 4, 'Controller' => 'maquinasListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/observaciones/new/@id',           'RutaController' => 'maquinasListadoObservaciones->New',          'Descripcion' => 'Mostrar modal nuevo',                             'idLevelLimit' => 2, 'Controller' => 'maquinasListadoObservaciones'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/observaciones/updateList/@id',    'RutaController' => 'maquinasListadoObservaciones->UpdateList',   'Descripcion' => 'Actualizar Lista',                                'idLevelLimit' => 2, 'Controller' => 'maquinasListadoObservaciones'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/observaciones/view/@id',          'RutaController' => 'maquinasListadoObservaciones->View',         'Descripcion' => 'Mostrar Detallado',                               'idLevelLimit' => 2, 'Controller' => 'maquinasListadoObservaciones'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/observaciones/getID/@id',         'RutaController' => 'maquinasListadoObservaciones->GetID',        'Descripcion' => 'Información para el formulario edición',          'idLevelLimit' => 2, 'Controller' => 'maquinasListadoObservaciones'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/observaciones',                   'RutaController' => 'maquinasListadoObservaciones->Insert',       'Descripcion' => 'Crear Información',                               'idLevelLimit' => 2, 'Controller' => 'maquinasListadoObservaciones'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/observaciones/update',            'RutaController' => 'maquinasListadoObservaciones->Update',       'Descripcion' => 'Editar por post (modificar y subir archivos)',    'idLevelLimit' => 2, 'Controller' => 'maquinasListadoObservaciones'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'administracion/maquinas/listado/observaciones',                   'RutaController' => 'maquinasListadoObservaciones->Delete',       'Descripcion' => 'Borrar dato y archivos',                          'idLevelLimit' => 2, 'Controller' => 'maquinasListadoObservaciones'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/documentos/new/@id',              'RutaController' => 'maquinasListadoDocumentos->New',             'Descripcion' => 'Mostrar modal nuevo',                             'idLevelLimit' => 2, 'Controller' => 'maquinasListadoDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/documentos/updateList/@id',       'RutaController' => 'maquinasListadoDocumentos->UpdateList',      'Descripcion' => 'Actualizar Lista',                                'idLevelLimit' => 2, 'Controller' => 'maquinasListadoDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/documentos/view/@id',             'RutaController' => 'maquinasListadoDocumentos->View',            'Descripcion' => 'Mostrar Detallado',                               'idLevelLimit' => 2, 'Controller' => 'maquinasListadoDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/documentos/getID/@id',            'RutaController' => 'maquinasListadoDocumentos->GetID',           'Descripcion' => 'Información para el formulario edición',          'idLevelLimit' => 2, 'Controller' => 'maquinasListadoDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/documentos',                      'RutaController' => 'maquinasListadoDocumentos->Insert',          'Descripcion' => 'Crear Información',                               'idLevelLimit' => 2, 'Controller' => 'maquinasListadoDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/documentos/update',               'RutaController' => 'maquinasListadoDocumentos->Update',          'Descripcion' => 'Editar por post (modificar y subir archivos)',    'idLevelLimit' => 2, 'Controller' => 'maquinasListadoDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'administracion/maquinas/listado/documentos',                      'RutaController' => 'maquinasListadoDocumentos->Delete',          'Descripcion' => 'Borrar dato y archivos',                          'idLevelLimit' => 2, 'Controller' => 'maquinasListadoDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/componentes/new/@id',             'RutaController' => 'maquinasListadoComponentes->New',            'Descripcion' => 'Mostrar modal nuevo',                             'idLevelLimit' => 2, 'Controller' => 'maquinasListadoComponentes'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/componentes/updateList/@id',      'RutaController' => 'maquinasListadoComponentes->UpdateList',     'Descripcion' => 'Actualizar Lista',                                'idLevelLimit' => 2, 'Controller' => 'maquinasListadoComponentes'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/componentes/view/@id',            'RutaController' => 'maquinasListadoComponentes->View',           'Descripcion' => 'Mostrar Detallado',                               'idLevelLimit' => 2, 'Controller' => 'maquinasListadoComponentes'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/componentes/getID/@id',           'RutaController' => 'maquinasListadoComponentes->GetID',          'Descripcion' => 'Información para el formulario edición',          'idLevelLimit' => 2, 'Controller' => 'maquinasListadoComponentes'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/componentes',                     'RutaController' => 'maquinasListadoComponentes->Insert',         'Descripcion' => 'Crear Información',                               'idLevelLimit' => 2, 'Controller' => 'maquinasListadoComponentes'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/componentes/update',              'RutaController' => 'maquinasListadoComponentes->Update',         'Descripcion' => 'Editar por post (modificar y subir archivos)',    'idLevelLimit' => 2, 'Controller' => 'maquinasListadoComponentes'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'administracion/maquinas/listado/componentes',                     'RutaController' => 'maquinasListadoComponentes->Delete',         'Descripcion' => 'Borrar dato y archivos',                          'idLevelLimit' => 2, 'Controller' => 'maquinasListadoComponentes'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/sensores/new/@id',                'RutaController' => 'maquinasListadoSensores->New',               'Descripcion' => 'Mostrar modal nuevo',                             'idLevelLimit' => 2, 'Controller' => 'maquinasListadoSensores'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/sensores/updateList/@id',         'RutaController' => 'maquinasListadoSensores->UpdateList',        'Descripcion' => 'Actualizar Lista',                                'idLevelLimit' => 2, 'Controller' => 'maquinasListadoSensores'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/sensores/view/@id',               'RutaController' => 'maquinasListadoSensores->View',              'Descripcion' => 'Mostrar Detallado',                               'idLevelLimit' => 2, 'Controller' => 'maquinasListadoSensores'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/sensores/getID/@id',              'RutaController' => 'maquinasListadoSensores->GetID',             'Descripcion' => 'Información para el formulario edición',          'idLevelLimit' => 2, 'Controller' => 'maquinasListadoSensores'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/sensores',                        'RutaController' => 'maquinasListadoSensores->Insert',            'Descripcion' => 'Crear Información',                               'idLevelLimit' => 2, 'Controller' => 'maquinasListadoSensores'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/sensores/update',                 'RutaController' => 'maquinasListadoSensores->Update',            'Descripcion' => 'Editar por post (modificar y subir archivos)',    'idLevelLimit' => 2, 'Controller' => 'maquinasListadoSensores'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'administracion/maquinas/listado/sensores',                        'RutaController' => 'maquinasListadoSensores->Delete',            'Descripcion' => 'Borrar dato y archivos',                          'idLevelLimit' => 2, 'Controller' => 'maquinasListadoSensores'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/alarmas/new/@id',                 'RutaController' => 'maquinasListadoAlarmas->New',                'Descripcion' => 'Mostrar modal nuevo',                             'idLevelLimit' => 2, 'Controller' => 'maquinasListadoAlarmas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/alarmas/updateList/@id',          'RutaController' => 'maquinasListadoAlarmas->UpdateList',         'Descripcion' => 'Actualizar Lista',                                'idLevelLimit' => 2, 'Controller' => 'maquinasListadoAlarmas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/alarmas/view/@id',                'RutaController' => 'maquinasListadoAlarmas->View',               'Descripcion' => 'Mostrar Detallado',                               'idLevelLimit' => 2, 'Controller' => 'maquinasListadoAlarmas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/maquinas/listado/alarmas/getID/@id',               'RutaController' => 'maquinasListadoAlarmas->GetID',              'Descripcion' => 'Información para el formulario edición',          'idLevelLimit' => 2, 'Controller' => 'maquinasListadoAlarmas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/alarmas',                         'RutaController' => 'maquinasListadoAlarmas->Insert',             'Descripcion' => 'Crear Información',                               'idLevelLimit' => 2, 'Controller' => 'maquinasListadoAlarmas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/maquinas/listado/alarmas/update',                  'RutaController' => 'maquinasListadoAlarmas->Update',             'Descripcion' => 'Editar por post (modificar y subir archivos)',    'idLevelLimit' => 2, 'Controller' => 'maquinasListadoAlarmas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'administracion/maquinas/listado/alarmas',                         'RutaController' => 'maquinasListadoAlarmas->Delete',             'Descripcion' => 'Borrar dato y archivos',                          'idLevelLimit' => 2, 'Controller' => 'maquinasListadoAlarmas'];

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
        $RutaController  = '"maquinasListado"';
        $RutaController .= ',"maquinasListadoDocumentos"';
        $RutaController .= ',"maquinasListadoObservaciones"';

        /******************************************/
        //devuelvo
        return $RutaController;
    }

}