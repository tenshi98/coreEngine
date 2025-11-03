<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class tercerosEntidadesListadoUsuariosMaq extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;
    private $FormInputs;
    private $Codification;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_1);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'tercerosEntidadesListado';
		$this->FormInputs     = new UIFormInputs();
		$this->Codification   = new FunctionsSecurityCodification();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //List
    public function UpdateList($f3, $params){
        /*******************************************************************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');
        $arrLevel = $f3->get('SESSION.arrLevel');

        /******************************************/
        //Se genera la query
        $query = [
            'data'    => 'idUsuario,idEntidad,Nombre',
            'table'   => 'terceros_entidades_listado_usuarios',
            'join'    => '',
            'where'   => 'idUsuario = "'.$this->Codification->encryptDecrypt('decrypt', $params['idUsuario']).'"',
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        //Ejecuto la query
        $xParams = ['query' => $query];
        $rowData = $this->Base_GetByID($xParams);

        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => '
                terceros_entidades_listado_maquinas.idMaq,
                terceros_entidades_listado_maquinas.idMaq AS ID,
                maquinas_listado.Nombre AS Maquina,
                (SELECT COUNT(idPermiso) FROM terceros_entidades_listado_usuarios_maq WHERE idMaquina = ID AND idUsuario = '.$this->Codification->encryptDecrypt('decrypt', $params['idUsuario']).' LIMIT 1) AS IsActivo',
            'table'   => 'terceros_entidades_listado_maquinas',
            'join'    => 'LEFT JOIN maquinas_listado ON maquinas_listado.idMaquina = terceros_entidades_listado_maquinas.idMaquina',
            'where'   => 'terceros_entidades_listado_maquinas.idEntidad='.$this->Codification->encryptDecrypt('decrypt', $params['idEntidad']),
            'group'   => '',
            'having'  => '',
            'order'   => 'maquinas_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        //Ejecuto la query
        $xParams     = ['query' => $query];
        $arrPermisos = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Si hay resultados
        if(is_array($arrPermisos)){

            /******************************************/
            //Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $UserData,
                'UserAccess'    => $arrLevel[$this->controllerName],
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_Codification'    => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'rowData'     => $rowData,
                'arrPermisos' => $arrPermisos,
            ];

            /******************************************/
            //Se instancia la vista
            $this->showVista($UserData['TypeSession'], 2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Usuarios-Maquinas-formEdit.php');
        /*******************************************************************/
        //si no hay resultados
        } else {
            //Muestra los errores
            $this->showError($UserData['TypeSession'], 2, $f3);
        }
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
                    idMaq,
                    idMaq AS ID,
                    (SELECT COUNT(idPermiso) FROM terceros_entidades_listado_usuarios_maq WHERE idMaquina = ID AND idUsuario = '.$_POST['idUsuario'].' LIMIT 1) AS IsActivo',
                'table'   => 'terceros_entidades_listado_maquinas',
                'join'    => '',
                'where'   => 'idEntidad='.$_POST['idEntidad'],
                'group'   => '',
                'having'  => '',
                'order'   => 'idMaq ASC',
                'limit'   => ConfigAPP::APP["N_MaxItems"]
            ];
            //Ejecuto la query
            $xParams     = ['query' => $query];
            $arrPermisos = $this->Base_GetList($xParams);

            /*******************************************************************/
            //Recorro los permisos
            foreach ($arrPermisos AS $permisos){
                //Se verifica si esta marcado
                switch ($_POST['switch_'.$permisos['idMaq']]) {
                    /*******************************************************************/
                    //Inactivo
                    case 1:
                        //Se verifica si permiso existe
                        switch ($permisos['IsActivo']) {
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
                                    'idUsuario' => $this->Codification->encryptDecrypt('encrypt',$_POST['idUsuario']),
                                    'idMaquina' => $this->Codification->encryptDecrypt('encrypt',$permisos['idMaq']),
                                ];

                                /******************************/
                                //Se genera la query
                                $query = [
                                    'files'       => '',
                                    'table'       => 'terceros_entidades_listado_usuarios_maq',
                                    'where'       => 'idUsuario,idMaquina',
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
                        //Verifico si existe
                        switch ($permisos['IsActivo']) {
                            /*******************************************************************/
                            //Si no hay permisos se crea
                            case 0:
                                /******************************/
                                //Se borran los datos
                                $Post = [
                                    'idUsuario'   => $_POST['idUsuario'],
                                    'idMaquina'   => $permisos['idMaq'],
                                ];

                                /******************************/
                                //Se genera la query
                                $query = [
                                    'data'      => 'idUsuario,idMaquina',
                                    'required'  => 'idUsuario,idMaquina',
                                    'unique'    => '',
                                    'encode'    => '',
                                    'table'     => 'terceros_entidades_listado_usuarios_maq',
                                    'Post'      => $Post
                                ];
                                //Ejecuto la query
                                $xParams  = ['DataCheck' => '', 'query' => $query];
                                $this->Base_insert($xParams);
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


}
