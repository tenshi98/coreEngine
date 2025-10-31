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
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <?php
    $arrData = [
        ['Icon' => '','Titulo' => 'idCrud',     'Texto' => $data['rowData']['idCrud']],
        ['Icon' => '','Titulo' => 'idUsuario',  'Texto' => $data['rowData']['idUsuario']],
        ['Icon' => '','Titulo' => 'Email',      'Texto' => $data['rowData']['Email']],
        ['Icon' => '','Titulo' => 'Numero',     'Texto' => $data['Fnc_DataNumbers']->Valores($data['rowData']['Numero'], 2)],
        ['Icon' => '','Titulo' => 'Rut',        'Texto' => $data['rowData']['Rut']],
        ['Icon' => '','Titulo' => 'Patente',    'Texto' => $data['rowData']['Patente']],
        ['Icon' => '','Titulo' => 'Fecha',      'Texto' => $data['Fnc_DataDate']->fechaEstandar($data['rowData']['Fecha'])],
        ['Icon' => '','Titulo' => 'Hora',       'Texto' => $data['rowData']['Hora']],
        ['Icon' => '','Titulo' => 'Palabra',    'Texto' => $data['rowData']['Palabra']],
    ];
    $data['Fnc_WidgetsCommon']->responsiveTable($arrData, 8);
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