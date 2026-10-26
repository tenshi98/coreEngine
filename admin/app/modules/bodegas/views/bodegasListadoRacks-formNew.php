<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

// Se obtiene el ID
$DataID = $data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idPasillo']);
?>
<form id="FormNewRack" name="FormNewRack" autocomplete="off" method="POST" action="" role="form" novalidate aria-label="Formulario de ejecucion">
    <div class="modal-header">
        <?php
        switch ($data['UserData']["sistemaModalSubtitle"]) {
            case 1:
                echo '
                <h5 class="modal-title">
                    <i class="bi bi-archive"></i> Crear Nuevo Rack
                </h5>';
                break;
            case 2:
                echo '
                <h5 class="modal-title modal-subtitle">
                    <div class="icon"><i class="bi bi-archive"></i></div>
                    Crear Nuevo Rack<br>
                    <small>Permite crear un rack en ';
                echo $data['rowData']['Bodega'].' / '.$data['rowData']['Zona'].' / '.$data['rowData']['Pasillo'];
                echo '</small>
                </h5>';
                break;
        } ?>
        <button type="button" class="btn-close" aria-label="Close" data-modal-close></button>
    </div>
    <div class="modal-body">
        <?php
        //se dibujan los inputs
        $data['Fnc_FormInputs']->formInput([  'FormType' => 1, 'Placeholder' => 'Nombre',               'Name' => 'Nombre',             'Id' => 'NewRack_Nombre',             'Value' => '',   'Required' => 2, 'Icon' => 'bi bi-archive']);
        $data['Fnc_FormInputs']->formNumberSpinner([           'Placeholder' => 'Niveles',              'Name' => 'Niveles',            'Id' => 'NewRack_Niveles',            'Value' => '1',  'Required' => 2, 'Min' => 1,'Max' => 50,  'Step' => 1,'Ndecimal' => 0, 'DataInfo' => 'Genera los niveles y posiciones al crear el rack (maximo 50)']);
        $data['Fnc_FormInputs']->formNumberSpinner([           'Placeholder' => 'Posiciones por Nivel', 'Name' => 'PosicionesPorNivel', 'Id' => 'NewRack_PosicionesPorNivel', 'Value' => '1',  'Required' => 2, 'Min' => 1,'Max' => 50,  'Step' => 1,'Ndecimal' => 0, 'DataInfo' => 'Posiciones creadas en cada nivel (maximo 50)']);
        $data['Fnc_FormInputs']->formNumberSpinner([           'Placeholder' => 'Orden',                'Name' => 'Orden',              'Id' => 'NewRack_Orden',              'Value' => '1',  'Required' => 2, 'Min' => 1,'Max' => 200, 'Step' => 1,'Ndecimal' => 0, 'DataInfo' => 'Define el orden del Rack creado']);
        $data['Fnc_FormInputs']->formSelect([                  'Placeholder' => 'Estado',               'Name' => 'idEstado',           'Id' => 'NewRack_idEstado',           'Value' => '1',  'Required' => 2, 'arrData' => $data['arrEstado']]);

        //pasillo doble: select crudo para omitir el valor sin gemelo
        if (!empty($data['arrGemelo'])) {
            $data['Fnc_FormInputs']->formSelect([ 'Placeholder' => 'Enfrentar con (pasillo doble)', 'Name' => 'idRackEnfrentado', 'Id' => 'NewRack_idRackEnfrentado', 'Value' => '0',  'Required' => 1, 'arrData' => $data['arrGemelo'], 'DataInfo' => 'Opcional: agrupa este rack con otro del mismo pasillo y la vista 3D los dibuja enfrentados.']);
        }

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idPasillo', 'Value' => $data['rowData']['idPasillo'], 'Required' => 2]);
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
    $("#FormNewRack").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/zonas/pasillos/racks'; ?>';
            let Informacion = $("#FormNewRack").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabRacksP<?php echo $data['rowData']['idPasillo']; ?>', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/zonas/pasillos/racks/updateList/'.$DataID['data']; ?>', refreshTbl:'true'}
                ],
                showNoti:'Rack Creado Correctamente (se generaron sus niveles y posiciones)',
                closeModal:'#viewModal-lg',
                ClearForm:'FormNewRack',
                closeObject:'#PDloader',
                changeValForm: ejecutandoForm,
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });

</script>
