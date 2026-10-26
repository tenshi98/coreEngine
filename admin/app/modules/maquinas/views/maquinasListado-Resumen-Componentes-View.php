<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

// Variables del componente
$NombreArchivo = $data['rowData']['NombreArchivo'] ?? '';
$Anio          = $data['rowData']['AnioFabricacion'] ?? '';
$Tipo          = ((int)($data['rowData']['Nivel'] ?? 1) === 1) ? 'Componente' : 'Subcomponente';

// Ruta jerárquica: máquina + niveles (el último es el nodo actual)
$arrRuta = is_array($data['arrRuta'] ?? null) ? $data['arrRuta'] : [];
?>
<div class="modal-header">
    <?php
    switch ($data['UserData']["sistemaModalSubtitle"]) {
        case 1:
            echo '
            <h5 class="modal-title">
                <i class="bi bi-puzzle"></i> Ver Datos
            </h5>';
            break;
        case 2:
            echo '
            <h5 class="modal-title modal-subtitle">
                <div class="icon"><i class="bi bi-puzzle"></i></div>
                Ver Datos<br>
                <small>Permite visualizar los datos de un componente existente</small>
            </h5>';
            break;
    } ?>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" data-modal-close="PopupModalLarge"></button>
</div>
<div class="modal-body">

    <?php
    /************************************/
    // Ruta jerárquica (breadcrumb)
    echo '<nav aria-label="breadcrumb"><ol class="breadcrumb mb-2">';
    echo '<li class="breadcrumb-item"><i class="bi bi-cpu"></i> '.$data['rowData']['NombreMaquina'].'</li>';
    $nTotal = count($arrRuta);
    foreach ($arrRuta as $i => $nodo) {
        $Clase  = ($i === $nTotal - 1) ? ' active' : '';
        $Actual = ($i === $nTotal - 1) ? ' aria-current="page"' : '';
        echo '<li class="breadcrumb-item'.$Clase.'"'.$Actual.'>'.$nodo['Nombre'].'</li>';
    }
    echo '</ol></nav>';

    /************************************/
    // Badges de tipo y marca
    echo '<div class="mb-3">';
    echo '<span class="badge bg-primary">'.$Tipo.'</span>';
    if ((int)($data['rowData']['NoUtilizable'] ?? 0) === 1) {
        echo ' <span class="badge bg-danger">No utilizable</span>';
    }
    echo '</div>';

    /************************************/
    // Datos básicos
    $Theme_color = 'summary-theme-color-2';
    $arrData = [
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-cpu',       'Icon_color' => 10,'Title' => 'Máquina',           'Text' => $data['rowData']['NombreMaquina']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-card-text', 'Icon_color' => 10,'Title' => 'Nombre',            'Text' => ($data['rowData']['Nombre'] ?? '')],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-upc-scan',  'Icon_color' => 10,'Title' => 'Codigo',            'Text' => ($data['rowData']['Codigo'] ?? '')],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-bookmark',  'Icon_color' => 10,'Title' => 'Marca',             'Text' => ($data['rowData']['Marca'] ?? '')],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-tag',       'Icon_color' => 10,'Title' => 'Modelo',            'Text' => ($data['rowData']['Modelo'] ?? '')],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-upc-scan',  'Icon_color' => 10,'Title' => 'Codigo Saf',        'Text' => ($data['rowData']['CodigoSaf'] ?? '')],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-hash',      'Icon_color' => 10,'Title' => 'Numero',            'Text' => ($data['rowData']['Numero'] ?? '')],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-calendar3', 'Icon_color' => 10,'Title' => 'Año de Fabricación','Text' => ($Anio !== '' && $Anio !== null) ? (int)$Anio : '—'],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-hash',      'Icon_color' => 10,'Title' => 'Serie',             'Text' => ($data['rowData']['Serie'] ?? '')],
    ];
    echo $data['Fnc_WidgetsViews']->tittle_v2('Datos Básicos', 23);
    echo '<div class="row">';
        $OptionsTable['Cols'] = 2;
        echo $data['Fnc_WidgetsViews']->table_v6($arrData, $OptionsTable);
    echo '</div>';

    /************************************/
    // Tareas asignadas
    $arrTareasSel = is_array($data['arrTareasSel'] ?? null) ? $data['arrTareasSel'] : [];
    echo $data['Fnc_WidgetsViews']->tittle_v2('Tareas', 23);
    if (!empty($arrTareasSel)) {
        echo '<ul class="list-unstyled mb-0">';
        foreach ($arrTareasSel as $tarea) {
            echo '<li><i class="bi bi-check2-circle text-success"></i> '.($tarea['Nombre'] ?? '').'</li>';
        }
        echo '</ul>';
    } else {
        echo '<p class="text-muted mb-0">Sin tareas asignadas</p>';
    }

    /************************************/
    // Previsualización del adjunto (solo si existe archivo)
    if ($NombreArchivo !== '' && $NombreArchivo !== null) {
        echo '<h5 class="box-title text-color-red-dark mt-3">Previsualización</h5>';
        $data['Fnc_WidgetsPreview']->previewDocs($BASE, 'upload', $NombreArchivo);
    }
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
