<form id="FormEditData" name="FormEditData" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
    <div class="modal-header">
        <?php
        switch ($data['UserData']["sistemaModalSubtitle"]) {
            case 1:
                echo '
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square"></i> Editar Información
                </h5>';
                break;
            case 2:
                echo '
                <h5 class="modal-title modal-subtitle">
                    <div class="icon"><i class="bi bi-pencil-square"></i></div>
                    Editar Información<br>
                    <small>Permite editar un elemento existente</small>
                </h5>';
                break;
        } ?>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <?php
        //Se verifican si existen los datos
        $x1  = $data['rowData']['idPrioridad'] ?? '';
        $x2  = $data['rowData']['Fecha'] ?? '';
        $x3  = $data['rowData']['Titulo'] ?? '';
        $x4  = $data['rowData']['Descripcion'] ?? '';
        $x5  = $data['rowData']['idEstadoCierre'] ?? '';

        //se dibujan los inputs
        $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Prioridad',     'Name' => 'idPrioridad',  'Id' => 'EditTarea_idPrioridad',  'Value' => $x1, 'Required' => 2,'arrData' => $data['arrPrioridad']]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha Termino', 'Name' => 'Fecha',        'Id' => 'EditTarea_Fecha',        'Value' => $x2, 'Required' => 2,'Icon' => 'bi bi-calendar3']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Titulo',        'Name' => 'Titulo',       'Id' => 'EditTarea_Titulo',       'Value' => $x3, 'Required' => 2]);
        $data['Fnc_FormInputs']->formTextarea([               'Placeholder' => 'Descripcion',   'Name' => 'Descripcion',  'Id' => 'EditTarea_Descripcion',  'Value' => $x4, 'Required' => 2]);
        /*******************************************************************/
        //Verifico si permite el cierre
        if($data['rowData']['idCierre']==1){
            $data['Fnc_FormInputs']->formSelect(['Placeholder' => 'Estado Cierre', 'Name' => 'idEstadoCierre', 'Id' => 'EditTarea_idEstadoCierre', 'Value' => $x5, 'Required' => 2,'arrData' => $data['arrEstadoCierre']]);
            $data['Fnc_FormInputs']->formInputHidden([                            'Name' => 'Old_idEstadoCierre',                                 'Value' => $x5, 'Required' => 2]);
        }

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idUsuario', 'Value' => $data['UserData']['UserID'],'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idKanban',  'Value' => $data['rowData']['idKanban'],'Required' => 2]);
        //Datos antiguos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Old_idPrioridad',  'Value' => $x1,'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Old_Fecha',        'Value' => $x2,'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Old_Titulo',       'Value' => $x3,'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Old_Descripcion',  'Value' => $x4,'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Fecha_Actual',     'Value' => $data['Fnc_ServerServer']->fechaActual(),'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Hora_Actual',      'Value' => $data['Fnc_ServerServer']->horaActual(),'Required' => 2]);
        ?>
    </div>
    <div class="modal-footer">
        <div class="d-grid gap-2 d-md-flex justify-content-md-end w-100">
            <button type="button" class="btn btn-danger" onclick="listTableDataView('<?php echo $data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idKanban']); ?>')"><i class="bx bx-arrow-back"></i> Volver</button>
            <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
        </div>
    </div>
</form>

<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    $("#FormEditData").submit(function(e) {
        //Se validan los datos de los formularios
        var validatorResult = validator.checkAll(this);
        //verifico el resultado
        if(validatorResult.valid===false){
            return !!validatorResult.valid;
        }else{
            e.preventDefault();
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Metodo      = 'POST';
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/update'; ?>';
            let Informacion = $("#FormEditData").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#listTableData', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/updateList'; ?>', refreshTbl:'true', callFNC:'call_1'},
                    {Div:'#modalContent', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/view/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idKanban']); ?>', refreshTbl:'false'}
                ],
                showNoti:'Datos Editados Correctamente',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
</script>