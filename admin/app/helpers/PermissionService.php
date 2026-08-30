<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Resuelve el menú, las rutas permitidas y los niveles de acceso de un
 * usuario según su tipo (super administrador vs usuario normal).
 */
class PermissionService {

    /******************************************************************************/
    // Variables
    private $DBConn;
    private $QBuilder;

    /******************************************************************************/
    /**
     * Inicializa la conexión a base de datos y el constructor de queries.
     *
     * @return void
     */
    public function __construct(){
        /*================== Instancias =================*/
        $this->DBConn     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $this->QBuilder   = new QueryBuilder();
    }

    /******************************************************************************/
    /**
     * Ejecuta la query de $superAdminQuery o $normalQuery según corresponda
     * al tipo de usuario recibido.
     *
     * @param int   $TipoUsuarioID    Tipo de usuario (1 = super administrador).
     * @param array $superAdminQuery Definición de query (formato QueryBuilder) para super administrador.
     * @param array $normalQuery     Definición de query (formato QueryBuilder) para usuario normal.
     *
     * @return array Resultado de QueryBuilder::queryArray().
     */
    private function queryByUserType($TipoUsuarioID, array $superAdminQuery, array $normalQuery){
        //Super administrador ve el listado completo, el resto ve solo lo asignado
        $query = ($TipoUsuarioID == 1) ? $superAdminQuery : $normalQuery;
        // Retorno de datos
        return $this->QBuilder->queryArray($query, $this->DBConn);
    }

    /******************************************************************************/
    /**
     * Obtiene el menú de opciones habilitadas para el usuario: el super
     * administrador ve el listado completo de core_permisos_listado,
     * el resto solo lo asignado en usuarios_listado_permisos.
     *
     * @param int $TipoUsuarioID Tipo de usuario (1 = super administrador).
     * @param int $UsuarioID     Id del usuario (usado solo para usuarios normales).
     *
     * @return array{status:bool, data:array} Filas del menú con categoría, icono, nombre, ruta y nivel.
     */
    public function getMenu($TipoUsuarioID, $UsuarioID){
        // Retorno de datos
        return $this->queryByUserType(
            $TipoUsuarioID,
            [
                'data'    => '
                    core_permisos_categorias.Nombre AS PermisosCat,
                    core_permisos_categorias.Icon AS PermisosIcon,
                    core_iconos_colores.Nombre AS PermisosIconColor,
                    core_permisos_listado.Nombre,
                    core_permisos_listado.RutaWeb,
                    core_permisos_listado.idLevelLimit AS PermisosLevel,
                    core_permisos_listado.RutaController AS PermisosController',
                'table'   => 'core_permisos_listado',
                'join'    => '
                    LEFT JOIN core_permisos_categorias ON core_permisos_categorias.idPermisosCat = core_permisos_listado.idPermisosCat
                    LEFT JOIN core_iconos_colores      ON core_iconos_colores.idColor            = core_permisos_categorias.IdIconColor',
                'where'   => 'core_permisos_listado.idEstado = ?',
                'params'  => [1],
                'group'   => '',
                'having'  => '',
                'order'   => 'core_permisos_categorias.Nombre ASC, core_permisos_listado.Nombre ASC, core_permisos_listado.RutaWeb ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ],
            [
                'data'    => '
                    core_permisos_categorias.Nombre AS PermisosCat,
                    core_permisos_categorias.Icon AS PermisosIcon,
                    core_iconos_colores.Nombre AS PermisosIconColor,
                    core_permisos_listado.Nombre,
                    core_permisos_listado.RutaWeb,
                    usuarios_listado_permisos.idLevelLimit AS PermisosLevel,
                    core_permisos_listado.RutaController AS PermisosController',
                'table'   => 'usuarios_listado_permisos',
                'join'    => '
                    LEFT JOIN core_permisos_listado    ON core_permisos_listado.idPermisos       = usuarios_listado_permisos.idPermisos
                    LEFT JOIN core_permisos_categorias ON core_permisos_categorias.idPermisosCat = core_permisos_listado.idPermisosCat
                    LEFT JOIN core_iconos_colores      ON core_iconos_colores.idColor            = core_permisos_categorias.IdIconColor',
                'where'   => 'usuarios_listado_permisos.idUsuario = ? AND core_permisos_listado.idEstado = ?',
                'params'  => [$UsuarioID, 1],
                'group'   => '',
                'having'  => '',
                'order'   => 'core_permisos_categorias.Nombre ASC, core_permisos_listado.Nombre ASC, core_permisos_listado.RutaWeb ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ]
        );
    }

    /******************************************************************************/
    /**
     * Obtiene las rutas (RutaWeb/RutaController) y métodos HTTP permitidos
     * para el usuario: el super administrador ve todas las rutas de
     * core_permisos_listado_rutas, el resto solo las de su nivel de acceso
     * asignado en usuarios_listado_permisos.
     *
     * @param int $TipoUsuarioID Tipo de usuario (1 = super administrador).
     * @param int $UsuarioID     Id del usuario (usado solo para usuarios normales).
     *
     * @return array{status:bool, data:array} Filas con Metodo, RutaWeb y RutaController.
     */
    public function getRoutes($TipoUsuarioID, $UsuarioID){
        // Retorno de datos
        return $this->queryByUserType(
            $TipoUsuarioID,
            [
                'data'    => '
                    core_permisos_listado_rutas_metodo.Nombre AS Metodo,
                    core_permisos_listado_rutas.RutaWeb,
                    core_permisos_listado_rutas.RutaController',
                'table'   => 'core_permisos_listado',
                'join'    => '
                    LEFT JOIN core_permisos_listado_rutas        ON core_permisos_listado_rutas.idPermisos      = core_permisos_listado.idPermisos
                    LEFT JOIN core_permisos_listado_rutas_metodo ON core_permisos_listado_rutas_metodo.idMetodo = core_permisos_listado_rutas.idMetodo',
                'where'   => 'core_permisos_listado.idEstado = ?',
                'params'  => [1],
                'group'   => '',
                'having'  => '',
                'order'   => 'core_permisos_listado_rutas_metodo.Nombre ASC, core_permisos_listado_rutas.RutaWeb ASC, core_permisos_listado_rutas.RutaController ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ],
            [
                'data'    => '
                    core_permisos_listado_rutas_metodo.Nombre AS Metodo,
                    core_permisos_listado_rutas.RutaWeb,
                    core_permisos_listado_rutas.RutaController',
                'table'   => 'usuarios_listado_permisos',
                'join'    => '
                    LEFT JOIN core_permisos_listado              ON core_permisos_listado.idPermisos            = usuarios_listado_permisos.idPermisos
                    LEFT JOIN core_permisos_listado_rutas        ON core_permisos_listado_rutas.idPermisos      = core_permisos_listado.idPermisos AND core_permisos_listado_rutas.idLevelLimit <= usuarios_listado_permisos.idLevelLimit
                    LEFT JOIN core_permisos_listado_rutas_metodo ON core_permisos_listado_rutas_metodo.idMetodo = core_permisos_listado_rutas.idMetodo',
                'where'   => 'usuarios_listado_permisos.idUsuario = ? AND core_permisos_listado.idEstado = ?',
                'params'  => [$UsuarioID, 1],
                'group'   => '',
                'having'  => '',
                'order'   => 'core_permisos_listado_rutas_metodo.Nombre ASC, core_permisos_listado_rutas.RutaWeb ASC, core_permisos_listado_rutas.RutaController ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ]
        );
    }

    /******************************************************************************/
    /**
     * Calcula, a partir del menú del usuario, un mapeo de nivel de acceso y
     * ruta por controlador (indexado por RutaController). Para el super
     * administrador agrega además rutas de prueba fijas con nivel máximo.
     *
     * @param int   $TipoUsuarioID Tipo de usuario (1 = super administrador).
     * @param array $arrMenu       Resultado de getMenu() (status/data).
     *
     * @return array Mapeo [RutaController => ['LevelAccess' => int, 'RouteAccess' => string]].
     */
    public function getLevels($TipoUsuarioID, $arrMenu){

        /******************************/
        // Se crea variable para los niveles de permisos
        $arrLevel = [];
        // Si hay datos
        if ($arrMenu['status']){
            // Se recorren las variables
            foreach ($arrMenu['data'] as $value) {
                // Se crea la variable
                $arrLevel[$value['PermisosController']]['LevelAccess']  = $value['PermisosLevel'];
                $arrLevel[$value['PermisosController']]['RouteAccess']  = $value['RutaWeb'];
            }
        }

        // Solo si es super usuario
        if($TipoUsuarioID==1){
            //Permisos rutas de prueba
            $arrLevel['crudNormal']['LevelAccess']   = 4;
            $arrLevel['crudResumen']['LevelAccess']  = 4;
            $arrLevel['crudInforme']['LevelAccess']  = 4;
            $arrLevel['Empty']['LevelAccess']        = 4;
            $arrLevel['crudNormal']['RouteAccess']   = 'Core/pruebas/crudNormal';
            $arrLevel['crudResumen']['RouteAccess']  = 'Core/pruebas/crudResumen';
            $arrLevel['crudInforme']['RouteAccess']  = 'Core/pruebas/crudInforme';
            $arrLevel['Empty']['RouteAccess']        = '';
        }

        // Retorno de datos
        return $arrLevel;

    }

}
