<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th scope="col">Nombre (declarado)</th>
            <th scope="col">Client ID</th>
            <th scope="col">Tipo</th>
            <th scope="col">Estado</th>
            <th scope="col">Accesos</th>
            <th scope="col">Registro</th>
            <th scope="col">Último uso</th>
            <th scope="col" style="width: 10px;">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Verifico si hay datos
        if(is_array($data['arrList'])&&!empty($data['arrList'])){
            // Recorro los datos
            foreach($data['arrList'] as $crud){
                // Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idCliente']);
                $level       = $data['UserAccess']['LevelAccess'];
                $Entidad     = addslashes(htmlspecialchars('#'.$crud['idCliente'], ENT_QUOTES)); ?>
                <tr>
                    <td><?php echo htmlspecialchars((string)($crud['Nombre'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)($crud['ClientId'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo (($crud['AuthMethod'] ?? '') === 'none') ? 'Público' : 'Confidencial'; ?></td>
                    <td><span class="badge-sp1 badge-sp1-<?php echo htmlspecialchars((string)($crud['EstadoColor'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string)($crud['Estado'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span></td>
                    <td><?php echo (int)($crud['NAccesos'] ?? 0); ?></td>
                    <td><?php echo htmlspecialchars((string)($crud['FechaRegistro'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo !empty($crud['FechaUltimoUso']) ? htmlspecialchars((string)$crud['FechaUltimoUso'], ENT_QUOTES, 'UTF-8') : 'Sin uso'; ?></td>
                    <td>
                        <div class="btn-group" role="group">
                            <?php
                            //Valido
                            if ($level >= 1) {echo '<button type="button" onclick="listTableDataView(\''.$encryptedId['data'].'\')"                  class="btn btn-primary   btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>';}
                            if ($level >= 2) {echo '<button type="button" onclick="listTableDataEdit(\''.$encryptedId['data'].'\')"                  class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>';}
                            if ($level >= 4) {echo '<button type="button" onclick="listTableDataDel(\''.$encryptedId['data'].'\', \''.$Entidad.'\')" class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>';}
                            ?>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>
