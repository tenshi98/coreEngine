<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class testeos extends ControllerBase {

    /*******************************************************************/
    // Variables
    /*******************************************************************/
    private $controllerName;
    private $FormInputs;
    private $Server;
    private $Notifications;
    private $DataNumbers;
    private $DataText;
    private $ServerClient;


    /*******************************************************************/
    // Constructor
    /*******************************************************************/
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigDataBase::MySQL_ADMIN);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'Empty';
		$this->FormInputs     = new UIFormInputs();
		$this->Server         = new FunctionsServerServer();
		$this->Notifications  = new FunctionsServerSocial();
		$this->DataNumbers    = new FunctionsDataNumbers();
		$this->DataText       = new FunctionsDataText();
		$this->ServerClient   = new FunctionsServerClient();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /*******************************************************************/
    // Controladores
    /*******************************************************************/
    public function controladores($f3){
        /************************************/
        //Llamo a las otras clases
        $test             = new Test;
        $Fnc_Codification = new FunctionsSecurityCodification();

        //Se agrega datos post insert
        $Post_1 = [
            'Email'   => 'asd_'.rand(1,99999).'@asd.cl',
            'Numero'  => rand(1,99999),
            'Rut'     => '16029464-7',
            'Patente' => 'au1825',
            'Fecha'   => $this->Server->fechaActual(),
            'Hora'    => $this->Server->horaActual(),
            'Palabra' => 'test',
        ];
        //Se agrega datos post update
        $Post_2 = [
            'idTest'  => 1,
            'Email'   => 'asd_'.rand(1,99999).'@asd.cl',
            'Numero'  => rand(1,99999),
        ];

        $DataCheck1 = [
            'emptyData'                 => 'Email,Numero',
            'encode'                    => '',
            'ValidarEmail'              => 'Email',
            'ValidarNumero'             => 'Numero',
            'ValidarEntero'             => 'Numero',
            'ValidarRut'                => 'Rut',
            'ValidarPatente'            => 'Patente',
            'ValidarFecha'              => 'Fecha',
            'ValidarHora'               => 'Hora',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => 'Palabra',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'Palabra',
            'ValidarLargoMaximoN'       => 15,
            'ValidarPalabrasCensuradas' => 'Palabra',
            'ValidarEspaciosVacios'     => 'Palabra',
            'ValidarMayusculas'         => 'Palabra',
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
            'Post'                      => $Post_1,
        ];

        $DataCheck2 = [
            'emptyData'                 => '',
            'encode'                    => '',
            'ValidarEmail'              => 'Email,Palabra',
            'ValidarNumero'             => 'Numero',
            'ValidarEntero'             => 'Numero',
            'ValidarRut'                => 'Rut',
            'ValidarPatente'            => 'Patente',
            'ValidarFecha'              => 'Fecha',
            'ValidarHora'               => 'Hora',
            'ValidarURL'                => '',
            'ValidarLargoMinimo'        => 'Palabra',
            'ValidarLargoMinimoN'       => 3,
            'ValidarLargoMaximo'        => 'Palabra',
            'ValidarLargoMaximoN'       => 15,
            'ValidarPalabrasCensuradas' => 'Palabra',
            'ValidarEspaciosVacios'     => 'Palabra',
            'ValidarMayusculas'         => 'Palabra',
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
            'Post'                      => $Post_1,
        ];

        /*******************************************************************/
        /*                          Insertar Datos                         */
        /*******************************************************************/
        // Se genera la query
        $query = [
            'data'      => 'Email,Numero,Rut,Patente,Fecha,Hora,Palabra',
            'required'  => 'Email,Numero',
            'unique'    => 'Email,Numero',
            'table'     => 'core_test',
            'Post'      => $Post_1,
        ];
        /************************************/
        // Preparo los datos (Inserción Normal - Éxito)
        $xParams       = ['DataCheck' => $DataCheck1, 'query' => $query];
        $Base_insert_1 = $this->Base_insert($xParams);
        $insertedId    = $Base_insert_1['status'] ? $Base_insert_1['data'] : 1;

        /************************************/
        // Preparo los datos (Verificar Repetidos - Error)
        $xParams       = ['DataCheck' => $DataCheck1, 'query' => $query];
        $Base_insert_2 = $this->Base_insert($xParams);

        /************************************/
        // Preparo los datos (Verificar Tipo Dato - Error)
        $xParams       = ['DataCheck' => $DataCheck2, 'query' => $query];
        $Base_insert_3 = $this->Base_insert($xParams);


        /*******************************************************************/
        /*                          Actualizar Datos                       */
        /*******************************************************************/
        $Post_2['idTest'] = $insertedId;

        // Se genera la query
        $query = [
            'data'      => 'Email,Numero',
            'required'  => 'Email,Numero',
            'unique'    => 'Email,Numero',
            'table'     => 'core_test',
            'where'     => 'idTest',
            'Post'      => $Post_2,
        ];
        $DataCheckUpdateSuccess = [
            'emptyData'     => 'Email,Numero',
            'ValidarEmail'  => 'Email',
            'ValidarNumero' => 'Numero',
            'ValidarEntero' => 'Numero',
            'Post'          => $Post_2,
        ];
        /************************************/
        // Preparo los datos (Actualización Normal - Éxito)
        $xParams       = ['DataCheck' => $DataCheckUpdateSuccess, 'query' => $query];
        $Base_update_1 = $this->Base_update($xParams);

        /************************************/
        // Preparo los datos (Error Validación - Error)
        $Post_Update_Error          = $Post_2;
        $Post_Update_Error['Email'] = 'email_no_valido';
        $DataCheckUpdateError = [
            'ValidarEmail' => 'Email',
            'Post'         => $Post_Update_Error,
        ];
        $queryUpdateError           = $query;
        $queryUpdateError['Post']   = $Post_Update_Error;
        $xParams       = ['DataCheck' => $DataCheckUpdateError, 'query' => $queryUpdateError];
        $Base_update_2 = $this->Base_update($xParams);

        /*******************************************************************/
        /*                          Listar Datos                           */
        /*******************************************************************/
        // Se genera la query (Normal - Éxito)
        $query = [
            'data'    => 'Email,Numero,Fecha,Hora,Palabra',
            'table'   => 'core_test',
            'join'    => '',
            'where'   => '',
            'params'  => [],
            'group'   => '',
            'having'  => '',
            'order'   => 'Email ASC',
            'limit'   => '5'
        ];
        /************************************/
        // Preparo los datos
        $xParams        = ['query' => $query];
        $Base_GetList_1 = $this->Base_GetList($xParams);

        /************************************/
        // Se genera la query (Error Consulta - Error por falta de tabla)
        $queryListError          = $query;
        $queryListError['table'] = '';
        $xParams        = ['query' => $queryListError];
        $Base_GetList_2 = $this->Base_GetList($xParams);


        /*******************************************************************/
        /*                           Ver Datos                             */
        /*******************************************************************/
        // Se genera la query (Normal - Éxito)
        $query = [
            'data'   => 'Email,Numero,Fecha,Hora,Palabra',
            'table'  => 'core_test',
            'join'   => '',
            'where'  => 'idTest = ?',
            'params' => [$insertedId],
            'group'  => '',
            'having' => '',
            'order'  => 'Email ASC'
        ];
        /************************************/
        // Preparo los datos
        $xParams        = ['query' => $query];
        $Base_GetByID_1 = $this->Base_GetByID($xParams);

        /************************************/
        // Se genera la query (Error Consulta - Error por falta de tabla)
        $queryByIDError          = $query;
        $queryByIDError['table'] = '';
        $xParams        = ['query' => $queryByIDError];
        $Base_GetByID_2 = $this->Base_GetByID($xParams);


        /*******************************************************************/
        /*                         Contar Datos                            */
        /*******************************************************************/
        // Se genera la query (Normal - Éxito)
        $query = [
            'data'   => 'idTest',
            'table'  => 'core_test',
            'join'   => '',
            'where'  => 'idTest = ?',
            'params' => [$insertedId],
            'group'  => '',
            'having' => '',
        ];
        /************************************/
        // Preparo los datos
        $xParams             = ['query' => $query];
        $Base_GetCountData_1 = $this->Base_GetCountData($xParams);

        /************************************/
        // Se genera la query (Error Consulta - Error por falta de columnas)
        $queryCountError         = $query;
        $queryCountError['data'] = '';
        $xParams             = ['query' => $queryCountError];
        $Base_GetCountData_2 = $this->Base_GetCountData($xParams);


        /*******************************************************************/
        /*                         Eliminar Datos                          */
        /*******************************************************************/
        // Se cifra el identificador como lo exige queryDelete
        $idEncriptado = $Fnc_Codification->encryptDecrypt('encrypt', $insertedId);
        $Post_Delete_Success = [
            'idTest' => $idEncriptado['data'],
        ];

        // Se genera la query (Normal - Éxito)
        $query = [
            'files'       => '',
            'table'       => 'core_test',
            'where'       => 'idTest',
            'SubCarpeta'  => '',
            'Post'        => $Post_Delete_Success
        ];
        /************************************/
        // Preparo los datos
        $xParams       = ['query' => $query];
        $Base_delete_1 = $this->Base_delete($xParams);

        /************************************/
        // Se genera la query (Error Consulta - Error por falta de where)
        $queryDeleteError = [
            'files'       => '',
            'table'       => 'core_test',
            'where'       => '',
            'SubCarpeta'  => '',
            'Post'        => []
        ];
        $xParams       = ['query' => $queryDeleteError];
        $Base_delete_2 = $this->Base_delete($xParams);


        //Base_SMTPMail
        //Base_GMail
        //Base_SendingBlue

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/

        /********** Base_insert **********/
        // Éxito:
        $test->expect(method_exists($this, 'Base_insert'), 'Base_insert() -> Normal es un metodo existente');
        $test->expect($Base_insert_1['status'] === true && !empty($Base_insert_1['data']), 'Base_insert() -> Normal Ha devuelto datos');
        $test->expect(is_string($Base_insert_1['data']) || is_numeric($Base_insert_1['data']), 'Base_insert() -> Normal Los datos obtenidos son del tipo ' . gettype($Base_insert_1['data']), $Base_insert_1['data']);
        // Error Unicidad:
        $test->expect(method_exists($this, 'Base_insert'), 'Base_insert() -> Verificar Repetidos es un metodo existente');
        $test->expect($Base_insert_2['status'] === false && !empty($Base_insert_2['error']), 'Base_insert() -> Verificar Repetidos Ha devuelto error esperado');
        $test->expect(is_string($Base_insert_2['error']) || is_array($Base_insert_2['error']), 'Base_insert() -> Verificar Repetidos Error obtenido del tipo ' . gettype($Base_insert_2['error']), $Base_insert_2['error']);
        // Error Validación Tipo Dato:
        $test->expect(method_exists($this, 'Base_insert'), 'Base_insert() -> Verificar Tipo Dato es un metodo existente');
        $test->expect($Base_insert_3['status'] === false && !empty($Base_insert_3['error']), 'Base_insert() -> Verificar Tipo Dato Ha devuelto error esperado');
        $test->expect(is_array($Base_insert_3['error']), 'Base_insert() -> Verificar Tipo Dato Error obtenido del tipo ' . gettype($Base_insert_3['error']), $Base_insert_3['error']);

        /********** Base_update **********/
        // Éxito:
        $test->expect(method_exists($this, 'Base_update'), 'Base_update() -> Normal es un metodo existente');
        $test->expect($Base_update_1['status'] === true, 'Base_update() -> Normal Ha devuelto ejecucion correcta');
        $test->expect(is_array($Base_update_1['data']), 'Base_update() -> Normal Los datos obtenidos son del tipo ' . gettype($Base_update_1['data']), $Base_update_1['data']);
        // Error Validación:
        $test->expect(method_exists($this, 'Base_update'), 'Base_update() -> Error Validacion es un metodo existente');
        $test->expect($Base_update_2['status'] === false && !empty($Base_update_2['error']), 'Base_update() -> Error Validacion Ha devuelto error esperado');
        $test->expect(is_array($Base_update_2['error']), 'Base_update() -> Error Validacion Error obtenido del tipo ' . gettype($Base_update_2['error']), $Base_update_2['error']);

        /********** Base_GetList **********/
        // Éxito:
        $test->expect(method_exists($this, 'Base_GetList'), 'Base_GetList() -> Normal es un metodo existente');
        $test->expect($Base_GetList_1['status'] === true && is_array($Base_GetList_1['data']), 'Base_GetList() -> Normal Ha devuelto datos');
        $test->expect(is_array($Base_GetList_1['data']), 'Base_GetList() -> Normal Los datos obtenidos son del tipo ' . gettype($Base_GetList_1['data']), $Base_GetList_1['data']);
        // Error Consulta:
        $test->expect(method_exists($this, 'Base_GetList'), 'Base_GetList() -> Error Consulta es un metodo existente');
        $test->expect($Base_GetList_2['status'] === false && !empty($Base_GetList_2['error']), 'Base_GetList() -> Error Consulta Ha devuelto error esperado');
        $test->expect(is_string($Base_GetList_2['error']), 'Base_GetList() -> Error Consulta Error obtenido del tipo ' . gettype($Base_GetList_2['error']), $Base_GetList_2['error']);

        /********** Base_GetByID **********/
        // Éxito:
        $test->expect(method_exists($this, 'Base_GetByID'), 'Base_GetByID() -> Normal es un metodo existente');
        $test->expect($Base_GetByID_1['status'] === true && is_array($Base_GetByID_1['data']), 'Base_GetByID() -> Normal Ha devuelto datos');
        $test->expect(is_array($Base_GetByID_1['data']), 'Base_GetByID() -> Normal Los datos obtenidos son del tipo ' . gettype($Base_GetByID_1['data']), $Base_GetByID_1['data']);
        // Error Consulta:
        $test->expect(method_exists($this, 'Base_GetByID'), 'Base_GetByID() -> Error Consulta es un metodo existente');
        $test->expect($Base_GetByID_2['status'] === false && !empty($Base_GetByID_2['error']), 'Base_GetByID() -> Error Consulta Ha devuelto error esperado');
        $test->expect(is_string($Base_GetByID_2['error']), 'Base_GetByID() -> Error Consulta Error obtenido del tipo ' . gettype($Base_GetByID_2['error']), $Base_GetByID_2['error']);

        /********** Base_GetCountData **********/
        // Éxito:
        $test->expect(method_exists($this, 'Base_GetCountData'), 'Base_GetCountData() -> Normal es un metodo existente');
        $test->expect($Base_GetCountData_1['status'] === true && is_int($Base_GetCountData_1['data']), 'Base_GetCountData() -> Normal Ha devuelto conteo de datos');
        $test->expect(is_int($Base_GetCountData_1['data']), 'Base_GetCountData() -> Normal Los datos obtenidos son del tipo ' . gettype($Base_GetCountData_1['data']), $Base_GetCountData_1['data']);
        // Error Consulta:
        $test->expect(method_exists($this, 'Base_GetCountData'), 'Base_GetCountData() -> Error Consulta es un metodo existente');
        $test->expect($Base_GetCountData_2['status'] === false && !empty($Base_GetCountData_2['error']), 'Base_GetCountData() -> Error Consulta Ha devuelto error esperado');
        $test->expect(is_string($Base_GetCountData_2['error']), 'Base_GetCountData() -> Error Consulta Error obtenido del tipo ' . gettype($Base_GetCountData_2['error']), $Base_GetCountData_2['error']);

        /********** Base_delete **********/
        // Éxito:
        $test->expect(method_exists($this, 'Base_delete'), 'Base_delete() -> Normal es un metodo existente');
        $test->expect($Base_delete_1['status'] === true, 'Base_delete() -> Normal Ha devuelto ejecucion correcta');
        $test->expect(is_array($Base_delete_1['data']), 'Base_delete() -> Normal Los datos obtenidos son del tipo ' . gettype($Base_delete_1['data']), $Base_delete_1['data']);
        // Error Consulta:
        $test->expect(method_exists($this, 'Base_delete'), 'Base_delete() -> Error Consulta es un metodo existente');
        $test->expect($Base_delete_2['status'] === false && !empty($Base_delete_2['error']), 'Base_delete() -> Error Consulta Ha devuelto error esperado');
        $test->expect(is_string($Base_delete_2['error']), 'Base_delete() -> Error Consulta Error obtenido del tipo ' . gettype($Base_delete_2['error']), $Base_delete_2['error']);

        /************************************/
        // Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Controlador Base',
            'PageDescription' => 'Testeos del controlador.',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            'TableTitle'      => 'Pruebas Unitarias del Controlador Base',
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
            /*=========== Datos Consultados ===========*/
            'test'            => $test->results(),
        ];

        /************************************/
        // Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/testeos-controladores.php');



    }

    /*******************************************************************/
    // Funciones
    /*******************************************************************/
    public function funciones($f3){
        /************************************/
        //Se abre la libreria de testeos
        $test  = new Test;
        $FNC_DataOperations       = new FunctionsDataOperations;
        /*******************************************************************/
        /*  COBERTURA DE FUNCIONES                                          */
        /*  Cada clase instanciada por runTest() recibe al menos una prueba  */
        /*  por cada metodo publico declarado. Los metodos que NO pueden     */
        /*  ejecutarse en la pagina de pruebas se documentan a continuacion: */
        /*                                                                   */
        /*  - FunctionsLocation::getGeocodeData / geocodeAddress             */
        /*      Solo se prueban sus validaciones; la geocodificacion real    */
        /*      requiere ApiKey de Google / Nominatim y salida a Internet.   */
        /*  - FunctionsServerServer::tareasServer                           */
        /*      Solo se prueban sus validaciones; los casos validos ejecutan */
        /*      comandos de sistema (iptables, wget).                        */
        /*  - FunctionsServerServer::removeDirectoryRecursive                */
        /*      Contiene un error ($$src) y su proposito es destructivo      */
        /*      (borrado recursivo), por lo que no se ejecuta.               */
        /*  - FunctionsServerServer::writeEnvFile / writeConfigClassFile     */
        /*      Solo se prueban sus validaciones; la escritura real crea /   */
        /*      modifica archivos del servidor.                              */
        /*  - FunctionsServerWeb::obtenerInfoIp / callExternalApi /          */
        /*    obtenerDatosXML                                                */
        /*      Solo se prueban sus validaciones; dependen de servicios      */
        /*      externos (geoplugin, cURL, HTTPS).                           */
        /*******************************************************************/


        /**********  FunctionsCommonData  **********/
        //--------------------- safePath ---------------------
        $this->runTest($test, 'FunctionsCommonData',   'safePath',                   ['/var/www/uploads/imagen.jpg','/var/www/uploads'],                          'array',   '("/var/www/uploads/imagen.jpg","/var/www/uploads"                -> Devuelve {"success":true,"data":"/var/www/uploads/imagen.jpg","error":"null"})');
        $this->runTest($test, 'FunctionsCommonData',   'safePath',                   ['/var/www/uploads/../uploads/documento.pdf','/var/www/uploads'],            'array',   '("/var/www/uploads/../uploads/documento.pdf","/var/www/uploads"  -> Devuelve {"success":true,"data":"/var/www/uploads/documento.pdf","error":"null"})');
        $this->runTest($test, 'FunctionsCommonData',   'safePath',                   ['/var/www/uploads/../../etc/passwd','/var/www/uploads'],                    'array',   '("/var/www/uploads/../../etc/passwd","/var/www/uploads"          -> Devuelve {"success":false,"data":"","error":"La ruta raíz no existe o no es accesible"})');
        $this->runTest($test, 'FunctionsCommonData',   'safePath',                   ['/var/www/uploads/no_existe.txt','/var/www/uploads'],                       'array',   '("/var/www/uploads/no_existe.txt","/var/www/uploads"             -> Devuelve {"success":false,"data":"","error":"La ruta raíz no existe o no es accesible"})');
        $this->runTest($test, 'FunctionsCommonData',   'safePath',                   ['/home/user/secret.txt','/var/www/uploads'],                                'array',   '("/home/user/secret.txt","/var/www/uploads"                      -> Devuelve {"success":false,"data":"","error":"La ruta raíz no existe o no es accesible"})');
        // Caso real valido: archivo existente dentro de la raiz permitida
        $this->runTest($test, 'FunctionsCommonData',   'safePath',                   [__FILE__, __DIR__],                                                         'array',   '(__FILE__, __DIR__ -> Devuelve success=true, data=ruta real del archivo, error=null)');
        // Caso sin datos: retorna el arreglo de error del metodo
        $this->runTest($test, 'FunctionsCommonData',   'safePath',                   ['', ''],                                                                    'array',   '("","" -> Devuelve success=false, data="", error=Sin datos ingresados)');
        //--------------------- agruparPorClave ---------------------
        $this->runTest($test, 'FunctionsCommonData',   'agruparPorClave',            [[['cat'=>'A','v'=>1],['cat'=>'B','v'=>2],['cat'=>'A','v'=>3]], 'cat'],      'array',   '([[3 registros]], "cat"                                          -> Devuelve {"A":[{"v":1},{"v":3}],"B":[{"v":2}]})');
        $this->runTest($test, 'FunctionsCommonData',   'agruparPorClave',            [[['v'=>1],['cat'=>'A'],['v'=>2]], 'cat'],                                   'array',   '([[3 registros]], "cat"                                          -> Devuelve {"Sin información":[{"v":1},{"v":2}],"A":[[]]})');
        $this->runTest($test, 'FunctionsCommonData',   'agruparPorClave',            [[['cat'=>'A']], ''],                                                        'array',   '([[1 registro]], ""                                              -> Devuelve [])');
        //--------------------- agruparYContar ---------------------
        $this->runTest($test, 'FunctionsCommonData',   'agruparYContar',             [[['estado'=>'ok'],['estado'=>'ok'],['estado'=>'']], ['estado']],            'array',   '([[3 registros]], ["estado"]                                     -> Devuelve {"estado":[{"nombre":"ok","cantidad":2})');
        //--------------------- colorMasClaro ---------------------
        $this->runTest($test, 'FunctionsCommonData',   'colorMasClaro',              ['#1A2B3C', 0.35],                                                         'string',  '("#1A2B3C", 0.35                                               -> Devuelve #5484B5)');
        //--------------------- objectToArrayRecursive ---------------------
        $this->runTest($test, 'FunctionsCommonData',   'objectToArrayRecursive',     [(object)['nombre'=>'Ana','direccion'=>(object)['ciudad'=>'Madrid']]],       'array',   '(object                                                          -> Devuelve {"nombre":"Ana","direccion":{"ciudad":"Madrid"}})');
        //--------------------- obtenerExtensionArchivo ---------------------
        $this->runTest($test, 'FunctionsCommonData',   'obtenerExtensionArchivo',    ['documento.pdf'],                                                           'string',  '("documento.pdf"                                                 -> Devuelve pdf)');
        //--------------------- parseDataCommas ---------------------
        $this->runTest($test, 'FunctionsCommonData',   'parseDataCommas',            ['uno, dos,tres'],                                                           'array',   '("uno, dos,tres"                                                 -> Devuelve ["uno","dos","tres"])');
        //--------------------- parseDataSeparator ---------------------
        $this->runTest($test, 'FunctionsCommonData',   'parseDataSeparator',         ['uno-dos-tres'],                                                            'array',   '("uno-dos-tres"                                                  -> Devuelve ["uno","dos","tres"])');
        //--------------------- parseDataSymbol ---------------------
        $this->runTest($test, 'FunctionsCommonData',   'parseDataSymbol',            ['edad=18'],                                                                 'array',   '("edad=18"                                                       -> Devuelve ["edad","18"])');


        /**********  FunctionsConvertions  **********/
        //--------------------- numero2horas ---------------------
        $this->runTest($test, 'FunctionsConvertions',   'numero2horas',     [''],           'string',  '(""  -> Devuelve Sin datos ingresados en horasDecimales)');
        $this->runTest($test, 'FunctionsConvertions',   'numero2horas',     ['a'],          'string',  '("a" -> Devuelve El dato ingresado en horasDecimales no es un numero (a))');
        $this->runTest($test, 'FunctionsConvertions',   'numero2horas',     [1.5],          'string',  '(1.5 -> Devuelve 01:30:00)');
        //--------------------- minutos2horas ---------------------
        $this->runTest($test, 'FunctionsConvertions',   'minutos2horas',    [''],           'string',  '(""  -> Devuelve Sin datos ingresados en nMinutos)');
        $this->runTest($test, 'FunctionsConvertions',   'minutos2horas',    ['a'],          'string',  '("a" -> Devuelve El dato ingresado en nMinutos no es un numero (a))');
        $this->runTest($test, 'FunctionsConvertions',   'minutos2horas',    [65],           'string',  '(65  -> Devuelve 01:05:00)');
        //--------------------- segundos2horas ---------------------
        $this->runTest($test, 'FunctionsConvertions',   'segundos2horas',   [''],           'string',  '(""   -> Devuelve Sin datos ingresados en nSegundos)');
        $this->runTest($test, 'FunctionsConvertions',   'segundos2horas',   ['a'],          'string',  '("a"  -> Devuelve El dato ingresado en nSegundos no es un numero (a))');
        $this->runTest($test, 'FunctionsConvertions',   'segundos2horas',   [3600],         'string',  '(3600 -> Devuelve 01:00:00)');
        //--------------------- horas2minutos ---------------------
        $this->runTest($test, 'FunctionsConvertions',   'horas2minutos',    [''],           'string',  '(""       -> Devuelve Sin datos ingresados en horas)');
        $this->runTest($test, 'FunctionsConvertions',   'horas2minutos',    ['a'],          'string',  '("a"      -> Devuelve El dato ingresado en horas no es una hora (a))');
        $this->runTest($test, 'FunctionsConvertions',   'horas2minutos',    ['01:05:00'],   'int',     '(01:05:00 -> Devuelve 65)');
        //--------------------- horas2segundos ---------------------
        $this->runTest($test, 'FunctionsConvertions',   'horas2segundos',   [''],           'string',  '(""       -> Devuelve Sin datos ingresados en horas)');
        $this->runTest($test, 'FunctionsConvertions',   'horas2segundos',   ['a'],          'string',  '("a"      -> Devuelve El dato ingresado en horas no es una hora (a))');
        $this->runTest($test, 'FunctionsConvertions',   'horas2segundos',   ['00:30:00'],   'int',     '(00:30:00 -> Devuelve 1800)');
        //--------------------- horas2decimales ---------------------
        $this->runTest($test, 'FunctionsConvertions',   'horas2decimales',  [''],           'string',  '(""       -> Devuelve Sin datos ingresados en horas)');
        $this->runTest($test, 'FunctionsConvertions',   'horas2decimales',  ['a'],          'string',  '("a"      -> Devuelve El dato ingresado en horas no es una hora (a))');
        $this->runTest($test, 'FunctionsConvertions',   'horas2decimales',  ['01:30:00'],   'float',   '(01:30:00 -> Devuelve 1.5)');
        //--------------------- DevolverMes ---------------------
        $this->runTest($test, 'FunctionsConvertions',   'DevolverMes',      [''],           'string',  '(""  -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsConvertions',   'DevolverMes',      ['a'],          'string',  '("a" -> Devuelve Dato fuera de parámetros esperados)');
        $this->runTest($test, 'FunctionsConvertions',   'DevolverMes',      ['Ene'],        'string',  '(Ene -> Devuelve Enero)');
        //--------------------- numero2mes ---------------------
        $this->runTest($test, 'FunctionsConvertions',   'numero2mes',       [''],           'string',  '(""  -> Devuelve Sin datos ingresados en numero)');
        $this->runTest($test, 'FunctionsConvertions',   'numero2mes',       ['a'],          'string',  '("a" -> Devuelve El dato ingresado en numero no es un numero (a))');
        $this->runTest($test, 'FunctionsConvertions',   'numero2mes',       [25],           'string',  '(25  -> Devuelve Numero fuera de parámetros esperados)');
        $this->runTest($test, 'FunctionsConvertions',   'numero2mes',       [1],            'string',  '(1   -> Devuelve Enero)');
        //--------------------- numero2mesCorto ---------------------
        $this->runTest($test, 'FunctionsConvertions',   'numero2mesCorto',  [''],           'string',  '(""  -> Devuelve Sin datos ingresados en numero)');
        $this->runTest($test, 'FunctionsConvertions',   'numero2mesCorto',  ['a'],          'string',  '("a" -> Devuelve El dato ingresado en numero no es un numero (a))');
        $this->runTest($test, 'FunctionsConvertions',   'numero2mesCorto',  [25],           'string',  '(25  -> Devuelve Numero fuera de parámetros esperados)');
        $this->runTest($test, 'FunctionsConvertions',   'numero2mesCorto',  [1],            'string',  '(1   -> Devuelve Ene)');
        //--------------------- numeroNombreDia ---------------------
        $this->runTest($test, 'FunctionsConvertions',   'numeroNombreDia',  [''],           'string',  '(""  -> Devuelve Sin datos ingresados en numero)');
        $this->runTest($test, 'FunctionsConvertions',   'numeroNombreDia',  ['a'],          'string',  '("a" -> Devuelve El dato ingresado en numero no es un numero (a))');
        $this->runTest($test, 'FunctionsConvertions',   'numeroNombreDia',  [25],           'string',  '(25  -> Devuelve Numero fuera de parámetros esperados)');
        $this->runTest($test, 'FunctionsConvertions',   'numeroNombreDia',  [3],            'string',  '(3   -> Devuelve Miercoles)');
        //--------------------- porcentaje ---------------------
        $this->runTest($test, 'FunctionsConvertions',   'porcentaje',       [''],           'string',  '(""   -> Devuelve Sin datos ingresados en valor)');
        $this->runTest($test, 'FunctionsConvertions',   'porcentaje',       ['a'],          'string',  '("a"  -> Devuelve El dato ingresado en valor no es un numero (a))');
        $this->runTest($test, 'FunctionsConvertions',   'porcentaje',       [0.65],         'string',  '(0.65 -> Devuelve 65 %)');
        //--------------------- numeroApalabras ---------------------
        $this->runTest($test, 'FunctionsConvertions',   'numeroApalabras',  [''],           'string',  '(""        -> Devuelve Sin datos ingresados en numero)');
        $this->runTest($test, 'FunctionsConvertions',   'numeroApalabras',  ['a'],          'string',  '("a"       -> Devuelve El dato ingresado en numero no es un numero (a))');
        $this->runTest($test, 'FunctionsConvertions',   'numeroApalabras',  [250000000],    'string',  '(250000000 -> Devuelve doscientos cincuenta millones)');

        /**********  FunctionsDataDate  **********/
        //--------------------- fechaCompleta ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fechaCompleta',         [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada en Fecha)');
        $this->runTest($test, 'FunctionsDataDate',   'fechaCompleta',         ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado en Fecha no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fechaCompleta',         ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve Enero 01 del 2024)');
        //--------------------- fechaCompletaAlt ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fechaCompletaAlt',      [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada en Fecha)');
        $this->runTest($test, 'FunctionsDataDate',   'fechaCompletaAlt',      ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado en Fecha no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fechaCompletaAlt',      ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve 01 de Enero de 2024)');
        //--------------------- diaMes ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'diaMes',                [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada en Fecha)');
        $this->runTest($test, 'FunctionsDataDate',   'diaMes',                ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado en Fecha no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'diaMes',                ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve 01 Enero)');
        //--------------------- fechaEstandar ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fechaEstandar',         [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada en Fecha)');
        $this->runTest($test, 'FunctionsDataDate',   'fechaEstandar',         ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado en Fecha no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fechaEstandar',         ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve 01-01-2024)');
        //--------------------- fechaEstandarCorta ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fechaEstandarCorta',    [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada en Fecha)');
        $this->runTest($test, 'FunctionsDataDate',   'fechaEstandarCorta',    ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado en Fecha no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fechaEstandarCorta',    ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve 01-01-24)');
        //--------------------- fechaNormalizada ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fechaNormalizada',      [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada en Fecha)');
        $this->runTest($test, 'FunctionsDataDate',   'fechaNormalizada',      ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado en Fecha no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fechaNormalizada',      ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve 2024-01-01)');
        //--------------------- fechaArchivos ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fechaArchivos',         [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada en Fecha)');
        $this->runTest($test, 'FunctionsDataDate',   'fechaArchivos',         ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado en Fecha no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fechaArchivos',         ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve 20240101)');
        //--------------------- fechaMesAno ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fechaMesAno',           [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada en Fecha)');
        $this->runTest($test, 'FunctionsDataDate',   'fechaMesAno',           ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado en Fecha no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fechaMesAno',           ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve Enero del 2024)');
        //--------------------- fecha2NdiaMes ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NdiaMes',         [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada en Fecha)');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NdiaMes',         ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado en Fecha no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NdiaMes',         ['2024-01-02'],             'string',  '("2024-01-02" -> Devuelve 2)');
        //--------------------- fecha2NdiaMesCon0 ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NdiaMesCon0',     [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada en Fecha)');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NdiaMesCon0',     ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado en Fecha no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NdiaMesCon0',     ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve 01)');
        //--------------------- fecha2NDiaSemana ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NDiaSemana',      [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada en Fecha)');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NDiaSemana',      ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado en Fecha no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NDiaSemana',      ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve 1)');
        //--------------------- fecha2NombreDia ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NombreDia',       [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada en Fecha)');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NombreDia',       ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado en Fecha no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NombreDia',       ['2024-01-02'],             'string',  '("2024-01-02" -> Devuelve Martes)');
        //--------------------- fecha2NSemana ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NSemana',         [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada en Fecha)');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NSemana',         ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado en Fecha no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NSemana',         ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve 01)');
        //--------------------- fecha2NMes ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NMes',            [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada en Fecha)');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NMes',            ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado en Fecha no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NMes',            ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve 1)');
        //--------------------- fecha2NombreMes ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NombreMes',       [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada en Fecha)');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NombreMes',       ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado en Fecha no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NombreMes',       ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve Enero)');
        //--------------------- fecha2NombreMesCorto ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NombreMesCorto',  [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada en Fecha)');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NombreMesCorto',  ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado en Fecha no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NombreMesCorto',  ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve Ene)');
        //--------------------- fecha2Ano ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fecha2Ano',             [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada en Fecha)');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2Ano',             ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado en Fecha no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2Ano',             ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve 2024)');
        //--------------------- fechaGringa ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fechaGringa',           [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada en Fecha)');
        $this->runTest($test, 'FunctionsDataDate',   'fechaGringa',           ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado en Fecha no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fechaGringa',           ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve January 01 2024)');
        //--------------------- fechaUltimoDiaMes ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fechaUltimoDiaMes',     [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada en Fecha)');
        $this->runTest($test, 'FunctionsDataDate',   'fechaUltimoDiaMes',     ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado en Fecha no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fechaUltimoDiaMes',     ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve 2024-01-31)');
        //--------------------- fullDate ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fullDate',              [''],                       'string',  '(""                    -> Devuelve Sin fecha ingresada en Fecha)');
        $this->runTest($test, 'FunctionsDataDate',   'fullDate',              ['a'],                      'string',  '("a"                   -> Devuelve El dato ingresado en Fecha no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fullDate',              ['2023-12-12 13:17:58'],    'string',  '("2023-12-12 13:17:58" -> Devuelve Diciembre 12 del 2023 13:17:58)');

        /**********  FunctionsDataNumbers  **********/
        //--------------------- Cantidades ---------------------
        $this->runTest($test, 'FunctionsDataNumbers',   'Cantidades',                  ['', 1],           'string',  '("" - 1        -> Devuelve 0)');
        $this->runTest($test, 'FunctionsDataNumbers',   'Cantidades',                  [1250.85, ''],     'string',  '(1250.85 - ""  -> Devuelve Sin datos ingresados en n_decimales)');
        $this->runTest($test, 'FunctionsDataNumbers',   'Cantidades',                  ['a', 1],          'string',  '("a"  - 1      -> Devuelve El dato ingresado en valor no es un numero (a))');
        $this->runTest($test, 'FunctionsDataNumbers',   'Cantidades',                  [1250.85, 'a'],    'string',  '(1250.85 - "a" -> Devuelve El dato ingresado en n_decimales no es un numero (a))');
        $this->runTest($test, 'FunctionsDataNumbers',   'Cantidades',                  [1250.85, 6],      'string',  '(1250.85       -> Devuelve 1.250,850000)');
        //--------------------- nDoc ---------------------
        $this->runTest($test, 'FunctionsDataNumbers',   'nDoc',                        ['', 7],           'string',  '("" - 7   -> Devuelve Sin datos ingresados en valor)');
        $this->runTest($test, 'FunctionsDataNumbers',   'nDoc',                        [25, ''],          'string',  '(25 - ""  -> Devuelve Sin datos ingresados en n_ceros)');
        $this->runTest($test, 'FunctionsDataNumbers',   'nDoc',                        ['a',7],           'string',  '("a" - 7  -> Devuelve El dato ingresado en valor no es un numero (a))');
        $this->runTest($test, 'FunctionsDataNumbers',   'nDoc',                        [25, 'a'],         'string',  '(25 - "a" -> Devuelve El dato ingresado en n_ceros no es un numero (a))');
        $this->runTest($test, 'FunctionsDataNumbers',   'nDoc',                        [25, 7],           'string',  '(25       -> Devuelve 0000025)');
        //--------------------- Valores ---------------------
        $this->runTest($test, 'FunctionsDataNumbers',   'Valores',                     ['', 1],           'string',  '("" - 1           -> Devuelve 0)');
        $this->runTest($test, 'FunctionsDataNumbers',   'Valores',                     [1500.85565, ''],  'string',  '(1500.85565 - ""  -> Devuelve Sin datos ingresados en n_decimales)');
        $this->runTest($test, 'FunctionsDataNumbers',   'Valores',                     ['a', 1],          'string',  '("a" - 1          -> Devuelve El dato ingresado en valor no es un numero (a))');
        $this->runTest($test, 'FunctionsDataNumbers',   'Valores',                     [1500.85565, 'a'], 'string',  '(1500.85565 - "a" -> Devuelve El dato ingresado en n_decimales no es un numero (a))');
        $this->runTest($test, 'FunctionsDataNumbers',   'Valores',                     [1500.85565, 2],   'string',  '(1500.85565       -> Devuelve $ 1.500,86)');
        //--------------------- valoresEnteros ---------------------
        $this->runTest($test, 'FunctionsDataNumbers',   'valoresEnteros',              [''],              'string',  '(""      -> Devuelve 0)');
        $this->runTest($test, 'FunctionsDataNumbers',   'valoresEnteros',              ['a'],             'string',  '("a"     -> Devuelve El dato ingresado en valor no es un numero (a))');
        $this->runTest($test, 'FunctionsDataNumbers',   'valoresEnteros',              [1500.85],         'float',   '(1500.85 -> Devuelve 1501)');
        //--------------------- valoresComparables ---------------------
        $this->runTest($test, 'FunctionsDataNumbers',   'valoresComparables',          [''],              'string',  '(""      -> Devuelve 0)');
        $this->runTest($test, 'FunctionsDataNumbers',   'valoresComparables',          ['a'],             'string',  '("a"     -> Devuelve El dato ingresado en valor no es un numero (a))');
        $this->runTest($test, 'FunctionsDataNumbers',   'valoresComparables',          [1500.85],         'float',   '(1500.85 -> Devuelve 1501)');
        //--------------------- valoresTruncados ---------------------
        $this->runTest($test, 'FunctionsDataNumbers',   'valoresTruncados',            [''],              'string',  '(""      -> Devuelve 0)');
        $this->runTest($test, 'FunctionsDataNumbers',   'valoresTruncados',            ['a'],             'string',  '("a"     -> Devuelve El dato ingresado en valor no es un numero (a))');
        $this->runTest($test, 'FunctionsDataNumbers',   'valoresTruncados',            [1500.85],         'float',   '(1500.85 -> Devuelve 1500)');
        //--------------------- cantidadesDecimalesJustos ---------------------
        $this->runTest($test, 'FunctionsDataNumbers',   'cantidadesDecimalesJustos',   [''],              'string',  '(""         -> Devuelve 0)');
        $this->runTest($test, 'FunctionsDataNumbers',   'cantidadesDecimalesJustos',   ['a'],             'string',  '("a"        -> Devuelve El dato ingresado en valor no es un numero (a))');
        $this->runTest($test, 'FunctionsDataNumbers',   'cantidadesDecimalesJustos',   [1500.85000],      'float',   '(1500.85000 -> Devuelve 1500.85)');
        //--------------------- cantidadesExcel ---------------------
        $this->runTest($test, 'FunctionsDataNumbers',   'cantidadesExcel',             [''],              'string',  '(""      -> Devuelve 0)');
        $this->runTest($test, 'FunctionsDataNumbers',   'cantidadesExcel',             ['a'],             'string',  '("a"     -> Devuelve El dato ingresado en valor no es un numero (a))');
        $this->runTest($test, 'FunctionsDataNumbers',   'cantidadesExcel',             [1500.85],         'string',  '(1500.85 -> Devuelve 1500,85)');
        //--------------------- cantidadesGoogle ---------------------
        $this->runTest($test, 'FunctionsDataNumbers',   'cantidadesGoogle',            [''],              'string',  '(""      -> Devuelve 0)');
        $this->runTest($test, 'FunctionsDataNumbers',   'cantidadesGoogle',            ['a'],             'string',  '("a"     -> Devuelve El dato ingresado en valor no es un numero (a))');
        $this->runTest($test, 'FunctionsDataNumbers',   'cantidadesGoogle',            [1500.85],         'string',  '(1500.85 -> Devuelve 1500.85)');
        //--------------------- formatPhone ---------------------
        $this->runTest($test, 'FunctionsDataNumbers',   'formatPhone',                 [''],              'string',  '(""           -> Devuelve Sin datos ingresados en Fono)');
        $this->runTest($test, 'FunctionsDataNumbers',   'formatPhone',                 ['a'],             'string',  '("a"          -> Devuelve Numero demasiado corto, tiene 1 numeros y debe tener al menos 9)');
        $this->runTest($test, 'FunctionsDataNumbers',   'formatPhone',                 ['+56911265984'],  'string',  '(+56911265984 -> Devuelve (+56) 9 1126 5984)');
        //--------------------- normalizarPhone ---------------------
        $this->runTest($test, 'FunctionsDataNumbers',   'normalizarPhone',             [''],              'string',  '(""           -> Devuelve Sin datos ingresados en Fono)');
        $this->runTest($test, 'FunctionsDataNumbers',   'normalizarPhone',             ['a'],             'string',  '("a"          -> Devuelve Numero demasiado corto, tiene 1 numeros y debe tener al menos 9)');
        $this->runTest($test, 'FunctionsDataNumbers',   'normalizarPhone',             ['+56911265984'],  'string',  '(+56911265984 -> Devuelve +56911265984)');
        //--------------------- numberInit0 ---------------------
        $this->runTest($test, 'FunctionsDataNumbers',   'numberInit0',                 [''],              'string',  '(""   -> Devuelve 0)');
        $this->runTest($test, 'FunctionsDataNumbers',   'numberInit0',                 ['a'],             'string',  '("a"  -> Devuelve El dato ingresado en valor no es un numero (a))');
        $this->runTest($test, 'FunctionsDataNumbers',   'numberInit0',                 [1],               'string',  '(1    -> Devuelve 01)');

        /**********  FunctionsDataOperations  **********/
        //--------------------- dividirHoras ---------------------
        $this->runTest($test, 'FunctionsDataOperations',   'dividirHoras',        ['', 4],                                                'string',  '("" / 4         -> Devuelve Sin datos ingresados en hora)');
        $this->runTest($test, 'FunctionsDataOperations',   'dividirHoras',        ['04:00:00', ''],                                       'string',  '(04:00:00 / ""  -> Devuelve Sin datos ingresados en divisor)');
        $this->runTest($test, 'FunctionsDataOperations',   'dividirHoras',        ['a', 4],                                               'string',  '("a" / 4        -> Devuelve El dato ingresado en hora no es una hora (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'dividirHoras',        ['04:00:00', 'a'],                                      'string',  '(04:00:00 / "a" -> Devuelve El dato ingresado en divisor no es un numero (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'dividirHoras',        ['04:00:00', 4],                                        'int',     '(04:00:00 / 4   -> Devuelve 60)');
        //--------------------- multiplicarHoras ---------------------
        $this->runTest($test, 'FunctionsDataOperations',   'multiplicarHoras',    ['', 4],                                                'string',  '("" * 4         -> Devuelve Sin datos ingresados en hora)');
        $this->runTest($test, 'FunctionsDataOperations',   'multiplicarHoras',    ['04:00:00', ''],                                       'string',  '(04:00:00 * ""  -> Devuelve Sin datos ingresados en multiplicador)');
        $this->runTest($test, 'FunctionsDataOperations',   'multiplicarHoras',    ['a', 4],                                               'string',  '("a" * 4        -> Devuelve El dato ingresado en hora no es una hora (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'multiplicarHoras',    ['04:00:00', 'a'],                                      'string',  '(04:00:00 * "a" -> Devuelve El dato ingresado en multiplicador no es un numero (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'multiplicarHoras',    ['04:00:00', 4],                                        'string',  '(04:00:00 * 4   -> Devuelve 16:00:00)');
        //--------------------- restarhoras ---------------------
        $this->runTest($test, 'FunctionsDataOperations',   'restarhoras',         ['', '14:00:00'],                                       'string',  '("" - 14:00:00       -> Devuelve Sin datos ingresados en hora)');
        $this->runTest($test, 'FunctionsDataOperations',   'restarhoras',         ['07:00:00', ''],                                       'string',  '(07:00:00 - ""       -> Devuelve Sin datos ingresados en horaResta)');
        $this->runTest($test, 'FunctionsDataOperations',   'restarhoras',         ['a', '14:00:00'],                                      'string',  '("a" - 14:00:00      -> Devuelve El dato ingresado en hora no es una hora (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'restarhoras',         ['07:00:00', 'a'],                                      'string',  '(07:00:00 - "a"      -> Devuelve El dato ingresado en horaResta no es una hora (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'restarhoras',         ['07:00:00', '14:00:00'],                               'string',  '(07:00:00 - 14:00:00 -> Devuelve 07:00:00)');
        //--------------------- sumarhoras ---------------------
        $this->runTest($test, 'FunctionsDataOperations',   'sumarhoras',          ['', '14:00:00'],                                       'string',  '("" + 14:00:00       -> Devuelve Sin datos ingresados en hora)');
        $this->runTest($test, 'FunctionsDataOperations',   'sumarhoras',          ['07:00:00', ''],                                       'string',  '(07:00:00 + ""       -> Devuelve Sin datos ingresados en horaSuma)');
        $this->runTest($test, 'FunctionsDataOperations',   'sumarhoras',          ['a', '14:00:00'],                                      'string',  '("a" + 14:00:00      -> Devuelve El dato ingresado en hora no es una hora (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'sumarhoras',          ['07:00:00', 'a'],                                      'string',  '(07:00:00 + "a"      -> Devuelve El dato ingresado en horaSuma no es una hora (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'sumarhoras',          ['07:00:00', '14:00:00'],                               'string',  '(07:00:00 + 14:00:00 -> Devuelve 21:00:00)');
        //--------------------- sumarDias ---------------------
        $this->runTest($test, 'FunctionsDataOperations',   'sumarDias',           ['', 5],                                                'string',  '("" + 5           -> Devuelve Sin datos ingresados en Fecha)');
        $this->runTest($test, 'FunctionsDataOperations',   'sumarDias',           ['2019-01-02', ''],                                     'string',  '(2019-01-02 + ""  -> Devuelve Sin datos ingresados en nDias)');
        $this->runTest($test, 'FunctionsDataOperations',   'sumarDias',           ['a', 5],                                               'string',  '("a" + 5          -> Devuelve El dato ingresado en Fecha no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'sumarDias',           ['2019-01-02', 'a'],                                    'string',  '(2019-01-02 + "a" -> Devuelve El dato ingresado en nDias no es un numero (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'sumarDias',           ['2019-01-02', 5],                                      'string',  '(2019-01-02 + 5   -> Devuelve 2019-01-07)');
        //--------------------- restarDias ---------------------
        $this->runTest($test, 'FunctionsDataOperations',   'restarDias',          ['', 5],                                                'string',  '("" - 5           -> Devuelve Sin datos ingresados en Fecha)');
        $this->runTest($test, 'FunctionsDataOperations',   'restarDias',          ['2019-01-07', ''],                                     'string',  '(2019-01-02 - ""  -> Devuelve Sin datos ingresados en nDias)');
        $this->runTest($test, 'FunctionsDataOperations',   'restarDias',          ['a', 5],                                               'string',  '("a" - 5          -> Devuelve El dato ingresado en Fecha no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'restarDias',          ['2019-01-07', 'a'],                                    'string',  '(2019-01-02 - "a" -> Devuelve El dato ingresado en nDias no es un numero (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'restarDias',          ['2019-01-07', 5],                                      'string',  '(2019-01-02 - 5   -> Devuelve 2019-01-02)');
        //--------------------- obtenerEdad ---------------------
        $this->runTest($test, 'FunctionsDataOperations',   'obtenerEdad',         [''],                                                   'string',  '(2022-01-01 -> Devuelve Sin datos ingresados en fNacimiento)');
        $this->runTest($test, 'FunctionsDataOperations',   'obtenerEdad',         ['a'],                                                  'string',  '(2022-01-01 -> Devuelve El dato ingresado en fNacimiento no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'obtenerEdad',         ['2022-01-01'],                                         'string',  '(2022-01-01 -> (a la fecha '.$this->Server->fechaActual().') Devuelve '.$FNC_DataOperations->obtenerEdad('2022-01-01').')');
        //--------------------- obtenerNumeroAnos ---------------------
        $this->runTest($test, 'FunctionsDataOperations',   'obtenerNumeroAnos',   [''],                                                   'string',  '(2022-01-01 -> Devuelve Sin datos ingresados en fNacimiento)');
        $this->runTest($test, 'FunctionsDataOperations',   'obtenerNumeroAnos',   ['a'],                                                  'string',  '(2022-01-01 -> Devuelve El dato ingresado en fNacimiento no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'obtenerNumeroAnos',   ['2022-01-01'],                                         'string',  '(2022-01-01 -> (a la fecha '.$this->Server->fechaActual().') Devuelve '.$FNC_DataOperations->obtenerNumeroAnos('2022-01-01').')');
        //--------------------- diasTranscurridos ---------------------
        $this->runTest($test, 'FunctionsDataOperations',   'diasTranscurridos',   ['', '2019-02-02'],                                     'string',  '(2019-01-02 - 2019-02-02 -> Devuelve Sin datos ingresados en fechaInicio)');
        $this->runTest($test, 'FunctionsDataOperations',   'diasTranscurridos',   ['2019-01-02', ''],                                     'string',  '(2019-01-02 - 2019-02-02 -> Devuelve Sin datos ingresados en fechaTermino)');
        $this->runTest($test, 'FunctionsDataOperations',   'diasTranscurridos',   ['a', '2019-02-02'],                                    'string',  '(2019-01-02 - 2019-02-02 -> Devuelve El dato ingresado en fechaInicio no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'diasTranscurridos',   ['2019-01-02', 'a'],                                    'string',  '(2019-01-02 - 2019-02-02 -> Devuelve El dato ingresado en fechaTermino no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'diasTranscurridos',   ['2019-01-02', '2019-02-02'],                           'int',     '(2019-01-02 - 2019-02-02 -> Devuelve 31)');
        //--------------------- horasTranscurridas ---------------------
        $this->runTest($test, 'FunctionsDataOperations',   'horasTranscurridas',  ['', '', '', ''],                                       'string',  '(""                                        -> Devuelve Sin datos ingresados en fechaInicio)');
        $this->runTest($test, 'FunctionsDataOperations',   'horasTranscurridas',  ['a', 'a', 'a', 'a'],                                   'string',  '("a"                                       -> Devuelve El dato ingresado en fechaInicio no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'horasTranscurridas',  ['2019-01-02', '2019-02-02', '14:00:00', '07:00:00'],   'string',  '(2019-01-02 14:00:00 - 2019-02-02 07:00:00 -> Devuelve 737:00:00)');
        //--------------------- diferenciaMeses ---------------------
        $this->runTest($test, 'FunctionsDataOperations',   'diferenciaMeses',     ['', ''],                                               'string',  '(""-""                 -> Devuelve Sin datos ingresados en fechaInicio)');
        $this->runTest($test, 'FunctionsDataOperations',   'diferenciaMeses',     ['a', 'a'],                                             'string',  '("a"-"a"               -> Devuelve El dato ingresado en fechaInicio no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'diferenciaMeses',     ['2019-01-02', '2019-02-02'],                           'int',     '(2019-01-02-2019-02-02 -> Devuelve 1)');

        /**********  FunctionsDataText  **********/
        //--------------------- cortar ---------------------
        $this->runTest($test, 'FunctionsDataText',    'cortar',                      ['', 10],                                               'string',  '("" - 10               -> Devuelve Sin datos ingresados en texto)');
        $this->runTest($test, 'FunctionsDataText',    'cortar',                      ['Lorem ipsum dolor sit amet, consectetur', ''],        'string',  '("Lorem ipsum.." - ""  -> Devuelve Sin datos ingresados en cuantos)');
        $this->runTest($test, 'FunctionsDataText',    'cortar',                      ['Lorem ipsum dolor sit amet, consectetur', 'a'],       'string',  '("Lorem ipsum.." - "a" -> Devuelve El dato ingresado en cuantos no es un numero (a))');
        $this->runTest($test, 'FunctionsDataText',    'cortar',                      ['Lorem ipsum dolor sit amet, consectetur', 10],        'string',  '("Lorem ipsum.." - 10  -> Devuelve Lorem ipsu...)');
        //--------------------- eliminarVerificadorRut ---------------------
        $this->runTest($test, 'FunctionsDataText',    'eliminarVerificadorRut',      [''],                                                   'string',  '(16.029.464-7 -> Devuelve Sin datos ingresados en Rut)');
        $this->runTest($test, 'FunctionsDataText',    'eliminarVerificadorRut',      ['16.029.464-7'],                                       'string',  '(16.029.464-7 -> Devuelve 16029464)');
        //--------------------- limpiarString ---------------------
        $this->runTest($test, 'FunctionsDataText',    'limpiarString',               [''],                                                   'string',  '(""                                              -> Devuelve Sin datos ingresados en texto)');
        $this->runTest($test, 'FunctionsDataText',    'limpiarString',               ['Lorem ipsum\n dolor sit amet\n, consectetur\r'],      'string',  '("Lorem ipsum\n dolor sit amet\n, consectetur\r" -> Devuelve Lorem ipsum dolor sit amet consectetur)');
        //--------------------- reemplazarEspaciosxGuion ---------------------
        $this->runTest($test, 'FunctionsDataText',    'reemplazarEspaciosxGuion',    [''],                                                   'string',  '(""                                        -> Devuelve Sin datos ingresados en texto)');
        $this->runTest($test, 'FunctionsDataText',    'reemplazarEspaciosxGuion',    ['Lorem ipsum dolor sit amet, consectetur'],            'string',  '("Lorem ipsum dolor sit amet, consectetur" -> Devuelve Lorem_ipsum_dolor_sit_amet,_consectetur)');
        //--------------------- sanitizarTexto ---------------------
        $this->runTest($test, 'FunctionsDataText',    'sanitizarTexto',              [''],                                                   'string',  '(""                                        -> Devuelve Sin datos ingresados en texto)');
        $this->runTest($test, 'FunctionsDataText',    'sanitizarTexto',              ['Lorem ipsum dolor sit amet, consectetur'],            'string',  '("Lorem ipsum dolor sit amet, consectetur" -> Devuelve Lorem ipsum dolor sit amet, consectetur)');
        //--------------------- desanitizarTexto ---------------------
        $this->runTest($test, 'FunctionsDataText',    'desanitizarTexto',            [''],                                                   'string',  '(""                                        -> Devuelve Sin datos ingresados en texto)');
        $this->runTest($test, 'FunctionsDataText',    'desanitizarTexto',            ['Lorem ipsum dolor sit amet, consectetur'],            'string',  '("Lorem ipsum dolor sit amet, consectetur" -> Devuelve Lorem ipsum dolor sit amet, consectetur)');
        //--------------------- limpiezaTexto ---------------------
        $this->runTest($test, 'FunctionsDataText',    'limpiezaTexto',               [""],                                                   'string',  '(""             -> Devuelve Sin datos ingresados en texto)');
        $this->runTest($test, 'FunctionsDataText',    'limpiezaTexto',               ["blabla'bla"],                                         'string',  '("blabla%27bla" -> Devuelve blabla%27bla)');
        //--------------------- limpiezaTexto ---------------------
        $this->runTest($test, 'FunctionsDataText',    'limpiarOracion',              [""],                                                   'string',  '(""                -> Devuelve Sin datos ingresados en texto)');
        $this->runTest($test, 'FunctionsDataText',    'limpiarOracion',              ["ÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ"],                                    'string',  '("ÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ" -> Devuelve eeeeiiiidnooooo)');
        //--------------------- contarPalabrasCensuradas ---------------------
        $this->runTest($test, 'FunctionsDataText',    'contarPalabrasCensuradas',    [''],                                                   'string',  '(""                                   -> Devuelve Sin datos ingresados en texto)');
        $this->runTest($test, 'FunctionsDataText',    'contarPalabrasCensuradas',    ['Lorem ipsum dolor sit amet, fuck d'],                 'int',     '("Lorem ipsum dolor sit amet, fuck d" -> Devuelve 1)');
        //--------------------- filtrarPalabrasCensuradas ---------------------
        $this->runTest($test, 'FunctionsDataText',    'filtrarPalabrasCensuradas',   [''],                                                   'string',  '(""                                   -> Devuelve Sin datos ingresados en texto)');
        $this->runTest($test, 'FunctionsDataText',    'filtrarPalabrasCensuradas',   ['Lorem ipsum dolor sit amet, fuck d'],                 'string',  '("Lorem ipsum dolor sit amet, fuck d" -> Devuelve lorem ipsum dolor sit amet, **** d)');
        //--------------------- tituloMenu ---------------------
        $this->runTest($test, 'FunctionsDataText',    'tituloMenu',                  [''],                                                   'string',  '(""            -> Devuelve Sin datos ingresados en texto)');
        $this->runTest($test, 'FunctionsDataText',    'tituloMenu',                  ['01 - Titulo'],                                        'string',  '("01 - Titulo" -> Devuelve Titulo)');
        //--------------------- buscarPalabraYExtraer ---------------------
        $this->runTest($test, 'FunctionsDataText',    'buscarPalabraYExtraer',       ['', ''],                                               'array',  '( -> Devuelve {"success":false,"data":"","error":"Sin datos ingresados en cadena"})');
        $this->runTest($test, 'FunctionsDataText',    'buscarPalabraYExtraer',       ['', 'ipsum'],                                          'array',  '( -> Devuelve {"success":false,"data":"","error":"Sin datos ingresados en cadena"})');
        $this->runTest($test, 'FunctionsDataText',    'buscarPalabraYExtraer',       ['Lorem ipsum dolor sit amet', ''],                     'array',  '( -> Devuelve {"success":false,"data":"","error":"Sin datos ingresados en palabra"})');
        $this->runTest($test, 'FunctionsDataText',    'buscarPalabraYExtraer',       ['Lorem ipsum dolor sit amet', 'ipsum'],                'array',  '( -> Devuelve {"success":true,"data":{"posicion":6,"extraido":" dolor sit amet"}})');
        //--------------------- dividirTexto ---------------------
        $this->runTest($test, 'FunctionsDataText',    'dividirTexto',                ['', ''],                                               'array',  '( -> Devuelve {"izquierda":"Sin datos ingresados en texto","derecha":""})');
        $this->runTest($test, 'FunctionsDataText',    'dividirTexto',                ['', ':'],                                              'array',  '( -> Devuelve {"izquierda":"Sin datos ingresados en texto","derecha":""})');
        $this->runTest($test, 'FunctionsDataText',    'dividirTexto',                ['clave:valor', ''],                                    'array',  '( -> Devuelve {"izquierda":"Sin datos ingresados en divisor","derecha":""})');
        $this->runTest($test, 'FunctionsDataText',    'dividirTexto',                ['clave:valor', ':'],                                   'array',  '( -> Devuelve {"izquierda":"clave","derecha":"valor"})');

        /**********  FunctionsDataTime  **********/
        //--------------------- formatoHoraEstandar ---------------------
        $this->runTest($test, 'FunctionsDataTime',   'formatoHoraEstandar',   [''],       'string',  '(""    -> Devuelve Sin datos ingresados en Hora)');
        $this->runTest($test, 'FunctionsDataTime',   'formatoHoraEstandar',   ['a'],      'string',  '("a"   -> Devuelve El dato ingresado en Hora no es una hora (a))');
        $this->runTest($test, 'FunctionsDataTime',   'formatoHoraEstandar',   ['01:01'],  'string',  '(01:01 -> Devuelve 01:01)');
        //--------------------- formatoHoraProgramada ---------------------
        $this->runTest($test, 'FunctionsDataTime',   'formatoHoraProgramada', [''],       'string',  '(""    -> Devuelve Sin datos ingresados en Hora)');
        $this->runTest($test, 'FunctionsDataTime',   'formatoHoraProgramada', ['a'],      'string',  '("a"   -> Devuelve El dato ingresado en Hora no es una hora (a))');
        $this->runTest($test, 'FunctionsDataTime',   'formatoHoraProgramada', ['01:01'],  'string',  '(01:01 -> Devuelve 01:01:00)');
        //--------------------- formatoHoraArchivos ---------------------
        $this->runTest($test, 'FunctionsDataTime',   'formatoHoraArchivos',   [''],       'string',  '(""    -> Devuelve Sin datos ingresados en Hora)');
        $this->runTest($test, 'FunctionsDataTime',   'formatoHoraArchivos',   ['a'],      'string',  '("a"   -> Devuelve El dato ingresado en Hora no es una hora (a))');
        $this->runTest($test, 'FunctionsDataTime',   'formatoHoraArchivos',   ['01:01'],  'string',  '(01:01 -> Devuelve 010100)');

        /**********  FunctionsDataValidations  **********/
        //--------------------- validarRut ---------------------
        $this->runTest($test, 'FunctionsDataValidations',   'validarRut',              [''],                        'bool',  '(""           -> Devuelve false)');
        $this->runTest($test, 'FunctionsDataValidations',   'validarRut',              ['a'],                       'bool',  '("a"          -> Devuelve false)');
        $this->runTest($test, 'FunctionsDataValidations',   'validarRut',              ['16.029.464-7'],            'bool',  '(16.029.464-7 -> Devuelve true)');
        //--------------------- validarEmail ---------------------
        $this->runTest($test, 'FunctionsDataValidations',   'validarEmail',            [''],                        'bool',  '(""         -> Devuelve false)');
        $this->runTest($test, 'FunctionsDataValidations',   'validarEmail',            ['a'],                       'bool',  '("a"        -> Devuelve false)');
        $this->runTest($test, 'FunctionsDataValidations',   'validarEmail',            ['asd@asd.cl'],              'bool',  '(asd@asd.cl -> Devuelve true)');
        //--------------------- validarNumero ---------------------
        $this->runTest($test, 'FunctionsDataValidations',   'validarNumero',           [''],                        'bool',  '(""  -> Devuelve false)');
        $this->runTest($test, 'FunctionsDataValidations',   'validarNumero',           ['a'],                       'bool',  '("a" -> Devuelve false)');
        $this->runTest($test, 'FunctionsDataValidations',   'validarNumero',           ['25'],                      'bool',  '(25  -> Devuelve true)');
        //--------------------- validarPatente ---------------------
        $this->runTest($test, 'FunctionsDataValidations',   'validarPatente',          [''],                        'bool',  '(""     -> Devuelve false)');
        $this->runTest($test, 'FunctionsDataValidations',   'validarPatente',          ['a'],                       'bool',  '("a"    -> Devuelve false)');
        $this->runTest($test, 'FunctionsDataValidations',   'validarPatente',          ['au1825'],                  'bool',  '(au1825 -> Devuelve true)');
        //--------------------- validarURL ---------------------
        $this->runTest($test, 'FunctionsDataValidations',   'validarURL',              [''],                        'bool',  '(""                    -> Devuelve false)');
        $this->runTest($test, 'FunctionsDataValidations',   'validarURL',              ['a'],                       'bool',  '("a"                   -> Devuelve false)');
        $this->runTest($test, 'FunctionsDataValidations',   'validarURL',              ['https://www.google.cl'],   'bool',  '(https://www.google.cl -> Devuelve true)');
        //--------------------- validarHora ---------------------
        $this->runTest($test, 'FunctionsDataValidations',   'validarHora',             [''],                        'bool',  '(""       -> Devuelve false)');
        $this->runTest($test, 'FunctionsDataValidations',   'validarHora',             ['a'],                       'bool',  '("a"      -> Devuelve false)');
        $this->runTest($test, 'FunctionsDataValidations',   'validarHora',             ['16:24:00'],                'bool',  '(16:24:00 -> Devuelve true)');
        //--------------------- validarFecha ---------------------
        $this->runTest($test, 'FunctionsDataValidations',   'validarFecha',            [''],                        'bool',  '(""         -> Devuelve false)');
        $this->runTest($test, 'FunctionsDataValidations',   'validarFecha',            ['a'],                       'bool',  '("a"        -> Devuelve false)');
        $this->runTest($test, 'FunctionsDataValidations',   'validarFecha',            ['1900-01-01'],              'bool',  '(1900-01-01 -> Devuelve true)');
        //--------------------- validarEntero ---------------------
        $this->runTest($test, 'FunctionsDataValidations',   'validarEntero',           [''],                        'bool',  '(""  -> Devuelve false)');
        $this->runTest($test, 'FunctionsDataValidations',   'validarEntero',           ['a'],                       'bool',  '("a" -> Devuelve false)');
        $this->runTest($test, 'FunctionsDataValidations',   'validarEntero',           [16],                        'bool',  '(16  -> Devuelve true)');
        //--------------------- validarDispositivoMovil ---------------------
        $this->runTest($test, 'FunctionsDataValidations',   'validarDispositivoMovil', [],                          'bool',  '(""  -> Devuelve false)');
        //--------------------- validarLargoMinimo ---------------------
        $this->runTest($test, 'FunctionsDataValidations',   'validarLargoMinimo',      ['', 10],                    'bool',  '("" - 10                -> Devuelve false)');
        $this->runTest($test, 'FunctionsDataValidations',   'validarLargoMinimo',      ['Lorem ipsum dolor', ''],   'bool',  '(Lorem ipsum dolor - "" -> Devuelve false)');
        $this->runTest($test, 'FunctionsDataValidations',   'validarLargoMinimo',      ['Lorem ipsum dolor', 'a'],  'bool',  '(Lorem ipsum dolor - "a"-> Devuelve false)');
        $this->runTest($test, 'FunctionsDataValidations',   'validarLargoMinimo',      ['Lorem ipsum dolor', 10],   'bool',  '(Lorem ipsum dolor - 10 -> Devuelve true)');
        //--------------------- validarLargoMaximo ---------------------
        $this->runTest($test, 'FunctionsDataValidations',   'validarLargoMaximo',      ['', 10],                    'bool',  '("" - 10     -> Devuelve false)');
        $this->runTest($test, 'FunctionsDataValidations',   'validarLargoMaximo',      ['Lorem', ''],               'bool',  '(Lorem - ""  -> Devuelve false)');
        $this->runTest($test, 'FunctionsDataValidations',   'validarLargoMaximo',      ['Lorem', 'a'],              'bool',  '(Lorem - "a" -> Devuelve false)');
        $this->runTest($test, 'FunctionsDataValidations',   'validarLargoMaximo',      ['Lorem', 10],               'bool',  '(Lorem - 10  -> Devuelve true)');
        //--------------------- checkData ---------------------
        // Tipo 7: valida numero entero (dato correcto)
        $this->runTest($test, 'FunctionsDataValidations',   'checkData',               [[], '123', 'campo', 7],                                                                                    'array',  '([], "123", "campo", 7                   -> Devuelve {"nErrors":0,"alerts":""})');
        // Tipo 1: valida pertenencia a un conjunto de opciones (dato correcto)
        $this->runTest($test, 'FunctionsDataValidations',   'checkData',               [['tipo'=>[1,2]], [['name'=>'tipo','value'=>1,'label'=>'Tipo']], 'formulario', 1],                          'array',  '(["tipo"=>[1,2]], value 1, 1             -> Devuelve {"nErrors":0,"alerts":""})');
        // Tipo 1: valida pertenencia a un conjunto de opciones (dato fuera de rango)
        $this->runTest($test, 'FunctionsDataValidations',   'checkData',               [['tipo'=>[1,2]], [['name'=>'tipo','value'=>9,'label'=>'Tipo']], 'formulario', 1],                          'array',  '(["tipo"=>[1,2]], value 9, 1             -> Devuelve {"nErrors":1,"alerts":"\r\n <div class="alert alert-danger alert-white alert-dismissible fade show" role="alert">\r\n <div class="icon"><i class="bi bi-exclamation-circle me-1"></i></div>La configuración Tipo (9) entregada no está dentro de las opciones<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>\r\n </div>"})');
        // Tipo 3: ejecuta dinamicamente un metodo de validacion interno
        $this->runTest($test, 'FunctionsDataValidations',   'checkData',               [[], [['method'=>'validarEmail','value'=>'asd','label'=>'Email','msg'=>'no es valido']], 'formulario', 3],  'array',  '([], metodos dinamicos, "formulario", 3  -> Devuelve {"nErrors":1,"alerts":"\r\n <div class="alert alert-danger alert-white alert-dismissible fade show" role="alert">\r\n <div class="icon"><i class="bi bi-exclamation-circle me-1"></i></div>El valor ingresado en Email (asd) en <strong>formulario</strong> no es valido<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>\r\n </div>"})');

        /**********  FunctionsLocation  **********/
        $this->runTest($test, 'FunctionsLocation',                'calcularDistancia',           ['', '', '', ''],                                       'string',  '(""                                             -> Devuelve Sin datos ingresados en latitude1)');
        $this->runTest($test, 'FunctionsLocation',                'calcularDistancia',           ['a', 'a', 'a', 'a'],                                   'string',  '("a"                                            -> Devuelve El dato ingresado en latitude1 no es un numero (a))');
        $this->runTest($test, 'FunctionsLocation',                'calcularDistancia',           [-40.807289, -72.634907, -42.176560, -73.425923],       'float',   '(-40.807289, -72.634907, -42.176560, -73.425923 -> Devuelve 165.89718855602)');
        //--------------------- getGeocodeData ---------------------
        // NOTA: Solo se cubren las validaciones previas (sin acceso a red). La rama que consulta
        // a Google Geocoding requiere una ApiKey valida y salida a Internet, por lo que no se ejecuta aqui.
        $this->runTest($test, 'FunctionsLocation',                'getGeocodeData',              ['', ''],                                               'string',  '("","" -> Devuelve No ha ingresado una direccion)');
        $this->runTest($test, 'FunctionsLocation',                'getGeocodeData',              ['Av. Providencia, Santiago', ''],                      'string',  '("Av. Providencia","" -> Devuelve No ha ingresado una ApiKey)');
        //--------------------- geocodeAddress ---------------------
        // NOTA: Solo se cubre la validacion de entrada. La busqueda real usa Nominatim (OpenStreetMap)
        // y depende de salida a Internet, por lo que no se ejecuta aqui.
        $this->runTest($test, 'FunctionsLocation',                'geocodeAddress',              [''],                                                   'string',  '("" -> Devuelve Sin datos ingresados en ubicacion)');
        //--------------------- normalizarDirecciones ---------------------
        $this->runTest($test, 'FunctionsLocation',                'normalizarDirecciones',       ['Av. Vicuña Mackenna N° 1234'],                        'string',  '("Av. Vicuña Mackenna N° 1234" -> Devuelve AV VICUNA MACKENNA 1234)');



        /**********  FunctionsSecurityCodification  **********/
        // Datos dinamicos para las pruebas de ida y vuelta: las comprobaciones originales usaban
        // cadenas fijas generadas por versiones anteriores del algoritmo (ya no decodificables).
        $FNC_Codification    = new FunctionsSecurityCodification;
        $Test_SimpleEncode   = $FNC_Codification->simpleEncode('hola', 'passkey');
        $Test_EncryptDecrypt = $FNC_Codification->encryptDecrypt('encrypt', 5008);
        //--------------------- simpleEncode ---------------------
        // NOTA: el metodo retorna array ['success'=>bool, 'data'/'error'=>...].
        $this->runTest($test, 'FunctionsSecurityCodification',    'simpleEncode',                ["", "passkey"],                                        'array',  '("", "passkey"     -> Devuelve {"success":false,"error":"Sin datos ingresados"})');
        $this->runTest($test, 'FunctionsSecurityCodification',    'simpleEncode',                ["hola", "passkey"],                                    'array',  '("hola", "passkey" -> Devuelve {"success":true,"data":"<cadena Base64 URL-safe>"})');
        //--------------------- simpleDecode ---------------------
        $this->runTest($test, 'FunctionsSecurityCodification',    'simpleDecode',                ["", "passkey"],                                        'array',  '("", "passkey"                   -> Devuelve {"success":false,"error":"Sin datos ingresados"})');
        $this->runTest($test, 'FunctionsSecurityCodification',    'simpleDecode',                [$Test_SimpleEncode['data'], "passkey"],                'array',  '(simpleEncode("hola"), "passkey" -> Devuelve {"success":true,"data":"hola"})');
        //--------------------- generateServerSpecificHash ---------------------
        $this->runTest($test, 'FunctionsSecurityCodification',    'generateServerSpecificHash',  [],                                                     'array',  '( -> Devuelve {"success":true,"data":"734dfe6a3d4ab4effe619c7daaff240329fc29b5c1d65a4f903ae084e2381532"})');
        //--------------------- encryptDecrypt ---------------------
        $this->runTest($test, 'FunctionsSecurityCodification',    'encryptDecrypt',              ['encrypt',''],                                         'array',  '("encrypt", ""   -> Devuelve {"success":false,"error":"Sin datos ingresados"})');
        $this->runTest($test, 'FunctionsSecurityCodification',    'encryptDecrypt',              ['encrypt',5008],                                       'array',  '("encrypt", 5008 -> Devuelve {"success":true,"data":"WR12OFL7RSPTf1wQALeq4OH4FdL_EQINLyGmlG9_n-Q"})');
        //--------------------- encryptDecrypt ---------------------
        $this->runTest($test, 'FunctionsSecurityCodification',    'encryptDecrypt',              ['decrypt',''],                                         'array',  '("decrypt", ""                  -> Devuelve {"success":false,"error":"Sin datos ingresados"})');
        $this->runTest($test, 'FunctionsSecurityCodification',    'encryptDecrypt',              ['decrypt', $Test_EncryptDecrypt['data']],              'array',  '(encryptDecrypt("encrypt",5008) -> Devuelve {"success":true,"data":"5008"})');

        /**********  FunctionsSecurityPasswords  **********/
        // NOTA: generarPassword(), caracteresRandom(), tokenBin2Hex() y hashCreate() retornan
        // array ['success'=>bool, 'data'/'error'=>...]; hashVerify() retorna bool.
        //--------------------- generarPassword ---------------------
        $this->runTest($test, 'FunctionsSecurityPasswords', 'generarPassword',      ['20','alfanumerico'],         'array',  '("","alfanumerico"   -> Devuelve array con password por defecto (longitud invalida) en "data")');
        $this->runTest($test, 'FunctionsSecurityPasswords', 'generarPassword',      [10,''],                     'array',  '("10",""             -> Devuelve array de error por tipo invalido)');
        $this->runTest($test, 'FunctionsSecurityPasswords', 'generarPassword',      ['a','alfanumerico'],        'array',  '("a","alfanumerico"  -> Devuelve array con password por defecto (longitud no numerica) en "data")');
        $this->runTest($test, 'FunctionsSecurityPasswords', 'generarPassword',      [10,'a'],                    'array',  '("10","a"            -> Devuelve array de error por tipo invalido)');
        $this->runTest($test, 'FunctionsSecurityPasswords', 'generarPassword',      [10,'alfanumerico'],         'array',  '("10","alfanumerico" -> Devuelve array con la password generada en "data")');
        //--------------------- caracteresRandom ---------------------
        $this->runTest($test, 'FunctionsSecurityPasswords', 'caracteresRandom',     ['', '', '', ''],            'array',  '("","","",""            -> Devuelve array de error por longitud vacia)');
        $this->runTest($test, 'FunctionsSecurityPasswords', 'caracteresRandom',     ['a', true, false, false],   'array',  '("a", true, false, false -> Devuelve array de error por longitud no numerica)');
        $this->runTest($test, 'FunctionsSecurityPasswords', 'caracteresRandom',     [16, 'a', 'a', 'a'],         'array',  '(16, "a", "a", "a"      -> Devuelve array de error por lecturaAmigable invalida)');
        $this->runTest($test, 'FunctionsSecurityPasswords', 'caracteresRandom',     [16, true, false, false],    'array',  '(16, true, false, false -> Devuelve array con la cadena aleatoria en "data")');
        //--------------------- tokenBin2Hex ---------------------
        $this->runTest($test, 'FunctionsSecurityPasswords', 'tokenBin2Hex',         [''],                        'array',  '(""  -> Devuelve {"success":false,"error":"Sin datos ingresados en longitud"})');
        $this->runTest($test, 'FunctionsSecurityPasswords', 'tokenBin2Hex',         ['a'],                       'array',  '("a" -> Devuelve {"success":false,"error":"El dato ingresado en longitud no es un numero (a)"})');
        $this->runTest($test, 'FunctionsSecurityPasswords', 'tokenBin2Hex',         [25],                        'array',  '(25  -> Devuelve {"success":true,"data":"<token hexadecimal>"})');
        //--------------------- hashCreate ---------------------
        $this->runTest($test, 'FunctionsSecurityPasswords', 'hashCreate',           [''],                        'array',  '(""        -> Devuelve {"success":false,"error":"Sin datos ingresados en Texto"})');
        $this->runTest($test, 'FunctionsSecurityPasswords', 'hashCreate',           ['palabra'],                 'array',  '("palabra" -> Devuelve {"success":true,"data":"<hash BCRYPT>"})');
        //--------------------- hashVerify ---------------------
        $this->runTest($test, 'FunctionsSecurityPasswords', 'hashVerify',           ['', ''],                                                                     'bool',    '("",""                  -> Devuelve false)');
        $this->runTest($test, 'FunctionsSecurityPasswords', 'hashVerify',           ['palabra', '$2y$12$pd1.kBABacsBwq8YXNDieuqNELrjJiq68kXCFtHoaj7IwqljDLdj6'],  'bool',    '("palabra","$2y$12$..." -> Devuelve true)');

        /**********  FunctionsDataText  **********/
        $this->runTest($test, 'FunctionsServerClient',   'getClientIp',              [],   'string',  '( -> Devuelve '.$this->ServerClient->getClientIp().')');
        // NOTA: la comprobacion original (sin argumentos) se conserva comentada; se agrega una
        // activa con cabecera explicita para que la lectura sea determinista en contexto HTTP.
        //$this->runTest($test, 'FunctionsServerClient',   'getClientIpAlternative',   [],   'string',  '( -> Devuelve '.$this->ServerClient->getClientIpAlternative().')');
        $this->runTest($test, 'FunctionsServerClient',   'getClientIpAlternative',   ['REMOTE_ADDR'],   'string',  '(REMOTE_ADDR -> Devuelve '.$this->ServerClient->getClientIpAlternative('REMOTE_ADDR').')');
        $this->runTest($test, 'FunctionsServerClient',   'getBrowser',               [],   'string',  '( -> Devuelve '.$this->ServerClient->getBrowser().')');
        $this->runTest($test, 'FunctionsServerClient',   'getOperatingSystem',       [],   'string',  '( -> Devuelve '.$this->ServerClient->getOperatingSystem().')');

        /**********  FunctionsServerServer  **********/
        $this->runTest($test, 'FunctionsServerServer',   'fechaActual',            [],  'string',  '( -> Devuelve '.$this->Server->fechaActual().')');
        $this->runTest($test, 'FunctionsServerServer',   'fechaActualAlternative', [],  'string',  '( -> Devuelve '.$this->Server->fechaActualAlternative().')');
        $this->runTest($test, 'FunctionsServerServer',   'horaActual',             [],  'string',  '( -> Devuelve '.$this->Server->horaActual().')');
        $this->runTest($test, 'FunctionsServerServer',   'horaActualAlternative',  [],  'string',  '( -> Devuelve '.$this->Server->horaActualAlternative().')');
        $this->runTest($test, 'FunctionsServerServer',   'diaActual',              [],  'string',  '( -> Devuelve '.$this->Server->diaActual().')');
        $this->runTest($test, 'FunctionsServerServer',   'semanaActual',           [],  'string',  '( -> Devuelve '.$this->Server->semanaActual().')');
        $this->runTest($test, 'FunctionsServerServer',   'mesActual',              [],  'string',  '( -> Devuelve '.$this->Server->mesActual().')');
        $this->runTest($test, 'FunctionsServerServer',   'anoActual',              [],  'string',  '( -> Devuelve '.$this->Server->anoActual().')');
        //--------------------- tareasServer ---------------------
        // NOTA: Solo se cubren las validaciones de entrada. Los casos validos ejecutan comandos
        // reales en el sistema operativo (iptables, wget), por lo que no se ejecutan por ser destructivos.
        $this->runTest($test, 'FunctionsServerServer',   'tareasServer',           ['', 1],         'array',  '("", 1 -> Devuelve error por tarea vacia)');
        $this->runTest($test, 'FunctionsServerServer',   'tareasServer',           ['no-es-ip', 1], 'array',  '("no-es-ip", 1 -> Devuelve error por dato invalido)');
        $this->runTest($test, 'FunctionsServerServer',   'tareasServer',           ['no-es-url', 2],'array',  '("no-es-url", 2 -> Devuelve error por dato invalido)');
        //--------------------- indicesServer ---------------------
        $this->runTest($test, 'FunctionsServerServer',   'indicesServer',          [],              'object', '( -> Devuelve objeto con indices de $_SERVER)');
        //--------------------- getParentPath ---------------------
        $this->runTest($test, 'FunctionsServerServer',   'getParentPath',          [__DIR__, 1],    'string', '(__DIR__, 1 -> Devuelve el directorio padre)');
        $this->runTest($test, 'FunctionsServerServer',   'getParentPath',          ['', 1],         'string', '("", 1 -> Devuelve error por falta de ruta)');
        //--------------------- isWritableDirectory ---------------------
        $this->runTest($test, 'FunctionsServerServer',   'isWritableDirectory',    [sys_get_temp_dir()], 'array', '(sys_get_temp_dir() -> Devuelve estado del directorio)');
        $this->runTest($test, 'FunctionsServerServer',   'isWritableDirectory',    [''],            'array',  '("" -> Devuelve error por falta de ruta)');
        //--------------------- ensureDirectoryExists ---------------------
        $this->runTest($test, 'FunctionsServerServer',   'ensureDirectoryExists',  [sys_get_temp_dir()], 'array', '(sys_get_temp_dir() -> Devuelve directorio existente)');
        //--------------------- ensurePermissions755 ---------------------
        $this->runTest($test, 'FunctionsServerServer',   'ensurePermissions755',   [__FILE__],      'array',  '(__FILE__ -> Devuelve estado de permisos)');
        //--------------------- canWrite ---------------------
        $this->runTest($test, 'FunctionsServerServer',   'canWrite',               [sys_get_temp_dir()], 'bool', '(sys_get_temp_dir() -> Devuelve true/false segun escritura)');
        $this->runTest($test, 'FunctionsServerServer',   'canWrite',               [''],            'bool',   '("" -> Devuelve false)');
        //--------------------- writeEnvFile ---------------------
        // NOTA: Solo se cubre la validacion de directorio inexistente. La escritura real no se ejecuta
        // para no generar archivos .env temporales en el servidor.
        $this->runTest($test, 'FunctionsServerServer',   'writeEnvFile',           [sys_get_temp_dir().'/coreengine_no_existe/'.uniqid().'.env', ['APP_ENV'=>'test']], 'array', '(ruta inexistente -> Devuelve error de directorio)');
        //--------------------- writeConfigClassFile ---------------------
        // NOTA: Solo se cubren las validaciones (directorio inexistente y extension invalida). La
        // escritura real no se ejecuta para no modificar archivos de configuracion de la plataforma.
        $this->runTest($test, 'FunctionsServerServer',   'writeConfigClassFile',   ['/ruta/inexistente_'.uniqid().'/ConfigDataBase.php', ['MySQL_ADMIN'=>['host'=>'localhost']]], 'array', '(ruta inexistente -> Devuelve error de directorio)');
        $this->runTest($test, 'FunctionsServerServer',   'writeConfigClassFile',   [sys_get_temp_dir().'/coreengine_'.uniqid().'.txt', ['X'=>['a'=>1]]], 'array', '(.txt -> Devuelve error de extension)');
        //--------------------- removeDirectoryRecursive ---------------------
        // NO TESTEABLE: el metodo contiene un error (`$$src` en lugar de `$src`), lo que provoca un
        // warning "Undefined variable" y que siempre retorne el error de ruta vacia; ademas su
        // proposito es destructivo (borrado recursivo), por lo que no se ejecuta en las pruebas.
        // $this->runTest($test, 'FunctionsServerServer',   'removeDirectoryRecursive', ['/ruta/a/borrar'], 'array', '( -> Devuelve estado del borrado)');



        /**********  FunctionsServerWeb  **********/
        //--------------------- hashVerify ---------------------
        //$this->runTest($test, 'FunctionsServerWeb',   'obtenerInfoIp', ["200.120.163.36", "city"],          'string',  '("200.120.163.36", "city"          -> Devuelve asd)');
        //$this->runTest($test, 'FunctionsServerWeb',   'obtenerInfoIp', ["200.120.163.36", "region"],        'string',  '("200.120.163.36", "region"        -> Devuelve asd)');
        //$this->runTest($test, 'FunctionsServerWeb',   'obtenerInfoIp', ["200.120.163.36", "regionCode"],    'string',  '("200.120.163.36", "regionCode"    -> Devuelve asd)');
        //$this->runTest($test, 'FunctionsServerWeb',   'obtenerInfoIp', ["200.120.163.36", "countryCode"],   'string',  '("200.120.163.36", "countryCode"   -> Devuelve asd)');
        //$this->runTest($test, 'FunctionsServerWeb',   'obtenerInfoIp', ["200.120.163.36", "countryName"],   'string',  '("200.120.163.36", "countryName"   -> Devuelve asd)');
        //$this->runTest($test, 'FunctionsServerWeb',   'obtenerInfoIp', ["200.120.163.36", "continentName"], 'string',  '("200.120.163.36", "continentName" -> Devuelve asd)');
        //--------------------- hashVerify ---------------------
        $this->runTest($test, 'FunctionsServerWeb',   'getBaseUrl', [],   'string',  '("200.120.163.36", "city"  -> Devuelve http://localhost/coreEngine/admin/public/)');
        //--------------------- obtenerInfoIp ---------------------
        // NOTA: Solo se cubren las validaciones previas (sin acceso a red). La consulta real a
        // geoplugin.net requiere salida a Internet, por lo que no se ejecuta aqui.
        $this->runTest($test, 'FunctionsServerWeb',   'obtenerInfoIp', ['', 'city'],                    'array',  '("", "city" -> Devuelve error por falta de IP)');
        $this->runTest($test, 'FunctionsServerWeb',   'obtenerInfoIp', ['no-es-ip', 'city'],             'array',  '("no-es-ip", "city" -> Devuelve error por IP invalida)');
        $this->runTest($test, 'FunctionsServerWeb',   'obtenerInfoIp', ['200.120.163.36', ''],           'array',  '("200.120.163.36", "" -> Devuelve error por falta de purpose)');
        // NOTA: las siguientes 6 comprobaciones de obtenerInfoIp realizan consultas reales a
        // geoplugin.net y su retorno real es array (['success'=>..., 'data'=>...]), no string.
        // Se dejan comentadas para no ejecutar peticiones externas en la pagina de pruebas.
        //$this->runTest($test, 'FunctionsServerWeb',   'obtenerInfoIp', ["200.120.163.36", "city"],          'string',  '("200.120.163.36", "city"          -> Devuelve asd)');
        //$this->runTest($test, 'FunctionsServerWeb',   'obtenerInfoIp', ["200.120.163.36", "region"],        'string',  '("200.120.163.36", "region"        -> Devuelve asd)');
        //$this->runTest($test, 'FunctionsServerWeb',   'obtenerInfoIp', ["200.120.163.36", "regionCode"],    'string',  '("200.120.163.36", "regionCode"    -> Devuelve asd)');
        //$this->runTest($test, 'FunctionsServerWeb',   'obtenerInfoIp', ["200.120.163.36", "countryCode"],   'string',  '("200.120.163.36", "countryCode"   -> Devuelve asd)');
        //$this->runTest($test, 'FunctionsServerWeb',   'obtenerInfoIp', ["200.120.163.36", "countryName"],   'string',  '("200.120.163.36", "countryName"   -> Devuelve asd)');
        //$this->runTest($test, 'FunctionsServerWeb',   'obtenerInfoIp', ["200.120.163.36", "continentName"], 'string',  '("200.120.163.36", "continentName" -> Devuelve asd)');
        //--------------------- callExternalApi ---------------------
        // NOTA: Solo se cubre una llamada sin URL (fallo controlado de cURL). Las llamadas reales
        // dependen de servicios externos y credenciales, por lo que no se ejecutan aqui.
        $this->runTest($test, 'FunctionsServerWeb',   'callExternalApi', [['url'=>'']],                 'array',  '(["url"=>""] -> Devuelve arreglo con status/error)');
        //--------------------- obtenerDatosXML ---------------------
        // NOTA: Solo se cubren las validaciones de entrada. La descarga real del XML requiere una
        // URL HTTPS accesible, por lo que no se ejecuta aqui.
        $this->runTest($test, 'FunctionsServerWeb',   'obtenerDatosXML', [''],                          'array',  '("" -> Devuelve error por falta de URL)');
        $this->runTest($test, 'FunctionsServerWeb',   'obtenerDatosXML', ['http://www.ejemplo.com/a.xml'], 'array', '("http://..." -> Devuelve error por no ser HTTPS)');
        $this->runTest($test, 'FunctionsServerWeb',   'obtenerDatosXML', ['no-es-url'],                 'array',  '("no-es-url" -> Devuelve error por URL invalida)');




        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Funciones',
            'PageDescription' => 'Testeos de las funciones.',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            'TableTitle'      => 'Pruebas Unitarias de las funciones',
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
            /*===========   Funcionalidad   ===========*/
            'Fnc_DataText'      => $this->DataText,
            /*=========== Datos Consultados ===========*/
            'test'            => $test->results(),
        ];

        /************************************/
        // Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/testeos-funciones.php');

    }

    /*******************************************************************/
    // Helper function for testing
    /*******************************************************************/
    public function runTest($test, $class, $method, $args, $expectedType, $desc, $extra = null) {

        /************************************/
        //Instancias
        // Se instancia unicamente la clase solicitada y se reutiliza durante la ejecucion,
        // evitando crear las 14 clases en cada una de las pruebas.
        static $instances = [];
        if (!isset($instances[$class])) {
            $instances[$class] = new $class;
        }
        $instance = $instances[$class];

        /************************************/
        //Ejecucion del metodo solicitado
        $data = call_user_func_array([$instance, $method], $args);

        /************************************/
        //Comprobaciones (mismas validaciones, mensajes seguros para datos no escalares)
        // Se contempla el retorno de array/object para no forzar una conversion invalida a string.
        // bool y null se etiquetan explicitamente porque (string) false === '' y (string) null === '',
        // lo que dejaria la comprobacion sin texto para la vista (y activaba el mensaje interno
        // "Sin datos ingresados en texto" del validador en lugar del valor devuelto).
        if (is_string($data)) {
            $dataLabel = $data;
        } elseif (is_bool($data)) {
            $dataLabel = $data ? 'true' : 'false';
        } elseif ($data === null) {
            $dataLabel = 'null';
        } elseif (is_scalar($data)) {
            $dataLabel = (string) $data;
        } else {
            $dataLabel = gettype($data);
        }
        $test->expect(method_exists($instance, $method), "$method aaa $desc"); //Solo la clase y el dato
        $test->expect($data !== null && $data !== '', $dataLabel); //Respuesta
        $typeCheck = "is_$expectedType";
        $test->expect($typeCheck($data), gettype($data), $data); //Tipo dato

        return $data;
    }

    /*******************************************************************/
    // Envio de correo por SMTP (solo un correo, con uno o varios receptores)
    /*******************************************************************/
    public function SMTPMail($f3){
        /************************************/
        //Llamo a las otras clases
        $TypeSend     = 'send_SMTPMail';

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Envio Correo SMTP',
            'PageDescription' => 'Testeos de las funciones.',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            'TableTitle'      => 'Pruebas Unitarias de envio de Correos',
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
            /*===========   Funcionalidad   ===========*/
            'Fnc_FormInputs'   => $this->FormInputs,
            'TypeSend'         => $TypeSend,
        ];

        /************************************/
        // Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/testeos-Mail.php');
    }

    /*******************************************************************/
    // Envio de correo por Gmail (solo un correo, con uno o varios receptores)
    /*******************************************************************/
    public function GMail($f3){
        /************************************/
        //Llamo a las otras clases
        $TypeSend     = 'send_GMail';

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Envio Correo GMail',
            'PageDescription' => 'Testeos de las funciones.',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            'TableTitle'      => 'Pruebas Unitarias de envio de Correos',
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
            /*===========   Funcionalidad   ===========*/
            'Fnc_FormInputs'   => $this->FormInputs,
            'TypeSend'         => $TypeSend,
        ];

        /************************************/
        // Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/testeos-Mail.php');
    }

    /*******************************************************************/
    // Envio de correo por Sending Blue
    /*******************************************************************/
    public function SendingBlue($f3){
        /************************************/
        //Llamo a las otras clases
        $TypeSend     = 'send_SendingBlue';

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Envio Correo SendingBlue',
            'PageDescription' => 'Testeos de las funciones.',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            'TableTitle'      => 'Pruebas Unitarias de envio de Correos',
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
            /*===========   Funcionalidad   ===========*/
            'Fnc_FormInputs'   => $this->FormInputs,
            'TypeSend'         => $TypeSend,
        ];

        /************************************/
        // Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/testeos-Mail.php');
    }

    /*******************************************************************/
    // Envio de whatsapp
    /*******************************************************************/
    public function Whatsapp($f3){
        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Envio de mensaje por Whatsapp',
            'PageDescription' => 'Testeos de las funciones.',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            'TableTitle'      => 'Pruebas Unitarias de envio de mensaje por Whatsapp',
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
            /*===========   Funcionalidad   ===========*/
            'Fnc_FormInputs'   => $this->FormInputs,
        ];

        /************************************/
        // Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/testeos-Whatsapp.php');
    }

    /*******************************************************************/
    // Seleccion de la plantilla
    /*******************************************************************/
    public function testMailTemplateSelect($f3){
        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Testeos email template',
            'PageDescription' => 'Testeos email template.',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            'TableTitle'      => 'Testeos email template',
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
        ];

        /************************************/
        // Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/testeos-MailTemplateSelect.php');
    }

    /*******************************************************************/
    // Testeo de plantillas
    /*******************************************************************/
    public function testMailTemplate($f3, $params){
        /************************************/
        // Se agrega respuesta
        $Post = [
            'Asunto'  => 'Cambio de contraseña',
            'Hacia'   => 'asd@asd.cl',
            'Mensaje' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
        ];

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'Asunto,Hacia,Mensaje',
            'template'  => $params['id'],
            'Post'      => $Post,
        ];

        // Recupera la información del usuario y del sistema almacenada en la sesión actual
        $UserData = $f3->get('SESSION.DataInfo');
        $BASE     = $f3->get('BASE');

        /************************************/
        $MailTemplate = $this->Base_TestMailTemplate($UserData, $BASE, $query);

        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Testeos email template',
            'PageDescription' => 'Testeos email template.',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            'TableTitle'      => 'Testeos email template',
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
            /*===========   Funcionalidad   ===========*/
            'MailTemplate'   => $MailTemplate,
        ];

        /************************************/
        // Se instancia la vista
        $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/testeos-MailTemplate.php');
    }

    /******************************************************************************/
    /*                                  DATOS                                     */
    /******************************************************************************/
    /*******************************************************************/
    // Envio de correo por SMTP (solo un correo, con uno o varios receptores)
    /*******************************************************************/
    public function send_SMTPMail($f3){

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'Asunto,Hacia,Mensaje',
            'template'  => 1,
            'Post'      => $_POST,
        ];
        // Recupera la información del usuario y del sistema almacenada en la sesión actual
        $UserData = $f3->get('SESSION.DataInfo');
        $BASE     = $f3->get('BASE');
        // Ejecuto la query
        echo $this->Base_SMTPMail($UserData, $BASE, $query);
    }

    /*******************************************************************/
    // Envio de correo por Gmail (solo un correo, con uno o varios receptores)
    /*******************************************************************/
    public function send_GMail($f3){

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'Asunto,Hacia,Mensaje',
            'template'  => 1,
            'Post'      => $_POST,
        ];
        // Recupera la información del usuario y del sistema almacenada en la sesión actual
        $UserData = $f3->get('SESSION.DataInfo');
        $BASE     = $f3->get('BASE');
        // Ejecuto la query
        echo $this->Base_GMail($UserData, $BASE, $query);
    }

    /*******************************************************************/
    // Envio de correo por Sending Blue
    /*******************************************************************/
    public function send_SendingBlue($f3){

        /************************************/
        // Se genera la query
        $query = [
            'data'      => 'De_correo,De_nombre,Hacia_correo,Hacia_nombre,Asunto,Mensaje',
            'template'  => 1,
            'Post'      => $_POST,
        ];
        // Recupera la información del usuario y del sistema almacenada en la sesión actual
        $UserData = $f3->get('SESSION.DataInfo');
        $BASE     = $f3->get('BASE');
        // Ejecuto la query
        echo $this->Base_SendingBlue($UserData, $BASE, $query);

    }

    /*******************************************************************/
    // Envio de correo por Sending Blue
    /*******************************************************************/
    public function send_Whatsapp($f3){

        /************************************/
        // Validación del método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Error en el Request Method', 405);
        }

        /************************************/
        // Se generan datos
        $Config['Token']      = $_POST['WhatsappToken'];
        $Config['InstanceId'] = $_POST['WhatsappInstanceId'];
        $Config['Type']       = 1;
        $Config['namespace']  = $_POST['namespace'];
        $Config['template']   = $_POST['template'];
        $WSP_Body['Phone']    = $this->DataNumbers->normalizarPhone($_POST['Fono']);
        $WSP_Body['Titulo']   = $_POST['Titulo'];
        $WSP_Body['Mensaje']  = $_POST['Mensaje'];

        /***************************************/
        //Se envia notificacion
        $Result = $this->Notifications->sendWhatsappTemplate($Config, $WSP_Body);

        /************************************/
        // Si falla la ejecucion, se revierte de inmediato
        if ($Result['status'] === false) {
            Response::error('Error al operar con la Base de Datos', 500, $Result['error']);
        }

        /***************************************/
        // Devuelvo true con código 200 (OK)
        Response::success($Result['success']);

    }

}
