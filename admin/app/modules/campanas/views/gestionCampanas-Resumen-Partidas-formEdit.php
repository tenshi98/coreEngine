<?php
//Se obtiene el nombre o la razón social
$Entidad = '';
$Entidad .= !empty($data['rowData']['EntidadNick'])
            ? $data['rowData']['EntidadNick'].' | '
            : '';
$Entidad .= !empty($data['rowData']['EntidadNombre'])
            ? $data['rowData']['EntidadApellido'].' '.$data['rowData']['EntidadNombre']
            : $data['rowData']['EntidadRazonSocial'];

/********************************/
//Nombre aleatorio para la variable
$ProdName = 'room_'.rand(1, 999999);
$RandName = 'rand_'.rand(1, 999999);
?>

<form id="FormEditPartida" name="FormEditPartida" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
    <div class="modal-header">
        <?php
        switch ($data['UserData']["sistemaModalSubtitle"]) {
            case 1:
                echo '
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square"></i> Editar Partida '.$Entidad.'
                </h5>';
                break;
            case 2:
                echo '
                <h5 class="modal-title modal-subtitle">
                    <div class="icon"><i class="bi bi-pencil-square"></i></div>
                    Editar Partida '.$Entidad.'<br>
                    <small>Permite editar una partida existente</small>
                </h5>';
                break;
        } ?>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <?php
        //Se verifican si existen los datos
        $x1 = $data['rowData']['Fecha'] ?? '';
        $x4 = $data['rowData']['idEstadoPartida'] ?? '';
        $x5 = $data['rowData']['EntidadSector'] ?? '';
        $x6 = $data['rowData']['EntidadDireccion'] ?? '';
        $x7 = $data['rowData']['EstadoPartida'] ?? '';
        $x8 = $Entidad;

        //Selecciono dependiendo del estado
        switch ($data['rowData']['idEstadoPartida']) {
            /*************************************/
            //Recién Creado
            case 1:
                $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha Partida', 'Name' => 'Fecha',            'Id' => 'EditPartida_Fecha',              'Value' => $x1, 'Required' => 2, 'Icon' => 'bi bi-calendar3']);
                $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Estado',        'Name' => 'idEstadoPartida',  'Id' => 'EditPartida_idEstadoPartida',    'Value' => $x4, 'Required' => 1, 'arrData' => $data['arrEstados']]);
                break;
            /*************************************/
            case 2: //Campaña Enviada
            case 3: //Campaña Revisada
            case 4: //Campaña Confirmada
                $data['Fnc_FormInputs']->formInput([ 'FormType' => 8, 'Placeholder' => 'Fecha Partida', 'Name' => 'Fecha',           'Id' => 'EditPartida_Fecha',            'Value' => $x1, 'Required' => 2, 'Icon' => 'bi bi-calendar3']);
                $data['Fnc_FormInputs']->formInput([ 'FormType' => 1, 'Placeholder' => 'Cliente',       'Name' => 'ClienteFake',     'Id' => 'ClienteFake',                  'Value' => $x8, 'Required' => 3]);
                $data['Fnc_FormInputs']->formInput([ 'FormType' => 1, 'Placeholder' => 'Sector',        'Name' => 'SectorFake',      'Id' => 'SectorFake',                   'Value' => $x5, 'Required' => 3]);
                $data['Fnc_FormInputs']->formInput([ 'FormType' => 1, 'Placeholder' => 'Dirección',     'Name' => 'DireccionFake',   'Id' => 'DireccionFake',                'Value' => $x6, 'Required' => 3]);
                $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Estado',        'Name' => 'idEstadoPartida', 'Id' => 'EditPartida_idEstadoPartida',  'Value' => $x4, 'Required' => 1, 'arrData' => $data['arrEstados']]);
                break;
            /*************************************/
            case 5: //Campaña Rechazada
            case 6: //Partida Entregada
                //nada
                break;
        }

        //Generacion del documento mercantil
        $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Documento Mercantil', 'Name' => 'idDocumentos',    'Id' => 'EditPartida_idDocumentos',    'Value' => '',  'Required' => 2, 'arrData' => $data['arrDocumentos']]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Numero Documento',    'Name' => 'N_Doc',           'Id' => 'EditPartida_N_Doc',           'Value' => '',  'Required' => 1, 'Icon' => 'bi bi-sort-numeric-down']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha de Venta',      'Name' => 'Creacion_fecha',  'Id' => 'EditPartida_Creacion_fecha',  'Value' => $x1, 'Required' => 2, 'Icon' => 'bi bi-calendar3']);
        $data['Fnc_FormInputs']->formTextarea([               'Placeholder' => 'Observaciones',       'Name' => 'Observaciones',   'Id' => 'EditPartida_Observaciones',   'Value' => '',  'Required' => 1]);

        ?>

        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" id="productos_List">
            <?php
            //Recorro
            foreach ($data['arrPartidasProd'] AS $dataP){
                //Variables
                $dataProd_1 = $dataP['Producto'];
                $dataProd_2 = (!empty($dataP['Cantidad']) && $dataP['Cantidad'] != 0)
                            ? $data['Fnc_DataNumbers']->cantidadesDecimalesJustos($dataP['Cantidad'])
                            : '';
                $dataProd_3 = (!empty($dataP['Beneficios']) && $dataP['Beneficios'] != 0)
                            ? $data['Fnc_DataNumbers']->cantidadesDecimalesJustos($dataP['Beneficios'])
                            : '';
                //Datos ocultos
                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Producto_idProdCamp[]', 'Value' => $dataP['idProdCamp'], 'Required' => 2]);
                $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Producto_idProducto[]', 'Value' => $dataP['idProducto'], 'Required' => 2]);
                ?>
                <div class="row">
                    <div class="col-6"> <?php $data['Fnc_FormInputs']->formInput([                                 'FormType' => 1, 'Placeholder' => 'Producto',     'Name' => 'ProductoFake',         'Value' => $dataProd_1,'Required' => 3]); ?></div>
                    <div class="col">   <?php $data['Fnc_FormInputs']->formInput(['FormAling' => 4,'FormCol' => 12,'FormType' => 5, 'Placeholder' => 'Cantidad',     'Name' => 'Producto_Cantidad[]',  'Value' => $dataProd_2,'Required' => 2,'Icon' => 'bi bi-sort-numeric-down']); ?></div>
                    <div class="col">   <?php $data['Fnc_FormInputs']->formInput(['FormAling' => 4,'FormCol' => 12,'FormType' => 6, 'Placeholder' => 'Valor Total',  'Name' => 'Producto_Valor[]',     'Value' => $dataProd_3,'Required' => 2,'Icon' => 'bi bi-currency-dollar']); ?></div>
                    <div class="col">
                        <div class="col-sm-12 field">
                            <select class="form-select" name="Producto_Eliminar[]" id="Producto_Eliminar[]" aria-label="Estado">
                                <option value="1" selected="selected">Mantener</option>
                                <option value="2">Eliminar</option>
                            </select>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                <p class="text-facture">
                    <i class="fa fa-list" aria-hidden="true"></i> Agregar Nuevos Productos
                    <a onclick="productos_add();" class="btn btn-primary btn-sm float-end" ><i class="bi bi-clipboard-plus"></i> Agregar Productos</a>
                </p>
            </div>
            <div class="clearfix"></div>
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" id="<?php echo 'insert_productos_'.$RandName; ?>"></div>
            <div class="clearfix"></div>

        </div>

        <?php
        $data['Fnc_FormInputs']->formSelect([                'Placeholder' => 'Estado Pago',            'Name' => 'idEstadoPago',      'Id' => 'EditPartida_idEstadoPago',     'Value' => 1,  'Required' => 1, 'arrData' => $data['arrEstadoPago']]);
        $data['Fnc_FormInputs']->formSelect([                'Placeholder' => 'Documento Pago',         'Name' => 'idDocumentoPago',   'Id' => 'EditPartida_idDocumentoPago',  'Value' => '', 'Required' => 2, 'arrData' => $data['arrDocumentoPago']]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Numero Documento Pago',  'Name' => 'N_DocPago',         'Id' => 'EditPartida_N_DocPago',        'Value' => '', 'Required' => 1, 'Icon' => 'bi bi-sort-numeric-down']);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idExistencia',   'Value' => $data['rowData']['idExistencia'],           'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idCampana',      'Value' => $data['rowData']['idCampana'],              'Required' => 2]);
        //datos ocultos de la facturacion
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idEntidad',       'Value' => $data['rowData']['idEntidad'],             'Required' => 2]);  //Cliente de la venta
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idUsuario',       'Value' => $data['UserData']['UserID'],               'Required' => 2]);  //Usuario que lo creo
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idTipo',          'Value' => 2,                                         'Required' => 2]);  //Venta
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'fecha_auto',      'Value' => $data['Fnc_ServerServer']->fechaActual(),  'Required' => 2]);  //Fecha de creacion automatica
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Creacion_hora',   'Value' => $data['Fnc_ServerServer']->horaActual(),   'Required' => 2]);  //Hora de creacion automatica
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'FechaPago',       'Value' => $data['Fnc_ServerServer']->fechaActual(),  'Required' => 2]);  //Fecha de creacion automatica
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'ClienteWhatsapp', 'Value' => $Entidad,                                  'Required' => 2]);  //Cliente para el mensaje de whatsapp

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

<div style="display: none;">
    <div id="<?php echo 'clone_productos_'.$RandName; ?>" class="prod_container container" style="margin-bottom: 15px;">
        <div class="row">
            <div class="col-6"><?php $data['Fnc_FormInputs']->formSelect([ 'FormAling' => 4,'FormCol' => 12,                 'Placeholder' => 'Producto',     'Name' => 'NewProducto_idProducto[]',  'Value' => '','Required' => 2,'arrData' => $data['arrProductos']]); ?></div>
            <div class="col">  <?php $data['Fnc_FormInputs']->formInput([  'FormAling' => 4,'FormCol' => 12,'FormType' => 5, 'Placeholder' => 'Cantidad',     'Name' => 'NewProducto_Cantidad[]',    'Value' => '','Required' => 2,'Icon' => 'bi bi-sort-numeric-down']); ?></div>
            <div class="col">  <?php $data['Fnc_FormInputs']->formInput([  'FormAling' => 4,'FormCol' => 12,'FormType' => 6, 'Placeholder' => 'Valor Total',  'Name' => 'NewProducto_Valor[]',       'Value' => '','Required' => 2,'Icon' => 'bi bi-currency-dollar']); ?></div>
            <div class="col-1"><button class="btn btn-danger remove_productos"><i class="bi bi-trash"></i></button></div>
        </div>
    </div>
</div>

<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    $("#FormEditPartida").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidas/update'; ?>';
            let Informacion = $("#FormEditPartida").serialize();
            //datos para el whatsapp
            let Cliente         = $("#FormEditPartida [name='ClienteWhatsapp']").val();
            let idEstadoPartida = $("#FormEditPartida [name='idEstadoPartida']").val();
            //opciones
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabPartidasDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCampana']); ?>'}
                ],
                //showNoti:'Datos Editados Correctamente',
                closeModal:'#viewModal_2',
                closeObject:'#PDloader',
                callFNC:'notiEntrega',
                callFNCData:{
                    "Fono": '<?php echo $data['rowData']['EntidadFono']; ?>',
                    "Cliente": Cliente,
                    "idEstadoPartida": idEstadoPartida
                },
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
    /******************************************/
    //Oculto
    document.getElementById('div_EditPartida_idDocumentos').style.display     = 'none';
    document.getElementById('div_EditPartida_N_Doc').style.display            = 'none';
    document.getElementById('div_EditPartida_Creacion_fecha').style.display   = 'none';
    document.getElementById('div_EditPartida_Observaciones').style.display    = 'none';
    document.getElementById('productos_List').style.display                   = 'none';
    document.getElementById('div_EditPartida_idEstadoPago').style.display     = 'none';
    document.getElementById('div_EditPartida_idDocumentoPago').style.display  = 'none';
    document.getElementById('div_EditPartida_N_DocPago').style.display        = 'none';

    /**********************************************************************/
    //cargo
    document.getElementById("EditPartida_idEstadoPartida").onchange = function() {cngFnc_EstadoPartida()}
    document.getElementById("EditPartida_idDocumentos").onchange    = function() {cngFnc_Documentos()}
    document.getElementById("EditPartida_idEstadoPago").onchange    = function() {cngFnc_EstadoPago()}
    //Ejecutar logica
    function cngFnc_EstadoPartida() {
        //obtengo los valores
        let idEstadoPartida = $("#EditPartida_idEstadoPartida").val();
        //selecciono
        if (idEstadoPartida != "") {
            //selecciono
            switch (idEstadoPartida) {
                //Partida Confirmada
                case '4':
                    document.getElementById('div_EditPartida_idDocumentos').style.display    = 'none';
                    document.getElementById('div_EditPartida_N_Doc').style.display           = 'none';
                    document.getElementById('div_EditPartida_Creacion_fecha').style.display  = 'none';
                    document.getElementById('div_EditPartida_Observaciones').style.display   = 'none';
                    document.getElementById('productos_List').style.display                  = '';
                    break;
                //Partida Entregada
                case '6':
                    document.getElementById('div_EditPartida_idDocumentos').style.display    = '';
                    document.getElementById('div_EditPartida_N_Doc').style.display           = '';
                    document.getElementById('div_EditPartida_Creacion_fecha').style.display  = '';
                    document.getElementById('div_EditPartida_Observaciones').style.display   = '';
                    document.getElementById('productos_List').style.display                  = '';
                    break;
                //el resto
                default:
                    document.getElementById('div_EditPartida_idDocumentos').style.display    = 'none';
                    document.getElementById('div_EditPartida_N_Doc').style.display           = 'none';
                    document.getElementById('div_EditPartida_Creacion_fecha').style.display  = 'none';
                    document.getElementById('div_EditPartida_Observaciones').style.display   = 'none';
                    document.getElementById('productos_List').style.display                  = 'none';
                    break;
            }
        //si el select esta vacio
        }else{
            document.getElementById('div_EditPartida_idDocumentos').style.display    = 'none';
            document.getElementById('div_EditPartida_N_Doc').style.display           = 'none';
            document.getElementById('div_EditPartida_Creacion_fecha').style.display  = 'none';
            document.getElementById('div_EditPartida_Observaciones').style.display   = 'none';
            document.getElementById('productos_List').style.display                  = 'none';
        }
    }
    //Ejecutar logica
    function cngFnc_Documentos() {
        //obtengo los valores
        let idDocumentos = $("#EditPartida_idDocumentos").val();
        //selecciono
        if (idDocumentos != "") {
            //selecciono
            switch (idDocumentos) {
                //Factura
                case '1':
                    document.getElementById('div_EditPartida_idEstadoPago').style.display    = '';
                    break;
                //Boleta
                case '2':
                    document.getElementById('div_EditPartida_idEstadoPago').style.display    = '';
                    break;
                //el resto
                default:
                    document.getElementById('div_EditPartida_idEstadoPago').style.display    = 'none';
                    break;
            }
        //si el select esta vacio
        }else{
            document.getElementById('div_EditPartida_idEstadoPago').style.display    = 'none';
        }
    }
    //Ejecutar logica
    function cngFnc_EstadoPago() {
        //obtengo los valores
        let idEstadoPartida = $("#EditPartida_idEstadoPago").val();
        //selecciono
        if (idEstadoPartida != "") {
            //selecciono
            switch (idEstadoPartida) {
                //Pagado
                case '2':
                    document.getElementById('div_EditPartida_idDocumentoPago').style.display  = '';
                    document.getElementById('div_EditPartida_N_DocPago').style.display        = '';
                    break;
                //el resto
                default:
                    document.getElementById('div_EditPartida_idDocumentoPago').style.display  = 'none';
                    document.getElementById('div_EditPartida_N_DocPago').style.display        = 'none';
                    break;
            }
        //si el select esta vacio
        }else{
            document.getElementById('div_EditPartida_idDocumentoPago').style.display  = 'none';
            document.getElementById('div_EditPartida_N_DocPago').style.display        = 'none';
        }
    }
    /**********************************************************************/
    //Al cargar la pagina
    cngFnc_EstadoPartida();
    /**********************************************************/
	//variable
	let <?php echo $ProdName; ?> = 0; //Productos
    /**********************************************************/
	//Se agrega producto
	function productos_add(){
		//se incrementa en 1
		<?php echo $ProdName; ?>++;
		addElement('<?php echo 'insert_productos_'.$RandName; ?>', '<?php echo 'clone_productos_'.$RandName; ?>', 'new_productos_', '', '', <?php echo $ProdName; ?>);
	}
	/**********************************************************/
	//se eliminan filas
	$(document).on('click', '.remove_productos', function(e) {
		e.preventDefault();
		$(this).parent().parent().parent().remove();
	});
</script>