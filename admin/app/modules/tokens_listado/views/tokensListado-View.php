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
    <div class="row">


    <?php
    $Theme_color = 'summary-theme-color-2';
    /************************************/
    // Token en texto plano, solo lectura (no se edita)
    $arrData = [
        [
            'Theme_color'  => $Theme_color,
            'Icon'         => 'bi bi-bell',
            'Icon_color'   => 15,
            'Title'        => 'Token',
            'Title_color'  => 1,
            'Text'         => htmlspecialchars((string)($data['rowData']['Token'] ?? '')),
            'Text_color'   => '',
            'Text_copy'    => ($data['rowData']['Token'] ?? ''),
        ],
    ];
    $Options['Cols'] = 1;
    echo $data['Fnc_WidgetsViews']->table_v6($arrData, $Options);

    /************************************/
    // Token en texto plano, solo lectura (no se edita)
    $arrData = [
        [
            'Theme_color'  => $Theme_color,
            'Icon'         => 'bi bi-bell',
            'Icon_color'   => 15,
            'Title'        => 'Nombre',
            'Title_color'  => 1,
            'Text'         => htmlspecialchars((string)($data['rowData']['Nombre'] ?? '')),
            'Text_color'   => '',
        ],
        [
            'Theme_color'  => $Theme_color,
            'Icon'         => 'bi bi-bell',
            'Icon_color'   => 15,
            'Title'        => 'Fecha Creacion',
            'Title_color'  => 1,
            'Text'         => htmlspecialchars((string)($data['rowData']['FechaCreacion'] ?? '')),
            'Text_color'   => '',
        ],
        [
            'Theme_color'  => $Theme_color,
            'Icon'         => 'bi bi-bell',
            'Icon_color'   => 15,
            'Title'        => 'Usuario Creacion',
            'Title_color'  => 1,
            'Text'         => htmlspecialchars((string)($data['rowData']['Usuario'] ?? '')),
            'Text_color'   => '',
        ],
        [
            'Theme_color'  => $Theme_color,
            'Icon'         => 'bi bi-bell',
            'Icon_color'   => 15,
            'Title'        => 'Estado',
            'Title_color'  => 1,
            'Text'         => '<span class="badge-sp1 badge-sp1-'.htmlspecialchars((string)($data['rowData']['EstadoColor'] ?? '')).'">'.htmlspecialchars((string)($data['rowData']['Estado'] ?? '')).'</span>',
            'Text_color'   => '',
        ],
        [
            'Theme_color'  => $Theme_color,
            'Icon'         => 'bi bi-bell',
            'Icon_color'   => 15,
            'Title'        => 'Fecha Caducidad',
            'Title_color'  => 1,
            'Text'         => htmlspecialchars((string)($data['rowData']['FechaCaducidad'] ?? '')).(!empty($data['rowData']['Caducado']) ? ' <span class="badge bg-danger">Caducado</span>' : ''),
            'Text_color'   => '',
        ],
        [
            'Theme_color'  => $Theme_color,
            'Icon'         => 'bi bi-bell',
            'Icon_color'   => 15,
            'Title'        => 'Origen',
            'Title_color'  => 1,
            'Text'         => 'Manual',
            'Text_color'   => '',
        ],
    ];
    $Options['Cols'] = 2;
    echo $data['Fnc_WidgetsViews']->table_v6($arrData, $Options);


    ?>
    </div>
    <?php
    /************************************/
    // Modulos asignados al token (solo lectura)
    $arrModulos = (isset($data['arrModulos']) && is_array($data['arrModulos'])) ? $data['arrModulos'] : [];
    ?>
    <h5 class="title_h4 box-title text-color-red-dark mt-3">Módulos asignados</h5>
    <div class="clearfix"></div>
    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th scope="col">Módulo</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Verifico si hay datos
                if(!empty($arrModulos)){
                    // Recorro los modulos
                    foreach ($arrModulos as $mod){ ?>
                        <tr>
                            <td><?php echo htmlspecialchars((string)($mod['nombre'] ?? $mod['Modulo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php }
                }else{ ?>
                    <tr><td class="text-center text-muted">Sin módulos asignados</td></tr>
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
