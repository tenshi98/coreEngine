<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<?php
// Verifico si hay modulos instalados
if(empty($data['arrModules'])){
    echo '<div class="alert alert-secondary"><i class="bi bi-info-circle"></i> No hay modulos instalados con pruebas disponibles.</div>';
}
?>
<?php foreach ($data['arrModules'] as $module) { ?>
    <div class="border rounded p-3 mb-3">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h6 class="mb-1">
                    <i class="bi bi-box-seam"></i> <strong>Módulo: <?php echo htmlspecialchars($module['Nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                </h6>
                <small class="text-muted"><?php echo htmlspecialchars($module['Descripcion'], ENT_QUOTES, 'UTF-8'); ?></small><br>
                <small class="text-muted"><code><?php echo htmlspecialchars($module['Module'], ENT_QUOTES, 'UTF-8'); ?></code></small>
            </div>
            <?php if(!empty($module['Tests'])) { ?>
                <button type="button" onclick="ejecutarTodasPruebas('<?php echo htmlspecialchars($module['Module'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($module['Nombre'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo count($module['Tests']); ?>')" class="btn btn-success btn-sm text-nowrap tooltiplink" data-title="Ejecutar todas las pruebas del modulo"><i class="bi bi-play-fill"></i> Ejecutar todas (<?php echo count($module['Tests']); ?>)</button>
            <?php } ?>
        </div>

        <?php if(!empty($module['Tests'])) { ?>
            <div class="table-responsive mt-2">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th scope="col">N°</th>
                            <th scope="col">Prueba</th>
                            <th scope="col">Descripción</th>
                            <th scope="col">Mecanismo</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $Contador = 1; foreach ($module['Tests'] as $test) { ?>
                            <tr>
                                <td><?php echo $Contador; ?></td>
                                <td><strong><?php echo htmlspecialchars($test['Nombre'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                <td><?php echo htmlspecialchars($test['Descripcion'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><code><?php echo htmlspecialchars($test['Metodo'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                                <td><span class="badge-sp1 badge-sp1-bg-secondary"><?php echo htmlspecialchars($test['Estado'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td>
                                    <button type="button" onclick="ejecutarPrueba('<?php echo $module['Module']; ?>', '<?php echo $test['Test']; ?>', '<?php echo $test['Nombre']; ?>')" class="btn btn-primary btn-sm tooltiplink" data-title="Ejecutar esta prueba"><i class="bi bi-play-circle"></i> Ejecutar</button>
                                </td>
                            </tr>
                        <?php $Contador++; } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <div class="alert alert-secondary mb-0 mt-2"><i class="bi bi-info-circle"></i> Sin pruebas disponibles en este módulo.</div>
        <?php } ?>
    </div>
<?php } ?>
