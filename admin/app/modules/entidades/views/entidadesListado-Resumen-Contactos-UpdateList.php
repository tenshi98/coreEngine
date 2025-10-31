<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Email</th>
            <th>Celular</th>
            <th>Telefono</th>
            <th width="10">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['arrContactos'])&&!empty($data['arrContactos'])){
            //Recorro
            foreach($data['arrContactos'] AS $crud){
                //Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idContacto']);
                $Entidad     = addslashes($crud['ApellidoPat'].' '.$crud['ApellidoMat'].' '.$crud['Nombre']); ?>
                <tr>
                    <td><?php echo $crud['ApellidoPat'].' '.$crud['ApellidoMat'].' '.$crud['Nombre']; ?></td>
                    <td><?php echo $crud['Email']; ?></td>
                    <td><?php echo $data['Fnc_DataNumbers']->formatPhone($crud['Fono1']); ?></td>
                    <td><?php echo $data['Fnc_DataNumbers']->formatPhone($crud['Fono2']); ?></td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" onclick="tabContactosView('<?php echo $encryptedId; ?>')"                             class="btn btn-primary   btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>
                            <button type="button" onclick="tabContactosEdit('<?php echo $encryptedId; ?>')"                             class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>
                            <button type="button" onclick="tabContactosDel( '<?php echo $encryptedId; ?>', '<?php echo $Entidad; ?>')"  class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>