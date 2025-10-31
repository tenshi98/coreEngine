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
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
            <h5 class="card-title">Rutas</h5>
            <div class="clearfix"></div>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Metodo</th>
                            <th>Ruta Web</th>
                            <th>Ruta Controller</th>
                            <th>Descripcion</th>
                            <th>Objetivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        //Verifico si hay datos
                        if(is_array($data['arrRutas'])&&!empty($data['arrRutas'])){
                            //filtro
                            $newData = $data['Fnc_CommonData']->agruparPorClave ($data['arrRutas'], 'Controller' );
                            //Recorro
                            foreach ($newData AS $Controller=>$permisos){
                                //imprimimos la categoría
                                echo '<tr class="table-secondary"><td colspan="5"><strong>'.$Controller.'</strong></td></tr>';
                                //se recorren los datos dentro de la categoría
                                foreach ($permisos AS $ruta){ ?>
                                    <tr>
                                        <td><?php echo $ruta['Metodo']; ?></td>
                                        <td><?php echo $ruta['RutaWeb']; ?></td>
                                        <td><?php echo $ruta['RutaController']; ?></td>
                                        <td><?php echo $ruta['Descripcion']; ?></td>
                                        <td><?php echo $ruta['LevelLimit']; ?></td>
                                    </tr>
                            <?php }
                            }
                        }else{
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