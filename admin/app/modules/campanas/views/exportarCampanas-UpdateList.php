<section class="section" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <?php echo $data['TableTitle']; ?>
                        <button type="button" class="btn btn-sm btn-success float-end" onclick="exportTableToExcel('tableData', 'partidas')"><i class="ri-file-excel-2-line"></i> Exportar a Excel</button>
                    </h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover datatable" id="tableData">
                            <thead>
                                <tr>
                                    <th colspan="12">Campañas</th>

                                    <th colspan="8">Partidas</th>

                                </tr>
                                <tr>
                                    <th>Fecha creacion</th>
                                    <th>Fecha Campaña</th>
                                    <th>Nombre</th>
                                    <th>Observaciones</th>
                                    <th>Costos</th>
                                    <th>Beneficios</th>
                                    <th>Perdidas</th>
                                    <th>Margen</th>
                                    <th>Creador</th>
                                    <th>Estado</th>
                                    <th>Bodega</th>

                                    <th>Sector</th>
                                    <th>Direccion</th>
                                    <th>Entidad</th>
                                    <th>Nick</th>
                                    <th>Fecha Partida</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Valor</th>
                                    <th>Estado</th>
                                    <th>Documento relacionado</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                //Verifico si hay datos
                                if(is_array($data['arrList'])&&!empty($data['arrList'])){
                                    //Recorro
                                    foreach($data['arrList'] AS $crud){
                                        //Se obtiene el nombre o la razón social
                                        $Entidad =  !empty($crud['EntidadNombre'])
                                                    ? $crud['EntidadApellido'].' '.$crud['EntidadNombre']
                                                    : $crud['EntidadRazonSocial'];
                                        ?>
                                        <tr>
                                            <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['fecha_auto']); ?></td>
                                            <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['Fecha']); ?></td>
                                            <td><?php echo $crud['Nombre']; ?></td>
                                            <td><?php echo $crud['Observaciones']; ?></td>
                                            <td><?php echo $data['Fnc_DataNumbers']->Valores($crud['Costos'], 2); ?></td>
                                            <td><?php echo $data['Fnc_DataNumbers']->Valores($crud['Beneficios'], 2); ?></td>
                                            <td><?php echo $data['Fnc_DataNumbers']->Valores($crud['Perdidas'], 2); ?></td>
                                            <td><?php echo $data['Fnc_DataNumbers']->Valores($crud['Margen'], 2); ?></td>
                                            <td><?php echo $crud['Creador']; ?></td>
                                            <td><?php echo '<span class="badge '.$crud['EstadoColor'].'">'.$crud['EstadoNombre'].'</span>'; ?></td>
                                            <td><?php echo $crud['Bodega']; ?></td>

                                            <td><?php echo $crud['EntidadSector']; ?></td>
                                            <td><?php echo $crud['EntidadDireccion']; ?></td>
                                            <td><?php echo $Entidad; ?></td>
                                            <td><?php echo $crud['EntidadNick']; ?></td>
                                            <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['Fecha']); ?></td>
                                            <td><?php echo $crud['Producto']; ?></td>
                                            <td><?php echo $crud['Cantidad'].' '.$crud['Unimed']; ?></td>
                                            <td><?php echo $data['Fnc_DataNumbers']->Valores($crud['Beneficios'], 2); ?></td>
                                            <td><?php echo $crud['PartidaEstado']; ?></td>
                                            <td><?php if(isset($crud['DocumentoNombre'])&&$crud['DocumentoNombre']!=''){echo $crud['DocumentoNombre'].' '.($crud['DocumentoN_Doc'] ?? 'nRef '.$crud['idFacturacion']);} ?></td>
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
