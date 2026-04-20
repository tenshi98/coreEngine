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
	/**
     * Gestiona el envío de mensajes de WhatsApp utilizando plantillas (Templates) a través de la API 1msg.io.
     *
     * La función configura la estructura del mensaje basada en diferentes tipos de plantillas (Type),
     * normaliza el número telefónico y el contenido del texto, y realiza una petición POST
     * mediante cURL al endpoint de la instancia correspondiente.
     *
     * @param array $Config Configuración que incluye 'Token', 'InstanceId', 'namespace', 'template' y 'Type'.
     * @param array $Body Datos del mensaje como 'Phone', 'Titulo', 'Mensaje', 'Entidad', 'Link' o 'Cuerpo'.
     *
     * @return array Resultado de la operación con estado 'success' y los datos de respuesta o error.
	 *
	 * @example
	 * ```php
	 *
	 * ```
	 *
     */
    public function sendWhatsappTemplate($Config, $Body): array {

        /********************** Validaciones   **********************/
        // Verifica que la configuración sea un arreglo válido y no esté vacío
        if(!is_array($Config) || empty($Config)){   return ['success' => false, 'error' => 'No ha ingresado el Body'];}
        // Verifica que el cuerpo del mensaje sea un arreglo válido y no esté vacío
        if(!is_array($Body) || empty($Body)){       return ['success' => false, 'error' => 'No ha ingresado el Body'];}

        /********************** Si todo esta ok **********************/
        // Estructura la data del mensaje según el tipo de plantilla especificado en la configuración
        switch ($Config['Type']) {
            /*********************************************************/
            // Tipo 1: Plantilla estándar con parámetros de Título y Mensaje
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
                    // Normalización del teléfono mediante el componente DataNumbers
                    "phone" => $this->DataNumbers->normalizarPhone($Body['Phone'])
                ];
                break;
            /*********************************************************/
            // Tipo 2: Plantilla extendida con Entidad, Mensaje y un Enlace (Link)
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
            // Tipo 999: Plantilla específica para alertas de monitoreo IoT
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
            // Tipo 1000: Reservado para futuras implementaciones
            case 1000:
                //otra
                break;

        }
        // Conversión del arreglo de datos a formato JSON para el cuerpo de la petición
        $data_string = json_encode($data);

        /**************************************/
        // Configuración de la petición HTTP mediante cURL hacia el endpoint de la instancia
        $url = 'https://api.1msg.io/'.$Config['InstanceId'].'/sendTemplate';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        /********************** Retorno datos  **********************/
        // Ejecución de la petición y manejo de la respuesta o excepciones
        try {
            $whatsappResult = curl_exec($ch);
            curl_close($ch);
            // Decodificación de la respuesta JSON del servidor
            $whatsappRes = json_decode($whatsappResult);
            // Validación de la propiedad 'sent' en la respuesta de la API
            if (isset($whatsappRes->sent) && $whatsappRes->sent === true) {
                return ['success' => true, 'data' => $whatsappRes];
            } else {
                return ['success' => false, 'error' => $whatsappRes];
            }
        } catch (\Throwable $th) {
            // Cierre preventivo del recurso cURL en caso de error fatal
            curl_close($ch);
            return ['success' => false, 'error' => $th->getMessage(), 'code' => $th->getCode()];
        }
    }

    /************************************************************************************************************/
	/**
     * Reformatea texto con etiquetas HTML básicas a un formato compatible con WhatsApp.
     *
     * Transforma etiquetas de salto de línea en separadores de texto y etiquetas de
     * negrita en el formato de asteriscos utilizado por la sintaxis de WhatsApp.
     *
     * @param string $Texto El texto original que contiene etiquetas HTML.
     *
     * @return string El texto procesado y compatible con el formato de WhatsApp.
	 *
	 * @example
	 * ```php
	 *
	 * ```
	 *
     */
    public function formatWhatsappText($Texto): string {

        /********************** Si todo esta ok **********************/
        // Reemplazo de etiquetas HTML por equivalentes visuales o de formato en WhatsApp
        $Texto = str_replace(
            ['<br/>', '<br>', '</br>', '<strong>', '</strong>'],
            [' // ',  ' // ', ' // ',   '*',         '*'],
            $Texto
        );

        /********************** Retorno datos  **********************/
        return $Texto;

    }


}
