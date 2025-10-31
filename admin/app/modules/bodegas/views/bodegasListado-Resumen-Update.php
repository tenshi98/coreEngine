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
            ['Icon' => '','Titulo' => 'Nombre',      'Texto' => $data['rowData']['Nombre']],
            ['Icon' => '','Titulo' => 'Ciudad',      'Texto' => $data['rowData']['Ciudad']],
            ['Icon' => '','Titulo' => 'Comuna',      'Texto' => $data['rowData']['Comuna']],
            ['Icon' => '','Titulo' => 'Direccion',   'Texto' => $data['rowData']['Direccion']],
            ['Icon' => '','Titulo' => 'Estado',      'Texto' => '<span class="badge '.$data['rowData']['EstadoColor'].'">'.$data['rowData']['Estado'].'</span>'],
        ];

        echo '<h5 class="card-title">Datos Básicos</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData, 8);

        ?>
    </div>
</div>
