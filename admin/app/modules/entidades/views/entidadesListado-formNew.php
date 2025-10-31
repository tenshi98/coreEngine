<div class="modal fade" id="newFormModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
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
                    $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Tipo',              'Name'  => 'idTipo',         'Value'  => '','Required' => 2,'arrData' => $data['arrTipo']]);
                    $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Tipo Entidad',      'Name'  => 'idTipoEntidad',  'Value'  => '','Required' => 2,'arrData' => $data['arrTipoEntidad']]);
                    //Persona natural
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre',            'Name'  => 'Nombre',         'Value'  => '','Required' => 1]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Apellido Paterno',  'Name'  => 'ApellidoPat',    'Value'  => '','Required' => 1]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Apellido Materno',  'Name'  => 'ApellidoMat',    'Value'  => '','Required' => 1]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nick',              'Name'  => 'Nick',           'Value'  => '','Required' => 1]);
                    $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Sexo',              'Name'  => 'idSexo',         'Value'  => '','Required' => 1,'arrData' => $data['arrSexo']]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha Nacimiento',  'Name'  => 'FNacimiento',    'Value'  => '','Required' => 1,'Icon' => 'bi bi-calendar3']);
                    //empresas
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Razón Social',      'Name'  => 'RazonSocial',    'Value'  => '','Required' => 1]);
                    //Comun
                    $data['Fnc_FormInputs']->formSelectDepend([          'Placeholder1' => 'Ciudad',           'Name1' => 'idCiudad',       'Value1' => '','Required1' => 1,'arrData1' => $data['arrCiudad'],
                                                                           'Placeholder2' => 'Comuna',           'Name2' => 'idComuna',       'Value2' => '','Required2' => 1,'arrData2' => $data['arrComuna'],
                                                                           'FormName' => 'FormNewData']);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Dirección',         'Name' => 'Direccion',       'Value'  => '','Required' => 1,'Icon' => 'bi bi-geo-alt-fill']);
                    $data['Fnc_FormInputs']->formSelectFilter([          'Placeholder' => 'Sector',            'Name' => 'idSector',        'Value'  => '','Required' => 1,'selectProperties' => 'data-dropdown-parent="#newFormModal"','arrData' => $data['arrSector'], 'BASE' => $BASE]);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 2,  'Placeholder' => 'Email',             'Name' => 'Email',           'Value'  => '','Required' => 1,'Icon' => 'bx bx-mail-send']);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 11, 'Placeholder' => 'Rut',               'Name' => 'Rut',             'Value'  => '','Required' => 1,'Icon' => 'bi bi-person-circle']);
                    $data['Fnc_FormInputs']->formPostData(4, 4, 'exclamation-circle', 0, 'Considerar que todos los números telefónicos ingresados deben iniciar con el +56');
                    $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder' => 'Celular',           'Name' => 'Fono1',           'Value'  => '','Required' => 1,'Icon' => 'bi bi-telephone-fill']);
                    $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder' => 'Teléfono',          'Name' => 'Fono2',           'Value'  => '','Required' => 1,'Icon' => 'bi bi-telephone-fill']);

                    //datos ocultos
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idEstado', 'Value' => 1,      'Required' => 2]); //Activo
                    $data['Fnc_FormInputs']->formInputHidden(['Name' => 'password', 'Value' => '1234', 'Required' => 2]); //password por defecto

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
    /******************************************/
    //Oculto
    document.getElementById('div_Nombre').style.display       = 'none';
    document.getElementById('div_ApellidoPat').style.display  = 'none';
    document.getElementById('div_ApellidoMat').style.display  = 'none';
    document.getElementById('div_idSexo').style.display       = 'none';
    document.getElementById('div_FNacimiento').style.display  = 'none';
    document.getElementById('div_RazonSocial').style.display  = 'none';

    /**********************************************************************/
    //cargo
    document.getElementById("idTipoEntidad").onchange = function() {cngFnc_idTipoEntidad()}
    //Ejecutar logica
    function cngFnc_idTipoEntidad() {
        //obtengo los valores
        let idTipoEntidad = $("#idTipoEntidad").val();
        //selecciono
        if (idTipoEntidad != "") {
            //selecciono
            switch (idTipoEntidad) {
                //Persona Natural
                case '1':
                    document.getElementById("Nombre").required                = true;
                    document.getElementById("ApellidoPat").required           = true;
                    document.getElementById("RazonSocial").required           = false;
                    document.getElementById('div_Nombre').style.display       = '';
                    document.getElementById('div_ApellidoPat').style.display  = '';
                    document.getElementById('div_ApellidoMat').style.display  = '';
                    document.getElementById('div_idSexo').style.display       = '';
                    document.getElementById('div_FNacimiento').style.display  = '';
                    document.getElementById('div_RazonSocial').style.display  = 'none';
                    break;
                //Empresas
                case '2':
                    document.getElementById("Nombre").required                = false;
                    document.getElementById("ApellidoPat").required           = false;
                    document.getElementById("RazonSocial").required           = true;
                    document.getElementById('div_Nombre').style.display       = 'none';
                    document.getElementById('div_ApellidoPat').style.display  = 'none';
                    document.getElementById('div_ApellidoMat').style.display  = 'none';
                    document.getElementById('div_idSexo').style.display       = 'none';
                    document.getElementById('div_FNacimiento').style.display  = 'none';
                    document.getElementById('div_RazonSocial').style.display  = '';
                    break;
                //el resto
                default:
                    document.getElementById("Nombre").required                = false;
                    document.getElementById("ApellidoPat").required           = false;
                    document.getElementById("RazonSocial").required           = false;
                    document.getElementById('div_Nombre').style.display       = 'none';
                    document.getElementById('div_ApellidoPat').style.display  = 'none';
                    document.getElementById('div_ApellidoMat').style.display  = 'none';
                    document.getElementById('div_idSexo').style.display       = 'none';
                    document.getElementById('div_FNacimiento').style.display  = 'none';
                    document.getElementById('div_RazonSocial').style.display  = 'none';
                    break;
            }
        //si el select esta vacio
        }else{
            document.getElementById("Nombre").required                = false;
            document.getElementById("ApellidoPat").required           = false;
            document.getElementById("RazonSocial").required           = false;
            document.getElementById('div_Nombre').style.display       = 'none';
            document.getElementById('div_ApellidoPat').style.display  = 'none';
            document.getElementById('div_ApellidoMat').style.display  = 'none';
            document.getElementById('div_idSexo').style.display       = 'none';
            document.getElementById('div_FNacimiento').style.display  = 'none';
            document.getElementById('div_RazonSocial').style.display  = 'none';
        }
    }
</script>