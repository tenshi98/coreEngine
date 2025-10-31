<form id="FormNewDocumentos" name="FormNewDocumentos" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
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
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre',            'Name' => 'Nombre',        'Id' => 'NewDocumentos_Nombre',         'Value' => '','Required' => 2]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha Vencimiento', 'Name' => 'FVencimiento',  'Id' => 'NewDocumentos_FVencimiento',   'Value' => '','Required' => 2,'Icon' => 'bi bi-calendar3']);
        $data['Fnc_FormInputs']->formTextarea([               'Placeholder' => 'Observacion',       'Name' => 'Observacion',   'Id' => 'NewDocumentos_Observacion',    'Value' => '','Required' => 1]);
        $data['Fnc_FormInputs']->formUploadMultiple([        'Placeholder' => 'Subir archivos',    'Name' => 'NombreArchivo', 'Id' => 'NewDocumentos_NombreArchivo',  'MaxFiles' => 1,'TypeFiles' => '"jpg", "png", "gif", "jpeg", "bmp", "doc", "docx", "xls", "xlsx", "ppt", "pptx", "mp3", "wav", "pdf", "txt", "rtf", "mp2", "mpeg", "mpg", "mov", "avi", "gz", "gzip", "7Z", "zip", "rar"']);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idProducto','Value' => $data['rowData']['idProducto'],'Required' => 2]);
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
    $("#FormNewDocumentos").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/documentos'; ?>';
            let Informacion = appendFiles('#FormNewDocumentos', 'NombreArchivo', 1);
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabDocumentosDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/documentos/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idProducto']); ?>', refreshTbl:'true'}
                ],
                showNoti:'Dato Creado Correctamente',
                closeModal:'#viewModal',
                ClearForm:'FormNewDocumentos',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataFormsFiles(Metodo, Direccion, Informacion, Options);
        }
    });
</script>