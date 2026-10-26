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
                <small>Pasillo ';
            echo $data['rowData']['Nombre'];
            echo '</small>
            </h5>';
            break;
    } ?>
    <button type="button" class="btn-close" aria-label="Close" data-modal-close></button>
</div>
<div class="modal-body">

    <?php
    $Theme_color = 'summary-theme-color-2';
    $arrData = [
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-signpost',          'Icon_color' => 10,'Title' => 'Pasillo',      'Text' => $data['rowData']['Nombre']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-compass',           'Icon_color' => 10,'Title' => 'Orientacion',  'Text' => ($data['rowData']['Orientacion']==='V') ? 'Vertical' : 'Horizontal'],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-geo-alt',           'Icon_color' => 10,'Title' => 'Zona',         'Text' => $data['rowData']['Zona']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-building',          'Icon_color' => 10,'Title' => 'Bodega',       'Text' => $data['rowData']['Bodega']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-sort-numeric-down', 'Icon_color' => 10,'Title' => 'Orden',        'Text' => $data['rowData']['Orden']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-power',             'Icon_color' => 10,'Title' => 'Estado',       'Text' => '<span class="badge '.$data['rowData']['EstadoColor'].'">'.$data['rowData']['Estado'].'</span>'],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-archive',           'Icon_color' => 10,'Title' => 'Racks',        'Text' => $data['rowData']['Racks']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-layers',            'Icon_color' => 10,'Title' => 'Niveles',      'Text' => $data['rowData']['Niveles']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-grid-3x3-gap',      'Icon_color' => 10,'Title' => 'Posiciones',   'Text' => $data['rowData']['Posiciones']],
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
