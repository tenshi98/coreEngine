<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/**
 * Clase UIWidgetsViews
 * * Genera bloques de visualizacion (listas, fichas, metadatos, barras de progreso, encabezados y
 * retratos) a partir de arreglos de datos, validando TODA la configuracion con
 * FunctionsDataValidations::checkData() antes de imprimir el HTML: si alguna opcion no pertenece al
 * catalogo permitido, el widget NO se imprime y en su lugar se muestran las alertas acumuladas.
 *
 * Anatomia comun de cualquier invocador (tabla o titulo):
 * ```php
 * $dataReturn = $WidgetsViews->table_common($arrData, $Options, $Fields); // normaliza y valida
 * if($dataReturn['nErrors'] == 0){ // HTML del widget } else { echo $dataReturn['alerts']; }
 * ```
 *
 * Validacion por vista: cada vista declara en FIELDS_ICON, FIELDS_TEXT, FIELDS_VALUE, FIELDS_CARD y
 * FIELDS_STAT los campos que pinta, y table_common() valida unicamente esos; asi un campo que la vista
 * no usa (p. ej. Value_color en table_v2) no puede bloquearla. Con $Fields vacio se validan los diez
 * campos, que es el comportamiento previo.
 *
 * Diagnostico por fila: las alertas del motor 1 llevan 'placeholder' => 'fila N' y las del motor 2
 * (validarTextoPlano, datos descriptivos) lo llevan dentro del label, porque su aviso interno de
 * datos no simples no usa placeholder. Los valores por defecto de la fila estan en ROW_DEFAULTS.
 *
 * Catalogo de claves de cada fila de $arrData (comun a table_v1 ... table_v7):
 *   Theme_color  string  'summary-theme-color-1' | '-2' | '-3'                                   (default 'summary-theme-color-1')
 *   Icon         string  clase del icono (ej: 'bi bi-bank'); '' = sin icono                      (default '')
 *   Icon_color   int     1..61   -> mapa colorsText() / colorsBackgroundBTN()                    (default 23  text-color-red)
 *   Title        string  rotulo del dato; '' = fila sin titulo                                   (default '')
 *   Title_color  int     1..61   -> mapa colorsText()                                            (default 17  text-muted)
 *   Text         string  valor del dato                                                          (default '')
 *   Text_color   int     1..61   -> mapa colorsText()                                            (default 17  text-muted)
 *   Text_copy    string  si trae valor se agrega el boton copiar al portapapeles                 (default '')
 *   Value        int     0..100; lo usa table_v5 (barra de progreso)                             (default 100)
 *   Value_color  string  'bg-empty'|'bg-success'|'bg-info'|'bg-warning'|'bg-danger'              (default 'bg-empty')
 *
 * Catalogo de colores (mapas internos colorsText / colorsBackgroundBTN / colorsBG / colorsTitle):
 *   1..16    colores base del sistema        (text-base-color01 ... text-base-color16)
 *   17..22   utilitarios Bootstrap/MDB       (text-muted, text-primary, text-warning, text-danger, ...)
 *   23..41   paleta solida                   (text-color-red, ...-light, ...-dark, text-color-white)
 *   42..61   paleta MDB                      (text-color-red-text ... text-color-grey-text)
 *   colorsBG   1..103 fondos (standart, pastel, lighten-1 y rgba-*-slight/light/strong)
 *   colorsTitle 1..39 usa los MISMOS nombres de 23..61 numerados 1..39 (1 => text-color-red, 39 => text-color-grey-text)
 */
class UIWidgetsViews {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                 Instancias                                                      */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	//Definiciones
	private $DataValidations;

	//Icono por defecto de las listas (table_v1, table_v2 y table_v3)
	private const DEFAULT_ICON = 'bi bi-chevron-double-right';

	//Imagen por defecto de la cabecera de perfil (headerProfile)
	private const DEFAULT_IMG_HEADER = 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3';

	//Imagen por defecto del retrato del usuario (_imgPortrait), ruta relativa a $BASE
	private const DEFAULT_IMG_PORTRAIT = '/img/picture-img.jpg';

	//Valores por defecto de cada fila: fuente unica que table_common() usa para normalizar
	private const ROW_DEFAULTS = [
		'Theme_color' => 'summary-theme-color-1',
		'Icon'        => '',
		'Icon_color'  => 23,  // text-color-red
		'Title'       => '',
		'Title_color' => 17,  // text-muted
		'Text'        => '',
		'Text_color'  => 17,  // text-muted
		'Text_copy'   => '',
		'Value'       => 100,
		'Value_color' => 'bg-empty',
	];

	//Campos que pinta y valida cada vista: lo que la vista no muestra no debe bloquearla
	private const FIELDS_ICON  = ['Icon', 'Icon_color', 'Title', 'Title_color', 'Text', 'Text_color', 'Text_copy'];                 // table_v1, table_v2 y table_v3
	private const FIELDS_TEXT  = ['Title', 'Title_color', 'Text', 'Text_color', 'Text_copy'];                                       // table_v4
	private const FIELDS_VALUE = ['Title', 'Title_color', 'Value', 'Value_color'];                                                  // table_v5
	private const FIELDS_CARD  = ['Theme_color', 'Icon', 'Icon_color', 'Title', 'Title_color', 'Text', 'Text_color', 'Text_copy'];  // table_v6
	private const FIELDS_STAT  = ['Theme_color', 'Title', 'Title_color', 'Text', 'Text_color'];                                     // table_v7

	/************************************************************************************************************/
	//Instancias
	/**
	 * Crea las instancias que la clase necesita (el validador de datos) al construir el objeto.
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * $WidgetsViews = new UIWidgetsViews();
	 * ```
	 */
	public function __construct() {
		$this->DataValidations = new FunctionsDataValidations();

	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/************************************************************************************************************/
	/**
	 * Normaliza y valida la configuracion comun de todos los bloques de datos (table_v1 ... table_v7).
	 * * Es el unico punto de validacion de las tablas: resuelve el valor por defecto de cada opcion
	 * (self::ROW_DEFAULTS), calcula el ancho de columna segun 'Cols' y delega la verificacion en
	 * checkData() con dos motores: el 1 para pertenencia estricta a la lista blanca de colores y
	 * valores, y el 2 con validarTextoPlano() para los datos descriptivos (impide que un arreglo u
	 * objeto provoque el aviso "Array to string conversion" al interpolarse en el HTML).
	 * * Cada vista valida solo los campos que pinta (parametro $Fields, ver FIELDS_ICON, FIELDS_TEXT,
	 * FIELDS_VALUE, FIELDS_CARD y FIELDS_STAT): un campo no usado no debe bloquear la vista.
	 * * NO imprime nada: retorna los datos normalizados para que cada vista construya su HTML.
	 *
	 * @param array $arrData Filas con las claves Theme_color, Icon, Icon_color, Title, Title_color,
	 *              Text, Text_color, Text_copy, Value y Value_color (catalogo en la cabecera de la
	 *              clase). Las claves ausentes o vacias toman su valor por defecto.
	 * @param array $Options Opciones del bloque: 'Cols' (1..6, default 1) e 'internalCol' (1..11,
	 *              default 8; ancho de la columna del valor, la del titulo es 12 - internalCol).
	 * @param array $Fields Campos de la fila que la vista pinta y por lo tanto valida (una constante
	 *              FIELDS_* de esta clase). Vacio => valida los diez campos (comportamiento previo).
	 *
	 * @return array Estructura con nErrors, alerts, internalCol, Cols, TituloCol, div_col y una
	 *              entrada por fila (indice 1..n) con las diez claves de la fila ya normalizadas.
	 *
	 * @example
	 * ```php
	 * $arrData = [['Icon' => 'bi bi-bank', 'Title' => 'Email', 'Text' => 'usuario@empresa.com']];
	 * $dataReturn = $WidgetsViews->table_common($arrData, ['Cols' => 2], ['Title', 'Text']);
	 * if($dataReturn['nErrors'] == 0){ echo $dataReturn['div_col']; } // 6 => col-md-6
	 * ```
	 */
    public function table_common($arrData, $Options = [], $Fields = []): array {

		/**********************  Definiciones   **********************/
		//se calcula tamaño de la columna
		$Return       = [];
		$count        = 1;
		$errorn       = 0;
		$alerts       = '';

		// Definir opciones válidas
		$validOptions = [
			'Cols'         => range(1, 6),
			'internalCol'  => range(1, 11), // 12 dejaria la columna del titulo en 0 (clase inexistente)
			'Icon_color'   => range(1, 61),
			'Title_color'  => range(1, 61),
			'Text_color'   => range(1, 61),
			'Value'        => range(0, 100),
			'Value_color'  => ['bg-empty', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger'],
			'Theme_color'  => ['summary-theme-color-1', 'summary-theme-color-2', 'summary-theme-color-3'],
		];

		// Ancho de cada columna segun la cantidad solicitada (la grilla es de 12 y no existe col-5)
		$arrColsClass = [1 => 12, 2 => 6, 3 => 4, 4 => 3, 5 => 3, 6 => 2];

		// Campos de cada fila a validar: colores y valores por pertenencia (motor 1) y datos
		// descriptivos por contenido (motor 2, validarTextoPlano)
		$arrColorFields = [
			'Icon_color'  => '$Icon_color',
			'Title_color' => '$Title_color',
			'Text_color'  => '$Text_color',
			'Value'       => '$Value',
			'Value_color' => '$Value_color',
			'Theme_color' => '$Theme_color',
		];
		$arrTextFields = ['Icon' => '$Icon', 'Title' => '$Title', 'Text' => '$Text', 'Text_copy' => '$Text_copy'];

		/*************************************/
		// Verifico
		$Return['internalCol']   = $this->_option($Options, 'internalCol', 8);
		$Return['Cols']          = $this->_option($Options, 'Cols', 1);
		$Return['TituloCol']     = 12 - $Return['internalCol'];
		//Selecciono
		$Return['div_col']       = $arrColsClass[$Return['Cols']] ?? 12;

		// Opciones a validar
		$optionsToCheck = [
			['value' => $Return['internalCol'],   'name' => 'internalCol',   'label' => '$internalCol'],
			['value' => $Return['Cols'],          'name' => 'Cols',          'label' => '$Cols'],
		];

		/**********************  Validaciones   **********************/
		// Definicion de errores
		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, '', 1);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		// Recorro
		foreach ( $arrData as $row ) {
			/*************************************/
			// Verifico los valores de la fila (los ausentes o vacios toman su default)
			foreach ( self::ROW_DEFAULTS as $field => $default ) {
				$Return[$count][$field] = $this->_option($row, $field, $default);
			}

			/*************************************/
			// Opciones a validar (motor 1): unicamente los campos que la vista pinta
			$optionsToCheck = [];
			foreach ( $arrColorFields as $field => $label ) {
				if ( !empty($Fields) && !in_array($field, $Fields, true) ) { continue; }
				$optionsToCheck[] = ['value' => $Return[$count][$field], 'name' => $field, 'label' => $label, 'placeholder' => 'fila '.$count];
			}

			/*************************************/
			// Validacion por pertenencia
			if ( !empty($optionsToCheck) ) {
				$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, '', 1);
				$errorn += $dataReturn['nErrors'];
				$alerts .= $dataReturn['alerts'];
			}

			/*************************************/
			// Datos descriptivos (motor 2): rechaza arreglos y objetos antes de interpolarlos en el HTML.
			// La fila va en el label porque el aviso interno de checkData() para datos no simples no
			// utiliza el placeholder del campo.
			$fieldsToCheck = [];
			foreach ( $arrTextFields as $field => $label ) {
				if ( !empty($Fields) && !in_array($field, $Fields, true) ) { continue; }
				$fieldsToCheck[] = ['value' => $Return[$count][$field], 'method' => 'validarTextoPlano', 'label' => $label.' (fila '.$count.')', 'msg' => 'no es un texto valido', 'placeholder' => 'fila '.$count];
			}

			/*************************************/
			// Validacion de contenido
			if ( !empty($fieldsToCheck) ) {
				$dataReturn = $this->DataValidations->checkData('', $fieldsToCheck, '', 2);
				$errorn += $dataReturn['nErrors'];
				$alerts .= $dataReturn['alerts'];
			}

			$count++;
		}

		$Return['nErrors'] = $errorn;
		$Return['alerts']  = $alerts;

		return $Return;

	}
	/************************************************************************************************************/
	/**
	 * Imprime una lista de filas "titulo : valor" con icono por fila y boton de copiado opcional.
	 * * Usa table_common() para normalizar y validar los datos: si hay errores imprime las alertas
	 * y NO el widget (nada de HTML a medias).
	 *
	 * @param array $arrData Filas con las claves del catalogo (ver cabecera de la clase).
	 * @param array $Options 'Cols' (1..6) e 'internalCol' (1..11): ancho del rotulo y del valor.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $WidgetsViews->table_v1($arrData, ['Cols' => 2, 'internalCol' => 8]);
	 * ```
	 */
    public function table_v1($arrData, $Options = []): void {

		/**********************  Definiciones   **********************/
		// Variables
		$count = 1;

		// Obtengo los datos
		$dataReturn = $this->table_common($arrData, $Options, self::FIELDS_ICON);
		$errorn = $dataReturn['nErrors'];
		$alerts = $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
        // Ejecucion si no hay errores
        if($errorn == 0){
			// Variable vacia
			$input = '<div class="'.$this->_colClass($dataReturn['div_col']).'">';
				// Recorro
				foreach ( $arrData as $data ) {
					/*************************************/
					// Datos normalizados de la fila
					$row  = $dataReturn[$count];
					$Icon = $this->_icon($row);
					/*************************************/
					// Verifico si existe un titulo
					if($row['Title'] != ''){
						// Se genera input
						$input.= '
						<div class="row">
							<div class="'.$this->_colClass($dataReturn['TituloCol']).' label ">
								<i class="'.$Icon.'"></i> <span class="'.$this->colorsText($row['Title_color']).'">'.$row['Title'].'</span>
							</div>
							<div class="'.$this->_colClass($dataReturn['internalCol']).'">'.$this->_valueField($row, 9).'
							</div>
						</div>';
					/*************************************/
					// Verifico si no existe el titulo
					}else{
						// Se genera input
						$input.= '
						<div class="row">
							<div class="'.$this->_colClass(12).'">'.$this->_valueField($row, 9).'
							</div>
						</div>';
					}
					// Se aumenta contador
					$count++;
				}
			// Cierro
			$input.= '</div>';

			// Imprimir dato
			echo $input;
        }else{
			// Imprimir dato
			echo $alerts;
		}

    }
	/************************************************************************************************************/
	/**
	 * Imprime una lista de tarjetas "titulo + valor" (icono y boton de copiado opcionales).
	 * * Usa table_common() para normalizar y validar los datos; ante errores imprime las alertas.
	 *
	 * @param array $arrData Filas con las claves del catalogo (ver cabecera de la clase).
	 * @param array $Options 'Cols' (1..6) e 'internalCol' (1..11).
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $WidgetsViews->table_v2($arrData, ['Cols' => 3]);
	 * ```
	 */
    public function table_v2($arrData, $Options = []): void {

		/**********************  Definiciones   **********************/
		// Variables
		$count = 1;

		// Obtengo los datos
		$dataReturn = $this->table_common($arrData, $Options, self::FIELDS_ICON);
		$errorn = $dataReturn['nErrors'];
		$alerts = $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
        // Ejecucion si no hay errores
        if($errorn == 0){
			// Variable vacia
			$input = '<div class="'.$this->_colClass($dataReturn['div_col']).'">';
				$input.= '<div class="data-card-list">';
					// Recorro
					foreach ( $arrData as $data ) {
						// Datos normalizados de la fila
						$row  = $dataReturn[$count];
						$Icon = $this->_icon($row);
						// Se genera input
						$input.= '
						<div class="data-card-list-item">
							<i class="'.$Icon.'"></i>
							<strong class="'.$this->colorsText($row['Title_color']).'">'.$row['Title'].'</strong>'.$this->_valueField($row, 8).'
						</div>';
						// Se aumenta contador
						$count++;
					}
				// Cierro
				$input.= '</div>';
			$input.= '</div>';

			// Imprimir dato
			echo $input;
        }else{
			// Imprimir dato
			echo $alerts;
		}

    }
	/************************************************************************************************************/
	/**
	 * Imprime una lista de metadatos con el icono dentro de un boton y el valor debajo del rotulo.
	 * * Usa table_common() para normalizar y validar los datos; ante errores imprime las alertas.
	 * * El icono se pinta con colorsBackgroundBTN() y sin color de texto.
	 *
	 * @param array $arrData Filas con las claves del catalogo (ver cabecera de la clase).
	 * @param array $Options 'Cols' (1..6) e 'internalCol' (1..11).
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $WidgetsViews->table_v3($arrData, ['Cols' => 3]);
	 * ```
	 */
    public function table_v3($arrData, $Options = []): void {

		/**********************  Definiciones   **********************/
		// Variables
		$count = 1;

		// Obtengo los datos
		$dataReturn = $this->table_common($arrData, $Options, self::FIELDS_ICON);
		$errorn = $dataReturn['nErrors'];
		$alerts = $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
        // Ejecucion si no hay errores
        if($errorn == 0){
			// Variable vacia
			$input = '<div class="'.$this->_colClass($dataReturn['div_col']).'">';
				$input.= '<div class="meta-list">';
					// Recorro
					foreach ( $arrData as $data ) {
						// Datos normalizados de la fila
						$row  = $dataReturn[$count];
						$Icon = $this->_icon($row, false);
						// Se genera input
						$input.= '
						<div class="meta-list__item">
							<div class="btn '.$this->colorsBackgroundBTN($row['Icon_color']).'">
								<i class="'.$Icon.'"></i>
							</div>
							<div>
								<div class="meta-list__label '.$this->colorsText($row['Title_color']).'">'.$row['Title'].'</div>'.$this->_valueField($row, 9, 'div', 'meta-list__value').'
							</div>
						</div>';
						// Se aumenta contador
						$count++;
					}
				// Cierro
				$input.= '</div>';
			$input.= '</div>';

			// Imprimir dato
			echo $input;
        }else{
			// Imprimir dato
			echo $alerts;
		}

    }
	/************************************************************************************************************/
	/**
	 * Imprime una lista compacta de pares clave/valor (sin icono).
	 * * Usa table_common() para normalizar y validar los datos; ante errores imprime las alertas.
	 *
	 * @param array $arrData Filas con las claves del catalogo (ver cabecera de la clase).
	 * @param array $Options 'Cols' (1..6) e 'internalCol' (1..11).
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $WidgetsViews->table_v4($arrData, ['Cols' => 3]);
	 * ```
	 */
    public function table_v4($arrData, $Options = []): void {

		/**********************  Definiciones   **********************/
		// Variables
		$count = 1;

		// Obtengo los datos
		$dataReturn = $this->table_common($arrData, $Options, self::FIELDS_TEXT);
		$errorn = $dataReturn['nErrors'];
		$alerts = $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
        // Ejecucion si no hay errores
        if($errorn == 0){
			// Variable vacia
			$input = '<div class="'.$this->_colClass($dataReturn['div_col']).'">';
				$input.= '<div class="quick-meta">';
					// Recorro
					foreach ( $arrData as $data ) {
						// Datos normalizados de la fila
						$row = $dataReturn[$count];
						// Se genera input
						$input.= '
						<div class="meta-row">
							<span class="meta-row__key '.$this->colorsText($row['Title_color']).'">'.$row['Title'].'</span>'.$this->_valueField($row, 8, 'span', 'meta-row__val').'
						</div>';
						// Se aumenta contador
						$count++;
					}
				// Cierro
				$input.= '</div>';
			$input.= '</div>';

			// Imprimir dato
			echo $input;
        }else{
			// Imprimir dato
			echo $alerts;
		}

    }
	/************************************************************************************************************/
	/**
	 * Imprime una lista de barras de progreso animadas (rotulo + valor 0..100).
	 * * Usa table_common() para normalizar y validar los datos; ante errores imprime las alertas.
	 * * Es la unica vista que usa 'Value' (porcentaje) y 'Value_color' (bg-* del progress-bar).
	 *
	 * @param array $arrData Filas con las claves del catalogo (ver cabecera de la clase).
	 * @param array $Options 'Cols' (1..6) e 'internalCol' (1..11).
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $WidgetsViews->table_v5($arrData, ['Cols' => 3]);
	 * ```
	 */
    public function table_v5($arrData, $Options = []): void {

		/**********************  Definiciones   **********************/
		// Variables
		$count = 1;

		// Obtengo los datos
		$dataReturn = $this->table_common($arrData, $Options, self::FIELDS_VALUE);
		$errorn = $dataReturn['nErrors'];
		$alerts = $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
        // Ejecucion si no hay errores
        if($errorn == 0){
			// Variable vacia
			$input = '<div class="'.$this->_colClass($dataReturn['div_col']).'">';
				// Recorro
				foreach ( $arrData as $data ) {
					// Datos normalizados de la fila
					$row = $dataReturn[$count];
					// Se genera input
					$input.= '
					<div class="skill-bar">
						<div class="skill-bar__top '.$this->colorsText($row['Title_color']).'">
							<span>'.$row['Title'].'</span>
							<span>'.$row['Value'].'</span>
						</div>
						<div class="progress" style="height: 10px;">
							<div class="progress-bar progress-bar-striped progress-bar-animated '.$row['Value_color'].'" role="progressbar" style="width: '.$row['Value'].'%" aria-valuenow="'.$row['Value'].'" aria-valuemin="0" aria-valuemax="100"></div>
						</div>
					</div>';
					// Se aumenta contador
					$count++;
				}
			// Cierro
			$input.= '</div>';

			// Imprimir dato
			echo $input;
        }else{
			// Imprimir dato
			echo $alerts;
		}

    }
	/************************************************************************************************************/
	/**
	 * Imprime una ficha resumen por columna (icono + rotulo + valor) con borde de tema.
	 * * Usa table_common() para normalizar y validar los datos; ante errores imprime las alertas.
	 * * Saca el bloque de columna completa, por lo que puede invocarse dentro de un <div class="row">.
	 *
	 * @param array $arrData Filas con las claves del catalogo (ver cabecera de la clase).
	 * @param array $Options 'Cols' (1..6) e 'internalCol' (1..11).
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $WidgetsViews->table_v6($arrData, ['Cols' => 3]);
	 * ```
	 */
    public function table_v6($arrData, $Options = []): void {

		/**********************  Definiciones   **********************/
		// Variables
		$count = 1;

		// Obtengo los datos
		$dataReturn = $this->table_common($arrData, $Options, self::FIELDS_CARD);
		$errorn = $dataReturn['nErrors'];
		$alerts = $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
        // Ejecucion si no hay errores
        if($errorn == 0){
			// Declaro
			$input = '';
			// Recorro
			foreach ( $arrData as $data ) {
				// Datos normalizados de la fila
				$row = $dataReturn[$count];
				// Se genera input
				$input.= '
				<div class="'.$this->_colClass($dataReturn['div_col']).'">
					<div class="summary-item '.$row['Theme_color'].'">';
						//Verifico si se envian datos para el icono
						if($row['Icon'] != ''){
							$input.= '<div class="summary-icon"><i class="'.$row['Icon'].' '.$this->colorsText($row['Icon_color']).'"></i></div>';
						}
						$input.= '
						<div class="summary-content">
							<span class="summary-label '.$this->colorsText($row['Title_color']).'">'.$row['Title'].'</span>'.$this->_valueField($row, 8, 'span', 'field-value', 'summary-field', 'summary-value').'
						</div>
					</div>
				</div>';
				// Se aumenta contador
				$count++;
			}

			// Imprimir dato
			echo $input;
        }else{
			// Imprimir dato
			echo $alerts;
		}

    }
	/************************************************************************************************************/
	/**
	 * Imprime una ficha de estadistica (valor grande sobre rotulo) con borde de tema, sin icono.
	 * * Usa table_common() para normalizar y validar los datos; ante errores imprime las alertas.
	 * * OJO: el rotulo se toma de 'Text' y el valor destacado de 'Title' (orden invertido respecto
	 * a las otras vistas).
	 *
	 * @param array $arrData Filas con las claves del catalogo (ver cabecera de la clase).
	 * @param array $Options 'Cols' (1..6) e 'internalCol' (1..11).
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $WidgetsViews->table_v7($arrData, ['Cols' => 6]);
	 * ```
	 */
    public function table_v7($arrData, $Options = []): void {

		/**********************  Definiciones   **********************/
		// Variables
		$count = 1;

		// Obtengo los datos
		$dataReturn = $this->table_common($arrData, $Options, self::FIELDS_STAT);
		$errorn = $dataReturn['nErrors'];
		$alerts = $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
        // Ejecucion si no hay errores
        if($errorn == 0){
			// Variable vacia
			$input = '';
			// Recorro
			foreach ( $arrData as $data ) {
				// Datos normalizados de la fila
				$row = $dataReturn[$count];
				// Se genera input
				$input.= '
				<div class="'.$this->_colClass($dataReturn['div_col']).'">
					<div class="summary-item '.$row['Theme_color'].'">
						<div class="summary-content summary-stat">
							<span class="summary-label '.$this->colorsText($row['Text_color']).'">'.$row['Text'].'</span>
							<span class="summary-value '.$this->colorsText($row['Title_color']).'">'.$row['Title'].'</span>
						</div>
					</div>
				</div>';
				// Se aumenta contador
				$count++;
			}

			// Imprimir dato
			echo $input;
        }else{
			// Imprimir dato
			echo $alerts;
		}

    }

    /************************************************************************************************************/
	/**
	 * Imprime el retrato del usuario con borde y esquinas redondeadas.
	 * * Si $Image viene vacio usa la imagen por defecto del sistema ($BASE.'/img/picture-img.jpg').
	 *
	 * @param string $BASE Ruta base del sistema (para la imagen por defecto).
	 * @param string $MainPathUrl Ruta del directorio de imagenes del usuario.
	 * @param string $Image Nombre del archivo de imagen del usuario.
	 *
	 * @return void Imprime la etiqueta <img>.
	 *
	 * @example
	 * ```php
	 * $WidgetsViews->imgPortrait_1($BASE, $data['UserData']['MainPathUrl'], $data['rowData']['Direccion_img']);
	 * ```
	 */
	public function imgPortrait_1($BASE, $MainPathUrl, $Image): void {
		//Se delega en el retrato comun agregando el borde
		$this->_imgPortrait($BASE, $MainPathUrl, $Image, 'square-border-3');
	}
    /************************************************************************************************************/
	/**
	 * Imprime el retrato del usuario con esquinas redondeadas (sin borde).
	 * * Si $Image viene vacio usa la imagen por defecto del sistema ($BASE.'/img/picture-img.jpg').
	 *
	 * @param string $BASE Ruta base del sistema (para la imagen por defecto).
	 * @param string $MainPathUrl Ruta del directorio de imagenes del usuario.
	 * @param string $Image Nombre del archivo de imagen del usuario.
	 *
	 * @return void Imprime la etiqueta <img>.
	 *
	 * @example
	 * ```php
	 * $WidgetsViews->imgPortrait_2($BASE, $data['UserData']['MainPathUrl'], $data['rowData']['Direccion_img']);
	 * ```
	 */
	public function imgPortrait_2($BASE, $MainPathUrl, $Image): void {
		//Se delega en el retrato comun sin el borde
		$this->_imgPortrait($BASE, $MainPathUrl, $Image);
	}

	/************************************************************************************************************/
	/**
	 * Normaliza y valida la configuracion comun de los titulos (tittle_v2, tittle_v3 y tittle_v4).
	 * * Resuelve los colores por defecto y delega la verificacion en checkData() (motor 1).
	 * * NO imprime nada: retorna los datos normalizados.
	 *
	 * @param array $Options Claves opcionales: 'Icon_color' (1..61, default 17 text-muted),
	 *              'Icon_bg_color' (1..103, default 43 bkg-grey-lighten-1), 'Title_color' (1..61,
	 *              default 17 text-muted) y 'Text_color' (1..61, default 17 text-muted).
	 *
	 * @return array Estructura con los cuatro colores ya normalizados mas nErrors y alerts.
	 *
	 * @example
	 * ```php
	 * $dataReturn = $WidgetsViews->tittle_common(['Title_color' => 20]);
	 * ```
	 */
    public function tittle_common($Options = []): array {

		/**********************  Definiciones   **********************/
		//se calcula tamaño de la columna
		$Return = [];
		$errorn = 0;
		$alerts = '';

		// Definir opciones válidas
		$validOptions = [
			'Icon_color'     => range(1, 61),
			'Icon_bg_color'  => range(1, 103),
			'Title_color'    => range(1, 61),
			'Text_color'     => range(1, 61),
		];

		/**********************  Validaciones   **********************/
		/*************************************/
		// Verifico
		$Return['Icon_color']    = $this->_option($Options, 'Icon_color', 17);    // text-muted
		$Return['Icon_bg_color'] = $this->_option($Options, 'Icon_bg_color', 43); // bkg-grey-lighten-1
		$Return['Title_color']   = $this->_option($Options, 'Title_color', 17);   // text-muted
		$Return['Text_color']    = $this->_option($Options, 'Text_color', 17);    // text-muted

		/*************************************/
		// Opciones a validar
		$optionsToCheck = [
			['value' => $Return['Icon_color'],    'name' => 'Icon_color',     'label' => '$Icon_color'],
			['value' => $Return['Icon_bg_color'], 'name' => 'Icon_bg_color',  'label' => '$Icon_bg_color'],
			['value' => $Return['Title_color'],   'name' => 'Title_color',    'label' => '$Title_color'],
			['value' => $Return['Text_color'],    'name' => 'Text_color',     'label' => '$Text_color'],
		];

		/*************************************/
		// Validacion
		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, '', 1);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		$Return['nErrors'] = $errorn;
		$Return['alerts']  = $alerts;

		// Retorno datos
		return $Return;

	}
    /************************************************************************************************************/
	/**
	 * Imprime un titulo simple con la etiqueta HTML indicada y color del catalogo colorsTitle().
	 * * No pasa por tittle_common(): valida 'Type' y 'TextColor' por separado (motor 1).
	 *
	 * @param string $Text Texto del titulo.
	 * @param string $Type Etiqueta HTML: 'h1', 'h2', 'h3', 'h4', 'h5', 'p' o 'strong'.
	 * @param int $TextColor Color 1..39 del catalogo colorsTitle() (default 3 text-color-red-dark).
	 *
	 * @return void Imprime la etiqueta con la clase 'box-title' o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $WidgetsViews->tittle_v1('Datos del Perfil', 'h5');
	 * ```
	 */
	public function tittle_v1($Text, $Type, $TextColor = 3): void {

		// Definir opciones válidas
		$validOptions = [
			'TextColor' => range(1, 39),
			'Type'      => ['h1', 'h2', 'h3', 'h4', 'h5', 'p', 'strong'],
		];

		// Opciones a validar
		$optionsToCheck = [
			['value' => $TextColor,  'name' => 'TextColor',  'label' => '$TextColor'],
			['value' => $Type,       'name' => 'Type',       'label' => '$Type'],
		];

		/**********************  Validaciones   **********************/
		// Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, '', 1);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
        // Ejecucion si no hay errores
        if($errorn==0){
			echo '<'.$Type.' class="box-title '.$this->colorsTitle($TextColor).'">'.$Text.'</'.$Type.'>';
		}else{
			echo $alerts;
		}

	}
    /************************************************************************************************************/
	/**
	 * Imprime un titulo de seccion (summary-title-1) a todo el ancho.
	 * * Valida el color a traves de tittle_common() (1..61) y usa el valor normalizado para pintar.
	 *
	 * @param string $Text Texto del titulo.
	 * @param int $TextColor Color 1..61 del catalogo colorsText() (default 20 text-color-mdb-text).
	 *
	 * @return void Imprime el HTML del titulo o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $WidgetsViews->tittle_v2('Tablas', 23);
	 * ```
	 */
	public function tittle_v2($Text, $TextColor = 20): void {

		// Valores
		$Options['Text_color'] = $TextColor;

		// Obtengo los datos
		$dataReturn = $this->tittle_common($Options);
		$errorn = $dataReturn['nErrors'];
		$alerts = $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
        // Ejecucion si no hay errores
        if($errorn==0){
			echo '
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                <div class="summary-title-1 '.$this->colorsText($dataReturn['Text_color']).'">'.$Text.'</div>
            </div>';
		}else{
			echo $alerts;
		}

	}
    /************************************************************************************************************/
	/**
	 * Imprime un titulo de seccion alternativo (summary-title-2) a todo el ancho.
	 * * Valida el color a traves de tittle_common() (1..61) y usa el valor normalizado para pintar.
	 *
	 * @param string $Text Texto del titulo.
	 * @param int $TextColor Color 1..61 del catalogo colorsText() (default 20 text-color-mdb-text).
	 *
	 * @return void Imprime el HTML del titulo o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $WidgetsViews->tittle_v3('Tabla barras', 1);
	 * ```
	 */
	public function tittle_v3($Text, $TextColor = 20): void {

		// Valores
		$Options['Text_color'] = $TextColor;

		// Obtengo los datos
		$dataReturn = $this->tittle_common($Options);
		$errorn = $dataReturn['nErrors'];
		$alerts = $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
        // Ejecucion si no hay errores
        if($errorn==0){
			echo '
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                <div class="summary-title-2 '.$this->colorsText($dataReturn['Text_color']).'">'.$Text.'</div>
            </div>';
		}else{
			echo $alerts;
		}

	}
    /************************************************************************************************************/
	/**
	 * Imprime el encabezado de un registro: titulo, identificador y, opcionalmente, un icono.
	 * * Valida los colores a traves de tittle_common(); el icono solo se imprime si viene informado.
	 *
	 * @param string $Title Titulo del registro (obligatorio).
	 * @param string $Text Identificador o dato secundario del registro (opcional).
	 * @param int $Title_color Color 1..61 del catalogo colorsText() (default 17 text-muted).
	 * @param int $Text_color Color 1..61 del catalogo colorsText() (default 17 text-muted).
	 * @param string $Icon Clase del icono (ej: 'bi bi-bell'); '' = sin icono.
	 * @param int $Icon_color Color 1..61 del icono (default 17 text-muted).
	 * @param int $Icon_bg_color Fondo 1..103 del icono, via colorsBG() (default 43 bkg-grey-lighten-1).
	 *
	 * @return void Imprime el HTML del encabezado o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $WidgetsViews->tittle_v4('Perfil de usuario', 'REG-0001', 15, 19, 'bi bi-bell', 20, 74);
	 * ```
	 */
	public function tittle_v4($Title, $Text = '', $Title_color = '', $Text_color = '', $Icon = '', $Icon_color = '', $Icon_bg_color = ''): void {

		// Valores
		$Options['Title_color']   = $Title_color;
		$Options['Text_color']    = $Text_color;
		$Options['Icon_color']    = $Icon_color;
		$Options['Icon_bg_color'] = $Icon_bg_color;

		// Obtengo los datos
		$dataReturn = $this->tittle_common($Options);
		$errorn = $dataReturn['nErrors'];
		$alerts = $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
        // Ejecucion si no hay errores
        if($errorn==0){
			echo '
			<div class="record-header d-flex align-items-center justify-content-between">
				<div class="record-header-identity d-flex align-items-center gap-3">';
					//Verifico siexiste el icono
					if($Icon != ''){
						echo '
						<span class="record-header-icon '.$this->colorsBG($dataReturn['Icon_bg_color']).'">
							<i class="'.$Icon.' '.$this->colorsText($dataReturn['Icon_color']).'"></i>
						</span>';
					}
					echo '
					<div>
						<h2 class="record-title '.$this->colorsText($dataReturn['Title_color']).'">'.$Title.'</h2>';
						// Verifico
						if($Text != ''){
							echo '<p class="record-id '.$this->colorsText($dataReturn['Text_color']).'">'.$Text.'</p>';
						}
						echo '
					</div>
				</div>
			</div>';
		}else{
			echo $alerts;
		}

	}

    /************************************************************************************************************/
	/**
	 * Normaliza y valida la configuracion comun de los encabezados de perfil (headerProfile).
	 * * Resuelve los colores por defecto y delega la verificacion en checkData() (motor 1).
	 * * NO imprime nada: retorna los datos normalizados.
	 *
	 * @param array $Options Claves opcionales: 'Title_color' (1..61, default 17 text-muted),
	 *              'SubTitle_color' (1..61, default 17 text-muted) y 'Text_color' (1..61,
	 *              default 17 text-muted).
	 *
	 * @return array Estructura con los tres colores ya normalizados mas nErrors y alerts.
	 *
	 * @example
	 * ```php
	 * $dataReturn = $WidgetsViews->header_common(['Title_color' => 13]);
	 * ```
	 */
    public function header_common($Options = []): array {

		/**********************  Definiciones   **********************/
		//se calcula tamaño de la columna
		$Return = [];
		$errorn = 0;
		$alerts = '';

		// Definir opciones válidas
		$validOptions = [
			'Title_color'    => range(1, 61),
			'SubTitle_color' => range(1, 61),
			'Text_color'     => range(1, 61),
		];

		/**********************  Validaciones   **********************/
		/*************************************/
		// Verifico
		$Return['Title_color']    = $this->_option($Options, 'Title_color', 17);    // text-muted
		$Return['SubTitle_color'] = $this->_option($Options, 'SubTitle_color', 17); // text-muted
		$Return['Text_color']     = $this->_option($Options, 'Text_color', 17);     // text-muted

		/*************************************/
		// Opciones a validar
		$optionsToCheck = [
			['value' => $Return['Title_color'],    'name' => 'Title_color',     'label' => '$Title_color'],
			['value' => $Return['SubTitle_color'], 'name' => 'SubTitle_color',  'label' => '$SubTitle_color'],
			['value' => $Return['Text_color'],     'name' => 'Text_color',      'label' => '$Text_color'],
		];

		/*************************************/
		// Validacion
		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, '', 1);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		$Return['nErrors'] = $errorn;
		$Return['alerts']  = $alerts;

		// Retorno datos
		return $Return;

	}
    /************************************************************************************************************/
	/**
	 * Imprime la cabecera de perfil: imagen de fondo, avatar y datos (rotulo, nombre y descripcion).
	 * * Valida los colores a traves de header_common(); si $IMG viene vacio usa la imagen por defecto.
	 *
	 * @param string $Title Rotulo superior (ej: el rol del usuario).
	 * @param string $SubTitle Titulo principal (ej: el nombre).
	 * @param string $Text Texto descriptivo bajo el titulo.
	 * @param string $IMG Ruta de la imagen de fondo y del avatar; vacio => imagen por defecto.
	 * @param int $Title_color Color 1..61 del catalogo colorsText() (default 17 text-muted).
	 * @param int $SubTitle_color Color 1..61 del catalogo colorsText() (default 17 text-muted).
	 * @param int $Text_color Color 1..61 del catalogo colorsText() (default 17 text-muted).
	 *
	 * @return void Imprime el HTML de la cabecera o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $WidgetsViews->headerProfile('Rol', 'REG-0001', 'Detalle', $IMG, 13, 17, 40);
	 * ```
	 */
	public function headerProfile($Title, $SubTitle = '', $Text = '', $IMG = '', $Title_color = '', $SubTitle_color = '', $Text_color = ''): void {

		// Valores
		$Options['Title_color']    = $Title_color;
		$Options['SubTitle_color'] = $SubTitle_color;
		$Options['Text_color']     = $Text_color;

		// Obtengo los datos
		$dataReturn = $this->header_common($Options);
		$errorn = $dataReturn['nErrors'];
		$alerts = $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
        // Ejecucion si no hay errores
        if($errorn==0){
			// Imagen (vacia => se usa la imagen por defecto del sistema)
			$IMG_profile = !empty($IMG) ? $IMG : self::DEFAULT_IMG_HEADER;
			// Se imprime
			echo '
			<div class="profile-header">
				<!-- Imagen decorativa del encabezado -->
				<div class="profile-header-background">
					<img src="'.$IMG_profile.'" alt="Back header" class="profile-header-background-image">
				</div>
				<!-- Capa de protección para mejorar la legibilidad -->
				<div class="profile-header-overlay"></div>
				<div class="profile-header-content">
					<!-- Avatar -->
					<div class="profile-avatar">
						<div class="profile-avatar-inner">
							<img src="'.$IMG_profile.'" alt="Perfil de usuario">
						</div>
					</div>
					<!-- Información -->
					<div class="profile-heading">
						<h2 class="eyebrow '.$this->colorsText($dataReturn['Title_color']).'">'.$Title.'</h2>
						<h1 class="'.$this->colorsText($dataReturn['SubTitle_color']).'">'.$SubTitle.'</h1>
						<p class="'.$this->colorsText($dataReturn['Text_color']).'">'.$Text.'</p>
					</div>
				</div>
			</div>';
		}else{
			echo $alerts;
		}

	}

	/************************************************************************************************************/
	/**
	 * Genera una tabla HTML estilizada con Bootstrap a partir de un arreglo de datos.
	 *
	 * Este método construye dinámicamente un componente visual compuesto por:
	 * - Un contenedor tipo "card"
	 * - Un encabezado con título y botón de exportación a Excel
	 * - Una tabla responsive con encabezados y filas generadas desde los datos
	 *
	 * Características principales:
	 * - Si el arreglo está vacío, retorna un mensaje de advertencia
	 * - Genera un ID único para la tabla (utilizado para exportación)
	 * - Usa las claves del primer elemento como encabezados de la tabla
	 * - Escapa los valores para prevenir inyección HTML
	 *
	 * @param array $data Arreglo de datos donde cada elemento representa una fila asociativa
	 *                    (clave => valor). Todas las filas deben compartir las mismas claves.
	 *
	 * @return string HTML completo del componente Bootstrap con la tabla generada
	 *
	 * @throws \Exception No lanza excepciones explícitas; depende de la estructura del arreglo de entrada
	 */
	public function arrayToBootstrapTable(array $data) {
		// Valida si el arreglo de datos está vacío
		if (empty($data)) {
			return '<div class="alert alert-warning">No hay datos disponibles</div>';
		}

		// Genera un identificador único para la tabla
		$tableId  = 'table_' . uniqid();

		// Genera un nombre de archivo basado en fecha y hora para exportación
		$fileName = 'detalle_' . date('Ymd_His');

		// Obtiene los encabezados a partir de las claves del primer elemento del arreglo
		$headers = array_keys(reset($data));

		// Inicializa el contenedor principal tipo card
		$html  = '<div class="card card-custom mb-3 shadow-sm">';

		// Construye el encabezado del card con título y botón de exportación
		$html .= '<div class="card-header d-flex justify-content-between align-items-center">';
		$html .= '<span>Tabla de datos</span>';
		$html .= '<button type="button" class="btn btn-sm btn-success" onclick="exportTableToExcel(\'' . $tableId . '\', \'' . $fileName . '\')"><i class="ri-file-excel-2-line"></i> Exportar a Excel</button>';
		$html .= '</div>';

		// Inicia el contenedor responsive para la tabla
		$html .= '<div class="table-responsive">';

		// Abre la tabla con el ID generado
		$html .= '<table id="' . $tableId . '" class="table table-borderless mb-0">';

		// Construye la sección THEAD con los encabezados
		$html .= '<thead class="table-light"><tr>';
		foreach ($headers as $header) {
			// Aplica formato al encabezado y escapa el contenido
			$html .= '<th scope="col">' . htmlspecialchars(ucfirst($header)) . '</th>';
		}
		$html .= '</tr></thead>';

		// Construye la sección TBODY con los datos
		$html .= '<tbody>';
		foreach ($data as $row) {
			$html .= '<tr>';

			// Recorre cada encabezado para mantener consistencia en columnas
			foreach ($headers as $header) {
				// Obtiene el valor correspondiente o asigna vacío si no existe
				$value = isset($row[$header]) ? $row[$header] : '';

				// Escapa el valor antes de insertarlo en la celda
				$html .= '<td>' . htmlspecialchars($value) . '</td>';
			}

			$html .= '</tr>';
		}
		$html .= '</tbody>';

		// Cierra la tabla y los contenedores
		$html .= '</table></div></div>';

		// Retorna el HTML generado
		return $html;
	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Internos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
    /************************************************************************************************************/
	/**
	 * Resuelve el valor de una opcion dentro de un arreglo (o su default si no viene informada).
	 * * Se considera "sin dato" cuando la clave no existe, es nula, o es una cadena vacia o de espacios.
	 * * A diferencia de !empty(), conserva el 0 y el '0', datos validos para 'Value' (0..100).
	 *
	 * @param array $Options Arreglo asociativo de opciones (o de fila de datos).
	 * @param string $Key Clave a resolver.
	 * @param mixed $default Valor a retornar cuando la clave viene vacia.
	 *
	 * @return mixed Valor informado o el valor por defecto.
	 *
	 * @example
	 * ```php
	 * $this->_option(['Value' => 0], 'Value', 100); // 0 (no se pierde el cero)
	 * $this->_option([], 'Cols', 1);                // 1
	 * ```
	 */
	private function _option($Options, $Key, $default): mixed {

		/**********************  Definiciones   **********************/
		// Sin arreglo o sin la clave => default
		if (!is_array($Options) || !array_key_exists($Key, $Options)) { return $default; }

		// Valor obtenido
		$Value = $Options[$Key];

		/**********************  Verificaciones   **********************/
		// Nulo o cadena vacia/de espacios => default
		if ($Value === null) { return $default; }
		if (is_string($Value) && trim($Value) === '') { return $default; }

		/**********************  Retorno datos  **********************/
		return $Value;

	}
    /************************************************************************************************************/
	/**
	 * Compone la clase de columna de Bootstrap para los seis breakpoints usados en el sistema.
	 *
	 * @param int $Cols Ancho de la columna en la grilla de 12.
	 *
	 * @return string Cadena de clases: 'col-xs-12 col-sm-12 col-md-N col-lg-N col-xl-N col-xxl-N'.
	 *
	 * @example
	 * ```php
	 * $this->_colClass(6); // col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6
	 * ```
	 */
	private function _colClass($Cols): string {

		/**********************  Retorno datos  **********************/
		return 'col-xs-12 col-sm-12 col-md-'.$Cols.' col-lg-'.$Cols.' col-xl-'.$Cols.' col-xxl-'.$Cols;

	}
    /************************************************************************************************************/
	/**
	 * Compone la clase del icono de una fila, aplicando el icono por defecto cuando no viene informado.
	 *
	 * @param array $Row Fila normalizada por table_common().
	 * @param bool $withColor True para anexar el color de texto del icono (table_v1 y table_v2).
	 *
	 * @return string Clases del icono listas para el atributo class.
	 *
	 * @example
	 * ```php
	 * $this->_icon(['Icon' => 'bi bi-bank', 'Icon_color' => 12]); // 'bi bi-bank text-base-color12'
	 * ```
	 */
	private function _icon($Row, $withColor = true): string {

		/**********************  Definiciones   **********************/
		//Icono informado o el por defecto de las listas
		$Icon = ($Row['Icon'] != '') ? $Row['Icon'] : self::DEFAULT_ICON;

		/**********************  Retorno datos  **********************/
		return ($withColor) ? $Icon.' '.$this->colorsText($Row['Icon_color']) : $Icon;

	}
    /************************************************************************************************************/
	/**
	 * Compone el bloque "valor" de una fila, con el boton de copiado cuando el dato trae 'Text_copy'.
	 * * Unifica el bloque que estaba duplicado en table_v1 (dos veces), table_v2, table_v3, table_v4
	 * y table_v6; $Indent mantiene la tabulacion del HTML identica a la de cada vista.
	 *
	 * @param array $Row Fila normalizada por table_common().
	 * @param int $Indent Nivel de tabulacion del bloque con boton (los hijos se derivan de este).
	 * @param string $tag Etiqueta del valor ('span' o 'div').
	 * @param string $class Clase extra del valor cuando se imprime con el boton.
	 * @param string $wrapper Clase del contenedor del valor y del boton.
	 * @param string $classPlain Clase del valor cuando NO se imprime el boton (default: $class).
	 *
	 * @return string HTML del valor, con o sin el boton de copiado.
	 *
	 * @example
	 * ```php
	 * echo $this->_valueField($row, 9);                                 // <span ...>valor</span>
	 * echo $this->_valueField($row, 8, 'span', 'field-value', 'summary-field', 'summary-value');
	 * ```
	 */
	private function _valueField($Row, $Indent, $tag = 'span', $class = '', $wrapper = 'grouped-field', $classPlain = null): string {

		/**********************  Definiciones   **********************/
		// Tabulaciones del bloque (contenedor, hijos y nietos)
		$t0 = str_repeat("\t", $Indent);
		$t1 = str_repeat("\t", $Indent + 1);
		$t2 = str_repeat("\t", $Indent + 2);
		// Clase del valor cuando no hay boton y color de texto del dato
		$classPlain = ($classPlain === null) ? $class : $classPlain;
		$colorText  = $this->colorsText($Row['Text_color']);

		/**********************  Verificaciones   **********************/
		// Clase del valor en cada caso (sin doble espacio cuando no hay clase base)
		$classAttr  = ($class != '') ? $class.' '.$colorText : $colorText;
		$classNoBtn = ($classPlain != '') ? $classPlain.' '.$colorText : $colorText;

		/**********************  Retorno datos  **********************/
		// Sin boton de copiado: solo el valor
		if ($Row['Text_copy'] == '') {
			return '<'.$tag.' class="'.$classNoBtn.'">'.$Row['Text'].'</'.$tag.'>';
		}

		// Con boton de copiado: contenedor + valor + boton
		$input  = '
'.$t0.'<span class="'.$wrapper.'">';
		$input .= '
'.$t1.'<'.$tag.' class="'.$classAttr.'">'.$Row['Text'].'</'.$tag.'>';
		$input .= '
'.$t1.'<button class="icon-button copy-field" type="button" onclick="copiarTexto(\''.$Row['Text_copy'].'\')" aria-label="Copiar '.$Row['Text'].'" title="Copiar '.$Row['Text'].'">';
		$input .= '
'.$t2.'<i class="bi bi-clipboard"></i>';
		$input .= '
'.$t1.'</button>';
		$input .= '
'.$t0.'</span>';

		return $input;

	}
    /************************************************************************************************************/
	/**
	 * Imprime el retrato cuadrado del usuario (avatar), con la imagen por defecto cuando no hay dato.
	 *
	 * @param string $BASE Ruta base del sistema.
	 * @param string $MainPathUrl Ruta del directorio de imagenes del usuario.
	 * @param string $Image Nombre del archivo de imagen.
	 * @param string $ExtraClass Clase adicional del retrato (ej: 'square-border-3').
	 *
	 * @return void Imprime la etiqueta <img>.
	 *
	 * @example
	 * ```php
	 * $this->_imgPortrait('/base', '/uploads/', 'foto.jpg', 'square-border-3');
	 * ```
	 */
	private function _imgPortrait($BASE, $MainPathUrl, $Image, $ExtraClass = ''): void {

		/**********************  Definiciones   **********************/
		// Verifico Imagen (vacia => imagen por defecto del sistema)
		$UserIMG = !empty($Image) ? $MainPathUrl.$Image : $BASE.self::DEFAULT_IMG_PORTRAIT;
		// Clases del retrato (sin doble espacio cuando no hay clase extra)
		$ImgClass = trim('square-rounded-2 '.$ExtraClass).' w-100 mb-2';

		/**********************  Retorno datos  **********************/
		// IMprimo
		echo '<img src="'.$UserIMG.'" alt="Profile" class="'.$ImgClass.'">';

	}
    /************************************************************************************************************/
	/**
	 * Mapa de colores de texto segun identificador (catalogo usado por casi todo el archivo).
	 * * 1..16 colores base del sistema, 17..22 utilitarios Bootstrap/MDB, 23..41 paleta solida
	 * (con variantes -light y -dark) y 42..61 paleta MDB (-text).
	 *
	 * @param int $idColor Identificador 1..61 (los invocadores lo validan antes de pintar).
	 *
	 * @return string Clase CSS del color de texto ('' si el identificador no existe en el mapa).
	 *
	 * @example
	 * ```php
	 * $this->colorsText(17); // 'text-muted'
	 * $this->colorsText(23); // 'text-color-red'
	 * ```
	 */
	private function colorsText($idColor): string {

		$arrColorClass = [
			1  => 'text-base-color01',
			2  => 'text-base-color02',
			3  => 'text-base-color03',
			4  => 'text-base-color04',
			5  => 'text-base-color05',
			6  => 'text-base-color06',
			7  => 'text-base-color07',
			8  => 'text-base-color08',
			9  => 'text-base-color09',
			10 => 'text-base-color10',
			11 => 'text-base-color11',
			12 => 'text-base-color12',
			13 => 'text-base-color13',
			14 => 'text-base-color14',
			15 => 'text-base-color15',
			16 => 'text-base-color16',

			17 => 'text-muted',
			18 => 'text-primary',
			19 => 'text-warning',
			20 => 'text-danger',
			21 => 'text-success',
			22 => 'text-info',

			23 => 'text-color-red',
			24 => 'text-color-red-light',
			25 => 'text-color-red-dark',

			26 => 'text-color-blue',
			27 => 'text-color-blue-light',
			28 => 'text-color-blue-dark',

			29 => 'text-color-green',
			30 => 'text-color-green-light',
			31 => 'text-color-green-dark',

			32 => 'text-color-yellow',
			33 => 'text-color-yellow-light',
			34 => 'text-color-yellow-dark',

			35 => 'text-color-dark',
			36 => 'text-color-dark-light',
			37 => 'text-color-dark-dark',

			38 => 'text-color-gray',
			39 => 'text-color-gray-light',
			40 => 'text-color-gray-dark',

			41 => 'text-color-white',

			42 => 'text-color-mdb-text',
			43 => 'text-color-red-text',
			44 => 'text-color-pink-text',
			45 => 'text-color-purple-text',
			46 => 'text-color-deep-purple-text',
			47 => 'text-color-indigo-text',
			48 => 'text-color-blue-text',
			49 => 'text-color-light-blue-text',
			50 => 'text-color-cyan-text',
			51 => 'text-color-teal-text',
			52 => 'text-color-green-text',
			53 => 'text-color-light-green-text',
			54 => 'text-color-lime-text',
			55 => 'text-color-yellow-text',
			56 => 'text-color-amber-text',
			57 => 'text-color-orange-text',
			58 => 'text-color-deep-orange-text',
			59 => 'text-color-brown-text',
			60 => 'text-color-blue-grey-text',
			61 => 'text-color-grey-text',
		];

		/**********************  Retorno datos  **********************/
		// Con identificador desconocido no se pinta color (evita el aviso por indice inexistente)
		return $arrColorClass[$idColor] ?? '';

	}
    /************************************************************************************************************/
	/**
	 * Mapa de botones con contorno (btn-outline-bkg-*) segun el color del icono.
	 * * Se usa en table_v3 para el boton redondo que contiene el icono.
	 *
	 * @param int $idColor Identificador 1..61 (los invocadores lo validan antes de pintar).
	 *
	 * @return string Clase CSS del boton ('' si el identificador no existe en el mapa).
	 *
	 * @example
	 * ```php
	 * $this->colorsBackgroundBTN(12); // 'btn-outline-bkg-deep-orange-darken'
	 * ```
	 */
	private function colorsBackgroundBTN($idColor): string {

		$arrColorClass = [
			1  => 'btn-outline-bkg-grey-darken',
			2  => 'btn-outline-bkg-grey-darken',
			3  => 'btn-outline-bkg-grey-darken',
			4  => 'btn-outline-bkg-grey-darken',
			5  => 'btn-outline-bkg-blue-grey-lighten',
			6  => 'btn-outline-bkg-blue-grey-lighten',
			7  => 'btn-outline-bkg-blue-grey-lighten',
			8  => 'btn-outline-bkg-blue-grey-lighten',
			9  => 'btn-outline-bkg-blue-grey-darken',
			10 => 'btn-outline-bkg-blue-grey-darken',
			11 => 'btn-outline-bkg-blue-grey-darken',
			12 => 'btn-outline-bkg-deep-orange-darken',
			13 => 'btn-outline-bkg-deep-orange-darken',
			14 => 'btn-outline-bkg-orange-lighten',
			15 => 'btn-outline-bkg-light-green-lighten',
			16 => 'btn-outline-bkg-deep-purple-lighten',

			17 => 'btn-outline-bkg-grey-darken',
			18 => 'btn-outline-bkg-blue-grey-lighten',
			19 => 'btn-outline-bkg-orange-lighten',
			20 => 'btn-outline-bkg-deep-orange-darken',
			21 => 'btn-outline-bkg-light-green-darken',
			22 => 'btn-outline-bkg-light-blue-accent',

			23 => 'btn-outline-bkg-red-lighten',
			24 => 'btn-outline-bkg-red-lighten',
			25 => 'btn-outline-bkg-red-lighten',

			26 => 'btn-outline-bkg-blue-accent',
			27 => 'btn-outline-bkg-blue-accent',
			28 => 'btn-outline-bkg-blue-accent',

			29 => 'btn-outline-bkg-green-lighten',
			30 => 'btn-outline-bkg-green-lighten',
			31 => 'btn-outline-bkg-green-lighten',

			32 => 'btn-outline-bkg-amber-lighten',
			33 => 'btn-outline-bkg-amber-lighten',
			34 => 'btn-outline-bkg-amber-lighten',

			35 => 'btn-outline-bkg-blue-grey-darken',
			36 => 'btn-outline-bkg-blue-grey-darken',
			37 => 'btn-outline-bkg-blue-grey-darken',

			38 => 'btn-outline-bkg-blue-grey-darken',
			39 => 'btn-outline-bkg-blue-grey-darken',
			40 => 'btn-outline-bkg-blue-grey-darken',

			41 => 'btn-outline-bkg-grey-lighten',

			42 => 'btn-outline-bkg-grey-darken',
			43 => 'btn-outline-bkg-red-darken',
			44 => 'btn-outline-bkg-pink-lighten',
			45 => 'btn-outline-bkg-purple-darken',
			46 => 'btn-outline-bkg-deep-purple-darken',
			47 => 'btn-outline-bkg-indigo-lighten',
			48 => 'btn-outline-bkg-blue-darken',
			49 => 'btn-outline-bkg-blue-accent',
			50 => 'btn-outline-bkg-cyan-lighten',
			51 => 'btn-outline-bkg-teal-darken',
			52 => 'btn-outline-bkg-green-lighten',
			53 => 'btn-outline-bkg-green-accent',
			54 => 'btn-outline-bkg-lime-lighten',
			55 => 'btn-outline-bkg-yellow-accent',
			56 => 'btn-outline-bkg-amber-lighten',
			57 => 'btn-outline-bkg-orange-accent',
			58 => 'btn-outline-bkg-deep-orange-lighten',
			59 => 'btn-outline-bkg-brown-darken',
			60 => 'btn-outline-bkg-blue-grey-lighten',
			61 => 'btn-outline-bkg-grey-lighten',
		];

		/**********************  Retorno datos  **********************/
		// Con identificador desconocido no se pinta fondo (evita el aviso por indice inexistente)
		return $arrColorClass[$idColor] ?? '';

	}
    /************************************************************************************************************/
	/**
	 * Mapa de fondos (bg-*, bkg-* y rgba-*) segun identificador, usado en tittle_v4 para el icono.
	 * * 1..7 colores standart, 8..20 pastel, 21..24 pastel oscuro, 25..43 bkg-*-lighten-1 y
	 * 44..103 fondos rgba (slight / light / strong).
	 *
	 * @param int $idColor Identificador 1..103 (los invocadores lo validan antes de pintar).
	 *
	 * @return string Clase CSS del fondo ('' si el identificador no existe en el mapa).
	 *
	 * @example
	 * ```php
	 * $this->colorsBG(43); // 'bkg-grey-lighten-1'
	 * ```
	 */
	private function colorsBG($idColor): string {

		$arrColorClass = [
			1   => 'bg-standart-purple',
			2   => 'bg-standart-green',
			3   => 'bg-standart-yellow',
			4   => 'bg-standart-aqua',
			5   => 'bg-standart-black',
			6   => 'bg-standart-red',
			7   => 'bg-standart-blue',
			8   => 'bg-pastel-magenta',
			9   => 'bg-pastel-violet',
			10  => 'bg-pastel-pink',
			11  => 'bg-pastel-pink-2',
			12  => 'bg-pastel-blue',
			13  => 'bg-pastel-green',
			14  => 'bg-pastel-gray',
			15  => 'bg-pastel-purple',
			16  => 'bg-pastel-purple-light',
			17  => 'bg-pastel-orange',
			18  => 'bg-pastel-red',
			19  => 'bg-pastel-yellow',
			20  => 'bg-pastel-brown',
			21  => 'bg-dark-pastel-blue',
			22  => 'bg-dark-pastel-purple',
			23  => 'bg-dark-pastel-green',
			24  => 'bg-dark-pastel-red',
			25  => 'bkg-red-lighten-1',
			26  => 'bkg-pink-lighten-1',
			27  => 'bkg-purple-lighten-1',
			28  => 'bkg-deep-purple-lighten-1',
			29  => 'bkg-indigo-lighten-1',
			30  => 'bkg-blue-lighten-1',
			31  => 'bkg-light-blue-lighten-1',
			32  => 'bkg-cyan-lighten-1',
			33  => 'bkg-teal-lighten-1',
			34  => 'bkg-green-lighten-1',
			35  => 'bkg-light-green-lighten-1',
			36  => 'bkg-lime-lighten-1',
			37  => 'bkg-yellow-lighten-1',
			38  => 'bkg-amber-lighten-1',
			39  => 'bkg-orange-lighten-1',
			40  => 'bkg-deep-orange-lighten-1',
			41  => 'bkg-brown-lighten-1',
			42  => 'bkg-blue-grey-lighten-1',
			43  => 'bkg-grey-lighten-1',
			44  => 'rgba-mdb-color-slight',
			45  => 'rgba-mdb-color-light',
			46  => 'rgba-mdb-color-strong',
			47  => 'rgba-red-slight',
			48  => 'rgba-red-light',
			49  => 'rgba-red-strong',
			50  => 'rgba-pink-slight',
			51  => 'rgba-pink-light',
			52  => 'rgba-pink-strong',
			53  => 'rgba-purple-slight',
			54  => 'rgba-purple-light',
			55  => 'rgba-purple-strong',
			56  => 'rgba-deep-purple-slight',
			57  => 'rgba-deep-purple-light',
			58  => 'rgba-deep-purple-strong',
			59  => 'rgba-indigo-slight',
			60  => 'rgba-indigo-light',
			61  => 'rgba-indigo-strong',
			62  => 'rgba-blue-slight',
			63  => 'rgba-blue-light',
			64  => 'rgba-blue-strong',
			65  => 'rgba-light-blue-slight',
			66  => 'rgba-light-blue-light',
			67  => 'rgba-light-blue-strong',
			68  => 'rgba-cyan-slight',
			69  => 'rgba-cyan-light',
			70  => 'rgba-cyan-strong',
			71  => 'rgba-teal-slight',
			72  => 'rgba-teal-light',
			73  => 'rgba-teal-strong',
			74  => 'rgba-green-slight',
			75  => 'rgba-green-light',
			76  => 'rgba-green-strong',
			77  => 'rgba-light-green-slight',
			78  => 'rgba-light-green-light',
			79  => 'rgba-light-green-strong',
			80  => 'rgba-lime-slight',
			81  => 'rgba-lime-light',
			82  => 'rgba-lime-strong',
			83  => 'rgba-yellow-slight',
			84  => 'rgba-yellow-light',
			85  => 'rgba-yellow-strong',
			86  => 'rgba-amber-slight',
			87  => 'rgba-amber-light',
			88  => 'rgba-amber-strong',
			89  => 'rgba-orange-slight',
			90  => 'rgba-orange-light',
			91  => 'rgba-orange-strong',
			92  => 'rgba-deep-orange-slight',
			93  => 'rgba-deep-orange-light',
			94  => 'rgba-deep-orange-strong',
			95  => 'rgba-brown-slight',
			96  => 'rgba-brown-light',
			97  => 'rgba-brown-strong',
			98  => 'rgba-blue-grey-slight',
			99  => 'rgba-blue-grey-light',
			100 => 'rgba-blue-grey-strong',
			101 => 'rgba-grey-slight',
			102 => 'rgba-grey-light',
			103 => 'rgba-grey-strong',

		];

		/**********************  Retorno datos  **********************/
		// Con identificador desconocido no se pinta fondo (evita el aviso por indice inexistente)
		return $arrColorClass[$idColor] ?? '';

	}
    /************************************************************************************************************/
	/**
	 * Mapa de colores de titulo (1..39) que reutiliza los MISMOS nombres de colorsText() 23..61.
	 * * Es decir: colorsTitle(1) === colorsText(23) (text-color-red) y colorsTitle(39) ===
	 * colorsText(61) (text-color-grey-text). Se mantiene el mapa explicito por legibilidad.
	 *
	 * @param int $idColor Identificador 1..39 (tittle_v1 lo valida antes de pintar).
	 *
	 * @return string Clase CSS del color de titulo ('' si el identificador no existe en el mapa).
	 *
	 * @example
	 * ```php
	 * $this->colorsTitle(20); // 'text-color-mdb-text' (equivale a colorsText(42))
	 * ```
	 */
	private function colorsTitle($idColor): string {

		$arrColorClass = [
			1 => 'text-color-red',
			2 => 'text-color-red-light',
			3 => 'text-color-red-dark',

			4 => 'text-color-blue',
			5 => 'text-color-blue-light',
			6 => 'text-color-blue-dark',

			7 => 'text-color-green',
			8 => 'text-color-green-light',
			9 => 'text-color-green-dark',

			10 => 'text-color-yellow',
			11 => 'text-color-yellow-light',
			12 => 'text-color-yellow-dark',

			13 => 'text-color-dark',
			14 => 'text-color-dark-light',
			15 => 'text-color-dark-dark',

			16 => 'text-color-gray',
			17 => 'text-color-gray-light',
			18 => 'text-color-gray-dark',

			19 => 'text-color-white',

			20 => 'text-color-mdb-text',
			21 => 'text-color-red-text',
			22 => 'text-color-pink-text',
			23 => 'text-color-purple-text',
			24 => 'text-color-deep-purple-text',
			25 => 'text-color-indigo-text',
			26 => 'text-color-blue-text',
			27 => 'text-color-light-blue-text',
			28 => 'text-color-cyan-text',
			29 => 'text-color-teal-text',
			30 => 'text-color-green-text',
			31 => 'text-color-light-green-text',
			32 => 'text-color-lime-text',
			33 => 'text-color-yellow-text',
			34 => 'text-color-amber-text',
			35 => 'text-color-orange-text',
			36 => 'text-color-deep-orange-text',
			37 => 'text-color-brown-text',
			38 => 'text-color-blue-grey-text',
			39 => 'text-color-grey-text',
		];

		/**********************  Retorno datos  **********************/
		// Con identificador desconocido no se pinta color (evita el aviso por indice inexistente)
		return $arrColorClass[$idColor] ?? '';

	}
}
