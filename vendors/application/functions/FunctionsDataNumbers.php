<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsDataNumbers {

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
	public function Cantidades($valor, int $n_decimales): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Convierte un valor entregado a un numero formateado de acuerdo a la configuracion dada, con separador de miles
		* y con la cantidad de decimales configurada, si la cantidad de decimales deseada no se cumple, seran rellenados
		* con 0
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea numero
		* 	$DataNumbers->Cantidades(1250.85, 6); //Devuelve 1.250,850000
		*
		*=================================================    Parametros   =================================================
		* @input  float       $valor         Numero a formatear
		* @input  int         $n_decimales   Numero de decimales deseados
		* @return string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if ($valor=='' || $valor==0) { return '0'; }
		if (!$this->DataValidations->validarNumero($n_decimales) || !$this->DataValidations->validarEntero($n_decimales) ||
			!$this->DataValidations->validarNumero($valor)) {
            return 'Verificar que el dato ingresado sea un numero';
        }

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return number_format($valor,$n_decimales,',','.');

	}

	/************************************************************************************************************/
	public function nDoc($valor, int $n_ceros): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Agrega ceros a un numero designado, permitiendo dar el formato de numero correlativo de un documento, la cantidad
		* de ceros define el largo del numero ingresado
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea numero
		* 	$DataNumbers->nDoc(25, 7); //Devuelve 0000025
		*
		*=================================================    Parametros   =================================================
		* @input  int         $valor         Numero a formatear
		* @input  int         $n_ceros       Numero de ceros a la izquierda del valor
		* @return string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if ($valor=='' || $valor==0) { return '0'; }
		if (!$this->DataValidations->validarNumero($valor)   || !$this->DataValidations->validarEntero($valor) ||
			!$this->DataValidations->validarNumero($n_ceros) || !$this->DataValidations->validarEntero($n_ceros)) {
            return 'Verificar que el dato ingresado sea un numero';
        }

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return str_pad($valor, $n_ceros, "0", STR_PAD_LEFT);

	}

	/************************************************************************************************************/
	public function Valores($valor, int $n_decimales): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Antepone un simbolo de peso al valor ingresado, luego formatea el valor con un separador de miles y por ultimo
		* agrega la cantidad de decimales predefinida por el usuario
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea numero
		* 	$DataNumbers->Valores(1500.85565,2); //Devuelve $ 1.500,86
		*
		*=================================================    Parametros   =================================================
		* @input  float       $valor         Numero a formatear
		* @input  int         $n_decimales   Numero de decimales deseados
		* @return string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if ($valor=='' || $valor==0) { return '0'; }
		if (!$this->DataValidations->validarNumero($n_decimales) || !$this->DataValidations->validarEntero($n_decimales) ||
			!$this->DataValidations->validarNumero($valor)) {
            return 'Verificar que el dato ingresado sea un numero';
        }

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return '$ '.number_format($valor,$n_decimales,',','.');

	}

	/************************************************************************************************************/
	public function valoresEnteros($valor): string | float | int {
		/*
		*=================================================     Detalles    =================================================
		*
		* Transforma el valor ingresado a un entero, aproximandolo al entero mas cercano, agregando un separador de miles
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea numero
		* 	$DataNumbers->valoresEnteros(1500.85); //Devuelve 1501
		*
		*=================================================    Parametros   =================================================
		* @input   float  $valor   Numero a formatear
		* @return  float
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if ($valor=='' || $valor==0) {                        return '0'; }
		if (!$this->DataValidations->validarNumero($valor)) { return 'Verificar que el dato ingresado sea un numero'; }

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return round($valor);

	}

	/************************************************************************************************************/
	public function valoresComparables($valor): string | float | int {
		/*
		*=================================================     Detalles    =================================================
		*
		* Transforma el valor ingresado a un entero, aproximandolo al entero mas cercano
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea numero
		* 	$DataNumbers->valoresComparables(1500.85); //Devuelve 1501
		*
		*=================================================    Parametros   =================================================
		* @input   float  $valor   Numero a comparar
		* @return  int
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if ($valor=='' || $valor==0) {                        return '0'; }
		if (!$this->DataValidations->validarNumero($valor)) { return 'Verificar que el dato ingresado sea un numero'; }

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return ceil($valor);

	}

	/************************************************************************************************************/
	public function valoresTruncados($valor): string | float | int {
		/*
		*=================================================     Detalles    =================================================
		*
		* Elimina los decimales del valor ingresado, sin aproximarlo al valor mas cercano, simplemente elimina la parte
		* decimal del valor, sin separador de miles
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea numero
		* 	$DataNumbers->valoresTruncados(1500.85); //Devuelve 1500
		*
		*=================================================    Parametros   =================================================
		* @input   float  $valor   Numero a formatear
		* @return  int
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if ($valor=='' || $valor==0) {                        return '0'; }
		if (!$this->DataValidations->validarNumero($valor)) { return 'Verificar que el dato ingresado sea un numero';  }

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return floor($valor);

	}

	/************************************************************************************************************/
	public function cantidadesDecimalesJustos($valor): string | float | int {
		/*
		*=================================================     Detalles    =================================================
		*
		* Formatea el valor entregado de forma variable, esto quiere decir que solo mostrara la cantidad de decimales reales
		* que tenga un valor decimal, si solo tiene 3 solo mostrara 3, si solo tiene 1 solo mostrara 1, no rellena los
		* decimales necesarios con 0, en el caso de ser un decimal infinito periodico, sino limita la cantidad de decimales
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea numero
		* 	$DataNumbers->cantidadesDecimalesJustos(1500.85000); //Devuelve 1500.85
		*
		*=================================================    Parametros   =================================================
		* @input   float  $valor   Numero a formatear
		* @return  float
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if ($valor=='' || $valor==0) {                        return '0'; }
		if (!$this->DataValidations->validarNumero($valor)) { return 'Verificar que el dato ingresado sea un numero'; }

		/********************** Si todo esta ok **********************/
		// Convertir a string para contar decimales, manejando la posible notación científica
		$valor_str = (string) $valor;

		// Contar decimales usando lógica simplificada.
		// strrchr($valor_str, '.') encuentra la parte desde el último punto decimal.
		// Si no hay punto, devuelve false, por lo que usamos strlen() en el resultado.
		$dec = strlen(substr(strrchr($valor_str, '.'), 1));

		// Aplicar el límite
		if ($dec >= 6) {$dec = 6;}

		/**********************  Retorno datos  **********************/
		// Redondear y devolver el float. Usar round() es más eficiente que number_format() + floatval().
		return round($valor, $dec);

	}

	/************************************************************************************************************/
	public function cantidadesExcel($valor): string | float | int {
		/*
		*=================================================     Detalles    =================================================
		*
		* Devuelve un valor compatible con excel en el uso de decimales
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea numero
		* 	$DataNumbers->cantidadesExcel(1500.85); //Devuelve 1500.85
		*
		*=================================================    Parametros   =================================================
		* @input   float   $valor   Numero a formatear
		* @return  float
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if ($valor=='' || $valor==0) { return '0'; }

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return str_replace('.', ',', $valor);

	}

	/************************************************************************************************************/
	public function cantidadesGoogle($valor): string | float | int {
		/*
		*=================================================     Detalles    =================================================
		*
		* Devuelve un valor compatible con google en el uso de decimales
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea numero
		* 	$DataNumbers->cantidadesGoogle(1500.85); //Devuelve 1500.85
		*
		*=================================================    Parametros   =================================================
		* @input   float   $valor   Numero a formatear
		* @return  float
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if ($valor=='' || $valor==0) {                        return '0'; }
		if (!$this->DataValidations->validarNumero($valor)) { return 'Verificar que el dato ingresado sea un numero'; }

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return str_replace(',', '.', $valor);

	}

	/************************************************************************************************************/
	public function formatPhone($Phone): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Valida y formatea un numero ingresado a uno mas legible
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea numero
		* 	$DataNumbers->formatPhone('+56911265984'); //Devuelve (+56) 9 1126 5984
		*
		*=================================================    Parametros   =================================================
		* @input    string    $Phone   Numero a formatear
		* @return   string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!isset($Phone) || $Phone==''){ return 'No ha ingresado el Fono';}
		if(strlen($Phone)<=7){            return 'Numero demasiado corto, tiene '.strlen($Phone).' numeros y debe tener al menos 9';}
		if(strlen($Phone)>=13){           return 'Numero demasiado largo, tiene '.strlen($Phone).' numeros y debe tener no mas de 11';}

		/********************** Si todo esta ok **********************/
		//Se formatea
		$myPhone = $this->normalizarPhone($Phone);

		/**********************  Retorno datos  **********************/
		return sprintf("(%s) %s %s %s",
			substr($myPhone, 0, 3),
			substr($myPhone, 3, 1),
			substr($myPhone, 4, 4),
			substr($myPhone, 8, 4));

	}

	/************************************************************************************************************/
	public function normalizarPhone($Phone): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Valida y formatea un numero ingresado a uno mas legible
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea numero
		* 	$DataNumbers->normalizarPhone('+56911265984'); //Devuelve +56 9 1126 5984
		*
		*=================================================    Parametros   =================================================
		* @input    string    $Phone   Numero a formatear
		* @return   string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!isset($Phone) || $Phone==''){ return 'No ha ingresado el Fono';}
		if(strlen($Phone)<=7){            return 'Numero demasiado corto, tiene '.strlen($Phone).' numeros y debe tener al menos 9';}
		if(strlen($Phone)>=13){           return 'Numero demasiado largo, tiene '.strlen($Phone).' numeros y debe tener no mas de 11';}

		/********************** Si todo esta ok **********************/
		//si solo tiene el formato antiguo se le agrega el 9
		if(strlen($Phone)==8){$Phone = '9'.$Phone;}
		//verifico si numero comienza con +56 o con 56
		$myNumber = $Phone;
		// Normaliza el número de teléfono chileno a formato internacional +56XXXXXXXXX
		$Phone = preg_replace('/\D/', '', $myNumber); // Elimina caracteres no numéricos
		// Si el número comienza con '+56', asegura el formato correcto
		if (strpos($myNumber, '+56') === 0) {
			$myPhone = '+56' . substr($Phone, 2);
		// Si el número comienza con '56', agrega el '+' al inicio
		} elseif (strpos($myNumber, '56') === 0) {
			$myPhone = '+56' . substr($Phone, 2);
		// Si el número comienza solo con '+', lo deja tal cual
		} elseif (strpos($myNumber, '+') === 0) {
			$myPhone = $myNumber;
		// Si no tiene prefijo, agrega '+56' al inicio
		} else {
			$myPhone = '+56' . $Phone;
		}

		/**********************  Retorno datos  **********************/
		return $myPhone;

	}

	/************************************************************************************************************/
	public function numberInit0($valor): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Antepone un 0 en caso de ser un numero inferior a 10
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea numero
		* 	$DataNumbers->numberInit0(1); //Devuelve 01
		*
		*=================================================    Parametros   =================================================
		* @input    string    $number   Numero a formatear
		* @return   string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if ($valor=='' || $valor==0) {                        return '0'; }
		if (!$this->DataValidations->validarNumero($valor)) { return 'Verificar que el dato ingresado sea un numero'; }

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return ($valor<10) ? '0'.$valor : $valor;

	}

}
