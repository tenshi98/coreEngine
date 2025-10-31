<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsServerClient {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	public function getClientIp(): string | bool {
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite obtener la ip del cliente que se conecta
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se obtiene dato
		* 	$ServerClient->getClientIp();
		*
		*=================================================    Parametros   =================================================
		* @return  string
		*===================================================================================================================
		*/

		/********************** Si todo esta ok **********************/
		$headers = [
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR'
		];

		foreach ($headers as $header) {
			if (!empty($_SERVER[$header])) {
				// Algunas cabeceras pueden tener múltiples IPs separadas por coma
				foreach (explode(',', $_SERVER[$header]) as $ip) {
					$ip = trim($ip);
					// Validar IP y excluir rangos privados/reservados
					if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE)) {
						return $ip;
					}
				}
			}
		}

		/**********************  Retorno datos  **********************/
		return false; // No se encontró una IP pública válida

	}

	/************************************************************************************************************/
	public function getClientIpAlternative($headerContainingIPAddress = null): string | bool{
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite obtener la ip del cliente que se conecta
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se obtiene dato
		* 	$ServerClient->getClientIpAlternative();
		*
		*=================================================    Parametros   =================================================
		* @return  string
		*===================================================================================================================
		*/

		/********************** Si todo esta ok **********************/
		if (!empty($headerContainingIPAddress)) {
			return isset($_SERVER[$headerContainingIPAddress]) ? trim($_SERVER[$headerContainingIPAddress]) : false;
		}

		$knowIPkeys = [
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		];

		foreach ($knowIPkeys as $key) {
			if (array_key_exists($key, $_SERVER) !== true) {
				continue;
			}
			foreach (explode(',', $_SERVER[$key]) as $ip) {
				$ip = trim($ip);
				if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
					return $ip;
				}
			}
		}

		/**********************  Retorno datos  **********************/
		return false;

	}

	/************************************************************************************************************/
	public function getBrowser(): string {
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite obtener el navegador del cliente que se conecta
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se obtiene dato
		* 	$ServerClient->getBrowser();
		*
		*=================================================    Parametros   =================================================
		* @return  string
		*===================================================================================================================
		*/

		/********************** Si todo esta ok **********************/
		if (empty($_SERVER['HTTP_USER_AGENT'])) {
			return 'No hemos podido detectar su navegador';
		}

		$user_agent = $_SERVER['HTTP_USER_AGENT'];

		// Lista de navegadores (ordenados estratégicamente: más específicos primero)
		$navegadores = [
			'Edg/'            => 'Microsoft Edge',         // Edge Chromium
			'Edge'            => 'Microsoft Edge',         // Edge clásico
			'OPR'             => 'Opera',
			'Opera Mini'      => 'Opera Mini',
			'Opera'           => 'Opera',
			'Vivaldi'         => 'Vivaldi',
			'Chrome'          => 'Google Chrome',
			'Chromium'        => 'Chromium',
			'Firefox'         => 'Mozilla Firefox',
			'Safari'          => 'Safari',                 // Colocar después de Chrome/Opera
			'Trident'         => 'Internet Explorer',
			'MSIE'            => 'Internet Explorer',
			'UCBrowser'       => 'UC Browser',
			'SamsungBrowser'  => 'Samsung Internet',
			'Brave'           => 'Brave',
			'YaBrowser'       => 'Yandex Browser',
			'DuckDuckGo'      => 'DuckDuckGo Privacy Browser',
			'Maxthon'         => 'Maxthon',
			'SeaMonkey'       => 'SeaMonkey',
			'Arora'           => 'Arora',
			'Avant Browser'   => 'Avant Browser',
			'Beamrise'        => 'Beamrise',
			'Epiphany'        => 'Epiphany',
			'Iceweasel'       => 'Iceweasel',
			'Galeon'          => 'Galeon',
			'iTunes'          => 'iTunes',
			'Konqueror'       => 'Konqueror',
			'Dillo'           => 'Dillo',
			'Netscape'        => 'Netscape',
			'Midori'          => 'Midori',
			'ELinks'          => 'ELinks',
			'Links'           => 'Links',
			'Lynx'            => 'Lynx',
			'w3m'             => 'w3m'
		];

		foreach ($navegadores as $clave => $nombre) {
			if (stripos($user_agent, $clave) !== false) {
				return $nombre;
			}
		}

		/**********************  Retorno datos  **********************/
		return 'No hemos podido detectar su navegador';

	}

	/************************************************************************************************************/
	public function getOperatingSystem(): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite obtener el sistema operativo del cliente que se conecta
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se obtiene dato
		* 	$ServerClient->getOperatingSystem();
		*
		*=================================================    Parametros   =================================================
		* @return  string
		*===================================================================================================================
		*/

		/********************** Si todo esta ok **********************/
		if (empty($_SERVER['HTTP_USER_AGENT'])) {
			return 'Plataforma Desconocida';
		}

		$user_agent = $_SERVER['HTTP_USER_AGENT'];

		// Lista de sistemas operativos (ordenada estratégicamente)
		$sistemas = [
			// Windows
			'Windows NT 10.0'   => 'Windows 10',
			'Windows NT 10.1'   => 'Windows 11',  // Algunas cadenas de Windows 11
			'Windows NT 6.3'    => 'Windows 8.1',
			'Windows NT 6.2'    => 'Windows 8',
			'Windows NT 6.1'    => 'Windows 7',
			'Windows NT 6.0'    => 'Windows Vista',
			'Windows NT 5.2'    => 'Windows Server 2003',
			'Windows NT 5.1'    => 'Windows XP',
			'Windows NT 5.0'    => 'Windows 2000',
			'Windows ME'        => 'Windows ME',
			'Win98'             => 'Windows 98',
			'Win95'             => 'Windows 95',
			'WinNT4.0'          => 'Windows NT 4.0',
			'Windows Phone'     => 'Windows Phone',
			'Windows'           => 'Windows',

			// Apple / iOS
			'iPad'              => 'iPadOS',
			'iPhone'            => 'iOS',
			'iPod'              => 'iOS',
			'Mac OS X'          => 'macOS',
			'Macintosh'         => 'Mac OS Classic',
			'CFNetwork'         => 'macOS',

			// Linux / Unix
			'Ubuntu'            => 'Ubuntu',
			'Debian'            => 'Debian',
			'Linux Mint'        => 'Linux Mint',
			'Kali'              => 'Kali Linux',
			'Arch Linux'        => 'Arch Linux',
			'Manjaro'           => 'Manjaro',
			'Fedora'            => 'Fedora',
			'Red Hat'           => 'Red Hat',
			'CentOS'            => 'CentOS',
			'Slackware'         => 'Slackware',
			'Gentoo'            => 'Gentoo',
			'Elementary OS'     => 'Elementary OS',
			'Kubuntu'           => 'Kubuntu',
			'Xubuntu'           => 'Xubuntu',
			'Linux'             => 'Linux',
			'FreeBSD'           => 'FreeBSD',
			'OpenBSD'           => 'OpenBSD',
			'NetBSD'            => 'NetBSD',
			'SunOS'             => 'Solaris',

			// Android / Otros móviles
			'Android TV'        => 'Android TV',
			'Android'           => 'Android',
			'Wear OS'           => 'Wear OS',
			'BlackBerry'        => 'BlackBerry OS',
			'Mobile'            => 'Firefox OS',
			'KaiOS'             => 'KaiOS',
			'Tizen'             => 'Tizen OS',
			'HarmonyOS'         => 'HarmonyOS',

			// Otros
			'Chrome OS'         => 'Chrome OS',
			'SteamOS'           => 'SteamOS',
			'Nintendo'          => 'Nintendo',
			'Xbox'              => 'Xbox OS',
			'PlayStation'       => 'PlayStation OS',
			'OS/2'              => 'OS/2',
			'BeOS'              => 'BeOS',
		];

		foreach ($sistemas as $clave => $nombre) {
			if (stripos($user_agent, $clave) !== false) {
				return $nombre;
			}
		}

		/**********************  Retorno datos  **********************/
		return 'Plataforma Desconocida';

	}

}
