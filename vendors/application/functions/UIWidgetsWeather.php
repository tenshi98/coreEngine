<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class UIWidgetsWeather {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                 Instancias                                                      */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	//Definiciones
	private $Alertas;

	/******************************************************************************/
	/**
	 * Crea las instancias internas que la clase necesita: el proveedor de alertas de UIWidgetsCommon.
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * $Weather = new UIWidgetsWeather();
	 * ```
	 */
	public function __construct() {
		$this->Alertas = new UIWidgetsCommon();
	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Publicos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	/**
	 * Genera elementos para mostrar la previsión meteorológica (11 variantes de widget).
	 *
	 * $Options['Type'] (1..11, default 1) selecciona la variante visual; el widget carga los
	 * datos en cliente vía la clase WeatherWidget con $Options['BASE'], la latitud y la
	 * longitud (defaults -33.45 / -70.66, Santiago). Un $Type fuera de catálogo imprime una
	 * alerta y detiene la ejecución (exit).
	 *
	 * @param array $Options Arreglo con las claves 'BASE' (ruta raíz), 'Type' (1..11), 'latitude' y 'longitude'.
	 *
	 * @return void Imprime el widget meteorológico.
	 *
	 * @example
	 * ```php
	 * $Weather = new UIWidgetsWeather();
	 * $Options = [
	 *     'BASE'      => 'https://mi-sitio.cl', // Ruta de la raíz del sitio
	 *     'Type'      => 1,                      // Tipo de widget
	 *     'latitude'  => -33.45,                 // Latitud
	 *     'longitude' => -70.66,                 // Longitud
	 * ];
	 * $Weather->widget_meteo($Options); // imprime el widget de clima
	 * ```
	 */
	public function widget_meteo($Options){

		/**********************  Validaciones   **********************/
		//se definen las opciones disponibles
		$tipos = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11);
		//Validaciones
		if(isset($Options['Type'])&&!in_array($Options['Type'], $tipos)){  echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'La configuracion $type entregada no esta dentro de las opciones.'); exit;}

		/**********************  Definiciones   **********************/
		$Type       = $Options['Type'] ?? 1;
		$latitude   = $Options['latitude'] ?? -33.45;
		$longitude  = $Options['longitude'] ?? -70.66;
		$widget     = '';

		/********************** Si todo esta ok **********************/
		switch ($Type) {
			/****************************************/
			case 1:
				$widget  = '
				<div class="weatherWidget_v1 loadMeteo_'.$Type.'">
					<div class="card weather-widget shadow-sm">
						<div class="card-body">
							<div class="d-flex justify-content-between mb-3">
								<h5 class="mb-0 weatherTitle">Clima</h5>
							</div>
							<!-- LOADER -->
							<div class="text-center py-4 weatherLoader">
								<div class="spinner-border text-primary"></div>
								<div class="small text-muted mt-2">Obteniendo clima...</div>
							</div>
							<!-- CONTENIDO -->
							<div class="weatherContent">
								<div class="row align-items-center mb-4">
									<div class="col-md-6 text-center">
										<img class="weather-main-icon weatherIcon">
									</div>
									<div class="col-md-6">
										<div class="weather-temp weatherTemp"></div>
										<!-- MIN MAX -->
										<div class="weather-minmax">
											<span class="weatherMin"></span> / <span class="weatherMax"></span>
										</div>
										<div class="weather-extra">
											<div>💧 Lluvia: <span class="weatherRain"></span>%</div>
											<div>💨 Viento: <span class="weatherWind"></span> km/h</div>
											<div>💦 Humedad: <span class="weatherHumidity"></span>%</div>
										</div>
									</div>
								</div>
								<div class="row text-center mt-3 weatherWeek"></div>
							</div>
						</div>
					</div>
				</div>
				<script>
					document.querySelectorAll(".loadMeteo_'.$Type.'").forEach(el=>{
						new WeatherWidget(el, "'.$Options['BASE'].'", '.$Options['Type'].', '.$latitude.', '.$longitude.');
					});
				</script>';
				break;
			/****************************************/
			case 2:
				$widget  = '
				<div class="weatherWidget_v1 loadMeteo_'.$Type.'">
					<div class="card weather-widget shadow-sm">
						<div class="card-body">
							<div class="d-flex justify-content-between mb-3">
								<h5 class="mb-0 weatherTitle">Clima</h5>
							</div>
							<!-- LOADER -->
							<div class="text-center py-4 weatherLoader">
								<div class="spinner-border text-primary"></div>
								<div class="small text-muted mt-2">Obteniendo clima...</div>
							</div>
							<!-- CONTENIDO -->
							<div class="weatherContent">
								<div class="row align-items-center mb-4">
									<div class="col-md-6 text-center">
										<img class="weather-main-icon weatherIcon">
									</div>
									<div class="col-md-6">
										<div class="weather-temp weatherTemp"></div>
										<!-- MIN MAX -->
										<div class="weather-minmax">
											<span class="weatherMin"></span> / <span class="weatherMax"></span>
										</div>
										<div class="weather-extra">
											<div>💧 Lluvia: <span class="weatherRain"></span>%</div>
											<div>💨 Viento: <span class="weatherWind"></span> km/h</div>
											<div>💦 Humedad: <span class="weatherHumidity"></span>%</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<script>
					document.querySelectorAll(".loadMeteo_'.$Type.'").forEach(el=>{
						new WeatherWidget(el, "'.$Options['BASE'].'", '.$Options['Type'].', '.$latitude.', '.$longitude.');
					});
				</script>';
				break;
			/****************************************/
			case 3:
				$widget  = '
				<div class="weatherWidget_v1 loadMeteo_'.$Type.'">
					<div class="card weather-widget shadow-sm">
						<div class="card-body">
							<div class="d-flex justify-content-between mb-3">
								<h5 class="mb-0 weatherTitle">Clima</h5>
							</div>
							<!-- LOADER -->
							<div class="text-center py-4 weatherLoader">
								<div class="spinner-border text-primary"></div>
								<div class="small text-muted mt-2">Obteniendo clima...</div>
							</div>
							<!-- CONTENIDO -->
							<div class="weatherContent">
								<div class="row text-center mt-3 weatherWeek"></div>
							</div>
						</div>
					</div>
				</div>
				<script>
					document.querySelectorAll(".loadMeteo_'.$Type.'").forEach(el=>{
						new WeatherWidget(el, "'.$Options['BASE'].'", '.$Options['Type'].', '.$latitude.', '.$longitude.');
					});
				</script>';
				break;
			/****************************************/
			case 4:
				$widget  = '
				<div class="weatherWidget_v2 loadMeteo_'.$Type.'">
					<div class="card text-body" style=" border-radius: 35px;">
						<div class="card-body p-4">
							<!-- LOADER -->
							<div class="text-center py-4 weatherLoader">
								<div class="spinner-border text-primary"></div>
								<div class="small text-muted mt-2">Obteniendo clima...</div>
							</div>
							<!-- CONTENIDO -->
							<div class="weatherContent">
								<div class="d-flex"><h6 class="flex-grow-1 weatherTitle">Clima</h6></div>
								<div class="d-flex flex-column text-center mt-5 mb-4">
									<h6 class="display-4 mb-0 font-weight-bold weatherTemp"></h6>
									<span class="small" style="color: #868B94"><span class="weatherMin"></span> / <span class="weatherMax"></span></span>
								</div>
								<div class="d-flex align-items-center">
									<div class="flex-grow-1" style="font-size: 1rem;">
										<div>💧 Lluvia: <span class="ms-1 weatherRain"></span>%</div>
										<div>💨 Viento: <span class="ms-1 weatherWind"></span> km/h</div>
										<div>💦 Humedad: <span class="ms-1 weatherHumidity"></span>%</div>
									</div>
									<div><img width="100px" class="weatherIcon"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<script>
					document.querySelectorAll(".loadMeteo_'.$Type.'").forEach(el=>{
						new WeatherWidget(el, "'.$Options['BASE'].'", '.$Options['Type'].', '.$latitude.', '.$longitude.');
					});
				</script>';
				break;
			/****************************************/
			case 5:
				$widget  = '
				<div class="weatherWidget_v3 loadMeteo_'.$Type.'">
					<div class="card" style="border-radius: 35px;border: none;">
						<div class="bg-image" style="border-radius: 35px;">
							<img src="'.$Options['BASE'].'/img/meteo/draw1.jpg" class="card-img" alt="weather" style="border-radius: 35px;" />
						</div>
						<div class="card-img-overlay text-dark p-5" style="border-radius: 35px;">
							<!-- LOADER -->
							<div class="text-center py-4 weatherLoader">
								<div class="spinner-border text-primary"></div>
								<div class="small text-muted mt-2">Obteniendo clima...</div>
							</div>
							<!-- CONTENIDO -->
							<div class="weatherContent">

								<h4 class="mb-0 weatherTitle">Clima</h4>
								<p class="display-2 my-3 weatherTemp"></p>
								<p class="mb-2"><span class="weatherMin"></span> / <span class="weatherMax"></span></p>
								<div class="flex-grow-1" style="font-size: 1rem;">
									<div>
									💧 <span class="ms-1 weatherRain"></span>% /
									💨 <span class="ms-1 weatherWind"></span> km/h /
									💦 <span class="ms-1 weatherHumidity"></span>%
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<script>
					document.querySelectorAll(".loadMeteo_'.$Type.'").forEach(el=>{
						new WeatherWidget(el, "'.$Options['BASE'].'", '.$Options['Type'].', '.$latitude.', '.$longitude.');
					});
				</script>';
				break;
			/****************************************/
			case 6:
				$widget  = '
				<div class="weatherWidget_v4 loadMeteo_'.$Type.'">
					<!-- LOADER -->
					<div class="text-center py-4 weatherLoader">
						<div class="spinner-border text-primary"></div>
						<div class="small text-muted mt-2">Obteniendo clima...</div>
					</div>
					<!-- CONTENIDO -->
					<div class="weatherContent">
						<div class="weather-card">
							<div class="top" style="background: url(\''.$Options['BASE'].'/img/meteo/draw1.jpg\') no-repeat;">
								<div class="wrapper">
									<h1 class="heading weatherTitle">Clima</h1>
									<h3 class="location"><span class="weatherMin"></span> / <span class="weatherMax"></span></h3>
									<p class="temp-value weatherTemp"></p>
								</div>
							</div>
							<div class="bottom">
								<div class="wrapper">
									<ul class="forecast weatherWeek"></ul>
								</div>
							</div>
						</div>
					</div>
				</div>
				<script>
					document.querySelectorAll(".loadMeteo_'.$Type.'").forEach(el=>{
						new WeatherWidget(el, "'.$Options['BASE'].'", '.$Options['Type'].', '.$latitude.', '.$longitude.');
					});
				</script>';
				break;
			/****************************************/
			case 7:
				$widget  = '
				<div class="weatherWidget_v4 loadMeteo_'.$Type.'">
					<!-- LOADER -->
					<div class="text-center py-4 weatherLoader">
						<div class="spinner-border text-primary"></div>
						<div class="small text-muted mt-2">Obteniendo clima...</div>
					</div>
					<!-- CONTENIDO -->
					<div class="weatherContent">
						<div class="weather-card">
							<div class="top" style="background: url(\''.$Options['BASE'].'/img/meteo/draw1.jpg\') no-repeat;">
								<div class="wrapper">
									<h1 class="heading weatherTitle">Clima</h1>
									<h3 class="location"><span class="weatherMin"></span> / <span class="weatherMax"></span></h3>
									<p class="temp-value weatherTemp"></p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<script>
					document.querySelectorAll(".loadMeteo_'.$Type.'").forEach(el=>{
						new WeatherWidget(el, "'.$Options['BASE'].'", '.$Options['Type'].', '.$latitude.', '.$longitude.');
					});
				</script>';
				break;
			/****************************************/
			case 8:
				$widget  = '
				<div class="weatherWidget_v4 loadMeteo_'.$Type.'">
					<!-- LOADER -->
					<div class="text-center py-4 weatherLoader">
						<div class="spinner-border text-primary"></div>
						<div class="small text-muted mt-2">Obteniendo clima...</div>
					</div>
					<!-- CONTENIDO -->
					<div class="weatherContent">
						<div class="weather-card">
							<div class="bottom">
								<div class="wrapper">
									<ul class="forecast weatherWeek"></ul>
								</div>
							</div>
						</div>
					</div>
				</div>
				<script>
					document.querySelectorAll(".loadMeteo_'.$Type.'").forEach(el=>{
						new WeatherWidget(el, "'.$Options['BASE'].'", '.$Options['Type'].', '.$latitude.', '.$longitude.');
					});
				</script>';
				break;
			/****************************************/
			case 9:
				$widget  = '
				<div class="weatherWidget_v5 loadMeteo_'.$Type.'">
					<div id="card" class="weater">
						<!-- LOADER -->
						<div class="text-center py-4 weatherLoader">
							<div class="spinner-border text-primary"></div>
							<div class="small text-muted mt-2">Obteniendo clima...</div>
						</div>
						<!-- CONTENIDO -->
						<div class="weatherContent">
							<div class="city-selected">
								<article>
									<div class="info">
										<div class="city weatherTitle">Clima</div>
										<div class="night"><span class="weatherMin"></span> / <span class="weatherMax"></span></div>
										<div class="temp weatherTemp"></div>
										<div class="wind">
											<div>💧 Lluvia: <span class="ms-1 weatherRain"></span>%</div>
											<div>💨 Viento: <span class="ms-1 weatherWind"></span> km/h</div>
											<div>💦 Humedad: <span class="ms-1 weatherHumidity"></span>%</div>
										</div>
									</div>
									<div class="icon">
										<img class="weather-main-icon weatherIcon">
									</div>
								</article>
							</div>
							<div class="days">
								<div class="row row-no-gutter weatherWeek"></div>
							</div>
						</div>
					</div>
				</div>
				<script>
					document.querySelectorAll(".loadMeteo_'.$Type.'").forEach(el=>{
						new WeatherWidget(el, "'.$Options['BASE'].'", '.$Options['Type'].', '.$latitude.', '.$longitude.');
					});
				</script>';
				break;
			/****************************************/
			case 10:
				$widget  = '
				<div class="weatherWidget_v5 loadMeteo_'.$Type.'">
					<div id="card" class="weater">
						<!-- LOADER -->
						<div class="text-center py-4 weatherLoader">
							<div class="spinner-border text-primary"></div>
							<div class="small text-muted mt-2">Obteniendo clima...</div>
						</div>
						<!-- CONTENIDO -->
						<div class="weatherContent">
							<div class="city-selected">
								<article>
									<div class="info">
										<div class="city weatherTitle">Clima</div>
										<div class="night"><span class="weatherMin"></span> / <span class="weatherMax"></span></div>
										<div class="temp weatherTemp"></div>
										<div class="wind">
											<div>💧 Lluvia: <span class="ms-1 weatherRain"></span>%</div>
											<div>💨 Viento: <span class="ms-1 weatherWind"></span> km/h</div>
											<div>💦 Humedad: <span class="ms-1 weatherHumidity"></span>%</div>
										</div>
									</div>
									<div class="icon">
										<img class="weather-main-icon weatherIcon">
									</div>
								</article>
							</div>
						</div>
					</div>
				</div>
				<script>
					document.querySelectorAll(".loadMeteo_'.$Type.'").forEach(el=>{
						new WeatherWidget(el, "'.$Options['BASE'].'", '.$Options['Type'].', '.$latitude.', '.$longitude.');
					});
				</script>';
				break;
			/****************************************/
			case 11:
				$widget  = '
				<div class="weatherWidget_v5 loadMeteo_'.$Type.'">
					<div id="card" class="weater">
						<!-- LOADER -->
						<div class="text-center py-4 weatherLoader">
							<div class="spinner-border text-primary"></div>
							<div class="small text-muted mt-2">Obteniendo clima...</div>
						</div>
						<!-- CONTENIDO -->
						<div class="weatherContent">
							<div class="city-selected">
								<article>
									<div class="info">
										<div class="city weatherTitle">Clima</div>
									</div>
								</article>
							</div>
							<div class="days">
								<div class="row row-no-gutter weatherWeek"></div>
							</div>
						</div>
					</div>
				</div>
				<script>
					document.querySelectorAll(".loadMeteo_'.$Type.'").forEach(el=>{
						new WeatherWidget(el, "'.$Options['BASE'].'", '.$Options['Type'].', '.$latitude.', '.$longitude.');
					});
				</script>';
				break;

		}

		/**********************/
		//Imprimir dato
		echo $widget;

	}



}
