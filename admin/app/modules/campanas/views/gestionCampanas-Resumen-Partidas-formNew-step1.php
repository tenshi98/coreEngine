<?php
//Nombre aleatorio para la variable
$ProdName = 'room_'.rand(1, 999999);
$RandName = 'rand_'.rand(1, 999999);
?>
<form id="FormNewPartidaStep1" name="FormNewPartidaStep1" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
    <div class="modal-header">
        <?php
        switch ($data['UserData']["sistemaModalSubtitle"]) {
            case 1:
                echo '
                <h5 class="modal-title">
                    <i class="bi bi-search"></i> Filtrar Datos
                </h5>';
                break;
            case 2:
                echo '
                <h5 class="modal-title modal-subtitle">
                    <div class="icon"><i class="bi bi-search"></i></div>
                    Filtrar Datos<br>
                    <small>Permite filtrar los datos en base a la selección de los datos de este formulario</small>
                </h5>';
                break;
        } ?>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col">
                <?php
                //se dibujan los inputs
                $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => '¿Tiene Fono?',       'Name'  => 'idTieneFono',    'Id'  => 'SearchPartida_idTieneFono',   'Value'  => 1, 'Required' => 2,'arrData' => $data['arrOpciones']]);
                $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => '¿Quitar Repetidos?', 'Name'  => 'idRepetidos',    'Id'  => 'SearchPartida_idRepetidos',   'Value'  => 1, 'Required' => 2,'arrData' => $data['arrOpciones']]);
                $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Tipo Entidad',       'Name'  => 'idTipoEntidad',  'Id'  => 'SearchPartida_idTipoEntidad', 'Value'  => '','Required' => 1,'arrData' => $data['arrTipoEntidad']]);
                //Persona natural
                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre',            'Name'  => 'Nombre',         'Id'  => 'SearchPartida_Nombre',        'Value'  => '','Required' => 1]);
                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Apellido Paterno',  'Name'  => 'ApellidoPat',    'Id'  => 'SearchPartida_ApellidoPat',   'Value'  => '','Required' => 1]);
                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Apellido Materno',  'Name'  => 'ApellidoMat',    'Id'  => 'SearchPartida_ApellidoMat',   'Value'  => '','Required' => 1]);
                //empresas
                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Razón Social',      'Name'  => 'RazonSocial',    'Id'  => 'SearchPartida_RazonSocial',   'Value'  => '','Required' => 1]);
                ?>
            </div>
            <div class="col">
                <?php
                //Comun
                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder'  => 'Nick',         'Name'  => 'Nick',       'Id'  => 'SearchPartida_Nick',          'Value'  => '','Required'  => 1]);
                $data['Fnc_FormInputs']->formSelectFilter([           'Placeholder'  => 'Sector',       'Name'  => 'idSector',   'Id'  => 'SearchPartida_idSector',      'Value'  => '','Required'  => 1,'arrData'  => $data['arrSector'], 'BASE' => $BASE]);
                $data['Fnc_FormInputs']->formSelectDepend([           'Placeholder1' => 'Ciudad',       'Name1' => 'idCiudad',   'Id1' => 'SearchPartida_idCiudad',      'Value1' => '','Required1' => 1,'arrData1' => $data['arrCiudad'],
                                                                      'Placeholder2' => 'Comuna',       'Name2' => 'idComuna',   'Id2' => 'SearchPartida_idComuna',      'Value2' => '','Required2' => 1,'arrData2' => $data['arrComuna']]);
                $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder'  => 'Dirección',    'Name'  => 'Direccion',  'Id'  => 'SearchPartida_Direccion',     'Value'  => '','Required'  => 1,'Icon' => 'bi bi-geo-alt-fill']);
                $data['Fnc_FormInputs']->formNumberSpinner([          'Placeholder'  => 'Numero Dias',  'Name'  => 'nDias',      'Id'  => 'SearchPartida_nDias',         'Value'  => '','Required'  => 2,'Min' => 1,'Max' => 20,'Step' => 1,'Ndecimal' => 0]);
                ?>
            </div>
        </div>

        <div class="row">
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

        <?php
        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idCampana',    'Value' => $data['rowData']['idCampana'], 'Required' => 2]);
        ?>
    </div>
    <div class="modal-footer">
        <div class="d-grid gap-2 d-md-flex justify-content-md-end w-100">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bx bi-x-circle"></i> Cerrar</button>
            <button type="submit" class="btn btn-success" disabled="" id="btn_z-save"><i class="bi bi-search"></i> Buscar</button>
        </div>
    </div>
</form>

<div style="display: none;">
    <div id="<?php echo 'clone_productos_'.$RandName; ?>" class="prod_container container" style="margin-bottom: 15px;">
        <div class="row">
            <div class="col-6"><?php $data['Fnc_FormInputs']->formSelect(['FormAling' => 4,'FormCol' => 12, 'Placeholder' => 'Producto',  'Name' => 'Producto_idProducto[]',  'Value' => '','Required' => 2,'arrData' => $data['arrProductos']]); ?></div>
            <div class="col-1"><button class="btn btn-danger remove_productos"><i class="bi bi-trash"></i></button></div>
        </div>
    </div>
</div>

<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    $("#FormNewPartidaStep1").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/partidas/new_step1'; ?>';
            let Informacion = $("#FormNewPartidaStep1").serialize();
            const Options     = {
                UpdateDivFrom : 'modalContent_2',
                ClearForm:'FormNewPartidaStep1',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
    /******************************************/
    //Oculto
    document.getElementById('div_SearchPartida_Nombre').style.display       = 'none';
    document.getElementById('div_SearchPartida_ApellidoPat').style.display  = 'none';
    document.getElementById('div_SearchPartida_ApellidoMat').style.display  = 'none';
    document.getElementById('div_SearchPartida_RazonSocial').style.display  = 'none';

    /**********************************************************************/
    //cargo
    document.getElementById("SearchPartida_idTipoEntidad").onchange = function() {cngFnc_SearchPartida_idTipoEntidad()}
    //Ejecutar logica
    function cngFnc_SearchPartida_idTipoEntidad() {
        //obtengo los valores
        let SearchPartida_idTipoEntidad = $("#SearchPartida_idTipoEntidad").val();
        //selecciono
        if (SearchPartida_idTipoEntidad != "") {
            //selecciono
            switch (SearchPartida_idTipoEntidad) {
                //Persona Natural
                case '1':
                    document.getElementById('div_SearchPartida_Nombre').style.display       = '';
                    document.getElementById('div_SearchPartida_ApellidoPat').style.display  = '';
                    document.getElementById('div_SearchPartida_ApellidoMat').style.display  = '';
                    document.getElementById('div_SearchPartida_RazonSocial').style.display  = 'none';
                    break;
                //Empresas
                case '2':
                    document.getElementById('div_SearchPartida_Nombre').style.display       = 'none';
                    document.getElementById('div_SearchPartida_ApellidoPat').style.display  = 'none';
                    document.getElementById('div_SearchPartida_ApellidoMat').style.display  = 'none';
                    document.getElementById('div_SearchPartida_RazonSocial').style.display  = '';
                    break;
                //el resto
                default:
                    document.getElementById('div_SearchPartida_Nombre').style.display       = 'none';
                    document.getElementById('div_SearchPartida_ApellidoPat').style.display  = 'none';
                    document.getElementById('div_SearchPartida_ApellidoMat').style.display  = 'none';
                    document.getElementById('div_SearchPartida_RazonSocial').style.display  = 'none';
                    break;
            }
        //si el select esta vacio
        }else{
            document.getElementById('div_SearchPartida_Nombre').style.display       = 'none';
            document.getElementById('div_SearchPartida_ApellidoPat').style.display  = 'none';
            document.getElementById('div_SearchPartida_ApellidoMat').style.display  = 'none';
            document.getElementById('div_SearchPartida_RazonSocial').style.display  = 'none';
        }
    }
    /*********************************************************************/
    //Permite utilizar el select filter en modals dinamicos
    $(document).ready(function() {
        $("#SearchPartida_idSector").select2({
            dropdownParent: $("#FormNewPartidaStep1"),
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
        document.getElementById("btn_z-save").disabled = false;
	}
	/**********************************************************/
	//se eliminan filas
	$(document).on('click', '.remove_productos', function(e) {
		e.preventDefault();
		$(this).parent().parent().parent().remove();
	});
</script>