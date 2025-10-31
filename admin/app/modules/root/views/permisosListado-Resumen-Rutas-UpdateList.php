<table class="table table-sm table-hover">
    <thead>
        <tr>
            <th>Metodo</th>
            <th>Ruta Web</th>
            <th>Ruta Controller</th>
            <th>Descripcion</th>
            <th>Objetivo</th>
            <th width="10">Acciones</th>
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
                echo '<tr class="table-secondary"><td colspan="6"><strong>'.$Controller.'</strong></td></tr>';
                //se recorren los datos dentro de la categoría
                foreach ($permisos AS $ruta){
                    //Variables
                    $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $ruta['idRutas']); ?>
                    <tr>
                        <td><?php echo $ruta['Metodo']; ?></td>
                        <td><?php echo $ruta['RutaWeb']; ?></td>
                        <td><?php echo $ruta['RutaController']; ?></td>
                        <td><?php echo $ruta['Descripcion']; ?></td>
                        <td><?php echo $ruta['LevelLimit']; ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" onclick="TDviewBTN('<?php echo $encryptedId; ?>')"          class="btn btn-primary   btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>
                                <button type="button" onclick="TDeditBTN('<?php echo $encryptedId; ?>')"          class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>
                                <button type="button" onclick="TDdelBTN( '<?php echo $encryptedId; ?>', 'Ruta')"  class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
            <?php }
            }
        } ?>
    </tbody>
</table>