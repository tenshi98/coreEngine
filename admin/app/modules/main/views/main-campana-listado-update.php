<table class="table table-sm table-hover table-bordered">
    <thead>
        <tr>
            <th></th>
            <th></th>
            <th colspan="2" class="text-center">Kilos</th>
            <th colspan="2" class="text-center">Valores</th>
            <th colspan="2" class="text-center">Precio</th>
            <th colspan="2" class="text-center">Utilidad</th>
        </tr>
        <tr>
            <th>Fact.Compra.Nro</th>
            <th>Producto</th>

            <th class="text-center">Compras</th>
            <th class="text-center">Ventas</th>

            <th class="text-center">Compras</th>
            <th class="text-center">Ventas</th>

            <th class="text-center">Compra</th>
            <th class="text-center">Venta</th>

            <th class="text-center">x Lote</th>
            <th class="text-center">x KG</th>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['MainViewData']['Data_Analisis'])){
            //Variables
            $Total_Costos     = 0;
            $Total_Beneficios = 0;
            $Total_Margen     = 0;
            $ProdCostos       = $data['Fnc_CommonData']->agruparPorClave ($data['MainViewData']['Data_AnalisisCostos'], 'idCampana' );
            $ProdVenta        = $data['Fnc_CommonData']->agruparPorClave ($data['MainViewData']['Data_AnalisisVentas'], 'idCampana' );
            //Recorro
            foreach($data['MainViewData']['Data_Analisis'] AS $crud){
                //Verifico el monto
                $isNegative = isset($crud['Margen']) && $crud['Margen'] < 0;
                $Icon       = $isNegative ? '<i class="ri-arrow-down-circle-line"></i>' : '<i class="ri-arrow-up-circle-line"></i>';
                $Color      = $isNegative ? 'text-danger' : 'text-success';
                //Variables
                $Producto          = '';
                $Costos_Cantidades = '';
                $Costos_Valores    = '<strong>'.$data['Fnc_DataNumbers']->Valores($crud['Costos'], 0).'</strong>';
                $Ventas_Cantidades = '';
                $Ventas_Valores    = '<strong>'.$data['Fnc_DataNumbers']->Valores($crud['Beneficios'], 0).'</strong>';
                $Precio_Compra     = '';
                $Precio_Venta      = '';
                $Utilidad_Lote     = '<strong><span class="'.$Color.'">'.$Icon.' '.$data['Fnc_DataNumbers']->Valores($crud['Margen'], 0).'</span></strong>';
                $Utilidad_KG       = '';
                $Utilidad          = array();
                //Sumo
                $Total_Costos     = $Total_Costos + $crud['Costos'];
                $Total_Beneficios = $Total_Beneficios + $crud['Beneficios'];
                $Total_Margen     = $Total_Margen + $crud['Margen'];
                //Se recorren los costos
                if(isset($ProdCostos[$crud['idCampana']])){
                    foreach ($ProdCostos[$crud['idCampana']] AS $dataP){
                        //Verifico si existe
                        if(isset($dataP['Producto'])&&$dataP['Producto']!=''){
                            //Calculo
                            if($dataP['Cantidad']!=0){
                                $Promedio = $dataP['Valor']/$dataP['Cantidad'];
                            }else{
                                $Promedio = 0;
                            }
                            //Variables
                            $Producto          .= '<br/>'.$dataP['Producto'];
                            $Costos_Cantidades .= '<br/>'.$data['Fnc_DataNumbers']->Cantidades($dataP['Cantidad'], 0).' '.$dataP['Unimed'];
                            $Costos_Valores    .= '<br/>'.$data['Fnc_DataNumbers']->Valores($dataP['Valor'], 0);
                            $Precio_Compra     .= '<br/>'.$data['Fnc_DataNumbers']->Valores(($Promedio), 0);
                            //Se guardan temporalmente
                            $Utilidad[$dataP['idProducto']]['idProducto']   = $dataP['idProducto'];
                            $Utilidad[$dataP['idProducto']]['Valor']        = $dataP['Valor'];
                            $Utilidad[$dataP['idProducto']]['ValorKG']      = $Promedio;
                        }
                    }
                }
                //Se recorren los costos
                if(isset($ProdVenta[$crud['idCampana']])){
                    foreach ($Utilidad AS $utils){
                        foreach ($ProdVenta[$crud['idCampana']] AS $dataP){
                            //Verifico si existe
                            if(isset($dataP['idProducto'],$utils['idProducto'])&&$dataP['idProducto']!=''&&$utils['idProducto']!=''&&$dataP['idProducto']==$utils['idProducto']){
                                //Calculo
                                if($dataP['Cantidad']!=0){
                                    $Promedio = $dataP['Valor']/$dataP['Cantidad'];
                                }else{
                                    $Promedio = 0;
                                }
                                //Variables
                                $Ventas_Cantidades .= '<br/>'.$data['Fnc_DataNumbers']->Cantidades($dataP['Cantidad'], 0).' '.$dataP['Unimed'];
                                $Ventas_Valores    .= '<br/>'.$data['Fnc_DataNumbers']->Valores($dataP['Valor'], 0);
                                $Precio_Venta      .= '<br/>'.$data['Fnc_DataNumbers']->Valores(($Promedio), 0);
                                //
                                if(isset($utils['Valor'])){
                                    $Utilidad_Lote .= '<br/>'.$data['Fnc_DataNumbers']->Valores(($dataP['Valor']-$utils['Valor']), 0);
                                }else{
                                    $Utilidad_Lote .= '<br/>';
                                }
                                if(isset($utils['ValorKG'])){
                                    $Utilidad_KG .= '<br/>'.$data['Fnc_DataNumbers']->Valores(($Promedio-$utils['ValorKG']), 0);
                                }else{
                                    $Utilidad_KG .= '<br/>';
                                }
                            }
                        }
                    }
                }

                ?>
                <tr>
                    <td><?php echo $crud['Nombre']; ?></td>
                    <td class="text-end"><?php echo $Producto; ?></td>
                    <td class="text-end"><?php echo $Costos_Cantidades; ?></td>
                    <td class="text-end"><?php echo $Ventas_Cantidades; ?></td>
                    <td class="text-end"><?php echo $Costos_Valores; ?></td>
                    <td class="text-end"><?php echo $Ventas_Valores; ?></td>
                    <td class="text-end"><?php echo $Precio_Compra; ?></td>
                    <td class="text-end"><?php echo $Precio_Venta; ?></td>
                    <td class="text-end"><?php echo $Utilidad_Lote; ?></td>
                    <td class="text-end"><?php echo $Utilidad_KG; ?></td>
                </tr>
            <?php } ?>
            <tr>
                <td class="text-end" colspan="2"><strong>Total General</strong></td>
                <td class="text-end"></td>
                <td class="text-end"></td>
                <td class="text-end"><strong><?php echo $data['Fnc_DataNumbers']->Valores($Total_Costos, 0); ?></strong></td>
                <td class="text-end"><strong><?php echo $data['Fnc_DataNumbers']->Valores($Total_Beneficios, 0); ?></strong></td>
                <td class="text-end"></td>
                <td class="text-end"></td>
                <td class="text-end"><strong><?php echo $data['Fnc_DataNumbers']->Valores($Total_Margen, 0); ?></strong></td>
                <td class="text-end"></td>
            </tr>

        <?php } ?>
    </tbody>
</table>