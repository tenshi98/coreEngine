<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class UIWidgetsPreview {

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
	 * $Preview = new UIWidgetsPreview();
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
	 * Genera una vista previa de los documentos según su extensión (imagen, Office, PDF, audio, video, texto).
	 *
	 * Para archivos no previsualizables (comprimidos, multimedia no soportado o extensión
	 * desconocida) imprime una alerta con el enlace de descarga. Si $BaseURL o $File vienen
	 * vacíos imprime una alerta y detiene la ejecución (exit).
	 *
	 * @param string $BaseURL La dirección base del sitio.
	 * @param string $Route   La ruta al archivo, a partir de la dirección base.
	 * @param string $File    Nombre del archivo.
	 *
	 * @return void Imprime la vista previa del archivo (o una alerta de error).
	 *
	 * @example
	 * ```php
	 * $Preview = new UIWidgetsPreview();
	 * $Preview->previewDocs('https://mi-sitio.cl', 'upload', 'informe.pdf'); // imprime la vista previa del PDF
	 * ```
	 */
	public function previewDocs($BaseURL, $Route, $File){

		/**********************  Validaciones   **********************/
        if ($BaseURL === null || trim((string)$BaseURL) === '') {
            echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la Dirección base del archivo.');
			exit;
        }
        if ($File === null || trim((string)$File) === '') {
            echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el Nombre del archivo.');
			exit;
        }

		/********************** Si todo esta ok **********************/
		/****************************************/
		//se verifican las extensiones
		$exten  = 'JPG,jpg,jpeg,gif,png,bmp';           //Imagenes
		$exten .= ',doc,docx,xls,xlsx,ppt,pptx';        //archivos microsoft office
		$exten .= ',odt,odp,ods';                       //archivos libre office
		$exten .= ',pdf';                               //pdf
		$exten .= ',mp3,oga,wav';                       //Audio
		$exten .= ',mp4,webm,ogv,mp2,mpeg,mpg,mov,avi'; //Video
		$exten .= ',txt,rtf';                           //texto plano
		$exten .= ',gz,gzip,7Z,zip,rar';                //Archivos Comprimidos

		/****************************************/
		//Se verifica si el archivo dado esta dentro de los permitidos
		$Extension  = pathinfo($File, PATHINFO_EXTENSION);
		$num_files  = glob($File.".{".$exten."}", GLOB_BRACE);

		/****************************************/
		//Se genera ruta del archivo
		$UrlToFile = '';
		if(isset($BaseURL)&&$BaseURL!=''){ $UrlToFile .= rtrim($BaseURL,'/').'/';}
		if(isset($Route)&&$Route!=''){     $UrlToFile .= $Route.'/';}
		if(isset($File)&&$File!=''){       $UrlToFile .= $File;}

		/****************************************/
		//Se genera ruta del archivo
		$PathToFile = '';
		if(isset($Route)&&$Route!=''){     $PathToFile .= $Route.'/';}
		if(isset($File)&&$File!=''){       $PathToFile .= $File;}

		/****************************************/
		//Se agrega estilo
		$input = '
		<style>
			.preview_img {width: 100%;height: auto;padding: 0;margin: 0;}
			.preview_iframe {width: 100%;height: 600px;padding: 0;margin: 0;float:right;}
		</style>';

		//Si existen archivos
		if($num_files > 0){
			//ejecuto segun su extension
			switch($Extension){
				/**************************************************/
				//Si son imagenes
				case 'JPG'; case 'jpg'; case 'jpeg'; case 'gif'; case 'png'; case 'bmp';
					$input .= '<img class="preview_img square-rounded-2 w-100" src="'.$UrlToFile.'" />';
				break;
				/**************************************************/
				//Si son archivos microsoft office
				case 'doc'; case 'docx'; case 'xls'; case 'xlsx'; case 'ppt'; case 'pptx';
					$input .= '
					<iframe class="preview_iframe" src="https://view.officeapps.live.com/op/embed.aspx?src='.$UrlToFile.'" frameborder="0">
						<a target="_blank" rel="noopener noreferrer" href="'.$UrlToFile.'">Descargar Documento</a>
					</iframe>';
				break;
				/**************************************************/
				//Si son archivos open office y pdf
				case 'odt'; case 'odp'; case 'ods'; case 'pdf';
					$input .= '<iframe class="preview_iframe" src="'.$BaseURL.'/vendor/ViewerJS/#../../'.$PathToFile.'" allowfullscreen webkitallowfullscreen></iframe>';
				break;
				/**************************************************/
				//Si son archivos de audio
				case 'mp3';
					$input .= '
					<link rel="stylesheet" type="text/css" href="'.$BaseURL.'/vendor/audio_player/css/style.css">
					<div class="audio green-audio-player">
						<div class="loading">
							<div class="spinner"></div>
						</div>
						<div class="play-pause-btn">
							<svg xmlns="https://www.w3.org/2000/svg" width="18" height="24" viewBox="0 0 18 24">
								<path fill="#566574" fill-rule="evenodd" d="M18 12L0 24V0" class="play-pause-icon" id="playPause"/>
							</svg>
						</div>

						<div class="controls">
							<span class="current-time">0:00</span>
							<div class="slider" data-direction="horizontal">
								<div class="progress">
									<div class="pin" id="progress-pin" data-method="rewind"></div>
								</div>
							</div>
							<span class="total-time">0:00</span>
						</div>

						<div class="volume">
							<div class="volume-btn">
								<svg xmlns="https://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
									<path fill="#566574" fill-rule="evenodd" d="M14.667 0v2.747c3.853 1.146 6.666 4.72 6.666 8.946 0 4.227-2.813 7.787-6.666 8.934v2.76C20 22.173 24 17.4 24 11.693 24 5.987 20 1.213 14.667 0zM18 11.693c0-2.36-1.333-4.386-3.333-5.373v10.707c2-.947 3.333-2.987 3.333-5.334zm-18-4v8h5.333L12 22.36V1.027L5.333 7.693H0z" id="speaker"/>
								</svg>
							</div>
							<div class="volume-controls hidden">
								<div class="slider" data-direction="vertical">
									<div class="progress">
										<div class="pin" id="volume-pin" data-method="changeVolume"></div>
									</div>
								</div>
							</div>
						</div>

						<audio crossorigin>
							<source src="'.$UrlToFile.'">
						</audio>
					</div>
					<script src="'.$BaseURL.'/vendor/audio_player/js/index.js"></script>';
				break;
				/**************************************************/
				//Si son archivos de video
				case 'mp4'; case 'webm'; case 'ogv';
					$input .= '
					<link href="'.$BaseURL.'/vendor/video_player/video-js.min.css" rel="stylesheet">
					<script src="'.$BaseURL.'/vendor/video_player/ie8/videojs-ie8.min.js"></script>
					<script src="'.$BaseURL.'/vendor/video_player/video.min.js"></script>
					<style> .video-js .vjs-big-play-button { visibility: hidden !important; } </style>
					<video id="video_1" class="video-js vjs-default-skin" controls preload="none" width="640" height="264" poster="'.$BaseURL.'/vendor/video_player/img/video-thumbnail.png" data-setup="{}">';
						switch ($Extension) {
							case 'mp4':  $input .= '<source src="'.$UrlToFile.'" type="video/mp4">'; break;
							case 'webm': $input .= '<source src="'.$UrlToFile.'" type="video/webm">'; break;
							case 'ogv':  $input .= '<source src="'.$UrlToFile.'" type="video/ogg">'; break;
						}
						$input .= '<p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="https://videojs.com/html5-video-support/" target="_blank" rel="noopener noreferrer">supports HTML5 video</a></p>
					</video>';
				break;
				/**************************************************/
				//Si son archivos de texto plano
				case 'txt'; case 'rtf';
					$archivo = file_get_contents($UrlToFile); //Guardamos archivo.txt en $archivo
					$archivo = ucfirst($archivo);             //Le damos un poco de formato
					$archivo = nl2br($archivo);               //Transforma todos los saltos de linea en tag <br/>
					$input   = $archivo;
				break;
				/**************************************************/
				//Si son archivos comprimidos
				case 'gz'; case 'gzip'; case '7Z'; case 'zip'; case 'rar';
					$data  = 'No se pueden previsualizar los archivos comprimidos '.$Extension.', descarguelos presionando <a href="'.$UrlToFile.'" class="">aqui</a>';
					$input = $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, $data);
				break;
				/**************************************************/
				//Si son archivos no reproducibles por los reproductores
				case 'mp2'; case 'mpeg'; case 'mpg'; case 'mov'; case 'avi'; case 'oga'; case 'wav';
					$data  = 'No se pueden previsualizar los archivos multimedia '.$Extension.', descarguelos presionando <a href="'.$UrlToFile.'" class="">aqui</a>';
					$input = $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, $data);
				break;
				/**************************************************/
				//excepcion
				default;
					$data  = 'No esta soportada la previsualizacion para los archivos '.$Extension.', para descargar el archivo presione <a href="'.$UrlToFile.'" class="">aqui</a>';
					$input = $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, $data);
				break;
			}

		}else{
			if(isset($UrlToFile)&&$UrlToFile!=''){
				$data  = 'No esta soportada la previsualizacion, para descargar el archivo presione <a href="'.$UrlToFile.'" class="">aqui</a>';
				$input = $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, $data);
			}else{
				$data  = 'El Archivo a previsualizar no existe';
				$input = $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, $data);
			}
		}

		/**********************/
		//Imprimir dato
		echo $input;

	}

	/******************************************************************************/
	/**
	 * Previsualiza un PDF embebiéndolo con PDFObject en el div indicado.
	 *
	 * Si $idDiv, $Route o $BASE vienen vacíos imprime una alerta y detiene la ejecución (exit).
	 *
	 * @param string $idDiv Identificador del div.
	 * @param string $Route Ruta de acceso del archivo.
	 * @param string $BASE  Ruta de la raíz del sitio.
	 *
	 * @return string HTML del visor PDF embebido (div + script PDFObject + estilos).
	 *
	 * @example
	 * ```php
	 * $Preview = new UIWidgetsPreview();
	 * echo $Preview->preview_pdf('Pdf_viewer', 'upload/archivo.pdf', 'https://mi-sitio.cl');
	 * ```
	 */
	public function preview_pdf($idDiv, $Route, $BASE){

		/**********************  Validaciones   **********************/
        if ($idDiv === null || trim((string)$idDiv) === '') {
            echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el identificador.');
			exit;
        }
        if ($Route === null || trim((string)$Route) === '') {
            echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la Ruta de acceso del archivo.');
			exit;
        }
        if ($BASE === null || trim((string)$BASE) === '') {
            echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la Ruta de la raiz del sitio.');
			exit;
        }

		/********************** Si todo esta ok **********************/
		$input = '
		<div id="'.$idDiv.'"></div>
		<script src="'.$BASE.'/vendor/PDFObject/pdfobject.js"></script>
		<script>PDFObject.embed("'.$Route.'", "#'.$idDiv.'");</script>
		<style>
			.pdfobject-container { height: 500px;}
			.pdfobject { border: 1px solid #666; }
		</style>';

		/**********************/
		//Imprimir dato
		return $input;

	}


}
