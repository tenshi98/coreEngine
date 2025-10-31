<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsServerSocial {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                 Instancias                                                      */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	//Definiciones
	private $DataNumbers;

	/************************************************************************************************************/
	//Instancias
	public function __construct() {
		$this->DataNumbers = new FunctionsDataNumbers();
	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	public function sendWhatsappTemplate($Token, $InstanceId, $Type, $Body): array{
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite el envio de mensajes whatsapp a traves de chat-api
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se obtiene dato
		* 	$ServerSocial->sendWhatsappTemplate('asdertcvbtrtr', '356644', 1, array());
		*
		*=================================================    Parametros   =================================================
		* @input   string   $Token        Token de la plataforma
		* @input   string   $InstanceId   Instancia a utilizar
		* @input   string   $Type         Plantilla del mensaje a utilizar
		* @input   string   $Body         Arreglo con los datos, como minimo el fono y el cuerpo
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!isset($Token) || $Token==''){           return ['success' => false, 'error' => 'No ha ingresado el Token'];}
		if(!isset($InstanceId) || $InstanceId==''){ return ['success' => false, 'error' => 'No ha ingresado el InstanceId'];}
		if(!isset($Type) || $Type==''){             return ['success' => false, 'error' => 'No ha ingresado el Type'];}
		if(!is_array($Body) || empty($Body)){       return ['success' => false, 'error' => 'No ha ingresado el Body'];}

		/********************** Si todo esta ok **********************/
		//Se arma el mensaje
		switch ($Type) {
			/*********************************************************/
			//Alertas solo un dato
			case 1:
				$data = [
					"token"     => $Token,
					"namespace" => "512f752c_ac4f_45a8_b5b5_2adcfe3ed73a",
					"template"  => "1tek_alerta_1",
					"language" => [
						"policy" => "deterministic",
						"code"   => "es"
					],
					"params" => [
						[
							"type" => "body",
							"parameters" => [
								["type" => "text", "text" => $this->formatWhatsappText($Body['Cuerpo'])],
							]
						]
					],
					"phone" => $this->DataNumbers->normalizarPhone($Body['Phone'])
				];
				break;
			/*********************************************************/
			//Alertas Admin
			case 999:
				$data = [
					"token"     => $Token,
					"namespace" => "512f752c_ac4f_45a8_b5b5_2adcfe3ed73a",
					"template"  => "alerta_iot",
					"language" => [
						"policy" => "deterministic",
						"code"   => "es"
					],
					"params" => [
						[
							"type" => "body",
							"parameters" => [
								["type" => "text", "text" => $this->formatWhatsappText($Body['Titulo'])],
								["type" => "text", "text" => $this->formatWhatsappText($Body['Cuerpo'])],
								["type" => "text", "text" => "asd2"],
								["type" => "text", "text" => "asd3"],
								["type" => "text", "text" => "asd4"],
								["type" => "text", "text" => "asd5"]
							]
						]
					],
					"phone" => $this->DataNumbers->normalizarPhone($Body['Phone'])
				];
				break;

		}
		//Se transforman a un array json
		$data_string = json_encode($data);

		/**************************************/
		//Se hace el envio
		$url = 'https://api.1msg.io/'.$InstanceId.'/sendTemplate';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		/**********************  Retorno datos  **********************/
		//Manejo de errores
		try {
			$whatsappResult = curl_exec($ch);
			curl_close($ch);
			//transformo respuesta a objeto
			$whatsappRes = json_decode($whatsappResult);
			//Si es el resultado esperado
			if (isset($whatsappRes->sent) && $whatsappRes->sent === true) {
                return ['success' => true, 'data' => $whatsappRes];
            } else {
                return ['success' => false, 'error' => $whatsappRes];
            }
		} catch (\Throwable $th) {
			curl_close($ch);
			return ['success' => false, 'error' => $th->getMessage(), 'code' => $th->getCode()];
		}
	}

	/************************************************************************************************************/
	public function formatWhatsappText($Texto): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Se reformatea el texto del fono para compatibilizarlo con whatsapp
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se obtiene dato
		* 	$ServerSocial->formatWhatsappText('texto<br><strong>destacado</strong>');
		*
		*=================================================    Parametros   =================================================
		* @input   string   $Texto        Texto a formatear
		* @return  string
		*===================================================================================================================
		*/

		/********************** Si todo esta ok **********************/
		//Normalizo el mensaje
		$Texto = str_replace(
			['<br/>', '<br>', '</br>', '<strong>', '</strong>'],
			[' // ',  ' // ', ' // ',   '*',         '*'],
			$Texto
		);

		/**********************  Retorno datos  **********************/
		return $Texto;

	}


}
