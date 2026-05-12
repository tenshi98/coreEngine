<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class UIWidgetsCommon {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                 Instancias                                                      */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	//Definiciones
	private $DataValidations;
	private $TemplateRender;

	/************************************************************************************************************/
	//Instancias
	public function __construct() {
		$this->DataValidations = new FunctionsDataValidations();
        $this->TemplateRender  = new TemplateRenderer();
	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
    /************************************************************************************************************/
	public function indicadores(){
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite obtener los indicadores desde el sitio del SII
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se obtiene dato
		* 	$Common->indicadores();
		*
		*=================================================    Parametros   =================================================
		* @return  string
		*===================================================================================================================
		*/

		/********************** Si todo esta ok **********************/
        /**********************  Retorno datos  **********************/
		//Variables
		$counter    = 1;
		$widgetData = '';

		// Colores predefinidos
		$arrColors = [
			1 => ['color' => 'text-color-blue'],
			2 => ['color' => 'text-color-green'],
			3 => ['color' => 'text-color-yellow'],
			4 => ['color' => 'text-color-red'],
		];

		/******************************************/
		//Se obtienen los datos
		$ServerWeb = new FunctionsServerSecurity();
		$XMLData   = $ServerWeb->getDataSIIindicadores('https://zeus.sii.cl/admin/rss/sii_ind_rss.xml');

		/******************************************/
		//Se verifica la recepcion de datos
		if($XMLData['success']===true){
			//Se recorren los datos
			foreach($XMLData['data'] as $data){
				//Imprimo los datos
				$widgetData .= '
				<a href="'.$data['link'].'" class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
					<span class="'.$arrColors[$counter]['color'].'">'.$data['description'].'</span>
					<span>'.$data['title'].'</span>
				</a>';
				//sumo
				$counter++;
				if($counter==5){$counter=1;}
			}
		}else{
			$widgetData = $XMLData['data'];
		}

		/******************************************/
		//Se agregan datos
		$this->TemplateRender->templatePath('../app/templates/Widgets/widgetsIndicadoresSII_1.php');
		$this->TemplateRender->assign('widgetData', $widgetData);

		/******************************************/
		//ejecucion
		echo $this->TemplateRender->render();

	}

	/************************************************************************************************************/
    public function acordeon($Options){
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite generar un widget tipo acordeon que se rellena en base a la info entregada
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime elemento
		*   $Options = [
		*		'type'     => 1,        //Tipo de acordeon
		*		'showOpen' => 8,        //elemento abierto, el id 0 mantiene todos cerrados
		*		'arrData'  => $arrData  //Arrego con los datos
		*	];
		* 	$Common->acordeon($Options);
		*
		*=================================================    Parametros   =================================================
		* @input   array   $Options    array con los datos
		* @return  string
		*===================================================================================================================
		*/

		/**********************    Variables    **********************/
		//Definicion de Valores
		$type     = $Options['type'] ?? 1;
		$showOpen = $Options['showOpen'] ?? 1;
		$arrData  = $Options['arrData'];

		/**********************  Definiciones   **********************/
		//Definir opciones válidas
		$validOptions = [
			'type'  => range(1, 2),
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $type,  'name' => 'type',  'label' => '$type'],
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, '', 6);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

        /********************** Si todo esta ok **********************/
        //Ejecucion si no hay errores
        if($errorn==0){

            //Selecciono el tipo de accordion
			$accordionType = ($type == 2) ? 'accordion-flush' : '';
			//Genero nombre unico
			$nameID = 'accordionId_'.uniqid();
			$Count  = 1;
            //Se crea el input
            $input = '<div class="accordion '.$accordionType.'" id="'.$nameID.'">';
				//Recorro
				foreach ( $arrData as $data ) {
					//Verifico si se muestra
					if($showOpen==$Count){$show='show';}else{$show='';}
					$input .= '
					<div class="accordion-item">
						<h2 class="accordion-header" id="heading_'.$nameID.'_'.$Count.'">
							<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_'.$nameID.'_'.$Count.'" aria-expanded="true" aria-controls="collapse_'.$nameID.'_'.$Count.'">
								'.$data['Title'].'
							</button>
						</h2>
						<div id="collapse_'.$nameID.'_'.$Count.'" class="accordion-collapse collapse '.$show.'" aria-labelledby="heading_'.$nameID.'_'.$Count.'" data-bs-parent="#'.$nameID.'">
							<div class="accordion-body">
								'.$data['Body'].'
							</div>
						</div>
					</div>';
					//Aumento contador
					$Count++;
				}
			$input .= '</div>';

            //Imprimir dato
            echo $input;
        }else{
			echo $alerts;
		}
    }

	/************************************************************************************************************/
    public function alertPostData($color, $type, $icon, $autoClose, $Text){
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite generar un cuadro de alerta personalizado
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime elemento
		* 	$Common->alertPostData(1,0,3,0, 'dato' );
		* 	$Common->alertPostData(2,1,2,0, '<strong>Dato:</strong>explicacion' );
		* 	$Common->alertPostData(3,2,1,0, '<strong>Dato 1:</strong>explicacion 1 <br/><strong>Dato 2:</strong>explicacion 2' );
		* 	$Common->alertPostData(4,3,0,0, 'bla' );
		*
		*=================================================    Parametros   =================================================
		* @input   int      $color           Color a utilizar
		* @input   int      $type            Tipo de mensaje (define el color de este)
		* @input   int      $icon            Icono a utilizar
		* @input   string   $autoClose       Configuracion para el cierre automatico del div
		* @input   string   $Text            Texto del mensaje (permite HTML)
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Definiciones   **********************/
		//Definir opciones válidas
		$validOptions = [
			'color'     => range(1, 8),
			'type'      => range(1, 6),
			'autoClose' => range(0, 1)
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $color,     'name' => 'color',     'label' => '$color'],
			['value' => $type,      'name' => 'type',      'label' => '$type'],
			['value' => $autoClose, 'name' => 'autoClose', 'label' => '$autoClose']
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, '', 6);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

        /********************** Si todo esta ok **********************/
        //Ejecucion si no hay errores
        if($errorn==0){
            //Selecciono el color de mensaje
            $options    = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'];
            $alertColor = $options[$color-1];

            //Selecciono el tipo de mensaje
            switch ($type) {
                case 1:  $alertType = 'alert-'.$alertColor;                                                     $alertIcon = '';                                                             break;//Default
                case 2:  $alertType = 'alert-'.$alertColor;                                                     $alertIcon = '<i class="bi bi-'.$icon.' me-1"></i>';                         break;//Default With Icon
                case 3:  $alertType = 'border-'.$alertColor;                                                    $alertIcon = '';                                                             break;//Outlined
                case 4:  $alertType = 'alert-'.$alertColor.' alert-white';                                      $alertIcon = '<div class="icon"><i class="bi bi-'.$icon.' me-1"></i></div>'; break;//Outlined With Icon
                case 5:  $alertType = 'border-'.$alertColor.' alert-information';                               $alertIcon = '';                                                             break;//Outlined info
                case 6:  $alertType = 'alert-'.$alertColor.' bg-'.$alertColor.'-gradient text-white border-0';  $alertIcon = '';                                                             break;//Default Solid Color
            	default: $alertType = 'alert-primary';                                                          $alertIcon = '';                                                             break;//valor Default
            }

            //Selecciono el tipo de mensaje
            $options  = ['', 'alert-dismissible'];
            $closeDiv = $options[$autoClose];

            //Selecciono el tipo de mensaje
            $options  = ['', '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'];
            $closeBtn = $options[$autoClose];

            //Se crea el input
            $input = '
            <div class="alert '.$alertType.' '.$closeDiv.' fade show" role="alert">
                '.$alertIcon.$Text.$closeBtn.'
            </div>';

            //Imprimir dato
            return $input;
        }else{
			echo $alerts;
		}
    }

	/************************************************************************************************************/
    public function printAlertData($color, $type, $icon, $autoClose, $Text){
		//Se imprime el dato
		echo $this->alertPostData($color, $type, $icon, $autoClose, $Text);
	}

	/************************************************************************************************************/
    public function tabs($Options){
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite generar un widget tipo tabs que se rellena en base a la info entregada
		*
		*=================================================    Modo de uso  =================================================
		*
		*   //se imprime elemento
		*   $Options = [
		*	   'type'      => 1,        //Tipo de tab
		*	   'justif'    => 1,        //Tipo de justificacion
		*	   'activeTab' => 1,        //Elemento a mostrar
		*	   'arrData'   => $arrData  //Arrego con los datos
		*   ];
		* 	$Common->tabs($Options);
		*
		*=================================================    Parametros   =================================================
		* @input   array   $Options    array con los datos
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Definiciones   **********************/
		//Definicion de Valores
		$type      = $Options['type'] ?? 1;
		$justif    = $Options['justif'] ?? 1;
		$activeTab = $Options['activeTab'] ?? 1;
		$arrData   = $Options['arrData'];

		//Definir opciones válidas
		$validOptions = [
			'type'   => range(1, 4),
			'justif' => range(1, 2),
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $type,   'name' => 'type',   'label' => '$type'],
			['value' => $justif, 'name' => 'justif', 'label' => '$justif'],
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, '', 6);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

        /********************** Si todo esta ok **********************/
        //Ejecucion si no hay errores
        if($errorn==0){

            //Selecciono el tipo de tab
            switch ($type) {
                case 1:  $tabType = '';                     break; //Default Tabs
                case 2:  $tabType = 'nav-tabs-inverted';    break; //Inverted Tabs
                case 3:  $tabType = 'nav-tabs-complement';  break; //Complement Tabs
                case 4:  $tabType = 'nav-tabs-bordered';    break; //Bordered Tabs
                default: $tabType = '';                     break; //valor default
            }
			//Selecciono la justificacion del tab
            switch ($justif) {
                case 1:  $justifContent = '';       $justifElem = '';          $wbuton = '';      break; //Normal
				case 2:  $justifContent = 'd-flex'; $justifElem = 'flex-fill'; $wbuton = 'w-100'; break; //Justificado
				default: $justifContent = '';       $justifElem = '';          $wbuton = '';      break; //valor default
            }
			//Genero nombre unico
			$nameID = 'tabId_'.uniqid();
			$Count  = 1;
            //Se crean elementos
            $title   = '<ul class="nav nav-tabs '.$tabType.' '.$justifContent.'" id="'.$nameID.'" role="tablist">';
			$content = '<div class="tab-content pt-2" id="'.$nameID.'_Content">';
			//Recorro
			foreach ( $arrData as $data ) {
				//Verifico si se muestra
				if($activeTab==$Count){$active='active';$show='show active';}else{$active='';$show='';}
				//Titulos
				$title .= '
				<li class="nav-item '.$justifElem.'" role="presentation">
					<button class="nav-link '.$active.' '.$wbuton.'" id="home-tab_'.$nameID.'_'.$Count.'" data-bs-toggle="tab" data-bs-target="#tab_'.$nameID.'_'.$Count.'" type="button" role="tab" aria-controls="tab_'.$nameID.'_'.$Count.'" aria-selected="true">'.$data['Title'].'</button>
				</li>';
				//Contenido
				$content .= '
				<div class="tab-pane fade '.$show.'" id="tab_'.$nameID.'_'.$Count.'" role="tabpanel" aria-labelledby="home-tab_'.$nameID.'_'.$Count.'">
					'.$data['Body'].'
				</div>';
				//Aumento contador
				$Count++;
			}
			//se cierran elementos
			$title .= '</ul>';
			$content .= '</div>';

            //Imprimir dato
            echo $title.$content;
        }else{
			echo $alerts;
		}
    }

	/************************************************************************************************************/
	public function previewDocs($BaseURL, $Route, $File){
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite generar una vista previa de los documentos
		*
		*=================================================    Modo de uso  =================================================
		* 	//se imprime elemento
		* 	$Common->previewDocs(BaseURL, $Route, $File);
		*
		*=================================================    Parametros   =================================================
		* @input   string   $BaseURL    La direccion base del sitio
		* @input   string   $Route      La ruta al archivo, a partir de la direccion base
		* @input   string   $File       Nombre del archivo
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!isset($BaseURL) || $BaseURL==''){  echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la Dirección base del archivo.');    exit;}
		if(!isset($Route) || $Route==''){      echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la Ruta a la carpeta contenedora.'); exit;}
		if(!isset($File) || $File==''){        echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el Nombre del archivo.');            exit;}

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
		$RutaCompleta = '';
		if(isset($BaseURL)&&$BaseURL!=''){ $RutaCompleta .= $BaseURL;}
		if(isset($Route)&&$Route!=''){     $RutaCompleta .= $Route;}
		if(isset($File)&&$File!=''){       $RutaCompleta .= '/'.$File;}

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
					$input .= '<img class="preview_img square-rounded-2 w-100" src="'.$RutaCompleta.'" />';
				break;
				/**************************************************/
				//Si son archivos microsoft office
				case 'doc'; case 'docx'; case 'xls'; case 'xlsx'; case 'ppt'; case 'pptx';
					$input .= '
					<iframe class="preview_iframe" src="https://view.officeapps.live.com/op/embed.aspx?src='.$RutaCompleta.'" frameborder="0">
						<a target="_blank" rel="noopener noreferrer" href="'.$RutaCompleta.'">Descargar Documento</a>
					</iframe>';
				break;
				/**************************************************/
				//Si son archivos open office y pdf
				case 'odt'; case 'odp'; case 'ods'; case 'pdf';
					$input .= '<iframe class="preview_iframe" src="'.$BaseURL.'/vendor/ViewerJS/#../../'.$Route.'/'.$File.'" allowfullscreen webkitallowfullscreen></iframe>';
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
							<source src="'.$RutaCompleta.'">
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
							case 'mp4':  $input .= '<source src="'.$RutaCompleta.'" type="video/mp4">'; break;
							case 'webm': $input .= '<source src="'.$RutaCompleta.'" type="video/webm">'; break;
							case 'ogv':  $input .= '<source src="'.$RutaCompleta.'" type="video/ogg">'; break;
						}
						$input .= '<p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="https://videojs.com/html5-video-support/" target="_blank" rel="noopener noreferrer">supports HTML5 video</a></p>
					</video>';
				break;
				/**************************************************/
				//Si son archivos de texto plano
				case 'txt'; case 'rtf';
					$archivo = file_get_contents($RutaCompleta); //Guardamos archivo.txt en $archivo
					$archivo = ucfirst($archivo);                //Le damos un poco de formato
					$archivo = nl2br($archivo);                  //Transforma todos los saltos de linea en tag <br/>
					$input   = $archivo;
				break;
				/**************************************************/
				//Si son archivos comprimidos
				case 'gz'; case 'gzip'; case '7Z'; case 'zip'; case 'rar';
					$data  = 'No se pueden previsualizar los archivos comprimidos '.$Extension.', descarguelos presionando <a href="'.$RutaCompleta.'" class="">aqui</a>';
					$input = $this->alertPostData(4, 4, 'exclamation-circle', 1, $data);
				break;
				/**************************************************/
				//Si son archivos no reproducibles por los reproductores
				case 'mp2'; case 'mpeg'; case 'mpg'; case 'mov'; case 'avi'; case 'oga'; case 'wav';
					$data  = 'No se pueden previsualizar los archivos multimedia '.$Extension.', descarguelos presionando <a href="'.$RutaCompleta.'" class="">aqui</a>';
					$input = $this->alertPostData(4, 4, 'exclamation-circle', 1, $data);
				break;
				/**************************************************/
				//excepcion
				default;
					$data  = 'No esta soportada la previsualizacion para los archivos '.$Extension.', para descargar el archivo presione <a href="'.$RutaCompleta.'" class="">aqui</a>';
					$input = $this->alertPostData(4, 4, 'exclamation-circle', 1, $data);
				break;
			}

		}else{
			if(isset($RutaCompleta)&&$RutaCompleta!=''){
				$data  = 'No esta soportada la previsualizacion, para descargar el archivo presione <a href="'.$RutaCompleta.'" class="">aqui</a>';
				$input = $this->alertPostData(4, 4, 'exclamation-circle', 1, $data);
			}else{
				$data  = 'El Archivo a previsualizar no existe';
				$input = $this->alertPostData(4, 4, 'exclamation-circle', 1, $data);
			}
		}

		/**********************/
		//Imprimir dato
		echo $input;

	}

	/************************************************************************************************************/
    public function responsiveTable($arrData, $FormCol){
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite generar un elemento que se asemeja a una tabla, pero es responsive
		*
		*=================================================    Modo de uso  =================================================
		* 	//se imprime elemento
		*	$arrData = [
		*		['Icon' => '','Titulo' => 'idCrud',     'Texto' => 'Texto Texto'],
		*		['Icon' => '','Titulo' => 'idUsuario',  'Texto' => 'Texto Texto'],
		*		['Icon' => '','Titulo' => 'Email',      'Texto' => 'Texto Texto'],
		*		['Icon' => '','Titulo' => 'Numero',     'Texto' => 'Texto Texto'],
		*		['Icon' => '','Titulo' => 'Rut',        'Texto' => 'Texto Texto'],
		*		['Icon' => '','Titulo' => 'Patente',    'Texto' => 'Texto Texto'],
		*		['Icon' => '','Titulo' => 'Fecha',      'Texto' => 'Texto Texto'],
		*		['Icon' => '','Titulo' => 'Hora',       'Texto' => 'Texto Texto'],
		*		['Icon' => '','Titulo' => 'Palabra',    'Texto' => 'Texto Texto'],
		*	];
		* 	$Common->responsiveTable($arrData, 8);
		*
		*=================================================    Parametros   =================================================
		* @input   array   $Options    array con los datos
		* @input   int     $FormCol    Ancho de la columna
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Definiciones   **********************/
		//se calcula tamaño de la columna
		$TextoCol  = $FormCol ?? 8;
		$TituloCol = 12 - $TextoCol;


		//Definir opciones válidas
		$validOptions = [
			'TextoCol'   => range(1, 12),
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $TextoCol,   'name' => 'TextoCol',   'label' => '$TextoCol'],
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, '', 6);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
        //Ejecucion si no hay errores
        if($errorn==0){
			//Variable vacia
			$input = '';
			//Recorro
			foreach ( $arrData as $data ) {
				/*************************************/
				//Verifico si existe un titulo
				if(isset($data['Titulo'])&&$data['Titulo']!=''){
					//Verifico si se envian datos para el icono
					$Icon = (isset($data['Icon']) && $data['Icon'] != '') ? $data['Icon'] : 'bi bi-chevron-double-right text-color-red';
					//Se genera input
					$input.= '
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-'.$TituloCol.' col-lg-'.$TituloCol.' col-xl-'.$TituloCol.' col-xxl-'.$TituloCol.' label ">
							<i class="'.$Icon.'"></i> '.$data['Titulo'].'
						</div>
						<div class="col-xs-12 col-sm-12 col-md-'.$TextoCol.' col-lg-'.$TextoCol.' col-xl-'.$TextoCol.' col-xxl-'.$TextoCol.'">
							'.$data['Texto'].'
						</div>
					</div>';
				/*************************************/
				//Verifico si no existe el titulo
				}else{
					$input.= '
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
							'.$data['Texto'].'
						</div>
					</div>';
				}
			}

			//Imprimir dato
			echo $input;
        }else{
			echo $alerts;
		}

    }

	/************************************************************************************************************/
	public function preview_pdf($idDiv, $Route, $BASE){
		/*
		*=================================================     Detalles    =================================================
		*
		* Previsualiza el PDF
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime elemento
		*	$Common->preview_pdf('Pdf_viewer', 'upload/archivo.pdf', 'www.google.com');
		*
		*=================================================    Parametros   =================================================
		* @input   String   $idDiv     Identificador del div
		* @input   String   $Route     Ruta de acceso del archivo
		* @input   String   $BASE      Ruta de la raiz del sitio
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!isset($idDiv) || $idDiv==''){  echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el identificador.');              exit;}
		if(!isset($Route) || $Route==''){  echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la Ruta de acceso del archivo.'); exit;}
		if(!isset($BASE) || $BASE==''){    echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la Ruta de la raiz del sitio.');  exit;}

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

	/************************************************************************************************************/
	public function widget_code_block($type, $code, $BASE){
		/*
		*=================================================     Detalles    =================================================
		*
		* Se muestra el visualizador de codigo fuente
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	$Common->widget_code_block($type, $code);
		*
		*=================================================    Parametros   =================================================
		* @input   String   $type     Tipo de elemento
		* @input   String   $code     Codigo a mostrar
		* @input   String   $BASE     Ruta de la raiz del sitio
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		//se definen las opciones disponibles
		$tipos = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13);
		//Validaciones
		if(!isset($type) || $type==''){  echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el Tipo de elemento.');                                             exit;}
		if(!isset($code) || $code==''){  echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el Codigo a mostrar.');                                             exit;}
		if(!in_array($type, $tipos)){    echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'La configuracion $type entregada en el codeblock no esta dentro de las opciones.'); exit;}

		/********************** Si todo esta ok **********************/
		//Si todo esta ok
		switch ($type) {
			case 1:  $tittle = 'Codigo HTML';       $class  = 'language-markup';     break;//HTML Code Example
			case 2:  $tittle = 'Codigo CSS';        $class  = 'language-css';        break;//CSS Code Example
			case 3:  $tittle = 'Codigo JavaScript'; $class  = 'language-javascript'; break;//JavaScript Code Example
			case 4:  $tittle = 'Codigo Python';     $class  = 'language-python';     break;//Python Code Example
			case 5:  $tittle = 'Codigo PHP';        $class  = 'language-php';        break;//PHP Code Example
			case 6:  $tittle = 'Codigo Handlebars'; $class  = 'language-handlebars'; break;//Handlebars Code Example
			case 7:  $tittle = 'Codigo Git';        $class  = 'language-git';        break;//Git Code Example
			case 8:  $tittle = 'Codigo Java';       $class  = 'language-java';       break;//JAVA Code Example
			case 9:  $tittle = 'Codigo C Like';     $class  = 'language-clike';      break;//C Like Code Example
			case 10: $tittle = 'Codigo C';          $class  = 'language-c';          break;//C Code Example
			case 11: $tittle = 'Codigo CSharp';     $class  = 'language-csharp';     break;//CSharp Code Example
			case 12: $tittle = 'Codigo SQL';        $class  = 'language-sql';        break;//SQL Code Example
			case 13: $tittle = 'Codigo PLSQL';      $class  = 'language-plsql';      break;//PLSQL Code Example
			default: $tittle = 'Nada';              $class  = 'language-markup';     break;//valor default
		}
		//Limpieza
		$code = str_replace('<','&lt;',$code);
		$code = str_replace('>','&gt;',$code);
		$code = str_replace('"','&quot;',$code);
		//Se genera widget
		$widget  = '<link rel="stylesheet" type="text/css" href="'.$BASE.'/vendor/prism/prism.css">';
		$widget .= '<script type="text/javascript"          src="'.$BASE.'/vendor/prism/prism.js"></script>';
		$widget .= '
		<div class="code-block">
			<h6>'.$tittle.'</h6>
			<pre style="padding-top: 0px;"><code class="'.$class.'">'.$code.'</code></pre>
		</div>';

		/**********************/
		//Imprimir dato
		echo $widget;

	}

	/************************************************************************************************************/
	public function widget_feed($Options){
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite generar un div que hace consumo de un feed de datos
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime elemento
		*   $Options = [
		*		'Titulo'        => 'Titulo', //Titulo del Feed
		*		'URL'           => 'URL',    //URL con la direccion del feed
		*		'BASE'          => 'URL',    //Ruta de la raiz del sitio
		*		'Identificador' => 'ID_xxx', //Identificador del div
		*		'MaxCount'      => '40',     //Numero maximo de datos a solicitar (depende del feed)
		*		'height'        => 200,      //Numero maximo de altura en px
		*		'ShowDesc'      => true,     //Mostrar de forma ascendente
		*		'ShowPubDate'   => false,    //Mostrar la fecha de publicacion
		*		'Type'          => 1,        //Tipo de feed
		*		'maxItems'      => 10,       //Numero de elementos para paginar
		*	];
		* 	$Common->widget_feed($Options);
		*
		*=================================================    Parametros   =================================================
		* @input   array   $Options    array con los datos
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		//se definen las opciones disponibles
		$tipos = array(1, 2);
		//Validaciones
		if(!isset($Options['URL']) || $Options['URL']==''){                                             echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la URL con la direccion del feed.');                exit;}
		if(isset($Options['MaxCount'])&&!$this->DataValidations->validarNumero($Options['MaxCount'])){  echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'El dato $MaxCount ingresado no es un numero.');                     exit;}
		if(isset($Options['height'])&&!$this->DataValidations->validarNumero($Options['height'])){      echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'El dato $height ingresado no es un numero.');                       exit;}
		if(isset($Options['Type'])&&!in_array($Options['Type'], $tipos)){                               echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'La configuracion $type entregada no esta dentro de las opciones.'); exit;}

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

	/************************************************************************************************************/
	public function widget_mejs_radio_player($BASE){
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite generar un reproductor de radio
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	$Common->widget_mejs_radio_player('www.google.com');
		*
		*=================================================    Parametros   =================================================
		* @input   string   $BASE   Ruta de la raiz del sitio
		* @return  string
		*===================================================================================================================
		*/

		/********************** Si todo esta ok **********************/
		/****************************************/
		//radios
		$arr = array();
		$arr[] = array('https://redirector.dps.live/biobiosantiago/aac/icecast.audio',                                               '2939.v10.png',         'Radio Bio Bio');
		$arr[] = array('https://playerservices.streamtheworld.com/api/livestream-redirect/CORAZONAAC.aac?dist=onlineradiobox',       '2940.v12.png',         'Radio Corazón');
		$arr[] = array('https://mdstrm.com/audio/5c8d6406f98fbf269f57c82c/live.m3u8',                                                '3545.v8.png',          'Play FM');
		$arr[] = array('https://playerservices.streamtheworld.com/api/livestream-redirect/ADN.mp3?dist=onlineradiobox',              '334.v17.png',          'ADN Radio');
		$arr[] = array('https://redirector.dps.live/cooperativafm/mp3/icecast.audio',                                                '2990.v21.png',         'Radio Cooperativa');
		$arr[] = array('https://unlimited4-us.dps.live/p7concepcion/mp3/icecast.audio',                                              '62835.v15.png',        'Radio Punto 7 Concepción');
		$arr[] = array('https://sp.tvcontrolcp.com:10905/;',                                                                         '63733.v12.png',        'Radio Kpop Star');
		$arr[] = array('https://playerservices.streamtheworld.com/api/livestream-redirect/FUTURO_SC?dist=onlineradiobox',            '2895.v8.png',          'Radio Futuro');
		$arr[] = array('https://playerservices.streamtheworld.com/api/livestream-redirect/ROCK_AND_POPAAC.aac?dist=onlineradiobox',  '2920.v8.png',          'Rock & Pop');
		$arr[] = array('https://playerservices.streamtheworld.com/api/livestream-redirect/IMAGINA_SC?dist=onlineradiobox',           '322.v8.png',           'Radio Imagina');
		$arr[] = array('https://onlineradiobox.com/json/cl/paloma/play?platform=web',                                                '3434.v16.png',         'Radio Paloma');
		$arr[] = array('https://unlimited1-us.dps.live/carolinatv/carolinatv.smil/playlist.m3u8',                                    '358.v20.png',          'Radio Carolina');
		$arr[] = array('https://stream.edelweiss.fm/radio/8040/radio.mp3',                                                           '3266.v19.png',         'Radio Mirador');
		$arr[] = array('https://onlineradiobox.com/json/cl/delosrecuerdos/play?platform=web',                                        '63830.v9.png',         'FM de los Recuerdos');
		$arr[] = array('https://mdstrm.com/audio/5c915497c6fd7c085b29169d/live.m3u8',                                                '2943.v6.png',          'Radio Oasis');
		$arr[] = array('https://stream.edelweiss.fm/radio/8000/radio.mp3',                                                           '63821.v15.png',        'Radio Edelweiss');
		$arr[] = array('https://onlineradiobox.com/json/cl/carabineros/play?platform=web',                                           '75629.v8.png',         'Radio Carabineros');
		$arr[] = array('https://audio1.tustreaming.cl/9020/stream',                                                                  '3655.v7.png',          'Mi Radio');
		$arr[] = array('https://mdstrm.com/audio/5c915724519bce27671c4d15/icecast.audio?property=radiobox',                          '2988.v8.png',          'Sonar 105.3 FM');
		$arr[] = array('https://unlimited4-us.dps.live/romantica/aac/icecast.audio',                                                 '307.v19.png',          'Radio Romantica');
		$arr[] = array('https://playerservices.streamtheworld.com/api/livestream-redirect/ACTIVA.mp3?dist=onlineradiobox',           '3124.v11.png',         'RadioActiva');
		$arr[] = array('https://playerservices.streamtheworld.com/api/livestream-redirect/FMDOS_SC?dist=onlineradiobox',             '2938.v8.png',          'FM Dos');
		$arr[] = array('https://onlineradiobox.com/json/cl/carnavalantofagasta/play?platform=web',                                   '3070.v10.png',         'Radio Carnaval');
		$arr[] = array('https://unlimited4-us.dps.live/universo/aac/icecast.audio',                                                  '306.v7.png',           'Universo');
		$arr[] = array('https://playerservices.streamtheworld.com/api/livestream-redirect/PUDAHUEL.mp3?dist=onlineradiobox',         '309.v9.png',           'Radio Pudahuel');
		$arr[] = array('https://playerservices.streamtheworld.com/api/livestream-redirect/CONCIERTOAAC.aac?dist=onlineradiobox',     '2894.v5.png',          'Concierto 88.5 FM');
		$arr[] = array('https://radio.trix.hosting:18094/;',                                                                         '63045.v13.png',        'Retroclásicos Radio');
		$arr[] = array('https://unlimited4-us.dps.live/agricultura/gotardis/audio/now/livestream1.m3u8',                             '318.v12.png',          'Radio Agricultura');
		$arr[] = array('https://stream10.usastreams.com:10998/;',                                                                    '2898.v16.png',         'El Conquistador');
		$arr[] = array('https://unlimited4-us.dps.live/disney/mp364k/icecast.audio',                                                 '62400.v11.png',        'Radio Disney');
		$arr[] = array('https://mdstrm.com/audio/5c915613519bce27671c4caa/live.m3u8',                                                '63666.v9.png',         'Tele 13 Radio');
		$arr[] = array('https://stream.festival.cl/1',                                                                               '313.v13.png',          'Radio Festival');
		$arr[] = array('https://playerservices.streamtheworld.com/api/livestream-redirect/LOS40_CHILEAAC.aac?dist=onlineradiobox',   '2941.v13.png',         'Los 40');
		$arr[] = array('https://centova.neonetwork.cl:9154/stream',                                                                  '63848.v9.png',         'Radio Lola');
		$arr[] = array('https://unlimited4-us.dps.live/digitalfm/aac/icecast.audio',                                                 '329.v13.png',          'Digital FM');
		$arr[] = array('https://xradiopanel.com/8004/stream',                                                                        '63092.v10.png',        'Radio 80s');
		$arr[] = array('https://onlineradiobox.com/json/cl/estacion247/play?platform=web',                                           '73087.v11.png',        'Radio Estación 24/7');
		$arr[] = array('https://streaming.conectaapp.cl/fmplus',                                                                     '3085.v6.png',          'Radio Plus FM');
		$arr[] = array('https://onlineradiobox.com/json/cl/scuraexitos8090s/play?platform=web',                                      '63095.v14.png',        'Radioscura Éxitos 80/90&amp;#39;s');
		$arr[] = array('https://kpopreplay.radioca.st//stream',                                                                      '63655.v8.png',         'Kpop Replay');
		$arr[] = array('https://sonic.portalfoxmix.club:7157/;',                                                                     '80313.v24.png',        'Radio Raol Retro');
		$arr[] = array('https://unlimited11-cl.dps.live/infinita/aac/icecast.audio',                                                 '321.v9.png',           'Infinita Radio');
		$arr[] = array('https://unlimited3-cl.dps.live/beethovenfm/gotardis/audio/now/livestream1.m3u8',                             '332.v10.png',          'Beethoven');
		$arr[] = array('https://onlineradiobox.com/json/cl/araucana/play?platform=web',                                              '3293.v10.png',         'Radio Araucana');
		$arr[] = array('https://onlineradiobox.com/json/cl/ritoque/play?platform=web',                                               '3570.v6.png',          'Radio Ritoque');
		$arr[] = array('https://sonic.portalfoxmix.cl:7045/;',                                                                       '3401.v9.png',          'Picarona Panguipulli');
		$arr[] = array('https://vintage.ice.infomaniak.ch/vintage.mp3',                                                              '63368.v7.png',         'Radio Vintage');
		$arr[] = array('https://stream.zenolive.com/p0ar2tuq98quv',                                                                  '80442.v4.png',         'Radio K-pop Music');
		$arr[] = array('https://unlimited4-us.dps.live/nostalgica/aac/icecast.audio',                                                '3111.v9.png',          'Radio Nostalgica');
		$arr[] = array('https://audio1.tustreaming.cl:10973/stream',                                                                 '3147.v12.png',         'Radio Corporacion');
		$arr[] = array('https://aac.noot.live/laclavebb.aac',                                                                        '63522.v12.png',        'Radio La Clave');
		$arr[] = array('https://sonic.portalfoxmix.cl:7034/live',                                                                    '3553.v6.png',          'FM Dance');
		$arr[] = array('https://onlineradiobox.com/json/cl/maxima/play?platform=web',                                                '62964.v13.png',        'Radio Máxima');
		$arr[] = array('https://unlimited3-cl.dps.live/duna/gotardis/audio/now/livestream1.m3u8',                                    '328.v12.png',          'Duna');
		$arr[] = array('https://streamuchile.teslati.com/liveruch',                                                                  '3081.v11.png',         'Radio Universidad de Chile');
		$arr[] = array('https://unlimited1-us.dps.live/fmtiempotv/fmtiempotv.smil/playlist.m3u8',                                    '324.v8.png',           'FM Tiempo');
		$arr[] = array('https://onlineradiobox.com/json/cl/mirasol/play?platform=web',                                               '63863.v8.png',         'Radio Mirasol');
		$arr[] = array('https://audio4.tustreaming.cl/8160/stream',                                                                  '63010.v13.png',        'Viña del Mar Classic');
		$arr[] = array('https://sonic.portalfoxmix.cl/8226/stream',                                                                  '80534.v7.png',         'Recuerdos Retro');
		$arr[] = array('https://us9.maindigitalstream.com/ssl/7389',                                                                 '1840.v10.png',         'Radio Sol');
		$arr[] = array('https://broadcast.radio247.net/radio/8100/stream',                                                           '3012.v11.png',         'Desierto FM');
		$arr[] = array('https://onlineradiobox.com/json/cl/rtl/play?platform=web',                                                   '3432.v17.png',         'Radio RTL Curicó');
		$arr[] = array('https://unlimited11-cl.dps.live/elcarbon/aac/icecast.audio',                                                 '63826.v10.png',        'Radio El Carbon');
		$arr[] = array('https://mdstrm.com/audio/5de7fdb07e2fde0798203821/live.m3u8',                                                '63379.v26.png',        'Rockaxis');
		$arr[] = array('https://rusach.janus.cl/playlist/stream.m3u8',                                                               '3543.v15.png',         'Radio USACH');
		$arr[] = array('https://onlineradiobox.com/json/cl/nahuel/play?platform=web',                                                '3324.v9.png',          'Radio Nahuel');
		$arr[] = array('https://onlineradiobox.com/json/cl/vln/play?platform=web',                                                   '69682.v11.png',        'VLN Radio');
		$arr[] = array('https://archi-us.digitalproserver.com/osorno-fm.aac',                                                        '3322.v6.png',          'Radio Sago');
		$arr[] = array('https://unlimited4-us.dps.live/positiva/aac/icecast.audio',                                                  '68190.v15.png',        'Radio Positiva');
		$arr[] = array('https://onlineradiobox.com/json/cl/powerplaydiscotheque/play?platform=web',                                  '63328.v9.png',         'Power Play Discotheque');
		$arr[] = array('https://sonando-us.digitalproserver.com/ucvradio',                                                           '62979.v9.png',         'UCV Radio');
		$arr[] = array('https://sonic.portalfoxmix.cl:7026/stream',                                                                  '63196.v10.png',        'Radio Fiesta Mix');
		$arr[] = array('https://onlineradiobox.com/json/cl/lavozdelacosta/play?platform=web',                                        '63841.v9.png',         'Radio La Voz de la Costa');
		$arr[] = array('https://streaming.conectaapp.cl/fmquiero',                                                                   '71461.v9.png',         'FM Quiero');
		$arr[] = array('https://onlineradiobox.com/json/cl/libra/play?platform=web',                                                 '62980.v9.png',         'Radio Libra');
		$arr[] = array('https://onlineradiobox.com/json/cl/codigometal/play?platform=web',                                           '58095.v9.png',         'Código Metal Radio');
		$arr[] = array('https://archi-us.digitalproserver.com/austral.aac',                                                          '3406.v6.png',          'Radio Austral');
		$arr[] = array('https://streaming.conectaapp.cl/canal95',                                                                    '3008.v6.png',          'Radio Canal 95');
		$arr[] = array('https://onlineradiobox.com/json/cl/dulce/play?platform=web',                                                 '3564.v7.png',          'Radio Dulce');
		$arr[] = array('https://portales.tustreamings1.cl/stream',                                                                   '3552.v7.png',          'Radio Portales');
		$arr[] = array('https://radiostreaming.cloudserverlatam.com/8088/stream',                                                    '74515.v5.png',         'Radio Beat 98.7 FM');
		$arr[] = array('https://onlineradiobox.com/json/cl/punto9/play?platform=web',                                                '62871.v14.png',        'Radio Punto 9');
		$arr[] = array('https://onlineradiobox.com/json/cl/azukar1079/play?platform=web',                                            '74095.v3.png',         'Radio Azukar 107.9 FM');
		$arr[] = array('https://onlineradiobox.com/json/cl/caramelo/play?platform=web',                                              '3230.v15.png',         'Radio Caramelo-Malleco');
		$arr[] = array('https://sonic-us.streaming-chile.com:7037/;',                                                                '63866.v25.png',        'Dossil Radio Chile');
		$arr[] = array('https://onlineradiobox.com/json/cl/sinfoniaonline/play?platform=web',                                        '63067.v16.png',        'Radio Sinfonia Online');
		$arr[] = array('https://onlineradiobox.com/json/cl/lagosdelsur/play?platform=web',                                           '79342.v7.png',         'FM Lagos del Sur');
		$arr[] = array('https://stream.zeno.fm/cpvysp4m4ceuv',                                                                       '76736.v21.png',        'World Hits Radio (Radio Hits Chile)');
		$arr[] = array('https://archi-us.digitalproserver.com/definitiva.aac',                                                       '314.v7.png',           'Radio Definitiva');
		$arr[] = array('https://audio4.tustreaming.cl/8130/stream',                                                                  '3551.v13.png',         'Radio Santiago');
		$arr[] = array('https://onlineradiobox.com/json/cl/contemporanea/play?platform=web',                                         '62974.v9.png',         'Radio Contemporánea');
		$arr[] = array('https://onlineradiobox.com/json/cl/toromondo/play?platform=web',                                             '63060.v10.png',        'ToroMondo');
		$arr[] = array('https://unlimited3-cl.dps.live/radiopaula/gotardis/audio/now/livestream1.m3u8',                              '2991.v8.png',          'Paula FM');
		$arr[] = array('https://radiox.tustreamings5.cl/stream',                                                                     '63636.v12.png',        'Radio X FM');
		$arr[] = array('https://radio.tvstream.cl/8008/stream',                                                                      '68735.v34.png',        'Radio Zona Activa');
		$arr[] = array('https://onlineradiobox.com/json/cl/folclordechile/play?platform=web',                                        '63373.v8.png',         'Radio Folclor De Chile');
		$arr[] = array('https://radio.saopaulo01.com.br/8188/stream',                                                                '62832.v11.png',        '94.1 FM Patagonia Radio');
		$arr[] = array('https://onlineradiobox.com/json/cl/sanbartolome/play?platform=web',                                          '3249.v8.png',          'Radio San Bartolome');
		$arr[] = array('https://onlineradiobox.com/json/cl/classica1063/play?platform=web',                                          '3352.v10.png',         'Radio Classica');
		$arr[] = array('https://centova.neonetwork.cl:9172/stream',                                                                  '3354.v8.png',          'Radio Reloncavi');
		$arr[] = array('https://onlineradiobox.com/json/cl/chileno/play?platform=web',                                               '63413.v7.png',         'Rock Chileno');
		$arr[] = array('https://stream.zeno.fm/ktmru7k741zuv',                                                                       '75973.v9.png',         'Radio Modelo');
		$arr[] = array('https://stream.zeno.fm/c16qw0esehruv',                                                                       '82795.v10.png',        'Radio Retrocadas');
		$arr[] = array('https://onlineradiobox.com/json/cl/congreso/play?platform=web',                                              '62981.v9.png',         'Radio Congreso');
		$arr[] = array('https://cp.streamchileno.cl/radio/8040/radio.mp3',                                                           '3252.v19.png',         'Radio Riquelme');
		$arr[] = array('https://onlineradiobox.com/json/cl/supersol/play?platform=web',                                              '3656.v6.png',          'Radio SuperSol');
		$arr[] = array('https://audio.streaminghd.cl:2000/stream/RadioPulso',                                                        '80554.v20.png',        'Radio Pulso');
		$arr[] = array('https://sonic.portalfoxmix.cl:7012/;',                                                                       '3335.v9.png',          'Radio La Palabra');
		$arr[] = array('https://onlineradiobox.com/json/cl/magiztral/play?platform=web',                                             '63528.v11.png',        'Radio Magiztral');
		$arr[] = array('https://onlineradiobox.com/json/cl/gabrielaonline/play?platform=web',                                        '63349.v11.png',        'Radio Gabriela On Line');
		$arr[] = array('https://onlineradiobox.com/json/cl/galaxia/play?platform=web',                                               '63512.v7.png',         'Radio Galaxia');
		$arr[] = array('https://onlineradiobox.com/json/cl/fiessta/play?platform=web',                                               '3465.v8.png',          'Radio Fiessta');
		$arr[] = array('https://archi-us.digitalproserver.com/portales-fm-valparaiso-vina-del-mar.aac',                              '72051.v5.png',         'Radio Portales de Valparaiso');
		$arr[] = array('https://onlineradiobox.com/json/cl/macarena997/play?platform=web',                                           '320.v10.png',          'Macarena');
		$arr[] = array('https://onlineradiobox.com/json/cl/dimension/play?platform=web',                                             '70347.v14.png',        'Dimensión Primavera FM');
		$arr[] = array('https://archi-us.digitalproserver.com/santa-maria-am.aac',                                                   '3194.v6.png',          'Radio Santa Maria');
		$arr[] = array('https://onlineradiobox.com/json/cl/futura/play?platform=web',                                                '62773.v9.png',         'Futura 100.7 FM');
		$arr[] = array('https://audio3.tustreaming.cl:10964/caramelosvicente',                                                       '62926.v13.png',        'Radio Caramelo 104.5 FM');
		$arr[] = array('https://onlineradiobox.com/json/cl/pauta/play?platform=web',                                                 '75624.v8.png',         'Pauta FM');
		$arr[] = array('https://estilofm.tustreamings2.cl/stream',                                                                   '3417.v9.png',          'Estilo FM');
		$arr[] = array('https://onlineradiobox.com/json/cl/azul/play?platform=web',                                                  '3571.v7.png',          'Radio Azul');
		$arr[] = array('https://mdstrm.com/audio/5d013e4bc8a64d0da420ced6/live.m3u8',                                                '63579.v10.png',        'Súbela Radio');
		$arr[] = array('https://cp.streamchileno.cl/radio/8130/radio.mp3',                                                           '3251.v6.png',          'Radio Pinamar');


		//Hoja de estilo
		$input ='
		<link rel="stylesheet prefetch" href="'.$BASE.'/vendor/mejs-player/build/mediaelementplayer.css">
		<link rel="stylesheet prefetch" href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
		';

		//se crea widget
		$input .='
		<div id="main-wrapper">
			<div class="player-wrapper">
				<audio id="audio" class="mejs__player" controls="controls" src="">
					Your browser does not support the audio format.
				</audio>
				<ul class="playlist custom-counter" id="list">';
					foreach ($arr as $prod) {
						$input .='
						<li>
						<div class="track-info">
								<img class="station__title__logo" src="'.$BASE.'/vendor/mejs-player/emisoras/'.$prod[1].'" alt="'.$prod[2].'" title="'.$prod[2].'">
								<a href="#" data-value="'.$prod[0].'">'.$prod[2].'</a>
							</div>
						</li>';
					}
				$input .='
				</ul>
			</div>
		</div>';

		//script
		$input .='
		<script src="'.$BASE.'/vendor/mejs-player/build/mediaelement-and-player.js"></script>
		<script >
			// Dynamic URL change
			list.onclick = function(e) {
			e.preventDefault();

			var elm = e.target;
			var audio = document.getElementById("audio");

			var source = document.getElementById("audio");
			source.src = elm.getAttribute("data-value");

			audio.load(); //call this to just preload the audio without playing
			audio.play(); //call this to play the song right away
			};
		</script>
		<style>
			/* Radio Player */
			#main-wrapper{padding:0;}
			#main-wrapper .player-wrapper{border-radius: 5px;box-shadow: 0 0 8px -1px rgba(0, 0, 0, 0.25);background-image: -webkit-linear-gradient(315deg, #FF5572, #FF7555);background-image: linear-gradient(135deg, #FF5572, #FF7555);overflow: hidden;margin: 0 auto;max-width:100%;width: 100%;padding: 0;border-radius:0;}
			#main-wrapper .player-wrapper .playlist {margin:0;padding:15px 15px 0 15px;height: 400px;overflow-x: auto;color:#fff;}
			#main-wrapper .player-wrapper .playlist li{ overflow: hidden;line-height: 20px;display: flex;padding: 10px 0;border-bottom: 1px solid rgba(230, 211, 211, 0.31);}
			#main-wrapper .player-wrapper .playlist li .track-info {display: inline-block;position: relative;line-height: 1.3em;width: 100%;font-weight:500;}
			#main-wrapper .player-wrapper .playlist li .track-info img { margin-right: 10px;border-radius: 3px;width: 90px;}
			#main-wrapper .player-wrapper .playlist li .track-info a{color: #fff;text-decoration:none;}
			.mejs__controls{height:60px;}
			.mejs__button.mejs__playpause-button.mejs__replay,.mejs__button.mejs__playpause-button.mejs__pause{background: #FFB00E;width: 40px;padding: 0 5px;border-radius: 50%;}
			.mejs__button.mejs__playpause-button.mejs__replay{background: #29cf54;}
			.mejs__button.mejs__playpause-button.mejs__play {background: #29cf54;width: 40px;padding: 0 5px;border-radius: 50%;}
			.mejs__time {box-sizing: content-box;color: #444;font-size: 15px;font-weight: bold;height: 24px;overflow: hidden;width: 50px;padding:16px 0;}
			.mejs__button > button  {display: block;padding: 0;border: 0;font-family: FontAwesome;font-size: 20px;color: #444;background: transparent!important;}
			.mejs__button.mejs__playpause-button.mejs__play button:before {content: "\f04b";color:#fff;}
			.mejs__button.mejs__playpause-button.mejs__pause button:before {content: "\f04c";color:#fff;}
			.mejs__button.mejs__playpause-button.mejs__replay button:before {content: "\f01e";color:#fff;}
			.mejs__button.mejs__volume-button.mejs__mute button:before {content: "\f028";}
			.mejs__button.mejs__volume-button.mejs__unmute button:before {content: "\f026";}
			.mejs__container {font-family: Segui Ui,Arial,serif;background-size: cover;position: relative;background:#fff;text-align: left;text-indent: 0;vertical-align: top;height: 80px!important;width: 100%!important;}
			.mejs__controls:not([style*="display: none"]) {background: none;}
			.mejs__time-total {background: rgb(212, 245, 221);margin: 5px 0 0;width: 100%;}
			span.mejs__time-current {background: #dedede;}
			span.mejs__time-loaded {background: #29cf54;}
			.mejs__time-handle-content {border: 4px solid rgba(255, 255, 255, 0.9);border-radius: 0;height: 10px;left: -5px;top: -4px;-webkit-transform: scale(0);-ms-transform: scale(0);transform: scale(0);width: 1px;}
			.mejs__horizontal-volume-total {background: rgb(41, 207, 84);height: 10px;top:14px;border-radius:0;}
		</style>';

		/**********************/
		//Imprimir dato
		echo $input;

	}

	/************************************************************************************************************/
	public function widget_radio_player($BASE, $Type){
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite generar un reproductor de radio
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	$Common->widget_radio_player('www.google.com');
		*
		*=================================================    Parametros   =================================================
		* @input   string   $BASE   Ruta de la raiz del sitio
		* @input   string   $Type   Tipo de radio a mostrar
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		//se definen las opciones disponibles
		$tipos = array(1, 2);
		//Validaciones
		//if(!isset($BASE) || $BASE==''){              echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la Ruta de la raiz del sitio.');                    exit;}
		if(!isset($Type) || $Type==''){              echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el tipo de configuracion.');                        exit;}
		if(isset($Type)&&!in_array($Type, $tipos)){  echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'La configuracion $type entregada no esta dentro de las opciones.'); exit;}

		/********************** Si todo esta ok **********************/
		$widget  = '
		<link type="text/css" rel="stylesheet" href="'.$BASE.'/vendor/radio_player/radio_player.css?get='.time().'"/>
		<div class="rp-wrap">
			<div class="card radio-card" style="border-radius:var(--rp-border-radius-lg) !important;background:var(--rp-color-background-primary)">
				<div class="card-body pb-2">
					<div class="d-flex align-items-center gap-3 mb-3">
						<div class="cover-box">
							<img id="mainCoverImg" alt="cover">
							<span id="mainCoverEmoji" style="font-size:28px">📻</span>
						</div>
						<div class="flex-grow-1 overflow-hidden">
							<div class="d-flex align-items-center gap-2 mb-1">
								<span class="live-dot" id="liveDot"></span>
								<span style="font-size:11px;color:var(--rp-color-text-secondary)" id="statusTxt">Sin reproducir</span>
							</div>
							<div style="font-size:16px;font-weight:500;color:var(--rp-color-text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis" id="mainName">Selecciona una emisora</div>
							<div style="font-size:12px;color:var(--rp-color-text-secondary)" id="mainGenre">—</div>
						</div>
						<span class="badge-band" id="mainBand">—</span>
					</div>
					<div class="d-flex align-items-center gap-2 mb-2">
						<button class="btn-ctrl" onclick="toggleTheme()" id="themeBtn" title="Cambiar tema">🌙</button>
						<button class="btn-ctrl" onclick="playPrev()" title="Anterior">&#9198;</button>
						<button class="btn-play-main" id="btnPlay" onclick="togglePlay()">&#9654;</button>
						<button class="btn-ctrl" onclick="playNext()" title="Siguiente">&#9197;</button>
						<button class="btn-ctrl" id="btnMute" onclick="toggleMute()">&#128266;</button>
						<input type="range" class="flex-grow-1" min="0" max="100" value="80" step="1" id="volSlider" oninput="setVol(this.value)" style="height:4px">
						<span style="font-size:11px;color:var(--rp-color-text-secondary);min-width:30px;text-align:right" id="volPct">80%</span>
					</div>';
					//Si es minireproductor
					if($Type==2){
						$widget  .= '
						<select class="station-select" id="stationSelect" onchange="loadStation(this.value)">
							<option value="">— Elige una emisora —</option>
						</select>';
					}
					$widget  .= '
				</div>
				<div class="stream-bar">
					<div class="stream-stat">Estado: <strong id="streamState">—</strong></div>
					<div class="stream-divider"></div>
					<div class="stream-stat">Bitrate: <strong id="streamBitrate">—</strong></div>
					<div class="stream-divider"></div>
					<div class="stream-stat">Tipo: <strong id="streamType">—</strong></div>
				</div>';
				//Si no es minireproductor
				if($Type==1){
					$widget  .= '
					<div style="border-top:0.5px solid var(--rp-color-border-tertiary);padding:8px 12px">
						<input type="text" class="form-control form-control-sm" id="srch" placeholder="Buscar emisora..." oninput="renderList()" style="background:var(--rp-color-background-secondary);color:var(--rp-color-text-primary);border:0.5px solid var(--rp-color-border-tertiary);font-size:13px">
					</div>
					<div class="cat-pills" id="catPills"></div>
					<div class="list-scroll" id="stList"></div>';
				}
				$widget  .= '
			</div>
		</div>
		<script>
			//Ubicacion de las caratulas
			let rp_covers = "'.$BASE.'/vendor/mejs-player/emisoras/";
			let rp_Type   = '.$Type.';
		</script>
		<script type="text/javascript" src="'.$BASE.'/vendor/radio_player/radio_player.js?get='.time().'"></script>';

		/**********************/
		//Imprimir dato
		echo $widget;

	}

	/************************************************************************************************************/
	public function widget_meteo($Options){
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite generar elementos para mostrar la prevision meteorologica
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime elemento
		*   $Options = [
		*		'BASE'        => 'URL',    //Ruta de la raiz del sitio
		*		'Type'        => 1,        //Tipo de widget
		*		'latitude'    => -33.45,   //Latitud
		*		'longitude'   => -70.66,   //Longitud
		*	];
		* 	$Common->widget_meteo($Options);
		*
		*=================================================    Parametros   =================================================
		* @input   array   $Options    array con los datos
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		//se definen las opciones disponibles
		$tipos = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11);
		//Validaciones
		if(isset($Options['Type'])&&!in_array($Options['Type'], $tipos)){  echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'La configuracion $type entregada no esta dentro de las opciones.'); exit;}

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

	/************************************************************************************************************/
	public function widget_fileExplorer($Options){
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite generar un explorador de archivos
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime elemento
		*   $Options = [
		*		'BASE'             => 'URL',                //Ruta de la raiz del sitio
		*		'Route'            => 'carpeta/subcarpeta', //Ruta de la carpeta a mostrar
		*		'ValidarTipo'      => 'word,excel',         //Archivos permitidos a mostrar
		*		'levelPermission'  => 4,                    //Nivel de permiso otorgado | 1-Solo ver | 2-Subir archivos | 3-Borrar archivos
		*	];
		* 	$Common->widget_fileExplorer($Options);
		*
		*=================================================    Parametros   =================================================
		* @input   array   $Options    array con los datos
		* @return  array
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		if(!isset($Options['rootPaht']) || $Options['rootPaht']==''){  echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el rootPaht.');    exit;}

		/**********************  Definiciones   **********************/
		$fnc_Codification   = new FunctionsSecurityCodification();
		$BASE               = $Options['BASE'];
		$rootPaht           = $Options['rootPaht'];
		$SubRoute           = (isset($Options['Route'])&&$Options['Route']!='') ? $Options['Route'] : '';
        $Route              = $fnc_Codification->encryptDecrypt('encrypt', $SubRoute);
		$ValidarTipo        = (isset($Options['ValidarTipo'])&&$Options['ValidarTipo']!='') ? $Options['ValidarTipo'] : 'all';
		$levelPermission    = (isset($Options['levelPermission'])&&$Options['levelPermission']!='') ? $Options['levelPermission'] : 4;

		/********************** Si todo esta ok **********************/
		$widget  = '
			<div class="file-explorer">

				<!-- Toolbar -->
				<div class="toolbar p-2 d-flex justify-content-between align-items-center">
					<div class="btn-group">
						<button class="btn btn-sm btn-outline-secondary" onclick="setView(\'grid\')"><i class="bi bi-grid"></i></button>
						<button class="btn btn-sm btn-outline-secondary" onclick="setView(\'list\')"><i class="bi bi-card-list"></i></button>
					</div>';

					if($levelPermission>=2){
						$widget  .= '
						<div class="btn-group">
							<button class="btn btn-sm btn-outline-primary"     onclick="document.getElementById(\'fileInput\').click()"><i class="bi bi-upload"></i> Subir Archivo</button>
							<button class="btn btn-sm btn-outline-secondary"   onclick="createNewFolder()"><i class="bi bi-folder-plus"></i> Crear Carpeta</button>
						</div>
						<input type="file" id="fileInput" style="display:none" onchange="uploadFile(this)">';
					}

					$widget  .= '
					<div class="input-group input-group-sm" style="max-width: 250px;">
						<span class="input-group-text"><i class="bi bi-search"></i></span>
						<input type="text" id="searchInput" class="form-control" placeholder="Buscar...">
					</div>
				</div>

				<!-- Breadcrumb -->
				<div class="p-2 border-bottom">
					<nav><ol class="breadcrumb" id="breadcrumb"></ol></nav>
				</div>

				<!-- Lista de archivos -->
				<div class="table-responsive file-content">
					<div id="gridView" class="file-grid"></div>
					<table id="listView" class="table table-hover bg-white d-none">
						<thead>
							<tr>
								<th scope="col">Nombre</th>
								<th scope="col">Tamaño</th>
								<th scope="col">Fecha</th>';
								if($levelPermission>=3){$widget  .= '<th scope="col">Acciones</th>';}
								$widget  .= '
							</tr>
						</thead>
						<tbody id="fileList"></tbody>
					</table>
				</div>

			</div>

			<!-- MODAL PREVIEW -->
			<div class="modal fade" id="previewModal">
				<div class="modal-dialog modal-xl">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="previewTitle"></h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body" id="previewBody"></div>
						<div class="modal-footer">
							<div class="d-grid gap-2 d-md-flex justify-content-md-end w-100" id="previewActions"></div>
						</div>
					</div>
				</div>
			</div>

			<style>
				.file-explorer {overflow: hidden;background: #fff;border: 1px solid var(--card-border-color);border-radius: 5px;}
				.file-explorer .toolbar {background: #f1f3f5;border-bottom: 1px solid #dee2e6;}
				.file-explorer .breadcrumb {margin-bottom: 0;}
				.file-explorer .file-content{min-height:400px;}

				.file-explorer .file-grid {display: grid;grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));gap: 15px;}
				.file-explorer .file-card {background: white;border-radius: 10px;padding: 15px;text-align: center;transition: 0.2s;}
				.file-explorer .file-card:hover {background: #e9f2ff;cursor: pointer;}
				.file-explorer .file-icon {font-size: 40px;}
				.file-explorer .file-name {font-size: 13px;word-break: break-word;}
				.file-explorer .view-toggle button {margin-left: 5px;}

				.file-explorer .table thead {background-color: #f8f9fa;}
				.file-explorer .table tbody tr td {cursor: pointer;}
			</style>

			<script>

				let currentPath = "";
				let currentView = "grid";
				let allFiles    = [];

				/**
				 * ===================================================================================
				 * CONFIGURACIÓN DE SEGURIDAD
				 * ===================================================================================
				 */
				const EXCLUDED_NAMES = [
					".htaccess", ".htpasswd", ".env", ".env.local", ".env.production", ".env.dev",
					".gitignore", ".gitattributes", "config.php", "configuration.php", "settings.php",
					"web.config", "composer.json", "composer.lock", "package.json", "package-lock.json",
					"yarn.lock", "Dockerfile", "docker-compose.yml", "phpunit.xml", "README.md",
					"LICENSE", "error_log", "access.log"
				];

				const EXCLUDED_EXTENSIONS = [
					"php", "php3", "php4", "php5", "phtml", "phar",
					"ini", "env", "conf", "config", "yaml", "yml", "toml",
					"log",
					"sh", "bash", "zsh", "bat", "cmd", "ps1",
					"exe", "bin", "run",
					"sql", "bak", "old", "backup", "dump",
					"cgi", "pl", "py", "rb", "jsp", "asp", "aspx"
				];

				const EXCLUDED_FOLDERS = [
					".git", ".svn", ".hg",
					"node_modules", "vendor",
					".idea", ".vscode",
					"bin", "etc", "var", "proc", "sys", "dev", "tmp",
					"logs", "log", "cache", "storage",
					"backup", "backups",
					".docker", ".github",
					"tests", "test"
				];

				function sanitizePath(path) {
					return path.replace(/\.\./g, "");
				}

				/**
				 * ===================================================================================
				 * FILTRO DE SEGURIDAD
				 * ===================================================================================
				 */
				/**
				 * Determina si un archivo o carpeta está permitido según reglas de exclusión.
				 *
				 * Reglas aplicadas:
				 * 1. Excluye archivos/carpetas por nombre exacto.
				 * 2. Excluye carpetas completas por nombre.
				 * 3. Excluye archivos según su extensión.
				 *
				 * @param {Object} file - Objeto que representa un archivo o carpeta.
				 * @param {string} file.name - Nombre del archivo o carpeta.
				 * @param {string} file.type - Tipo del elemento ("file" o "folder").
				 *
				 * @returns {boolean}
				 * - true  => El archivo/carpeta está permitido.
				 * - false => El archivo/carpeta está bloqueado según las reglas.
				 *
				 * @example
				 * const file = { name: "config.php", type: "file" };
				 * isAllowed(file); // false (si "php" está en EXCLUDED_EXTENSIONS)
				 */
				function isAllowed(file) {
					// Normaliza el nombre a minúsculas para evitar problemas de comparación
					const name = file.name.toLowerCase();

					/**
					 * 1. Exclusión por nombre exacto
					 * Ej: ".env", "config.php", "thumbs.db"
					 */
					if (EXCLUDED_NAMES.includes(name)) {
						return false;
					}

					/**
					 * 2. Exclusión de carpetas completas
					 * Solo aplica si el tipo es "folder"
					 * Ej: "node_modules", ".git", "vendor"
					 */
					if (file.type === "folder" && EXCLUDED_FOLDERS.includes(name)) {
						return false;
					}

					/**
					 * 3. Exclusión por extensión de archivo
					 * Extrae la extensión usando regex:
					 * - Busca el texto después del último punto
					 * - Ej: "archivo.tar.gz" → "gz"
					 */
					const extMatch = name.match(/\.([^.]+)$/);

					if (extMatch) {
						const ext = extMatch[1];

						/**
						 * Verifica si la extensión está en la lista negra
						 * Ej: ["exe", "bat", "sh", "php"]
						 */
						if (EXCLUDED_EXTENSIONS.includes(ext)) {
							return false;
						}
					}

					/**
					 * Si no cumple ninguna regla de exclusión, el archivo es permitido
					 */
					return true;
				}

				/**
				 * Carga y renderiza la lista de archivos/carpetas desde el servidor
				 * según una ruta dada, aplicando sanitización, seguridad y ordenamiento.
				 *
				 * Flujo:
				 * 1. Sanitiza la ruta recibida.
				 * 2. Transforma la ruta a un formato compatible con el backend.
				 * 3. Realiza petición HTTP para obtener los archivos.
				 * 4. Aplica filtros de seguridad (isAllowed).
				 * 5. Ordena: carpetas primero, luego archivos.
				 * 6. Actualiza estado global y renderiza UI.
				 *
				 * @async
				 * @function loadFiles
				 *
				 * @param {string} [path=""] - Ruta actual a cargar.
				 *
				 * @returns {Promise<void>}
				 *
				 * @example
				 * await loadFiles();          // Carga raíz
				 * await loadFiles("/images"); // Carga carpeta específica
				 */
				async function loadFiles(path = "") {

					/**
					 * ===============================
					 * 1. SANITIZACIÓN DE RUTA
					 * ===============================
					 * Evita rutas inválidas o inseguras (ej: "../", "//", etc.)
					 */
					path = sanitizePath(path);

					let finalPath = "";

					/**
					 * ===============================
					 * 2. TRANSFORMACIÓN DE RUTA
					 * ===============================
					 * El backend no acepta "/" directamente, por lo que:
					 * - Se elimina el "/" inicial
					 * - Se reemplazan "/" por "ntn"
					 *
					 * Ej:
					 * "/folder/sub" → "folderntnsub"
					 *
					 * Caso especial:
					 * - Si no hay path, se envía valor placeholder ("asdqwe")
					 */
					if (!path) {
						finalPath = "asdqwe"; // Valor usado como raíz en backend
					} else {
						let cleanedPath = path.replace(/^\//, "");
						cleanedPath = cleanedPath.replace(/\//g, "ntn");
						finalPath = cleanedPath;
					}

					/**
					 * ===============================
					 * 3. PETICIÓN AL BACKEND
					 * ===============================
					 * Obtiene listado de archivos/carpetas en formato JSON
					 */
					const res = await fetch(`'.$BASE.'/core/fileExplorer/updateList/'.$Route.'/'.$ValidarTipo.'/${finalPath}`);
					let files = await res.json();

					/**
					 * Guarda la ruta actual (estado global)
					 */
					currentPath = path;

					/**
					 * ===============================
					 * 4. FILTRO DE SEGURIDAD
					 * ===============================
					 * Elimina archivos/carpetas no permitidos
					 * según reglas definidas en isAllowed()
					 */
					files = files.filter(isAllowed);

					/**
					 * ===============================
					 * 5. ORDENAMIENTO
					 * ===============================
					 * - Carpetas primero
					 * - Luego archivos
					 * - Orden alfabético case-insensitive
					 */
					const folders = files
						.filter(f => f.type === "folder")
						.sort((a, b) => a.name.localeCompare(b.name, undefined, { sensitivity: \'base\' }));

					const others = files
						.filter(f => f.type !== "folder")
						.sort((a, b) => a.name.localeCompare(b.name, undefined, { sensitivity: \'base\' }));

					const sortedFiles = [...folders, ...others];

					/**
					 * ===============================
					 * 6. ACTUALIZACIÓN DE ESTADO UI
					 * ===============================
					 */

					// Dataset completo (usado por buscador)
					allFiles = sortedFiles;

					// Reset del input de búsqueda
					document.getElementById("searchInput").value = "";

					/**
					 * ===============================
					 * 7. RENDERIZADO
					 * ===============================
					 */

					// Renderiza lista de archivos
					render(sortedFiles);

					// Renderiza navegación tipo breadcrumb
					renderBreadcrumb();
				}

				/**
				 * Renderiza los archivos en las distintas vistas disponibles.
				 *
				 * Esta función actúa como orquestador de renderizado, delegando
				 * la visualización a diferentes componentes de UI:
				 *
				 * - Vista tipo grid (tarjetas / iconos)
				 * - Vista tipo lista (tabla / detalles)
				 *
				 * Ambas vistas se actualizan con el mismo dataset para mantener
				 * consistencia entre modos de visualización.
				 *
				 * @function render
				 *
				 * @param {Array<Object>} files - Lista de archivos/carpetas a renderizar.
				 * @param {string} files[].name - Nombre del archivo o carpeta.
				 * @param {string} files[].type - Tipo del elemento ("file" o "folder").
				 * @param {number} [files[].size] - Tamaño del archivo (opcional).
				 *
				 * @returns {void}
				 *
				 * @example
				 * render(files); // Actualiza grid y lista con los mismos datos
				 */
				function render(files) {

					/**
					 * ===============================
					 * 1. RENDER VISTA GRID
					 * ===============================
					 * Representación visual tipo explorador moderno
					 * (iconos grandes, tarjetas, preview, etc.)
					 */
					renderGrid(files);

					/**
					 * ===============================
					 * 2. RENDER VISTA LISTA
					 * ===============================
					 * Representación tipo tabla
					 * (nombre, tamaño, fecha, etc.)
					 */
					renderList(files);
				}

				/**
				 * Renderiza los archivos/carpetas en formato de grilla (grid view).
				 *
				 * Cada elemento se representa como una tarjeta visual ("file-card")
				 * que incluye:
				 * - Ícono del archivo/carpeta
				 * - Nombre del archivo
				 *
				 * Además, permite interacción mediante doble clic para abrir el elemento.
				 *
				 * @function renderGrid
				 *
				 * @param {Array<Object>} files - Lista de archivos/carpetas a renderizar.
				 * @param {string} files[].name - Nombre del archivo o carpeta.
				 * @param {string} files[].type - Tipo del elemento ("file" o "folder").
				 *
				 * @returns {void}
				 *
				 * @example
				 * renderGrid(files); // Renderiza tarjetas visuales en el contenedor grid
				 */
				function renderGrid(files) {

					/**
					 * ===============================
					 * 1. OBTENER CONTENEDOR
					 * ===============================
					 * Elemento HTML donde se renderiza la grilla
					 */
					const grid = document.getElementById("gridView");

					/**
					 * Limpia contenido previo para evitar duplicados
					 */
					grid.innerHTML = "";

					/**
					 * ===============================
					 * 2. ITERACIÓN DE ARCHIVOS
					 * ===============================
					 * Se crea una tarjeta por cada archivo/carpeta
					 */
					files.forEach(file => {

						/**
						 * Contenedor principal de la tarjeta
						 */
						const div = document.createElement("div");
						div.className = "file-card";

						/**
						 * Evento de doble clic
						 * - Abre carpeta o archivo
						 */
						div.ondblclick = () => openItem(file);

						/**
						 * ===============================
						 * 3. CONTENIDO HTML
						 * ===============================
						 * - Ícono dinámico según tipo de archivo
						 * - Nombre del archivo
						 */
						div.innerHTML = `
							<div class="file-icon">${getIcon(file)}</div>
							<div class="file-name">${file.name}</div>
						`;

						/**
						 * ===============================
						 * 4. INSERCIÓN EN EL DOM
						 * ===============================
						 */
						grid.appendChild(div);
					});
				}


				/**
				 * Renderiza los archivos/carpetas en formato de lista (tabla).
				 *
				 * Cada elemento se representa como una fila (<tr>) con:
				 * - Ícono + nombre
				 * - Tamaño (si aplica)
				 * - Fecha
				 * - Acciones (ej: eliminar, según permisos)
				 *
				 * Permite interacción mediante doble clic para abrir el elemento.
				 *
				 * ⚠️ IMPORTANTE:
				 * Este código mezcla JavaScript con PHP embebido para control de permisos,
				 * lo que implica que parte del HTML se construye en el servidor.
				 *
				 * @function renderList
				 *
				 * @param {Array<Object>} files - Lista de archivos/carpetas a renderizar.
				 * @param {string} files[].name - Nombre del archivo o carpeta.
				 * @param {string} files[].type - Tipo ("file" o "folder").
				 * @param {number} [files[].size] - Tamaño en bytes (opcional).
				 * @param {string} [files[].date] - Fecha de modificación.
				 *
				 * @returns {void}
				 *
				 * @example
				 * renderList(files); // Renderiza tabla de archivos
				 */
				function renderList(files) {

					/**
					 * ===============================
					 * 1. OBTENER CONTENEDOR
					 * ===============================
					 * <tbody> donde se insertan las filas
					 */
					const list = document.getElementById("fileList");

					/**
					 * Limpia contenido previo
					 */
					list.innerHTML = "";

					/**
					 * ===============================
					 * 2. ITERACIÓN DE ARCHIVOS
					 * ===============================
					 */
					files.forEach(file => {

						/**
						 * Fila de la tabla
						 */
						const row = document.createElement("tr");

						/**
						 * Evento de doble clic
						 * - Abre archivo o carpeta
						 */
						row.ondblclick = () => openItem(file);

						/**
						 * ===============================
						 * 3. CONTENIDO HTML
						 * ===============================
						 * ⚠️ Incluye PHP para control de permisos
						 */
						row.innerHTML = `
							<td>${getIcon(file)} ${file.name}</td>
							<td>${file.size ? formatSize(file.size) : "-"}</td>
							<td>${file.date}</td>';
							if($levelPermission>=3){
								$widget .= '
								<td>
									<button class="btn btn-sm btn-outline-danger"
										onclick="${file.type === "folder"
											? `deleteFolder(\'${file.name}\')`
											: `deleteFile(\'${file.name}\')`}">
										<i class="bi bi-trash"></i> Borrar
									</button>
								</td>';
								}
							$widget .= '
						`;

						/**
						 * ===============================
						 * 4. INSERCIÓN EN EL DOM
						 * ===============================
						 */
						list.appendChild(row);
					});
				}

				/**
				 * Obtiene el ícono correspondiente a un archivo o carpeta
				 * basado en su tipo o extensión.
				 *
				 * Utiliza Bootstrap Icons para representar visualmente
				 * diferentes tipos de archivos (imágenes, documentos, código, etc.).
				 *
				 * @function getIcon
				 *
				 * @param {Object} file - Objeto que representa un archivo o carpeta.
				 * @param {string} file.name - Nombre del archivo.
				 * @param {string} file.type - Tipo del elemento ("file" o "folder").
				 *
				 * @returns {string} HTML string con el ícono correspondiente.
				 *
				 * @example
				 * getIcon({ name: "foto.jpg", type: "file" });
				 * // <i class="bi bi-file-image text-info"></i>
				 *
				 * getIcon({ name: "documento.pdf", type: "file" });
				 * // <i class="bi bi-file-pdf text-danger"></i>
				 *
				 * getIcon({ name: "carpeta", type: "folder" });
				 * // <i class="bi bi-folder-fill text-warning"></i>
				 */
				function getIcon(file) {

					/**
					 * ===============================
					 * 1. CARPETAS
					 * ===============================
					 * Se prioriza la detección de carpetas
					 */
					if (file.type === "folder") {
						return "<i class=\'bi bi-folder-fill text-warning\'></i>";
					}

					/**
					 * Normaliza el nombre para evitar problemas de case-sensitive
					 */
					const name = file.name.toLowerCase();

					/**
					 * ===============================
					 * 2. OBTENER EXTENSIÓN
					 * ===============================
					 * Extrae la extensión del archivo usando regex:
					 * - Captura lo que está después del último "."
					 * - Ej: "archivo.tar.gz" → "gz"
					 */
					const extMatch = name.match(/\.([^.]+)$/);
					const ext = extMatch ? extMatch[1] : "";

					/**
					 * ===============================
					 * 3. MAPEO DE ICONOS
					 * ===============================
					 * Relación entre extensiones y clases de Bootstrap Icons
					 */
					const iconMap = {

						/** Imágenes */
						jpg: "bi-file-image text-info",
						jpeg: "bi-file-image text-info",
						png: "bi-file-image text-info",
						gif: "bi-file-image text-info",
						webp: "bi-file-image text-info",
						svg: "bi-file-image text-info",
						bmp: "bi-file-image text-info",

						/** PDF */
						pdf: "bi-file-pdf text-danger",

						/** Word */
						doc: "bi-file-word text-primary",
						docx: "bi-file-word text-primary",
						rtf: "bi-file-word text-primary",

						/** Excel */
						xls: "bi-file-excel text-success",
						xlsx: "bi-file-excel text-success",
						csv: "bi-file-excel text-success",

						/** PowerPoint */
						ppt: "bi-file-ppt text-warning",
						pptx: "bi-file-ppt text-warning",

						/** Código */
						js: "bi-filetype-js text-warning",
						html: "bi-filetype-html text-danger",
						css: "bi-filetype-css text-primary",
						json: "bi-filetype-json text-info",
						xml: "bi-filetype-xml text-secondary",
						py: "bi-filetype-py text-warning",
						java: "bi-filetype-java text-danger",
						c: "bi-filetype-c text-primary",
						cpp: "bi-filetype-cpp text-primary",
						cs: "bi-filetype-cs text-success",
						php: "bi-filetype-php text-indigo",

						/** Texto */
						txt: "bi-file-text text-secondary",
						md: "bi-file-text text-secondary",

						/** Comprimidos */
						zip: "bi-file-zip text-warning",
						rar: "bi-file-zip text-warning",
						// 7z: "bi-file-zip text-warning", // Comentado por soporte limitado en Bootstrap Icons
						tar: "bi-file-zip text-warning",
						gz: "bi-file-zip text-warning",

						/** Video */
						mp4: "bi-file-play text-danger",
						avi: "bi-file-play text-danger",
						mkv: "bi-file-play text-danger",
						mov: "bi-file-play text-danger",

						/** Audio */
						mp3: "bi-file-music text-success",
						wav: "bi-file-music text-success",
						ogg: "bi-file-music text-success",

						/** Binarios / instaladores */
						exe: "bi-file-earmark-binary text-dark",
						apk: "bi-file-earmark-binary text-success"
					};

					/**
					 * ===============================
					 * 4. RESOLUCIÓN DE ÍCONO
					 * ===============================
					 * - Busca en el mapa
					 * - Si no existe, usa ícono genérico
					 */
					const iconClass = iconMap[ext] || "bi-file-earmark text-muted";

					/**
					 * ===============================
					 * 5. RETORNO HTML
					 * ===============================
					 */
					return `<i class="bi ${iconClass}"></i>`;
				}

				/**
				 * Maneja la acción de apertura de un elemento (archivo o carpeta).
				 *
				 * - Si es carpeta: navega a su contenido.
				 * - Si es archivo: abre vista previa.
				 *
				 * @function openItem
				 *
				 * @param {Object} file - Archivo o carpeta seleccionada.
				 * @param {string} file.name - Nombre del elemento.
				 * @param {string} file.type - Tipo ("file" o "folder").
				 *
				 * @returns {void}
				 */
				function openItem(file) {

					/**
					 * Navegación de carpetas
					 */
					if (file.type === "folder") {
						loadFiles(currentPath + "/" + file.name);
					} else {
						/**
						 * Vista previa de archivo
						 */
						preview(file);
					}
				}

				/**
				 * Renderiza la navegación tipo breadcrumb según la ruta actual.
				 *
				 * Ejemplo:
				 * /images/icons → Inicio / images / icons
				 *
				 * Permite navegación rápida a cualquier nivel de la jerarquía.
				 *
				 * @function renderBreadcrumb
				 *
				 * @returns {void}
				 */
				function renderBreadcrumb() {

					const breadcrumb = document.getElementById("breadcrumb");
					breadcrumb.innerHTML = "";

					/**
					 * Divide la ruta actual en segmentos
					 */
					const parts = currentPath.split("/").filter(Boolean);

					let path = "";

					/**
					 * Elemento raíz (Inicio)
					 */
					breadcrumb.innerHTML += `
						<li class="breadcrumb-item">
							<a href="#" onclick="loadFiles(\'\')">
								<i class="bi bi-house-door"></i> Inicio
							</a>
						</li>
					`;

					/**
					 * Construcción dinámica del breadcrumb
					 */
					parts.forEach(p => {
						path += "/" + p;

						breadcrumb.innerHTML += `
							<li class="breadcrumb-item">
								<a href="#" onclick="loadFiles(\'${path}\')">${p}</a>
							</li>
						`;
					});
				}

				/**
				 * Cambia el modo de visualización del explorador.
				 *
				 * Vistas disponibles:
				 * - "grid" → vista tipo tarjetas
				 * - "list" → vista tipo tabla
				 *
				 * @function setView
				 *
				 * @param {string} view - Tipo de vista ("grid" | "list")
				 *
				 * @returns {void}
				 */
				function setView(view) {

					/**
					 * Actualiza estado global
					 */
					currentView = view;

					/**
					 * Alterna visibilidad de vistas usando Bootstrap
					 */
					document.getElementById("gridView")
						.classList.toggle("d-none", view !== "grid");

					document.getElementById("listView")
						.classList.toggle("d-none", view !== "list");
				}

				/**
				 * Formatea el tamaño de un archivo en KB.
				 *
				 * @function formatSize
				 *
				 * @param {number} bytes - Tamaño en bytes.
				 *
				 * @returns {string} Tamaño formateado (ej: "12.5 KB")
				 *
				 * @example
				 * formatSize(2048); // "2.0 KB"
				 */
				function formatSize(bytes) {
					return (bytes / 1024).toFixed(1) + " KB";
				}

				/**
				 * Filtra los archivos según un texto de búsqueda.
				 *
				 * - Búsqueda case-insensitive
				 * - Coincidencia parcial por nombre
				 * - Si no hay query, muestra todos los archivos
				 *
				 * @function filterFiles
				 *
				 * @param {string} query - Texto ingresado por el usuario.
				 *
				 * @returns {void}
				 */
				function filterFiles(query) {

					/**
					 * Normaliza input
					 */
					const q = query.toLowerCase().trim();

					/**
					 * Si está vacío → mostrar todo
					 */
					if (!q) {
						render(allFiles);
						return;
					}

					/**
					 * Filtrado por nombre
					 */
					const filtered = allFiles.filter(file =>
						file.name.toLowerCase().includes(q)
					);

					/**
					 * Render de resultados
					 */
					render(filtered);
				}

				/**
				 * Listener para búsqueda en tiempo real.
				 *
				 * Se ejecuta en cada cambio del input,
				 * aplicando filtro dinámico.
				 */
				document.getElementById("searchInput")
					.addEventListener("input", function () {
						filterFiles(this.value);
					});

				';

				if($levelPermission>=2){
					$widget  .= '
					async function createNewFolder() {
						// Lanzamos el diálogo de SweetAlert2
						const { value: folderName } = await Swal.fire({
							title: "Nueva Carpeta",
							input: "text",
							inputLabel: "Introduce el nombre de la carpeta:",
							inputPlaceholder: "Ej: Vacaciones 2026",
							showCancelButton: true,
							confirmButtonText: "<i class=\'bi bi-check-circle\'></i> Crear",
							cancelButtonText: "<i class=\'bi bi-x-circle\'></i> Cancelar",
							confirmButtonColor: "#81A1C1",
							cancelButtonColor: "#EA5757",
							reverseButtons: true,
							inputValidator: (value) => {
								if (!value || value.trim() === "") {
									return "¡Necesitas escribir un nombre!";
								}
							}
						});

						// Si el usuario canceló o cerró el diálogo, folderName será undefined
						if (!folderName) return;

						// Preparamos los datos
						const formData = new FormData();
						formData.append("SubRoute", "'.$SubRoute.'");
						formData.append("path", currentPath);
						formData.append("name", folderName.trim());

						try {
							const res = await fetch(`'.$BASE.'/core/fileExplorer/createFolder`, {
								method: "POST",
								body: formData
							});
							const result = await res.json();
							if (result.success) {
								// Notificación de éxito
								loadFiles(currentPath);
							} else {
								// Notificación de error del servidor
								Swal.fire({position: "top-end",timer: 5000,showConfirmButton: false,timerProgressBar: true,icon: "error",text: result.message || "No se pudo crear la carpeta."});
							}
						} catch (error) {
							console.error("Error al crear carpeta:", error);
							Swal.fire({position: "top-end",timer: 5000,showConfirmButton: false,timerProgressBar: true,icon: "error",text: "Hubo un problema al comunicarse con el servidor."});
						}
					}';
				}
				if($levelPermission>=3){
					$widget  .= '
					function deleteFolder(folderName) {

						Swal.fire({
							title: "¿Eliminar carpeta?",
							text: `Se eliminará la carpeta "${folderName}" y todo su contenido.`,
							icon: "warning",
							showCancelButton: true,
							confirmButtonText: "<i class=\'bi bi-trash\'></i> Eliminar",
							cancelButtonText: "<i class=\'bi bi-x-circle\'></i> Cancelar",
							confirmButtonColor: "#81a1c1",
							cancelButtonColor: "#ea5757",
							reverseButtons: true,
							focusCancel: true
						}).then((result) => {

							// Si cancela, salimos
							if (!result.isConfirmed) return;

							const formData = new FormData();
							formData.append("SubRoute", "'.$SubRoute.'");
							formData.append("path", currentPath);
							formData.append("name", folderName);

							fetch(`'.$BASE.'/core/fileExplorer/deleteFolder`, {
								method: "POST",
								body: formData
							})
							.then(res => res.json())
							.then(data => {

								if (data.success) {
									Swal.fire({
										position: "top-end",
										icon: "success",
										text: "La carpeta se eliminó correctamente.",
										timer: 4000,
										showConfirmButton: false,
										timerProgressBar: true
									});

									loadFiles(currentPath);

								} else {
									Swal.fire({
										position: "top-end",
										icon: "error",
										text: data.message || "No se pudo eliminar la carpeta.",
										timer: 4000,
										showConfirmButton: false,
										timerProgressBar: true
									});
								}

							})
							.catch(error => {
								console.error("Error al eliminar carpeta:", error);

								Swal.fire({
									position: "top-end",
									icon: "error",
									text: "Error de comunicación con el servidor.",
									timer: 4000,
									showConfirmButton: false,
									timerProgressBar: true
								});
							});

						});
					}';
				}
				if($levelPermission>=2){
					$widget  .= '
					async function uploadFile(input) {
						if (input.files.length === 0) return;

						const file = input.files[0];
						const formData = new FormData();
						formData.append("SubRoute", "'.$SubRoute.'");
						formData.append("file", file);
						formData.append("path", currentPath);

						try {
							// Mostramos un feedback visual simple
							const btn = document.querySelector(\'button[onclick*="fileInput"]\');
							const originalText = btn.innerHTML;
							btn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Subiendo...`;
							btn.disabled = true;

							const res = await fetch(`'.$BASE.'/core/fileExplorer/uploadFile`, {
								method: "POST",
								body: formData
							});

							const result = await res.json();
							btn.innerHTML = originalText;
							btn.disabled = false;

							if (result.success) {
								loadFiles(currentPath);
								input.value = ""; // Reset input
							} else {
								Swal.fire({
									position: "top-end",
									icon: "error",
									text: result.message || "No se pudo subir el archivo.",
									timer: 4000,
									showConfirmButton: false,
									timerProgressBar: true
								});
							}
						} catch (error) {
							Swal.fire({
								position: "top-end",
								icon: "error",
								text: "Error de conexión al subir archivo.",
								timer: 4000,
								showConfirmButton: false,
								timerProgressBar: true
							});
						}
					}';
				}
				if($levelPermission>=3){
					$widget  .= '
					function deleteFile(fileName) {

						Swal.fire({
							title: "¿Eliminar archivo?",
							text: `Se eliminará el archivo "${fileName}".`,
							icon: "warning",
							showCancelButton: true,
							confirmButtonText: "<i class=\'bi bi-trash\'></i> Eliminar",
							cancelButtonText: "<i class=\'bi bi-x-circle\'></i> Cancelar",
							confirmButtonColor: "#81a1c1",
							cancelButtonColor: "#ea5757",
							reverseButtons: true,
							focusCancel: true
						}).then((result) => {

							// Si cancela, salimos
							if (!result.isConfirmed) return;

							const formData = new FormData();
							formData.append("SubRoute", "'.$SubRoute.'");
							formData.append("path", currentPath);
							formData.append("name", fileName);

							fetch(`'.$BASE.'/core/fileExplorer/deleteFile`, {
								method: "POST",
								body: formData
							})
							.then(res => res.json())
							.then(data => {

								if (data.success) {
									Swal.fire({
										position: "top-end",
										icon: "success",
										text: "El archivo se eliminó correctamente.",
										timer: 4000,
										showConfirmButton: false,
										timerProgressBar: true
									});

									loadFiles(currentPath);

								} else {
									Swal.fire({
										position: "top-end",
										icon: "error",
										text: data.message || "No se pudo eliminar el archivo.",
										timer: 4000,
										showConfirmButton: false,
										timerProgressBar: true
									});
								}

							})
							.catch(error => {
								console.error("Error al eliminar archivo:", error);

								Swal.fire({
									position: "top-end",
									icon: "error",
									text: "Error de comunicación con el servidor.",
									timer: 4000,
									showConfirmButton: false,
									timerProgressBar: true
								});
							});

						});
					}';
				}

				$widget  .= '

				/**
				 * ===================================================================================
				 * PREVIEW REAL
				 * ===================================================================================
				 */
				function preview(file) {
					const modal    = new bootstrap.Modal(document.getElementById("previewModal"));
					const body     = document.getElementById("previewBody");
					const actions  = document.getElementById("previewActions"); // NUEVO contenedor de acciones
					const filePath = `'.$rootPaht.$SubRoute.'${currentPath}/${file.name}`;
					const name     = file.name.toLowerCase();

					document.getElementById("previewTitle").innerText = file.name;

					// Loader inicial
					body.innerHTML = `
						<div class="text-center p-5">
							<div class="spinner-border text-primary"></div>
							<div class="mt-3 text-muted">Cargando preview...</div>
						</div>
					`;

					// BOTÓN DESCARGA GLOBAL
					actions.innerHTML = `
						<a href="${normalizarURL(filePath)}" class="btn btn-success" download>
							<i class="bi bi-download"></i> Descargar
						</a>
					`;

					/* ===============================
					* IMÁGENES
					* =============================== */
					if (name.match(/\.(jpg|jpeg|png|gif|webp|svg|bmp)$/)) {
						body.innerHTML = `
							<div class="text-center">
								<img src="${normalizarURL(filePath)}" class="img-fluid rounded shadow">
							</div>
						`;
					}

					/* ===============================
					* PDF
					* =============================== */
					else if (name.endsWith(".pdf")) {
						body.innerHTML = `
							<iframe src="${normalizarURL(filePath)}" width="100%" height="600px" style="border:none;"></iframe>
						`;
					}

					/* ===============================
					* VIDEO
					* =============================== */
					else if (name.match(/\.(mp4|webm|ogg|mov|mkv)$/)) {
						body.innerHTML = `
							<video controls class="w-100 rounded shadow">
								<source src="${normalizarURL(filePath)}">
								Tu navegador no soporta video.
							</video>
						`;
					}

					/* ===============================
					* AUDIO
					* =============================== */
					else if (name.match(/\.(mp3|wav|ogg|aac|flac)$/)) {
						body.innerHTML = `
							<div class="p-4">
								<audio controls class="w-100">
									<source src="${normalizarURL(filePath)}">
									Tu navegador no soporta audio.
								</audio>
							</div>
						`;
					}

					/* ===============================
					* TEXTO / CÓDIGO
					* =============================== */
					else if (name.match(/\.(txt|json|js|css|html|md|xml|csv|log|env|ini)$/)) {
						fetch(normalizarURL(filePath))
							.then(res => res.text())
							.then(text => {
								body.innerHTML = `
									<pre style="
										text-align:left;
										max-height:600px;
										overflow:auto;
										background:#0f172a;
										color:#e2e8f0;
										padding:15px;
										border-radius:10px;
										font-size:13px;
									">${escapeHtml(text)}</pre>
								`;
							})
							.catch(() => {
								body.innerHTML = `<p class="text-danger">Error al cargar archivo</p>`;
							});
					}

					/* ===============================
					* HTML (SANDBOX SEGURO)
					* =============================== */
					else if (name.endsWith(".html")) {
						body.innerHTML = `
							<iframe src="${normalizarURL(filePath)}" width="100%" height="600px" sandbox></iframe>
						`;
					}

					/* ===============================
					* DOCUMENTOS OFFICE
					* =============================== */
					else if (name.match(/\.(doc|docx|xls|xlsx|ppt|pptx)$/)) {
						body.innerHTML = `
							<iframe 
								src="https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(location.origin + normalizarURL(filePath))}"
								width="100%" 
								height="600px"
								style="border:none;">
							</iframe>
						`;
					}

					/* ===============================
					* ARCHIVOS COMPRIMIDOS
					* =============================== */
					else if (name.match(/\.(zip|rar|7z|tar|gz)$/)) {
						body.innerHTML = `
							<div class="p-5 text-center">
								<i class="bi bi-file-zip fs-1 text-warning"></i>
								<p class="mt-3">Archivo comprimido</p>
								<small class="text-muted">No se puede previsualizar, pero puedes descargarlo</small>
							</div>
						`;
					}

					/* ===============================
					* FALLBACK UNIVERSAL
					* =============================== */
					else {
						body.innerHTML = `
							<div class="p-5 text-center">
								<i class="bi bi-file-earmark fs-1 text-muted"></i>
								<p class="mt-3">Vista previa no disponible</p>
								<small class="text-muted">Tipo de archivo no soportado</small>
							</div>
						`;
					}

					modal.show();
				}

				loadFiles();

			</script>

		';

		/**********************/
		//Imprimir dato
		echo $widget;
	}




}

