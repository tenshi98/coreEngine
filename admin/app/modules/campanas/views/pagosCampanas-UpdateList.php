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
                                    <th>Entidad</th>
                                    <th>Campaña</th>
                                    <th>Documento</th>
                                    <th width="160">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                //Verifico si hay datos
                                if(is_array($data['arrList'])&&!empty($data['arrList'])){
                                    //Recorro
                                    foreach ($data['arrList'] AS $crud){
                                        //Para la lista
                                        $Documento    = $crud['Documento'].' '.($crud['N_Doc'] ?? 'nRef '.$crud['idFacturacion']);
                                        $Documento   .= ' Fecha '.$data['Fnc_DataDate']->fechaEstandar($crud['Creacion_fecha']);
                                        $Documento   .= ' (Monto: '.$data['Fnc_DataNumbers']->Valores($crud['ValorTotal'], 0);
                                        $Documento   .= '/Pagado: '.$data['Fnc_DataNumbers']->Valores($crud['MontoPagado'], 0).')';
                                        //Se genera entidad
                                        $Entidad = '';
                                        $Entidad .= !empty($crud['EntidadNick'])
                                                    ? $crud['EntidadNick'].'<br>'
                                                    : '';
                                        $Entidad .= !empty($crud['EntidadNombre'])
                                                    ? $crud['EntidadApellido'].' '.$crud['EntidadNombre']
                                                    : $crud['EntidadRazonSocial'];
                                        //imprimimos la categoría
                                        echo '
                                        <tr>
                                            <td>'.$Entidad.'</td>
                                            <td>'.$crud['Campana'].'</td>
                                            <td>'.$Documento.'</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a target="new" href="'.$BASE.'/'.$data['UserAccess']['RouteAccess'].'/resumen/'.$data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idFacturacion']).'" class="btn btn-secondary btn-sm tooltiplink" data-title="Editar Información"><i class="bi bi-pencil-square"></i></a>
                                                </div>
                                            </td>
                                        </tr>';
                                    }
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
