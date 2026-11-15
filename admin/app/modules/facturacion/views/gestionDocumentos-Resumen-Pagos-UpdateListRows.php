<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<?php
// Verifico si hay datos
if(is_array($data['arrPagos'])&&!empty($data['arrPagos'])){
    // Recorro los datos
    foreach($data['arrPagos'] as $crud){ ?>
        <tr>
            <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['FechaPago']); ?></td>
            <td><?php echo $crud['UsuarioPago']; ?></td>
            <td><?php echo $crud['DocPago'].' '.$crud['N_Doc']; ?></td>
            <td class="text-end"><?php echo $data['Fnc_DataNumbers']->Valores($crud['MontoPagado'], 2); ?></td>
            <?php if(isset($data['rowData']['idEstadoPago'])&&$data['rowData']['idEstadoPago']==1){
                // Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idPago']);
                $Entidad     = addslashes($crud['DocPago'].' '.$crud['N_Doc']); ?>
                <td>
                    <div class="btn-group" role="group">
                        <button type="button" onclick="tabPagoEdit('<?php echo $encryptedId['data']; ?>')"                            class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>
                        <button type="button" onclick="tabPagoDel( '<?php echo $encryptedId['data']; ?>', '<?php echo $Entidad; ?>')" class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                    </div>
                </td>
            <?php } ?>
        </tr>
    <?php } ?>
<?php } ?>
