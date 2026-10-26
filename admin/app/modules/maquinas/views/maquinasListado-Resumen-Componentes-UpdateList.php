<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<table class="table table-sm table-hover">
    <thead>
        <tr>
            <th scope="col" style="width: 10px;"></th>
            <th scope="col" style="width: 170px;">Tipo</th>
            <th scope="col">Nombre</th>
            <th scope="col" style="width: 90px;">Codigo</th>
            <th scope="col" style="width: 100px;">Marca</th>
            <th scope="col" style="width: 100px;">Modelo</th>
            <th scope="col" style="width: 100px;">Serie</th>
            <th scope="col" style="width: 50px;">Hijos</th>
            <th scope="col" style="width: 10px;">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Verifico si hay datos
        if(is_array($data['arrComponentes'])&&!empty($data['arrComponentes'])){
            // Recorro los datos
            foreach($data['arrComponentes'] as $crud){
                // Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idComponentes']);
                $idPlain     = (int)$crud['idComponentes'];
                $Nivel       = (int)$crud['Nivel'];
                $Hijos       = (int)$crud['Hijos'];
                // RN-002: tipo derivado (raíz = Componente, resto = Subcomponente)
                $Tipo        = ($Nivel === 1) ? 'Componente' : 'Subcomponente';
                $Entidad     = addslashes($crud['Nombre']); ?>
                <tr>
                    <td>
                        <?php if($Hijos > 0 || $Nivel < 5){ ?>
                            <button type="button" onclick="tabComponentesToggleHijos('<?php echo $encryptedId['data']; ?>', '<?php echo $idPlain; ?>')" class="btn btn-info btn-sm tooltiplink" data-title="Ver Subcomponentes"><i class="bi bi-chevron-down"></i></button>
                        <?php } ?>
                    </td>
                    <td>
                        <span class="badge bg-primary"><?php echo $Tipo; ?></span>
                        <?php if((int)$crud['NoUtilizable'] === 1){ ?>
                            <span class="badge bg-danger">No utilizable</span>
                        <?php } ?>
                    </td>
                    <td><?php echo $crud['Nombre']; ?></td>
                    <td><?php echo $crud['Codigo']; ?></td>
                    <td><?php echo $crud['Marca']; ?></td>
                    <td><?php echo $crud['Modelo']; ?></td>
                    <td><?php echo $crud['Serie']; ?></td>
                    <td><?php echo $Hijos; ?></td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" onclick="tabComponentesView('<?php echo $encryptedId['data']; ?>')"                                 class="btn btn-primary   btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>
                            <button type="button" onclick="tabComponentesEdit('<?php echo $encryptedId['data']; ?>')"                                 class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>
                            <?php if($Nivel < 5){ ?>
                                <button type="button" onclick="tabComponentesNewSub('<?php echo $encryptedId['data']; ?>', '<?php echo $idPlain; ?>')" class="btn btn-success   btn-sm tooltiplink" data-title="Crear Subcomponente"><i class="bi bi-plus-lg"></i></button>
                            <?php } ?>
                            <button type="button" onclick="tabComponentesDel('<?php echo $encryptedId['data']; ?>', '<?php echo $Entidad; ?>')"    class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
                <tr id="tabCompHijosRow<?php echo $idPlain; ?>" style="display:none;">
                    <td colspan="9" class="bg-light">
                        <div id="tabCompHijos<?php echo $idPlain; ?>" class="p-1"></div>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>
