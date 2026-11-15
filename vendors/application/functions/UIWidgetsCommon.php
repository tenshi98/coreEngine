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
     * Imprime una cuenta regresiva (días, horas, minutos y segundos) hacia una fecha destino.
     *
     * Con fecha inválida imprime una alerta (alertPostData); con fecha pasada muestra 00:00:00 y el estado
     * "vencido". El título y las clases se escapan (RN-004); el id se sanea a [A-Za-z0-9_-].
     *
     * @param string $FechaDestino Fecha destino en formato compatible con strtotime().
     * @param array  $Opciones     'titulo' (rótulo), 'clase' (clases CSS extra) e 'id' (id del contenedor).
     *
     * @return void Imprime el HTML + JS del widget (o la alerta de error).
     *
     * @example
     * ```php
     * $Common = new UIWidgetsCommon();
     * $Common->widgetCountdown('2030-12-31 23:59:59', ['titulo' => 'Cierre del año', 'id' => 'cd_cierre']);
     * // imprime la cuenta regresiva; con fecha pasada muestra 00:00:00 vencido
     * ```
     */
    public function widgetCountdown(string $FechaDestino, array $Opciones = []){

        /**********************  Definiciones   **********************/
        $titulo = htmlspecialchars((string)($Opciones['titulo'] ?? ''), ENT_QUOTES, 'UTF-8');
        $clase  = $this->_widgetClass($Opciones['clase'] ?? '');
        $id     = $this->_widgetId($Opciones['id'] ?? '', 'countdown_');
        $time   = (trim($FechaDestino) === '') ? false : strtotime($FechaDestino);

        /**********************  Validaciones   **********************/
        if($time === false){
            echo $this->alertPostData(4, 4, 'bi bi-exclamation-circle', 1, 'La fecha destino de la cuenta regresiva no es valida.');
            return;
        }

        /********************** Si todo esta ok **********************/
        //Segundos restantes (nunca negativos) y desglose inicial
        $restante = max(0, $time - time());
        $dias     = intdiv($restante, 86400);
        $horas    = intdiv($restante % 86400, 3600);
        $minutos  = intdiv($restante % 3600, 60);
        $segundos = $restante % 60;
        $vencido  = ($restante === 0) ? '<span class="badge bg-danger ms-2">vencido</span>' : '';
        $cabecera = ($titulo !== '') ? '<h6 class="card-title mb-2">'.$titulo.'</h6>' : '';

        /**********************  Retorno datos  **********************/
        //Imprimir dato
        echo '
        <div class="card text-center '.$clase.'" id="'.$id.'" data-target="'.($time * 1000).'">
            <div class="card-body">
                '.$cabecera.'
                <div class="d-flex justify-content-center gap-3">
                    <div><span class="fs-3 fw-bold" data-unit="d">'.str_pad((string)$dias, 2, '0', STR_PAD_LEFT).'</span><small class="d-block text-muted">dias</small></div>
                    <div><span class="fs-3 fw-bold" data-unit="h">'.str_pad((string)$horas, 2, '0', STR_PAD_LEFT).'</span><small class="d-block text-muted">horas</small></div>
                    <div><span class="fs-3 fw-bold" data-unit="m">'.str_pad((string)$minutos, 2, '0', STR_PAD_LEFT).'</span><small class="d-block text-muted">min</small></div>
                    <div><span class="fs-3 fw-bold" data-unit="s">'.str_pad((string)$segundos, 2, '0', STR_PAD_LEFT).'</span><small class="d-block text-muted">seg</small></div>
                </div>
                <div class="mt-2" data-state>'.$vencido.'</div>
            </div>
        </div>
        <script>
        (function() {
            var box = document.getElementById('.json_encode($id).');
            if (!box) { return; }
            var target = parseInt(box.getAttribute("data-target"), 10);
            function pad(n) { return (n < 10 ? "0" : "") + n; }
            function tick() {
                var left = Math.max(0, Math.floor((target - Date.now()) / 1000));
                box.querySelector("[data-unit=d]").textContent = pad(Math.floor(left / 86400));
                box.querySelector("[data-unit=h]").textContent = pad(Math.floor((left % 86400) / 3600));
                box.querySelector("[data-unit=m]").textContent = pad(Math.floor((left % 3600) / 60));
                box.querySelector("[data-unit=s]").textContent = pad(left % 60);
                if (left === 0) {
                    box.querySelector("[data-state]").innerHTML = \'<span class="badge bg-danger ms-2">vencido</span>\';
                    clearInterval(timer);
                }
            }
            var timer = setInterval(tick, 1000);
            tick();
        })();
        </script>';

    }

    /******************************************************************************/
    /**
     * Imprime un estado vacío centrado con icono, título y mensaje.
     *
     * Título y mensaje se escapan con htmlspecialchars (RN-004); el icono se sanea a [a-z0-9_-]
     * (por defecto 'bi-inbox'). Con título vacío imprime una alerta (alertPostData).
     *
     * @param string $Titulo   Título del estado vacío (obligatorio).
     * @param string $Mensaje  Mensaje secundario (opcional).
     * @param array  $Opciones 'icono' (clase Bootstrap Icons, ej. 'bi-folder'), 'clase' e 'id'.
     *
     * @return void Imprime el HTML del widget (o la alerta de error).
     *
     * @example
     * ```php
     * $Common = new UIWidgetsCommon();
     * $Common->widgetEmptyState('Sin resultados', 'Prueba con otro filtro.', ['icono' => 'bi-search']);
     * // imprime el bloque centrado con icono bi-search
     * ```
     */
    public function widgetEmptyState(string $Titulo, string $Mensaje = '', array $Opciones = []){

        /**********************  Validaciones   **********************/
        if(trim($Titulo) === ''){
            echo $this->alertPostData(4, 4, 'bi bi-exclamation-circle', 1, 'No ha ingresado el titulo del estado vacio.');
            return;
        }

        /**********************  Definiciones   **********************/
        $titulo  = htmlspecialchars($Titulo, ENT_QUOTES, 'UTF-8');
        $mensaje = htmlspecialchars($Mensaje, ENT_QUOTES, 'UTF-8');
        $icono   = preg_replace('/[^a-z0-9_-]/', '', strtolower((string)($Opciones['icono'] ?? '')));
        if($icono === ''){ $icono = 'bi-inbox'; }
        $clase   = $this->_widgetClass($Opciones['clase'] ?? '');
        $idAttr  = isset($Opciones['id']) ? ' id="'.$this->_widgetId($Opciones['id'], 'empty_').'"' : '';

        /**********************  Retorno datos  **********************/
        //Imprimir dato
        echo '
        <div class="text-center text-muted py-5 '.$clase.'"'.$idAttr.'>
            <i class="bi '.$icono.' display-4 d-block mb-2"></i>
            <h5>'.$titulo.'</h5>'.
            ($mensaje !== '' ? '<p class="mb-0">'.$mensaje.'</p>' : '').'
        </div>';

    }

    /******************************************************************************/
    /**
     * Imprime una barra de progreso Bootstrap con atributos de accesibilidad.
     *
     * El porcentaje se acota a 0..100 (NaN o negativo = 0). Sin 'color' se elige por umbral:
     * <34 danger, <67 warning, resto success. El color se limita al catálogo Bootstrap.
     *
     * @param float $Porcentaje Porcentaje de avance.
     * @param array $Opciones   'color' (primary|secondary|success|info|warning|danger), 'titulo', 'clase', 'id'.
     *
     * @return void Imprime el HTML de la barra.
     *
     * @example
     * ```php
     * $Common = new UIWidgetsCommon();
     * $Common->widgetProgress(72.5, ['titulo' => 'Avance']); // barra verde al 72.5%
     * $Common->widgetProgress(150);                           // se acota a 100
     * ```
     */
    public function widgetProgress(float $Porcentaje, array $Opciones = []){

        /**********************  Definiciones   **********************/
        //Acotado (NaN -> 0)
        $valor   = is_nan($Porcentaje) ? 0.0 : min(100.0, max(0.0, $Porcentaje));
        $valor   = round($valor, 2);
        $colores = ['primary', 'secondary', 'success', 'info', 'warning', 'danger'];
        $color   = (string)($Opciones['color'] ?? '');
        if(!in_array($color, $colores, true)){
            $color = ($valor < 34) ? 'danger' : (($valor < 67) ? 'warning' : 'success');
        }
        $titulo  = htmlspecialchars((string)($Opciones['titulo'] ?? ''), ENT_QUOTES, 'UTF-8');
        $clase   = $this->_widgetClass($Opciones['clase'] ?? '');
        $idAttr  = isset($Opciones['id']) ? ' id="'.$this->_widgetId($Opciones['id'], 'progress_').'"' : '';
        $cabecera = ($titulo !== '') ? '<div class="d-flex justify-content-between mb-1"><small>'.$titulo.'</small><small>'.$valor.'%</small></div>' : '';

        /**********************  Retorno datos  **********************/
        //Imprimir dato
        echo '
        <div class="'.$clase.'"'.$idAttr.'>
            '.$cabecera.'
            <div class="progress" role="progressbar" aria-valuenow="'.$valor.'" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar bg-'.$color.'" style="width: '.$valor.'%"></div>
            </div>
        </div>';

    }

    /******************************************************************************/
    /**
     * Imprime un carrusel Bootstrap a partir de una lista de ítems.
     *
     * Cada ítem admite 'imagen' (URL http/https o ruta relativa), 'titulo' y 'texto'; todo se escapa
     * (RN-004). Con lista vacía imprime una alerta de sin datos (alertPostData). Con un solo ítem se
     * omiten los indicadores.
     *
     * @param array $Items    Ítems [['imagen'=>url,'titulo'=>'...','texto'=>'...'], ...].
     * @param array $Opciones 'id', 'controles' (bool, default true), 'indicadores' (bool, default true), 'intervalo' (ms, default 5000).
     *
     * @return void Imprime el HTML del carrusel (o la alerta de sin datos).
     *
     * @example
     * ```php
     * $Common = new UIWidgetsCommon();
     * $Common->widgetCarousel([
     *     ['imagen' => '/img/a.jpg', 'titulo' => 'Uno', 'texto' => 'Primera diapositiva'],
     *     ['imagen' => '/img/b.jpg', 'titulo' => 'Dos'],
     * ], ['id' => 'car_home', 'intervalo' => 4000]);
     * ```
     */
    public function widgetCarousel(array $Items, array $Opciones = []){

        /**********************  Validaciones   **********************/
        $Items = array_values(array_filter($Items, 'is_array'));
        if(count($Items) === 0){
            echo $this->alertPostData(5, 4, 'bi bi-exclamation-triangle-fill', 0, 'No hay elementos para mostrar en el carrusel.');
            return;
        }

        /**********************  Definiciones   **********************/
        $id          = $this->_widgetId($Opciones['id'] ?? '', 'carousel_');
        $controles   = (bool)($Opciones['controles'] ?? true);
        $indicadores = (bool)($Opciones['indicadores'] ?? true) && count($Items) > 1;
        $intervalo   = max(0, (int)($Opciones['intervalo'] ?? 5000));
        $slides = '';
        $dots   = '';

        /********************** Si todo esta ok **********************/
        foreach($Items as $i => $item){
            $imagen = trim((string)($item['imagen'] ?? ''));
            //Solo http(s) o rutas relativas (descarta javascript:, data:, etc.)
            if($imagen !== '' && preg_match('#^[a-z][a-z0-9+.-]*:#i', $imagen) && !preg_match('#^https?://#i', $imagen)){
                $imagen = '';
            }
            $imagen = htmlspecialchars($imagen, ENT_QUOTES, 'UTF-8');
            $titulo = htmlspecialchars((string)($item['titulo'] ?? ''), ENT_QUOTES, 'UTF-8');
            $texto  = htmlspecialchars((string)($item['texto'] ?? ''), ENT_QUOTES, 'UTF-8');
            $active = ($i === 0) ? ' active' : '';
            $slides .= '
                <div class="carousel-item'.$active.'">'.
                    ($imagen !== '' ? '<img src="'.$imagen.'" class="d-block w-100" alt="'.$titulo.'">' : '').
                    (($titulo !== '' || $texto !== '') ? '<div class="carousel-caption d-none d-md-block">'.
                        ($titulo !== '' ? '<h5>'.$titulo.'</h5>' : '').
                        ($texto !== '' ? '<p>'.$texto.'</p>' : '').'</div>' : '').'
                </div>';
            $dots .= '<button type="button" data-bs-target="#'.$id.'" data-bs-slide-to="'.$i.'"'.
                     ($i === 0 ? ' class="active" aria-current="true"' : '').' aria-label="Diapositiva '.($i + 1).'"></button>';
        }

        /**********************  Retorno datos  **********************/
        //Imprimir dato
        echo '
        <div id="'.$id.'" class="carousel slide" data-bs-ride="carousel" data-bs-interval="'.$intervalo.'">'.
            ($indicadores ? '<div class="carousel-indicators">'.$dots.'</div>' : '').'
            <div class="carousel-inner">'.$slides.'
            </div>'.
            ($controles ? '
            <button class="carousel-control-prev" type="button" data-bs-target="#'.$id.'" data-bs-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Anterior</span></button>
            <button class="carousel-control-next" type="button" data-bs-target="#'.$id.'" data-bs-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Siguiente</span></button>' : '').'
        </div>';

    }

    /******************************************************************************/
    /**
     * Imprime un botón que copia un texto al portapapeles (navigator.clipboard con fallback execCommand).
     *
     * El texto se escapa en el atributo data-copy y se deja visible en un code (sin JS el texto sigue
     * disponible). Con texto vacío imprime una alerta (alertPostData). Tras copiar muestra "¡Copiado!".
     *
     * @param string $Texto    Texto a copiar (obligatorio).
     * @param array  $Opciones 'etiqueta' (texto del botón, default 'Copiar'), 'clase' (clases del botón) e 'id'.
     *
     * @return void Imprime el HTML + JS del widget (o la alerta de error).
     *
     * @example
     * ```php
     * $Common = new UIWidgetsCommon();
     * $Common->widgetCopyToClipboard('https://mi-sitio.cl/ref/123', ['etiqueta' => 'Copiar enlace']);
     * // imprime code + botón que copia el enlace
     * ```
     */
    public function widgetCopyToClipboard(string $Texto, array $Opciones = []){

        /**********************  Validaciones   **********************/
        if(trim($Texto) === ''){
            echo $this->alertPostData(4, 4, 'bi bi-exclamation-circle', 1, 'No ha ingresado el texto a copiar.');
            return;
        }

        /**********************  Definiciones   **********************/
        $id       = $this->_widgetId($Opciones['id'] ?? '', 'copy_');
        $etiqueta = htmlspecialchars((string)(($Opciones['etiqueta'] ?? '') !== '' ? $Opciones['etiqueta'] : 'Copiar'), ENT_QUOTES, 'UTF-8');
        $clase    = $this->_widgetClass($Opciones['clase'] ?? '');
        if($clase === ''){ $clase = 'btn btn-sm btn-outline-secondary'; }
        $texto    = htmlspecialchars($Texto, ENT_QUOTES, 'UTF-8');

        /**********************  Retorno datos  **********************/
        //Imprimir dato
        echo '
        <span class="d-inline-flex align-items-center gap-2">
            <code>'.$texto.'</code>
            <button type="button" id="'.$id.'" class="'.$clase.'" data-copy="'.$texto.'">'.$etiqueta.'</button>
        </span>
        <script>
        (function() {
            var btn = document.getElementById('.json_encode($id).');
            if (!btn) { return; }
            var original = btn.textContent;
            function done() {
                btn.textContent = "¡Copiado!";
                setTimeout(function() { btn.textContent = original; }, 1500);
            }
            function fallback(text) {
                var ta = document.createElement("textarea");
                ta.value = text;
                ta.style.position = "fixed";
                ta.style.opacity = "0";
                document.body.appendChild(ta);
                ta.select();
                try { document.execCommand("copy"); done(); } catch (e) {}
                document.body.removeChild(ta);
            }
            btn.addEventListener("click", function() {
                var text = btn.getAttribute("data-copy");
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(done, function() { fallback(text); });
                } else {
                    fallback(text);
                }
            });
        })();
        </script>';

    }

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Privados                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	/**
	 * Sanea una lista de clases CSS dejando solo [A-Za-z0-9 _-].
	 *
	 * @param mixed $Clase Clases informadas por el usuario.
	 *
	 * @return string Clases saneadas ('' si no queda nada).
	 *
	 * @example
	 * ```php
	 * $this->_widgetClass('mt-3 "><script>'); // 'mt-3 script'
	 * ```
	 */
	private function _widgetClass($Clase): string {

		/**********************  Retorno datos  **********************/
		return trim(preg_replace('/[^A-Za-z0-9 _-]/', '', (string)$Clase));

	}

	/******************************************************************************/
	/**
	 * Sanea un id HTML a [A-Za-z0-9_-]; si queda vacío genera uno único con el prefijo indicado.
	 *
	 * @param mixed  $Id      Id informado por el usuario.
	 * @param string $Prefijo Prefijo del id autogenerado.
	 *
	 * @return string Id seguro para atributos HTML y selectores.
	 *
	 * @example
	 * ```php
	 * $this->_widgetId('mi id!', 'copy_'); // 'miid'
	 * $this->_widgetId('', 'copy_');       // 'copy_a1b2c3d4e5f6'
	 * ```
	 */
	private function _widgetId($Id, string $Prefijo): string {

		/**********************  Definiciones   **********************/
		$id = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$Id);

		/**********************  Retorno datos  **********************/
		return ($id !== '') ? $id : $Prefijo.bin2hex(random_bytes(6));

	}

}
