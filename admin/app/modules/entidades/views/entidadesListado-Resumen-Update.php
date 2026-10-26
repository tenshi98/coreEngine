<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4 col-xl-3 col-xxl-2">
        <?php
        //Imagen
        $UserIMG  = !empty($data['rowData']['Direccion_img'])
                    ? $data['UserData']['MainPathUrl'].$data['rowData']['Direccion_img']
                    : $BASE.'/img/picture-img.jpg';
        ?>
        <img src="<?php echo $UserIMG; ?>" alt="Profile" class="square-rounded-2 square-border-3 w-100 mb-2">

        <?php if(isset($data['rowData']['Latitud'], $data['rowData']['Longitud'], $data['rowData']['Direccion'])&&$data['rowData']['Latitud']!='0'&&$data['rowData']['Longitud']!='0'&&$data['rowData']['Direccion']!=''){
            echo '<div class="square-rounded-2 square-border-3 w-100">';
                // Valido segun mapa
                switch ($data['UserData']['Config_motorMap']) {
                    /********************************/
                    // Google maps
                    case 1:
                        # code...
                        break;
                    /********************************/
                    // leaFlet maps
                    case 2:
                        // Variable para los marcadores
                        $arrMarkers = [
                            [
                                $data['rowData']['Latitud'],
                                $data['rowData']['Longitud'],
                                'A',
                                '#81a1c1',
                                "<i class='bi bi-cursor-fill text-primary'></i>",
                                '<b>Direccion</b><br>'.$data['rowData']['Direccion']
                            ],
                        ];
                        //se imprime input
                        $Options = [
                            'Latitud'      => $data['rowData']['Latitud'],   //Latitud de la ubicacion
                            'Longitud'     => $data['rowData']['Longitud'],  //Longitud de la ubicacion
                            'ID_Map'       => 'map_1',                       //ID del div donde se dibuja el html
                            'Zoom'         => 14,                            //Zoom del mapa
                            'attribution'  => '&copy; Ubicacion',            //Pie de pagina del mapa
                            'arrMarkers'   => $arrMarkers,                   //array con los marcadores
                            'defaultLayer' => 'Esri_WorldTopoMap',           //Layer a mostrar en la carga
                            'ConfMode'     => 3,                             //Modo del mapa
                        ];
                        echo $data['Fnc_WidgetsMaps']->leaFletMap_from_gps($Options);
                        break;
                }
            echo '</div>';
        } ?>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-7 col-lg-8 col-xl-9 col-xxl-10 record-attachment">
        <?php
        // Color
        $Theme_color = 'summary-theme-color-2';
        //selecciona
        switch ($data['rowData']['idTipoEntidad']) {
            //Persona Natural
            case 1:
                $arrData_1 = [
                    ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-tag',              'Icon_color' => 10,'Title' => 'Tipo',             'Text' => $data['rowData']['Tipo']],
                    ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-building',         'Icon_color' => 10,'Title' => 'Tipo Entidad',     'Text' => $data['rowData']['TipoEntidad']],
                    ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-card-text',        'Icon_color' => 10,'Title' => 'Nombre',           'Text' => $data['rowData']['ApellidoPat'].' '.$data['rowData']['ApellidoMat'].', '.$data['rowData']['Nombre']],
                    ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-at',               'Icon_color' => 10,'Title' => 'Nick',             'Text' => $data['rowData']['Nick']],
                    ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-calendar-heart',   'Icon_color' => 10,'Title' => 'Fecha Nacimiento', 'Text' => $data['Fnc_DataDate']->fechaEstandar($data['rowData']['FNacimiento'])],
                    ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-gender-ambiguous', 'Icon_color' => 10,'Title' => 'Sexo',             'Text' => $data['rowData']['Sexo']],
                ];
                break;
            //Empresas
            case 2:
                $arrData_1 = [
                    ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-tag',       'Icon_color' => 10,'Title' => 'Tipo',         'Text' => $data['rowData']['Tipo']],
                    ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-building',  'Icon_color' => 10,'Title' => 'Tipo Entidad', 'Text' => $data['rowData']['TipoEntidad']],
                    ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-card-text', 'Icon_color' => 10,'Title' => 'Nombre',       'Text' => $data['rowData']['RazonSocial']],
                    ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-at',        'Icon_color' => 10,'Title' => 'Nick',         'Text' => $data['rowData']['Nick']],
                    ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-globe',     'Icon_color' => 10,'Title' => 'Web',          'Text' => $data['rowData']['Web']],
                    ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-briefcase', 'Icon_color' => 10,'Title' => 'Giro',         'Text' => $data['rowData']['Giro']],
                ];
                break;
        }
        $arrData_2 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-person-vcard', 'Icon_color' => 10,'Title' => 'Rut',        'Text' => $data['rowData']['Rut']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-phone',        'Icon_color' => 10,'Title' => 'Celular',    'Text' => $data['Fnc_DataNumbers']->formatPhone($data['rowData']['Fono1']), 'Text_color' => 26, 'Text_copy' => $data['rowData']['Fono1']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-telephone',    'Icon_color' => 10,'Title' => 'Teléfono',   'Text' => $data['Fnc_DataNumbers']->formatPhone($data['rowData']['Fono2']), 'Text_color' => 26, 'Text_copy' => $data['rowData']['Fono2']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-power',        'Icon_color' => 10,'Title' => 'Estado',     'Text' => '<span class="badge-sp1 badge-sp1-'.$data['rowData']['EstadoColor'].'">'.$data['rowData']['Estado'].'</span>'],
        ];
        $arrData_3 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-envelope',     'Icon_color' => 10,'Title' => 'Email',      'Text' => $data['rowData']['Email'], 'Text_color' => 26, 'Text_copy' => $data['rowData']['Email']],
        ];
        $arrData_4 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-geo-alt',  'Icon_color' => 10,'Title' => 'Sector',      'Text' => $data['rowData']['Sector']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-geo-alt',  'Icon_color' => 10,'Title' => 'Ciudad',      'Text' => $data['rowData']['Ciudad']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-geo-alt',  'Icon_color' => 10,'Title' => 'Comuna',      'Text' => $data['rowData']['Comuna']],
        ];
        $arrData_5 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-geo-fill', 'Icon_color' => 10,'Title' => 'Dirección',   'Text' => $data['rowData']['Direccion']],
        ];
        $arrData_6 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-card-text',    'Icon_color' => 10,'Title' => 'Nombre',    'Text' => $data['rowData']['RepLegalNombre']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-envelope',     'Icon_color' => 10,'Title' => 'Email',     'Text' => $data['rowData']['RepLegalEmail'], 'Text_color' => 26, 'Text_copy' => $data['rowData']['RepLegalEmail']],
        ];
        $arrData_7 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-person-vcard', 'Icon_color' => 10,'Title' => 'Rut',       'Text' => $data['rowData']['RepLegalRut']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-phone',        'Icon_color' => 10,'Title' => 'Celular',   'Text' => $data['Fnc_DataNumbers']->formatPhone($data['rowData']['RepLegalFono1']), 'Text_color' => 26, 'Text_copy' => $data['rowData']['RepLegalFono1']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-telephone',    'Icon_color' => 10,'Title' => 'Teléfono',  'Text' => $data['Fnc_DataNumbers']->formatPhone($data['rowData']['RepLegalFono2']), 'Text_color' => 26, 'Text_copy' => $data['rowData']['RepLegalFono2']],
        ];
        $arrData_8 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-twitter-x', 'Icon_color' => 10,'Title' => 'X',          'Text' => $data['rowData']['Social_X'],         'Text_color' => 26, 'Text_copy' => $data['rowData']['Social_X']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-facebook',  'Icon_color' => 10,'Title' => 'Facebook',   'Text' => $data['rowData']['Social_Facebook'],  'Text_color' => 26, 'Text_copy' => $data['rowData']['Social_Facebook']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-instagram', 'Icon_color' => 10,'Title' => 'Instagram',  'Text' => $data['rowData']['Social_Instagram'], 'Text_color' => 26, 'Text_copy' => $data['rowData']['Social_Instagram']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-linkedin',  'Icon_color' => 10,'Title' => 'Linkedin',   'Text' => $data['rowData']['Social_Linkedin'],  'Text_color' => 26, 'Text_copy' => $data['rowData']['Social_Linkedin']],
        ];

        echo $data['Fnc_WidgetsViews']->tittle_v2('Datos Básicos', 23);
        echo '<div class="row">';
            $OptionsTable['Cols'] = 2;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData_1, $OptionsTable);
            $OptionsTable['Cols'] = 2;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData_2, $OptionsTable);
            $OptionsTable['Cols'] = 1;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData_3, $OptionsTable);
            $OptionsTable['Cols'] = 3;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData_4, $OptionsTable);
            $OptionsTable['Cols'] = 1;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData_5, $OptionsTable);
        echo '</div>';

        echo $data['Fnc_WidgetsViews']->tittle_v2('Representante Legal', 23);
        echo '<div class="row">';
            $OptionsTable['Cols'] = 2;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData_6, $OptionsTable);
            $OptionsTable['Cols'] = 3;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData_7, $OptionsTable);
        echo '</div>';

        echo $data['Fnc_WidgetsViews']->tittle_v2('Social', 23);
        echo '<div class="row">';
            $OptionsTable['Cols'] = 1;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData_8, $OptionsTable);
        echo '</div>';

        ?>
    </div>
</div>
