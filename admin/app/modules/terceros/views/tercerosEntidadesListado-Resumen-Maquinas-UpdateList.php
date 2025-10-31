<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th>Maquina</th>
            <th>Fecha Ingreso</th>
            <th>Estado</th>
            <th width="10">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['arrMaq'])&&!empty($data['arrMaq'])){
            //Recorro
            foreach($data['arrMaq'] AS $crud){
                //Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idMaq']);
                $Entidad     = addslashes($crud['Maquina']);
                $level       = $data['UserAccess']['LevelAccess']; ?>
                <tr>
                    <td><?php echo $crud['Maquina']; ?></td>
                    <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['Fecha']); ?></td>
                    <td><?php echo '<span class="badge '.$crud['EstadoColor'].'">'.$crud['Estado'].'</span>'; ?></td>
                    <td>
                        <div class="btn-group" role="group">
                            <?php
                            //Valido
                            if ($level >= 1) {echo '<button type="button" onclick="tabMaquinasView(\''.$encryptedId.'\')"                              class="btn btn-primary   btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>';}
                            if ($level >= 2) {echo '<button type="button" onclick="tabMaquinasEdit(\''.$encryptedId.'\')"                              class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>';}
                            if ($level >= 4) {echo '<button type="button" onclick="tabMaquinasDel(\''.$encryptedId.'\', \''.addslashes($Entidad).'\')" class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>';}
                            ?>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>