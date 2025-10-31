<form id="FormEditContacto" name="FormEditContacto" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
    <div class="modal-header">
        <?php
        switch ($data['UserData']["sistemaModalSubtitle"]) {
            case 1:
                echo '
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square"></i> Editar Información
                </h5>';
                break;
            case 2:
                echo '
                <h5 class="modal-title modal-subtitle">
                    <div class="icon"><i class="bi bi-pencil-square"></i></div>
                    Editar Información<br>
                    <small>Permite editar un elemento existente</small>
                </h5>';
                break;
        } ?>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <?php
        //Se verifican si existen los datos
        $x1  = $data['rowData']['Nombre'] ?? '';
        $x2  = $data['rowData']['ApellidoPat'] ?? '';
        $x3  = $data['rowData']['ApellidoMat'] ?? '';
        $x4  = $data['rowData']['Email'] ?? '';
        $x5  = $data['rowData']['Rut'] ?? '';
        $x6  = $data['rowData']['Fono1'] ?? '';
        $x7  = $data['rowData']['Fono2'] ?? '';
        $x8  = $data['rowData']['idCiudad'] ?? '';
        $x9  = $data['rowData']['idComuna'] ?? '';
        $x10 = $data['rowData']['Direccion'] ?? '';
        $x11 = $data['rowData']['idTipoContacto'] ?? '';
        $x12 = $data['rowData']['Cargo'] ?? '';
        $x13 = $data['rowData']['idEstado'] ?? '';

        //se dibujan los inputs
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre',            'Name'  => 'Nombre',          'Id' => 'EditContacto_Nombre',          'Value'  => $x1,  'Required' => 2]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Apellido Paterno',  'Name'  => 'ApellidoPat',     'Id' => 'EditContacto_ApellidoPat',     'Value'  => $x2,  'Required' => 2]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Apellido Materno',  'Name'  => 'ApellidoMat',     'Id' => 'EditContacto_ApellidoMat',     'Value'  => $x3,  'Required' => 1]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 2,  'Placeholder' => 'Email',             'Name'  => 'Email',           'Id' => 'EditContacto_Email',           'Value'  => $x4,  'Required' => 1,'Icon' => 'bx bx-mail-send']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 11, 'Placeholder' => 'Rut',               'Name'  => 'Rut',             'Id' => 'EditContacto_Rut',             'Value'  => $x5,  'Required' => 1,'Icon' => 'bi bi-person-circle']);
        $data['Fnc_FormInputs']->formPostData(4, 4, 'exclamation-circle', 0, 'Considerar que todos los números telefónicos ingresados deben iniciar con el +56');
        $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder' => 'Celular',           'Name'  => 'Fono1',           'Id' => 'EditContacto_Fono1',           'Value'  => $x6,  'Required'  => 1,'Icon' => 'bi bi-telephone-fill']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder' => 'Teléfono',          'Name'  => 'Fono2',           'Id' => 'EditContacto_Fono2',           'Value'  => $x7,  'Required'  => 1,'Icon' => 'bi bi-telephone-fill']);
        $data['Fnc_FormInputs']->formSelectDepend([          'Placeholder1' => 'Ciudad',           'Name1' => 'idCiudad',        'Id1'=> 'EditContacto_idCiudad',        'Value1' => $x8,  'Required1' => 1,'arrData1' => $data['arrCiudad'],
                                                               'Placeholder2' => 'Comuna',           'Name2' => 'idComuna',        'Id2'=> 'EditContacto_idComuna',        'Value2' => $x9,  'Required2' => 1,'arrData2' => $data['arrComuna'],
                                                               'FormName' => 'FormEditContacto']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Dirección',         'Name'  => 'Direccion',       'Id' => 'EditContacto_Direccion',       'Value'  => $x10, 'Required' => 1,'Icon' => 'bi bi-geo-alt-fill']);
        $data['Fnc_FormInputs']->formSelectFilter([          'Placeholder' => 'Tipo Contacto',     'Name'  => 'idTipoContacto',  'Id' => 'EditContacto_idTipoContacto',  'Value'  => $x11, 'Required' => 2,'arrData' => $data['arrTipoContacto'], 'BASE' => $BASE]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Cargo',             'Name'  => 'Cargo',           'Id' => 'EditContacto_Cargo',           'Value'  => $x12, 'Required' => 1]);
        $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Estado',            'Name'  => 'idEstado',        'Id' => 'EditContacto_idEstado',        'Value'  => $x13, 'Required' => 2,'arrData' => $data['arrEstado']]);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idContacto','Value' => $data['rowData']['idContacto'],'Required' => 2]);
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
    $("#FormEditContacto").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/contactos/update'; ?>';
            let Informacion = $("#FormEditContacto").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabContactosDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/contactos/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>', refreshTbl:'true'}
                ],
                showNoti:'Datos Editados Correctamente',
                closeModal:'#viewModal',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
    /*********************************************************************/
    //Permite utilizar el select filter en modals dinamicos
    $(document).ready(function() {
        $("#EditContacto_idTipoContacto").select2({
            dropdownParent: $("#FormEditContacto"),
            width: '100%'
        });
    });
</script>