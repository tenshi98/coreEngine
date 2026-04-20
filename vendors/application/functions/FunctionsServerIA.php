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
	/**
     * Envía una petición de consulta a los modelos de lenguaje de OpenAI (GPT).
     *
     * Configura y ejecuta una llamada a la API de Chat Completions utilizando cURL.
     * Requiere una estructura de datos compatible con el endpoint de OpenAI (model, messages, etc.).
     *
     * @param string $api_key Clave de API de OpenAI para la autenticación Bearer.
     * @param array $data Arreglo asociativo con la configuración de la consulta (payload).
     *
     * @return array Resultado con éxito, la respuesta cruda de la IA o detalles del error.
	 *
	 * @example
	 * ```php
	 * $ServerIA->senDataIA('asdasqw', $array);
	 * ```
	 *
     */
    public function senDataIA($api_key, $data): array {

        /********************** Validaciones   **********************/
        // Verifica que la API Key no sea nula o una cadena vacía
        if(!isset($api_key) || $api_key==''){   return ['success' => false, 'error' => 'No ha ingresado una apikey'];}
        // Valida que el parámetro de datos sea un arreglo y contenga información
        if(!is_array($data) || empty($data)){   return ['success' => false, 'error' => 'No ha ingresado la info a enviar'];}

        /********************** Si todo esta ok **********************/
        // Inicialización del recurso cURL para la comunicación con el endpoint de OpenAI
        $ch = curl_init();

        // Configuración de parámetros de la petición POST
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        // Configuración de cabeceras de autenticación y tipo de contenido
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key,
        ]);

        // Conversión del arreglo de datos a formato JSON para el cuerpo de la petición
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        /********************** Retorno datos  **********************/
        // Gestión de la ejecución y captura de posibles errores de red o protocolos
        try {
            $response = curl_exec($ch);

            // Verificación de errores específicos de la librería cURL (ej: timeout, DNS)
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
                $error_code = curl_errno($ch);
                curl_close($ch);
                return ['success' => false, 'error' => $error_msg, 'code' => $error_code];
            }

            // Cierre del recurso tras una respuesta exitosa del servidor
            curl_close($ch);

            // Retorna la respuesta de la IA (generalmente un JSON que debe ser decodificado por quien llama)
            return ['success' => true, 'data' => $response];

        } catch (\Throwable $th) {
            // Captura de excepciones críticas durante el proceso de envío
            if (is_resource($ch)) { curl_close($ch); }
            return ['success' => false, 'error' => $th->getMessage(), 'code' => $th->getCode()];
        }

    }


}
