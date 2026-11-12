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
    // Escape de salida por contexto HTML; todo lo declarado por el cliente se muestra solo como texto
    $e = static function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
    // Valor de metadato a texto (los arrays se unen con coma)
    $txt = static function ($v) use ($e) {
        if (is_array($v)) { $v = implode(', ', array_map(static function ($x) { return is_scalar($x) ? (string)$x : json_encode($x); }, $v)); }
        return $e(is_scalar($v) || $v === null ? $v : json_encode($v));
    };
    $row  = (isset($data['rowData']) && is_array($data['rowData'])) ? $data['rowData'] : [];
    $meta = (isset($data['arrMetadatos']) && is_array($data['arrMetadatos'])) ? $data['arrMetadatos'] : [];
    $uris = (isset($data['arrRedirectUris']) && is_array($data['arrRedirectUris'])) ? $data['arrRedirectUris'] : [];
    $Theme_color = 'summary-theme-color-2';
    $arrData = [
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-card-text', 'Icon_color' => 10, 'Title' => 'Nombre (declarado, no verificado)', 'Text' => $e($row['Nombre'] ?? '')],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-key',       'Icon_color' => 10, 'Title' => 'Client ID',                         'Text' => $e($row['ClientId'] ?? '')],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-shield',    'Icon_color' => 10, 'Title' => 'Tipo',                              'Text' => ((($row['AuthMethod'] ?? '') === 'none') ? 'Público' : 'Confidencial').' ('.$e($row['AuthMethod'] ?? '').')'],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-toggle-on', 'Icon_color' => 10, 'Title' => 'Estado',                            'Text' => '<span class="badge-sp1 badge-sp1-'.$e($row['EstadoColor'] ?? '').'">'.$e($row['Estado'] ?? '').'</span>'],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-calendar',  'Icon_color' => 10, 'Title' => 'Registro',                          'Text' => $e($row['FechaRegistro'] ?? '')],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-hdd-network','Icon_color' => 10, 'Title' => 'IP de registro',                   'Text' => $e($row['IPRegistro'] ?? '')],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-clock',     'Icon_color' => 10, 'Title' => 'Último uso',                        'Text' => !empty($row['FechaUltimoUso']) ? $e($row['FechaUltimoUso']) : 'Sin uso'],
    ];
    echo '<div class="row">';
        $OptionsTable['Cols'] = 2;
        echo $data['Fnc_WidgetsViews']->table_v6($arrData, $OptionsTable);
    echo '</div>';
    ?>
    <h5 class="title_h4 box-title text-color-red-dark mt-3">URI de redirección registradas</h5>
    <div class="clearfix"></div>
    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <tbody>
                <?php if(!empty($uris)){ foreach($uris as $uri){ ?>
                    <tr><td><?php echo $txt($uri); ?></td></tr>
                <?php } }else{ ?>
                    <tr><td class="text-center text-muted">Sin URI registradas</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <h5 class="title_h4 box-title text-color-red-dark mt-3">Metadatos declarados (texto, no verificados)</h5>
    <div class="clearfix"></div>
    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <tbody>
                <?php if(!empty($meta)){ foreach($meta as $clave => $valor){ ?>
                    <tr>
                        <th scope="row" style="width: 200px;"><?php echo $e($clave); ?></th>
                        <td><?php echo $txt($valor); ?></td>
                    </tr>
                <?php } }else{ ?>
                    <tr><td class="text-center text-muted">Sin metadatos</td></tr>
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
