<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<table class="table table-sm table-hover">
    <thead>
        <tr>
            <th scope="col" style="width: 10px;">#</th>
            <th scope="col">Pasillo</th>
            <th scope="col" style="width: 90px;">Orientacion</th>
            <th scope="col" style="width: 90px;">Racks</th>
            <th scope="col" style="width: 90px;">Estado</th>
            <th scope="col" style="width: 10px;">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Verifico si hay datos
        if(is_array($data['arrPasillos'])&&!empty($data['arrPasillos'])){
            // Recorro los datos
            foreach($data['arrPasillos'] as $crud){
                // Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idPasillo']); ?>
                <tr>
                    <td><?php echo $crud['Orden']; ?></td>
                    <td><strong><?php echo $crud['Nombre']; ?></strong></td>
                    <td><?php echo ($crud['Orientacion']==='V') ? 'Vertical' : 'Horizontal'; ?></td>
                    <td><?php echo $crud['Racks']; ?></td>
                    <td><span class="badge <?php echo $crud['EstadoColor']; ?>"><?php echo $crud['Estado']; ?></span></td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" onclick="distPasilloToggleRacks('<?php echo $encryptedId['data']; ?>', '<?php echo $crud['idPasillo']; ?>')" class="btn btn-info     btn-sm tooltiplink" data-title="Ver Racks"><i class="bi bi-chevron-down"></i></button>
                            <?php if($data['UserAccess']['LevelAccess']>=1){ ?>
                                <button type="button" onclick="distPasilloView('<?php echo $encryptedId['data']; ?>')"                            class="btn btn-primary   btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>
                            <?php } ?>
                            <?php if($data['UserAccess']['LevelAccess']>=2){ ?>
                                <button type="button" onclick="distPasilloEdit('<?php echo $encryptedId['data']; ?>')"                            class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>
                                <button type="button" onclick="distRackNew('<?php echo $encryptedId['data']; ?>')"                                class="btn btn-success   btn-sm tooltiplink" data-title="Crear Rack"><i class="bi bi-plus-lg"></i></button>
                                <button type="button" onclick="distPasilloDel('<?php echo $encryptedId['data']; ?>', '<?php echo $crud['idPasillo']; ?>', '<?php echo $crud['idZona']; ?>', '<?php echo addslashes($crud['Nombre']); ?>')" class="btn btn-danger btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                            <?php } ?>
                        </div>
                    </td>
                </tr>
                <tr id="tabRacksRowP<?php echo $crud['idPasillo']; ?>" style="display:none;">
                    <td colspan="6" class="bg-light">
                        <div id="tabRacksP<?php echo $crud['idPasillo']; ?>" class="p-1"></div>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>
