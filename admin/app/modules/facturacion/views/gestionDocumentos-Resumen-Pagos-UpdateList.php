<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th>Fecha Pago</th>
            <th>Usuario Pago</th>
            <th>Documento Pago</th>
            <th class="text-end">Monto pago</th>
            <?php if(isset($data['rowData']['idEstadoPago'])&&$data['rowData']['idEstadoPago']==1){ ?>
                <th width="10">Acciones</th>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['arrPagos'])&&!empty($data['arrPagos'])){
            //Recorro
            foreach($data['arrPagos'] AS $crud){ ?>
                <tr>
                    <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['FechaPago']); ?></td>
                    <td><?php echo $crud['UsuarioPago']; ?></td>
                    <td><?php echo $crud['DocPago'].' '.$crud['N_Doc']; ?></td>
                    <td class="text-end"><?php echo $data['Fnc_DataNumbers']->Valores($crud['MontoPagado'], 2); ?></td>
                    <?php if(isset($data['rowData']['idEstadoPago'])&&$data['rowData']['idEstadoPago']==1){
                        //Variables
                        $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idPago']);
                        $Entidad     = addslashes($crud['DocPago'].' '.$crud['N_Doc']); ?>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" onclick="tabPagoEdit('<?php echo $encryptedId; ?>')"                            class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>
                                <button type="button" onclick="tabPagoDel( '<?php echo $encryptedId; ?>', '<?php echo $Entidad; ?>')" class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    <?php } ?>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>