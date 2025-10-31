<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4 col-xl-3 col-xxl-2">
        <?php
        $UserIMG = $BASE.'/img/picture-img.jpg';
        ?>
        <img src="<?php echo $UserIMG; ?>" alt="Profile" class="square-rounded-2 square-border-3 w-100">
    </div>
    <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-7">
        <?php
        //Se verifica si existe
        $NombreEntidad = '';
        if(isset($data['rowData']['EntidadesNombre'])&&$data['rowData']['EntidadesNombre']){
            $NombreEntidad .= $data['rowData']['EntidadesNombre'].' '.$data['rowData']['EntidadesApellido'];
        }else{
            $NombreEntidad .= $data['rowData']['EntidadesRazonSocial'];
        }
        if (isset($data['rowData']['EntidadesNick'])&&$data['rowData']['EntidadesNick']!='') { $NombreEntidad .= ' ('.$data['rowData']['EntidadesNick'].')';}

        $arrData1 = [
            ['Icon' => '','Titulo' => 'Documento Mercantil',   'Texto' => 'Cotización N° '.$data['rowData']['idCotizacion']],
            ['Icon' => '','Titulo' => 'Cliente',               'Texto' => $NombreEntidad],
            ['Icon' => '','Titulo' => 'Fecha de Cotización',   'Texto' => $data['Fnc_DataDate']->fechaEstandar($data['rowData']['Creacion_fecha'])],
            ['Icon' => '','Titulo' => 'Hora de Cotización',    'Texto' => $data['rowData']['Creacion_hora']],
            ['Icon' => '','Titulo' => 'Observaciones',         'Texto' => $data['rowData']['Observaciones']],
            ['Icon' => '','Titulo' => 'Usuario Creacion',      'Texto' => $data['rowData']['Usuario']],
        ];
        $arrData2 = [
            ['Icon' => '','Titulo' => 'Valor Neto',    'Texto' => $data['Fnc_DataNumbers']->Valores($data['rowData']['ValorNeto'], 2)],
            ['Icon' => '','Titulo' => 'IVA',           'Texto' => $data['Fnc_DataNumbers']->Valores($data['rowData']['IVA'], 2)],
            ['Icon' => '','Titulo' => 'Valor Total',   'Texto' => $data['Fnc_DataNumbers']->Valores($data['rowData']['ValorTotal'], 2)],
        ];

        echo '<h5 class="card-title">Datos Básicos</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData1, 8);
        echo '<h5 class="card-title">Montos y Pagos</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData2, 8);

        ?>
    </div>
</div>
