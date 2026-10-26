<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4 col-xl-3 col-xxl-2">
        <?php
        $UserIMG  = !empty($data['rowData']['Sistema_IMGLogo'])
                    ? $data['UserData']['MainPathUrl'].$data['rowData']['Sistema_IMGLogo']
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
                        // Variable para los marcadores
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
                        break;
                }
            echo '</div>';
        } ?>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-7 col-lg-8 col-xl-9 col-xxl-10 record-attachment">
        <?php
        /**************************************/
        $Theme_color = 'summary-theme-color-2';
        $arrData_1   = [];
        $arrData_1[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-card-text', 'Icon_color' => 10,'Title' => 'Nombre',     'Text' => $data['rowData']['Sistema_Nombre']];
        $arrData_1[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-envelope', 'Icon_color' => 10,'Title' => 'Email',      'Text' => $data['rowData']['Sistema_Email'], 'Text_color' => 26, 'Text_copy' => $data['rowData']['Sistema_Email']];
        $arrData_1[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-person-vcard', 'Icon_color' => 10,'Title' => 'Rut',        'Text' => $data['rowData']['Sistema_Rut']];
        $arrData_1[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-geo-alt', 'Icon_color' => 10,'Title' => 'Ciudad',     'Text' => $data['rowData']['Ciudad']];
        $arrData_1[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-geo-alt', 'Icon_color' => 10,'Title' => 'Comuna',     'Text' => $data['rowData']['Comuna']];
        $arrData_1[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-geo-fill', 'Icon_color' => 10,'Title' => 'Direccion',  'Text' => $data['rowData']['Sistema_Direccion']];
        $arrData_1[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-palette', 'Icon_color' => 10,'Title' => 'Tema',       'Text' => $data['rowData']['Tema']];
        //Se condiciona el uso de Whatsapp
        if($data['UserData']["sistemaUsoWhatsapp"]==2){
            $arrData_1[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-phone', 'Icon_color' => 10,'Title' => 'Fono Noti Whatsapp',  'Text' => $data['rowData']['Sistema_NotiWhatsapp'], 'Text_color' => 26, 'Text_copy' => $data['rowData']['Sistema_NotiWhatsapp']];
        }
        /**************************************/
        $arrData_2 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-person',    'Icon_color' => 10,'Title' => 'Contacto Nombre', 'Text' => $data['rowData']['Contacto_Nombre']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-telephone', 'Icon_color' => 10,'Title' => 'Contacto Fono1',  'Text' => $data['rowData']['Contacto_Fono1'], 'Text_color' => 26, 'Text_copy' => $data['rowData']['Contacto_Fono1']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-telephone', 'Icon_color' => 10,'Title' => 'Contacto Fono2',  'Text' => $data['rowData']['Contacto_Fono2'], 'Text_color' => 26, 'Text_copy' => $data['rowData']['Contacto_Fono2']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-printer',   'Icon_color' => 10,'Title' => 'Contacto Fax',    'Text' => $data['rowData']['Contacto_Fax']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-envelope',  'Icon_color' => 10,'Title' => 'Contacto Email',  'Text' => $data['rowData']['Contacto_Email'], 'Text_color' => 26, 'Text_copy' => $data['rowData']['Contacto_Email']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-globe',     'Icon_color' => 10,'Title' => 'Contacto Web',    'Text' => $data['rowData']['Contacto_Web']],
        ];
        /**************************************/
        $arrData_3 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-person',       'Icon_color' => 10,'Title' => 'Representante Nombre',  'Text' => $data['rowData']['RepresentanteNombre']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-person-vcard', 'Icon_color' => 10,'Title' => 'Representante Rut',     'Text' => $data['rowData']['RepresentanteRut']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-telephone',    'Icon_color' => 10,'Title' => 'Representante Fono',    'Text' => $data['rowData']['RepresentanteFono'], 'Text_color' => 26, 'Text_copy' => $data['rowData']['RepresentanteFono']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-envelope',     'Icon_color' => 10,'Title' => 'Representante Email',   'Text' => $data['rowData']['RepresentanteEmail'], 'Text_color' => 26, 'Text_copy' => $data['rowData']['RepresentanteEmail']],
        ];
        /**************************************/
        $arrData_4   = [];
        //Se condiciona el motor de mapas
        switch ($data['UserData']["Config_motorMap"]) {
            case 1:$arrData_4[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-key', 'Icon_color' => 10, 'Title' => 'Config API GoogleMaps', 'Text' => $data['rowData']['Config_API_GoogleMaps']];break;
        }
        //Se condiciona el uso de Whatsapp
        if($data['UserData']["sistemaUsoWhatsapp"]==2){
            $arrData_4[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-key',  'Icon_color' => 10,'Title' => 'Config Whatsapp Token',        'Text' => $data['rowData']['Config_WhatsappToken']];
            $arrData_4[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-gear', 'Icon_color' => 10,'Title' => 'Config Whatsapp Instance Id',  'Text' => $data['rowData']['Config_WhatsappInstanceId']];
        }
        $arrData_4[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-cloud-sun', 'Icon_color' => 10,'Title' => '<strong>Sistema:</strong> Mostrar Widget Meteorologico',   'Text' => activo($data['rowData']['Config_Principal_Meteo'])];
        $arrData_4[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-broadcast', 'Icon_color' => 10,'Title' => '<strong>Sistema:</strong> Mostrar Widget Radio',           'Text' => activo($data['rowData']['Config_Principal_Radio'])];
        $arrData_4[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-rss',       'Icon_color' => 10,'Title' => '<strong>Sistema:</strong> Mostrar Widget Feed',            'Text' => activo($data['rowData']['Config_Principal_Feed'])];
        //Solo si se muestra
        if($data['rowData']['Config_Principal_Feed']==2){
             $arrData_4[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-rss', 'Icon_color' => 10,'Title' => '<strong>Sistema:</strong> URL Feed Noticias',         'Text' => $data['rowData']['Config_Principal_FeedURL']];
        }
        $arrData_4[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-robot',       'Icon_color' => 10,'Title' => '<strong>Sistema:</strong> Configuracion Uso IA',             'Text' => activo($data['rowData']['Config_IA_Uso'])];
        $arrData_4[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-shield-lock', 'Icon_color' => 10,'Title' => '<strong>Usuarios - Listado:</strong> Uso Permisos Bodegas',  'Text' => activo($data['rowData']['usuariosPermisosBodegas'])];
        $arrData_4[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-shield-lock', 'Icon_color' => 10,'Title' => '<strong>Usuarios - Listado:</strong> Uso Permisos Maquinas', 'Text' => activo($data['rowData']['usuariosPermisosMaquinas'])];

        /**************************************/
        $arrData_5   = [];
        //Se condiciona el uso de IA
        if($data['UserData']["Config_IA_Uso"]==2){
            $arrData_5[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-robot',          'Icon_color' => 10,'Title' => 'Provider',             'Text' => $data['rowData']['IAProvider']];
            $arrData_5[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-key',            'Icon_color' => 10,'Title' => 'API Key',              'Text' => $data['rowData']['Config_IA_ApiKey']];
            $arrData_5[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-cpu',            'Icon_color' => 10,'Title' => 'API Model',            'Text' => $data['rowData']['Config_IA_Model']];
            $arrData_5[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-link-45deg',     'Icon_color' => 10,'Title' => 'API Base URL',         'Text' => $data['rowData']['Config_IA_Base_URL']];
            $arrData_5[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-robot',          'Icon_color' => 10,'Title' => 'Nombre IA',            'Text' => $data['rowData']['Config_IA_Name']];
            $arrData_5[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-sliders',        'Icon_color' => 10,'Title' => 'Tono IA',              'Text' => $data['rowData']['Config_IA_Tone']];
            $arrData_5[] = ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-database-check', 'Icon_color' => 10,'Title' => 'Uso Memoria Cache IA', 'Text' => activo($data['rowData']['Config_IA_UsoCache'])];
        }

        /**************************************/
        $arrData_6 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-twitter-x', 'Icon_color' => 10,'Title' => 'URL Twitter',    'Text' => (!empty($data['rowData']['Social_X']) ? '<a href="'.$data['rowData']['Social_X'].'" class="twitter"><i class="bi bi-twitter"></i> Twitter</a>' : '')],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-facebook',  'Icon_color' => 10,'Title' => 'URL Facebook',   'Text' => (!empty($data['rowData']['Social_Facebook']) ? '<a href="'.$data['rowData']['Social_Facebook'].'"  class="facebook"><i class="bi bi-facebook"></i> Facebook</a>' : '')],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-instagram', 'Icon_color' => 10,'Title' => 'URL Instagram',  'Text' => (!empty($data['rowData']['Social_Instagram']) ? '<a href="'.$data['rowData']['Social_Instagram'].'" class="instagram"><i class="bi bi-instagram"></i> Instagram</a>' : '')],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-linkedin',  'Icon_color' => 10,'Title' => 'URL Linkedin',   'Text' => (!empty($data['rowData']['Social_Linkedin']) ? '<a href="'.$data['rowData']['Social_Linkedin'].'"  class="linkedin"><i class="bi bi-linkedin"></i> Linkedin</a>' : '')],
        ];

        /**************************************/
        /**************************************/
        echo $data['Fnc_WidgetsViews']->tittle_v2('Datos Básicos', 23);
        echo '<div class="row">';
            $OptionsTable['Cols'] = 2;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData_1, $OptionsTable);
        echo '</div>';

        echo $data['Fnc_WidgetsViews']->tittle_v2('Datos de Contacto', 23);
        echo '<div class="row">';
            $OptionsTable['Cols'] = 2;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData_2, $OptionsTable);
        echo '</div>';

        echo $data['Fnc_WidgetsViews']->tittle_v2('Representante Legal', 23);
        echo '<div class="row">';
            $OptionsTable['Cols'] = 2;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData_3, $OptionsTable);
        echo '</div>';

        echo $data['Fnc_WidgetsViews']->tittle_v2('APIS Y Configuraciones', 23);
        echo '<div class="row">';
            $OptionsTable['Cols'] = 2;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData_4, $OptionsTable);
        echo '</div>';

        echo $data['Fnc_WidgetsViews']->tittle_v2('Inteligencia Artificial', 23);
        echo '<div class="row">';
            $OptionsTable['Cols'] = 2;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData_5, $OptionsTable);
        echo '</div>';

        echo $data['Fnc_WidgetsViews']->tittle_v2('Redes Sociales', 23);
        echo '<div class="row">';
            $OptionsTable['Cols'] = 2;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData_6, $OptionsTable);
        echo '</div>';

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
