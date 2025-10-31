<form id="FormEditRuta" name="FormEditRuta" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
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
        $x1  = $data['rowData']['idMetodo'] ?? '';
        $x2  = $data['rowData']['RutaWeb'] ?? '';
        $x3  = $data['rowData']['Controller'] ?? '';
        $x4  = $data['rowData']['RutaController'] ?? '';
        $x5  = $data['rowData']['Descripcion'] ?? '';
        $x6  = $data['rowData']['idLevelLimit'] ?? '';

        //se dibujan los inputs
        $data['Fnc_FormInputs']->formSelect([                'Placeholder' => 'Metodo',             'Name' => 'idMetodo',       'Id' => 'Edit_idMetodo',       'Value' => $x1, 'Required' => 2,'arrData' => $data['arrMetodo']]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Ruta Web',           'Name' => 'RutaWeb',        'Id' => 'Edit_RutaWeb',        'Value' => $x2, 'Required' => 2,'Icon' => 'bi bi-puzzle']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Controlador',        'Name' => 'Controller',     'Id' => 'Edit_Controller',     'Value' => $x3, 'Required' => 2,'Icon' => 'bi bi-share-fill']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Ruta Controlador',   'Name' => 'RutaController', 'Id' => 'Edit_RutaController', 'Value' => $x4, 'Required' => 2,'Icon' => 'bi bi-share-fill']);
        $data['Fnc_FormInputs']->formTextarea([              'Placeholder' => 'Descripcion',        'Name' => 'Descripcion',    'Id' => 'Edit_Descripcion',    'Value' => $x5, 'Required' => 2]);
        $data['Fnc_FormInputs']->formSelect([                'Placeholder' => 'Objetivo',           'Name' => 'idLevelLimit',   'Id' => 'Edit_idLevelLimit',   'Value' => $x6, 'Required' => 2,'arrData' => $data['arrLevelLimit']]);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idRutas','Value' => $data['rowData']['idRutas'],'Required' => 2]);
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
    $("#FormEditRuta").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/Core/permisos/listado/rutas/update'; ?>';
            let Informacion = $("#FormEditRuta").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#X_datatable', fromData:'<?php echo $BASE.'/Core/permisos/listado/rutas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idPermisos']); ?>', refreshTbl:'true'}
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