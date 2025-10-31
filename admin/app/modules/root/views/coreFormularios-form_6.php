<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Horizontal Form</h5>

            <!-- Horizontal Form -->
            <form id="form6" name="form6" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
                <?php
                /***********************************/
                //Variables
                $xvalue = '1,1,1,1,1,1,1,2,1,1,1,1,2,1,1';
                //input
                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Inputs checkbox']);
                $data['Fnc_FormInputs']->formCheckbox(['Placeholder' => 'formCheckbox 1','Name' => 'form_checkbox1','Id' => 'IDInput_6_1','Required' => 3,'Color' => 1]);
                $data['Fnc_FormInputs']->formCheckbox(['Placeholder' => 'formCheckbox 2','Name' => 'form_checkbox2','Id' => 'IDInput_6_2','Required' => 2,'Color' => 2]);
                $data['Fnc_FormInputs']->formCheckbox(['Placeholder' => 'formCheckbox 3','Name' => 'form_checkbox3','Id' => 'IDInput_6_3','Required' => 2,'Color' => 3]);
                $data['Fnc_FormInputs']->formCheckbox(['Placeholder' => 'formCheckbox 4','Name' => 'form_checkbox4','Id' => 'IDInput_6_4','Required' => 2,'Color' => 4]);
                $data['Fnc_FormInputs']->formCheckbox(['Placeholder' => 'formCheckbox 5','Name' => 'form_checkbox5','Id' => 'IDInput_6_5','Required' => 2,'Color' => 5]);
                $data['Fnc_FormInputs']->formCheckbox(['Placeholder' => 'formCheckbox 6','Name' => 'form_checkbox6','Id' => 'IDInput_6_6','Required' => 2,'Color' => 6]);
                $data['Fnc_FormInputs']->formCheckboxActive(['Placeholder' => 'formCheckboxActive','Name' => 'formCheckboxActive','Id' => 'IDInput_6_7','Value' => $xvalue,'Required' => 2,'Color' => 6,'arrData' => $data['arrCiudad']]);

                /***********************************/
                //Variables
                $xvalue = '1,1,1,1,1,1,1,2,1,1,1,1,1,1,1';
                //input
                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Inputs Radio']);
                $data['Fnc_FormInputs']->formRadio(['Placeholder' => 'formRadio 1','Name' => 'form_radio1','Id' => 'IDInput_6_8','Required' => 3,'Color' => 1]);
                $data['Fnc_FormInputs']->formRadio(['Placeholder' => 'formRadio 2','Name' => 'form_radio2','Id' => 'IDInput_6_9','Required' => 2,'Color' => 2]);
                $data['Fnc_FormInputs']->formRadio(['Placeholder' => 'formRadio 3','Name' => 'form_radio3','Id' => 'IDInput_6_10','Required' => 2,'Color' => 3]);
                $data['Fnc_FormInputs']->formRadio(['Placeholder' => 'formRadio 4','Name' => 'form_radio4','Id' => 'IDInput_6_11','Required' => 2,'Color' => 4]);
                $data['Fnc_FormInputs']->formRadio(['Placeholder' => 'formRadio 5','Name' => 'form_radio5','Id' => 'IDInput_6_12','Required' => 2,'Color' => 5]);
                $data['Fnc_FormInputs']->formRadio(['Placeholder' => 'formRadio 6','Name' => 'form_radio6','Id' => 'IDInput_6_13','Required' => 2,'Color' => 6]);
                $data['Fnc_FormInputs']->formRadioActive(['Placeholder' => 'formRadioActive','Name' => 'formRadioActive','Id' => 'IDInput_6_14','Value' => $xvalue,'Required' => 2,'Color' => 6,'arrData' => $data['arrCiudad']]);

                /***********************************/
                //Variables
                $xvalue = '1,1,1,1,1,1,1,2,1,1,1,1,2,1,1';
                //input
                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Inputs switch']);
                $data['Fnc_FormInputs']->formSwitch(['Placeholder' => 'formSwitch 1','Name' => 'form_switch1','Id' => 'IDInput_6_15','Required' => 3,'Color' => 1]);
                $data['Fnc_FormInputs']->formSwitch(['Placeholder' => 'formSwitch 2','Name' => 'form_switch2','Id' => 'IDInput_6_16','Required' => 2,'Color' => 2]);
                $data['Fnc_FormInputs']->formSwitch(['Placeholder' => 'formSwitch 3','Name' => 'form_switch3','Id' => 'IDInput_6_17','Required' => 2,'Color' => 3]);
                $data['Fnc_FormInputs']->formSwitch(['Placeholder' => 'formSwitch 4','Name' => 'form_switch4','Id' => 'IDInput_6_18','Required' => 2,'Color' => 4]);
                $data['Fnc_FormInputs']->formSwitch(['Placeholder' => 'formSwitch 5','Name' => 'form_switch5','Id' => 'IDInput_6_19','Required' => 2,'Color' => 5]);
                $data['Fnc_FormInputs']->formSwitch(['Placeholder' => 'formSwitch 6','Name' => 'form_switch6','Id' => 'IDInput_6_20','Required' => 2,'Color' => 6]);
                $data['Fnc_FormInputs']->formSwitchActive(['Placeholder' => 'formSwitchActive','Name' => 'formSwitchActive','Id' => 'IDInput_6_21','Value' => $xvalue,'Required' => 2,'Color' => 6,'arrData' => $data['arrCiudad']]);

                /***********************************/
                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Inputs Texto']);
                $data['Fnc_FormInputs']->formTextarea(['FormAling' => 1,'FormCol' => 12,'Placeholder' => 'formTextarea','Name' => 'formTextarea','Id' => 'IDInput_6_22','Required' => 2]);
                $data['Fnc_FormInputs']->formCKEditor(['FormAling' => 1,'FormCol' => 12,'Placeholder' => 'formCKEditor','Name' => 'formCKEditor','Id' => 'IDInput_6_23','Required' => 2,'Tipo' => 2]);

                /***********************************/
                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Subida Archivos']);
                $data['Fnc_FormInputs']->formUploadMultiple(['Placeholder' => 'Subir archivos','Name' => 'form_multiple_upload1','MaxFiles' => 2,'TypeFiles' => '"jpg", "jpeg", "gif", "png", "bmp", "doc", "docx", "xls", "xlsx", "ppt", "pptx", "odt", "odp", "ods", "pdf", "mp3", "oga", "wav", "txt", "rtf",  "gz", "gzip", "7Z", "zip", "rar"']);

                /***********************************/
                $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Form Details']);
                $data['Fnc_FormInputs']->formDetails($data['arrCiudad']);

                ?>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form><!-- End Horizontal Form -->

        </div>
    </div>
</div>