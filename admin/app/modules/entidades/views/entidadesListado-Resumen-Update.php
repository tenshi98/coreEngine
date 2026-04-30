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

        <?php if(isset($data['rowData']['Latitud'], $data['rowData']['Longitud'])&&$data['rowData']['Latitud']!='0'&&$data['rowData']['Longitud']!='0'){ ?>
            <div class="square-rounded-2 square-border-3 w-100">
                <?php
                //variable para los marcadores
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
                ?>
            </div>
        <?php } ?>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-7 col-lg-8 col-xl-9 col-xxl-10">
        <?php
        //Se obtiene el nombre o la razón social
        $Entidad = '';
        $Entidad .= !empty($data['rowData']['Nombre'])
                    ? $data['rowData']['ApellidoPat'].' '.$data['rowData']['ApellidoMat'].' '.$data['rowData']['Nombre']
                    : $data['rowData']['RazonSocial'];
        //selecciona
        switch ($data['rowData']['idTipoEntidad']) {
            //Persona Natural
            case 1:
                $arrData_1 = [
                    ['Icon' => '','Titulo' => 'Tipo',             'Texto' => $data['rowData']['Tipo']],
                    ['Icon' => '','Titulo' => 'Tipo Entidad',     'Texto' => $data['rowData']['TipoEntidad']],
                    ['Icon' => '','Titulo' => 'Nombre',           'Texto' => $Entidad],
                    ['Icon' => '','Titulo' => 'Nick',             'Texto' => $data['rowData']['Nick']],
                    ['Icon' => '','Titulo' => 'Fecha Nacimiento', 'Texto' => $data['Fnc_DataDate']->fechaEstandar($data['rowData']['FNacimiento'])],
                    ['Icon' => '','Titulo' => 'Sexo',             'Texto' => $data['rowData']['Sexo']],
                ];
                break;
            //Empresas
            case 2:
                $arrData_1 = [
                    ['Icon' => '','Titulo' => 'Tipo',         'Texto' => $data['rowData']['Tipo']],
                    ['Icon' => '','Titulo' => 'Tipo Entidad', 'Texto' => $data['rowData']['TipoEntidad']],
                    ['Icon' => '','Titulo' => 'Nombre',       'Texto' => $Entidad],
                    ['Icon' => '','Titulo' => 'Nick',         'Texto' => $data['rowData']['Nick']],
                    ['Icon' => '','Titulo' => 'Web',          'Texto' => $data['rowData']['Web']],
                    ['Icon' => '','Titulo' => 'Giro',         'Texto' => $data['rowData']['Giro']],
                ];
                break;
        }
        $arrData_2 = [
            ['Icon' => '','Titulo' => 'Rut',        'Texto' => $data['rowData']['Rut']],
            ['Icon' => '','Titulo' => 'Email',      'Texto' => $data['rowData']['Email']],
            ['Icon' => '','Titulo' => 'Celular',    'Texto' => $data['Fnc_DataNumbers']->formatPhone($data['rowData']['Fono1'])],
            ['Icon' => '','Titulo' => 'Teléfono',   'Texto' => $data['Fnc_DataNumbers']->formatPhone($data['rowData']['Fono2'])],
        ];
        $arrData_3 = [
            ['Icon' => '','Titulo' => 'Ciudad',      'Texto' => $data['rowData']['Ciudad']],
            ['Icon' => '','Titulo' => 'Comuna',      'Texto' => $data['rowData']['Comuna']],
            ['Icon' => '','Titulo' => 'Dirección',   'Texto' => $data['rowData']['Direccion']],
            ['Icon' => '','Titulo' => 'Sector',      'Texto' => $data['rowData']['Sector']],
            ['Icon' => '','Titulo' => 'Estado',      'Texto' => '<span class="badge-sp1 badge-sp1-'.$data['rowData']['EstadoColor'].'">'.$data['rowData']['Estado'].'</span>'],
        ];
        $arrData_4 = [
            ['Icon' => '','Titulo' => 'Nombre',    'Texto' => $data['rowData']['RepLegalNombre']],
            ['Icon' => '','Titulo' => 'Rut',       'Texto' => $data['rowData']['RepLegalRut']],
            ['Icon' => '','Titulo' => 'Email',     'Texto' => $data['rowData']['RepLegalEmail']],
            ['Icon' => '','Titulo' => 'Celular',   'Texto' => $data['rowData']['RepLegalFono1']],
            ['Icon' => '','Titulo' => 'Teléfono',  'Texto' => $data['rowData']['RepLegalFono2']],
        ];
        $arrData_5 = [
            ['Icon' => '','Titulo' => 'X',          'Texto' => $data['rowData']['Social_X']],
            ['Icon' => '','Titulo' => 'Facebook',   'Texto' => $data['rowData']['Social_Facebook']],
            ['Icon' => '','Titulo' => 'Instagram',  'Texto' => $data['rowData']['Social_Instagram']],
            ['Icon' => '','Titulo' => 'Linkedin',   'Texto' => $data['rowData']['Social_Linkedin']],
        ];

        echo '<h5 class="box-title text-color-red-dark">Datos Básicos</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData_1, 8);
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData_2, 8);
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData_3, 8);

        echo '<h5 class="box-title text-color-red-dark">Representante Legal</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData_4, 8);

        echo '<h5 class="box-title text-color-red-dark">Social</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData_5, 8);

        ?>
    </div>
</div>
