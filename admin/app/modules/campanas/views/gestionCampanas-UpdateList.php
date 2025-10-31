<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th>Nombre Campaña</th>
            <th>Fecha</th>
            <th class="text-end">Costos</th>
            <th class="text-end">Beneficios</th>
            <th class="text-end">Perdidas</th>
            <th class="text-end">Margen</th>
            <th>Estado</th>
            <th>Creador</th>
            <th width="10">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['arrList'])&&!empty($data['arrList'])){
            foreach($data['arrList'] AS $crud){
                //Verifico el monto
                $isNegative = isset($crud['Margen']) && $crud['Margen'] < 0;
                $Icon       = $isNegative ? '<i class="ri-arrow-down-circle-line"></i>' : '<i class="ri-arrow-up-circle-line"></i>';
                $Color      = $isNegative ? 'text-danger' : 'text-success';
                //Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idCampana']);
                $level       = $data['UserAccess']['LevelAccess'];
                $route       = $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumen/'.$encryptedId;
                $Entidad     = addslashes($crud['Nombre']);
                ?>
                <tr>
                    <td><?php echo $crud['Nombre']; ?></td>
                    <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['Fecha']); ?></td>
                    <td class="text-end"><?php echo $data['Fnc_DataNumbers']->Valores($crud['Costos'], 2); ?></td>
                    <td class="text-end"><?php echo $data['Fnc_DataNumbers']->Valores($crud['Beneficios'], 2); ?></td>
                    <td class="text-end"><span class="text-danger"><?php echo $data['Fnc_DataNumbers']->Valores($crud['Perdidas'], 2); ?></span></td>
                    <td class="text-end"><span class="<?php echo $Color; ?>"><?php echo $Icon; ?> <?php echo $data['Fnc_DataNumbers']->Valores($crud['Margen'], 2); ?></span></td>
                    <td><?php echo '<span class="badge '.$crud['EstadoColor'].'">'.$crud['EstadoNombre'].'</span>'; ?></td>
                    <td><?php echo $crud['Creador']; ?></td>
                    <td>
                        <div class="btn-group" role="group">
                            <?php
                            //Valido
                            if ($level >= 1) {echo '<button type="button" onclick="listTableDataView(\''.$encryptedId.'\')"                  class="btn btn-primary   btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>';}
                            if ($level >= 2) {echo '<a href="'.$route.'"                                                                     class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></a>';}
                            if ($level >= 4) {echo '<button type="button" onclick="listTableDataDel(\''.$encryptedId.'\', \''.$Entidad.'\')" class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>';}
                            ?>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>