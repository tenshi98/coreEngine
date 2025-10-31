<form id="FormNewPago" name="FormNewPago" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
    <div class="modal-header">
        <?php
        switch ($data['UserData']["sistemaModalSubtitle"]) {
            case 1:
                echo '
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark"></i> Ingresar Nuevo
                </h5>';
                break;
            case 2:
                echo '
                <h5 class="modal-title modal-subtitle">
                    <div class="icon"><i class="bi bi-file-earmark"></i></div>
                    Ingresar Nuevo<br>
                    <small>Permite el ingreso de pagos a los documentos mercantiles</small>
                </h5>';
                break;
        } ?>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <?php
        //se dibujan los inputs
        $data['Fnc_FormInputs']->formSelect([                'Placeholder' => 'Documento Pago',    'Name' => 'idDocumentoPago',   'Id' => 'NewPago_idDocumentoPago',  'Value' => '', 'Required' => 2, 'arrData' => $data['arrDocumentoPago']]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Numero Documento',  'Name' => 'N_Doc',             'Id' => 'NewPago_N_Doc',            'Value' => '', 'Required' => 1, 'Icon' => 'bi bi-sort-numeric-down']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 6, 'Placeholder' => 'Monto Pagado',      'Name' => 'MontoPagado',       'Id' => 'NewPago_MontoPagado',      'Value' => '', 'Required' => 2, 'Icon' => 'bi bi-currency-dollar']);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idFacturacion', 'Value' => $data['rowData']['idFacturacion'],         'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idUsuario',     'Value' => $data['UserData']['UserID'],               'Required' => 2]);  //Usuario que lo creo
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'FechaPago',     'Value' => $data['Fnc_ServerServer']->fechaActual(), 'Required' => 2]);  //Fecha de creacion automatica

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
    $("#FormNewPago").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/pagos'; ?>';
            let Informacion = $("#FormNewPago").serialize();
            const Options     = {
                Destino:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumen/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idFacturacion']); ?>',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
</script>