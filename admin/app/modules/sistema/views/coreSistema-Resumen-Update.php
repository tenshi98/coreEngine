
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4 col-xl-3 col-xxl-2">
        <?php
        $UserIMG  = !empty($data['rowData']['Sistema_IMGLogo'])
                    ? $data['UserData']['MainPathUrl'].$data['rowData']['Sistema_IMGLogo']
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
                        '<b>Direccion</b><br>'.$data['rowData']['Sistema_Direccion']
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
    <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-7">
        <?php
        /**************************************/
        $arrData_1   = [];
        $arrData_1[] = ['Icon' => '','Titulo' => 'Nombre',     'Texto' => $data['rowData']['Sistema_Nombre']];
        $arrData_1[] = ['Icon' => '','Titulo' => 'Email',      'Texto' => $data['rowData']['Sistema_Email']];
        $arrData_1[] = ['Icon' => '','Titulo' => 'Rut',        'Texto' => $data['rowData']['Sistema_Rut']];
        $arrData_1[] = ['Icon' => '','Titulo' => 'Ciudad',     'Texto' => $data['rowData']['Ciudad']];
        $arrData_1[] = ['Icon' => '','Titulo' => 'Comuna',     'Texto' => $data['rowData']['Comuna']];
        $arrData_1[] = ['Icon' => '','Titulo' => 'Direccion',  'Texto' => $data['rowData']['Sistema_Direccion']];
        $arrData_1[] = ['Icon' => '','Titulo' => 'Tema',       'Texto' => $data['rowData']['Tema']];
        //Se condiciona el uso de Whatsapp
        if($data['UserData']["sistemaUsoWhatsapp"]==2){
            $arrData_1[] = ['Icon' => '','Titulo' => 'Fono Noti Whatsapp',  'Texto' => $data['rowData']['Sistema_NotiWhatsapp']];
        }
        /**************************************/
        $arrData_2 = [
            ['Icon' => '','Titulo' => 'Contacto Nombre', 'Texto' => $data['rowData']['Contacto_Nombre']],
            ['Icon' => '','Titulo' => 'Contacto Fono1',  'Texto' => $data['rowData']['Contacto_Fono1']],
            ['Icon' => '','Titulo' => 'Contacto Fono2',  'Texto' => $data['rowData']['Contacto_Fono2']],
            ['Icon' => '','Titulo' => 'Contacto Fax',    'Texto' => $data['rowData']['Contacto_Fax']],
            ['Icon' => '','Titulo' => 'Contacto Email',  'Texto' => $data['rowData']['Contacto_Email']],
            ['Icon' => '','Titulo' => 'Contacto Web',    'Texto' => $data['rowData']['Contacto_Web']],
        ];
        /**************************************/
        $arrData_3 = [
            ['Icon' => '','Titulo' => 'Representante Nombre',  'Texto' => $data['rowData']['RepresentanteNombre']],
            ['Icon' => '','Titulo' => 'Representante Rut',     'Texto' => $data['rowData']['RepresentanteRut']],
            ['Icon' => '','Titulo' => 'Representante Fono',    'Texto' => $data['rowData']['RepresentanteFono']],
            ['Icon' => '','Titulo' => 'Representante Email',   'Texto' => $data['rowData']['RepresentanteEmail']],
        ];
        /**************************************/
        $arrData_4   = [];
        //Se condiciona el motor de mapas
        switch ($data['UserData']["Config_motorMap"]) {
            case 1:$arrData_4[] = ['Icon' => '', 'Titulo' => 'Config API GoogleMaps', 'Texto' => $data['rowData']['Config_API_GoogleMaps']];break;
        }
        //Se condiciona el uso de Whatsapp
        if($data['UserData']["sistemaUsoWhatsapp"]==2){
            $arrData_4[] = ['Icon' => '','Titulo' => 'Config Whatsapp Token',        'Texto' => $data['rowData']['Config_WhatsappToken']];
            $arrData_4[] = ['Icon' => '','Titulo' => 'Config Whatsapp Instance Id',  'Texto' => $data['rowData']['Config_WhatsappInstanceId']];
        }
        $arrData_4[] = ['Icon' => '','Titulo' => 'Mostrar Widget Meteorologico',   'Texto' => activo($data['rowData']['Config_Principal_Meteo'])];
        $arrData_4[] = ['Icon' => '','Titulo' => 'Mostrar Widget Radio',           'Texto' => activo($data['rowData']['Config_Principal_Radio'])];
        $arrData_4[] = ['Icon' => '','Titulo' => 'Mostrar Widget Feed',            'Texto' => activo($data['rowData']['Config_Principal_Feed'])];
        //Solo si se muestra
        if($data['rowData']['Config_Principal_Feed']==2){
             $arrData_4[] = ['Icon' => '','Titulo' => 'URL Feed Noticias',         'Texto' => $data['rowData']['Config_Principal_FeedURL']];
        }

        /**************************************/
        $arrData_5 = [
            ['Icon' => '','Titulo' => 'URL Twitter',    'Texto' => (!empty($data['rowData']['Social_X']) ? '<a href="'.$data['rowData']['Social_X'].'" class="twitter"><i class="bi bi-twitter"></i> Twitter</a>' : '')],
            ['Icon' => '','Titulo' => 'URL Facebook',   'Texto' => (!empty($data['rowData']['Social_Facebook']) ? '<a href="'.$data['rowData']['Social_Facebook'].'"  class="facebook"><i class="bi bi-facebook"></i> Facebook</a>' : '')],
            ['Icon' => '','Titulo' => 'URL Instagram',  'Texto' => (!empty($data['rowData']['Social_Instagram']) ? '<a href="'.$data['rowData']['Social_Instagram'].'" class="instagram"><i class="bi bi-instagram"></i> Instagram</a>' : '')],
            ['Icon' => '','Titulo' => 'URL Linkedin',   'Texto' => (!empty($data['rowData']['Social_Linkedin']) ? '<a href="'.$data['rowData']['Social_Linkedin'].'"  class="linkedin"><i class="bi bi-linkedin"></i> Linkedin</a>' : '')],
        ];

        /**************************************/
        /**************************************/
        echo '<h5 class="box-title text-color-red-dark">Datos Básicos</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData_1, 8);

        echo '<h5 class="box-title text-color-red-dark">Datos de Contacto</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData_2, 8);

        echo '<h5 class="box-title text-color-red-dark">Representante Legal</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData_3, 8);

        echo '<h5 class="box-title text-color-red-dark">APIS Y Configuraciones</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData_4, 7);

        echo '<h5 class="box-title text-color-red-dark">Redes Sociales</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData_5, 8);

        //funcion para devolver el uso
        function activo($valor){
            switch ($valor) {
                case 1: return 'No'; break;
                case 2: return 'Si'; break;
            }
        }
        ?>
    </div>

</div>
