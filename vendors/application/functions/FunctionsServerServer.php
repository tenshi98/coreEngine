<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsServerServer {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	public function fechaActual(): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite obtener la fecha actual de chile
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se obtiene dato
		* 	$ServerServer->fechaActual(); //devuelve la fecha actual con formato 2024-07-01
		*
		*=================================================    Parametros   =================================================
		* @return  date
		*===================================================================================================================
		*/

		/********************** Si todo esta ok **********************/
		// Establecer la zona horaria predeterminada a usar.
		date_default_timezone_set('America/Santiago');

		/**********************  Retorno datos  **********************/
		//Devolvemos la fecha actual dandole un formato
		return date("Y-m-d");

	}

	/************************************************************************************************************/
	public function fechaActualAlternative(): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite obtener la fecha actual de chile sin los separadores
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se obtiene dato
		* 	$ServerServer->fechaActualAlternative(); //devuelve la fecha actual con formato 20240701
		*
		*=================================================    Parametros   =================================================
		* @return  string
		*===================================================================================================================
		*/

		/********************** Si todo esta ok **********************/
		// Establecer la zona horaria predeterminada a usar.
		date_default_timezone_set('America/Santiago');

		/**********************  Retorno datos  **********************/
		//Devolvemos la fecha actual dandole un formato
		return date("Ymd");

	}

	/************************************************************************************************************/
	public function horaActual(): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite obtener la hora actual de chile en formato estandar
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se obtiene dato
		* 	$ServerServer->horaActual(); //devuelve la hora actual con formato 18:28:58
		*
		*=================================================    Parametros   =================================================
		* @return  time
		*===================================================================================================================
		*/

		/********************** Si todo esta ok **********************/
		// Establecer la zona horaria predeterminada a usar.
		date_default_timezone_set('America/Santiago');

		/**********************  Retorno datos  **********************/
		//Devolvemos la hora actual dandole un formato
		return date("H:i:s");

	}

	/************************************************************************************************************/
	public function horaActualAlternative(): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite obtener la hora actual de chile utilizando guiones como separadores
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se obtiene dato
		* 	$ServerServer->horaActualAlternative(); //devuelve la hora actual con formato 18-28-58
		*
		*=================================================    Parametros   =================================================
		* @return  time
		*===================================================================================================================
		*/

		/********************** Si todo esta ok **********************/
		// Establecer la zona horaria predeterminada a usar.
		date_default_timezone_set('America/Santiago');

		/**********************  Retorno datos  **********************/
		//Devolvemos la hora actual dandole un formato
		return date("H-i-s");

	}

	/************************************************************************************************************/
	public function diaActual(): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite obtener el dia actual de chile, de 1 a 31 sin ceros
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se obtiene dato
		* 	$ServerServer->diaActual(); //devuelve 1 (para la fecha 2024-07-01)
		*
		*=================================================    Parametros   =================================================
		* @return  int
		*===================================================================================================================
		*/

		/********************** Si todo esta ok **********************/
		// Establecer la zona horaria predeterminada a usar.
		date_default_timezone_set('America/Santiago');

		/**********************  Retorno datos  **********************/
		//Devolvemos el dia actual dandole un formato
		return date("j");

	}

	/************************************************************************************************************/
	public function semanaActual(): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite obtener la semana actual de chile, de 1 a 56?
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se obtiene dato
		* 	$ServerServer->semanaActual(); //devuelve 27 (para la fecha 2024-07-01)
		*
		*=================================================    Parametros   =================================================
		* @return  int
		*===================================================================================================================
		*/

		/********************** Si todo esta ok **********************/
		// Establecer la zona horaria predeterminada a usar.
		date_default_timezone_set('America/Santiago');

		/**********************  Retorno datos  **********************/
		//Devolvemos la semana actual dandole un formato
		return date("W");

	}

	/************************************************************************************************************/
	public function mesActual(): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite obtener el mes actual de chile, de 1 a 12
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se obtiene dato
		* 	$ServerServer->mesActual(); //devuelve 7 (para la fecha 2024-07-01)
		*
		*=================================================    Parametros   =================================================
		* @return  int
		*===================================================================================================================
		*/

		/********************** Si todo esta ok **********************/
		// Establecer la zona horaria predeterminada a usar.
		date_default_timezone_set('America/Santiago');

		/**********************  Retorno datos  **********************/
		//Devolvemos el mes actual dandole un formato
		return date("n");

	}

	/************************************************************************************************************/
	public function anoActual(): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite obtener el año actual de chile
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se obtiene dato
		* 	$ServerServer->anoActual(); //devuelve 2024 (para la fecha 2024-07-01)
		*
		*=================================================    Parametros   =================================================
		* @return  int
		*===================================================================================================================
		*/

		/********************** Si todo esta ok **********************/
		// Establecer la zona horaria predeterminada a usar.
		date_default_timezone_set('America/Santiago');

		/**********************  Retorno datos  **********************/
		//Devolvemos el año actual dandole un formato
		return date("Y");

	}

	/************************************************************************************************************/
	public function tareasServer(string $tarea, int $Type): array{
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite entregar una tarea al servidor para que la ejecute de forma separada a los tiempos de ejecucion de el
		* programa desde donde se llama
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se obtiene dato
		* 	$ServerServer->tareasServer(https://www.ejemplo.com?param1=1&param2=2&param3=3);
		*
		*=================================================    Parametros   =================================================
		* @input  string    $tarea    Dirección web con lo que se tiene que ejecutar en el servidor, entregar URL completas
		*===================================================================================================================
		*/
		/********************** Si todo esta ok **********************/
		try {
			// Validar que la variable $tarea no esté vacía
			if (empty($tarea)) {
				return ['success' => false, 'data' => 'La tarea no puede estar vacía.'];
			}

			//Se evalua lo que se envio
			switch ($Type) {
				/*************************/
				//Bloqueo de la IP
				case 1:
					if(filter_var(trim($tarea), FILTER_VALIDATE_IP)){
						// Construir el comando de forma segura
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
				//Si es una URL, se ejecuta mediante wget
				case 2:
					if(filter_var(trim($tarea), FILTER_VALIDATE_URL)){
						// Escapar el argumento para evitar inyecciones
						$urlSeguro = escapeshellarg($tarea);
						// Construir el comando de forma segura
						$command = "/usr/bin/wget -N -q $urlSeguro &";
					}else{
						return ['success' => false, 'data' => 'Verifique el dato solicitado, no es una URL.'];
					}
					break;
			}

			//Se ejecuta comando a la terminal del servidor
			try {
				// Ejecutar el comando
				$resultado = shell_exec($command);

				// Verificar si hubo algún problema con la ejecución
				if ($resultado === null) {
					return ['success' => false, 'data' => 'Error al ejecutar el comando. No se recibió salida.'];
				}

				//Ejecucion correcta
				return ['success' => true, 'data' => 'Ejecucion correcta'];
				// Opcional: puedes registrar el resultado si lo necesitas
				// file_put_contents('log.txt', $resultado, FILE_APPEND);
			} catch (\Throwable $th) {
				return ['success' => false, 'data' => $th->getMessage(), 'code' => $th->getCode()];
			}

		} catch (Exception $e) {
			// Capturar y mostrar el mensaje de error
			return ['success' => false, 'data' => 'Ocurrió un error:'.htmlspecialchars($e->getMessage())];
		}

	}

	/************************************************************************************************************/
	public function indicesServer(): object{
		/*
		*=================================================     Detalles    =================================================
		*
		* Devuelve toda la info del servidor
		*
		*=================================================    Modo de uso  =================================================
		* 	//se imprime input
		* 	$ServerServer->indicesServer()->PHP_SELF;
		* 	$ServerServer->indicesServer()->GATEWAY_INTERFACE;
		* 	$ServerServer->indicesServer()->SERVER_NAME;
		* 	$ServerServer->indicesServer()->SERVER_PROTOCOL;
		* 	$ServerServer->indicesServer()->REQUEST_TIME;
		*
		*=================================================    Parametros   =================================================
		* @return  object
		*===================================================================================================================
		*/

		/********************** Si todo esta ok **********************/
		try {
			// Lista de claves que queremos extraer de $_SERVER
			$claves = [
				'PHP_SELF', 'argv', 'argc', 'GATEWAY_INTERFACE', 'SERVER_ADDR', 'SERVER_NAME',
				'SERVER_SOFTWARE', 'SERVER_PROTOCOL', 'REQUEST_METHOD', 'REQUEST_TIME',
				'REQUEST_TIME_FLOAT', 'QUERY_STRING', 'DOCUMENT_ROOT', 'HTTP_ACCEPT',
				'HTTP_ACCEPT_CHARSET', 'HTTP_ACCEPT_ENCODING', 'HTTP_ACCEPT_LANGUAGE',
				'HTTP_CONNECTION', 'HTTP_HOST', 'HTTP_REFERER', 'HTTP_USER_AGENT', 'HTTPS',
				'REMOTE_ADDR', 'REMOTE_HOST', 'REMOTE_PORT', 'REMOTE_USER', 'REDIRECT_REMOTE_USER',
				'SCRIPT_FILENAME', 'SERVER_ADMIN', 'SERVER_PORT', 'SERVER_SIGNATURE',
				'PATH_TRANSLATED', 'SCRIPT_NAME', 'REQUEST_URI', 'PHP_AUTH_DIGEST',
				'PHP_AUTH_USER', 'PHP_AUTH_PW', 'AUTH_TYPE', 'PATH_INFO', 'ORIG_PATH_INFO'
			];

			$datos = [];

			foreach ($claves as $clave) {
				// Verifica si la clave existe en $_SERVER
				$datos[$clave] = array_key_exists($clave, $_SERVER) ? $_SERVER[$clave] : null;
			}

			/**********************  Retorno datos  **********************/
			return (object) $datos;

		} catch (Throwable $e) {
			error_log("Error al obtener datos del servidor: " . $e->getMessage());
			return (object) ['error' => 'No se pudieron obtener los datos del servidor.'];
		}

	}

	/************************************************************************************************************/
	public function removeDirectoryRecursive($src): array {
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite eliminar una carpeta en especifico dentro del servidor
		*
		*=================================================    Modo de uso  =================================================
		* 	//elimino la carpeta en caso de existir
		* 	$structure = '/client_folder/client/tutor'; //carpeta
		* 	$ServerServer->removeDirectoryRecursive($structure);
		*
		*=================================================    Parametros   =================================================
		* @input  string  $src         ruta de la carpeta
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!isset($src) || $src==''){ return ['success' => false, 'error' => 'No ha ingresado la ruta de la carpeta'];}

		/********************** Si todo esta ok **********************/
		//pruebo si se puede hacer
		try {
			//se abre carpeta
			$dir = opendir($src);
			//se recorren los archivos al interior y se borran
			while(false !== ( $file = readdir($dir)) ) {
				if (( $file != '.' ) && ( $file != '..' )) {
					$full = $src . '/' . $file;
					if ( is_dir($full) ) {
						//se agrega recursividad
						$this->removeDirectoryRecursive($full);
					}
					else {
						unlink($full);
					}
				}
			}
			//se cierra carpeta
			closedir($dir);
			//se borra carpeta
			rmdir($src);
			//Agrego respuesta
			return ['success' => true, 'data' => 'Archivos borrados'];
		} catch (Exception $e) {
			return ['success' => false, 'error' => 'Ha ocurrido un error al borrar archivos'];
		}
	}

}
