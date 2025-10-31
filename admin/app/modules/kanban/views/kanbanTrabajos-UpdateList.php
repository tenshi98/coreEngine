<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Estado</th>
            <th width="10">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['arrList'])&&!empty($data['arrList'])){
            //Recorro
            foreach($data['arrList'] AS $crud){
                //Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idTrabajo']);
                $level       = $data['UserAccess']['LevelAccess'];
                $Entidad     = addslashes($crud['Nombre']); ?>
                <tr>
                    <td><?php echo $crud['Nombre']; ?></td>
                    <td><span class="badge <?php echo $crud['EstadoColor']; ?>"><?php echo $crud['Estado']; ?></span></td>
                    <td>
                        <div class="btn-group" role="group">
                            <?php
                            //Valido
                            if ($level >= 1) {echo '<button type="button" onclick="listTableDataView(\''.$encryptedId.'\')"                  class="btn btn-primary   btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>';}
                            if ($level >= 2) {echo '<button type="button" onclick="listTableDataEdit(\''.$encryptedId.'\')"                  class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></button>';}
                            if ($level >= 4) {echo '<button type="button" onclick="listTableDataDel(\''.$encryptedId.'\', \''.$Entidad.'\')" class="btn btn-danger    btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>';}
                            ?>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>