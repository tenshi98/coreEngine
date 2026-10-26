<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th scope="col">Grupo</th>
            <th scope="col">Subgrupo</th>
            <th scope="col" style="width: 10px;">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Verifico si hay datos (arreglo agrupado por nombre de grupo)
        if(is_array($data['arrList'])&&!empty($data['arrList'])){
            // Recorro los grupos
            foreach($data['arrList'] as $grupo => $subgrupos){
                // Cabecera del grupo
                $total = is_array($subgrupos) ? count($subgrupos) : 0; ?>
                <tr class="table-secondary">
                    <td colspan="3"><i class="bi bi-folder2-open"></i> <strong><?php echo $grupo; ?></strong> <span class="badge bg-secondary"><?php echo $total; ?></span></td>
                </tr>
                <?php
                // Recorro los subgrupos del grupo
                if(is_array($subgrupos)){
                    foreach($subgrupos as $crud){
                        // Variables
                        $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idSubGrupo']);
                        $level       = $data['UserAccess']['LevelAccess'];
                        $Entidad     = addslashes($crud['Nombre']); ?>
                        <tr>
                            <td><?php echo $grupo; ?></td>
                            <td><?php echo $crud['Nombre']; ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <?php
                                    //Valido
                                    if ($level >= 1) {echo '<button type="button" onclick="listTableDataView(\''.$encryptedId['data'].'\')"                  class="btn btn-primary   btn-sm tooltiplink"   data-title="Ver Información"><i class="bi bi-eye"></i></button>';}
                                    if ($level >= 2) {echo '<button type="button" onclick="listTableDataEdit(\''.$encryptedId['data'].'\')"                  class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>';}
                                    if ($level >= 4) {echo '<button type="button" onclick="listTableDataDel(\''.$encryptedId['data'].'\', \''.$Entidad.'\')" class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>';}
                                    ?>
                                </div>
                            </td>
                        </tr>
                    <?php }
                }
            } ?>
        <?php } ?>
    </tbody>
</table>
