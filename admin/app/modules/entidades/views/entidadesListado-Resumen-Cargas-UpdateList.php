<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th scope="col">Nombre</th>
            <th scope="col">Parentesco</th>
            <th scope="col" style="width: 10px;">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['arrCargas'])&&!empty($data['arrCargas'])){
            //Recorro
            foreach($data['arrCargas'] as $crud){
                // Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idCargas']);
                $Entidad     = addslashes($crud['ApellidoPat'].' '.$crud['ApellidoMat'].' '.$crud['Nombre']); ?>
                <tr>
                    <td><?php echo $crud['ApellidoPat'].' '.$crud['ApellidoMat'].' '.$crud['Nombre']; ?></td>
                    <td><?php echo $crud['Parentesco']; ?></td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" onclick="tabCargasView('<?php echo $encryptedId; ?>')"                             class="btn btn-primary   btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>
                            <button type="button" onclick="tabCargasEdit('<?php echo $encryptedId; ?>')"                             class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>
                            <button type="button" onclick="tabCargasDel( '<?php echo $encryptedId; ?>', '<?php echo $Entidad; ?>')"  class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>
