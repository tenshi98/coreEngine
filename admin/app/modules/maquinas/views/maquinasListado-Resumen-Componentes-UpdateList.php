<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th>Nombre</th>
            <th width="200">Fecha Vencimiento</th>
            <th width="10">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['arrDocumentos'])&&!empty($data['arrDocumentos'])){
            //Recorro
            foreach($data['arrDocumentos'] AS $crud){
                //Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idDocumentos']);
                $Entidad     = addslashes($crud['Nombre']); ?>
                <tr>
                    <td><?php echo $crud['Nombre']; ?></td>
                    <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['FVencimiento']); ?></td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" onclick="tabDocumentosView('<?php echo $encryptedId; ?>')"                             class="btn btn-primary   btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>
                            <button type="button" onclick="tabDocumentosEdit('<?php echo $encryptedId; ?>')"                             class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>
                            <button type="button" onclick="tabDocumentosDel( '<?php echo $encryptedId; ?>', '<?php echo $Entidad; ?>')"  class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>