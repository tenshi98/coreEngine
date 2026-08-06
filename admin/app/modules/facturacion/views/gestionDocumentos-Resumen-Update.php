<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4 col-xl-3 col-xxl-2">
        <?php
        $UserIMG = $BASE.'/img/picture-img.jpg';
        ?>
        <img src="<?php echo $UserIMG; ?>" alt="Profile" class="square-rounded-2 square-border-3 w-100">
    </div>
    <div class="col-xs-12 col-sm-12 col-md-7 col-lg-8 col-xl-9 col-xxl-10">
        <?php
        //Se verifica movimiento
        switch ($data['rowData']['idTipo']) {
            case 1: $Movimiento = $data['rowData']['BodegaIngreso']; $Entidad = 'Proveedor';break;  //Ingreso
            case 2: $Movimiento = $data['rowData']['BodegaEgreso'];  $Entidad = 'Cliente';break;   //Egreso
        }
        //valido
        $NombreEntidad  = '';
        //Se obtiene el nombre o la razón social
        switch ($data['rowData']['idTipoEntidad']) {
            case 1: $NombreEntidad .= $data['rowData']['EntidadesApellido'].', '.$data['rowData']['EntidadesNombre']; break; //Persona Natural
            case 2: $NombreEntidad .= $data['rowData']['EntidadesRazonSocial']; break;                                       //Empresas
        }
        $NombreEntidad .= !empty($data['rowData']['EntidadesNick'])
                        ? ' ('.$data['rowData']['EntidadesNick'].')'
                        : '';

        $arrData1 = [
            ['Icon' => '','Titulo' => 'Tipo Facturacion',    'Texto' => $data['rowData']['TipoFacturacion']],
            ['Icon' => '','Titulo' => 'Documento Mercantil', 'Texto' => $data['rowData']['Documento'].' '.($data['rowData']['N_Doc'] ?? 'nRef '.$data['rowData']['idFacturacion'])],
            ['Icon' => '','Titulo' => $Entidad,              'Texto' => $NombreEntidad],
            ['Icon' => '','Titulo' => 'Fecha de Creacion',   'Texto' => $data['Fnc_DataDate']->fechaEstandar($data['rowData']['Creacion_fecha'])],
            ['Icon' => '','Titulo' => 'Hora de Creacion',    'Texto' => $data['rowData']['Creacion_hora']],
            ['Icon' => '','Titulo' => 'Observaciones',       'Texto' => $data['rowData']['Observaciones']],
            ['Icon' => '','Titulo' => 'Usuario Creacion',    'Texto' => $data['rowData']['Usuario']],
        ];
        $arrData2 = [
            ['Icon' => '','Titulo' => 'Bodega Productos',    'Texto' => $Movimiento],
        ];
        $arrData3 = [
            ['Icon' => '','Titulo' => 'Valor Neto',    'Texto' => $data['Fnc_DataNumbers']->Valores($data['rowData']['ValorNeto'], 2)],
            ['Icon' => '','Titulo' => 'IVA',           'Texto' => $data['Fnc_DataNumbers']->Valores($data['rowData']['IVA'], 2)],
            ['Icon' => '','Titulo' => 'Valor Total',   'Texto' => $data['Fnc_DataNumbers']->Valores($data['rowData']['ValorTotal'], 2)],
            ['Icon' => '','Titulo' => 'Monto Pagado',  'Texto' => $data['Fnc_DataNumbers']->Valores($data['rowData']['MontoPagado'], 2)],
            ['Icon' => '','Titulo' => 'Estado Pago',   'Texto' => '<span class="badge-sp1 badge-sp1-'.$data['rowData']['EstadoColor'].'">'.$data['rowData']['EstadoPago'].'</span>'],
        ];

        echo '<h5 class="box-title text-color-red-dark">Datos Básicos</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData1, 8);
        echo '<h5 class="box-title text-color-red-dark">Productos</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData2, 8);
        echo '<h5 class="box-title text-color-red-dark">Montos y Pagos</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData3, 8);

        ?>
    </div>
</div>
