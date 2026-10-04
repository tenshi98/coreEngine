<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class tercerosEntidadesListadoUsuariosNoti extends ControllerBase {

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
        if (!$this->isValidDecrypted($UsuarioID, 'id')) {
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
                idTipoNoti,
                idTipoNoti AS ID,
                Nombre AS Notificacion,
                (SELECT COUNT(idPermiso) FROM terceros_entidades_listado_usuarios_noti WHERE idTipoNoti = ID AND idUsuario = '.$UsuarioID['data'].' LIMIT 1) AS IsActivo',
            'table'   => 'core_telemetria_tipo_noti',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
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
            $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/'.$this->controllerName.'-Resumen-Usuarios-Notificaciones-formEdit.php');
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
                idTipoNoti,
                idTipoNoti AS ID,
                (SELECT COUNT(idPermiso) FROM terceros_entidades_listado_usuarios_noti WHERE idTipoNoti = ID AND idUsuario = '.$_POST['idUsuario'].' LIMIT 1) AS IsActivo',
            'table'   => 'core_telemetria_tipo_noti',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Nombre ASC',
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
                switch ($_POST['switch_'.$permisos['idTipoNoti']]) {
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
                                $TipoNotiID = $this->Codification->encryptDecrypt('encrypt',$permisos['idTipoNoti']);
                                // Se verifica
                                if (!$this->isValidDecrypted($UsuarioID, 'text')) {
                                    Response::error('Registro inválido', 400);
                                }
                                if (!$this->isValidDecrypted($TipoNotiID, 'text')) {
                                    Response::error('Registro inválido', 400);
                                }
                                // Se borran los datos
                                $Post = [
                                    'idUsuario'  => $UsuarioID['data'],
                                    'idTipoNoti' => $TipoNotiID['data'],
                                ];
                                /************************************/
                                // Se genera la query
                                $query = [
                                    'files'       => '',
                                    'table'       => 'terceros_entidades_listado_usuarios_noti',
                                    'where'       => 'idUsuario,idTipoNoti',
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
                                    'idUsuario'   => $_POST['idUsuario'],
                                    'idTipoNoti'  => $permisos['idTipoNoti'],
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
                    'data'      => 'idUsuario,idTipoNoti',
                    'required'  => 'idUsuario,idTipoNoti',
                    'table'     => 'terceros_entidades_listado_usuarios_noti',
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
            'ValidarNumero'             => 'idUsuario,idTipoNoti',
            'ValidarEntero'             => 'idUsuario,idTipoNoti',
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
