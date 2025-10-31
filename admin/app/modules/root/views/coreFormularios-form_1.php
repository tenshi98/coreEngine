<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6">

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Horizontal Form</h5>

            <!-- Horizontal Form -->
            <form id="form1" name="form1" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
                <?php
                /***********************************/
                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Inputs Normales']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 1,'Placeholder' => 'Texto normal',      'Name' => 'Nombre',  'Id' => 'IDInput_1_1', 'Required' => 2]);
                $data['Fnc_FormInputs']->formInput(['FormType' => 1,'Placeholder' => 'Input desactivado', 'Name' => 'Nombre',  'Id' => 'IDInput_1_2', 'Required' => 3]);
                $data['Fnc_FormInputs']->formInput(['FormType' => 2,'Placeholder' => 'Email',             'Name' => 'email',   'Id' => 'IDInput_1_3', 'Required' => 2,'Icon' => 'bx bx-mail-send']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 3,'Placeholder' => 'Contraseñas',       'Name' => 'password','Id' => 'IDInput_1_4', 'Required' => 2,'Icon' => 'bi bi-key']);
                $data['Fnc_FormInputs']->formInputDatalist(['Placeholder' => 'Input Datalist','Name' => 'formInputDatalist','Id' => 'IDInput_1_29','Required' => 2,'Icon' => 'bi bi-server','arrData' => $data['arrCiudad']]);

                /***********************************/
                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Inputs Especificos']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 11,'Placeholder' => 'Rut','Name' => 'rut','Id' => 'IDInput_1_5','Required' => 2,'Icon' => 'bi bi-person-circle']);

                /***********************************/
                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Inputs Numericos']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 4,'Placeholder' => 'Numeros enteros positivos', 'Name' => 'Numeros1',      'Id' => 'IDInput_1_6','Required' => 2,'Icon' => 'bi bi-sort-numeric-down']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 5,'Placeholder' => 'Numeros reales',            'Name' => 'Numeros2',      'Id' => 'IDInput_1_7','Required' => 2,'Icon' => 'bi bi-sort-numeric-down']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 6,'Placeholder' => 'Numeros enteros',           'Name' => 'Numeros3',      'Id' => 'IDInput_1_8','Required' => 2,'Icon' => 'bi bi-sort-numeric-down']);
                $data['Fnc_FormInputs']->formNumberSpinner([ 'Placeholder' => 'number_spinner',            'Name' => 'number_spinner','Id' => 'IDInput_1_9','Required' => 2,'Min' => 1,'Max' => 20,'Step' => 1,'Ndecimal' => 0]);

                /***********************************/
                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Inputs Fecha y Hora']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 7, 'Placeholder' => 'date',            'Name' => 'date',             'Id' => 'IDInput_1_10','Required' => 2,'Icon' => 'bi bi-calendar-date']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 8, 'Placeholder' => 'form_input_date', 'Name' => 'form_input_date',  'Id' => 'IDInput_1_11','Required' => 2,'Icon' => 'bi bi-calendar-date']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 9, 'Placeholder' => 'time',            'Name' => 'time',             'Id' => 'IDInput_1_12','Required' => 2,'Icon' => 'bi bi-clock']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 10,'Placeholder' => 'form_time_picker','Name' => 'form_time_picker', 'Id' => 'IDInput_1_13','Required' => 2,'Icon' => 'bi bi-clock']);
                $data['Fnc_FormInputs']->formTime([                  'Placeholder' => 'formTime',       'Name' => 'formTime',        'Id' => 'IDInput_1_14','Required' => 2,'Position' => 1,'Icon' => 'bi bi-clock']);

                /***********************************/
                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Inputs Funcionales']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 12,'Placeholder' => 'Color',            'Name' => 'Color',            'Id' => 'IDInput_1_15','Required' => 2,'InputClass'  => 'form-control-color','Icon' => 'bi bi-pencil']);
                $data['Fnc_FormInputs']->formInput(['FormType' => 13,'Placeholder' => 'form_color_picker','Name' => 'form_color_picker','Id' => 'IDInput_1_16','Required' => 2,'Icon' => 'bi bi-pencil']);

                /***********************************/
                //Variables
                $xvalue  = '5';
                $xvalue2 = '';
                //input
                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Selects']);
                $data['Fnc_FormInputs']->formSelect([              'Placeholder' => 'Ciudad',              'Name' => 'idCiudad0',              'Id' => 'IDInput_1_17','Value' => $xvalue,'Required' => 2,'arrData' => $data['arrCiudad']]);
                $data['Fnc_FormInputs']->formSelectFilter([       'Placeholder' => 'formSelectFilter',       'Name' => 'formSelectFilter',       'Id' => 'IDInput_1_18','Value' => $xvalue,'Required' => 2,'arrData' => $data['arrCiudad'], 'BASE' => $BASE]);
                $data['Fnc_FormInputs']->formSelectGroup([        'Placeholder' => 'formSelectGroup',        'Name' => 'formSelectGroup',        'Id' => 'IDInput_1_19','Value' => $xvalue,'Required' => 2,'arrData' => $data['arrGroup']]);
                $data['Fnc_FormInputs']->formSelectGroupFilter([ 'Placeholder' => 'formSelectGroupFilter', 'Name' => 'formSelectGroupFilter', 'Id' => 'IDInput_1_20','Value' => $xvalue,'Required' => 2,'arrData' => $data['arrGroup'], 'BASE' => $BASE]);
                $data['Fnc_FormInputs']->formSelectMultiple([            'Placeholder' => 'form_multiple1',           'Name' => 'form_multiple2[]',         'Id' => 'IDInput_1_21','Value' => $xvalue,'Required' => 2,'arrData' => $data['arrCiudad']]);
                $data['Fnc_FormInputs']->formSelectMultipleGroup([      'Placeholder' => 'formSelectMultipleGroup',      'Name' => 'formSelectMultipleGroup[]',    'Id' => 'IDInput_1_22','Value' => $xvalue,'Required' => 2,'arrData' => $data['arrGroup']]);

                $data['Fnc_FormInputs']->formTittle(['Tipo' => 5,'Texto' => 'formSelectDepend']);
                $data['Fnc_FormInputs']->formSelectDepend(['Placeholder1' => 'Ciudad','Name1' => 'idCiudad','Id1' => 'IDInput_1_23','Value1' => $xvalue2,'Required1' => 2,'arrData1' => $data['arrCiudad'],
                                                             'Placeholder2' => 'Comuna','Name2' => 'idComuna','Id2' => 'IDInput_1_24','Value2' => $xvalue2,'Required2' => 2,'arrData2' => $data['arrComuna'],
                                                             'FormName' => 'form1']);
                $data['Fnc_FormInputs']->formTittle(['Tipo' => 5,'Texto' => 'formSelectDependFilter']);
                $data['Fnc_FormInputs']->formSelectDependFilter(['Placeholder1' => 'Ciudad','Name1' => 'idCiudad','Id1' => 'IDInput_1_25','Value1' => $xvalue2,'Required1' => 2,'arrData1' => $data['arrCiudad'],
                                                                'Placeholder2' => 'Comuna','Name2' => 'idComuna','Id2' => 'IDInput_1_26','Value2' => $xvalue2,'Required2' => 2,'arrData2' => $data['arrComuna'],
                                                                'FormName' => 'form1', 'BASE' => $BASE]);
                $data['Fnc_FormInputs']->formSelectCountry([ 'Placeholder' => 'formSelectCountry','Name' => 'formSelectCountry','Id' => 'IDInput_1_27','Value' => $xvalue2,'Required' => 2, 'BASE' => $BASE]);
                $data['Fnc_FormInputs']->formSelectnAuto([  'Placeholder' => 'formSelectnAuto', 'Name' => 'formSelectnAuto', 'Id' => 'IDInput_1_28','Value' => $xvalue, 'Required' => 2,'ValorInicio' => 1,'ValorFin' => 25]);

                ?>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form><!-- End Horizontal Form -->

        </div>
    </div>

</div>