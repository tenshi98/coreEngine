<form id="FormEditMaq" name="FormEditMaq" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
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
        $x1 = $data['rowData']['idMaquina'] ?? '';
        $x2 = $data['rowData']['Fecha'] ?? '';
        $x3 = $data['rowData']['Observacion'] ?? '';
        $x4 = $data['rowData']['idEstado'] ?? '';

        //se dibujan los inputs
        $data['Fnc_FormInputs']->formSelectFilter([          'Placeholder' => 'Maquina',     'Name' => 'idMaquina',    'Id' => 'EditMaquinas_idMaquina',   'Value' => $x1, 'Required' => 2,'arrData' => $data['arrMaquinas'],   'BASE' => $BASE]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha',       'Name' => 'Fecha',        'Id' => 'EditMaquinas_Fecha',       'Value' => $x2, 'Required' => 2, 'Icon' => 'bi bi-calendar3']);
        $data['Fnc_FormInputs']->formTextarea([               'Placeholder' => 'Observacion', 'Name' => 'Observacion',  'Id' => 'EditMaquinas_Observacion', 'Value' => $x3, 'Required' => 1]);
        $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Estado',      'Name' => 'idEstado',     'Id' => 'EditMaquinas_idEstado',    'Value' => $x4, 'Required' => 2,'arrData' => $data['arrEstado']]);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idMaq','Value' => $data['rowData']['idMaq'],'Required' => 2]);
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
    $("#FormEditMaq").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/maquinas/update'; ?>';
            let Informacion = $("#FormEditMaq").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabMaquinasDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/maquinas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>', refreshTbl:'true'}
                ],
                showNoti:'Datos Editados Correctamente',
                closeModal:'#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
    /*********************************************************************/
    //Permite utilizar el select filter en modals dinamicos
    $(document).ready(function() {
        $("#EditMaquinas_idMaquina").select2({
            dropdownParent: $("#FormEditMaq"),
            width: '100%'
        });
    });
</script>