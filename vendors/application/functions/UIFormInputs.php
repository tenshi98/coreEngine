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
	/************************************************************************************************************/
	//Definiciones
	private $DataValidations;
	private $DataText;
	private $CommonData;
	private $Alertas;
	private $TemplateRender;

	/************************************************************************************************************/
	//Instancias
	public function __construct() {
		$this->DataValidations = new FunctionsDataValidations();
		$this->DataText        = new FunctionsDataText();
		$this->CommonData      = new FunctionsCommonData();
		$this->Alertas         = new UIWidgetsCommon();
        $this->TemplateRender  = new TemplateRenderer();
	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                  Metodos                                                        */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/*******************************************************************************************************************/
	/*                                                 Privadas                                                        */
	/*******************************************************************************************************************/
	/****************************************************************************************/
	//Crea el input en base a los datos
	private function checkInputGen($placeholder,$name, $ID, $tipo, $type, $valor, $check, $requerido){

		/******************************************/
		//Se agregan datos
		$this->TemplateRender->templatePath('../app/templates/Forms/formCheckInputGen_1.php');
		$this->TemplateRender->assign('tipo',        $tipo);
		$this->TemplateRender->assign('requerido',   $requerido);
		$this->TemplateRender->assign('check',       $check);
		$this->TemplateRender->assign('name',        $name);
		$this->TemplateRender->assign('type',        $type);
		$this->TemplateRender->assign('valor',       $valor);
		$this->TemplateRender->assign('ID',          $ID);
		$this->TemplateRender->assign('placeholder', $placeholder);

		/******************************************/
		//devuelvo
		return $this->TemplateRender->render();
	}
	/****************************************************************************************/
	//Crea el input en base a los datos
	private function selectInputGen($FormAling, $FormCol, $placeholder,$name, $nameID, $value, $selectProperties, $arrData, $classMain){

		/******************************************/
		//generacion del input
		switch ($FormAling) {
			case 1: $formRoute = '../app/templates/Forms/formSelectInputGen_1.php'; $this->TemplateRender->assign('otrcol', (12 - $FormCol)); break; //Horizontal Form
			case 2: $formRoute = '../app/templates/Forms/formSelectInputGen_2.php'; break; //Vertical Form
			case 3: $formRoute = '../app/templates/Forms/formSelectInputGen_3.php'; break; //Multi Columns Form
			case 4: $formRoute = '../app/templates/Forms/formSelectInputGen_4.php'; break; //No Labels Form
			case 5: $formRoute = '../app/templates/Forms/formSelectInputGen_5.php'; break; //Floating Labels Form
		}

		/*******************************************/
		// Inicializa la variable que marcará la opción vacía como seleccionada por defecto
		$selectedx = 'selected="selected"';
		// Inicializa la cadena que contendrá todas las opciones del <select>
		$Options = '';
		// Recorre el arreglo de datos para construir cada opción del <select>
		foreach ($arrData as $select) {
			// Verifica si el valor actual coincide con el ID del elemento
			$isSelected = ($value == $select['ID']) ? 'selected="selected"' : '';
			// Si alguna opción está seleccionada, se desactiva la selección por defecto
			if ($isSelected) {$selectedx = '';}
			// Construye la etiqueta <option> con el valor y el texto formateado
			$Options .= '<option value="' . $select['ID'] . '" ' . $isSelected . '>' . $this->DataText->tituloMenu($select['Nombre']) . '</option>';
		}
		// Construye la opción inicial "Seleccione una Opción", que estará seleccionada si ninguna otra lo está
		$SelectOptions = '<option value="" ' . $selectedx . '>Seleccione una Opción</option>';
		// Agrega todas las opciones generadas al conjunto final
		$SelectOptions .= $Options;

		/******************************************/
		//Se agregan datos
		$this->TemplateRender->templatePath($formRoute);
		$this->TemplateRender->assign('nameID',           $nameID);
		$this->TemplateRender->assign('placeholder',      $placeholder);
		$this->TemplateRender->assign('FormCol',          $FormCol);
		$this->TemplateRender->assign('classMain',        $classMain);
		$this->TemplateRender->assign('name',             $name);
		$this->TemplateRender->assign('selectProperties', $selectProperties);
		$this->TemplateRender->assign('SelectOptions',    $SelectOptions);

		/******************************************/
		//devuelvo
		return $this->TemplateRender->render();
	}
	/****************************************************************************************/
	//Crea el input en base a los datos
	private function selectInputGroupGen($FormAling, $FormCol, $placeholder,$name, $nameID, $value, $selectProperties, $arrData, $classMain){

		/********************************************************/
		//Opero los datos
		$newArray  = $this->CommonData->agruparPorClave ($arrData,'ID2'); //transforma a array multinivel

		/******************************************/
		//generacion del input
		switch ($FormAling) {
			case 1: $formRoute = '../app/templates/Forms/formSelectInputGen_1.php'; $this->TemplateRender->assign('otrcol', (12 - $FormCol)); break; //Horizontal Form
			case 2: $formRoute = '../app/templates/Forms/formSelectInputGen_2.php'; break; //Vertical Form
			case 3: $formRoute = '../app/templates/Forms/formSelectInputGen_3.php'; break; //Multi Columns Form
			case 4: $formRoute = '../app/templates/Forms/formSelectInputGen_4.php'; break; //No Labels Form
			case 5: $formRoute = '../app/templates/Forms/formSelectInputGen_5.php'; break; //Floating Labels Form
		}

		/*******************************************/
		// Variable que indica si la opción vacía ("Seleccione una Opción") debe estar seleccionada por defecto
		$selectedx = 'selected="selected"';
		// Variable que acumulará todas las opciones y grupos de opciones del <select>
		$Options = '';
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
				if ($isSelected) {$selectedx = '';}
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

		/******************************************/
		//Se agregan datos
		$this->TemplateRender->templatePath($formRoute);
		$this->TemplateRender->assign('nameID',           $nameID);
		$this->TemplateRender->assign('placeholder',      $placeholder);
		$this->TemplateRender->assign('FormCol',          $FormCol);
		$this->TemplateRender->assign('classMain',        $classMain);
		$this->TemplateRender->assign('name',             $name);
		$this->TemplateRender->assign('selectProperties', $selectProperties);
		$this->TemplateRender->assign('SelectOptions',    $SelectOptions);

		/******************************************/
		//devuelvo
		return $this->TemplateRender->render();
	}
	/****************************************************************************************/
	//Funcionalidad de select depend
	private function selectInputEmpty($FormAling, $FormCol, $placeholder,$name, $nameID, $selectProperties){

		/******************************************/
		//generacion del input
		switch ($FormAling) {
			case 1: $formRoute = '../app/templates/Forms/formSelectInputGen_1.php'; $this->TemplateRender->assign('otrcol', (12 - $FormCol)); break; //Horizontal Form
			case 2: $formRoute = '../app/templates/Forms/formSelectInputGen_2.php'; break; //Vertical Form
			case 3: $formRoute = '../app/templates/Forms/formSelectInputGen_3.php'; break; //Multi Columns Form
			case 4: $formRoute = '../app/templates/Forms/formSelectInputGen_4.php'; break; //No Labels Form
			case 5: $formRoute = '../app/templates/Forms/formSelectInputGen_5.php'; break; //Floating Labels Form
		}

		/*******************************************/
		//Variables
		$SelectOptions  = '<option value="">Seleccione una Opción</option>';
		/******************************************/
		//Se agregan datos
		$this->TemplateRender->templatePath($formRoute);
		$this->TemplateRender->assign('nameID',           $nameID);
		$this->TemplateRender->assign('placeholder',      $placeholder);
		$this->TemplateRender->assign('FormCol',          $FormCol);
		$this->TemplateRender->assign('classMain',        '');
		$this->TemplateRender->assign('name',             $name);
		$this->TemplateRender->assign('selectProperties', $selectProperties);
		$this->TemplateRender->assign('SelectOptions',    $SelectOptions);

		/******************************************/
		//devuelvo
		return $this->TemplateRender->render();
	}
	/****************************************************************************************/
	//Funcionalidad de select depend
	private function selectInputScript($arrData, $value, $name1, $name2, $form_name, $FormAling){
		/********************************************************/
		//Opero los datos
		$newArray  = $this->CommonData->agruparPorClave ($arrData, 'ID2');    //transforma a array multinivel

		//para corregir consulta en caso de vacio
		if(!isset($value) || $value == ''){$value = 0;}

		/******************************************/
		//generacion del input
		switch ($FormAling) {
			case 1: $XDisplay = 'flex';break;//Horizontal Form
			case 2: $XDisplay = 'block';break;//Vertical Form
			case 3: $XDisplay = 'block';break;//Multi Columns Form
			case 4: $XDisplay = 'block';break;//No Labels Form
			case 4: $XDisplay = 'block';break;//Floating Labels Form
		}

		//caracteres prohibidos
		$vowels = array(" ", "´", "-");
		//Variable
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

		/******************************************/
		//Se agregan datos
		$this->TemplateRender->templatePath('../app/templates/Forms/formSelectInputScript_1.php');
		$this->TemplateRender->assign('MatrixData',  $MatrixData);
		$this->TemplateRender->assign('RandName',    $RandName);
		$this->TemplateRender->assign('name1',       $name1);
		$this->TemplateRender->assign('name2',       $name2);
		$this->TemplateRender->assign('XDisplay',    $XDisplay);

		/******************************************/
		//devuelvo
		return $this->TemplateRender->render();
	}
	/****************************************************************************************/
	//Crea el input en base a los datos
	private function inputDatalist($nameID, $arrData){

		/*******************************************/
		//Datos
		$dataList = '';
		// Recorre el arreglo $arrData
		foreach ( $arrData as $select ) {
			$dataList .= '<option value="'.$this->DataText->tituloMenu($select['Nombre']).'">';
		}

		/******************************************/
		//Se agregan datos
		$this->TemplateRender->templatePath('../app/templates/Forms/formInputData_1.php');
		$this->TemplateRender->assign('dataList', $dataList);
		$this->TemplateRender->assign('nameID',   $nameID);

		/******************************************/
		//devuelvo
		return $this->TemplateRender->render();
	}




	/*******************************************************************************************************************/
	/*                                                 PUBLICAS                                                        */
	/*******************************************************************************************************************/
	/*******************************************************************************************************************/
	public function formTittle($Options){
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite crear un input tipo titulo
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formTittle($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'Tipo'  => 1,  //Tipo de formulario (1 al 7)
		*		'Texto' => ''  //Texto a mostrar
		*	];
		*===================================================================================================================
		*/

		/**********************  Definiciones   **********************/
		$type = $Options['Tipo'];
		$Text = $Options['Texto'];

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

			/******************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath('../app/templates/Forms/formTittle_1.php');
			$this->TemplateRender->assign('tipo',  $tipo);
			$this->TemplateRender->assign('Text',  $Text);

			/******************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/******************************************/
			//Imprimir dato
			echo $alerts;

		}
	}
	/*******************************************************************************************************************/
	public function formPostData($color, $type, $icon, $autoClose, $Text){
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite crear un elemento tipo alerta
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formPostData($color, $type, $icon, $autoClose, $Text);
		*
		*=================================================    Parametros   =================================================
		* String   $color      Numerico 1 al 8, permite seleccionar entre los colores disponibles
		* String   $type       Numerico 1 al 6, permite seleccionar el tipo de mensaje
		* String   $icon       icono a utilizar(fontawesome u otro)
		* String   $autoClose  Numerico 0 o 1, muestra la opcion de mostrar el boton de cerrado
		* String   $Text       Texto del mensaje
		* @return  HTML
		*===================================================================================================================
		*/

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		echo $this->Alertas->alertPostData($color, $type, $icon, $autoClose, $Text);

	}
    /*******************************************************************************************************************/
	public function formInputHidden($Options){
		/*
		*=================================================     Detalles    =================================================
		*
		* Permite crear un input tipo oculto
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formInputHidden($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'Name'     => 'Nombre', //Nombre del input
		*		'Value'    => 'asd',    //Valor del input
		*		'Required' => 1         //Si input es requerido (1 al 2)
		*	];
		*===================================================================================================================
		*/

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

			/******************************************/
			//Valido si es requerido
			$requerido = ($required === 2) ? 'required="required"' : '';

			/******************************************/
			//Si existe un valor entregado
			$valor = ($value !== '') ? $value : '';

			/******************************************/
			//Imprimir dato
			echo '<input type="hidden" name="'.$name.'" id="'.$name.'" value="'.$valor.'" '.$requerido.' >';
		}else{
			echo $alerts;
		}
	}
	/*******************************************************************************************************************/
	public function formInput($Options){
		/*
		*=================================================     Detalles    =================================================
		* Permite crear un input basado en las configuraciones, ofrece varias
		* opciones
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formInput($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'FormType'    => 1,               //Tipo de formulario (1 al 13)
		*		'FormAling'   => 1,               //Alineacion del formulario (1 al 5)
		*		'FormCol'     => 8,               //Columnas del formulario (0 al 12)
		*		'Placeholder' => 'Nombre',        //Nombre a mostrar
		*		'Name'        => 'Nombre',        //Nombre del input
		*		'Id'          => 'Identificador', //Identificador del input
		*		'Value'       => 'asd',           //Valor del input
		*		'Required'    => 1,               //Si input es requerido (1 al 3)
		*		'InputClass'  => '',              //Clase extra
		*		'Icon'        => '',              //Icono a mostrar
		*	];
		*===================================================================================================================
		*/

		/**********************  Definiciones   **********************/
		$placeholder   = $Options['Placeholder'];
		$name          = $Options['Name'];
		$formType      = $Options['FormType'] ?? 1;
		$formAlign     = $Options['FormAling'] ?? 1;
		$formCol       = $Options['FormCol'] ?? 8;
		$identificador = $Options['Id'] ?? $name;
		$value         = $Options['Value'] ?? '';
		$required      = $Options['Required'] ?? 1;
		$InputClass    = $Options['InputClass'] ?? '';
		$Icono         = $Options['Icon'] ?? '';

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

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 2);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

        /******************************************/
        //Valido por tipo de elemento
        switch ($formType) {
            case 4: $dataReturnSwitch = $this->DataValidations->checkData('', $value, $placeholder, 7); break; //number ->Numeros enteros positivos
            case 6: $dataReturnSwitch = $this->DataValidations->checkData('', $value, $placeholder, 7); break; //number ->Numeros enteros, permite valores negativos
            case 7: $dataReturnSwitch = $this->DataValidations->checkData('', $value, $placeholder, 8); break; //date
            case 8: $dataReturnSwitch = $this->DataValidations->checkData('', $value, $placeholder, 8); break; //form_input_date
        }
		//Verifico si hay datos
		if (isset($dataReturnSwitch) && !empty($dataReturnSwitch) && is_array($dataReturnSwitch)) {
			$errorn += $dataReturnSwitch['nErrors'];
			$alerts .= $dataReturnSwitch['alerts'];
		}

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

            /******************************************/
			//Variables
			$ExtraClass      = '';
            $ExtraClassGroup = '';
            $ExtraCode       = '';

            /******************************************/
			//Verifico si nombre viene de un array
			$nameID = (strpos($identificador, '[]') !== false)
				? str_replace('[]', '', $identificador) . '_' . uniqid()
				: $identificador;

			/******************************************/
			//Valido si es requerido
			switch ($required) {
				case 1:$requerido = '';                    break;//Si el dato no es requerido
				case 2:$requerido = 'required="required"'; break;//Si el dato es requerido
				case 3:$requerido = 'disabled';            break;//Si el dato esta desactivado
			}

			/******************************************/
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
										$("#'.$nameID.'").bootstrapMaterialDatePicker
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
										$("#'.$nameID.'").bootstrapMaterialDatePicker
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
					'ExtraCode'    => '<script>$("#'.$nameID.'").rut();</script>',
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
										$("#'.$nameID.'").colorpickerplus();
										$("#'.$nameID.'").on("changeColor", function(e,color){
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


            /******************************************/
			//Verifico si se utiliza el icono
			$input_1 = $input_2 = $input_3 = '';
			if (!empty($Icono)&&$Icono!='') {
				$input_1 = '<div class="input-group '.$ExtraClassGroup.'"><span class="input-group-text" id="basic-addon1"><i class="'.$Icono.'"></i></span>';
                $input_2 = 'aria-describedby="basic-addon1"';
                $input_3 = '</div>';
			}

			/******************************************/
			//generacion del input
			switch ($formAlign) {
				case 1: $formRoute = '../app/templates/Forms/formInput_1.php'; $this->TemplateRender->assign('otrcol', (12 - $formCol)); break;//Horizontal Form
				case 2: $formRoute = '../app/templates/Forms/formInput_2.php'; break;//Vertical Form
				case 3: $formRoute = '../app/templates/Forms/formInput_3.php'; break;//Multi Columns Form
				case 4: $formRoute = '../app/templates/Forms/formInput_4.php'; break;//No Labels Form
				case 5: $formRoute = '../app/templates/Forms/formInput_5.php'; break;//Floating Labels Form
			}

            /******************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath($formRoute);
			$this->TemplateRender->assign('nameID',       $nameID);
			$this->TemplateRender->assign('name',         $name);
			$this->TemplateRender->assign('placeholder',  $placeholder);
			$this->TemplateRender->assign('formCol',      $formCol);
			$this->TemplateRender->assign('InTipo',       $InTipo);
			$this->TemplateRender->assign('InputClass',   $InputClass);
			$this->TemplateRender->assign('value',        $value);
			$this->TemplateRender->assign('requerido',    $requerido);
			$this->TemplateRender->assign('jsValidation', $jsValidation);
			$this->TemplateRender->assign('input_1',      $input_1);
			$this->TemplateRender->assign('input_2',      $input_2);
			$this->TemplateRender->assign('input_3',      $input_3);
			$this->TemplateRender->assign('ExtraClass',   $ExtraClass);

			/******************************************/
			//ejecucion
			$input  = $this->TemplateRender->render();
			$input .= $ExtraCode;

			/******************************************/
			//Imprimir dato
			echo $input;

		}else{

			/******************************************/
			//Imprimir dato
			echo $alerts;

		}
	}
	/*******************************************************************************************************************/
	public function formNumberSpinner($Options){
		/*
		*=================================================     Detalles    =================================================
		* Permite crear un input que solo permite el ingreso de numeros,
		* permite valores decimales y numeros negativos, agregando botones
		* en ambos lados, que aumentan o disminuyen los valores ingresados,
		* en los rangos preestablecido
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formNumberSpinner($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'FormAling'   => 1,               //Alineacion del formulario (1 al 5)
		*		'FormCol'     => 8,               //Columnas del formulario (0 al 12)
		*		'Placeholder' => 'Nombre',        //Nombre a mostrar
		*		'Name'        => 'Nombre',        //Nombre del input
		*		'Id'          => 'Identificador', //Identificador del input
		*		'Value'       => 'asd',           //Valor del input
		*		'Min'         => '',              //Valor Minimo
		*		'Max'         => '',              //Valor Maximo
		*		'Step'        => '',              //Valores de avance o retroceso
		*		'Ndecimal'    => '',              //numero de decimales
		*		'Required'    => 1,               //Si input es requerido (1 al 3)
		*	];
		*===================================================================================================================
		*/

		/**********************  Definiciones   **********************/
		$placeholder   = $Options['Placeholder'];
		$name          = $Options['Name'];
		$min           = $Options['Min'];
		$max           = $Options['Max'];
		$step          = $Options['Step'];
		$ndecimal      = $Options['Ndecimal'];
		$FormAling     = $Options['FormAling'] ?? 1;
		$FormCol       = $Options['FormCol'] ?? 8;
		$identificador = $Options['Id'] ?? $name;
		$value         = $Options['Value'] ?? '';
		$required      = $Options['Required'] ?? 1;

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

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 2);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		$dataReturn = $this->DataValidations->checkData('', $fieldsToCheck, $placeholder, 3);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			 /******************************************/
			//Verifico si nombre viene de un array
			$nameID = (strpos($identificador, '[]') !== false)
				? str_replace('[]', '', $identificador) . '_' . uniqid()
				: $identificador;

			/******************************************/
			//Valido si es requerido
			switch ($required) {
				case 1:$requerido = '';                    break;//Si el dato no es requerido
				case 2:$requerido = 'required="required"'; break;//Si el dato es requerido
				case 3:$requerido = 'disabled';            break;//Si el dato esta desactivado
			}

			/******************************************/
			//Si existe un valor entregado
			$valor = ($value != 0) ? str_replace(',', '.', $value) : '';

			/******************************************/
			//generacion del input
			switch ($FormAling) {
				case 1: $formRoute = '../app/templates/Forms/formInputNumberSpinner_1.php'; $this->TemplateRender->assign('otrcol', (12 - $FormCol)); break;//Horizontal Form
				case 2: $formRoute = '../app/templates/Forms/formInputNumberSpinner_2.php'; break;//Vertical Form
				case 3: $formRoute = '../app/templates/Forms/formInputNumberSpinner_3.php'; break;//Multi Columns Form
				case 4: $formRoute = '../app/templates/Forms/formInputNumberSpinner_4.php'; break;//No Labels Form
				case 5: $formRoute = '../app/templates/Forms/formInputNumberSpinner_5.php'; break;//Floating Labels Form
			}

			/******************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath($formRoute);
			$this->TemplateRender->assign('nameID',      $nameID);
			$this->TemplateRender->assign('name',        $name);
			$this->TemplateRender->assign('placeholder', $placeholder);
			$this->TemplateRender->assign('FormCol',     $FormCol);
			$this->TemplateRender->assign('valor',       $valor);
			$this->TemplateRender->assign('requerido',   $requerido);
			$this->TemplateRender->assign('min',         $min);
			$this->TemplateRender->assign('max',         $max);
			$this->TemplateRender->assign('step',        $step);
			$this->TemplateRender->assign('ndecimal',    $ndecimal);

			/******************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/******************************************/
			//Imprimir dato
			echo $alerts;

		}
	}
	/*******************************************************************************************************************/
	public function formTime($Options){
		/*
		*=================================================     Detalles    =================================================
		* Permite crear un input dentro del cual se selecciona la hora que se
		* desea ingresar, el selector de hora aparece al presionar dentro del
		* input, una vez seleccionada la hora esta sera traspasada al input
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formTime($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'FormAling'   => 1,               //Alineacion del formulario (1 al 5)
		*		'FormCol'     => 8,               //Columnas del formulario (0 al 12)
		*		'Placeholder' => 'Nombre',        //Nombre a mostrar
		*		'Name'        => 'Nombre',        //Nombre del input
		*		'Id'          => 'Identificador', //Identificador del input
		*		'Value'       => 'asd',           //Valor del input
		*		'Required'    => 2,               //Si input es requerido (1 al 3)
		*		'Position'    => '',              //Posicion del popup (1 o 2)
		*		'Icon'        => '',              //Icono a mostrar
		*	];
		*===================================================================================================================
		*/

		/**********************  Definiciones   **********************/
		$placeholder   = $Options['Placeholder'];
		$name          = $Options['Name'];
		$FormAling     = $Options['FormAling'] ?? 1;
		$FormCol       = $Options['FormCol'] ?? 8;
		$identificador = $Options['Id'] ?? $name;
		$value         = $Options['Value'] ?? '';
		$required      = $Options['Required'] ?? 1;
		$position      = $Options['Position'] ?? '';
		$Icono         = $Options['Icon'] ?? '';

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

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 2);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/******************************************/
			//Verifico si nombre viene de un array
			$nameID = (strpos($identificador, '[]') !== false)
				? str_replace('[]', '', $identificador) . '_' . uniqid()
				: $identificador;

			/******************************************/
			//Valido si es requerido
			switch ($required) {
				case 1:$requerido = '';                    break;//Si el dato no es requerido
				case 2:$requerido = 'required="required"'; break;//Si el dato es requerido
				case 3:$requerido = 'disabled';            break;//Si el dato esta desactivado
			}

            /******************************************/
			//Verifico si se utiliza el icono
			$input_1 = $input_2 = $input_3 = '';
			if (!empty($Icono)&&$Icono!='') {
				$input_1 = '<div class="input-group"><span class="input-group-text" id="basic-addon1"><i class="'.$Icono.'"></i></span>';
                $input_2 = 'aria-describedby="basic-addon1"';
                $input_3 = '</div>';
			}

			/******************************************/
			//Posicion de la burbuja
			$options = ['top', 'bottom'];
			$x_pos   = $options[$position-1];

			/******************************************/
			//generacion del input
			switch ($FormAling) {
				case 1: $formRoute = '../app/templates/Forms/formTime_1.php'; $this->TemplateRender->assign('otrcol', (12 - $FormCol)); break;//Horizontal Form
				case 2: $formRoute = '../app/templates/Forms/formTime_2.php'; break;//Vertical Form
				case 3: $formRoute = '../app/templates/Forms/formTime_3.php'; break;//Multi Columns Form
				case 4: $formRoute = '../app/templates/Forms/formTime_4.php'; break;//No Labels Form
				case 5: $formRoute = '../app/templates/Forms/formTime_5.php'; break;//Floating Labels Form
			}

			/******************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath($formRoute);
			$this->TemplateRender->assign('nameID',      $nameID);
			$this->TemplateRender->assign('name',        $name);
			$this->TemplateRender->assign('placeholder', $placeholder);
			$this->TemplateRender->assign('FormCol',     $FormCol);
			$this->TemplateRender->assign('value',       $value);
			$this->TemplateRender->assign('requerido',   $requerido);
			$this->TemplateRender->assign('input_1',     $input_1);
			$this->TemplateRender->assign('input_2',     $input_2);
			$this->TemplateRender->assign('input_3',     $input_3);
			$this->TemplateRender->assign('x_pos',       $x_pos);

			/******************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/******************************************/
			//Imprimir dato
			echo $alerts;

		}
	}
	/*******************************************************************************************************************/
	public function formCheckbox($Options){
		/*
		*=================================================     Detalles    =================================================
		* Permite crear un input tipo checkbox
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formCheckbox($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'FormCol'     => 8,               //Columnas del formulario (0 al 12)
		*		'Placeholder' => 'Nombre',        //Nombre a mostrar
		*		'Name'        => 'Nombre',        //Nombre del input
		*		'Id'          => 'Identificador', //Identificador del input
		*		'Value'       => 'asd',           //Valor del input
		*		'Required'    => 1,               //Si input es requerido (1 al 3)
		*		'Color'       => '',              //Color a mostrar
		*	];
		*===================================================================================================================
		*/

		/**********************  Definiciones   **********************/
		$placeholder   = $Options['Placeholder'];
		$name          = $Options['Name'];
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

		// Validaciones numéricas y enteras
		$fieldsToCheck = [
			['value' => $value, 'method' => 'validarNumero',  'label' => '$value', 'msg' => 'no es un numero'],
			['value' => $value, 'method' => 'validarEntero',  'label' => '$value', 'msg' => 'no es un numero entero'],
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 2);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		$dataReturn = $this->DataValidations->checkData('', $fieldsToCheck, $placeholder, 3);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/******************************************/
			//Valido si es requerido
			switch ($required) {
				case 1:$requerido = '';                     break;//Si el dato no es requerido
				case 2:$requerido = 'required="required"';  break;//Si el dato es requerido
				case 3:$requerido = 'disabled';             break;//Si el dato esta desactivado
			}

			/******************************************/
			//Verifico si nombre viene de un array
			$nameID = (strpos($identificador, '[]') !== false)
				? str_replace('[]', '', $identificador) . '_' . uniqid()
				: $identificador;

			/******************************************/
			//Si el tab correspondiente esta seleccionado
			$check = (isset($value) && $value == 2) ? 'checked' : '';
			$valor = '2';

			/******************************************/
			//Selecciono el tipo de mensaje
			$options = ['default', 'primary', 'success', 'danger', 'warning', 'info'];
			$tipo    = $options[$color-1];

			/******************************************/
			//Se genera input
			$formInput = $this->checkInputGen($placeholder,$name, $nameID, $tipo, 'checkbox', $valor, $check, $requerido);

			/******************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath('../app/templates/Forms/formCheckRadioBox_1.php');
			$this->TemplateRender->assign('otrcol',      (12 - $FormCol));
			$this->TemplateRender->assign('nameID',      $nameID);
			$this->TemplateRender->assign('placeholder', $placeholder);
			$this->TemplateRender->assign('FormCol',     $FormCol);
			$this->TemplateRender->assign('formInput',   $formInput);

			/******************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/******************************************/
			//Imprimir dato
			echo $alerts;

		}
	}
	/*******************************************************************************************************************/
	public function formCheckboxActive($Options){
		/*
		*=================================================     Detalles    =================================================
		* Permite crear un input tipo checkbox en base a datos de la base de
		* datos, que lleva por defecto opciones activas
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formCheckboxActive($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'FormCol'     => 8,               //Columnas del formulario (0 al 12)
		*		'Placeholder' => 'Nombre',        //Nombre a mostrar
		*		'Name'        => 'Nombre',        //Nombre del input
		*		'Id'          => 'Identificador', //Identificador del input
		*		'Value'       => 'asd',           //Valor del input
		*		'Required'    => 1,               //Si input es requerido (1 al 3)
		*		'Color'       => '',              //Color a mostrar
		*		'arrData'     => '',              //Datos a recorrer
		*	];
		*===================================================================================================================
		*/

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

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 2);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/******************************************/
			//Verifico si nombre viene de un array
			$nameID = (strpos($identificador, '[]') !== false)
				? str_replace('[]', '', $identificador) . '_' . uniqid()
				: $identificador;

			/******************************************/
			//Valido si es requerido
			switch ($required) {
				case 1:$requerido = '';          break;//Si el dato no es requerido
				case 2:$requerido = '';          break;//Si el dato es requerido
				case 3:$requerido = 'disabled';  break;//Si el dato esta desactivado
			}

			/******************************************/
			//Selecciono el tipo de mensaje
			$options = ['default', 'primary', 'success', 'danger', 'warning', 'info'];
			$tipo    = $options[$color-1];

			/******************************************/
			//vars
			$arrValTab = array_map('trim', explode(",", $value));

			/******************************************/
			$y         = 1;
			$formInput = '';
			//Recorro
			foreach ( $arrData as $select ) {
				/******************************************/
				//Si el tab correspondiente esta seleccionado
				$check = (isset($arrValTab[$y]) && $arrValTab[$y] == 2) ? 'checked' : '';
				$valor = '2';
				/******************************************/
				//generacion del input
				$formInput .= $this->checkInputGen($select['Nombre'],$name.'_'.$select['ID'], $nameID.'_'.$select['ID'], $tipo, 'checkbox', $valor, $check, $requerido);
				//sumo
				$y++;
			}

			/******************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath('../app/templates/Forms/formCheckRadioBoxActive_1.php');
			$this->TemplateRender->assign('otrcol',      (12 - $FormCol));
			$this->TemplateRender->assign('nameID',      $nameID);
			$this->TemplateRender->assign('placeholder', $placeholder);
			$this->TemplateRender->assign('FormCol',     $FormCol);
			$this->TemplateRender->assign('formInput',   $formInput);

			/******************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/******************************************/
			//Imprimir dato
			echo $alerts;

		}
	}
	/*******************************************************************************************************************/
	public function formRadio($Options){
		/*
		*=================================================     Detalles    =================================================
		* Permite crear un input tipo radio
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formRadio($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'FormCol'     => 8,               //Columnas del formulario (0 al 12)
		*		'Placeholder' => 'Nombre',        //Nombre a mostrar
		*		'Name'        => 'Nombre',        //Nombre del input
		*		'Id'          => 'Identificador', //Identificador del input
		*		'Value'       => 'asd',           //Valor del input
		*		'Required'    => 1,               //Si input es requerido (1 al 3)
		*		'Color'       => '',              //Color a mostrar
		*	];
		*===================================================================================================================
		*/

		/**********************  Definiciones   **********************/
		$placeholder   = $Options['Placeholder'];
		$name          = $Options['Name'];
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

		// Validaciones numéricas y enteras
		$fieldsToCheck = [
			['value' => $value, 'method' => 'validarNumero',  'label' => '$value', 'msg' => 'no es un numero'],
			['value' => $value, 'method' => 'validarEntero',  'label' => '$value', 'msg' => 'no es un numero entero'],
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 2);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		$dataReturn = $this->DataValidations->checkData('', $fieldsToCheck, $placeholder, 3);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/******************************************/
			//Valido si es requerido
			switch ($required) {
				case 1:$requerido = '';          break;//Si el dato no es requerido
				case 2:$requerido = '';          break;//Si el dato es requerido
				case 3:$requerido = 'disabled';  break;//Si el dato esta desactivado
			}

			/******************************************/
			//Verifico si nombre viene de un array
			$nameID = (strpos($identificador, '[]') !== false)
				? str_replace('[]', '', $identificador) . '_' . uniqid()
				: $identificador;

			/******************************************/
			//Si el tab correspondiente esta seleccionado
			$check = (isset($value) && $value == 2) ? 'checked' : '';
			$valor = '2';

			/******************************************/
			//Selecciono el tipo de mensaje
			$options = ['default', 'primary', 'success', 'danger', 'warning', 'info'];
			$tipo    = $options[$color-1];

			/******************************************/
			//Se genera input
			$formInput = $this->checkInputGen($placeholder,$name, $nameID, $tipo, 'radio', $valor, $check, $requerido);

			/******************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath('../app/templates/Forms/formCheckRadioBox_1.php');
			$this->TemplateRender->assign('otrcol',      (12 - $FormCol));
			$this->TemplateRender->assign('nameID',      $nameID);
			$this->TemplateRender->assign('placeholder', $placeholder);
			$this->TemplateRender->assign('FormCol',     $FormCol);
			$this->TemplateRender->assign('formInput',   $formInput);

			/******************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/******************************************/
			//Imprimir dato
			echo $alerts;

		}
	}
	/*******************************************************************************************************************/
	public function formRadioActive($Options){
		/*
		*=================================================     Detalles    =================================================
		* Permite crear un input tipo radio en base a datos de la base de
		* datos, que lleva por defecto opciones activas
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formRadioActive($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'FormCol'     => 8,               //Columnas del formulario (0 al 12)
		*		'Placeholder' => 'Nombre',        //Nombre a mostrar
		*		'Name'        => 'Nombre',        //Nombre del input
		*		'Id'          => 'Identificador', //Identificador del input
		*		'Value'       => 'asd',           //Valor del input
		*		'Required'    => 1,               //Si input es requerido (1 al 3)
		*		'Color'       => '',              //Color a mostrar
		*		'arrData'     => '',              //Datos a recorrer
		*	];
		*===================================================================================================================
		*/

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

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 2);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/******************************************/
			//Verifico si nombre viene de un array
			$nameID = (strpos($identificador, '[]') !== false)
				? str_replace('[]', '', $identificador) . '_' . uniqid()
				: $identificador;

			/******************************************/
			//Valido si es requerido
			switch ($required) {
				case 1:$requerido = '';          break;//Si el dato no es requerido
				case 2:$requerido = '';          break;//Si el dato es requerido
				case 3:$requerido = 'disabled';  break;//Si el dato esta desactivado
			}

			/******************************************/
			//Selecciono el tipo de mensaje
			$options = ['default', 'primary', 'success', 'danger', 'warning', 'info'];
			$tipo    = $options[$color-1];

			/******************************************/
			//vars
			$arrValTab = array_map('trim', explode(",", $value));

			/******************************************/
			$y         = 1;
			$formInput = '';
			//Recorro
			foreach ( $arrData as $select ) {
				/******************************************/
				//Si el tab correspondiente esta seleccionado
				$check = (isset($arrValTab[$y]) && $arrValTab[$y] == 2) ? 'checked' : '';
				$valor = '2';
				/******************************************/
				//generacion del input
				$formInput .= $this->checkInputGen($select['Nombre'],$name, $nameID.'_'.$select['ID'], $tipo, 'radio', $valor, $check, $requerido);
				//sumo
				$y++;
			}

			/******************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath('../app/templates/Forms/formCheckRadioBoxActive_1.php');
			$this->TemplateRender->assign('otrcol',      (12 - $FormCol));
			$this->TemplateRender->assign('nameID',      $nameID);
			$this->TemplateRender->assign('placeholder', $placeholder);
			$this->TemplateRender->assign('FormCol',     $FormCol);
			$this->TemplateRender->assign('formInput',   $formInput);

			/******************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/******************************************/
			//Imprimir dato
			echo $alerts;

		}
	}
	/*******************************************************************************************************************/
	public function formSwitch($Options){
		/*
		*=================================================     Detalles    =================================================
		* Permite crear un input tipo switch
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formSwitch($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'FormCol'     => 8,               //Columnas del formulario (0 al 12)
		*		'Placeholder' => 'Nombre',        //Nombre a mostrar
		*		'Name'        => 'Nombre',        //Nombre del input
		*		'Id'          => 'Identificador', //Identificador del input
		*		'Value'       => 'asd',           //Valor del input
		*		'Required'    => 1,               //Si input es requerido (1 al 3)
		*		'Color'       => '',              //Color a mostrar
		*	];
		*===================================================================================================================
		*/

		/**********************  Definiciones   **********************/
		$placeholder   = $Options['Placeholder'];
		$name          = $Options['Name'];
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

		// Validaciones numéricas y enteras
		$fieldsToCheck = [
			['value' => $value, 'method' => 'validarNumero',  'label' => '$value', 'msg' => 'no es un numero'],
			['value' => $value, 'method' => 'validarEntero',  'label' => '$value', 'msg' => 'no es un numero entero'],
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 2);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		$dataReturn = $this->DataValidations->checkData('', $fieldsToCheck, $placeholder, 3);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/******************************************/
			//Valido si es requerido
			switch ($required) {
				case 1:$requerido = '';                     break;//Si el dato no es requerido
				case 2:$requerido = 'required="required"';  break;//Si el dato es requerido
				case 3:$requerido = 'disabled';             break;//Si el dato esta desactivado
			}

			/******************************************/
			//Verifico si nombre viene de un array
			$nameID = (strpos($identificador, '[]') !== false)
				? str_replace('[]', '', $identificador) . '_' . uniqid()
				: $identificador;

			/******************************************/
			//Si el tab correspondiente esta seleccionado
			$check = (isset($value) && $value == 2) ? 'checked' : '';
			$valor = '2';

			/******************************************/
			//Selecciono el tipo de mensaje
			$options = ['default', 'primary', 'success', 'danger', 'warning', 'info'];
			$tipo    = $options[$color-1];

			/******************************************/
			//Si el valor es 0
			$extracol = ((12 - $FormCol) != 0) ? '<label class="col-form-label col-sm-'.(12 - $FormCol).'">'.$placeholder.'</label>' : '';

			/******************************************/
			//Se genera input
			$formInput = $this->checkInputGen($placeholder,$name, $nameID, $tipo.' form-switch', 'checkbox', $valor, $check, $requerido);

			/******************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath('../app/templates/Forms/formSwitch_1.php');
			$this->TemplateRender->assign('nameID',      $nameID);
			$this->TemplateRender->assign('extracol',    $extracol);
			$this->TemplateRender->assign('FormCol',     $FormCol);
			$this->TemplateRender->assign('formInput',   $formInput);

			/******************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/******************************************/
			//Imprimir dato
			echo $alerts;

		}
	}
	/*******************************************************************************************************************/
	public function formSwitchActive($Options){
		/*
		*=================================================     Detalles    =================================================
		* Permite crear un input tipo switch en base a datos de la base de
		* datos, que lleva por defecto opciones activas
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formSwitchActive($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'FormCol'     => 8,               //Columnas del formulario (0 al 12)
		*		'Placeholder' => 'Nombre',        //Nombre a mostrar
		*		'Name'        => 'Nombre',        //Nombre del input
		*		'Id'          => 'Identificador', //Identificador del input
		*		'Value'       => 'asd',           //Valor del input
		*		'Required'    => 1,               //Si input es requerido (1 al 3)
		*		'Color'       => '',              //Color a mostrar
		*		'arrData'     => '',              //Datos a recorrer
		*	];
		*===================================================================================================================
		*/

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

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 2);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/******************************************/
			//Verifico si nombre viene de un array
			$nameID = (strpos($identificador, '[]') !== false)
				? str_replace('[]', '', $identificador) . '_' . uniqid()
				: $identificador;

			/******************************************/
			//Valido si es requerido
			switch ($required) {
				case 1:$requerido = '';          break;//Si el dato no es requerido
				case 2:$requerido = '';          break;//Si el dato es requerido
				case 3:$requerido = 'disabled';  break;//Si el dato esta desactivado
			}

			/******************************************/
			//Selecciono el tipo de mensaje
			$options = ['default', 'primary', 'success', 'danger', 'warning', 'info'];
			$tipo    = $options[$color-1];

			/******************************************/
			//vars
			$arrValTab = array_map('trim', explode(",", $value));

			/******************************************/
			$y         = 1;
			$formInput = '';
			//Recorro
			foreach ( $arrData as $select ) {
				/******************************************/
				//Si el tab correspondiente esta seleccionado
				$check = (isset($arrValTab[$y]) && $arrValTab[$y] == 2) ? 'checked' : '';
				$valor = '2';
				/******************************************/
				//generacion del input
				$formInput .= $this->checkInputGen($select['Nombre'], $name.'_'.$select['ID'], $nameID.'_'.$select['ID'], $tipo.' form-switch', 'checkbox', $valor, $check, $requerido);
				//sumo
				$y++;
			}

			/******************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath('../app/templates/Forms/formSwitchActive_1.php');
			$this->TemplateRender->assign('otrcol',      (12 - $FormCol));
			$this->TemplateRender->assign('nameID',      $nameID);
			$this->TemplateRender->assign('placeholder', $placeholder);
			$this->TemplateRender->assign('FormCol',     $FormCol);
			$this->TemplateRender->assign('formInput',   $formInput);

			/******************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/******************************************/
			//Imprimir dato
			echo $alerts;

		}
	}
	/*******************************************************************************************************************/
	public function formTextarea($Options){
		/*
		*=================================================     Detalles    =================================================
		* Permite crear un input tipo text
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formTextarea($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'FormAling'   => 1,               //Alineacion del formulario (1 al 5)
		*		'FormCol'     => 8,               //Columnas del formulario (0 al 12)
		*		'Placeholder' => 'Nombre',        //Nombre a mostrar
		*		'Name'        => 'Nombre',        //Nombre del input
		*		'Id'          => 'Identificador', //Identificador del input
		*		'Value'       => 'asd',           //Valor del input
		*		'Required'    => 2,               //Si input es requerido (1 al 3)
		*	];
		*===================================================================================================================
		*/

		/**********************  Definiciones   **********************/
		$placeholder   = $Options['Placeholder'];
		$name          = $Options['Name'];
		$FormAling     = $Options['FormAling'] ?? 1;
		$FormCol       = $Options['FormCol'] ?? 8;
		$identificador = $Options['Id'] ?? $name;
		$value         = $Options['Value'] ?? '';
		$required      = $Options['Required'] ?? 1;

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

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 2);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/******************************************/
			//Verifico si nombre viene de un array
			$nameID = (strpos($identificador, '[]') !== false)
				? str_replace('[]', '', $identificador) . '_' . uniqid()
				: $identificador;

			/******************************************/
			//Valido si es requerido
			switch ($required) {
				case 1:$requerido = '';                    break;//Si el dato no es requerido
				case 2:$requerido = 'required="required"'; break;//Si el dato es requerido
				case 3:$requerido = 'disabled';            break;//Si el dato esta desactivado
			}

			/******************************************/
			//generacion del input
			switch ($FormAling) {
				case 1: $formRoute = '../app/templates/Forms/formTextBox_1.php'; $this->TemplateRender->assign('otrcol', (12 - $FormCol)); break;//Horizontal Form
				case 2: $formRoute = '../app/templates/Forms/formTextBox_2.php'; break;//Vertical Form
				case 3: $formRoute = '../app/templates/Forms/formTextBox_3.php'; break;//Multi Columns Form
				case 4: $formRoute = '../app/templates/Forms/formTextBox_4.php'; break;//No Labels Form
				case 5: $formRoute = '../app/templates/Forms/formTextBox_5.php'; break;//Floating Labels Form
			}

			/******************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath($formRoute);
			$this->TemplateRender->assign('nameID',      $nameID);
			$this->TemplateRender->assign('placeholder', $placeholder);
			$this->TemplateRender->assign('FormCol',     $FormCol);
			$this->TemplateRender->assign('name',        $name);
			$this->TemplateRender->assign('requerido',   $requerido);
			$this->TemplateRender->assign('value',       $value);

			/******************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/******************************************/
			//Imprimir dato
			echo $alerts;

		}
	}
	/*******************************************************************************************************************/
	public function formCKEditor($Options){
		/*
		*=================================================     Detalles    =================================================
		* Permite crear un editor de texto completo
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formCKEditor($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'FormAling'   => 1,               //Alineacion del formulario (1 al 5)
		*		'FormCol'     => 8,               //Columnas del formulario (0 al 12)
		*		'Placeholder' => 'Nombre',        //Nombre a mostrar
		*		'Name'        => 'Nombre',        //Nombre del input
		*		'Id'          => 'Identificador', //Identificador del input
		*		'Value'       => 'asd',           //Valor del input
		*		'Required'    => 2,               //Si input es requerido (1 al 2)
		*		'Tipo'        => 2,               //Tipo de input (1 al 3)
		*	];
		*===================================================================================================================
		*/

		/**********************  Definiciones   **********************/
		$placeholder   = $Options['Placeholder'];
		$name          = $Options['Name'];
		$FormAling     = $Options['FormAling'] ?? 1;
		$FormCol       = $Options['FormCol'] ?? 8;
		$identificador = $Options['Id'] ?? $name;
		$value         = $Options['Value'] ?? '';
		$required      = $Options['Required'] ?? 1;
		$tipo          = $Options['Tipo'] ?? 1;

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

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 2);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/******************************************/
			//Verifico si nombre viene de un array
			$nameID = (strpos($identificador, '[]') !== false)
				? str_replace('[]', '', $identificador) . '_' . uniqid()
				: $identificador;

			/******************************************/
			//Valido si es requerido
			$requerido = ($required === 2) ? 'required="required"' : '';

			/******************************************/
			//generacion del input
			switch ($FormAling) {
				case 1: $formRoute = '../app/templates/Forms/formCKEditor_1.php'; $this->TemplateRender->assign('otrcol', (12 - $FormCol)); break; //Horizontal Form
				case 2: $formRoute = '../app/templates/Forms/formCKEditor_2.php'; break; //Vertical Form
				case 3: $formRoute = '../app/templates/Forms/formCKEditor_3.php'; break; //Multi Columns Form
				case 4: $formRoute = '../app/templates/Forms/formCKEditor_4.php'; break;//No Labels Form
				case 5: $formRoute = '../app/templates/Forms/formCKEditor_5.php'; break; //Floating Labels Form
			}

			/******************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath($formRoute);
			$this->TemplateRender->assign('nameID',       $nameID);
			$this->TemplateRender->assign('placeholder',  $placeholder);
			$this->TemplateRender->assign('FormCol',      $FormCol);
			$this->TemplateRender->assign('name',         $name);
			$this->TemplateRender->assign('requerido',    $requerido);
			$this->TemplateRender->assign('value',        $value);

			/******************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/******************************************/
			//Imprimir dato
			echo $alerts;

		}
	}
	/*******************************************************************************************************************/
	public function formSelect($Options){
		/*
		*=================================================     Detalles    =================================================
		* Permite crear un input tipo select en base a datos de la base de datos
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formSelect($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'FormAling'   => 1,               //Alineacion del formulario (1 al 5)
		*		'FormCol'     => 8,               //Columnas del formulario (0 al 12)
		*		'Placeholder' => 'Nombre',        //Nombre a mostrar
		*		'Name'        => 'Nombre',        //Nombre del input
		*		'Id'          => 'Identificador', //Identificador del input
		*		'Value'       => 'asd',           //Valor del input
		*		'Required'    => 2,               //Si input es requerido (1 al 2)
		*		'arrData'     => '',              //Datos recibidos
		*	];
		*===================================================================================================================
		*/

		/**********************  Definiciones   **********************/
		$placeholder   = $Options['Placeholder'];
		$name          = $Options['Name'];
		$arrData       = $Options['arrData'];
		$FormAling     = $Options['FormAling'] ?? 1;
		$FormCol       = $Options['FormCol'] ?? 8;
		$identificador = $Options['Id'] ?? $name;
		$value         = $Options['Value'] ?? '';
		$required      = $Options['Required'] ?? 1;

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

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 2);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		$dataReturn = $this->DataValidations->checkData('', $fieldsToCheck, $placeholder, 3);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/******************************************/
			//Verifico si nombre viene de un array
			$nameID = (strpos($identificador, '[]') !== false)
				? str_replace('[]', '', $identificador) . '_' . uniqid()
				: $identificador;

			/******************************************/
			//Valido si es requerido
			$selectProperties = ($required === 2) ? 'required="required"' : '';

			/******************************************/
			//Imprimir dato
			echo $this->selectInputGen($FormAling, $FormCol, $placeholder,$name, $nameID, $value, $selectProperties, $arrData, '');

		}else{
			echo $alerts;
		}
	}
	/*******************************************************************************************************************/
	public function formSelectFilter($Options){
		/*
		*=================================================     Detalles    =================================================
		* Permite crear un input tipo select en base a datos de la base de datos,
		* con un filtro
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formSelectFilter($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'FormAling'        => 1,               //Alineacion del formulario (1 al 5)
		*		'FormCol'          => 8,               //Columnas del formulario (0 al 12)
		*		'Placeholder'      => 'Nombre',        //Nombre a mostrar
		*		'Name'             => 'Nombre',        //Nombre del input
		*		'Id'               => 'Identificador', //Identificador del input
		*		'Value'            => 'asd',           //Valor del input
		*		'Required'         => 2,               //Si input es requerido (1 al 2)
		*		'selectProperties' => '',              //Permite agregar propiedades dentro del elemento
		*		'arrData'          => '',              //Datos recibidos
		*	];
		*===================================================================================================================
		*/

		/**********************  Definiciones   **********************/
		$placeholder      = $Options['Placeholder'];
		$name             = $Options['Name'];
		$arrData          = $Options['arrData'];
		$FormAling        = $Options['FormAling'] ?? 1;
		$FormCol          = $Options['FormCol'] ?? 8;
		$identificador    = $Options['Id'] ?? $name;
		$value            = $Options['Value'] ?? '';
		$required         = $Options['Required'] ?? 1;
		$selectProperties = $Options['selectProperties'] ?? '';
		$BASE             = $Options['BASE'];

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

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 2);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		$dataReturn = $this->DataValidations->checkData('', $fieldsToCheck, $placeholder, 3);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/******************************************/
			//Verifico si nombre viene de un array
			$nameID = (strpos($identificador, '[]') !== false)
				? str_replace('[]', '', $identificador) . '_' . uniqid()
				: $identificador;

			/******************************************/
			//Valido si es requerido
			$selectProperties .= ($required === 2) ? 'required="required"' : '';

			/******************************************/
			//generacion del input
			$input = $this->selectInputGen($FormAling, $FormCol, $placeholder,$name, $nameID, $value, $selectProperties, $arrData, 'select2_Main');

			//validacion si es requerido
			$input .= ($required === 2) ? '<style>#div_'.$nameID.' .select2-container .select2-selection--single {background:url('.$BASE.'/img/required.png) no-repeat 5px center !important;background-color: #fff !important;}</style>' : '';

			/******************************************/
			//Imprimir dato
			echo $input;

		}else{
			echo $alerts;
		}
	}
	/*******************************************************************************************************************/
	public function formSelectGroup($Options){
		/*
		*=================================================     Detalles    =================================================
		* Permite crear un input tipo select en base a datos de la base de datos,
		* agrupando los valores
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formSelectGroup($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'FormAling'   => 1,               //Alineacion del formulario (1 al 5)
		*		'FormCol'     => 8,               //Columnas del formulario (0 al 12)
		*		'Placeholder' => 'Nombre',        //Nombre a mostrar
		*		'Name'        => 'Nombre',        //Nombre del input
		*		'Id'          => 'Identificador', //Identificador del input
		*		'Value'       => 'asd',           //Valor del input
		*		'Required'    => 2,               //Si input es requerido (1 al 2)
		*		'arrData'     => '',              //Datos recibidos
		*	];
		*===================================================================================================================
		*/

		/**********************  Definiciones   **********************/
		$placeholder   = $Options['Placeholder'];
		$name          = $Options['Name'];
		$arrData       = $Options['arrData'];
		$FormAling     = $Options['FormAling'] ?? 1;
		$FormCol       = $Options['FormCol'] ?? 8;
		$identificador = $Options['Id'] ?? $name;
		$value         = $Options['Value'] ?? '';
		$required      = $Options['Required'] ?? 1;

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

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 2);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		$dataReturn = $this->DataValidations->checkData('', $fieldsToCheck, $placeholder, 3);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/******************************************/
			//Verifico si nombre viene de un array
			$nameID = (strpos($identificador, '[]') !== false)
				? str_replace('[]', '', $identificador) . '_' . uniqid()
				: $identificador;

			/******************************************/
			//Valido si es requerido
			$selectProperties = ($required === 2) ? 'required="required"' : '';

			/******************************************/
			//Imprimir dato
			echo $this->selectInputGroupGen($FormAling, $FormCol, $placeholder,$name, $nameID, $value, $selectProperties, $arrData, '');

		}else{
			echo $alerts;
		}
	}
	/*******************************************************************************************************************/
	public function formSelectGroupFilter($Options){
		/*
		*=================================================     Detalles    =================================================
		* Permite crear un input tipo select en base a datos de la base de datos,
		* agrupando los valores, con filtro
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formSelectGroupFilter($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'FormAling'   => 1,               //Alineacion del formulario (1 al 5)
		*		'FormCol'     => 8,               //Columnas del formulario (0 al 12)
		*		'Placeholder' => 'Nombre',        //Nombre a mostrar
		*		'Name'        => 'Nombre',        //Nombre del input
		*		'Id'          => 'Identificador', //Identificador del input
		*		'Value'       => 'asd',           //Valor del input
		*		'Required'    => 2,               //Si input es requerido (1 al 2)
		*		'arrData'     => '',              //Datos recibidos
		*	];
		*===================================================================================================================
		*/

		/**********************  Definiciones   **********************/
		$placeholder   = $Options['Placeholder'];
		$name          = $Options['Name'];
		$arrData       = $Options['arrData'];
		$FormAling     = $Options['FormAling'] ?? 1;
		$FormCol       = $Options['FormCol'] ?? 8;
		$identificador = $Options['Id'] ?? $name;
		$value         = $Options['Value'] ?? '';
		$required      = $Options['Required'] ?? 1;
		$BASE          = $Options['BASE'];

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

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 2);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		$dataReturn = $this->DataValidations->checkData('', $fieldsToCheck, $placeholder, 3);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/******************************************/
			//Verifico si nombre viene de un array
			$nameID = (strpos($identificador, '[]') !== false)
				? str_replace('[]', '', $identificador) . '_' . uniqid()
				: $identificador;

			/******************************************/
			//Valido si es requerido
			$selectProperties = ($required === 2) ? 'required="required"' : '';

			/******************************************/
			//generacion del input
			$input = $this->selectInputGroupGen($FormAling, $FormCol, $placeholder,$name, $nameID, $value, $selectProperties, $arrData, 'select2_Main');

			//validacion si es requerido
			$input .= ($required === 2) ? '<style>#div_'.$nameID.' .select2-container .select2-selection--single {background:url('.$BASE.'/img/required.png) no-repeat 5px center !important;background-color: #fff !important;}</style>' : '';

			/******************************************/
			//Imprimir dato
			echo $input;

		}else{
			echo $alerts;
		}
	}
	/*******************************************************************************************************************/
	public function formSelectMultiple($Options){
		/*
		*=================================================     Detalles    =================================================
		* Permite crear un input tipo select en base a datos de la base de datos,
		* con la opcion de seleccionar varios elementos
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formSelectMultiple($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'FormAling'   => 1,               //Alineacion del formulario (1 al 5)
		*		'FormCol'     => 8,               //Columnas del formulario (0 al 12)
		*		'Placeholder' => 'Nombre',        //Nombre a mostrar
		*		'Name'        => 'Nombre',        //Nombre del input
		*		'Id'          => 'Identificador', //Identificador del input
		*		'Value'       => 'asd',           //Valor del input
		*		'Required'    => 2,               //Si input es requerido (1 al 2)
		*		'arrData'     => '',              //Datos recibidos
		*	];
		*===================================================================================================================
		*/

		/**********************  Definiciones   **********************/
		$placeholder   = $Options['Placeholder'];
		$name          = $Options['Name'];
		$arrData       = $Options['arrData'];
		$FormAling     = $Options['FormAling'] ?? 1;
		$FormCol       = $Options['FormCol'] ?? 8;
		$identificador = $Options['Id'] ?? $name;
		$value         = $Options['Value'] ?? '';
		$required      = $Options['Required'] ?? 1;

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

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 2);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/******************************************/
			//Verifico si nombre viene de un array
			$nameID = (strpos($identificador, '[]') !== false)
				? str_replace('[]', '', $identificador) . '_' . uniqid()
				: $identificador;

			/******************************************/
			// Configura propiedades para select multiple de forma compacta
			$selectProperties = 'multiple="multiple"' . (($required === 2) ? ' required="required"' : '');

			/******************************************/
			//generacion del input
			$input = $this->selectInputGen($FormAling, $FormCol, $placeholder,$name, $nameID, $value, $selectProperties, $arrData, '');
			//ejecuto script
			$input .= '
			<script>
				$(document).ready(function() {
					$("#'.$nameID.'").select2();
				});
			</script>';

			/******************************************/
			//Imprimir dato
			echo $input;

		}else{
			echo $alerts;
		}
	}
	/*******************************************************************************************************************/
	public function formSelectMultipleGroup($Options){
		/*
		*=================================================     Detalles    =================================================
		* Permite crear un input tipo select en base a datos de la base de datos,
		* con la opcion de seleccionar varios elementos, con filtros
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formSelectMultipleGroup($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'FormAling'   => 1,               //Alineacion del formulario (1 al 5)
		*		'FormCol'     => 8,               //Columnas del formulario (0 al 12)
		*		'Placeholder' => 'Nombre',        //Nombre a mostrar
		*		'Name'        => 'Nombre',        //Nombre del input
		*		'Id'          => 'Identificador', //Identificador del input
		*		'Value'       => 'asd',           //Valor del input
		*		'Required'    => 2,               //Si input es requerido (1 al 2)
		*		'arrData'     => '',              //Datos recibidos
		*	];
		*===================================================================================================================
		*/

		/**********************  Definiciones   **********************/
		$placeholder   = $Options['Placeholder'];
		$name          = $Options['Name'];
		$arrData       = $Options['arrData'];
		$FormAling     = $Options['FormAling'] ?? 1;
		$FormCol       = $Options['FormCol'] ?? 8;
		$identificador = $Options['Id'] ?? $name;
		$value         = $Options['Value'] ?? '';
		$required      = $Options['Required'] ?? 1;

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

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 2);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/******************************************/
			//Verifico si nombre viene de un array
			$nameID = (strpos($identificador, '[]') !== false)
				? str_replace('[]', '', $identificador) . '_' . uniqid()
				: $identificador;

			/******************************************/
			// Configura propiedades para select multiple de forma compacta
			$selectProperties = 'multiple="multiple"' . (($required === 2) ? ' required="required"' : '');

			/******************************************/
			//generacion del input
			$input = $this->selectInputGroupGen($FormAling, $FormCol, $placeholder,$name, $nameID, $value, $selectProperties, $arrData, '');
			//ejecuto script
			$input .= '
			<script>
				$(document).ready(function() {
					$("#'.$nameID.'").select2();
				});
			</script>';

			/******************************************/
			//Imprimir dato
			echo $input;

		}else{
			echo $alerts;
		}
	}
	/*******************************************************************************************************************/
	public function formSelectDepend($Options){
		/*
		*=================================================     Detalles    =================================================
		* Permite crear un input tipo select en base a datos de la base de datos,
		* dependiente uno de otro
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formSelectDepend($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'FormAling1'   => 1,               //Alineacion del formulario (1 al 5)
		*		'FormCol1'     => 8,               //Columnas del formulario (0 al 12)
		*		'Placeholder1' => 'Nombre',        //Nombre a mostrar
		*		'Name1'        => 'Nombre',        //Nombre del input
		*		'Id1'          => 'Identificador', //Identificador del input
		*		'Value1'       => 'asd',           //Valor del input
		*		'Required1'    => 2,               //Si input es requerido (1 al 2)
		*		'arrData1'     => '',              //Datos recibidos
		*		'FormAling2'   => 1,               //Alineacion del formulario (1 al 5)
		*		'FormCol2'     => 8,               //Columnas del formulario (0 al 12)
		*		'Placeholder2' => 'Nombre',        //Nombre a mostrar
		*		'Name2'        => 'Nombre',        //Nombre del input
		*		'Id2'          => 'Identificador', //Identificador del input
		*		'Value2'       => 'asd',           //Valor del input
		*		'Required2'    => 2,               //Si input es requerido (1 al 2)
		*		'arrData2'     => '',              //Datos recibidos
		*		'FormName'     => '',              //Nombre del formulario
		*	];
		*===================================================================================================================
		*/

		/**********************  Definiciones   **********************/
		$placeholder1   = $Options['Placeholder1'];
		$name1          = $Options['Name1'];
		$arrData1       = $Options['arrData1'];
		$FormAling1     = $Options['FormAling1'] ?? 1;
		$FormCol1       = $Options['FormCol1'] ?? 8;
		$identificador1 = $Options['Id1'] ?? $name1;
		$value1         = $Options['Value1'] ?? '';
		$required1      = $Options['Required1'] ?? 1;
		$placeholder2   = $Options['Placeholder2'];
		$name2          = $Options['Name2'];
		$arrData2       = $Options['arrData2'];
		$FormAling2     = $Options['FormAling2'] ?? 1;
		$FormCol2       = $Options['FormCol2'] ?? 8;
		$identificador2 = $Options['Id2'] ?? $name2;
		$value2         = $Options['Value2'] ?? '';
		$required2      = $Options['Required2'] ?? 1;
		$form_name      = $Options['FormName'] ?? 'form1';

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

		// Validaciones numéricas y enteras
		$fieldsToCheck = [
			['value' => $value1, 'method' => 'validarNumero',  'label' => '$value1', 'msg' => 'no es un numero',          'placeholder' => $placeholder1],
			['value' => $value1, 'method' => 'validarEntero',  'label' => '$value1', 'msg' => 'no es un numero entero',   'placeholder' => $placeholder2],
			['value' => $value2, 'method' => 'validarNumero',  'label' => '$value2', 'msg' => 'no es un numero',          'placeholder' => $placeholder1],
			['value' => $value2, 'method' => 'validarEntero',  'label' => '$value2', 'msg' => 'no es un numero entero',   'placeholder' => $placeholder2],
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, '', 4);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		$dataReturn = $this->DataValidations->checkData('', $fieldsToCheck, '', 5);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/******************************************/
			//Verifico si nombre viene de un array
			$nameID1 = (strpos($identificador1, '[]') !== false)
				? str_replace('[]', '', $identificador1) . '_' . uniqid()
				: $identificador1;
			//Verifico si nombre viene de un array
			$nameID2 = (strpos($identificador2, '[]') !== false)
				? str_replace('[]', '', $identificador2) . '_' . uniqid()
				: $identificador2;

			/******************************************/
			//Valido si es requerido
			$selectProperties1 = ($required1 === 2) ? 'required="required"' : '';
			$selectProperties2 = ($required2 === 2) ? 'required="required"' : '';

			/******************************************/
			//generacion del input
			$input  = $this->selectInputGen($FormAling1, $FormCol1, $placeholder1,$name1, $nameID1, $value1, $selectProperties1, $arrData1, '');
			$input .= $this->selectInputEmpty($FormAling2, $FormCol2, $placeholder2,$name2, $nameID2, $selectProperties2);
			$input .= $this->selectInputScript($arrData2, $value2, $nameID1, $nameID2, $form_name, $FormAling2);

			/******************************************/
			//Imprimir dato
			echo $input;

		}else{
			echo $alerts;
		}
	}
	/*******************************************************************************************************************/
	public function formSelectDependFilter($Options){
		/*
		*=================================================     Detalles    =================================================
		* Permite crear un input tipo select en base a datos de la base de datos,
		* dependiente uno de otro, con filtros
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formSelectDependFilter($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'FormAling1'   => 1,               //Alineacion del formulario (1 al 5)
		*		'FormCol1'     => 8,               //Columnas del formulario (0 al 12)
		*		'Placeholder1' => 'Nombre',        //Nombre a mostrar
		*		'Name1'        => 'Nombre',        //Nombre del input
		*		'Id1'          => 'Identificador', //Identificador del input
		*		'Value1'       => 'asd',           //Valor del input
		*		'Required1'    => 2,               //Si input es requerido (1 al 2)
		*		'arrData1'     => '',              //Datos recibidos
		*		'FormAling2'   => 1,               //Alineacion del formulario (1 al 5)
		*		'FormCol2'     => 8,               //Columnas del formulario (0 al 12)
		*		'Placeholder2' => 'Nombre',        //Nombre a mostrar
		*		'Name2'        => 'Nombre',        //Nombre del input
		*		'Id2'          => 'Identificador', //Identificador del input
		*		'Value2'       => 'asd',           //Valor del input
		*		'Required2'    => 2,               //Si input es requerido (1 al 2)
		*		'arrData2'     => '',              //Datos recibidos
		*		'FormName'     => '',              //Nombre del formulario
		*	];
		*===================================================================================================================
		*/

		/**********************  Definiciones   **********************/
		$placeholder1   = $Options['Placeholder1'];
		$name1          = $Options['Name1'];
		$arrData1       = $Options['arrData1'];
		$FormAling1     = $Options['FormAling1'] ?? 1;
		$FormCol1       = $Options['FormCol1'] ?? 8;
		$identificador1 = $Options['Id1'] ?? $name1;
		$value1         = $Options['Value1'] ?? '';
		$required1      = $Options['Required1'] ?? 1;
		$placeholder2   = $Options['Placeholder2'];
		$name2          = $Options['Name2'];
		$arrData2       = $Options['arrData2'];
		$FormAling2     = $Options['FormAling2'] ?? 1;
		$FormCol2       = $Options['FormCol2'] ?? 8;
		$identificador2 = $Options['Id2'] ?? $name2;
		$value2         = $Options['Value2'] ?? '';
		$required2      = $Options['Required2'] ?? 1;
		$form_name      = $Options['FormName'] ?? 'form1';
		$BASE           = $Options['BASE'];

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

		// Validaciones numéricas y enteras
		$fieldsToCheck = [
			['value' => $value1, 'method' => 'validarNumero',  'label' => '$value1', 'msg' => 'no es un numero',          'placeholder' => $placeholder1],
			['value' => $value1, 'method' => 'validarEntero',  'label' => '$value1', 'msg' => 'no es un numero entero',   'placeholder' => $placeholder2],
			['value' => $value2, 'method' => 'validarNumero',  'label' => '$value2', 'msg' => 'no es un numero',          'placeholder' => $placeholder1],
			['value' => $value2, 'method' => 'validarEntero',  'label' => '$value2', 'msg' => 'no es un numero entero',   'placeholder' => $placeholder2],
		];

		/**********************  Validaciones   **********************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, '', 4);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		$dataReturn = $this->DataValidations->checkData('', $fieldsToCheck, '', 5);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/******************************************/
			//Verifico si nombre viene de un array
			$nameID1 = (strpos($identificador1, '[]') !== false)
				? str_replace('[]', '', $identificador1) . '_' . uniqid()
				: $identificador1;
			//Verifico si nombre viene de un array
			$nameID2 = (strpos($identificador2, '[]') !== false)
				? str_replace('[]', '', $identificador2) . '_' . uniqid()
				: $identificador2;

			/******************************************/
			//Valido si es requerido
			$selectProperties1 = ($required1 === 2) ? 'required="required"' : '';
			$selectProperties2 = ($required2 === 2) ? 'required="required"' : '';

			/******************************************/
			//generacion del input
			$input  = $this->selectInputGen($FormAling1, $FormCol1, $placeholder1,$name1, $nameID1, $value1, $selectProperties1, $arrData1, 'select2_Main');
			$input .= $this->selectInputEmpty($FormAling2, $FormCol2, $placeholder2,$name2, $nameID2, $selectProperties2);
			$input .= $this->selectInputScript($arrData2, $value2, $nameID1, $nameID2, $form_name, $FormAling2);

			//validacion si es requerido
			$input .= ($required1 === 2) ? '<style>#div_'.$nameID1.' .select2-container .select2-selection--single {background:url('.$BASE.'/img/required.png) no-repeat 5px center !important;background-color: #fff !important;}</style>' : '';
			$input .= ($required2 === 2) ? '<style>#div_'.$nameID2.' .select2-container .select2-selection--single {background:url('.$BASE.'/img/required.png) no-repeat 5px center !important;background-color: #fff !important;}</style>' : '';

			/******************************************/
			//Imprimir dato
			echo $input;

		}else{
			echo $alerts;
		}
	}
	/*******************************************************************************************************************/
	public function formSelectCountry($Options){
		/*
		*=================================================     Detalles    =================================================
		* Permite crear un input select que trae los paises
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formSelectCountry($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'FormAling'   => 1,               //Alineacion del formulario (1 al 5)
		*		'FormCol'     => 8,               //Columnas del formulario (0 al 12)
		*		'Placeholder' => 'Nombre',        //Nombre a mostrar
		*		'Name'        => 'Nombre',        //Nombre del input
		*		'Id'          => 'Identificador', //Identificador del input
		*		'Value'       => 'asd',           //Valor del input
		*		'Required'    => 2,               //Si input es requerido (1 al 2)
		*	];
		*===================================================================================================================
		*/

		/**********************  Definiciones   **********************/
		$placeholder   = $Options['Placeholder'];
		$name          = $Options['Name'];
		$FormAling     = $Options['FormAling'] ?? 1;
		$FormCol       = $Options['FormCol'] ?? 8;
		$identificador = $Options['Id'] ?? $name;
		$value         = $Options['Value'] ?? '';
		$required      = $Options['Required'] ?? 1;
		$BASE          = $Options['BASE'];

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

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 2);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		$dataReturn = $this->DataValidations->checkData('', $fieldsToCheck, $placeholder, 3);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/******************************************/
			//Verifico si nombre viene de un array
			$nameID = (strpos($identificador, '[]') !== false)
				? str_replace('[]', '', $identificador) . '_' . uniqid()
				: $identificador;

			/******************************************/
			//Valido si es requerido
			$requerido = ($required === 2) ? 'required="required"' : '';

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

			/******************************************/
			//generacion del input
			switch ($FormAling) {
				case 1: $formRoute = '../app/templates/Forms/formSelectCountry_1.php'; $this->TemplateRender->assign('otrcol', (12 - $FormCol)); break; //Horizontal Form
				case 2: $formRoute = '../app/templates/Forms/formSelectCountry_2.php'; break; //Vertical Form
				case 3: $formRoute = '../app/templates/Forms/formSelectCountry_3.php'; break; //Multi Columns Form
				case 4: $formRoute = '../app/templates/Forms/formSelectCountry_4.php'; break; //No Labels Form
				case 5: $formRoute = '../app/templates/Forms/formSelectCountry_5.php'; break; //Floating Labels Form
			}
			/*******************************************/
			//Variables
			$selectedx = 'selected="selected"';
			$Options   = '';
			//Recorro
			foreach ($arrData as $select) {
				$isSelected = ($value == $select['value']) ? 'selected="selected"' : '';
				if ($isSelected) $selectedx = '';
				$Options .= '<option value="'.$select['value'].'" '.$isSelected.'>'.$select['Nombre'].'</option>';
			}
			//Se generan opciones
			$FormOptions  = '<option value="" '.$selectedx.'>Seleccione una Opción</option>';
			$FormOptions .= $Options;
			//validacion si es requerido
			$dataRequire = ($required === 2) ? '<style>#div_'.$nameID.' .select2-container .select2-selection--single {background:url('.$BASE.'/img/required.png) no-repeat 5px center !important;background-color: #fff !important;}</style>' : '';

			/******************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath($formRoute);
			$this->TemplateRender->assign('nameID',      $nameID);
			$this->TemplateRender->assign('placeholder', $placeholder);
			$this->TemplateRender->assign('FormCol',     $FormCol);
			$this->TemplateRender->assign('name',        $name);
			$this->TemplateRender->assign('requerido',   $requerido);
			$this->TemplateRender->assign('FormOptions', $FormOptions);
			$this->TemplateRender->assign('dataRequire', $dataRequire);

			/******************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/******************************************/
			//Imprimir dato
			echo $alerts;

		}
	}
	/*******************************************************************************************************************/
	public function formSelectnAuto($Options){
		/*
		*=================================================     Detalles    =================================================
		* Permite crear un input tipo select que contiene numeros enteros secuenciales
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formSelectnAuto($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'FormAling'   => 1,               //Alineacion del formulario (1 al 5)
		*		'FormCol'     => 8,               //Columnas del formulario (0 al 12)
		*		'Placeholder' => 'Nombre',        //Nombre a mostrar
		*		'Name'        => 'Nombre',        //Nombre del input
		*		'Id'          => 'Identificador', //Identificador del input
		*		'Value'       => 'asd',           //Valor del input
		*		'Required'    => 2,               //Si input es requerido (1 al 2)
		*		'ValorInicio' => '',              //Valor Inicio
		*		'ValorFin'    => '',              //Valor Fin
		*	];
		*===================================================================================================================
		*/

		/**********************  Definiciones   **********************/
		$placeholder   = $Options['Placeholder'];
		$name          = $Options['Name'];
		$valor_ini     = $Options['ValorInicio'];
		$valor_fin     = $Options['ValorFin'];
		$FormAling     = $Options['FormAling'] ?? 1;
		$FormCol       = $Options['FormCol'] ?? 8;
		$identificador = $Options['Id'] ?? $name;
		$value         = $Options['Value'] ?? '';
		$required      = $Options['Required'] ?? 1;

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

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 2);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		$dataReturn = $this->DataValidations->checkData('', $fieldsToCheck, $placeholder, 3);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/******************************************/
			//Verifico si nombre viene de un array
			$nameID = (strpos($identificador, '[]') !== false)
				? str_replace('[]', '', $identificador) . '_' . uniqid()
				: $identificador;

			/******************************************/
			//Valido si es requerido
			$requerido = ($required === 2) ? 'required="required"' : '';

			/******************************************/
			//generacion del input
			switch ($FormAling) {
				case 1: $formRoute = '../app/templates/Forms/formSelectNAuto_1.php'; $this->TemplateRender->assign('otrcol', (12 - $FormCol)); break; //Horizontal Form
				case 2: $formRoute = '../app/templates/Forms/formSelectNAuto_2.php'; break; //Vertical Form
				case 3: $formRoute = '../app/templates/Forms/formSelectNAuto_3.php'; break; //Multi Columns Form
				case 4: $formRoute = '../app/templates/Forms/formSelectNAuto_4.php'; break; //No Labels Form
				case 5: $formRoute = '../app/templates/Forms/formSelectNAuto_5.php'; break; //Floating Labels Form
			}
			/*******************************************/
			//Variables
			$selectedx = 'selected="selected"';
			$Options   = '';
			//Recorro
			for ($ini = $valor_ini; $ini <= $valor_fin; $ini++) {
				$isSelected = ($value == $ini) ? 'selected="selected"' : '';
				if ($isSelected) $selectedx = '';
				$Options .= '<option value="'.$ini.'" '.$isSelected.'>'.$ini.'</option>';
			}
			$FormOptions  = '<option value="" '.$selectedx.'>Seleccione una Opción</option>';
			$FormOptions .= $Options;

			/******************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath($formRoute);
			$this->TemplateRender->assign('nameID',       $nameID);
			$this->TemplateRender->assign('placeholder',  $placeholder);
			$this->TemplateRender->assign('name',         $name);
			$this->TemplateRender->assign('requerido',    $requerido);
			$this->TemplateRender->assign('FormCol',      $FormCol);
			$this->TemplateRender->assign('FormOptions',  $FormOptions);

			/******************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/******************************************/
			//Imprimir dato
			echo $alerts;

		}
	}
	/*******************************************************************************************************************/
	public function formUploadMultiple($Options){
		/*
		*=================================================     Detalles    =================================================
		* Permite crear un input tipo upload, condicionado a la cantidad y al
		* tipo de archivo
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formUploadMultiple($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'Placeholder' => 'Nombre',                      //Nombre a mostrar
		*		'Name'        => 'Nombre',                      //Nombre del input
		*		'MaxFiles'    => 2,                             //Numero de Archivos
		*		'TypeFiles'   => '"jpg", "png", "gif", "jpeg"'  //Tipos de archivos a permitir
		*	];
		*===================================================================================================================
		*/

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

		$dataReturn = $this->DataValidations->checkData('', $fieldsToCheck, $placeholder, 3);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/******************************************/
			//Verifico si es mas de un archivo
			$ndat = (isset($max_files) && $max_files != 1) ? '[]' : '';

			/******************************************/
			//Mostrar Maximo de archivos
			$s_msg    = '<strong><i class="fa fa-file-o" aria-hidden="true"></i> Maximo de Archivos Permitidos: </strong>'.$max_files.'<br/>';
			$s_msg   .= '<strong><i class="fa fa-file-o" aria-hidden="true"></i> Extensiones de Archivos Permitidos: </strong><br/>'.$type_files;
			$Alertas  = $this->Alertas->alertPostData(6, 4, 'exclamation-circle', 0, $s_msg);

			/******************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath('../app/templates/Forms/formMultipleUpload_1.php');
			$this->TemplateRender->assign('Alertas',      $Alertas);
			$this->TemplateRender->assign('name',         $name);
			$this->TemplateRender->assign('placeholder',  $placeholder);
			$this->TemplateRender->assign('ndat',         $ndat);
			$this->TemplateRender->assign('type_files',   $type_files);
			$this->TemplateRender->assign('max_files',    $max_files);

			/******************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/******************************************/
			//Imprimir dato
			echo $alerts;

		}
	}
	/*******************************************************************************************************************/
	public function formTermsAndConditions($Options){
		/*
		*=================================================     Detalles    =================================================
		* Permite crear un input check que desactiva el boton submit a menos que
		* sea marcado, tambien agrega un enlace que permite que sea leido
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formTermsAndConditions($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'FormCol'     => 8,         //Columnas del formulario (0 al 12)
		*		'NameID'      => 'NameID',  //Identificador
		*		'Color'       => 'Color',   //Color input
		*		'Inicio'      => 'Inicio',  //Texto Inicio
		*		'Fin'         => 'Fin',     //Texto Fin
		*		'Link'        => 'Link',    //Enlace
		*		'NombreBoton' => '',        //Nombre del boton
		*	];
		*===================================================================================================================
		*/

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

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, '', 6);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/******************************************/
			//Selecciono el tipo de mensaje
			$options = ['default', 'primary', 'success', 'danger', 'warning', 'info'];
			$tipo    = $options[$color-1];

			/******************************************/
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

			/******************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/******************************************/
			//Imprimir dato
			echo $alerts;

		}
	}
	/*******************************************************************************************************************/
	public function formConditionalSubmit($Options){
		/*
		*=================================================     Detalles    =================================================
		* Permite crear un input check que desactiva el boton submit a menos que
		* sea marcado
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formConditionalSubmit($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'FormCol'     => 8,         //Columnas del formulario (0 al 12)
		*		'NameID'      => 'NameID',  //Identificador
		*		'Color'       => 'Color',   //Color input
		*		'Text'        => 'Text',    //Texto
		*		'NombreBoton' => '',        //Nombre del boton
		*	];
		*===================================================================================================================
		*/

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

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, '', 6);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/******************************************/
			//Selecciono el tipo de mensaje
			$options = ['default', 'primary', 'success', 'danger', 'warning', 'info'];
			$tipo    = $options[$color-1];

			/******************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath('../app/templates/Forms/formCondicionalSubmit_1.php');
			$this->TemplateRender->assign('otrcol',      (12 - $FormCol));
			$this->TemplateRender->assign('nameID',      $nameID);
			$this->TemplateRender->assign('FormCol',     $FormCol);
			$this->TemplateRender->assign('tipo',        $tipo);
			$this->TemplateRender->assign('Text',        $Text);
			$this->TemplateRender->assign('submitName',  $submitName);

			/******************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/******************************************/
			//Imprimir dato
			echo $alerts;

		}
	}
	/*******************************************************************************************************************/
	public function formInputDatalist($Options){
		/*
		*=================================================     Detalles    =================================================
		* Permite crear un input tipo que al llenar datos muestra opciones ya
		* alojadas en la BBDD
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formInputDatalist($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'FormAling'   => 1,               //Alineacion del formulario (1 al 5)
		*		'FormCol'     => 8,               //Columnas del formulario (0 al 12)
		*		'Placeholder' => 'Nombre',        //Nombre a mostrar
		*		'Name'        => 'Nombre',        //Nombre del input
		*		'Id'          => 'Identificador', //Identificador del input
		*		'Value'       => 'asd',           //Valor del input
		*		'Required'    => 1,               //Si input es requerido (1 al 3)
		*		'InputClass'  => '',              //Clase extra
		*		'Icon'        => '',              //Icono a mostrar
		*		'arrData'     => '',              //Datos recibidos
		*	];
		*===================================================================================================================
		*/

		/**********************  Definiciones   **********************/
		$placeholder   = $Options['Placeholder'];
		$name          = $Options['Name'];
		$arrData       = $Options['arrData'];
		$FormAling     = $Options['FormAling'] ?? 1;
		$FormCol       = $Options['FormCol'] ?? 8;
		$identificador = $Options['Id'] ?? $name;
		$value         = $Options['Value'] ?? '';
		$required      = $Options['Required'] ?? 1;
		$InputClass    = $Options['InputClass'] ?? '';
		$Icono         = $Options['Icon'] ?? '';

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

		$dataReturn = $this->DataValidations->checkData($validOptions, $optionsToCheck, $placeholder, 2);
		$errorn += $dataReturn['nErrors'];
		$alerts .= $dataReturn['alerts'];

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){

			/******************************************/
			//Verifico si nombre viene de un array
			$nameID = (strpos($identificador, '[]') !== false)
				? str_replace('[]', '', $identificador) . '_' . uniqid()
				: $identificador;

			/******************************************/
			//Valido si es requerido
			switch ($required) {
				case 1:$requerido = '';                    break;//Si el dato no es requerido
				case 2:$requerido = 'required="required"'; break;//Si el dato es requerido
				case 3:$requerido = 'disabled';            break;//Si el dato esta desactivado
			}

            /******************************************/
			//Verifico si se utiliza el icono
			$input_1 = $input_2 = $input_3 = '';
			if (!empty($Icono)&&$Icono!='') {
				$input_1 = '<div class="input-group"><span class="input-group-text" id="basic-addon1"><i class="'.$Icono.'"></i></span>';
                $input_2 = 'aria-describedby="basic-addon1"';
                $input_3 = '</div>';
			}

			/******************************************/
			//generacion del input
			/******************************************/
			//generacion del input
			switch ($FormAling) {
				case 1: $formRoute = '../app/templates/Forms/formInputDatalist_1.php'; $this->TemplateRender->assign('otrcol', (12 - $FormCol)); break; //Horizontal Form
				case 2: $formRoute = '../app/templates/Forms/formInputDatalist_2.php'; break; //Vertical Form
				case 3: $formRoute = '../app/templates/Forms/formInputDatalist_3.php'; break; //Multi Columns Form
				case 4: $formRoute = '../app/templates/Forms/formInputDatalist_4.php'; break; //No Labels Form
				case 5: $formRoute = '../app/templates/Forms/formInputDatalist_5.php'; break; //Floating Labels Form
			}

			/******************************************/
			//Se genera input
			$dataList = $this->inputDatalist($name, $arrData);

			/******************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath($formRoute);
			$this->TemplateRender->assign('nameID',      $nameID);
			$this->TemplateRender->assign('name',        $name);
			$this->TemplateRender->assign('placeholder', $placeholder);
			$this->TemplateRender->assign('FormCol',     $FormCol);
			$this->TemplateRender->assign('value',       $value);
			$this->TemplateRender->assign('requerido',   $requerido);
			$this->TemplateRender->assign('InputClass',  $InputClass);
			$this->TemplateRender->assign('input_1',     $input_1);
			$this->TemplateRender->assign('input_2',     $input_2);
			$this->TemplateRender->assign('input_3',     $input_3);
			$this->TemplateRender->assign('dataList',    $dataList);

			/******************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/******************************************/
			//Imprimir dato
			echo $alerts;

		}
	}
	/*******************************************************************************************************************/
	public function formDetails($arrData){
		/*
		*=================================================     Detalles    =================================================
		* Permite crear un elemento details que despliega informacion
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formDetails($arrData);
		*
		*=================================================    Parametros   =================================================
		*	$arrData = [
		*		'ID'      => 'Nombre',  //Nombre del input
		*		'Nombre'  => 'URL',     //URL a redireccionar
		*	]
		*===================================================================================================================
		*/

		/********************************************************/
		//Definicion de errores
		$errorn = 0;
		$alerts = '';

		/********************** Si todo esta ok **********************/
		//Ejecucion si no hay errores
		if($errorn==0){
			/******************************************/
			//inicial
			$dataDetails = '';
			//Recorro
			foreach ( $arrData as $select ) {$dataDetails .= '<details><summary>'.$select['ID'].'</summary><p>'.$this->DataText->tituloMenu($select['Nombre']).'</p></details>';}
			/******************************************/
			//Se agregan datos
			$this->TemplateRender->templatePath('../app/templates/Forms/formDetails_1.php');
			$this->TemplateRender->assign('dataDetails', $dataDetails);

			/******************************************/
			//Imprimir dato
			echo $this->TemplateRender->render();

		}else{

			/******************************************/
			//Imprimir dato
			echo $alerts;

		}
	}
	/*******************************************************************************************************************/
	public function formUploadIMG($Options){
		/*
		*=================================================     Detalles    =================================================
		* Permite crear un input tipo subida de imagen, con un popup que selecciona
		* un area activa, la recorta y la entrega para su guardado
		*=================================================    Modo de uso  =================================================
		*
		* 	//se imprime input
		* 	$Form->formUploadIMG($Options);
		*
		*=================================================    Parametros   =================================================
		*	$Options = [
		*		'Name'      => 'Nombre',     //Nombre del input
		*		'URL'       => 'URL',        //URL a redireccionar
		*		'ExtraData' => 'ExtraData',  //Datos extras aparte de la imagen
		*	]
		*===================================================================================================================
		*/

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

		/******************************************/
		//Imprimir dato
		echo $this->TemplateRender->render();

	}




}

