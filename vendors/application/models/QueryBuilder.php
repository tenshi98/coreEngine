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
    //Se consulta por solo un dato
    public function queryRow(array $query, $DBConn, bool $showQuery = false){
        /*
		*=================================================     Detalles    =================================================
		*
		* Permite seleccionar una fila de la base de datos
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//Formato de la query
        *    $query = [
        *        'data'   => 'data1,data2,data3',   -> Ver opciones select
        *        'table'  => 'data_table',
        *        'join'   => '',                    -> Ver opciones de los join
        *        'where'  => 'data1 = 1',           -> Ver modos alternativos where
        *        'group'  => '',                    -> Ver agrupaciones
        *        'having' => '',
        *        'order'  => 'data1 DESC'
        *    ];
        *
		* 	//ejecucion
		* 	$qbuilder->queryRow($query, $DBConn);
		*
		*=================================================    Parametros   =================================================
		* @input   array  $query       Arreglo con la consulta
		* @input   array  $DBConn      Arreglo con la configuracion de base de datos
		* @input   array  $showQuery   Variable opcional con la opcion de visualizacion de la consulta
		* @return  string|array
		*===================================================================================================================
		*/

        /*************** Validaciones ***************/
        //Se verifica si hay datos
        if(!isset($query['data']) || $query['data']==''){   return 'Query Error: No hay datos en $data'; }
        if(!isset($query['table']) || $query['table']==''){ return 'Query Error: No hay datos en $table'; }

        /*************** Generacion Query ***************/
        //armado de la query
        $ActionSQL = $this->createQuery($query);
        $ActionSQL.= ' LIMIT 1';

        /***************   Ejecutar   ***************/
        //Verifico si se pide mostrar la consulta
        if ($showQuery) {
            return $ActionSQL;
        }
        //Ejecucion
        try {
            $result = $this->queryExecute($ActionSQL, $DBConn);
            return (!empty($result)&&$result !== false) ? $result[0] : false;
        } catch (Exception $e) {
            return $this->logError($ActionSQL, $e);
        }

    }

    /******************************************************************************/
    //Se consulta por el numero de coincidencias
    public function queryNRows(array $query, $DBConn, bool $showQuery = false){
        /*
		*=================================================     Detalles    =================================================
		*
		* Se consulta por el numero de coincidencias
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//Formato de la query
        *    $query = [
        *        'data'   => 'data1,data2,data3',   -> Ver opciones select
        *        'table'  => 'data_table',
        *        'join'   => '',                    -> Ver opciones de los join
        *        'where'  => 'data1 = 1',           -> Ver modos alternativos where
        *        'group'  => '',                    -> Ver agrupaciones
        *        'having' => '',
        *        'order'  => 'data1 DESC'
        *    ];
        *
		* 	//ejecucion
		* 	$qbuilder->queryNRows($query, $DBConn);
		*
		*=================================================    Parametros   =================================================
		* @input   array  $query       Arreglo con la consulta
		* @input   array  $DBConn      Arreglo con la configuracion de base de datos
		* @input   array  $showQuery   Variable opcional con la opcion de visualizacion de la consulta
		* @return  string|array
		*===================================================================================================================
		*/

        /*************** Validaciones ***************/
        //Se verifica si hay datos
        if(!isset($query['data']) || $query['data']==''){   return 'Query Error: No hay datos en $data'; }
        if(!isset($query['table']) || $query['table']==''){ return 'Query Error: No hay datos en $table'; }

        /*************** Generacion Query ***************/
        //Se construye el COUNT(*) primero para que $showQuery muestre la query real ejecutada
        $BaseSQL   = $this->createQuery($query);
        $ActionSQL = 'SELECT COUNT(*) AS _total FROM (' . $BaseSQL . ') AS _t';

        /***************   Ejecutar   ***************/
        //Verifico si se pide mostrar la consulta
        if ($showQuery) {
            return $ActionSQL;
        }
        //Ejecucion
        try {
            $result = $this->queryExecute($ActionSQL, $DBConn);
            //Si se ejecuta correctamente
            return (!empty($result)&&$result !== false) ? (int)$result[0]['_total'] : 0;
        } catch (Exception $e) {
            return $this->logError($ActionSQL, $e);
        }

    }

    /******************************************************************************/
    //Se consulta por un conjunto de datos
    public function queryArray(array $query, $DBConn, bool $showQuery = false){
        /*
		*=================================================     Detalles    =================================================
		*
		* Se consulta por un conjunto de datos
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//Formato de la query
        *    $query = [
        *        'data'   => 'data1,data2,data3',   -> Ver opciones select
        *        'table'  => 'data_table',
        *        'join'   => '',                    -> Ver opciones de los join
        *        'where'  => 'data1 = 1',           -> Ver modos alternativos where
        *        'group'  => '',                    -> Ver agrupaciones
        *        'having' => '',
        *        'order'  => 'data1 DESC',
        *        'limit'  => 60
        *    ];
        *
		* 	//ejecucion
		* 	$qbuilder->queryArray($query, $DBConn);
		*
		*=================================================    Parametros   =================================================
		* @input   array  $query       Arreglo con la consulta
		* @input   array  $DBConn      Arreglo con la configuracion de base de datos
		* @input   array  $showQuery   Variable opcional con la opcion de visualizacion de la consulta
		* @return  string|array
		*===================================================================================================================
		*/

        /*************** Validaciones ***************/
        //Se verifica si hay datos
        if(!isset($query['data']) || $query['data']==''){   return 'Query Error: No hay datos en $data'; }
        if(!isset($query['table']) || $query['table']==''){ return 'Query Error: No hay datos en $table'; }

        /*************** Generacion Query ***************/
        //armado de la query
        $ActionSQL = $this->createQuery($query);

        /***************   Ejecutar   ***************/
        //Verifico si se pide mostrar la consulta
        if ($showQuery) {
            return $ActionSQL;
        }
        //Ejecucion
        try {
            //Ejecuto la query
            $result = $this->queryExecute($ActionSQL, $DBConn);
            //Si se ejecuta correctamente
            return $result;
        } catch (Exception $e) {
            return $this->logError($ActionSQL, $e);
        }

    }

    /******************************************************************************/
    /******************************************************************************/
    //Se inserta nuevo registro
    public function queryInsert(array $query, $DBConn, bool $showQuery = false, bool $novalidate = false){
        /*
		*=================================================     Detalles    =================================================
		*
		* Se inserta nuevo registro en la base de datos
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//Formato de la query
        *    $query = [
        *        'data'      => 'usuario,idEstado,email,Nombre,Rut,password', -> Datos a insertar, dejar fuera los archivos
        *        'required'  => 'email,Nombre,Rut',                           -> Datos obligatorios a insertar, son validados, si no existen impide la ejecucion
        *        'unique'    => 'email,Nombre-Rut',                           -> Datos unicos, se consulta en la BD que el dato ingresado no este repetido
        *        'encode'    => 'password',                                   -> Datos a codificar
        *        'table'     => 'usuarios_listado',                           -> Tabla donde se ejecuta la consulta
        *        'Post'      => $_POST,                                       -> Datos $_POST entregados
        *        'files'     => [                                             -> Arreglo con los archivos, cada uno va dentro de su propio array
        *            [
        *                'Identificador' => 'Direccion_img',                                       -> Columna dentro de la BD, identificador del archivo
        *                'SubCarpeta'    => '',                                                    -> Opcional, si el archivo se guarda en una subcarpeta
        *                'NombreArchivo' => '',                                                    -> Se se utiliza un nombre particular, sino, se utiliza el sufijo
        *                'SufijoArchivo' => 'Sufijo_',                                             -> Si al nombre del archivo se le pone un sufijo
        *                'ValidarTipo'   => 'word,excel,powerpoint,pdf,image,txt,zip,video,music', -> Formato archivo a validar
        *                'ValidarPeso'   => 10,                                                    -> Validacion peso maximo del archivo (en megas)
        *                'Base64'        => true                                                   -> Si el archivo es entregado como texto (base64), esto hace que se ignoren todas las validaciones
        *            ],
        *        ]
        *    ];
        *
		* 	//ejecucion
		* 	$qbuilder->queryInsert($query, $DBConn);
		*
		*=================================================    Parametros   =================================================
		* @input   array  $query       Arreglo con la consulta
		* @input   array  $DBConn      Arreglo con la configuracion de base de datos
		* @input   array  $showQuery   Variable opcional con la opcion de visualizacion de la consulta
		* @input   array  $novalidate  Variable opcional con la opcion de validacion de los datos
		* @return  string|array
		*===================================================================================================================
		*/

        /*************** Validaciones ***************/
        //Validacion datos obligatorios
        if(isset($query['required'])&&$query['required']!=''){
            $dataVal  = $this->validateRequired($query['required'], $query['Post']);
            if ($dataVal !== true) {return $dataVal;}
        }
        //Validacion datos unicos
        if(isset($query['unique'])&&$query['unique']!=''){
            $dataUniq = $this->validateUnique($query['unique'], $query['table'], $query['Post'], '', $DBConn);
            if ($dataUniq !== true) {return $dataUniq;}
        }

        /***************    Datos    ***************/
        //Variables
        $arrData = $this->CommonData->parseDataCommas($query['data']); //Separacion por comas

        /***************   Archivos   ***************/
        //Subida de archivos - procesado por metodo auxiliar processFiles
        $fileProc      = $this->processFiles($query, 'insert');
        if ($fileProc['success'] === false) {return $fileProc['error'];}
        $DatosNombres  = $fileProc['nombres'];
        $DatosArchivos = $fileProc['archivos'];

        /***************  Codificacion  ***************/
        //Codificacion Datos - procesado por metodo auxiliar encodeFormData
        $Post = $this->encodeFormData($query);

        /***************   Guardar   ***************/
        //Variable vacia
        $matrixColumn = [];
        $matrixValue  = [];
        //Se recorren los datos separados
        foreach ($arrData as $data) {
            // Se verifican los datos del post
            if (!empty($Post[$data])) {
                $matrixColumn[] = $data;
                $matrixValue[]  = "'".(($novalidate ? $Post[$data] : $this->clearData($Post[$data])))."'";
            }
        }
        //Se crea cadena en base al arreglo, con su propia separacion
        $DataColumn = $matrixColumn ? implode(', ', $matrixColumn) : '';
        $DataValue  = $matrixValue ? implode(', ', $matrixValue) : '';

        /*************** Generacion Query ***************/
        //Se crea la consulta
        $ActionSQL = 'INSERT INTO '.$query['table'].' ('.$DataColumn.$DatosNombres.') VALUES ('.$DataValue.$DatosArchivos.')';

        /***************   Ejecutar   ***************/
        //Verifico si se pide mostrar la consulta
        if ($showQuery) {
            return $ActionSQL;
        }
        //Ejecucion
        try {
            //Ejecuto la query
            $result = $this->queryExecute($ActionSQL, $DBConn);
            //Si se ejecuta correctamente
            return ($result > 0) ? $DBConn->lastInsertId() : false;
        } catch (Exception $e) {
            return $this->logError($ActionSQL, $e);
        }

    }

    /******************************************************************************/
    //Se actualiza registro
    public function queryUpdate(array $query, $DBConn, bool $showQuery = false, bool $novalidate = false){
        /*
		*=================================================     Detalles    =================================================
		*
		* Se actualiza registro en la base de datos
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//Formato de la query
        *    $query = [
        *        'data'      => 'usuario,idEstado,email,Nombre,Rut,password', -> Datos a insertar, dejar fuera los archivos
        *        'required'  => 'email,Nombre,Rut',                           -> Datos obligatorios a insertar, son validados, si no existen impide la ejecucion
        *        'unique'    => 'email,Nombre-Rut',                           -> Datos unicos, se consulta en la BD que el dato ingresado no este repetido
        *        'encode'    => 'password',                                   -> Datos a codificar
        *        'table'     => 'usuarios_listado',                           -> Tabla donde se ejecuta la consulta
        *        'Post'      => $_POST,                                       -> Datos $_POST entregados
        *        'files'     => [                                             -> Arreglo con los archivos, cada uno va dentro de su propio array
        *            [
        *                'Identificador' => 'Direccion_img',                                       -> Columna dentro de la BD, identificador del archivo
        *                'SubCarpeta'    => '',                                                    -> Opcional, si el archivo se guarda en una subcarpeta
        *                'NombreArchivo' => '',                                                    -> Se se utiliza un nombre particular, sino, se utiliza el sufijo
        *                'SufijoArchivo' => 'Sufijo_',                                             -> Si al nombre del archivo se le pone un sufijo
        *                'ValidarTipo'   => 'word,excel,powerpoint,pdf,image,txt,zip,video,music', -> Formato archivo a validar
        *                'ValidarPeso'   => 10,                                                    -> Validacion peso maximo del archivo (en megas)
        *                'Base64'        => true                                                   -> true-false ->Si el archivo es entregado como texto (base64), esto hace que se ignoren todas las validaciones
        *            ],
        *        ]
        *    ];
        *
		* 	//ejecucion
		* 	$qbuilder->queryUpdate($query, $DBConn);
		*
		*=================================================    Parametros   =================================================
		* @input   array  $query       Arreglo con la consulta
		* @input   array  $DBConn      Arreglo con la configuracion de base de datos
		* @input   array  $showQuery   Variable opcional con la opcion de visualizacion de la consulta
		* @input   array  $novalidate  Variable opcional con la opcion de validacion de los datos
		* @return  string|array
		*===================================================================================================================
		*/

        /*************** Validaciones ***************/
        //Validacion datos obligatorios
        if(isset($query['required'])&&$query['required']!=''){
            $dataVal  = $this->validateRequired($query['required'], $query['Post']);
            if ($dataVal !== true) {return $dataVal;}
        }
        //Validacion campo where (siempre obligatorio en un UPDATE)
        $dataWhere = $this->validateRequired($query['where'], $query['Post']);
        if ($dataWhere !== true) {return $dataWhere;}
        //Validacion datos unicos
        if(isset($query['unique'])&&$query['unique']!=''){
            $dataUniq = $this->validateUnique($query['unique'], $query['table'], $query['Post'], $query['where'], $DBConn);
            if ($dataUniq !== true) {return $dataUniq;}
        }

        /***************    Datos    ***************/
        //Variables
        $arrData  = $this->CommonData->parseDataCommas($query['data']);  //Separacion por comas
        $arrWhere = $this->CommonData->parseDataCommas($query['where']); //Separacion por comas

        /***************   Archivos   ***************/
        //Subida de archivos - procesado por metodo auxiliar processFiles
        $fileProc  = $this->processFiles($query, 'update');
        if ($fileProc['success'] === false) {return $fileProc['error'];}
        $FilesData = $fileProc['update'];

        /***************  Codificacion  ***************/
        //Codificacion Datos - procesado por metodo auxiliar encodeFormData
        $Post = $this->encodeFormData($query);

        /*************** Generacion Datos ***************/
        //Variable vacia
        $matrixData  = [];
        $matrixWhere = [];
        //Se recorren los datos a actualizar
        foreach ($arrData as $data) {
            // Se verifican los datos del post
            if (!empty($Post[$data])) {
                $matrixData[] = "`".$data."`='".($novalidate ? $Post[$data] : $this->clearData($Post[$data]))."'";
            }
        }
        //Se recorren los datos del where
        foreach ($arrWhere as $where) {
            // Se verifican los datos del post
            if (!empty($Post[$where])) {
                $matrixWhere[] = $where." = '".($novalidate ? $Post[$where] : $this->clearData($Post[$where]))."'";
            }
        }
        //Se crea cadena en base al arreglo, con su propia separacion
        $DataColumn = $matrixData ? implode(', ', $matrixData) : '';
        $DataWhere  = $matrixWhere ? implode(' AND ', $matrixWhere) : '';

        /*************** Generacion Query ***************/
        //Se crea la consulta
        $ActionSQL = 'UPDATE '.$query['table'].' SET '.$DataColumn.$FilesData.' WHERE '.$DataWhere;

        /***************   Ejecutar   ***************/
        //Verifico si se pide mostrar la consulta
        if ($showQuery) {
            return $ActionSQL;
        }
        //Ejecucion
        try {
            //Ejecuto la query
            $this->queryExecute($ActionSQL, $DBConn);
            //Siempre devuelve true
            return true;
        } catch (Exception $e) {
            return $this->logError($ActionSQL, $e);
        }

    }

    /******************************************************************************/
    //Se elimina dato
    public function queryDelete(array $query, $DBConn, bool $showQuery = false){
        /*
		*=================================================     Detalles    =================================================
		*
		* Se elimina dato en la base de datos, eliminando los archivos relacionados
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//Formato de la query
        *    $query = [
        *        'files'       => 'Direccion_img',    -> Nombre del archivo dentro de la base de datos
        *        'table'       => 'usuarios_listado', -> Tabla donde esta el dato
        *        'where'       => 'idUsuario',        -> Dato del where, es validado con los datos $_POST
        *        'SubCarpeta'  => '',                 -> Si el archivo esta dentro de una subcarpeta
        *        'Post'        => $_POST              -> Datos $_POST
        *    ];
        *
		* 	//ejecucion
		* 	$qbuilder->queryDelete($query, $DBConn);
		*
		*=================================================    Parametros   =================================================
		* @input   array  $query       Arreglo con la consulta
		* @input   array  $DBConn      Arreglo con la configuracion de base de datos
		* @input   array  $showQuery   Variable opcional con la opcion de visualizacion de la consulta
		* @return  string|array
		*===================================================================================================================
		*/

        /*************** Validaciones ***************/
        //Se verifica si hay datos
        if(!isset($query['table']) || $query['table']==''){ return 'Query Error: No hay datos en $table'; }
        if(!isset($query['where']) || $query['where']==''){ return 'Query Error: No hay datos en $where'; }
        //Validacion datos obligatorios
        $dataVal  = $this->validateRequired($query['where'], $query['Post']);
        if ($dataVal !== true) {return $dataVal;}

        /***************    Datos    ***************/
        //Variables
        $arrWhere = $this->CommonData->parseDataCommas($query['where']); //Separacion por comas

        /******************************************/
        //Se eliminan los archivos en caso de existir
        if(isset($query['files'])&&$query['files']!=''){
            /******************************************/
            //Variable vacia
            $matrixColumn = [];
            //Se recorren los datos separados
            foreach ($arrWhere as $where) {
                // Se verifican los datos del post
                if (!empty($query['Post'][$where])) {
                    $matrixColumn[] = $where." = '".$this->clearData($this->Codification->encryptDecrypt('decrypt', $query['Post'][$where]))."'";
                }
            }
            //Se crea cadena en base al arreglo, con su propia separacion
            $DataColumn = $matrixColumn ? implode(', ', $matrixColumn) : '';

            /******************************************/
            //Se genera la query
            $queryRow = [
                'data'   => $query['files'],
                'table'  => $query['table'],
                'join'   => '',
                'where'  => $DataColumn,
                'group'  => '',
                'having' => '',
                'order'  => ''
            ];
            //Ejecuto la query
            $result = $this->queryRow($queryRow, $DBConn);

            /******************************************/
            //Se eliminan los archivos en caso de existir
            $delFile  = $this->FileManager->deleteFilesMassive($query['files'], $query['SubCarpeta'], $result);
            if ($delFile !== true) {return $delFile;}
        }

        /*************** Generacion Datos ***************/
        //Variable vacia
        $matrixWhere = [];
        //Se recorren los datos separados
        foreach ($arrWhere as $where) {
            // Se verifican los datos del post
            if (!empty($query['Post'][$where])) {
                $matrixWhere[] = $where." = '".$this->clearData($this->Codification->encryptDecrypt('decrypt', $query['Post'][$where]))."'";
            }
        }
        //Se agregan los datos al where
        $DataWhere = $matrixWhere ? implode(' AND ', $matrixWhere) : '';

        /*************** Generacion Query ***************/
        //Se crea la consulta
        $ActionSQL = 'DELETE FROM '.$query['table'].' WHERE '.$DataWhere;

        /***************   Ejecutar   ***************/
        //Verifico si se pide mostrar la consulta
        if ($showQuery) {
            return $ActionSQL;
        }
        //Ejecucion
        try {
            //Ejecuto la query
            $result = $this->queryExecute($ActionSQL, $DBConn);
            //Si se ejecuta correctamente
            return true;
        } catch (Exception $e) {
            return $this->logError($ActionSQL, $e);
        }

    }

    /******************************************************************************/
    //Se ejecuta la query
    public function queryExecute(string $query, $DBConn, bool $showQuery = false){
        /*
		*=================================================     Detalles    =================================================
		*
		* Se ejecuta la query
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//Formato de la query
        *    $query = 'SELECT * FROM Test';
        *
		* 	//ejecucion
		* 	$qbuilder->queryExecute($query, $DBConn);
		*
		*=================================================    Parametros   =================================================
		* @input   array  $query       Arreglo con la consulta
		* @input   array  $DBConn      Arreglo con la configuracion de base de datos
		* @input   array  $showQuery   Variable opcional con la opcion de visualizacion de la consulta
		* @return  string|array
		*===================================================================================================================
		*/

        /*************** Validaciones ***************/
        //Se verifica si hay datos
        if(!isset($query) || $query==''){ return 'Query Error: No hay datos en $query'; }

        /***************   Ejecutar   ***************/
        //Verifico si se pide mostrar la consulta
        if ($showQuery) {
            return $query;
        }
        //Ejecucion
        try {
            //FIX: exec() no retorna resultados para SELECT, se usa prepare()+fetchAll()
            $isSelect = (bool) preg_match('/^\s*(SELECT|SHOW|DESCRIBE|EXPLAIN)\s/i', $query);
            $stmt = $DBConn->prepare($query);
            $stmt->execute();
            //Si se ejecuta correctamente
            return $isSelect ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->rowCount();
        } catch (Exception $e) {
            return $this->logError($query, $e);
        }

    }

    /******************************************************************************/
    //Se elimina archivo
    public function delFiles(array $query, $DBConn){
        /*
		*=================================================     Detalles    =================================================
		*
		* Se elimina archivo fisicamente, actualiza el registro en la base de datos
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//Formato de la query
        *    $query = [
        *        'files'       => 'Direccion_img',    -> Nombre del archivo dentro de la base de datos
        *        'table'       => 'usuarios_listado', -> Tabla donde esta el dato
        *        'where'       => 'idUsuario',        -> Dato del where, es validado con los datos $_POST
        *        'SubCarpeta'  => '',                 -> Si el archivo esta dentro de una subcarpeta
        *        'Post'        => $_POST              -> Datos $_POST
        *    ];
        *
		* 	//ejecucion
		* 	$qbuilder->delFiles($query, $DBConn);
		*
		*=================================================    Parametros   =================================================
		* @input   array  $query       Arreglo con la consulta
		* @input   array  $DBConn      Arreglo con la configuracion de base de datos
		* @return  string|array
		*===================================================================================================================
		*/

        /*************** Validaciones ***************/
        //Se verifica si hay datos
        if(!isset($query['files']) || $query['files']==''){ return 'Query Error: No hay datos en $files'; }
        if(!isset($query['table']) || $query['table']==''){ return 'Query Error: No hay datos en $table'; }
        if(!isset($query['where']) || $query['where']==''){ return 'Query Error: No hay datos en $where'; }
        //Validacion datos obligatorios
        $dataVal  = $this->validateRequired($query['where'], $query['Post']);
        if ($dataVal !== true) {return $dataVal;}

        /***************    Datos    ***************/
        //Separo los datos
        $arrWhere   = $this->CommonData->parseDataCommas($query['where']); //Separacion por comas
        $arrFiles   = $this->CommonData->parseDataCommas($query['files']); //Separacion por comas

        /*************** Generacion Datos ***************/
        //Variable vacia
        $matrixData  = [];
        $matrixWhere = [];
        //Se recorren los datos separados
        foreach ($arrFiles as $file) {
            // Se verifican los datos del post
            if (!empty($query['Post'][$file])) {
                /******************************************/
                //Se eliminan los archivos en caso de existir
                $delFile  = $this->FileManager->deleteFile($query['Post'][$file], $query['SubCarpeta']);
                /******************************************/
                //Si se ejecuta correctamente
                if($delFile === true){
                    $matrixData[] = $file." = ''";
                }else{
                    return $delFile;
                }
            }
        }
        //Se recorren los datos separados
        foreach ($arrWhere as $where) {
            // Se verifican los datos del post
            if (!empty($query['Post'][$where])) {
                $matrixWhere[] = $where." = '".$this->clearData($query['Post'][$where])."'";
            }
        }
        //Se crea cadena en base al arreglo, con su propia separacion
        $DataColumn = $matrixData ? implode(', ', $matrixData) : '';
        $DataWhere  = $matrixWhere ? implode(' AND ', $matrixWhere) : '';

        /*************** Generacion Query ***************/
        //Se crea la consulta
        $ActionSQL = 'UPDATE '.$query['table'].' SET '.$DataColumn.' WHERE '.$DataWhere;

        /***************   Ejecutar   ***************/
        //Ejecuto la query
        try {
            $this->queryExecute($ActionSQL, $DBConn);
        } catch (Exception $e) {
            return $this->logError($ActionSQL, $e);
        }

        /******************************************/
        //Siempre devuelve true
        return true;

    }

    /******************************************************************************/
    /******************************************************************************/
    //Permite la creacion de una tabla en la base de datos
    public function queryCreateTable(array $query, $DBConn, bool $showQuery = false){
        /*
		*=================================================     Detalles    =================================================
		*
		* Permite la creacion de una tabla en la base de datos
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//Formato de la query
        *    $query = [
        *        'table'      => 'usuarios_listado',                                    -> Tabla donde se ejecuta la consulta
        *        'data'       => '`idCorreosCat` int UNSIGNED NOT NULL AUTO_INCREMENT', -> Datos a crear
        *        'primaryKey' => 'idusuario',                                           -> Clave Primaria
        *        'comentario' => 'fija',                                                -> Comentario de la tabla
        *    ];
        *
		* 	//ejecucion
		* 	$qbuilder->queryCreateTable($query, $DBConn);
		*
		*=================================================    Parametros   =================================================
		* @input   array  $query       Arreglo con la consulta
		* @input   array  $DBConn      Arreglo con la configuracion de base de datos
		* @input   array  $showQuery   Variable opcional con la opcion de visualizacion de la consulta
		* @return  string|array
		*===================================================================================================================
		*/

        /*************** Validaciones ***************/
        //Se verifica si hay datos
        if(!isset($query['table']) || $query['table']==''){           return 'Query Error: No hay datos en $table'; }
        if(!isset($query['data']) || $query['data']==''){             return 'Query Error: No hay datos en $data'; }
        if(!isset($query['primaryKey']) || $query['primaryKey']==''){ return 'Query Error: No hay datos en $primaryKey'; }

        /*************** Generacion Query ***************/
        //armado de la query
        $ActionSQL = 'CREATE TABLE `'.$query['table'].'` ('.$query['data'].', PRIMARY KEY (`'.$query['primaryKey'].'`) USING BTREE) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci COMMENT = \''.$query['comentario'].'\' ROW_FORMAT = DYNAMIC;';

        /***************   Ejecutar   ***************/
        //Verifico si se pide mostrar la consulta
        if ($showQuery) {
            return $ActionSQL;
        }
        //Ejecucion
        try {
            //Ejecuto la query
            $result = $this->queryExecute($ActionSQL, $DBConn);
            //Si se ejecuta correctamente
            return $result;
        } catch (Exception $e) {
            return $this->logError($ActionSQL, $e);
        }

    }

    /******************************************************************************/
    //Permite la eliminacion de una tabla en la base de datos
    public function queryDropTable(array $query, $DBConn, bool $showQuery = false){
        /*
		*=================================================     Detalles    =================================================
		*
		* Permite la eliminacion de una tabla en la base de datos
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//Formato de la query
        *    $query = [
        *        'table' => 'usuarios_listado', -> Tabla donde se ejecuta la consulta
        *    ];
        *
		* 	//ejecucion
		* 	$qbuilder->queryDropTable($query, $DBConn);
		*
		*=================================================    Parametros   =================================================
		* @input   array  $query       Arreglo con la consulta
		* @input   array  $DBConn      Arreglo con la configuracion de base de datos
		* @input   array  $showQuery   Variable opcional con la opcion de visualizacion de la consulta
		* @return  string|array
		*===================================================================================================================
		*/

        /*************** Validaciones ***************/
        //Se verifica si hay datos
        if(!isset($query['table']) || $query['table']==''){ return 'Query Error: No hay datos en $table'; }

        /*************** Generacion Query ***************/
        //armado de la query
        $ActionSQL = 'DROP TABLE IF EXISTS `'.$query['table'].'`;';

        /***************   Ejecutar   ***************/
        //Verifico si se pide mostrar la consulta
        if ($showQuery) {
            return $ActionSQL;
        }
        //Ejecucion
        try {
            //Ejecuto la query
            $result = $this->queryExecute($ActionSQL, $DBConn);
            //Si se ejecuta correctamente
            return $result;
        } catch (Exception $e) {
            return $this->logError($ActionSQL, $e);
        }

    }


    /******************************************************************************/
    /******************************************************************************/
    //Crear una base de datos
    public function createDatabase(array $query, array $DBConn, bool $showQuery = false){
        /*
		*=================================================     Detalles    =================================================
		*
		* Crear una base de datos
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	 //Formato de la query
        *    $query = [
        *        'dbName'    => 'Nombre_db',          -> Nombre de la base de datos
        *        'charset'   => 'utf8mb4',            -> Charset (opcional)
        *        'collation' => 'utf8mb4_unicode_ci', -> Collation (opcional)
        *    ];
        *
		* 	//ejecucion
		* 	$qbuilder->createDatabase($query, $DBConn);
		*
		*=================================================    Parametros   =================================================
        * @input array $query['dbName']     Nombre de la base de datos
        * @input array $query['charset']    Charset (opcional)
        * @input array $query['collation']  Collation (opcional)
        * @return bool True si se creó exitosamente
        * @throws PDOException Si falla la creación
		*===================================================================================================================
		*/

        /*************** Validaciones ***************/
        //Se verifica si hay datos
        if(!isset($query['dbName']) || $query['dbName']==''){          return 'Query Error: No hay datos en $dbName'; }
        if (!preg_match('/^[A-Za-z0-9_]{3,64}$/', $query['dbName'])) { return 'Query Error: Nombre de base de datos inválido'; }
        //Verificacion datos por defecto
        $charset      = $query['charset'] ?? 'utf8mb4';
        $collation    = $query['collation'] ?? 'utf8mb4_unicode_ci';
        $BD_host      = $DBConn['HOSTNAME'];
        $BD_username  = $DBConn['USERNAME'];
        $BD_password  = $DBConn['PASSWORD'];
        $BD_port      = $DBConn['PORT'] ?? 3306;
        $BD_charset   = $DBConn['CHARSET'] ?? 'utf8mb4';

        /***************   Ejecutar   ***************/
        try {

            /*************** Conexión al servidor ***************/
            $NewDBConn = new DB\SQL(
                'mysql:host='.$BD_host.';port='.$BD_port.';charset='.$BD_charset,
                $BD_username,
                $BD_password,
                array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8;')
            );

            /*************** Generacion Query ***************/
            //armado de la query
            $ActionSQL = sprintf(
                "CREATE DATABASE `%s` CHARACTER SET %s COLLATE %s",
                $query['dbName'],
                $charset,
                $collation
            );

            /***************   Ejecutar   ***************/
            //Verifico si se pide mostrar la consulta
            if ($showQuery) {
                return $ActionSQL;
            }
            //Ejecucion
            try {
                //Ejecuto la query
                $this->queryExecute($ActionSQL, $NewDBConn);
                //Si se ejecuta correctamente
                return true;
            }  catch (PDOException $e) {
                return $this->logError($ActionSQL, $e);
            }

        } catch (\PDOException $e) {

            $message = $e->getMessage();

            // Permiso denegado
            if (str_contains($message, '1044') || str_contains($message, '1045')) {
                return 'Query Error: El usuario no tiene permisos para crear bases de datos';
            }

            // Base ya existe
            if (str_contains($message, '1007')) {
                return 'Query Error: La base de datos ya existe';
            }

            return 'Query Error: '.$message;
        }

    }

    /******************************************************************************/
    //Ejecutar un archivo SQL
    public function executeFile(string $filepath, $DBConn){
        /*
		*=================================================     Detalles    =================================================
		*
		* Ejecutar un archivo SQL
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//ejecucion
		* 	$qbuilder->executeFile($filepath, $DBConn);
		*
		*=================================================    Parametros   =================================================
        * @input string $filepath Ruta del archivo SQL
        * @return bool True si se ejecutó exitosamente
        * @throws Exception Si el archivo no existe o falla la ejecución
		*===================================================================================================================
		*/

        /*************** Validaciones ***************/
        //Se verifica si hay datos
        if(!isset($filepath) || $filepath==''){ return 'Query Error: No hay datos en $filepath'; }
        //Se inicializa la libreria de validacion
        $DataValidations = new FunctionsDataValidations();
        $result          = $DataValidations->validatePathFile($filepath);
        //Se verifica si el archivo existe
        if($result['success']===false){ return 'Query Error: Archivo SQL no encontrado:'.$filepath; }

        /***************   Ejecutar   ***************/
        //Ejecucion
        try {
            //Se obtiene el contenido del archivo
            $sql = file_get_contents($filepath);

            // Dividir el archivo en consultas individuales
            // Eliminar comentarios SQL
            $sql = preg_replace('/--.*$/m', '', $sql);
            $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

            // Dividir por punto y coma
            $queries = array_filter(
                array_map('trim', explode(';', $sql)),
                function ($query) {
                    return !empty($query);
                }
            );

            // Ejecutar cada consulta
            foreach ($queries as $query) {
                if (!empty(trim($query))) {
                    $this->queryExecute($query, $DBConn);
                }
            }

            //Siempre devuelve true
            return true;
        } catch (PDOException $e) {
            return $this->logError($filepath, $e);
        }
    }


    /*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos Auxiliares                                             */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
    /******************************************************************************/
    private function validateRequired(string $SIS_data, array $SIS_Post): bool|array{
        //Variables
        $arrData = $this->CommonData->parseDataCommas($SIS_data); //Separacion por comas
        $errors  = [];
        //Recorro
        foreach ($arrData as $field) {
            //verifico si existe el dato y verifico si esta vacio
            if(isset($SIS_Post[$field]) && empty($SIS_Post[$field])){
                $errors[] = ["message" => "$field es obligatorio"];
            }
        }
        //si no hay errores
        return (empty($errors)) ? true : $errors;

    }
    /******************************************************************************/
    private function validateUnique(string $SIS_Data, string $SIS_Table, array $SIS_Post, string $SIS_Where, $DBConn): bool|array{

        /******************************************/
        //Variables
        $arrData   = $this->CommonData->parseDataCommas($SIS_Data); //Separacion por comas
        $subWhere  = '';
        $errors    = [];

        /******************************************/
        //Este es en el caso de que se esten actualizando datos y se busque un dato distinto al actualizado
        if (!empty($SIS_Where)) {
            //Variable vacia
            $parts    = [];
            //Se separan los datos
            $arrWhere = $this->CommonData->parseDataCommas($SIS_Where);
            //Se recorren los datos separados
            foreach ($arrWhere as $field) {
                // Se verifican los datos del post
                if (isset($SIS_Post[$field])&&$SIS_Post[$field]!='') {
                    $parts[] = $field . " != '" . $this->clearData($SIS_Post[$field]) . "'";
                }
            }
            //Se agregan los datos al where
            $subWhere .= $parts ? implode(' AND ', $parts) : '';
        }


        /******************************************/
        //Recorro
        foreach ($arrData as $data) {
            /******************************************/
            //Guardo los datos
            $DataInternal  = '';
            $whereInternal = $subWhere;
            /******************************************/
            //verifico si hay subgrupos
            if (strpos($data, "-")){
                //Variable vacia
                $parts_data  = [];
                $parts_where = [];
                //Se separan los datos
                $arrData2 = $this->CommonData->parseDataSeparator($data); //Separacion por guiones
                //Se recorren los datos separados
                foreach ($arrData2 as $field) {
                    // Se verifica que el dato POST exista
                    if (isset($SIS_Post[$field])&&$SIS_Post[$field]!='') {
                        $parts_data[]  = $field;
                        $parts_where[] = $field . " = '" . $this->clearData($SIS_Post[$field]) . "'";
                    // En el caso de que sea una validacion distinta a los datos enviados por POST
                    }elseif (strpos($field, "=")||strpos($field, "!=")){
                        $arrData3      = $this->CommonData->parseDataSymbol($field);
                        $parts_data[]  = $arrData3[0];
                        $parts_where[] = $field;
                    }
                }
                //Se crea cadena en base al arreglo, con su propia separacion
                $x_data  = $parts_data ? implode(',', $parts_data) : '';
                $x_where = $parts_where ? implode(' AND ', $parts_where) : '';
                //Se genera la query solo si hay datos
                if($x_data!=''){
                    //Guardo los datos
                    $DataInternal  = $x_data;
                    $whereInternal = ($whereInternal!='') ? $whereInternal.' AND '.$x_where : $x_where;
                }
            /******************************************/
            //si no hay subgrupo se ejecuta normalmente
            }else{
                //verifico si existe el dato
                if (isset($SIS_Post[$data])&&$SIS_Post[$data]!='') {
                    //Guardo los datos
                    $DataInternal  = $data;
                    $whereInternal = ($whereInternal!='') ? $whereInternal.' AND '.$data." = '".$this->clearData($SIS_Post[$data])."'" : $data." = '".$this->clearData($SIS_Post[$data])."'";
                }
            }
            /******************************************/
            //valido si hay datos
            if($DataInternal!=''){
                //se ejecuta la query
                $query = [
                    'data'  => $DataInternal,
                    'table' => $SIS_Table,
                    'where' => $whereInternal
                ];
                //Ejecuto la query
                $ndata = $this->queryNRows($query, $DBConn);
                //si hay un dato
                if($ndata > 0) {$errors[] = ["message" => "Los datos que intenta ingresar ya existen en el Sistema"];}
            }
        }

        //si no hay errores
        return (empty($errors)) ? true : $errors;

    }
    /******************************************************************************/
    private function clearData(string $Data): string{
        $Data = trim($Data);         //Elimina espacios al inicio y al termino
        $Data = stripslashes($Data); //Elimina barras invertidas
        //FIX: htmlspecialchars() es para contexto HTML, no SQL. addslashes() escapa correctamente para SQL
        $Data = addslashes($Data);   //Escapa comillas simples, dobles y barras para uso en SQL
        return $Data;
    }
    /******************************************************************************/
    private function createQuery(array $query): string{
        //armado de la query
        $ActionSQL = 'SELECT '.$query['data'];
        $ActionSQL.= ' FROM `'.$query['table'].'`';
        //Recorro las opciones
        foreach (['join' => ' ', 'where' => ' WHERE ', 'group' => ' GROUP BY ', 'having' => ' HAVING ', 'order' => ' ORDER BY ', 'limit' => ' LIMIT '] as $key => $clause) {
            if (!empty($query[$key])) {
                $ActionSQL .= $clause . $query[$key];
            }
        }
        return $ActionSQL;
    }
    /******************************************************************************/
    //Loguea de forma segura los errores sin exponer SQL al cliente
    private function logError($sql, $exception) {
        //Guarda el detalle en logs del servidor (no se expone al cliente)
        error_log('QueryBuilder Error: [' . $exception->getMessage() . '] SQL: ' . $sql);
        //Retorna un mensaje generico al cliente
        return 'Query Error: Se produjo un error al procesar la solicitud.';
    }
    /******************************************************************************/
    //Procesamiento de archivos - evita codigo duplicado entre queryInsert y queryUpdate
    private function processFiles($query, $action = 'insert') {
        //Variable vacia
        $result = ['success' => true, 'nombres' => '', 'archivos' => '', 'update' => ''];
        //Si no hay archivos se retorna vacio
        if (empty($query['files'])) {return $result;}
        //Cuento los archivos esperados y si existen
        $CountFileExist = array_reduce(
            $query['files'],
            function($count, $archivo) use ($query) {
                return $count + (!empty($query['Post'][$archivo['Identificador']]) ? 1 : 0);
            },
            0
        );
        //Si existen archivos fisicos o si se enviaron por base64
        if (!empty($_FILES) || $CountFileExist != 0) {
            $isUpdate = ($action === 'update');
            //Valido los archivos
            $dataFiles = $isUpdate
                ? $this->FileManager->validateFiles($_FILES, $query['files'], $query['Post'])
                : $this->FileManager->validateFiles($_FILES, $query['files']);
            //Si todos los datos requeridos estan ok
            if ($dataFiles['success'] !== true) {
                $result['success'] = $dataFiles['success'];
                $result['error']   = $dataFiles['message'];
                return $result;
            }
            //Si no hay errores se suben los archivos
            $newFileName = $isUpdate
                ? $this->FileManager->uploadFile($_FILES, $query['files'], $query['Post'])
                : $this->FileManager->uploadFile($_FILES, $query['files']);
            //Se guardan los nombres
            if ($action === 'insert') {
                $result['nombres']  = $newFileName['Nombres'];
                $result['archivos'] = $newFileName['Archivos'];
            } else {
                $result['update'] = $newFileName['Update'];
            }
        }
        return $result;
    }
    /******************************************************************************/
    //Codificacion de datos del formulario - evita codigo duplicado entre queryInsert y queryUpdate
    private function encodeFormData($query) {
        //Se verifica si hay datos a codificar
        if (!empty($query['encode'])){
            //Separo los datos
            $arrEncode = $this->CommonData->parseDataCommas($query['encode']); //Separacion por comas
            //recorro validando
            foreach ($arrEncode as $data) {
                if(isset($query['Post'][$data]) && $query['Post'][$data] != ''){
                    $query['Post'][$data] = $this->Codification->encryptDecrypt('encrypt', $query['Post'][$data], ConfigToken::ENCODE_KEYS["KEY_1"]);
                }
            }
        }
        //Retorna el arreglo POST (modificado o no)
        return $query['Post'];
    }

}


