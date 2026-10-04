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
    <div class="col-xs-12 col-sm-12 col-md-7 col-lg-8 col-xl-9 col-xxl-10">
        <?php
        $arrData_1 = [
            ['Icon' => '','Titulo' => 'Nombre',           'Texto' => $data['rowData']['Nombre']],
            ['Icon' => '','Titulo' => 'Tipo de Vehículo', 'Texto' => $data['rowData']['Tipo']],
            ['Icon' => '','Titulo' => 'Marca',            'Texto' => $data['rowData']['Marca']],
            ['Icon' => '','Titulo' => 'Modelo',           'Texto' => $data['rowData']['Modelo']],
            ['Icon' => '','Titulo' => 'Patente',          'Texto' => $data['rowData']['Patente']],
            ['Icon' => '','Titulo' => 'Estado',           'Texto' => '<span class="badge-sp1 badge-sp1-'.$data['rowData']['EstadoColor'].'">'.$data['rowData']['Estado'].'</span>'],
        ];
        $arrData_2 = [
            ['Icon' => '','Titulo' => 'Año de Fabricación',  'Texto' => $data['rowData']['AnoFab']],
            ['Icon' => '','Titulo' => 'Número de serie',     'Texto' => $data['rowData']['Num_serie']],
            ['Icon' => '','Titulo' => 'Capacidad Pasajeros', 'Texto' => $data['rowData']['CapacidadPersonas']],
            ['Icon' => '','Titulo' => 'Capacidad (Kilos)',   'Texto' => $data['Fnc_DataNumbers']->cantidadesDecimalesJustos($data['rowData']['Capacidad'])],
            ['Icon' => '','Titulo' => 'Metros Cubicos (M3)', 'Texto' => $data['Fnc_DataNumbers']->cantidadesDecimalesJustos($data['rowData']['MCubicos'])],
            ['Icon' => '','Titulo' => 'Tipo de Carga',       'Texto' => $data['rowData']['TipoCarga']],
        ];

        echo '<h5 class="box-title text-color-red-dark">Datos Básicos</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData_1, 8);

        echo '<h5 class="box-title text-color-red-dark">Característicos</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData_2, 8);

        ?>
    </div>
</div>
