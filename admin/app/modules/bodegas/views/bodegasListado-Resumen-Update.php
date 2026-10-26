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
        $Theme_color     = 'summary-theme-color-2';
        $Estado          = '<span class="badge-sp1 badge-sp1-'.$data['rowData']['EstadoColor'].'">'.$data['rowData']['Estado'].'</span>';
        $arrData_1 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-card-text', 'Icon_color' => 10, 'Title' => 'Nombre',    'Text' => $data['rowData']['Nombre'],],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-power',     'Icon_color' => 10, 'Title' => 'Estado',    'Text' => $Estado,],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-map',       'Icon_color' => 10, 'Title' => 'Ciudad',    'Text' => $data['rowData']['Ciudad'],],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-pin-map',   'Icon_color' => 10, 'Title' => 'Comuna',    'Text' => $data['rowData']['Comuna'],],
        ];
        $arrData_2 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-geo-fill',  'Icon_color' => 10, 'Title' => 'Dirección', 'Text' => $data['rowData']['Direccion'],],
        ];

        echo $data['Fnc_WidgetsViews']->tittle_v2('Datos Básicos', 23);
        echo '<div class="row">';
            $Options['Cols'] = 2;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData_1, $Options);
            $Options['Cols'] = 1;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData_2, $Options);
        echo '</div>';

        ?>
    </div>
</div>
