<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<form id="FormEditPlanes" name="FormEditPlanes" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data" aria-label="Formulario de ejecucion">
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
        $x_EditPlanes_Monto = (!empty($data['rowData']['Monto']) && $data['rowData']['Monto'] != 0)
                            ? $data['Fnc_DataNumbers']->cantidadesDecimalesJustos($data['rowData']['Monto'])
                            : '';

        //se dibujan los inputs
        $data['Fnc_FormInputs']->formSelectFilter([           'Placeholder' => 'Servicio',    'Name' => 'idServicio',   'Id' => 'EditPlanes_idServicio',  'Value' => ($data['rowData']['idServicio'] ?? ''),  'Required' => 2,'arrData' => $data['arrServicios'],   'BASE' => $BASE]);
        $data['Fnc_FormInputs']->formInput(['FormType' => 8,  'Placeholder' => 'Fecha',       'Name' => 'Fecha',        'Id' => 'EditPlanes_Fecha',       'Value' => ($data['rowData']['Fecha'] ?? ''),       'Required' => 2, 'Icon' => 'bi bi-calendar3']);
        $data['Fnc_FormInputs']->formInput(['FormType' => 6,  'Placeholder' => 'Monto',       'Name' => 'Monto',        'Id' => 'EditPlanes_Monto',       'Value' => $x_EditPlanes_Monto,                     'Required' => 2, 'Icon' => 'bi bi-currency-dollar']);
        $data['Fnc_FormInputs']->formTextarea([               'Placeholder' => 'Observacion', 'Name' => 'Observacion',  'Id' => 'EditPlanes_Observacion', 'Value' => ($data['rowData']['Observacion'] ?? ''), 'Required' => 1]);
        $data['Fnc_FormInputs']->formSelect([                 'Placeholder' => 'Estado',      'Name' => 'idEstado',     'Id' => 'EditPlanes_idEstado',    'Value' => ($data['rowData']['idEstado'] ?? ''),    'Required' => 2,'arrData' => $data['arrEstado']]);

        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idPlan','Value' => $data['rowData']['idPlan'],'Required' => 2]);
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
    $("#FormEditPlanes").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/planes/update'; ?>';
            let Informacion = $("#FormEditPlanes").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabPlanesDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/planes/updateList/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idEntidad']); ?>', refreshTbl:'true'}
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
        $("#EditPlanes_idServicio").select2({
            dropdownParent: $("#FormEditPlanes"),
            width: '100%'
        });
    });

</script>
