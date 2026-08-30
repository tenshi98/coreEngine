<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class usuariosListadoPermisos extends ControllerBase {

    /******************************************************************************/
    // Variables
    private $Codification;
    private $ServerServer;

    /******************************************************************************/
    //Constructor
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
    /******************************************************************************/
    //Editar por put (solo modificar datos)
    //Editar por post (modificar y subir archivos)
    public function Update(){
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            /*******************************************************************/
            //Se traen los permisos
            $query = [
                'data'    => '
                    idPermisos,
                    idPermisos AS ID,
                    (SELECT idLevelLimit FROM usuarios_listado_permisos WHERE idPermisos = ID AND idUsuario = '.$_POST['idUsuario'].' LIMIT 1) AS level',
                'table'   => 'core_permisos_listado',
                'join'    => '',
                'where'   => 'idEstado = ?',
                'params'  => [1],
                'group'   => '',
                'having'  => '',
                'order'   => 'idPermisos ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            // Ejecuto la query
            $xParams     = ['query' => $query];
            $arrPermisos = $this->Base_GetList($xParams);

            /*******************************************************************/
            // Si hay datos
            if ($arrPermisos['status']){
                // Se acumulan las filas a insertar para evitar un INSERT por cada recurso nuevo (N+1)
                $rowsPermisosNuevos = [];
                // Recorro los permisos
                foreach ($arrPermisos['data'] as $permisos){
                    // Se verifica si esta marcado
                    switch ($_POST['switch_'.$permisos['idPermisos']]) {
                        /*******************************************************************/
                        // Inactivo
                        case 1:
                            // Se verifica si permiso existe
                            switch ($permisos['level']) {
                                /*******************************************************************/
                                // No existe permiso previo
                                case 0:
                                    // Nada
                                    break;
                                /*******************************************************************/
                                // Si hay al menos un permiso
                                default:
                                    /******************************/
                                    // Se borran los datos
                                    $Post = [
                                        'idUsuario'  => $this->Codification->encryptDecrypt('encrypt',$_POST['idUsuario']),
                                        'idPermisos' => $this->Codification->encryptDecrypt('encrypt',$permisos['idPermisos']),
                                    ];
                                    /******************************/
                                    // Se genera la query
                                    $query = [
                                        'files'       => '',
                                        'table'       => 'usuarios_listado_permisos',
                                        'where'       => 'idUsuario,idPermisos',
                                        'SubCarpeta'  => '',
                                        'Post'        => $Post
                                    ];
                                    // Ejecuto la query
                                    $xParams = ['query' => $query];
                                    $this->Base_delete($xParams);
                                    break;
                            }
                            break;
                        /*******************************************************************/
                        // Activo
                        case 2:
                            // Verifico si existe
                            switch ($permisos['level']) {
                                /*******************************************************************/
                                // Si no hay permisos se crea
                                case 0:
                                    /******************************/
                                    // Se acumula la fila para insertarla junto con el resto de recursos nuevos
                                    $rowsPermisosNuevos[]    = [
                                        'idUsuario'     => $_POST['idUsuario'],
                                        'idPermisos'    => $permisos['idPermisos'],
                                        'idLevelLimit'  => $_POST['level_'.$permisos['idPermisos']],
                                        'fechaCreacion' => $this->ServerServer->fechaActual(),
                                    ];
                                    break;
                                /*******************************************************************/
                                //Si existe se actualiza
                                default:
                                    /******************************/
                                    // Se borran los datos
                                    $Post = [
                                        'idUsuario'     => $_POST['idUsuario'],
                                        'idPermisos'    => $permisos['idPermisos'],
                                        'idLevelLimit'  => $_POST['level_'.$permisos['idPermisos']],
                                        'fechaCreacion' => $this->ServerServer->fechaActual(),
                                    ];
                                    /******************************/
                                    // Se genera la query
                                    $query = [
                                        'data'      => 'idLevelLimit,fechaCreacion',
                                        'required'  => 'idLevelLimit',
                                        'unique'    => '',
                                        'encode'    => '',
                                        'table'     => 'usuarios_listado_permisos',
                                        'where'     => 'idUsuario,idPermisos',
                                        'Post'      => $Post,
                                    ];
                                    //Se genera el chequeo
                                    $dataCheck = $this->dataCheck($Post);
                                    // Ejecuto la query
                                    $xParams = ['DataCheck' => $dataCheck, 'query' => $query];
                                    $this->Base_update($xParams);
                                    break;
                            }
                            break;
                    }
                }

                /******************************/
                // Si hay recursos nuevos marcados, se insertan todos en una sola sentencia
                if ($rowsPermisosNuevos){
                    //Se genera el chequeo
                    $DataCheck = $this->dataCheck('');
                    // Se genera la query
                    $query = [
                        'data'      => 'idUsuario,idPermisos,idLevelLimit,fechaCreacion',
                        'required'  => 'idUsuario,idPermisos,idLevelLimit',
                        'table'     => 'usuarios_listado_permisos',
                        'rows'      => $rowsPermisosNuevos
                    ];
                    // Ejecuto la query
                    $xParams = ['DataCheck' => $DataCheck, 'query' => $query];
                    $this->Base_insertMultiple($xParams);
                }
            }

            /******************************/
            // Devuelvo true con código 200 (OK)
            Response::success(true);
        }else {
            // Request Method no esperado
            Response::error('Error en el Request Method', 500);
        }
    }


    /******************************************************************************/
    /*                             Métodos privados                               */
    /******************************************************************************/
    /******************************************************************************/
    //Se validan los datos
    private function dataCheck($POST){
        // Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => '',
            'ValidarNumero'             => 'idUsuario,idPermisos,idLevelLimit',
            'ValidarEntero'             => 'idUsuario,idPermisos,idLevelLimit',
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
        //Devuelvo
        return $DataChecking;
    }

}
