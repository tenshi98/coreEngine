<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<?php
// Verifico si hay datos
if(is_array($data['arrStocks'])&&!empty($data['arrStocks'])){
    // Recorro
    foreach($data['arrStocks'] as $stock){ ?>
        <tr>
            <td><?php echo $stock['Producto']; ?></td>
            <?php
            // Verifico si hay datos
            if(is_array($data['arrBodegas'])){
                // Recorro
                foreach($data['arrBodegas'] as $bod){
                    // Se obtiene el ID
                    $DataID1 = $data['Fnc_Codification']->encryptDecrypt('encrypt', $stock['idProducto']);
                    $DataID2 = $data['Fnc_Codification']->encryptDecrypt('encrypt', $bod['idBodegas']);
                    echo '
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" onclick="listTableDataView(\''.$DataID1['data'].'\',\''.$DataID2['data'].'\')" class="btn btn-primary btn-sm tooltiplink" data-title="Ver Movimientos"><i class="bi bi-eye"></i></button>
                        </div>
                        '.$data['Fnc_DataNumbers']->Cantidades($stock['Cantidad_idBodegas_'.$bod['idBodegas']], 2).' '.$stock['UniMed'].'
                    </td>';
                }
            } ?>
        </tr>
    <?php } ?>
<?php } ?>
