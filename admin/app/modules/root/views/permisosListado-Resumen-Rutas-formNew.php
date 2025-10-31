<div class="modal fade" id="newFormModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="FormNewRuta" name="FormNewRuta" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
                <div class="modal-header">
                    <?php
                    switch ($data['UserData']["sistemaModalSubtitle"]) {
                        case 1:
                            echo '
                            <h5 class="modal-title">
                                <i class="bi bi-file-earmark"></i> Crear Nueva Ruta
                            </h5>';
                            break;
                        case 2:
                            echo '
                            <h5 class="modal-title modal-subtitle">
                                <div class="icon"><i class="bi bi-file-earmark"></i></div>
                                Crear Nueva Ruta<br>
                                <small>Permite crear un nuevo elemento</small>
                            </h5>';
                            break;
                    } ?>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php
                    //se dibujan los inputs
                    $data['Fnc_FormInputs']->formSelect([                'Placeholder' => 'Metodo',             'Name' => 'idMetodo',       'Value' => '', 'Required' => 2,'arrData' => $data['arrMetodo']]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Ruta Web',           'Name' => 'RutaWeb',        'Value' => '', 'Required' => 2,'Icon' => 'bi bi-puzzle']);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Controlador',        'Name' => 'Controller',     'Value' => '', 'Required' => 2,'Icon' => 'bi bi-share-fill']);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Ruta Controlador',   'Name' => 'RutaController', 'Value' => '', 'Required' => 2,'Icon' => 'bi bi-share-fill']);
                    $data['Fnc_FormInputs']->formTextarea([              'Placeholder' => 'Descripcion',        'Name' => 'Descripcion',    'Value' => '', 'Required' => 2]);
                    $data['Fnc_FormInputs']->formSelect([                'Placeholder' => 'Objetivo',           'Name' => 'idLevelLimit',   'Value' => '', 'Required' => 2,'arrData' => $data['arrObjetivo']]);

                    //datos ocultos
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idPermisos','Value' => $data['rowData']['idPermisos'],'Required' => 2]);
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
    $("#FormNewRuta").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/Core/permisos/listado/rutas'; ?>';
            let Informacion = $("#FormNewRuta").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#X_datatable', fromData:'<?php echo $BASE.'/Core/permisos/listado/rutas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idPermisos']); ?>', refreshTbl:'true'}
                ],
                showNoti:'Dato Creado Correctamente',
                closeModal:'#newFormModal',
                ClearForm:'FormNewRuta',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
</script>