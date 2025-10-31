<?php
//Nombre aleatorio para la variable
$ProdName = 'room_'.rand(1, 999999);
$RandName = 'rand_'.rand(1, 999999);
?>
<div class="modal fade" id="newFormModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="FormNewData" name="FormNewData" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
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
                    $data['Fnc_FormInputs']->formSelectFilter([          'Placeholder' => 'Entidad',               'Name' => 'idEntidad',       'Id' => 'New_idEntidad',       'Value' => '', 'Required' => 2, 'selectProperties' => 'data-dropdown-parent="#newFormModal"','arrData' => $data['arrEntidades'], 'BASE' => $BASE]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha de Cotización',   'Name' => 'Creacion_fecha',  'Id' => 'New_Creacion_fecha',  'Value' => '', 'Required' => 2, 'Icon' => 'bi bi-calendar3']);
                    $data['Fnc_FormInputs']->formTextarea([               'Placeholder' => 'Observaciones',         'Name' => 'Observaciones',   'Id' => 'New_Observaciones',   'Value' => '', 'Required' => 1]);

                    //datos ocultos
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idUsuario',       'Value' => $data['UserData']['UserID'],               'Required' => 2]);  //Usuario que lo creo
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'fecha_auto',      'Value' => $data['Fnc_ServerServer']->fechaActual(), 'Required' => 2]);  //Fecha de creacion automatica
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Creacion_hora',   'Value' => $data['Fnc_ServerServer']->horaActual(),  'Required' => 2]);  //Hora de creacion automatica

                    ?>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <p class="text-facture">
                            <i class="fa fa-list" aria-hidden="true"></i> Items
                            <a onclick="items_add();" class="btn btn-primary btn-sm float-end" ><i class="bi bi-clipboard-plus"></i> Agregar Item</a>
                        </p>
                    </div>
                    <div class="clearfix"></div>

                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" id="<?php echo 'insert_items_'.$RandName; ?>"></div>
                    <div class="clearfix"></div>

                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <p class="text-facture">
                            <i class="fa fa-list" aria-hidden="true"></i> Productos
                            <a onclick="productos_add();" class="btn btn-primary btn-sm float-end" ><i class="bi bi-clipboard-plus"></i> Agregar Productos</a>
                        </p>
                    </div>
                    <div class="clearfix"></div>

                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" id="<?php echo 'insert_productos_'.$RandName; ?>"></div>
                    <div class="clearfix"></div>

                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <p class="text-facture">
                            <i class="fa fa-list" aria-hidden="true"></i> Servicios
                            <a onclick="servicios_add();" class="btn btn-primary btn-sm float-end" ><i class="bi bi-clipboard-plus"></i> Agregar Servicios</a>
                        </p>
                    </div>
                    <div class="clearfix"></div>

                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" id="<?php echo 'insert_servicios_'.$RandName; ?>"></div>
                    <div class="clearfix"></div>

                </div>
                <div class="modal-footer">
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end w-100">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bx bi-x-circle"></i> Cerrar</button>
                        <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div style="display: none;">

    <div id="<?php echo 'clone_items_'.$RandName; ?>" class="prod_container container" style="margin-bottom: 15px;">
        <div class="row">
            <div class="col-6"><?php $data['Fnc_FormInputs']->formInput(['FormAling' => 4,'FormCol' => 12,'FormType' => 1, 'Placeholder' => 'Item',         'Name' => 'Item_Item[]',        'Value' => '', 'Required' => 2]); ?></div>
            <div class="col">  <?php $data['Fnc_FormInputs']->formInput(['FormAling' => 4,'FormCol' => 12,'FormType' => 5, 'Placeholder' => 'Cantidad',     'Name' => 'Item_Number[]',      'Value' => '', 'Required' => 1,'Icon' => 'bi bi-sort-numeric-down']); ?></div>
            <div class="col">  <?php $data['Fnc_FormInputs']->formInput(['FormAling' => 4,'FormCol' => 12,'FormType' => 6, 'Placeholder' => 'Valor Total',  'Name' => 'Item_ValorTotal[]',  'Value' => '', 'Required' => 2,'Icon' => 'bi bi-currency-dollar']); ?></div>
            <div class="col-1"><button class="btn btn-danger remove_items"><i class="bi bi-trash"></i></button></div>
        </div>
    </div>

    <div id="<?php echo 'clone_productos_'.$RandName; ?>" class="prod_container container" style="margin-bottom: 15px;">
        <div class="row">
            <div class="col-6"><?php $data['Fnc_FormInputs']->formSelect([ 'FormAling' => 4,'FormCol' => 12,                 'Placeholder' => 'Producto',     'Name' => 'Producto_idProducto[]',  'Value' => '','Required' => 2,'arrData' => $data['arrProductos']]); ?></div>
            <div class="col">  <?php $data['Fnc_FormInputs']->formInput([  'FormAling' => 4,'FormCol' => 12,'FormType' => 5, 'Placeholder' => 'Cantidad',     'Name' => 'Producto_Number[]',      'Value' => '','Required' => 2,'Icon' => 'bi bi-sort-numeric-down']); ?></div>
            <div class="col">  <?php $data['Fnc_FormInputs']->formInput([  'FormAling' => 4,'FormCol' => 12,'FormType' => 6, 'Placeholder' => 'Valor Total',  'Name' => 'Producto_ValorTotal[]',  'Value' => '','Required' => 2,'Icon' => 'bi bi-currency-dollar']); ?></div>
            <div class="col-1"><button class="btn btn-danger remove_productos"><i class="bi bi-trash"></i></button></div>
        </div>
    </div>

    <div id="<?php echo 'clone_servicios_'.$RandName; ?>" class="prod_container container" style="margin-bottom: 15px;">
        <div class="row">
            <div class="col-6"><?php $data['Fnc_FormInputs']->formSelect([ 'FormAling' => 4,'FormCol' => 12,                 'Placeholder' => 'Servicio',     'Name' => 'Servicio_idServicio[]',  'Value' => '','Required' => 2,'arrData' => $data['arrServicios']]); ?></div>
            <div class="col">  <?php $data['Fnc_FormInputs']->formInput([  'FormAling' => 4,'FormCol' => 12,'FormType' => 5, 'Placeholder' => 'Cantidad',     'Name' => 'Servicio_Number[]',      'Value' => '','Required' => 1,'Icon' => 'bi bi-sort-numeric-down']); ?></div>
            <div class="col">  <?php $data['Fnc_FormInputs']->formInput([  'FormAling' => 4,'FormCol' => 12,'FormType' => 6, 'Placeholder' => 'Valor Total',  'Name' => 'Servicio_ValorTotal[]',  'Value' => '','Required' => 2,'Icon' => 'bi bi-currency-dollar']); ?></div>
            <div class="col-1"><button class="btn btn-danger remove_servicios"><i class="bi bi-trash"></i></button></div>
        </div>
    </div>

</div>

<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    $("#FormNewData").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess']; ?>';
            let Informacion = $("#FormNewData").serialize();
            const Options     = {
                DestinoFrom:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumen/'; ?>',
                ClearForm:'FormNewData',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });

    /*********************************************************************/
    /*                         ELEMENTOS DINAMICOS                       */
    /*********************************************************************/
    /**********************************************************/
	//variable
	let <?php echo $ProdName; ?> = [4];
	<?php echo $ProdName; ?>[0] = 0; //Items
	<?php echo $ProdName; ?>[1] = 0; //Productos
	<?php echo $ProdName; ?>[2] = 0; //Servicios
    /**********************************************************/
	//Se agrega item
	function items_add(){
		//se incrementa en 1
		<?php echo $ProdName; ?>[0]++;
		addElement('<?php echo 'insert_items_'.$RandName; ?>', '<?php echo 'clone_items_'.$RandName; ?>', 'new_items_', '', '', <?php echo $ProdName; ?>[0]);
	}
	//Se agrega producto
	function productos_add(){
		//se incrementa en 1
		<?php echo $ProdName; ?>[1]++;
		addElement('<?php echo 'insert_productos_'.$RandName; ?>', '<?php echo 'clone_productos_'.$RandName; ?>', 'new_productos_', '', '', <?php echo $ProdName; ?>[1]);
	}
	//Se agrega servicio
	function servicios_add(){
		//se incrementa en 1
		<?php echo $ProdName; ?>[2]++;
		addElement('<?php echo 'insert_servicios_'.$RandName; ?>', '<?php echo 'clone_servicios_'.$RandName; ?>', 'new_servicios_', '', '', <?php echo $ProdName; ?>[2]);
	}
	/**********************************************************/
	//se eliminan filas
	$(document).on('click', '.remove_items', function(e) {
		e.preventDefault();
		$(this).parent().parent().parent().remove();
	});
	//se eliminan filas
	$(document).on('click', '.remove_productos', function(e) {
		e.preventDefault();
		$(this).parent().parent().parent().remove();
	});
	//se eliminan filas
	$(document).on('click', '.remove_servicios', function(e) {
		e.preventDefault();
		$(this).parent().parent().parent().remove();
	});
</script>