<table class="table table-sm table-hover">
    <thead>
        <tr>
            <th>Tipo</th>
            <th>Nombre</th>
            <th>Ruta Web</th>
            <th>Controlador</th>
            <th>Nivel Acceso</th>
            <th>Estado</th>
            <th width="10">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['arrPermisos'])&&!empty($data['arrPermisos'])){
            //Arreglo con los estados
            $estadoBadge = [
                1 => 'bg-success', // Activo
                2 => 'bg-danger',  // Inactivo
            ];
            //filtro
            $newData = $data['Fnc_CommonData']->agruparPorClave ($data['arrPermisos'], 'PermisosCat' );
            //Recorro
            foreach ($newData AS $Categoria=>$permisos){
                //imprimimos la categoría
                echo '<tr class="table-secondary"><td colspan="7"><strong>'.$Categoria.'</strong></td></tr>';
                //se recorren los datos dentro de la categoría
                foreach ($permisos AS $perm){
                    //Variables
                    $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $perm['idPermisos']);
                    $Entidad     = addslashes($perm['Nombre']); ?>
                    <tr>
                        <td><?php echo $perm['Tipo']; ?></td>
                        <td><?php echo $perm['Nombre']; ?></td>
                        <td><?php echo $perm['RutaWeb']; ?></td>
                        <td><?php echo $perm['RutaController']; ?></td>
                        <td><?php echo $perm['LevelLimit']; ?></td>
                        <td><?php echo '<span class="badge '.$estadoBadge[$perm['idEstado']].'">'.$perm['Estado'].'</span>'; ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" onclick="TDviewBTN('<?php echo $encryptedId; ?>')"                            class="btn btn-primary   btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>
                                <a href="<?php echo $BASE.'/Core/permisos/listado/resumen/'.$encryptedId; ?>"                       class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></a>
                                <button type="button" onclick="TDdelBTN('<?php echo $encryptedId; ?>', '<?php echo $Entidad; ?>')"  class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                <?php
                }
            }
        } ?>
    </tbody>
</table>