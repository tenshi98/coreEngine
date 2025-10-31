<form id="FormNewProducto" name="FormNewProducto" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
    <div class="modal-header">
        <?php
        switch ($data['UserData']["sistemaModalSubtitle"]) {
            case 1:
                echo '
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark"></i> Crear Nuevo
                </h5>';
                break;
            case 2:
                echo '
                <h5 class="modal-title modal-subtitle">
                    <div class="icon"><i class="bi bi-file-earmark"></i></div>
                    Crear Nuevo<br>
                    <small>Permite crear un nuevo elemento</small>
                </h5>';
                break;
        } ?>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <?php
        //se dibujan los inputs
        $data['Fnc_FormInputs']->formSelectFilter([         'Placeholder' => 'Bodega',       'Name' => 'idBodegas',    'Id' => 'NewProd_idBodegas',   'Value' => '','Required' => 2,'arrData' => $data['arrBodegas'],   'BASE' => $BASE]);
        $data['Fnc_FormInputs']->formSelectFilter([         'Placeholder' => 'Producto',     'Name' => 'idProducto',   'Id' => 'NewProd_idProducto',  'Value' => '','Required' => 2,'arrData' => $data['arrProductos'], 'BASE' => $BASE]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 5, 'Placeholder' => 'Cantidad',     'Name' => 'Number',       'Id' => 'NewProd_Number',      'Value' => '','Required' => 1,'Icon' => 'bi bi-sort-numeric-down']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 6, 'Placeholder' => 'Valor Total',  'Name' => 'ValorTotal',   'Id' => 'NewProd_ValorTotal',  'Value' => '','Required' => 2,'Icon' => 'bi bi-currency-dollar']);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idFacturacion',    'Value' => $data['rowData']['idFacturacion'], 'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idEstadoIngreso',  'Value' => $data['idTipo'],                   'Required' => 2]);

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
    $("#FormNewProducto").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/productos'; ?>';
            let Informacion = $("#FormNewProducto").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabProdDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/productos/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idFacturacion']); ?>', refreshTbl:'true'}
                ],
                showNoti:'Dato Creado Correctamente',
                closeModal:'#viewModal',
                ClearForm:'FormNewProducto',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
    /*********************************************************************/
    //Permite utilizar el select filter en modals dinamicos
    $(document).ready(function() {
        $("#NewProd_idBodegas").select2({
            dropdownParent: $("#FormNewProducto"),
            width: '100%'
        });
        $("#NewProd_idProducto").select2({
            dropdownParent: $("#FormNewProducto"),
            width: '100%'
        });
    });
</script>