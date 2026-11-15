<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<section class="section" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            <?php echo $data['TableTitle']; ?>
                            <?php if($data['UserAccess']['LevelAccess']>=3){ ?>
                                <button type="button" class="btn btn-success" data-export-url="<?php echo htmlspecialchars($BASE.'/'.$data['UserAccess']['RouteAccess'].'/exportExcel', ENT_QUOTES, 'UTF-8'); ?>" data-export-filename="Vehiculos" onclick="exportExcelServer(this)"><i class="ri-file-excel-2-line"></i> Exportar a Excel</button>
                            <?php } ?>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Nombre</th>
                                    <th scope="col">Tipo</th>
                                    <th scope="col">Marca</th>
                                    <th scope="col">Modelo</th>
                                    <th scope="col">Patente</th>
                                    <th scope="col">Número de serie</th>
                                    <th scope="col">Año de Fabricación</th>
                                    <th scope="col">Capacidad Pasajeros</th>
                                    <th scope="col">Capacidad (Kilos)</th>
                                    <th scope="col">Metros Cúbicos (M3)</th>
                                    <th scope="col">Tipo de Carga</th>
                                    <th scope="col">Latitud</th>
                                    <th scope="col">Longitud</th>
                                    <th scope="col">Velocidad</th>
                                    <th scope="col">Última Actualización</th>
                                    <th scope="col">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Verifico si hay datos
                                if(is_array($data['arrList'])&&!empty($data['arrList'])){
                                    // Recorro
                                    foreach($data['arrList'] as $crud){
                                        // Se arma la fecha y hora de la ultima actualizacion
                                        $UltimaActualizacion = !empty($crud['LastUpdateFecha'])
                                                             ? $data['Fnc_DataDate']->fechaEstandar($crud['LastUpdateFecha']).' '.$crud['LastUpdateHora']
                                                             : ''; ?>
                                        <tr>
                                            <td><?php echo $crud['Nombre']; ?></td>
                                            <td><?php echo $crud['Tipo']; ?></td>
                                            <td><?php echo $crud['Marca']; ?></td>
                                            <td><?php echo $crud['Modelo']; ?></td>
                                            <td><?php echo $crud['Patente']; ?></td>
                                            <td><?php echo $crud['Num_serie']; ?></td>
                                            <td><?php echo $crud['AnoFab']; ?></td>
                                            <td><?php echo $crud['CapacidadPersonas']; ?></td>
                                            <td><?php echo $data['Fnc_DataNumbers']->cantidadesDecimalesJustos($crud['Capacidad']); ?></td>
                                            <td><?php echo $data['Fnc_DataNumbers']->cantidadesDecimalesJustos($crud['MCubicos']); ?></td>
                                            <td><?php echo $crud['TipoCarga']; ?></td>
                                            <td><?php echo $crud['Latitud']; ?></td>
                                            <td><?php echo $crud['Longitud']; ?></td>
                                            <td><?php echo $crud['Velocidad']; ?></td>
                                            <td><?php echo $UltimaActualizacion; ?></td>
                                            <td><?php echo $crud['Estado']; ?></td>
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
    <a href="<?php echo $BASE.'/'.$data['UserAccess']['RouteAccess'].'/listAll'; ?>" class="btn btn-danger"><i class="bi bi-arrow-left-circle"></i> Volver</a>
</div>
<div class="clearfix"></div>
