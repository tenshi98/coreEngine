<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4 col-xl-3 col-xxl-2">
        <?php
        $UserIMG  = !empty($data['rowData']['Direccion_img'])
                    ? $data['UserData']['MainPathUrl'].$data['rowData']['Direccion_img']
                    : $BASE.'/img/picture-img.jpg';
        ?>
        <img src="<?php echo $UserIMG; ?>" alt="Profile" class="square-rounded-2 square-border-3 w-100">
    </div>
    <div class="col-xs-12 col-sm-12 col-md-7 col-lg-8 col-xl-9 col-xxl-10">
        <?php
        $arrData = [
            ['Icon' => '','Titulo' => 'Categoria',      'Texto' => $data['rowData']['Categoria']],
            ['Icon' => '','Titulo' => 'Nombre',         'Texto' => $data['rowData']['Nombre']],
            ['Icon' => '','Titulo' => 'Valor Ingreso',  'Texto' => $data['rowData']['ValorIngreso']],
            ['Icon' => '','Titulo' => 'Valor Egreso',   'Texto' => $data['rowData']['ValorEgreso']],
            ['Icon' => '','Titulo' => 'Descripcion',    'Texto' => $data['rowData']['Descripcion']],
            ['Icon' => '','Titulo' => 'Codigo',         'Texto' => $data['rowData']['Codigo']],
            ['Icon' => '','Titulo' => 'Estado',         'Texto' => '<span class="badge-sp1 badge-sp1-'.$data['rowData']['EstadoColor'].'">'.$data['rowData']['Estado'].'</span>'],
        ];

        $data['Fnc_WidgetsCommon']->responsiveTable($arrData, 8);

        ?>
    </div>
</div>
