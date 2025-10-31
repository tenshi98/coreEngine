<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4 col-xl-3 col-xxl-2">
        <?php
        $UserIMG  = !empty($data['rowData']['Direccion_img'])
                    ? $BASE.'/upload/'.$data['rowData']['Direccion_img']
                    : $BASE.'/img/picture-img.jpg';
        ?>
        <img src="<?php echo $UserIMG; ?>" alt="Profile" class="square-rounded-2 square-border-3 w-100">
    </div>
    <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-7">
        <?php
        $arrData_1 = [
            ['Icon' => '','Titulo' => 'Nombre',         'Texto' => $data['rowData']['Nombre']],
            ['Icon' => '','Titulo' => 'Identificador',  'Texto' => $data['rowData']['CodIdentificador']],
            ['Icon' => '','Titulo' => 'Descripcion',    'Texto' => $data['rowData']['Descripcion']],
            ['Icon' => '','Titulo' => 'Estado',         'Texto' => '<span class="badge '.$data['rowData']['EstadoColor'].'">'.$data['rowData']['Estado'].'</span>'],
        ];
        echo '<h5 class="card-title">Datos Básicos</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData_1, 8);

        if($data['UserData']["maquinasListadoTelemetria"]==2){
            $arrData_2 = [
                ['Icon' => '','Titulo' => 'SIM - Numero Telefónico',    'Texto' => $data['rowData']['Sim_Num_Tel']],
                ['Icon' => '','Titulo' => 'SIM - Compañia',             'Texto' => $data['rowData']['Sim_Compania']],
                ['Icon' => '','Titulo' => 'Tiempo Fuera Linea Máximo',  'Texto' => $data['rowData']['TiempoFueraLinea']],
                ['Icon' => '','Titulo' => 'Tab',                        'Texto' => $data['rowData']['Tabs']],
                ['Icon' => '','Titulo' => 'Uso Geolocalización',        'Texto' => $data['rowData']['UsoGeo']],
                ['Icon' => '','Titulo' => 'Uso Sensores',               'Texto' => $data['rowData']['UsoSensores']],
                ['Icon' => '','Titulo' => 'Backup Tabla relacionada',   'Texto' => $data['rowData']['UsoBackup'].' ('.$data['rowData']['NregBackup'].' registros)'],
                ['Icon' => '','Titulo' => 'Alerta Temprana',            'Texto' => $data['rowData']['UsoAlertaTemprana']],
                ['Icon' => '','Titulo' => 'Tiempo Alertas Criticas',    'Texto' => $data['rowData']['AlertaTemprCritica']],
                ['Icon' => '','Titulo' => 'Tiempo Alertas Normales',    'Texto' => $data['rowData']['AlertaTemprNormal']],
            ];
            echo '<h5 class="card-title">Configuración</h5>';
            $data['Fnc_WidgetsCommon']->responsiveTable($arrData_2, 8);
        }

        ?>
    </div>
</div>
