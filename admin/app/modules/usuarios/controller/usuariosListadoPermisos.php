<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class usuariosListadoPermisos extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $Codification;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
		$this->Codification = new FunctionsSecurityCodification();
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

            /******************************/
            //Se genera el chequeo
            $DataCheck = $this->dataCheck($_POST);

            /*******************************************************************/
            //Se traen los permisos
            $query = [
                'data'    => '
                    idPermisos,
                    idPermisos AS ID,
                    (SELECT idLevelLimit FROM usuarios_listado_permisos WHERE idPermisos = ID AND idUsuario = '.$_POST['idUsuario'].' LIMIT 1) AS level',
                'table'   => 'core_permisos_listado',
                'join'    => '',
                'where'   => 'idEstado=1',
                'group'   => '',
                'having'  => '',
                'order'   => 'idPermisos ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            //Ejecuto la query
            $xParams     = ['query' => $query];
            $arrPermisos = $this->Base_GetList($xParams);

            /*******************************************************************/
            //Recorro los permisos
            foreach ($arrPermisos AS $permisos){
                //Se verifica si esta marcado
                switch ($_POST['switch_'.$permisos['idPermisos']]) {
                    /*******************************************************************/
                    //Inactivo
                    case 1:
                        //Se verifica si permiso existe
                        switch ($permisos['level']) {
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
                                    'idPermisos' => $this->Codification->encryptDecrypt('encrypt',$permisos['idPermisos']),
                                ];

                                /******************************/
                                //Se genera la query
                                $query = [
                                    'files'       => '',
                                    'table'       => 'usuarios_listado_permisos',
                                    'where'       => 'idUsuario,idPermisos',
                                    'SubCarpeta'  => '',
                                    'Post'        => $Post
                                ];
                                //Ejecuto la query
                                $xParams  = ['query' => $query];
                                $Response = $this->Base_delete($xParams);

                                break;
                        }
                        break;
                    /*******************************************************************/
                    //Activo
                    case 2:
                        /******************************/
                        //Se borran los datos
                        $Post = [
                            'idUsuario'    => $_POST['idUsuario'],
                            'idPermisos'   => $permisos['idPermisos'],
                            'idLevelLimit' => $_POST['level_'.$permisos['idPermisos']],
                        ];

                        //Verifico si existe
                        switch ($permisos['level']) {
                            /*******************************************************************/
                            //Si no hay permisos se crea
                            case 0:
                                /******************************/
                                //Se genera la query
                                $query = [
                                    'data'      => 'idUsuario,idPermisos,idLevelLimit',
                                    'required'  => 'idUsuario,idPermisos,idLevelLimit',
                                    'unique'    => '',
                                    'encode'    => '',
                                    'table'     => 'usuarios_listado_permisos',
                                    'Post'      => $Post
                                ];
                                //Ejecuto la query
                                //Ejecuto la query
        $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
        $Response = $this->Base_insert($xParams);
                                break;

                            /*******************************************************************/
                            //Si existe se actualiza
                            default:
                                /******************************/
                                //Se genera la query
                                $query = [
                                    'data'      => 'idLevelLimit',
                                    'required'  => 'idLevelLimit',
                                    'unique'    => '',
                                    'encode'    => '',
                                    'table'     => 'usuarios_listado_permisos',
                                    'where'     => 'idUsuario,idPermisos',
                                    'Post'      => $Post,
                                ];
                                //Ejecuto la query
                                $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
                                $Response = $this->Base_update($xParams);
                                break;
                        }
                        break;
                }
            }

            /******************************/
            // Devuelvo true con código 200 (OK)
            echo Response::sendData(200, true);
        }else {
            // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
            echo Response::sendData(500, "Error en el Request Method");
        }
    }


    /******************************************************************************/
    //Se validan los datos
    private function dataCheck($POST){
        //Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => '',
            'ValidarNumero'             => '',
            'ValidarEntero'             => '',
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
            'Post'                      => $POST,
        ];
        //Devuelvo
        return $DataChecking;
    }

}