<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<div class="row">
    <?php
    $Theme_color = 'summary-theme-color-2';
    $Estado      = '<span class="badge-sp1 badge-sp1-'.htmlspecialchars((string)($data['rowData']['EstadoColor'] ?? ''), ENT_QUOTES, 'UTF-8').'">'.htmlspecialchars((string)($data['rowData']['Estado'] ?? ''), ENT_QUOTES, 'UTF-8').'</span>';
    $Caducidad   = htmlspecialchars((string)($data['rowData']['FechaCaducidad'] ?? ''), ENT_QUOTES, 'UTF-8').(!empty($data['rowData']['Caducado']) ? ' <span class="badge bg-danger">Caducado</span>' : '');

    /************************************/
    // Token en texto plano, solo lectura
    $arrData_1 = [
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-key', 'Icon_color' => 10, 'Title' => 'Token', 'Text' => htmlspecialchars((string)($data['rowData']['Token'] ?? ''), ENT_QUOTES, 'UTF-8'), 'Text_copy' => ($data['rowData']['Token'] ?? '')],
    ];
    /************************************/
    // Datos generales
    $arrData_2 = [
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-card-text',     'Icon_color' => 10, 'Title' => 'Nombre',          'Text' => htmlspecialchars((string)($data['rowData']['Nombre'] ?? ''), ENT_QUOTES, 'UTF-8')],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-power',         'Icon_color' => 10, 'Title' => 'Estado',          'Text' => $Estado],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-calendar-x',    'Icon_color' => 10, 'Title' => 'Fecha Caducidad', 'Text' => $Caducidad],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-hand-index',    'Icon_color' => 10, 'Title' => 'Origen',          'Text' => 'Manual'],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-calendar-plus', 'Icon_color' => 10, 'Title' => 'Fecha Creacion',  'Text' => htmlspecialchars((string)($data['rowData']['FechaCreacion'] ?? ''), ENT_QUOTES, 'UTF-8')],
    ];
    /************************************/
    // Conteos
    $arrData_3 = [
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-shield-check', 'Icon_color' => 10, 'Title' => 'Módulos',           'Text' => (string)(int)($data['rowData']['NModulos'] ?? 0)],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-hdd-network',  'Icon_color' => 10, 'Title' => 'Clientes OAuth',    'Text' => (string)(int)($data['rowData']['NClientes'] ?? 0)],
        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-unlock',       'Icon_color' => 10, 'Title' => 'Accesos vigentes',  'Text' => (string)(int)($data['rowData']['NAccesos'] ?? 0)],
    ];

    echo $data['Fnc_WidgetsViews']->tittle_v2('Datos del Token', 23);
    echo '<div class="row">';
        $Options['Cols'] = 1;
        echo $data['Fnc_WidgetsViews']->table_v6($arrData_1, $Options);
        $Options['Cols'] = 2;
        echo $data['Fnc_WidgetsViews']->table_v6($arrData_2, $Options);
        $Options['Cols'] = 3;
        echo $data['Fnc_WidgetsViews']->table_v6($arrData_3, $Options);
    echo '</div>';
    ?>
</div>
