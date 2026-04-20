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
	/**
     * Obtiene información geográfica detallada a partir de una dirección IP.
     *
     * Utiliza el servicio externo geoplugin.net para recuperar datos de ubicación.
     * Permite extraer atributos específicos como ciudad, región, códigos de país y continente.
     *
     * @param string $IP_Cliente La dirección IP que se desea consultar.
     * @param string $purpose El atributo específico a recuperar (city, region, regionCode, countryCode, countryName, continentName).
     *
     * @return string El valor geográfico solicitado o un mensaje de error si los parámetros son inválidos.
	 *
	 * @example
	 * ```php
	 * $ServerWeb->obtenerInfoIp('200.120.163.36', "city");
	 * $ServerWeb->obtenerInfoIp('200.120.163.36', "region");
	 * $ServerWeb->obtenerInfoIp('200.120.163.36', "regionCode");
	 * $ServerWeb->obtenerInfoIp('200.120.163.36', "countryCode");
	 * $ServerWeb->obtenerInfoIp('200.120.163.36', "countryName");
	 * $ServerWeb->obtenerInfoIp('200.120.163.36', "continentName");
	 * ```
	 *
     */
	public function obtenerInfoIp($IP_Cliente, $purpose): string {

        /********************** Validaciones   **********************/
        // Verifica que la IP del cliente haya sido proporcionada y no sea una cadena vacía
        if(!isset($IP_Cliente) || $IP_Cliente==''){ return 'No ha ingresado IP_Cliente';}
        // Verifica que el propósito de la consulta haya sido proporcionado y no sea una cadena vacía
        if(!isset($purpose) || $purpose==''){       return 'No ha ingresado purpose';}

        /********************** Si todo esta ok **********************/
        // Inicialización de la variable de salida
        $output = '';
        // Realiza la conexión al servidor externo geoplugin para obtener los datos de la IP en formato JSON
        $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$IP_Cliente));
        // Valida que la respuesta contenga un código de país válido de 2 caracteres antes de procesar
        if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
            // Estructura de selección para asignar el dato solicitado a la variable de salida
            switch ($purpose) {
                case "city":           $output = @$ipdat->geoplugin_city;           break;
                case "region":         $output = @$ipdat->geoplugin_region;         break;
                case "regionCode":     $output = @$ipdat->geoplugin_regionCode;     break;
                case "countryCode":    $output = @$ipdat->geoplugin_countryCode;    break;
                case "countryName":    $output = @$ipdat->geoplugin_countryName;    break;
                case "continentName":  $output = @$ipdat->geoplugin_continentName;  break;
            }
        }

        /********************** Retorno datos  **********************/
        // Devuelve el dato geográfico obtenido o una cadena vacía
        return $output;

    }

	/************************************************************************************************************/
	/**
     * Determina y construye la URL base del entorno de ejecución actual.
     *
     * Calcula dinámicamente el protocolo, host y rutas de directorio. Permite
     * retornar la raíz del dominio, incluir el núcleo de la ruta o parsear el resultado.
	 *
     * @param bool $atRoot Define si se debe retornar solo la raíz del host.
     * @param bool $atCore Define si se debe incluir el directorio base del script.
     * @param bool $parse Define si el retorno debe ser un array procesado por parse_url.
     *
     * @return string|array La URL base construida o un array con sus componentes.
	 *
	 * @example
	 * ```php
	 * $ServerWeb->getBaseUrl();                 //retornara: http://stackoverflow.com/questions/2820723/
	 * $ServerWeb->getBaseUrl(TRUE);             //retornara: http://stackoverflow.com/
	 * $ServerWeb->getBaseUrl(TRUE, TRUE);       //retornara: http://stackoverflow.com/questions/
	 * $ServerWeb->getBaseUrl(NULL, TRUE);       //retornara: http://stackoverflow.com/questions/
	 * $ServerWeb->getBaseUrl(NULL, NULL, TRUE);
	 * ```
	 *
     */
	public function getBaseUrl($atRoot=false, $atCore=false, $parse=false): string {

        /********************** Si todo esta ok **********************/
        // Verifica si el host está definido en las variables globales del servidor
        if (isset($_SERVER['HTTP_HOST'])) {
            // Identifica si el protocolo actual es seguro (https) o estándar (http)
            $http       = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
            $hostname   = $_SERVER['HTTP_HOST'];
            // Obtiene la ruta del directorio eliminando el nombre del script actual
            $dir        =  str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

            // Divide la ruta para obtener el nombre del directorio raíz del proyecto
            $core       = preg_split('@/@', str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath(dirname(__FILE__))), 1, PREG_SPLIT_NO_EMPTY);
            $core       = $core[0];

            // Define el formato de la URL base según la combinación de los parámetros atRoot y atCore
            $tmplt      = $atRoot ? ($atCore ? "%s://%s/%s/" : "%s://%s/") : ($atCore ? "%s://%s/%s/" : "%s://%s%s");
            // Determina el componente final de la ruta basado en la jerarquía solicitada
            $end        = $atRoot ? ($atCore ? $core : $hostname) : ($atCore ? $core : $dir);
            // Formatea los componentes para generar la URL final
            $getBaseUrl = sprintf( $tmplt, $http, $hostname, $end );
        }else{
            // Valor de contingencia para entornos locales o ejecuciones sin cabeceras de host
            $getBaseUrl = 'https://localhost/';
        }

        // Si se solicita el parseo, descompone la URL en sus componentes asociativos
        if ($parse) {
            $getBaseUrl = parse_url($getBaseUrl);
            // Remueve la barra diagonal si es el único contenido en el componente path
            if (isset($getBaseUrl['path']) && $getBaseUrl['path'] === '/') {
                $getBaseUrl['path'] = '';
            }
        }

        /********************** Retorno datos  **********************/
        // Devuelve la URL construida o el array de componentes
        return $getBaseUrl;

    }

	/************************************************************************************************************/
	/**
     * Ejecuta peticiones HTTP a APIs externas utilizando la librería cURL.
     *
     * Soporta métodos GET, POST, PUT, PATCH y DELETE. Normaliza la respuesta
     * en una estructura consistente de éxito/error.
	 *
     * @param array $config Configuración de la petición:
     *    - method (string): Método HTTP.
     *    - url (string): Endpoint de la API.
     *    - headers (array): Cabeceras HTTP.
     *    - body (mixed): Datos a enviar.
     *    - timeout (int): Tiempo de espera en segundos.
     *
     * @return array Resultado normalizado con status, success, error y data.
	 *
	 * @example
	 * ```php
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
	 *		Procesar datos normalizados
	 *	} else {
	 *		Manejo de errores
	 *		error_log("Error al conectar con API: " . $response['error']);
	 *	}
	 * ```
	 *
     */
	public function callExternalApi(array $config): array {

        /********************** Variables    **********************/
        // Inicializa y normaliza los parámetros de configuración de la petición
        $method  = strtoupper($config['method'] ?? 'GET');
        $url     = $config['url'] ?? '';
        $headers = $config['headers'] ?? [];
        $body    = $config['body'] ?? null;
        $timeout = $config['timeout'] ?? 10;

        /********************** Si todo esta ok **********************/
        // Inicia la sesión cURL
        $curl = curl_init();

        // Configura las opciones fundamentales de la transferencia
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_HTTPHEADER => $headers,
        ];

        // Configura parámetros específicos para métodos de envío de datos
        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = is_array($body) ? json_encode($body) : $body;
        } elseif ($method === 'PUT' || $method === 'PATCH' || $method === 'DELETE') {
            $options[CURLOPT_CUSTOMREQUEST] = $method;
            $options[CURLOPT_POSTFIELDS] = is_array($body) ? json_encode($body) : $body;
        }

        // Establece todas las opciones de cURL de una sola vez
        curl_setopt_array($curl, $options);
        // Ejecuta la petición y captura la respuesta, código de estado y posibles errores
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error    = curl_error($curl);

        // Cierra la sesión cURL para liberar recursos del sistema
        curl_close($curl);

        /********************** Retorno datos  **********************/
        // Devuelve un array normalizado (Capa de anticorrupción) con los resultados del servicio
        return [
            'status'  => $httpCode,
            'success' => $error === '',
            'error'   => $error ?: null,
            // Intenta decodificar la respuesta JSON; de lo contrario, devuelve el cuerpo original
            'data'    => json_decode($response, true) ?? $response,
        ];
    }

	/************************************************************************************************************/
	/**
     * Recupera y procesa datos desde una fuente XML externa asegurando la integridad.
     *
     * Valida la seguridad de la conexión (HTTPS), descarga el contenido mediante contextos de flujo,
     * verifica la integridad mediante hash SHA-256 y convierte el XML a un array asociativo.
     *
     * @param string $url Dirección URL del recurso XML (debe ser HTTPS).
     *
     * @return array Resultado del procesamiento con éxito, datos y hash de integridad.
	 *
	 * @example
	 * ```php
	 * 	try {
	 *		$resultado = obtenerDatosXML("https://ejemplo.com/archivo.xml");
	 *		print_r($resultado['datos']);
	 *		echo "Hash de integridad: " . $resultado['hash_integridad'];
	 *	} catch (Exception $e) {
	 *		echo "Error: " . $e->getMessage();
	 *  }
	 * ```
	 *
     */
	public function obtenerDatosXML($url): array {

        /**********************  Validaciones   **********************/
        // Valida que la cadena sea una URL válida y utilice el esquema de seguridad HTTPS
        if (!filter_var($url, FILTER_VALIDATE_URL) || parse_url($url, PHP_URL_SCHEME) !== 'https') {
            return ['success' => false, 'error' => "URL inválida o no segura. Solo se permite HTTPS."];
        }

        /********************** Si todo esta ok **********************/
        // Define un contexto de flujo para la petición GET con restricciones de tiempo y redirección
        $context = stream_context_create([
            'http' => [
                'method'          => 'GET',
                'timeout'         => 5,
                'follow_location' => 0,
                'header'          => "Accept: application/xml\r\n"
            ]
        ]);

        // Intenta leer el contenido del archivo remoto usando el contexto configurado
        $contenido = @file_get_contents($url, false, $context);
        if ($contenido === false) {
            return ['success' => false, 'error' => "No se pudo obtener el archivo XML."];
        }

        // Desactiva el reporte de errores externos de libxml para manejarlos localmente
        libxml_use_internal_errors(true);
        // Intenta cargar la cadena de texto como un objeto XML
        $xml = simplexml_load_string($contenido);
        if ($xml === false) {
            return ['success' => false, 'error' => "El contenido recibido no es un XML válido."];
        }

        // Genera un hash SHA-256 del contenido crudo para validación de integridad
        $hash = hash('sha256', $contenido);
        if (!$hash || strlen($hash) !== 64) {
            return ['success' => false, 'error' => "Error al verificar la integridad del archivo XML."];
        }

		/********************** Retorno datos  **********************/
        // Retorna los datos convertidos a array mediante una serialización intermedia y el hash generado
        return ['success' => true, 'data' => json_decode(json_encode($xml), true), 'hash_integridad' => $hash];

    }

}

