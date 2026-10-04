<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class tercerosEntidadesListadoUsuariosMaq extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $Codification;

    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_1);
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
    /*******************************************************************/
    // Actualizar Listar
    /*******************************************************************/
    public function UpdateList($f3, $params){

        /************************************/
        // Se obtiene el ID
        $UsuarioID  = $this->Codification->encryptDecrypt('decrypt', $params['idUsuario']);
        $EntidadID  = $this->Codification->encryptDecrypt('decrypt', $params['idEntidad']);

        if (!$this->isValidDecrypted($UsuarioID, 'id')) {
            Response::error('Registro inválido', 400);
        }
        if (!$this->isValidDecrypted($EntidadID, 'id')) {
            Response::error('Registro inválido', 400);
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idUsuario,idEntidad,Nombre',
            'table'   => 'terceros_entidades_listado_usuarios',
            'join'    => '',
            'where'   => 'idUsuario = ?',
            'params'  => [$UsuarioID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $rowData = $this->Base_GetByID($xParams);

        /************************************/
        // Se genera la query
        $query = [
            'data'    => '
                terceros_entidades_listado_maquinas.idMaq,
                terceros_entidades_listado_maquinas.idMaq AS ID,
                maquinas_listado.Nombre AS Maquina,
                (SELECT COUNT(idPermiso) FROM terceros_entidades_listado_usuarios_maq WHERE idMaquina = ID AND idUsuario = '.$UsuarioID['data'].' LIMIT 1) AS IsActivo',
            'table'   => 'terceros_entidades_listado_maquinas',
            'join'    => 'LEFT JOIN maquinas_listado ON maquinas_listado.idMaquina = terceros_entidades_listado_maquinas.idMaquina',
            'where'   => 'terceros_entidades_listado_maquinas.idEntidad = ?',
            'params'  => [$EntidadID['data']],
            'group'   => '',
            'having'  => '',
            'order'   => 'maquinas_listado.Nombre ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams     = ['query' => $query];
        // Ejecuto la query
        $arrPermisos = $this->Base_GetList($xParams);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        // Si hay resultados
        if($rowData['status'] && $arrPermisos['status']){

            /************************************/
            // Datos enviados a la pagina
            $f3->data = [
                /*===========  Datos del usuario ===========*/
                'UserData'      => $this->getUserData($f3),
                'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
                /*===========   Funcionalidad   ===========*/
                'Fnc_FormInputs'      => $this->FormInputs,
                'Fnc_Codification'    => $this->Codification,
                /*=========== Datos Consultados ===========*/
                'rowData'     => $rowData['data'],
                'arrPermisos' => $arrPermisos['data'],
            ];

            /************************************/
            // Se instancia la vista
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Usuarios-Maquinas-formEdit.php');
        /************************************/
        // Si no hay resultados
        } else {
            // Busco errores de la consulta
            $result = $this->mergeResponses([$rowData,$arrPermisos]);
            // Despliegue de errores
            $this->showError(2, $f3, $result);
        }
    }

    /******************************************************************************/
    /*                                  DATOS                                     */
    /******************************************************************************/
    /*******************************************************************/
    // Editar por put (solo modificar datos)
    // Editar por post (modificar y subir archivos)
    /*******************************************************************/
    public function Update($f3){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Usuario creador
        $_POST['idUsuario'] = $f3->get('SESSION.DataInfo.UserID');

        /*******************************************************************/
        //Se traen los permisos
        $query = [
            'data'    => '
                idMaq,
                idMaq AS ID,
                (SELECT COUNT(idPermiso) FROM terceros_entidades_listado_usuarios_maq WHERE idMaquina = ID AND idUsuario = '.$_POST['idUsuario'].' LIMIT 1) AS IsActivo',
            'table'   => 'terceros_entidades_listado_maquinas',
            'join'    => '',
            'where'   => 'idEntidad = ?',
            'params'  => [$_POST['idEntidad']],
            'group'   => '',
            'having'  => '',
            'order'   => 'idMaq ASC',
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
                switch ($_POST['switch_'.$permisos['idMaq']]) {
                    /*******************************************************************/
                    // Inactivo
                    case 1:
                        // Se verifica si permiso existe
                        switch ($permisos['IsActivo']) {
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
                                $MaqID      = $this->Codification->encryptDecrypt('encrypt',$permisos['idMaq']);
                                // Se verifica
                                if (!$this->isValidDecrypted($UsuarioID, 'text')) {
                                    Response::error('Registro inválido', 400);
                                }
                                if (!$this->isValidDecrypted($MaqID, 'text')) {
                                    Response::error('Registro inválido', 400);
                                }
                                // Se borran los datos
                                $Post = [
                                    'idUsuario' => $UsuarioID['data'],
                                    'idMaquina' => $MaqID['data'],
                                ];
                                /************************************/
                                // Se genera la query
                                $query = [
                                    'files'       => '',
                                    'table'       => 'terceros_entidades_listado_usuarios_maq',
                                    'where'       => 'idUsuario,idMaquina',
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
                        switch ($permisos['IsActivo']) {
                            /*******************************************************************/
                            // Si no hay permisos se crea
                            case 0:
                                /************************************/
                                // Se acumula la fila para insertarla junto con el resto de recursos nuevos
                                $rowsPermisosNuevos[]    = [
                                    'idUsuario'  => $_POST['idUsuario'],
                                    'idMaquina'  => $permisos['idMaq'],
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
                $DataCheck = $this->dataCheck_1('');
                /************************************/
                // Se genera la query
                $query = [
                    'data'      => 'idUsuario,idMaquina',
                    'required'  => 'idUsuario,idMaquina',
                    'table'     => 'terceros_entidades_listado_usuarios_maq',
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
    private function dataCheck_1($POST){
        // Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => '',
            'ValidarNumero'             => 'idUsuario,idMaquina',
            'ValidarEntero'             => 'idUsuario,idMaquina',
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
