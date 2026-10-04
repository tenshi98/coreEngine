<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class ControllerInstaller {
    /*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                 Instancias                                                      */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	//Definiciones
    private $DBConn;
    private $queryBuilder;
    private $checkData;

	/************************************************************************************************************/
	//Instancias
    public function __construct($DBConn, $queryBuilder, $checkData){
        $this->DBConn        = $DBConn;
        $this->queryBuilder  = $queryBuilder;
        $this->checkData     = $checkData;
    }

    /*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
    /**
     * Ejecuta una consulta para obtener un conjunto de múltiples registros de la base de datos.
     *
	 * @example
	 * ```php
	 * //Formato de la query
     * $query = [
     *        'data'    => 'idComuna AS ID1, idCiudad AS ID2, Nombre',
     *        'table'   => 'core_ubicacion_comunas',
     *        'join'    => '',
     *        'where'   => '',
     *        'params'  => [],
     *        'group'   => '',
     *        'having'  => '',
     *        'order'   => 'Nombre ASC',
     *        'limit'   => ConfigAPP::APP["N_MaxItems"]
     *    ];
     *    // Ejecuto la query
     *    $xParams   = ['query' => $query];
     *    $arrComuna = $this->Base_GetList($xParams);
	 * ```
	 *
     */
    protected function Base_GetList(array &$params){

        /**********************     Valores     **********************/
        // Extraer parámetros con valores por defecto
        $query     = $params['query'] ?? '';
        $showQuery = $params['showQuery'] ?? false;
        $DBConn    = $params['newBDConn'] ?? $this->DBConn;

        /**********************  Retorno datos  **********************/
        //devuelvo resultados
        return $this->queryBuilder->queryArray($query, $DBConn, $showQuery);
    }

    /************************************************************************************************************/
    /**
     * Calcula el número total de coincidencias (filas) que devuelve una consulta específica.
     *
	 * @example
	 * ```php
	 *  // === Se genera la query ===
     *  $query = [
     *  'data'    => 'idBodegas',
     *  'table'   => 'bodegas_listado',
     *  'join'    => '',
     *  'where'   => 'idBodegas = ?',
     *  'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
     *  'group'   => '',
     *  'having'  => '',
     *  'order'   => ''
     *  ];
     *  // Ejecuto la query
     *  $xParams = ['query' => $query];
     *  $rowData = $this->Base_GetCountData($xParams);
	 * ```
	 *
     */
    protected function Base_GetCountData(array &$params){

        /**********************     Valores     **********************/
        // Extraer parámetros con valores por defecto
        $query     = $params['query'] ?? '';
        $showQuery = $params['showQuery'] ?? false;
        $DBConn    = $params['newBDConn'] ?? $this->DBConn;

        /**********************  Retorno datos  **********************/
        //devuelvo resultados
        return $this->queryBuilder->queryNRows($query, $DBConn, $showQuery);
    }

    /************************************************************************************************************/
    /**
     * Inserta un nuevo registro en la base de datos con soporte para validaciones y subida de archivos.
     *
	 * @example
	 * ```php
	 *  // === Se genera la query ===
     *  $query = [
     *      'data'      => 'idEstado,Nombre,idCiudad,idComuna,Direccion',
     *      'required'  => 'idEstado,Nombre',
     *      'unique'    => 'Nombre',
     *      'encode'    => '',
     *      'table'     => 'bodegas_listado',
     *      'Post'      => $_POST
     *  ];
     *  // Ejecuto la query
     *  $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
     *  $Response = $this->Base_insert($xParams);
	 * ```
	 *
     */
    protected function Base_insert(array &$params){

        /**********************     Valores     **********************/
        // Extraer parámetros con valores por defecto
        $DataCheck  = (isset($params['DataCheck'])&&$params['DataCheck']!='') ? $params['DataCheck'] : [];
        $query      = $params['query'] ?? '';
        $showQuery  = $params['showQuery'] ?? false;
        $novalidate = $params['novalidate'] ?? false;
        $DBConn     = $params['newBDConn'] ?? $this->DBConn;

        /********************** Si todo esta ok **********************/
        // Ejecuto el chequeo
        $checkData = $this->checkData->checkingData($DataCheck);
        if ($checkData['status'] === false) {
            return $checkData;
        }

        /**********************  Retorno datos  **********************/
        //devuelvo resultados
        return $this->queryBuilder->queryInsert($query, $DBConn, $showQuery, $novalidate);
    }

    /************************************************************************************************************/
    /**
     * Inicia una transacción explícita sobre la conexión de base de datos.
     *
     * Debe usarse cuando una operación involucra escrituras (Insert/Update/Delete) en más de
     * una tabla y todas deben aplicarse de forma atómica (todo o nada). Los Base_insert/
     * Base_update/Base_delete llamados después de este método, sin indicar 'newBDConn',
     * participan automáticamente de la misma transacción porque reutilizan $this->DBConn.
     *
     * @param array $params Puede incluir 'newBDConn' para operar sobre una conexión distinta a la por defecto.
     * @return array ['status' => bool, 'error' => string] (error solo presente si status es false)
     *
	 * @example
	 * ```php
	 * $this->Base_transactionBegin();
     *
     * $respReserva = $this->Base_insert($xParamsReserva);
     * if ($respReserva['status'] === false) { $this->Base_transactionRollback(); return $respReserva; }
     *
     * foreach ($recursos as $recurso) {
     *     $respRecurso = $this->Base_insert($xParamsRecurso);
     *     if ($respRecurso['status'] === false) { $this->Base_transactionRollback(); return $respRecurso; }
     * }
     *
     * $respHistorial = $this->Base_insert($xParamsHistorial);
     * if ($respHistorial['status'] === false) { $this->Base_transactionRollback(); return $respHistorial; }
     *
     * $this->Base_transactionCommit();
     * return ['status' => true, 'data' => $respReserva['data']];
	 * ```
	 *
     */
    protected function Base_transactionBegin(array $params = []){

        /**********************     Valores     **********************/
        $DBConn = $params['newBDConn'] ?? $this->DBConn;

        /**********************  Ejecutar  **********************/
        try {
            $DBConn->beginTransaction();
            return ['status' => true];
        } catch (Exception $e) {
            return ['status' => false, 'error' => 'No se pudo iniciar la transacción: '.$e->getMessage()];
        }
    }

    /************************************************************************************************************/
    /**
     * Confirma (COMMIT) una transacción previamente iniciada con Base_transactionBegin().
     *
     * @param array $params Puede incluir 'newBDConn' para operar sobre una conexión distinta a la por defecto.
     * @return array ['status' => bool, 'error' => string] (error solo presente si status es false)
     */
    protected function Base_transactionCommit(array $params = []){

        /**********************     Valores     **********************/
        $DBConn = $params['newBDConn'] ?? $this->DBConn;

        /**********************  Ejecutar  **********************/
        try {
            $DBConn->commit();
            return ['status' => true];
        } catch (Exception $e) {
            return ['status' => false, 'error' => 'No se pudo confirmar la transacción: '.$e->getMessage()];
        }
    }

    /************************************************************************************************************/
    /**
     * Revierte (ROLLBACK) una transacción previamente iniciada con Base_transactionBegin().
     *
     * Es seguro llamarla incluso si no hay una transacción activa: en ese caso no hace nada.
     *
     * @param array $params Puede incluir 'newBDConn' para operar sobre una conexión distinta a la por defecto.
     * @return array ['status' => bool, 'error' => string] (error solo presente si status es false)
     */
    protected function Base_transactionRollback(array $params = []){

        /**********************     Valores     **********************/
        $DBConn = $params['newBDConn'] ?? $this->DBConn;

        /**********************  Ejecutar  **********************/
        try {
            if ($DBConn->inTransaction()) {
                $DBConn->rollBack();
            }
            return ['status' => true];
        } catch (Exception $e) {
            return ['status' => false, 'error' => 'No se pudo revertir la transacción: '.$e->getMessage()];
        }
    }

    /************************************************************************************************************/
    /**
     * Si ya hay una transacción activa (ej. llamada desde Update() de partidas),
     * no abrimos una nueva ni hacemos commit/rollback propio: el llamador controla el ciclo..
     *
     * @param array $params Puede incluir 'newBDConn' para operar sobre una conexión distinta a la por defecto.
     * @return void true - false
     */
    protected function Base_inTransaction(array $params = []){

        /**********************     Valores     **********************/
        $DBConn = $params['newBDConn'] ?? $this->DBConn;

        /**********************  Ejecutar  **********************/
        return $DBConn->inTransaction();
    }

    /************************************************************************************************************/
    /**
     * Ejecuta una sentencia SQL directamente en la base de datos.
     *
     * @param string $params['query'] Sentencia SQL completa a ejecutar.
     * @param mixed $params['newBDConn'] Instancia de conexión a la base de datos (compatible con PDO).
     * @param bool $params['showQuery'] Si es true, retorna la cadena SQL sin ejecutarla.
     * @param bool $params['singleRow'] Si es true o false, indica si debe traer una fila o muchas.
     *
	 * @example
	 * ```php
	 *  //Formato de la query
     *  $query_1 = 'DELETE FROM `usuarios_listado_permisos` WHERE idPermisos = 1';
     *  $query_2 = 'DELETE FROM `core_permisos_listado` WHERE RutaController = 1';
     *  $query_3 = 'DELETE FROM `core_permisos_listado_rutas` WHERE Controller = 1';
	 * ```
	 *
     */
    protected function Base_queryExecute(array &$params){

        /**********************     Valores     **********************/
        // Extraer parámetros con valores por defecto
        $query     = $params['query'] ?? '';
        $DBConn    = $params['newBDConn'] ?? $this->DBConn;
        $showQuery = $params['showQuery'] ?? false;
        $singleRow = $params['singleRow'] ?? false;

        /**********************  Retorno datos  **********************/
        //devuelvo resultados
        return $this->queryBuilder->queryExecute($query, $DBConn, $showQuery, $singleRow);
    }

    /************************************************************************************************************/
    /**
     * Crea una nueva tabla en la base de datos utilizando el motor InnoDB.
     *
	 * @example
	 * ```php
	 *  //Se estructura la tabla
     *  $arrTables[] = [
     *      'table'      => 'bodegas_listado',
     *      'data'       => '`idBodegas` int(10) unsigned NOT NULL AUTO_INCREMENT,`idEstado` int(10) unsigned NOT NULL,`Nombre` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`idCiudad` int(10) unsigned NULL DEFAULT NULL,`idComuna` int(10) unsigned NULL DEFAULT NULL,`Direccion` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,`Direccion_img` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL',
     *      'primaryKey' => 'idBodegas',
     *      'comentario' => 'Creado desde el Instalador',
     *  ];
     *  $arrTables[] = [
     *      'table'      => 'bodegas_listado_observaciones',
     *      'data'       => '`idObservaciones` int(10) unsigned NOT NULL AUTO_INCREMENT,`idBodegas` int(10) unsigned NOT NULL,`Observacion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,`FechaCreacion` date NOT NULL',
     *      'primaryKey' => 'idObservaciones',
     *      'comentario' => 'Creado desde el Instalador',
     *  ];
     *  // Verifico si existe
     *  if($arrTables){
     *      // Recorro
     *      foreach ($arrTables as $table) {
     *          // Se genera la query
     *          $xParams  = ['query' => $table];
     *          $this->Base_createTable($xParams);
     *      }
     *  }
	 * ```
	 *
     */
    protected function Base_createTable(array &$params){

        /**********************     Valores     **********************/
        // Extraer parámetros con valores por defecto
        $query     = $params['query'] ?? '';
        $showQuery = $params['showQuery'] ?? false;
        $DBConn    = $params['newBDConn'] ?? $this->DBConn;

        /**********************  Retorno datos  **********************/
        //devuelvo resultados
        return $this->queryBuilder->queryCreateTable($query, $DBConn, $showQuery);
    }

    /************************************************************************************************************/
    /**
     * Elimina de forma permanente una tabla de la base de datos.
     *
	 * @example
	 * ```php
	 *  // Se listan las tablas
     *  $arrTableDel  = array();
     *  $arrTableDel[] = ['table' => 'bodegas_listado'];
     *  $arrTableDel[] = ['table' => 'bodegas_listado_observaciones'];
     *  $arrTableDel[] = ['table' => 'bodegas_movimientos'];
     *  $arrTableDel[] = ['table' => 'bodegas_movimientos_productos'];
     *  $arrTableDel[] = ['table' => 'bodegas_productos_stocks'];
     *
     *   // Verifico si existe
     *   if (!empty($arrTableDel)) {
     *      // Recorro
     *      foreach ($arrTableDel as $tblDel) {
     *          // Se ejecuta la query
     *          $xParams  = ['query' => $tblDel];
     *          $this->Base_dropTable($xParams);
     *      }
     *  }
	 * ```
	 *
     */
    protected function Base_dropTable(array &$params){

        /**********************     Valores     **********************/
        // Extraer parámetros con valores por defecto
        $query     = $params['query'] ?? '';
        $showQuery = $params['showQuery'] ?? false;
        $DBConn    = $params['newBDConn'] ?? $this->DBConn;

        /**********************  Retorno datos  **********************/
        //devuelvo resultados
        return $this->queryBuilder->queryDropTable($query, $DBConn, $showQuery);
    }

    /************************************************************************************************************/
    /**
     * Instala los permisos y las rutas asociadas a un módulo dentro de una
     * transacción de base de datos.
     *
     * El método recorre la lista de permisos recibida, valida e inserta cada
     * permiso en la tabla de permisos del sistema y, cuando existe el método
     * listRouteModule(), obtiene e inserta las rutas asociadas al permiso creado.
     *
     * Si alguna operación de inserción falla, se revierte la transacción y se
     * reporta el error mediante Response::error().
     *
     * El contador interno se utiliza como identificador del tipo de módulo
     * utilizado al obtener las rutas mediante listRouteModule().
     *
     * @param array $arrPermisos Lista de permisos que serán instalados. Cada
     *                           elemento contiene los datos requeridos para
     *                           insertar un permiso.
     *
     * @return void
     *
     * @throws No se declara una excepción explícita. Los errores de operación
     *         de base de datos son reportados mediante Response::error().
     */
    protected function Base_installModule(array $arrPermisos){

        /************************************/
        // Variable
        $IntCounter = 1;
        // Recorro los datos
        foreach ($arrPermisos as $permiso) {
            /************************************/
            // Se genera el chequeo
            $dataCheck_1 = $this->dataCheck_1($permiso);

            /************************************/
            // Se genera la query
            $query = [
                'data'      => 'idPermisosCat,idEstado,idTipo,Nombre,Descripcion,idLevelLimit,RutaWeb,RutaController',
                'required'  => 'idPermisosCat,idEstado,idTipo,Nombre,Descripcion,idLevelLimit,RutaWeb,RutaController',
                'unique'    => 'Nombre-RutaWeb-RutaController',
                'encode'    => '',
                'table'     => 'core_permisos_listado',
                'Post'      => $permiso
            ];
            // Preparo los datos
            $xParams    = ['DataCheck' => $dataCheck_1, 'query' => $query];
            // Ejecuto la query
            $permisosID = $this->Base_insert($xParams);
            /************************************/
            // Si falla la ejecucion, se revierte de inmediato
            if ($permisosID['status'] === false) {
                // Se revierte la transaccion
                $this->Base_transactionRollback();
                // Se reporta el error
                Response::error('Error al operar con la Base de Datos', 500, $permisosID['error']);
            }
            /************************************/
            // Listar las rutas
            if (method_exists($this, 'listRouteModule')) {
                $arrRutas = $this->listRouteModule($IntCounter, $permisosID['data']);
            } else {
                $arrRutas = [];
            }
            /************************************/
            // Verifico si existe
            if($arrRutas){
                // Recorro los datos
                foreach ($arrRutas as $rutas) {
                    /************************************/
                    // Se genera el chequeo
                    $dataCheck_2 = $this->dataCheck_2($rutas);

                    /************************************/
                    // Se genera la query
                    $query = [
                        'data'      => 'idPermisos,idMetodo,RutaWeb,RutaController,Descripcion,idLevelLimit,Controller',
                        'required'  => 'idPermisos,idMetodo,RutaWeb,RutaController,Descripcion,idLevelLimit,Controller',
                        'unique'    => 'RutaWeb-RutaController',
                        'encode'    => '',
                        'table'     => 'core_permisos_listado_rutas',
                        'Post'      => $rutas
                    ];
                    // Preparo los datos
                    $xParams  = ['DataCheck' => $dataCheck_2, 'query' => $query, 'novalidate' => true];
                    // Se ejecuta la query
                    $rutaID   = $this->Base_insert($xParams);
                    /************************************/
                    // Si falla la ejecucion, se revierte de inmediato
                    if ($rutaID['status'] === false) {
                        // Se revierte la transaccion
                        $this->Base_transactionRollback();
                        // Se reporta el error
                        Response::error('Error al operar con la Base de Datos', 500, $rutaID['error']);
                    }
                }
            }
            /************************************/
            // Se aumenta contador
            $IntCounter++;
        }
    }

    /************************************************************************************************************/
    /**
     * Desinstala los permisos asociados a una ruta de controlador y, cuando corresponde,
     * elimina las tablas indicadas dentro de una misma transacción de base de datos.
     *
     * El proceso realiza las siguientes operaciones:
     * - Valida que se haya proporcionado una ruta de controlador.
     * - Inicia una transacción.
     * - Consulta los permisos asociados a las rutas indicadas.
     * - Elimina las relaciones de dichos permisos con los usuarios.
     * - Elimina los permisos asociados a las rutas.
     * - Elimina las rutas de permisos asociadas a los controladores.
     * - Procesa las tablas recibidas para su eliminación.
     * - Revierte la transacción si alguna operación falla.
     * - Confirma la transacción cuando todas las operaciones finalizan correctamente.
     *
     * @param string $RutaController Lista de rutas de controlador utilizada en las
     *                               consultas de eliminación.
     * @param array  $listTables     Lista de tablas que serán procesadas para su
     *                               eliminación.
     *
     * @return bool Retorna true cuando el proceso finaliza correctamente y false
     *              cuando la validación inicial, una consulta o una operación de
     *              eliminación falla.
     */
    protected function Base_uninstallModule(string $RutaController, array $listTables = []){

        /************************************/
        // Verifico si hay datos
        if(empty($RutaController) || $RutaController == ''){
            return false;
        }

        /************************************/
        // Se inicia la transacción
        $this->Base_transactionBegin();

        /*******************************************************/
        /*             SE CONSULTAN LOS PERMISOS               */
        /*******************************************************/
        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idPermisos',
            'table'   => 'core_permisos_listado',
            'join'    => '',
            'where'   => 'RutaController IN ('.$RutaController.')',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'idPermisos ASC',
            'limit'   => ConfigAPP::APP["N_MaxItems"]
        ];
        // Preparo los datos
        $xParams     = ['query' => $query];
        // Ejecuto la query
        $arrPermisos = $this->Base_GetList($xParams);

        /************************************/
        // Si falla la ejecucion, se retorna false
        if (!isset($arrPermisos['status']) || !$arrPermisos['status'] || !isset($arrPermisos['data']) || empty($arrPermisos['data'])) {
            return false;
        }

        /*******************************************************/
        /*        SE ELIMINAN PERMISOS DE LOS USUARIOS         */
        /*******************************************************/
        // Se obtiene la lista de identificadores de permisos
        // para utilizarla en las relaciones asociadas a los usuarios.
        $subQuery = $arrPermisos['status']
                    ? ',' . implode(',', array_column($arrPermisos['data'], 'idPermisos'))
                    : '';

        /************************************/
        // Se arman las querys
        $arrPermDel   = array();
        $arrPermDel[] = 'DELETE FROM `usuarios_listado_permisos` WHERE idPermisos IN (0 '.$subQuery.')';
        $arrPermDel[] = 'DELETE FROM `core_permisos_listado` WHERE RutaController IN ('.$RutaController.')';
        $arrPermDel[] = 'DELETE FROM `core_permisos_listado_rutas` WHERE Controller IN ('.$RutaController.')';

        /************************************/
        // Verifico si existe
        if($arrPermDel){
            // Recorro los datos
            foreach ($arrPermDel as $sql) {
                /************************************/
                // Preparo los datos
                $xParams = ['query' => $sql];
                // Ejecuto la query
                $Response = $this->Base_queryExecute($xParams);

                /************************************/
                // Si falla la ejecucion, se revierte de inmediato
                if ($Response['status'] === false) {
                    $this->Base_transactionRollback();
                    return false;
                }
            }
        }

        /*******************************************************/
        /*              SE ELIMINAN LAS TABLAS                 */
        /*******************************************************/
        // Verifico si hay datos
        if(empty($listTables)){
            // Se crea variable
            $arrTableDel = array();
            // Recorro
            foreach ($listTables as $table) {
                $arrTableDel[] = ['table' => $table['table']];
            }

            /************************************/
            // Verifico si existe
            if (!empty($arrTableDel)) {
                // Recorro los datos
                foreach ($arrTableDel as $tblDel) {
                    /************************************/
                    // Preparo los datos
                    $xParams  = ['query' => $tblDel];
                    // Ejecuto la query
                    $Response = $this->Base_dropTable($xParams);

                    /************************************/
                    // Si falla la ejecucion, se revierte de inmediato
                    if ($Response['status'] === false) {
                        $this->Base_transactionRollback();
                        return false;
                    }
                }
            }
        }

        /************************************/
        // Confirmar transacción
        $this->Base_transactionCommit();

        /************************************/
        // Retorno True por defecto
        return true;

    }

    /************************************************************************************************************/
    /**
     * Obtiene la cantidad de rutas asociadas a uno o más controladores de un módulo.
     *
     * El método valida la ruta del controlador, construye una consulta sobre la tabla
     * de rutas de permisos y obtiene la cantidad de registros mediante el método
     * Base_GetCountData().
     *
     * @param string $RutaController Lista de controladores utilizada como criterio
     *                                de búsqueda en la consulta.
     *
     * @return int Cantidad de rutas encontradas para los controladores indicados.
     *             Retorna 0 cuando no se proporciona una ruta de controlador.
     */
    protected function Base_getCountDataModule(string $RutaController){

        /************************************/
        // Verifico si hay datos
        if(empty($RutaController) || $RutaController == ''){
            return 0;
        }

        /************************************/
        // Se genera la query
        $query = [
            'data'    => 'idRutas',
            'table'   => 'core_permisos_listado_rutas',
            'join'    => '',
            'where'   => 'Controller IN ('.$RutaController.')',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => ''
        ];
        // Preparo los datos
        $xParams = ['query' => $query];
        // Ejecuto la query
        $nData   = $this->Base_GetCountData($xParams);

        /************************************/
        // Retorno los datos
        return $nData['data'];

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
            'ValidarNumero'             => 'idPermisosCat,idEstado,idTipo,idLevelLimit',
            'ValidarEntero'             => 'idPermisosCat,idEstado,idTipo,idLevelLimit',
            'ValidarRut'                => '',
            'ValidarPatente'            => '',
            'ValidarFecha'              => '',
            'ValidarHora'               => '',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => 'Nombre,Descripcion',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'Nombre',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Nombre,Descripcion',
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

    /*******************************************************************/
    // Se validan los datos
    /*******************************************************************/
    private function dataCheck_2($POST){
        // Variables
        $DataChecking = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => '',
            'ValidarNumero'             => 'idPermisos,idMetodo,idLevelLimit',
            'ValidarEntero'             => 'idPermisos,idMetodo,idLevelLimit',
            'ValidarRut'                => '',
            'ValidarPatente'            => '',
            'ValidarFecha'              => '',
            'ValidarHora'               => '',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => 'Descripcion',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => '',
            'ValidarLargoMaximoN'       => 255,
            'ValidarPalabrasCensuradas' => 'Descripcion',
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
