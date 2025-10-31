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
        $x1  = $data['rowData']['Nombre'] ?? '';
        $x2  = $data['rowData']['Icon'] ?? '';
        $x3  = $data['rowData']['IdIconColor'] ?? '';
        $x4  = $data['rowData']['Descripcion'] ?? '';
        $x5  = $data['rowData']['Carpeta'] ?? '';

        //se dibujan los inputs
        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Nombre',              'Name' => 'Nombre',      'Id' => 'Edit_Nombre',       'Value' => $x1, 'Required' => 2]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Icono',               'Name' => 'Icon',        'Id' => 'Edit_Icon',         'Value' => $x2, 'Required' => 2]);
        $data['Fnc_FormInputs']->formSelect([                'Placeholder' => 'Color Icono',         'Name' => 'IdIconColor', 'Id' => 'Edit_IdIconColor',  'Value' => $x3, 'Required' => 2,'arrData' => $data['arrColores']]);
        $data['Fnc_FormInputs']->formTextarea([              'Placeholder' => 'Descripcion',         'Name' => 'Descripcion', 'Id' => 'Edit_Descripcion',  'Value' => $x4, 'Required' => 1]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Carpeta Contenedora', 'Name' => 'Carpeta',     'Id' => 'Edit_Carpeta',      'Value' => $x5, 'Required' => 2]);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idPermisosCat','Value' => $data['rowData']['idPermisosCat'],'Required' => 2]);
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
            let Direccion   = '<?php echo $BASE.'/Core/permisos/categorias/update'; ?>';
            let Informacion = $("#FormEditData").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#X_datatable', fromData:'<?php echo $BASE.'/Core/permisos/categorias/updateList'; ?>', refreshTbl:'true'}
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