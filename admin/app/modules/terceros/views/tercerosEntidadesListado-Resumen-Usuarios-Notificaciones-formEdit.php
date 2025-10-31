<form id="FormEditUsuariosNotificaciones" name="FormEditUsuariosNotificaciones" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
    <div class="modal-header">
        <?php
        switch ($data['UserData']["sistemaModalSubtitle"]) {
            case 1:
                echo '
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square"></i> Tipos de Notificaciones Recibidas del usuario '.$data['rowData']['Nombre'].'
                </h5>';
                break;
            case 2:
                echo '
                <h5 class="modal-title modal-subtitle">
                    <div class="icon"><i class="bi bi-pencil-square"></i></div>
                    Tipos de Notificaciones<br>
                    <small>Permite editar los tipos de notificaciones que recibe el usuario '.$data['rowData']['Nombre'].'</small>
                </h5>';
                break;
        } ?>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover datatable">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th style="width: 120px;">Permiso</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    //Verifico si hay datos
                    if(is_array($data['arrPermisos'])&&!empty($data['arrPermisos'])){
                        //Recorro
                        foreach($data['arrPermisos'] AS $crud){
                            //si tiene permiso
                            if(isset($crud['IsActivo'])&&$crud['IsActivo']!=0){
                                $checked = 'checked';
                            }else{
                                $checked  = '';
                            } ?>
                            <tr>
                                <td><?php echo $crud['Notificacion']; ?></td>
                                <td>
                                    <div class="col-sm-8 field">
                                        <div class="form-check checkbox-success form-switch required=" required>
                                            <input                          type="hidden"   value="1" name="<?php echo 'switch_'.$crud['idTipoNoti']; ?>">
                                            <input class="form-check-input" type="checkbox" value="2" name="<?php echo 'switch_'.$crud['idTipoNoti']; ?>" <?php echo $checked; ?>>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    //datos ocultos
    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idEntidad','Value' => $data['rowData']['idEntidad'],'Required' => 2]);
    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idUsuario','Value' => $data['rowData']['idUsuario'],'Required' => 2]);
    ?>
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
    $("#FormEditUsuariosNotificaciones").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/usuariosNoti/update'; ?>';
            let Informacion = $("#FormEditUsuariosNotificaciones").serialize();
            const Options     = {
                showNoti:'Datos Editados Correctamente',
                closeModal:'#viewModal_2',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
</script>