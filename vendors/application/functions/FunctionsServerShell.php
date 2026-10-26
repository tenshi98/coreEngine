<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsServerShell {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Publicos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
    /**
     * Ejecuta comandos administrativos o peticiones web en segundo plano en el servidor.
     *
     * Permite delegar tareas pesadas o de configuración (bloqueo de IP, wget) al sistema operativo.
     * Implementa validaciones de seguridad para IPs y URLs antes de la ejecución.
     *
     * @param string $tarea Dato de entrada (IP o URL) según el tipo de tarea.
     * @param int $Type Identificador del tipo de tarea (1: Iptables, 2: Wget, 3: Personalizado).
     *
     * @return array Resultado con éxito y datos, o error: ['success' => bool, 'data' => string].
	 *
	 * @example
	 * ```php
	 * $Shell->tareasServer('192.168.1.10', 1); //bloquea la IP en iptables; devuelve ['success' => true, 'data' => 'Ejecucion correcta']
	 * $Shell->tareasServer('https://www.ejemplo.com?param1=1', 2); //descarga la URL con wget en segundo plano
	 * ```
	 *
     */
    public function tareasServer(string $tarea, int $Type): array {

        /********************** Si todo esta ok **********************/
        try {
            // Validación de integridad: la cadena de tarea no debe ser nula o vacía
            if (empty($tarea)) {
                return ['success' => false, 'data' => 'La tarea no puede estar vacía.'];
            }

            // Selección del comando de sistema a construir basado en el tipo de operación
            switch ($Type) {
                /*************************/
                // Caso 1: Gestión de seguridad mediante bloqueo de IP en el firewall (iptables)
                case 1:
                    if(filter_var(trim($tarea), FILTER_VALIDATE_IP)){
                        // Genera un script multilínea para añadir la IP al DROP y persistir cambios
                        $command = "
                        # Agrega la IP a la lista negra (DROP todo el tráfico entrante)
                        iptables -A INPUT -s ".$tarea." -j DROP

                        # Guarda los cambios (puede variar según la distribución)
                        if command -v netfilter-persistent &> /dev/null; then
                            netfilter-persistent save
                        elif command -v iptables-save &> /dev/null; then
                            iptables-save > /etc/iptables/rules.v4
                        fi";
                    }else{
                        return ['success' => false, 'data' => 'Verifique el dato solicitado, no es una IP.'];
                    }
                    break;
                /*************************/
                // Caso 2: Ejecución de petición HTTP asíncrona mediante wget
                case 2:
                    // Defensa SSRF: valida esquema http/https y que el host no resuelva a redes internas o reservadas
                    $DataValidations = new FunctionsDataValidations();
                    $validacionUrl   = $DataValidations->validarUrlExterna(trim($tarea));
                    if($validacionUrl['success'] === false){
                        return ['success' => false, 'data' => $validacionUrl['error']];
                    }
                    // Escapa el argumento de la URL para prevenir inyecciones de comandos shell
                    $urlSeguro = escapeshellarg(trim($tarea));
                    // Ejecuta wget en modo silencioso y en segundo plano (&)
                    $command = "/usr/bin/wget -N -q $urlSeguro &";
                    break;
                /*************************/
                // Caso 3: Eliminar IP de la lista negra del firewall (iptables -D)
                case 3:
                    if(filter_var(trim($tarea), FILTER_VALIDATE_IP)){
                        // Genera un script multilínea para eliminar la IP del DROP y persistir cambios
                        $command = "
                        # Elimina la IP de la lista negra (DROP todo el tráfico entrante)
                        iptables -D INPUT -s ".$tarea." -j DROP

                        # Guarda los cambios (puede variar según la distribución)
                        if command -v netfilter-persistent &> /dev/null; then
                            netfilter-persistent save
                        elif command -v iptables-save &> /dev/null; then
                            iptables-save > /etc/iptables/rules.v4
                        fi";
                    }else{
                        return ['success' => false, 'data' => 'Verifique el dato solicitado, no es una IP.'];
                    }
                    break;
            }

            // Intento de ejecución del comando construido en la terminal del sistema operativo
            try {
                // Ejecución del comando y captura de la salida estándar
                $resultado = shell_exec($command);

                // Si shell_exec retorna null, indica que hubo un error o el comando no produjo salida
                if ($resultado === null) {
                    return ['success' => false, 'data' => 'Error al ejecutar el comando. No se recibió salida.'];
                }

                // Confirmación de envío del comando al sistema
                return ['success' => true, 'data' => 'Ejecucion correcta'];
            } catch (\Throwable $th) {
                // Captura de errores fatales durante el proceso de ejecución shell
                return ['success' => false, 'data' => $th->getMessage(), 'code' => $th->getCode()];
            }

        } catch (Exception $e) {
            // Manejo de excepciones generales y sanitización de mensajes de error
            return ['success' => false, 'data' => 'Ocurrió un error:'.htmlspecialchars($e->getMessage())];
        }

    }


}
