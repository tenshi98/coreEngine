<form id="FormEditProducto" name="FormEditProducto" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
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
        $x3 = $data['rowData']['idProducto'] ?? '';
        $x4 = (!empty($data['rowData']['Number']) && $data['rowData']['Number'] != 0)
            ? $data['Fnc_DataNumbers']->cantidadesDecimalesJustos($data['rowData']['Number'])
            : '';
        $x5 = (!empty($data['rowData']['ValorTotal']) && $data['rowData']['ValorTotal'] != 0)
            ? $data['Fnc_DataNumbers']->cantidadesDecimalesJustos($data['rowData']['ValorTotal'])
            : '';

        //se dibujan los inputs
        $data['Fnc_FormInputs']->formSelectFilter([         'Placeholder' => 'Producto',        'Name' => 'idProducto',         'Id' => 'EditProd_idProducto', 'Value' => $x3,'Required' => 2,'arrData' => $data['arrProductos'],   'BASE' => $BASE]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 5, 'Placeholder' => 'Cantidad',        'Name' => 'Number',             'Id' => 'EditProd_Number',     'Value' => $x4,'Required' => 2,'Icon' => 'bi bi-sort-numeric-down']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 6, 'Placeholder' => 'Valor Total',     'Name' => 'ValorTotal',         'Id' => 'EditProd_ValorTotal', 'Value' => $x5,'Required' => 2,'Icon' => 'bi bi-currency-dollar']);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idExistencia',   'Value' => $data['rowData']['idExistencia'],  'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idCotizacion',   'Value' => $data['rowData']['idCotizacion'],  'Required' => 2]);
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
    $("#FormEditProducto").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/productos/update'; ?>';
            let Informacion = $("#FormEditProducto").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabProdDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/productos/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCotizacion']); ?>', refreshTbl:'true'}
                ],
                showNoti:'Datos Editados Correctamente',
                closeModal:'#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
    /*********************************************************************/
    //Permite utilizar el select filter en modals dinamicos
    $(document).ready(function() {
        $("#EditProd_idProducto").select2({
            dropdownParent: $("#FormEditProducto"),
            width: '100%'
        });
    });
</script>