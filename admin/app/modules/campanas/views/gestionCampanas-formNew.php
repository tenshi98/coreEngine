<div class="modal fade" id="newFormModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="FormNewData" name="FormNewData" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
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
                    $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha',          'Name' => 'Fecha',         'Id' => 'New_Fecha',         'Value' => '', 'Required' => 2, 'Icon' => 'bi bi-calendar3']);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre',         'Name' => 'Nombre',        'Id' => 'New_Nombre',        'Value' => '', 'Required' => 2]);
                    $data['Fnc_FormInputs']->formSelectFilter([          'Placeholder' => 'Bodega',         'Name' => 'idBodegas',     'Id' => 'New_idBodegas',     'Value' => '', 'Required' => 2, 'arrData' => $data['arrBodegas'],   'BASE' => $BASE, 'selectProperties' => 'data-dropdown-parent="#newFormModal"']);
                    $data['Fnc_FormInputs']->formTextarea([               'Placeholder' => 'Observaciones',  'Name' => 'Observaciones', 'Id' => 'New_Observaciones', 'Value' => '', 'Required' => 1]);

                    //datos ocultos
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idUsuario',   'Value' => $data['UserData']['UserID'],               'Required' => 2]);  //Usuario que lo creo
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idEstado',    'Value' => 1,                                         'Required' => 2]);  //Abierto
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'fecha_auto',  'Value' => $data['Fnc_ServerServer']->fechaActual(), 'Required' => 2]);  //Fecha de creacion automatica

                    ?>
                </div>
                <div class="modal-footer">
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end w-100">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bx bi-x-circle"></i> Cerrar</button>
                        <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    $("#FormNewData").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess']; ?>';
            let Informacion = $("#FormNewData").serialize();
            const Options     = {
                DestinoFrom:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumen/'; ?>',
                ClearForm:'FormNewData',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
</script>