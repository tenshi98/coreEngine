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
        /*****************************************/
        //Se verifica movimiento
        switch ($data['rowData']['idEstadoIngreso']) {
            case 1: $Movimiento = $data['rowData']['BodegaIngreso']; break;                                        //Ingreso
            case 2: $Movimiento = $data['rowData']['BodegaEgreso']; break;                                         //Egreso
            case 3: $Movimiento = $data['rowData']['BodegaEgreso'].' a '.$data['rowData']['BodegaIngreso']; break; //Traspaso
        }

        /*****************************************/
        //datos
        $Theme_color = 'summary-theme-color-2';
        $arrData_1 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-hash',           'Icon_color' => 10, 'Title' => 'nRef',            'Text' => '#'.$data['rowData']['idMovimiento'],],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-person-fill',    'Icon_color' => 10, 'Title' => 'Creador',         'Text' => $data['rowData']['UsuarioNombre'],],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-arrow-repeat',   'Icon_color' => 10, 'Title' => 'Tipo Movimiento', 'Text' => $data['rowData']['TipoMovimiento'],],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-calendar-event', 'Icon_color' => 10, 'Title' => 'Fecha',           'Text' => $data['Fnc_DataDate']->fechaEstandar($data['rowData']['Creacion_fecha']),],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-alarm',          'Icon_color' => 10, 'Title' => 'Hora',            'Text' => $data['rowData']['Creacion_hora'],],
        ];
        $arrData_2 = [
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-box',            'Icon_color' => 10, 'Title' => 'Bodegas',         'Text' => $Movimiento,],
            ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-chat-left-text', 'Icon_color' => 10, 'Title' => 'Observaciones',   'Text' => $data['rowData']['Observaciones'],],
        ];

        echo $data['Fnc_WidgetsViews']->tittle_v2('Datos Básicos', 23);
        echo '<div class="row">';
            $Options['Cols'] = 3;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData_1, $Options);
            $Options['Cols'] = 1;
            echo $data['Fnc_WidgetsViews']->table_v6($arrData_2, $Options);
        echo '</div>';



        /*****************************************/
        //permite la interaccion con la bodega, para generar documentos de ingreso o egreso y Verifico si existe
        if($data['UserData']["gestionDocumentosUsoBodega"]==2&&isset($data['rowData']['idFacturacion'])&&$data['rowData']['idFacturacion']!=0){

            /*****************************************/
            // Se obtiene el ID
            $DataIDFact = $data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idFacturacion']);

            //Verifico el tipo de movimiento
            switch ($data['rowData']['idTipo']) {
                case 1:$rRoute = 'gestionDocumentos/compras/listado'; break;//Compra
                case 2:$rRoute = 'gestionDocumentos/ventas/listado'; break; //Venta
            }
            //texto
            $Texto = $data['rowData']['Documento'].' '.($data['rowData']['N_Doc'] ?? 'nRef #'.$data['rowData']['idFacturacion']).'
                <div class="btn-group" role="group">
                    <a target="new" href="'.$BASE.'/'.$rRoute.'/noPrint/'.$DataIDFact['data'].'" class="btn btn-primary btn-sm"><i class="bi bi-eye"></i> Ver Documento</a>
                </div>';

            /*****************************************/
            // Se imprime
            $arrData_3 = [
                ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-bell', 'Icon_color' => 10, 'Title' => 'Documento', 'Text' => $Texto,],
            ];
            echo '<br>';
            echo $data['Fnc_WidgetsViews']->tittle_v2('Documento Facturación', 23);
            echo '<div class="row">';
                $Options['Cols'] = 2;
                echo $data['Fnc_WidgetsViews']->table_v6($arrData_3, $Options);
            echo '</div>';


        }
        ?>
    </div>
</div>
