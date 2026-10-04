<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

$arrData = [
    [
        'Theme_color'  => 'summary-theme-color-1',
        'Icon'         => '',
        'Icon_color'   => 1,
        'Title'        => 'idUsuario',
        'Title_color'  => 1,
        'Text'         => $data['rowData']['idUsuario'],
        'Text_color'   => '',
        'Text_copy'    => '',
        'Value'        => 70,
        'Value_color'  => 'bg-empty',
    ],
    [
        'Theme_color'  => 'summary-theme-color-2',
        'Icon'         => '',
        'Icon_color'   => 5,
        'Title'        => 'Email',
        'Title_color'  => '',
        'Text'         => $data['rowData']['Email'],
        'Text_color'   => '',
        'Text_copy'    => '',
        'Value'        => 35,
        'Value_color'  => 'bg-success',
    ],
    [
        'Theme_color'  => 'summary-theme-color-3',
        'Icon'         => '',
        'Icon_color'   => 9,
        'Title'        => 'Numero',
        'Title_color'  => '',
        'Text'         => $data['rowData']['Numero'],
        'Text_color'   => '',
        'Text_copy'    => '',
        'Value'        => 54,
        'Value_color'  => 'bg-info',
    ],
    [
        'Theme_color'  => 'summary-theme-color-1',
        'Icon'         => 'bi bi-bank',
        'Icon_color'   => 12,
        'Title'        => 'Rut',
        'Title_color'  => 12,
        'Text'         => $data['rowData']['Rut'],
        'Text_color'   => '',
        'Text_copy'    => '',
        'Value'        => 92,
        'Value_color'  => 'bg-warning',
    ],
    [
        'Theme_color'  => 'summary-theme-color-2',
        'Icon'         => 'bi bi-bell',
        'Icon_color'   => 15,
        'Title'        => 'Patente',
        'Title_color'  => '',
        'Text'         => $data['rowData']['Patente'],
        'Text_color'   => '',
        'Text_copy'    => '',
        'Value'        => 31,
        'Value_color'  => 'bg-danger',
    ],
    [
        'Theme_color'  => 'summary-theme-color-3',
        'Icon'         => 'bi bi-box-seam',
        'Icon_color'   => 16,
        'Title'        => 'Fecha',
        'Title_color'  => '',
        'Text'         => $data['rowData']['Fecha'],
        'Text_color'   => '',
        'Text_copy'    => '',
        'Value'        => 49,
        'Value_color'  => 'bg-empty',
    ],
    [
        'Theme_color'  => 'summary-theme-color-1',
        'Icon'         => 'bi bi-briefcase',
        'Icon_color'   => 17,
        'Title'        => 'Hora',
        'Title_color'  => '',
        'Text'         => $data['rowData']['Hora'],
        'Text_color'   => '',
        'Text_copy'    => '',
        'Value'        => 89,
        'Value_color'  => 'bg-success',
    ],
    [
        'Theme_color'  => 'summary-theme-color-2',
        'Icon'         => 'bi bi-building',
        'Icon_color'   => 18,
        'Title'        => 'Palabra',
        'Title_color'  => '',
        'Text'         => $data['rowData']['Palabra'],
        'Text_color'   => '',
        'Text_copy'    => '',
        'Value'        => 69,
        'Value_color'  => 'bg-info',
    ],
    [
        'Theme_color'  => 'summary-theme-color-3',
        'Icon'         => 'bi bi-archive',
        'Icon_color'   => 20,
        'Title'        => 'Fono',
        'Title_color'  => '',
        'Text'         => '+56 9 5539 1914',
        'Text_color'   => 26,
        'Text_copy'    => '56955391914',
        'Value'        => 69,
        'Value_color'  => 'bg-warning',
    ],
];

?>
<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12" data-aos="fade-up" data-aos-delay="600" data-aos-offset="200" data-aos-duration="500">

    <div class="card">

        <?php
        // Datos
        $Title          = 'Perfil de usuario';
        $SubTitle       = 'Datos del Perfil';
        $Text           = 'Información general y datos asociados al usuario';
        $IMG            = 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3';
        $Title_color    = 25;
        $SubTitle_color = 17;
        $Text_color     = 40;
        // Imprimo
        echo $data['Fnc_WidgetsViews']->headerProfile($Title, $SubTitle, $Text, $IMG, $Title_color, $SubTitle_color, $Text_color);
        ?>

        <div class="card-body pt-3">

            <ul class="nav nav-tabs nav-tabs-bordered d-grid d-md-flex justify-content-md-between">
                <li class="nav-item flex-fill"><button class="nav-link w-100 active" data-bs-toggle="tab" data-bs-target="#resumen"><i class="bi bi-card-list"></i> Resumen</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100"        data-bs-toggle="tab" data-bs-target="#resumen-obs"><i class="bi bi-chat-dots"></i> Observaciones</button></li>
            </ul>
            <div class="tab-content pt-2">

                <div class="tab-pane fade show active" id="resumen">

                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4 col-xl-3 col-xxl-2">
                            <?php $data['Fnc_WidgetsViews']->imgPortrait_1($BASE, $data['UserData']['MainPathUrl'], $data['rowData']['Direccion_img']); ?>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-7 col-lg-8 col-xl-9 col-xxl-10">
                            <?php
                            echo $data['Fnc_WidgetsViews']->tittle_v1('Datos del Perfil', 'h5');
                            $Options['Cols'] = 2;
                            echo '<div class="row">';
                                echo $data['Fnc_WidgetsViews']->table_v1($arrData, $Options);
                            echo '</div>';
                            ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4 col-xl-3 col-xxl-2">
                            <?php $data['Fnc_WidgetsViews']->imgPortrait_2($BASE, $data['UserData']['MainPathUrl'], $data['rowData']['Direccion_img']); ?>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-7 col-lg-8 col-xl-9 col-xxl-10 record-attachment">
                            <?php
                            $Options['Cols'] = 3;
                            echo $data['Fnc_WidgetsViews']->tittle_v2('Tablas', 23);
                            echo '<div class="row">';
                                echo $data['Fnc_WidgetsViews']->table_v2($arrData, $Options);
                                echo $data['Fnc_WidgetsViews']->table_v3($arrData, $Options);
                                echo $data['Fnc_WidgetsViews']->table_v4($arrData, $Options);
                            echo '</div>';

                            echo $data['Fnc_WidgetsViews']->tittle_v3('Tabla barras', 1);
                            echo '<div class="row">';
                                echo $data['Fnc_WidgetsViews']->table_v5($arrData, $Options);
                            echo '</div>';

                            echo $data['Fnc_WidgetsViews']->tittle_v3('Fichas', 4);
                            echo '<div class="row">';
                                echo $data['Fnc_WidgetsViews']->table_v6($arrData, $Options);
                            echo '</div>';

                            echo $data['Fnc_WidgetsViews']->tittle_v3('Stats', 11);
                            echo '<div class="row">';
                                $Options['Cols'] = 6;
                                echo $data['Fnc_WidgetsViews']->table_v7($arrData, $Options);
                            echo '</div>';

                            echo $data['Fnc_WidgetsViews']->tittle_v3('Titulos', 12);
                            echo $data['Fnc_WidgetsViews']->tittle_v4('Perfil de usuario');
                            echo $data['Fnc_WidgetsViews']->tittle_v4('Perfil de usuario', 'REG-0001');
                            echo $data['Fnc_WidgetsViews']->tittle_v4('Perfil de usuario', 'REG-0001', 13, 17);
                            echo $data['Fnc_WidgetsViews']->tittle_v4('Perfil de usuario', 'REG-0001', 14, 18, $Icon = 'bi bi-bell');
                            echo $data['Fnc_WidgetsViews']->tittle_v4('Perfil de usuario', 'REG-0001', 15, 19, $Icon = 'bi bi-bell', 20);
                            echo $data['Fnc_WidgetsViews']->tittle_v4('Perfil de usuario', 'REG-0001', 16, 20, $Icon = 'bi bi-bell', 20, 74);
                            ?>

                        </div>
                    </div>

                </div>

                <div class="tab-pane fade" id="resumen-obs">
                    <h5 class="text-color-red-dark">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            Observaciones de
                            <button type="button" class="btn btn-success"><i class="bi bi-file-earmark"></i> Crear Nuevo</button>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive" id="tabObsDataTable">
                        <table class="table table-sm table-hover datatable">
                            <thead>
                                <tr>
                                    <th scope="col" style="width: 100px;">Fecha Creacion</th>
                                    <th scope="col">Observacion</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Verifico si hay datos
                                if(is_array($data['arrObservaciones'])&&!empty($data['arrObservaciones'])){
                                    // Recorro los datos
                                    foreach($data['arrObservaciones'] as $crud){ ?>
                                        <tr>
                                            <td><?php echo $crud['FechaCreacion']; ?></td>
                                            <td><?php echo '<strong>'.$crud['Usuario'].':</strong><br>'.$crud['Observacion']; ?></td>
                                        </tr>
                                    <?php } ?>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="observacion-List mt-3">
                        <?php
                        // Verifico si hay datos
                        if(is_array($data['arrObservaciones'])&&!empty($data['arrObservaciones'])){
                            // Recorro los datos
                            foreach($data['arrObservaciones'] as $crud){ ?>
                                <article class="observacion-item">
                                    <p class="observacion-item-meta"><?php echo '<strong>'.$crud['Usuario'].'</strong> '.$crud['FechaCreacion']; ?></p>
                                    <p class="observacion-item-texto"><?php echo $crud['Observacion']; ?></p>
                                </article>
                                <article class="observacion-item observacion-success-border-left">
                                    <p class="observacion-item-meta"><?php echo '<strong>'.$crud['Usuario'].'</strong> '.$crud['FechaCreacion']; ?></p>
                                    <p class="observacion-item-texto"><?php echo $crud['Observacion']; ?></p>
                                </article>
                                <article class="observacion-item observacion-danger-border-left observacion-danger-border">
                                    <p class="observacion-item-meta"><?php echo '<strong>'.$crud['Usuario'].'</strong> '.$crud['FechaCreacion']; ?></p>
                                    <p class="observacion-item-texto"><?php echo $crud['Observacion']; ?></p>
                                </article>
                                <article class="observacion-item observacion-danger-border-left observacion-danger-border observacion-danger-title">
                                    <p class="observacion-item-meta"><?php echo '<strong>'.$crud['Usuario'].'</strong> '.$crud['FechaCreacion']; ?></p>
                                    <p class="observacion-item-texto"><?php echo $crud['Observacion']; ?></p>
                                </article>
                            <?php } ?>
                        <?php } ?>
                    </div>


                    <div class="observations-timeline">
                        <?php
                        // Verifico si hay datos
                        if(is_array($data['arrObservaciones'])&&!empty($data['arrObservaciones'])){
                            // Recorro los datos
                            foreach($data['arrObservaciones'] as $crud){ ?>
                                <div class="timeline-item">
                                    <div class="timeline-item__dot"></div>
                                    <div class="timeline-item__date"><?php echo $crud['FechaCreacion']; ?></div>
                                    <p class="timeline-item__text"><?php echo $crud['Observacion']; ?></p>
                                    <div class="timeline-item__author"><?php echo $crud['Usuario']; ?></div>
                                </div>
                                <div class="timeline-item timeline-danger">
                                    <div class="timeline-item__dot"></div>
                                    <div class="timeline-item__date"><?php echo $crud['FechaCreacion']; ?></div>
                                    <p class="timeline-item__text"><?php echo $crud['Observacion']; ?></p>
                                    <div class="timeline-item__author"><?php echo $crud['Usuario']; ?></div>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>



                    <div class="timeline-obs">
                        <?php
                        // Verifico si hay datos
                        if(is_array($data['arrObservaciones'])&&!empty($data['arrObservaciones'])){
                            // Recorro los datos
                            foreach($data['arrObservaciones'] as $crud){ ?>
                                <div class="timeline-item timeline-item--warning" data-type="warning">
                                    <div class="timeline-card">
                                        <div class="timeline-card__meta">
                                            <span class="timeline-card__author"><?php echo $crud['Usuario']; ?></span>
                                            <span class="timeline-card__date"><?php echo $crud['FechaCreacion']; ?></span>
                                        </div>
                                        <div class="timeline-card__text"><?php echo $crud['Observacion']; ?></div>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>

                    <div class="timeline-obs timeline-obs-danger">
                        <?php
                        // Verifico si hay datos
                        if(is_array($data['arrObservaciones'])&&!empty($data['arrObservaciones'])){
                            // Recorro los datos
                            foreach($data['arrObservaciones'] as $crud){ ?>
                                <div class="timeline-item timeline-item--warning" data-type="warning">
                                    <div class="timeline-card">
                                        <div class="timeline-card__meta">
                                            <span class="timeline-card__author"><?php echo $crud['Usuario']; ?></span>
                                            <span class="timeline-card__date"><?php echo $crud['FechaCreacion']; ?></span>
                                        </div>
                                        <div class="timeline-card__text"><?php echo $crud['Observacion']; ?></div>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>

                    <div class="tl-timeline">
                        <?php
                        // Verifico si hay datos
                        if(is_array($data['arrObservaciones'])&&!empty($data['arrObservaciones'])){
                            // Recorro los datos
                            foreach($data['arrObservaciones'] as $crud){ ?>
                                <div class="tl-item">
                                    <div class="tl-item__line">
                                        <div class="tl-dot tl-dot--warning"></div>
                                        <div class="tl-connector"></div>
                                    </div>
                                    <div class="tl-content">
                                        <div class="tl-meta">
                                            <span class="tl-author"><?php echo $crud['Usuario']; ?></span>
                                            <span class="tl-sep">·</span>
                                            <span class="tl-date"><?php echo $crud['FechaCreacion']; ?></span>
                                        </div>
                                        <div class="tl-text"><?php echo $crud['Observacion']; ?></div>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>
