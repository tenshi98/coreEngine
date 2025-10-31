<form id="FormNewUsuario" name="FormNewUsuario" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
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
        $data['Fnc_FormInputs']->formPostData(4, 4, 'exclamation-circle', 0, 'Cada usuario nuevo creado lleva por defecto la contraseña 1234');
        $data['Fnc_FormInputs']->formInput(['FormType' => 2,  'Placeholder' => 'Email',               'Name' => 'email',         'Id' => 'NewUsuario_email',          'Value' => '',  'Required' => 2,  'Icon' => 'bx bx-mail-send']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre',              'Name' => 'Nombre',        'Id' => 'NewUsuario_Nombre',         'Value' => '',  'Required' => 2]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 11, 'Placeholder' => 'Rut',                 'Name' => 'Rut',           'Id' => 'NewUsuario_Rut',            'Value' => '',  'Required' => 1,  'Icon' => 'bi bi-person-circle']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder' => 'Fono',                'Name' => 'Fono',          'Id' => 'NewUsuario_Fono',           'Value' => '',  'Required' => 1,  'Icon' => 'bi bi-telephone-fill']);
        $data['Fnc_FormInputs']->formSelectFilter([          'Placeholder' => 'Tipo de Usuario',     'Name' => 'idTipoUsuario', 'Id' => 'NewUsuario_idTipoUsuario',  'Value' => '',  'Required' => 2,'selectProperties' => 'data-dropdown-parent="#newFormModal"','arrData' => $data['arrTipoUsuario'], 'BASE' => $BASE]);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idEntidad','Value' => $data['rowData']['idEntidad'],'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'password',       'Value' => '1234', 'Required' => 2]);//password por defecto
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idEstado',       'Value' => 1,      'Required' => 2]);//Usuario Activo
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
    $("#FormNewUsuario").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/usuarios'; ?>';
            let Informacion = $("#FormNewUsuario").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabUsuariosDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/usuarios/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>', refreshTbl:'true'}
                ],
                showNoti:'Dato Creado Correctamente',
                closeModal:'#viewModal',
                ClearForm:'FormNewUsuario',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
</script>