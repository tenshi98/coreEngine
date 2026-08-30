<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<form id="FormEditConfiguracion" name="FormEditConfiguracion" autocomplete="off" method="POST" action="" role="form" novalidate enctype="multipart/form-data" aria-label="Formulario de ejecucion">
    <div class="d-flex justify-content-center pt-4">
        <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-6">
            <?php
            //se dibujan los inputs
            $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Datos de la tarjeta SIM', 'Clase' => 'box-title text-color-red-dark']);
            $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'SIM - Numero Telefónico',  'Name' => 'Sim_Num_Tel',  'Id' => 'EditConfig_Sim_Num_Tel',   'Value' => ($data['rowData']['Sim_Num_Tel'] ?? ''),  'Required' => 1,'Icon' => 'bi bi-sd-card']);
            $data['Fnc_FormInputs']->formInput(['FormType' => 1,  'Placeholder' => 'SIM - Compañia',           'Name' => 'Sim_Compania', 'Id' => 'EditConfig_Sim_Compania',  'Value' => ($data['rowData']['Sim_Compania'] ?? ''), 'Required' => 1,'Icon' => 'bi bi-share']);

            $data['Fnc_FormInputs']->formTittle(['Tipo' => 4,'Texto' => 'Configuración', 'Clase' => 'box-title text-color-red-dark']);
            $data['Fnc_FormInputs']->formTime([ 'Placeholder' => 'Tiempo Fuera Linea Máximo', 'Name' => 'TiempoFueraLinea',  'Id' => 'EditConfig_TiempoFueraLinea', 'Value' => ($data['rowData']['TiempoFueraLinea'] ?? ''),'Required' => 1,'Position' => 1,'Icon' => 'bi bi-clock']);

            $data['Fnc_FormInputs']->formPostData(3, 4, 'exclamation-circle', 0, '<strong>Tab:</strong> Esta opción indica en que pestaña de la pantalla principal sera mostrado el equipo');
            $data['Fnc_FormInputs']->formSelectFilter([ 'Placeholder' => 'Tab', 'Name' => 'idTab', 'Id' => 'EditConfig_idTab',  'Value' => ($data['rowData']['idTab'] ?? ''),  'Required' => 1,'arrData' => $data['arrTabs'], 'BASE' => $BASE]);

            $data['Fnc_FormInputs']->formPostData(3, 4, 'exclamation-circle', 0, '<strong>Geolocalización:</strong> Uso de las funciones de gps y alertas relacionadas a este (geocercas, detenciones, ingreso a lugares prohibidos, etc.)');
            $data['Fnc_FormInputs']->formSelect([ 'Placeholder' => 'Uso Geolocalización', 'Name' => 'id_Geo', 'Id' => 'EditConfig_id_Geo',  'Value' => ($data['rowData']['id_Geo'] ?? ''),  'Required' => 1,'arrData' => $data['arrOpciones']]);

            $data['Fnc_FormInputs']->formPostData(3, 4, 'exclamation-circle', 0, '<strong>Sensores:</strong> Uso de las funciones de Telemetria (registro de sensores, alertas, etc.)');
            $data['Fnc_FormInputs']->formSelect([ 'Placeholder' => 'Uso Sensores', 'Name' => 'id_Sensores', 'Id' => 'EditConfig_id_Sensores',  'Value' => ($data['rowData']['id_Sensores'] ?? ''),  'Required' => 1,'arrData' => $data['arrOpciones']]);

            $data['Fnc_FormInputs']->formPostData(3, 4, 'exclamation-circle', 0, '<strong>Backup Tabla relacionada:</strong> Opción de respaldo de la tabla donde se guardan los datos del equipo bajo una cierta cantidad de registros.');
            $data['Fnc_FormInputs']->formSelect([                'Placeholder' => 'Backup Tabla relacionada', 'Name' => 'idBackup',    'Id' => 'EditConfig_idBackup',    'Value' => ($data['rowData']['idBackup'] ?? ''),    'Required' => 1,'arrData' => $data['arrOpciones']]);
            $data['Fnc_FormInputs']->formInput(['FormType' => 4, 'Placeholder' => 'N° Registros para Backup', 'Name' => 'NregBackup',  'Id' => 'EditConfig_NregBackup',  'Value' => ($data['rowData']['NregBackup'] ?? ''),  'Required' => 1,'Icon' => 'bi bi-sort-numeric-down']);

            $data['Fnc_FormInputs']->formPostData(3, 4, 'exclamation-circle', 0, '<strong>Alerta Temprana:</strong> Indica si el equipo de telemetria notificara de inmediato a los usuarios respecto a un error.');
            $data['Fnc_FormInputs']->formSelect([  'Placeholder' => 'Alerta Temprana',         'Name' => 'idAlertaTemprana',    'Id' => 'EditConfig_idAlertaTemprana',   'Value' => ($data['rowData']['idAlertaTemprana'] ?? ''),    'Required' => 1,'arrData' => $data['arrOpciones']]);
            $data['Fnc_FormInputs']->formTime([    'Placeholder' => 'Tiempo Alertas Criticas', 'Name' => 'AlertaTemprCritica',  'Id' => 'EditConfig_AlertaTemprCritica', 'Value' => ($data['rowData']['AlertaTemprCritica'] ?? ''),  'Required' => 1,'Position' => 1,'Icon' => 'bi bi-clock']);
            $data['Fnc_FormInputs']->formTime([    'Placeholder' => 'Tiempo Alertas Normales', 'Name' => 'AlertaTemprNormal',   'Id' => 'EditConfig_AlertaTemprNormal',  'Value' => ($data['rowData']['AlertaTemprNormal'] ?? ''),   'Required' => 1,'Position' => 1,'Icon' => 'bi bi-clock']);


            //datos ocultos
            $data['Fnc_FormInputs']->formInputHidden(['Name' => 'idMaquina','Value' => $data['rowData']['idMaquina'],'Required' => 2]);
            ?>
            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar Cambios</button>
            </div>
        </div>
    </div>
</form>
