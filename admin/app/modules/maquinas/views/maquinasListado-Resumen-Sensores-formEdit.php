<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

// Se obtiene el ID
$DataID = $data['Fnc_Codification']->encryptDecrypt('encrypt', $data['idMaquina']);
?>

<form id="FormEditSensores" name="FormEditSensores" autocomplete="off" method="POST" action="" role="form" novalidate aria-label="Formulario de ejecucion">
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
        <button type="button" class="btn-close" aria-label="Close" data-modal-close></button>
    </div>
    <div class="modal-body">
        <?php
        //se dibujan los inputs
        $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'Nombre',           'Name' => 'Nombre',     'Id' => 'EditSensores_Nombre',     'Value' => ($data['rowData']['Nombre'] ?? ''),    'Required' => 2]);
        $data['Fnc_FormInputs']->formSelectDepend([           'Placeholder1' => 'Grupo',           'Name1' => 'idGrupo',   'Id1' => 'EditSensores_idGrupo',   'Value1' => ($data['rowData']['idGrupo'] ?? ''),   'Required1' => 2, 'arrData1' => $data['arrGrupos'],
                                                              'Placeholder2' => 'Subgrupo',        'Name2' => 'idSubGrupo','Id2' => 'EditSensores_idSubGrupo','Value2' => ($data['rowData']['idSubGrupo'] ?? ''),'Required2' => 2, 'arrData2' => $data['arrSubgrupos']]);
        $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Unidad de Medida', 'Name' => 'idUniMed',   'Id' => 'EditSensores_idUniMed',   'Value' => ($data['rowData']['idUniMed'] ?? ''),   'Required' => 2, 'arrData' => $data['arrUnimed']]);
        $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Estado Sensor',    'Name' => 'idEstado',   'Id' => 'EditSensores_idEstado',   'Value' => ($data['rowData']['idEstado'] ?? ''),   'Required' => 2, 'arrData' => $data['arrEstado']]);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idMaquina','Value' => ($data['idMaquina'] ?? ''),'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'uid',      'Value' => ($data['rowData']['uid'] ?? ''),'Required' => 2]);
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
    $("#FormEditSensores").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/sensores/update'; ?>';
            let Informacion = $("#FormEditSensores").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabSensoresDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/sensores/updateList/'.$DataID['data']; ?>', callFNC:'initSensoresSortable'}
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
