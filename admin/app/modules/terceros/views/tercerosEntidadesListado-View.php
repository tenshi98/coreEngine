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
        <?php if($data['UserData']["entidadesListadoUsoPlanes"]==2){ ?>     <li class="nav-item flex-fill" role="presentation"><button class="nav-link w-100" id="view_tab_2" data-bs-toggle="tab" data-bs-target="#tab_id_2" type="button" role="tab" aria-controls="tab_id_2" aria-selected="false" tabindex="-1"><i class="bi bi-currency-dollar"></i> Planes</button></li><?php } ?>
        <?php if($data['UserData']["entidadesListadoUsoUsuarios"]==2){ ?>   <li class="nav-item flex-fill" role="presentation"><button class="nav-link w-100" id="view_tab_3" data-bs-toggle="tab" data-bs-target="#tab_id_3" type="button" role="tab" aria-controls="tab_id_3" aria-selected="false" tabindex="-1"><i class="bi bi-person"></i> Usuarios</button></li><?php } ?>
    </ul>
    <div class="tab-content pt-2" id="tabId_560_Content">
        <div class="tab-pane fade active show" id="tab_id_1" role="tabpanel" aria-labelledby="view_tab_1">
            <?php require_once('tercerosEntidadesListado-Resumen-Update.php'); ?>
        </div>
        <?php if($data['UserData']["entidadesListadoUsoPlanes"]==2){ ?>
            <div class="tab-pane fade" id="tab_id_2" role="tabpanel" aria-labelledby="view_tab_2">
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <h5 class="card-title">Planes</h5>
                        <div class="clearfix"></div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <tbody>
                                    <?php
                                    //Verifico si hay datos
                                    if(is_array($data['arrPlanes'])&&!empty($data['arrPlanes'])){
                                        //Recorro
                                        foreach($data['arrPlanes'] AS $crud){ ?>
                                            <tr>
                                                <td><?php echo $crud['Servicio']; ?></td>
                                                <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['Fecha']); ?></td>
                                                <td><?php echo $data['Fnc_DataNumbers']->Valores($crud['Monto'], 0); ?></td>
                                                <td><?php echo '<span class="badge '.$crud['EstadoColor'].'">'.$crud['Estado'].'</span>'; ?></td>
                                            </tr>
                                        <?php } ?>
                                    <?php }else{
                                        echo '<tr><td colspan="4">No se encontraron entradas</td></tr>';
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
        <?php if($data['UserData']["entidadesListadoUsoUsuarios"]==2){ ?>
            <div class="tab-pane fade" id="tab_id_3" role="tabpanel" aria-labelledby="view_tab_3">
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <h5 class="card-title">Usuarios</h5>
                        <div class="clearfix"></div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <tbody>
                                    <?php
                                    //Verifico si hay datos
                                    if(is_array($data['arrUsuarios'])&&!empty($data['arrUsuarios'])){
                                        //Recorro
                                        foreach($data['arrUsuarios'] AS $crud){ ?>
                                            <tr>
                                                <td><?php echo $crud['email']; ?></td>
                                                <td><?php echo $crud['Nombre']; ?></td>
                                                <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['Ultimo_acceso']); ?></td>
                                                <td><?php echo '<span class="badge '.$crud['EstadoColor'].'">'.$crud['Estado'].'</span>'; ?></td>
                                            </tr>
                                        <?php } ?>
                                    <?php }else{
                                        echo '<tr><td colspan="4">No se encontraron entradas</td></tr>';
                                    } ?>
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