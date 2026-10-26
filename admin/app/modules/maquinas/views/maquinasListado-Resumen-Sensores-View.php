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
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" data-modal-close="PopupModalLarge"></button>
</div>
<div class="modal-body">

    <?php
    $Theme_color = 'summary-theme-color-2';
    $arrData = [
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-card-text', 'Icon_color' => 10,'Title' => 'Nombre',            'Text' => ($data['rowData']['Nombre'] ?? '')],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-diagram-3', 'Icon_color' => 10,'Title' => 'Grupo',             'Text' => ($data['rowData']['Grupo'] ?? '')],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-diagram-3', 'Icon_color' => 10,'Title' => 'Subgrupo',          'Text' => ($data['rowData']['SubGrupo'] ?? '')],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-ruler',     'Icon_color' => 10,'Title' => 'Unidad de Medida',  'Text' => ($data['rowData']['UniMed'] ?? '')],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-activity',  'Icon_color' => 10,'Title' => 'Estado Sensor',     'Text' => ($data['rowData']['Estado'] ?? '')],
    ];
    echo $data['Fnc_WidgetsViews']->tittle_v2('Datos del Sensor', 23);
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
