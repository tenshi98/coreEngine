<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

// Se obtiene el ID (padre y máquina) para los refrescos del árbol
$DataID  = $data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idMaquina']);
$PadreID = $data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idComponentes']);
?>

<form id="FormNewSubComponentes" name="FormNewSubComponentes" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data" aria-label="Formulario de ejecucion">
    <div class="modal-header">
        <?php
        switch ($data['UserData']["sistemaModalSubtitle"]) {
            case 1:
                echo '
                <h5 class="modal-title">
                    <i class="bi bi-puzzle"></i> Crear Subcomponente
                </h5>';
                break;
            case 2:
                echo '
                <h5 class="modal-title modal-subtitle">
                    <div class="icon"><i class="bi bi-puzzle"></i></div>
                    Crear Subcomponente<br>
                    <small>Permite crear un subcomponente bajo el elemento seleccionado</small>
                </h5>';
                break;
        } ?>
        <button type="button" class="btn-close" aria-label="Close" data-modal-close></button>
    </div>
    <div class="modal-body">
        <?php
        //se dibujan los inputs
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre',              'Name' => 'Nombre',           'Id' => 'NewSubComponentes_Nombre',           'Value' => '',  'Required' => 2]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Codigo',              'Name' => 'Codigo',           'Id' => 'NewSubComponentes_Codigo',           'Value' => '',  'Required' => 1]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Marca',               'Name' => 'Marca',            'Id' => 'NewSubComponentes_Marca',            'Value' => '',  'Required' => 1]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Modelo',              'Name' => 'Modelo',           'Id' => 'NewSubComponentes_Modelo',           'Value' => '',  'Required' => 1]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Codigo Saf',          'Name' => 'CodigoSaf',        'Id' => 'NewSubComponentes_CodigoSaf',        'Value' => '',  'Required' => 1]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Numero',              'Name' => 'Numero',           'Id' => 'NewSubComponentes_Numero',           'Value' => '',  'Required' => 1]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder' => 'Año de Fabricación',  'Name' => 'AnioFabricacion',  'Id' => 'NewSubComponentes_AnioFabricacion',  'Value' => '',  'Required' => 1, 'Icon' => 'bi bi-calendar3']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Serie',               'Name' => 'Serie',            'Id' => 'NewSubComponentes_Serie',            'Value' => '',  'Required' => 1]);
        ?>
        <div class="row mb-3" id="div_NewSubComponentes_idTareas">
            <legend class="col-form-label col-sm-4 pt-0">Tareas Asignadas</legend>
            <div class="col-sm-8 field">
                <?php foreach (($data['arrTareas'] ?? []) as $tarea) {
                    $idTarea = (int)($tarea['ID'] ?? 0);
                    if ($idTarea <= 0) { continue; } ?>
                    <div class="form-check checkbox-primary">
                        <input class="form-check-input" type="checkbox" name="idTareas[]" value="<?php echo $idTarea; ?>" id="NewSubComponentes_idTarea_<?php echo $idTarea; ?>">
                        <label class="form-check-label" for="NewSubComponentes_idTarea_<?php echo $idTarea; ?>"><?php echo htmlspecialchars($tarea['Nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?></label>
                    </div>
                <?php } ?>
                <p class="formHelp text-muted">Seleccione una o varias tareas del catálogo (opcional)</p>
            </div>
        </div>
        <?php
        $data['Fnc_FormInputs']->formCheckbox(['Placeholder' => 'No Utilizable', 'Name' => 'NoUtilizable', 'Id' => 'NewSubComponentes_NoUtilizable', 'Value' => '', 'Required' => 1, 'Color' => 3, 'DataInfo' => 'Marca el componente como no utilizable']);
        // Subida de archivos
        $data['Fnc_FormInputs']->formUploadMultiple(['Placeholder' => 'Subir archivo', 'Name' => 'NombreArchivo', 'MaxFiles' => 1, 'TypeFiles' => '"jpg", "png", "gif", "jpeg", "bmp", "doc", "docx", "xls", "xlsx", "ppt", "pptx", "mp3", "wav", "pdf", "txt", "rtf", "mp2", "mpeg", "mpg", "mov", "avi", "gz", "gzip", "7Z", "zip", "rar"']);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idPadre',   'Value' => $data['rowData']['idComponentes'], 'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idMaquina', 'Value' => $data['rowData']['idMaquina'],     'Required' => 2]);
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
    /************************************/
    $("#FormNewSubComponentes").submit(function(e) {
        //Se validan los datos de los formularios
        var validatorResult = validator.checkAll(this);
        //verifico el resultado
        if(validatorResult.valid===false){
            return !!validatorResult.valid;
        }else{
            // Si ya se está ejecutando, salimos
            if (ejecutandoForm.valor) return;
            //Cambio los valores
            ejecutandoForm.valor = true;
            //Ejecucion normal
            e.preventDefault();
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Metodo      = 'POST';
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/componentes'; ?>';
            let Informacion = appendFiles('#FormNewSubComponentes', 'NombreArchivo', 1);
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabCompHijos<?php echo $data['rowData']['idComponentes']; ?>', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/componentes/hijos/'.$PadreID['data']; ?>'},
                    {Div:'#tabComponentesDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/componentes/updateList/'.$DataID['data']; ?>', refreshTbl:'true'}
                ],
                showNoti:'Dato Creado Correctamente',
                closeModal:'#viewModal-lg',
                ClearForm:'FormNewSubComponentes',
                closeObject:'#PDloader',
                changeValForm: ejecutandoForm,
            };
            //Se envian los datos al formulario
            SendDataFormsFiles(Metodo, Direccion, Informacion, Options);
        }
    });

</script>
