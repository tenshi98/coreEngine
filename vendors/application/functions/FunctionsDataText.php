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
	public function cortar($texto, $cuantos): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite cortar un texto determinado despues de cierta cantidad de caracteres determinados por el usuario, poniendo
		* tres puntos suspensivos indicando que el texto esta cortado
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se ejecuta operacion
		* 	$DataText->cortar('Lorem ipsum dolor sit amet, consectetur', 10); //Devuelve 'Lorem ipsu...'
		*
		*=================================================    Parametros   =================================================
		* @input   string   $texto     Texto a cortar
		* @input   int      $cuantos   Cantidad de caracteres a mostrar antes de cortar
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if (!$this->DataValidations->validarNumero($cuantos) || !$this->DataValidations->validarEntero($cuantos)) {
            return 'Verificar que el dato ingresado sea un numero';
        }

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		// Asegurarse de que la extensión mbstring esté habilitada
		if (extension_loaded('mbstring')) {
			return (mb_strlen($texto) <= $cuantos) ? $texto : mb_substr($texto, 0, $cuantos, 'UTF-8').'...';
		} else {
			// Fallback o manejo de error si mbstring no está disponible
			return (strlen($texto) <= $cuantos) ? $texto : substr($texto, 0, $cuantos).'...';
		}

	}

	/************************************************************************************************************/
	public function eliminarVerificadorRut($Rut): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Elimina el digito verificador del rut entregado, dejando solo los numeros. Hay que tener en cuenta que solo
		* funciona en Rut chilenos
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se ejecuta operacion
		* 	$DataText->eliminarVerificadorRut('10294658-9'); //Devuelve 10294658
		*
		*=================================================    Parametros   =================================================
		* @input   string   $Rut    Rut a cortar
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if (!isset($Rut) || $Rut==''){                   return 'No hay datos ingresados';}
		if (!$this->DataValidations->validarRut($Rut)){  return 'El dato ingresado no es un Rut';}

		/********************** Si todo esta ok **********************/
		// str_replace elimina todas las ocurrencias del punto (.) en la cadena $Rut.
		$Rut_limpio = str_replace('.', '', $Rut);
		// Encuentra la última ocurrencia de '-' y toma la subcadena antes de ella
		$lastDashPos = strrpos($Rut_limpio, '-');

		/**********************  Retorno datos  **********************/
		return ($lastDashPos !== false) ? substr($Rut_limpio, 0, $lastDashPos) : $Rut_limpio;

	}

	/************************************************************************************************************/
	public function limpiarString($texto): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite limpiar palabras u oraciones de caracteres raros o no admitidos
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se ejecuta operacion
		* 	$DataText->limpiarString('Lorem ipsum\n dolor sit amet\n, consectetur\r'); //Devuelve 'Lorem ipsum dolor sit amet consectetur'
		*
		*=================================================    Parametros   =================================================
		* @input   string   $texto   Texto a limpiar
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!isset($texto) || $texto==''){  return 'No ha ingresado ningún dato';}

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return trim(strip_tags(preg_replace('/[\r\n]+|[^A-Za-z0-9.]/', ' ', $texto)));

	}

	/************************************************************************************************************/
	public function reemplazarEspaciosxGuion($texto): string {
		/*
		*=================================================     Detalles    =================================================
		*
		* Formatea todos los espacios dentro de una oracion a guiones bajos
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se ejecuta operacion
		* 	$DataText->reemplazarEspaciosxGuion('Lorem ipsum dolor sit amet, consectetur'); //Devuelve 'Lorem_ipsum_dolor_sit_amet,_consectetur'
		*
		*=================================================    Parametros   =================================================
		* @input   string   $dato   Oracion a transformar
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!isset($texto) || $texto==''){  return 'No ha ingresado ningún dato';}

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		return str_replace(' ', '_', $texto);

	}

	/************************************************************************************************************/
	public function sanitizarTexto($texto): string {
		/*
		*=================================================     Detalles    =================================================
		*
		* Formatea todos los caracteres especiales por los estandares html
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se ejecuta operacion
		* 	$DataText->sanitizarTexto('Lorem ipsum dolor sit amet, consectetur'); //Devuelve 'Lorem ipsum dolor sit amet, consectetur'
		*
		*=================================================    Parametros   =================================================
		* @input   string   $texto   Oracion a transformar
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!isset($texto) || $texto==''){  return 'No ha ingresado ningún dato';}

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		// Convierte caracteres especiales a entidades HTML
        return htmlentities($texto, ENT_QUOTES, 'UTF-8');

	}

	/************************************************************************************************************/
	public function desanitizarTexto($texto): string {
		/*
		*=================================================     Detalles    =================================================
		*
		* Formatea los caracteres estandares html por los especiales
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se ejecuta operacion
		* 	$DataText->desanitizarTexto('Lorem ipsum dolor sit amet, consectetur'); //Devuelve 'Lorem ipsum dolor sit amet, consectetur'
		*
		*=================================================    Parametros   =================================================
		* @input   string   $texto   Oracion a transformar
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!isset($texto) || $texto==''){  return 'No ha ingresado ningún dato';}

		/********************** Si todo esta ok **********************/
		/**********************  Retorno datos  **********************/
		// Convierte entidades HTML a caracteres especiales
        return html_entity_decode($texto, ENT_QUOTES, 'UTF-8');

	}

	/************************************************************************************************************/
	public function limpiezaTexto($texto): string{
		/*
		*=================================================     Detalles    =================================================
		*
		* Reemplaza las comillas simples y dobles
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se verifica
		* 	$DataText->limpiezaTexto("bla"bla'bla"); //Devuelve 'bla%27bla%27bla'
		*
		*=================================================    Parametros   =================================================
		* @input   string   $texto   Texto a estandarizar
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!isset($texto) || $texto==''){  return 'No ha ingresado ningún dato';}

		/********************** Si todo esta ok **********************/
		// Limpieza general del texto
		$texto = preg_replace("/[\r\n]+/", '', strip_tags($texto));

		// Reemplaza comillas simples y dobles por sus equivalentes codificados
		$texto = str_replace(["'", '"'], ['%27', '%22'], $texto);

		// Normaliza acentos y reemplaza la ñ
		$texto = $this->sanitizarTexto($texto);

		/**********************  Retorno datos  **********************/
		return $texto;

	}

	/************************************************************************************************************/
	public function limpiarOracion($texto): string {
		/*
		*=================================================     Detalles    =================================================
		*
		* Se encarga de reemplazar todos los caracteres especiales a los similares en la lengua castellana
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se verifica
		* 	$DataText->limpiarOracion('ÀÁÂÃÄÅÆ'); //devuelve aaaaaaa
		*
		*=================================================    Parametros   =================================================
		* @input   string   $texto   Oracion a revisar
		* @return  string
		*===================================================================================================================
		*/

		/********************** Si todo esta ok **********************/
		if (extension_loaded('intl')) {
                $texto = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $texto);
		} else {
			// Fallback a la lógica actual o manejo de error
			$originales   = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿª';
			$modificadas  = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybya';
			$cadena       = mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
			$cadena       = strtr($cadena, mb_convert_encoding($originales, 'ISO-8859-1', 'UTF-8'), $modificadas);
			$texto        = mb_convert_encoding($cadena, 'UTF-8', 'ISO-8859-1');
			$texto        = strtolower($texto);
		}

		/**********************  Retorno datos  **********************/
		return $texto;

	}

	/************************************************************************************************************/
	public function contarPalabrasCensuradas($texto): string | int {
		/*
		*=================================================     Detalles    =================================================
		*
		* Cuenta si hay palabras malas u ofensivas que esten prohibidas por el sistema
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se ejecuta operacion
		* 	$DataText->contarPalabrasCensuradas('Lorem ipsum dolor sit amet, fuck d'); //Devuelve 1
		*
		*=================================================    Parametros   =================================================
		* @input   string   $texto   Oracion a revisar
		* @return  int
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!isset($texto) || $texto==''){   return 'No hay datos ingresados';}

		/********************** Si todo esta ok **********************/
		//se limpia la oracion
		$texto      = $this->limpiarOracion($texto);
		//bd con las palabras
		$censuradas   = $this->getListaPalabrasCensuradas();

		/**********************  Retorno datos  **********************/
		//Numero de palabras prohibidas
		return count(array_filter($censuradas, fn($w) => stripos(" $texto ", " $w ") !== false));

	}

	/************************************************************************************************************/
	public function filtrarPalabrasCensuradas($texto): string {
		/*
		*=================================================     Detalles    =================================================
		*
		* Filtra si hay palabras malas u ofensivas que esten prohibidas por el sistema, reemplazandolas por un ****
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se ejecuta operacion
		* 	$DataText->filtrarPalabrasCensuradas('Lorem ipsum dolor sit amet, fuck d'); //Devuelve 'lorem ipsum dolor sit amet, **** d'
		*
		*=================================================    Parametros   =================================================
		* @input   string   $texto   Oracion a revisar
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!isset($texto) || $texto==''){   return 'No hay datos ingresados';}

		/********************** Si todo esta ok **********************/
		//se limpia la oracion
		$texto       = $this->limpiarOracion($texto);
		//bd con las palabras
		$censuradas   = $this->getListaPalabrasCensuradas();

		/**********************  Retorno datos  **********************/
		//Frase limpia de palabras prohibidas
		return str_ireplace($censuradas, '****', $texto);

	}

    /************************************************************************************************************/
    public function tituloMenu($texto ): string {
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite eliminar la numeracion inicial en los textos
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se ejecuta operacion
		* 	$DataText->tituloMenu( '01 - titulo' ); //Devuelve 'titulo'
		*
		*=================================================    Parametros   =================================================
		* @input   string   $texto   Nombre a filtrar
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!isset($texto) || $texto==''){   return 'No hay datos ingresados';}

		/********************** Si todo esta ok **********************/
		//variable vacia
		$xdata = [];
		// Números del 0 al 100, con formato "n - " y "n.- "
		for ($i = 0; $i <= 100; $i++) {
			// Formato con dos dígitos, por ejemplo 01, 02, ..., 09
			$num_padded = str_pad($i, 2, '0', STR_PAD_LEFT);
			// Agregamos variantes sin y con ceros a la izquierda
			$xdata[] = $num_padded . " - ";
			$xdata[] = $num_padded . ".- ";
			$xdata[] = $i . " - ";
			$xdata[] = $i . ".- ";
		}

		/**********************  Retorno datos  **********************/
		//Si todo esta ok
		return str_replace($xdata, "", $texto);

    }

	/************************************************************************************************************/
	public function buscarPalabraYExtraer($cadena, string $palabra): array | string {
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite buscar dentro de una oracion y extraer el texto que sigue a esta, devuelve un array con la posicion y el
		* texto que le sigue
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se verifica
		* 	$DataText->buscarPalabraYExtraer('01 - titulo', '01 - '); //Devuelve 'titulo'
		*
		*=================================================    Parametros   =================================================
		* @input   string   $cadena    Cadena completa a revisar
		* @input   string   $palabra   Palabra a ubicar
		* @return  array
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!isset($cadena) || $cadena==''){     return 'No hay datos ingresados';}
		if(!isset($palabra) || $palabra==''){   return 'No hay datos ingresados';}

		/********************** Si todo esta ok **********************/
		// Buscar la posición de la palabra en la cadena
		$pos = strpos($cadena, $palabra);

		/**********************  Retorno datos  **********************/
		if ($pos === false) {
			// La palabra no se encontró
			return false;
		} else {
			// Calcular la posición donde empieza lo que sigue a la palabra
			$posSiguiente = $pos + strlen($palabra);

			// Extraer lo que sigue a la palabra
			$extraido = substr($cadena, $posSiguiente);

			// Devolver un array con la posición y el texto extraído
			return [
				'posicion' => $pos,
				'extraido' => $extraido
			];
		}
	}

	/************************************************************************************************************/
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
			'pitofloro', 'plomo', 'pocasluces', 'pollopera', 'quitahipos', 'rastrapajo',
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

}

