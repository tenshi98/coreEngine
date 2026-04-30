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
                                <button type="button" class="btn btn-success"    onclick="exportTableToExcel('tableData', 'Entidades')"><i class="ri-file-excel-2-line"></i> Exportar a Excel</button>
                            <?php } ?>
                        </div>
                    </h5>
                    <div class="clearfix"></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" id="tableData">
                            <thead>
                                <tr>
                                    <th scope="col">Nombre</th>
                                    <th scope="col">Nick</th>
                                    <th scope="col">Rut</th>
                                    <th scope="col">Ciudad</th>
                                    <th scope="col">Comuna</th>
                                    <th scope="col">Sector</th>
                                    <th scope="col">Dirección</th>
                                    <th scope="col">Fecha Nacimiento</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Celular</th>
                                    <th scope="col">Teléfono</th>
                                    <th scope="col">Web</th>
                                    <th scope="col">Giro</th>
                                    <th scope="col">Tipo</th>
                                    <th scope="col">Tipo Entidad</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col">Sexo</th>
                                    <th scope="col">Rep Legal Nombre</th>
                                    <th scope="col">Rep Legal Rut</th>
                                    <th scope="col">Rep Legal Email</th>
                                    <th scope="col">Rep Legal Celular</th>
                                    <th scope="col">Rep Legal Teléfono</th>
                                    <th scope="col">X (twiter)</th>
                                    <th scope="col">Facebook</th>
                                    <th scope="col">Instagram</th>
                                    <th scope="col">Linkedin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                //Verifico si hay datos
                                if(is_array($data['arrList'])&&!empty($data['arrList'])){
                                    //Recorro
                                    foreach($data['arrList'] as $crud){
                                        //Se obtiene el nombre o la razón social
                                        $Entidad  = !empty($crud['Nombre'])
                                                    ? $crud['ApellidoPat'].' '.$crud['ApellidoMat'].' '.$crud['Nombre']
                                                    : $crud['RazonSocial'];
                                         ?>
                                        <tr>
                                            <td><?php echo $Entidad; ?></td>
                                            <td><?php echo $crud['Nick']; ?></td>
                                            <td><?php echo $crud['Rut']; ?></td>
                                            <td><?php echo $crud['Ciudad']; ?></td>
                                            <td><?php echo $crud['Comuna']; ?></td>
                                            <td><?php echo $crud['Sector']; ?></td>
                                            <td><?php echo $crud['Direccion']; ?></td>
                                            <td><?php echo $data['Fnc_DataDate']->fechaEstandar($crud['FNacimiento']); ?></td>
                                            <td><?php echo $crud['Email']; ?></td>
                                            <td><?php echo $data['Fnc_DataNumbers']->formatPhone($crud['Fono1']); ?></td>
                                            <td><?php echo $data['Fnc_DataNumbers']->formatPhone($crud['Fono2']); ?></td>
                                            <td><?php echo $crud['Web']; ?></td>
                                            <td><?php echo $crud['Giro']; ?></td>
                                            <td><?php echo $crud['Tipo']; ?></td>
                                            <td><?php echo $crud['TipoEntidad']; ?></td>
                                            <td><?php echo $crud['Estado']; ?></td>
                                            <td><?php echo $crud['Sexo']; ?></td>
                                            <td><?php echo $crud['RepLegalNombre']; ?></td>
                                            <td><?php echo $crud['RepLegalRut']; ?></td>
                                            <td><?php echo $crud['RepLegalEmail']; ?></td>
                                            <td><?php echo $data['Fnc_DataNumbers']->formatPhone($crud['RepLegalFono1']); ?></td>
                                            <td><?php echo $data['Fnc_DataNumbers']->formatPhone($crud['RepLegalFono2']); ?></td>
                                            <td><?php echo $crud['Social_X']; ?></td>
                                            <td><?php echo $crud['Social_Facebook']; ?></td>
                                            <td><?php echo $crud['Social_Instagram']; ?></td>
                                            <td><?php echo $crud['Social_Linkedin']; ?></td>
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
