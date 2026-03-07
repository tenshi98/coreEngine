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
		* 	//se imprime input
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
		* 	//se imprime input
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
                case 1:$alertType = 'alert-'.$alertColor;                                                     $alertIcon = '';                                                             break;//Default
                case 2:$alertType = 'alert-'.$alertColor;                                                     $alertIcon = '<i class="bi bi-'.$icon.' me-1"></i>';                         break;//Default With Icon
                case 3:$alertType = 'border-'.$alertColor;                                                    $alertIcon = '';                                                             break;//Outlined
                case 4:$alertType = 'alert-'.$alertColor.' alert-white';                                      $alertIcon = '<div class="icon"><i class="bi bi-'.$icon.' me-1"></i></div>'; break;//Outlined With Icon
                case 5:$alertType = 'border-'.$alertColor.' alert-information';                               $alertIcon = '';                                                             break;//Outlined info
                case 6:$alertType = 'alert-'.$alertColor.' bg-'.$alertColor.'-gradient text-white border-0';  $alertIcon = '';                                                             break;//Default Solid Color
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
		* 	//se imprime input
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
                case 1:$tabType = '';                     break; //Default Tabs
                case 2:$tabType = 'nav-tabs-inverted';    break; //Inverted Tabs
                case 3:$tabType = 'nav-tabs-complement';  break; //Complement Tabs
                case 4:$tabType = 'nav-tabs-bordered';    break; //Bordered Tabs
            }
			//Selecciono la justificacion del tab
            switch ($justif) {
                case 1:$justifContent = '';       $justifElem = '';          $wbuton = '';      break; //Normal
				case 2:$justifContent = 'd-flex'; $justifElem = 'flex-fill'; $wbuton = 'w-100'; break; //Justificado
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
		* Permite generar un widget tipo tabs que se rellena en base a la info entregada
		*
		*=================================================    Modo de uso  =================================================
		* 	//se imprime input
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
		if(!isset($BaseURL) || $BaseURL==''){  echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la Dirección base del archivo.');}
		if(!isset($Route) || $Route==''){      echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la Ruta a la carpeta contenedora.');}
		if(!isset($File) || $File==''){        echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el Nombre del archivo.');}

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
		if(isset($BaseURL)&&$BaseURL!=''){ $RutaCompleta .= $BaseURL.'/';}
		if(isset($Route)&&$Route!=''){     $RutaCompleta .= $Route.'/';}
		if(isset($File)&&$File!=''){       $RutaCompleta .= $File;}

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
		* 	//se imprime input
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
		* 	//se imprime input
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
		if(!isset($idDiv) || $idDiv==''){  echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el identificador.');}
		if(!isset($Route) || $Route==''){  echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la Ruta de acceso del archivo.');}
		if(!isset($BASE) || $BASE==''){    echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la Ruta de la raiz del sitio.');}

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
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		//se definen las opciones disponibles
		$tipos = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13);
		//Validaciones
		if(!isset($type) || $type==''){  echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el Tipo de elemento.');}
		if(!isset($code) || $code==''){  echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el Codigo a mostrar.');}
		if(!in_array($type, $tipos)){    echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'La configuracion $type entregada en el codeblock no esta dentro de las opciones.');}

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
	public function widget_feed($Type, $Titulo, $Identificador, $URL, $MaxCount, $height, $ShowDesc, $ShowPubDate, $BASE){
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite generar un div que hace consumo de un feed de datos
		*
		*=================================================    Modo de uso  =================================================
		*
		* 	$Common->widget_feed('www.data.cl/feed', 10, 200, true, true, 'www.google.com');
		*
		*=================================================    Parametros   =================================================
		* @input   string   $Titulo         Titulo del Feed
		* @input   string   $Identificador  Identificador del div
		* @input   string   $URL            URL con la direccion del feed
		* @input   int      $MaxCount       Numero maximo de datos a solicitar
		* @input   int      $height         Numero maximo de altura en px
		* @input   bool     $ShowDesc       Mostrar de forma ascendente
		* @input   bool     $ShowPubDate    Mostrar la fecha de publicacion
		* @input   string   $BASE           Ruta de la raiz del sitio
		* @return  string
		*===================================================================================================================
		*/

		/**********************  Validaciones   **********************/
		//se definen las opciones disponibles
		$tipos = array(1, 2);
		//Validaciones
		if(!isset($Type) || $Type==''){                         echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el Tipo de Feed.');}
		if(!isset($Identificador) || $Identificador==''){       echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el Identificador del div.');}
		if(!isset($URL) || $URL==''){                           echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la URL con la direccion del feed.');}
		if(!isset($MaxCount) || $MaxCount==''){                 echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el Numero maximo de datos a solicitar.');}
		if(!isset($ShowDesc) || $ShowDesc==''){                 echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado si se muestra la descripcion.');}
		if(!isset($ShowPubDate) || $ShowPubDate==''){           echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado si se muestra la fecha de publicacion.');}
		if(!isset($height) || $height==''){                     echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el Numero maximo de altura en px.');}
		if(!isset($BASE) || $BASE==''){                         echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la Ruta de la raiz del sitio.');}
		if(!$this->DataValidations->validarNumero($MaxCount)){  echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'El dato $MaxCount ingresado no es un numero.');}
		if(!$this->DataValidations->validarNumero($height)){    echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'El dato $height ingresado no es un numero.');}
		if(!in_array($Type, $tipos)){                           echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'La configuracion $type entregada no esta dentro de las opciones.');}

		/********************** Si todo esta ok **********************/
		/****************************************/
		//$Type:
		//		1 - Normal
		//		2 - Mini
		/****************************************/
		$widget  = '<link type="text/css" rel="stylesheet" href="'.$BASE.'/vendor/rss_reader/rssReader_'.$Type.'.css" />';
		$widget .= '<script type="text/javascript"          src="'.$BASE.'/vendor/rss_reader/rssReader.js"></script>';
		$widget .= '
		<div id="rssReader_'.$Identificador.'"></div>
		<script type="text/javascript">
			// Inicialización
			$(document).ready(function() {
				new RSSReader("#rssReader_'.$Identificador.'", {
					cardTitle: "'.$Titulo.'",        /* Titulo del feed */
					feedUrl: "'.$URL.'",             /* URL de los datos */
					itemsPerPage: '.$MaxCount.',     /* cantidad de post a mostrar */
					showDescription: '.$ShowDesc.',  /* Mostrar descripcion (true-false) */
					showPubDate: '.$ShowPubDate.',   /* Mostrar fecha de publicacion (true-false) */
					maxHeight: "'.$height.'px"       /* Altura del div */
				});
			});
		</script>';
		//Si es la version mini
		if($Type==2){
			$widget .= '<style>#rssReader_'.$Identificador.' .rss-img {display: none;}</style>';
		}

		/**********************/
		//Imprimir dato
		echo $widget;
	}

	/************************************************************************************************************/
	public function widget_radio_player($BASE){
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
						<div class="track-info" >
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


}

