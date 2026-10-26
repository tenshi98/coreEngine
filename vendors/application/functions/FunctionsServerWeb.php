<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsServerWeb {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Publicos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
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
	public function getBaseUrl($atRoot = false, $atCore = false, $parse = false): string {

        /********************** Si todo esta ok **********************/
        // Verifica si el host está definido en las variables globales del servidor.
        // HTTP_HOST es una cabecera controlable por el cliente: se valida su formato
        // (hostname, IPv4 o IPv6 entre corchetes, con puerto opcional) para impedir
        // Host Header Injection (CRLF, comillas, barras u otros caracteres prohibidos).
        $hostValido = isset($_SERVER['HTTP_HOST']) && preg_match('/^(?:\[[0-9a-fA-F:.]+\]|[A-Za-z0-9](?:[A-Za-z0-9\-._]*[A-Za-z0-9])*)(?::\d{1,5})?$/', (string)$_SERVER['HTTP_HOST']);
        if ($hostValido) {
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

	/******************************************************************************/
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

        // Defensa SSRF: valida el destino antes de ejecutar la petición cURL
		$DataValidations = new FunctionsDataValidations();
        $validacionUrl   = $DataValidations->validarUrlExterna((string)$url);
        if ($validacionUrl['success'] === false) {
            return ['status' => 0, 'success' => false, 'error' => $validacionUrl['error'], 'data' => null];
        }

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

	/******************************************************************************/
	/**
	 * Obtiene datos JSON desde una URL externa.
	 *
	 * Espejo de obtenerDatosXML: mismo manejo de URL, validaciones y estructura de retorno.
	 * Decodifica la respuesta como array asociativo.
	 *
	 * @param string $Url URL a consultar.
	 * @param array $Config Opciones de contexto stream (timeout, headers, etc.).
	 *
	 * @return array ['success'=>bool,'data'=>array|'error'=>string] con json_decode(assoc=true).
	 *
	 * @example
	 * ```php
	 * $ServerWeb->obtenerDatosJSON('https://api.ejemplo.com/datos'); // Devuelve ['success'=>true,'data'=>[...]]
	 * ```
	 *
	 */
	public function obtenerDatosJSON(string $Url, array $Config = []): array {

		/********************** Validaciones   **********************/
		// Verifica que la URL no sea una cadena vacía antes de instanciar servicios
		if ($Url === null || trim((string)$Url) === '') {
			return ['success' => false, 'error' => 'No ha ingresado una URL'];
		}

		// Verifica que la URL sea válida y que el destino no apunte a redes internas (defensa SSRF)
		$DataValidations = new FunctionsDataValidations();
		$validacionUrl   = $DataValidations->validarUrlExterna((string)$Url);
		if ($validacionUrl['success'] === false) {
			return ['success' => false, 'error' => $validacionUrl['error']];
		}

		/********************** Si todo esta ok **********************/
		// Preparación del contexto de stream con configuración opcional
		$Context = stream_context_create($Config);

		// Consumo del servicio vía HTTP GET
		$ResponseData = file_get_contents($Url, false, $Context);

		/********************** Retorno datos  **********************/
		if ($ResponseData === false) {
			return ['success' => false, 'error' => 'Error al obtener datos desde la URL'];
		}

		// Decodificación JSON (array asociativo)
		$Decoded = json_decode($ResponseData, true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			return ['success' => false, 'error' => 'La respuesta no es un JSON válido'];
		}

		return ['success' => true, 'data' => $Decoded];

	}

	/******************************************************************************/
	/**
	 * Descarga un archivo desde una URL a un destino local.
	 *
	 * Crea el directorio destino si no existe (usa ensureDirectoryExists).
	 * Informa los bytes escritos.
	 *
	 * @param string $Url URL del archivo a descargar.
	 * @param string $Destino Ruta local donde guardar el archivo.
	 *
	 * @return array ['success'=>bool,'data'=>bytes|'error'=>string].
	 *
	 * @example
	 * ```php
	 * $ServerWeb->descargarArchivo('https://ejemplo.com/archivo.pdf', '/tmp/archivo.pdf');
	 * // Devuelve ['success'=>true,'data'=>102456]
	 * ```
	 *
	 */
	public function descargarArchivo(string $Url, string $Destino): array {

		/********************** Validaciones   **********************/
		if ($Url === null || trim((string)$Url) === '') {
			return ['success' => false, 'error' => 'No ha ingresado una URL'];
		}
		// Verifica que la URL sea válida y que el destino no apunte a redes internas (defensa SSRF)
		$DataValidations = new FunctionsDataValidations();
		$validacionUrl   = $DataValidations->validarUrlExterna((string)$Url);
		if ($validacionUrl['success'] === false) {
			return ['success' => false, 'error' => $validacionUrl['error']];
		}
		if ($Destino === null || trim((string)$Destino) === '') {
			return ['success' => false, 'error' => 'No ha ingresado un destino'];
		}

		/********************** Si todo esta ok **********************/
		// Asegura que el directorio destino exista
		$Dir = dirname($Destino);
		if (!is_dir($Dir)) {
			if (!mkdir($Dir, 0755, true)) {
				return ['success' => false, 'error' => 'No se pudo crear el directorio destino'];
			}
		}

		// Contexto con timeout razonable
		$Context = stream_context_create(['http' => ['timeout' => 30]]);

		// Descarga y escritura
		$Bytes = file_put_contents($Destino, file_get_contents($Url, false, $Context));
		if ($Bytes === false) {
			return ['success' => false, 'error' => 'Error al descargar o escribir el archivo'];
		}

		/********************** Retorno datos **********************/
		return ['success' => true, 'data' => $Bytes];

	}

	/******************************************************************************/
	/**
	 * Verifica la disponibilidad de una URL (HEAD request con fallback GET).
	 *
	 * Devuelve código de estado HTTP y tiempo de respuesta en milisegundos.
	 *
	 * @param string $Url URL a verificar.
	 * @param int $TiempoLimite Timeout en segundos (por defecto 5).
	 *
	 * @return array ['success'=>bool,'data'=>['status'=>int,'milisegundos'=>int]].
	 *
	 * @example
	 * ```php
	 * $ServerWeb->verificarDisponibilidadURL('https://google.com'); // Devuelve ['success'=>true,'data'=>['status'=>200,'milisegundos'=>123]]
	 * ```
	 *
	 */
	public function verificarDisponibilidadURL(string $Url, int $TiempoLimite = 5): array {

		/********************** Validaciones **********************/
		$Url = trim($Url);

		if ($Url === '') {
			return ['success' => false, 'error' => 'No ha ingresado una URL'];
		}

		if (!filter_var($Url, FILTER_VALIDATE_URL)) {
			return ['success' => false, 'error' => 'La URL ingresada no es válida'];
		}

		if (!in_array(strtolower((string) parse_url($Url, PHP_URL_SCHEME)), ['http', 'https'], true)) {
			return ['success' => false, 'error' => 'La URL debe utilizar HTTP o HTTPS'];
		}

		// Defensa SSRF: valida que el destino no apunte a redes internas o reservadas
		$DataValidations = new FunctionsDataValidations();
		$validacionUrl   = $DataValidations->validarUrlExterna((string)$Url);
		if ($validacionUrl['success'] === false) {
			return ['success' => false, 'error' => $validacionUrl['error']];
		}

		/********************** Verificar disponibilidad **********************/
		$Inicio = microtime(true);
		$HttpCode = 0;

		$ObtenerCodigoHTTP = static function (string $Url, string $Metodo, int $TiempoLimite): int {
			$Context = stream_context_create([
				'http' => [
					'method'        => $Metodo,
					'timeout'       => $TiempoLimite,
					'ignore_errors' => true,
					'follow_location' => 1,
					'max_redirects' => 5,
				],
				'ssl' => [
					'verify_peer'      => true,
					'verify_peer_name' => true,
				],
			]);

			$Headers = @get_headers($Url, true, $Context);

			if (!$Headers || !isset($Headers[0])) {
				return 0;
			}

			if (preg_match('/\b(\d{3})\b/', $Headers[0], $Matches)) {
				return (int) $Matches[1];
			}

			return 0;
		};

		// Primero intenta con HEAD.
		$HttpCode = $ObtenerCodigoHTTP($Url, 'HEAD', $TiempoLimite);

		// Si HEAD no funciona, intenta con GET.
		if ($HttpCode === 0 || $HttpCode === 403 || $HttpCode === 405) {
			$HttpCode = $ObtenerCodigoHTTP($Url, 'GET', $TiempoLimite);
		}

		$Milisegundos = (int) round((microtime(true) - $Inicio) * 1000);

		/********************** Retorno datos **********************/
		return [
			'success' => $HttpCode >= 200 && $HttpCode < 400,
			'data' => [
				'status'       => $HttpCode,
				'milisegundos' => $Milisegundos,
			],
		];

	}

	/******************************************************************************/
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
        // Valida que exista una URL
        if ($url === null || trim((string)$url) === '') {
            return ['success' => false, 'error' => 'Sin datos ingresados'];
        }
        // Valida que la cadena sea una URL válida y utilice el esquema de seguridad HTTPS
        if (!filter_var($url, FILTER_VALIDATE_URL) || parse_url($url, PHP_URL_SCHEME) !== 'https') {
            return ['success' => false, 'error' => "URL inválida o no segura. Solo se permite HTTPS."];
        }

        // Defensa SSRF: valida que el host destino no apunte a redes internas o reservadas
		$DataValidations = new FunctionsDataValidations();
        $validacionUrl   = $DataValidations->validarUrlExterna((string)$url);
        if ($validacionUrl['success'] === false) {
            return ['success' => false, 'error' => $validacionUrl['error']];
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
        // Intenta cargar la cadena de texto como un objeto XML (LIBXML_NONET bloquea el
        // acceso a red durante el parseo, cerrando el vector XXE/SSRF en builds vulnerables)
        $xml = simplexml_load_string($contenido, 'SimpleXMLElement', LIBXML_NONET);
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

	/******************************************************************************/
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
	public function obtenerInfoIp($IP_Cliente, $purpose): array {

        /********************** Validaciones   **********************/
        // Verifica que la IP del cliente haya sido proporcionada y no sea una cadena vacía
        if ($IP_Cliente === null || trim((string)$IP_Cliente) === '') {
            return ['success' => false, 'error' => 'No ha ingresado IP_Cliente'];
        }

        // Valida estrictamente que sea una dirección IPv4 o IPv6 válida.
        if (filter_var($IP_Cliente, FILTER_VALIDATE_IP) === false) {
            return ['success' => false, 'error' => 'La dirección IP_Cliente ingresada no es válida'];
        }

        // Verifica que el propósito de la consulta haya sido proporcionado y no sea una cadena vacía
        if ($purpose === null || trim((string)$purpose) === '') {
            return ['success' => false, 'error' => 'No ha ingresado purpose'];
        }

        /********************** Si todo esta ok **********************/
        // Inicialización de la variable de salida
        $output = '';

        // Contexto de petición: timeout explícito y sin seguimiento de redirecciones
        $contexto = stream_context_create(['http' => ['timeout' => 10, 'follow_location' => 0]]);

        // Consulta al servicio geoplugin con HTTPS e IP codificada (urlencode); si el
        // HTTPS falla se reintenta una sola vez por HTTP como respaldo del proveedor
        $respuesta = @file_get_contents('https://www.geoplugin.net/json.gp?ip=' . urlencode($IP_Cliente), false, $contexto);
        if ($respuesta === false) {
            $errorRed = error_get_last();
            error_log('[FunctionsServerWeb::obtenerInfoIp] Falla consultando por HTTPS (' . ($errorRed['message'] ?? 'sin detalle') . '); se reintenta por HTTP');
            $respuesta = @file_get_contents('http://www.geoplugin.net/json.gp?ip=' . urlencode($IP_Cliente), false, $contexto);
        }

        // Si no hubo respuesta, se registra el error real y se retorna un error controlado
        if ($respuesta === false) {
            $errorRed = error_get_last();
            error_log('[FunctionsServerWeb::obtenerInfoIp] ' . ($errorRed['message'] ?? 'Sin respuesta del servicio geoplugin'));
            return ['success' => false, 'error' => 'No se pudo consultar la información de la IP'];
        }

        // Decodificación del JSON de respuesta con verificación explícita del resultado
        $ipdat = json_decode($respuesta);
        if ($ipdat === null) {
            return ['success' => false, 'error' => 'La respuesta del servicio geoplugin no es un JSON válido'];
        }

        // Valida que la respuesta contenga un código de país válido de 2 caracteres antes de procesar
        if (isset($ipdat->geoplugin_countryCode) && strlen(trim((string)$ipdat->geoplugin_countryCode)) == 2) {
            // Estructura de selección para asignar el dato solicitado a la variable de salida
            switch ($purpose) {
                case "city":           $output = $ipdat->geoplugin_city          ?? ''; break;
                case "region":         $output = $ipdat->geoplugin_region        ?? ''; break;
                case "regionCode":     $output = $ipdat->geoplugin_regionCode    ?? ''; break;
                case "countryCode":    $output = $ipdat->geoplugin_countryCode   ?? ''; break;
                case "countryName":    $output = $ipdat->geoplugin_countryName   ?? ''; break;
                case "continentName":  $output = $ipdat->geoplugin_continentName ?? ''; break;
            }
        }

        /********************** Retorno datos  **********************/
        // Devuelve el dato geográfico obtenido o una cadena vacía
        return ['success' => true, 'data' => $output];

    }

	/******************************************************************************/
	/**
     * Recupera y procesa los indicadores económicos desde el canal RSS del SII.
     *
     * Consume un recurso XML externo (generalmente de zeus.sii.cl) y estructura
     * la información de los indicadores (UF, UTM, Dólar, etc.) en un arreglo asociativo limpio.
     *
     * @param string $URL Dirección del recurso RSS/XML del SII.
     *
     * @return array Resultado con éxito, datos procesados (título, link, descripción) o error.
	 *
	 * @example
	 * ```php
	 * $ServerWeb->getDataSIIindicadores('https://zeus.sii.cl/admin/rss/sii_ind_rss.xml');
	 * ```
	 *
     */
    public function getDataSIIindicadores(string $URL): array {

        /********************** Validaciones   **********************/
        // Verifica que la URL no sea una cadena vacía antes de instanciar servicios
        if ($URL === null || trim((string)$URL) === '') {
            return ['success' => false, 'error' => 'No ha ingresado una URL'];
        }

        // Verifica que la URL sea válida y que el destino no apunte a redes internas (defensa SSRF en profundidad: falla temprano antes de delegar a obtenerDatosXML())
        $DataValidations = new FunctionsDataValidations();
        $validacionUrl   = $DataValidations->validarUrlExterna((string)$URL);
        if ($validacionUrl['success'] === false) {
            return ['success' => false, 'error' => $validacionUrl['error']];
        }

        /********************** Si todo esta ok **********************/
        try {
            // Inicialización de contenedor para los indicadores procesados
            $arrData = array();

            // Utiliza el método propio de obtención de datos XML de esta clase
            $resultado = $this->obtenerDatosXML($URL);

            // Iteración sobre la estructura multinivel del XML (Channel > Items)
            foreach($resultado as $data_lvl1){
                foreach($data_lvl1 as $data_lvl2){
                    // Validación de existencia de contenido en el nodo antes de asignar
                    if(isset($data_lvl2['title']) && $data_lvl2['title']!=''){
                        $arrData[] = [
                            'title'       => $data_lvl2['title'],
                            'link'        => $data_lvl2['link'],
                            'description' => $data_lvl2['description'],
                        ];
                    }
                }
            }

            /********************** Retorno datos  **********************/
            // Devuelve la colección de indicadores normalizada
            return ['success' => true, 'data' => $arrData];

        } catch (Exception $e) {
            // Captura de errores durante el parseo o la conexión
            return ['success' => false, 'error' => $e->getMessage()];
        }

	}

    /******************************************************************************/
	/**
	 * Obtiene un indicador específico desde el RSS del SII.
	 *
	 * Reutiliza obtenerDatosXML para descargar el RSS y filtra el indicador
	 * cuyo título contiene la cadena buscada (case-insensitive).
	 *
	 * @param string $URL URL del RSS del SII.
	 * @param string $Indicador Nombre/texto a buscar en el título del indicador.
	 *
	 * @return array|string ['success'=>true,'data'=>item] o mensaje de error.
	 *
	 * @example
	 * ```php
	 * $ServerWeb->getDataSIIindicador('https://sii.cl/rss', 'UF'); // Devuelve ['success'=>true,'data'=>[...]]
	 * ```
	 *
	 */
	public function getDataSIIindicador(string $URL, string $Indicador): array|string {

		/********************** Validaciones   **********************/
		if ($URL === null || trim((string)$URL) === '') {
			return 'No ha ingresado una URL';
		}
		if ($Indicador === null || trim((string)$Indicador) === '') {
			return 'No ha ingresado un indicador a buscar';
		}

		/********************** Si todo esta ok **********************/
		// Reutiliza la obtención del RSS completo
		$Rss = $this->getDataSIIindicadores($URL);
		if (!$Rss['success'] || empty($Rss['data'])) {
			return $Rss['success'] ? 'No se encontraron indicadores en el RSS' : $Rss;
		}

		// Busca el indicador cuyo título contenga la cadena (case-insensitive)
		$Buscar = strtolower($Indicador);
		foreach ($Rss['data'] as $Item) {
			if (isset($Item['title']) && stripos($Item['title'], $Buscar) !== false) {
				return ['success' => true, 'data' => $Item];
			}
		}

		/********************** Retorno datos  **********************/
		return ['success' => false, 'data' => 'Indicador no encontrado'];

	}


}
