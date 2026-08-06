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
                    <h5 class="card-title"><?php echo $data['TableTitle']; ?></h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover datatable">
                            <thead>
                                <tr>
                                    <th scope="col">Tipo Movimiento</th>
                                    <th scope="col">Documento</th>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Entidad</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col">Valor Total</th>
                                    <th scope="col">Monto Pagado</th>
                                    <th scope="col" style="width: 10px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                //Verifico si hay datos
                                if(is_array($data['arrList'])&&!empty($data['arrList'])){
                                    //Recorro
                                    foreach($data['arrList'] as $crud){ ?>
                                        <tr>
                                            <td><?php echo $crud['TipoMov']; ?></td>
                                            <td><?php echo $crud['Documento'].' '.$crud['N_Doc']; ?></td>
                                            <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['Creacion_fecha']); ?></td>
                                            <td>
                                                <?php
                                                //Se obtiene el nombre o la razón social
                                                switch ($crud['idTipoEntidad']) {
                                                    case 1: $Entidad = $crud['EntidadesApellido'].', '.$crud['EntidadesNombre']; break; //Persona Natural
                                                    case 2: $Entidad = $crud['EntidadesRazonSocial']; break;                            //Empresas
                                                }
                                                // Imprimir
                                                echo $Entidad;
                                                ?>
                                            </td>
                                            <td><?php echo '<span class="badge-sp1 badge-sp1-'.$crud['EstadoColor'].'">'.$crud['EstadoPago'].'</span>'; ?></td>
                                            <td><?php echo $data['Fnc_DataNumbers']->Valores($crud['ValorTotal'], 2); ?></td>
                                            <td><?php echo $data['Fnc_DataNumbers']->Valores($crud['MontoPagado'], 2); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <?php if($data['UserAccess']['LevelAccess']>=1){ ?>
                                                        <button type="button" onclick="listTableDataView('<?php echo $data['Fnc_Codification']->encryptDecrypt('encrypt', $crud['idFacturacion']); ?>')"    class="btn btn-primary   btn-sm tooltiplink" data-title="Ver Información"><i class="bi bi-eye"></i></button>
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
