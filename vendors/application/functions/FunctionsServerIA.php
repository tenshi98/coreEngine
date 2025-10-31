<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsServerIA {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	public function senDataIA($api_key, $data): array{
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite el envio de datos a modo de consulta a una IA
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se obtiene dato
		* 	$ServerIA->senDataIA('asdasqw', $array);
		*
		*=================================================    Parametros   =================================================
		* @input   string     $api_key  Api Key de la IA
        * @input   array      $data     Arreglo con los datos a consultar
        * @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!isset($api_key) || $api_key==''){   return ['success' => false, 'error' => 'No ha ingresado una apikey'];}
		if(!is_array($data) || empty($data)){   return ['success' => false, 'error' => 'No ha ingresado la info a enviar'];}

		/********************** Si todo esta ok **********************/
		//Se hace el envio
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'Authorization: Bearer ' . $api_key,
		]);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

		/**********************  Retorno datos  **********************/
		//Manejo de errores
		try {
            $response = curl_exec($ch);
            if (curl_errno($ch)) {
				return ['success' => false, 'error' => curl_error($ch), 'code' => curl_errno($ch)];
            }
            curl_close($ch);
            return ['success' => true, 'data' => $response];
        } catch (\Throwable $th) {
            curl_close($ch);
            return ['success' => false, 'error' => $th->getMessage(), 'code' => $th->getCode()];
        }

	}

}
