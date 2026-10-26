<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
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
    <button type="button" class="btn-close" aria-label="Close" data-modal-close></button>
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
                                ? $data['UserData']['MainPathUrl'].$data['rowData']['Direccion_img']
                                : $BASE.'/img/picture-img.jpg';
                    ?>
                    <img src="<?php echo $UserIMG; ?>" alt="Profile" class="square-rounded-2 square-border-3 w-100">
                </div>
                <div class="col-xs-12 col-sm-12 col-md-7 col-lg-8 col-xl-9 col-xxl-10">
                    <?php
                    $Theme_color = 'summary-theme-color-2';
                    $arrData_1 = [
                        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-person-badge', 'Icon_color' => 10,'Title' => 'Tipo de usuario', 'Text' => $data['rowData']['TipoUsuario']],
                        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-envelope',     'Icon_color' => 10,'Title' => 'Email',           'Text' => $data['rowData']['email'], 'Text_color' => 26, 'Text_copy' => $data['rowData']['email']],
                        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-card-text',    'Icon_color' => 10,'Title' => 'Nombre',          'Text' => $data['rowData']['Nombre']],
                        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-person-vcard', 'Icon_color' => 10,'Title' => 'Rut',             'Text' => $data['rowData']['Rut']],
                        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-telephone',    'Icon_color' => 10,'Title' => 'Fono',            'Text' => $data['rowData']['Fono'], 'Text_color' => 26, 'Text_copy' => $data['rowData']['Fono']],
                        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-power',        'Icon_color' => 10,'Title' => 'Estado',          'Text' => '<span class="badge-sp1 badge-sp1-'.$data['rowData']['EstadoColor'].'">'.$data['rowData']['Estado'].'</span>'],
                    ];
                    $arrData_2 = [
                        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-clock-history', 'Icon_color' => 10,'Title' => 'Ultimo acceso', 'Text' => $data['rowData']['Ultimo_acceso']],
                        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-hdd-network',   'Icon_color' => 10,'Title' => 'IP',            'Text' => $data['rowData']['IP_Client']],
                        ['Theme_color' => $Theme_color, 'Icon' => 'bi bi-window',        'Icon_color' => 10,'Title' => 'Navegador',     'Text' => $data['rowData']['Agent_Transp']],
                    ];

                    /*******************************************/
                    echo $data['Fnc_WidgetsViews']->tittle_v2('Datos del Perfil', 23);
                    echo '<div class="row">';
                        $OptionsTable['Cols'] = 2;
                        echo $data['Fnc_WidgetsViews']->table_v6($arrData_1, $OptionsTable);
                    echo '</div>';

                    echo $data['Fnc_WidgetsViews']->tittle_v2('Datos de Acceso', 23);
                    echo '<div class="row">';
                        $OptionsTable['Cols'] = 2;
                        echo $data['Fnc_WidgetsViews']->table_v6($arrData_2, $OptionsTable);
                    echo '</div>';

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
                        <?php echo $data['Fnc_WidgetsViews']->tittle_v2('Permisos a las máquinas', 23); ?>
                        <div class="clearfix"></div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <tbody>
                                    <?php
                                    // Verifico si hay datos
                                    if(is_array($data['MainViewData']['Data_Maquinas'])&&!empty($data['MainViewData']['Data_Maquinas'])){
                                        // Recorro
                                        foreach($data['MainViewData']['Data_Maquinas'] as $crud){
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
                            <?php echo $data['Fnc_WidgetsViews']->tittle_v2('Permisos a las notificaciones', 23); ?>
                            <div class="clearfix"></div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <tbody>
                                        <?php
                                        // Verifico si hay datos
                                        if(is_array($data['MainViewData']['Data_Noti'])&&!empty($data['MainViewData']['Data_Noti'])){
                                            // Recorro
                                            foreach($data['MainViewData']['Data_Noti'] as $crud){
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
