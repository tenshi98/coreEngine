<div class="modal fade" id="newFormModalEstado" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="FormNewDataEstado" name="FormNewDataEstado" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
                <div class="modal-header">
                    <?php
                    switch ($data['UserData']["sistemaModalSubtitle"]) {
                        case 1:
                            echo '
                            <h5 class="modal-title">
                                <i class="bi bi-file-earmark"></i> Crear Nuevo Tablero
                            </h5>';
                            break;
                        case 2:
                            echo '
                            <h5 class="modal-title modal-subtitle">
                                <div class="icon"><i class="bi bi-file-earmark"></i></div>
                                Crear Nuevo Tablero<br>
                                <small>Permite crear un nuevo elemento</small>
                            </h5>';
                            break;
                    } ?>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php
                    //se dibujan los inputs
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre Tablero',               'Name' => 'Nombre',      'Id' => 'NewEstado_Nombre',      'Value' => '', 'Required' => 2]);
                    $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Color Icono',                  'Name' => 'idColor',     'Id' => 'NewEstado_idColor',     'Value' => '', 'Required' => 2,'arrData' => $data['arrColores']]);
                    $data['Fnc_FormInputs']->formSelectnAuto([          'Placeholder' => 'Prioridad',                    'Name' => 'idPrioridad', 'Id' => 'NewEstado_idPrioridad', 'Value' => '', 'Required' => 2,'ValorInicio' => 1,'ValorFin' => 25]);
                    $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => '¿Permite Cierre de la Tarea?', 'Name' => 'idCierre',    'Id' => 'NewEstado_idCierre',    'Value' => '', 'Required' => 2,'arrData' => $data['arrCierre']]);

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
    $("#FormNewDataEstado").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/gestionProyectos/kanban/estados'; ?>';
            let Informacion = $("#FormNewDataEstado").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#listTableData', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/updateList'; ?>', refreshTbl:'true'}
                ],
                showNoti:'Dato Creado Correctamente',
                closeModal:'#newFormModalEstado',
                ClearForm:'FormNewDataEstado',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
</script>