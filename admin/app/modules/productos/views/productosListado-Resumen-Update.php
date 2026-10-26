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
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-tag',       'Icon_color' => 10,'Title' => 'Tipo',           'Text' => $data['rowData']['Tipo']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-tag',       'Icon_color' => 10,'Title' => 'Categoria',      'Text' => $data['rowData']['Categoria']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-card-text', 'Icon_color' => 10,'Title' => 'Nombre',         'Text' => $data['rowData']['Nombre']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-bookmark',  'Icon_color' => 10,'Title' => 'Marca',          'Text' => $data['rowData']['Marca']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-box',       'Icon_color' => 10,'Title' => 'Stock Limite',   'Text' => $data['rowData']['StockLimite'].' '.$data['rowData']['UniMed']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-cash-coin', 'Icon_color' => 10,'Title' => 'Valor Ingreso',  'Text' => $data['rowData']['ValorIngreso']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-cash-coin', 'Icon_color' => 10,'Title' => 'Valor Egreso',   'Text' => $data['rowData']['ValorEgreso']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-upc-scan',  'Icon_color' => 10,'Title' => 'Codigo',         'Text' => $data['rowData']['Codigo']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-power',     'Icon_color' => 10,'Title' => 'Estado',         'Text' => '<span class="badge-sp1 badge-sp1-'.$data['rowData']['EstadoColor'].'">'.$data['rowData']['Estado'].'</span>'],
        ];
        $arrData2 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-card-text', 'Icon_color' => 10,'Title' => 'Descripcion',    'Text' => $data['rowData']['Descripcion']],
        ];

        echo '<div class="row">';
            $OptionsTable['Cols'] = 2;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData1, $OptionsTable);
            $OptionsTable['Cols'] = 1;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData2, $OptionsTable);
        echo '</div>';

        ?>
    </div>
</div>
