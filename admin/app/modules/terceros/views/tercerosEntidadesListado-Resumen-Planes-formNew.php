<form id="FormNewPlanes" name="FormNewPlanes" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
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
        $data['Fnc_FormInputs']->formSelectFilter([          'Placeholder' => 'Servicio',    'Name' => 'idServicio',   'Id' => 'NewPlanes_idServicio',  'Value' => '', 'Required' => 2,'arrData' => $data['arrServicios'],   'BASE' => $BASE]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha',       'Name' => 'Fecha',        'Id' => 'NewPlanes_Fecha',       'Value' => '', 'Required' => 2, 'Icon' => 'bi bi-calendar3']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 6,  'Placeholder' => 'Monto',       'Name' => 'Monto',        'Id' => 'NewPlanes_Monto',       'Value' => '', 'Required' => 2, 'Icon' => 'bi bi-currency-dollar']);
        $data['Fnc_FormInputs']->formTextarea([               'Placeholder' => 'Observacion', 'Name' => 'Observacion',  'Id' => 'NewPlanes_Observacion', 'Value' => '', 'Required' => 1]);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idEntidad', 'Value' => $data['rowData']['idEntidad'], 'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idEstado',  'Value' => 1,                             'Required' => 2]); //Activo
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idUsuario', 'Value' => $data['UserData']['UserID'],   'Required' => 2]); //Usuario que lo creo
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
    $("#FormNewPlanes").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/planes'; ?>';
            let Informacion = $("#FormNewPlanes").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabPlanesDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/planes/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>', refreshTbl:'true'}
                ],
                showNoti:'Dato Creado Correctamente',
                closeModal:'#viewModal',
                ClearForm:'FormNewPlanes',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
    /*********************************************************************/
    //Permite utilizar el select filter en modals dinamicos
    $(document).ready(function() {
        $("#NewPlanes_idServicio").select2({
            dropdownParent: $("#FormNewPlanes"),
            width: '100%'
        });
    });
</script>