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
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-card-text',    'Icon_color' => 10,'Title' => 'Nombre',        'Text' => $data['rowData']['ApellidoPat'].' '.$data['rowData']['ApellidoMat'].' '.$data['rowData']['Nombre']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-envelope',     'Icon_color' => 10,'Title' => 'Email',         'Text' => $data['rowData']['Email'], 'Text_color' => 26, 'Text_copy' => $data['rowData']['Email']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-person-vcard', 'Icon_color' => 10,'Title' => 'Rut',           'Text' => $data['rowData']['Rut']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-phone',        'Icon_color' => 10,'Title' => 'Celular',       'Text' => $data['Fnc_DataNumbers']->formatPhone($data['rowData']['Fono1']), 'Text_color' => 26, 'Text_copy' => $data['rowData']['Fono1']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-telephone',    'Icon_color' => 10,'Title' => 'Teléfono',      'Text' => $data['Fnc_DataNumbers']->formatPhone($data['rowData']['Fono2']), 'Text_color' => 26, 'Text_copy' => $data['rowData']['Fono2']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-power',        'Icon_color' => 10,'Title' => 'Estado',        'Text' => '<span class="badge-sp1 badge-sp1-'.$data['rowData']['EstadoColor'].'">'.$data['rowData']['Estado'].'</span>'],
    ];
    $arrData2 = [
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-person-lines-fill', 'Icon_color' => 10,'Title' => 'Tipo Contacto', 'Text' => $data['rowData']['TipoContacto']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-briefcase',         'Icon_color' => 10,'Title' => 'Cargo',         'Text' => $data['rowData']['Cargo']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-geo-alt',           'Icon_color' => 10,'Title' => 'Ciudad',        'Text' => $data['rowData']['Ciudad']],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-geo-alt',           'Icon_color' => 10,'Title' => 'Comuna',        'Text' => $data['rowData']['Comuna']],
    ];
    $arrData3 = [
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-geo-fill',          'Icon_color' => 10,'Title' => 'Direccion',     'Text' => $data['rowData']['Direccion']],
    ];
    // Imprimo resultados
    echo $data['Fnc_WidgetsViews']->tittle_v2('Datos Básicos', 23);
    echo '<div class="row">';
        $OptionsTable['Cols'] = 2;
        echo $data['Fnc_WidgetsViews']->table_v6($arrData1, $OptionsTable);
    echo '</div>';

    echo $data['Fnc_WidgetsViews']->tittle_v2('Otros Datos', 23);
    echo '<div class="row">';
        $OptionsTable['Cols'] = 2;
        echo $data['Fnc_WidgetsViews']->table_v6($arrData2, $OptionsTable);
        $OptionsTable['Cols'] = 1;
        echo $data['Fnc_WidgetsViews']->table_v6($arrData3, $OptionsTable);
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
