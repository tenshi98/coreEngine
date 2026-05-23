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
    <div class="col-xs-12 col-sm-12 col-md-7 col-lg-8 col-xl-9 col-xxl-10">
        <?php
        /**********************************************************/
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
        /**********************************************************/
        $arrData_2 = [
            ['Icon' => '','Titulo' => 'Contacto Nombre', 'Texto' => $data['rowData']['Contacto_Nombre']],
            ['Icon' => '','Titulo' => 'Contacto Fono1',  'Texto' => $data['rowData']['Contacto_Fono1']],
            ['Icon' => '','Titulo' => 'Contacto Fono2',  'Texto' => $data['rowData']['Contacto_Fono2']],
            ['Icon' => '','Titulo' => 'Contacto Fax',    'Texto' => $data['rowData']['Contacto_Fax']],
            ['Icon' => '','Titulo' => 'Contacto Email',  'Texto' => $data['rowData']['Contacto_Email']],
            ['Icon' => '','Titulo' => 'Contacto Web',    'Texto' => $data['rowData']['Contacto_Web']],
        ];
        /**********************************************************/
        $arrData_3 = [
            ['Icon' => '','Titulo' => 'Representante Nombre',  'Texto' => $data['rowData']['RepresentanteNombre']],
            ['Icon' => '','Titulo' => 'Representante Rut',     'Texto' => $data['rowData']['RepresentanteRut']],
            ['Icon' => '','Titulo' => 'Representante Fono',    'Texto' => $data['rowData']['RepresentanteFono']],
            ['Icon' => '','Titulo' => 'Representante Email',   'Texto' => $data['rowData']['RepresentanteEmail']],
        ];
        /**********************************************************/
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
        /**********************************************************/
        $arrData_5   = [];
        //Se condiciona el uso de IA
        if($data['UserData']["Config_IA_Uso"]==2){
            $arrData_5[] = ['Icon' => '','Titulo' => 'Provider',             'Texto' => $data['rowData']['IAProvider']];
            $arrData_5[] = ['Icon' => '','Titulo' => 'API Key',              'Texto' => $data['rowData']['Config_IA_ApiKey']];
            $arrData_5[] = ['Icon' => '','Titulo' => 'API Model',            'Texto' => $data['rowData']['Config_IA_Model']];
            $arrData_5[] = ['Icon' => '','Titulo' => 'API Base URL',         'Texto' => $data['rowData']['Config_IA_Base_URL']];
            $arrData_5[] = ['Icon' => '','Titulo' => 'Nombre IA',            'Texto' => $data['rowData']['Config_IA_Name']];
            $arrData_5[] = ['Icon' => '','Titulo' => 'Tono IA',              'Texto' => $data['rowData']['Config_IA_Tone']];
            $arrData_5[] = ['Icon' => '','Titulo' => 'Uso Memoria Cache IA', 'Texto' => activo($data['rowData']['Config_IA_UsoCache'])];
        }

        /**********************************************************/
        $arrData_6 = [
            ['Icon' => '','Titulo' => 'URL Twitter',    'Texto' => (!empty($data['rowData']['Social_X']) ? '<a href="'.$data['rowData']['Social_X'].'" class="twitter"><i class="bi bi-twitter"></i> Twitter</a>' : '')],
            ['Icon' => '','Titulo' => 'URL Facebook',   'Texto' => (!empty($data['rowData']['Social_Facebook']) ? '<a href="'.$data['rowData']['Social_Facebook'].'"  class="facebook"><i class="bi bi-facebook"></i> Facebook</a>' : '')],
            ['Icon' => '','Titulo' => 'URL Instagram',  'Texto' => (!empty($data['rowData']['Social_Instagram']) ? '<a href="'.$data['rowData']['Social_Instagram'].'" class="instagram"><i class="bi bi-instagram"></i> Instagram</a>' : '')],
            ['Icon' => '','Titulo' => 'URL Linkedin',   'Texto' => (!empty($data['rowData']['Social_Linkedin']) ? '<a href="'.$data['rowData']['Social_Linkedin'].'"  class="linkedin"><i class="bi bi-linkedin"></i> Linkedin</a>' : '')],
        ];
        /**********************************************************/
        $arrData_7 = [
            ['Icon' => '','Titulo' => '<strong>Sistema:</strong> Modal - Subtítulos',                                     'Texto' => activo($data['rowData']['sistemaModalSubtitle'])],
            ['Icon' => '','Titulo' => '<strong>Sistema:</strong> Modal - Botón Cerrar',                                   'Texto' => activo($data['rowData']['sistemaModalCloseBTN'])],
            ['Icon' => '','Titulo' => '<strong>Sistema:</strong> Uso Whatsapp',                                           'Texto' => activo($data['rowData']['sistemaUsoWhatsapp'])],
            ['Icon' => '','Titulo' => '<strong>Sistema:</strong> Motor Email',                                            'Texto' => $data['rowData']['ConfigEmail']],
            ['Icon' => '','Titulo' => '<strong>Sistema:</strong> Motor Mapas',                                            'Texto' => $data['rowData']['ConfigMap']],
            ['Icon' => '','Titulo' => '<strong>Sistema:</strong> Mostrar Widget Meteorologico',                           'Texto' => activo($data['rowData']['Config_Principal_Meteo'])],
            ['Icon' => '','Titulo' => '<strong>Sistema:</strong> Mostrar Widget Radio',                                   'Texto' => activo($data['rowData']['Config_Principal_Radio'])],
            ['Icon' => '','Titulo' => '<strong>Sistema:</strong> Mostrar Widget Feed',                                    'Texto' => activo($data['rowData']['Config_Principal_Feed'])],
            ['Icon' => '','Titulo' => '<strong>Sistema:</strong> URL Feed Noticias',                                      'Texto' => $data['rowData']['Config_Principal_FeedURL']],
            ['Icon' => '','Titulo' => '<strong>Sistema:</strong> Configuracion Uso IA',                                   'Texto' => activo($data['rowData']['Config_IA_Uso'])],
            ['Icon' => '','Titulo' => '<strong>Kanban Tareas:</strong> Uso listados de tareas en las Tareas Pendientes',  'Texto' => activo($data['rowData']['KanbanTareasUsoTareas'])],
            ['Icon' => '','Titulo' => '<strong>Kanban Tareas:</strong> Admin Tableros Independiente de las Tareas',       'Texto' => activo($data['rowData']['KanbanTareasAdminTabIndepend'])],
            ['Icon' => '','Titulo' => '<strong>Gestión Entidades:</strong> Uso Cargas',                                   'Texto' => activo($data['rowData']['entidadesListadoVerCargas'])],
            ['Icon' => '','Titulo' => '<strong>Gestión Entidades:</strong> Uso Contactos',                                'Texto' => activo($data['rowData']['entidadesListadoVerContactos'])],
            ['Icon' => '','Titulo' => '<strong>Gestión Entidades:</strong> Uso Documentos',                               'Texto' => activo($data['rowData']['entidadesListadoVerDocumentos'])],
            ['Icon' => '','Titulo' => '<strong>Gestión Entidades:</strong> Uso Password',                                 'Texto' => activo($data['rowData']['entidadesListadoUsoPassword'])],
            ['Icon' => '','Titulo' => '<strong>Productos - Listado:</strong> Uso Documentos',                             'Texto' => activo($data['rowData']['productosListadoVerDocumentos'])],
            ['Icon' => '','Titulo' => '<strong>Servicios - Listado:</strong> Uso Documentos',                             'Texto' => activo($data['rowData']['serviciosListadoVerDocumentos'])],
            ['Icon' => '','Titulo' => '<strong>Gestión Documentos:</strong> Uso Bodega',                                  'Texto' => activo($data['rowData']['gestionDocumentosUsoBodega'])],
            ['Icon' => '','Titulo' => '<strong>Externalización Servicios:</strong> Clientes - Uso Planes',                'Texto' => activo($data['rowData']['entidadesListadoUsoPlanes'])],
            ['Icon' => '','Titulo' => '<strong>Externalización Servicios:</strong> Clientes - Uso Usuarios',              'Texto' => activo($data['rowData']['entidadesListadoUsoUsuarios'])],
            ['Icon' => '','Titulo' => '<strong>Externalización Servicios:</strong> Clientes - Uso Máquinas',              'Texto' => activo($data['rowData']['entidadesListadoUsoMaquinas'])],
            ['Icon' => '','Titulo' => '<strong>Maquinas - Listado:</strong> Uso Documentos',                              'Texto' => activo($data['rowData']['maquinasListadoVerDocumentos'])],
            ['Icon' => '','Titulo' => '<strong>Maquinas - Listado:</strong> Uso Componentes',                             'Texto' => activo($data['rowData']['maquinasListadoComponentes'])],
            ['Icon' => '','Titulo' => '<strong>Maquinas - Listado:</strong> Uso Telemetría',                              'Texto' => activo($data['rowData']['maquinasListadoTelemetria'])],
            ['Icon' => '','Titulo' => '<strong>Maquinas - Listado:</strong> Uso Backups Telemetría',                      'Texto' => activo($data['rowData']['maquinasListadoBackups'])],
            ['Icon' => '','Titulo' => '<strong>Maquinas - Listado:</strong> Envío de Notificaciones',                     'Texto' => activo($data['rowData']['maquinasListadoNotificaciones'])],
            ['Icon' => '','Titulo' => '<strong>Usuarios - Listado:</strong> Uso Permisos Bodegas',                        'Texto' => activo($data['rowData']['usuariosPermisosBodegas'])],
            ['Icon' => '','Titulo' => '<strong>Usuarios - Listado:</strong> Uso Permisos Maquinas',                       'Texto' => activo($data['rowData']['usuariosPermisosMaquinas'])],
        ];

        /**************************************/
        /**************************************/
        echo '
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6">';
                echo '<h5 class="box-title text-color-red-dark">Datos Básicos</h5>';
                $data['Fnc_WidgetsCommon']->responsiveTable($arrData_1, 6);
            echo '
            </div>
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6">';
                echo '<h5 class="box-title text-color-red-dark">Datos de Contacto</h5>';
                $data['Fnc_WidgetsCommon']->responsiveTable($arrData_2, 6);
            echo '
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-6 col-xl-12 col-xxl-12">';
                echo '<h5 class="box-title text-color-red-dark">Representante Legal</h5>';
                $data['Fnc_WidgetsCommon']->responsiveTable($arrData_3, 8);
            echo '
            </div>
        </div>';

        echo '<h5 class="box-title text-color-red-dark">APIS</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData_4, 8);

        echo '<h5 class="box-title text-color-red-dark">Inteligencia Artificial</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData_5, 8);

        echo '<h5 class="box-title text-color-red-dark">Redes Sociales</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData_6, 4);

        echo '<h5 class="box-title text-color-red-dark">Configuracion Sistema</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData_7, 4);

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
