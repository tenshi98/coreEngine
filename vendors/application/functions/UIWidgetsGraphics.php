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
	 * @param string $type   Tipo de gráfico compatible con ApexCharts (por defecto 'bar')
	 * @param string $title  Título del gráfico
	 * @param int    $height Altura del gráfico en píxeles
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
				<h6 class="card-title">'.htmlspecialchars($title).'</h6>
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
						height: '.$height.'
					},
					series: [{
						name: "Valores",
						data: '.$valuesJson.'
					}],
					xaxis: {
						categories: '.$categoriesJson.'
					},
					title: {
						text: "'.addslashes($title).'"
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
	 * Genera un gráfico de series múltiples utilizando ApexCharts.
	 *
	 * @param array  $Series Arreglo de series: [['nombre'=>'...','datos'=>[…]], …]
	 * @param array  $Categorias Arreglo de categorías para el eje X
	 * @param string $Titulo Título del gráfico (default: 'Gráfico')
	 * @param int    $Altura Altura del gráfico en píxeles (default: 350)
	 *
	 * @return string HTML + JavaScript necesario para renderizar el gráfico
	 *
	 * @example
	 * ```php
	 * $Graphics = new UIWidgetsGraphics();
	 * $Graphics->generateApexMultiSeries(
	 *     [['nombre' => 'Enero', 'datos' => [10, 20, 30]], ['nombre' => 'Febrero', 'datos' => [15, 25, 35]]],
	 *     ['Enero', 'Febrero', 'Marzo'],
	 *     'Ventas 2026'
	 * );
	 * ```
	 */
	public function generateApexMultiSeries($Series, $Categorias, $Titulo = 'Gráfico', $Altura = 350) {

		// Delegar a generateApexChart
		return $this->generateApexChart(
			[$Series],
			'bar',
			$Titulo,
			$Altura
		);

	}

	/******************************************************************************/
	/**
	 * Genera un gráfico donut utilizando ApexCharts.
	 *
	 * @param array  $Etiquetas Arreglo de etiquetas
	 * @param array  $Valores Arreglo de valores
	 * @param string $Titulo Título del gráfico (default: 'Gráfico')
	 *
	 * @return string HTML + JavaScript necesario para renderizar el gráfico
	 *
	 * @example
	 * ```php
	 * $Graphics = new UIWidgetsGraphics();
	 * $Graphics->generateApexDonut(
	 *     ['Enero', 'Febrero', 'Marzo'],
	 *     [10, 20, 30],
	 *     'Ventas 2026'
	 * );
 	 * ```
	 */
	public function generateApexDonut($Etiquetas, $Valores, $Titulo = 'Gráfico') {

		// Delegar a generateApexChart
		return $this->generateApexChart(
			array_combine($Etiquetas, $Valores),
			'donut',
			$Titulo
		);

	}

	/******************************************************************************/
	/**
	 * Genera un gráfico radial bar utilizando ApexCharts.
	 *
	 * @param array  $Valores Arreglo de valores (0-100 por serie)
	 * @param string $Titulo Título del gráfico (default: 'Gráfico')
	 *
	 * @return string HTML + JavaScript necesario para renderizar el gráfico
	 *
	 * @example
	 * ```php
	 * $Graphics = new UIWidgetsGraphics();
	 * $Graphics->generateApexRadialBar(
	 *     [45, 60, 30],
	 *     'Mi Gráfico'
 	 * ```
	 */
	public function generateApexRadialBar($Valores, $Titulo = 'Gráfico') {

		// Delegar a generateApexChart
		return $this->generateApexChart(
			array_combine(range(1, count($Valores)), $Valores),
			'radialBar',
			$Titulo
		);

	}

	/******************************************************************************/
	/**
	 * Genera un sparkline (línea minúscula) utilizando ApexCharts.
	 *
	 * @param array  $Valores Arreglo de valores
	 * @param array  $Opciones Opciones adicionales: 'color'
	 *
	 * @return string HTML + JavaScript necesario para renderizar el gráfico
	 *
	 * @example
	 * ```php
	 * $Graphics = new UIWidgetsGraphics();
	 * $Graphics->generateSparkline([10, 20, 15, 25, 30], ['color' => 'primary']);
 	 *```
	 */
	public function generateSparkline($Valores, $Opciones = []) {

		// Delegar a generateApexChart
		$color = $Opciones['color'] ?? 'primary';
		return $this->generateApexChart(
			array_combine(range(1, count($Valores)), $Valores),
			'line',
			'',
			50
		);

	}

}
