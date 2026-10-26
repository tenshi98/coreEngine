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
        // Color
        $Theme_color = 'summary-theme-color-2';

        //selecciono
        switch ($data['rowData']['idTipoEntidad']) {
            //Persona Natural
            case 1:
                $arrData_1 = [
                    ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-building',         'Icon_color' => 10,'Title' => 'Tipo Entidad',     'Text' => $data['rowData']['TipoEntidad']],
                    ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-card-text',        'Icon_color' => 10,'Title' => 'Nombre',           'Text' => $data['rowData']['ApellidoPat'].' '.$data['rowData']['ApellidoMat'].', '.$data['rowData']['Nombre']],
                    ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-at',               'Icon_color' => 10,'Title' => 'Nick',             'Text' => $data['rowData']['Nick']],
                    ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-gender-ambiguous', 'Icon_color' => 10,'Title' => 'Sexo',             'Text' => $data['rowData']['Sexo']],
                ];
                break;
            //Empresas
            case 2:
                $arrData_1 = [
                    ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-building',  'Icon_color' => 10,'Title' => 'Tipo Entidad', 'Text' => $data['rowData']['TipoEntidad']],
                    ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-card-text', 'Icon_color' => 10,'Title' => 'Nombre',       'Text' => $data['rowData']['RazonSocial']],
                    ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-at',        'Icon_color' => 10,'Title' => 'Nick',         'Text' => $data['rowData']['Nick']],
                ];
                break;
        }

        echo $data['Fnc_WidgetsViews']->tittle_v2('Datos Básicos', 23);
        echo '<div class="row">';
            $OptionsTable['Cols'] = 2;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData_1, $OptionsTable);
        echo '</div>';

        ?>
    </div>
</div>
