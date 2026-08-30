<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class ControllerBase {
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
	private $CommonData;
	private $UserData;

	/************************************************************************************************************/
	//Instancias
    public function __construct($DBConn, $queryBuilder, $checkData){
        $this->DBConn        = $DBConn;
        $this->queryBuilder  = $queryBuilder;
        $this->checkData     = $checkData;
		$this->CommonData    = new FunctionsCommonData();
    }

    /*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/

    /************************************************************************************************************/
    /**
     * Obtiene los datos del usuario almacenados en sesión.
     *
     * Este método implementa un patrón de carga perezosa (lazy loading),
     * inicializando la propiedad `$this->UserData` únicamente si aún no ha sido definida
     * y si se proporciona una instancia válida de `$f3` (framework Fat-Free).
     *
     * Los datos se obtienen desde la clave de sesión `SESSION.DataInfo`.
     * En caso de no existir información en sesión, se asigna un arreglo vacío.
     *
     * @param \Base|null $f3 Instancia del framework Fat-Free (opcional).
     *
     * @return array Retorna un arreglo con los datos del usuario. Si no existen datos,
     *               devuelve un arreglo vacío.
     *
     * @example
     * $userData = $this->getUserData($f3);
     * echo $userData['username'] ?? 'Invitado';
     */
    protected function getUserData($f3 = null): array {

        // Si ya está cargado en memoria, retornarlo
        if (is_array($this->UserData)) {
            return $this->UserData;
        }

        // Si no hay instancia de F3, fallback seguro
        if (!$f3) {
            return $this->UserData = [];
        }

        // Obtener desde sesión con validación estricta
        $data = $f3->get('SESSION.DataInfo');

        /**********************  Retorno datos  **********************/
        //devuelvo resultados
        return $this->UserData = is_array($data) ? $data : [];

    }

    /************************************************************************************************************/
    /**
     * Obtiene la configuración de niveles/permisos asociada a un controlador específico.
     *
     * Este método accede a la variable de sesión `SESSION.arrLevel`, la cual
     * contiene un arreglo indexado por nombre de controlador con sus respectivos niveles
     * o permisos.
     *
     * @param \Base|null $f3 Instancia del framework Fat-Free.
     * @param string|null $controllerName Nombre del controlador del cual se desean obtener los niveles.
     *
     * @return array Retorna un arreglo con los niveles/permisos del controlador indicado.
     *               Si no existe la clave o los datos en sesión, retorna un arreglo vacío.
     *
     * @note
     * - No implementa cache interno como `getUserData`, por lo que accede directamente a sesión en cada llamada.
     * - Se recomienda validar que `$controllerName` no sea null para evitar accesos innecesarios.
     *
     * @example
     * $levels = $this->getArrLevel($f3, 'UserController');
     * if (in_array('admin', $levels)) {
     *     // lógica para administrador
     * }
     */
    protected function getArrLevel($f3 = null, $controllerName = null): array {

        // Validación temprana (fail-fast)
        if (!$f3 || !$controllerName) {
            return [];
        }

        $arrLevel = $f3->get('SESSION.arrLevel');

        // Validar estructura
        if (!is_array($arrLevel)) {
            return [];
        }

        $levels = $arrLevel[$controllerName] ?? [];

        /**********************  Retorno datos  **********************/
        //devuelvo resultados
        return is_array($levels) ? $levels : [];

    }

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
     * Ejecuta una consulta para obtener una única fila de la base de datos.
     *
	 * @example
	 * ```php
	 *  // Se genera la query
     *  $query = [
     *  'data'    => '
     *      bodegas_listado.Nombre,
     *      bodegas_listado.Direccion,
     *      bodegas_listado.Direccion_img,
     *      core_estados.Nombre AS Estado,
     *      core_estados.Color AS EstadoColor,
     *      core_ubicacion_ciudad.Nombre AS Ciudad,
     *      core_ubicacion_comunas.Nombre AS Comuna',
     *  'table'   => 'bodegas_listado',
     *  'join'    => '
     *      LEFT JOIN core_estados             ON core_estados.idEstado               = bodegas_listado.idEstado
     *      LEFT JOIN core_ubicacion_ciudad    ON core_ubicacion_ciudad.idCiudad      = bodegas_listado.idCiudad
     *      LEFT JOIN core_ubicacion_comunas   ON core_ubicacion_comunas.idComuna     = bodegas_listado.idComuna',
     *  'where'   => 'bodegas_listado.idBodegas = ?',
     *  'params'  => [$this->Codification->encryptDecrypt('decrypt', $params['id'])],
     *  'group'   => '',
     *  'having'  => '',
     *  'order'   => ''
     *  ];
     *  // Ejecuto la query
     *  $xParams = ['query' => $query];
     *  $rowData = $this->Base_GetByID($xParams);
	 * ```
	 *
     */
    protected function Base_GetByID(array &$params){

        /**********************     Valores     **********************/
        // Extraer parámetros con valores por defecto
        $query     = $params['query'] ?? '';
        $showQuery = $params['showQuery'] ?? false;
        $DBConn    = $params['newBDConn'] ?? $this->DBConn;

        /**********************  Retorno datos  **********************/
        //devuelvo resultados
        return $this->queryBuilder->queryRow($query, $DBConn, $showQuery);
    }

    /************************************************************************************************************/
    /**
     * Calcula el número total de coincidencias (filas) que devuelve una consulta específica.
     *
	 * @example
	 * ```php
	 *  // Se genera la query
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
	 *  // Se genera la query
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
     * Inserta múltiples filas en una sola sentencia INSERT, evitando el patrón N+1 de llamar
     * Base_insert() dentro de un foreach (una consulta por elemento).
     *
	 * @example
	 * ```php
	 *  // Se genera la query
     *  $query = [
     *      'data'      => 'idReserva,idRecurso',
     *      'required'  => 'idReserva,idRecurso',
     *      'table'     => 'reservas_listado_recursos',
     *      'rows'      => [
     *          ['idReserva' => 10, 'idRecurso' => 3],
     *          ['idReserva' => 10, 'idRecurso' => 7],
     *      ],
     *  ];
     *  // Ejecuto la query
     *  $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
     *  $Response = $this->Base_insertMultiple($xParams);
	 * ```
	 *
     */
    protected function Base_insertMultiple(array &$params){

        /**********************     Valores     **********************/
        // Extraer parámetros con valores por defecto
        $DataCheck = (isset($params['DataCheck']) && $params['DataCheck'] != '') ? $params['DataCheck'] : [];
        $query     = $params['query'] ?? '';
        $showQuery = $params['showQuery'] ?? false;
        $DBConn    = $params['newBDConn'] ?? $this->DBConn;

        /********************** Validación fila por fila **********************/
        if (!empty($DataCheck)) {
            foreach ($query['rows'] as $row) {
                // Se entregan los datos a revisar
                $rowCheck         = $DataCheck;
                $rowCheck['Post'] = $row;
                // Ejecuto el chequeo
                $checkData = $this->checkData->checkingData($rowCheck);
                if ($checkData['status'] === false) {
                    return $checkData;
                }
            }
        }

        /**********************  Retorno datos  **********************/
        //devuelvo resultados
        return $this->queryBuilder->queryInsertMultiple($query, $DBConn, $showQuery);
    }

    /************************************************************************************************************/
    /**
     * Actualiza uno o más registros existentes en la base de datos con soporte para validaciones y archivos.
     *
	 * @example
	 * ```php
	 *  // Se genera la query
     *  $query = [
     *      'data'      => 'idBodegas,idEstado,Nombre,idCiudad,idComuna,Direccion',
     *      'required'  => 'idEstado,Nombre',
     *      'unique'    => 'Nombre',
     *      'encode'    => '',
     *      'table'     => 'bodegas_listado',
     *      'where'     => 'idBodegas',
     *      'Post'      => $_POST,
     *      'files'     => [
     *          [
     *              'Identificador' => 'Direccion_img',
     *              'SubCarpeta'    => '',
     *              'NombreArchivo' => '',
     *              'SufijoArchivo' => 'BodegasIMG_',
     *              'ValidarTipo'   => 'image',
     *              'ValidarPeso'   => 10,
     *              'Base64'        => true
     *          ],
     *      ]
     *  ];
     *  // Ejecuto la query
     *  $xParams  = ['DataCheck' => $DataCheck, 'query' => $query];
     *  $Response = $this->Base_update($xParams);
	 * ```
	 *
     */
    protected function Base_update(array &$params){

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
        return $this->queryBuilder->queryUpdate($query, $DBConn, $showQuery, $novalidate);
    }

    /************************************************************************************************************/
    /**
     * Elimina un registro de la base de datos y sus archivos físicos asociados.
     *
	 * @example
	 * ```php
	 *  // Se genera la query
     *  $query = [
     *      'files'       => 'Direccion_img',
     *      'table'       => 'bodegas_listado',
     *      'where'       => 'idBodegas',
     *      'SubCarpeta'  => '',
     *      'Post'        => $dataDelete
     *  ];
     *  // Ejecuto la query
     *  $xParams  = ['query' => $query];
     *  $Response = $this->Base_delete($xParams);
	 * ```
	 *
     */
    protected function Base_delete(array &$params){

        /**********************     Valores     **********************/
        // Extraer parámetros con valores por defecto
        $query     = $params['query'] ?? '';
        $showQuery = $params['showQuery'] ?? false;
        $DBConn    = $params['newBDConn'] ?? $this->DBConn;

        /**********************  Retorno datos  **********************/
        //devuelvo resultados
        return $this->queryBuilder->queryDelete($query, $DBConn, $showQuery);
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
     * if (!$respReserva['status']) { $this->Base_transactionRollback(); return $respReserva; }
     *
     * foreach ($recursos as $recurso) {
     *     $respRecurso = $this->Base_insert($xParamsRecurso);
     *     if (!$respRecurso['status']) { $this->Base_transactionRollback(); return $respRecurso; }
     * }
     *
     * $respHistorial = $this->Base_insert($xParamsHistorial);
     * if (!$respHistorial['status']) { $this->Base_transactionRollback(); return $respHistorial; }
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
     * Elimina archivos físicos y limpia sus referencias en el registro de la base de datos.
     *
	 * @example
	 * ```php
	 *  // Se genera la query
     *  $query = [
     *      'files'       => 'Direccion_img',
     *      'table'       => 'bodegas_listado',
     *      'where'       => 'idBodegas',
     *      'SubCarpeta'  => '',
     *      'Post'        => $dataPut
     *  ];
     *  // Ejecuto la query
     *  $xParams  = ['query' => $query];
     *  $Response = $this->Base_delFiles($xParams);
	 * ```
	 *
     */
    protected function Base_delFiles(array &$params){

        /**********************     Valores     **********************/
        // Extraer parámetros con valores por defecto
        $query     = $params['query'] ?? '';
        $DBConn    = $params['newBDConn'] ?? $this->DBConn;

        /**********************  Retorno datos  **********************/
        //devuelvo resultados
        return $this->queryBuilder->delFiles($query, $DBConn);
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
     *      //recorro
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
	 *  //Se listan las tablas
     *  $arrTableDel  = array();
     *  $arrTableDel[] = ['table' => 'bodegas_listado'];
     *  $arrTableDel[] = ['table' => 'bodegas_listado_observaciones'];
     *  $arrTableDel[] = ['table' => 'bodegas_movimientos'];
     *  $arrTableDel[] = ['table' => 'bodegas_movimientos_productos'];
     *  $arrTableDel[] = ['table' => 'bodegas_productos_stocks'];
     *
     *   // Verifico si existe
     *   if($arrTableDel){
     *      //recorro
     *      foreach ($arrTableDel as $tblDel) {
     *          //Se ejecuta la query
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
     * Crea una nueva base de datos en el servidor de base de datos especificado.
     *
	 * @example
	 * ```php
	 *  //Se generan los datos de conexión
     *  $query = [
     *      'dbName' => $DBName,
     *  ];
     *  $newBDConn = [
     *      'HOSTNAME' => $Host,
     *      'USERNAME' => $Admin_Usuario,
     *      'PASSWORD' => $Admin_Password,
     *      'PORT'     => $Port,
     *      'CHARSET'  => $Charset,
     *  ];
     *  //Se genera el array con datos
     *  $xParams  = ['query' => $query, 'newBDConn' => $newBDConn];
     *  // Ejecuto la query
     *  $CreateDB = $this->Base_createDatabase($xParams);
	 * ```
	 *
     */
    protected function Base_createDatabase(array &$params){

        /**********************     Valores     **********************/
        // Extraer parámetros con valores por defecto
        $query     = $params['query'] ?? '';
        $showQuery = $params['showQuery'] ?? false;
        $DBConn    = $params['newBDConn'] ?? $this->DBConn;

        /**********************  Retorno datos  **********************/
        //devuelvo resultados
        return $this->queryBuilder->createDatabase($query, $DBConn, $showQuery);
    }

    /************************************************************************************************************/
    /**
     * Procesa y ejecuta el contenido de un archivo SQL externo en la base de datos.
     *
	 * @example
	 * ```php
	 *  //Se generan los datos de conexión
     *  $BD_Data = [
     *      'HOSTNAME' => $Host,
     *      'USERNAME' => $Admin_Usuario,
     *      'PASSWORD' => $Admin_Password,
     *      'PORT'     => $Port,
     *      'CHARSET'  => $Charset,
     *      'DATABASE' => $DBName,
     *  ];
     *  //Se genera conexion a la base de datos utilizando la conexion normal
     *  $newBDConn = Database::getSQLConnection($BD_Data);
     *  //Se genera el array con datos
     *  $xParams  = ['filepath' => $filepath, 'newBDConn' => $newBDConn];
     *  // Ejecuto la query
     *  $ExecuteFileSQL = $this->Base_executeFile($xParams);
	 * ```
	 *
     */
    protected function Base_executeFile(array &$params){

        /**********************     Valores     **********************/
        // Extraer parámetros con valores por defecto
        $filepath  = $params['filepath'] ?? '';
        $DBConn    = $params['newBDConn'] ?? $this->DBConn;

        /**********************  Retorno datos  **********************/
        //devuelvo resultados
        return $this->queryBuilder->executeFile($filepath, $DBConn);
    }

    /************************************************************************************************************/
    /**
     * Gestiona la selección del método de envío de correo electrónico basado en un tipo específico.
     *
     * Esta función actúa como un despachador (dispatcher) que redirige la solicitud de envío
     * hacia diferentes proveedores o protocolos (SMTP, Gmail o Sendinblue) según el valor
     * del parámetro $type.
     *
     * @param object $UserData  Instancia de datos de sesión.
     * @param object $BASE      Instancia de datos de configuración.
     * @param mixed  $query     Objeto o arreglo con la información específica del correo a enviar (destinatario, asunto, contenido).
     * @param int    $type      Identificador del método de envío: 1 para SMTP, 2 para Gmail, 3 para Sendinblue.
     *
     * @return mixed Resultado de la ejecución de la función de envío seleccionada.
     */
    public function Base_SelectMail($UserData, $BASE, $query, $type){

        /********************** Si todo esta ok **********************/
        // Evalúa el parámetro de tipo para determinar el controlador de correo correspondiente
        switch ($type) {
            // Ejecuta el envío a través de una configuración SMTP estándar
            case 1: $Response = $this->Base_SMTPMail($UserData, $BASE, $query); break;
            // Ejecuta el envío utilizando la API o configuración específica de Gmail
            case 2: $Response = $this->Base_GMail($UserData, $BASE, $query); break;
            // Ejecuta el envío a través del servicio externo Sendinblue (Brevo)
            case 3: $Response = $this->Base_SendingBlue($UserData, $BASE, $query); break;
        }
        /**********************  Retorno datos  **********************/
        // Retorna la respuesta obtenida del método de envío invocado
        return $Response;
    }

    /************************************************************************************************************/
    /**
     * Procesa y envía un correo electrónico utilizando el protocolo SMTP.
     *
     * Esta función extrae la información de configuración del sistema y redes sociales
     * desde la sesión del usuario, prepara una estructura de datos para la plantilla
     * y utiliza la clase MailSender para realizar el envío físico del mensaje.
     *
     * @param object $UserData  Instancia de datos de sesión.
     * @param object $BASE      Instancia de datos de configuración.
     * @param mixed  $query     Objeto o arreglo con la información específica del correo a enviar (destinatario, asunto, contenido).
     *
     * @return mixed Retorna el resultado del envío si es exitoso, o false en caso de fallo.
     */
    protected function Base_SMTPMail($UserData, $BASE, $query){

        /********************** Si todo esta ok **********************/
        // Construye el arreglo de datos que se inyectarán en la plantilla de correo
        $TemplateData = [
            'Sistema_IMGLogo'   => $UserData['Sistema_IMGLogo'],
            'Sistema_Direccion' => $UserData['Sistema_Direccion'],
            'Sistema_Email'     => $UserData['Sistema_Email'],
            'Social_X'          => $UserData['Social_X'],
            'Social_Facebook'   => $UserData['Social_Facebook'],
            'Social_Instagram'  => $UserData['Social_Instagram'],
            'Social_Linkedin'   => $UserData['Social_Linkedin'],
            'baseUrl'           => $BASE,
        ];

        // Inicializa el controlador de envíos de correo
        $mailSender = new MailSender();
        // Ejecuta el envío SMTP pasando la configuración de la plantilla y los datos del mensaje
        $result     = $mailSender->sendSMTPMail($TemplateData, $query); //Envio por correo normal

        /**********************  Retorno datos  **********************/
        // Evalúa la respuesta del emisor y retorna el resultado o un booleano falso si no hubo respuesta positiva
        return ($result) ? $result : false;
    }

    /************************************************************************************************************/
    /**
     * Procesa y envía un correo electrónico utilizando la integración específica de Gmail.
     *
     * Esta función recopila la configuración visual, corporativa y de redes sociales desde
     * la sesión activa del usuario. A diferencia del método SMTP estándar, esta función
     * incluye el parámetro 'MainPathUrl' en el conjunto de datos de la plantilla antes de
     * delegar el envío a la clase MailSender mediante el método especializado para Gmail.
     *
     * @param object $UserData  Instancia de datos de sesión.
     * @param object $BASE      Instancia de datos de configuración.
     * @param mixed  $query     Objeto o arreglo con la información específica del correo a enviar (destinatario, asunto, contenido).
     *
     * @return mixed Retorna el objeto de respuesta del envío si es exitoso, o false en caso de error o ausencia de resultado.
     */
    protected function Base_GMail($UserData, $BASE, $query){

        /********************** Si todo esta ok **********************/
        // Construye el arreglo de datos que se inyectarán en la plantilla de correo
        $TemplateData = [
            'Sistema_IMGLogo'   => $UserData['Sistema_IMGLogo'],
            'Sistema_Direccion' => $UserData['Sistema_Direccion'],
            'Sistema_Email'     => $UserData['Sistema_Email'],
            'Social_X'          => $UserData['Social_X'],
            'Social_Facebook'   => $UserData['Social_Facebook'],
            'Social_Instagram'  => $UserData['Social_Instagram'],
            'Social_Linkedin'   => $UserData['Social_Linkedin'],
            'baseUrl'           => $BASE,
            'MainPathUrl'       => $UserData['MainPathUrl'],
        ];

        // Inicializa el controlador de envíos de correo
        $mailSender = new MailSender();
        // Ejecuta el envío a través del canal específico configurado para Gmail
        $result     = $mailSender->sendGMail($TemplateData, $query);    //Envio por gmail

        /**********************  Retorno datos  **********************/
        // Evalúa la respuesta del emisor y retorna el resultado o un booleano falso si no hubo respuesta positiva
        return ($result) ? $result : false;
    }

    /************************************************************************************************************/
    /**
     * Gestiona el envío de correos electrónicos a través del servicio externo SendingBlue (Brevo).
     *
     * Esta función extrae los metadatos corporativos y de redes sociales desde la sesión
     * del usuario para configurar la apariencia de la plantilla de correo. Posteriormente,
     * delega la ejecución técnica a la clase MailSender utilizando su método específico
     * para la API de SendingBlue.
     *
     * @param object $UserData  Instancia de datos de sesión.
     * @param object $BASE      Instancia de datos de configuración.
     * @param mixed  $query     Objeto o arreglo con la información específica del correo a enviar (destinatario, asunto, contenido).
     *
     * @return mixed Retorna el objeto de respuesta del servicio SendingBlue si la operación es exitosa, o false en caso contrario.
     */
    protected function Base_SendingBlue($UserData, $BASE, $query){

        /********************** Si todo esta ok **********************/
        // Construye el arreglo de datos que se inyectarán en la plantilla de correo
        $TemplateData = [
            'Sistema_IMGLogo'   => $UserData['Sistema_IMGLogo'],
            'Sistema_Direccion' => $UserData['Sistema_Direccion'],
            'Sistema_Email'     => $UserData['Sistema_Email'],
            'Social_X'          => $UserData['Social_X'],
            'Social_Facebook'   => $UserData['Social_Facebook'],
            'Social_Instagram'  => $UserData['Social_Instagram'],
            'Social_Linkedin'   => $UserData['Social_Linkedin'],
            'baseUrl'           => $BASE,
        ];

        // Inicializa el controlador de envíos de correo
        $mailSender = new MailSender();
        // Invoca el método de envío especializado para la API de SendingBlue/Brevo
        $result     = $mailSender->sendSendingBlueMail($TemplateData, $query);    //Envio por Sending Blue

        /**********************  Retorno datos  **********************/
        // Evalúa la respuesta del emisor y retorna el resultado o un booleano falso si no hubo respuesta positiva
        return ($result) ? $result : false;
    }

    /************************************************************************************************************/
    /**
     * Genera una previsualización o prueba técnica de la plantilla de correo electrónico sin realizar un envío real.
     *
     * Esta función recopila los datos de identidad corporativa y redes sociales de la sesión
     * del usuario para integrarlos en el motor de plantillas. Se utiliza principalmente
     * para verificar la renderización, la estructura del contenido y validar los datos
     * antes de proceder con un envío definitivo a través de un servidor de correo.
     *
     * @param object $UserData  Instancia de datos de sesión.
     * @param object $BASE      Instancia de datos de configuración.
     * @param mixed  $query     Parámetros dinámicos y contenido específico que se inyectarán en la plantilla de prueba.
     *
     * @return mixed Retorna el contenido procesado de la plantilla o el estado del test si es exitoso; de lo contrario, false.
     */
    protected function Base_TestMailTemplate($UserData, $BASE, $query){

        /********************** Si todo esta ok **********************/

        // Construye el arreglo de datos que se inyectarán en la plantilla de correo
        $TemplateData = [
            'Sistema_IMGLogo'   => $UserData['Sistema_IMGLogo'],
            'Sistema_Direccion' => $UserData['Sistema_Direccion'],
            'Sistema_Email'     => $UserData['Sistema_Email'],
            'Social_X'          => $UserData['Social_X'],
            'Social_Facebook'   => $UserData['Social_Facebook'],
            'Social_Instagram'  => $UserData['Social_Instagram'],
            'Social_Linkedin'   => $UserData['Social_Linkedin'],
            'baseUrl'           => $BASE,
        ];

        // Inicializa el controlador de envíos de correo
        $mailSender = new MailSender();
        // Ejecuta el método de prueba que procesa la plantilla pero omite la instrucción de envío (dispatch)
        $result     = $mailSender->testMailTemplate($TemplateData, $query);    //Se obtiene solo las respuesta

        /**********************  Retorno datos  **********************/
        // Evalúa la respuesta del emisor y retorna el resultado o un booleano falso si no hubo respuesta positiva
        return ($result) ? $result : false;
    }

    /************************************************************************************************************/
    /**
     * Traduce la ruta del sistema de archivos de un controlador a su ruta de vista correspondiente.
     *
     * Esta función asume una arquitectura de carpetas basada en convenciones donde las rutas
     * contienen los directorios "controller" y "views". Realiza una manipulación de strings
     * para extraer la ruta relativa desde el nombre de la aplicación y sustituir el
     * segmento de lógica por el de presentación.
     *
     * @param string $directorio El path absoluto o relativo completo del archivo del controlador.
     * @param string $aplicacion El nombre o segmento de la aplicación que sirve como punto de anclaje.
     *
     * @return string La ruta transformada hacia el directorio de vistas.
     */
    protected function returnRutaVista($directorio, $aplicacion){

        /********************** Si todo esta ok **********************/
        // Localiza la posición de la aplicación en el path y extrae la ruta a partir de ese punto
        $rutaController = substr($directorio, strpos($directorio, $aplicacion)); //se obtiene la ruta del controlador

        // Reemplaza la subcadena "controller" por "views" para apuntar al directorio de plantillas
        $rutaVista      = str_replace("controller", "views", $rutaController);   //se obtiene la ruta a la vista

        /**********************  Retorno datos  **********************/
        // Devuelve la cadena de texto con la ruta final hacia la vista
        return $rutaVista;
    }

    /************************************************************************************************************/
    /**
     * Construye dinámicamente condiciones SQL para una cláusula WHERE a partir
     * de parámetros recibidos mediante POST.
     *
     * La función procesa una lista de campos y genera condiciones según el tipo
     * de comparación indicado. Los valores recibidos se incorporan mediante
     * parámetros preparados (?) y se almacenan separadamente en el arreglo
     * de parámetros de retorno.
     *
     * Tipos de filtrado soportados:
     * - 1: Comparación exacta mediante el operador "=".
     * - 2: Coincidencia parcial mediante el operador "LIKE".
     * - 3: Comparación de rangos mediante el operador "BETWEEN".
     *
     * Para el tipo 3, cada elemento de $WhereData debe representar la configuración
     * de un campo y sus respectivos parámetros de inicio y término, de acuerdo
     * con el formato procesado por parseDataSeparator().
     *
     * @param string $whereInt   Cláusula WHERE existente a la que se agregarán
     *                           las nuevas condiciones.
     * @param array  $WhereParams Parámetros asociados a la cláusula WHERE inicial.
     * @param string $WhereData  Lista de campos o configuraciones de campos,
     *                           separados por comas.
     * @param string $Transx     Alias utilizado para referenciar los campos
     *                           dentro de la consulta SQL.
     * @param int    $Type       Tipo de comparación que se aplicará:
     *                           1 para igualdad, 2 para LIKE y 3 para BETWEEN.
     *
     * @return array Arreglo con dos elementos:
     *               - where: cláusula WHERE resultante.
     *               - params: parámetros correspondientes a las condiciones
     *                         generadas, combinados con los parámetros recibidos.
     */
    protected function searchWhere($whereInt, $WhereParams, $WhereData, $Transx, $Type){

        /********************** Si todo esta ok **********************/
        // Inicializa los contenedores para las condiciones SQL y sus parámetros.
        $parts  = [];
        $params = [];

        // Ejecuta el procesamiento únicamente cuando se proporciona información de campos.
        if($WhereData!=''){
            // Convierte la cadena de campos separados por comas en un arreglo.
            $arrWhere = $this->CommonData->parseDataCommas($WhereData); //Separacion por comas
            // Determina el tipo de comparación que se utilizará para construir las condiciones SQL.
            switch ($Type) {
                /***********************************/
                // Tipo 1: Comparación exacta mediante el operador "=".
                case 1:
                    // Recorre los campos configurados para el filtro.
                    foreach ($arrWhere as $field) {
                        // Verifica que el campo tenga un valor no vacío en POST.
                        if (!empty($_POST[$field])) {
                            // Agrega la condición SQL utilizando un parámetro preparado.
                            $parts[]  = $Transx.'.'.$field.' = ?';
                            $params[] = $_POST[$field];
                        }
                    }
                    break;
                /***********************************/
                // Tipo 2: Comparación parcial mediante el operador LIKE.
                case 2:
                    // Recorre los campos configurados para la búsqueda.
                    foreach ($arrWhere as $field) {
                        // Verifica que el campo tenga un valor no vacío en POST.
                        if (!empty($_POST[$field])) {
                            // Agrega comodines al valor para realizar una coincidencia parcial.
                            $parts[]  = $Transx.'.'.$field.' LIKE ?';
                            $params[] = '%'.$_POST[$field].'%';
                        }
                    }
                    break;
                /***********************************/
                // Tipo 3: Comparación mediante un rango utilizando BETWEEN.
                case 3:
                    // Recorre las configuraciones de campos correspondientes a rangos.
                    foreach ($arrWhere as $field) {
                        // Descompone la configuración en campo, parámetro inicial y parámetro final.
                        $arrData = $this->CommonData->parseDataSeparator($field);
                        // Verifica que ambos extremos del rango tengan valores en POST.
                        if (!empty($_POST[$arrData[1]]) && !empty($_POST[$arrData[2]])) {
                            // Agrega la condición BETWEEN con dos parámetros preparados.
                            $parts[]  = $Transx.'.'.$arrData[0].' BETWEEN ? AND ?';
                            $params[] = $_POST[$arrData[1]];
                            $params[] = $_POST[$arrData[2]];
                        }
                    }
                    break;
            }
        }

        /**********************  Retorno datos  **********************/
        // Combina todas las condiciones generadas utilizando el operador lógico AND.
        $subWhere   = $parts ? implode(' AND ', $parts) : '';

        // Agrega las nuevas condiciones a la cláusula WHERE existente cuando corresponde.
        $DataReturn = ($subWhere != '') ? $whereInt.' AND '.$subWhere : $whereInt;

        // Determina si se utiliza la cláusula WHERE existente o únicamente las nuevas condiciones.
        $finalWhere = ($whereInt != '') ? $DataReturn : $subWhere;

        // Retorna la cláusula WHERE final junto con todos los parámetros asociados.
        return [
            'where'  => $finalWhere,
            'params' => array_merge($WhereParams, $params),
        ];
    }

    /************************************************************************************************************/
    /**
     * Valida rangos de fechas recibidos mediante datos de entrada POST.
     *
     * Recibe una configuración de campos separados por comas, obtiene de cada
     * elemento los nombres de los campos correspondientes a la fecha de inicio
     * y término, y verifica que ambos valores estén presentes y puedan ser
     * interpretados como fechas válidas.
     *
     * También valida que la fecha de término no sea anterior a la fecha de inicio.
     * Los mensajes generados durante las validaciones se acumulan y se retornan
     * como una cadena separada por comas.
     *
     * @param string $WhereData Configuración de los campos de fecha separados
     *                          por comas. Cada elemento debe contener la información
     *                          necesaria para identificar los campos POST de inicio
     *                          y término.
     *
     * @return string Cadena con los mensajes de validación encontrados. Retorna
     *               una cadena vacía cuando no se generan errores.
     *
     * @throws Exception No se propaga directamente. Las excepciones generadas
     *                   durante la creación de objetos DateTime son capturadas
     *                   internamente y transformadas en un mensaje de validación.
     */
    protected function searchValidateDates($WhereData){

        /********************** Si todo esta ok **********************/
        // Ejecuta la lógica solo si se proporcionaron campos para filtrar
        if(isset($WhereData)&&$WhereData!=''){
            // Inicializa el contenedor para las partes de la consulta
            $parts    = [];
            // Convierte la cadena de campos separados por comas en un array usable
            $arrWhere = $this->CommonData->parseDataCommas($WhereData); //Separacion por comas
            //Se recorren los datos separados
            foreach ($arrWhere as $field) {
                // Descompone la configuración del campo (espera: campo-post_inicio-post_fin)
                $arrData = $this->CommonData->parseDataSeparator($field); //Separacion por guiones
                // Verifica que ambos límites del rango existan en el POST
                if (isset($_POST[$arrData[1]], $_POST[$arrData[2]])&&!empty($_POST[$arrData[1]])&&!empty($_POST[$arrData[2]])) {
                    // Compara los datos 'Inicio' AND 'Fin'
                    try {
                        $inicio = new DateTime($_POST[$arrData[1]]);
                        $fin    = new DateTime($_POST[$arrData[2]]);

                        // Registra un error cuando la fecha de término es anterior a la fecha de inicio
                        if ($fin < $inicio) {
                            $parts[] = 'La fecha de término no puede ser anterior a la fecha de inicio.';
                        }

                    } catch (Exception $e) {
                        // Registra un error cuando alguno de los valores no puede interpretarse como fecha
                        $parts[] = 'Formato de fecha inválido.';
                    }
                }
            }
            /**********************  Retorno datos  **********************/
            // Une todas las partes generadas mediante el una coma
            $returnData   = $parts ? implode(', ', $parts) : '';
            // Retorna la respuesta
            return ($returnData != '') ? $returnData : '';

        }

        // Retorna vacio por defecto
        return '';
    }


    /************************************************************************************************************/
    /**
     * Realiza la limpieza y saneamiento de una cadena de texto para prevenir ataques básicos y errores de formato.
     *
     * Esta función aplica tres niveles de procesamiento: elimina espacios en blanco residuales,
     * remueve escapes de barras invertidas y convierte caracteres especiales en entidades
     * HTML para prevenir la ejecución de scripts (XSS) al renderizar los datos.
     *
     * @param string $Data La cadena de texto original que se desea procesar.
     *
     * @return string La cadena procesada y saneada.
     */
    private function clearData($Data){

        /********************** Si todo esta ok **********************/
        // Elimina espacios en blanco u otros caracteres del principio y final de la cadena
        $Data = trim($Data);

        // Quita las barras invertidas de una cadena con comillas escapadas
        $Data = stripslashes($Data);

        // Convierte caracteres especiales (como <, >, &, ", ') en entidades HTML
        $Data = htmlspecialchars($Data);

        /**********************  Retorno datos  **********************/
        // Retorna el valor final una vez aplicados todos los filtros de seguridad y formato
        return $Data;
    }

    /************************************************************************************************************/
    /**
     * Renderiza la interfaz de usuario combinando plantillas de encabezado, cuerpo y pie de página.
     *
     * Esta función orquestadora gestiona la visualización de las vistas del sistema basándose
     * en el tipo de sesión del usuario y el propósito de la vista (web, impresión, API o modal).
     * Selecciona dinámicamente los componentes estructurales (header/footer) que deben
     * acompañar al contenido principal definido en la ruta.
     *
     * @param int    $TypeView Define el formato de la vista:
     *                         0: Invitado, 1: Usuario estándar, 2: Sin plantillas (Modal),
     *                         3: Impresión normal, 4: Impresión de documentos mercantiles.
     * @param string $Route    Ruta relativa del archivo de la vista que contiene el cuerpo de la página.
     *
     * @return void La función emite el contenido directamente al buffer de salida mediante echo.
     */
    protected function showVista($TypeView, $Route){

        /**********************    Instancia    **********************/
        // Inicializa el motor de renderizado de vistas
        $view     = new View;

        /**********************  Retorno datos  **********************/
        // Determina la combinación de plantillas (templates) según el propósito de la vista
        switch ($TypeView) {
            // Caso 0: Estructura para usuarios no autenticados (Guest)
            case 0:
                echo $view->render('../app/templates/guest-header.php');
                echo $view->render('../'.$Route);
                echo $view->render('../app/templates/guest-footer.php');
                break;
            // Caso 1: Estructura estándar para usuarios del sistema
            case 1:
                echo $view->render('../app/templates/user-header.php');
                echo $view->render('../'.$Route);
                echo $view->render('../app/templates/user-footer.php');
                break;
            // Caso 2: Carga únicamente el cuerpo (útil para peticiones AJAX o Ventanas Modales)
            case 2:
                echo $view->render('../'.$Route); // Vista
                break;
            // Caso 3: Estructura optimizada para impresión de reportes genéricos
            case 3:
                echo $view->render('../app/templates/user-printer-header.php');
                echo $view->render('../'.$Route);
                echo $view->render('../app/templates/user-printer-footer.php');
                break;
            // Caso 4: Estructura específica para documentos mercantiles (facturas, boletas, etc.)
            case 4:
                echo $view->render('../app/templates/user-printerDocs-header.php');
                echo $view->render('../'.$Route);
                echo $view->render('../app/templates/user-printerDocs-footer.php');
                break;

        }

    }

    /************************************************************************************************************/
    /**
     * Gestiona y despliega la visualización de errores del sistema según el contexto del usuario.
     *
     * Esta función configura los metadatos de la página (SEO y autoría) y la información del error,
     * seleccionando el método de respuesta adecuado: renderizado de plantillas HTML para la
     * interfaz web o respuestas formateadas en JSON para peticiones de API.
     *
     * @param int    $TypeView  Determina el formato de salida: 1 (Página completa), 2 (Solo contenido/Modal).
     * @param object $f3        Instancia del framework (Fat-Free Framework) para gestión de variables y sesión.
     * @param mixed  $dataError Información detallada del error que se mostrará al usuario o se enviará a la API.
     *
     * @return void Envía la respuesta directamente al cliente mediante renderizado o llamada a la clase Response.
     */
    protected function showError($TypeView, $f3, $dataError = ''){

        // Obtiene la información del usuario desde la sesión actual
        $UserData = $f3->get('SESSION.DataInfo');

        /********************** Si todo esta ok **********************/
        //Datos enviados a la pagina
        $f3->data = [
            'PageTitle'       => 'Error Consulta',
            'PageDescription' => 'Error Consulta.',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            /*===========  Datos del usuario ===========*/
            'UserData'        => $UserData,
            /*=========== Datos Consultados ===========*/
            'dataError'       => $dataError,
        ];

        /**********************    Instancia    **********************/
        // Inicializa el motor de renderizado de vistas
        $view     = new View;

        /**********************  Retorno datos  **********************/
        // Define el nivel de profundidad del renderizado según TypeView
        switch ($TypeView) {
            // Caso 1: Renderiza la página de error completa con cabecera y pie de página
            case 1:
                echo $view->render('../app/templates/user-header.php'); // Header
                echo $view->render('../app/templates/user-error.php');  // Vista
                echo $view->render('../app/templates/user-footer.php'); // Footer
                break;
            // Caso 2: Renderiza únicamente el componente de error (para llamadas parciales)
            case 2:
                echo $view->render('../app/templates/user-error.php');  // Vista
                break;
            // Caso 3: Reservado para futuras implementaciones de visualización
            case 3:
                //otra vista
                break;

        }

    }

    /************************************************************************************************************/
    /**
     * Consolida múltiples respuestas de operaciones de base de datos en un único reporte de errores y datos.
     *
     * Esta función recorre un conjunto de resultados, validando la estructura de cada uno.
     * Si una respuesta indica un estado fallido, extrae el mensaje de error y genera
     * un registro detallado que vincula el error con la tabla específica donde ocurrió
     * la falla.
     *
     * @param array $responses Arreglo de respuestas de consultas, donde cada elemento
     *                         debe ser un array asociativo con claves 'status', 'table', 'error' y 'data'.
     *
     * @return array Arreglo asociativo con dos claves:
     *               - 'errors': Lista de mensajes de error descriptivos.
     *               - 'data': Lista de trazas técnicas formateadas indicando la tabla afectada.
     */
    protected function mergeResponses(array $responses): array {

        // Inicializa contenedores para los mensajes de error y la información técnica
        $errors = [];
        $data   = [];

        // Itera a través de cada respuesta individual recibida en el conjunto
        foreach ($responses as $response) {

            // Validación mínima: Omite el elemento si no es un arreglo válido
            if (!is_array($response)) {
                continue;
            }

            // Identifica la tabla involucrada; usa 'undefined' como respaldo si no existe la clave
            $table = $response['table'] ?? 'undefined';

            // Verifica si la operación fue marcada como fallida explícitamente
            if (isset($response['status']) && $response['status'] === false) {
                // Almacena el mensaje de error legible para el usuario
                $errors[] = $response['error'] ?? '';
                // Construye y almacena una cadena técnica detallando la ubicación y el dato del error
                $data[]   = 'Query Error:Table '.$table.'->'.$response['data'] ?? '';
            }

        }

        // Retorna el resumen consolidado de las fallas encontradas
        return [
            'errors' => $errors,
            'data'   => $data
        ];

    }




}
