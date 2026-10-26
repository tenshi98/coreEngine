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
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-tools',           'Icon_color' => 10,'Title' => 'Servicio',        'Text' => $data['rowData']['Servicio']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-calendar-event',  'Icon_color' => 10,'Title' => 'Fecha Ingreso',   'Text' => $data['Fnc_DataDate']->fechaEstandar($data['rowData']['Fecha'])],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-currency-dollar', 'Icon_color' => 10,'Title' => 'Monto Servicio',  'Text' => $data['Fnc_DataNumbers']->Valores($data['rowData']['Monto'], 0)],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-power',           'Icon_color' => 10,'Title' => 'Estado',          'Text' => '<span class="badge-sp1 badge-sp1-'.$data['rowData']['EstadoColor'].'">'.$data['rowData']['Estado'].'</span>'],
    ];
    $arrData2 = [
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-chat-left-text',  'Icon_color' => 10,'Title' => 'Observación',     'Text' => $data['rowData']['Observacion']],
    ];

    echo '<div class="row">';
        $OptionsTable['Cols'] = 2;
        echo $data['Fnc_WidgetsViews']->table_v6($arrData1, $OptionsTable);
        $OptionsTable['Cols'] = 1;
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
