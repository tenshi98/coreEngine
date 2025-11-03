<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsDataOperations {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                 Instancias                                                      */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	//Definiciones
	private $DataValidations;
	private $Convertions;

	/************************************************************************************************************/
	//Instancias
	public function __construct() {
		$this->DataValidations = new FunctionsDataValidations();
		$this->Convertions     = new FunctionsConvertions();
	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	public function dividirHoras($hora,int $divisor): int {
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite dividir la hora en base a un numero entero, Devuelve el resultado en minutos, si se requiere en horas,
		* utilizar la funcion correspondiente
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se ejecuta operacion
		* 	$DataOperations->dividirHoras('04:00:00', 4); //Devuelve 60
		*
		*=================================================    Parametros   =================================================
		* @input   time     $hora        Hora ingresada
		* @input   int      $divisor     Divisor de la hora
		* @return  int
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!$this->DataValidations->validarHora($hora)){       return 'El dato ingresado no es una hora ('.$hora.')';}
		if (!$this->DataValidations->validarNumero($divisor) || !$this->DataValidations->validarEntero($divisor)) {
            return 'Verificar que el dato ingresado sea un numero';
        }

		/********************** Si todo esta ok **********************/
		$minutos = $this->Convertions->horas2minutos($hora);

		/**********************  Retorno datos  **********************/
		return $minutos/$divisor;

	}

	/************************************************************************************************************/
	public function multiplicarHoras($hora,int $multiplicador): string {
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite multiplicar las horas ingresadas por un numero entero, el resultado es devuelto en formato hora
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se ejecuta operacion
		* 	$DataOperations->multiplicarHoras('04:00:00', 4); //Devuelve 16:00:00
		*
		*=================================================    Parametros   =================================================
		* @input   time     $hora            Hora ingresada
		* @input   int      $multiplicador   Multiplicador de la hora
		* @return  time
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!$this->DataValidations->validarHora($hora)){ return 'El dato ingresado no es una hora ('.$hora.')';}
		if (!$this->DataValidations->validarNumero($multiplicador) || !$this->DataValidations->validarEntero($multiplicador)) {
            return 'Verificar que el dato ingresado sea un numero';
        }

		/********************** Si todo esta ok **********************/
		// 1. Convertir la hora al total de segundos.
		// strtotime("1970-01-01 $hora UTC") sigue siendo la forma más sencilla y robusta
		// para convertir 'HH:MM:SS' a segundos desde cero.
		$total_segundos = strtotime("1970-01-01 $hora UTC");

		// 2. Multiplicar y asegurar que el resultado sea un entero (la parte que realmente importa).
		$segundos_multiplicados = (int) round($total_segundos * $multiplicador);

		// 3. Calcular la parte de segundos, minutos y horas usando matemática simple.
		$segundos        = $segundos_multiplicados % 60;
		$minutos_totales = floor($segundos_multiplicados / 60);
		$minutos         = $minutos_totales % 60;
		$horas           = floor($minutos_totales / 60);

		/**********************  Retorno datos  **********************/
		// 4. Formatear y devolver el resultado.
		return sprintf("%02d:%02d:%02d", $horas, $minutos, $segundos);

	}

	/************************************************************************************************************/
	public function restarhoras($hora, $horaResta): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite restar una hora a otra hora, devolviendo un resultado en formato hora, verifica cual hora es mayor para
		* evitar problemas en la resta
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se ejecuta operacion
		* 	$DataOperations->restarhoras('14:00:00', '07:00:00'); //Devuelve 07:00:00
		*
		*=================================================    Parametros   =================================================
		* @input   time    $hora        Hora ingresada
		* @input   time    $horaResta   Cantidad de horas a restar
		* @return  time
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if (!$this->DataValidations->validarHora($hora) || !$this->DataValidations->validarHora($horaResta)) {
            return 'Verificar que el dato ingresado sea una hora';
        }

		/********************** Si todo esta ok **********************/
		//Se verifica cual es el mayor
		if(strtotime($hora)>strtotime($horaResta)){
			$horaResta  = $this->sumarhoras($horaResta, '24:00:00');
		}

		//Separo la hora
		$hora      = explode(":",$hora);
		$horaResta = explode(":",$horaResta);

		//obtengo valores por separado
		$horai = $hora[0];
		$mini  = $hora[1];
		$segi  = $hora[2];

		//obtengo valores por separado
		$horaf = $horaResta[0];
		$minf  = $horaResta[1];
		$segf  = $horaResta[2];

		//transformo a segundos
		$ini   = ((($horai*60)*60)+($mini*60)+$segi);
		$fin   = ((($horaf*60)*60)+($minf*60)+$segf);

		//ejecuto operacion
		$dif   = $fin-$ini;

		/**********************  Retorno datos  **********************/
		return $this->Convertions->segundos2horas($dif);

	}

	/************************************************************************************************************/
	public function sumarhoras($hora,$horaSuma): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite la suma de dos horas, dando como resultado otro dato con formato hora
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se ejecuta operacion
		* 	$DataOperations->sumarhoras('14:00:00', '07:00:00'); //Devuelve 21:00:00
		*
		*=================================================    Parametros   =================================================
		* @input   time     $hora        Hora ingresada
		* @input   time     $horaSuma    Cantidad de horas a sumar
		* @return  time
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!$this->DataValidations->validarHora($hora)){ return 'El dato ingresado no es una hora ('.$hora.')';}

		/********************** Si todo esta ok **********************/
		//Separo la hora
		$hora     = explode(":",$hora);
		$horaSuma = explode(":",$horaSuma);

		//obtengo valores por separado
		$horai = $hora[0];
		$mini  = $hora[1];
		$segi  = $hora[2];

		//obtengo valores por separado
		$horaf = $horaSuma[0];
		$minf  = $horaSuma[1];
		$segf  = $horaSuma[2];

		//transformo a segundos
		$ini   = ((($horai*60)*60)+($mini*60)+$segi);
		$fin   = ((($horaf*60)*60)+($minf*60)+$segf);

		//ejecuto operacion
		$dif   = $fin+$ini;

		/**********************  Retorno datos  **********************/
		return $this->Convertions->segundos2horas($dif);

	}

	/************************************************************************************************************/
	public function sumarDias($Fecha,int $nDias): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite sumar n cantidad de dias a una fecha entregada
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se ejecuta operacion
		* 	$DataOperations->sumarDias('2019-01-02', 5); //Devuelve 2019-01-07
		*
		*=================================================    Parametros   =================================================
		* @input   date     $Fecha   Fecha entregada
		* @input   int      $nDias   Cantidad de dias a sumar
		* @return  date
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!$this->DataValidations->validarFecha($Fecha)){   return 'El dato ingresado no es una fecha ('.$Fecha.')';}
		if (!$this->DataValidations->validarNumero($nDias) || !$this->DataValidations->validarEntero($nDias)) {
            return 'Verificar que el dato ingresado sea un numero';
        }

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return date('Y-m-d', strtotime( '+'.$nDias.' day' , strtotime($Fecha)));

	}

	/************************************************************************************************************/
	public function restarDias($Fecha,int $nDias): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite restar n cantidad de dias a una fecha entregada
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se ejecuta operacion
		* 	$DataOperations->restarDias('2019-01-07', 5); //Devuelve 2019-01-02
		*
		*=================================================    Parametros   =================================================
		* @input   date     $Fecha   Fecha entregada
		* @input   int      $nDias   Cantidad de dias a restar
		* @return  date
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!$this->DataValidations->validarFecha($Fecha)){   return 'El dato ingresado no es una fecha ('.$Fecha.')';}
		if (!$this->DataValidations->validarNumero($nDias) || !$this->DataValidations->validarEntero($nDias)) {
            return 'Verificar que el dato ingresado sea un numero';
        }

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return date('Y-m-d', strtotime( '-'.$nDias.' day' , strtotime($Fecha)));

	}

	/************************************************************************************************************/
	public function obtenerEdad($fechaNacimiento): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite ver el numero de los años y meses transcurridos a partir de una fecha entregada y la fecha actual
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se ejecuta operacion
		* 	$DataOperations->obtenerEdad('2022-01-01'); //Devuelve 'dos años, 5 meses' (a la fecha '2024-06-01')
		*
		*=================================================    Parametros   =================================================
		* @input   date     $fechaNacimiento   Fecha
		* @return  int
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!$this->DataValidations->validarFecha($fechaNacimiento)){ return 'Las fechas ingresadas no tienen formato fecha';}

		/********************** Si todo esta ok **********************/
		$nacimiento = new DateTime($fechaNacimiento);
		$ahora      = new DateTime(date("Y-m-d"));
		$diferencia = $ahora->diff($nacimiento);

		/**********************  Retorno datos  **********************/
		return $diferencia->format("%y").' años, '.$diferencia->format("%m").' meses';

	}

	/************************************************************************************************************/
	public function obtenerNumeroAnos($fechaNacimiento): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite ver el el numero de años transcurridos entre la fecha entregada y la actual
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se ejecuta operacion
		* 	$DataOperations->obtenerNumeroAnos('2022-01-01'); //Devuelve '2' (a la fecha '2024-06-01')
		*
		*=================================================    Parametros   =================================================
		* @input   date     $fechaNacimiento   Fecha
		* @return  int
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!$this->DataValidations->validarFecha($fechaNacimiento)){ return 'Las fechas ingresadas no tienen formato fecha';}

		/********************** Si todo esta ok **********************/
		$nacimiento = new DateTime($fechaNacimiento);
		$ahora      = new DateTime(date("Y-m-d"));
		$diferencia = $ahora->diff($nacimiento);

		/**********************  Retorno datos  **********************/
		return $diferencia->format("%y");

	}

	/************************************************************************************************************/
	public function diasTranscurridos($fechaInicio,$fechaTermino): string | float | int {
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite ver el numero de los dias transcurridos entre dos fechas entregadas
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se ejecuta operacion
		* 	$DataOperations->diasTranscurridos('2019-01-02', '2019-02-02'); //Devuelve 31
		*
		*=================================================    Parametros   =================================================
		* @input   date     $fechaInicio    Fecha de inicio
		* @input   date     $fechaTermino   Fecha de termino
		* @return  int
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if (!$this->DataValidations->validarFecha($fechaInicio) || !$this->DataValidations->validarFecha($fechaTermino)) {
            return 'Verificar que el dato ingresado sea una fecha';
        }

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return floor(abs((strtotime($fechaInicio)-strtotime($fechaTermino))/86400));

	}

	/************************************************************************************************************/
	public function horasTranscurridas($fechaInicio, $fechaTermino, $horaInicio, $horaTermino): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Se calcula la diferencia de meses en base a dos fechas
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se ejecuta operacion
		* 	$DataOperations->horasTranscurridas('2019-01-02', '2019-02-02', '14:00:00', '07:00:00'); //Devuelve 737:00:00
		*
		*=================================================    Parametros   =================================================
		* @input   date     $fechaInicio    Fecha de inicio
		* @input   date     $fechaTermino   Fecha de termino
		* @input   time     $horaInicio     Hora de inicio
		* @input   time     $horaTermino    Hora de termino
		* @return  time
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if (!$this->DataValidations->validarFecha($fechaInicio) || !$this->DataValidations->validarFecha($fechaTermino)) {
            return 'Verificar que el dato ingresado sea una fecha';
        }
		if (!$this->DataValidations->validarHora($horaInicio) || !$this->DataValidations->validarHora($horaTermino)) {
            return 'Verificar que el dato ingresado sea una hora';
        }

		/********************** Si todo esta ok **********************/
		$n_dias     = $this->diasTranscurridos($fechaInicio,$fechaTermino); //calculo diferencia de dias
		$HorasTrans = $this->restarhoras($horaInicio, $horaTermino);      //calculo del tiempo transcurrido
		//Sumo el tiempo por los dias transcurridos
		if($n_dias!=0){
			if($n_dias>=2){
				$n_dias_temp  = $n_dias-1;
				$horas_trans  = $this->multiplicarHoras('24:00:00',$n_dias_temp);
				$HorasTrans   = $this->sumarhoras($HorasTrans,$horas_trans);
			}
			if($n_dias==1&&$horaInicio<$horaTermino){
				$horas_trans  = $this->multiplicarHoras('24:00:00',$n_dias);
				$HorasTrans   = $this->sumarhoras($HorasTrans,$horas_trans);
			}
		}

		/**********************  Retorno datos  **********************/
		//devuelvo la cantidad de horas transcurridas
		return $HorasTrans;

	}

	/************************************************************************************************************/
	public function diferenciaMeses( $fechaInicio, $fechaTermino ): string | int {
		/*
		*=================================================     Detalles    =================================================
		*
		* Se calcula la diferencia de meses en base a dos fechas
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se ejecuta operacion
		* 	$DataOperations->diferenciaMeses('2019-01-02', '2019-02-02'); //Devuelve 1
		*
		*=================================================    Parametros   =================================================
		* @input   date     $fechaInicio   Fecha de inicio
		* @input   date     $fechaTermino  Fecha de termino
		* @return  int
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if (!$this->DataValidations->validarFecha($fechaInicio) || !$this->DataValidations->validarFecha($fechaTermino)) {
            return 'Verificar que el dato ingresado sea una fecha';
        }

		/********************** Si todo esta ok **********************/
		$datetime1 = new DateTime($fechaInicio);
		$datetime2 = new DateTime($fechaTermino);

		//operaciones
		$interval      = $datetime2->diff($datetime1); // obtenemos la diferencia entre las dos fechas
		$intervalMeses = $interval->format("%m");      // obtenemos la diferencia en meses
		$intervalAnos  = $interval->format("%y")*12;   // obtenemos la diferencia en años y la multiplicamos por 12 para tener los meses

		/**********************  Retorno datos  **********************/
		return $intervalMeses + $intervalAnos;

	}

}
