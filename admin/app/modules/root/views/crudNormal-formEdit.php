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
        $x1  = $data['rowData']['Email'] ?? '';
        $x2  = $data['rowData']['Numero'] ?? '';
        $x3  = $data['rowData']['Rut'] ?? '';
        $x4  = $data['rowData']['Patente'] ?? '';
        $x5  = $data['rowData']['Fecha'] ?? '';
        $x6  = $data['rowData']['Hora'] ?? '';
        $x7  = $data['rowData']['Palabra'] ?? '';

        //se dibujan los inputs
        $data['Fnc_FormInputs']->formInput(['FormType' => 2,  'Placeholder' => 'Email',   'Name' => 'Email',    'Id' => 'Edit_Email',    'Value' => $x1,'Required' => 2,'Icon' => 'bx bx-mail-send']);
        $data['Fnc_FormInputs']->formNumberSpinner([   'Placeholder' => 'Numero',  'Name' => 'Numero',   'Id' => 'Edit_Numero',   'Value' => $x2,'Required' => 2,'Min' => 1,'Max' => 20,'Step' => 1,'Ndecimal' => 0]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 11, 'Placeholder' => 'Rut',     'Name' => 'Rut',      'Id' => 'Edit_Rut',      'Value' => $x3,'Required' => 2,'Icon' => 'bi bi-person-circle']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Patente', 'Name' => 'Patente',  'Id' => 'Edit_Patente',  'Value' => $x4,'Required' => 2,'Icon' => 'ri-car-fill']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha',   'Name' => 'Fecha',    'Id' => 'Edit_Fecha',    'Value' => $x5,'Required' => 2,'Icon' => 'bi bi-calendar3']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 10, 'Placeholder' => 'Hora',    'Name' => 'Hora',     'Id' => 'Edit_Hora',     'Value' => $x6,'Required' => 2,'Icon' => 'bi bi-clock']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Palabra', 'Name' => 'Palabra',  'Id' => 'Edit_Palabra',  'Value' => $x7,'Required' => 2]);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idCrud','Value' => $data['rowData']['idCrud'],'Required' => 2]);
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
                    {Div:'#listTableData', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/updateList'; ?>', refreshTbl:'true'}
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