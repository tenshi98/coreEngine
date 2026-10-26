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

        <?php if(isset($data['rowData']['Latitud'], $data['rowData']['Longitud'])&&$data['rowData']['Latitud']!='0'&&$data['rowData']['Longitud']!='0'){
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
                        $xData  = '<b>Velocidad</b><br>'.$data['rowData']['Velocidad'];
                        $xData .= '<br><b>Velocidad</b><br>'.$data['Fnc_DataDate']->fechaEstandar($data['rowData']['LastUpdateFecha']);
                        $xData .= '<br><b>Velocidad</b><br>'.$data['rowData']['LastUpdateHora'];
                        // Variable para los marcadores
                        $arrMarkers = [
                            [
                                $data['rowData']['Latitud'],
                                $data['rowData']['Longitud'],
                                'A',
                                '#81a1c1',
                                "<i class='bi bi-cursor-fill text-primary'></i>",
                                '<b>Velocidad</b><br>'.$data['rowData']['Velocidad'].'<br><b>Velocidad</b><br>'.$data['rowData']['Velocidad']
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
        $Theme_color = 'summary-theme-color-2';
        $arrData1 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-card-text', 'Icon_color' => 10,'Title' => 'Nombre',           'Text' => $data['rowData']['Nombre']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-truck',     'Icon_color' => 10,'Title' => 'Tipo de Vehículo', 'Text' => $data['rowData']['Tipo']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-bookmark',  'Icon_color' => 10,'Title' => 'Marca',            'Text' => $data['rowData']['Marca']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-tag',       'Icon_color' => 10,'Title' => 'Modelo',           'Text' => $data['rowData']['Modelo']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-truck',     'Icon_color' => 10,'Title' => 'Patente',          'Text' => $data['rowData']['Patente']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-power',     'Icon_color' => 10,'Title' => 'Estado',           'Text' => '<span class="badge-sp1 badge-sp1-'.$data['rowData']['EstadoColor'].'">'.$data['rowData']['Estado'].'</span>'],
        ];
        $arrData2 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-calendar3',    'Icon_color' => 10,'Title' => 'Año de Fabricación',  'Text' => $data['rowData']['AnoFab']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-hash',         'Icon_color' => 10,'Title' => 'Número de serie',     'Text' => $data['rowData']['Num_serie']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-people',       'Icon_color' => 10,'Title' => 'Capacidad Pasajeros', 'Text' => $data['rowData']['CapacidadPersonas']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-speedometer2', 'Icon_color' => 10,'Title' => 'Capacidad (Kilos)',   'Text' => $data['Fnc_DataNumbers']->cantidadesDecimalesJustos($data['rowData']['Capacidad'])],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-box',          'Icon_color' => 10,'Title' => 'Metros Cubicos (M3)', 'Text' => $data['Fnc_DataNumbers']->cantidadesDecimalesJustos($data['rowData']['MCubicos'])],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-box-seam',     'Icon_color' => 10,'Title' => 'Tipo de Carga',       'Text' => $data['rowData']['TipoCarga']],
        ];

        echo $data['Fnc_WidgetsViews']->tittle_v2('Datos Básicos', 23);
        echo '<div class="row">';
            $OptionsTable['Cols'] = 2;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData1, $OptionsTable);
        echo '</div>';

        echo $data['Fnc_WidgetsViews']->tittle_v2('Característicos', 23);
        echo '<div class="row">';
            $OptionsTable['Cols'] = 2;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData2, $OptionsTable);
        echo '</div>';

        ?>
    </div>
</div>
