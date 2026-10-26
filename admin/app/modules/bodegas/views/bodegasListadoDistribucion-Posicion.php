<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

// Se obtiene el ID
$DataID = $data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idBodegas']);
?>
<form id="FormEditPosicion" name="FormEditPosicion" autocomplete="off" method="POST" action="" role="form" novalidate aria-label="Formulario de ejecucion">
    <div class="modal-header">
        <?php
        switch ($data['UserData']["sistemaModalSubtitle"]) {
            case 1:
                echo '
                <h5 class="modal-title">
                    <i class="bi bi-geo-alt"></i> Estado de la Posición
                </h5>';
                break;
            case 2:
                echo '
                <h5 class="modal-title modal-subtitle">
                    <div class="icon"><i class="bi bi-geo-alt"></i></div>
                    Estado de la Posición<br>
                    <small>Permite cambiar el estado de ';
                echo $data['rowData']['Codigo'];
                echo '</small>
                </h5>';
                break;
        } ?>
        <button type="button" class="btn-close" aria-label="Close" data-modal-close></button>
    </div>
    <div class="modal-body">
        <?php
        //se dibujan los inputs
        $data['Fnc_FormInputs']->formInput([ 'FormType' => 1, 'Placeholder' => 'Codigo',    'Name' => 'CodigoFake',       'Id' => 'EditPos_CodigoFake',       'Value' => ($data['rowData']['Codigo'] ?? ''),                                                              'Required' => 3, 'Icon' => 'bi bi-geo-alt']);
        $data['Fnc_FormInputs']->formInput([ 'FormType' => 1, 'Placeholder' => 'Ubicacion', 'Name' => 'UbicacionFake',    'Id' => 'EditPos_UbicacionFake',    'Value' => ($data['rowData']['Zona'].' / '.$data['rowData']['Rack'].' / Nivel '.$data['rowData']['Nivel']), 'Required' => 3, 'Icon' => 'bi bi-signpost']);
        $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Estado',    'Name' => 'idEstadoPosicion', 'Id' => 'EditPos_idEstadoPosicion', 'Value' => ($data['rowData']['idEstadoPosicion'] ?? ''),                                                    'Required' => 2, 'arrData' => $data['arrEstado']]);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idPosicion', 'Value' => $data['rowData']['idPosicion'], 'Required' => 2]);
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
    /************************************/
    $("#FormEditPosicion").submit(function(e) {
        //Se validan los datos de los formularios
        var validatorResult = validator.checkAll(this);
        //verifico el resultado
        if(validatorResult.valid===false){
            return !!validatorResult.valid;
        }else{
            // Si ya se está ejecutando, salimos
            if (ejecutandoForm.valor) return;
            //Cambio los valores
            ejecutandoForm.valor = true;
            //Ejecucion normal
            e.preventDefault();
            //Cargo el loader
            $('#PDloader').show();
            //Ejecuto
            let Metodo      = 'POST';
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/distribucion/posicion'; ?>';
            let Informacion = $("#FormEditPosicion").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabDistMapa', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/distribucion/mapa/'.$DataID['data']; ?>'}
                ],
                showNoti:'Posición Actualizada Correctamente',
                closeModal:'#viewModal-lg',
                closeObject:'#PDloader',
                changeValForm: ejecutandoForm,
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });

</script>
