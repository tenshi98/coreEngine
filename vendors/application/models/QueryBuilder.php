<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class QueryBuilder{

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                 Instancias                                                      */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	//Definiciones
	private $FileManager;
	private $Codification;
	private $CommonData;

	/******************************************************************************/
	//Instancias
	public function __construct() {
		$this->FileManager  = new FileManager();
		$this->Codification = new FunctionsSecurityCodification();
		$this->CommonData   = new FunctionsCommonData();
	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                Documentacion                                                    */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
    /*
        funciones de los array
        array_keys()     ->Es un array
        count()          -> Devuelve el número de elementos en un array.
        array_push()     -> Inserta uno o más elementos al final de un array.
        array_pop()      -> Elimina y devuelve el último elemento de un array.
        array_shift()    -> Elimina y devuelve el primer elemento de un array.
        array_unshift()  -> Inserta uno o más elementos al inicio de un array.
        array_merge()    -> Combina uno o más arrays en un solo array.
        array_slice()    -> Devuelve una porción de un array.
        array_reverse()  -> Invierte un array.
        array_unique()   -> Elimina valores duplicados de un array.

        sort()    -> Ordena un array en orden ascendente.
        rsort()   -> Ordena un array en orden descendente.
        asort()   -> Ordena un array asociativo en orden ascendente, manteniendo la asociación entre claves y valores.
        arsort()  -> Ordena un array asociativo en orden descendente, manteniendo la asociación entre claves y valores.
        ksort()   -> Ordena un array asociativo por claves en orden ascendente.
        krsort()  -> Ordena un array asociativo por claves en orden descendente.

        *****************************
        opciones select
        'data'   => 'DISTINCT(data1) AS Cuenta',                 -> Hace lo mismo que GROUP BY, agrupa los valores de la columna seleccionada
        'data'   => 'COUNT(data1) AS Cuenta',                    -> Permite el conteo de datos, agrupa el resultado, separarlos con GROUP BY
        'data'   => 'SUM(data1) AS Suma',                        -> Permite la suma de datos, agrupa el resultado, separarlos con GROUP BY
        'data'   => 'AVG(data1) AS Promedio',                    -> Permite tener el promedio de datos, agrupa el resultado, separarlos con GROUP BY
        'data'   => 'MIN(data1) AS Minimo',                      -> Permite el minimo de un grupo de datos, agrupa el resultado, separarlos con GROUP BY
        'data'   => 'MAX(data1) AS Maximo',                      -> Permite el maximo de un grupo de datos, agrupa el resultado, separarlos con GROUP BY
        'data'   => 'UCASE(data1) AS Nombre',                    -> Permite modificar el dato, todo a mayusculas
        'data'   => 'LCASE(data1) AS Nombre',                    -> Permite modificar el dato, todo a minusculas
        'data'   => 'CONCAT(data1,"PALABRA1",data2) AS Palabra', -> Permite concatenar datos
        'data'   => 'SUBSTRING(data1,6,10) AS Palabra',          -> Permite extraer datos desde un punto en especifico del texto
        'data'   => 'LEN(data1) AS Largo',                       -> Obtiene el numero de caracteres que conforman el dato
        'data'   => 'ROUND(data1, 2) AS Total',                  -> Redeondear con un numero de decimales indicados
        'data'   => 'FLOOR(data1) AS Total',                     -> Redondea al numero mas bajo cercano
        'data'   => 'CEILING(data1) AS Total',                   -> Redondea al numero mas alto cercano
        'data'   => 'data1, data2, CASE WHEN data3 IS NULL THEN "Sin dato" ELSE data3 END AS Comentario', -> Cambia el valor en el caso de que este vacio, se pueden dar mas opciones

        *****************************
        opciones de los join
        'join'   => 'LEFT JOIN data_table2 ON data_table.ID = data_table2.ID AND data_table2.Fecha LIKE "2004-08-%"', -> Poner un filtro desde el mismo join

        *****************************
        modos alternativos where
        'where'  => 'data1 IN ("1","2","3")',     -> Es lo mismo que 'data1 = 1 OR data1 = 2 OR data1 = 3'
        'where'  => 'data1 NOT IN ("1","2","3")', -> Es lo mismo que 'data1 != 1 AND data1 != 2 AND data1 != 3'
        'where'  => 'data1 LIKE "2004-08-%"',     -> Busca todos los registros que comiencen con 2004-08-
        'where'  => 'data1 LIKE "%io%"',          -> Busca todos los registros que contengan, no importando donde, la frase entre los %
        'where'  => 'data1 LIKE "%i_"',           -> Busca todos los registros que contengan i como penultimo dato (cada _ es un caracter)
        'where'  => 'data1 LIKE "_i%"',           -> Busca todos los registros que contengan i como segundo dato (cada _ es un caracter)

        *****************************
        Agrupaciones
        'group'  => 'DATETRUNC(MONTH, Fecha)', -> Por fechas en base al mes

    */

    /*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
    /**
     * Ejecuta una consulta para obtener una única fila de la base de datos.
     * * Construye una sentencia SQL SELECT a partir de un arreglo asociativo,
     * añade una cláusula de límite unitario y retorna el primer registro encontrado
     * o el texto de la consulta según los parámetros.
     *
     * @param array $query Arreglo asociativo con las partes de la consulta (data, table, join, where, group, having, order).
     * @param mixed $DBConn Recurso o instancia de conexión a la base de datos.
     * @param bool $showQuery Si es true, retorna el string de la consulta SQL en lugar de ejecutarla.
     * @return array Retorna un arreglo con los resultados.
     * @throws Exception Si ocurre un error durante la ejecución capturado por el bloque try-catch.
     *
	 * @example
	 * ```php
	 * //Formato de la query
     * $query = [
     *   'data'   => 'data1,data2,data3',   -> Ver opciones select
     *   'table'  => 'data_table',          -> Tabla
     *   'join'   => '',                    -> Ver opciones de los join
     *   'where'  => 'data1 = 1',           -> Ver modos alternativos where
     *   'group'  => '',                    -> Ver agrupaciones
     *   'having' => '',
     *   'order'  => 'data1 DESC'
     * ];
     *
     * //ejecucion
     * $qbuilder->queryRow($query, $DBConn);
	 * ```
	 *
     */
    public function queryRow(array $query, $DBConn, bool $showQuery = false){

        /*************** Validaciones ***************/
        // Comprueba la existencia y contenido de la tabla objetivo
        if(!isset($query['table']) || $query['table']==''){ return ['status' => false, 'error' => 'Query Error: No hay datos en $table', 'data' => []];}
        // Comprueba la existencia y contenido de las columnas a seleccionar
        if(!isset($query['data']) || $query['data']==''){   return ['status' => false, 'error' => 'Query Error: No hay datos en $data',  'data' => [], 'table' => $query['table']];}

        /*************** Generacion Query ***************/
        // Llama al método interno para construir la sentencia SQL base
        $ActionSQL = $this->createQuery($query);
        // Restringe el conjunto de resultados a un solo registro
        $ActionSQL.= ' LIMIT 1';

        /*************** Ejecutar   ***************/
        // Retorna la cadena SQL si se ha solicitado el modo de depuración/visualización
        if ($showQuery) {
            return ['status' => false, 'error' => '', 'data' => $ActionSQL, 'table' => $query['table']];
        }

        // Intento de ejecución de la consulta contra el motor de base de datos
        try {
            $result = $this->queryExecute($ActionSQL, $DBConn, false, true);
            // Obtiene el primer índice del conjunto de resultados si es válido, de lo contrario false
            if($result['status']){
                // Estructura estándar de respuesta
                $response = [
                    'status'  => true,
                    'data'    => $result['data']
                ];
            }else{
                // Estructura estándar de respuesta
                $response = [
                    'status'  => false,
                    'error'   => $result['error'],
                    'data'    => $result['internal_error'],
                    'table'   => $query['table'],
                ];
            }
            // Retorna los datos
            return $response;
        } catch (Exception $e) {
            // Se listan los errores
            $Error = $this->logError($ActionSQL, $e);
            // Registra el fallo y retorna la información del error procesada
            return ['status' => false, 'error' => $Error['error'], 'data' => $Error['internal_error'], 'table' => $query['table']];
        }

    }

    /******************************************************************************/
    /**
     * Calcula el número total de coincidencias (filas) que devuelve una consulta específica.
     * * Esta función envuelve la consulta original en una subconsulta para contar el
     * número de registros resultantes, garantizando la compatibilidad con consultas
     * que utilicen agrupamientos o selecciones complejas.
     *
     * @param array $query Arreglo asociativo con las partes de la consulta (data, table, join, where, etc.).
     * @param mixed $DBConn Recurso o instancia de conexión a la base de datos.
     * @param bool $showQuery Si es true, retorna el string de la consulta SQL de conteo en lugar de ejecutarla.
     * @return array Retorna un arreglo con los resultados.
     *
	 * @example
	 * ```php
	 * //Formato de la query
     * $query = [
     *   'data'   => 'data1,data2,data3',   -> Ver opciones select
     *   'table'  => 'data_table',          -> Tabla
     *   'join'   => '',                    -> Ver opciones de los join
     *   'where'  => 'data1 = 1',           -> Ver modos alternativos where
     *   'group'  => '',                    -> Ver agrupaciones
     *   'having' => '',
     *   'order'  => 'data1 DESC'
     * ];
     *
     * //ejecucion
     * $qbuilder->queryNRows($query, $DBConn);
	 * ```
	 *
     */
    public function queryNRows(array $query, $DBConn, bool $showQuery = false){

        /*************** Validaciones ***************/
        // Valida que se haya especificado una tabla de origen
        if(!isset($query['table']) || $query['table']==''){ return ['status' => false, 'error' => 'Query Error: No hay datos en $table', 'data' => []];}
        // Valida que existan columnas definidas para la selección
        if(!isset($query['data']) || $query['data']==''){   return ['status' => false, 'error' => 'Query Error: No hay datos en $data',  'data' => [], 'table' => $query['table']];}

        /*************** Generacion Query ***************/
        // Genera la sentencia SQL base mediante el método interno createQuery
        $BaseSQL   = $this->createQuery($query);
        // Encapsula la consulta base en una subconsulta para realizar el conteo total de registros
        $ActionSQL = 'SELECT COUNT(*) AS _total FROM (' . $BaseSQL . ') AS _t';

        /*************** Ejecutar   ***************/
        // Retorna la sentencia SQL de conteo si se solicita la visualización
        if ($showQuery) {
            return ['status' => false, 'error' => '', 'data' => $ActionSQL, 'table' => $query['table']];
        }

        // Bloque de ejecución con manejo de excepciones
        try {
            $result = $this->queryExecute($ActionSQL, $DBConn, false, true);
            // Obtiene el valor numérico de la columna virtual '_total' convertido a entero
            if($result['status']){
                // Estructura estándar de respuesta
                $response = [
                    'status'  => true,
                    'data'    => (int)$result['data']['_total']
                ];
            }else{
                // Estructura estándar de respuesta
                $response = [
                    'status'  => false,
                    'error'   => $result['error'],
                    'data'    => $result['internal_error'],
                    'table'   => $query['table'],
                ];
            }
            // Retorna los datos
            return $response;
        } catch (Exception $e) {
            // Se listan los errores
            $Error = $this->logError($ActionSQL, $e);
            // Registra el fallo y retorna la información del error procesada
            return ['status' => false, 'error' => $Error['error'], 'data' => $Error['internal_error'], 'table' => $query['table']];
        }

    }

    /******************************************************************************/
    /**
     * Ejecuta una consulta para obtener un conjunto de múltiples registros de la base de datos.
     * * A diferencia de queryRow, este método no limita el resultado a una sola fila,
     * permitiendo recuperar colecciones completas de datos según los criterios
     * definidos en el arreglo de configuración.
     *
     * @param array $query Arreglo asociativo con las partes de la consulta (data, table, join, where, group, having, order, limit).
     * @param mixed $DBConn Recurso o instancia de conexión a la base de datos.
     * @param bool $showQuery Si es true, retorna el string de la consulta SQL generada en lugar de ejecutarla.
     * @return array Retorna un arreglo con los resultados.
     * @throws Exception Captura cualquier error surgido durante la ejecución de la sentencia SQL.
     *
	 * @example
	 * ```php
	 * //Formato de la query
     * $query = [
     *   'data'   => 'data1,data2,data3',   -> Ver opciones select
     *   'table'  => 'data_table',          -> Tabla
     *   'join'   => '',                    -> Ver opciones de los join
     *   'where'  => 'data1 = 1',           -> Ver modos alternativos where
     *   'group'  => '',                    -> Ver agrupaciones
     *   'having' => '',
     *   'order'  => 'data1 DESC',
     *   'limit'  => 60
     * ];
     *
     * //ejecucion
     * $qbuilder->queryArray($query, $DBConn);
	 * ```
	 *
     */
    public function queryArray(array $query, $DBConn, bool $showQuery = false){

        /*************** Validaciones ***************/
        // Verifica que se haya definido la tabla principal de la consulta
        if(!isset($query['table']) || $query['table']==''){ return ['status' => false, 'error' => 'Query Error: No hay datos en $table', 'data' => []];}
        // Verifica que se hayan especificado las columnas o campos de retorno
        if(!isset($query['data']) || $query['data']==''){   return ['status' => false, 'error' => 'Query Error: No hay datos en $data',  'data' => [], 'table' => $query['table']];}

        /*************** Generacion Query ***************/
        // Llama al constructor de consultas para armar la sentencia SQL completa
        $ActionSQL = $this->createQuery($query);

        /*************** Ejecutar   ***************/
        // Retorna la cadena de texto de la consulta si se activó el parámetro de visualización
        if ($showQuery) {
            return ['status' => false, 'error' => '', 'data' => $ActionSQL, 'table' => $query['table']];
        }

        // Intento de ejecución de la consulta
        try {
            // Ejecuta la sentencia a través del método de conexión de bajo nivel
            $result = $this->queryExecute($ActionSQL, $DBConn);
            // Obtiene el conjunto completo de resultados obtenidos
            if($result['status']){
                // Estructura estándar de respuesta
                $response = [
                    'status'  => true,
                    'data'    => $result['data']
                ];
            }else{
                // Estructura estándar de respuesta
                $response = [
                    'status'  => false,
                    'error'   => $result['error'],
                    'data'    => $result['internal_error'],
                    'table'   => $query['table'],
                ];
            }
            // Retorna los datos
            return $response;
        } catch (Exception $e) {
            // Se listan los errores
            $Error = $this->logError($ActionSQL, $e);
            // Registra el fallo y retorna la información del error procesada
            return ['status' => false, 'error' => $Error['error'], 'data' => $Error['internal_error'], 'table' => $query['table']];
        }

    }

    /******************************************************************************/
    /******************************************************************************/
    /**
     * Inserta un nuevo registro en la base de datos con soporte para validaciones y subida de archivos.
     * * Esta función orquestra el proceso de inserción realizando validaciones de campos obligatorios
     * y únicos, procesando archivos adjuntos, codificando datos sensibles y sanitizando los valores
     * antes de generar y ejecutar la sentencia SQL INSERT.
     *
     * @param array $query Configuración de la inserción (data, table, Post, required, unique, files, etc.).
     * @param mixed $DBConn Instancia de conexión a la base de datos.
     * @param bool $showQuery Si es true, retorna la cadena SQL sin ejecutar la acción.
     * @param bool $novalidate Si es true, omite la limpieza/sanitización de los datos del Post.
     * @return array Retorna un arreglo con los resultados.
     *
	 * @example
	 * ```php
	 * //Formato de la query
     * $query = [
     *   'data'      => 'usuario,idEstado,email,Nombre,Rut,password', -> Datos a insertar, dejar fuera los archivos
     *   'required'  => 'email,Nombre,Rut',                           -> Datos obligatorios a insertar, son validados, si no existen impide la ejecucion
     *   'unique'    => 'email,Nombre-Rut',                           -> Datos unicos, se consulta en la BD que el dato ingresado no este repetido
     *   'encode'    => 'password',                                   -> Datos a codificar
     *   'table'     => 'usuarios_listado',                           -> Tabla donde se ejecuta la consulta
     *   'Post'      => $_POST,                                       -> Datos $_POST entregados
     *   'files'     => [                                             -> Arreglo con los archivos, cada uno va dentro de su propio array
     *     [
     *      'Identificador' => 'Direccion_img',                                       -> Columna dentro de la BD, identificador del archivo
     *      'SubCarpeta'    => '',                                                    -> Opcional, si el archivo se guarda en una subcarpeta
     *      'NombreArchivo' => '',                                                    -> Se se utiliza un nombre particular, sino, se utiliza el sufijo
     *      'SufijoArchivo' => 'Sufijo_',                                             -> Si al nombre del archivo se le pone un sufijo
     *      'ValidarTipo'   => 'word,excel,powerpoint,pdf,image,txt,zip,video,music', -> Formato archivo a validar
     *      'ValidarPeso'   => 10,                                                    -> Validacion peso maximo del archivo (en megas)
     *      'Base64'        => true                                                   -> Si el archivo es entregado como texto (base64), esto hace que se ignoren todas las validaciones
     *     ],
     *    ]
     * ];
     *
     * //ejecucion
     * $qbuilder->queryInsert($query, $DBConn);
	 * ```
	 *
     */
    public function queryInsert(array $query, $DBConn, bool $showQuery = false, bool $novalidate = false){

        /*************** Validaciones ***************/
        // Verifica que se haya definido la tabla principal de la consulta
        if(!isset($query['table']) || $query['table']==''){ return ['status' => false, 'error' => 'Query Error: No hay datos en $table', 'data' => []];}
        // Ejecuta la validación de presencia para los campos definidos como obligatorios
        if(isset($query['required'])&&$query['required']!=''){
            $dataVal  = $this->validateRequired($query['required'], $query['Post']);
            if ($dataVal['status'] !== true) {return ['status' => false, 'error' => $dataVal['error'], 'data' => [], 'table' => $query['table']];}
        }
        // Verifica que los valores para campos únicos no existan previamente en la tabla
        if(isset($query['unique'])&&$query['unique']!=''){
            $dataUniq = $this->validateUnique($query['unique'], $query['table'], $query['Post'], '', $DBConn);
            if ($dataUniq['status'] !== true) {return ['status' => false, 'error' => $dataUniq['error'], 'data' => [], 'table' => $query['table']];}
        }

        /*************** Datos    ***************/
        // Descompone la cadena de nombres de columnas en un arreglo indexado
        $arrData = $this->CommonData->parseDataCommas($query['data']);

        /*************** Archivos   ***************/
        // Gestiona la lógica de carga física de archivos y obtiene los nombres de columnas y valores para el SQL
        $fileProc      = $this->processFiles($query, 'insert');
        if ($fileProc['success'] === false) {return ['status' => false, 'error' => $fileProc['error'], 'data' => [], 'table' => $query['table']];}
        $DatosNombres  = $fileProc['nombres'];
        $DatosArchivos = $fileProc['archivos'];

        /*************** Codificacion  ***************/
        // Aplica algoritmos de cifrado o hashing a los campos especificados en 'encode'
        $Post = $this->encodeFormData($query);

        /*************** Guardar   ***************/
        // Inicializa contenedores para construir dinámicamente las partes de la sentencia SQL
        $matrixColumn = [];
        $matrixValue  = [];

        // Filtra los datos del Post que coinciden con las columnas definidas en 'data'
        foreach ($arrData as $data) {
            if (!empty($Post[$data])) {
                $matrixColumn[] = $data;
                // Aplica limpieza de caracteres especiales a menos que se indique lo contrario en $novalidate
                $matrixValue[]  = "'".($novalidate ? $Post[$data] : $this->clearData($Post[$data]))."'";
            }
        }
        // Consolida los arreglos en cadenas separadas por comas
        $DataColumn = $matrixColumn ? implode(', ', $matrixColumn) : '';
        $DataValue  = $matrixValue ? implode(', ', $matrixValue) : '';

        /*************** Generacion Query ***************/
        // Ensambla la sentencia INSERT final incluyendo los fragmentos de archivos procesados
        $ActionSQL = 'INSERT INTO '.$query['table'].' ('.$DataColumn.$DatosNombres.') VALUES ('.$DataValue.$DatosArchivos.')';

        /*************** Ejecutar   ***************/
        // Retorna el texto de la consulta si se activó el modo de visualización
        if ($showQuery) {
            return ['status' => false, 'error' => '', 'data' => $ActionSQL, 'table' => $query['table']];
        }

        // Ejecución de la sentencia dentro de un bloque de control de excepciones
        try {
            $result = $this->queryExecute($ActionSQL, $DBConn);
            // Obtiene el ID autogenerado del nuevo registro
            if($result['status']){
                // Estructura estándar de respuesta
                $response = [
                    'status'  => true,
                    'data'    => $DBConn->lastInsertId()
                ];
            }else{
                // Estructura estándar de respuesta
                $response = [
                    'status'  => false,
                    'error'   => $result['error'],
                    'data'    => $result['internal_error'],
                    'table'   => $query['table'],
                ];
            }
            // Retorna los datos
            return $response;
        } catch (Exception $e) {
            // Se listan los errores
            $Error = $this->logError($ActionSQL, $e);
            // Registra el fallo y retorna la información del error procesada
            return ['status' => false, 'error' => $Error['error'], 'data' => $Error['internal_error'], 'table' => $query['table']];
        }

    }

    /******************************************************************************/
    /**
     * Actualiza uno o más registros existentes en la base de datos con soporte para validaciones y archivos.
     * * Este método gestiona el ciclo completo de una sentencia UPDATE: valida campos obligatorios,
     * verifica que los datos únicos no colisionen con otros registros (excluyendo el actual),
     * procesa la subida de nuevos archivos, codifica datos sensibles y construye la cláusula
     * WHERE de forma dinámica basándose en los parámetros proporcionados.
     *
     * @param array $query Configuración de la actualización (data, where, table, Post, required, unique, files, etc.).
     * @param mixed $DBConn Instancia de conexión a la base de datos.
     * @param bool $showQuery Si es true, retorna la cadena SQL sin ejecutar la actualización.
     * @param bool $novalidate Si es true, omite la limpieza/sanitización de los datos del Post.
     * @return array Retorna un arreglo con los resultados.
     *
	 * @example
	 * ```php
	 * //Formato de la query
     * $query = [
     *   'data'      => 'usuario,idEstado,email,Nombre,Rut,password', -> Datos a insertar, dejar fuera los archivos
     *   'required'  => 'email,Nombre,Rut',                           -> Datos obligatorios a insertar, son validados, si no existen impide la ejecucion
     *   'unique'    => 'email,Nombre-Rut',                           -> Datos unicos, se consulta en la BD que el dato ingresado no este repetido
     *   'encode'    => 'password',                                   -> Datos a codificar
     *   'table'     => 'usuarios_listado',                           -> Tabla donde se ejecuta la consulta
     *   'Post'      => $_POST,                                       -> Datos $_POST entregados
     *   'files'     => [                                             -> Arreglo con los archivos, cada uno va dentro de su propio array
     *     [
     *      'Identificador' => 'Direccion_img',                                       -> Columna dentro de la BD, identificador del archivo
     *      'SubCarpeta'    => '',                                                    -> Opcional, si el archivo se guarda en una subcarpeta
     *      'NombreArchivo' => '',                                                    -> Se se utiliza un nombre particular, sino, se utiliza el sufijo
     *      'SufijoArchivo' => 'Sufijo_',                                             -> Si al nombre del archivo se le pone un sufijo
     *      'ValidarTipo'   => 'word,excel,powerpoint,pdf,image,txt,zip,video,music', -> Formato archivo a validar
     *      'ValidarPeso'   => 10,                                                    -> Validacion peso maximo del archivo (en megas)
     *      'Base64'        => true                                                   -> true-false ->Si el archivo es entregado como texto (base64), esto hace que se ignoren todas las validaciones
     *     ],
     *   ]
     * ];
     *
     * //ejecucion
     * $qbuilder->queryUpdate($query, $DBConn);
	 * ```
	 *
     */
    public function queryUpdate(array $query, $DBConn, bool $showQuery = false, bool $novalidate = false){

        /*************** Validaciones ***************/
        // Verifica que se haya definido la tabla principal de la consulta
        if(!isset($query['table']) || $query['table']==''){ return ['status' => false, 'error' => 'Query Error: No hay datos en $table', 'data' => []];}
        // Valida la presencia de datos en los campos marcados como requeridos
        if(isset($query['required'])&&$query['required']!=''){
            $dataVal  = $this->validateRequired($query['required'], $query['Post']);
            if ($dataVal['status'] !== true) {return ['status' => false, 'error' => $dataVal['error'], 'data' => [], 'table' => $query['table']];}
        }

        // El campo 'where' es mandatorio para evitar actualizaciones accidentales de toda la tabla
        $dataWhere = $this->validateRequired($query['where'], $query['Post']);
        if ($dataWhere['status'] !== true) {return ['status' => false, 'error' => $dataWhere['error'], 'data' => [], 'table' => $query['table']];}

        // Verifica unicidad ignorando el registro actual definido en el 'where'
        if(isset($query['unique'])&&$query['unique']!=''){
            $dataUniq = $this->validateUnique($query['unique'], $query['table'], $query['Post'], $query['where'], $DBConn);
            if ($dataUniq['status'] !== true) {return ['status' => false, 'error' => $dataUniq['error'], 'data' => [], 'table' => $query['table']];}
        }

        /*************** Datos    ***************/
        // Fracciona las cadenas de texto de campos y condiciones en arreglos manejables
        $arrData  = $this->CommonData->parseDataCommas($query['data']);
        $arrWhere = $this->CommonData->parseDataCommas($query['where']);

        /*************** Archivos   ***************/
        // Procesa la actualización de archivos (reemplazo o carga nueva)
        $fileProc  = $this->processFiles($query, 'update');
        if ($fileProc['success'] === false) {return ['status' => false, 'error' => $fileProc['error'], 'data' => [], 'table' => $query['table']];}
        // Fragmento SQL generado para la sección SET relacionado con archivos
        $FilesData = $fileProc['update'];

        /*************** Codificacion  ***************/
        // Ejecuta la transformación/encriptación de los campos definidos
        $Post = $this->encodeFormData($query);

        /*************** Generacion Datos ***************/
        $matrixData  = [];
        $matrixWhere = [];

        // Construye los pares columna='valor' para la cláusula SET
        foreach ($arrData as $data) {
            if (!empty($Post[$data])) {
                $matrixData[] = "`".$data."`='".($novalidate ? $Post[$data] : $this->clearData($Post[$data]))."'";
            }
        }

        // Construye las condiciones para la cláusula WHERE unidas por AND
        foreach ($arrWhere as $where) {
            if (!empty($Post[$where])) {
                $matrixWhere[] = $where." = '".($novalidate ? $Post[$where] : $this->clearData($Post[$where]))."'";
            }
        }

        // Formatea los arreglos en strings finales para la consulta
        $DataColumn = $matrixData ? implode(', ', $matrixData) : '';
        $DataWhere  = $matrixWhere ? implode(' AND ', $matrixWhere) : '';

        /*************** Generacion Query ***************/
        // Ensambla la sentencia UPDATE completa
        $ActionSQL = 'UPDATE '.$query['table'].' SET '.$DataColumn.$FilesData.' WHERE '.$DataWhere;

        /*************** Ejecutar   ***************/
        // Retorna el SQL generado si se solicita el modo de depuración
        if ($showQuery) {
            return ['status' => false, 'error' => '', 'data' => $ActionSQL, 'table' => $query['table']];
        }

        // Ejecución de la transacción
        try {
            $result = $this->queryExecute($ActionSQL, $DBConn);
            // Obtiene el conjunto completo de resultados obtenidos
            if($result['status']){
                // Estructura estándar de respuesta
                $response = [
                    'status'  => true,
                    'data'    => $result['data']
                ];
            }else{
                // Estructura estándar de respuesta
                $response = [
                    'status'  => false,
                    'error'   => $result['error'],
                    'data'    => $result['internal_error'],
                    'table'   => $query['table'],
                ];
            }
            // Retorna los datos
            return $response;
        } catch (Exception $e) {
            // Se listan los errores
            $Error = $this->logError($ActionSQL, $e);
            // Registra el fallo y retorna la información del error procesada
            return ['status' => false, 'error' => $Error['error'], 'data' => $Error['internal_error'], 'table' => $query['table']];
        }

    }

    /******************************************************************************/
    /**
     * Elimina un registro de la base de datos y sus archivos físicos asociados.
     * * Este método realiza una eliminación integral: primero identifica y remueve los
     * archivos del servidor vinculados al registro (usando FileManager) y luego
     * ejecuta la sentencia SQL DELETE. Requiere que los identificadores en el
     * parámetro 'where' existan en el arreglo 'Post' para filtrar la operación.
     *
     * @param array $query Configuración de la eliminación (table, where, Post, files, SubCarpeta).
     * @param mixed $DBConn Instancia de conexión a la base de datos.
     * @param bool $showQuery Si es true, retorna la cadena SQL sin ejecutar la eliminación.
     * @return array Retorna un arreglo con los resultados.
     *
	 * @example
	 * ```php
	 * //Formato de la query
     * $query = [
     *   'files'       => 'Direccion_img',    -> Nombre del archivo dentro de la base de datos
     *   'table'       => 'usuarios_listado', -> Tabla donde esta el dato
     *   'where'       => 'idUsuario',        -> Dato del where, es validado con los datos $_POST
     *   'SubCarpeta'  => '',                 -> Si el archivo esta dentro de una subcarpeta
     *   'Post'        => $_POST              -> Datos $_POST
     * ];
     *
     * //ejecucion
     * $qbuilder->queryDelete($query, $DBConn);
	 * ```
	 *
     */
    public function queryDelete(array $query, $DBConn, bool $showQuery = false){

        /*************** Validaciones ***************/
        // Valida que se haya especificado la tabla de destino
        if(!isset($query['table']) || $query['table']==''){ return ['status' => false, 'error' => 'Query Error: No hay datos en $table', 'data' => []];}
        // Valida que se haya definido el campo de condición para la eliminación
        if(!isset($query['where']) || $query['where']==''){ return ['status' => false, 'error' => 'Query Error: No hay datos en $where', 'data' => [], 'table' => $query['table']];}

        // Verifica que los valores necesarios para el WHERE estén presentes en los datos recibidos (Post)
        $dataVal  = $this->validateRequired($query['where'], $query['Post']);
        if ($dataVal['status'] !== true) {return ['status' => false, 'error' => $dataVal['error'], 'data' => [], 'table' => $query['table']];}

        /*************** Datos    ***************/
        // Fracciona los campos del WHERE en un arreglo indexado
        $arrWhere = $this->CommonData->parseDataCommas($query['where']);

        /******************************************/
        // Lógica para la eliminación de archivos físicos antes de borrar el registro de la BD
        if(isset($query['files'])&&$query['files']!=''){

            $matrixColumn = [];
            // Construye la condición de búsqueda para localizar las rutas de archivos en la BD
            foreach ($arrWhere as $where) {
                if (!empty($query['Post'][$where])) {
                    // Desencripta y limpia el identificador recibido para la consulta de búsqueda
                    $matrixColumn[] = $where." = '".$this->clearData($this->Codification->encryptDecrypt('decrypt', $query['Post'][$where]))."'";
                }
            }
            $DataColumn = $matrixColumn ? implode(', ', $matrixColumn) : '';

            // Prepara una sub-consulta para obtener los nombres de archivos actuales del registro
            $queryRow = [
                'data'   => $query['files'],
                'table'  => $query['table'],
                'join'   => '',
                'where'  => $DataColumn,
                'group'  => '',
                'having' => '',
                'order'  => ''
            ];
            // Recupera la fila con la información de los archivos
            $result = $this->queryRow($queryRow, $DBConn);

            // Invoca al gestor de archivos para borrar los recursos del almacenamiento físico
            $delFile  = $this->FileManager->deleteFilesMassive($query['files'], $query['SubCarpeta'], $result);
            if ($delFile !== true) {return ['status' => false, 'error' => $delFile, 'data' => [], 'table' => $query['table']];}
        }

        /*************** Generacion Datos ***************/
        $matrixWhere = [];
        // Reconstruye la cláusula WHERE final para la sentencia DELETE
        foreach ($arrWhere as $where) {
            if (!empty($query['Post'][$where])) {
                // Aplica desencriptación y sanitización a los valores de condición
                $matrixWhere[] = $where." = '".$this->clearData($this->Codification->encryptDecrypt('decrypt', $query['Post'][$where]))."'";
            }
        }
        // Une las condiciones con el operador lógico AND
        $DataWhere = $matrixWhere ? implode(' AND ', $matrixWhere) : '';

        /*************** Generacion Query ***************/
        // Ensambla la sentencia DELETE FROM
        $ActionSQL = 'DELETE FROM '.$query['table'].' WHERE '.$DataWhere;

        /*************** Ejecutar   ***************/
        // Retorna la cadena SQL si se ha solicitado el modo de visualización
        if ($showQuery) {
            return ['status' => false, 'error' => '', 'data' => $ActionSQL, 'table' => $query['table']];
        }

        // Ejecución de la eliminación en la base de datos
        try {
            $result = $this->queryExecute($ActionSQL, $DBConn);
            // Obtiene el conjunto completo de resultados obtenidos
            if($result['status']){
                // Estructura estándar de respuesta
                $response = [
                    'status'  => true,
                    'data'    => $result['data']
                ];
            }else{
                // Estructura estándar de respuesta
                $response = [
                    'status'  => false,
                    'error'   => $result['error'],
                    'data'    => $result['internal_error'],
                    'table'   => $query['table'],
                ];
            }
            // Retorna los datos
            return $response;
        } catch (Exception $e) {
            // Se listan los errores
            $Error = $this->logError($ActionSQL, $e);
            // Registra el fallo y retorna la información del error procesada
            return ['status' => false, 'error' => $Error['error'], 'data' => $Error['internal_error'], 'table' => $query['table']];
        }

    }

    /******************************************************************************/
    /**
     * Ejecuta una sentencia SQL directamente en la base de datos.
     * * Este método actúa como la capa final de ejecución del Query Builder. Determina
     * automáticamente si la consulta es de lectura (SELECT, SHOW, etc.) para devolver
     * un conjunto de resultados, o de escritura (INSERT, UPDATE, DELETE) para devolver
     * el número de filas afectadas.
     *
     * @param string $query Sentencia SQL completa a ejecutar.
     * @param mixed $DBConn Instancia de conexión a la base de datos (compatible con PDO).
     * @param bool $showQuery Si es true, retorna la cadena SQL sin ejecutarla.
     * @param bool $singleRow Si es true o false, indica si debe traer una fila o muchas.
     * @return array Retorna un arreglo con los resultados.
     *
	 * @example
	 * ```php
	 * //Formato de la query
     * $query = 'SELECT * FROM Test';
     *
     * //ejecucion
     * $qbuilder->queryExecute($query, $DBConn);
	 * ```
	 *
     */
    public function queryExecute(string $query, $DBConn, bool $showQuery = false, bool $singleRow = false){

        /*************** Validaciones ***************/
        // Verifica que la cadena de la consulta no esté vacía o nula
        if(!isset($query) || $query==''){
            return [
                'status' => false,
                'error'  => 'Query Error: No hay datos en $query'
            ];
        }

        /*************** Ejecutar   ***************/
        // Retorna el texto de la consulta si se activó el modo de previsualización
        if ($showQuery) {
            return [
                'status' => true,
                'query'  => $query
            ];
        }

        // Intento de ejecución de la sentencia mediante sentencias preparadas
        try {
            // Determina si la consulta requiere el retorno de datos (SELECT) mediante expresiones regulares
            $isSelect = (bool) preg_match('/^\s*(SELECT|SHOW|DESCRIBE|EXPLAIN)\s/i', $query);

            // Prepara la sentencia en el motor de base de datos
            $stmt = $DBConn->prepare($query);

            // Ejecuta la sentencia preparada
            $stmt->execute();

            // Evalúa el tipo de respuesta: fetchAll para consultas de lectura, rowCount para escritura
            $data = $isSelect
                    ? (
                        $singleRow
                            ? $stmt->fetch(PDO::FETCH_ASSOC)
                            : $stmt->fetchAll(PDO::FETCH_ASSOC)
                    )
                    : $stmt->rowCount();

            // Se retornan los datos
            return [
                'status' => true,
                'data'   => $data
            ];
        } catch (Exception $e) {
            // Se listan los errores
            $Error = $this->logError($query, $e);
            // En caso de error, delega el registro al método logError
            return [
                'status'          => false,
                'error'           => $Error['error'],
                'internal_error'  => $Error['internal_error'],
                'query'           => $query
            ];
        }

    }

    /******************************************************************************/
    /**
     * Elimina archivos físicos y limpia sus referencias en el registro de la base de datos.
     * * A diferencia de queryDelete, este método no elimina el registro completo de la tabla,
     * sino que borra los archivos del servidor y actualiza las columnas correspondientes
     * del registro a un valor vacío (''). Utiliza FileManager para la eliminación física
     * y ejecuta una sentencia UPDATE para reflejar los cambios en la BD.
     *
     * @param array $query Configuración de la operación (files, table, where, SubCarpeta, Post).
     * @param mixed $DBConn Instancia de conexión a la base de datos.
     * @return array Retorna un arreglo con los resultados.
     *
	 * @example
	 * ```php
	 * //Formato de la query
     * $query = [
     *   'files'       => 'Direccion_img',    -> Nombre del archivo dentro de la base de datos
     *   'table'       => 'usuarios_listado', -> Tabla donde esta el dato
     *   'where'       => 'idUsuario',        -> Dato del where, es validado con los datos $_POST
     *   'SubCarpeta'  => '',                 -> Si el archivo esta dentro de una subcarpeta
     *   'Post'        => $_POST              -> Datos $_POST
     * ];
     *
     * //ejecucion
     * $qbuilder->delFiles($query, $DBConn);
	 * ```
	 *
     */
    public function delFiles(array $query, $DBConn){

        /*************** Validaciones ***************/
        // Valida la existencia de los parámetros críticos para identificar columnas y tablas
        if(!isset($query['table']) || $query['table']==''){ return ['status' => false, 'error' => 'Query Error: No hay datos en $table', 'data' => []];}
        // Valida que se haya definido el listado de archivos a eliminar
        if(!isset($query['files']) || $query['files']==''){ return ['status' => false, 'error' => 'Query Error: No hay datos en $files', 'data' => [], 'table' => $query['table']];}
        // Valida que se haya definido el campo de condición para la eliminación
        if(!isset($query['where']) || $query['where']==''){ return ['status' => false, 'error' => 'Query Error: No hay datos en $where', 'data' => [], 'table' => $query['table']];}

        // Verifica que los campos definidos en el 'where' estén presentes en el arreglo 'Post'
        $dataVal  = $this->validateRequired($query['where'], $query['Post']);
        if ($dataVal['status'] !== true) {return ['status' => false, 'error' => $dataVal['error'], 'data' => [], 'table' => $query['table']];}

        /*************** Datos    ***************/
        // Convierte las cadenas de texto separadas por comas en arreglos indexados
        $arrWhere   = $this->CommonData->parseDataCommas($query['where']);
        $arrFiles   = $this->CommonData->parseDataCommas($query['files']);

        /*************** Generacion Datos ***************/
        $matrixData  = [];
        $matrixWhere = [];

        // Itera sobre los nombres de archivos para proceder con la eliminación física
        foreach ($arrFiles as $file) {
            // Verifica que el valor del archivo (ruta/nombre) exista en el Post
            if (!empty($query['Post'][$file])) {
                /******************************************/
                // Solicita al gestor de archivos la eliminación del recurso en el disco
                $delFile  = $this->FileManager->deleteFile($query['Post'][$file], $query['SubCarpeta']);
                /******************************************/
                // Si falla la eliminación física, interrumpe el proceso y retorna el error
                if($delFile !== true){
                    return ['status' => false, 'error' => $delFile, 'data' => [], 'table' => $query['table']];
                }

                // Si el archivo se borró correctamente, prepara la columna para ser limpiada en la BD
                $matrixData[] = $file." = ''";
            }
        }

        // Construye la cláusula WHERE basándose en los identificadores proporcionados
        foreach ($arrWhere as $where) {
            if (!empty($query['Post'][$where])) {
                // Sanitiza los datos del filtro para la consulta SQL
                $matrixWhere[] = $where." = '".$this->clearData($query['Post'][$where])."'";
            }
        }

        // Formatea los elementos de los arreglos en cadenas SQL válidas
        $DataColumn = $matrixData ? implode(', ', $matrixData) : '';
        $DataWhere  = $matrixWhere ? implode(' AND ', $matrixWhere) : '';

        /*************** Generacion Query ***************/
        // Crea la sentencia UPDATE para establecer las columnas de archivos como cadenas vacías
        $ActionSQL = 'UPDATE '.$query['table'].' SET '.$DataColumn.' WHERE '.$DataWhere;

        /*************** Ejecutar   ***************/
        // Ejecuta la actualización en el servidor de base de datos
        try {
            $result = $this->queryExecute($ActionSQL, $DBConn);
            // Obtiene el conjunto completo de resultados obtenidos
            if($result['status']){
                // Estructura estándar de respuesta
                $response = [
                    'status'  => true,
                    'data'    => $result['data']
                ];
            }else{
                // Estructura estándar de respuesta
                $response = [
                    'status'  => false,
                    'error'   => $result['error'],
                    'data'    => $result['internal_error'],
                    'table'   => $query['table'],
                ];
            }
            // Retorna los datos
            return $response;
        } catch (Exception $e) {
            // Se listan los errores
            $Error = $this->logError($ActionSQL, $e);
            // Registra el fallo y retorna la información del error procesada
            return ['status' => false, 'error' => $Error['error'], 'data' => $Error['internal_error'], 'table' => $query['table']];
        }

    }

    /******************************************************************************/
    /******************************************************************************/
    /**
     * Crea una nueva tabla en la base de datos utilizando el motor InnoDB.
     * * Este método construye una sentencia SQL 'CREATE TABLE' con una configuración
     * predefinida que incluye el uso de índices BTREE para la clave primaria,
     * cotejamiento latin1 y formato de fila dinámico.
     *
     * @param array $query Configuración de la tabla (table, data, primaryKey, comentario).
     * @param mixed $DBConn Instancia de conexión a la base de datos.
     * @param bool $showQuery Si es true, retorna la sentencia SQL sin ejecutarla.
     * @return array Retorna un arreglo con los resultados.
     *
	 * @example
	 * ```php
	 * //Formato de la query
     * $query = [
     * 'table'      => 'usuarios_listado',                                    -> Tabla donde se ejecuta la consulta
     * 'data'       => '`idCorreosCat` int(10) unsigned NOT NULL AUTO_INCREMENT', -> Datos a crear
     * 'primaryKey' => 'idusuario',                                           -> Clave Primaria
     * 'comentario' => 'fija',                                                -> Comentario de la tabla
     * ];
     *
     * //ejecucion
     * $qbuilder->queryCreateTable($query, $DBConn);
	 * ```
	 *
     */
    public function queryCreateTable(array $query, $DBConn, bool $showQuery = false){

        /*************** Validaciones ***************/
        // Valida que el nombre de la tabla esté presente
        if(!isset($query['table']) || $query['table']==''){           return ['status' => false, 'error' => 'Query Error: No hay datos en $table',      'data' => []];}
        // Valida que se hayan definido las columnas y tipos de datos
        if(!isset($query['data']) || $query['data']==''){             return ['status' => false, 'error' => 'Query Error: No hay datos en $data',       'data' => [], 'table' => $query['table']];}
        // Valida la definición de la clave primaria obligatoria
        if(!isset($query['primaryKey']) || $query['primaryKey']==''){ return ['status' => false, 'error' => 'Query Error: No hay datos en $primaryKey', 'data' => [], 'table' => $query['table']];}

        /*************** Generacion Query ***************/
        // Construcción de la sentencia DDL (Data Definition Language) con parámetros fijos de motor y codificación
        $ActionSQL = 'CREATE TABLE `'.$query['table'].'` ('.$query['data'].', PRIMARY KEY (`'.$query['primaryKey'].'`) USING BTREE) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci COMMENT = \''.$query['comentario'].'\' ROW_FORMAT = DYNAMIC;';

        /*************** Ejecutar   ***************/
        // Retorna el string de la consulta si se solicita previsualización
        if ($showQuery) {
            return ['status' => false, 'error' => '', 'data' => $ActionSQL, 'table' => $query['table']];
        }

        // Intento de creación de la tabla
        try {
            // Ejecuta la sentencia a través del driver de conexión
            $result = $this->queryExecute($ActionSQL, $DBConn);
            // Obtiene el conjunto completo de resultados obtenidos
            if($result['status']){
                // Estructura estándar de respuesta
                $response = [
                    'status'  => true,
                    'data'    => $result['data']
                ];
            }else{
                // Estructura estándar de respuesta
                $response = [
                    'status'  => false,
                    'error'   => $result['error'],
                    'data'    => $result['internal_error'],
                    'table'   => $query['table'],
                ];
            }
            // Retorna el resultado de la operación
            return $response;
        } catch (Exception $e) {
            // Se listan los errores
            $Error = $this->logError($ActionSQL, $e);
            // Registra el fallo y retorna la información del error procesada
            return ['status' => false, 'error' => $Error['error'], 'data' => $Error['internal_error'], 'table' => $query['table']];
        }

    }

    /******************************************************************************/
    /**
     * Elimina de forma permanente una tabla de la base de datos.
     * * Este método construye y ejecuta una sentencia SQL 'DROP TABLE'. Utiliza la
     * cláusula 'IF EXISTS' para prevenir errores en la ejecución en caso de que
     * la tabla no se encuentre en el esquema actual.
     *
     * @param array $query Arreglo asociativo que debe contener la clave 'table'.
     * @param mixed $DBConn Recurso o instancia de conexión a la base de datos.
     * @param bool $showQuery Si es true, retorna la sentencia SQL sin ejecutarla.
     * @return array Retorna un arreglo con los resultados.
     *
	 * @example
	 * ```php
	 * //Formato de la query
     * $query = [
     *   'table' => 'usuarios_listado', -> Tabla donde se ejecuta la consulta
     * ];
     *
     * //ejecucion
     * $qbuilder->queryDropTable($query, $DBConn);
	 * ```
	 *
     */
    public function queryDropTable(array $query, $DBConn, bool $showQuery = false){

        /*************** Validaciones ***************/
        // Verifica que el nombre de la tabla objetivo haya sido proporcionado
        if(!isset($query['table']) || $query['table']==''){ return ['status' => false, 'error' => 'Query Error: No hay datos en $table', 'data' => []];}

        /*************** Generacion Query ***************/
        // Construye la sentencia DDL. Se incluyen backticks para proteger nombres de tabla con caracteres especiales o reservados
        $ActionSQL = 'DROP TABLE IF EXISTS `'.$query['table'].'`;';

        /*************** Ejecutar   ***************/
        // Retorna la cadena de la consulta si se solicita el modo de previsualización
        if ($showQuery) {
            return ['status' => false, 'error' => '', 'data' => $ActionSQL, 'table' => $query['table']];
        }

        // Intento de ejecución de la eliminación de la estructura
        try {
            // Ejecuta la sentencia mediante el método central de ejecución
            $result = $this->queryExecute($ActionSQL, $DBConn);
            // Obtiene el conjunto completo de resultados obtenidos
            if($result['status']){
                // Estructura estándar de respuesta
                $response = [
                    'status'  => true,
                    'data'    => $result['data']
                ];
            }else{
                // Estructura estándar de respuesta
                $response = [
                    'status'  => false,
                    'error'   => $result['error'],
                    'data'    => $result['internal_error'],
                    'table'   => $query['table'],
                ];
            }
            // Retorna los datos
            return $response;
        } catch (Exception $e) {
            // Se listan los errores
            $Error = $this->logError($ActionSQL, $e);
            // Registra el fallo y retorna la información del error procesada
            return ['status' => false, 'error' => $Error['error'], 'data' => $Error['internal_error'], 'table' => $query['table']];
        }

    }


    /******************************************************************************/
    /******************************************************************************/
    /**
     * Crea una nueva base de datos en el servidor de base de datos especificado.
     * * Este método establece una conexión inicial al servidor (sin seleccionar una base
     * de datos específica) para ejecutar la sentencia DDL 'CREATE DATABASE'. Incluye
     * validaciones de seguridad para el nombre de la base de datos, configuración de
     * charset/collation por defecto y un manejo detallado de excepciones de PDO.
     *
     * @param array $query Configuración de la base de datos (dbName, charset, collation).
     * @param array $DBConn Credenciales y parámetros de conexión al servidor (HOSTNAME, USERNAME, PASSWORD, PORT, CHARSET).
     * @param bool $showQuery Si es true, retorna la sentencia SQL sin ejecutarla.
     * @return array Retorna un arreglo con los resultados.
     *
	 * @example
	 * ```php
	 * //Formato de la query
     * $query = [
     *  'dbName'    => 'Nombre_db',          -> Nombre de la base de datos
     *  'charset'   => 'utf8mb4',            -> Charset (opcional)
     *  'collation' => 'utf8mb4_unicode_ci', -> Collation (opcional)
     * ];
     *
     * //ejecucion
     * $qbuilder->createDatabase($query, $DBConn);
	 * ```
	 *
     */
    public function createDatabase(array $query, array $DBConn, bool $showQuery = false){

        /*************** Validaciones ***************/
        // Valida que el nombre de la base de datos no sea nulo o vacío
        if(!isset($query['dbName']) || $query['dbName']==''){          return ['status' => false, 'error' => 'Query Error: No hay datos en $dbName',          'data' => []];}
        // Aplica una expresión regular para restringir el nombre a caracteres alfanuméricos y guiones bajos (3-64 caracteres)
        if (!preg_match('/^[A-Za-z0-9_]{3,64}$/', $query['dbName'])) { return ['status' => false, 'error' => 'Query Error: Nombre de base de datos inválido', 'data' => []];}


        // Configura valores predeterminados para la codificación si no se proporcionan
        $charset      = $query['charset'] ?? 'utf8mb4';
        $collation    = $query['collation'] ?? 'utf8mb4_unicode_ci';

        // Mapea las variables de conexión del servidor desde el arreglo $DBConn
        $BD_host      = $DBConn['HOSTNAME'];
        $BD_username  = $DBConn['USERNAME'];
        $BD_password  = $DBConn['PASSWORD'];
        $BD_port      = $DBConn['PORT'] ?? 3306;
        $BD_charset   = $DBConn['CHARSET'] ?? 'utf8mb4';

        /*************** Ejecutar   ***************/
        try {

            /*************** Conexión al servidor ***************/
            // Inicia una conexión genérica al host (sin dbname) para permitir la creación de un nuevo esquema
            $NewDBConn = new DB\SQL(
                'mysql:host='.$BD_host.';port='.$BD_port.';charset='.$BD_charset,
                $BD_username,
                $BD_password,
                array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8;')
            );

            /*************** Generacion Query ***************/
            // Prepara la sentencia SQL formateando el nombre, charset y collation de forma segura
            $ActionSQL = sprintf(
                "CREATE DATABASE `%s` CHARACTER SET %s COLLATE %s",
                $query['dbName'],
                $charset,
                $collation
            );

            /*************** Ejecutar   ***************/
            // Retorna la cadena de la consulta si se solicita el modo de previsualización
            if ($showQuery) {
                return ['status' => false, 'error' => '', 'data' => $ActionSQL, 'table' => $query['table']];
            }

            // Ejecución interna de la sentencia SQL
            try {
                $result = $this->queryExecute($ActionSQL, $NewDBConn);
                // Obtiene el conjunto completo de resultados obtenidos
                if($result['status']){
                    // Estructura estándar de respuesta
                    $response = [
                        'status'  => true,
                        'data'    => $result['data']
                    ];
                }else{
                    // Estructura estándar de respuesta
                    $response = [
                        'status'  => false,
                        'error'   => $result['error'],
                        'data'    => $result['internal_error'],
                        'table'   => $query['dbName'],
                    ];
                }
                // Retorna los datos
                return $response;
            }  catch (PDOException $e) {
                // Se listan los errores
                $Error = $this->logError($ActionSQL, $e);
                // Registra el fallo y retorna la información del error procesada
                return ['status' => false, 'error' => $Error['error'], 'data' => $Error['internal_error'], 'table' => $query['dbName']];
            }

        } catch (\PDOException $e) {
            // Bloque de captura para errores específicos de conexión o permisos de nivel superior
            $message = $e->getMessage();

            // Evalúa códigos de error estándar de MySQL (1044: Acceso denegado, 1045: Usuario/Pass inválido)
            if (str_contains($message, '1044') || str_contains($message, '1045')) {
                return ['status' => false, 'error' => 'Query Error: El usuario no tiene permisos para crear bases de datos', 'data' => [], 'table' => $query['dbName']];
            }

            // Evalúa código de error 1007: La base de datos ya existe
            if (str_contains($message, '1007')) {
                return ['status' => false, 'error' => 'Query Error: La base de datos ya existe', 'data' => [], 'table' => $query['dbName']];
            }

            // Retorna el mensaje de error genérico si no coincide con los casos anteriores
            return ['status' => false, 'error' => 'Query Error: '.$message, 'data' => [], 'table' => $query['dbName']];
        }

    }

    /******************************************************************************/
    /**
     * Procesa y ejecuta el contenido de un archivo SQL externo en la base de datos.
     * * Este método lee un archivo físico, elimina comentarios de tipo línea (-- ) y de bloque (/* *\/),
     * fragmenta el contenido en sentencias individuales utilizando el punto y coma (;) como
     * delimitador y ejecuta cada instrucción de forma secuencial.
     *
     * @param string $filepath Ruta física completa hacia el archivo .sql.
     * @param mixed $DBConn Instancia de conexión a la base de datos.
     * @return array Retorna un arreglo con los resultados.
     * @throws Exception Si el archivo no es accesible o la sintaxis SQL es inválida.
     *
	 * @example
	 * ```php
	 * //ejecucion
     * $qbuilder->executeFile($filepath, $DBConn);
	 * ```
	 *
     */
    public function executeFile(string $filepath, $DBConn){

        /*************** Validaciones ***************/
        // Verifica que se haya proporcionado una ruta de archivo
        if(!isset($filepath) || $filepath==''){ return ['status' => false, 'error' => 'Query Error: No hay datos en $filepath', 'data' => []];}

        // Inicializa la utilidad de validación para comprobar la integridad de la ruta
        $DataSQL = new FunctionsDataSQL();
        $result  = $DataSQL->validatePathFile($filepath);

        // Valida la existencia física del archivo antes de intentar leerlo
        if($result['success']===false){ return ['status' => false, 'error' => 'Query Error: Archivo SQL no encontrado:'.$filepath, 'data' => []];}

        /*************** Ejecutar   ***************/
        try {
            // Carga el contenido completo del archivo SQL en una cadena de texto
            $sql = file_get_contents($filepath);

            /*************** Limpieza de SQL ***************/
            // Elimina comentarios de una sola línea (estilo --) mediante expresiones regulares
            $sql = preg_replace('/--.*$/m', '', $sql);
            // Elimina comentarios multilínea o de bloque (estilo /* */)
            $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

            /*************** Segmentación ***************/
            // Divide el script en un arreglo de consultas individuales usando el punto y coma como separador
            // Se limpian espacios en blanco y se filtran elementos vacíos del arreglo resultante
            $queries = array_filter(
                array_map('trim', explode(';', $sql)),
                function ($query) {
                    return !empty($query);
                }
            );

            /*************** Procesamiento ***************/
            // Itera sobre el conjunto de consultas validadas
            foreach ($queries as $query) {
                // Ejecuta cada instrucción de forma independiente a través del método central queryExecute
                if (!empty(trim($query))) {
                    $this->queryExecute($query, $DBConn);
                }
            }

            // Retorna true indicando que el script finalizó sin errores críticos
            return ['status' => true, 'query' => $query, 'data' => []];
        } catch (PDOException $e) {
            // Se listan los errores
            $Error = $this->logError('', $e, $filepath);
            // Registra el fallo y retorna la información del error procesada
            return ['status' => false, 'error' => $Error['error'], 'data' => $Error['internal_error']];
        }
    }


    /*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos Auxiliares                                             */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
    /******************************************************************************/
    /**
     * Valida la presencia de datos obligatorios en un conjunto de entradas (Post).
     * * Este método recorre una lista de campos definidos como requeridos y verifica
     * si existen en el arreglo de datos proporcionado y si contienen un valor no vacío.
     * Es una pieza fundamental para la integridad de datos en operaciones de inserción
     * y actualización.
     *
     * @param string $SIS_data Cadena de texto con los nombres de campos separados por comas (ej: "nombre,email,password").
     * @param array $SIS_Post Arreglo asociativo con los datos a validar (normalmente $_POST o un JSON decodificado).
     * @return array Retorna true si todos los campos son válidos, o un arreglo con los mensajes de error encontrados.
     *
	 * @example
	 * ```php
	 * $campos    = "usuario,password";
     * $datos     = ["usuario" => "admin", "password" => ""];
     * $resultado = $this->validateRequired($campos, $datos);
     * // Retorna: [["message" => "password es obligatorio"]]
	 * ```
	 *
     */
    private function validateRequired(string $SIS_data, array $SIS_Post): bool|array{

        /*************** Preparación ***************/
        // Descompone la cadena de campos separados por comas en un arreglo manejable
        $arrData = $this->CommonData->parseDataCommas($SIS_data);
        // Inicializa el contenedor de errores
        $errors  = '';

        /*************** Validación ***************/
        // Itera sobre cada campo marcado como obligatorio
        foreach ($arrData as $field) {
            // Regla de validación:
            // 1. isset($SIS_Post[$field]): El campo debe existir en el arreglo.
            // 2. empty($SIS_Post[$field]): El valor no debe ser nulo, falso, cadena vacía o 0.
            if(isset($SIS_Post[$field]) && empty($SIS_Post[$field])){
                // Si el campo existe pero su valor es considerado "vacío" por PHP
                $errors .= $field.' es obligatorio';
            }
        }

        /*************** Resultado ***************/
        // Retorna true si el arreglo de errores permanece vacío; de lo contrario, devuelve los errores
        if(empty($errors)){
            return ['status' => true];
        }else{
            return ['status' => false, 'error' => $errors];
        }

    }
    /******************************************************************************/
    /**
     * Valida la unicidad de los datos en una tabla antes de realizar una inserción o actualización.
     * * Este método verifica que los valores proporcionados no existan ya en la base de datos,
     * permitiendo validaciones simples (un solo campo) o compuestas (grupos de campos mediante
     * el separador "-"). En caso de actualización, excluye automáticamente el registro actual
     * de la búsqueda para evitar falsos positivos.
     *
     * @param string $SIS_Data Campos a validar (ej: "email" o "rut-digito").
     * @param string $SIS_Table Nombre de la tabla donde se realizará la consulta.
     * @param array $SIS_Post Arreglo de datos de entrada (generalmente $_POST).
     * @param string $SIS_Where Campos que identifican el registro actual (para exclusión en UPDATE).
     * @param mixed $DBConn Instancia de conexión a la base de datos.
     * @return array Retorna true si los datos son únicos, o un arreglo con mensajes de error.
     */
    private function validateUnique(string $SIS_Data, string $SIS_Table, array $SIS_Post, string $SIS_Where, $DBConn): bool|array{

        /******************************************/
        // Preparación de variables iniciales
        $arrData   = $this->CommonData->parseDataCommas($SIS_Data); // Campos a validar (separados por comas)
        $subWhere  = ''; // Cláusula WHERE base (usada para exclusión en actualizaciones)
        $errors    = '';

        /******************************************/
        /**
         * Lógica de Exclusión para UPDATE
         * Si se proporciona $SIS_Where, significa que estamos editando un registro.
         * Debemos asegurar que la búsqueda de "duplicados" no nos encuentre a nosotros mismos.
         */
        if (!empty($SIS_Where)) {
            $parts    = [];
            $arrWhere = $this->CommonData->parseDataCommas($SIS_Where);
            foreach ($arrWhere as $field) {
                // Se agrega la condición: campo != 'valor_actual'
                if (isset($SIS_Post[$field]) && $SIS_Post[$field] != '') {
                    $parts[] = $field . " != '" . $this->clearData($SIS_Post[$field]) . "'";
                }
            }
            $subWhere .= $parts ? implode(' AND ', $parts) : '';
        }

        /******************************************/
        /**
         * Procesamiento de Reglas de Unicidad
         * Recorre cada regla definida en $SIS_Data.
         */
        foreach ($arrData as $data) {
            $DataInternal  = '';
            $whereInternal = $subWhere;

            /******************************************/
            /**
             * CASO A: Validación Compuesta (Subgrupos con "-")
             * Ejemplo: "sucursal-codigo" verifica que la combinación de ambos sea única.
             */
            if (strpos($data, "-")){
                $parts_data  = [];
                $parts_where = [];
                $arrData2 = $this->CommonData->parseDataSeparator($data); // Separación por guiones

                foreach ($arrData2 as $field) {
                    // Si el campo viene en el POST
                    if (isset($SIS_Post[$field]) && $SIS_Post[$field] != '') {
                        $parts_data[]  = $field;
                        $parts_where[] = $field . " = '" . $this->clearData($SIS_Post[$field]) . "'";

                    // Si es una condición directa (ej: "estado=1")
                    } elseif (strpos($field, "=") || strpos($field, "!=")) {
                        $arrData3      = $this->CommonData->parseDataSymbol($field);
                        $parts_data[]  = $arrData3[0];
                        $parts_where[] = $field;
                    }
                }
                // Separacion de datos
                $x_data  = $parts_data ? implode(',', $parts_data) : '';
                $x_where = $parts_where ? implode(' AND ', $parts_where) : '';
                // Si existen datos
                if($x_data != ''){
                    $DataInternal  = $x_data;
                    $whereInternal = ($whereInternal != '') ? $whereInternal . ' AND ' . $x_where : $x_where;
                }

            /******************************************/
            /**
             * CASO B: Validación Simple
             * Ejemplo: "email" verifica que el correo no esté registrado.
             */
            } else {
                if (isset($SIS_Post[$data]) && $SIS_Post[$data] != '') {
                    $DataInternal  = $data;
                    $condicion     = $data . " = '" . $this->clearData($SIS_Post[$data]) . "'";
                    $whereInternal = ($whereInternal != '') ? $whereInternal . ' AND ' . $condicion : $condicion;
                }
            }

            /******************************************/
            /**
             * Ejecución de la Verificación
             * Si se construyó una consulta válida, se cuenta cuántos registros coinciden.
             */
            if($DataInternal != ''){
                // Se genera la query
                $query = [
                    'data'  => $DataInternal,
                    'table' => $SIS_Table,
                    'where' => $whereInternal
                ];

                // Llama a queryNRows para obtener el total de coincidencias
                $ndata = $this->queryNRows($query, $DBConn);

                // Si el conteo es mayor a 0, existe una colisión de datos
                if ($ndata['status'] && $ndata['data'] > 0){
                    $errors .= 'Los datos que intenta ingresar ya existen en el Sistema';
                }

            }
        }

        // Retorna true si es único, o el arreglo de errores si hubo duplicados
        if(empty($errors)){
            return ['status' => true];
        }else{
            return ['status' => false, 'error' => $errors];
        }
    }
    /******************************************************************************/
    /**
     * Sanitiza y prepara una cadena de texto para su uso seguro en sentencias SQL.
     * * Este método actúa como una capa de limpieza para prevenir ataques de
     * inyección SQL básicos y errores de sintaxis causados por caracteres
     * especiales (como comillas). Aplica una serie de transformaciones
     * secuenciales para asegurar la integridad de la cadena antes de ser
     * concatenada en una consulta.
     *
     * @param string $Data Cadena de texto cruda (proveniente de $_POST, $_GET, etc.).
     * @return string Cadena procesada y escapada lista para SQL.
     *
	 * @example
	 * ```php
	 * $input  = " O'Connor ";
     * $limpio = $this->clearData($input);
     * // Resultado: "O\'Connor" (Listo para: INSERT INTO tabla VALUES ('O\'Connor'))
	 * ```
	 *
     */
    private function clearData(string $Data): string{

        // Elimina espacios en blanco accidentales al inicio y al final de la cadena
        $Data = trim($Data);

        // Elimina barras invertidas (\) para evitar el doble escapado si magic_quotes estuviera activo
        $Data = stripslashes($Data);

        // Escapa caracteres que podrían romper la consulta SQL (comillas simples, dobles, barras y NUL)
        // Agrega una barra invertida antes de estos caracteres para que el motor SQL los trate como texto
        $Data = addslashes($Data);

        return $Data;
    }
    /******************************************************************************/
    /**
     * Construye una sentencia SQL SELECT completa a partir de un arreglo asociativo.
     * Este método actúa como el motor de ensamblaje de la clase, concatenando
     * las diversas partes de una consulta (SELECT, FROM, JOIN, WHERE, etc.)
     * siguiendo el orden sintáctico estándar de SQL.
     *
     * @param array $query Arreglo con las partes de la consulta (data, table, join, where, group, having, order, limit).
     * @return string Sentencia SQL concatenada y lista para ser ejecutada o procesada.
     *
	 * @example
	 * ```php
	 * $query = [
     *  'data'  => '*',
     *  'table' => 'usuarios',
     *  'where' => 'id = 1'
     * ];
     * $sql = $this->createQuery($query);
     * // Resultado: "SELECT * FROM `usuarios` WHERE id = 1"
	 * ```
	 *
     */
    private function createQuery(array $query): string{

        /*************** Construcción Base ***************/
        // Define las columnas a seleccionar
        $ActionSQL = 'SELECT '.$query['data'];
        // Define la tabla de origen (usando backticks para seguridad en nombres de tabla)
        $ActionSQL.= ' FROM `'.$query['table'].'`';

        /*************** Cláusulas Opcionales ***************/
        /**
         * Mapeo de Cláusulas:
         * Se recorre un arreglo de configuración que define el orden lógico de SQL.
         * Si la llave existe en el input $query y no está vacía, se concatena.
         */
        $clauses = [
            'join'   => ' ',          // Los JOIN usualmente ya incluyen su palabra clave en el string
            'where'  => ' WHERE ',
            'group'  => ' GROUP BY ',
            'having' => ' HAVING ',
            'order'  => ' ORDER BY ',
            'limit'  => ' LIMIT '
        ];

        foreach ($clauses as $key => $prefix) {
            if (!empty($query[$key])) {
                $ActionSQL .= $prefix . $query[$key];
            }
        }

        return $ActionSQL;
    }
    /******************************************************************************/
    /**
     * Registra de forma segura los errores de base de datos en los logs del servidor.
     * * Este método actúa como un cortafuegos de seguridad: captura la consulta SQL
     * fallida y el mensaje técnico de la excepción para guardarlos en el log de
     * errores de PHP (invisible para el usuario), mientras retorna un mensaje
     * genérico y estandarizado al cliente para evitar la exposición de la
     * estructura de la base de datos (prevención de Information Leakage).
     *
     * @param string $sql La sentencia SQL que originó el error.
     * @param Exception|PDOException $exception El objeto de excepción capturado.
     * @return array Mensajes de error genérico y real para mostrar en la interfaz.
     *
	 * @example
	 * ```php
	 * try {
     *  // ... código ...
     * } catch (Exception $e) {
     *  return $this->logError($sql, $e);
     * }
	 * ```
	 *
     */
    private function logError($sql, $exception, $filepath = null) {

        /*************** Generacion Error ***************/
        // Se obtiene el error
        $error = $filepath!=''
                ? 'QueryBuilder Error: '.$filepath
                : 'QueryBuilder Error: [' . $exception->getMessage() . '] SQL: ' . $sql;

        /*************** Registro Interno ***************/
        // Escribe en el archivo de registro de errores del servidor (ej. error_log de Apache/Nginx)
        // Incluye el mensaje técnico detallado y la query exacta para diagnóstico del desarrollador.
        error_log($error);

        /*************** Respuesta Segura ***************/
        // Retorna un array con dos datos:
        // - Cadena simple que no da pistas sobre nombres de tablas, columnas o credenciales.
        // - Cadena simple con el error Real.
        return [
            'error'          => 'Query Error: Se produjo un error al procesar la solicitud.',
            'internal_error' => $error,
        ];

    }
    /******************************************************************************/
    /**
     * Centraliza la lógica de validación y carga de archivos para las operaciones de base de datos.
     * * Este método actúa como un puente entre el Query Builder y el FileManager.
     * Detecta si los archivos se envían mediante el arreglo global $_FILES o como
     * cadenas Base64 en el $_POST, gestionando las diferencias sintácticas entre
     * una inserción (INSERT) y una actualización (UPDATE).
     *
     * @param array $query Configuración que contiene la definición de 'files' y el 'Post'.
     * @param string $action Define el contexto de la operación: 'insert' o 'update'.
     * @return array Arreglo con el estado del proceso y los fragmentos SQL generados (nombres, archivos o update).
     */
    private function processFiles($query, $action = 'insert') {

        /*************** Inicialización ***************/
        // Estructura de retorno por defecto (caso donde no hay archivos que procesar)
        $result = ['success' => true, 'nombres' => '', 'archivos' => '', 'update' => ''];

        // Si no se definieron archivos en la configuración, termina el proceso prematuramente
        if (empty($query['files'])) {return $result;}

        /*************** Detección de Archivos ***************/
        // Cuenta cuántos de los identificadores de archivo definidos están presentes en el Post (útil para Base64)
        $CountFileExist = array_reduce(
            $query['files'],
            function($count, $archivo) use ($query) {
                return $count + (!empty($query['Post'][$archivo['Identificador']]) ? 1 : 0);
            },
            0
        );

        // Solo procede si hay archivos en el buffer de subida de PHP ($_FILES) o datos de archivo en el Post
        if (!empty($_FILES) || $CountFileExist != 0) {
            $isUpdate = ($action === 'update');

            /*************** Validación Física ***************/
            // Llama al FileManager para verificar tipos permitidos, pesos máximos y existencia
            $dataFiles = $isUpdate
                ? $this->FileManager->validateFiles($_FILES, $query['files'], $query['Post'])
                : $this->FileManager->validateFiles($_FILES, $query['files']);

            // Si la validación falla, retorna el error para detener la operación principal (Insert/Update)
            if ($dataFiles['success'] !== true) {
                $result['success'] = $dataFiles['success'];
                $result['error']   = $dataFiles['message'];
                return $result;
            }

            /*************** Carga (Upload) ***************/
            // Ejecuta el movimiento de archivos al servidor y genera los fragmentos de SQL
            $newFileName = $isUpdate
                ? $this->FileManager->uploadFile($_FILES, $query['files'], $query['Post'])
                : $this->FileManager->uploadFile($_FILES, $query['files']);

            /*************** Formateo de Resultados ***************/
            // Distribuye los fragmentos SQL según el tipo de consulta
            if ($action === 'insert') {
                // Genera: , columna1, columna2  y  , 'ruta1', 'ruta2'
                $result['nombres']  = $newFileName['Nombres'];
                $result['archivos'] = $newFileName['Archivos'];
            } else {
                // Genera: , columna1 = 'ruta1', columna2 = 'ruta2'
                $result['update'] = $newFileName['Update'];
            }
        }
        // Se retorna respuesta
        return $result;
    }
    /******************************************************************************/
    /**
     * Aplica algoritmos de cifrado a campos específicos del formulario antes de su almacenamiento.
     * * Este método actúa como una capa de seguridad intermedia que protege datos sensibles
     * (como contraseñas o folios) transformándolos en cadenas cifradas mediante una clave
     * maestra definida en la configuración del sistema (ConfigToken).
     *
     * @param array $query Configuración que contiene la clave 'encode' (campos a cifrar) y 'Post' (datos).
     * @return array Retorna el arreglo de datos del Post con los campos seleccionados ya cifrados.
     *
	 * @example
	 * ```php
	 * $query = [
     * 'encode' => 'password,pin_seguridad',
     *   'Post'   => ['usuario' => 'admin', 'password' => '123456']
     * ];
     * $datosCifrados = $this->encodeFormData($query);
     * // Resultado: ['usuario' => 'admin', 'password' => 'T3xToCifrad0...']
	 * ```
	 *
     */
    private function encodeFormData($query) {

        /*************** Validación de Requerimientos ***************/
        // Si no se definieron campos para codificar, retorna el Post original de inmediato
        if (!empty($query['encode'])){

            /*************** Preparación ***************/
            // Descompone la lista de campos separados por comas en un arreglo
            $arrEncode = $this->CommonData->parseDataCommas($query['encode']);

            /*************** Proceso de Cifrado ***************/
            // Itera sobre los campos marcados para cifrado
            foreach ($arrEncode as $data) {
                // Verifica que el campo exista en el Post y no sea una cadena vacía
                if(isset($query['Post'][$data]) && $query['Post'][$data] != ''){
                    /**
                     * Invocación al motor de codificación:
                     * - Modo: 'encrypt'
                     * - Valor: El dato original enviado por el usuario.
                     * - Key: Utiliza una constante global de configuración para asegurar la persistencia del cifrado.
                     */
                    $query['Post'][$data] = $this->Codification->encryptDecrypt(
                        'encrypt',
                        $query['Post'][$data],
                        ConfigToken::ENCODE_KEYS["KEY_1"]
                    );
                }
            }
        }

        /*************** Resultado ***************/
        // Retorna el arreglo POST modificado (o el original si no hubo campos a cifrar)
        return $query['Post'];
    }

}


