<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class UIWidgetsIndicators {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                 Instancias                                                      */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	//Definiciones
	private $TemplateRender;

	/******************************************************************************/
	/**
	 * Crea la instancia de TemplateRenderer que la clase usa para la utilizacion de
	 * plantillas.
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * $TemplateRender = new TemplateRenderer();
	 * ```
	 */
	public function __construct() {
		$this->TemplateRender = new TemplateRenderer();
	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Publicos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
    /******************************************************************************/
	/**
	 * Permite obtener los indicadores desde el sitio del SII.
	 *
	 * Consulta el feed RSS del SII, pinta cada indicador como un enlace con color alternado
	 * (azul, verde, amarillo, rojo) y renderiza la plantilla widgetsIndicadoresSII_1. Si la
	 * consulta falla, imprime el mensaje de error devuelto por el servicio.
	 *
	 * @return void Imprime el widget de indicadores (o el mensaje de error) en salida estándar.
	 *
	 * @example
	 * ```php
	 * $Indicators = new UIWidgetsIndicators();
	 * $Indicators->indicadores(); // imprime el widget con los indicadores del SII
	 * ```
	 */
	public function indicadores(){

		/********************** Si todo esta ok **********************/
        /**********************  Retorno datos  **********************/
		// Variables
		$counter    = 1;
		$widgetData = '';

		// Colores predefinidos
		$arrColors = [
			1 => ['color' => 'text-color-blue'],
			2 => ['color' => 'text-color-green'],
			3 => ['color' => 'text-color-yellow'],
			4 => ['color' => 'text-color-red'],
		];

		/************************************/
		//Se obtienen los datos
		$ServerWeb = new FunctionsServerWeb();
		$XMLData   = $ServerWeb->getDataSIIindicadores('https://zeus.sii.cl/admin/rss/sii_ind_rss.xml');

		/************************************/
		//Se verifica la recepcion de datos
		if($XMLData['success']===true){
			//Se recorren los datos
			foreach($XMLData['data'] as $data){
				//Escapado de los datos del feed remoto (evita XSS si el origen se compromete por MITM/compromiso DNS)
				$linkFeed = (string)($data['link'] ?? '');
				if (!preg_match('#^https?://#i', $linkFeed)) { $linkFeed = '#'; }
				$linkFeed       = htmlspecialchars($linkFeed, ENT_QUOTES, 'UTF-8');
				$descriptionFeed = htmlspecialchars((string)($data['description'] ?? ''), ENT_QUOTES, 'UTF-8');
				$titleFeed      = htmlspecialchars((string)($data['title'] ?? ''), ENT_QUOTES, 'UTF-8');
				//Imprimo los datos
				$widgetData .= '
				<a href="'.$linkFeed.'" class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
					<span class="'.$arrColors[$counter]['color'].'">'.$descriptionFeed.'</span>
					<span>'.$titleFeed.'</span>
				</a>';
				//sumo
				$counter++;
				if($counter==5){$counter=1;}
			}
		}else{
			$widgetData = $XMLData['data'];
		}

		/************************************/
		//Se agregan datos
		$this->TemplateRender->templatePath('../app/templates/Widgets/widgetsIndicadoresSII_1.php');
		$this->TemplateRender->assign('widgetData', $widgetData);

		/************************************/
		//ejecucion
		echo $this->TemplateRender->render();

	}


}
