<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsSecurityPasswords {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                 Instancias                                                      */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	//Definiciones
	private $DataValidations;

	/************************************************************************************************************/
	//Instancias
	public function __construct() {
		$this->DataValidations = new FunctionsDataValidations();
	}


    /*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
    public function generarPassword($longitud,$tipo): string{
        /*
        *=================================================     Detalles    =================================================
        *
        * Permite generar un pasword aleatorio de dos tipos, numerico o alfanumerico, seleccionando el largo del password
        * aleatorio
        *
        *=================================================    Modo de uso  =================================================
        * 	//Numerico:
        * 	$SecurityPasswords->generarPassword(10,'numerico'); //Devuelve valores numeros aleatoreos
        *
        * 	//Alfanumerico:
        * 	$SecurityPasswords->generarPassword(10,'alfanumerico'); //Devuelve valores alfanumerico aleatoreos
        *
        *=================================================    Parametros   =================================================
        * @input     int        $longitud   Largo de la password generada
        * @input     string     $tipo       Tipo de password a generar
        * @return    string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
        //verifico si los datos estan bien entregados
		if(!isset($longitud) || $longitud==''){                                                                 return 'No ha ingresado longitud';}
		if(!isset($tipo) || $tipo==''){                                                                         return 'No ha ingresado tipo';}
        if (!$this->DataValidations->validarNumero($longitud) || ($tipo!="alfanumerico" && $tipo!="numerico")){ return 'Datos requeridos mal ingresados';}

        /********************** Si todo esta ok **********************/
        // Definir los alfabetos disponibles
        $alfabetos = [
            'alfanumerico' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
            'numerico'     => '0123456789',
        ];

        // Seleccionar el alfabeto según el tipo, por defecto alfanumérico
        $alphabet = $alfabetos[$tipo] ?? $alfabetos['alfanumerico'];

        // Si la longitud requerida es mayor que la longitud del alfabeto,
        // repetir el alfabeto para garantizar suficiente longitud
        $repeticiones = ceil($longitud / strlen($alphabet));
        $pool         = str_repeat($alphabet, $repeticiones);

        // Mezclar aleatoriamente los caracteres del pool
        $shuffled = str_shuffle($pool);

        /**********************  Retorno datos  **********************/
        // Obtener la contraseña con la longitud deseada
        return substr($shuffled, 0, $longitud);

    }

    /************************************************************************************************************/
    public function generarPasswordUnica(): string{
        /*
        *=================================================     Detalles    =================================================
        *
        * Se genera una password unica en base a la fecha y a la hora del servidor, de esta forma no hay probabilidades de
        * que esta se repita, tener en cuenta su uso en caso de ser utilizada reiteradamente (2 veces en el mismo segundo)
        *
        *=================================================    Modo de uso  =================================================
        *
        * 	//generar una password
        * 	$SecurityPasswords->generarPasswordUnica(); //Devuelve 20241007152055 (para la fecha 2024/10/07 15:20:55)
        *
        *=================================================    Parametros   =================================================
        * @return    string
		*===================================================================================================================
		*/

        /********************** Si todo esta ok **********************/
        // Establecer la zona horaria predeterminada a usar.
        date_default_timezone_set('America/Santiago');

        /**********************  Retorno datos  **********************/
        //devuelvo valor
        return date("Ymd").date("His");

    }

    /************************************************************************************************************/
    public function caracteresRandom($longitud = 16, $lecturaAmigable = true, $incluirSimbolos = false, $sinDuplicados = false): string{
        /*
        *=================================================     Detalles    =================================================
        *
        * Permite generar palabras con caracteres random, con varias opciones disponibles
        *
        *=================================================    Modo de uso  =================================================
        *
        * 	//Caracteres Random
        * 	$SecurityPasswords->caracteresRandom(16, true, false, false); //Devuelve valores aleatoreos
        *
        *=================================================    Parametros   =================================================
        * @input   int        $longitud          Define el largo de la palabra generada
        * @input   boolean    $lecturaAmigable   Remueve los caracteres similares a otro, tales como O y 0, l y 1, etc(true - false)
        * @input   boolean    $incluirSimbolos   Permite incluir simbolos en la palabra generada, no debe estar activada si lectura
        *                                       amigable esta activa(true - false)
        * @input   boolean    $sinDuplicados     Da la opción de que la palabra generada no contenga caracteres repetidos(true - false)
        * @return  String
		*===================================================================================================================
		*/

		/********************** Si todo esta ok **********************/
        $caracteres_legibles = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefhjkmnprstuvwxyz23456789';
        $caracteres_todos    = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
        $simbolos            = '!@#$%^&*()~_-=+{}[]|:;<>,.?/"\'\\`';

        // Seleccionar conjunto base de caracteres
        $pool = $lecturaAmigable ? $caracteres_legibles : $caracteres_todos;

        if (!$lecturaAmigable && $incluirSimbolos) {
            $pool .= $simbolos;
        }

        // Si se permiten duplicados, generar simplemente con str_shuffle y str_repeat
        if (!$sinDuplicados) {
            $repeticiones = (int) ceil($longitud / strlen($pool));
            return substr(str_shuffle(str_repeat($pool, $repeticiones)), 0, $longitud);
        }

        // Verificar que la longitud no supere la cantidad de caracteres únicos disponibles
        $caracteres_unicos = count(array_unique(str_split($pool)));
        if ($longitud > $caracteres_unicos) {
            throw new LengthException("La longitud solicitada ($longitud) excede el número de caracteres únicos disponibles ($caracteres_unicos) cuando sinDuplicados está habilitado.");
        }

        // Convertir pool a array y mezclar aleatoriamente (usando shuffle seguro)
        $caracteres = str_split($pool);

        // Shuffle seguro (usando algoritmo Fisher-Yates y random_int)
        for ($i = count($caracteres) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$caracteres[$i], $caracteres[$j]] = [$caracteres[$j], $caracteres[$i]];
        }

        /**********************  Retorno datos  **********************/
        // Tomar los primeros $longitud caracteres sin duplicados
        return implode('', array_slice($caracteres, 0, $longitud));

    }

    /************************************************************************************************************/
    public function tokenBin2Hex($longitud): string {
        /*
        *=================================================     Detalles    =================================================
        *
        * Codificacion propia por cada servidor, esto impide el copiado de información entre servidores
        *
        *=================================================    Modo de uso  =================================================
        *
        * 	//se genera codigo
        * 	$SecurityPasswords->tokenBin2Hex(25); //Devuelve valores aleatoreos
        *
        *=================================================    Parametros   =================================================
        * @input  int      $longitud  largo del codigo generado
        * @return  String
        *===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!isset($longitud) || $longitud==''){ return 'No ha ingresado longitud';}

		/********************** Si todo esta ok **********************/
        /**********************  Retorno datos  **********************/
        return bin2hex(openssl_random_pseudo_bytes(($longitud - ($longitud % 2)) / 2));

    }

    /************************************************************************************************************/
    public static function hashCreate($plain): string{
        /*
        *=================================================     Detalles    =================================================
        *
        * Crea un hash seguro de una cadena de texto (contraseña) utilizando el algoritmo BCRYPT.
        * El algoritmo BCRYPT es una excelente opción para el hashing de contraseñas debido a
        * su diseño y a la capacidad de aumentar el factor de "costo" (computacional) con el tiempo.
        *
        *=================================================    Modo de uso  =================================================
        *
        * 	//se genera codigo
        * 	$SecurityPasswords->password_hash(25);
        *
        *=================================================    Parametros   =================================================
        * @input   string       $plain   La cadena de texto (ej. la contraseña en texto plano) a hashear.
        * @return  string|false          El hash de la cadena de texto si la operación fue exitosa, o FALSE en caso de error.
        *===================================================================================================================
		*/

		/********************** Si todo esta ok **********************/
        // Define las opciones para el hashing.
        // 'cost' es el factor de trabajo. Un costo de 12 se considera seguro para la mayoría de las aplicaciones a partir de 2024.
        // Ajustar este valor requiere recalcular el equilibrio entre seguridad y rendimiento.
        $options = ['cost' => 12,];

        /**********************  Retorno datos  **********************/
        // Crea y retorna el hash utilizando el algoritmo BCRYPT.
        return password_hash($plain, PASSWORD_BCRYPT, $options);

    }

    /************************************************************************************************************/
    public static function hashVerify($plain, $hash): string{
        /*
        *=================================================     Detalles    =================================================
        *
        * Verifica si una cadena de texto sin hashear (texto plano) coincide con un hash dado.
        *
        * Utiliza el algoritmo que se especificó al crear el hash (ej. BCRYPT) para verificar
        * si el texto plano, al ser hasheado, produce el mismo resultado.
        * Esta función maneja automáticamente los diferentes algoritmos y el factor de 'costo'
        * almacenados dentro del propio hash.
        *
        *=================================================    Modo de uso  =================================================
        *
        * 	//se genera codigo
        * 	$SecurityPasswords->hashVerify(25, 'asdqwe');
        *
        *=================================================    Parametros   =================================================
        * @input   string  $plain  La cadena de texto (ej. contraseña ingresada por el usuario) en texto plano.
        * @input   string  $hash   El hash conocido para comparar (ej. el hash almacenado en la base de datos).
        * @return  bool            TRUE si el texto plano coincide con el hash, o FALSE si no coincide.
        *===================================================================================================================
		*/

		/********************** Si todo esta ok **********************/
        /**********************  Retorno datos  **********************/
        // Compara la cadena en texto plano con el hash.
        // Esta función es segura en cuanto al tiempo, protegiendo contra ataques de temporización.
        return password_verify($plain, $hash);

    }


}
