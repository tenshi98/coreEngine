<form id="FormEditUsuarios" name="FormEditUsuarios" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
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
        $x1  = $data['rowData']['email'] ?? '';
        $x2  = $data['rowData']['Nombre'] ?? '';
        $x3  = $data['rowData']['Rut'] ?? '';
        $x4  = $data['rowData']['Fono'] ?? '';
        $x5  = $data['rowData']['idTipoUsuario'] ?? '';
        $x6  = $data['rowData']['idEstado'] ?? '';

        //se dibujan los inputs
        $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Datos Personales']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 2,  'Placeholder' => 'Email',   'Name'  => 'email',   'Id'  => 'EditUsuarios_email',   'Value'  => $x1,  'Required'  => 2, 'Icon' => 'bx bx-mail-send']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre',  'Name'  => 'Nombre',  'Id'  => 'EditUsuarios_Nombre',  'Value'  => $x2,  'Required'  => 2]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 11, 'Placeholder' => 'Rut',     'Name'  => 'Rut',     'Id'  => 'EditUsuarios_Rut',     'Value'  => $x3,  'Required'  => 1, 'Icon' => 'bi bi-person-circle']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder' => 'Fono',    'Name'  => 'Fono',    'Id'  => 'EditUsuarios_Fono',    'Value'  => $x4,  'Required'  => 1, 'Icon' => 'bi bi-telephone-fill']);

        $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Configuración']);
        $data['Fnc_FormInputs']->formSelectFilter([         'Placeholder' => 'Tipo de Usuario',  'Name' => 'idTipoUsuario', 'Id' => 'EditUsuarios_idTipoUsuario',  'Value' => $x5, 'Required' => 2,'arrData' => $data['arrTipoUsuario'], 'BASE' => $BASE]);

        $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Administración']);
        $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Estado',       'Name' => 'idEstado',   'Id' => 'EditUsuarios_idEstado',  'Value'  => $x6,  'Required' => 2,'arrData' => $data['arrEstado']]);
        $data['Fnc_FormInputs']->formPostData(1, 4, 'exclamation-circle', 0, '<strong>Contraseña: </strong> permite modificar arbitrariamente la contraseña.');
        $data['Fnc_FormInputs']->formInput(['FormType' => 3,  'Placeholder' => 'Contraseña',   'Name' => 'password',   'Id' => 'EditUsuarios_password',  'Value'  => '',    'Required' => 1,'Icon' => 'bi bi-key']);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idUsuario','Value' => $data['rowData']['idUsuario'],'Required' => 2]);
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
    $("#FormEditUsuarios").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/usuarios/update'; ?>';
            let Informacion = $("#FormEditUsuarios").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabUsuariosDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/usuarios/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>', refreshTbl:'true'}
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