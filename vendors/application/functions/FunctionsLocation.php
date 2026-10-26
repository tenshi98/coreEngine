<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsLocation {

    /*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                 Instancias                                                      */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	//Definiciones
	private $DataValidations;

	/******************************************************************************/
    /**
	 * Inicializa la instancia con el componente de validaciones requerido.
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * $Location = new FunctionsLocation();
	 * ```
	 */
	public function __construct() {
		$this->DataValidations = new FunctionsDataValidations();
	}

    /*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Publicos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
    /**
     * Calcula la distancia entre dos puntos geográficos utilizando la fórmula de Haversine.
     * * Esta función determina la distancia en línea recta sobre la superficie de una esfera
     * (la Tierra) entre dos pares de coordenadas (latitud/longitud). El resultado se
     * entrega inicialmente en kilómetros según el radio medio terrestre definido.
     *
     * @param float|string $latitude1  Latitud del punto de origen.
     * @param float|string $longitude1 Longitud del punto de origen.
     * @param float|string $latitude2  Latitud del punto de destino.
     * @param float|string $longitude2 Longitud del punto de destino.
     *
     * @return float|string Distancia calculada en kilómetros.
	 *
	 * @example
	 * ```php
	 * $Location->calcularDistancia(-40.807289, -72.634907, -42.176560, -73.425923); // Devuelve aprox. 165.9 (kilómetros)
	 * ```
	 *
     */
    public function calcularDistancia($latitude1, $longitude1, $latitude2, $longitude2): string | float {

        /********************** Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateValue($latitude1, 'latitude1');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }

		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_2 = $this->_validateValue($latitude2, 'latitude2');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_2 !== true) { return $dataVal_2; }

		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_3 = $this->_validateValue($longitude1, 'longitude1');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_3 !== true) { return $dataVal_3; }

		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_4 = $this->_validateValue($longitude2, 'longitude2');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_4 !== true) { return $dataVal_4; }

        /********************** Si todo esta ok **********************/
        // Asegurar tipo de dato flotante para precisión matemática
        $latitude1  = floatval($latitude1);
        $longitude1 = floatval($longitude1);
        $latitude2  = floatval($latitude2);
        $longitude2 = floatval($longitude2);

        // Radio medio de la Tierra en kilómetros
        $earth_radius = 6371;

        // Conversión de diferencias de coordenadas de grados a radianes
        $dLat = deg2rad($latitude2 - $latitude1);
        $dLon = deg2rad($longitude2 - $longitude1);

        // Aplicación de la fórmula de Haversine
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * asin(sqrt($a));
        $d = $earth_radius * $c;

        /********************** Retorno datos  **********************/
        return (float)$d;

    }

	/******************************************************************************/
    /**
     * Obtiene coordenadas y dirección formateada desde la API de Geocoding de Google Maps.
     * * Convierte una dirección de texto plano en datos geográficos (Latitud y Longitud)
     * utilizando los servicios de Google. Requiere una API Key válida y activa.
     *
     * @param string $address Dirección completa a consultar (ej: "Av. Siempreviva 742, Springfield").
     * @param string $ApiKey  Llave de API autorizada por Google Cloud Console.
     *
     * @return array|bool|string Arreglo con [lat, lng, formatted_address]; false si falla la consulta;
     *                           mensaje de error si falta la dirección o la ApiKey.
	 *
	 * @example
	 * ```php
	 * 	//se ejecuta codigo
     * 	$geocodeData = $Location->getGeocodeData($address, $ApiKey);
     * 	if($geocodeData) {
     * 		$latitude  = $geocodeData[0];
     * 		$longitude = $geocodeData[1];
     * 		$address   = $geocodeData[2];
     * 	}else{
     * 		echo "Detalles incorrectos!";
     * 	}
	 * ```
	 *
     */
    public function getGeocodeData($address, $ApiKey): array|bool|string {

        /********************** Validaciones   **********************/
        if ($address === null || trim((string)$address) === '') {
            return 'No ha ingresado una direccion';
        }
        if ($ApiKey === null || trim((string)$ApiKey) === '') {
            return 'No ha ingresado una ApiKey';
        }

        /********************** Si todo esta ok **********************/
        // Preparación de la dirección para URL (reemplazo de espacios y caracteres especiales)
        $addressEnc          = urlencode($address);
        $googleMapUrl        = "https://maps.googleapis.com/maps/api/geocode/json?address=".$addressEnc."&key=".$ApiKey;

        // Defensa SSRF: valida que el destino no apunte a redes internas o reservadas
        $validacionUrl = $this->DataValidations->validarUrlExterna($googleMapUrl);
        if($validacionUrl['success'] === false){
            error_log('[FunctionsLocation::getGeocodeData] '.$validacionUrl['error']);
            return false;
        }

        // Consumo del servicio vía HTTP GET
        $geocodeResponseData = file_get_contents($googleMapUrl);
        $responseData        = json_decode($geocodeResponseData, true);

        /********************** Retorno datos  **********************/
        // Verificación del estado de respuesta de Google
        if($responseData['status'] == 'OK') {

            $latitude         = $responseData['results'][0]['geometry']['location']['lat'] ?? null;
            $longitude        = $responseData['results'][0]['geometry']['location']['lng'] ?? null;
            $formattedAddress = $responseData['results'][0]['formatted_address'] ?? null;

            // Retorno de datos si la geometría y la dirección existen
            if($latitude && $longitude && $formattedAddress) {
                return [
                    $latitude,
                    $longitude,
                    $formattedAddress
                ];
            }
            return false;
        } else {
            // Log de error en caso de que el status sea diferente a OK (ej: OVER_QUERY_LIMIT, REQUEST_DENIED)
            error_log("Google Geocode ERROR: {$responseData['status']}");
            return false;
        }

    }

    /******************************************************************************/
    /**
     * Realiza geocodificación de una dirección utilizando el servicio gratuito Nominatim (OpenStreetMap).
     * * Incluye una fase de limpieza de texto para normalizar abreviaciones comunes en
     * direcciones de habla hispana (Nº, Av.) y cumple con las políticas de uso de
     * Nominatim mediante la definición de un User-Agent.
     *
     * @param string $ubicacion Dirección o calle a geocodificar.
     *
     * @return array|bool|string Diccionario con 'lat', 'lon' y 'display_name'; false si no hay
     *                           resultados; mensaje de error si el dato viene vacío.
     *
     * @example
     * ```php
     * $Location->geocodeAddress('Av. Vicuña Mackenna 1234, Osorno');
     * // Devuelve ['lat' => ..., 'lon' => ..., 'display_name' => ...] o false si no hay resultados
     * ```
     *
     */
    public function geocodeAddress($ubicacion) {

		/**********************  Validaciones   **********************/
        // Retorno inmediato si el valor es nulo o cadena vacía
        if ($ubicacion === null || trim((string)$ubicacion) === '') {
            return 'Sin datos ingresados en ubicacion';
        }

        /********************** Si todo esta ok **********************/
        // Normaliza las abreviaturas
        $ubicacion = $this->normalizarDirecciones($ubicacion);

        // Construcción de la URL de consulta para Nominatim (formato JSON, límite 1 resultado)
        $url = "https://nominatim.openstreetmap.org/search?format=json&limit=1&q=" . urlencode($ubicacion);

        // Configuración de cabeceras obligatorias para evitar bloqueos del servicio
        $opts = [
            "http" => [
                "header" => "User-Agent: MyPHPGeocoder/1.0\r\n"
            ]
        ];
        $context = stream_context_create($opts);

        // Defensa SSRF: valida que el destino no apunte a redes internas o reservadas
        $validacionUrl = $this->DataValidations->validarUrlExterna($url);
        if($validacionUrl['success'] === false){
            error_log('[FunctionsLocation::geocodeAddress] '.$validacionUrl['error']);
            return false;
        }

        // Ejecución de la consulta a la API de OpenStreetMap (el error real queda registrado en el log)
        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            $errorRed = error_get_last();
            error_log('[FunctionsLocation::geocodeAddress] ' . ($errorRed['message'] ?? 'Sin respuesta de Nominatim'));
            return false;
        }

        $data = json_decode($response, true);

        /********************** Retorno datos  **********************/
        // Si el arreglo de resultados no está vacío, retorna el primer elemento
        if (!empty($data) && isset($data[0])) {
            return [
                'lat'          => $data[0]['lat'],
                'lon'          => $data[0]['lon'],
                'display_name' => $data[0]['display_name']
            ];
        }

        /**********************  Retorno datos  **********************/
        return false;

    }

	/******************************************************************************/
    /**
     * Normaliza una dirección chilena para facilitar su comparación.
     *
     * Ejemplos:
     *
     * "Av. Vicuña Mackenna N° 1234"
     *   -> "AVENIDA VICUNA MACKENNA 1234"
     *
     * "Avda Vicuña Mackenna #1234"
     *   -> "AVENIDA VICUNA MACKENNA 1234"
     *
     * "Vicuña Mackenna 1234, Depto. 501"
     *   -> "VICUNA MACKENNA 1234 DEPARTAMENTO 501"
     *
     * "Pje. Las Rosas N° 25, Block B"
     *   -> "PASAJE LAS ROSAS 25 BLOCK B"
     *
     * @param string $Ubicacion Dirección a normalizar.
     *
     * @return string Dirección normalizada en mayúsculas y sin tildes; cadena vacía si el dato viene vacío.
     *
     * @example
     * ```php
     * $Location->normalizarDirecciones('Av. Vicuña Mackenna N° 1234'); // Devuelve 'AVENIDA VICUNA MACKENNA 1234'
     * $Location->normalizarDirecciones('Pje. Las Rosas N° 25, Block B'); // Devuelve 'PASAJE LAS ROSAS 25 BLOCK B'
     * ```
     */
    public function normalizarDirecciones(string $Ubicacion): string {
        // ---------------------------------------------------------------------
        // 1. Limpieza inicial
        // ---------------------------------------------------------------------
        if ($Ubicacion === null || trim((string)$Ubicacion) === '') {
            return '';
        }

        // Normaliza saltos de línea, tabs y espacios
        $Ubicacion = preg_replace('/\s+/u', ' ', trim($Ubicacion));

        // ---------------------------------------------------------------------
        // 2. Mayúsculas
        // ---------------------------------------------------------------------

        $Ubicacion = mb_strtoupper($Ubicacion, 'UTF-8');

        // ---------------------------------------------------------------------
        // 3. Normalización de caracteres
        // ---------------------------------------------------------------------

        $Ubicacion = str_replace(
            ['"', "'", '´', '`', '“', '”', '‘', '’'],
            '',
            $Ubicacion
        );

        // Normaliza diferentes tipos de guion
        $Ubicacion = str_replace(
            ['–', '—', '−'],
            '-',
            $Ubicacion
        );

        // ---------------------------------------------------------------------
        // 4. Elimina tildes
        // ---------------------------------------------------------------------

        $Ubicacion = strtr($Ubicacion, [
            'Á' => 'A',
            'É' => 'E',
            'Í' => 'I',
            'Ó' => 'O',
            'Ú' => 'U',
            'Ü' => 'U',
            'Ñ' => 'N',
        ]);

        // ---------------------------------------------------------------------
        // 5. Normalización de tipos de vía
        // ---------------------------------------------------------------------

        $reemplazos = [

            // Avenida
            '/\bAVDA?\.?\b/u'          => 'AVENIDA',
            '/\bAVENIDA\b/u'           => 'AVENIDA',

            // Calle
            '/\bCLL?\.?\b/u'           => 'CALLE',
            '/\bCALLE\b/u'             => 'CALLE',

            // Pasaje
            '/\bPSJ(?:E)?\.?\b/u'      => 'PASAJE',
            '/\bPJE\.?\b/u'            => 'PASAJE',
            '/\bPASAJE\b/u'            => 'PASAJE',

            // Camino
            '/\bCAM\.?\b/u'            => 'CAMINO',
            '/\bCAMINO\b/u'            => 'CAMINO',

            // Ruta
            '/\bRTA\.?\b/u'            => 'RUTA',
            '/\bRUTA\b/u'              => 'RUTA',

            // Carretera
            '/\bCTRA\.?\b/u'           => 'CARRETERA',
            '/\bCARRET\.?\b/u'         => 'CARRETERA',
            '/\bCARRETERA\b/u'         => 'CARRETERA',

            // Autopista
            '/\bAUT\.?\b/u'            => 'AUTOPISTA',
            '/\bAUTOP\.?\b/u'          => 'AUTOPISTA',
            '/\bAUTOPISTA\b/u'         => 'AUTOPISTA',

            // Alameda
            '/\bALAM\.?\b/u'           => 'ALAMEDA',
            '/\bALAMEDA\b/u'            => 'ALAMEDA',

            // Costanera
            '/\bCOST\.?\b/u'           => 'COSTANERA',
            '/\bCOSTANERA\b/u'         => 'COSTANERA',

            // Diagonal
            '/\bDIAG\.?\b/u'           => 'DIAGONAL',
            '/\bDIAGONAL\b/u'          => 'DIAGONAL',

            // Circular
            '/\bCIRC\.?\b/u'           => 'CIRCULAR',
            '/\bCIRCULAR\b/u'          => 'CIRCULAR',

            // Boulevard
            '/\bBLVD?\.?\b/u'          => 'BOULEVARD',
            '/\bBLVRD\.?\b/u'          => 'BOULEVARD',
            '/\bBOULEVARD\b/u'         => 'BOULEVARD',

            // Paseo
            '/\bPSO\.?\b/u'            => 'PASEO',
            '/\bPASEO\b/u'             => 'PASEO',

            // Plaza
            '/\bPLZ\.?\b/u'            => 'PLAZA',
            '/\bPLZA\.?\b/u'           => 'PLAZA',
            '/\bPLAZA\b/u'             => 'PLAZA',
        ];

        foreach ($reemplazos as $patron => $reemplazo) {
            $Ubicacion = preg_replace($patron, $reemplazo, $Ubicacion);
        }

        // ---------------------------------------------------------------------
        // 6. Orientaciones
        // ---------------------------------------------------------------------

        $orientaciones = [

            '/\bNTE?\.?\b/u'       => 'NORTE',
            '/\bNORTE\b/u'         => 'NORTE',

            '/\bSUR\b/u'           => 'SUR',

            '/\bOTE?\.?\b/u'       => 'ORIENTE',
            '/\bORIENTE\b/u'       => 'ORIENTE',

            '/\bPTE?\.?\b/u'       => 'PONIENTE',
            '/\bPONIENTE\b/u'      => 'PONIENTE',

            '/\bOESTE\b/u'         => 'OESTE',
        ];

        foreach ($orientaciones as $patron => $reemplazo) {
            $Ubicacion = preg_replace($patron, $reemplazo, $Ubicacion);
        }

        // ---------------------------------------------------------------------
        // 7. Número de dirección
        // ---------------------------------------------------------------------

        // N°, Nº, N.°, NRO, NRO., NUM, NUMERO
        $Ubicacion = preg_replace(
            '/\bN(?:[°º]|\.?°|\.?\b)\s*/u',
            '',
            $Ubicacion
        );

        $Ubicacion = preg_replace(
            '/\bNRO?\.?\s*/u',
            '',
            $Ubicacion
        );

        $Ubicacion = preg_replace(
            '/\bNUM(?:ERO)?\.?\s*/u',
            '',
            $Ubicacion
        );

        // #
        $Ubicacion = preg_replace('/#\s*/u', '', $Ubicacion);

        // ---------------------------------------------------------------------
        // 8. Departamento / oficina / local / piso
        // ---------------------------------------------------------------------

        $componentes = [

            // Departamento
            '/\bDEPTO?\.?\b/u'       => 'DEPARTAMENTO',
            '/\bDPTO?\.?\b/u'        => 'DEPARTAMENTO',
            '/\bDEPT\.?\b/u'         => 'DEPARTAMENTO',
            '/\bDEPARTAMENTO\b/u'    => 'DEPARTAMENTO',

            // Oficina
            '/\bOF\.?\b/u'           => 'OFICINA',
            '/\bOFIC\.?\b/u'         => 'OFICINA',
            '/\bOFICINA\b/u'         => 'OFICINA',

            // Local
            '/\bLOC\.?\b/u'          => 'LOCAL',
            '/\bLOCAL\b/u'           => 'LOCAL',

            // Suite
            '/\bSTE\.?\b/u'          => 'SUITE',
            '/\bSUITE\b/u'           => 'SUITE',

            // Piso
            '/\bPISO\b/u'            => 'PISO',
        ];

        foreach ($componentes as $patron => $reemplazo) {
            $Ubicacion = preg_replace($patron, $reemplazo, $Ubicacion);
        }

        // ---------------------------------------------------------------------
        // 9. Block / casa / torre / edificio
        // ---------------------------------------------------------------------

        $vivienda = [

            '/\bBLK\.?\b/u'              => 'BLOCK',
            '/\bBLOCK\b/u'               => 'BLOCK',

            '/\bTOR\.?\b/u'              => 'TORRE',
            '/\bTORRE\b/u'               => 'TORRE',

            '/\bCS\.?\b/u'               => 'CASA',
            '/\bCSA\.?\b/u'              => 'CASA',
            '/\bCASA\b/u'                => 'CASA',

            '/\bEDIF\.?\b/u'             => 'EDIFICIO',
            '/\bEDIFICIO\b/u'            => 'EDIFICIO',
        ];

        foreach ($vivienda as $patron => $reemplazo) {
            $Ubicacion = preg_replace($patron, $reemplazo, $Ubicacion);
        }

        // ---------------------------------------------------------------------
        // 10. Parcela / lote / sitio
        // ---------------------------------------------------------------------

        $terreno = [

            '/\bPCL\.?\b/u'              => 'PARCELA',
            '/\bPARC\.?\b/u'             => 'PARCELA',
            '/\bPARCELA\b/u'             => 'PARCELA',

            '/\bLOTE\b/u'                => 'LOTE',

            '/\bSITIO\b/u'               => 'SITIO',
        ];

        foreach ($terreno as $patron => $reemplazo) {
            $Ubicacion = preg_replace($patron, $reemplazo, $Ubicacion);
        }

        // ---------------------------------------------------------------------
        // 11. Villa / población / condominio / sector
        // ---------------------------------------------------------------------

        $sectores = [

            '/\bCONDO?\.?\b/u'           => 'CONDOMINIO',
            '/\bCONDOMINIO\b/u'          => 'CONDOMINIO',

            '/\bPOB\.?\b/u'              => 'POBLACION',
            '/\bPOBL\.?\b/u'             => 'POBLACION',
            '/\bPOBLACION\b/u'           => 'POBLACION',

            '/\bSECT\.?\b/u'             => 'SECTOR',
            '/\bSECTOR\b/u'              => 'SECTOR',

            '/\bVILLA\b/u'               => 'VILLA',

            '/\bPARCELACION\b/u'         => 'PARCELACION',
        ];

        foreach ($sectores as $patron => $reemplazo) {
            $Ubicacion = preg_replace($patron, $reemplazo, $Ubicacion);
        }

        // ---------------------------------------------------------------------
        // 12. Kilómetros
        // ---------------------------------------------------------------------

        $Ubicacion = preg_replace(
            '/\bKM\.?\s*/u',
            'KILOMETRO ',
            $Ubicacion
        );

        $Ubicacion = preg_replace(
            '/\bKILOMETROS?\b/u',
            'KILOMETRO',
            $Ubicacion
        );

        // ---------------------------------------------------------------------
        // 13. Normalización de ordinales
        // ---------------------------------------------------------------------

        // 1° -> 1
        // 2º -> 2
        // 3° -> 3
        $Ubicacion = preg_replace(
            '/(\d+)\s*[°º]/u',
            '$1',
            $Ubicacion
        );

        // ---------------------------------------------------------------------
        // 14. Normalización de números con letras
        // ---------------------------------------------------------------------

        // 123 A -> 123-A
        $Ubicacion = preg_replace(
            '/\b(\d+)\s+([A-Z])\b/u',
            '$1-$2',
            $Ubicacion
        );

        // ---------------------------------------------------------------------
        // 15. Limpieza de caracteres
        // ---------------------------------------------------------------------

        /*
        * Conserva:
        * - Letras
        * - Números
        * - Espacios
        * - Puntos
        * - Guiones
        *
        * En esta etapa todavía conservamos los puntos porque
        * pueden formar parte de abreviaturas que no hayan sido
        * procesadas.
        */
        $Ubicacion = preg_replace(
            '/[^\p{L}\p{N}\s.\-]/u',
            ' ',
            $Ubicacion
        );

        // ---------------------------------------------------------------------
        // 16. Normalización de puntos
        // ---------------------------------------------------------------------

        // Varios puntos consecutivos -> uno
        $Ubicacion = preg_replace(
            '/\.{2,}/u',
            '.',
            $Ubicacion
        );

        // ---------------------------------------------------------------------
        // 17. Elimina puntos residuales
        // ---------------------------------------------------------------------

        /*
        * A esta altura ya procesamos las abreviaturas.
        *
        * Por lo tanto:
        *
        * "AV." -> "AVENIDA"
        * "DEPTO." -> "DEPARTAMENTO"
        * "KM." -> "KILOMETRO"
        *
        * Los puntos restantes normalmente son ruido.
        */
        $Ubicacion = str_replace('.', ' ', $Ubicacion);

        // ---------------------------------------------------------------------
        // 18. Normalización de guiones
        // ---------------------------------------------------------------------

        $Ubicacion = preg_replace(
            '/\s*-\s*/u',
            '-',
            $Ubicacion
        );

        // ---------------------------------------------------------------------
        // 19. Normalización de espacios
        // ---------------------------------------------------------------------

        $Ubicacion = preg_replace(
            '/\s+/u',
            ' ',
            $Ubicacion
        );

        // ---------------------------------------------------------------------
        // 20. Limpieza final
        // ---------------------------------------------------------------------

        $Ubicacion = trim($Ubicacion, " .-\t\n\r\0\x0B");

        /**********************  Retorno datos  **********************/
        return $Ubicacion;

    }

	/******************************************************************************/
    /**
     * Geocodificación inversa utilizando la API de Google Maps.
     * * Recibe latitud y longitud directamente (en vez de dirección de texto)
     * y devuelve los datos geográficos formateados. Es un espejo de `getGeocodeData`
     * cambiando el parámetro de entrada de `address` a `latlng`.
     *
     * @param float $lat   Latitud del punto (debe estar en [-90, 90]).
     * @param float $lng   Longitud del punto (debe estar en [-180, 180]).
     * @param string $ApiKey Llave de API de Google Cloud Console.
     *
     * @return array|bool|string Arreglo con [lat, lng, formatted_address]; false si falla la consulta;
     *                         false si status ≠ OK (con error_log).
     *
     * @example
     * ```php
     * $Location->getGeocodeDataInverso(-33.4489, -70.6693, 'TU_API_KEY');
     * // Devuelve [lat, lng, formatted_address] o false si falla
     * ```
     *
     */
    public function getGeocodeDataInverso($lat, $lng, $ApiKey): array|bool|string {

        /********************** Validaciones   **********************/
        if ($lat === null || trim((string)$lat) === '' || $lat < -90 || $lat > 90) {
            return 'No ha ingresado coordenadas válidas';
        }
        if ($lng === null || trim((string)$lng) === '' || $lng < -180 || $lng > 180) {
            return 'No ha ingresado coordenadas longitud válidas';
        }
        if ($ApiKey === null || trim((string)$ApiKey) === '') {
            return 'No ha ingresado una ApiKey';
        }

        /********************** Si todo esta ok **********************/
        // Preparación de la lat/lng para URL
        $latEnc  = urlencode($lat);
        $lngEnc  = urlencode($lng);
        $googleMapUrl = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$latEnc.",".$lngEnc."&key=".$ApiKey;

        // Defensa SSRF: valida que el destino no apunte a redes internas o reservadas
        $validacionUrl = $this->DataValidations->validarUrlExterna($googleMapUrl);
        if($validacionUrl['success'] === false){
            error_log('[FunctionsLocation::getGeocodeDataInverso] '.$validacionUrl['error']);
            return false;
        }

        // Consumo del servicio vía HTTP GET (el error real queda registrado en el log)
        $geocodeResponseData = @file_get_contents($googleMapUrl);
        if ($geocodeResponseData === false) {
            $errorRed = error_get_last();
            error_log('[FunctionsLocation::getGeocodeDataInverso] ' . ($errorRed['message'] ?? 'Sin respuesta de Google Geocode'));
            return false;
        }

        $responseData = json_decode($geocodeResponseData, true);

        /********************** Retorno datos  **********************/
        // Verificación del estado de respuesta de Google (respuesta no JSON => error controlado)
        if (!is_array($responseData)) {
            error_log("Google Geocode Inverso ERROR: respuesta sin JSON válido");
            return false;
        }

        if(($responseData['status'] ?? '') == 'OK') {

            $latitude         = $responseData['results'][0]['geometry']['location']['lat'] ?? null;
            $longitude        = $responseData['results'][0]['geometry']['location']['lng'] ?? null;
            $formattedAddress = $responseData['results'][0]['formatted_address'] ?? null;

            // Retorno de datos si la geometría y la dirección existen
            if($latitude && $longitude && $formattedAddress) {
                return [
                    $latitude,
                    $longitude,
                    $formattedAddress
                ];
            }
            return false;
        } else {
            // Log de error en caso de que el status sea diferente a OVER_QUERY_LIMIT, REQUEST_DENIED, etc.
            error_log("Google Geocode Inverso ERROR: " . ($responseData['status'] ?? 'sin status'));
            return false;
        }

    }

    /******************************************************************************/
    /**
     * Formatea unas coordenadas lat/lng a una cadena de texto con el número
     * de decimales especificado.
     * * Resultado en formato: "-33.448900, -70.669300"
     * * Si los decimales son 0, no incluye separador decimal.
     * * Valida que las coordenadas estén dentro de rangos geográficos válidos.
     *
     * @param float $Lat   Latitud a formatear.
     * @param float $Long  Longitud a formatear.
     * @param int $Decimales Número de decimales a mostrar (0 a 8, por defecto 6).
     *
     * @return string|string-error Cadena con las coordenadas formateadas, o un mensaje de error si las coordenadas son inválidas.
     *
     * @example
     * ```php
     * $Location->formatoCoordenada(-33.4489, -70.6693, 6); // Devuelve "-33.448900, -70.669300"
     * $Location->formatoCoordenada(-33.4489, -70.6693, 0); // Devuelve "-33, -70"
     * $Location->formatoCoordenada(91, -70);            // Devuelve 'Latitud debe estar en [-90, 90]'
     * ```
     *
     */
    public function formatoCoordenada($Lat, $Long, $Decimales = 6): ?string {

        /********************** Validaciones   **********************/
        if (!is_numeric($Lat) || !is_numeric($Long)) {
            return 'Ambas coordenadas deben ser valores numéricos';
        }
        $Lat = (float) $Lat;
        $Long = (float) $Long;

        if ($Lat < -90 || $Lat > 90) {
            return 'Latitud debe estar en [-90, 90]';
        }
        if ($Long < -180 || $Long > 180) {
            return 'Longitud debe estar en [-180, 180]';
        }

        /********************** Formateo **********************/
        // Asegurar que $Decimales sea un entero válido
        $Decimales = (int) $Decimales;
        if ($Decimales < 0) { $Decimales = 0; }
        if ($Decimales > 8) { $Decimales = 8; }

        /********************** Retorno datos **********************/
        // Formato: "lat, lng" con el número especificado de decimales
        $formato = sprintf('%.' . $Decimales . 'F, %.' . $Decimales . 'F', $Lat, $Long);
        return $formato;

    }

    /******************************************************************************/
    /**
     * Valida que las coordenadas geográficas (lat/long) estén dentro
     * de los rangos geográficos permitidos.
     * * Latitud debe estar en el rango [-90, 90].
     * * Longitud debe estar en el rango [-180, 180].
     * * Acepta valores numéricos y strings numéricos.
     *
     * @param mixed $Lat  Latitud a validar (número o string numérico).
     * @param mixed $Long Longitud a validar (número o string numérico).
     *
     * @return true|string True si las coordenadas son válidas, o un mensaje de error string nombrando el campo.
     *
     * @example
     * ```php
     * $Location->coordenadasValidas(-33.4489, -70.6693); // Devuelve true
     * $Location->coordenadasValidas(91, -70);            // Devuelve 'Latitud debe estar en [-90, 90]'
     * $Location->coordenadasValidas('abc', -70);         // Devuelve 'Latitud debe ser un número'
     * ```
     *
     */
    public function coordenadasValidas($Lat, $Long): ?string {

        /********************** Validaciones   **********************/
        // Normalizar a números si vienen como strings
        if (is_string($Lat)) {
            if (!is_numeric($Lat)) {
                return 'Latitud debe ser un número';
            }
            $Lat = (float) $Lat;
        } else {
            $Lat = (float) $Lat;
        }

        if (is_string($Long)) {
            if (!is_numeric($Long)) {
                return 'Longitud debe ser un número';
            }
            $Long = (float) $Long;
        } else {
            $Long = (float) $Long;
        }

        /********************** Validación de rangos **********************/
        $errores = [];

        if ($Lat < -90 || $Lat > 90) {
            $errores[] = 'Latitud debe estar en [-90, 90]';
        }
        if ($Long < -180 || $Long > 180) {
            $errores[] = 'Longitud debe estar en [-180, 180]';
        }

        /********************** Retorno datos **********************/
        if (empty($errores)) {
            return true;
        }
        // Retornar el primer error encontrado
        return $errores[0];

    }

    /*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Privados                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
    /******************************************************************************/
    /**
	 * Valida que un dato corresponda a un valor numérico (coordenada geográfica).
	 * * Rechaza valores nulos y cadenas vacías; el resto se delega en
	 * FunctionsDataValidations::validarNumero().
	 *
	 * @param mixed  $Data Valor a validar (ej: '-40.807289').
	 * @param string $Name Nombre del campo para componer el mensaje de error.
	 *
	 * @return true|string True si el valor es numérico; mensaje de error en caso contrario.
	 *
	 * @example
	 * ```php
	 * $this->_validateValue('-40.807289', 'latitude1'); // Devuelve true
	 * $this->_validateValue('abc', 'latitude1');        // Devuelve 'El dato ingresado en latitude1 no es un numero (abc)'
	 * ```
	 */
	private function _validateValue($Data, $Name){

		/**********************  Validaciones   **********************/
        // Retorno inmediato si el valor es nulo, cadena vacía o numéricamente cero
        if ($Data === null || trim((string)$Data) === '') {
            return 'Sin datos ingresados en '.$Name;
        }
        // Validación de tipos de datos mediante el componente externo DataValidations
        if (!$this->DataValidations->validarNumero($Data)) {
            return 'El dato ingresado en '.$Name.' no es un numero ('.$Data.')';
        }

		/**********************  Retorno datos  **********************/
		return true;

	}




}
