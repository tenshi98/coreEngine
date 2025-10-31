<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsDataDate {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                 Instancias                                                      */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	//Definiciones
	private $DataValidations;
	const OptionsMesLargo  = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
	const OptionsMesCorto  = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
	const OptionsDiaSemana = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'];

	/************************************************************************************************************/
	//Instancias
	public function __construct() {
		$this->DataValidations  = new FunctionsDataValidations();
	}


	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	public function fechaCompleta($Fecha): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Se formatea la fecha ingresada a una con un formato personalizado
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea fecha
		* 	$DataDate->fechaCompleta('2024-01-01'); //Devuelve enero 01 del 2024
		*
		*=================================================    Parametros   =================================================
		* @input   date     $Fecha   Fecha a Formatear
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		$dataVal = $this->_validateDate($Fecha);
		if ($dataVal !== true) { return $dataVal;}

		/********************** Si todo esta ok **********************/
		$mes_c   = new DateTime($Fecha);
		$dia     = $mes_c->format('d');
		$ano     = $mes_c->format('Y');
		$mes     = self::OptionsMesLargo[$mes_c->format('m') - 1];

		/**********************  Retorno datos  **********************/
		return $mes.' '.$dia.' del '.$ano;

	}

	/************************************************************************************************************/
	public function fechaCompletaAlt($Fecha): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Se formatea la fecha ingresada a una con un formato personalizado
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea fecha
		* 	$DataDate->fechaCompletaAlt('2024-01-01'); //Devuelve 01 de enero de 2024
		*
		*=================================================    Parametros   =================================================
		* @input   date     $Fecha    Fecha a Formatear
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		$dataVal = $this->_validateDate($Fecha);
		if ($dataVal !== true) { return $dataVal;}

		/********************** Si todo esta ok **********************/
		$mes_c   = new DateTime($Fecha);
		$dia     = $mes_c->format('d');
		$ano     = $mes_c->format('Y');
		$mes     = self::OptionsMesLargo[$mes_c->format('m') - 1];

		/**********************  Retorno datos  **********************/
		return $dia.' de '.$mes.' de '.$ano;

	}

	/************************************************************************************************************/
	public function diaMes($Fecha): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Se formatea la fecha ingresada a una con un formato personalizado
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea fecha
		* 	$DataDate->diaMes('2024-01-01'); //Devuelve 01 Enero
		*
		*=================================================    Parametros   =================================================
		* @input    date     $Fecha    Fecha a Formatear
		* @return   string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		$dataVal = $this->_validateDate($Fecha);
		if ($dataVal !== true) { return $dataVal;}

		/********************** Si todo esta ok **********************/
		$mes_c   = new DateTime($Fecha);
		$dia     = $mes_c->format('d');
		$mes     = self::OptionsMesLargo[$mes_c->format('m') - 1];

		/**********************  Retorno datos  **********************/
		return $dia.' '.$mes;

	}

	/************************************************************************************************************/
	public function fechaEstandar($Fecha): DateTime | string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Se formatea la fecha ingresada a una con un formato personalizado
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea fecha
		* 	$DataDate->fechaEstandar('2024-01-01'); //Devuelve 01-01-2024
		*
		*=================================================    Parametros   =================================================
		* @input   date     $Fecha    Fecha a Formatear
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		$dataVal = $this->_validateDate($Fecha);
		if ($dataVal !== true) { return $dataVal;}

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return date_format(date_create($Fecha), 'd-m-Y');

	}

	/************************************************************************************************************/
	public function fechaEstandarCorta($Fecha): DateTime | string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Se formatea la fecha ingresada a una con un formato personalizado
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea fecha
		* 	$DataDate->fechaEstandarCorta('2024-01-01'); //Devuelve 01-01-24
		*
		*=================================================    Parametros   =================================================
		* @input   date     $Fecha   Fecha a Formatear
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		$dataVal = $this->_validateDate($Fecha);
		if ($dataVal !== true) { return $dataVal;}

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return date_format(date_create($Fecha), 'd-m-y');

	}

	/************************************************************************************************************/
	public function fechaNormalizada($Fecha): DateTime | string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Se formatea la fecha ingresada a una con un formato personalizado
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea fecha
		* 	$DataDate->fechaNormalizada('2024-01-01'); //Devuelve 2024-01-01
		*
		*=================================================    Parametros   =================================================
		* @input   date     $Fecha   Fecha a Formatear
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		$dataVal = $this->_validateDate($Fecha);
		if ($dataVal !== true) { return $dataVal;}

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return date_format(date_create(str_replace('/', '-', $Fecha)), 'Y-m-d');

	}

	/************************************************************************************************************/
	public function fechaArchivos($Fecha): DateTime | string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Se formatea la fecha ingresada a una con un formato personalizado
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea fecha
		* 	$DataDate->fechaArchivos('2024-01-01'); //Devuelve 20240101
		*
		*=================================================    Parametros   =================================================
		* @input   date     $Fecha   Fecha a Formatear
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		$dataVal = $this->_validateDate($Fecha);
		if ($dataVal !== true) { return $dataVal;}

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return date_format(date_create(str_replace('/', '-', $Fecha)), 'Ymd');

	}

	/************************************************************************************************************/
	public function fechaMesAno($Fecha): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Se formatea la fecha ingresada a una con un formato personalizado
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea fecha
		* 	$DataDate->fechaMesAno('2024-01-01'); //Devuelve Enero del 2024
		*
		*=================================================    Parametros   =================================================
		* @input    date     $Fecha   Fecha a Formatear
		* @return   string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		$dataVal = $this->_validateDate($Fecha);
		if ($dataVal !== true) { return $dataVal;}

		/********************** Si todo esta ok **********************/
		$mes_c   = new DateTime($Fecha);
		$ano     = $mes_c->format('Y');
		$mes     = self::OptionsMesLargo[$mes_c->format('m') - 1];

		/**********************  Retorno datos  **********************/
		return $mes.' del '.$ano;

	}

	/************************************************************************************************************/
	public function fecha2NdiaMes($Fecha): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite obtener el numero del dia en el mes a partir de la fecha ingresada, del 1 al 31
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se obtiene el numero del dia
		* 	$DataDate->fecha2NdiaMes('2024-01-02'); //Devuelve 2
		*
		*=================================================    Parametros   =================================================
		* @input   date     $Fecha   Fecha a Formatear
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		$dataVal = $this->_validateDate($Fecha);
		if ($dataVal !== true) { return $dataVal;}

		/********************** Si todo esta ok **********************/
		$subdato = new DateTime($Fecha);

		/**********************  Retorno datos  **********************/
		return $subdato->format("j");

	}

	/************************************************************************************************************/
	public function fecha2NdiaMesCon0($Fecha): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite obtener el numero del dia en el mes a partir de la fecha ingresada, 2 dígitos con ceros iniciales (1 al 31)
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea fecha
		* 	$DataDate->fecha2NdiaMesCon0('2024-01-01'); //Devuelve 01
		*
		*=================================================    Parametros   =================================================
		* @input   date     $Fecha   Fecha a Formatear
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		$dataVal = $this->_validateDate($Fecha);
		if ($dataVal !== true) { return $dataVal;}

		/********************** Si todo esta ok **********************/
		$subdato = new DateTime($Fecha);

		/**********************  Retorno datos  **********************/
		return $subdato->format('d');

	}

	/************************************************************************************************************/
	public function fecha2NDiaSemana($Fecha): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Muestra el numero del dia dentro de la semana, siendo 1 (para lunes) hasta 7 (para domingo)
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea fecha
		* 	$DataDate->fecha2NDiaSemana('2024-01-01'); //Devuelve 1
		*
		*=================================================    Parametros   =================================================
		* @input   date     $Fecha   Fecha a Formatear
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		$dataVal = $this->_validateDate($Fecha);
		if ($dataVal !== true) { return $dataVal;}

		/********************** Si todo esta ok **********************/
		$subdato = new DateTime($Fecha);

		/**********************  Retorno datos  **********************/
		return $subdato->format('N');

	}

	/************************************************************************************************************/
	public function fecha2NombreDia($Fecha): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Devuelve el nombre del dia en base a la fecha ingresada (lunes a domingo)
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se transforma los datos
		* 	$DataDate->fecha2NombreDia('2024-01-02'); //Devuelve Martes
		*
		*=================================================    Parametros   =================================================
		* @input    date     $Fecha   Fecha a Formatear
		* @return   string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		$dataVal = $this->_validateDate($Fecha);
		if ($dataVal !== true) { return $dataVal;}

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return self::OptionsDiaSemana[$this->fecha2NDiaSemana($Fecha) - 1];

	}

	/************************************************************************************************************/
	public function fecha2NSemana($Fecha): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Se obtiene el numero de la semana en base a la fecha entregada
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea fecha
		* 	$DataDate->fecha2NSemana('2024-01-01'); //Devuelve 01
		*
		*=================================================    Parametros   =================================================
		* @input   date     $Fecha   Fecha a Formatear
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		$dataVal = $this->_validateDate($Fecha);
		if ($dataVal !== true) { return $dataVal;}

		/********************** Si todo esta ok **********************/
		$subdato = new DateTime($Fecha);

		/**********************  Retorno datos  **********************/
		return $subdato->format("W");

	}

	/************************************************************************************************************/
	public function fecha2NMes($Fecha): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Se obtiene el numero del mes en base a la fecha entregada (1 a 12)
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea fecha
		* 	$DataDate->fecha2NMes('2024-01-01'); //Devuelve 1
		*
		*=================================================    Parametros   =================================================
		* @input   date     $Fecha  Fecha a Formatear
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		$dataVal = $this->_validateDate($Fecha);
		if ($dataVal !== true) { return $dataVal;}

		/********************** Si todo esta ok **********************/
		$subdato = new DateTime($Fecha);

		/**********************  Retorno datos  **********************/
		return $subdato->format("n");

	}

	/************************************************************************************************************/
	public function fecha2NombreMes($Fecha): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Se obtiene el nombre del mes en base a una fecha ingresada
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea fecha
		* 	$DataDate->fecha2NombreMes('2024-01-01'); //Devuelve Enero
		*
		*=================================================    Parametros   =================================================
		* @input    date     $Fecha   Fecha a Formatear
		* @return   string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		$dataVal = $this->_validateDate($Fecha);
		if ($dataVal !== true) { return $dataVal;}

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return self::OptionsMesLargo[$this->fecha2NMes($Fecha) - 1];

	}

	/************************************************************************************************************/
	public function fecha2NombreMesCorto($Fecha): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Se obtiene el nombre abreviado (3 primeras letras) del mes en base a una fecha ingresada
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea fecha
		* 	$DataDate->fecha2NombreMesCorto('2024-01-01'); //Devuelve Ene
		*
		*=================================================    Parametros   =================================================
		* @input    date     $Fecha   Fecha a Formatear
		* @return   string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		$dataVal = $this->_validateDate($Fecha);
		if ($dataVal !== true) { return $dataVal;}

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return self::OptionsMesCorto[$this->fecha2NMes($Fecha) - 1];

	}

	/************************************************************************************************************/
	public function fecha2Ano($Fecha): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Obtiene el Año en base a la fecha ingresada
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea fecha
		* 	$DataDate->fecha2Ano('2024-01-01'); //Devuelve 2024
		*
		*=================================================    Parametros   =================================================
		* @input   date     $Fecha   Fecha a Formatear
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		$dataVal = $this->_validateDate($Fecha);
		if ($dataVal !== true) { return $dataVal;}

		/********************** Si todo esta ok **********************/
		$subdato = new DateTime($Fecha);

		/**********************  Retorno datos  **********************/
		return $subdato->format('Y');

	}

	/************************************************************************************************************/
	public function fechaGringa($Fecha): DateTime | string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Se obtiene la descripcion de la fecha en ingles, una representación textual completa de un mes, como January o
		* March, el numero del dia y el año
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea fecha
		* 	$DataDate->fechaGringa('2024-01-01'); //Devuelve January 01 2024
		*
		*=================================================    Parametros   =================================================
		* @input    date     $Fecha   Fecha a Formatear
		* @return   string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		$dataVal = $this->_validateDate($Fecha);
		if ($dataVal !== true) { return $dataVal;}

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return date_format(date_create($Fecha), 'F d Y');

	}

	/************************************************************************************************************/
	public function fechaUltimoDiaMes($Fecha): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Se obtiene el ultimo dia del mes de la fecha entregada
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se formatea fecha
		* 	$DataDate->fechaUltimoDiaMes('2024-01-01'); //Devuelve '2024-01-31'
		*
		*=================================================    Parametros   =================================================
		* @input    date     $Fecha   Fecha a usar
		* @return   string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		$dataVal = $this->_validateDate($Fecha);
		if ($dataVal !== true) { return $dataVal;}

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return date("Y-m-t", strtotime($Fecha));

	}

	/************************************************************************************************************/
	public function fullDate($Fecha): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Devuelve la fecha formateada: Diciembre 12 del 2023 13:17:59
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se convierten los datos
		* 	$DataDate->fullDate('2023-12-12 13:17:59');
		*
		*=================================================    Parametros   =================================================
		* @input    date       $Fecha   Fecha a transformar
		* @return   string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if($Fecha=='' || $Fecha=='0000-00-00'){                           return 'Sin Fecha';}
		if(!$this->DataValidations->validarFecha($Fecha, 'Y-m-d H:i:s')){ return 'El dato ingresado no es una fecha ('.$Fecha.')';}

		/********************** Si todo esta ok **********************/
		// Establecer la zona horaria predeterminada a usar.
		date_default_timezone_set('America/Santiago');
		//Se formatea
		$NewFecha = new DateTime($Fecha);
		$dia     = $NewFecha->format('d');
		$ano     = $NewFecha->format('Y');
		$hora    = $NewFecha->format('H:i:s');
		$mes     = self::OptionsMesLargo[$NewFecha->format('m') - 1];

		/**********************  Retorno datos  **********************/
		return $mes.' '.$dia.' del '.$ano.' '.$hora;

	}


	/************************************************************************************************************/
	private function _validateDate($Fecha){

		/**********************  Validaciones   **********************/
		if($Fecha=='' || $Fecha=='0000-00-00' || $Fecha=='00-00-0000'){   return 'Sin Fecha';}
		if(!$this->DataValidations->validarFecha($Fecha)){                return 'El dato ingresado no es una fecha ('.$Fecha.')';}

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return true;

	}

}
