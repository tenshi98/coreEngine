<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4 col-xl-3 col-xxl-2">
        <?php
        $UserIMG = $BASE.'/img/picture-img.jpg';
        ?>
        <img src="<?php echo $UserIMG; ?>" alt="Profile" class="square-rounded-2 square-border-3 w-100">
    </div>
    <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-7">
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
        $arrData1 = [
            ['Icon' => '','Titulo' => 'Creador',          'Texto' => $data['rowData']['UsuarioNombre']],
            ['Icon' => '','Titulo' => 'Tipo Movimiento',  'Texto' => $data['rowData']['TipoMovimiento']],
            ['Icon' => '','Titulo' => 'Fecha',            'Texto' => $data['Fnc_DataDate']->fechaEstandar($data['rowData']['Creacion_fecha'])],
            ['Icon' => '','Titulo' => 'Hora',             'Texto' => $data['rowData']['Creacion_hora']],
            ['Icon' => '','Titulo' => 'Bodegas',          'Texto' => $Movimiento],
            ['Icon' => '','Titulo' => 'Observaciones',    'Texto' => $data['rowData']['Observaciones']],
        ];

        /*****************************************/
        echo '<h5 class="card-title">Datos Básicos</h5>';
        $data['Fnc_WidgetsCommon']->responsiveTable($arrData1, 8);

        /*****************************************/
        //permite la interaccion con la bodega, para generar documentos de ingreso o egreso
        if($data['UserData']["gestionDocumentosUsoBodega"]==2){
            //Verificosi existe
            if(isset($data['rowData']['idFacturacion'])&&$data['rowData']['idFacturacion']!=0){
                switch ($data['rowData']['idTipo']) {
                    case 1:$rRoute = 'gestionDocumentos/compras/listado'; break;//Compra
                    case 2:$rRoute = 'gestionDocumentos/ventas/listado'; break; //Venta
                }
                //texto
                $Texto = $data['rowData']['Documento'].' '.($data['rowData']['N_Doc'] ?? 'nRef '.$data['rowData']['idFacturacion']).'
                    <div class="btn-group" role="group">
                        <a target="new" href="'.$BASE.'/'.$rRoute.'/noPrint/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idFacturacion']).'" class="btn btn-primary btn-sm" ><i class="bi bi-eye"></i> Ver Documento</a>
                    </div>';

                $arrData2 = [
                    ['Icon' => '','Titulo' => 'Documento', 'Texto' => $Texto],
                ];

                echo '<h5 class="card-title">Documento Facturacion</h5>';
                $data['Fnc_WidgetsCommon']->responsiveTable($arrData2, 8);
            }
        }
        ?>
    </div>
</div>
