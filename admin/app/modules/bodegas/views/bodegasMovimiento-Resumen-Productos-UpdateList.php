<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th scope="col">Tipo Movimiento</th>
            <th scope="col">Bodega</th>
            <th scope="col">Producto</th>
            <th scope="col">Cantidad</th>
            <th scope="col" style="width: 10px;">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['arrProductos'])&&!empty($data['arrProductos'])){
            //Recorro
            foreach($data['arrProductos'] as $crud){
                //Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idExistencia']);
                $Entidad     = addslashes($crud['ProductoNombre']); ?>
                <tr>
                    <td><?php echo $crud['TipoMovimiento']; ?></td>
                    <td><?php echo $crud['Bodega']; ?></td>
                    <td><?php echo $crud['ProductoNombre']; ?></td>
                    <td><?php echo $data['Fnc_DataNumbers']->Cantidades($crud['ProductoCantidad'], 2).' '.$crud['UnidadMedida']; ?></td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" onclick="tabProdEdit('<?php echo $encryptedId; ?>')"                             class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>
                            <button type="button" onclick="tabProdDel( '<?php echo $encryptedId; ?>', '<?php echo $Entidad; ?>')"  class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>
