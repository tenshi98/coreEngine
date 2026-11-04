<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

$idTipo = (int)($data['rowData']['idTipoAlarma'] ?? 0);
?>
<div class="modal-header">
    <?php
    switch ($data['UserData']["sistemaModalSubtitle"]) {
        case 1:
            echo '
            <h5 class="modal-title">
                <i class="bi bi-bell"></i> Ver Alarma
            </h5>';
            break;
        case 2:
            echo '
            <h5 class="modal-title modal-subtitle">
                <div class="icon"><i class="bi bi-bell"></i></div>
                Ver Alarma<br>
                <small>Permite visualizar los datos de una alarma personalizada</small>
            </h5>';
            break;
    } ?>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" data-modal-close="PopupModalLarge"></button>
</div>
<div class="modal-body">

    <?php
    $Theme_color = 'summary-theme-color-2';
    $arrData = [
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-card-text',  'Icon_color' => 10, 'Title' => 'Nombre',            'Text' => ($data['rowData']['Nombre'] ?? '')],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-bell',       'Icon_color' => 10, 'Title' => 'Tipo de Alarma',    'Text' => ($data['rowData']['TipoAlarma'] ?? '')],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-flag',       'Icon_color' => 10, 'Title' => 'Prioridad',         'Text' => ($data['rowData']['Prioridad'] ?? '')],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-activity',   'Icon_color' => 10, 'Title' => 'Estado',            'Text' => ($data['rowData']['Estado'] ?? '')],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-ruler',      'Icon_color' => 10, 'Title' => 'Unidad de Medida',  'Text' => ($data['rowData']['UniMed'] ?? '')],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-exclamation-triangle', 'Icon_color' => 10, 'Title' => 'N° Máximo Errores', 'Text' => ($data['rowData']['MaxErrores'] ?? '')],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-exclamation-circle',   'Icon_color' => 10, 'Title' => 'N° Actual Errores', 'Text' => ($data['rowData']['ActualErrores'] ?? '')],
    ];

    if($idTipo===4){
        $arrData[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-exclamation-octagon', 'Icon_color' => 10, 'Title' => 'Valor de Error', 'Text' => ($data['rowData']['ValorError'] ?? '')];
    }
    if($idTipo===5 || $idTipo===6){
        $arrData[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-arrow-down', 'Icon_color' => 10, 'Title' => 'Valor Mínimo', 'Text' => ($data['rowData']['ValorMinimo'] ?? '')];
        $arrData[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-arrow-up',   'Icon_color' => 10, 'Title' => 'Valor Máximo', 'Text' => ($data['rowData']['ValorMaximo'] ?? '')];
    }
    if($idTipo===7){
        $arrData[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-percent', 'Icon_color' => 10, 'Title' => 'Porcentaje de Diferencia', 'Text' => ($data['rowData']['PorcentajeDiferencia'] ?? '').'%'];
    }
    if($idTipo===8){
        $arrData[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-clock', 'Icon_color' => 10, 'Title' => 'Hora Inicio',  'Text' => ($data['rowData']['HoraInicio'] ?? '')];
        $arrData[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-clock-history', 'Icon_color' => 10, 'Title' => 'Hora Término', 'Text' => ($data['rowData']['HoraTermino'] ?? '')];
    }

    echo $data['Fnc_WidgetsViews']->tittle_v2('Datos de la Alarma', 23);
    echo '<div class="row">';
        $OptionsTable['Cols'] = 2;
        echo $data['Fnc_WidgetsViews']->table_v6($arrData, $OptionsTable);
    echo '</div>';

    echo $data['Fnc_WidgetsViews']->tittle_v2('Sensores de la Alarma', 23);
    ?>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
                    <th>Sensor</th>
                    <?php if($idTipo===1 || $idTipo===2){ ?>
                        <th>Rango Inicio</th>
                        <th>Rango Término</th>
                    <?php } ?>
                    <?php if($idTipo===3){ ?>
                        <th>Valor Específico</th>
                    <?php } ?>
                    <?php if($idTipo===8){ ?>
                        <th>Valor Encendido</th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $arrSensores = $data['rowData']['Sensores'] ?? [];
                if(is_array($arrSensores) && !empty($arrSensores)){
                    foreach($arrSensores as $sensor){ ?>
                        <tr>
                            <td><?php echo htmlspecialchars((string)($sensor['Sensor'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <?php if($idTipo===1 || $idTipo===2){ ?>
                                <td><?php echo htmlspecialchars((string)($sensor['RangoInicio'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string)($sensor['RangoTermino'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <?php } ?>
                            <?php if($idTipo===3){ ?>
                                <td><?php echo htmlspecialchars((string)($sensor['ValorEspecifico'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <?php } ?>
                            <?php if($idTipo===8){ ?>
                                <td><?php echo htmlspecialchars((string)($sensor['ValorEncendido'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <?php } ?>
                        </tr>
                    <?php }
                }else{ ?>
                    <tr><td colspan="4" class="text-muted">Sin sensores asociados.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</div>
<?php
if($data['UserData']["sistemaModalCloseBTN"] === 2){
    echo '
    <div class="modal-footer">
        <div class="d-grid gap-2 d-md-flex justify-content-md-end w-100">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bx bi-x-circle"></i> Cerrar</button>
        </div>
    </div>';
}else{
    echo '<style>.modal-body {max-height: 80vh;}</style>';
} ?>
