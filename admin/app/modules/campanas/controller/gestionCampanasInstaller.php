<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class gestionCampanasInstaller extends ControllerBase {

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
        $this->controllerName      = 'gestionCampanasInstaller';
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
        //Se instancian los datos
        $entidadesInstaller  = new entidadesInstaller();
		$productosInstaller  = new productosInstaller();
		$bodegasInstaller    = new bodegasInstaller();
		$documentosInstaller = new gestionDocumentosInstaller();

        /*******************************************************/
        //Rutas
        $nData1    = $this->GetCountDataModule();
        $DepData1  = $entidadesInstaller->GetCountDataModule();
        $DepData2  = $productosInstaller->GetCountDataModule();
        $DepData3  = $bodegasInstaller->GetCountDataModule();
        $DepData4  = $documentosInstaller->GetCountDataModule();

        /******************************************/
        //si es la respuesta esperada
        $countPermisos = is_numeric($nData1) ? 1 : 0;
        $DepInstall_1  = is_numeric($DepData1) ? 1 : 0;
        $DepInstall_2  = is_numeric($DepData2) ? 1 : 0;
        $DepInstall_3  = is_numeric($DepData3) ? 1 : 0;
        $DepInstall_4  = is_numeric($DepData4) ? 1 : 0;

        /******************************************/
        //Verificar que existan los permisos
        $arrData = [
            'Nombre'        => 'Módulo de Gestión de Campañas',
            'Descripcion'   => 'Módulo para gestionar las Campañas',
            'Controller'    => $this->controllerName,
            'countPermisos' => $countPermisos,
            'Dependencias'  => [
                [
                    'Nombre' => ' - Módulo de Gestión de Entidades instalado',
                    'Numero' => $DepInstall_1,
                ],
                [
                    'Nombre' => ' - Módulo de Gestión de Productos instalado',
                    'Numero' => $DepInstall_2,
                ],
                [
                    'Nombre' => ' - Módulo de Gestión de Bodegas instalado',
                    'Numero' => $DepInstall_3,
                ],
                [
                    'Nombre' => ' - Módulo de Gestión de Documentos instalado',
                    'Numero' => $DepInstall_4,
                ],
            ]
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
            'table'      => 'campanas_listado',
            'data'       => '`idCampana` bigint UNSIGNED NOT NULL AUTO_INCREMENT,`idUsuario` int UNSIGNED NOT NULL,`idEstado` int UNSIGNED NOT NULL,`fecha_auto` date NOT NULL,`Fecha` date NOT NULL,`Fecha_Dia` int UNSIGNED NOT NULL,`Fecha_Mes` int UNSIGNED NOT NULL,`Fecha_Ano` int UNSIGNED NOT NULL,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`Observaciones` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,`Costos` decimal(15, 2) UNSIGNED NULL DEFAULT NULL,`Beneficios` decimal(15, 2) UNSIGNED NULL DEFAULT NULL,`Perdidas` decimal(15, 2) UNSIGNED NULL DEFAULT NULL,`Margen` decimal(15, 2) NULL DEFAULT NULL,`idBodegas` int UNSIGNED NOT NULL',
            'primaryKey' => 'idCampana',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'campanas_listado_costos',
            'data'       => '`idExistencia` bigint UNSIGNED NOT NULL AUTO_INCREMENT,`idCampana` bigint UNSIGNED NOT NULL,`Fecha` date NOT NULL,`Item` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`Costos` decimal(15, 2) UNSIGNED NOT NULL,`idFacturacion` bigint UNSIGNED NULL DEFAULT NULL,`idUsuario` int UNSIGNED NOT NULL',
            'primaryKey' => 'idExistencia',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'campanas_listado_partidas',
            'data'       => '`idExistencia` bigint UNSIGNED NOT NULL AUTO_INCREMENT,`idCampana` bigint UNSIGNED NOT NULL,`Fecha` date NOT NULL,`Fecha_Dia` int UNSIGNED NOT NULL,`Fecha_Mes` int UNSIGNED NOT NULL,`Fecha_Ano` int UNSIGNED NOT NULL,`idEntidad` int UNSIGNED NOT NULL,`Beneficios` decimal(15, 2) UNSIGNED NULL DEFAULT NULL,`idEstadoPartida` int UNSIGNED NOT NULL,`idFacturacion` bigint UNSIGNED NULL DEFAULT NULL,`idUsuario` int UNSIGNED NOT NULL,`ConfirmacionFecha` date NULL DEFAULT NULL,`ConfirmacionHora` time NULL DEFAULT NULL',
            'primaryKey' => 'idExistencia',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'campanas_listado_partidas_productos',
            'data'       => '`idProdCamp` bigint UNSIGNED NOT NULL AUTO_INCREMENT,`idExistencia` bigint UNSIGNED NOT NULL,`idCampana` bigint UNSIGNED NOT NULL,`idProducto` int UNSIGNED NULL DEFAULT NULL,`Cantidad` decimal(10, 2) UNSIGNED NULL DEFAULT NULL,`Beneficios` decimal(15, 2) UNSIGNED NULL DEFAULT NULL',
            'primaryKey' => 'idProdCamp',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'campanas_listado_perdidas',
            'data'       => '`idExistencia` bigint UNSIGNED NOT NULL AUTO_INCREMENT,`idCampana` bigint UNSIGNED NOT NULL,`Fecha` date NOT NULL,`Fecha_Dia` int UNSIGNED NOT NULL,`Fecha_Mes` int UNSIGNED NOT NULL,`Fecha_Ano` int UNSIGNED NOT NULL,`Item` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`idProducto` int UNSIGNED NULL DEFAULT NULL,`Cantidad` decimal(10, 2) UNSIGNED NULL DEFAULT NULL,`Perdidas` decimal(15, 2) UNSIGNED NOT NULL,`idUsuario` int UNSIGNED NOT NULL,`idMovimiento` int UNSIGNED NULL DEFAULT NULL',
            'primaryKey' => 'idExistencia',
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
                $xParams = ['query' => $table];
                $this->Base_createTable($xParams);
            }
        }

        /*******************************************************/
        /*                 SE GENERAN LAS RUTAS                */
        /*******************************************************/
        $arrPermisos[] = [
            'idPermisosCat'  => '5',
            'idEstado'       => '1',
            'idTipo'         => '2',
            'Nombre'         => 'Campañas - Listado',
            'Descripcion'    => 'Permite la generacion de campañas',
            'idLevelLimit'   => '4',
            'RutaWeb'        => 'gestionCampanas/campanas/listado',
            'RutaController' => 'gestionCampanas',
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '5',
            'idEstado'       => '1',
            'idTipo'         => '3',
            'Nombre'         => 'Informe Cobranzas',
            'Descripcion'    => 'Permite filtrar los clientes con pagos atrasados',
            'idLevelLimit'   => '1',
            'RutaWeb'        => 'gestionCampanas/cobranzas/listado',
            'RutaController' => 'cobranzaCampanas',
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '5',
            'idEstado'       => '1',
            'idTipo'         => '3',
            'Nombre'         => 'Exportar Datos',
            'Descripcion'    => 'Permite exportar todos los datos',
            'idLevelLimit'   => '1',
            'RutaWeb'        => 'gestionCampanas/informe/exportacionDatos',
            'RutaController' => 'exportarCampanas',
        ];
        $arrPermisos[] = [
            'idPermisosCat'  => '5',
            'idEstado'       => '1',
            'idTipo'         => '3',
            'Nombre'         => 'Informe Pagos',
            'Descripcion'    => 'Permite filtrar los documentos con falta de pagos para ingresar los pagos',
            'idLevelLimit'   => '2',
            'RutaWeb'        => 'gestionCampanas/pagos/listado',
            'RutaController' => 'pagosCampanas',
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

        /*******************************************************/
        /*              SE ELIMINAN LAS TABLAS                 */
        /*******************************************************/
        $arrTableDel  = array();
        $arrTableDel[] = ['table' => 'campanas_listado'];
        $arrTableDel[] = ['table' => 'campanas_listado_costos'];
        $arrTableDel[] = ['table' => 'campanas_listado_partidas'];
        $arrTableDel[] = ['table' => 'campanas_listado_partidas_productos'];
        $arrTableDel[] = ['table' => 'campanas_listado_perdidas'];

        /************************************************/
        //Verifico si existe
        if($arrTableDel){
            //recorro
            foreach ($arrTableDel as $tblDel) {
                //Se ejecuta la query
                $xParams = ['query' => $tblDel];
                $this->Base_dropTable($xParams);
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
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/campanas/listado/listAll',                                                    'RutaController' => 'gestionCampanas->listAll',                         'Descripcion' => 'Listar Toda la Información',                    'idLevelLimit' => 1, 'Controller' => 'gestionCampanas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionCampanas/campanas/listado/search',                                                     'RutaController' => 'gestionCampanas->UpdateList',                      'Descripcion' => 'Filtrar datos',                                 'idLevelLimit' => 1, 'Controller' => 'gestionCampanas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/campanas/listado/updateList',                                                 'RutaController' => 'gestionCampanas->UpdateList',                      'Descripcion' => 'Actualizar Lista',                              'idLevelLimit' => 2, 'Controller' => 'gestionCampanas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/campanas/listado/view/@id',                                                   'RutaController' => 'gestionCampanas->View',                            'Descripcion' => 'Mostrar Detallado',                             'idLevelLimit' => 1, 'Controller' => 'gestionCampanas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/campanas/listado/print/@id',                                                  'RutaController' => 'gestionCampanas->Print',                           'Descripcion' => 'Pantalla imprimir',                             'idLevelLimit' => 1, 'Controller' => 'gestionCampanas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/campanas/listado/noPrint/@id',                                                'RutaController' => 'gestionCampanas->noPrint',                         'Descripcion' => 'Pantalla para visualizar documento',            'idLevelLimit' => 1, 'Controller' => 'gestionCampanas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/campanas/listado/resumen/@id',                                                'RutaController' => 'gestionCampanas->Resumen',                         'Descripcion' => 'Mostrar Resúmen',                               'idLevelLimit' => 2, 'Controller' => 'gestionCampanas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/campanas/listado/resumenUpdate/@id',                                          'RutaController' => 'gestionCampanas->ResumenUpdate',                   'Descripcion' => 'Mostrar información',                           'idLevelLimit' => 2, 'Controller' => 'gestionCampanas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionCampanas/campanas/listado',                                                            'RutaController' => 'gestionCampanas->Insert',                          'Descripcion' => 'Crear Información',                             'idLevelLimit' => 3, 'Controller' => 'gestionCampanas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionCampanas/campanas/listado/update',                                                     'RutaController' => 'gestionCampanas->Update',                          'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'gestionCampanas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 4, 'RutaWeb' => 'gestionCampanas/campanas/listado/delFiles',                                                   'RutaController' => 'gestionCampanas->DelFiles',                        'Descripcion' => 'Permite eliminar archivos',                     'idLevelLimit' => 2, 'Controller' => 'gestionCampanas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'gestionCampanas/campanas/listado',                                                            'RutaController' => 'gestionCampanas->Delete',                          'Descripcion' => 'Borrar dato y archivos',                        'idLevelLimit' => 4, 'Controller' => 'gestionCampanas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/campanas/listado/costos/new/@id',                                             'RutaController' => 'gestionCampanasCostos->New',                       'Descripcion' => 'Mostrar modal nuevo',                           'idLevelLimit' => 2, 'Controller' => 'gestionCampanasCostos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/campanas/listado/costos/newDoc/@id',                                          'RutaController' => 'gestionCampanasCostos->NewDoc',                    'Descripcion' => 'Mostrar modal nuevo',                           'idLevelLimit' => 2, 'Controller' => 'gestionCampanasCostos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/campanas/listado/costos/updateList/@id',                                      'RutaController' => 'gestionCampanasCostos->UpdateList',                'Descripcion' => 'Actualizar Lista',                              'idLevelLimit' => 2, 'Controller' => 'gestionCampanasCostos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/campanas/listado/costos/getID/@id',                                           'RutaController' => 'gestionCampanasCostos->GetID',                     'Descripcion' => 'Información para el formulario edición',        'idLevelLimit' => 2, 'Controller' => 'gestionCampanasCostos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/campanas/listado/costos/view/@id',                                            'RutaController' => 'gestionDocumentos->View_1',                        'Descripcion' => 'Mostrar Detallado',                             'idLevelLimit' => 1, 'Controller' => 'gestionDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionCampanas/campanas/listado/costos',                                                     'RutaController' => 'gestionCampanasCostos->Insert',                    'Descripcion' => 'Crear Información',                             'idLevelLimit' => 2, 'Controller' => 'gestionCampanasCostos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionCampanas/campanas/listado/costosDoc',                                                  'RutaController' => 'gestionCampanasCostos->InsertDoc',                 'Descripcion' => 'Crear Información',                             'idLevelLimit' => 2, 'Controller' => 'gestionCampanasCostos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionCampanas/campanas/listado/costos/update',                                              'RutaController' => 'gestionCampanasCostos->Update',                    'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'gestionCampanasCostos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'gestionCampanas/campanas/listado/costos',                                                     'RutaController' => 'gestionCampanasCostos->Delete',                    'Descripcion' => 'Borrar dato y archivos',                        'idLevelLimit' => 2, 'Controller' => 'gestionCampanasCostos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/campanas/listado/partidas/new_step1/@id',                                     'RutaController' => 'gestionCampanasPartidas->New_step1',               'Descripcion' => 'Mostrar primera parte modal nuevo',             'idLevelLimit' => 2, 'Controller' => 'gestionCampanasPartidas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionCampanas/campanas/listado/partidas/new_step1',                                         'RutaController' => 'gestionCampanasPartidas->New_step2',               'Descripcion' => 'Mostrar Selección',                             'idLevelLimit' => 2, 'Controller' => 'gestionCampanasPartidas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/campanas/listado/partidas/updateList/@id',                                    'RutaController' => 'gestionCampanasPartidas->UpdateList',              'Descripcion' => 'Actualizar Lista',                              'idLevelLimit' => 2, 'Controller' => 'gestionCampanasPartidas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/campanas/listado/partidas/getID/@id',                                         'RutaController' => 'gestionCampanasPartidas->GetID',                   'Descripcion' => 'Información para el formulario edición',        'idLevelLimit' => 2, 'Controller' => 'gestionCampanasPartidas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionCampanas/campanas/listado/partidas',                                                   'RutaController' => 'gestionCampanasPartidas->Insert',                  'Descripcion' => 'Crear Información',                             'idLevelLimit' => 2, 'Controller' => 'gestionCampanasPartidas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionCampanas/campanas/listado/partidas/update',                                            'RutaController' => 'gestionCampanasPartidas->Update',                  'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'gestionCampanasPartidas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'gestionCampanas/campanas/listado/partidas',                                                   'RutaController' => 'gestionCampanasPartidas->Delete',                  'Descripcion' => 'Borrar dato y archivos',                        'idLevelLimit' => 2, 'Controller' => 'gestionCampanasPartidas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'gestionCampanas/campanas/listado/partidas/delMassive',                                        'RutaController' => 'gestionCampanasPartidas->DeleteMassive',           'Descripcion' => 'Borra datos de forma masiva',                   'idLevelLimit' => 2, 'Controller' => 'gestionCampanasPartidas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/campanas/listado/partidas/new_unique/@id',                                    'RutaController' => 'gestionCampanasPartidas->New_unique',              'Descripcion' => 'Mostrar primera parte modal nuevo',             'idLevelLimit' => 2, 'Controller' => 'gestionCampanasPartidas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionCampanas/campanas/listado/partidas/new_unique',                                        'RutaController' => 'gestionCampanasPartidas->InsertUnique',            'Descripcion' => 'Crear Información',                             'idLevelLimit' => 2, 'Controller' => 'gestionCampanasPartidas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionCampanas/campanas/listado/partidas/sendCampanaManual',                                 'RutaController' => 'gestionCampanasPartidas->Update',                  'Descripcion' => 'Envio Manual Whatsapp',                         'idLevelLimit' => 2, 'Controller' => 'gestionCampanasPartidas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/campanas/listado/partidas/sendCampanaMassive/@CampanaID/@Fecha/@PartidaID',   'RutaController' => 'gestionCampanasPartidas->sendCampanaMassiveForm',  'Descripcion' => 'Informacion para el formulario de Envio',       'idLevelLimit' => 2, 'Controller' => 'gestionCampanasPartidas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionCampanas/campanas/listado/partidas/sendCampanaMassive',                                'RutaController' => 'gestionCampanasPartidas->sendCampanaMassive',      'Descripcion' => 'Envio Masivo Whatsapp',                         'idLevelLimit' => 2, 'Controller' => 'gestionCampanasPartidas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/campanas/listado/partidas/sendCampanaWhatsapp/@ExistenciaID',                 'RutaController' => 'gestionCampanasPartidas->sendCampanaWhatsappForm', 'Descripcion' => 'Informacion para el formulario de Envio',       'idLevelLimit' => 2, 'Controller' => 'gestionCampanasPartidas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionCampanas/campanas/listado/partidas/sendCampanaWhatsapp',                               'RutaController' => 'gestionCampanasPartidas->sendCampanaWhatsapp',     'Descripcion' => 'Envio Unico Whatsapp',                          'idLevelLimit' => 2, 'Controller' => 'gestionCampanasPartidas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/campanas/listado/partidasFinalizadas/updateList/@id',                         'RutaController' => 'gestionCampanasPartidas->UpdateListFin',           'Descripcion' => 'Actualizar Lista',                              'idLevelLimit' => 2, 'Controller' => 'gestionCampanasPartidas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/campanas/listado/partidasFinalizadas/view/@id',                               'RutaController' => 'gestionDocumentos->View_2',                        'Descripcion' => 'Mostrar Detallado',                             'idLevelLimit' => 1, 'Controller' => 'gestionDocumentos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/campanas/listado/partidasFinalizadas/getID/@id',                              'RutaController' => 'gestionCampanasPartidas->GetIDFinalizadas',        'Descripcion' => 'Información para el formulario edición',        'idLevelLimit' => 2, 'Controller' => 'gestionCampanasPartidas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionCampanas/campanas/listado/partidasFinalizadas/update',                                 'RutaController' => 'gestionCampanasPartidas->UpdateFinalizadas',       'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'gestionCampanasPartidas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/campanas/listado/perdidas/new/@id',                                           'RutaController' => 'gestionCampanasPerdidas->New',                     'Descripcion' => 'Mostrar primera parte modal nuevo',             'idLevelLimit' => 2, 'Controller' => 'gestionCampanasPerdidas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/campanas/listado/perdidas/updateList/@id',                                    'RutaController' => 'gestionCampanasPerdidas->UpdateList',              'Descripcion' => 'Actualizar Lista',                              'idLevelLimit' => 2, 'Controller' => 'gestionCampanasPerdidas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/campanas/listado/perdidas/getID/@id',                                         'RutaController' => 'gestionCampanasPerdidas->GetID',                   'Descripcion' => 'Información para el formulario edición',        'idLevelLimit' => 2, 'Controller' => 'gestionCampanasPerdidas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionCampanas/campanas/listado/perdidas',                                                   'RutaController' => 'gestionCampanasPerdidas->Insert',                  'Descripcion' => 'Crear Información',                             'idLevelLimit' => 2, 'Controller' => 'gestionCampanasPerdidas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionCampanas/campanas/listado/perdidas/update',                                            'RutaController' => 'gestionCampanasPerdidas->Update',                  'Descripcion' => 'Editar por post (modificar y subir archivos)',  'idLevelLimit' => 2, 'Controller' => 'gestionCampanasPerdidas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'gestionCampanas/campanas/listado/perdidas',                                                   'RutaController' => 'gestionCampanasPerdidas->Delete',                  'Descripcion' => 'Borrar dato y archivos',                        'idLevelLimit' => 2, 'Controller' => 'gestionCampanasPerdidas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/campanas/listado/perdidas/view/@id',                                          'RutaController' => 'bodegasMovimiento->View_2',                        'Descripcion' => 'Mostrar Detallado',                             'idLevelLimit' => 1, 'Controller' => 'bodegasMovimiento'];

                break;
            /******************************************/
            case 2:
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/cobranzas/listado/listAll', 'RutaController' => 'cobranzaCampanas->listAll',    'Descripcion' => 'Filtro de búsqueda', 'idLevelLimit' => 1, 'Controller' => 'cobranzaCampanas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionCampanas/cobranzas/listado/search',  'RutaController' => 'cobranzaCampanas->UpdateList', 'Descripcion' => 'Filtrar datos',      'idLevelLimit' => 1, 'Controller' => 'cobranzaCampanas'];

                break;
            /******************************************/
            case 3:
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/informe/exportacionDatos/listAll',         'RutaController' => 'exportarCampanas->listAll',      'Descripcion' => 'Filtro de búsqueda', 'idLevelLimit' => 1, 'Controller' => 'exportarCampanas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionCampanas/informe/exportacionDatos/search',          'RutaController' => 'exportarCampanas->UpdateList',   'Descripcion' => 'Filtrar datos',      'idLevelLimit' => 1, 'Controller' => 'exportarCampanas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/informe/exportacionDatos/print/@id',       'RutaController' => 'exportarCampanas->Print',        'Descripcion' => 'Pantalla imprimir',  'idLevelLimit' => 1, 'Controller' => 'exportarCampanas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/informe/exportacionDatos/exportExcel/@id', 'RutaController' => 'exportarCampanas->exportExcel',  'Descripcion' => 'Exportar Excel',     'idLevelLimit' => 1, 'Controller' => 'exportarCampanas'];

                break;
            /******************************************/
            case 4:
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/pagos/listado/listAll',              'RutaController' => 'pagosCampanas->listAll',         'Descripcion' => 'Filtro de búsqueda',                           'idLevelLimit' => 1, 'Controller' => 'pagosCampanas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionCampanas/pagos/listado/search',               'RutaController' => 'pagosCampanas->UpdateList',      'Descripcion' => 'Filtrar datos',                                'idLevelLimit' => 1, 'Controller' => 'pagosCampanas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/pagos/listado/resumen/@id',          'RutaController' => 'pagosCampanas->Resumen',         'Descripcion' => 'Mostrar Resúmen',                              'idLevelLimit' => 2, 'Controller' => 'pagosCampanas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/pagos/listado/pagos/new/@id',        'RutaController' => 'pagosCampanas->New_2',           'Descripcion' => 'Mostrar modal nuevo',                          'idLevelLimit' => 2, 'Controller' => 'pagosCampanas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/pagos/listado/pagos/updateList/@id', 'RutaController' => 'pagosCampanas->UpdateList_2',    'Descripcion' => 'Actualizar Lista',                             'idLevelLimit' => 2, 'Controller' => 'pagosCampanas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 1, 'RutaWeb' => 'gestionCampanas/pagos/listado/pagos/getID/@id',      'RutaController' => 'pagosCampanas->GetID_2',         'Descripcion' => 'Información para el formulario edición',       'idLevelLimit' => 2, 'Controller' => 'pagosCampanas'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionCampanas/pagos/listado/pagos',                'RutaController' => 'gestionDocumentosPagos->Insert', 'Descripcion' => 'Crear Información',                            'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosPagos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 2, 'RutaWeb' => 'gestionCampanas/pagos/listado/pagos/update',         'RutaController' => 'gestionDocumentosPagos->Update', 'Descripcion' => 'Editar por post (modificar y subir archivos)', 'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosPagos'];
                $arrRutas[] = ['idPermisos' => $permisosID, 'idMetodo' => 3, 'RutaWeb' => 'gestionCampanas/pagos/listado/pagos',                'RutaController' => 'gestionDocumentosPagos->Delete', 'Descripcion' => 'Borrar dato y archivos',                       'idLevelLimit' => 2, 'Controller' => 'gestionDocumentosPagos'];

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
        $RutaController  = '"gestionCampanas"';
        $RutaController .= ',"gestionCampanasCostos"';
        $RutaController .= ',"gestionCampanasPartidas"';
        $RutaController .= ',"gestionCampanasPerdidas"';
        $RutaController .= ',"cobranzaCampanas"';
        $RutaController .= ',"exportarCampanas"';
        $RutaController .= ',"pagosCampanas"';

        //devuelvo
        return $RutaController;
    }



}
