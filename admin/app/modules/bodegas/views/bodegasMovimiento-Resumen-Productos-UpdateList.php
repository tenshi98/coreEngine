<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th>Tipo Movimiento</th>
            <th>Bodega</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th width="10">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['arrProductos'])&&!empty($data['arrProductos'])){
            //Recorro
            foreach($data['arrProductos'] AS $crud){
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