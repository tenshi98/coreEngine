<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6">

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Floating labels Form</h5>

            <!-- Floating Labels Form -->
            <form id="form5" name="form5" class="row g-3" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data" aria-label="Formulario de ejecucion">

                <?php
                /***********************************/
                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Inputs Normales', 'Clase' => 'box-title text-color-red-dark']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 1,'FormAling' => 5,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'Texto normal',      'Name' => 'Nombre',            'Id' => 'IDInput_5_1', 'Required' => 2, 'DataInfo' => 'Lorem ipsum dolor sit amet,']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 1,'FormAling' => 5,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'Input desactivado', 'Name' => 'Nombre',            'Id' => 'IDInput_5_2', 'Required' => 3, 'DataInfo' => 'Lorem ipsum dolor sit amet,']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 2,'FormAling' => 5,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'Email',             'Name' => 'email',             'Id' => 'IDInput_5_3', 'Required' => 2, 'DataInfo' => 'Lorem ipsum dolor sit amet,','Icon' => 'bx bx-mail-send']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 3,'FormAling' => 5,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'Contraseñas',       'Name' => 'password',          'Id' => 'IDInput_5_4', 'Required' => 2, 'DataInfo' => 'Lorem ipsum dolor sit amet,','Icon' => 'bi bi-key']);
                $data['Fnc_FormInputs']->formInputDatalist([        'FormAling' => 5,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'Input Datalist',    'Name' => 'formInputDatalist', 'Id' => 'IDInput_5_29','Required' => 2, 'DataInfo' => 'Lorem ipsum dolor sit amet,','Icon' => 'bi bi-server','arrData' => $data['arrCiudad']]);

                /***********************************/
                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Inputs Especificos', 'Clase' => 'box-title text-color-red-dark']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 11,'FormAling' => 5,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'Rut','Name' => 'rut','Id' => 'IDInput_5_5','Required' => 2,'Icon' => 'bi bi-person-circle']);

                /***********************************/
                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Inputs Numericos', 'Clase' => 'box-title text-color-red-dark']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 4,'FormAling' => 5,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'Numeros enteros positivos', 'Name' => 'Numeros1',      'Id' => 'IDInput_5_6','Required' => 2,'Icon' => 'bi bi-sort-numeric-down']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 5,'FormAling' => 5,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'Numeros reales',            'Name' => 'Numeros2',      'Id' => 'IDInput_5_7','Required' => 2,'Icon' => 'bi bi-sort-numeric-down']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 6,'FormAling' => 5,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'Numeros enteros',           'Name' => 'Numeros3',      'Id' => 'IDInput_5_8','Required' => 2,'Icon' => 'bi bi-sort-numeric-down']);
                $data['Fnc_FormInputs']->formNumberSpinner([        'FormAling' => 5,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'number_spinner',            'Name' => 'number_spinner','Id' => 'IDInput_5_9','Required' => 2,'Min' => 1,'Max' => 20,'Step' => 1,'Ndecimal' => 0]);

                /***********************************/
                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Inputs Fecha y Hora', 'Clase' => 'box-title text-color-red-dark']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 7, 'FormAling' => 5,'FormCol' => 12, 'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'date',            'Name' => 'date',             'Id' => 'IDInput_5_10','Required' => 2,'Icon' => 'bi bi-calendar-date']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 8, 'FormAling' => 5,'FormCol' => 12, 'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'form_input_date', 'Name' => 'form_input_date',  'Id' => 'IDInput_5_11','Required' => 2,'Icon' => 'bi bi-calendar-date']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 9, 'FormAling' => 5,'FormCol' => 6,  'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'time',            'Name' => 'time',             'Id' => 'IDInput_5_12','Required' => 2,'Icon' => 'bi bi-clock']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 10,'FormAling' => 5,'FormCol' => 6,  'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'form_time_picker','Name' => 'form_time_picker', 'Id' => 'IDInput_5_13','Required' => 2,'Icon' => 'bi bi-clock']);
                $data['Fnc_FormInputs']->formTime([                  'FormAling' => 5,'FormCol' => 6,  'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'formTime',        'Name' => 'formTime',         'Id' => 'IDInput_5_14','Required' => 2,'Position' => 1,'Icon' => 'bi bi-clock']);

                /***********************************/
                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Inputs Funcionales', 'Clase' => 'box-title text-color-red-dark']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 12,'FormAling' => 5,'FormCol' => 6,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'Color',            'Name' => 'Color',            'Id' => 'IDInput_5_15','Required' => 2,'InputClass'  => 'form-control-color','Icon' => 'bi bi-pencil']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 13,'FormAling' => 5,'FormCol' => 6,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'form_color_picker','Name' => 'form_color_picker','Id' => 'IDInput_5_16','Required' => 2,'Icon' => 'bi bi-pencil']);

                /***********************************/
                //Variables
                $xvalue  = '5';
                $xvalue2 = '';
                //input
                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Selects', 'Clase' => 'box-title text-color-red-dark']);
                $data['Fnc_FormInputs']->formSelect([               'FormAling' => 5,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'formSelect',               'Name' => 'formSelect',                 'Id' => 'IDInput_5_17','Value' => $xvalue,'Required' => 2,'arrData' => $data['arrCiudad']]);
                $data['Fnc_FormInputs']->formSelectFilter([         'FormAling' => 5,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'formSelectFilter',         'Name' => 'formSelectFilter',           'Id' => 'IDInput_5_18','Value' => $xvalue,'Required' => 2,'arrData' => $data['arrCiudad'], 'BASE' => $BASE]);
                $data['Fnc_FormInputs']->formSelectGroup([          'FormAling' => 5,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'formSelectGroup',          'Name' => 'formSelectGroup',            'Id' => 'IDInput_5_19','Value' => $xvalue,'Required' => 2,'arrData' => $data['arrGroup']]);
                $data['Fnc_FormInputs']->formSelectGroupFilter([    'FormAling' => 5,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'formSelectGroupFilter',    'Name' => 'formSelectGroupFilter',      'Id' => 'IDInput_5_20','Value' => $xvalue,'Required' => 2,'arrData' => $data['arrGroup'], 'BASE' => $BASE]);
                $data['Fnc_FormInputs']->formSelectMultiple([       'FormAling' => 5,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'form_multiple1',           'Name' => 'form_multiple2[]',           'Id' => 'IDInput_5_21','Value' => $xvalue,'Required' => 2,'arrData' => $data['arrCiudad']]);
                $data['Fnc_FormInputs']->formSelectMultipleGroup([  'FormAling' => 5,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'formSelectMultipleGroup',  'Name' => 'formSelectMultipleGroup[]',  'Id' => 'IDInput_5_22','Value' => $xvalue,'Required' => 2,'arrData' => $data['arrGroup']]);

                $data['Fnc_FormInputs']->formTittle(['Tipo' => 5,'Texto' => 'formSelectDepend', 'Clase' => 'box-title text-color-red-dark']);
                $data['Fnc_FormInputs']->formSelectDepend([  'FormAling1' => 2,'FormCol1' => 12,'PlaceholderIcon1' => 'bi bi-person', 'Placeholder1' => 'Ciudad','Name1' => 'idCiudad','Id1' => 'IDInput_5_23','Value1' => $xvalue2,'Required1' => 2,'arrData1' => $data['arrCiudad'],
                                                             'FormAling2' => 2,'FormCol2' => 12,'PlaceholderIcon2' => 'bi bi-person', 'Placeholder2' => 'Comuna','Name2' => 'idComuna','Id2' => 'IDInput_5_24','Value2' => $xvalue2,'Required2' => 2,'arrData2' => $data['arrComuna'],]);
                $data['Fnc_FormInputs']->formTittle(['Tipo' => 5,'Texto' => 'formSelectDependFilter', 'Clase' => 'box-title text-color-red-dark']);
                $data['Fnc_FormInputs']->formSelectDependFilter(['FormAling1' => 2,'FormCol1' => 12,'PlaceholderIcon1' => 'bi bi-person', 'Placeholder1' => 'Ciudad','Name1' => 'idCiudad','Id1' => 'IDInput_5_25','Value1' => $xvalue2,'Required1' => 2,'arrData1' => $data['arrCiudad'],
                                                                 'FormAling2' => 2,'FormCol2' => 12,'PlaceholderIcon2' => 'bi bi-person', 'Placeholder2' => 'Comuna','Name2' => 'idComuna','Id2' => 'IDInput_5_26','Value2' => $xvalue2,'Required2' => 2,'arrData2' => $data['arrComuna'],
                                                                 'BASE' => $BASE]);
                $data['Fnc_FormInputs']->formSelectCountry([ 'FormAling' => 5,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'formSelectCountry','Name' => 'formSelectCountry','Id' => 'IDInput_5_27','Value' => $xvalue2,'Required' => 2, 'BASE' => $BASE]);
                $data['Fnc_FormInputs']->formSelectnAuto([   'FormAling' => 5,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'formSelectnAuto', 'Name' => 'formSelectnAuto', 'Id' => 'IDInput_5_28','Value' => $xvalue, 'Required' => 2,'ValorInicio' => 1,'ValorFin' => 25]);


                ?>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form><!-- End floating Labels Form -->

        </div>
    </div>

</div>
