<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<div class="modal-header">
    <?php
    switch ($data['UserData']["sistemaModalSubtitle"]) {
        case 1:
            echo '
            <h5 class="modal-title">
                <i class="bi bi-card-checklist"></i> Ver Datos
            </h5>';
            break;
        case 2:
            echo '
            <h5 class="modal-title modal-subtitle">
                <div class="icon"><i class="bi bi-card-checklist"></i></div>
                Ver Datos<br>
                <small>Permite visualizar los datos de un elemento existente</small>
            </h5>';
            break;
    } ?>
    <button type="button" class="btn-close" aria-label="Close" data-modal-close></button>
</div>
<div class="modal-body">

    <?php
    $Theme_color = 'summary-theme-color-2';
    $arrData1 = [
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-card-text',        'Icon_color' => 10,'Title' => 'Nombre',            'Text' => $data['rowData']['ApellidoPat'].' '.$data['rowData']['ApellidoMat'].' '.$data['rowData']['Nombre']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-calendar-heart',   'Icon_color' => 10,'Title' => 'Fecha Nacimiento',  'Text' => $data['Fnc_DataDate']->fechaEstandar($data['rowData']['FNacimiento'])],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-gender-ambiguous', 'Icon_color' => 10,'Title' => 'Sexo',              'Text' => $data['rowData']['Sexo']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-people',           'Icon_color' => 10,'Title' => 'Parentesco',        'Text' => $data['rowData']['Parentesco']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-power',            'Icon_color' => 10,'Title' => 'Estado',            'Text' => '<span class="badge-sp1 badge-sp1-'.$data['rowData']['EstadoColor'].'">'.$data['rowData']['Estado'].'</span>'],
    ];
    $arrData2 = [
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-journal-text',    'Icon_color' => 10,'Title' => 'Estudios',               'Text' => $data['rowData']['Estudios']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-clipboard-check', 'Icon_color' => 10,'Title' => 'Estado Estudio',         'Text' => $data['rowData']['EstadoEstudio']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-chat-left-text',  'Icon_color' => 10,'Title' => 'Observaciones Estudio',  'Text' => $data['rowData']['ObsEstudios']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-calendar-event',  'Icon_color' => 10,'Title' => 'Fecha Vigencia',         'Text' => $data['Fnc_DataDate']->fechaEstandar($data['rowData']['FechaVigencia'])],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-calendar-event',  'Icon_color' => 10,'Title' => 'Fecha Vencimiento',      'Text' => $data['Fnc_DataDate']->fechaEstandar($data['rowData']['FechaVencimiento'])],
    ];
    // Imprimo resultados
    echo $data['Fnc_WidgetsViews']->tittle_v2('Datos Básicos', 23);
    echo '<div class="row">';
        $OptionsTable['Cols'] = 2;
        echo $data['Fnc_WidgetsViews']->table_v6($arrData1, $OptionsTable);
    echo '</div>';
    echo $data['Fnc_WidgetsViews']->tittle_v2('Datos Académicos', 23);
    echo '<div class="row">';
        $OptionsTable['Cols'] = 2;
        echo $data['Fnc_WidgetsViews']->table_v6($arrData2, $OptionsTable);
    echo '</div>';

    ?>

</div>
<?php
if($data['UserData']["sistemaModalCloseBTN"]==2){
    echo '
    <div class="modal-footer">
        <div class="d-grid gap-2 d-md-flex justify-content-md-end w-100">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bx bi-x-circle"></i> Cerrar</button>
        </div>
    </div>';
}else{
    echo '<style>.modal-body {max-height: 80vh;}</style>';
} ?>
