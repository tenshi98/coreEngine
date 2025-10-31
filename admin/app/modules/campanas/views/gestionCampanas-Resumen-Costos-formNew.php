<form id="FormNewCosto" name="FormNewCosto" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
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
        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Item',    'Name' => 'Item',    'Id' => 'NewCosto_Item',    'Value' => '', 'Required' => 2]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 6, 'Placeholder' => 'Costos',  'Name' => 'Costos',  'Id' => 'NewCosto_Costos',  'Value' => '', 'Required' => 2,'Icon' => 'bi bi-currency-dollar']);

        //datos ocultos para el costo
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idCampana',  'Value' => $data['rowData']['idCampana'],             'Required' => 2]);  //Campaña relacionada
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Fecha',      'Value' => $data['Fnc_ServerServer']->fechaActual(), 'Required' => 2]);  //Fecha de creacion automatica
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idUsuario',  'Value' => $data['UserData']['UserID'],               'Required' => 2]);  //Usuario que lo creo

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
    $("#FormNewCosto").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/costos'; ?>';
            let Informacion = $("#FormNewCosto").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabCostosDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/costos/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCampana']); ?>'}
                ],
                showNoti:'Dato Creado Correctamente',
                closeModal:'#viewModal',
                ClearForm:'FormNewCosto',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });

</script>