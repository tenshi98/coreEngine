<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

// Se obtiene el ID
$DataID = $data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idMaquina']);

// Se preparan las ayudas exactas por tipo de alarma
$arrAyudaTipo = [
    1 => 'Al menos un sensor se encuentra fuera del rango de trabajo normal',
    2 => 'Todos los sensores se encuentra fuera del rango de trabajo normal',
    3 => 'Todos los sensores estan con un valor predefinido, el cual indica error',
    4 => 'Todos los sensores tienen el mismo valor de error',
    5 => 'El promedio de los sensores esta dentro del rango establecido',
    6 => 'El promedio de los sensores esta fuera del rango establecido',
    7 => 'Un sensor tiene un valor que es x porcentaje inferior o superior a otro sensor dentro del grupo de sensores',
    8 => 'El sensor se encuentra activo fuera del horario establecido entre la hora de inicio y la hora de termino',
];
$arrAyudaFila = [
    1 => 'Rango de valores donde normalmente trabaja el sensor, en caso de que un valor sea distinto de este rango (inferior al minimo, superior al maximo) marcara una alerta personalizada',
    2 => 'Rango de valores donde normalmente trabaja el sensor, en caso de que todos los valores sean distintos de este rango (inferior al minimo, superior al maximo) marcara una alerta personalizada',
    3 => 'El valor especifico que es considerado como error (por ejemplo 0 o 1)',
    8 => 'Valor con el cual se considera que el sensor esta activo',
];

// Se define el render de una fila de sensor
$renderSensorRow = function($fila, $arrSensores) use ($data) {
    $idSensor = $fila['idSensor'] ?? '';
    ?>
    <div class="alarma-sensor-row border rounded p-2 mb-2">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <?php $data['Fnc_FormInputs']->formSelect([
                    'FormAling' => 4,
                    'FormCol'   => 12,
                    'Placeholder' => 'Sensor',
                    'Name'        => 'Sensor_idSensor[]',
                    'Value'       => $idSensor,
                    'Required'    => 2,
                    'arrData'     => $arrSensores,
                ]); ?>
            </div>
            <div class="col-12 col-md-7">
                <div class="row g-2">
                    <div class="col-12 col-md-6 alarma-field-rango">
                        <?php $data['Fnc_FormInputs']->formInput(['FormAling' => 4,'FormCol' => 12,'FormType' => 5, 'Placeholder' => 'Rango Inicio',        'Name' => 'RangoInicio[]',     'Value' => ($fila['RangoInicio'] ?? ''),     'Required' => 1]); ?>
                    </div>
                    <div class="col-12 col-md-6 alarma-field-rango">
                        <?php $data['Fnc_FormInputs']->formInput(['FormAling' => 4,'FormCol' => 12,'FormType' => 5, 'Placeholder' => 'Rango Término',       'Name' => 'RangoTermino[]',    'Value' => ($fila['RangoTermino'] ?? ''),    'Required' => 1]); ?>
                    </div>
                    <div class="col-12 col-md-6 alarma-field-especifico">
                        <?php $data['Fnc_FormInputs']->formInput(['FormAling' => 4,'FormCol' => 12,'FormType' => 5, 'Placeholder' => 'Valor Específico',    'Name' => 'ValorEspecifico[]', 'Value' => ($fila['ValorEspecifico'] ?? ''), 'Required' => 1]); ?>
                    </div>
                    <div class="col-12 col-md-6 alarma-field-encendido">
                        <?php $data['Fnc_FormInputs']->formInput(['FormAling' => 4,'FormCol' => 12,'FormType' => 5, 'Placeholder' => 'Valor Encendido',     'Name' => 'ValorEncendido[]',  'Value' => ($fila['ValorEncendido'] ?? ''),  'Required' => 1]); ?>
                    </div>
                </div>
                <div class="alarma-help-fila formHelp text-muted d-none"></div>
            </div>
            <div class="col-12 col-md-1 text-end">
                <button type="button" class="btn btn-danger btn-sm alarma-remove-row" title="Eliminar fila" onclick="alarmasRemoveSensorRow(this)"><i class="bi bi-trash"></i></button>
            </div>
        </div>
    </div>
    <?php
};
?>

<form id="FormNewAlarmas" name="FormNewAlarmas" autocomplete="off" method="POST" action="" role="form" novalidate aria-label="Formulario de ejecucion">
    <div class="modal-header">
        <?php
        switch ($data['UserData']["sistemaModalSubtitle"]) {
            case 1:
                echo '
                <h5 class="modal-title">
                    <i class="bi bi-megaphone"></i> Crear Nueva Alarma
                </h5>';
                break;
            case 2:
                echo '
                <h5 class="modal-title modal-subtitle">
                    <div class="icon"><i class="bi bi-megaphone"></i></div>
                    Crear Nueva Alarma<br>
                    <small>Permite crear una alarma personalizada</small>
                </h5>';
                break;
        } ?>
        <button type="button" class="btn-close" aria-label="Close" data-modal-close></button>
    </div>
    <div class="modal-body">
        <?php if (empty($data['arrSensores'])) { ?>
            <div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> La máquina no tiene sensores activos. Agregue al menos un sensor activo antes de crear una alarma.</div>
        <?php } ?>

        <div class="row g-2">
            <?php
            $data['Fnc_FormInputs']->formInput(['FormType' => 1, 'Placeholder' => 'Nombre',         'Name' => 'Nombre',       'Id' => 'NewAlarmas_Nombre',    'Value' => '', 'Required' => 2]);
            $data['Fnc_FormInputs']->formSelect([                'Placeholder' => 'Tipo de Alarma', 'Name' => 'idTipoAlarma', 'Id' => 'Alarmas_idTipoAlarma', 'Value' => '', 'Required' => 2, 'arrData' => $data['arrTipos']]);
            ?>
            <div class="col-12">
                <div id="Alarmas_AyudaTipo" class="alert alert-info py-1 px-2 mb-0 small"></div>
            </div>
            <?php
            $data['Fnc_FormInputs']->formInput(['FormType' => 4, 'Placeholder' => 'N° Máximo Errores',        'Name' => 'MaxErrores',           'Id' => 'NewAlarmas_MaxErrores',           'Value' => '',  'Required' => 2]);
            $data['Fnc_FormInputs']->formInput(['FormType' => 4, 'Placeholder' => 'N° Actual Errores',        'Name' => 'ActualErrores',        'Id' => 'NewAlarmas_ActualErrores',        'Value' => '0', 'Required' => 1]);
            $data['Fnc_FormInputs']->formInput(['FormType' => 5, 'Placeholder' => 'Valor de Error',           'Name' => 'ValorError',           'Id' => 'NewAlarmas_ValorError',           'Value' => '',  'Required' => 1]);
            $data['Fnc_FormInputs']->formInput(['FormType' => 5, 'Placeholder' => 'Valor Mínimo',             'Name' => 'ValorMinimo',          'Id' => 'NewAlarmas_ValorMinimo',          'Value' => '',  'Required' => 1]);
            $data['Fnc_FormInputs']->formInput(['FormType' => 5, 'Placeholder' => 'Valor Máximo',             'Name' => 'ValorMaximo',          'Id' => 'NewAlarmas_ValorMaximo',          'Value' => '',  'Required' => 1]);
            $data['Fnc_FormInputs']->formInput(['FormType' => 5, 'Placeholder' => 'Porcentaje de Diferencia', 'Name' => 'PorcentajeDiferencia', 'Id' => 'NewAlarmas_PorcentajeDiferencia', 'Value' => '',  'Required' => 1]);
            $data['Fnc_FormInputs']->formInput(['FormType' => 9, 'Placeholder' => 'Hora Inicio',              'Name' => 'HoraInicio',           'Id' => 'NewAlarmas_HoraInicio',           'Value' => '',  'Required' => 1, 'Icon' => 'bi bi-clock']);
            $data['Fnc_FormInputs']->formInput(['FormType' => 9, 'Placeholder' => 'Hora Término',             'Name' => 'HoraTermino',          'Id' => 'NewAlarmas_HoraTermino',          'Value' => '',  'Required' => 1, 'Icon' => 'bi bi-clock']);
            $data['Fnc_FormInputs']->formSelect([                'Placeholder' => 'Prioridad',                'Name' => 'idPrioridad',          'Id' => 'NewAlarmas_idPrioridad',          'Value' => '',  'Required' => 2, 'arrData' => $data['arrPrioridades']]);
            $data['Fnc_FormInputs']->formSelect([                'Placeholder' => 'Unidad de Medida',         'Name' => 'idUniMed',             'Id' => 'NewAlarmas_idUniMed',             'Value' => '1', 'Required' => 1, 'arrData' => $data['arrUnimed']]);
            ?>
        </div>

        <hr>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0"><i class="bi bi-diagram-3"></i> Sensores de la alarma</h6>
            <button type="button" class="btn btn-primary btn-sm" onclick="alarmasAddSensorRow()"><i class="bi bi-plus-circle"></i> Agregar Sensor</button>
        </div>
        <div id="AlarmasSensoresRows"></div>

        <?php
        //datos ocultos
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idMaquina', 'Value' => $data['rowData']['idMaquina'],'Required' => 2]);
        $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idEstado',  'Value' => '1','Required' => 1]);
        ?>
    </div>
    <div class="modal-footer">
        <div class="d-grid gap-2 d-md-flex justify-content-md-end w-100">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bx bi-x-circle"></i> Cerrar</button>
            <button type="submit" class="btn btn-success" <?php echo empty($data['arrSensores']) ? 'disabled' : ''; ?>><i class="bx bx-save"></i> Guardar Cambios</button>
        </div>
    </div>
</form>

<div style="display: none;">
    <div id="AlarmasSensorRowProto">
        <?php $renderSensorRow([], $data['arrSensores']); ?>
    </div>
</div>

<script>
    /*********************************************************************/
    /*                      EJECUCION DE LA LOGICA                       */
    /*********************************************************************/
    <?php
    // Valor random
    $random = bin2hex(random_bytes(8));
    ?>
    // Ayudas exactas por tipo
    const AlarmasAyudaTipo_<?php echo $random; ?> = <?php echo json_encode($arrAyudaTipo, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    const AlarmasAyudaFila_<?php echo $random; ?> = <?php echo json_encode($arrAyudaFila, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    let AlarmasRowIndex_<?php echo $random; ?> = 0;

    /************************************/
    // Se inicializa Select2 de una fila
    function alarmasInitRow(row) {
        $(row).find('select').each(function() {
            if ($(this).hasClass('select2-hidden-accessible')) { return; }
            $(this).select2({
                dropdownParent: $('#viewModal-xl'),
                width: '100%',
                language: 'es'
            });
        });
    }

    /************************************/
    // Se generan IDs unicos en el clon (Select2 usa el id como clave)
    function alarmasMakeIdsUnique(row, index) {
        const suffix = '_row' + index;
        const map = {};
        $(row).find('[id]').each(function() {
            map[this.id] = this.id + suffix;
            this.id = this.id + suffix;
        });
        $(row).find('style').each(function() {
            let css = this.textContent;
            Object.keys(map).forEach(function(oldId) {
                css = css.split('#' + oldId).join('#' + map[oldId]);
            });
            this.textContent = css;
        });
        $(row).find('label[for]').each(function() {
            const nuevo = map[this.getAttribute('for')];
            if (nuevo) { this.setAttribute('for', nuevo); }
        });
        $(row).find('[data-select2-id]').removeAttr('data-select2-id');
    }

    /************************************/
    // Se agrega una fila de sensor
    function alarmasAddSensorRow() {
        const proto = document.getElementById('AlarmasSensorRowProto');
        const cont  = document.getElementById('AlarmasSensoresRows');
        if (!proto || !cont) { return; }
        AlarmasRowIndex_<?php echo $random; ?>++;
        const clone = proto.cloneNode(true);
        clone.id = 'AlarmaSensorRow_' + AlarmasRowIndex_<?php echo $random; ?>;
        clone.style.display = '';
        alarmasMakeIdsUnique(clone, AlarmasRowIndex_<?php echo $random; ?>);
        cont.appendChild(clone);
        alarmasInitRow(clone);
        alarmasToggleTypeFields(document.getElementById('Alarmas_idTipoAlarma').value);
    }

    /************************************/
    // Se elimina una fila de sensor
    function alarmasRemoveSensorRow(btn) {
        $(btn).closest('.alarma-sensor-row').remove();
    }

    /************************************/
    // Se muestran u ocultan los campos segun el tipo
    function alarmasToggleTypeFields(idTipoAlarma) {
        const tipo = parseInt(idTipoAlarma, 10) || 0;
        const toggle = function(selector, visible) {
            const $field = $(selector);
            $field.toggleClass('d-none', !visible);
            $field.find('input, select').prop('required', visible);
        };

        // Campos a nivel de alarma
        toggle('#div_NewAlarmas_ValorError', tipo === 4);
        toggle('#div_NewAlarmas_ValorMinimo', tipo === 5 || tipo === 6);
        toggle('#div_NewAlarmas_ValorMaximo', tipo === 5 || tipo === 6);
        toggle('#div_NewAlarmas_PorcentajeDiferencia', tipo === 7);
        toggle('#div_NewAlarmas_HoraInicio', tipo === 8);
        toggle('#div_NewAlarmas_HoraTermino', tipo === 8);

        // Campos a nivel de fila de sensor
        toggle('#AlarmasSensoresRows .alarma-field-rango', tipo === 1 || tipo === 2);
        toggle('#AlarmasSensoresRows .alarma-field-especifico', tipo === 3);
        toggle('#AlarmasSensoresRows .alarma-field-encendido', tipo === 8);

        // Ayudas
        $('#Alarmas_AyudaTipo').text(AlarmasAyudaTipo_<?php echo $random; ?>[tipo] || '');
        const ayudaFila = AlarmasAyudaFila_<?php echo $random; ?>[tipo] || '';
        $('#AlarmasSensoresRows .alarma-help-fila').toggleClass('d-none', ayudaFila === '').text(ayudaFila);
    }

    /************************************/
    // Se inicializa el formulario
    $(document).ready(function() {
        $('#Alarmas_idTipoAlarma').on('change', function() {
            alarmasToggleTypeFields(this.value);
        });
        $('#AlarmasSensoresRows .alarma-sensor-row').each(function() {
            alarmasInitRow(this);
        });
        alarmasToggleTypeFields($('#Alarmas_idTipoAlarma').val() || 0);
    });

    /************************************/
    $("#FormNewAlarmas").submit(function(e) {
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
            let Direccion   = '<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/alarmas'; ?>';
            let Informacion = $("#FormNewAlarmas").serialize();
            const Options     = {
                UpdateDiv : [
                    {Div:'#tabAlarmasDataTable', fromData:'<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/alarmas/updateList/'.$DataID['data']; ?>'}
                ],
                showNoti:'Dato Creado Correctamente',
                closeModal:'#viewModal-lg',
                ClearForm:'FormNewAlarmas',
                closeObject:'#PDloader',
                changeValForm: ejecutandoForm,
            };
            //Se envian los datos al formulario
            SendDataForms(Metodo, Direccion, Informacion, Options);
        }
    });

</script>
