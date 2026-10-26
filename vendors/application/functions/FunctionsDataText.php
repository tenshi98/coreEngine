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
	/******************************************************************************/
	//Definiciones
	private $DataValidations;

	/******************************************************************************/
	/**
	 * Inicializa la instancia con el componente de validaciones requerido.
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * $DataText = new FunctionsDataText();
	 * ```
	 */
	public function __construct() {
		$this->DataValidations = new FunctionsDataValidations();
	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Publicos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
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
	 * $DataText->limpiarString('Lorem ipsum\n dolor sit amet\n, consectetur\r'); // Devuelve 'Lorem ipsum dolor sit amet consectetur'
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

    /******************************************************************************/
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
	 * $DataText->limpiezaTexto("bla\"bla'bla"); // Devuelve 'bla%22bla%27bla'
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

    /******************************************************************************/
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
	 * $DataText->limpiarOracion('ÀÁÂÃÄÅÆ'); // Devuelve aaaaaaae
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

    /******************************************************************************/
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
	 * $DataText->tituloMenu( '01 - titulo' ); // Devuelve 'titulo'
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

	/******************************************************************************/
	/**
     * Convierte un texto en un slug apto para URL (kebab-case).
     *
     * Normaliza a minúsculas, elimina tildes (iconv), sustituye cualquier
     * secuencia no alfanumérica por un solo guion y recorta guiones de los
     * extremos. La longitud máxima es de 200 caracteres, cortada en borde de palabra.
     *
     * @param string $texto Texto de origen.
     *
     * @return string Slug resultante; mensaje de error si el texto está vacío.
     *
     * @example
     * ```php
     * $DataText->slugUrl('¡Hola, Mundo!');   // Devuelve 'hola-mundo'
     * $DataText->slugUrl('Café con Leche');  // Devuelve 'cafe-con-leche'
     * ```
     *
     */
    public function slugUrl($texto): string {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna: el texto no puede estar vacío
		$dataVal = $this->_validateEmpty($texto, 'texto');
		if ($dataVal !== true) { return $dataVal; }

        /********************** Proceso de Normalizacion **********************/
        // Minúsculas, sin tildes (cuando iconv está disponible) y recorte de bordes
        $slug = strtolower(trim((string)$texto));
        if (function_exists('iconv')) {
            $slug = (string)iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug);
        }
        // Cualquier secuencia no alfanumérica se colapsa en un único guion
        $slug = (string)preg_replace('/[^a-z0-9]+/i', '-', $slug);
        $slug = trim($slug, '-');

        // Corte de seguridad en borde de palabra bajo el máximo de 200 caracteres
        if (strlen($slug) > 200) {
            $corte = strrpos(substr($slug, 0, 200), '-');
            $slug  = ($corte !== false && $corte > 0) ? substr($slug, 0, $corte) : substr($slug, 0, 200);
        }

        /********************** Retorno datos **********************/
        return $slug;

    }

	/******************************************************************************/
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
	 * $DataText->sanitizarTexto('Lorem <b>ipsum</b>'); // Devuelve 'Lorem &lt;b&gt;ipsum&lt;/b&gt;'
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

    /******************************************************************************/
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
	 * $DataText->desanitizarTexto('Lorem &lt;b&gt;ipsum&lt;/b&gt;'); // Devuelve 'Lorem <b>ipsum</b>'
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

	/******************************************************************************/
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
	 * $DataText->cortar('Lorem ipsum dolor sit amet, consectetur', 10); // Devuelve 'Lorem ipsu...'
	 * ```
	 *
     */
    public function cortar($texto, $cuantos): string {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateEmpty($texto, 'texto');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return $dataVal_1; }

		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_2 = $this->_validateInteger($cuantos, 'cuantos');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_2 !== true) { return $dataVal_2; }

        /********************** Proceso de Recorte **********************/
        // Operación compatible con caracteres especiales y acentos
        if (extension_loaded('mbstring')) {
            return (mb_strlen($texto) <= $cuantos) ? $texto : mb_substr($texto, 0, $cuantos, 'UTF-8').'...';
        } else {
            // Sin mbstring no se puede cortar por caracteres UTF-8 sin riesgo de partir
            // un multibyte (tildes, eñe); se devuelve el texto intacto en vez de corromperlo
            return $texto;
        }

    }

    /******************************************************************************/
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
	 * $DataText->reemplazarEspaciosxGuion('Lorem ipsum dolor sit amet, consectetur'); // Devuelve 'Lorem_ipsum_dolor_sit_amet,_consectetur'
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

	/******************************************************************************/
	/**
     * Genera un resumen recortando un texto a la cantidad de palabras indicada.
     *
     * Si el texto tiene menos o igual palabras que el límite se devuelve completo;
     * en caso contrario se trunca y se agrega el sufijo indicado.
     *
     * @param string $texto Texto de origen.
     * @param int $Palabras Cantidad máxima de palabras (entre 1 y 500).
     * @param string $Sufijo Texto agregado al recortar (por defecto '...').
     *
     * @return string Resumen; vacío si el texto está vacío; mensaje de error si $Palabras está fuera de rango.
     *
     * @example
     * ```php
     * $DataText->resumen('uno dos tres cuatro', 2); // Devuelve 'uno dos...'
     * $DataText->resumen('uno dos', 5);             // Devuelve 'uno dos'
     * ```
     *
     */
    public function resumen($texto, int $Palabras = 30, string $Sufijo = '...'): string {

        /**********************  Validaciones   **********************/
        // Texto vacío o nulo se reporta como cadena vacía (no es un error)
        if ($texto === null || trim((string)$texto) === '') {
            return '';
        }
        // Control de rango de palabras solicitadas
        if ($Palabras < 1 || $Palabras > 500) {
            return 'El dato ingresado no es un numero de palabras valido ('.$Palabras.')';
        }

        /********************** Proceso de Recorte **********************/
        // Segmentación del texto en palabras reales (colapsa espacios múltiples)
        $Partes = preg_split('/\s+/', trim((string)$texto), -1, PREG_SPLIT_NO_EMPTY);

        /********************** Retorno datos **********************/
        // Si el texto cabe completo se devuelve sin sufijo
        if (count($Partes) <= $Palabras) {
            return implode(' ', $Partes);
        }
        // De lo contrario se trunca en el límite y se agrega el sufijo
        return implode(' ', array_slice($Partes, 0, $Palabras)).$Sufijo;

    }

	/******************************************************************************/
	/**
     * Localiza una palabra o subcadena específica y extrae todo el contenido que aparece tras ella.
     *
     * Busca la primera aparición de $palabra dentro de $cadena y devuelve la posición
     * en la que comienza junto con el texto posterior a dicha palabra (sin incluirla).
     *
     * Características de la búsqueda:
     * - sensible a mayúsculas/minúsculas (strpos),
     * - solo considera la primera coincidencia,
     * - trabaja por bytes: 'posicion' es un índice byte base 0, por lo que en cadenas
     *   UTF-8 puede diferir del índice de carácter (ej: 'ñandú feliz' => posición 8).
     *
     * Guardas aplicadas:
     * - Si $cadena está vacío, retorna success=false con 'Sin datos ingresados en cadena'.
     * - Si $palabra está vacío, retorna success=false con 'Sin datos ingresados en palabra'.
     * - Si $palabra no aparece en $cadena, retorna success=false con 'No se encuentra la posicion'.
     *
     * @param string $cadena Texto completo donde se realizará la búsqueda.
     * @param string $palabra Término de referencia a ubicar.
     *
     * @return array Respuesta estructurada con las claves:
     *               - success (bool): true solo si la palabra fue encontrada.
     *               - data (array|string): cuando success=true, arreglo con:
     *                   - 'posicion' (int): índice byte base 0 de la primera aparición.
     *                   - 'extraido' (string): texto posterior a la palabra
     *                     (puede ser '' si la palabra cierra la cadena);
     *                 cuando success=false, cadena vacía ('').
     *               - error (string): presente solo cuando success=false, indica el
     *                 motivo del fallo; no se incluye en el retorno de éxito.
	 *
	 * @example
	 * ```php
	 * $DataText->buscarPalabraYExtraer('01 - titulo', '01 - ');
	 * // ['success' => true, 'data' => ['posicion' => 0, 'extraido' => 'titulo']]
	 *
	 * $DataText->buscarPalabraYExtraer('Lorem ipsum dolor sit amet', 'ipsum');
	 * // ['success' => true, 'data' => ['posicion' => 6, 'extraido' => ' dolor sit amet']]
	 *
	 * $DataText->buscarPalabraYExtraer('Lorem ipsum dolor', 'xyz');
	 * // ['success' => false, 'data' => '', 'error' => 'No se encuentra la posicion']
	 * ```
	 *
     */
    public function buscarPalabraYExtraer($cadena, $palabra): array {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_1 = $this->_validateEmpty($cadena, 'cadena');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return ['success' => false, 'data' => '', 'error' => $dataVal_1]; }

		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_2 = $this->_validateEmpty($palabra, 'palabra');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_2 !== true) { return ['success' => false, 'data' => '', 'error' => $dataVal_2]; }

        /********************** Localización y Extracción **********************/
        // Encuentra el índice numérico de la primera aparición de la palabra
        $pos = strpos($cadena, $palabra);

        /********************** Retorno datos **********************/
        if ($pos === false) {
            return ['success' => false, 'data' => '', 'error' => 'No se encuentra la posicion'];
        } else {
            // Calcula el inicio del texto posterior sumando el largo de la palabra clave
            $posSiguiente = $pos + strlen($palabra);

            // Obtiene la subcadena restante desde la posición calculada
            $extraido = substr($cadena, $posSiguiente);

			// Devolver un array con la posición y el texto extraído
            $data = [
                'posicion' => $pos,
                'extraido' => $extraido
            ];

			// Retorno de datos
			return ['success' => true, 'data' => $data];
        }

    }

    /******************************************************************************/
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
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_1 !== true) { return ['izquierda' => trim($dataVal_1 ?? ''),'derecha'   => ''];}

		// Ejecuta la validación interna del formato y consistencia de la fecha recibida
		$dataVal_2 = $this->_validateEmpty($divisor, 'divisor');
		// Si la validación devuelve un valor distinto a true, se retorna el error/resultado de la validación
		if ($dataVal_2 !== true) { return ['izquierda' => trim($dataVal_2 ?? ''),'derecha'   => ''];}

		/********************** Si todo esta ok **********************/
        $partes = explode($divisor, $texto, 2); // solo divide en 2 partes

		/********************** Retorno datos **********************/
        return [
			'izquierda' => trim($partes[0] ?? ''),
			'derecha'   => trim($partes[1] ?? '')
		];

	}

	/******************************************************************************/
	/**
     * Resalta la primera aparición de una palabra dentro de un texto (HTML seguro).
     *
     * El texto y la palabra se escapan con htmlspecialchars antes de envolver la
     * coincidencia en una etiqueta <mark>, por lo que el resultado es seguro
     * frente a inyección HTML. La búsqueda no distingue mayúsculas.
     *
     * @param string $texto Texto de origen (se escapa antes de marcar).
     * @param string $palabra Palabra a resaltar.
     * @param string $Clase Clase CSS de la etiqueta (saneada a [A-Za-z0-9_-], por defecto 'mark').
     *
     * @return string Texto con la marca insertada (o escapado sin marca si no hay coincidencia).
     *
     * @example
     * ```php
     * $DataText->resaltarPalabra('Hola Mundo', 'mundo'); // Devuelve 'Hola <mark class="mark">Mundo</mark>'
     * $DataText->resaltarPalabra('<b>txt</b>', 'txt');  // Devuelve '&lt;b&gt;<mark class="mark">txt</mark>&lt;/b&gt;'
     * ```
     *
     */
    public function resaltarPalabra($texto, $palabra, $Clase = 'mark'): string {

        /**********************  Validaciones   **********************/
		// Ejecuta la validación interna: el texto no puede estar vacío
		$dataVal_1 = $this->_validateEmpty($texto, 'texto');
		if ($dataVal_1 !== true) { return $dataVal_1; }
		// Ejecuta la validación interna: la palabra no puede estar vacía
		$dataVal_2 = $this->_validateEmpty($palabra, 'palabra');
		if ($dataVal_2 !== true) { return $dataVal_2; }

        /********************** Proceso de Sanitizacion **********************/
        // La clase CSS se sanea a caracteres seguros para atributo; si queda vacía usa el valor por defecto
        $Clase = (string)preg_replace('/[^A-Za-z0-9_-]/', '', (string)$Clase);
        if ($Clase === '') { $Clase = 'mark'; }
        // Escapado de seguridad de ambos textos antes de construir el HTML
        $TextoSeguro  = htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
        $PalabraSegura = htmlspecialchars((string)$palabra, ENT_QUOTES, 'UTF-8');

        /********************** Retorno datos **********************/
        // Búsqueda de la primera coincidencia sin distinguir mayúsculas
        $offset = stripos($TextoSeguro, $PalabraSegura);
        // Sin coincidencia: se devuelve el texto escapado sin marca
        if ($offset === false) {
            return $TextoSeguro;
        }
        // Inserta la etiqueta mark alrededor de la coincidencia encontrada
        return substr($TextoSeguro, 0, $offset)
             . '<mark class="'.$Clase.'">'.substr($TextoSeguro, $offset, strlen($PalabraSegura)).'</mark>'
             . substr($TextoSeguro, $offset + strlen($PalabraSegura));

    }

	/******************************************************************************/
	/**
     * Cuenta la cantidad de palabras contenidas en un texto.
     *
     * Considera como palabra cualquier secuencia separada por espacios, tabs
     * o saltos de línea; los espacios múltiples no cuentan doble.
     *
     * @param string $texto Texto a contar.
     *
     * @return int Cantidad de palabras; 0 si el texto está vacío.
     *
     * @example
     * ```php
     * $DataText->contarPalabras('hola   mundo cruel'); // Devuelve 3
     * $DataText->contarPalabras('');                   // Devuelve 0
     * ```
     *
     */
    public function contarPalabras($texto): int {

        /**********************  Validaciones   **********************/
        // Texto vacío o nulo contiene cero palabras (no es un error)
        if ($texto === null || trim((string)$texto) === '') {
            return 0;
        }

        /********************** Proceso de Conteo **********************/
        // Segmentación por espacios en blanco descartando fragmentos vacíos
        $Partes = preg_split('/\s+/', trim((string)$texto), -1, PREG_SPLIT_NO_EMPTY);

        /********************** Retorno datos **********************/
        return count($Partes);

    }

	/******************************************************************************/
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
	 * $DataText->eliminarVerificadorRut('10294658-8'); // Devuelve '10294658'
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

	/******************************************************************************/
	/**
     * Identifica y cuenta la cantidad de palabras prohibidas o censuradas presentes en un texto.
     *
     * @param string $texto Texto a analizar en busca de lenguaje ofensivo.
     *
     * @return string|int Cantidad de palabras encontradas o mensaje de error si el input es nulo.
	 *
	 * @example
	 * ```php
	 * $DataText->contarPalabrasCensuradas('Lorem ipsum dolor sit amet, fuck d'); // Devuelve 1
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
        $censuradas = $this->_getListaPalabrasCensuradas();

        /********************** Retorno datos **********************/
        /**
         * Filtra la lista de palabras censuradas verificando si cada una existe dentro
         * del texto (usando espacios alrededor para evitar falsos positivos en sub-palabras).
         */
        return count(array_filter($censuradas, fn($w) => preg_match('/\b' . preg_quote($w, '/') . '\b/iu', $texto) === 1));

    }

    /******************************************************************************/
    /**
     * Busca palabras prohibidas en un texto y las oculta reemplazándolas por asteriscos.
     *
     * @param string $texto Texto original a filtrar.
     *
     * @return string Texto resultante con las palabras censuradas sustituidas por '****'.
	 *
	 * @example
	 * ```php
	 * $DataText->filtrarPalabrasCensuradas('Lorem ipsum dolor sit amet, fuck d'); // Devuelve 'lorem ipsum dolor sit amet, **** d'
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
        $censuradas   = $this->_getListaPalabrasCensuradas();

        /********************** Retorno datos **********************/
        // Reemplazo insensible a mayúsculas/minúsculas de todos los términos detectados
        usort($censuradas, fn($a, $b) => strlen($b) <=> strlen($a));
        $patrones = array_map(fn($w) => '/\b' . preg_quote($w, '/') . '\b/iu', $censuradas);
        return preg_replace($patrones, '****', $texto);

    }

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Privados                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
    /******************************************************************************/
    /**
	 * Entrega la base de datos (lista negra) de palabras a censurar.
	 *
	 * Incluye términos en inglés, español, modismos chilenos y algunos inclusivos;
	 * es consumida por contarPalabrasCensuradas() y filtrarPalabrasCensuradas().
	 *
	 * @return array Arreglo de cadenas con las palabras censuradas.
	 *
	 * @example
	 * ```php
	 * $this->_getListaPalabrasCensuradas(); // Devuelve el arreglo con las palabras censuradas
	 * ```
	 */
	private static $cacheCensuradas = null;

	private function _getListaPalabrasCensuradas(): array {

		/********************** Si todo esta ok **********************/
		// Cache estatica: el literal de ~300 cadenas se construye una sola vez por proceso
		if (self::$cacheCensuradas !== null) { return self::$cacheCensuradas; }
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
		self::$cacheCensuradas = $censuradas;

		/**********************  Retorno datos  **********************/
		return $censuradas;

	}

	/******************************************************************************/
	/**
	 * Valida que un dato no venga nulo ni en cadena vacía.
	 * * A diferencia de la validación de Operations, aquí el cero ('0' o 0)
	 * sí se acepta como dato válido.
	 *
	 * @param mixed  $Data Valor a revisar.
	 * @param string $Name Nombre del campo para componer el mensaje de error.
	 *
	 * @return true|string True si hay dato; mensaje de error si está vacío.
	 *
	 * @example
	 * ```php
	 * $this->_validateEmpty('Lorem', 'texto'); // Devuelve true
	 * $this->_validateEmpty('', 'texto');      // Devuelve 'Sin datos ingresados en texto'
	 * ```
	 */
	private function _validateEmpty($Data, $Name){

		/**********************  Validaciones   **********************/
        // Retorno inmediato si el valor es nulo, cadena vacía o numéricamente cero
        if ($Data === null || trim((string)$Data) === '') {
            return 'Sin datos ingresados en '.$Name;
        }

		/**********************  Retorno datos  **********************/
		return true;

	}

	/******************************************************************************/
	/**
	 * Valida que un dato corresponda a un número entero sin decimales.
	 * * Rechaza valores nulos y cadenas vacías; el resto se delega en
	 * FunctionsDataValidations::validarNumero() y validarEntero().
	 *
	 * @param mixed  $Data Valor a validar (ej: 5).
	 * @param string $Name Nombre del campo para componer el mensaje de error.
	 *
	 * @return true|string True si el valor es un entero; mensaje de error en caso contrario.
	 *
	 * @example
	 * ```php
	 * $this->_validateInteger(5, 'cuantos');     // Devuelve true
	 * $this->_validateInteger('abc', 'cuantos'); // Devuelve 'El dato ingresado en cuantos no es un numero (abc)'
	 * ```
	 */
	private function _validateInteger($Data, $Name){

		/**********************  Validaciones   **********************/
        // Retorno inmediato si el valor es nulo, cadena vacía o numéricamente cero
        if ($Data === null || trim((string)$Data) === '') {
            return 'Sin datos ingresados en '.$Name;
        }
        // Validación de tipos de datos mediante el componente externo DataValidations
        if (!$this->DataValidations->validarNumero($Data) || !$this->DataValidations->validarEntero($Data)) {
            return 'El dato ingresado en '.$Name.' no es un numero ('.$Data.')';
        }

		/**********************  Retorno datos  **********************/
		return true;

	}


}
