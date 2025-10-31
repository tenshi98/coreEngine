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
                    //se dibujan los inputs
                    $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Prioridad',     'Name' => 'idPrioridad',  'Id' => 'NewTarea_idPrioridad',  'Value' => '', 'Required' => 2,'arrData' => $data['arrPrioridad']]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha Termino', 'Name' => 'Fecha',        'Id' => 'NewTarea_Fecha',        'Value' => '', 'Required' => 2,'Icon' => 'bi bi-calendar3']);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Titulo',        'Name' => 'Titulo',       'Id' => 'NewTarea_Titulo',       'Value' => '', 'Required' => 2]);
                    $data['Fnc_FormInputs']->formTextarea([               'Placeholder' => 'Descripcion',   'Name' => 'Descripcion',  'Id' => 'NewTarea_Descripcion',  'Value' => '', 'Required' => 2]);

                    //datos ocultos
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idKanbanEstado', 'Value' => 1,'Required' => 2]); //Primer panel
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idEstadoCierre', 'Value' => 1,'Required' => 2]); //abierta
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'FechaCreacion',  'Value' => $data['Fnc_ServerServer']->fechaActual(),'Required' => 2]);
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idUsuario',      'Value' => $data['UserData']['UserID'],'Required' => 2]);
                    //Datos antiguos
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Fecha_Actual', 'Value' => $data['Fnc_ServerServer']->fechaActual(),'Required' => 2]);
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Hora_Actual',  'Value' => $data['Fnc_ServerServer']->horaActual(),'Required' => 2]);
                    ?>

                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <p class="text-facture">
                            <i class="fa fa-list" aria-hidden="true"></i> Tareas
                            <a onclick="tarea_add();" class="btn btn-primary btn-sm float-end" ><i class="bi bi-clipboard-plus"></i> Agregar Tarea</a>
                        </p>
                    </div>
                    <div class="clearfix"></div>

                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" id="<?php echo 'insert_tarea_'.$RandName; ?>"></div>
                    <div class="clearfix"></div>

                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <p class="text-facture">
                            <i class="fa fa-list" aria-hidden="true"></i> Participantes
                            <a onclick="participante_add();" class="btn btn-primary btn-sm float-end" ><i class="bi bi-person-plus"></i> Agregar Participante</a>
                        </p>
                    </div>
                    <div class="clearfix"></div>

                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" id="<?php echo 'insert_participante_'.$RandName; ?>"></div>
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

    <div id="<?php echo 'clone_tarea_'.$RandName; ?>" class="prod_container" style="border: 1px solid #ccc;border-radius: 4px;margin-bottom: 15px;background-color: #f5f5f5;padding:15px;">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
			<p class="text-primary">
				<i class="fa fa-list" aria-hidden="true"></i> Crear Nueva Tarea
				<button class="btn btn-danger btn-sm float-end remove_tarea"><i class="bi bi-trash"></i> Borrar Tarea</button>
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

    <div id="<?php echo 'clone_participante_'.$RandName; ?>" class="prod_container" style="border: 1px solid #ccc;border-radius: 4px;margin-bottom: 15px;background-color: #f5f5f5;padding:15px;">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
			<p class="text-primary">
				<i class="fa fa-list" aria-hidden="true"></i> Agregar Participante
				<button class="btn btn-danger btn-sm float-end remove_participante"><i class="bi bi-trash"></i> Borrar Participante</button>
			</p>
		</div>
		<div class="clearfix"></div>
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
            <div class="form-group">
                <?php $data['Fnc_FormInputs']->formSelect([ 'Placeholder' => 'Participante', 'Name' => 'idParticipante[]', 'Value' => '','Required' => 2,'arrData' => $data['arrUsuarios']]);?>
            </div>
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess']; ?>';
            let Informacion = $("#FormNewData").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#listTableData', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/updateList'; ?>', refreshTbl:'true', callFNC:'call_1'}
                ],
                showNoti:'Dato Creado Correctamente',
                closeModal:'#newFormModal',
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
	<?php echo $ProdName; ?>[0] = 0; //Tareas
	<?php echo $ProdName; ?>[1] = 0; //Participantes
	<?php echo $ProdName; ?>[2] = 0; //New Tareas
	<?php echo $ProdName; ?>[3] = 0; //New Participantes
    /**********************************************************/
	//Se agrega producto
	function tarea_add(){
		//se incrementa en 1
		<?php echo $ProdName; ?>[0]++;
		addElement('<?php echo 'insert_tarea_'.$RandName; ?>', '<?php echo 'clone_tarea_'.$RandName; ?>', 'new_tarea_', '', '', <?php echo $ProdName; ?>[0]);
	}
	/**********************************************************/
	//Se agrega producto
	function participante_add(){
		//se incrementa en 1
		<?php echo $ProdName; ?>[1]++;
		addElement('<?php echo 'insert_participante_'.$RandName; ?>', '<?php echo 'clone_participante_'.$RandName; ?>', 'new_participante_', '', '', <?php echo $ProdName; ?>[1]);
	}
	/**********************************************************/
	/**********************************************************/
	//se eliminan filas
	$(document).on('click', '.remove_tarea', function(e) {
		e.preventDefault();
		$(this).parent().parent().parent().remove();
	});
	/**********************************************************/
	//se eliminan filas
	$(document).on('click', '.remove_participante', function(e) {
		e.preventDefault();
		$(this).parent().parent().parent().remove();
	});
</script>