<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th scope="col">Nombre</th>
            <th scope="col">Creación</th>
            <th scope="col">Creador</th>
            <th scope="col">Estado</th>
            <th scope="col">Caducidad</th>
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
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idToken']);
                $level       = $data['UserAccess']['LevelAccess'];
                $route       = $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumen/'.$encryptedId['data'];
                $Entidad     = addslashes(htmlspecialchars('#'.$crud['idToken'], ENT_QUOTES)); ?>
                <tr>
                    <td><?php echo htmlspecialchars((string)($crud['Nombre'] ?? '')); ?></td>
                    <td><?php echo htmlspecialchars((string)$crud['FechaCreacion']); ?></td>
                    <td><?php echo htmlspecialchars((string)($crud['Usuario'] ?? '')); ?></td>
                    <td><span class="badge-sp1 badge-sp1-<?php echo htmlspecialchars((string)($crud['EstadoColor'] ?? '')); ?>"><?php echo htmlspecialchars((string)($crud['Estado'] ?? '')); ?></span></td>
                    <td>
                        <?php echo htmlspecialchars((string)($crud['FechaCaducidad'] ?? '')); ?>
                        <?php if(!empty($crud['Caducado'])){ ?><span class="badge bg-danger">Caducado</span><?php } ?>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <?php
                            //Valido
                            if ($level >= 1) {echo '<button type="button" onclick="listTableDataView(\''.$encryptedId['data'].'\')"                  class="btn btn-primary   btn-sm tooltiplink"   data-title="Ver Información"><i class="bi bi-eye"></i></button>';}
                            if ($level >= 2) {echo '<a href="'.$route.'"                                                                             class="btn btn-secondary btn-sm tooltiplink"   data-title="Editar Información"><i class="bi bi-pencil-square"></i></a>';}
                            if ($level >= 4) {echo '<button type="button" onclick="listTableDataDel(\''.$encryptedId['data'].'\', \''.$Entidad.'\')" class="btn btn-danger    btn-sm tooltiplink"   data-title="Borrar Información"><i class="bi bi-trash"></i></button>';}
                            ?>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>
