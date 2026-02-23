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
                    <th>Ruta Web</th>
                    <th>Estado</th>
                    <th>Comparado</th>
                    <th>Existe</th>
                </tr>
            </thead>
            <tbody>
                <?php
                //Variables
                $arrCompare = array();
                //Se parsean los datos
                if(is_array($data['arrModules'])&&!empty($data['arrModules'])){
                    foreach ($data['arrModules'] as $key=>$modules){
                        //Recorro
                        foreach($modules as $crud){
                            if(isset($crud['idMetodo'])&&$crud['idMetodo']!=''){
                                $arrCompare[$crud['RutaWeb']][$crud['RutaController']]['idMetodo']        = $crud['idMetodo'];
                                $arrCompare[$crud['RutaWeb']][$crud['RutaController']]['RutaWeb']         = $crud['RutaWeb'];
                                $arrCompare[$crud['RutaWeb']][$crud['RutaController']]['RutaController']  = $crud['RutaController'];
                                $arrCompare[$crud['RutaWeb']][$crud['RutaController']]['Descripcion']     = $crud['Descripcion'];
                                $arrCompare[$crud['RutaWeb']][$crud['RutaController']]['idLevelLimit']    = $crud['idLevelLimit'];
                                $arrCompare[$crud['RutaWeb']][$crud['RutaController']]['Controller']      = $crud['Controller'];
                            }
                        }
                    }
                }
                //Verifico si hay datos
                if(is_array($data['arrRutas'])&&!empty($data['arrRutas'])){
                    //Recorro
                    foreach($data['arrRutas'] as $crud){
                        //Conteo
                        $CountExist = 0;
                        $CountDif   = 0; ?>
                        <tr>
                            <td><?php echo $crud['RutaWeb'].' ('.$crud['RutaController'].')'; ?></td>
                            <td>
                                <?php
                                //Campos
                                $fields = [
                                    'idMetodo',
                                    'RutaWeb',
                                    'RutaController',
                                    'Descripcion',
                                    'idLevelLimit',
                                    'Controller'
                                ];
                                //Se recorren campos
                                foreach ($fields as $field) {
                                    if (isset($arrCompare[$crud['RutaWeb']][$crud['RutaController']][$field]) && $arrCompare[$crud['RutaWeb']][$crud['RutaController']][$field] != '') {
                                        if ($arrCompare[$crud['RutaWeb']][$crud['RutaController']][$field] != $crud[$field]) {
                                            echo "{$field} es distinto ({$crud[$field]} | {$arrCompare[$crud['RutaWeb']][$crud['RutaController']][$field]})<br/>";
                                        } else {
                                            $CountDif++;
                                        }
                                    } else {
                                        $CountExist++;
                                    }
                                } ?>
                            </td>
                            <td <?php if($CountDif!=6){echo 'class="table-danger"';} ?> ><?php echo $CountDif; ?></td>
                            <td <?php if($CountExist!=0){echo 'class="table-danger"';} ?> ><?php echo $CountExist; ?></td>
                        </tr>
                    <?php } ?>
                <?php } ?>
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

