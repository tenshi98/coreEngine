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
                    $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Documento Mercantil', 'Name' => 'idDocumentos',    'Id' => 'New_idDocumentos',    'Value' => '', 'Required' => 2, 'arrData' => $data['arrDocumentos']]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Numero Documento',    'Name' => 'N_Doc',           'Id' => 'New_N_Doc',           'Value' => '', 'Required' => 1, 'Icon' => 'bi bi-sort-numeric-down']);
                    $data['Fnc_FormInputs']->formSelectFilter([          'Placeholder' => 'Entidad',             'Name' => 'idEntidad',       'Id' => 'New_idEntidad',       'Value' => '', 'Required' => 2, 'selectProperties' => 'data-dropdown-parent="#newFormModal"','arrData' => $data['arrEntidades'], 'BASE' => $BASE]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha de Creacion',   'Name' => 'Creacion_fecha',  'Id' => 'New_Creacion_fecha',  'Value' => '', 'Required' => 2, 'Icon' => 'bi bi-calendar3']);
                    $data['Fnc_FormInputs']->formTextarea([               'Placeholder' => 'Observaciones',       'Name' => 'Observaciones',   'Id' => 'New_Observaciones',   'Value' => '', 'Required' => 1]);

                    //datos ocultos
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idUsuario',       'Value' => $data['UserData']['UserID'],               'Required' => 2]);  //Usuario que lo creo
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idTipo',          'Value' => $data['idTipo'],                           'Required' => 2]);  //Tipo de movimiento
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'fecha_auto',      'Value' => $data['Fnc_ServerServer']->fechaActual(), 'Required' => 2]);  //Fecha de creacion automatica
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Creacion_hora',   'Value' => $data['Fnc_ServerServer']->horaActual(),  'Required' => 2]);  //Hora de creacion automatica
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idEstadoPago',    'Value' => 1,                                         'Required' => 2]);  //No Pagado

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

                    <div id="div_Bodegas">
                        <?php
                        //permite la interaccion con la bodega, para generar documentos de ingreso o egreso
                        if($data['UserData']["gestionDocumentosUsoBodega"]==2){
                            //Se verifica movimiento
                            switch ($data['idTipo']) {
                                /************************************/
                                //Ingreso
                                case 1:
                                    $data['Fnc_FormInputs']->formSelect([ 'Placeholder' => 'Bodega Ingreso', 'Name' => 'idBodegasIngreso',   'Id' => 'New_idBodegasIngreso',  'Value' => '','Required' => 1,'arrData' => $data['arrBodegas']]);
                                    break;
                                /************************************/
                                //Egreso
                                case 2:
                                    $data['Fnc_FormInputs']->formSelect([ 'Placeholder' => 'Bodega Egreso',  'Name' => 'idBodegasEgreso',    'Id' => 'New_idBodegasEgreso',   'Value' => '','Required' => 1,'arrData' => $data['arrBodegas']]);
                                    break;
                            }
                        }
                        ?>
                    </div>

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

                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" id="div_Guias">
                        <p class="text-facture">
                            <i class="fa fa-list" aria-hidden="true"></i> Guias de Despacho
                            <a onclick="guia_add();" class="btn btn-primary btn-sm float-end" ><i class="bi bi-clipboard-plus"></i> Agregar Guias de Despacho</a>
                        </p>
                    </div>
                    <div class="clearfix"></div>

                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" id="<?php echo 'insert_guia_'.$RandName; ?>"></div>
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

    <div id="<?php echo 'clone_guia_'.$RandName; ?>" class="prod_container container" style="margin-bottom: 15px;">
        <div class="row">
            <div class="col">
                <div class="col-md-12 field" id="div_idFacturacionRel_114">
					<select class="form-select select2_idFacturacionRel_114 " name="idFacturacionRel[]" id="idFacturacionRel_114" aria-label="Guias de Despacho" required="required">
						<option value="" selected="selected">Seleccione una Opción</option>
					</select>
				</div>
            </div>
            <div class="col-1">
                <button class="btn btn-danger remove_guia"><i class="bi bi-trash"></i></button>
            </div>
        </div>
    </div>

</div>

<script>
    /******************************************/
    //Oculto
    document.getElementById('div_Bodegas').style.display = 'none';
    document.getElementById('div_Guias').style.display   = 'none';

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
    /**********************************************************************/
    <?php
    //filtro
    $newData = $data['Fnc_CommonData']->agruparPorClave ($data['arrGuias'], 'idEntidad' );
    //Recorro
    foreach ($newData AS $EntidadID=>$datos){
        //imprimimos la categoría
        $DataID   = 'let id_data_guias_'.$EntidadID.' = new Array(""';
        $DataText = 'let data_guias_'.$EntidadID.'    = new Array("Seleccione una Opción"';
        //se recorren los datos dentro de la categoría
        foreach ($datos AS $crud){
            // Construir nombre de la guía de forma más compacta
            $x_nombre = 'Guia Despacho ';
            $x_nombre .= (!empty($crud['N_Doc'])) ? $crud['N_Doc'] : 'nRef '.$crud['idFacturacion'];
            $x_nombre .= (!empty($crud['Creacion_fecha'])) ? ' Fecha '.$data['Fnc_DataDate']->fechaEstandar($crud['Creacion_fecha']) : '';
            //se imprime
            $DataID   .= ',"'.$crud['idFacturacion'].'"';
            $DataText .= ',"'.$x_nombre.'"';
        }
        //se cierra
        $DataID   .= ');';
        $DataText .= ');';
        //Se imprime
        echo $DataID;
        echo $DataText;
    }
    ?>

    //Si el select cambio
    document.getElementById("New_idEntidad").onchange = function() {cngFnc_idEntidad()};

    //Funcion cambio de estado
    function cngFnc_idEntidad() {
        //obtengo los valores
        let Componente   = $("#New_idEntidad").val();
        let idDocumentos = $("#New_idDocumentos").val();
        //Verifico que valor exista
        if (Componente != "" && idDocumentos != "") {
            //Se vacia div
            $("#insert_guia").empty();
            //actualizo los datos del select
            try {
                //Obtengo las variables con los id y los datos
                slectId_guias   = eval("id_data_guias_" + Componente);
                slectData_guias = eval("data_guias_" + Componente);
                //se vacia
                $("#idFacturacionRel_114").empty();
                //Agregar elementos al select
                for(i=0;i<slectId_guias.length;i++){
                    $("#idFacturacionRel_114").append('<option value="'+slectId_guias[i]+'">'+slectData_guias[i]+'</option>');
                }
            //si el select previo da error
            } catch (error) {
                //Vaciar select y poner opcion por defecto
                $("#idFacturacionRel_114").empty().append('<option value="0" selected="">Seleccione una Opción</option>');
            }
            //selecciono
            switch (idDocumentos) {
                case '1': document.getElementById('div_Guias').style.display  = '';     break;//Factura
                case '2': document.getElementById('div_Guias').style.display  = 'none'; break;//Boleta
                case '3': document.getElementById('div_Guias').style.display  = 'none'; break;//Guia Despacho
                default: document.getElementById('div_Guias').style.display   = 'none'; break;//el resto
            }
        //si el select previo no tiene datos
        }else{
            $("#idFacturacionRel_114").empty();
            document.getElementById('div_Guias').style.display  = 'none';
        }
        //reposiciono el selected al primer elemento
        document.getElementById("idFacturacionRel_114").selectedIndex = "0";
    }

    /**********************************************************/
	//variable
	let <?php echo $ProdName; ?> = [4];
	<?php echo $ProdName; ?>[0] = 0; //Items
	<?php echo $ProdName; ?>[1] = 0; //Productos
	<?php echo $ProdName; ?>[2] = 0; //Servicios
	<?php echo $ProdName; ?>[3] = 0; //Guias de despacho
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
        //Mostrar el div
        document.getElementById('div_Bodegas').style.display       = '';
	}
	//Se agrega servicio
	function servicios_add(){
		//se incrementa en 1
		<?php echo $ProdName; ?>[2]++;
		addElement('<?php echo 'insert_servicios_'.$RandName; ?>', '<?php echo 'clone_servicios_'.$RandName; ?>', 'new_servicios_', '', '', <?php echo $ProdName; ?>[2]);
	}
	//Se agrega guia
	function guia_add(){
		//se incrementa en 1
		<?php echo $ProdName; ?>[3]++;
		addElement('<?php echo 'insert_guia_'.$RandName; ?>', '<?php echo 'clone_guia_'.$RandName; ?>', 'new_guia_', '', '', <?php echo $ProdName; ?>[3]);
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
	//se eliminan filas
	$(document).on('click', '.remove_guia', function(e) {
		e.preventDefault();
		$(this).parent().parent().parent().remove();
	});
</script>