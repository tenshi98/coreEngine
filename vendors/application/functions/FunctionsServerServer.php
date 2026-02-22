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
	public function fechaActual(): string {
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
	public function fechaActualAlternative(): string {
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
	public function horaActual(): string {
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
	public function horaActualAlternative(): string {
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
	public function diaActual(): string {
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
	public function semanaActual(): string {
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
	public function mesActual(): string {
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
	public function anoActual(): string {
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
	public function tareasServer(string $tarea, int $Type): array {
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
				/*************************/
				//otro comando
				case 3:
					//otro comando
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
	public function indicesServer(): object {
		/*
		*=================================================     Detalles    =================================================
		*
		* Devuelve toda la info del servidor
		*
		*=================================================    Modo de uso  =================================================
		*
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
		*
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

    /******************************************************************************/
    public function writeEnvFile(string $path, array $variables, bool $overwrite = false): array {
        /*
		*=================================================     Detalles    =================================================
		*
		* Crea o sobrescribe un archivo .env en la ruta especificada.
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//ejecucion
		* 	$envPath = __DIR__ . '/.env';
        *
        * 	$variables = [
        * 	    'APP_NAME'    => 'Mi Aplicacion',
        * 	    'APP_ENV'     => 'production',
        * 	    'DB_HOST'     => 'localhost',
        * 	    'DB_DATABASE' => 'mi_base',
        * 	    'DB_USERNAME' => 'root',
        * 	    'DB_PASSWORD' => '123456'
        * 	];
        *
        * 	$result = writeEnvFile($envPath, $variables, true);
        *
        * 	if ($result['success']) {
        * 	    echo $result['message'];
        * 	} else {
        * 	    echo "Error: " . $result['message'];
        * 	}
		*
		*=================================================    Parametros   =================================================
        * @input string $path       Ruta completa donde se creará el archivo (ej: /var/www/html/.env)
        * @input array  $variables  Array asociativo con las variables ['KEY' => 'VALUE']
        * @input bool   $overwrite  Permite sobrescribir si ya existe (default: false)
        * @return array Retorna ['success' => bool, 'message' => string]
		*===================================================================================================================
		*/

        try {
            // Validar que el directorio exista
            $directory = dirname($path);
            if (!is_dir($directory)) {
                return [
                    'success' => false,
                    'message' => "El directorio no existe: {$directory}"
                ];
            }

            // Verificar si el archivo existe
            if (file_exists($path) && !$overwrite) {
                return [
                    'success' => false,
                    'message' => "El archivo ya existe y overwrite está deshabilitado."
                ];
            }

            // Construir contenido del .env
            $content = '';
            foreach ($variables as $key => $value) {
                // Sanitizar clave (solo letras, números y guión bajo)
                $cleanKey = preg_replace('/[^A-Z0-9_]/i', '', $key);

                // Escapar valores con espacios
                if (preg_match('/\s/', $value)) {
                    $value = '"' . addslashes($value) . '"';
                }

                $content .= "{$cleanKey}={$value}" . PHP_EOL;
            }

            // Escribir archivo
            if (file_put_contents($path, $content) === false) {
                return [
                    'success' => false,
                    'message' => "No se pudo escribir el archivo."
                ];
            }

            return [
                'success' => true,
                'message' => "Archivo .env creado correctamente."
            ];

        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => "Error: " . $e->getMessage()
            ];
        }
    }

    /******************************************************************************/
    public function writeConfigClassFile(string $path, array $variables, string $constName = 'MySQL_1'): array {
        /*
		*=================================================     Detalles    =================================================
		*
		* Crea o sobrescribe un archivo .php en la ruta especificada.
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//ejecucion
		* 	$phpPath = __DIR__ . '/config.php';
        *
        * 	$variables = [
        * 	    'APP_NAME'    => 'Mi Aplicacion',
        * 	    'APP_ENV'     => 'production',
        * 	    'DB_HOST'     => 'localhost',
        * 	    'DB_DATABASE' => 'mi_base',
        * 	    'DB_USERNAME' => 'root',
        * 	    'DB_PASSWORD' => '123456'
        * 	];
        *
        * 	$result = writeConfigClassFile($phpPath, $variables, 'MySQL_1');
        *
        * 	if ($result['success']) {
        * 	    echo $result['message'];
        * 	} else {
        * 	    echo "Error: " . $result['message'];
        * 	}
		*
		*=================================================    Parametros   =================================================
        * @input string $path       Ruta completa donde se creará el archivo (ej: /var/www/html/.env)
        * @input array  $variables  Array asociativo con las variables ['KEY' => 'VALUE']
        * @input string $constName  Permite el ingreso del nombre de la constante
        * @return array Retorna ['success' => bool, 'message' => string]
		*===================================================================================================================
		*/

		try {

			// Devuelve la ruta del directorio padre de una ruta dada.
			$directory = dirname($path);

			// Validar que el directorio exista
			if (!is_dir($directory)) {
				return [
					'success' => false,
					'message' => "El directorio no existe: {$directory}"
				];
			}

			// Verificar si el archivo existe
			if (pathinfo($path, PATHINFO_EXTENSION) !== 'php') {
				return [
					'success' => false,
					'message' => "El archivo debe tener extensión .php"
				];
			}

			// Sanitizar nombre constante
			$constName = preg_replace('/[^A-Z0-9_]/', '', strtoupper($constName));

			// Construir bloque de constante
			$constBlock  = "    /*****************************************************/\n";
			$constBlock .= "    //Variables para MySQL\n";
			$constBlock .= "    const {$constName} = [\n";

			foreach ($variables as $key => $value) {

				$cleanKey = preg_replace('/[^A-Z0-9_]/i', '', strtoupper($key));

				// Detectar tipo de dato
				if (is_int($value) || is_float($value)) {
					$exportedValue = $value; // sin comillas
				} elseif (is_bool($value)) {
					$exportedValue = $value ? 'true' : 'false';
				} elseif (is_null($value)) {
					$exportedValue = 'null';
				} elseif (is_numeric($value) && !preg_match('/^0\d+$/', $value)) {
					// Si viene como string numérico (ej: "3306") lo convertimos a número
					$exportedValue = $value + 0;
				} else {
					// Escapar string correctamente
					$exportedValue = "'" . addslashes($value) . "'";
				}

				$constBlock .= "        '{$cleanKey}' => {$exportedValue},\n";
			}


			$constBlock .= "    ];\n\n";

			// ---------------------------------------------------------------------
			// Si el archivo no existe → crear estructura completa
			// ---------------------------------------------------------------------
			if (!file_exists($path)) {

				$content  = "<?php\n";
				$content .= "/*******************************************************************************************************************/\n";
				$content .= "/*                                              Se define la clase                                                 */\n";
				$content .= "/*******************************************************************************************************************/\n";
				$content .= "class ConfigData{\n";
				$content .= $constBlock;
				$content .= "}\n";

				file_put_contents($path, $content);

				return [
					'success' => true,
					'message' => "Archivo creado y constante {$constName} agregada."
				];
			}

			// ---------------------------------------------------------------------
			// Si existe → leer contenido
			// ---------------------------------------------------------------------
			$existingContent = file_get_contents($path);

			// Verificar si la clase existe
			if (!preg_match('/class\s+ConfigData/i', $existingContent)) {

				$existingContent .= "\n\nclass ConfigData{\n";
				$existingContent .= $constBlock;
				$existingContent .= "}\n";

				file_put_contents($path, $existingContent);

				return [
					'success' => true,
					'message' => "Clase creada y constante {$constName} agregada."
				];
			}

			// Verificar si la constante ya existe
			if (preg_match('/const\s+' . $constName . '\s*=/i', $existingContent)) {
				return [
					'success' => false,
					'message' => "La constante {$constName} ya existe."
				];
			}

			// Insertar constante antes del cierre de la clase
			$updatedContent = preg_replace(
				'/}\s*$/',
				$constBlock . "}",
				$existingContent
			);

			if ($updatedContent === null) {
				return [
					'success' => false,
					'message' => "Error al actualizar el archivo."
				];
			}

			file_put_contents($path, $updatedContent);

			return [
				'success' => true,
				'message' => "Constante {$constName} agregada correctamente."
			];

		} catch (Throwable $e) {
			return [
				'success' => false,
				'message' => "Error: " . $e->getMessage()
			];
		}
	}

	/************************************************************************************************************/
	public function getParentPath(string $path, int $levels = 1): string {
		/*
		*=================================================     Detalles    =================================================
		*
		* Sube N niveles desde una ruta dada.
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//ejecucion
		* 	$envPath  = __DIR__;
		* 	$rootPath = getParentPath($envPath, 4);
		* 	echo $rootPath;
		*
		*=================================================    Parametros   =================================================
		* @input string $path    Ruta base (ej: __DIR__)
		* @input int    $levels  Cantidad de niveles a subir
		* @return string Ruta resultante normalizada
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!isset($path) || $path==''){ return 'No ha ingresado la ruta del directorio';}

		/********************** Si todo esta ok **********************/
		$result = rtrim($path, DIRECTORY_SEPARATOR);

		for ($i = 0; $i < $levels; $i++) {
			$result = dirname($result);
		}

		return $result;
	}

	/************************************************************************************************************/
	public function isWritableDirectory(string $directory, int $permission = 0755): array {
		/*
		*=================================================     Detalles    =================================================
		*
		* Verifica si una carpeta permite crear archivos dentro.
		* Si no lo permite, intenta corregir los permisos.
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//ejecucion
		* 	$path = '/var/www/html/coreEngine/admin/storage';
		*
		* 	$result = ensureWritableDirectory($path, 0775);
		*
		* 	if ($result['success']) {
		* 		echo $result['message'];
		* 	} else {
		* 		echo "Error: " . $result['message'];
		* 	}
		*
		*=================================================    Parametros   =================================================
		* @input string $directory   Ruta absoluta de la carpeta
		* @input int    $permission  Permisos a aplicar si no tiene escritura (default: 0755)
		* @return array ['success' => bool, 'message' => string]
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!isset($directory) || $directory==''){ return ['success' => false,'message' => "No ha ingresado la ruta del directorio."];}

		/********************** Si todo esta ok **********************/
		try {

			// Verificar existencia
			if (!is_dir($directory)) {
				return [
					'success' => false,
					'message' => "El directorio no existe."
				];
			}

			// Verificar si ya es escribible
			if (is_writable($directory)) {
				return [
					'success' => true,
					'message' => "El directorio ya tiene permisos de escritura."
				];
			}

			// Intentar cambiar permisos
			if (!chmod($directory, $permission)) {
				return [
					'success' => false,
					'message' => "No se pudieron cambiar los permisos del directorio."
				];
			}

			// Verificar nuevamente
			if (!is_writable($directory)) {
				return [
					'success' => false,
					'message' => "Los permisos fueron cambiados, pero aún no es escribible."
				];
			}

			return [
				'success' => true,
				'message' => "Permisos actualizados correctamente."
			];

		} catch (Throwable $e) {
			return [
				'success' => false,
				'message' => "Error: " . $e->getMessage()
			];
		}
	}

}
