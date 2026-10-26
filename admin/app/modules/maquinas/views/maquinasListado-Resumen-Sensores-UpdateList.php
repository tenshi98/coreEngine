<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<table class="table table-sm table-hover align-middle" id="tabSensoresData">
    <thead>
        <tr>
            <th scope="col" style="width: 30px;"></th>
            <th scope="col">Nombre</th>
            <th scope="col">Grupo</th>
            <th scope="col">Subgrupo</th>
            <th scope="col">Unidad de Medida</th>
            <th scope="col">Estado</th>
            <th scope="col" style="width: 10px;">Acciones</th>
        </tr>
    </thead>
    <tbody id="tabSensoresDataBody">
        <?php
        // Verifico si hay datos
        if(is_array($data['arrSensores'])&&!empty($data['arrSensores'])){
            // Recorro los datos
            foreach($data['arrSensores'] as $crud){
                // Variables
                $uid     = $crud['uid'] ?? '';
                $Entidad = addslashes($crud['Nombre'] ?? ''); ?>
                <tr data-uid="<?php echo htmlspecialchars($uid, ENT_QUOTES, 'UTF-8'); ?>">
                    <td class="text-center"><i class="bi bi-grip-vertical handle-sensor" style="cursor:move;" title="Arrastrar para ordenar" draggable="false"></i></td>
                    <td><?php echo $crud['Nombre'] ?? ''; ?></td>
                    <td><?php echo $crud['Grupo'] ?? ''; ?></td>
                    <td><?php echo $crud['SubGrupo'] ?? ''; ?></td>
                    <td><?php echo $crud['UniMed'] ?? ''; ?></td>
                    <td><?php echo $crud['Estado'] ?? ''; ?></td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" onclick="tabSensoresView('<?php echo $uid; ?>')"                            class="btn btn-primary   btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>
                            <button type="button" onclick="tabSensoresEdit('<?php echo $uid; ?>')"                            class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>
                            <button type="button" onclick="tabSensoresDel( '<?php echo $uid; ?>', '<?php echo $Entidad; ?>')"  class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>
