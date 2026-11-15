<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class UIWidgetsGraphics {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Publicos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	/**
	 * Genera un componente HTML que renderiza un gráfico utilizando la librería ApexCharts.
	 *
	 * Este método construye dinámicamente:
	 * - Un contenedor tipo "card" con título
	 * - Un elemento DIV donde se renderizará el gráfico
	 * - Un script autoejecutable que inicializa el gráfico de forma asíncrona
	 *
	 * Características principales:
	 * - Soporta distintos tipos de gráfico (bar, line, pie, etc.)
	 * - Maneja carga asíncrona de la librería ApexCharts mediante reintentos
	 * - Convierte los datos de entrada a formato JSON seguro para JavaScript
	 * - Extrae etiquetas y valores desde un arreglo estructurado
	 *
	 * Estructura esperada de $data:
	 * [
	 *   ['label' => 'Categoría 1', 'value' => 10],
	 *   ['label' => 'Categoría 2', 'value' => 20]
	 * ]
	 *
	 * @param array  $data   Arreglo de datos con claves 'label' y 'value'
	 * @param string $type   Tipo de gráfico ApexCharts (lista blanca: line, area, bar, pie, donut, radialBar, scatter, bubble, heatmap, candlestick, boxPlot, radar, polarArea, rangeBar, rangeArea, treemap); otro valor => 'bar'
	 * @param string $title  Título del gráfico (se escapa para HTML y para JS con json_encode)
	 * @param int    $height Altura en píxeles (entero, o cadena '350', '350px', '100%'); otro valor => 350
	 *
	 * @return string HTML + JavaScript necesario para renderizar el gráfico
	 *
	 * @throws \Exception No lanza excepciones explícitas; depende de la estructura de entrada y del entorno cliente
	 *
	 * @example
	 * ```php
	 * $Graphics = new UIWidgetsGraphics();
	 * $Graphics->generateApexChart(
	 *     [['label' => 'Enero', 'value' => 10], ['label' => 'Febrero', 'value' => 20]],
	 *     'bar',
	 *     'Ventas 2026'
	 * );
	 * // devuelve el HTML + script ApexCharts del gráfico
	 * ```
	 */
	public function generateApexChart($data, $type = 'bar', $title = 'Gráfico', $height = 350) {

		// Valida si existen datos para graficar
		if (empty($data)) {
			// Se Instancia
			$WidgetsCommon = new UIWidgetsCommon();
			// Mensaje
			$Btn_Text = 'No hay datos para graficar.';
			// Mostrar alerta
			return $WidgetsCommon->alertPostData(5, 4, 'bi bi-exclamation-triangle-fill', 0, $Btn_Text);
		}

		// Lista blanca de tipo y altura (valores inválidos => valores por defecto)
		$tiposValidos = ['line','area','bar','pie','donut','radialBar','scatter','bubble','heatmap','candlestick','boxPlot','radar','polarArea','rangeBar','rangeArea','treemap'];
		if (!is_string($type) || !in_array($type, $tiposValidos, true)) { $type = 'bar'; }
		if (is_int($height) || (is_string($height) && preg_match('/^[0-9]{1,4}$/', $height))) {
			$heightJs = (string)(int)$height;
		} elseif (is_string($height) && preg_match('/^[0-9]{1,4}(px|%)$/', $height)) {
			$heightJs = json_encode($height);
		} else {
			$heightJs = '350';
		}
		// Título seguro tanto para JS (json_encode con HEX_*) como para HTML
		$titleStr  = (string)$title;
		$titleJs   = json_encode($titleStr, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
		if ($titleJs === false) { $titleJs = '""'; }
		$titleHtml = htmlspecialchars($titleStr, ENT_QUOTES, 'UTF-8');

		// Genera un ID único para el contenedor del gráfico
		$chartId = 'chart_' . uniqid();

		// Inicializa arreglos para categorías (eje X) y valores (serie de datos)
		$categories = [];
		$values = [];

		// Recorre los datos para separar etiquetas y valores
		foreach ($data as $row) {
			$categories[] = $row['label'] ?? '';
			$values[]     = $row['value'] ?? 0;
		}

		// Convierte los arreglos a formato JSON para uso en JavaScript
		$categoriesJson = json_encode($categories);
		$valuesJson     = json_encode($values);

		// Construye el HTML y script necesario para renderizar el gráfico
		$html = '
		<div class="card mb-3 shadow-sm">
			<div class="card-body">
				<h6 class="card-title">'.$titleHtml.'</h6>
				<div id="'.$chartId.'"></div>
			</div>
		</div>

		<script>
		(function() {
			// Función encargada de renderizar el gráfico
			function renderChart() {
				// Verifica si la librería ApexCharts está disponible
				if (typeof ApexCharts === "undefined") {
					// Reintenta luego de un breve intervalo si aún no está cargada
					return setTimeout(renderChart, 100);
				}

				// Configuración del gráfico
				var options = {
					chart: {
						type: "'.$type.'",
						height: '.$heightJs.'
					},
					series: [{
						name: "Valores",
						data: '.$valuesJson.'
					}],
					xaxis: {
						categories: '.$categoriesJson.'
					},
					title: {
						text: '.$titleJs.'
					}
				};

				// Inicializa el gráfico en el contenedor correspondiente
				var chart = new ApexCharts(document.querySelector("#'.$chartId.'"), options);
				chart.render();
			}

			// Ejecuta la función inmediatamente para soportar inserciones dinámicas
			renderChart();
		})();
		</script>
		';

		/**********************  Retorno datos  **********************/
		// Retorna el HTML completo con el gráfico
		return $html;

	}

	/******************************************************************************/
	/**
	 * Genera un gráfico de series múltiples (barras) delegando el render en generateApexChart().
	 *
	 * Valida que existan series y que cada una tenga tantos datos como categorías; ante error devuelve
	 * la alerta de alertPostData(). El HTML/JS lo produce generateApexChart() con la primera serie y
	 * luego se le inyectan todas las series (sin duplicar la lógica de render).
	 *
	 * @param array  $Series     Series [['nombre'=>'Ventas','datos'=>[10,20]], ...].
	 * @param array  $Categorias Categorías del eje X (mismo largo que 'datos' de cada serie).
	 * @param string $Titulo     Título del gráfico.
	 * @param int    $Altura     Altura en píxeles.
	 *
	 * @return string HTML + JavaScript del gráfico, o la alerta de error.
	 *
	 * @example
	 * ```php
	 * $Graphics = new UIWidgetsGraphics();
	 * echo $Graphics->generateApexMultiSeries(
	 *     [['nombre' => '2025', 'datos' => [10, 20, 30]], ['nombre' => '2026', 'datos' => [15, 25, 35]]],
	 *     ['Ene', 'Feb', 'Mar'],
	 *     'Ventas comparadas'
	 * );
	 * ```
	 */
	public function generateApexMultiSeries(array $Series, array $Categorias, string $Titulo = 'Gráfico', int $Altura = 350) {

		/**********************  Validaciones   **********************/
		if (empty($Series) || empty($Categorias)) {
			return $this->_graphAlert('No hay datos para graficar.');
		}
		$Categorias = array_values($Categorias);
		$apexSeries = [];
		foreach ($Series as $serie) {
			$datos = (is_array($serie) && isset($serie['datos']) && is_array($serie['datos'])) ? array_values($serie['datos']) : [];
			if (count($datos) !== count($Categorias)) {
				return $this->_graphAlert('Cada serie debe tener tantos datos como categorias.');
			}
			$apexSeries[] = ['name' => (string)($serie['nombre'] ?? ''), 'data' => array_map('floatval', $datos)];
		}

		/********************** Si todo esta ok **********************/
		// Filas label/value de la primera serie para el render base
		$rows = [];
		foreach ($Categorias as $i => $cat) {
			$rows[] = ['label' => (string)$cat, 'value' => $apexSeries[0]['data'][$i]];
		}

		/**********************  Retorno datos  **********************/
		return $this->_apexOverride($this->generateApexChart($rows, 'bar', $Titulo, $Altura), ['series' => $apexSeries]);

	}

	/******************************************************************************/
	/**
	 * Genera un gráfico donut delegando el render en generateApexChart().
	 *
	 * @param array  $Etiquetas Etiquetas de cada porción.
	 * @param array  $Valores   Valores numéricos (mismo largo que $Etiquetas).
	 * @param string $Titulo    Título del gráfico.
	 *
	 * @return string HTML + JavaScript del gráfico, o la alerta de error (vacío o largos distintos).
	 *
	 * @example
	 * ```php
	 * $Graphics = new UIWidgetsGraphics();
	 * echo $Graphics->generateApexDonut(['Activos', 'Inactivos'], [70, 30], 'Estado de clientes');
	 * ```
	 */
	public function generateApexDonut(array $Etiquetas, array $Valores, string $Titulo = 'Gráfico') {

		/**********************  Validaciones   **********************/
		if (empty($Etiquetas) || empty($Valores)) {
			return $this->_graphAlert('No hay datos para graficar.');
		}
		if (count($Etiquetas) !== count($Valores)) {
			return $this->_graphAlert('Las etiquetas y los valores deben tener la misma cantidad de elementos.');
		}

		/********************** Si todo esta ok **********************/
		$Etiquetas = array_map('strval', array_values($Etiquetas));
		$Valores   = array_map('floatval', array_values($Valores));
		$rows = [];
		foreach ($Etiquetas as $i => $label) {
			$rows[] = ['label' => $label, 'value' => $Valores[$i]];
		}

		/**********************  Retorno datos  **********************/
		return $this->_apexOverride($this->generateApexChart($rows, 'donut', $Titulo), ['series' => $Valores, 'labels' => $Etiquetas]);

	}

	/******************************************************************************/
	/**
	 * Genera un gráfico radialBar delegando el render en generateApexChart().
	 *
	 * Cada valor se recorta al rango 0..100 (valores fuera de rango no producen error).
	 *
	 * @param array  $Valores Porcentajes 0..100, uno por serie.
	 * @param string $Titulo  Título del gráfico.
	 *
	 * @return string HTML + JavaScript del gráfico, o la alerta de error (vacío).
	 *
	 * @example
	 * ```php
	 * $Graphics = new UIWidgetsGraphics();
	 * echo $Graphics->generateApexRadialBar([45, 60, 130], 'Metas'); // 130 se recorta a 100
	 * ```
	 */
	public function generateApexRadialBar(array $Valores, string $Titulo = 'Gráfico') {

		/**********************  Validaciones   **********************/
		if (empty($Valores)) {
			return $this->_graphAlert('No hay datos para graficar.');
		}

		/********************** Si todo esta ok **********************/
		$rows   = [];
		$series = [];
		$labels = [];
		foreach (array_values($Valores) as $i => $valor) {
			$valor    = min(100.0, max(0.0, (float)$valor));
			$series[] = $valor;
			$labels[] = 'Serie '.($i + 1);
			$rows[]   = ['label' => 'Serie '.($i + 1), 'value' => $valor];
		}

		/**********************  Retorno datos  **********************/
		return $this->_apexOverride($this->generateApexChart($rows, 'radialBar', $Titulo), ['series' => $series, 'labels' => $labels]);

	}

	/******************************************************************************/
	/**
	 * Genera un sparkline (línea minúscula, sin ejes ni leyenda, alto 50) delegando en generateApexChart().
	 *
	 * @param array $Valores  Puntos de la serie (mínimo 2).
	 * @param array $Opciones 'color': primary|secondary|success|info|warning|danger (otro valor se ignora).
	 *
	 * @return string HTML + JavaScript del gráfico, o la alerta de error (<=1 punto).
	 *
	 * @example
	 * ```php
	 * $Graphics = new UIWidgetsGraphics();
	 * echo $Graphics->generateSparkline([10, 20, 15, 25, 30], ['color' => 'success']);
	 * ```
	 */
	public function generateSparkline(array $Valores, array $Opciones = []) {

		/**********************  Validaciones   **********************/
		if (count($Valores) <= 1) {
			return $this->_graphAlert('Se requieren al menos 2 puntos para el sparkline.');
		}

		/**********************  Definiciones   **********************/
		$paleta = [
			'primary'   => '#0d6efd',
			'secondary' => '#6c757d',
			'success'   => '#198754',
			'info'      => '#0dcaf0',
			'warning'   => '#ffc107',
			'danger'    => '#dc3545',
		];
		$override = [
			'chart'  => ['sparkline' => ['enabled' => true], 'toolbar' => ['show' => false]],
			'legend' => ['show' => false],
			'title'  => ['text' => ''],
		];
		$color = (string)($Opciones['color'] ?? '');
		if (isset($paleta[$color])) {
			$override['colors'] = [$paleta[$color]];
		}

		/********************** Si todo esta ok **********************/
		$rows = [];
		foreach (array_values($Valores) as $i => $valor) {
			$rows[] = ['label' => (string)($i + 1), 'value' => (float)$valor];
		}

		/**********************  Retorno datos  **********************/
		return $this->_apexOverride($this->generateApexChart($rows, 'line', '', 50), $override);

	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Privados                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	/**
	 * Inyecta opciones ApexCharts adicionales en el script producido por generateApexChart(), justo antes
	 * de instanciar el gráfico (fusiona 'chart' y reemplaza el resto de claves). No duplica el render.
	 *
	 * @param string $Html     HTML devuelto por generateApexChart().
	 * @param array  $Override Opciones Apex a aplicar (se serializan con json_encode seguro para script).
	 *
	 * @return string HTML con las opciones inyectadas (sin cambios si no es un gráfico).
	 *
	 * @example
	 * ```php
	 * $this->_apexOverride($html, ['legend' => ['show' => false]]);
	 * ```
	 */
	private function _apexOverride(string $Html, array $Override): string {

		/**********************  Definiciones   **********************/
		$marker = 'var chart = new ApexCharts(';
		if (strpos($Html, $marker) === false) { return $Html; }
		// Si la serialización falla (INF/NaN, UTF-8 inválido) se degrada sin override: nunca 'var ov = ;'
		$json = json_encode($Override, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
		if ($json === false) { return $Html; }

		/**********************  Retorno datos  **********************/
		$inject = 'var ov = '.$json.';
				for (var k in ov) { if (k === "chart") { for (var c in ov.chart) { options.chart[c] = ov.chart[c]; } } else { options[k] = ov[k]; } }
				';
		return str_replace($marker, $inject.$marker, $Html);

	}

	/******************************************************************************/
	/**
	 * Devuelve una alerta de error con el formato de la casa (alertPostData).
	 *
	 * @param string $Texto Mensaje a mostrar.
	 *
	 * @return string HTML de la alerta.
	 *
	 * @example
	 * ```php
	 * echo $this->_graphAlert('No hay datos para graficar.');
	 * ```
	 */
	private function _graphAlert(string $Texto): string {

		/**********************  Retorno datos  **********************/
		$WidgetsCommon = new UIWidgetsCommon();
		return $WidgetsCommon->alertPostData(5, 4, 'bi bi-exclamation-triangle-fill', 0, $Texto);

	}

}
