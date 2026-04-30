<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th scope="col">Servicio</th>
            <th scope="col">Fecha Ingreso</th>
            <th scope="col">Monto Servicio</th>
            <th scope="col">Estado</th>
            <th scope="col" style="width: 10px;">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['arrPlanes'])&&!empty($data['arrPlanes'])){
            //Recorro
            foreach($data['arrPlanes'] as $crud){
                //Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idPlan']);
                $Entidad     = addslashes($crud['Servicio']);
                $level       = $data['UserAccess']['LevelAccess']; ?>
                <tr>
                    <td><?php echo $crud['Servicio']; ?></td>
                    <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['Fecha']); ?></td>
                    <td><?php echo $data['Fnc_DataNumbers']->Valores($crud['Monto'], 0); ?></td>
                    <td><?php echo '<span class="badge-sp1 badge-sp1-'.$crud['EstadoColor'].'">'.$crud['Estado'].'</span>'; ?></td>
                    <td>
                        <div class="btn-group" role="group">
                            <?php
                            //Valido
                            if ($level >= 1) {echo '<button type="button" onclick="tabPlanesView(\''.$encryptedId.'\')"                              class="btn btn-primary   btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>';}
                            if ($level >= 2) {echo '<button type="button" onclick="tabPlanesEdit(\''.$encryptedId.'\')"                              class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>';}
                            if ($level >= 4) {echo '<button type="button" onclick="tabPlanesDel(\''.$encryptedId.'\', \''.addslashes($Entidad).'\')" class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>';}
                            ?>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>
