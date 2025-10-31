<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsServerWeb {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	public function obtenerInfoIp($IP_Cliente, $purpose): string {
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite obtener la datos desde la ip ingresada
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se obtiene dato
		* 	$ServerWeb->obtenerInfoIp('200.120.163.36', "city");
		*   $ServerWeb->obtenerInfoIp('200.120.163.36', "region");
		*   $ServerWeb->obtenerInfoIp('200.120.163.36', "regionCode");
		*   $ServerWeb->obtenerInfoIp('200.120.163.36', "countryCode");
		*   $ServerWeb->obtenerInfoIp('200.120.163.36', "countryName");
		*   $ServerWeb->obtenerInfoIp('200.120.163.36', "continentName");
		*
		*=================================================    Parametros   =================================================
		* @input   string      $IP_Cliente  La IP del cliente
        * @input   string      $purpose     La info que se desea
        * @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!isset($IP_Cliente) || $IP_Cliente==''){ return 'No ha ingresado IP_Cliente';}
		if(!isset($purpose) || $purpose==''){       return 'No ha ingresado purpose';}

		/********************** Si todo esta ok **********************/
		//salida
		$output = '';
		//coneccion a servidor externo para obtener los datos de la ip
		$ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$IP_Cliente));
		//se etrae la solicitud desde la respuesta
		if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
			switch ($purpose) {
				case "city":           $output = @$ipdat->geoplugin_city;           break;
				case "region":         $output = @$ipdat->geoplugin_region;         break;
				case "regionCode":     $output = @$ipdat->geoplugin_regionCode;     break;
				case "countryCode":    $output = @$ipdat->geoplugin_countryCode;    break;
				case "countryName":    $output = @$ipdat->geoplugin_countryName;    break;
				case "continentName":  $output = @$ipdat->geoplugin_continentName;  break;
			}
		}

		/**********************  Retorno datos  **********************/
		return $output;

	}

	/************************************************************************************************************/
	public function getBaseUrl($atRoot=FALSE, $atCore=FALSE, $parse=FALSE): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Muestra la URL Base desde donde se ejecuta
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se obtiene dato
		* 	$ServerWeb->getBaseUrl();                                                     //will produce something like: http://stackoverflow.com/questions/2820723/
		* 	$ServerWeb->getBaseUrl(TRUE);                                                 //will produce something like: http://stackoverflow.com/
		* 	$ServerWeb->getBaseUrl(TRUE, TRUE); || $ServerWeb->getBaseUrl(NULL, TRUE);    //will produce something like: http://stackoverflow.com/questions/
		* 	//  and finally
		* 	$ServerWeb->getBaseUrl(NULL, NULL, TRUE);
		*
		*=================================================    Parametros   =================================================
		* @return  string
		*===================================================================================================================
		*/

		/********************** Si todo esta ok **********************/
		if (isset($_SERVER['HTTP_HOST'])) {
			$http = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
			$hostname = $_SERVER['HTTP_HOST'];
			$dir =  str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

			$core = preg_split('@/@', str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath(dirname(__FILE__))), 1, PREG_SPLIT_NO_EMPTY);
			$core = $core[0];

			$tmplt = $atRoot ? ($atCore ? "%s://%s/%s/" : "%s://%s/") : ($atCore ? "%s://%s/%s/" : "%s://%s%s");
			$end = $atRoot ? ($atCore ? $core : $hostname) : ($atCore ? $core : $dir);
			$getBaseUrl = sprintf( $tmplt, $http, $hostname, $end );
		}else{
			$getBaseUrl = 'https://localhost/';
		}

		if ($parse) {
			$getBaseUrl = parse_url($getBaseUrl);
			if (isset($getBaseUrl['path'])) if ($getBaseUrl['path'] == '/') $getBaseUrl['path'] = '';
		}

		/**********************  Retorno datos  **********************/
		return $getBaseUrl;

	}

	/************************************************************************************************************/
	public function callExternalApi(array $config): array {
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite llamar a una API para su posterior tratamiento
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//Cómo usarla
		* 	$response = callExternalApi([
		*		'method' => 'GET',
		*		'url' => 'https://api.example.com/data',
		*		'headers' => [
		*			'Authorization: Bearer YOUR_TOKEN',
		*			'Accept: application/json'
		*		]
		*	]);
		*
		*	if ($response['success']) {
		*		$data = $response['data'];
		*		// Procesar datos normalizados
		*	} else {
		*		// Manejo de errores
		*		error_log("Error al conectar con API: " . $response['error']);
		*	}
		*
		*=================================================    Parametros   =================================================
		* @input   array   $url    Datos necesarios
		* @return  array
		*===================================================================================================================
		*/

		/**********************    Variables    **********************/
		$method  = strtoupper($config['method'] ?? 'GET');
		$url     = $config['url'] ?? '';
		$headers = $config['headers'] ?? [];
		$body    = $config['body'] ?? null;
		$timeout = $config['timeout'] ?? 10;

		/********************** Si todo esta ok **********************/
		$curl = curl_init();

		$options = [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => $timeout,
			CURLOPT_HTTPHEADER => $headers,
		];

		if ($method === 'POST') {
			$options[CURLOPT_POST] = true;
			$options[CURLOPT_POSTFIELDS] = is_array($body) ? json_encode($body) : $body;
		} elseif ($method === 'PUT' || $method === 'PATCH' || $method === 'DELETE') {
			$options[CURLOPT_CUSTOMREQUEST] = $method;
			$options[CURLOPT_POSTFIELDS] = is_array($body) ? json_encode($body) : $body;
		}

		curl_setopt_array($curl, $options);
		$response = curl_exec($curl);
		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$error    = curl_error($curl);

		curl_close($curl);

		/**********************  Retorno datos  **********************/
		// Capa de anticorrupción: normalizamos la respuesta
		return [
			'status'  => $httpCode,
			'success' => $error === '',
			'error'   => $error ?: null,
			'data'    => json_decode($response, true) ?? $response,
		];
	}

	/************************************************************************************************************/
	public function obtenerDatosXML($url): array {
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite obtener datos de un archivo XML alojado en otro servidor
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//Cómo usarla
		* 	try {
		*		$resultado = obtenerDatosXML("https://ejemplo.com/archivo.xml");
		*		print_r($resultado['datos']);
		*		echo "Hash de integridad: " . $resultado['hash_integridad'];
		*	} catch (Exception $e) {
		*		echo "Error: " . $e->getMessage();
		*	}
		*
		*=================================================    Parametros   =================================================
		* @input   string   $url    URL con el archivo
		* @return  array
		*===================================================================================================================
		*/

		// Validar que la URL sea HTTPS
		if (!filter_var($url, FILTER_VALIDATE_URL) || parse_url($url, PHP_URL_SCHEME) !== 'https') {
			throw new Exception("URL inválida o no segura. Solo se permite HTTPS.");
		}

		// Configurar contexto con timeout y sin seguir redirecciones
		$context = stream_context_create([
			'http' => [
				'method'          => 'GET',
				'timeout'         => 5,
				'follow_location' => 0,
				'header'          => "Accept: application/xml\r\n"
			]
		]);

		// Obtener contenido
		$contenido = @file_get_contents($url, false, $context);
		if ($contenido === false) {
			throw new Exception("No se pudo obtener el archivo XML.");
		}

		// Verificar que el contenido sea XML válido
		libxml_use_internal_errors(true);
		$xml = simplexml_load_string($contenido);
		if ($xml === false) {
			throw new Exception("El contenido recibido no es un XML válido.");
		}

		// Verificar integridad con hash SHA-256
		$hash = hash('sha256', $contenido);
		if (!$hash || strlen($hash) !== 64) {
			throw new Exception("Error al verificar la integridad del archivo XML.");
		}

		// Retornar datos como array y hash de integridad
		return [
			'datos'           => json_decode(json_encode($xml), true),
			'hash_integridad' => $hash
		];
	}

}
?>
