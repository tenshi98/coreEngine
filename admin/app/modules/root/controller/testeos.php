<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class testeos extends ControllerBase {

    /******************************************************************************/
    //Variables
    private $controllerName;
    private $FormInputs;
    private $Server;
    private $Notifications;
    private $DataNumbers;
    private $ServerIA;
    private $DataText;

    /******************************************************************************/
    //Constructor
    public function __construct(){
        /*=========== Se instancian los datos ===========*/
        $DB_conn_1     = Database::getSQLConnection(ConfigData::MySQL_ADMIN);
        $queryBuilder  = new QueryBuilder();
        $checkData     = new CheckData();
        /*================== Instancias =================*/
        $this->controllerName = 'Empty';
		$this->FormInputs     = new UIFormInputs();
		$this->Server         = new FunctionsServerServer();
		$this->Notifications  = new FunctionsServerSocial();
		$this->DataNumbers    = new FunctionsDataNumbers();
		$this->ServerIA       = new FunctionsServerIA();
		$this->DataText       = new FunctionsDataText();
        /*========== Datos para la clase padre ==========*/
        parent::__construct($DB_conn_1, $queryBuilder, $checkData);
    }

    /******************************************************************************/
    /*                                  VISTAS                                    */
    /******************************************************************************/
    /******************************************************************************/
    //controladores
    public function controladores($f3){
        /******************************************/
        //Llamo a las otras clases
        $test   = new Test;

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
        //Se agrega datos post delete
        $Post_3 = [
            'idTest'  => 99999,
        ];

        $DataCheck1 = [
            'emptyData'                 => '',
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
        //Se genera la query
        $query = [
            'data'      => 'Email,Numero,Rut,Patente,Fecha,Hora,Palabra',
            'required'  => 'Email,Numero',
            'unique'    => 'Email,Numero',
            'table'     => 'core_test',
            'Post'      => $Post_1,
        ];
        //Ejecuto la query
        $xParams     = ['DataCheck' => $DataCheck1, 'query' => $query];
        $Base_insert = $this->Base_insert($xParams);
        /******************************/
        //testeos
        $test->expect(is_callable('FunctionsConvertions','Base_insert'),'Base_insert()->Normal es un metodo existente');
        $test->expect(!empty($Base_insert),'Base_insert()->Normal Ha devuelto datos');
        $test->expect(is_string($Base_insert),'Base_insert()->Normal Los datos obtenidos son del tipo '.gettype($Base_insert),$Base_insert);
        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'      => 'Email,Numero,Rut,Patente,Fecha,Hora,Palabra',
            'required'  => 'Email,Numero',
            'unique'    => 'Email,Numero',
            'table'     => 'core_test',
            'Post'      => $Post_1,
        ];
        //Ejecuto la query
        $xParams     = ['DataCheck' => $DataCheck1, 'query' => $query];
        $Base_insert = $this->Base_insert($xParams);
        /******************************/
        //testeos
        $test->expect(is_callable('FunctionsConvertions','Base_insert'),'Base_insert()->Verificar Repetidos es un metodo existente');
        $test->expect(!empty($Base_insert),'Base_insert()->Verificar Repetidos Ha devuelto datos');
        $test->expect(is_array($Base_insert),'Base_insert()->Verificar Repetidos Los datos obtenidos son del tipo '.gettype($Base_insert),$Base_insert);
        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'      => 'Email,Numero,Rut,Patente,Fecha,Hora,Palabra',
            'required'  => 'Email,Numero',
            'unique'    => 'Email,Numero',
            'table'     => 'core_test',
            'Post'      => $Post_1,
        ];
        //Ejecuto la query
        $xParams     = ['DataCheck' => $DataCheck2, 'query' => $query];
        $Base_insert = $this->Base_insert($xParams);
        /******************************/
        //testeos
        $test->expect(is_callable('FunctionsConvertions','Base_insert'),'Base_insert()->Verificar Tipo Dato es un metodo existente');
        $test->expect(!empty($Base_insert),'Base_insert()->Verificar Tipo Dato Ha devuelto datos');
        $test->expect(is_array($Base_insert),'Base_insert()->Verificar Tipo Dato Los datos obtenidos son del tipo '.gettype($Base_insert),$Base_insert);


        /*******************************************************************/
        /*                          Actualizar Datos                       */
        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'      => 'Email,Numero',
            'required'  => 'Email,Numero',
            'unique'    => 'Email,Numero',
            'table'     => 'core_test',
            'where'     => 'idTest',
            'Post'      => $Post_2,
        ];
        //Ejecuto la query
        $xParams     = ['DataCheck' => $DataCheck1, 'query' => $query];
        $Base_update = $this->Base_update($xParams);
        /******************************/
        //testeos
        $test->expect(is_callable('FunctionsConvertions','Base_update'),'Base_update()->Normal es un metodo existente');
        $test->expect(!empty($Base_update),'Base_update()->Normal Ha devuelto datos');
        $test->expect(is_bool($Base_update),'Base_update()->Normal Los datos obtenidos son del tipo '.gettype($Base_update),$Base_update);

        /*******************************************************************/
        /*                          Listar Datos                           */
        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'    => 'Email,Numero,Fecha,Hora,Palabra',
            'table'   => 'core_test',
            'join'    => '',
            'where'   => 'idTest!=0',
            'group'   => '',
            'having'  => '',
            'order'   => 'Email ASC',
            'limit'   => '5'
        ];
        //Ejecuto la query
        $xParams      = ['query' => $query];
        $Base_GetList = $this->Base_GetList($xParams);
        /******************************/
        //testeos
        $test->expect(is_callable('FunctionsConvertions','Base_GetList'),'Base_GetList() es un metodo existente');
        $test->expect(!empty($Base_GetList),'Base_GetList() Ha devuelto datos');
        $test->expect(is_array($Base_GetList),'Base_GetList() Los datos obtenidos son del tipo '.gettype($Base_GetList),$Base_GetList);


        /*******************************************************************/
        /*                           Ver Datos                             */
        /*******************************************************************/
        //Se genera la query
        $query = [
            'data'   => 'Email,Numero,Fecha,Hora,Palabra',
            'table'  => 'core_test',
            'join'   => '',
            'where'  => 'idTest = 1',
            'group'  => '',
            'having' => '',
            'order'  => 'Email ASC'
        ];
        //Ejecuto la query
        $xParams      = ['query' => $query];
        $Base_GetByID = $this->Base_GetByID($xParams);
        /******************************/
        //testeos
        $test->expect(is_callable('FunctionsConvertions','Base_GetByID'),'Base_GetByID() es un metodo existente');
        $test->expect(!empty($Base_GetByID),'Base_GetByID() Ha devuelto datos');
        $test->expect(is_array($Base_GetByID),'Base_GetByID() Los datos obtenidos son del tipo '.gettype($Base_GetByID),$Base_GetByID);


        /*******************************************************************/
        /*                         Eliminar Datos                          */
        /*******************************************************************/
        //Se genera la query
        $query = [
            'files'       => '',
            'table'       => 'core_test',
            'where'       => 'idTest',
            'SubCarpeta'  => '',
            'Post'        => $Post_3
        ];
        //Ejecuto la query
        $xParams     = ['query' => $query];
        $Base_delete = $this->Base_delete($xParams);
        /******************************/
        //testeos
        $test->expect(is_callable('FunctionsConvertions','Base_delete'),'Base_delete() es un metodo existente');
        $test->expect(!empty($Base_delete),'Base_delete() Ha devuelto datos');
        $test->expect(is_bool($Base_delete),'Base_delete() Los datos obtenidos son del tipo '.gettype($Base_delete),$Base_delete);


        //Base_SMTPMail
        //Base_GMail
        //Base_SendingBlue


        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        /******************************************/
        //Datos enviados a la pagina
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

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/testeos-controladores.php');

    }

    /******************************************************************************/
    //controladores
    public function funciones($f3){
        /******************************************/
        //Se abre la libreria de testeos
        $test  = new Test;
        $FNC_DataOperations       = new FunctionsDataOperations;

        /**********  FunctionsConvertions  **********/
        //--------------------- numero2horas ---------------------
        $this->runTest($test, 'FunctionsConvertions',   'numero2horas',     [''],           'string',  '(""  -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsConvertions',   'numero2horas',     ['a'],          'string',  '("a" -> Devuelve El dato ingresado no es un numero)');
        $this->runTest($test, 'FunctionsConvertions',   'numero2horas',     [1.5],          'string',  '(1.5 -> Devuelve 01:30:00)');
        //--------------------- minutos2horas ---------------------
        $this->runTest($test, 'FunctionsConvertions',   'minutos2horas',    [''],           'string',  '(""  -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsConvertions',   'minutos2horas',    ['a'],          'string',  '("a" -> Devuelve El dato ingresado no es un numero)');
        $this->runTest($test, 'FunctionsConvertions',   'minutos2horas',    [65],           'string',  '(65  -> Devuelve 01:05:00)');
        //--------------------- segundos2horas ---------------------
        $this->runTest($test, 'FunctionsConvertions',   'segundos2horas',   [''],           'string',  '(""   -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsConvertions',   'segundos2horas',   ['a'],          'string',  '("a"  -> Devuelve El dato ingresado no es un numero)');
        $this->runTest($test, 'FunctionsConvertions',   'segundos2horas',   [3600],         'string',  '(3600 -> Devuelve 01:00:00)');
        //--------------------- horas2minutos ---------------------
        $this->runTest($test, 'FunctionsConvertions',   'horas2minutos',    [''],           'string',  '(""       -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsConvertions',   'horas2minutos',    ['a'],          'string',  '("a"      -> Devuelve El dato ingresado no es una hora (a))');
        $this->runTest($test, 'FunctionsConvertions',   'horas2minutos',    ['01:05:00'],   'int',     '(01:05:00 -> Devuelve 65)');
        //--------------------- horas2segundos ---------------------
        $this->runTest($test, 'FunctionsConvertions',   'horas2segundos',   [''],           'string',  '(""       -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsConvertions',   'horas2segundos',   ['a'],          'string',  '("a"      -> Devuelve El dato ingresado no es una hora (a))');
        $this->runTest($test, 'FunctionsConvertions',   'horas2segundos',   ['00:30:00'],   'int',     '(00:30:00 -> Devuelve 1800)');
        //--------------------- horas2decimales ---------------------
        $this->runTest($test, 'FunctionsConvertions',   'horas2decimales',  [''],           'string',  '(""       -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsConvertions',   'horas2decimales',  ['a'],          'string',  '("a"      -> Devuelve El dato ingresado no es una hora (a))');
        $this->runTest($test, 'FunctionsConvertions',   'horas2decimales',  ['01:30:00'],   'float',   '(01:30:00 -> Devuelve 1.5)');
        //--------------------- DevolverMes ---------------------
        $this->runTest($test, 'FunctionsConvertions',   'DevolverMes',      [''],           'string',  '(""  -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsConvertions',   'DevolverMes',      ['a'],          'string',  '("a" -> Devuelve Dato fuera de parámetros esperados)');
        $this->runTest($test, 'FunctionsConvertions',   'DevolverMes',      ['Ene'],        'string',  '(Ene -> Devuelve Enero)');
        //--------------------- numero2mes ---------------------
        $this->runTest($test, 'FunctionsConvertions',   'numero2mes',       [''],           'string',  '(""  -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsConvertions',   'numero2mes',       ['a'],          'string',  '("a" -> Devuelve El dato ingresado no es un numero)');
        $this->runTest($test, 'FunctionsConvertions',   'numero2mes',       [1],            'string',  '(1   -> Devuelve Enero)');
        //--------------------- numero2mesCorto ---------------------
        $this->runTest($test, 'FunctionsConvertions',   'numero2mesCorto',  [''],           'string',  '(""  -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsConvertions',   'numero2mesCorto',  ['a'],          'string',  '("a" -> Devuelve El dato ingresado no es un numero)');
        $this->runTest($test, 'FunctionsConvertions',   'numero2mesCorto',  [1],            'string',  '(1   -> Devuelve Ene)');
        //--------------------- numeroNombreDia ---------------------
        $this->runTest($test, 'FunctionsConvertions',   'numeroNombreDia',  [''],           'string',  '(""  -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsConvertions',   'numeroNombreDia',  ['a'],          'string',  '("a" -> Devuelve El dato ingresado no es un numero)');
        $this->runTest($test, 'FunctionsConvertions',   'numeroNombreDia',  [3],            'string',  '(3   -> Devuelve Miercoles)');
        //--------------------- porcentaje ---------------------
        $this->runTest($test, 'FunctionsConvertions',   'porcentaje',       [''],           'string',  '(""   -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsConvertions',   'porcentaje',       ['a'],          'string',  '("a"  -> Devuelve El dato ingresado no es un numero)');
        $this->runTest($test, 'FunctionsConvertions',   'porcentaje',       [0.65],         'string',  '(0.65 -> Devuelve 65 %)');
        //--------------------- numeroApalabras ---------------------
        $this->runTest($test, 'FunctionsConvertions',   'numeroApalabras',  [''],           'string',  '(""        -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsConvertions',   'numeroApalabras',  ['a'],          'string',  '("a"       -> Devuelve El dato ingresado no es un numero)');
        $this->runTest($test, 'FunctionsConvertions',   'numeroApalabras',  [250000000],    'string',  '(250000000 -> Devuelve doscientos cincuenta millones)');

        /**********  FunctionsDataDate  **********/
        //--------------------- fechaCompleta ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fechaCompleta',         [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada)');
        $this->runTest($test, 'FunctionsDataDate',   'fechaCompleta',         ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fechaCompleta',         ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve Enero 01 del 2024)');
        //--------------------- fechaCompletaAlt ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fechaCompletaAlt',      [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada)');
        $this->runTest($test, 'FunctionsDataDate',   'fechaCompletaAlt',      ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fechaCompletaAlt',      ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve 01 de Enero de 2024)');
        //--------------------- diaMes ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'diaMes',                [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada)');
        $this->runTest($test, 'FunctionsDataDate',   'diaMes',                ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'diaMes',                ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve 01 Enero)');
        //--------------------- fechaEstandar ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fechaEstandar',         [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada)');
        $this->runTest($test, 'FunctionsDataDate',   'fechaEstandar',         ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fechaEstandar',         ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve 01-01-2024)');
        //--------------------- fechaEstandarCorta ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fechaEstandarCorta',    [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada)');
        $this->runTest($test, 'FunctionsDataDate',   'fechaEstandarCorta',    ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fechaEstandarCorta',    ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve 01-01-24)');
        //--------------------- fechaNormalizada ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fechaNormalizada',      [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada)');
        $this->runTest($test, 'FunctionsDataDate',   'fechaNormalizada',      ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fechaNormalizada',      ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve 2024-01-01)');
        //--------------------- fechaArchivos ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fechaArchivos',         [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada)');
        $this->runTest($test, 'FunctionsDataDate',   'fechaArchivos',         ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fechaArchivos',         ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve 20240101)');
        //--------------------- fechaMesAno ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fechaMesAno',           [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada)');
        $this->runTest($test, 'FunctionsDataDate',   'fechaMesAno',           ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fechaMesAno',           ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve Enero del 2024)');
        //--------------------- fecha2NdiaMes ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NdiaMes',         [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada)');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NdiaMes',         ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NdiaMes',         ['2024-01-02'],             'string',  '("2024-01-02" -> Devuelve 2)');
        //--------------------- fecha2NdiaMesCon0 ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NdiaMesCon0',     [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada)');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NdiaMesCon0',     ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NdiaMesCon0',     ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve 01)');
        //--------------------- fecha2NDiaSemana ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NDiaSemana',      [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada)');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NDiaSemana',      ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NDiaSemana',      ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve 1)');
        //--------------------- fecha2NombreDia ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NombreDia',       [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada)');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NombreDia',       ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NombreDia',       ['2024-01-02'],             'string',  '("2024-01-02" -> Devuelve Martes)');
        //--------------------- fecha2NSemana ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NSemana',         [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada)');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NSemana',         ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NSemana',         ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve 01)');
        //--------------------- fecha2NMes ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NMes',            [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada)');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NMes',            ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NMes',            ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve 1)');
        //--------------------- fecha2NombreMes ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NombreMes',       [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada)');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NombreMes',       ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NombreMes',       ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve Enero)');
        //--------------------- fecha2NombreMesCorto ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NombreMesCorto',  [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada)');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NombreMesCorto',  ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2NombreMesCorto',  ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve Ene)');
        //--------------------- fecha2Ano ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fecha2Ano',             [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada)');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2Ano',             ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fecha2Ano',             ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve 2024)');
        //--------------------- fechaGringa ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fechaGringa',           [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada)');
        $this->runTest($test, 'FunctionsDataDate',   'fechaGringa',           ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fechaGringa',           ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve January 01 2024)');
        //--------------------- fechaUltimoDiaMes ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fechaUltimoDiaMes',     [''],                       'string',  '(""           -> Devuelve Sin fecha ingresada)');
        $this->runTest($test, 'FunctionsDataDate',   'fechaUltimoDiaMes',     ['a'],                      'string',  '("a"          -> Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fechaUltimoDiaMes',     ['2024-01-01'],             'string',  '("2024-01-01" -> Devuelve 2024-01-31)');
        //--------------------- fullDate ---------------------
        $this->runTest($test, 'FunctionsDataDate',   'fullDate',              [''],                       'string',  '(""                    -> Devuelve Sin fecha ingresada)');
        $this->runTest($test, 'FunctionsDataDate',   'fullDate',              ['a'],                      'string',  '("a"                   -> Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataDate',   'fullDate',              ['2023-12-12 13:17:58'],    'string',  '("2023-12-12 13:17:58" -> Devuelve Diciembre 12 del 2023 13:17:58)');

        /**********  FunctionsDataNumbers  **********/
        //--------------------- Cantidades ---------------------
        $this->runTest($test, 'FunctionsDataNumbers',   'Cantidades',                  ['', 1],           'string',  '(""      -> Devuelve 0)');
        $this->runTest($test, 'FunctionsDataNumbers',   'Cantidades',                  [1250.85, ''],     'string',  '(""      -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataNumbers',   'Cantidades',                  ['a', 1],          'string',  '("a"     -> Devuelve El dato ingresado no es un numero (a))');
        $this->runTest($test, 'FunctionsDataNumbers',   'Cantidades',                  [1250.85, 'a'],    'string',  '("a"     -> Devuelve El dato ingresado no es un numero (a))');
        $this->runTest($test, 'FunctionsDataNumbers',   'Cantidades',                  [1250.85, 6],      'string',  '(1250.85 -> Devuelve 1.250,850000)');
        //--------------------- nDoc ---------------------
        $this->runTest($test, 'FunctionsDataNumbers',   'nDoc',                        ['', 7],           'string',  '(""  -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataNumbers',   'nDoc',                        [25, ''],          'string',  '(""  -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataNumbers',   'nDoc',                        ['a',1],           'string',  '("a" -> Devuelve El dato ingresado no es un numero (a))');
        $this->runTest($test, 'FunctionsDataNumbers',   'nDoc',                        [25, 'a'],         'string',  '("a" -> Devuelve El dato ingresado no es un numero (a))');
        $this->runTest($test, 'FunctionsDataNumbers',   'nDoc',                        [25, 7],           'string',  '(25  -> Devuelve 0000025)');
        //--------------------- Valores ---------------------
        $this->runTest($test, 'FunctionsDataNumbers',   'Valores',                     ['', 1],           'string',  '(""         -> Devuelve 0)');
        $this->runTest($test, 'FunctionsDataNumbers',   'Valores',                     [1500.85565, ''],  'string',  '(""         -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataNumbers',   'Valores',                     ['a', 1],          'string',  '("a"        -> Devuelve El dato ingresado no es un numero (a))');
        $this->runTest($test, 'FunctionsDataNumbers',   'Valores',                     [1500.85565, 'a'], 'string',  '("a"        -> Devuelve El dato ingresado no es un numero (a))');
        $this->runTest($test, 'FunctionsDataNumbers',   'Valores',                     [1500.85565, 2],   'string',  '(1500.85565 -> Devuelve $ 1.500,86)');
        //--------------------- valoresEnteros ---------------------
        $this->runTest($test, 'FunctionsDataNumbers',   'valoresEnteros',              [''],              'string',  '(""      -> Devuelve 0)');
        $this->runTest($test, 'FunctionsDataNumbers',   'valoresEnteros',              ['a'],             'string',  '("a"     -> Devuelve El dato ingresado no es un numero (a))');
        $this->runTest($test, 'FunctionsDataNumbers',   'valoresEnteros',              [1500.85],         'float',   '(1500.85 -> Devuelve 1501)');
        //--------------------- valoresComparables ---------------------
        $this->runTest($test, 'FunctionsDataNumbers',   'valoresComparables',          [''],              'string',  '(""      -> Devuelve 0)');
        $this->runTest($test, 'FunctionsDataNumbers',   'valoresComparables',          ['a'],             'string',  '("a"     -> Devuelve El dato ingresado no es un numero (a))');
        $this->runTest($test, 'FunctionsDataNumbers',   'valoresComparables',          [1500.85],         'float',   '(1500.85 -> Devuelve 1501)');
        //--------------------- valoresTruncados ---------------------
        $this->runTest($test, 'FunctionsDataNumbers',   'valoresTruncados',            [''],              'string',  '(""      -> Devuelve 0)');
        $this->runTest($test, 'FunctionsDataNumbers',   'valoresTruncados',            ['a'],             'string',  '("a"     -> Devuelve El dato ingresado no es un numero (a))');
        $this->runTest($test, 'FunctionsDataNumbers',   'valoresTruncados',            [1500.85],         'float',   '(1500.85 -> Devuelve 1500)');
        //--------------------- cantidadesDecimalesJustos ---------------------
        $this->runTest($test, 'FunctionsDataNumbers',   'cantidadesDecimalesJustos',   [''],              'string',  '(""         -> Devuelve 0)');
        $this->runTest($test, 'FunctionsDataNumbers',   'cantidadesDecimalesJustos',   ['a'],             'string',  '("a"        -> Devuelve El dato ingresado no es un numero (a))');
        $this->runTest($test, 'FunctionsDataNumbers',   'cantidadesDecimalesJustos',   [1500.85000],      'float',   '(1500.85000 -> Devuelve 1500.85)');
        //--------------------- cantidadesExcel ---------------------
        $this->runTest($test, 'FunctionsDataNumbers',   'cantidadesExcel',             [''],              'string',  '(""      -> Devuelve 0)');
        $this->runTest($test, 'FunctionsDataNumbers',   'cantidadesExcel',             ['a'],             'string',  '("a"     -> Devuelve El dato ingresado no es un numero (a))');
        $this->runTest($test, 'FunctionsDataNumbers',   'cantidadesExcel',             [1500.85],         'string',  '(1500.85 -> Devuelve 1500,85)');
        //--------------------- cantidadesGoogle ---------------------
        $this->runTest($test, 'FunctionsDataNumbers',   'cantidadesGoogle',            [''],              'string',  '(""      -> Devuelve 0)');
        $this->runTest($test, 'FunctionsDataNumbers',   'cantidadesGoogle',            ['a'],             'string',  '("a"     -> Devuelve El dato ingresado no es un numero (a))');
        $this->runTest($test, 'FunctionsDataNumbers',   'cantidadesGoogle',            [1500.85],         'string',  '(1500.85 -> Devuelve 1500.85)');
        //--------------------- formatPhone ---------------------
        $this->runTest($test, 'FunctionsDataNumbers',   'formatPhone',                 [''],              'string',  '(""           -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataNumbers',   'formatPhone',                 ['a'],             'string',  '("a"          -> Devuelve Numero demasiado corto, tiene 1 numeros y debe tener al menos 9)');
        $this->runTest($test, 'FunctionsDataNumbers',   'formatPhone',                 ['+56911265984'],  'string',  '(+56911265984 -> Devuelve (+56) 9 1126 5984)');
        //--------------------- normalizarPhone ---------------------
        $this->runTest($test, 'FunctionsDataNumbers',   'normalizarPhone',             [''],              'string',  '(""           -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataNumbers',   'normalizarPhone',             ['a'],             'string',  '("a"          -> Devuelve Numero demasiado corto, tiene 1 numeros y debe tener al menos 9)');
        $this->runTest($test, 'FunctionsDataNumbers',   'normalizarPhone',             ['+56911265984'],  'string',  '(+56911265984 -> Devuelve +56911265984)');
        //--------------------- numberInit0 ---------------------
        $this->runTest($test, 'FunctionsDataNumbers',   'numberInit0',                 [''],              'string',  '(""   -> Devuelve 0)');
        $this->runTest($test, 'FunctionsDataNumbers',   'numberInit0',                 ['a'],             'string',  '("a"  -> Devuelve El dato ingresado no es un numero (a))');
        $this->runTest($test, 'FunctionsDataNumbers',   'numberInit0',                 [1],               'string',  '(1    -> Devuelve 01)');

        /**********  FunctionsDataOperations  **********/
        //--------------------- dividirHoras ---------------------
        $this->runTest($test, 'FunctionsDataOperations',   'dividirHoras',        ['', 4],                                                'string',  '("" / 4         -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataOperations',   'dividirHoras',        ['04:00:00', ''],                                       'string',  '(04:00:00 / ""  -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataOperations',   'dividirHoras',        ['a', 4],                                               'string',  '("a" / 4        -> Devuelve El dato ingresado no es una hora (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'dividirHoras',        ['04:00:00', 'a'],                                      'string',  '(04:00:00 / "a" -> Devuelve El dato ingresado no es un numero (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'dividirHoras',        ['04:00:00', 4],                                        'int',     '(04:00:00 / 4   -> Devuelve 60)');
        //--------------------- multiplicarHoras ---------------------
        $this->runTest($test, 'FunctionsDataOperations',   'multiplicarHoras',    ['', 4],                                                'string',  '("" * 4         -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataOperations',   'multiplicarHoras',    ['04:00:00', ''],                                       'string',  '(04:00:00 * ""  -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataOperations',   'multiplicarHoras',    ['a', 4],                                               'string',  '("a" * 4        -> Devuelve El dato ingresado no es una hora (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'multiplicarHoras',    ['04:00:00', 'a'],                                      'string',  '(04:00:00 * "a" -> Devuelve El dato ingresado no es un numero (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'multiplicarHoras',    ['04:00:00', 4],                                        'string',  '(04:00:00 * 4   -> Devuelve 16:00:00)');
        //--------------------- restarhoras ---------------------
        $this->runTest($test, 'FunctionsDataOperations',   'restarhoras',         ['', '14:00:00'],                                       'string',  '("" - 14:00:00       -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataOperations',   'restarhoras',         ['07:00:00', ''],                                       'string',  '(07:00:00 - ""       -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataOperations',   'restarhoras',         ['a', '14:00:00'],                                      'string',  '("a" - 14:00:00      -> Devuelve El dato ingresado no es una hora (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'restarhoras',         ['07:00:00', 'a'],                                      'string',  '(07:00:00 - "a"      -> Devuelve El dato ingresado no es una hora (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'restarhoras',         ['07:00:00', '14:00:00'],                               'string',  '(07:00:00 - 14:00:00 -> Devuelve 07:00:00)');
        //--------------------- sumarhoras ---------------------
        $this->runTest($test, 'FunctionsDataOperations',   'sumarhoras',          ['', '14:00:00'],                                       'string',  '("" + 14:00:00       -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataOperations',   'sumarhoras',          ['07:00:00', ''],                                       'string',  '(07:00:00 + ""       -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataOperations',   'sumarhoras',          ['a', '14:00:00'],                                      'string',  '("a" + 14:00:00      -> Devuelve El dato ingresado no es una hora (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'sumarhoras',          ['07:00:00', 'a'],                                      'string',  '(07:00:00 + "a"      -> Devuelve El dato ingresado no es una hora (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'sumarhoras',          ['07:00:00', '14:00:00'],                               'string',  '(07:00:00 + 14:00:00 -> Devuelve 21:00:00)');
        //--------------------- sumarDias ---------------------
        $this->runTest($test, 'FunctionsDataOperations',   'sumarDias',           ['', 5],                                                'string',  '("" + 5           -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataOperations',   'sumarDias',           ['2019-01-02', ''],                                     'string',  '(2019-01-02 + ""  -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataOperations',   'sumarDias',           ['a', 5],                                               'string',  '("a" + 5          -> Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'sumarDias',           ['2019-01-02', 'a'],                                    'string',  '(2019-01-02 + "a" -> Devuelve El dato ingresado no es un numero (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'sumarDias',           ['2019-01-02', 5],                                      'string',  '(2019-01-02 + 5   -> Devuelve 2019-01-07)');
        //--------------------- restarDias ---------------------
        $this->runTest($test, 'FunctionsDataOperations',   'restarDias',          ['', 5],                                                'string',  '("" - 5           -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataOperations',   'restarDias',          ['2019-01-07', ''],                                     'string',  '(2019-01-02 - ""  -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataOperations',   'restarDias',          ['a', 5],                                               'string',  '("a" - 5          -> Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'restarDias',          ['2019-01-07', 'a'],                                    'string',  '(2019-01-02 - "a" -> Devuelve El dato ingresado no es un numero (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'restarDias',          ['2019-01-07', 5],                                      'string',  '(2019-01-02 - 5   -> Devuelve 2019-01-02)');
        //--------------------- obtenerEdad ---------------------
        $this->runTest($test, 'FunctionsDataOperations',   'obtenerEdad',         [''],                                                   'string',  '(2022-01-01 -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataOperations',   'obtenerEdad',         ['a'],                                                  'string',  '(2022-01-01 -> Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'obtenerEdad',         ['2022-01-01'],                                         'string',  '(2022-01-01 -> (a la fecha '.$this->Server->fechaActual().') Devuelve '.$FNC_DataOperations->obtenerEdad('2022-01-01').')');
        //--------------------- obtenerNumeroAnos ---------------------
        $this->runTest($test, 'FunctionsDataOperations',   'obtenerNumeroAnos',   [''],                                                   'string',  '(2022-01-01 -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataOperations',   'obtenerNumeroAnos',   ['a'],                                                  'string',  '(2022-01-01 -> Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'obtenerNumeroAnos',   ['2022-01-01'],                                         'string',  '(2022-01-01 -> (a la fecha '.$this->Server->fechaActual().') Devuelve '.$FNC_DataOperations->obtenerNumeroAnos('2022-01-01').')');
        //--------------------- diasTranscurridos ---------------------
        $this->runTest($test, 'FunctionsDataOperations',   'diasTranscurridos',   ['', '2019-02-02'],                                     'string',  '(2019-01-02 - 2019-02-02 -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataOperations',   'diasTranscurridos',   ['2019-01-02', ''],                                     'string',  '(2019-01-02 - 2019-02-02 -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataOperations',   'diasTranscurridos',   ['a', '2019-02-02'],                                    'string',  '(2019-01-02 - 2019-02-02 -> Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'diasTranscurridos',   ['2019-01-02', 'a'],                                    'string',  '(2019-01-02 - 2019-02-02 -> Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'diasTranscurridos',   ['2019-01-02', '2019-02-02'],                           'int',     '(2019-01-02 - 2019-02-02 -> Devuelve 31)');
        //--------------------- horasTranscurridas ---------------------
        $this->runTest($test, 'FunctionsDataOperations',   'horasTranscurridas',  ['', '', '', ''],                                       'string',  '(Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataOperations',   'horasTranscurridas',  ['a', 'a', 'a', 'a'],                                   'string',  '(Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'horasTranscurridas',  ['2019-01-02', '2019-02-02', '14:00:00', '07:00:00'],   'string',  '(Devuelve 737:00:00)');
        //--------------------- diferenciaMeses ---------------------
        $this->runTest($test, 'FunctionsDataOperations',   'diferenciaMeses',     ['', ''],                                               'string',  '(""-""                 -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataOperations',   'diferenciaMeses',     ['a', 'a'],                                             'string',  '("a"-"a"               -> Devuelve El dato ingresado no es una fecha (a))');
        $this->runTest($test, 'FunctionsDataOperations',   'diferenciaMeses',     ['2019-01-02', '2019-02-02'],                           'int',     '(2019-01-02-2019-02-02 -> Devuelve 1)');

        /**********  FunctionsDataText  **********/
        //--------------------- diferenciaMeses ---------------------
        $this->runTest($test, 'FunctionsDataText',    'cortar',                      ['', 10],                                               'string',  '(Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataText',    'cortar',                      ['Lorem ipsum dolor sit amet, consectetur', ''],        'string',  '(Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataText',    'cortar',                      ['Lorem ipsum dolor sit amet, consectetur', 'a'],       'string',  '(Devuelve El dato ingresado no es un numero (a))');
        $this->runTest($test, 'FunctionsDataText',    'cortar',                      ['Lorem ipsum dolor sit amet, consectetur', 10],        'string',  '(Devuelve Lorem ipsu...)');
        //--------------------- diferenciaMeses ---------------------
        $this->runTest($test, 'FunctionsDataText',    'eliminarVerificadorRut',      [''],                                                   'string',  '(16.029.464-7 -> Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataText',    'eliminarVerificadorRut',      ['16.029.464-7'],                                       'string',  '(16.029.464-7 -> Devuelve 16029464)');
        //--------------------- diferenciaMeses ---------------------
        $this->runTest($test, 'FunctionsDataText',    'limpiarString',               [''],                                                   'string',  '(Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataText',    'limpiarString',               ['Lorem ipsum\n dolor sit amet\n, consectetur\r'],      'string',  '(Devuelve Lorem ipsum dolor sit amet consectetur)');
        //--------------------- diferenciaMeses ---------------------
        $this->runTest($test, 'FunctionsDataText',    'reemplazarEspaciosxGuion',    [''],                                                   'string',  '(Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataText',    'reemplazarEspaciosxGuion',    ['Lorem ipsum dolor sit amet, consectetur'],            'string',  '(Devuelve Lorem_ipsum_dolor_sit_amet,_consectetur)');
        //--------------------- diferenciaMeses ---------------------
        $this->runTest($test, 'FunctionsDataText',    'sanitizarTexto',              [''],                                                   'string',  '(Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataText',    'sanitizarTexto',              ['Lorem ipsum dolor sit amet, consectetur'],            'string',  '(Devuelve Lorem ipsum dolor sit amet, consectetur)');
        //--------------------- diferenciaMeses ---------------------
        $this->runTest($test, 'FunctionsDataText',    'desanitizarTexto',            [''],                                                   'string',  '(Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataText',    'desanitizarTexto',            ['Lorem ipsum dolor sit amet, consectetur'],            'string',  '(Devuelve Lorem ipsum dolor sit amet, consectetur)');
        //--------------------- diferenciaMeses ---------------------
        $this->runTest($test, 'FunctionsDataText',    'limpiezaTexto',               [""],                                                   'string',  '(Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataText',    'limpiezaTexto',               ["blabla'bla"],                                         'string',  '(Devuelve blabla%27bla)');
        //--------------------- diferenciaMeses ---------------------
        $this->runTest($test, 'FunctionsDataText',    'contarPalabrasCensuradas',    [''],                                                   'string',  '(Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataText',    'contarPalabrasCensuradas',    ['Lorem ipsum dolor sit amet, fuck d'],                 'int',     '(Devuelve 1)');
        //--------------------- diferenciaMeses ---------------------
        $this->runTest($test, 'FunctionsDataText',    'filtrarPalabrasCensuradas',   [''],                                                   'string',  '(Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataText',    'filtrarPalabrasCensuradas',   ['Lorem ipsum dolor sit amet, fuck d'],                 'string',  '(Devuelve lorem ipsum dolor sit amet, **** d)');
        //--------------------- diferenciaMeses ---------------------
        $this->runTest($test, 'FunctionsDataText',    'tituloMenu',                  [''],                                                   'string',  '(Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsDataText',    'tituloMenu',                  ['01 - Titulo'],                                        'string',  '(Devuelve Titulo)');
        //--------------------- diferenciaMeses ---------------------
        //$this->runTest($test, 'FunctionsDataText',    'buscarPalabraYExtraer',       ['Lorem ipsum dolor sit amet', 'ipsum'],                'string',  '(Devuelve dolor sit amet)');

        /**********  FunctionsDataTime  **********/
        $this->runTest($test, 'FunctionsDataTime',                'formatoHoraEstandar',         ['1:1'],                                                'string',  '(1:1 -> Devuelve 01:01)');
        $this->runTest($test, 'FunctionsDataTime',                'formatoHoraProgramada',       ['1:1'],                                                'string',  '(1:1 -> Devuelve 01:01:00)');
        $this->runTest($test, 'FunctionsDataTime',                'formatoHoraArchivos',         ['1:1'],                                                'string',  '(1:1 -> Devuelve 010100)');

        /**********  FunctionsDataValidations  **********/
        $this->runTest($test, 'FunctionsDataValidations',         'validarRut',                  ['16.029.464-7'],                                       'bool',    '(16.029.464-7 -> Devuelve true)');
        $this->runTest($test, 'FunctionsDataValidations',         'validarEmail',                ['asd@asd.cl'],                                         'bool',    '(asd@asd.cl -> Devuelve true)');
        $this->runTest($test, 'FunctionsDataValidations',         'validarNumero',               ['25'],                                                 'bool',    '(25 -> Devuelve true)');
        $this->runTest($test, 'FunctionsDataValidations',         'validarPatente',              ['au1825'],                                             'bool',    '(au1825 -> Devuelve true)');
        $this->runTest($test, 'FunctionsDataValidations',         'validarURL',                  ['https://www.google.cl'],                              'bool',    '(https://www.google.cl -> Devuelve true)');
        $this->runTest($test, 'FunctionsDataValidations',         'validarHora',                 ['16:24:00'],                                           'bool',    '(16:24:00 -> Devuelve true)');
        $this->runTest($test, 'FunctionsDataValidations',         'validarFecha',                ['1900-01-01'],                                         'bool',    '(1900-01-01 -> Devuelve true)');
        $this->runTest($test, 'FunctionsDataValidations',         'validarEntero',               [16],                                                   'bool',    '(16 -> Devuelve true)');
        //$this->runTest($test, 'FunctionsDataValidations',         'validarDispositivoMovil',     [],                                                     'bool',    '(16 -> Devuelve true)');
        $this->runTest($test, 'FunctionsDataValidations',         'validarLargoMinimo',          ['Lorem ipsum dolor sit amet, consectetur', 10],        'bool',    '(Lorem ipsum dolor sit amet, consectetur -> Devuelve true)');
        $this->runTest($test, 'FunctionsDataValidations',         'validarLargoMaximo',          ['Lorem', 10],                                          'bool',    '(Lorem -> Devuelve true)');

        /**********  FunctionsLocation  **********/
        $this->runTest($test, 'FunctionsLocation',                'calcularDistancia',           ['', '', '', ''],                                       'string',  '(Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsLocation',                'calcularDistancia',           ['a', 'a', 'a', 'a'],                                   'string',  '(Devuelve El dato ingresado no es un numero (a))');
        $this->runTest($test, 'FunctionsLocation',                'calcularDistancia',           [-40.807289, -72.634907, -42.176560, -73.425923],       'float',   '(Devuelve 165.89718855602)');

        /**********  FunctionsSecurityCodification  **********/
        //--------------------- simpleEncode ---------------------
        $this->runTest($test, 'FunctionsSecurityCodification',    'simpleEncode',                ["", "passkey"],                                        'string',  '(Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsSecurityCodification',    'simpleEncode',                ["php recipe", "passkey"],                              'string',  '(Devuelve lEKK57naUY4---VQ==)');
        //--------------------- simpleDecode ---------------------
        $this->runTest($test, 'FunctionsSecurityCodification',    'simpleDecode',                ["", "passkey"],                                        'string',  '(Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsSecurityCodification',    'simpleDecode',                ["lEKK57naUY4/VQ==", "passkey"],                        'string',  '(Devuelve php recipe)');
        //--------------------- generateServerSpecificHash ---------------------
        $this->runTest($test, 'FunctionsSecurityCodification',    'generateServerSpecificHash',  [],                                                     'string',  '(Devuelve 49960de5880e8c687434170f6476605b8fe4aeb9a28632c7995cf3ba831d9763)');
        //--------------------- encryptDecrypt ---------------------
        $this->runTest($test, 'FunctionsSecurityCodification',    'encryptDecrypt',              ['encrypt',''],                                         'string',  '(Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsSecurityCodification',    'encryptDecrypt',              ['encrypt',5008],                                       'string',  '(Devuelve OExmMkRxL0ZtWWlRVzJLZHYyVWF3Zz09)');
        //--------------------- encryptDecrypt ---------------------
        $this->runTest($test, 'FunctionsSecurityCodification',    'encryptDecrypt',              ['decrypt',''],                                         'string',  '(Devuelve Sin datos ingresados)');
        $this->runTest($test, 'FunctionsSecurityCodification',    'encryptDecrypt',              ['decrypt','OExmMkRxL0ZtWWlRVzJLZHYyVWF3Zz09'],         'string',  '(Devuelve 5008)');

        /**********  FunctionsSecurityPasswords  **********/
        $this->runTest($test, 'FunctionsSecurityPasswords',       'generarPassword',             [10,'alfanumerico'],                                    'string',  '(Devuelve asd)');
        $this->runTest($test, 'FunctionsSecurityPasswords',       'generarPasswordUnica',        [],                                                     'string',  '(Devuelve asd)');
        $this->runTest($test, 'FunctionsSecurityPasswords',       'caracteresRandom',            [16, true, false, false],                               'string',  '(Devuelve asd)');
        $this->runTest($test, 'FunctionsSecurityPasswords',       'tokenBin2Hex',                [25],                                                   'string',  '(Devuelve asd)');
        $this->runTest($test, 'FunctionsSecurityPasswords',       'hashCreate',                  ['palabra'],                                                                  'string',  '(Devuelve asd)');
        $this->runTest($test, 'FunctionsSecurityPasswords',       'hashVerify',                  ['palabra', '$2y$12$pd1.kBABacsBwq8YXNDieuqNELrjJiq68kXCFtHoaj7IwqljDLdj6'],  'bool',  '(Devuelve 1)');

        /**********  FunctionsDataText  **********/
        $this->runTest($test, 'FunctionsServerClient',            'getClientIp',                 [],                                                     'string',  '(Devuelve asd)');
        $this->runTest($test, 'FunctionsServerClient',            'getBrowser',                  [],                                                     'string',  '(Devuelve asd)');
        $this->runTest($test, 'FunctionsServerClient',            'getOperatingSystem',          [],                                                     'string',  '(Devuelve asd)');

        /**********  FunctionsServerServer  **********/
        $this->runTest($test, 'FunctionsServerServer',            'fechaActual',                 [],                                                     'string',  '(Devuelve '.$this->Server->fechaActual().')');
        $this->runTest($test, 'FunctionsServerServer',            'fechaActualAlternative',      [],                                                     'string',  '(Devuelve '.$this->Server->fechaActualAlternative().')');
        $this->runTest($test, 'FunctionsServerServer',            'horaActual',                  [],                                                     'string',  '(Devuelve '.$this->Server->horaActual().')');
        $this->runTest($test, 'FunctionsServerServer',            'horaActualAlternative',       [],                                                     'string',  '(Devuelve '.$this->Server->horaActualAlternative().')');
        $this->runTest($test, 'FunctionsServerServer',            'diaActual',                   [],                                                     'string',  '(Devuelve '.$this->Server->diaActual().')');
        $this->runTest($test, 'FunctionsServerServer',            'semanaActual',                [],                                                     'string',  '(Devuelve '.$this->Server->semanaActual().')');
        $this->runTest($test, 'FunctionsServerServer',            'mesActual',                   [],                                                     'string',  '(Devuelve '.$this->Server->mesActual().')');
        $this->runTest($test, 'FunctionsServerServer',            'anoActual',                   [],                                                     'string',  '(Devuelve '.$this->Server->anoActual().')');
        //$this->runTest($test, 'asd',    'asd',       [asd],            'asd',  '(Devuelve asd)');


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

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/testeos-funciones.php');

    }

    /******************************/
    // Helper function for testing
    public function runTest($test, $class, $method, $args, $expectedType, $desc, $extra = null) {

        /******************************************/
        //Llamo a las otras clases
        $FNC_DataValidations      = new FunctionsDataValidations;
        $FNC_Convertions          = new FunctionsConvertions;
        $FNC_DataOperations       = new FunctionsDataOperations;
        $FNC_DataDate             = new FunctionsDataDate;
        $FNC_DataNumbers          = new FunctionsDataNumbers;
        $FNC_DataText             = new FunctionsDataText;
        $FNC_DataTime             = new FunctionsDataTime;
        $FNC_Location             = new FunctionsLocation;
        $FNC_SecurityCodification = new FunctionsSecurityCodification;
        $FNC_SecurityPasswords    = new FunctionsSecurityPasswords;
        $FNC_ServerClient         = new FunctionsServerClient;
        $FNC_ServerServer         = new FunctionsServerServer;

        /******************************************/
        //Llamo a las otras clases
        $instance = ${'FNC_'.str_replace('Functions', '', $class)};
        $data = call_user_func_array([$instance, $method], $args);
        $test->expect(method_exists($class, $method), "$method() es una funcion $desc");
        $test->expect($data !== null && $data !== '', "$method() Ha devuelto datos ($data)");
        $typeCheck = "is_$expectedType";
        $test->expect($typeCheck($data), "$method() Los datos obtenidos son del tipo " . gettype($data), $data);
        return $data;
    }

    /******************************************************************************/
    //Envio de correo por SMTP (solo un correo, con uno o varios receptores)
    public function SMTPMail($f3){
        /******************************************/
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

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/testeos-Mail.php');
    }

    /******************************************************************************/
    //Envio de correo por Gmail (solo un correo, con uno o varios receptores)
    public function GMail($f3){
        /******************************************/
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

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/testeos-Mail.php');
    }

    /******************************************************************************/
    //Envio de correo por Sending Blue
    public function SendingBlue($f3){
        /******************************************/
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

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/testeos-Mail.php');
    }

    /******************************************************************************/
    //Envio de correo por Sending Blue
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

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/testeos-Whatsapp.php');
    }

    /******************************************************************************/
    //Envio de correo por SMTP (solo un correo, con uno o varios receptores)
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

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/testeos-MailTemplateSelect.php');
    }

    /******************************************************************************/
    //Envio de correo por SMTP (solo un correo, con uno o varios receptores)
    public function testMailTemplate($f3, $params){
        /******************************/
        //Se agrega respuesta
        $Post = [
            'Asunto'  => 'Cambio de contraseña',
            'Hacia'   => 'asd@asd.cl',
            'Mensaje' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
        ];

        /******************************/
        //Se genera la query
        $query = [
            'data'      => 'Asunto,Hacia,Mensaje',
            'template'  => $params['id'],
            'Post'      => $Post,
        ];

        /******************************/
        $MailTemplate = $this->Base_TestMailTemplate($f3, $query);

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

        /******************************************/
        //Se instancia la vista
        $this->showVista(2, $this->returnRutaVista(__DIR__, 'app').'/testeos-MailTemplate.php');
    }

    /******************************************************************************/
    //Envio de correo por SMTP (solo un correo, con uno o varios receptores)
    public function IA_View($f3){
        /*******************************************************************/
        /*                         Imprimir Datos                          */
        /*******************************************************************/
        //Datos enviados a la pagina
        $f3->data = [
            /*=========== Datos de la Pagina ===========*/
            'PageTitle'       => 'Testeos Inteligencia Artificial',
            'PageDescription' => 'Testeos Inteligencia Artificial.',
            'PageAuthor'      => ConfigAPP::SOFTWARE['SoftwareName'],
            'PageKeywords'    => ConfigAPP::SOFTWARE['SoftwareName'],
            'TableTitle'      => 'Testeos Inteligencia Artificial',
            /*===========   Funcionalidad   ===========*/
            'Fnc_FormInputs'   => $this->FormInputs,
            /*===========  Datos del usuario ===========*/
            'UserData'      => $this->getUserData($f3),
            'UserAccess'    => $this->getArrLevel($f3, $this->controllerName),
        ];

        /******************************************/
        //Se instancia la vista
        $this->showVista(1, $this->returnRutaVista(__DIR__, 'app').'/testeos-IA_Chat.php');
    }

    /******************************************************************************/
    /*                                  DATOS                                     */
    /******************************************************************************/
    /******************************************************************************/
    //Envio de correo por SMTP (solo un correo, con uno o varios receptores)
    public function send_SMTPMail($f3){
        /******************************/
        //Se genera la query
        $query = [
            'data'      => 'Asunto,Hacia,Mensaje',
            'template'  => 1,
            'Post'      => $_POST,
        ];
        //Ejecuto la query
        echo $this->Base_SMTPMail($f3, $query);
    }

    /******************************************************************************/
    //Envio de correo por Gmail (solo un correo, con uno o varios receptores)
    public function send_GMail($f3){
        /******************************/
        //Se genera la query
        $query = [
            'data'      => 'Asunto,Hacia,Mensaje',
            'template'  => 1,
            'Post'      => $_POST,
        ];
        //Ejecuto la query
        echo $this->Base_GMail($f3, $query);
    }

    /******************************************************************************/
    //Envio de correo por Sending Blue
    public function send_SendingBlue($f3){
        /******************************/
        //Se genera la query
        $query = [
            'data'      => 'De_correo,De_nombre,Hacia_correo,Hacia_nombre,Asunto,Mensaje',
            'template'  => 1,
            'Post'      => $_POST,
        ];
        //Ejecuto la query
        echo $this->Base_SendingBlue($f3, $query);

    }

    /******************************************************************************/
    //Envio de correo por Sending Blue
    public function send_Whatsapp($f3){
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            /******************************/
            //Se generan datos
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

            /***************************************/
            //si se envia correctamente
            if($Result['success']===true){
                // Devuelvo true con código 200 (OK)
                echo Response::sendData(200, $Result['success']);
            }else{
                // se asume que es un error o una respuesta que debe enviarse con código 500 (Error del Servidor)
                echo Response::sendData(500, $Result['error']);
            }
        }else {
            echo Response::sendData(500, "Error en el Request Method");
        }

    }

    /******************************************************************************/
    //Envio de correo por Sending Blue
    public function IA_Response($f3){
        //Verificacion metodo POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            /*******************************************************************/
            //variables
            $Pregunta = isset($_POST['mensaje']) ? $_POST['mensaje'] : 0;

            //generacion de errores
            if($Pregunta==0) {
                echo Response::sendData(500, 'No hay productos ingresados');
            }else{
                //La API
                $api_key = "";
                //El cuerpo
                $data = [
                    'model'    => 'gpt-3.5-turbo',
                    'messages' => [],
                ];
                $data['messages'][] = ['role' => 'system', 'content' => 'Actua como un experto '];
                $data['messages'][] = ['role' => 'user',   'content' => $Pregunta];

                /******************************************/
                $response  = $this->ServerIA->senDataIA($api_key, $data);

                //Se consigue la respuesta
                if($response['success']===true){
                    //Se decodifica la respuesta
                    $decoded_response = json_decode($response['data'], true);
                    //Se muestra el resultado
                    if (isset($decoded_response['choices'][0]['message']['content'])) {
                        echo Response::sendData(200, $decoded_response['choices'][0]['message']['content']);
                    }
                }else{
                    echo Response::sendData(500, $response['error']);
                }
            }
        }else {
            echo Response::sendData(500, "Error en el Request Method");
        }

    }

}
