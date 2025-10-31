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
			1 => ['color' => 'color-blue'],
			2 => ['color' => 'color-green'],
			3 => ['color' => 'color-yellow'],
			4 => ['color' => 'color-red'],
		];

		/******************************************/
		//Se obtienen los datos
		$ServerWeb = new FunctionsServerSecurity();
		$XMLData   = $ServerWeb->getDataSIIindicadores('https://zeus.sii.cl/admin/rss/sii_ind_rss.xml');

		/******************************************/
		//Se verifica la recepcion de datos
		if($XMLData['success']==true){
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
                case 1:$alertType = 'alert-'.$alertColor;                                           $alertIcon = '';                                                             break;//Default
                case 2:$alertType = 'alert-'.$alertColor;                                           $alertIcon = '<i class="bi bi-'.$icon.' me-1"></i>';                         break;//Default With Icon
                case 3:$alertType = 'border-'.$alertColor;                                          $alertIcon = '';                                                             break;//Outlined
                case 4:$alertType = 'alert-'.$alertColor.' alert-white';                            $alertIcon = '<div class="icon"><i class="bi bi-'.$icon.' me-1"></i></div>'; break;//Outlined With Icon
                case 5:$alertType = 'border-'.$alertColor.' alert-information';                     $alertIcon = '';                                                             break;//Outlined info
                case 6:$alertType = 'alert-'.$alertColor.' bg-'.$alertColor.' text-white border-0'; $alertIcon = '';                                                             break;//Default Solid Color
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
		if(!isset($BaseURL) || $BaseURL==''){  return $this->printAlertData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la Dirección base del archivo.');}
		if(!isset($Route) || $Route==''){      return $this->printAlertData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la Ruta a la carpeta contenedora.');}
		if(!isset($File) || $File==''){        return $this->printAlertData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el Nombre del archivo.');}

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
		//devuelvo
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
					$Icon = (isset($data['Icon']) && $data['Icon'] != '') ? $data['Icon'] : 'bi bi-chevron-double-right color-red';
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

        /********************** Si todo esta ok **********************/
        
    }


}

