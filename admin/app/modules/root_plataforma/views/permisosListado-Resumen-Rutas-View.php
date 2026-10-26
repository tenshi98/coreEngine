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
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-code-slash', 'Icon_color' => 10,'Title' => 'Metodo',           'Text' => $data['rowData']['Metodo']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-link-45deg', 'Icon_color' => 10,'Title' => 'Ruta Web',         'Text' => $data['rowData']['RutaWeb']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-signpost-2', 'Icon_color' => 10,'Title' => 'Ruta Controller',  'Text' => $data['rowData']['RutaController']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-card-text',  'Icon_color' => 10,'Title' => 'Descripcion',      'Text' => $data['rowData']['Descripcion']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-bullseye',   'Icon_color' => 10,'Title' => 'Objetivo',         'Text' => $data['rowData']['LevelLimit']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-code-slash', 'Icon_color' => 10,'Title' => 'Controller',       'Text' => $data['rowData']['Controller']],
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
