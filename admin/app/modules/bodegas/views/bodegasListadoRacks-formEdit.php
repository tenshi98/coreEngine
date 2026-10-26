<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

// Se obtiene el ID
$DataID = $data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idPasillo']);
?>
<form id="FormEditRack" name="FormEditRack" autocomplete="off" method="POST" action="" role="form" novalidate aria-label="Formulario de ejecucion">
    <div class="modal-header">
        <?php
        switch ($data['UserData']["sistemaModalSubtitle"]) {
            case 1:
                echo '
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square"></i> Editar Rack
                </h5>';
                break;
            case 2:
                echo '
                <h5 class="modal-title modal-subtitle">
                    <div class="icon"><i class="bi bi-pencil-square"></i></div>
                    Editar Rack<br>
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
        $data['Fnc_FormInputs']->formInput([  'FormType' => 1, 'Placeholder' => 'Nombre',               'Name' => 'NombreFake',             'Id' => 'EditRack_NombreFake',             'Value' => ($data['rowData']['Nombre'] ?? ''),            'Required' => 3, 'Icon' => 'bi bi-archive']);
        $data['Fnc_FormInputs']->formInput([  'FormType' => 4, 'Placeholder' => 'Niveles',              'Name' => 'NivelesFake',            'Id' => 'EditRack_NivelesFake',            'Value' => ($data['rowData']['Niveles'] ?? 0),            'Required' => 3, 'Icon' => 'bi bi-layers',        'DataInfo' => 'Las dimensiones se definieron al crear el rack y no se pueden editar']);
        $data['Fnc_FormInputs']->formInput([  'FormType' => 4, 'Placeholder' => 'Posiciones por Nivel', 'Name' => 'PosicionesPorNivelFake', 'Id' => 'EditRack_PosicionesPorNivelFake', 'Value' => ($data['rowData']['PosicionesPorNivel'] ?? 0), 'Required' => 3, 'Icon' => 'bi bi-grid-3x3-gap']);
        $data['Fnc_FormInputs']->formNumberSpinner([           'Placeholder' => 'Orden',                'Name' => 'Orden',                  'Id' => 'EditRack_Orden',                  'Value' => ($data['rowData']['Orden'] ?? 1),              'Required' => 2, 'Min' => 1,'Max' => 200,'Step' => 1,'Ndecimal' => 0, 'DataInfo' => 'Define el orden del Rack creado']);
        $data['Fnc_FormInputs']->formSelect([                  'Placeholder' => 'Estado',               'Name' => 'idEstado',               'Id' => 'EditRack_idEstado',               'Value' => ($data['rowData']['idEstado'] ?? 1),           'Required' => 2, 'arrData' => $data['arrEstado']]);

        //pasillo doble: select crudo para poder omitir el valor sin gemelo
        if (!empty($data['arrGemelo'])) {
            $data['Fnc_FormInputs']->formSelect([ 'Placeholder' => 'Enfrentar con (pasillo doble)', 'Name' => 'idRackEnfrentado', 'Id' => 'EditRack_idRackEnfrentado', 'Value' => ($data['rowData']['idRackEnfrentado'] ?? 0),  'Required' => 1, 'arrData' => $data['arrGemelo'], 'DataInfo' => 'Opcional: agrupa este rack con otro del mismo pasillo y la vista 3D los dibuja enfrentados.']);
        }

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idRack',    'Value' => $data['rowData']['idRack'],    'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idPasillo', 'Value' => $data['rowData']['idPasillo'], 'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idBodegas', 'Value' => $data['rowData']['idBodegas'], 'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'Nombre',    'Value' => $data['rowData']['Nombre'],    'Required' => 2]);
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
    $("#FormEditRack").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/zonas/pasillos/racks/update'; ?>';
            let Informacion = $("#FormEditRack").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabRacksP<?php echo $data['rowData']['idPasillo']; ?>', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/zonas/pasillos/racks/updateList/'.$DataID['data']; ?>', refreshTbl:'true'}
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
