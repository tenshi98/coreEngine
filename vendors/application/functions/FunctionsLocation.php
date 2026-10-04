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
	/************************************************************************************************************/
	//Definiciones
	private $DataValidations;

	/************************************************************************************************************/
	//Instancias
	public function __construct() {
		$this->DataValidations = new FunctionsDataValidations();
	}

    /*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
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
	 * $Location->calcularDistancia(-40.807289, -72.634907, -42.176560, -73.425923);
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

    /************************************************************************************************************/
    /**
     * Obtiene coordenadas y dirección formateada desde la API de Geocoding de Google Maps.
     * * Convierte una dirección de texto plano en datos geográficos (Latitud y Longitud)
     * utilizando los servicios de Google. Requiere una API Key válida y activa.
     *
     * @param string $address Dirección completa a consultar (ej: "Av. Siempreviva 742, Springfield").
     * @param string $ApiKey  Llave de API autorizada por Google Cloud Console.
     *
     * @return array|bool Arreglo con [lat, lng, formatted_address] o false si falla.
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

    /************************************************************************************************************/
    /**
     * Realiza geocodificación de una dirección utilizando el servicio gratuito Nominatim (OpenStreetMap).
     * * Incluye una fase de limpieza de texto para normalizar abreviaciones comunes en
     * direcciones de habla hispana (Nº, Av.) y cumple con las políticas de uso de
     * Nominatim mediante la definición de un User-Agent.
     *
     * @param string $street Dirección o calle a geocodificar.
     *
     * @return array|bool Diccionario con 'lat', 'lon' y 'display_name', o false si no hay resultados.
	 *
	 * @example
	 * ```php
	 *
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

        // Ejecución de la consulta a la API de OpenStreetMap
        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
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

        return false;
    }

    /************************************************************************************************************/
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
     * @param string $ubicacion
     * @return string
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

        return $Ubicacion;
    }



	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Internos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
    /************************************************************************************************************/
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

/************************************************************************************************************/
class subpointLocation {
    /*
    *=================================================     Detalles    =================================================
    *
    * Permite verificar si punto de georeferencia se ubica dentro de una geocerca referenciada
    *
    *=================================================    Modo de uso  =================================================
    *
    * 	//se ejecuta codigo
    * 	//Se crea geocerca
    * 	$polygon = array();
    * 	array_push( $polygon,-37.085118 -72.739278 );//Punto 1
    * 	array_push( $polygon,-37.281183 -72.832662 );//Punto 2
    * 	array_push( $polygon,-37.267195 -71.992208 );//Punto 3
    * 	array_push( $polygon,-36.858664 -71.964742 );//Punto 4
    * 	array_push( $polygon,-37.085118 -72.739278 );//Se cierra figura
    * 	//se verifica si se esta dentro
    * 	$pointLocation = new subpointLocation();
    * 	//$c_chek =  $pointLocation->pointInPolygon(-40.807289 -72.634907, $polygon);
    * 	$c_chek =  $pointLocation->pointInPolygon($point, $polygon);
    * 	if($c_chek=='inside'){
    *
    * 	}
    *
    *=================================================    Parametros   =================================================
    * @input   object   $polygon   Geocerca definida
    * @input   string   $point     Latitud y longitus separado por un espacio
    * @return  string
    *===================================================================================================================
    */
    var $pointOnVertex = true; // Check if the point sits exactly on one of the vertices?

    function pointLocation() {
        //Nada de momento
    }

    function pointInPolygon($point, $polygon, $pointOnVertex = true) {
        $this->pointOnVertex = $pointOnVertex;

        // Transform string coordinates into arrays with x and y values
        $point = $this->pointStringToCoordinates($point);
        $vertices = array();
        foreach ($polygon as $vertex) {
            $vertices[] = $this->pointStringToCoordinates($vertex);
        }

        // Check if the point sits exactly on a vertex
        if ($this->pointOnVertex == true and $this->pointOnVertex($point, $vertices) == true) {
            return "vertex";
        }

        // Check if the point is inside the polygon or on the boundary
        $intersections = 0;
        $vertices_count = count($vertices);

        for ($i=1; $i < $vertices_count; $i++) {
            $vertex1 = $vertices[$i-1];
            $vertex2 = $vertices[$i];
            if ($vertex1['y'] == $vertex2['y'] and $vertex1['y'] == $point['y'] and $point['x'] > min($vertex1['x'], $vertex2['x']) and $point['x'] < max($vertex1['x'], $vertex2['x'])) { // Check if point is on an horizontal polygon boundary
                return "boundary";
            }
            if ($point['y'] > min($vertex1['y'], $vertex2['y']) and $point['y'] <= max($vertex1['y'], $vertex2['y']) and $point['x'] <= max($vertex1['x'], $vertex2['x']) and $vertex1['y'] != $vertex2['y']) {
                $xinters = ($point['y'] - $vertex1['y']) * ($vertex2['x'] - $vertex1['x']) / ($vertex2['y'] - $vertex1['y']) + $vertex1['x'];
                if ($xinters == $point['x']) { // Check if point is on the polygon boundary (other than horizontal)
                    return "boundary";
                }
                if ($vertex1['x'] == $vertex2['x'] || $point['x'] <= $xinters) {
                    $intersections++;
                }
            }
        }
        // If the number of edges we passed through is odd, then it's in the polygon.
        if ($intersections % 2 != 0) {
            return "inside";
        } else {
            return "outside";
        }
    }

    function pointOnVertex($point, $vertices) {
        foreach($vertices as $vertex) {
            if ($point == $vertex) {
                return true;
            }
        }

    }

    function pointStringToCoordinates($pointString) {
        $coordinates = explode(" ", $pointString);
        return array("x" => $coordinates[0], "y" => $coordinates[1]);
    }

}
