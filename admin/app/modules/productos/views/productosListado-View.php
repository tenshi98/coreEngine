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
        <?php if($data['UserData']["productosListadoVerDocumentos"]==2){ ?><li class="nav-item flex-fill" role="presentation"><button class="nav-link w-100" id="view_tab_4" data-bs-toggle="tab" data-bs-target="#tab_id_4" type="button" role="tab" aria-controls="tab_id_4" aria-selected="false" tabindex="-1"><i class="bi bi-file-text"></i> Documentos</button></li><?php } ?>
        <li class="nav-item flex-fill" role="presentation"><button class="nav-link w-100" id="view_tab_5" data-bs-toggle="tab" data-bs-target="#tab_id_5" type="button" role="tab" aria-controls="tab_id_5" aria-selected="false" tabindex="-1"><i class="bi bi-chat-dots"></i> Observaciones</button></li>
    </ul>
    <div class="tab-content pt-2" id="tabId_560_Content">
        <div class="tab-pane fade active show" id="tab_id_1" role="tabpanel" aria-labelledby="view_tab_1">
            <?php require_once('productosListado-Resumen-Update.php'); ?>
        </div>
        <?php if($data['UserData']["productosListadoVerDocumentos"]==2){ ?>
            <div class="tab-pane fade" id="tab_id_4" role="tabpanel" aria-labelledby="view_tab_4">
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <h5 class="card-title">Documentos</h5>
                        <div class="clearfix"></div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <tbody>
                                    <?php
                                    //Verifico si hay datos
                                    if(is_array($data['arrDocumentos'])&&!empty($data['arrDocumentos'])){
                                        //Recorro
                                        foreach($data['arrDocumentos'] AS $crud){ ?>
                                            <tr>
                                                <td><?php echo $crud['Nombre']; ?></td>
                                                <td><?php echo $crud['FVencimiento']; ?></td>
                                                <td><?php echo $crud['NombreArchivo']; ?></td>
                                            </tr>
                                        <?php } ?>
                                    <?php }else{
                                        echo '<tr><td colspan="3">No se encontraron entradas</td></tr>';
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
        <div class="tab-pane fade" id="tab_id_5" role="tabpanel" aria-labelledby="view_tab_5">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                    <h5 class="card-title">Observaciones</h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <tbody>
                                <?php
                                //Verifico si hay datos
                                if(is_array($data['arrObservaciones'])&&!empty($data['arrObservaciones'])){
                                    //Recorro
                                    foreach($data['arrObservaciones'] AS $crud){ ?>
                                        <tr>
                                            <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['FechaCreacion']); ?></td>
                                            <td><?php echo $crud['Observacion']; ?></td>
                                        </tr>
                                    <?php } ?>
                                <?php }else{
                                    echo '<tr><td colspan="2">No se encontraron entradas</td></tr>';
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
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