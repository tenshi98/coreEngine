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
                                    foreach($data['arrList'] AS $crud){ ?>
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
                                                    <?php if($data['UserAccess']['LevelAccess']>=1){ ?>
                                                        <button type="button" onclick="TDviewBTN('<?php echo $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idCrud']); ?>')"   class="btn btn-primary btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } ?>
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
