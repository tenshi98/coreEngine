<div class="tab-pane fade active show" id="tab_kanban_1" role="tabpanel" aria-labelledby="home-tab_kanban_1">
    <div class="table-responsive">
        <div class="d-flex">
            <?php
            //Verifico si hay datos
            if(is_array($data['arrList'])){
                //Recorro
                foreach($data['arrList'] AS $crud){ ?>
                    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 col-xl-4 col-xxl-4">
                        <div class="kanban status" id="<?php echo $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idKanbanEstado']); ?>">
                            <h5 class="title text-center">
                                <span class="badge <?php echo $crud['Color']; ?>"><?php echo $crud['Nombre']; ?></span>
                                <?php
                                //Se verifica si se permite Administrar Tableros Independiente de las Tareas
                                if($data['UserData']["KanbanTareasAdminTabIndepend"]!=2){
                                    //Variables
                                    $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idKanbanEstado']);
                                    $level       = $data['UserAccess']['LevelAccess'];
                                    $Entidad     = addslashes($crud['Nombre']); ?>
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <?php
                                        //Valido
                                        if ($level >= 2) {echo '<button type="button" onclick="EditTabla(\''.$encryptedId.'\')"                  class="btn btn-secondary btn-sm"><i class="bi bi-pencil-square"></i></button>';}
                                        if ($level >= 4) {echo '<button type="button" onclick="delTabla(\''.$encryptedId.'\', \''.$Entidad.'\')" class="btn btn-danger    btn-sm"><i class="bi bi-trash"></i></button>';}
                                        ?>
                                    </div>
                                <?php } ?>
                            </h5>

                            <?php
                            //Verifico si hay datos
                            if(is_array($data['arrTareas'])){
                                //Recorro
                                foreach ($data['arrTareas'] AS $Tareas=>$tarea){
                                    //Filtro los del mismo estado
                                    if($tarea[0]['idKanbanEstado']==$crud['idKanbanEstado']){
                                        //Verifico si la fecha de cierre es mayor que fecha actual
                                        $col_borde = ($tarea[0]['Fecha'] > $data['Fnc_ServerServer']->fechaActual()) ? 'task-ok' : 'task-problem';
                                        //Variables
                                        $encryptedId = $data['Fnc_Codification']->encryptDecrypt('encrypt', $Tareas);
                                        $level       = $data['UserAccess']['LevelAccess'];
                                        $Entidad     = addslashes($tarea[0]['Titulo']);
                                        ?>
                                        <div class="task <?php echo $col_borde; ?> todo" draggable="true" id="<?php echo $data['Fnc_Codification']->encryptDecrypt('encrypt', $Tareas); ?>">
                                            <h4 class="align-middle">
                                                <span class="badge <?php echo $tarea[0]['PrioridadColor']; ?>"><?php echo $tarea[0]['PrioridadNombre']; ?></span>
                                                <span class="float-end date"><i class="bi bi-calendar3"></i> <?php echo $data['Fnc_DataDate']->fechaEstandar($tarea[0]['Fecha']); ?></span>
                                            </h4>
                                            <p><?php echo $tarea[0]['Titulo']; ?></p>
                                            <div>
                                                <div class="float-start img">
                                                    <?php
                                                    //se recorre
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
                                                                <img src="'.$UserIMG.'" alt="Profile" class="rounded-circle">
                                                            </span>';
                                                        }
                                                    } ?>
                                                </div>
                                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                                    <?php
                                                    //Valido
                                                    if ($level >= 1) {echo '<button type="button" onclick="listTableDataView(\''.$encryptedId.'\')"                  class="btn btn-primary btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>';}
                                                    if ($level >= 4) {echo '<button type="button" onclick="listTableDataDel(\''.$encryptedId.'\', \''.$Entidad.'\')" class="btn btn-danger  btn-sm tooltiplink" data-title="Borrar Información"><i class="bi bi-trash"></i></button>';}
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="clearfix"></div>
                                        </div>
                                    <?php
                                    }
                                }
                            } ?>
                        </div>
                    </div>
                <?php }
            } ?>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="tab_kanban_2" role="tabpanel" aria-labelledby="home-tab_kanban_2">
    <h5 class="card-title">
        <div class="btn-group" role="group">
            <button class="btn btn-secondary tooltiplink" data-title="Filtrar Información" type="button" data-bs-toggle="collapse" data-bs-target="#formSearch" aria-expanded="false" aria-controls="formSearch"><i class="bi bi-search"></i> Filtrar</button>
            <button class="btn btn-danger tooltiplink"    data-title="Quitar Filtro"       type="button" onclick="deleteFilter()"><i class="ri-filter-off-line"></i></button>
        </div>
        Búsqueda de Tareas
    </h5>
    <?php require_once('kanbanTareas-formSearch.php'); ?>
    <div class="clearfix"></div>
    <div class="table-responsive" id="listTableDataList">
        <?php require_once('kanbanTareas-UpdateTableList.php'); ?>
    </div>
</div>
