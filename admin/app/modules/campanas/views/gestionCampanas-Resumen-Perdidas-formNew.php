<form id="FormNewPerdida" name="FormNewPerdida" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
    <div class="modal-header">
        <?php
        switch ($data['UserData']["sistemaModalSubtitle"]) {
            case 1:
                echo '
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark"></i> Crear Perdida
                </h5>';
                break;
            case 2:
                echo '
                <h5 class="modal-title modal-subtitle">
                    <div class="icon"><i class="bi bi-file-earmark"></i></div>
                    Crear Perdida<br>
                    <small>Permite ingresar las perdidas en las campañas</small>
                </h5>';
                break;
        } ?>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">

        <div class="row mb-3">
            <label class="col-sm-4 col-form-label">Tipo Perdida</label>
            <div class="col-sm-8 field">
                <select class="form-select " name="TipoPerdida" id="TipoPerdida" aria-label="Tipo Perdida" required="required">
                    <option value="">Seleccione una Opción</option>
                    <option value="1">Perdida Normal</option>
                    <option value="2">Perdida Productos</option>
                </select>
            </div>
        </div>

        <?php
        //se dibujan los inputs
        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Item',         'Name' => 'Item',                 'Id' => 'NewPerdida_Item',                 'Value' => '',  'Required' => 2]);
        $data['Fnc_FormInputs']->formSelectFilter([         'Placeholder' => 'Producto',     'Name' => 'idProducto',           'Id' => 'NewPerdida_idProducto',           'Value' => '',  'Required' => 1, 'arrData' => $data['arrProductos'], 'BASE' => $BASE]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 5, 'Placeholder' => 'Cantidad',     'Name' => 'Cantidad',      'Id' => 'NewPerdida_Cantidad',      'Value' => '',  'Required' => 1,'Icon' => 'bi bi-sort-numeric-down']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 6, 'Placeholder' => 'Valor Total',  'Name' => 'Perdidas',  'Id' => 'NewPerdida_Perdidas',  'Value' => '',  'Required' => 2,'Icon' => 'bi bi-currency-dollar']);

        //datos ocultos para el costo
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idCampana',       'Value' => $data['rowData']['idCampana'],             'Required' => 2]);  //Campaña relacionada
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idUsuario',       'Value' => $data['UserData']['UserID'],               'Required' => 2]);  //Usuario que lo creo
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idTipo',          'Value' => 2,                                         'Required' => 2]);  //Perdida
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'fecha_auto',      'Value' => $data['Fnc_ServerServer']->fechaActual(), 'Required' => 2]);  //Fecha de creacion automatica
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Creacion_fecha',  'Value' => $data['Fnc_ServerServer']->fechaActual(), 'Required' => 2]);  //Fecha de creacion automatica
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Creacion_hora',   'Value' => $data['Fnc_ServerServer']->horaActual(),  'Required' => 2]);  //Hora de creacion automatica
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idEstadoPago',    'Value' => 1,                                         'Required' => 2]);  //No Pagado

        //permite la interaccion con la bodega, para generar documentos de ingreso o egreso
        if($data['UserData']["gestionDocumentosUsoBodega"]==2){
            $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idBodegasEgreso',   'Value' => $data['rowData']['idBodegas'],   'Required' => 2]);  //Bodega preconfigurada
        }

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
    $("#FormNewPerdida").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/perdidas'; ?>';
            let Informacion = $("#FormNewPerdida").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabPerdidasDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/perdidas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCampana']); ?>'}
                ],
                showNoti:'Dato Creado Correctamente',
                closeModal:'#viewModal_2',
                ClearForm:'FormNewPerdida',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
    /*********************************************************************/
    //Permite utilizar el select filter en modals dinamicos
    $(document).ready(function() {
        $("#NewPerdida_idProducto").select2({
            dropdownParent: $("#FormNewPerdida"),
            width: '100%'
        });
    });
    /******************************************/
    //Oculto
    document.getElementById('div_NewPerdida_Item').style.display        = 'none';
    document.getElementById('div_NewPerdida_idProducto').style.display  = 'none';
    document.getElementById('div_NewPerdida_Cantidad').style.display    = 'none';
    document.getElementById('div_NewPerdida_Perdidas').style.display    = 'none';

    /**********************************************************************/
    //cargo
    document.getElementById("TipoPerdida").onchange = function() {cngFnc_TipoPerdida()}
    //Ejecutar logica
    function cngFnc_TipoPerdida() {
        //obtengo los valores
        let TipoPerdida = $("#TipoPerdida").val();
        //selecciono
        if (TipoPerdida != "") {
            //selecciono
            switch (TipoPerdida) {
                //Perdida Normal
                case '1':
                    document.getElementById('div_NewPerdida_Item').style.display        = '';
                    document.getElementById('div_NewPerdida_idProducto').style.display  = 'none';
                    document.getElementById('div_NewPerdida_Cantidad').style.display    = 'none';
                    document.getElementById('div_NewPerdida_Perdidas').style.display    = '';
                    break;
                //Perdida Productos
                case '2':
                    document.getElementById('div_NewPerdida_Item').style.display        = '';
                    document.getElementById('div_NewPerdida_idProducto').style.display  = '';
                    document.getElementById('div_NewPerdida_Cantidad').style.display    = '';
                    document.getElementById('div_NewPerdida_Perdidas').style.display    = '';
                    break;
                //el resto
                default:
                    document.getElementById('div_NewPerdida_Item').style.display        = 'none';
                    document.getElementById('div_NewPerdida_idProducto').style.display  = 'none';
                    document.getElementById('div_NewPerdida_Cantidad').style.display    = 'none';
                    document.getElementById('div_NewPerdida_Perdidas').style.display    = 'none';
                    break;
            }
        //si el select esta vacio
        }else{
            document.getElementById('div_NewPerdida_Item').style.display        = 'none';
            document.getElementById('div_NewPerdida_idProducto').style.display  = 'none';
            document.getElementById('div_NewPerdida_Cantidad').style.display    = 'none';
            document.getElementById('div_NewPerdida_Perdidas').style.display    = 'none';
        }
    }
</script>