<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th scope="col">Fecha</th>
            <th scope="col">Documento</th>
            <th scope="col" class="text-end">Valor</th>
            <?php if(isset($data['rowData']['idEstadoPago'])&&$data['rowData']['idEstadoPago']==1){ ?>
                <th scope="col" style="width: 10px;">Acciones</th>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['arrGuias'])&&!empty($data['arrGuias'])){
            //Recorro
            foreach($data['arrGuias'] as $crud){ ?>
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
