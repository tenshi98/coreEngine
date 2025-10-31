<div class="kanban-container">
    <div class="kanban-header">
        <div class="row gutters">
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6">
                <address>
                    <p>
                        <span class="badge <?php echo $data['rowData']['PrioridadColor']; ?>"><?php echo 'Prioridad: '.$data['rowData']['PrioridadNombre']; ?></span>
                        <span class="badge <?php echo $data['rowData']['KanbanColor']; ?>"><?php echo 'Tablero: '.$data['rowData']['KanbanEstado']; ?></span>
                        <span class="badge <?php echo $data['rowData']['EstadoCierreColor']; ?>"><?php echo 'Estado Cierre: '.$data['rowData']['EstadoCierreNombre']; ?></span>
                    </p>
                </address>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6">
                <address>
                    <span class="float-end date"><i class="bi bi-calendar3"></i> <?php echo $data['Fnc_DataDate']->fechaEstandar($data['rowData']['Fecha']); ?></span>
                </address>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                <p class="title"><strong><?php echo $data['rowData']['Titulo']; ?></strong></p>
            </div>
        </div>
        <div class="row gutters">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                <div class="kanban-details">
                    <address>
                        <?php echo $data['rowData']['Descripcion']; ?>
                    </address>
                </div>
            </div>
        </div>
        <!-- Row end -->
    </div>
    <div class="kanban-body">
        <div class="row gutters">

            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                <p class="text-facture">
                    <strong><i class="bi bi-clipboard-plus"></i> Tareas</strong>
                </p>
            </div>
            <div class="clearfix"></div>

            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <?php
                            //Se verifica si se permite usar tareas especificas
                            if($data['UserData']["KanbanTareasUsoTareas"]==2){ ?>
                                <?php foreach ($data['arrTareas'] AS $task){ ?>
                                    <tr>
                                        <td><?php echo $task['Trabajo'].'<br> - '.$task['Tarea'];?></td>
                                        <td width=120><span class="badge <?php echo $task['EstadoColor']?>"><?php echo '<i class="'.$task['EstadoIcon'].'"></i> '.$task['EstadoNombre']?></span></td>
                                    </tr>
                                <?php } ?>
                            <?php }else{ ?>
                                <?php foreach ($data['arrTareas'] AS $task){ ?>
                                    <tr>
                                        <td><?php echo $task['Tarea']?></td>
                                        <td width=120><span class="badge <?php echo $task['EstadoColor']?>"><?php echo '<i class="'.$task['EstadoIcon'].'"></i> '.$task['EstadoNombre']?></span></td>
                                    </tr>
                                <?php } ?>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                <p class="text-facture">
                    <strong><i class="bi bi-person-plus"></i> Participantes</strong>
                </p>
            </div>
            <div class="clearfix"></div>

            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <?php foreach ($data['arrParticipantes'] AS $task){
                                //verifico si existe imagen
                                $UserIMG =  !empty($task['UsuarioImg'])
                                            ? $BASE.'/upload/'.$task['UsuarioImg']
                                            : $BASE.'/img/profile-img.jpg';
                                ?>
                                <tr>
                                    <td><img src="<?php echo $UserIMG; ?>" alt="Profile" class="rounded-circle" style="width: 30px;height: 30px;border: 1px solid #ebeef4;"> <?php echo $task['UsuarioNombre']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>