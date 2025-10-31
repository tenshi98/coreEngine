<section class="section" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $data['TableTitle']; ?></h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive">
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
                                    foreach ($data['arrTareas'] AS $Tareas=>$tarea){ ?>
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
                                                    <?php if($data['UserAccess']['LevelAccess']>=1){ ?>
                                                        <button type="button" onclick="listTableDataView('<?php echo $data['Fnc_Codification']->encryptDecrypt('encrypt', $Tareas); ?>')"   class="btn btn-primary btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        </tr>
                                <?php }
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="clearfix"></div>
<div class="d-grid gap-2 d-md-flex justify-content-md-end">
    <a href="<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/listAll'; ?>" class="btn btn-danger float-end"><i class="bi bi-arrow-left-circle"></i> Volver</a>
</div>
<div class="clearfix"></div>
