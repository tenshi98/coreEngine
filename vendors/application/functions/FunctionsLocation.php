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
		$dataVal_2 = $this->_validateValue($latitude2, 'latitude2');
		$dataVal_3 = $this->_validateValue($longitude1, 'longitude1');
		$dataVal_4 = $this->_validateValue($longitude2, 'longitude2');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }
		if ($dataVal_2 !== true) { return $dataVal_2; }
		if ($dataVal_3 !== true) { return $dataVal_3; }
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
        if(!isset($address) || $address==''){ return 'No ha ingresado una direccion';}
        if(!isset($ApiKey) || $ApiKey==''){   return 'No ha ingresado una ApiKey';}

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
        if ($ubicacion=='') { return 'Sin datos ingresados en ubicacion';}

        /********************** Si todo esta ok **********************/
        // Normaliza las abreviaturas
        $reemplazos = [
            '/\bAVENIDA\b|\bAV(?:\.|\s|$)/iu'   => 'Avenida',   // Avenida
            '/\bCALLE\b|\bCLL(?:\.|\s|$)/iu'    => 'Calle',     // Calle
            '/\bPASAJE\b|\bPSJE(?:\.|\s|$)/iu'  => 'Pasaje',    // Pasaje
            '/\bCAMINO\b|\bCAM(?:\.|\s|$)/iu'   => 'Camino',    // Camino
            '/\bRUTA\b|\bRTA(?:\.|\s|$)/iu'     => 'Ruta',      // Ruta
            '/\bKILOMETRO\b|\bKM(?:\.|\s|$)/iu' => 'Kilometro', // Kilómetro
            '/\bN[°º]\b|\bNO\.\b|#/iu'          => '',          // Número
        ];

        // Realiza el cambio
        foreach ($reemplazos as $patron => $reemplazo) {
            $ubicacion = preg_replace($patron, $reemplazo, $ubicacion);
        }

        // Elimina espacios múltiples
        $ubicacion = preg_replace('/\s+/', ' ', $ubicacion);

        // Eliminacion de espacios en blanco
        $ubicacion = trim($ubicacion);

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


	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Internos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
    /************************************************************************************************************/
	private function _validateValue($Data, $Name){

		/**********************  Validaciones   **********************/
        // Retorno inmediato si el valor es nulo, cadena vacía o numéricamente cero
        if ($Data=='' || $Data==0) { return 'Sin datos ingresados en '.$Name;}
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
