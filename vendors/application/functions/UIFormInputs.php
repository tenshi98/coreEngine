<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class UIFormInputs {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                 Instancias                                                      */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	//Definiciones
	private $DataValidations;
	private $DataText;
	private $CommonData;
	private $Alertas;
	private $TemplateRender;

	/******************************************************************************/
	/**
	 * Crea las instancias que la clase necesita (validaciones, texto, datos comunes, alertas
	 * y renderizado de plantillas) al construir el objeto.
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * ```
	 */
	public function __construct() {
		$this->DataValidations = new FunctionsDataValidations();
		$this->DataText        = new FunctionsDataText();
		$this->CommonData      = new FunctionsCommonData();
		$this->Alertas         = new UIWidgetsCommon();
        $this->TemplateRender  = new TemplateRenderer();
	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Privados                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	/**
	 * Genera el HTML de un input checkbox o radio a partir de la plantilla formCheckInputGen_1.
	 *
	 * @param string $placeholder   Texto a mostrar al lado del input.
	 * @param string $name          Nombre (name) del input.
	 * @param string $ID            Identificador (id) del input.
	 * @param string $tipo          Clase Bootstrap del estado (default, primary, success, danger, warning, info).
	 * @param string $type          Tipo de input HTML (checkbox o radio).
	 * @param string $valor         Valor del input (ej: 2).
	 * @param string $check         Atributo checked (checked o vacio).
	 * @param string $requerido     Atributo requerido (required="required", disabled o vacio).
	 * @param string $requerido_div Clase required en el div contenedor (required o vacio).
	 *
	 * @return string HTML del input generado.
	 *
	 * @example
	 * ```php
	 * $html = $this->_checkInputGen('Acepto terminos', 'acepto', 'acepto', 'primary', 'checkbox', '2', 'checked', 'required="required"', 'required');
	 * ```
	 */
	private function _checkInputGen($placeholder,$name, $ID, $tipo, $type, $valor, $check, $requerido, $requerido_div){

		/************************************/
		//Se agregan datos
		$this->TemplateRender->templatePath('../app/templates/Forms/formCheckInputGen_1.php');
		$this->TemplateRender->assign('tipo',          $tipo);
		$this->TemplateRender->assign('requerido_div', $requerido_div);
		$this->TemplateRender->assign('requerido',     $requerido);
		$this->TemplateRender->assign('check',         $check);
		$this->TemplateRender->assign('name',          $name);
		$this->TemplateRender->assign('type',          $type);
		$this->TemplateRender->assign('valor',         $valor);
		$this->TemplateRender->assign('ID',            $ID);
		$this->TemplateRender->assign('placeholder',   $placeholder);

		/************************************/
		//devuelvo
		return $this->TemplateRender->render();

	}

	/******************************************************************************/
	/**
	 * Genera el HTML de un select con opciones planas segun la alineacion del formulario.
	 * Incluye siempre la opcion vacia "Seleccione una Opcion" y marca como selected el valor
	 * que coincida con $value.
	 *
	 * @param int    $FormAling        Alineacion del formulario (1 al 5).
	 * @param int    $FormCol          Columnas del formulario (0 al 12).
	 * @param string $placeholder      Texto del placeholder / etiqueta del campo.
	 * @param string $PlaceholderIcon  Icono a mostrar al lado del placeholder (vacio si no aplica).
	 * @param string $name             Nombre (name) del select.
	 * @param string $nameID           Identificador (id) del select.
	 * @param string $value            Valor precargado (se marca selected si coincide con el ID).
	 * @param string $selectProperties Propiedades HTML extra del select (ej: required="required").
	 * @param array  $arrData          Filas con claves ID y Nombre para generar cada option.
	 * @param string $classMain        Clase CSS extra del contenedor (vacio si no aplica).
	 * @param string $dataInfo         Informacion a mostrar debajo del input (vacio si no aplica).
	 * @param string $dataPopover      Icono popover con info (vacio si no aplica).
	 *
	 * @return string HTML del select generado.
	 *
	 * @example
	 * ```php
	 * $arrData = [['ID' => 1, 'Nombre' => 'Metropolitana'], ['ID' => 2, 'Nombre' => 'Valparaiso']];
	 * $html = $this->_selectInputGen(2, 8, 'Region', '', 'region', 'region', '', '', $arrData, '', '', '');
	 * ```
	 */
	private function _selectInputGen($FormAling, $FormCol, $placeholder, $PlaceholderIcon, $name, $nameID, $value, $selectProperties, $arrData, $classMain, $dataInfo, $dataPopover){

		/************************************/
		// Variables vacias
		$Options = '';

		/************************************/
		//Verifico si el placeholder usa icono
		$placeholderIcon = (!empty($PlaceholderIcon)&&$PlaceholderIcon!='') ? "<i class='".$this->_scapeValue($PlaceholderIcon)."'></i> " : '';

		/************************************/
		//generacion del input
		$formRoute = $this->_resolveFormRoute('formSelectInputGen', $FormAling, $FormCol);

		/*******************************************/
		// Inicializa la variable que marcará la opción vacía como seleccionada por defecto
		$selectedx = 'selected="selected"';
		// Recorre el arreglo de datos para construir cada opción del <select>
		foreach ($arrData as $select) {
			// Verifica si el valor actual coincide con el ID del elemento
			$isSelected = ($value == $select['ID']) ? 'selected="selected"' : '';
			// Si alguna opción está seleccionada, se desactiva la selección por defecto
			if ($isSelected){$selectedx = '';}
			// Construye la etiqueta <option> con el valor y el texto formateado
			$Options .= '<option value="' . $select['ID'] . '" ' . $isSelected . '>' . $this->DataText->tituloMenu($select['Nombre']) . '</option>';
		}
		// Construye la opción inicial "Seleccione una Opción", que estará seleccionada si ninguna otra lo está
		$SelectOptions = '<option value="" ' . $selectedx . '>Seleccione una Opción</option>';
		// Agrega todas las opciones generadas al conjunto final
		$SelectOptions .= $Options;

		/************************************/
		//Se agregan datos
		$this->TemplateRender->templatePath($formRoute);
		$this->TemplateRender->assign('nameID',           $nameID);
		$this->TemplateRender->assign('placeholder',      $placeholder);
		$this->TemplateRender->assign('placeholderIcon',  $placeholderIcon);
		$this->TemplateRender->assign('FormCol',          $FormCol);
		$this->TemplateRender->assign('classMain',        $classMain);
		$this->TemplateRender->assign('name',             $name);
		$this->TemplateRender->assign('selectProperties', $selectProperties);
		$this->TemplateRender->assign('SelectOptions',    $SelectOptions);
		$this->TemplateRender->assign('dataInfo',         $dataInfo);
		$this->TemplateRender->assign('dataPopover',      $dataPopover);

		/************************************/
		//devuelvo
		return $this->TemplateRender->render();

	}

	/******************************************************************************/
	/**
	 * Genera el HTML de un select con opciones agrupadas en optgroup segun la alineacion del
	 * formulario. Agrupa $arrData por la clave ID2 y usa Nombre2 como etiqueta de grupo.
	 *
	 * @param int    $FormAling        Alineacion del formulario (1 al 5).
	 * @param int    $FormCol          Columnas del formulario (0 al 12).
	 * @param string $placeholder      Texto del placeholder / etiqueta del campo.
	 * @param string $PlaceholderIcon  Icono a mostrar al lado del placeholder (vacio si no aplica).
	 * @param string $name             Nombre (name) del select.
	 * @param string $nameID           Identificador (id) del select.
	 * @param string $value            Valor precargado (se marca selected si coincide con ID1).
	 * @param string $selectProperties Propiedades HTML extra del select (ej: required="required").
	 * @param array  $arrData          Filas con claves ID1, ID2, Nombre1 y Nombre2 para generar
	 *                                  los optgroup y sus opciones.
	 * @param string $classMain        Clase CSS extra del contenedor (vacio si no aplica).
	 * @param string $dataInfo         Informacion a mostrar debajo del input (vacio si no aplica).
	 * @param string $dataPopover      Icono popover con info (vacio si no aplica).
	 *
	 * @return string HTML del select agrupado generado.
	 *
	 * @example
	 * ```php
	 * $arrData = [['ID1' => 1, 'ID2' => 10, 'Nombre1' => 'Cobre', 'Nombre2' => 'Materiales']];
	 * $html = $this->_selectInputGroupGen(2, 8, 'Material', '', 'material', 'material', '', '', $arrData, '', '', '');
	 * ```
	 */
	private function _selectInputGroupGen($FormAling, $FormCol, $placeholder, $PlaceholderIcon, $name, $nameID, $value, $selectProperties, $arrData, $classMain, $dataInfo, $dataPopover){

		/************************************/
		// Variables vacias
		$Options = '';

		/************************************/
		//Verifico si el placeholder usa icono
		$placeholderIcon = (!empty($PlaceholderIcon)&&$PlaceholderIcon!='') ? "<i class='".$this->_scapeValue($PlaceholderIcon)."'></i> " : '';

		/********************************************************/
		//Opero los datos
		$newArray  = $this->CommonData->agruparPorClave ($arrData,'ID2'); //transforma a array multinivel

		/************************************/
		//generacion del input
		$formRoute = $this->_resolveFormRoute('formSelectInputGen', $FormAling, $FormCol);

		/*******************************************/
		// Variable que indica si la opción vacía ("Seleccione una Opción") debe estar seleccionada por defecto
		$selectedx = 'selected="selected"';
		// Recorre el arreglo $newArray, donde cada clave representa una categoría
		foreach ($newArray as $categoria => $selected) {
			// Crea un grupo de opciones <optgroup> con la etiqueta de la categoría
			// Se asume que el nombre de la categoría está en el primer elemento del grupo, en la clave 'Nombre2'
			$Options .= '<optgroup label="' . $selected[0]['Nombre2'] . '">';
			// Recorre cada elemento dentro de la categoría para generar sus opciones
			foreach ($selected as $select) {
				// Verifica si el valor actual coincide con el ID del elemento para marcarlo como seleccionado
				$isSelected = ($value == $select['ID1']) ? 'selected="selected"' : '';
				// Si alguna opción está seleccionada, se desactiva la selección por defecto
				if ($isSelected){$selectedx = '';}
				// Construye la etiqueta <option> con el valor y el texto formateado
				// Usa el método personalizado tituloMenu() para mostrar el nombre de la opción
				$Options .= '<option value="' . $select['ID1'] . '" ' . $isSelected . '>' . $this->DataText->tituloMenu($select['Nombre1']) . '</option>';
			}
			// Cierra el grupo de opciones
			$Options .= '</optgroup>';
		}
		// Crea la opción inicial vacía ("Seleccione una Opción"), que estará seleccionada si ninguna otra lo está
		$SelectOptions = '<option value="" ' . $selectedx . '>Seleccione una Opción</option>';
		// Agrega todas las opciones generadas al conjunto final
		$SelectOptions .= $Options;

		/************************************/
		//Se agregan datos
		$this->TemplateRender->templatePath($formRoute);
		$this->TemplateRender->assign('nameID',           $nameID);
		$this->TemplateRender->assign('placeholder',      $placeholder);
		$this->TemplateRender->assign('placeholderIcon',  $placeholderIcon);
		$this->TemplateRender->assign('FormCol',          $FormCol);
		$this->TemplateRender->assign('classMain',        $classMain);
		$this->TemplateRender->assign('name',             $name);
		$this->TemplateRender->assign('selectProperties', $selectProperties);
		$this->TemplateRender->assign('SelectOptions',    $SelectOptions);
		$this->TemplateRender->assign('dataInfo',         $dataInfo);
		$this->TemplateRender->assign('dataPopover',      $dataPopover);

		/************************************/
		//devuelvo
		return $this->TemplateRender->render();

	}

	/******************************************************************************/
	/**
	 * Genera el HTML de un select vacio (solo la opcion "Seleccione una Opcion") para el segundo
	 * campo de un select dependiente; las opciones se cargan luego via _selectInputScript.
	 *
	 * @param int    $FormAling        Alineacion del formulario (1 al 5).
	 * @param int    $FormCol          Columnas del formulario (0 al 12).
	 * @param string $placeholder      Texto del placeholder / etiqueta del campo.
	 * @param string $PlaceholderIcon  Icono a mostrar al lado del placeholder (vacio si no aplica).
	 * @param string $name             Nombre (name) del select.
	 * @param string $nameID           Identificador (id) del select.
	 * @param string $selectProperties Propiedades HTML extra del select (ej: required="required").
	 * @param string $dataPopover      Icono popover con info (vacio si no aplica).
	 *
	 * @return string HTML del select vacio generado.
	 *
	 * @example
	 * ```php
	 * $html = $this->_selectInputEmpty(2, 8, 'Comuna', '', 'comuna', 'comuna', '', '');
	 * ```
	 */
	private function _selectInputEmpty($FormAling, $FormCol, $placeholder, $PlaceholderIcon, $name, $nameID, $selectProperties, $dataPopover){

		/************************************/
		//Verifico si el placeholder usa icono
		$placeholderIcon = (!empty($PlaceholderIcon)&&$PlaceholderIcon!='') ? "<i class='".$this->_scapeValue($PlaceholderIcon)."'></i> " : '';

		/************************************/
		//generacion del input
		$formRoute = $this->_resolveFormRoute('formSelectInputGen', $FormAling, $FormCol);

		/*******************************************/
		// Variables
		$SelectOptions  = '<option value="">Seleccione una Opción</option>';
		/************************************/
		//Se agregan datos
		$this->TemplateRender->templatePath($formRoute);
		$this->TemplateRender->assign('nameID',           $nameID);
		$this->TemplateRender->assign('placeholder',      $placeholder);
		$this->TemplateRender->assign('placeholderIcon',  $placeholderIcon);
		$this->TemplateRender->assign('FormCol',          $FormCol);
		$this->TemplateRender->assign('classMain',        '');
		$this->TemplateRender->assign('name',             $name);
		$this->TemplateRender->assign('selectProperties', $selectProperties);
		$this->TemplateRender->assign('SelectOptions',    $SelectOptions);
		$this->TemplateRender->assign('dataPopover',      $dataPopover);

		/************************************/
		//devuelvo
		return $this->TemplateRender->render();

	}

	/******************************************************************************/
	/**
	 * Genera el script y la matriz de datos JavaScript que permiten cargar las opciones del
	 * segundo select en funcion del valor elegido en el primero (select dependiente).
	 *
	 * @param array $arrData Filas con claves ID1, ID2 y Nombre de las opciones del segundo select.
	 * @param mixed $value   Valor precargado del segundo select (vacio se corrige a 0).
	 * @param string $name1  Identificador del select padre.
	 * @param string $name2  Identificador del select dependiente.
	 * @param int   $FormAling Alineacion del formulario del segundo select (1 al 5).
	 *
	 * @return string HTML con el script y la matriz de datos del select dependiente.
	 *
	 * @example
	 * ```php
	 * $arrData = [['ID1' => 1, 'ID2' => 10, 'Nombre' => 'Santiago']];
	 * $html = $this->_selectInputScript($arrData, '', 'region', 'comuna', 2);
	 * ```
	 */
	private function _selectInputScript($arrData, $value, $name1, $name2, $FormAling){
		/********************************************************/
		//Opero los datos
		$newArray  = $this->CommonData->agruparPorClave ($arrData, 'ID2');    //transforma a array multinivel

		//para corregir consulta en caso de vacio
		if(!isset($value) || $value == ''){$value = 0;}

		/************************************/
		//generacion del input
		switch ($FormAling) {
			case 1: $XDisplay = 'flex';break;//Horizontal Form
			case 2: $XDisplay = 'block';break;//Vertical Form
			case 3: $XDisplay = 'block';break;//Multi Columns Form
			case 4: $XDisplay = 'block';break;//No Labels Form
			case 5: $XDisplay = 'block';break;//Floating Labels Form
		}

		//caracteres prohibidos
		$vowels = array(" ", "´", "-");
		// Variable
		$RandName = 'Rand_'.rand(1, 999999);

		/*********************** Datos del arreglo ***********************/
		$MatrixData = 'const MatrixData'.$RandName.' = {';
			// Recorre el arreglo $newArray, donde cada clave representa un grupo o categoría
			foreach ($newArray as $Clave => $Datos) {
				// Inicializa las cadenas que representarán los arrays de IDs y nombres
				// Se comienza con un valor vacío y una opción por defecto
				$Int_id_data = 'ids: [""'; // Primer elemento vacío
				$Int_data    = 'data: ["Seleccione una Opción"'; // Primer texto visible
				// Recorre los elementos dentro de cada grupo
				foreach ($Datos as $dato) {
					// Verifica que el dato tenga un ID válido
					if (isset($dato['ID1']) && $dato['ID1'] != '') {
						// Agrega el ID al array de IDs
						$Int_id_data .= ',"' . $dato['ID1'] . '"';
						// Agrega el nombre formateado al array de textos visibles
						// Se eliminan comillas dobles para evitar errores de formato
						$Int_data .= ',"' . str_replace('"', '', $this->DataText->tituloMenu($dato['Nombre'])) . '"';
					}
				}
				// Cierra los arrays de IDs y textos
				$Int_id_data .= ']';
				$Int_data    .= ']';
				// Construye la entrada final para $MatrixData, usando la clave como identificador
				// Se reemplazan vocales definidas en $vowels por guiones bajos para normalizar la clave
				$MatrixData .= '"' . str_replace($vowels, '_', $Clave) . '": {' . $Int_id_data . ', ' . $Int_data . '},';
			}
		$MatrixData .= '};';

		/************************************/
		//Se agregan datos
		$this->TemplateRender->templatePath('../app/templates/Forms/formSelectInputScript_1.php');
		$this->TemplateRender->assign('MatrixData',  $MatrixData);
		$this->TemplateRender->assign('RandName',    $RandName);
		$this->TemplateRender->assign('name1',       $name1);
		$this->TemplateRender->assign('name2',       $name2);
		$this->TemplateRender->assign('XDisplay',    $XDisplay);
		$this->TemplateRender->assign('value',       $value);

		/************************************/
		//devuelvo
		return $this->TemplateRender->render();

	}

	/******************************************************************************/
	/**
	 * Genera las etiquetas option de un datalist a partir de los datos recibidos.
	 *
	 * @param string $nameID Identificador (id) del input asociado al datalist.
	 * @param array  $arrData Filas con la clave Nombre de cada opcion sugerida.
	 *
	 * @return string HTML con las opciones del datalist.
	 *
	 * @example
	 * ```php
	 * $arrData = [['Nombre' => 'Santiago'], ['Nombre' => 'Valparaiso']];
	 * $html = $this->_inputDatalist('ciudad', $arrData);
	 * ```
	 */
	private function _inputDatalist($nameID, $arrData){

		/*******************************************/
		//Datos
		$dataList = '';
		// Recorre el arreglo $arrData
		foreach ( $arrData as $select ) {
			$dataList .= '<option value="'.$this->DataText->tituloMenu($select['Nombre']).'">';
		}

		/************************************/
		//Se agregan datos
		$this->TemplateRender->templatePath('../app/templates/Forms/formInputData_1.php');
		$this->TemplateRender->assign('dataList', $dataList);
		$this->TemplateRender->assign('nameID',   $nameID);

		/************************************/
		//devuelvo
		return $this->TemplateRender->render();

	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Publicos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	/**
	 * Permite crear un input tipo titulo.
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'Tipo' => Tipo de
	 *                      formulario (1 al 7); 'Texto' => Texto a mostrar.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formTittle([
	 *     'Tipo'  => 1,
	 *     'Texto' => '',
	 * ]);
	 * ```
	 */
	public function formTittle($Options){

		/**********************  Definiciones   **********************/
		$type  = $Options['Tipo'];
		$Text  = $Options['Texto'];
		$Class = $Options['Clase'] ?? '';

		//Definir opciones válidas
		$validOptions = [
			'type'  => range(1, 7),
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
			//Selecciono el tipo de mensaje
			$options = ['h1', 'h2', 'h3', 'h4', 'h5', 'p', 'strong'];
			$tipo    = $options[$type-1];

			/************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath('../app/templates/Forms/formTittle_1.php');
			$this->TemplateRender->assign('tipo',  $tipo);
			$this->TemplateRender->assign('texto',  $Text);
			$this->TemplateRender->assign('clase',  $Class);

			/************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/************************************/
			//Imprimir dato
			echo $alerts;

		}

	}

	/******************************************************************************/
	/**
	 * Permite crear un elemento details que despliega informacion.
	 *
	 * @param array $arrData Filas a desplegar: 'ID' => titulo del elemento (summary); 'Nombre' =>
	 *                      contenido del detalle.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formDetails([
	 *     'ID'     => 'Nombre',
	 *     'Nombre' => 'URL',
	 * ]);
	 * ```
	 */
	public function formDetails($arrData){

		/********************************************************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){
			/************************************/
			// Variables vacias
			$dataDetails = '';
			// Recorro
			foreach ( $arrData as $select ) {$dataDetails .= '<details><summary>'.$this->_scapeValue($select['ID']).'</summary><p>'.$this->_scapeValue($this->DataText->tituloMenu($select['Nombre'])).'</p></details>';}
			/************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath('../app/templates/Forms/formDetails_1.php');
			$this->TemplateRender->assign('dataDetails', $dataDetails);

			/************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/************************************/
			//Imprimir dato
			echo $alerts;

		}

	}

	/******************************************************************************/
	/**
	 * Permite crear un elemento tipo alerta.
	 *
	 * @param string $color Numerico 1 al 8, permite seleccionar entre los colores disponibles.
	 * @param string $type Numerico 1 al 6, permite seleccionar el tipo de mensaje.
	 * @param string $icon icono a utilizar(fontawesome u otro).
	 * @param string $autoClose Numerico 0 o 1, muestra la opcion de mostrar el boton de cerrado.
	 * @param string $Text Texto del mensaje.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formPostData(6, 4, 'exclamation-circle', 0, 'Operacion exitosa');
	 * ```
	 */
	public function formPostData($color, $type, $icon, $autoClose, $Text){

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		echo $this->Alertas->alertPostData($color, $type, $icon, $autoClose, $Text);

	}

	/******************************************************************************/
	/**
	 * Permite crear un input tipo oculto.
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'Name' => Nombre del
	 *                      input; 'Value' => Valor del input; 'Required' => Si input es requerido
	 *                      (1 al 2).
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formInputHidden([
	 *     'Name'     => 'Nombre',
	 *     'Value'    => 'asd',
	 *     'Required' => 1,
	 * ]);
	 * ```
	 */
	public function formInputHidden($Options){

		/**********************  Definiciones   **********************/
		$name     = $Options['Name'];
		$value    = $Options['Value'] ?? '';
		$required = $Options['Required'] ?? 1;

		//Definir opciones válidas
		$validOptions = [
			'required'  => range(1, 2),
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $required,  'name' => 'required',  'label' => '$required'],
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

			/************************************/
			//Valido si es requerido
			$requerido = ($required === 2) ? 'required="required"' : '';

			/************************************/
			//Si existe un valor entregado
			$valor = ($value !== '') ? $value : '';

			/************************************/
			//Imprimir dato
			echo '<input type="hidden" name="'.$this->_scapeValue($name).'" id="'.$this->_scapeValue($name).'" value="'.$this->_scapeValue($valor).'" '.$requerido.' >';
		}else{
			echo $alerts;
		}

	}

	/******************************************************************************/
	/**
	 * Permite crear un input basado en las configuraciones, ofrece varias opciones.
	 *
	 * Nota: el parametro 'Value' se valida automaticamente segun el FormType declarado
	 * 2=email | 4=entero positivo | 5=decimal | 6=entero con signo
	 * 7 y 8=fecha | 9 y 10=hora | 11=rut (un Value vacio no se valida)
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'FormType' => Tipo de
	 *                      formulario (1 al 13); 'FormAling' => Alineacion del formulario (1 al 5);
	 *                      'FormCol' => Columnas del formulario (0 al 12); 'Placeholder' => Nombre
	 *                      a mostrar; 'PlaceholderIcon' => Icono a mostrar al lado izquierdo Nombre
	 *                      a mostrar; 'Name' => Nombre del input; 'Id' => Identificador del input;
	 *                      'Value' => Valor del input; 'Required' => Si input es requerido (1 al
	 *                      3); 'InputClass' => Clase extra; 'Icon' => Icono a mostrar; 'DataInfo'
	 *                      => Informacion a mostrar debajo de un input; 'dataPops' => Popup con
	 *                      info.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formInput([
	 *     'FormType'        => 1,
	 *     'FormAling'       => 1,
	 *     'FormCol'         => 8,
	 *     'Placeholder'     => 'Nombre',
	 *     'PlaceholderIcon' => 'ri-database-2-line',
	 *     'Name'            => 'Nombre',
	 *     'Id'              => 'Identificador',
	 *     'Value'           => 'asd',
	 *     'Required'        => 1,
	 *     'InputClass'      => '',
	 *     'Icon'            => '',
	 *     'DataInfo'        => 'Lorem ipsum dolor',
	 *     'dataPops'        => [],
	 * ]);
	 * ```
	 */
	public function formInput($Options){

		/**********************  Definiciones   **********************/
		$placeholder      = $Options['Placeholder'];
		$PlaceholderIcon  = $Options['PlaceholderIcon'] ?? '';
		$name             = $Options['Name'];
		$formType         = $Options['FormType'] ?? 1;
		$formAlign        = $Options['FormAling'] ?? 1;
		$formCol          = $Options['FormCol'] ?? 8;
		$identificador    = $Options['Id'] ?? $name;
		$value            = $Options['Value'] ?? '';
		$required         = $Options['Required'] ?? 1;
		$InputClass       = $Options['InputClass'] ?? '';
		$Icono            = $Options['Icon'] ?? '';
		$DataInfo         = $Options['DataInfo'] ?? '';
		$dataPops         = $Options['dataPops'] ?? '';

		//Define valid options arrays
		$validOptions = [
			'required'  => range(1, 3),
			'formType'  => range(1, 13),
			'formAlign' => range(1, 5),
			'formCol'   => range(0, 12)
		];

		//Validate required options
		$optionsToCheck = [
			['value' => $required,  'name' => 'required',  'label' => '$required'],
			['value' => $formType,  'name' => 'formType',  'label' => '$FormType'],
			['value' => $formAlign, 'name' => 'formAlign', 'label' => '$FormAling'],
			['value' => $formCol,   'name' => 'formCol',   'label' => '$FormCol'],
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 1);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

        /************************************/
        //Valido por tipo de elemento
        switch ($formType) {
            case 2:  $dataReturnSwitch = $this->DataValidations->checkData('', [['value' => $value, 'method' => 'validarEmail', 'label' => '$value', 'msg' => 'no es un email valido']], $placeholder, 2); break; //email
            case 4:  $dataReturnSwitch = $this->DataValidations->checkData('', $value, $placeholder, 4); break; //number -> Enteros positivos (sin decimales ni signo)
            case 5:  $dataReturnSwitch = $this->DataValidations->checkData('', $value, $placeholder, 3); break; //number -> Numeros reales (admite decimales con punto o coma)
            case 6:  $dataReturnSwitch = $this->DataValidations->checkData('', $value, $placeholder, 5); break; //number -> Enteros con signo (admite negativos)
            case 7:  $dataReturnSwitch = $this->DataValidations->checkData('', $value, $placeholder, 6); break; //date
            case 8:  $dataReturnSwitch = $this->DataValidations->checkData('', $value, $placeholder, 6); break; //form_input_date
            case 9:  //time
            case 10: //form_time_picker
                     $dataReturnSwitch = $this->DataValidations->checkData('', [['value' => $value, 'method' => 'validarHora', 'label' => '$value', 'msg' => 'no es una hora valida (formato H:M o H:M:S)']], $placeholder, 2); break;
            case 11: //rut
                     $dataReturnSwitch = $this->DataValidations->checkData('', [['value' => $value, 'method' => 'validarRut', 'label' => '$value', 'msg' => 'no es un RUT valido']], $placeholder, 2); break;
        }
		// Verifico si hay datos
		if (isset($dataReturnSwitch) && !empty($dataReturnSwitch) && is_array($dataReturnSwitch)) {
			$errorn += $dataReturnSwitch['nErrors'];
			$alerts .= $dataReturnSwitch['alerts'];
		}

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

            /************************************/
			// Variables vacias
			$ExtraClass = $ExtraClassGroup = $ExtraCode  = $input_1 = $input_2 = $input_3 = '';

            /************************************/
			//Verifico si se utiliza el icono
			if (!empty($Icono)&&$Icono!='') {
				$input_1 = '<div class="input-group '.$ExtraClassGroup.'"><span class="input-group-text" id="basic-addon1"><i class="'.$this->_scapeValue($Icono).'"></i></span>';
                $input_2 = 'aria-describedby="basic-addon1"';
                $input_3 = '</div>';
			}

			/************************************/
			//Verifico
			$nameID          = (strpos($identificador, '[]') !== false) ? str_replace('[]', '', $identificador) . '_' . uniqid() : $identificador;
			$placeholderIcon = (!empty($PlaceholderIcon)&&$PlaceholderIcon!='') ? "<i class='".$this->_scapeValue($PlaceholderIcon)."'></i> " : '';
			$dataInfo        = (!empty($DataInfo)&&$DataInfo!='') ? '<p class="formHelp text-muted">'.$this->_scapeValue($DataInfo).'</p>' : '';
			$dataPopover = $this->_buildDataPopover($dataPops);

			/************************************/
			//Valido si es requerido
			switch ($required) {
				case 1:$requerido = '';                    break;//Si el dato no es requerido
				case 2:$requerido = 'required="required"'; break;//Si el dato es requerido
				case 3:$requerido = 'disabled';            break;//Si el dato esta desactivado
			}

			/************************************/
			// Define el array de configuración
			$formConfigurations = [
				// 1: text
				1 => [
					'InTipo'       => 'text',
					'jsValidation' => 'onkeydown="return soloLetras(event)"',
				],
				// 2: email
				2 => [
					'InTipo'       => 'email',
				],
				// 3: password
				3 => [
					'InTipo'       => 'password',
				],
				// 4: number (Enteros positivos)
				4 => [
					'InTipo'       => 'text',
					'jsValidation' => 'onkeydown="return soloNumeroNatural(event)"',
				],
				// 5: number (Reales/Decimales)
				5 => [
					'InTipo'       => 'text',
					'jsValidation' => 'onkeydown="return soloNumeroRealRacional(event)"',
				],
				// 6: number (Enteros, permite negativos)
				6 => [
					'InTipo'       => 'text',
					'jsValidation' => 'onkeydown="return soloNumeroNaturalReal(event)"',
				],
				// 7: date
				7 => [
					'InTipo'       => 'date',
				],
				// 8: form_input_date (Material Datepicker)
				8 => [
					'InTipo'       => 'text',
					'ExtraCode'    => '
								<script type="text/javascript">
									$(document).ready(function(){
										$("#'.$this->_escapeJs($nameID).'").bootstrapMaterialDatePicker
										({
											time: false,
											lang: "es",
											weekStart: 1,
											cancelText : "Cancelar",
											clearButton: true,
											clearText : "Limpiar",
										});
									});
								</script>',
				],
				// 9: time
				9 => [
					'InTipo'       => 'time',
					'jsValidation' => ' min="00:00" max="24:00"',
				],
				// 10: form_time_picker (Material Timepicker)
				10 => [
					'InTipo'       => 'text',
					'ExtraCode'    => '
								<script type="text/javascript">
									$(document).ready(function(){
										$("#'.$this->_escapeJs($nameID).'").bootstrapMaterialDatePicker
										({
											date: false,
											shortTime: false,
											format: "HH:mm",
											lang: "es",
											cancelText : "Cancelar",
											clearButton: true,
											clearText : "Limpiar",
										});
									});
								</script>',
				],
				// 11: rut
				11 => [
					'InTipo'       => 'text',
					'jsValidation' => 'onkeydown="return soloRut(event)"',
					'ExtraCode'    => '<script>$("#'.$this->_escapeJs($nameID).'").rut();</script>',
				],
				// 12: color
				12 => [
					'InTipo'       => 'color',
				],
				// 13: form_color_picker
				13 => [
					'InTipo'       => 'text',
					'ExtraCode'    => '
								<script type="text/javascript">
									$(function(){
										$("#'.$this->_escapeJs($nameID).'").colorpickerplus();
										$("#'.$this->_escapeJs($nameID).'").on("changeColor", function(e,color){
											if(color==null)
												$(this).val("transparent").css("background-color", "#fff");//tranparent
											else
												$(this).val(color).css("background-color", color);
										});
									});
								</script>',
				],
			];

			// Definición de valores predeterminados
			$defaults = [
				'InTipo'          => 'text', // Un valor por defecto seguro si no se encuentra el tipo
				'jsValidation'    => '',
				'ExtraClass'      => '',
				'ExtraClassGroup' => '',
				'ExtraCode'       => '',
			];

			// Aplicar la configuración
			// Se utiliza isset para verificar que el $formType exista en el array de configuración.
			if (isset($formConfigurations[$formType])) {
				// Combina los valores predeterminados con la configuración específica del tipo
				$config = array_merge($defaults, $formConfigurations[$formType]);
				// Asigna las variables con un solo paso
				$InTipo          = $config['InTipo'];
				$jsValidation    = $config['jsValidation'];
				$ExtraClass      = $config['ExtraClass'];
				$ExtraClassGroup = $config['ExtraClassGroup'];
				$ExtraCode       = $config['ExtraCode'];
			} else {
				// Si el $formType no existe, usa solo los valores predeterminados
				$InTipo          = $defaults['InTipo'];
				$jsValidation    = $defaults['jsValidation'];
				$ExtraClass      = $defaults['ExtraClass'];
				$ExtraClassGroup = $defaults['ExtraClassGroup'];
				$ExtraCode       = $defaults['ExtraCode'];
			}

            /************************************/
			//generacion del input
			$formRoute = $this->_resolveFormRoute('formInput', $formAlign, $formCol);

            /************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath($formRoute);
			$this->TemplateRender->assign('nameID',           $nameID);
			$this->TemplateRender->assign('name',             $name);
			$this->TemplateRender->assign('placeholder',      $placeholder);
			$this->TemplateRender->assign('placeholderIcon',  $placeholderIcon);
			$this->TemplateRender->assign('formCol',          $formCol);
			$this->TemplateRender->assign('InTipo',           $InTipo);
			$this->TemplateRender->assign('InputClass',       $InputClass);
			$this->TemplateRender->assign('value',            $value);
			$this->TemplateRender->assign('requerido',        $requerido);
			$this->TemplateRender->assign('jsValidation',     $jsValidation);
			$this->TemplateRender->assign('input_1',          $input_1);
			$this->TemplateRender->assign('input_2',          $input_2);
			$this->TemplateRender->assign('input_3',          $input_3);
			$this->TemplateRender->assign('ExtraClass',       $ExtraClass);
			$this->TemplateRender->assign('dataInfo',         $dataInfo);
			$this->TemplateRender->assign('dataPopover',      $dataPopover);

			/************************************/
			//ejecucion
			$input  = $this->TemplateRender->render();
			$input .= $ExtraCode;

			/************************************/
			//Imprimir dato
			echo $input;

		}else{

			/************************************/
			//Imprimir dato
			echo $alerts;

		}

	}

	/******************************************************************************/
	/**
	 * Permite crear un input tipo que al llenar datos muestra opciones ya alojadas en la BBDD.
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'FormAling' =>
	 *                      Alineacion del formulario (1 al 5); 'FormCol' => Columnas del formulario
	 *                      (0 al 12); 'Placeholder' => Nombre a mostrar; 'PlaceholderIcon' => Icono
	 *                      a mostrar al lado izquierdo Nombre a mostrar; 'Name' => Nombre del
	 *                      input; 'Id' => Identificador del input; 'Value' => Valor del input;
	 *                      'Required' => Si input es requerido (1 al 3); 'InputClass' => Clase
	 *                      extra; 'Icon' => Icono a mostrar; 'arrData' => Datos recibidos;
	 *                      'dataPops' => Popup con info.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formInputDatalist([
	 *     'FormAling'       => 1,
	 *     'FormCol'         => 8,
	 *     'Placeholder'     => 'Nombre',
	 *     'PlaceholderIcon' => 'ri-database-2-line',
	 *     'Name'            => 'Nombre',
	 *     'Id'              => 'Identificador',
	 *     'Value'           => 'asd',
	 *     'Required'        => 1,
	 *     'InputClass'      => '',
	 *     'Icon'            => '',
	 *     'arrData'         => '',
	 *     'dataPops'        => [],
	 * ]);
	 * ```
	 */
	public function formInputDatalist($Options){

		/**********************  Definiciones   **********************/
		$placeholder      = $Options['Placeholder'];
		$PlaceholderIcon  = $Options['PlaceholderIcon'] ?? '';
		$name             = $Options['Name'];
		$arrData          = $Options['arrData'];
		$FormAling        = $Options['FormAling'] ?? 1;
		$FormCol          = $Options['FormCol'] ?? 8;
		$identificador    = $Options['Id'] ?? $name;
		$value            = $Options['Value'] ?? '';
		$required         = $Options['Required'] ?? 1;
		$InputClass       = $Options['InputClass'] ?? '';
		$Icono            = $Options['Icon'] ?? '';
		$dataPops         = $Options['dataPops'] ?? '';

		//Definir opciones válidas
		$validOptions = [
			'required'  => range(1, 3),
			'FormAling' => range(1, 5),
			'FormCol'   => range(0, 12)
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $required,  'name' => 'required',  'label' => '$required'],
			['value' => $FormAling, 'name' => 'FormAling', 'label' => '$FormAling'],
			['value' => $FormCol,   'name' => 'FormCol',   'label' => '$FormCol']
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 1);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/************************************/
			// Variables vacias
			$input_1 = $input_2 = $input_3 = '';

			/************************************/
			//Valido si es requerido
			switch ($required) {
				case 1:$requerido = '';                    break;//Si el dato no es requerido
				case 2:$requerido = 'required="required"'; break;//Si el dato es requerido
				case 3:$requerido = 'disabled';            break;//Si el dato esta desactivado
			}

            /************************************/
			//Verifico si se utiliza el icono
			if (!empty($Icono)&&$Icono!='') {
				$input_1 = '<div class="input-group"><span class="input-group-text" id="basic-addon1"><i class="'.$this->_scapeValue($Icono).'"></i></span>';
                $input_2 = 'aria-describedby="basic-addon1"';
                $input_3 = '</div>';
			}

			/************************************/
			//Verifico
			$nameID          = (strpos($identificador, '[]') !== false) ? str_replace('[]', '', $identificador) . '_' . uniqid() : $identificador;
			$placeholderIcon = (!empty($PlaceholderIcon)&&$PlaceholderIcon!='') ? "<i class='".$this->_scapeValue($PlaceholderIcon)."'></i> " : '';
			$dataPopover = $this->_buildDataPopover($dataPops);

			/************************************/
			//generacion del input
			$formRoute = $this->_resolveFormRoute('formInputDatalist', $FormAling, $FormCol);

			/************************************/
			//Se genera input
			$dataList = $this->_inputDatalist($name, $arrData);

			/************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath($formRoute);
			$this->TemplateRender->assign('nameID',           $nameID);
			$this->TemplateRender->assign('name',             $name);
			$this->TemplateRender->assign('placeholder',      $placeholder);
			$this->TemplateRender->assign('placeholderIcon',  $placeholderIcon);
			$this->TemplateRender->assign('FormCol',          $FormCol);
			$this->TemplateRender->assign('value',            $value);
			$this->TemplateRender->assign('requerido',        $requerido);
			$this->TemplateRender->assign('InputClass',       $InputClass);
			$this->TemplateRender->assign('input_1',          $input_1);
			$this->TemplateRender->assign('input_2',          $input_2);
			$this->TemplateRender->assign('input_3',          $input_3);
			$this->TemplateRender->assign('dataList',         $dataList);
			$this->TemplateRender->assign('dataPopover',      $dataPopover);

			/************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/************************************/
			//Imprimir dato
			echo $alerts;

		}

	}

	/******************************************************************************/
	/**
	 * Permite crear un input que solo permite el ingreso de numeros, permite valores decimales y
	 * numeros negativos, agregando botones en ambos lados, que aumentan o disminuyen los valores
	 * ingresados, en los rangos preestablecido.
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'FormAling' =>
	 *                      Alineacion del formulario (1 al 5); 'FormCol' => Columnas del formulario
	 *                      (0 al 12); 'Placeholder' => Nombre a mostrar; 'PlaceholderIcon' => Icono
	 *                      a mostrar al lado izquierdo Nombre a mostrar; 'Name' => Nombre del
	 *                      input; 'Id' => Identificador del input; 'Value' => Valor del input;
	 *                      'Min' => Valor Minimo; 'Max' => Valor Maximo; 'Step' => Valores de
	 *                      avance o retroceso; 'Ndecimal' => numero de decimales; 'Required' => Si
	 *                      input es requerido (1 al 3); 'DataInfo' => Informacion a mostrar debajo
	 *                      de un input; 'dataPops' => Popup con info.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formNumberSpinner([
	 *     'FormAling'       => 1,
	 *     'FormCol'         => 8,
	 *     'Placeholder'     => 'Nombre',
	 *     'PlaceholderIcon' => 'ri-database-2-line',
	 *     'Name'            => 'Nombre',
	 *     'Id'              => 'Identificador',
	 *     'Value'           => 'asd',
	 *     'Min'             => '',
	 *     'Max'             => '',
	 *     'Step'            => '',
	 *     'Ndecimal'        => '',
	 *     'Required'        => 1,
	 *     'DataInfo'        => 'Lorem ipsum dolor',
	 *     'dataPops'        => [],
	 * ]);
	 * ```
	 */
	public function formNumberSpinner($Options){

		/**********************  Definiciones   **********************/
		$placeholder      = $Options['Placeholder'];
		$PlaceholderIcon  = $Options['PlaceholderIcon'] ?? '';
		$name             = $Options['Name'];
		$min              = $Options['Min'];
		$max              = $Options['Max'];
		$step             = $Options['Step'];
		$ndecimal         = $Options['Ndecimal'];
		$FormAling        = $Options['FormAling'] ?? 1;
		$FormCol          = $Options['FormCol'] ?? 8;
		$identificador    = $Options['Id'] ?? $name;
		$value            = $Options['Value'] ?? '';
		$required         = $Options['Required'] ?? 1;
		$DataInfo         = $Options['DataInfo'] ?? '';
		$dataPops         = $Options['dataPops'] ?? '';

		//Definir opciones válidas
		$validOptions = [
			'required'  => range(1, 3),
			'FormAling' => range(1, 5),
			'FormCol'   => range(0, 12)
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $required,  'name' => 'required',  'label' => '$required'],
			['value' => $FormAling, 'name' => 'FormAling', 'label' => '$FormAling'],
			['value' => $FormCol,   'name' => 'FormCol',   'label' => '$FormCol'],
		];

		// Validaciones numéricas y enteras
		$fieldsToCheck = [
			['value' => $value,    'method' => 'validarNumero',  'label' => '$value',    'msg' => 'no es un numero'],
			['value' => $min,      'method' => 'validarNumero',  'label' => '$min',      'msg' => 'no es un numero'],
			['value' => $max,      'method' => 'validarNumero',  'label' => '$max',      'msg' => 'no es un numero'],
			['value' => $step,     'method' => 'validarNumero',  'label' => '$step',     'msg' => 'no es un numero'],
			['value' => $ndecimal, 'method' => 'validarNumero',  'label' => '$ndecimal', 'msg' => 'no es un numero'],
			['value' => $ndecimal, 'method' => 'validarEntero',  'label' => '$ndecimal', 'msg' => 'no es un numero entero'],
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 1);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		$dataReturn = $this->DataValidations->checkData('', $fieldsToCheck, $placeholder, 2);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/************************************/
			//Valido si es requerido
			switch ($required) {
				case 1:$requerido = '';                    break;//Si el dato no es requerido
				case 2:$requerido = 'required="required"'; break;//Si el dato es requerido
				case 3:$requerido = 'disabled';            break;//Si el dato esta desactivado
			}

			/************************************/
			//Verifico
			$nameID          = (strpos($identificador, '[]') !== false) ? str_replace('[]', '', $identificador) . '_' . uniqid() : $identificador;
			$valor           = ($value != 0) ? str_replace(',', '.', $value) : '';
			$placeholderIcon = (!empty($PlaceholderIcon)&&$PlaceholderIcon!='') ? "<i class='".$this->_scapeValue($PlaceholderIcon)."'></i> " : '';
			$dataInfo        = (!empty($DataInfo)&&$DataInfo!='') ? '<p class="formHelp text-muted">'.$this->_scapeValue($DataInfo).'</p>' : '';
			$dataPopover = $this->_buildDataPopover($dataPops);

			/************************************/
			//generacion del input
			$formRoute = $this->_resolveFormRoute('formInputNumberSpinner', $FormAling, $FormCol);

			/************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath($formRoute);
			$this->TemplateRender->assign('nameID',           $nameID);
			$this->TemplateRender->assign('name',             $name);
			$this->TemplateRender->assign('placeholder',      $placeholder);
			$this->TemplateRender->assign('placeholderIcon',  $placeholderIcon);
			$this->TemplateRender->assign('FormCol',          $FormCol);
			$this->TemplateRender->assign('valor',            $valor);
			$this->TemplateRender->assign('requerido',        $requerido);
			$this->TemplateRender->assign('min',              $min);
			$this->TemplateRender->assign('max',              $max);
			$this->TemplateRender->assign('step',             $step);
			$this->TemplateRender->assign('ndecimal',         $ndecimal);
			$this->TemplateRender->assign('dataInfo',         $dataInfo);
			$this->TemplateRender->assign('dataPopover',      $dataPopover);

			/************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/************************************/
			//Imprimir dato
			echo $alerts;

		}

	}

	/******************************************************************************/
	/**
	 * Permite crear un input dentro del cual se selecciona la hora que se desea ingresar, el
	 * selector de hora aparece al presionar dentro del input, una vez seleccionada la hora esta
	 * sera traspasada al input.
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'FormAling' =>
	 *                      Alineacion del formulario (1 al 5); 'FormCol' => Columnas del formulario
	 *                      (0 al 12); 'Placeholder' => Nombre a mostrar; 'PlaceholderIcon' => Icono
	 *                      a mostrar al lado izquierdo Nombre a mostrar; 'Name' => Nombre del
	 *                      input; 'Id' => Identificador del input; 'Value' => Valor del input;
	 *                      'Required' => Si input es requerido (1 al 3); 'Position' => Posicion del
	 *                      popup (1 o 2); 'Icon' => Icono a mostrar; 'dataPops' => Popup con info.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formTime([
	 *     'FormAling'       => 1,
	 *     'FormCol'         => 8,
	 *     'Placeholder'     => 'Nombre',
	 *     'PlaceholderIcon' => 'ri-database-2-line',
	 *     'Name'            => 'Nombre',
	 *     'Id'              => 'Identificador',
	 *     'Value'           => 'asd',
	 *     'Required'        => 2,
	 *     'Position'        => '',
	 *     'Icon'            => '',
	 *     'dataPops'        => [],
	 * ]);
	 * ```
	 */
	public function formTime($Options){

		/**********************  Definiciones   **********************/
		$placeholder      = $Options['Placeholder'];
		$PlaceholderIcon  = $Options['PlaceholderIcon'] ?? '';
		$name             = $Options['Name'];
		$FormAling        = $Options['FormAling'] ?? 1;
		$FormCol          = $Options['FormCol'] ?? 8;
		$identificador    = $Options['Id'] ?? $name;
		$value            = $Options['Value'] ?? '';
		$required         = $Options['Required'] ?? 1;
		$position         = $Options['Position'] ?? '';
		$Icono            = $Options['Icon'] ?? '';
		$dataPops         = $Options['dataPops'] ?? '';

		//Definir opciones válidas
		$validOptions = [
			'required'  => range(1, 3),
			'position'  => range(1, 2),
			'FormAling' => range(1, 5),
			'FormCol'   => range(0, 12)
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $required,   'name' => 'required',   'label' => '$required'],
			['value' => $position,   'name' => 'position',   'label' => '$position'],
			['value' => $FormAling,  'name' => 'FormAling',  'label' => '$FormAling'],
			['value' => $FormCol,    'name' => 'FormCol',    'label' => '$FormCol']
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 1);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/************************************/
			// Variables vacias
			$input_1 = $input_2 = $input_3 = '';

			/************************************/
			//Valido si es requerido
			switch ($required) {
				case 1:$requerido = '';                    break;//Si el dato no es requerido
				case 2:$requerido = 'required="required"'; break;//Si el dato es requerido
				case 3:$requerido = 'disabled';            break;//Si el dato esta desactivado
			}

            /************************************/
			//Verifico si se utiliza el icono
			if (!empty($Icono)&&$Icono!='') {
				$input_1 = '<div class="input-group"><span class="input-group-text" id="basic-addon1"><i class="'.$this->_scapeValue($Icono).'"></i></span>';
                $input_2 = 'aria-describedby="basic-addon1"';
                $input_3 = '</div>';
			}

			/************************************/
			//Verifico
			$nameID          = (strpos($identificador, '[]') !== false) ? str_replace('[]', '', $identificador) . '_' . uniqid() : $identificador;
			$placeholderIcon = (!empty($PlaceholderIcon)&&$PlaceholderIcon!='') ? "<i class='".$this->_scapeValue($PlaceholderIcon)."'></i> " : '';
			$dataPopover = $this->_buildDataPopover($dataPops);

			/************************************/
			//Posicion de la burbuja
			$options = ['top', 'bottom'];
			$x_pos   = $options[$position-1];

			/************************************/
			//generacion del input
			$formRoute = $this->_resolveFormRoute('formTime', $FormAling, $FormCol);

			/************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath($formRoute);
			$this->TemplateRender->assign('nameID',           $nameID);
			$this->TemplateRender->assign('name',             $name);
			$this->TemplateRender->assign('placeholder',      $placeholder);
			$this->TemplateRender->assign('placeholderIcon',  $placeholderIcon);
			$this->TemplateRender->assign('FormCol',          $FormCol);
			$this->TemplateRender->assign('value',            $value);
			$this->TemplateRender->assign('requerido',        $requerido);
			$this->TemplateRender->assign('input_1',          $input_1);
			$this->TemplateRender->assign('input_2',          $input_2);
			$this->TemplateRender->assign('input_3',          $input_3);
			$this->TemplateRender->assign('x_pos',            $x_pos);
			$this->TemplateRender->assign('dataPopover',      $dataPopover);

			/************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/************************************/
			//Imprimir dato
			echo $alerts;

		}

	}

	/******************************************************************************/
	/**
	 * Permite crear un input tipo text.
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'FormAling' =>
	 *                      Alineacion del formulario (1 al 5); 'FormCol' => Columnas del formulario
	 *                      (0 al 12); 'Placeholder' => Nombre a mostrar; 'PlaceholderIcon' => Icono
	 *                      a mostrar al lado izquierdo Nombre a mostrar; 'Name' => Nombre del
	 *                      input; 'Id' => Identificador del input; 'Value' => Valor del input;
	 *                      'Required' => Si input es requerido (1 al 3); 'dataPops' => Popup con
	 *                      info.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formTextarea([
	 *     'FormAling'       => 1,
	 *     'FormCol'         => 8,
	 *     'Placeholder'     => 'Nombre',
	 *     'PlaceholderIcon' => 'ri-database-2-line',
	 *     'Name'            => 'Nombre',
	 *     'Id'              => 'Identificador',
	 *     'Value'           => 'asd',
	 *     'Required'        => 2,
	 *     'dataPops'        => [],
	 * ]);
	 * ```
	 */
	public function formTextarea($Options){

		/**********************  Definiciones   **********************/
		$placeholder      = $Options['Placeholder'];
		$PlaceholderIcon  = $Options['PlaceholderIcon'] ?? '';
		$name             = $Options['Name'];
		$FormAling        = $Options['FormAling'] ?? 1;
		$FormCol          = $Options['FormCol'] ?? 8;
		$identificador    = $Options['Id'] ?? $name;
		$value            = $Options['Value'] ?? '';
		$required         = $Options['Required'] ?? 1;
		$dataPops         = $Options['dataPops'] ?? '';

		//Definir opciones válidas
		$validOptions = [
			'required'  => range(1, 3),
			'FormAling' => range(1, 5),
			'FormCol'   => range(0, 12)
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $required,  'name' => 'required',  'label' => '$required'],
			['value' => $FormAling, 'name' => 'FormAling', 'label' => '$FormAling'],
			['value' => $FormCol,   'name' => 'FormCol',   'label' => '$FormCol'],
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 1);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/************************************/
			//Valido si es requerido
			switch ($required) {
				case 1:$requerido = '';                    break;//Si el dato no es requerido
				case 2:$requerido = 'required="required"'; break;//Si el dato es requerido
				case 3:$requerido = 'disabled';            break;//Si el dato esta desactivado
			}

            /************************************/
			//Verifico
			$nameID          = (strpos($identificador, '[]') !== false) ? str_replace('[]', '', $identificador) . '_' . uniqid() : $identificador;
			$placeholderIcon = (!empty($PlaceholderIcon)&&$PlaceholderIcon!='') ? "<i class='".$this->_scapeValue($PlaceholderIcon)."'></i> " : '';
			$dataPopover = $this->_buildDataPopover($dataPops);

			/************************************/
			//generacion del input
			$formRoute = $this->_resolveFormRoute('formTextBox', $FormAling, $FormCol);

			/************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath($formRoute);
			$this->TemplateRender->assign('nameID',           $nameID);
			$this->TemplateRender->assign('placeholder',      $placeholder);
			$this->TemplateRender->assign('placeholderIcon',  $placeholderIcon);
			$this->TemplateRender->assign('FormCol',          $FormCol);
			$this->TemplateRender->assign('name',             $name);
			$this->TemplateRender->assign('requerido',        $requerido);
			$this->TemplateRender->assign('value',            $value);
			$this->TemplateRender->assign('dataPopover',      $dataPopover);

			/************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/************************************/
			//Imprimir dato
			echo $alerts;

		}

	}

	/******************************************************************************/
	/**
	 * Permite crear un editor de texto completo.
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'FormAling' =>
	 *                      Alineacion del formulario (1 al 5); 'FormCol' => Columnas del formulario
	 *                      (0 al 12); 'Placeholder' => Nombre a mostrar; 'PlaceholderIcon' => Icono
	 *                      a mostrar al lado izquierdo Nombre a mostrar; 'Name' => Nombre del
	 *                      input; 'Id' => Identificador del input; 'Value' => Valor del input;
	 *                      'Required' => Si input es requerido (1 al 2); 'Tipo' => Tipo de input (1
	 *                      al 3); 'dataPops' => Popup con info.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formCKEditor([
	 *     'FormAling'       => 1,
	 *     'FormCol'         => 8,
	 *     'Placeholder'     => 'Nombre',
	 *     'PlaceholderIcon' => 'ri-database-2-line',
	 *     'Name'            => 'Nombre',
	 *     'Id'              => 'Identificador',
	 *     'Value'           => 'asd',
	 *     'Required'        => 2,
	 *     'Tipo'            => 2,
	 *     'dataPops'        => [],
	 * ]);
	 * ```
	 */
	public function formCKEditor($Options){

		/**********************  Definiciones   **********************/
		$placeholder      = $Options['Placeholder'];
		$PlaceholderIcon  = $Options['PlaceholderIcon'] ?? '';
		$name             = $Options['Name'];
		$FormAling        = $Options['FormAling'] ?? 1;
		$FormCol          = $Options['FormCol'] ?? 8;
		$identificador    = $Options['Id'] ?? $name;
		$value            = $Options['Value'] ?? '';
		$required         = $Options['Required'] ?? 1;
		$tipo             = $Options['Tipo'] ?? 1;
		$dataPops         = $Options['dataPops'] ?? '';

		//Definir opciones válidas
		$validOptions = [
			'required'  => range(1, 2),
			'tipo'      => range(1, 3),
			'FormAling' => range(1, 5),
			'FormCol'   => range(0, 12)
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $required,   'name' => 'required',  'label' => '$required'],
			['value' => $tipo,       'name' => 'tipo',      'label' => '$tipo'],
			['value' => $FormAling,  'name' => 'FormAling', 'label' => '$FormAling'],
			['value' => $FormCol,    'name' => 'FormCol',   'label' => '$FormCol']
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 1);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/************************************/
			//Verifico
			$nameID          = (strpos($identificador, '[]') !== false) ? str_replace('[]', '', $identificador) . '_' . uniqid() : $identificador;
			$requerido       = ($required === 2) ? 'required="required"' : '';
			$placeholderIcon = (!empty($PlaceholderIcon)&&$PlaceholderIcon!='') ? "<i class='".$this->_scapeValue($PlaceholderIcon)."'></i> " : '';
			$dataPopover = $this->_buildDataPopover($dataPops);

			/************************************/
			//generacion del input
			$formRoute = $this->_resolveFormRoute('formCKEditor', $FormAling, $FormCol);

			/************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath($formRoute);
			$this->TemplateRender->assign('nameID',           $nameID);
			$this->TemplateRender->assign('placeholder',      $placeholder);
			$this->TemplateRender->assign('placeholderIcon',  $placeholderIcon);
			$this->TemplateRender->assign('FormCol',          $FormCol);
			$this->TemplateRender->assign('name',             $name);
			$this->TemplateRender->assign('requerido',        $requerido);
			$this->TemplateRender->assign('value',            $value);
			$this->TemplateRender->assign('dataPopover',      $dataPopover);

			/************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/************************************/
			//Imprimir dato
			echo $alerts;

		}

	}

	/******************************************************************************/
	/**
	 * Permite crear un input tipo checkbox.
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'FormCol' => Columnas
	 *                      del formulario (0 al 12); 'Placeholder' => Nombre a mostrar; 'Name' =>
	 *                      Nombre del input; 'Id' => Identificador del input; 'Value' => Valor del
	 *                      input; 'Required' => Si input es requerido (1 al 3); 'Color' => Color a
	 *                      mostrar; 'DataInfo' => Informacion a mostrar debajo de un input;
	 *                      'dataPops' => Popup con info.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formCheckbox([
	 *     'FormCol'     => 8,
	 *     'Placeholder' => 'Nombre',
	 *     'Name'        => 'Nombre',
	 *     'Id'          => 'Identificador',
	 *     'Value'       => 'asd',
	 *     'Required'    => 1,
	 *     'Color'       => '',
	 *     'DataInfo'    => 'Lorem ipsum dolor',
	 *     'dataPops'    => [],
	 * ]);
	 * ```
	 */
	public function formCheckbox($Options){

		/**********************  Definiciones   **********************/
		$placeholder   = $Options['Placeholder'];
		$name          = $Options['Name'];
		$FormCol       = $Options['FormCol'] ?? 8;
		$identificador = $Options['Id'] ?? $name;
		$value         = $Options['Value'] ?? '';
		$required      = $Options['Required'] ?? 1;
		$color         = $Options['Color'] ?? 1;
		$DataInfo      = $Options['DataInfo'] ?? '';
		$dataPops      = $Options['dataPops'] ?? '';

		//Definir opciones válidas
		$validOptions = [
			'required'  => range(1, 3),
			'color'     => range(1, 6),
			'FormCol'   => range(0, 12),
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $required,  'name' => 'required',  'label' => '$required'],
			['value' => $color,     'name' => 'color',     'label' => '$color'],
			['value' => $FormCol,   'name' => 'FormCol',   'label' => '$FormCol'],
		];

		// Validaciones numéricas y enteras
		$fieldsToCheck = [
			['value' => $value, 'method' => 'validarNumero',  'label' => '$value', 'msg' => 'no es un numero'],
			['value' => $value, 'method' => 'validarEntero',  'label' => '$value', 'msg' => 'no es un numero entero'],
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 1);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		$dataReturn = $this->DataValidations->checkData('', $fieldsToCheck, $placeholder, 2);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/************************************/
			//Valido si es requerido
			switch ($required) {
				case 1:  $requerido = '';                    $requerido_div = '';          break;//Si el dato no es requerido
				case 2:  $requerido = 'required="required"'; $requerido_div = 'required';  break;//Si el dato es requerido
				case 3:  $requerido = 'disabled';            $requerido_div = '';          break;//Si el dato esta desactivado
				default: $requerido = '';                    $requerido_div = '';          break;
			}

			/************************************/
			//Si el tab correspondiente esta seleccionado
			$check = (isset($value) && $value == 2) ? 'checked' : '';
			$valor = '2';

			/************************************/
			//Selecciono el tipo de mensaje
			$options = ['default', 'primary', 'success', 'danger', 'warning', 'info'];
			$tipo    = $options[$color-1];

			/************************************/
			//Verifico
			$nameID      = (strpos($identificador, '[]') !== false) ? str_replace('[]', '', $identificador) . '_' . uniqid() : $identificador;
			$dataInfo    = (!empty($DataInfo)&&$DataInfo!='') ? '<p class="formHelp text-muted">'.$this->_scapeValue($DataInfo).'</p>' : '';
			$dataPopover = $this->_buildDataPopover($dataPops);

			/************************************/
			//Se genera input
			$formInput = $this->_checkInputGen($placeholder,$name, $nameID, $tipo, 'checkbox', $valor, $check, $requerido, $requerido_div);

			/************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath('../app/templates/Forms/formCheckRadioBox_1.php');
			$this->TemplateRender->assign('otrcol',      (12 - $FormCol));
			$this->TemplateRender->assign('nameID',      $nameID);
			$this->TemplateRender->assign('placeholder', ($FormCol==12 ? '' : $placeholder));
			$this->TemplateRender->assign('FormCol',     $FormCol);
			$this->TemplateRender->assign('formInput',   $formInput);
			$this->TemplateRender->assign('dataInfo',    $dataInfo);
			$this->TemplateRender->assign('dataPopover', $dataPopover);

			/************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/************************************/
			//Imprimir dato
			echo $alerts;

		}

	}

	/******************************************************************************/
	/**
	 * Permite crear un input tipo checkbox en base a datos de la base de datos, que lleva por
	 * defecto opciones activas.
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'FormCol' => Columnas
	 *                      del formulario (0 al 12); 'Placeholder' => Nombre a mostrar; 'Name' =>
	 *                      Nombre del input; 'Id' => Identificador del input; 'Value' => Valor del
	 *                      input; 'Required' => Si input es requerido (1 al 3); 'Color' => Color a
	 *                      mostrar; 'arrData' => Datos a recorrer.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formCheckboxActive([
	 *     'FormCol'     => 8,
	 *     'Placeholder' => 'Nombre',
	 *     'Name'        => 'Nombre',
	 *     'Id'          => 'Identificador',
	 *     'Value'       => 'asd',
	 *     'Required'    => 1,
	 *     'Color'       => '',
	 *     'arrData'     => '',
	 * ]);
	 * ```
	 */
	public function formCheckboxActive($Options){

		/**********************  Definiciones   **********************/
		$placeholder   = $Options['Placeholder'];
		$name          = $Options['Name'];
		$arrData       = $Options['arrData'];
		$FormCol       = $Options['FormCol'] ?? 8;
		$identificador = $Options['Id'] ?? $name;
		$value         = $Options['Value'] ?? '';
		$required      = $Options['Required'] ?? 1;
		$color         = $Options['Color'] ?? 1;

		//Definir opciones válidas
		$validOptions = [
			'required'  => range(1, 3),
			'color'     => range(1, 6),
			'FormCol'   => range(0, 12)
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $required,  'name' => 'required',  'label' => '$required'],
			['value' => $color,     'name' => 'color',     'label' => '$color'],
			['value' => $FormCol,   'name' => 'FormCol',   'label' => '$FormCol'],
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 1);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/************************************/
			//Verifico si nombre viene de un array
			$nameID = (strpos($identificador, '[]') !== false) ? str_replace('[]', '', $identificador) . '_' . uniqid() : $identificador;

			/************************************/
			//Valido si es requerido
			switch ($required) {
				case 1:  $requerido = '';          $requerido_div = '';  break;//Si el dato no es requerido
				case 2:  $requerido = '';          $requerido_div = '';  break;//Si el dato es requerido
				case 3:  $requerido = 'disabled';  $requerido_div = '';  break;//Si el dato esta desactivado
				default: $requerido = '';          $requerido_div = '';  break;
			}

			/************************************/
			//Selecciono el tipo de mensaje
			$options = ['default', 'primary', 'success', 'danger', 'warning', 'info'];
			$tipo    = $options[$color-1];

			/************************************/
			//vars
			$arrValTab = array_map('trim', explode(",", $value));

			/************************************/
			$y         = 1;
			$formInput = '';
			// Recorro
			foreach ( $arrData as $select ) {
				/************************************/
				//Si el tab correspondiente esta seleccionado
				$check = (isset($arrValTab[$y]) && $arrValTab[$y] == 2) ? 'checked' : '';
				$valor = '2';
				/************************************/
				//generacion del input
				$formInput .= $this->_checkInputGen($select['Nombre'],$name.'_'.$select['ID'], $nameID.'_'.$select['ID'], $tipo, 'checkbox', $valor, $check, $requerido, $requerido_div);
				//sumo
				$y++;
			}

			/************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath('../app/templates/Forms/formCheckRadioBoxActive_1.php');
			$this->TemplateRender->assign('otrcol',      (12 - $FormCol));
			$this->TemplateRender->assign('nameID',      $nameID);
			$this->TemplateRender->assign('placeholder', $placeholder);
			$this->TemplateRender->assign('FormCol',     $FormCol);
			$this->TemplateRender->assign('formInput',   $formInput);

			/************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/************************************/
			//Imprimir dato
			echo $alerts;

		}

	}

	/******************************************************************************/
	/**
	 * Permite crear un input tipo radio.
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'FormCol' => Columnas
	 *                      del formulario (0 al 12); 'Placeholder' => Nombre a mostrar; 'Name' =>
	 *                      Nombre del input; 'Id' => Identificador del input; 'Value' => Valor del
	 *                      input; 'Required' => Si input es requerido (1 al 3); 'Color' => Color a
	 *                      mostrar; 'DataInfo' => Informacion a mostrar debajo de un input;
	 *                      'dataPops' => Popup con info.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formRadio([
	 *     'FormCol'     => 8,
	 *     'Placeholder' => 'Nombre',
	 *     'Name'        => 'Nombre',
	 *     'Id'          => 'Identificador',
	 *     'Value'       => 'asd',
	 *     'Required'    => 1,
	 *     'Color'       => '',
	 *     'DataInfo'    => 'Lorem ipsum dolor',
	 *     'dataPops'    => [],
	 * ]);
	 * ```
	 */
	public function formRadio($Options){

		/**********************  Definiciones   **********************/
		$placeholder   = $Options['Placeholder'];
		$name          = $Options['Name'];
		$FormCol       = $Options['FormCol'] ?? 8;
		$identificador = $Options['Id'] ?? $name;
		$value         = $Options['Value'] ?? '';
		$required      = $Options['Required'] ?? 1;
		$color         = $Options['Color'] ?? 1;
		$DataInfo      = $Options['DataInfo'] ?? '';
		$dataPops      = $Options['dataPops'] ?? '';

		//Definir opciones válidas
		$validOptions = [
			'required'  => range(1, 3),
			'color'     => range(1, 6),
			'FormCol'   => range(0, 12)
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $required,  'name' => 'required',  'label' => '$required'],
			['value' => $color,     'name' => 'color',     'label' => '$color'],
			['value' => $FormCol,   'name' => 'FormCol',   'label' => '$FormCol'],
		];

		// Validaciones numéricas y enteras
		$fieldsToCheck = [
			['value' => $value, 'method' => 'validarNumero',  'label' => '$value', 'msg' => 'no es un numero'],
			['value' => $value, 'method' => 'validarEntero',  'label' => '$value', 'msg' => 'no es un numero entero'],
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 1);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		$dataReturn = $this->DataValidations->checkData('', $fieldsToCheck, $placeholder, 2);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/************************************/
			//Valido si es requerido
			switch ($required) {
				case 1:  $requerido = '';          $requerido_div = '';  break;//Si el dato no es requerido
				case 2:  $requerido = '';          $requerido_div = '';  break;//Si el dato es requerido
				case 3:  $requerido = 'disabled';  $requerido_div = '';  break;//Si el dato esta desactivado
				default: $requerido = '';          $requerido_div = '';  break;
			}

			/************************************/
			//Si el tab correspondiente esta seleccionado
			$check = (isset($value) && $value == 2) ? 'checked' : '';
			$valor = '2';

			/************************************/
			//Selecciono el tipo de mensaje
			$options = ['default', 'primary', 'success', 'danger', 'warning', 'info'];
			$tipo    = $options[$color-1];

			/************************************/
			//Verifico
			$nameID      = (strpos($identificador, '[]') !== false) ? str_replace('[]', '', $identificador) . '_' . uniqid() : $identificador;
			$dataInfo    = (!empty($DataInfo)&&$DataInfo!='') ? '<p class="formHelp text-muted">'.$this->_scapeValue($DataInfo).'</p>' : '';
			$dataPopover = $this->_buildDataPopover($dataPops);

			/************************************/
			//Se genera input
			$formInput = $this->_checkInputGen($placeholder,$name, $nameID, $tipo, 'radio', $valor, $check, $requerido, $requerido_div);

			/************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath('../app/templates/Forms/formCheckRadioBox_1.php');
			$this->TemplateRender->assign('otrcol',      (12 - $FormCol));
			$this->TemplateRender->assign('nameID',      $nameID);
			$this->TemplateRender->assign('placeholder', $placeholder);
			$this->TemplateRender->assign('FormCol',     $FormCol);
			$this->TemplateRender->assign('formInput',   $formInput);
			$this->TemplateRender->assign('dataInfo',    $dataInfo);
			$this->TemplateRender->assign('dataPopover', $dataPopover);

			/************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/************************************/
			//Imprimir dato
			echo $alerts;

		}

	}

	/******************************************************************************/
	/**
	 * Permite crear un input tipo radio en base a datos de la base de datos, que lleva por defecto
	 * opciones activas.
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'FormCol' => Columnas
	 *                      del formulario (0 al 12); 'Placeholder' => Nombre a mostrar; 'Name' =>
	 *                      Nombre del input; 'Id' => Identificador del input; 'Value' => Valor del
	 *                      input; 'Required' => Si input es requerido (1 al 3); 'Color' => Color a
	 *                      mostrar; 'arrData' => Datos a recorrer.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formRadioActive([
	 *     'FormCol'     => 8,
	 *     'Placeholder' => 'Nombre',
	 *     'Name'        => 'Nombre',
	 *     'Id'          => 'Identificador',
	 *     'Value'       => 'asd',
	 *     'Required'    => 1,
	 *     'Color'       => '',
	 *     'arrData'     => '',
	 * ]);
	 * ```
	 */
	public function formRadioActive($Options){

		/**********************  Definiciones   **********************/
		$placeholder   = $Options['Placeholder'];
		$name          = $Options['Name'];
		$arrData       = $Options['arrData'];
		$FormCol       = $Options['FormCol'] ?? 8;
		$identificador = $Options['Id'] ?? $name;
		$value         = $Options['Value'] ?? '';
		$required      = $Options['Required'] ?? 1;
		$color         = $Options['Color'] ?? 1;

		//Definir opciones válidas
		$validOptions = [
			'required'  => range(1, 3),
			'color'     => range(1, 6),
			'FormCol'   => range(0, 12),
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $required,  'name' => 'required',  'label' => '$required'],
			['value' => $color,     'name' => 'color',     'label' => '$color'],
			['value' => $FormCol,   'name' => 'FormCol',   'label' => '$FormCol'],
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 1);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/************************************/
			//Verifico si nombre viene de un array
			$nameID = (strpos($identificador, '[]') !== false) ? str_replace('[]', '', $identificador) . '_' . uniqid() : $identificador;

			/************************************/
			//Valido si es requerido
			switch ($required) {
				case 1:  $requerido = '';          $requerido_div = '';  break;//Si el dato no es requerido
				case 2:  $requerido = '';          $requerido_div = '';  break;//Si el dato es requerido
				case 3:  $requerido = 'disabled';  $requerido_div = '';  break;//Si el dato esta desactivado
				default: $requerido = '';          $requerido_div = '';  break;
			}

			/************************************/
			//Selecciono el tipo de mensaje
			$options = ['default', 'primary', 'success', 'danger', 'warning', 'info'];
			$tipo    = $options[$color-1];

			/************************************/
			//vars
			$arrValTab = array_map('trim', explode(",", $value));

			/************************************/
			$y         = 1;
			$formInput = '';
			// Recorro
			foreach ( $arrData as $select ) {
				/************************************/
				//Si el tab correspondiente esta seleccionado
				$check = (isset($arrValTab[$y]) && $arrValTab[$y] == 2) ? 'checked' : '';
				$valor = '2';
				/************************************/
				//generacion del input
				$formInput .= $this->_checkInputGen($select['Nombre'],$name, $nameID.'_'.$select['ID'], $tipo, 'radio', $valor, $check, $requerido, $requerido_div);
				//sumo
				$y++;
			}

			/************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath('../app/templates/Forms/formCheckRadioBoxActive_1.php');
			$this->TemplateRender->assign('otrcol',      (12 - $FormCol));
			$this->TemplateRender->assign('nameID',      $nameID);
			$this->TemplateRender->assign('placeholder', $placeholder);
			$this->TemplateRender->assign('FormCol',     $FormCol);
			$this->TemplateRender->assign('formInput',   $formInput);

			/************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/************************************/
			//Imprimir dato
			echo $alerts;

		}

	}

	/******************************************************************************/
	/**
	 * Permite crear un input tipo switch.
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'FormCol' => Columnas
	 *                      del formulario (0 al 12); 'Placeholder' => Nombre a mostrar; 'Name' =>
	 *                      Nombre del input; 'Id' => Identificador del input; 'Value' => Valor del
	 *                      input; 'Required' => Si input es requerido (1 al 3); 'Color' => Color a
	 *                      mostrar; 'DataInfo' => Informacion a mostrar debajo de un input;
	 *                      'dataPops' => Popup con info.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formSwitch([
	 *     'FormCol'     => 8,
	 *     'Placeholder' => 'Nombre',
	 *     'Name'        => 'Nombre',
	 *     'Id'          => 'Identificador',
	 *     'Value'       => 'asd',
	 *     'Required'    => 1,
	 *     'Color'       => '',
	 *     'DataInfo'    => 'Lorem ipsum dolor',
	 *     'dataPops'    => [],
	 * ]);
	 * ```
	 */
	public function formSwitch($Options){

		/**********************  Definiciones   **********************/
		$placeholder   = $Options['Placeholder'];
		$name          = $Options['Name'];
		$FormCol       = $Options['FormCol'] ?? 8;
		$identificador = $Options['Id'] ?? $name;
		$value         = $Options['Value'] ?? '';
		$required      = $Options['Required'] ?? 1;
		$color         = $Options['Color'] ?? 1;
		$DataInfo      = $Options['DataInfo'] ?? '';
		$dataPops      = $Options['dataPops'] ?? '';

		//Definir opciones válidas
		$validOptions = [
			'required'  => range(1, 3),
			'color'     => range(1, 6),
			'FormCol'   => range(0, 12)
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $required,  'name' => 'required',  'label' => '$required'],
			['value' => $color,     'name' => 'color',     'label' => '$color'],
			['value' => $FormCol,   'name' => 'FormCol',   'label' => '$FormCol'],
		];

		// Validaciones numéricas y enteras
		$fieldsToCheck = [
			['value' => $value, 'method' => 'validarNumero',  'label' => '$value', 'msg' => 'no es un numero'],
			['value' => $value, 'method' => 'validarEntero',  'label' => '$value', 'msg' => 'no es un numero entero'],
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 1);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		$dataReturn = $this->DataValidations->checkData('', $fieldsToCheck, $placeholder, 2);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/************************************/
			//Valido si es requerido
			switch ($required) {
				case 1:  $requerido = '';                    $requerido_div = '';          break;//Si el dato no es requerido
				case 2:  $requerido = 'required="required"'; $requerido_div = 'required';  break;//Si el dato es requerido
				case 3:  $requerido = 'disabled';            $requerido_div = '';          break;//Si el dato esta desactivado
				default: $requerido = '';                    $requerido_div = '';          break;
			}

			/************************************/
			//Si el tab correspondiente esta seleccionado
			$check = (isset($value) && $value == 2) ? 'checked' : '';
			$valor = '2';

			/************************************/
			//Verifico
			$nameID      = (strpos($identificador, '[]') !== false) ? str_replace('[]', '', $identificador) . '_' . uniqid() : $identificador;
			$dataInfo    = (!empty($DataInfo)&&$DataInfo!='') ? '<p class="formHelp text-muted">'.$this->_scapeValue($DataInfo).'</p>' : '';
			$dataPopover = $this->_buildDataPopover($dataPops);

			/************************************/
			//Selecciono el tipo de mensaje
			$options = ['default', 'primary', 'success', 'danger', 'warning', 'info'];
			$tipo    = $options[$color-1];

			/************************************/
			//Si el valor es 0
			$extracol = ((12 - $FormCol) != 0) ? '<label class="col-form-label col-sm-'.(12 - $FormCol).'">'.$placeholder.'</label>' : '';

			/************************************/
			//Se genera input
			$formInput = $this->_checkInputGen($placeholder,$name, $nameID, $tipo.' form-switch', 'checkbox', $valor, $check, $requerido, $requerido_div);

			/************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath('../app/templates/Forms/formSwitch_1.php');
			$this->TemplateRender->assign('nameID',      $nameID);
			$this->TemplateRender->assign('extracol',    $extracol);
			$this->TemplateRender->assign('FormCol',     $FormCol);
			$this->TemplateRender->assign('formInput',   $formInput);
			$this->TemplateRender->assign('dataInfo',    $dataInfo);
			$this->TemplateRender->assign('dataPopover', $dataPopover);

			/************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/************************************/
			//Imprimir dato
			echo $alerts;

		}

	}

	/******************************************************************************/
	/**
	 * Permite crear un input tipo switch en base a datos de la base de datos, que lleva por defecto
	 * opciones activas.
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'FormCol' => Columnas
	 *                      del formulario (0 al 12); 'Placeholder' => Nombre a mostrar; 'Name' =>
	 *                      Nombre del input; 'Id' => Identificador del input; 'Value' => Valor del
	 *                      input; 'Required' => Si input es requerido (1 al 3); 'Color' => Color a
	 *                      mostrar; 'arrData' => Datos a recorrer.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formSwitchActive([
	 *     'FormCol'     => 8,
	 *     'Placeholder' => 'Nombre',
	 *     'Name'        => 'Nombre',
	 *     'Id'          => 'Identificador',
	 *     'Value'       => 'asd',
	 *     'Required'    => 1,
	 *     'Color'       => '',
	 *     'arrData'     => '',
	 * ]);
	 * ```
	 */
	public function formSwitchActive($Options){

		/**********************  Definiciones   **********************/
		$placeholder   = $Options['Placeholder'];
		$name          = $Options['Name'];
		$arrData       = $Options['arrData'];
		$FormCol       = $Options['FormCol'] ?? 8;
		$identificador = $Options['Id'] ?? $name;
		$value         = $Options['Value'] ?? '';
		$required      = $Options['Required'] ?? 1;
		$color         = $Options['Color'] ?? 1;

		//Definir opciones válidas
		$validOptions = [
			'required'  => range(1, 3),
			'color'     => range(1, 6),
			'FormCol'   => range(0, 12)
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $required,  'name' => 'required',  'label' => '$required'],
			['value' => $color,     'name' => 'color',     'label' => '$color'],
			['value' => $FormCol,   'name' => 'FormCol',   'label' => '$FormCol'],
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 1);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/************************************/
			//Verifico si nombre viene de un array
			$nameID = (strpos($identificador, '[]') !== false) ? str_replace('[]', '', $identificador) . '_' . uniqid() : $identificador;

			/************************************/
			//Valido si es requerido
			switch ($required) {
				case 1:  $requerido = '';          $requerido_div = '';  break;//Si el dato no es requerido
				case 2:  $requerido = '';          $requerido_div = '';  break;//Si el dato es requerido
				case 3:  $requerido = 'disabled';  $requerido_div = '';  break;//Si el dato esta desactivado
				default: $requerido = '';          $requerido_div = '';  break;
			}

			/************************************/
			//Selecciono el tipo de mensaje
			$options = ['default', 'primary', 'success', 'danger', 'warning', 'info'];
			$tipo    = $options[$color-1];

			/************************************/
			//vars
			$arrValTab = array_map('trim', explode(",", $value));

			/************************************/
			$y         = 1;
			$formInput = '';
			// Recorro
			foreach ( $arrData as $select ) {
				/************************************/
				//Si el tab correspondiente esta seleccionado
				$check = (isset($arrValTab[$y]) && $arrValTab[$y] == 2) ? 'checked' : '';
				$valor = '2';
				/************************************/
				//generacion del input
				$formInput .= $this->_checkInputGen($select['Nombre'], $name.'_'.$select['ID'], $nameID.'_'.$select['ID'], $tipo.' form-switch', 'checkbox', $valor, $check, $requerido, $requerido_div);
				//sumo
				$y++;
			}

			/************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath('../app/templates/Forms/formSwitchActive_1.php');
			$this->TemplateRender->assign('otrcol',      (12 - $FormCol));
			$this->TemplateRender->assign('nameID',      $nameID);
			$this->TemplateRender->assign('placeholder', $placeholder);
			$this->TemplateRender->assign('FormCol',     $FormCol);
			$this->TemplateRender->assign('formInput',   $formInput);

			/************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/************************************/
			//Imprimir dato
			echo $alerts;

		}

	}

	/******************************************************************************/
	/**
	 * Permite crear un input tipo select en base a datos de la base de datos.
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'FormAling' =>
	 *                      Alineacion del formulario (1 al 5); 'FormCol' => Columnas del formulario
	 *                      (0 al 12); 'Placeholder' => Nombre a mostrar; 'PlaceholderIcon' => Icono
	 *                      a mostrar al lado izquierdo Nombre a mostrar; 'Name' => Nombre del
	 *                      input; 'Id' => Identificador del input; 'Value' => Valor del input;
	 *                      'Required' => Si input es requerido (1 al 2); 'arrData' => Datos
	 *                      recibidos; 'DataInfo' => Informacion a mostrar debajo de un input;
	 *                      'dataPops' => Popup con info.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formSelect([
	 *     'FormAling'       => 1,
	 *     'FormCol'         => 8,
	 *     'Placeholder'     => 'Nombre',
	 *     'PlaceholderIcon' => 'ri-database-2-line',
	 *     'Name'            => 'Nombre',
	 *     'Id'              => 'Identificador',
	 *     'Value'           => 'asd',
	 *     'Required'        => 2,
	 *     'arrData'         => '',
	 *     'DataInfo'        => 'Lorem ipsum dolor',
	 *     'dataPops'        => [],
	 * ]);
	 * ```
	 */
	public function formSelect($Options){

		/**********************  Definiciones   **********************/
		$placeholder      = $Options['Placeholder'];
		$PlaceholderIcon  = $Options['PlaceholderIcon'] ?? '';
		$name             = $Options['Name'];
		$arrData          = $Options['arrData'];
		$FormAling        = $Options['FormAling'] ?? 1;
		$FormCol          = $Options['FormCol'] ?? 8;
		$identificador    = $Options['Id'] ?? $name;
		$value            = $Options['Value'] ?? '';
		$required         = $Options['Required'] ?? 1;
		$DataInfo         = $Options['DataInfo'] ?? '';
		$dataPops         = $Options['dataPops'] ?? '';

		//Definir opciones válidas
		$validOptions = [
			'required'  => range(1, 2),
			'FormAling' => range(1, 5),
			'FormCol'   => range(0, 12)
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $required,  'name' => 'required',  'label' => '$required'],
			['value' => $FormAling, 'name' => 'FormAling', 'label' => '$FormAling'],
			['value' => $FormCol,   'name' => 'FormCol',   'label' => '$FormCol']
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 1);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

            /************************************/
			//Verifico
			$nameID           = (strpos($identificador, '[]') !== false) ? str_replace('[]', '', $identificador) . '_' . uniqid() : $identificador;
			$selectProperties = ($required === 2) ? 'required="required"' : '';
			$dataInfo         = (!empty($DataInfo)&&$DataInfo!='') ? '<p class="formHelp text-muted">'.$this->_scapeValue($DataInfo).'</p>' : '';
			$dataPopover = $this->_buildDataPopover($dataPops);

			/************************************/
			//Imprimir dato
			echo $this->_selectInputGen($FormAling, $FormCol, $placeholder, $PlaceholderIcon, $name, $nameID, $value, $selectProperties, $arrData, '', $dataInfo, $dataPopover);

		}else{

			/************************************/
			//Imprimir dato
			echo $alerts;

		}

	}

	/******************************************************************************/
	/**
	 * Permite crear un input tipo select en base a datos de la base de datos, con un filtro.
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'FormAling' =>
	 *                      Alineacion del formulario (1 al 5); 'FormCol' => Columnas del formulario
	 *                      (0 al 12); 'Placeholder' => Nombre a mostrar; 'PlaceholderIcon' => Icono
	 *                      a mostrar al lado izquierdo Nombre a mostrar; 'Name' => Nombre del
	 *                      input; 'Id' => Identificador del input; 'Value' => Valor del input;
	 *                      'Required' => Si input es requerido (1 al 2); 'selectProperties' =>
	 *                      Permite agregar propiedades dentro del elemento; 'arrData' => Datos
	 *                      recibidos; 'BASE' => Ruta base del sistema; 'DataInfo' => Informacion a
	 *                      mostrar debajo de un input; 'dataPops' => Popup con info.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formSelectFilter([
	 *     'FormAling'        => 1,
	 *     'FormCol'          => 8,
	 *     'Placeholder'      => 'Nombre',
	 *     'PlaceholderIcon'  => 'ri-database-2-line',
	 *     'Name'             => 'Nombre',
	 *     'Id'               => 'Identificador',
	 *     'Value'            => 'asd',
	 *     'Required'         => 2,
	 *     'selectProperties' => '',
	 *     'arrData'          => '',
	 *     'BASE'             => 'http://google.cl',
	 *     'DataInfo'         => 'Lorem ipsum dolor',
	 *     'dataPops'         => [],
	 * ]);
	 * ```
	 */
	public function formSelectFilter($Options){

		/**********************  Definiciones   **********************/
		$placeholder      = $Options['Placeholder'];
		$PlaceholderIcon  = $Options['PlaceholderIcon'] ?? '';
		$name             = $Options['Name'];
		$arrData          = $Options['arrData'];
		$FormAling        = $Options['FormAling'] ?? 1;
		$FormCol          = $Options['FormCol'] ?? 8;
		$identificador    = $Options['Id'] ?? $name;
		$value            = $Options['Value'] ?? '';
		$required         = $Options['Required'] ?? 1;
		$selectProperties = $Options['selectProperties'] ?? '';
		$BASE             = $Options['BASE'];
		$DataInfo         = $Options['DataInfo'] ?? '';
		$dataPops         = $Options['dataPops'] ?? '';

		//Definir opciones válidas
		$validOptions = [
			'required'  => range(1, 2),
			'FormAling' => range(1, 5),
			'FormCol'   => range(0, 12)
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $required,  'name' => 'required',  'label' => '$required'],
			['value' => $FormAling, 'name' => 'FormAling', 'label' => '$FormAling'],
			['value' => $FormCol,   'name' => 'FormCol',   'label' => '$FormCol']
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 1);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

            /************************************/
			//Verifico
			$nameID            = (strpos($identificador, '[]') !== false) ? str_replace('[]', '', $identificador) . '_' . uniqid() : $identificador;
			$selectProperties .= ($required === 2) ? 'required="required"' : '';
			$dataInfo          = (!empty($DataInfo)&&$DataInfo!='') ? '<p class="formHelp text-muted">'.$this->_scapeValue($DataInfo).'</p>' : '';
			$dataPopover = $this->_buildDataPopover($dataPops);

			/************************************/
			//generacion del input
			$input = $this->_selectInputGen($FormAling, $FormCol, $placeholder, $PlaceholderIcon, $name, $nameID, $value, $selectProperties, $arrData, 'select2_Main', $dataInfo, $dataPopover);

			//validacion si es requerido
			$input .= ($required === 2) ? '<style>#div_'.$nameID.' .select2-container .select2-selection--single {background:url('.$BASE.'/img/required.png) no-repeat 5px center !important;background-color: #fff !important;}</style>' : '';

			/************************************/
			//Imprimir dato
			echo $input;

		}else{

			/************************************/
			//Imprimir dato
			echo $alerts;

		}

	}

	/******************************************************************************/
	/**
	 * Permite crear un input tipo select en base a datos de la base de datos, agrupando los
	 * valores.
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'FormAling' =>
	 *                      Alineacion del formulario (1 al 5); 'FormCol' => Columnas del formulario
	 *                      (0 al 12); 'Placeholder' => Nombre a mostrar; 'PlaceholderIcon' => Icono
	 *                      a mostrar al lado izquierdo Nombre a mostrar; 'Name' => Nombre del
	 *                      input; 'Id' => Identificador del input; 'Value' => Valor del input;
	 *                      'Required' => Si input es requerido (1 al 2); 'arrData' => Datos
	 *                      recibidos; 'DataInfo' => Informacion a mostrar debajo de un input;
	 *                      'dataPops' => Popup con info.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formSelectGroup([
	 *     'FormAling'       => 1,
	 *     'FormCol'         => 8,
	 *     'Placeholder'     => 'Nombre',
	 *     'PlaceholderIcon' => 'ri-database-2-line',
	 *     'Name'            => 'Nombre',
	 *     'Id'              => 'Identificador',
	 *     'Value'           => 'asd',
	 *     'Required'        => 2,
	 *     'arrData'         => '',
	 *     'DataInfo'        => 'Lorem ipsum dolor',
	 *     'dataPops'        => [],
	 * ]);
	 * ```
	 */
	public function formSelectGroup($Options){

		/**********************  Definiciones   **********************/
		$placeholder      = $Options['Placeholder'];
		$PlaceholderIcon  = $Options['PlaceholderIcon'] ?? '';
		$name             = $Options['Name'];
		$arrData          = $Options['arrData'];
		$FormAling        = $Options['FormAling'] ?? 1;
		$FormCol          = $Options['FormCol'] ?? 8;
		$identificador    = $Options['Id'] ?? $name;
		$value            = $Options['Value'] ?? '';
		$required         = $Options['Required'] ?? 1;
		$DataInfo         = $Options['DataInfo'] ?? '';
		$dataPops         = $Options['dataPops'] ?? '';

		//Definir opciones válidas
		$validOptions = [
			'required'  => range(1, 2),
			'FormAling' => range(1, 5),
			'FormCol'   => range(0, 12)
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $required,  'name' => 'required',  'label' => '$required'],
			['value' => $FormAling, 'name' => 'FormAling', 'label' => '$FormAling'],
			['value' => $FormCol,   'name' => 'FormCol',   'label' => '$FormCol']
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 1);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

            /************************************/
			//Verifico
			$nameID           = (strpos($identificador, '[]') !== false) ? str_replace('[]', '', $identificador) . '_' . uniqid() : $identificador;
			$selectProperties = ($required === 2) ? 'required="required"' : '';
			$dataInfo         = (!empty($DataInfo)&&$DataInfo!='') ? '<p class="formHelp text-muted">'.$this->_scapeValue($DataInfo).'</p>' : '';
			$dataPopover = $this->_buildDataPopover($dataPops);

			/************************************/
			//Imprimir dato
			echo $this->_selectInputGroupGen($FormAling, $FormCol, $placeholder, $PlaceholderIcon, $name, $nameID, $value, $selectProperties, $arrData, '', $dataInfo, $dataPopover);

		}else{

			/************************************/
			//Imprimir dato
			echo $alerts;

		}

	}

	/******************************************************************************/
	/**
	 * Permite crear un input tipo select en base a datos de la base de datos, agrupando los
	 * valores, con filtro.
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'FormAling' =>
	 *                      Alineacion del formulario (1 al 5); 'FormCol' => Columnas del formulario
	 *                      (0 al 12); 'Placeholder' => Nombre a mostrar; 'PlaceholderIcon' => Icono
	 *                      a mostrar al lado izquierdo Nombre a mostrar; 'Name' => Nombre del
	 *                      input; 'Id' => Identificador del input; 'Value' => Valor del input;
	 *                      'Required' => Si input es requerido (1 al 2); 'arrData' => Datos
	 *                      recibidos; 'BASE' => Ruta base del sistema; 'DataInfo' => Informacion a
	 *                      mostrar debajo de un input; 'dataPops' => Popup con info.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formSelectGroupFilter([
	 *     'FormAling'       => 1,
	 *     'FormCol'         => 8,
	 *     'Placeholder'     => 'Nombre',
	 *     'PlaceholderIcon' => 'ri-database-2-line',
	 *     'Name'            => 'Nombre',
	 *     'Id'              => 'Identificador',
	 *     'Value'           => 'asd',
	 *     'Required'        => 2,
	 *     'arrData'         => '',
	 *     'BASE'            => 'http://google.cl',
	 *     'DataInfo'        => 'Lorem ipsum dolor',
	 *     'dataPops'        => [],
	 * ]);
	 * ```
	 */
	public function formSelectGroupFilter($Options){

		/**********************  Definiciones   **********************/
		$placeholder      = $Options['Placeholder'];
		$PlaceholderIcon  = $Options['PlaceholderIcon'] ?? '';
		$name             = $Options['Name'];
		$arrData          = $Options['arrData'];
		$FormAling        = $Options['FormAling'] ?? 1;
		$FormCol          = $Options['FormCol'] ?? 8;
		$identificador    = $Options['Id'] ?? $name;
		$value            = $Options['Value'] ?? '';
		$required         = $Options['Required'] ?? 1;
		$BASE             = $Options['BASE'];
		$DataInfo         = $Options['DataInfo'] ?? '';
		$dataPops         = $Options['dataPops'] ?? '';

		//Definir opciones válidas
		$validOptions = [
			'required'  => range(1, 2),
			'FormAling' => range(1, 5),
			'FormCol'   => range(0, 12)
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $required,  'name' => 'required',  'label' => '$required'],
			['value' => $FormAling, 'name' => 'FormAling', 'label' => '$FormAling'],
			['value' => $FormCol,   'name' => 'FormCol',   'label' => '$FormCol']
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 1);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

            /************************************/
			//Verifico
			$nameID           = (strpos($identificador, '[]') !== false) ? str_replace('[]', '', $identificador) . '_' . uniqid() : $identificador;
			$selectProperties = ($required === 2) ? 'required="required"' : '';
			$dataInfo         = (!empty($DataInfo)&&$DataInfo!='') ? '<p class="formHelp text-muted">'.$this->_scapeValue($DataInfo).'</p>' : '';
			$dataPopover = $this->_buildDataPopover($dataPops);

			/************************************/
			//generacion del input
			$input = $this->_selectInputGroupGen($FormAling, $FormCol, $placeholder, $PlaceholderIcon, $name, $nameID, $value, $selectProperties, $arrData, 'select2_Main', $dataInfo, $dataPopover);

			//validacion si es requerido
			$input .= ($required === 2) ? '<style>#div_'.$nameID.' .select2-container .select2-selection--single {background:url('.$BASE.'/img/required.png) no-repeat 5px center !important;background-color: #fff !important;}</style>' : '';

			/************************************/
			//Imprimir dato
			echo $input;

		}else{

			/************************************/
			//Imprimir dato
			echo $alerts;

		}

	}

	/******************************************************************************/
	/**
	 * Permite crear un input tipo select en base a datos de la base de datos, con la opcion de
	 * seleccionar varios elementos.
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'FormAling' =>
	 *                      Alineacion del formulario (1 al 5); 'FormCol' => Columnas del formulario
	 *                      (0 al 12); 'Placeholder' => Nombre a mostrar; 'PlaceholderIcon' => Icono
	 *                      a mostrar al lado izquierdo Nombre a mostrar; 'Name' => Nombre del
	 *                      input; 'Id' => Identificador del input; 'Value' => Valor del input;
	 *                      'Required' => Si input es requerido (1 al 2); 'arrData' => Datos
	 *                      recibidos; 'DataInfo' => Informacion a mostrar debajo de un input;
	 *                      'dataPops' => Popup con info.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formSelectMultiple([
	 *     'FormAling'       => 1,
	 *     'FormCol'         => 8,
	 *     'Placeholder'     => 'Nombre',
	 *     'PlaceholderIcon' => 'ri-database-2-line',
	 *     'Name'            => 'Nombre',
	 *     'Id'              => 'Identificador',
	 *     'Value'           => 'asd',
	 *     'Required'        => 2,
	 *     'arrData'         => '',
	 *     'DataInfo'        => 'Lorem ipsum dolor',
	 *     'dataPops'        => [],
	 * ]);
	 * ```
	 */
	public function formSelectMultiple($Options){

		/**********************  Definiciones   **********************/
		$placeholder      = $Options['Placeholder'];
		$PlaceholderIcon  = $Options['PlaceholderIcon'] ?? '';
		$name             = $Options['Name'];
		$arrData          = $Options['arrData'];
		$FormAling        = $Options['FormAling'] ?? 1;
		$FormCol          = $Options['FormCol'] ?? 8;
		$identificador    = $Options['Id'] ?? $name;
		$value            = $Options['Value'] ?? '';
		$required         = $Options['Required'] ?? 1;
		$DataInfo         = $Options['DataInfo'] ?? '';
		$dataPops         = $Options['dataPops'] ?? '';

		//Definir opciones válidas
		$validOptions = [
			'required'  => range(1, 2),
			'FormAling' => range(1, 5),
			'FormCol'   => range(0, 12)
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $required,  'name' => 'required',  'label' => '$required'],
			['value' => $FormAling, 'name' => 'FormAling', 'label' => '$FormAling'],
			['value' => $FormCol,   'name' => 'FormCol',   'label' => '$FormCol']
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 1);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

            /************************************/
			//Verifico
			$nameID           = (strpos($identificador, '[]') !== false) ? str_replace('[]', '', $identificador) . '_' . uniqid() : $identificador;
			$selectProperties = 'multiple="multiple"' . (($required === 2) ? ' required="required"' : ''); // Configura propiedades para select multiple de forma compacta
			$dataInfo         = (!empty($DataInfo)&&$DataInfo!='') ? '<p class="formHelp text-muted">'.$this->_scapeValue($DataInfo).'</p>' : '';
			$dataPopover = $this->_buildDataPopover($dataPops);

			/************************************/
			//generacion del input
			$input = $this->_selectInputGen($FormAling, $FormCol, $placeholder, $PlaceholderIcon, $name, $nameID, $value, $selectProperties, $arrData, '', $dataInfo, $dataPopover);
			//ejecuto script
			$input .= '
			<script>
				$(document).ready(function() {
					$("#'.$this->_escapeJs($nameID).'").select2();
				});
			</script>';

			/************************************/
			//Imprimir dato
			echo $input;

		}else{

			/************************************/
			//Imprimir dato
			echo $alerts;

		}

	}

	/******************************************************************************/
	/**
	 * Permite crear un input tipo select en base a datos de la base de datos, con la opcion de
	 * seleccionar varios elementos, con filtros.
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'FormAling' =>
	 *                      Alineacion del formulario (1 al 5); 'FormCol' => Columnas del formulario
	 *                      (0 al 12); 'Placeholder' => Nombre a mostrar; 'PlaceholderIcon' => Icono
	 *                      a mostrar al lado izquierdo Nombre a mostrar; 'Name' => Nombre del
	 *                      input; 'Id' => Identificador del input; 'Value' => Valor del input;
	 *                      'Required' => Si input es requerido (1 al 2); 'arrData' => Datos
	 *                      recibidos; 'DataInfo' => Informacion a mostrar debajo de un input;
	 *                      'dataPops' => Popup con info.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formSelectMultipleGroup([
	 *     'FormAling'       => 1,
	 *     'FormCol'         => 8,
	 *     'Placeholder'     => 'Nombre',
	 *     'PlaceholderIcon' => 'ri-database-2-line',
	 *     'Name'            => 'Nombre',
	 *     'Id'              => 'Identificador',
	 *     'Value'           => 'asd',
	 *     'Required'        => 2,
	 *     'arrData'         => '',
	 *     'DataInfo'        => 'Lorem ipsum dolor',
	 *     'dataPops'        => [],
	 * ]);
	 * ```
	 */
	public function formSelectMultipleGroup($Options){

		/**********************  Definiciones   **********************/
		$placeholder      = $Options['Placeholder'];
		$PlaceholderIcon  = $Options['PlaceholderIcon'] ?? '';
		$name             = $Options['Name'];
		$arrData          = $Options['arrData'];
		$FormAling        = $Options['FormAling'] ?? 1;
		$FormCol          = $Options['FormCol'] ?? 8;
		$identificador    = $Options['Id'] ?? $name;
		$value            = $Options['Value'] ?? '';
		$required         = $Options['Required'] ?? 1;
		$DataInfo         = $Options['DataInfo'] ?? '';
		$dataPops         = $Options['dataPops'] ?? '';

		//Definir opciones válidas
		$validOptions = [
			'required'  => range(1, 2),
			'FormAling' => range(1, 5),
			'FormCol'   => range(0, 12)
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $required,  'name' => 'required',  'label' => '$required'],
			['value' => $FormAling, 'name' => 'FormAling', 'label' => '$FormAling'],
			['value' => $FormCol,   'name' => 'FormCol',   'label' => '$FormCol']
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 1);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

            /************************************/
			//Verifico
			$nameID           = (strpos($identificador, '[]') !== false) ? str_replace('[]', '', $identificador) . '_' . uniqid() : $identificador;
			$selectProperties = 'multiple="multiple"' . (($required === 2) ? ' required="required"' : ''); // Configura propiedades para select multiple de forma compacta
			$dataInfo         = (!empty($DataInfo)&&$DataInfo!='') ? '<p class="formHelp text-muted">'.$this->_scapeValue($DataInfo).'</p>' : '';
			$dataPopover = $this->_buildDataPopover($dataPops);

			/************************************/
			//generacion del input
			$input = $this->_selectInputGroupGen($FormAling, $FormCol, $placeholder, $PlaceholderIcon, $name, $nameID, $value, $selectProperties, $arrData, '', $dataInfo, $dataPopover);
			//ejecuto script
			$input .= '
			<script>
				$(document).ready(function() {
					$("#'.$this->_escapeJs($nameID).'").select2();
				});
			</script>';

			/************************************/
			//Imprimir dato
			echo $input;

		}else{

			/************************************/
			//Imprimir dato
			echo $alerts;

		}

	}

	/******************************************************************************/
	/**
	 * Permite crear un input tipo select en base a datos de la base de datos, dependiente uno de
	 * otro.
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'FormAling1' =>
	 *                      Alineacion del formulario (1 al 5); 'FormCol1' => Columnas del
	 *                      formulario (0 al 12); 'Placeholder1' => Nombre a mostrar;
	 *                      'PlaceholderIcon1' => Icono a mostrar al lado izquierdo Nombre a
	 *                      mostrar; 'Name1' => Nombre del input; 'Id1' => Identificador del input;
	 *                      'Value1' => Valor del input; 'Required1' => Si input es requerido (1 al
	 *                      2); 'arrData1' => Datos recibidos; 'dataPops1' => Popup con info;
	 *                      'FormAling2' => Alineacion del formulario (1 al 5); 'FormCol2' =>
	 *                      Columnas del formulario (0 al 12); 'Placeholder2' => Nombre a mostrar;
	 *                      'PlaceholderIcon2' => Icono a mostrar al lado izquierdo Nombre a
	 *                      mostrar; 'Name2' => Nombre del input; 'Id2' => Identificador del input;
	 *                      'Value2' => Valor del input; 'Required2' => Si input es requerido (1 al
	 *                      2); 'arrData2' => Datos recibidos; 'dataPops2' => Popup con info.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formSelectDepend([
	 *     'FormAling1'       => 1,
	 *     'FormCol1'         => 8,
	 *     'Placeholder1'     => 'Nombre',
	 *     'PlaceholderIcon1' => 'ri-database-2-line',
	 *     'Name1'            => 'Nombre',
	 *     'Id1'              => 'Identificador',
	 *     'Value1'           => 'asd',
	 *     'Required1'        => 2,
	 *     'arrData1'         => '',
	 *     'dataPops1'        => [],
	 *     'FormAling2'       => 1,
	 *     'FormCol2'         => 8,
	 *     'Placeholder2'     => 'Nombre',
	 *     'PlaceholderIcon2' => 'ri-database-2-line',
	 *     'Name2'            => 'Nombre',
	 *     'Id2'              => 'Identificador',
	 *     'Value2'           => 'asd',
	 *     'Required2'        => 2,
	 *     'arrData2'         => '',
	 *     'dataPops2'        => [],
	 * ]);
	 * ```
	 */
	public function formSelectDepend($Options){

		/**********************  Definiciones   **********************/
		$placeholder1      = $Options['Placeholder1'];
		$placeholderIcon1  = $Options['PlaceholderIcon1'] ?? '';
		$name1             = $Options['Name1'];
		$arrData1          = $Options['arrData1'];
		$FormAling1        = $Options['FormAling1'] ?? 1;
		$FormCol1          = $Options['FormCol1'] ?? 8;
		$identificador1    = $Options['Id1'] ?? $name1;
		$value1            = $Options['Value1'] ?? '';
		$required1         = $Options['Required1'] ?? 1;
		$dataPops1         = $Options['dataPops'] ?? '';
		$placeholder2      = $Options['Placeholder2'];
		$placeholderIcon2  = $Options['PlaceholderIcon2'] ?? '';
		$name2             = $Options['Name2'];
		$arrData2          = $Options['arrData2'];
		$FormAling2        = $Options['FormAling2'] ?? 1;
		$FormCol2          = $Options['FormCol2'] ?? 8;
		$identificador2    = $Options['Id2'] ?? $name2;
		$value2            = $Options['Value2'] ?? '';
		$required2         = $Options['Required2'] ?? 1;
		$dataPops2         = $Options['dataPops'] ?? '';

		//Definir opciones válidas
		$validOptions = [
			'required1'  => range(1, 2),
			'required2'  => range(1, 2),
			'FormAling1' => range(1, 5),
			'FormAling2' => range(1, 5),
			'FormCol1'   => range(0, 12),
			'FormCol2'   => range(0, 12)
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $required1,  'name' => 'required1',  'label' => '$required1',   'placeholder' => $placeholder1],
			['value' => $required2,  'name' => 'required2',  'label' => '$required2',   'placeholder' => $placeholder2],
			['value' => $FormAling1, 'name' => 'FormAling1', 'label' => '$FormAling1',  'placeholder' => $placeholder1],
			['value' => $FormAling2, 'name' => 'FormAling2', 'label' => '$FormAling2',  'placeholder' => $placeholder2],
			['value' => $FormCol1,   'name' => 'FormCol1',   'label' => '$FormCol1',    'placeholder' => $placeholder1],
			['value' => $FormCol2,   'name' => 'FormCol2',   'label' => '$FormCol2',    'placeholder' => $placeholder2]
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

			/************************************/
			//Verifico
			$nameID1           = (strpos($identificador1, '[]') !== false) ? str_replace('[]', '', $identificador1) . '_' . uniqid() : $identificador1;
			$nameID2           = (strpos($identificador2, '[]') !== false) ? str_replace('[]', '', $identificador2) . '_' . uniqid() : $identificador2;
			$selectProperties1 = ($required1 === 2) ? 'required="required"' : '';
			$selectProperties2 = ($required2 === 2) ? 'required="required"' : '';
			$dataPopover1      = (!empty($dataPops1)&&$dataPops1!='') ? ' <button type="button" class="btn btn-sm btn-outline-'.$dataPops1['style'].'" data-popover data-extraclass="popover-'.$dataPops1['style'].'" data-title="'.$dataPops1['title'].'" data-content="'.$dataPops1['content'].'" data-placement="'.$dataPops1['position'].'"><i class="'.$dataPops1['icon'].'"></i></button>' : '';
			$dataPopover2      = (!empty($dataPops2)&&$dataPops2!='') ? ' <button type="button" class="btn btn-sm btn-outline-'.$dataPops2['style'].'" data-popover data-extraclass="popover-'.$dataPops2['style'].'" data-title="'.$dataPops2['title'].'" data-content="'.$dataPops2['content'].'" data-placement="'.$dataPops2['position'].'"><i class="'.$dataPops2['icon'].'"></i></button>' : '';

			/************************************/
			//generacion del input
			$input  = $this->_selectInputGen($FormAling1, $FormCol1, $placeholder1, $placeholderIcon1, $name1, $nameID1, $value1, $selectProperties1, $arrData1, '', '', $dataPopover1);
			$input .= $this->_selectInputEmpty($FormAling2, $FormCol2, $placeholder2, $placeholderIcon2, $name2, $nameID2, $selectProperties2, $dataPopover2);
			$input .= $this->_selectInputScript($arrData2, $value2, $nameID1, $nameID2, $FormAling2);

			/************************************/
			//Imprimir dato
			echo $input;

		}else{

			/************************************/
			//Imprimir dato
			echo $alerts;

		}

	}

	/******************************************************************************/
	/**
	 * Permite crear un input tipo select en base a datos de la base de datos, dependiente uno de
	 * otro, con filtros.
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'FormAling1' =>
	 *                      Alineacion del formulario (1 al 5); 'FormCol1' => Columnas del
	 *                      formulario (0 al 12); 'Placeholder1' => Nombre a mostrar;
	 *                      'PlaceholderIcon1' => Icono a mostrar al lado izquierdo Nombre a
	 *                      mostrar; 'Name1' => Nombre del input; 'Id1' => Identificador del input;
	 *                      'Value1' => Valor del input; 'Required1' => Si input es requerido (1 al
	 *                      2); 'arrData1' => Datos recibidos; 'dataPops1' => Popup con info;
	 *                      'FormAling2' => Alineacion del formulario (1 al 5); 'FormCol2' =>
	 *                      Columnas del formulario (0 al 12); 'Placeholder2' => Nombre a mostrar;
	 *                      'PlaceholderIcon2' => Icono a mostrar al lado izquierdo Nombre a
	 *                      mostrar; 'Name2' => Nombre del input; 'Id2' => Identificador del input;
	 *                      'Value2' => Valor del input; 'Required2' => Si input es requerido (1 al
	 *                      2); 'arrData2' => Datos recibidos; 'dataPops2' => Popup con info; 'BASE'
	 *                      => Ruta base del sistema.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formSelectDependFilter([
	 *     'FormAling1'       => 1,
	 *     'FormCol1'         => 8,
	 *     'Placeholder1'     => 'Nombre',
	 *     'PlaceholderIcon1' => 'ri-database-2-line',
	 *     'Name1'            => 'Nombre',
	 *     'Id1'              => 'Identificador',
	 *     'Value1'           => 'asd',
	 *     'Required1'        => 2,
	 *     'arrData1'         => '',
	 *     'dataPops1'        => [],
	 *     'FormAling2'       => 1,
	 *     'FormCol2'         => 8,
	 *     'Placeholder2'     => 'Nombre',
	 *     'PlaceholderIcon2' => 'ri-database-2-line',
	 *     'Name2'            => 'Nombre',
	 *     'Id2'              => 'Identificador',
	 *     'Value2'           => 'asd',
	 *     'Required2'        => 2,
	 *     'arrData2'         => '',
	 *     'dataPops2'        => [],
	 *     'BASE'             => 'http://google.cl',
	 * ]);
	 * ```
	 */
	public function formSelectDependFilter($Options){

		/**********************  Definiciones   **********************/
		$placeholder1      = $Options['Placeholder1'];
		$placeholderIcon1  = $Options['PlaceholderIcon1'] ?? '';
		$name1             = $Options['Name1'];
		$arrData1          = $Options['arrData1'];
		$FormAling1        = $Options['FormAling1'] ?? 1;
		$FormCol1          = $Options['FormCol1'] ?? 8;
		$identificador1    = $Options['Id1'] ?? $name1;
		$value1            = $Options['Value1'] ?? '';
		$required1         = $Options['Required1'] ?? 1;
		$dataPops1         = $Options['dataPops1'] ?? '';
		$placeholder2      = $Options['Placeholder2'];
		$placeholderIcon2  = $Options['PlaceholderIcon2'] ?? '';
		$name2             = $Options['Name2'];
		$arrData2          = $Options['arrData2'];
		$FormAling2        = $Options['FormAling2'] ?? 1;
		$FormCol2          = $Options['FormCol2'] ?? 8;
		$identificador2    = $Options['Id2'] ?? $name2;
		$value2            = $Options['Value2'] ?? '';
		$required2         = $Options['Required2'] ?? 1;
		$dataPops2         = $Options['dataPops2'] ?? '';
		$BASE              = $Options['BASE'];

		//Definir opciones válidas
		$validOptions = [
			'required1'  => range(1, 2),
			'required2'  => range(1, 2),
			'FormAling1' => range(1, 5),
			'FormAling2' => range(1, 5),
			'FormCol1'   => range(0, 12),
			'FormCol2'   => range(0, 12)
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $required1,  'name' => 'required1',  'label' => '$required1',   'placeholder' => $placeholder1],
			['value' => $required2,  'name' => 'required2',  'label' => '$required2',   'placeholder' => $placeholder2],
			['value' => $FormAling1, 'name' => 'FormAling1', 'label' => '$FormAling1',  'placeholder' => $placeholder1],
			['value' => $FormAling2, 'name' => 'FormAling2', 'label' => '$FormAling2',  'placeholder' => $placeholder2],
			['value' => $FormCol1,   'name' => 'FormCol1',   'label' => '$FormCol1',    'placeholder' => $placeholder1],
			['value' => $FormCol2,   'name' => 'FormCol2',   'label' => '$FormCol2',    'placeholder' => $placeholder2]
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

			/************************************/
			//Verifico
			$nameID1           = (strpos($identificador1, '[]') !== false) ? str_replace('[]', '', $identificador1) . '_' . uniqid() : $identificador1;
			$nameID2           = (strpos($identificador2, '[]') !== false) ? str_replace('[]', '', $identificador2) . '_' . uniqid() : $identificador2;
			$selectProperties1 = ($required1 === 2) ? 'required="required"' : '';
			$selectProperties2 = ($required2 === 2) ? 'required="required"' : '';
			$dataPopover1      = (!empty($dataPops1)&&$dataPops1!='') ? ' <button type="button" class="btn btn-sm btn-outline-'.$dataPops1['style'].'" data-popover data-extraclass="popover-'.$dataPops1['style'].'" data-title="'.$dataPops1['title'].'" data-content="'.$dataPops1['content'].'" data-placement="'.$dataPops1['position'].'"><i class="'.$dataPops1['icon'].'"></i></button>' : '';
			$dataPopover2      = (!empty($dataPops2)&&$dataPops2!='') ? ' <button type="button" class="btn btn-sm btn-outline-'.$dataPops2['style'].'" data-popover data-extraclass="popover-'.$dataPops2['style'].'" data-title="'.$dataPops2['title'].'" data-content="'.$dataPops2['content'].'" data-placement="'.$dataPops2['position'].'"><i class="'.$dataPops2['icon'].'"></i></button>' : '';

			/************************************/
			//generacion del input
			$input  = $this->_selectInputGen($FormAling1, $FormCol1, $placeholder1, $placeholderIcon1, $name1, $nameID1, $value1, $selectProperties1, $arrData1, 'select2_Main', '', $dataPopover1);
			$input .= $this->_selectInputEmpty($FormAling2, $FormCol2, $placeholder2, $placeholderIcon2, $name2, $nameID2, $selectProperties2, $dataPopover2);
			$input .= $this->_selectInputScript($arrData2, $value2, $nameID1, $nameID2, $FormAling2);

			//validacion si es requerido
			$input .= ($required1 === 2) ? '<style>#div_'.$nameID1.' .select2-container .select2-selection--single {background:url('.$BASE.'/img/required.png) no-repeat 5px center !important;background-color: #fff !important;}</style>' : '';
			$input .= ($required2 === 2) ? '<style>#div_'.$nameID2.' .select2-container .select2-selection--single {background:url('.$BASE.'/img/required.png) no-repeat 5px center !important;background-color: #fff !important;}</style>' : '';

			/************************************/
			//Imprimir dato
			echo $input;

		}else{

			/************************************/
			//Imprimir dato
			echo $alerts;

		}

	}

	/******************************************************************************/
	/**
	 * Permite crear un input select que trae los paises.
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'FormAling' =>
	 *                      Alineacion del formulario (1 al 5); 'FormCol' => Columnas del formulario
	 *                      (0 al 12); 'Placeholder' => Nombre a mostrar; 'PlaceholderIcon' => Icono
	 *                      a mostrar al lado izquierdo Nombre a mostrar; 'Name' => Nombre del
	 *                      input; 'Id' => Identificador del input; 'Value' => Valor del input;
	 *                      'Required' => Si input es requerido (1 al 2); 'BASE' => Ruta base del
	 *                      sistema; 'DataInfo' => Informacion a mostrar debajo de un input;
	 *                      'dataPops' => Popup con info.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formSelectCountry([
	 *     'FormAling'       => 1,
	 *     'FormCol'         => 8,
	 *     'Placeholder'     => 'Nombre',
	 *     'PlaceholderIcon' => 'ri-database-2-line',
	 *     'Name'            => 'Nombre',
	 *     'Id'              => 'Identificador',
	 *     'Value'           => 'asd',
	 *     'Required'        => 2,
	 *     'BASE'            => 'http://google.cl',
	 *     'DataInfo'        => 'Lorem ipsum dolor',
	 *     'dataPops'        => [],
	 * ]);
	 * ```
	 */
	public function formSelectCountry($Options){

		/**********************  Definiciones   **********************/
		$placeholder      = $Options['Placeholder'];
		$PlaceholderIcon  = $Options['PlaceholderIcon'] ?? '';
		$name             = $Options['Name'];
		$FormAling        = $Options['FormAling'] ?? 1;
		$FormCol          = $Options['FormCol'] ?? 8;
		$identificador    = $Options['Id'] ?? $name;
		$value            = $Options['Value'] ?? '';
		$required         = $Options['Required'] ?? 1;
		$BASE             = $Options['BASE'];
		$DataInfo         = $Options['DataInfo'] ?? '';
		$dataPops         = $Options['dataPops'] ?? '';

		//Definir opciones válidas
		$validOptions = [
			'required'  => range(1, 2),
			'FormAling' => range(1, 5),
			'FormCol'   => range(0, 12)
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $required,  'name' => 'required',  'label' => '$required'],
			['value' => $FormAling, 'name' => 'FormAling', 'label' => '$FormAling'],
			['value' => $FormCol,   'name' => 'FormCol',   'label' => '$FormCol']
		];

		// Validaciones numéricas y enteras
		$fieldsToCheck = [
			['value' => $value, 'method' => 'validarNumero',  'label' => '$value', 'msg' => 'no es un numero'],
			['value' => $value, 'method' => 'validarEntero',  'label' => '$value', 'msg' => 'no es un numero entero'],
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 1);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		$dataReturn = $this->DataValidations->checkData('', $fieldsToCheck, $placeholder, 2);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

            /************************************/
			//Verifico
			$nameID          = (strpos($identificador, '[]') !== false) ? str_replace('[]', '', $identificador) . '_' . uniqid() : $identificador;
			$requerido       = ($required === 2) ? 'required="required"' : '';
			$placeholderIcon = (!empty($PlaceholderIcon)&&$PlaceholderIcon!='') ? "<i class='".$this->_scapeValue($PlaceholderIcon)."'></i> " : '';
			$dataInfo        = (!empty($DataInfo)&&$DataInfo!='') ? '<p class="formHelp text-muted">'.$this->_scapeValue($DataInfo).'</p>' : '';
			$dataPopover = $this->_buildDataPopover($dataPops);

			/************************************/
			//Se agregan los paises
			$arrData = [
				["value" => 1, "Nombre" => '🇦🇫 Afghanistan'],
				["value" => 2, "Nombre" => '🇦🇴 Angola'],
				["value" => 3, "Nombre" => '🇦🇱 Albania'],
				["value" => 4, "Nombre" => '🇦🇩 Andorra'],
				["value" => 5, "Nombre" => '🇦🇪 United Arab Emirates'],
				["value" => 6, "Nombre" => '🇦🇷 Argentina'],
				["value" => 7, "Nombre" => '🇦🇲 Armenia'],
				["value" => 8, "Nombre" => '🇦🇬 Antigua and Barbuda'],
				["value" => 9, "Nombre" => '🇦🇺 Australia'],
				["value" => 10, "Nombre" => '🇦🇹 Austria'],
				["value" => 11, "Nombre" => '🇦🇿 Azerbaijan'],
				["value" => 12, "Nombre" => '🇧🇮 Burundi'],
				["value" => 13, "Nombre" => '🇧🇪 Belgium'],
				["value" => 14, "Nombre" => '🇧🇯 Benin'],
				["value" => 15, "Nombre" => '🇧🇫 Burkina Faso'],
				["value" => 16, "Nombre" => '🇧🇩 Bangladesh'],
				["value" => 17, "Nombre" => '🇧🇬 Bulgaria'],
				["value" => 18, "Nombre" => '🇧🇭 Bahrain'],
				["value" => 19, "Nombre" => '🇧🇸 Bahamas'],
				["value" => 20, "Nombre" => '🇧🇦 Bosnia and Herzegovina'],
				["value" => 21, "Nombre" => '🇧🇾 Belarus'],
				["value" => 22, "Nombre" => '🇧🇿 Belize'],
				["value" => 23, "Nombre" => '🇧🇴 Bolivia'],
				["value" => 24, "Nombre" => '🇧🇷 Brazil'],
				["value" => 25, "Nombre" => '🇧🇧 Barbados'],
				["value" => 26, "Nombre" => '🇧🇳 Brunei'],
				["value" => 27, "Nombre" => '🇧🇹 Bhutan'],
				["value" => 28, "Nombre" => '🇧🇼 Botswana'],
				["value" => 29, "Nombre" => '🇨🇫 Central African Republic'],
				["value" => 30, "Nombre" => '🇨🇦 Canada'],
				["value" => 31, "Nombre" => '🇨🇭 Switzerland'],
				["value" => 32, "Nombre" => '🇨🇱 Chile'],
				["value" => 33, "Nombre" => '🇨🇳 China'],
				["value" => 34, "Nombre" => '🇨🇮 Ivory Coast'],
				["value" => 35, "Nombre" => '🇨🇲 Cameroon'],
				["value" => 36, "Nombre" => '🇨🇩 DR Congo'],
				["value" => 37, "Nombre" => '🇨🇬 Republic of the Congo'],
				["value" => 38, "Nombre" => '🇨🇴 Colombia'],
				["value" => 39, "Nombre" => '🇰🇲 Comoros'],
				["value" => 40, "Nombre" => '🇨🇻 Cape Verde'],
				["value" => 41, "Nombre" => '🇨🇷 Costa Rica'],
				["value" => 42, "Nombre" => '🇨🇺 Cuba'],
				["value" => 43, "Nombre" => '🇨🇾 Cyprus'],
				["value" => 44, "Nombre" => '🇨🇿 Czechia'],
				["value" => 45, "Nombre" => '🇩🇪 Germany'],
				["value" => 46, "Nombre" => '🇩🇯 Djibouti'],
				["value" => 47, "Nombre" => '🇩🇲 Dominica'],
				["value" => 48, "Nombre" => '🇩🇰 Denmark'],
				["value" => 49, "Nombre" => '🇩🇴 Dominican Republic'],
				["value" => 50, "Nombre" => '🇩🇿 Algeria'],
				["value" => 51, "Nombre" => '🇪🇨 Ecuador'],
				["value" => 52, "Nombre" => '🇪🇬 Egypt'],
				["value" => 53, "Nombre" => '🇪🇷 Eritrea'],
				["value" => 54, "Nombre" => '🇪🇸 Spain'],
				["value" => 55, "Nombre" => '🇪🇪 Estonia'],
				["value" => 56, "Nombre" => '🇪🇹 Ethiopia'],
				["value" => 57, "Nombre" => '🇫🇮 Finland'],
				["value" => 58, "Nombre" => '🇫🇯 Fiji'],
				["value" => 59, "Nombre" => '🇫🇷 France'],
				["value" => 60, "Nombre" => '🇫🇲 Micronesia'],
				["value" => 61, "Nombre" => '🇬🇦 Gabon'],
				["value" => 62, "Nombre" => '🇬🇧 United Kingdom'],
				["value" => 63, "Nombre" => '🇬🇪 Georgia'],
				["value" => 64, "Nombre" => '🇬🇭 Ghana'],
				["value" => 65, "Nombre" => '🇬🇳 Guinea'],
				["value" => 66, "Nombre" => '🇬🇲 Gambia'],
				["value" => 67, "Nombre" => '🇬🇼 Guinea-Bissau'],
				["value" => 68, "Nombre" => '🇬🇶 Equatorial Guinea'],
				["value" => 69, "Nombre" => '🇬🇷 Greece'],
				["value" => 70, "Nombre" => '🇬🇩 Grenada'],
				["value" => 71, "Nombre" => 'Guatemala 🇬🇹'],
				["value" => 72, "Nombre" => 'Guyana 🇬🇾'],
				["value" => 73, "Nombre" => 'Honduras 🇭🇳'],
				["value" => 74, "Nombre" => 'Croatia 🇭🇷'],
				["value" => 75, "Nombre" => 'Haiti 🇭🇹'],
				["value" => 76, "Nombre" => 'Hungary 🇭🇺'],
				["value" => 77, "Nombre" => 'Indonesia 🇮🇩'],
				["value" => 78, "Nombre" => 'India 🇮🇳'],
				["value" => 79, "Nombre" => 'Ireland 🇮🇪'],
				["value" => 80, "Nombre" => 'Iran 🇮🇷'],
				["value" => 81, "Nombre" => 'Iraq 🇮🇶'],
				["value" => 82, "Nombre" => 'Iceland 🇮🇸'],
				["value" => 83, "Nombre" => 'Israel 🇮🇱'],
				["value" => 84, "Nombre" => 'Italy 🇮🇹'],
				["value" => 85, "Nombre" => 'Jamaica 🇯🇲'],
				["value" => 86, "Nombre" => 'Jordan 🇯🇴'],
				["value" => 87, "Nombre" => 'Japan 🇯🇵'],
				["value" => 88, "Nombre" => 'Kazakhstan 🇰🇿'],
				["value" => 89, "Nombre" => 'Kenya 🇰🇪'],
				["value" => 90, "Nombre" => 'Kyrgyzstan 🇰🇬'],
				["value" => 91, "Nombre" => 'Cambodia 🇰🇭'],
				["value" => 92, "Nombre" => 'Kiribati 🇰🇮'],
				["value" => 93, "Nombre" => 'Saint Kitts and Nevis 🇰🇳'],
				["value" => 94, "Nombre" => 'South Korea 🇰🇷'],
				["value" => 95, "Nombre" => 'Kuwait 🇰🇼'],
				["value" => 96, "Nombre" => 'Laos 🇱🇦'],
				["value" => 97, "Nombre" => 'Lebanon 🇱🇧'],
				["value" => 98, "Nombre" => 'Liberia 🇱🇷'],
				["value" => 99, "Nombre" => 'Libya 🇱🇾'],
				["value" => 100, "Nombre" => 'Saint Lucia 🇱🇨'],
				["value" => 101, "Nombre" => 'Liechtenstein 🇱🇮'],
				["value" => 102, "Nombre" => 'Sri Lanka 🇱🇰'],
				["value" => 103, "Nombre" => 'Lesotho 🇱🇸'],
				["value" => 104, "Nombre" => 'Lithuania 🇱🇹'],
				["value" => 105, "Nombre" => 'Luxembourg 🇱🇺'],
				["value" => 106, "Nombre" => 'Latvia 🇱🇻'],
				["value" => 107, "Nombre" => 'Morocco 🇲🇦'],
				["value" => 108, "Nombre" => 'Monaco 🇲🇨'],
				["value" => 109, "Nombre" => 'Moldova 🇲🇩'],
				["value" => 110, "Nombre" => 'Madagascar 🇲🇬'],
				["value" => 111, "Nombre" => 'Maldives 🇲🇻'],
				["value" => 112, "Nombre" => 'Mexico 🇲🇽'],
				["value" => 113, "Nombre" => 'Marshall Islands 🇲🇭'],
				["value" => 114, "Nombre" => 'Macedonia 🇲🇰'],
				["value" => 115, "Nombre" => 'Mali 🇲🇱'],
				["value" => 116, "Nombre" => 'Malta 🇲🇹'],
				["value" => 117, "Nombre" => 'Myanmar 🇲🇲'],
				["value" => 118, "Nombre" => 'Montenegro 🇲🇪'],
				["value" => 119, "Nombre" => 'Mongolia 🇲🇳'],
				["value" => 120, "Nombre" => 'Mozambique 🇲🇿'],
				["value" => 121, "Nombre" => 'Mauritania 🇲🇷'],
				["value" => 122, "Nombre" => 'Mauritius 🇲🇺'],
				["value" => 123, "Nombre" => 'Malawi 🇲🇼'],
				["value" => 124, "Nombre" => 'Malaysia 🇲🇾'],
				["value" => 125, "Nombre" => 'Namibia 🇳🇦'],
				["value" => 126, "Nombre" => 'Niger 🇳🇪'],
				["value" => 127, "Nombre" => 'Nigeria 🇳🇬'],
				["value" => 128, "Nombre" => 'Nicaragua 🇳🇮'],
				["value" => 129, "Nombre" => 'Netherlands 🇳🇱'],
				["value" => 130, "Nombre" => 'Norway 🇳🇴'],
				["value" => 131, "Nombre" => 'Nepal 🇳🇵'],
				["value" => 132, "Nombre" => 'Nauru 🇳🇷'],
				["value" => 133, "Nombre" => 'New Zealand 🇳🇿'],
				["value" => 134, "Nombre" => 'Oman 🇴🇲'],
				["value" => 135, "Nombre" => 'Pakistan 🇵🇰'],
				["value" => 136, "Nombre" => 'Panama 🇵🇦'],
				["value" => 137, "Nombre" => 'Peru 🇵🇪'],
				["value" => 138, "Nombre" => 'Philippines 🇵🇭'],
				["value" => 139, "Nombre" => 'Palau 🇵🇼'],
				["value" => 140, "Nombre" => 'Papua New Guinea 🇵🇬'],
				["value" => 141, "Nombre" => 'Poland 🇵🇱'],
				["value" => 142, "Nombre" => 'North Korea 🇰🇵'],
				["value" => 143, "Nombre" => 'Portugal 🇵🇹'],
				["value" => 144, "Nombre" => 'Paraguay 🇵🇾'],
				["value" => 145, "Nombre" => 'Qatar 🇶🇦'],
				["value" => 146, "Nombre" => 'Romania 🇷🇴'],
				["value" => 147, "Nombre" => 'Russia 🇷🇺'],
				["value" => 148, "Nombre" => 'Rwanda 🇷🇼'],
				["value" => 149, "Nombre" => 'Saudi Arabia 🇸🇦'],
				["value" => 150, "Nombre" => 'Sudan 🇸🇩'],
				["value" => 151, "Nombre" => 'Senegal 🇸🇳'],
				["value" => 152, "Nombre" => 'Singapore 🇸🇬'],
				["value" => 153, "Nombre" => 'Solomon Islands 🇸🇧'],
				["value" => 154, "Nombre" => 'Sierra Leone 🇸🇱'],
				["value" => 155, "Nombre" => 'El Salvador 🇸🇻'],
				["value" => 156, "Nombre" => 'San Marino 🇸🇲'],
				["value" => 157, "Nombre" => 'Somalia 🇸🇴'],
				["value" => 158, "Nombre" => 'Serbia 🇷🇸'],
				["value" => 159, "Nombre" => 'South Sudan 🇸🇸'],
				["value" => 160, "Nombre" => 'São Tomé and Príncipe 🇸🇹'],
				["value" => 161, "Nombre" => 'Suriname 🇸🇷'],
				["value" => 162, "Nombre" => 'Slovakia 🇸🇰'],
				["value" => 163, "Nombre" => 'Slovenia 🇸🇮'],
				["value" => 164, "Nombre" => 'Sweden 🇸🇪'],
				["value" => 165, "Nombre" => 'Swaziland 🇸🇿'],
				["value" => 166, "Nombre" => 'Seychelles 🇸🇨'],
				["value" => 167, "Nombre" => 'Syria 🇸🇾'],
				["value" => 168, "Nombre" => 'Chad 🇹🇩'],
				["value" => 169, "Nombre" => 'Togo 🇹🇬'],
				["value" => 170, "Nombre" => 'Thailand 🇹🇭'],
				["value" => 171, "Nombre" => 'Tajikistan 🇹🇯'],
				["value" => 172, "Nombre" => 'Turkmenistan 🇹🇲'],
				["value" => 173, "Nombre" => 'Timor-Leste 🇹🇱'],
				["value" => 174, "Nombre" => 'Tonga 🇹🇴'],
				["value" => 175, "Nombre" => 'Trinidad and Tobago 🇹🇹'],
				["value" => 176, "Nombre" => 'Tunisia 🇹🇳'],
				["value" => 177, "Nombre" => 'Turkey 🇹🇷'],
				["value" => 178, "Nombre" => 'Tuvalu 🇹🇻'],
				["value" => 179, "Nombre" => 'Tanzania 🇹🇿'],
				["value" => 180, "Nombre" => 'Uganda 🇺🇬'],
				["value" => 181, "Nombre" => 'Ukraine 🇺🇦'],
				["value" => 182, "Nombre" => 'Uruguay 🇺🇾'],
				["value" => 183, "Nombre" => 'United States 🇺🇸'],
				["value" => 184, "Nombre" => 'Uzbekistan 🇺🇿'],
				["value" => 185, "Nombre" => 'Vatican City 🇻🇦'],
				["value" => 186, "Nombre" => 'Saint Vincent and the Grenadines 🇻🇨'],
				["value" => 187, "Nombre" => 'Venezuela 🇻🇪'],
				["value" => 188, "Nombre" => 'Vietnam 🇻🇳'],
				["value" => 189, "Nombre" => 'Vanuatu 🇻🇺'],
				["value" => 190, "Nombre" => 'Samoa 🇼🇸'],
				["value" => 191, "Nombre" => 'Yemen 🇾🇪'],
				["value" => 192, "Nombre" => 'South Africa 🇿🇦'],
				["value" => 193, "Nombre" => 'Zambia 🇿🇲'],
				["value" => 194, "Nombre" => 'Zimbabwe 🇿🇼']
			];

			/************************************/
			//generacion del input
			$formRoute = $this->_resolveFormRoute('formSelectCountry', $FormAling, $FormCol);
			/*******************************************/
			// Variables
			$selectedx = 'selected="selected"';
			$Options   = '';
			// Recorro
			foreach ($arrData as $select) {
				$isSelected = ($value == $select['value']) ? 'selected="selected"' : '';
				if ($isSelected){$selectedx = '';}
				$Options .= '<option value="'.$select['value'].'" '.$isSelected.'>'.$select['Nombre'].'</option>';
			}
			//Se generan opciones
			$FormOptions  = '<option value="" '.$selectedx.'>Seleccione una Opción</option>';
			$FormOptions .= $Options;
			//validacion si es requerido
			$dataRequire = ($required === 2) ? '<style>#div_'.$nameID.' .select2-container .select2-selection--single {background:url('.$BASE.'/img/required.png) no-repeat 5px center !important;background-color: #fff !important;}</style>' : '';

			/************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath($formRoute);
			$this->TemplateRender->assign('nameID',           $nameID);
			$this->TemplateRender->assign('placeholder',      $placeholder);
			$this->TemplateRender->assign('placeholderIcon',  $placeholderIcon);
			$this->TemplateRender->assign('FormCol',          $FormCol);
			$this->TemplateRender->assign('name',             $name);
			$this->TemplateRender->assign('requerido',        $requerido);
			$this->TemplateRender->assign('FormOptions',      $FormOptions);
			$this->TemplateRender->assign('dataRequire',      $dataRequire);
			$this->TemplateRender->assign('dataInfo',         $dataInfo);
			$this->TemplateRender->assign('dataPopover',      $dataPopover);

			/************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/************************************/
			//Imprimir dato
			echo $alerts;

		}

	}

	/******************************************************************************/
	/**
	 * Permite crear un input tipo select que contiene numeros enteros secuenciales.
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'FormAling' =>
	 *                      Alineacion del formulario (1 al 5); 'FormCol' => Columnas del formulario
	 *                      (0 al 12); 'Placeholder' => Nombre a mostrar; 'PlaceholderIcon' => Icono
	 *                      a mostrar al lado izquierdo Nombre a mostrar; 'Name' => Nombre del
	 *                      input; 'Id' => Identificador del input; 'Value' => Valor del input;
	 *                      'Required' => Si input es requerido (1 al 2); 'ValorInicio' => Valor
	 *                      Inicio; 'ValorFin' => Valor Fin; 'DataInfo' => Informacion a mostrar
	 *                      debajo de un input; 'dataPops' => Popup con info.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formSelectnAuto([
	 *     'FormAling'       => 1,
	 *     'FormCol'         => 8,
	 *     'Placeholder'     => 'Nombre',
	 *     'PlaceholderIcon' => 'ri-database-2-line',
	 *     'Name'            => 'Nombre',
	 *     'Id'              => 'Identificador',
	 *     'Value'           => 'asd',
	 *     'Required'        => 2,
	 *     'ValorInicio'     => '',
	 *     'ValorFin'        => '',
	 *     'DataInfo'        => 'Lorem ipsum dolor',
	 *     'dataPops'        => [],
	 * ]);
	 * ```
	 */
	public function formSelectnAuto($Options){

		/**********************  Definiciones   **********************/
		$placeholder      = $Options['Placeholder'];
		$PlaceholderIcon  = $Options['PlaceholderIcon'] ?? '';
		$name             = $Options['Name'];
		$valor_ini        = $Options['ValorInicio'];
		$valor_fin        = $Options['ValorFin'];
		$FormAling        = $Options['FormAling'] ?? 1;
		$FormCol          = $Options['FormCol'] ?? 8;
		$identificador    = $Options['Id'] ?? $name;
		$value            = $Options['Value'] ?? '';
		$required         = $Options['Required'] ?? 1;
		$DataInfo         = $Options['DataInfo'] ?? '';
		$dataPops         = $Options['dataPops'] ?? '';

		//Definir opciones válidas
		$validOptions = [
			'required'  => range(1, 2),
			'FormAling' => range(1, 5),
			'FormCol'   => range(0, 12)
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $required,  'name' => 'required',  'label' => '$required'],
			['value' => $FormAling, 'name' => 'FormAling', 'label' => '$FormAling'],
			['value' => $FormCol,   'name' => 'FormCol',   'label' => '$FormCol']
		];

		// Validaciones numéricas y enteras
		$fieldsToCheck = [
			['value' => $value,     'method' => 'validarNumero',  'label' => '$value',     'msg' => 'no es un numero'],
			['value' => $value,     'method' => 'validarEntero',  'label' => '$value',     'msg' => 'no es un numero entero'],
			['value' => $valor_ini, 'method' => 'validarNumero',  'label' => '$valor_ini', 'msg' => 'no es un numero'],
			['value' => $valor_ini, 'method' => 'validarEntero',  'label' => '$valor_ini', 'msg' => 'no es un numero entero'],
			['value' => $valor_fin, 'method' => 'validarNumero',  'label' => '$valor_fin', 'msg' => 'no es un numero'],
			['value' => $valor_fin, 'method' => 'validarEntero',  'label' => '$valor_fin', 'msg' => 'no es un numero entero'],
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 1);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		$dataReturn = $this->DataValidations->checkData('', $fieldsToCheck, $placeholder, 2);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

            /************************************/
			//Verifico
			$nameID          = (strpos($identificador, '[]') !== false) ? str_replace('[]', '', $identificador) . '_' . uniqid() : $identificador;
			$requerido       = ($required === 2) ? 'required="required"' : '';
			$placeholderIcon = (!empty($PlaceholderIcon)&&$PlaceholderIcon!='') ? "<i class='".$this->_scapeValue($PlaceholderIcon)."'></i> " : '';
			$dataInfo        = (!empty($DataInfo)&&$DataInfo!='') ? '<p class="formHelp text-muted">'.$this->_scapeValue($DataInfo).'</p>' : '';
			$dataPopover = $this->_buildDataPopover($dataPops);

			/************************************/
			//generacion del input
			$formRoute = $this->_resolveFormRoute('formSelectNAuto', $FormAling, $FormCol);
			/*******************************************/
			// Variables
			$selectedx = 'selected="selected"';
			$Options   = '';
			// Recorro
			for ($ini = $valor_ini; $ini <= $valor_fin; $ini++) {
				$isSelected = ($value == $ini) ? 'selected="selected"' : '';
				if ($isSelected){$selectedx = '';}
				$Options .= '<option value="'.$ini.'" '.$isSelected.'>'.$ini.'</option>';
			}
			$FormOptions  = '<option value="" '.$selectedx.'>Seleccione una Opción</option>';
			$FormOptions .= $Options;

			/************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath($formRoute);
			$this->TemplateRender->assign('nameID',           $nameID);
			$this->TemplateRender->assign('placeholder',      $placeholder);
			$this->TemplateRender->assign('placeholderIcon',  $placeholderIcon);
			$this->TemplateRender->assign('name',             $name);
			$this->TemplateRender->assign('requerido',        $requerido);
			$this->TemplateRender->assign('FormCol',          $FormCol);
			$this->TemplateRender->assign('FormOptions',      $FormOptions);
			$this->TemplateRender->assign('dataInfo',         $dataInfo);
			$this->TemplateRender->assign('dataPopover',      $dataPopover);

			/************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/************************************/
			//Imprimir dato
			echo $alerts;

		}

	}

	/******************************************************************************/
	/**
	 * Permite crear un input tipo upload, condicionado a la cantidad y al tipo de archivo.
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'Placeholder' => Nombre
	 *                      a mostrar; 'Name' => Nombre del input; 'MaxFiles' => Numero de Archivos;
	 *                      'TypeFiles' => Tipos de archivos a permitir.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formUploadMultiple([
	 *     'Placeholder' => 'Nombre',
	 *     'Name'        => 'Nombre',
	 *     'MaxFiles'    => 2,
	 *     'TypeFiles'   => '"jpg", "png", "gif", "jpeg"',
	 * ]);
	 * ```
	 */
	public function formUploadMultiple($Options){

		/**********************  Definiciones   **********************/
		$placeholder   = $Options['Placeholder'];
		$name          = $Options['Name'];
		$max_files     = $Options['MaxFiles'];
		$type_files    = $Options['TypeFiles'];

		// Validaciones numéricas y enteras
		$fieldsToCheck = [
			['value' => $max_files, 'method' => 'validarNumero',  'label' => '$max_files', 'msg' => 'no es un numero'],
			['value' => $max_files, 'method' => 'validarEntero',  'label' => '$max_files', 'msg' => 'no es un numero entero ('.$this->DataValidations->validarEntero($max_files).')'],
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData('', $fieldsToCheck, $placeholder, 2);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/************************************/
			//Verifico si es mas de un archivo
			$ndat = (isset($max_files) && $max_files != 1) ? '[]' : '';

			/************************************/
			//Mostrar Maximo de archivos
			$s_msg    = '<strong><i class="fa fa-file-o" aria-hidden="true"></i> Maximo de Archivos Permitidos: </strong>'.$max_files.'<br/>';
			$s_msg   .= '<strong><i class="fa fa-file-o" aria-hidden="true"></i> Extensiones de Archivos Permitidos: </strong><br/>'.$type_files;
			$Alertas  = $this->Alertas->alertPostData(6, 4, 'exclamation-circle', 0, $s_msg);

			/************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath('../app/templates/Forms/formMultipleUpload_1.php');
			$this->TemplateRender->assign('Alertas',      $Alertas);
			$this->TemplateRender->assign('name',         $name);
			$this->TemplateRender->assign('placeholder',  $placeholder);
			$this->TemplateRender->assign('ndat',         $ndat);
			$this->TemplateRender->assign('type_files',   $type_files);
			$this->TemplateRender->assign('max_files',    $max_files);

			/************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/************************************/
			//Imprimir dato
			echo $alerts;

		}

	}

	/******************************************************************************/
	/**
	 * Permite crear un input tipo subida de imagen, con un popup que selecciona un area activa, la
	 * recorta y la entrega para su guardado.
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'Name' => Nombre del
	 *                      input; 'URL' => URL a redireccionar; 'ExtraData' => Datos extras aparte
	 *                      de la imagen.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formUploadIMG([
	 *     'Name'      => 'Nombre',
	 *     'URL'       => 'URL',
	 *     'ExtraData' => 'ExtraData',
	 * ]);
	 * ```
	 */
	public function formUploadIMG($Options){

		/**********************  Definiciones   **********************/
		$name      = $Options['Name'];
		$URL       = $Options['URL'];
		$ExtraData = $Options['ExtraData'];

		/********************** Si todo esta ok **********************/
		//Se agregan datos
		$this->TemplateRender->templatePath('../app/templates/Forms/formUploadImage_1.php');
		$this->TemplateRender->assign('name',      $name);
		$this->TemplateRender->assign('URL',       $URL);
		$this->TemplateRender->assign('ExtraData', $ExtraData);

		/************************************/
		//Imprimir dato
		echo $this->TemplateRender->render();

	}

	/******************************************************************************/
	/**
	 * Permite crear un input check que desactiva el boton submit a menos que sea marcado, tambien
	 * agrega un enlace que permite que sea leido.
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'FormCol' => Columnas
	 *                      del formulario (0 al 12); 'NameID' => Identificador; 'Color' => Color
	 *                      input; 'Inicio' => Texto Inicio; 'Fin' => Texto Fin; 'Link' => Enlace;
	 *                      'NombreBoton' => Nombre del boton.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formTermsAndConditions([
	 *     'FormCol'     => 8,
	 *     'NameID'      => 'NameID',
	 *     'Color'       => 'Color',
	 *     'Inicio'      => 'Inicio',
	 *     'Fin'         => 'Fin',
	 *     'Link'        => 'Link',
	 *     'NombreBoton' => '',
	 * ]);
	 * ```
	 */
	public function formTermsAndConditions($Options){

		/**********************  Definiciones   **********************/
		$FormCol     = $Options['FormCol'] ?? 8;
		$nameID      = $Options['NameID'];
		$color       = $Options['Color'];
		$inicio      = $Options['Inicio'];
		$fin         = $Options['Fin'];
		$link        = $Options['Link'];
		$submitName  = $Options['NombreBoton'];

		//Definir opciones válidas
		$validOptions = [
			'color'   => range(1, 6),
			'FormCol' => range(0, 12)
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $color,   'name' => 'color',   'label' => '$color'],
			['value' => $FormCol, 'name' => 'FormCol', 'label' => '$FormCol'],
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

			/************************************/
			//Selecciono el tipo de mensaje
			$options = ['default', 'primary', 'success', 'danger', 'warning', 'info'];
			$tipo    = $options[$color-1];

			/************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath('../app/templates/Forms/formTermsAndConditions_1.php');
			$this->TemplateRender->assign('otrcol',      (12 - $FormCol));
			$this->TemplateRender->assign('nameID',      $nameID);
			$this->TemplateRender->assign('FormCol',     $FormCol);
			$this->TemplateRender->assign('tipo',        $tipo);
			$this->TemplateRender->assign('inicio',      $inicio);
			$this->TemplateRender->assign('link',        $link);
			$this->TemplateRender->assign('fin',         $fin);
			$this->TemplateRender->assign('submitName',  $submitName);

			/************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/************************************/
			//Imprimir dato
			echo $alerts;

		}

	}

	/******************************************************************************/
	/**
	 * Permite crear un input check que desactiva el boton submit a menos que sea marcado.
	 *
	 * @param array $Options Arreglo asociativo con las opciones del widget: 'FormCol' => Columnas
	 *                      del formulario (0 al 12); 'NameID' => Identificador; 'Color' => Color
	 *                      input; 'Text' => Texto; 'NombreBoton' => Nombre del boton.
	 *
	 * @return void Imprime el HTML del widget o las alertas de validacion.
	 *
	 * @example
	 * ```php
	 * $Form = new UIFormInputs();
	 * $Form->formConditionalSubmit([
	 *     'FormCol'     => 8,
	 *     'NameID'      => 'NameID',
	 *     'Color'       => 'Color',
	 *     'Text'        => 'Text',
	 *     'NombreBoton' => '',
	 * ]);
	 * ```
	 */
	public function formConditionalSubmit($Options){

		/**********************  Definiciones   **********************/
		$FormCol     = $Options['FormCol'] ?? 8;
		$nameID      = $Options['NameID'];
		$color       = $Options['Color'];
		$Text        = $Options['Text'];
		$submitName  = $Options['NombreBoton'];

		//Definir opciones válidas
		$validOptions = [
			'color'   => range(1, 6),
			'FormCol' => range(0, 12)
		];

		//Opciones a validar
		$optionsToCheck = [
			['value' => $color,   'name' => 'color',   'label' => '$color'],
			['value' => $FormCol, 'name' => 'FormCol', 'label' => '$FormCol'],
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

			/************************************/
			//Selecciono el tipo de mensaje
			$options = ['default', 'primary', 'success', 'danger', 'warning', 'info'];
			$tipo    = $options[$color-1];

			/************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath('../app/templates/Forms/formCondicionalSubmit_1.php');
			$this->TemplateRender->assign('otrcol',      (12 - $FormCol));
			$this->TemplateRender->assign('nameID',      $nameID);
			$this->TemplateRender->assign('FormCol',     $FormCol);
			$this->TemplateRender->assign('tipo',        $tipo);
			$this->TemplateRender->assign('Text',        $Text);
			$this->TemplateRender->assign('submitName',  $submitName);

			/************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/************************************/
			//Imprimir dato
			echo $alerts;

		}

	}


    /*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Privados                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
    /**
     * Escapa un valor para inserción segura en atributos o texto HTML.
     *
     * @param mixed $valor Valor a escapar (se convierte a string).
     *
     * @return string Valor escapado con htmlspecialchars (ENT_QUOTES, UTF-8).
     *
     * @example
     * ```php
     * echo $this->_scapeValue($valor); // valor con comillas y etiquetas neutralizadas
     * ```
     */
    private function _scapeValue($valor): string {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    }

    /******************************************************************************/
    /**
     * Escapa un valor para incrustarlo en literales JavaScript embebidos en HTML.
     *
     * Escapa contrabarras y comillas (no introduce entidades HTML, por lo que el
     * contenido con etiquetas intencionales se conserva al renderizarse) y neutraliza
     * el cierre prematuro de </script>. Elimina saltos de línea que romperían el
     * literal JS de una sola línea.
     *
     * @param mixed $valor Valor a escapar (se convierte a string).
     *
     * @return string Valor seguro para interpolar dentro de un literal JS.
     *
     * @example
     * ```php
     * $js = 'var x = "'.$this->_escapeJs($valor).'";';
     * ```
     */
    private function _escapeJs($valor): string {
        // Escapa contrabarras primero para no invalidar los escapes posteriores
        $escapeado = str_replace('\\', '\\\\', (string)$valor);
        // Escapa comillas simples y dobles para no romper el literal JS
        $escapeado = str_replace(["'", '"'], ["\\'", '\\"'], $escapeado);
        // Neutraliza el cierre de </script> conservando el valor en el HTML resultante
        $escapeado = str_ireplace('</script', '<\/script', $escapeado);
        // Elimina saltos de línea que romperían el literal JS de una sola línea
        return str_replace(["\r", "\n"], ' ', $escapeado);
    }

    /******************************************************************************/
    /**
     * Construye el HTML del icono popover (tooltip) de un campo de formulario.
     *
     * Centraliza el patrón repetido en los métodos de la clase y escapa los datos
     * de $dataPops para evitar inyección de atributos HTML (XSS).
     *
     * @param array|string $dataPops Popover: 'icon', 'style', 'position', 'content' (opcional).
     *
     * @return string HTML del icono popover o cadena vacía si no hay datos.
     *
     * @example
     * ```php
     * $dataPopover = $this->_buildDataPopover($dataPops);
     * ```
     */
    private function _buildDataPopover($dataPops): string {
        // Sin datos de popover (vacío o no-array) no se genera marca
        if (empty($dataPops) || !is_array($dataPops)) {
            return '';
        }

        // Escapado de cada valor insertado en atributos HTML
        $icono     = $this->_scapeValue($dataPops['icon']     ?? '');
        $estilo    = $this->_scapeValue($dataPops['style']    ?? '');
        $posicion  = $this->_scapeValue($dataPops['position'] ?? '');
        $contenido = $this->_scapeValue($dataPops['content']  ?? '');

        // Marca HTML del icono popover/tooltip
        return ' <i class="'.$icono.' text-'.$estilo.'" data-bs-toggle="tooltip" data-bs-placement="'.$posicion.'" title="'.$contenido.'"></i>';
    }

    /******************************************************************************/
    /**
     * Resuelve la ruta de plantilla de formulario según la alineación declarada.
     *
     * Centraliza el switch ($FormAling) duplicado en los métodos de la clase:
     * construye la ruta base + sufijo y asigna 'otrcol' en el caso 1 (Horizontal Form).
     *
     * @param string $Prefix    Nombre base de la plantilla (ej: 'formTime').
     * @param mixed  $FormAling Alineación del formulario (1 al 5).
     * @param mixed  $FormCol   Columnas del formulario (para el cálculo de 'otrcol').
     *
     * @return string Ruta de la plantilla seleccionada ('' si la alineación no es válida).
     *
     * @example
     * ```php
     * $formRoute = $this->_resolveFormRoute('formTime', $FormAling, $FormCol);
     * ```
     */
    private function _resolveFormRoute(string $Prefix, $FormAling, $FormCol): string {
        // Ruta por defecto vacía si la alineación está fuera de rango
        $formRoute = '';

        // Selección de la plantilla según la alineación declarada
        switch ($FormAling) {
            case 1: $formRoute = '../app/templates/Forms/'.$Prefix.'_1.php'; $this->TemplateRender->assign('otrcol', (12 - $FormCol)); break; //Horizontal Form
            case 2: $formRoute = '../app/templates/Forms/'.$Prefix.'_2.php'; break; //Vertical Form
            case 3: $formRoute = '../app/templates/Forms/'.$Prefix.'_3.php'; break; //Multi Columns Form
            case 4: $formRoute = '../app/templates/Forms/'.$Prefix.'_4.php'; break; //No Labels Form
            case 5: $formRoute = '../app/templates/Forms/'.$Prefix.'_5.php'; break; //Floating Labels Form
        }

        return $formRoute;
    }


}
