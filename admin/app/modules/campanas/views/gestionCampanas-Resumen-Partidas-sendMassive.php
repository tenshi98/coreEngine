<form id="FormSendMassive" name="FormSendMassive" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
    <div class="modal-header">
        <?php
        switch ($data['UserData']["sistemaModalSubtitle"]) {
            case 1:
                echo '
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square"></i> Enviar Notificación masiva a la partida con fecha '.$data['rowData']['Fecha'].'
                </h5>';
                break;
            case 2:
                echo '
                <h5 class="modal-title modal-subtitle">
                    <div class="icon"><i class="bi bi-pencil-square"></i></div>
                    Enviar Notificación masiva<br>
                    <small>Permite enviar una notificación masiva a la partida con fecha '.$data['rowData']['Fecha'].'</small>
                </h5>';
                break;
        } ?>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <?php
        //Se dibujan los inputs
        $data['Fnc_FormInputs']->formTextarea(['Placeholder' => 'Mensaje', 'Name' => 'Mensaje', 'Id' => 'SendMassive_Mensaje', 'Value' => '', 'Required' => 2]);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idCampana',       'Value' => $data['rowData']['idCampana'],       'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Fecha',           'Value' => $data['rowData']['Fecha'],           'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idEstadoPartida', 'Value' => $data['rowData']['idEstadoPartida'], 'Required' => 2]);

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
    $("#FormSendMassive").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidas/sendCampanaMassive'; ?>';
            let Informacion = $("#FormSendMassive").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabPartidasDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCampana']); ?>'}
                ],
                //showNoti:'Datos Editados Correctamente',
                closeModal:'#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
</script>