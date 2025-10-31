<form id="FormEditTareas" name="FormEditTareas" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
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
        $x1  = $data['rowData']['idTrabajo'] ?? '';
        $x2  = $data['rowData']['Tarea'] ?? '';
        $x3  = $data['rowData']['idEstadoTrabajo'] ?? '';

        //se dibujan los inputs
        //Se verifica si se permite usar tareas especificas
        if($data['UserData']["KanbanTareasUsoTareas"]==2){
            $data['Fnc_FormInputs']->formSelect([ 'Placeholder' => 'Trabajo Especifico', 'Name' => 'idTrabajo', 'Value' => $x1,'Required' => 2,'arrData' => $data['arrTrabajos']]);
            $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Old_idTrabajo', 'Value' => $x1,'Required' => 2]);
        }
        $data['Fnc_FormInputs']->formTextarea([ 'Placeholder' => 'Tarea',   'Name' => 'Tarea',           'Id' => 'EditTareas_Tarea',           'Value' => $x2, 'Required' => 2]);
        $data['Fnc_FormInputs']->formSelect([   'Placeholder' => 'Estado',  'Name' => 'idEstadoTrabajo', 'Id' => 'EditTareas_idEstadoTrabajo', 'Value' => $x3, 'Required' => 2,'arrData' => $data['arrEstadoTrabajo']]);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idUsuario', 'Value' => $data['UserData']['UserID'],'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idKanban',  'Value' => $data['rowData']['idKanban'],'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idTareas',  'Value' => $data['rowData']['idTareas'],'Required' => 2]);
        //Datos antiguos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Old_Tarea',           'Value' => $x2,'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Old_idEstadoTrabajo', 'Value' => $x3,'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Fecha_Actual',        'Value' => $data['Fnc_ServerServer']->fechaActual(),'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Hora_Actual',         'Value' => $data['Fnc_ServerServer']->horaActual(),'Required' => 2]);
        ?>
    </div>
    <div class="modal-footer">
        <div class="d-grid gap-2 d-md-flex justify-content-md-end w-100">
            <button type="button" class="btn btn-danger" onclick="listTableDataView('<?php echo $data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idKanban']); ?>')"><i class="bx bx-arrow-back"></i> Volver</button>
            <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
        </div>
    </div>
</form>

<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    $("#FormEditTareas").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'Tareas/update'; ?>';
            let Informacion = $("#FormEditTareas").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#modalContent', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/view/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idKanban']); ?>', refreshTbl:'false'}
                ],
                showNoti:'Datos Editados Correctamente',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
</script>