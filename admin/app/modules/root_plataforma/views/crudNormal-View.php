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
    $arrData = [
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-person-badge',     'Icon_color' => 10,'Title' => 'idUsuario',  'Text' => $data['rowData']['idUsuario']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-envelope',         'Icon_color' => 10,'Title' => 'Email',      'Text' => $data['rowData']['Email'], 'Text_color' => 26, 'Text_copy' => $data['rowData']['Email']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-hash',             'Icon_color' => 10,'Title' => 'Numero',     'Text' => $data['Fnc_DataNumbers']->Valores($data['rowData']['Numero'], 2)],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-person-vcard',     'Icon_color' => 10,'Title' => 'Rut',        'Text' => $data['rowData']['Rut']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-truck',            'Icon_color' => 10,'Title' => 'Patente',    'Text' => $data['rowData']['Patente']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-calendar-event',   'Icon_color' => 10,'Title' => 'Fecha',      'Text' => $data['Fnc_DataDate']->fechaEstandar($data['rowData']['Fecha'])],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-clock',            'Icon_color' => 10,'Title' => 'Hora',       'Text' => $data['rowData']['Hora']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-chat-square-text', 'Icon_color' => 10,'Title' => 'Palabra',    'Text' => $data['rowData']['Palabra']],
    ];
    echo '<div class="row">';
        $OptionsTable['Cols'] = 2;
        echo $data['Fnc_WidgetsViews']->table_v6($arrData, $OptionsTable);
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
