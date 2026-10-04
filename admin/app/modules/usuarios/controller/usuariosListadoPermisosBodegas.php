<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class usuariosListadoPermisosBodegas extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $Codification;
    private $ServerServer;

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
		$this->Codification   = new FunctionsSecurityCodification();
		$this->ServerServer   = new FunctionsServerServer();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  DATOS                                     */
    /******************************************************************************/
    /*******************************************************************/
    // Editar por put (solo modificar datos)
    // Editar por post (modificar y subir archivos)
    /*******************************************************************/
    public function Update(){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /*******************************************************************/
        //Se traen los permisos
        $query = [
            'data'    => '
                idBodegas,
                idBodegas AS ID,
                (SELECT COUNT(idPermisoUsuario) FROM bodegas_listado_permisos_usuarios WHERE idBodegas = ID AND idUsuario = '.$_POST['idUsuario'].' LIMIT 1) AS cuentaPerms',
            'table'   => 'bodegas_listado',
            'join'    => '',
            'where'   => 'idEstado = ?',
            'params'  => [1],
            'group'   => '',
            'having'  => '',
            'order'   => 'idBodegas ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams     = ['query' => $query];
        // Ejecuto la query
        $arrPermisos = $this->Base_GetList($xParams);

        /*******************************************************************/
        // Si hay datos
        if ($arrPermisos['status']){
            // Se acumulan las filas a insertar para evitar un INSERT por cada permiso nuevo (N+1)
            $rowsPermisosNuevos = [];
            // Recorro los permisos
            foreach ($arrPermisos['data'] as $permisos){
                // Se verifica si esta marcado
                switch ($_POST['switch_'.$permisos['idBodegas']]) {
                    /*******************************************************************/
                    // Inactivo
                    case 1:
                        // Se verifica si permiso existe
                        switch ($permisos['cuentaPerms']) {
                            /*******************************************************************/
                            // No existe permiso previo
                            case 0:
                                // Nada
                                break;
                            /*******************************************************************/
                            // Si hay al menos un permiso
                            default:
                                /************************************/
                                // Se obtiene el ID
                                $UsuarioID  = $this->Codification->encryptDecrypt('encrypt',$_POST['idUsuario']);
                                $BodegasID  = $this->Codification->encryptDecrypt('encrypt',$permisos['idBodegas']);
                                // Se verifica
                                if (!$this->isValidDecrypted($UsuarioID, 'text')) {
                                    Response::error('Registro inválido', 400);
                                }
                                if (!$this->isValidDecrypted($BodegasID, 'text')) {
                                    Response::error('Registro inválido', 400);
                                }
                                // Se borran los datos
                                $Post = [
                                    'idUsuario'  => $UsuarioID['data'],
                                    'idBodegas'  => $BodegasID['data'],
                                ];
                                /************************************/
                                // Se genera la query
                                $query = [
                                    'files'       => '',
                                    'table'       => 'bodegas_listado_permisos_usuarios',
                                    'where'       => 'idUsuario,idBodegas',
                                    'SubCarpeta'  => '',
                                    'Post'        => $Post
                                ];
                                // Preparo los datos
                                $xParams = ['query' => $query];
                                // Ejecuto la query
                                $this->Base_delete($xParams);
                                break;
                        }
                        break;
                    /*******************************************************************/
                    // Activo
                    case 2:
                        // Verifico si existe
                        switch ($permisos['cuentaPerms']) {
                            /*******************************************************************/
                            // Si no hay permisos se crea
                            case 0:
                                /************************************/
                                // Se acumula la fila para insertarla junto con el resto de recursos nuevos
                                $rowsPermisosNuevos[]    = [
                                    'idUsuario'     => $_POST['idUsuario'],
                                    'idBodegas'     => $permisos['idBodegas'],
                                    'fechaCreacion' => $this->ServerServer->fechaActual(),
                                ];
                                break;
                        }
                        break;
                }
            }

            /************************************/
            // Si hay recursos nuevos marcados, se insertan todos en una sola sentencia
            if ($rowsPermisosNuevos){
                /************************************/
                // Se genera el chequeo
                $DataCheck = $this->dataCheck('');
                /************************************/
                // Se genera la query
                $query = [
                    'data'      => 'idUsuario,idBodegas,fechaCreacion',
                    'required'  => 'idUsuario,idBodegas',
                    'table'     => 'bodegas_listado_permisos_usuarios',
                    'rows'      => $rowsPermisosNuevos
                ];
                // Preparo los datos
                $xParams = ['DataCheck' => $DataCheck, 'query' => $query];
                // Ejecuto la query
                $this->Base_insertMultiple($xParams);
            }
        }

        /************************************/
        // Devuelvo true con código 200 (OK)
        Response::success(true);

    }


    /******************************************************************************/
    /*                             Métodos privados                               */
    /******************************************************************************/
    /*******************************************************************/
    // Se validan los datos
    /*******************************************************************/
    private function dataCheck($POST){
        // Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => '',
            'ValidarNumero'             => 'idUsuario,idBodegas',
            'ValidarEntero'             => 'idUsuario,idBodegas',
            'ValidarRut'                => '',
            'ValidarPatente'            => '',
            'ValidarFecha'              => '',
            'ValidarHora'               => '',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => '',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => '',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => '',
            'ValidarEspaciosVacios'     => '',
            'ValidarMayusculas'         => '',
            'ValidarCoincidencias'      => '',
            'ValidarDominioEmail'       => '',
            'ValidarPasswordSegura'     => '',
            'ValidarFechaRango'         => '',
            'ValidarEdadMinima'         => '',
            'ValidarJSON'               => '',
            'ValidarUUID'               => '',
            'ValidarIP'                 => '',
            'ValidarSoloAlfanumerico'   => '',
            'ValidarSoloLetras'         => '',
            'Post'                      => $POST,
        ];
        // Retorno los datos
        return $DataChecking;
    }

}
