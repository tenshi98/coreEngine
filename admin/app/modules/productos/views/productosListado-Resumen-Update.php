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
            ['Icon' => '','Titulo' => 'Tipo',           'Texto' => $data['rowData']['Tipo']],
            ['Icon' => '','Titulo' => 'Categoria',      'Texto' => $data['rowData']['Categoria']],
            ['Icon' => '','Titulo' => 'Nombre',         'Texto' => $data['rowData']['Nombre']],
            ['Icon' => '','Titulo' => 'Marca',          'Texto' => $data['rowData']['Marca']],
            ['Icon' => '','Titulo' => 'Stock Limite',   'Texto' => $data['rowData']['StockLimite'].' '.$data['rowData']['UniMed']],
            ['Icon' => '','Titulo' => 'Valor Ingreso',  'Texto' => $data['rowData']['ValorIngreso']],
            ['Icon' => '','Titulo' => 'Valor Egreso',   'Texto' => $data['rowData']['ValorEgreso']],
            ['Icon' => '','Titulo' => 'Descripcion',    'Texto' => $data['rowData']['Descripcion']],
            ['Icon' => '','Titulo' => 'Codigo',         'Texto' => $data['rowData']['Codigo']],
            ['Icon' => '','Titulo' => 'Estado',         'Texto' => '<span class="badge '.$data['rowData']['EstadoColor'].'">'.$data['rowData']['Estado'].'</span>'],
        ];

        $data['Fnc_WidgetsCommon']->responsiveTable($arrData, 8);

        ?>
    </div>
</div>
