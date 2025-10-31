<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsConvertions {

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
	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Funciones  Horas                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	public function numero2horas($horasDecimales): string {
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite ingresar un numero (decimales, representando las horas) y transformarlo en formato hora
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//transformar minutos
		* 	$Convertions->numero2horas(1.5); //Devuelve 01:30:00
		*
		*=================================================    Parametros   =================================================
		* @input   float    $horasDecimales   Numero de minutos a transformar
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if ($horasDecimales==''){                                     return 'Sin Dato ingresado';}
		if (!$this->DataValidations->validarNumero($horasDecimales)){ return 'El dato ingresado no es un numero';}

		/********************** Si todo esta ok **********************/
		$h = intval($horasDecimales);
		$m = ($horasDecimales - $h) * 60;
		if ($m == 60){$h++;$m = 0;}

		/**********************  Retorno datos  **********************/
		return sprintf("%02d:%02d:00", $h, $m);

	}

	/************************************************************************************************************/
	public function minutos2horas($nMinutos): string {
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite ingresar un numero entero (minutos) y transformarlo en formato hora
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//transformar minutos
		* 	$Convertions->minutos2horas(65); //Devuelve 01:05:00
		*
		*=================================================    Parametros   =================================================
		* @input   int     $nMinutos   Numero de minutos a transformar
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if ($nMinutos==''){ return 'Sin Dato ingresado';}
		if (!$this->DataValidations->validarNumero($nMinutos) || !$this->DataValidations->validarEntero($nMinutos)) {
            return 'Verificar que el dato ingresado sea un numero';
        }

		/********************** Si todo esta ok **********************/
		$horas   = floor($nMinutos / 60);
		$minutos = $nMinutos % 60;

		/**********************  Retorno datos  **********************/
		return sprintf('%02d:%02d:00', $horas, $minutos);

	}

	/************************************************************************************************************/
	public function segundos2horas($nSegundos): string {
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite ingresar un numero entero (segundos) y transformarlo en formato hora
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//transformar segundos
		* 	$Convertions->segundos2horas(3600); //Devuelve 01:00:00
		*
		*=================================================    Parametros   =================================================
		* @input   int    $nSegundos   Numero de segundos a transformar
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if ($nSegundos==''){   return 'Sin Dato ingresado';}
		if (!$this->DataValidations->validarNumero($nSegundos) || !$this->DataValidations->validarEntero($nSegundos)) {
            return 'Verificar que el dato ingresado sea un numero';
        }

		/********************** Si todo esta ok **********************/
		$horas    = floor($nSegundos / 3600);
		$minutos  = floor(($nSegundos % 3600) / 60);
		$segundos = $nSegundos % 60;

		/**********************  Retorno datos  **********************/
		return sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos);

	}

	/************************************************************************************************************/
	public function horas2minutos($horas): string | int {
		/*
		*=================================================     Detalles    =================================================
		*
		* Transforma una hora ingresada a la cantidad de minutos que representa
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//transformar hora
		* 	$Convertions->horas2minutos('01:05:00'); //Devuelve 65
		*
		*=================================================    Parametros   =================================================
		* @input    time   $horas   La hora en formato texto
		* @return   int
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if ($horas==''){                                  return 'Sin Dato ingresado';}
		if (!$this->DataValidations->validarHora($horas)){return 'El dato ingresado no es una hora ('.$horas.')';}

		/********************** Si todo esta ok **********************/
		$dateTime = DateTime::createFromFormat('H:i:s', $horas);
		if ($dateTime === false) {
			// Manejar error de formato si validarHora no es lo suficientemente estricta
			return 'El dato ingresado no es una hora ('.$horas.')';
		}

		/**********************  Retorno datos  **********************/
		return ($dateTime->format('H') * 60) + $dateTime->format('i');

	}

	/************************************************************************************************************/
	public function horas2segundos($horas): string | int{
		/*
		*=================================================     Detalles    =================================================
		*
		* Transforma una hora ingresada a la cantidad de segundos que representa
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se transforma la hora
		* 	$Convertions->horas2segundos('00:30:00'); //Devuelve 1800
		*
		*=================================================    Parametros   =================================================
		* @input   time     $horas   La hora en formato texto
		* @return  int
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if ($horas==''){                                  return 'Sin Dato ingresado';}
		if (!$this->DataValidations->validarHora($horas)){return 'El dato ingresado no es una hora ('.$horas.')';}

		/********************** Si todo esta ok **********************/
		$dateTime = DateTime::createFromFormat('H:i:s', $horas);
		if ($dateTime === false) {
			// Manejar error de formato si validarHora no es lo suficientemente estricta
			return 'El dato ingresado no es una hora ('.$horas.')';
		}

		/**********************  Retorno datos  **********************/
		return ($dateTime->format('H') * 3600) + ($dateTime->format('i') * 60) + $dateTime->format('s');

	}

	/************************************************************************************************************/
	public function horas2decimales($horas): string | float{
		/*
		*=================================================     Detalles    =================================================
		*
		* Transforma una hora ingresada a numeros decimales que representa
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se transforma la hora
		* 	$Convertions->horas2decimales('01:30:00'); //Devuelve 1.5
		*
		*=================================================    Parametros   =================================================
		* @input   time     $horas   La hora en formato texto
		* @return  float
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if ($horas==''){                                  return 'Sin Dato ingresado';}
		if (!$this->DataValidations->validarHora($horas)){return 'El dato ingresado no es una hora ('.$horas.')';}

		/********************** Si todo esta ok **********************/
		$dateTime = DateTime::createFromFormat('H:i:s', $horas);
		if ($dateTime === false) {
			// Manejar error de formato si validarHora no es lo suficientemente estricta
			return 'El dato ingresado no es una hora ('.$horas.')';
		}

		/**********************  Retorno datos  **********************/
		return $dateTime->format('H') + ($dateTime->format('i') / 60) + ($dateTime->format('s')/3600);

	}




	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Funciones  Fechas                                                  */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	public function devolverMes($mes): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Formatea un mes abreviado al nombre del mes al que representa
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se convierten los datos
		* 	$Convertions->devolverMes('Ene'); //Devuelve Enero
		*
		*=================================================    Parametros   =================================================
		* @input    string    $mes    Mes con 3 letras
		* @return   string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if ($mes==''){ return 'Sin Dato ingresado';}

		/**********************  Definiciones   **********************/
		$meses = [
			'ene' => 'Enero',
			'feb' => 'Febrero',
			'mar' => 'Marzo',
			'abr' => 'Abril',
			'may' => 'Mayo',
			'jun' => 'Junio',
			'jul' => 'Julio',
			'ago' => 'Agosto',
			'sep' => 'Septiembre',
			'oct' => 'Octubre',
			'nov' => 'Noviembre',
			'dic' => 'Diciembre'
		];

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return array_key_exists(strtolower($mes), $meses) ? $meses[strtolower($mes)] : 'Dato fuera de parámetros esperados';

	}

	/************************************************************************************************************/
	public function numero2mes($numero): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Devuelve el nombre del mes al cual el numero representa (valores del 1 a 12)
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se convierten los datos
		* 	$Convertions->numero2mes(1); //Devuelve Enero
		*
		*=================================================    Parametros   =================================================
		* @input   int        $numero   Numero a transformar (de 1 a 12)
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if ($numero==''){                                     return 'Sin Dato ingresado';}
		if (!$this->DataValidations->validarNumero($numero)){ return 'El dato ingresado no es un numero';}
		if ($numero < 1 || $numero > 12){                     return 'Numero fuera de parámetros esperados';}

		/********************** Si todo esta ok **********************/
		$options = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

		/**********************  Retorno datos  **********************/
		return $options[$numero-1];

	}

	/************************************************************************************************************/
	public function numero2mesCorto($numero): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Devuelve el nombre abreviado del mes al cual el numero representa (valores del 1 a 12)
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se convierten los datos
		* 	$Convertions->numero2mesCorto(1); //Devuelve Ene
		*
		*=================================================    Parametros   =================================================
		* @input    int        $numero   Numero a transformar (de 1 a 12)
		* @return   string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if ($numero==''){                                     return 'Sin Dato ingresado';}
		if (!$this->DataValidations->validarNumero($numero)){ return 'El dato ingresado no es un numero';}
		if ($numero < 1 || $numero > 12){                     return 'Numero fuera de parámetros esperados';}

		/********************** Si todo esta ok **********************/
		$options = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

		/**********************  Retorno datos  **********************/
		return $options[$numero-1];

	}

	/************************************************************************************************************/
	public function numeroNombreDia($numero): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Devuelve el nombre del dia al cual el numero representa (valores del 1 a 7)
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se convierten los datos
		* 	$Convertions->numeroNombreDia(3); //Devuelve Miercoles
		*
		*=================================================    Parametros   =================================================
		* @input    int       $numero   Numero a transformar (de 1 a 7)
		* @return   string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if ($numero==''){                                     return 'Sin Dato ingresado';}
		if (!$this->DataValidations->validarNumero($numero)){ return 'El dato ingresado no es un numero';}
		if($numero<0 || $numero>8){                           return 'Numero fuera de parámetros esperados';}

		/********************** Si todo esta ok **********************/
		$options = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'];

		/**********************  Retorno datos  **********************/
		return $options[$numero-1];

	}


	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Funciones  Valores                                                 */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	public function porcentaje($valor): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite transformar cualquier valor decimal ingresado en formato porcentaje
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se transforman los valores
		* 	$Convertions->porcentaje(0.65); //Devuelve 65%
		*
		*=================================================    Parametros   =================================================
		* @input     float    $valor   Decimal a transformar
		* @return    string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if ($valor==''){                                    return 'Sin Dato ingresado';}
		if (!$this->DataValidations->validarNumero($valor)){return 'El dato ingresado no es un numero';}

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return number_format(($valor *100),0,',','.').' %';

	}


	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Funciones  Textos                                                  */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	public function numeroApalabras($numero): string {
		/*
		*=================================================     Detalles    =================================================
		*
		* Transforma los numeros ingresados a su equivalente en palabras
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se transforma los datos
		* 	$Convertions->numeroApalabras(1200); //Devuelve mil doscientos
		*
		*=================================================    Parametros   =================================================
		* @input   int      $monto   Valor a transformar en palabras
		* @return  String
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if ($numero==''){                                    return 'Sin Dato ingresado';}
		if (!$this->DataValidations->validarNumero($numero)){return 'El dato ingresado no es un numero';}

		/********************** Si todo esta ok **********************/
		// 1. Manejar el signo
		$es_negativo = $numero < 0;
		$numero_abs = abs($numero); // Trabajar con el valor absoluto

		// 2. Definir las tablas de palabras
		$unidades = [
			0 => 'cero', 1 => 'uno', 2 => 'dos', 3 => 'tres', 4 => 'cuatro',
			5 => 'cinco', 6 => 'seis', 7 => 'siete', 8 => 'ocho', 9 => 'nueve'
		];
		$dieces = [
			10 => 'diez', 11 => 'once', 12 => 'doce', 13 => 'trece', 14 => 'catorce',
			15 => 'quince', 20 => 'veinte', 30 => 'treinta', 40 => 'cuarenta',
			50 => 'cincuenta', 60 => 'sesenta', 70 => 'setenta', 80 => 'ochenta', 90 => 'noventa'
		];
		$potencias = [
			1000000000 => 'mil millones', 1000000 => 'millón', 1000 => 'mil'
		];

		// 3. Función auxiliar para manejar números de 0 a 999 (el núcleo recursivo)
		$convertirCientos = function ($n) use (&$convertirCientos, $unidades, $dieces) {
			if ($n < 10) {
				return $unidades[$n];
			} elseif ($n < 16) {
				return $dieces[$n];
			} elseif ($n < 20) {
				return 'dieci' . $unidades[$n - 10];
			} elseif ($n < 30) {
				return ($n === 20) ? $dieces[20] : 'veinti' . $unidades[$n - 20];
			} elseif ($n < 100) {
				$decena = floor($n / 10) * 10;
				$unidad = $n % 10;
				if ($unidad === 0) {
					return $dieces[$decena];
				} else {
					return $dieces[$decena] . ' y ' . $unidades[$unidad];
				}
			} elseif ($n < 1000) {
				$cifra = floor($n / 100);
				$resto = $n % 100;

				$centenas = [
					1 => 'ciento',
					2 => 'doscientos',
					3 => 'trescientos',
					4 => 'cuatrocientos',
					5 => 'quinientos',
					6 => 'seiscientos',
					7 => 'setecientos',
					8 => 'ochocientos',
					9 => 'novecientos'
				];

				if ($n === 100) {
					$centena = 'cien';
				} else {
					$centena = $centenas[$cifra] ?? ($unidades[$cifra] . 'cientos');
				}

				if ($resto === 0) {
					return $centena;
				} else {
					return $centena . ' ' . $convertirCientos($resto);
				}
			}
			return ''; // Retorno por defecto
		};

		// 4. Lógica principal para números grandes (millares, millones, etc.)
		if ($numero_abs === 0) {
			return $unidades[0];
		}

		$palabras = [];

		// Iterar sobre las potencias (mil, millón, mil millones...)
		foreach ($potencias as $valor => $nombre) {
			if ($numero_abs >= $valor) {
				$cociente = floor($numero_abs / $valor);
				$resto = $numero_abs % $valor;

				// Caso especial: 'un millón' vs 'dos millones'
				$bloque_palabras = $convertirCientos($cociente);

				if ($nombre === 'millón' && $cociente === 1) {
					$palabras[] = 'un ' . $nombre;
				} else {
					$palabras[] = $bloque_palabras . ' ' . ($cociente > 1 && $nombre === 'millón' ? 'millones' : $nombre);
				}

				$numero_abs = $resto;
			}
		}

		// Procesar el bloque restante (cientos)
		if ($numero_abs > 0) {
			$palabras[] = $convertirCientos($numero_abs);
		}

		// 5. Construir el resultado final
		$resultado = trim(implode(' ', $palabras));

		// 6. Añadir el signo si era negativo
		if ($es_negativo) {
			$resultado = 'menos ' . $resultado;
		}

		/**********************  Retorno datos  **********************/
		return $resultado;
	}


}


