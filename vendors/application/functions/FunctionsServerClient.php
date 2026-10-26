<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsServerClient {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Publicos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	/**
     * Obtiene la dirección IP del cliente que se conecta al servidor.
     *
     * Analiza diversas cabeceras HTTP para identificar la IP de origen, considerando
     * casos donde el cliente está tras un proxy o balanceador de carga. Valida que
     * la IP sea válida (IPv4 o IPv6), aceptando también rangos privados o reservados
     * (necesario para entornos LAN/desarrollo donde REMOTE_ADDR es, p. ej., 127.0.0.1
     * o 192.168.x.x). Si ninguna cabecera produce una IP válida, se hace fallback a
     * $_SERVER['REMOTE_ADDR'] crudo.
     *
     * @return string|bool Retorna la IP del cliente en formato string o false si no se detecta ninguna IP válida.
	 *
	 * @example
	 * ```php
	 * $ServerClient->getClientIp();
	 * ```
	 *
     */
    public function getClientIp(): string | bool {

        /********************** Si todo esta ok **********************/
        // Listado de cabeceras estándar y personalizadas donde se suele alojar la IP
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
                // Procesa las cabeceras que pueden contener múltiples IPs (separadas por comas)
                foreach (explode(',', $_SERVER[$header]) as $ip) {
                    $ip = trim($ip);
                    // Aplica validación de formato (IPv4 o IPv6), aceptando también rangos privados/reservados
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        return $ip;
                    }
                }
            }
        }

        /********************** Fallback a REMOTE_ADDR **********************/
        // Si ninguna cabecera produjo una IP válida, se intenta con REMOTE_ADDR crudo
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = trim($_SERVER['REMOTE_ADDR']);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        /********************** Retorno datos  **********************/
        // No se encontró una IP válida en ninguna de las cabeceras ni en REMOTE_ADDR
        return false;

    }

	/******************************************************************************/
	/**
     * Versión alternativa para la obtención de IP con soporte para cabeceras específicas.
     *
     * Permite forzar la lectura de una cabecera particular o realizar un barrido
     * exhaustivo por claves comunes de clusters y proxies, validando formato IPv4
     * (acepta también rangos privados/reservados, coherente con getClientIp).
     *
     * @param string|null $headerContainingIPAddress Nombre de la cabecera específica a consultar.
     *
     * @return string|bool La IP detectada o false en caso de error.
	 *
	 * @example
	 * ```php
	 * $ServerClient->getClientIpAlternative();
	 * ```
	 *
     */
    public function getClientIpAlternative($headerContainingIPAddress = null): string | bool {

        /********************** Si todo esta ok **********************/
        // Si se especifica una cabecera manualmente, se prioriza su lectura directa
        if (!empty($headerContainingIPAddress)) {
            return isset($_SERVER[$headerContainingIPAddress]) ? trim($_SERVER[$headerContainingIPAddress]) : false;
        }

        // Diccionario extendido de claves de servidor para detección de IP
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
            // Fragmenta y valida cada segmento de la cabecera
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                // Validación de formato IPv4 (acepta también rangos privados/reservados)
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
                    return $ip;
                }
            }
        }

        /********************** Retorno datos  **********************/
        return false;

    }

	/******************************************************************************/
	/**
     * Identifica el navegador web utilizado por el cliente.
     *
     * Examina la cadena HTTP_USER_AGENT para encontrar coincidencias con una lista
     * extensa de navegadores conocidos, priorizando los más específicos.
     *
     * @return string Nombre del navegador detectado o un mensaje de error si no se identifica.
	 *
	 * @example
	 * ```php
	 * $ServerClient->getBrowser();
	 * ```
	 *
     */
    public function getBrowser(): string {

        /********************** Si todo esta ok **********************/
        // Validación de existencia de la cadena User Agent en la petición
        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            return 'No hemos podido detectar su navegador';
        }

        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        // Diccionario de firmas de navegadores ordenado por especificidad
        $navegadores = [
            'Edg/'            => 'Microsoft Edge',         // Edge basado en Chromium
            'Edge'            => 'Microsoft Edge',         // Edge heredado (Legacy)
            'OPR'             => 'Opera',
            'Opera Mini'      => 'Opera Mini',
            'Opera'           => 'Opera',
            'Vivaldi'         => 'Vivaldi',
            'Chrome'          => 'Google Chrome',
            'Chromium'        => 'Chromium',
            'Firefox'         => 'Mozilla Firefox',
            'Safari'          => 'Safari',                 // Nota: Safari debe ir después de Chrome para evitar falsos positivos
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

        // Búsqueda de la firma en la cadena de texto del User Agent
        foreach ($navegadores as $clave => $nombre) {
            if (stripos($user_agent, $clave) !== false) {
                return $nombre;
            }
        }

        /********************** Retorno datos  **********************/
        // Caso en que la firma no coincide con ningún registro conocido
        return 'No hemos podido detectar su navegador';

    }

	/******************************************************************************/
	/**
     * Determina el sistema operativo (SO) del cliente conectado.
     *
     * Analiza el User Agent para clasificar la plataforma entre Windows, Apple,
     * distribuciones de Linux, sistemas móviles y consolas de videojuegos.
     *
     * @return string Nombre del sistema operativo o 'Plataforma Desconocida'.
	 *
	 * @example
	 * ```php
	 * $ServerClient->getOperatingSystem();
	 * ```
	 *
     */
    public function getOperatingSystem(): string {

        /********************** Si todo esta ok **********************/
        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            return 'Plataforma Desconocida';
        }

        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        // Diccionario estructurado de plataformas y versiones
        $sistemas = [
            // Ecosistema Windows (NT Versions)
            'Windows NT 10.0'   => 'Windows 10',
            'Windows NT 10.1'   => 'Windows 11',
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

            // Ecosistema Apple / iOS
            'iPad'              => 'iPadOS',
            'iPhone'            => 'iOS',
            'iPod'              => 'iOS',
            'Mac OS X'          => 'macOS',
            'Macintosh'         => 'Mac OS Classic',
            'CFNetwork'         => 'macOS',

            // Distribuciones Linux / Unix
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

            // Ecosistema Android / Dispositivos Inteligentes
            'Android TV'        => 'Android TV',
            'Android'           => 'Android',
            'Wear OS'           => 'Wear OS',
            'BlackBerry'        => 'BlackBerry OS',
            'Mobile'            => 'Firefox OS',
            'KaiOS'             => 'KaiOS',
            'Tizen'             => 'Tizen OS',
            'HarmonyOS'         => 'HarmonyOS',

            // Consolas y Otros Sistemas
            'Chrome OS'         => 'Chrome OS',
            'SteamOS'           => 'SteamOS',
            'Nintendo'          => 'Nintendo',
            'Xbox'              => 'Xbox OS',
            'PlayStation'       => 'PlayStation OS',
            'OS/2'              => 'OS/2',
            'BeOS'              => 'BeOS',
        ];

        // Iteración sobre el diccionario para encontrar la coincidencia del SO
        foreach ($sistemas as $clave => $nombre) {
            if (stripos($user_agent, $clave) !== false) {
                return $nombre;
            }
        }

        /********************** Retorno datos  **********************/
        // Fallback en caso de no poder identificar la plataforma
        return 'Plataforma Desconocida';

    }

	/******************************************************************************/
    /**
     * Obtiene el idioma del cliente basado en cabeceras HTTP o acepta header.
     * * Analiza las cabeceras Accept-Language para determinar el idioma preferido.
     *
     * @return string|null Idioma detectado (ej: 'en', 'es') o null si no se detecta.
     *
     * @example
     * ```php
     * $ServerClient->getLanguage();
     * // Devuelve 'es' si el navegador acepta español
     * ```
     *
     */
    public function getLanguage(): ?string {

        /********************** Si todo esta ok **********************/
        // Prioridad de cabeceras para deteccion de idioma
        $langHeaders = [
            'HTTP_ACCEPT_LANGUAGE',
            'HTTP_ACCEPT',
        ];

        foreach ($langHeaders as $header) {
            if (!empty($_SERVER[$header])) {
                // Tomamos la primera parte antes de la coma (preferencia principal)
                $lang = trim(explode(',', $_SERVER[$header])[0]);
                // Extraemos el codigo de idioma (dos o tres letras)
                if (preg_match('/^[a-z]{2,3}(?:-[a-z]{2})?$/i', $lang, $matches)) {
                    return strtolower($matches[0]);
                }
                // Si tiene formato idioma-pais, solo devolvemos el idioma
                if (preg_match('/^[a-z]{2,3}/i', $lang, $matches)) {
                    return strtolower($matches[0]);
                }
            }
        }

        /********************** Retorno datos **********************/
        return null;

    }

    /******************************************************************************/
    /**
     * Detecta si el cliente es un robot (bot) conocido.
     * * Analiza la cadena HTTP_USER_AGENT en busca de identificadores de bots conocidos.
     * * Los bots incluyen crawlers, scrapers y agentes de automatización.
     *
     * @return bool True si se detecta un bot, false en caso contrario.
     *
     * @example
     * ```php
     * $ServerClient->esBot();
     * // Devuelve true si el User-Agent es de Googlebot, Bingbot, etc.
     * ```
     *
     */
    public function esBot(): bool {

        // Obtiene el User Agent del servidor
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Lista de identificadores de bots conocidos
        $botSignatures = [
            'Googlebot',
            'Bingbot',
            'Slurp',
            'DuckDuckBot',
            'Baidu',
            'Yandex',
            'Applebot',
            'AhaBot',
            'Exabot',
            'DotBot',
            'jahia',
            ' Gesicht',
            'rogerbot',
            'megaindex',
            'WebMeUp',
            'Webshrinker',
        ];

        // Convertimos a minúsculas para comparaci贸n insensible a mayúscuslas
        $userAgentLower = strtolower($userAgent);

        foreach ($botSignatures as $bot) {
            if (strpos($userAgentLower, strtolower($bot)) !== false) {
                return true;
            }
        }

        /********************** Retorno datos **********************/
        return false;

    }

    /******************************************************************************/
    /**
     * Detecta si el usuario está accediendo desde un dispositivo móvil.
     * * Analiza la cadena HTTP_USER_AGENT del navegador en busca de palabras clave
     * comunes de sistemas operativos y navegadores móviles.
     *
     * @return bool True si se detecta un dispositivo móvil o tablet.
	 *
	 * @example
	 * ```php
	 * $ServerClient->validarDispositivoMovil(); // Devuelve true si el User-Agent corresponde a un móvil o tablet
	 * ```
	 *
     */
    public function validarDispositivoMovil(): bool {

        // Obtiene el User Agent del servidor
        $userAgent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');

        /********************** Si todo esta ok **********************/
        // Lista de palabras clave para identificar plataformas móviles
        $movilKeywords = [
            'android', 'iphone', 'ipod', 'ipad', 'blackberry', 'windows phone',
            'opera mini', 'opera mobi', 'mobile', 'silk', 'kindle', 'webos',
            'palm', 'symbian', 'fennec', 'maemo', 'nokia', 'htc', 'samsung',
            'lg', 'motorola', 'tablet', 'playbook'
        ];

        /********************** Retorno datos  **********************/
        foreach ($movilKeywords as $keyword) {
            if (strpos($userAgent, $keyword) !== false) {
                return true;
            }
        }

        /**********************  Retorno datos  **********************/
        return false;

    }

}
