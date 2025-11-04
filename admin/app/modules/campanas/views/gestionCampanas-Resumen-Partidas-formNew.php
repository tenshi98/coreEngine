<?php
//Nombre aleatorio para la variable
$ProdName = 'room_'.rand(1, 999999);
$RandName = 'rand_'.rand(1, 999999);
?>
<form id="FormNewPartida" name="FormNewPartida" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
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
        $data['Fnc_FormInputs']->formSelectFilter([ 'Placeholder' => 'Entidad',        'Name' => 'idEntidad', 'Id' => 'NewPartida_idEntidad', 'Value' => '', 'Required' => 2, 'arrData' => $data['arrEntidades'], 'BASE' => $BASE]);

        //datos ocultos para la campaña
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idCampana',       'Value' => $data['rowData']['idCampana'],  'Required' => 2]);  //Campaña relacionada
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Fecha',           'Value' => $data['Fnc_ServerServer']->fechaActual(), 'Required' => 2]);  //Fecha de creacion automatica
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idEstadoPartida', 'Value' => 1,                              'Required' => 2]);  //Recién Creado
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idUsuario',       'Value' => $data['UserData']['UserID'],    'Required' => 2]);  //Usuario que lo creo

        ?>

        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
            <p class="text-facture">
                <i class="fa fa-list" aria-hidden="true"></i> Productos
                <a onclick="productos_add();" class="btn btn-primary btn-sm float-end" ><i class="bi bi-clipboard-plus"></i> Agregar Productos</a>
            </p>
        </div>
        <div class="clearfix"></div>
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" id="<?php echo 'insert_productos_'.$RandName; ?>"></div>
        <div class="clearfix"></div>

    </div>
    <div class="modal-footer">
        <div class="d-grid gap-2 d-md-flex justify-content-md-end w-100">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bx bi-x-circle"></i> Cerrar</button>
            <button type="submit" class="btn btn-success" disabled="" id="btn_x-save"><i class="bx bx-save"></i> Guardar Cambios</button>
        </div>
    </div>
</form>

<div style="display: none;">
    <div id="<?php echo 'clone_productos_'.$RandName; ?>" class="prod_container container" style="margin-bottom: 15px;">
        <div class="row">
            <div class="col-11"><?php $data['Fnc_FormInputs']->formSelect(['FormAling' => 4,'FormCol' => 12, 'Placeholder' => 'Producto',  'Name' => 'Producto_idProducto[]',  'Value' => '','Required' => 2,'arrData' => $data['arrProductos']]); ?></div>
            <div class="col-1"><button class="btn btn-danger remove_productos"><i class="bi bi-trash"></i></button></div>
        </div>
    </div>
</div>

<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    $("#FormNewPartida").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidas/new_unique'; ?>';
            let Informacion = $("#FormNewPartida").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabPartidasDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCampana']); ?>'}
                ],
                showNoti:'Dato Creado Correctamente',
                closeModal:'#viewModal',
                ClearForm:'FormNewPartida',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
    /*********************************************************************/
    //Permite utilizar el select filter en modals dinamicos
    $(document).ready(function() {
        $("#NewPartida_idEntidad").select2({
            dropdownParent: $("#FormNewPartida"),
            width: '100%'
        });
    });
    /**********************************************************/
	//variable
	let <?php echo $ProdName; ?> = 0; //Productos
    /**********************************************************/
	//Se agrega producto
	function productos_add(){
		//se incrementa en 1
		<?php echo $ProdName; ?>++;
		addElement('<?php echo 'insert_productos_'.$RandName; ?>', '<?php echo 'clone_productos_'.$RandName; ?>', 'new_productos_', '', '', <?php echo $ProdName; ?>);
        //Se activa el boton guardar
        document.getElementById("btn_x-save").disabled = false;
	}
	/**********************************************************/
	//se eliminan filas
	$(document).on('click', '.remove_productos', function(e) {
		e.preventDefault();
		$(this).parent().parent().parent().remove();
	});

</script>