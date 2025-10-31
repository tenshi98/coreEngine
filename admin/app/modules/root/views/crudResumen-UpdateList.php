<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th>Email</th>
            <th>Numero</th>
            <th>Rut</th>
            <th>Patente</th>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Palabra</th>
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
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idCrud']);
                $level       = $data['UserAccess']['LevelAccess'];
                $route       = $BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumen/'.$encryptedId;
                $Entidad     = addslashes($crud['Email']); ?>
                <tr>
                    <td><?php echo $crud['Email']; ?></td>
                    <td><?php echo $data['Fnc_DataNumbers']->Valores($crud['Numero'], 2); ?></td>
                    <td><?php echo $crud['Rut']; ?></td>
                    <td><?php echo $crud['Patente']; ?></td>
                    <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['Fecha']); ?></td>
                    <td><?php echo $crud['Hora']; ?></td>
                    <td><?php echo $crud['Palabra']; ?></td>
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