<div class="modal fade" id="newFormModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
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
                    //se dibujan los inputs
                    $data['Fnc_FormInputs']->formInput(['FormType' => 2,  'Placeholder' => 'Email',   'Name' => 'Email',    'Value' => '','Required' => 2,'Icon' => 'bx bx-mail-send']);
                    $data['Fnc_FormInputs']->formNumberSpinner([   'Placeholder' => 'Numero',  'Name' => 'Numero',   'Value' => '','Required' => 2,'Min' => 1,'Max' => 20,'Step' => 1,'Ndecimal' => 0]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 11, 'Placeholder' => 'Rut',     'Name' => 'Rut',      'Value' => '','Required' => 2,'Icon' => 'bi bi-person-circle']);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Patente', 'Name' => 'Patente',  'Value' => '','Required' => 2,'Icon' => 'ri-car-fill']);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha',   'Name' => 'Fecha',    'Value' => '','Required' => 2,'Icon' => 'bi bi-calendar3']);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 10, 'Placeholder' => 'Hora',    'Name' => 'Hora',     'Value' => '','Required' => 2,'Icon' => 'bi bi-clock']);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Palabra', 'Name' => 'Palabra',  'Value' => '','Required' => 2]);

                    //datos ocultos
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idUsuario','Value' => $data['UserData']['UserID'],'Required' => 2]);
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
                UpdateDiv : [
                    {Div:'#listTableData', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/updateList'; ?>', refreshTbl:'true'}
                ],
                showNoti:'Dato Creado Correctamente',
                closeModal:'#newFormModal',
                ClearForm:'FormNewData',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
</script>