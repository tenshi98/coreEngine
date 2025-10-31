<form id="FormEditDocumentos" name="FormEditDocumentos" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
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
        $x1 = $data['rowData']['Nombre'] ?? '';
        $x2 = $data['rowData']['FVencimiento'] ?? '';
        $x3 = $data['rowData']['Observacion'] ?? '';

        //se dibujan los inputs
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre',            'Name' => 'Nombre',        'Id' => 'EditDocumentos_Nombre',         'Value' => $x1,'Required' => 2]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha Vencimiento', 'Name' => 'FVencimiento',  'Id' => 'EditDocumentos_FVencimiento',   'Value' => $x2,'Required' => 2,'Icon' => 'bi bi-calendar3']);
        $data['Fnc_FormInputs']->formTextarea([               'Placeholder' => 'Observacion',       'Name' => 'Observacion',   'Id' => 'EditDocumentos_Observacion',    'Value' => $x3,'Required' => 1]);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idDocumentos','Value' => $data['rowData']['idDocumentos'],'Required' => 2]);
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
    $("#FormEditDocumentos").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/documentos/update'; ?>';
            let Informacion = $("#FormEditDocumentos").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabDocumentosDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/documentos/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>', refreshTbl:'true'}
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