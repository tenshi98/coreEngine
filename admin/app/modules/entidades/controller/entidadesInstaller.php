<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class entidadesInstaller extends ControllerBase {

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
        $this->controllerName = 'entidadesInstaller';
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
            'Nombre'        => 'Módulo de Gestión de Entidades',
            'Descripcion'   => 'Módulo para gestionar a las Entidades',
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
            'table'      => 'entidades_listado',
            'data'       => '`idEntidad` int UNSIGNED NOT NULL AUTO_INCREMENT,`idEstado` int UNSIGNED NOT NULL,`idSector` int UNSIGNED NULL DEFAULT NULL,`idSexo` int UNSIGNED NULL DEFAULT NULL,`idTipo` int UNSIGNED NOT NULL,`idTipoEntidad` int UNSIGNED NOT NULL,`password` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`ApellidoPat` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`ApellidoMat` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`RazonSocial` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Nick` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Rut` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`idCiudad` int UNSIGNED NULL DEFAULT NULL,`idComuna` int UNSIGNED NULL DEFAULT NULL,`Direccion` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Direccion_img` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`FNacimiento` date NULL DEFAULT NULL,`Email` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Fono1` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Fono2` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Web` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Giro` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`RepLegalNombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`RepLegalRut` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`RepLegalEmail` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`RepLegalFono1` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`RepLegalFono2` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Social_X` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Social_Facebook` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Social_Instagram` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Social_Linkedin` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`IP_Client` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Agent_Transp` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Ultimo_acceso` date NULL DEFAULT NULL',
            'primaryKey' => 'idEntidad',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'entidades_listado_cargas',
            'data'       => '`idCargas` int UNSIGNED NOT NULL AUTO_INCREMENT,`idEntidad` int UNSIGNED NOT NULL,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`ApellidoPat` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`ApellidoMat` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`idSexo` int UNSIGNED NULL DEFAULT NULL,`FNacimiento` date NULL DEFAULT NULL,`idEstado` int UNSIGNED NULL DEFAULT NULL,`idParentesco` int UNSIGNED NULL DEFAULT NULL,`idEstudios` int UNSIGNED NULL DEFAULT NULL,`idEstadoEstudio` int UNSIGNED NULL DEFAULT NULL,`ObsEstudios` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`FechaVigencia` date NULL DEFAULT NULL,`FechaVencimiento` date NULL DEFAULT NULL',
            'primaryKey' => 'idCargas',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'entidades_listado_contactos',
            'data'       => '`idContacto` int UNSIGNED NOT NULL AUTO_INCREMENT,`idEntidad` int UNSIGNED NOT NULL,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`ApellidoPat` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`ApellidoMat` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Email` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Rut` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Fono1` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Fono2` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`idCiudad` int UNSIGNED NULL DEFAULT NULL,`idComuna` int UNSIGNED NULL DEFAULT NULL,`Direccion` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`idTipoContacto` int UNSIGNED NULL DEFAULT NULL,`Cargo` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`idEstado` int UNSIGNED NOT NULL',
            'primaryKey' => 'idContacto',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'entidades_listado_documentos',
            'data'       => '`idDocumentos` int UNSIGNED NOT NULL AUTO_INCREMENT,`idEntidad` int UNSIGNED NOT NULL,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`NombreArchivo` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Observacion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,`FVencimiento` date NULL DEFAULT NULL',
            'primaryKey' => 'idDocumentos',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'entidades_listado_observaciones',
            'data'       => '`idObservaciones` int UNSIGNED NOT NULL AUTO_INCREMENT,`idEntidad` int UNSIGNED NOT NULL,`Observacion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`FechaCreacion` date NOT NULL',
            'primaryKey' => 'idObservaciones',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'entidades_sectores',
            'data'       => '`idSector` int UNSIGNED NOT NULL AUTO_INCREMENT,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL',
            'primaryKey' => 'idSector',
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
            'idTipo'         => '1',
            'Nombre'         => 'Gestion Entidades - Sectores',
            'Descripcion'    => 'Permite administrar los sectores',
            'idLevelLimit'   => '4',
            'RutaWeb'        => 'administracion/entidades/sectores',
            'RutaController' => 'entidadesSectores',
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '1',
            'idEstado'       => '1',
            'idTipo'         => '2',
            'Nombre'         => 'Gestion Entidades - Listado',
            'Descripcion'    => 'Permite administrar las entidades',
            'idLevelLimit'   => '4',
            'RutaWeb'        => 'administracion/entidades/listado',
            'RutaController' => 'entidadesListado',
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
        $arrTableDel[] = ['table' => 'entidades_listado'];
        $arrTableDel[] = ['table' => 'entidades_listado_cargas'];
        $arrTableDel[] = ['table' => 'entidades_listado_contactos'];
        $arrTableDel[] = ['table' => 'entidades_listado_documentos'];
        $arrTableDel[] = ['table' => 'entidades_listado_observaciones'];
        $arrTableDel[] = ['table' => 'entidades_sectores'];

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
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/sectores/listAll',      'RutaController' => 'entidadesSectores->listAll',     'Descripcion' => 'Listar Toda la Información',                    'idLevelLimit' => 1, 'Controller' => 'entidadesSectores'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/sectores/search',       'RutaController' => 'entidadesSectores->UpdateList',  'Descripcion' => 'Filtrar datos',                                 'idLevelLimit' => 1, 'Controller' => 'entidadesSectores'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/sectores/updateList',   'RutaController' => 'entidadesSectores->UpdateList',  'Descripcion' => 'Actualizar Lista',                              'idLevelLimit' => 2, 'Controller' => 'entidadesSectores'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/sectores/view/@id',     'RutaController' => 'entidadesSectores->View',        'Descripcion' => 'Mostrar Detallado',                             'idLevelLimit' => 1, 'Controller' => 'entidadesSectores'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/sectores/getID/@id',    'RutaController' => 'entidadesSectores->GetID',       'Descripcion' => 'Información para el formulario edición',        'idLevelLimit' => 2, 'Controller' => 'entidadesSectores'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/sectores',              'RutaController' => 'entidadesSectores->Insert',      'Descripcion' => 'Crear Información',                             'idLevelLimit' => 3, 'Controller' => 'entidadesSectores'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/sectores/update',       'RutaController' => 'entidadesSectores->Update',      'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'entidadesSectores'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'administracion/entidades/sectores',              'RutaController' => 'entidadesSectores->Delete',      'Descripcion' => 'Borrar dato y archivos',                        'idLevelLimit' => 4, 'Controller' => 'entidadesSectores'];

                break;
            /******************************************/
            case 2:
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/listAll',                       'RutaController' => 'entidadesListado->listAll',                  'Descripcion' => 'Listar Toda la Información',                    'idLevelLimit' => 1, 'Controller' => 'entidadesListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/listado/search',                        'RutaController' => 'entidadesListado->UpdateList',               'Descripcion' => 'Filtrar datos',                                 'idLevelLimit' => 1, 'Controller' => 'entidadesListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/updateList',                    'RutaController' => 'entidadesListado->UpdateList',               'Descripcion' => 'Actualizar Lista',                              'idLevelLimit' => 2, 'Controller' => 'entidadesListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/exportar',                      'RutaController' => 'entidadesListado->export',                   'Descripcion' => 'Listar Todas las entidades para exportarlas',   'idLevelLimit' => 3, 'Controller' => 'entidadesListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/view/@id',                      'RutaController' => 'entidadesListado->View',                     'Descripcion' => 'Mostrar Detallado',                             'idLevelLimit' => 1, 'Controller' => 'entidadesListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/resumen/@id',                   'RutaController' => 'entidadesListado->Resumen',                  'Descripcion' => 'Mostrar Resúmen',                               'idLevelLimit' => 2, 'Controller' => 'entidadesListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/resumenUpdate/@id',             'RutaController' => 'entidadesListado->ResumenUpdate',            'Descripcion' => 'Mostrar información',                           'idLevelLimit' => 2, 'Controller' => 'entidadesListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/listado',                               'RutaController' => 'entidadesListado->Insert',                   'Descripcion' => 'Crear Información',                             'idLevelLimit' => 3, 'Controller' => 'entidadesListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/listado/update',                        'RutaController' => 'entidadesListado->Update',                   'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'entidadesListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 4, 'RutaWeb' => 'administracion/entidades/listado/delFiles',                      'RutaController' => 'entidadesListado->DelFiles',                 'Descripcion' => 'Permite eliminar archivos',                     'idLevelLimit' => 2, 'Controller' => 'entidadesListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'administracion/entidades/listado',                               'RutaController' => 'entidadesListado->Delete',                   'Descripcion' => 'Borrar dato y archivos',                        'idLevelLimit' => 4, 'Controller' => 'entidadesListado'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/observaciones/new/@id',         'RutaController' => 'entidadesListadoObservaciones->New',         'Descripcion' => 'Mostrar modal nuevo',                           'idLevelLimit' => 2, 'Controller' => 'entidadesListadoObservaciones'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/observaciones/updateList/@id',  'RutaController' => 'entidadesListadoObservaciones->UpdateList',  'Descripcion' => 'Actualizar Lista',                              'idLevelLimit' => 2, 'Controller' => 'entidadesListadoObservaciones'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/observaciones/view/@id',        'RutaController' => 'entidadesListadoObservaciones->View',        'Descripcion' => 'Mostrar Detallado',                             'idLevelLimit' => 2, 'Controller' => 'entidadesListadoObservaciones'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/observaciones/getID/@id',       'RutaController' => 'entidadesListadoObservaciones->GetID',       'Descripcion' => 'Información para el formulario edición',        'idLevelLimit' => 2, 'Controller' => 'entidadesListadoObservaciones'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/listado/observaciones',                 'RutaController' => 'entidadesListadoObservaciones->Insert',      'Descripcion' => 'Crear Información',                             'idLevelLimit' => 2, 'Controller' => 'entidadesListadoObservaciones'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/listado/observaciones/update',          'RutaController' => 'entidadesListadoObservaciones->Update',      'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'entidadesListadoObservaciones'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'administracion/entidades/listado/observaciones',                 'RutaController' => 'entidadesListadoObservaciones->Delete',      'Descripcion' => 'Borrar dato y archivos',                        'idLevelLimit' => 2, 'Controller' => 'entidadesListadoObservaciones'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/cargas/new/@id',                'RutaController' => 'entidadesListadoCargas->New',                'Descripcion' => 'Mostrar modal nuevo',                           'idLevelLimit' => 2, 'Controller' => 'entidadesListadoCargas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/cargas/updateList/@id',         'RutaController' => 'entidadesListadoCargas->UpdateList',         'Descripcion' => 'Actualizar Lista',                              'idLevelLimit' => 2, 'Controller' => 'entidadesListadoCargas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/cargas/view/@id',               'RutaController' => 'entidadesListadoCargas->View',               'Descripcion' => 'Mostrar Detallado',                             'idLevelLimit' => 2, 'Controller' => 'entidadesListadoCargas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/cargas/getID/@id',              'RutaController' => 'entidadesListadoCargas->GetID',              'Descripcion' => 'Información para el formulario edición',        'idLevelLimit' => 2, 'Controller' => 'entidadesListadoCargas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/listado/cargas',                        'RutaController' => 'entidadesListadoCargas->Insert',             'Descripcion' => 'Crear Información',                             'idLevelLimit' => 2, 'Controller' => 'entidadesListadoCargas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/listado/cargas/update',                 'RutaController' => 'entidadesListadoCargas->Update',             'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'entidadesListadoCargas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'administracion/entidades/listado/cargas',                        'RutaController' => 'entidadesListadoCargas->Delete',             'Descripcion' => 'Borrar dato y archivos',                        'idLevelLimit' => 2, 'Controller' => 'entidadesListadoCargas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/contactos/new/@id',             'RutaController' => 'entidadesListadoContactos->New',             'Descripcion' => 'Mostrar modal nuevo',                           'idLevelLimit' => 2, 'Controller' => 'entidadesListadoContactos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/contactos/updateList/@id',      'RutaController' => 'entidadesListadoContactos->UpdateList',      'Descripcion' => 'Actualizar Lista',                              'idLevelLimit' => 2, 'Controller' => 'entidadesListadoContactos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/contactos/view/@id',            'RutaController' => 'entidadesListadoContactos->View',            'Descripcion' => 'Mostrar Detallado',                             'idLevelLimit' => 2, 'Controller' => 'entidadesListadoContactos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/contactos/getID/@id',           'RutaController' => 'entidadesListadoContactos->GetID',           'Descripcion' => 'Información para el formulario edición',        'idLevelLimit' => 2, 'Controller' => 'entidadesListadoContactos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/listado/contactos',                     'RutaController' => 'entidadesListadoContactos->Insert',          'Descripcion' => 'Crear Información',                             'idLevelLimit' => 2, 'Controller' => 'entidadesListadoContactos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/listado/contactos/update',              'RutaController' => 'entidadesListadoContactos->Update',          'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'entidadesListadoContactos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'administracion/entidades/listado/contactos',                     'RutaController' => 'entidadesListadoContactos->Delete',          'Descripcion' => 'Borrar dato y archivos',                        'idLevelLimit' => 2, 'Controller' => 'entidadesListadoContactos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/documentos/new/@id',            'RutaController' => 'entidadesListadoDocumentos->New',            'Descripcion' => 'Mostrar modal nuevo',                           'idLevelLimit' => 2, 'Controller' => 'entidadesListadoDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/documentos/updateList/@id',     'RutaController' => 'entidadesListadoDocumentos->UpdateList',     'Descripcion' => 'Actualizar Lista',                              'idLevelLimit' => 2, 'Controller' => 'entidadesListadoDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/documentos/view/@id',           'RutaController' => 'entidadesListadoDocumentos->View',           'Descripcion' => 'Mostrar Detallado',                             'idLevelLimit' => 2, 'Controller' => 'entidadesListadoDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'administracion/entidades/listado/documentos/getID/@id',          'RutaController' => 'entidadesListadoDocumentos->GetID',          'Descripcion' => 'Información para el formulario edición',        'idLevelLimit' => 2, 'Controller' => 'entidadesListadoDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/listado/documentos',                    'RutaController' => 'entidadesListadoDocumentos->Insert',         'Descripcion' => 'Crear Información',                             'idLevelLimit' => 2, 'Controller' => 'entidadesListadoDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'administracion/entidades/listado/documentos/update',             'RutaController' => 'entidadesListadoDocumentos->Update',         'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'entidadesListadoDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'administracion/entidades/listado/documentos',                    'RutaController' => 'entidadesListadoDocumentos->Delete',         'Descripcion' => 'Borrar dato y archivos',                        'idLevelLimit' => 2, 'Controller' => 'entidadesListadoDocumentos'];

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
        $RutaController  = '"entidadesSectores"';
        $RutaController .= ',"entidadesListado"';
        $RutaController .= ',"entidadesListadoObservaciones"';
        $RutaController .= ',"entidadesListadoCargas"';
        $RutaController .= ',"entidadesListadoContactos"';
        $RutaController .= ',"entidadesListadoDocumentos"';

        //devuelvo
        return $RutaController;
    }

}