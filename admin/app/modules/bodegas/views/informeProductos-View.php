<div class="modal-header">
    <?php
    switch ($data['UserData']["sistemaModalSubtitle"]) {
        case 1:
            echo '
            <h5 class="modal-title">
                <i class="bi bi-card-checklist"></i> Ver Movimientos de '.$data['rowProducto']['Nombre'].' en '.$data['rowBodega']['Nombre'].'
            </h5>';
            break;
        case 2:
            echo '
            <h5 class="modal-title modal-subtitle">
                <div class="icon"><i class="bi bi-card-checklist"></i></div>
                Ver Movimientos<br>
                <small>Permite ver los movimientos de '.$data['rowProducto']['Nombre'].' en '.$data['rowBodega']['Nombre'].'</small>
            </h5>';
            break;
    } ?>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <?php
                            //permite la interaccion con la bodega, para generar documentos de ingreso o egreso
                            if($data['UserData']["gestionDocumentosUsoBodega"]==2){
                                echo '<th>Documento</th>';
                            } ?>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Tipo Movimiento</th>
                            <th>Ingreso</th>
                            <th>Egreso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        //Verifico si hay datos
                        if(is_array($data['arrStocks'])&&!empty($data['arrStocks'])){
                            //Recorro
                            foreach($data['arrStocks'] AS $crud){ ?>
                                <tr>
                                    <?php
                                    //permite la interaccion con la bodega, para generar documentos de ingreso o egreso
                                    if($data['UserData']["gestionDocumentosUsoBodega"]==2){
                                        //Si existe documento relacionado
                                        if(isset($crud['idTipo'])&&$crud['idTipo']!=''){
                                            switch ($crud['idTipo']) {
                                                case 1:$rRoute = 'gestionDocumentos/compras/listado'; break;//Compra
                                                case 2:$rRoute = 'gestionDocumentos/ventas/listado'; break; //Venta
                                            }
                                            echo '
                                            <td>
                                                '.$crud['Documento'].' '.($crud['N_Doc'] ?? 'nRef '.$crud['idFacturacion']).'
                                                <div class="btn-group" role="group">
                                                    <a target="new" href="'.$BASE.'/'.$rRoute.'/noPrint/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idFacturacion']).'" class="btn btn-primary btn-sm" ><i class="bi bi-eye"></i> Ver Documento</a>
                                                </div>
                                            </td>';
                                        }else{
                                            echo'<td>'.$crud['Observaciones'].'</td>';
                                        }
                                    } ?>
                                    <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['Creacion_fecha']); ?></td>
                                    <td><?php echo $crud['Creacion_hora']; ?></td>
                                    <td><?php echo $crud['TipoMov']; ?></td>
                                    <?php
                                    switch ($crud['idEstadoIngreso']) {
                                        /*******************************/
                                        case 1:
                                            echo '
                                                <td>'.$data['Fnc_DataNumbers']->Cantidades($crud['Number'], 2).' '.$crud['UniMed'].'</td>
                                                <td></td>
                                            ';
                                            break;
                                        /*******************************/
                                        case 2:
                                            echo '
                                                <td></td>
                                                <td>'.$data['Fnc_DataNumbers']->Cantidades($crud['Number'], 2).' '.$crud['UniMed'].'</td>
                                            ';
                                            break;
                                    }
                                    ?>
                                </tr>
                            <?php } ?>
                        <?php }else{
                            echo '<tr><td colspan="5">No se encontraron entradas</td></tr>';
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
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