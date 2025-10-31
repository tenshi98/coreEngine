<form id="FormNewCarga" name="FormNewCarga" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
    <div class="modal-header">
        <?php
        switch ($data['UserData']["sistemaModalSubtitle"]) {
            case 1:
                echo '
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark"></i> Crear Nuevo
                </h5>';
                break;
            case 2:
                echo '
                <h5 class="modal-title modal-subtitle">
                    <div class="icon"><i class="bi bi-file-earmark"></i></div>
                    Crear Nuevo<br>
                    <small>Permite crear un nuevo elemento</small>
                </h5>';
                break;
        } ?>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <?php
        //se dibujan los inputs
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre',            'Name' => 'Nombre',           'Id' => 'NewCargas_Nombre',             'Value' => '','Required' => 2]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Apellido Paterno',  'Name' => 'ApellidoPat',      'Id' => 'NewCargas_ApellidoPat',        'Value' => '','Required' => 2]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Apellido Materno',  'Name' => 'ApellidoMat',      'Id' => 'NewCargas_ApellidoMat',        'Value' => '','Required' => 1]);
        $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Sexo',              'Name' => 'idSexo',           'Id' => 'NewCargas_idSexo',             'Value' => '','Required' => 2,'arrData' => $data['arrSexo']]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha Nacimiento',  'Name' => 'FNacimiento',      'Id' => 'NewCargas_FNacimiento',        'Value' => '','Required' => 1,'Icon' => 'bi bi-calendar3']);
        $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Parentesco',        'Name' => 'idParentesco',     'Id' => 'NewCargas_idParentesco',       'Value' => '','Required' => 1,'arrData' => $data['arrParentesco']]);
        $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Estudios',          'Name' => 'idEstudios',       'Id' => 'NewCargas_idEstudios',         'Value' => '','Required' => 1,'arrData' => $data['arrEstudios']]);
        $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Estado Estudios',   'Name' => 'idEstadoEstudio',  'Id' => 'NewCargas_idEstadoEstudio',    'Value' => '','Required' => 1,'arrData' => $data['arrEstadoEstudio']]);
        $data['Fnc_FormInputs']->formTextarea([               'Placeholder' => 'Observacion',       'Name' => 'ObsEstudios',      'Id' => 'NewCargas_ObsEstudios',        'Value' => '','Required' => 1]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha Vigencia',    'Name' => 'FechaVigencia',    'Id' => 'NewCargas_FechaVigencia',      'Value' => '','Required' => 1,'Icon' => 'bi bi-calendar3']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha Vencimiento', 'Name' => 'FechaVencimiento', 'Id' => 'NewCargas_FechaVencimiento',   'Value' => '','Required' => 1,'Icon' => 'bi bi-calendar3']);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idEntidad','Value' => $data['rowData']['idEntidad'],'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idEstado', 'Value' => 1, 'Required' => 2]); //Activo
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
    $("#FormNewCarga").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/cargas'; ?>';
            let Informacion = $("#FormNewCarga").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabCargasDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/cargas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>', refreshTbl:'true'}
                ],
                showNoti:'Dato Creado Correctamente',
                closeModal:'#viewModal',
                ClearForm:'FormNewCarga',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
</script>