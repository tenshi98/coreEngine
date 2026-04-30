<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6">

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">No Labels / Placeholders as labels Form</h5>

            <!-- No Labels Form -->
            <form id="form4" name="form4" class="row g-3" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data" aria-label="Formulario de ejecucion">

                <?php
                /***********************************/
                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Inputs Normales', 'Clase' => 'box-title text-color-red-dark']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 1,'FormAling' => 4,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'Texto normal',      'Name' => 'Nombre',            'Id' => 'IDInput_4_1', 'Required' => 2, 'DataInfo' => 'Lorem ipsum dolor sit amet,']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 1,'FormAling' => 4,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'Input desactivado', 'Name' => 'Nombre',            'Id' => 'IDInput_4_2', 'Required' => 3, 'DataInfo' => 'Lorem ipsum dolor sit amet,']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 2,'FormAling' => 4,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'Email',             'Name' => 'email',             'Id' => 'IDInput_4_3', 'Required' => 2, 'DataInfo' => 'Lorem ipsum dolor sit amet,','Icon' => 'bx bx-mail-send']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 3,'FormAling' => 4,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'Contraseñas',       'Name' => 'password',          'Id' => 'IDInput_4_4', 'Required' => 2, 'DataInfo' => 'Lorem ipsum dolor sit amet,','Icon' => 'bi bi-key']);
                $data['Fnc_FormInputs']->formInputDatalist([        'FormAling' => 4,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'Input Datalist',    'Name' => 'formInputDatalist', 'Id' => 'IDInput_4_29','Required' => 2, 'DataInfo' => 'Lorem ipsum dolor sit amet,','Icon' => 'bi bi-server','arrData' => $data['arrCiudad']]);

                /***********************************/
                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Inputs Especificos', 'Clase' => 'box-title text-color-red-dark']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 11,'FormAling' => 4,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'Rut','Name' => 'rut','Id' => 'IDInput_4_5','Required' => 2,'Icon' => 'bi bi-person-circle']);

                /***********************************/
                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Inputs Numericos', 'Clase' => 'box-title text-color-red-dark']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 4,'FormAling' => 4,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'Numeros enteros positivos', 'Name' => 'Numeros1',      'Id' => 'IDInput_4_6','Required' => 2,'Icon' => 'bi bi-sort-numeric-down']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 5,'FormAling' => 4,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'Numeros reales',            'Name' => 'Numeros2',      'Id' => 'IDInput_4_7','Required' => 2,'Icon' => 'bi bi-sort-numeric-down']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 6,'FormAling' => 4,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'Numeros enteros',           'Name' => 'Numeros3',      'Id' => 'IDInput_4_8','Required' => 2,'Icon' => 'bi bi-sort-numeric-down']);
                $data['Fnc_FormInputs']->formNumberSpinner([        'FormAling' => 4,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'number_spinner',            'Name' => 'number_spinner','Id' => 'IDInput_4_9','Required' => 2,'Min' => 1,'Max' => 20,'Step' => 1,'Ndecimal' => 0]);

                /***********************************/
                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Inputs Fecha y Hora', 'Clase' => 'box-title text-color-red-dark']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 7, 'FormAling' => 4,'FormCol' => 12, 'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'date',            'Name' => 'date',             'Id' => 'IDInput_4_10','Required' => 2,'Icon' => 'bi bi-calendar-date']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 8, 'FormAling' => 4,'FormCol' => 12, 'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'form_input_date', 'Name' => 'form_input_date',  'Id' => 'IDInput_4_11','Required' => 2,'Icon' => 'bi bi-calendar-date']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 9, 'FormAling' => 4,'FormCol' => 6,  'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'time',            'Name' => 'time',             'Id' => 'IDInput_4_12','Required' => 2,'Icon' => 'bi bi-clock']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 10,'FormAling' => 4,'FormCol' => 6,  'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'form_time_picker','Name' => 'form_time_picker', 'Id' => 'IDInput_4_13','Required' => 2,'Icon' => 'bi bi-clock']);
                $data['Fnc_FormInputs']->formTime([                  'FormAling' => 4,'FormCol' => 6,  'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'formTime',       'Name' => 'formTime',        'Id' => 'IDInput_4_14','Required' => 2,'Position' => 1,'Icon' => 'bi bi-clock']);

                /***********************************/
                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Inputs Funcionales', 'Clase' => 'box-title text-color-red-dark']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 12,'FormAling' => 4,'FormCol' => 6,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'Color',            'Name' => 'Color',            'Id' => 'IDInput_4_15','Required' => 2,'InputClass'  => 'form-control-color','Icon' => 'bi bi-pencil']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 13,'FormAling' => 4,'FormCol' => 6,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'form_color_picker','Name' => 'form_color_picker','Id' => 'IDInput_4_16','Required' => 2,'Icon' => 'bi bi-pencil']);

                /***********************************/
                //Variables
                $xvalue  = '5';
                $xvalue2 = '';
                //input
                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Selects', 'Clase' => 'box-title text-color-red-dark']);
                $data['Fnc_FormInputs']->formSelect([               'FormAling' => 4,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'formSelect',               'Name' => 'formSelect',                 'Id' => 'IDInput_4_17','Value' => $xvalue,'Required' => 2,'arrData' => $data['arrCiudad']]);
                $data['Fnc_FormInputs']->formSelectFilter([         'FormAling' => 4,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'formSelectFilter',         'Name' => 'formSelectFilter',           'Id' => 'IDInput_4_18','Value' => $xvalue,'Required' => 2,'arrData' => $data['arrCiudad'], 'BASE' => $BASE]);
                $data['Fnc_FormInputs']->formSelectGroup([          'FormAling' => 4,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'formSelectGroup',          'Name' => 'formSelectGroup',            'Id' => 'IDInput_4_19','Value' => $xvalue,'Required' => 2,'arrData' => $data['arrGroup']]);
                $data['Fnc_FormInputs']->formSelectGroupFilter([    'FormAling' => 4,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'formSelectGroupFilter',    'Name' => 'formSelectGroupFilter',      'Id' => 'IDInput_4_20','Value' => $xvalue,'Required' => 2,'arrData' => $data['arrGroup'], 'BASE' => $BASE]);
                $data['Fnc_FormInputs']->formSelectMultiple([       'FormAling' => 4,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'form_multiple1',           'Name' => 'form_multiple2[]',           'Id' => 'IDInput_4_21','Value' => $xvalue,'Required' => 2,'arrData' => $data['arrCiudad']]);
                $data['Fnc_FormInputs']->formSelectMultipleGroup([  'FormAling' => 4,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'formSelectMultipleGroup',  'Name' => 'formSelectMultipleGroup[]',  'Id' => 'IDInput_4_22','Value' => $xvalue,'Required' => 2,'arrData' => $data['arrGroup']]);

                $data['Fnc_FormInputs']->formTittle(['Tipo' => 5,'Texto' => 'formSelectDepend', 'Clase' => 'box-title text-color-red-dark']);
                $data['Fnc_FormInputs']->formSelectDepend([  'FormAling1' => 4,'FormCol1' => 12,'PlaceholderIcon1' => 'bi bi-person', 'Placeholder1' => 'Ciudad','Name1' => 'idCiudad','Id1' => 'IDInput_4_23','Value1' => $xvalue2,'Required1' => 2,'arrData1' => $data['arrCiudad'],
                                                             'FormAling2' => 4,'FormCol2' => 12,'PlaceholderIcon2' => 'bi bi-person', 'Placeholder2' => 'Comuna','Name2' => 'idComuna','Id2' => 'IDInput_4_24','Value2' => $xvalue2,'Required2' => 2,'arrData2' => $data['arrComuna'],]);
                $data['Fnc_FormInputs']->formTittle(['Tipo' => 5,'Texto' => 'formSelectDependFilter', 'Clase' => 'box-title text-color-red-dark']);
                $data['Fnc_FormInputs']->formSelectDependFilter(['FormAling1' => 4,'FormCol1' => 12,'PlaceholderIcon1' => 'bi bi-person', 'Placeholder1' => 'Ciudad','Name1' => 'idCiudad','Id1' => 'IDInput_4_25','Value1' => $xvalue2,'Required1' => 2,'arrData1' => $data['arrCiudad'],
                                                                 'FormAling2' => 4,'FormCol2' => 12,'PlaceholderIcon2' => 'bi bi-person', 'Placeholder2' => 'Comuna','Name2' => 'idComuna','Id2' => 'IDInput_4_26','Value2' => $xvalue2,'Required2' => 2,'arrData2' => $data['arrComuna'],
                                                                 'BASE' => $BASE]);
                $data['Fnc_FormInputs']->formSelectCountry([ 'FormAling' => 4,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'formSelectCountry','Name' => 'formSelectCountry','Id' => 'IDInput_4_27','Value' => $xvalue2,'Required' => 2, 'BASE' => $BASE]);
                $data['Fnc_FormInputs']->formSelectnAuto([   'FormAling' => 4,'FormCol' => 12,'PlaceholderIcon' => 'bi bi-person', 'Placeholder' => 'formSelectnAuto',  'Name' => 'formSelectnAuto',  'Id' => 'IDInput_4_28','Value' => $xvalue, 'Required' => 2,'ValorInicio' => 1,'ValorFin' => 25]);


                ?>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form><!-- End No Labels Form -->

        </div>
    </div>

</div>
