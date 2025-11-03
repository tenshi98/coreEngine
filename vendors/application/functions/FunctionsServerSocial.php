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
	public function sendWhatsappTemplate($Config, $Body): array{
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
		* @input   array   $Config       Configuracion de whatsapp
		* @input   array   $Body         Arreglo con los datos, como minimo el fono y el cuerpo
		* @return  array
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!is_array($Config) || empty($Config)){   return ['success' => false, 'error' => 'No ha ingresado el Body'];}
		if(!is_array($Body) || empty($Body)){       return ['success' => false, 'error' => 'No ha ingresado el Body'];}

		/********************** Si todo esta ok **********************/
		//Se arma el mensaje
		switch ($Config['Type']) {
			/*********************************************************/
			//Template con solo el titulo y el mensaje
			case 1:
				$data = [
					"token"     => $Config['Token'],
					"namespace" => $Config['namespace'],
					"template"  => $Config['template'],
					"language" => [
						"policy" => "deterministic",
						"code"   => "es"
					],
					"params" => [
						[
							"type" => "body",
							"parameters" => [
								["type" => "text", "text" => $this->formatWhatsappText($Body['Titulo'])],
								["type" => "text", "text" => $this->formatWhatsappText($Body['Mensaje'])],
							]
						]
					],
					"phone" => $this->DataNumbers->normalizarPhone($Body['Phone'])
				];
				break;
			/*********************************************************/
			//Template con el nombre de la persona, el mensaje y el enlace
			case 2:
				$data = [
					"token"     => $Config['Token'],
					"namespace" => $Config['namespace'],
					"template"  => $Config['template'],
					"language" => [
						"policy" => "deterministic",
						"code"   => "es"
					],
					"params" => [
						[
							"type" => "body",
							"parameters" => [
								["type" => "text", "text" => $this->formatWhatsappText($Body['Entidad'])],
								["type" => "text", "text" => $this->formatWhatsappText($Body['Mensaje'])],
								["type" => "text", "text" => $this->formatWhatsappText($Body['Link'])],
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
					"token"     => $Config['Token'],
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
			/*********************************************************/
			//Alertas Admin
			case 1000:
				//otra
				break;

		}
		//Se transforman a un array json
		$data_string = json_encode($data);

		/**************************************/
		//Se hace el envio
		$url = 'https://api.1msg.io/'.$Config['InstanceId'].'/sendTemplate';
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
