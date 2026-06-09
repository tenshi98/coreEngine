<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class FunctionsDataText {

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
     * Trunca un texto a una longitud específica y añade puntos suspensivos si excede el límite.
     * * Utiliza la extensión mbstring si está disponible para garantizar la compatibilidad
     * con caracteres multi-byte (UTF-8).
     *
     * @param string $texto El contenido de texto original.
     * @param int $cuantos Cantidad máxima de caracteres a conservar antes del recorte.
     *
     * @return string Texto procesado con o sin puntos suspensivos según la longitud.
	 *
	 * @example
	 * ```php
	 * $DataText->cortar('Lorem ipsum dolor sit amet, consectetur', 10); //Devuelve 'Lorem ipsu...'
	 * ```
	 *
     */
    public function cortar($texto, $cuantos): string {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateEmpty($texto, 'texto');
		$dataVal_2 = $this->_validateInteger($cuantos, 'cuantos');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }
		if ($dataVal_2 !== true) { return $dataVal_2; }

        /********************** Proceso de Recorte **********************/
        // Operación compatible con caracteres especiales y acentos
        if (extension_loaded('mbstring')) {
            return (mb_strlen($texto) <= $cuantos) ? $texto : mb_substr($texto, 0, $cuantos, 'UTF-8').'...';
        } else {
            // Método alternativo en caso de ausencia de la extensión mbstring
            return (strlen($texto) <= $cuantos) ? $texto : substr($texto, 0, $cuantos).'...';
        }

    }

	/************************************************************************************************************/
	/**
     * Extrae la parte numérica de un RUT chileno, removiendo el dígito verificador y separadores.
     * * Requiere que el RUT sea válido bajo los estándares chilenos (XXXXXXXX-X).
     *
     * @param string $Rut Cadena que representa el RUT (puede incluir puntos y guion).
     *
     * @return string Parte numérica del RUT sin puntos ni el carácter posterior al guion.
	 *
	 * @example
	 * ```php
	 * $DataText->eliminarVerificadorRut('10294658-9'); //Devuelve 10294658
	 * ```
	 *
     */
    public function eliminarVerificadorRut($Rut): string {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateEmpty($Rut, 'Rut');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }
        // Validación lógica del formato RUT antes de proceder
        if (!$this->DataValidations->validarRut($Rut)){  return 'El dato ingresado no es un Rut';}

        /********************** Limpieza de Formato **********************/
        // Remueve puntos para normalizar la cadena a números y guion
        $Rut_limpio = str_replace('.', '', $Rut);

        // Localiza la posición del guion verificador
        $lastDashPos = strrpos($Rut_limpio, '-');

        /********************** Retorno de Datos **********************/
        // Si existe guion, retorna todo lo que esté a su izquierda; de lo contrario, el RUT limpio
        return ($lastDashPos !== false) ? substr($Rut_limpio, 0, $lastDashPos) : $Rut_limpio;

    }

	/************************************************************************************************************/
	/**
	 * Limpia un string de forma robusta y segura.
	 *
	 * ✔ Soporta UTF-8 (acentos, ñ, etc.)
	 * ✔ Elimina saltos de línea reales y literales (\n, \r)
	 * ✔ Elimina etiquetas HTML/PHP
	 * ✔ Elimina caracteres invisibles/control
	 * ✔ Normaliza espacios múltiples
	 * ✔ Mantiene solo letras, números y espacios (opcionalmente puntuación)
	 *
	 * @param string $texto
	 * @param bool $keepPunctuation Permite mantener puntuación básica (. , - _)
	 *
	 * @return string
	 *
	 * @example
	 * ```php
	 * $DataText->limpiarString('Lorem ipsum\n dolor sit amet\n, consectetur\r'); //Devuelve 'Lorem ipsum dolor sit amet consectetur'
	 * ```
	 *
	 */
    public function limpiarString($texto, $keepPunctuation = false): string {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateEmpty($texto, 'texto');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

		/********************** Normalización base **********************/
		// Elimina secuencias literales "\n" y "\r"
		$texto = str_replace(['\\n', '\\r'], ' ', $texto);

		// Reemplaza saltos reales por espacio
		$texto = str_replace(["\n", "\r", "\t"], ' ', $texto);

		/********************** Limpieza HTML **********************/
		$texto = strip_tags($texto);

		/********************** Elimina caracteres invisibles **********************/
		// Control chars Unicode + ASCII
		$texto = preg_replace('/[\x00-\x1F\x7F]/u', '', $texto);

		/********************** Limpieza principal **********************/
		if ($keepPunctuation) {
			// Mantiene letras, números, espacios y puntuación básica
			$texto = preg_replace('/[^\p{L}\p{N}\s\.\,\-\_]/u', ' ', $texto);
		} else {
			// Solo letras, números y espacios (UTF-8)
			$texto = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $texto);
		}

		/********************** Normaliza espacios **********************/
		// Reemplaza múltiples espacios por uno solo
		$texto = preg_replace('/\s+/', ' ', $texto);

		/********************** Retorno **********************/
		return trim($texto);

    }

	/************************************************************************************************************/
	/**
     * Sustituye todos los espacios en blanco por guiones bajos en una cadena.
     * * Comúnmente utilizado para generar nombres de archivos o slugs a partir de títulos.
     *
     * @param string $texto Oración o palabra con espacios.
     *
     * @return string Texto con guiones bajos en lugar de espacios.
	 *
	 * @example
	 * ```php
	 * $DataText->reemplazarEspaciosxGuion('Lorem ipsum dolor sit amet, consectetur'); //Devuelve 'Lorem_ipsum_dolor_sit_amet,_consectetur'
	 * ```
	 *
     */
    public function reemplazarEspaciosxGuion($texto): string {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateEmpty($texto, 'texto');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

        /********************** Retorno de Datos **********************/
		// Reemplazo de datos
        return str_replace(' ', '_', $texto);

    }

	/************************************************************************************************************/
	/**
     * Convierte caracteres especiales en sus entidades HTML correspondientes para evitar XSS.
     * * Transforma símbolos como <, >, &, ", ' en códigos seguros para el navegador.
     *
     * @param string $texto Texto con caracteres especiales o código HTML.
     *
     * @return string Texto sanitizado con entidades HTML en formato UTF-8.
	 *
	 * @example
	 * ```php
	 * $DataText->sanitizarTexto('Lorem ipsum dolor sit amet, consectetur'); //Devuelve 'Lorem ipsum dolor sit amet, consectetur'
	 * ```
	 *
     */
    public function sanitizarTexto($texto): string {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateEmpty($texto, 'texto');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

        /********************** Proceso de Seguridad **********************/
        // ENT_QUOTES asegura que tanto comillas simples como dobles sean convertidas
        return htmlentities($texto, ENT_QUOTES, 'UTF-8');

    }

	/************************************************************************************************************/
	/**
     * Revierte la sanitización, convirtiendo entidades HTML de vuelta a caracteres especiales.
     * * Proceso inverso a sanitizarTexto, útil para editar contenido previamente guardado.
     *
     * @param string $texto Texto con entidades HTML (ej: &quot;).
     *
     * @return string Texto con los caracteres originales recuperados.
	 *
	 * @example
	 * ```php
	 * $DataText->desanitizarTexto('Lorem ipsum dolor sit amet, consectetur'); //Devuelve 'Lorem ipsum dolor sit amet, consectetur'
	 * ```
	 *
     */
    public function desanitizarTexto($texto): string {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateEmpty($texto, 'texto');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

        /********************** Proceso de Reversión **********************/
        // Traduce entidades como &amp; de nuevo a &
        return html_entity_decode($texto, ENT_QUOTES, 'UTF-8');

    }

	/************************************************************************************************************/
	/**
     * Realiza una limpieza profunda del texto, eliminando etiquetas HTML, saltos de línea
     * y codificando comillas para evitar conflictos en el almacenamiento o visualización.
     *
     * @param string $texto Texto original a estandarizar.
     *
     * @return string Texto sin etiquetas, sin saltos de línea y con comillas codificadas (%27 y %22).
	 *
	 * @example
	 * ```php
	 * $DataText->limpiezaTexto("bla"bla'bla"); //Devuelve 'bla%27bla%27bla'
	 * ```
	 *
     */
    public function limpiezaTexto($texto): string {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateEmpty($texto, 'texto');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

        /********************** Proceso de Limpieza **********************/
        // Elimina etiquetas HTML y remueve saltos de línea (\r, \n)
        $texto = preg_replace("/[\r\n]+/", '', strip_tags($texto));

        // Sustituye comillas simples (') por %27 y comillas dobles (") por %22
        $texto = str_replace(["'", '"'], ['%27', '%22'], $texto);

        // Aplica sanitización adicional (acentos y ñ) mediante método interno
        $texto = $this->sanitizarTexto($texto);

        /********************** Retorno datos **********************/
		// Retorna texto limpiado
        return $texto;

    }

	/************************************************************************************************************/
	/**
     * Normaliza una oración reemplazando caracteres especiales, acentuados o diacríticos
     * por sus equivalentes más cercanos en formato ASCII/Latín estándar.
     *
     * @param string $texto Oración con caracteres especiales o acentos.
     *
     * @return string Oración convertida a minúsculas y sin caracteres especiales (ej: À -> a).
	 *
	 * @example
	 * ```php
	 * $DataText->limpiarOracion('ÀÁÂÃÄÅÆ'); //devuelve aaaaaaae
	 * ```
	 *
     */
    public function limpiarOracion($texto): string {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateEmpty($texto, 'texto');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

        /********************** Proceso de Transliteración **********************/
        // Intenta utilizar la extensión 'intl' para una transliteración precisa y moderna
        if (extension_loaded('intl')) {
                $texto = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $texto);
        } else {
            // Lógica de respaldo (Fallback) basada en mapeo manual de caracteres
            $originales   = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿª';
            $modificadas  = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybya';

            // Conversión temporal a ISO-8859-1 para facilitar el reemplazo de caracteres de un solo byte
            $cadena       = mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
            $cadena       = strtr($cadena, mb_convert_encoding($originales, 'ISO-8859-1', 'UTF-8'), $modificadas);

            // Retorno al formato universal UTF-8 y conversión a minúsculas
            $texto        = mb_convert_encoding($cadena, 'UTF-8', 'ISO-8859-1');
            $texto        = strtolower($texto);
        }

        /********************** Retorno datos **********************/
		// Retorna texto limpiado
        return $texto;

    }

	/************************************************************************************************************/
	/**
     * Identifica y cuenta la cantidad de palabras prohibidas o censuradas presentes en un texto.
     *
     * @param string $texto Texto a analizar en busca de lenguaje ofensivo.
     *
     * @return string|int Cantidad de palabras encontradas o mensaje de error si el input es nulo.
	 *
	 * @example
	 * ```php
	 * $DataText->contarPalabrasCensuradas('Lorem ipsum dolor sit amet, fuck d'); //Devuelve 1
	 * ```
	 *
     */
    public function contarPalabrasCensuradas($texto): string | int {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateEmpty($texto, 'texto');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

        /********************** Análisis de Contenido **********************/
        // Normaliza el texto para asegurar que la comparación no ignore acentos
        $texto = $this->limpiarOracion($texto);

        // Recupera la lista negra de palabras mediante método del sistema
        $censuradas = $this->getListaPalabrasCensuradas();

        /********************** Retorno datos **********************/
        /**
         * Filtra la lista de palabras censuradas verificando si cada una existe dentro
         * del texto (usando espacios alrededor para evitar falsos positivos en sub-palabras).
         */
        return count(array_filter($censuradas, fn($w) => stripos(" $texto ", " $w ") !== false));

    }

	/************************************************************************************************************/
	/**
     * Busca palabras prohibidas en un texto y las oculta reemplazándolas por asteriscos.
     *
     * @param string $texto Texto original a filtrar.
     *
     * @return string Texto resultante con las palabras censuradas sustituidas por '****'.
	 *
	 * @example
	 * ```php
	 * $DataText->filtrarPalabrasCensuradas('Lorem ipsum dolor sit amet, fuck d'); //Devuelve 'lorem ipsum dolor sit amet, **** d'
	 * ```
	 *
     */
    public function filtrarPalabrasCensuradas($texto): string {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateEmpty($texto, 'texto');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

        /********************** Proceso de Filtrado **********************/
        // Normalización del texto para coincidencia exacta
        $texto = $this->limpiarOracion($texto);

        // Obtención de la lista de términos prohibidos
        $censuradas   = $this->getListaPalabrasCensuradas();

        /********************** Retorno datos **********************/
        // Reemplazo insensible a mayúsculas/minúsculas de todos los términos detectados
        return str_ireplace($censuradas, '****', $texto);

    }

    /************************************************************************************************************/
	/**
     * Limpia nombres de archivos o elementos de menú eliminando prefijos numéricos
     * de ordenamiento (ej: "01 - ", "2.- ").
     *
     * @param string $texto Cadena que contiene una numeración inicial seguida de un título.
     *
     * @return string Título limpio sin la numeración de orden.
	 *
	 * @example
	 * ```php
	 * $DataText->tituloMenu( '01 - titulo' ); //Devuelve 'titulo'
	 * ```
	 *
     */
    public function tituloMenu($texto): string {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal = $this->_validateEmpty($texto, 'texto');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal !== true) { return $dataVal; }

        /********************** Generación de Patrones **********************/
        $xdata = [];
        // Genera dinámicamente una lista de prefijos numéricos del 0 al 100
        for ($i = 0; $i <= 100; $i++) {
            $num_padded = str_pad($i, 2, '0', STR_PAD_LEFT);
            // Formatos: "01 - ", "01.- ", "1 - ", "1.- "
            $xdata[] = $num_padded . " - ";
            $xdata[] = $num_padded . ".- ";
            $xdata[] = $i . " - ";
            $xdata[] = $i . ".- ";
        }

        /********************** Retorno datos **********************/
        // Remueve cualquier coincidencia de la lista de prefijos generada
        return str_replace($xdata, "", $texto);

    }

	/************************************************************************************************************/
	/**
     * Localiza una palabra o subcadena específica y extrae todo el contenido que aparece tras ella.
     *
     * @param string $cadena Texto completo donde se realizará la búsqueda.
     * @param string $palabra Término de referencia a ubicar.
     *
     * @return array|string|false Array con 'posicion' y texto 'extraido', o false si no se encuentra.
	 *
	 * @example
	 * ```php
	 * $DataText->buscarPalabraYExtraer('01 - titulo', '01 - '); //Devuelve 'titulo'
	 * ```
	 *
     */
    public function buscarPalabraYExtraer($cadena, $palabra): array | string | false {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateEmpty($cadena, 'cadena');
		$dataVal_2 = $this->_validateEmpty($palabra, 'palabra');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }
		if ($dataVal_2 !== true) { return $dataVal_2; }

        /********************** Localización y Extracción **********************/
        // Encuentra el índice numérico de la primera aparición de la palabra
        $pos = strpos($cadena, $palabra);

        /********************** Retorno datos **********************/
        if ($pos === false) {
            return false;
        } else {
            // Calcula el inicio del texto posterior sumando el largo de la palabra clave
            $posSiguiente = $pos + strlen($palabra);

            // Obtiene la subcadena restante desde la posición calculada
            $extraido = substr($cadena, $posSiguiente);

			// Devolver un array con la posición y el texto extraído
            return [
                'posicion' => $pos,
                'extraido' => $extraido
            ];
        }
    }

	/************************************************************************************************************/
	/**
	 * Divide un texto en dos partes utilizando un divisor específico.
	 *
	 * Esta función separa un string en un máximo de dos segmentos usando el primer
	 * match encontrado del divisor. Si el divisor no existe en el texto, todo el
	 * contenido se asigna a la parte izquierda y la derecha queda vacía.
	 *
	 * Se aplica `trim()` a ambos resultados para eliminar espacios en blanco
	 * al inicio y al final.
	 *
	 * @param string $texto   Texto completo a dividir.
	 * @param string $divisor Cadena utilizada como separador.
	 *
	 * @return array{
	 *     izquierda: string,
	 *     derecha: string
	 * }
	 * Retorna un arreglo asociativo con:
	 *  - 'izquierda': Parte anterior al divisor.
	 *  - 'derecha'  : Parte posterior al divisor (o vacío si no existe).
	 *
	 * @example
	 * ```php
	 * $resultado = dividirTexto('clave:valor', ':');
	 *
	 * // Resultado:
	 * // [
	 * //   'izquierda' => 'clave',
	 * //   'derecha'   => 'valor'
	 * // ]
	 * ```
	 *
	 * @example
	 * ```php
	 * $resultado = dividirTexto('soloTexto', ':');
	 *
	 * // Resultado:
	 * // [
	 * //   'izquierda' => 'soloTexto',
	 * //   'derecha'   => ''
	 * // ]
	 * ```
	 *
	 * @note Solo se realiza una división (límite = 2).
	 * @note Si el divisor aparece múltiples veces, solo se considera la primera ocurrencia.
	 */
	public function dividirTexto($texto, $divisor): array {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateEmpty($texto, 'texto');
		$dataVal_2 = $this->_validateEmpty($divisor, 'divisor');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return ['izquierda' => trim($dataVal_1 ?? ''),'derecha'   => ''];}
		if ($dataVal_2 !== true) { return ['izquierda' => trim($dataVal_2 ?? ''),'derecha'   => ''];}

		/********************** Si todo esta ok **********************/
        $partes = explode($divisor, $texto, 2); // solo divide en 2 partes

		/********************** Retorno datos **********************/
        return [
			'izquierda' => trim($partes[0] ?? ''),
			'derecha'   => trim($partes[1] ?? '')
		];
	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Internos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
    /************************************************************************************************************/
	private function getListaPalabrasCensuradas(): array {
		/*
		*=================================================     Detalles    =================================================
		*
		* Base de datos con las palabras a censurar
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se verifica
		* 	getListaPalabrasCensuradas(); //Devuelve el arreglo con palabras censuradas
		*
		*=================================================    Parametros   =================================================
		* @return  array
		*===================================================================================================================
		*/

		/********************** Si todo esta ok **********************/
		$censuradas = array(
			/* Lista de palabras censuradas en ingles */
			'fuck','horny','aroused','hentai','slut','slag','boob','pussy','vagina',
			'faggot','bugger','bastard','cunt','nigga','nigger','jerk','wanker',
			'tosser','shit','rape','rapist','dick','cock','whore','bitch','asshole',
			'twat','titt','piss','intercourse','sperm','spunk','testicle','milf',
			'retard','anus','dafuq','gay','lesbian','homo','homosexual','cum',
			'prostitute','wtf','penis','ffs','pedo','hack','dumb','crap','fuck you',
			'bullshit','damn','hell','ass','badass','son of a bitch','pissed off',
			'dickhead','motherfucker','dumbass','tramp',
			/* Lista de palabras censuradas en español */
			'zorra', 'prostituta', 'cerda', 'mujer pública', 'mujer publica',
			'fulana','bruja', 'mujerzuela', 'mujer fácil', 'mujer facil', 'cortesana',
			'abanto', 'abrazafarolas', 'adufe', 'alcornoque', 'alfeñique', 'andurriasmo',
			'arrastracueros', 'artaban', 'atarre', 'baboso', 'barrabas', 'barriobajero',
			'bebecharcos', 'bellaco', 'belloto', 'berzotas', 'besugo', 'bobalicon',
			'bocabuzon', 'bocachancla', 'bocallanta', 'boquimuelle', 'borrico',
			'botarate', 'brasas', 'cabestro', 'cabezaalberca', 'cabezabuque',
			'cachibache', 'cafre', 'cagalindes', 'cagarruta', 'calambuco',
			'calamidad', 'calduo', 'calientahielos', 'calzamonas', 'cansalmas',
			'cantamañanas', 'capullo', 'caracaballo', 'caracarton', 'caraculo',
			'caraflema', 'carajaula', 'carajote', 'carapapa', 'carapijo', 'cazurro',
			'cebollino', 'cenizo', 'cenutrio', 'ceporro', 'cernicalo', 'charran',
			'chiquilicuatre', 'chirimbaina', 'chupacables', 'chupasangre', 'chupoptero',
			'cierrabares', 'cipote', 'comebolsas', 'comechapas', 'comeflores',
			'comestacas', 'cretino', 'cuerpoescombro', 'culopollo', 'descerebrado',
			'desgarracalzas', 'dondiego', 'donnadie', 'echacantos', 'ejarramantas',
			'energumeno', 'esbaratabailes', 'escolimoso', 'escornacabras', 'estulto',
			'fanfosquero', 'fantoche', 'fariseo', 'filimincias', 'foligoso', 'fulastre',
			'ganapan', 'ganapio', 'gandul', 'gañan', 'gaznapiro', 'gilipuertas',
			'giraesquinas', 'gorrino', 'gorrumino', 'guitarro', 'gurriato', 'habahela',
			'huelegateras', 'huevon', 'lamebotas', 'lamecharcos', 'lameculos', 'lameplatos',
			'lechuguino', 'lerdo', 'letrin', 'lloramigas', 'lumbreras', 'maganto',
			'majadero', 'malasangre', 'malasombra', 'malparido', 'mameluco', 'mamporrero',
			'manegueta', 'mangarran', 'mangurrian', 'mastuerzo', 'matacandiles', 'meapilas',
			'mendrugo', 'mentecato', 'mequetrefe', 'merluzo', 'metemuertos', 'metijaco',
			'mindundi', 'morlaco', 'morroestufa', 'muerdesartenes', 'orate', 'ovejo',
			'pagafantas', 'palurdo', 'pamplinas', 'panarra', 'panoli', 'papafrita',
			'papanatas', 'papirote', 'pardillo', 'parguela', 'pasmarote', 'pasmasuegras',
			'pataliebre', 'patan', 'pavitonto', 'pazguato', 'pecholata', 'pedorro',
			'peinabombillas', 'peinaovejas', 'pelagallos', 'pelagambas', 'pelagatos',
			'pelatigres', 'pelazarzas', 'pelele', 'pelma', 'percebe', 'perrocostra',
			'perroflauta', 'peterete', 'petimetre', 'picapleitos', 'pichabrava',
			'pillavispas', 'piltrafa', 'pinchauvas', 'pintamonas', 'piojoso', 'pitañoso',
			'pitofloro', 'pocasluces', 'pollopera', 'quitahipos', 'rastrapajo',
			'rebañasandias', 'revientabaules', 'rieleches', 'robaperas', 'sabandija',
			'sacamuelas', 'sanguijuela', 'sinentraero', 'sinsustancia', 'sonajas',
			'sonso', 'soplagaitas', 'soplaguindas', 'sosco', 'tagarote', 'tarado',
			'tarugo', 'tiralevitas', 'tocapelotas', 'tocho', 'tolai', 'tontaco',
			'tontucio', 'tordo', 'tragaldabas', 'tuercebotas', 'tunante', 'zamacuco',
			'zambombo', 'zampabollos', 'zamugo', 'zangano', 'zarrapastroso', 'zascandil',
			'zopenco', 'zoquete', 'zote', 'zullenco', 'zurcefrenillos', 'mamon',
			/* Lista de palabras censuradas chilenas */
			'amermelao', 'antifoca', 'apitutaa', 'apitutada', 'apitutado', 'apitutao',
			'apretao', 'atao', 'ataoso', 'bacan', 'bajon', 'bajoneao', 'bajonearse',
			'barateli', 'barsa', 'barsuo', 'bolsera', 'bolsero', 'cachai', 'cachar',
			'cacheteo', 'cacheton', 'cachetona', 'cacho', 'cagada', 'cagarla', 'cagarse',
			'cagaste', 'cahuin', 'cahuinera', 'caleta', 'charcha', 'charchas', 'chauchas',
			'chorear', 'chorearse', 'chucha', 'chucha tu madre', 'chuche tu madre',
			'chula', 'chuleteo', 'chulo', 'concha tu madre', 'conche tu madre', 'copete',
			'copucha', 'copuchar', 'copuchenta', 'copuchento', 'corremano', 'correr mano',
			'creerse la muerte', 'cresta', 'cuatico', 'cuevuo', 'cuica', 'cuico',
			'dejar la cagada', 'dejar la crema', 'dejar la escoba', 'el descueve',
			'engrupir', 'facha', 'facho', 'fleto', 'fome', 'funao', 'funar', 'hocicon',
			'hocicona', 'hociconear', 'hueada', 'hueco', 'julepe', 'lacha', 'lacho',
			'lanza', 'lanzazo', 'lesear', 'leseo', 'manoseo', 'ni ahi', 'ni cagando',
			'nica', 'no estar ni ahi', 'paco', 'paja', 'pajaron', 'pajear', 'pajearse',
			'pajera', 'pajero', 'pelotillehue', 'penca', 'pito', 'pucho', 'pulento',
			'punga', 'puta', 'valer hongo', 'volao', 'volarse', 'agüevonao', 'agüevona',
			'agüevonada', 'ahueonao', 'ahueona', 'ahueonada', 'awueonao', 'awueona',
			'awueonada', 'güevon', 'güevona', 'güeon', 'güeona', 'güevada', 'guevon',
			'guevona', 'gueon', 'guevada', 'gueona', 'huevon', 'huevona', 'huevonada',
			'hueon', 'hueona', 'hueonada', 'huevada', 'hueveo', 'wueon', 'wuevada',
			'wueveo', 'concha tu madre', 'conchetumare', 'conchatumare', 'conche tu mare',
			'concha tu mare', 'conche tumare', 'concha tumare', 'conchesumare', 'conchasumare',
			'conche su mare', 'concha su mare', 'conche sumare', 'concha sumare', 'culiao',
			'gil', 'agilao', 'agila', 'sapo culiao', 'tragasables', 'jolaperra', 'maricon',
			'maricona', 'perkin', 'longi', 'sacoweas', 'mermelao', 'weon', 'weona', 'pichula',
			'tula', 'wueona', 'pija', 'marica',
			/* Lista de palabras inclusivas */
			'aliades', 'elles', 'cuerpa'
		);

		/**********************  Retorno datos  **********************/
		return $censuradas;

	}
    /************************************************************************************************************/
	private function _validateEmpty($Data, $Name){

		/**********************  Validaciones   **********************/
        // Retorno inmediato si el valor es nulo, cadena vacía o numéricamente cero
        if ($Data=='') {return 'Sin datos ingresados en '.$Name;}

		/**********************  Retorno datos  **********************/
		return true;

	}
    /************************************************************************************************************/
	private function _validateInteger($Data, $Name){

		/**********************  Validaciones   **********************/
        // Retorno inmediato si el valor es nulo, cadena vacía o numéricamente cero
        if ($Data=='') {return 'Sin datos ingresados en '.$Name;}
        // Validación de tipos de datos mediante el componente externo DataValidations
        if (!$this->DataValidations->validarNumero($Data) || !$this->DataValidations->validarEntero($Data)) {
            return 'El dato ingresado en '.$Name.' no es un numero ('.$Data.')';
        }

		/**********************  Retorno datos  **********************/
		return true;

	}

}

