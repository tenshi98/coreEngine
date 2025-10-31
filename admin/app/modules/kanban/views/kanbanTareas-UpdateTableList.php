<table class="table table-sm table-hover datatable">
    <thead>
        <tr>
            <th>Estado</th>
            <th>Prioridad</th>
            <th>Fecha</th>
            <th>Titulo</th>
            <th>Participantes</th>
            <th width="10">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        //Verifico si hay datos
        if(is_array($data['arrTareas'])&&!empty($data['arrTareas'])){
            //Recorro
            foreach ($data['arrTareas'] AS $Tareas=>$tarea){
                //Variables
                $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $Tareas);
                $level       = $data['UserAccess']['LevelAccess'];
                $Entidad     = addslashes($tarea[0]['Titulo']); ?>
                <tr>
                    <td><span class="badge <?php echo $tarea[0]['KanbanColor']; ?>"><?php echo $tarea[0]['KanbanEstado']; ?></span></td>
                    <td><span class="badge <?php echo $tarea[0]['PrioridadColor']; ?>"><?php echo $tarea[0]['PrioridadNombre']; ?></span></td>
                    <td><?php echo $data['Fnc_DataDate']->fechaEstandar($tarea[0]['Fecha']); ?></td>
                    <td><?php echo $tarea[0]['Titulo']; ?></td>
                    <td>
                        <div class="taskimg">
                            <?php
                            //Se recorre
                            foreach ($tarea AS $task){
                                //se verifica si existe
                                if(isset($task['UsuarioNombre'])&&$task['UsuarioNombre']!=''){
                                    //verifico si existe imagen
                                    $UserIMG  = !empty($task['UsuarioImg'])
                                                ? $BASE.'/upload/'.$task['UsuarioImg']
                                                : $BASE.'/img/profile-img.jpg';

                                    //imprimo
                                    echo '
                                    <span class="tooltiplink" data-title="'.$task['UsuarioNombre'].'">
                                        <img src="'.$UserIMG.'" alt="Profile" class="rounded-circle" style="width: 30px;height: 30px;border: 1px solid #ebeef4;">
                                    </span>';
                                }
                            } ?>
                        </div>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <?php
                            //Valido
                            if ($level >= 1) {echo '<button type="button" onclick="listTableDataView(\''.$encryptedId.'\')"                  class="btn btn-primary btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>';}
                            if ($level >= 4) {echo '<button type="button" onclick="listTableDataDel(\''.$encryptedId.'\', \''.$Entidad.'\')" class="btn btn-danger  btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>';}
                            ?>
                        </div>
                    </td>
                </tr>
        <?php }
        } ?>
    </tbody>
</table>