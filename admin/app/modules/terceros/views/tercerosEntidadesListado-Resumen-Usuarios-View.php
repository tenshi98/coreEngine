<div class="modal-header">
    <?php
    switch ($data['UserData']["sistemaModalSubtitle"]) {
        case 1:
            echo '
            <h5 class="modal-title">
                <i class="bi bi-card-checklist"></i> Ver Datos
            </h5>';
            break;
        case 2:
            echo '
            <h5 class="modal-title modal-subtitle">
                <div class="icon"><i class="bi bi-card-checklist"></i></div>
                Ver Datos<br>
                <small>Permite visualizar los datos de un elemento existente</small>
            </h5>';
            break;
    } ?>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <ul class="nav nav-tabs nav-tabs-bordered d-grid d-md-flex justify-content-md-between">
        <li class="nav-item flex-fill" role="presentation"><button class="nav-link w-100 active" id="view_tab_1" data-bs-toggle="tab" data-bs-target="#tab_id_1" type="button" role="tab" aria-controls="tab_id_1" aria-selected="true"><i class="bi bi-card-list"></i> Datos</button></li>
        <?php
        //Si existen permisos a las maquinas
        if(isset($data['MainViewData']['Count_Maquinas'])&&$data['MainViewData']['Count_Maquinas']!=0){
            echo '<li class="nav-item flex-fill" role="presentation"><button class="nav-link w-100"        id="view_tab_2" data-bs-toggle="tab" data-bs-target="#tab_id_2" type="button" role="tab" aria-controls="tab_id_2" aria-selected="false" tabindex="-1"><i class="bi bi-gear-fill"></i> Permisos Máquinas</button></li>';
            if($data['UserData']["maquinasListadoNotificaciones"]==2){
                echo '<li class="nav-item flex-fill" role="presentation"><button class="nav-link w-100"    id="view_tab_3" data-bs-toggle="tab" data-bs-target="#tab_id_3" type="button" role="tab" aria-controls="tab_id_3" aria-selected="false" tabindex="-1"><i class="bi bi-whatsapp"></i> Permisos Notificaciones</button></li>';
            }
        } ?>
    </ul>
    <div class="tab-content pt-2" id="tabId_560_Content">
        <div class="tab-pane fade active show" id="tab_id_1" role="tabpanel" aria-labelledby="view_tab_1">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4 col-xl-3 col-xxl-2">
                    <?php
                    $UserIMG  = !empty($data['rowData']['Direccion_img'])
                                ? $BASE.'/upload/'.$data['rowData']['Direccion_img']
                                : $BASE.'/img/picture-img.jpg';
                    ?>
                    <img src="<?php echo $UserIMG; ?>" alt="Profile" class="square-rounded-2 square-border-3 w-100">
                </div>
                <div class="col-xs-12 col-sm-12 col-md-10 col-lg-9 col-xl-8 col-xxl-7">
                    <?php
                    $arrData_1 = [
                        ['Icon' => '','Titulo' => 'Tipo de usuario', 'Texto' => $data['rowData']['TipoUsuario']],
                        ['Icon' => '','Titulo' => 'Email',           'Texto' => $data['rowData']['email']],
                        ['Icon' => '','Titulo' => 'Nombre',          'Texto' => $data['rowData']['Nombre']],
                        ['Icon' => '','Titulo' => 'Rut',             'Texto' => $data['rowData']['Rut']],
                        ['Icon' => '','Titulo' => 'Fono',            'Texto' => $data['rowData']['Fono']],
                        ['Icon' => '','Titulo' => 'Email',           'Texto' => $data['rowData']['email']],
                        ['Icon' => '','Titulo' => 'Estado',          'Texto' => '<span class="badge '.$data['rowData']['EstadoColor'].'">'.$data['rowData']['Estado'].'</span>'],
                    ];
                    $arrData_2 = [
                        ['Icon' => '','Titulo' => 'Ultimo acceso', 'Texto' => $data['rowData']['Ultimo_acceso']],
                        ['Icon' => '','Titulo' => 'IP',            'Texto' => $data['rowData']['IP_Client']],
                        ['Icon' => '','Titulo' => 'Navegador',     'Texto' => $data['rowData']['Agent_Transp']],
                    ];

                    /*******************************************/
                    echo '<h5 class="card-title">Datos del Perfil</h5>';
                    $data['Fnc_WidgetsCommon']->responsiveTable($arrData_1, 8);

                    echo '<h5 class="card-title">Datos de Acceso</h5>';
                    $data['Fnc_WidgetsCommon']->responsiveTable($arrData_2, 8);

                    ?>
                </div>
            </div>
        </div>
        <?php
        //Si existen permisos a las maquinas
        if(isset($data['MainViewData']['Count_Maquinas'])&&$data['MainViewData']['Count_Maquinas']!=0){ ?>
            <div class="tab-pane fade" id="tab_id_2" role="tabpanel" aria-labelledby="view_tab_2">
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <h5 class="card-title">Permisos a las máquinas</h5>
                        <div class="clearfix"></div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <tbody>
                                    <?php
                                    //Verifico si hay datos
                                    if(is_array($data['MainViewData']['Data_Maquinas'])&&!empty($data['MainViewData']['Data_Maquinas'])){
                                        //Recorro
                                        foreach($data['MainViewData']['Data_Maquinas'] AS $crud){
                                            echo '<tr><td>'.$crud['Maquina'].'</td></tr>';
                                        }
                                    }else{
                                        echo '<tr><td>No se encontraron entradas</td></tr>';
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            //Si existen permisos de notificaciones
            if($data['UserData']["maquinasListadoNotificaciones"]==2){ ?>
                <div class="tab-pane fade" id="tab_id_3" role="tabpanel" aria-labelledby="view_tab_3">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                            <h5 class="card-title">Permisos a las notificaciones</h5>
                            <div class="clearfix"></div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <tbody>
                                        <?php
                                        //Verifico si hay datos
                                        if(is_array($data['MainViewData']['Data_Noti'])&&!empty($data['MainViewData']['Data_Noti'])){
                                            //Recorro
                                            foreach($data['MainViewData']['Data_Noti'] AS $crud){
                                                echo '<tr><td>'.$crud['Notificacion'].'</td></tr>';
                                            }
                                        }else{
                                            echo '<tr><td>No se encontraron entradas</td></tr>';
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            <?php }
        } ?>
    </div>
</div>
<?php
if($data['UserData']["sistemaModalCloseBTN"]==2){
    echo '
    <div class="modal-footer">
        <div class="d-grid gap-2 d-md-flex justify-content-md-end w-100">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bx bi-x-circle"></i> Cerrar</button>
        </div>
    </div>';
}else{
    echo '<style>.modal-body {max-height: 80vh;}</style>';
} ?>