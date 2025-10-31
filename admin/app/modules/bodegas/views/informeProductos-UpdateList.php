<section class="section" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $data['TableTitle']; ?></h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover datatable">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <?php
                                    //Verifico si hay datos
                                    if(is_array($data['arrBodegas'])){
                                        //Recorro
                                        foreach($data['arrBodegas'] AS $bod){
                                            echo '<th>'.$bod['Nombre'].'</th>';
                                        }
                                    } ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                //Verifico si hay datos
                                if(is_array($data['arrStocks'])&&!empty($data['arrStocks'])){
                                    //Recorro
                                    foreach($data['arrStocks'] AS $stock){ ?>
                                        <tr>
                                            <td><?php echo $stock['Producto']; ?></td>
                                            <?php
                                            //Verifico si hay datos
                                            if(is_array($data['arrBodegas'])){
                                                //Recorro
                                                foreach($data['arrBodegas'] AS $bod){
                                                    echo '
                                                    <td>
                                                        '.$data['Fnc_DataNumbers']->Cantidades($stock['Cantidad_idBodegas_'.$bod['idBodegas']], 2).' '.$stock['UniMed'].'
                                                        <div class="btn-group" role="group">
                                                            <button type="button" onclick="listTableDataView(\''.$data['Fnc_Codification']->encryptDecrypt('encrypt', $stock['idProducto']).'\',\''.$data['Fnc_Codification']->encryptDecrypt('encrypt', $bod['idBodegas']).'\')" class="btn btn-primary btn-sm tooltiplink" data-title="Ver Movimientos"><i class="bi bi-eye"></i></button>
                                                        </div>
                                                    </td>';
                                                }
                                            } ?>
                                        </tr>
                                    <?php } ?>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="clearfix"></div>
<div class="d-grid gap-2 d-md-flex justify-content-md-end">
    <a href="<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/listAll'; ?>" class="btn btn-danger float-end"><i class="bi bi-arrow-left-circle"></i> Volver</a>
</div>
<div class="clearfix"></div>
