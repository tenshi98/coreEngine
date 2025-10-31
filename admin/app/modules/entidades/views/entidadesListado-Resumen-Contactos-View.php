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
        ['Icon' => '','Titulo' => 'Nombre',        'Texto' => $data['rowData']['ApellidoPat'].' '.$data['rowData']['ApellidoMat'].' '.$data['rowData']['Nombre']],
        ['Icon' => '','Titulo' => 'Email',         'Texto' => $data['rowData']['Email']],
        ['Icon' => '','Titulo' => 'Rut',           'Texto' => $data['rowData']['Rut']],
        ['Icon' => '','Titulo' => 'Celular',       'Texto' => $data['Fnc_DataNumbers']->formatPhone($data['rowData']['Fono1'])],
        ['Icon' => '','Titulo' => 'Teléfono',      'Texto' => $data['Fnc_DataNumbers']->formatPhone($data['rowData']['Fono2'])],
        ['Icon' => '','Titulo' => 'Ciudad',        'Texto' => $data['rowData']['Ciudad']],
        ['Icon' => '','Titulo' => 'Comuna',        'Texto' => $data['rowData']['Comuna']],
        ['Icon' => '','Titulo' => 'Direccion',     'Texto' => $data['rowData']['Direccion']],
        ['Icon' => '','Titulo' => 'Tipo Contacto', 'Texto' => $data['rowData']['TipoContacto']],
        ['Icon' => '','Titulo' => 'Cargo',         'Texto' => $data['rowData']['Cargo']],
        ['Icon' => '','Titulo' => 'Estado',        'Texto' => '<span class="badge '.$data['rowData']['EstadoColor'].'">'.$data['rowData']['Estado'].'</span>'],
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