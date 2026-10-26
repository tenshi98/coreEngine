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
    <div class="col-xs-12 col-sm-12 col-md-7 col-lg-8 col-xl-9 col-xxl-10 record-attachment">
        <?php
        //Se verifica movimiento
        switch ($data['rowData']['idTipo']) {
            case 1: $Movimiento = $data['rowData']['BodegaIngreso']; $Entidad = 'Proveedor';break;  //Ingreso
            case 2: $Movimiento = $data['rowData']['BodegaEgreso'];  $Entidad = 'Cliente';break;   //Egreso
        }
        //valido
        $NombreEntidad  = '';
        // Se obtiene el nombre o la razón social
        switch ($data['rowData']['idTipoEntidad']) {
            case 1: $NombreEntidad .= $data['rowData']['EntidadesApellido'].', '.$data['rowData']['EntidadesNombre']; break; //Persona Natural
            case 2: $NombreEntidad .= $data['rowData']['EntidadesRazonSocial']; break;                                       //Empresas
        }
        $NombreEntidad .= !empty($data['rowData']['EntidadesNick'])
                        ? ' ('.$data['rowData']['EntidadesNick'].')'
                        : '';

        $Theme_color = 'summary-theme-color-2';
        $arrData1 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-receipt',           'Icon_color' => 10,'Title' => 'Tipo Facturacion',    'Text' => $data['rowData']['TipoFacturacion']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-file-earmark-text', 'Icon_color' => 10,'Title' => 'Documento Mercantil', 'Text' => $data['rowData']['Documento'].' '.($data['rowData']['N_Doc'] ?? 'nRef #'.$data['rowData']['idFacturacion'])],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-building',          'Icon_color' => 10,'Title' => $Entidad,              'Text' => $NombreEntidad],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-calendar-event',    'Icon_color' => 10,'Title' => 'Fecha de Creacion',   'Text' => $data['Fnc_DataDate']->fechaEstandar($data['rowData']['Creacion_fecha'])],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-clock',             'Icon_color' => 10,'Title' => 'Hora de Creacion',    'Text' => $data['rowData']['Creacion_hora']],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-person-fill',       'Icon_color' => 10,'Title' => 'Usuario Creacion',    'Text' => $data['rowData']['Usuario']],
        ];
        $arrData2 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-chat-left-text',    'Icon_color' => 10,'Title' => 'Observaciones',       'Text' => $data['rowData']['Observaciones']],
        ];
        $arrData3 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-box-seam', 'Icon_color' => 10,'Title' => 'Bodega Productos',    'Text' => $Movimiento],
        ];
        $arrData4 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-currency-dollar', 'Icon_color' => 10,'Title' => 'Valor Neto',    'Text' => $data['Fnc_DataNumbers']->Valores($data['rowData']['ValorNeto'], 2)],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-currency-dollar', 'Icon_color' => 10,'Title' => 'IVA',           'Text' => $data['Fnc_DataNumbers']->Valores($data['rowData']['IVA'], 2)],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-currency-dollar', 'Icon_color' => 10,'Title' => 'Valor Total',   'Text' => $data['Fnc_DataNumbers']->Valores($data['rowData']['ValorTotal'], 2)],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-currency-dollar', 'Icon_color' => 10,'Title' => 'Monto Pagado',  'Text' => $data['Fnc_DataNumbers']->Valores($data['rowData']['MontoPagado'], 2)],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-credit-card',     'Icon_color' => 10,'Title' => 'Estado Pago',   'Text' => '<span class="badge-sp1 badge-sp1-'.$data['rowData']['EstadoColor'].'">'.$data['rowData']['EstadoPago'].'</span>'],
        ];

        echo $data['Fnc_WidgetsViews']->tittle_v2('Datos Básicos', 23);
        echo '<div class="row">';
            $OptionsTable['Cols'] = 3;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData1, $OptionsTable);
            $OptionsTable['Cols'] = 1;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData2, $OptionsTable);
        echo '</div>';

        echo $data['Fnc_WidgetsViews']->tittle_v2('Bodegas', 23);
        echo '<div class="row">';
            $OptionsTable['Cols'] = 3;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData3, $OptionsTable);
        echo '</div>';

        echo $data['Fnc_WidgetsViews']->tittle_v2('Montos y Pagos', 23);
        echo '<div class="row">';
            $OptionsTable['Cols'] = 3;
            echo $data['Fnc_WidgetsViews']->table_v4($arrData4, $OptionsTable);
        echo '</div>';

        ?>
    </div>
</div>
