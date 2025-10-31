<table class="table table-sm table-hover datatable" id="tableDataCostos">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Item</th>
            <th>Facturacion</th>
            <th class="text-end">Valor</th>
            <?php if(isset($data['rowData']['idEstado'])&&$data['rowData']['idEstado']==1){ ?>
                <th width="10">Acciones</th>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['arrCostos'])&&!empty($data['arrCostos'])){
            //Totales
            $TotalCostos = 0;
            //Recorro
            foreach($data['arrCostos'] AS $crud){
                $TotalCostos = $TotalCostos + $crud['Costos'];
                ?>
                <tr>
                    <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['Fecha']); ?></td>
                    <td><?php echo $crud['Item']; ?></td>
                    <td>
                        <?php if(isset($crud['idFacturacion'])&&$crud['idFacturacion']!=0){
                            //Variables
                            $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idFacturacion']);
                            //imprimo
                            echo $crud['DocumentoNombre'].' '.($crud['DocumentoN_Doc'] ?? 'nRef '.$crud['idFacturacion']); ?>
                            <div class="btn-group" role="group">
                                <button type="button" onclick="tabCostosView('<?php echo $encryptedId; ?>')" class="btn btn-primary btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>
                            </div>
                        <?php } ?>
                    </td>
                    <td class="text-end"><?php echo $data['Fnc_DataNumbers']->Valores($crud['Costos'], 2); ?></td>
                    <?php if(isset($data['rowData']['idEstado'])&&$data['rowData']['idEstado']==1){
                        //Variables
                        $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idExistencia']);
                        $Entidad     = addslashes($crud['Item']); ?>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" onclick="tabCostosEdit('<?php echo $encryptedId; ?>')"                             class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>
                                <button type="button" onclick="tabCostosDel( '<?php echo $encryptedId; ?>', '<?php echo $Entidad; ?>')"  class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    <?php } ?>
                </tr>
            <?php } ?>
            <tr>
                <td class="text-end" colspan="3"><strong>Totales</strong></td>
                <td class="text-end"><?php echo $data['Fnc_DataNumbers']->Valores($TotalCostos, 2); ?></td>
                <?php if(isset($data['rowData']['idEstado'])&&$data['rowData']['idEstado']==1){ ?>
                    <td></td>
                <?php } ?>
            </tr>
        <?php } ?>
    </tbody>
</table>