<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th>Item</th>
            <th class="text-end">Cantidad</th>
            <th class="text-end">Valor</th>
            <th width="10">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['arrItems'])&&!empty($data['arrItems'])){
            //Recorro
            foreach($data['arrItems'] AS $crud){
                //Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idExistencia']);
                $Entidad     = addslashes($crud['Item']);  ?>
                <tr>
                    <td><?php echo $crud['Item']; ?></td>
                    <td class="text-end"><?php echo $data['Fnc_DataNumbers']->Cantidades($crud['Number'], 2); ?></td>
                    <td class="text-end"><?php echo $data['Fnc_DataNumbers']->Valores($crud['ValorTotal'], 2); ?></td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" onclick="tabItemEdit('<?php echo $encryptedId; ?>')"                             class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>
                            <button type="button" onclick="tabItemDel( '<?php echo $encryptedId; ?>', '<?php echo $Entidad; ?>')"  class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>