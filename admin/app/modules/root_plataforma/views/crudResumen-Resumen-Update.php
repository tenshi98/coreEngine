<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4 col-xl-3 col-xxl-2">
        <?php
        $UserIMG  = !empty($data['rowData']['Direccion_img'])
                    ? $data['UserData']['MainPathUrl'].$data['rowData']['Direccion_img']
                    : $BASE.'/img/picture-img.jpg';
        ?>
        <img src="<?php echo $UserIMG; ?>" alt="Profile" class="square-rounded-2 square-border-3 w-100">
    </div>
    <div class="col-xs-12 col-sm-12 col-md-7 col-lg-8 col-xl-9 col-xxl-10 record-attachment">
        <?php
        $Theme_color = 'summary-theme-color-2';
        $arrData = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-person-badge',     'Icon_color' => 10,'Title' => 'idUsuario',  'Text' => $data['rowData']['idUsuario']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-envelope',         'Icon_color' => 10,'Title' => 'Email',      'Text' => $data['rowData']['Email'], 'Text_color' => 26, 'Text_copy' => $data['rowData']['Email']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-hash',             'Icon_color' => 10,'Title' => 'Numero',     'Text' => $data['Fnc_DataNumbers']->Valores($data['rowData']['Numero'], 2)],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-person-vcard',     'Icon_color' => 10,'Title' => 'Rut',        'Text' => $data['rowData']['Rut']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-truck',            'Icon_color' => 10,'Title' => 'Patente',    'Text' => $data['rowData']['Patente']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-calendar-event',   'Icon_color' => 10,'Title' => 'Fecha',      'Text' => $data['Fnc_DataDate']->fechaEstandar($data['rowData']['Fecha'])],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-clock',            'Icon_color' => 10,'Title' => 'Hora',       'Text' => $data['rowData']['Hora']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-chat-square-text', 'Icon_color' => 10,'Title' => 'Palabra',    'Text' => $data['rowData']['Palabra']],
        ];

        echo $data['Fnc_WidgetsViews']->tittle_v2('Datos del Perfil', 23);
        echo '<div class="row">';
            $OptionsTable['Cols'] = 2;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData, $OptionsTable);
        echo '</div>';
        ?>
    </div>
</div>
