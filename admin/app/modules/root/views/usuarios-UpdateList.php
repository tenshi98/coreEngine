<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th>Email</th>
            <th>Nombre</th>
            <th>Ultimo Acceso</th>
            <th>Estado</th>
            <th width="10">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['arrList'])&&!empty($data['arrList'])){
            //Recorro
            foreach($data['arrList'] AS $crud){
                //Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idUsuario']);
                $Entidad     = addslashes($crud['Nombre']); ?>
                <tr>
                    <td><?php echo $crud['email']; ?></td>
                    <td><?php echo $crud['Nombre']; ?></td>
                    <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['Ultimo_acceso']); ?></td>
                    <td><?php echo '<span class="badge '.$crud['EstadoColor'].'">'.$crud['Estado'].'</span>'; ?></td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" onclick="TDviewBTN('<?php echo $encryptedId; ?>')"                            class="btn btn-primary   btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>
                            <a href="<?php echo $BASE.'/Core/administracion/usuarios/resumen/'.$encryptedId; ?>"                class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></a>
                            <button type="button" onclick="TDdelBTN('<?php echo $encryptedId; ?>', '<?php echo $Entidad; ?>')"  class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>