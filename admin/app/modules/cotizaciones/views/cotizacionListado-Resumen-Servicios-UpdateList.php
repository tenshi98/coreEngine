<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th>Servicio</th>
            <th class="text-end">Cantidad</th>
            <th class="text-end">Valor</th>
            <th width="10">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['arrServicios'])&&!empty($data['arrServicios'])){
            //Recorro
            foreach($data['arrServicios'] AS $crud){
                //Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idExistencia']);
                $Entidad     = addslashes($crud['ServicioNombre']); ?>
                <tr>
                    <td><?php echo $crud['ServicioNombre']; ?></td>
                    <td class="text-end"><?php echo $data['Fnc_DataNumbers']->Cantidades($crud['ServicioCantidad'], 2); ?></td>
                    <td class="text-end"><?php echo $data['Fnc_DataNumbers']->Valores($crud['ServicioValor'], 2); ?></td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" onclick="tabServEdit('<?php echo $encryptedId; ?>')"                             class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>
                            <button type="button" onclick="tabServDel( '<?php echo $encryptedId; ?>', '<?php echo $Entidad; ?>')"  class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>