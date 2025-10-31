<form id="FormEditDataEstado" name="FormEditDataEstado" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
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
        $x2  = $data['rowData']['idColor'] ?? '';
        $x3  = $data['rowData']['idPrioridad'] ?? '';
        $x4  = $data['rowData']['idCierre'] ?? '';

        //se dibujan los inputs
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre Tablero',               'Name' => 'Nombre',      'Id' => 'EditEstado_Nombre',      'Value' => $x1, 'Required' => 2]);
        $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Color Icono',                  'Name' => 'idColor',     'Id' => 'EditEstado_idColor',     'Value' => $x2, 'Required' => 2,'arrData' => $data['arrColores']]);
        $data['Fnc_FormInputs']->formSelectnAuto([          'Placeholder' => 'Prioridad',                    'Name' => 'idPrioridad', 'Id' => 'EditEstado_idPrioridad', 'Value' => $x3, 'Required' => 2,'ValorInicio' => 1,'ValorFin' => 25]);
        $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => '¿Permite Cierre de la Tarea?', 'Name' => 'idCierre',    'Id' => 'EditEstado_idCierre',    'Value' => $x4, 'Required' => 2,'arrData' => $data['arrCierre']]);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idKanbanEstado','Value' => $data['rowData']['idKanbanEstado'],'Required' => 2]);

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
    $("#FormEditDataEstado").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/gestionProyectos/kanban/estados/update'; ?>';
            let Informacion = $("#FormEditDataEstado").serialize();
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