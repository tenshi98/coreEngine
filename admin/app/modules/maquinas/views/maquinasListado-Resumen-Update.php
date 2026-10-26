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
        $arrData1 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-card-text', 'Icon_color' => 10,'Title' => 'Nombre',         'Text' => $data['rowData']['Nombre']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-hash',      'Icon_color' => 10,'Title' => 'Identificador',  'Text' => $data['rowData']['CodIdentificador']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-power',     'Icon_color' => 10,'Title' => 'Estado',         'Text' => '<span class="badge-sp1 badge-sp1-'.$data['rowData']['EstadoColor'].'">'.$data['rowData']['Estado'].'</span>'],
        ];
        $arrData2 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-card-text', 'Icon_color' => 10,'Title' => 'Descripcion',    'Text' => $data['rowData']['Descripcion']],
        ];
        echo $data['Fnc_WidgetsViews']->tittle_v2('Datos Básicos', 23);
        echo '<div class="row">';
            $OptionsTable['Cols'] = 3;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData1, $OptionsTable);
            $OptionsTable['Cols'] = 1;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData2, $OptionsTable);
        echo '</div>';

        if($data['UserData']["maquinasListadoTelemetria"]==2){
            $arrData_2 = [
                ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-phone',         'Icon_color' => 10,'Title' => 'SIM - Numero Telefónico',    'Text' => $data['rowData']['Sim_Num_Tel'], 'Text_color' => 26, 'Text_copy' => $data['rowData']['Sim_Num_Tel']],
                ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-sim',           'Icon_color' => 10,'Title' => 'SIM - Compañia',             'Text' => $data['rowData']['Sim_Compania']],
                ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-clock-history', 'Icon_color' => 10,'Title' => 'Tiempo Fuera Linea Máximo',  'Text' => $data['rowData']['TiempoFueraLinea']],
                ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-folder',        'Icon_color' => 10,'Title' => 'Tab',                        'Text' => $data['rowData']['Tabs']],
                ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-geo-alt',       'Icon_color' => 10,'Title' => 'Uso Geolocalización',        'Text' => $data['rowData']['UsoGeo']],
                ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-broadcast',     'Icon_color' => 10,'Title' => 'Uso Sensores',               'Text' => $data['rowData']['UsoSensores']],
                ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-database',      'Icon_color' => 10,'Title' => 'Backup Tabla relacionada',   'Text' => $data['rowData']['UsoBackup'].' ('.$data['rowData']['NregBackup'].' registros)'],
                ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-bell',          'Icon_color' => 10,'Title' => 'Alerta Temprana',            'Text' => $data['rowData']['UsoAlertaTemprana']],
                ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-clock-history', 'Icon_color' => 10,'Title' => 'Tiempo Alertas Criticas',    'Text' => $data['rowData']['AlertaTemprCritica']],
                ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-clock-history', 'Icon_color' => 10,'Title' => 'Tiempo Alertas Normales',    'Text' => $data['rowData']['AlertaTemprNormal']],
            ];
            echo $data['Fnc_WidgetsViews']->tittle_v2('Configuración', 23);
            echo '<div class="row">';
                $OptionsTable['Cols'] = 3;
                echo $data['Fnc_WidgetsViews']->table_v6($arrData_2, $OptionsTable);
            echo '</div>';
        }

        ?>
    </div>
</div>
