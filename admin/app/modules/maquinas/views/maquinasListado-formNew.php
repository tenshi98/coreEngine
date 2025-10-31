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
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre',          'Name' => 'Nombre',           'Value' => '','Required' => 2]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Identificador',   'Name' => 'CodIdentificador', 'Value' => '','Required' => 1, 'Icon' => 'ri-barcode-line']);

                    //datos ocultos
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idEstado',         'Value' => 1, 'Required' => 2]); //Activo
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'id_Geo',           'Value' => 2, 'Required' => 2]); //Uso Geolocalización - No
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'id_Sensores',      'Value' => 2, 'Required' => 2]); //Uso Sensores - No
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idBackup',         'Value' => 2, 'Required' => 2]); //Backup Tabla relacionada - No
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idAlertaTemprana', 'Value' => 2, 'Required' => 2]); //Alerta Temprana - No

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