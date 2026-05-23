<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class usuariosListadoPermisosBodegas extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $Codification;
    private $ServerServer;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
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
                    idBodegas,
                    idBodegas AS ID,
                    (SELECT COUNT(idPermisoUsuario) FROM bodegas_listado_permisos_usuarios WHERE idBodegas = ID AND idUsuario = '.$_POST['idUsuario'].' LIMIT 1) AS cuentaPerms',
                'table'   => 'bodegas_listado',
                'join'    => '',
                'where'   => 'idEstado=1',
                'group'   => '',
                'having'  => '',
                'order'   => 'idBodegas ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            //Ejecuto la query
            $xParams     = ['query' => $query];
            $arrPermisos = $this->Base_GetList($xParams);

            /*******************************************************************/
            // Si hay datos
            if ($arrPermisos['status']){
                //Recorro los permisos
                foreach ($arrPermisos['data'] as $permisos){
                    //Se verifica si esta marcado
                    switch ($_POST['switch_'.$permisos['idBodegas']]) {
                        /*******************************************************************/
                        //Inactivo
                        case 1:
                            //Se verifica si permiso existe
                            switch ($permisos['cuentaPerms']) {
                                /*******************************************************************/
                                //No existe permiso previo
                                case 0:
                                    //nada
                                    break;
                                /*******************************************************************/
                                //Si hay al menos un permiso
                                default:
                                    /******************************/
                                    //Se borran los datos
                                    $Post = [
                                        'idUsuario'  => $this->Codification->encryptDecrypt('encrypt',$_POST['idUsuario']),
                                        'idBodegas'  => $this->Codification->encryptDecrypt('encrypt',$permisos['idBodegas']),
                                    ];

                                    /******************************/
                                    //Se genera la query
                                    $query = [
                                        'files'       => '',
                                        'table'       => 'bodegas_listado_permisos_usuarios',
                                        'where'       => 'idUsuario,idBodegas',
                                        'SubCarpeta'  => '',
                                        'Post'        => $Post
                                    ];
                                    //Ejecuto la query
                                    $xParams = ['query' => $query];
                                    $this->Base_delete($xParams);

                                    break;
                            }
                            break;
                        /*******************************************************************/
                        //Activo
                        case 2:
                            /******************************/
                            //Se borran los datos
                            $Post = [
                                'idUsuario'     => $_POST['idUsuario'],
                                'idBodegas'     => $permisos['idBodegas'],
                                'fechaCreacion' => $this->ServerServer->fechaActual(),
                            ];

                            //Verifico si existe
                            switch ($permisos['cuentaPerms']) {
                                /*******************************************************************/
                                //Si no hay permisos se crea
                                case 0:
                                    /******************************/
                                    //Se genera la query
                                    $query = [
                                        'data'      => 'idUsuario,idBodegas,fechaCreacion',
                                        'required'  => 'idUsuario,idBodegas',
                                        'unique'    => '',
                                        'encode'    => '',
                                        'table'     => 'bodegas_listado_permisos_usuarios',
                                        'Post'      => $Post
                                    ];
                                    //Se genera el chequeo
                                    $dataCheck = $this->dataCheck($Post);
                                    //Ejecuto la query
                                    $xParams = ['DataCheck' => $dataCheck, 'query' => $query];
                                    $this->Base_insert($xParams);
                                    break;

                            }
                            break;
                    }
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
        //Variables
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
        //Devuelvo
        return $DataChecking;
    }

}
