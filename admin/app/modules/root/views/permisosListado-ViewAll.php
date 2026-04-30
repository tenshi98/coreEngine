<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
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
            <h5 class="box-title text-color-red-dark">Rutas</h5>
            <div class="clearfix"></div>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Metodo</th>
                            <th scope="col">Ruta</th>
                            <th scope="col">Descripcion</th>
                            <th scope="col">Objetivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        //Verifico si hay datos
                        if(is_array($data['arrRutas'])&&!empty($data['arrRutas'])){
                            //filtro
                            $newData = $data['Fnc_CommonData']->agruparPorClave ($data['arrRutas'], 'Controller' );
                            //Recorro
                            foreach ($newData as $Controller=>$permisos){
                                //imprimimos la categoría
                                echo '<tr class="table-secondary"><td colspan="5"><strong>'.$Controller.'</strong></td></tr>';
                                //se recorren los datos dentro de la categoría
                                foreach ($permisos as $ruta){ ?>
                                    <tr>
                                        <td><?php echo $ruta['Metodo']; ?></td>
                                        <td><?php echo '<strong>Ruta: </strong>'.$ruta['RutaWeb'].'<br><strong>Controlador: </strong>'.$ruta['RutaController']; ?></td>
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
