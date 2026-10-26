<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsServerSecurity {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Publicos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
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
     * $ServerSecurity->sendIPtoBlackList('203.0.113.45');
     * // devuelve ['success' => true, 'data' => ...] o ['success' => false, 'error' => 'La dirección IP ingresada no es válida']
     * ```
     *
     */
    public function sendIPtoBlackList(string $IP): array {

        /********************** Validaciones   **********************/
        // Valida que el parámetro IP contenga información antes de proceder
        if ($IP === null || trim((string)$IP) === '') {
            return ['success' => false, 'error' => 'No ha ingresado una IP'];
        }

        // Valida estrictamente que sea una dirección IPv4 o IPv6 válida.
        if (filter_var($IP, FILTER_VALIDATE_IP) === false) {
            return ['success' => false, 'error' => 'La dirección IP ingresada no es válida'];
        }

        /********************** Si todo esta ok **********************/
        try {
            // Instancia las funciones de servidor para ejecución de tareas de sistema
            $Server    = new FunctionsServerShell();

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

    /******************************************************************************/
    /**
     * Envía una dirección IP a la lista blanca del firewall del servidor.
     *
     * Actúa como un puente hacia las tareas de bajo nivel del servidor para
     * ejecutar comandos de apertura (iptables) sobre una IP específica.
     * Es el espejo de sendIPtoBlackList pero usando el tipo 3 (eliminar bloqueo).
     *
     * @param string $IP Dirección IP que se desea agregar a la lista blanca.
     *
     * @return array Resultado de la operación delegada al servidor.
     *
     * @example
     * ```php
     * $ServerSecurity->sendIPtoWhiteList('203.0.113.45');
     * // devuelve ['success' => true, 'data' => ...] o ['success' => false, 'error' => 'La dirección IP ingresada no es válida']
     * ```
     *
     */
    public function sendIPtoWhiteList(string $IP): array {

        /********************** Validaciones   **********************/
        // Valida que el parámetro IP contenga información antes de proceder
        if ($IP === null || trim((string)$IP) === '') {
            return ['success' => false, 'error' => 'No ha ingresado una IP'];
        }

        // Valida estrictamente que sea una dirección IPv4 o IPv6 válida.
        if (filter_var($IP, FILTER_VALIDATE_IP) === false) {
            return ['success' => false, 'error' => 'La dirección IP ingresada no es válida'];
        }

        /********************** Si todo esta ok **********************/
        try {
            // Instancia las funciones de servidor para ejecución de tareas de sistema
            $Server    = new FunctionsServerShell();

            // Llama al ejecutor de tareas con el Tipo 3 (definido para desbloqueo de IP/Iptables)
            $resultado = $Server->tareasServer($IP, 3);

            /********************** Retorno datos  **********************/
            // Replica el estado de éxito y la respuesta del comando ejecutado
            return ['success' => $resultado['success'], 'data' => $resultado['data']];

        } catch (Exception $e) {
            // Manejo de excepciones en la comunicación con el núcleo del servidor
            return ['success' => false, 'error' => $e->getMessage()];
        }

    }

}
