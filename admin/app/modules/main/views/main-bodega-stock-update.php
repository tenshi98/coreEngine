<?php
//Verifico si hay datos
if(is_array($data['MainViewData']['Data_arrBodegas'])){
    //Recorro
    foreach($data['MainViewData']['Data_arrBodegas'] AS $bod){ ?>
        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
            <div class="card">
                <div class="card-body p-4">
                    <div class="infographicBox">
                        <i class="bi bi-box-seam bg-info"></i>
                        <span class="headline">Productos con bajo Stock</span>
                        <span class="value"><span class="timer"><?php echo $bod['Nombre']; ?></span></span>
                    </div>
                    <div class="clearfix"></div>
                    <div class="table-responsive" id="listTableData">
                        <table class="table table-sm table-hover datatable">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-end">Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                //Verifico si hay datos
                                if(is_array($data['MainViewData']['Data_arrStocks'])&&!empty($data['MainViewData']['Data_arrStocks'])){
                                    //Recorro
                                    foreach($data['MainViewData']['Data_arrStocks'] AS $stock){
                                        //Se imprimen solo los productos con bajo stocks
                                        if(isset($stock['Cantidad_idBodegas_'.$bod['idBodegas']], $stock['ProductoStock'])&&$stock['Cantidad_idBodegas_'.$bod['idBodegas']]<=$stock['ProductoStock']){
                                            echo '
                                            <tr>
                                                <td>'.$stock['Producto'].' (Mín '.$data['Fnc_DataNumbers']->Cantidades($stock['ProductoStock'], 2).')</td>
                                                <td class="text-end">'.$data['Fnc_DataNumbers']->Cantidades($stock['Cantidad_idBodegas_'.$bod['idBodegas']], 2).' '.$stock['UniMed'].'</td>
                                            </tr>';
                                        }
                                    }
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
<?php } ?>
