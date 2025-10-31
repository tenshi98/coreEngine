<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class CheckData{

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                 Instancias                                                      */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	//Definiciones
	private $DataValidations;
	private $DataText;
	private $Codification;
	private $CommonData;

	/******************************************************************************/
	//Instancias
	public function __construct() {
		$this->DataValidations = new FunctionsDataValidations();
		$this->DataText        = new FunctionsDataText();
		$this->Codification    = new FunctionsSecurityCodification();
		$this->CommonData      = new FunctionsCommonData();

	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
    /******************************************************************************/
    public function checkData($DataCheck){

        /******************************************/
        /*
            Formato de la query
            $DataCheck = [
                'emptyData'                 => 'mainPassword,oldPassword,password,rePassword', -> Se validan datos vacios al ejecutar formulario
                'encode'                    => 'oldPassword',                                  -> Codificar los datos
                'ValidarEmail'              => 'email',                                        -> Validacion si es email
                'ValidarNumero'             => '',                                             -> Validacion si es un numero
                'ValidarEntero'             => '',                                             -> Validacion si es un numero entero
                'ValidarRut'                => 'Rut',                                          -> Validacion si es un Rut
                'ValidarPatente'            => '',                                             -> Validacion si es una patente
                'ValidarFecha'              => 'fNacimiento',                                  -> Validacion si es una fecha
                'ValidarHora'               => '',                                             -> Validacion si es una hora
                'ValidarURL'                => '',                                             -> Validacion si es una url
                'ValidarLargoMinimo'        => '',                                             -> Validacion de los datos
                'ValidarLargoMinimoN'       => 3,
                'ValidarLargoMaximo'        => '',
                'ValidarLargoMaximoN'       => 255,
                'ValidarPalabrasCensuradas' => '',                                             -> Validacion si hay palabras censuradas
                'ValidarEspaciosVacios'     => 'password',                                     -> Validacion si hay espacios en los datos ingresados
                'ValidarMayusculas'         => '',                                             -> Validacion si hay letras mayusculas dentro del texto
                'ValidarCoincidencias'      => 'mainPassword-oldPassword,password-rePassword', -> Validacion si los datos son iguales
                'Post'                      => $DataPOST,                                      -> Datos entregados
            ];

        */

        /******************************************/
        //Variables
        $errors  = [];

        /******************************************/
        //Codificacion Datos
        if (!empty($DataCheck['emptyData'])){
            //Separo los datos
            $arrData = $this->CommonData->parseDataCommas($DataCheck['emptyData']); //Separacion por comas
            // Recorro y verifico datos vacíos
            foreach ($arrData as $data) {
                if (isset($DataCheck['Post'][$data]) && empty($DataCheck['Post'][$data])) {
                    $errors[] = ["message" => "No ha llenado $data."];
                }
            }
            // Si hay errores, retorno inmediatamente
            if (!empty($errors)) {
                return $errors;
            }
        }

        /******************************************/
        //Codificacion Datos
        if (!empty($DataCheck['encode'])){
            //Separo los datos
            $arrEncode = $this->CommonData->parseDataCommas($DataCheck['encode']); //Separacion por comas
            //recorro validando
            foreach ($arrEncode as $data) {
                if(isset($DataCheck['Post'][$data]) && $DataCheck['Post'][$data]!=''){
                    $DataCheck['Post'][$data] = $this->Codification->encryptDecrypt('encrypt',$DataCheck['Post'][$data],ConfigToken::ENCODE_KEYS["KEY_1"]);
                }
            }
        }

        /******************************************/
        // Datos a Validar
		$fieldsToCheck = [
			['fncName' => 'ValidarEmail',              'fncCheck' => '',                       'method' => 'validarEmail',                  'msgText' => 'no tiene un formato de email correcto',        'case' => 1],
			['fncName' => 'ValidarNumero',             'fncCheck' => '',                       'method' => 'validarNumero',                 'msgText' => 'no es validado como un número',                'case' => 1],
			['fncName' => 'ValidarEntero',             'fncCheck' => '',                       'method' => 'validarEntero',                 'msgText' => 'no es un número entero',                       'case' => 1],
			['fncName' => 'ValidarRut',                'fncCheck' => '',                       'method' => 'validarRut',                    'msgText' => 'no es un Rut válido',                          'case' => 1],
			['fncName' => 'ValidarPatente',            'fncCheck' => '',                       'method' => 'validarPatente',                'msgText' => 'no es una patente válida',                     'case' => 1],
			['fncName' => 'ValidarFecha',              'fncCheck' => '',                       'method' => 'validarFecha',                  'msgText' => 'no es una fecha válida',                       'case' => 1],
			['fncName' => 'ValidarHora',               'fncCheck' => '',                       'method' => 'validarHora',                   'msgText' => 'no es una hora válida',                        'case' => 1],
			['fncName' => 'ValidarURL',                'fncCheck' => '',                       'method' => 'validarURL',                    'msgText' => 'no es una URL válida',                         'case' => 1],
			['fncName' => 'ValidarLargoMinimo',        'fncCheck' => 'ValidarLargoMinimoN',    'method' => 'validarLargoMinimo',            'msgText' => 'no tiene el mínimo de caracteres requerido',   'case' => 2],
			['fncName' => 'ValidarLargoMaximo',        'fncCheck' => 'ValidarLargoMaximoN',    'method' => 'validarLargoMaximo',            'msgText' => 'no tiene el máximo de caracteres requerido',   'case' => 2],
			['fncName' => 'ValidarPalabrasCensuradas', 'fncCheck' => '',                       'method' => 'contarPalabrasCensuradas',      'msgText' => 'contiene palabras no permitidas',              'case' => 3],
			['fncName' => 'ValidarEspaciosVacios',     'fncCheck' => '',                       'method' => '',                              'msgText' => 'contiene espacios vacíos',                     'case' => 4],
			['fncName' => 'ValidarMayusculas',         'fncCheck' => '',                       'method' => '',                              'msgText' => 'contiene mayúsculas',                          'case' => 5],
			['fncName' => 'ValidarCoincidencias',      'fncCheck' => '',                       'method' => '',                              'msgText' => 'Los datos ingresados no coinciden',            'case' => 6],
		];

		//Validar cada opción
		foreach ($fieldsToCheck as $field) {
            //Verifico si existe el input POST esperado
            if(isset($DataCheck[$field['fncName']])&&$DataCheck[$field['fncName']]!=''){
                //En caso de existir, se separan los input a revisar en un array
                $arrData = $this->CommonData->parseDataCommas($DataCheck[$field['fncName']]); //Separacion por comas
                //Dependiendo del tipo de input, es el tipo de validacion
                switch ($field['case']) {
                    /*****************/
                    //Validacion (Email, Rut, patente, etc)
                    case 1:
                        //recorro los campos a validar
                        foreach ($arrData as $data) {
                            if (!empty($DataCheck['Post'][$data])&&!$this->DataValidations->{$field['method']}($DataCheck['Post'][$data])) {
                                $errors[] = ["message" => "El dato $data ingresado ".$field['msgText']];
                            }
                        }
                        break;
                    /*****************/
                    //Validacion Largo Palabras
                    case 2:
                        //recorro los campos a validar
                        foreach ($arrData as $data) {
                            if (!empty($DataCheck['Post'][$data])&&$this->DataValidations->{$field['method']}($DataCheck['Post'][$data], $DataCheck[$field['fncCheck']])!=1) {
                                $errors[] = ["message" => "El dato $data ingresado ".$field['msgText']." (".$DataCheck[$field['fncCheck']]." carácteres)"];
                            }
                        }
                        break;
                    /*****************/
                    //Validacion Palabras censuradas
                    case 3:
                        //recorro los campos a validar
                        foreach ($arrData as $data) {
                            if (!empty($DataCheck['Post'][$data])&&$this->DataText->{$field['method']}($DataCheck['Post'][$data])!=0) {
                                $errors[] = ["message" => "El dato $data ingresado ".$field['msgText']];
                            }
                        }
                        break;
                    /*****************/
                    //Validacion Espacios
                    case 4:
                        //recorro los campos a validar
                        foreach ($arrData as $data) {
                            if (!empty($DataCheck['Post'][$data])&&trim(strpos($DataCheck['Post'][$data], " "))) {
                                $errors[] = ["message" => "El dato $data ingresado ".$field['msgText']];
                            }
                        }
                        break;
                    /*****************/
                    //Validacion Mayusculas
                    case 5:
                        //recorro los campos a validar
                        foreach ($arrData as $data) {
                            if (!empty($DataCheck['Post'][$data])&&strtolower($DataCheck['Post'][$data]) != $DataCheck['Post'][$data]) {
                                $errors[] = ["message" => "El dato $data ingresado ".$field['msgText']];
                            }
                        }
                        break;
                    /*****************/
                    //Validacion Coincidencias
                    case 6:
                        //recorro los campos a validar
                        foreach ($arrData as $data) {
                            //Se separan los datos
                            $arrData2 = $this->CommonData->parseDataSeparator($data); //Separacion por guiones
                            // Recolectar valores no vacíos de los campos a comparar
                            $arrCoin = array_filter(
                                array_intersect_key($DataCheck['Post'], array_flip($arrData2)),
                                function($value) {
                                    return !empty($value);
                                }
                            );
                            // Si hay al menos un valor, verificar si todos son iguales
                            if (count($arrCoin) > 0 && count(array_unique($arrCoin)) !== 1) {
                                $errors[] = ["message" => $field['msgText']];
                            }


                            //Se separan los datos
                            /*$arrData2 = $this->CommonData->parseDataSeparator($data); //Separacion por guiones
                            // Recolectar valores no vacíos de los campos a comparar
                            $arrCoin = [];
                            foreach ($arrData2 as $data2) {
                                if (!empty($DataCheck['Post'][$data2])) {
                                    $arrCoin[] = $DataCheck['Post'][$data2];
                                }
                            }
                            // Si hay al menos un valor, verificar si todos son iguales
                            if (count($arrCoin) > 0 && count(array_unique($arrCoin)) !== 1) {
                                $errors[] = ["message" => $field['msgText']];
                            }*/
                        }
                        break;
                }
            }
		}

        /******************************************/
        //si no hay errores
        return (empty($errors)) ? false : $errors;
    }


}

