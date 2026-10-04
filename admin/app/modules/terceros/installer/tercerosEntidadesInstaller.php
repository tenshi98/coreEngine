<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class tercerosEntidadesInstaller extends ControllerInstaller {

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
        $this->controllerName = 'tercerosEntidadesInstaller';
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

        /*******************************************************/
        // Se instancian otros controladores
        $entidadesInstaller = new entidadesInstaller();
        $serviciosInstaller = new serviciosInstaller();
        $maquinasInstaller  = new maquinasInstaller();

        /************************************/
        // Se verifica si esta instalado
        $nData1    = $this->GetCountDataModule();
        $DepData1  = $entidadesInstaller->GetCountDataModule();
        $DepData2  = $serviciosInstaller->GetCountDataModule();
        $DepData3  = $maquinasInstaller->GetCountDataModule();

        /************************************/
        // Defino si etsa instalado en base a la respuesta
        $countPermisos = is_numeric($nData1)&&$nData1!=0 ? 1 : 0;
        $DepInstall_1  = is_numeric($DepData1)&&$DepData1!=0 ? 1 : 0;
        $DepInstall_2  = is_numeric($DepData2)&&$DepData2!=0 ? 1 : 0;
        $DepInstall_3  = is_numeric($DepData3)&&$DepData3!=0 ? 1 : 0;

        /************************************/
        // Se crean los datos a mostrar
        $arrData = [
            'Nombre'        => 'Módulo de Gestión de Clientes - Opciones Extras',
            'Descripcion'   => 'Módulo para gestionar a las Clientes - Opciones Extras',
            'Controller'    => $this->controllerName,
            'countPermisos' => $countPermisos,
            'Dependencias'  => [
                [
                    'Nombre' => ' - Módulo de Gestión de Entidades instalado',
                    'Numero' => $DepInstall_1,
                ],
                [
                    'Nombre' => ' - Módulo de Gestión de Servicios instalado',
                    'Numero' => $DepInstall_2,
                ],
                [
                    'Nombre' => ' - Módulo de Gestión de Maquinas instalado',
                    'Numero' => $DepInstall_3,
                ],
            ]
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
        $arrTables    = $this->listTables();
        $arrPermisos  = array();

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

        /*******************************************************/
        /*                 SE GENERAN LAS RUTAS                */
        /*******************************************************/
        $arrPermisos[] = [
            'idPermisosCat'  => '6',                                                       // Externalización Servicios
            'idEstado'       => '1',                                                       // Activo
            'idTipo'         => '2',                                                       // Crud Resumen
            'Nombre'         => 'Clientes - Opciones Extras',                              // Nombre de la transaccion
            'Descripcion'    => 'Permite administrar las opciones extras de los clientes', // Descripcion de la transaccion
            'idLevelLimit'   => '4',                                                       // Ver / Editar / Crear / Borrar
            'RutaWeb'        => 'serviciosTerceros/entidades/listado',                     // Ruta web de la transaccion
            'RutaController' => 'tercerosEntidadesListado',                                // Controlador de la transaccion
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
                ['idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/listAll',                                         'RutaController' => 'tercerosEntidadesListado->listAll',                  'Descripcion' => 'Listar Toda la Información',                     'idLevelLimit' => 1, 'Controller' => 'tercerosEntidadesListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'serviciosTerceros/entidades/listado/search',                                          'RutaController' => 'tercerosEntidadesListado->UpdateList',               'Descripcion' => 'Filtrar datos',                                  'idLevelLimit' => 1, 'Controller' => 'tercerosEntidadesListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/updateList',                                      'RutaController' => 'tercerosEntidadesListado->UpdateList',               'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/view/@id',                                        'RutaController' => 'tercerosEntidadesListado->View',                     'Descripcion' => 'Mostrar Detallado',                              'idLevelLimit' => 1, 'Controller' => 'tercerosEntidadesListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/resumen/@id',                                     'RutaController' => 'tercerosEntidadesListado->Resumen',                  'Descripcion' => 'Mostrar Resúmen',                                'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/resumenUpdate/@id',                               'RutaController' => 'tercerosEntidadesListado->ResumenUpdate',            'Descripcion' => 'Mostrar información',                            'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListado'],
                ['idMetodo' => 2, 'RutaWeb' => 'serviciosTerceros/entidades/listado/update',                                          'RutaController' => 'tercerosEntidadesListado->Update',                   'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListado'],
                ['idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/planes/new/@id',                                  'RutaController' => 'tercerosEntidadesListadoPlanes->New',                'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoPlanes'],
                ['idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/planes/updateList/@id',                           'RutaController' => 'tercerosEntidadesListadoPlanes->UpdateList',         'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoPlanes'],
                ['idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/planes/view/@id',                                 'RutaController' => 'tercerosEntidadesListadoPlanes->View',               'Descripcion' => 'Mostrar Detallado',                              'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoPlanes'],
                ['idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/planes/getID/@id',                                'RutaController' => 'tercerosEntidadesListadoPlanes->GetID',              'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoPlanes'],
                ['idMetodo' => 2, 'RutaWeb' => 'serviciosTerceros/entidades/listado/planes',                                          'RutaController' => 'tercerosEntidadesListadoPlanes->Insert',             'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoPlanes'],
                ['idMetodo' => 2, 'RutaWeb' => 'serviciosTerceros/entidades/listado/planes/update',                                   'RutaController' => 'tercerosEntidadesListadoPlanes->Update',             'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoPlanes'],
                ['idMetodo' => 3, 'RutaWeb' => 'serviciosTerceros/entidades/listado/planes',                                          'RutaController' => 'tercerosEntidadesListadoPlanes->Delete',             'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoPlanes'],
                ['idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/usuarios/new/@id',                                'RutaController' => 'tercerosEntidadesListadoUsuarios->New',              'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoUsuarios'],
                ['idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/usuarios/updateList/@id',                         'RutaController' => 'tercerosEntidadesListadoUsuarios->UpdateList',       'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoUsuarios'],
                ['idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/usuarios/view/@id',                               'RutaController' => 'tercerosEntidadesListadoUsuarios->View',             'Descripcion' => 'Mostrar Detallado',                              'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoUsuarios'],
                ['idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/usuarios/getID/@id',                              'RutaController' => 'tercerosEntidadesListadoUsuarios->GetID',            'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoUsuarios'],
                ['idMetodo' => 2, 'RutaWeb' => 'serviciosTerceros/entidades/listado/usuarios',                                        'RutaController' => 'tercerosEntidadesListadoUsuarios->Insert',           'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoUsuarios'],
                ['idMetodo' => 2, 'RutaWeb' => 'serviciosTerceros/entidades/listado/usuarios/update',                                 'RutaController' => 'tercerosEntidadesListadoUsuarios->Update',           'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoUsuarios'],
                ['idMetodo' => 3, 'RutaWeb' => 'serviciosTerceros/entidades/listado/usuarios',                                        'RutaController' => 'tercerosEntidadesListadoUsuarios->Delete',           'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoUsuarios'],
                ['idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/maquinas/new/@id',                                'RutaController' => 'tercerosEntidadesListadoMaquinas->New',              'Descripcion' => 'Mostrar modal nuevo',                            'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoMaquinas'],
                ['idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/maquinas/updateList/@id',                         'RutaController' => 'tercerosEntidadesListadoMaquinas->UpdateList',       'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoMaquinas'],
                ['idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/maquinas/view/@id',                               'RutaController' => 'tercerosEntidadesListadoMaquinas->View',             'Descripcion' => 'Mostrar Detallado',                              'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoMaquinas'],
                ['idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/maquinas/getID/@id',                              'RutaController' => 'tercerosEntidadesListadoMaquinas->GetID',            'Descripcion' => 'Información para el formulario edición',         'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoMaquinas'],
                ['idMetodo' => 2, 'RutaWeb' => 'serviciosTerceros/entidades/listado/maquinas',                                        'RutaController' => 'tercerosEntidadesListadoMaquinas->Insert',           'Descripcion' => 'Crear Información',                              'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoMaquinas'],
                ['idMetodo' => 2, 'RutaWeb' => 'serviciosTerceros/entidades/listado/maquinas/update',                                 'RutaController' => 'tercerosEntidadesListadoMaquinas->Update',           'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoMaquinas'],
                ['idMetodo' => 3, 'RutaWeb' => 'serviciosTerceros/entidades/listado/maquinas',                                        'RutaController' => 'tercerosEntidadesListadoMaquinas->Delete',           'Descripcion' => 'Borrar dato y archivos',                         'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoMaquinas'],
                ['idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/usuariosMaq/updateList/@idEntidad/@idUsuario',    'RutaController' => 'tercerosEntidadesListadoUsuariosMaq->UpdateList',    'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoUsuariosMaq'],
                ['idMetodo' => 2, 'RutaWeb' => 'serviciosTerceros/entidades/listado/usuariosMaq/update',                              'RutaController' => 'tercerosEntidadesListadoUsuariosMaq->Update',        'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoUsuariosMaq'],
                ['idMetodo' => 1, 'RutaWeb' => 'serviciosTerceros/entidades/listado/usuariosNoti/updateList/@idEntidad/@idUsuario',   'RutaController' => 'tercerosEntidadesListadoUsuariosNoti->UpdateList',   'Descripcion' => 'Actualizar Lista',                               'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoUsuariosNoti'],
                ['idMetodo' => 2, 'RutaWeb' => 'serviciosTerceros/entidades/listado/usuariosNoti/update',                             'RutaController' => 'tercerosEntidadesListadoUsuariosNoti->Update',       'Descripcion' => 'Editar por post (modificar y subir archivos)',   'idLevelLimit' => 2, 'Controller' => 'tercerosEntidadesListadoUsuariosNoti'],
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
        $RutaController  = '"tercerosEntidadesListado"';
        $RutaController .= ',"tercerosEntidadesListadoPlanes"';
        $RutaController .= ',"tercerosEntidadesListadoUsuarios"';
        $RutaController .= ',"tercerosEntidadesListadoMaquinas"';
        $RutaController .= ',"tercerosEntidadesListadoUsuariosMaq"';
        $RutaController .= ',"tercerosEntidadesListadoUsuariosNoti"';

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
            'table'      => 'terceros_entidades_listado',
            'data'       => '`idTerceros` int(10) unsigned NOT NULL AUTO_INCREMENT,`idEntidad` int(10) unsigned NOT NULL',
            'primaryKey' => 'idTerceros',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'terceros_entidades_listado_maquinas',
            'data'       => '`idMaq` int(10) unsigned NOT NULL AUTO_INCREMENT,`idEntidad` int(10) unsigned NOT NULL,`idMaquina` int(10) unsigned NOT NULL,`idEstado` int(10) unsigned NOT NULL,`idUsuario` int(10) unsigned NOT NULL,`Fecha` date NULL DEFAULT NULL,`Observacion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL',
            'primaryKey' => 'idMaq',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'terceros_entidades_listado_planes',
            'data'       => '`idPlan` int(10) unsigned NOT NULL AUTO_INCREMENT,`idEntidad` int(10) unsigned NOT NULL,`idServicio` int(10) unsigned NOT NULL,`idEstado` int(10) unsigned NOT NULL,`idUsuario` int(10) unsigned NOT NULL,`Fecha` date NULL DEFAULT NULL,`Monto` decimal(10, 2) UNSIGNED NOT NULL,`Observacion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL',
            'primaryKey' => 'idPlan',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'terceros_entidades_listado_usuarios',
            'data'       => '`idUsuario` int(10) unsigned NOT NULL AUTO_INCREMENT,`idEntidad` int(10) unsigned NOT NULL,`password` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`idTipoUsuario` int(10) unsigned NOT NULL,`idEstado` int(10) unsigned NOT NULL,`email` varchar(60) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`Nombre` varchar(60) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`Rut` varchar(13) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Fono` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Direccion_img` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Ultimo_acceso` date NULL DEFAULT NULL,`IP_Client` varchar(120) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Agent_Transp` varchar(240) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL',
            'primaryKey' => 'idUsuario',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'terceros_entidades_listado_usuarios_maq',
            'data'       => '`idPermiso` int(10) unsigned NOT NULL AUTO_INCREMENT,`idUsuario` int(10) unsigned NOT NULL,`idMaquina` int(10) unsigned NOT NULL',
            'primaryKey' => 'idPermiso',
            'comentario' => 'Creado desde el Instalador',
        ];
        $arrTables[] = [
            'table'      => 'terceros_entidades_listado_usuarios_noti',
            'data'       => '`idPermiso` int(10) unsigned NOT NULL AUTO_INCREMENT,`idUsuario` int(10) unsigned NOT NULL,`idTipoNoti` int(10) unsigned NOT NULL',
            'primaryKey' => 'idPermiso',
            'comentario' => 'Creado desde el Instalador',
        ];

        /************************************/
        // Retorno True por defecto
        return $arrTables;

    }

    /*******************************************************************/
    // Mapeo de las tablas
    /*******************************************************************/
    public function mapTables(){

        $Data = '
        usuarios_listado(idUsuario,idCiudad,idComuna,idTipoUsuario,Nombre)
        core_tipos_usuario(idTipoUsuario,Nombre)
        entidades_listado(idEntidad,idSector,idSexo,idTipoEntidad,Nombre,ApellidoPat,ApellidoMat,RazonSocial,Nick,Rut,idCiudad,idComuna,Direccion,FNacimiento,Email,Fono1,Fono2,Web,Giro,RepLegalNombre,RepLegalRut,RepLegalEmail,RepLegalFono1,RepLegalFono2)
        core_tipos_entidades(idTipoEntidad,Nombre)
        entidades_sectores(idSector,Nombre)
        servicios_listado(idServicio,Nombre)
        maquinas_listado(idMaquina,Nombre)
        core_telemetria_tipo_noti(idTipoNoti,Nombre)
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
