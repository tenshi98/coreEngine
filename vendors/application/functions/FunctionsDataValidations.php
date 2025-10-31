<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsDataValidations {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	public function validarRut($Data):bool {
		/*
		*=================================================     Detalles    =================================================
		*
		* Valida si el dato ingresado es un rut chileno
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se valida dato
		* 	$DataValidations->validarRut('10.569.874-5');
		*
		*=================================================    Parametros   =================================================
		* @input   string   $Data    Dato a validar
		* @return  bolean
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if($Data=='' || $Data=='0'){ return false;}

		/********************** Si todo esta ok **********************/
		//elimino el punto
		$rut = str_replace('.', '', $Data);

		// Verifica que no esté vacio y que el string sea de tamaño mayor a 3 carácteres(1-9)
		if ((empty($rut)) || strlen($rut) < 3) {
			return false; //RUT vacío o con menos de 3 caracteres.
		}

		// Quitar los últimos 2 valores (el guión y el dígito verificador) y luego verificar que sólo sea
		// numérico
		$parteNumerica = str_replace(substr($rut, -2, 2), '', $rut);

		if (!preg_match("/^[0-9]*$/", $parteNumerica)) {
			return false; //La parte numérica del RUT sólo debe contener números.
		}

		$guionYVerificador = substr($rut, -2, 2);
		// Verifica que el guion y dígito verificador tengan un largo de 2.
		if (strlen($guionYVerificador) != 2) {
			return false; //Error en el largo del dígito verificador.
		}

		// obliga a que el dígito verificador tenga la forma -[0-9] o -[kK]
		if (!preg_match('/(^[-]{1}+[0-9kK]).{0}$/', $guionYVerificador)) {
			return false; //El dígito verificador no cuenta con el patrón requerido
		}

		// Valida que sólo sean números, excepto el último dígito que pueda ser k
		if (!preg_match("/^[0-9.]+[-]?+[0-9kK]{1}/", $rut)) {
			return false; //Error al digitar el RUT
		}

		$rutV   = preg_replace('/[\.\-]/i', '', $rut);
		$dv     = substr($rutV, -1);
		$numero = substr($rutV, 0, strlen($rutV) - 1);
		$i      = 2;
		$suma   = 0;
		foreach (array_reverse(str_split($numero)) as $v) {
			if ($i == 8) {
				$i = 2;
			}
			$suma += $v * $i;
			++$i;
		}
		$dvr = 11 - ($suma % 11);
		if ($dvr == 11) {$dvr = 0;}
		if ($dvr == 10) {$dvr = 'K';}

		/**********************  Retorno datos  **********************/
		//Si todo esta ok
		return ($dvr == strtoupper($dv)) ? true : false;

	}

	/************************************************************************************************************/
	public function validarEmail($Data):bool{
		/*
		*=================================================     Detalles    =================================================
		*
		* Valida si el dato ingresado es un correo
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se valida dato
		* 	$DataValidations->validarEmail('asd@asd.cl'); //Devuelve true
		* 	$DataValidations->validarEmail('asd@asd');    //Devuelve false
		*
		*=================================================    Parametros   =================================================
		* @input   string   $Data    Dato a validar
		* @return  bolean
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if($Data==''){ return false;}

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return (filter_var($Data,FILTER_VALIDATE_EMAIL)) ? true : false;

	}

	/************************************************************************************************************/
	public function validarNumero($Data):bool{
		/*
		*=================================================     Detalles    =================================================
		*
		* Valida si el dato ingresado es un numero (acepta negativos y decimales)
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se valida dato
		* 	$DataValidations->validarNumero(25);   //Devuelve true
		* 	$DataValidations->validarNumero('25'); //Devuelve false
		*
		*=================================================    Parametros   =================================================
		* @input   decimal  $Data    Dato a validar
		* @return  bolean
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if($Data==''){ return false;}

		/********************** Si todo esta ok **********************/
		//cambio la coma por puntos para evitar problemas con los decimales
		$number = str_replace(',', '.', $Data);

		/**********************  Retorno datos  **********************/
		//Si todo esta ok
		return is_numeric($number);

	}

	/************************************************************************************************************/
	public function ValidarPatente($Data):bool{
		/*
		*=================================================     Detalles    =================================================
		*
		* Valida si el dato ingresado es una patente chilena
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se valida dato
		* 	$DataValidations->ValidarPatente('AU1825');  //Devuelve true
		* 	$DataValidations->ValidarPatente('512369');  //Devuelve false
		*
		*=================================================    Parametros   =================================================
		* @input   string   $Data    Dato a validar
		* @return  bolean
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if($Data==''){ return false;}

		/********************** Si todo esta ok **********************/
		//elimino los posibles guones
		$patente = str_replace("-","",$Data);
		//caracteres admitidos
		$regex = '/^[a-z]{2}[\.\- ]?[0-9]{2}[\.\- ]?[0-9]{2}|[b-d,f-h,j-l,p,r-t,v-z]{2}[\-\. ]?[b-d,f-h,j-l,p,r-t,v-z]{2}[\.\- ]?[0-9]{2}$/i';

		/**********************  Retorno datos  **********************/
		//Si todo esta ok
		return (preg_match($regex, $patente)) ? true : false;

	}

	/************************************************************************************************************/
	public function validarURL($Data):bool{
		/*
		*=================================================     Detalles    =================================================
		*
		* Valida si el dato ingresado es una URL
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se valida dato
		* 	$DataValidations->validarURL(https://www.google.cl');  //Devuelve true
		* 	$DataValidations->validarURL(https://www.  SSS  ');    //Devuelve false
		*
		*=================================================    Parametros   =================================================
		* @input   string   $Data    Dato a validar
		* @return  bolean
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if($Data==''){ return false;}

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return (bool) filter_var($Data, FILTER_VALIDATE_URL);

	}

	/************************************************************************************************************/
	public function validarHora($Data):bool {
		/*
		*=================================================     Detalles    =================================================
		*
		* Valida si el dato ingresado es una hora, con un tope maximo de 99 horas
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se valida dato
		* 	$DataValidations->validarHora('16:24:00'); //Devuelve true
		* 	$DataValidations->validarHora(16);         //Devuelve false
		*
		*=================================================    Parametros   =================================================
		* @input   time     $Data     Dato a validar
		* @return  bolean
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if($Data==''){ return false;}

		/********************** Si todo esta ok **********************/
		// Expresión Regular (RegEx) para el formato HHH:MM:SS
		// Explicación:
		// ^                   # Inicio de la cadena
		// (?:[0-9]{1,3})      # Grupo 1 (Horas): Coincide con 1 a 3 dígitos (0 a 999)
		// :                   # Separador de dos puntos (:)
		// (?:[0-5][0-9])      # Grupo 2 (Minutos): Coincide con 00 a 59
		// :                   # Separador de dos puntos (:)
		// (?:[0-5][0-9])      # Grupo 3 (Segundos): Coincide con 00 a 59
		// $                   # Fin de la cadena

		$patron = '/^(?:[0-9]{1,3}):(?:[0-5][0-9]):(?:[0-5][0-9])$/';

		/**********************  Retorno datos  **********************/
		// Verifica si la cadena coincide con el patrón
		if (preg_match($patron, $Data)) {
			// Validación adicional: Asegura que las horas no excedan 999
			$partes = explode(':', $Data);
			$horas = (int)$partes[0];

			return $horas <= 999;
		}

		/**********************  Retorno datos  **********************/
		//Si todo esta ok
		return false;

	}

	/************************************************************************************************************/
	public function validarFecha($Data, $format = 'Y-m-d'):bool{
		/*
		*=================================================     Detalles    =================================================
		*
		* Valida si el dato ingresado es una fecha
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se valida dato
		* 	$DataValidations->validarFecha('1900-01-01');          //Devuelve true
		* 	$DataValidations->validarFecha('1900-01-01', 'Y-m-d'); //Devuelve true
		* 	$DataValidations->validarFecha(1900-01-01);            //Devuelve false
		*
		*=================================================    Parametros   =================================================
		* @input   date     $Data     Dato a validar
		* @input   string   $format   (Opcional) formato a validar
		* @return  bolean
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if($Data=='' || $Data=='0000-00-00'){ return false;}

		/********************** Si todo esta ok **********************/
		$d = DateTime::createFromFormat($format, $Data);

		/**********************  Retorno datos  **********************/
		return $d && $d->format($format) == $Data;

	}

	/************************************************************************************************************/
	public function validarEntero($Data):bool{
		/*
		*=================================================     Detalles    =================================================
		*
		* Valida si el dato ingresado es un numero entero
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se valida dato
		* 	$DataValidations->validarEntero(16);   //Devuelve true
		* 	$DataValidations->validarEntero('16'); //Devuelve false
		*
		*=================================================    Parametros   =================================================
		* @input   int      $Data    Dato a validar
		* @return  bolean
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if($Data==''){ return false;}

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return (is_numeric($Data)) ? ctype_digit(strval($Data)) : false;

	}

	/************************************************************************************************************/
	public function validarDispositivoMovil():bool{
		/*
		*=================================================     Detalles    =================================================
		*
		* Valida si el dispositivo es un dispositivo movil
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se valida dato
		* 	$DataValidations->validarDispositivoMovil();
		*
		*=================================================    Parametros   =================================================
		* @return  bolean
		*===================================================================================================================
		*/

		$userAgent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');

		/********************** Si todo esta ok **********************/
		// Lista simplificada de palabras clave comunes en móviles
		$movilKeywords = [
			'android', 'iphone', 'ipod', 'ipad', 'blackberry', 'windows phone',
			'opera mini', 'opera mobi', 'mobile', 'silk', 'kindle', 'webos',
			'palm', 'symbian', 'fennec', 'maemo', 'nokia', 'htc', 'samsung',
			'lg', 'motorola', 'tablet', 'playbook'
		];

		/**********************  Retorno datos  **********************/
		foreach ($movilKeywords as $keyword) {
			if (strpos($userAgent, $keyword) !== false) {
				return true;
			}
		}
		//valor si no se valida nada
		return false;

	}

	/************************************************************************************************************/
	public function validarLargoMinimo($oracion, int $largo):bool{
		/*
		*=================================================     Detalles    =================================================
		*
		* Verifica la cantidad de caracteres maximo dentro de la palabra u oracion a revisar
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se ejecuta operacion
		* 	$DataValidations->validarLargoMinimo('Lorem ipsum dolor sit amet, consectetur', 10); //Devuelve 'El dato ingresado debe tener no mas de 10 caracteres'
		* 	$DataValidations->validarLargoMinimo('Lorem', 10); //Devuelve 1
		*
		*=================================================    Parametros   =================================================
		* @input   string   $oracion   Palabra u oracion entregada
		* @input   int      $largo     Cantidad de caracteres maximo a admitir
		* @return  bolean
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if (!$this->validarNumero($largo)){  return false;}
		if (!$this->validarEntero($largo)){  return false;}

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return strlen($oracion) >= $largo;

	}

	/************************************************************************************************************/
	public function validarLargoMaximo($oracion, int $largo):bool{
		/*
		*=================================================     Detalles    =================================================
		*
		* Verifica la cantidad de caracteres minimos dentro de la palabra u oracion a revisar
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se ejecuta operacion
		* 	$DataValidations->validarLargoMaximo('Lorem', 10); //Devuelve 'El dato ingresado debe tener al menos 10 caracteres'
		* 	$DataValidations->validarLargoMaximo('Lorem ipsum dolor sit amet, consectetur', 10); //Devuelve 1
		*
		*=================================================    Parametros   =================================================
		* @input   string   $oracion   Palabra u oracion entregada
		* @input   int      $largo     Cantidad de caracteres minimos a admitir
		* @return  bool
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if (!$this->validarNumero($largo)){  return false;}
		if (!$this->validarEntero($largo)){  return false;}

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return strlen($oracion) <= $largo;

	}

	/****************************************************************************************/
	//Crea el input en base a los datos
	public function checkData($validOptions, $dataToCheck, $placeholder, $type): array{
		/*
		*=================================================     Detalles    =================================================
		*
		* Verifica la cantidad de caracteres minimos dentro de la palabra u oracion a revisar
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se ejecuta operacion
		* 	$DataValidations->validarLargoMaximo('Lorem', 10); //Devuelve 'El dato ingresado debe tener al menos 10 caracteres'
		* 	$DataValidations->validarLargoMaximo('Lorem ipsum dolor sit amet, consectetur', 10); //Devuelve 1
		*
		*=================================================    Parametros   =================================================
		* @input   string   $oracion   Palabra u oracion entregada
		* @input   int      $largo     Cantidad de caracteres minimos a admitir
		* @return  bool
		*===================================================================================================================
		*/

		/**********************  Definiciones   **********************/
		$dataReturn['nErrors'] = 0;
		$dataReturn['alerts']  = '';
		$Alertas               = new UIWidgetsCommon();

		/**********************  Validaciones   **********************/
		switch ($type) {
			/*****************************************/
			case 1:
				//Validar cada opción
				foreach($dataToCheck as $option) {
					if (!in_array($option['value'], $validOptions[$option['name']], true)) {
						$dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, sprintf('La configuración %s (%s) entregada no está dentro de las opciones',$option['label'],$option['value']));
						$dataReturn['nErrors']++;
					}
				}
				break;
			/*****************************************/
			case 2:
				//Check each option against valid values
				foreach($dataToCheck as $option) {
					if (!in_array($option['value'], $validOptions[$option['name']], true)) {
						$dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, sprintf('La configuración %s (%s) entregada en <strong>%s</strong> no esta dentro de las opciones',$option['label'],$option['value'],$placeholder));
						$dataReturn['nErrors']++;
					}
				}
				break;
			/*****************************************/
			case 3:
				//Validar cada opción
				foreach ($dataToCheck as $field) {
					if (!$this->{$field['method']}($field['value']) && $field['value'] != '') {
						$dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'El valor ingresado en '.$field['label'].' ('.$field['value'].') en <strong>'.$placeholder.'</strong> '.$field['msg']);
						$dataReturn['nErrors']++;
					}
				}
				break;
			/*****************************************/
			case 4:
				//Validar cada opción
				foreach($dataToCheck as $option) {
					if (!in_array($option['value'], $validOptions[$option['name']], true)) {
						$dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, sprintf('La configuración %s (%s) entregada en <strong>%s</strong> no esta dentro de las opciones',$option['label'],$option['value'],$option['placeholder']));
						$dataReturn['nErrors']++;
					}
				}
				break;
			/*****************************************/
			case 5:
				//Validar cada opción
				foreach ($dataToCheck as $field) {
					if (!$this->{$field['method']}($field['value']) && $field['value'] != '') {
						$dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'El valor ingresado en '.$field['label'].' ('.$field['value'].') en <strong>'.$field['placeholder'].'</strong> '.$field['msg']);
						$dataReturn['nErrors']++;
					}
				}
				break;
			/*****************************************/
			case 6:
				//Validar cada opción
				foreach($dataToCheck as $option) {
					if (!in_array($option['value'], $validOptions[$option['name']], true)) {
						$dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, sprintf('La configuración %s (%s) entregada no esta dentro de las opciones',$option['label'],$option['value']));
						$dataReturn['nErrors']++;
					}
				}
				break;
			/*****************************************/
			case 7:
				if (!$this->validarNumero($dataToCheck)&&!$this->validarEntero($dataToCheck)&&$dataToCheck!=''){
					$dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'El valor ingresado en $value ('.$dataToCheck.') en <strong>'.$placeholder.'</strong> no es un numero o no es un numero entero');
                    $dataReturn['nErrors']++;
                }
				break;
			/*****************************************/
			case 8:
				if (!$this->validarFecha($dataToCheck)&&$dataToCheck!=''){
					$dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'El valor ingresado en $value ('.$dataToCheck.') en <strong>'.$placeholder.'</strong> no es una fecha');
                    $dataReturn['nErrors']++;
                }
				break;
			/*****************************************/
			case 9:
				if ($dataToCheck!=''){
					$dataReturn['alerts'] .= $Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'El valor ingresado en $value ('.$dataToCheck.') en <strong>'.$placeholder.'</strong> esta vacio');
                    $dataReturn['nErrors']++;
                }
				break;
		}

		/**********************  Retorno datos  **********************/
		return $dataReturn;

	}

}
