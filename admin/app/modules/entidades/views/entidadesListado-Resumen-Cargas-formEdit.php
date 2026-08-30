<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<form id="FormEditCarga" name="FormEditCarga" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data" aria-label="Formulario de ejecucion">
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
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre',            'Name' => 'Nombre',           'Id' => 'EditCargas_Nombre',             'Value' => ($data['rowData']['Nombre'] ?? ''),           'Required' => 2]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Apellido Paterno',  'Name' => 'ApellidoPat',      'Id' => 'EditCargas_ApellidoPat',        'Value' => ($data['rowData']['ApellidoPat'] ?? ''),      'Required' => 2]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Apellido Materno',  'Name' => 'ApellidoMat',      'Id' => 'EditCargas_ApellidoMat',        'Value' => ($data['rowData']['ApellidoMat'] ?? ''),      'Required' => 1]);
        $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Sexo',              'Name' => 'idSexo',           'Id' => 'EditCargas_idSexo',             'Value' => ($data['rowData']['idSexo'] ?? ''),           'Required' => 2,'arrData' => $data['arrSexo']]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha Nacimiento',  'Name' => 'FNacimiento',      'Id' => 'EditCargas_FNacimiento',        'Value' => ($data['rowData']['FNacimiento'] ?? ''),      'Required' => 1,'Icon' => 'bi bi-calendar3']);
        $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Parentesco',        'Name' => 'idParentesco',     'Id' => 'EditCargas_idParentesco',       'Value' => ($data['rowData']['idParentesco'] ?? ''),     'Required' => 1,'arrData' => $data['arrParentesco']]);
        $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Estudios',          'Name' => 'idEstudios',       'Id' => 'EditCargas_idEstudios',         'Value' => ($data['rowData']['idEstudios'] ?? ''),       'Required' => 1,'arrData' => $data['arrEstudios']]);
        $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Estado Estudios',   'Name' => 'idEstadoEstudio',  'Id' => 'EditCargas_idEstadoEstudio',    'Value' => ($data['rowData']['idEstadoEstudio'] ?? ''),  'Required' => 1,'arrData' => $data['arrEstadoEstudio']]);
        $data['Fnc_FormInputs']->formTextarea([               'Placeholder' => 'Observacion',       'Name' => 'ObsEstudios',      'Id' => 'EditCargas_ObsEstudios',        'Value' => ($data['rowData']['ObsEstudios'] ?? ''),      'Required' => 1]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha Vigencia',    'Name' => 'FechaVigencia',    'Id' => 'EditCargas_FechaVigencia',      'Value' => ($data['rowData']['FechaVigencia'] ?? ''),    'Required' => 1,'Icon' => 'bi bi-calendar3']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha Vencimiento', 'Name' => 'FechaVencimiento', 'Id' => 'EditCargas_FechaVencimiento',   'Value' => ($data['rowData']['FechaVencimiento'] ?? ''), 'Required' => 1,'Icon' => 'bi bi-calendar3']);
        $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Estado',            'Name' => 'idEstado',         'Id' => 'EditCargas_Estado',             'Value' => ($data['rowData']['idEstado'] ?? ''),         'Required' => 2,'arrData' => $data['arrEstado']]);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idCargas','Value' => $data['rowData']['idCargas'],'Required' => 2]);
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
    $("#FormEditCarga").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/cargas/update'; ?>';
            let Informacion = $("#FormEditCarga").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabCargasDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/cargas/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>', refreshTbl:'true'}
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

</script>
