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
        $arrData = [
            ['Icon' => '','Titulo' => 'idUsuario',  'Texto' => $data['rowData']['idUsuario']],
            ['Icon' => '','Titulo' => 'Email',      'Texto' => $data['rowData']['Email']],
            ['Icon' => '','Titulo' => 'Numero',     'Texto' => $data['Fnc_DataNumbers']->Valores($data['rowData']['Numero'], 2)],
            ['Icon' => '','Titulo' => 'Rut',        'Texto' => $data['rowData']['Rut']],
            ['Icon' => '','Titulo' => 'Patente',    'Texto' => $data['rowData']['Patente']],
            ['Icon' => '','Titulo' => 'Fecha',      'Texto' => $data['Fnc_DataDate']->fechaEstandar($data['rowData']['Fecha'])],
            ['Icon' => '','Titulo' => 'Hora',       'Texto' => $data['rowData']['Hora']],
            ['Icon' => '','Titulo' => 'Palabra',    'Texto' => $data['rowData']['Palabra']],
        ];

        echo '<h5 class="card-title">Datos del Perfil</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData, 8);
        ?>
    </div>
</div>
