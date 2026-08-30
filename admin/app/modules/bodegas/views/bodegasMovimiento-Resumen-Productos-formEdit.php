<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<form id="FormEditMovimiento" name="FormEditMovimiento" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data" aria-label="Formulario de ejecucion">
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
        $x_EditProd_Number = (!empty($data['rowData']['Number']) && $data['rowData']['Number'] != 0)
                            ? $data['Fnc_DataNumbers']->cantidadesDecimalesJustos($data['rowData']['Number'])
                            : '';

        //se dibujan los inputs
        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Tipo Movimiento', 'Name' => 'TipoMovimientoFake', 'Id' => 'TipoMovimientoFake',  'Value' => ($data['rowData']['TipoMovimiento'] ?? ''), 'Required' => 3]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Bodega',          'Name' => 'BodegaFake',         'Id' => 'BodegaFake',          'Value' => ($data['rowData']['Bodega'] ?? ''),         'Required' => 3]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Producto',        'Name' => 'ProductoFake',       'Id' => 'ProductoFake',        'Value' => ($data['rowData']['ProductoNombre'] ?? ''), 'Required' => 3]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 5, 'Placeholder' => 'Cantidad',        'Name' => 'Number',             'Id' => 'EditProd_Number',     'Value' => $x_EditProd_Number,                         'Required' => 2,'Icon' => 'bi bi-sort-numeric-down']);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idExistencia',    'Value' => $data['rowData']['idExistencia'],    'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idMovimiento',    'Value' => $data['rowData']['idMovimiento'],    'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idEstadoIngreso', 'Value' => $data['rowData']['idEstadoIngreso'], 'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idBodegas',       'Value' => $data['rowData']['idBodegas'],       'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idProducto',      'Value' => $data['rowData']['idProducto'],      'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'NumberOld',       'Value' => $data['rowData']['Number'],          'Required' => 2]);
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
    /******************************************/
    $("#FormEditMovimiento").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/productos/update'; ?>';
            let Informacion = $("#FormEditMovimiento").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabProdDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/productos/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idMovimiento']); ?>', refreshTbl:'true'}
                ],
                showNoti:'Datos Editados Correctamente',
                closeModal:'#viewModal-lg',
                closeObject:'#PDloader',
                changeValForm: ejecutandoForm,
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });

</script>
