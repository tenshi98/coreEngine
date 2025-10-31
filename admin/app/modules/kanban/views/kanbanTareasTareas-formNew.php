<?php
//Nombre aleatorio para la variable
$ProdName = 'room_'.rand(1, 999999);
$RandName = 'rand_'.rand(1, 999999);
?>
<form id="FormNewTareas" name="FormNewTareas" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
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
        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idUsuario', 'Value' => $data['UserData']['UserID'],'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idKanban',  'Value' => $data['rowData']['idKanban'],'Required' => 2]);
        //Datos antiguos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Fecha_Actual', 'Value' => $data['Fnc_ServerServer']->fechaActual(),'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Hora_Actual',  'Value' => $data['Fnc_ServerServer']->horaActual(),'Required' => 2]);
        ?>

        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
            <p class="text-facture">
                <i class="fa fa-list" aria-hidden="true"></i> Tareas para <strong><?php echo $data['rowData']['Titulo']; ?></strong>
                <a onclick="tareaNew_add();" class="btn btn-primary btn-sm float-end" ><i class="bi bi-clipboard-plus"></i> Agregar Tarea</a>
            </p>
        </div>
        <div class="clearfix"></div>

        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" id="<?php echo 'insert_Newtarea_'.$RandName; ?>"></div>
        <div class="clearfix"></div>

    </div>
    <div class="modal-footer">
        <div class="d-grid gap-2 d-md-flex justify-content-md-end w-100">
            <button type="button" class="btn btn-danger" onclick="listTableDataView('<?php echo $data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idKanban']); ?>')"><i class="bx bx-arrow-back"></i> Volver</button>
            <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
        </div>
    </div>
</form>

<div style="display: none;">

    <div id="<?php echo 'clone_Newtarea_'.$RandName; ?>" class="prod_container" style="border: 1px solid #ccc;border-radius: 4px;margin-bottom: 15px;background-color: #f5f5f5;padding:15px;">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
			<p class="text-primary">
				<i class="fa fa-list" aria-hidden="true"></i> Crear Nueva Tarea
				<button class="btn btn-danger btn-sm float-end remove_Newtarea"><i class="bi bi-trash"></i> Borrar Tarea</button>
			</p>
		</div>
        <?php
        //Se verifica si se permite usar tareas especificas
        if($data['UserData']["KanbanTareasUsoTareas"]==2){ ?>
            <div class="clearfix"></div>
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                <div class="form-group">
                    <?php $data['Fnc_FormInputs']->formSelect([ 'Placeholder' => 'Trabajo Especifico', 'Name' => 'idTrabajo[]', 'Value' => '','Required' => 2,'arrData' => $data['arrTrabajos']]);?>
                </div>
            </div>
        <?php } ?>
		<div class="clearfix"></div>
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
            <div class="form-group">
                <?php $data['Fnc_FormInputs']->formTextarea([ 'Placeholder' => 'Tarea',   'Name' => 'Tarea[]',  'Value' => '','Required' => 2]); ?>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>

</div>

<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    $("#FormNewTareas").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'Tareas'; ?>';
            let Informacion = $("#FormNewTareas").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#modalContent', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/view/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idKanban']); ?>', refreshTbl:'false'}
                ],
                showNoti:'Dato Creado Correctamente',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
	/*********************************************************************/
    /*                        ELEMENTOS DINAMICOS                        */
    /*********************************************************************/
    /**********************************************************/
	//variable
	let <?php echo $ProdName; ?> = 0; //New Tareas
    /**********************************************************/
	//Se agrega producto
	function tareaNew_add(){
		//se incrementa en 1
		<?php echo $ProdName; ?>++;
		addElement('<?php echo 'insert_Newtarea_'.$RandName; ?>', '<?php echo 'clone_Newtarea_'.$RandName; ?>', 'new_Newtarea_', '', '', <?php echo $ProdName; ?>);
	}
	/**********************************************************/
	//se eliminan filas
	$(document).on('click', '.remove_Newtarea', function(e) {
		e.preventDefault();
		$(this).parent().parent().parent().remove();
	});
</script>