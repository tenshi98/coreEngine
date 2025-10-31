<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Documento</th>
            <th class="text-end">Valor</th>
            <?php if(isset($data['rowData']['idEstadoPago'])&&$data['rowData']['idEstadoPago']==1){ ?>
                <th width="10">Acciones</th>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['arrGuias'])&&!empty($data['arrGuias'])){
            //Recorro
            foreach($data['arrGuias'] AS $crud){ ?>
                <tr>
                    <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['Creacion_fecha']); ?></td>
                    <td><?php echo $crud['Documento'].' '.($crud['N_Doc'] ?? 'nRef '.$crud['idFacturacionRel']); ?></td>
                    <td class="text-end"><?php echo $data['Fnc_DataNumbers']->Valores($crud['ValorTotal'], 2); ?></td>
                    <?php if(isset($data['rowData']['idEstadoPago'])&&$data['rowData']['idEstadoPago']==1){ ?>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" onclick="tabGuiaDel('<?php echo $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idExistencia']); ?>', '<?php echo $crud['Documento'].' '.($crud['N_Doc'] ?? 'nRef '.$crud['idFacturacionRel']); ?>')"    class="btn btn-danger  btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    <?php } ?>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>