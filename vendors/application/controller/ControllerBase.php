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
    protected function Base_GetList(array &$params){
		/*
		*=================================================     Detalles    =================================================
		*
		* Listar
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//Formato de la query
        *   $query = [
        *       'data'   => 'data1,data2,data3',   -> Ver opciones select
        *       'table'  => 'data_table',
        *       'join'   => '',                    -> Ver opciones de los join
        *       'where'  => 'data1 = 1',           -> Ver modos alternativos where
        *       'group'  => '',                    -> Ver agrupaciones
        *       'having' => '',
        *       'order'  => 'data1 DESC',
        *       'limit'  => 60
        *   ];
		*
		*===================================================================================================================
		*/

        /**********************     Valores     **********************/
        // Extraer parámetros con valores por defecto
        $query     = $params['query'] ?? '';
        $showQuery = $params['showQuery'] ?? false;
        $newBDConn = $params['newBDConn'] ?? null;

        /********************** Si todo esta ok **********************/
        //Verifico que base de datos se va a utilizar
        $DBConn = $newBDConn ?? $this->DBConn;

        /**********************  Retorno datos  **********************/
        //devuelvo resultados
        return $this->queryBuilder->queryArray($query, $DBConn, $showQuery);
    }

    /************************************************************************************************************/
    protected function Base_GetByID(array &$params){
		/*
		*=================================================     Detalles    =================================================
		*
		* Ver Datos
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//Formato de la query
        *   $query = [
        *       'data'   => 'data1,data2,data3',   -> Ver opciones select
        *       'table'  => 'data_table',
        *       'join'   => '',                    -> Ver opciones de los join
        *       'where'  => 'data1 = 1',           -> Ver modos alternativos where
        *       'group'  => '',                    -> Ver agrupaciones
        *       'having' => '',
        *       'order'  => 'data1 DESC'
        *   ];
		*
		*===================================================================================================================
		*/

        /**********************     Valores     **********************/
        // Extraer parámetros con valores por defecto
        $query     = $params['query'] ?? '';
        $showQuery = $params['showQuery'] ?? false;
        $newBDConn = $params['newBDConn'] ?? null;

        /********************** Si todo esta ok **********************/
        //Verifico que base de datos se va a utilizar
        $DBConn = $newBDConn ?? $this->DBConn;

        /**********************  Retorno datos  **********************/
        //devuelvo resultados
        return $this->queryBuilder->queryRow($query, $DBConn, $showQuery);
    }

    /************************************************************************************************************/
    protected function Base_GetCountData(array &$params){
		/*
		*=================================================     Detalles    =================================================
		*
		* Contar
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//Formato de la query
        *   $query = [
        *       'data'   => 'data1,data2,data3',   -> Ver opciones select
        *       'table'  => 'data_table',
        *       'join'   => '',                    -> Ver opciones de los join
        *       'where'  => 'data1 = 1',           -> Ver modos alternativos where
        *       'group'  => '',                    -> Ver agrupaciones
        *       'having' => '',
        *       'order'  => 'data1 DESC'
        *   ];
		*
		*=================================================    Parametros   =================================================
		* @input   float   $valor   Numero a formatear
		* @return  float
		*===================================================================================================================
		*/

        /**********************     Valores     **********************/
        // Extraer parámetros con valores por defecto
        $query     = $params['query'] ?? '';
        $showQuery = $params['showQuery'] ?? false;
        $newBDConn = $params['newBDConn'] ?? null;

        /********************** Si todo esta ok **********************/
        //Verifico que base de datos se va a utilizar
        $DBConn = $newBDConn ?? $this->DBConn;

        /**********************  Retorno datos  **********************/
        //devuelvo resultados
        return $this->queryBuilder->queryNRows($query, $DBConn, $showQuery);
    }

    /************************************************************************************************************/
    protected function Base_insert(array &$params){
		/*
		*=================================================     Detalles    =================================================
		*
		* Crear
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	Formato de la query
        *   $query = [
        *       'data'      => 'usuario,idEstado,email,Nombre,Rut,password', -> Datos a insertar, dejar fuera los archivos
        *       'required'  => 'email,Nombre,Rut',                           -> Datos obligatorios a insertar, son validados, si no existen impide la ejecucion
        *       'unique'    => 'email,Nombre-Rut',                           -> Datos unicos, se consulta en la BD que el dato ingresado no este repetido
        *       'encode'    => 'password',                                   -> Datos a codificar
        *       'table'     => 'usuarios_listado',                           -> Tabla donde se ejecuta la consulta
        *       'Post'      => $_POST,                                       -> Datos $_POST entregados
        *       'files'     => [                                             -> Arreglo con los archivos, cada uno va dentro de su propio array
        *           [
        *               'Identificador' => 'Direccion_img',                                       -> Columna dentro de la BD, identificador del archivo
        *               'SubCarpeta'    => '',                                                    -> Opcional, si el archivo se guarda en una subcarpeta
        *               'NombreArchivo' => '',                                                    -> Se se utiliza un nombre particular, sino, se utiliza el sufijo
        *               'SufijoArchivo' => 'Sufijo_',                                             -> Si al nombre del archivo se le pone un sufijo
        *               'ValidarTipo'   => 'word,excel,powerpoint,pdf,image,txt,zip,video,music', -> Formato archivo a validar
        *               'ValidarPeso'   => 10,                                                    -> Validacion peso maximo del archivo (en megas)
        *               'Base64'        => true                                                   -> Si el archivo es entregado como texto (base64), esto hace que se ignoren todas las validaciones
        *           ],
        *       ]
        *   ];
		*
		*===================================================================================================================
		*/

        /**********************     Valores     **********************/
        // Extraer parámetros con valores por defecto
        $DataCheck  = $params['DataCheck'] ?? '';
        $query      = $params['query'] ?? '';
        $showQuery  = $params['showQuery'] ?? false;
        $novalidate = $params['novalidate'] ?? false;
        $newBDConn  = $params['newBDConn'] ?? null;

        /********************** Si todo esta ok **********************/
        //Verifico que base de datos se va a utilizar
        $DBConn = $newBDConn ?? $this->DBConn;

        /********************** Si todo esta ok **********************/
        //Ejecuto el chequeo
        $checkData = $this->checkData->checkingData($DataCheck);
        if ($checkData!==false) { return $checkData;}

        /**********************  Retorno datos  **********************/
        //devuelvo resultados
        return $this->queryBuilder->queryInsert($query, $DBConn, $showQuery, $novalidate);
    }

    /************************************************************************************************************/
    protected function Base_update(array &$params){
		/*
		*=================================================     Detalles    =================================================
		*
		* Editar
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//Formato de la query
        *   $query = [
        *       'data'      => 'usuario,idEstado,email,Nombre,Rut,password', -> Datos a insertar, dejar fuera los archivos
        *       'required'  => 'email,Nombre,Rut',                           -> Datos obligatorios a insertar, son validados, si no existen impide la ejecucion
        *       'unique'    => 'email,Nombre-Rut',                           -> Datos unicos, se consulta en la BD que el dato ingresado no este repetido
        *       'encode'    => 'password',                                   -> Datos a codificar
        *       'table'     => 'usuarios_listado',                           -> Tabla donde se ejecuta la consulta
        *       'Post'      => $_POST,                                       -> Datos $_POST entregados
        *       'files'     => [                                             -> Arreglo con los archivos, cada uno va dentro de su propio array
        *           [
        *               'Identificador' => 'Direccion_img',                                       -> Columna dentro de la BD, identificador del archivo
        *               'SubCarpeta'    => '',                                                    -> Opcional, si el archivo se guarda en una subcarpeta
        *               'NombreArchivo' => '',                                                    -> Se se utiliza un nombre particular, sino, se utiliza el sufijo
        *               'SufijoArchivo' => 'Sufijo_',                                             -> Si al nombre del archivo se le pone un sufijo
        *               'ValidarTipo'   => 'word,excel,powerpoint,pdf,image,txt,zip,video,music', -> Formato archivo a validar
        *               'ValidarPeso'   => 10,                                                    -> Validacion peso maximo del archivo (en megas)
        *               'Base64'        => true                                                   -> true-false ->Si el archivo es entregado como texto (base64), esto hace que se ignoren todas las validaciones
        *           ],
        *       ]
        *   ];
		*
		*===================================================================================================================
		*/

        /**********************     Valores     **********************/
        // Extraer parámetros con valores por defecto
        $DataCheck  = $params['DataCheck'] ?? '';
        $query      = $params['query'] ?? '';
        $showQuery  = $params['showQuery'] ?? false;
        $novalidate = $params['novalidate'] ?? false;
        $newBDConn  = $params['newBDConn'] ?? null;

        /********************** Si todo esta ok **********************/
        //Verifico que base de datos se va a utilizar
        $DBConn = $newBDConn ?? $this->DBConn;

        /********************** Si todo esta ok **********************/
        //Ejecuto el chequeo
        $checkData = $this->checkData->checkingData($DataCheck);
        if ($checkData!==false) { return $checkData;}

        /**********************  Retorno datos  **********************/
        //devuelvo resultados
        return $this->queryBuilder->queryUpdate($query, $DBConn, $showQuery, $novalidate);
    }

    /************************************************************************************************************/
    protected function Base_delete(array &$params){
		/*
		*=================================================     Detalles    =================================================
		*
		* Borrar dato y archivos
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//Formato de la query
        *   $query = [
        *       'files'       => 'Direccion_img',    -> Nombre del archivo dentro de la base de datos
        *       'table'       => 'usuarios_listado', -> Tabla donde esta el dato
        *       'where'       => 'idUsuario',        -> Dato del where, es validado con los datos $_POST
        *       'SubCarpeta'  => '',                 -> Si el archivo esta dentro de una subcarpeta
        *       'Post'        => $_POST              -> Datos $_POST
        *   ];
		*
		*===================================================================================================================
		*/

        /**********************     Valores     **********************/
        // Extraer parámetros con valores por defecto
        $query     = $params['query'] ?? '';
        $showQuery = $params['showQuery'] ?? false;
        $newBDConn = $params['newBDConn'] ?? null;

        /********************** Si todo esta ok **********************/
        //Verifico que base de datos se va a utilizar
        $DBConn = $newBDConn ?? $this->DBConn;

        /**********************  Retorno datos  **********************/
        //devuelvo resultados
        return $this->queryBuilder->queryDelete($query, $DBConn, $showQuery);
    }

    /************************************************************************************************************/
    protected function Base_queryExecute(array &$params){
		/*
		*=================================================     Detalles    =================================================
		*
		* Borrar dato y archivos
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//Formato de la query
        *   $query = [
        *       'files'       => 'Direccion_img',    -> Nombre del archivo dentro de la base de datos
        *       'table'       => 'usuarios_listado', -> Tabla donde esta el dato
        *       'where'       => 'idUsuario',        -> Dato del where, es validado con los datos $_POST
        *       'SubCarpeta'  => '',                 -> Si el archivo esta dentro de una subcarpeta
        *       'Post'        => $_POST              -> Datos $_POST
        *   ];
		*
		*===================================================================================================================
		*/

        /**********************     Valores     **********************/
        // Extraer parámetros con valores por defecto
        $query     = $params['query'] ?? '';
        $showQuery = $params['showQuery'] ?? false;
        $newBDConn = $params['newBDConn'] ?? null;

        /********************** Si todo esta ok **********************/
        //Verifico que base de datos se va a utilizar
        $DBConn = $newBDConn ?? $this->DBConn;

        /**********************  Retorno datos  **********************/
        //devuelvo resultados
        return $this->queryBuilder->queryExecute($query, $DBConn, $showQuery);
    }

    /************************************************************************************************************/
    protected function Base_delFiles(array &$params){
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite eliminar archivos
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//Formato de la query
        *   $query = [
        *       'files'       => 'Direccion_img',    -> Nombre del archivo dentro de la base de datos
        *       'table'       => 'usuarios_listado', -> Tabla donde esta el dato
        *       'where'       => 'idUsuario',        -> Dato del where, es validado con los datos $_POST
        *       'SubCarpeta'  => '',                 -> Si el archivo esta dentro de una subcarpeta
        *       'Post'        => $_POST              -> Datos $_POST
        *   ];
		*
		*===================================================================================================================
		*/

        /**********************     Valores     **********************/
        // Extraer parámetros con valores por defecto
        $query     = $params['query'] ?? '';
        $newBDConn = $params['newBDConn'] ?? null;

        /********************** Si todo esta ok **********************/
        //Verifico que base de datos se va a utilizar
        $DBConn = $newBDConn ?? $this->DBConn;

        /**********************  Retorno datos  **********************/
        //devuelvo resultados
        return $this->queryBuilder->delFiles($query, $DBConn);
    }

    /************************************************************************************************************/
    protected function Base_createTable(array &$params){
		/*
		*=================================================     Detalles    =================================================
		*
		* Creacion de tablas
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//Formato de la query
        *   $query = [
        *       'table'      => 'usuarios_listado',                                    -> Tabla donde se ejecuta la consulta
        *       'data'       => '`idCorreosCat` int UNSIGNED NOT NULL AUTO_INCREMENT', -> Datos a crear
        *       'primaryKey' => 'idusuario',                                           -> Clave Primaria
        *       'comentario' => 'fija',                                                -> Comentario de la tabla
        *   ];
		*
		*===================================================================================================================
		*/

        /**********************     Valores     **********************/
        // Extraer parámetros con valores por defecto
        $query     = $params['query'] ?? '';
        $showQuery = $params['showQuery'] ?? false;
        $newBDConn = $params['newBDConn'] ?? null;

        /********************** Si todo esta ok **********************/
        //Verifico que base de datos se va a utilizar
        $DBConn = $newBDConn ?? $this->DBConn;

        /**********************  Retorno datos  **********************/
        //devuelvo resultados
        return $this->queryBuilder->queryCreateTable($query, $DBConn, $showQuery);
    }

    /************************************************************************************************************/
    protected function Base_dropTable(array &$params){
		/*
		*=================================================     Detalles    =================================================
		*
		* Eliminacion de tablas
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//Formato de la query
        *   $query = [
        *       'table' => 'usuarios_listado', -> Tabla donde se ejecuta la consulta
        *   ];
		*
		*===================================================================================================================
		*/

        /**********************     Valores     **********************/
        // Extraer parámetros con valores por defecto
        $query     = $params['query'] ?? '';
        $showQuery = $params['showQuery'] ?? false;
        $newBDConn = $params['newBDConn'] ?? null;

        /********************** Si todo esta ok **********************/
        //Verifico que base de datos se va a utilizar
        $DBConn = $newBDConn ?? $this->DBConn;

        /**********************  Retorno datos  **********************/
        //devuelvo resultados
        return $this->queryBuilder->queryDropTable($query, $DBConn, $showQuery);
    }

    /************************************************************************************************************/
    protected function Base_SMTPMail($f3, $query){
		/*
		*=================================================     Detalles    =================================================
		*
		* Envio de correo (solo un correo, con uno o varios receptores)
		*
		*===================================================================================================================
		*/

        /********************** Si todo esta ok **********************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');

        //Se arman los datos
        $TemplateData = [
            'Sistema_IMGLogo'   => $UserData['Sistema_IMGLogo'],
            'Sistema_Direccion' => $UserData['Sistema_Direccion'],
            'Sistema_Email'     => $UserData['Sistema_Email'],
            'Social_X'          => $UserData['Social_X'],
            'Social_Facebook'   => $UserData['Social_Facebook'],
            'Social_Instagram'  => $UserData['Social_Instagram'],
            'Social_Linkedin'   => $UserData['Social_Linkedin'],
            'baseUrl'           => $f3->get('BASE')
        ];

        //Ejecuto la query
        $mailSender = new MailSender();
        $result     = $mailSender->sendSMTPMail($TemplateData, $query); //Envio por correo normal

        /**********************  Retorno datos  **********************/
        //Si hay resultados
        return ($result) ? $result : false;
    }

    /************************************************************************************************************/
    protected function Base_GMail($f3, $query){
		/*
		*=================================================     Detalles    =================================================
		*
		* Envio de correo (solo un correo, con uno o varios receptores)
		*
		*===================================================================================================================
		*/

        /********************** Si todo esta ok **********************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');

        //Se arman los datos
        $TemplateData = [
            'Sistema_IMGLogo'   => $UserData['Sistema_IMGLogo'],
            'Sistema_Direccion' => $UserData['Sistema_Direccion'],
            'Sistema_Email'     => $UserData['Sistema_Email'],
            'Social_X'          => $UserData['Social_X'],
            'Social_Facebook'   => $UserData['Social_Facebook'],
            'Social_Instagram'  => $UserData['Social_Instagram'],
            'Social_Linkedin'   => $UserData['Social_Linkedin'],
            'baseUrl'           => $f3->get('BASE')
        ];

        //Ejecuto la query
        $mailSender = new MailSender();
        $result     = $mailSender->sendGMail($TemplateData, $query);    //Envio por gmail

        /**********************  Retorno datos  **********************/
        //Si hay resultados
        return ($result) ? $result : false;
    }

    /************************************************************************************************************/
    protected function Base_SendingBlue($f3, $query){
		/*
		*=================================================     Detalles    =================================================
		*
		* Envio de correo (solo un correo, con uno o varios receptores)
		*
		*===================================================================================================================
		*/

        /********************** Si todo esta ok **********************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');

        //Se arman los datos
        $TemplateData = [
            'Sistema_IMGLogo'   => $UserData['Sistema_IMGLogo'],
            'Sistema_Direccion' => $UserData['Sistema_Direccion'],
            'Sistema_Email'     => $UserData['Sistema_Email'],
            'Social_X'          => $UserData['Social_X'],
            'Social_Facebook'   => $UserData['Social_Facebook'],
            'Social_Instagram'  => $UserData['Social_Instagram'],
            'Social_Linkedin'   => $UserData['Social_Linkedin'],
            'baseUrl'           => $f3->get('BASE')
        ];

        //Ejecuto la query
        $mailSender = new MailSender();
        $result     = $mailSender->sendSendingBlueMail($TemplateData, $query);    //Envio por Sending Blue

        /**********************  Retorno datos  **********************/
        //Si hay resultados
        return ($result) ? $result : false;
    }

    /************************************************************************************************************/
    protected function Base_TestMailTemplate($f3, $query){
		/*
		*=================================================     Detalles    =================================================
		*
		* Revision de plantillas de correo
		*
		*===================================================================================================================
		*/

        /********************** Si todo esta ok **********************/
        //Se llaman los datos
        $UserData = $f3->get('SESSION.DataInfo');

        //Se arman los datos
        $TemplateData = [
            'Sistema_IMGLogo'   => $UserData['Sistema_IMGLogo'],
            'Sistema_Direccion' => $UserData['Sistema_Direccion'],
            'Sistema_Email'     => $UserData['Sistema_Email'],
            'Social_X'          => $UserData['Social_X'],
            'Social_Facebook'   => $UserData['Social_Facebook'],
            'Social_Instagram'  => $UserData['Social_Instagram'],
            'Social_Linkedin'   => $UserData['Social_Linkedin'],
            'baseUrl'           => $f3->get('BASE')
        ];

        //Ejecuto la query
        $mailSender = new MailSender();
        $result     = $mailSender->testMailTemplate($TemplateData, $query);    //Se obtiene solo las respuesta

        /**********************  Retorno datos  **********************/
        //Si hay resultados
        return ($result) ? $result : false;
    }

    /************************************************************************************************************/
    protected function returnRutaVista($directorio, $aplicacion){
		/*
		*=================================================     Detalles    =================================================
		*
		* Devuelve la ruta a la vista a partir de la ruta del controlador
		*
		*===================================================================================================================
		*/

        /********************** Si todo esta ok **********************/
        //Generar ubicacion de las vistas
        $rutaController = substr($directorio, strpos($directorio, $aplicacion)); //se obtiene la ruta del controlador
        $rutaVista      = str_replace("controller", "views", $rutaController);   //se obtiene la ruta a la vista

        /**********************  Retorno datos  **********************/
        return $rutaVista;
    }

    /************************************************************************************************************/
    protected function searchWhere($whereInt, $WhereData, $Transx, $Type){
		/*
		*=================================================     Detalles    =================================================
		*
		* Genera la query para las consultas filtradas
		*
		*===================================================================================================================
		*/

        /********************** Si todo esta ok **********************/
        //Variable vacia
        $parts    = [];
        //Verifico que no venga vacio
        if($WhereData!=''){
            //Se separan los datos
            $arrWhere = $this->CommonData->parseDataCommas($WhereData); //Separacion por comas
            //Verifico el tipo
            switch ($Type) {
                /***********************************/
                //Tipo Integer
                case 1:
                    //Se recorren los datos separados
                    foreach ($arrWhere as $field) {
                        // Se verifican los datos del post
                        if (!empty($_POST[$field])) {
                            $parts[] = $Transx.'.'.$field." = '".$this->clearData($_POST[$field])."'";
                        }
                    }
                    break;
                /***********************************/
                //Tipo String
                case 2:
                    //Se recorren los datos separados
                    foreach ($arrWhere as $field) {
                        // Se verifican los datos del post
                        if (!empty($_POST[$field])) {
                            $parts[] = $Transx.'.'.$field." LIKE '%".$this->clearData($_POST[$field])."%'";
                        }
                    }
                    break;
                /***********************************/
                //Tipo Between
                case 3:
                    //Se recorren los datos separados
                    foreach ($arrWhere as $field) {
                        //Separacion por guiones
                        $arrData = $this->CommonData->parseDataSeparator($field); //Separacion por guiones
                        // Se verifican los datos del post
                        if (!empty($_POST[$arrData[1]])&&!empty($_POST[$arrData[2]])) {
                            $parts[] = $Transx.'.'.$arrData[0]." BETWEEN '".$_POST[$arrData[1]]."' AND '".$_POST[$arrData[2]]."'";
                        }
                    }
                    break;
            }
        }

        /**********************  Retorno datos  **********************/
        //Se agregan los datos al where
        $subWhere   = $parts ? implode(' AND ', $parts) : '';
        //Se validan si hay datos de la consulta, sino se mantiene la herencia anterior
        $DataReturn = ($subWhere != '') ? $whereInt.' AND '.$subWhere : $whereInt;
        //Devuelvo
        return ($whereInt != '') ? $DataReturn : $subWhere;
    }

    /************************************************************************************************************/
    private function clearData($Data){
		/*
		*=================================================     Detalles    =================================================
		*
		* Elimina los datos no deseados
		*
		*===================================================================================================================
		*/

        /********************** Si todo esta ok **********************/
        $Data = trim($Data);             //Elimina espacios al inicio y al termino
        $Data = stripslashes($Data);     //Elimina barras invertidas
        $Data = htmlspecialchars($Data); //Transforma caracteres especiales en entidades HTML

        /**********************  Retorno datos  **********************/
        return $Data;
    }

    /************************************************************************************************************/
    protected function showVista($TypeSession, $TypeView, $Route){
		/*
		*=================================================     Detalles    =================================================
		*
		* Muestra los errores, la pagina o la vista
		*
		*===================================================================================================================
		*/

        /**********************    Instancia    **********************/
        //Se instancia la vista
        $view = new View;

        /**********************  Retorno datos  **********************/
        //opcion de vista
        switch ($TypeSession) {
            /**********************************/
            //Vista de la APP
            case 1:
                //opcion de vista
                switch ($TypeView) {
                    //Vista de la pagina
                    case 1:
                        echo $view->render('../app/templates/user-header.php');
                        echo $view->render('../'.$Route);
                        echo $view->render('../app/templates/user-footer.php');
                        break;
                    //Vista de un modal u otro
                    case 2:
                        echo $view->render('../'.$Route); // Vista
                        break;
                    //Vista de la impresion normal
                    case 3:
                        echo $view->render('../app/templates/user-printer-header.php');
                        echo $view->render('../'.$Route);
                        echo $view->render('../app/templates/user-printer-footer.php');
                        break;
                    //Vista de la impresion documentos mercantiles
                    case 4:
                        echo $view->render('../app/templates/user-printerDocs-header.php');
                        echo $view->render('../'.$Route);
                        echo $view->render('../app/templates/user-printerDocs-footer.php');
                        break;

                }
                break;
            /**********************************/
            //Vista de la API con sesion
            case 2:
                echo $view->render('../app/templates/api-vew.php');  // Vista
                break;
            /**********************************/
            //Vista de la API con token
            case 3:
                echo $view->render('../app/templates/api-vew.php');  // Vista
                break;
        }

    }

    /************************************************************************************************************/
    protected function showError($TypeSession, $TypeView, $f3){
		/*
		*=================================================     Detalles    =================================================
		*
		* Muestra los errores, la pagina o la vista
		*
		*===================================================================================================================
		*/

        /********************** Si todo esta ok **********************/
        //Datos enviados a la pagina
        $f3->data = [
            'PageTitle'       => 'Error Consulta',
            'PageDescription' => 'Error Consulta.',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            'UserData'        => $f3->get('SESSION.DataInfo'),
        ];

        /**********************    Instancia    **********************/
        //Se instancia la vista
        $view = new View;

        /**********************  Retorno datos  **********************/
        //opcion de vista
        switch ($TypeSession) {
            /**********************************/
            //Vista de la APP
            case 1:
                //opcion de vista
                switch ($TypeView) {
                    //Vista de la pagina
                    case 1:
                        echo $view->render('../app/templates/user-header.php'); // Header
                        echo $view->render('../app/templates/user-error.php');  // Vista
                        echo $view->render('../app/templates/user-footer.php'); // Footer
                        break;
                    //Vista de un modal u otro
                    case 2:
                        echo $view->render('../app/templates/user-error.php');  // Vista
                        break;
                    //Otra vista
                    case 3:
                        //otra vista
                        break;

                }
                break;
            /**********************************/
            //Vista de la API con sesion
            case 2:
                echo $view->render('../app/templates/api-vew.php');  // Vista
                break;
            /**********************************/
            //Vista de la API con token
            case 3:
                echo $view->render('../app/templates/api-vew.php');  // Vista
                break;
        }

    }


}
