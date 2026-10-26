<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class UIWidgetsFeed {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                 Instancias                                                      */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	//Definiciones
	private $Alertas;
	private $DataValidations;

	/******************************************************************************/
	/**
	 * Crea las instancias internas que la clase necesita: el proveedor de alertas y el validador de datos.
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * $Feed = new UIWidgetsFeed();
	 * ```
	 */
	public function __construct() {
		$this->Alertas = new UIWidgetsCommon();
		$this->DataValidations = new FunctionsDataValidations();
	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Publicos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	/**
	 * Genera un div que hace consumo de un feed de datos (lector RSS con paginación).
	 *
	 * Claves de $Options: 'URL' (obligatoria), 'Titulo', 'BASE', 'Identificador',
	 * 'MaxCount' (5), 'height' (400 px), 'ShowDesc', 'ShowPubDate', 'Type' (1 normal |
	 * 2 mini, default 1) y 'maxItems' (40). Ante URL vacía, valores no numéricos o un
	 * $Type fuera de catálogo imprime una alerta y detiene la ejecución (exit).
	 *
	 * @param array $Options Arreglo con las opciones del feed.
	 *
	 * @return void Imprime el widget del feed (CSS + JS RSSReader + div).
	 *
	 * @example
	 * ```php
	 * $Feed = new UIWidgetsFeed();
	 * $Options = [
	 *     'Titulo'        => 'Noticias',                    // Título del Feed
	 *     'URL'           => 'https://mi-sitio.cl/feed.xml', // URL con la dirección del feed
	 *     'BASE'          => 'https://mi-sitio.cl',          // Ruta de la raíz del sitio
	 *     'Identificador' => 'ID_noticias',                 // Identificador del div
	 *     'MaxCount'      => '40',      // Número máximo de datos a solicitar (depende del feed)
	 *     'height'        => 200,       // Número máximo de altura en px
	 *     'ShowDesc'      => true,      // Mostrar descripción (true-false)
	 *     'ShowPubDate'   => false,     // Mostrar la fecha de publicación (true-false)
	 *     'Type'          => 1,         // Tipo de feed (1 normal | 2 mini)
	 *     'maxItems'      => 10,        // Número de elementos para paginar
	 * ];
	 * $Feed->widget_feed($Options); // imprime el widget del feed
	 * ```
	 */
	public function widget_feed($Options){

		/**********************  Validaciones   **********************/
		//se definen las opciones disponibles
		$tipos = array(1, 2);
		//Validaciones
        if ($Options['URL'] === null || trim((string)$Options['URL']) === '') {                         echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la URL con la direccion del feed.');                exit;}
		if(isset($Options['MaxCount'])&&!$this->DataValidations->validarNumero($Options['MaxCount'])){  echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'El dato $MaxCount ingresado no es un numero.');                     exit;}
		if(isset($Options['height'])&&!$this->DataValidations->validarNumero($Options['height'])){      echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'El dato $height ingresado no es un numero.');                       exit;}
		if(isset($Options['Type'])&&!in_array($Options['Type'], $tipos)){                               echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'La configuracion $type entregada no esta dentro de las opciones.'); exit;}

		/**********************  Definiciones   **********************/
		$feed_Titulo       = $Options['Titulo'] ?? '';
		$feed_URL          = $Options['URL'] ?? '';
		$feed_BASE         = $Options['BASE'] ?? '';
		$feed_ID           = $Options['Identificador'] ?? 'div_feed_'.uniqid();
		$feed_MaxCount     = $Options['MaxCount'] ?? 5;
		$feed_height       = $Options['height'] ?? 400;
		$feed_ShowDesc     = $Options['ShowDesc']  ?? 'true';
		$feed_ShowPubDate  = $Options['ShowPubDate'] ?? 'true';
		$feed_Type         = $Options['Type'] ?? 1;
		$feed_maxItems     = $Options['maxItems'] ?? 40;

        /********************** Si todo esta ok **********************/
		/****************************************/
		//$Type:
		//		1 - Normal
		//		2 - Mini
		/****************************************/
		$widget  = '<link type="text/css" rel="stylesheet" href="'.$feed_BASE.'/vendor/rss_reader/rssReader.css?get='.time().'"/>';
		$widget .= '<script type="text/javascript"          src="'.$feed_BASE.'/vendor/rss_reader/rssReader.js?get='.time().'"></script>';
		$widget .= '
		<div id="rssReader_'.$feed_ID.'"></div>
		<script type="text/javascript">
			// Inicialización
			$(document).ready(function() {
				new RSSReader("#rssReader_'.$feed_ID.'", {
					cardTitle: "'.$feed_Titulo.'",        /* Titulo del feed */
					feedUrl: "'.$feed_URL.'",             /* URL de los datos */
					itemsPerPage: '.$feed_MaxCount.',     /* cantidad de post a mostrar */
					showDescription: '.$feed_ShowDesc.',  /* Mostrar descripcion (true-false) */
					showPubDate: '.$feed_ShowPubDate.',   /* Mostrar fecha de publicacion (true-false) */
					maxHeight: "'.$feed_height.'px",      /* Altura del div */
    				maxItems:'.$feed_maxItems.',          /* Numero maximo de noticias */
    				feed_Type:'.$feed_Type.',             /* Tipo de Feed */
				});
			});
		</script>';

		/**********************/
		//Imprimir dato
		echo $widget;

	}


}
