<form id="FormEditCarga" name="FormEditCarga" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
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
        $x1  = $data['rowData']['Nombre'] ?? '';
        $x2  = $data['rowData']['ApellidoPat'] ?? '';
        $x3  = $data['rowData']['ApellidoMat'] ?? '';
        $x4  = $data['rowData']['idSexo'] ?? '';
        $x5  = $data['rowData']['FNacimiento'] ?? '';
        $x6  = $data['rowData']['idParentesco'] ?? '';
        $x7  = $data['rowData']['idEstudios'] ?? '';
        $x8  = $data['rowData']['idEstadoEstudio'] ?? '';
        $x9  = $data['rowData']['ObsEstudios'] ?? '';
        $x10 = $data['rowData']['FechaVigencia'] ?? '';
        $x11 = $data['rowData']['FechaVencimiento'] ?? '';
        $x12 = $data['rowData']['idEstado'] ?? '';

        //se dibujan los inputs
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre',            'Name' => 'Nombre',           'Id' => 'EditCargas_Nombre',             'Value' => $x1,  'Required' => 2]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Apellido Paterno',  'Name' => 'ApellidoPat',      'Id' => 'EditCargas_ApellidoPat',        'Value' => $x2,  'Required' => 2]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Apellido Materno',  'Name' => 'ApellidoMat',      'Id' => 'EditCargas_ApellidoMat',        'Value' => $x3,  'Required' => 1]);
        $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Sexo',              'Name' => 'idSexo',           'Id' => 'EditCargas_idSexo',             'Value' => $x4,  'Required' => 2,'arrData' => $data['arrSexo']]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha Nacimiento',  'Name' => 'FNacimiento',      'Id' => 'EditCargas_FNacimiento',        'Value' => $x5,  'Required' => 1,'Icon' => 'bi bi-calendar3']);
        $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Parentesco',        'Name' => 'idParentesco',     'Id' => 'EditCargas_idParentesco',       'Value' => $x6,  'Required' => 1,'arrData' => $data['arrParentesco']]);
        $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Estudios',          'Name' => 'idEstudios',       'Id' => 'EditCargas_idEstudios',         'Value' => $x7,  'Required' => 1,'arrData' => $data['arrEstudios']]);
        $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Estado Estudios',   'Name' => 'idEstadoEstudio',  'Id' => 'EditCargas_idEstadoEstudio',    'Value' => $x8,  'Required' => 1,'arrData' => $data['arrEstadoEstudio']]);
        $data['Fnc_FormInputs']->formTextarea([               'Placeholder' => 'Observacion',       'Name' => 'ObsEstudios',      'Id' => 'EditCargas_ObsEstudios',        'Value' => $x9,  'Required' => 1]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha Vigencia',    'Name' => 'FechaVigencia',    'Id' => 'EditCargas_FechaVigencia',      'Value' => $x10, 'Required' => 1,'Icon' => 'bi bi-calendar3']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha Vencimiento', 'Name' => 'FechaVencimiento', 'Id' => 'EditCargas_FechaVencimiento',   'Value' => $x11, 'Required' => 1,'Icon' => 'bi bi-calendar3']);
        $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Estado',            'Name' => 'idEstado',         'Id' => 'EditCargas_Estado',             'Value' => $x12, 'Required' => 2,'arrData' => $data['arrEstado']]);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idCargas','Value' => $data['rowData']['idCargas'],'Required' => 2]);
        ?>
    </div>
    <div class="modal-footer">
        <div class="d-grid gap-2 d-md-flex justify-content-md-end w-100">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bx bi-x-circle"></i> Cerrar</button>
            <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
        </div>
    </div>
</form>

<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    $("#FormEditCarga").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/cargas/update'; ?>';
            let Informacion = $("#FormEditCarga").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabCargasDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/cargas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>', refreshTbl:'true'}
                ],
                showNoti:'Datos Editados Correctamente',
                closeModal:'#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
</script>