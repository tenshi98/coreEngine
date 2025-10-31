<div class="modal-header">
    <?php
    switch ($data['UserData']["sistemaModalSubtitle"]) {
        case 1:
            echo '
            <h5 class="modal-title">
                <i class="bi bi-card-checklist"></i> Ver Datos
            </h5>';
            break;
        case 2:
            echo '
            <h5 class="modal-title modal-subtitle">
                <div class="icon"><i class="bi bi-card-checklist"></i></div>
                Ver Datos<br>
                <small>Permite visualizar los datos de un elemento existente</small>
            </h5>';
            break;
    } ?>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">

    <ul class="nav nav-tabs nav-tabs-bordered d-grid d-md-flex justify-content-md-between">
        <li class="nav-item flex-fill" role="presentation"><button class="nav-link w-100 active" id="view_tab_1" data-bs-toggle="tab" data-bs-target="#tab_id_1" type="button" role="tab" aria-controls="tab_id_1" aria-selected="true"><i class="bi bi-card-list"></i> Datos Básicos</button></li>
        <?php if(is_array($data['arrCostos'])&&!empty($data['arrCostos'])){ ?>      <li class="nav-item flex-fill" role="presentation"><button class="nav-link w-100" id="view_tab_2" data-bs-toggle="tab" data-bs-target="#tab_id_2" type="button" role="tab" aria-controls="tab_id_2" aria-selected="false" tabindex="-1"><i class="bi bi-bounding-box"></i> Costos</button></li><?php } ?>
        <?php if(is_array($data['arrPartidas'])&&!empty($data['arrPartidas'])){ ?>  <li class="nav-item flex-fill" role="presentation"><button class="nav-link w-100" id="view_tab_3" data-bs-toggle="tab" data-bs-target="#tab_id_3" type="button" role="tab" aria-controls="tab_id_3" aria-selected="false" tabindex="-1"><i class="bi bi-box-seam"></i> Partidas</button></li><?php } ?>
        <?php if(is_array($data['arrPerdidas'])&&!empty($data['arrPerdidas'])){ ?>  <li class="nav-item flex-fill" role="presentation"><button class="nav-link w-100" id="view_tab_4" data-bs-toggle="tab" data-bs-target="#tab_id_4" type="button" role="tab" aria-controls="tab_id_4" aria-selected="false" tabindex="-1"><i class="bi bi-currency-dollar"></i> Perdidas</button></li><?php } ?>
        <li class="flex-grow-1">
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a target="new" href="<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/noPrint/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCampana']); ?>" class="btn btn-primary btn-sm" ><i class="bi bi-eye"></i> Ver Documento</a>
                <a target="new" href="<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/print/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $data['rowData']['idCampana']); ?>" class="btn btn-secondary btn-sm" ><i class="bi bi-printer"></i> Imprimir</a>
            </div>
        </li>
    </ul>

    <div class="tab-content pt-2" id="tabId_560_Content">
        <div class="tab-pane fade active show" id="tab_id_1" role="tabpanel" aria-labelledby="view_tab_1">
            <?php require_once('gestionCampanas-Resumen-Update.php'); ?>
        </div>
        <?php if(is_array($data['arrCostos'])&&!empty($data['arrCostos'])){ ?>
            <div class="tab-pane fade" id="tab_id_2" role="tabpanel" aria-labelledby="view_tab_2">
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <h5 class="card-title">Costos</h5>
                        <div class="clearfix"></div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Item</th>
                                        <th>Facturacion</th>
                                        <th class="text-end">Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    //Totales
                                    $TotalCostos = 0;
                                    //Recorro
                                    foreach($data['arrCostos'] AS $crud){
                                        $TotalCostos = $TotalCostos + $crud['Costos']; ?>
                                        <tr>
                                            <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['Fecha']); ?></td>
                                            <td><?php echo $crud['Item']; ?></td>
                                            <td>
                                                <?php
                                                if(isset($crud['idFacturacion'])&&$crud['idFacturacion']!=0){
                                                    echo $crud['DocumentoNombre'].' '.($crud['DocumentoN_Doc'] ?? 'nRef '.$crud['idFacturacion']);
                                                } ?>
                                            </td>
                                            <td class="text-end"><?php echo $data['Fnc_DataNumbers']->Valores($crud['Costos'], 2); ?></td>
                                        </tr>
                                    <?php } ?>
                                    <tr>
                                        <td class="text-end" colspan="3"><strong>Totales</strong></td>
                                        <td class="text-end"><?php echo $data['Fnc_DataNumbers']->Valores($TotalCostos, 2); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
        <?php if(is_array($data['arrPartidas'])&&!empty($data['arrPartidas'])){ ?>
            <div class="tab-pane fade" id="tab_id_3" role="tabpanel" aria-labelledby="view_tab_3">
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <h5 class="card-title">Partidas</h5>
                        <div class="clearfix"></div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Ubicacion</th>
                                        <th>Entidad</th>
                                        <th>Estado</th>
                                        <th>Producto</th>
                                        <th class="text-end" width="120">Cantidad</th>
                                        <th class="text-end" width="120">Valor</th>
                                        <th>Facturacion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    //Variables
                                    $TotalValor    = 0;
                                    //filtro
                                    $newData  = $data['Fnc_CommonData']->agruparPorClave ($data['arrPartidas'], 'Fecha' );
                                    $ProdData = $data['Fnc_CommonData']->agruparPorClave ($data['arrPartidasProd'], 'idExistencia' );
                                    //Recorro
                                    foreach ($newData AS $Fecha=>$datos){
                                        //imprimimos la categoría
                                        echo '<tr class="table-secondary"><td colspan="7"><strong>Partida del '.$data['Fnc_DataDate']->fechaEstandar($Fecha).'</strong></td></tr>';
                                        //se recorren los datos dentro de la categoría
                                        foreach ($datos AS $crud){
                                            //Se obtiene el nombre o la razón social
                                            $Entidad  = '';
                                            $Entidad .= !empty($crud['EntidadNick'])
                                                        ? $crud['EntidadNick'].'<br>'
                                                        : '';
                                            $Entidad .= !empty($crud['EntidadNombre'])
                                                        ? $crud['EntidadApellido'].' '.$crud['EntidadNombre']
                                                        : $crud['EntidadRazonSocial'];
                                            $P_Producto   = '';
                                            $P_Cantidad   = '';
                                            $P_Beneficios = '';
                                            foreach ($ProdData[$crud['idExistencia']] AS $dataP){
                                                $P_Producto   .= $dataP['Producto'].'<br/>';
                                                $P_Cantidad   .= $data['Fnc_DataNumbers']->Cantidades($dataP['Cantidad'], 2).' '.$dataP['Unimed'].'<br/>';
                                                $P_Beneficios .= $data['Fnc_DataNumbers']->Valores($dataP['Beneficios'], 2).'<br/>';
                                                //Variables
                                                $TotalValor    = $TotalValor + $dataP['Beneficios'];
                                            } ?>
                                            <tr>
                                                <td><?php echo $crud['EntidadSector'].'<br>'.$crud['EntidadDireccion']; ?></td>
                                                <td><?php echo $Entidad; ?></td>
                                                <td><?php echo $crud['EstadoPartida']; ?></td>
                                                <td><?php echo $P_Producto; ?></td>
                                                <td class="text-end"><?php echo $P_Cantidad; ?></td>
                                                <td class="text-end"><?php echo $P_Beneficios; ?></td>
                                                <td>
                                                    <?php
                                                    if(isset($crud['idFacturacion'])&&$crud['idFacturacion']!=0){
                                                        echo $crud['DocumentoNombre'].' '.($crud['DocumentoN_Doc'] ?? 'nRef '.$crud['idFacturacion']);
                                                    } ?>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td class="text-end" colspan="4"><strong>Totales</strong></td>
                                        <td class="text-end"></td>
                                        <td class="text-end"><?php echo $data['Fnc_DataNumbers']->Valores($TotalValor, 2); ?></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
        <?php if(is_array($data['arrPerdidas'])&&!empty($data['arrPerdidas'])){ ?>
            <div class="tab-pane fade" id="tab_id_4" role="tabpanel" aria-labelledby="view_tab_4">
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <h5 class="card-title">Perdidas</h5>
                        <div class="clearfix"></div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Item</th>
                                        <th>Mov Bodega</th>
                                        <th class="text-end">Cantidad</th>
                                        <th class="text-end">Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    //Totales
                                    $TotalCantidad = 0;
                                    $TotalPerdidas = 0;
                                    //Recorro
                                    foreach($data['arrPerdidas'] AS $crud){
                                        $TotalCantidad = $TotalCantidad + $crud['Cantidad'];
                                        $TotalPerdidas = $TotalPerdidas + $crud['Perdidas'];  ?>
                                        <tr>
                                            <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['Fecha']); ?></td>
                                            <td><?php echo $crud['Item']; ?></td>
                                            <td>
                                                <?php if(isset($crud['idMovimiento'])&&$crud['idMovimiento']!=0){
                                                    echo 'Mov Prod '.$crud['Producto'].' nRef '.$crud['idMovimiento'];
                                                } ?>
                                            </td>
                                            <td class="text-end"><?php echo $data['Fnc_DataNumbers']->Cantidades($crud['Cantidad'], 2); ?></td>
                                            <td class="text-end"><?php echo $data['Fnc_DataNumbers']->Valores($crud['Perdidas'], 2); ?></td>
                                        </tr>
                                    <?php } ?>
                                    <tr>
                                        <td class="text-end" colspan="3"><strong>Totales</strong></td>
                                        <td class="text-end"><?php echo $data['Fnc_DataNumbers']->Cantidades($TotalCantidad, 2); ?></td>
                                        <td class="text-end"><?php echo $data['Fnc_DataNumbers']->Valores($TotalPerdidas, 2); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<?php
if($data['UserData']["sistemaModalCloseBTN"]==2){
    echo '
    <div class="modal-footer">
        <div class="d-grid gap-2 d-md-flex justify-content-md-end w-100">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bx bi-x-circle"></i> Cerrar</button>
        </div>
    </div>';
}else{
    echo '<style>.modal-body {max-height: 80vh;}</style>';
} ?>
