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
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-envelope',     'Icon_color' => 10,'Title' => 'Email',           'Text' => $data['rowData']['email'], 'Text_color' => 26, 'Text_copy' => $data['rowData']['email']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-person-badge', 'Icon_color' => 10,'Title' => 'Tipo de usuario', 'Text' => $data['rowData']['TipoUsuario']],
        ];
        $arrData2 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-card-text',      'Icon_color' => 10,'Title' => 'Nombre',               'Text' => $data['rowData']['Nombre']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-telephone',      'Icon_color' => 10,'Title' => 'Fono',                 'Text' => $data['Fnc_DataNumbers']->formatPhone($data['rowData']['Fono']), 'Text_color' => 26, 'Text_copy' => $data['rowData']['Fono']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-person-vcard',   'Icon_color' => 10,'Title' => 'Rut',                  'Text' => $data['rowData']['Rut']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-calendar-heart', 'Icon_color' => 10,'Title' => 'Fecha de Nacimiento',  'Text' => $data['Fnc_DataDate']->fechaCompleta($data['rowData']['fNacimiento'])],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-geo-alt',        'Icon_color' => 10,'Title' => 'Ciudad',               'Text' => $data['rowData']['Ciudad']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-geo-alt',        'Icon_color' => 10,'Title' => 'Comuna',               'Text' => $data['rowData']['Comuna']],
        ];
        $arrData3 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-geo-fill',       'Icon_color' => 10,'Title' => 'Dirección',            'Text' => $data['rowData']['Direccion']],
        ];
        $arrData4 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-twitter-x', 'Icon_color' => 10,'Title' => 'X(Twitter)',  'Text' => $data['rowData']['Social_X'],         'Text_color' => 26, 'Text_copy' => $data['rowData']['Social_X']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-facebook',  'Icon_color' => 10,'Title' => 'Facebook',    'Text' => $data['rowData']['Social_Facebook'],  'Text_color' => 26, 'Text_copy' => $data['rowData']['Social_Facebook']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-instagram', 'Icon_color' => 10,'Title' => 'Instagram',   'Text' => $data['rowData']['Social_Instagram'], 'Text_color' => 26, 'Text_copy' => $data['rowData']['Social_Instagram']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-linkedin',  'Icon_color' => 10,'Title' => 'Linkedin',    'Text' => $data['rowData']['Social_Linkedin'],  'Text_color' => 26, 'Text_copy' => $data['rowData']['Social_Linkedin']],
        ];
        $arrData5 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-power', 'Icon_color' => 10,'Title' => 'Estado',         'Text' => '<span class="badge-sp1 badge-sp1-'.$data['rowData']['EstadoColor'].'">'.$data['rowData']['Estado'].'</span>'],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-list',  'Icon_color' => 10,'Title' => 'Posición Menu',  'Text' => $data['rowData']['MenuPosicion']],
        ];

        /*******************************************/
        echo $data['Fnc_WidgetsViews']->tittle_v2('Datos del Perfil', 23);
        echo '<div class="row">';
            $OptionsTable['Cols'] = 2;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData1, $OptionsTable);
        echo '</div>';

        echo $data['Fnc_WidgetsViews']->tittle_v2('Datos Personales', 23);
        echo '<div class="row">';
            $OptionsTable['Cols'] = 2;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData2, $OptionsTable);
            $OptionsTable['Cols'] = 1;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData3, $OptionsTable);
        echo '</div>';

        echo $data['Fnc_WidgetsViews']->tittle_v2('Social', 23);
        echo '<div class="row">';
            $OptionsTable['Cols'] = 1;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData4, $OptionsTable);
        echo '</div>';

        echo $data['Fnc_WidgetsViews']->tittle_v2('Opciones', 23);
        echo '<div class="row">';
            $OptionsTable['Cols'] = 2;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData5, $OptionsTable);
        echo '</div>';

        ?>
    </div>
</div>
