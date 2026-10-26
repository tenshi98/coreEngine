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
	/******************************************************************************/
	//Definiciones
	private $DataValidations;
	private $TemplateRender;

	/******************************************************************************/
	/**
	 * Crea las instancias internas que la clase necesita: el validador de datos y el renderizador de plantillas.
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * $Common = new UIWidgetsCommon();
	 * ```
	 */
	public function __construct() {
		$this->DataValidations = new FunctionsDataValidations();
        $this->TemplateRender  = new TemplateRenderer();
    }

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Publicos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	/**
	 * Genera un widget tipo acordeón que se rellena con la información entregada.
	 *
	 * Claves de $Options: 'type' (1 normal | 2 flush, default 1), 'showOpen' (índice del
	 * elemento que nace abierto; 0 mantiene todos cerrados, default 1) y 'arrData' (filas
	 * con 'Title' y 'Body'). Ante una opción fuera de catálogo imprime las alertas de validación.
	 *
	 * @param array $Options Arreglo con las opciones del acordeón ('type', 'showOpen', 'arrData').
	 *
	 * @return void Imprime el HTML del acordeón (o las alertas de validación).
	 *
	 * @example
	 * ```php
	 * $Common = new UIWidgetsCommon();
	 * $arrData = [
	 *     ['Title' => 'Sección 1', 'Body' => 'Contenido uno'],
	 *     ['Title' => 'Sección 2', 'Body' => 'Contenido dos'],
	 * ];
	 * $Options = [
	 *     'type'     => 1,        // Tipo de acordeón
	 *     'showOpen' => 8,        // elemento abierto, el id 0 mantiene todos cerrados
	 *     'arrData'  => $arrData, // Arreglo con los datos
	 * ];
	 * $Common->acordeon($Options); // imprime el acordeón
	 * ```
	 */
	public function acordeon($Options){

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

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, '', 1);
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
                // Recorro
                foreach ( $arrData as $data ) {
                    //Verifico si se muestra
                    if($showOpen==$Count){
                        $show='show';
                    }else{
                        $show='';
                    }
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
        } else {
            echo $alerts;
        }

    }

    /******************************************************************************/
    /**
     * Genera un cuadro de alerta personalizado (lo retorna para imprimirlo o componerlo).
     *
     * Valores validados por catálogo: $color (1..8), $type (1..6: default, con icono,
     * outlined,Outlined con icono,outlined info y sólido) y $autoClose (0 sin cierre,
     * 1 con botón de cierre). Si la validación falla imprime las alertas y no retorna valor.
     *
     * @param int    $color     Color a utilizar (1..8).
     * @param int    $type      Tipo de mensaje (1..6, define el color y la variante).
     * @param string $icon      Clase CSS del icono (ej: 'bi bi-info-circle').
     * @param int    $autoClose Configuración del cierre automático (0 = sin cierre, 1 = con botón de cierre).
     * @param string $Text      Texto del mensaje (permite HTML).
     *
     * @return string|null HTML de la alerta generada; en error de validación imprime las alertas y no retorna valor.
     *
     * @example
     * ```php
     * $Common = new UIWidgetsCommon();
     * $alert = $Common->alertPostData(3, 2, 'bi bi-info-circle', 0, '<strong>Dato:</strong> explicacion');
     * echo $alert; // imprime la alerta descartable con icono
     * ```
     */
    public function alertPostData($color, $type, $icon, $autoClose, $Text){

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

        $dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, '', 1);
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
                case 1:  $alertType = 'alert-'.$alertColor;                                                     $alertIcon = '';                                                  break;//Default
                case 2:  $alertType = 'alert-'.$alertColor;                                                     $alertIcon = '<i class="'.$icon.'"></i>';                         break;//Default With Icon
                case 3:  $alertType = 'border-'.$alertColor;                                                    $alertIcon = '';                                                  break;//Outlined
                case 4:  $alertType = 'alert-'.$alertColor.' alert-white';                                      $alertIcon = '<div class="icon"><i class="'.$icon.'"></i></div>'; break;//Outlined With Icon
                case 5:  $alertType = 'border-'.$alertColor.' alert-information';                               $alertIcon = '';                                                  break;//Outlined info
                case 6:  $alertType = 'alert-'.$alertColor.' bg-'.$alertColor.'-gradient text-white border-0';  $alertIcon = '';                                                  break;//Default Solid Color
            	default: $alertType = 'alert-primary';                                                          $alertIcon = '';                                                  break;//valor Default
            }

            //Selecciono el tipo de mensaje
            $options  = ['', 'alert-dismissible'];
            $closeDiv = $options[$autoClose];

            //Selecciono el tipo de mensaje
            $options  = ['', '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'];
            $closeBtn = $options[$autoClose];

            //Se crea el input
            $input = '
            <div class="alert '.$alertType.' '.$closeDiv.' fade show " role="alert">'
                .$alertIcon
                .'<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-0">'.$Text.'</div>'
                .$closeBtn
            .'</div>';

            //Imprimir dato
            return $input;
        }else{
            echo $alerts;
        }

    }

    /******************************************************************************/
    /**
     * Imprime directamente una alerta generada por alertPostData().
     *
     * @param int    $color     Color a utilizar (1..8).
     * @param int    $type      Tipo de mensaje (1..6, define el color y la variante).
     * @param string $icon      Clase CSS del icono (ej: 'bi bi-info-circle').
     * @param int    $autoClose Configuración del cierre automático (0 = sin cierre, 1 = con botón de cierre).
     * @param string $Text      Texto del mensaje (permite HTML).
     *
     * @return void Imprime el HTML de la alerta en salida estándar.
     *
     * @example
     * ```php
     * $Common = new UIWidgetsCommon();
     * $Common->printAlertData(3, 1, '', 0, 'Operación realizada con éxito.');
     * ```
     */
    public function printAlertData($color, $type, $icon, $autoClose, $Text){

        //Se imprime el dato
        echo $this->alertPostData($color, $type, $icon, $autoClose, $Text);

    }

    /******************************************************************************/
    /**
     * Genera un widget tipo tabs que se rellena con la información entregada.
     *
     * Claves de $Options: 'type' (1 default | 2 inverted | 3 complement | 4 bordered,
     * default 1), 'justif' (1 normal | 2 justificado, default 1), 'activeTab' (índice del
     * tab activo, default 1) y 'arrData' (filas con 'Title' y 'Body'). Ante una opción
     * fuera de catálogo imprime las alertas de validación.
     *
     * @param array $Options Arreglo con las opciones del tabs ('type', 'justif', 'activeTab', 'arrData').
     *
     * @return void Imprime el HTML de los tabs (o las alertas de validación).
     *
     * @example
     * ```php
     * $Common = new UIWidgetsCommon();
     * $arrData = [
     *     ['Title' => 'Pestaña 1', 'Body' => 'Contenido uno'],
     *     ['Title' => 'Pestaña 2', 'Body' => 'Contenido dos'],
     * ];
     * $Options = [
     *     'type'      => 1,        // Tipo de tab
     *     'justif'    => 1,        // Tipo de justificación
     *     'activeTab' => 1,        // Elemento a mostrar
     *     'arrData'   => $arrData, // Arreglo con los datos
     * ];
     * $Common->tabs($Options); // imprime los tabs
     * ```
     */
    public function tabs($Options){

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

        $dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, '', 1);
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
            // Recorro
            foreach ( $arrData as $data ) {
                //Verifico si se muestra
                if($activeTab==$Count){
                    $active='active';
                    $show='show active';
                }else{
                    $active='';
                    $show='';
                }
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

    /******************************************************************************/
    /**
     * Genera un elemento que se asemeja a una tabla, pero es responsive.
     *
     * Cada fila de $arrData puede traer 'Titulo', 'Texto' e 'Icon' (opcional; si no viene se
     * usa un icono por defecto). Sin 'Titulo' la fila se pinta a ancho completo; el ancho de
     * la columna del título es 12 - $FormCol. Ante un ancho fuera de 1..12 imprime las alertas de validación.
     *
     * @param array $arrData Filas con las claves 'Titulo', 'Texto' e 'Icon' (opcional).
     * @param int   $FormCol Ancho de la columna del texto (1..12; default 8).
     *
     * @return void Imprime el HTML de la tabla responsive (o las alertas de validación).
     *
     * @example
     * ```php
     * $Common = new UIWidgetsCommon();
     * $arrData = [
     *     ['Icon' => '', 'Titulo' => 'Email', 'Texto' => 'Texto Texto'],
     *     ['Icon' => '', 'Titulo' => 'Rut',   'Texto' => 'Texto Texto'],
     *     ['Icon' => '', 'Titulo' => 'Fecha', 'Texto' => 'Texto Texto'],
     * ];
     * $Common->responsiveTable($arrData, 8); // imprime la tabla responsive
     * ```
     */
    public function responsiveTable($arrData, $FormCol){

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

        $dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, '', 1);
        $errorn += $dataReturn['nErrors'];
        $alerts .= $dataReturn['alerts'];

        /********************** Si todo esta ok **********************/
        //Ejecucion si no hay errores
        if($errorn==0){
            // Variable vacia
            $input = '';
            // Recorro
            foreach ( $arrData as $data ) {
                /*************************************/
                // Verifico si existe un titulo
                if(isset($data['Titulo'])&&$data['Titulo']!=''){
                    //Verifico si se envian datos para el icono
                    $Icon = (isset($data['Icon']) && $data['Icon'] != '') ? $data['Icon'] : 'bi bi-chevron-double-right text-color-red';
                    //Se genera input
                    $input.= '
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-'.$TituloCol.' col-lg-'.$TituloCol.' col-xl-'.$TituloCol.' col-xxl-'.$TituloCol.' label ">
                            <i class="'.$Icon.'"></i> '.$data['Titulo'].'
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-'.$TextoCol.' col-lg-'.$TextoCol.' col-xl-'.$TituloCol.' col-xxl-'.$TituloCol.'">
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
        } else {
            echo $alerts;
        }

    }

    /******************************************************************************/
    /**
     * Muestra el visualizador de código fuente resaltado con Prism.
     *
     * El lenguaje se define con $type (1 HTML, 2 CSS, 3 JavaScript, 4 Python, 5 PHP,
     * 6 Handlebars, 7 Git, 8 Java, 9 C Like, 10 C, 11 CSharp, 12 SQL, 13 PLSQL). El código
     * se escapa antes de imprimirlo. Si algún parámetro viene vacío o $type está fuera de
     * catálogo imprime una alerta y detiene la ejecución (exit).
     *
     * @param int    $type Tipo de elemento (1..13, según el lenguaje).
     * @param string $code Código a mostrar.
     * @param string $BASE Ruta de la raíz del sitio.
     *
     * @return void Imprime el bloque de código resaltado (o una alerta de error).
     *
     * @example
     * ```php
     * $Common = new UIWidgetsCommon();
     * $Common->widget_code_block(5, '<p>Hola Mundo</p>', 'https://mi-sitio.cl'); // imprime el bloque de código PHP
     * ```
     */
    public function widget_code_block($type, $code, $BASE){

        /**********************  Validaciones   **********************/
        //se definen las opciones disponibles
        $tipos = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13);
        //Validaciones
        if ($type === null || trim((string)$type) === '') {
            echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el Tipo de elemento.');
            exit;
        }
        if ($code === null || trim((string)$code) === '') {
            echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el Codigo a mostrar.');
            exit;
        }
        if(!in_array($type, $tipos)){
            echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'La configuracion $type entregada en el codeblock no esta dentro de las opciones.');
            exit;
        }

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
        $code = str_replace('<','<',$code);
        $code = str_replace('>','>',$code);
        $code = str_replace('"','"',$code);
        //Se genera widget
        $widget  = '<link rel="stylesheet" type="text/css" href="'.$BASE.'/vendor/prism/prism.css">';
        $widget .= '<script type="text/javascript"          src="'.$BASE.'/vendor/prism/prism.js"></script>';
        $widget .= '
        <div class="code-block">
            <h6>'.$tittle.'</h6>
            <pre style="padding-top: 0px;"><code class="'.$code.'"></code></pre>
        </div>';

        /**********************/
        //Imprimir dato
        echo $widget;

    }

    /******************************************************************************/
    /**
     * Genera un widget tipo countdown.
     *
     * @param string $FechaDestino Fecha límite en formato strtotime compatible.
     * @param array $Opciones Opciones adicionales: 'titulo', 'fecha', 'clase', 'id'.
     *
     * @return void Imprime el HTML del widget.
     *
     * @example
     * ```php
     * $Common = new UIWidgetsCommon();
     * $Common->widgetCountdown('2024-12-31', ['titulo' => 'Evento en:']);
     * ```
     *
     */
    public function widgetCountdown(string $FechaDestino, array $Opciones = []){

        /**********************  Definiciones   **********************/
        $titulo     = $Opciones['titulo'] ?? '';
        $fecha      = $Opciones['fecha'] ?? '';

        /**********************  Validaciones   **********************/
        if($titulo === ''){
            echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el titulo.');
            return;
        }
        if($fecha === ''){
            echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado la fecha.');
            return;
        }

        /********************** Si todo esta ok **********************/
        //Genero el widget de countdown
        $tiempo = strtotime($fecha);
        $ahora  = time();
        $restante = max(0, $tiempo - $ahora);
        $dias   = floor($restante / 86400);
        $horas  = floor(($restante % 3600) / 3600);
        $minutos = floor(($restante % 3600) / 60);
        $segundos = $restante % 60;

        //Escapar HTML para seguridad (RN-004)
        $titulo = htmlspecialchars($titulo);

        //Imprimir dato
        echo '
        <div class="widget widget-countdown">
            <h4>'.$titulo.'</h4>
            <div class="countdown">
                <span class="dias">'.$dias.'d </span>
                <span class="horas">'.$horas.'h </span>
                <span class="minutos">'.$minutos.'m </span>
                <span class="segundos">'.$segundos.'s </span>
            </div>
        </div>';

    }

    /******************************************************************************/
    /**
     * Genera un widget de estado vacío.
     *
     * @param string $Titulo Título del widget.
     * @param string $Mensaje Mensaje del widget (opcional).
     * @param array $Opciones Opciones adicionales: 'icono', 'clase', 'id'.
     *
     * @return void Imprime el HTML del widget.
     *
     * @example
     * ```php
     * $Common = new UIWidgetsCommon();
     * $Common->widgetEmptyState('Sin datos', '', ['icono' => 'bi bi-inbox']);
     * ```
     *
     */
    public function widgetEmptyState(string $Titulo, string $Mensaje = '', array $Opciones = []){

        /**********************  Validaciones   **********************/
        if($Titulo === ''){
            echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el titulo.');
            return;
        }

        /********************** Si todo esta ok **********************/
        //Escapar HTML para seguridad (RN-004)
        $titulo = htmlspecialchars($Titulo);

        //Imprimir dato
        echo '
        <div class="widget widget-empty">
            <h4>'.$titulo.'</h4>
            <p>No hay datos para mostrar.</p>
        </div>';

    }

    /******************************************************************************/
    /**
     * Genera un widget de progreso.
     *
     * @param float $Porcentaje Porcentaje de progreso (0-100).
     * @param array $Opciones Opciones adicionales: 'titulo', 'color', 'id'.
     *
     * @return void Imprime el HTML del widget.
     *
     * @example
     * ```php
     * $Common = new UIWidgetsCommon();
     * $Common->widgetProgress(75, ['titulo' => 'Progreso']);
     * ```
     *
     */
    public function widgetProgress(float $Porcentaje, array $Opciones = []){

        /**********************  Validaciones   **********************/
        if(!isset($Opciones['porcentaje']) || !is_numeric($Opciones['porcentaje'])){
            echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el porcentaje.');
            return;
        }
        $porcentaje = min(100, max(0, (int)$Opciones['porcentaje']));

        /********************** Si todo esta ok **********************/
        //Escapar HTML para seguridad (RN-004)
        $titulo = htmlspecialchars($Opciones['titulo'] ?? '');

        //Imprimir dato
        echo '
        <div class="widget widget-progress">
            <h4>'.$titulo.'</h4>
            <div class="progress-bar" style="width: '.$porcentaje.'%"></div>
        </div>';

    }

    /******************************************************************************/
    /**
     * Genera un widget de carrusel.
     *
     * @param array $Items Arreglo de items con 'titulo' y 'contenido'.
     * @param array $Opciones Opciones adicionales: 'id', 'controles', 'indicadores', 'intervalo'.
     *
     * @return void Imprime el HTML del widget.
     *
     * @example
     * ```php
     * $Common = new UIWidgetsCommon();
     * $Common->widgetCarousel(['items' => [['titulo' => 'Slide 1', 'contenido' => 'Contenido del slide 1']]], ['id' => 'miCarrousel']);
     * ```
     *
     */
    public function widgetCarousel(array $Items, array $Opciones = []){

        /**********************  Validaciones   **********************/
        if(!isset($Opciones['items']) || !is_array($Opciones['items'])){
            echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado los items.');
            return;
        }

        /********************** Si todo esta ok **********************/
        //Escapar HTML para seguridad (RN-004)
        $itemsHtml = '';
        foreach($Opciones['items'] as $item){
            $titulo = isset($item['titulo']) ? htmlspecialchars($item['titulo']) : '';
            $contenido = isset($item['contenido']) ? htmlspecialchars($item['contenido']) : '';
            $itemsHtml .= '<div class="carousel-item">'.$contenido.'</div>';
        }

        //Imprimir dato
        echo '<div class="widget widget-carousel">'.$itemsHtml.'</div>';

    }

    /******************************************************************************/
    /**
     * Genera un widget para copiar al portapapeles.
     *
     * @param string $Texto Texto a copiar al portapapeles.
     * @param array $Opciones Opciones adicionales: 'etiqueta', 'clase'.
     *
     * @return void Imprime el HTML del widget.
     *
     * @example
     * ```php
     * $Common = new UIWidgetsCommon();
     * $Common->widgetCopyToClipboard('Texto para copiar', ['etiqueta' => 'Copiar']);
     * ```
     *
     */
    public function widgetCopyToClipboard(string $Texto, array $Opciones = []){

        /**********************  Validaciones   **********************/
        if($Opciones['texto'] ?? '' === ''){
            echo $this->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el texto.');
            return;
        }

        /********************** Si todo esta ok **********************/
        //Escapar HTML para seguridad (RN-004)
        $texto = htmlspecialchars($Opciones['texto']);

        //Imprimir dato con botón de copiar (usando clipboard.js o similar)
        echo '
        <div class="widget widget-clipboard">
            <input type="text" value="'.$texto.'" id="clipboardInput" readonly>
            <button class="btn-clipboard" onclick="copyToClipboard()">Copiar</button>
            <script>
                function copyToClipboard() {
                    navigator.clipboard.writeText(document.getElementById("clipboardInput").value);
                    alert(' . json_encode('Texto copiado exitosamente') . ');
                }
            </script>
        </div>';

    }

}
