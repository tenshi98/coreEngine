<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

// Se obtiene el ID
$DataID = $data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idZona']);
?>
<form id="FormEditPasillo" name="FormEditPasillo" autocomplete="off" method="POST" action="" role="form" novalidate aria-label="Formulario de ejecucion">
    <div class="modal-header">
        <?php
        switch ($data['UserData']["sistemaModalSubtitle"]) {
            case 1:
                echo '
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square"></i> Editar Pasillo
                </h5>';
                break;
            case 2:
                echo '
                <h5 class="modal-title modal-subtitle">
                    <div class="icon"><i class="bi bi-pencil-square"></i></div>
                    Editar Pasillo<br>
                    <small>Permite editar ';
                echo $data['rowData']['Nombre'];
                echo '</small>
                </h5>';
                break;
        } ?>
        <button type="button" class="btn-close" aria-label="Close" data-modal-close></button>
    </div>
    <div class="modal-body">
        <?php
        //se dibujan los inputs
        $data['Fnc_FormInputs']->formInput([  'FormType' => 1, 'Placeholder' => 'Nombre',       'Name' => 'Nombre',       'Id' => 'EditPas_Nombre',       'Value' => ($data['rowData']['Nombre'] ?? ''),        'Required' => 2, 'Icon' => 'bi bi-signpost']);
        $data['Fnc_FormInputs']->formSelect([                  'Placeholder' => 'Orientacion',  'Name' => 'Orientacion',  'Id' => 'EditPas_Orientacion',  'Value' => ($data['rowData']['Orientacion'] ?? 'H'),  'Required' => 2, 'arrData' => [['ID' => 'H','Nombre' => 'Horizontal'],['ID' => 'V','Nombre' => 'Vertical']], 'DataInfo' => 'Define la disposición de los racks dentro del pasillo']);
        $data['Fnc_FormInputs']->formNumberSpinner([           'Placeholder' => 'Orden',        'Name' => 'Orden',        'Id' => 'EditPas_Orden',        'Value' => ($data['rowData']['Orden'] ?? 1),          'Required' => 2, 'Min' => 1,'Max' => 200,'Step' => 1,'Ndecimal' => 0, 'DataInfo' => 'Define el orden del Pasillo creado']);
        $data['Fnc_FormInputs']->formSelect([                  'Placeholder' => 'Estado',       'Name' => 'idEstado',     'Id' => 'EditPas_idEstado',     'Value' => ($data['rowData']['idEstado'] ?? 1),       'Required' => 2, 'arrData' => $data['arrEstado']]);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idPasillo', 'Value' => $data['rowData']['idPasillo'], 'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idBodegas', 'Value' => $data['rowData']['idBodegas'], 'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idZona',    'Value' => $data['rowData']['idZona'],    'Required' => 2]);
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
    /************************************/
    $("#FormEditPasillo").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/zonas/pasillos/update'; ?>';
            let Informacion = $("#FormEditPasillo").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabPasillosZ<?php echo $data['rowData']['idZona']; ?>', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/zonas/pasillos/updateList/'.$DataID['data']; ?>', refreshTbl:'true'}
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
