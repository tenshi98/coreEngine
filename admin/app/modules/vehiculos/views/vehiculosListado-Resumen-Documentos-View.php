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
    $arrData = [
        ['Icon' => '','Titulo' => 'Tipo',              'Texto' => $data['rowData']['Tipo']],
        ['Icon' => '','Titulo' => 'Nombre',            'Texto' => $data['rowData']['Nombre']],
        ['Icon' => '','Titulo' => 'Fecha de Creacion', 'Texto' => $data['Fnc_DataDate']->fechaEstandar($data['rowData']['FechaCreacion'])],
        ['Icon' => '','Titulo' => 'Fecha Vencimiento', 'Texto' => $data['Fnc_DataDate']->fechaEstandar($data['rowData']['FechaVencimiento'])],
        ['Icon' => '','Titulo' => 'Observación',       'Texto' => $data['rowData']['Observacion']],
    ];
    echo '<h5 class="box-title text-color-red-dark">Datos Básicos</h5>';
    $data['Fnc_WidgetsCommon']->responsiveTable($arrData, 8);

    echo '<h5 class="box-title text-color-red-dark">Previsualización</h5>';
    $data['Fnc_WidgetsCommon']->previewDocs($BASE, 'upload', $data['rowData']['NombreArchivo']);

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
