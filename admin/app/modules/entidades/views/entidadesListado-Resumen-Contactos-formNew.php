<form id="FormNewContacto" name="FormNewContacto" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data">
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
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre',            'Name'  => 'Nombre',          'Id' => 'NewContacto_Nombre',          'Value'  => '','Required' => 2]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Apellido Paterno',  'Name'  => 'ApellidoPat',     'Id' => 'NewContacto_ApellidoPat',     'Value'  => '','Required' => 2]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Apellido Materno',  'Name'  => 'ApellidoMat',     'Id' => 'NewContacto_ApellidoMat',     'Value'  => '','Required' => 1]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 2,  'Placeholder' => 'Email',             'Name'  => 'Email',           'Id' => 'NewContacto_Email',           'Value'  => '','Required' => 1,'Icon' => 'bx bx-mail-send']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 11, 'Placeholder' => 'Rut',               'Name'  => 'Rut',             'Id' => 'NewContacto_Rut',             'Value'  => '','Required' => 1,'Icon' => 'bi bi-person-circle']);
        $data['Fnc_FormInputs']->formPostData(4, 4, 'exclamation-circle', 0, 'Considerar que todos los números telefónicos ingresados deben iniciar con el +56');
        $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder' => 'Celular',           'Name'  => 'Fono1',           'Id' => 'NewContacto_Fono1',           'Value'  => '','Required'  => 1,'Icon' => 'bi bi-telephone-fill']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder' => 'Teléfono',          'Name'  => 'Fono2',           'Id' => 'NewContacto_Fono2',           'Value'  => '','Required'  => 1,'Icon' => 'bi bi-telephone-fill']);
        $data['Fnc_FormInputs']->formSelectDepend([          'Placeholder1' => 'Ciudad',           'Name1' => 'idCiudad',        'Id1'=> 'NewContacto_idCiudad',        'Value1' => '','Required1' => 1,'arrData1' => $data['arrCiudad'],
                                                               'Placeholder2' => 'Comuna',           'Name2' => 'idComuna',        'Id2'=> 'NewContacto_idComuna',        'Value2' => '','Required2' => 1,'arrData2' => $data['arrComuna'],
                                                               'FormName' => 'FormNewContacto']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Dirección',         'Name'  => 'Direccion',       'Id' => 'NewContacto_Direccion',       'Value'  => '','Required' => 1,'Icon' => 'bi bi-geo-alt-fill']);
        $data['Fnc_FormInputs']->formSelectFilter([          'Placeholder' => 'Tipo Contacto',     'Name'  => 'idTipoContacto',  'Id' => 'NewContacto_idTipoContacto',  'Value'  => '','Required' => 2,'arrData' => $data['arrTipoContacto'], 'BASE' => $BASE]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Cargo',             'Name'  => 'Cargo',           'Id' => 'NewContacto_Cargo',           'Value'  => '','Required' => 1]);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idEntidad','Value' => $data['rowData']['idEntidad'],'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idEstado', 'Value' => 1, 'Required' => 2]); //Activo
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
    $("#FormNewContacto").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/contactos'; ?>';
            let Informacion = $("#FormNewContacto").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabContactosDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/contactos/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>', refreshTbl:'true'}
                ],
                showNoti:'Dato Creado Correctamente',
                closeModal:'#viewModal',
                ClearForm:'FormNewContacto',
                closeObject:'#PDloader',
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });
    /*********************************************************************/
    //Permite utilizar el select filter en modals dinamicos
    $(document).ready(function() {
        $("#NewContacto_idTipoContacto").select2({
            dropdownParent: $("#FormNewContacto"),
            width: '100%'
        });
    });
</script>