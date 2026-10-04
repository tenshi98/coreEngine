<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th scope="col">Tipo</th>
            <th scope="col">Nombre</th>
            <th scope="col" style="width: 220px;">Fechas</th>
            <th scope="col" style="width: 10px;">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Verifico si hay datos
        if(is_array($data['arrDocumentos'])&&!empty($data['arrDocumentos'])){
            // Recorro los datos
            foreach($data['arrDocumentos'] as $crud){
                // Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idDocumentos']);
                $Entidad     = addslashes($crud['Nombre']); ?>
                <tr>
                    <td><?php echo $crud['Tipo']; ?></td>
                    <td><?php echo $crud['Nombre']; ?></td>
                    <td>
                        <?php
                        echo '<strong>Creacion: </strong>'.$data['Fnc_DataDate']->fechaEstandar($crud['FechaCreacion']);
                        if(isset($crud['FechaVencimiento'])&&$crud['FechaVencimiento']!='0000-00-00'){echo '<br><strong>Vencimiento: </strong>'.$data['Fnc_DataDate']->fechaEstandar($crud['FechaVencimiento']);}
                        ?>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" onclick="tabDocumentosView('<?php echo $encryptedId['data']; ?>')"                             class="btn btn-primary   btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>
                            <button type="button" onclick="tabDocumentosEdit('<?php echo $encryptedId['data']; ?>')"                             class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>
                            <button type="button" onclick="tabDocumentosDel( '<?php echo $encryptedId['data']; ?>', '<?php echo $Entidad; ?>')"  class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>
