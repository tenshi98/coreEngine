<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th>Fecha Creacion</th>
            <th>Observacion</th>
            <th width="10">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['arrObservaciones'])&&!empty($data['arrObservaciones'])){
            //Recorro
            foreach($data['arrObservaciones'] AS $crud){
                //Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idObservaciones']); ?>
                <tr>
                    <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['FechaCreacion']); ?></td>
                    <td><?php echo $crud['Observacion']; ?></td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" onclick="tabObsView('<?php echo $encryptedId; ?>')"                class="btn btn-primary   btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>
                            <button type="button" onclick="tabObsEdit('<?php echo $encryptedId; ?>')"                class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>
                            <button type="button" onclick="tabObsDel( '<?php echo $encryptedId; ?>', 'Observacion')" class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>