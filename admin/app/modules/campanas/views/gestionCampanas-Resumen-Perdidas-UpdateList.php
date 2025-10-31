<table class="table table-sm table-hover datatable" id="tableDataPerdidas">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Item</th>
            <th>Mov Bodega</th>
            <th class="text-end">Cantidad</th>
            <th class="text-end">Valor</th>
            <?php if(isset($data['rowData']['idEstado'])&&$data['rowData']['idEstado']==1){ ?>
                <th width="10">Acciones</th>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['arrPerdidas'])&&!empty($data['arrPerdidas'])){
            //Totales
            $TotalCantidad = 0;
            $TotalPerdidas = 0;
            //Recorro
            foreach($data['arrPerdidas'] AS $crud){
                $TotalCantidad = $TotalCantidad + $crud['Cantidad'];
                $TotalPerdidas = $TotalPerdidas + $crud['Perdidas']; ?>
                <tr>
                    <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['Fecha']); ?></td>
                    <td><?php echo $crud['Item']; ?></td>
                    <td>
                        <?php if(isset($crud['idMovimiento'])&&$crud['idMovimiento']!=0){
                            //Variables
                            $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idMovimiento']);
                            //imprimo
                            echo 'Mov Prod '.$crud['Producto'].' nRef '.$crud['idMovimiento']; ?>
                            <div class="btn-group" role="group">
                                <button type="button" onclick="tabPerdidasView('<?php echo $encryptedId; ?>')" class="btn btn-primary btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>
                            </div>
                        <?php } ?>
                    </td>
                    <td class="text-end"><?php if(isset($crud['Cantidad'])&&$crud['Cantidad']!=0){echo $data['Fnc_DataNumbers']->Cantidades($crud['Cantidad'], 2).' '.$crud['Unimed'];} ?></td>
                    <td class="text-end"><?php echo $data['Fnc_DataNumbers']->Valores($crud['Perdidas'], 2); ?></td>
                    <?php if(isset($data['rowData']['idEstado'])&&$data['rowData']['idEstado']==1){
                        //Variables
                        $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idExistencia']);
                        $Entidad     = addslashes($crud['Item']); ?>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" onclick="tabPerdidasEdit('<?php echo $encryptedId; ?>')"                             class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>
                                <button type="button" onclick="tabPerdidasDel( '<?php echo $encryptedId; ?>', '<?php echo $Entidad; ?>')"  class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    <?php } ?>
                </tr>
            <?php } ?>
            <tr>
                <td class="text-end" colspan="3"><strong>Totales</strong></td>
                <td class="text-end"><?php echo $data['Fnc_DataNumbers']->Cantidades($TotalCantidad, 2); ?></td>
                <td class="text-end"><?php echo $data['Fnc_DataNumbers']->Valores($TotalPerdidas, 2); ?></td>
                <?php if(isset($data['rowData']['idEstado'])&&$data['rowData']['idEstado']==1){ ?>
                    <td></td>
                <?php } ?>
            </tr>
        <?php } ?>
    </tbody>
</table>