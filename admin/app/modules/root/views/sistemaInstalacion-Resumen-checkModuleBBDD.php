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
    <div class="table-responsive">
        <table class="table table-sm table-hover datatable">
            <thead>
                <tr>
                    <th scope="col">N°</th>
                    <th scope="col">Ruta Web</th>
                    <th scope="col">Ruta Controller</th>
                    <th scope="col">Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php
                //Variables
                $arrCompare = array();
                $Contador   = 1;
                //Se parsean los datos
                if(is_array($data['arrModules'])&&!empty($data['arrModules'])){
                    foreach ($data['arrModules'] as $key=>$modules){
                        //Recorro
                        foreach($modules as $crud){
                            if(isset($crud['idMetodo'])&&$crud['idMetodo']!=''){
                                $arrCompare[$crud['RutaWeb']][$crud['RutaController']]['RutaController']  = $crud['RutaController'];
                            }
                        }
                    }
                }
                //Verifico si hay datos en la base de datos
                if(is_array($data['arrRutas'])&&!empty($data['arrRutas'])){
                    //Recorro
                    foreach($data['arrRutas'] as $crud){
                        //Verifico si existe
                        $estado = isset($arrCompare[$crud['RutaWeb']][$crud['RutaController']]['RutaController']) ? '<span class="badge-sp1 badge-sp1-bg-success">Encontrado</span>' : '<span class="badge-sp1 badge-sp1-bg-danger">No Encontrado</span>';
                        //Imprimo
                        echo '
                        <tr>
                            <td>'.$Contador.'</td>
                            <td>'.$crud['RutaWeb'].'</td>
                            <td>'.$crud['RutaController'].'</td>
                            <td>'.$estado.'</td>
                        </tr>';
                        $Contador++;
                    }
                }
                ?>
            </tbody>
        </table>
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

