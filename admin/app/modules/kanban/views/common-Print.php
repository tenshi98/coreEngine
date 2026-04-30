<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<div class="kanban-container">
    <div class="kanban-header">
        <div class="row gutters">
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6">
                <address>
                    <p>
                        <span class="<?php echo 'badge-sp1 badge-sp1-'.$data['rowData']['PrioridadColor']; ?>"><?php echo 'Prioridad: '.$data['rowData']['PrioridadNombre']; ?></span>
                        <span class="<?php echo 'badge-sp1 badge-sp1-'.$data['rowData']['KanbanColor']; ?>"><?php echo 'Tablero: '.$data['rowData']['KanbanEstado']; ?></span>
                        <span class="<?php echo 'badge-sp1 badge-sp1-'.$data['rowData']['EstadoCierreColor']; ?>"><?php echo 'Estado Cierre: '.$data['rowData']['EstadoCierreNombre']; ?></span>
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
                                <?php foreach ($data['arrTareas'] as $task){ ?>
                                    <tr>
                                        <td><?php echo $task['Trabajo'].'<br> - '.$task['Tarea'];?></td>
                                        <td style="width: 120px;"><span class="<?php echo 'badge-sp1 badge-sp1-'.$task['EstadoColor']?>"><?php echo '<i class="'.$task['EstadoIcon'].'"></i> '.$task['EstadoNombre']?></span></td>
                                    </tr>
                                <?php } ?>
                            <?php }else{ ?>
                                <?php foreach ($data['arrTareas'] as $task){ ?>
                                    <tr>
                                        <td><?php echo $task['Tarea']?></td>
                                        <td style="width: 120px;"><span class="<?php echo 'badge-sp1 badge-sp1-'.$task['EstadoColor']?>"><?php echo '<i class="'.$task['EstadoIcon'].'"></i> '.$task['EstadoNombre']?></span></td>
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
                            <?php foreach ($data['arrParticipantes'] as $task){
                                //verifico si existe imagen
                                $UserIMG  = !empty($task['UsuarioImg'])
                                            ? $data['UserData']['MainPathUrl'].$task['UsuarioImg']
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
