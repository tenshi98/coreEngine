<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<form id="FormEditContacto" name="FormEditContacto" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data" aria-label="Formulario de ejecucion">
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
        //se dibujan los inputs
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre',            'Name'  => 'Nombre',          'Id' => 'EditContacto_Nombre',          'Value'  => ($data['rowData']['Nombre'] ?? ''),       'Required' => 2]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Apellido Paterno',  'Name'  => 'ApellidoPat',     'Id' => 'EditContacto_ApellidoPat',     'Value'  => ($data['rowData']['ApellidoPat'] ?? ''),  'Required' => 2]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Apellido Materno',  'Name'  => 'ApellidoMat',     'Id' => 'EditContacto_ApellidoMat',     'Value'  => ($data['rowData']['ApellidoMat'] ?? ''),  'Required' => 1]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 2,  'Placeholder' => 'Email',             'Name'  => 'Email',           'Id' => 'EditContacto_Email',           'Value'  => ($data['rowData']['Email'] ?? ''),        'Required' => 1,'Icon' => 'bx bx-mail-send']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 11, 'Placeholder' => 'Rut',               'Name'  => 'Rut',             'Id' => 'EditContacto_Rut',             'Value'  => ($data['rowData']['Rut'] ?? ''),          'Required' => 1,'Icon' => 'bi bi-person-circle']);
        $data['Fnc_FormInputs']->formPostData(4, 4, 'exclamation-circle', 0, 'Considerar que todos los números telefónicos ingresados deben iniciar con el +56');
        $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder'  => 'Celular',           'Name'  => 'Fono1',           'Id' => 'EditContacto_Fono1',           'Value'  => ($data['rowData']['Fono1'] ?? ''),          'Required'  => 1,'Icon' => 'bi bi-telephone-fill']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 4,  'Placeholder'  => 'Teléfono',          'Name'  => 'Fono2',           'Id' => 'EditContacto_Fono2',           'Value'  => ($data['rowData']['Fono2'] ?? ''),          'Required'  => 1,'Icon' => 'bi bi-telephone-fill']);
        $data['Fnc_FormInputs']->formSelectDepend([           'Placeholder1' => 'Ciudad',            'Name1' => 'idCiudad',        'Id1'=> 'EditContacto_idCiudad',        'Value1' => ($data['rowData']['idCiudad'] ?? ''),       'Required1' => 1,'arrData1' => $data['arrCiudad'],
                                                              'Placeholder2' => 'Comuna',            'Name2' => 'idComuna',        'Id2'=> 'EditContacto_idComuna',        'Value2' => ($data['rowData']['idComuna'] ?? ''),       'Required2' => 1,'arrData2' => $data['arrComuna']]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder'  => 'Dirección',         'Name'  => 'Direccion',       'Id' => 'EditContacto_Direccion',       'Value'  => ($data['rowData']['Direccion'] ?? ''),      'Required'  => 1,'Icon' => 'bi bi-geo-alt-fill']);
        $data['Fnc_FormInputs']->formSelectFilter([           'Placeholder'  => 'Tipo Contacto',     'Name'  => 'idTipoContacto',  'Id' => 'EditContacto_idTipoContacto',  'Value'  => ($data['rowData']['idTipoContacto'] ?? ''), 'Required'  => 2,'arrData' => $data['arrTipoContacto'], 'BASE' => $BASE]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder'  => 'Cargo',             'Name'  => 'Cargo',           'Id' => 'EditContacto_Cargo',           'Value'  => ($data['rowData']['Cargo'] ?? ''),          'Required'  => 1]);
        $data['Fnc_FormInputs']->formSelect([                 'Placeholder'  => 'Estado',            'Name'  => 'idEstado',        'Id' => 'EditContacto_idEstado',        'Value'  => ($data['rowData']['idEstado'] ?? ''),       'Required'  => 2,'arrData' => $data['arrEstado']]);

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
    /******************************************/
    $("#FormEditContacto").submit(function(e) {
        //Se validan los datos de los formularios
        var validatorResult = validator.checkAll(this);
        //verifico el resultado
        if(validatorResult.valid===false){
            return !!validatorResult.valid;
        }else{
            // Si ya se está ejecutando, salimos
            if (ejecutandoForm.valor) return;
            //Cambio los valores
            ejecutandoForm.valor = true;
            //Ejecucion normal
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
                closeModal:'#viewModal-lg',
                closeObject:'#PDloader',
                changeValForm: ejecutandoForm,
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
