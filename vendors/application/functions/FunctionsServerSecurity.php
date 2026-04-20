<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsServerSecurity {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
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
        if($URL==''){  return ['success' => false, 'error' => 'No ha ingresado una URL']; }

        /********************** Si todo esta ok **********************/
        try {
            // Inicialización de contenedor para los indicadores procesados
            $arrData = array();

            // Instancia del servidor para utilizar el método de obtención de datos XML
            $Server    = new FunctionsServerWeb();
            $resultado = $Server->obtenerDatosXML($URL);

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

	/************************************************************************************************************/
	/**
     * Envía una dirección IP a la lista negra del firewall del servidor.
     *
     * Actúa como un puente hacia las tareas de bajo nivel del servidor para
     * ejecutar comandos de bloqueo (iptables) sobre una IP específica.
     *
     * @param string $IP Dirección IP que se desea bloquear.
     *
     * @return array Resultado de la operación delegada al servidor.
	 *
	 * @example
	 * ```php
	 *
	 * ```
	 *
     */
    public function sendIPtoBlackList(string $IP): array {

        /********************** Validaciones   **********************/
        // Valida que el parámetro IP contenga información antes de proceder
        if($IP==''){  return ['success' => false, 'error' => 'No ha ingresado una IP']; }

        /********************** Si todo esta ok **********************/
        try {
            // Instancia las funciones de servidor para ejecución de tareas de sistema
            $Server    = new FunctionsServerServer();

            // Llama al ejecutor de tareas con el Tipo 1 (definido para bloqueo de IP/Iptables)
            $resultado = $Server->tareasServer($IP, 1);

            /********************** Retorno datos  **********************/
            // Replica el estado de éxito y la respuesta del comando ejecutado
            return ['success' => $resultado['success'], 'data' => $resultado['data']];

        } catch (Exception $e) {
            // Manejo de excepciones en la comunicación con el núcleo del servidor
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

}

