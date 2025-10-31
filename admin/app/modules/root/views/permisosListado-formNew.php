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
                    <?php $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Información Base']); ?>
                    <div class="d-flex">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-6 col-xl-6 col-xxl-6" style="padding-right:6px;">
                            <?php
                            $data['Fnc_FormInputs']->formSelect([                'Placeholder' => 'Categoria Permiso',  'Name' => 'idPermisosCat',  'Value' => '', 'Required' => 2,'arrData' => $data['arrPermisosCat']]);
                            $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Ruta Web',           'Name' => 'RutaWeb',        'Value' => '', 'Required' => 2,'Icon' => 'bi bi-puzzle']);
                            $data['Fnc_FormInputs']->formSelect([                'Placeholder' => 'Nivel Acceso',       'Name' => 'idLevelLimit',   'Value' => '', 'Required' => 2,'arrData' => $data['arrLevelLimit']]);
                            ?>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-6 col-xl-6 col-xxl-6" style="padding-left:6px;">
                            <?php
                            $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Nombre',        'Name' => 'Nombre',         'Value' => '', 'Required' => 2]);
                            $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Controlador',   'Name' => 'RutaController', 'Value' => '', 'Required' => 2,'Icon' => 'bi bi-share-fill']);
                            $data['Fnc_FormInputs']->formSelect([                'Placeholder' => 'Tipo',          'Name' => 'idTipo',         'Value' => '', 'Required' => 2,'arrData' => $data['arrTipos']]);
                            ?>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <?php
                        $data['Fnc_FormInputs']->formTextarea(['Placeholder' => 'Descripcion', 'Name' => 'Descripcion', 'Value' => '', 'Required' => 1]);
                        ?>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" id="TipoCrud">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                            <p class="text-facture">
                                <i class="fa fa-list" aria-hidden="true"></i> Rutas
                                <a onclick="ruta_add();" class="btn btn-primary btn-sm float-end" ><i class="bi bi-clipboard-plus"></i> Agregar Ruta</a>
                            </p>
                        </div>
                        <div class="clearfix"></div>

                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" id="<?php echo 'insert_ruta_'.$RandName; ?>"></div>
                        <div class="clearfix"></div>


                    </div>
                    <?php
                    //datos ocultos
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idEstado','Value' => 1,'Required' => 2]); //Activo por defecto
                    ?>
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

    <div id="<?php echo 'clone_ruta_'.$RandName; ?>" class="prod_container" style="border: 1px solid #ccc;border-radius: 4px;margin-bottom: 15px;background-color: #f5f5f5;padding:15px;">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
			<p class="text-primary">
				<i class="fa fa-list" aria-hidden="true"></i> Crear Nueva Ruta
				<button class="btn btn-danger btn-sm float-end remove_ruta"><i class="bi bi-trash"></i> Borrar Ruta</button>
			</p>
		</div>
		<div class="clearfix"></div>
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
            <?php
            //se dibujan los inputs
            $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Carpeta SubRuta',       'Name' => 'SubRuta[]',    'Value' => '', 'Required' => 2,'Icon' => 'bi bi-share-fill']);
            $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Nombre SubControlador', 'Name' => 'Controller[]', 'Value' => '', 'Required' => 2,'Icon' => 'bi bi-share-fill']);
            ?>
        </div>
        <div class="clearfix"></div>
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
            let Direccion   = '<?php echo $BASE.'/Core/permisos/listado'; ?>';
            let Informacion = $("#FormNewData").serialize();
            const Options     = {
                DestinoFrom:'<?php echo $BASE.'/Core/permisos/listado/resumen/'; ?>',
                ClearForm:'FormNewData',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
    /******************************************/
    //Oculto
    document.getElementById('TipoCrud').style.display = 'none';
    /**********************************************************************/
    //cargo
    document.getElementById("idTipo").onchange = function() {cngFnc_idTipo()}
    //Ejecutar logica
    function cngFnc_idTipo() {
        //obtengo los valores
        let idTipo = $("#idTipo").val();
        //selecciono
        if (idTipo != "") {
            //selecciono
            switch (idTipo) {
                //Solo si es crud resumen
                case '2':
                    document.getElementById('TipoCrud').style.display = '';
                    break;
                //el resto
                default:
                    document.getElementById('TipoCrud').style.display = 'none';
                    break;
            }
        //si el select esta vacio
        }else{
            document.getElementById('TipoCrud').style.display = 'none';
        }
    }
	/**********************************************************/
    //variable
	let <?php echo $ProdName; ?> = 0; //Rutas
    /**********************************************************/
	//Se agrega producto
	function ruta_add(){
		//se incrementa en 1
		<?php echo $ProdName; ?>++;
		addElement('<?php echo 'insert_ruta_'.$RandName; ?>', '<?php echo 'clone_ruta_'.$RandName; ?>', 'new_ruta_', '', '', <?php echo $ProdName; ?>);
	}
	/**********************************************************/
	//se eliminan filas
	$(document).on('click', '.remove_ruta', function(e) {
		e.preventDefault();
		$(this).parent().parent().parent().remove();
	});
</script>