<form id="FormEditPago" name="FormEditPago" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
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
        $x1 = $data['rowData']['idDocumentoPago'] ?? '';
        $x2 = $data['rowData']['N_Doc'] ?? '';
        $x3 = (!empty($data['rowData']['MontoPagado']) && $data['rowData']['MontoPagado'] != 0)
            ? $data['Fnc_DataNumbers']->cantidadesDecimalesJustos($data['rowData']['MontoPagado'])
            : '';
        $x4 = $data['rowData']['UsuarioPago'] ?? '';
        $x5 = $data['rowData']['FechaPago'] ?? '';

        //se dibujan los inputs
        $data['Fnc_FormInputs']->formSelect([                'Placeholder' => 'Documento Pago',    'Name' => 'idDocumentoPago',   'Id' => 'EditPago_idDocumentoPago',  'Value' => $x1, 'Required' => 2, 'arrData' => $data['arrDocumentoPago']]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Numero Documento',  'Name' => 'N_Doc',             'Id' => 'EditPago_N_Doc',            'Value' => $x2, 'Required' => 1, 'Icon' => 'bi bi-sort-numeric-down']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 6, 'Placeholder' => 'Monto Pagado',      'Name' => 'MontoPagado',       'Id' => 'EditPago_MontoPagado',      'Value' => $x3, 'Required' => 2, 'Icon' => 'bi bi-currency-dollar']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Usuario Pago',      'Name' => 'UsuarioPagoFake',   'Id' => 'UsuarioPagoFake',           'Value' => $x4, 'Required' => 3]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Fecha Pago',        'Name' => 'FechaPagoFake',     'Id' => 'FechaPagoFake',             'Value' => $x5, 'Required' => 3]);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idPago',        'Value' => $data['rowData']['idPago'],        'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idFacturacion', 'Value' => $data['rowData']['idFacturacion'], 'Required' => 2]);
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
    $("#FormEditPago").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/pagos/update'; ?>';
            let Informacion = $("#FormEditPago").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabPagoDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/pagos/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idFacturacion']); ?>'}
                ],
                closeModal : '#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
</script>