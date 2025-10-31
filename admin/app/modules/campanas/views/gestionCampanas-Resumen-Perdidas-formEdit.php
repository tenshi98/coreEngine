<form id="FormEditPerdida" name="FormEditPerdida" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
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
        $x1 = $data['rowData']['Item'] ?? '';
        $x2 = $data['rowData']['Producto'] ?? '';
        $x3 = (!empty($data['rowData']['Cantidad']) && $data['rowData']['Cantidad'] != 0)
            ? $data['Fnc_DataNumbers']->cantidadesDecimalesJustos($data['rowData']['Cantidad'])
            : '';
        $x4 = (!empty($data['rowData']['Perdidas']) && $data['rowData']['Perdidas'] != 0)
            ? $data['Fnc_DataNumbers']->cantidadesDecimalesJustos($data['rowData']['Perdidas'])
            : '';

        //se dibujan los inputs
        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Item',         'Name' => 'Item',           'Id' => 'EditPerdida_Item',      'Value' => $x1, 'Required' => 2]);
        //Solo si existe movimiento de productos
        if(isset($data['rowData']['Cantidad'])&&$data['rowData']['Cantidad']!=0){
            //$data['Fnc_FormInputs']->formSelectFilter([         'Placeholder' => 'Producto',     'Name' => 'idProducto',  'Id' => 'EditPerdida_idProducto', 'Value' => $x2, 'Required' => 1, 'arrData' => $data['arrProductos'], 'BASE' => $BASE]);
            $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Producto',  'Name' => 'ProductoFake',   'Id' => 'ProductoFake',           'Value' => $x2, 'Required' => 3]);
            $data['Fnc_FormInputs']->formInput(['FormType' => 5, 'Placeholder' => 'Cantidad',  'Name' => 'Cantidad',       'Id' => 'EditPerdida_Cantidad',   'Value' => $x3, 'Required' => 1, 'Icon' => 'bi bi-sort-numeric-down']);
            //datos ocultos
            $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idMovimiento',    'Value' => $data['rowData']['idMovimiento'],  'Required' => 2]);
            $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Old_idProducto',  'Value' => $data['rowData']['idProducto'],    'Required' => 2]);
            $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Old_Cantidad',    'Value' => $data['rowData']['Cantidad'],      'Required' => 2]);
        }
        $data['Fnc_FormInputs']->formInput(['FormType' => 6, 'Placeholder' => 'Valor Total',  'Name' => 'Perdidas',       'Id' => 'EditPerdida_Perdidas',  'Value' => $x4, 'Required' => 2,'Icon' => 'bi bi-currency-dollar']);


        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idExistencia',  'Value' => $data['rowData']['idExistencia'],  'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idCampana',     'Value' => $data['rowData']['idCampana'],     'Required' => 2]);
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
    $("#FormEditPerdida").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/perdidas/update'; ?>';
            let Informacion = $("#FormEditPerdida").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabPerdidasDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/perdidas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCampana']); ?>'}
                ],
                showNoti:'Datos Editados Correctamente',
                closeModal:'#viewModal_2',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
</script>