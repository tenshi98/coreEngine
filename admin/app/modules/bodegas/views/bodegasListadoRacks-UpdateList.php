<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<table class="table table-sm table-hover">
    <thead>
        <tr>
            <th scope="col" style="width: 10px;">#</th>
            <th scope="col">Rack</th>
            <th scope="col" style="width: 80px;">Niveles</th>
            <th scope="col" style="width: 110px;">Pos./Nivel</th>
            <th scope="col" style="width: 100px;">Posiciones</th>
            <th scope="col" style="width: 90px;">Estado</th>
            <th scope="col" style="width: 10px;">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Verifico si hay datos
        if(is_array($data['arrRacks'])&&!empty($data['arrRacks'])){
            // Recorro los datos
            foreach($data['arrRacks'] as $crud){
                // Variables
                $encryptedId      = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idRack']);
                $encryptedPasillo = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idPasillo']); ?>
                <tr>
                    <td><?php echo $crud['Orden']; ?></td>
                    <td><strong><?php echo $crud['Nombre']; ?></strong>
                        <?php if(!empty($crud['RackGemelo'])){ ?>
                            <span class="badge bg-info ms-1" title="Pasillo doble: enfrentado a <?php echo htmlspecialchars($crud['RackGemelo'], ENT_QUOTES, 'UTF-8'); ?>"><i class="bi bi-arrows-left-right"></i> <?php echo htmlspecialchars($crud['RackGemelo'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php } ?>
                    </td>
                    <td><?php echo $crud['Niveles']; ?></td>
                    <td><?php echo $crud['PosicionesPorNivel']; ?></td>
                    <td><?php echo $crud['Posiciones']; ?></td>
                    <td><span class="badge <?php echo $crud['EstadoColor']; ?>"><?php echo $crud['Estado']; ?></span></td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" onclick="distShowMapa()"                                                 class="btn btn-info     btn-sm tooltiplink" data-title="Ver Posiciones"><i class="bi bi-grid-3x3-gap"></i></button>
                            <?php if($data['UserAccess']['LevelAccess']>=1){ ?>
                                <button type="button" onclick="distRackView('<?php echo $encryptedId['data']; ?>')"            class="btn btn-primary   btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>
                            <?php } ?>
                            <?php if($data['UserAccess']['LevelAccess']>=2){ ?>
                                <button type="button" onclick="distRackEdit('<?php echo $encryptedId['data']; ?>')"            class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>
                                <button type="button" onclick="distRackDel('<?php echo $encryptedId['data']; ?>', '<?php echo $crud['idPasillo']; ?>', '<?php echo $encryptedPasillo['data']; ?>', '<?php echo addslashes($crud['Nombre']); ?>')" class="btn btn-danger btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                            <?php } ?>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>
